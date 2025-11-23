<?php

namespace App;

class ProductionCalculator
{
    // Constantes para factores de cálculo y "números mágicos"
    private const TON_PRODUCCION_DISENO_PER_SEC = 0.3935;
    private const TON_ENVIO_DISENO_PER_SEC = 0.5346;
    private const TON_ENVIO_FAJA0_DISENO_PER_SEC = 0.5469;
    private const POWER_MOLINO_MAX = 8500;

    private int $metaMes = 900053;

    /**
     * Calcula todas las métricas de producción basadas en datos en vivo e históricos.
     *
     * @param array $liveData Datos en tiempo real de la tabla Live.
     * @param array $historicalData Datos históricos de los inicios de turno, semana y mes.
     * @return array Un array asociativo con todas las métricas calculadas.
     */




    public function calculateMetrics(array $liveData, array $historicalData): array
    {
        $now = new \DateTimeImmutable();
        $shiftTimes = $this->getShiftTimestamps($now);

        // Determina qué datos de inicio de día usar según la hora actual.
        $tonelajeInicioDia = ($now->getTimestamp() - $shiftTimes['inicioDia'] >= 43200) // 12 horas (43200s)
            ? $historicalData['guardiaAnterior']
            : $historicalData['inicioGuardia'];

        $segundosTurno = $now->getTimestamp() - $shiftTimes['inicioTurno'];
        $segundosDia = $now->getTimestamp() - $shiftTimes['inicioDia'];

        $calculations = [];

        // --- 1. Tonelajes de Diseño (Metas) ---
        $calculations['tonelajedisenodia'] = $segundosDia * self::TON_PRODUCCION_DISENO_PER_SEC;
        $calculations['tonelajeFaja0Disenodia'] = $segundosDia * self::TON_ENVIO_FAJA0_DISENO_PER_SEC;
        $calculations['tonelajeenviodisenodia'] = $segundosDia * self::TON_ENVIO_DISENO_PER_SEC;
        $calculations['tonelajedisenoTurno'] = $segundosTurno * self::TON_PRODUCCION_DISENO_PER_SEC;
        $calculations['tonelajeFaja0DisenoTurno'] = $segundosTurno * self::TON_ENVIO_FAJA0_DISENO_PER_SEC;
        $calculations['tonelajeenviodisenoturno'] = $segundosTurno * self::TON_ENVIO_DISENO_PER_SEC;

        // --- 2. Tonelajes del Día Anterior (8am a 8am) ---
        $calculations['tonelajeDiaAnterior'] = ($tonelajeInicioDia['WCT1741.Value'] + $tonelajeInicioDia['WCT2741.Value']) - ($historicalData['guardiaDiaAnterior']['WCT1741.Value'] + $historicalData['guardiaDiaAnterior']['WCT2741.Value']);
        $calculations['tonelajeenviodiaanterior'] = $tonelajeInicioDia['WCT0303.Value'] - $historicalData['guardiaDiaAnterior']['WCT0303.Value'];
        $calculations['tonelajeFaja0DiaAnterior'] = $tonelajeInicioDia['WCT5841WE120_002.Value'] - $historicalData['guardiaDiaAnterior']['WCT5841WE120_002.Value'];

        // --- 3. Tonelajes Acumulados del Día Actual (desde las 8am) ---
        $calculations['tonelajeEnvioActualDia'] = $liveData['WCT0303.Value'] - $tonelajeInicioDia['WCT0303.Value'];
        $calculations['tonelajeActualDia'] = ($liveData['WCT1741.Value'] + $liveData['WCT2741.Value']) - ($tonelajeInicioDia['WCT1741.Value'] + $tonelajeInicioDia['WCT2741.Value']);
        $calculations['tonelajeFaja0ActualDia'] = $liveData['WCT5841WE120_002.Value'] - $tonelajeInicioDia['WCT5841WE120_002.Value'];

        // --- 4. Tonelajes de la Guardia Anterior Completa ---
        $calculations['tonelajeguardiaanterior'] = ($historicalData['inicioGuardia']['WCT1741.Value'] + $historicalData['inicioGuardia']['WCT2741.Value']) - ($historicalData['guardiaAnterior']['WCT1741.Value'] + $historicalData['guardiaAnterior']['WCT2741.Value']);
        $calculations['tonelajeenvioguardiaanterior'] = $historicalData['inicioGuardia']['WCT0303.Value'] - $historicalData['guardiaAnterior']['WCT0303.Value'];
        $calculations['tonelajeFaja0TurnoAnterior'] = $historicalData['inicioGuardia']['WCT5841WE120_002.Value'] - $historicalData['guardiaAnterior']['WCT5841WE120_002.Value'];

        // --- 5. Tonelajes Acumulados del Turno Actual ---
        $tonelajeActualTurno = ($liveData['WCT1741.Value'] + $liveData['WCT2741.Value']) - ($historicalData['inicioGuardia']['WCT1741.Value'] + $historicalData['inicioGuardia']['WCT2741.Value']);
        $calculations['tonelajeactualturno'] = $tonelajeActualTurno;
        $tonelajeEnvioActualTurno = $liveData['WCT0303.Value'] - $historicalData['inicioGuardia']['WCT0303.Value'];
        $calculations['tonelajeEnvioActualTurno'] = $tonelajeEnvioActualTurno;
        $tonelajeFaja0ActualTurno = $liveData['WCT5841WE120_002.Value'] - $historicalData['inicioGuardia']['WCT5841WE120_002.Value'];
        $calculations['tonelajeFaja0ActualTurno'] = $tonelajeFaja0ActualTurno;

        // --- 6. Diferencias vs. Diseño (Variaciones) ---
        // Variación del turno
        $calculations['diferenciaproduccionturno'] = $tonelajeActualTurno - $calculations['tonelajedisenoTurno'];
        $calculations['diferenciaenvioturno'] = $tonelajeEnvioActualTurno - $calculations['tonelajeenviodisenoturno'];
        $calculations['diferenciaTonelajeFaja0Turno'] = $tonelajeFaja0ActualTurno - $calculations['tonelajeFaja0DisenoTurno'];
        // Variación del día
        $calculations['diferenciaproducciondia'] = $calculations['tonelajeActualDia'] - $calculations['tonelajedisenodia'];
        $calculations['diferenciaenviodia'] = $calculations['tonelajeEnvioActualDia'] - $calculations['tonelajeenviodisenodia'];
        $calculations['diferenciaTonelajeFaja0Dia'] = $calculations['tonelajeFaja0ActualDia'] - $calculations['tonelajeFaja0Disenodia'];

        // --- 7. Cálculos Adicionales de Proceso ---
        $calculations['presion_procesos_l1'] = max($liveData['PIT0922A.IO.Value'] ?? 0, $liveData['PIT0922B.IO.Value'] ?? 0, $liveData['PIT0922C.IO.Value'] ?? 0);
        $calculations['presion_procesos_l2'] = max($liveData['PIT0921A.IO.Value'] ?? 0, $liveData['PIT0921B.IO.Value'] ?? 0, $liveData['PIT0921C.IO.Value'] ?? 0);

        $calculations['porcentajeMolino1001'] = round((($liveData['5740_BM1001.M101_POW'] ?? 0) / self::POWER_MOLINO_MAX) * 100, 2);
        $calculations['porcentajeMolino1002'] = round((($liveData['5740_BM1002.M101_POW'] ?? 0) / self::POWER_MOLINO_MAX) * 100, 2);
        $calculations['porcentajeMolino2001'] = round((($liveData['5740_BM2001.M101_POW'] ?? 0) / self::POWER_MOLINO_MAX) * 100, 2);
        $calculations['porcentajeMolino2002'] = round((($liveData['5740_BM2002.M101_POW'] ?? 0) / self::POWER_MOLINO_MAX) * 100, 2);
        $calculations['produccion_l1'] = ($liveData['WCT1741.Value'] ?? 0) - ($tonelajeInicioDia['WCT1741.Value'] ?? 0);
        $calculations['produccion_l2'] = ($liveData['WCT2741.Value'] ?? 0) - ($tonelajeInicioDia['WCT2741.Value'] ?? 0);

        // --- 8. Porcentaje de Cumplimiento vs. Metas ---
        $diasDelMes = (int)$now->format('t');
        $metaDiaria = $diasDelMes > 0 ? $this->metaMes / $diasDelMes : 0;
        $metaSemanal = $diasDelMes > 0 ? ($this->metaMes / $diasDelMes) * 7 : 0;

        // Producción actual del mes
        $produccionActualMes = (($liveData['WCT1741.Value'] ?? 0) + ($liveData['WCT2741.Value'] ?? 0)) - (($historicalData['inicioMes']['WCT1741.Value'] ?? 0) + ($historicalData['inicioMes']['WCT2741.Value'] ?? 0));

        // Producción actual de la semana
        $produccionActualSemana = (($liveData['WCT1741.Value'] ?? 0) + ($liveData['WCT2741.Value'] ?? 0)) - (($historicalData['inicioSemana']['WCT1741.Value'] ?? 0) + ($historicalData['inicioSemana']['WCT2741.Value'] ?? 0));

        // Producción actual del día ya está en $calculations['tonelajeActualDia']

        $calculations['pCumpDia'] = round($metaDiaria > 0 ? ($calculations['tonelajeActualDia'] / $metaDiaria) * 100 : 0, 2);
        $calculations['pCumpSemana'] = round($metaSemanal > 0 ? ($produccionActualSemana / $metaSemanal) * 100 : 0, 2);
        $calculations['pCumpMes'] = round($this->metaMes > 0 ? ($produccionActualMes / $this->metaMes) * 100 : 0, 2);


        // Conteo de filtros activos
        $filtros_l1 = 0;
        $filtros_l2 = 0;
        for ($i = 1; $i <= 10; $i++) {
            $numeroFiltro = str_pad($i, 2, '0', STR_PAD_LEFT);
            if (($liveData["VF10{$numeroFiltro}.HZ1"] ?? 0) > 0 && ($liveData["VF10{$numeroFiltro}.HZ2"] ?? 0) > 0) {
                $filtros_l1++;
            }
            if (($liveData["VF20{$numeroFiltro}.HZ1"] ?? 0) > 0 && ($liveData["VF20{$numeroFiltro}.HZ2"] ?? 0) > 0) {
                $filtros_l2++;
            }
        }
        $calculations['filtros_l1'] = $filtros_l1;
        $calculations['filtros_l2'] = $filtros_l2;


        //agregamos la hora de consulta
        $year = (int)$liveData['SysDateYear'];
        $month = str_pad((int)$liveData['SysDateMonth'], 2, '0', STR_PAD_LEFT);
        $day = str_pad((int)$liveData['SysDateDay'], 2, '0', STR_PAD_LEFT);
        $hour = str_pad((int)$liveData['SysTimeHour'], 2, '0', STR_PAD_LEFT);
        $min = str_pad((int)$liveData['SysTimeMin'], 2, '0', STR_PAD_LEFT);
        $sec = str_pad((int)$liveData['SysTimeSec'], 2, '0', STR_PAD_LEFT);
     
        $dateTime = \DateTime::createFromFormat(
            'Y-m-d H:i:s',
            "$year-$month-$day $hour:$min:$sec"
        );
        if ($dateTime !== false) {
            $calculations['queryTime'] = $dateTime->format('H:i:s a');
        }
        return $calculations;
    }

    /**
     * Determina los timestamps de inicio para el turno actual, el turno anterior y el día.
     * El día de producción empieza a las 08:00.
     * Turno 1: 08:00 - 19:59
     * Turno 2: 20:00 - 07:59 del día siguiente
     *
     * @param \DateTimeInterface $now La fecha y hora actual.
     * @return array Un array con los timestamps de 'inicioTurno', 'inicioTurnoAnterior', 'inicioDia'.
     */
    public function getShiftTimestamps(\DateTimeInterface $now): array
    {
        $currentHour = (int)$now->format('H');
        if ($currentHour >= 8 && $currentHour < 20) {
            // Estamos en el primer turno (8am - 8pm)
            $inicioTurno = $now->setTime(8, 0, 0)->getTimestamp();
            $inicioTurnoAnterior = $now->modify('-1 day')->setTime(20, 0, 0)->getTimestamp();
            $inicioDia = $inicioTurno;
        } else {
            // Estamos en el segundo turno (noche)
            if ($currentHour >= 20) {
                // Noche del mismo día (8pm - medianoche)
                $inicioTurno = $now->setTime(20, 0, 0)->getTimestamp();
                $inicioTurnoAnterior = $now->setTime(8, 0, 0)->getTimestamp();
                $inicioDia = $inicioTurnoAnterior;
            } else {
                // Madrugada del día siguiente (medianoche - 8am)
                $inicioTurno = $now->modify('-1 day')->setTime(20, 0, 0)->getTimestamp();
                $inicioTurnoAnterior = $now->modify('-1 day')->setTime(8, 0, 0)->getTimestamp();
                $inicioDia = $inicioTurnoAnterior;
            }
        }

        return [
            'inicioTurno' => $inicioTurno,
            'inicioTurnoAnterior' => $inicioTurnoAnterior,
            'inicioDia' => $inicioDia,
        ];
    }
}
