<?php
date_default_timezone_set('America/Lima');

function conexion()
{

    $serverName = "172.16.0.244"; //gr_history
    //$serverName = "172.16.0.72";  //gr
    //$connectionOptions = array(“Database” => “Runtime”);  // otro master
    $connectionOptions = array("Database" => "Runtime", "Uid" => "ccruser", "PWD" => "ccruser@2019", "MultipleActiveResultSets" => 0,"TrustServerCertificate"=>true);
    $conn = sqlsrv_connect($serverName, $connectionOptions);
    if ($conn === false) {
        echo "Habilitar para conectar.</br>";
        die(print_r(sqlsrv_errors(), true));
    } 


    return $conn;
}

$conn = conexion();

$prevHour = date('Y-m-d H:i:00',strtotime('-1 hour'));

// 1. Obtener valores actuales
$queryNow = "SELECT TagName, Value FROM Runtime.dbo.AnalogLive WHERE TagName IN ('WCT1741.Value','WCT2741.Value')";
$stmtNow = sqlsrv_query($conn, $queryNow);
$currentValues = [];
while ($row = sqlsrv_fetch_array($stmtNow, SQLSRV_FETCH_ASSOC)) {
    $currentValues[$row['TagName']] = $row['Value'];
}

// 2. Obtener valores de la hora anterior
$queryPrev = "SELECT TagName, Value FROM Runtime.dbo.AnalogHistory WHERE TagName IN ('WCT1741.Value','WCT2741.Value') AND DateTime = '$prevHour'";
$stmtPrev = sqlsrv_query($conn, $queryPrev);
$previousValues = [];
while ($row = sqlsrv_fetch_array($stmtPrev, SQLSRV_FETCH_ASSOC)) {
    $previousValues[$row['TagName']] = $row['Value'];
}

// 3. Calcular la cantidad para cada línea, si ambos valores existen. Si no, es 0.
$quantityL1 = 0;
if (isset($currentValues['WCT1741.Value']) && isset($previousValues['WCT1741.Value'])) {
    $quantityL1 = $currentValues['WCT1741.Value'] - $previousValues['WCT1741.Value'];
} else {
    echo "Advertencia: No se encontró valor actual o histórico para WCT1741.Value. Enviando 0.".PHP_EOL;
}

$quantityL2 = 0;
if (isset($currentValues['WCT2741.Value']) && isset($previousValues['WCT2741.Value'])) {
    $quantityL2 = $currentValues['WCT2741.Value'] - $previousValues['WCT2741.Value'];
} else {
    echo "Advertencia: No se encontró valor actual o histórico para WCT2741.Value. Enviando 0.".PHP_EOL;
}

$postData = [];
$lineOneHourlyData = new stdClass();
$lineOneHourlyData->quantity = $quantityL1;
$lineOneHourlyData->productionLineId = 1;
$lineOneHourlyData->date = $prevHour;
array_push($postData, $lineOneHourlyData);

$lineTwoHourlyData = new stdClass();
$lineTwoHourlyData->quantity = $quantityL2;
$lineTwoHourlyData->productionLineId = 2;
$lineTwoHourlyData->date = $prevHour;
array_push($postData, $lineTwoHourlyData);

$postData =json_encode($postData);


$url = "https://172.191.199.255/api/production/hourly";

$ch1=curl_init();

curl_setopt($ch1,CURLOPT_SSL_VERIFYPEER,false);
  curl_setopt($ch1,CURLOPT_SSL_VERIFYHOST,0);

  curl_setopt_array($ch1, array(
    
    
  CURLOPT_URL            => $url,
  // Indicar que vamos a hacer una petición POST
  CURLOPT_CUSTOMREQUEST => "POST",
  // Justo aquí ponemos los datos dentro del cuerpo
  CURLOPT_POSTFIELDS => $postData,

  // Encabezados
  //CURLOPT_HEADER => true,
  CURLOPT_HTTPHEADER => array(
     'Content-Type: application/json',
     'Content-Length: ' . strlen($postData), // Abajo podríamos agregar más encabezados
   ),
  # indicar que regrese los datos, no que los imprima directamente
  CURLOPT_CONNECTTIMEOUT=>0,
  CURLOPT_TIMEOUT_MS=>3000,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_HEADER         => true,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_ENCODING       => "",
  CURLOPT_AUTOREFERER    => true,
  CURLOPT_CONNECTTIMEOUT => 120,
  CURLOPT_TIMEOUT        => 120,
  CURLOPT_MAXREDIRS      => 10,
  ));

# Hora de hacer la petición
$resultado = curl_exec($ch1);
# Vemos si el código es 200, es decir, HTTP_OK
//echo var_dump($datos);
$codigoRespuesta = curl_getinfo($ch1, CURLINFO_HTTP_CODE);
if($codigoRespuesta === 200 || $codigoRespuesta === 100){
  # Decodificar JSON porque esa es la respuesta
  //$respuestaDecodificada = json_decode($resultado);
  # Simplemente los imprimimos
  //echo $url_data;
  echo "Mensaje del servidor: " .$codigoRespuesta."respuesta: ".$resultado.PHP_EOL;
  //echo "<br><strong>INTERVALO DE ENVIO: </strong>".$t_refresh;
  //echo " Segundos";
  //echo "<br><strong>TRANSFIRIENDO:... </strong>" .$respuestaDecodificada->tramadatos;

}
else {

echo "Mensaje del servidor: " .$codigoRespuesta."respuesta: ".$resultado.PHP_EOL;
}


?>