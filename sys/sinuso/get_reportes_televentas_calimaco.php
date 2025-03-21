<?php

$result=array();
include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER TRANSACCIONES 
//*******************************************************************************************************************
//*******************************************************************************************************************

if (isset($_POST["accion"]) && $_POST["accion"]==="listar"){

    $busqueda_fecha_inicio=$_POST["fecha_inicio"];
	$busqueda_fecha_fin=$_POST["fecha_fin"];
	$busqueda_cajero=$_POST["cajero"];
	$busqueda_cliente=$_POST["cliente_id"];
	$busqueda_proveedor=$_POST["proveedor_id"];

	$where_fecha_inicio=" AND DATE(tra.created_at)>= '".$busqueda_fecha_inicio."'";
	$where_fecha_fin=" AND DATE(tra.created_at)<= '".$busqueda_fecha_fin."'";

	$where_cajero="";
	/* if( (int) $cargo_id === 5 ){
		$where_cajero=" AND tra.user_id='".$usuario_id."' ";
	} else {
		if( (int) $busqueda_cajero > 0 ){
			$where_cajero=" AND tra.user_id='".$busqueda_cajero."' ";
		}
	} */

	$where_cliente="";
	if( (int) $busqueda_cliente > 0 ){
		$where_cliente=" AND tra.cliente_id='".$busqueda_cliente."' ";
	}

	$where_proveedor="";
	if( (int) $busqueda_proveedor > 0 ){
		$where_proveedor=" AND tra.api_id='".$busqueda_proveedor."' ";
	}

    $query_1 ="
		SELECT
			IFNULL(REPLACE(REPLACE(tra.observacion_cajero, '\n', ''), '\n', ''), '') observacion_cajero,
			IFNULL(REPLACE(REPLACE(tra.observacion_validador, '\n', ''), '\n', ''), '') observacion_validador,
			IFNULL(REPLACE(REPLACE(tra.observacion_supervisor, '\n', ''), '\n', ''), '') observacion_supervisor,
			tipo_rechazo_id, 
			update_user_at,
			tra.id,
			tra.cliente_id,
			IFNULL( tra.web_id, '' ) web_id,
			tra.tipo_id cod_tipo_transaccion,
			(CASE tra.tipo_id 
				WHEN '1' THEN 'DEPÓSITO' WHEN '2' THEN 'RECARGA WEB' WHEN '3' THEN 'RETONOR RECARGA WEB' ELSE 'NO DEFINIDO' 
			END ) AS tipo_transaccion,
			tra.created_at fecha_hora_registro,
			usu.usuario AS cajero,
			super.usuario AS supervisor,
			(CASE cli.tipo_doc 
				WHEN '0' THEN 'DNI' WHEN '8' THEN 'DNI' WHEN '1' THEN 'CARNÉ EXTRANJERÍA' WHEN '2' THEN 'PASAPORTE' ELSE 'NO DEFINIDO' 
			END) AS tipo_doc,
			IFNULL(cli.num_doc, '') num_doc,
			IFNULL(CONCAT( cli.nombre, ' ', IFNULL( cli.apellido_paterno, '' ), ' ', IFNULL( cli.apellido_materno, '' ) ), '') AS cliente,
			IFNULL(cli.telefono, '') telefono,
			tra.estado AS estado_id,
			IFNULL(cuen.cuenta_descripcion, '') AS cuenta,
			tra.monto AS monto,
			IFNULL(tra.bono_monto, 0) AS bono_monto,
			IFNULL(tra.total_recarga, 0) AS total_recarga,
            IFNULL(tapi.name, 'BC') AS proveedor
		FROM
			tbl_televentas_clientes_transaccion tra
			LEFT JOIN tbl_usuarios usu ON usu.id = tra.user_id
			LEFT JOIN tbl_usuarios super ON super.id = tra.update_user_id
			LEFT JOIN tbl_televentas_clientes cli ON cli.id = tra.cliente_id
			LEFT JOIN tbl_cuentas_apt cuen ON cuen.id = tra.cuenta_id
            LEFT JOIN tbl_televentas_proveedor tapi     ON tapi.id = tra.api_id
		WHERE
		tra.estado = 1 
		".$where_fecha_inicio."
		".$where_fecha_fin."
		".$where_cajero."
		".$where_cliente."
		".$where_proveedor."
		
		ORDER BY tra.id ASC
	";

    $list_query = $mysqli -> query($query_1);

    $list_transaccion = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_transaccion[] = $li;
	}

	$result["http_code"] = 200;
    $result["status"] = "ok";
    $result["result"] = $list_transaccion;

}


//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER TRANSACCIONES X CAJERO --> EXCEL
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"]==="listar_transacciones_calimaco_export_xls") {
	global $mysqli;
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$busqueda_fecha_inicio=$_POST["fecha_inicio"];
	$busqueda_fecha_fin=$_POST["fecha_fin"];
	$busqueda_cajero=$_POST["cajero"];
	$busqueda_cliente=$_POST["cliente_id"];
	$busqueda_proveedor=$_POST["proveedor_id"];


	$where_users_test="";
	if((int)$area_id!==6){
		$where_users_test="	
		AND tra.web_id not in ('3333200', '71938219') 
		AND tra.user_id not in (1, 249, 250, 2572, 3028) 
		";
		if((int)$busqueda_tipo_transaccion===1){
			$where_users_test="	
				AND tra.user_id not in (1, 249, 250, 2572, 3028) 
			";
		}
	}

	$busqueda_fecha_inicio=$_POST["fecha_inicio"];
	$busqueda_fecha_fin=$_POST["fecha_fin"];
	$busqueda_cajero=$_POST["cajero"];
	$busqueda_cliente=$_POST["cliente_id"];
	$busqueda_proveedor=$_POST["proveedor_id"];

	$where_fecha_inicio=" AND DATE(tra.created_at)>= '".$busqueda_fecha_inicio."'";
	$where_fecha_fin=" AND DATE(tra.created_at)<= '".$busqueda_fecha_fin."'";
	
	$where_cajero="";
	if( (int) $cargo_id === 5 ){
		$where_cajero=" AND tra.user_id='".$usuario_id."' ";
	} else {
		if( (int) $busqueda_cajero > 0 ){
			$where_cajero=" AND tra.user_id='".$busqueda_cajero."' ";
		}
	}

	$where_cliente="";
	if( (int) $busqueda_cliente > 0 ){
		$where_cliente=" AND tra.cliente_id='".$busqueda_cliente."' ";
	}

	$where_proveedor="";
	if( (int) $busqueda_proveedor > 0 ){
		$where_proveedor=" AND tra.api_id='".$busqueda_proveedor."' ";
	}

	$query_1 ="
		SELECT
			IFNULL(ttra.nombre, '') tipo_transaccion,
			IFNULL(CONCAT( cli.nombre, ' ', IFNULL( cli.apellido_paterno, '' ), ' ', IFNULL( cli.apellido_materno, '' ) ), '') AS cliente,
			IFNULL(tapi.name, 'BC') AS proveedor,
			tra.monto AS monto,
			tra.created_at fecha_hora_registro
		FROM
			tbl_televentas_clientes_transaccion tra
			LEFT JOIN tbl_usuarios usu ON usu.id = tra.user_id
			LEFT JOIN tbl_televentas_clientes cli ON cli.id = tra.cliente_id
			LEFT JOIN tbl_cuentas_apt cuen ON cuen.id = tra.cuenta_id
			LEFT JOIN tbl_televentas_proveedor tapi     ON tapi.id = tra.api_id
			LEFT JOIN tbl_televentas_clientes_tipo_transaccion ttra ON tra.tipo_id = ttra.id
		WHERE
		tra.estado = 1 
		".$where_users_test ." 
		".$where_fecha_inicio ." 
		".$where_fecha_fin ." 
		".$where_cajero ." 
		".$where_cliente ."
		".$where_proveedor."
		ORDER BY tra.id ASC
	";
	//$result["consulta_query"] = $query_1;
	$list_query=$mysqli->query($query_1);
	$result_data=array();
	$monto_total=0;
	$recarga_total=0;
	while ($li=$list_query->fetch_assoc()) {
		$result_data[]=$li;
		$monto_total+=$li['monto'];
	}


	if (!$result_data) {
		echo json_encode([
			"error" => "Export error"
		]);
		exit;
	}

	$result_totales=array();
	$result_totales['tipo_transaccion']='';
	$result_totales['cliente']='';
	$result_totales['proveedor']='';
	$result_totales['monto']=$monto_total;
	$result_totales['fecha_hora_registro']='';
	/* $result_totales['cajero']='';
	$result_totales['telefono']='';
	$result_totales['tipo_doc']='';
	
	$result_totales['web_id']='';
	
	$result_totales['cuenta']='';
	
	$result_totales['bono_monto']='';
	$result_totales['total_recarga']=$recarga_total; */

	$result_data[]=$result_totales;

	$headers = [
		"tipo_transaccion" => "Tipo Transaccion",
		"cliente" => "Nombre de Cliente",
		"proveedor" => "Proveedor",
		"monto" => "Monto",
		"fecha_hora_registro" => "Fecha y Hora Registro"
		/* "cajero" => "Usuario",
		"telefono" => "Telefono",
		"tipo_doc" => "Tipo de Documento",
		"num_doc" => "Numero de Documento",
		"web_id" => "WEB-ID",
		
		"cuenta" => "Cuenta",
		
		"bono_monto" => "Bono",
		"total_recarga" => "Recarga" */
	];
	array_unshift($result_data, $headers);

	require_once '../phpexcel/classes/PHPExcel.php';
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getActiveSheet()->fromArray($result_data, null, 'A1');
	$date = new DateTime();
	$file_title = "reporte_televentas_calimaco" . $date->getTimestamp();

	if (!file_exists('/var/www/html/export/files_exported/reporte_calimaco/')) {
		mkdir('/var/www/html/export/files_exported/reporte_calimaco/', 0777, true);
	}

	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="' . $file_title . '.xls"');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$excel_path = '/var/www/html/export/files_exported/reporte_calimaco/' . $file_title . '.xls';
	$excel_path_download = '/export/files_exported/reporte_calimaco/' . $file_title . '.xls';
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