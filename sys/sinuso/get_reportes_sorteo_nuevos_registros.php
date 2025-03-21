<?php
$result=array();
include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER REGISTROS
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"]==="SecRNR_listar_registros") {
	
	$usuario_id = $login ? $login['id'] : 0;

	$fecha_inicio     = $_POST["fecha_inicio"];// sorteo
	$fecha_fin        = $_POST["fecha_fin"];// sorteo
	$estado           = $_POST["estado"];//sorteo
	$premio           = $_POST["premio"];//sorteo
	$cliente_tipo     = $_POST["cliente_tipo"];//sorteo
	$cliente_texto    = $_POST["cliente_texto"];//sorteo
	$tienda_pago      = $_POST["tienda_pago"];//gestion

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set("America/Lima");

	$where_tienda_pago = "";
	$where_SORTEO_tienda_pago = "";
	if ($tienda_pago != "0"){ //PAGADO
		$where_tienda_pago = " AND l.id = " . $tienda_pago;
		$where_SORTEO_tienda_pago = " AND ew.paid_at IS NOT NULL ";
	}

	// QUERY
	$query_gestion = "
        SELECT 
			rp.num_doc,
			IFNULL(u.usuario, '') cajero,
			IFNULL(l.cc_id, '') cc,
			IFNULL(l.nombre, '') local,
			IFNULL(z.nombre, '') zona,
			rp.created_at fecha
		FROM tbl_registro_premios rp
		JOIN tbl_registro_premios_tipos pt ON pt.codigo = rp.tipo_codigo
		JOIN tbl_locales l ON l.id = rp.paid_local_id
		LEFT JOIN tbl_zonas z ON z.id = l.zona_id
		JOIN tbl_usuarios u ON u.id = rp.user_id
		WHERE 
			rp.tipo_registro = 3 
			AND IFNULL(rp.tipo_codigo, '') != '' 
            AND rp.created_at >= '2024-05-22 00:00:00' 
            AND rp.created_at >= '". $fecha_inicio ." 00:00:00' 
            AND rp.created_at <= '". $fecha_fin ." 23:59:59' 
            $where_tienda_pago
			";
	$list_gestion = $mysqli->query($query_gestion);
	$res_pagados = array();
	$res_pagados_num_doc = array();
	if($mysqli->error){
		$result["http_code"] = 400;
		$result["error"] = 'Error al consultar informacion de locales.';
		echo json_encode($result);
		exit;
	} else {
		while ($li_g = $list_gestion->fetch_assoc()) {
			$res_pagados[$li_g['num_doc']]['cajero'] = $li_g['cajero'];
			$res_pagados[$li_g['num_doc']]['cc'] = $li_g['cc'];
			$res_pagados[$li_g['num_doc']]['local'] = $li_g['local'];
			$res_pagados[$li_g['num_doc']]['zona'] = $li_g['zona'];
			$res_pagados[$li_g['num_doc']]['fecha'] = $li_g['fecha'];

			$res_pagados_num_doc[] = $li_g['num_doc'];
		}
	}

	$where_pagados_numdoc = "";
	if(strlen($where_SORTEO_tienda_pago)>0){
		$where_pagados_numdoc = " AND c.document_number IN (" . implode(",", $res_pagados_num_doc) . ") ";
	}
	

	// ********************************************************************
	// CONEXION
	$con_sorteo_host = env('DB_SORTEOS_AT_HOST');
	$con_sorteo_db   = env('DB_SORTEOS_AT_DATABASE');
	$con_sorteo_user = env('DB_SORTEOS_AT_USERNAME');
	$con_sorteo_pass = env('DB_SORTEOS_AT_PASSWORD');
	$mysqli_sorteo   = new mysqli($con_sorteo_host, $con_sorteo_user, $con_sorteo_pass, $con_sorteo_db, 3306);
	if (mysqli_connect_errno()) {
	    printf("Conexion fallida sorteos: %s\n", mysqli_connect_error());
	    exit();
	}
	$mysqli_sorteo->query("SET CHARACTER SET utf8");

	$where_fecha = " AND DATE(ew.created_at) >= '" . $fecha_inicio . "'  AND DATE(ew.created_at) <= '" . $fecha_fin . "' ";

	$where_estado = "";
	if($estado != "999"){
		if((int)$estado === 1){ //PAGADO
			$where_estado = " AND ew.paid_at IS NOT NULL ";
		} else {
			$where_estado = " AND ew.paid_at IS NULL ";
		}
	}

	$where_premio = "";
	if($premio != "0"){
		$where_premio = " AND ew.value = '" . $premio . "' ";
	}

	$where_cliente = "";
	if(strlen($cliente_texto)>1){
		if((int)$cliente_tipo === 1){
			$where_cliente = " AND ( UPPER(IFNULL(c.name, '')) LIKE '%" . $cliente_texto . "%' OR
									UPPER(IFNULL(c.last_name, '')) LIKE '%" . $cliente_texto . "%' ) ";
		}
		if((int)$cliente_tipo === 2){
			$where_cliente = " AND IFNULL(c.document_number, '') = '". $cliente_texto ."' ";
		}
	}

	$limit = '';
	if(isset($_POST["length"])) {
		if($_POST["length"] != -1) {
			$limit = ' LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
		}	
	}

	// QUERY
	$query_sorteo = "
		SELECT 
			ew.created_at fecha_registro,
			UPPER(CONCAT(c.name, ' ', c.last_name)) cliente,
			c.document_type tipo_doc,
			c.document_number num_doc,
			IFNULL(ew.type, '') premio_pagado,
			ew.amount monto_premio_pagado,
			IFNULL(ew.paid_at, '') fecha_premio_pagado,
			'' cc_tienda_pago,
			'' local_pago,
			'' zona_pago
		FROM sorteos.event_winner ew
			JOIN sorteos.clients c on c.id=ew.winner_id 
		WHERE ew.event_id = 804 
		"
		. $where_fecha
		. $where_estado
		. $where_SORTEO_tienda_pago
		. $where_pagados_numdoc
		. $where_premio
		. $where_cliente
		. " ORDER BY ew.id asc "
		. $limit;

	$list_sorteo = $mysqli_sorteo->query($query_sorteo);
	$res_sorteo = array();
	if($mysqli_sorteo->error){
	} else {
		while ($li = $list_sorteo->fetch_assoc()) {
			if(strlen($li['fecha_premio_pagado'])>1){
				if(isset($res_pagados[$li['num_doc']])){
					$li['cc_tienda_pago'] = $res_pagados[$li['num_doc']]['cc'];
					$li['local_pago'] = $res_pagados[$li['num_doc']]['local'];
					$li['zona_pago'] = $res_pagados[$li['num_doc']]['zona'];
					$li['fecha_premio_pagado'] = $res_pagados[$li['num_doc']]['fecha'];
				}
			}
			if(!(strlen($where_SORTEO_tienda_pago)>1 && strlen($li['local_pago'])==0)){
				$res_sorteo[] = $li;
			}
			
		}
	}



	$query_COUNT ="
		SELECT 
			count(1) cant
		FROM sorteos.event_winner ew
			JOIN sorteos.clients c on c.id=ew.winner_id 
		WHERE ew.event_id = 804 
		"
		. $where_fecha
		. $where_estado
		. $where_SORTEO_tienda_pago
		. $where_pagados_numdoc
		. $where_premio
		. $where_cliente
		;

	$list_query_COUNT=$mysqli_sorteo->query($query_COUNT);
	$list_registers_COUNT=array();
	if($mysqli_sorteo->error){
		$result["consulta_error"] = $mysqli_sorteo->error;
	} else {
		while ($li=$list_query_COUNT->fetch_assoc()) {
			$list_registers_COUNT[]=$li;
		}
	}

	if(count($res_sorteo)===0){
		$result["http_code"] = 204;
		$result["status"]    = "No hay registros.";
		$result["data"]      = $res_sorteo;
		$result["recordsTotal"]    = 0;
		$result["recordsFiltered"] = 0;
	} elseif(count($res_sorteo)>0){
		$result["http_code"]       = 200;
		$result["status"]          = "ok";
		$result["draw"]            = intval($_POST["draw"]);
		$result["recordsTotal"]    = $list_registers_COUNT[0]["cant"];
		$result["recordsFiltered"] = $list_registers_COUNT[0]["cant"];
		$result["data"]            = $res_sorteo;
	} else {
		$result["http_code"] = 400;
		$result["status"]    ="Ocurrió un error al consultar los registros.";
		$result["data"]      = $res_sorteo;
		$result["resumen"]   = $list_registers_COUNT;
		$result["recordsTotal"]    = 0;
		$result["recordsFiltered"] = 0;
	}
}

//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER TRANSACCIONES X CAJERO --> EXCEL
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "SecRNR_exportar_xls") {
	global $mysqli;
	$fecha_inicio     = $_POST["fecha_inicio"];
	$fecha_fin        = $_POST["fecha_fin"];
	$cliente_tipo     = $_POST["cliente_tipo"];
	$cliente_texto    = $_POST["cliente_texto"];
	$estado           = $_POST["estado"];
	$tienda_pago      = $_POST["tienda_pago"];
	$premio           = $_POST["premio"];



	$where_tienda_pago = "";
	$where_SORTEO_tienda_pago = "";
	if ($tienda_pago != "0"){ //PAGADO
		$where_tienda_pago = " AND l.id = " . $tienda_pago;
		$where_SORTEO_tienda_pago = " AND ew.paid_at IS NOT NULL ";
	}

	// QUERY
	$query_gestion = "
        SELECT 
			rp.num_doc,
			IFNULL(u.usuario, '') cajero,
			IFNULL(l.cc_id, '') cc,
			IFNULL(l.nombre, '') local,
			IFNULL(z.nombre, '') zona,
			rp.created_at fecha
		FROM tbl_registro_premios rp
		JOIN tbl_registro_premios_tipos pt ON pt.codigo = rp.tipo_codigo
		JOIN tbl_locales l ON l.id = rp.paid_local_id
		LEFT JOIN tbl_zonas z ON z.id = l.zona_id
		JOIN tbl_usuarios u ON u.id = rp.user_id
		WHERE 
			rp.tipo_registro = 3 
			AND IFNULL(rp.tipo_codigo, '') != '' 
            AND rp.created_at >= '2024-05-22 00:00:00' 
            AND rp.created_at >= '". $fecha_inicio ." 00:00:00' 
            AND rp.created_at <= '". $fecha_fin ." 23:59:59' 
            $where_tienda_pago
			";
	$list_gestion = $mysqli->query($query_gestion);
	$res_pagados = array();
	$res_pagados_num_doc = array();
	if($mysqli->error){
		$result["http_code"] = 400;
		$result["error"] = 'Error al consultar informacion de locales.';
		echo json_encode($result);
		exit;
	} else {
		while ($li_g = $list_gestion->fetch_assoc()) {
			$res_pagados[$li_g['num_doc']]['cajero'] = $li_g['cajero'];
			$res_pagados[$li_g['num_doc']]['cc'] = $li_g['cc'];
			$res_pagados[$li_g['num_doc']]['local'] = $li_g['local'];
			$res_pagados[$li_g['num_doc']]['zona'] = $li_g['zona'];
			$res_pagados[$li_g['num_doc']]['fecha'] = $li_g['fecha'];

			$res_pagados_num_doc[] = $li_g['num_doc'];
		}
	}

	$where_pagados_numdoc = "";
	if(strlen($where_SORTEO_tienda_pago)>0){
		$where_pagados_numdoc = " AND c.document_number IN (" . implode(",", $res_pagados_num_doc) . ") ";
	}
	

	// ********************************************************************
	// CONEXION
	$con_sorteo_host = env('DB_SORTEOS_AT_HOST');
	$con_sorteo_db   = env('DB_SORTEOS_AT_DATABASE');
	$con_sorteo_user = env('DB_SORTEOS_AT_USERNAME');
	$con_sorteo_pass = env('DB_SORTEOS_AT_PASSWORD');
	$mysqli_sorteo   = new mysqli($con_sorteo_host, $con_sorteo_user, $con_sorteo_pass, $con_sorteo_db, 3306);
	if (mysqli_connect_errno()) {
	    printf("Conexion fallida sorteos: %s\n", mysqli_connect_error());
	    exit();
	}
	$mysqli_sorteo->query("SET CHARACTER SET utf8");

	$where_fecha = " AND DATE(ew.created_at) >= '" . $fecha_inicio . "'  AND DATE(ew.created_at) <= '" . $fecha_fin . "' ";

	$where_estado = "";
	if($estado != "999"){
		if((int)$estado === 1){ //PAGADO
			$where_estado = " AND ew.paid_at IS NOT NULL ";
		} else {
			$where_estado = " AND ew.paid_at IS NULL ";
		}
	}

	$where_premio = "";
	if($premio != "0"){
		$where_premio = " AND ew.value = '" . $premio . "' ";
	}

	$where_cliente = "";
	if(strlen($cliente_texto)>1){
		if((int)$cliente_tipo === 1){
			$where_cliente = " AND ( UPPER(IFNULL(c.name, '')) LIKE '%" . $cliente_texto . "%' OR
									UPPER(IFNULL(c.last_name, '')) LIKE '%" . $cliente_texto . "%' ) ";
		}
		if((int)$cliente_tipo === 2){
			$where_cliente = " AND IFNULL(c.document_number, '') = '". $cliente_texto ."' ";
		}
	}


	// QUERY
	$query_sorteo = "
		SELECT 
			ew.created_at fecha_registro,
			UPPER(CONCAT(c.name, ' ', c.last_name)) cliente,
			c.document_type tipo_doc,
			c.document_number num_doc,
			IFNULL(ew.type, '') premio_pagado,
			ew.amount monto_premio_pagado,
			IFNULL(ew.paid_at, '') fecha_premio_pagado,
			'' cc_tienda_pago,
			'' local_pago,
			'' zona_pago
		FROM sorteos.event_winner ew
			JOIN sorteos.clients c on c.id=ew.winner_id 
		WHERE ew.event_id = 804 
		"
		. $where_fecha
		. $where_estado
		. $where_SORTEO_tienda_pago
		. $where_pagados_numdoc
		. $where_premio
		. $where_cliente
		. " ORDER BY ew.id asc "
		;

	$list_sorteo = $mysqli_sorteo->query($query_sorteo);
	$res_sorteo = array();
	if($mysqli_sorteo->error){
	} else {
		while ($li = $list_sorteo->fetch_assoc()) {
			if(strlen($li['fecha_premio_pagado'])>1){
				if(isset($res_pagados[$li['num_doc']])){
					$li['cc_tienda_pago'] = $res_pagados[$li['num_doc']]['cc'];
					$li['local_pago'] = $res_pagados[$li['num_doc']]['local'];
					$li['zona_pago'] = $res_pagados[$li['num_doc']]['zona'];
					$li['fecha_premio_pagado'] = $res_pagados[$li['num_doc']]['fecha'];
				}
			}
			if(!(strlen($where_SORTEO_tienda_pago)>1 && strlen($li['local_pago'])==0)){
				$res_sorteo[] = $li;
			}
		}
	}


		$result_data = $res_sorteo;
		

		$headers = [
			"fecha_registro" => "Registro",
			"cliente" => "Cliente",
			"tipo_doc" => "Tipo Documento",
			"num_doc" => "Número Documento",
			"premio_pagado" => "Premio",
			"monto_premio_pagado" => "Monto Premio",
			"fecha_premio_pagado" => "Fecha Pago",
			"cc_tienda_pago" => "CC Tienda Pago",
			"local_pago" => "Tienda Pago",
			"zona_pago" => "Zona Pago"
		];
		array_unshift($result_data, $headers);

		ini_set('display_errors', 0);
		ini_set('display_startup_errors', 0);
		error_reporting(0);

		require_once '../phpexcel/classes/PHPExcel.php';
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getActiveSheet()->fromArray($result_data, null, 'A1');
		$date = new DateTime();
		$file_title = "reporte_sorteo_nuevos_registros_" . $date->getTimestamp();

		if (!file_exists('/var/www/html/export/files_exported/reporte_premios/')) {
			mkdir('/var/www/html/export/files_exported/reporte_premios/', 0777, true);
		}

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . $file_title . '.xls"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$excel_path = '/var/www/html/export/files_exported/reporte_premios/' . $file_title . '.xls';
		$excel_path_download = '/export/files_exported/reporte_premios/' . $file_title . '.xls';
		$url = $file_title . '.xls';

		try {
			$objWriter->save($excel_path);
		} catch (PHPExcel_Writer_Exception $e) {
			echo json_encode(["error" => $e]);
			exit;
		}

		$insert_cmd = "INSERT INTO tbl_exported_files (url,tipo,ext,size,fecha_registro,usuario_id)";
		$insert_cmd .= " VALUES ('" . $url . "','excel','xls','" . filesize($excel_path) . "','" . date("Y-m-d h:i:s") . "','" . $login["id"] . "')";
		$mysqli->query($insert_cmd);

		echo json_encode(array(
			"path" => $excel_path_download,
			"url" => $file_title . '.xls',
			"tipo" => "excel",
			"ext" => "xls",
			"size" => filesize($excel_path),
			"fecha_registro" => date("d-m-Y h:i:s"),
			"sql" => $insert_cmd
		));
		exit;
	
}

echo json_encode($result);
?>