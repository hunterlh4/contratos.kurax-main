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

	$where_fecha_inicio=" tt.created_at >= '".$busqueda_fecha_inicio." 00:00:00' ";
	$where_fecha_fin=" AND tt.created_at <= '".$busqueda_fecha_fin." 23:59:59' ";

	$where_tipo_transaccion="";
	if( (int) $busqueda_tipo_transaccion > 0 ){
		$where_tipo_transaccion=" AND tt.tipo_id='".$busqueda_tipo_transaccion."' ";
	}

	$where_cajero="";
	if( (int) $cargo_id === 5 ){//Cajero
		$where_cajero=" AND tt.user_id='".$usuario_id."' ";
	} else {
		if( (int) $busqueda_cajero > 0 ){
			$where_cajero=" AND tt.user_id='".$busqueda_cajero."' ";
		}
	}

	$where_local="";
	if( (int) $cargo_id !== 5 && (int) $busqueda_local > 0){
		$where_local=" AND l.cc_id='". str_pad( ((int)$busqueda_local), 4, "0", STR_PAD_LEFT) ."' ";
	}

	$where_zona="";
	if((int)$busqueda_zona>0){
		$where_zona=" AND l.zona_id='".$busqueda_zona."' ";
	}

	$where_estado="";
	if($busqueda_estado > 0){
		$where_estado=" AND tt.status='".$busqueda_estado."' ";
	}

	$query_1 ="SELECT 
				tt.created_at registro,
				z.nombre zona, 
				tt.tipo_id cod_tipo,
				CASE
					WHEN tt.tipo_id = 1 THEN 'Depósito'
					WHEN tt.tipo_id = 2 THEN 'Retiro'
					ELSE ''
				END tipo,
				tt.monto monto,
				tt.client_tipo_doc tipo_doc,
				CONCAT(client_num_doc, ' - ',client_name) cliente,
				tt.user_id cod_cajero,
				u.usuario cajero,
				l.id cod_local,
				CONCAT('[', l.cc_id, '] ', l.nombre) local,
				tt.txn_id cod_txn,
				tt.status cod_estado,
				CASE
					WHEN tt.status = 1 THEN 'Completado'
					WHEN tt.status = 2 THEN 'Anulado'
					ELSE ''
				END estado
			FROM tbl_saldo_teleservicios_transaccion tt
			INNER JOIN tbl_locales l ON tt.cc_id = l.cc_id 
			INNER JOIN tbl_zonas z ON l.zona_id = z.id
			INNER JOIN tbl_usuarios u ON tt.user_id = u.id 
			WHERE "

			. $where_fecha_inicio
			. $where_fecha_fin
			. $where_tipo_transaccion
			. $where_cajero
			. $where_local
			. $where_zona
			. $where_estado
			. " ORDER BY tt.id ASC
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

	$where_fecha_inicio=" tt.created_at >= '".$busqueda_fecha_inicio." 00:00:00' ";
	$where_fecha_fin=" AND tt.created_at <= '".$busqueda_fecha_fin." 23:59:59' ";

	$where_tipo_transaccion="";
	if( (int) $busqueda_tipo_transaccion > 0 ){
		$where_tipo_transaccion=" AND tt.tipo_id='".$busqueda_tipo_transaccion."' ";
	}

	$where_cajero="";
	if( (int) $cargo_id === 5 ){//Cajero
		$where_cajero=" AND tt.user_id='".$usuario_id."' ";
	} else {
		if( (int) $busqueda_cajero > 0 ){
			$where_cajero=" AND tt.user_id='".$busqueda_cajero."' ";
		}
	}

	$where_local="";
	if( (int) $cargo_id !== 5 && (int) $busqueda_local > 0){
		$where_local=" AND l.cc_id='". str_pad( ((int)$busqueda_local), 4, "0", STR_PAD_LEFT) ."' ";
	}

	$where_zona="";
	if((int)$busqueda_zona>0){
		$where_zona=" AND l.zona_id='".$busqueda_zona."' ";
	}

	$where_estado="";
	if($busqueda_estado > 0){
		$where_estado=" AND tt.status='".$busqueda_estado."' ";
	}

	$query_1 ="SELECT 
				tt.created_at registro,
				z.nombre zona, 
				CASE
					WHEN tt.tipo_id = 1 THEN 'Depósito'
					WHEN tt.tipo_id = 2 THEN 'Retiro'
					ELSE ''
				END tipo,
				tt.monto monto,
				CONCAT(client_num_doc, ' - ',client_name) cliente,
				l.cc_id,
				l.nombre local,
				u.usuario cajero,
				tt.txn_id cod_txn,
				CASE
					WHEN tt.status = 1 THEN 'Completado'
					WHEN tt.status = 2 THEN 'Anulado'
					ELSE ''
				END estado
			FROM tbl_saldo_teleservicios_transaccion tt
			INNER JOIN tbl_locales l ON tt.cc_id = l.cc_id 
			INNER JOIN tbl_zonas z ON l.zona_id = z.id
			INNER JOIN tbl_usuarios u ON tt.user_id = u.id 
			WHERE "
			. $where_fecha_inicio
			. $where_fecha_fin
			. $where_tipo_transaccion
			. $where_cajero
			. $where_local
			. $where_zona
			. $where_estado
			. " ORDER BY tt.id ASC
		";
	$result["consulta_query"] = $query_1;
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
			"local" => "Local",
			"cajero" => "Cajero",
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
		$file_title = "reporte_saldo_teleservicios_" . $date->getTimestamp() . "_" . $usuario_id;

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