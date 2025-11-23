<?php

namespace App;

use Psr\Log\LoggerInterface;

class EventSyncTask implements ScheduledTaskInterface
{
    private int $intervalSeconds = 600; // 10 minutos

    public function __construct(
        private DataBaseService $dbService,
        private LoggerInterface $logger,
        private Config $config, // Inyecta Config como dependencia
        private TagRepository $tagRepo // Inyecta TagRepository como dependencia
    ) {}

    public function getIntervalSeconds(): int
    {
        return $this->intervalSeconds;
    }

    public function run(): void
    {
        $this->logger->info("obteniendo tags requeridos...");
        $requiredTags = $this->tagRepo->fetchParametersFromAPI();
        $requiredTags = array_column($requiredTags, 'TagName');
        $this->logger->info("Consultando eventos...");
        $events = $this->dbService->getHistoricalEvents($requiredTags);
        if (!empty($events)) {
            $this->logger->info("Enviando " . count($events) . " eventos al servidor externo...");

        $bearerToken = $this->config->get('ws.token');
        $eventApiUrl = $this->config->get('app.event_api_url'); 
        // Parsear la URL solo una vez
        $urlParts = parse_url($eventApiUrl);
        $host = $urlParts['host'];
        $ssl = ($urlParts['scheme'] ?? 'https') === 'https';
        $port = $urlParts['port'] ?? ($ssl ? 443 : 80);
        $path = ($urlParts['path'] ?? '/') . (isset($urlParts['query']) ? '?' . $urlParts['query'] : '');

        foreach ($events as $event) {
            $client = new \OpenSwoole\Coroutine\Http\Client($host, $port, $ssl);
            $client->setHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $bearerToken
            ]);
            $payload = json_encode($event);
            $client->post($path, $payload);

            if ($client->statusCode >= 200 && $client->statusCode < 300) {
                $this->logger->info("Evento enviado correctamente. Código: {$client->statusCode}");
            } else {
                $this->logger->error("Error al enviar evento. Código: {$client->statusCode}, Respuesta: " . substr($client->body ?? '', 0, 500));
            }
            $client->close();
        }
        } else {
            $this->logger->info("No hay eventos nuevos para enviar.");
        }
    }
}
