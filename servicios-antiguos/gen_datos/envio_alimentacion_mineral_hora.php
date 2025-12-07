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

$actualDate = date("Y-m-d H:i:00");
$prevHourDate = date('Y-m-d H:i:00',strtotime('-1 hour'));
$prevHourDateObject =date_create($prevHourDate);
$plantFeed = new stdClass();
$plantFeed->date = $prevHourDate;

// 1. Obtener valor actual
$queryNow = "SELECT Value FROM Runtime.dbo.AnalogLive WHERE TagName = 'WCT0303.Value'";
$stmtNow = sqlsrv_query($conn, $queryNow);
$rowNow = sqlsrv_fetch_array($stmtNow, SQLSRV_FETCH_ASSOC);
$currentValue = ($rowNow !== null) ? $rowNow['Value'] : null;

// 2. Obtener valor de la hora anterior
$queryPrev = "SELECT Value FROM Runtime.dbo.AnalogHistory WHERE TagName = 'WCT0303.Value' AND DateTime = '$prevHourDate'";
$stmtPrev = sqlsrv_query($conn, $queryPrev);
$rowPrev = sqlsrv_fetch_array($stmtPrev, SQLSRV_FETCH_ASSOC);
$previousValue = ($rowPrev !== null) ? $rowPrev['Value'] : null;

// 3. Calcular la cantidad, si ambos valores existen. Si no, es 0.
$hourlyQuantity = 0;
if ($currentValue !== null && $previousValue !== null) {
    $hourlyQuantity = $currentValue - $previousValue;
} else {
    echo "Advertencia: No se encontró valor actual o histórico para WCT0303.Value. Enviando 0.".PHP_EOL;
}

$plantFeed->quantity = $hourlyQuantity;

$postData =json_encode($plantFeed);

$url = "https://172.191.199.255/api/mineralfeed/hour";

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
