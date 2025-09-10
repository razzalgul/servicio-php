<?php

namespace App;

use OpenSwoole\Coroutine\Http\Client;
use Psr\Log\LoggerInterface;
use Throwable;

class WebSocketClient
{
    private array $wsConfig;
    private int $reconnectDelay = 5; // Segundos de espera antes de reintentar la conexión
    private int $noServerResponseLimit; // Número de intentos sin respuesta antes de reconectar
    private int $noServerResponseCount = 0;
    private ?Client $client = null;
    private bool $isConnected = false;

    public function __construct(
        private Config $config,
        private LoggerInterface $logger
    ) {
        $this->wsConfig = $this->config->get('ws');
        $this->noServerResponseLimit = $this->wsConfig['no_server_response_limit'] ?? 10;
    }

    /**
     * Asegura que el cliente esté conectado y autenticado.
     * Este método bloqueará y reintentará hasta que la conexión sea exitosa.
     */
    public function ensureConnected(): void
    {
        if ($this->isConnected && $this->client && $this->client->connected) {
            return;
        }

        $this->isConnected = false;

        while (!$this->isConnected) {
            try {
                $this->logger->info("Intentando conectar al servidor WebSocket...", [
                    'host' => $this->wsConfig['host'],
                    'port' => $this->wsConfig['port']
                ]);

                $this->client = new Client($this->wsConfig['host'], $this->wsConfig['port'], $this->wsConfig['ssl']);
                $this->client->set([
                    'timeout' => 10,
                    'websocket_compression' => true,
                ]);

                if (!$this->client->upgrade($this->wsConfig['path']. '?sourceKey=' . $this->wsConfig['token'])) {
                    throw new \Exception("Fallo al actualizar a WebSocket. Status: {$this->client->statusCode}");
                }

                $this->logger->info("Conexión WebSocket establecida. Autenticando...");

                // Payload de autenticación, usando el token de .env
                $authPayload = [

                    'sourceKey' => $this->wsConfig['token']
                ];
            
                $this->logger->info("Autenticación enviada. Esperando confirmación...");
                $response = $this->client->recv(2); // Esperar 2 segundos por una respuesta
                if ($response === false || $response === '') {
                    $this->logger->warning("No se recibió confirmación del servidor, pero se procederá.");
                } else {
                    $this->logger->info("Autenticación exitosa.", ['response' => (string)$response->data]);
                }

                $this->isConnected = true;
                $this->logger->info("Cliente WebSocket conectado y autenticado.");

            } catch (Throwable $e) {
                $this->logger->error("Fallo en la conexión WebSocket: " . $e->getMessage());
                $this->close();
                $this->logger->info("Se reintentará la conexión en {$this->reconnectDelay} segundos.");
                \Co::sleep($this->reconnectDelay);
            }
        }
    }

    /**
     * Envía datos al servidor WebSocket.
     *
     * @param array $data El payload a enviar.
     * @return bool True en caso de éxito, false en caso de fallo.
     */
    public function send(array | object $data): bool
    {
        if (!$this->isConnected || !$this->client) {
            $this->logger->error("No se pueden enviar datos: el cliente no está conectado.");
            return false;
        }

        try {
            $payload = json_encode($data);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Error de codificación JSON: ' . json_last_error_msg());
            }

            if (!$this->client->push($payload)) {
                throw new \Exception("Fallo al enviar datos al WebSocket. Código de error: {$this->client->errCode}, Mensaje: {$this->client->errMsg}");
            }
            return true;

        } catch (Throwable $e) {
            $this->logger->error("Error enviando datos: " . $e->getMessage());
            $this->isConnected = false;
            $this->close();
            return false;
        }
    }

       private function resetNoResponseCount(): void
    {
        $this->noServerResponseCount = 0;
    }
    public function getNoResponseCount(): int
    {
        return $this->noServerResponseCount;
    }

    public function readMessage(int $timeout = 5): ?string
    {
        if (!$this->isConnected || !$this->client) {
            $this->logger->error("No se pueden leer datos: el cliente no está conectado.");
            return null;
        }

        try {
            $response = $this->client->recv($timeout);
            if ($response === false) {
                throw new \Exception("Error al recibir datos del WebSocket. Código de error: {$this->client->errCode}, Mensaje: {$this->client->errMsg}");
            }
            $this->logger->debug("Respuesta recibida del servidor WebSocket.", ['response' => $response]);
            if ($response === '') {
                return null; // Timeout sin datos
            }
            if ($response->opcode === WEBSOCKET_OPCODE_PING) {
                // Responder a pings automáticamente
                $pongFrame = new \Swoole\WebSocket\Frame();
                $pongFrame->opcode = WEBSOCKET_OPCODE_PONG;
                $this->client->push($pongFrame);
                $this->logger->debug("Ping recibido y respondido con Pong.");
                return null;
            }

            return (string)$response->data;

        } catch (Throwable $e) {
            $this->logger->error("Error leyendo datos: " . $e->getMessage());
            $this->isConnected = false;
            $this->close();
            return null;
        }
    }

    public function reconnect(): void
    {
        $this->close();
        $this->resetNoResponseCount();
        $this->ensureConnected();
    }

    /**
     * Cierra la conexión WebSocket.
     */
    public function close(): void
    {
        if ($this->client && $this->client->connected) {
            $this->client->close();
        }
        $this->client = null;
        $this->isConnected = false;
    }
    public function incrementNoResponseCount(): int
    {   
        return ++$this->noServerResponseCount; 
    }

    public function getNoServerResponseLimit(): int
    {
        return $this->noServerResponseLimit;
    }

 
}
