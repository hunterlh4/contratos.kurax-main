<?php 	
$result=array();
include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



// ****************************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"]==="listar_pagos_clientes") {
	
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$tipo_busqueda             = $_POST["tipo_busqueda"];
	$busqueda_fecha_inicio     = $_POST["fecha_inicio"];
	$busqueda_fecha_fin        = $_POST["fecha_fin"];
	$busqueda_pagador          = $_POST["pagador"];
	$busqueda_comprobante      = $_POST["comprobante"];
	$busqueda_razon      	   = $_POST["razon"];
	$busqueda_tipo_operacion   = $_POST["tipo_operacion"];
	$busqueda_motivo_dev       = $_POST["motivo_dev"];
	$busqueda_estado_solicitud = $_POST["estado_solicitud"];

	$where_users_test="";
	if((int)$area_id !== 6) {// diferente de sistemas
		$where_users_test="	
			AND IFNULL(tra.web_id, '') not in ('3333200', '71938219') 
			AND tra.user_id not in (1, 249, 250, 2572, 3028) 
		";
	}

	$order = "";
	$order = 'ORDER BY tra.id ASC';

	$where_fecha = "";
	if ((int) $tipo_busqueda === 1){ // POR FECHA DE REGISTRO DE SOLICITUD
		$where_fecha = " 
			AND DATE(tra.created_at) >= '".$busqueda_fecha_inicio."' 
			AND DATE(tra.created_at) <= '".$busqueda_fecha_fin."' 
		";
	} else if ((int) $tipo_busqueda === 2){ // POR FECHA DE PAGO
		$where_fecha = " 
			AND DATE(tra2.created_at) >= '".$busqueda_fecha_inicio."' 
			AND DATE(tra2.created_at) <= '".$busqueda_fecha_fin."' 
		";
	}

	if(isset($_POST["search"]["value"])){		
		$busqueda_nombre = $_POST["search"]["value"];
		if ($busqueda_nombre !="") {
			$nombre_busqueda = '
			AND (
			tra2.created_at LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.created_at LIKE "%'.$_POST["search"]["value"].'%"
			OR c.num_doc LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.monto LIKE "%'.$_POST["search"]["value"].'%"			
			OR tra.comision_monto LIKE "%'.$_POST["search"]["value"].'%"
			OR b.nombre LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.observacion_validador LIKE "%'.$_POST["search"]["value"].'%"
			OR UPPER(CONCAT(IFNULL(apt.nombre,""), " ",IFNULL( apt.apellido_paterno, "" ), " ", IFNULL( apt.apellido_materno, "" ))) LIKE "%'.$_POST["search"]["value"].'%"
			OR UPPER(CONCAT(IFNULL(apt2.nombre,""), " ",IFNULL( apt2.apellido_paterno, "" ), " ", IFNULL( apt2.apellido_materno, "" ))) LIKE "%'.$_POST["search"]["value"].'%"
			OR UPPER(CONCAT(IFNULL(c.nombre, ""), " ", IFNULL(c.apellido_paterno, ""), " ", IFNULL(c.apellido_materno, ""))) LIKE "%'.$_POST["search"]["value"].'%"
			OR CASE WHEN IFNULL(tra.id_operacion_retiro,0) IN (0, 1) THEN "TELESERVICIOS" ELSE "TIENDA" end LIKE "%'.$_POST["search"]["value"].'%"
			OR CASE WHEN tra.enviar_comprobante = 1 THEN "ENVIADO" ELSE "PENDIENTE" END LIKE "%'.$_POST["search"]["value"].'%"';
			$nombre_busqueda .= ')';
		} else {
			$nombre_busqueda = "";
		}
	}

	if(isset($_POST["length"]))
	{
		if($_POST["length"] != -1){
			$limit = 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
		}	
	}

	$where_cuenta = "";
	if (isset($_POST["cuenta"]) && $_POST["cuenta"] !== '' && $_POST["cuenta"] !== null) {
		$where_cuenta = " AND b.id IN ( " . implode(",",$_POST["cuenta"]) . " ) " ;
	}

	$where_encargado = "";
	if($busqueda_pagador != 0){
		$where_encargado = " AND tra.user_id='" . $busqueda_pagador . "' ";	
	}
	$where_comprobante = "";
	if($busqueda_comprobante != 0 && $busqueda_comprobante != 999){
		if($busqueda_comprobante == 2){$busqueda_comprobante = 0;}
		$where_comprobante = " AND IFNULL(tra.enviar_comprobante,0)='" . $busqueda_comprobante . "' ";	
	}

	$where_razon = "";
	if($busqueda_razon != 0){
		if($busqueda_razon == 1){
			$where_razon = " AND IFNULL(tra.id_operacion_retiro, 0) in (0, 1)";		
		}else{
			$where_razon = " AND IFNULL(tra.id_operacion_retiro, 0) = '" . $busqueda_razon . "' ";		
		}
	}

	$where_tipo_operacion = "";
	if($busqueda_tipo_operacion != 0){
		$where_tipo_operacion = " AND IFNULL(tra.tipo_operacion, 0) = " . $busqueda_tipo_operacion;
	}

	$where_motivo_dev = "";
	if($busqueda_motivo_dev != 0){
		$where_motivo_dev = " AND IFNULL(tra.id_motivo_dev, 0) = " . $busqueda_motivo_dev;
	}

	$where_estado_solicitud = "";
	if($busqueda_estado_solicitud == 0 || $busqueda_estado_solicitud == 999){
		$where_estado_solicitud = " AND IFNULL(tra.estado, 0) IN (1,2,5,6)";
	}else if($busqueda_estado_solicitud == 1){
		$where_estado_solicitud = " AND IFNULL(tra.estado, 0) IN (2)";
	}else if($busqueda_estado_solicitud == 2){
		$where_estado_solicitud = " AND IFNULL(tra.estado, 0) IN (1,5,6)";
	}


	// Lista
	$query_1 ="
		SELECT 
			tra.created_at fecha_hora_registro,
			IFNULL(tra2.created_at,tra.created_at) fecha_hora_pago,
			CASE
				WHEN tra2.user_id IS NULL 
				THEN
					UPPER(CONCAT(IFNULL(apt.nombre,''), ' ',IFNULL( apt.apellido_paterno, '' ), ' ', IFNULL( apt.apellido_materno, '' )))
				ELSE
					UPPER(CONCAT(IFNULL(apt2.nombre,''), ' ',IFNULL( apt2.apellido_paterno, '' ), ' ', IFNULL( apt2.apellido_materno, '' )))  
				END pagador,
			UPPER(CONCAT(IFNULL(apt.nombre,''), ' ',IFNULL( apt.apellido_paterno, '' ), ' ', IFNULL( apt.apellido_materno, '' ))) asesor,
			IFNULL(c.num_doc, '') num_doc,
			UPPER(CONCAT(IFNULL(c.nombre, ''), ' ', IFNULL(c.apellido_paterno, ''), ' ', IFNULL(c.apellido_materno, ''))) cliente,
			IFNULL(tra.monto, 0) monto,
			IFNULL(tra.comision_monto, 0) comision,
			IFNULL(b.nombre, '') banco,
		    tra.observacion_validador observacion_pagador,
		    CASE WHEN IFNULL(tra.id_operacion_retiro,0) IN (0, 1) THEN 'TELESERVICIOS' ELSE 'TIENDA' end razon,
            CASE WHEN tra.enviar_comprobante = 1 THEN 'ENVIADO' ELSE 'PENDIENTE' END enviar_comprobante,
            CASE WHEN IFNULL(tra.tipo_operacion, 0) IN (0,1) THEN 'PAGO' ELSE 'DEVOLUCION' END tipo_operacion,
            IFNULL(tmv.descripcion, '') motivo_devolucion,
            IFNULL(val_sup.usuario,'') validado_por,
            IFNULL(esr.descripcion, '') estado_solicitud,
			IFNULL(tra.observacion_supervisor, '') link_atencion
		FROM tbl_televentas_clientes_transaccion tra
			INNER JOIN tbl_televentas_clientes c ON tra.cliente_id = c.id
			INNER JOIN tbl_televentas_clientes_cuenta cc ON tra.cuenta_id = cc.id
			INNER JOIN tbl_bancos b ON cc.banco_id = b.id
		    LEFT JOIN tbl_usuarios val ON val.id = tra.user_id
		    LEFT JOIN tbl_personal_apt apt ON apt.id = val.personal_id

		    LEFT JOIN tbl_caja caj ON caj.id = tra.turno_id
		    LEFT JOIN tbl_televentas_clientes_transaccion tra2 ON tra2.transaccion_id = tra.id
		    LEFT JOIN tbl_usuarios val2 ON val2.id = tra2.user_id
		    LEFT JOIN tbl_personal_apt apt2 ON apt2.id = val2.personal_id

		    LEFT JOIN tbl_televentas_tipo_motivo_devolucion tmv ON tra.id_motivo_dev = tmv.id
		    LEFT JOIN tbl_usuarios val_sup ON val_sup.id = tra.user_valid_id
		    LEFT JOIN tbl_televentas_estado_solicitud_retiro esr ON tra.estado = esr.id
		WHERE 
		(
			(tra.tipo_id = 9 and IFNULL(tra.caja_vip,0) = 0) 
			OR (tra.tipo_id = 28 and IFNULL(tra.caja_vip,0) = 0) 
			OR (tra.tipo_id = 11 and IFNULL(tra.caja_vip,0) = 1)
			OR (tra.tipo_id = 29 and IFNULL(tra.caja_vip,0) = 1)
		)  
		".$where_users_test ." 
		".$where_fecha ." 
		".$where_cuenta."
		".$where_encargado."
		".$where_comprobante."
		".$where_razon."
		".$nombre_busqueda."
		".$where_tipo_operacion."
		".$where_motivo_dev."
		".$where_estado_solicitud."
		".$order ."
		".$limit;
	//echo $query_1;
	$result["consulta_query"] = $query_1;
	$list_query = $mysqli->query($query_1);
	$list_transaccion = array();
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
			INNER JOIN tbl_televentas_clientes c ON tra.cliente_id = c.id
			INNER JOIN tbl_televentas_clientes_cuenta cc ON tra.cuenta_id = cc.id
			INNER JOIN tbl_bancos b ON cc.banco_id = b.id
		    LEFT JOIN tbl_usuarios val ON val.id = tra.user_id
		    LEFT JOIN tbl_personal_apt apt ON apt.id = val.personal_id

		    #LEFT JOIN tbl_usuarios val3 ON val3.id = tra.supervisor_id
			#LEFT JOIN tbl_personal_apt apt3 ON apt3.id = val3.personal_id
		    
			#LEFT JOIN tbl_usuarios val4 ON val4.id = tra.pagador_id
			#LEFT JOIN tbl_personal_apt apt4 ON apt4.id = val4.personal_id

		    LEFT JOIN tbl_caja caj ON caj.id = tra.turno_id
		    LEFT JOIN tbl_televentas_clientes_transaccion tra2 ON tra2.transaccion_id = tra.id
		    LEFT JOIN tbl_usuarios val2 ON val2.id = tra2.user_id
		    LEFT JOIN tbl_personal_apt apt2 ON apt2.id = val2.personal_id
		WHERE 
		(
			(tra.tipo_id = 9 and IFNULL(tra.caja_vip,0) = 0) 
			OR (tra.tipo_id = 28 and IFNULL(tra.caja_vip,0) = 0) 
			OR (tra.tipo_id = 11 and IFNULL(tra.caja_vip,0) = 1)
			OR (tra.tipo_id = 29 and IFNULL(tra.caja_vip,0) = 1)
		)  
		".$where_users_test ." 
		".$where_fecha ." 
		".$where_cuenta."
		".$where_encargado."
		".$where_comprobante."
		".$where_razon."
		".$nombre_busqueda."
		".$where_tipo_operacion."
		".$where_motivo_dev."
		".$where_estado_solicitud."
		".$nombre_busqueda;
	//echo $query_1;
	//$result["consulta_query_COUNT"] = $query_COUNT;
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
	}
}


//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER TRANSACCIONES X CAJERO --> EXCEL
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"]==="listar_transacciones_pagos_export_xls") {
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$tipo_busqueda             = $_POST["tipo_busqueda"];
	$busqueda_fecha_inicio     = $_POST["fecha_inicio"];
	$busqueda_fecha_fin        = $_POST["fecha_fin"];
	$busqueda_pagador          = $_POST["pagador"];
	$busqueda_comprobante      = $_POST["comprobante"];
	$busqueda_razon      	   = $_POST["razon"];
	$busqueda_tipo_operacion   = $_POST["tipo_operacion"];
	$busqueda_motivo_dev       = $_POST["motivo_dev"];
	$busqueda_estado_solicitud = $_POST["estado_solicitud"];

	$where_users_test="";
	if((int)$area_id !== 6) {// diferente de sistemas
		$where_users_test="	
			AND IFNULL(tra.web_id, '') not in ('3333200', '71938219') 
			AND tra.user_id not in (1, 249, 250, 2572, 3028) 
		";
	}

	$order = "";
	
	$order = 'ORDER BY tra.id ASC';
	$where_fecha = "";
	if ((int) $tipo_busqueda === 1){ // POR FECHA DE REGISTRO DE SOLICITUD
		$where_fecha = " 
			AND DATE(tra.created_at) >= '".$busqueda_fecha_inicio."' 
			AND DATE(tra.created_at) <= '".$busqueda_fecha_fin."' 
		";
	} else if ((int) $tipo_busqueda === 2){ // POR FECHA DE PAGO
		$where_fecha = " 
			AND DATE(tra2.created_at) >= '".$busqueda_fecha_inicio."' 
			AND DATE(tra2.created_at) <= '".$busqueda_fecha_fin."' 
		";
	}

	if(isset($_POST["length"]))
	{
		if($_POST["length"] != -1){
			$limit = 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
		}	
	}

	$where_cuenta = "";
	if (isset($_POST["cuenta"]) && $_POST["cuenta"] !== '' && $_POST["cuenta"] !== null) {
		$where_cuenta = " AND b.id IN ( " . implode(",",$_POST["cuenta"]) . " ) " ;
	}

	$where_encargado = "";
	if($busqueda_pagador != 0){
		$where_encargado = " AND tra.user_id='" . $busqueda_pagador . "' ";	
	}

	$where_comprobante = "";
	if($busqueda_comprobante != 0 && $busqueda_comprobante != 999){
		if($busqueda_comprobante == 2){$busqueda_comprobante = 0;}
		$where_comprobante = " AND IFNULL(tra.enviar_comprobante,0)='" . $busqueda_comprobante . "' ";	
	}

	$where_razon = "";
	if($busqueda_razon != 0){
		if($busqueda_razon == 1){
			$where_razon = " AND IFNULL(tra.id_operacion_retiro, 0) in (0, 1)";		
		}else{
			$where_razon = " AND IFNULL(tra.id_operacion_retiro, 0) = '" . $busqueda_razon . "' ";		
		}
	}

	$where_tipo_operacion = "";
	if($busqueda_tipo_operacion != 0){
		$where_tipo_operacion = " AND IFNULL(tra.tipo_operacion, 0) = " . $busqueda_tipo_operacion;
	}

	$where_motivo_dev = "";
	if($busqueda_motivo_dev != 0){
		$where_motivo_dev = " AND IFNULL(tra.id_motivo_dev, 0) = " . $busqueda_motivo_dev;
	}

	$where_estado_solicitud = "";
	if($busqueda_estado_solicitud == 0){
		$where_estado_solicitud = " AND IFNULL(tra.estado, 0) IN (1,2,5,6)";
	}else if($busqueda_estado_solicitud == 1){
		$where_estado_solicitud = " AND IFNULL(tra.estado, 0) IN (2)";
	}else if($busqueda_estado_solicitud == 2){
		$where_estado_solicitud = " AND IFNULL(tra.estado, 0) IN (1,5,6)";
	}

	// Lista
	$query_1 ="
		SELECT 
			tra.created_at fecha_hora_registro,
			tra2.created_at fecha_hora_pago,
			UPPER(CONCAT(IFNULL(apt2.nombre,''), ' ',IFNULL( apt2.apellido_paterno, '' ), ' ', IFNULL( apt2.apellido_materno, '' )))  pagador,
			UPPER(CONCAT(IFNULL(apt.nombre,''), ' ',IFNULL( apt.apellido_paterno, '' ), ' ', IFNULL( apt.apellido_materno, '' ))) asesor,
			IFNULL(c.num_doc, '') num_doc,
			UPPER(CONCAT(IFNULL(c.nombre, ''), ' ', IFNULL(c.apellido_paterno, ''), ' ', IFNULL(c.apellido_materno, ''))) cliente,
			IFNULL(tra.comision_monto, 0) comision,
			IFNULL(tra.monto, 0) monto,
			IFNULL(b.nombre, '') banco,
            IFNULL(esr.descripcion, '') estado_solicitud,
			IFNULL(tra.observacion_supervisor, '') link_atencion,
		    tra.observacion_validador observacion_pagador,
		    CASE WHEN IFNULL(tra.id_operacion_retiro,0) IN (0, 1) THEN 'TELESERVICIOS' ELSE 'TIENDA' end razon,
            CASE WHEN tra.enviar_comprobante = 1 THEN 'ENVIADO' ELSE 'PENDIENTE' END enviar_comprobante,
            CASE WHEN IFNULL(tra.tipo_operacion, 0) IN (0,1) THEN 'PAGO' ELSE 'DEVOLUCION' END tipo_operacion,
            IFNULL(tmv.descripcion, '') motivo_devolucion,
            IFNULL(val_sup.usuario,'') validado_por
		FROM tbl_televentas_clientes_transaccion tra
			INNER JOIN tbl_televentas_clientes c ON tra.cliente_id = c.id
			INNER JOIN tbl_televentas_clientes_cuenta cc ON tra.cuenta_id = cc.id
			INNER JOIN tbl_bancos b ON cc.banco_id = b.id
		    LEFT JOIN tbl_usuarios val ON val.id = tra.user_id
		    LEFT JOIN tbl_personal_apt apt ON apt.id = val.personal_id

		    LEFT JOIN tbl_caja caj ON caj.id = tra.turno_id
		    LEFT JOIN tbl_televentas_clientes_transaccion tra2 ON tra2.transaccion_id = tra.id
		    LEFT JOIN tbl_usuarios val2 ON val2.id = tra2.user_id
		    LEFT JOIN tbl_personal_apt apt2 ON apt2.id = val2.personal_id

		    LEFT JOIN tbl_televentas_tipo_motivo_devolucion tmv ON tra.id_motivo_dev = tmv.id
		    LEFT JOIN tbl_usuarios val_sup ON val_sup.id = tra.user_valid_id
		    LEFT JOIN tbl_televentas_estado_solicitud_retiro esr ON tra.estado = esr.id
		WHERE 
		(
			(tra.tipo_id = 9 and IFNULL(tra.caja_vip,0) = 0) 
			OR (tra.tipo_id = 28 and IFNULL(tra.caja_vip,0) = 0) 
			OR (tra.tipo_id = 11 and IFNULL(tra.caja_vip,0) = 1)
			OR (tra.tipo_id = 29 and IFNULL(tra.caja_vip,0) = 1)
		)  
		".$where_users_test ." 
		".$where_fecha ." 
		".$where_cuenta."
		".$where_encargado."
		".$where_comprobante."
		".$where_razon."
		".$where_tipo_operacion."
		".$where_motivo_dev."
		".$where_estado_solicitud."
		".$order;
	//echo $query_1;
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
			"fecha_hora_registro" => "Fecha Solicitud",
			"fecha_hora_pago" => "Fecha Pago",
			"pagador" => "Pagador",
			"asesor" => "Asesor",
			"num_doc" => "Num Doc",
			"cliente" => "Cliente",
			"comision" => "Comision",
			"monto" => "Monto",
			"banco" => "Banco",
			"estado_solicitud" => "Estado Solicitud",
			'link_atencion' => "Link Atención",
			"observacion_pagador" => "Obs Pagador",
			"razon" => "Razon",
			"enviar_comprobante" => "Comprobante",
			"tipo_operacion" => "Tipo",
			"motivo_devolucion" => "Motivo Devolución",
			"validado_por" => "Aprobado Por"
		];
		array_unshift($result_data, $headers);

		ini_set('display_errors', 0);
		ini_set('display_startup_errors', 0);
		error_reporting(0);

		require_once '../phpexcel/classes/PHPExcel.php';
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getActiveSheet()->fromArray($result_data, null, 'A1');
		$date = new DateTime();
		$file_title = "reporte_televentas_pagos_clientes_" . $date->getTimestamp();

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

// ****************************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"]==="listar_totales_pagos_clientes") {
	
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$tipo_busqueda             = $_POST["tipo_busqueda"];
	$busqueda_fecha_inicio     = $_POST["fecha_inicio"];
	$busqueda_fecha_fin        = $_POST["fecha_fin"];
	$busqueda_pagador          = $_POST["pagador"];
	$busqueda_comprobante      = $_POST["comprobante"];
	$busqueda_razon      	   = $_POST["razon"];
	$busqueda_tipo_operacion   = $_POST["tipo_operacion"];
	$busqueda_motivo_dev       = $_POST["motivo_dev"];
	$busqueda_estado_solicitud = $_POST["estado_solicitud"];

	$where_users_test="";
	if((int)$area_id !== 6) {// diferente de sistemas
		$where_users_test="	
			AND IFNULL(tra.web_id, '') not in ('3333200', '71938219') 
			AND tra.user_id not in (1, 249, 250, 2572, 3028) 
		";
	}

	$order = "";
	
	$order = 'ORDER BY tra.id ASC';
	$where_fecha = "";

	if ((int) $tipo_busqueda === 1){ // POR FECHA DE REGISTRO DE SOLICITUD
		$where_fecha = " 
			AND DATE(tra.created_at) >= '".$busqueda_fecha_inicio."' 
			AND DATE(tra.created_at) <= '".$busqueda_fecha_fin."' 
		";
	} else if ((int) $tipo_busqueda === 2){ // POR FECHA DE PAGO
		$where_fecha = " 
			AND DATE(tra2.update_user_at) >= '".$busqueda_fecha_inicio."' 
			AND DATE(tra2.update_user_at) <= '".$busqueda_fecha_fin."' 
		";
	}

	$where_cuenta = "";
	if (isset($_POST["cuenta"]) && $_POST["cuenta"] !== '' && $_POST["cuenta"] !== null) {
		$where_cuenta = " AND b.id IN ( " . implode(",",$_POST["cuenta"]) . " ) " ;
	}

	$where_encargado = "";
	if($busqueda_pagador != 0){
		$where_encargado = " AND tra.user_id='" . $busqueda_pagador . "' ";	
	}

	$where_comprobante = "";
	if($busqueda_comprobante != 0){
		if($busqueda_comprobante == 2){$busqueda_comprobante = 0;}
		$where_comprobante = " AND IFNULL(tra.enviar_comprobante,0)='" . $busqueda_comprobante . "' ";	
	}
	

	$where_razon = "";
	if($busqueda_razon != 0){
		if($busqueda_razon == 1){
			$where_razon = " AND IFNULL(tra.id_operacion_retiro, 0) in (0, 1)";		
		}else{
			$where_razon = " AND IFNULL(tra.id_operacion_retiro, 0) = '" . $busqueda_razon . "' ";		
		}
	}

	$where_tipo_operacion = "";
	if($busqueda_tipo_operacion != 0){
		$where_tipo_operacion = " AND IFNULL(tra.tipo_operacion, 0) = " . $busqueda_tipo_operacion;
	}

	$where_motivo_dev = "";
	if($busqueda_motivo_dev != 0){
		$where_motivo_dev = " AND IFNULL(tra.id_motivo_dev, 0) = " . $busqueda_motivo_dev;
	}


	$where_estado_solicitud = "";
	if($busqueda_estado_solicitud == 0){
		$where_estado_solicitud = " AND IFNULL(tra.estado, 0) IN (1,2,5,6)";
	}else if($busqueda_estado_solicitud == 1){
		$where_estado_solicitud = " AND IFNULL(tra.estado, 0) IN (2)";
	}else if($busqueda_estado_solicitud == 2){
		$where_estado_solicitud = " AND IFNULL(tra.estado, 0) IN (1,5,6)";
	}


	// Lista
	$query_1 ="
		SELECT 
			IFNULL(SUM(IF(IFNULL(tra.tipo_operacion,0) in (0,1), 1, 0)), 0) AS cant_pagos,
			IFNULL(SUM(IF(IFNULL(tra.tipo_operacion,0) in (0,1), tra.comision_monto, 0)), 0) AS total_comision,
			IFNULL(SUM(IF(IFNULL(tra.tipo_operacion,0) in (0,1), tra.monto, 0)), 0) AS total_monto,
			IFNULL(SUM(IF(tra.tipo_operacion = 2, 1, 0)), 0) AS cant_dev,
			IFNULL(SUM(IF(tra.tipo_operacion = 2, tra.monto, 0)), 0) AS total_dev
		FROM tbl_televentas_clientes_transaccion tra
			INNER JOIN tbl_televentas_clientes c ON tra.cliente_id = c.id
			INNER JOIN tbl_televentas_clientes_cuenta cc ON tra.cuenta_id = cc.id
			INNER JOIN tbl_bancos b ON cc.banco_id = b.id
			LEFT JOIN tbl_usuarios val ON val.id = tra.update_user_id
			LEFT JOIN tbl_personal_apt apt ON apt.id = val.personal_id
			LEFT JOIN tbl_usuarios val2 ON val2.id = tra.user_id
			LEFT JOIN tbl_personal_apt apt2 ON apt2.id = val2.personal_id
			LEFT JOIN tbl_caja caj ON caj.id = tra.turno_id
		WHERE 
		(
			(tra.tipo_id = 9 and tra.estado = 2 and IFNULL(tra.caja_vip,0) = 0) 
			OR (tra.tipo_id = 28 and tra.estado = 2 and IFNULL(tra.caja_vip,0) = 0) 
			OR (tra.tipo_id = 11 and tra.estado = 2 and IFNULL(tra.caja_vip,0) = 1)
			OR (tra.tipo_id = 29 and tra.estado = 2 and IFNULL(tra.caja_vip,0) = 1)
		)  
		".$where_users_test ." 
		".$where_fecha ." 
		".$where_cuenta."
		".$where_encargado."
		".$where_comprobante."
		".$where_razon."
		".$where_tipo_operacion."
		".$where_motivo_dev."
		".$where_estado_solicitud."
		".$order;
	//echo $query_1;
	$result["consulta_query"] = $query_1;
	$list_query = $mysqli->query($query_1);
	$list_transaccion = array();
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li=$list_query->fetch_assoc()) {
			$list_transaccion[]=$li;
		}
	}

	if(count($list_transaccion)===0){
		$result["http_code"] = 204;
		$result["status"] ="No hay transacciones.";
		$result["data"] =$list_transaccion;
	} elseif(count($list_transaccion)>0){
		//$result["draw"] = intval($_POST["draw"]);
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["data"] = $list_transaccion;
	} else {
		$result["http_code"] = 400;
		$result["status"] ="Ocurrió un error al consultar transacciones.";
		$result["data"] =$list_transaccion;
	}
}



echo json_encode($result);

?>