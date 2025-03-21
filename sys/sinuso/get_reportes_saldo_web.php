<?php
$result=array();
include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);




//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER TRANSACCIONES X CAJERO
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"]==="listar_transacciones") {
	global $login;
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 5;

	$busqueda_fecha_inicio = $_POST["fecha_inicio"];
	$busqueda_fecha_fin    = $_POST["fecha_fin"];
	$busqueda_tipo_transaccion = $_POST["tipo_transaccion"];
	$busqueda_cajero       = $_POST["cajero"];
	$busqueda_local        = $_POST["local"];
	$busqueda_zona         = $_POST["zona"];
	$busqueda_estado       = $_POST["estado"];

	$where_fecha_inicio=" tsw.created_at>= '".$busqueda_fecha_inicio." 00:00:00' ";
	$where_fecha_fin=" AND tsw.created_at<= '".$busqueda_fecha_fin." 23:59:59' ";
	$where_tipo_transaccion="";
	if( (int) $busqueda_tipo_transaccion > 0 ){
		$where_tipo_transaccion=" AND tsw.tipo_id='".$busqueda_tipo_transaccion."' ";
	}

	$where_cajero="";
	if( (int) $cargo_id === 5 ){//Cajero
		$where_cajero=" AND tsw.user_id='".$usuario_id."' ";
	} else {
		if( (int) $busqueda_cajero > 0 ){
			$where_cajero=" AND tsw.user_id='".$busqueda_cajero."' ";
		}
	}

	$where_local="";
	if( (int) $cargo_id !== 5 && (int) $busqueda_local > 0){
		$where_local=" AND loc.cc_id='". str_pad( ((int)$busqueda_local), 4, "0", STR_PAD_LEFT) ."' ";
	}

	$where_zona="";
	if((int)$busqueda_zona>0){
		$where_zona=" AND loc.zona_id='".$busqueda_zona."' ";
	}

	$where_estado="";
	if(strlen($busqueda_estado)>0){
		$where_estado=" AND tsw.status='".$busqueda_estado."' ";
	}

	$query_1 ="
		SELECT
			tsw.id cod_transaccion,
			tsw.tipo_id cod_tipo,
			( CASE tsw.tipo_id WHEN 1 THEN 'Depósito' WHEN 2 THEN 'Retiro'  WHEN 3 THEN 'Extorno' ELSE '' END ) tipo,
			IFNULL(tsw.txn_id, '') cod_txn,
			tsw.client_id cod_cliente,
			tsw.client_name cliente,
			IFNULL( tsw.cc_id, '' ) cc_id,
			-- IFNULL( l.nombre, 'SIN LOCAL' ) nombre_local,
			loc.id cod_local,
			UPPER(IFNULL(loc.nombre, '')) nombre_local,
			UPPER(IFNULL(z.nombre, '')) zona,
			tsw.user_id cod_cajero,
			UPPER( u.usuario ) cajero,
			CONCAT(
			IF
				( LENGTH( pl.apellido_paterno ) > 0, CONCAT( UPPER( pl.apellido_paterno ), ' ' ), '' ),
			IF
				( LENGTH( pl.apellido_materno ) > 0, CONCAT( UPPER( pl.apellido_materno ), ' ' ), '' ),
			IF
				( LENGTH( pl.nombre ) > 0, UPPER( pl.nombre ), '' ) 
			) nombre_cajero,
			tsw.monto monto,
			tsw.created_at registro,
			tsw.status cod_estado,
			( CASE tsw.status WHEN 0 THEN 'Fallido' WHEN 1 THEN 'Completado' ELSE '' END ) estado
		FROM
			tbl_saldo_web_transaccion tsw
			JOIN tbl_usuarios u ON u.id = tsw.user_id
			JOIN tbl_personal_apt pl ON pl.id = u.personal_id
			LEFT JOIN tbl_caja caj ON caj.id = tsw.turno_id
			LEFT JOIN tbl_caja_eliminados ce on ce.id = tsw.turno_id 
			LEFT JOIN tbl_local_cajas loc_caj ON loc_caj.id = caj.local_caja_id OR loc_caj.id = ce.local_caja_id 
			LEFT JOIN tbl_locales loc ON loc.cc_id = tsw.cc_id
			LEFT JOIN tbl_zonas z ON z.id = loc.zona_id 
		WHERE 
			".$where_fecha_inicio ."
			".$where_fecha_fin ."
			".$where_tipo_transaccion ."
			".$where_cajero ."
			".$where_local ."
			".$where_zona ."
			".$where_estado ."
		ORDER BY tsw.id ASC
		";
	$result["consulta_query"] = $query_1;
	$list_query=$mysqli->query($query_1);
	if($mysqli->error){
		$result["http_code"] = 400;
		$result["status"] ="Ocurrió un error al consultar transacciones.";
		$result["consulta_error"] = $mysqli->error;
	} else {
		$list_transaccion=array();
		while ($li=$list_query->fetch_assoc()) {
			$list_transaccion[]=$li;
		}
		if(count($list_transaccion)==0){
			$result["http_code"] = 300;
			$result["status"] ="No hay transacciones.";
		} elseif(count($list_transaccion)>0){
			$result["http_code"] = 200;
			$result["status"] ="ok";
			$result["result"] =$list_transaccion;
			//$result["login"]=$login;
		} else{
			$result["http_code"] = 400;
			$result["status"] ="Ocurrió un error al consultar transacciones.";
		}
	}

}





//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER TRANSACCIONES X CAJERO --> EXCEL
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"]==="listar_transacciones_export_xls") {
	global $mysqli;
	global $login;
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 5;

	$busqueda_fecha_inicio = $_POST["fecha_inicio"];
	$busqueda_fecha_fin    = $_POST["fecha_fin"];
	$busqueda_tipo_transaccion = $_POST["tipo_transaccion"];
	$busqueda_cajero       = $_POST["cajero"];
	$busqueda_local        = $_POST["local"];
	$busqueda_zona         = $_POST["zona"];
	$busqueda_estado       = $_POST["estado"];

	$where_fecha_inicio=" tsw.created_at>= '".$busqueda_fecha_inicio." 00:00:00' ";
	$where_fecha_fin=" AND tsw.created_at<= '".$busqueda_fecha_fin." 23:59:59' ";

	$where_permisos_locales="";
	if($login["usuario_locales"]){
		$where_permisos_locales=" AND l.id IN (".implode(",", $login["usuario_locales"]).") ";
	}

	$where_tipo_transaccion="";
	if( (int) $busqueda_tipo_transaccion > 0 ){
		$where_tipo_transaccion=" AND tsw.tipo_id='".$busqueda_tipo_transaccion."' ";
	}

	$where_cajero="";
	if( (int) $cargo_id === 5 ){//Cajero
		$where_cajero=" AND tsw.user_id='".$usuario_id."' ";
	} else {
		if( (int) $busqueda_cajero > 0 ){
			$where_cajero=" AND tsw.user_id='".$busqueda_cajero."' ";
		}
	}

	$where_local="";
	if( (int) $cargo_id !== 5 && (int) $busqueda_local > 0){
		$where_local=" AND tsw.cc_id='". str_pad( ((int) $busqueda_local), 4, "0", STR_PAD_LEFT) ."' ";
	}

	$where_zona="";
	if((int)$busqueda_zona>0){
		$where_zona=" AND loc.zona_id='".$busqueda_zona."' ";
	}

	$where_estado="";
	if(strlen($busqueda_estado)>0){
		$where_estado=" AND tsw.status='".$busqueda_estado."' ";
	}

	$query_1 ="
		SELECT
			tsw.created_at registro,
			UPPER(IFNULL(z.nombre, '')) zona,
			( CASE tsw.tipo_id WHEN 1 THEN 'Depósito' WHEN 2 THEN 'Retiro' WHEN 3 THEN 'Extorno' ELSE '' END ) tipo,
			tsw.monto monto,
			CONCAT(tsw.client_id, ' - ', tsw.client_name) cliente,
			IFNULL( tsw.cc_id, '' ) cc_id,
			IFNULL( l.nombre, 'SIN LOCAL' ) nombre_local,
			CONCAT(
			IF
				( LENGTH( pl.apellido_paterno ) > 0, CONCAT( UPPER( pl.apellido_paterno ), ' ' ), '' ),
			IF
				( LENGTH( pl.apellido_materno ) > 0, CONCAT( UPPER( pl.apellido_materno ), ' ' ), '' ),
			IF
				( LENGTH( pl.nombre ) > 0, UPPER( pl.nombre ), '' ) 
			) nombre_cajero,
			IFNULL(tsw.txn_id, '') cod_txn,
			( CASE tsw.status WHEN 0 THEN 'Fallido' WHEN 1 THEN 'Completado' ELSE '' END ) estado
		FROM
			tbl_saldo_web_transaccion tsw
			JOIN tbl_usuarios u ON u.id = tsw.user_id
			JOIN tbl_personal_apt pl ON pl.id = u.personal_id
			LEFT JOIN tbl_locales l ON l.cc_id = tsw.cc_id 
			LEFT JOIN tbl_zonas z ON z.id = l.zona_id 
		WHERE
			".$where_fecha_inicio ."
			".$where_fecha_fin ."
			".$where_tipo_transaccion ."
			".$where_cajero ."
			".$where_local ."
			".$where_zona ."
			".$where_estado ."
			".$where_permisos_locales ."
			ORDER BY tsw.id ASC
	";
	//$result["consulta_query"] = $query_1;
	$list_query=$mysqli->query($query_1);
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["consulta_query"] = $query_1;
		$result["error"] = 'Export error: ' . $mysqli->error;
		echo json_encode($result);
		exit;
	} else {
		$result_data=array();
		$venta_total=0;
		$pago_total=0;
		while ($li=$list_query->fetch_assoc()) {
			$result_data[]=$li;
		}

		if (!$result_data) {
			echo json_encode([
				"error" => "Export error"
			]);
			exit;
		}

		$headers = [
			"registro" => "Fecha y Hora",
			"zona" => "Zona",
			"tipo" => "Tipo",
			"monto" => "Monto",
			"cliente" => "Cliente",
			"cc_id" => "C.C.",
			"nombre_local" => "Local",
			"nombre_cajero" => "Cajero",
			"cod_txn" => "Transacción-ID",
			"estado" => "Estado"
		];
		array_unshift($result_data, $headers);


		ini_set('display_errors', 0);
		ini_set('display_startup_errors', 0);
		error_reporting(0);

		require_once '../phpexcel/classes/PHPExcel.php';
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getActiveSheet()->fromArray($result_data, null, 'A1');
		$date = new DateTime();
		$file_title = "reporte_saldo_web_" . $date->getTimestamp() . "_" . $usuario_id;

		if (!file_exists('/var/www/html/export/files_exported/reporte_torito/')) {
			mkdir('/var/www/html/export/files_exported/reporte_torito/', 0777, true);
		}

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . $file_title . '.xls"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$excel_path = '/var/www/html/export/files_exported/reporte_torito/' . $file_title . '.xls';
		$excel_path_download = '/export/files_exported/reporte_torito/' . $file_title . '.xls';
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

?>