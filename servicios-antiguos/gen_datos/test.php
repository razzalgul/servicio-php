<?php
// require_once('../php_func/conexion.php');

// date_default_timezone_set('America/Lima');

// // $tiempo_rep = $_POST["tireporte1"];      //valido para mysql tambien
// $tiempo_rep = '2024-09-03';
//   $tinicio = date("Y-m-d H:i",strtotime($tiempo_rep)); // valido para msql y label;
//   $tinicio = strtotime ('+8 hour' , strtotime ($tinicio));
//   $tinicio = date ('Y-m-d H:i:s' , $tinicio);

//   $tifinal = date("Y-m-d H:i:s",strtotime($tiempo_rep)); // valido para msql y label;
//   $tifinal  = strtotime ('+32 hour' , strtotime ($tifinal));
//   $tifinal  = date ('Y-m-d H:i:s' , $tifinal );


//   /// inicio segundo turno 7 pm
//   $tisegundoturno = date("Y-m-d H:i:s",strtotime($tiempo_rep));
//   $tisegundoturno = strtotime('+19 hour', strtotime($tisegundoturno));
//   $tisegundoturno = date('Y-m-d H:i:s', $tisegundoturno);

//   $tfinal_am = date("Y-m-d H:i:s",strtotime($tiempo_rep)); // valido para msql y label;
//   $tfinal_am = strtotime ('+31 hour' , strtotime ($tfinal_am));
//   $tfinal_am = date ('Y-m-d H:i:s' , $tfinal_am);


// $con = conectar_1();
// $con1 = conexion();

// $con2 = conectar_4();
// $queryPotenciaMolinos= "SELECT DateTime, TagName, value FROM Runtime.dbo.History WHERE TagName IN ('5740_BM1001.M101_POW','5740_BM1002.M101_POW','5740_BM2001.M101_POW','5740_BM2002.M101_POW') AND DateTime BETWEEN '$tinicio' AND '$tfinal_am' AND wwRetrievalMode='Cyclic' AND wwCycleCount=93";

// $sql2 = "SELECT * FROM datos_repdia WHERE (fecha BETWEEN '$tinicio'AND '$tifinal') ORDER BY fecha ASC ";
// //$sql = "SELECT * FROM datosc2 ORDER BY id ASC"; //$SQL = 'SELECT id,  valor, color FROM estado_general'; id ASC WHERE id='$id'
// $stmt2 = $con2->prepare($queryPotenciaMolinos);
// $result2 = $stmt2->execute();
// // datos del reporte diario 
// $rows2 = $stmt2->fetchAll(\PDO::FETCH_OBJ);

// $potenciaMolino1001 = array_values(array_filter($rows2,fn($row)=>$row->TagName == '5740_BM1001.M101_POW'));
// $potenciaMolino1002 = array_filter($rows2,fn($row)=>$row->TagName == '5740_BM1002.M101_POW');
// $potenciaMolino2001 = array_filter($rows2,fn($row)=>$row->TagName == '5740_BM2001.M101_POW');
// $potenciaMolino2002 = array_filter($rows2,fn($row)=>$row->TagName == '5740_BM2002.M101_POW');
// print_r($potenciaMolino1001);

/// QUERY PARA POTENCIA DE MOLINOS 


  // extraemos los datos para los tonelajes  y los convertimos e objetos 
  // $datoinicioturno = array_filter($rows2,fn($row)=>$row->fecha == $tinicio);
  // $datoinicioturno = reset($datoinicioturno);
  // $datosegundoturno = array_filter($rows2,fn($row)=>$row->fecha == $tisegundoturno);
  // $datosegundoturno = reset($datosegundoturno);
  // $datofinguardia = array_filter($rows2,fn($row)=>$row->fecha == $tfinal_am);
  // $datofinguardia = reset($datofinguardia);

  function conexion()
  {
  
      $serverName = "172.16.0.244"; //gr_history
     // $serverName = "172.16.0.72";  //gr
      //$connectionOptions = array(“Database” => “Runtime”);  // otro master
      $connectionOptions = array("Database" => "Runtime", "Uid" => "ccruser", "PWD" => "ccruser@2019", "MultipleActiveResultSets" => 0,"TrustServerCertificate"=>true);
      $conn = sqlsrv_connect($serverName, $connectionOptions);
      if ($conn === false) {
          echo "Habilitar para conectar.</br>";
          die(print_r(sqlsrv_errors(), true));
      } else {
          echo "Conectado a la base de datos ";
          //    print_r($conn);
      }
      return $conn;
  }
  $conn = conexion();
  print_r($conn);
exit();

?>
