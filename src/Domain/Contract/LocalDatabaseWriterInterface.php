<?php

declare(strict_types=1);

namespace App\Domain\Contract;

interface LocalDatabaseWriterInterface
{
    /**
     * Inserta datos en la tabla datosc2 (carga de planta C2).
     *
     * @param array<string, mixed> $data Datos a insertar.
     */
    public function insertPlantLoadC2(array $data): void;

    /**
     * Inserta datos en la tabla datos_repdia (reporte diario).
     *
     * @param array<string, mixed> $data Datos a insertar.
     */
    public function insertPlantLoadReport(array $data): void;
}
