<?php

namespace App;

use Psr\Log\LoggerInterface;
use Throwable;

class TagRepository
{
    public function __construct(
        private Config $config,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Obtiene una lista plana de todos los tags desde una API externa.
     * @return string[] Una lista de nombres de tags.
     */
    public function fetchTagsFromApi(): array
    {
        $apiUrl = $this->config->get('app.api_limits_url');
        if (!$apiUrl) {
            $this->logger->error("La URL de la API de límites no está configurada en .env (API_LIMITS_URL).");
            return [];
        }

        $this->logger->info("Obteniendo la lista de tags desde la API.", ['url' => $apiUrl]);
         $bearerToken = $this->config->get('ws.token');
        try {
            $urlParts = parse_url($apiUrl);
            $host = $urlParts['host'];
            $ssl = ($urlParts['scheme'] ?? 'https') === 'https';
            $port = $urlParts['port'] ?? ($ssl ? 443 : 80);
            $path = ($urlParts['path'] ?? '/') . (isset($urlParts['query']) ? '?' . $urlParts['query'] : '');

            $client = new \OpenSwoole\Coroutine\Http\Client($host, $port, $ssl);
             $client->setHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $bearerToken
            ]);
            $client->set(['timeout' => 10]);
            $client->get($path);

            if ($client->statusCode !== 200) {
                $this->logger->error("Error al obtener tags de la API. Código de estado: {$client->statusCode}", [
                    'body' => substr($client->body, 0, 500)
                ]);
                $client->close();
                return []; // Retornar vacío en caso de error para no detener el servicio.
            }

            $body = $client->body;
            $client->close();

            $data = json_decode($body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logger->error("Error al decodificar la respuesta JSON de la API de tags.", [
                    'error' => json_last_error_msg()
                ]);
                return [];
            }
            $payload = $data['payload'] ?? []; 
            $tags = array_column($payload, 'tag');
            $this->logger->info("Se obtuvieron " . count($tags) . " tags únicos desde la API.");

            return $tags;
        } catch (Throwable $e) {
            $this->logger->error("Excepción al contactar la API de tags: " . $e->getMessage());
            return [];
        }
    }

    public function fetchParametersFromAPI(): array
    {
        $apiUrl = $this->config->get('app.event_parameters_url');
        if (!$apiUrl) {
            $this->logger->error("La URL de parámetros de eventos no está configurada en config.");
            return [];
        }
        $this->logger->info("Obteniendo la lista de tags desde la API.", ['url' => $apiUrl]);
         $bearerToken = $this->config->get('ws.token');
        try {
            $urlParts = parse_url($apiUrl);
            $host = $urlParts['host'];
            $ssl = ($urlParts['scheme'] ?? 'https') === 'https';
            $port = $urlParts['port'] ?? ($ssl ? 443 : 80);
            $path = ($urlParts['path'] ?? '/') . (isset($urlParts['query']) ? '?' . $urlParts['query'] : '');

            $client = new \OpenSwoole\Coroutine\Http\Client($host, $port, $ssl);
            $client->set(['timeout' => 10]);
              $client->setHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $bearerToken
            ]);
            $client->get($path);

            if ($client->statusCode !== 200) {
                $this->logger->error("Error al obtener parámetros de la API. Código: {$client->statusCode}");
                $client->close();
                return [];
            }

            $body = $client->body;
            $client->close();

            $data = json_decode($body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logger->error("Error al decodificar la respuesta JSON de la API de parámetros.", [
                    'error' => json_last_error_msg()
                ]);
                return [];
            }
            return $data['payload'] ?? [];
        } catch (\Throwable $e) {
            $this->logger->error("Excepción al contactar la API de parámetros: " . $e->getMessage());
            return [];
        }
    }
}

