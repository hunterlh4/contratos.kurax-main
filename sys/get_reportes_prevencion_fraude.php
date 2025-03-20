<?php
$result = array();
include("db_connect.php");
include("sys_login.php");
date_default_timezone_set("America/Lima");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER REGISTROS
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "sec_rpt_fv_listar_registros") {
	$busqueda_fecha_inicio		= $_POST["fecha_inicio"];
	$busqueda_fecha_fin			= $_POST["fecha_fin"];
	$busqueda_tipo				= $_POST["tipo"];
	$busqueda_usuario			= $_POST["usuario"];
	$busqueda_cuenta      		= $_POST["cuenta"];
	$busqueda_movimiento   		= $_POST["movimiento"];

	$usuario_id = $login["id"];
	$area_id = $login ? $login['area_id'] : 0;

	$where_users_test="";
	if((int)$area_id !== 6) {// diferente de sistemas
		$where_users_test="	
			AND IFNULL(tra.web_id, '') not in ('3333200', '71938219')			 
			AND IFNULL(tra.user_id, '') not in (1, 249, 250, 2572, 3028)
		";
	}

	$where_tipo = '';
	$where_movimiento = '';
	if ((int)$busqueda_tipo > 0) {
		
	} else {
		//$where_tipo = '';
	}

	if((int)$busqueda_movimiento != 0){
		if($busqueda_movimiento == 26){
			$where_movimiento = " AND tra.tipo_id = " . $busqueda_movimiento . " AND tra.estado = 1";
		}else{
			$where_movimiento = " AND tra.tipo_id = " . $busqueda_movimiento . " AND tra.estado = 2";
		}
	}else{
		if ((int)$busqueda_tipo == 1){
			$where_tipo = ' AND tra.tipo_id = 26 AND tra.estado = 1';
		}elseif ((int)$busqueda_tipo == 9) {
			$where_tipo = ' AND tra.tipo_id IN (11,24,29) AND tra.estado = 2';
		}
	}

	$where_usuario = '';
	if ((int)$busqueda_usuario > 0) {
		$where_usuario = ' AND tra.user_id = ' . $busqueda_usuario;
	} else {
		$where_usuario = '';
	}

	//$where_fecha_inicio = " AND DATE(tra.created_at)>= '" . $busqueda_fecha_inicio . "'";
	//$where_fecha_fin = " AND DATE(tra.created_at)<= '" . $busqueda_fecha_fin . "'";

	$where_fecha = " AND DATE(tra2.created_at) >= '" . $busqueda_fecha_inicio . "' AND DATE(tra2.created_at) <= '" . $busqueda_fecha_fin . "' ";

	$where_cuenta = '';

	if((int)$busqueda_tipo == 1){
		if($busqueda_cuenta != 0){
			$where_cuenta = ' AND cuen.id = ' .$busqueda_cuenta;
		}
	}else if ((int)$busqueda_tipo == 9){
		if($busqueda_cuenta != 0){
			$where_cuenta = ' AND (ban_2.id = ' .$busqueda_cuenta;
			$where_cuenta .= ' OR ban_3.id = ' .$busqueda_cuenta . ')';
		}
	}

	$where_estados = '';
	if((int)$busqueda_movimiento == 0 && (int)$busqueda_tipo == 0){
		$where_estados = '
		AND ((tra.tipo_id = 26 AND tra.estado = 1) OR (tra.tipo_id IN (11,24,29) AND tra.estado = 2))';
	}


	$query_1 = "
		SELECT 
			tra.id,
			tra.cliente_id,
			tra.tipo_id,
			tra.created_at fecha_hora_registro,
			DATE(tra2.created_at) fecha_registro, 
			TIME(tra2.created_at) hora_registro,
			DATE(tra.created_at) fecha_abono, 
			TIME(tra.created_at) hora_abono,
			DATE(tra.registro_deposito) fecha_filtro, 
			TIME(tra.registro_deposito) hora_filtro,
		    IFNULL(usu.usuario,'') AS cajero,
			IFNULL(cli.num_doc,'') AS num_doc,
			IFNULL(CONCAT( cli.nombre, ' ', IFNULL( cli.apellido_paterno, '' ), ' ', IFNULL( cli.apellido_materno, '' ) ), '') AS cliente,
			UPPER(IFNULL(tit_abon.nombre_apellido_titular, IFNULL(CONCAT( cli.nombre, ' ', IFNULL( cli.apellido_paterno, '' ), ' ', IFNULL( cli.apellido_materno, '' ) ), ''))) titular_abono,
		    CASE 
				WHEN tra.tipo_id = 26 THEN 'Ingreso' 
				WHEN tra.tipo_id IN (11,24,29) THEN 'Salida'
				ELSE '' 
			END tipo_transaccion,
		    IFNULL(ttra.nombre, '') movimiento,
		    CASE 
				WHEN tra.tipo_id IN (26) THEN IFNULL(ban.nombre,'') 
				WHEN tra.tipo_id IN (11,29) THEN IFNULL(ban_2.nombre,'') 
				WHEN tra.tipo_id = 24 THEN IFNULL(ban_3.nombre,'') 
				ELSE ''
			END Banco,
		    CASE 
				WHEN (tra.tipo_id IN (11,24,29)) THEN IFNULL(pr.nombre, '') 
				ELSE '' 
			END Banco_pago,
		    CASE 
				WHEN (tra.tipo_id IN (11,24,29)) THEN IFNULL(pr.nombre, '') 
				WHEN tra.tipo_id IN (26) THEN IFNULL(cuen.cuenta_descripcion, '') 
				ELSE '' 
			END AS banco_ts,
		    CASE 
				WHEN tra.tipo_id IN (26) THEN IFNULL(cuen.sigla, '') 
				WHEN tra.tipo_id IN (11,24,29) THEN IFNULL(pr.sigla, '') 
				ELSE ''
			END AS sigla_cuenta,
		    CASE 
				WHEN tra.tipo_id IN (26) THEN IFNULL(cuen.cuenta_descripcion, '') 
				WHEN tra.tipo_id IN (11,29) THEN IFNULL(cc.cuenta_num, '') 
				WHEN tra.tipo_id = 24 THEN IFNULL(tcc.cuenta_num, '') 
				ELSE ''
			END AS cuenta,
		    CASE
				WHEN tra.tipo_id IN (26) THEN IFNULL(cuen.cuenta_inter_num,'')
				WHEN tra.tipo_id IN (11,29) THEN IFNULL(cc.cci,'')
				WHEN tra.tipo_id = 24 THEN IFNULL(tcc.cci,'')
				ELSE ''
			END cci,
		    tra.estado AS estado_id,
			CASE 
				WHEN tra.tipo_id IN (26) AND tra.estado = 1 THEN 'Aprobado'
				WHEN tra.tipo_id IN (11,24,29) AND tra.estado = 2 THEN 'Pagado'
				ELSE ''
			END estado,
		    CASE
				WHEN ( cuen.id NOT IN (3,4,14,15,22) || pr.id NOT IN (8,9,13,14) ) THEN TRIM(LEADING '0' FROM IFNULL(tra.num_operacion, ''))
				ELSE IFNULL(tra.num_operacion, '')
			END AS num_operacion,
		    CONCAT(
				CASE 
					WHEN tra.tipo_id IN (26) THEN IFNULL(cuen.sigla, '') 
					WHEN tra.tipo_id IN (11, 24, 29) THEN IFNULL(pr.sigla, '') 
					ELSE ''
				END,
				'-',
				CASE
					WHEN ( cuen.id NOT IN (3,4,14,15,22) || pr.id NOT IN (8,9,13,14) ) THEN TRIM(LEADING '0' FROM IFNULL(tra.num_operacion, ''))
					ELSE IFNULL(tra.num_operacion, '')
				END
			) AS cod_operacion,
		    IFNULL(tra.monto_deposito, 0) AS monto_deposito,
			IFNULL(tra.monto, 0) AS monto,
			IF(IFNULL(tra.monto, 0) > 500, '>500', '') AS si_monto_mayor,
			IFNULL(tra.comision_monto, 0) AS comision_monto,
			IFNULL(tra.observacion_validador, '') AS observacion_validador,
		    IFNULL(CONCAT( apt.nombre, ' ', IFNULL( apt.apellido_paterno, '' ), ' ', IFNULL( apt.apellido_materno, '' ) ), '') AS validador_nombre,
			UPPER(IFNULL(loc.nombre, '')) AS turno_local,
			IFNULL(tip_cons.descripcion, '') tipo_constancia
		FROM tbl_televentas_clientes_transaccion tra
		INNER JOIN tbl_televentas_clientes_transaccion tra2 ON tra.transaccion_id = tra2.id
		LEFT JOIN tbl_usuarios usu ON usu.id = tra2.user_id
		LEFT JOIN tbl_televentas_clientes cli ON cli.id = tra.cliente_id
		LEFT JOIN tbl_usuarios val ON val.id = tra.user_id
		LEFT JOIN tbl_personal_apt apt ON apt.id = val.personal_id

		LEFT JOIN tbl_televentas_cuentas tc ON tc.cuenta_apt_id = tra.cuenta_id AND tra.tipo_id IN (1,26)
		LEFT JOIN tbl_cuentas_apt cuen ON cuen.id = tc.cuenta_apt_id AND tra.tipo_id IN (1,26)
		LEFT JOIN tbl_bancos ban ON cuen.banco_id = ban.id AND tra.tipo_id IN (1,26)

		LEFT JOIN tbl_televentas_clientes_cuenta cc ON tra.cuenta_id = cc.id AND tra.tipo_id in (9,11,28,29)
		LEFT JOIN tbl_bancos ban_2 ON cc.banco_id = ban_2.id

		LEFT JOIN tbl_televentas_cajeros_cuenta tcc ON tra.cajero_cuenta_id = tcc.id AND tra.tipo_id = 21
		LEFT JOIN tbl_bancos ban_3 ON tcc.banco_id = ban_3.id

		LEFT JOIN tbl_locales loc ON loc.cc_id = tra.cc_id 
		LEFT JOIN tbl_televentas_tipo_contacto ttc ON ttc.id = tra.id_tipo_contacto
		LEFT JOIN tbl_televentas_cuentas_pago_retiro pr ON tra.cuenta_pago_id = pr.id
		LEFT JOIN tbl_televentas_clientes_tipo_transaccion ttra ON tra.tipo_id = ttra.id
		LEFT JOIN tbl_televentas_titular_abono tit_abon ON tra.titular_abono = tit_abon.id
		LEFT JOIN tbl_televentas_tipo_constancia tip_cons ON tra.id_tipo_constancia = tip_cons.id
		WHERE
		tra.estado > 0 
		$where_estados
		$where_users_test
		$where_fecha 
		$where_tipo
		$where_usuario
		$where_cuenta
		$where_movimiento
		ORDER BY tra.id ASC
		";

	$list_query = $mysqli->query($query_1);
	$list_transaccion = array();
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$list_transaccion[] = $li;
		}
	}
	$result["list_query"] = $query_1;
	if (count($list_transaccion) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
	} elseif (count($list_transaccion) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_transaccion;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los registros.";
	}
}

//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER TRANSACCIONES X CAJERO --> EXCEL
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"]==="sec_rpt_fv_exportar_registros_xls") {
	global $mysqli;
	$busqueda_fecha_inicio		= $_POST["fecha_inicio"];
	$busqueda_fecha_fin			= $_POST["fecha_fin"];
	$busqueda_tipo				= $_POST["tipo"];
	$busqueda_usuario			= $_POST["usuario"];
	$busqueda_cuenta      		= $_POST["cuenta"];
	$busqueda_movimiento   		= $_POST["movimiento"];
	$usuario_id = $login ? $login['id'] : 0;

	$area_id = $login ? $login['area_id'] : 0;

	$where_users_test="";
	if((int)$area_id !== 6) {// diferente de sistemas
		$where_users_test="	
			AND IFNULL(tra.web_id, '') not in ('3333200', '71938219')			 
			AND IFNULL(tra.user_id, '') not in (1, 249, 250, 2572, 3028)
		";
	}

	$where_tipo = '';
	$where_movimiento = '';
	if ((int)$busqueda_tipo > 0) {
		
	} else {
		//$where_tipo = '';
	}

	if((int)$busqueda_movimiento != 0){
		if($busqueda_movimiento == 26){
			$where_movimiento = " AND tra.tipo_id = " . $busqueda_movimiento . " AND tra.estado = 1";
		}else{
			$where_movimiento = " AND tra.tipo_id = " . $busqueda_movimiento . " AND tra.estado = 2";
		}
	}else{
		if ((int)$busqueda_tipo == 1){
			$where_tipo = ' AND tra.tipo_id = 26 AND tra.estado = 1';
		}elseif ((int)$busqueda_tipo == 9) {
			$where_tipo = ' AND tra.tipo_id IN (11,24,29) AND tra.estado = 2';
		}
	}

	$where_usuario = '';
	if ((int)$busqueda_usuario > 0) {
		$where_usuario = ' AND tra.user_id = ' . $busqueda_usuario;
	} else {
		$where_usuario = '';
	}

	//$where_fecha_inicio = " AND DATE(tra.created_at)>= '" . $busqueda_fecha_inicio . "'";
	//$where_fecha_fin = " AND DATE(tra.created_at)<= '" . $busqueda_fecha_fin . "'";

	$where_fecha = " AND DATE(tra2.created_at) >= '" . $busqueda_fecha_inicio . "' AND DATE(tra2.created_at) <= '" . $busqueda_fecha_fin . "' ";

	$where_cuenta = '';

	if((int)$busqueda_tipo == 1){
		if($busqueda_cuenta != 0){
			$where_cuenta = ' AND cuen.id = ' .$busqueda_cuenta;
		}
	}else if ((int)$busqueda_tipo == 9){
		if($busqueda_cuenta != 0){
			$where_cuenta = ' AND (ban_2.id = ' .$busqueda_cuenta;
			$where_cuenta .= ' OR ban_3.id = ' .$busqueda_cuenta . ')';
		}
	}

	$where_estados = '';
	if((int)$busqueda_movimiento == 0 && (int)$busqueda_tipo == 0){
		$where_estados = '
		AND ((tra.tipo_id = 26 AND tra.estado = 1) OR (tra.tipo_id IN (11,24,29) AND tra.estado = 2))';
	}


	$query_1 = "
		SELECT
			DATE(tra2.created_at)  fecha_registro, 
			TIME(tra2.created_at)  hora_registro,
			DATE(tra.created_at)  fecha_abono, 
			TIME(tra.created_at)  hora_abono,
			DATE(tra.registro_deposito)  fecha_filtro, 
			TIME(tra.registro_deposito)  hora_filtro,
			UPPER(IFNULL(loc.nombre, '')) AS turno_local,
		    IFNULL(usu.usuario,'') AS cajero,
		    IFNULL(CONCAT( apt.nombre, ' ', IFNULL( apt.apellido_paterno, '' ), ' ', IFNULL( apt.apellido_materno, '' ) ), '') AS validador_nombre,
			IFNULL(cli.num_doc,'') AS num_doc,
			IFNULL(CONCAT( cli.nombre, ' ', IFNULL( cli.apellido_paterno, '' ), ' ', IFNULL( cli.apellido_materno, '' ) ), '') AS cliente,


			UPPER(IFNULL(tit_abon.nombre_apellido_titular, IFNULL(CONCAT( cli.nombre, ' ', IFNULL( cli.apellido_paterno, '' ), ' ', IFNULL( cli.apellido_materno, '' ) ), ''))) titular_abono,
		    CASE 
				WHEN tra.tipo_id = 26 THEN 'Ingreso' 
				WHEN tra.tipo_id IN (11,24,29) THEN 'Salida'
				ELSE '' 
			END tipo_transaccion,
		    IFNULL(ttra.nombre, '') movimiento,
		    CASE 
				WHEN (tra.tipo_id IN (11,24,29)) THEN IFNULL(pr.nombre, '') 
				ELSE '' 
			END Banco_pago,
		    CASE 
				WHEN tra.tipo_id IN (26) THEN IFNULL(ban.nombre,'') 
				WHEN tra.tipo_id IN (11,29) THEN IFNULL(ban_2.nombre,'') 
				WHEN tra.tipo_id = 24 THEN IFNULL(ban_3.nombre,'') 
				ELSE ''
			END Banco,
			IFNULL(tip_cons.descripcion, '') tipo_constancia,
		    CASE 
				WHEN (tra.tipo_id IN (11,24,29)) THEN IFNULL(pr.nombre, '') 
				WHEN tra.tipo_id IN (26) THEN IFNULL(cuen.cuenta_descripcion, '') 
				ELSE '' 
			END AS banco_ts,
		    CASE 
				WHEN tra.tipo_id IN (26) THEN IFNULL(cuen.sigla, '') 
				WHEN tra.tipo_id IN (11,24,29) THEN IFNULL(pr.sigla, '') 
				ELSE ''
			END AS sigla_cuenta,
		    CASE 
				WHEN tra.tipo_id IN (26) THEN IFNULL(cuen.cuenta_descripcion, '') 
				WHEN tra.tipo_id IN (11,29) THEN IFNULL(cc.cuenta_num, '') 
				WHEN tra.tipo_id = 24 THEN IFNULL(tcc.cuenta_num, '') 
				ELSE ''
			END AS cuenta,
		    CASE
				WHEN tra.tipo_id IN (26) THEN IFNULL(cuen.cuenta_inter_num,'')
				WHEN tra.tipo_id IN (11,29) THEN IFNULL(cc.cci,'')
				WHEN tra.tipo_id = 24 THEN IFNULL(tcc.cci,'')
				ELSE ''
			END cci,
			CASE 
				WHEN tra.tipo_id IN (26) AND tra.estado = 1 THEN 'Aprobado'
				WHEN tra.tipo_id IN (11,24,29) AND tra.estado = 2 THEN 'Pagado'
				ELSE ''
			END estado,
		    CASE
				WHEN ( cuen.id NOT IN (3,4,14,15,22) || pr.id NOT IN (8,9,13,14) ) THEN TRIM(LEADING '0' FROM IFNULL(tra.num_operacion, ''))
				ELSE IFNULL(tra.num_operacion, '')
			END AS num_operacion,
		    CONCAT(
				CASE 
					WHEN tra.tipo_id IN (26) THEN IFNULL(cuen.sigla, '') 
					WHEN tra.tipo_id IN (11, 24, 29) THEN IFNULL(pr.sigla, '') 
					ELSE ''
				END,
				'-',
				CASE
					WHEN ( cuen.id NOT IN (3,4,14,15,22) || pr.id NOT IN (8,9,13,14) ) THEN TRIM(LEADING '0' FROM IFNULL(tra.num_operacion, ''))
					ELSE IFNULL(tra.num_operacion, '')
				END
			) AS cod_operacion,
		    IFNULL(tra.monto_deposito, 0) AS monto_deposito,
			IFNULL(tra.monto, 0) AS monto,CASE 
				WHEN tra.tipo_id IN (26) THEN IFNULL(tra.monto_deposito, 0)
                WHEN tra.tipo_id IN (11,24,29) THEN IFNULL(tra.monto, 0)
            END importe,
			IFNULL(tra.comision_monto, 0) AS comision_monto,
			IFNULL(tra.observacion_validador, '') AS observacion_validador,
			IF(IFNULL(tra.monto, 0) > 500, '>500', '') AS si_monto_mayor
		FROM tbl_televentas_clientes_transaccion tra
		INNER JOIN tbl_televentas_clientes_transaccion tra2 ON tra.transaccion_id = tra2.id
		LEFT JOIN tbl_usuarios usu ON usu.id = tra2.user_id
		LEFT JOIN tbl_televentas_clientes cli ON cli.id = tra.cliente_id
		LEFT JOIN tbl_usuarios val ON val.id = tra.user_id
		LEFT JOIN tbl_personal_apt apt ON apt.id = val.personal_id

		LEFT JOIN tbl_televentas_cuentas tc ON tc.cuenta_apt_id = tra.cuenta_id AND tra.tipo_id IN (1,26)
		LEFT JOIN tbl_cuentas_apt cuen ON cuen.id = tc.cuenta_apt_id AND tra.tipo_id IN (1,26)
		LEFT JOIN tbl_bancos ban ON cuen.banco_id = ban.id AND tra.tipo_id IN (1,26)

		LEFT JOIN tbl_televentas_clientes_cuenta cc ON tra.cuenta_id = cc.id AND tra.tipo_id in (9,11,28,29)
		LEFT JOIN tbl_bancos ban_2 ON cc.banco_id = ban_2.id

		LEFT JOIN tbl_televentas_cajeros_cuenta tcc ON tra.cajero_cuenta_id = tcc.id AND tra.tipo_id = 21
		LEFT JOIN tbl_bancos ban_3 ON tcc.banco_id = ban_3.id

		LEFT JOIN tbl_locales loc ON loc.cc_id = tra.cc_id 
		LEFT JOIN tbl_televentas_tipo_contacto ttc ON ttc.id = tra.id_tipo_contacto
		LEFT JOIN tbl_televentas_cuentas_pago_retiro pr ON tra.cuenta_pago_id = pr.id
		LEFT JOIN tbl_televentas_clientes_tipo_transaccion ttra ON tra.tipo_id = ttra.id
		LEFT JOIN tbl_televentas_titular_abono tit_abon ON tra.titular_abono = tit_abon.id
		LEFT JOIN tbl_televentas_tipo_constancia tip_cons ON tra.id_tipo_constancia = tip_cons.id
		WHERE
		tra.estado > 0 
		$where_estados
		$where_users_test
		$where_fecha 
		$where_tipo
		$where_usuario
		$where_cuenta
		$where_movimiento
		ORDER BY tra.id ASC
		";

	$list_query = $mysqli->query($query_1);
	$result["query"] = $query_1;
	$result_data = array();
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
		echo json_encode($result); exit;
		echo json_encode([
			"error" => "Export error"
		]);
		exit;
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$result_data[] = $li;
		}

		$headers = [
			"fecha_registro" => "Fecha",
			"hora_registro" => "Hora",
			"fecha_abono" => "Fecha Abono",
			"hora_abono" => "Hora Abono",
			"fecha_filtro" => "Fecha Filtro",
			"hora_filtro" => "Hora Filtro",
			"turno_local" => "Caja",
			"cajero" => "Cajero",
			"validador_nombre" => "Validador",
			"num_doc" => "DNI",
			"cliente" => "Cliente",
			"titular_abono" => "Titular Abono",
			"tipo_transaccion" => "Tipo",
			"movimiento" => "Movimiento",
			"Banco_pago" => "Banco Pago",
			"Banco" => "Banco",
			"tipo_constancia" => "Tipo Constancia",
			"banco_ts" => "Banco TS",
			"sigla_cuenta" => "New OP",
			"cuenta" => "Cuenta",
			"cci" => "CCI",
			"estado" => "Estado",
			"num_operacion" => "Núm Operación",
			"cod_operacion" => "Cod Operación",
			"monto_deposito" => "Depósito",
			"monto" => "Monto",
			"importe" => "Importe",
			"comision_monto" => "Comisión",
			"observacion_validador" => "Observación",
			"si_monto_mayor" => "Monto > S/ 500"
		];
		array_unshift($result_data, $headers);

		ini_set('display_errors', 0);
		ini_set('display_startup_errors', 0);
		error_reporting(0);

		require_once '../phpexcel/classes/PHPExcel.php';
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getActiveSheet()->fromArray($result_data, null, 'A1');
		$objPHPExcel->getActiveSheet()->getStyle('U')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
		$date = new DateTime();
		$file_title = "Reporte de Ingresos y Salidas _" . $date->getTimestamp();

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

//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER VALIDADOR
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "sec_rpt_fv_listar_usuarios_validador") {
	$query_1 = "SELECT 
				  b.user_id AS id, 
				  u.usuario,
				  CONCAT(
					IF( LENGTH( pl.nombre ) > 0, CONCAT( UPPER( TRIM(pl.nombre) ), ', '), '' ),
					IF( LENGTH( pl.apellido_paterno ) > 0, CONCAT( UPPER( pl.apellido_paterno ), ' ' ), '' ),
					IF( LENGTH( pl.apellido_materno ) > 0, UPPER( pl.apellido_materno ), '' )
					) nombre_cajero
				from 
				  (
				    select 
				      a.user_id 
				    from 
				      (
				        select 
				          usuario_id user_id 
				        from 
				          tbl_permisos p 
				          join tbl_menu_sistemas ms on ms.id = p.menu_id 
				        where 
				          ms.sec_id = 'televentas_depositos' 
				          AND p.boton_nombre = 'Ver' 
				          AND p.usuario_id > '0' 
				          AND p.estado = '1' 
				        union all 
				        select 
				          tct.update_user_id user_id 
				        from 
				          tbl_televentas_clientes_transaccion tct 
				        where 
				          tct.tipo_id = 1 
				          and tct.update_user_id is not null 
				        group by 
				          tct.update_user_id
				      ) a 
				    group by 
				      a.user_id
				  ) b 
				  JOIN tbl_usuarios u ON u.id = b.user_id 
				  JOIN tbl_personal_apt pl ON pl.id = u.personal_id
				ORDER BY pl.nombre ASC";

	$list_query = $mysqli->query($query_1);
	$list_transaccion = array();
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$list_transaccion[] = $li;
		}
	}
	if (count($list_transaccion) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
	} elseif (count($list_transaccion) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_transaccion;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los registros.";
	}
}

//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER PAGADOR
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "sec_rpt_fv_listar_usuarios_pagador") {
	$query_1 = "SELECT 
				  b.user_id, 
				  u.usuario, 
				  -- u.personal_id cod_personal,
				  CONCAT(
					IF( LENGTH( pl.nombre ) > 0, CONCAT( UPPER( TRIM(pl.nombre) ), ', '), '' ),
					IF( LENGTH( pl.apellido_paterno ) > 0, CONCAT( UPPER( pl.apellido_paterno ), ' ' ), '' ),
					IF( LENGTH( pl.apellido_materno ) > 0, UPPER( pl.apellido_materno ), '' )
					) nombre_cajero
				from 
				  (
				    select 
				      a.user_id 
				    from 
				      (
				        select 
				          usuario_id user_id 
				        from 
				          tbl_permisos p 
				          join tbl_menu_sistemas ms on ms.id = p.menu_id 
				        where 
				          ms.sec_id = 'televentas_pagador' 
				          AND p.boton_nombre = 'Ver' 
				          AND p.usuario_id > '0' 
				          AND p.estado = '1' 
				        union all 
				        select 
				          tct.update_user_id user_id 
				        from 
				          tbl_televentas_clientes_transaccion tct 
				        where 
				          tct.tipo_id = 1 
				          and tct.update_user_id is not null 
				        group by 
				          tct.update_user_id
				      ) a 
				    group by 
				      a.user_id
				  ) b 
				  JOIN tbl_usuarios u ON u.id = b.user_id 
				  JOIN tbl_personal_apt pl ON pl.id = u.personal_id
				WHERE u.estado = 1
				ORDER BY pl.nombre ASC";

	$list_query = $mysqli->query($query_1);
	$list_transaccion = array();
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$list_transaccion[] = $li;
		}
	}
	if (count($list_transaccion) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
	} elseif (count($list_transaccion) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_transaccion;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los registros.";
	}
}

//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER CUENTAS
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "sec_rpt_pf_obtener_listado_cuentas_validador") {
	$usuario_id = $login["id"];

	$query = "	
	SELECT
		ca.id,
		COALESCE((SELECT
			uca.cuenta_apt_id
			FROM
			tbl_usuario_cuentas_apt AS uca	
			WHERE uca.usuario_id = $usuario_id AND uca.cuenta_apt_id = ca.id  AND uca.estado =1
			GROUP BY uca.cuenta_apt_id), 0) AS activos,

		UPPER(ca.cuenta_descripcion) AS cuenta_descripcion

	FROM
		tbl_cuentas_apt AS ca
	WHERE ca.estado = 1
	
	";

	$list_query = $mysqli->query($query);
	$list_transaccion = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_transaccion[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list_transaccion) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay transacciones.";
	} elseif (count($list_transaccion) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_transaccion;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar transacciones nuevas.";
	}
}

//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER CUENTAS
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "sec_rpt_pf_obtener_listado_cuentas_pagador") {
	$usuario_id = $login["id"];

	$query = "	
			SELECT 
			  b.id, 
			  b.nombre AS cuenta_descripcion
			FROM 
			  tbl_bancos b
			WHERE 
			  b.estado = 1 and b.id not in (54,55,56)
			ORDER BY
				b.nombre	
	";

	$list_query = $mysqli->query($query);
	$list_transaccion = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_transaccion[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list_transaccion) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay transacciones.";
	} elseif (count($list_transaccion) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_transaccion;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar transacciones nuevas.";
	}
}

//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER TOTALES REGISTROS
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "sec_rpt_fv_listar_totales_registros") {
	$busqueda_fecha_inicio		= $_POST["fecha_inicio"];
	$busqueda_fecha_fin			= $_POST["fecha_fin"];
	$busqueda_tipo				= $_POST["tipo"];
	$busqueda_usuario			= $_POST["usuario"];
	$busqueda_cuenta      		= $_POST["cuenta"];
	$busqueda_movimiento   		= $_POST["movimiento"];

	$usuario_id = $login["id"];
	$area_id = $login ? $login['area_id'] : 0;

	$where_users_test="";
	if((int)$area_id !== 6) {// diferente de sistemas
		$where_users_test="	
			AND IFNULL(tra.web_id, '') not in ('3333200', '71938219') 
			AND IFNULL(tra.user_id, '') not in (1, 249, 250, 2572, 3028) 
		";
	}

	$where_tipo = '';
	$where_movimiento = '';
	if ((int)$busqueda_tipo > 0) {
		
	} else {
		//$where_tipo = '';
	}

	if((int)$busqueda_movimiento != 0){
		if($busqueda_movimiento == 26){
			$where_movimiento = " AND tra.tipo_id = " . $busqueda_movimiento . " AND tra.estado = 1";
		}else{
			$where_movimiento = " AND tra.tipo_id = " . $busqueda_movimiento . " AND tra.estado = 2";
		}
	}else{
		if ((int)$busqueda_tipo == 1){
			$where_tipo = ' AND tra.tipo_id = 26 AND tra.estado = 1';
		}elseif ((int)$busqueda_tipo == 9) {
			$where_tipo = ' AND tra.tipo_id IN (11,24,29) AND tra.estado = 2';
		}
	}

	$where_usuario = '';
	if ((int)$busqueda_usuario > 0) {
		$where_usuario = ' AND tra.user_id = ' . $busqueda_usuario;
	} else {
		$where_usuario = '';
	}

	//$where_fecha_inicio = " AND DATE(tra.created_at)>= '" . $busqueda_fecha_inicio . "'";
	//$where_fecha_fin = " AND DATE(tra.created_at)<= '" . $busqueda_fecha_fin . "'";

	$where_fecha = " AND DATE(tra2.created_at) >= '" . $busqueda_fecha_inicio . "' AND DATE(tra2.created_at) <= '" . $busqueda_fecha_fin . "' ";

	$where_cuenta = '';

	if((int)$busqueda_tipo == 1){
		if($busqueda_cuenta != 0){
			$where_cuenta = ' AND cuen.id = ' .$busqueda_cuenta;
		}
	}else if ((int)$busqueda_tipo == 9){
		if($busqueda_cuenta != 0){
			$where_cuenta = ' AND (ban_2.id = ' .$busqueda_cuenta;
			$where_cuenta .= ' OR ban_3.id = ' .$busqueda_cuenta . ')';
		}
	}

	$where_estados = '';
	if((int)$busqueda_movimiento == 0 && (int)$busqueda_tipo == 0){
		$where_estados = '
		AND ((tra.tipo_id = 26 AND tra.estado = 1) OR (tra.tipo_id IN (11,24,29) AND tra.estado = 2))';
	}

	$query_1 = "
		SELECT 
			IFNULL( SUM( IF((tra.tipo_id = 26), 1, 0 ) ), 0 ) AS cantidad_ingresos,
			IFNULL( SUM( IF((tra.tipo_id IN (11,24,29)), 1, 0 ) ), 0 ) AS cantidad_salidas,
			IFNULL( SUM( IF((tra.tipo_id = 26), tra.monto, 0 ) ), 0 ) AS total_ingresos,
			IFNULL( SUM( IF((tra.tipo_id IN (11,24,29)), tra.monto, 0 ) ), 0 ) AS total_salidas,
			IFNULL( SUM( tra.monto ), 0) AS total
		FROM tbl_televentas_clientes_transaccion tra
		INNER JOIN tbl_televentas_clientes_transaccion tra2 ON tra.transaccion_id = tra2.id

		LEFT JOIN tbl_televentas_cuentas tc ON tc.cuenta_apt_id = tra.cuenta_id AND tra.tipo_id IN (1,26)
		LEFT JOIN tbl_cuentas_apt cuen ON cuen.id = tc.cuenta_apt_id AND tra.tipo_id IN (1,26)
		LEFT JOIN tbl_bancos ban ON cuen.banco_id = ban.id AND tra.tipo_id IN (1,26)

		LEFT JOIN tbl_televentas_clientes_cuenta cc ON tra.cuenta_id = cc.id AND tra.tipo_id in (9,11,28,29)
		LEFT JOIN tbl_bancos ban_2 ON cc.banco_id = ban_2.id

		LEFT JOIN tbl_televentas_cajeros_cuenta tcc ON tra.cajero_cuenta_id = tcc.id AND tra.tipo_id = 21
		LEFT JOIN tbl_bancos ban_3 ON tcc.banco_id = ban_3.id
		WHERE
		tra.estado > 0 
		$where_estados
		$where_users_test
		$where_fecha
		$where_tipo
		$where_usuario
		$where_cuenta
		$where_movimiento
		ORDER BY tra.id ASC LIMIT 1
		";

	$list_query = $mysqli->query($query_1);
	$list_transaccion = array();
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$list_transaccion[] = $li;
		}
	}
	$result["list_query"] = $query_1;
	if (count($list_transaccion) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
	} elseif (count($list_transaccion) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_transaccion;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los registros.";
	}
}


//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER MOVIMIENTOS
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "sec_rpt_pf_obtener_listado_movimientos") {
	$usuario_id = $login["id"];
	$tipo = $_POST["tipo"];
	$where = ' tt.id IN (26,11,24,29)';
	if((int)$tipo == 1){
		$where = ' tt.id IN (26)';
	}else if((int)$tipo == 9){
		$where = ' tt.id IN (11,24,29)';
	}
	$query = "	
	SELECT 
		IFNULL(tt.id, 0) id,
		IFNULL(tt.nombre, '') nombre
	FROM tbl_televentas_clientes_tipo_transaccion tt
	WHERE " . $where;

	$list_query = $mysqli->query($query);
	$list_transaccion = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_transaccion[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list_transaccion) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
	} elseif (count($list_transaccion) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_transaccion;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los movimientos existentes.";
	}
}


echo json_encode($result);
?>
