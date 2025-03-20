<?php
$result=array();
include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);

//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER TRANSACCIONES X CAJERO
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"]==="listar_registros") {
	$cliente_tipo              = $_POST["cliente_tipo"];
	$cliente_texto             = $_POST["cliente_texto"];

	$where_cliente = "";
	if(strlen($cliente_texto)>1){
		if((int)$cliente_tipo === 1){ // Por nombres
			$where_cliente = " AND CONCAT( IFNULL(c.nombre, ''), ' ', IFNULL(c.apellido_paterno, ''), ' ', IFNULL(c.apellido_materno, '') ) LIKE '%". $cliente_texto ."%' ";
		}
		if((int)$cliente_tipo === 2){ // Por num doc
			$where_cliente = " AND IFNULL(c.num_doc, '') = '". $cliente_texto ."' ";
		}
		if((int)$cliente_tipo === 3){ // Por web-id
			$where_cliente = " AND IFNULL(c.web_id, '') = '". $cliente_texto ."' ";
		}
	}

	$nombre_busqueda = "";
	if(isset($_POST["search"]["value"])) {
		$busqueda_nombre = $_POST["search"]["value"];
		if ($busqueda_nombre !="") {
			$nombre_busqueda = '
			AND (
			c.nombre LIKE "%'.$_POST["search"]["value"].'%"
			OR c.num_doc LIKE "%'.$_POST["search"]["value"].'%"
			OR c.web_id LIKE "%'.$_POST["search"]["value"].'%"
			OR CONCAT( IFNULL(c.nombre, ""), " ", IFNULL(c.apellido_paterno, ""), " ", IFNULL(c.apellido_materno, "") ) LIKE "%'.$_POST["search"]["value"].'%"
			OR e.label LIKE "%'.$_POST["search"]["value"].'%"';
			$nombre_busqueda .= ')';
		}
	}

	$order = "";
	$column = array(
		1=>"c.num_doc",
		2=>"c.web_id",
		3=>"c.nombre",
		4=>"e.label"
		);

	if(isset($_POST["order"])) {
		if (array_key_exists($_POST['order']['0']['column'],$column)) {
			$order = ' ORDER BY '.$column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' ';
		} else {
			$order = ' ORDER BY ORDER c.id ASC, e.id ASC';
		}
	} else {
		$order = ' ORDER BY c.id ASC, e.id ASC';
	}

	if(isset($_POST["length"])) {
		if($_POST["length"] != -1) {
			$limit = ' LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
		}	
	}

	$query_1 ="
		SELECT 
		    IFNULL(c.num_doc, '') num_doc,
		    IFNULL(c.web_id, '') web_id,
			CONCAT( IFNULL(c.nombre, ''), ' ', IFNULL(c.apellido_paterno, ''), ' ', IFNULL(c.apellido_materno, '') ) cliente,
			IFNULL(e.label, '') label
		FROM tbl_televentas_clientes_etiqueta ce
		INNER JOIN tbl_televentas_clientes c ON ce.client_id = c.id
		INNER JOIN tbl_televentas_etiqueta e ON ce.etiqueta_id = e.id
		WHERE 
			ce.status = 1 
		"
		. $where_cliente
		. $nombre_busqueda
		. $order
		. $limit;

	$result["consulta_query"] = $query_1;
	$list_query=$mysqli->query($query_1);
	$list_regis=array();
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li=$list_query->fetch_assoc()) {
			$list_regis[]=$li;
		}
	}

	// Cantidades
	$query_COUNT ="
		SELECT 
		    COUNT(*) cant
		FROM tbl_televentas_clientes_etiqueta ce
		INNER JOIN tbl_televentas_clientes c ON ce.client_id = c.id
		INNER JOIN tbl_televentas_etiqueta e ON ce.etiqueta_id = e.id
		WHERE 
			ce.status = 1 
		"
		. $where_cliente;

	$result["consulta_query_COUNT"] = $query_COUNT;
	$list_query_COUNT=$mysqli->query($query_COUNT);
	$list_regis_count=array();
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li=$list_query_COUNT->fetch_assoc()) {
			$list_regis_count[]=$li;
		}
	}

	if(count($list_regis)===0){
		$result["http_code"] = 204;
		$result["status"]    = "No hay registros.";
		$result["data"]      = $list_regis;
	} elseif(count($list_regis)>0){
		$result["http_code"]       = 200;
		$result["status"]          = "ok";
		$result["draw"]            = intval($_POST["draw"]);
		$result["recordsTotal"]    = $list_regis_count[0]["cant"];
		$result["recordsFiltered"] = $list_regis_count[0]["cant"];
		$result["data"]            = $list_regis;
	} else {
		$result["http_code"] = 400;
		$result["status"]    ="Ocurrió un error al consultar transacciones.";
		$result["data"]      = $list_regis;
		$result["resumen"]   = $list_regis_count;
	}
}

//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER TRANSACCIONES X CAJERO --> EXCEL
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"]==="listar_export_xls") {
	global $mysqli;
	$cliente_tipo              = $_POST["cliente_tipo"];
	$cliente_texto             = $_POST["cliente_texto"];

	$where_cliente = "";
	if(strlen($cliente_texto)>1){
		if((int)$cliente_tipo === 1){ // Por nombres
			$where_cliente = " AND CONCAT( IFNULL(c.nombre, ''), ' ', IFNULL(c.apellido_paterno, ''), ' ', IFNULL(c.apellido_materno, '') ) LIKE '%". $cliente_texto ."%' ";
		}
		if((int)$cliente_tipo === 2){ // Por num doc
			$where_cliente = " AND IFNULL(c.num_doc, '') = '". $cliente_texto ."' ";
		}
		if((int)$cliente_tipo === 3){ // Por web-id
			$where_cliente = " AND IFNULL(c.web_id, '') = '". $cliente_texto ."' ";
		}
	}

	$query_1 ="
		SELECT 
		    IFNULL(c.num_doc, '') num_doc,
		    IFNULL(c.web_id, '') web_id,
			CONCAT( IFNULL(c.nombre, ''), ' ', IFNULL(c.apellido_paterno, ''), ' ', IFNULL(c.apellido_materno, '') ) cliente,
			IFNULL(e.label, '') label
		FROM tbl_televentas_clientes_etiqueta ce
		INNER JOIN tbl_televentas_clientes c ON ce.client_id = c.id
		INNER JOIN tbl_televentas_etiqueta e ON ce.etiqueta_id = e.id
		WHERE 
			ce.status = 1 
		"
		. $where_cliente
		. " ORDER BY c.id ASC, e.id ASC";

	$list_query = $mysqli->query($query_1);
	$result_data = array();
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["consulta_query"] = $query_1;
		$result["error"] = 'Export error: ' . $mysqli->error;
		echo json_encode($result);
		exit;
	} else {
		while ($li=$list_query->fetch_assoc()) {
			$result_data[]=$li;
		}

		$headers = [
			"num_doc" => "NUM. DOCUMENTO",
			"web_id" => "WEB ID",
			"cliente" => "CLIENTE",
			"label" => "ETIQUETA"
		];

		array_unshift($result_data, $headers);

		ini_set('display_errors', 0);
		ini_set('display_startup_errors', 0);
		error_reporting(0);

		require_once '../phpexcel/classes/PHPExcel.php';
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getActiveSheet()->fromArray($result_data, null, 'A1');
		$date = new DateTime();
		$file_title = "reporte_etiquetas_por_cliente_" . $date->getTimestamp();

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



}

echo json_encode($result);