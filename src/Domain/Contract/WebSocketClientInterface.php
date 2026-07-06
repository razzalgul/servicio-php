<?php

declare(strict_types=1);

namespace App\Domain\Contract;

interface WebSocketClientInterface
{
    /**
     * Asegura que la conexión WebSocket esté activa, reconectando si es necesario.
     */
    public function ensureConnected(): void;

    /**
     * Envía datos al servidor WebSocket.
     *
     * @param array|object $data Datos a enviar (serán codificados como JSON).
     * @return bool True si el envío fue exitoso.
     */
    public function send(array|object $data): bool;

    /**
     * Lee un mensaje del servidor WebSocket.
     *
     * @param int $timeout Tiempo máximo de espera en segundos.
     * @return string|null Mensaje recibido o null si no hay respuesta.
     */
    public function readMessage(int $timeout = 5): ?string;

    /**
     * Fuerza una reconexión al servidor WebSocket.
     */
    public function reconnect(): void;

    /**
     * Cierra la conexión WebSocket.
     */
    public function close(): void;

    /**
     * Incrementa el contador de respuestas sin recibir del servidor.
     *
     * @return int El nuevo valor del contador.
     */
    public function incrementNoResponseCount(): int;

    /**
     * Obtiene el contador actual de respuestas sin recibir.
     *
     * @return int Cantidad de veces sin respuesta.
     */
    public function getNoResponseCount(): int;

    /**
     * Obtiene el límite máximo de respuestas sin recibir antes de reconectar.
     *
     * @return int Límite configurado.
     */
    public function getNoServerResponseLimit(): int;
}
