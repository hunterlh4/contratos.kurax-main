<?php
$result = array();
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
if (isset($_POST["accion"]) && $_POST["accion"]==="SecRepCor_listar_registros") {
	$usuario_id = $login ? $login['id'] : 0;

	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$busqueda_fecha_inicio     = $_POST["fecha_inicio"];
	$busqueda_fecha_fin        = $_POST["fecha_fin"];

	$where_fecha = " WHERE DATE(tra.created_at) >= '".$busqueda_fecha_inicio."' AND DATE(tra.created_at) <= '".$busqueda_fecha_fin."'";

	$where_users_test="";
	if((int)$area_id !== 6) {// diferente de sistemas
		$where_users_test="	
			AND IFNULL(tra.web_id, '') not in ('3333200', '71938219') 
			AND tra.user_id not in (1, 249, 250, 2572, 3028) 
		";
	}

	$nombre_busqueda = "";
	if(isset($_POST["search"]["value"])) {
		$busqueda_nombre = $_POST["search"]["value"];
		if ($busqueda_nombre !="") {
			$nombre_busqueda = '
			AND (
			CONCAT(IFNULL(cli.nombre, ""), " " , IFNULL(cli.apellido_paterno, "") , " ", IFNULL(cli.apellido_materno,"") ) LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.nombre LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.apellido_paterno LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.apellido_materno LIKE "%'.$_POST["search"]["value"].'%"
			OR tm.created_at LIKE "%'.$_POST["search"]["value"].'%"
			OR tra.created_at LIKE "%'.$_POST["search"]["value"].'%"			
			OR u.usuario LIKE "%'.$_POST["search"]["value"].'%" ';

			$nombre_busqueda .= ')';
		}
	}

	$order = "";
	$column = array(
		1=>"tm.id",
		2=>"tm.transaccion_id",
		3=>"tm.created_at",
		4=>"tra.created_at",
		5=>"u.usuario",
		6=>"cli.nombre",
		7=>"tip_c.descripcion",
		);
	if(isset($_POST["order"])) {
		if (array_key_exists($_POST['order']['0']['column'],$column)) {
			$order = 'ORDER BY '.$column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' ';
		} else {
			$order = 'ORDER BY tm.id ASC';
		}
	} else {
		$order = 'ORDER BY tm.id ASC';
	}
	$limit = "";
	if(isset($_POST["length"])) {
		if($_POST["length"] != -1) {
			$limit = 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
		}	
	}

	// QUERY
	$query_1 ="
		SELECT 
			tm.id,
		    tm.transaccion_id,
		    tm.created_at fecha_modificacion,
		    tra.created_at fecha_creacion,
		    UPPER(IFNULL(u.usuario,'')) usuario,
		    CASE WHEN tra.tipo_id = 1 THEN 'MODULO DE VALIDACIÓN' ELSE 'MODULO DE PAGADOR' END modulo,
		    CONCAT(IFNULL(cli.nombre, ''), ' ' , IFNULL(cli.apellido_paterno, '') , ' ', IFNULL(cli.apellido_materno,'') ) cliente,
		    UPPER(IFNULL(u_valid.usuario,'')) promotor,
		    UPPER(IFNULL(loc.nombre, ce_ssql.nombre)) caja,
		    CASE WHEN tra.tipo_id = 1 THEN UPPER(IFNULL(cuen.cuenta_descripcion, '')) ELSE UPPER(IFNULL(cpr.nombre,'')) END cuenta,
		    tra.monto monto, 
		    CASE 
			WHEN tra.tipo_id in (1,21) THEN 
		    		IFNULL(CONCAT( apt.nombre, ' ', IFNULL( apt.apellido_paterno, '' ), ' ', IFNULL( apt.apellido_materno, '' ) ), '')
		    	ELSE 
		    		IFNULL(CONCAT( apt_aprov.nombre, ' ',IFNULL( apt_aprov.apellido_paterno, '' ), ' ',IFNULL( apt_aprov.apellido_materno, '' ) ), '')
		    END valid,
		    IFNULL(IF((
				SELECT id FROM tbl_televentas_clientes_transaccion_modificaciones tmm
				WHERE tmm.transaccion_id = tm.transaccion_id AND tmm.campo_name = 'id_tipo_constancia' AND tra.tipo_id = 1 AND tmm.status = 1
            ) > 0, 1, '' ), '') cm_tipo_constancia,
		    IFNULL(IF((
				SELECT id FROM tbl_televentas_clientes_transaccion_modificaciones tmm
				WHERE tmm.transaccion_id = tm.transaccion_id AND tmm.campo_name = 'cuenta_id' AND tra.tipo_id = 1 AND tmm.status = 1
            ) > 0, 1, '' ), '') cm_cuenta_deposito,
            IFNULL(IF((
				SELECT id FROM tbl_televentas_transaccion_audit ta
				WHERE ta.id_transaccion = tm.transaccion_id
                ORDER BY ta.id DESC
                LIMIT 1
            ) > 0, 1, ''),
            
            '') cm_fecha_abono,
            IFNULL(IF((
				SELECT id FROM tbl_televentas_clientes_transaccion_modificaciones tmm
				WHERE tmm.transaccion_id = tm.transaccion_id AND tmm.campo_name = 'num_operacion' AND tmm.status = 1
				) > 0 and tra.tipo_id = 1, 1, ''), '') cm_num_operacion,
				IFNULL(IF((
					SELECT id FROM tbl_televentas_clientes_transaccion_modificaciones tmm
					WHERE tmm.transaccion_id = tm.transaccion_id AND tmm.campo_name = 'num_operacion' AND tmm.status = 1
				) > 0 and tra.tipo_id not in (1), 1, ''), '') cm_num_operacion_pag,
            IFNULL(IF((
				SELECT id FROM tbl_televentas_clientes_transaccion_modificaciones tmm
				WHERE tmm.transaccion_id = tm.transaccion_id AND tmm.campo_name = 'cuenta_id' AND tra.tipo_id != 1 AND tmm.status = 1
            ) > 0, 1, ''), '') cm_cuenta_pagadora,
            IFNULL(IF((
				SELECT id FROM tbl_televentas_clientes_transaccion_modificaciones tmm
				WHERE tmm.transaccion_id = tm.transaccion_id AND tmm.campo_name = 'cuenta_pago_id' AND tmm.status = 1
            ) > 0, 1, ''), '') cm_banco_pago,
		    IFNULL(SUM(IF(tm.status = 1, 1, 0)), 0) cant_changes,
		    tra.tipo_id
		FROM 
			tbl_televentas_clientes_transaccion_modificaciones tm
		    INNER JOIN tbl_televentas_clientes_transaccion tra ON tra.id = tm.transaccion_id
		    LEFT JOIN tbl_televentas_clientes_transaccion tra_2 ON tra.transaccion_id = tra_2.id AND tra.tipo_id NOT IN (1)
		    INNER JOIN tbl_usuarios u ON tm.user_id = u.id
		    #promotor
		    INNER JOIN tbl_usuarios u_valid ON tra.user_id = u_valid.id
		    #cliente
		    INNER JOIN tbl_televentas_clientes cli ON tra.cliente_id = cli.id
		    #caja / caja eliminada
		    LEFT JOIN tbl_caja caj ON caj.id = tra.turno_id
			LEFT JOIN tbl_local_cajas loc_caj ON loc_caj.id = caj.local_caja_id
			LEFT JOIN tbl_locales loc ON loc.id = loc_caj.local_id
			LEFT JOIN tbl_caja_eliminados ce on ce.id = tra.turno_id 
			LEFT JOIN tbl_local_cajas ce_sqlc ON ce_sqlc.id = ce.local_caja_id 
			LEFT JOIN tbl_locales ce_ssql ON ce_ssql.id = ce_sqlc.local_id 
			#cuenta deposito
			LEFT JOIN tbl_televentas_cuentas tc ON tc.cuenta_apt_id = tra.cuenta_id
			LEFT JOIN tbl_cuentas_apt cuen ON cuen.id = tc.cuenta_apt_id
			#banco de pago
			LEFT JOIN tbl_televentas_cuentas_pago_retiro cpr ON tra.cuenta_pago_id = cpr.id

			LEFT JOIN tbl_usuarios val ON val.id = tra.update_user_id
			LEFT JOIN tbl_personal_apt apt ON apt.id = val.personal_id

			LEFT JOIN tbl_usuarios aprov ON aprov.id = tra_2.user_id
			LEFT JOIN tbl_personal_apt apt_aprov ON apt_aprov.id = aprov.personal_id

			LEFT JOIN tbl_televentas_tipo_constancia tip_c ON tra.id_tipo_constancia = tip_c.id 
		". $where_fecha ."
		". $where_users_test ."
		GROUP BY
			tm.transaccion_id
		". $order ."
		". $limit;

	 //$result["consulta_query"] = $query_1;
	 $list_query=$mysqli->query($query_1);
	 $list_transaccion=array();
	 while ($li=$list_query->fetch_assoc()) {
		 $list_transaccion[]=$li;
	 }
 
	 if($mysqli->error){
		 $result["consulta_error"] = $mysqli->error;
	 }
 
	 if(count($list_transaccion)==0){
		 $result["http_code"] = 400;
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

if (isset($_POST["accion"]) && $_POST["accion"]==="secRepCor_obtener_detalle_transaccion_deposito") {
	$usuario_id = $login ? $login['id'] : 0;
	$transaccion_id            = $_POST["transaccion_id"];

	// QUERY
	$query_1 ="
		SELECT 
			tra.id,
		    tra.tipo_id,
		    ttra.nombre,
		    ca.cuenta_descripcion cuenta,
		    IFNULL(tra.monto,0) monto,
		    IFNULL(tra.bono_monto,0) bono_monto,
		    IFNULL(tra.bono_id,0) bono_id,
		    tra.num_operacion,
		    IFNULL(tra.registro_deposito,'') registro_deposito,
		    IFNULL(tra.monto_deposito,0) monto_deposito,
		    IFNULL(tra.comision_monto,0) comision_monto,
		    IFNULL(tra.total_recarga,0) total_recarga,
		    IFNULL(tra.observacion_cajero,'') observacion_cajero,
		    IFNULL(tra.observacion_validador,'') observacion_validador,
		    IFNULL(ta.id, 0) id,
		    IFNULL(ta.archivo, '') archivo,
		    tra.created_at fecha_hora,
		    IFNULL(tip_c.descripcion, '') tipo_constancia
		FROM 
			tbl_televentas_clientes_transaccion tra
		INNER JOIN tbl_televentas_clientes_tipo_transaccion ttra ON tra.tipo_id = ttra.id
		LEFT JOIN tbl_televentas_transaccion_archivos ta ON ta.transaccion_id = tra.id AND ta.estado = 1
		LEFT JOIN tbl_televentas_cuentas tta ON tra.cuenta_id = tta.id
		LEFT JOIN tbl_cuentas_apt ca ON tta.cuenta_apt_id = ca.id
		LEFT JOIN tbl_televentas_tipo_constancia tip_c ON tra.id_tipo_constancia = tip_c.id
		WHERE tra.id = " . $transaccion_id; 

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
		$result["status"] ="No hay registros.";
		$result["result"] =$list_transaccion;
	} elseif(count($list_transaccion)>0){
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_transaccion;
	} else {
		$result["http_code"] = 400;
		$result["status"] ="Ocurrió un error al consultar los registros.";
		$result["result"] =$list_transaccion;
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="secRepCor_obtener_detalle_transaccion_pago") {
	$usuario_id = $login ? $login['id'] : 0;
	$transaccion_id            = $_POST["transaccion_id"];
	$tipo_id                   = $_POST["tipo_id"];

	if((int)$tipo_id == 21){
		$where_trans = " tra.transaccion_id = " . $transaccion_id;
	}else if((int)$tipo_id == 11 || (int)$tipo_id == 29){
		$where_trans = " tra.id = " . $transaccion_id;
	}

	$query_1 ="
		SELECT 
			tra.id,	
			tra.tipo_id,
			tra.cliente_id,
			tra.created_at fecha_hora_registro,
			tra.registro_deposito fecha_abono,
			usu.usuario AS cajero,
			IFNULL(CONCAT( cli.nombre, ' ', IFNULL( cli.apellido_paterno, '' ), ' ', IFNULL( cli.apellido_materno, '' ) ), '') AS cliente,
			stra.descripcion AS estado,
			tra.estado AS estado_id,
			IF(tra.tipo_id IN (9,11,28), IFNULL(tc.banco_id, ''), IFNULL(tcc.banco_id, '')) cuenta_id,
			IFNULL(cuen.nombre, '') AS cuenta,
			IF(tra.tipo_id IN (9,11,28), IFNULL(tc.cuenta_num, ''), IFNULL(tcc.cuenta_num, '')) cuenta_num,
			IF(tra.tipo_id IN (9,11,28), IFNULL(tc.cci, ''), IFNULL(tcc.cci, '')) cci,
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
            CASE WHEN IFNULL(tra.id_operacion_retiro,0) IN (0, 1) THEN 'TELESERVICIOS' ELSE 'TIENDA' end razon,
            ifnull(trs.afect_balance,0) afect_balance,
            CASE WHEN tra.tipo_id IN (9,11) and IFNULL(tra.tipo_operacion,0) = 1 THEN 'PAGO' 
			WHEN tra.tipo_id IN (28,29) and IFNULL(tra.tipo_operacion,0) = 2 THEN 'DEVOLUCIÓN'
			WHEN tra.tipo_id IN (21,24) THEN 'PROPINA'
            	ELSE 'PAGO'
            END tipo_operacion,
            IFNULL(cpr.nombre,'') banco_pago,
            IFNULL(tra.id_motivo_dev, 0) id_motivo_dev,
            IFNULL(md.descripcion, '') motivo_devolucion,
			IFNULL(ttra.nombre, '') nombre_trans,
            IFNULL(CONCAT( apt_aprov.nombre, ' ',IFNULL( apt_aprov.apellido_paterno, '' ), ' ',IFNULL( apt_aprov.apellido_materno, '' ) ), '') AS validado_por,
		    IFNULL(ta.id, 0) id_archivo,
		    IFNULL(ta.archivo, '') archivo,
            IFNULL(ta2.id, 0) id_archivo_2,
		    IFNULL(ta2.archivo, '') archivo_2
			FROM
			tbl_televentas_clientes_transaccion tra
			INNER JOIN tbl_televentas_clientes_tipo_transaccion ttra ON ttra.id = tra.tipo_id
			LEFT JOIN tbl_televentas_cajeros_cuenta tcc ON tra.cajero_cuenta_id = tcc.id
			LEFT JOIN tbl_usuarios usu ON usu.id = tra.user_id
			LEFT JOIN tbl_televentas_clientes cli ON cli.id = tra.cliente_id
			LEFT JOIN tbl_usuarios val ON val.id = tra.update_user_id
			LEFT JOIN tbl_personal_apt apt ON apt.id = val.personal_id
			LEFT JOIN tbl_televentas_clientes_cuenta tc ON tra.cuenta_id = tc.id
			LEFT JOIN tbl_bancos cuen ON tc.banco_id = cuen.id OR tcc.banco_id = cuen.id
			LEFT JOIN tbl_locales loc ON loc.cc_id = tra.cc_id
			LEFT JOIN tbl_televentas_estado_solicitud_retiro stra ON tra.estado = stra.id
            LEFT JOIN tbl_televentas_tipo_rechazo_solicitud_retiro trs ON tra.tipo_rechazo_id = trs.id
            LEFT JOIN tbl_televentas_cuentas_pago_retiro cpr ON tra.cuenta_pago_id = cpr.id
            LEFT JOIN tbl_televentas_tipo_motivo_devolucion md ON tra.id_motivo_dev = md.id
            LEFT JOIN tbl_usuarios aprov ON aprov.id = tra.user_valid_id
			LEFT JOIN tbl_personal_apt apt_aprov ON apt_aprov.id = aprov.personal_id
			LEFT JOIN tbl_televentas_transaccion_color tlvc ON tlvc.id_transaccion = tra.id 
			LEFT JOIN tbl_televentas_transaccion_archivos ta ON ta.transaccion_id = tra.id AND ta.estado = 1
			LEFT JOIN tbl_televentas_transaccion_archivos ta2 ON ta2.transaccion_id = tra.transaccion_id AND ta2.estado = 1
		WHERE " . $where_trans;

	$result["query_rpt_cor"] = $query_1;
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
		$result["status"] ="No hay registros.";
		$result["result"] =$list_transaccion;
	} elseif(count($list_transaccion)>0){
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_transaccion;
	} else {
		$result["http_code"] = 400;
		$result["status"] ="Ocurrió un error al consultar los registros.";
		$result["result"] =$list_transaccion;
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="secRepCor_exportar_listado_xls") {
	global $mysqli;
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$busqueda_fecha_inicio     = $_POST["fecha_inicio"];
	$busqueda_fecha_fin        = $_POST["fecha_fin"];

	$where_fecha = " WHERE DATE(tra.created_at) >= '".$busqueda_fecha_inicio."' AND DATE(tra.created_at) <= '".$busqueda_fecha_fin."'";

	$where_users_test="";
	if((int)$area_id !== 6) {// diferente de sistemas
		$where_users_test="	
			AND IFNULL(tra.web_id, '') not in ('3333200', '71938219') 
			AND tra.user_id not in (1, 249, 250, 2572, 3028) 
		";
	}

	$query_1 ="
		SELECT 
		    tra.created_at fecha_creacion,
		    UPPER(IFNULL(u.usuario,'')) usuario,
		    CASE WHEN tra.tipo_id = 1 THEN 'MODULO DE VALIDACIÓN' ELSE 'MODULO DE PAGADOR' END modulo,
		    CONCAT(IFNULL(cli.nombre, ''), ' ' , IFNULL(cli.apellido_paterno, '') , ' ', IFNULL(cli.apellido_materno,'') ) cliente,
		    UPPER(IFNULL(u_valid.usuario,'')) promotor,
		    UPPER(IFNULL(loc.nombre, ce_ssql.nombre)) caja,
		    CASE WHEN tra.tipo_id = 1 THEN UPPER(IFNULL(cuen.cuenta_descripcion, '')) ELSE UPPER(IFNULL(cpr.nombre,'')) END cuenta,
		    tra.monto monto, 
		    CASE 
		    	WHEN tra.tipo_id = 1 THEN 
		    		IFNULL(CONCAT( apt.nombre, ' ', IFNULL( apt.apellido_paterno, '' ), ' ', IFNULL( apt.apellido_materno, '' ) ), '')
		    	ELSE 
		    		IFNULL(CONCAT( apt_aprov.nombre, ' ',IFNULL( apt_aprov.apellido_paterno, '' ), ' ',IFNULL( apt_aprov.apellido_materno, '' ) ), '')
		    END valid,
		    IFNULL(IF((
				SELECT id FROM tbl_televentas_clientes_transaccion_modificaciones tmm
				WHERE tmm.transaccion_id = tm.transaccion_id AND tmm.campo_name = 'id_tipo_constancia' AND tra.tipo_id = 1 AND tmm.status = 1
            ) > 0, 1, '' ), '') cm_tipo_constancia,
		    IFNULL(IF((
				SELECT id FROM tbl_televentas_clientes_transaccion_modificaciones tmm
				WHERE tmm.transaccion_id = tm.transaccion_id AND tmm.campo_name = 'cuenta_id' AND tra.tipo_id = 1 AND tmm.status = 1
            ) > 0, 1, '' ), '') cm_cuenta_deposito,
            IFNULL(IF((
				SELECT id FROM tbl_televentas_transaccion_audit ta
				WHERE ta.id_transaccion = tm.transaccion_id
                ORDER BY ta.id DESC
                LIMIT 1
            ) > 0, 1, ''),
            
            '') cm_fecha_abono,
            IFNULL(IF((
				SELECT id FROM tbl_televentas_clientes_transaccion_modificaciones tmm
				WHERE tmm.transaccion_id = tm.transaccion_id AND tmm.campo_name = 'num_operacion' AND tmm.status = 1
				) > 0 and tra.tipo_id = 1, 1, ''), '') cm_num_operacion,
				IFNULL(IF((
					SELECT id FROM tbl_televentas_clientes_transaccion_modificaciones tmm
					WHERE tmm.transaccion_id = tm.transaccion_id AND tmm.campo_name = 'num_operacion' AND tmm.status = 1
				) > 0 and tra.tipo_id not in (1), 1, ''), '') cm_num_operacion_pag,
            IFNULL(IF((
				SELECT id FROM tbl_televentas_clientes_transaccion_modificaciones tmm
				WHERE tmm.transaccion_id = tm.transaccion_id AND tmm.campo_name = 'cuenta_id' AND tra.tipo_id != 1 AND tmm.status = 1
            ) > 0, 1, ''), '') cm_cuenta_pagadora,
            IFNULL(IF((
				SELECT id FROM tbl_televentas_clientes_transaccion_modificaciones tmm
				WHERE tmm.transaccion_id = tm.transaccion_id AND tmm.campo_name = 'cuenta_pago_id' AND tmm.status = 1
            ) > 0, 1, ''), '') cm_banco_pago,
		    IFNULL(SUM(IF(tm.status = 1, 1, 0)), 0) cant_changes
		FROM 
			tbl_televentas_clientes_transaccion_modificaciones tm
		    INNER JOIN tbl_televentas_clientes_transaccion tra ON tra.id = tm.transaccion_id
		    LEFT JOIN tbl_televentas_clientes_transaccion tra_2 ON tra.transaccion_id = tra_2.id AND tra.tipo_id NOT IN (1)
		    INNER JOIN tbl_usuarios u ON tm.user_id = u.id
		    #promotor
		    INNER JOIN tbl_usuarios u_valid ON tra.user_id = u_valid.id
		    #cliente
		    INNER JOIN tbl_televentas_clientes cli ON tra.cliente_id = cli.id
		    #caja / caja eliminada
		    LEFT JOIN tbl_caja caj ON caj.id = tra.turno_id
			LEFT JOIN tbl_local_cajas loc_caj ON loc_caj.id = caj.local_caja_id
			LEFT JOIN tbl_locales loc ON loc.id = loc_caj.local_id
			LEFT JOIN tbl_caja_eliminados ce on ce.id = tra.turno_id 
			LEFT JOIN tbl_local_cajas ce_sqlc ON ce_sqlc.id = ce.local_caja_id 
			LEFT JOIN tbl_locales ce_ssql ON ce_ssql.id = ce_sqlc.local_id 
			#cuenta deposito
			LEFT JOIN tbl_televentas_cuentas tc ON tc.cuenta_apt_id = tra.cuenta_id
			LEFT JOIN tbl_cuentas_apt cuen ON cuen.id = tc.cuenta_apt_id
			#banco de pago
			LEFT JOIN tbl_televentas_cuentas_pago_retiro cpr ON tra.cuenta_pago_id = cpr.id

			LEFT JOIN tbl_usuarios val ON val.id = tra.update_user_id
			LEFT JOIN tbl_personal_apt apt ON apt.id = val.personal_id

			LEFT JOIN tbl_usuarios aprov ON aprov.id = tra_2.user_id
			LEFT JOIN tbl_personal_apt apt_aprov ON apt_aprov.id = aprov.personal_id

			LEFT JOIN tbl_televentas_tipo_constancia tip_c ON tra.id_tipo_constancia = tip_c.id
		". $where_fecha ."
		". $where_users_test ."
		GROUP BY
			tm.transaccion_id";

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
			"fecha_creacion" => "Fecha",
			"usuario" => "Modificador",
			"modulo" => "Módulo",
			"cliente" => "Cliente",
			"promotor" => "Usuario",
			"caja" => "Caja",
			"cuenta" => "Cuenta/Banco Pago",
			"monto" => "Monto",
			"valid" => "Validador/Pagador",
			"cm_tipo_constancia" => "CM-Tipo Constancia",
			"cm_cuenta_deposito" => "CM-Cuenta Depósito",
			"cm_fecha_abono" => "CM-Fecha Abono",
			"cm_num_operacion" => "CM-Num Op Validador",
			"cm_num_operacion_pag" => "CM-Num Op Pagador",
			"cm_cuenta_pagadora" => "CM-cuenta Pagadora",
			"cm_banco_pago" => "CM-Banco Pago",
			"cant_changes" => "Cantidad de Modificaciones"
		];
		array_unshift($result_data, $headers);

		ini_set('display_errors', 0);
		ini_set('display_startup_errors', 0);
		error_reporting(0);

		require_once '../phpexcel/classes/PHPExcel.php';
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getActiveSheet()->fromArray($result_data, null, 'A1');
		$date = new DateTime();
		$file_title = "reporte_correcciones_validacion_pagadores_" . $date->getTimestamp();

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

if (isset($_POST["accion"]) && $_POST["accion"]==="secRepCor_show_changes_transaction") {
	$usuario_id = $login ? $login['id'] : 0;
	$transaccion_id            = $_POST["transaccion_id"];

	$query_1 ="
		SELECT 
			tm.id,
		    IFNULL(tm.transaccion_id, 0) transaccion_id,
		    IFNULL(tm.campo_name, '') campo_name,
		    CASE
		    	WHEN tm.campo_name = 'archivo' THEN 'Imagen' 
		    	WHEN tm.campo_name = 'cuenta_id' THEN 'Cuenta' 
		    	WHEN tm.campo_name = 'cajero_cuenta_id' THEN 'Cuenta'
		    	WHEN tm.campo_name = 'cuenta_pago_id' THEN 'Banco de Pago'
		    	WHEN tm.campo_name = 'num_operacion' THEN 'Número de Operación'
		    	WHEN tm.campo_name = 'registro_deposito' THEN 'Fecha de Abono'
		    	WHEN tm.campo_name = 'id_tipo_constancia' THEN 'Tipo de Constancia'
		    END campo_name_obs,
		    IFNULL(tm.valor_original, '') valor_original,
		    IFNULL(tm.valor_nuevo, '') valor_nuevo,
		    IFNULL( tm.created_at, '') fecha_creacion,
		    tm.user_id,
		    IFNULL(u.usuario,'') usuario,
		    CASE
		    	WHEN tm.campo_name = 'archivo' THEN IFNULL(tm.valor_original, '') 
		    	WHEN tm.campo_name = 'cuenta_id' AND tra.tipo_id = 1 THEN  IFNULL(cuen.cuenta_descripcion, '')
		    	WHEN tm.campo_name = 'cuenta_id' AND tra.tipo_id NOT IN (1) THEN  IFNULL(bb.nombre, '')
		    	WHEN tm.campo_name = 'cajero_cuenta_id' THEN  IFNULL(tm.valor_original, '')
		    	WHEN tm.campo_name = 'cuenta_pago_id' THEN IFNULL(cpr.nombre, '')
		    	WHEN tm.campo_name = 'num_operacion' THEN IFNULL(tm.valor_original, '') 
		    	WHEN tm.campo_name = 'registro_deposito' THEN IFNULL(tm.valor_original, '') 
		    	WHEN tm.campo_name = 'id_tipo_constancia' THEN IFNULL(tip_c.descripcion, '') 
		    END 
		    AS valor_original_desc,
		    CASE
		    	WHEN tm.campo_name = 'archivo' THEN IFNULL(tm.valor_nuevo, '')
		    	WHEN tm.campo_name = 'cuenta_id' AND tra.tipo_id = 1 THEN  IFNULL(cuen_2.cuenta_descripcion, '')
		    	WHEN tm.campo_name = 'cuenta_id' AND tra.tipo_id NOT IN (1) THEN  IFNULL(bb_2.nombre, '')
		    	WHEN tm.campo_name = 'cajero_cuenta_id' THEN  IFNULL(tm.valor_nuevo, '')
		    	WHEN tm.campo_name = 'cuenta_pago_id' THEN IFNULL(cpr_2.nombre, '')
		    	WHEN tm.campo_name = 'num_operacion' THEN IFNULL(tm.valor_nuevo, '') 
		    	WHEN tm.campo_name = 'registro_deposito' THEN IFNULL(tm.valor_nuevo, '') 
		    	WHEN tm.campo_name = 'id_tipo_constancia' THEN IFNULL(tip_c_2.descripcion, '') 
		    END 
		    AS valor_nuevo_desc
		FROM
			tbl_televentas_clientes_transaccion_modificaciones tm
		INNER JOIN tbl_usuarios u ON tm.user_id = u.id
		LEFT JOIN tbl_televentas_clientes_transaccion tra ON tm.transaccion_id = tra.id

		LEFT JOIN tbl_televentas_cuentas tc ON tc.id = tm.valor_original AND tm.campo_name = 'cuenta_id' AND tra.tipo_id = 1
		LEFT JOIN tbl_cuentas_apt cuen ON cuen.id = tc.id

		LEFT JOIN tbl_televentas_cuentas tc_2 ON tc_2.id = tm.valor_nuevo AND tm.campo_name = 'cuenta_id' AND tra.tipo_id = 1
		LEFT JOIN tbl_cuentas_apt cuen_2 ON cuen_2.id = tc_2.id

		LEFT JOIN tbl_televentas_clientes_cuenta tcc ON tcc.id = tm.valor_original AND tm.campo_name = 'cuenta_id' AND tra.tipo_id NOT IN (1)
		LEFT JOIN tbl_bancos bb ON tcc.banco_id = bb.id

		LEFT JOIN tbl_televentas_clientes_cuenta tcc_2 ON tcc_2.id = tm.valor_nuevo AND tm.campo_name = 'cuenta_id' AND tra.tipo_id NOT IN (1)
		LEFT JOIN tbl_bancos bb_2 ON tcc_2.banco_id = bb_2.id

		LEFT JOIN tbl_televentas_cuentas_pago_retiro cpr ON cpr.id = tm.valor_original AND tm.campo_name = 'cuenta_pago_id' AND tra.tipo_id NOT IN (1)
		LEFT JOIN tbl_televentas_cuentas_pago_retiro cpr_2 ON cpr_2.id = tm.valor_nuevo AND tm.campo_name = 'cuenta_pago_id' AND tra.tipo_id NOT IN (1)

		LEFT JOIN tbl_televentas_tipo_constancia tip_c ON tip_c.id = tm.valor_original AND tm.campo_name = 'id_tipo_constancia' AND tra.tipo_id = 1
		LEFT JOIN tbl_televentas_tipo_constancia tip_c_2 ON tip_c_2.id = tm.valor_nuevo AND tm.campo_name = 'id_tipo_constancia' AND tra.tipo_id = 1

		where tm.transaccion_id = " . $transaccion_id .
		" ORDER BY tm.id ASC";

	$result["query_rpt_cor"] = $query_1;
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
		$result["status"] ="No hay registros.";
		$result["result"] =$list_transaccion;
	} elseif(count($list_transaccion)>0){
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_transaccion;
	} else {
		$result["http_code"] = 400;
		$result["status"] ="Ocurrió un error al consultar los registros.";
		$result["result"] =$list_transaccion;
	}
}

echo json_encode($result);
