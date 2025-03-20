<?php
$result = array();
$result_verificacion = array();
include("db_connect.php");
include("sys_login.php");
date_default_timezone_set("America/Lima");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function get_turno()
{
	global $login;
	global $mysqli;
	$usuario_id = $login['id'];

	$command = "
		SELECT
			sqc.id,
			ssql.id local_id,
			ssql.cc_id
		FROM
			tbl_caja sqc
			JOIN tbl_local_cajas sqlc ON sqlc.id = sqc.local_caja_id
			JOIN tbl_locales ssql ON ssql.id = sqlc.local_id 
		WHERE
			sqc.estado = 0 
			AND sqc.usuario_id = '" . $usuario_id . "' 
		ORDER BY sqc.id DESC
		LIMIT 1 
		";
	$list_query = $mysqli->query($command);
	$list = array();
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if ($mysqli->error) {
		print_r($mysqli->error);
	}
	return $list;
}

function api_calimaco_auditoria_insert($method, $txn_id, $client_id, $body){
	global $mysqli;
	global $login;

	date_default_timezone_set("America/Lima");
	$date_time = date('Y-m-d H:i:s');

	$user_id = $login ? $login['id'] : 0;
	$turno_id = 0;
	$cc_id = '';
	$turno = get_turno();
	if (count($turno) > 0) {
		$turno_id = $turno[0]["id"];
		$cc_id = $turno[0]["cc_id"];
	}

	$insert_command = "
		INSERT INTO tbl_televentas_api_calimaco_response (
			method,
			bet_id,
			client_id,
			body,
			turno_id,
			cc_id,
			status,
			user_id,
			created_at
		) VALUES (
			'" . $method . "',
			'" . $txn_id . "',
			'" . $client_id . "',
			'" . $body . "',
			'" . $turno_id . "',
			'" . $cc_id . "',
			'0',
			'" . $user_id . "',
			'" . $date_time . "'
		)";
	$mysqli->query($insert_command);

	$query = "
		SELECT
			t.id 
		FROM
			tbl_televentas_api_calimaco_response t 
		WHERE
			t.method = '$method' 
			AND t.client_id = '$client_id' 
			AND t.turno_id = '$turno_id' 
			AND t.cc_id = '$cc_id' 
			AND t.status = 0 
			AND t.user_id = '$user_id' 
			AND t.created_at = '$date_time' 
		ORDER BY
			t.id DESC 
		LIMIT 1
	";
	$list_query = $mysqli->query($query);
	$list = array();
	if ($mysqli->error) {
		return 0;
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$list[] = $li;
		}
		if (count($list) >= 0) {
			return $list[0]["id"];
		} else {
			return 0;
		}
	}
}
function api_calimaco_auditoria_update($auditoria_id, $txn_id, $response, $status){
	global $mysqli;
	date_default_timezone_set("America/Lima");
	$date_time = date('Y-m-d H:i:s');

	$update_command = "
		UPDATE tbl_televentas_api_calimaco_response 
		SET
			bet_id='" . $txn_id . "', 
			response='" . $response . "', 
			status='" . $status . "', 
			updated_at='" . $date_time . "' 
		WHERE 
			id='" . $auditoria_id . "' 
		";
	$mysqli->query($update_command);
}

function sec_tlv_deposito_get_value_parameter($nombre_codigo){
	global $mysqli;

	$command = "
		SELECT 
			IFNULL(valor, 0) valor
		FROM 
			tbl_televentas_parametros
		WHERE 
			nombre_codigo = '" . $nombre_codigo . "'
			AND estado = 1
		LIMIT 1
		";
	$list_query = $mysqli->query($command);
	$list = array();
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["query_erro"] = $mysqli->error;
		$result["query"] = $command;
		echo json_encode($result);exit();
	}
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	$valor = '';
	if ($mysqli->error) {
		$valor = '';
	}
	$valor = $list[0]['valor'];
	return $valor;
}

//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER CUENTAS
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_listado_cuentas") {


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
	LEFT JOIN tbl_televentas_cuentas tc ON tc.cuenta_apt_id = ca.id
	WHERE ca.estado = 1
	AND (ca.mod_tls_deposito = 1 OR IFNULL(tc.valid_caja7,0) = 1)
	AND ca.id not in (12)
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
// OBTENER TRANSACCIONES DE DEPOSITOS
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_transacciones_x_estado") {
	$busqueda_fecha_inicio = $_POST["fecha_inicio"];
	$busqueda_fecha_fin    = $_POST["fecha_fin"];
	$busqueda_estado       = $_POST["estado"];
	$busqueda_validador    = $_POST["validador"];
	$busqueda_autovalidacion = $_POST["autovalidacion"];
	$busqueda_tipo_constancia = $_POST["tipo_constancia"];

	$busqueda_cuenta       = "";
	if (isset($_POST["cuenta"]) && $_POST["cuenta"] !== '') {
		$busqueda_cuenta = implode(",",$_POST["cuenta"]) ;
	}

	$usuario_id = $login["id"];
	$area_id = $login ? $login['area_id'] : 0;

	$where_users_test="";
	if((int)$area_id !== 6) {// diferente de sistemas
		$where_users_test="	
			AND IFNULL(tra.web_id, '') not in ('3333200', '71938219') 
			AND IFNULL(tra.user_id, '') not in (1, 249, 250, 2572, 3028)
		";
	}

	$where_tipo_tra = ' tra.tipo_id =1 ';

	$where_estado = '';
	if ($busqueda_estado !== '') {
		if($busqueda_estado == '1'){ // Aprobado
			/*$where_tipo_tra = '(
									(tra.tipo_id = 1 and tra.estado = 1)
								) ';*/
			/*$where_tipo_tra = '(
									(tra.tipo_id = 1 and tra.estado = 1 AND IFNULL(tra_2.estado, 0) != 3 AND IFNULL(tra.caja_vip,0) IN (0,2)) 
									OR (tra.tipo_id = 1 and tra.estado = 1 AND IFNULL(tra_2.estado, 0) = 0 AND IFNULL(tra.caja_vip,0) = 1) 
									OR (tra.tipo_id = 26 and tra.estado = 1 and IFNULL(tra.caja_vip,0) = 1)
								) ';*/
			$where_estado = ' AND tra.estado = 1 AND IFNULL(tra_2.estado, 0) != 3 '; //' AND tra.estado = 1 AND IFNULL(tra_2.estado,0) != 3';
		} else if($busqueda_estado == '2'){ //Rechazado
			/*$where_tipo_tra = '
								(tra.tipo_id = 1 AND tra.estado = 2 AND IFNULL(tra_2.estado, 0) = 0 ) -- AND IFNULL(tra.caja_vip, 0) != 1)
								';*/
			$where_estado = ' AND tra.estado = 2  ';
		} else if ($busqueda_estado == '3'){ //Eliminado
			/*$where_tipo_tra = ' 
								(
									(tra.tipo_id = 1 AND tra.estado = 3 AND IFNULL(tra_2.estado, 0) = 3 AND IFNULL(tra.caja_vip, 0) != 1)
									OR (tra.tipo_id = 26 AND tra.estado = 3 AND tra.caja_vip = 1)
								)
								';*/
			$where_estado = ' AND tra.estado = 1 AND IFNULL(tra_2.estado, 0) = 3 '; 
		}else{
			$where_estado = ' AND tra.estado = ' . $busqueda_estado . ' ';
		}
	} else { // Todos
		$where_estado = '';
		/*$where_tipo_tra = ' (
								(tra.tipo_id = 1 and tra.estado IN (0,1,2,3) ) 
								-- (tra.tipo_id = 1 and tra.estado IN (0,1,2,3) AND IFNULL(tra_2.estado, 0) = 0 AND IFNULL(tra.caja_vip,0) IN (0,2)) 
								-- OR (tra.tipo_id = 1 and IFNULL(tra_2.estado, 0) = 0 AND IFNULL(tra.caja_vip,0) = 1) 
								-- OR (tra.tipo_id = 1 and tra_2.estado IN(1,3) and IFNULL(tra.caja_vip,0) IN (0,2))
								-- OR (tra.tipo_id = 26 and tra.estado IN(1,3) and IFNULL(tra.caja_vip,0) = 1)
							) ';*/
	}

	$where_validador = '';
	if ((int) $busqueda_validador > 0) {
		$where_validador = ' AND tra.update_user_id=' . $busqueda_validador . ' ';
	}

	$where_fecha_inicio = " AND tra.created_at >= '" . $busqueda_fecha_inicio . " 00:00:00' ";
	$where_fecha_fin = " AND tra.created_at <= '" . $busqueda_fecha_fin . " 23:59:59' ";

	$where_cuenta = "";
	if ($busqueda_cuenta !== '') {
		$where_cuenta = " AND cuen.id IN ($busqueda_cuenta) ";
	} else {
		$where_cuenta = "
			AND cuen.id IN (
							SELECT
								uca.cuenta_apt_id
							FROM
								tbl_usuario_cuentas_apt AS uca
							WHERE uca.usuario_id = $usuario_id AND uca.estado = 1
							GROUP BY uca.cuenta_apt_id
			) 
		";
	}

	$where_autovalidacion = "";
	if($busqueda_autovalidacion == 1){
		$where_autovalidacion = " AND IFNULL(tra.caja_vip, 0) != 2 ";
	}else if($busqueda_autovalidacion == 2){
		$where_autovalidacion = " AND IFNULL(tra.caja_vip, 0) = 2 ";
	}

	$where_tipo_constancia = "";
	 if($busqueda_tipo_constancia <> 0){
		$where_tipo_constancia = " AND IFNULL(tra.id_tipo_constancia, 0) =$busqueda_tipo_constancia ";
	}

	$nombre_busqueda = "";
	if(isset($_POST["search"]["value"])) {
		$busqueda_nombre = $_POST["search"]["value"];
		if ($busqueda_nombre !="") {
			$nombre_busqueda = '
			AND (
			tra.created_at LIKE "%'.$_POST["search"]["value"].'%"
			OR usu.usuario LIKE "%'.$_POST["search"]["value"].'%"
			OR loc.nombre LIKE "%'.$_POST["search"]["value"].'%"
			OR IFNULL(CONCAT( cli.nombre, " ", IFNULL( cli.apellido_paterno, "" ), " ", IFNULL( cli.apellido_materno, "" ) ), "") LIKE "%'.$_POST["search"]["value"].'%"
			OR cuen.cuenta_descripcion LIKE "%'.$_POST["search"]["value"].'%"		
			OR bon.nombre LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.num_operacion LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.monto_deposito LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.comision_monto LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.monto LIKE "%'.$_POST["search"]["value"].'%"
			OR IFNULL(CONCAT( apt.nombre, " ", IFNULL( apt.apellido_paterno, "" ), " ", IFNULL( apt.apellido_materno, "" ) ), "") LIKE "%'.$_POST["search"]["value"].'%"
			OR UPPER(IFNULL(tit_abon.nombre_apellido_titular, IFNULL(CONCAT( cli.nombre, " ", IFNULL( cli.apellido_paterno, "" ), " ", IFNULL( cli.apellido_materno, "" ) ), "")))  LIKE "%'.$_POST["search"]["value"].'%"';
			$nombre_busqueda .= ')';
		}
	}

	$order = "";
	$column = array(
		1=>"tra.created_at",
		2=>"usu.usuario",
		3=>"loc.nombre",
		4=>"cuen.cuenta_descripcion",
		6=>"tra.monto",
		7=>"tra.monto_deposito",
		8=>"tra.num_operacion",
		9=>"tra.comision_monto"
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

	$limit = "";
	if(isset($_POST["length"])) {
		if($_POST["length"] != -1) {
			$limit = 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
		}	
	}


	$query_1 = "
		SELECT
			tra.id,
			tra.cliente_id,
			tra.created_at fecha_hora_registro,
			CASE WHEN IFNULL(tra.txn_id, '') != '' THEN 'SI' ELSE 'NO' END autovalidacion,
			usu.usuario AS cajero,
			IFNULL(CONCAT( cli.nombre, ' ', IFNULL( cli.apellido_paterno, '' ), ' ', IFNULL( cli.apellido_materno, '' ) ), '') AS cliente,
			(CASE
				WHEN tra.estado = 0 THEN
				'Pendiente' 
				WHEN tra.estado = 1 AND IFNULL(tra_2.estado, 0) != 3 THEN
				'Aprobado' 
				WHEN tra.estado = 2 THEN
				'Rechazado' 
				WHEN tra.estado = 3 or tra_2.estado = 3 THEN
				'Eliminado'
			END) AS estado,

			tra.estado AS estado_id,
			IFNULL(tra_2.estado, 0) AS estado_id_aprov,
			IFNULL(tc.id, 0) AS cuenta_id,
			IFNULL(tc.valid_num_ope_existe, 0) valid_num_ope_existe,
			IFNULL(tc.valid_num_ope_unico, 0) valid_num_ope_unico,
			IFNULL(tc.valid_cuenta_yape, 0) valid_cuenta_yape,
			IFNULL(cuen.cuenta_descripcion, '') AS cuenta,
			IFNULL(tra.bono_id, 0) bono_id,
			IFNULL(tra.registro_deposito, '') AS registro_deposito,
			IFNULL(tra.num_operacion, '') AS num_operacion,
			IFNULL(tra.monto_deposito, 0) AS monto_deposito,
			IFNULL(tra.monto, 0) AS monto,
			IFNULL(tra.comision_monto, 0) AS comision_monto,
			IFNULL(tra.bono_monto, 0) AS bono_monto,
			IFNULL(bon.nombre, 'Ninguno' ) AS bono_nombre,
			IFNULL(tra.total_recarga, 0) AS total_recarga,
			val.usuario AS validador_usuario,
			IFNULL(CONCAT( apt.nombre, ' ', IFNULL( apt.apellido_paterno, '' ), ' ', IFNULL( apt.apellido_materno, '' ) ), '') AS validador_nombre,
			IFNULL(tra.update_user_at, '') fecha_hora_validacion,
			IFNULL(REPLACE(REPLACE(tra.observacion_cajero, '\n', ''), '\n', ''), '') observacion_cajero,
			IFNULL(REPLACE(REPLACE(tra.observacion_validador, '\n', ''), '\n', ''), '') observacion_validador,
			IFNULL(tra.tipo_rechazo_id, 0) tipo_rechazo_id,
			IFNULL(ttc.contact_type, '') AS tipo_contacto,
			UPPER(IFNULL(loc.nombre, '')) AS turno_local,
			IFNULL(tra.bono_mensual_actual, 0) AS bono_mensual_actual,
			IFNULL(rec_bon.nombre, 'Ninguno') bono_nombre,
			UPPER(IFNULL(tit_abon.nombre_apellido_titular, IFNULL(CONCAT( cli.nombre, ' ', IFNULL( cli.apellido_paterno, '' ), ' ', IFNULL( cli.apellido_materno, '' ) ), ''))) titular_abono,
			IFNULL(tra.titular_abono, 0) AS id_titular_abono, 
			IFNULL(tra.id_tipo_constancia, 0) id_tipo_constancia,
			IFNULL(tip_c.descripcion, '') tipo_constancia,
			IFNULL(tj.nombre, '') tipo_jugada,
			IFNULL(tra.id_abono_pendiente, 0) id_abono_pendiente,
            IFNULL(ap.fecha_operacion, '') ap_fecha_operacion,
            IFNULL(ap.hora_operacion, '') ap_hora_operacion,
            IFNULL(ap.nro_operacion, '') ap_nro_operacion,
            IFNULL(tapm.nombre, '') ap_nombre_medio,
            IFNULL(ap.monto, '') ap_monto
		FROM
			tbl_televentas_clientes_transaccion tra
			LEFT JOIN tbl_televentas_recargas_bono bon ON bon.id = tra.bono_id 
			LEFT JOIN tbl_usuarios usu ON usu.id = tra.user_id
			LEFT JOIN tbl_televentas_clientes cli ON cli.id = tra.cliente_id
			LEFT JOIN tbl_usuarios val ON val.id = tra.update_user_id
			LEFT JOIN tbl_personal_apt apt ON apt.id = val.personal_id
			LEFT JOIN tbl_televentas_cuentas tc ON tc.cuenta_apt_id = tra.cuenta_id
			LEFT JOIN tbl_cuentas_apt cuen ON cuen.id = tc.cuenta_apt_id
			LEFT JOIN tbl_locales loc ON loc.cc_id = tra.cc_id 
			LEFT JOIN tbl_televentas_tipo_contacto ttc ON ttc.id = tra.id_tipo_contacto
			LEFT JOIN tbl_televentas_recargas_bono rec_bon ON tra.bono_id = rec_bon.id
			LEFT JOIN tbl_televentas_titular_abono tit_abon ON tra.titular_abono = tit_abon.id
			LEFT JOIN tbl_televentas_tipo_constancia tip_c ON tra.id_tipo_constancia = tip_c.id
			LEFT JOIN tbl_televentas_clientes_transaccion tra_2 ON tra.id = tra_2.transaccion_id AND tra_2.tipo_id = 26
			LEFT JOIN tbl_televentas_tipo_juego tj ON tra.id_juego_balance = tj.id
			LEFT JOIN tbl_televentas_abonos_pendientes ap ON tra.id_abono_pendiente = ap.id
            LEFT JOIN tbl_televentas_abonos_pendientes_medio tapm ON tapm.id = ap.medio_id
		WHERE 
		$where_tipo_tra
		AND tra.cliente_id > 0
		$where_users_test
		$where_fecha_inicio 
		$where_fecha_fin 
		$where_estado 
		$where_validador 
		$where_cuenta 
		$where_autovalidacion
		$where_tipo_constancia
		$nombre_busqueda
		 "
		.$order
		.$limit;
	$result["consulta_query_listar_depositos"] = $query_1;
	$list_query = $mysqli->query($query_1);
	$list_transaccion = array();
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$list_transaccion[] = $li;
		}
	}

	//cantidad

	$query_COUNT = "
		SELECT
			COUNT(*) cant
		FROM
			tbl_televentas_clientes_transaccion tra
			LEFT JOIN tbl_televentas_recargas_bono bon ON bon.id = tra.bono_id 
			LEFT JOIN tbl_usuarios usu ON usu.id = tra.user_id
			LEFT JOIN tbl_televentas_clientes cli ON cli.id = tra.cliente_id
			LEFT JOIN tbl_usuarios val ON val.id = tra.update_user_id
			LEFT JOIN tbl_personal_apt apt ON apt.id = val.personal_id
			LEFT JOIN tbl_televentas_cuentas tc ON tc.cuenta_apt_id = tra.cuenta_id
			LEFT JOIN tbl_cuentas_apt cuen ON cuen.id = tc.cuenta_apt_id
			LEFT JOIN tbl_locales loc ON loc.cc_id = tra.cc_id 
			LEFT JOIN tbl_televentas_tipo_contacto ttc ON ttc.id = tra.id_tipo_contacto
			LEFT JOIN tbl_televentas_recargas_bono rec_bon ON tra.bono_id = rec_bon.id
			LEFT JOIN tbl_televentas_titular_abono tit_abon ON tra.titular_abono = tit_abon.id
			LEFT JOIN tbl_televentas_tipo_constancia tip_c ON tra.id_tipo_constancia = tip_c.id
			LEFT JOIN tbl_televentas_clientes_transaccion tra_2 ON tra.id = tra_2.transaccion_id  AND tra_2.tipo_id = 26
		WHERE
		$where_tipo_tra
		AND tra.cliente_id > 0
		$where_users_test
		$where_fecha_inicio 
		$where_fecha_fin 
		$where_estado 
		$where_validador 
		$where_cuenta 
		$where_autovalidacion
		$where_tipo_constancia
		$nombre_busqueda
		ORDER BY tra.id ASC ";

	$list_query_COUNT=$mysqli->query($query_COUNT);
	$list_transaccion_COUNT=array();
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li=$list_query_COUNT->fetch_assoc()) {
			$list_transaccion_COUNT[]=$li;
		}
	}

	$totales = array();
	$totales = get_cant_abonos_pending($busqueda_fecha_inicio, $busqueda_fecha_fin);
	$result["totales"] = $totales;

	if (count($list_transaccion) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay transacciones.";
	} elseif (count($list_transaccion) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_transaccion;
		$result["draw"]            = isset($_POST["draw"]) == true ?intval($_POST["draw"]):'';
		$result["recordsTotal"]    = $list_transaccion_COUNT[0]["cant"];
		$result["recordsFiltered"] = $list_transaccion_COUNT[0]["cant"];
		$result["data"] = $list_transaccion;
		
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar transacciones nuevas.";
	}
}

//*******************************************************************************************************************
//*******************************************************************************************************************
// ACTUALIZAR TABLA TRANSACCIONES DE DEPOSITOS / CADA (n) SEGUNDOS
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "actualizar_transacciones_x_estado") {
	$busqueda_fecha_inicio = $_POST["fecha_inicio"];
	$busqueda_fecha_fin    = $_POST["fecha_fin"];
	$busqueda_estado       = $_POST["estado"];
	$busqueda_validador    = $_POST["validador"];
	$busqueda_id           = $_POST["where_id"];
	$busqueda_autovalidacion = $_POST["autovalidacion"];
	$busqueda_tipo_constancia = $_POST["tipo_constancia"];
	
	global $login;
	$usuario_id = $login ? $login['id'] : null;
	$area_id = $login ? $login['area_id'] : 0;

	$where_users_test="";
	if((int)$area_id !== 6) {// diferente de sistemas
		$where_users_test="	
			AND IFNULL(tra.web_id, '') not in ('3333200', '71938219') 
			AND IFNULL(tra.user_id, '') not in (1, 249, 250, 2572, 3028)
		";
	}

	$where_estado = '';
	if ($busqueda_estado !== '') {
		$where_estado = ' AND tra.estado=' . $busqueda_estado . ' ';
	} else {
		$where_estado = ' AND tra.estado IN ( 0, 1, 2 ) ';
	}

	$where_validador = '';
	if ((int) $busqueda_validador > 0) {
		$where_validador = ' AND tra.update_user_id=' . $busqueda_validador . ' ';
	}

	$where_fecha_inicio = " AND tra.created_at >= '" . $busqueda_fecha_inicio . " 00:00:00' ";
	$where_fecha_fin = " AND tra.created_at <= '" . $busqueda_fecha_fin . " 23:59:59' ";

	$where_id = " AND tra.id > " . $busqueda_id . " ";
	$where_cuenta = "";
	if ((int) $usuario_id > 0) {
	$where_cuenta = "AND cuen.id IN( SELECT
		uca.cuenta_apt_id	
		FROM
			tbl_usuario_cuentas_apt AS uca	
		WHERE uca.usuario_id =". $usuario_id ." AND uca.estado = 1
	)";
	}

	$where_autovalidacion = "";
	if($busqueda_autovalidacion == 1){
		$where_autovalidacion = " AND IFNULL(tra.txn_id, 0) = 0 ";
	}else if($busqueda_autovalidacion == 2){
		$where_autovalidacion = " AND IFNULL(tra.txn_id, 0) != 0 ";
	}

	$where_tipo_constancia = "";
	 if($busqueda_tipo_constancia <> 0){
		$where_tipo_constancia = " AND IFNULL(tra.id_tipo_constancia, 0) =$busqueda_tipo_constancia ";
	}

	$query_verificacion = "
		SELECT CASE
			WHEN EXISTS (
				SELECT 1 FROM tbl_televentas_clientes_transaccion tra
				LEFT JOIN tbl_televentas_cuentas tc ON tc.cuenta_apt_id = tra.cuenta_id
				LEFT JOIN tbl_cuentas_apt cuen ON cuen.id = tc.cuenta_apt_id
				WHERE
					tra.tipo_id =1 
					AND tra.cliente_id > 0
					$where_fecha_inicio 
					$where_users_test
					$where_fecha_fin 
					$where_estado 
					$where_validador 
					$where_id 
					$where_cuenta 
					$where_autovalidacion 
					$where_tipo_constancia
			) 
			then 1
			else 0 
		END AS resultado;
	";

	$query_1 = "
		SELECT
			tra.id,
			tra.cliente_id,
			tra.created_at fecha_hora_registro,
			CASE WHEN IFNULL(tra.txn_id, '') != '' THEN 'SI' ELSE 'NO' END autovalidacion,
			usu.usuario AS cajero,
			IFNULL(CONCAT( cli.nombre, ' ', IFNULL( cli.apellido_paterno, '' ), ' ', IFNULL( cli.apellido_materno, '' ) ), '') AS cliente,
			(CASE
				WHEN tra.estado = 0 THEN
				'Pendiente' 
				WHEN tra.estado = 1 THEN
				'Validado' 
				WHEN tra.estado = 2 THEN
				'Rechazado' 
			END) AS estado,
			tra.estado AS estado_id,
			IFNULL(tc.id, 0) AS cuenta_id,
			IFNULL(tc.valid_num_ope_existe, 0) valid_num_ope_existe,
			IFNULL(tc.valid_num_ope_unico, 0) valid_num_ope_unico,
			IFNULL(tc.valid_cuenta_yape, 0) valid_cuenta_yape,
			IFNULL(cuen.cuenta_descripcion, '') AS cuenta,
			IFNULL(tra.bono_id, 0) bono_id,
			IFNULL(tra.registro_deposito, '') AS registro_deposito,
			IFNULL(tra.num_operacion, '') AS num_operacion,
			IFNULL(tra.monto_deposito, 0) AS monto_deposito,
			IFNULL(tra.monto, 0) AS monto,
			IFNULL(tra.comision_monto, 0) AS comision_monto,
			IFNULL(tra.bono_monto, 0) AS bono_monto,
			IFNULL(bon.nombre, 'Ninguno' ) AS bono_nombre,
			# IFNULL(tra.total_recarga, 0) AS total_recarga,
			# val.usuario AS validador_usuario,
			IFNULL(CONCAT( apt.nombre, ' ', IFNULL( apt.apellido_paterno, '' ), ' ', IFNULL( apt.apellido_materno, '' ) ), '') AS validador_nombre,
			# IFNULL(tra.update_user_at, '') fecha_hora_validacion,
			IFNULL(REPLACE(REPLACE(tra.observacion_cajero, '\n', ''), '\n', ''), '') observacion_cajero,
			IFNULL(REPLACE(REPLACE(tra.observacion_validador, '\n', ''), '\n', ''), '') observacion_validador,
			IFNULL(tra.tipo_rechazo_id, 0) tipo_rechazo_id,
			IFNULL(ttc.contact_type, '') AS tipo_contacto,
			UPPER(IFNULL(loc.nombre, '')) AS turno_local,
			IFNULL(tra.bono_mensual_actual, 0) AS bono_mensual_actual,
			UPPER(IFNULL(tit_abon.nombre_apellido_titular, IFNULL(CONCAT( cli.nombre, ' ', IFNULL( cli.apellido_paterno, '' ), ' ', IFNULL( cli.apellido_materno, '' ) ), ''))) titular_abono,
			IFNULL(tra.titular_abono, 0) AS id_titular_abono, 
			IFNULL(tra.id_tipo_constancia, 0) id_tipo_constancia,
			IFNULL(tip_c.descripcion, '') tipo_constancia,
			IFNULL(tj.nombre, '') tipo_jugada,
			IFNULL(tra.id_abono_pendiente, 0) id_abono_pendiente,
            IFNULL(ap.fecha_operacion, '') ap_fecha_operacion,
            IFNULL(ap.hora_operacion, '') ap_hora_operacion,
            IFNULL(ap.nro_operacion, '') ap_nro_operacion,
            IFNULL(tapm.nombre, '') ap_nombre_medio,
            IFNULL(ap.monto, '') ap_monto
		FROM
			tbl_televentas_clientes_transaccion tra
			LEFT JOIN tbl_televentas_recargas_bono bon ON bon.id = tra.bono_id 
			LEFT JOIN tbl_usuarios usu ON usu.id = tra.user_id
			LEFT JOIN tbl_televentas_clientes cli ON cli.id = tra.cliente_id
			LEFT JOIN tbl_usuarios val ON val.id = tra.update_user_id
			LEFT JOIN tbl_personal_apt apt ON apt.id = val.personal_id
			LEFT JOIN tbl_televentas_cuentas tc ON tc.cuenta_apt_id = tra.cuenta_id
			LEFT JOIN tbl_cuentas_apt cuen ON cuen.id = tc.cuenta_apt_id
			LEFT JOIN tbl_locales loc ON loc.cc_id = tra.cc_id 
			LEFT JOIN tbl_televentas_tipo_contacto ttc ON ttc.id = tra.id_tipo_contacto
			LEFT JOIN tbl_televentas_titular_abono tit_abon ON tra.titular_abono = tit_abon.id
			LEFT JOIN tbl_televentas_tipo_constancia tip_c ON tra.id_tipo_constancia = tip_c.id
			LEFT JOIN tbl_televentas_tipo_juego tj ON tra.id_juego_balance = tj.id
			LEFT JOIN tbl_televentas_abonos_pendientes ap ON tra.id_abono_pendiente = ap.id
            LEFT JOIN tbl_televentas_abonos_pendientes_medio tapm ON tapm.id = ap.medio_id
		WHERE
		tra.tipo_id =1 
		AND tra.cliente_id > 0
		$where_fecha_inicio 
		$where_users_test
		$where_fecha_fin 
		$where_estado 
		$where_validador 
		$where_id 
		$where_cuenta 
		$where_autovalidacion 
		$where_tipo_constancia
		ORDER BY tra.id ASC
	";

	$verificacion = $mysqli->query($query_verificacion);

	if($verificacion){
		$result["consulta_query"] = $query_1;
		$list_query = $mysqli->query($query_1);
		$list_transaccion = array();
		if ($mysqli->error) {
			$result["consulta_error"] = $mysqli->error;
		} else {
			while ($li = $list_query->fetch_assoc()) {
				$list_transaccion[] = $li;
			}
		}

		$totales = array();
		$totales = get_cant_abonos_pending($busqueda_fecha_inicio, $busqueda_fecha_fin);
		$result["totales"] = $totales;

		$result["result_tls_dep_ultima_version"] = 0;
		$query_4 = "
			SELECT
				v.version
			FROM tbl_versiones v
			WHERE v.menu_id = '161'
			ORDER BY v.id DESC
			LIMIT 1
			";
		$list_query_4 = $mysqli->query($query_4);
		while ($li_4 = $list_query_4->fetch_assoc()) {
			$result["result_tls_dep_ultima_version"] = $li_4["version"];
		}

	
		if (count($list_transaccion) > 0) {
			$result["http_code"] = 200;
			$result["status"] = "ok";
			$result["result"] = $list_transaccion;
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al consultar transacciones nuevas.";
		}
	}else{
		$result["http_code"] = 400;
		$result["status"] = "No hay transacciones.";
	}
}


//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER IMÁGENES
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_imagenes_x_transaccion") {
	$id_transaccion = $_POST["id_transaccion"];

	$query_1 = "
				SELECT
					tta.id,
					tta.archivo 
				FROM
					tbl_televentas_transaccion_archivos tta 
				WHERE
					tta.transaccion_id = $id_transaccion 
					AND tta.estado = 1
			";
	//$result["consulta_query"] = $query_1;
	$list_query = $mysqli->query($query_1);
	$list_transaccion = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_transaccion[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list_transaccion) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay archivos en esta transacción.";
	} elseif (count($list_transaccion) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["fecha_hora"] = date('YmdHis');
		$result["result"] = $list_transaccion;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar las imágenes.";
	}
}




//*******************************************************************************************************************
//*******************************************************************************************************************
// GUARDAR VALIDACIÓN
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_validacion_deposito") {
	include("function_replace_invalid_caracters.php");
	$id_transaccion      = $_POST["id_transaccion"];
	$id_estado           = $_POST["id_estado"];
	$valid_num_ope_unico = $_POST["validacion_num_operacion_unico"];
	$valid_cuenta_yape   = $_POST["gen_validacion_cuenta_yape"];
	$cuenta_id           = $_POST["cuenta_id"];
	$observacion         = replace_invalid_caracters($_POST["observacion"]);
	$motivo              = $_POST["motivo"];
    $registro            = str_replace('T', ' ', $_POST["registro"]);
    $num_operacion       = $_POST["num_operacion"];
    $bono_select         = $_POST["bono_select"];
    $monto_deposito      = $_POST["monto_deposito"];
    $monto_comision      = $_POST["monto_comision"];
    $monto_real          = $_POST["monto_real"];
    $monto_bono          = $_POST["monto_bono"];
    $monto_total         = $_POST["monto_total"];
    $id_abono_pendiente  = $_POST["id_abono_pendiente"];
    $origen_abono_pend   = $_POST["origen_abono_pendiente"];
    $permitir_dupl       = $_POST["permitir_dupl"];
    $tipo_constancia     = $_POST["tipo_constancia"];
	$new_titular_abono   = $_POST["new_titular_abono"];
	

	$usuario_id = $login ? $login['id'] : 0;
	$usuario = $login ? $login['usuario'] : '';	
	if (!((int) $usuario_id > 0)) {
		$result["http_code"] = 400;
		$result["status"] = "Sesión perdida, actualice la página.";
		echo json_encode($result);exit();
	}


	$body=array();
	$body["id_transaccion"]                 =$_POST["id_transaccion"];
	$body["id_estado"]                      =$_POST["id_estado"];
	$body["validacion_num_operacion_unico"] =$_POST["validacion_num_operacion_unico"];
	$body["gen_validacion_cuenta_yape"]     =$_POST["gen_validacion_cuenta_yape"];
	$body["cuenta_id"]                      =$_POST["cuenta_id"];
	$body["observacion"]                    =$_POST["observacion"];
	$body["motivo"]                         =$_POST["motivo"];
	$body["registro"]                       =$_POST["registro"];
	$body["num_operacion"]                  =$_POST["num_operacion"];
	$body["bono_select"]                    =$_POST["bono_select"];
	$body["monto_deposito"]                 =$_POST["monto_deposito"];
	$body["monto_comision"]                 =$_POST["monto_comision"];
	$body["monto_real"]                     =$_POST["monto_real"];
	$body["monto_bono"]                     =$_POST["monto_bono"];
	$body["monto_total"]                    =$_POST["monto_total"];
	$body["id_abono_pendiente"]             =$_POST["id_abono_pendiente"];
	$body["origen_abono_pendiente"]         =$_POST["origen_abono_pendiente"];
	$body["permitir_dupl"]                  =$_POST["permitir_dupl"];
	$body["tipo_constancia"]                =$_POST["tipo_constancia"];
	$body["new_titular_abono"]              =$_POST["new_titular_abono"];

	$auditoria_id = api_calimaco_auditoria_insert('tlsValidateDeposit_guardar_validacion_deposito', $id_transaccion, 0, json_encode($body));
	if(!((int)$auditoria_id>0)){
		$result["http_code"] = 400;
		$result["query_valid_yape_erro"] = $mysqli->error;
		$result["status"] = "Ocurrió un error al guardar el log.";
		echo json_encode($result);exit();
	}

	if($new_titular_abono == "" || $new_titular_abono == null || $new_titular_abono == "null"){
		$new_titular_abono = '0';
	}

	if(!(strlen($id_abono_pendiente)>0)){
		$id_abono_pendiente = 0;
	}

	$update_origen_abono_pendiente = "";
	$insert_origen_abono_pendiente = "";


	$con_db_name   = env('DB_DATABASE_YAPE');
	$con_host      = env('DB_HOST_YAPE');
	$con_user      = env('DB_USERNAME_YAPE');
	$con_pass      = env('DB_PASSWORD_YAPE');
	$mysqli2 = new mysqli($con_host,$con_user,$con_pass,$con_db_name,3306);
	if ($mysqli2->connect_error){
		$result["http_code"] = 500;
		$result["status"] = "Conexión fallida: %s\n" . $mysqli2->connect_error;
		throw new Exception("Conexión fallida: %s\n" . $mysqli2->connect_error);
		echo json_encode($result);exit();
	}

	if ( $origen_abono_pend == "APK" ) {
		$update_origen_abono_pendiente = " txn_id = '".$id_abono_pendiente."', ";
		$insert_origen_abono_pendiente = " txn_id, ";

		$query_validate_pending = " 
			SELECT id
			FROM `at-yape`.transactions t
			WHERE t.state != 'pending'
			AND id = " . $id_abono_pendiente
		;
		$list_pending = $mysqli2->query($query_validate_pending);
		$array_pending = array();
		if ($mysqli2->error) {
			$result["query_validate_pending"] = $query_validate_pending;
			$result["query_error"] = $mysqli2->error;
			echo json_encode($result);exit();
		}
		while ($li = $list_pending->fetch_assoc()) {
			$array_pending[] = $li;
		}

		if ( count($array_pending) > 0 ) {
			$result["http_code"] = 400;
			$result["status"] = "El Yape Pendiente seleccionado ha sido validado, por favor actualizar la página.";
			echo json_encode($result);exit();
		}
	} else {
		$update_origen_abono_pendiente = " id_abono_pendiente = '".$id_abono_pendiente."', ";
		$insert_origen_abono_pendiente = " id_abono_pendiente, ";
	}
	
	$result["question_yape"] = 0;
	//Inicio de validacion yape
	if((int)$valid_cuenta_yape === 1 && (int)$id_estado === 1){
		$query_valid_yape = "
			SELECT tra.num_operacion 
			FROM tbl_televentas_clientes_transaccion tra 
			WHERE tra.tipo_id = 26 
			AND tra.cliente_id = (select cliente_id from tbl_televentas_clientes_transaccion ctt where ctt.id = " . $id_transaccion . ") 
				AND tra.estado = 1 
				AND tra.monto = " . $monto_real ." 
				AND date(tra.registro_deposito) >= date(date_add(now(), INTERVAL -1 DAY))
				AND tra.num_operacion = '". $num_operacion ."' 
				AND tra.cuenta_id = " . $cuenta_id;
		//$result["consulta_query_valid_yape"] = $query_valid_yape;
		$list_query = $mysqli->query($query_valid_yape);
		$list_valid_yape = array();
		if ($mysqli->error) {
			$result["http_code"] = 400;
			$result["query_valid_yape_erro"] = $mysqli->error;
			$result["status"] = "Ocurrió un error al consultar la existencia del Número de Operación.";
			echo json_encode($result);exit();
		} else {
			while ($li = $list_query->fetch_assoc()) {
				$list_valid_yape[] = $li;
			}
			if (count($list_valid_yape) == 0) {

			} elseif (count($list_valid_yape) > 0) {
				if($permitir_dupl == 0){
					$body["permitir_dupl"] = "Este número de operación ya fue registrado al cliente en el día \n ¿Desea registrar la operación?";
					api_calimaco_auditoria_update($auditoria_id, $id_transaccion, json_encode($body), 0);
					$result["http_code"] = 400;
					$result["question_yape"] = 1;
					$result["status"] = "Este número de operación ya fue registrado al cliente en el día \n ¿Desea registrar la operación?";
					echo json_encode($result);exit();
				}
			} else {
				$body["permitir_dupl"] = 'Ocurrió un error al consultar la existencia del Número de Operación';
				api_calimaco_auditoria_update($auditoria_id, $id_transaccion, json_encode($body), 0);
				$result["http_code"] = 400;
				$result["status"] = "Ocurrió un error al consultar la existencia del Número de Operación.";
				echo json_encode($result);exit();
			}
		}
	}
	//Fin de validacion yape

	if((int)$valid_num_ope_unico === 1 && (int)$id_estado === 1){
		$query_valid = "
					SELECT 
						tra.num_operacion, tra.registro_deposito fecha,
						IFNULL(CONCAT( cli.nombre, ' ', IFNULL( cli.apellido_paterno, '' ), ' ', IFNULL( cli.apellido_materno, '' ) ), '') AS cliente
					FROM 
						tbl_televentas_clientes_transaccion tra
					INNER JOIN tbl_televentas_clientes cli ON tra.cliente_id = cli.id
					WHERE tra.tipo_id = 26 
					AND tra.estado = 1 
					AND tra.num_operacion = '". $num_operacion ."' 
					AND tra.cuenta_id = '". $cuenta_id ."' 
					AND DATE(tra.registro_deposito) = DATE('" . $registro . "')
				";
		//$result["consulta_query_valid"] = $query_valid;
		$list_query = $mysqli->query($query_valid);
		$list_valid = array();
		if ($mysqli->error) {
			$result["http_code"] = 400;
			$result["query_valid"] = $mysqli->error;
			$result["status"] = "Ocurrió un error al consultar la existencia del Número de Operación.";
			echo json_encode($result);exit();
		} else {
			while ($li = $list_query->fetch_assoc()) {
				$list_valid[] = $li;
			}
			if (count($list_valid) == 0) {

			} elseif (count($list_valid) > 0) {
				$date = date_create($list_valid[0]["fecha"]);
				$date = date_format($date, 'd/m/Y H:i:s');
				$cliente = $list_valid[0]["cliente"];
				$body["valid_num_ope_unico"] = "El Número de Operación ya fué registrado \n Fecha: " . $date . " \n Por el cliente: " . $cliente;
				api_calimaco_auditoria_update($auditoria_id, $id_transaccion, json_encode($body), 0);
				
				$result["http_code"] = 400;
				$result["status"] = "El Número de Operación ya fué registrado \n Fecha: " . $date . " \n Por el cliente: " . $cliente;
				echo json_encode($result);exit();
			} else {
				$result["http_code"] = 400;
				$result["status"] = "Ocurrió un error al consultar la existencia del Número de Operación.";
				echo json_encode($result);exit();
			}
		}
	}
	$query_1 = "
				SELECT 
				  tra.id, 
				  tra.cliente_id, 
				  tra.id_tipo_contacto, 
				  tra.cuenta_id, 
				  tra.turno_id, 
				  tra.cc_id, 
				  tra.web_id, 
				  tra.bono_id, 
				  tra.monto_deposito, 
				  tra.monto, 
				  tra.bono_monto,
				  tra.user_id,
				  IFNULL(tra.id_juego_balance, 0) id_juego_balance,
				  IFNULL(ba1.balance, 0) balance, 
				  IFNULL(ba2.balance, 0) balance_bono_disponible, 
				  IFNULL(ba4.balance, 0) balance_deposito,
				  IFNULL(ba6.balance, 0) balance_dinero_at 
				FROM 
				  tbl_televentas_clientes_transaccion tra 
				  LEFT JOIN tbl_televentas_clientes_balance ba1 ON ba1.cliente_id = tra.cliente_id AND ba1.tipo_balance_id = 1 
				  LEFT JOIN tbl_televentas_clientes_balance ba2 ON ba2.cliente_id = tra.cliente_id AND ba2.tipo_balance_id = 2 
				  LEFT JOIN tbl_televentas_clientes_balance ba4 ON ba4.cliente_id = tra.cliente_id AND ba4.tipo_balance_id = 4 
				  LEFT JOIN tbl_televentas_clientes_balance ba6 ON ba6.cliente_id = tra.cliente_id AND ba6.tipo_balance_id = 6 
				WHERE 
				tra.id= $id_transaccion 
				AND tra.estado=0
				LIMIT 1
			";
	//$result["consulta_query"] = $query_1;
	$list_query = $mysqli->query($query_1);
	$list_transaccion = array();
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["query"] = $mysqli->error;
		$result["status"] = "Ocurrió un error al consultar el estado de la transacción.";
		echo json_encode($result);exit();
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$list_transaccion[] = $li;
		}
		if (count($list_transaccion) == 0) {
			$body["list_transaccion"] = "La transacción ya ha sido validada. Por favor actualice la pestaña. (F5)";
			api_calimaco_auditoria_update($auditoria_id, $id_transaccion, json_encode($body), 0);
			$result["http_code"] = 400;
			$result["status"] = "La transacción ya ha sido validada. Por favor actualice la pestaña. (F5)";
			echo json_encode($result);exit();
		} elseif (count($list_transaccion) == 1) {
			$transaccion_id = $list_transaccion[0]["id"];
			$cliente_id = $list_transaccion[0]["cliente_id"];
			//$monto = $list_transaccion[0]["monto"];
			//$bono_monto = $list_transaccion[0]["bono_monto"];
			$balance_actual = $list_transaccion[0]["balance"];
			$balance_deposito = $list_transaccion[0]["balance_deposito"];
			$balance_bono_disponible = $list_transaccion[0]["balance_bono_disponible"];
			$balance_dinero_at = $list_transaccion[0]["balance_dinero_at"];

			$usuario_cajero = $list_transaccion[0]["user_id"];
			
			$balance_nuevo = $balance_actual;
			$deposito_tipo_estado = 0;
			if ((int) $id_estado === 1) {
				$deposito_tipo_estado = 26;//Depósito Aprobado
				$balance_nuevo = $balance_actual + $monto_real;
				
			} else {
				$deposito_tipo_estado = 27;//Depósito Cancelado
			}
			if ((int) $cliente_id > 0) {
				// nuevo_balance = '" . $balance_nuevo . "',
				$query_update = "
						UPDATE tbl_televentas_clientes_transaccion 
						SET 
							estado = '" . $id_estado . "',
							registro_deposito = '" . $registro . "',
							num_operacion = '" . $num_operacion . "',
	                        comision_monto = '" . $monto_comision . "',
	                        monto = '" . $monto_real . "',
	                        bono_monto = '" . $monto_bono . "',
	                        total_recarga = '" . $monto_total . "',
							observacion_validador = '" . $observacion . "',
							tipo_rechazo_id = '" . $motivo . "',
							$update_origen_abono_pendiente
							update_user_id= '" . $usuario_id . "',
							update_user_at=now(),
							id_tipo_constancia = '" . $tipo_constancia . "',
							titular_abono = '" . $new_titular_abono  . "'
							
						WHERE id = '" . $id_transaccion . "'
					";
				$mysqli->query($query_update);

				$query_cont = "
				SELECT 
				cont_temp
				FROM tbl_usuarios
				where id= '" . $usuario_id . "' ";
				 
				$list_conte = $mysqli->query($query_cont);
				$list_exi = array();
				while ($li_e = $list_conte->fetch_assoc()) {
					$list_exi[] = $li_e;
				}

				if (is_null($list_exi[0]["cont_temp"])) {
					$query_upconteo = "UPDATE tbl_usuarios SET cont_temp = 0  WHERE id='" . $usuario_cajero . "' ";
					$mysqli->query($query_upconteo);
				}

				if ($list_exi[0]["cont_temp"] < 0) {
					$query_upconteo = "UPDATE tbl_usuarios SET cont_temp = 0  WHERE id='" . $usuario_cajero . "' ";
					$mysqli->query($query_upconteo);
				}

				if ($list_exi[0]["cont_temp"] > 0) {
					$query_upconteo = "UPDATE tbl_usuarios SET cont_temp = cont_temp -1  WHERE id='" . $usuario_cajero . "' ";
					$mysqli->query($query_upconteo);
				} 
						
				if ($mysqli->error) {
					$body["list_exi"] = "Ocurrió un error al guardar la validación";
					api_calimaco_auditoria_update($auditoria_id, $id_transaccion, json_encode($body), 0);
					$result["http_code"] = 400;
					$result["update_error"] = $mysqli->error;
					$result["status"] = "Ocurrió un error al guardar la validación.";
				} else {

					$insert_command = " 
						INSERT INTO tbl_televentas_clientes_transaccion (
							tipo_id,
							cliente_id,
							cuenta_id,
							turno_id,
							cc_id,
							web_id,
							num_operacion,
							registro_deposito,
							bono_id,
							monto_deposito,
							comision_monto,
							monto,
							bono_monto,
							total_recarga,
							nuevo_balance,
							estado,
							$insert_origen_abono_pendiente
							observacion_validador,
							transaccion_id,
							id_tipo_constancia,
							id_juego_balance,
							titular_abono,
							user_id,
							created_at
						) VALUES (
							$deposito_tipo_estado,
							'" . $list_transaccion[0]["cliente_id"] . "',
							'" . $list_transaccion[0]["cuenta_id"] . "',
							'" . $list_transaccion[0]["turno_id"] . "',
							'" . $list_transaccion[0]["cc_id"] . "',
							'" . $list_transaccion[0]["web_id"] . "',
							'" . $num_operacion . "',
							'" . $registro . "',
							'" . $list_transaccion[0]["bono_id"] . "',
							'" . $list_transaccion[0]["monto_deposito"] . "',
							'" . $monto_comision . "',
							'" . $monto_real . "',
							'" . $monto_bono . "',
							'" . $monto_total . "',
							'" . $balance_nuevo . "',
							'" . $id_estado . "',
							'" . $id_abono_pendiente . "',
							'" . $observacion . "',
							'" . $id_transaccion . "',
							'" . $tipo_constancia . "',
							'" . $list_transaccion[0]["id_juego_balance"] . "',
							'" . $new_titular_abono . "',							
							'" . $usuario_id . "',
							'".date('Y-m-d H:i:s')."'
						)
						";
					$mysqli->query($insert_command);

					$cmd_que = "
						SELECT tra.id
						FROM tbl_televentas_clientes_transaccion tra
						WHERE tra.tipo_id = '" . $deposito_tipo_estado . "'
						AND tra.turno_id = '" . $list_transaccion[0]["turno_id"] . "'
						AND tra.num_operacion = '" . $num_operacion . "'
						AND tra.monto_deposito = '" . $list_transaccion[0]["monto_deposito"] . "'
						AND tra.transaccion_id = '" . $id_transaccion . "'
						AND tra.user_id = '" . $usuario_id . "'
						AND tra.id_tipo_constancia = '" . $tipo_constancia . "'
						AND tra.titular_abono = '" . $new_titular_abono . "' 
					";
					$result["cmd_que"] = $cmd_que;
					$list_cmd_que_query = $mysqli->query($cmd_que);
					$list_cmd_que = array();
					if ($mysqli->error) {
						$result["http_code"] = 400;
						$result["query"] = $mysqli->error;
						$result["status"] = "Ocurrió un error al consultar el estado de la transacción de validación.";
						echo json_encode($result);exit();
					} else {
						while ($li = $list_cmd_que_query->fetch_assoc()) {
							$list_cmd_que[] = $li;
						}
					}
					$result["count"] = count($list_cmd_que);
					if(count($list_cmd_que) == 1){
						//if($monto_comision > 0){
							$id_trans_valid = $list_cmd_que[0]["id"];
							//**************************************************************************************************
							//**************************************************************************************************
							// IMAGEN
							//**************************************************************************************************
							if(isset($_FILES['imagen_voucher'])){
								$path = "/var/www/html/files_bucket/depositos/";
								$file = [];
								$imageLayer = [];
								if (!is_dir($path))
									mkdir($path, 0777, true);
								$imageProcess = 0;

								$filename = $_FILES['imagen_voucher']['tmp_name'];
								$filenametem = $_FILES['imagen_voucher']['name'];
								$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
								if ($filename != "") {
									$fileExt = pathinfo($_FILES['imagen_voucher']['name'], PATHINFO_EXTENSION);
									$resizeFileName = $id_trans_valid . "_" . date('YmdHis');
									$nombre_archivo = $resizeFileName . "." . $fileExt;
									if ($fileExt == "pdf") {
										move_uploaded_file($_FILES['imagen_voucher']['tmp_name'], $path . $nombre_archivo);
									} else {
										$sourceProperties = getimagesize($filename);
										$size = $_FILES['imagen_voucher']['size'];
										$uploadImageType = $sourceProperties[2];
										$sourceImageWith = $sourceProperties[0];
										$sourceImageHeight = $sourceProperties[1];
										switch ($uploadImageType) {
											case IMAGETYPE_JPEG:
												$resourceType = imagecreatefromjpeg($filename);
												break;
											case IMAGETYPE_PNG:
												$resourceType = imagecreatefrompng($filename);
												break;
											case IMAGETYPE_GIF:
												$resourceType = imagecreatefromgif($filename);
												break;
											default:
												$imageProcess = 0;
												break;
										}
										$imageLayer = sec_tlv_dep_resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);

										$file[0] = imagegif($imageLayer[0], $path . $nombre_archivo);
										$file[1] = imagegif($imageLayer[1], $path . "min_" . $nombre_archivo);
										move_uploaded_file($file[0], $path . $nombre_archivo);
										move_uploaded_file($file[1], $path . $nombre_archivo);
										$imageProcess = 1;
									}

									$comando = " INSERT INTO tbl_televentas_transaccion_archivos
													(transaccion_id,tipo,archivo,created_at,estado)
													VALUES(
														'" . $id_trans_valid . "',
														1,
														'" . $nombre_archivo . "',
														'" . date('Y-m-d H:i:s') . "',
														1
														)";
									$mysqli->query($comando);
									$archivo_id = mysqli_insert_id($mysqli);
									$filepath = $path . $resizeFileName . "." . $fileExt;
								}
							}
							
							//**************************************************************************************************
							//**************************************************************************************************
						//}
						if ((int)$id_estado === 1 && (int)$deposito_tipo_estado === 26) {
							if((int)$id_abono_pendiente > 0){

								if ( $origen_abono_pend == "APK" ) {
									$query_validate = " 
										UPDATE `at-yape`.transactions
										SET
											state = 'validated',
											updated_at = '".date('Y-m-d H:i:s')."',
											external_user = '" . $usuario . "', 
											updated_by = 1
										WHERE id = " . $id_abono_pendiente;
									$mysqli2->query($query_validate);
									if ($mysqli2->error) {
										$result["query_validate"] = $query_validate;
										$result["update_error"] = $mysqli2->error;
										echo json_encode($result);exit();
									}
								} else {
									$query_1 ="
										UPDATE tbl_televentas_abonos_pendientes 
										SET
											estado_abono_id = 2,
											updated_user_id = '" . $usuario_id . "',
											updated_at = now()
										WHERE id = " . $id_abono_pendiente;
									$mysqli->query($query_1);
									if ($mysqli->error) {
										$result["http_code"] = 400;
										$result["update_error"] = $mysqli->error;
										$result["status"] = "Ocurrió un error al actualizar el estado del abono pendiente.";
										echo json_encode($result);exit();
									}
								}

								
							}

							$balance_deposito_NUEVO = $balance_deposito + $monto_real;
		                    $balance_bono_disponible_NUEVO = $balance_bono_disponible + $monto_bono;

							sec_tlv_dep_tbl_televentas_clientes_balance('update', $cliente_id, 1, $balance_nuevo);
							sec_tlv_dep_tbl_televentas_clientes_balance_transaccion('insert', $id_trans_valid, 
								$cliente_id, 1, $balance_actual, $monto_real, $balance_nuevo);

							sec_tlv_dep_tbl_televentas_clientes_balance('update', $cliente_id, 2, $balance_bono_disponible_NUEVO);
							sec_tlv_dep_tbl_televentas_clientes_balance_transaccion('insert', $id_trans_valid, 
								$cliente_id, 2, $balance_bono_disponible, $monto_bono, $balance_bono_disponible_NUEVO);

							sec_tlv_dep_tbl_televentas_clientes_balance('update', $cliente_id, 4, $balance_deposito_NUEVO);
							sec_tlv_dep_tbl_televentas_clientes_balance_transaccion('insert', $id_trans_valid, 
								$cliente_id, 4, $balance_deposito, $monto_real, $balance_deposito_NUEVO);

							//**************************************************************************************************
							// LÓGICA PARA ASIGNAR EL BALANCE PROMOCIONAL - CLIENTES QUE FUERON INCLUIDOS CON PORCENTAJE
								// Consultar si aún no se le asigna el monto al cliente, en una promo activa
								$query_promo_no_asignada = "
									SELECT
										e.id,
										ec.cliente_id,
										e.monto_cliente,
										IFNULL(e.rollover, 0) rollover,
										ec.user_id
									FROM tbl_televentas_dinero_at_eventos e
										INNER JOIN tbl_televentas_dinero_at_eventos_clientes ec ON e.id = ec.dinero_at_evento_id
									WHERE 
										e.estado = 1
										AND ec.estado = 0
										AND ec.monto = 0
										AND e.tipo_monto = 2
										AND e.porcentaje_monto_minimo <= $monto_real
										AND ec.cliente_id = $cliente_id
										AND CURDATE() BETWEEN e.fecha_inicio AND e.fecha_fin
									LIMIT 1
								";
								$list_query_promo_no_asignada = $mysqli->query($query_promo_no_asignada);
								if ($mysqli->error) {
									$result["http_code"] = 400;
									$result["status"] = "Ocurrió un error al consultar si el cliente cuenta con saldo promocional no asignado. Por favor comunique a SISTEMAS. De igual manera se informa que el flujo de la validación a sido correcto.";
									$result["query"] = $query_promo_no_asignada;
									$result["query_error"] = $mysqli->error;
									echo json_encode($result);exit();
								}
								$list_cliente_sin_promo_asignada = array();
								while ($li = $list_query_promo_no_asignada->fetch_assoc()) {
									$list_cliente_sin_promo_asignada[] = $li;
									$porcentaje = $li["monto_cliente"];
									$rollover = $li["rollover"];
									$evento_id = $li["id"];
									$usuario_asigna_dinero_at = $li["user_id"];
								}
								if ( count($list_cliente_sin_promo_asignada)===1 ) {
									$monto_asignar = round( (($monto_real*$porcentaje)/100), 2 );

									$rollover_monto = 'NULL';
									if ( (int)$rollover > 0 ) {
										$rollover_monto = $monto_asignar*$rollover;
									}

									$update_asignar_dinero_at = "
										UPDATE tbl_televentas_dinero_at_eventos_clientes
										SET monto = $monto_asignar,
											rollover_monto = $rollover_monto,
											estado = 1,
											updated_at = now(),
											updated_user_id = '" . $usuario_id . "'
										WHERE cliente_id = $cliente_id
											AND dinero_at_evento_id = $evento_id
											AND estado = 0
									";
									$mysqli->query($update_asignar_dinero_at);
									if ($mysqli->error) {
										$result["query"] = $update_asignar_dinero_at;
										$result["query_error"] = $mysqli->error;
										echo json_encode($result);exit();
									}

									// Asignar etiqueta
									$query_consultar_etiqueta = "
										SELECT 
											ce.id
										FROM tbl_televentas_clientes_etiqueta ce
										WHERE 
											ce.client_id = '" . $cliente_id . "' 
											AND ce.etiqueta_id = 43
											AND ce.status = 1 
									";
									$list_query_ce = $mysqli->query($query_consultar_etiqueta);
									if ($mysqli->error) {
										$result["query"] = $query_consultar_etiqueta;
										$result["query_error"] = $mysqli->error;
										echo json_encode($result);exit();
									}
									$list_ce = array();
									while ($li_ce = $list_query_ce->fetch_assoc()) {
										$list_ce[] = $li_ce;
									}
									if (count($list_ce) === 0) { // Si no tiene la etiqueta, se le inserta
										$insert_etiqueta = "
											INSERT INTO tbl_televentas_clientes_etiqueta
											(
												client_id,
												etiqueta_id,
												status,
												created_user_id,
												created_at
											) VALUES (
												'" . $cliente_id . "',
												43,
												'1',
												'" . $usuario_id . "',
												NOW()
											)
										";
										$mysqli->query($insert_etiqueta);
										if ($mysqli->error) {
											$result["query"] = $insert_etiqueta;
											$result["query_error"] = $mysqli->error;
											echo json_encode($result);exit();
										}
									}

									if ( (float)$balance_dinero_at==0 ) {
										$respu = query_tbl_televentas_clientes_transaccion(17, $cliente_id, $monto_asignar, $monto_asignar, 6, $evento_id, $usuario_asigna_dinero_at);

										if ( (int)$respu["http_code"]===400){
											$result["http_code"] = 400;
											$result["asignar_dinero_at"] = "Error al obtener el id al momento de subir balance de Dinero AT.";
											echo json_encode($result);exit();
										} else if ( (int)$respu["http_code"]===200) {
											$transaccion_id = $respu["id"];
										}
										sec_tlv_dep_tbl_televentas_clientes_balance('update', $cliente_id, 6, $monto_asignar);
										sec_tlv_dep_tbl_televentas_clientes_balance_transaccion('insert', $transaccion_id, $cliente_id, 6, $balance_dinero_at, $monto_asignar, $monto_asignar);
										$result["asignar_dinero_at"] = "Se asignó correctamente el Saldo promocional.";

									} else {
										$result["asignar_dinero_at"] = "No se asignó el Saldo promocional, porque el cliente cuenta con Saldo promocional.";
									}
								}
								
							//**************************************************************************************************
							
							$body["balance_actual"] = $balance_actual;
							$body["balance_nuevo"] = $balance_nuevo;
							$body["balance_deposito"] = $balance_deposito;
							$body["balance_deposito_NUEVO"] = $balance_deposito_NUEVO;
							$body["id_trans_valid"] = $id_trans_valid;
							api_calimaco_auditoria_update($auditoria_id, $id_transaccion, json_encode($body), 1);
						}
						$result["http_code"] = 200;
						$result["status"] = "ok";
					}else{
						$result["http_code"] = 400;
						$result["status"] = "Ocurrió un error al insertar la validación";
						echo json_encode($result);exit();
					}
				}
			} else {
				$result["http_code"] = 400;
				$result["status"] = "No pudimos obtener al cliente.";
			}
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al consultar el estado de la transacción.";
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
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$busqueda_fecha_inicio = $_POST["fecha_inicio"];
	$busqueda_fecha_fin    = $_POST["fecha_fin"];
	$busqueda_estado       = $_POST["estado"];
	$busqueda_validador    = $_POST["validador"];
	$busqueda_autovalidacion = $_POST["autovalidacion"];
	$busqueda_tipo_constancia = $_POST["tipo_constancia"];

	$busqueda_cuenta       = "";
	if (isset($_POST["cuenta"]) && $_POST["cuenta"] !== '') {
		$busqueda_cuenta = implode(",",$_POST["cuenta"]) ;
	}

	$usuario_id = $login["id"];
	$area_id = $login ? $login['area_id'] : 0;

	$where_users_test="";
	if((int)$area_id !== 6) {// diferente de sistemas
		$where_users_test="	
			AND IFNULL(tra.web_id, '') not in ('3333200', '71938219') 
			AND IFNULL(tra.user_id, '') not in (1, 249, 250, 2572, 3028)
		";
	}

	$where_tipo_tra = 'tra.tipo_id =1';

	$where_estado = '';
	if ($busqueda_estado !== '') {
		if($busqueda_estado == '1'){ // Aprobado
			/*$where_tipo_tra = '(
									(tra.tipo_id = 1 and tra.estado = 1)
								) ';*/
			/*$where_tipo_tra = '(
									(tra.tipo_id = 1 and tra.estado = 1 AND IFNULL(tra_2.estado, 0) != 3 AND IFNULL(tra.caja_vip,0) IN (0,2)) 
									OR (tra.tipo_id = 1 and tra.estado = 1 AND IFNULL(tra_2.estado, 0) = 0 AND IFNULL(tra.caja_vip,0) = 1) 
									OR (tra.tipo_id = 26 and tra.estado = 1 and IFNULL(tra.caja_vip,0) = 1)
								) ';*/
			$where_estado = ' AND tra.estado = 1 AND IFNULL(tra_2.estado, 0) != 3 '; //' AND tra.estado = 1 AND IFNULL(tra_2.estado,0) != 3';
		} else if($busqueda_estado == '2'){ //Rechazado
			/*$where_tipo_tra = '
								(tra.tipo_id = 1 AND tra.estado = 2 AND IFNULL(tra_2.estado, 0) = 0 ) -- AND IFNULL(tra.caja_vip, 0) != 1)
								';*/
			$where_estado = ' AND tra.estado = 2  ';
		} else if ($busqueda_estado == '3'){ //Eliminado
			/*$where_tipo_tra = ' 
								(
									(tra.tipo_id = 1 AND tra.estado = 3 AND IFNULL(tra_2.estado, 0) = 3 AND IFNULL(tra.caja_vip, 0) != 1)
									OR (tra.tipo_id = 26 AND tra.estado = 3 AND tra.caja_vip = 1)
								)
								';*/
			$where_estado = ' AND tra.estado = 1 AND IFNULL(tra_2.estado, 0) = 3 '; 
		}else{
			$where_estado = ' AND tra.estado = ' . $busqueda_estado . ' ';
		}
	} else { // Todos
		$where_estado = '';
		/*$where_tipo_tra = ' (
								(tra.tipo_id = 1 and tra.estado IN (0,1,2,3) ) 
								-- (tra.tipo_id = 1 and tra.estado IN (0,1,2,3) AND IFNULL(tra_2.estado, 0) = 0 AND IFNULL(tra.caja_vip,0) IN (0,2)) 
								-- OR (tra.tipo_id = 1 and IFNULL(tra_2.estado, 0) = 0 AND IFNULL(tra.caja_vip,0) = 1) 
								-- OR (tra.tipo_id = 1 and tra_2.estado IN(1,3) and IFNULL(tra.caja_vip,0) IN (0,2))
								-- OR (tra.tipo_id = 26 and tra.estado IN(1,3) and IFNULL(tra.caja_vip,0) = 1)
							) ';*/
	}

	$where_validador = '';
	if ((int) $busqueda_validador > 0) {
		$where_validador = ' AND tra.update_user_id=' . $busqueda_validador . ' ';
	}

	$where_fecha_inicio = " AND tra.created_at >= '" . $busqueda_fecha_inicio . " 00:00:00' ";
	$where_fecha_fin = " AND tra.created_at <= '" . $busqueda_fecha_fin . " 23:59:59' ";

	$where_cuenta = "";
	if ($busqueda_cuenta !== '') {
		$where_cuenta = " AND cuen.id IN ($busqueda_cuenta) ";
	} else {
		$where_cuenta = "
			AND cuen.id IN (
							SELECT
								uca.cuenta_apt_id
							FROM
								tbl_usuario_cuentas_apt AS uca
							WHERE uca.usuario_id = $usuario_id AND uca.estado = 1
							GROUP BY uca.cuenta_apt_id
			) 
		";
	}

	$where_autovalidacion = "";
	if($busqueda_autovalidacion == 1){
		$where_autovalidacion = " AND IFNULL(tra.caja_vip, 0) != 2 ";
	}else if($busqueda_autovalidacion == 2){
		$where_autovalidacion = " AND IFNULL(tra.caja_vip, 0) = 2 ";
	}

	$where_tipo_constancia = "";
	 if($busqueda_tipo_constancia <> 0){
		$where_tipo_constancia = " AND IFNULL(tra.id_tipo_constancia, 0) =$busqueda_tipo_constancia ";
	}

	$query_1 = "
		SELECT
			tra.id,
			tra.created_at fecha_hora_registro,
			CASE WHEN IFNULL(tra.txn_id, '') != '' THEN 'SI' ELSE 'NO' END autovalidacion,
			usu.usuario AS cajero,
			UPPER(IFNULL(loc.nombre, '')) AS turno_local,
			IFNULL(CONCAT( cli.nombre, ' ', IFNULL( cli.apellido_paterno, '' ), ' ', IFNULL( cli.apellido_materno, '' ) ), '') AS cliente,
			IFNULL(cuen.cuenta_descripcion, '') AS cuenta,
			IFNULL(tra.registro_deposito, '') AS registro_deposito,
			IFNULL(tra.num_operacion, '') AS num_operacion,
			IFNULL(tra.monto_deposito, 0) AS monto_deposito,
			IFNULL(tra.comision_monto, 0) AS comision_monto,
			IFNULL(tra.monto, 0) AS monto,
			IFNULL(tra.bono_monto, 0) AS bono_monto,
			IFNULL(tra.total_recarga, 0) AS total_recarga,
			(CASE
				WHEN tra.estado = 0 THEN
				'Pendiente' 
				WHEN tra.estado = 1 AND IFNULL(tra_2.estado,0) != 3 THEN
				'Aprobado' 
				WHEN tra.estado = 2 THEN
				'Rechazado' 
				WHEN tra.estado = 3 or tra_2.estado = 3 THEN
				'Eliminado'
			END) AS estado,
            IFNULL(tr.tipo_rechazo,'') tipo_rechazo,
			-- val.usuario AS validador_usuario,
			IFNULL(CONCAT( apt.nombre, ' ', IFNULL( apt.apellido_paterno, '' ), ' ', IFNULL( apt.apellido_materno, '' ) ), '') AS validador_nombre,
			IFNULL(tra.update_user_at, '') fecha_hora_validacion,
			IFNULL(REPLACE(REPLACE(tra.observacion_cajero, '\n', ''), '\n', ''), '') observacion_cajero,
			IFNULL(REPLACE(REPLACE(tra.observacion_validador, '\n', ''), '\n', ''), '') observacion_validador, 
			IFNULL(tip_c.descripcion, '') tipo_constancia,
			IFNULL(rec_bon.nombre, 'Ninguno') bono_nombre,
			UPPER(IFNULL(tit_abon.nombre_apellido_titular, IFNULL(CONCAT( cli.nombre, ' ', IFNULL( cli.apellido_paterno, '' ), ' ', IFNULL( cli.apellido_materno, '' ) ), ''))) titular_abono 
		FROM
			tbl_televentas_clientes_transaccion tra
			LEFT JOIN tbl_televentas_recargas_bono bon ON bon.id = tra.bono_id 
			LEFT JOIN tbl_usuarios usu ON usu.id = tra.user_id
			LEFT JOIN tbl_televentas_clientes cli ON cli.id = tra.cliente_id
			LEFT JOIN tbl_usuarios val ON val.id = tra.update_user_id
			LEFT JOIN tbl_personal_apt apt ON apt.id = val.personal_id
			LEFT JOIN tbl_televentas_cuentas tc ON tc.cuenta_apt_id = tra.cuenta_id
			LEFT JOIN tbl_cuentas_apt cuen ON cuen.id = tc.cuenta_apt_id
			LEFT JOIN tbl_locales loc ON loc.cc_id = tra.cc_id 
			LEFT JOIN tbl_televentas_tipo_contacto ttc ON ttc.id = tra.id_tipo_contacto
			LEFT JOIN tbl_televentas_recargas_bono rec_bon ON tra.bono_id = rec_bon.id
			LEFT JOIN tbl_televentas_tipo_rechazo tr ON tra.tipo_rechazo_id = tr.id
			LEFT JOIN tbl_televentas_titular_abono tit_abon ON tra.titular_abono = tit_abon.id
			LEFT JOIN tbl_televentas_tipo_constancia tip_c ON tra.id_tipo_constancia = tip_c.id
			LEFT JOIN tbl_televentas_clientes_transaccion tra_2 ON tra.id = tra_2.transaccion_id  AND tra_2.tipo_id = 26
		WHERE
		$where_tipo_tra
		AND tra.cliente_id > 0
		$where_users_test
		$where_fecha_inicio 
		$where_fecha_fin 
		$where_estado 
		$where_validador 
		$where_cuenta 
		$where_autovalidacion
		$where_tipo_constancia
		ORDER BY tra.id ASC
	";
	$result["consulta_query_xls"] = $query_1;
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
			"id" => "ID",
			"fecha_hora_registro" => "Fecha Registro",
			"autovalidacion" => "Autovalidación",
			"cajero" => "Promotor",
			"turno_local" => "Caja",
			"cliente" => "Cliente",
			"cuenta" => "Cuenta",
			"registro_deposito" => "Fecha Abono",
			"num_operacion" => "Núm. Operación",
			"monto_deposito" => "Depósito S/",
			"comision_monto" => "Comisión S/",
			"monto" => "Real S/",
			"bono_monto" => "Bono S/",
			"total_recarga" => "Recarga S/",			
			"estado" => "Estado",
			"tipo_rechazo" => "Motivo Rechazo",
			"validador_nombre" => "Validador",
			"fecha_hora_validacion" => "Fecha Validación",
			"observacion_cajero" => "Observación Cajero",
			"observacion_validador" => "Observación Validador",
			"tipo_constancia" => "Constancia",
			"bono_nombre" => "Bono Nombre",
			"titular_abono" => "Titular de Abono"
		];
		array_unshift($result_data, $headers);

		ini_set('display_errors', 0);
		ini_set('display_startup_errors', 0);
		error_reporting(0);

		require_once '../phpexcel/classes/PHPExcel.php';
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getActiveSheet()->fromArray($result_data, null, 'A1');
		$date = new DateTime();
		$file_title = "reporte_televentas_depositos_" . $date->getTimestamp();

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

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_tlv_dep_actualizar_fecha_abono") {
	$id_transaccion = $_POST["id_transaccion"];
	$fecha_hora_actual = str_replace('T', ' ', $_POST["fecha_hora_actual"]);
	$fecha_hora_modificada = str_replace('T', ' ', $_POST["fecha_hora_modificada"]);

	$id_usuario = $login ? $login['id'] : null;

	$limite_dias = 0;
	$query_limit_dias = "
		SELECT valor
		FROM tbl_televentas_parametros
		WHERE nombre_codigo = 'editar_depositos_pagos' AND estado = 1
		LIMIT 1
	";
	$list_query_limit_dias = $mysqli->query($query_limit_dias);
	$list_limit_dias = array();
	if($mysqli->error){
		$result["query"] = $mysqli->error;
		$result["status"] = "Ocurrió un error al consultar el parámetro de dias para editar los depósitos y retiros aprobados.";
		echo json_encode($result);exit();
	}else{
		while ($li = $list_query_limit_dias->fetch_assoc()) {
			$list_limit_dias[] = $li;
		}
		if (count($list_limit_dias) == 0) {
			$result["http_code"] = 400;
			$result["status"] = "No hay datos registrados para el parámetro editar depósitos y retiros.";
			echo json_encode($result);exit();
		} else{
			$limite_dias = $list_limit_dias[0]["valor"];
			$result["http_code"] = 200;
			$result["status"] = "Ok";
		}
	}

	if($limite_dias == 0){
		$result["http_code"] = 400;
		$result["status"] = "Está retornando 0 como valor de límite de días";
		echo json_encode($result);exit();
	}

	$query_1 = "
		SELECT tra.id, IFNULL(tra.created_at, '') fecha_creacion
		FROM tbl_televentas_clientes_transaccion tra
		WHERE tra.id = $id_transaccion
		LIMIT 1
	";
	$list_query = $mysqli->query($query_1);
	$list_transaccion = array();
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["query"] = $mysqli->error;
		$result["status"] = "Ocurrió un error al consultar el estado de la transacción.";
		echo json_encode($result);exit();
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$list_transaccion[] = $li;
		}
		if (count($list_transaccion) == 0) {
			$result["http_code"] = 400;
			$result["status"] = "Hubo un error al consultar la transacción, intentar nuevamente, por favor.";
			echo json_encode($result);exit();
		} elseif (count($list_transaccion) == 1) {
			$fecha_creacion = $list_transaccion[0]["fecha_creacion"];
			$date1 = new DateTime("now");
			$date2 = new DateTime($fecha_creacion);
			$diff = $date1->diff($date2);
			$diff = $diff->days;

			if($diff > $limite_dias){
				$result["http_code"] = 400;
				$result["status"] = "No puede editar registros de más de ".$limite_dias." días de antiguedad.";
				echo json_encode($result);exit();
			}
		}
	}

	$cmd_update_change = "
			UPDATE tbl_televentas_clientes_transaccion_modificaciones 
			SET 
				status = 0
			WHERE transaccion_id = " . $id_transaccion . " AND status = 1 AND campo_name = 'registro_deposito'" ;
	$mysqli->query($cmd_update_change);

	$cmd_insert_change = " 
		INSERT INTO 
			tbl_televentas_clientes_transaccion_modificaciones
			(transaccion_id,tabla,campo_name,valor_original,valor_nuevo,user_id,status,created_at)
		VALUES(
			" . $id_transaccion . ",
			'tbl_televentas_transaccion_clientes_transaccion',
			'registro_deposito',
			'" . $fecha_hora_actual . "',
			'" . $fecha_hora_modificada . "',
			" . $id_usuario . ",
			1,
			now()
			)";
	$mysqli->query($cmd_insert_change);

	$error_insert = "";
	$query_insert = "INSERT INTO tbl_televentas_transaccion_audit (
					id_transaccion,
					registro_deposito_actual,
					registro_deposito_nuevo,
					status,
					user_at,
					created_at
					) VALUES (
					'".$id_transaccion."',
					'".$fecha_hora_actual."',
					'".$fecha_hora_modificada."',
					1,
					'".$id_usuario."',now())
					";
	$mysqli->query($query_insert);

	if($mysqli->error){
		$error_insert .= 'Error al insertar la modificación de la transacción: ' . $mysqli->error . $query;
	}

	$error_update = "";
	$query_update = "UPDATE tbl_televentas_clientes_transaccion 
					set registro_deposito = '" . $fecha_hora_modificada. "' WHERE id = " . $id_transaccion;
	$mysqli->query($query_update);

	if($mysqli->error){
		$error_update .= 'Error al insertar la modificación de la transacción: ' . $mysqli->error . $query;
	}


	if ($error_insert == '' && $error_update == '') {
		$result["http_code"] = 200;
	} else {
		$result["http_code"] = 400;
	}
	//$result["query_insert"] = $query_insert;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["error_insert"] = $error_insert;
	$result["error_update"] = $error_update;
}


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_abonos_pendientes") {
	$banco_id		  = (int)$_POST["cuenta_id"];
	$id_cliente 	  = (int)$_POST["id_cliente"];
	$id_titular_abono = (int)$_POST["id_titular_abono"];
	$titular_abono    = $_POST["titular_abono"];
	$monto_deposito   = (double)$_POST["monto_deposito"];
	$fecha_registro   = $_POST["fecha_hora_registro"];

	$query ="
		SELECT 
			tap.id AS cod_transaccion,
			tap.fecha_operacion,
			tap.hora_operacion,
			tapm.nombre AS nombre_medio,
			IFNULL(ca.cuenta_descripcion, '') AS nombre_banco,
			IFNULL(tap.name_file, '') AS nombre_imagen,
			tap.created_at AS fecha_registro,
			IFNULL(tap.monto, 0) AS monto,
			IFNULL(tap.nro_operacion, 0) AS nro_operacion,
			IFNULL(tap.comision_id, 0) AS comision_id,
			IFNULL(tap.observacion, '') as observacion,
			IFNULL(tap.estado_abono_id, '') as estado_abono,
			IFNULL(u.usuario, '') AS usuario,
			IF(tap.cliente_id > 0 OR isnull(tap.cliente_id),
				IFNULL(CONCAT( tc.nombre, ' ', IFNULL( tc.apellido_paterno, '' ), ' ', IFNULL( tc.apellido_materno, '' ) ), ''),
				'') AS cliente,
			'TLS' origen_abono_pendiente
		FROM
			tbl_televentas_abonos_pendientes tap
			INNER JOIN tbl_cuentas_apt ca ON ca.id = tap.banco_id
			INNER JOIN tbl_televentas_cuentas tcuen ON tcuen.cuenta_apt_id = tap.banco_id
			INNER JOIN tbl_televentas_abonos_pendientes_medio tapm ON tapm.id = tap.medio_id
			INNER JOIN tbl_usuarios u ON u.id = tap.user_id
			LEFT JOIN tbl_televentas_clientes tc ON tc.id = tap.cliente_id
		WHERE
			tap.estado_abono_id = 1
			AND tap.status = 1
			AND tcuen.id = '" . $banco_id . "'
			AND tap.monto = '" . $monto_deposito . "'
		ORDER BY 
			tap.fecha_operacion DESC,
			tap.created_at DESC
		";
	
	$list_query = $mysqli->query($query);
	$list = array();
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}

	$consulta_cmd = "
		SELECT
			ca.id,
			ca.cuenta_descripcion
		FROM
			tbl_televentas_cuentas tc
			JOIN tbl_cuentas_apt ca ON ca.id = tc.cuenta_apt_id 
		WHERE
			tc.status = 1 
			AND IFNULL(tc.valid_caja7, 0) = 0
			AND ca.id not in (12)
            AND tc.valid_cuenta_yape = 1
		ORDER BY ca.orden ASC
		";

	$list_query_yape = $mysqli->query($consulta_cmd);
	if ($mysqli->error) {
		$result["query"] = $consulta_cmd;
		$result["error"] = $mysqli->error;
		echo json_encode($result); exit();
	}
	$list_yapes = array();
	$yape = null;
	while ($li = $list_query_yape->fetch_assoc()) {
		$list_yapes[] = $li;
		if ( $li["id"] == (string)$banco_id ){
			$yape = $li["cuenta_descripcion"];
			break;
		}
	}
	
	$list_yape_pendientes = array();
	if (!is_null($yape)) {
		$result["yape"] = $yape;

		$query_cliente = "
					SELECT 
						IFNULL(
							TRIM(
								CONCAT( 
									IFNULL( TRIM(c.nombre), '' )
									,
									IF(
										c.apellido_paterno IS NOT NULL AND c.apellido_paterno != '',
										CONCAT(' ', TRIM(c.apellido_paterno)),
										''
									)
									,
									IF(
										c.apellido_materno IS NOT NULL AND c.apellido_materno != '',
										CONCAT(' ', TRIM(c.apellido_materno)),
										''
									)
								)
							)
							, ''
						) nombre1
						,
						IFNULL(
							TRIM(
								CONCAT( 
									IFNULL( TRIM(c.apellido_paterno), '' )
									,
									IF(
										c.apellido_materno IS NOT NULL AND c.apellido_materno != '',
										CONCAT(' ', TRIM(c.apellido_materno)),
										''
									)
									,
									IF(
										c.nombre IS NOT NULL AND c.apellido_paterno != '',
										CONCAT(' ', TRIM(c.nombre)),
										''
									)
								)
							)
							, ''
						) nombre2
						,
						IFNULL(
							TRIM(
								CONCAT( 
									IFNULL( TRIM(c.nombre), '' )
									,
									IF(
										c.apellido_paterno IS NOT NULL AND c.apellido_paterno != '',
										CONCAT(' ', TRIM(c.apellido_paterno)),
										''
									)
								)
							)
							, ''
						) nombre3
					FROM 
						tbl_televentas_clientes c
					WHERE c.id = $id_cliente
					LIMIT 1
		";
		//$result["consulta_query_valid"] = $query_valid;
		$list_cliente_0 = $mysqli->query($query_cliente);
		$list_cliente = array();
		if ($mysqli->error) {
			$result["query_cliente"] = $query_cliente;
			$result["error"] = $mysqli->error;
			echo json_encode($result);exit();
		}
		while ($li = $list_cliente_0->fetch_assoc()) {
			$list_cliente[] = $li;
		}

		$where_monto = " AND t.amount = '" . $monto_deposito . "' ";
		$where_yape = "";
		if (count($list_cliente) === 1) {
			if ( $id_titular_abono != 0 ) {
				$where_yape = "
					AND (
						UPPER(t.person) LIKE '%" . strtoupper($list_cliente[0]["nombre1"]) . "%' 
						OR UPPER(t.person) LIKE '%" . strtoupper($list_cliente[0]["nombre2"]) . "%'
						OR UPPER(t.person) LIKE '%" . strtoupper($list_cliente[0]["nombre3"]) . "%'
					) 
				";
			} else {
				$where_yape = " AND ( UPPER(t.person) LIKE '%" . strtoupper($titular_abono) . "%' ) ";
			}

			$con_db_name   = env('DB_DATABASE_YAPE');
			$con_host      = env('DB_HOST_YAPE');
			$con_user      = env('DB_USERNAME_YAPE');
			$con_pass      = env('DB_PASSWORD_YAPE');
			$mysqli2 = new mysqli($con_host,$con_user,$con_pass,$con_db_name,3306);
			if ($mysqli2->connect_error){
				$result["http_code"] = 500;
				$result["status"] = "Conexión fallida: %s\n" . $mysqli2->connect_error;
				throw new Exception("Conexión fallida: %s\n" . $mysqli2->connect_error);
			}

			$query_yape_pend = "
				SELECT 
					t.id cod_transaccion,
					DATE(t.register_date) fecha_operacion,
					TIME(t.register_date) hora_operacion,
					'Yape - APK' nombre_medio,
					CONCAT('APK', ' - ', t.description) nombre_banco,
					'' nombre_imagen,
					t.created_at fecha_registro,
					t.amount monto,
					' - ' nro_operacion,
					'0.00' comision_id,
					'' observacion,
					CASE
						WHEN t.state = 'pending' THEN 1
						WHEN t.state = 'validated' THEN 2
						WHEN t.state = 'cancelled' THEN 3
						ELSE ''
					END estado_abono,
					'' usuario,
					t.person cliente,
					'APK' origen_abono_pendiente
				FROM `at-yape`.transactions t
				WHERE 
					t.state = 'pending' 
					#AND t.created_at >=  date_add('" . $fecha_registro . "', INTERVAL 0 MINUTE)
					AND t.created_at >= date_sub('" . $fecha_registro . "', INTERVAL " . sec_tlv_deposito_get_value_parameter('days_yapes_pending_before_validate_deposit') . " DAY) 
					#AND t.created_at <= date_add('" . $fecha_registro . "', INTERVAL 3 MINUTE)
					AND UPPER('" . $yape . "') LIKE CONCAT('%', UPPER(description), '%')
				$where_monto
				$where_yape
			";
			$list_query_yape_pend = $mysqli2->query($query_yape_pend);
			// $result["query"] = $query_yape_pend;
			if ($mysqli2->error) {
				$result["query"] = $query_yape_pend;
				$result["error"] = $mysqli2->error;
				echo json_encode($result);exit();
			}
			while ($li = $list_query_yape_pend->fetch_assoc()) {
				$list_yape_pendientes[] = $li;
			}
			$result["lista_yapes_pendientes"] = $list_yape_pendientes;



			
			// echo json_encode($result);exit();

		}  else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al consultar los datos del cliente.";
			echo json_encode($result);exit();
		}

		$where = "";

	}

	$array_merge = array_merge($list_yape_pendientes, $list);

	
	
	if(count($array_merge) == 0){
		$result["http_code"] = 400;
		$result["result"] = "No existe abonos.";
	} elseif (count($array_merge) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $array_merge;
	} else {
		$result["http_code"] = 400;
		$result["result"] = "El banco no existe.";
	}
}


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_data_abono_pendiente") {
	$abono_pendiente_id 	= $_POST["abono_pendiente_id"];
	$origen_abono_pendiente = $_POST["origen_abono_pendiente"];
	
	$list = array();
	if ( $origen_abono_pendiente == "TLS" ) {
		$query ="
			SELECT 
				tap.id AS cod_transaccion,
				tap.fecha_operacion,
				tap.hora_operacion,
				tapm.nombre AS nombre_medio,
				IFNULL(ca.cuenta_descripcion, '') AS nombre_banco,
				ca.id AS banco_id,
				IFNULL(tap.name_file, '') AS nombre_imagen,
				tap.created_at AS fecha_registro,
				IFNULL(tap.monto, 0) AS monto,
				IFNULL(tap.nro_operacion, 0) AS nro_operacion,
				IFNULL(tap.comision_id, 0) AS comision_id,
				IFNULL(tap.observacion, '') as observacion,
				IFNULL(tap.estado_abono_id, '') as estado_abono,
				IFNULL(u.usuario, '') AS usuario,
				'$origen_abono_pendiente' origen_abono_pendiente
			FROM
				tbl_televentas_abonos_pendientes tap
				INNER JOIN tbl_cuentas_apt ca ON ca.id = tap.banco_id
				INNER JOIN tbl_televentas_abonos_pendientes_medio tapm ON tapm.id = tap.medio_id
				INNER JOIN tbl_usuarios u ON u.id = tap.user_id
			WHERE
				tap.estado_abono_id = 1
				AND tap.id = '" . $abono_pendiente_id . "'
			ORDER BY 
				tap.fecha_operacion DESC,
				tap.created_at DESC
			LIMIT 1
			";
		
		$list_query = $mysqli->query($query);
		while ($li = $list_query->fetch_assoc()) {
			$list[] = $li;
		}
	
		if($mysqli->error){
			$result["consulta_error"] = $mysqli->error;
		}

	} else if ( $origen_abono_pendiente == "APK" ) {
		$con_db_name   = env('DB_DATABASE_YAPE');
		$con_host      = env('DB_HOST_YAPE');
		$con_user      = env('DB_USERNAME_YAPE');
		$con_pass      = env('DB_PASSWORD_YAPE');
		$mysqli2 = new mysqli($con_host,$con_user,$con_pass,$con_db_name,3306);

		$cmd_valid_yape_pending = "
			SELECT
				t.id cod_transaccion,
				DATE(t.register_date) fecha_operacion,
				-- TIME(t.register_date) hora_operacion,
				DATE_FORMAT(t.register_date, '%H:%i') hora_operacion,
				'Yape - APK' nombre_medio,
				CONCAT('APK', ' - ', t.description) nombre_banco,
				'' AS banco_id,
				'' nombre_imagen,
				t.created_at fecha_registro,
				t.amount monto,
				'' nro_operacion,
				'0.00' comision_id,
				'' observacion,
				CASE
					WHEN t.state = 'pending' THEN 1
					WHEN t.state = 'validated' THEN 2
					WHEN t.state = 'cancelled' THEN 3
					ELSE ''
				END estado_abono,
				'' usuario,
				'$origen_abono_pendiente' origen_abono_pendiente
			FROM `at-yape`.transactions t
			WHERE 
				t.id = " . $abono_pendiente_id;
		$list_yape_pending = $mysqli2->query($cmd_valid_yape_pending);
		if ($mysqli2->error) {
			$result["query"] = $cmd_valid_yape_pending;
			$result["consulta_error"] = $mysqli2->error;
			echo json_encode($result);exit();
		}
		while ($li = $list_yape_pending->fetch_assoc()) {
			$list[] = $li;
		}

	} else {
		$result["http_code"] = 400;
		$result["result"] = "Error, origen de abono pendiente no especificado.";
		echo json_encode($result);exit();
	}

	if(count($list) == 0){
		$result["http_code"] = 400;
		$result["result"] = "No existe abono.";
	} elseif (count($list) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "Datos de abono pendiente obtenidos de gestion.";
		$result["result"] = $list;
	} else {
		$result["http_code"] = 400;
		$result["result"] = "El abono pendiente no existe.";
	}
}


if (isset($_POST["accion"]) && $_POST["accion"]==="update_id_abono_pendiente") {
	$id_abono_pendiente = $_POST["id_abono_pendiente"];
	$id_transaccion     = $_POST["id_transaccion"];

	$usuario_id = $login ? $login['id'] : null;
	if (!((int) $usuario_id > 0)) {
		$result["http_code"] = 400;
		$result["status"] = "Sesión perdida. Por favor vuelva a iniciar sesión.";
		echo json_encode($result);exit();
	}

	$query ="
		UPDATE tbl_televentas_clientes_transaccion 
		SET
			id_abono_pendiente= " . $id_abono_pendiente . "
		WHERE
			id = " . $id_transaccion. "
	";
	
	/* $list_query = $mysqli->query($query);
	$list = array();
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	} */

	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	
	/* if(count($list) == 0){
		$result["http_code"] = 400;
		$result["result"] = "No existe id.";
	} elseif (count($list) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "Id de abono pendiente actualizado correctamente.";
		$result["result"] = $list;
	} else {
		$result["http_code"] = 400;
		$result["result"] = "El id de abono pendiente no existe.";
	} */

	$query_1 ="
		UPDATE tbl_televentas_abonos_pendientes 
		SET
			estado_abono_id = 2,
			update_user_id = '" . $usuario_id . "',
			updated_at = now()
		WHERE
			id = " . $id_abono_pendiente. "
	";
	
	/* $list_query_1 = $mysqli->query($query_1);
	$list_1 = array();
	while ($li = $list_query_1->fetch_assoc()) {
		$list_1[] = $li;
	} */

	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
}

//*******************************************************************************************************************
//*******************************************************************************************************************
// GUARDAR EDICION REGISTRO
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "sec_tlv_dep_guardar_cambios_registro") {
	include("function_replace_invalid_caracters.php");
	$id_transaccion      = $_POST["id_transaccion"];
	$rr_data             = json_decode($_POST["rr_data"]);

	$usuario_id = $login ? $login['id'] : 0;
	if (!((int) $usuario_id > 0)) {
		$result["http_code"] = 400;
		$result["status"] = "Sesión perdida, actualice la página por favor.";
		echo json_encode($result);exit();
	}

	$limite_dias = 0;
	$query_limit_dias = "
		SELECT valor
		FROM tbl_televentas_parametros
		WHERE nombre_codigo = 'editar_depositos_pagos' AND estado = 1
		LIMIT 1
	";
	$list_query_limit_dias = $mysqli->query($query_limit_dias);
	$list_limit_dias = array();
	if($mysqli->error){
		$result["query"] = $mysqli->error;
		$result["status"] = "Ocurrió un error al consultar el parámetro de dias para editar los depósitos y retiros aprobados.";
		echo json_encode($result);exit();
	}else{
		while ($li = $list_query_limit_dias->fetch_assoc()) {
			$list_limit_dias[] = $li;
		}
		if (count($list_limit_dias) == 0) {
			$result["http_code"] = 400;
			$result["status"] = "No hay datos registrados para el parametro editar depósitos y retiros.";
			echo json_encode($result);exit();
		} else{
			$limite_dias = $list_limit_dias[0]["valor"];
			$result["http_code"] = 200;
			$result["status"] = "Ok";
		}
	}

	if($limite_dias == 0){
		$result["http_code"] = 400;
		$result["status"] = "Está retornando 0 como valor de límite de días";
		echo json_encode($result);exit();
	}

	$query_1 = "
		SELECT 
		  tra.id, 
		  tra.cliente_id, 
		  tra.cuenta_id, 
          IFNULL(ta.id, 0) imagen_id,
          IFNULL(ta.archivo, '') imagen,
          IFNULL(tra.created_at, '') fecha_creacion
		FROM 
		  tbl_televentas_clientes_transaccion tra 
          LEFT JOIN tbl_televentas_transaccion_archivos ta ON ta.transaccion_id = tra.id AND ta.estado = 1
		WHERE 
		tra.id = $id_transaccion 
		AND tra.estado = 1
		LIMIT 1
	";
	$list_query = $mysqli->query($query_1);
	$list_transaccion = array();
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["query"] = $mysqli->error;
		$result["status"] = "Ocurrió un error al consultar el estado de la transacción.";
		echo json_encode($result);exit();
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$list_transaccion[] = $li;
		}
		if (count($list_transaccion) == 0) {
			$result["http_code"] = 400;
			$result["status"] = "Hubo un error al consultar la transacción, intentar nuevamente, por favor.";
			echo json_encode($result);exit();
		} elseif (count($list_transaccion) == 1) {
			$transaccion_id = $list_transaccion[0]["id"];
			$cliente_id = $list_transaccion[0]["cliente_id"];
			$cuenta_id = $list_transaccion[0]["cuenta_id"];
			$imagen_id = $list_transaccion[0]["imagen_id"];
			$valor_imagen = $list_transaccion[0]["imagen"];
			$fecha_creacion = $list_transaccion[0]["fecha_creacion"];

			$date1 = new DateTime("now");
			$date2 = new DateTime($fecha_creacion);
			$diff = $date1->diff($date2);
			$diff = $diff->days;

			if($diff > $limite_dias){
				$result["http_code"] = 400;
				$result["status"] = "No puede editar registros de más de ". $limite_dias ." días de antiguedad.";
				echo json_encode($result);exit();
			}

			//Imagen
			$isEmpty = empty($_FILES);
			$size    = $_FILES['imagen_voucher']['size'] ?? 0;
			$error   = $_FILES['imagen_voucher']['error'] ?? 4;

			if (!$isEmpty && $error === 0){
				//Desactivar imagen anterior
				$cmd_image_update = "
						UPDATE tbl_televentas_transaccion_archivos 
						SET 
							estado = 0
						WHERE id = " . $imagen_id . " 
							AND transaccion_id = " . $transaccion_id;
				$mysqli->query($cmd_image_update);

				//Agregar nueva imagen
				$filename = $_FILES['imagen_voucher']['tmp_name'];
				$filenametem = $_FILES['imagen_voucher']['name'];

				$path = "/var/www/html/files_bucket/depositos/";
				$file = [];
				$imageLayer = [];
				if (!is_dir($path))
					mkdir($path, 0777, true);
				$imageProcess = 0;

				$filename = $_FILES['imagen_voucher']['tmp_name'];
				$filenametem = $_FILES['imagen_voucher']['name'];
				$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
				if ($filename != "") {
					$fileExt = pathinfo($_FILES['imagen_voucher']['name'], PATHINFO_EXTENSION);
					$resizeFileName = $transaccion_id . "_" . date('YmdHis');
					$nombre_archivo = $resizeFileName . "." . $fileExt;
					if ($fileExt == "pdf") {
						move_uploaded_file($_FILES['imagen_voucher']['tmp_name'], $path . $nombre_archivo);
					} else {
						$sourceProperties = getimagesize($filename);
						$size = $_FILES['imagen_voucher']['size'];
						$uploadImageType = $sourceProperties[2];
						$sourceImageWith = $sourceProperties[0];
						$sourceImageHeight = $sourceProperties[1];
						switch ($uploadImageType) {
							case IMAGETYPE_JPEG:
								$resourceType = imagecreatefromjpeg($filename);
								break;
							case IMAGETYPE_PNG:
								$resourceType = imagecreatefrompng($filename);
								break;
							case IMAGETYPE_GIF:
								$resourceType = imagecreatefromgif($filename);
								break;
							default:
								$imageProcess = 0;
								break;
						}
						$imageLayer = sec_tlv_dep_resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);

						$file[0] = imagegif($imageLayer[0], $path . $nombre_archivo);
						$file[1] = imagegif($imageLayer[1], $path . "min_" . $nombre_archivo);
						move_uploaded_file($file[0], $path . $nombre_archivo);
						move_uploaded_file($file[1], $path . $nombre_archivo);
						$imageProcess = 1;
					}

					$comando = " INSERT INTO tbl_televentas_transaccion_archivos
									(transaccion_id,tipo,archivo,created_at,estado)
									VALUES(
										'" . $transaccion_id . "',
										1,
										'" . $nombre_archivo . "',
										'" . date('Y-m-d H:i:s') . "',
										1
										)";
					$mysqli->query($comando);
					$filepath = $path . $resizeFileName . "." . $fileExt;

					$cmd_update_change = "
							UPDATE tbl_televentas_clientes_transaccion_modificaciones 
							SET 
								status = 0
							WHERE transaccion_id = " . $transaccion_id . " AND status = 1 AND campo_name = 'archivo'" ;
					$mysqli->query($cmd_update_change);

					$cmd_insert_change = " 
						INSERT INTO 
							tbl_televentas_clientes_transaccion_modificaciones
							(transaccion_id,tabla,campo_name,valor_original,valor_nuevo,user_id,status,created_at)
						VALUES(
							" . $transaccion_id . ",
							'tbl_televentas_transaccion_archivos',
							'archivo',
							'" . $valor_imagen . "',
							'" . $nombre_archivo . "',
							" . $usuario_id . ",
							1,
							now()
							)";
					$mysqli->query($cmd_insert_change);

				}
			}

			foreach ($rr_data as $d) {
				$updated = $d->updated;
				if($updated == false){
					$campo = $d->campo;
					$valor_anterior = $d->valor_anterior;
					$valor_nuevo = $d->valor_nuevo;

					$cmd_change_transaction = "
							UPDATE tbl_televentas_clientes_transaccion 
							SET 
								" . $campo . " = '" . $valor_nuevo. "'
							WHERE id = " . $transaccion_id . " OR transaccion_id = " .  $transaccion_id;
					$mysqli->query($cmd_change_transaction);

					$cmd_update_change = "
							UPDATE tbl_televentas_clientes_transaccion_modificaciones 
							SET 
								status = 0
							WHERE transaccion_id = " . $transaccion_id . " AND status = 1 AND campo_name = '" . $campo . "'" ;
					$mysqli->query($cmd_update_change);

					$cmd_insert_change = " 
						INSERT INTO 
							tbl_televentas_clientes_transaccion_modificaciones
							(transaccion_id,tabla,campo_name,valor_original,valor_nuevo,user_id,status,created_at)
						VALUES(
							" . $transaccion_id . ",
							'tbl_televentas_clientes_transaccion',
							'" . $campo . "',
							'" . $valor_anterior . "',
							'" . $valor_nuevo . "',
							" . $usuario_id . ",
							1,
							now()
							)";
					$mysqli->query($cmd_insert_change);
				}
			}

			$result["http_code"] = 200;
			$result["status"] = "Ok";
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al consultar el estado de la transacción.";
		}
	}
}

function sec_tlv_dep_resizeImage($resourceType, $image_width, $image_height) {
	$imagelayer = [];
	if ($image_width < 1920 && $image_height < 1080) {
		//mini
		$resizewidth_mini = 100;
		$resizeheight_mini = 100;
		$imagelayer[0] = imagecreatetruecolor($image_width, $image_height);
		imagecopyresampled($imagelayer[0], $resourceType, 0, 0, 0, 0, $image_width, $image_height, $image_width, $image_height);
		//mini
		$imagelayer[1] = imagecreatetruecolor($resizewidth_mini, $resizeheight_mini);
		imagecopyresampled($imagelayer[1], $resourceType, 0, 0, 0, 0, $resizewidth_mini, $resizeheight_mini, $image_width, $image_height);
	} else {
		$ratio = $image_width / $image_height;
		$escalaW = 1920 / $image_width;
		$escalaH = 1080 / $image_height;
		if ($ratio > 1) {
			$resizewidth = $image_width * $escalaW;
			$resizeheight = $image_height * $escalaW;
		} else {
			$resizeheight = $image_height * $escalaH;
			$resizewidth = $image_width * $escalaH;
		}
		//mini
		$resizewidth_mini = 100;
		$resizeheight_mini = 100;
		$imagelayer[0] = imagecreatetruecolor($resizewidth, $resizeheight);
		imagecopyresampled($imagelayer[0], $resourceType, 0, 0, 0, 0, $resizewidth, $resizeheight, $image_width, $image_height);
		//mini
		$imagelayer[1] = imagecreatetruecolor($resizewidth_mini, $resizeheight_mini);
		imagecopyresampled($imagelayer[1], $resourceType, 0, 0, 0, 0, $resizewidth_mini, $resizeheight_mini, $image_width, $image_height);
	}
	return $imagelayer;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_tlv_dep_obtener_fecha_hora") {
	$query = "SELECT now() fecha_hora ";
	$list_query = $mysqli->query($query);
	$list_res = array();
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$list_res[] = $li;
		}
	}

	if (count($list_res) == 1) {
		$result["http_code"] = 200;
		$result["status"] = "No hay transacciones nuevas.";
		$result["result"] = $list_res[0]["fecha_hora"];
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_tlv_dep_obtener_tipos_constancia") {
	$tipo_cuenta = $_POST["tipo_cuenta"];
	$query = "	
	SELECT
		tc.id,
		tc.descripcion
	FROM
		tbl_televentas_tipo_constancia AS tc
	WHERE tc.estado = 1  
		AND tc.tipo_cuenta = " . $tipo_cuenta." or tc.id =5"; 

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


if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tlv_tercero_aut") {
	 
	$id_cliente = $_POST["id_cliente"];

	$query_1 = "SELECT 
		id,
		id_cliente,
		dni_titular,
		nombre_apellido_titular ,
		estado
	FROM tbl_televentas_titular_abono
	WHERE id_cliente ='".$id_cliente."'
	AND estado in (0,1)
	ORDER BY id ASC";

	$list_query_1 = $mysqli->query($query_1);
	$list_1 = array();
	if ($mysqli->error) {
		$result["query_1_error"] = $mysqli->error;
	} else {
		while ($li_1 = $list_query_1->fetch_assoc()) {
			$list_1[] = $li_1;
		}
	}

	if (count($list_1) == 0) {

		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = "Sin registros de terceros autorizados";

		
	} elseif (count($list_1) > 0) {	
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_1;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los registros";
	}

}

if (isset($_GET["accion"]) && $_GET["accion"]==="sec_tlv_dep_listar_validadores") {
	$query ="
		SELECT 
		  u.id user_id, 
		  u.usuario, 
		  CONCAT(
			IF( LENGTH( pl.nombre ) > 0, CONCAT( UPPER( TRIM(pl.nombre) ), ', '), '' ),
			IF( LENGTH( pl.apellido_paterno ) > 0, CONCAT( UPPER( pl.apellido_paterno ), ' ' ), '' ),
			IF( LENGTH( pl.apellido_materno ) > 0, UPPER( pl.apellido_materno ), '' )
			) nombre_cajero
		FROM tbl_usuarios u 
		JOIN tbl_personal_apt pl ON pl.id = u.personal_id
		WHERE u.estado = 1
		HAVING nombre_cajero LIKE '%" . strtoupper(trim($_GET["term"])) . "%'
			OR u.usuario LIKE '%" . strtoupper(trim($_GET["term"])) . "%'
		ORDER BY pl.nombre ASC
		";

	$list_query=$mysqli->query($query);
	$list_registros=array();
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li=$list_query->fetch_assoc()) {
			$list_registros[]=$li;
			$temp_array['codigo'] = $li['user_id'];
            $temp_array['value'] = strtoupper('[' . $li['usuario'] . '] ' . $li['nombre_cajero']);
            $temp_array['label'] = $temp_array['value'];
            array_push($result, $temp_array);
		}
	}

	if(count($list_registros)===0){
		//$result["http_code"] = 204;
		//$result["result"] = $list_registros;
		//$result['value'] = '';
        $result['label'] = 'No se encontraron coincidencias.';
	} elseif(count($list_registros)>0){
		
	} else {
		//$result["http_code"] = 400;
		//$result['value'] = '';
        $result['label'] = 'No se encontraron coincidencias.';
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_tlv_obtener_totales_transacciones") {
	$busqueda_fecha_inicio = $_POST["fecha_inicio"];
	$busqueda_fecha_fin    = $_POST["fecha_fin"];
	$busqueda_estado       = $_POST["estado"];
	$busqueda_validador    = $_POST["validador"];
	$busqueda_autovalidacion = $_POST["autovalidacion"];
	$busqueda_tipo_constancia = $_POST["tipo_constancia"];
	$busqueda_cuenta       = "";
	if (isset($_POST["cuenta"]) && $_POST["cuenta"] !== '') {
		$busqueda_cuenta = implode(",",$_POST["cuenta"]) ;
	}


	$usuario_id = $login["id"];
	$where_tipo_tra = 'tra.tipo_id =1';

	$area_id = $login ? $login['area_id'] : 0;

	$where_users_test="";
	if((int)$area_id !== 6) {// diferente de sistemas
		$where_users_test="	
			AND IFNULL(tra.web_id, '') not in ('3333200', '71938219') 
			AND IFNULL(tra.user_id, '') not in (1, 249, 250, 2572, 3028)
		";
	}
	$where_estado = '';
	if ($busqueda_estado !== '') {
		if($busqueda_estado == '1'){ // Aprobado
			/*$where_tipo_tra = '(
									(tra.tipo_id = 1 and tra.estado = 1)
								) ';*/
			/*$where_tipo_tra = '(
									(tra.tipo_id = 1 and tra.estado = 1 AND IFNULL(tra_2.estado, 0) != 3 AND IFNULL(tra.caja_vip,0) IN (0,2)) 
									OR (tra.tipo_id = 1 and tra.estado = 1 AND IFNULL(tra_2.estado, 0) = 0 AND IFNULL(tra.caja_vip,0) = 1) 
									OR (tra.tipo_id = 26 and tra.estado = 1 and IFNULL(tra.caja_vip,0) = 1)
								) ';*/
			$where_estado = ' AND tra.estado = 1 AND IFNULL(tra_2.estado, 0) != 3 '; //' AND tra.estado = 1 AND IFNULL(tra_2.estado,0) != 3';
		} else if($busqueda_estado == '2'){ //Rechazado
			/*$where_tipo_tra = '
								(tra.tipo_id = 1 AND tra.estado = 2 AND IFNULL(tra_2.estado, 0) = 0 ) -- AND IFNULL(tra.caja_vip, 0) != 1)
								';*/
			$where_estado = ' AND tra.estado = 2  ';
		} else if ($busqueda_estado == '3'){ //Eliminado
			/*$where_tipo_tra = ' 
								(
									(tra.tipo_id = 1 AND tra.estado = 3 AND IFNULL(tra_2.estado, 0) = 3 AND IFNULL(tra.caja_vip, 0) != 1)
									OR (tra.tipo_id = 26 AND tra.estado = 3 AND tra.caja_vip = 1)
								)
								';*/
			$where_estado = ' AND tra.estado = 1 AND IFNULL(tra_2.estado, 0) = 3 '; 
		}else{
			$where_estado = ' AND tra.estado = ' . $busqueda_estado . ' ';
		}
	} else { // Todos
		$where_estado = '';
		/*$where_tipo_tra = ' (
								(tra.tipo_id = 1 and tra.estado IN (0,1,2,3) ) 
								-- (tra.tipo_id = 1 and tra.estado IN (0,1,2,3) AND IFNULL(tra_2.estado, 0) = 0 AND IFNULL(tra.caja_vip,0) IN (0,2)) 
								-- OR (tra.tipo_id = 1 and IFNULL(tra_2.estado, 0) = 0 AND IFNULL(tra.caja_vip,0) = 1) 
								-- OR (tra.tipo_id = 1 and tra_2.estado IN(1,3) and IFNULL(tra.caja_vip,0) IN (0,2))
								-- OR (tra.tipo_id = 26 and tra.estado IN(1,3) and IFNULL(tra.caja_vip,0) = 1)
							) ';*/
	}

	$where_validador = '';
	if ((int) $busqueda_validador > 0) {
		$where_validador = ' AND tra.update_user_id=' . $busqueda_validador . ' ';
	}

	$where_fecha_inicio = " AND tra.created_at >= '" . $busqueda_fecha_inicio . " 00:00:00' ";
	$where_fecha_fin = " AND tra.created_at <= '" . $busqueda_fecha_fin . " 23:59:59' ";

	$where_cuenta = "";
	if ($busqueda_cuenta !== '') {
		$where_cuenta = " AND cuen.id IN ($busqueda_cuenta) ";
	} else {
		$where_cuenta = "
			AND cuen.id IN (
							SELECT
								uca.cuenta_apt_id
							FROM
								tbl_usuario_cuentas_apt AS uca
							WHERE uca.usuario_id = $usuario_id AND uca.estado = 1
							GROUP BY uca.cuenta_apt_id
			) 
		";
	}

	$where_autovalidacion = "";
	if($busqueda_autovalidacion == 1){
		$where_autovalidacion = " AND IFNULL(tra.caja_vip, 0) != 2 ";
	}else if($busqueda_autovalidacion == 2){
		$where_autovalidacion = " AND IFNULL(tra.caja_vip, 0) = 2 ";
	}

	$where_tipo_constancia = "";
	 if($busqueda_tipo_constancia <> 0){
		$where_tipo_constancia = " AND IFNULL(tra.id_tipo_constancia, 0) =$busqueda_tipo_constancia ";
	}
	

	$query_1 = "
		SELECT
			IFNULL(SUM(IF(DATE(tra.registro_deposito) < DATE(tra.created_at) and tc.valid_cuenta_yape = 1, 1, 0)),0) num_ant,
			IFNULL(SUM(IF(DATE(tra.registro_deposito) < DATE(tra.created_at) and tc.valid_cuenta_yape = 1, tra.monto, 0)),0) total_ant,
			IFNULL(SUM(IF(IFNULL(tra.txn_id, 0) = 0, 1, 0)), 0) AS cant,
			IFNULL(SUM(IF(IFNULL(tra.txn_id, 0) = 0, tra.monto, 0)), 0) AS monto_deposito,
			SUM(IFNULL(tra.comision_monto, 0)) comision_monto,
			SUM(IFNULL(tra.monto, 0)) monto,
			IFNULL(SUM(IF(IFNULL(tra.txn_id, 0) > 0, 1, 0)), 0) AS cant_autovalidaciones,
			IFNULL(SUM(IF(IFNULL(tra.txn_id, 0) > 0, tra.monto, 0)), 0) AS monto_total_autovalidaciones
		FROM
			tbl_televentas_clientes_transaccion tra
			LEFT JOIN tbl_televentas_recargas_bono bon ON bon.id = tra.bono_id 
			LEFT JOIN tbl_usuarios usu ON usu.id = tra.user_id
			LEFT JOIN tbl_televentas_clientes cli ON cli.id = tra.cliente_id
			LEFT JOIN tbl_usuarios val ON val.id = tra.update_user_id
			LEFT JOIN tbl_personal_apt apt ON apt.id = val.personal_id
			LEFT JOIN tbl_televentas_cuentas tc ON tc.cuenta_apt_id = tra.cuenta_id
			LEFT JOIN tbl_cuentas_apt cuen ON cuen.id = tc.cuenta_apt_id
			LEFT JOIN tbl_locales loc ON loc.cc_id = tra.cc_id 
			LEFT JOIN tbl_televentas_tipo_contacto ttc ON ttc.id = tra.id_tipo_contacto
			LEFT JOIN tbl_televentas_recargas_bono rec_bon ON tra.bono_id = rec_bon.id
			LEFT JOIN tbl_televentas_titular_abono tit_abon ON tra.titular_abono = tit_abon.id
			LEFT JOIN tbl_televentas_tipo_constancia tip_c ON tra.id_tipo_constancia = tip_c.id
			LEFT JOIN tbl_televentas_clientes_transaccion tra_2 ON tra.id = tra_2.transaccion_id  AND tra_2.tipo_id = 26
		WHERE
		$where_tipo_tra
		AND tra.cliente_id > 0 
		$where_users_test
		$where_fecha_inicio 
		$where_fecha_fin 
		$where_estado 
		$where_validador 
		$where_cuenta 
		$where_autovalidacion 
		$where_tipo_constancia
		ORDER BY tra.id ASC ";
	$result["consulta_query_cant_totales"] = $query_1;
	$list_query = $mysqli->query($query_1);
	$list_transaccion = array();
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$list_transaccion[] = $li;
		}
	}

	$totales = array();
	$totales = get_cant_abonos_pending($busqueda_fecha_inicio, $busqueda_fecha_fin);
	$result["totales"] = $totales;

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

function query_tbl_televentas_clientes_transaccion($tipo_transaccion, $cliente_id, $monto, $nuevo_balance, $id_tipo_balance, $evento_dineroat_id, $usuario_id){
	global $mysqli;

	$query = " 
		INSERT INTO tbl_televentas_clientes_transaccion (
			tipo_id,
			cliente_id,
			monto,
			nuevo_balance,
			estado,
			created_at,
			update_user_at,
			id_tipo_balance,
			evento_dineroat_id,
			user_id
		) VALUES (
			" . $tipo_transaccion . ",
			" . $cliente_id . ",
			" . $monto . ",
			" . $nuevo_balance . ",
			1,
			now(),
			now(),
			'". $id_tipo_balance ."',
			'". $evento_dineroat_id ."',
			'". $usuario_id ."'
		)";
	$mysqli->query($query);

	$select = "
		SELECT id
		FROM tbl_televentas_clientes_transaccion
		WHERE tipo_id = $tipo_transaccion
			AND cliente_id = $cliente_id
			AND monto = $monto
			AND nuevo_balance = $nuevo_balance
			AND estado = 1
			AND id_tipo_balance = $id_tipo_balance
			AND evento_dineroat_id = $evento_dineroat_id
			AND user_id = $usuario_id
		LIMIT 1
	";
	$list_select = $mysqli->query($select);
	$res_ct_id = array();
	if ($mysqli->error) {
		$res_ct_id["http_code"] = 400;
		$res_ct_id["select_clien_trans"] = $select;
		$res_ct_id["select_clien_trans_error"] = $mysqli->error;
		$res_ct_id["status"] = "Error al insertar en la tabla clientes_transaccion";
		echo json_encode($res_ct_id);exit();
	}
	$list_id = array();
	while ($li = $list_select->fetch_assoc()) {
		$list_id[] = $li;
		$id = $li["id"];
	}
	if (count($list_id) === 0){
		$res_ct_id["http_code"] = 400;
		$res_ct_id["status"] = "error al consutar el id en clientes_transaccion.";
	}else{
		$res_ct_id["http_code"] = 200;
		$res_ct_id["id"] = $id;
	}
	return $res_ct_id;

}

function sec_tlv_dep_tbl_televentas_clientes_balance($action, $cliente_id, $tipo_id, $balance){
	global $mysqli;

	if($action==='insert') {
		$query = " 
			INSERT INTO tbl_televentas_clientes_balance (
				cliente_id,
				tipo_balance_id,
				balance,
				created_at,
				updated_at
			) VALUES (
				" . $cliente_id . ",
				" . $tipo_id . ",
				" . $balance . ",
				now(),
				now()
			)";
		$mysqli->query($query);
	}
	if($action==='update') {
		$query = " 
			UPDATE tbl_televentas_clientes_balance 
			SET
				balance = '" . $balance . "',
				updated_at = now()
			WHERE cliente_id = " . $cliente_id . " AND tipo_balance_id = " . $tipo_id . " 
		";
		$mysqli->query($query);
	}
}

function sec_tlv_dep_tbl_televentas_clientes_balance_transaccion($action, $transaccion_id, $cliente_id, $tipo_balance_id, $balance_actual, $monto, $balance_nuevo){
	global $mysqli;
	global $login;

	$user_id = $login ? $login['id'] : 0;

	if($action==='insert') {
		$query = "
			INSERT INTO tbl_televentas_clientes_balance_transaccion (
				transaccion_id,
				cliente_id,
				tipo_balance_id,
				balance_actual,
				monto,
				balance_nuevo,
				user_id,
				created_at
			) VALUES (
				'" . $transaccion_id . "',
				'" . $cliente_id . "',
				'" . $tipo_balance_id . "',
				'" . $balance_actual . "',
				'" . $monto . "',
				'" . $balance_nuevo . "',
				'" . $user_id . "',
				now()
			)
		";
		$mysqli->query($query);
	}
}

//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER DATA DE TRANSACCIÓN POR ABONO ID
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_data_transaccion_x_abono_id") {
	$abono_id = (int)$_POST["abono_id"];
	$origen = $_POST["origen"];
	$usuario_id = $login["id"];

	$where = "";
	if ( $origen == "APK" ) {
		$where = " AND tra.txn_id = '" . $abono_id . "'";

		$con_db_name   = env('DB_DATABASE_YAPE');
		$con_host      = env('DB_HOST_YAPE');
		$con_user      = env('DB_USERNAME_YAPE');
		$con_pass      = env('DB_PASSWORD_YAPE');
		$mysqli2 = new mysqli($con_host,$con_user,$con_pass,$con_db_name,3306);
		if ($mysqli2->connect_error){
			$result["http_code"] = 500;
			$result["status"] = "Conexión fallida: %s\n" . $mysqli2->connect_error;
			throw new Exception("Conexión fallida: %s\n" . $mysqli2->connect_error);
			echo json_encode($result);exit();
		}

		$query_validate_pending = " 
			SELECT
				DATE(t.register_date) fecha_operacion,
				TIME(t.register_date) hora_operacion,
				'-' nro_operacion,
				'Yape - APK' nombre_medio,
				t.amount monto
			FROM `at-yape`.transactions t
			WHERE id = $abono_id
			LIMIT 1
		";

		$list_pending = $mysqli2->query($query_validate_pending);
		$array_pending = array();
		if ($mysqli2->error) {
			$result["query_validate_pending"] = $query_validate_pending;
			$result["query_error"] = $mysqli2->error;
			echo json_encode($result);exit();
		}
		while ($li = $list_pending->fetch_assoc()) {
			$array_pending[] = $li;
		}

		if ( count($array_pending) === 0 ) {
			$result["http_code"] = 400;
			$result["status"] = "No se encuentra el id del yape-pendiente.";
			echo json_encode($result);exit();
		} else {
			$result["data_yape"] = $array_pending;
		}

	} else {
		$where = " AND tra.id_abono_pendiente = '" . $abono_id . "'";
	}

	$query = "	
		SELECT
			tra.id,
			tra.cliente_id,
			tra.created_at fecha_hora_registro,
			CASE WHEN IFNULL(tra.txn_id, '') != '' THEN 'SI' ELSE 'NO' END autovalidacion,
			usu.usuario AS cajero,
			IFNULL(CONCAT( cli.nombre, ' ', IFNULL( cli.apellido_paterno, '' ), ' ', IFNULL( cli.apellido_materno, '' ) ), '') AS cliente,
			(CASE
				WHEN tra.estado = 0 THEN
				'Pendiente' 
				WHEN tra.estado = 1 AND IFNULL(tra_2.estado, 0) != 3 THEN
				'Aprobado' 
				WHEN tra.estado = 2 THEN
				'Rechazado' 
				WHEN tra.estado = 3 or tra_2.estado = 3 THEN
				'Eliminado'
			END) AS estado,

			tra.estado AS estado_id,
			IFNULL(tra_2.estado, 0) AS estado_id_aprov,
			IFNULL(tc.id, 0) AS cuenta_id,
			IFNULL(tc.valid_num_ope_existe, 0) valid_num_ope_existe,
			IFNULL(tc.valid_num_ope_unico, 0) valid_num_ope_unico,
			IFNULL(tc.valid_cuenta_yape, 0) valid_cuenta_yape,
			IFNULL(cuen.cuenta_descripcion, '') AS cuenta,
			IFNULL(tra.bono_id, 0) bono_id,
			IFNULL(tra.registro_deposito, '') AS registro_deposito,
			IFNULL(tra.num_operacion, '') AS num_operacion,
			IFNULL(tra.monto_deposito, 0) AS monto_deposito,
			IFNULL(tra.monto, 0) AS monto,
			IFNULL(tra.comision_monto, 0) AS comision_monto,
			IFNULL(tra.bono_monto, 0) AS bono_monto,
			IFNULL(bon.nombre, 'Ninguno' ) AS bono_nombre,
			IFNULL(tra.total_recarga, 0) AS total_recarga,
			val.usuario AS validador_usuario,
			IFNULL(CONCAT( apt.nombre, ' ', IFNULL( apt.apellido_paterno, '' ), ' ', IFNULL( apt.apellido_materno, '' ) ), '') AS validador_nombre,
			IFNULL(tra.update_user_at, '') fecha_hora_validacion,
			IFNULL(REPLACE(REPLACE(tra.observacion_cajero, '\n', ''), '\n', ''), '') observacion_cajero,
			IFNULL(REPLACE(REPLACE(tra.observacion_validador, '\n', ''), '\n', ''), '') observacion_validador,
			IFNULL(tra.tipo_rechazo_id, 0) tipo_rechazo_id,
			IFNULL(ttc.contact_type, '') AS tipo_contacto,
			UPPER(IFNULL(loc.nombre, '')) AS turno_local,
			IFNULL(tra.bono_mensual_actual, 0) AS bono_mensual_actual,
			IFNULL(rec_bon.nombre, 'Ninguno') bono_nombre,
			UPPER(IFNULL(tit_abon.nombre_apellido_titular, IFNULL(CONCAT( cli.nombre, ' ', IFNULL( cli.apellido_paterno, '' ), ' ', IFNULL( cli.apellido_materno, '' ) ), ''))) titular_abono,
			IFNULL(tra.titular_abono, 0) AS id_titular_abono, 
			IFNULL(tra.id_tipo_constancia, 0) id_tipo_constancia,
			IFNULL(tip_c.descripcion, '') tipo_constancia,
			IFNULL(tj.nombre, '') tipo_jugada,
            IFNULL(tra.id_abono_pendiente, 0) id_abono_pendiente,
            IFNULL(ap.fecha_operacion, '') ap_fecha_operacion,
            IFNULL(ap.hora_operacion, '') ap_hora_operacion,
            IFNULL(ap.nro_operacion, '') ap_nro_operacion,
            IFNULL(tapm.nombre, '') ap_nombre_medio,
            IFNULL(ap.monto, '') ap_monto
		FROM
			tbl_televentas_clientes_transaccion tra
			LEFT JOIN tbl_televentas_recargas_bono bon ON bon.id = tra.bono_id 
			LEFT JOIN tbl_usuarios usu ON usu.id = tra.user_id
			LEFT JOIN tbl_televentas_clientes cli ON cli.id = tra.cliente_id
			LEFT JOIN tbl_usuarios val ON val.id = tra.update_user_id
			LEFT JOIN tbl_personal_apt apt ON apt.id = val.personal_id
			LEFT JOIN tbl_televentas_cuentas tc ON tc.cuenta_apt_id = tra.cuenta_id
			LEFT JOIN tbl_cuentas_apt cuen ON cuen.id = tc.cuenta_apt_id
			LEFT JOIN tbl_locales loc ON loc.cc_id = tra.cc_id 
			LEFT JOIN tbl_televentas_tipo_contacto ttc ON ttc.id = tra.id_tipo_contacto
			LEFT JOIN tbl_televentas_recargas_bono rec_bon ON tra.bono_id = rec_bon.id
			LEFT JOIN tbl_televentas_titular_abono tit_abon ON tra.titular_abono = tit_abon.id
			LEFT JOIN tbl_televentas_tipo_constancia tip_c ON tra.id_tipo_constancia = tip_c.id
			LEFT JOIN tbl_televentas_clientes_transaccion tra_2 ON tra.id = tra_2.transaccion_id AND tra_2.tipo_id = 26
			LEFT JOIN tbl_televentas_tipo_juego tj ON tra.id_juego_balance = tj.id
			LEFT JOIN tbl_televentas_abonos_pendientes ap ON tra.id_abono_pendiente = ap.id
            LEFT JOIN tbl_televentas_abonos_pendientes_medio tapm ON tapm.id = ap.medio_id
		WHERE 
			tra.tipo_id =1
			$where
			AND tra.estado = 1 AND IFNULL(tra_2.estado, 0) != 3
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

function get_cant_abonos_pending($fecha_inicio, $fecha_fin){
	global $mysqli;
	//YAPES PENDIENTES
	$con_db_name   = env('DB_DATABASE_YAPE');
	$con_host      = env('DB_HOST_YAPE');
	$con_user      = env('DB_USERNAME_YAPE');
	$con_pass      = env('DB_PASSWORD_YAPE');
	$mysqli2 = new mysqli($con_host,$con_user,$con_pass,$con_db_name,3306);
	if ($mysqli2->connect_error){
		$result["http_code"] = 500;
		$result["status"] = "Conexión fallida: %s\n" . $mysqli2->connect_error;
		throw new Exception("Conexión fallida: %s\n" . $mysqli2->connect_error);
		echo json_encode($result);exit();
	}
	$cmd_yapes_pendientes = "
		SELECT 
			COUNT(*) cant,
			IFNULL(SUM(t.amount),0) total
		FROM transactions t
		WHERE t.state = 'pending'
		AND DATE(t.register_date) >= '" . $fecha_inicio . "'
		AND DATE(t.register_date) <= '" . $fecha_fin . "'
	";
	$result["cmd_yapes_pendientes"] = $cmd_yapes_pendientes;
	$list_yape_pending = $mysqli2->query($cmd_yapes_pendientes);
	if ($mysqli2->error) {
		$result["consulta_error"] = $mysqli2->error;
		echo json_encode($result);exit();
	}
	$list_yapes_pending = array();
	while ($li = $list_yape_pending->fetch_assoc()) {
		$list_yapes_pending[] = $li;
		$result["list_yapes_pending"] = $list_yapes_pending;
	}

	//ABONOS PENDIENTES
	$cmd_total_abonos_pendientes = "
		SELECT 
			COUNT(*) cant,
		    IFNULL(SUM(ap.monto),0) total
		FROM tbl_televentas_abonos_pendientes ap
		WHERE 
			ap.status = 1
			AND estado_abono_id = 1
		    AND DATE(ap.created_at) >= '" . $fecha_inicio . "'
		    AND DATE(ap.created_at) <= '" . $fecha_fin . "'
	";
	$result["cmd_total_abonos_pendientes"] = $cmd_total_abonos_pendientes;
	$list_abono_pending = $mysqli->query($cmd_total_abonos_pendientes);
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
		echo json_encode($result);exit();
	}
	$list_abonos_pending = array();
	while ($li = $list_abono_pending->fetch_assoc()) {
		$list_abonos_pending[] = $li;
		$result["list_abonos_pending"] = $list_abonos_pending;
	}

	$array_merge = array_merge($list_yapes_pending, $list_abonos_pending);
	$result["array_merge"] = $array_merge;
	return $array_merge;
}

echo json_encode($result);

