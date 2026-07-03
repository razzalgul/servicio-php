<?php

date_default_timezone_set('America/Lima');

// Permite definir fechas por parámetros de consola (CLI) o con valores por defecto
$fecha_inicio_str =  '2026-06-24 22:00:00';
$fecha_fin_str = '2026-07-03 04:00:00';


function conexion()
{
    $serverName = "172.16.0.244"; //gr_history
    $connectionOptions = array("Database" => "Runtime", "Uid" => "ccruser", "PWD" => "ccruser@2019", "MultipleActiveResultSets" => 0, "TrustServerCertificate" => true);
    $conn = sqlsrv_connect($serverName, $connectionOptions);
    if ($conn === false) {
        echo "Habilitar para conectar.</br>";
        die(print_r(sqlsrv_errors(), true));
    }

    return $conn;
}

$conn = conexion();

$inicio = new DateTime($fecha_inicio_str);
$fin = new DateTime($fecha_fin_str);

echo "Iniciando carga de datos faltantes de Produccion Hora desde " . $inicio->format('Y-m-d H:i:s') . " hasta " . $fin->format('Y-m-d H:i:s') . PHP_EOL;

$current = clone $inicio;

while ($current < $fin) {
    $start_str = $current->format('Y-m-d H:i:00');
    $current->modify('+1 hour');
    $end_str = $current->format('Y-m-d H:i:00');

    echo "Procesando intervalo: $start_str a $end_str" . PHP_EOL;

    // 1. Obtener valores al final de la hora (usando AnalogHistory en lugar de AnalogLive)
    $queryNow = "SELECT TagName, Value FROM Runtime.dbo.AnalogHistory WHERE TagName IN ('WCT1741.Value','WCT2741.Value') AND DateTime = '$end_str'";
    $stmtNow = sqlsrv_query($conn, $queryNow);
    if ($stmtNow === false) {
        echo "Mala consulta Now para $end_str" . PHP_EOL;
        print_r(sqlsrv_errors(), true);
        continue;
    }
    $currentValues = [];
    while ($row = sqlsrv_fetch_array($stmtNow, SQLSRV_FETCH_ASSOC)) {
        $currentValues[$row['TagName']] = $row['Value'];
    }

    // 2. Obtener valores al inicio de la hora
    $queryPrev = "SELECT TagName, Value FROM Runtime.dbo.AnalogHistory WHERE TagName IN ('WCT1741.Value','WCT2741.Value') AND DateTime = '$start_str'";
    $stmtPrev = sqlsrv_query($conn, $queryPrev);
    if ($stmtPrev === false) {
        echo "Mala consulta Prev para $start_str" . PHP_EOL;
        print_r(sqlsrv_errors(), true);
        continue;
    }
    $previousValues = [];
    while ($row = sqlsrv_fetch_array($stmtPrev, SQLSRV_FETCH_ASSOC)) {
        $previousValues[$row['TagName']] = $row['Value'];
    }

    // 3. Calcular la cantidad para cada línea, si ambos valores existen. Si no, es 0.
    $quantityL1 = 0;
    if (isset($currentValues['WCT1741.Value']) && isset($previousValues['WCT1741.Value'])) {
        $quantityL1 = $currentValues['WCT1741.Value'] - $previousValues['WCT1741.Value'];
    } else {
        echo "Advertencia: No se encontró valor actual o histórico para WCT1741.Value en el rango $start_str a $end_str. Enviando 0." . PHP_EOL;
    }

    $quantityL2 = 0;
    if (isset($currentValues['WCT2741.Value']) && isset($previousValues['WCT2741.Value'])) {
        $quantityL2 = $currentValues['WCT2741.Value'] - $previousValues['WCT2741.Value'];
    } else {
        echo "Advertencia: No se encontró valor actual o histórico para WCT2741.Value en el rango $start_str a $end_str. Enviando 0." . PHP_EOL;
    }

    $postData = [];
    $lineOneHourlyData = new stdClass();
    $lineOneHourlyData->quantity = $quantityL1;
    $lineOneHourlyData->productionLineId = 1;
    $lineOneHourlyData->date = $start_str; // Se asocia la fecha de inicio del intervalo
    array_push($postData, $lineOneHourlyData);

    $lineTwoHourlyData = new stdClass();
    $lineTwoHourlyData->quantity = $quantityL2;
    $lineTwoHourlyData->productionLineId = 2;
    $lineTwoHourlyData->date = $start_str; // Se asocia la fecha de inicio del intervalo
    array_push($postData, $lineTwoHourlyData);

    $postData = json_encode($postData);

    $url = "https://172.191.199.255/api/production/hourly";

    $ch1 = curl_init();

    curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch1, CURLOPT_SSL_VERIFYHOST, 0);

    curl_setopt_array($ch1, array(
        CURLOPT_URL            => $url,
        CURLOPT_CUSTOMREQUEST  => "POST",
        CURLOPT_POSTFIELDS     => $postData,
        CURLOPT_HTTPHEADER     => array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($postData),
        ),
        CURLOPT_CONNECTTIMEOUT => 0,
        CURLOPT_TIMEOUT_MS     => 3000,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER         => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_ENCODING       => "",
        CURLOPT_AUTOREFERER    => true,
        CURLOPT_CONNECTTIMEOUT => 120,
        CURLOPT_TIMEOUT        => 120,
        CURLOPT_MAXREDIRS      => 10,
    ));

    $resultado = curl_exec($ch1);
    $codigoRespuesta = curl_getinfo($ch1, CURLINFO_HTTP_CODE);
    curl_close($ch1);

    if($codigoRespuesta === 200 || $codigoRespuesta === 100){
        echo "[$start_str] Enviado exitosamente. Mensaje del servidor: " . $codigoRespuesta . " respuesta: " . $resultado . PHP_EOL;
    } else {
        echo "[$start_str] Error al enviar. Mensaje del servidor: " . $codigoRespuesta . " respuesta: " . $resultado . PHP_EOL;
    }
}

echo "Proceso de Produccion Hora finalizado." . PHP_EOL;

?>
