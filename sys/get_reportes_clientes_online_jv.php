<?php
$result = array();
include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);

if (isset($_POST["accion"]) && $_POST["accion"]==="SecRepCliOn_listar_clientes_online_jv") {
	
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id   = $login ? $login['cargo_id'] : 0;
	$area_id    = $login ? $login['area_id'] : 0;

	$estado = $_POST["estado"];

	$where_users_test = "";
	if((int)$area_id !== 6) {// diferente de sistemas
		$where_users_test = " AND IFNULL(c.web_id, '') not in ('3333200', '71938219') ";
	}

	$having_estado = "";
	if($estado != 0){
		if($estado == 1){
			$having_estado = " HAVING estado = 'ONLINE'";
		} else if($estado == 2){
			$having_estado = " HAVING estado = 'JUGANDO'";
		}
	}
	
	$nombre_busqueda = "";
	if(isset($_POST["search"]["value"])) {
		$busqueda_nombre = $_POST["search"]["value"];
		if ($busqueda_nombre !="") {
			$nombre_busqueda = '
			AND (
			IFNULL(CONCAT( c.nombre, " ", IFNULL( c.apellido_paterno, "" ), " ", IFNULL( c.apellido_materno, "" ) ), "") LIKE "%'.$_POST["search"]["value"].'%"
			OR c.num_doc LIKE "%'.$_POST["search"]["value"].'%"
			OR cr.created_at LIKE "%'.$_POST["search"]["value"].'%"';
			$nombre_busqueda .= ')';
		}
	}

	$order = "";
	$column = array(
		1=>"cr.id",
		2=>"c.nombre",
		3=>"c.num_doc"
		);
	if(isset($_POST["order"])) {
		if (array_key_exists($_POST['order']['0']['column'],$column)) {
			$order = ' ORDER BY '.$column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' ';
		} else {
			$order = ' ORDER BY cr.id DESC';
		}
	} else {
		$order = ' ORDER BY cr.id DESC';
	}
	$limit = "";
	if(isset($_POST["length"])) {
		if($_POST["length"] != -1) {
			$limit = ' LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
		}	
	}

	$limite_online = SecRepCliOn_get_parametros('limit_minutes_client_online');
	$limite_jugando = SecRepCliOn_get_parametros('limit_minutes_client_jugando');

	$query ="
		SELECT 
			CASE
				WHEN 
					TIMESTAMPDIFF(MINUTE,cr.created_at,NOW()) >= 0  && 
					TIMESTAMPDIFF(MINUTE,cr.created_at,NOW()) <= " . $limite_online . " 
					&& cr.method = 'login'
				THEN 'ONLINE'
				WHEN 
					TIMESTAMPDIFF(MINUTE,cr.created_at,NOW()) >= 0 && 
					TIMESTAMPDIFF(MINUTE,cr.created_at,NOW()) <= " . $limite_jugando . " && 
					cr.method = 'sell' 
				THEN 'JUGANDO'
			END estado,
		    cr.created_at ultimo_registro, 
		    cr.client_id,
		    IFNULL(CONCAT( c.nombre, ' ', IFNULL( c.apellido_paterno, '' ), ' ', IFNULL( c.apellido_materno, '' ) ), '') AS cliente,
			IFNULL(c.num_doc, '') num_doc
		FROM tbl_televentas_api_calimaco_response cr
		INNER JOIN tbl_televentas_clientes c ON cr.client_id = c.id
		INNER JOIN (
			SELECT 
				ccr.client_id, MAX(ccr.id) max_id
		    FROM tbl_televentas_api_calimaco_response ccr
		    WHERE 
		    	ccr.method in ('sell', 'login')
		    	AND 
				(
					(TIMESTAMPDIFF(MINUTE,ccr.created_at,NOW()) >= 0 AND TIMESTAMPDIFF(MINUTE,ccr.created_at,NOW()) <= " . $limite_online . " AND ccr.method = 'login') OR 
					(TIMESTAMPDIFF(MINUTE,ccr.created_at,NOW()) >= 0 AND TIMESTAMPDIFF(MINUTE,ccr.created_at,NOW()) <= " . $limite_jugando . " AND ccr.method = 'sell') 
				)
		    GROUP BY ccr.client_id
		) AS A ON cr.id = A.max_id
		GROUP BY cr.client_id  " 
		. $having_estado
		. $order
		. $limit;

	//$result["consulta_query"] = $query;
	$list_query = $mysqli->query($query);
	$list_registers = array();
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li=$list_query->fetch_assoc()) {
			$list_registers[]=$li;
		}
	}

	$query_COUNT ="
		SELECT COUNT(*) cant
		FROM
			(SELECT 
				CASE
					WHEN 
						TIMESTAMPDIFF(MINUTE,cr.created_at,NOW()) >= 0  && 
						TIMESTAMPDIFF(MINUTE,cr.created_at,NOW()) <= " . $limite_online . " 
						&& cr.method = 'login'
					THEN 'ONLINE'
					WHEN 
						TIMESTAMPDIFF(MINUTE,cr.created_at,NOW()) >= 0 && 
						TIMESTAMPDIFF(MINUTE,cr.created_at,NOW()) <= " . $limite_jugando . " && 
						cr.method = 'sell' 
					THEN 'JUGANDO'
				END estado
			FROM tbl_televentas_api_calimaco_response cr
			INNER JOIN tbl_televentas_clientes c ON cr.client_id = c.id
			INNER JOIN (
				SELECT 
					ccr.client_id, MAX(ccr.id) max_id
			    FROM tbl_televentas_api_calimaco_response ccr
			    WHERE 
			    	ccr.method in ('sell', 'login')
			    	AND 
					(
						(TIMESTAMPDIFF(MINUTE,ccr.created_at,NOW()) >= 0 AND TIMESTAMPDIFF(MINUTE,ccr.created_at,NOW()) <= " . $limite_online . " AND ccr.method = 'login') OR 
						(TIMESTAMPDIFF(MINUTE,ccr.created_at,NOW()) >= 0 AND TIMESTAMPDIFF(MINUTE,ccr.created_at,NOW()) <= " . $limite_jugando . " AND ccr.method = 'sell') 
					)
			    GROUP BY ccr.client_id
			) AS A ON cr.id = A.max_id
			GROUP BY cr.client_id  " 
			. $having_estado
			. $order . "
			) AS B";

	//$result["consulta_query_COUNT"] = $query_COUNT;
	$list_query_COUNT = $mysqli->query($query_COUNT);
	$list_registers_COUNT = array();
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li=$list_query_COUNT->fetch_assoc()) {
			$list_registers_COUNT[]=$li;
		}
	}

	if(count($list_registers)===0){
		$result["http_code"] = 204;
		$result["status"]    = "No hay transacciones.";
		$result["data"]      = $list_registers;
	} elseif(count($list_registers)>0){
		$result["http_code"]       = 200;
		$result["status"]          = "ok";
		$result["draw"]            = intval($_POST["draw"]);
		$result["recordsTotal"]    = $list_registers_COUNT[0]["cant"];
		$result["recordsFiltered"] = $list_registers_COUNT[0]["cant"];
		$result["data"]            = $list_registers;
	} else {
		$result["http_code"] = 400;
		$result["status"]          = "Ocurrió un error al consultar transacciones.";
		$result["data"]            = $list_registers;
		$result["resumen"]         = $list_registers_COUNT;
		$result["draw"]            = intval($_POST["draw"]);
		$result["recordsTotal"]    = '0';
		$result["recordsFiltered"] = '0';
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="SecRepCliOn_export_clientes_online_jv_xls") {
	global $mysqli;
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$estado = $_POST["estado"];

	$where_users_test = "";
	if((int)$area_id !== 6) {// diferente de sistemas
		$where_users_test = " AND IFNULL(c.web_id, '') not in ('3333200', '71938219') ";
	}

	$having_estado = "";
	if($estado != 0){
		if($estado == 1){
			$having_estado = " HAVING estado = 'ONLINE'";
		} else if($estado == 2){
			$having_estado = " HAVING estado = 'JUGANDO'";
		}
	}

	$limite_online = SecRepCliOn_get_parametros('limit_minutes_client_online');
	$limite_jugando = SecRepCliOn_get_parametros('limit_minutes_client_jugando');

	$query_1 ="
		SELECT 
			CASE
				WHEN 
					TIMESTAMPDIFF(MINUTE,cr.created_at,NOW()) >= 0  && 
					TIMESTAMPDIFF(MINUTE,cr.created_at,NOW()) <= " . $limite_online . " 
					&& cr.method = 'login'
				THEN 'ONLINE'
				WHEN 
					TIMESTAMPDIFF(MINUTE,cr.created_at,NOW()) >= 0 && 
					TIMESTAMPDIFF(MINUTE,cr.created_at,NOW()) <= " . $limite_jugando . " && 
					cr.method = 'sell' 
				THEN 'JUGANDO'
			END estado,
		    IFNULL(CONCAT( c.nombre, ' ', IFNULL( c.apellido_paterno, '' ), ' ', IFNULL( c.apellido_materno, '' ) ), '') AS cliente,
			IFNULL(c.num_doc, '') num_doc,
		    cr.created_at ultimo_registro
		FROM tbl_televentas_api_calimaco_response cr
		INNER JOIN tbl_televentas_clientes c ON cr.client_id = c.id
		INNER JOIN (
			SELECT 
				ccr.client_id, MAX(ccr.id) max_id
		    FROM tbl_televentas_api_calimaco_response ccr
		    WHERE 
		    	ccr.method in ('sell', 'login')
		    	AND 
				(
					(TIMESTAMPDIFF(MINUTE,ccr.created_at,NOW()) >= 0 AND TIMESTAMPDIFF(MINUTE,ccr.created_at,NOW()) <= " . $limite_online . " AND ccr.method = 'login') OR 
					(TIMESTAMPDIFF(MINUTE,ccr.created_at,NOW()) >= 0 AND TIMESTAMPDIFF(MINUTE,ccr.created_at,NOW()) <= " . $limite_jugando . " AND ccr.method = 'sell') 
				)
		    GROUP BY ccr.client_id
		) AS A ON cr.id = A.max_id
		GROUP BY cr.client_id "
		. $having_estado;

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
			"estado" => "Estado",
			"cliente" => "Cliente",
			"num_doc" => "Numero de Documento",
			"ultimo_registro" => "Último registro"
		];
		array_unshift($result_data, $headers);

		ini_set('display_errors', 0);
		ini_set('display_startup_errors', 0);
		error_reporting(0);

		require_once '../phpexcel/classes/PHPExcel.php';
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getActiveSheet()->fromArray($result_data, null, 'A1');
		$date = new DateTime();
		$file_title = "reporte_clientes_juevos_virtuales" . $date->getTimestamp();

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

if (isset($_POST["accion"]) && $_POST["accion"]==="SecRepCliOn_cantidades") {
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id   = $login ? $login['cargo_id'] : 0;
	$area_id    = $login ? $login['area_id'] : 0;

	$where_users_test = "";
	if((int)$area_id !== 6) {// diferente de sistemas
		$where_users_test = " AND IFNULL(c.web_id, '') not in ('3333200', '71938219') ";
	}

	$result["cant_online"] = 0;
	$result["cant_jugando"] = 0;

	$limite_online = SecRepCliOn_get_parametros('limit_minutes_client_online');
	$limite_jugando = SecRepCliOn_get_parametros('limit_minutes_client_jugando');

	$query ="
		SELECT 
			IFNULL(SUM(IF(data.estado = 'ONLINE', 1, 0)), 0) AS cant_online,
			IFNULL(SUM(IF(data.estado = 'JUGANDO', 1, 0)), 0) AS cant_jugando
		FROM (
			SELECT 
				CASE
					WHEN 
						TIMESTAMPDIFF(MINUTE,cr.created_at,NOW()) >= 0  AND 
						TIMESTAMPDIFF(MINUTE,cr.created_at,NOW()) <= " . $limite_online . " AND 
						cr.method = 'login'
					THEN 'ONLINE'
					WHEN 
						TIMESTAMPDIFF(MINUTE,cr.created_at,NOW()) >= 0 AND 
						TIMESTAMPDIFF(MINUTE,cr.created_at,NOW()) <= " . $limite_jugando . " AND
						cr.method = 'sell' 
					THEN 'JUGANDO'
				END estado
			FROM tbl_televentas_api_calimaco_response cr
			INNER JOIN tbl_televentas_clientes c ON cr.client_id = c.id
			INNER JOIN (
				SELECT 
					ccr.client_id, MAX(ccr.id) max_id
				FROM tbl_televentas_api_calimaco_response ccr
				WHERE 
					ccr.method in ('sell', 'login')
					AND 
					(
						(TIMESTAMPDIFF(MINUTE,ccr.created_at,NOW()) >= 0 AND TIMESTAMPDIFF(MINUTE,ccr.created_at,NOW()) <= " . $limite_online . " AND ccr.method = 'login') OR 
						(TIMESTAMPDIFF(MINUTE,ccr.created_at,NOW()) >= 0 AND TIMESTAMPDIFF(MINUTE,ccr.created_at,NOW()) <= " . $limite_jugando . " AND ccr.method = 'sell') 
					)
				GROUP BY ccr.client_id
			) AS A ON cr.id = A.max_id
			GROUP BY cr.client_id
			) AS data";

	//$result["consulta_query"] = $query;
	$list_query = $mysqli->query($query);
	$list_registers = array();
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li=$list_query->fetch_assoc()) {
			$list_registers[]=$li;
		}
		$result["cant_online"] = $list_registers[0]["cant_online"];
		$result["cant_jugando"] = $list_registers[0]["cant_jugando"];
	}
	if(count($list_registers)===0){
		$result["http_code"] = 204;
		$result["status"]    = "No hay transacciones.";
		$result["data"]      = $list_registers;
	} elseif(count($list_registers)>0){
		$result["http_code"] = 200;
	} else {
		$result["http_code"] = 400;
		$result["status"]    ="Ocurrió un error al consultar transacciones.";
	}
}

function SecRepCliOn_get_parametros($codigo_parametro){
	global $mysqli;
	$cmd_limit_amount_parameter = "
		SELECT 
			IFNULL(valor, 0) valor
		FROM 
			tbl_televentas_parametros
		WHERE 
			nombre_codigo = '" . $codigo_parametro . "'
			AND estado = 1
		LIMIT 1";
		 
	$list_cmd_l = $mysqli->query($cmd_limit_amount_parameter);
	$list_parameters = array();
	while ($li = $list_cmd_l->fetch_assoc()) {
		$list_parameters[] = $li;
	}
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}
	$valor = "";
	if(count($list_parameters) > 0){
		if((double)$list_parameters[0]["valor"] > 0){
			$valor = $list_parameters[0]["valor"];
		}
	}
	return $valor;
}

echo json_encode($result);
?>
