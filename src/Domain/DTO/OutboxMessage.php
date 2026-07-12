<?php

declare(strict_types=1);

namespace App\Domain\DTO;

final class OutboxMessage
{
    public function __construct(
        public readonly string $id,
        public readonly string $taskType,
        public readonly string $destination, // 'remote_api' | 'local_mysql'
        public readonly string $endpoint,
        public readonly string $httpMethod,
        public readonly string $payload,
        public readonly string $createdAt,
        public int $attempts = 0,
        public ?string $lastError = null,
        public ?string $updatedAt = null,
    ) {}

    /**
     * Verifica si el mensaje ha excedido el número máximo de reintentos.
     *
     * @param int $maxRetries Número máximo de reintentos permitidos.
     * @return bool True si se superó el límite.
     */
    public function hasExceededMaxRetries(int $maxRetries): bool
    {
        return $this->attempts >= $maxRetries;
    }

    /**
     * Verifica si el mensaje ha expirado según el TTL en horas.
     *
     * @param int $ttlHours Tiempo de vida máximo en horas.
     * @return bool True si el mensaje ha expirado.
     */
    public function hasExpired(int $ttlHours): bool
    {
        $created = new \DateTimeImmutable($this->createdAt);
        $now = new \DateTimeImmutable();
        $diffHours = ($now->getTimestamp() - $created->getTimestamp()) / 3600;
        return $diffHours > $ttlHours;
    }
}
