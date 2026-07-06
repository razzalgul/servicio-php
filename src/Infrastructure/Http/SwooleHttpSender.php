<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Domain\Contract\HttpSenderInterface;
use App\Domain\Contract\OutboxRepositoryInterface;
use App\Domain\DTO\HttpResponse;
use App\Domain\DTO\OutboxMessage;
use App\Infrastructure\Config\EnvConfig;
use Psr\Log\LoggerInterface;

class SwooleHttpSender implements HttpSenderInterface
{
    private string $baseUrl;
    private string $token;
    private bool $sslVerify;

    public function __construct(
        EnvConfig $config,
        private OutboxRepositoryInterface $outbox,
        private LoggerInterface $logger
    ) {
        $apiConfig = $config->get('remote_api');
        $this->baseUrl = rtrim($apiConfig['base_url'] ?? '', '/');
        $this->token = $apiConfig['token'] ?? '';
        $this->sslVerify = (bool)($apiConfig['ssl_verify'] ?? false);
    }

    public function post(string $endpoint, array|string $payload): HttpResponse
    {
        if (empty($this->baseUrl)) {
            $this->logger->error("SwooleHttpSender: base_url no configurada.");
            return new HttpResponse(0, "Base URL not configured");
        }

        $parsed = parse_url($this->baseUrl);
        $host = $parsed['host'] ?? '';
        $port = isset($parsed['port']) ? (int)$parsed['port'] : (($parsed['scheme'] ?? '') === 'https' ? 443 : 80);
        $ssl = (($parsed['scheme'] ?? '') === 'https');

        try {
            $client = new \Swoole\Coroutine\Http\Client($host, $port, $ssl);
            
            $settings = ['timeout' => 10];
            if (!$this->sslVerify) {
                $settings['ssl_verify_peer'] = false;
                $settings['ssl_allow_self_signed'] = true;
            }
            $client->set($settings);

            $client->setHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->token,
            ]);

            $body = is_array($payload) ? json_encode($payload) : $payload;
            
            // Asegurar que el endpoint empiece con /
            $path = '/' . ltrim($endpoint, '/');
            $client->post($path, $body);

            $statusCode = (int)$client->statusCode;
            $responseBody = (string)($client->body ?? '');
            $client->close();

            return new HttpResponse($statusCode, $responseBody);
        } catch (\Throwable $e) {
            $this->logger->error("SwooleHttpSender error al conectar con {$host}:{$port}{$endpoint}: " . $e->getMessage());
            return new HttpResponse(0, $e->getMessage());
        }
    }

    public function sendWithFallback(string $taskType, string $endpoint, array|string $payload): bool
    {
        $response = $this->post($endpoint, $payload);

        if ($response->isSuccessful()) {
            return true;
        }

        // Fallback: encolar en outbox
        $body = is_array($payload) ? json_encode($payload) : $payload;
        $message = new OutboxMessage(
            id: time() . '_' . $taskType . '_' . substr(md5(uniqid('', true)), 0, 6),
            taskType: $taskType,
            destination: 'remote_api',
            endpoint: $endpoint,
            httpMethod: 'POST',
            payload: $body,
            createdAt: date('c')
        );

        $this->outbox->enqueue($message);
        $this->logger->warning("Envío fallido (HTTP {$response->statusCode}). Encolado en outbox: {$taskType} → {$endpoint}");
        return false;
    }
}
