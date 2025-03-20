<?php

$result = array();
include("db_connect.php");
//include("sys_login.php");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
/*
//$_POST["accion"] = "generar_transacciones";
$_POST["accion"] = "generar_transacciones";
$_POST["fecha"] = "2022-05-20";
//$_POST["log"] = 1;
$login['id'] = 0;
//echo 'Iniciando ...';
*/
//*******************************************************************************************************************
//*******************************************************************************************************************
// LISTAR TRANSACCIONES
//*******************************************************************************************************************
//*******************************************************************************************************************
function listar_transacciones($fecha){

	global $mysqli;
	$_GET["today"] = date('Y-m-d', strtotime('+1 days', strtotime($fecha)));

	$dates = (object) [
		"today"       => date('Y-m-d', strtotime($_GET["today"])),
		"yesterday"   => date('Y-m-d', strtotime('-1 days', strtotime($_GET["today"]))),
		"yearmonth"   => date('Y-m-01', strtotime('-1 days', strtotime($_GET["today"]))),
		"year"        => date('Y-01-01', strtotime('-1 days', strtotime($_GET["today"]))),
		"startmonth"  => date('Y-m-01', strtotime('-1 days', strtotime($_GET["today"]))),
		"startyear"   => date('Y-01-01', strtotime('-1 days', strtotime($_GET["today"]))),
		"onlyday"     => (int) date('d', strtotime('-1 days', strtotime($_GET["today"]))),
		"onlylastday" => (int) date("t", strtotime('-1 days', strtotime($_GET["today"]))),
	];
	//*******************************************************************************************************************
	//*******************************************************************************************************************
	// DIARIO
	//*******************************************************************************************************************
	//*******************************************************************************************************************

	$query_tabla_ventas_x_producto_diario = "	
		SELECT
			tvp.id_reporte_teleservicios_concepto id_concepto,
			tc.concepto,
			tvp.num_tickets_apostado,
			tvp.total_tickets_apostado,
			tvp.promedio,
			tvp.num_tickets_calculado,
			tvp.total_tickets_calculado,
			IF((tc.id = '1' OR tc.id = '16'),0,tvp.resultado) as resultado,
			IF((tc.id = '1' OR tc.id = '16'),0,tvp.hold) as hold,
			tvp.num_tickets_pagado,
			tvp.total_tickets_pagado,
			tvp.porcentaje_tickets_pagado 
		FROM
			tbl_reporte_teleservicios_ventas_x_producto tvp
			JOIN tbl_reporte_teleservicios_concepto tc ON tc.id = tvp.id_reporte_teleservicios_concepto 
		WHERE
			tvp.fecha = '$fecha' 
			AND tvp.id_estado =1
		ORDER BY tc.id ASC
		";
	$res_query_tabla_ventas_x_producto_diario = $mysqli->query($query_tabla_ventas_x_producto_diario);

    //echo "<pre>";print_r($query_tabla_ventas_x_producto_diario);echo "</pre>";

	if ($mysqli->error) {
		$result["error_query_tabla_ventas_x_producto_diario"] = $mysqli->error;
	}

	$list_tabla_ventas_x_producto_diario = array();
	$list_tabla_ventas_transaccionales_diario = array();
	$list_tabla_ventas_x_caja_diario = array();
	$list_tabla_ventas_recargas_web_diario = array();
	$list_tabla_ventas_recargas_web_diario_dif = array();
	$list_tabla_ventas_terminales_diario = array();
	$list_tabla_ventas_otros_pagos_diario = array();
	$list_tabla_ventas_jv_x_juego_diario = array();

    $calimaco_row = "";
	while ($li = $res_query_tabla_ventas_x_producto_diario->fetch_assoc()) {
		if((int) $li["id_concepto"] >= 1 && (int) $li["id_concepto"] <= 2) {
			$list_tabla_ventas_x_producto_diario[] = $li;
		}
        if((int) $li["id_concepto"] == 16) {//a d calimaco
            $calimaco_row = $li;
			//$list_tabla_ventas_x_producto_diario[] = $li;
		}
        if((int) $li["id_concepto"] == 17 ){//horses
			$list_tabla_ventas_jv_x_juego_diario[] = $li;
		}
		/*if((int) $li["id_concepto"] === 4) { quitar torito
			$list_tabla_ventas_transaccionales_diario[] = $li;
		}*/
		if((int) $li["id_concepto"] >= 5 && (int) $li["id_concepto"] <= 6) {
			$list_tabla_ventas_x_caja_diario[] = $li;
		}
		if((int) $li["id_concepto"] >= 7 && (int) $li["id_concepto"] <= 8) {
			$list_tabla_ventas_recargas_web_diario_dif[] = $li;
		}
		if((int) $li["id_concepto"] === 9) {
			$list_tabla_ventas_terminales_diario[] = $li;
		}
		if((int) $li["id_concepto"] >= 10 && (int) $li["id_concepto"] <= 11) {
			$list_tabla_ventas_otros_pagos_diario[] = $li;
		}
		if((int) $li["id_concepto"] >= 12 && (int) $li["id_concepto"] <= 15) {
			$list_tabla_ventas_jv_x_juego_diario[] = $li;
		}
        
	}
    //move calimaco row before bingo
    $temp = array_slice($list_tabla_ventas_x_producto_diario, 0, 1, true) +
    		array( 2 => $calimaco_row) +
    		array_slice($list_tabla_ventas_x_producto_diario, 1 , 2, true) ;
    ;
	$list_tabla_ventas_x_producto_diario = $temp;

	if (count($list_tabla_ventas_jv_x_juego_diario) > 0) {

		$result_temp=array();
		$result_temp['id_concepto']=0;
		$result_temp['concepto']='Acumulado';
		$result_temp['num_tickets_apostado']=0;
		$result_temp['total_tickets_apostado']=0;
		$result_temp['promedio']=0;
		$result_temp['num_tickets_calculado']=0;
		$result_temp['total_tickets_calculado']=0;
		$result_temp['resultado']=0;
		$result_temp['hold']=0;
		$result_temp['num_tickets_pagado']=0;
		$result_temp['total_tickets_pagado']=0;
		$result_temp['porcentaje_tickets_pagado']=0;
		
		$cont_array_detalle = 0;
		foreach ($list_tabla_ventas_jv_x_juego_diario as $value) {
			$cont_array_detalle++;
			$result_temp['num_tickets_apostado'] += $value["num_tickets_apostado"];
			$result_temp['total_tickets_apostado'] += $value["total_tickets_apostado"];

			$result_temp['num_tickets_calculado'] += $value["num_tickets_calculado"];
			$result_temp['total_tickets_calculado'] += $value["total_tickets_calculado"];

			$result_temp['num_tickets_pagado'] += $value["num_tickets_pagado"];
			$result_temp['total_tickets_pagado'] += $value["total_tickets_pagado"];
		}

		$result_temp['resultado'] = $result_temp["total_tickets_apostado"]-$result_temp["total_tickets_calculado"];
		if ((float)$result_temp['total_tickets_apostado']<>0 && (float)$result_temp['num_tickets_apostado']<>0){
			$result_temp['promedio'] = ((float)$result_temp['total_tickets_apostado']/(float)$result_temp['num_tickets_apostado']);
		}
		if ((float)$result_temp['resultado']<>0 && (float)$result_temp['total_tickets_apostado']<>0){
			$result_temp['hold'] = (((float)$result_temp['resultado']/(float)$result_temp['total_tickets_apostado'])*100);
		}
		if ((float)$result_temp['num_tickets_pagado']<>0 && (float)$result_temp['num_tickets_calculado']<>0){
			$result_temp['porcentaje_tickets_pagado'] = (((float)$result_temp['num_tickets_pagado']/(float)$result_temp['num_tickets_calculado'])*100);
		}

		$list_tabla_ventas_jv_x_juego_diario[]=$result_temp;
		$list_tabla_ventas_jv_x_juego_diario[$cont_array_detalle]["id_concepto"] = 3;
		$list_tabla_ventas_jv_x_juego_diario[$cont_array_detalle]["concepto"] = "Juevos Virtuales";
		$list_tabla_ventas_x_producto_diario[] = $list_tabla_ventas_jv_x_juego_diario[$cont_array_detalle];
		$list_tabla_ventas_jv_x_juego_diario[$cont_array_detalle]["id_concepto"] = 0;
		$list_tabla_ventas_jv_x_juego_diario[$cont_array_detalle]["concepto"] = "Acumulado";

		// Format numero
		foreach ($list_tabla_ventas_jv_x_juego_diario as $key => $value) {
			$list_tabla_ventas_jv_x_juego_diario[$key]["resultado"]                = number_format($value["resultado"], 2, ".", ",");
			$list_tabla_ventas_jv_x_juego_diario[$key]["total_tickets_apostado"]   = number_format($value["total_tickets_apostado"], 2, ".", ",");
			$list_tabla_ventas_jv_x_juego_diario[$key]["total_tickets_calculado"]  = number_format($value["total_tickets_calculado"], 2, ".", ",");
			$list_tabla_ventas_jv_x_juego_diario[$key]["total_tickets_pagado"]     = number_format($value["total_tickets_pagado"], 2, ".", ",");
			$list_tabla_ventas_jv_x_juego_diario[$key]["promedio"]                 = number_format($value["promedio"], 2, ".", ",");
			$list_tabla_ventas_jv_x_juego_diario[$key]["hold"]                     = number_format($value["hold"], 2, ".", ",");
			$list_tabla_ventas_jv_x_juego_diario[$key]["porcentaje_tickets_pagado"]= number_format($value["porcentaje_tickets_pagado"], 2, ".", ",");
		}
		
	}

	// Acumulado
	if (count($list_tabla_ventas_x_producto_diario) > 0) {

		$result_temp=array();
		$result_temp['id_concepto']=0;
		$result_temp['concepto']='Acumulado';
		$result_temp['num_tickets_apostado']=0;
		$result_temp['total_tickets_apostado']=0;
		$result_temp['promedio']=0;
		$result_temp['num_tickets_calculado']=0;
		$result_temp['total_tickets_calculado']=0;
		$result_temp['resultado']=0;
		$result_temp['hold']=0;
		$result_temp['num_tickets_pagado']=0;
		$result_temp['total_tickets_pagado']=0;
		$result_temp['porcentaje_tickets_pagado']=0;

		foreach ($list_tabla_ventas_x_producto_diario as $value) {			
			if ($value['id_concepto'] != "1" AND $value["id_concepto"] != 16) {
				$result_temp['num_tickets_apostado'] += $value["num_tickets_apostado"];
				$result_temp['total_tickets_apostado'] += $value["total_tickets_apostado"];

				$result_temp['num_tickets_calculado'] += $value["num_tickets_calculado"];
				$result_temp['total_tickets_calculado'] += $value["total_tickets_calculado"];

				$result_temp['num_tickets_pagado'] += $value["num_tickets_pagado"];
				$result_temp['total_tickets_pagado'] += $value["total_tickets_pagado"];
			}
		}
		$list_tabla_ventas_x_producto_diario[]=$result_temp;

		// Format numero
		foreach ($list_tabla_ventas_x_producto_diario as $key => $value) {
			if ($value['id_concepto'] == "1" or $value['id_concepto'] == "16"){
				$result_temp['resultado'] += $value['resultado'];
				$value["total_tickets_calculado"] = 0;
			} else if ($value['id_concepto'] != "0") {
				$value['resultado'] = $value["total_tickets_apostado"]-$value["total_tickets_calculado"];
				$result_temp['resultado'] += $value['resultado'];
			} else {
				$value['resultado'] += $result_temp['resultado'];
			}

			if ((float)$value['total_tickets_apostado']<>0 && (float)$value['num_tickets_apostado']<>0){
				$value['promedio'] = ((float)$value['total_tickets_apostado']/(float)$value['num_tickets_apostado']);
			}
			if ((float)$value['resultado']<>0 && (float)$value['total_tickets_apostado']<>0){
				$value['hold'] = (((float)$value['resultado']/(float)$value['total_tickets_apostado'])*100);
			}
			if ((float)$value['num_tickets_pagado']<>0 && (float)$value['num_tickets_calculado']<>0){
				$value['porcentaje_tickets_pagado'] = (((float)$value['num_tickets_pagado']/(float)$value['num_tickets_calculado'])*100);
			}

			$list_tabla_ventas_x_producto_diario[$key]["resultado"]                = number_format($value["resultado"], 2, ".", ",");
			$list_tabla_ventas_x_producto_diario[$key]["total_tickets_apostado"]   = number_format($value["total_tickets_apostado"], 2, ".", ",");
			$list_tabla_ventas_x_producto_diario[$key]["total_tickets_calculado"]  = number_format($value["total_tickets_calculado"], 2, ".", ",");
			$list_tabla_ventas_x_producto_diario[$key]["total_tickets_pagado"]     = number_format($value["total_tickets_pagado"], 2, ".", ",");
			$list_tabla_ventas_x_producto_diario[$key]["promedio"]                 = number_format($value["promedio"], 2, ".", ",");
			$list_tabla_ventas_x_producto_diario[$key]["hold"]                     = number_format($value["hold"], 2, ".", ",");
			$list_tabla_ventas_x_producto_diario[$key]["porcentaje_tickets_pagado"]= number_format($value["porcentaje_tickets_pagado"], 2, ".", ",");
		}
		
	}

	if (count($list_tabla_ventas_x_caja_diario) > 0) {

		$result_temp=array();
		$result_temp['id_concepto']=0;
		$result_temp['concepto']='Acumulado';
		$result_temp['num_tickets_apostado']=0;
		$result_temp['total_tickets_apostado']=0;
		$result_temp['promedio']=0;
		$result_temp['num_tickets_calculado']=0;
		$result_temp['total_tickets_calculado']=0;
		$result_temp['resultado']=0;
		$result_temp['hold']=0;
		$result_temp['num_tickets_pagado']=0;
		$result_temp['total_tickets_pagado']=0;
		$result_temp['porcentaje_tickets_pagado']=0;

		foreach ($list_tabla_ventas_x_caja_diario as $value) {
			$result_temp['num_tickets_apostado'] += $value["num_tickets_apostado"];
			$result_temp['total_tickets_apostado'] += $value["total_tickets_apostado"];

			$result_temp['num_tickets_calculado'] += $value["num_tickets_calculado"];
			$result_temp['total_tickets_calculado'] += $value["total_tickets_calculado"];

			$result_temp['num_tickets_pagado'] += $value["num_tickets_pagado"];
			$result_temp['total_tickets_pagado'] += $value["total_tickets_pagado"];
		}

		$result_temp['resultado'] = $result_temp["total_tickets_apostado"]-$result_temp["total_tickets_calculado"];
		if ((float)$result_temp['total_tickets_apostado']<>0 && (float)$result_temp['num_tickets_apostado']<>0){
			$result_temp['promedio'] = ((float)$result_temp['total_tickets_apostado']/(float)$result_temp['num_tickets_apostado']);
		}
		if ((float)$result_temp['resultado']<>0 && (float)$result_temp['total_tickets_apostado']<>0){
			$result_temp['hold'] = (((float)$result_temp['resultado']/(float)$result_temp['total_tickets_apostado'])*100);
		}
		if ((float)$result_temp['num_tickets_pagado']<>0 && (float)$result_temp['num_tickets_calculado']<>0){
			$result_temp['porcentaje_tickets_pagado'] = (((float)$result_temp['num_tickets_pagado']/(float)$result_temp['num_tickets_calculado'])*100);
		}
		$list_tabla_ventas_x_caja_diario[]=$result_temp;

		// Format numero
		foreach ($list_tabla_ventas_x_caja_diario as $key => $value) {
			$list_tabla_ventas_x_caja_diario[$key]["resultado"]                = number_format($value["resultado"], 2, ".", ",");
			$list_tabla_ventas_x_caja_diario[$key]["total_tickets_apostado"]   = number_format($value["total_tickets_apostado"], 2, ".", ",");
			$list_tabla_ventas_x_caja_diario[$key]["total_tickets_calculado"]  = number_format($value["total_tickets_calculado"], 2, ".", ",");
			$list_tabla_ventas_x_caja_diario[$key]["total_tickets_pagado"]     = number_format($value["total_tickets_pagado"], 2, ".", ",");
			$list_tabla_ventas_x_caja_diario[$key]["promedio"]                 = number_format($value["promedio"], 2, ".", ",");
			$list_tabla_ventas_x_caja_diario[$key]["hold"]                     = number_format($value["hold"], 2, ".", ",");
			$list_tabla_ventas_x_caja_diario[$key]["porcentaje_tickets_pagado"]= number_format($value["porcentaje_tickets_pagado"], 2, ".", ",");
		}
		
	}

	if (count($list_tabla_ventas_recargas_web_diario_dif) > 0) {

		$result_temp=array();
		$result_temp['id_concepto']=0;
		$result_temp['concepto']='Recargas Web';
		$result_temp['num_tickets_apostado']=0;
		$result_temp['total_tickets_apostado']=0;
		$result_temp['promedio']=0;

		foreach ($list_tabla_ventas_recargas_web_diario_dif as $value) {
			$result_temp['num_tickets_apostado'] += $value["num_tickets_apostado"];
			$result_temp['total_tickets_apostado'] += $value["total_tickets_apostado"];
		}
		if ((float)$result_temp['total_tickets_apostado']<>0 && (float)$result_temp['num_tickets_apostado']<>0){
			$result_temp['promedio'] = ((float)$result_temp['total_tickets_apostado']/(float)$result_temp['num_tickets_apostado']);
		}
		$list_tabla_ventas_recargas_web_diario[]=$result_temp;

		// Format numero
		foreach ($list_tabla_ventas_recargas_web_diario as $key => $value) {
			$list_tabla_ventas_recargas_web_diario[$key]["total_tickets_apostado"]   = number_format($value["total_tickets_apostado"], 2, ".", ",");
			$list_tabla_ventas_recargas_web_diario[$key]["promedio"]                 = number_format($value["promedio"], 2, ".", ",");
		}
		
	}

	if (count($list_tabla_ventas_otros_pagos_diario) > 0) {

		$result_temp=array();
		$result_temp['id_concepto']=0;
		$result_temp['concepto']='Acumulado';
		$result_temp['num_tickets_apostado']=0;
		$result_temp['total_tickets_apostado']=0;
		$result_temp['promedio']=0;

		foreach ($list_tabla_ventas_otros_pagos_diario as $value) {
			$result_temp['num_tickets_apostado'] += $value["num_tickets_apostado"];
			$result_temp['total_tickets_apostado'] += $value["total_tickets_apostado"];
		}

		if ((float)$result_temp['total_tickets_apostado']<>0 && (float)$result_temp['num_tickets_apostado']<>0){
			$result_temp['promedio'] = ((float)$result_temp['total_tickets_apostado']/(float)$result_temp['num_tickets_apostado']);
		}

		$list_tabla_ventas_otros_pagos_diario[]=$result_temp;

		// Format numero
		foreach ($list_tabla_ventas_otros_pagos_diario as $key => $value) {
			$list_tabla_ventas_otros_pagos_diario[$key]["total_tickets_apostado"]   = number_format($value["total_tickets_apostado"], 2, ".", ",");
			$list_tabla_ventas_otros_pagos_diario[$key]["promedio"]                 = number_format($value["promedio"], 2, ".", ",");
		}
		
	}



	//*******************************************************************************************************************
	//*******************************************************************************************************************
	// MENSUAL
	//*******************************************************************************************************************
	//*******************************************************************************************************************

	$query_tabla_ventas_x_producto_mensual = "	
		SELECT
			A.id_concepto,
			tc.concepto,
			A.num_tickets_apostado,
			A.total_tickets_apostado,
			0 promedio,
			A.num_tickets_calculado,
			A.total_tickets_calculado,
			0 resultado,
			0 hold,
			A.num_tickets_pagado,
			A.total_tickets_pagado,
			0 porcentaje_tickets_pagado
		FROM (
				SELECT
					tvp.id_reporte_teleservicios_concepto id_concepto,
					SUM(tvp.num_tickets_apostado) num_tickets_apostado,
					SUM(tvp.total_tickets_apostado) total_tickets_apostado,
					SUM(tvp.num_tickets_calculado) num_tickets_calculado,
					SUM(tvp.total_tickets_calculado) total_tickets_calculado,
					SUM(tvp.num_tickets_pagado) num_tickets_pagado,
					SUM(tvp.total_tickets_pagado) total_tickets_pagado
				FROM
					tbl_reporte_teleservicios_ventas_x_producto tvp
				WHERE
					tvp.fecha >= '{$dates->startmonth}' 
					AND tvp.fecha <= '$fecha' 
					AND tvp.id_estado =1
				GROUP BY tvp.id_reporte_teleservicios_concepto 
			) A
		JOIN tbl_reporte_teleservicios_concepto tc ON tc.id = A.id_concepto 
		ORDER BY tc.id ASC
		";
	/*
	echo '<br><br>';
	echo $query_tabla_ventas_x_producto_mensual;
	echo '<br><br>';
	*/
	$res_query_tabla_ventas_x_producto_mensual = $mysqli->query($query_tabla_ventas_x_producto_mensual);
	if ($mysqli->error) {
		$result["error_query_tabla_ventas_x_producto_mensual"] = $mysqli->error;
	}

	$list_tabla_ventas_x_producto_mensual = array();
	$list_tabla_ventas_transaccionales_mensual = array();
	$list_tabla_ventas_x_caja_mensual = array();
	$list_tabla_ventas_recargas_web_mensual = array();
	$list_tabla_ventas_recargas_web_mensual_dif = array();
	$list_tabla_ventas_terminales_mensual = array();
	$list_tabla_ventas_otros_pagos_mensual = array();
	$list_tabla_ventas_jv_x_juego_mensual = array();

    $calimaco_row = "";
	while ($li = $res_query_tabla_ventas_x_producto_mensual->fetch_assoc()) {
		if((int) $li["id_concepto"] >= 1 && (int) $li["id_concepto"] <= 2) {
			$list_tabla_ventas_x_producto_mensual[] = $li;
		}
        if((int) $li["id_concepto"] == 16) {//a d calimaco
			$calimaco_row = $li;
            //$list_tabla_ventas_x_producto_mensual[] = $li;
		}
        if((int) $li["id_concepto"] == 17 ){//horses
			$list_tabla_ventas_jv_x_juego_mensual[] = $li;
		}
		if((int) $li["id_concepto"] === 4) {
			$list_tabla_ventas_transaccionales_mensual[] = $li;
		}
		if((int) $li["id_concepto"] >= 5 && (int) $li["id_concepto"] <= 6) {
			$list_tabla_ventas_x_caja_mensual[] = $li;
		}
		if((int) $li["id_concepto"] >= 7 && (int) $li["id_concepto"] <= 8) {
			$list_tabla_ventas_recargas_web_mensual_dif[] = $li;
		}
		if((int) $li["id_concepto"] === 9) {
			$list_tabla_ventas_terminales_mensual[] = $li;
		}
		if((int) $li["id_concepto"] >= 10 && (int) $li["id_concepto"] <= 11) {
			$list_tabla_ventas_otros_pagos_mensual[] = $li;
		}
		if((int) $li["id_concepto"] >= 12 && (int) $li["id_concepto"] <= 15) {
			$list_tabla_ventas_jv_x_juego_mensual[] = $li;
		}
	}
    /*move calimaco before bingo*/
    $temp = array_slice($list_tabla_ventas_x_producto_mensual, 0, 1, true) +
    		array( 2 => $calimaco_row) +
    		array_slice($list_tabla_ventas_x_producto_mensual, 1 , 2, true) ;
    ;
	$list_tabla_ventas_x_producto_mensual = $temp;

	if (count($list_tabla_ventas_jv_x_juego_mensual) > 0) {

		$result_temp=array();
		$result_temp['id_concepto']=0;
		$result_temp['concepto']='Acumulado';
		$result_temp['num_tickets_apostado']=0;
		$result_temp['total_tickets_apostado']=0;
		$result_temp['promedio']=0;
		$result_temp['num_tickets_calculado']=0;
		$result_temp['total_tickets_calculado']=0;
		$result_temp['resultado']=0;
		$result_temp['hold']=0;
		$result_temp['num_tickets_pagado']=0;
		$result_temp['total_tickets_pagado']=0;
		$result_temp['porcentaje_tickets_pagado']=0;

		$cont_array_detalle = 0;
		foreach ($list_tabla_ventas_jv_x_juego_mensual as $value) {
			$cont_array_detalle++;
			$result_temp['num_tickets_apostado'] += $value["num_tickets_apostado"];
			$result_temp['total_tickets_apostado'] += $value["total_tickets_apostado"];

			$result_temp['num_tickets_calculado'] += $value["num_tickets_calculado"];
			$result_temp['total_tickets_calculado'] += $value["total_tickets_calculado"];

			$result_temp['num_tickets_pagado'] += $value["num_tickets_pagado"];
			$result_temp['total_tickets_pagado'] += $value["total_tickets_pagado"];
		}
		$list_tabla_ventas_jv_x_juego_mensual[]=$result_temp;

		$list_tabla_ventas_jv_x_juego_mensual[$cont_array_detalle]["id_concepto"] = 3;
		$list_tabla_ventas_jv_x_juego_mensual[$cont_array_detalle]["concepto"] = "Juevos Virtuales";
		$list_tabla_ventas_x_producto_mensual[] = $list_tabla_ventas_jv_x_juego_mensual[$cont_array_detalle];
		$list_tabla_ventas_jv_x_juego_mensual[$cont_array_detalle]["id_concepto"] = 0;
		$list_tabla_ventas_jv_x_juego_mensual[$cont_array_detalle]["concepto"] = "Acumulado";

		// Format numero
		foreach ($list_tabla_ventas_jv_x_juego_mensual as $key => $value) {

			$value['resultado'] = $value["total_tickets_apostado"]-$value["total_tickets_calculado"];
			if ((float)$value['total_tickets_apostado']<>0 && (float)$value['num_tickets_apostado']<>0){
				$value['promedio'] = ((float)$value['total_tickets_apostado']/(float)$value['num_tickets_apostado']);
			}
			if ((float)$value['resultado']<>0 && (float)$value['total_tickets_apostado']<>0){
				$value['hold'] = (((float)$value['resultado']/(float)$value['total_tickets_apostado'])*100);
			}
			if ((float)$value['num_tickets_pagado']<>0 && (float)$value['num_tickets_calculado']<>0){
				$value['porcentaje_tickets_pagado'] = (((float)$value['num_tickets_pagado']/(float)$value['num_tickets_calculado'])*100);
			}

			$list_tabla_ventas_jv_x_juego_mensual[$key]["resultado"]                = number_format($value["resultado"], 2, ".", ",");
			$list_tabla_ventas_jv_x_juego_mensual[$key]["total_tickets_apostado"]   = number_format($value["total_tickets_apostado"], 2, ".", ",");
			$list_tabla_ventas_jv_x_juego_mensual[$key]["total_tickets_calculado"]  = number_format($value["total_tickets_calculado"], 2, ".", ",");
			$list_tabla_ventas_jv_x_juego_mensual[$key]["total_tickets_pagado"]     = number_format($value["total_tickets_pagado"], 2, ".", ",");
			$list_tabla_ventas_jv_x_juego_mensual[$key]["promedio"]                 = number_format($value["promedio"], 2, ".", ",");
			$list_tabla_ventas_jv_x_juego_mensual[$key]["hold"]                     = number_format($value["hold"], 2, ".", ",");
			$list_tabla_ventas_jv_x_juego_mensual[$key]["porcentaje_tickets_pagado"]= number_format($value["porcentaje_tickets_pagado"], 2, ".", ",");
		}
		
		// Proyeccion
		$proyeccion = ($result_temp["total_tickets_apostado"] / $dates->onlyday) * $dates->onlylastday ;
		$result_temp = array();
		$result_temp['concepto'] = 'Proyectado';
		$result_temp['total_tickets_apostado'] = number_format($proyeccion, 2, ".", ",");
		$list_tabla_ventas_jv_x_juego_mensual[] = $result_temp;
		
	}
	// Acumulado
	if (count($list_tabla_ventas_x_producto_mensual) > 0) {

		$result_temp=array();
		$result_temp['id_concepto']=0;
		$result_temp['concepto']='Acumulado';
		$result_temp['num_tickets_apostado']=0;
		$result_temp['total_tickets_apostado']=0;
		$result_temp['promedio']=0;
		$result_temp['num_tickets_calculado']=0;
		$result_temp['total_tickets_calculado']=0;
		$result_temp['resultado']=0;
		$result_temp['hold']=0;
		$result_temp['num_tickets_pagado']=0;
		$result_temp['total_tickets_pagado']=0;
		$result_temp['porcentaje_tickets_pagado']=0;

		foreach ($list_tabla_ventas_x_producto_mensual as $value) {
			if ($value['id_concepto'] != "1" AND $value["id_concepto"] != 16) {
				$result_temp['num_tickets_apostado'] += $value["num_tickets_apostado"];
				$result_temp['total_tickets_apostado'] += $value["total_tickets_apostado"];

				$result_temp['num_tickets_calculado'] += $value["num_tickets_calculado"];
				$result_temp['total_tickets_calculado'] += $value["total_tickets_calculado"];

				$result_temp['num_tickets_pagado'] += $value["num_tickets_pagado"];
				$result_temp['total_tickets_pagado'] += $value["total_tickets_pagado"];
			}
		}
		$list_tabla_ventas_x_producto_mensual[]=$result_temp;

		// Format numero
		foreach ($list_tabla_ventas_x_producto_mensual as $key => $value) {
			if ($value['id_concepto'] == "1" or $value['id_concepto'] == "16"){
				$result_temp['resultado'] += $value['resultado'];
				$value["total_tickets_calculado"] = 0;
			} else if ($value['id_concepto'] != "0") {
				$value['resultado'] = $value["total_tickets_apostado"]-$value["total_tickets_calculado"];
				$result_temp['resultado'] += $value['resultado'];
			} else {
				$value['resultado'] += $result_temp['resultado'];
			}

			if ((float)$value['total_tickets_apostado']<>0 && (float)$value['num_tickets_apostado']<>0){
				$value['promedio'] = ((float)$value['total_tickets_apostado']/(float)$value['num_tickets_apostado']);
			}
			if ((float)$value['resultado']<>0 && (float)$value['total_tickets_apostado']<>0){
				$value['hold'] = (((float)$value['resultado']/(float)$value['total_tickets_apostado'])*100);
			}
			if ((float)$value['num_tickets_pagado']<>0 && (float)$value['num_tickets_calculado']<>0){
				$value['porcentaje_tickets_pagado'] = (((float)$value['num_tickets_pagado']/(float)$value['num_tickets_calculado'])*100);
			}

			$list_tabla_ventas_x_producto_mensual[$key]["resultado"]                = number_format($value["resultado"], 2, ".", ",");
			$list_tabla_ventas_x_producto_mensual[$key]["total_tickets_apostado"]   = number_format($value["total_tickets_apostado"], 2, ".", ",");
			$list_tabla_ventas_x_producto_mensual[$key]["total_tickets_calculado"]  = number_format($value["total_tickets_calculado"], 2, ".", ",");
			$list_tabla_ventas_x_producto_mensual[$key]["total_tickets_pagado"]     = number_format($value["total_tickets_pagado"], 2, ".", ",");
			$list_tabla_ventas_x_producto_mensual[$key]["promedio"]                 = number_format($value["promedio"], 2, ".", ",");
			$list_tabla_ventas_x_producto_mensual[$key]["hold"]                     = number_format($value["hold"], 2, ".", ",");
			$list_tabla_ventas_x_producto_mensual[$key]["porcentaje_tickets_pagado"]= number_format($value["porcentaje_tickets_pagado"], 2, ".", ",");
		}
		
	}

	if (count($list_tabla_ventas_x_caja_mensual) > 0) {

		$result_temp=array();
		$result_temp['id_concepto']=0;
		$result_temp['concepto']='Acumulado';
		$result_temp['num_tickets_apostado']=0;
		$result_temp['total_tickets_apostado']=0;
		$result_temp['promedio']=0;
		$result_temp['num_tickets_calculado']=0;
		$result_temp['total_tickets_calculado']=0;
		$result_temp['resultado']=0;
		$result_temp['hold']=0;
		$result_temp['num_tickets_pagado']=0;
		$result_temp['total_tickets_pagado']=0;
		$result_temp['porcentaje_tickets_pagado']=0;

		foreach ($list_tabla_ventas_x_caja_mensual as $value) {
			$result_temp['num_tickets_apostado'] += $value["num_tickets_apostado"];
			$result_temp['total_tickets_apostado'] += $value["total_tickets_apostado"];

			$result_temp['num_tickets_calculado'] += $value["num_tickets_calculado"];
			$result_temp['total_tickets_calculado'] += $value["total_tickets_calculado"];

			$result_temp['num_tickets_pagado'] += $value["num_tickets_pagado"];
			$result_temp['total_tickets_pagado'] += $value["total_tickets_pagado"];
		}
		$list_tabla_ventas_x_caja_mensual[]=$result_temp;

		// Format numero
		foreach ($list_tabla_ventas_x_caja_mensual as $key => $value) {

			$value['resultado'] = $value["total_tickets_apostado"]-$value["total_tickets_calculado"];
			if ((float)$value['total_tickets_apostado']<>0 && (float)$value['num_tickets_apostado']<>0){
				$value['promedio'] = ((float)$value['total_tickets_apostado']/(float)$value['num_tickets_apostado']);
			}
			if ((float)$value['resultado']<>0 && (float)$value['total_tickets_apostado']<>0){
				$value['hold'] = (((float)$value['resultado']/(float)$value['total_tickets_apostado'])*100);
			}
			if ((float)$value['num_tickets_pagado']<>0 && (float)$value['num_tickets_calculado']<>0){
				$value['porcentaje_tickets_pagado'] = (((float)$value['num_tickets_pagado']/(float)$value['num_tickets_calculado'])*100);
			}

			$list_tabla_ventas_x_caja_mensual[$key]["resultado"]                = number_format($value["resultado"], 2, ".", ",");
			$list_tabla_ventas_x_caja_mensual[$key]["total_tickets_apostado"]   = number_format($value["total_tickets_apostado"], 2, ".", ",");
			$list_tabla_ventas_x_caja_mensual[$key]["total_tickets_calculado"]  = number_format($value["total_tickets_calculado"], 2, ".", ",");
			$list_tabla_ventas_x_caja_mensual[$key]["total_tickets_pagado"]     = number_format($value["total_tickets_pagado"], 2, ".", ",");
			$list_tabla_ventas_x_caja_mensual[$key]["promedio"]                 = number_format($value["promedio"], 2, ".", ",");
			$list_tabla_ventas_x_caja_mensual[$key]["hold"]                     = number_format($value["hold"], 2, ".", ",");
			$list_tabla_ventas_x_caja_mensual[$key]["porcentaje_tickets_pagado"]= number_format($value["porcentaje_tickets_pagado"], 2, ".", ",");
		}

		// Proyeccion
		$proyeccion = ($result_temp["total_tickets_apostado"] / $dates->onlyday) * $dates->onlylastday ;
		$result_temp = array();
		$result_temp['concepto'] = 'Proyectado';
		$result_temp['total_tickets_apostado'] = number_format($proyeccion, 2, ".", ",");
		$list_tabla_ventas_x_caja_mensual[] = $result_temp;
		
	}

	if (count($list_tabla_ventas_recargas_web_mensual_dif) > 0) {

		$result_temp=array();
		$result_temp['id_concepto']=0;
		$result_temp['concepto']='Recargas Web';
		$result_temp['num_tickets_apostado']=0;
		$result_temp['total_tickets_apostado']=0;
		$result_temp['promedio']=0;

		foreach ($list_tabla_ventas_recargas_web_mensual_dif as $value) {
			$result_temp['num_tickets_apostado'] += $value["num_tickets_apostado"];
			$result_temp['total_tickets_apostado'] += $value["total_tickets_apostado"];
		}
		$list_tabla_ventas_recargas_web_mensual[]=$result_temp;

		// Proyeccion
		$proyeccion = ($list_tabla_ventas_recargas_web_mensual[0]["total_tickets_apostado"] / $dates->onlyday) * $dates->onlylastday ;

		// Format numero
		foreach ($list_tabla_ventas_recargas_web_mensual as $key => $value) {

			if ((float)$value['total_tickets_apostado']<>0 && (float)$value['num_tickets_apostado']<>0){
				$list_tabla_ventas_recargas_web_mensual[$key]['promedio'] = ((float)$value['total_tickets_apostado']/(float)$value['num_tickets_apostado']);
			}

			$list_tabla_ventas_recargas_web_mensual[$key]["total_tickets_apostado"] = number_format($value["total_tickets_apostado"], 2, ".", ",");
			$list_tabla_ventas_recargas_web_mensual[$key]["promedio"]               = number_format($list_tabla_ventas_recargas_web_mensual[$key]["promedio"], 2, ".", ",");
		}

		// Proyeccion
		$result_temp = array();
		$result_temp['concepto'] = 'Proyectado';
		$result_temp['total_tickets_apostado'] = number_format($proyeccion, 2, ".", ",");
		$list_tabla_ventas_recargas_web_mensual[] =$result_temp;
		
	}

	if (count($list_tabla_ventas_otros_pagos_mensual) > 0) {

		$result_temp=array();
		$result_temp['id_concepto']=0;
		$result_temp['concepto']='Acumulado';
		$result_temp['num_tickets_apostado']=0;
		$result_temp['total_tickets_apostado']=0;
		$result_temp['promedio']=0;

		foreach ($list_tabla_ventas_otros_pagos_mensual as $value) {
			$result_temp['num_tickets_apostado'] += $value["num_tickets_apostado"];
			$result_temp['total_tickets_apostado'] += $value["total_tickets_apostado"];
		}

		$list_tabla_ventas_otros_pagos_mensual[]=$result_temp;

		// Format numero
		foreach ($list_tabla_ventas_otros_pagos_mensual as $key => $value) {

			if ((float)$value['total_tickets_apostado']<>0 && (float)$value['num_tickets_apostado']<>0){
				$list_tabla_ventas_otros_pagos_mensual[$key]['promedio'] = ((float)$value['total_tickets_apostado']/(float)$value['num_tickets_apostado']);
			}

			$list_tabla_ventas_otros_pagos_mensual[$key]["total_tickets_apostado"]   = number_format($value["total_tickets_apostado"], 2, ".", ",");
			$list_tabla_ventas_otros_pagos_mensual[$key]["promedio"]                 = number_format($list_tabla_ventas_otros_pagos_mensual[$key]["promedio"], 2, ".", ",");
		}
		
	}



	//*******************************************************************************************************************
	//*******************************************************************************************************************
	// ANUAL
	//*******************************************************************************************************************
	//*******************************************************************************************************************

	$query_tabla_ventas_x_producto_anual = "	
		SELECT
			A.id_concepto,
			tc.concepto,
			A.num_tickets_apostado,
			A.total_tickets_apostado,
			0 promedio,
			A.num_tickets_calculado,
			A.total_tickets_calculado,
			0 resultado,
			0 hold,
			A.num_tickets_pagado,
			A.total_tickets_pagado,
			0 porcentaje_tickets_pagado
		FROM (
				SELECT
					tvp.id_reporte_teleservicios_concepto id_concepto,
					SUM(tvp.num_tickets_apostado) num_tickets_apostado,
					SUM(tvp.total_tickets_apostado) total_tickets_apostado,
					SUM(tvp.num_tickets_calculado) num_tickets_calculado,
					SUM(tvp.total_tickets_calculado) total_tickets_calculado,
					SUM(tvp.num_tickets_pagado) num_tickets_pagado,
					SUM(tvp.total_tickets_pagado) total_tickets_pagado
				FROM
					tbl_reporte_teleservicios_ventas_x_producto tvp
				WHERE
					tvp.fecha >= '{$dates->year}' 
					AND tvp.fecha <= '$fecha' 
					AND tvp.id_estado =1
				GROUP BY tvp.id_reporte_teleservicios_concepto 
			) A
		JOIN tbl_reporte_teleservicios_concepto tc ON tc.id = A.id_concepto 
		ORDER BY tc.id ASC
		";
	/*
	echo '<br><br>';
	echo $query_tabla_ventas_x_producto_anual;
	echo '<br><br>';
	*/
	$res_query_tabla_ventas_x_producto_anual = $mysqli->query($query_tabla_ventas_x_producto_anual);
	if ($mysqli->error) {
		$result["error_query_tabla_ventas_x_producto_anual"] = $mysqli->error;
	}

	$list_tabla_ventas_x_producto_anual = array();
	$list_tabla_ventas_transaccionales_anual = array();
	$list_tabla_ventas_x_caja_anual = array();
	$list_tabla_ventas_recargas_web_anual = array();
	$list_tabla_ventas_recargas_web_anual_dif = array();
	$list_tabla_ventas_terminales_anual = array();
	$list_tabla_ventas_otros_pagos_anual = array();
	$list_tabla_ventas_jv_x_juego_anual = array();

    $calimaco_row = "";
	while ($li = $res_query_tabla_ventas_x_producto_anual->fetch_assoc()) {
		if((int) $li["id_concepto"] >= 1 && (int) $li["id_concepto"] <= 2) {
			$list_tabla_ventas_x_producto_anual[] = $li;
		}
        if((int) $li["id_concepto"] == 16) {//a d calimaco
			$calimaco_row = $li;
            //$list_tabla_ventas_x_producto_anual[] = $li;
		}
        if((int) $li["id_concepto"] == 17 ){//horses
			$list_tabla_ventas_jv_x_juego_anual[] = $li;
		}
		if((int) $li["id_concepto"] === 4) {
			$list_tabla_ventas_transaccionales_anual[] = $li;
		}
		if((int) $li["id_concepto"] >= 5 && (int) $li["id_concepto"] <= 6) {
			$list_tabla_ventas_x_caja_anual[] = $li;
		}
		if((int) $li["id_concepto"] >= 7 && (int) $li["id_concepto"] <= 8) {
			$list_tabla_ventas_recargas_web_anual_dif[] = $li;
		}
		if((int) $li["id_concepto"] === 9) {
			$list_tabla_ventas_terminales_anual[] = $li;
		}
		if((int) $li["id_concepto"] >= 10 && (int) $li["id_concepto"] <= 11) {
			$list_tabla_ventas_otros_pagos_anual[] = $li;
		}
		if((int) $li["id_concepto"] >= 12 && (int) $li["id_concepto"] <= 15) {
			$list_tabla_ventas_jv_x_juego_anual[] = $li;
		}
	}
    /*move calimaco before bingo*/
    $temp = array_slice($list_tabla_ventas_x_producto_anual, 0, 1, true) +
    		array( 2 => $calimaco_row) +
    		array_slice($list_tabla_ventas_x_producto_anual, 1 , 2, true) ;
    ;
	$list_tabla_ventas_x_producto_anual = $temp;

	if (count($list_tabla_ventas_jv_x_juego_anual) > 0) {

		$result_temp=array();
		$result_temp['id_concepto']=0;
		$result_temp['concepto']='Acumulado';
		$result_temp['num_tickets_apostado']=0;
		$result_temp['total_tickets_apostado']=0;
		$result_temp['promedio']=0;
		$result_temp['num_tickets_calculado']=0;
		$result_temp['total_tickets_calculado']=0;
		$result_temp['resultado']=0;
		$result_temp['hold']=0;
		$result_temp['num_tickets_pagado']=0;
		$result_temp['total_tickets_pagado']=0;
		$result_temp['porcentaje_tickets_pagado']=0;

		$cont_array_detalle = 0;
		foreach ($list_tabla_ventas_jv_x_juego_anual as $value) {
			$cont_array_detalle++;
			$result_temp['num_tickets_apostado'] += $value["num_tickets_apostado"];
			$result_temp['total_tickets_apostado'] += $value["total_tickets_apostado"];

			$result_temp['num_tickets_calculado'] += $value["num_tickets_calculado"];
			$result_temp['total_tickets_calculado'] += $value["total_tickets_calculado"];

			$result_temp['num_tickets_pagado'] += $value["num_tickets_pagado"];
			$result_temp['total_tickets_pagado'] += $value["total_tickets_pagado"];
		}
		$list_tabla_ventas_jv_x_juego_anual[]=$result_temp;

		$list_tabla_ventas_jv_x_juego_anual[$cont_array_detalle]["id_concepto"] = 3;
		$list_tabla_ventas_jv_x_juego_anual[$cont_array_detalle]["concepto"] = "Juevos Virtuales";
		$list_tabla_ventas_x_producto_anual[] = $list_tabla_ventas_jv_x_juego_anual[$cont_array_detalle];
		$list_tabla_ventas_jv_x_juego_anual[$cont_array_detalle]["id_concepto"] = 0;
		$list_tabla_ventas_jv_x_juego_anual[$cont_array_detalle]["concepto"] = "Acumulado";

		// Format numero
		foreach ($list_tabla_ventas_jv_x_juego_anual as $key => $value) {

			$value['resultado'] = $value["total_tickets_apostado"]-$value["total_tickets_calculado"];
			if ((float)$value['total_tickets_apostado']<>0 && (float)$value['num_tickets_apostado']<>0){
				$value['promedio'] = ((float)$value['total_tickets_apostado']/(float)$value['num_tickets_apostado']);
			}
			if ((float)$value['resultado']<>0 && (float)$value['total_tickets_apostado']<>0){
				$value['hold'] = (((float)$value['resultado']/(float)$value['total_tickets_apostado'])*100);
			}
			if ((float)$value['num_tickets_pagado']<>0 && (float)$value['num_tickets_calculado']<>0){
				$value['porcentaje_tickets_pagado'] = (((float)$value['num_tickets_pagado']/(float)$value['num_tickets_calculado'])*100);
			}

			$list_tabla_ventas_jv_x_juego_anual[$key]["resultado"]                = number_format($value["resultado"], 2, ".", ",");
			$list_tabla_ventas_jv_x_juego_anual[$key]["total_tickets_apostado"]   = number_format($value["total_tickets_apostado"], 2, ".", ",");
			$list_tabla_ventas_jv_x_juego_anual[$key]["total_tickets_calculado"]  = number_format($value["total_tickets_calculado"], 2, ".", ",");
			$list_tabla_ventas_jv_x_juego_anual[$key]["total_tickets_pagado"]     = number_format($value["total_tickets_pagado"], 2, ".", ",");
			$list_tabla_ventas_jv_x_juego_anual[$key]["promedio"]                 = number_format($value["promedio"], 2, ".", ",");
			$list_tabla_ventas_jv_x_juego_anual[$key]["hold"]                     = number_format($value["hold"], 2, ".", ",");
			$list_tabla_ventas_jv_x_juego_anual[$key]["porcentaje_tickets_pagado"]= number_format($value["porcentaje_tickets_pagado"], 2, ".", ",");
		}
		
	}
	// Acumulado
	if (count($list_tabla_ventas_x_producto_anual) > 0) {

		$result_temp=array();
		$result_temp['id_concepto']=0;
		$result_temp['concepto']='Acumulado';
		$result_temp['num_tickets_apostado']=0;
		$result_temp['total_tickets_apostado']=0;
		$result_temp['promedio']=0;
		$result_temp['num_tickets_calculado']=0;
		$result_temp['total_tickets_calculado']=0;
		$result_temp['resultado']=0;
		$result_temp['hold']=0;
		$result_temp['num_tickets_pagado']=0;
		$result_temp['total_tickets_pagado']=0;
		$result_temp['porcentaje_tickets_pagado']=0;

		foreach ($list_tabla_ventas_x_producto_anual as $value) {
			if ($value['id_concepto'] != "1" AND $value["id_concepto"] != 16) {
				$result_temp['num_tickets_apostado'] += $value["num_tickets_apostado"];
				$result_temp['total_tickets_apostado'] += $value["total_tickets_apostado"];

				$result_temp['num_tickets_calculado'] += $value["num_tickets_calculado"];
				$result_temp['total_tickets_calculado'] += $value["total_tickets_calculado"];

				$result_temp['num_tickets_pagado'] += $value["num_tickets_pagado"];
				$result_temp['total_tickets_pagado'] += $value["total_tickets_pagado"];
			}
		}
		$list_tabla_ventas_x_producto_anual[]=$result_temp;

		// Format numero
		foreach ($list_tabla_ventas_x_producto_anual as $key => $value) {
			if ($value['id_concepto'] == "1" or $value['id_concepto'] == "16"){
				$result_temp['resultado'] += $value['resultado'];
				$value["total_tickets_calculado"] = 0;
			} else if ($value['id_concepto'] != "0") {
				$value['resultado'] = $value["total_tickets_apostado"]-$value["total_tickets_calculado"];
				$result_temp['resultado'] += $value['resultado'];
			} else {
				$value['resultado'] += $result_temp['resultado'];
			}

			if ((float)$value['total_tickets_apostado']<>0 && (float)$value['num_tickets_apostado']<>0){
				$value['promedio'] = ((float)$value['total_tickets_apostado']/(float)$value['num_tickets_apostado']);
			}
			if ((float)$value['resultado']<>0 && (float)$value['total_tickets_apostado']<>0){
				$value['hold'] = (((float)$value['resultado']/(float)$value['total_tickets_apostado'])*100);
			}
			if ((float)$value['num_tickets_pagado']<>0 && (float)$value['num_tickets_calculado']<>0){
				$value['porcentaje_tickets_pagado'] = (((float)$value['num_tickets_pagado']/(float)$value['num_tickets_calculado'])*100);
			}

			$list_tabla_ventas_x_producto_anual[$key]["resultado"]                = number_format($value["resultado"], 2, ".", ",");
			$list_tabla_ventas_x_producto_anual[$key]["total_tickets_apostado"]   = number_format($value["total_tickets_apostado"], 2, ".", ",");
			$list_tabla_ventas_x_producto_anual[$key]["total_tickets_calculado"]  = number_format($value["total_tickets_calculado"], 2, ".", ",");
			$list_tabla_ventas_x_producto_anual[$key]["total_tickets_pagado"]     = number_format($value["total_tickets_pagado"], 2, ".", ",");
			$list_tabla_ventas_x_producto_anual[$key]["promedio"]                 = number_format($value["promedio"], 2, ".", ",");
			$list_tabla_ventas_x_producto_anual[$key]["hold"]                     = number_format($value["hold"], 2, ".", ",");
			$list_tabla_ventas_x_producto_anual[$key]["porcentaje_tickets_pagado"]= number_format($value["porcentaje_tickets_pagado"], 2, ".", ",");
		}
		
	}

	if (count($list_tabla_ventas_x_caja_anual) > 0) {

		$result_temp=array();
		$result_temp['id_concepto']=0;
		$result_temp['concepto']='Acumulado';
		$result_temp['num_tickets_apostado']=0;
		$result_temp['total_tickets_apostado']=0;
		$result_temp['promedio']=0;
		$result_temp['num_tickets_calculado']=0;
		$result_temp['total_tickets_calculado']=0;
		$result_temp['resultado']=0;
		$result_temp['hold']=0;
		$result_temp['num_tickets_pagado']=0;
		$result_temp['total_tickets_pagado']=0;
		$result_temp['porcentaje_tickets_pagado']=0;

		foreach ($list_tabla_ventas_x_caja_anual as $value) {
			$result_temp['num_tickets_apostado'] += $value["num_tickets_apostado"];
			$result_temp['total_tickets_apostado'] += $value["total_tickets_apostado"];

			$result_temp['num_tickets_calculado'] += $value["num_tickets_calculado"];
			$result_temp['total_tickets_calculado'] += $value["total_tickets_calculado"];

			$result_temp['num_tickets_pagado'] += $value["num_tickets_pagado"];
			$result_temp['total_tickets_pagado'] += $value["total_tickets_pagado"];
		}
		$list_tabla_ventas_x_caja_anual[]=$result_temp;

		// Format numero
		foreach ($list_tabla_ventas_x_caja_anual as $key => $value) {

			$value['resultado'] = $value["total_tickets_apostado"]-$value["total_tickets_calculado"];
			if ((float)$value['total_tickets_apostado']<>0 && (float)$value['num_tickets_apostado']<>0){
				$value['promedio'] = ((float)$value['total_tickets_apostado']/(float)$value['num_tickets_apostado']);
			}
			if ((float)$value['resultado']<>0 && (float)$value['total_tickets_apostado']<>0){
				$value['hold'] = (((float)$value['resultado']/(float)$value['total_tickets_apostado'])*100);
			}
			if ((float)$value['num_tickets_pagado']<>0 && (float)$value['num_tickets_calculado']<>0){
				$value['porcentaje_tickets_pagado'] = (((float)$value['num_tickets_pagado']/(float)$value['num_tickets_calculado'])*100);
			}

			$list_tabla_ventas_x_caja_anual[$key]["resultado"]                = number_format($value["resultado"], 2, ".", ",");
			$list_tabla_ventas_x_caja_anual[$key]["total_tickets_apostado"]   = number_format($value["total_tickets_apostado"], 2, ".", ",");
			$list_tabla_ventas_x_caja_anual[$key]["total_tickets_calculado"]  = number_format($value["total_tickets_calculado"], 2, ".", ",");
			$list_tabla_ventas_x_caja_anual[$key]["total_tickets_pagado"]     = number_format($value["total_tickets_pagado"], 2, ".", ",");
			$list_tabla_ventas_x_caja_anual[$key]["promedio"]                 = number_format($value["promedio"], 2, ".", ",");
			$list_tabla_ventas_x_caja_anual[$key]["hold"]                     = number_format($value["hold"], 2, ".", ",");
			$list_tabla_ventas_x_caja_anual[$key]["porcentaje_tickets_pagado"]= number_format($value["porcentaje_tickets_pagado"], 2, ".", ",");
		}
		
	}

	if (count($list_tabla_ventas_recargas_web_anual_dif) > 0) {

		$result_temp=array();
		$result_temp['id_concepto']=0;
		$result_temp['concepto']='Recargas Web';
		$result_temp['num_tickets_apostado']=0;
		$result_temp['total_tickets_apostado']=0;
		$result_temp['promedio']=0;

		foreach ($list_tabla_ventas_recargas_web_anual_dif as $value) {
			$result_temp['num_tickets_apostado'] += $value["num_tickets_apostado"];
			$result_temp['total_tickets_apostado'] += $value["total_tickets_apostado"];
		}
		$list_tabla_ventas_recargas_web_anual[]=$result_temp;

		// Format numero
		foreach ($list_tabla_ventas_recargas_web_anual as $key => $value) {

			if ((float)$value['total_tickets_apostado']<>0 && (float)$value['num_tickets_apostado']<>0){
				$list_tabla_ventas_recargas_web_anual[$key]['promedio'] = ((float)$value['total_tickets_apostado']/(float)$value['num_tickets_apostado']);
			}

			$list_tabla_ventas_recargas_web_anual[$key]["total_tickets_apostado"]   = number_format($value["total_tickets_apostado"], 2, ".", ",");
			$list_tabla_ventas_recargas_web_anual[$key]["promedio"]                 = number_format($list_tabla_ventas_recargas_web_anual[$key]["promedio"], 2, ".", ",");
		}
		
	}

	if (count($list_tabla_ventas_otros_pagos_anual) > 0) {

		$result_temp=array();
		$result_temp['id_concepto']=0;
		$result_temp['concepto']='Acumulado';
		$result_temp['num_tickets_apostado']=0;
		$result_temp['total_tickets_apostado']=0;
		$result_temp['promedio']=0;

		foreach ($list_tabla_ventas_otros_pagos_anual as $value) {
			$result_temp['num_tickets_apostado'] += $value["num_tickets_apostado"];
			$result_temp['total_tickets_apostado'] += $value["total_tickets_apostado"];
		}

		$list_tabla_ventas_otros_pagos_anual[]=$result_temp;

		// Format numero
		foreach ($list_tabla_ventas_otros_pagos_anual as $key => $value) {

			if ((float)$value['total_tickets_apostado']<>0 && (float)$value['num_tickets_apostado']<>0){
				$list_tabla_ventas_otros_pagos_anual[$key]['promedio'] = ((float)$value['total_tickets_apostado']/(float)$value['num_tickets_apostado']);
			}

			$list_tabla_ventas_otros_pagos_anual[$key]["total_tickets_apostado"]   = number_format($value["total_tickets_apostado"], 2, ".", ",");
			$list_tabla_ventas_otros_pagos_anual[$key]["promedio"]                 = number_format($list_tabla_ventas_otros_pagos_anual[$key]["promedio"], 2, ".", ",");
		}
		
	}



	//*******************************************************************************************************************
	//*******************************************************************************************************************
	// RESULTADO
	//*******************************************************************************************************************
	//*******************************************************************************************************************
	if (count($list_tabla_ventas_x_producto_diario) == 0) {
		$result["http_code"] = 204;
		$result["status"] = "No hay transacciones.";
	} elseif (count($list_tabla_ventas_x_producto_diario) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result_tabla_ventas_x_producto_diario"] = $list_tabla_ventas_x_producto_diario;
		$result["result_tabla_ventas_transaccionales_diario"] = $list_tabla_ventas_transaccionales_diario;
		$result["result_tabla_ventas_x_caja_diario"] = $list_tabla_ventas_x_caja_diario;
		$result["result_tabla_ventas_recargas_web_diario"] = $list_tabla_ventas_recargas_web_diario;
		$result["result_tabla_ventas_terminales_diario"] = $list_tabla_ventas_terminales_diario;
		$result["result_tabla_ventas_otros_pagos_diario"] = $list_tabla_ventas_otros_pagos_diario;
		$result["result_tabla_ventas_jv_x_juego_diario"] = $list_tabla_ventas_jv_x_juego_diario;

		$result["result_tabla_ventas_x_producto_mensual"] = $list_tabla_ventas_x_producto_mensual;
		$result["result_tabla_ventas_transaccionales_mensual"] = $list_tabla_ventas_transaccionales_mensual;
		$result["result_tabla_ventas_x_caja_mensual"] = $list_tabla_ventas_x_caja_mensual;
		$result["result_tabla_ventas_recargas_web_mensual"] = $list_tabla_ventas_recargas_web_mensual;
		$result["result_tabla_ventas_terminales_mensual"] = $list_tabla_ventas_terminales_mensual;
		$result["result_tabla_ventas_otros_pagos_mensual"] = $list_tabla_ventas_otros_pagos_mensual;
		$result["result_tabla_ventas_jv_x_juego_mensual"] = $list_tabla_ventas_jv_x_juego_mensual;

		$result["result_tabla_ventas_x_producto_anual"] = $list_tabla_ventas_x_producto_anual;
		$result["result_tabla_ventas_transaccionales_anual"] = $list_tabla_ventas_transaccionales_anual;
		$result["result_tabla_ventas_x_caja_anual"] = $list_tabla_ventas_x_caja_anual;
		$result["result_tabla_ventas_recargas_web_anual"] = $list_tabla_ventas_recargas_web_anual;
		$result["result_tabla_ventas_terminales_anual"] = $list_tabla_ventas_terminales_anual;
		$result["result_tabla_ventas_otros_pagos_anual"] = $list_tabla_ventas_otros_pagos_anual;
		$result["result_tabla_ventas_jv_x_juego_anual"] = $list_tabla_ventas_jv_x_juego_anual;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar transacciones nuevas.";
	}

	return $result;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "listar_transacciones") {
	$result = listar_transacciones($_POST["fecha"]);
}


//*******************************************************************************************************************
//*******************************************************************************************************************
// GENERAR TRANSACCIONES
//*******************************************************************************************************************
//*******************************************************************************************************************
function generar_transacciones($fecha){

	global $login;
	global $mysqli;
    $usuario_id = $login ? $login['id'] : 0;

	$_GET["today"] = date('Y-m-d', strtotime('+1 days', strtotime($fecha)));

	$dates = (object) [
		"today"       => date('Y-m-d', strtotime($_GET["today"])),
		"yesterday"   => date('Y-m-d', strtotime('-1 days', strtotime($_GET["today"]))),
		"yearmonth"   => date('Y-m-01', strtotime('-1 days', strtotime($_GET["today"]))),
		"year"        => date('Y-01-01', strtotime('-1 days', strtotime($_GET["today"]))),
		"startmonth"  => date('Y-m-01', strtotime('-1 days', strtotime($_GET["today"]))),
		"startyear"   => date('Y-01-01', strtotime('-1 days', strtotime($_GET["today"]))),
		"onlyday"     => (int) date('d', strtotime('-1 days', strtotime($_GET["today"]))),
		"onlylastday" => (int) date("t", strtotime('-1 days', strtotime($_GET["today"]))),
	];

	if(isset($_POST["log"])){cron_print_log("Inicio");}

	//************************************************************************************************************
	// LOCALES
	$query = "
		SELECT 
		  lp.proveedor_id, 
		  lp.servicio_id,
		  lp.local_id,
		  l.cc_id
		FROM 
		  wwwapuestatotal_gestion.tbl_locales l 
		  LEFT JOIN wwwapuestatotal_gestion.tbl_local_proveedor_id lp ON (lp.local_id = l.id) 
		WHERE 
		  l.red_id = 8 
		  -- AND lp.estado = 1 
		  AND lp.proveedor_id > 0 
			";
	$temp = $mysqli->query($query);
	if ($mysqli->error) { $result["consulta_error_locales"] = $mysqli->error;}
	$rlocal_serv_prov = [];
	$rlocal_id = [];
	$rlocal_cc_id = [];
	while($t=$temp->fetch_assoc()){
		$rlocal_serv_prov[$t["servicio_id"]][]=$t["proveedor_id"];
		$rlocal_id[]=$t["local_id"];
		$rlocal_cc_id[]=$t["cc_id"];
	}
	$rlocal_id = array_unique($rlocal_id );
	$rlocal_cc_id = array_unique($rlocal_cc_id );
	if(isset($_POST["log"])){cron_print_log("consulta_error_locales");}



	//************************************************************************************************************
	$resultado         = [];

	//************************************************************************************************************
	// APUESTAS DEPORTIVAS
	$query = "
		SELECT
			1 as id_concepto,
			'Apuestas Deportivas' as descripcion,
			IFNULL(SUM(num_tickets), 0) AS tickets,
			IFNULL(SUM(total_apostado), 0) AS apostado,
			0 promedio,
			0 tickets_calculados,
			0 calculado,
			0 resultado,
			0 hold,
			0 tickets_ganados,
			IFNULL(SUM(pagado_en_otra_tienda)+(SUM(total_pagos_fisicos)-SUM(pagado_de_otra_tienda)), 0) AS ganado,
			0 porcentaje_pagados
		FROM wwwapuestatotal_gestion.tbl_transacciones_cabecera ca
		INNER JOIN tbl_locales l ON ca.local_id = l.id
		WHERE
		ca.fecha >= '{$dates->yesterday}' AND ca.fecha < '{$dates->today}'
		-- AND l.zona_id = 9
		AND l.red_id = 8
		AND ca.producto_id = 1
		AND ca.estado = 1
		-- AND l.estado = 1
		-- AND l.operativo = 1
		AND (l.fecha_fin_operacion IS NULL OR l.fecha_fin_operacion >= '{$dates->startmonth}')
		";
	$temp = $mysqli->query($query);
	if ($mysqli->error) {
		$result["ad_consulta"] = $query;
		$result["ad_error"] = $mysqli->error;
	} else {
		$resultado["dia"][] = $temp->fetch_assoc();
	}

	$query = "
			SELECT 
			  /*'Deportivas', */
			  count(b.col_Id) TicketsCalculados, 
			  IFNULL(sum(b.col_WinningAmount), 0) AS Calculado 
			from 
			  bc_apuestatotal.tbl_Bet as b 
			where 
			  b.col_state in (4,2) 
			  and b.col_CalcDate >= '{$dates->yesterday} 09:00:00' 
			  and b.col_CalcDate < '{$dates->today} 09:00:00' 
			  AND b.col_CashDeskId IN (".implode(',', $rlocal_serv_prov[1]).")
		";
	$temp = $mysqli->query($query);
	if ($mysqli->error) { 
		$result["ad_consulta_1"] = $query;
		$result["ad_error_1"] = $mysqli->error;
	} else {
		$temp_res_array = $temp->fetch_assoc();
		$resultado["dia"][0]["tickets_calculados"] = $temp_res_array["TicketsCalculados"];
		$resultado["dia"][0]["calculado"] = $temp_res_array["Calculado"];
	}

	$query = "
			SELECT 
			  /*'Deportivas', */
			  count(b.col_Id) TicketsPagados 
			from 
			  bc_apuestatotal.tbl_Bet as b 
			where 
			  b.col_state in (4,2) 
			  and b.col_PaidDate >= '{$dates->yesterday} 09:00:00' 
			  and b.col_PaidDate < '{$dates->today} 09:00:00' 
			  /* and l.red_id = 8*/
			  AND b.col_CashDeskId IN (".implode(',', $rlocal_serv_prov[1]).")
		";
	$temp = $mysqli->query($query);
	if ($mysqli->error) { 
		$result["ad_consulta_2"] = $query;
		$result["ad_error_2"] = $mysqli->error;
	} else {
		$resultado["dia"][0]["tickets_ganados"] = $temp->fetch_assoc()["TicketsPagados"];
	}

	if(isset($_POST["log"])){cron_print_log("consulta_error_ad");}


    //*****************************************************************************************************
    ///new query CUADRO 1 APUESTAS DEPORTIVAS CALIMICO
    $query = "SELECT
                16 AS id_concepto
                ,'Apuestas Deportivas Calimaco' as descripcion
                , SUM(TksApostados) AS tickets
                , SUM(Apostados) AS apostado 
                , SUM(Apostados)/ SUM(TksApostados) AS promedio
                , 0 tickets_calculados
                , 0 calculado
                , 0 resultado
                , 0 hold
                , SUM(TksPag) AS tickets_ganados
                , SUM(Pagos) AS ganado
			    ,0 porcentaje_pagados
                FROM (
                SELECT 	COUNT(id) TksApostados, SUM(monto) as Apostados, 0 AS TksPag, 0 AS Pagos-- , SUM(monto)/COUNT(id) as Promedio
                FROM 	wwwapuestatotal_gestion.tbl_televentas_clientes_transaccion
                WHERE 	tipo_id = 4   -- Apuesta generada
                AND	api_id = 5	  -- Altenar
                AND	estado = 1                
                AND created_at >= '{$dates->yesterday}' 
                AND created_at < '{$dates->today}'
                UNION ALL
                SELECT 	0 AS TksApostados, 0 AS Apostados, COUNT(id) TksPag, SUM(monto) as Pagos
                FROM 	wwwapuestatotal_gestion.tbl_televentas_clientes_transaccion
                WHERE 	tipo_id = 5   -- Apuesta Pagada
                AND	api_id = 5	  -- Altenar
                AND	estado = 1
                AND created_at >= '{$dates->yesterday}' 
                AND created_at < '{$dates->today}'
                ) TREG
		    ";
	$temp = $mysqli->query($query);
	if ($mysqli->error) {
		$result["ad_consulta"] = $query;
		$result["ad_error"] = $mysqli->error;
	} else {
		$resultado["dia"][] = $temp->fetch_assoc();
	}
	if(isset($_POST["log"])){cron_print_log("consulta_error_ad");}

	//************************************************************************************************************
	// Bingo
	$query = "
		SELECT
			2 as id_concepto,
			'Bingo' as descripcion,
			IFNULL(SUM(num_tickets), 0) AS tickets,
			IFNULL(SUM(total_apostado), 0) AS apostado,
			0 promedio,
			0 tickets_calculados,
			0 calculado,
			0 resultado,
			0 hold,
			0 tickets_ganados,
			-- IFNULL(SUM(pagado_en_otra_tienda)+(SUM(total_pagos_fisicos)-SUM(pagado_de_otra_tienda)), 0) AS ganado,
			0 ganado,
			0 porcentaje_pagados
		FROM wwwapuestatotal_gestion.tbl_transacciones_cabecera ca
		INNER JOIN wwwapuestatotal_gestion.tbl_locales l ON ca.local_id = l.id
		WHERE ca.fecha >= '{$dates->yesterday}' AND ca.fecha < '{$dates->today}'
		-- AND l.zona_id = 9
		AND l.red_id = 8
		AND ca.producto_id = 4
		AND ca.estado = 1
		-- AND l.estado = 1
		-- AND l.operativo = 1
		AND (l.fecha_fin_operacion IS NULL OR l.fecha_fin_operacion >= '{$dates->startmonth}')
		";
	$temp = $mysqli->query($query);
	if ($mysqli->error) { 
		$result["bingo_consulta"] = $query;
		$result["bingo_error"] = $mysqli->error;
	} else {
		$resultado["dia"][] = $temp->fetch_assoc();
	}

	$query = "
			SELECT 
			  /*'Bingo', */
			  count(bt.ticket_id) as TicketsCalculados, 
			  IFNULL(sum(bt.winning), 0) as Calculado 
			FROM 
			  tbl_repositorio_bingo_tickets bt 
			  LEFT JOIN tbl_repositorio_bingo_games bg ON (bg.game_id = bt.game_id) 
			WHERE 
			  bg.finished_at >= '{$dates->yesterday}' 
			  AND bg.finished_at < '{$dates->today}' 
			  AND bt.status IN ('Paid', 'Won', 'Expired') 
			  /* and l.red_id = 8*/
			  AND bt.sell_local_id IN (".implode(',', $rlocal_cc_id).")
		";
	$temp = $mysqli->query($query);
	if ($mysqli->error) {
		$result["bingo_consulta_1"] = $query;
		$result["bingo_error_1"] = $mysqli->error;
	} else {
		$temp_res_array = $temp->fetch_assoc();
		$resultado["dia"][1]["tickets_calculados"] = $temp_res_array["TicketsCalculados"];
		$resultado["dia"][1]["calculado"] = $temp_res_array["Calculado"];
	}

	$query = "
			SELECT 
			  /*'Bingo', */
			  IFNULL(sum(c.winning), 0) TotalTicketsPagados, count(c.ticket_id) TicketsPagados 
			FROM 
			  tbl_repositorio_bingo_tickets as c 
			  /* left join tbl_locales as l on c.sell_local_id = l.cc_id */
			where 
			  c.paid_at >= '{$dates->yesterday}' 
			  and c.paid_at < '{$dates->today}' 
			  and c.status = 'Paid' 
			  /* and l.red_id = 8*/
			  AND c.sell_local_id IN (".implode(',', $rlocal_cc_id).")
		";
	$temp = $mysqli->query($query);
	if ($mysqli->error) {
		$result["bingo_consulta_2"] = $query;
		$result["bingo_error_2"] = $mysqli->error;
	} else {
		$temp_res_array = $temp->fetch_assoc();
		$resultado["dia"][1]["ganado"] = $temp_res_array["TotalTicketsPagados"];
		$resultado["dia"][1]["tickets_ganados"] = $temp_res_array["TicketsPagados"];
	}

	if(isset($_POST["log"])){cron_print_log("consulta_error_bingo");}

	//************************************************************************************************************
	// S2W
	$query = "
		SELECT 
			3 as id_concepto,
			'Juegos Virtuales' as descripcion,
			IFNULL(SUM(num_tickets), 0) AS tickets,
			IFNULL(SUM(total_apostado), 0) AS apostado,
			0 promedio,
			0 tickets_calculados,
			0 calculado,
			0 resultado,
			0 hold,
			0 tickets_ganados,
			IFNULL(SUM(pagado_en_otra_tienda)+(SUM(total_pagos_fisicos)-SUM(pagado_de_otra_tienda)), 0) AS ganado,
			0 porcentaje_pagados
		FROM wwwapuestatotal_gestion.tbl_transacciones_cabecera ca
		INNER JOIN wwwapuestatotal_gestion.tbl_locales l ON ca.local_id = l.id
		WHERE ca.fecha >= '{$dates->yesterday}' AND ca.fecha < '{$dates->today}'
		-- AND l.zona_id = 9
		AND l.red_id = 8
		AND ca.producto_id = 2
		AND ca.estado = 1
		-- AND l.estado = 1
		-- AND l.operativo = 1
		AND (l.fecha_fin_operacion IS NULL OR l.fecha_fin_operacion >= '{$dates->startmonth}')
		";
	$temp = $mysqli->query($query);
	if ($mysqli->error) {
		$result["jv_consulta"] = $query;
		$result["jv_error"] = $mysqli->error;
	} else {
		$resultado["dia"][] = $temp->fetch_assoc();
	}

	$query = "
			SELECT 
			  /*'Virtuales', */
			  count(c.ticket_id) TicketsCalculados, 
			  IFNULL(sum(c.winning_amount), 0) AS Calculado 
			FROM 
			  tbl_repositorio_tickets_goldenrace as c 
			where 
			  c.time_played >= '{$dates->yesterday}' 
			  and c.time_played < '{$dates->today}' 
			  and c.ticket_status IN ('WON', 'PAIDOUT', 'EXPIRED', 'PAID OUT') 
			  and c.estado = 0 
			  AND c.local_id IN (".implode(',', $rlocal_id).")
		";
	$temp = $mysqli->query($query);
	if ($mysqli->error) {
		$result["jv_consulta_1"] = $query;
		$result["jv_error_1"] = $mysqli->error;
	} else {
		$temp_res_array = $temp->fetch_assoc();
		$resultado["dia"][2]["tickets_calculados"] = $temp_res_array["TicketsCalculados"];
		$resultado["dia"][2]["calculado"] = $temp_res_array["Calculado"];
	}
	
	$query = "
			SELECT 
			  /*'Virtuales', */
			  count(c.ticket_id) TicketsPagados 
			FROM 
			  tbl_repositorio_tickets_goldenrace as c 
			where 
			  c.paid_out_time >= '{$dates->yesterday}' 
			  and c.paid_out_time < '{$dates->today}' 
			  and c.ticket_status in ('Paid Out', 'PAIDOUT') 
			  /*and l.red_id = 8*/
			  AND c.local_id IN (".implode(',', $rlocal_id).")
		";
	$temp = $mysqli->query($query);
	if ($mysqli->error) {
		$result["jv_consulta_2"] = $query;
		$result["jv_error_2"] = $mysqli->error;
	} else {
		$resultado["dia"][2]["tickets_ganados"] = $temp->fetch_assoc()["TicketsPagados"];
	}

	if(isset($_POST["log"])){cron_print_log("consulta_error_s2w");}


	//************************************************************************************************************
	// Caja TV
	/*$query = "
		SELECT
			5 as id_concepto,
			'Cajas TV' as descripcion,
			IFNULL(SUM(num_tickets), 0) AS tickets,
			IFNULL(SUM(total_apostado), 0) AS apostado,
			0 promedio,
			0 tickets_calculados,
			0 calculado,
			0 resultado,
			0 hold,
			0 tickets_ganados,
			IFNULL(SUM(pagado_en_otra_tienda)+(SUM(total_pagos_fisicos)-SUM(pagado_de_otra_tienda)), 0) AS ganado,
			0 porcentaje_pagados
		FROM wwwapuestatotal_gestion.tbl_transacciones_cabecera ca
		INNER JOIN tbl_locales l ON ca.local_id = l.id
		WHERE ca.fecha >= '{$dates->yesterday}' AND ca.fecha < '{$dates->today}'
			-- AND l.zona_id = 9
			AND l.red_id = 8
			AND ca.estado = 1
			AND ca.producto_id = 1
			-- AND l.estado = 1
			-- AND l.operativo = 1
			AND (l.fecha_fin_operacion IS NULL OR l.fecha_fin_operacion >= '{$dates->startmonth}')
			AND l.id!=802
		";
	$temp = $mysqli->query($query);
	if ($mysqli->error) {
		$result["cajastv_consulta"] = $query;
		$result["cajastv_error"] = $mysqli->error;
	} else {
		$resultado["dia"][] = $temp->fetch_assoc();
	}

	$query = "
			SELECT 
			  //'Caja TV', 
			  count(b.col_Id) TicketsCalculados, 
			  IFNULL(sum(b.col_WinningAmount), 0) AS Calculado 
			from 
			  bc_apuestatotal.tbl_Bet as b 
			  left join bc_apuestatotal.tbl_CashDesk as c on b.col_CashDeskId = c.col_Id 
			  left join bc_apuestatotal.tbl_Betshop as bs on c.col_BetshopId = bs.col_Id 
			where 
			  b.col_state in (4,2) 
			  and b.col_CalcDate >= '{$dates->yesterday} 09:00:00' 
			  and b.col_CalcDate < '{$dates->today} 09:00:00' 
			  and bs.col_Id <> 109959 
			  // and l.red_id = 8 
			  AND b.col_CashDeskId IN (".implode(',', $rlocal_serv_prov[1]).")
		";
	$temp = $mysqli->query($query);
	if ($mysqli->error) {
		$result["cajastv_consulta_1"] = $query;
		$result["cajastv_error_1"] = $mysqli->error;
	} else {
		$temp_res_array = $temp->fetch_assoc();
		$resultado["dia"][3]["tickets_calculados"] = $temp_res_array["TicketsCalculados"];
		$resultado["dia"][3]["calculado"] = $temp_res_array["Calculado"];
	}

	$query = "
			SELECT 
			  //'Caja TV', 
			  count(b.col_Id) TicketsPagados 
			from 
			  bc_apuestatotal.tbl_Bet as b 
			  left join bc_apuestatotal.tbl_CashDesk as c on b.col_CashDeskId = c.col_Id 
			  left join bc_apuestatotal.tbl_Betshop as bs on c.col_BetshopId = bs.col_Id 
			where 
			  b.col_state in (4,2) 
			  and b.col_PaidDate >= '{$dates->yesterday} 09:00:00' 
			  and b.col_PaidDate < '{$dates->today} 09:00:00' 
			  and bs.col_Id <> 109959 
			  // and l.red_id = 8 
			  AND b.col_CashDeskId IN (".implode(',', $rlocal_serv_prov[1]).")
		";
	$temp = $mysqli->query($query);
	if ($mysqli->error) {
		$result["cajastv_consulta_2"] = $query;
		$result["cajastv_error_2"] = $mysqli->error;
	} else {
		$resultado["dia"][3]["tickets_ganados"] = $temp->fetch_assoc()["TicketsPagados"];
	}

	if(isset($_POST["log"])){cron_print_log("consulta_error_cajastv");}

	//************************************************************************************************************
	// Caja 7
	$query = "
		SELECT
			6 as id_concepto,
			'Caja 7' as descripcion,
			IFNULL(SUM(num_tickets), 0) AS tickets,
			IFNULL(SUM(total_apostado), 0) AS apostado,
			0 promedio,
			0 tickets_calculados,
			IFNULL(SUM(total_ganado), 0) AS calculado,
			0 resultado,
			0 hold,
			0 tickets_ganados,
			SUM(pagado_en_otra_tienda)+(SUM(total_pagos_fisicos)-SUM(pagado_de_otra_tienda)) AS ganado,
			0 porcentaje_pagados
		FROM wwwapuestatotal_gestion.tbl_transacciones_cabecera ca
		INNER JOIN tbl_locales l ON ca.local_id = l.id
		WHERE ca.fecha >= '{$dates->yesterday}' AND ca.fecha < '{$dates->today}'
			-- AND l.zona_id = 9
			AND l.red_id = 8
			AND ca.producto_id = 1
			AND ca.estado = 1
			-- AND l.estado = 1
			-- AND l.operativo = 1
			AND (l.fecha_fin_operacion IS NULL OR l.fecha_fin_operacion >= '{$dates->startmonth}')
			AND l.id=802
		";
	$temp = $mysqli->query($query);
	if ($mysqli->error) {
		$result["cajas7_consulta"] = $query;
		$result["cajas7_error"] = $mysqli->error;
	} else {
		$resultado["dia"][] = $temp->fetch_assoc();
	}

	$query = "
			SELECT 
			  //'Caja 7', 
			  count(b.col_Id) TicketsCalculados 
			from 
			  bc_apuestatotal.tbl_Bet as b 
			  left join bc_apuestatotal.tbl_CashDesk as c on b.col_CashDeskId = c.col_Id 
			  left join bc_apuestatotal.tbl_Betshop as bs on c.col_BetshopId = bs.col_Id 
			where 
			  b.col_state in (4,2) 
			  and b.col_CalcDate >= '{$dates->yesterday} 09:00:00' 
			  and b.col_CalcDate < '{$dates->today} 09:00:00' 
			  and bs.col_Id = 109959 
			  // and l.red_id = 8 
			  AND b.col_CashDeskId IN (".implode(',', $rlocal_serv_prov[1]).")
		";
	$temp = $mysqli->query($query);
	if ($mysqli->error) {
		$result["cajas7_consulta_1"] = $query;
		$result["cajas7_error_1"] = $mysqli->error;
	} else {
		$resultado["dia"][4]["tickets_calculados"] = $temp->fetch_assoc()["TicketsCalculados"];
	}

	$query = "
			SELECT 
			  'Caja 7', 
			  count(b.col_Id) TicketsPagados 
			from 
			  bc_apuestatotal.tbl_Bet as b 
			  left join bc_apuestatotal.tbl_CashDesk as c on b.col_CashDeskId = c.col_Id 
			  left join bc_apuestatotal.tbl_Betshop as bs on c.col_BetshopId = bs.col_Id 
			where 
			  b.col_state in (4,2) 
			  and b.col_PaidDate >= '{$dates->yesterday} 09:00:00' 
			  and b.col_PaidDate < '{$dates->today} 09:00:00' 
			  and bs.col_Id = 109959 
			   and l.red_id = 8 
			  AND b.col_CashDeskId IN (".implode(',', $rlocal_serv_prov[1]).")
		";
	$temp = $mysqli->query($query);
	if ($mysqli->error) {
		$result["cajas7_consulta_2"] = $query;
		$result["cajas7_error_2"] = $mysqli->error;
	} else {
		$resultado["dia"][4]["tickets_ganados"] = $temp->fetch_assoc()["TicketsPagados"];
	}
	
	if(isset($_POST["log"])){cron_print_log("consulta_error_cajas7");}*/


    /////cuadro 3  query new VENTAS AD POR CAJA

	//$resultado["dia"][] = $result_temp;
    $query = "SELECT   
      IF(descripcion = 'Cajas TV' , 5 , 6) AS id_concepto
    , descripcion
    , SUM(TREG.Tks_Apostado) AS tickets
    , SUM(TREG.Apostado) AS apostado
    , SUM(TREG.Apostado)/SUM(TREG.Tks_Apostado) AS promedio
    , SUM(TREG.Tks_Pagado) AS ganado
    , SUM(TREG.Pagado) AS Pagado
    , 0 AS promedio
    , 0 AS hold
    , 0 AS porcentaje_pagados
    , 0 AS  tickets_calculados
    , 0 AS  calculado
    , 0 AS  tickets_ganados
    FROM 	(
        SELECT 	CASE 
            WHEN CL.id IN (802,1522) THEN 1
            ELSE 2 END Id,
            CASE 
            WHEN CL.id IN (802,1522) THEN 'CAJA 7'
            ELSE 'CAJAS TV' END descripcion,
            COUNT(CT.id) AS Tks_Apostado, SUM(CT.monto) AS Apostado,
            0 AS Tks_Pagado, 0 AS Pagado
        FROM 	wwwapuestatotal_gestion.tbl_televentas_clientes_transaccion CT INNER JOIN  wwwapuestatotal_gestion.tbl_locales CL
        ON	CT.cc_id = CL.cc_id
        WHERE	CT.tipo_id = 4  
        AND	CT.estado = 1	
        AND	CT.created_at   >=   '{$dates->yesterday}'
        AND CT.created_at < '{$dates->today}'
        AND	CT.api_id IN (1, 5)		
        AND	CL.red_id = 8 		
        AND	UPPER(CL.nombre) NOT LIKE '%TEST%' 
        GROUP BY 	CL.id
        
        UNION ALL
        
        SELECT 	CASE 
            WHEN CL.id IN (802,1522) THEN 1
            ELSE 2 END Id,
            CASE 
            WHEN CL.id IN (802,1522) THEN 'CAJA 7'
            ELSE 'CAJAS TV' END descripcion,
            0 AS Tks_Apostado, 0 AS Apostado,
            COUNT(CT.id) AS Tks_Pagado , SUM(CT.monto) AS Pagado
        FROM 	wwwapuestatotal_gestion.tbl_televentas_clientes_transaccion CT INNER JOIN  wwwapuestatotal_gestion.tbl_locales CL
        ON	CT.cc_id = CL.cc_id
        WHERE	CT.tipo_id = 5  
        AND	CT.estado = 1	
        AND	CT.created_at  >= '{$dates->yesterday}'
        AND CT.created_at < '{$dates->today}'
        /*AND	CL.red_id = 8*/
        AND	UPPER(CL.nombre) NOT LIKE '%TEST%' 
        GROUP BY CL.id
    ) TREG
    GROUP BY Id, descripcion
        ";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["cajas_consulta"] = $query;
        $result["cajas_error"] = $mysqli->error;
    } else {
        if($temp->num_rows > 0 ){
            while($row = $temp->fetch_assoc())
            {
                $resultado["dia"][] = $row;
            }
        }
    }
    
    
	//************************************************************************************************************
	// JUEGOS VIRTUALES
	$result_temp=array();
	$result_temp['id_concepto']=12;
	$result_temp['descripcion']='Acumulado';
	$result_temp['tickets']=0;
	$result_temp['apostado']=0;
	$result_temp['promedio']=0;
	$result_temp['tickets_calculados']=0;
	$result_temp['calculado']=0;
	$result_temp['resultado']=0;
	$result_temp['hold']=0;
	$result_temp['tickets_ganados']=0;
	$result_temp['ganado']=0;
	$result_temp['porcentaje_pagados']=0;

	//************************************************************************************************************
	//************************************************************************************************************
	// S2W
	$result_temp['descripcion']='S2W';
	$resultado["dia"][] = $result_temp;
	/*
	SELECT 
	  count(c.ticket_id) 'Tks Apostado', 
	  sum(c.stake_amount) as Apostado, 
	  (
	    count(c.ticket_id)/ sum(c.stake_amount)
	  ) * 100 as Promedio 
	FROM 
	  tbl_repositorio_tickets_goldenrace as c 
	  left join tbl_locales as l on c.local_id = l.id 
	where 
	  c.time_played >= '2022-05-02' 
	  and c.time_played < '2022-05-03' 
	  -- and c.ticket_status not in ('CANCELLED','Expired','PENDING') 
	  and l.red_id = 8 
	  and c.estado = 0 
	  -- and c.game not in ('dog racing', 'dog')
	  and c.game not in ('dog racing','dog','Spin2Win Royale','World Cup')
	*/
	$query = "
			SELECT 
			  count(c.ticket_id) as num_tickets_apostado, 
			  sum(c.stake_amount) as total_apostado, 
			  (
			    count(c.ticket_id)/ sum(c.stake_amount)
			  ) * 100 as Promedio 
			FROM 
			  tbl_repositorio_tickets_goldenrace as c 
			  -- left join tbl_locales as l on c.local_id = l.id 
			where 
			  c.time_played >= '{$dates->yesterday}' 
			  and c.time_played < '{$dates->today}' 
			  -- and c.ticket_status not in ('CANCELLED') 
			  -- and c.ticket_status not in ('CANCELLED','Expired','PENDING') 
			  -- and l.red_id = 8 
			  and c.estado = 0 
			  -- and c.game <> 'dog'
			  -- and c.game not in ('dog racing', 'dog')
			  and c.game not in ('dog racing','dog','Spin2Win Royale','World Cup')
			  AND c.local_id IN (".implode(',', $rlocal_id).") 
		";
	$temp = $mysqli->query($query);
	if ($mysqli->error) {
		$result["s2w_consulta"] = $query;
		$result["s2w_error"] = $mysqli->error;
	} else {
		$temp_res_array = $temp->fetch_assoc();
		$resultado["dia"][5]["tickets"] = $temp_res_array["num_tickets_apostado"];
		$resultado["dia"][5]["apostado"] = $temp_res_array["total_apostado"];
	}
	/*
	SELECT 
	  -- 'Virtuales', 
	  count(c.ticket_id) 'Tks Calculados', 
	  sum(c.winning_amount) as calculado 
	FROM 
	  tbl_repositorio_tickets_goldenrace as c 
	  left join tbl_locales as l on c.local_id = l.id 
	where 
	  c.paid_out_time >= '2022-06-01' 
	  and c.paid_out_time < '2022-06-02' 
	  and c.ticket_status in ('WON', 'PAIDOUT', 'PAID OUT') 
	  and l.red_id = 8 
	  and c.estado = 0 
	  -- and c.game not in ('dog racing', 'dog')
	  and c.game not in ('dog racing','dog','Spin2Win Royale','World Cup')

	*/

	$query = "
			SELECT 
			  
			  /*'Virtuales', */
			  count(c.ticket_id) as num_tickets_calculado, 
			  sum(c.winning_amount) as total_tickets_calculado 
			FROM 
			  tbl_repositorio_tickets_goldenrace as c 
			  -- left join tbl_locales as l on c.local_id = l.id 
			where 
			  c.paid_out_time >= '{$dates->yesterday}' 
			  and c.paid_out_time < '{$dates->today}' 
			  -- and c.ticket_status in ('WON') 
			  and c.ticket_status in ('WON','PAIDOUT',  'PAID OUT' ) 
			  -- and l.red_id = 8 
			  and c.estado = 0 
			  -- and c.game <> 'dog racing'
			  -- and c.game not in ('dog racing', 'dog')
			  and c.game not in ('dog racing','dog','Spin2Win Royale','World Cup')
			  AND c.local_id IN (".implode(',', $rlocal_id).") 
		";
	$temp = $mysqli->query($query);
	if ($mysqli->error) {
		$result["s2w_consulta_1"] = $query;
		$result["s2w_error_1"] = $mysqli->error;
	} else {
		$temp_res_array = $temp->fetch_assoc();
		$resultado["dia"][5]["tickets_calculados"] = $temp_res_array["num_tickets_calculado"];
		$resultado["dia"][5]["calculado"] = $temp_res_array["total_tickets_calculado"];
	}
	/*
	SELECT 
	  -- 'Virtuales', 
	  count(c.ticket_id) 'Tks Pag.', 
	  sum(c.winning_amount) as pagos 
	FROM 
	  tbl_repositorio_tickets_goldenrace as c 
	  left join tbl_locales as l on c.local_id = l.id 
	where 
	  c.paid_out_time >= '2022-06-01' 
	  and c.paid_out_time < '2022-06-02' 
	  and c.ticket_status in ('PAIDOUT', 'PAID OUT') 
	  and l.red_id = 8 
	  and c.estado = 0 
	  -- and c.game not in ('dog racing', 'dog')
	  and c.game  not in ('dog racing','dog','Spin2Win Royale','World Cup')
	*/
	$query = "
			SELECT 
			  /*'Virtuales', */
			  count(c.ticket_id) num_tickets_pagado, 
			  sum(c.winning_amount) as pagado
			FROM 
			  tbl_repositorio_tickets_goldenrace as c 
			  -- left join tbl_locales as l on c.local_id = l.id 
			where 
			  c.paid_out_time >= '{$dates->yesterday}' 
			  and c.paid_out_time < '{$dates->today}' 
			  and c.ticket_status in ('PAIDOUT', 'PAID OUT') 
			  -- and l.red_id = 8 
			  and c.estado = 0 
			  -- and c.game <> 'dog'
			  -- and c.game not in ('dog racing', 'dog')
			  and c.game  not in ('dog racing','dog','Spin2Win Royale','World Cup')
			  AND c.local_id IN (".implode(',', $rlocal_id).") 
		";
	$temp = $mysqli->query($query);
	if ($mysqli->error) {
		$result["s2w_consulta_2"] = $query;
		$result["s2w_error_2"] = $mysqli->error;
	} else {
		$temp_res_array = $temp->fetch_assoc();
		$resultado["dia"][5]["tickets_ganados"] = $temp_res_array["num_tickets_pagado"];
		$resultado["dia"][5]["ganado"] = $temp_res_array["pagado"];
	}

	//************************************************************************************************************
	//************************************************************************************************************
	// DOG RACING
	$result_temp['descripcion']='Dog Racing';
	$result_temp['id_concepto']=13;
	$resultado["dia"][] = $result_temp;
	/*
	SELECT 
	  count(c.ticket_id) 'Tks Apostado', 
	  sum(c.stake_amount) as apostado, 
	  (
	    count(c.ticket_id)/ sum(c.stake_amount)
	  ) * 100 as promedio 
	FROM 
	  tbl_repositorio_tickets_goldenrace as c 
	  left join tbl_locales as l on c.local_id = l.id 
	where 
	  c.time_played >= '2022-05-02' 
	  and c.time_played < '2022-05-03' 
	  -- and c.ticket_status not in ('CANCELLED','Expired','PENDING') 
	  and l.red_id = 8 
	  and c.estado = 0 
	  and c.game in ('dog racing', 'dog')

	*/
	$query = "
			SELECT 
			  count(c.ticket_id) as num_tickets_apostado,
			  sum(c.stake_amount) as total_apostado, 
			  (
			    count(c.ticket_id)/ sum(c.stake_amount)
			  ) * 100 as promedio 
			FROM 
			  tbl_repositorio_tickets_goldenrace as c 
			  -- left join tbl_locales as l on c.local_id = l.id 
			where 
			  c.time_played >= '{$dates->yesterday}' 
			  and c.time_played < '{$dates->today}' 
			  -- and c.ticket_status not in ('CANCELLED','Expired','PENDING') 
			  -- and c.ticket_status not in ('CANCELLED') 
			  -- and l.red_id = 8 
			  and c.estado = 0 
			  -- and c.game = 'dog'
			  and c.game in ('dog racing', 'dog')
			  AND c.local_id IN (".implode(',', $rlocal_id).") 
		";
	$temp = $mysqli->query($query);
	if ($mysqli->error) {
		$result["dog_consulta"] = $query;
		$result["dog_error"] = $mysqli->error;
	} else {
		$temp_res_array = $temp->fetch_assoc();
		$resultado["dia"][6]["tickets"] = $temp_res_array["num_tickets_apostado"];
		$resultado["dia"][6]["apostado"] = $temp_res_array["total_apostado"];
	}

	/*
	SELECT 
	  -- 'Virtuales', 
	  count(c.ticket_id) tickets, 
	  sum(c.winning_amount) as calculado 
	FROM 
	  tbl_repositorio_tickets_goldenrace as c 
	  left join tbl_locales as l on c.local_id = l.id 
	where 
	  c.paid_out_time >= '2022-06-01' 
	  and c.paid_out_time < '2022-06-02' 
	  and c.ticket_status in ('WON', 'PAIDOUT', 'PAID OUT') 
	  and l.red_id = 8 
	  and c.estado = 0 
	  and c.game in ('dog racing', 'dog')

	*/
	$query = "
			SELECT 
			  /*'Virtuales', */
			  count(c.ticket_id) num_tickets_calculado, 
			  sum(c.winning_amount) as total_tickets_calculado 
			FROM 
			  tbl_repositorio_tickets_goldenrace as c 
			  -- left join tbl_locales as l on c.local_id = l.id 
			where 
			  c.paid_out_time >= '{$dates->yesterday}' 
			  and c.paid_out_time < '{$dates->today}' 
			  -- and c.ticket_status in ('WON') 
			  and c.ticket_status in ('WON','PAIDOUT',  'PAID OUT' ) 
			  -- and l.red_id = 8 
			  and c.estado = 0 
			  -- and c.game = 'dog racing' 
			  and c.game in ('dog racing', 'dog')
			  AND c.local_id IN (".implode(',', $rlocal_id).") 
		";
	$temp = $mysqli->query($query);
	if ($mysqli->error) {
		$result["dog_consulta_1"] = $query;
		$result["dog_error_1"] = $mysqli->error;
	} else {
		$temp_res_array = $temp->fetch_assoc();
		$resultado["dia"][6]["tickets_calculados"] = $temp_res_array["num_tickets_calculado"];
		$resultado["dia"][6]["calculado"] = $temp_res_array["total_tickets_calculado"];
	}
	/*
	SELECT 
	  -- 'Virtuales', 
	  count(c.ticket_id) 'Tks Pag.', 
	  sum(c.winning_amount) as pagos 
	FROM 
	  tbl_repositorio_tickets_goldenrace as c 
	  left join tbl_locales as l on c.local_id = l.id 
	where 
	  c.paid_out_time >= '2022-06-01' 
	  and c.paid_out_time < '2022-06-02' 
	  and c.ticket_status in ('PAIDOUT', 'PAID OUT') 
	  and l.red_id = 8 
	  and c.estado = 0 
	  and c.game in ('dog racing', 'dog')

	*/
	$query = "
			SELECT 
			  /*'Virtuales', */
			  count(c.ticket_id) num_tickets_pagado, 
			  sum(c.winning_amount) as pagado 
			FROM 
			  tbl_repositorio_tickets_goldenrace as c 
			  -- left join tbl_locales as l on c.local_id = l.id 
			where 
			  c.paid_out_time >= '{$dates->yesterday}' 
			  and c.paid_out_time < '{$dates->today}' 
			  and c.ticket_status in ('PAIDOUT', 'PAID OUT') 
			  -- and l.red_id = 8 
			  and c.estado = 0 
			  -- and c.game = 'dog' 
			  and c.game in ('dog racing', 'dog')
			  AND c.local_id IN (".implode(',', $rlocal_id).") 
		";
	$temp = $mysqli->query($query);
	if ($mysqli->error) {
		$result["dog_consulta_2"] = $query;
		$result["dog_error_2"] = $mysqli->error;
	} else {
		$temp_res_array = $temp->fetch_assoc();
		$resultado["dia"][6]["tickets_ganados"] = $temp_res_array["num_tickets_pagado"];
		$resultado["dia"][6]["ganado"] = $temp_res_array["pagado"];
	}
	//var_dump($resultado["dia"][6]);
	//************************************************************************************************************
	//************************************************************************************************************
	// SPIN2WIN ROYALE
	$result_temp['descripcion']='S2W Royale';
	$result_temp['id_concepto']=14;
	$resultado["dia"][] = $result_temp;
	/*
	SELECT 
	  count(c.ticket_id) 'Tks Apostado', 
	  sum(c.stake_amount) as apostado, 
	  (
	    count(c.ticket_id)/ sum(c.stake_amount)
	  ) * 100 as promedio 
	FROM 
	  tbl_repositorio_tickets_goldenrace as c 
	  left join tbl_locales as l on c.local_id = l.id 
	where 
	  c.time_played >= '2022-05-02' 
	  and c.time_played < '2022-05-03' #and c.ticket_status not in ('CANCELLED','Expired','PENDING') 
	  and l.red_id = 8 
	  and c.estado = 0 
	  and c.game in ('Spin2Win Royale')

	*/
	$query = "
			SELECT 
			  count(c.ticket_id) as num_tickets_apostado,
			  sum(c.stake_amount) as total_apostado, 
			  (
			    count(c.ticket_id)/ sum(c.stake_amount)
			  ) * 100 as promedio 
			FROM 
			  tbl_repositorio_tickets_goldenrace as c 
			  -- left join tbl_locales as l on c.local_id = l.id 
			where 
			  c.time_played >= '{$dates->yesterday}' 
			  and c.time_played < '{$dates->today}'  
			  -- and l.red_id = 8 
			  and c.estado = 0 
			  and c.game in ('Spin2Win Royale')
			  AND c.local_id IN (".implode(',', $rlocal_id).") 
		";
	$temp = $mysqli->query($query);
	if ($mysqli->error) {
		$result["s2wroyale_consulta"] = $query;
		$result["s2wroyale_error"] = $mysqli->error;
	} else {
		$temp_res_array = $temp->fetch_assoc();
		$resultado["dia"][7]["tickets"] = $temp_res_array["num_tickets_apostado"];
		$resultado["dia"][7]["apostado"] = $temp_res_array["total_apostado"];
	}

	/*
	SELECT 
	  count(c.ticket_id) tickets, 
	  sum(c.winning_amount) as calculado 
	FROM 
	  tbl_repositorio_tickets_goldenrace as c 
	  left join tbl_locales as l on c.local_id = l.id 
	where 
	  c.paid_out_time >= '2022-06-01' 
	  and c.paid_out_time < '2022-06-02' 
	  and c.ticket_status in ('WON', 'PAIDOUT', 'PAID OUT') 
	  and l.red_id = 8 
	  and c.estado = 0 
	  and c.game in ('Spin2Win Royale')


	*/
	$query = "
			SELECT 
			  /*'Virtuales', */
			  count(c.ticket_id) num_tickets_calculado, 
			  sum(c.winning_amount) as total_tickets_calculado 
			FROM 
			  tbl_repositorio_tickets_goldenrace as c 
			  -- left join tbl_locales as l on c.local_id = l.id 
			where 
			  c.paid_out_time >= '{$dates->yesterday}' 
			  and c.paid_out_time < '{$dates->today}' 
			  -- and c.ticket_status in ('WON') 
			  and c.ticket_status in ('WON','PAIDOUT',  'PAID OUT' ) 
			  -- and l.red_id = 8 
			  and c.estado = 0 
			  and c.game in ('Spin2Win Royale')
			  AND c.local_id IN (".implode(',', $rlocal_id).") 
		";
	$temp = $mysqli->query($query);
	if ($mysqli->error) {
		$result["s2wroyale_consulta_1"] = $query;
		$result["s2wroyale_error_1"] = $mysqli->error;
	} else {
		$temp_res_array = $temp->fetch_assoc();
		$resultado["dia"][7]["tickets_calculados"] = $temp_res_array["num_tickets_calculado"];
		$resultado["dia"][7]["calculado"] = $temp_res_array["total_tickets_calculado"];
	}
	/*
	SELECT 
	  count(c.ticket_id) 'Tks Pag.', 
	  sum(c.winning_amount) as pagos 
	FROM 
	  tbl_repositorio_tickets_goldenrace as c 
	  left join tbl_locales as l on c.local_id = l.id 
	where 
	  c.paid_out_time >= '2022-06-01' 
	  and c.paid_out_time < '2022-06-02' 
	  and c.ticket_status in ('PAIDOUT', 'PAID OUT') 
	  and l.red_id = 8 
	  and c.estado = 0 
	  and c.game in ('Spin2Win Royale')

	*/
	$query = "
			SELECT 
			  /*'Virtuales', */
			  count(c.ticket_id) num_tickets_pagado, 
			  sum(c.winning_amount) as pagado 
			FROM 
			  tbl_repositorio_tickets_goldenrace as c 
			  -- left join tbl_locales as l on c.local_id = l.id 
			where 
			  c.paid_out_time >= '{$dates->yesterday}' 
			  and c.paid_out_time < '{$dates->today}' 
			  and c.ticket_status in ('PAIDOUT', 'PAID OUT') 
			  -- and l.red_id = 8 
			  and c.estado = 0 
			  and c.game in ('Spin2Win Royale')
			  AND c.local_id IN (".implode(',', $rlocal_id).") 
		";
	$temp = $mysqli->query($query);
	if ($mysqli->error) {
		$result["s2wroyale_consulta_2"] = $query;
		$result["s2wroyale_error_2"] = $mysqli->error;
	} else {
		$temp_res_array = $temp->fetch_assoc();
		$resultado["dia"][7]["tickets_ganados"] = $temp_res_array["num_tickets_pagado"];
		$resultado["dia"][7]["ganado"] = $temp_res_array["pagado"];
	}

	//************************************************************************************************************
	//************************************************************************************************************
	// World Cup
	$result_temp['id_concepto']=15;
	$result_temp['descripcion']='World Cup';
	$resultado["dia"][] = $result_temp;
	/*
	SELECT 
	  count(c.ticket_id) 'Tks Apostado', 
	  sum(c.stake_amount) as Apostado, 
	  (
	    count(c.ticket_id)/ sum(c.stake_amount)
	  ) * 100 as Promedio 
	FROM 
	  tbl_repositorio_tickets_goldenrace as c 
	  left join tbl_locales as l on c.local_id = l.id 
	where 
	  c.time_played >= '2022-05-02' 
	  and c.time_played < '2022-05-03' #and c.ticket_status not in ('CANCELLED','Expired','PENDING') 
	  and l.red_id = 8 
	  and c.estado = 0 
	  and c.game not in ('World Cup')

	*/
	$query = "
			SELECT 
			  count(c.ticket_id) as num_tickets_apostado,
			  sum(c.stake_amount) as total_apostado, 
			  (
			    count(c.ticket_id)/ sum(c.stake_amount)
			  ) * 100 as promedio 
			FROM 
			  tbl_repositorio_tickets_goldenrace as c 
			  -- left join tbl_locales as l on c.local_id = l.id 
			where 
			  c.time_played >= '{$dates->yesterday}' 
			  and c.time_played < '{$dates->today}' 
			  -- and l.red_id = 8 
			  and c.estado = 0 
			  and c.game in ('World Cup')
			  AND c.local_id IN (".implode(',', $rlocal_id).") 
		";
	$temp = $mysqli->query($query);
	if ($mysqli->error) {
		$result["worldcup_consulta"] = $query;
		$result["worldcup_error"] = $mysqli->error;
	} else {
		$temp_res_array = $temp->fetch_assoc();
		$resultado["dia"][8]["tickets"] = $temp_res_array["num_tickets_apostado"];
		$resultado["dia"][8]["apostado"] = $temp_res_array["total_apostado"];
	}

	/*
	SELECT 
	  count(c.ticket_id) tickets, 
	  sum(c.winning_amount) as calculado 
	FROM 
	  tbl_repositorio_tickets_goldenrace as c 
	  left join tbl_locales as l on c.local_id = l.id 
	where 
	  c.paid_out_time >= '2022-06-01' 
	  and c.paid_out_time < '2022-06-02' 
	  and c.ticket_status in ('WON', 'PAIDOUT', 'PAID OUT') 
	  and l.red_id = 8 
	  and c.estado = 0 
	  and c.game in ('World Cup')

	*/
	$query = "
			SELECT 
			  /*'Virtuales', */
			  count(c.ticket_id) num_tickets_calculado, 
			  sum(c.winning_amount) as total_tickets_calculado 
			FROM 
			  tbl_repositorio_tickets_goldenrace as c 
			  -- left join tbl_locales as l on c.local_id = l.id 
			where 
			  c.paid_out_time >= '{$dates->yesterday}' 
			  and c.paid_out_time < '{$dates->today}' 
			  -- and c.ticket_status in ('WON') 
			  and c.ticket_status in ('WON','PAIDOUT',  'PAID OUT' ) 
			  -- and l.red_id = 8 
			  and c.estado = 0 
			  and c.game in ('World Cup')
			  AND c.local_id IN (".implode(',', $rlocal_id).") 
		";
	$temp = $mysqli->query($query);
	if ($mysqli->error) {
		$result["worldcup_consulta_1"] = $query;
		$result["worldcup_error_1"] = $mysqli->error;
	} else {
		$temp_res_array = $temp->fetch_assoc();
		$resultado["dia"][8]["tickets_calculados"] = $temp_res_array["num_tickets_calculado"];
		$resultado["dia"][8]["calculado"] = $temp_res_array["total_tickets_calculado"];
	}
	/*
	SELECT 
	  count(c.ticket_id) 'Tks Pag.', 
	  sum(c.winning_amount) as pagos 
	FROM 
	  tbl_repositorio_tickets_goldenrace as c 
	  left join tbl_locales as l on c.local_id = l.id 
	where 
	  c.paid_out_time >= '2022-06-01' 
	  and c.paid_out_time < '2022-06-02' 
	  and c.ticket_status in ('PAIDOUT', 'PAID OUT') 
	  and l.red_id = 8 
	  and c.estado = 0 
	  and c.game in ('World Cup')

	*/
	$query = "
			SELECT 
			  /*'Virtuales', */
			  count(c.ticket_id) num_tickets_pagado, 
			  sum(c.winning_amount) as pagado 
			FROM 
			  tbl_repositorio_tickets_goldenrace as c 
			  -- left join tbl_locales as l on c.local_id = l.id 
			where 
			  c.paid_out_time >= '{$dates->yesterday}' 
			  and c.paid_out_time < '{$dates->today}' 
			  and c.ticket_status in ('PAIDOUT', 'PAID OUT') 
			  -- and l.red_id = 8 
			  and c.estado = 0 
			  and c.game in ('World Cup')
			  AND c.local_id IN (".implode(',', $rlocal_id).") 
		";
	$temp = $mysqli->query($query);
	if ($mysqli->error) {
		$result["worldcup_consulta_2"] = $query;
		$result["worldcup_error_2"] = $mysqli->error;
	} else {
		$temp_res_array = $temp->fetch_assoc();
		$resultado["dia"][8]["tickets_ganados"] = $temp_res_array["num_tickets_pagado"];
		$resultado["dia"][8]["ganado"] = $temp_res_array["pagado"];
	}


    ////new query horses CUADRO 4 Ventas JV por Juego
    $result_temp['id_concepto'] = 17;
	$result_temp['descripcion'] = 'Horses';
	//$resultado["dia"][] = $result_temp;
    $query = "SELECT 
                17 as id_concepto,
                SUM(num_tickets_apostado) AS tickets_apostado, SUM(total_apostado) AS apostado,
                SUM(num_tickets_apostado)/SUM(total_apostado) AS Promedio, SUM(num_tickets_calculado) AS tickets_calculado,
                SUM(total_calculado) AS calculado, SUM(total_apostado) - SUM(total_calculado) AS Resultado,
                (SUM(total_apostado) - SUM(total_calculado)) / SUM(total_apostado) AS Hold, SUM(num_tickets_pagado) AS tickets_pagado,
                SUM(pagado) AS pagado, SUM(num_tickets_pagado)/SUM(num_tickets_calculado) AS Porcentaje_Tks_Pagados,
                0 AS promedio ,
                0 AS hold , 
                0 AS porcentaje_pagados,
                0 AS tickets,
                0 as tickets_ganados,
                0 AS ganado

            FROM  	(
                SELECT 
                    count(c.ticket_id) as num_tickets_apostado,
                    sum(c.stake_amount) as total_apostado, 
                    0 AS num_tickets_calculado,
                    0 AS total_calculado,
                    0 AS num_tickets_pagado,
                    0 AS pagado
                FROM  	wwwapuestatotal_gestion.tbl_repositorio_tickets_goldenrace as c 
                WHERE    c.time_played >= '{$dates->yesterday}' 
			    AND     c.time_played < '{$dates->today}'                 
                AND 	c.estado = 0 
                AND 	c.game in ('HORSE', 'Horses')
                AND 	c.local_id IN (     SELECT 	 lp.local_id
                        FROM 	wwwapuestatotal_gestion.tbl_locales l LEFT JOIN wwwapuestatotal_gestion.tbl_local_proveedor_id lp 
                        ON 	(lp.local_id = l.id) 
                        WHERE 	l.red_id = 8 
                        AND 	lp.proveedor_id > 0 
                        GROUP BY 	lp.local_id)
                
                UNION ALL
                
                SELECT 	0 AS num_tickets_apostado,
                    0 AS total_apostado,
                    count(c.ticket_id) num_tickets_calculado, 
                    sum(c.winning_amount) as total_calculado,
                    0 AS num_tickets_pagado,
                    0 AS pagado
                FROM 	wwwapuestatotal_gestion.tbl_repositorio_tickets_goldenrace as c 
                WHERE 	c.paid_out_time >= '{$dates->yesterday}'
                AND 	c.paid_out_time < '{$dates->today}' 
                AND 	c.ticket_status in ('WON','PAIDOUT',  'PAID OUT' ) 
                AND 	c.estado = 0 
                AND 	c.game in ('HORSE', 'Horses')
                AND 	c.local_id IN (	SELECT 	lp.local_id
                        FROM 	wwwapuestatotal_gestion.tbl_locales l LEFT JOIN wwwapuestatotal_gestion.tbl_local_proveedor_id lp 
                        ON 	(lp.local_id = l.id) 
                        WHERE 	l.red_id = 8 
                        AND 	lp.proveedor_id > 0 
                        GROUP BY 	lp.local_id)
                
                UNION ALL
                
                SELECT 	0 AS num_tickets_apostado,
                    0 AS total_apostado,
                    0 AS num_tickets_calculado,
                    0 AS total_calculado,
                    count(c.ticket_id) num_tickets_pagado, 
                    sum(c.winning_amount) as pagado 
                FROM 	wwwapuestatotal_gestion.tbl_repositorio_tickets_goldenrace as c
                where 	c.paid_out_time >= '{$dates->yesterday}'			/* Parametro a cambiar*/
                and 	c.paid_out_time <'{$dates->today}'			/* Parametro a cambiar*/
                and 	c.ticket_status in ('PAIDOUT', 'PAID OUT') 
                and 	c.estado = 0 
                and 	c.game in ('HORSE', 'Horses')
                AND 	c.local_id IN (	SELECT 	lp.local_id
                FROM 	wwwapuestatotal_gestion.tbl_locales l LEFT JOIN wwwapuestatotal_gestion.tbl_local_proveedor_id lp 
                ON 	(lp.local_id = l.id) 
                WHERE 	l.red_id = 8 
                AND 	lp.proveedor_id > 0 
                GROUP BY 	lp.local_id)
            ) TREG
            ;
		";
	$temp = $mysqli->query($query);

	if ($mysqli->error) {
		$result["horses_consulta"] = $query;
		$result["horses_error"] = $mysqli->error;
	} else {
		$temp_res_array = $temp->fetch_assoc();
		$resultado["dia"][] = $temp_res_array;
	}



	//echo var_dump($resultado);


	//************************************************************************************************************
    $query_update = "
		UPDATE tbl_reporte_teleservicios_ventas_x_producto 
		SET 
			id_estado = 0,
			id_user_updated = '$usuario_id',
			updated_at = now()
		WHERE fecha = '$fecha'
			AND id_estado = 1
    	";
    $mysqli->query($query_update);

	if(isset($_POST["log"])){cron_print_log("query_update");}

	//************************************************************************************************************
	foreach ($resultado as $key => $value) {
		for ($i = 0; $i < count($value); $i++) {

			$resultado[$key][$i]["resultado"] = $resultado[$key][$i]["apostado"]-$resultado[$key][$i]["calculado"];

			//CAST(A.apostado/A.tickets AS DECIMAL(10,2)) promedio
			if ((float)$resultado[$key][$i]["apostado"]<>0 && (float)$resultado[$key][$i]["tickets"]<>0){
				$resultado[$key][$i]["promedio"] = ((float)$resultado[$key][$i]["apostado"]/(float)$resultado[$key][$i]["tickets"]);
			}
			//CAST((A.apostado - A.ganado) * 100 / A.apostado AS DECIMAL(10,2)) AS hold
			if ((float)$resultado[$key][$i]["resultado"]<>0 && (float)$resultado[$key][$i]["apostado"]<>0){
				$resultado[$key][$i]["hold"] = (((float)$resultado[$key][$i]["resultado"]/(float)$resultado[$key][$i]["apostado"])*100);
			}
			//CAST(A.tickets_ganados/A.tickets_calculados AS DECIMAL(10,2)) porcentaje_pagados
			if ((float)$resultado[$key][$i]["tickets_ganados"]<>0 && (float)$resultado[$key][$i]["tickets_calculados"]<>0){
				$resultado[$key][$i]["porcentaje_pagados"] = (((float)$resultado[$key][$i]["tickets_ganados"]/(float)$resultado[$key][$i]["tickets_calculados"])*100);
			}
			
			$resultado[$key][$i]["tickets"]            = number_format($resultado[$key][$i]["tickets"], 0, ".", "");
			$resultado[$key][$i]["apostado"]           = number_format($resultado[$key][$i]["apostado"], 2, ".", "");
			$resultado[$key][$i]["promedio"]           = number_format($resultado[$key][$i]["promedio"], 2, ".", "");
			$resultado[$key][$i]["tickets_calculados"] = number_format($resultado[$key][$i]["tickets_calculados"], 0, ".", "");
			$resultado[$key][$i]["calculado"]          = number_format($resultado[$key][$i]["calculado"], 2, ".", "");
			$resultado[$key][$i]["resultado"]          = number_format($resultado[$key][$i]["resultado"], 2, ".", "");
			$resultado[$key][$i]["hold"]               = number_format($resultado[$key][$i]["hold"], 2, ".", "");
			$resultado[$key][$i]["tickets_ganados"]    = number_format($resultado[$key][$i]["tickets_ganados"], 0, ".", "");
			$resultado[$key][$i]["ganado"]             = number_format($resultado[$key][$i]["ganado"], 2, ".", "");
			$resultado[$key][$i]["porcentaje_pagados"] = number_format($resultado[$key][$i]["porcentaje_pagados"], 2, ".", "");

            $query_insert = "
                INSERT INTO tbl_reporte_teleservicios_ventas_x_producto ( 
                	id_reporte_teleservicios_concepto,
                	fecha,
					num_tickets_apostado,
					total_tickets_apostado,
					promedio,
					num_tickets_calculado,
					total_tickets_calculado,
					resultado,
					hold,
					num_tickets_pagado,
					total_tickets_pagado,
					porcentaje_tickets_pagado,
					id_estado,
                	id_user_created,
                	created_at
                ) VALUES ( 
                    '". $resultado[$key][$i]["id_concepto"] ."', 
                    '". $fecha ."', 
                    '". $resultado[$key][$i]["tickets"] ."', 
                    '". $resultado[$key][$i]["apostado"] ."', 
                    '". $resultado[$key][$i]["promedio"] ."', 
                    '". $resultado[$key][$i]["tickets_calculados"] ."', 
                    '". $resultado[$key][$i]["calculado"] ."', 
                    '". $resultado[$key][$i]["resultado"] ."', 
                    '". $resultado[$key][$i]["hold"] ."', 
                    '". $resultado[$key][$i]["tickets_ganados"] ."', 
                    '". $resultado[$key][$i]["ganado"] ."', 
                    '". $resultado[$key][$i]["porcentaje_pagados"] ."', 
                    '1', 
                    '". $usuario_id ."', 
                    now()
                );
            ";
            //echo "<pre>".$key;print_r($query_insert);echo "</pre>";

            $mysqli->query($query_insert);
			if ($mysqli->error) {
				$result["insert_query_1"] = $query;
				$result["insert_error_1"] = $mysqli->error;
			}
		}
	}
	if(isset($_POST["log"])){cron_print_log("foreach");}

	//************************************************************************************************************
	//************************************************************************************************************
	//************************************************************************************************************
	//************************************************************************************************************

	//************************************************************************************************************
	// TELESERVICIOS - Ventas Transaccionales (Torito)
	$result_torito  = [];

	$result_temp=array();
	$result_temp['id_concepto']=0;
	$result_temp['descripcion']='';
	$result_temp['tickets']=0;
	$result_temp['apostado']=0;
	$result_temp['promedio']=0;
	$result_temp['tickets_ganados']=0;
	$result_temp['ganado']=0;

	$query = "
		  SELECT
			4 as id_concepto,
			'Torito' as descripcion,
			SUM(num_tickets) AS tickets,
			SUM(total_apostado) AS apostado,
			CAST(SUM(total_apostado)/SUM(num_tickets) AS DECIMAL(10,2)) AS promedio,
			SUM(num_tickets_ganados) AS tickets_ganados,
			SUM(pagado_en_otra_tienda)+(SUM(total_pagos_fisicos)-SUM(pagado_de_otra_tienda)) AS ganado
		FROM wwwapuestatotal_gestion.tbl_transacciones_cabecera ca
		INNER JOIN wwwapuestatotal_gestion.tbl_locales l ON ca.local_id = l.id
		WHERE
			ca.fecha >= '{$dates->yesterday}' AND ca.fecha < '{$dates->today}'
			-- AND l.zona_id = 9
			AND l.red_id = 8
			AND ca.producto_id = 9
			AND ca.estado = 1
			-- AND l.estado = 1
			-- AND l.operativo = 1
			AND (l.fecha_fin_operacion IS NULL OR l.fecha_fin_operacion >= '{$dates->startmonth}')
		";
	$temp = $mysqli->query($query);
	if ($mysqli->error) {
		$result["torito_consulta"] = $query;
		$result["torito_error"] = $mysqli->error;
	} else {
		$temp_torito = $temp->fetch_assoc();
		if(count($temp_torito)>0){
			$result_torito["dia"][] = $temp_torito;
		} else {
			$result_temp['id_concepto'] = 4;
			$result_torito["dia"][] = $result_temp;
		}
	}

	if(isset($_POST["log"])){cron_print_log("consulta_error_torito");}

	//************************************************************************************************************
	foreach ($result_torito as $key => $value) {
		for ($i = 0; $i < count($value); $i++) {
			$result_torito[$key][$i]["tickets"]    = number_format($result_torito[$key][$i]["tickets"], 0, ".", "");
			$result_torito[$key][$i]["apostado"]   = number_format($result_torito[$key][$i]["apostado"], 2, ".", "");
			$result_torito[$key][$i]["promedio"]   = number_format($result_torito[$key][$i]["promedio"], 2, ".", "");

			$result_torito[$key][$i]["tickets_ganados"] = number_format($result_torito[$key][$i]["tickets_ganados"], 0, ".", "");
			$result_torito[$key][$i]["ganado"]          = number_format($result_torito[$key][$i]["ganado"], 2, ".", "");

			$query_insert = " 
				INSERT INTO tbl_reporte_teleservicios_ventas_x_producto ( 
                	id_reporte_teleservicios_concepto,
                	fecha,
					num_tickets_apostado,
					total_tickets_apostado,
					promedio,
					num_tickets_calculado,
					total_tickets_calculado,
					resultado,
					hold,
					num_tickets_pagado,
					total_tickets_pagado,
					porcentaje_tickets_pagado,
					id_estado,
                	id_user_created,
                	created_at
                ) VALUES ( 
                    '". $result_torito[$key][$i]["id_concepto"] ."', 
                    '". $fecha ."', 
                    '". $result_torito[$key][$i]["tickets"] ."', 
                    '". $result_torito[$key][$i]["apostado"] ."', 
                    '". $result_torito[$key][$i]["promedio"] ."', 
                    0,
                    0,
                    0,
                    0,
                    '". $result_torito[$key][$i]["tickets_ganados"] ."', 
                    '". $result_torito[$key][$i]["ganado"] ."', 
                    0,
                    '1', 
                    '". $usuario_id ."', 
                    now()
                );
            ";
            $mysqli->query($query_insert);
			if ($mysqli->error) {
				$result["torito_insert_query"] = $query;
				$result["torito_insert_error"] = $mysqli->error;
			}
		}
	}
	if(isset($_POST["log"])){cron_print_log("foreach");}



	//************************************************************************************************************
	//************************************************************************************************************
	//************************************************************************************************************
	//************************************************************************************************************

	$otrosingresos = [];

	$result_temp=array();
	$result_temp['id_concepto']=0;
	$result_temp['descripcion']='';
	$result_temp['cantidad']=0;
	$result_temp['monto']=0;
	$result_temp['promedio']=0;

	//************************************************************************************************************
	//Recargas Web Gestion diario
	$query = "
		SELECT
			7 as id_concepto,
			'Recargas Web Gestion' as descripcion,
			COUNT(d.col_id) AS cantidad,
			SUM(d.col_Amount) AS monto,
			CAST(SUM(d.col_Amount) / COUNT(d.col_id)AS DECIMAL(10,2)) AS promedio
		from bc_apuestatotal.at_ClientDeposits as d
		LEFT JOIN bc_apuestatotal.tbl_DocumentType as dt  ON d.col_TypeId=dt.col_Id
		LEFT JOIN bc_apuestatotal.tbl_TranslationEntry AS T ON dt.col_NameId=T.col_TranslationId AND T.col_LanguageId='en'
			where d.col_TypeId in (3) AND d.col_PaymentSystemId=1630
			AND d.col_Created >= '{$dates->yesterday} 09:00:00'
			AND d.col_Created <= '{$dates->today} 09:00:00'
		GROUP BY d.col_TypeId,dt.col_Id,T.col_TranslationId
		";
	$temp = $mysqli->query($query);
	if ($mysqli->error) {
		$result["recwebges_consulta"] = $query;
		$result["recwebges_error"] = $mysqli->error;
	} else {
		$temp_recargaswebgestion = $temp->fetch_assoc();
		if(!empty($temp_recargaswebgestion)){
			$otrosingresos["dia"][] = $temp_recargaswebgestion;
		} else {
			$result_temp['id_concepto']=7;
			$otrosingresos["dia"][] = $result_temp;
		}
	}
	if(isset($_POST["log"])){cron_print_log("consulta_error_recargaswebgestion");}

	//************************************************************************************************************
	//Query Web Bethop diario
	/*$query = "
		SELECT
			8 as id_concepto,
			'Recargas Web BetShop' as descripcion,
			COUNT(d.col_id) AS cantidad,
			SUM(d.col_Amount) AS monto,
			CAST(SUM(d.col_Amount) / COUNT(d.col_id)AS DECIMAL(10,2)) AS promedio
		from bc_apuestatotal.at_ClientDeposits as d
		LEFT JOIN bc_apuestatotal.tbl_DocumentType as dt  ON d.col_TypeId=dt.col_Id
		LEFT JOIN bc_apuestatotal.tbl_TranslationEntry AS T ON dt.col_NameId=T.col_TranslationId AND T.col_LanguageId='en'
		LEFT JOIN bc_apuestatotal.tbl_CashDesk as cs ON d.col_CashDeskId=cs.col_Id
		LEFT JOIN bc_apuestatotal.tbl_Betshop as b ON b.col_Id=cs.col_BetshopId
		where d.col_TypeId in (5)
			AND d.col_Created >= '{$dates->yesterday} 09:00:00'
			AND d.col_Created <  '{$dates->today} 09:00:00'
			AND b.col_Id in (SELECT col_Id FROM  bc_apuestatotal.tbl_Betshop  WHERE col_Name like '%televentas%')
		GROUP BY d.col_TypeId,dt.col_Id,T.col_TranslationId
	";
	$temp = $mysqli->query($query);
	if ($mysqli->error) {
		$result["recwebbet_consulta"] = $query;
		$result["recwebbet_error"] = $mysqli->error;
	} else {
		$temp_recargaswebBetShop = $temp->fetch_assoc();
		if(!empty($temp_recargaswebBetShop)){
			$otrosingresos["dia"][] = $temp_recargaswebBetShop;
		} else {
			$result_temp['id_concepto'] = 8;
			$otrosingresos["dia"][] = $result_temp;
		}
	}
	if(isset($_POST["log"])){cron_print_log("consulta_error_recargaswebBetShop");}

	//************************************************************************************************************
	//Query  Terminal deposit diario
	$query = "
		SELECT 
			9 as id_concepto,
			'TerminalDeposit' as descripcion,
			COUNT(d.col_id) AS cantidad,
			SUM(d.col_Amount) AS monto,
			CAST(SUM(d.col_Amount) / COUNT(d.col_id)AS DECIMAL(10,2)) AS promedio
		FROM bc_apuestatotal.at_ClientDeposits as d
		LEFT JOIN bc_apuestatotal.tbl_DocumentType as dt  ON d.col_TypeId=dt.col_Id
		LEFT JOIN bc_apuestatotal.tbl_TranslationEntry AS T ON dt.col_NameId=T.col_TranslationId AND T.col_LanguageId='en'
		LEFT JOIN bc_apuestatotal.tbl_CashDesk as cs ON d.col_CashDeskId=cs.col_Id
		LEFT JOIN bc_apuestatotal.tbl_Betshop as b ON b.col_Id=cs.col_BetshopId -- and b.col_Name like '%televentas%'
		WHERE 
			d.col_TypeId in (701)
			AND d.col_Created >= '{$dates->yesterday} 09:00:00' 
			AND d.col_Created < '{$dates->today} 09:00:00'
			AND b.col_Id in (SELECT col_Id FROM  bc_apuestatotal.tbl_Betshop  WHERE col_Name like '%televentas%28%')
		GROUP BY d.col_TypeId,dt.col_Id,T.col_TranslationId;
	";
	$temp = $mysqli->query($query);
	if ($mysqli->error) {
		$result["terminal_consulta"] = $query;
		$result["terminal_error"] = $mysqli->error;
	} else {
		$temp_TerminalDeposit = $temp->fetch_assoc();
		if(!empty($temp_TerminalDeposit)){
			$otrosingresos["dia"][] = $temp_TerminalDeposit;
		} else {
			$result_temp['id_concepto'] = 9;
			$otrosingresos["dia"][] = $result_temp;
		}
	}*/

    /*new query TELESERVICIOS Venta para terminales CUADRO 6 Venta para terminales tambo*/
    $query = "SELECT
                9 as id_concepto
                ,'Terminal deposito Tambo' AS descripcion
                , SUM(TB1.Transacciones) AS cantidad
                ,SUM(TB1.monto) AS monto
                ,  SUM(TB1.Transacciones)/SUM(TB1.Monto) AS promedio
                0 AS hold , 
                0 AS porcentaje_pagados,
                0 AS ganado
                    FROM	
                    (
                        SELECT  	COUNT(CT.id) AS Transacciones , SUM(CT.monto) AS monto
                        FROM 	wwwapuestatotal_gestion.tbl_televentas_clientes_transaccion CT
                        WHERE	CT.tipo_id = 33  -- Terminal Deposit - Tambo
                        AND	CT.estado = 1	-- aceptado
                        AND	CT.created_at  >= '{$dates->yesterday}' 	/* Parametro a cambiar*/
                        AND CT.created_at < '{$dates->today}'	/* Parametro a cambiar*/
                    ) TB1
                    ORDER BY descripcion
    ";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["terminal_consulta"] = $query;
        $result["terminal_error"] = $mysqli->error;
    } else {
        $temp_TerminalDeposit = $temp->fetch_assoc();
    if(!empty($temp_TerminalDeposit)){
        $otrosingresos["dia"][] = $temp_TerminalDeposit;
    } else {
        $result_temp['id_concepto'] = 9;
        $otrosingresos["dia"][] = $result_temp;
    }
}
    
    /*recargas web new query  CUADRO 5 Recargas web */
    $query = "SELECT 
			  8 as id_concepto
            ,'Recargas Web' AS descripcion
            , SUM(TB1.Transacciones) AS cantidad
            , SUM(TB1.monto) AS monto
            , SUM(TB1.Transacciones)/SUM(TB1.Monto) AS promedio
            ,0 AS hold
            ,0 AS porcentaje_pagados
                FROM	
                (
                    SELECT  	COUNT(CT.id) AS Transacciones , SUM(CT.total_Recarga) AS monto
                    FROM 	wwwapuestatotal_gestion.tbl_televentas_clientes_transaccion CT INNER JOIN  wwwapuestatotal_gestion.tbl_locales CL
                    ON	CT.cc_id = CL.cc_id
                    WHERE	CT.tipo_id = 2  -- Recarga Web
                    AND	CT.estado = 1	-- aceptado
                    AND	CT.created_at  >= '{$dates->yesterday}' 				/* Parametro a cambiar*/
                    AND CT.created_at  < '{$dates->today}'				/* Parametro a cambiar*/
                    AND	CL.red_id = 8 		-- TELESERVICIOS
                    AND	UPPER(CL.nombre) NOT LIKE '%TEST%' 
                    GROUP BY CL.id
                ) TB1
                ORDER BY descripcion;
	";
	$temp = $mysqli->query($query);
	if ($mysqli->error) {
		$result["terminal_consulta"] = $query;
		$result["terminal_error"] = $mysqli->error;
	} else {
		$temp_TerminalDeposit = $temp->fetch_assoc();
		if(!empty($temp_TerminalDeposit)){
			$otrosingresos["dia"][] = $temp_TerminalDeposit;
		} else {
			$result_temp['id_concepto'] = 8;
			$otrosingresos["dia"][] = $result_temp;
		}
	}

    

	if(isset($_POST["log"])){cron_print_log("consulta_error_TerminalDeposit");}

	//************************************************************************************************************
	//Query de Pagos TK Tiendas diario
	/*$query = "
		SELECT
			10 as id_concepto,
			'Pagos tickets Tiendas'  as descripcion,
			count(d.ganado) as cantidad,
			sum(d.ganado) as monto,
			cast(sum(d.ganado)/count(d.ganado)AS DECIMAL(10,2)) as promedio
		FROM tbl_transacciones_detalle d
		LEFT JOIN tbl_locales l ON (l.id = d.local_id)
		LEFT JOIN tbl_locales lp ON (lp.id = d.paid_local_id)
		WHERE d.paid_day >= '{$dates->yesterday}'
			AND d.paid_day < '{$dates->today}'
			AND d.paid_local_id IS NOT NULL
			AND d.local_id != d.paid_local_id
			AND d.tipo = '1'
			AND lp.cc_id in (3907,3938,3939,3940,3941,3942,3943,3944,3945,3946,3947,3948,3950,3951,3952,3953)
			-- AND lp.cc_id in (3938,3939,3940,3941,3942,3943,3919,3908,3906,3920,3909,3902,3905,3903,3925)
			AND l.zona_id!=9
	";
	$temp = $mysqli->query($query);
	if ($mysqli->error) {
		$result["ptktiendas_consulta"] = $query;
		$result["ptktiendas_error"] = $mysqli->error;
	} else {
		$temp_PagosTicketsTiendas = $temp->fetch_assoc();
		if(!empty($temp_PagosTicketsTiendas)){
			$otrosingresos["dia"][] = $temp_PagosTicketsTiendas;
		} else {
			$result_temp['id_concepto'] = 10;
			$otrosingresos["dia"][] = $result_temp;
		}
	}
	if(isset($_POST["log"])){cron_print_log("consulta_error_PagosTicketsTiendas");}

	//************************************************************************************************************
	//Query de pagos tk terminales diario
	$query = "
		SELECT
			11 as id_concepto,
			 'Pagos tickets Terminales'  as descripcion,
			COUNT(d.col_id) AS cantidad,
			SUM(d.col_Amount) AS monto,
			CAST(SUM(d.col_Amount)/COUNT(d.col_id)AS DECIMAL(10,2)) AS promedio
		from bc_apuestatotal.at_ClientDeposits as d
		LEFT JOIN bc_apuestatotal.tbl_CashDesk as cs ON d.col_CashDeskId=cs.col_Id
		LEFT JOIN bc_apuestatotal.tbl_Betshop as b ON b.col_Id=cs.col_BetshopId
		where d.col_TypeId in (702)
			AND d.col_Created >= '{$dates->yesterday} 09:00:00'
			AND d.col_Created < '{$dates->today} 09:00:00'
			AND b.col_Id in (SELECT col_Id FROM  bc_apuestatotal.tbl_Betshop  WHERE col_Name like '%televentas%28%')
		GROUP BY b.col_Id
	";
	$temp = $mysqli->query($query);
	if ($mysqli->error) {
		$result["ptkterm_consulta"] = $query;
		$result["ptkterm_error"] = $mysqli->error;
	} else {
		$temp_PagosTicketsTerminales = $temp->fetch_assoc();
		if(!empty($temp_PagosTicketsTerminales)){
			$otrosingresos["dia"][] = $temp_PagosTicketsTerminales;
		} else {
			$result_temp['id_concepto'] = 11;
			$otrosingresos["dia"][] = $result_temp;
		}
	}*/
    /*new query CUADRO 7 Otros pagos de tickets  */
    $query = "SELECT 	
                  descripcion
                ,IF(descripcion = 'Pagos tickets Tiendas y terminales',11 , 10 ) as id_concepto
                ,SUM(Transacciones) AS cantidad
                , SUM(ifnull(monto, 0)) AS monto
                ,IFNULL(SUM(ifnull(monto,0))/SUM(Transacciones), 0) as promedio
                ,0 AS hold 
                ,0 AS porcentaje_pagados
                FROM 	(
                    SELECT	'Pagos tickets Tiendas y terminales'  as descripcion,
                        count(d.id) as Transacciones,
                        sum(d.ganado) as monto
                    FROM 	wwwapuestatotal_gestion.tbl_transacciones_detalle d LEFT JOIN wwwapuestatotal_gestion.tbl_locales l 
                    ON 	(l.id = d.local_id) LEFT JOIN wwwapuestatotal_gestion.tbl_locales lp 
                    ON 	(lp.id = d.paid_local_id)
                    WHERE 	d.paid_day >= '{$dates->yesterday}'			/* Parametro a cambiar*/
                    AND 	d.paid_day < '{$dates->today}'			/* Parametro a cambiar*/
                    AND 	d.paid_local_id IS NOT NULL
                    AND 	d.local_id != d.paid_local_id
                    AND 	d.tipo = '1'	-- 1 ticket, 2 terminal, 3 cashdesk
                    AND 	l.red_id != 7	-- Local generado
                    AND 	lp.red_id = 8 	-- Televentas 
                    AND	UPPER(lp.nombre) NOT LIKE '%TEST%' 
                    AND 	l.zona_id != 9 -- Tiendas pago sur (glosario)  se deberia de quitar

                    UNION ALL

                    SELECT	'Pagos tickets Terminales Tambo'  as descripcion,
                        count(d.id) as Transacciones,
                        sum(d.ganado) as monto
                    FROM 	wwwapuestatotal_gestion.tbl_transacciones_detalle d LEFT JOIN wwwapuestatotal_gestion.tbl_locales l 
                    ON 	(l.id = d.local_id) LEFT JOIN wwwapuestatotal_gestion.tbl_locales lp 
                    ON 	(lp.id = d.paid_local_id)
                    WHERE 	d.paid_day >= '{$dates->yesterday}'			/* Parametro a cambiar*/
                    AND 	d.paid_day < '{$dates->today}'			/* Parametro a cambiar*/
                    AND 	d.paid_local_id IS NOT NULL
                    AND 	d.local_id != d.paid_local_id
                    AND 	d.tipo = '1'	-- 1 ticket, 2 terminal, 3 cashdesk
                    AND 	l.red_id = 7	-- Local generado
                    AND 	lp.red_id = 8 	-- Televentas
                    AND	UPPER(lp.nombre) NOT LIKE '%TEST%'
                    AND 	l.zona_id != 9 -- Tiendas pago sur (glosario)  se deberia de quitar
                ) TBREG
                GROUP BY descripcion
	";
	$temp = $mysqli->query($query);
	if ($mysqli->error) {
		$result["terminal_consulta"] = $query;
		$result["terminal_error"] = $mysqli->error;
	} else {
	    while ($row = $temp->fetch_assoc()) {
            $otrosingresos["dia"][] = $row;
        }

	}

	if(isset($_POST["log"])){cron_print_log("consulta_error_PagosTicketsTerminales");}

	//************************************************************************************************************
	foreach ($otrosingresos as $key => $value) {
		for ($i = 0; $i < count($value); $i++) {
			$otrosingresos[$key][$i]["cantidad"]    = number_format($otrosingresos[$key][$i]["cantidad"], 0, ".", "");
			$otrosingresos[$key][$i]["monto"]   = number_format($otrosingresos[$key][$i]["monto"], 2, ".", "");
			$otrosingresos[$key][$i]["promedio"]   = number_format($otrosingresos[$key][$i]["promedio"], 2, ".", "");

			$query_insert = " 
				INSERT INTO tbl_reporte_teleservicios_ventas_x_producto ( 
                	id_reporte_teleservicios_concepto,
                	fecha,
					num_tickets_apostado,
					total_tickets_apostado,
					promedio,
					num_tickets_calculado,
					total_tickets_calculado,
					resultado,
					hold,
					num_tickets_pagado,
					total_tickets_pagado,
					porcentaje_tickets_pagado,
					id_estado,
                	id_user_created,
                	created_at
                ) VALUES ( 
                    '". $otrosingresos[$key][$i]["id_concepto"] ."', 
                    '". $fecha ."', 
                    '". $otrosingresos[$key][$i]["cantidad"] ."', 
                    '". $otrosingresos[$key][$i]["monto"] ."', 
                    '". $otrosingresos[$key][$i]["promedio"] ."', 
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    '1', 
                    '". $usuario_id ."', 
                    now()
                );
            ";
            $mysqli->query($query_insert);
			if ($mysqli->error) {
				$result["otrosingresos_insert_query"] = $query;
				$result["otrosingresos_insert_error"] = $mysqli->error;
			}
		}
	}
	if(isset($_POST["log"])){cron_print_log("foreach");}

	//************************************************************************************************************
	//************************************************************************************************************
	//************************************************************************************************************
	//************************************************************************************************************

    $result["http_code"] = 200;
    $result["status"] = "ok.";
    //$result["dates"] = $dates;
    $result["resultado"] = $resultado;
    $result["otrosingresos"] = $otrosingresos;
    return $result;
}


















if (isset($_POST["accion"]) && $_POST["accion"] === "generar_transacciones") {
	$result = generar_transacciones($_POST["fecha"]);
}







//*******************************************************************************************************************
//*******************************************************************************************************************
// ENVIAR CORREO
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "enviar_correo") {

	include '/var/www/html/sys/helpers.php';
	include '/var/www/html/sys/mailer/class.phpmailer.php';
	
	$fecha = $_POST["fecha"];
	$asunto = $_POST["asunto"];
	$correos_principales = explode( ',', $_POST["correos_principales"]);

	$oculto_validacion = false;
	$oculto_validacion_arroba = strpos($_POST["correos_ocultos"], '@');
	if ($oculto_validacion_arroba !== false) {
		$oculto_validacion = true;
		$correos_ocultos = explode( ',', $_POST["correos_ocultos"]);
	}

	$body = $_POST["body"];

	global $correction_ok;
	try {
		$mail = new PHPMailer(true);
		$mail->IsSMTP();
		$mail->CharSet    = 'utf-8';
		$mail->SMTPDebug  = 1;
		$mail->SMTPAuth   = true;
		$mail->Host       = "smtp.gmail.com";
		$mail->Port       = 465;
		$mail->SMTPSecure = "ssl";

		foreach ($correos_principales as $correo) {
			$mail->AddAddress(trim($correo));
		}
		if ($oculto_validacion===true){
			foreach ($correos_ocultos as $correo) {
				$mail->AddBCC(trim($correo));
			}
		}
		
		$subject = "TELESERVICIOS - VENTAS Y RESULTADOS ";
		if(strlen($asunto) > 2) {
			$subject = mb_strtoupper($asunto, 'UTF-8') . " " . $subject;
		}

		$mail->Username = env('MAIL_GESTION_USER');
		$mail->Password = env('MAIL_GESTION_PASS');
		$mail->FromName = env('MAIL_GESTION_NAME');
		$mail->Subject  = $subject . $fecha . " REPORTE GESTION#" . time();
		$mail->Body     = $body;
		$mail->isHTML(true);

		if(!($mail->send())) {
			$result["status"] = "Problemas enviando correo electrónico a ".$correo;
			$result["status"] .= "<br/>".$mail->ErrorInfo;	
		} else {
			$result["status"] = "Mensaje enviado correctamente";
		} 

    	$result["http_code"] = 200;

	} catch (phpmailerException $e) {		
    	$result["http_code"] = 400;
    	$result["status"] = "phpmailerException.";
    	$result["message"] = $e;
	} catch (Exception $e) {		
    	$result["http_code"] = 400;
    	$result["status"] = "Exception.";
    	$result["message"] = $e;
	}
}


echo json_encode($result);
