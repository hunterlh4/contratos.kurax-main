<?php
$result = array();
include("db_connect.php");
include("sys_login.php");
date_default_timezone_set("America/Lima");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function get_turno_tlv_pag()
{
	global $login;
	global $mysqli;
	$usuario_id = $login['id'];
	$command = "
		SELECT
			sqc.id,
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


//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER CUENTAS
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_listado_bancos") {

	$usuario_id = $login["id"];

	$query = "	
		SELECT
			b.id codigo,
			b.nombre
		FROM
			tbl_bancos b
		WHERE estado = 1
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
// OBTENER TRANSACCIONES DE RETIROS
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_transacciones_x_estado") {
	$busqueda_fecha_inicio = $_POST["fecha_inicio"];
	$busqueda_fecha_fin    = $_POST["fecha_fin"];
	$busqueda_estado       = $_POST["estado"];
	$busqueda_validador    = $_POST["validador"];
	$busqueda_razon	       = $_POST["razon"];
	$busqueda_tipo	       = $_POST["tipo"];
	$busqueda_validador_supervisor = $_POST["validador_supervisor"];
	$tipo_saldo            = $_POST["tipo_saldo"];
	$busqueda_cuenta       = "";
	if (isset($_POST["cuenta"]) && $_POST["cuenta"] !== '') {
		$busqueda_cuenta = implode(",",$_POST["cuenta"]) ;
	}
	$busqueda_cuenta_pago  = "";
	if (isset($_POST["cuenta_pago"]) && $_POST["cuenta_pago"] !== '') {
		$busqueda_cuenta_pago = implode(",",$_POST["cuenta_pago"]) ;
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

	$where_tipo = ' 
		(
			(tra.tipo_id IN (9,21,28))
			-- (tra.tipo_id IN (9,21,28) and IFNULL(tra.caja_vip, 0) = 0)
            -- OR (tra.tipo_id IN (11,24,29) and IFNULL(tra.caja_vip, 0) = 1)
         )
	';
	if((int) $busqueda_tipo > 0){
		if($busqueda_tipo == 1){ // Retiros
			//$where_tipo = ' tra.tipo_id = 9 AND IFNULL(tra.tipo_operacion, 0) in (0, 1) ';
			$where_tipo = '
				(
					(tra.tipo_id = 9)
					-- (tra.tipo_id = 9 and IFNULL(tra.caja_vip, 0) = 0)
		            -- OR (tra.tipo_id = 11 and IFNULL(tra.caja_vip, 0) = 1)
		         )
			';
		}else if($busqueda_tipo == 2){ // Devoluciones
			//$where_tipo = ' tra.tipo_id = 28 ';
			$where_tipo = '
				(
					(tra.tipo_id = 28)
					-- (tra.tipo_id = 28 and IFNULL(tra.caja_vip, 0) = 0)
		            -- OR (tra.tipo_id = 29 and IFNULL(tra.caja_vip, 0) = 1)
		         )
			';
		}else if($busqueda_tipo == 3){ // Propinas
			//$where_tipo = ' tra.tipo_id = 21 ';
			$where_tipo = '
				(
					(tra.tipo_id = 21 and IFNULL(tra.caja_vip, 0) = 0)
		            -- OR (tra.tipo_id = 24 and IFNULL(tra.caja_vip, 0) = 1)
		         )
			';
		}
	}

	$where_estado = '';
	if ($busqueda_estado !== '0') {
		$where_estado = ' AND tra.estado = ' . $busqueda_estado . ' ';
	} else {
		$where_estado = ' ';
		//$where_estado = ' AND tra.estado in (1,2,3) ';
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
		/*$where_cuenta = "
			AND cuen.id IN (
							SELECT
								uca.cuenta_apt_id
							FROM
								tbl_usuario_cuentas_apt AS uca
							WHERE uca.usuario_id = $usuario_id AND uca.estado = 1
							GROUP BY uca.cuenta_apt_id
			) 
		";*/

		/*$where_cuenta = "
			AND cuen.id IN (
							SELECT
								uca.cuenta_apt_id
							FROM
								tbl_usuario_cuentas_apt AS uca
							WHERE uca.usuario_id = $usuario_id AND uca.estado = 1
							GROUP BY uca.cuenta_apt_id
			) 
		";*/
	}
	$where_cuenta_pago = "";
	if ($busqueda_cuenta_pago !== '') {
		if($busqueda_estado == 2){
			$where_cuenta_pago = " AND (tra.cuenta_pago_id IN ($busqueda_cuenta_pago) ) ";
		}
	}



	$where_razon = '';
	if ((int) $busqueda_razon > 0) {
		if($busqueda_razon == 1){
			$where_razon = ' AND IFNULL(tra.id_operacion_retiro, 0) in (0, 1) ';
		}else{
			$where_razon = ' AND IFNULL(tra.id_operacion_retiro, 0) = ' . $busqueda_razon . ' ';
		}
	}

	

	$where_validador_supervisor = '';
	if($busqueda_validador_supervisor > 0){
		$where_validador_supervisor = ' AND IFNULL(tra.user_valid_id, 0) = ' . $busqueda_validador_supervisor;
	}

	$where_tipo_saldo = '';
	if($tipo_saldo != '0'){
		if($tipo_saldo == '1'){
			$where_tipo_saldo = ' AND IFNULL(ce.id, 0) = 0';
		}else if($tipo_saldo == '2'){
			$where_tipo_saldo = ' AND IFNULL(ce.id, 0) > 0';
		}
	}

	$nombre_busqueda = "";
	if(isset($_POST["search"]["value"])) {
		$busqueda_nombre = $_POST["search"]["value"];
		if ($busqueda_nombre !="") {
			$nombre_busqueda = '
			AND (
			IFNULL(CONCAT( apt.nombre, " ",IFNULL( apt.apellido_paterno, "" ), " ",IFNULL( apt.apellido_materno, "" ) ), "") LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.created_at LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.updated_at LIKE "%'.$_POST["search"]["value"].'%"
			OR usu.usuario LIKE "%'.$_POST["search"]["value"].'%"
			OR loc.nombre LIKE "%'.$_POST["search"]["value"].'%"		
			OR cuen.nombre LIKE "%'.$_POST["search"]["value"].'%"
			OR IF(tra.tipo_id IN (9,28), IFNULL(tc.cuenta_num, ""), IFNULL(tcc.cuenta_num, "")) LIKE "%'.$_POST["search"]["value"].'%"
			OR IF(tra.tipo_id IN (9,28), IFNULL(tc.cci, ""), IFNULL(tcc.cci, "")) LIKE "%'.$_POST["search"]["value"].'%"
			OR cpr.nombre LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.monto LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.num_operacion LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.comision_monto LIKE "%'.$_POST["search"]["value"].'%"
			OR IFNULL(CONCAT( apt_aprov.nombre, " ",IFNULL( apt_aprov.apellido_paterno, "" ), " ",IFNULL( apt_aprov.apellido_materno, "" ) ), "") LIKE "%'.$_POST["search"]["value"].'%"';
			$nombre_busqueda .= ')';
		}
	}

	$order = "";
	$column = array(
		1=>"tra.created_at",
		2=>"tra.updated_at",
		3=>"usu.usuario",
		4=>"loc.nombre",
		5=>"cuen.nombre",
		6=>"cpr.nombre",
		7=>"tra.monto",
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
			CONCAT(year(now()), LPAD(tra.tipo_id, 2, 0), LPAD(tra.id, 10, 0)) cod_transaction,
			if(tra.estado IN (6), IFNULL(tlvc.color, ''), '') as color_celda,	
			tra.cliente_id,
			tra.created_at fecha_hora_registro,
			usu.usuario AS cajero,
			IFNULL(CONCAT( cli.nombre, ' ', IFNULL( cli.apellido_paterno, '' ), ' ', IFNULL( cli.apellido_materno, '' ) ), '') AS cliente,
			stra.descripcion AS estado,
			tra.estado AS estado_id,
			IF(tra.tipo_id IN (9,11,28,29), IFNULL(tc.banco_id, ''), IFNULL(tcc.banco_id, '')) cuenta_id,      -- IFNULL(tc.banco_id, '') AS cuenta_id,
			IFNULL(cuen.nombre, '') AS cuenta,
			IF(tra.tipo_id IN (9,11,28,29), IFNULL(tc.cuenta_num, ''), IFNULL(tcc.cuenta_num, '')) cuenta_num, -- IFNULL(tc.cuenta_num, '') AS cuenta_num,
			IF(tra.tipo_id IN (9,11,28,29), IFNULL(tc.cci, ''), IFNULL(tcc.cci, '')) cci,                      -- IFNULL(tc.cci, '') AS cci,
			IFNULL(tra.num_operacion, '') AS num_operacion,
			IFNULL(tra.monto, 0) AS monto,
			val.usuario AS validador_usuario,
			IFNULL(CONCAT( apt.nombre, ' ',IFNULL( apt.apellido_paterno, '' ), ' ',IFNULL( apt.apellido_materno, '' ) ), '') AS validador_nombre,
			IFNULL(tra.updated_at, '') fecha_hora_validacion,
			IFNULL(REPLACE(REPLACE(tra.observacion_cajero, '\n', ''), '\n', ''), '') observacion_cajero,
			IFNULL(REPLACE(REPLACE(tra.observacion_validador, '\n', ''), '\n', ''), '') observacion_validador,
			UPPER(IFNULL(loc.nombre, '')) AS turno_local,
    		IFNULL(tra.comision_monto, 0) comision_monto,
    		IFNULL(tra.tipo_rechazo_id, 0) tipo_rechazo_id,
    		CASE WHEN tra.estado = 5 THEN IFNULL(tra.update_user_id,0) ELSE 0 end update_user_id,
            CASE WHEN tra.enviar_comprobante = 1 THEN 'ENVIADO' ELSE 'PENDIENTE' END enviar_comprobante,
            IFNULL(tra.cuenta_pago_id, 0) cuenta_pago_id,
            IFNULL(tra.id_operacion_retiro,0 ) id_operacion_retiro,
			if (tra.id_operacion_retiro IN (0, 1), 'TELESERVICIOS' , if (tra.id_operacion_retiro = '2', 'TIENDA' , 'KURAX')) as razon,
            ifnull(trs.afect_balance,0) afect_balance,
            CASE WHEN tra.tipo_id IN (9,11) and IFNULL(tra.tipo_operacion,0) = 1 THEN 'PAGO' 
            	 WHEN tra.tipo_id IN (28,29) and IFNULL(tra.tipo_operacion,0) = 2 THEN 'DEVOLUCIÓN'
            	 WHEN tra.tipo_id = 21 THEN 'PROPINA'
            	ELSE 'PAGO'
            END tipo_operacion,
            IFNULL(cpr.nombre,'') banco_pago,
            IFNULL(tra.id_motivo_dev, 0) id_motivo_dev,
            IFNULL(md.descripcion, '') motivo_devolucion,
			IFNULL(ttra.nombre, '') nombre_trans,
            IFNULL(CONCAT( apt_aprov.nombre, ' ',IFNULL( apt_aprov.apellido_paterno, '' ), ' ',IFNULL( apt_aprov.apellido_materno, '' ) ), '') AS validado_por,
            IFNULL(tra2.registro_deposito, '') fecha_pago,
			IFNULL(tra.observacion_supervisor, '') link_atencion
			FROM
			tbl_televentas_clientes_transaccion tra
			INNER JOIN tbl_televentas_clientes_tipo_transaccion ttra ON ttra.id = tra.tipo_id
			LEFT JOIN tbl_televentas_cajeros_cuenta tcc ON tra.cajero_cuenta_id = tcc.id
			LEFT JOIN tbl_usuarios usu ON usu.id = tra.user_id
			LEFT JOIN tbl_televentas_clientes cli ON cli.id = tra.cliente_id
            LEFT JOIN tbl_televentas_clientes_transaccion tra2 ON tra.id = tra2.transaccion_id
			LEFT JOIN tbl_usuarios val ON val.id = tra.update_user_id
			LEFT JOIN tbl_personal_apt apt ON apt.id = val.personal_id
			LEFT JOIN tbl_televentas_clientes_cuenta tc ON tra.cuenta_id = tc.id
			LEFT JOIN tbl_bancos cuen ON tc.banco_id = cuen.id
			OR tcc.banco_id = cuen.id
			LEFT JOIN tbl_locales loc ON loc.cc_id = tra.cc_id 
			#LEFT JOIN tbl_televentas_tipo_contacto ttc ON ttc.id = tra.id_tipo_contacto
			LEFT JOIN tbl_televentas_estado_solicitud_retiro stra ON tra.estado = stra.id
            LEFT JOIN tbl_televentas_tipo_rechazo_solicitud_retiro trs ON tra.tipo_rechazo_id = trs.id
            LEFT JOIN tbl_televentas_cuentas_pago_retiro cpr ON tra.cuenta_pago_id = cpr.id
            LEFT JOIN tbl_televentas_tipo_motivo_devolucion md ON tra.id_motivo_dev = md.id
            LEFT JOIN tbl_usuarios aprov ON aprov.id = tra.user_valid_id
			LEFT JOIN tbl_personal_apt apt_aprov ON apt_aprov.id = aprov.personal_id
			LEFT JOIN tbl_televentas_transaccion_color tlvc ON tlvc.id_transaccion = tra.id 
			LEFT JOIN tbl_televentas_clientes_etiqueta ce ON cli.id = ce.client_id AND ce.status = 1 AND ce.etiqueta_id = 44
		WHERE
		$where_tipo
		$where_users_test
		$where_fecha_inicio 
		$where_fecha_fin 
		$where_estado 
		$where_validador 
		$where_cuenta 
		$where_razon
		$where_validador_supervisor
		$where_cuenta_pago
		$where_tipo_saldo 
		$nombre_busqueda
		"
		.$order
		.$limit;

	//$result["consulta_query_pagador"] = $query_1;
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
			count(*) cant
			FROM
			tbl_televentas_clientes_transaccion tra
			INNER JOIN tbl_televentas_clientes_tipo_transaccion ttra ON ttra.id = tra.tipo_id
			LEFT JOIN tbl_televentas_cajeros_cuenta tcc ON tra.cajero_cuenta_id = tcc.id
			LEFT JOIN tbl_usuarios usu ON usu.id = tra.user_id
			LEFT JOIN tbl_televentas_clientes cli ON cli.id = tra.cliente_id
            #LEFT JOIN tbl_televentas_clientes_transaccion tra2 ON tra.id = tra2.transaccion_id
			LEFT JOIN tbl_usuarios val ON val.id = tra.update_user_id
			LEFT JOIN tbl_personal_apt apt ON apt.id = val.personal_id
			LEFT JOIN tbl_televentas_clientes_cuenta tc ON tra.cuenta_id = tc.id
			LEFT JOIN tbl_bancos cuen ON tc.banco_id = cuen.id
			OR tcc.banco_id = cuen.id
			LEFT JOIN tbl_locales loc ON loc.cc_id = tra.cc_id
			LEFT JOIN tbl_televentas_estado_solicitud_retiro stra ON tra.estado = stra.id
            LEFT JOIN tbl_televentas_tipo_rechazo_solicitud_retiro trs ON tra.tipo_rechazo_id = trs.id
            LEFT JOIN tbl_televentas_cuentas_pago_retiro cpr ON tra.cuenta_pago_id = cpr.id
            LEFT JOIN tbl_televentas_tipo_motivo_devolucion md ON tra.id_motivo_dev = md.id
            LEFT JOIN tbl_usuarios aprov ON aprov.id = tra.user_valid_id
			LEFT JOIN tbl_personal_apt apt_aprov ON apt_aprov.id = aprov.personal_id
			LEFT JOIN tbl_televentas_transaccion_color tlvc ON tlvc.id_transaccion = tra.id 
			LEFT JOIN tbl_televentas_clientes_etiqueta ce ON cli.id = ce.client_id AND ce.status = 1 AND ce.etiqueta_id = 44
		WHERE
		$where_tipo
		$where_users_test
		$where_fecha_inicio 
		$where_fecha_fin 
		$where_estado 
		$where_validador 
		$where_cuenta 
		$where_razon
		$where_validador_supervisor
		$where_cuenta_pago
		$where_tipo_saldo
		$nombre_busqueda
		";
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
	$busqueda_id           = $_POST["ultimo_id"];
	$busqueda_tipo         = $_POST["tipo"];
	$transacciones_id = $_POST["transacciones_id"];
	$busqueda_validador_supervisor = $_POST["validador_supervisor"];
	$tipo_saldo            = $_POST["tipo_saldo"];

	$busqueda_cuenta_pago  = "";
	if (isset($_POST["cuenta_pago"]) && $_POST["cuenta_pago"] !== '') {
		$busqueda_cuenta_pago = implode(",",$_POST["cuenta_pago"]) ;
	}
	
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
		$where_estado = ' AND tra.estado IN ( 1 ) ';
	}

	$where_validador = '';
	if ((int) $busqueda_validador > 0) {
		$where_validador = ' AND tra.update_user_id=' . $busqueda_validador . ' ';
	}

	$where_fecha_inicio = " AND tra.created_at >= '" . $busqueda_fecha_inicio . " 00:00:00' ";
	$where_fecha_fin = " AND tra.created_at <= '" . $busqueda_fecha_fin . " 23:59:59' ";

	$where_id = "";
	$where_id = " AND tra.id > " . $busqueda_id . " ";
	//$where_id = " AND tra.update_user_at >= now()";
	$where_cuenta = "";
	if ((int) $usuario_id > 0) {
	$where_cuenta = " AND tra.cuenta_id IN( SELECT tcc.id 
								FROM tbl_televentas_clientes_cuenta tcc
									INNER JOIN tbl_cuentas_apt ca ON ca.banco_id = tcc.banco_id
									LEFT JOIN tbl_usuario_cuentas_apt uca ON uca.cuenta_apt_id = ca.id
								WHERE uca.usuario_id =". $usuario_id ." AND uca.estado = 1
									GROUP BY tcc.id
							)";
	}

	$where_cuenta_pago = "";
	if ($busqueda_cuenta_pago !== '') {
		if($busqueda_estado == 2){
			$where_cuenta_pago = " AND (tra.cuenta_pago_id IN ($busqueda_cuenta_pago) ) ";
		}
	}

	if($busqueda_estado != 0){
		if($transacciones_id != ""){
			$query_cancelador = "SELECT count(*) cant
						FROM tbl_televentas_clientes_transaccion tra 
						WHERE tra.id in (" . $transacciones_id . ") 
						AND tra.estado not in ( " . $busqueda_estado . ")";
			$list_query_2 = $mysqli->query($query_cancelador);
			$list_transacciones_canc = array();
			if ($mysqli->error) {
				$result["consulta_error"] = $mysqli->error;
			} else {
				while ($li = $list_query_2->fetch_assoc()) {
					$list_transacciones_canc[] = $li;
				}
			}
			$result["list_transacciones_canc"] = $list_transacciones_canc[0]["cant"];
		}else{
			$result["list_transacciones_canc"] = 0;
		}
		
	}else{
		$result["list_transacciones_canc"] = 0;
	}

	$where_tipo = ' tra.tipo_id IN (9,21,28) ';
	if((int) $busqueda_tipo > 0){
		if($busqueda_tipo == 1){
			$where_tipo = ' tra.tipo_id = 9 AND IFNULL(tra.tipo_operacion, 0) in (0, 1) ';
		}else if($busqueda_tipo == 2){
			$where_tipo = ' tra.tipo_id = 28 ';
		}else if($busqueda_tipo == 3){
			$where_tipo = ' tra.tipo_id = 21 ';
		}
	}

	$where_validador_supervisor = '';
	if($busqueda_validador_supervisor > 0){
		$where_validador_supervisor = ' AND IFNULL(tra.user_valid_id, 0) = ' . $busqueda_validador_supervisor;
	}

	$where_tipo_saldo = '';
	if($tipo_saldo != '0'){
		if($tipo_saldo == '1'){
			$where_tipo_saldo = ' AND IFNULL(ce.id, 0) = 0';
		}else if($tipo_saldo == '2'){
			$where_tipo_saldo = ' AND IFNULL(ce.id, 0) > 0';
		}
	}
	
	$result["busqueda_id"] = $busqueda_id;
	$result["where_id"] = $where_id;
	
	


	$query_1 = "
		SELECT 
			tra.id,
			CONCAT(year(now()), LPAD(tra.tipo_id, 2, 0), LPAD(tra.id, 10, 0)) cod_transaction,
			tra.cliente_id,
			tra.created_at fecha_hora_registro,
			usu.usuario AS cajero,
			IFNULL(CONCAT( cli.nombre, ' ', IFNULL( cli.apellido_paterno, '' ), ' ', IFNULL( cli.apellido_materno, '' ) ), '') AS cliente,
			stra.descripcion AS estado,
			tra.estado AS estado_id,
			IFNULL(tc.banco_id, '') AS cuenta_id,
			IFNULL(cuen.nombre, '') AS cuenta,
			IFNULL(tc.cuenta_num, '') AS cuenta_num,
			IFNULL(tc.cci, '') AS cci,
			IFNULL(tra.num_operacion, '') AS num_operacion,
			IFNULL(tra.monto, 0) AS monto,
			val.usuario AS validador_usuario,
			IFNULL(CONCAT( apt.nombre, ' ',IFNULL( apt.apellido_paterno, '' ), ' ',IFNULL( apt.apellido_materno, '' ) ), '') AS validador_nombre,
			IFNULL(tra.updated_at, '') fecha_hora_validacion,
			IFNULL(REPLACE(REPLACE(tra.observacion_cajero, '\n', ''), '\n', ''), '') observacion_cajero,
			IFNULL(REPLACE(REPLACE(tra.observacion_validador, '\n', ''), '\n', ''), '') observacion_validador,
			UPPER(IFNULL(loc.nombre, '')) AS turno_local,
    		IFNULL(tra.comision_monto, 0) comision_monto,
    		IFNULL(tra.tipo_rechazo_id, 0) tipo_rechazo_id,
    		CASE WHEN tra.estado = 5 THEN IFNULL(tra.update_user_id,0) ELSE 0 end update_user_id,
            CASE WHEN tra.enviar_comprobante = 1 THEN 'ENVIADO' ELSE 'PENDIENTE' END enviar_comprobante,
            IFNULL(tra.cuenta_pago_id, 0) cuenta_pago_id,
            IFNULL(tra.id_operacion_retiro,0 ) id_operacion_retiro, 
			if (tra.id_operacion_retiro IN (0, 1), 'TELESERVICIOS' , if (tra.id_operacion_retiro = '2', 'TIENDA' , 'KURAX')) as razon,
            ifnull(trs.afect_balance,0) afect_balance,
            CASE WHEN tra.tipo_id = 9 and IFNULL(tra.tipo_operacion,0) = 1 THEN 'PAGO' 
            	 WHEN tra.tipo_id = 28 and IFNULL(tra.tipo_operacion,0) = 2 THEN 'DEVOLUCIÓN'
            	 WHEN tra.tipo_id = 21 THEN 'PROPINA'
            	ELSE 'PAGO'
            END tipo_operacion,
            IFNULL(cpr.nombre,'') banco_pago,
            IFNULL(tra.id_motivo_dev, 0) id_motivo_dev,
            IFNULL(md.descripcion, '') motivo_devolucion,
            IFNULL(CONCAT( apt_aprov.nombre, ' ',IFNULL( apt_aprov.apellido_paterno, '' ), ' ',IFNULL( apt_aprov.apellido_materno, '' ) ), '') AS validado_por,
			IFNULL(tra.observacion_supervisor, '') link_atencion
			FROM
			tbl_televentas_clientes_transaccion tra
			LEFT JOIN tbl_usuarios usu ON usu.id = tra.user_id
			LEFT JOIN tbl_televentas_clientes cli ON cli.id = tra.cliente_id
			#LEFT JOIN tbl_televentas_clientes_transaccion tra2 ON tra.id = tra2.transaccion_id
			LEFT JOIN tbl_usuarios val ON val.id = tra.update_user_id
			LEFT JOIN tbl_personal_apt apt ON apt.id = val.personal_id
			LEFT JOIN tbl_televentas_clientes_cuenta tc ON tra.cuenta_id = tc.id
			LEFT JOIN tbl_bancos cuen ON tc.banco_id = cuen.id
			LEFT JOIN tbl_locales loc ON loc.cc_id = tra.cc_id 
			#LEFT JOIN tbl_televentas_tipo_contacto ttc ON ttc.id = tra.id_tipo_contacto
			LEFT JOIN tbl_televentas_estado_solicitud_retiro stra ON tra.estado = stra.id
            LEFT JOIN tbl_televentas_tipo_rechazo_solicitud_retiro trs ON tra.tipo_rechazo_id = trs.id
            LEFT JOIN tbl_televentas_cuentas_pago_retiro cpr ON tra.cuenta_pago_id = cpr.id
            LEFT JOIN tbl_televentas_tipo_motivo_devolucion md ON tra.id_motivo_dev = md.id
            LEFT JOIN tbl_usuarios aprov ON aprov.id = tra.update_user_id
			LEFT JOIN tbl_personal_apt apt_aprov ON apt_aprov.id = aprov.personal_id
			LEFT JOIN tbl_televentas_clientes_etiqueta ce ON cli.id = ce.client_id AND ce.status = 1 AND ce.etiqueta_id = 44
		WHERE
		$where_tipo 
		$where_users_test
		$where_fecha_inicio 
		$where_fecha_fin 
		$where_estado 
		$where_validador 
		$where_id 
		$where_cuenta 
		$where_validador_supervisor
		$where_cuenta_pago
		$where_tipo_saldo
		ORDER BY tra.id ASC
	";
	//$result["consulta_query"] = $query_1;
	$list_query = $mysqli->query($query_1);
	$list_transaccion = array();
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$list_transaccion[] = $li;
		}
	}

	$result["result_tls_pagador_ultima_version"] = 0;
	$query_ultima_version = "
		SELECT
			v.version
		FROM tbl_versiones v
		WHERE v.menu_id = 221
		ORDER BY v.id DESC
		LIMIT 1
		";
	$list_query_ultima_version = $mysqli->query($query_ultima_version);
	while ($li_ultima_version = $list_query_ultima_version->fetch_assoc()) {
		$result["result_tls_pagador_ultima_version"] = $li_ultima_version["version"];
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
// OBTENER IMÁGENES
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_imagenes_x_transaccion_retiro") {
	$id_transaccion = $_POST["id_transaccion"];

	$query_1 = "
				SELECT
					tta.id,
					tta.archivo 
				FROM
					tbl_televentas_transaccion_archivos tta 
				    INNER JOIN tbl_televentas_clientes_transaccion tra ON tta.transaccion_id = tra.id
				WHERE
					tra.transaccion_id = " . $id_transaccion . "
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
// OBTENER IMAGEN TRANSACCION PROPINA
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_imagenes_x_transaccion_propina") {
	$id_transaccion = $_POST["id_transaccion"];

	$query_1 = "
		SELECT
			tta.id,
			tta.archivo 
		FROM
			tbl_televentas_transaccion_archivos tta 
			INNER JOIN tbl_televentas_clientes_transaccion tra ON tra.id = tta.transaccion_id
		WHERE
			tta.transaccion_id = " . $id_transaccion . "
			AND tta.estado = 1
			AND tra.tipo_id = 21
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
if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_validacion_solicitud_retiro") {
	include("function_replace_invalid_caracters.php");
	$id_transaccion      = $_POST["id_transaccion"];
	$id_estado           = $_POST["id_estado"];
	$cuenta_id           = $_POST["cuenta_id"];
	$observacion         = replace_invalid_caracters($_POST["observacion"]);
	$motivo              = $_POST["motivo"];
    $registro            = str_replace('T', ' ', $_POST["registro"]);
    $num_operacion       = replace_invalid_caracters($_POST["num_operacion"]);
    $monto_retiro      	 = $_POST["monto_retiro"];
    $monto_comision      = $_POST["monto_comision"];
    $monto_real          = $_POST["monto_real"];
    $cuenta_pago_id      = $_POST["cuenta_pago_id"];
    $operation      	 = $_POST["operation"];
    //$supervisor_turno_id = $_POST["supervisor_turno_id"];
    //$pagador_turno_id    = $_POST["pagador_turno_id"];

	$usuario_id = $login ? $login['id'] : null;
	$turno = get_turno_tlv_pag();
	$turno_id = 0;
	$cc_id = 0;
	if (count($turno) > 0) {
		$turno_id = $turno[0]["id"];
		$cc_id = $turno[0]["cc_id"];
	}
	if ((int) $usuario_id > 0) {
		if ((int) $turno_id > 0) {
			if((int)$id_transaccion > 0){
				$query_1 = "
							SELECT 
								tra.id, 
								tra.tipo_id,
								IFNULL(tra.cliente_id, '') cliente_id, 
								IFNULL(tra.web_id, '') web_id,
								IFNULL(tra.monto, 0) monto,
								IFNULL(tra.cuenta_id, 0) cuenta_id,
								IFNULL(tra.cajero_cuenta_id, 0) cajero_cuenta_id,
								IFNULL(tra.tipo_operacion, 0) tipo_operacion,
								IFNULL(tra.id_motivo_dev, 0) id_motivo_dev,
								IFNULL(tra.user_valid_id, 0) user_valid_id,
								(SELECT b.balance FROM tbl_televentas_clientes_balance b
									WHERE b.tipo_balance_id = 1 AND b.cliente_id=tra.cliente_id
									LIMIT 1
								) balance_total,
								(SELECT b.balance FROM tbl_televentas_clientes_balance b
									WHERE b.tipo_balance_id = 4 AND b.cliente_id=tra.cliente_id
									LIMIT 1
								) balance_deposito,
								(SELECT b.balance FROM tbl_televentas_clientes_balance b
									WHERE b.tipo_balance_id = 5 AND b.cliente_id=tra.cliente_id
									LIMIT 1
								) balance_retiro_disponible
							FROM
								tbl_televentas_clientes_transaccion tra
							WHERE
								tra.id= $id_transaccion and tra.estado not in (2,3,4)
							LIMIT 1
						";
				$list_query = $mysqli->query($query_1);
				$list_transaccion = array();
				if ($mysqli->error) {
					$result["consulta_query"] = $query_1;
					$result["consulta_error"] = $mysqli->error;
				} else {
					while ($li = $list_query->fetch_assoc()) {
						$list_transaccion[] = $li;
					}
				}

				if (count($list_transaccion) == 0) {
					$result["http_code"] = 400;
					$result["status"] = "La transacción ya ha sido validada. Por favor actualice la pestaña. (F5)";
				} elseif (count($list_transaccion) == 1) {
					//Validar num ope unico
					$cmd_valid_num = "
						SELECT 
							tra.num_operacion, tra.created_at
						FROM
							tbl_televentas_clientes_transaccion tra
						INNER JOIN tbl_televentas_cuentas_pago_retiro cp ON tra.cuenta_pago_id = cp.id
						WHERE
							tra.tipo_id = 9 
							and tra.num_operacion = '" . $num_operacion . "'
							and cuenta_pago_id = " . $cuenta_pago_id . "
							and IFNULL(valid_num_ope_unico, 0) = 1
							and DATE(tra.created_at) = DATE(now())
					";
					$list_valid_num = $mysqli->query($cmd_valid_num);
					$list_registers_num_ope = array();
					if ($mysqli->error) {
						$result["consulta_query"] = $query_1;
						$result["consulta_error"] = $mysqli->error;
					} else {
						while ($li = $list_valid_num->fetch_assoc()) {
							$list_registers_num_ope[] = $li;
						}
					}
					if(count($list_registers_num_ope) > 0){
						$date = date_create($list_registers_num_ope[0]["created_at"]);
						$date = date_format($date, 'd/m/Y H:i:s');
						$result["http_code"] = 400;
						$result["status"] = "El número de operación ya se encuentra registrado con la misma entidad bancaria. \n Fecha de registro: " . $date;
						echo json_encode($result); exit();
					}

					$transaccion_id   = $list_transaccion[0]["id"];
					$tipo_id          = $list_transaccion[0]["tipo_id"];
					$cliente_id       = $list_transaccion[0]["cliente_id"];
					$web_id           = $list_transaccion[0]["web_id"];
					$monto            = $list_transaccion[0]["monto"];
					$cuenta_usada_id  = $list_transaccion[0]["cuenta_id"];
					$cajero_cuenta_usada_id  = $list_transaccion[0]["cajero_cuenta_id"];
					$tipo_operacion   = $list_transaccion[0]["tipo_operacion"];
					$id_motivo_dev    = $list_transaccion[0]["id_motivo_dev"];
					$user_valid_id    = $list_transaccion[0]["user_valid_id"];
					//$balance_actual   = $list_transaccion[0]["balance"];
					$balance_actual   = $list_transaccion[0]["balance_total"];
					$balance_total    = $list_transaccion[0]["balance_total"];
					$balance_deposito = $list_transaccion[0]["balance_deposito"];
					$balance_retiro_disponible = $list_transaccion[0]["balance_retiro_disponible"];
					
					$balance_nuevo = $balance_total;
					$id_tipo_tran = 0;
					if ((int) $cliente_id > 0) {
						if ((int) $id_estado === 2) { // Pagado
							if(!isset($_FILES['sec_tlv_pag_file_imagen']['tmp_name'])){
								$result["http_code"] = 400;
								$result["status"] = "Ocurrió un error al leer la imágen.";
								echo json_encode($result);
								exit();
							}
							$balance_nuevo = $balance_total;
							// $id_tipo_tran = 11;
							if((int)$tipo_id === 9){
								$id_tipo_tran = 11;
							}else if((int)$tipo_id === 21){
								$id_tipo_tran = 24;
							}else if((int)$tipo_id === 28){
								$id_tipo_tran = 29;
							} 
							//$id_tipo_tran = ( (int)$tipo_id === 9 ) ? 11 : 24;
						}else if((int) $id_estado == 3){ // Rechazo
							$balance_nuevo = $balance_total + $monto_real;
							// $id_tipo_tran = 12;
							if((int)$tipo_id === 9){
								$id_tipo_tran = 12;
							}else if((int)$tipo_id === 21){
								$id_tipo_tran = 25;
							}else if((int)$tipo_id === 28){
								$id_tipo_tran = 30;
							} 
							//$id_tipo_tran = ( (int)$tipo_id === 9 ) ? 12 : 25;
						}

						$query_2 = " 
							UPDATE tbl_televentas_clientes_transaccion 
							SET estado = " . $id_estado . ", 
								num_operacion = '" . $num_operacion . "',
								updated_at = now(),
								update_user_id = " . $usuario_id . ",
								comision_monto = " . $monto_comision . ",
								tipo_rechazo_id = " . $motivo . ",
								cuenta_pago_id = " . $cuenta_pago_id . ",
								update_user_at = now()
							WHERE id = " . $id_transaccion;
						$mysqli->query($query_2);

						$insert_command = " 
							INSERT INTO tbl_televentas_clientes_transaccion (
								tipo_id,
								cliente_id,
								cuenta_id,
								cajero_cuenta_id,
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
								tipo_rechazo_id,
								estado,
								observacion_cajero,
								observacion_validador,
								bono_mensual_actual,
								user_id,
								created_at,
								transaccion_id,
								cuenta_pago_id,
								id_operacion_retiro,
								tipo_operacion,
								id_motivo_dev,
								user_valid_id
							) VALUES (
								'". $id_tipo_tran ."',
								'". $cliente_id ."',
								'". $cuenta_usada_id ."',
								'". $cajero_cuenta_usada_id ."',
								'". $turno_id ."',
								'". $cc_id ."',
								'". $web_id ."',
								'". $num_operacion ."',
								'". $registro ."',
								'0',
								'". $monto_retiro ."',
								'". $monto_comision ."',
								'". $monto_retiro ."',
								'0',
								'0',
								'". $balance_nuevo ."',
								'". $motivo ."',
								'". $id_estado ."',
								'',
								'". $observacion ."',
								'0',
								'". $usuario_id ."',
								now(),
								'". $id_transaccion ."',
								'". $cuenta_pago_id ."',
								'". $operation ."',
								'" . $tipo_operacion ."',
								'" . $id_motivo_dev . "',
								'" . $user_valid_id . "'
							) 
							";
						$mysqli->query($insert_command);
						$error = '';
						if ($mysqli->error) {
							$result["insert_error"] = $mysqli->error;
							$error = $mysqli->error;
						}

						if ($error === '') {
							if ((int) $id_estado === 2) {
								$query_verifica = "SELECT * FROM tbl_televentas_clientes_transaccion  ";
								$query_verifica .= " WHERE tipo_id in (11,24,29)  ";
								$query_verifica .= " AND cliente_id='" . $cliente_id . "'  ";
								$query_verifica .= " AND user_id='" . $usuario_id . "' ";
								$query_verifica .= " AND turno_id='" . $turno_id . "' ";
								$query_verifica .= " AND monto='" . $monto_retiro . "' ";
								$query_verifica .= " AND transaccion_id='" . $id_transaccion . "' ";
								$query_verifica .= " AND cuenta_id='" . $cuenta_usada_id . "' ";
								$query_verifica .= " AND cajero_cuenta_id='" . $cajero_cuenta_usada_id . "' ";
								$query_verifica .= " AND cuenta_pago_id='" . $cuenta_pago_id . "' ";
								$query_verifica .= " AND tipo_operacion='" . $tipo_operacion . "' ";
								$query_verifica .= " AND id_motivo_dev='" . $id_motivo_dev . "' ";
								$query_verifica .= " ORDER BY id DESC ";
								$list_query = $mysqli->query($query_verifica);
								$list_transaccion_verifica = array();
								if ($mysqli->error) {
									$result["http_code"] = 400;
									$result["status"] = "Ocurrió un error al consultar la transacción.";	
									$result["query_verifica"] = $query_verifica;
									$result["query_error"] = $mysqli->error;
								} else {
									while ($li = $list_query->fetch_assoc()) {
										$list_transaccion_verifica[] = $li;
									}
									if (count($list_transaccion_verifica) === 0) {
										$result["http_code"] = 400;
										$result["status"] = "No se guardó la transacción.";								
									} elseif (count($list_transaccion_verifica) === 1) {
										$transaccion_id_nuevo = $list_transaccion_verifica[0]["id"];

										//**************************************************************************************************
										//**************************************************************************************************
										// IMAGEN
										//**************************************************************************************************
										$path = "/var/www/html/files_bucket/retiros/";
										$file = [];
										$imageLayer = [];
										if (!is_dir($path))
											mkdir($path, 0777, true);
										$imageProcess = 0;

										$filename = $_FILES['sec_tlv_pag_file_imagen']['tmp_name'];
										$filenametem = $_FILES['sec_tlv_pag_file_imagen']['name'];
										$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
										if ($filename != "") {
											$fileExt = pathinfo($_FILES['sec_tlv_pag_file_imagen']['name'], PATHINFO_EXTENSION);
											$resizeFileName = $transaccion_id_nuevo . "_" . date('YmdHis');
											$nombre_archivo = $resizeFileName . ".png"; //" . $fileExt;
											if ($fileExt == "pdf") {
												move_uploaded_file($_FILES['sec_tlv_pag_file_imagen']['tmp_name'], $path . $nombre_archivo);
											} else {
												$sourceProperties = getimagesize($filename);
												$size = $_FILES['sec_tlv_pag_file_imagen']['size'];
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
												$imageLayer = sec_tlv_pag_resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);

												$file[0] = imagegif($imageLayer[0], $path . $nombre_archivo);
												$file[1] = imagegif($imageLayer[1], $path . "min_" . $nombre_archivo);
												move_uploaded_file($file[0], $path . $nombre_archivo);
												move_uploaded_file($file[1], $path . $nombre_archivo);
												$imageProcess = 1;
											}

											$comando = " 
													INSERT INTO tbl_televentas_transaccion_archivos (
														transaccion_id,
														tipo,
														archivo,
														created_at,
														estado
													) VALUES(
														'" . $transaccion_id_nuevo . "',
														1,
														'" . $nombre_archivo . "',
														'" . date('Y-m-d H:i:s') . "',
														1
													)";
											$mysqli->query($comando);

											$query_verifica_2 = "SELECT * FROM tbl_televentas_transaccion_archivos
																WHERE transaccion_id = " . $transaccion_id_nuevo ."
																AND archivo = '" . $nombre_archivo . "'";
											$list_query_2 = $mysqli->query($query_verifica_2);
											$list_transaccion_verifica_img = array();
											while ($li = $list_query_2->fetch_assoc()) {
												$list_transaccion_verifica_img[] = $li;
											}
											if (count($list_transaccion_verifica_img) === 0) {
												$result["http_code"] = 400;
												$result["status"] = "No se guardó la imagen.";
											} else {
												$filepath = $path . $resizeFileName . "." . $fileExt;	
												$result["http_code"] = 200;
												$result["status"] = "OK";
											}
										} else {
											$result["http_code"] = 400;
											$result["status"] = "Ocurrió un error al leer la imágen. 2";
										}
										//**************************************************************************************************
										//**************************************************************************************************
									} elseif (count($list_transaccion_verifica) > 1) {
										$result["http_code"] = 400;
										$result["status"] = "Se duplicaron las transacciones, por favor informar a informática.";
									} else {
										$result["http_code"] = 400;
										$result["status"] = "Ocurrió un error al guardar la transacción.";	
									}
								}

							} else if ((int) $id_estado === 3){ // Rechazar
								$query_verifica = "SELECT * FROM tbl_televentas_clientes_transaccion  ";
								$query_verifica .= " WHERE tipo_id in (12,25,30)  ";
								$query_verifica .= " AND cliente_id='" . $cliente_id . "'  ";
								$query_verifica .= " AND user_id='" . $usuario_id . "' ";
								$query_verifica .= " AND turno_id='" . $turno_id . "' ";
								$query_verifica .= " AND monto='" . $monto_retiro . "' ";
								$query_verifica .= " AND transaccion_id='" . $id_transaccion . "' ";
								$query_verifica .= " AND cuenta_id='" . $cuenta_usada_id . "' ";
								$query_verifica .= " AND cajero_cuenta_id='" . $cajero_cuenta_usada_id . "' ";
								$query_verifica .= " AND cuenta_pago_id='" . $cuenta_pago_id . "' ";
								$query_verifica .= " AND tipo_operacion='" . $tipo_operacion . "' ";
								$query_verifica .= " AND id_motivo_dev='" . $id_motivo_dev . "' ";
								$query_verifica .= " ORDER BY id DESC ";
								$list_query = $mysqli->query($query_verifica);
								$list_transaccion_verifica = array();
								if ($mysqli->error) {
									$result["http_code"] = 400;
									$result["status"] = "Ocurrió un error al consultar la transacción.";	
									$result["query_verifica"] = $query_verifica;
									$result["query_error"] = $mysqli->error;
								} else {
									while ($li = $list_query->fetch_assoc()) {
										$list_transaccion_verifica[] = $li;
									}
									if (count($list_transaccion_verifica) === 0) {
										$result["http_code"] = 400;
										$result["status"] = "No se guardó la transacción.";								
									} elseif (count($list_transaccion_verifica) === 1) {
										$transaccion_id_nuevo = $list_transaccion_verifica[0]["id"];
										$result = sec_tlv_pag_rollback_transaccion_retiro($cliente_id, (int)$id_transaccion, $usuario_id, $turno_id, $observacion,$transaccion_id_nuevo);
									}
								}
							} else {
								$result["http_code"] = 400;
								$result["status"] = "Ocurrió un error al recibir el estado de la transacción.";
							}
						} else {
							$result["http_code"] = 400;
							$result["status"] = "Ocurrió un error al guardar la validación.";
						}
					} else {
						$result["http_code"] = 400;
						$result["status"] = "No pudimos obtener al cliente.";
					}
				} else {
					$result["http_code"] = 400;
					$result["status"] = "Ocurrió un error al consultar el estado de la transacción.";
				}
			} else {
				$result["http_code"] = 400;
				$result["status"] = "El codigo de transacción es ilegible.";
				$result["result"] = '';
				$result["id_transaccion"] = $id_transaccion;
			}
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al consultar el turno.";
			$result["result"] = $turno;
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Sesión perdida, actualice la página.";
	}
}



if (isset($_POST["accion"]) && $_POST["accion"]==="listar_transacciones_retiros_export_xls") {
	global $mysqli;
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$busqueda_fecha_inicio = $_POST["fecha_inicio"];
	$busqueda_fecha_fin    = $_POST["fecha_fin"];
	$busqueda_estado       = $_POST["estado"];
	$busqueda_validador    = $_POST["validador"];
	$busqueda_razon	       = $_POST["razon"];
	$busqueda_tipo	       = $_POST["tipo"];
	$busqueda_validador_supervisor = $_POST["validador_supervisor"];
	$tipo_saldo            = $_POST["tipo_saldo"];
	$busqueda_cuenta       = "";
	if (isset($_POST["cuenta"]) && $_POST["cuenta"] !== '') {
		$busqueda_cuenta = implode(",",$_POST["cuenta"]) ;
	}

	$busqueda_cuenta_pago  = "";
	if (isset($_POST["cuenta_pago"]) && $_POST["cuenta_pago"] !== '') {
		$busqueda_cuenta_pago = implode(",",$_POST["cuenta_pago"]) ;
	}

	$usuario_id = $login["id"];

	$where_users_test="";
	if((int)$area_id !== 6) {// diferente de sistemas
		$where_users_test="	
			AND IFNULL(tra.web_id, '') not in ('3333200', '71938219') 
			AND IFNULL(tra.user_id, '') not in (1, 249, 250, 2572, 3028) 
		";
	}

	$where_tipo = ' 
		(
			(tra.tipo_id IN (9,21,28))
			-- (tra.tipo_id IN (9,21,28) and IFNULL(tra.caja_vip, 0) = 0)
            -- OR (tra.tipo_id IN (11,24,29) and IFNULL(tra.caja_vip, 0) = 1)
         )
	';
	if((int) $busqueda_tipo > 0){
		if($busqueda_tipo == 1){ // Retiros
			//$where_tipo = ' tra.tipo_id = 9 AND IFNULL(tra.tipo_operacion, 0) in (0, 1) ';
			$where_tipo = '
				(
					(tra.tipo_id = 9)
					-- (tra.tipo_id = 9 and IFNULL(tra.caja_vip, 0) = 0)
		            -- OR (tra.tipo_id = 11 and IFNULL(tra.caja_vip, 0) = 1)
		         )
			';
		}else if($busqueda_tipo == 2){ // Devoluciones
			//$where_tipo = ' tra.tipo_id = 28 ';
			$where_tipo = '
				(
					(tra.tipo_id = 28)
					-- (tra.tipo_id = 28 and IFNULL(tra.caja_vip, 0) = 0)
		            -- OR (tra.tipo_id = 29 and IFNULL(tra.caja_vip, 0) = 1)
		         )
			';
		}else if($busqueda_tipo == 3){ // Propinas
			//$where_tipo = ' tra.tipo_id = 21 ';
			$where_tipo = '
				(
					(tra.tipo_id = 21 and IFNULL(tra.caja_vip, 0) = 0)
		            -- OR (tra.tipo_id = 24 and IFNULL(tra.caja_vip, 0) = 1)
		         )
			';
		}
	}

	$where_estado = '';
	if ($busqueda_estado !== '0') {
		$where_estado = ' AND tra.estado = ' . $busqueda_estado . ' ';
	} else {
		$where_estado = ' ';
		//$where_estado = ' AND tra.estado in (1,2,3) ';
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
		/*$where_cuenta = "
			AND cuen.id IN (
							SELECT
								uca.cuenta_apt_id
							FROM
								tbl_usuario_cuentas_apt AS uca
							WHERE uca.usuario_id = $usuario_id AND uca.estado = 1
							GROUP BY uca.cuenta_apt_id
			) 
		";*/

		/*$where_cuenta = "
			AND cuen.id IN (
							SELECT
								uca.cuenta_apt_id
							FROM
								tbl_usuario_cuentas_apt AS uca
							WHERE uca.usuario_id = $usuario_id AND uca.estado = 1
							GROUP BY uca.cuenta_apt_id
			) 
		";*/
	}
	$where_cuenta_pago = "";
	if ($busqueda_cuenta_pago !== '') {
		if($busqueda_estado == 2){
			$where_cuenta_pago = " AND (tra.cuenta_pago_id IN ($busqueda_cuenta_pago) ) ";
		}
	}



	$where_razon = '';
	if ((int) $busqueda_razon > 0) {
		if($busqueda_razon == 1){
			$where_razon = ' AND IFNULL(tra.id_operacion_retiro, 0) in (0, 1) ';
		}else{
			$where_razon = ' AND IFNULL(tra.id_operacion_retiro, 0) = ' . $busqueda_razon . ' ';
		}
	}

	

	$where_validador_supervisor = '';
	if($busqueda_validador_supervisor > 0){
		$where_validador_supervisor = ' AND IFNULL(tra.user_valid_id, 0) = ' . $busqueda_validador_supervisor;
	}

	$where_tipo_saldo = '';
	if($tipo_saldo != '0'){
		if($tipo_saldo == '1'){
			$where_tipo_saldo = ' AND IFNULL(ce.id, 0) = 0';
		}else if($tipo_saldo == '2'){
			$where_tipo_saldo = ' AND IFNULL(ce.id, 0) > 0';
		}
	}

	$query_1 = "
		SELECT 
			CONCAT(year(now()), LPAD(tra.tipo_id, 2, 0), LPAD(tra.id, 10, 0), ' ') cod_transaction,
			tra.created_at fecha_hora_registro,
			IFNULL(tra.updated_at, '') fecha_hora_validacion,
			usu.usuario AS cajero,
			UPPER(IFNULL(loc.nombre, '')) AS turno_local,
			IFNULL(CONCAT( cli.nombre, ' ', IFNULL( cli.apellido_paterno, '' ), ' ', IFNULL( cli.apellido_materno, '' ) ), '') AS cliente,
			IFNULL(cuen.nombre, '') AS cuenta,
			IF(tra.tipo_id IN (9,28), IFNULL(tc.cuenta_num, ''), IFNULL(tcc.cuenta_num, '')) cuenta_num, 
			IF(tra.tipo_id IN (9,28), IFNULL(tc.cci, ''), IFNULL(tcc.cci, '')) cci, 
			IFNULL(tra.observacion_supervisor, '') link_atencion,
            IFNULL(cpr.nombre,'') banco_pago,
			IFNULL(tra.monto, 0) AS monto,
    		IFNULL(tra.comision_monto, 0) comision_monto,
			IFNULL(tra.num_operacion, '') AS num_operacion,
			stra.descripcion AS estado,
			trs.descripcion AS tipo_rechazo,
			IFNULL(CONCAT( apt.nombre, ' ',IFNULL( apt.apellido_paterno, '' ), ' ',IFNULL( apt.apellido_materno, '' ) ), '') AS validador_nombre, 
			if (tra.id_operacion_retiro IN (0, 1), 'TELESERVICIOS' , if (tra.id_operacion_retiro = '2', 'TIENDA' , 'KURAX')) as razon,
            CASE WHEN tra.enviar_comprobante = 1 THEN 'ENVIADO' ELSE 'PENDIENTE' END enviar_comprobante,
            CASE WHEN tra.tipo_id = 9 and IFNULL(tra.tipo_operacion,0) = 1 THEN 'PAGO' 
            	 WHEN tra.tipo_id = 28 and IFNULL(tra.tipo_operacion,0) = 2 THEN 'DEVOLUCIÓN'
            	 WHEN tra.tipo_id = 21 THEN 'PROPINA'
            	ELSE 'PAGO'
            END tipo_operacion,
            IFNULL(CONCAT( apt_aprov.nombre, ' ',IFNULL( apt_aprov.apellido_paterno, '' ), ' ',IFNULL( apt_aprov.apellido_materno, '' ) ), '') AS validado_por
		FROM
			tbl_televentas_clientes_transaccion tra
			INNER JOIN tbl_televentas_clientes_tipo_transaccion ttra ON ttra.id = tra.tipo_id
			LEFT JOIN tbl_televentas_cajeros_cuenta tcc ON tra.cajero_cuenta_id = tcc.id
			LEFT JOIN tbl_usuarios usu ON usu.id = tra.user_id
			LEFT JOIN tbl_televentas_clientes cli ON cli.id = tra.cliente_id
            #LEFT JOIN tbl_televentas_clientes_transaccion tra2 ON tra.id = tra2.transaccion_id
			LEFT JOIN tbl_usuarios val ON val.id = tra.update_user_id
			LEFT JOIN tbl_personal_apt apt ON apt.id = val.personal_id
			LEFT JOIN tbl_televentas_clientes_cuenta tc ON tra.cuenta_id = tc.id
			LEFT JOIN tbl_bancos cuen ON tc.banco_id = cuen.id
			OR tcc.banco_id = cuen.id
			LEFT JOIN tbl_locales loc ON loc.cc_id = tra.cc_id 
			#LEFT JOIN tbl_televentas_tipo_contacto ttc ON ttc.id = tra.id_tipo_contacto
			LEFT JOIN tbl_televentas_estado_solicitud_retiro stra ON tra.estado = stra.id
            LEFT JOIN tbl_televentas_tipo_rechazo_solicitud_retiro trs ON tra.tipo_rechazo_id = trs.id
            LEFT JOIN tbl_televentas_cuentas_pago_retiro cpr ON tra.cuenta_pago_id = cpr.id
            LEFT JOIN tbl_televentas_tipo_motivo_devolucion md ON tra.id_motivo_dev = md.id
            LEFT JOIN tbl_usuarios aprov ON aprov.id = tra.user_valid_id
			LEFT JOIN tbl_personal_apt apt_aprov ON apt_aprov.id = aprov.personal_id
			LEFT JOIN tbl_televentas_clientes_etiqueta ce ON cli.id = ce.client_id AND ce.status = 1 AND ce.etiqueta_id = 44
		WHERE
			$where_tipo
			$where_users_test
			$where_fecha_inicio 
			$where_fecha_fin 
			$where_estado 
			$where_validador 
			$where_cuenta 
			$where_razon
			$where_validador_supervisor
			$where_cuenta_pago
			$where_tipo_saldo
		ORDER BY tra.id ASC
		";
	//$result["query_xls"] = $query_1; echo json_encode($result); exit();
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
			"cod_transaction" => "Cod Transaccion",
			"fecha_hora_registro" => "Fecha Solicitud",
			"fecha_hora_validacion" => "Fecha Pago",
			"cajero" => "Usuario",
			"turno_local" => "Caja",
			"cliente" => "Cliente",
			"cuenta" => "Banco",
			"cuenta_num" => "Nro Cuenta",
			"cci" => "CCI",
			"link_atencion" => "Link Atención",
			"banco_pago" => "Banco Pago",
			"monto" => "Monto S/",
			"comision_monto" => "Comisión S/",
			"num_operacion" => "Nro Operación",
			"estado" => "Estado",
			"tipo_rechazo" => "Motivo Rechazo",
			"validador_nombre" => "Pagador",
			"razon" => "Razón",
			"enviar_comprobante" => "Comprobante",
			"tipo_operacion" => "Tipo",
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
		$file_title = "reporte_televentas_solicitudes_retiros_" . $date->getTimestamp();

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

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_tlv_dep_actualizar_fecha_abono") {
	$id_transaccion = $_POST["id_transaccion"];
	$fecha_hora_actual = str_replace('T', ' ', $_POST["fecha_hora_actual"]);
	$fecha_hora_modificada = str_replace('T', ' ', $_POST["fecha_hora_modificada"]);

	$id_usuario = $login ? $login['id'] : null;

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


if (isset($_POST["accion"]) && $_POST["accion"] === "cambiar_estado_solicitud_retiro") {
	$id_transaccion = $_POST["id_transaccion"];
	$id_estado = $_POST["id_estado"];
	$id_usuario = $login ? $login['id'] : null;
	$error_update = "";
	$query_update = "UPDATE tbl_televentas_clientes_transaccion 
					SET estado = " . $id_estado . ", 
					update_user_at = now(),
					update_user_id = " . $id_usuario . " 
					WHERE id = " . $id_transaccion .
					" AND estado = 1 ";
	$mysqli->query($query_update);

	if($mysqli->error){
		$error_update .= 'Error al cambiar el estado de la transacción: ' . $mysqli->error . $query_update;
	}

	if ($error_update == '') {
		$result["http_code"] = 200;
	} else {
		$result["http_code"] = 400;
	}

	$result["status"] = "Datos obtenidos de gestion.";
	$result["error_update"] = $error_update;
}

function sec_tlv_pag_resizeImage($resourceType, $image_width, $image_height) {
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

//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER SUPERVISOR Y PAGADOR DE TURNO
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "sec_tlv_pagador_obtener_supervisor_pagador_turno") {

	$usuario_id = $login["id"];

	$query = "	
		SELECT 
			IFNULL((select user_id from tbl_televentas_programaciones where tipo_programacion = 1 and now() >= desde and now() <= hasta LIMIT 1),0)supervisor,
			IFNULL((select user_id from tbl_televentas_programaciones where tipo_programacion = 2 and now() >= desde and now() <= hasta LIMIT 1),0) pagador
		FROM tbl_televentas_programaciones
			WHERE now() >= desde and now() <= hasta
		LIMIT 1
		";

	$list_query = $mysqli->query($query);
	$list_turnos = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_turnos[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list_turnos) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay responsables en turno.";
	} elseif (count($list_turnos) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_turnos;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los responsables en turno.";
	}
}

//*******************************************************************************************************************
// OBTENER CUENTAS DE CAJERO PARA DEPOSITAR PROPINAS
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_cuentas_x_cajero") {

	$cliente_id = $_POST["cliente_id"];
	$id_transaccion = $_POST["id_transaccion"];

	$query_select = "
		SELECT
			id
			,user_id
		FROM
			tbl_televentas_clientes_transaccion
		WHERE
			id = $id_transaccion
	";
	//$result["consulta_query"] = $query_3;
	$list_query_0 = $mysqli->query($query_select);
	$consulta_id_transacc = array();
	while ($li = $list_query_0->fetch_assoc()) {
		$consulta_id_transacc[] = $li;
		$cajero_id = $li["user_id"];
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($consulta_id_transacc) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay transacciones con el id de propina.";
	} elseif (count($consulta_id_transacc) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $consulta_id_transacc;

		$query_1 = "
			SELECT
				tcc.id cod,
				ifnull( b.nombre, 0 ) banco,
				ifnull(tcc.banco_id, 0) banco_id,
				substring(tcc.cuenta_num, -4) AS cuenta_num_cliente,
				lpad(substring(tcc.cuenta_num, -4), length(tcc.cuenta_num),'*') AS cuenta_num,
				tcc.cuenta_num as cuenta_num_ent,
				lpad(substring(tcc.cci, -4), length(tcc.cci),'*') AS cci,
				tcc.cci as cci_ent
			FROM
				tbl_televentas_cajeros_cuenta tcc
				JOIN tbl_bancos b ON b.id = tcc.banco_id 
			WHERE
				tcc.`status` = 1 
				AND tcc.user_id = $cajero_id
			";
			//$result["consulta_query"] = $query_3;
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
				$result["status"] = "No hay transacciones nuevas.";
			} elseif (count($list_transaccion) > 0) {
				$result["http_code"] = 200;
				$result["status"] = "ok";
				$result["result"] = $list_transaccion;
			} else {
				$result["http_code"] = 400;
				$result["status"] = "Ocurrió un error al consultar transacciones nuevas.";
			}

		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al consultar transacciones nuevas.";
		}


	
}

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_tlv_pag_actualizar_cuenta_solicitud_propina") {
	include("function_replace_invalid_caracters.php");
	$id_transaccion = $_POST["id_transaccion"];
	$id_cuenta = $_POST["id_cuenta"];
	$usuario_id = $login ? $login['id'] : 0;

	$error_update = '';
	$query_update = "UPDATE tbl_televentas_clientes_transaccion SET cuenta_id = " . $id_cuenta . ", update_user_at = now() WHERE id = " . $id_transaccion;
	$mysqli->query($query_update);

	if($mysqli->error){
		$error_update .= 'Error al actualizar la transaccion: ' . $mysqli->error . $query_update;
	}
	if ($error_update == '') {
		$result["http_code"] = 200;
	} else {
		$result["http_code"] = 400;
	}
	$result["status"] = "Datos obtenidos de gestion.";
	$result["error_update"] = $error_update;
}

//*******************************************************************************************************************
// OBTENER TRANSACCIONES NUEVAS 
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_cuentas_x_cliente") {

	$cliente_id = $_POST["cliente_id"];

	$query_1 = "
		SELECT
			tcc.id cod,
			ifnull( b.nombre, 0 ) banco,
			ifnull(tcc.banco_id, 0) banco_id,
			substring(tcc.cuenta_num, -4) AS cuenta_num_cliente,
			lpad(substring(tcc.cuenta_num, -4), length(tcc.cuenta_num),'*') AS cuenta_num,
			tcc.cuenta_num as cuenta_num_ent,
			lpad(substring(tcc.cci, -4), length(tcc.cci),'*') AS cci,
			tcc.cci as cci_ent
		FROM
			tbl_televentas_clientes_cuenta tcc
			JOIN tbl_bancos b ON b.id = tcc.banco_id 
		WHERE
			tcc.`status` = 1 
			AND tcc.cliente_id = $cliente_id 
		";
	//$result["consulta_query"] = $query_3;
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
		$result["status"] = "No hay transacciones nuevas.";
	} elseif (count($list_transaccion) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_transaccion;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar transacciones nuevas.";
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_cuenta_x_cliente") {
	include("function_replace_invalid_caracters.php");
	$id_cuenta = $_POST["id_cuenta"];
	$id_cliente = $_POST["id_cliente"];
	$id_banco = $_POST["id_banco"];
	$cuenta_num = strtoupper(trim(replace_invalid_caracters($_POST["cuenta_num"])));
	$cci = strtoupper(trim(replace_invalid_caracters($_POST["cci"])));
	$op = $_POST["operacion"];
	$usuario_id = $login ? $login['id'] : 0;

	if ((int) $usuario_id > 0) {
		if($op == 1){
			$query_1 = "SELECT cc.id FROM tbl_televentas_clientes_cuenta cc 
						WHERE cc.cliente_id = '" . $id_cliente . "' AND cc.banco_id = '" . $id_banco . "' AND cc.cuenta_num = '" . $cuenta_num . "' AND cc.status = 1";
			$list_query = $mysqli->query($query_1);
			if ($mysqli->error) {
				$result["query_1_error"] = $mysqli->error;
			}
			$list_1 = array();
			while ($li = $list_query->fetch_assoc()) {
				$list_1[] = $li;
			}
			if (count($list_1) == 0) {
				$insert_1 = " 
					INSERT INTO tbl_televentas_clientes_cuenta
						(
						`cliente_id`,
						`banco_id`,
						`cuenta_num`,
						`cci`,
						`status`,
						`user_id`,
						`created_at`
						) VALUES (
						'" . $id_cliente . "',
						'" . $id_banco . "',
						'" . $cuenta_num . "',
						'" . $cci . "',
						'1',
						'" . $usuario_id . "',
						now()
						)";
				$mysqli->query($insert_1);
				if ($mysqli->error) {
					$result["insert_1_error"] = $mysqli->error;
				}
				$list_query = $mysqli->query($query_1);
				$list_2 = array();
				while ($li = $list_query->fetch_assoc()) {
					$list_2[] = $li;
				}
				if ($mysqli->error) {
					$result["query_2_error"] = $mysqli->error;
				}
				if (count($list_2) == 1) {
					$result["http_code"] = 200;
					$result["status"] = "ok";
				} else {
					$result["http_code"] = 400;
					$result["status"] = "Ocurrió un error al registrar la cuenta, vuelva a intentarlo por favor.";
				}
			} elseif (count($list_1) > 0) {
				$result["http_code"] = 400;
				$result["status"] = "Esta cuenta ya existe.";
			} else {
				$result["http_code"] = 400;
				$result["status"] = "Ocurrió un error al consultar las coincidencias.";
			}
		}else if ($op == 2){
			$error_update = '';
			$query_update = "UPDATE tbl_televentas_clientes_cuenta 
							SET cuenta_num = '" . $cuenta_num . "', cci = '" . $cci . "', 
							update_user_id = " . $usuario_id . ", updated_at = now()
							WHERE id = " . $id_cuenta;
			$mysqli->query($query_update);

			if($mysqli->error){
				$error_update .= 'Error editar la cuenta: ' . $mysqli->error . $query_update;
			}
			if ($error_update == '') {
				$result["http_code"] = 200;
			} else {
				$result["http_code"] = 400;
			}

			$result["status"] = "Datos obtenidos de gestion.";
			$result["error_update"] = $error_update;
		}else if($op == 3){
			$error_update = '';
			$query_update = "UPDATE tbl_televentas_clientes_cuenta 
							SET status = 0, update_user_id = " . $usuario_id . ", updated_at = now()
							WHERE id = " . $id_cuenta;
			$mysqli->query($query_update);

			if($mysqli->error){
				$error_update .= 'Error desactivar la cuenta: ' . $mysqli->error . $query_update;
			}
			if ($error_update == '') {
				$result["http_code"] = 200;
			} else {
				$result["http_code"] = 400;
			}

			$result["status"] = "Datos obtenidos de gestion.";
			$result["error_update"] = $error_update;
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Sesión perdida.";
	}
}


if (isset($_POST["accion"]) && $_POST["accion"] === "sec_tlv_pg_cambio_estado_color") {
 
	$id_transaccion = $_POST["id_transaccion"];
	$usuario_id = $login ? $login['id'] : 0; 

	$query_cp = "SELECT color FROM tbl_televentas_estado_solicitud_retiro  
				WHERE id = 6  ";

	$list_cp = $mysqli->query($query_cp);
	$row = $list_cp->fetch_array();

	$color_pend = $row['color'];
 
	
	$query_1 = "SELECT cc.id FROM tbl_televentas_transaccion_color cc 
				WHERE cc.id_transaccion = '" . $id_transaccion . "'  ";
			$list_query = $mysqli->query($query_1);
			if ($mysqli->error) {
				$result["query_1_error"] = $mysqli->error;
			}
			$list_1 = array();
			while ($li = $list_query->fetch_assoc()) {
				$list_1[] = $li;
			}
			if (count($list_1) == 0) {
				$error_insert = '';
				$query_insert = "INSERT INTO tbl_televentas_transaccion_color (id_transaccion, color)
				VALUES (" . $id_transaccion . ", '" . $color_pend . "')";

				$mysqli->query($query_insert);

				if($mysqli->error){
					$error_insert .= 'Error al insertar la transaccion: ' . $mysqli->error . $query_insert;
				}
				if ($error_insert == '') {
					$result["http_code"] = 200;
				} else {
					$result["http_code"] = 400;
				}
			
				$result["status"] = "ok";
				$result["error_insert"] = $error_insert;

			}
}



if (isset($_POST["accion"]) && $_POST["accion"] === "sec_tlv_pag_transaccion_verificada") {
	include("function_replace_invalid_caracters.php");
	$id_transaccion = $_POST["id_transaccion"];
	$usuario_id = $login ? $login['id'] : 0;

	$error_update = '';
	$query_update = "UPDATE tbl_televentas_clientes_transaccion 
					SET 
					estado = 6, 
					update_user_at = now(),
					user_valid_id = " . $usuario_id . "
					WHERE id = " . $id_transaccion .
					" AND estado = 5 ";
	$mysqli->query($query_update);

	if($mysqli->error){
		$error_update .= 'Error al verificar la transaccion: ' . $mysqli->error . $query_update;
	}
	if ($error_update == '') {
		$result["http_code"] = 200;
	} else {
		$result["http_code"] = 400;
	}

	$result["status"] = "Datos obtenidos de gestion.";
	$result["error_update"] = $error_update;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_tlv_pag_actualizar_cuenta_solicitud") {
	include("function_replace_invalid_caracters.php");
	$id_transaccion = $_POST["id_transaccion"];
	$id_cuenta = $_POST["id_cuenta"];
	$usuario_id = $login ? $login['id'] : 0;

	$error_update = '';
	$query_update = "UPDATE tbl_televentas_clientes_transaccion SET cuenta_id = " . $id_cuenta . ", update_user_at = now() WHERE id = " . $id_transaccion;
	$mysqli->query($query_update);

	if($mysqli->error){
		$error_update .= 'Error al actualizar la transaccion: ' . $mysqli->error . $query_update;
	}
	if ($error_update == '') {
		$result["http_code"] = 200;
	} else {
		$result["http_code"] = 400;
	}
	$result["status"] = "Datos obtenidos de gestion.";
	$result["error_update"] = $error_update;
}

//*******************************************************************************************************************
// ELIMINAR PAGO APUESTA
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "eliminar_transaccion_pago_apuesta") {
	include("function_replace_invalid_caracters.php");

	$id_cliente = $_POST["cliente_id"];
	//$id_trans = $_POST["trans_id"];
	$tipo_id = 5;
	$observacion = replace_invalid_caracters($_POST["observacion"]);

	$usuario_id = $login ? $login['id'] : 0;
	if ((int) $usuario_id > 0) {

		$turno = get_turno_tlv_pag();
		if (count($turno) > 0) {
			$turno_id = $turno[0]["id"];
			$cc_id = $turno[0]["cc_id"];

			if ((int) $turno_id > 0) {
				$query_1 = "SELECT tra.id
							FROM tbl_televentas_clientes_transaccion tra
							WHERE tra.tipo_id = " . $tipo_id . " AND tra.cliente_id = " . $id_cliente . " 
							ORDER BY tra.id DESC
							LIMIT 1";
				$list_query = $mysqli->query($query_1);
				if ($mysqli->error) {
					$result["query_1_error"] = $mysqli->error;
				}
				$list_1 = array();
				while ($li = $list_query->fetch_assoc()) {
					$list_1[] = $li;
				}
				if (count($list_1) != 0){
					$result = sec_tlv_pag_rollback_transaccion($id_cliente, (int)$list_1[0]["id"], $usuario_id, $turno_id, $observacion);
				}else{
					$result["http_code"] = 400;
					$result["status"] = "No hay transacciones a revertir";
					$result["result"] = $list_query;
				}
			} else {
				$result["http_code"] = 400;
				$result["status"] = "Ocurrió un error al consultar el turno.";
				$result["result"] = $turno;
			}
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Debe abrir un turno.";
			$result["result"] = $turno;
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Sesión perdida. Por favor vuelva a iniciar sesión.";
	}
}

function sec_tlv_pag_rollback_transaccion($cliente_id, $transaccion_id, $usuario_id, $turno_id, $observacion){
	global $mysqli;

	$respuesta = 0;

	$query = "
		SELECT
			cbt.transaccion_id,
			cbt.tipo_balance_id,
			cbt.monto,
			ct.tipo_id,
			ctt.rollback_tipo_id,
			ctt.operacion,
			cb.balance
		FROM
			tbl_televentas_clientes_balance_transaccion cbt
			JOIN tbl_televentas_clientes_transaccion ct ON ct.id = cbt.transaccion_id
			JOIN tbl_televentas_clientes_tipo_transaccion ctt ON ctt.id = ct.tipo_id 
			JOIN tbl_televentas_clientes_balance cb ON cb.tipo_balance_id = cbt.tipo_balance_id and cb.cliente_id = cbt.cliente_id
		WHERE
			cbt.transaccion_id = $transaccion_id
			AND ct.estado = 1
			#AND TIMESTAMPDIFF(HOUR,ct.created_at,now()) <= 24 
		ORDER BY ctt.id asc
			
		";
	$list_query = $mysqli->query($query);
	$list_transacciones = array();

	$balance_principal=0;
	$rollback_transaccion_id = 0;
	$continue = 0;

	while ($li = $list_query->fetch_assoc()) { 
		$list_transacciones[] = $li;
		if((float)$li['balance']<(float)$li['monto']){
			$result["http_code"] = 400;
			$result["status"] = "El balance es menor al monto a retornar.";
			$result["balance"] = $li['balance'];
			$result["monto"] = $li['monto'];
			$result["tipo_id"] = $li['tipo_id'];
			$continue = 1;
		}
	}

	$li = $list_transacciones[0];

	if((int)$continue===0){
		if((int)$li['tipo_balance_id']===1){
			if($li['operacion']==='credit') { // credit + -> -
				$balance_principal = $li['balance'] - $li['monto'];
			}
			if($li['operacion']==='debit') { // debit - -> +
				$balance_principal = $li['balance'] + $li['monto'];
			}

			$insert_command = "
				INSERT INTO tbl_televentas_clientes_transaccion (
					tipo_id,
					cliente_id,
					user_id,
					turno_id,
					monto,
					nuevo_balance,
					estado,
					transaccion_id,
					created_at
				) VALUES (
					" . $li['rollback_tipo_id'] . ",
					" . $cliente_id . ",
					" . $usuario_id . ",
					" . $turno_id . ",
					" . $li['monto'] . ",
					" . $balance_principal . ",
					1,
					" . $transaccion_id . ",
					now()
				)";
			$mysqli->query($insert_command);

			$query = "
					UPDATE tbl_televentas_clientes_transaccion 
					SET 
						estado = '3', 
						update_user_id = " . $usuario_id . ", 
						observacion_supervisor = '" . $observacion . "', 
						update_user_at = now() 
					WHERE id = '" . $transaccion_id . "'
				";
			$mysqli->query($query);

			$query_3 = "SELECT id FROM tbl_televentas_clientes_transaccion  ";
			$query_3 .= " WHERE tipo_id=" . $li['rollback_tipo_id'] . " ";
			$query_3 .= " AND user_id='" . $usuario_id . "' ";
			$query_3 .= " AND turno_id='" . $turno_id . "' ";
			$query_3 .= " AND cliente_id='" . $cliente_id . "' ";
			$query_3 .= " AND transaccion_id='" . $transaccion_id . "' ";
			$query_3 .= " AND estado = 1";
			$list_query = $mysqli->query($query_3);
			$list_transaccion2 = array();
			while ($li2 = $list_query->fetch_assoc()) {
				$list_transaccion2[] = $li2;
			}
			if (count($list_transaccion2) === 1) {
				$rollback_transaccion_id = $list_transaccion2[0]['id'];
			} elseif (count($list_transaccion2) > 1) {
				$result["http_code"] = 400;
				$result["status"] = "Ya existe un rollback registrado. ".count($list_transaccion2);
				$result["list_transacciones"] = $list_transacciones;
				$result["list_transaccion2"] = $list_transaccion2;
				$continue = 1;
			} else {
				$result["http_code"] = 400;
				$result["status"] = "Ocurrió un error al registrar el rollback.";
				$result["list_transacciones"] = $list_transacciones;
				$result["list_transaccion2"] = $list_transaccion2;
				$continue = 1;
			}
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al registrar el rollback. 2";
			$continue = 1;
		}
	}

	if((int)$continue===0){

		if (count($list_transacciones) > 0) {
			foreach ($list_transacciones as $tra) {

				if((float)$tra['balance']<(float)$tra['monto']){
					$result["http_code"] = 400;
					$result["status"] = "El balance es menor al monto a retornar. 2";
					$result["balance"] = $tra['balance'];
					$result["monto"] = $tra['monto'];
					$result["tipo_id"] = $tra['tipo_balance_id'];
					$continue = 1;
				}

				if((int)$continue===0){
					$temp_tipo_balance_id = $tra['tipo_balance_id'];
					$temp_tipo_id = $tra['tipo_id'];
					$temp_rollback_tipo_id = $tra['rollback_tipo_id'];
					$temp_balance_actual = $tra['balance'];


					if($tra['operacion']==='credit') { // credit + -> -
						$temp_balance_nuevo = $tra['balance'] - $tra['monto'];
					}
					if($tra['operacion']==='debit') { // debit - -> +
						$temp_balance_nuevo = $tra['balance'] + $tra['monto'];
					}

					sec_tlv_pag_query_tbl_televentas_clientes_balance('update', $cliente_id, $temp_tipo_balance_id, $temp_balance_nuevo);

					sec_tlv_pag_query_tbl_televentas_clientes_balance_transaccion('insert', $rollback_transaccion_id, 
						$cliente_id, $temp_tipo_balance_id, $temp_balance_actual, $tra['monto'], $temp_balance_nuevo);

				}
			}
		} else {
			$result["http_code"] = 400;
			$result["status"] = "No se puede realizar el rollback.";
			$continue = 1;
		}
	}


	if((int)$continue===0){
		$result["http_code"] = 200;
		$result["status"] = "Ok.";
	}

	return $result;
}

function sec_tlv_pag_rollback_transaccion_retiro($cliente_id, $transaccion_id, $usuario_id, $turno_id, $observacion,$rollback_transaccion_id){
	global $mysqli;

	$respuesta = 0;

	$query = "
		SELECT
			cbt.transaccion_id,
			cbt.tipo_balance_id,
			cbt.monto,
			ct.tipo_id,
			ctt.rollback_tipo_id,
			ctt.operacion,
			cb.balance
		FROM
			tbl_televentas_clientes_balance_transaccion cbt
			JOIN tbl_televentas_clientes_transaccion ct ON ct.id = cbt.transaccion_id
			JOIN tbl_televentas_clientes_tipo_transaccion ctt ON ctt.id = ct.tipo_id 
			JOIN tbl_televentas_clientes_balance cb ON cb.tipo_balance_id = cbt.tipo_balance_id and cb.cliente_id = cbt.cliente_id
		WHERE
			cbt.transaccion_id = $transaccion_id
			#AND ct.estado = 1
			#AND TIMESTAMPDIFF(HOUR,ct.created_at,now()) <= 24 
		ORDER BY ctt.id asc
			
		";
	$list_query = $mysqli->query($query);
	$list_transacciones = array();

	$balance_principal=0;
	//$rollback_transaccion_id = 0;
	$continue = 0;

	while ($li = $list_query->fetch_assoc()) { 
		$list_transacciones[] = $li;
		/*if((float)$li['balance']<=(float)$li['monto']){
			$result["http_code"] = 400;
			$result["status"] = "El balance es menor al monto a retornar.";
			$result["balance"] = $li['balance'];
			$result["monto"] = $li['monto'];
			$result["tipo_id"] = $li['tipo_id'];
			$continue = 1;
		}*/
	}

	$li = $list_transacciones[0];

	/*if((int)$continue===0){
		if((int)$li['tipo_balance_id']===1){
			if($li['operacion']==='credit') { // credit + -> -
				$balance_principal = $li['balance'] - $li['monto'];
			}
			if($li['operacion']==='debit') { // debit - -> +
				$balance_principal = $li['balance'] + $li['monto'];
			}

			$insert_command = "
				INSERT INTO tbl_televentas_clientes_transaccion (
					tipo_id,
					cliente_id,
					user_id,
					turno_id,
					monto,
					nuevo_balance,
					estado,
					transaccion_id,
					created_at
				) VALUES (
					" . $li['rollback_tipo_id'] . ",
					" . $cliente_id . ",
					" . $usuario_id . ",
					" . $turno_id . ",
					" . $li['monto'] . ",
					" . $balance_principal . ",
					1,
					" . $transaccion_id . ",
					now()
				)";
			$mysqli->query($insert_command);

			$query = "
					UPDATE tbl_televentas_clientes_transaccion 
					SET 
						estado = '3', 
						update_user_id = " . $usuario_id . ", 
						observacion_supervisor = '" . $observacion . "', 
						update_user_at = now() 
					WHERE id = '" . $transaccion_id . "'
				";
			$mysqli->query($query);

			$query_3 = "SELECT id FROM tbl_televentas_clientes_transaccion  ";
			$query_3 .= " WHERE tipo_id=" . $li['rollback_tipo_id'] . " ";
			$query_3 .= " AND user_id='" . $usuario_id . "' ";
			$query_3 .= " AND turno_id='" . $turno_id . "' ";
			$query_3 .= " AND cliente_id='" . $cliente_id . "' ";
			$query_3 .= " AND transaccion_id='" . $transaccion_id . "' ";
			$query_3 .= " AND estado = 1";
			$list_query = $mysqli->query($query_3);
			$list_transaccion2 = array();
			while ($li2 = $list_query->fetch_assoc()) {
				$list_transaccion2[] = $li2;
			}
			if (count($list_transaccion2) === 1) {
				$rollback_transaccion_id = $list_transaccion2[0]['id'];
			} elseif (count($list_transaccion2) > 1) {
				$result["http_code"] = 400;
				$result["status"] = "Ya existe un rollback registrado. ".count($list_transaccion2);
				$result["list_transacciones"] = $list_transacciones;
				$result["list_transaccion2"] = $list_transaccion2;
				$continue = 1;
			} else {
				$result["http_code"] = 400;
				$result["status"] = "Ocurrió un error al registrar el rollback.";
				$result["list_transacciones"] = $list_transacciones;
				$result["list_transaccion2"] = $list_transaccion2;
				$continue = 1;
			}
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al registrar el rollback. 2";
			$continue = 1;
		}
	}*/

	if((int)$continue===0){

		if (count($list_transacciones) > 0) {
			foreach ($list_transacciones as $tra) {

				/*if((float)$tra['balance']<(float)$tra['monto']){
					$result["http_code"] = 400;
					$result["status"] = "El balance es menor al monto a retornar. 2";
					$result["balance"] = $tra['balance'];
					$result["monto"] = $tra['monto'];
					$result["tipo_id"] = $tra['tipo_balance_id'];
					$continue = 1;
				}*/

				if((int)$continue===0){
					$temp_tipo_balance_id = $tra['tipo_balance_id'];
					$temp_tipo_id = $tra['tipo_id'];
					$temp_rollback_tipo_id = $tra['rollback_tipo_id'];
					$temp_balance_actual = $tra['balance'];


					if($tra['operacion']==='credit') { // credit + -> -
						$temp_balance_nuevo = $tra['balance'] - $tra['monto'];
					}
					if($tra['operacion']==='debit') { // debit - -> +
						$temp_balance_nuevo = $tra['balance'] + $tra['monto'];
					}

					sec_tlv_pag_query_tbl_televentas_clientes_balance('update', $cliente_id, $temp_tipo_balance_id, $temp_balance_nuevo);

					sec_tlv_pag_query_tbl_televentas_clientes_balance_transaccion('insert', $rollback_transaccion_id, 
						$cliente_id, $temp_tipo_balance_id, $temp_balance_actual, $tra['monto'], $temp_balance_nuevo);

				}
			}
		} else {
			$result["http_code"] = 400;
			$result["status"] = "No se puede realizar el rollback.";
			$continue = 1;
		}
	}


	if((int)$continue===0){
		$result["http_code"] = 200;
		$result["status"] = "Ok.";
	}

	return $result;
}


function sec_tlv_pag_query_tbl_televentas_clientes_balance($action, $cliente_id, $tipo_id, $balance){
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

function sec_tlv_pag_query_tbl_televentas_clientes_balance_transaccion($action, $transaccion_id, $cliente_id, $tipo_balance_id, $balance_actual, $monto, $balance_nuevo){
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
// GUARDAR EDICION REGISTRO
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "sec_tlv_pag_guardar_cambios_registro") {
	include("function_replace_invalid_caracters.php");
	$id_transaccion      = $_POST["id_transaccion"];
	$tipo_operacion      = $_POST["tipo_operacion"];
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
		tra.transaccion_id = $id_transaccion 
		AND tra.estado = 2
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
			$path = "/var/www/html/files_bucket/retiros/";
			if($tipo_operacion == "PROPINA"){
				$transaccion_id = $id_transaccion;
				$path = "/var/www/html/files_bucket/propinas/";
			}else{
				$transaccion_id = $list_transaccion[0]["id"];	
				$path = "/var/www/html/files_bucket/retiros/";
			}

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
				$result["status"] = "No puede editar registros de más de $limite_dias días de antiguedad.";
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
						WHERE transaccion_id = " . $transaccion_id;
				$mysqli->query($cmd_image_update);

				//Agregar nueva imagen
				$filename = $_FILES['imagen_voucher']['tmp_name'];
				$filenametem = $_FILES['imagen_voucher']['name'];


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
						$imageLayer = sec_tlv_pag_resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);

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

					if($campo == "cuenta_id" || $campo == "cuenta_pago_id" || $campo == "cajero_cuenta_id"){
						$cmd_consulta = " 
							SELECT id, cuenta_id, cuenta_pago_id, cajero_cuenta_id 
							FROM tbl_televentas_clientes_transaccion 
							WHERE id = " . $transaccion_id;
						$list_cmd_cons = $mysqli->query($cmd_consulta);
						$list_cons = array();
						if ($mysqli->error) {
							$result["http_code"] = 400;
							$result["query"] = $mysqli->error;
							$result["status"] = "Ocurrió un error al consultar el estado de la transacción.";
							echo json_encode($result);exit();
						} else {
							while ($li = $list_cmd_cons->fetch_assoc()) {
								$list_cons[] = $li;
							}
							if (count($list_cons) == 0) {
								$result["http_code"] = 400;
								$result["status"] = "Hubo un error al consultar la transacción, intentar nuevamente, por favor.";
								echo json_encode($result);exit();
							} elseif (count($list_cons) == 1) {
								$valor_anterior = $list_cons[0][$campo];
							}
						}
					}

					$cmd_change_transaction = "
							UPDATE tbl_televentas_clientes_transaccion 
							SET 
								" . $campo . " = " . $valor_nuevo. "
							WHERE id = " . $transaccion_id . " OR id = " 
									.  $list_transaccion[0]["id"] 
									. " OR id = " . $id_transaccion;
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

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_tlv_pag_obtener_fecha_hora") {
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

if (isset($_GET["accion"]) && $_GET["accion"]==="sec_tlv_pag_listar_pagadores") {
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
	//$result["consulta_query"] = $query;
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
		//$result["http_code"] = 200;
		//$result = $list_registros;
	} else {
		//$result["http_code"] = 400;
		//$result['value'] = '';
        $result['label'] = 'No se encontraron coincidencias.';
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_tlv_pag_obtener_totales") {
	$busqueda_fecha_inicio = $_POST["fecha_inicio"];
	$busqueda_fecha_fin    = $_POST["fecha_fin"];
	$busqueda_estado       = $_POST["estado"];
	$busqueda_validador    = $_POST["validador"];
	$busqueda_razon	       = $_POST["razon"];
	$busqueda_tipo	       = $_POST["tipo"];
	$busqueda_validador_supervisor = $_POST["validador_supervisor"];
	$busqueda_cuenta       = "";
	$tipo_saldo            = $_POST["tipo_saldo"];
	if (isset($_POST["cuenta"]) && $_POST["cuenta"] !== '') {
		$busqueda_cuenta = implode(",",$_POST["cuenta"]) ;
	}
	$busqueda_cuenta_pago  = "";
	if (isset($_POST["cuenta_pago"]) && $_POST["cuenta_pago"] !== '') {
		$busqueda_cuenta_pago = implode(",",$_POST["cuenta_pago"]) ;
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

	$where_tipo = ' 
		(
			(tra.tipo_id IN (9,21,28))
			-- (tra.tipo_id IN (9,21,28) and IFNULL(tra.caja_vip, 0) = 0)
            -- OR (tra.tipo_id IN (11,24,29) and IFNULL(tra.caja_vip, 0) = 1)
         )
	';
	if((int) $busqueda_tipo > 0){
		if($busqueda_tipo == 1){ // Retiros
			//$where_tipo = ' tra.tipo_id = 9 AND IFNULL(tra.tipo_operacion, 0) in (0, 1) ';
			$where_tipo = '
				(
					(tra.tipo_id = 9)
					-- (tra.tipo_id = 9 and IFNULL(tra.caja_vip, 0) = 0)
		            -- OR (tra.tipo_id = 11 and IFNULL(tra.caja_vip, 0) = 1)
		         )
			';
		}else if($busqueda_tipo == 2){ // Devoluciones
			//$where_tipo = ' tra.tipo_id = 28 ';
			$where_tipo = '
				(
					(tra.tipo_id = 28)
					-- (tra.tipo_id = 28 and IFNULL(tra.caja_vip, 0) = 0)
		            -- OR (tra.tipo_id = 29 and IFNULL(tra.caja_vip, 0) = 1)
		         )
			';
		}else if($busqueda_tipo == 3){ // Propinas
			//$where_tipo = ' tra.tipo_id = 21 ';
			$where_tipo = '
				(
					(tra.tipo_id = 21 and IFNULL(tra.caja_vip, 0) = 0)
		            -- OR (tra.tipo_id = 24 and IFNULL(tra.caja_vip, 0) = 1)
		         )
			';
		}
	}

	$where_estado = '';
	if ($busqueda_estado !== '0') {
		$where_estado = ' AND tra.estado = ' . $busqueda_estado . ' ';
	} else {
		$where_estado = ' ';
		//$where_estado = ' AND tra.estado in (1,2,3) ';
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
		/*$where_cuenta = "
			AND cuen.id IN (
							SELECT
								uca.cuenta_apt_id
							FROM
								tbl_usuario_cuentas_apt AS uca
							WHERE uca.usuario_id = $usuario_id AND uca.estado = 1
							GROUP BY uca.cuenta_apt_id
			) 
		";*/

		/*$where_cuenta = "
			AND cuen.id IN (
							SELECT
								uca.cuenta_apt_id
							FROM
								tbl_usuario_cuentas_apt AS uca
							WHERE uca.usuario_id = $usuario_id AND uca.estado = 1
							GROUP BY uca.cuenta_apt_id
			) 
		";*/
	}
	$where_cuenta_pago = "";
	if ($busqueda_cuenta_pago !== '') {
		if($busqueda_estado == 2){
			$where_cuenta_pago = " AND (tra.cuenta_pago_id IN ($busqueda_cuenta_pago) ) ";
		}
	}



	$where_razon = '';
	if ((int) $busqueda_razon > 0) {
		if($busqueda_razon == 1){
			$where_razon = ' AND IFNULL(tra.id_operacion_retiro, 0) in (0, 1) ';
		}else{
			$where_razon = ' AND IFNULL(tra.id_operacion_retiro, 0) = ' . $busqueda_razon . ' ';
		}
	}

	

	$where_validador_supervisor = '';
	if($busqueda_validador_supervisor > 0){
		$where_validador_supervisor = ' AND IFNULL(tra.user_valid_id, 0) = ' . $busqueda_validador_supervisor;
	}

	$where_tipo_saldo = '';
	if($tipo_saldo != '0'){
		if($tipo_saldo == '1'){
			$where_tipo_saldo = ' AND IFNULL(ce.id, 0) = 0';
		}else if($tipo_saldo == '2'){
			$where_tipo_saldo = ' AND IFNULL(ce.id, 0) > 0';
		}
	}
	$query_1 = "
		SELECT 
			SUM(IFNULL(tra.monto,0)) monto,
			SUM(IFNULL(tra.comision_monto,0)) comision_monto,
			COUNT(*) cant
			FROM
			tbl_televentas_clientes_transaccion tra
			INNER JOIN tbl_televentas_clientes_tipo_transaccion ttra ON ttra.id = tra.tipo_id
			LEFT JOIN tbl_televentas_cajeros_cuenta tcc ON tra.cajero_cuenta_id = tcc.id
			LEFT JOIN tbl_usuarios usu ON usu.id = tra.user_id
			LEFT JOIN tbl_televentas_clientes cli ON cli.id = tra.cliente_id
			LEFT JOIN tbl_usuarios val ON val.id = tra.update_user_id
			LEFT JOIN tbl_personal_apt apt ON apt.id = val.personal_id
			LEFT JOIN tbl_televentas_clientes_cuenta tc ON tra.cuenta_id = tc.id
			LEFT JOIN tbl_bancos cuen ON tc.banco_id = cuen.id
			OR tcc.banco_id = cuen.id
			LEFT JOIN tbl_locales loc ON loc.cc_id = tra.cc_id 
			LEFT JOIN tbl_televentas_estado_solicitud_retiro stra ON tra.estado = stra.id
            LEFT JOIN tbl_televentas_tipo_rechazo_solicitud_retiro trs ON tra.tipo_rechazo_id = trs.id
            LEFT JOIN tbl_televentas_cuentas_pago_retiro cpr ON tra.cuenta_pago_id = cpr.id
            LEFT JOIN tbl_televentas_tipo_motivo_devolucion md ON tra.id_motivo_dev = md.id
            LEFT JOIN tbl_usuarios aprov ON aprov.id = tra.user_valid_id
			LEFT JOIN tbl_personal_apt apt_aprov ON apt_aprov.id = aprov.personal_id
			LEFT JOIN tbl_televentas_transaccion_color tlvc ON tlvc.id_transaccion = tra.id 
		WHERE
		$where_tipo
		$where_users_test
		$where_fecha_inicio 
		$where_fecha_fin 
		$where_estado 
		$where_validador 
		$where_cuenta 
		$where_razon
		$where_validador_supervisor
		$where_cuenta_pago
		ORDER BY tra.id ASC ";
	$result["query_totales"] = $query_1;
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

echo json_encode($result);