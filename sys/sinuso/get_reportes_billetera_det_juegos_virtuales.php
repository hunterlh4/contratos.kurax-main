<?php
$result=array();
include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);

if (isset($_POST["accion"]) && $_POST["accion"]==="listar_det_resultados") {
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$busqueda_fecha_inicio     = $_POST["fecha_inicio"];
	$busqueda_fecha_fin        = $_POST["fecha_fin"];

	$cliente_tipo              = $_POST["cliente_tipo"];
	$cliente_texto             = $_POST["cliente_texto"];
	$estado                    = $_POST["estado"];
	$tipo_saldo                = $_POST["tipo_saldo"];

	$where_users_test="";
	if((int)$area_id !== 6) {// diferente de sistemas
		$where_users_test="	
			AND IFNULL(tra.web_id, '') not in ('3333200', '71938219') 
			AND IFNULL(tra.user_id, '') not in (1, 249, 250, 2572, 3028) 
		";
	}

	$where_fecha = " 
		AND DATE(tra.created_at) >= '".$busqueda_fecha_inicio."' 
		AND DATE(tra.created_at) <= '".$busqueda_fecha_fin."' 
	";

	$where_cliente = "";
	if(strlen($cliente_texto) > 1){
		if((int)$cliente_tipo === 1){ // Por nombres
			$where_cliente = " AND CONCAT( IFNULL(c.nombre, ''), ' ', IFNULL(c.apellido_paterno, ''), ' ', IFNULL(c.apellido_materno, '') ) LIKE '%". $cliente_texto ."%' ";
		}
		if((int)$cliente_tipo === 2){ // Por num doc
			$where_cliente = " AND IFNULL(c.num_doc, '') = '". $cliente_texto ."' ";
		}
	}

	$where_estado = "";
	if($estado != ""){
		$where_estado = " HAVING estado = '". $estado ."' ";
	}

	$where_tipo_saldo="";
	if( (int) $tipo_saldo === 2 ){ // Listar solo transacciones de DINERO AT
		$where_tipo_saldo=" AND tra.id_tipo_balance = 6 ";
	}else if ( (int) $tipo_saldo === 1 ) {
		$where_tipo_saldo=" AND (tra.id_tipo_balance != 6 OR tra.id_tipo_balance IS NULL ) ";
	}else if ( (int) $tipo_saldo === 0 ) {
		$where_tipo_saldo="";
	}

	$nombre_busqueda = "";
	if(isset($_POST["search"]["value"])) {
		$busqueda_nombre = $_POST["search"]["value"];
		if ($busqueda_nombre !="") {
			$nombre_busqueda = ' 
			AND (
			IFNULL(CONCAT( c.nombre, " ", IFNULL( c.apellido_paterno, "" ), " ", IFNULL( c.apellido_materno, "" ) ), "") LIKE "%'.$_POST["search"]["value"].'%"
			OR c.num_doc LIKE "%'.$_POST["search"]["value"].'%" ';
			$nombre_busqueda .= ')';
		}
	}

	$order = "";
	$column = array(
		1=>"c.nombre",
		2=>"c.num_doc"
	);

	if(isset($_POST["order"])) {
		if (array_key_exists($_POST['order']['0']['column'],$column)) {
			$order = ' ORDER BY '.$column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' ';
		} else {
			$order = ' ORDER BY c.nombre ASC ';
		}
	} else {
		$order = ' ORDER BY c.nombre ASC ';
	}

	if(isset($_POST["length"])) {
		if($_POST["length"] != -1) {
			$limit = ' LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
		}	
	}

	$query_1 ="SELECT 
					c.id cliente_id, 
					IFNULL(CONCAT( c.nombre, ' ', IFNULL( c.apellido_paterno, '' ), ' ', IFNULL( c.apellido_materno, '' ) ), '') AS cliente,
					IFNULL(c.num_doc, '') num_doc,
					IFNULL(t.game, '') nomb_juego,
					/* (CASE 
						WHEN t.game = 'Spin2Win ApuestaTotal - ondemand' THEN 'SPIN2WIN' 
						WHEN t.game = 'Spin2Win Royale ApuestaTotal - ondemand' THEN 'SPIN2WINROYALE' 
						WHEN t.game = 'Keno - ondemand' THEN 'KENO' 
						WHEN t.game = 'Keno Deluxe - ondemand' THEN 'KENO_DELUXE' 
						WHEN t.game = 'Greyhounds' THEN 'DOG' 
						WHEN t.game = 'Horses 6' THEN 'HORSE' 
						END) AS juego,*/
					IFNULL(tra.created_at, '') fecha_juego,
					IFNULL(tra.txn_id, '') ticket,
					CASE
						WHEN IFNULL(r.status, '') = 'WON' AND IFNULL(r.paidAmount, 0) > 0 THEN 'PAIDOUT'
						ELSE IFNULL(r.status, '') 
					END estado,
					IFNULL(ttra.nombre, '') tipo,
					IFNULL(tra.monto, '') monto,
					IF( tra.id_tipo_balance = 6, 'Saldo Promocional', 'Saldo Real') tipo_saldo
				FROM tbl_televentas_clientes_transaccion tra
				INNER JOIN tbl_televentas_clientes_tipo_transaccion ttra ON tra.tipo_id = ttra.id
				INNER JOIN tbl_televentas_tickets t ON tra.txn_id = t.ticket_id
				INNER JOIN tbl_televentas_api_gr_requests_sell r ON tra.txn_id = r.ticketId
				INNER JOIN tbl_televentas_clientes c ON tra.cliente_id = c.id
				WHERE 
					tra.api_id = 8 
					AND t.proveedor_id = 8 "
				. $where_fecha
				. $where_cliente 
				. $nombre_busqueda
				. $where_estado 
				. $where_tipo_saldo
				 
				. $order 
				. $limit
				;
	$result["consulta_query"] = $query_1;
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
		SELECT COUNT(*) cant 
        FROM (
        		SELECT 
					t.id
					FROM tbl_televentas_clientes_transaccion tra
					INNER JOIN tbl_televentas_clientes_tipo_transaccion ttra ON tra.tipo_id = ttra.id
					INNER JOIN tbl_televentas_tickets t ON tra.txn_id = t.ticket_id
					INNER JOIN tbl_televentas_api_gr_requests_sell r ON tra.txn_id = r.ticketId
					INNER JOIN tbl_televentas_clientes c ON tra.cliente_id = c.id
				WHERE 
					tra.api_id = 8 
					AND t.proveedor_id = 8 "
				. $where_fecha
				. $where_cliente 
				. $nombre_busqueda
				. $where_estado 
				. $where_tipo_saldo
				. " 
			) AS data";
	$result["query_COUNT"] = $query_COUNT;
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
		$result["status"]    = "No hay resultados.";
		$result["data"]      = $list_transaccion;
	} elseif(count($list_transaccion)>0){
		$result["http_code"]       = 200;
		$result["status"]          = "ok";
		$result["draw"]            = intval($_POST["draw"]);
		$result["recordsTotal"]    = $list_transaccion_COUNT[0]["cant"];
		$result["recordsFiltered"] = $list_transaccion_COUNT[0]["cant"];
		$result["data"]            = $list_transaccion;
	} else {
		$result["http_code"] = 400;
		$result["status"]    ="Ocurrió un error al consultar transacciones.";
		$result["data"]      = $list_transaccion;
		$result["resumen"]   = $list_transaccion_COUNT;
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="exportar_det_resultados_xls") {
	global $mysqli;
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$busqueda_fecha_inicio     = $_POST["fecha_inicio"];
	$busqueda_fecha_fin        = $_POST["fecha_fin"];

	$cliente_tipo              = $_POST["cliente_tipo"];
	$cliente_texto             = $_POST["cliente_texto"];
	$estado                    = $_POST["estado"];
	$tipo_saldo                = $_POST["tipo_saldo"];

	$where_users_test="";
	if((int)$area_id !== 6) {// diferente de sistemas
		$where_users_test="	
			AND IFNULL(tra.web_id, '') not in ('3333200', '71938219') 
			AND IFNULL(tra.user_id, '') not in (1, 249, 250, 2572, 3028) 
		";
	}

	$where_fecha = " 
		AND DATE(tra.created_at) >= '".$busqueda_fecha_inicio."' 
		AND DATE(tra.created_at) <= '".$busqueda_fecha_fin."' 
	";

	$where_cliente = "";
	if(strlen($cliente_texto) > 1){
		if((int)$cliente_tipo === 1){ // Por nombres
			$where_cliente = " AND CONCAT( IFNULL(c.nombre, ''), ' ', IFNULL(c.apellido_paterno, ''), ' ', IFNULL(c.apellido_materno, '') ) LIKE '%". $cliente_texto ."%' ";
		}
		if((int)$cliente_tipo === 2){ // Por num doc
			$where_cliente = " AND IFNULL(c.num_doc, '') = '". $cliente_texto ."' ";
		}
	}

	$where_estado = "";
	if($estado != ""){
		$where_estado = " HAVING estado = '". $estado ."' ";
	}

	$where_tipo_saldo="";
	if( (int) $tipo_saldo === 2 ){ // Listar solo transacciones de DINERO AT
		$where_tipo_saldo=" AND tra.id_tipo_balance = 6 ";
	}else if ( (int) $tipo_saldo === 1 ) {
		$where_tipo_saldo=" AND (tra.id_tipo_balance != 6 OR tra.id_tipo_balance IS NULL ) ";
	}else if ( (int) $tipo_saldo === 0 ) {
		$where_tipo_saldo="";
	}

	$query_1 ="SELECT 
				IFNULL(CONCAT( c.nombre, ' ', IFNULL( c.apellido_paterno, '' ), ' ', IFNULL( c.apellido_materno, '' ) ), '') AS cliente,
				IFNULL(c.num_doc, '') num_doc,
				IFNULL(t.game, '') nomb_juego,
				IFNULL(tra.created_at, '') fecha_juego,
				IFNULL(tra.txn_id, '') ticket,
					CASE
						WHEN IFNULL(r.status, '') = 'WON' AND IFNULL(r.paidAmount, 0) > 0 THEN 'PAIDOUT'
						ELSE IFNULL(r.status, '') 
					END estado,
				IFNULL(ttra.nombre, '') tipo,
				IFNULL(tra.monto, '') monto,
				IF( tra.id_tipo_balance = 6, 'Saldo Promocional', 'Saldo Real') tipo_saldo
			FROM tbl_televentas_clientes_transaccion tra
			INNER JOIN tbl_televentas_clientes_tipo_transaccion ttra ON tra.tipo_id = ttra.id
			INNER JOIN tbl_televentas_tickets t ON tra.txn_id = t.ticket_id
			INNER JOIN tbl_televentas_api_gr_requests_sell r ON tra.txn_id = r.ticketId
			INNER JOIN tbl_televentas_clientes c ON tra.cliente_id = c.id
		WHERE 
			tra.api_id = 8 
			AND t.proveedor_id = 8 "
		. $where_fecha
		. $where_cliente 
		. $nombre_busqueda
		. $where_estado
		. $where_tipo_saldo;
	$result["consulta_query"] = $query_1;
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
			"cliente," => "CLIENTE",
			"num_doc," => "NUM DOC",
			"nomb_juego," => "JUEGO",
			"fecha_juego," => "FECHA JUEGO",
			"ticket," => "TICKET",
			"estado," => "ESTADO",
			"tipo," => "TIPO",
			"monto," => "MONTO",
			"monto," => "TIPO SALDO"
			/*"speedway," => "SPEEDWAY",
			"liga_inglaterra," => "LIGA INGLATERRA",
			"liga_espana," => "LIGA ESPAÑA",
			"liga_italia," => "OPERATION-LIGA ITALIA",
			"torneo" => "TORNEO"*/
		];
		array_unshift($result_data, $headers);

		ini_set('display_errors', 0);
		ini_set('display_startup_errors', 0);
		error_reporting(0);

		require_once '../phpexcel/classes/PHPExcel.php';
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getActiveSheet()->fromArray($result_data, null, 'A1');
		$date = new DateTime();
		$file_title = "reporte_billetera_det_juegos_virtuales" . $date->getTimestamp();

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

if (isset($_POST["accion"]) && $_POST["accion"]==="listar_det_resultados_totales") {
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$busqueda_fecha_inicio     = $_POST["fecha_inicio"];
	$busqueda_fecha_fin        = $_POST["fecha_fin"];

	$cliente_tipo              = $_POST["cliente_tipo"];
	$cliente_texto             = $_POST["cliente_texto"];
	$estado                    = $_POST["estado"];
	$tipo_saldo                = $_POST["tipo_saldo"];

	$where_users_test="";
	if((int)$area_id !== 6) {// diferente de sistemas
		$where_users_test="	
			AND IFNULL(tra.web_id, '') not in ('3333200', '71938219') 
			AND IFNULL(tra.user_id, '') not in (1, 249, 250, 2572, 3028) 
		";
	}

	$where_fecha = " 
		AND DATE(tra.created_at) >= '".$busqueda_fecha_inicio."' 
		AND DATE(tra.created_at) <= '".$busqueda_fecha_fin."' 
	";

	$where_cliente = "";
	if(strlen($cliente_texto) > 1){
		if((int)$cliente_tipo === 1){ // Por nombres
			$where_cliente = " AND CONCAT( IFNULL(c.nombre, ''), ' ', IFNULL(c.apellido_paterno, ''), ' ', IFNULL(c.apellido_materno, '') ) LIKE '%". $cliente_texto ."%' ";
		}
		if((int)$cliente_tipo === 2){ // Por num doc
			$where_cliente = " AND IFNULL(c.num_doc, '') = '". $cliente_texto ."' ";
		}
	}

	$where_estado = "";
	if($estado != ""){
		$where_estado = " AND (CASE WHEN IFNULL(r.status, '') = 'WON' AND IFNULL(r.paidAmount, 0) > 0 THEN 'PAIDOUT' ELSE IFNULL(r.status, '')  END) =  '". $estado ."' ";
	}

	$where_tipo_saldo="";
	if( (int) $tipo_saldo === 2 ){ // Listar solo transacciones de DINERO AT
		$where_tipo_saldo=" AND tra.id_tipo_balance = 6 ";
	}else if ( (int) $tipo_saldo === 1 ) {
		$where_tipo_saldo=" AND (tra.id_tipo_balance != 6 OR tra.id_tipo_balance IS NULL ) ";
	}else if ( (int) $tipo_saldo === 0 ) {
		$where_tipo_saldo="";
	}

	$query_1 ="SELECT 			 
					IFNULL(SUM(tra.monto), 0) AS monto_total		 
				FROM tbl_televentas_clientes_transaccion tra
				INNER JOIN tbl_televentas_clientes_tipo_transaccion ttra ON tra.tipo_id = ttra.id
				INNER JOIN tbl_televentas_tickets t ON tra.txn_id = t.ticket_id
				INNER JOIN tbl_televentas_api_gr_requests_sell r ON tra.txn_id = r.ticketId
				INNER JOIN tbl_televentas_clientes c ON tra.cliente_id = c.id
				WHERE 
				tra.api_id = 8 
				AND t.proveedor_id = 8 "
				. $where_fecha
				. $where_cliente 
				. $where_estado
				. $nombre_busqueda
				. $where_tipo_saldo ;
	$result["consulta_query"] = $query_1;
	$list_query=$mysqli->query($query_1);
	$list_transaccion=array();
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li=$list_query->fetch_assoc()) {
			$list_transaccion[]=$li;
		}
	}

	if(count($list_transaccion)===0){
		$result["http_code"] = 204;
		$result["status"]    = "No hay resultados.";
	} elseif(count($list_transaccion)>0){
		$result["http_code"]       = 200;
		$result["status"]          = "ok";
		$result["result"]          = $list_transaccion;
	} else {
		$result["http_code"] = 400;
		$result["status"]    ="Ocurrió un error al consultar transacciones.";
	}
}

echo json_encode($result);

?>