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
if (isset($_POST["accion"]) && $_POST["accion"]==="SecRptBal_listar_registros") {
	
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$fecha                     = $_POST["fecha"];
	$hora                      = $_POST["hora"];
	$cliente_tipo              = $_POST["cliente_tipo"];
	$cliente_texto             = $_POST["cliente_texto"];

	$where_cliente = "";
	if(strlen($cliente_texto)>1){
		if((int)$cliente_tipo === 1){ // Por nombres
			$where_cliente = " AND CONCAT( IFNULL(cli.nombre, ''), ' ', IFNULL(cli.apellido_paterno, ''), ' ', IFNULL(cli.apellido_materno, '') ) LIKE '%". $cliente_texto ."%' ";
		}
		if((int)$cliente_tipo === 2){ // Por num doc
			$where_cliente = " AND IFNULL(cli.num_doc, '') = '". $cliente_texto ."' ";
		}
		if((int)$cliente_tipo === 3){ // Por web-id
			$where_cliente = " AND IFNULL(cli.web_id, '') = '". $cliente_texto ."' ";
		}
		if((int)$cliente_tipo === 4){ // Por celular
			$where_cliente = " AND IFNULL(cli.telefono, '') = '". $cliente_texto ."' ";
		}
	}

	$nombre_busqueda = "";
	if(isset($_POST["search"]["value"])) {
		$busqueda_nombre = $_POST["search"]["value"];
		if ($busqueda_nombre !="") {
			$nombre_busqueda = '
			 AND (
			cli.num_doc LIKE "%'.$_POST["search"]["value"].'%"
			cli.web_id LIKE "%'.$_POST["search"]["value"].'%"
			cli.nombre LIKE "%'.$_POST["search"]["value"].'%"
			cli.apellido_paterno LIKE "%'.$_POST["search"]["value"].'%"
			cli.apellido_materno LIKE "%'.$_POST["search"]["value"].'%"';
			$nombre_busqueda .= ')';
		}
	}

	$order = "";
	$column = array(
		1=>"cli.cliente_id",
		2=>"cli.nombre",
		3=>"bal.balance"
		);
	if(isset($_POST["order"])) {
		if (array_key_exists($_POST['order']['0']['column'],$column)) {
			$order = ' ORDER BY '.$column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' ';
		} else {
			$order = ' ORDER BY cli.id ASC ';
		}
	} else {
		$order = ' ORDER BY cli.id ASC ';
	}

	if(isset($_POST["length"])) {
		if($_POST["length"] != -1) {
			$limit = ' LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
		}	
	}

	// QUERY
	$query_1 ="
		SELECT 
			cli.id cliente_id,
			IFNULL(cli.num_doc,'') num_doc,
			CONCAT(IFNULL(cli.nombre,''), ' ', IFNULL(cli.apellido_paterno,''), ' ', IFNULL(cli.apellido_materno,'')) cliente,
			IFNULL(cb.balance, 0) balance,
			IFNULL(cb.balance_retirable, 0) balance_retirable
		FROM tbl_televentas_clientes cli
		INNER JOIN tbl_televentas_res_fecha_cliente_balance cb ON cb.cliente_id = cli.id
		WHERE 
			TRIM(CONCAT
				(IFNULL(cli.nombre,''), ' ', IFNULL(cli.apellido_paterno,''), ' ', IFNULL(cli.apellido_materno,''),IFNULL(cli.num_doc,''))
				) <> '' 
		AND cb.fecha = '" . $fecha . "' 
		AND LENGTH(cli.num_doc) != 4 "
		.$where_cliente
		.$nombre_busqueda
		. " AND cb.balance > 0 "
		.$order
		.$limit;

	//$result["consulta_query"] = $query_1;
	$list_query=$mysqli->query($query_1);
	$list_transaccion=array();
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li=$list_query->fetch_assoc()) {
			$list_transaccion[]=$li;
		}
	}

	// Cantidades
	$query_COUNT ="
		SELECT 
			COUNT(*) cant
		FROM tbl_televentas_clientes cli
		INNER JOIN tbl_televentas_res_fecha_cliente_balance cb ON cb.cliente_id = cli.id
		WHERE 
		TRIM(CONCAT
			(IFNULL(cli.nombre,''), ' ', IFNULL(cli.apellido_paterno,''), ' ', IFNULL(cli.apellido_materno,''),IFNULL(cli.num_doc,''))
		    ) <> '' 
		AND cb.fecha = '" . $fecha . "' 
		AND (IFNULL((
			SELECT cb.balance
			FROM tbl_televentas_res_fecha_cliente_balance cb
			WHERE cb.cliente_id = cli.id AND cb.fecha = '" . $fecha . "'
			ORDER BY cb.id DESC LIMIT 1
		),0) +
		IFNULL((
			SELECT cb.balance_retirable
			FROM tbl_televentas_res_fecha_cliente_balance cb
			WHERE cb.cliente_id = cli.id AND cb.fecha = '" . $fecha . "'
			ORDER BY cb.id DESC LIMIT 1
		),0)) > 0
		AND LENGTH(cli.num_doc) != 4
		".$where_cliente."
		".$order ;
	$list_query_COUNT=$mysqli->query($query_COUNT);
	$list_transaccion_COUNT=array();
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li=$list_query_COUNT->fetch_assoc()) {
			$list_transaccion_COUNT[]=$li;
		}
	}

	if(count($list_transaccion)===0){
		$result["http_code"] = 204;
		$result["status"] ="No hay transacciones.";
		$result["data"] =$list_transaccion;
	} elseif(count($list_transaccion)>0){
		$result["draw"] = intval($_POST["draw"]);
		$result["recordsTotal"] = $list_transaccion_COUNT[0]["cant"];
		$result["recordsFiltered"] = $list_transaccion_COUNT[0]["cant"];
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["data"] = $list_transaccion;
	} else {
		$result["http_code"] = 400;
		$result["status"] ="Ocurrió un error al consultar transacciones.";
		$result["data"] =$list_transaccion;
		$result["resumen"] = $list_transaccion_COUNT;
	}
}

//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER TRANSACCIONES X CAJERO --> EXCEL
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"]==="SecRptBal_exportar_xls") {
	global $mysqli;
	$usuario_id = $login ? $login['id'] : 0;

	$fecha                     = $_POST["fecha"];
	$hora                      = $_POST["hora"];
	$cliente_tipo              = $_POST["cliente_tipo"];
	$cliente_texto             = $_POST["cliente_texto"];

	$where_cliente = "";
	if(strlen($cliente_texto)>1){
		if((int)$cliente_tipo === 1){ // Por nombres
			$where_cliente = " AND CONCAT(IFNULL(cli.nombre,''), ' ', IFNULL(cli.apellido_paterno,''), ' ', IFNULL(cli.apellido_materno,'')) LIKE '%". $cliente_texto ."%' ";
		}
		if((int)$cliente_tipo === 2){ // Por num doc
			$where_cliente = " AND IFNULL(cli.num_doc, '') = '". $cliente_texto ."' ";
		}
		if((int)$cliente_tipo === 3){ // Por web-id
			$where_cliente = " AND IFNULL(cli.web_id, '') = '". $cliente_texto ."' ";
		}
		if((int)$cliente_tipo === 4){ // Por celular
			$where_cliente = " AND IFNULL(cli.telefono, '') = '". $cliente_texto ."' ";
		}
	}

	$nombre_busqueda = "";
	if(isset($_POST["search"]["value"])) {
		$busqueda_nombre = $_POST["search"]["value"];
		if ($busqueda_nombre !="") {
			$nombre_busqueda = '
			AND (
			cli.nombre LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.apellido_paterno LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.apellido_materno LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.num_doc LIKE "%'.$_POST["search"]["value"].'%" ';
			$nombre_busqueda .= ')';
		}
	}

	// QUERY
	$query_1 ="
		SELECT 
			IFNULL(cli.num_doc,'') num_doc,
			CONCAT(IFNULL(cli.nombre,''), ' ', IFNULL(cli.apellido_paterno,''), ' ', IFNULL(cli.apellido_materno,'')) cliente,
			IFNULL(cb.balance, 0) balance,
			IFNULL(cb.balance_retirable, 0) balance_retirable
		FROM tbl_televentas_clientes cli
		INNER JOIN tbl_televentas_res_fecha_cliente_balance cb ON cb.cliente_id = cli.id
		WHERE 
		TRIM(CONCAT
			(IFNULL(cli.nombre,''), ' ', IFNULL(cli.apellido_paterno,''), ' ', IFNULL(cli.apellido_materno,''),IFNULL(cli.num_doc,''))
		    ) <> '' 
		AND cb.fecha = '" . $fecha . "' 
		AND LENGTH(cli.num_doc) != 4 "
		.$where_cliente
		.$nombre_busqueda
		. " AND cb.balance > 0 ";
	

	$list_query = $mysqli->query($query_1);
	$result_data = array();

	if ($mysqli->error) {
		//$result["consulta_error"] = $mysqli->error;
		echo json_encode([
			"error" => "Export error"
		]);
		exit;
	} else {
		
		while ($li = $list_query->fetch_assoc()) {
			$result_data[] = $li;
		}

		$headers = [
			"num_doc" => "DNI",
			"cliente" => "Cliente",
			"balance" => "Balance",
			"balance_retirable" => "Retiro Disponible"
		];
		array_unshift($result_data, $headers);

		ini_set('display_errors', 0);
		ini_set('display_startup_errors', 0);
		error_reporting(0);

		require_once '../phpexcel/classes/PHPExcel.php';
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getActiveSheet()->fromArray($result_data, null, 'A1');
		$date = new DateTime();
		$file_title = "reporte_balance_" . $date->getTimestamp();

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
			echo json_encode(["error" => $e, "query" => $query_1]);
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

if (isset($_POST["accion"]) && $_POST["accion"]==="SecRptBal_listar_resumen") {
	
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$fecha                     = $_POST["fecha"];
	$hora                      = $_POST["hora"];
	$cliente_tipo              = $_POST["cliente_tipo"];
	$cliente_texto             = $_POST["cliente_texto"];


	$where_users_test="";
	if((int)$area_id !== 6) {// diferente de sistemas
		$where_users_test="	
			AND IFNULL(tra.web_id, '') not in ('3333200', '71938219') 
			AND tra.user_id not in (1, 249, 250, 2572, 3028) 
		";
		if((int)$busqueda_tipo_transaccion === 1) {// si es deposito
			$where_users_test="	
				AND tra.user_id not in (1, 249, 250, 2572, 3028) 
			";
		}
	}

	$where_cliente = "";
	if(strlen($cliente_texto)>1){
		if((int)$cliente_tipo === 1){ // Por nombres
			$where_cliente = " AND CONCAT( IFNULL(cli.nombre, ''), ' ', IFNULL(cli.apellido_paterno, ''), ' ', IFNULL(cli.apellido_materno, '') ) LIKE '%". $cliente_texto ."%' ";
		}
		if((int)$cliente_tipo === 2){ // Por num doc
			$where_cliente = " AND IFNULL(cli.num_doc, '') = '". $cliente_texto ."' ";
		}
		if((int)$cliente_tipo === 3){ // Por web-id
			$where_cliente = " AND IFNULL(cli.web_id, '') = '". $cliente_texto ."' ";
		}
		if((int)$cliente_tipo === 4){ // Por celular
			$where_cliente = " AND IFNULL(cli.telefono, '') = '". $cliente_texto ."' ";
		}
	}

	$nombre_busqueda = "";
	if(isset($_POST["search"]["value"])) {
		$busqueda_nombre = $_POST["search"]["value"];
		if ($busqueda_nombre !="") {
			$nombre_busqueda = '
			 AND (
			cli.num_doc LIKE "%'.$_POST["search"]["value"].'%"
			cli.web_id LIKE "%'.$_POST["search"]["value"].'%"
			cli.nombre LIKE "%'.$_POST["search"]["value"].'%"
			cli.apellido_paterno LIKE "%'.$_POST["search"]["value"].'%"
			cli.apellido_materno LIKE "%'.$_POST["search"]["value"].'%"';
			$nombre_busqueda .= ')';
		}
	}
	
	// Cantidades
	$query_COUNT ="
		SELECT
			IFNULL(SUM(1), 0) AS cant_clientes,
			IFNULL(SUM(IFNULL(cb.balance, 0)), 0) total_balance,
			IFNULL(SUM(IFNULL(cb.balance_retirable, 0)), 0) total_retiro
		FROM tbl_televentas_res_fecha_cliente_balance cb
		LEFT JOIN tbl_televentas_clientes cli ON cb.cliente_id = cli.id
		WHERE fecha = '" . $fecha . "' 
		AND LENGTH(cli.num_doc) != 4 "
		. $where_cliente;

	$list_query_COUNT=$mysqli->query($query_COUNT);
	$list_transaccion_COUNT=array();
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li=$list_query_COUNT->fetch_assoc()) {
			$list_transaccion_COUNT[]=$li;
		}
	}

	if(count($list_transaccion_COUNT)===0){
		$result["http_code"] = 204;
		$result["status"] ="No hay transacciones.";
		$result["resumen"] =$list_transaccion_COUNT;
	} elseif(count($list_transaccion_COUNT)>0){
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["resumen"] = $list_transaccion_COUNT;
	} else {
		$result["http_code"] = 400;
		$result["status"] ="Ocurrió un error al consultar transacciones.";
		$result["resumen"] = $list_transaccion_COUNT;
	}
}

echo json_encode($result);
?>