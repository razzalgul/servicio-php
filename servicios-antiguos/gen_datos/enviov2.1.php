<?php

// ----------------------------------------------------------------
// DISCLAIMER
//
// NO ESTOY MUY SEGURO DEL FUNCIONAMIENTO DE TODO LO QUE VIENE ABAJO
// ASI QUE SI POR A O B ALGUN DIA TRATA DE OPTIMIZARLO Y FRACASA
// POR FAVOR INCREMENTE EL CONTADOR MANUALMENTE A MODO DE ADVERTENCIA PARA LOS QUE VENDRAN DESPUES DE USTED
//
//
// # DE HORAS PERDIDAS: 20
//
//
//
// ---------------------------------------------------------------------------

date_default_timezone_set('America/Lima');

//echo microtime(true).PHP_EOL;
function conexion()
{

    $serverName = "172.16.0.244"; //gr_history
    //$serverName = "172.16.0.72";  //gr
    //$connectionOptions = array(“Database” => “Runtime”);  // otro master
    $connectionOptions = array("Database" => "Runtime", "Uid" => "ccruser", "PWD" => "ccruser@2019", "MultipleActiveResultSets" => 0);
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

$datos_general = array(

    'estado_general' => array(
        '5730_PU0001.status.TotalCurrent' => 0,
        '5730_PU0002.status.TotalCurrent' => 0,
        '5730_PU0003.status.TotalCurrent' => 0,
        '5730_PU0004.status.TotalCurrent' => 0,
        '5740_BM1001.M101_ELEC' => 0,
        '5740_BM1001.M101_POW' => 0,
        '5740_BM1002.M101_ELEC' => 0,
        '5740_BM1002.M101_POW' => 0,
        '5740_BM2001.M101_ELEC' => 0,
        '5740_BM2001.M101_POW' => 0,
        '5740_BM2002.M101_ELEC' => 0,
        '5740_BM2002.M101_POW' => 0,
        '5760_PU1001.status.Speed' => 0,
        '5760_PU1001.status.TotalCurrent' => 0,
        '5760_PU1002.status.Speed' => 0,
        '5760_PU1002.status.TotalCurrent' => 0,
        '5760_PU2001.status.Speed' => 0,
        '5760_PU2001.status.TotalCurrent' => 0,
        '5760_PU2002.status.Speed' => 0,
        '5760_PU2002.status.TotalCurrent' => 0,
        '5771_TH1001.MudBed_Pressure' => 0,
        '5771_TH1001.Rotating_Pressure' => 0,
        '5771_TH2001.MudBed_Pressure' => 0,
        '5771_TH2001.Rotating_Pressure' => 0,
        '5780_MX1001.status.Iavg' => 0,
        '5780_MX2001.status.Iavg' => 0,
        '5841_CB120.WIT' => 0,
        'FIT0963.IO.Value' => 0,
        'FIT1401.IO.Value' => 0,
        'FIT1402.IO.Value' => 0,
        'FIT1801.IO.Value' => 0,
        'FIT2401.IO.Value' => 0,
        'FIT2402.IO.Value' => 0,
        'FIT2801.IO.Value' => 0,
        'LIT0301A.IO.Value' => 0,
        'LIT0301B.IO.Value' => 0,
        'LIT0302A.IO.Value' => 0,
        'LIT0302B.IO.Value' => 0,
        'LIT0302C.IO.Value' => 0,
        'LIT0311.IO.Value' => 0,
        'LIT0312.IO.Value' => 0,
        'LIT0313.IO.Value' => 0,
        'LIT0314.IO.Value' => 0,
        'LIT0315.IO.Value' => 0,
        'LIT0316.IO.Value' => 0,
        'LIT0317.IO.Value' => 0,
        'LIT0318.IO.Value' => 0,
        'LIT0921.IO.Value' => 0,
        'LIT0961.IO.Value' => 0,
        'LIT1803.IO.Value' => 0,
        'LIT2803.IO.Value' => 0,
        'N1920_FIT0001.IO.Value' => 0,
        'PIT0911.IO.Value' => 0,
        'PIT1503.IO.Value' => 0,
        'PIT1504.IO.Value' => 0,
        'PIT2503.IO.Value' => 0,
        'PIT2504.IO.Value' => 0,
        'WIT0304.IO.Value' => 0,
        'WIT1741.IO.Value' => 0,
        'WIT2741.IO.Value' => 0,
        'tonelajedisenodia' => 0, // tonelaje de diseño dia
        'tonelajedisenoTurno' => 0, // tonelaje de diseño turno
        'tonelajeactualturno' => 0, // tonelaje actual turno
        'tonelajeDiaAnterior' => 0, // tonelaje del dia anterior
        'tonelajeEnvioActualDia' => 0, // tonelaje faja 5 del dia
        'tonelajeActualDia' => 0, // tonelaje actual del dia
        'tonelajeguardiaanterior' => 0, // tonelaje de turno anterior
        'tonelajeEnvioActualTurno' => 0, // tonelaje faja 5 turno
        'tonelajeenviodisenoturno' => 0, // tonelaje de diseño faja 5 turno
        'tonelajeenviodisenodia' => 0, // tonelaje de diseño faja 5 dia
        'diferenciaproduccionturno' => 0, // variacion de la produccion respecto al diseño en el turno
        'diferenciaproducciondia' => 0, // variacion de la produccion respecto al diseño en el dia
        'diferenciaenvioturno' => 0, // variacion del acumulado en la faja 5 respecto al diseño en turno
        'diferenciaenviodia' => 0, // variacion del acumulado en la faja 5 respecto al diseño en el dia
        'tonelajeenvioguardiaanterior' => 0, // acumulado faja 5 guardia anterior
        'tonelajeenviodiaanterior' => 0, // acumulado faja 5 dia anterior
        'presion_procesos_l1' => 0, // presion agua de procesos L1
        'presion_procesos_l2' => 0, // presion agua de procesos L2
        'porcentajeMolino1001' => 0,
        'porcentajeMolino1002' => 0,
        'porcentajeMolino2001' => 0,
        'porcentajeMolino2002' => 0,
        'produccion_l1' => 0,
        'produccion_l2' => 0,
        'filtros_l1' => 0,
        'filtros_l2' => 0,
        'queryTime'=> '',

    ),
    'circuito_1' => array(
        '5730_CB0003.status.Iavg' => 0,
        '5730_CB0005_Current1M.IO.Value' => 0,
        '5730_CB0005_Current2M.IO.Value' => 0,
        '5730_CB0005_Current3M.IO.Value' => 0,
        '5730_CB0007_Current1M.IO.Value' => 0,
        '5730_CB0008.Status.Current' => 0,
        '5730_CB0009.Status.Current' => 0,
        '5730_CB0010.Status.Current' => 0,
        '5730_CB0011_Current1M.IO.Value' => 0,
        '5730_CB0013_Current1M.IO.Value' => 0,
        '5730_CB0014.Status.Current' => 0,
        '5730_CB0015.Status.Current' => 0,
        '5730_CB0016.Status.Current' => 0,
        '5730_CB0017.Status.Current' => 0,
        '5730_CB0018.Status.Current' => 0,
        '5730_CB0019.Status.Current' => 0,
        '5730_CB0020.Status.Current' => 0,
        '5730_CB0021.Status.Current' => 0,
        '5730_PU0001.status.TotalCurrent' => 0,
        '5730_PU0002.status.TotalCurrent' => 0,
        '5730_PU0003.status.TotalCurrent' => 0,
        '5730_PU0004.status.TotalCurrent' => 0,
        '5730_RP0001.AI_DRV01M1001_EIA' => 0,
        '5730_RP0001.AI_DRV02M1001_EIA' => 0,
        '5730_RP0001.AI_HYS01B5001_PIA' => 0,
        '5730_RP0001.AI_HYS01B5002_PIA' => 0,
        '5730_RP0001.Mean_gap' => 0,
        '5730_RP0002.AI_DRV01M1001_EIA' => 0,
        '5730_RP0002.AI_DRV02M1001_EIA' => 0,
        '5730_RP0002.AI_HYS01B5001_PIA' => 0,
        '5730_RP0002.AI_HYS01B5002_PIA' => 0,
        '5730_RP0002.Mean_gap' => 0,
        '5730_RP0003.AI_DRV01M1001_EIA' => 0,
        '5730_RP0003.AI_DRV02M1001_EIA' => 0,
        '5730_RP0003.AI_HYS01B5001_PIA' => 0,
        '5730_RP0003.AI_HYS01B5002_PIA' => 0,
        '5730_RP0003.Mean_gap' => 0,
        '5841_CB120.WIT' => 0,
        'FE0001.status.Iavg' => 0,
        'FE0002.status.Iavg' => 0,
        'FE0003.status.Iavg' => 0,
        'FE0004.status.Iavg' => 0,
        'FIT1301.IO.Value' => 0,
        'FIT2301.IO.Value' => 0,
        'LIT0301A.IO.Value' => 0,
        'LIT0301B.IO.Value' => 0,
        'LIT0302A.IO.Value' => 0,
        'LIT0302B.IO.Value' => 0,
        'LIT0302C.IO.Value' => 0,
        'LIT0311.IO.Value' => 0,
        'LIT0312.IO.Value' => 0,
        'LIT0313.IO.Value' => 0,
        'LIT0314.IO.Value' => 0,
        'LIT0315.IO.Value' => 0,
        'LIT0316.IO.Value' => 0,
        'LIT0317.IO.Value' => 0,
        'LIT0318.IO.Value' => 0,
        'LIT1304.IO.Value' => 0,
        'LIT2304.IO.Value' => 0,
        'WIT0303.IO.Value' => 0,
        'WIT0304.IO.Value' => 0,
        'WIT0305.IO.Value' => 0,
        '5730_CB0006_Current1M.IO.Value' => 0,
        '5730_CB0006_Current2M.IO.Value' => 0,
        '5730_CB0012_Current1M.IO.Value' => 0,
        '5730_CB0012_Current2M.IO.Value' => 0,
        '5730_CB0012_Current3M.IO.Value' => 0,
        '5730_CB0012_Current4M.IO.Value' => 0,
        '5730_CB0022_Current1M.IO.Value' => 0,
        '5730_CB0022_Current2M.IO.Value' => 0,
        '5730_PU0001_rev1.frecuencyRpm' => 0,
        '5730_PU0002_rev1.frecuencyRpm' => 0,
        '5730_PU0003_rev1.frecuencyRpm' => 0,
        '5730_PU0004_rev1.frecuencyRpm' => 0,
        '5730_SC0008.Current' => 0,
        '5730_SC0007.Current' => 0,
        '5730_SC0006.Current' => 0,
        '5730_SC0005.Current' => 0,
        '5730_SC0004.Current' => 0,
        '5730_SC0003.Current' => 0,
        '5730_SC0002.Current' => 0,
        '5730_SC0001.Current' => 0,
        'WCT0303.Value' => 0,
        'WCT0304.Value' => 0,
        'WCT0305.Value' => 0,
        'WCT5841WE120.Value' => 0,
        '5730_CB0001_Current1M.IO.Value' => 0,
        '5841_CB120_M101_Alarm.Current' => 0,
        '5841_CB120_M102_Alarm.Current' => 0,
        '5841_CB120_M103_Alarm.Current' => 0,
        '5730_RP0001.AI_FBJ01B9501_WICA' => 0,
        '5730_RP0002.AI_FBJ01B9501_WICA' => 0,
        '5730_RP0003.AI_FBJ01B9501_WICA' => 0,
        '5730_RP0001.SCEW_R_MINUS_L' => 0,
        '5730_RP0002.SCEW_R_MINUS_L' => 0,
        '5730_RP0003.SCEW_R_MINUS_L' => 0,
        '5730_RP0003.AI_DRV01M1001_SI' => 0,
        '5730_RP0003.AI_DRV02M1001_SI' => 0,
        '5730_RP0002.AI_DRV02M1001_SI' => 0,
        '5730_RP0002.AI_DRV01M1001_SI' => 0,
        '5730_RP0001.AI_DRV02M1001_SI' => 0,
        '5730_RP0001.AI_DRV01M1001_SI' => 0,
        'DIT1001_PV.IO.Value' => 0,
        'DIT2001_PV.IO.Value' => 0,
        'queryTime'=> '',
    ),
    'circuito_2' => array(
        '5740_BM1001.M101_ELEC' => 0,
        '5740_BM1001.M101_POW' => 0,
        '5740_BM1002.M101_ELEC' => 0,
        '5740_BM1002.M101_POW' => 0,
        '5740_BM2001.M101_ELEC' => 0,
        '5740_BM2001.M101_POW' => 0,
        '5740_BM2002.M101_ELEC' => 0,
        '5740_BM2002.M101_POW' => 0,
        '5740_PU1001.status.Speed' => 0,
        '5740_PU1001.status.TotalCurrent' => 0,
        '5740_PU1002.status.Speed' => 0,
        '5740_PU1002.status.TotalCurrent' => 0,
        '5740_PU1003.status.Speed' => 0,
        '5740_PU1003.status.TotalCurrent' => 0,
        '5740_PU1004.status.Speed' => 0,
        '5740_PU1004.status.TotalCurrent' => 0,
        '5740_PU2001.status.Speed' => 0,
        '5740_PU2001.status.TotalCurrent' => 0,
        '5740_PU2002.status.Speed' => 0,
        '5740_PU2002.status.TotalCurrent' => 0,
        '5740_PU2003.status.Speed' => 0,
        '5740_PU2003.status.TotalCurrent' => 0,
        '5740_PU2004.status.Speed' => 0,
        '5740_PU2004.status.TotalCurrent' => 0,
        '5760_BL0001.P2' => 0,
        '5760_BL0001.PA1' => 0,
        '5760_BL0002.P2' => 0,
        '5760_BL0002.PA1' => 0,
        '5760_BL0003.P2' => 0,
        '5760_BL0003.PA1' => 0,
        '5760_FC1001_2.Status.Thickness_Pv' => 0,
        '5760_FC1003_4.Status.Thickness_Pv' => 0,
        '5760_FC1005_6.Status.Thickness_Pv' => 0,
        '5760_FC2001_2.Status.Thickness_Pv' => 0,
        '5760_FC2003_4.Status.Thickness_Pv' => 0,
        '5760_FC2005_6.Status.Thickness_Pv' => 0,
        '5760_PU1001.status.Speed' => 0,
        '5760_PU1001.status.TotalCurrent' => 0,
        '5760_PU1002.status.Speed' => 0,
        '5760_PU1002.status.TotalCurrent' => 0,
        '5760_PU1007.Status.FrequencyPv' => 0,
        '5760_PU1008.Status.FrequencyPv' => 0,
        '5760_PU1009.Status.FrequencyPv' => 0,
        '5760_PU1010.Status.FrequencyPv' => 0,
        '5760_PU2001.status.Speed' => 0,
        '5760_PU2001.status.TotalCurrent' => 0,
        '5760_PU2002.status.Speed' => 0,
        '5760_PU2002.status.TotalCurrent' => 0,
        '5760_PU2007.Status.FrequencyPv' => 0,
        '5760_PU2008.Status.FrequencyPv' => 0,
        '5760_PU2009.Status.FrequencyPv' => 0,
        '5760_PU2010.Status.FrequencyPv' => 0,
        'AG1001.status.Iavg' => 0,
        'AG1002.status.Iavg' => 0,
        'AG1003.status.Iavg' => 0,
        'AG1004.status.Iavg' => 0,
        'AG1005.status.Iavg' => 0,
        'AG1006.status.Iavg' => 0,
        'AG2001.status.Iavg' => 0,
        'AG2002.status.Iavg' => 0,
        'AG2003.status.Iavg' => 0,
        'AG2004.status.Iavg' => 0,
        'AG2005.status.Iavg' => 0,
        'AG2006.status.Iavg' => 0,
        'FIT1401.IO.Value' => 0,
        'FIT1402.IO.Value' => 0,
        'FIT2401.IO.Value' => 0,
        'FIT2402.IO.Value' => 0,
        'LIT1401.IO.Value' => 0,
        'LIT1402.IO.Value' => 0,
        'LIT1601.IO.Value' => 0,
        'LIT1604.IO.Value' => 0,
        'LIT1613.IO.Value' => 0,
        'LIT2401.IO.Value' => 0,
        'LIT2402.IO.Value' => 0,
        'LIT2601.IO.Value' => 0,
        'LIT2604.IO.Value' => 0,
        'LIT2613.IO.Value' => 0,
        'LM1001.status.Iavg' => 0,
        'LM1002.status.Iavg' => 0,
        'LM1003.status.Iavg' => 0,
        'LM1004.status.Iavg' => 0,
        'LM1005.status.Iavg' => 0,
        'LM1006.status.Iavg' => 0,
        'LM1007.status.Iavg' => 0,
        'LM1008.status.Iavg' => 0,
        'LM1009.status.Iavg' => 0,
        'LM1010.status.Iavg' => 0,
        'LM1011.status.Iavg' => 0,
        'LM1012.status.Iavg' => 0,
        'LM1013.status.Iavg' => 0,
        'LM1014.status.Iavg' => 0,
        'LM1015.status.Iavg' => 0,
        'LM1016.status.Iavg' => 0,
        'LM1017.status.Iavg' => 0,
        'LM2001.status.Iavg' => 0,
        'LM2002.status.Iavg' => 0,
        'LM2003.status.Iavg' => 0,
        'LM2004.status.Iavg' => 0,
        'LM2005.status.Iavg' => 0,
        'LM2006.status.Iavg' => 0,
        'LM2007.status.Iavg' => 0,
        'LM2008.status.Iavg' => 0,
        'LM2009.status.Iavg' => 0,
        'LM2010.status.Iavg' => 0,
        'LM2011.status.Iavg' => 0,
        'LM2012.status.Iavg' => 0,
        'LM2013.status.Iavg' => 0,
        'LM2014.status.Iavg' => 0,
        'LM2015.status.Iavg' => 0,
        'LM2016.status.Iavg' => 0,
        'LM2017.status.Iavg' => 0,
        'PIT1503.IO.Value' => 0,
        'PIT1504.IO.Value' => 0,
        'PIT2503.IO.Value' => 0,
        'PIT2504.IO.Value' => 0,
        'AG1001.status.Iavg' => 0,
        'AG1006.status.Iavg' => 0,
        'AG1004.status.Iavg' => 0,
        'AG1003.status.Iavg' => 0,
        'AG1002.status.Iavg' => 0,
        'AG1005.status.Iavg' => 0,
        '5760_FC1001_2.Status.Valve1_Pv' => 0,
        '5760_FC1001_2.Status.Valve2_Pv' => 0,
        '5760_FC1003_4.Status.Valve1_Pv' => 0,
        '5760_FC1003_4.Status.Valve2_Pv' => 0,
        '5760_FC1005_6.Status.Valve1_Pv' => 0,
        '5760_FC1005_6.Status.Valve2_Pv' => 0,
        '5760_FC2001_2.Status.Valve1_Pv' => 0,
        '5760_FC2001_2.Status.Valve2_Pv' => 0,
        '5760_FC2003_4.Status.Valve1_Pv' => 0,
        '5760_FC2003_4.Status.Valve2_Pv' => 0,
        '5760_FC2005_6.Status.Valve1_Pv' => 0,
        '5760_FC2005_6.Status.Valve2_Pv' => 0,
        'MX1001.status.Iavg' => 0,
        'MX1002.status.Iavg' => 0,
        'MX2001.status.Iavg' => 0,
        'MX2002.status.Iavg' => 0,
        '5740_BM1001.M101_FRE' => 0,
        '5740_BM1002.M101_FRE' => 0,
        '5740_BM2001.M101_FRE' => 0,
        '5740_BM2002.M101_FRE' => 0,
        'DIT1001_PV.IO.Value' => 0,
        'DIT2001_PV.IO.Value' => 0,
        'queryTime'=> '',
    ),
    'circuito_3' => array(
        '5771_PU1001.Status.Current' => 0,
        '5771_PU1001.Status.FrequencyPv' => 0,
        '5771_PU1002.Status.Current' => 0,
        '5771_PU1002.Status.FrequencyPv' => 0,
        '5771_PU2001.Status.Current' => 0,
        '5771_PU2001.Status.FrequencyPv' => 0,
        '5771_PU2002.Status.Current' => 0,
        '5771_PU2002.Status.FrequencyPv' => 0,
        '5771_TH1001.Lifting_Pressure' => 0,
        '5771_TH1001.MudBed_Pressure' => 0,
        '5771_TH1001.Oil_Te' => 0,
        '5771_TH1001.Rake_Thickness' => 0,
        '5771_TH1001.Rotating_Pressure' => 0,
        '5771_TH2001.Lifting_Pressure' => 0,
        '5771_TH2001.MudBed_Pressure' => 0,
        '5771_TH2001.Oil_Te' => 0,
        '5771_TH2001.Rake_Thickness' => 0,
        '5771_TH2001.Rotating_Pressure' => 0,
        '5772_CB1001.status.Iavg' => 0,
        '5772_CB2001.status.Iavg' => 0,
        '5772_PU1001.Status.Current' => 0,
        '5772_PU1001.Status.FrequencyPv' => 0,
        '5772_PU1002.Status.Current' => 0,
        '5772_PU1002.Status.FrequencyPv' => 0,
        '5772_PU2001.Status.Current' => 0,
        '5772_PU2001.Status.FrequencyPv' => 0,
        '5772_PU2002.Status.Current' => 0,
        '5772_PU2002.Status.FrequencyPv' => 0,
        'FIT1721.IO.Value' => 0,
        'FIT2721.IO.Value' => 0,
        'FIT0911.IO.Value' => 0,
        'LIT1742.IO.Value' => 0,
        'LIT2742.IO.Value' => 0,
        'PIT0911.IO.Value' => 0,
        'WIT1741.IO.Value' => 0,
        'WIT2741.IO.Value' => 0,
        '5798_CM0001.Current' => 0,
        '5798_CM0002.Current' => 0,
        '5798_CM0003.Current' => 0,
        '5798_CM0004.Current' => 0,
        '5798_CM0005.Current' => 0,
        '5851CB110.M101_II' => 0,
        '5851CB120.M101_II' => 0,
        '5941CB110.M101_II' => 0,
        '5772_VP1001.Current' => 0,
        '5772_VP1002.Current' => 0,
        '5772_VP1003.Current' => 0,
        '5772_VP1004.Current' => 0,
        '5772_VP1005.Current' => 0,
        '5772_VP1006.Current' => 0,
        '5772_VP1007.Current' => 0,
        '5772_VP1008.Current' => 0,
        '5772_VP2001.Current' => 0,
        '5772_VP2002.Current' => 0,
        '5772_VP2003.Current' => 0,
        '5772_VP2004.Current' => 0,
        '5772_VP2005.Current' => 0,
        '5772_VP2006.Current' => 0,
        '5772_VP2007.Current' => 0,
        '5772_VP2008.Current' => 0,

        'VF1001.HZ1' => 0,
        'VF1002.HZ1' => 0,
        'VF1003.HZ1' => 0,
        'VF1004.HZ1' => 0,
        'VF1005.HZ1' => 0,
        'VF1006.HZ1' => 0,
        'VF1007.HZ1' => 0,
        'VF1008.HZ1' => 0,
        'VF1009.HZ1' => 0,
        'VF1010.HZ1' => 0,

        'VF1001.HZ2' => 0,
        'VF1002.HZ2' => 0,
        'VF1003.HZ2' => 0,
        'VF1004.HZ2' => 0,
        'VF1005.HZ2' => 0,
        'VF1006.HZ2' => 0,
        'VF1007.HZ2' => 0,
        'VF1008.HZ2' => 0,
        'VF1009.HZ2' => 0,
        'VF1010.HZ2' => 0,

        'VF2001.HZ1' => 0,
        'VF2002.HZ1' => 0,
        'VF2003.HZ1' => 0,
        'VF2004.HZ1' => 0,
        'VF2005.HZ1' => 0,
        'VF2006.HZ1' => 0,
        'VF2007.HZ1' => 0,
        'VF2008.HZ1' => 0,
        'VF2009.HZ1' => 0,
        'VF2010.HZ1' => 0,

        'VF2001.HZ2' => 0,
        'VF2002.HZ2' => 0,
        'VF2003.HZ2' => 0,
        'VF2004.HZ2' => 0,
        'VF2005.HZ2' => 0,
        'VF2006.HZ2' => 0,
        'VF2007.HZ2' => 0,
        'VF2008.HZ2' => 0,
        'VF2009.HZ2' => 0,
        'VF2010.HZ2' => 0,
        'queryTime'=> '',

    ),
    'circuito_4' => array(
        '5780_RS1001.Flow_CLD' => 0,
        '5780_RS2001.Flow_CLD' => 0,
        'WQI1801.Value' => 0,
        '5780_MX1001.status.Iavg' => 0,
        '5780_MX2001.status.Iavg' => 0,
        '5780_PU1001.Status.Current' => 0,
        '5780_PU1001.Status.FrequencyPv' => 0,
        '5780_PU1002.Status.Current' => 0,
        '5780_PU1002.Status.FrequencyPv' => 0,
        '5780_PU2001.Status.Current' => 0,
        '5780_PU2001.Status.FrequencyPv' => 0,
        '5780_PU2002.Status.Current' => 0,
        '5780_PU2002.Status.FrequencyPv' => 0,
        '5780_RS1001.Flow_PV' => 0,
        '5780_RS2001.Flow_PV' => 0,
        '5780_TH1001.Bar_MudPressure' => 0,
        '5780_TH1001.Bar_OilPressure' => 0,
        '5780_TH1001.C_OilTemperature' => 0,
        '5780_TH1001.M_MudLevel' => 0,
        '5780_TH2001.Bar_MudPressure' => 0,
        '5780_TH2001.Bar_OilPressure' => 0,
        '5780_TH2001.C_OilTemperature' => 0,
        '5780_TH2001.M_MudLevel' => 0,
        '5791_PU0001.status.Iavg' => 0,
        '5791_PU0002.status.Iavg' => 0,
        '5791_PU0003.status.Iavg' => 0,
        '5794_PU0001.status.Speed' => 0,
        '5794_PU0001.status.TotalCurrent' => 0,
        '5794_PU0002.status.Speed' => 0,
        '5794_PU0002.status.TotalCurrent' => 0,
        '5794_PU0003.status.Speed' => 0,
        '5794_PU0003.status.TotalCurrent' => 0,
        '5794_PU0004.status.Speed' => 0,
        '5794_PU0004.status.TotalCurrent' => 0,
        '5794_PU0005.status.Speed' => 0,
        '5794_PU0005.status.TotalCurrent' => 0,
        '5794_PU0006.status.Speed' => 0,
        '5794_PU0006.status.TotalCurrent' => 0,
        'FIT0963.IO.Value' => 0,
        'FIT1801.IO.Value' => 0,
        'FIT2801.IO.Value' => 0,
        'LIT0921.IO.Value' => 0,
        'LIT0941.IO.Value' => 0,
        'LIT0961.IO.Value' => 0,
        'LIT1803.IO.Value' => 0,
        'LIT2803.IO.Value' => 0,
        'N1920_FIT0001.IO.Value' => 0,
        'PIT0921A.IO.Value' => 0,
        'PIT0921B.IO.Value' => 0,
        'PIT0921C.IO.Value' => 0,
        'PIT0922A.IO.Value' => 0,
        'PIT0922B.IO.Value' => 0,
        'PIT0922C.IO.Value' => 0,
        'PIT0961A.IO.Value' => 0,
        'PIT0961B.IO.Value' => 0,
        'PIT0961C.IO.Value' => 0,
        'Shouxin_601_PIT_016.Value' => 0,
        'Shouxin_601_PIT_017.Value' => 0,
        'Shouxin_601_PIT_018.Value' => 0,
        'Shouxin_601_PU01A.Current' => 0,
        'Shouxin_601_PU01B.Current' => 0,
        'Shouxin_601_PU01C.Current' => 0,
        'Shouxin_601_PU01A.Frecuency' => 0,
        'Shouxin_601_PU01B.Frecuency' => 0,
        'Shouxin_601_PU01C.Frecuency' => 0,
        'Shouxin_601_PU02A.Current' => 0,
        'Shouxin_601_PU02B.Current' => 0,
        'Shouxin_601_PU02C.Current' => 0,
        'Shouxin_601_PU02A.Frecuency' => 0,
        'Shouxin_601_PU02B.Frecuency' => 0,
        'Shouxin_601_PU02C.Frecuency' => 0,
        '5780_PU2002_rev1.Status.SpeedPv' => 0,
        '5780_PU2001_rev1.Status.SpeedPv' => 0,
        '5780_PU1002_rev1.Status.SpeedPv' => 0,
        '5780_PU1001_rev1.Status.SpeedPv' => 0,
        'DIT1801.IO.Value' => 0,
        'WI1802.IO.Value' => 0,
        'DFI1801.IO.Value' => 0,
        'FIT0962.IO.Value' => 0,
        'PIT0941A.IO.Value' => 0,
        'PIT0991A.IO.Value' => 0,
        'FIT0962.IO.Value' => 0,
        'FIT0961.IO.Value' => 0,
        'FQI0963.Value' => 0,
        'FQI0962.Value' => 0,
        'queryTime'=> '',
        'FIT1802.IO.Value' => 0,
        'FIT2802.IO.Value' => 0,
    ),


);

$datos_produccion = array(
    "inicioGuardia" => array(
        "WCT0303.Value" => 0,
        "WCT1741.Value" => 0,
        "WCT2741.Value" => 0,

    ),
    "guardiaAnterior" => array(
        "WCT0303.Value" => 0,
        "WCT1741.Value" => 0,
        "WCT2741.Value" => 0,

    ),
    "guardiaDiaAnterior" => array(
        "WCT0303.Value" => 0,
        "WCT1741.Value" => 0,
        "WCT2741.Value" => 0,

    ));

$inicioTurno;
$inicioTurnoAnterior;
$inicioDiaPrevio;
$inicioDia;
$tonelajeInicioDia;
$tonelajeGuardiaAnterior;
$tonelajeDiaAnterior;
$tonelajeEnvioGuardiaAnterior;
$tonelajeEnvioDiaAnterior;
$tonelajeEnvioActualTurno;
$tonelajeEnvioActualDia;
$tonelajeEnvioDisenoTurno;
$tonelajeEnvioDisenoDia;




function consultabase(&$datos_produccion, &$inicioTurno, &$inicioTurnoAnterior, &$inicioDiaPrevio, &$inicioDia, &$datos_general, $firebasedata)
{

/* SE HA CAMBIADO EL CODIGO ANTERIOR PARA REALIZAR EL MENOR NUEMERO DE CONSULTAS A LA BASE DE DATOS
DEL SCRIPT DE ENVIO, LA FUNCION REALIZA PRIMERO UNA CONSULTA A LA TABLA DE DATOS QUE CONTIENE LOS DATOS REFRESCADOS RECIENTEMENTE
Y LUEGO HACE 3 CONSULTAS PARA OBTENER DATOS NECESARIOS PARA LOS HISTORICOS QUE PERMITAN CALCULAR LOS DATOS DE PRODUCCION
 */

    $ton_produccion_diseno = 0.3935; //metas del dia 30k ----> 30k/24 ---> 1250 t/h --> 20.83 t/m --> 0.3472 t/s
    $ton_envio_diseno = 0.5346; //metas del dia 46190 ----> x/24 ---> 1924.58 t/h --> 32.07 t/m --> 0.5346 t/s

    $fecha = date('Y-m-d');
    //$hora_actual = date('H:i:s',strtotime('+0 seconds',strtotime($fecha)));
    $hora = date("H:i:s");
    $tactual = 0;

    $minutos_actual = 0;

    $conn = conexion();
    $fechas = date('Y-m-d g:i:sa');
    $sql = "SELECT TagName, round(Value,2) as pvalor from Runtime.dbo.Live WHERE TagName IN ('DIT1001_PV.IO.Value','DIT2001_PV.IO.Value','5780_RS1001.Flow_CLD','5780_RS2001.Flow_CLD','WQI1801.Value','5730_RP0003.AI_DRV02M1001_SI','5730_RP0003.AI_DRV01M1001_SI','5730_RP0002.AI_DRV02M1001_SI','5730_RP0002.AI_DRV01M1001_SI','5730_RP0001.AI_DRV02M1001_SI','5730_RP0001.AI_DRV01M1001_SI','PIT0991A.IO.Value','PIT0941A.IO.Value','5730_SC0008.Current','5730_SC0007.Current','5730_SC0006.Current','5730_SC0005.Current','5730_SC0004.Current','5730_SC0003.Current','5730_SC0002.Current','5730_SC0001.Current','5772_VP2008.Current','5772_VP2007.Current','5772_VP2006.Current','5772_VP2005.Current','5772_VP2004.Current','5772_VP2003.Current','5772_VP2002.Current','5772_VP2001.Current','5772_VP1008.Current','5772_VP1007.Current','5772_VP1006.Current','5772_VP1005.Current','5772_VP1004.Current','5772_VP1003.Current','5772_VP1002.Current','5772_VP1001.Current','5841_CB120_M103_Alarm.Current','5841_CB120_M102_Alarm.Current','5841_CB120_M101_Alarm.Current','5730_CB0001_Current1M.IO.Value','5730_CB0003.status.Iavg','5730_CB0005_Current1M.IO.Value','5730_CB0005_Current2M.IO.Value','5730_CB0005_Current3M.IO.Value','5730_CB0006_Current1M.IO.Value','5730_CB0006_Current2M.IO.Value', '5730_CB0007_Current1M.IO.Value','5730_CB0008.Status.Current','5730_CB0008.Status.FrequencyPv','5730_CB0009.Status.Current','5730_CB0009.Status.FrequencyPv','5730_CB0010.Status.Current','5730_CB0010.Status.FrequencyPv','5730_CB0011_Current1M.IO.Value','5730_CB0012_Current1M.IO.Value','5730_CB0012_Current2M.IO.Value','5730_CB0012_Current3M.IO.Value','5730_CB0012_Current4M.IO.Value','5730_CB0013_Current1M.IO.Value','5730_CB0014.Status.Current','5730_CB0014.Status.FrequencyPv','5730_CB0015.Status.Current','5730_CB0015.Status.FrequencyPv','5730_CB0016.Status.Current','5730_CB0016.Status.FrequencyPv','5730_CB0017.Status.Current','5730_CB0017.Status.FrequencyPv','5730_CB0018.Status.Current','5730_CB0018.Status.FrequencyPv','5730_CB0019.Status.Current','5730_CB0019.Status.FrequencyPv','5730_CB0020.Status.Current','5730_CB0020.Status.FrequencyPv','5730_CB0021.Status.Current','5730_CB0021.Status.FrequencyPv','5730_CB0022_Current1M.IO.Value','5730_CB0022_Current2M.IO.Value','DFI1801.IO.Value','WI1802.IO.Value','DIT1801.IO.Value','5780_PU2002_rev1.Status.SpeedPv','5780_PU2001_rev1.Status.SpeedPv','5780_PU1002_rev1.Status.SpeedPv','5780_PU1001_rev1.Status.SpeedPv','MX2002.status.Iavg','MX2001.status.Iavg','MX1002.status.Iavg','MX1001.status.Iavg','5760_PU2010.Status.FrequencyPv','5760_PU1010.Status.FrequencyPv','5760_PU2009.Status.FrequencyPv','5760_PU1009.Status.FrequencyPv','5760_PU2008.Status.FrequencyPv','5760_PU1008.Status.FrequencyPv','5760_PU2007.Status.FrequencyPv','5760_PU1007.Status.FrequencyPv','LIT2613.IO.Value','LIT1613.IO.Value','LIT2601.IO.Value','LIT1601.IO.Value','Shouxin_601_PIT_016.Value','Shouxin_601_PIT_017.Value','Shouxin_601_PIT_018.Value','Shouxin_601_PU01A.Current','Shouxin_601_PU01A.Frecuency','Shouxin_601_PU01B.Current','Shouxin_601_PU01B.Frecuency','Shouxin_601_PU01C.Current','Shouxin_601_PU01C.Frecuency','Shouxin_601_PU02A.Current','Shouxin_601_PU02A.Frecuency','Shouxin_601_PU02B.Current','Shouxin_601_PU02B.Frecuency','Shouxin_601_PU02C.Current','Shouxin_601_PU02C.Frecuency','LM2017.status.Iavg','LM2016.status.Iavg','LM2015.status.Iavg','LM2014.status.Iavg','LM2013.status.Iavg','LM2012.status.Iavg','LM2011.status.Iavg','LM2010.status.Iavg','LM2009.status.Iavg','LM2008.status.Iavg','LM2007.status.Iavg','LM2006.status.Iavg','LM2005.status.Iavg','LM2004.status.Iavg','LM2003.status.Iavg','LM2002.status.Iavg','LM2001.status.Iavg','LM1017.status.Iavg','LM1016.status.Iavg','LM1015.status.Iavg','LM1014.status.Iavg','LM1013.status.Iavg','LM1012.status.Iavg','LM1011.status.Iavg','LM1010.status.Iavg','LM1009.status.Iavg','LM1008.status.Iavg','LM1007.status.Iavg','LM1006.status.Iavg','LM1005.status.Iavg','LM1004.status.Iavg','LM1003.status.Iavg','LM1002.status.Iavg','LM1001.status.Iavg','5730_RP0003.AI_HYS01B5002_PIA','5730_RP0003.AI_HYS01B5001_PIA','5730_RP0002.AI_HYS01B5002_PIA','5730_RP0002.AI_HYS01B5001_PIA','5730_RP0001.AI_HYS01B5002_PIA','5730_RP0001.AI_HYS01B5001_PIA','5730_RP0003.AI_DRV02M1001_EIA','5730_RP0003.AI_DRV01M1001_EIA','5730_RP0002.AI_DRV02M1001_EIA','5730_RP0002.AI_DRV01M1001_EIA','5730_RP0001.AI_DRV02M1001_EIA','5730_RP0001.AI_DRV01M1001_EIA','5730_RP0003.Mean_gap','5730_RP0002.Mean_gap','5730_RP0001.Mean_gap','WCT5841WE120.Value','WCT0305.Value','WCT0304.Value','WCT0303.Value','5798_CM0005.Current','5798_CM0004.Current','5798_CM0003.Current','5798_CM0002.Current','5798_CM0001.Current','FE0004.status.Iavg','FE0003.status.Iavg','FE0002.status.Iavg','FE0001.status.Iavg','5730_PU0004_rev1.frecuencyRpm','5730_PU0003_rev1.frecuencyRpm','5730_PU0002_rev1.frecuencyRpm','5730_PU0001_rev1.frecuencyRpm','5771_PU1001.Status.FrequencyPv','5771_PU1002.Status.FrequencyPv','5771_PU2001.Status.FrequencyPv','5771_PU2002.Status.FrequencyPv','5760_PU1001.status.Speed','5760_PU1001.status.TotalCurrent','5760_PU1002.status.Speed','5760_PU1002.status.TotalCurrent','5760_PU2001.status.Speed','5760_PU2001.status.TotalCurrent','5760_PU2002.status.Speed','5760_PU2002.status.TotalCurrent','5740_PU1001.status.Speed','5740_PU1001.status.TotalCurrent','5740_PU1002.status.Speed','5740_PU1002.status.TotalCurrent','5740_PU1003.status.Speed','5740_PU1003.status.TotalCurrent','5740_PU1004.status.Speed','5740_PU1004.status.TotalCurrent','5740_PU2001.status.Speed','5740_PU2001.status.TotalCurrent','5740_PU2002.status.Speed','5740_PU2002.status.TotalCurrent','5740_PU2003.status.Speed','5740_PU2003.status.TotalCurrent','5740_PU2004.status.Speed','5740_PU2004.status.TotalCurrent','FIT2721.IO.Value','FIT1721.IO.Value','5780_RS2001.Flow_PV','5780_RS1001.Flow_PV','LIT0301A.IO.Value','LIT0301B.IO.Value','LIT0302A.IO.Value','LIT0302B.IO.Value','LIT0302C.IO.Value','LIT0311.IO.Value','LIT0312.IO.Value','LIT0313.IO.Value','LIT0314.IO.Value','LIT0315.IO.Value','LIT0316.IO.Value','LIT0317.IO.Value','LIT0318.IO.Value','5841_CB120.WIT','WIT0303.IO.Value','WIT0304.IO.Value','WIT0305.IO.Value','LIT1304.IO.Value','LIT2304.IO.Value','5730_PU0001.status.TotalCurrent','5730_PU0002.status.TotalCurrent','5730_PU0003.status.TotalCurrent','5730_PU0004.status.TotalCurrent','FIT1301.IO.Value','FIT2301.IO.Value','5740_BM1001.M101_ELEC','5740_BM1001.M101_POW','5740_BM1002.M101_ELEC','5740_BM1002.M101_POW','5740_BM2001.M101_ELEC','5740_BM2001.M101_POW','5740_BM2002.M101_ELEC','5740_BM2002.M101_POW','PIT1503.IO.Value','FIT1401.IO.Value','PIT1504.IO.Value','FIT1402.IO.Value','PIT2503.IO.Value','FIT2401.IO.Value','PIT2504.IO.Value','FIT2402.IO.Value','LIT1401.IO.Value','LIT1402.IO.Value','LIT2401.IO.Value','LIT2402.IO.Value','5760_BL0001.PA1','5760_BL0002.PA1','5760_BL0003.PA1','5760_BL0001.P2','5760_BL0002.P2','5760_BL0003.P2','AG1001.status.Iavg','AG1002.status.Iavg','AG1003.status.Iavg','AG1004.status.Iavg','AG1005.status.Iavg','AG1006.status.Iavg','AG2001.status.Iavg','AG2002.status.Iavg','AG2003.status.Iavg','AG2004.status.Iavg','AG2005.status.Iavg','AG2006.status.Iavg','LIT1604.IO.Value','LIT2604.IO.Value','5771_TH1001.Rotating_Pressure','5771_TH1001.Lifting_Pressure','5771_TH1001.MudBed_Pressure','5771_TH1001.Oil_Te','5771_TH1001.Rake_Thickness','5771_TH2001.Rotating_Pressure','5771_TH2001.Lifting_Pressure','5771_TH2001.MudBed_Pressure','5771_TH2001.Rake_Thickness','5771_TH2001.Oil_Te','5771_PU1001.Status.Current','5771_PU1001.Status.Frequency.Pv','5771_PU1002.Status.Current','5771_PU1002.Status.Frequency.Pv','5771_PU2001.Status.Current','5771_PU2001.Status.Frequency.Pv','5771_PU2002.Status.Current','5771_PU2002.Status.Frequency.Pv','5772_PU1001.Status.Current','5772_PU1002.status.Current','5772_PU1002.Status.FrequencyPv','5772_PU1001.Status.FrequencyPv','5772_PU2001.status.Current','5772_PU2001.Status.FrequencyPv','5772_PU2002.status.Current','5772_PU2002.Status.FrequencyPv','PIT0911.IO.Value','FIT0911.IO.Value','WIT1741.IO.Value','WIT2741.IO.Value','5772_CB1001.status.Iavg','5772_CB2001.status.Iavg','LIT1742.IO.Value','LIT2742.IO.Value','5780_PU1001.Status.Current','5780_PU1001.Status.FrequencyPv','5780_PU1002.Status.Current','5780_PU1002.Status.FrequencyPv','5780_PU2001.Status.Current','5780_PU2001.Status.FrequencyPv','5780_PU2002.Status.Current','5780_PU2002.Status.FrequencyPv','FIT1801.IO.Value','FIT2801.IO.Value','FIT1802.IO.Value','FIT2802.IO.Value','5791_PU0001.status.Iavg','5791_PU0002.status.Iavg','5791_PU0003.status.Iavg','PIT0961A.IO.Value','PIT0961B.IO.Value','PIT0961C.IO.Value','FIT0963.IO.Value','5780_TH1001.M_MudLevel','5780_TH1001.C_OilTemperature','5780_TH1001.Bar_OilPressure','5780_TH1001.Bar_MudPressure','5780_TH2001.M_MudLevel','5780_TH2001.C_OilTemperature','5780_TH2001.Bar_OilPressure','5780_TH2001.Bar_MudPressure','5794_PU0002.status.Speed','5794_PU0002.status.TotalCurrent','PIT0921B.IO.Value','5794_PU0001.status.Speed','5794_PU0001.status.TotalCurrent','PIT0921A.IO.Value','5794_PU0003.status.Speed','5794_PU0003.status.TotalCurrent','PIT0921C.IO.Value','5794_PU0004.status.Speed','5794_PU0004.status.TotalCurrent','PIT0922A.IO.Value','5794_PU0005.status.Speed','5794_PU0005.status.TotalCurrent','PIT0922B.IO.Value','5794_PU0006.status.Speed','5794_PU0006.status.TotalCurrent','PIT0922C.IO.Value','LIT0921.IO.Value','LIT0961.IO.Value','LIT0941.IO.Value','LIT0942.IO.Value','LIT1803.IO.Value','5780_MX1001.status.Iavg','LIT2803.IO.Value','5780_MX2001.status.Iavg','N1920_FIT0001.IO.Value','5760_FC1001_2.Status.Thickness_Pv','5760_FC1003_4.Status.Thickness_Pv','5760_FC1005_6.Status.Thickness_Pv','5760_FC1001_2.Status.Valve1_Pv','5760_FC1001_2.Status.Valve2_Pv','5760_FC1003_4.Status.Valve1_Pv', '5760_FC1003_4.Status.Valve2_Pv','5760_FC1005_6.Status.Valve1_Pv','5760_FC1005_6.Status.Valve2_Pv','5760_FC2001_2.Status.Thickness_Pv','5760_FC2003_4.Status.Thickness_Pv','5760_FC2005_6.Status.Thickness_Pv','5760_FC2001_2.Status.Valve1_Pv','5760_FC2001_2.Status.Valve2_Pv','5760_FC2003_4.Status.Valve1_Pv','5760_FC2003_4.Status.Valve2_Pv','5760_FC2005_6.Status.Valve1_Pv','5760_FC2005_6.Status.Valve2_Pv','5851CB110.M101_II', '5851CB120.M101_II','5941CB110.M101_II','WCT1741.Value','WCT2741.Value','FIT0962.IO.Value','5730_RP0001.AI_FBJ01B9501_WICA','5730_RP0002.AI_FBJ01B9501_WICA','5730_RP0003.AI_FBJ01B9501_WICA','5730_RP0001.SCEW_R_MINUS_L','5730_RP0002.SCEW_R_MINUS_L','5730_RP0003.SCEW_R_MINUS_L',

	'VF1001.HZ1', 'VF1002.HZ1',	'VF1003.HZ1', 'VF1004.HZ1', 'VF1005.HZ1', 'VF1006.HZ1', 'VF1007.HZ1', 'VF1008.HZ1', 'VF1009.HZ1', 'VF1010.HZ1',
	'VF1001.HZ2', 'VF1002.HZ2', 'VF1003.HZ2', 'VF1004.HZ2', 'VF1005.HZ2', 'VF1006.HZ2', 'VF1007.HZ2', 'VF1008.HZ2', 'VF1009.HZ2', 'VF1010.HZ2',
	'VF2001.HZ1', 'VF2002.HZ1', 'VF2003.HZ1', 'VF2004.HZ1', 'VF2005.HZ1', 'VF2006.HZ1', 'VF2007.HZ1', 'VF2008.HZ1', 'VF2009.HZ1', 'VF2010.HZ1',
	'VF2001.HZ2', 'VF2002.HZ2', 'VF2003.HZ2', 'VF2004.HZ2', 'VF2005.HZ2', 'VF2006.HZ2', 'VF2007.HZ2', 'VF2008.HZ2', 'VF2009.HZ2', 'VF2010.HZ2',
	'5740_BM1001.M101_FRE','5740_BM1002.M101_FRE','5740_BM2001.M101_FRE','5740_BM2002.M101_FRE',

    'FIT0962.IO.Value','FIT0961.IO.Value','FQI0962.Value','FQI0963.Value', 'SystimeHour', 'SystimeMin','SysTimeSec'
	)";

    $stmt = sqlsrv_query($conn, $sql);
    if ($stmt === false) {
        echo "Mala consulta";
        die(print_r(sqlsrv_errors(), true));
    }

    // guardamos la data  de la consulta en un array
    $dato = array();
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $dato[$row['TagName']] = $row['pvalor'];
    }
    $dbhour = sprintf("%02d",$dato['SysTimeHour']);
    $dbmin  = sprintf("%02d",$dato['SysTimeMin']);
    $dbsec = sprintf("%02d",$dato['SysTimeSec']);

    $dbtime =  $dbhour.':'.$dbmin.':'.$dbsec;
    $dato['queryTime'] = $dbtime;

    if ($hora >= "00:00:00" & $hora <= "08:00:00") {
        //segundo turno
        $inicioTurno = date("Y-m-d 20:00:00", strtotime("-1 day"));
        $inicioTurnoAnterior = date("Y-m-d 08:00:00", strtotime("-1 day"));
        $inicioDiaPrevio = date("Y-m-d 08:00:00", strtotime("-2 day"));
        $inicioDia = strtotime($inicioTurnoAnterior);
    } else {
        if (($hora >= "20:00:00") & ($hora <= "23:59:59")) {
            //segundo turno
            $inicioTurno = date("Y-m-d 20:00:00");
            $inicioTurnoAnterior = date("Y-m-d 08:00:00");
            $inicioDiaPrevio = date("Y-m-d 08:00:00", strtotime("-1 day"));
            $inicioDia = strtotime($inicioTurnoAnterior);

        } else {
            //primer turno
            $inicioTurno = date("Y-m-d 08:00:00");
            $inicioTurnoAnterior = date("Y-m-d 20:00:00", strtotime("-1 day"));
            $inicioDiaPrevio = date("Y-m-d 08:00:00", strtotime("-1 day"));
            $inicioDia = strtotime($inicioTurno);

        }
    }
    $queryInicioTurno = "SELECT TagName, round(Value,2) as pvalor from Runtime.dbo.AnalogHistory WHERE TagName IN ('WCT0303.Value','WCT1741.Value','WCT2741.Value') AND DateTime = '$inicioTurno'";

    $queryiniciTurnoAnterior = "SELECT TagName, round(Value,2) as pvalor from Runtime.dbo.AnalogHistory WHERE TagName IN ('WCT0303.Value','WCT1741.Value','WCT2741.Value') AND DateTime = '$inicioTurnoAnterior'";

    $queryInicioDiaPrevio = "SELECT TagName, round(Value,2) as pvalor from Runtime.dbo.AnalogHistory WHERE TagName IN ('WCT0303.Value','WCT1741.Value','WCT2741.Value') AND DateTime = '$inicioDiaPrevio'";

// sacamos los datos de inicio de guardiaa

    $stmt = sqlsrv_query($conn, $queryInicioTurno);
    if ($stmt === false) {
        echo "Mala consulta";
        die(print_r(sqlsrv_errors(), true));
    }
// SOLUCION 1
// foreach ($datos_produccion["inicioGuardia"] as &$dato) {
//     $row = sqlsrv_fetch_array($stmt,SQLSRV_FETCH_ASSOC);
//     echo key($dato);
//     $dato = $row["pvalor"];
//     //echo "nueva ".$value.PHP_EOL;

// }

// solucion corta
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $datos_produccion["inicioGuardia"][$row['TagName']] = $row['pvalor'];
    }
// sacamos los datos de la guardia anterior
    $stmt = sqlsrv_query($conn, $queryiniciTurnoAnterior);
    if ($stmt === false) {
        echo "Mala consulta";
        die(print_r(sqlsrv_errors(), true));
    }
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $datos_produccion["guardiaAnterior"][$row['TagName']] = $row['pvalor'];
    }
//sacamos los datos del dia previo
    $stmt = sqlsrv_query($conn, $queryInicioDiaPrevio);
    if ($stmt === false) {
        echo "Mala consulta";
        die(print_r(sqlsrv_errors(), true));
    }
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $datos_produccion["guardiaDiaAnterior"][$row['TagName']] = $row['pvalor'];
    }
// liberamos los recursos y cerramos la conexiòn
    sqlsrv_free_stmt($stmt);
    sqlsrv_close($conn);

// llenamos los datos para enviar a firebase datos

    // foreach ($firebasedata as $tag => $value) {
    //     $firebasedata[$tag] = $dato[$tag];
    // }

    $segundosTurno = time() - strtotime($inicioTurno);
    $segundosDia = time() - $inicioDia;

    $tonelajeDisenoDia = $segundosDia * $ton_produccion_diseno;
    $dato['tonelajedisenodia'] = $tonelajeDisenoDia;
    $tonelajeDisenoTurno = $segundosTurno * $ton_produccion_diseno;
    $dato['tonelajedisenoTurno'] = $tonelajeDisenoTurno;
    //echo PHP_EOL;
    //echo "TONELAJE DISEÑO DIA ES: ".$tonelajeDisenoDia.PHP_EOL;
    //echo "TONELAJE DISEÑO TURNO: ".$tonelajeDisenoTurno.PHP_EOL;
    $tonelajeActualTurno = ($dato['WCT1741.Value'] + $dato['WCT2741.Value']) - ($datos_produccion['inicioGuardia']['WCT1741.Value'] + $datos_produccion['inicioGuardia']['WCT2741.Value']);
    $dato['tonelajeactualturno'] = $tonelajeActualTurno;
    //echo "TONELAJE ACTUAL TURNO ES: ".$tonelajeActualTurno.PHP_EOL;
    //$tonelajeInicioDia =((time()-$inicioDia)>= 43200)?$datos_produccion['guardiaAngerior']:$datos_produccion['inicioGuardia']; --- metodo abreviado para operador ternario

    if (time() - $inicioDia >= 43200) {
        $tonelajeInicioDia = $datos_produccion['guardiaAnterior'];
        $tonelajeDiaAnterior = ($datos_produccion['guardiaAnterior']['WCT1741.Value'] + $datos_produccion['guardiaAnterior']['WCT2741.Value']) - ($datos_produccion['guardiaDiaAnterior']['WCT1741.Value'] + $datos_produccion['guardiaDiaAnterior']['WCT2741.Value']);
        $dato['tonelajeDiaAnterior'] = $tonelajeDiaAnterior;
        $tonelajeEnvioActualDia = $dato['WCT0303.Value'] - $datos_produccion['guardiaAnterior']['WCT0303.Value'];
        $dato['tonelajeEnvioActualDia'] = $tonelajeEnvioActualDia;

    } else {
        $tonelajeInicioDia = $datos_produccion['inicioGuardia'];
        $tonelajeDiaAnterior = ($datos_produccion['inicioGuardia']['WCT1741.Value'] + $datos_produccion['inicioGuardia']['WCT2741.Value']) - ($datos_produccion['guardiaDiaAnterior']['WCT1741.Value'] + $datos_produccion['guardiaDiaAnterior']['WCT2741.Value']);
        $dato['tonelajeDiaAnterior'] = $tonelajeDiaAnterior;
        $tonelajeEnvioActualDia = $dato['WCT0303.Value'] - $datos_produccion['inicioGuardia']['WCT0303.Value'];
        $dato['tonelajeEnvioActualDia'] = $tonelajeEnvioActualDia;
    }

    $tonelajeActualDia = ($dato['WCT1741.Value'] + $dato['WCT2741.Value']) - ($tonelajeInicioDia['WCT1741.Value'] + $tonelajeInicioDia['WCT2741.Value']);
    $dato['tonelajeActualDia'] = $tonelajeActualDia;
    //echo "TONELAJE ACTUAL DIA ES: ".$tonelajeActualDia.PHP_EOL;

    $tonelajeGuardiaAnterior = ($datos_produccion['inicioGuardia']['WCT1741.Value'] + $datos_produccion['inicioGuardia']['WCT2741.Value']) - ($datos_produccion['guardiaAnterior']['WCT1741.Value'] + $datos_produccion['guardiaAnterior']['WCT2741.Value']);
    $dato['tonelajeguardiaanterior'] = $tonelajeGuardiaAnterior;
    //echo "TONELAJE GUARDIA ANTERIOR: ". $tonelajeGuardiaAnterior.PHP_EOL;
    //echo "TONELAJE DIA ANTERIOR: ". $tonelajeDiaAnterior.PHP_EOL;

    $tonelajeEnvioActualTurno = $dato['WCT0303.Value'] - $datos_produccion['inicioGuardia']['WCT0303.Value'];
    $dato['tonelajeEnvioActualTurno'] = $tonelajeEnvioActualTurno;
    //echo "TONELAJE ENVIO ACTUAL GUARDIA  ES: ". $tonelajeEnvioActualTurno.PHP_EOL;
    //echo "TONELAE ENVIO ACTUAL DIA ES: ".$tonelajeEnvioActualDia.PHP_EOL;

    $tonelajeEnvioDisenoTurno = $segundosTurno * $ton_envio_diseno;
    $dato['tonelajeenviodisenoturno'] = $tonelajeEnvioDisenoTurno;

    $tonelajeEnvioDisenoDia = $segundosDia * $ton_envio_diseno;
    $dato['tonelajeenviodisenodia'] = $tonelajeEnvioDisenoDia;

    //echo "TONELAJE ENVIO DISEÑO DIA ES: ".$tonelajeEnvioDisenoDia.PHP_EOL;
    //echo "TONELAJE ENVIO DISEÑO TURNO ES:".$tonelajeEnvioDisenoTurno.PHP_EOL;

    $diferenciaProduccionTurno = $tonelajeActualTurno - $tonelajeDisenoTurno;
    $dato['diferenciaproduccionturno'] = $diferenciaProduccionTurno;
    $diferenciaProduccionDia = $tonelajeActualDia - $tonelajeDisenoDia;
    $dato['diferenciaproducciondia'] = $diferenciaProduccionDia;

    //echo "DIFERENCIA TONELAJE TURNO ES: ".$diferenciaProduccionTurno.PHP_EOL;
    //echo "DIFERENCIA TONELAJE DIA ES: ".$diferenciaProduccionDia.PHP_EOL;

    $diferenciaEnvioTurno = $tonelajeEnvioActualTurno - $tonelajeEnvioDisenoTurno;
    $dato['diferenciaenvioturno'] = $diferenciaEnvioTurno;
    $diferenciaEnvioDia = $tonelajeEnvioActualDia - $tonelajeEnvioDisenoDia;
    $dato['diferenciaenviodia'] = $diferenciaEnvioDia;
    $tonelajeEnvioGuardiaAnterior = $datos_produccion['inicioGuardia']['WCT0303.Value'] - $datos_produccion['guardiaAnterior']['WCT0303.Value'];
    $dato['tonelajeenvioguardiaanterior'] = $tonelajeEnvioGuardiaAnterior;

    $tonelajeEnvioDiaAnterior = $tonelajeInicioDia['WCT0303.Value'] - $datos_produccion['guardiaDiaAnterior']['WCT0303.Value'];
    $dato['tonelajeenviodiaanterior'] = $tonelajeEnvioDiaAnterior;

    //echo "DIFERENCIA TONELAJE ENVIO TURNO ES: ".$diferenciaEnvioTurno.PHP_EOL;
    //echo "DIFERENCIA TONELAJE ENVIO DIA ES: ".$diferenciaEnvioDia.PHP_EOL;

    //echo "TONELAJE ENVIO DIA ANTERIOR ES: ".$tonelajeEnvioDiaAnterior.PHP_EOL;

    //echo "TONELAJE ENVIO GUARDIA ANTERIOR ES: ".$tonelajeEnvioGuardiaAnterior.PHP_EOL;
    $dato['presion_procesos_l2'] = max($dato['PIT0921A.IO.Value'], $dato['PIT0921B.IO.Value'], $dato['PIT0921C.IO.Value']);
    $dato['presion_procesos_l1'] = max($dato['PIT0922A.IO.Value'], $dato['PIT0922B.IO.Value'], $dato['PIT0922C.IO.Value']);
    $dato['porcentajeMolino1001'] = ($dato['5740_BM1001.M101_POW'] / 8500) * 100;
    $dato['porcentajeMolino1002'] = ($dato['5740_BM1002.M101_POW'] / 8500) * 100;
    $dato['porcentajeMolino2001'] = ($dato['5740_BM2001.M101_POW'] / 8500) * 100;
    $dato['porcentajeMolino2002'] = ($dato['5740_BM2002.M101_POW'] / 8500) * 100;

    $dato['produccion_l1'] = $dato['WCT1741.Value'] - $tonelajeInicioDia['WCT1741.Value'];
    $dato['produccion_l2'] = $dato['WCT2741.Value'] - $tonelajeInicioDia['WCT2741.Value'];

    $filtros_l1 = 0;
    $filtros_l2 = 0;

    for ($i = 1; $i < 10; $i++) {
        if ($dato["VF100{$i}.HZ1"] > 0 && $dato["VF100{$i}.HZ2"] > 0) {
            $filtros_l1 = $filtros_l1 + 1;
        }
        if ($dato["VF200{$i}.HZ1"] > 0 && $dato["VF200{$i}.HZ2"] > 0) {
            $filtros_l2 = $filtros_l2 + 1;
        }
    }
    if ($dato["VF1010.HZ1"] > 0 && $dato["VF1010.HZ2"] > 0) {
        $filtros_l1 = $filtros_l1 + 1;
    }
    if ($dato["VF2010.HZ1"] > 0 && $dato["VF2010.HZ2"] > 0) {
        $filtros_l2 = $filtros_l2 + 1;
    }
    $dato['filtros_l1'] = $filtros_l1;
    $dato['filtros_l2'] = $filtros_l2;

    foreach ($datos_general as &$area) {
        foreach ($area as $key => &$value) {
            $value = $dato[$key];
        }
    }
    echo $datos_general['estado_general']['queryTime'];
    echo $datos_general['circuito_4']['FIT1802.IO.Value'].PHP_EOL;
    echo $datos_general['circuito_4']['FIT2802.IO.Value'].PHP_EOL;
    return $datos_general;
}

function test_internet_connection()
{

    $connection = fsockopen("wwww.ccrshp.com", 443);
    if ($connection) {
        return true;
    } else {
        return false;
    }
}

co::run(function () {
    
    
    global $datos_general;
    global $firebasedata;

    $data_registro = array(
        'tipo_usuario' => 'localbd',
        'tipo_operacion' => 'registro',
    );
    $proxy_internet = false;
    $internet = false;

    $cli = new OpenSwoole\Coroutine\Http\Client('ccrshp.com', 443, 1);
//     $cli2 = new Co\http\Client('127.0.0.1', 9502);
    $cli->set(['websocket_compression' => true,
        'http_proxy_host' => '192.168.85.1',
        'http_proxy_port' => 3128,
        'Proxy-Connection' => 'keep-alive',
        'Connection' => 'keep-alive',
      //  'enable_coroutine' =>false,

    ]);
    print('cliente creado');
    $ret = $cli->upgrade("/sw/");

    if ($ret) {
        echo "conectado";
        $recibido = $cli->recv(3);
        echo "mensaje de bienvenida es: " . $recibido . PHP_EOL;
        $cli->push(json_encode($data_registro), SWOOLE_WEBSOCKET_OPCODE_TEXT,
            SWOOLE_WEBSOCKET_FLAG_FIN | SWOOLE_WEBSOCKET_FLAG_COMPRESS);
        $recibido = $cli->recv(3);
        echo $recibido . PHP_EOL;
        co::sleep(1);
        $proxy_internet = true;

    } else {
        $cli_internet = new OpenSwoole\Coroutine\Http\Client('ccrshp.com', 443, 1);
        $cli_internet->set(['websocket_compression' => true,
            'Proxy-Connection' => 'keep-alive',
            'Connection' => 'keep-alive',

        ]);
        print('cliente internet creado');
        $ret_internet = $cli_internet->upgrade("/sw/");
        $internet = true;

    }
    while (1) {

        if ($proxy_internet) {

            $intento = 0;
            while ($proxy_internet) {

                echo "trabajando con la red de shougang" . PHP_EOL;
                $dato = consultabase($datos_produccion, $inicioTurno, $inicioTurnoAnterior, $inicioDiaPrevio, $inicioDia, $datos_general, $firebasedata);
               //sendToFirebase($firebasedata);
                $dato['tipo_operacion'] = "refresco";
                $dato['tipo_usuario'] = "localbd";

                //var_dump($dato);
                $cli->push(json_encode($dato), SWOOLE_WEBSOCKET_OPCODE_TEXT,
                    SWOOLE_WEBSOCKET_FLAG_FIN | SWOOLE_WEBSOCKET_FLAG_COMPRESS);
                //    echo microtime(true).PHP_EOL;
                $recibido = $cli->recv(3);
                echo "recibido es:" . $recibido . PHP_EOL;
                if (strpos($recibido, "mensaje recibido")) {
                    echo "mensaje recepcionado" . PHP_EOL;
                } else {
                    echo "no hay respuesta del servidor" . PHP_EOL;

                    $proxy_internet = $intento >= 5 ? false : true;
                    $internet = $intento >= 5 ? true : false;

                    while ($intento <= 5) {
                        $cli = new OpenSwoole\Coroutine\Http\Client('ccrshp.com', 443, true);
                        $cli->set(['websocket_compression' => true,
                            'http_proxy_host' => '192.168.85.1',
                            'http_proxy_port' => 3128,
                            'Proxy-Connection' => 'keep-alive',
                            'Connection' => 'keep-alive',

                        ]);
                        $ret = $cli->upgrade("/sw/");

                        if ($ret == 1) {
                            echo "reconectado." . PHP_EOL;
                            $intento = 0;
                            $recibido = $cli->recv(3);
                            echo "mensaje de bienvenida es: " . $recibido . PHP_EOL;
                            if (strpos($recibido, "bienvenida")) {
                                echo "reconexiòn exiosa " . PHP_EOL;
                                $cli->push(json_encode($data_registro), SWOOLE_WEBSOCKET_OPCODE_TEXT,
                                    SWOOLE_WEBSOCKET_FLAG_FIN | SWOOLE_WEBSOCKET_FLAG_COMPRESS);
                                echo $cli->recv();
                                break;
                            } else {
                                echo "no se recepciona mensaje de bienvenida, cerrando comunicaciòn" . PHP_EOL;
                                $cli->close();
                            }
                        } else {
                            echo "no se ha podido establecer comunicaciòn con el socket, reintentando" . PHP_EOL;
                            co::sleep(1);
                            $intento++;
                        }

                    }

                }
                //     echo "durmiendo".PHP_E   OL;
                //    echo memory_get_usage();
                //    echo PHP_EOL;
                co::sleep(1);
                //     echo microtime(true).PHP_EOL;
            }
        } else {

            $cli_internet = new OpenSwoole\Coroutine\Http\Client('ccrshp.com', 443, 1);
            $cli_internet->set(['websocket_compression' => true,
                'Proxy-Connection' => 'keep-alive',
                'Connection' => 'keep-alive',

            ]);
            print('cliente modem creado');
            $ret_internet = $cli_internet->upgrade("/sw/");
            $recibido = $cli_internet->recv(3);
            echo "mensaje de bienvenida es: " . $recibido . PHP_EOL;
            $cli_internet->push(json_encode($data_registro), SWOOLE_WEBSOCKET_OPCODE_TEXT,
                SWOOLE_WEBSOCKET_FLAG_FIN | SWOOLE_WEBSOCKET_FLAG_COMPRESS);
            $recibido = $cli_internet->recv(3);
            echo $recibido . PHP_EOL;
            co::sleep(1);
            $intento = 0;

            // se configura para que trabaje por una hora, luego de ese tiempo volvera a intentar conectarse con la red shougang
            $connectionOut = time() + 3600;
            while ($internet) {
                echo "trabajando con el modem" . PHP_EOL;
                $dato = consultabase($datos_produccion, $inicioTurno, $inicioTurnoAnterior, $inicioDiaPrevio, $inicioDia, $datos_general, $firebasedata);
               // sendToFirebase($firebasedata);
                $dato['tipo_operacion'] = "refresco";
                $dato['tipo_usuario'] = "localbd";
                //var_dump($dato);
                $cli_internet->push(json_encode($dato), SWOOLE_WEBSOCKET_OPCODE_TEXT,
                    SWOOLE_WEBSOCKET_FLAG_FIN | SWOOLE_WEBSOCKET_FLAG_COMPRESS);

                //    echo microtime(true).PHP_EOL;
                $recibido = $cli_internet->recv(4);
                echo "recibido es:" . $recibido . PHP_EOL;
                if (strpos($recibido, "mensaje recibido")) {
                    echo "mensaje recepcionado" . PHP_EOL;
                } else {
                    echo "no hay respuesta del servidor" . PHP_EOL;
                    $proxy_internet = $intento >= 5 ? true : false;
                    $internet = $intento >= 5 ? false : true;
                    while ($intento <= 5) {
                        $cli_internet = new OpenSwoole\Coroutine\Http\Client('ccrshp.com', 443, true);
                        $cli_internet->set(['websocket_compression' => true,
                            'Proxy-Connection' => 'keep-alive',
                            'Connection' => 'keep-alive',

                        ]);
                        $ret_internet = $cli_internet->upgrade("/sw/");

                        if ($ret_internet == 1) {
                            echo "reconectado." . PHP_EOL;
                            $intento = 0;
                            $recibido = $cli_internet->recv(3);
                            echo "mensaje de bienvenida es: " . $recibido . PHP_EOL;
                            if (strpos($recibido, "bienvenida")) {
                                echo "reconexiòn exiosa " . PHP_EOL;
                                $cli_internet->push(json_encode($data_registro), SWOOLE_WEBSOCKET_OPCODE_TEXT,
                                    SWOOLE_WEBSOCKET_FLAG_FIN | SWOOLE_WEBSOCKET_FLAG_COMPRESS);
                                echo $cli_internet->recv(3);
                                break;
                            } else {
                                echo "no se recepciona mensaje de bienvenida, cerrando comunicaciòn" . PHP_EOL;
                                $cli_internet->close();
                            }
                        } else {
                            echo "no se ha podido establecer comunicaciòn con el socket, reintentando" . PHP_EOL;
                            co::sleep(1);
                            $intento++;
                        }

                    }
                }
                //echo "durmiendo".PHP_EOL;
                //echo memory_get_usage();
                co::sleep(1);
                //     echo microtime(true).PHP_EOL;
                if (time() > $connectionOut) {
                    $internet = false;
                    $proxy_internet = true;
                }

            }

        }

    }

});

//echo microtime(true);

//print_r($dato);
