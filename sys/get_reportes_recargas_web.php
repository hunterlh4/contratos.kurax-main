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
if (isset($_POST["accion"]) && $_POST["accion"]==="SecRptRW_listar_registros") {
	
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$tipo_busqueda             = $_POST["tipo_busqueda"];
	$fecha_inicio              = $_POST["fecha_inicio"];
	$fecha_fin                 = $_POST["fecha_fin"];
	$cliente_tipo              = $_POST["cliente_tipo"];
	$cliente_texto             = $_POST["cliente_texto"];

	$where_fecha = "";
	if($tipo_busqueda == 1){
		$where_fecha = " AND DATE(tra.created_at) >= '" . $fecha_inicio . "'  AND DATE(tra.created_at) <= '" . $fecha_fin . "'";
	}else if($tipo_busqueda == 2){
		$where_fecha = " 
				AND ( 
					(DATE ( caj.fecha_operacion ) >= '".$fecha_inicio."' AND DATE ( caj.fecha_operacion ) <= '".$fecha_fin."' ) 
					OR 
					(DATE ( ce.fecha_operacion ) >= '".$fecha_inicio."' AND DATE ( ce.fecha_operacion ) <= '".$fecha_fin."' ) 
				) ";
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
			tra.txn_id LIKE "%'.$_POST["search"]["value"].'%"
			OR loc.nombre LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.created_at LIKE "%'.$_POST["search"]["value"].'%"
			OR ce_ssql.nombre LIKE "%'.$_POST["search"]["value"].'%"
			OR rb.nombre LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.monto LIKE "%'.$_POST["search"]["value"].'%"
			OR tra_b.bono_monto LIKE "%'.$_POST["search"]["value"].'%"
			OR usu.usuario LIKE "%'.$_POST["search"]["value"].'%"
			OR (CASE cli.tipo_doc WHEN "0" THEN "DNI" WHEN "8" THEN "DNI" WHEN "1" THEN "CARNÉ EXTRANJERÍA" WHEN "2" THEN "PASAPORTE" ELSE "NO DEFINIDO" END) LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.num_doc LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.web_id LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.nombre LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.apellido_paterno LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.apellido_materno LIKE "%'.$_POST["search"]["value"].'%"';
			$nombre_busqueda .= ')';
		}
	}

	$order = "";
	$column = array(
		1=>"tra.txn_id",
		2=>"tra.created_at",
		3=>"ttra.nombre",
		4=>"tra.monto",
		5=>"rb.nombre",
		6=>"cli.telefono",
		7=>"cli.nombre",
		8=>"cli.web_id",
		9=>"tra.monto",
		10=>"tra.bono_monto",
		11=>"usu.usuario"
		);
	if(isset($_POST["order"])) {
		if (array_key_exists($_POST['order']['0']['column'],$column)) {
			$order = ' ORDER BY '.$column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' ';
		} else {
			$order = ' ORDER BY tra.id ASC ';
		}
	} else {
		$order = ' ORDER BY tra.id ASC ';
	}

	if(isset($_POST["length"])) {
		if($_POST["length"] != -1) {
			$limit = ' LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
		}	
	}

	$locales_usuario = [];

	$where_locales_usuarios = "SELECT id,red_id FROM tbl_locales WHERE id IN (".implode(",", $login["usuario_locales"]).") ";
	$result_locales_usuarios = mysqli_query($mysqli,$where_locales_usuarios);
	while ($row_locales_usuarios = mysqli_fetch_assoc($result_locales_usuarios)) {
		array_push($locales_usuario, $row_locales_usuarios['id']);
	}
	if(empty($locales_usuario)){
		array_push($locales_usuario, 0); 
	}
	if ($login["usuario_locales"]) {
		$where_locales = " AND loc.id IN (".implode(",", $locales_usuario).") ";
	}

	// QUERY
	$query_1 ="
		SELECT
			IFNULL(tra.txn_id,'') operation_id,
		    UPPER(IFNULL(loc.nombre, ce_ssql.nombre)) local_cierre,
		    IFNULL(tra.created_at,'') fecha_registro,
		    IFNULL(ttra.nombre,'') tipo,
		    'Calimaco' proveedor,
		    IFNULL(rb.nombre, 'Sin Bono') bono,
		    IFNULL(cli.telefono, '') telefono,
		    (CASE cli.tipo_doc WHEN '0' THEN 'DNI' WHEN '8' THEN 'DNI' WHEN '1' THEN 'CARNÉ EXTRANJERÍA' WHEN '2' THEN 'PASAPORTE' ELSE 'NO DEFINIDO' END) AS tipo_doc,
		    IFNULL(cli.num_doc, '') num_doc,
		    IFNULL(cli.web_id, '') web_id,
		    IFNULL(CONCAT( cli.nombre, ' ', IFNULL( cli.apellido_paterno, '' ), ' ', IFNULL( cli.apellido_materno, '' ) ), '') AS cliente,
		    IFNULL(tra.monto,0) monto,
		    IFNULL(tra_b.bono_monto,0) bono_monto,
		    IFNULL(usu.usuario,'') promotor
		FROM tbl_televentas_clientes_transaccion tra
			LEFT JOIN tbl_caja caj ON caj.id = tra.turno_id
			LEFT JOIN tbl_local_cajas loc_caj ON loc_caj.id = caj.local_caja_id
			LEFT JOIN tbl_locales loc ON loc.id = loc_caj.local_id
		    LEFT JOIN tbl_caja_eliminados ce on ce.id = tra.turno_id 
			LEFT JOIN tbl_local_cajas ce_sqlc ON ce_sqlc.id = ce.local_caja_id 
			LEFT JOIN tbl_locales ce_ssql ON ce_ssql.id = ce_sqlc.local_id 
		    LEFT JOIN tbl_televentas_clientes_tipo_transaccion ttra ON tra.tipo_id = ttra.id
		    LEFT JOIN tbl_televentas_recargas_bono rb ON tra.bono_id = rb.id
		    LEFT JOIN tbl_televentas_clientes cli ON tra.cliente_id = cli.id
		    LEFT JOIN tbl_usuarios usu ON usu.id = tra.user_id
		    LEFT JOIN tbl_televentas_clientes_transaccion tra_b ON tra.transaccion_id = tra_b.id
		WHERE tra.tipo_id = 2 AND tra.estado = 1 AND tra.api_id = 2 "
		.$where_fecha
		.$where_cliente
		.$nombre_busqueda
		.$where_locales
		.$order
		.$limit;

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
		SELECT
			COUNT(*) cant
		FROM tbl_televentas_clientes_transaccion tra
			LEFT JOIN tbl_caja caj ON caj.id = tra.turno_id
			LEFT JOIN tbl_local_cajas loc_caj ON loc_caj.id = caj.local_caja_id
			LEFT JOIN tbl_locales loc ON loc.id = loc_caj.local_id
		    LEFT JOIN tbl_caja_eliminados ce on ce.id = tra.turno_id 
			LEFT JOIN tbl_local_cajas ce_sqlc ON ce_sqlc.id = ce.local_caja_id 
			LEFT JOIN tbl_locales ce_ssql ON ce_ssql.id = ce_sqlc.local_id 
		    LEFT JOIN tbl_televentas_clientes_tipo_transaccion ttra ON tra.tipo_id = ttra.id
		    LEFT JOIN tbl_televentas_recargas_bono rb ON tra.bono_id = rb.id
		    LEFT JOIN tbl_televentas_clientes cli ON tra.cliente_id = cli.id
		    LEFT JOIN tbl_usuarios usu ON usu.id = tra.user_id
		    LEFT JOIN tbl_televentas_clientes_transaccion tra_b ON tra.transaccion_id = tra_b.id
		WHERE tra.tipo_id = 2
			AND tra.estado = 1  AND tra.api_id = 2 "
		.$where_fecha
		.$where_cliente
		.$nombre_busqueda
		.$where_locales;

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
if (isset($_POST["accion"]) && $_POST["accion"]==="SecRptRW_exportar_xls") {
	global $mysqli;
	$usuario_id = $login ? $login['id'] : 0;

	$tipo_busqueda             = $_POST["tipo_busqueda"];
	$fecha_inicio              = $_POST["fecha_inicio"];
	$fecha_fin                 = $_POST["fecha_fin"];
	$cliente_tipo              = $_POST["cliente_tipo"];
	$cliente_texto             = $_POST["cliente_texto"];

	$where_fecha = "";
	if($tipo_busqueda == 1){
		$where_fecha = " AND DATE(tra.created_at) >= '" . $fecha_inicio . "'  AND DATE(tra.created_at) <= '" . $fecha_fin . "'";
	}else if($tipo_busqueda == 2){
		$where_fecha = " 
				AND ( 
					(DATE ( caj.fecha_operacion ) >= '".$fecha_inicio."' AND DATE ( caj.fecha_operacion ) <= '".$fecha_fin."' ) 
					OR 
					(DATE ( ce.fecha_operacion ) >= '".$fecha_inicio."' AND DATE ( ce.fecha_operacion ) <= '".$fecha_fin."' ) 
				) ";
	}

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
			tra.txn_id LIKE "%'.$_POST["search"]["value"].'%"
			OR loc.nombre LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.created_at LIKE "%'.$_POST["search"]["value"].'%"
			OR ce_ssql.nombre LIKE "%'.$_POST["search"]["value"].'%"
			OR rb.nombre LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.monto LIKE "%'.$_POST["search"]["value"].'%"
			OR tra_b.bono_monto LIKE "%'.$_POST["search"]["value"].'%"
			OR usu.usuario LIKE "%'.$_POST["search"]["value"].'%"
			OR (CASE cli.tipo_doc WHEN "0" THEN "DNI" WHEN "8" THEN "DNI" WHEN "1" THEN "CARNÉ EXTRANJERÍA" WHEN "2" THEN "PASAPORTE" ELSE "NO DEFINIDO" END) LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.num_doc LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.web_id LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.nombre LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.apellido_paterno LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.apellido_materno LIKE "%'.$_POST["search"]["value"].'%"';
			$nombre_busqueda .= ')';
		}
	}

	// QUERY
	$query_1 ="
		SELECT
			IFNULL(tra.txn_id,'') operation_id,
		    UPPER(IFNULL(loc.nombre, ce_ssql.nombre)) local_cierre,
		    IFNULL(tra.created_at,'') fecha_registro,
		    IFNULL(ttra.nombre,'') tipo,
		    'Calimaco' proveedor,
		    IFNULL(rb.nombre, 'Sin Bono') bono,
		    IFNULL(cli.telefono, '') telefono,
		    (CASE cli.tipo_doc WHEN '0' THEN 'DNI' WHEN '8' THEN 'DNI' WHEN '1' THEN 'CARNÉ EXTRANJERÍA' WHEN '2' THEN 'PASAPORTE' ELSE 'NO DEFINIDO' END) AS tipo_doc,
		    IFNULL(cli.num_doc, '') num_doc,
		    IFNULL(cli.web_id, '') web_id,
		    IFNULL(CONCAT( cli.nombre, ' ', IFNULL( cli.apellido_paterno, '' ), ' ', IFNULL( cli.apellido_materno, '' ) ), '') AS cliente,
		    IFNULL(tra.monto,0) monto,
		    IFNULL(tra_b.bono_monto,0) bono_monto,
		    IFNULL(usu.usuario,'') promotor
		FROM tbl_televentas_clientes_transaccion tra
			LEFT JOIN tbl_caja caj ON caj.id = tra.turno_id
			LEFT JOIN tbl_local_cajas loc_caj ON loc_caj.id = caj.local_caja_id
			LEFT JOIN tbl_locales loc ON loc.id = loc_caj.local_id
		    LEFT JOIN tbl_caja_eliminados ce on ce.id = tra.turno_id 
			LEFT JOIN tbl_local_cajas ce_sqlc ON ce_sqlc.id = ce.local_caja_id 
			LEFT JOIN tbl_locales ce_ssql ON ce_ssql.id = ce_sqlc.local_id 
		    LEFT JOIN tbl_televentas_clientes_tipo_transaccion ttra ON tra.tipo_id = ttra.id
		    LEFT JOIN tbl_televentas_recargas_bono rb ON tra.bono_id = rb.id
		    LEFT JOIN tbl_televentas_clientes cli ON tra.cliente_id = cli.id
		    LEFT JOIN tbl_usuarios usu ON usu.id = tra.user_id
		    LEFT JOIN tbl_televentas_clientes_transaccion tra_b ON tra.transaccion_id = tra_b.id
		WHERE tra.tipo_id = 2 AND tra.estado = 1  AND tra.api_id = 2 "
		.$where_fecha
		.$where_cliente
		.$nombre_busqueda
		."  ORDER BY tra.id ASC ";
	$list_query = $mysqli->query($query_1);
	$result_data = array();
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
		echo json_encode([
			"error" => "Export error"
		]);
		exit;
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$result_data[] = $li;
		}

		$headers = [
			"operation_id" => "Identifier",
			"local_cierre" => "Local",
			"fecha_registro" => "Registro",
			"tipo" => "Tipo",
			"proveedor" => "Proveedor",
			"bono" => "Bono",
			"telefono" => "Teléfono",
			"tipo_doc" => "Tipo Documento",
			"num_doc" => "Número Documento",
			"web_id" => "WEB-ID",
			"cliente" => "Cliente",
			"monto" => "Recarga",
			"bono_monto" => "Bono 5%",
			"promotor" => "Promotor"
		];
		array_unshift($result_data, $headers);

		require_once '../phpexcel/classes/PHPExcel.php';
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getActiveSheet()->fromArray($result_data, null, 'A1');
		$date = new DateTime();
		$file_title = "reporte_recargas_web_" . $date->getTimestamp();

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

if (isset($_POST["accion"]) && $_POST["accion"]==="SecRptRW_listar_resumen") {
	$tipo_busqueda             = $_POST["tipo_busqueda"];
	$fecha_inicio              = $_POST["fecha_inicio"];
	$fecha_fin                 = $_POST["fecha_fin"];
	$cliente_tipo              = $_POST["cliente_tipo"];
	$cliente_texto             = $_POST["cliente_texto"];

	$where_fecha = "";
	if($tipo_busqueda == 1){
		$where_fecha = " AND DATE(tra.created_at) >= '" . $fecha_inicio . "'  AND DATE(tra.created_at) <= '" . $fecha_fin . "'";
	}else if($tipo_busqueda == 2){
		$where_fecha = " 
				AND ( 
					(DATE ( caj.fecha_operacion ) >= '".$fecha_inicio."' AND DATE ( caj.fecha_operacion ) <= '".$fecha_fin."' ) 
					OR 
					(DATE ( ce.fecha_operacion ) >= '".$fecha_inicio."' AND DATE ( ce.fecha_operacion ) <= '".$fecha_fin."' ) 
				) ";
	}

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
			tra.txn_id LIKE "%'.$_POST["search"]["value"].'%"
			OR loc.nombre LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.created_at LIKE "%'.$_POST["search"]["value"].'%"
			OR ce_ssql.nombre LIKE "%'.$_POST["search"]["value"].'%"
			OR rb.nombre LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.monto LIKE "%'.$_POST["search"]["value"].'%"
			OR tra_b.bono_monto LIKE "%'.$_POST["search"]["value"].'%"
			OR usu.usuario LIKE "%'.$_POST["search"]["value"].'%"
			OR (CASE cli.tipo_doc WHEN "0" THEN "DNI" WHEN "8" THEN "DNI" WHEN "1" THEN "CARNÉ EXTRANJERÍA" WHEN "2" THEN "PASAPORTE" ELSE "NO DEFINIDO" END) LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.num_doc LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.web_id LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.nombre LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.apellido_paterno LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.apellido_materno LIKE "%'.$_POST["search"]["value"].'%"';
			$nombre_busqueda .= ')';
		}
	}
	
	// Cantidades
	$query_COUNT ="
		SELECT
			IFNULL(SUM(IF(tra.tipo_id = 2, 1, 0)), 0) AS recargas_cant,
			IFNULL(SUM(IF(tra.tipo_id=2, tra.monto, 0)), 0) AS total_recarga,
			IFNULL(SUM(IF(rb.nombre LIKE '%Bono Apuestas Deportivas%' AND tra.tipo_id IN (10), 1, 0)), 0) AS bono_apuesta_deportiva_cant,
			IFNULL(SUM(IF(rb.nombre LIKE '%Bono Apuestas Deportivas%' AND tra.tipo_id IN (10), tra_b.bono_monto, 0)), 0) AS total_bono_apuesta_deportiva,
			IFNULL(SUM(IF(rb.nombre LIKE '%Bono Casino%' AND tra.tipo_id IN (10), 1, 0)), 0) AS bono_casino_cant,
			IFNULL(SUM(IF(rb.nombre LIKE '%Bono Casino%' AND tra.tipo_id IN (10), tra_b.bono_monto, 0)), 0) AS total_bono_casino
		FROM tbl_televentas_clientes_transaccion tra
			LEFT JOIN tbl_caja caj ON caj.id = tra.turno_id
			LEFT JOIN tbl_local_cajas loc_caj ON loc_caj.id = caj.local_caja_id
			LEFT JOIN tbl_locales loc ON loc.id = loc_caj.local_id
			LEFT JOIN tbl_caja_eliminados ce on ce.id = tra.turno_id 
			LEFT JOIN tbl_local_cajas ce_sqlc ON ce_sqlc.id = ce.local_caja_id 
			LEFT JOIN tbl_locales ce_ssql ON ce_ssql.id = ce_sqlc.local_id 
			LEFT JOIN tbl_televentas_clientes_tipo_transaccion ttra ON tra.tipo_id = ttra.id
			LEFT JOIN tbl_televentas_recargas_bono rb ON tra.bono_id = rb.id
			LEFT JOIN tbl_televentas_clientes cli ON tra.cliente_id = cli.id
			LEFT JOIN tbl_usuarios usu ON usu.id = tra.user_id
		    LEFT JOIN tbl_televentas_clientes_transaccion tra_b ON tra.transaccion_id = tra_b.id
		WHERE tra.tipo_id in (2,10) AND tra.estado = 1  AND tra.api_id = 2  
		".$where_fecha ." 
		".$where_cliente."
		".$nombre_busqueda
		;
	//echo $query_1;
	$result["QUERY_listar_transacciones_resumen_v2"] = $query_COUNT;
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