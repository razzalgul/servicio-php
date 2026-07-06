<?php

declare(strict_types=1);

namespace App\Application\Task;

use App\Domain\Contract\DatabaseReaderInterface;
use App\Domain\Contract\HttpSenderInterface;
use App\Domain\Contract\ScheduledTaskInterface;
use Psr\Log\LoggerInterface;

class HourlyMineralFeedTask implements ScheduledTaskInterface
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
        $this->logger->info("Iniciando tarea HourlyMineralFeedTask...");

        $prevHour = date('Y-m-d H:00:00', strtotime('-1 hour'));
        $tag = 'WCT0303.Value';
        
        $deltas = $this->dbReader->getHourlyTagDelta([$tag], $prevHour);
        $hourlyQuantity = $deltas[$tag] ?? 0;

        if (!isset($deltas[$tag])) {
            $this->logger->warning("Advertencia: No se calculó delta para {$tag}. Enviando 0.");
        }

        $postData = [
            'date' => $prevHour,
            'quantity' => $hourlyQuantity
        ];

        $endpoint = '/api/mineralfeed/hour';
        $success = $this->httpSender->sendWithFallback('hourly_mineral_feed', $endpoint, $postData);

        if ($success) {
            $this->logger->info("HourlyMineralFeedTask: Datos enviados correctamente a {$endpoint}.");
        } else {
            $this->logger->warning("HourlyMineralFeedTask: Envío fallido, encolado en outbox.");
        }
    }
}
