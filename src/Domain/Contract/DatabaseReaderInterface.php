<?php

declare(strict_types=1);

namespace App\Domain\Contract;

interface DatabaseReaderInterface
{
    /**
     * Retorna los valores en vivo de la tabla Live.
     *
     * @return array<string, mixed> Arreglo asociativo [TagName => value]
     */
    public function getLiveValues(): array;

    /**
     * Retorna valores históricos en un momento específico.
     *
     * @param string[] $tagNames Lista de nombres de tags a consultar.
     * @param string   $dateTime Fecha y hora en formato compatible con SQL Server.
     * @return array<string, mixed> Arreglo asociativo [TagName => value]
     */
    public function getHistoricalValues(array $tagNames, string $dateTime): array;

    /**
     * Retorna el historial de eventos para los tags dados.
     *
     * @param string[] $tagNames Lista de nombres de tags.
     * @return array<int, mixed> Arreglo de registros de eventos.
     */
    public function getHistoricalEvents(array $tagNames): array;

    /**
     * Retorna la diferencia entre el valor live actual y el valor a la hora anterior
     * para cada tag.
     *
     * @param string[] $tagNames Lista de nombres de tags.
     * @param string   $prevHour Hora anterior en formato 'Y-m-d H:i:00'.
     * @return array<string, float> Arreglo asociativo [TagName => delta_value]
     */
    public function getHourlyTagDelta(array $tagNames, string $prevHour): array;

    /**
     * Retorna el promedio (AVG) de valores para cada tag en los últimos N minutos.
     *
     * @param string[] $tagNames    Lista de nombres de tags.
     * @param int      $minutesBack Cantidad de minutos hacia atrás para promediar.
     * @return array<string, float> Arreglo asociativo [TagName => avg_value]
     */
    public function getAverageValues(array $tagNames, int $minutesBack): array;
}
