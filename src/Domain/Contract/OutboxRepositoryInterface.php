<?php

declare(strict_types=1);

namespace App\Domain\Contract;

use App\Domain\DTO\OutboxMessage;

interface OutboxRepositoryInterface
{
    /**
     * Encola un mensaje en el outbox para reintento posterior.
     *
     * @param OutboxMessage $message Mensaje a encolar.
     */
    public function enqueue(OutboxMessage $message): void;

    /**
     * Obtiene mensajes pendientes para procesar.
     *
     * @param int $limit Cantidad máxima de mensajes a obtener.
     * @return OutboxMessage[] Lista de mensajes pendientes.
     */
    public function dequeuePending(int $limit = 50): array;

    /**
     * Marca un mensaje como completado exitosamente.
     *
     * @param string $id Identificador único del mensaje.
     */
    public function markAsCompleted(string $id): void;

    /**
     * Incrementa el contador de reintentos y registra el error.
     *
     * @param string $id    Identificador único del mensaje.
     * @param string $error Descripción del error ocurrido.
     */
    public function incrementRetry(string $id, string $error): void;

    /**
     * Elimina mensajes expirados del outbox.
     *
     * @return int Cantidad de mensajes eliminados.
     */
    public function purgeExpired(): int;
}
