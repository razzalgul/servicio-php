<?php

declare(strict_types=1);

namespace App\Application\Task;

use App\Domain\Contract\DatabaseReaderInterface;
use App\Domain\Contract\HttpSenderInterface;
use App\Domain\Contract\ScheduledTaskInterface;
use App\Domain\Contract\TagProviderInterface;
use App\Infrastructure\Config\EnvConfig;
use Psr\Log\LoggerInterface;

class EventSyncTask implements ScheduledTaskInterface
{
    private int $intervalSeconds = 600; // 10 minutos

    public function __construct(
        private DatabaseReaderInterface $dbReader,
        private TagProviderInterface $tagRepo,
        private HttpSenderInterface $httpSender,
        private EnvConfig $config,
        private LoggerInterface $logger
    ) {}

    public function getIntervalSeconds(): int
    {
        return $this->intervalSeconds;
    }

    public function run(): void
    {
        $this->logger->info("Iniciando EventSyncTask: obteniendo parámetros requeridos...");
        
        $requiredTags = $this->tagRepo->fetchParametersFromApi();
        $tagNames = array_column($requiredTags, 'TagName');
        
        if (empty($tagNames)) {
            $this->logger->warning("EventSyncTask: No se obtuvieron TagNames de la API.");
            return;
        }

        $this->logger->info("Consultando eventos históricos...");
        $events = $this->dbReader->getHistoricalEvents($tagNames);

        if (empty($events)) {
            $this->logger->info("No hay eventos nuevos para enviar.");
            return;
        }

        $this->logger->info("Enviando " . count($events) . " eventos al servidor externo...");

        $eventApiUrl = $this->config->get('app.event_api_url') ?? '';
        $urlParts = parse_url($eventApiUrl);
        $endpoint = ($urlParts['path'] ?? '/api/events') . (isset($urlParts['query']) ? '?' . $urlParts['query'] : '');

        foreach ($events as $event) {
            $success = $this->httpSender->sendWithFallback('event_sync', $endpoint, $event);
            if ($success) {
                $this->logger->debug("Evento enviado correctamente.");
            } else {
                $this->logger->warning("Fallo al enviar evento, encolado en outbox.");
            }
        }
    }
}
