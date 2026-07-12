<?php

declare(strict_types=1);

namespace App\Application\Task;

use App\Domain\Contract\HttpSenderInterface;
use App\Domain\Contract\LocalDatabaseWriterInterface;
use App\Domain\Contract\OutboxRepositoryInterface;
use App\Domain\Contract\ScheduledTaskInterface;
use App\Infrastructure\Config\EnvConfig;
use Psr\Log\LoggerInterface;
use Throwable;

class OutboxRetryTask implements ScheduledTaskInterface
{
    private int $retryInterval;

    public function __construct(
        private OutboxRepositoryInterface $outbox,
        private HttpSenderInterface $httpSender,
        private LocalDatabaseWriterInterface $localDbWriter,
        private EnvConfig $config,
        private LoggerInterface $logger
    ) {
        $outboxConfig = $this->config->get('outbox');
        $this->retryInterval = (int)($outboxConfig['retry_interval'] ?? 300);
    }

    public function getIntervalSeconds(): int
    {
        return $this->retryInterval;
    }

    public function run(): void
    {
        $this->logger->info("Iniciando OutboxRetryTask: verificando mensajes pendientes en cola...");

        // 1. Purgar mensajes expirados o que excedieron máximo de reintentos
        $purged = $this->outbox->purgeExpired();
        if ($purged > 0) {
            $this->logger->info("OutboxRetryTask: Se purgaron {$purged} mensajes expirados.");
        }

        // 2. Obtener pendientes
        $messages = $this->outbox->dequeuePending(50);
        if (empty($messages)) {
            $this->logger->debug("OutboxRetryTask: No hay mensajes pendientes en la cola.");
            return;
        }

        $this->logger->info("OutboxRetryTask: Procesando " . count($messages) . " mensajes pendientes...");

        foreach ($messages as $msg) {
            if ($msg->destination === 'remote_api') {
                $response = $this->httpSender->post($msg->endpoint, $msg->payload);
                if ($response->isSuccessful()) {
                    $this->logger->info("OutboxRetryTask: Mensaje {$msg->id} enviado con éxito a {$msg->endpoint}.");
                    $this->outbox->markAsCompleted($msg->id);
                } else {
                    $error = "HTTP {$response->statusCode}: " . substr($response->body, 0, 200);
                    $this->logger->warning("OutboxRetryTask: Fallo al reintentar mensaje {$msg->id}. Error: {$error}");
                    $this->outbox->incrementRetry($msg->id, $error);
                }
            } elseif ($msg->destination === 'local_mysql') {
                try {
                    $data = json_decode($msg->payload, true);
                    if (!is_array($data)) {
                        throw new \Exception("Payload JSON inválido para MySQL");
                    }

                    if ($msg->endpoint === 'datosc2' || $msg->taskType === 'datosc2_insert') {
                        // Puede ser un array de filas (ej. L1 y L2) o una sola fila
                        if (isset($data[0]) && is_array($data[0])) {
                            foreach ($data as $row) {
                                $this->localDbWriter->insertPlantLoadC2($row);
                            }
                        } else {
                            $this->localDbWriter->insertPlantLoadC2($data);
                        }
                    } elseif ($msg->endpoint === 'datos_repdia' || $msg->taskType === 'datos_repdia_insert') {
                        $this->localDbWriter->insertPlantLoadReport($data);
                    } else {
                        throw new \Exception("Destino local MySQL desconocido: {$msg->endpoint}");
                    }

                    $this->logger->info("OutboxRetryTask: Mensaje local MySQL {$msg->id} insertado con éxito en {$msg->endpoint}.");
                    $this->outbox->markAsCompleted($msg->id);
                } catch (Throwable $e) {
                    $error = "MySQL Error: " . $e->getMessage();
                    $this->logger->warning("OutboxRetryTask: Fallo al reintentar mensaje local MySQL {$msg->id}. Error: {$error}");
                    $this->outbox->incrementRetry($msg->id, $error);
                }
            } else {
                $this->logger->error("OutboxRetryTask: Destino desconocido '{$msg->destination}' para mensaje {$msg->id}. Purgando.");
                $this->outbox->markAsCompleted($msg->id);
            }
        }
    }
}
