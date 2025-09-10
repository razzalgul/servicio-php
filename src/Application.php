<?php

namespace App;

use Psr\Log\LoggerInterface;
use Throwable;

class Application
{
    private int $tickInterval;

    public function __construct(
        private Config $config,
        private LoggerInterface $logger,
        private WebSocketClient $wsClient,
        private DataBaseService $dbService,
        private TagRepository $tagRepo,
        private ProductionCalculator $calculator
    ) {
        $this->tickInterval = $this->config->get('app.tick_interval');
    }
    private function filtrarTags(array $allData, array $tagsRequeridos): array
    {
    $filtrados = [];
    foreach ($tagsRequeridos as $tag) {
        if (isset($allData[$tag])) {
            $filtrados[$tag] = $allData[$tag];
        }
    }
    return $filtrados;
    }

    private function roundValues(array $payload): array
{
    $payloadRedondeado = [];
    foreach ($payload as $tag => $valor) {
        if (is_numeric($valor)) {
            if (
                str_contains($tag, 'PIT') ||
                str_contains($tag, 'Rake_Thickness') ||
                str_contains($tag, 'Rotating_Pressure') ||
                str_contains($tag, 'FE00') ||
                str_contains($tag, 'SCEW_R_MINUS')
            ) {
                $payloadRedondeado[$tag] = round($valor, 1);
            } elseif (
                str_contains($tag, 'LIT') ||
                str_contains($tag, 'M_MudLevel') ||
                str_contains($tag, 'Flow_')
            ) {
                $payloadRedondeado[$tag] = round($valor, 2);
            } else {
                $payloadRedondeado[$tag] = round($valor);
            }
        } else {
            $payloadRedondeado[$tag] = $valor;
        }
    }
    return $payloadRedondeado;
}

    public function run(): void
    {
        $this->logger->info("Servicio de datos iniciando...");

        // 1. Obtener la lista de tags de la API una sola vez al iniciar.
        $apiTags = $this->tagRepo->fetchTagsFromApi();
        if (empty($apiTags)) {
            $this->logger->critical("No se pudieron obtener los tags de la API. El servicio no puede continuar.");
            return;
        }

        // Tags fijos necesarios para los cálculos históricos, independientemente de la API.
        $historicalCalculationTags = [
            'WCT5841WE120_002.Value',
            'WCT0303.Value',
            'WCT1741.Value',
            'WCT2741.Value'
        ];
    
            while (true) {
                $startTime = microtime(true);
                try {
                    // 1. Asegurar que el WebSocket esté conectado
                    $this->wsClient->ensureConnected();

                    // 2. Obtener datos de la base de datos
                    $this->logger->debug("Obteniendo datos de la base de datos...");

                    $now = new \DateTimeImmutable();
                    $shiftTimestamps = $this->calculator->getShiftTimestamps($now);
                    // El día de producción anterior comenzó 24 horas (86400s) antes del inicio del día actual.
                    $startOfPreviousDay = date('Y-m-d H:i:s', $shiftTimestamps['inicioDia'] - 86400);

                    $dbData = $this->dbService->getLiveValues();
                    $liveData = $this->filtrarTags($dbData, $apiTags);

                    $historicalData = [
                        'inicioGuardia' => $this->dbService->getHistoricalValues($historicalCalculationTags, date('Y-m-d H:i:s', $shiftTimestamps['inicioTurno'])),
                        'guardiaAnterior' => $this->dbService->getHistoricalValues($historicalCalculationTags, date('Y-m-d H:i:s', $shiftTimestamps['inicioTurnoAnterior'])),
                        'guardiaDiaAnterior' => $this->dbService->getHistoricalValues($historicalCalculationTags, $startOfPreviousDay),
                    ];

                    // 3. Realizar cálculos
                    $this->logger->debug("Realizando cálculos de producción...");
                    $calculatedMetrics = $this->calculator->calculateMetrics($dbData, $historicalData);
                    // 4. Preparar el payload final como un objeto plano tag=>valor
                    $payload = array_merge($liveData, $calculatedMetrics);
                    $payload = $this->roundValues($payload);
                    $wsData = (object)[
                        'type' => 'data',
                        'payload' => $payload
                    ];
                    
                    // 5. Enviar datos
                    if ($this->wsClient->send($wsData)) {
                        $this->logger->info("Datos enviados correctamente al servidor WebSocket.");
                    } else {
                        $this->logger->warning("Fallo al enviar datos. La reconexión se gestionará en el próximo ciclo.");
                    }

                    // 6. Leer mensajes entrantes (si los hay)
                    $incomingMessage = $this->wsClient->readMessage(5);
                    if ($incomingMessage !== null) {
                        $this->logger->info("Mensaje recibido del servidor WebSocket.", ['message' => $incomingMessage]);
                    }
                    else {
                        $this->logger->debug("No se recibieron mensajes del servidor WebSocket en este ciclo.");
                        $this->wsClient->incrementNoResponseCount();
                        $this->logger->debug("Incrementando contador de intentos sin respuesta del servidor. {$this->wsClient->getNoResponseCount()} de {$this->wsClient->getNoServerResponseLimit()}.");
                        // Si no se recibe respuesta del servidor en varios intentos, forzar reconexión
                        if ($this->wsClient->getNoResponseCount() >= $this->wsClient->getNoServerResponseLimit()) {
                            $this->logger->warning("No se ha recibido respuesta del servidor en varios intentos. Forzando reconexión.");
                            $this->wsClient->reconnect();
                        }
                    }

                } catch (Throwable $e) {
                    $this->logger->error("Error en el ciclo principal de la aplicación: " . $e->getMessage(), [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString() // Descomentar para debugging detallado
                    ]);
                    // Esperar un poco antes de reintentar en caso de error para no crear un bucle de fallos rápido.
                    \OpenSwoole\Coroutine::sleep(5);
                } finally {
                    // 6. Esperar hasta el próximo ciclo para mantener el intervalo configurado (e.g., 1 segundo).
                    $endTime = microtime(true);
                    $elapsedMicroseconds = ($endTime - $startTime) * 1000000;
                    $sleepTimeMicroseconds = ($this->tickInterval * 1000000) - $elapsedMicroseconds;

                    if ($sleepTimeMicroseconds > 0) {
                      \OpenSwoole\Coroutine::usleep($sleepTimeMicroseconds);
                    }
                }
            }
       
    }
}
