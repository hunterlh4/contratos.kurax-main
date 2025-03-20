<?php

$result = array();
include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('America/Lima');

/*
  ini_set('display_errors', 0);
  ini_set('display_startup_errors', 0);
  error_reporting(0);
*/
 
 
 
function obtener_balances_historial_cliente($cliente_id){
	global $mysqli;

	$query_balances = "
		SELECT 
		  c.id,
		  IFNULL(ba1.balance, -99999999) balance, 
		  IFNULL(ba2.balance, -99999999) balance_bono_disponible, 
		  IFNULL(ba3.balance, -99999999) balance_bono_utilizado, 
		  IFNULL(ba4.balance, -99999999) balance_deposito,
		  IFNULL(ba5.balance, -99999999) balance_retiro_disponible,
		  IFNULL(ba6.balance, -99999999) balance_dinero_at
		FROM 
		  tbl_televentas_clientes c 
		  LEFT JOIN tbl_televentas_clientes_balance ba1 ON ba1.cliente_id = c.id AND ba1.tipo_balance_id = 1 
		  LEFT JOIN tbl_televentas_clientes_balance ba2 ON ba2.cliente_id = c.id AND ba2.tipo_balance_id = 2 
		  LEFT JOIN tbl_televentas_clientes_balance ba3 ON ba3.cliente_id = c.id AND ba3.tipo_balance_id = 3 
		  LEFT JOIN tbl_televentas_clientes_balance ba4 ON ba4.cliente_id = c.id AND ba4.tipo_balance_id = 4 
		  LEFT JOIN tbl_televentas_clientes_balance ba5 ON ba5.cliente_id = c.id AND ba5.tipo_balance_id = 5 
		  LEFT JOIN tbl_televentas_clientes_balance ba6 ON ba6.cliente_id = c.id AND ba6.tipo_balance_id = 6 
		WHERE 
		c.id= $cliente_id
		";
	$list_query_balances = $mysqli->query($query_balances);
	$list_balance = array();
	if (!($mysqli->error)) {
		while ($li = $list_query_balances->fetch_assoc()) { $list_balance[] = $li; }
		if(count($list_balance)>0){
			if((float)$list_balance[0]["balance"]<-9999999) {
			
				$list_balance[0]["balance"] = number_format(0, 2, '.', '');
			}
			if((float)$list_balance[0]["balance_bono_disponible"]<-9999999) {
		
				$list_balance[0]["balance_bono_disponible"] = number_format(0, 2, '.', '');
			}
			if((float)$list_balance[0]["balance_bono_utilizado"]<-9999999) {
			
				$list_balance[0]["balance_bono_utilizado"] = number_format(0, 2, '.', '');
			}
			if((float)$list_balance[0]["balance_deposito"]<-9999999) {
			
				$list_balance[0]["balance_deposito"] = number_format(0, 2, '.', '');
			}
			if((float)$list_balance[0]["balance_retiro_disponible"]<-9999999) {
			
				$list_balance[0]["balance_retiro_disponible"] = number_format(0, 2, '.', '');
			}
		}
	}
	return $list_balance;
}
 




//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER CLIENTE
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_televentas_cliente") {
	include("function_replace_invalid_caracters.php");

	$usuario_id = $login ? $login['id'] : 0;
	$timestamp = $_POST["timestamp"];
	$busqueda_tipo = $_POST["tipo"];
	$busqueda_valor = $_POST["valor"];
	$where = "";
	if ((int) $busqueda_tipo === 9) {//CELULAR
		$where = "telefono='" . $busqueda_valor . "'";
		$where_web = "col_Phone='" . $busqueda_valor . "'";
	}
	if ((int) $busqueda_tipo === 8) {//WEB
		$where = "web_id='" . $busqueda_valor . "'";
		$where_web = "col_Id='" . $busqueda_valor . "'";
	}
	if ((int) $busqueda_tipo >= 0 && (int) $busqueda_tipo <= 2) {
		$where = "tipo_doc='" . $busqueda_tipo . "' AND num_doc='" . $busqueda_valor . "'";
		$where_web = "col_DocNumber='" . $busqueda_valor . "'";
	}

	$query_1 = "
		SELECT 
			c.id,
			UPPER(IFNULL(c.nombre, '')) nombre,
			UPPER(IFNULL(c.apellido_paterno, '')) apellido_paterno,
			UPPER(IFNULL(c.apellido_materno, '')) apellido_materno,
			IFNULL(c.tipo_doc, '') tipo_doc,
			IFNULL(c.num_doc, '') num_doc,
			IFNULL(c.web_id, '') web_id,
			IFNULL(c.web_full_name, '') web_full_name,
			IFNULL(c.player_id, '') player_id,
			IFNULL(c.calimaco_id, '') calimaco_id,
			IFNULL(c.telefono, '') telefono,
			IFNULL(c.fec_nac, '') fec_nac,
			IFNULL(c.block_user_id, '') block_user_id,
			IFNULL(c.cc_id, '') cc_id,
			IFNULL(c.bono_limite, '10000') bono_limite,
			IFNULL(c.updated_at, '') updated_at,
			'' fecha_creacion_web
		FROM tbl_televentas_clientes c
		WHERE " . $where . " 
		ORDER BY id ASC
		";
	$list_query_1 = $mysqli->query($query_1);
	$list_1 = array();
	if ($mysqli->error) {
		$result["query_1_error"] = $mysqli->error;
	} else {
		while ($li_1 = $list_query_1->fetch_assoc()) {
			$list_1[] = $li_1;
		}
	}

 if (count($list_1) > 0) {

		if(strlen($list_1[0]["web_full_name"])===0){
			if(strlen($list_1[0]["web_id"])>0){
				$full_name_nuevo = "";
				$array_user = array();
				 
				if(isset($array_user["result"])){
					if($array_user["result"]==="OK") {
						$array_user["first_name"] = strtoupper(replace_invalid_caracters(trim($array_user["first_name"])));
						$array_user["middle_name"] = strtoupper(replace_invalid_caracters(trim($array_user["middle_name"])));
						$array_user["last_name"] = strtoupper(replace_invalid_caracters(trim($array_user["last_name"])));

						$full_name_nuevo .= (strlen($array_user["first_name"])>0)?$array_user["first_name"]:'';
						$full_name_nuevo .= (strlen($array_user["middle_name"])>0)?' '.$array_user["middle_name"]:'';
						$full_name_nuevo .= (strlen($array_user["last_name"])>0)?' '.$array_user["last_name"]:'';

						 
					}
				}
			}
		}

		$res_web_id = sec_tlv_api_calimaco_get_web_id($list_1[0]["num_doc"]);
		if((int)$res_web_id['http_code']===200){
			$list_1[0]["fecha_creacion_web"] = $res_web_id["WebCreatedDate"];
		}
	 

		if ((int) $list_1[0]["block_user_id"] === (int) $usuario_id ||
				(int) $list_1[0]["block_user_id"] === 0 ||
				(int) $list_1[0]["block_user_id"] === null) {
			$result["http_code"] = 200;
			$result["status"] = "Cliente encontrado en la BD de televentas.";
			$result["result"] = $list_1[0];
		} else {
			$query_cliente_bloqueado = "
				SELECT 
					u.id usuario_id, 
					u.usuario, 
					CONCAT(IFNULL(a.nombre, ''), ' ', IFNULL(a.apellido_paterno, ''), ' ', IFNULL(a.apellido_materno, '')) usuario_nombres
				FROM tbl_usuarios u 
				JOIN tbl_personal_apt a ON a.id=u.personal_id
				WHERE u.id=" . $list_1[0]["block_user_id"] . "
			";
			$list_query = $mysqli->query($query_cliente_bloqueado);
			$list_bloqueado = array();
			while ($li2 = $list_query->fetch_assoc()) {
				$list_bloqueado[] = $li2;
			}
			if ($mysqli->error) {
				$result["query_cliente_bloqueado"] = $mysqli->error;
			}
			$result["http_code"] = 200;
			$result["status"] = "Cliente bloqueado";
			$result["result"] = $list_1[0];
			$result["result2"] = $list_bloqueado[0];
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrio un error en la BD de televentas.";
		$result["result"] = "El numero de documento ingresado no existe como cliente.";
	}
}







//*******************************************************************************************************************
// OBTENER TRANSACCIONES X CLIENTE
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_transacciones_por_cliente") {

	$cargo_id = $login ? $login['cargo_id'] : 0;

	date_default_timezone_set("America/Lima");
	$id_cliente = $_POST["id_cliente"];
	$fecha_inicio = $_POST["fecha_inicio"];
	$fecha_fin = $_POST["fecha_fin"];
	$tipo_balance = $_POST["tipo_balance"];

	$result["http_code"] = 400;
	$result["status"] = "Error.";
	$result["result"] = "Sin respuesta.";

	if( !((int)$id_cliente > 0) || strlen($fecha_inicio)!== 10 || strlen($fecha_fin)!== 10) {
		$result["result"] = "No se encontro un cliente, una fecha de inicio o de fin para la busqueda.";
		echo json_encode($result);
		exit();
	}

	$where_update_balance="";
	if( (int) $cargo_id !== 5 ){ // Si no es cajero
		$where_update_balance=" OR (tra.tipo_id IN (17,18)) ";
	}

	$where_balance_dinero_at="";
	if( (int) $tipo_balance === 6 ){ // Listar solo transacciones de DINERO AT
		$where_balance_dinero_at=" AND tra.id_tipo_balance = 6 ";
	}else if ( (int) $tipo_balance === 1 ) {
		$where_balance_dinero_at=" AND (tra.id_tipo_balance != 6 OR tra.id_tipo_balance IS NULL ) ";
	}else if ( (int) $tipo_balance === 999 ) {
		$where_balance_dinero_at="";
	}

	// BALANCE BILLETERO
	$list_balance = array();
	$list_balance = obtener_balances_historial_cliente($id_cliente);

	if(count($list_balance)>0){
		// TRANSACCIONES
		$query = "
				SELECT
				tra.id AS trans_id,
				IFNULL(tta.archivo, '') archivo,
				tra.tipo_id,	
				IFNULL(tk.is_bonus, 0) is_bonus,
				IFNULL(tra.txn_id, '') txn_id,
				tra.estado AS estado,
				IFNULL(tra.web_id, '') web_id,
				IFNULL(tra.nuevo_balance, 0) nuevo_balance,
				tra.created_at AS fecha_creacion,
				IFNULL(tra.registro_deposito, '') AS registro_deposito,
				IFNULL(tra.num_operacion, '') AS num_operacion,
				IFNULL(tra.monto_deposito, 0) monto_deposito,
				IFNULL(tra.monto, 0) monto,
				IFNULL(tra.comision_monto, 0) comision_monto,
				IF(IFNULL(tra.bono_id, 0)>0, CONCAT(tra.bono_id,'-',bon.nombre), 'Ninguno') bono_nombre,
				IFNULL(tra.bono_monto, 0.00 ) AS bono_monto,
				IFNULL(tra.total_recarga, 0.00 ) total_recarga,
				IF(tra.tipo_id=10,IFNULL( bon.nombre, 'Ninguno' ),tipo.nombre) AS tipo_transaccion,
				tipo.operacion AS operacion,
				CASE WHEN tra.tipo_id not in (9,11,12,13,28,29,30,31) then IFNULL(cuen.cuenta_descripcion, '') else IFNULL(bcs.nombre, '') end cuenta,
				UPPER(IFNULL(loc.nombre, '')) AS local,
				IFNULL(REPLACE(tra.observacion_cajero, '\n', ''), '') observacion_cajero,
				IFNULL(REPLACE(tra.observacion_validador, '\n', ''), '') observacion_validador,
				IFNULL(REPLACE(tra.observacion_supervisor, '\n', ''), '') observacion_supervisor,
				CASE WHEN tra.tipo_id not in (12, 13, 30, 31) then IFNULL(tr.tipo_rechazo, '') else IFNULL(trs.descripcion, '') end tipo_rechazo,
				IFNULL(tra.cuenta_pago_id, 0) cuenta_pago_id,
				IFNULL(pr.nombre, '') banco_pago, 
				IFNULL(tra.api_id, 0) proveedor_id,
				IFNULL(ta.name, '') proveedor_name,
				usu.usuario AS usuario,
				IFNULL(CONCAT( per.nombre, ' ', IFNULL( per.apellido_paterno, '' ), ' ', IFNULL( per.apellido_materno, '' ) ), '') AS cajero,
				CASE WHEN IFNULL(tra.tipo_operacion, 1) IN (0,1) THEN 1 ELSE tra.tipo_operacion END tipo_operacion,
				IFNULL(tmv.descripcion, '') motivo_dev,
				IFNULL(tra.caja_vip, 0) caja_vip,
	            IFNULL(tta_2.archivo, '') archivo_comision,
				IF(tra.id_tipo_balance = 6, 'Promocional', 'Real') saldo,
				IF( DATE(now()) > DATE(dat_e.fecha_fin), '0', '1') rollback_disponible,
				IFNULL(tj.nombre, '') tipo_jugada
			FROM
				tbl_televentas_clientes_transaccion tra
				LEFT JOIN tbl_televentas_tickets tk ON tk.ticket_id = tra.txn_id and tk.proveedor_id = tra.api_id
				LEFT JOIN tbl_televentas_transaccion_archivos tta ON tra.id = tta.transaccion_id
				LEFT JOIN tbl_televentas_clientes_tipo_transaccion tipo ON tipo.id = tra.tipo_id
				LEFT JOIN tbl_televentas_recargas_bono bon ON bon.id = tra.bono_id 
				LEFT JOIN tbl_cuentas_apt cuen ON cuen.id = tra.cuenta_id
				LEFT JOIN tbl_televentas_clientes_cuenta ccuen ON tra.cuenta_id = ccuen.id
				LEFT JOIN tbl_bancos bcs ON bcs.id = ccuen.banco_id
				LEFT JOIN tbl_caja caj ON caj.id = tra.turno_id
				LEFT JOIN tbl_local_cajas loc_caj ON loc_caj.id = caj.local_caja_id
				LEFT JOIN tbl_locales loc ON loc.id = loc_caj.local_id
				LEFT JOIN tbl_televentas_tipo_rechazo tr ON tr.id=tra.tipo_rechazo_id
				LEFT JOIN tbl_televentas_tipo_rechazo_solicitud_retiro trs ON trs.id=tra.tipo_rechazo_id
				LEFT JOIN tbl_televentas_cuentas_pago_retiro pr ON tra.cuenta_pago_id = pr.id
				LEFT JOIN tbl_televentas_proveedor ta ON tra.api_id = ta.id
				LEFT JOIN tbl_usuarios usu ON usu.id = tra.user_id
				LEFT JOIN tbl_personal_apt per ON per.id = usu.personal_id
				LEFT JOIN tbl_televentas_tipo_motivo_devolucion tmv ON tra.id_motivo_dev = tmv.id
	            LEFT JOIN tbl_televentas_transaccion_archivos tta_2 ON tra.id = tta_2.transaccion_id AND tra.tipo_id = 26
	            LEFT JOIN tbl_televentas_dinero_at_eventos dat_e ON tra.evento_dineroat_id = dat_e.id AND tra.id_tipo_balance = 6
	            LEFT JOIN tbl_televentas_tipo_juego tj ON tra.id_juego_balance = tj.id
			WHERE
				tra.cliente_id = $id_cliente 
				-- AND tra.estado IN (0,1,2,3,4,5,6) 
				-- AND tra.tipo_id  IN (1,2,4,5,9,11,12,13,14,15,16) 
				-- AND (tra.tipo_id  IN (1,2,4,5,9,11,12,13,14,15,16) OR (tra.tipo_id=7 and tra.api_id=2) ) 
				AND 
				-- (
				--	tra.tipo_id IN (1,9,11,12,13,14,19,34) 
				--	OR (tra.tipo_id = 2 and tra.estado = 1) 
				--	OR (tra.tipo_id IN (4,5,15,16,20) and tra.estado in (1,3)) 
				--	OR (tra.tipo_id = 7 and tra.api_id = 2) 
				-- ) 
				(
				   (tra.tipo_id = 1 and tra.estado IN (0,1,2,3) AND IFNULL(tra.caja_vip, 0) != 1) -- depositos: pendientes, validos, rechazados, eliminados
				OR (tra.tipo_id = 6 ) -- depositos rollback
				OR (tra.tipo_id = 2 and tra.estado = 1) -- recargas
				OR (tra.tipo_id = 3 and tra.estado = 1) -- recargas canceladas
				OR (tra.tipo_id IN (4,5,19,20,34) and tra.estado in (1,3,4) and tra.api_id != 4) -- apuestas, pagadas, retornadas, jackpot
				OR (tra.tipo_id IN (4,5,20) and tra.estado in (1,3,4,5) and tra.api_id = 4) -- bingo, jackpot bingo (ventas, pagos, reembolsos, cancelacion)
				OR (tra.tipo_id = 7 ) -- apuestas generada rollback
				OR (tra.tipo_id = 9 and tra.estado IN (1,2,3,4,5,6)) -- retiros: pendiente, pagado, en proceso, verificado
				OR (tra.tipo_id = 11 and tra.estado IN (2)) -- retiros: pagado 
				OR (tra.tipo_id = 12 and tra.estado IN (3)) -- retiros: rechazado
				OR (tra.tipo_id = 13 and tra.estado IN (4)) -- retiros: cancelado
				OR (tra.tipo_id = 14 and tra.estado IN (0,1)) -- terminal-deposit
				OR (tra.tipo_id IN (15,16)) -- cancer: donacion y correccion
				OR (tra.tipo_id IN (21,22,24,25)) -- propinas: solicitud, pagada, rechazada
				OR (tra.tipo_id = 28 and tra.estado IN (1,2,3,4,5,6)) -- retiros: pendiente, pagado, en proceso, verificado
				OR (tra.tipo_id = 32) -- sorteo mundial
				OR (tra.tipo_id = 29 and tra.estado IN (2)) -- retiros: pagado 
				OR (tra.tipo_id = 30 and tra.estado IN (3)) -- retiros: rechazado
				OR (tra.tipo_id = 31 and tra.estado IN (4)) -- retiros: cancelado
				$where_update_balance
				-- OR (tra.tipo_id IN (17,18)) -- subir y bajar balance
				OR (tra.tipo_id IN (26,27)) -- depositos: aprobados y 27 rechazados
				OR (tra.tipo_id IN (33)) -- terminal deposit tambo
				OR (tra.tipo_id IN (35)) -- solicitud recarga
				OR (tra.tipo_id IN (36)) -- Pago Dinero AT
				OR (tra.tipo_id IN (37)) -- Transferencia Dinero AT
				)
				$where_balance_dinero_at
				AND tra.created_at >= '$fecha_inicio 00:00:00' 
				AND tra.created_at <= '$fecha_fin 23:59:59' 
			ORDER BY
				tra.created_at DESC, tra.nuevo_balance ASC, tra.id DESC
			";
		//echo $query;
		$list_query = $mysqli->query($query);
		$list = array();
		if ($mysqli->error) {
			$result["status"] = "Ocurrió un error al consultar las transacciones.";
			$result["result"] = $mysqli->error;
			$result["query"] = $query;
			echo json_encode($result);
			exit();
		} else {
			while ($li = $list_query->fetch_assoc()) {
				$list[] = $li;
			}
			if (count($list) >= 0) {
				$result["http_code"] = 200;
				$result["status"] = "ok";
				$result["result"] = $list;
			} else {
				$result["status"] = "Ocurrió un error al consultar las transacciones.";
				$result["result"] = $list;
				echo json_encode($result);
				exit();
			}
		}

		// BONO USADO EN EL MES ACTUAL
		$query_bono = "
			SELECT
				IFNULL( SUM(bono_monto), 0.00 ) AS bono_monto
			FROM
				tbl_televentas_clientes_transaccion trans
				LEFT JOIN tbl_televentas_clientes_tipo_transaccion tipo ON tipo.id = trans.tipo_id
				LEFT JOIN tbl_televentas_recargas_bono bon ON bon.id = trans.bono_id
				LEFT JOIN tbl_cuentas_apt cuen ON cuen.id = trans.cuenta_id
				LEFT JOIN tbl_caja caj ON caj.id = trans.turno_id
				LEFT JOIN tbl_local_cajas loc_caj ON loc_caj.id = caj.local_caja_id
				LEFT JOIN tbl_locales loc ON loc.id = loc_caj.local_id
				LEFT JOIN tbl_televentas_tipo_rechazo tr ON tr.id=trans.tipo_rechazo_id
			WHERE
				trans.cliente_id = $id_cliente 
				AND trans.tipo_id in (2, 10) 
				AND trans.estado = 1 
				AND MONTH(trans.created_at) = ". date('m') ." 
				AND YEAR(trans.created_at) = ". date('Y') ." 
			ORDER BY
				trans.id DESC 
			";
		$list_query_bono = $mysqli->query($query_bono);
		$list_bonos = array();
		while ($li = $list_query_bono->fetch_assoc()) {
			$list_bonos[] = $li;
		}
		$result["result_bono_usado_mes_actual"] = $list_bonos[0]["bono_monto"];

		// ETIQUETAS
		$query_2 = "
			SELECT
				ce.id AS id,
				UPPER(IFNULL(e.label, '')) AS etiqueta,
				e.color AS color
			FROM
				tbl_televentas_clientes_etiqueta ce
				JOIN tbl_televentas_etiqueta e ON e.id = ce.etiqueta_id
			WHERE
				ce.client_id = $id_cliente 
				AND ce.status = 1 
				AND e.status = 1 
			ORDER BY
				ce.id ASC 
			";
		//echo $query;
		$list_query = $mysqli->query($query_2);
		$list_labels = array();
		while ($li_2 = $list_query->fetch_assoc()) {
			$list_labels[] = $li_2;
		}
		$result["result_labels"] = $list_labels;

		$result["result_balance"] = $list_balance[0]["balance"];
		$result["result_balance_bono_disponible"] = $list_balance[0]["balance_bono_disponible"];
		$result["result_balance_retiro_disponible"] = $list_balance[0]["balance_retiro_disponible"];
		$result["result_balance_no_retirable_disponible"] = $list_balance[0]["balance_deposito"];
		$result["result_balance_dinero_at"] = $list_balance[0]["balance_dinero_at"];
	}
}
 


//*******************************************************************************************************************
// EXPORTAR EXCEL TRANSACCIONES X CLIENTE
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "tabla_historial_cliente_export_xls") {

	$cargo_id = $login ? $login['cargo_id'] : 0;

	date_default_timezone_set("America/Lima");
	$id_cliente = $_POST["id_cliente"];
	$fecha_inicio = $_POST["fecha_inicio"];
	$fecha_fin = $_POST["fecha_fin"];
	$tipo_balance = $_POST["tipo_balance"];

	$result["http_code"] = 400;
	$result["status"] = "Error.";
	$result["result"] = "Sin respuesta.";

	if( !((int)$id_cliente > 0) || strlen($fecha_inicio)!== 10 || strlen($fecha_fin)!== 10) {
		$result["result"] = "No se encontro un cliente, una fecha de inicio o de fin para la busqueda.";
		echo json_encode($result);
		exit();
	}

	$where_update_balance="";
	if( (int) $cargo_id !== 5 ){ // Si no es cajero
		$where_update_balance=" OR (tra.tipo_id IN (17,18)) ";
	}

	$where_balance_dinero_at="";
	if( (int) $tipo_balance === 6 ){ // Listar solo transacciones de DINERO AT
		$where_balance_dinero_at=" AND tra.id_tipo_balance = 6 ";
	}else if ( (int) $tipo_balance === 1 ) {
		$where_balance_dinero_at=" AND (tra.id_tipo_balance != 6 OR tra.id_tipo_balance IS NULL ) ";
	}else if ( (int) $tipo_balance === 999 ) {
		$where_balance_dinero_at="";
	}

	// BALANCE BILLETERO
	$list_balance = array();
	$list_balance = obtener_balances_historial_cliente($id_cliente);

	if(count($list_balance)>0){
		// TRANSACCIONES
		$query = "
			SELECT
			tra.created_at AS fecha_creacion,
			IF(tra.tipo_id=10,IFNULL( bon.nombre, 'Ninguno' ),
			IF(tra.tipo_id=1 and tra.estado = 0, CONCAT(tipo.nombre, ' - ', 'PENDIENTE'),  
			IF(tra.tipo_id=1 and tra.estado = 1, CONCAT(tipo.nombre, ' - ', 'APROBADO'),  
			IF(tra.tipo_id=1 and tra.estado = 2, CONCAT(tipo.nombre, ' - ', 'RECHAZADO'),  
			IF(tra.tipo_id=1 and tra.estado = 3, CONCAT(tipo.nombre, ' - ', 'ELIMINADO'), 
			IF(tra.tipo_id=4 and tra.api_id = 3, 'VENTA GOLDEN', 
			IF(tra.tipo_id=4 and tra.api_id = 3 and tra.estado = 3, 'VENTA GOLDEN - ANULADA', 
			IF(tra.tipo_id=4 and tra.api_id = 4 , 'VENTA BINGO',  
			IF(tra.tipo_id=4 and tra.api_id = 8 , 'VENTA VIRTUAL GOLDEN', 
			IF(tra.tipo_id=4 and tra.api_id = 9 , CONCAT( 'VENTA', ' - ', tra.observacion_cajero), 
			IF(tra.tipo_id=4 and tra.estado = 3 , 'APUESTA ANULADA', 
			IF(tra.tipo_id=4 and tk.is_bonus = 1 , CONCAT(tipo.nombre, ' - ', 'GRATIS'), 
			IF(tra.tipo_id=5 and tra.api_id = 3 , 'PAGO GOLDEN', 
			IF(tra.tipo_id=5 and tra.api_id = 3 and tra.estado = 3, 'PAGO GOLDEN - ANULADA', 
			IF(tra.tipo_id=5 and tra.api_id = 4, 'PAGO BINGO', 
			IF(tra.tipo_id=5 and tra.api_id = 8, 'PAGO VIRTUAL GOLDEN', 
			IF(tra.tipo_id=5 and tra.api_id = 9, CONCAT( 'PAGO', ' - ', tra.observacion_cajero), 
			IF(tra.tipo_id=5 and tra.estado = 3, CONCAT(tipo.nombre, ' - ', 'ANULADA'), 
			IF(tra.tipo_id=20 and tra.api_id = 4, 'PAGO JACKPOT BINGO', 
			IF(tra.tipo_id=20 and tra.api_id = 8, 'PAGO JACKPOT GOLDEN',  
			IF(tra.tipo_id=35 and tra.estado = 1, CONCAT(tipo.nombre, ' - ', 'ATENDIDA'), 
			IF(tra.tipo_id=7, 'APUESTA GENERADA ANULADA', 
			IF(tra.tipo_id=7 and tra.api_id = 4 and tra.estado = 4, 'BINGO CANCELADO', 
			IF(tra.tipo_id=7 and tra.api_id = 4 and tra.estado = 5, 'BINGO REEMBOLSADO', 
			IF(tra.tipo_id=9 and tra.tipo_operacion = 1 and tra.estado = 1 , 'SOLICITUD RETIRO', 
			IF(tra.tipo_id=9 and tra.tipo_operacion = 0 and tra.estado = 1 , 'SOLICITUD DEVOLUCIÓN', 
			IF(tra.tipo_id=9 and tra.tipo_operacion = 0 and tra.estado = 5 , 'SOLICITUD DEVOLUCIÓN - EN PROCESO', 
			IF(tra.tipo_id=9 and tra.tipo_operacion = 1 and tra.estado = 5 , 'SOLICITUD RETIRO - EN PROCESO', 
			IF(tra.tipo_id=9 and tra.tipo_operacion = 1 and tra.estado = 6 , 'SOLICITUD RETIRO - VERIFICADO',  
			IF(tra.tipo_id=9 and tra.tipo_operacion = 0 and tra.estado = 6 , 'SOLICITUD DEVOLUCIÓN - VERIFICADO', 
			IF(tra.tipo_id=11 and tra.tipo_operacion = 0 and tra.caja_vip = 0 , 'DEVOLUCIÓN - PAGADO', 
			IF(tra.tipo_id=11 and tra.tipo_operacion = 0 and tra.caja_vip = 1 , 'DEVOLUCIÓN - PAGADO C7', 
			IF(tra.tipo_id=11 and tra.tipo_operacion = 1 and tra.caja_vip = 0 , 'RETIRO - PAGADO',  
			IF(tra.tipo_id=11 and tra.tipo_operacion = 1 and tra.caja_vip = 1 , 'RETIRO - PAGADO C7', 
			IF(tra.tipo_id=12 and tra.tipo_operacion = 1 , 'RETIRO - RECHAZADO',  
			IF(tra.tipo_id=12 and tra.tipo_operacion = 0 , 'DEVOLUCIÓN - RECHAZADO', 
			IF(tra.tipo_id=13 and tra.tipo_operacion = 0 , 'DEVOLUCIÓN - CANCELADO', 
			IF(tra.tipo_id=13 and tra.tipo_operacion = 1 , 'RETIRO - CANCELADO', 
			IF(tra.tipo_id=21 and tra.estado = 1, 'SOLICITUD PROPINA', 
			IF(tra.tipo_id=21 and tra.estado = 5, 'SOLICITUD EN PROCESO', 
			IF(tra.tipo_id=21 and tra.estado = 6, 'SOLICITUD VERIFICADO', 
			IF(tra.tipo_id=28 and tra.tipo_operacion = 0 and tra.estado = 1, 'SOLICITUD RETIRO',  
			IF(tra.tipo_id=28 and tra.tipo_operacion = 1 and tra.estado = 1, 'SOLICITUD DEVOLUCIÓN', 
			IF(tra.tipo_id=28 and tra.tipo_operacion = 1 and tra.estado = 5, 'DEVOLUCIÓN EN PROCESO', 
			IF(tra.tipo_id=28 and tra.tipo_operacion = 0 and tra.estado = 5, 'RETIRO EN PROCESO', 
			IF(tra.tipo_id=28 and tra.tipo_operacion = 0 and tra.estado = 6, 'RETIRO - VERIFICADO', 
			IF(tra.tipo_id=28 and tra.tipo_operacion = 1 and tra.estado = 6, 'DEVOLUCIÓN - VERIFICADO', 
			IF(tra.tipo_id=29 and tra.tipo_operacion = 1 and tra.estado = 6 and tra.caja_vip = 0, 'DEVOLUCIÓN - PAGADO', 
			IF(tra.tipo_id=29 and tra.tipo_operacion = 1 and tra.estado = 6 and tra.caja_vip = 1, 'DEVOLUCIÓN - PAGADO C7', 
			IF(tra.tipo_id=29 and tra.tipo_operacion = 0 and tra.estado = 6 and tra.caja_vip = 0, 'RETIRO - PAGADO', 
			IF(tra.tipo_id=29 and tra.tipo_operacion = 0 and tra.estado = 6 and tra.caja_vip = 1, 'RETIRO - PAGADO C7', 
			IF(tra.tipo_id=30 and tra.tipo_operacion = 0 , 'RETIRO - RECHAZADO', 
			IF(tra.tipo_id=30 and tra.tipo_operacion = 1 , 'DEVOLUCIÓN - RECHAZADO', 
			IF(tra.tipo_id=31 and tra.tipo_operacion = 1 , 'DEVOLUCIÓN - CANCELADO', 
			IF(tra.tipo_id=31 and tra.tipo_operacion = 0 , 'RETIRO - CANCELADO', tipo.nombre)))))))))))))))))))))))))))))))))))))))))))))))) )))))) ) AS tipo_transaccion,
			IFNULL(tra.txn_id, '') txn_id,
			UPPER(IFNULL(loc.nombre, '')) AS local,
			CASE WHEN tra.tipo_id not in (9,11,12,13,28,29,30,31) then IFNULL(cuen.cuenta_descripcion, '') else IFNULL(bcs.nombre, '') end cuenta,
			IFNULL(tra.monto_deposito, 0) monto_deposito,
			IFNULL(tra.comision_monto, 0) comision_monto,
			IFNULL(tra.monto, 0) monto,
			IFNULL(tra.bono_monto, 0.00 ) AS bono_monto,
			IFNULL(tra.total_recarga, 0.00 ) total_recarga,
			IFNULL(tra.nuevo_balance, 0) nuevo_balance,
			IF(tra.id_tipo_balance = 6, 'Promocional', 'Real') saldo  

			FROM
				tbl_televentas_clientes_transaccion tra 
				LEFT JOIN tbl_televentas_tickets tk ON tk.ticket_id = tra.txn_id and tk.proveedor_id = tra.api_id
				LEFT JOIN tbl_televentas_recargas_bono bon ON bon.id = tra.bono_id 
				LEFT JOIN tbl_televentas_clientes_tipo_transaccion tipo ON tipo.id = tra.tipo_id
				LEFT JOIN tbl_cuentas_apt cuen ON cuen.id = tra.cuenta_id
				LEFT JOIN tbl_televentas_clientes_cuenta ccuen ON tra.cuenta_id = ccuen.id
				LEFT JOIN tbl_bancos bcs ON bcs.id = ccuen.banco_id
				LEFT JOIN tbl_caja caj ON caj.id = tra.turno_id
				LEFT JOIN tbl_local_cajas loc_caj ON loc_caj.id = caj.local_caja_id
				LEFT JOIN tbl_locales loc ON loc.id = loc_caj.local_id
			WHERE
				tra.cliente_id = $id_cliente 
				-- AND tra.estado IN (0,1,2,3,4,5,6) 
				-- AND tra.tipo_id  IN (1,2,4,5,9,11,12,13,14,15,16) 
				-- AND (tra.tipo_id  IN (1,2,4,5,9,11,12,13,14,15,16) OR (tra.tipo_id=7 and tra.api_id=2) ) 
				AND 
				-- (
				--	tra.tipo_id IN (1,9,11,12,13,14,19,34) 
				--	OR (tra.tipo_id = 2 and tra.estado = 1) 
				--	OR (tra.tipo_id IN (4,5,15,16,20) and tra.estado in (1,3)) 
				--	OR (tra.tipo_id = 7 and tra.api_id = 2) 
				-- ) 
				(
				   (tra.tipo_id = 1 and tra.estado IN (0,1,2,3) AND IFNULL(tra.caja_vip, 0) != 1) -- depositos: pendientes, validos, rechazados, eliminados
				OR (tra.tipo_id = 6 ) -- depositos rollback
				OR (tra.tipo_id = 2 and tra.estado = 1) -- recargas
				OR (tra.tipo_id = 3 and tra.estado = 1) -- recargas canceladas
				OR (tra.tipo_id IN (4,5,19,20,34) and tra.estado in (1,3,4) and tra.api_id != 4) -- apuestas, pagadas, retornadas, jackpot
				OR (tra.tipo_id IN (4,5,20) and tra.estado in (1,3,4,5) and tra.api_id = 4) -- bingo, jackpot bingo (ventas, pagos, reembolsos, cancelacion)
				OR (tra.tipo_id = 7 ) -- apuestas generada rollback
				OR (tra.tipo_id = 9 and tra.estado IN (1,2,3,4,5,6)) -- retiros: pendiente, pagado, en proceso, verificado
				OR (tra.tipo_id = 11 and tra.estado IN (2)) -- retiros: pagado 
				OR (tra.tipo_id = 12 and tra.estado IN (3)) -- retiros: rechazado
				OR (tra.tipo_id = 13 and tra.estado IN (4)) -- retiros: cancelado
				OR (tra.tipo_id = 14 and tra.estado IN (0,1)) -- terminal-deposit
				OR (tra.tipo_id IN (15,16)) -- cancer: donacion y correccion
				OR (tra.tipo_id IN (21,22,24,25)) -- propinas: solicitud, pagada, rechazada
				OR (tra.tipo_id = 28 and tra.estado IN (1,2,3,4,5,6)) -- retiros: pendiente, pagado, en proceso, verificado
				OR (tra.tipo_id = 32) -- sorteo mundial
				OR (tra.tipo_id = 29 and tra.estado IN (2)) -- retiros: pagado 
				OR (tra.tipo_id = 30 and tra.estado IN (3)) -- retiros: rechazado
				OR (tra.tipo_id = 31 and tra.estado IN (4)) -- retiros: cancelado
				$where_update_balance
				-- OR (tra.tipo_id IN (17,18)) -- subir y bajar balance
				OR (tra.tipo_id IN (26,27)) -- depositos: aprobados y 27 rechazados
				OR (tra.tipo_id IN (33)) -- terminal deposit tambo
				OR (tra.tipo_id IN (35)) -- solicitud recarga
				OR (tra.tipo_id IN (36)) -- Pago Dinero AT
				OR (tra.tipo_id IN (37)) -- Transferencia Dinero AT
				)
				$where_balance_dinero_at
				AND tra.created_at >= '$fecha_inicio 00:00:00' 
				AND tra.created_at <= '$fecha_fin 23:59:59' 
			ORDER BY
				tra.created_at DESC, tra.nuevo_balance ASC, tra.id DESC
			";
		 
			$list_query = $mysqli->query($query);
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
					"fecha_creacion" => "Fecha",
					"tipo_transaccion" => "Tipo",
					"txn_id" => "ID",
					"local" => "Caja",
					"cuenta" => "Cuenta",
					"monto_deposito" => "Depósito",
					"comision_monto" => "Comisión",
					"monto" => "Monto",
					"bono_monto" => "Bono",
					"total_recarga" => "Total",
					"nuevo_balance" => "Nuevo Balance",
					"saldo" => "Tipo Saldo"
				];
				array_unshift($result_data, $headers);

				ini_set('display_errors', 0);
				ini_set('display_startup_errors', 0);
				error_reporting(0);

				require_once '../phpexcel/classes/PHPExcel.php';
				$objPHPExcel = new PHPExcel();
				$objPHPExcel->getActiveSheet()->fromArray($result_data, null, 'A1');
				$date = new DateTime();
				$file_title = "reporte_historial_cliente_" . $date->getTimestamp();

				if (!file_exists('/var/www/html/export/files_exported/historial_cliente/')) {
					mkdir('/var/www/html/export/files_exported/historial_cliente/', 0777, true);
				}

				header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
				header('Content-Disposition: attachment;filename="' . $file_title . '.xls"');
				header('Cache-Control: max-age=0');

				$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
				$excel_path = '/var/www/html/export/files_exported/historial_cliente/' . $file_title . '.xls';
				$excel_path_download = '/export/files_exported/historial_cliente/' . $file_title . '.xls';
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
}



 
  
//*******************************************************************************************************************
// OBTENER ULTIMAS TRANSACCIONES
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_ultimas_transacciones") {

	$cliente_id = $_POST["cliente_id"];
	$limit = $_POST["limit"];

	$query_1 = "
		SELECT
			tra.id,
			tra.tipo_id,
			ttra.nombre,
			CASE WHEN tra.tipo_id = 10 then tra.bono_monto else tra.monto end AS monto,
			tra.created_at as fecha_hora
			FROM tbl_televentas_clientes_transaccion tra FORCE INDEX (cliente_id)
			INNER JOIN tbl_televentas_clientes_tipo_transaccion ttra FORCE INDEX (PRIMARY) ON tra.tipo_id = ttra.id
		WHERE tra.cliente_id = " . $cliente_id . "
			ORDER BY tra.id DESC
			LIMIT " . $limit;
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
		$result["status"] = "No hay transacciones.";
	} elseif (count($list_transaccion) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_transaccion;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar transaccioneS.";
	}
}

if ($_POST["accion"] === "sec_tlv_hist_obtener_televentas_titular_abono_reg") {
	include("function_replace_invalid_caracters.php");

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
		$query_2 = "
			SELECT 
				c.id,
				CONCAT(IFNULL(c.nombre, ''), ' ', IFNULL(c.apellido_paterno, ''), ' ', IFNULL(c.apellido_materno, '')) nombre_apellido_titular,
				IFNULL(c.tipo_doc, '') tipo_doc,
				IFNULL(c.num_doc, '') dni_titular
			FROM tbl_televentas_clientes c
			WHERE id =".$id_cliente."
			ORDER BY id ASC";

			$list_query_2 = $mysqli->query($query_2);
			$list_2 = array();
			if ($mysqli->error) {
				$result["query_2_error"] = $mysqli->error;
			} else {
				while ($li_2 = $list_query_2->fetch_assoc()) {
					$list_2[] = $li_2;
				}
			}

			$result["http_code"] = 200;
			$result["status"] = "ok";
			$result["result"] = $list_2;

		
	} elseif (count($list_1) > 0) {	
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_1;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los registros";
	}

}

function sec_tlv_api_calimaco_get_web_id($num_doc){
	global $mysqli;
	global $login;

	$result = [];

    try {
		$url = "https://api.apuestatotal.com/v2/teleservicios/calimaco/getWebId";
		$rq = ['numDoc' => $num_doc];
		$request_headers = array();
		$request_headers[] = "Content-type: application/json";
		$request_headers[] = "Authorization:Bearer " . env('TELEVENTAS_API_TOKEN');
		$request_json = json_encode($rq);
		$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $request_json);
		$response = curl_exec($curl);
		$err = curl_error($curl);
		$response_arr = json_decode($response, true);
		curl_close($curl);

		if ($err) {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al consumir el API.";
		} else {
			$result = $response_arr;
		}
    } catch (Exception $e) {
		$result["http_code"] = 400;
    }
	return $result;
}

echo json_encode($result);
?>