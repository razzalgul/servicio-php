<?php

declare(strict_types=1);

namespace App\Application\Task;

use App\Domain\Contract\DatabaseReaderInterface;
use App\Domain\Contract\HttpSenderInterface;
use App\Domain\Contract\ScheduledTaskInterface;
use Psr\Log\LoggerInterface;

class HourlyProductionTask implements ScheduledTaskInterface
{
    private int $intervalSeconds = 3600; // 1 hora

    public function __construct(
        private DatabaseReaderInterface $dbReader,
        private HttpSenderInterface $httpSender,
        private LoggerInterface $logger
    ) {}

    public function getIntervalSeconds(): int
    {
        return $this->intervalSeconds;
    }

    public function run(): void
    {
        $this->logger->info("Iniciando tarea HourlyProductionTask...");

        $prevHour = date('Y-m-d H:00:00', strtotime('-1 hour'));
        $deltas = $this->dbReader->getHourlyTagDelta(['WCT1741.Value', 'WCT2741.Value'], $prevHour);

        $quantityL1 = $deltas['WCT1741.Value'] ?? 0;
        $quantityL2 = $deltas['WCT2741.Value'] ?? 0;

        if (!isset($deltas['WCT1741.Value'])) {
            $this->logger->warning("Advertencia: No se calculó delta para WCT1741.Value. Enviando 0.");
        }
        if (!isset($deltas['WCT2741.Value'])) {
            $this->logger->warning("Advertencia: No se calculó delta para WCT2741.Value. Enviando 0.");
        }

        $postData = [
            [
                'quantity' => $quantityL1,
                'productionLineId' => 1,
                'date' => $prevHour
            ],
            [
                'quantity' => $quantityL2,
                'productionLineId' => 2,
                'date' => $prevHour
            ]
        ];

        $endpoint = '/api/production/hourly';
        $success = $this->httpSender->sendWithFallback('hourly_production', $endpoint, $postData);

        if ($success) {
            $this->logger->info("HourlyProductionTask: Datos enviados correctamente a {$endpoint}.");
        } else {
            $this->logger->warning("HourlyProductionTask: Envío fallido, encolado en outbox.");
        }
    }
}
