<?php
$return = array();
$return["memory_init"]=memory_get_usage();
$return["time_init"] = microtime(true);
include("global_config.php");
include("db_connect.php");
include("sys_login.php");



function rec_data_to_db($d){
	global $mysqli;
	$tmp=array();
	$nulls=array("null","",false);
	foreach ($d as $k => $v) {
		// if($v===0){
		if(is_numeric($v)){
			// $tmp[$k]=$v;
			$v=str_replace(",", ".", $v);
			$tmp[$k]="'".$v."'";
		}elseif(in_array($v, $nulls)){
			$tmp[$k]="NULL";
		}else{
			if(is_float($v)){
				$tmp[$k]="'".$v."'";
			}elseif(is_int($v)){
				$tmp[$k]=$v;
			}else{
				$v=str_replace(",", ".", $v);
				$tmp[$k]="'".trim($mysqli->real_escape_string($v))."'";
			}
		}
	}
	return $tmp;
}


function periodo_liquidacion_agregar_deudas($post){
	global $mysqli;
	global $return;
	global $login;
	$periodo_liquidacion_id = $post["periodo_liquidacion_id"];//2021-10-01
	$comando_periodo_liquidacion = "SELECT id,fecha_inicio,fecha_fin,estado from tbl_periodo_liquidacion where id = ".$periodo_liquidacion_id;
	$tbl_periodo_liquidacion = $mysqli->query($comando_periodo_liquidacion)->fetch_assoc();

	$fecha_ini = $tbl_periodo_liquidacion["fecha_inicio"];//2021-10-01
	$fecha_fin = $tbl_periodo_liquidacion["fecha_fin"];//2021-10-01

	//$return["periodo"]=$periodo;	
	$periodo_rango = date("d",strtotime($fecha_ini))."-".date("d",strtotime($fecha_fin));
	$periodo_rango_int = date("d",strtotime($fecha_ini)).date("d",strtotime($fecha_fin));
	
	$fecha_fin_temp = date("Y-m-d",strtotime($tbl_periodo_liquidacion["fecha_fin"]." +1 day"));

	$deuda_command = "
		SELECT
				cab.canal_de_venta_id AS canal_de_venta_id,
				cab.fecha,
				l.id AS local_id,
				CAST(YEAR(cab.fecha) AS SIGNED) AS periodo_year,
				DATE_FORMAT(cab.fecha, '%m') AS periodo_mes,

				SUM(cab.total_freegames) AS part_fg,
				SUM(cab.total_pagado) AS total_pagado,
				CAST(SUM(cab.pagado_en_otra_tienda) - SUM(cab.pagado_de_otra_tienda) AS DECIMAL(10,2)) AS dif_tk,
				(SUM(cab.total_freegames)) AS web_total,
				(total_pagado - pagado_en_otra_tienda) AS pagados_en_su_punto_propios
				FROM
					tbl_transacciones_cabecera  cab
				LEFT JOIN tbl_locales l ON (l.id  = cab.local_id)
				WHERE
					cab.fecha >= '$fecha_ini' AND cab.fecha < '$fecha_fin_temp'
				AND cab.estado = 1
				AND cab.servicio_id in (1,3,9,13,15,17)
				AND l.reportes_mostrar = '1'
				AND l.red_id = 5
				GROUP BY 
					local_id ASC,
					canal_de_venta_id ASC,
					cab.fecha DESC";
	$deuda_query = $mysqli->query($deuda_command);
	//echo $deuda_command;die();
	if($mysqli_error = $mysqli->error){
		$return["error"]=true;
		$return["error_msg"]= "Error Servidor";
		$return["error_detalle"]= $mysqli_error;
		print_r(json_encode($return));
		exit();
	}
	$temp_arr = array();
	while($d=$deuda_query->fetch_assoc()){
		$tmp = array();
			//$tmp["proceso_unique_id"]=$d["proceso_unique_id"];
			$tmp["periodo_liquidacion_id"]=$periodo_liquidacion_id;
			$tmp["fecha_ingreso"]=date("Y-m-d H:i:s");
			$tmp["fecha"]=$d["fecha"];
			$tmp["periodo_year"]=$d["periodo_year"];
			$tmp["periodo_mes"]=$d["periodo_mes"];
			$tmp["periodo_rango"]  = $periodo_rango;
			$tmp["periodo_inicio"] = $fecha_ini;
			$tmp["periodo_fin"]    = $fecha_fin;
			$tmp["periodo_rango_int"]=$periodo_rango_int;
			$tmp["canal_de_venta_id"]=$d["canal_de_venta_id"];
			$tmp["local_id"]=$d["local_id"];
			$tmp["saldo"]=0;
			$tmp["estado"]=1;

		switch ($d["canal_de_venta_id"]) {
			case 15:
			break;
			case 16: // PBET
				$part_arr = $tmp;
				$part_arr["monto"]=$d["part_fg"];
				$temp_arr["part"][]=$part_arr;

				$web_arr = $tmp;
				$web_arr["monto"]=0;
				$temp_arr["web"][]=$web_arr;
			break;
			case 17:
				$part_arr = $tmp;
				$part_arr["monto"]=$d["part_fg"];
				$temp_arr["part"][]=$part_arr;

			break;
			case 18:
			break;
			case 19:
				$part_arr = $tmp;
				$part_arr["monto"]=$d["part_fg"];
				$temp_arr["part"][]=$part_arr;

			break;
			case 20:
			break;
			case 21:
				$part_arr = $tmp;
				$part_arr["monto"]=$d["part_fg"];
				$temp_arr["part"][]=$part_arr;
			break;
			case 30:  //bingo =>  restar pago de bingos
				$part_arr = $tmp;
				//$part_arr["monto"]=$d["part_fg"];
				$part_arr["monto"] = ($d["part_fg"] - $d["pagados_en_su_punto_propios"]);
				$temp_arr["part"][]=$part_arr;
			break;
			case 34:  //Carrera de Caballos => Carrera de Caballos
				$part_arr = $tmp;
				$part_arr["monto"]=$d["part_fg"];
				$temp_arr["part"][]=$part_arr;
			break;
			case 37:  //Recarga Web
				$part_arr = $tmp;
				$part_arr["monto"]=$d["web_total"];
				$temp_arr["part"][]=$part_arr;
			break;
			case 42: // Kurax MVR
			case 43: // Kurax SBT
				$part_arr = $tmp;
				$part_arr["monto"]=$d["part_fg"];
				$temp_arr["part"][]=$part_arr;
			break;
		}
	}


	$deudas_tipos_arr = array();
		$deudas_tipos_command = "SELECT id,codigo FROM tbl_deudas_tipos WHERE estado = '1'";
		$deudas_tipos_query = $mysqli->query($deudas_tipos_command);
		while ($dt = $deudas_tipos_query->fetch_assoc()) {
			$deudas_tipos_arr[$dt["id"]]=$dt["codigo"];
		}

	$deudas_arr = array();
	$return["num_update"]=0;
	$return["num_insert"]=0;
	$return["num_nothing"]=0;
	$mysqli->query("START TRANSACTION");
	$valores_insert_array = [];

	//echo "<pre>";print_r($temp_arr["part"]);echo "</pre>";die();

	$val_deuda_array = [];
	foreach ($temp_arr as $tipo => $tmp_v) {
		foreach ($tmp_v as $v_k => $v) {
			$v["tipo"]=$tipo;
			$v["tipo_id"]=array_search($tipo, $deudas_tipos_arr);
			$v["at_unique_id"]=md5($v["periodo_liquidacion_id"].$v["local_id"].$v["canal_de_venta_id"].$v["tipo"].$v["fecha"]); //RE-Finalizar
			unset($v["fecha"]);
			$data_to_db=rec_data_to_db($v);
			//echo "<pre>";print_r($v);echo "</pre>";//die();
			/*if($v["monto"]!=0){*/
				$valores_insertar="";
				$valores_insertar.="(";
				$valores_insertar.=implode(",", $data_to_db);
				$valores_insertar.=")";
				$valores_insert_array[]=$valores_insertar;// ["(col1val,col2val)" , "(col1val,col2val)"]
			/*}*/

			if (!in_array($data_to_db['local_id'], $val_deuda_array)) {
				$val_deuda_array[] = $data_to_db['local_id'];
			}
		}
	}
	
	$val_insertar_deuda_eec = implode(",", $val_deuda_array);
	// $return['val_insertar_deuda_eec'] = $val_insertar_deuda_eec;
	periodo_liquidacion_actualizacion_deudas($val_insertar_deuda_eec, $fecha_ini, $fecha_fin);

	if(count($valores_insert_array)>0){
		$insert_command2 = "INSERT INTO tbl_deudas";
		$insert_command2.="(";
		$insert_command2.=implode(",", array_keys($data_to_db));
		$insert_command2.=")";
		$insert_command2.=" VALUES ";
		$insert_command2.=implode(",",$valores_insert_array);//(col1val,col2val),(col1val,col2val),(col1val,col2val)
		$insert_command2.=" ON DUPLICATE KEY UPDATE ";
		$uqn=0;
		$no_update_array = array("fecha_ingreso");
		foreach ($data_to_db as $key => $value) {
			if(!in_array($key, $no_update_array)){
				if($uqn>0) { $insert_command2.=","; }
				$insert_command2.= $key." = VALUES(".$key.")";
				$uqn++;
			}
		}
		$return["insert_command2"]=$insert_command2;
		$return["valores_insert_array"]=$valores_insert_array[0];

		$mysqli->query($insert_command2);
		if($mysqli_error = $mysqli->error){
			$return["error"]=true;
			$return["error_msg"]= "Error Servidor";
			$return["error_detalle"]= $mysqli_error;
			print_r(json_encode($return));
			exit();
		}else{
			$affected_rows = $mysqli->affected_rows;
			$return["affected_rows"]=$affected_rows;
			if($affected_rows==2){
				$return["num_update"]++;
			}elseif($affected_rows==1){
				$return["num_insert"]++;
			}else{
				$return["num_nothing"]++;
			}
		}
		$mysqli->query($insert_command2);
	}
	////
	////tbl_pagos  saldo a favor  , excedente
	$query = "SELECT abono AS monto,periodo_liquidacion_id,local_id,pago_detalle_id
			FROM tbl_pagos 
			WHERE periodo_liquidacion_id < {$periodo_liquidacion_id}
			AND pago_tipo_id = 5 /*saldo a favor*/
			AND deuda_tipo_id is NULL
			AND estado = 1
			";
	$saldos_periodo_anterior = $mysqli->query($query);
	$pagos_arr2 = [];
	while ($saldo = $saldos_periodo_anterior->fetch_assoc()) {
		$pago = array();
		$pago["fecha_ingreso"] = date("Y-m-d H:i:s");
		$pago["periodo_year"] = date("Y",strtotime($fecha_fin));
		$pago["periodo_mes"] = date("m",strtotime($fecha_fin));
		$pago["periodo_rango"] = date("d",strtotime($fecha_ini))."-".date("d",strtotime($fecha_fin));
		$pago["periodo_inicio"] = $fecha_ini;
		$pago["periodo_fin"] = $fecha_fin;

		$pago["periodo_rango_int"]=intval(str_replace("-", "", $pago["periodo_rango"]));
		$pago["local_id"] = $saldo["local_id"];
		$pago["estado"] = 1;
		$pago["abono"] = $saldo["monto"];
		$pago["pago_tipo_id"] = 5;//saldo a favor
		$pago["deuda_tipo_id"] = 1;//participacion
		$pago["pago_detalle_id"] = $saldo["pago_detalle_id"];
		$pago["at_unique_id"] = md5($pago["fecha_ingreso"].$pago["local_id"].$pago["deuda_tipo_id"].$pago["pago_tipo_id"]);
		$pago["periodo_liquidacion_id"] = $periodo_liquidacion_id;
		$pagos_arr2[$saldo["local_id"]][] = $pago;
	}
	//echo "<pre>";print_r($pagos_arr2);echo "</pre>";die();
	//////deudas periodo procesado   $periodo_liquidacion_id

	foreach ($pagos_arr2 as $local_id => $values) {
		foreach ($values as $key => $valuepago) {
			//echo "<pre>";print_r($valuepago);echo "</pre>";die();
			$repartir_monto = $valuepago["abono"]; //
			$query = "SELECT  d.local_id, d.tipo,d.tipo_id ,
						sum(d.monto) AS 'deuda',
						(SELECT sum(p.abono)  FROM tbl_pagos p WHERE p.periodo_liquidacion_id =  $periodo_liquidacion_id
							AND p.local_id = d.local_id AND p.estado = 1  AND p.deuda_tipo_id = d.tipo_id
						) AS 'pago',
						(sum(d.monto) -
						(SELECT IFNULL(sum(p.abono),0)  FROM tbl_pagos p WHERE p.periodo_liquidacion_id = $periodo_liquidacion_id
							AND p.local_id = d.local_id AND p.estado = 1  AND p.deuda_tipo_id = d.tipo_id
						)) AS 'saldo'
						FROM tbl_deudas d
						WHERE d.periodo_liquidacion_id =  $periodo_liquidacion_id
						AND d.local_id = $local_id
						AND d.estado = 1
						GROUP BY d.local_id, d.tipo ";
			$deudas_periodo = $mysqli->query($query);
			$deudas_por_local=[];
			while ($fila = $deudas_periodo->fetch_assoc()) {
				$deudas_por_local[$fila["local_id"]][]= $fila;
			}
			//echo "<pre>";print_r($valuepago);echo "</pre>";
			//echo "<pre>";print_r($deudas_por_local);echo "</pre>";
			if(!isset($deudas_por_local[$local_id])){//si no hay deudas en periodo, agregar pago saldo a favor
				$pago_saldo =[];
				$pago_saldo = $valuepago;//pagos_arr[$local_id];
				$pago_saldo["abono"] = $repartir_monto;
				$pago_saldo["deuda_tipo_id"] = null;
				$pago_saldo["pago_tipo_id"] = 5;//saldo a favor
				$pago_saldo["at_unique_id"] = md5($pago_saldo["fecha_ingreso"].$pago_saldo["local_id"]."null".$pago_saldo["pago_tipo_id"]);

				$data_to_db_pagos=rec_data_to_db($pago_saldo);
				$command = "INSERT INTO tbl_pagos";
					$command.="(";
					$command.=implode(",", array_keys($data_to_db_pagos));
					$command.=")";
					$command.=" VALUES ";
					$command.="(";
					$command.=implode(",", $data_to_db_pagos);
					$command.=")";
				$mysqli->query($command);
				$affected_rows_pago = $mysqli->affected_rows;
				continue;
			}
			$deudas = $deudas_por_local[$local_id];
			/*$negativo_valor = 0;///deudas negativas
			foreach ($deudas as $key => $value) {
				if($value["saldo"] < 0){
					$negativo_valor += $value["saldo"];
				}
			}
			$repartir_monto -= $negativo_valor;*/
			foreach ($deudas as $key => $value) { ///deudas  repartir saldo
				$deuda_tipo = $value["tipo_id"];
				//$deuda_monto = $value["deuda"];
				$deuda_monto = $value["saldo"];
				$saldo = $value["saldo"];
				$deuda_abonar = 0;
				if($deuda_monto > 0){
					if($repartir_monto > 0){
						if($deuda_monto >= $repartir_monto){
							$deuda_abonar = $repartir_monto;
						}else{
							$deuda_abonar = $deuda_monto;
						}
						$repartir_monto = $repartir_monto - $deuda_abonar;
					}
				}else{
					$deuda_abonar = $deuda_monto;
				}
				///insert pago
				if($deuda_abonar > 0){
					$pago_deuda =[];
					$pago_deuda = $valuepago;//$pagos_arr[$local_id];
					$pago_deuda["abono"] = $deuda_abonar;
					$pago_deuda["deuda_tipo_id"] = $deuda_tipo;
					$pago_deuda["pago_tipo_id"] = 5;//saldo a favor
					$pago_deuda["at_unique_id"] = md5($pago_deuda["fecha_ingreso"].$pago_deuda["local_id"].$pago_deuda["deuda_tipo_id"].$pago_deuda["pago_tipo_id"].$pago_deuda["pago_detalle_id"]);

					$data_to_db_pagos=rec_data_to_db($pago_deuda);
					$command = "INSERT INTO tbl_pagos";
						$command.="(";
						$command.=implode(",", array_keys($data_to_db_pagos));
						$command.=")";
						$command.=" VALUES ";
						$command.="(";
						$command.=implode(",", $data_to_db_pagos);
						$command.=")";
					$inser= $mysqli->query($command);
				//	$affected_rows_pa = $mysqli->affected_rows;
				}
				///fin insert pago
			}
			if($repartir_monto > 0){//si monto a repartir sobro , agregar saldo a favor
				$pago_saldo =[];
				$pago_saldo = $valuepago;//$pagos_arr[$local_id];
				$pago_saldo["abono"] = $repartir_monto;
				$pago_saldo["deuda_tipo_id"] = null;
				$pago_saldo["pago_tipo_id"] = 5;//saldo a favor
				$pago_saldo["at_unique_id"] = md5($pago_saldo["fecha_ingreso"].$pago_saldo["local_id"]."null".$pago_saldo["pago_tipo_id"]);

				$data_to_db_pagos=rec_data_to_db($pago_saldo);
				$command = "INSERT INTO tbl_pagos";
					$command.="(";
					$command.=implode(",", array_keys($data_to_db_pagos));
					$command.=")";
					$command.=" VALUES ";
					$command.="(";
					$command.=implode(",", $data_to_db_pagos);
					$command.=")";
				$mysqli->query($command);
				//$affected_rows_pago = $mysqli->affected_rows;
			}
		}
	}
	
	$command = "UPDATE tbl_pagos SET estado = 0
				WHERE periodo_liquidacion_id < {$periodo_liquidacion_id}
				AND pago_tipo_id = 5
				AND deuda_tipo_id is NULL
				AND estado = 1 ";
	$mysqli->query($command);
	////

	$return["valores_insert_array"]=count($valores_insert_array);
	$return["memory_end"]=memory_get_usage();
	$return["time_end"] = microtime(true);
	$return["memory_total"]=($return["memory_end"]-$return["memory_init"]);
	$return["time_total"]=($return["time_end"]-$return["time_init"])*1000;
	$mysqli->query("COMMIT");
}

function periodo_liquidacion_actualizacion_pagos_deudas($data){
	global $mysqli;
	global $return;

	$periodo_liquidacion_id = $data["periodo_liquidacion_id"];//2021-10-01
	$comando_periodo_liquidacion = "SELECT id,fecha_inicio,fecha_fin,estado from tbl_periodo_liquidacion where id = ".$periodo_liquidacion_id;
	$tbl_periodo_liquidacion = $mysqli->query($comando_periodo_liquidacion)->fetch_assoc();

	$fecha_ini = $tbl_periodo_liquidacion["fecha_inicio"];//2021-10-01
	$fecha_fin = $tbl_periodo_liquidacion["fecha_fin"];//2021-10-01

	$locales_search = array();
	$eec_insert = array();

	$locales_query = "
        SELECT
            l.operativo
            ,l.id
            ,l.nombre AS 'local_nombre'
        FROM tbl_locales l
        WHERE 
            l.id NOT IN (1)
            AND l.reportes_mostrar = '1'
            AND l.operativo in (1,2)
            AND l.red_id = 5
    ";
    $locales_q= $mysqli->query($locales_query);
	while ($l = $locales_q->fetch_assoc()) {
		$sql_deudas_pagos = "
			SELECT
				(
					SELECT IF(SUM(d.monto),SUM(d.monto),0) AS monto_deuda
					FROM tbl_deudas d
					WHERE
						d.local_id = {$l['id']}
						AND d.estado = 1
						AND d.periodo_inicio >= '$fecha_ini'
						AND d.periodo_fin <= '$fecha_fin'
						AND d.estado_liquidacion != 1
				)AS monto_deuda
				,(
					SELECT IFNULL(sum(pd.monto), 0)
					FROM tbl_pagos_detalle pd
					WHERE
						pd.id in (
									SELECT distinct p.pago_detalle_id
									FROM tbl_pagos p
									WHERE
										p.local_id = {$l['id']}
										AND p.estado = 1
										AND p.periodo_inicio >= '$fecha_ini'
										AND p.periodo_fin <= '$fecha_fin'
								)
						AND pd.estado_liquidacion != 1
				) AS monto_pago
				,NOW() as fecha_busqueda
		";
		$deudas_pagos_q= $mysqli->query($sql_deudas_pagos);
		$monto_deuda = 0;
		$monto_pago = 0;
		while ($row_montos = $deudas_pagos_q->fetch_assoc()) {
			$monto_deuda = $row_montos['monto_deuda'];
			$monto_pago = $row_montos['monto_pago'];
		}

		$loc_search_id = $l['id'];
		$locales_search[] = $loc_search_id;
		$eec_insert[] = "($loc_search_id, $monto_deuda, $monto_pago)";
	}
	if (count($locales_search) > 0 || count($eec_insert) > 0) {
		$locales_search = implode(",", $locales_search);
		$eec_insert = implode(",", $eec_insert);

		$sql_insert = "
			INSERT INTO tbl_estados_cuenta
			(
				id_local
				,deuda
				,pago
			)
			VALUES 
			$eec_insert
			ON DUPLICATE KEY UPDATE
				deuda = deuda + VALUES(deuda)
				,pago = pago + VALUES(pago)
		";
		$mysqli->query($sql_insert);
		if($mysqli_error = $mysqli->error){
			$return["error"]=true;
			$return["error_msg"]= "Error UPDATE estados_cuenta";
			$return["error_detalle"]= $mysqli_error;
			exit();
		}

		$sql_update_deudas = "
			UPDATE tbl_deudas
			SET estado_liquidacion = 1
			WHERE
				local_id IN ($locales_search)
				AND estado = 1
				AND periodo_inicio >= '$fecha_ini'
				AND periodo_fin <= '$fecha_fin'
				AND estado_liquidacion IS NULL
		";
		$mysqli->query($sql_update_deudas);
		if($mysqli_error = $mysqli->error){
			$return["error"]=true;
			$return["error_msg"]= "Error UPDATE deudas";
			$return["error_detalle"]= $mysqli_error;
			exit();
		}

		$sql_update_pagos = "
			UPDATE tbl_pagos_detalle pd
			SET pd.estado_liquidacion = 1
			WHERE
				pd.id in (
							SELECT distinct p.pago_detalle_id
							FROM tbl_pagos p
							WHERE
								p.local_id IN ($locales_search)
								AND p.estado = 1
								AND p.periodo_inicio >= '$fecha_ini'
								AND p.periodo_fin <= '$fecha_fin'
						)
				AND pd.estado_liquidacion IS NULL
		";
		$mysqli->query($sql_update_pagos);
		if($mysqli_error = $mysqli->error){
			$return["error"]=true;
			$return["error_msg"]= "Error Update pagos";
			$return["error_detalle"]= $mysqli_error;
			exit();
		}
	}
}

function periodo_liquidacion_actualizacion_deudas($val_insertar_deuda_eec, $fecha_ini, $fecha_fin){
	global $mysqli;
	global $return;

	$sql_deudas = "
	SELECT
		d.local_id,
		IF(SUM(d.monto),SUM(d.monto),0) AS monto_deuda,
		NOW() as fecha_busqueda
	FROM tbl_deudas d
	WHERE
		d.local_id IN ($val_insertar_deuda_eec)
		AND d.periodo_inicio >= '$fecha_ini'
		AND d.periodo_fin <= '$fecha_fin'
		AND (d.estado_liquidacion IS NULL OR d.estado_liquidacion !=1)
	GROUP BY
		d.local_id
	";
	// $return['sql_deudas'] = $sql_deudas;
	$deudas_q= $mysqli->query($sql_deudas);

	$deudas_arr = array();
	while ($row = $deudas_q->fetch_assoc()) {
		$row_to_db=rec_data_to_db($row);
		$deudas_insertar = "";
		$deudas_insertar.= "(";
		$deudas_insertar.= implode(",", $row_to_db);
		$deudas_insertar.= ")";
		$deudas_arr[] = $deudas_insertar;
	}
	// $return['deudas_arr'] = $deudas_arr;

	if (count($deudas_arr)>0) {
		$deuda_insert_command = "INSERT INTO tbl_estados_cuenta ";
		$deuda_insert_command.= "(id_local, deuda, update_fecha_deuda)";
		$deuda_insert_command.= " VALUES ";
		$deuda_insert_command.= implode(",", $deudas_arr);
		$deuda_insert_command.= " ON DUPLICATE KEY UPDATE ";
		$deuda_insert_command.= " deuda = deuda + VALUES(deuda), update_fecha_deuda = VALUES(update_fecha_deuda) ";

		// $return['deuda_insert_command'] = $deuda_insert_command;

		$mysqli->query($deuda_insert_command);
		if($mysqli_error = $mysqli->error){
			$return["error"]=true;
			$return["error_msg"]= "Error al insertar la deuda";
			$return["error_detalle"]= $mysqli_error;
			print_r(json_encode($return));
			exit();
		}
	}
}


if(isset($_POST["sec_adm_periodo_liquidacion_save"])){
	$data=$_POST["sec_adm_periodo_liquidacion_save"];

	$fecha_inicio= $data["fecha_inicio"];
	$fecha_fin= $data["fecha_fin"];
	if($fecha_inicio == "" || $fecha_fin ==""){
		$return["error"] = true;
		$return["error_msg"] = "Ingrese fechas";
		print_r(json_encode($return));
		return;
	}
	$exists = $mysqli->query("SELECT id from tbl_periodo_liquidacion WHERE  fecha_inicio= '$fecha_inicio'  AND fecha_fin='$fecha_fin'")->fetch_assoc();
	$exists2 = $mysqli->query("SELECT COUNT(id) as periodo FROM tbl_periodo_liquidacion
					WHERE
					 fecha_inicio BETWEEN '$fecha_inicio' AND '$fecha_fin'
					OR fecha_fin BETWEEN '$fecha_inicio' AND '$fecha_fin'
					OR '$fecha_inicio' BETWEEN fecha_inicio AND fecha_fin")
				 ->fetch_assoc();
	if($exists){
		$return["error"]=true;
		$return["error_msg"]= "Ya existe un periodo liquidación con esas fechas";
	}
	elseif( strtotime($fecha_inicio) >strtotime($fecha_fin) ){
		$return["error"]=true;
		$return["error_msg"]= "Fecha Fin debe ser mayor a Fecha Inicio";
	}
	elseif($exists2["periodo"] > 0){
		$return["error"]=true;
		$return["error_msg"]= "Ya existe un periodo liquidación en ese rango de fechas";
	}
	else{
		if($data["id"]=="new"){
			$insert_command = "INSERT INTO tbl_periodo_liquidacion (fecha_inicio,fecha_fin,estado,created_at)";
			$insert_command.= "VALUES ('".$fecha_inicio."','".$fecha_fin."',0,now())";
			$mysqli->query($insert_command);
			if($mysqli->error){
				print_r($mysqli->error);
				echo "\n";
				echo $insert_command;
				exit();
			}
			$return["id"] = $mysqli->insert_id;
			$return["mensaje"]= "Periodo ".$return["id"]." :  $fecha_inicio - $fecha_fin  Insertado";

		}else{
			$udpate_command = "UPDATE tbl_periodo_liquidacion 
				SET fecha_inicio = '".$fecha_inicio."'
				, fecha_fin = '".$fecha_fin."'
				,updated_at=now() 
				WHERE id = '".$data["id"]."'";
			$mysqli->query($udpate_command);
			$return["mensaje"]= "Periodo ".$data["id"]." Actualizado";
		}
	}
}


if(isset($_POST["sec_adm_periodo_liquidacion_estado"])){
	$data=$_POST["sec_adm_periodo_liquidacion_estado"];
	
	$periodo_liquidacion_id=$data["periodo_liquidacion_id"];
	$periodo = $data["periodo"];
	$estado=$data["estado"];
	$estado_nombre=$data["estado_nombre"];
	if($estado == 1  || $estado == 2){//procesar  o reprocesar
		$udpate_command = "
				UPDATE tbl_periodo_liquidacion
				SET updated_at=now(),
				    estado='".$estado."' 
				WHERE id = '".$periodo_liquidacion_id."'";
		$mysqli->query($udpate_command);

		periodo_liquidacion_agregar_deudas($data);
	}

	// Actualizar la tabla de estados de cuenta
	// periodo_liquidacion_actualizacion_pagos_deudas($data);
	
	
	$return["mensaje"]="Periodo $periodo ".$estado_nombre;
}



if(isset($_POST["sec_periodo_liquidacion_list"])){
	$data=$_POST["sec_periodo_liquidacion_list"];
	
	$comando_select = "SELECT 
						id,
						fecha_inicio,
						fecha_fin,
						estado,
						created_at,
						updated_at,
						   (CASE  
								WHEN estado ='0' THEN 'Pendiente'
								WHEN estado ='1' THEN 'Procesado'
								ELSE 'Reprocesado'
					 		END ) AS estado_nombre	
					   FROM tbl_periodo_liquidacion";
	$query = $mysqli->query($comando_select);
	$lista=[];
	while($d=$query->fetch_assoc()){
		$lista[]=$d;
	}
	$return["lista"]=$lista;
	$return["mensaje"]="lista realizada correctamente";
}



$return["memory_end"]=memory_get_usage();
$return["time_end"] = microtime(true);
$return["memory_total"]=($return["memory_end"]-$return["memory_init"]);
$return["time_total"]=($return["time_end"]-$return["time_init"]);
print_r(json_encode($return));
?>