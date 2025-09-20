<?php

date_default_timezone_set('America/Lima');
$hztorpm =(1786/1000);
function conexion(){
	
$serverName = "172.16.0.244"; //gr_history
	//$serverName = "172.16.0.72";  //gr

	//$connectionOptions = array(“Database” => “Runtime”);
	$connectionOptions = array("Database"=>"master","Uid"=>"ccruser", "PWD"=>"ccruser@2019","MultipleActiveResultSets"=>"0");
	$conn = sqlsrv_connect( $serverName,$connectionOptions);
	if( $conn === false ){
		echo "Habilitar para conectar. \n";
	  die( print_r( sqlsrv_errors(), true));
	}else{
		echo "Conectado a la base de datos GR_HISTORY. \n";
	}
	return $conn;
}

function ConsultaDB(){

	$frec_z_l1=null;
	$amp_z_l1=null;
	$flow_z_l1=null;
	$modo_l1=null;
	$frec_z_l2=null;
	$amp_z_l2=null;
	$flow_z_l2=null;
	$modo_l2=null;



  $mfecha = date('Y-m-d H:i:00');
  $mhora = date('H:i:00');
	$conn=conexion();
	//$fecha = date('Y-m-d g:i:sa');

	$sql = "SELECT TagName, round(AVG(Value),2) as pvalor from Runtime.dbo.History WHERE TagName IN
   ('5730_PU0001_rev1.frecuencyRpm','5730_PU0002_rev1.frecuencyRpm','5730_PU0003_rev1.frecuencyRpm','5730_PU0004_rev1.frecuencyRpm','5730_PU0001.status.TotalCurrent',
   '5730_PU0002.status.TotalCurrent','5730_PU0003.status.TotalCurrent','5730_PU0004.status.TotalCurrent','FIT1301.IO.Value','FIT2301.IO.Value',
'WIT0303.IO.Value',
		'WIT0304.IO.Value',
		'5841_CB120.WIT',
		'5841_CB120_M103_Alarm.Current',
		'5730_CB0001_Current1M.IO.Value',
		'5730_CB0003.status.Iavg',

		'FIT0962.IO.Value',
		'FIT0963.IO.Value',
		'FIT1801.IO.Value',
		'FIT2801.IO.Value',

		'5780_MX1001.status.Iavg',
		'5780_MX2001.status.Iavg',
		'LIT1803.IO.Value',
		'LIT2803.IO.Value',

		'Shouxin_601_PU01A.Frecuency',
		'Shouxin_601_PU02A.Frecuency',
		'Shouxin_601_PU01B.Frecuency',
		'Shouxin_601_PU02B.Frecuency',
		'Shouxin_601_PU01C.Frecuency',
		'Shouxin_601_PU02C.Frecuency',

		'Shouxin_601_PIT_016.Value',
		'Shouxin_601_PIT_017.Value',
		'Shouxin_601_PIT_018.Value',
		'DIT1801.IO.Value',

		'5780_PU1001.Status.FrequencyPv',
		'5780_PU1001.Status.Current',
		'5780_PU1002.Status.FrequencyPv',
		'5780_PU1002.Status.Current',
		'5780_PU2001.Status.FrequencyPv',
		'5780_PU2001.Status.Current',
		'5780_PU2002.Status.FrequencyPv',
		'5780_PU2002.Status.Current',

		'WIT1741.IO.Value',
		'WIT2741.IO.Value',
		'DIT2001_TV_Densidad.IO.Value',
		'DIT2001_PV.IO.Value',
		'WCT5841WE120.Value',
		'WCT0303.Value',
		'WI1802.IO.Value'
		
		)and DateTime BETWEEN DateAdd(ss,-60,GetDate()) and GetDate() GROUP BY TagName";

  $stmt = sqlsrv_query( $conn, $sql );
  if( $stmt === false) {
    echo "Mala consulta";
    die( print_r( sqlsrv_errors(), true) );
  }

  $jsonarray['init']=1;
  while( $row = sqlsrv_fetch_array( $stmt,SQLSRV_FETCH_ASSOC) ) {
    $jsonarray[$row['TagName']] = $row['pvalor'];
  }

  sqlsrv_free_stmt($stmt);
  sqlsrv_close($conn);

  $jsondata = json_encode($jsonarray);          //no lo entenderias -_- // nunca lo sabremos .-. --------> haces pendejadas (-.-)
  $jsondecode = json_decode($jsondata, true);

  //echo $jsondata;
  //echo $jsondecode->5730_PU0002.status.Speed;
  //$jsondecode = json_decode($jsondata);
  //echo $jsondecode["5730_PU0001.status.Speed"];

  $query = "INSERT INTO datosc2 (id, fecha, hora, linea, modo, frec, amp, sol, flujo, ton) VALUES ";
	$query2 = "INSERT INTO datos_repdia (id, fecha, hora, WIT0304_IO_Value, p5841_CB120_WIT, p5841_CB120_M103_Alarm_M_II,
	p5730_CB0001_Current1M_IO_Value, p5730_CB0003_status_Iavg, flujo_1001, flujo_2001, FIT0963_IO_Value, FIT0962_IO_Value, relmodol1, sol_rel1,
	FIT1801_IO_Value, ton_rel1, amp_rel1, frec_rel1, relmodol2, sol_rel2, FIT2801_IO_Value, ton_rel2, amp_rel2, frec_rel2,
	p5780_MX1001_status_Iavg, p5780_MX2001_status_Iavg, LIT1803_IO_Value, LIT2803_IO_Value, frecba_shoux, frecbb_shoux, frecbc_shoux,
	Shouxin_601_PIT_018_Value, Shouxin_601_PIT_017_Value, Shouxin_601_PIT_016_Value, relmallal1, relmallal2, DIT1801_IO_alue, WCT5841, WCT0303,WIT0303_IO_Value) VALUES ";
  //$mfecha = date('Y-m-d H:i:s');
  //$mhora = date('H:i');



/// bd 1---------------------------------------------------------------------------
  //CONDICION l1
  if($jsondecode["5730_PU0001_rev1.frecuencyRpm"] > 1200 && $jsondecode["5730_PU0002_rev1.frecuencyRpm"] < 750){
    //modo disenio

    $frec = (int)$jsondecode["5730_PU0001_rev1.frecuencyRpm"];
    $amp = $jsondecode["5730_PU0001.status.TotalCurrent"];
    $flow = (int)$jsondecode["FIT1301.IO.Value"];
    $query .= "(NULL, '$mfecha', '$mhora', '1', '1', '$frec', '". str_replace( ',', '.', $amp). "', NULL, '$flow', NULL)";

		$frec_z_l1 = $frec;
		$amp_z_l1 = $amp;
		$flow_z_l1 = $flow;
		$modo_l1=1;

  }else if($jsondecode["5730_PU0002_rev1.frecuencyRpm"] > 360){
    //modo modificado
    $frec = (int)$jsondecode["5730_PU0002_rev1.frecuencyRpm"];
    $amp = $jsondecode["5730_PU0002.status.TotalCurrent"];
    $query .= "(NULL, '$mfecha', '$mhora', '1', '2', '$frec', '". str_replace( ',', '.', $amp). "', NULL, NULL, NULL)";

		$frec_z_l1 = $frec;
		$amp_z_l1 = $amp;
		$modo_l1=2;

  }else{
		$frec = (int)$jsondecode["5730_PU0002_rev1.frecuencyRpm"];
    $amp = $jsondecode["5730_PU0002.status.TotalCurrent"];
	//parado
	//$value =1.23;		//'". str_replace( ',', '.', $value ). "'
		$frec_z_l1 = $frec;
		$amp_z_l1 = $amp;
    $query .= "(NULL, '$mfecha', '$mhora', '1', '3', '0', '0', NULL, NULL, NULL)";
		$modo_l1=3;
  }

  //CONDICION l2
  if($jsondecode["5730_PU0003_rev1.frecuencyRpm"] > 1200 && $jsondecode["5730_PU0004_rev1.frecuencyRpm"] < 750){
    //modo disenio
    $frec = (int)$jsondecode["5730_PU0003_rev1.frecuencyRpm"];
    $amp = $jsondecode["5730_PU0003.status.TotalCurrent"];
    $flow = (int)$jsondecode["FIT2301.IO.Value"];
		$sol_l2 = (int)$jsondecode["DIT2001_TV_Densidad.IO.Value"];
		$ton_l2 = (int)$jsondecode["DIT2001_PV.IO.Value"];
    $query .= ",(NULL, '$mfecha', '$mhora', '2', '1', '$frec', '". str_replace( ',', '.', $amp). "','$sol_l2', '$flow', '$ton_l2')";

		$frec_z_l2 = $frec;
		$amp_z_l2 = $amp;
		$flow_z_l2 = $flow;
		$modo_l2=1;


  }else if($jsondecode["5730_PU0004_rev1.frecuencyRpm"] > 360){
    //modo modificado
    $frec = (int)$jsondecode["5730_PU0004_rev1.frecuencyRpm"];
    $amp = $jsondecode["5730_PU0004.status.TotalCurrent"];
    $query .= ",(NULL, '$mfecha', '$mhora', '2', '2', '$frec', '". str_replace( ',', '.', $amp). "', NULL, NULL, NULL)";

		$frec_z_l2 = $frec;
		$amp_z_l2 = $amp;
		$modo_l2 = 2;

  }else{
    //parado
		$frec = (int)$jsondecode["5730_PU0004_rev1.frecuencyRpm"];
    $amp = $jsondecode["5730_PU0004.status.TotalCurrent"];
		$frec_z_l2 = $frec;
		$amp_z_l2 = $amp;
    $query .= ",(NULL, '$mfecha', '$mhora', '2', '3', '0', '0', NULL, NULL, NULL)";
		$modo_l2 = 3;
  }

/// bd 2---------------------------------------------------------------------------


	$WCT5841_Value = (int)$jsondecode["WCT5841WE120.Value"];
	$WCT0303_Value = (int)$jsondecode["WCT0303.Value"];
	$WIT0303_IO_Value = (int)$jsondecode["WIT0303.IO.Value"];
	$WIT0304_IO_Value = (int)$jsondecode["WIT0304.IO.Value"];
	$p5841_CB120_WIT = (int)$jsondecode["5841_CB120.WIT"];
	$p5841_CB120_M103_Alarm_M_II = (int)$jsondecode["5841_CB120_M103_Alarm.Current"];
	$p5730_CB0001_Current1M_IO_Value = (int)$jsondecode["5730_CB0001_Current1M.IO.Value"];
	$p5730_CB0003_status_Iavg = (int)$jsondecode["5730_CB0003.status.Iavg"];

	$faja1001 = (int)$jsondecode["WIT1741.IO.Value"];
	$faja2001 = (int)$jsondecode["WIT2741.IO.Value"];

	$FIT0962_IO_Value = (int)$jsondecode["FIT0962.IO.Value"];
	$FIT0963_IO_Value = (int)$jsondecode["FIT0963.IO.Value"];
	$FIT1801_IO_Value = (int)$jsondecode["FIT1801.IO.Value"];
	$FIT2801_IO_Value = (int)$jsondecode["FIT2801.IO.Value"];

	$relpu1001_amp = (int)$jsondecode["5780_PU1001.Status.Current"];
	$relpu1001_hz  = (int)$jsondecode["5780_PU1001.Status.FrequencyPv"];
	$relpu1002_amp = (int)$jsondecode["5780_PU1002.Status.Current"];
	$relpu1002_hz  = (int)	$jsondecode["5780_PU1002.Status.FrequencyPv"];

	$relpu2001_amp = (int)$jsondecode["5780_PU2001.Status.Current"];
	$relpu2001_hz  = (int)$jsondecode["5780_PU2001.Status.FrequencyPv"];
	$relpu2002_amp = (int)$jsondecode["5780_PU2002.Status.Current"];
	$relpu2002_hz  = (int)$jsondecode["5780_PU2002.Status.FrequencyPv"];

	$p5780_MX1001_status_Iavg = (int)$jsondecode["5780_MX1001.status.Iavg"];
	$p5780_MX2001_status_Iavg = (int)$jsondecode["5780_MX2001.status.Iavg"];
	$LIT1803_IO_Value = $jsondecode["LIT1803.IO.Value"];
	$LIT2803_IO_Value = $jsondecode["LIT2803.IO.Value"];

	$Shouxin_601_PU01A_FREC = $jsondecode["Shouxin_601_PU01A.Frecuency"];
	$Shouxin_601_PU02A_FREC = $jsondecode["Shouxin_601_PU02A.Frecuency"];
	$Shouxin_601_PU01B_FREC = $jsondecode["Shouxin_601_PU01B.Frecuency"];
	$Shouxin_601_PU02B_FREC = $jsondecode["Shouxin_601_PU02B.Frecuency"];
	$Shouxin_601_PU01C_FREC = $jsondecode["Shouxin_601_PU01C.Frecuency"];
	$Shouxin_601_PU02C_FREC = $jsondecode["Shouxin_601_PU02C.Frecuency"];


	$Shouxin_601_PIT_018_Value = $jsondecode["Shouxin_601_PIT_018.Value"];
	$Shouxin_601_PIT_017_Value = $jsondecode["Shouxin_601_PIT_017.Value"];
	$Shouxin_601_PIT_016_Value = $jsondecode["Shouxin_601_PIT_016.Value"];
	$DIT1801_IO_alue = (int)$jsondecode["DIT1801.IO.Value"];

	$relmodol1;
	$amp_rel1;
	$frec_rel1;
	$relmodol2;
	$amp_rel2;
	$frec_rel2;


	$frecba_shoux = ($Shouxin_601_PU01A_FREC + $Shouxin_601_PU02A_FREC)/2;
	$frecbb_shoux = ($Shouxin_601_PU01B_FREC + $Shouxin_601_PU02B_FREC)/2;
	$frecbc_shoux = ($Shouxin_601_PU01C_FREC + $Shouxin_601_PU02C_FREC)/2;


//relaves l1------------------------
	if($relpu1002_amp>5 and $relpu1002_hz>6){
		//bomba 1002 ; con flujo
		$relmodol1 = 1;
		$amp_rel1 = $relpu1002_amp;
		$frec_rel1 = $relpu1002_hz;

	}elseif($relpu1001_amp>5 and $relpu1001_hz>6){
		// bomba 1001
		$relmodol1 = 2;
		$amp_rel1 = $relpu1001_amp;
		$frec_rel1 = $relpu1001_hz;

	}else{
		//bombas paradas
		$relmodol1 = 3;
		$amp_rel1 = 0;	// "";
		$frec_rel1 = 0; // "";
	}


//relaves l2------------------------
	if ($relpu2001_amp>5 and $relpu2001_hz>6){
		// bomba 2001 ; con flujo
		$relmodol2 = 1;
		$amp_rel2 = $relpu2001_amp;
		$frec_rel2 = $relpu2001_hz;

	}elseif($relpu2002_amp>5 and $relpu2002_hz>6){
		//bomba 1002
		$relmodol2 = 2;
		$amp_rel2 = $relpu2002_amp;
		$frec_rel2 = $relpu2002_hz;

	}else{
		//bombas paradas
		$relmodol2 = 3;
		$amp_rel2 = 0;	// "";
		$frec_rel2 = 0;	// "";
	}

  $query2 .= "(NULL, '$mfecha', '$mhora', '$WIT0304_IO_Value', '$p5841_CB120_WIT', '$p5841_CB120_M103_Alarm_M_II', '$p5730_CB0001_Current1M_IO_Value',
  '$p5730_CB0003_status_Iavg', '$faja1001', $faja2001, '$FIT0963_IO_Value', '$FIT0962_IO_Value', '$relmodol1', NULL, '". str_replace( ',', '.', $FIT1801_IO_Value). "', NULL, '$amp_rel1', '$frec_rel1', '$relmodol2', NULL, '". str_replace( ',', '.', $FIT2801_IO_Value)."', NULL, '$amp_rel2', '$frec_rel2', '$p5780_MX1001_status_Iavg', '$p5780_MX2001_status_Iavg',
  '". str_replace( ',', '.', $LIT1803_IO_Value). "', '". str_replace( ',', '.', $LIT2803_IO_Value). "', '". str_replace( ',', '.', $frecba_shoux)."',
  '". str_replace( ',', '.', $frecbb_shoux)."', '". str_replace( ',', '.', $frecbc_shoux)."', '". str_replace( ',', '.', $Shouxin_601_PIT_018_Value)."',
  '". str_replace( ',', '.', $Shouxin_601_PIT_017_Value)."', '". str_replace( ',', '.', $Shouxin_601_PIT_016_Value)."', NULL, NULL, '$DIT1801_IO_alue', '$WCT5841_Value', '$WCT0303_Value','$WIT0303_IO_Value') ";



/// envio---------------------------------------------------------------------------

  $mysqli = new mysqli("127.0.0.1","admin1","ccr.admin","db_pq7");

  if ($mysqli -> connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli -> connect_error;
    exit();
  }
/*
  // Perform a query, check for error
  if (!$mysqli -> query("INSERT INTO Persons (FirstName) VALUES ('Glenn')")) {
    echo("Error description: " . $mysqli -> error);
  }
*/

  if (!$mysqli -> query($query)) {
    echo("Error description: " . $mysqli -> error);
  }

	if (!$mysqli -> query($query2)) {
    echo("Error description: " . $mysqli -> error);
  }

  $mysqli -> close();
	//echo $query;
	//echo $query2;
	print_r($sol_l2);
	print_r($ton_l2);
	$mfecha = str_replace(' ','+',$mfecha);
	$query_param = "flow_l1=".$flow_z_l1."&flow_l2=".$flow_z_l2."&amp_l1=".$amp_z_l1."&amp_l2=".$amp_z_l2."&frec_l1=".$frec_z_l1."&frec_l2=".$frec_z_l2."&fecha=".$mfecha."&hora=".$mhora."&modo_l1=".$modo_l1."&modo_l2=".$modo_l2."&tonelaje_l1=".$jsondecode['WIT1741.IO.Value']."&tonelaje_l2=".$jsondecode['WIT2741.IO.Value']."&fmrelaves=".$jsondecode['WI1802.IO.Value']."&sol_l2=".$sol_l2."&ton_l2=".$ton_l2;
	//echo $query_param;
	$url = 'https://127.0.0.1/php_func/insertdata.php?';
	$url = $url.$query_param;
	$ch1=curl_init();

	//DESCOMENTAR LAS SIGUIENTES 5 LINEAS PARA ENVIAR DATOS A TRAVES DEL PROXY Y HABILITAR EN NETWORKD EL ADAPTADOR CON LA RED DE CCR
	// UNA VEZ HABILITADO EL ADAPTADOR PARAR EL SCRIPT Y VOLVERLO A INICIAR
	
		//curl_setopt($ch1, CURLOPT_HTTPPROXYTUNNEL, 1);
		//curl_setopt($ch1, CURLOPT_PROXY, $proxyIP);
		//curl_setopt($ch1, CURLOPT_PROXYPORT, $proxyPort);
	
	curl_setopt($ch1,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch1,CURLOPT_SSL_VERIFYHOST,0);
	
		// curl_setopt_array($ch1, array(
		// // Indicar que vamos a hacer una petición POST
		// //CURLOPT_CUSTOMREQUEST => "POST",
		// // Justo aquí ponemos los datos dentro del cuerpo
		// //CURLOPT_POSTFIELDS => $query,
		// // Encabezados
		// //CURLOPT_HEADER => true,
		// CURLOPT_HTTPHEADER => array(
		//    'Content-Type: text/html; charset=UTF-8',
		//    'Content-Length: ' . strlen($url), // Abajo podríamos agregar más encabezados
		//    //'Personalizado: ¡ingresando-_-!', # Un encabezado personalizado
		//  ),
		// # indicar que regrese los datos, no que los imprima directamente
		// CURLOPT_CONNECTTIMEOUT=>0,
		// CURLOPT_TIMEOUT_MS=>3000,
		// ));
		$options = array(
			CURLOPT_URL            => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER         => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_ENCODING       => "",
			CURLOPT_AUTOREFERER    => true,
			CURLOPT_CONNECTTIMEOUT => 120,
			CURLOPT_TIMEOUT        => 120,
			CURLOPT_MAXREDIRS      => 10,
	);
	
	curl_setopt_array( $ch1, $options );
	# Hora de hacer la petición
	$resultado = curl_exec($ch1);
	# Vemos si el código es 200, es decir, HTTP_OK
	
	$codigoRespuesta = curl_getinfo($ch1, CURLINFO_HTTP_CODE);
	if($codigoRespuesta === 200 || $codigoRespuesta === 100){
		# Decodificar JSON porque esa es la respuesta
		//$respuestaDecodificada = json_decode($resultado);
		# Simplemente los imprimimos
		echo "Mensaje del servidor: " .$codigoRespuesta." respuesta: ".$resultado.PHP_EOL;
		//echo "<br><strong>INTERVALO DE ENVIO: </strong>".$t_refresh;
		//echo " Segundos";
		//echo "<br><strong>TRANSFIRIENDO:... </strong>" .$respuestaDecodificada->tramadatos;
	
	}else{
		# Error
		echo "Error consultando. Código de respuesta: $codigoRespuesta"." respuesta: ".$resultado.PHP_EOL;
		//echo "fin codigo respuesta";
	}
	/*
	if(curl_errno($ch1)){
		throw new Exception(curl_errno($ch1));
		catch(Exception $ex){
			echo $ex->getMessage();
		}
	}
	*/
	//echo "<br><strong>Mensaje del servidor: </strong>" .$resultado;
	//echo "<br><strong>INTERVALO DE ENVIO: </strong>".$t_refresh;;
	curl_close($ch1);


echo $mfecha;
echo PHP_EOL;
  echo "Sleeping (NO cerrar)...\n";
  sleep(720);    // 12min--  15 minutos
  echo 'Ahi vamos...';
  return 1;
}
//ConsultaDB();
while(1){
	$minactual = date('i');
	if($minactual>=0 && $minactual<=1){
		//hora xx:00
		$datos_proceso = ConsultaDB();
	}else if($minactual>=15 && $minactual<=16){
		//hora xx:15
		$datos_proceso = ConsultaDB();
	}else if($minactual>=30 && $minactual<=31){
		//hora xx:30
		$datos_proceso = ConsultaDB();
	}else if($minactual>=45 && $minactual<=46){
		//hora xx:45
		$datos_proceso = ConsultaDB();
	}else{
		echo '.';
		//$datos_proceso = ConsultaDB();
	}
	sleep(10);
}

/*
while(1){
  $minactual = date('i');
  if($minactual>=0 && $minactual<=1){
    //hora xx:00
    $datos_proceso = ConsultaDB();
  }else if($minactual>=15 && $minactual<=16){
    //hora xx:15
    $datos_proceso = ConsultaDB();
  }else if($minactual>=30 && $minactual<=31){
    //hora xx:30
    $datos_proceso = ConsultaDB();
  }else if($minactual>=45 && $minactual<=46){
    //hora xx:45
    $datos_proceso = ConsultaDB();
  }else{
    echo '.';
    //$datos_proceso = ConsultaDB();
  }
  sleep(10);
}
*/
?>
