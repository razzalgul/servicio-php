<?php

declare(strict_types=1);

namespace App\Infrastructure\Outbox;

use App\Domain\Contract\OutboxRepositoryInterface;
use App\Domain\DTO\OutboxMessage;
use App\Infrastructure\Config\EnvConfig;
use Psr\Log\LoggerInterface;

class FileOutboxRepository implements OutboxRepositoryInterface
{
    private string $queueDir;
    private int $maxRetries;
    private int $ttlHours;

    public function __construct(
        EnvConfig $config,
        private LoggerInterface $logger
    ) {
        $outboxConfig = $config->get('outbox');
        $queuePath = $outboxConfig['queue_path'] ?? 'data/queue';
        
        // Resolver ruta relativa al directorio raíz del proyecto
        $rootPath = realpath(__DIR__ . '/../../../');
        $this->queueDir = rtrim($rootPath . '/' . ltrim($queuePath, '/'), '/');
        
        if (!is_dir($this->queueDir)) {
            mkdir($this->queueDir, 0777, true);
        }

        $this->maxRetries = (int)($outboxConfig['max_retries'] ?? 5);
        $this->ttlHours = (int)($outboxConfig['ttl_hours'] ?? 24);
    }

    public function enqueue(OutboxMessage $message): void
    {
        $filename = $this->queueDir . '/' . $message->id . '.json';
        $tmpFilename = $filename . '.tmp';

        $data = [
            'id' => $message->id,
            'taskType' => $message->taskType,
            'destination' => $message->destination,
            'endpoint' => $message->endpoint,
            'httpMethod' => $message->httpMethod,
            'payload' => $message->payload,
            'createdAt' => $message->createdAt,
            'attempts' => $message->attempts,
            'lastError' => $message->lastError,
            'updatedAt' => $message->updatedAt,
        ];

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            $this->logger->error("FileOutboxRepository: Error al codificar JSON para mensaje {$message->id}");
            return;
        }

        if (file_put_contents($tmpFilename, $json, LOCK_EX) !== false) {
            rename($tmpFilename, $filename);
            $this->logger->info("FileOutboxRepository: Mensaje encolado en {$filename}");
        } else {
            $this->logger->error("FileOutboxRepository: Error al escribir archivo temporal {$tmpFilename}");
        }
    }

    public function dequeuePending(int $limit = 50): array
    {
        $files = glob($this->queueDir . '/*.json');
        if ($files === false || empty($files)) {
            return [];
        }

        // Ordenar por tiempo de modificación o nombre de archivo (que empieza con timestamp)
        sort($files);

        $pending = [];
        foreach ($files as $file) {
            if (count($pending) >= $limit) {
                break;
            }

            $message = $this->readMessageFromFile($file);
            if ($message === null) {
                continue;
            }

            if ($message->hasExceededMaxRetries($this->maxRetries) || $message->hasExpired($this->ttlHours)) {
                continue;
            }

            $pending[] = $message;
        }

        return $pending;
    }

    public function markAsCompleted(string $id): void
    {
        $filename = $this->queueDir . '/' . $id . '.json';
        if (file_exists($filename)) {
            unlink($filename);
            $this->logger->info("FileOutboxRepository: Mensaje {$id} completado y eliminado de la cola.");
        }
    }

    public function incrementRetry(string $id, string $error): void
    {
        $filename = $this->queueDir . '/' . $id . '.json';
        if (!file_exists($filename)) {
            return;
        }

        $message = $this->readMessageFromFile($filename);
        if ($message === null) {
            return;
        }

        $updatedMessage = new OutboxMessage(
            id: $message->id,
            taskType: $message->taskType,
            destination: $message->destination,
            endpoint: $message->endpoint,
            httpMethod: $message->httpMethod,
            payload: $message->payload,
            createdAt: $message->createdAt,
            attempts: $message->attempts + 1,
            lastError: $error,
            updatedAt: date('c')
        );

        $this->enqueue($updatedMessage);
        $this->logger->warning("FileOutboxRepository: Incrementado intento para {$id} (Intento: {$updatedMessage->attempts}/{$this->maxRetries}). Error: {$error}");
    }

    public function purgeExpired(): int
    {
        $files = glob($this->queueDir . '/*.json');
        if ($files === false || empty($files)) {
            return 0;
        }

        $purgedCount = 0;
        foreach ($files as $file) {
            $message = $this->readMessageFromFile($file);
            if ($message === null) {
                continue;
            }

            if ($message->hasExceededMaxRetries($this->maxRetries) || $message->hasExpired($this->ttlHours)) {
                if (unlink($file)) {
                    $purgedCount++;
                    $reason = $message->hasExceededMaxRetries($this->maxRetries) ? "máximo de reintentos ({$this->maxRetries})" : "TTL expirado ({$this->ttlHours}h)";
                    $this->logger->error("FileOutboxRepository: Mensaje {$message->id} purgado por {$reason}. Último error: {$message->lastError}");
                }
            }
        }

        return $purgedCount;
    }

    private function readMessageFromFile(string $file): ?OutboxMessage
    {
        $content = file_get_contents($file);
        if ($content === false) {
            return null;
        }

        $data = json_decode($content, true);
        if (!is_array($data) || !isset($data['id'], $data['taskType'], $data['destination'], $data['endpoint'], $data['httpMethod'], $data['payload'], $data['createdAt'])) {
            $this->logger->error("FileOutboxRepository: Archivo JSON corrupto o inválido: {$file}");
            return null;
        }

        return new OutboxMessage(
            id: (string)$data['id'],
            taskType: (string)$data['taskType'],
            destination: (string)$data['destination'],
            endpoint: (string)$data['endpoint'],
            httpMethod: (string)$data['httpMethod'],
            payload: (string)$data['payload'],
            createdAt: (string)$data['createdAt'],
            attempts: (int)($data['attempts'] ?? 0),
            lastError: isset($data['lastError']) ? (string)$data['lastError'] : null,
            updatedAt: isset($data['updatedAt']) ? (string)$data['updatedAt'] : null
        );
    }
}
