<?php

date_default_timezone_set('America/Lima');

// Permite definir fechas por parámetros de consola (CLI) o con valores por defecto
$fecha_inicio_str = $argv[1] ?? '2026-07-01 00:00:00';
$fecha_fin_str = $argv[2] ?? '2026-07-02 00:00:00';

function conexion()
{
    $serverName = "172.16.0.244"; //gr_history
    $connectionOptions = array("Database" => "Runtime", "Uid" => "ccruser", "PWD" => "ccruser@2019", "MultipleActiveResultSets" => 0,"TrustServerCertificate"=>true);
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

echo "Iniciando carga de datos faltantes desde " . $inicio->format('Y-m-d H:i:s') . " hasta " . $fin->format('Y-m-d H:i:s') . PHP_EOL;

$current = clone $inicio;

while ($current < $fin) {
    $start_str = $current->format('Y-m-d H:i:s');
    $current->modify('+1 hour');
    $end_str = $current->format('Y-m-d H:i:s');

    echo "Procesando intervalo: $start_str a $end_str" . PHP_EOL;

    // Dado que es una consulta de datos históricos, reemplazamos AnalogLive por consultas históricas en $end_str
    $queryAvg = "SELECT TagName, AVG(Value) as Value FROM Runtime.dbo.AnalogHistory WHERE TagName IN ('WIT1741.IO.Value','WIT2741.IO.Value') AND DateTime BETWEEN '$start_str' AND '$end_str' GROUP BY TagName";
    $queryTotalNow = "SELECT TagName, Value from Runtime.dbo.AnalogHistory WHERE TagName IN ('WCT1741.Value','WCT2741.Value') AND DateTime = '$end_str'";
    $queryTotalPrev = "SELECT TagName, Value from Runtime.dbo.AnalogHistory WHERE TagName IN ('WCT1741.Value','WCT2741.Value') AND DateTime = '$start_str'";

    $stmt = sqlsrv_query($conn, $queryAvg);
    if ($stmt === false) {
        echo "Mala consulta Avg para el rango $start_str - $end_str" . PHP_EOL;
        print_r(sqlsrv_errors(), true);
        continue;
    }

    $raw_data = new stdClass();
    $raw_data->DateTime = $current->format("Y-m-d H:i");

    while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
        $raw_data->{$row['TagName']} = $row['Value'];
    }

    $stmt = sqlsrv_query($conn, $queryTotalNow);
    if ($stmt === false) {
        echo "Mala consulta TotalNow para $end_str" . PHP_EOL;
        print_r(sqlsrv_errors(), true);
        continue;
    }

    while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
        $raw_data->{$row['TagName']} = $row['Value'];
    }

    $stmt = sqlsrv_query($conn, $queryTotalPrev);
    if ($stmt === false) {
        echo "Mala consulta TotalPrev para $start_str" . PHP_EOL;
        print_r(sqlsrv_errors(), true);
        continue;
    }

    while( $row = sqlsrv_fetch_array( $stmt, SQLSRV_FETCH_ASSOC) ) {
        $raw_data->{$row['TagName']} = $raw_data->{$row['TagName']} - $row['Value'];
    }

    // Si faltan datos clave, evitar enviar campos nulos o con errores
    $post_data = new stdClass();
    $post_data->DateTime = $raw_data->DateTime;
    $post_data->WIT1741 = $raw_data->{"WIT1741.IO.Value"} ?? 0;
    $post_data->WIT2741 = $raw_data->{"WIT2741.IO.Value"} ?? 0;
    $post_data->WCT1741 = $raw_data->{"WCT1741.Value"} ?? 0;
    $post_data->WCT2741 = $raw_data->{"WCT2741.Value"} ?? 0;
    
    $data = json_encode($post_data, 1);

    $proxyIP = '192.168.85.1';
    $proxyPort = '3128';
    $url = "https://172.191.199.255/api/production/filter";

    $ch1 = curl_init();

    curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch1, CURLOPT_SSL_VERIFYHOST, 0);

    curl_setopt_array($ch1, array(
        CURLOPT_URL            => $url,
        CURLOPT_CUSTOMREQUEST  => "POST",
        CURLOPT_POSTFIELDS     => $data,
        CURLOPT_HTTPHEADER     => array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data),
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
        echo "[$end_str] Enviado exitosamente. Mensaje del servidor: " . $codigoRespuesta . " respuesta: " . $resultado . PHP_EOL;
    } else {
        echo "[$end_str] Error al enviar. Mensaje del servidor: " . $codigoRespuesta . " respuesta: " . $resultado . PHP_EOL;
    }
}

echo "Proceso finalizado." . PHP_EOL;

?>
