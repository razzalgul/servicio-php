<?php

declare(strict_types=1);

namespace App\Domain\Contract;

interface TagProviderInterface
{
    /**
     * Obtiene la lista de nombres de tags desde la API remota.
     *
     * @return string[] Lista de nombres de tags.
     */
    public function fetchTagsFromApi(): array;

    /**
     * Obtiene los parámetros de configuración desde la API remota.
     *
     * @return array Lista de objetos/arreglos de parámetros.
     */
    public function fetchParametersFromApi(): array;
}
