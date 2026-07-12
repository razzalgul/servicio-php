<?php

declare(strict_types=1);

namespace App\Domain\Contract;

use App\Domain\DTO\HttpResponse;

interface HttpSenderInterface
{
    /**
     * Envía una solicitud POST al endpoint indicado.
     *
     * @param string       $endpoint URL del endpoint destino.
     * @param array|string $payload  Cuerpo de la solicitud (array será codificado como JSON).
     * @return HttpResponse Respuesta del servidor.
     */
    public function post(string $endpoint, array|string $payload): HttpResponse;

    /**
     * Envía con mecanismo de fallback (outbox) en caso de fallo.
     *
     * @param string       $taskType Tipo de tarea para identificar en el outbox.
     * @param string       $endpoint URL del endpoint destino.
     * @param array|string $payload  Cuerpo de la solicitud.
     * @return bool True si el envío fue exitoso, false si se encoló en el outbox.
     */
    public function sendWithFallback(string $taskType, string $endpoint, array|string $payload): bool;
}
