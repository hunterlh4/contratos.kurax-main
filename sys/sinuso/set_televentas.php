<?php

$result = array();
include("db_connect.php");
include("sys_login.php");
include 'mailer/class.phpmailer.php';
include 'helpers.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('America/Lima');

/*
  ini_set('display_errors', 0);
  ini_set('display_startup_errors', 0);
  error_reporting(0);
*/


$usuario_id = $login ? $login['id'] : 0;
$usuario_nombre  = $login ? $login['nombre'] : '';
$usuario_ape_pat = $login ? $login['apellido_paterno'] : '';
$usuario = $login ? $login['usuario'] : '';

if (!((int) $usuario_id > 0)) {
	$result["http_code"] = 400;
	$result["status"] = "Sesión perdida. Por favor vuelva a iniciar sesión.";
	echo json_encode($result);exit();
}

if(!isset($_POST["accion"])){
	$result["http_code"] = 400;
	$result["status"] = "Acción no válida.";
	$result["result"] = "Acción no válida.";
	echo json_encode($result);exit();
}

$turno_id = 0;
$turno_local_id = 0;
$turno_red_id = 0;
$cc_id = '';

$array_developers = get_array_developers();

if(!(in_array($login["usuario"], $array_developers))){

	$validate_turno = get_array_validate_turno();

	if (in_array($_POST["accion"], $validate_turno)) {
		$turno = get_turno();
		if (!(count($turno) > 0)) {
			$result["http_code"] = 400;
			$result["status"] = "Debe abrir un turno.";
			$result["result"] = $turno;
			echo json_encode($result);exit();
		}
		$turno_id = $turno[0]["id"];
		$cc_id = $turno[0]["cc_id"];
		$turno_local_id = $turno[0]["local_id"];
		$turno_red_id   = $turno[0]["red_id"];
		if (!((int) $turno_id > 0)) {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al consultar el turno.";
			$result["result"] = $turno;
			echo json_encode($result);exit();
		}
	}
} else {
	$turno = get_turno();
	if (count($turno) > 0) {
		$turno_id       = $turno[0]["id"];
		$cc_id          = $turno[0]["cc_id"];
		$turno_local_id = $turno[0]["local_id"];
		$turno_red_id   = $turno[0]["red_id"];
	}
}




function get_array_developers(){
	$array_developers = array();
	$array_developers[] = "bladimir.quispe";
	$array_developers[] = "jhonny.quispe";
	$array_developers[] = "lila";
	$array_developers[] = "pierre.herrera";
	$array_developers[] = "jsantiago";
	return $array_developers;
}
function get_array_validate_turno(){
	$validate_turno = array();
	$validate_turno[] = "obtener_televentas_cliente";
	$validate_turno[] = "validar_cliente_televentas";
	$validate_turno[] = "busqueda_api_cliente_televentas";
	$validate_turno[] = "guardar_titular_abono";
	$validate_turno[] = "obtener_televentas_titular_abono";
	$validate_turno[] = "busqueda_titular_abono_tbl_clientes";
	$validate_turno[] = "busqueda_titular_abono_api";
	$validate_turno[] = "obtener_televentas_titular_abono_reg";
	$validate_turno[] = "listado_eliminar_titular_abono";
	$validate_turno[] = "eliminar_titular_abono";
	
	//$validate_turno[] = "obtener_depositos_disponibles";
	//$validate_turno[] = "obtener_recarga_x_bono";
	$validate_turno[] = "obtener_transacciones_por_cliente";
	//$validate_turno[] = "obtener_lista_bonos_disponibles";
	//$validate_turno[] = "obtener_bloqueo_bono";
	$validate_turno[] = "guardar_transaccion_deposito";
	$validate_turno[] = "eliminar_transaccion_deposito";
	$validate_turno[] = "guardar_transaccion_recarga_web";
	$validate_turno[] = "registrar_apuesta_detalle";
	$validate_turno[] = "registrar_apuesta";
	$validate_turno[] = "pagar_apuesta_detalle";
	$validate_turno[] = "pagar_apuesta";
	$validate_turno[] = "pagar_ticket_kurax";
	$validate_turno[] = "obtener_transacciones_nuevas";
	$validate_turno[] = "bloquear_cliente";
	//$validate_turno[] = "listar_motivos_balance_sube";
	//$validate_turno[] = "listar_motivos_balance_baja";
	//$validate_turno[] = "listar_juegos_balance";
	//$validate_turno[] = "listar_cajeros_tlv";
	//$validate_turno[] = "listar_supervisores_tlv";
	//$validate_turno[] = "desbloquear_cliente";
	$validate_turno[] = "editar_cliente";
	$validate_turno[] = "fusionar_clientes";
	$validate_turno[] = "verificar_datos_fusion_clientes";
	$validate_turno[] = "guardar_fusion_clientes";
	$validate_turno[] = "actualizar_etiqueta";
	$validate_turno[] = "guardar_etiqueta";
	$validate_turno[] = "elegir_etiqueta";
	$validate_turno[] = "eliminar_etiqueta";
	$validate_turno[] = "guardar_fec_nac";
	//$validate_turno[] = "consultar_dni";
	$validate_turno[] = "obtener_cuentas_x_cliente";
	$validate_turno[] = "guardar_cuenta_x_cliente";
	$validate_turno[] = "obtener_cuentas_x_cajero";
	$validate_turno[] = "guardar_cuenta_x_cajero";
	$validate_turno[] = "guardar_propina_cajero";
	$validate_turno[] = "obtener_fecha_hora";
	$validate_turno[] = "eliminar_transaccion_apuesta";
	$validate_turno[] = "eliminar_transaccion_pago_apuesta";
	$validate_turno[] = "guardar_transaccion_solicitud_retiro";
	$validate_turno[] = "cancelar_solicitud_retiro";
	$validate_turno[] = "cancelar_solicitud_propina";
	//$validate_turno[] = "obtener_imagenes_x_transaccion_retiro";
	//$validate_turno[] = "obtener_imagenes_x_transaccion_propina";
	$validate_turno[] = "obtener_ultimas_transacciones";
	$validate_turno[] = "sec_tlv_cambiar_estado_enviar_comprobante";
	$validate_turno[] = "guardar_donacion_cancer";
	$validate_turno[] = "eliminar_transaccion_donacion_cancer";
	$validate_turno[] = "generar_url_calimaco";
	$validate_turno[] = "generar_url_torito";
	$validate_turno[] = "portal_calimaco_registrar_apuesta";
	$validate_turno[] = "obtener_apuesta_altenar";
	$validate_turno[] = "guardar_transaccion_recarga_web2";
	$validate_turno[] = "consultar_terminal_deposit";
	$validate_turno[] = "eliminar_transaccion_terminal";
	$validate_turno[] = "editar_balance";
	$validate_turno[] = "sec_tlv_registrar_bingo_venta_detalle";
	$validate_turno[] = "sec_tlv_registrar_bingo_venta";
	$validate_turno[] = "sec_tlv_registrar_bingo_pago_detalle";
	$validate_turno[] = "sec_tlv_registrar_bingo_pago";
	$validate_turno[] = "guardar_transaccion_deposito_c7";
	$validate_turno[] = "guardar_transaccion_pago_retiro_c7";
	//$validate_turno[] = "obtener_televentas_limite_cajero";
	//$validate_turno[] = "obtener_televentas_cont_cajero";
	//$validate_turno[] = "obtener_televentas_cliente_cajero";
	$validate_turno[] = "sec_tlv_ingresar_tambo";
	$validate_turno[] = "guardar_transaccion_devolucion_c7";
	$validate_turno[] = "sec_tlv_obtener_session_gr";
	$validate_turno[] = "sec_tlv_get_credenciales_bingo";
	$validate_turno[] = "sec_tlv_set_credenciales_bingo";
	$validate_turno[] = "transferir_saldo_promocional";
	$validate_turno[] = "consultar_ubigeo";
	$validate_turno[] = "consultar_nacionalidad";
	$validate_turno[] = "consultar_api_SIC_cliente_televentas";


	return $validate_turno;
}

function get_turno() {
	global $mysqli;
	global $usuario_id;

	$command = "
		SELECT
			sqc.id,
			IFNULL(ssql.id, 0) local_id,
			IFNULL(ssql.cc_id, '') cc_id,
			IFNULL(ssql.red_id, 0) red_id
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

function cliente_mincetur($tipo_documento, $numero_cliente){
	switch ($tipo_documento) {
		case "0":
			$tipo_doc = "DNI";
			break;
		case "1":
			$tipo_doc = "CE";
			break;
		case "2":
			$tipo_doc = "PAS";
			break;
		default:
			$tipo_doc = "DNI";
			break;
	}

	$data = [
		"numero_documento" => $numero_cliente,
		"tipo_documento" => $tipo_doc
	];

	$exists_mincetur = 0;
	$result_api_mincetur = consultar_mincetur_ludopatia($data);
	if (isset($result_api_mincetur["success"])) {
		if($result_api_mincetur["success"] === true &&  $result_api_mincetur["estado"] == 100){
			$exists_mincetur = 1;
		}
	}
	return $exists_mincetur;
}

function consultar_mincetur_ludopatia($data){
	$curl = curl_init();
	$url = env('SIC_API_URL') . "/find_data_mincetur_ludopatia";
	$request_headers = array();
	$request_headers[] = "Content-type: application/json";
	$request_headers[] = "Authorization:Bearer " . env('SIC_API_TOKEN');
	$request_json = json_encode($data);
	$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $request_json);
	$response = curl_exec($curl);
	$err = curl_error($curl);
	$response_arr = $response;
	curl_close($curl);

	$consulta = ($response ?? []);
	return json_decode($consulta, true);
}

function get_cliente_por_dni($dni) {
	$curl = curl_init();
	curl_setopt_array($curl, [
		CURLOPT_URL => "https://api.apuestatotal.com/v2/dni?dni=" . $dni,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "POST",
		CURLOPT_HTTPHEADER => [
			"Accept: application/json",
			"Authorization: Bearer " . env('TELEVENTAS_API_TOKEN')
		],
	]);
	$response = json_decode(curl_exec($curl), true);
	$err = curl_error($curl);
	curl_close($curl);
	$consulta = ($response["result"] ?? []);
	return $consulta;
}

function resizeImage($resourceType, $image_width, $image_height) {
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





function query_tbl_televentas_tickets($ticket){
	global $mysqli;
	date_default_timezone_set("America/Lima");
	$date_time = date('Y-m-d H:i:s');
	$result = array();
	$query_1 = "
		SELECT 
				t.ticket_id 
		FROM tbl_televentas_tickets t 
		WHERE ticket_id = '" . $ticket['ticket_id'] . "' 
		AND proveedor_id = '" . $ticket['proveedor_id'] . "' 
		";
	$list_query_1 = $mysqli->query($query_1);
	$list_1 = array();
	if ($mysqli->error) {
		$result["query_tbl_televentas_tickets_select_error"] = $mysqli->error;
		$result["query_tbl_televentas_tickets_select"] = $query_1;
	} else {
		while ($li_1 = $list_query_1->fetch_assoc()) {
			$list_1[] = $li_1;
		}
		if (count($list_1) === 0) {
			$query_inset = "";
			$query_value = "";
			if(strlen($ticket['calc_date'])>0){
				$query_inset .= " calc_date, ";
				$query_value .= " '".$ticket['calc_date']."', ";
			}
			if(strlen($ticket['paid_date'])>0){
				$query_inset .= " paid_date, ";
				$query_value .= " '".$ticket['paid_date']."', ";
			}
			$query = "
				INSERT INTO tbl_televentas_tickets (
					ticket_id,
					proveedor_id,
					created,
					$query_inset
					external_id,
					game,
					sell_local_id,
					paid_local_id,
					num_selections,
					price,
					stake_amount,
					winning_amount,
					jackpot_amount,
					is_bonus,
					status,
					status_text,
					created_at,
					updated_at
				) VALUES (
					'" . $ticket['ticket_id'] . "',
					'" . $ticket['proveedor_id'] . "',
					'" . $ticket['created'] . "',
					$query_value
					'" . $ticket['external_id'] . "',
					'" . $ticket['game'] . "',
					'" . $ticket['sell_local_id'] . "',
					'" . $ticket['paid_local_id'] . "',
					'" . $ticket['num_selections'] . "',
					'" . $ticket['price'] . "',
					'" . $ticket['stake_amount'] . "',
					'" . $ticket['winning_amount'] . "',
					'" . $ticket['jackpot_amount'] . "',
					'" . $ticket['is_bonus'] . "',
					'" . $ticket['status'] . "',
					'" . $ticket['status_text'] . "',
					'" . $date_time . "',
					'" . $date_time . "'
				)";
			$mysqli->query($query);
			if ($mysqli->error) {
				$result["query_tbl_televentas_tickets_insert_error"] = $mysqli->error;
				$result["query_tbl_televentas_tickets_insert"] = $query;
			}
		} else if (count($list_1) > 0) {
			$update_set = "";
			if(strlen($ticket['created'])>0){
				$update_set .= " ,created = '".$ticket['created']."' ";
			}
			if(strlen($ticket['calc_date'])>0){
				$update_set .= " ,calc_date = '".$ticket['calc_date']."' ";
			}
			if(strlen($ticket['paid_date'])>0){
				$update_set .= " ,paid_date = '".$ticket['paid_date']."' ";
			}
			if(strlen($ticket['external_id'])>0){
				$update_set .= " ,external_id = '".$ticket['external_id']."' ";
			}
			if(strlen($ticket['game'])>0){
				$update_set .= " ,game = '".$ticket['game']."' ";
			}
			if((int)$ticket['paid_local_id']>0){
				$update_set .= " ,paid_local_id = '".$ticket['paid_local_id']."' ";
			}
			if((int)$ticket['num_selections']>0){
				$update_set .= " ,num_selections = '".$ticket['num_selections']."' ";
			}
			if((float)$ticket['price']>0){
				$update_set .= " ,price = '".$ticket['price']."' ";
			}
			if((float)$ticket['stake_amount']>0){
				$update_set .= " ,stake_amount = '".$ticket['stake_amount']."' ";
			}
			if((float)$ticket['winning_amount']>0){
				$update_set .= " ,winning_amount = '".$ticket['winning_amount']."' ";
			}
			if((float)$ticket['jackpot_amount']>0){
				$update_set .= " ,jackpot_amount = '".$ticket['jackpot_amount']."' ";
			}
			if((int)$ticket['status']!==0){
				$update_set .= " ,status = '".$ticket['status']."' ";
			}
			if(strlen($ticket['status_text'])>0){
				$update_set .= " ,status_text = '".$ticket['status_text']."' ";
			}
			$query_update = "
				UPDATE tbl_televentas_tickets 
				SET updated_at = '" . $date_time . "' 
					$update_set 
				WHERE ticket_id = '" . $ticket['ticket_id'] . "' 
				AND proveedor_id = '" . $ticket['proveedor_id'] . "' 
				";
			$mysqli->query($query_update);
			if ($mysqli->error) {
				$result["query_tbl_televentas_tickets_updated_error"] = $mysqli->error;
				$result["query_tbl_televentas_tickets_updated"] = $query_update;
			}
		}
	}
	return $result;
}

function query_tbl_televentas_clientes_block($cliente_id, $block){
	global $mysqli;
	global $login;

	$user_id = $login ? $login['id'] : 0;

	$query = "
		INSERT INTO tbl_televentas_clientes_block (
			client_id,
			block,
			user_id,
			created_at
		) VALUES (
			" . $cliente_id . ",
			" . $block . ",
			" . $user_id . ",
			now()
		)";
	$mysqli->query($query);

	if ($mysqli->error) {
		echo $mysqli->error;
	}

	if((int)$block === 0){
		$user_id = 0;
	}

	$query_update = "
		UPDATE tbl_televentas_clientes 
		SET block_user_id = $user_id, 
			updated_at = now() 
		WHERE id = $cliente_id 
		";
	$mysqli->query($query_update);
}

function query_tbl_televentas_clientes_balance($action, $cliente_id, $tipo_id, $balance){
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

		if ($mysqli->error) {
			sec_tlv_log_error($mysqli->error . ' SQL: ' . $query);
		}
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

		if ($mysqli->error) {
			sec_tlv_log_error($mysqli->error . ' SQL: ' . $query);
		}
	}
}

function query_tbl_televentas_clientes_balance_transaccion($action, $transaccion_id, $cliente_id, $tipo_balance_id, $balance_actual, $monto, $balance_nuevo){
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

		if ($mysqli->error) {
			sec_tlv_log_error($mysqli->error . ' SQL: ' . $query);
		}
	}
}

function obtener_balances($cliente_id){
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
				query_tbl_televentas_clientes_balance('insert', $cliente_id, 1, 0);
				$list_balance[0]["balance"] = number_format(0, 2, '.', '');
			}
			if((float)$list_balance[0]["balance_bono_disponible"]<-9999999) {
				query_tbl_televentas_clientes_balance('insert', $cliente_id, 2, 0);
				$list_balance[0]["balance_bono_disponible"] = number_format(0, 2, '.', '');
			}
			if((float)$list_balance[0]["balance_bono_utilizado"]<-9999999) {
				query_tbl_televentas_clientes_balance('insert', $cliente_id, 3, 0);
				$list_balance[0]["balance_bono_utilizado"] = number_format(0, 2, '.', '');
			}
			if((float)$list_balance[0]["balance_deposito"]<-9999999) {
				query_tbl_televentas_clientes_balance('insert', $cliente_id, 4, 0);
				$list_balance[0]["balance_deposito"] = number_format(0, 2, '.', '');
			}
			if((float)$list_balance[0]["balance_retiro_disponible"]<-9999999) {
				query_tbl_televentas_clientes_balance('insert', $cliente_id, 5, 0);
				$list_balance[0]["balance_retiro_disponible"] = number_format(0, 2, '.', '');
			}
			if((float)$list_balance[0]["balance_dinero_at"]<-9999999) {
				query_tbl_televentas_clientes_balance('insert', $cliente_id, 6, 0);
				$list_balance[0]["balance_dinero_at"] = number_format(0, 2, '.', '');
			}
		}
	}
	return $list_balance;
}




function rollback_transaccion($cliente_id, $transaccion_id, $usuario_id, $turno_id, $observacion, $motivo_id){
	global $mysqli;

	date_default_timezone_set("America/Lima");
	$date_time = date('Y-m-d H:i:s');

	$respuesta = 0;

	$query = "
		SELECT
			cbt.transaccion_id,
			cbt.tipo_balance_id,
			cbt.monto,
			ct.tipo_id,
			IFNULL(ct.api_id,0) api_id,
			IFNULL(ct.txn_id,'')txn_id,
			ctt.rollback_tipo_id,
			ctt.operacion,
			IFNULL(ct.evento_dineroat_id, 0) evento_dineroat_id,
			cb.balance
		FROM
			tbl_televentas_clientes_balance_transaccion cbt
			JOIN tbl_televentas_clientes_transaccion ct ON ct.id = cbt.transaccion_id
			JOIN tbl_televentas_clientes_tipo_transaccion ctt ON ctt.id = ct.tipo_id 
			JOIN tbl_televentas_clientes_balance cb ON cb.tipo_balance_id = cbt.tipo_balance_id and cb.cliente_id = cbt.cliente_id
		WHERE
			cbt.transaccion_id = $transaccion_id 
			AND ct.estado in (0,1)
			-- AND TIMESTAMPDIFF(HOUR,ct.created_at,now()) <= 24 
		ORDER BY ctt.id asc 
		";
	$list_query = $mysqli->query($query);
	$list_transacciones = array();

	$balance_principal=0;
	$rollback_transaccion_id = 0;
	$continue = 0;

	while ($li = $list_query->fetch_assoc()) { 
		$list_transacciones[] = $li;
		if($li['operacion']==='credit'){
			if((double)$li['balance']<(double)$li['monto']){
				$result["http_code"] = 400;
				$result["status"] = "El balance es menor al monto a retornar.";
				$result["balance"] = $li['balance'];
				$result["monto"] = $li['monto'];
				$result["tipo_id"] = $li['tipo_id'];
				$continue = 1;
			}
		}
		if( (int)$li['api_id'] === 2 && (int)$li['tipo_id'] === 5 ){
			$result["http_code"] = 400;
			$result["status"] = "No se pueden eliminar apuestas del proveedor Calimaco.";
			$result["balance"] = $li['balance'];
			$result["monto"] = $li['monto'];
			$result["tipo_id"] = $li['tipo_id'];
			$continue = 1;
		}
	}

	if(count($list_transacciones) == 0){
		$result["http_code"] = 400;
		$result["status"] = "No hay log de balance de la transacción.";
		$result["balance"] = 0;
		$result["monto"] = 0;
		$result["tipo_id"] = 0;
		$continue = 1;
	}else{
		$li = $list_transacciones[0];
	}

	if((int)$continue===0){
		$evento_dineroat_id = (int)$li['evento_dineroat_id'];
		if ((int)$evento_dineroat_id == 0){
			$evento_dineroat_id = 'null';
		}
		if( (int)$li['tipo_balance_id']===1 || (int)$li['tipo_balance_id']===6 ){
			if($li['operacion']==='credit') { // credit + -> -
				$balance_principal = (double)$li['balance'] - (double)$li['monto'];
			}
			if($li['operacion']==='debit') { // debit - -> +
				$balance_principal = (double)$li['balance'] + (double)$li['monto'];
			}

			$insert_command = "
				INSERT INTO tbl_televentas_clientes_transaccion (
					tipo_id,
					cliente_id,
					api_id,
					txn_id,
					user_id,
					turno_id,
					monto,
					nuevo_balance,
					estado,
					transaccion_id,
					id_tipo_balance,
					evento_dineroat_id,
					created_at
				) VALUES (
					" . $li['rollback_tipo_id'] . ",
					" . $cliente_id . ",
					" . $li['api_id'] . ",
					'" . $li['txn_id'] . "',
					" . $usuario_id . ",
					" . $turno_id . ",
					" . $li['monto'] . ",
					" . $balance_principal . ",
					1,
					" . $transaccion_id . ",
					" .$li['tipo_balance_id']. ",
					$evento_dineroat_id,
					'" . $date_time . "'
				)";
			$mysqli->query($insert_command);

			// nuevo_balance = '" . $balance_principal . "', 
			$query = "
					UPDATE tbl_televentas_clientes_transaccion 
					SET 
						estado = '3', 
						update_user_id = " . $usuario_id . ", 
						id_motivo_dev = " . $motivo_id . ",
						observacion_supervisor = '" . $observacion . "', 
						update_user_at = '" . $date_time . "' 
					WHERE id = '" . $transaccion_id . "'
				";
			$mysqli->query($query);

			$query_3 = "SELECT id FROM tbl_televentas_clientes_transaccion  ";
			$query_3 .= " WHERE tipo_id=" . $li['rollback_tipo_id'] . " ";
			$query_3 .= " AND user_id='" . $usuario_id . "' ";
			$query_3 .= " AND turno_id='" . $turno_id . "' ";
			$query_3 .= " AND cliente_id='" . $cliente_id . "' ";
			$query_3 .= " AND transaccion_id='" . $transaccion_id . "' ";
			$query_3 .= " AND id_tipo_balance='" . $li['tipo_balance_id'] . "' ";
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

				if($tra['operacion']==='credit'){
					if((double)$tra['balance']<(double)$tra['monto']){
						$result["http_code"] = 400;
						$result["status"] = "El balance es menor al monto a retornar. 2";
						$result["balance"] = $tra['balance'];
						$result["monto"] = $tra['monto'];
						$result["tipo_id"] = $tra['tipo_balance_id'];
						$continue = 1;
					}
				}

				if((int)$continue===0){
					$temp_tipo_balance_id = $tra['tipo_balance_id'];
					$temp_tipo_id = $tra['tipo_id'];
					$temp_rollback_tipo_id = $tra['rollback_tipo_id'];
					$temp_balance_actual = $tra['balance'];


					if($tra['operacion']==='credit') { // credit + -> -
						$temp_balance_nuevo = (double)$tra['balance'] - (double)$tra['monto'];
					}
					if($tra['operacion']==='debit') { // debit - -> +
						$temp_balance_nuevo = (double)$tra['balance'] + (double)$tra['monto'];
					}

					query_tbl_televentas_clientes_balance('update', $cliente_id, $temp_tipo_balance_id, $temp_balance_nuevo);

					query_tbl_televentas_clientes_balance_transaccion('insert', $rollback_transaccion_id, 
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
function query_tbl_televentas_tickets_get_array(){
	$ticket = array();
	$ticket['ticket_id'] = '';
	$ticket['proveedor_id'] = '';
	$ticket['created'] = '';
	$ticket['calc_date'] = '';
	$ticket['paid_date'] = '';
	$ticket['external_id'] = '';
	$ticket['game'] = '';
	$ticket['sell_local_id'] = 0;
	$ticket['paid_local_id'] = 0;
	$ticket['num_selections'] = 0;
	$ticket['price'] = 0;
	$ticket['stake_amount'] = 0;
	$ticket['winning_amount'] = 0;
	$ticket['jackpot_amount'] = 0;
	$ticket['is_bonus'] = 0;
	$ticket['status'] = 0;
	$ticket['status_text'] = '';
	return $ticket;
}




//*******************************************************************************************************************
//*******************************************************************************************************************
// API CALIMACO
//*******************************************************************************************************************
//*******************************************************************************************************************
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
			AND t.body = '$body' 
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

function api_calimaco_get_url_sportbook($client_id, $balance, $timestamp, $calimaco_id){
	global $mysqli;
	global $login;

	$num_doc = '';
	$query = "
		SELECT
			IFNULL(ct.num_doc, '') num_doc
		FROM
			tbl_televentas_clientes ct
		WHERE
			ct.id = $client_id 
		";
	$list_query = $mysqli->query($query);
	while ($li = $list_query->fetch_assoc()) { 
		$num_doc = $li['num_doc'];
	}

	$balance = $balance * 100;
	$msj = $client_id . $balance . $timestamp;
	$clavesecreta = env('CALIMACO_TLS_PASSWORD');

	$hash_encryp = urlencode(base64_encode(hash_hmac('sha256', $msj, $clavesecreta, true)));
	$url = env('CALIMACO_TLS_URL')."new_session";
	$url .= "?external_id=" . $client_id . "&dni=" . $num_doc . "&balance=" . $balance . "&timestamp=" . $timestamp . "&hash=" . $hash_encryp;

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
			client_id,
			body,
			turno_id,
			cc_id,
			status,
			user_id,
			created_at
		) VALUES (
			'new_session',
			'" . $client_id . "',
			'" . $url . "',
			'" . $turno_id . "',
			'" . $cc_id . "',
			'1',
			'" . $user_id . "',
			'" . $date_time . "'
		)";
	$mysqli->query($insert_command);

	if(strlen($calimaco_id)===0){
	    try {
			$curl = curl_init();
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $url,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => '',
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => 'GET',
			));
			$response = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
	    } catch (Exception $e) {
	    	$response = 'Excepción capturada: '.  $e->getMessage();
	    }
	}

	return $url;
}

function api_calimaco_get_calimaco_id($client_id){
	global $mysqli;
	global $login;

	$calimaco_id = '';

    try {
		$url = "https://api.apuestatotal.com/v2/calimaco/getClientId";
		$rq = ['external_id' => $client_id];
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
			$result["result"] = $response;
			$result["error"] = "cURL Error #:" . $err;
		} else {
			if(isset($response_arr["http_code"])){
				if((int)$response_arr["http_code"]===200){
					if(isset($response_arr["result"]["user"])){
						if((int)$response_arr["result"]["user"]>0){
							$calimaco_id = $response_arr["result"]["user"];

							$query_update = "
								UPDATE tbl_televentas_clientes 
								SET 
									calimaco_id = '" . $calimaco_id . "'
								WHERE id = '" . $client_id . "'
								";
							$mysqli->query($query_update);
						}
					}
				}
			}
		}
    } catch (Exception $e) {
    	$response = 'Excepción capturada: '.  $e->getMessage();
    }
	return $calimaco_id;
}

function api_calimaco_get_bet($client_id, $bet_id){
	$msj = $client_id . $bet_id;
	$clavesecreta = env('CALIMACO_TLS_PASSWORD');

	$hash_encryp = urlencode(base64_encode(hash_hmac('sha256', $msj, $clavesecreta, true)));
	$url = env('CALIMACO_TLS_URL')."get_bet";
	$url .= "?external_id=" . $client_id . "&game=" . $bet_id . "&hash=" . $hash_encryp;

    $auditoria_id = api_calimaco_auditoria_insert('get_bet', $bet_id, $client_id, $url);

    $ticket = query_tbl_televentas_tickets_get_array();

    $response = null;
	$result = array();
	$status = 0;
	$txn_id = $bet_id;

    try {
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		));
		$response = curl_exec($curl);
		$err = curl_error($curl);
		$response_arr = json_decode($response, true);
		curl_close($curl);

		if ($err) {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al consumir el API.";
			$result["result"] = $response;
			$result["error"] = "cURL Error #:" . $err;
		} else {
			$result = $response_arr;
			if(isset($result["result"])){
				if(isset($result["bet"]["details"]["data"])){
					$result["bet"]["details"]["data"]=null;
					$response = json_encode($result, JSON_PRETTY_PRINT);
					//echo $response;
				}
				if($result["result"]==="OK"){
					$result["bet"]["created_date"] = date('Y-m-d H:i:s', strtotime ('-5 hour' , strtotime($result["bet"]["created_date"])));
					$result["bet"]["resolved_date"] = strlen($result["bet"]["resolved_date"])===19 ? date('Y-m-d H:i:s', strtotime ('-5 hour' , strtotime($result["bet"]["resolved_date"]))): '';
					$status = 1;
					$local_id = 0;
					$turno = get_turno();
					if (count($turno) > 0) {
						$local_id = $turno[0]["local_id"];
					}
					$ticket['ticket_id'] = $bet_id;
					$ticket['proveedor_id'] = '5';
					$ticket['created'] = $result["bet"]["created_date"];
					$ticket['calc_date'] = $result["bet"]["resolved_date"];
					$ticket['paid_date'] = '';
					$ticket['external_id'] = '';
					$ticket['game'] = '';
					$ticket['sell_local_id'] = $local_id;
					$ticket['paid_local_id'] = 0;
					$ticket['num_selections'] = $result["bet"]["total_selections"];
					$ticket['price'] = $result["bet"]["odds"];
					$ticket['stake_amount'] = $result["bet"]["wager"]/100;
					$ticket['winning_amount'] = ($result["bet"]["winning"]>0) ? $result["bet"]["winning"]/100 : 0;
					$ticket['jackpot_amount'] = 0;
					if($result["bet"]["is_bonus"] === true) {
						$ticket['is_bonus'] = 1;
					}
					$ticket['status'] = calimaco_get_status($result["bet"]["status"])['status_id'];
					$ticket['status_text'] = $result["bet"]["status"];
					$ticket_query = query_tbl_televentas_tickets($ticket);
					//$result['ticket'] = $ticket;
					//$result['ticket_query'] = $ticket_query;
				}
			}
		}
    } catch (Exception $e) {
    	$response = 'Excepción capturada: '.  $e->getMessage();
    }
    api_calimaco_auditoria_update($auditoria_id, $txn_id, $response, $status);
	//api_calimaco_auditoria('get_bet', $bet_id, $client_id, $url, $response, $status, $hash_encryp);
	return $result;
}

function api_calimaco_set_bet($client_id, $bet_id){
	$msj = $client_id . $bet_id;
	$clavesecreta = env('CALIMACO_TLS_PASSWORD');

	$hash_encryp = urlencode(base64_encode(hash_hmac('sha256', $msj, $clavesecreta, true)));
	$url = env('CALIMACO_TLS_URL')."set_bet_paid";
	$url .= "?external_id=" . $client_id . "&game=" . $bet_id . "&hash=" . $hash_encryp;

    $auditoria_id = api_calimaco_auditoria_insert('set_bet_paid', $bet_id, $client_id, $url);

    $ticket = query_tbl_televentas_tickets_get_array();

    $response = null;
	$result = array();
	$status = 0;
	$txn_id = $bet_id;

	if((int)$auditoria_id>0){

	    try {
			$curl = curl_init();
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $url,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => '',
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => 'GET',
			));
			$response = curl_exec($curl);
			$err = curl_error($curl);
			$response_arr = json_decode($response, true);
			curl_close($curl);

			if ($err) {
				$result["http_code"] = 400;
				$result["status"] = "Ocurrió un error al consumir el API.";
				$result["result"] = $response;
				$result["error"] = "cURL Error #:" . $err;
			} else {
				$result = $response_arr;
				if(isset($response_arr["result"])){
					if($result["result"]==="OK"){
						$ticket['ticket_id'] = $bet_id;
						$ticket['proveedor_id'] = '5';
						$status = 1;
						$turno = get_turno();
						if (count($turno) > 0) {
							$ticket['paid_local_id'] = $turno[0]["local_id"];
						}
						date_default_timezone_set("America/Lima");
						$date_time = date('Y-m-d H:i:s');
						$ticket['paid_date'] = $date_time;
						$ticket_query = query_tbl_televentas_tickets($ticket);
						$result['ticket'] = $ticket;
						$result['ticket_query'] = $ticket_query;
					}
				}
			}
	    } catch (Exception $e) {
	    	$response = 'Excepción capturada: '.  $e->getMessage();
	    }
	    api_calimaco_auditoria_update($auditoria_id, $txn_id, $response, $status);
		//api_calimaco_auditoria('set_bet_paid', $bet_id, $client_id, $url, $response, $status, $hash_encryp);

	}
	return $result;
}

function api_calimaco_recarga_web($client_id, $web_id, $amount, $operacionId){
	//$method = 'Teleservicios';
	$method = 'TeleServicios_AT';
	$msj = $amount . ':' . $web_id . ':' . $method . ':' . $operacionId;
	$clavesecreta = env('CALIMACO_RECARGAS_PASSWORD');
	$hash_encryp = hash_hmac('sha256', $msj, $clavesecreta, false);

	$url = env('CALIMACO_RECARGAS_URL') . $method;
	$url .= "&amount=" . $amount;
	$url .= "&user=" . $web_id;
	//$url .= "&operationId=" . $operacionId;
	$url .= "&at_operation=" . $operacionId;
	$url .= "&hash=" . strtoupper($hash_encryp);

    $auditoria_id = api_calimaco_auditoria_insert($method, $operacionId, $client_id, $url);

    $response = null;
	$result = array();
	$status = 0;
	$txn_id = $operacionId;

	if((int)$auditoria_id>0){
	    try {
			$curl = curl_init();
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $url,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => '',
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 0,
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => 'GET',
			));
			$response = curl_exec($curl);
			$err = curl_error($curl);
			$response_arr = json_decode($response, true);
			curl_close($curl);

			if ($err) {
				$result["http_code"] = 400;
				$result["status"] = "Ocurrió un error al consumir el API.";
				$result["result"] = $response;
				$result["error"] = "cURL Error #:" . $err;
			} else {
				$result = $response_arr;
				if(isset($response_arr["result"])){
					$status = ($response_arr["result"]==="OK") ? 1 : 0;
				}
			}
	    } catch (Exception $e) {
	    	$response = 'Excepción capturada: '.  $e->getMessage();
	    }
	    api_calimaco_auditoria_update($auditoria_id, $txn_id, $response, $status);
		//api_calimaco_auditoria($method, $operacionId, $client_id, $url, $response, $status, $hash_encryp);
	}
	return $result;
}

function api_calimaco_asig_bono($client_id, $web_id, $bono, $amount, $timestamp){
	$msj = $amount . ":" . $web_id . ":" . $bono . ":" . $timestamp;
	$clavesecreta = env('CALIMACO_RECARGAS_PASSWORD');
	$hash_encryp = hash_hmac('sha256', $msj, $clavesecreta, false);

	$url = env('CALIMACO_RECARGAS_URL')."manualPromotion";
	$url .= "&user=" . $web_id;
	$url .= "&promotionId=" . $bono;
	$url .= "&amount=" . $amount;
	$url .= "&timestamp=" . $timestamp;
	$url .= "&hash=" . $hash_encryp;

    $auditoria_id = api_calimaco_auditoria_insert('manualPromotion', 0, $client_id, $url);

    $response = null;
	$result = array();
	$status = 0;
	$txn_id = 0;

    try {
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		));
		$response = curl_exec($curl);
		$err = curl_error($curl);
		$response_arr = json_decode($response, true);
		curl_close($curl);

		if ($err) {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al consumir el API.";
			$result["result"] = $response;
			$result["error"] = "cURL Error #:" . $err;
		} else {
			$result = $response_arr;
			if(isset($response_arr["result"])){
				$status = ($response_arr["result"]==="OK") ? 1 : 0;
			}
		}
    } catch (Exception $e) {
    	$response = 'Excepción capturada: '.  $e->getMessage();
    }
    api_calimaco_auditoria_update($auditoria_id, $txn_id, $response, $status);
	//api_calimaco_auditoria('manualPromotion', 0, $client_id, $url, $response, $status, $hash_encryp);
	return $result;
}

function api_calimaco_check_user($client_id, $web_id){
	$method = 'checkUser';

	$msj = $web_id;
	$clavesecreta = env('CALIMACO_RECARGAS_PASSWORD');
	$hash_encryp = hash_hmac('sha256', $msj, $clavesecreta, false);

	$url = env('CALIMACO_RECARGAS_URL') . $method;
	$url .= "&hash=" . $hash_encryp;
	$url .= "&user=" . $web_id;

    $auditoria_id = api_calimaco_auditoria_insert('checkUser', 0, $client_id, $url);

    $response = null;
	$result = array();
	$status = 0;
	$txn_id = 0;

    try {
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		));
		$response = curl_exec($curl);
		$err = curl_error($curl);
		$response_arr = json_decode($response, true);
		curl_close($curl);

		if ($err) {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al consumir el API.";
			$result["result"] = $response;
			$result["error"] = "cURL Error #:" . $err;
		} else {
			$result = $response_arr;
			if(isset($response_arr["result"])){
				$status = ($response_arr["result"]==="OK") ? 1 : 0;
			}
		}
    } catch (Exception $e) {
    	$response = 'Excepción capturada: '.  $e->getMessage();
    }
    api_calimaco_auditoria_update($auditoria_id, $txn_id, $response, $status);
	//api_calimaco_auditoria($method, 0, $client_id, $url, $response, $status, $hash_encryp);
	return $result;
}


function api_calimaco_get_web_id($num_doc){
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

function calimaco_get_status($status_text){
	$result = array();
	$status_id = '';
	$status = '';
	$color = '';
	switch ($status_text) {
		case 'OPEN':
			$status = 'PENDIENTE';
			$color = "black";
			$status_id = 0;
			break;
		case 'WON':
			$status = 'GANADO';
			$color = "green";
			$status_id = 1;
			break;
		case 'WON_PAID':
			$status = 'GANADO';
			$color = "green";
			$status_id = 1;
			break;
		case 'LOST':
			$status = 'PERDIDO';
			$color = "red";
			$status_id = 2;
			break;
		case 'VOIDED':
			//$status='RECHAZADO';
			$status = 'RETORNADO';
			$color = "red";
			$status_id = 3;
			break;
		case 'VOID':
			//$status='RECHAZADO';
			$status = 'RETORNADO';
			$color = "red";
			$status_id = 3;
			break;
		case 'REJECTED':
			//$status='RECHAZADO';
			$status = 'CANCELADO';
			$color = "red";
			$status_id = 3;
			break;
		case 'REJECTED_PAID':
			//$status='RECHAZADO';
			$status = 'CANCELADO';
			$color = "red";
			$status_id = 3;
			break;
		case 'PAID':
			$status = 'PAGADO';
			$color = "blue";
			$status_id = 0;
			break;
		default:
			$status = '-';
			$color = "red";
			$status_id = 0;
	}
	$result["status"] = $status;
	$result["color"] = $color;
	$result["status_id"] = $status_id;
	return $result;
}


//*******************************************************************************************************************
//*******************************************************************************************************************
// API GR
//*******************************************************************************************************************
//*******************************************************************************************************************
function api_goldenrace_get_ticket($ticket_id){
	global $mysqli;

	$API_header = [];
	$API_header["apiId"] = "33476";
	$API_header["apiHash"] = "ad926faacfab1b77b272363e9989f0c3";
	$API_header["apiDomain"] = "ApuestaTotal";
	$API_header["entityId"] = "33475";

	$request_headers = array(
		'apiId: '.$API_header["apiId"],
		'apiHash: '.$API_header["apiHash"],
		'apiDomain: '.$API_header["apiDomain"]
	  );
	
	$url = 'https://america-api.virtustec.com:8383/api/external/v2/ticket/findById?entityId='.$API_header["entityId"].'&ticketId='.$ticket_id;

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL,$url);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_ENCODING, '');
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
	$response = curl_exec($curl);
	curl_close($curl);

    $res_ticket = query_tbl_televentas_tickets_get_array();
	$result = array();

	$tickets = json_decode($response, true);

	if(!empty($tickets)) {
		$ticket = $tickets;
		if(isset($ticket["ticketId"])) {

			$locales = [];
			$res_locales = $mysqli->query("
				SELECT l.id, lp.proveedor_id 
				FROM tbl_locales l
				INNER JOIN tbl_local_proveedor_id lp ON (lp.local_id = l.id)
				WHERE lp.estado = 1 
					AND lp.canal_de_venta_id =21
					AND lp.nombre = 'v2'
					AND lp.proveedor_id IS NOT NULL
				ORDER BY lp.proveedor_id DESC
			");
			while($r = $res_locales->fetch_assoc()) $locales[$r["proveedor_id"]] = $r["id"];

			$juegos = [];
			$res_juegos = $mysqli->query("SELECT nombre,abreviatura FROM tbl_juegos");
			while($r = $res_juegos->fetch_assoc()) $juegos[$r["abreviatura"]] = $r["nombre"];

			$query = "
				INSERT INTO tbl_repositorio_tickets_goldenrace (
					ticket_id,
					staff_id,
					unit_id,
					local_id,
					time_played,
					event_id,
					game,
					stake_amount,
					currency,
					ticket_status,
					winning_amount,
					jackpot,
					paid_out_time,
					result,
					created_at,
					updated_at,
					version
				) 
				VALUES 
				";

			$paid_out_time = (isset($ticket["timePaid"]) ? '"'.date('Y-m-d H:i:s', strtotime($ticket["timePaid"])).'"' : "NULL");
			//CONDICIONAL
			$game_type = $ticket["gameType"][0];
			if($game_type === "CH"){
				if($ticket["details"]["events"][0]["data"]["competitionSubType"] == "WORLDCUP"){
					$game_type = "World Cup";
				} else if($ticket["details"]["events"][0]["data"]["competitionSubType"] == "CHAMPION"){
					$game_type = "Champions 2022";
				} else if($ticket["details"]["events"][0]["data"]["competitionSubType"] == "LIBERTADORES"){
					$game_type = "Libertadores 2020";
				} else if($ticket["details"]["events"][0]["data"]["competitionSubType"] == "LEAGUE"){
					$game_type = "League";
				} else {
					$game_type = "Champions 2022";
				}
			} else if ($game_type === "SN") {
				if(substr(strtoupper($ticket["details"]["events"][0]["playlistDescription"]), 0, 15) === "SPIN2WIN ROYALE"){
					$game_type = "Spin2Win Royale";
				} else {
					$game_type = isset($juegos[strtolower($ticket["gameType"][0])]) ? $juegos[strtolower($ticket["gameType"][0])] : $ticket["gameType"][0];
				}
			} else {
				$game_type = isset($juegos[strtolower($ticket["gameType"][0])]) ? $juegos[strtolower($ticket["gameType"][0])] : $ticket["gameType"][0];
			}
			
			$query .= ' (
				'.$ticket["ticketId"].',
				"'.$ticket["sellStaff"]["id"].'",
				"'.$ticket["unit"]["id"].'",
				'.(isset($locales[$ticket["unit"]["id"]]) ? $locales[$ticket["unit"]["id"]] : "NULL").',
				"'.date('Y-m-d H:i:s', strtotime($ticket["timeRegister"])).'",
				'.$ticket["details"]["events"][0]["eventId"].',
				"'.$game_type.'",
				'.$ticket["stake"].',
				"'.$ticket["currency"]["code"].'",
				"'.$ticket["status"].'",
				'.(isset($ticket["wonData"]) ? $ticket["wonData"]["wonAmount"] : "0").',
				'.(isset($ticket["wonData"]) ? $ticket["wonData"]["wonJackpot"] : "0").',
				'.$paid_out_time.',
				"",
				NOW(),
				NOW(),
				2
			) ';
			$query .= " ON DUPLICATE KEY UPDATE 
				local_id=VALUES(local_id),
				ticket_status=VALUES(ticket_status),
				paid_out_time=VALUES(paid_out_time),
				game=VALUES(game),
				time_played=VALUES(time_played),
				event_id=VALUES(event_id),
				winning_amount=VALUES(winning_amount),
				jackpot=VALUES(jackpot),
				updated_at=NOW(),
				version=2
			";
			if (!$mysqli->query($query)) { 
				printf("Errormessage: %s\n", $mysqli->error);
			}

			$res_ticket['ticket_id']      = $ticket["ticketId"];
			$res_ticket['proveedor_id']   = '3';
			$res_ticket['created']   = date('Y-m-d H:i:s', strtotime($ticket["timeRegister"]));
			$res_ticket['calc_date'] = (isset($ticket["timeResolved"]) ? date('Y-m-d H:i:s', strtotime($ticket["timeResolved"])) : '');
			$res_ticket['paid_date'] = (isset($ticket["timePaid"]) ? date('Y-m-d H:i:s', strtotime($ticket["timePaid"])) : '');
			$res_ticket['external_id']    = '';
			$res_ticket['game']           = $game_type;
			$res_ticket['sell_local_id']  = (isset($locales[$ticket["unit"]["id"]]) ? $locales[$ticket["unit"]["id"]] : 0);
			$res_ticket['paid_local_id']  = (isset($ticket["payStaff"]["id"]) ? (isset($locales[$ticket["payStaff"]["id"]]) ? $locales[$ticket["unit"]["id"]] : 0) : 0);
			$res_ticket['num_selections'] = 0;
			$res_ticket['price']          = 0;
			$res_ticket['stake_amount']   = $ticket["stake"];
			$res_ticket['winning_amount'] = (isset($ticket["wonData"]) ? $ticket["wonData"]["wonAmount"] : 0);
			$res_ticket['jackpot_amount'] = (isset($ticket["wonData"]) ? $ticket["wonData"]["wonJackpot"] : 0);
			$res_ticket['status']         = goldenrace_get_status($ticket["status"])['status_id'];
			$res_ticket['status_text']    = goldenrace_get_status($ticket["status"])['status'];
			query_tbl_televentas_tickets($res_ticket);
			$result = $res_ticket;
		}

	}
	return $result;
}

function goldenrace_get_status($status_text){
	$result = array();
	$status_id = '';
	$status = '';
	$color = '';
	switch ($status_text) {
		case 'OPEN':
			$status = 'PENDIENTE';
			$color = "black";
			$status_id = 0;
			break;
		case 'PENDING':
			$status = 'PENDIENTE';
			$color = "black";
			$status_id = 0;
			break;
		case 'WON':
			$status = 'GANADO';
			$color = "green";
			$status_id = 1;
			break;
		case 'LOST':
			$status = 'PERDIDO';
			$color = "red";
			$status_id = 2;
			break;
		case 'REJECTED':
			//$status='RECHAZADO';
			$status = 'RETORNADO';
			$color = "red";
			$status_id = 3;
			break;
		case 'PAID':
			$status = 'PAGADO';
			$color = "blue";
			$status_id = 0;
			break;
		case 'PAIDOUT':
			$status = 'PAGADO';
			$color = "blue";
			$status_id = 0;
			break;
		case 'PAID OUT':
			$status = 'PAGADO';
			$color = "blue";
			$status_id = 0;
			break;
		case 'CANCELLED':
			$status = 'CANCELADO';
			$color = "red";
			$status_id = 4;
			break;
		case 'EXPIRED':
			$status = 'EXPIRADO';
			$color = "red";
			$status_id = 4;
			break;
		case 'LOCKED':
			$status = 'BLOQUEADO';
			$color = "red";
			$status_id = 4;
			break;
		case null:
			$status = '';
			$color = "red";
			$status_id = 4;
			break;
		default:
			$status = '-';
			$color = "red";
			$status_id = 0;
	}
	$result["status"] = $status;
	$result["color"] = $color;
	$result["status_id"] = $status_id;
	return $result;
}


//*******************************************************************************************************************
//*******************************************************************************************************************
// API BC
//*******************************************************************************************************************
//*******************************************************************************************************************
function api_betconstruct_get_bet($id_bet){

    $ticket = query_tbl_televentas_tickets_get_array();

	$url = "https://api.apuestatotal.com/v2/betconstruct/getBets";
	$rq = ['BetId' => $id_bet];
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

	$result = array();
	if ($err) {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consumir el API.";
		$result["result"] = $response;
		$result["error"] = "cURL Error #:" . $err;
	} else {
		$result = $response_arr;
		if(isset($result["http_code"])){
			if((int)$result["http_code"]===200){
				$detalle = $result["result"][0];
				$ticket['ticket_id'] = $detalle["bet_id"];
				$ticket['proveedor_id'] = '1';
				$ticket['created'] = $detalle["created"];
				$ticket['calc_date'] = strlen($detalle["calc_date"])===19 ? $detalle["calc_date"]: '';
				$ticket['paid_date'] = strlen($detalle["paid_date"])===19 ? $detalle["paid_date"]: '';;
				$ticket['external_id'] = '';
				$ticket['game'] = '';
				$ticket['sell_local_id'] = $detalle["local_id"];
				$ticket['paid_local_id'] = $detalle["paid_local_id"];
				$ticket['num_selections'] = $detalle["selection_count"];
				$ticket['price'] = $detalle["price"];
				$ticket['stake_amount'] = $detalle["amount"];
				$ticket['winning_amount'] = $detalle["winning"];
				$ticket['jackpot_amount'] = 0;
				$ticket['status'] = betconstruct_get_status($detalle["state"])['status_id'];
				query_tbl_televentas_tickets($ticket);
			}
		}
	}
	/*
	if(count($result)===0){
		global $mysqli;
		global $login;
		$query = "
			SELECT
				IFNULL(ct.num_doc, '') num_doc
			FROM
				tbl_televentas_clientes ct
			WHERE
				ct.id = $client_id 
			";
		$list_query = $mysqli->query($query);
		$list = array();
		while ($li = $list_query->fetch_assoc()) { 
			$list[] = $li;
		}

	}
	*/
	return $result;
}

function api_betconstruct_get_terminaldoc($cod){
	$url = "https://api.apuestatotal.com/v2/betconstruct/GetTerminalTicketDocuments";
	$rq = ['DocumentId' => $cod];
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

	$result = array();
	if ($err) {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consumir el API.";
		$result["result"] = $response;
		$result["error"] = "cURL Error #:" . $err;
	} else {
		$result = $response_arr;
	}
	return $result;
}

function betconstruct_get_status($status_param){
	$result = array();
	$status_id = '';
	$status = '';
	switch ($status_param) {
		case '-3':
			$status = 'Rollbacked';
			$status_id = 4;
			break;
		case '-2':
			$status = 'Waiting partner';
			$status_id = 0;
			break;
		case '-1':
			$status = 'Rechazado';
			$status_id = 3;
			break;
		case '0':
			$status = 'Pendiente';
			$status_id = 0;
			break;
		case '1':
			$status = 'Aceptado';
			$status_id = 0;
			break;
		case '2':
			$status = 'Retornado';
			$status_id = 3;
			break;
		case '3':
			$status = 'Perdido';
			$status_id = 2;
			break;
		case '4':
			$status = 'Ganado';
			$status_id = 1;
			break;
		case '5':
			$status = 'CashOut';
			$status_id = 5;
			break;
		default:
			$status = '-';
			$status_id = 0;
	}
	$result["status"] = $status;
	$result["status_id"] = $status_id;
	return $result;
}


//*******************************************************************************************************************
//*******************************************************************************************************************
// API BINGO
//*******************************************************************************************************************
//*******************************************************************************************************************
function api_bingo_get_bet($id_bet){
	global $mysqli;
    
    $ticket = query_tbl_televentas_tickets_get_array();
	$result = array();

	$query = "
		SELECT 
			t.id, 
			t.ticket_id, 
			IFNULL(t.created, '') created,
			IFNULL(t.sell_local_id, 0) sell_local_id,
			ROUND(t.amount,1) as monto,
			UPPER(IFNULL(l1.nombre, '')) AS local_creado,
			IFNULL(t.paid_at, '') paid_at,
			IFNULL(t.paid_local_id, 0) paid_local_id,
			IFNULL(t.winning, 0) winning,
			IFNULL(t.jackpot_amount, 0) jackpot_amount,
			UPPER(IFNULL(l2.nombre, '')) AS local_pago,
			UPPER(t.status) status
		FROM tbl_repositorio_bingo_tickets t 
		LEFT JOIN tbl_locales l1 ON t.sell_local_id = l1.cc_id 
		LEFT JOIN tbl_locales l2 ON t.paid_local_id = l2.cc_id 
		WHERE 
			t.ticket_id = '" . $id_bet . "' 
		LIMIT 1 
		";
	$list_query = $mysqli->query($query);
	$list_res = array();
	if ($mysqli->error) {
		$result["select_error"] = $mysqli->error;
		$result["select_query"] = $query;
		//echo json_encode($result);exit();
	} else {
		while ($li_ticket = $list_query->fetch_assoc()) {
			$list_res[] = $li_ticket;
		}
		if (count($list_res) > 0){
			$res = $list_res[0];
			$findme = 'TELEVENTAS';
			$valid_local_creado = strpos($res["local_creado"], $findme);
			$valid_local_pago = strpos($res["local_pago"], $findme);

			$ticket['ticket_id']      = $id_bet;
			$ticket['validate_tls_creado'] = 0;
			$ticket['validate_tls_pagado'] = 0;
			$ticket['local_creado'] = $res["local_creado"];
			$ticket['local_pago'] = $res["local_pago"];
			
			if (!($valid_local_creado === false) || !($valid_local_pago === false)) {
				if($res["monto"] >= 0.95 && $res["monto"] <= 0.99) {
					$res["monto"] = 1;
				}
				if(!($valid_local_creado === false)) {
					$ticket['validate_tls_creado'] = 1;
				}
				if(!($valid_local_pago === false)) {
					$ticket['validate_tls_pagado'] = 1;
				}

				$ticket['proveedor_id']   = '4';
				$ticket['created']   = date('Y-m-d H:i:s', strtotime($res["created"]));
				$ticket['calc_date'] = '';
				$ticket['paid_date'] = ((strlen($res["paid_at"])>0) ? date('Y-m-d H:i:s', strtotime($res["paid_at"])) : '');
				$ticket['external_id']    = '';
				$ticket['game']           = '';
				$ticket['sell_local_id']  = $res["sell_local_id"];
				$ticket['paid_local_id']  = $res["paid_local_id"];
				$ticket['num_selections'] = 0;
				$ticket['price']          = 0;
				$ticket['stake_amount']   = $res["monto"];
				$ticket['winning_amount'] = $res["winning"];
				$ticket['jackpot_amount'] = $res["jackpot_amount"];
				$ticket['status']         = bingo_get_status($res["status"])['status_id'];
				$ticket['status_text']    = bingo_get_status($res["status"])['status'];
				query_tbl_televentas_tickets($ticket);
			}
			$result = $ticket;
		}
	}
	return $result;
}
function bingo_get_status($status_text){
	$result = array();
	$status_id = '';
	$status = '';
	$color = '';
	switch ($status_text) {
		case 'OPEN':
			$status = 'PENDIENTE';
			$color = "black";
			$status_id = 0;
			break;
		case 'PENDING':
			$status = 'PENDIENTE';
			$color = "black";
			$status_id = 0;
			break;
		case 'WON':
			$status = 'GANADO';
			$color = "green";
			$status_id = 1;
			break;
		case 'LOST':
			$status = 'PERDIDO';
			$color = "red";
			$status_id = 2;
			break;
		case 'REFUNDED':
			//$status='RECHAZADO';
			$status = 'REINTEGRADO';
			$color = "red";
			$status_id = 3;
			break;
		case 'PAID':
			$status = 'PAGADO';
			$color = "blue";
			$status_id = 0;
			break;
		case 'PAIDOUT':
			$status = 'PAGADO';
			$color = "blue";
			$status_id = 0;
			break;
		case 'PAID OUT':
			$status = 'PAGADO';
			$color = "blue";
			$status_id = 0;
			break;
		case 'EXPIRED':
			$status = 'EXPIRADO';
			$color = "red";
			$status_id = 4;
			break;
		case null:
			$status = '';
			$color = "red";
			$status_id = 4;
			break;
		default:
			$status = '-';
			$color = "red";
			$status_id = 0;
	}
	$result["status"] = $status;
	$result["color"] = $color;
	$result["status_id"] = $status_id;
	return $result;
}



//*******************************************************************************************************************
//*******************************************************************************************************************
// REGISTRAR NOMBRE TITULAR ABONO
//*******************************************************************************************************************
//*******************************************************************************************************************


if ($_POST["accion"] === "guardar_titular_abono") {
	include("function_replace_invalid_caracters.php");

	$dni_titular = $_POST["dni_titular"];
	$nombre_titular = strtoupper(replace_invalid_caracters(trim($_POST["nombre_titular"])));
	$id_cliente = $_POST["id_cliente"];

	$query_update_titular = "
	UPDATE tbl_televentas_titular_abono 
	SET 
	estado = 0 
	WHERE id_cliente = '" . $id_cliente. "' and estado = 1";
	$mysqli->query($query_update_titular);

	$query_ee = "
		SELECT 
		id 
		FROM tbl_televentas_titular_abono
		WHERE id_cliente ='".$id_cliente."' AND dni_titular ='".$dni_titular."'
		AND estado in (2)
		ORDER BY id ASC";
					
		$list_query_ee = $mysqli->query($query_ee);
		$list_ee = array();
		if ($mysqli->error) {
			$result["query_1_error"] = $mysqli->error;
		} else {
			while ($li_ee = $list_query_ee->fetch_assoc()) {
				$list_ee[] = $li_ee;
			}
		}
	
		if (count($list_ee) == 0) {

			$query_insert_dni = "
			INSERT INTO tbl_televentas_titular_abono (
			id_cliente,
			dni_titular,
			nombre_apellido_titular,
			estado,
			id_cajero,
			created_at ) VALUES (
			$id_cliente,
			'" . $dni_titular . "',
			'" . $nombre_titular . "',
			1,
			'" . $usuario_id . "',
			now()
			);
			";
			$mysqli->query($query_insert_dni);
			if ($mysqli->error) {
			$result["query_insert_dni"] = $mysqli->error;
			}

		}else{
			$query_update_titular_eliminado = "
			UPDATE tbl_televentas_titular_abono 
			SET 
			nombre_apellido_titular = '" . $nombre_titular . "',
			estado = 1
			WHERE id_cliente ='".$id_cliente."' AND dni_titular ='".$dni_titular."'";
			$mysqli->query($query_update_titular_eliminado);
		}

		$query_t = "
			SELECT 
			id 
			FROM tbl_televentas_titular_abono
			WHERE id_cliente ='".$id_cliente."' AND dni_titular ='".$dni_titular."'
			AND estado in (0,1)
			ORDER BY id ASC";
						
			$list_query_t = $mysqli->query($query_t);
			$list_t = array();
			if ($mysqli->error) {
				$result["query_1_error"] = $mysqli->error;
			} else {
				while ($li_t = $list_query_t->fetch_assoc()) {
					$list_t[] = $li_t;
				}
			}
		
			if (count($list_t) == 0) {
				$result["http_code"] = 400;
				$result["status"] = "Ocurrio un error al insertar datos"; 
				$result["result"] = $list_t;
			} else {
				$result["http_code"] = 200;
				$result["status"] = "ok";
				$result["result"] = $list_t;
			}
}

//*******************************************************************************************************************
//*******************************************************************************************************************
// CONSULTA TITULAR ABONO API
//*******************************************************************************************************************
//*******************************************************************************************************************

if ($_POST["accion"] === "busqueda_titular_abono_api") {

	include("function_replace_invalid_caracters.php");

	$dni_titular = $_POST["dni_titular"];
	$id_cliente = $_POST["id_cliente"];

	$result_api_dni = get_cliente_por_dni($dni_titular);

	if(!(isset($result_api_dni["dni"]))) {
		if(isset($result_api_dni["message"])) {	
			$result["http_code"] = 400;
			$result["status"] = "Cuotas no disponibles para la busqueda. Por favor comunicarse con el canal digital y/o soporte.";
			echo json_encode($result); exit();
		}
		$result["http_code"] = 401;
		$result["status"] = "No se encontro al DNI";
		echo json_encode($result); exit();
	}

	if ((int) $result_api_dni["dni"] === (int) $dni_titular) {
		$apellido_paterno_titular = strtoupper(replace_invalid_caracters(trim($result_api_dni["apellido_paterno"])));
		$apellido_materno_titular = strtoupper(replace_invalid_caracters(trim($result_api_dni["apellido_materno"])));
		$nombres_titular = strtoupper(replace_invalid_caracters(trim($result_api_dni["nombres"])));
		$nombre_apellido_titular = $nombres_titular.' '.$apellido_paterno_titular.' '.$apellido_materno_titular;

		$query_update_titular = "
		UPDATE tbl_televentas_titular_abono 
		SET estado = 0 
		WHERE id_cliente = '" . $id_cliente. "' and estado = 1
		";
		$mysqli->query($query_update_titular);

		$query_eex = "
			SELECT 
				id 
			FROM tbl_televentas_titular_abono
			WHERE id_cliente ='".$id_cliente."' AND dni_titular ='".$dni_titular."'
			AND estado in (2)
			ORDER BY id ASC";

			$list_query_eex = $mysqli->query($query_eex);
			$list_eex = array();
			if ($mysqli->error) {
				$result["query_1_error"] = $mysqli->error;
			} else {
				while ($li_eex = $list_query_eex->fetch_assoc()) {
								$list_eex[] = $li_eex;
				}
			}

			if (count($list_eex) == 0) {
				$query_insert_dni = "
				INSERT INTO tbl_televentas_titular_abono (
					id_cliente,
					dni_titular,
					nombre_apellido_titular,
					estado,
					id_cajero,
					created_at
					) VALUES (
					$id_cliente,
					'" . $dni_titular . "',
					'" . $nombre_apellido_titular . "',
					1,
					'" . $usuario_id . "',
					now()
					);
				";
				$mysqli->query($query_insert_dni);
					if ($mysqli->error) {
						$result["query_insert_dni"] = $mysqli->error;
					}
			}
			else{

				$query_update_titular_elim = "
				UPDATE tbl_televentas_titular_abono 
				SET  nombre_apellido_titular = '" . $nombre_apellido_titular . "',
				estado = 1
				WHERE id_cliente ='".$id_cliente."' AND dni_titular ='".$dni_titular."'";
				$mysqli->query($query_update_titular_elim);

			}						

			$query_t = "
				SELECT 
					id,
					id_cliente,
					dni_titular,
					nombre_apellido_titular ,
					estado
					FROM tbl_televentas_titular_abono
					WHERE id_cliente ='".$id_cliente."' AND dni_titular ='".$dni_titular."'
					AND estado in (0,1)
					ORDER BY id ASC
			";

			$list_query_t = $mysqli->query($query_t);
			$list_t = array();
			if ($mysqli->error) {
				$result["query_1_error"] = $mysqli->error;
			} else {
				while ($li_t = $list_query_t->fetch_assoc()) {
								$list_t[] = $li_t;
				}
			}

			if (count($list_t) > 0) {
				$result["http_code"] = 200;
				$result["status"] = "ok";
				$result["result"] = $list_t;
			}else{
				$result["http_code"] = 400;
				$result["status"] = "Ocurrió un error al registrar los datos";
				$result["result"] = $list_t;
			}

	}else{
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los registros";
		$result["result"] = $result_api_dni;

	}			


}

//*******************************************************************************************************************
//*******************************************************************************************************************
// CONSULTA TITULAR ABONO TBL
//*******************************************************************************************************************
//*******************************************************************************************************************

if ($_POST["accion"] === "busqueda_titular_abono_tbl_clientes") {
	include("function_replace_invalid_caracters.php");

	$dni_titular = $_POST["dni_titular"];
	$id_cliente = $_POST["id_cliente"];

	$query_existe = "SELECT 
				id
				FROM tbl_televentas_titular_abono
				WHERE id_cliente ='".$id_cliente."' and dni_titular='".$dni_titular."'
				AND estado in (0,1)
				ORDER BY id ASC";

				$list_query_e = $mysqli->query($query_existe);
				$list_e = array();
				if ($mysqli->error) {
					$result["query_existe_error"] = $mysqli->error;
				} else {
					while ($li_e = $list_query_e->fetch_assoc()) {
						$list_e[] = $li_e;
					}
				}


	if (count($list_e) == 0) {

		$query_1 = "
		SELECT 
			c.id,
			UPPER(IFNULL(c.nombre, '')) nombre,
			UPPER(IFNULL(c.apellido_paterno, '')) apellido_paterno,
			UPPER(IFNULL(c.apellido_materno, '')) apellido_materno,
			IFNULL(c.tipo_doc, '') tipo_doc,
			IFNULL(c.num_doc, '') dni_titular
		FROM tbl_televentas_clientes c
		WHERE num_doc ='".$dni_titular."'
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

			$query_existe_eliminado = "
					SELECT 
						id,
						id_cliente,
						dni_titular,
						nombre_apellido_titular ,
						estado
					FROM tbl_televentas_titular_abono
					WHERE id_cliente ='".$id_cliente."' and dni_titular='".$dni_titular."'
					AND estado in (2)
					ORDER BY id ASC
					";

				$list_query_ee = $mysqli->query($query_existe_eliminado);
				$list_ee = array();
				if ($mysqli->error) {
					$result["query_existe_error"] = $mysqli->error;
				} else {
					while ($li_ee = $list_query_ee->fetch_assoc()) {
						$list_ee[] = $li_ee;
					}
				}

				if (count($list_ee) == 0) {
					$result["http_code"] = 300;
					$result["status"] = "El documento no existe. Registrar";
				}else{
					$result["http_code"] = 301;
					$result["status"] = "El titular fue registrado anteriormente. Desea volver a registrar?";
					$result["result"] = $list_ee;
				}


		} elseif (count($list_1) > 0) {

			$query_etq_idmf = "SELECT 
					id, etiqueta_id
				FROM tbl_televentas_clientes_etiqueta
				WHERE etiqueta_id in (45,48) AND client_id ='".$list_1[0]["id"]."'  ";

			$list_query_etq_idmf = $mysqli->query($query_etq_idmf);
			$list_etq_idmf = array();
				if ($mysqli->error) {
					$result["query_etq_idmf"] = $mysqli->error;
				} else {
					while ($li_etq_idmf = $list_query_etq_idmf->fetch_assoc()) {
						$list_etq_idmf[] = $li_etq_idmf;
					}
				}

				 
				if (count($list_etq_idmf) > 0) {

					if($list_etq_idmf[0]["etiqueta_id"] == 45){
						$result["http_code"] = 400;
						$result["status"] = "El DNI pertenece a un menor de edad";

					}else{
						$result["http_code"] = 400;
						$result["status"] = "El DNI pertenece a un fallecido";

					}				 

				}else{
					
					$apellido_paterno_titular =  $list_1[0]["apellido_paterno"];
					$apellido_materno_titular =  $list_1[0]["apellido_materno"];
					$nombres_titular =  $list_1[0]["nombre"];
					$nombre_apellido_titular = $nombres_titular.' '.$apellido_paterno_titular.' '.$apellido_materno_titular;

					$query_update_titular = "
						UPDATE tbl_televentas_titular_abono 
						SET 
							estado = 0 
						WHERE id_cliente = '" . $id_cliente. "' and estado = 1
						";
						$mysqli->query($query_update_titular);


						$query_eed = "
						SELECT 
						id 
						FROM tbl_televentas_titular_abono
						WHERE id_cliente ='".$id_cliente."' AND dni_titular ='".$dni_titular."'
						AND estado in (2)
						ORDER BY id ASC";

						$list_query_eed = $mysqli->query($query_eed);
						$list_eed = array();
						if ($mysqli->error) {
							$result["query_1_error"] = $mysqli->error;
						} else {
							while ($li_eed = $list_query_eed->fetch_assoc()) {
								$list_eed[] = $li_eed;
							}
						}

						if (count($list_eed) == 0) {

							$query_insert_dni = "
										INSERT INTO tbl_televentas_titular_abono (
											id_cliente,
											dni_titular,
											nombre_apellido_titular,
											estado,
											id_cajero,
											created_at
										) VALUES (
											$id_cliente,
											'" . $dni_titular . "',
											'" . $nombre_apellido_titular. "',
											1,
											'" . $usuario_id. "',
											now()
										);
									";
									$mysqli->query($query_insert_dni);
									if ($mysqli->error) {
										$result["query_insert_dni"] = $mysqli->error;
									}

						}else{
							$query_update_titular_eliminado = "
							UPDATE tbl_televentas_titular_abono 
							SET 
							nombre_apellido_titular = '" . $nombre_apellido_titular . "',
							estado = 1
							WHERE id_cliente ='".$id_cliente."' AND dni_titular ='".$dni_titular."'";
							$mysqli->query($query_update_titular_eliminado);
						}



						$query_tf = "SELECT 
							id,
							id_cliente,
							dni_titular,
							nombre_apellido_titular ,
							estado
						FROM tbl_televentas_titular_abono
						WHERE id_cliente ='".$id_cliente."' AND dni_titular ='".$dni_titular."'
						AND estado in (0,1)
						ORDER BY id ASC";

						$list_query_tf = $mysqli->query($query_tf);
						$list_tf = array();
						if ($mysqli->error) {
							$result["query_1_error"] = $mysqli->error;
						} else {
							while ($li_tf = $list_query_tf->fetch_assoc()) {
								$list_tf[] = $li_tf;
								}
						}
						
						if (count($list_1) > 0) {

							$result["http_code"] = 200;
							$result["status"] = "ok";
							$result["result"] = $list_tf;
						}
						else{
							$result["http_code"] = 400;
							$result["status"] = "Ocurrió un error al insertar los datos";
						}

				}			


		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al consultar los registros";
		}

	}else{
		$result["http_code"] = 400;
		$result["status"] = "Titular ya registrado, solo debe seleccionarlo del listado";
	}

}

//*******************************************************************************************************************
//*******************************************************************************************************************
// REGISTRAR TITULAR ABONO
//*******************************************************************************************************************
//*******************************************************************************************************************


if ($_POST["accion"] === "obtener_televentas_titular_abono") {
	include("function_replace_invalid_caracters.php");

	$dni_titular = $_POST["dni_titular"];
	$id_cliente = $_POST["id_cliente"];

	$query_existe = "SELECT 
				id
				FROM tbl_televentas_titular_abono
				WHERE id_cliente ='".$id_cliente."' and dni_titular='".$dni_titular."'
				AND estado in (0,1)
				ORDER BY id ASC";
				
				$list_query_e = $mysqli->query($query_existe);
				$list_e = array();
				if ($mysqli->error) {
					$result["query_existe_error"] = $mysqli->error;
				} else {
					while ($li_e = $list_query_e->fetch_assoc()) {
						$list_e[] = $li_e;
					}
				}

	 
	if (count($list_e) == 0) {

		$query_1 = "
		SELECT 
			c.id,
			UPPER(IFNULL(c.nombre, '')) nombre,
			UPPER(IFNULL(c.apellido_paterno, '')) apellido_paterno,
			UPPER(IFNULL(c.apellido_materno, '')) apellido_materno,
			IFNULL(c.tipo_doc, '') tipo_doc,
			IFNULL(c.num_doc, '') dni_titular
		FROM tbl_televentas_clientes c
		WHERE num_doc ='".$dni_titular."'
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
			
			$result_api_dni = get_cliente_por_dni($dni_titular);

			
			if((!is_array($result_api_dni)) or (empty($result_api_dni))){

				$query_existe_eliminado = "
					SELECT 
						id,
						id_cliente,
						dni_titular,
						nombre_apellido_titular ,
						estado
					FROM tbl_televentas_titular_abono
					WHERE id_cliente ='".$id_cliente."' and dni_titular='".$dni_titular."'
					AND estado in (2)
					ORDER BY id ASC
					";
					
				$list_query_ee = $mysqli->query($query_existe_eliminado);
				$list_ee = array();
				if ($mysqli->error) {
					$result["query_existe_error"] = $mysqli->error;
				} else {
					while ($li_ee = $list_query_ee->fetch_assoc()) {
						$list_ee[] = $li_ee;
					}
				}
		
				if (count($list_ee) == 0) {
					$result["http_code"] = 300;
					$result["status"] = "El documento no existe. Registrar";
				}else{
					$result["http_code"] = 301;
					$result["status"] = "El titular fue registrado anteriormente. Desea volver a registrar?";
					$result["result"] = $list_ee;
				}			
			} else {

				if(!(isset($result_api_dni["dni"]))) {
					if(isset($result_api_dni["message"])) {	
						$result["http_code"] = 400;
						$result["status"] = "Cuotas no disponibles para la busqueda. Por favor comunicarse con el canal digital y/o soporte.";
						echo json_encode($result); exit();
					}
					$result["http_code"] = 400;
					$result["status"] = "No se encontro al DNI.";
					echo json_encode($result); exit();
				}

				if ((int) $result_api_dni["dni"] === (int) $dni_titular) {
					$apellido_paterno_titular = strtoupper(replace_invalid_caracters(trim($result_api_dni["apellido_paterno"])));
					$apellido_materno_titular = strtoupper(replace_invalid_caracters(trim($result_api_dni["apellido_materno"])));
					$nombres_titular = strtoupper(replace_invalid_caracters(trim($result_api_dni["nombres"])));
					$nombre_apellido_titular = $nombres_titular.' '.$apellido_paterno_titular.' '.$apellido_materno_titular;
		
					$query_update_titular = "
					UPDATE tbl_televentas_titular_abono 
					SET estado = 0 
					WHERE id_cliente = '" . $id_cliente. "' and estado = 1
					";
					$mysqli->query($query_update_titular);


					$query_eex = "
						SELECT 
							id 
						FROM tbl_televentas_titular_abono
						WHERE id_cliente ='".$id_cliente."' AND dni_titular ='".$dni_titular."'
							AND estado in (2)
						ORDER BY id ASC";
									
						$list_query_eex = $mysqli->query($query_eex);
						$list_eex = array();
						if ($mysqli->error) {
							$result["query_1_error"] = $mysqli->error;
						} else {
							while ($li_eex = $list_query_eex->fetch_assoc()) {
								$list_eex[] = $li_eex;
							}
						}
					
						if (count($list_eex) == 0) {
							$query_insert_dni = "
								INSERT INTO tbl_televentas_titular_abono (
									id_cliente,
									dni_titular,
									nombre_apellido_titular,
									estado,
									id_cajero,
									created_at
								) VALUES (
									$id_cliente,
									'" . $dni_titular . "',
									'" . $nombre_apellido_titular . "',
									1,
									'" . $usuario_id . "',
									now()
									);
								";
							$mysqli->query($query_insert_dni);
								if ($mysqli->error) {
									$result["query_insert_dni"] = $mysqli->error;
							}
						}
						else{

							$query_update_titular_elim = "
								UPDATE tbl_televentas_titular_abono 
								SET  nombre_apellido_titular = '" . $nombre_apellido_titular . "',
									estado = 1
								WHERE id_cliente ='".$id_cliente."' AND dni_titular ='".$dni_titular."'";
							$mysqli->query($query_update_titular_elim);

						}
						
		
						$query_t = "
							SELECT 
								id,
								id_cliente,
								dni_titular,
								nombre_apellido_titular ,
								estado
							FROM tbl_televentas_titular_abono
							WHERE id_cliente ='".$id_cliente."' AND dni_titular ='".$dni_titular."'
								AND estado in (0,1)
							ORDER BY id ASC
							";
						
						$list_query_t = $mysqli->query($query_t);
						$list_t = array();
						if ($mysqli->error) {
							$result["query_1_error"] = $mysqli->error;
						} else {
							while ($li_t = $list_query_t->fetch_assoc()) {
								$list_t[] = $li_t;
							}
						}

						if (count($list_t) > 0) {
							$result["http_code"] = 200;
							$result["status"] = "ok";
							$result["result"] = $list_t;
						}else{
							$result["http_code"] = 400;
							$result["status"] = "Ocurrió un error al registrar los datos";
							$result["result"] = $list_t;
						}
		
				}else{
					$result["http_code"] = 400;
					$result["status"] = "Ocurrió un error al consultar los registros";
					$result["result"] = $result_api_dni;
					
				}
				
			}

			
			
		} elseif (count($list_1) > 0) {

			$apellido_paterno_titular =  $list_1[0]["apellido_paterno"];
			$apellido_materno_titular =  $list_1[0]["apellido_materno"];
			$nombres_titular =  $list_1[0]["nombre"];
			$nombre_apellido_titular = $nombres_titular.' '.$apellido_paterno_titular.' '.$apellido_materno_titular;

			$query_update_titular = "
				UPDATE tbl_televentas_titular_abono 
				SET 
					estado = 0 
				WHERE id_cliente = '" . $id_cliente. "' and estado = 1
				";
				$mysqli->query($query_update_titular);


				$query_eed = "
				SELECT 
				id 
				FROM tbl_televentas_titular_abono
				WHERE id_cliente ='".$id_cliente."' AND dni_titular ='".$dni_titular."'
				AND estado in (2)
				ORDER BY id ASC";
							
				$list_query_eed = $mysqli->query($query_eed);
				$list_eed = array();
				if ($mysqli->error) {
					$result["query_1_error"] = $mysqli->error;
				} else {
					while ($li_eed = $list_query_eed->fetch_assoc()) {
						$list_eed[] = $li_eed;
					}
				}
			
				if (count($list_eed) == 0) {

					$query_insert_dni = "
								INSERT INTO tbl_televentas_titular_abono (
									id_cliente,
									dni_titular,
									nombre_apellido_titular,
									estado,
									id_cajero,
									created_at
								) VALUES (
									$id_cliente,
									'" . $dni_titular . "',
									'" . $nombre_apellido_titular. "',
									1,
									'" . $usuario_id. "',
									now()
								);
							";
							$mysqli->query($query_insert_dni);
							if ($mysqli->error) {
								$result["query_insert_dni"] = $mysqli->error;
							}

				}else{
					$query_update_titular_eliminado = "
					UPDATE tbl_televentas_titular_abono 
					SET 
					nombre_apellido_titular = '" . $nombre_apellido_titular . "',
					estado = 1
					WHERE id_cliente ='".$id_cliente."' AND dni_titular ='".$dni_titular."'";
					$mysqli->query($query_update_titular_eliminado);
				}
				
		
				
				$query_tf = "SELECT 
					id,
					id_cliente,
					dni_titular,
					nombre_apellido_titular ,
					estado
				FROM tbl_televentas_titular_abono
				WHERE id_cliente ='".$id_cliente."' AND dni_titular ='".$dni_titular."'
				AND estado in (0,1)
				ORDER BY id ASC";
					
				$list_query_tf = $mysqli->query($query_tf);
				$list_tf = array();
				if ($mysqli->error) {
					$result["query_1_error"] = $mysqli->error;
				} else {
					while ($li_tf = $list_query_tf->fetch_assoc()) {
						$list_tf[] = $li_tf;
						}
				}

				if (count($list_1) > 0) {

					$result["http_code"] = 200;
					$result["status"] = "ok";
					$result["result"] = $list_tf;
				}
				else{
					$result["http_code"] = 400;
					$result["status"] = "Ocurrió un error al insertar los datos";
				}
							
			
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al consultar los registros";
		}

	}else{
		$result["http_code"] = 400;
		$result["status"] = "Titular ya registrado, solo debe seleccionarlo del listado";
	}


}


//*******************************************************************************************************************
//*******************************************************************************************************************
// CARGAR TITULAR ABONO
//*******************************************************************************************************************
//*******************************************************************************************************************

if ($_POST["accion"] === "obtener_televentas_titular_abono_reg") {
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




//*******************************************************************************************************************
//*******************************************************************************************************************
// LISTADO ELIMINACION TITULAR ABONO
//*******************************************************************************************************************
//*******************************************************************************************************************

if ($_POST["accion"] === "listado_eliminar_titular_abono") {
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

	if (count($list_1) > 0) {	
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_1;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros";
		$result["result"] = $list_1;
	}

}


//*******************************************************************************************************************
//*******************************************************************************************************************
// ELIMINAR TITULAR ABONO
//*******************************************************************************************************************
//*******************************************************************************************************************

if ($_POST["accion"] === "eliminar_titular_abono") {
	include("function_replace_invalid_caracters.php");

	$id_cliente = $_POST["id_cliente"];
	$id_tit = $_POST["id_tit"];

	$query_update_titular = "
	UPDATE tbl_televentas_titular_abono 
	SET 
	estado = 2 
	WHERE id_cliente = '" . $id_cliente. "' and id = '" . $id_tit. "'";
	$mysqli->query($query_update_titular);

	$query_1 = "SELECT 
		id 
	FROM tbl_televentas_titular_abono
	WHERE id_cliente = '" . $id_cliente. "' and id = '" . $id_tit. "'
	AND estado = 2";

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
		$result["http_code"] = 200;
		$result["status"] = "Titular eliminado";
		$result["result"] = $list_1;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrio un error al eliminar";
		$result["result"] = $list_1;
	}

}


//*******************************************************************************************************************
//*******************************************************************************************************************
// VALIDAR SI CLIENTE EXISTE
//*******************************************************************************************************************
//*******************************************************************************************************************

if ($_POST["accion"] === "validar_cliente_televentas") {
	include("function_replace_invalid_caracters.php");

    $timestamp      = $_POST["timestamp"];
    $busqueda_tipo  = $_POST["tipo"];
    $busqueda_valor = $_POST["valor"];
    $hash           = $_POST["hash"];
	$result["mensaje"] = "";

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
				UPPER(IFNULL(c.web_full_name, '')) web_full_name,
				IFNULL(c.player_id, '') player_id,
				IFNULL(c.calimaco_id, '') calimaco_id,
				IFNULL(c.telefono, '') telefono,
				IFNULL(c.fec_nac, '') fec_nac,
				IFNULL(c.correo, '') correo,
				IFNULL(c.block_user_id, '') block_user_id,
				IFNULL(c.cc_id, '') cc_id,
				IFNULL(c.bono_limite, '10000') bono_limite,
				IFNULL(c.updated_at, '') updated_at,
				IFNULL(c.block_hash, '') block_hash,
				IFNULL(c.validate_web_id, 0) validate_web_id,
				1 tipo_balance_id,
				IFNULL(c.nacionalidad , '') nacionalidad,
				IFNULL(c.ubigeo , '') ubigeo,
				IFNULL(c.direccion , '') direccion,
				IFNULL(c.terminos_condiciones  , false) tyc,
				IFNULL(c.es_pep , '') es_pep,
				'1' clienteExistente
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

		
		$is_min = cliente_mincetur($busqueda_tipo, $busqueda_valor);
		$result["is_min"] = $is_min;
		if (count($list_1) == 0){
			$result["http_code"] = 400;
			$result["status"] = "Cliente no encontrado en la BD de televentas.";
			$result["result"] = $list_1;

		}else{
			$list_1[0]["is_disabled_mincetur"] = $is_min;
			if ((int) $list_1[0]["block_user_id"] === (int) $usuario_id ||
					(int) $list_1[0]["block_user_id"] === 0 ||
					(int) $list_1[0]["block_user_id"] === null) {

				query_tbl_televentas_clientes_block($list_1[0]["id"], 1);

	            $query_update = "UPDATE tbl_televentas_clientes
	                    		SET
	                            block_hash = '$hash',
	                            updated_at = now()
	                    	WHERE id = '" . $list_1[0]["id"] . "' ";
	            $mysqli->query($query_update);

				if(strlen($list_1[0]["calimaco_id"])===0){
					$calimaco_id = api_calimaco_get_calimaco_id($list_1[0]["id"]);
					if(strlen($calimaco_id)===0){
						$url_ = api_calimaco_get_url_sportbook($list_1[0]["id"], 0, $timestamp, $list_1[0]["calimaco_id"]);
					} else {
						$list_1[0]["calimaco_id"] = $calimaco_id;
					}
				}


				$full_name_nuevo = "";
				if(strlen($list_1[0]["web_full_name"])===0){
					if(strlen($list_1[0]["web_id"])>0){
						$array_user = array();
						$array_user = api_calimaco_check_user($list_1[0]["id"], $list_1[0]["web_id"]);
						if(isset($array_user["result"])){
							if($array_user["result"]==="OK") {
								$array_user["first_name"] = strtoupper(replace_invalid_caracters(trim($array_user["first_name"])));
								$array_user["middle_name"] = strtoupper(replace_invalid_caracters(trim($array_user["middle_name"])));
								$array_user["last_name"] = strtoupper(replace_invalid_caracters(trim($array_user["last_name"])));

								$full_name_nuevo .= (strlen($array_user["first_name"])>0)?$array_user["first_name"]:'';
								$full_name_nuevo .= (strlen($array_user["middle_name"])>0)?' '.$array_user["middle_name"]:'';
								$full_name_nuevo .= (strlen($array_user["last_name"])>0)?' '.$array_user["last_name"]:'';

								$query_update = "
									UPDATE tbl_televentas_clientes 
									SET 
										web_full_name = '" . $full_name_nuevo . "',
										updated_at = now()
									WHERE id = '" . $list_1[0]["id"] . "'
									";
								$mysqli->query($query_update);

								$list_1[0]["web_full_name"] = $full_name_nuevo;
							}
						}
					}
				}

				if(strlen($list_1[0]["web_id"])===0 || (int)$list_1[0]["validate_web_id"]===0) {
					$res_web_id = api_calimaco_get_web_id($list_1[0]["num_doc"]);
					if((int)$res_web_id['http_code']===200) {
						$query_update = "
							UPDATE tbl_televentas_clientes 
							SET 
								validate_web_id = '1',
								web_id = '" . $res_web_id["WebId"] . "',
								web_full_name = '" . $res_web_id["WebFullName"] . "',
								updated_at = now()
							WHERE id = '" . $list_1[0]["id"] . "'
							";
						$mysqli->query($query_update);

						$list_1[0]["web_id"] = $res_web_id["WebId"];
						$list_1[0]["web_full_name"] = $res_web_id["WebFullName"];
						$list_1[0]["validate_web_id"] = 1;
					}
				}

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
				$result["http_code"] = 405;
				$result["status"] = "Cliente bloqueado";
				$result["result"] = $list_1[0];
				$result["result2"] = (count($list_bloqueado) > 0) ? $list_bloqueado[0] : [];
			}			


		}

}

//*******************************************************************************************************************
//*******************************************************************************************************************
// BUSCAR DATOS CLIENTE VERIFICADO
//*******************************************************************************************************************
//*******************************************************************************************************************

if ($_POST["accion"] === "busqueda_api_cliente_televentas") {
	include("function_replace_invalid_caracters.php");

    $busqueda_tipo  = $_POST["tipo"];
    $busqueda_valor = $_POST["valor"];
	$result["mensaje"] = "";
	$result["status"] = "";
	$val_nombres = "";
	$val_apellido_paterno = "";
	$val_apellido_materno = "";
	$val_documento = "";


	//TIPO DOC
	if ((int) $busqueda_tipo >= 0 && (int) $busqueda_tipo <= 2) {
		// Si es DNI usamos la API de DNI
		if ((int) $busqueda_tipo === 0) {
			$result_api_dni = get_cliente_por_dni($busqueda_valor);
			if((!is_array($result_api_dni)) or (empty($result_api_dni))){
				$result["mensaje"] = "El DNI no existe. Debe registrarlo manualmente";
				$val_documento = $busqueda_valor;//(int)$busqueda_valor;
			}else{
				if(!(isset($result_api_dni["dni"]))) {
					if(isset($result_api_dni["message"])) {	
						$result["http_code"] = 200;
						$result["status"] = "Cuotas no disponibles para la consulta de nuevos DNI. Por favor, comunicarse con el canal digital y/o soporte.";
					}else{
						$result["http_code"] = 200;
						$result["status"] = "No se encontró el DNI.";
					}
					$val_documento = $busqueda_valor;//(int)$busqueda_valor;
					
				}else{
					if ((int) $result_api_dni["dni"] === (int) $busqueda_valor) {
						$val_nombres = strtoupper(replace_invalid_caracters(trim($result_api_dni["nombres"])));
						$val_apellido_paterno = strtoupper(replace_invalid_caracters(trim($result_api_dni["apellido_paterno"])));
						$val_apellido_materno = strtoupper(replace_invalid_caracters(trim($result_api_dni["apellido_materno"])));
						$val_documento = $result_api_dni["dni"];
					//	$result["status"] = "Datos obtenidos de la API de DNI.";
					} else {
						$val_documento = $busqueda_valor; //(int)$busqueda_valor;
					}
				}
			}
		} elseif ((int) $busqueda_tipo === 1 || (int) $busqueda_tipo === 2) {
				$val_documento = $busqueda_valor;
		}
	}

	$query_2 = "SELECT 
				'0' id,
				'" . $val_nombres . "' nombre,
				'" . $val_apellido_paterno . "' apellido_paterno,
				'" . $val_apellido_materno . "' apellido_materno,
				'" . $busqueda_tipo . "' tipo_doc,
				'" . $val_documento . "' num_doc,
				'' web_id,
				'' web_full_name,
				'' player_id,
				'' calimaco_id,
				'' telefono,
				'' fec_nac,
				'' block_user_id,
				'' cc_id,
				'10000' bono_limite,
				'' updated_at,
				'' block_hash,
				1 tipo_balance_id,
				0 validate_web_id,
				'' nacionalidad,
				'' ubigeo,
				'' direccion,
				'' es_pep,
				'0' clienteExistente
				";
	$list_query_2 = $mysqli->query($query_2);
	$list_2 = array();
	while ($li_2 = $list_query_2->fetch_assoc()) {
		$list_2[] = $li_2;
	}

	if (count($list_2) === 0) {
		$result["http_code"] = 400;
		$result["status"] = "Datos no obtenidos.";
		$result["result"] = "No se pudo registrar al cliente.";
	} else if (count($list_2) > 0) {
		$result["http_code"] = 200;
		

        $res_web_id = api_calimaco_get_web_id($list_2[0]["num_doc"]);
        if((int)$res_web_id['http_code']===200){
			$list_2[0]["web_id"] = $res_web_id["WebId"];
			$list_2[0]["web_full_name"] = $res_web_id["WebFullName"];
        }
		$list_2[0]["is_disabled_mincetur"] = cliente_mincetur($busqueda_tipo, $busqueda_valor);
		$result["result"] = $list_2[0];

	} else{
		$result["http_code"] = 400;
		$result["status"] = "Datos no obtenidos.";
		$result["result"] = "*No se pudo registrar al cliente.";
	}

}


//*******************************************************************************************************************
//*******************************************************************************************************************
// REGISTRAR CLIENTE MENOR O FALLECIDO
//*******************************************************************************************************************
//*******************************************************************************************************************

if ($_POST["accion"] === "registrar_cliente_id_menor") {
	include("function_replace_invalid_caracters.php");

    $num_doc 	 = $_POST["dni_cliente"];
    $messageCode = $_POST["messageCode"];
	$hash        = $_POST["hash"];

	if($messageCode == 9002){
		$id_etiqueta =45;
	}else{
		$id_etiqueta =48;
	}

	$query_insert = "INSERT INTO tbl_televentas_clientes (
					cc_id,
					tipo_doc,
					num_doc,
					created_user_id,
					created_at,
					updated_at,
					block_user_id,
					bono_limite,
					block_hash) 
					VALUES (
					'3900',
					0,
					'" . $num_doc . "',
					'" . $usuario_id . "',
					now(),
					now(),
					'" . $usuario_id . "',
					'10000',
					'$hash'
					);
	";
	$mysqli->query($query_insert);

	if ($mysqli->error) {
		$result["query_insert"] = $mysqli->error;
	}


	$query_1 = "
			SELECT id 
			FROM tbl_televentas_clientes
			WHERE num_doc='" . $num_doc . "'
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

			$query_insert_etq = "INSERT INTO tbl_televentas_clientes_etiqueta (
				`client_id`,
				`etiqueta_id`,
				`status`,
				`created_user_id`,
				`created_at`) 
				VALUES (
					'" . $list_1[0]["id"] . "',
					'" . $id_etiqueta . "',
					'1',
					'" . $usuario_id . "',
					now()
					)";
			$mysqli->query($query_insert_etq);

			if ($mysqli->error) {
				$result["query_insert_etq"] = $mysqli->error;
			}

			$query_etq_idmf = "
				SELECT 
					ce.id
				FROM tbl_televentas_clientes_etiqueta ce
				WHERE 
					ce.client_id = '" . $list_1[0]["id"] . "' 
					AND ce.etiqueta_id = '" . $id_etiqueta . "' 
					AND ce.status = '1' 
				";
			//$result["consulta_query"] = $query_3;
			$list_query = $mysqli->query($query_etq_idmf);
			$list_etq_idmf = array();
			while ($li = $list_query->fetch_assoc()) {
				$list_etq_idmf[] = $li;
			}
			if ($mysqli->error) {
				$result["query_etq_idmf"] = $mysqli->error;
			}
			if (count($list_etq_idmf) == 1) {
				$result["http_code"] = 200;
				$result["status"] = "ok";
				$result["result"] = "ok";
			} else {
				$result["http_code"] = 400;
				$result["status"] = "Ocurrió un error al registrar la etiqueta con el cliente.";
			}

		}else{
			$result["http_code"] = 400;
			$result["status"] = "Datos no obtenidos.";
			$result["result"] = "*No se pudo registrar al cliente.";
		}



}

//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER CLIENTE
//*******************************************************************************************************************
//*******************************************************************************************************************
if ($_POST["accion"] === "obtener_televentas_cliente") {
	include("function_replace_invalid_caracters.php");

    $timestamp      = $_POST["timestamp"];
    $busqueda_tipo  = $_POST["tipo"];
    $busqueda_valor = $_POST["valor"];
    $hash           = $_POST["hash"];
    $result["mensaje"] = "";
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

	$query_p_bloqueo = "
		SELECT dni
		FROM tbl_televentas_blacklist 
		WHERE dni = '" . $busqueda_valor . "'";

	$list_query_pb = $mysqli->query($query_p_bloqueo);

	$list_pb = array();
	if ($mysqli->error) {
		$result["query_pb_error"] = $mysqli->error;
	} else {
		while ($li_pb = $list_query_pb->fetch_assoc()) {
			$list_pb[] = $li_pb;
		}
	}

	$turno = get_turno();
	if (count($turno) > 0) {
		$local_id = $turno[0]["local_id"];
	}

	if (count($list_pb) === 0) {
		$query_1 = "
			SELECT 
				c.id,
				UPPER(IFNULL(c.nombre, '')) nombre,
				UPPER(IFNULL(c.apellido_paterno, '')) apellido_paterno,
				UPPER(IFNULL(c.apellido_materno, '')) apellido_materno,
				IFNULL(c.tipo_doc, '') tipo_doc,
				IFNULL(c.num_doc, '') num_doc,
				IFNULL(c.web_id, '') web_id,
				UPPER(IFNULL(c.web_full_name, '')) web_full_name,
				IFNULL(c.player_id, '') player_id,
				IFNULL(c.calimaco_id, '') calimaco_id,
				IFNULL(c.telefono, '') telefono,
				IFNULL(c.fec_nac, '') fec_nac,
				IFNULL(c.block_user_id, '') block_user_id,
				IFNULL(c.cc_id, '') cc_id,
				IFNULL(c.bono_limite, '10000') bono_limite,
				IFNULL(c.updated_at, '') updated_at,
				IFNULL(c.block_hash, '') block_hash,
				IFNULL(c.validate_web_id, 0) validate_web_id,
				1 tipo_balance_id
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

		if (count($list_1) === 0) {
			$val_nombres = "";
			$val_apellido_paterno = "";
			$val_apellido_materno = "";
			$val_documento = "";
			//TIPO DOC
			if ((int) $busqueda_tipo >= 0 && (int) $busqueda_tipo <= 2) {
				// Si es DNI usamos la API de DNI
				if ((int) $busqueda_tipo === 0) {
					$result_api_dni = get_cliente_por_dni($busqueda_valor);
					if((!is_array($result_api_dni)) or (empty($result_api_dni))){
						$result["mensaje"] = "El DNI no existe. Debe registrarlo manualmente";
						$val_documento = $busqueda_valor; //(int)$busqueda_valor;
					}else{

						if(!(isset($result_api_dni["dni"]))) {
							if(isset($result_api_dni["message"])) {	
								$result["http_code"] = 400;
								$result["status"] = "Cuotas no disponibles para la busqueda. Por favor comunicarse con el canal digital y/o soporte.";
								echo json_encode($result); exit();
							}
							$result["http_code"] = 400;
							$result["status"] = "No se encontro al DNI.";
							echo json_encode($result); exit();
						}
						
						if ((int) $result_api_dni["dni"] === (int) $busqueda_valor) {
							$val_nombres = strtoupper(replace_invalid_caracters(trim($result_api_dni["nombres"])));
							$val_apellido_paterno = strtoupper(replace_invalid_caracters(trim($result_api_dni["apellido_paterno"])));
							$val_apellido_materno = strtoupper(replace_invalid_caracters(trim($result_api_dni["apellido_materno"])));
							$val_documento = $result_api_dni["dni"];
						} else {
							$val_documento = $busqueda_valor; //(int)$busqueda_valor;
						}
					}
				} elseif ((int) $busqueda_tipo === 1 || (int) $busqueda_tipo === 2) {
					$val_documento = $busqueda_valor;
				}
			}

			$query_2 = "
				SELECT 
					'0' id,
					'" . $val_nombres . "' nombre,
					'" . $val_apellido_paterno . "' apellido_paterno,
					'" . $val_apellido_materno . "' apellido_materno,
					'" . $busqueda_tipo . "' tipo_doc,
					'" . $val_documento . "' num_doc,
					'' web_id,
					'' web_full_name,
					'' player_id,
					'' calimaco_id,
					'' telefono,
					'' fec_nac,
					'' block_user_id,
					'' cc_id,
					'10000' bono_limite,
					'' updated_at,
					'' block_hash,
					1 tipo_balance_id,
					0 validate_web_id
				";
			$list_query_2 = $mysqli->query($query_2);
			$list_2 = array();
			while ($li_2 = $list_query_2->fetch_assoc()) {
				$list_2[] = $li_2;
			}
			if (count($list_2) === 0) {
				$result["http_code"] = 400;
				$result["status"] = "Datos no obtenidos.";
				$result["result"] = "No se pudo registrar al cliente.";
			} else if (count($list_2) > 0) {
				$result["http_code"] = 200;
				$result["status"] = "Datos obtenidos de la API de DNI.";

        		$res_web_id = api_calimaco_get_web_id($list_2[0]["num_doc"]);
        		if((int)$res_web_id['http_code']===200){
					$list_2[0]["web_id"] = $res_web_id["WebId"];
					$list_2[0]["web_full_name"] = $res_web_id["WebFullName"];
        		}

				$result["result"] = $list_2[0];

			} else {
				$result["http_code"] = 400;
				$result["status"] = "Datos no obtenidos.";
				$result["result"] = "*No se pudo registrar al cliente.";
			}
		} else if (count($list_1) > 0) {
			$full_name_nuevo = "";
			if(strlen($list_1[0]["web_full_name"])===0){
				if(strlen($list_1[0]["web_id"])>0){
					$array_user = array();
					$array_user = api_calimaco_check_user($list_1[0]["id"], $list_1[0]["web_id"]);
					if(isset($array_user["result"])){
						if($array_user["result"]==="OK") {
							$array_user["first_name"] = strtoupper(replace_invalid_caracters(trim($array_user["first_name"])));
							$array_user["middle_name"] = strtoupper(replace_invalid_caracters(trim($array_user["middle_name"])));
							$array_user["last_name"] = strtoupper(replace_invalid_caracters(trim($array_user["last_name"])));

							$full_name_nuevo .= (strlen($array_user["first_name"])>0)?$array_user["first_name"]:'';
							$full_name_nuevo .= (strlen($array_user["middle_name"])>0)?' '.$array_user["middle_name"]:'';
							$full_name_nuevo .= (strlen($array_user["last_name"])>0)?' '.$array_user["last_name"]:'';

							$query_update = "
								UPDATE tbl_televentas_clientes 
								SET 
									web_full_name = '" . $full_name_nuevo . "',
									updated_at = now()
								WHERE id = '" . $list_1[0]["id"] . "'
								";
							$mysqli->query($query_update);

							$list_1[0]["web_full_name"] = $full_name_nuevo;
						}
					}
				}
			}

			if(strlen($list_1[0]["web_id"])===0 || (int)$list_1[0]["validate_web_id"]===0) {
        		$res_web_id = api_calimaco_get_web_id($list_1[0]["num_doc"]);
        		if((int)$res_web_id['http_code']===200) {
					$query_update = "
						UPDATE tbl_televentas_clientes 
						SET 
							validate_web_id = '1',
							web_id = '" . $res_web_id["WebId"] . "',
							web_full_name = '" . $res_web_id["WebFullName"] . "',
							updated_at = now()
						WHERE id = '" . $list_1[0]["id"] . "'
						";
					$mysqli->query($query_update);

					$list_1[0]["web_id"] = $res_web_id["WebId"];
					$list_1[0]["web_full_name"] = $res_web_id["WebFullName"];
					$list_1[0]["validate_web_id"] = 1;
        		}
			}

			if(strlen($list_1[0]["calimaco_id"])===0){
				$calimaco_id = api_calimaco_get_calimaco_id($list_1[0]["id"]);
				if(strlen($calimaco_id)===0){
					$url_ = api_calimaco_get_url_sportbook($list_1[0]["id"], 0, $timestamp, $list_1[0]["calimaco_id"]);
				} else {
					$list_1[0]["calimaco_id"] = $calimaco_id;
				}
			}

			if ((int) $list_1[0]["block_user_id"] === (int) $usuario_id ||
					(int) $list_1[0]["block_user_id"] === 0 ||
					(int) $list_1[0]["block_user_id"] === null) {

				query_tbl_televentas_clientes_block($list_1[0]["id"], 1);
				
	            $query_update = "
	                    UPDATE tbl_televentas_clientes
	                    SET
	                            block_hash = '$hash',
	                            updated_at = now()
	                    WHERE id = '" . $list_1[0]["id"] . "'
	                    ";
	            $mysqli->query($query_update);
				
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
				$result["http_code"] = 405;
				$result["status"] = "Cliente bloqueado";
				$result["result"] = $list_1[0];
				$result["result2"] = $list_bloqueado[0];
			}
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrio un error en la BD de televentas.";
			$result["result"] = "Ocurrió un error en la busqueda.";
		}
	}
	else{
		$result["http_code"] = 400;
		$result["status"] = "El cliente no puede ser atendido.";
		$result["result"] = "El cliente no puede ser atendido.";
	}
}


if($_POST["accion"]==="consultar_ubigeo"){
	$ubigeo = $_POST["ubigeo"];
	try {
		$response = get_ubigeo($ubigeo);
		echo json_encode($response);
		exit();
	}
	catch (Exception $e) {
		echo json_encode(array("error" => $e->getMessage()));
		exit();
	}
}

function get_ubigeo($ubigeo){
	global $mysqli;

	$where = null;

	if ($ubigeo === null || $ubigeo === "") {

		$where = "cod_depa !='00' and cod_prov = '00' and cod_dist ='00'";
		
	} elseif (strlen($ubigeo) === 2) {

		$where = "cod_depa = '".$ubigeo."' and  cod_prov != '00' and cod_dist ='00' ";
		
	} elseif (strlen($ubigeo) === 4) {
		$where = "cod_depa = SUBSTRING('$ubigeo',1,2) and cod_prov =SUBSTRING('$ubigeo',3,2) and cod_dist !='00'";
	}
	else {
		throw new Exception("Invalid ubigeo format");
	}

	$query = "
		SELECT cod_depa, cod_prov, cod_dist, nombre
		FROM tbl_ubigeo
		WHERE $where ";

		$list_query = $mysqli->query($query);
		$list_ubigeo = array();
		while ($li2 = $list_query->fetch_assoc()) {
			$list_ubigeo[] = $li2;
		}
		if ($mysqli->error) {
			$result["query"] = $mysqli->error;
		}


	return $list_ubigeo;

}

function api_get_ubigeo($ubigeo){

	$response = null;

	$postFields = '';
	$url = '';
	$method = '';
	if ($ubigeo === null || $ubigeo === "") {
		$postFields = 'country=PE&company=ATP';
		$method = 'getStates';
		$url =  env('WALLET_API_URL') . '/contents/' . $method; //'https://wallet.apuestatotal.com/api/contents/getStates';
	} elseif (strlen($ubigeo) === 2) {
		$postFields = 'country=PE&company=ATP&state=' . $ubigeo;
		$method = 'getProvinces';
		$url = env('WALLET_API_URL') . '/contents/' . $method; //'https://wallet.apuestatotal.com/api/contents/getProvinces';
	} elseif (strlen($ubigeo) === 4) {
		$postFields = 'country=PE&company=ATP&state=' . substr($ubigeo, 0, 2) . '&province=' . substr($ubigeo,2, 2);
		$method = 'getCities';
		$url = env('WALLET_API_URL') . '/contents/' . $method; //'https://wallet.apuestatotal.com/api/contents/getCities';
	}
	else {
		throw new Exception("Invalid ubigeo format");
	}

	$curl = curl_init();
	//$auditoria_id = api_calimaco_auditoria_insert($method, 0, 0, $url);
	curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'GET',
		CURLOPT_POSTFIELDS => $postFields,
		CURLOPT_HTTPHEADER => array(
			'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
			'Content-Type : application/json'
		),
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);
	$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);

	if ($err) {
		$response = "cURL Error #:" . $err;
	} elseif ($httpCode !== 200) {
			throw new Exception("HTTP Error: " . $httpCode);
	}
	//api_calimaco_auditoria_update($auditoria_id, 0, $response, 1);
	return json_decode($response, true);

}

if($_POST["accion"]==="consultar_nacionalidad"){
	try {
		$response = get_nacionalidad();
		echo json_encode($response);
		exit();
	}
	catch (Exception $e) {
		echo json_encode(array("error" => $e->getMessage()));
		exit();
	}
}

function get_nacionalidad (){
	global $mysqli;

	$query = "
		SELECT iso3 as codigo, name as nombre
		FROM countries";

		$list_query = $mysqli->query($query);
		$list_nacionalidad = array();
		while ($li2 = $list_query->fetch_assoc()) {
			$list_nacionalidad[] = $li2;
		}
		if ($mysqli->error) {
			$result["query"] = $mysqli->error;
		}


	return $list_nacionalidad;

}

function api_get_nacionalidad (){
	$curl = curl_init();
	$url = env('SIC_API_URL') . '/country';
	//$auditoria_id = api_calimaco_auditoria_insert('country', 0, 0, $url);
	curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'GET',
		CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json',
			'Authorization: Bearer ' .env('SIC_API_TOKEN')
		),
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);
	$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);

	if ($err) {
		$response = "cURL Error #:" . $err;
	} elseif ($httpCode !== 200) {
			throw new Exception("HTTP Error: " . $httpCode);
	}
	//api_calimaco_auditoria_update($auditoria_id, 0, json_encode($response), 1);
	return json_decode($response, true);
}

if($_POST["accion"]==="consultar_api_SIC_cliente_televentas"){
	$tipoDocumento = $_POST["tipoDocumento"];
	$numeroDocumento = $_POST["numeroDocumento"];
	$tipoCanal = env('SIC_DOMAIN_ID_TELESERVICIO');

	try {
		$response = api_get_sic_findClient($tipoDocumento, $numeroDocumento,$tipoCanal ,"");
		echo json_encode($response);
		exit();
	}
	catch (Exception $e) {
		echo json_encode(array("error" => $e->getMessage()));
		exit();
	}
}

function api_get_sic_findClient($tipoDoc, $numDoc, $id_tipo_canal, $id_cliente){
	$curl = curl_init();
	// $tipoDocumento = $tipoDoc === "0" ? "DNI" : "CEX";
	$tipoDocumento = $tipoDoc === "0" ? "DNI" : ($tipoDoc === "1" ? "CEX" : "PAS");
	$url = env('SIC_API_URL') . '/find_client';
	
	$body = [
		"tipo_documento" => $tipoDocumento,
		"numero_documento" => $numDoc,
		"domain_id" => $id_tipo_canal
	];
	// $auditoria_id = api_calimaco_auditoria_insert('find_client', 0, $id_cliente, $url . json_encode($body));
	$request_json = json_encode($body);
	curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => $request_json,
		CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json',
			'Authorization: Bearer ' .env('SIC_API_TOKEN')
		),
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);
	$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);

	if ($err) {
		$response = "cURL Error #:" . $err;
	} elseif ($httpCode !== 200) {
		
	}
	// api_calimaco_auditoria_update($auditoria_id, 0, $response, 1);
	return json_decode($response, true);
}


function api_post_sic_registerClient($json_array, $id_cliente){
	$curl = curl_init();
	$url = env('SIC_API_URL') . '/register_client';
	$auditoria_id = api_calimaco_auditoria_insert('register_client', 0, $id_cliente, $url . json_encode($json_array));
	$request_json = json_encode($json_array);
	curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => $request_json,
		CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json',
			'Authorization: Bearer ' .env('SIC_API_TOKEN')
		),
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);
	$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);

	if ($err) {
		$response = "cURL Error #:" . $err;
	} elseif ($httpCode !== 200) {
			
	}
	api_calimaco_auditoria_update($auditoria_id, 0, $response, 1);
	return json_decode($response, true);

}

function api_post_sic_editClient($json_array, $id_cliente){
	$curl = curl_init();
	$url = env('SIC_API_URL') . '/update_client';
	$auditoria_id = api_calimaco_auditoria_insert('update_client', 0, $id_cliente, $url . json_encode($json_array));
	$request_json = json_encode($json_array);
	curl_setopt_array($curl, array(
		CURLOPT_URL => $url,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => $request_json,
		CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json',
			'Authorization: Bearer ' .env('SIC_API_TOKEN')
		),
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);
	$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);

	if ($err) {
		$response = "cURL Error #:" . $err;
	} elseif ($httpCode !== 200) {
			
	}
	api_calimaco_auditoria_update($auditoria_id, 0, $response, 1);
	return json_decode($response, true);

}

//*******************************************************************************************************************
// OBTENER DEPOSITOS DISPONIBLES PARA RECARGA
//*******************************************************************************************************************
if ($_POST["accion"] === "obtener_depositos_disponibles") {

	date_default_timezone_set("America/Lima");
	$id_cliente = $_POST["id_cliente"];

	// TRANSACCIONES
	$query = "
		SELECT
			trans.id AS codigo,
			IFNULL(trans.monto, 0) monto,
			IFNULL(trans.bono_monto, 0) bono_monto,
			IFNULL( bon.nombre, 'Ninguno' ) AS bono_nombre
		FROM
			tbl_televentas_clientes_transaccion trans
			LEFT JOIN tbl_televentas_recargas_bono bon ON bon.id = trans.bono_id 
		WHERE 
			trans.cliente_id = $id_cliente 
			AND trans.tipo_id = 1 
			AND trans.estado = 1 
			AND trans.bono_id > 1 
			AND trans.created_at > date_add(NOW(), INTERVAL -1 DAY) 
			AND ( trans.valid_bono = 0 or trans.valid_bono is null ) 
		ORDER BY
			trans.id DESC 
		";
	//echo $query;
	$list_query = $mysqli->query($query);
	$list = array();
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar las transacciones.";
		$result["result"] = $mysqli->error;
		$result["query"] = $query;
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$list[] = $li;
		}
		if (count($list) >= 0) {
			$result["http_code"] = 200;
			$result["status"] = "ok";
			$result["result"] = $list;
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al consultar las transacciones.";
			$result["result"] = $list;
		}
	}
}





//*******************************************************************************************************************
// OBTENER DETALLE DEL BONO A TRAVES DE UNA RECARGA
//*******************************************************************************************************************
if ($_POST["accion"] === "obtener_recarga_x_bono") {

	date_default_timezone_set("America/Lima");
	$recarga_id = $_POST["recarga_id"];

	// TRANSACCIONES
	$query = "
		SELECT
			trans_b.bono_id AS bono_codigo,
			IFNULL(trans_b.bono_monto, 0) bono_monto,
			IFNULL( bon.nombre, 'Ninguno' ) AS bono_nombre
		FROM
			tbl_televentas_clientes_transaccion trans
			JOIN tbl_televentas_clientes_transaccion trans_b ON trans_b.transaccion_id=trans.transaccion_id and trans_b.tipo_id=10
			LEFT JOIN tbl_televentas_recargas_bono bon ON bon.id = trans_b.bono_id 
		WHERE
			trans.id = $recarga_id 
			AND trans.tipo_id = 2 
		ORDER BY
			trans.id DESC 
		LIMIT 1
	";
	//echo $query;
	$list_query = $mysqli->query($query);
	$list = array();
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar las transacciones.";
		$result["result"] = $mysqli->error;
		$result["query"] = $query;
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$list[] = $li;
		}
		if (count($list) > 0) {
			$result["http_code"] = 200;
			$result["status"] = "ok";
			$result["bono_monto"] = $list[0]["bono_monto"];
			$result["bono_nombre_full"] = $list[0]["bono_codigo"] . '-' . $list[0]["bono_nombre"];
			$result["bono_nombre"] = $list[0]["bono_nombre"];
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al consultar las transacciones.";
			$result["result"] = $list;
		}
	}
}









//*******************************************************************************************************************
// OBTENER TRANSACCIONES X CLIENTE
//*******************************************************************************************************************
if ($_POST["accion"] === "obtener_transacciones_por_cliente") {

	$cargo_id = $login ? $login['cargo_id'] : 0;

	date_default_timezone_set("America/Lima");
	$fecha_hora_actual = date('Y-m-d H:i:s');

	$id_cliente   = $_POST["id_cliente"];
	$fecha_inicio = $_POST["fecha_inicio"];
	$fecha_fin    = $_POST["fecha_fin"];
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
	$list_balance = obtener_balances($id_cliente);

	if(!(count($list_balance)>0)){
		$result["result"] = "No se pudo obtener los balances.";
		echo json_encode($result);
		exit();
	}

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

	// Consultar el monto en apuestas generadas con Dinero AT, por evento
	if ( (int)$tipo_balance === 6 ) {
		$fecha_actual = date('Y-m-d');
	
		$query_id_dineroat = "
			SELECT
				e.id evento_dineroat_id
			FROM
				tbl_televentas_dinero_at_eventos e
				INNER JOIN tbl_televentas_dinero_at_eventos_clientes ce ON e.id = ce.dinero_at_evento_id
			WHERE e.estado = 1
				AND ce.cliente_id = $id_cliente
				AND e.fecha_inicio <= '".$fecha_actual."'
				AND e.fecha_fin >= '".$fecha_actual."'
			LIMIT 1
		";
			
		$list_query_id_dineroat = $mysqli->query($query_id_dineroat);
		$list_id_dineroat = array();
		if ($mysqli->error) {
			$result["http_code"] = 400;
			$result["status"] = "El cliente no tiene una promocion con Dinero AT.";
			$result["query"] = $query_id_dineroat;
			$result["query_error"] = $mysqli->error;
			echo json_encode($result);exit();
		} else {
			while ($li = $list_query_id_dineroat->fetch_assoc()) {
				$list_id_dineroat[] = $li;
			}
		}
		if ( count($list_id_dineroat) === 1 ) { 
			$id_evento_dinero_at = $list_id_dineroat[0]["evento_dineroat_id"];
			$result["evento_dineroat_id"] = $list_id_dineroat[0]["evento_dineroat_id"];
		} else {
			$result["http_code"] = 400;
			$result["status"] = "El cliente no tiene una pomocion de Dinero AT.";
			echo json_encode($result);exit();
		}

		$query_dineroat = "
			SELECT
				( 	SELECT IFNULL(SUM(monto), 0) 
					FROM tbl_televentas_clientes_transaccion
					WHERE estado = 1
						AND tipo_id = 4
						AND id_tipo_balance = 6
						AND evento_dineroat_id = $id_evento_dinero_at
						AND cliente_id = $id_cliente
				) rollover_acumulado,
				IFNULL(dat_ec.rollover_monto, 0) rollover_meta,
				dat_e.tipo_conversion,
				dat_e.conversion_maxima,
				dat_ec.monto bono_cliente
			FROM tbl_televentas_dinero_at_eventos dat_e 
				LEFT JOIN tbl_televentas_dinero_at_eventos_clientes dat_ec ON dat_ec.dinero_at_evento_id = dat_e.id
			WHERE dat_e.estado = 1
				AND dat_ec.estado = 1
				AND dat_ec.dinero_at_evento_id = $id_evento_dinero_at
				AND dat_ec.cliente_id = $id_cliente
			LIMIT 1
		";
		$list_query_dineroat = $mysqli->query($query_dineroat);
		if ($mysqli->error) {
			$result["http_code"] = 400;
			$result["status"] = "Error al consultar el el monto acumulado de rollover.";
			$result["query"] = $query_dineroat;
			$result["query_error"] = $mysqli->error;
			echo json_encode($result);exit();
		}
		$list_rollover = array();
		while ($li = $list_query_dineroat->fetch_assoc()) {
			$list_rollover[] = $li;
		}
		if ( count($list_rollover)===1 ) {
			if ( (int)$list_rollover[0]["tipo_conversion"] === 2 ) {
				$list_rollover[0]["conversion_maxima"] = round( $list_rollover[0]["bono_cliente"]*$list_rollover[0]["conversion_maxima"]/100, 2);
			}
			$result["rollover_status"] = "SI";
			$result["rollover_status_msj"] = "El cliente cuenta con rollover en este promoción";
			$result["rollover_acumulado"] = (double)$list_rollover[0]["rollover_acumulado"];
			$result["rollover_meta"] = (double)$list_rollover[0]["rollover_meta"];
			$result["rollover_conversion_maxima"] = (double)$list_rollover[0]["conversion_maxima"];
		} else {
			$result["rollover_status"] = "NO";
			$result["rollover_status_msj"] = "No existe rollover para el cliente en esta promoción";
		}

	}

	// Actualizar la etiqueta en caso ya no tenga dinero promocional AT
	if( (float)$list_balance[0]["balance_dinero_at"] == 0){
		$query_label = "
			UPDATE tbl_televentas_clientes_etiqueta
			SET status = 0,
				updated_user_id = $usuario_id,
				updated_at = now()
			WHERE
				client_id = $id_cliente 
				AND status = 1 
				AND status = 1
				AND etiqueta_id = 43
		";
		$mysqli->query($query_label);
		if ($mysqli->error) {
			$result["status"] = "Ocurrió un error aaa.";
			$result["result"] = $mysqli->error;
			$result["query"] = $query_label;
			echo json_encode($result);
			exit();
		} 
	}

	// ETIQUETAS
	$query_2 = "
		SELECT
			a.id,
			a.etiqueta,
			a.color,
			a.tipo_etiqueta,
			a.id_etiqueta
		FROM (
			SELECT
				ce.id AS id,
				UPPER(IFNULL(e.label, '')) AS etiqueta,
				e.color AS color,
				IFNULL(e.tipo, 1) tipo_etiqueta,
				e.id id_etiqueta
			FROM
				tbl_televentas_clientes_etiqueta ce
				JOIN tbl_televentas_etiqueta e ON e.id = ce.etiqueta_id
			WHERE
				ce.client_id = $id_cliente 
				AND ce.status = 1 
				AND e.status = 1 
				AND ce.start_date IS NULL 
				AND ce.end_date IS NULL 
			UNION ALL
			SELECT
				ce.id AS id,
				UPPER(IFNULL(e.label, '')) AS etiqueta,
				e.color AS color,
				IFNULL(e.tipo, 1) tipo_etiqueta,
				e.id id_etiqueta
			FROM
				tbl_televentas_clientes_etiqueta ce
				JOIN tbl_televentas_etiqueta e ON e.id = ce.etiqueta_id
			WHERE
				ce.client_id = $id_cliente 
				AND ce.status = 1 
				AND e.status = 1 
				AND ce.start_date IS NOT NULL 
				AND ce.end_date IS NOT NULL 
				AND ce.start_date <= '$fecha_hora_actual' 
				AND ce.end_date >= '$fecha_hora_actual' 
		) a
		ORDER BY
			a.id ASC 
		";
	//echo $query;
	$result["query_list_labels"] = $query_2;
	$list_query = $mysqli->query($query_2);
	$list_labels = array();
	while ($li_2 = $list_query->fetch_assoc()) {
		$list_labels[] = $li_2;
	}
	$result["result_labels"] = $list_labels;

	// CANT TRANSACCIONES
	$query_tr = "
		SELECT id
		FROM
			tbl_televentas_clientes_transaccion  
		WHERE
			 cliente_id = $id_cliente 
		";
	$list_tr_cli = $mysqli->query($query_tr);
	$list_tr_cli_fn = array();
	while ($li_tr = $list_tr_cli->fetch_assoc()) {
		$list_tr_cli_fn[] = $li_tr;
	}

	// ETIQUETA MENOR O FALLECIDO
	$query_idmf = "
		SELECT id
		FROM
		tbl_televentas_clientes_etiqueta  
		WHERE
		etiqueta_id in (45,48) AND 
			 client_id = $id_cliente 
		";
	$list_idmf = $mysqli->query($query_idmf);
	$list_tr_idmf = array();
	while ($li_idmf = $list_idmf->fetch_assoc()) {
		$list_tr_idmf[] = $li_idmf;
	}

	// imagenes cliente
	$query_img_cli = "
		SELECT id
		FROM
			tbl_televentas_clientes_imagenes  
		WHERE
			 cliente_id = $id_cliente 
		";
	$list_img_cli = $mysqli->query($query_img_cli);
	$list_img_cli_fn = array();
	while ($li_img = $list_img_cli->fetch_assoc()) {
		$list_img_cli_fn[] = $li_img;
	}


	$query_fn = "
		SELECT IFNULL(fec_nac, '') fec_nac
		FROM
		tbl_televentas_clientes  
		WHERE
		id = $id_cliente
		";
	//echo $query;
	$list_fn_cli = $mysqli->query($query_fn);
	$list_fn_cli_fn = array();
	while ($li_fn = $list_fn_cli->fetch_assoc()) {
		$list_fn_cli_fn[] = $li_fn;
	}

	if ((count($list_tr_cli_fn) == 0) and $list_fn_cli_fn[0]["fec_nac"] == "" and (count($list_tr_idmf) == 0) ) {
		$result["result_tr_cli"] = "0";
	}
	else if ((count($list_tr_cli_fn) == 0) and (count($list_img_cli_fn) == 0) and (count($list_tr_idmf) == 0)) {
		$result["result_tr_cli"] = "1";
	}
	else {
		$result["result_tr_cli"] = "2";
	}
	
	$result["result_balance"] = $list_balance[0]["balance"];
	$result["result_balance_bono_disponible"] = $list_balance[0]["balance_bono_disponible"];
	$result["result_balance_retiro_disponible"] = $list_balance[0]["balance_retiro_disponible"];
	$result["result_balance_no_retirable_disponible"] = $list_balance[0]["balance_deposito"];
	$result["result_balance_dinero_at"] = $list_balance[0]["balance_dinero_at"];
	$result["query"] = $query;
}


//*******************************************************************************************************************
// OBTENER BONOS
//*******************************************************************************************************************
if ($_POST["accion"] === "obtener_lista_bonos_disponibles") {

	date_default_timezone_set("America/Lima");
	$fecha_hora = date('Y-m-d H:i:s');

	// BONO
	$query_2 = "
		SELECT
			b.id,
			b.codigo,
			b.nombre bono,
			b.formula
		FROM tbl_televentas_recargas_bono b
		WHERE b.estado = 1 
		AND b.visible_inicio <= '".$fecha_hora."' 
		AND b.visible_fin >= '".$fecha_hora."' 
		ORDER BY
			b.nombre ASC 
		";
	//echo $query;
	//$result["query_2"] = $query_2;
	$list_query = $mysqli->query($query_2);
	$list_bonos = array();
	while ($li_2 = $list_query->fetch_assoc()) {
		$list_bonos[] = $li_2;
	}
	if(count($list_bonos)>0){
		$result["http_code"] = 200;
		$result["result_bonos_disponibles"] = $list_bonos;
	}else{
		$result["http_code"] = 400;
	}
}



//*******************************************************************************************************************
// OBTENER BLOQUEO BONO
//*******************************************************************************************************************
if ($_POST["accion"] === "obtener_bloqueo_bono") {
	$web_id = $_POST["web_id"];
	$client_id = $_POST["client_id"];

	$result["http_code"] = 400;
	$result["result"] = "Sin respuesta";

	$array_user = array();
	$array_user = api_calimaco_check_user($client_id, $web_id);
	//$result["response"] = $array_user;
	//$result["array_user"] = $array_user; echo json_encode($result); exit();
	if(isset($array_user["result"])){
		if($array_user["result"]==="OK") {
			if(isset($array_user["allow_promotions"])){
				if($array_user["allow_promotions"]===true) {
					$result["result"] = "Cliente permitido para bonos.";
				} else if($array_user["allow_promotions"]===false) {
					$result["http_code"] = 200;
					$result["result_IsNoBonus"] = 1;
					$result["result"] = "Cliente bloqueado para bonos.";
				} else {
					$result["result"] = null;
				}
			}
		}
	}
}






//*******************************************************************************************************************
// GUARDAR DEPOSITO
//*******************************************************************************************************************
if ($_POST["accion"] === "guardar_transaccion_deposito") {
	$datetime_start_at = date('Y-m-d H:i:s');
	include("function_replace_invalid_caracters.php");

	$id_cliente = $_POST["id_cliente"];
	$hash = $id_cliente . '_' . date('YmdHis') . ' - ';

	sec_tlv_log($hash . 'INIT Solicitud Deposito');

	$id_web = $_POST["idweb"];
	$cuenta_id = $_POST["cuenta"];
	$tipo_constancia_id = $_POST["tipo_constancia"];
	$bono_id = $_POST["bono_id"];
	$monto_deposito = $_POST["monto"];
	$total_bono_mes = $_POST["total_bono_mes"];
	$titular_abono = $_POST["titular_abono"];
	$tipo_apuesta = $_POST["tipo_apuesta"];
	$id_validacion_yape = $_POST["id_validacion_yape"];
	$fecha_abono = str_replace('T', ' ', $_POST["fecha_abono"]);

	if ( $id_validacion_yape > 0 ) {
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
			SELECT id
			FROM `at-yape`.transactions t
			WHERE t.state != 'pending'
			AND id = " . $id_validacion_yape
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
			$result["http_code"] = 402;
			$result["status"] = "El Yape Pendiente seleccionado ya fue validado anteriormente.";
			echo json_encode($result);exit();
		}
	}

	$observacion = replace_invalid_caracters($_POST["observacion"]);

	$query_update_titular1 = "
			UPDATE tbl_televentas_titular_abono 
			SET estado = 0
			WHERE id_cliente = '" . $id_cliente. "' and estado = 1
			";
	$mysqli->query($query_update_titular1);

	$query_update_titular = "
			UPDATE tbl_televentas_titular_abono 
			SET estado = 1 
			WHERE id_cliente = '" . $id_cliente. "' AND id='" . $titular_abono. "'
			";
	$mysqli->query($query_update_titular);


	if (!((float) $monto_deposito > 0)) {
		$result["http_code"] = 400;
		$result["status"] = "Montos incorrectos.";
		$result["monto_deposito"] = ((float) $monto_deposito > 0) ? true : false;
		echo json_encode($result);exit();
	}

	$list_balance = array();

	$list_balance = obtener_balances($id_cliente);

	if (count($list_balance) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "Usted no tiene balance.";
		$result["result"] = $list_balance;
	} elseif (count($list_balance) > 0) {
		$balance_actual = $list_balance[0]["balance"];
		$balance_deposito = $list_balance[0]["balance_deposito"];

		//*********************** VALIDACION BALANCE
		$query_cont = "
				SELECT cont_temp
				FROM tbl_usuarios
				where id= '" . $usuario_id . "' ";
				 
				$list_conte = $mysqli->query($query_cont);
				$list_exi = array();
				while ($li_e = $list_conte->fetch_assoc()) {
					$list_exi[] = $li_e;
				}


				if (is_null($list_exi[0]["cont_temp"])) {
					$query_upconteo = "UPDATE tbl_usuarios SET cont_temp = 0  WHERE id='" . $usuario_id . "' ";
					$mysqli->query($query_upconteo);
				}

				if ($list_exi[0]["cont_temp"] < 0) {
					$query_upconteo = "UPDATE tbl_usuarios SET cont_temp = 0  WHERE id='" . $usuario_id . "' ";
					$mysqli->query($query_upconteo);
				}

				if ($list_exi[0]["cont_temp"] >= 0) {
					$query_upconteo = "UPDATE tbl_usuarios SET cont_temp = cont_temp + 1  WHERE id='" . $usuario_id . "' ";
					$mysqli->query($query_upconteo);
				} 

		if(!(int)$titular_abono > 0){
			$titular_abono = '0';
		}

		$tipo_id = 1;
		$estado = 0;
		$balance_nuevo = $balance_actual;
		$num_operacion = '';
		$registro_deposito = NULL;
		$txn_id = '';
		$validador = 0;
		$balance_deposito_nuevo = 0;
		if($id_validacion_yape > 0){

			sec_tlv_log($hash . 'Init Consultar en bd yape - solicitud ');
			$con_db_name   = env('DB_DATABASE_YAPE');
			$con_host      = env('DB_HOST_YAPE');
			$con_user      = env('DB_USERNAME_YAPE');
			$con_pass      = env('DB_PASSWORD_YAPE');
			$mysqli2 = new mysqli($con_host,$con_user,$con_pass,$con_db_name,3306);

			$cmd_valid_yape_pending = "
				SELECT 
					t.id,
					IFNULL(t.external_user, '') external_user,
					t.state
				FROM `at-yape`.transactions t
				WHERE 
					t.id = " . $id_validacion_yape;
			$result["cmd_valid_yape_pending"] = $cmd_valid_yape_pending;
			$list_yape_pending = $mysqli2->query($cmd_valid_yape_pending);
			if ($mysqli2->error) {
				sec_tlv_log($hash . 'Error Consultar en bd yape - solicitud');
				sec_tlv_log($hash . $mysqli2->error);
				$result["consulta_error"] = $mysqli2->error;
			}
			$list_transaction_pending = array();
			while ($li = $list_yape_pending->fetch_assoc()) {
				$list_transaction_pending[] = $li;
			}
			sec_tlv_log($hash . 'End Consultar en bd yape - solicitud');


			if (count($list_transaction_pending) > 0) {
				if($list_transaction_pending[0]["state"] != 'pending'){
					sec_tlv_log($hash . 'Aviso yape ya fue validado');
					$result["http_code"] = 201;
					$result["status"] = "El yape seleccionado ya fue validado por: <br><b>" . $list_transaction_pending[0]["external_user"] . "</b>";
					echo json_encode($result);exit();
				}
			}

			$txn_id = $id_validacion_yape;
			$estado = 1;
			$balance_nuevo = $balance_actual + $monto_deposito;
			$balance_deposito_nuevo = $balance_deposito + $monto_deposito;
			$num_operacion = substr($fecha_abono, 11, 5);
			$registro_deposito = $fecha_abono;
			$validador = $usuario_id;
		}


		$insert_command = " 
			INSERT INTO tbl_televentas_clientes_transaccion (
				tipo_id,
				cliente_id,
				cuenta_id,
				turno_id,
				cc_id,
				web_id,
				txn_id,
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
				observacion_cajero,
				bono_mensual_actual,
				id_juego_balance,
				titular_abono,
				user_id,
				update_user_id,
				created_at,
				id_tipo_constancia
			) VALUES (
				'" . $tipo_id . "',
				'" . $id_cliente . "',
				'" . $cuenta_id . "',
				'" . $turno_id . "',
				'" . $cc_id . "',
				'" . $id_web . "',
				'" . $txn_id . "',
				'" . $num_operacion . "',
				" . ($registro_deposito == NULL ? "NULL" : "'$registro_deposito'") . ",
				'" . $bono_id . "',
				" . $monto_deposito . ",
				0,
				" . $monto_deposito . ",
				0,
				" . $monto_deposito . ",
				" . $balance_actual . ",
				'" . $estado . "',
				'" . $observacion . "',
				" . $total_bono_mes . ",
				'" . $tipo_apuesta . "',
				'" . $titular_abono . "',
				" . $usuario_id . ",
				" . $validador . ",
				'".date('Y-m-d H:i:s')."',
				'" . $tipo_constancia_id . "'
			)
			";
		sec_tlv_log($hash . 'Init insert en tbl_televentas_clientes_transaccion');
		$mysqli->query($insert_command);
		sec_tlv_log($hash . 'End insert en tbl_televentas_clientes_transaccion');

		if ($mysqli->error) {
			sec_tlv_log($hash . 'Error insert en tbl_televentas_clientes_transaccion');
			sec_tlv_log($hash . $mysqli->error);
			$result["insert_command"] = $insert_command;
			$result["insert_error"] = $mysqli->error;
		}

		$query_3 = "SELECT id FROM tbl_televentas_clientes_transaccion  ";
		$query_3 .= " WHERE tipo_id = 1 ";
		$query_3 .= " AND cliente_id='" . $id_cliente . "' AND user_id='" . $usuario_id . "' ";
		$query_3 .= " AND turno_id='" . $turno_id . "' AND cc_id='" . $cc_id . "' ";
		$query_3 .= " AND nuevo_balance='" . $balance_actual . "' AND monto_deposito='" . $monto_deposito . "' ";
		$query_3 .= " AND cuenta_id='" . $cuenta_id . "' ";
		$query_3 .= " AND id_juego_balance='" . $tipo_apuesta . "' ";
		$query_3 .= " ORDER BY id DESC LIMIT 1 ";

		sec_tlv_log($hash . 'Init select en tbl_televentas_clientes_transaccion obtener id tipo_id = 1');
		$list_query = $mysqli->query($query_3);
		if ($mysqli->error) {
			sec_tlv_log($hash . 'Error select en tbl_televentas_clientes_transaccion obtener id tipo_id = 1');
			sec_tlv_log($hash . $mysqli->error);
			$result["consulta_error"] = $mysqli->error;
		}
		sec_tlv_log($hash . 'End select en tbl_televentas_clientes_transaccion obtener id tipo_id = 1');

		$list_transaccion = array();
		while ($li = $list_query->fetch_assoc()) {
			$list_transaccion[] = $li;
		}

		if (count($list_transaccion) === 0) {
			sec_tlv_log($hash . 'Error No se guardó la transacción tipo_id = 1');
			$result["http_code"] = 400;
			$result["status"] = "No se guardó la transacción.";
		} elseif (count($list_transaccion) === 1) {
			$transaccion_id = $list_transaccion[0]["id"];
			if($id_validacion_yape > 0){
				$insert_command_aprov = " 
					INSERT INTO tbl_televentas_clientes_transaccion (
						tipo_id,
						cliente_id,
						cuenta_id,
						turno_id,
						cc_id,
						web_id,
						txn_id,
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
						observacion_cajero,
						transaccion_id,
						bono_mensual_actual,
						id_juego_balance,
						titular_abono,
						user_id,
						update_user_id,
						created_at,
						id_tipo_constancia,
						caja_vip
					) VALUES (
						'26',
						'" . $id_cliente . "',
						'" . $cuenta_id . "',
						'" . $turno_id . "',
						'" . $cc_id . "',
						'" . $id_web . "',
						'" . $txn_id . "',
						'" . $num_operacion . "',
						" . ($registro_deposito == NULL ? "NULL" : "'$registro_deposito'") . ",
						'" . $bono_id . "',
						" . $monto_deposito . ",
						0,
						" . $monto_deposito . ",
						0,
						" . $monto_deposito . ",
						" . $balance_nuevo . ",
						'" . $estado . "',
						'" . $observacion . "',
						" . $transaccion_id . ",
						" . $total_bono_mes . ",
						'" . $tipo_apuesta . "',
						'" . $titular_abono . "',
						" . $usuario_id . ",
						" . $validador . ",
						'".date('Y-m-d H:i:s')."',
						'4',
						'2'
					)
					";
				sec_tlv_log($hash . 'Init insert en tbl_televentas_clientes_transaccion obtener id');
				$mysqli->query($insert_command_aprov);
				if ($mysqli->error) {
					sec_tlv_log($hash . 'Error insert en tbl_televentas_clientes_transaccion obtener id');
					sec_tlv_log($hash . $mysqli->error);
					$result["insert_command_aprov"] = $insert_command_aprov;
					$result["insert_error"] = $mysqli->error;
				}
				sec_tlv_log($hash . 'End insert en tbl_televentas_clientes_transaccion obtener id');


				$query_4 = "SELECT id FROM tbl_televentas_clientes_transaccion  ";
				$query_4 .= " WHERE tipo_id = 26";
				$query_4 .= " AND cliente_id='" . $id_cliente . "' AND user_id='" . $usuario_id . "' ";
				$query_4 .= " AND turno_id='" . $turno_id . "' AND cc_id='" . $cc_id . "' ";
				$query_4 .= " AND nuevo_balance='" . $balance_nuevo . "' AND monto_deposito='" . $monto_deposito . "' ";
				$query_4 .= " AND cuenta_id='" . $cuenta_id . "' ";
				$query_4 .= " AND id_juego_balance='" . $tipo_apuesta . "' AND caja_vip = 2";
				$query_4 .= " ORDER BY id DESC LIMIT 1 ";

				sec_tlv_log($hash . 'Init select en tbl_televentas_clientes_transaccion obtener id tipo_id = 26');
				$list_query_aprov = $mysqli->query($query_4);
				if ($mysqli->error) {
					sec_tlv_log($hash . 'Error select en tbl_televentas_clientes_transaccion obtener id tipo_id = 26');
					sec_tlv_log($hash . $mysqli->error);
					$result["consulta_error"] = $mysqli->error;
				}
				sec_tlv_log($hash . 'End select en tbl_televentas_clientes_transaccion obtener id tipo_id = 26');

				$list_transaccion_aprov = array();
				while ($li = $list_query_aprov->fetch_assoc()) {
					$list_transaccion_aprov[] = $li;
				}

				$transaccion_id_aprov = 0;
				if (count($list_transaccion_aprov) === 0) {
					$result["http_code"] = 400;
					$result["status"] = "No se guardó la transacción.";
				} elseif (count($list_transaccion_aprov) === 1) {
					$transaccion_id_aprov = $list_transaccion_aprov[0]["id"];
				}
				if($transaccion_id_aprov > 0){
					$query_update = "
							UPDATE tbl_televentas_clientes_transaccion 
							SET 
								update_user_id = '" . $usuario_id . "',
								update_user_at = now(),
								id_tipo_constancia = '4',
								caja_vip = '2'
							WHERE id = '" . $transaccion_id . "'
						";
					sec_tlv_log($hash . 'Init update tbl_televentas_clientes_transaccion id_tipo_constancia = 4');
					$mysqli->query($query_update);
					sec_tlv_log($hash . 'End update tbl_televentas_clientes_transaccion id_tipo_constancia = 4');

					sec_tlv_log($hash . 'Init update balance update');
					query_tbl_televentas_clientes_balance('update', $id_cliente, 1, $balance_nuevo);
					query_tbl_televentas_clientes_balance_transaccion('insert', $transaccion_id_aprov, 
									$id_cliente, 1, $balance_actual, $monto_deposito, $balance_nuevo);
					query_tbl_televentas_clientes_balance('update', $id_cliente, 4, $balance_deposito_nuevo);
					query_tbl_televentas_clientes_balance_transaccion('insert', $transaccion_id_aprov, 
									$id_cliente, 4, $balance_deposito, $monto_deposito, $balance_deposito_nuevo);
					sec_tlv_log($hash . 'End insert balance update');

					$query_update_validate = " 
						UPDATE `at-yape`.transactions
						SET
							state = 'validated',
							updated_at = now(),
							external_user = '" . $usuario . "', 
							updated_by = 1
						WHERE id = " . $id_validacion_yape;
					sec_tlv_log($hash . 'Init update transactions yape state = validated');
					$mysqli2->query($query_update_validate);
					sec_tlv_log($hash . 'End update transactions yape state = validated');
				}
				
			}

			$result["http_code"] = 200;
			$result["status"] = "ok";
			$result["result"] = "Solicitud de Depósito Registrada";
			$result["data"] = $list_transaccion;

			//**************************************************************************************************
			//**************************************************************************************************
			// IMAGEN
			//**************************************************************************************************
			sec_tlv_log($hash . 'Init subir imagen');
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
					$imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);

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
				$archivo_id = mysqli_insert_id($mysqli);
				$filepath = $path . $resizeFileName . "." . $fileExt;
			}
			sec_tlv_log($hash . 'End subir imagen');

			sec_tlv_log($hash . 'Init asignar_etiqueta');
			sec_tlv_asignar_etiqueta_test($id_cliente);
			sec_tlv_log($hash . 'End asignar_etiqueta');
			//**************************************************************************************************
			//**************************************************************************************************
		} else {
			sec_tlv_log($hash . 'End Ocurrió un error al guardar la transacción.');
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al guardar la transacción.";
		}

		//*********************** VALIDACION BALANCE
	} else {
		sec_tlv_log($hash . 'End Ocurrió un error al consultar el balance.');
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar el balance.";
		$result["result"] = $list_balance;
	}

	$diff = (new DateTime($datetime_start_at))->diff(new DateTime(date('Y-m-d H:i:s')));
	$datetime_result = (($diff->days * 24 ) * 60 ) + ( $diff->i * 60) + $diff->s;

	sec_tlv_log($hash . 'END Solicitud Deposito - Time executed ' . $datetime_result);
}


//*******************************************************************************************************************
// ELIMINAR DEPOSITO
//*******************************************************************************************************************
if ($_POST["accion"] === "eliminar_transaccion_deposito") {

	include("function_replace_invalid_caracters.php");

	$id_cliente = $_POST["cliente_id"];
	$id_trans = $_POST["trans_id"];
	$tipo_id = $_POST["tipo_id"];
	$motivo_id = $_POST["motivo_del_dep"];
	$observacion = replace_invalid_caracters($_POST["observacion"]);

	$result = rollback_transaccion($id_cliente, $id_trans, $usuario_id, $turno_id, $observacion, $motivo_id);

}








//*******************************************************************************************************************
// GUARDAR RECARGAR WEB
//*******************************************************************************************************************
if ($_POST["accion"] === "guardar_transaccion_recarga_web") {

	$id_cliente = $_POST["id_cliente"];
	$idweb = $_POST["idweb"];
	$monto = $_POST["monto"];
	$bono = $_POST["bono"];
	$id_deposito = $_POST["id_deposito"];
	$total_recarga = 0;
	$cc_id = 0;

	if (!(((float) $monto > 0) && ((float) $monto > (float) $bono))) {
		$result["http_code"] = 400;
		$result["status"] = "Montos incorrectos.";
		echo json_encode($result);exit();
	}

		$nuevo_monto=0;
		$nuevo_bono=0;
		$bono_id = 0;

		$result["http_code_testbono"] = $bono;
		$result["result_testbono"] = $monto;

		if((float)$bono>0){
			$query_1 = "
				SELECT
					trans.id AS codigo,
					IFNULL(trans.monto, 0) monto,
					IFNULL(trans.bono_id, 0) bono_id,
					IFNULL(trans.bono_monto, 0) bono_monto,
					ba.balance
				FROM
					tbl_televentas_clientes_transaccion trans
					JOIN tbl_televentas_clientes_balance ba ON ba.cliente_id=trans.cliente_id AND tipo_balance_id = 1 
				WHERE
					trans.id = $id_deposito 
					AND trans.tipo_id = 1 
					AND trans.estado = 1 
					AND trans.created_at > date_add(NOW(), INTERVAL -1 DAY) 
					AND ( trans.valid_bono = 0 or trans.valid_bono is null )
				ORDER BY
					trans.id DESC 
				LIMIT 1
			";
			$list_query = $mysqli->query($query_1);
			$list_transaccion_dep = array();
			if ($mysqli->error) {
				$result["consulta_query"] = $query_1;
				$result["consulta_error"] = $mysqli->error;
			} else {
				while ($li = $list_query->fetch_assoc()) {
					$list_transaccion_dep[] = $li;
					$bono_id = $li["bono_id"];
					$nuevo_monto = $li["monto"];
					$nuevo_bono = $li["bono_monto"];
				}
			}

			$result["http_code_test"] = 200;
			$result["result_test"] = $list_transaccion_dep;

			if (!((float)$bono <= (float)$nuevo_bono)) {
				$result["http_code"] = 400;
				$result["status"] = "Montos del bono inválido.";
				echo json_encode($result);
				exit();
			}
		}

		$total_recarga = $monto;

		$query = "
			SELECT 
				(SELECT balance FROM tbl_televentas_clientes_balance 
					WHERE tipo_balance_id=1 AND cliente_id=" . $id_cliente . " 
				) balance,
				(SELECT balance FROM tbl_televentas_clientes_balance 
					WHERE tipo_balance_id=2 AND cliente_id=" . $id_cliente . " 
				) balance_bono_disponible,
				IFNULL((SELECT balance FROM tbl_televentas_clientes_balance 
					WHERE tipo_balance_id=3 AND cliente_id=" . $id_cliente . " 
				), 0) balance_bono_utilizado,
				IFNULL((SELECT balance FROM tbl_televentas_clientes_balance 
					WHERE tipo_balance_id=4 AND cliente_id=" . $id_cliente . " 
				), 0) balance_deposito,
				IFNULL((SELECT balance FROM tbl_televentas_clientes_balance 
					WHERE tipo_balance_id=5 AND cliente_id=" . $id_cliente . " 
				), 0) balance_disponible_retiro";
		$list_query = $mysqli->query($query);
		$list_balance = array();
		while ($li = $list_query->fetch_assoc()) {
			$list_balance[] = $li;
		}

		if (count($list_balance) == 0) {
			$result["http_code"] = 400;
			$result["status"] = "Usted no tiene balance.";
			$result["result"] = $list_balance;
		} elseif (count($list_balance) > 0) {
			$balance_actual = $list_balance[0]["balance"];
			$balance_bono_disponible = $list_balance[0]["balance_bono_disponible"];
			$balance_bono_utilizado = $list_balance[0]["balance_bono_utilizado"];
			$balance_deposito = $list_balance[0]["balance_deposito"];
			$balance_disponible_retiro = $list_balance[0]["balance_disponible_retiro"];
			$nuevo_balance_deposito = 0;
			$nuevo_balance_retiro = 0;
			if ((float) $balance_actual > 0) {

				if ((float) $balance_actual >= (float) $monto) {
					//*********************** VALIDACION BALANCE
					if ((float) $balance_bono_disponible >= (float) $bono) {
						//*********************** VALIDACION BALANCE
						$url = "https://api.apuestatotal.com/v2/betconstruct/televentaspayment/deposit";
						$rq = ['account' => $idweb, 'amount' => $total_recarga];
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

						if ($response_arr["http_code"]) {
							if ($response_arr["http_code"] == 200) {

								//$result["bono_id"] = $bono_id;
								//$result["bono"] = $bono;
								$txn_id_bono = 0;
								if ((float) $bono > 0) {
									$url = "https://api.apuestatotal.com/v2/betconstruct/externaladmin/AddClientToBonus";
									$rq = ['ClientId' => $idweb, 'PartnerBonusId' => $bono_id, 'Amount' => $bono];
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
									$response_bono = curl_exec($curl);
									$err_bono = curl_error($curl);
									$response_arr_bono = json_decode($response_bono, true);
									curl_close($curl);
									if ($response_arr_bono["http_code"]) {
										if ($response_arr_bono["http_code"] == 200) {
											$txn_id_bono = $response_arr_bono["result"]["Data"]["Id"];
											$result["http_code_bono"] = 200;
											$result["status_bono"] = "Ok.";
										} else {
											$result["http_code_bono"] = 400;
											$result["status_bono"] = "El API de Recargas WEB respondio un valor ilegible.";
											$result["result_bono"] = $response_arr_bono;
										}
									} else {
										$result["http_code_bono"] = 400;
										$result["status_bono"] = "El API de Recargas WEB no responde.";
										$result["result_bono"] = $response;
										$result["error_bono"] = $err;
									}
								}

								//echo json_encode($result);
								//exit();

								$balance_nuevo = $balance_actual - $monto;
								$query_2 = " 
									UPDATE tbl_televentas_clientes_balance 
									SET
										balance = " . $balance_nuevo . ",
										updated_at = now()
									WHERE tipo_balance_id = 1
										AND cliente_id = " . $id_cliente;
								$mysqli->query($query_2);

								/***************BALANCE DE DEPOSITO*************/
								$suma_balances = $balance_deposito + $balance_disponible_retiro;
								$restante = 0;
								$resto_deposito = 0;
								if($monto > $balance_deposito){
									$restante = $monto - $balance_deposito;
									$resto_deposito = $monto - $restante;
								}else{
									$resto_deposito = $monto;
								}
								$nuevo_balance_deposito = $balance_deposito - $resto_deposito;
								$query_2 = " 
									UPDATE tbl_televentas_clientes_balance 
									SET
										balance = balance - " . $resto_deposito . ",
										updated_at = now()
									WHERE tipo_balance_id = 4
										AND cliente_id = " . $id_cliente;
								$mysqli->query($query_2);
								
								if($restante <= $balance_disponible_retiro){
									$nuevo_balance_retiro = $balance_disponible_retiro - $restante;
									$query_2 = " 
										UPDATE tbl_televentas_clientes_balance 
										SET
											balance = balance - " . $restante . ",
											updated_at = now()
										WHERE tipo_balance_id = 5
											AND cliente_id = " . $id_cliente;
									$mysqli->query($query_2);
								}

								if ((int)$txn_id_bono > 0) {
									$balance_bono_disponible_NUEVO = $balance_bono_disponible - $bono;
									$query_2 = " 
										UPDATE tbl_televentas_clientes_balance 
										SET
											balance = " . $balance_bono_disponible_NUEVO . ",
											updated_at = now()
										WHERE tipo_balance_id = 2
											AND cliente_id = " . $id_cliente;
									$mysqli->query($query_2);

									$balance_bono_utilizado_NUEVO = $balance_bono_utilizado + $bono;
									$query_2 = " 
										UPDATE tbl_televentas_clientes_balance 
										SET
											balance = " . $balance_bono_utilizado_NUEVO . ",
											updated_at = now()
										WHERE tipo_balance_id = 3
											AND cliente_id = " . $id_cliente;
									$mysqli->query($query_2);


									$query_3 = " 
										UPDATE tbl_televentas_clientes_transaccion 
										SET
											valid_bono = 1,
											updated_at = now()
										WHERE id = " . $id_deposito;
									$result["tbl_televentas_clientes_transaccion"] = $query_3;
									$mysqli->query($query_3);

									$insert_command = "
										INSERT INTO tbl_televentas_clientes_transaccion (
											tipo_id,
											cliente_id,
											turno_id,
											cc_id,
											web_id,
											txn_id,
											bono_id,
											monto,
											bono_monto,
											total_recarga,
											nuevo_balance,
											estado,
											user_id,
											created_at,
											transaccion_id
										) VALUES (
											10,
											" . $id_cliente . ",
											" . $turno_id . ",
											" . $cc_id . ",
											" . $idweb . ",
											" . $txn_id_bono . ",
											" . $bono_id . ",
											0,
											" . $bono . ",
											0,
											" . $balance_nuevo . ",
											1,
											" . $usuario_id . ",
											now(),
											" . $id_deposito . "
										)
									";
									$mysqli->query($insert_command);
								}

								$result = $response_arr["result"];
								$txn_id = $result["txn_id"];

								$insert_command = "
									INSERT INTO tbl_televentas_clientes_transaccion (
										tipo_id,
										cliente_id,
										turno_id,
										cc_id,
										web_id,
										txn_id,
										bono_id,
										monto,
										bono_monto,
										total_recarga,
										nuevo_balance,
										estado,
										user_id,
										created_at,
										transaccion_id
									) VALUES (
										2,
										" . $id_cliente . ",
										" . $turno_id . ",
										" . $cc_id . ",
										" . $idweb . ",
										" . $txn_id . ",
										" . $bono_id . ",
										" . $monto . ",
										0,
										" . $total_recarga . ",
										" . $balance_nuevo . ",
										1,
										" . $usuario_id . ",
										now(),
										" . $id_deposito . "
									)
								";
								$mysqli->query($insert_command);


								$query_3 = "SELECT * FROM tbl_televentas_clientes_transaccion  ";
								$query_3 .= " WHERE tipo_id=2 AND txn_id='" . $txn_id . "' ";
								$query_3 .= " AND cliente_id='" . $id_cliente . "' AND user_id='" . $usuario_id . "' ";
								$query_3 .= " AND turno_id='" . $turno_id . "' AND web_id='" . $idweb . "' ";
								$query_3 .= " AND nuevo_balance='" . $balance_nuevo . "' ";
								$query_3 .= " AND monto='" . $monto . "' ";
								$query_3 .= " AND total_recarga='" . $total_recarga . "' ";
								$list_query = $mysqli->query($query_3);
								$list_transaccion = array();
								while ($li = $list_query->fetch_assoc()) {
									$list_transaccion[] = $li;
								}

								if (count($list_transaccion) == 0) {
									$result["http_code"] = 400;
									$result["status"] = "Se realizó la recarga, sin embargo no se pudo guardar la transacción.";
								} elseif (count($list_transaccion) === 1) {

									query_tbl_televentas_clientes_balance_transaccion('insert', $list_transaccion[0]["id"], $id_cliente, 1, 
										$balance_actual, $monto, $balance_nuevo);

									query_tbl_televentas_clientes_balance_transaccion('insert', $list_transaccion[0]["id"], $id_cliente, 4, 
										$balance_deposito, $resto_deposito, $nuevo_balance_deposito);

									query_tbl_televentas_clientes_balance_transaccion('insert', $list_transaccion[0]["id"], $id_cliente, 5, 
										$balance_disponible_retiro, $restante, $nuevo_balance_retiro);

									if ((int)$txn_id_bono > 0) {

										query_tbl_televentas_clientes_balance_transaccion('insert', $list_transaccion[0]["id"], $id_cliente, 2, 
											$balance_bono_disponible, $bono, $balance_bono_disponible_NUEVO);

										query_tbl_televentas_clientes_balance_transaccion('insert', $list_transaccion[0]["id"], $id_cliente, 3, 
											$balance_bono_utilizado, $bono, $balance_bono_utilizado_NUEVO);
									}

									$result["http_code"] = 200;
									$result["status"] = "ok.";
								} else {
									$result["http_code"] = 400;
									$result["status"] = "Se realizó la recarga, sin embargo ocurrió un error al guardar la transacción.";
								}
							} elseif ($response_arr["http_code"] == 400) {
								$query_2 = "SELECT * FROM bc_apuestatotal.tbl_Client WHERE col_Id='" . $idweb . "' ";
								$list_query_2 = $mysqli->query($query_2);
								$list_web = array();
								while ($li = $list_query_2->fetch_assoc()) {
									$list_web[] = $li;
								}
								if (count($list_web) === 0) {
									$result["http_code"] = 400;
									$result["status"] = "El ID-WEB no existe.";
									$result["result"] = $response_arr;
								} elseif (count($list_web) === 1) {
									$result["http_code"] = 400;
									$result["status"] = "Problemas con el API de Recargas WEB.";
									$result["result"] = $response_arr;
								} elseif (count($list_web) > 1) {
									$result["http_code"] = 400;
									$result["status"] = "Ocurrió un error al consultar el ID-WEB. (Duplicidad)";
									$result["result"] = $response_arr;
								} else {
									$result["http_code"] = 400;
									$result["status"] = "Ocurrió un error al consultar el ID-WEB.";
									$result["result"] = $response_arr;
								}
							} else {
								$result["http_code"] = 400;
								$result["status"] = "El API de Recargas WEB respondio un valor ilegible.";
								$result["result"] = $response_arr;
							}
						} else {
							$result["http_code"] = 400;
							$result["status"] = "El API de Recargas WEB no responde.";
							$result["result"] = $response;
							$result["error"] = $err;
						}
						if ($err) {
							$result["http_code"] = 400;
							$result["status"] = "Ocurrió un error al consumir el API de Recargas WEB.";
							$result["result"] = $response;
							$result["error"] = "cURL Error #:" . $err;
						}
						//*********************** VALIDACION BALANCE
					} else {
						$result["http_code"] = 400;
						$result["status"] = "El balance de bono disponible es menor al bono a recargar.";
						$result["result"] = $list_balance;
					}
				} else {
					$result["http_code"] = 400;
					$result["status"] = "El balance es menor al total a recargar.";
					$result["result"] = $list_balance;
				}
			} else {
				$result["http_code"] = 400;
				$result["status"] = "El balance esta en 0.";
				$result["result"] = $list_balance;
			}
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al consultar el balance.";
			$result["result"] = $list_balance;
		}

}








//*******************************************************************************************************************
// REGISTRAR APUESTA DETALLE
//*******************************************************************************************************************
if ($_POST["accion"] === "registrar_apuesta_detalle") {

	$proveedor_id = $_POST["proveedor"];
	$id_cliente   = $_POST["id_cliente"];
	$balance_tipo = $_POST["balance_tipo"];
	$id_bet       = $_POST["id_bet"];
	$evento_dineroat_id = $_POST["evento_dineroat_id"];

	if ( (int)$_POST["evento_dineroat_id"] === 0 ) {
		$evento_dineroat_id = 'null';
	}

	$result["id_bet"]  = $id_bet;
	$result["amount"]  = 0;
	$result["jackpot"] = 0;

	$query_1 = "
		SELECT 
			ct.id, 
			ct.estado, 
			ct.monto, 
			ct.api_id proveedor_id, 
			a.name proveedor_name 
		FROM tbl_televentas_clientes_transaccion ct
		join tbl_televentas_proveedor a on a.id = ct.api_id
		WHERE ct.txn_id = '$id_bet' 
			AND ct.api_id = '$proveedor_id' 
			AND ct.tipo_id = 4 
			AND ct.estado IN (0,1) 
		ORDER BY ct.estado DESC, ct.id ASC
		";
	$list_query = $mysqli->query($query_1);
	$list_1 = array();
	if ($mysqli->error) {
		$result["select_error"] = $mysqli->error;
		$result["select_query"] = $query_1;
		//echo json_encode($result);exit();
	}
	while ($li_1 = $list_query->fetch_assoc()) {
		$list_1[] = $li_1;
	}
	if (count($list_1) == 0) {
		$switch_continue = 0;
		$local_name = "";

		// BC **************************************************************************************
		$array_bet_bc = array();
		if((int)$proveedor_id===1){
			$result["proveedor_name"] = 'BC';
			$array_bet_bc = api_betconstruct_get_bet($id_bet);
			if(!isset($array_bet_bc["http_code"])) {
				$result["http_code"] = 400;
				$result["color_status"] = 'black';
				$result["status"] = "Ticket no encontrado.";
				echo json_encode($result);
				exit();
			}
			if($array_bet_bc["http_code"]==200) {
				$local_name_temp = mb_strtoupper($array_bet_bc["result"][0]["local_name"], 'UTF-8');
				$findme = 'TELEVENTAS';
				$pos = strpos($local_name_temp, $findme);
				if ($pos === false) {
					$result["http_code"] = 400;
					$result["color_status"] = 'Red';
					$result["status"] = "Ticket pagado en lugar de cobro: " . $local_name_temp;
					echo json_encode($result);exit();
				} else {
					$local_name = $local_name_temp;
					$result["amount"] = $array_bet_bc["result"][0]["amount"];
				}
			} else {
				$result["http_code"] = 400;
				$result["color_status"] = 'black';
				$result["status"] = "Ticket no encontrado.";
				$result["result_api"] = $array_bet_bc;
				echo json_encode($result);exit();
			}
		}
		
		// ALTENAR ****************************************************************************
		$array_bet_calimaco = array();
		if((int)$proveedor_id===5){
			$result["proveedor_name"] = "Altenar";
			$array_bet_calimaco = api_calimaco_get_bet($id_cliente, $id_bet);
			$result["array_bet_calimaco"] = $array_bet_calimaco;
			if(isset($array_bet_calimaco["result"])){
				if($array_bet_calimaco["result"]==="OK") {
					$result["amount"] = $array_bet_calimaco["bet"]["wager"]/100;
				} else {
					$result["http_code"] = 400;
					$result["color_status"] = 'black';
					$result["status"] = "Error: Apuesta no existe o le pertenece a otro cliente.";
					$result["result_api"] = $array_bet_calimaco;
					echo json_encode($result);exit();
				}
			} else {
				$result["http_code"] = 400;
				$result["color_status"] = 'black';
				$result["status"] = "Error: Apuesta no existe o le pertenece a otro cliente.";
				$result["result_api"] = $array_bet_calimaco;
				echo json_encode($result);exit();
			}
		}
		
		// GOLDEN RACE ************************************************************************
		$array_bet_gr = array();
		if((int)$proveedor_id===3){
			$result["proveedor_name"] = 'Golden Race';
			$array_bet_gr = api_goldenrace_get_ticket($id_bet);
			if(isset($array_bet_gr["ticket_id"])){
				$result["amount"] = $array_bet_gr["stake_amount"];
			} else {
				$result["http_code"] = 400;
				$result["color_status"] = 'black';
				$result["status"] = "Ticket no encontrado";
				$result["result_api"] = $array_bet_gr;
				echo json_encode($result);exit();
			}
		}

		// BINGO ******************************************************************************
		$array_bet_bingo = array();
		if((int)$proveedor_id===4){
			$result["proveedor_name"] = 'Bingo';
			$array_bet_bingo = api_bingo_get_bet($id_bet);
			if(isset($array_bet_bingo["ticket_id"])) {
				if((int)$array_bet_bingo["validate_tls_creado"] === 1) {
					$result["amount"] = $array_bet_bingo["stake_amount"];
				} else {
					$result["http_code"] = 400;
					$result["color_status"] = 'black';
					$result["status"] = "El Ticket no pertenece a Televentas.";
					$result["result_api"] = $array_bet_bingo;
					echo json_encode($result);exit();
				}
			} else {
				$result["http_code"] = 400;
				$result["color_status"] = 'black';
				$result["status"] = "Ticket no encontrado";
				$result["result_api"] = $array_bet_bingo;
				echo json_encode($result);exit();
			}
		}

		$insert_command = "
			INSERT INTO tbl_televentas_clientes_transaccion (
				tipo_id,
				api_id,
				txn_id,
				cliente_id,
				user_id,
				turno_id,
				monto,
				nuevo_balance,
				bono_monto,
				id_tipo_balance,
				evento_dineroat_id,
				estado,
				created_at
			) VALUES (
				4,
				$proveedor_id,
				'" . $id_bet . "',
				0,
				" . $usuario_id . ",
				" . $turno_id . ",
				" . $result["amount"] . ",
				0,
				0,
				" . $balance_tipo . ",
				$evento_dineroat_id,
				0,
				now()
			)";
		$mysqli->query($insert_command);
		if ($mysqli->error) {
			//$result["insert_query"] = $insert_command;
			$result["insert_error"] = $mysqli->error;
		}
		$query_3 = "
			SELECT id 
			FROM tbl_televentas_clientes_transaccion  
			WHERE tipo_id = 4 
				AND txn_id = '$id_bet' 
				AND api_id = '$proveedor_id' 
				AND user_id = '$usuario_id' 
				AND turno_id = '$turno_id' 
				AND id_tipo_balance = '$balance_tipo' 
				AND estado = '0' 
			";
		$list_query = $mysqli->query($query_3);
		$list_transaccion = array();
		while ($li = $list_query->fetch_assoc()) {
			$list_transaccion[] = $li;
		}
		if (count($list_transaccion) == 0) {
			$result["http_code"] = 400;
			$result["color_status"] = 'black';
			$result["status"] = "Se registro la apuesta, sin embargo no se pudo guardar la transacción.";
			//$result["list_transaccion"] = $list_transaccion;
			//$result["query"] = $query_3;
		} elseif (count($list_transaccion) === 1) {
			$result["http_code"] = 200;
			$result["color_status"] = 'black';
			$result["status"] = "ok";
		} else {
			$result["http_code"] = 400;
			$result["color_status"] = 'black';
			$result["status"] = "Se registro la apuesta, sin embargo ocurrió un error al guardar la transacción.";
			//$result["list_transaccion"] = $list_transaccion;
			//$result["query"] = $query_3;
		}

	} elseif (count($list_1) > 0) {
		$estado = $list_1[0]["estado"];
		if ((int) $estado === 0) {
			$query_2 = " 
				UPDATE tbl_televentas_clientes_transaccion 
				SET
					turno_id = " . $turno_id . ",
					id_tipo_balance = " . $balance_tipo . ",
					evento_dineroat_id = " . $evento_dineroat_id . ",
					update_user_id = " . $usuario_id . ",
					updated_at = now()
				WHERE txn_id = '" . $id_bet . "' AND tipo_id = 4 ";
			$mysqli->query($query_2);

			if(count($list_1)>1){
				$query_update_status = " 
					UPDATE tbl_televentas_clientes_transaccion 
					SET
						estado = '5',
						update_user_id = '$usuario_id',
						updated_at = now()
					WHERE 
						tipo_id = 4 
						AND estado = '0' 
						AND txn_id = '$id_bet' 
						AND api_id = '$proveedor_id' 
						AND id != '".$list_1[0]['id']."'
					";
				$mysqli->query($query_update_status);
			}

			$result["http_code"] = 200;
			$result["color_status"] = 'black';
			$result["status"] = "ok";
			$result["amount"] = $list_1[0]["monto"];
			$result["proveedor_name"] = $list_1[0]["proveedor_name"];
			$result["result"] = $list_1[0];
		} else {
			$result["http_code"] = 400;
			$result["color_status"] = 'black';
			$result["status"] = "Apuesta ya registrada";
			$result["amount"] = $list_1[0]["monto"];
			$result["proveedor_name"] = $list_1[0]["proveedor_name"];
		}
	} else {
		$result["http_code"] = 400;
		$result["color_status"] = 'black';
		$result["status"] = "Ocurrió un error al consultar la apuesta.";
	}

}


//*******************************************************************************************************************
// REGISTRAR APUESTA
//*******************************************************************************************************************
if ($_POST["accion"] === "registrar_apuesta") {

	$id_cliente   = $_POST["id_cliente"];
	$balance_tipo = $_POST["balance_tipo"];
	$cant_bet     = $_POST["cant_bet"];
	$array_bet    = $_POST["array_bet"];
	$total_bet    = $_POST["total_bet"];

	$query_0= "
		SELECT 
			a.*
		FROM (
		SELECT
			ct.txn_id,
			MIN(ct.id) min_transaccion_id,
			COUNT( * ) cant 
		FROM
			tbl_televentas_clientes_transaccion ct 
		WHERE
			ct.tipo_id = 4 
			AND ct.estado = '0' 
			AND ct.txn_id IN ( " . $array_bet . " ) 
		GROUP BY ct.txn_id
		) a
		WHERE a.cant>1
	";
	$list_query = $mysqli->query($query_0);
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["error_select_0"] = $mysqli->error;
		$result["status"] = "Ocurrió un error al consultar los tickets.";
		echo json_encode($result);exit();
	}
	$list_0 = array();
	while ($li_0 = $list_query->fetch_assoc()) {
		$list_0[] = $li_0;
	}
	if (count($list_0) > 0) {
		foreach ($list_0 as $l) {
			$query_update_status = " 
				UPDATE tbl_televentas_clientes_transaccion 
				SET
					estado = '5',
					update_user_id = '$usuario_id',
					updated_at = now()
				WHERE 
					tipo_id = 4 
					AND estado = '0' 
					AND txn_id = '".$l['txn_id']."' 
					AND id != '".$l['min_transaccion_id']."'
				";
			$mysqli->query($query_update_status);
		}
	}

	$query_1 = "
		SELECT
			SUM( ct.monto ) monto_total,
			COUNT( * ) cantidad 
		FROM
			tbl_televentas_clientes_transaccion ct 
		WHERE
			ct.tipo_id=4 
			AND ct.estado = '0' 
			AND ct.txn_id IN ( " . $array_bet . " ) 
	";
	//$result["query_1"] = $query_1;
	$list_query = $mysqli->query($query_1);
	$list_1 = array();
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["error_select_1"] = $mysqli->error;
		$result["status"] = "Ocurrió un error al consultar los tickets.";
		echo json_encode($result);exit();
	}
	while ($li_1 = $list_query->fetch_assoc()) {
		$list_1[] = $li_1;
	}
	if (count($list_1) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los tickets.";
		echo json_encode($result);exit();
	} elseif (count($list_1) > 0) {

		$list_1_monto_total = $list_1[0]["monto_total"];
		$list_1_cantidad = $list_1[0]["cantidad"];

		if ((double) $total_bet !== (double) $list_1_monto_total) {
			$result["http_code"] = 400;
			$result["status"] = "Existen diferencias con el total enviado y el total en data.";
			echo json_encode($result);exit();
		}
		if ((double) $cant_bet !== (double) $list_1_cantidad) {
			$result["http_code"] = 400;
			$result["status"] = "Existen diferencias con el total enviado y el total en data.";
			echo json_encode($result);exit();
		}

		$list_balance = array();
		$list_balance = obtener_balances($id_cliente);
		
		if (count($list_balance) === 1) {

			$monto = (double)$list_1_monto_total;

			$balance_total_actual     = (double)$list_balance[0]["balance"];
			$balance_deposito_actual  = (double)$list_balance[0]["balance_deposito"];
			$balance_retiro_actual    = (double)$list_balance[0]["balance_retiro_disponible"];
			$balance_dinero_at_actual = (double)$list_balance[0]["balance_dinero_at"];

			$balance_total_nuevo      = (double)$balance_total_actual - (double)$monto;
			$balance_dinero_at_nuevo  = (double)$balance_dinero_at_actual - (double)$monto;
			$balance_deposito_nuevo   = (double)$list_balance[0]["balance_deposito"];
			$balance_retiro_nuevo     = (double)$list_balance[0]["balance_retiro_disponible"];

			if (!($balance_total_actual > 0)) {
				$result["http_code"] = 400;
				$result["status"] = "El balance esta en 0.";
				$result["result"] = $list_balance;
				echo json_encode($result);exit();
			}
			if (!($balance_total_actual >= $list_1_monto_total)) {
				$result["http_code"] = 400;
				$result["status"] = "El balance es menor al total a recargar.";
				$result["result"] = $list_balance;
				echo json_encode($result);exit();
			}

			if ((int)$balance_tipo === 1) {
				//*********************** BALANCE TOTAL
				query_tbl_televentas_clientes_balance('update', $id_cliente, 1, $balance_total_nuevo);

				//*********************** BALANCE DEPOSITO
				if((double)$balance_deposito_actual>0){
					if((double)$balance_deposito_nuevo>(double)$monto){
						$balance_deposito_nuevo = (double)$balance_deposito_nuevo-(double)$monto;
						$monto = 0;
					} else {
						$monto = (double)$monto-(double)$balance_deposito_nuevo;
						$balance_deposito_nuevo = 0;
					}
					query_tbl_televentas_clientes_balance('update', $id_cliente, 4, $balance_deposito_nuevo);
				}

				//*********************** BALANCE RETIRO
				if((double)$monto>0){
					if((double)$balance_retiro_actual>0){
						if((double)$balance_retiro_nuevo>(double)$monto){
							$balance_retiro_nuevo = $balance_retiro_nuevo-$monto;
							$monto = 0;
						} else {
							$monto = (double)$monto-(double)$balance_retiro_nuevo;
							$balance_retiro_nuevo = 0;
						}
						query_tbl_televentas_clientes_balance('update', $id_cliente, 5, $balance_retiro_nuevo);
					}
				}
			}
			if ((int)$balance_tipo === 6) {

				if (!($balance_dinero_at_actual > 0)) {
					$result["http_code"] = 400;
					$result["status"] = "El balance promocional esta en 0.";
					$result["result"] = $list_balance;
					echo json_encode($result);exit();
				}
				if (!($balance_dinero_at_actual >= $list_1_monto_total)) {
					$result["http_code"] = 400;
					$result["status"] = "El balance promocional es menor al total a recargar.";
					$result["result"] = $list_balance;
					echo json_encode($result);exit();
				}

				query_tbl_televentas_clientes_balance('update', $id_cliente, 6, $balance_dinero_at_nuevo);


			}



			//**************************************************
			//*************** UPDATE TRANSACCIONES *************
			$query = " 
				UPDATE tbl_televentas_clientes_transaccion 
				SET
					estado = '1',
					cliente_id = '" . $id_cliente . "',
					turno_id = '" . $turno_id . "',
					cc_id = '" . $cc_id . "',
					update_user_id = '" . $usuario_id . "',
					updated_at = now(),
					created_at = now(),
					user_id = '" . $usuario_id . "'
				WHERE tipo_id=4 AND txn_id IN ( " . $array_bet . " ) and estado=0 
				";
			$mysqli->query($query);
			if ($mysqli->error) {
				$result["error_update_transacciones"] = $mysqli->error;
			}

			$query = "
				SELECT
					ct.id cod_transaccion,
					ct.txn_id,
					ct.monto
				FROM
					tbl_televentas_clientes_transaccion ct 
				WHERE
					ct.tipo_id = 4 
					AND ct.estado = '1' 
					AND ct.cliente_id = '" . $id_cliente . "' 
					AND ct.update_user_id = '" . $usuario_id . "' 
					AND ct.txn_id IN ( " . $array_bet . " ) 
			";
			$list_query = $mysqli->query($query);
			if ($mysqli->error) {
				$result["error_select_3"] = $mysqli->error;
			}
			$list_transaccion = array();
			while ($li = $list_query->fetch_assoc()) {
				$list_transaccion[] = $li;
			}
			if (count($list_transaccion) == 0) {
				$result["http_code"] = 400;
				$result["status"] = "Se registro la apuesta, sin embargo no se pudo guardar la transacción.";
			} elseif (count($list_transaccion) > 0) {

				$temp_balance_total_actual     = (double)$balance_total_actual;
				$temp_balance_deposito_actual  = (double)$balance_deposito_actual;
				$temp_balance_retiro_actual    = (double)$balance_retiro_actual;
				$temp_balance_dinero_at_actual = (double)$balance_dinero_at_actual;

				foreach ($list_transaccion as $bet) {
					$temp_bet_id = $bet['cod_transaccion'];
					$temp_monto = (double)$bet['monto'];

					if ((int)$balance_tipo === 1) {

						$temp_balance_total_nuevo    = (double)$temp_balance_total_actual - (double)$temp_monto;
						$temp_balance_deposito_nuevo = (double)$temp_balance_deposito_actual;
						$temp_balance_retiro_nuevo   = (double)$temp_balance_retiro_actual;

						$query = " 
							UPDATE tbl_televentas_clientes_transaccion 
							SET nuevo_balance = '" . $temp_balance_total_nuevo . "',
								updated_at = now(),
								created_at = now()
							WHERE id = " . $temp_bet_id . " 
							";
						$mysqli->query($query);
						if ($mysqli->error) {
							$result["error_update_".$temp_bet_id] = $mysqli->error;
						}

						query_tbl_televentas_clientes_balance_transaccion('insert', $temp_bet_id, $id_cliente, 1, 
							$temp_balance_total_actual, $temp_monto, $temp_balance_total_nuevo);

						//*********************** BALANCE DEPOSITO
						if((double)$temp_balance_deposito_actual>0){
							$temp_monto_balance_tipo4 = 0;
							if((double)$temp_balance_deposito_actual>(double)$temp_monto){
								$temp_balance_deposito_nuevo = (double)$temp_balance_deposito_nuevo - (double)$temp_monto;
								$temp_monto_balance_tipo4    = (double)$temp_monto;
								$temp_monto = 0;
							} else {
								$temp_monto = (double)$temp_monto - (double)$temp_balance_deposito_actual;
								$temp_monto_balance_tipo4 = (double)$temp_balance_deposito_actual;
								$temp_balance_deposito_nuevo = 0;
							}
							query_tbl_televentas_clientes_balance_transaccion('insert', $temp_bet_id, $id_cliente, 4, 
								$temp_balance_deposito_actual, $temp_monto_balance_tipo4, $temp_balance_deposito_nuevo);
						}

						//*********************** BALANCE RETIRO
						if((double)$temp_monto>0){
							if((double)$temp_balance_retiro_actual>0){
								$temp_monto_balance_tipo5 = 0;
								if((double)$temp_balance_retiro_actual>(double)$temp_monto){
									$temp_balance_retiro_nuevo = (double)$temp_balance_retiro_actual - (double)$temp_monto;
									$temp_monto_balance_tipo5 = (double)$temp_monto;
									$temp_monto = 0;
								} else {
									$temp_monto = (double)$temp_monto - (double)$temp_balance_retiro_actual;
									$temp_monto_balance_tipo5 = (double)$temp_balance_retiro_actual;
									$temp_balance_retiro_nuevo = 0;
								}
								query_tbl_televentas_clientes_balance_transaccion('insert', $temp_bet_id, $id_cliente, 5, 
									$temp_balance_retiro_actual, $temp_monto_balance_tipo5, $temp_balance_retiro_nuevo);
							}
						}

						$temp_balance_total_actual    = $temp_balance_total_nuevo;
						$temp_balance_deposito_actual = $temp_balance_deposito_nuevo;
						$temp_balance_retiro_actual   = $temp_balance_retiro_nuevo;
					}

					if ((int)$balance_tipo === 6) {
						$temp_balance_dinero_at_nuevo = (double)$temp_balance_dinero_at_actual - (double)$temp_monto;

						$query = " 
							UPDATE tbl_televentas_clientes_transaccion 
							SET nuevo_balance = '" . $temp_balance_dinero_at_nuevo . "',
								updated_at = now(),
								created_at = now()
							WHERE id = " . $temp_bet_id . " 
							";
						$mysqli->query($query);
						if ($mysqli->error) {
							$result["error_update_".$temp_bet_id] = $mysqli->error;
						}

						query_tbl_televentas_clientes_balance_transaccion('insert', $temp_bet_id, $id_cliente, 6, 
							$temp_balance_dinero_at_actual, $temp_monto, $temp_balance_dinero_at_nuevo);

						$temp_balance_dinero_at_actual = $temp_balance_dinero_at_nuevo;
					}

				}

				sec_tlv_asignar_etiqueta_test($id_cliente);

				//$result["query_command"] = $insert_command;
				$result["http_code"] = 200;
				$result["status"] = "Ok";
				//$result["list_transaccion"] = $list_transaccion;
			} else {
				$result["http_code"] = 400;
				$result["status"] = "Se registro la apuesta, sin embargo ocurrió un error al guardar la transacción.";
			}

		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al consultar el balance.";
			$result["result"] = $list_balance;
			echo json_encode($result);exit();
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar la apuesta.";
		echo json_encode($result);exit();
	}

}












//*******************************************************************************************************************
// PAGAR APUESTA DETALLE
//*******************************************************************************************************************
if ($_POST["accion"] === "pagar_apuesta_detalle") {

	$proveedor_id = $_POST["proveedor"];
	$id_cliente   = $_POST["id_cliente"];
	$id_bet       = $_POST["id_bet"];
	$evento_dineroat_id = (int)$_POST["evento_dineroat_id"];

	$tipo_balance_id = "NULL";
	if ( $evento_dineroat_id == 0 ) {
		$evento_dineroat_id = "NULL";
		$tipo_balance_id = 1;
	} elseif ( $evento_dineroat_id>0 ) {
		$tipo_balance_id = 6;
	}

	date_default_timezone_set("America/Lima");
	$fecha_actual = date('Y-m-d');

	$result["id_bet"]  = $id_bet;
	$result["amount"]  = 0;
	$result["jackpot"] = 0;

	$query_jackpot = "0";
	if((int)$proveedor_id===3){
		$query_jackpot = "(
			SELECT IFNULL(gr.jackpot, 0) jackpot 
			FROM tbl_repositorio_tickets_goldenrace gr 
			WHERE gr.ticket_id = '" . $id_bet . "' and created_at > '2022-01-01 00:00:00'
			)";
	}
	if((int)$proveedor_id===4){
		$query_jackpot = "(
			SELECT IFNULL(b.jackpot_amount, 0) jackpot 
			FROM tbl_repositorio_bingo_tickets b 
			WHERE b.ticket_id = '" . $id_bet . "' 
			)";
	}
	$query_1 = "
		SELECT 
			ct.id, 
			ct.estado, 
			ct.cliente_id, 
			ct.tipo_id, 
			ct.monto, 
			IFNULL(ct.evento_dineroat_id, 0) id_evento_dinero_at_ticket,
			ct.api_id proveedor_id, 
			a.name proveedor_name,
			$query_jackpot jackpot 
		FROM tbl_televentas_clientes_transaccion ct
		JOIN tbl_televentas_proveedor a on a.id = ct.api_id
		WHERE ct.txn_id = '$id_bet' 
			AND ct.api_id = '$proveedor_id' 
			AND ct.tipo_id IN (5,19,34) 
			AND ct.estado in (0,1) 
		ORDER BY ct.estado DESC, ct.id ASC
		";
	//$result["query_1"] = $query_1;
	$list_query = $mysqli->query($query_1);
	$list_1 = array();
	while ($li_1 = $list_query->fetch_assoc()) {
		$list_1[] = $li_1;
	}
	if ($mysqli->error) {
		$result["query_1_error"] = $mysqli->error;
	}

	if ( isset($list_1[0]["id_evento_dinero_at_ticket"]) && (int)$list_1[0]["id_evento_dinero_at_ticket"]>0 ) {
		if ( (int)$list_1[0]["id_evento_dinero_at_ticket"] != $evento_dineroat_id ) {
			$result["http_code"] = 400;
			$result["id_evento_dinero_at_ticket"] = $list_1[0]["id_evento_dinero_at_ticket"];
			$result["evento_dineroat_id"] = $evento_dineroat_id;
			if ( $evento_dineroat_id > 0 ) {
				$result["status"] = "El ticket tiene un evento promocional distinto.";
			} else {
				$result["status"] = "Estas intentando pagar una apuesta que fue creada con Dinero Promocional.";
			}
			echo json_encode($result);exit();
		}
	}

	// Consultar si el ticket fue registrado con dinero AT
	$query_ticket_evento_dineroat = "
		SELECT 
			ct.id, 
			ct.estado, 
			ct.cliente_id, 
			ct.tipo_id, 
			ct.monto, 
			IFNULL(ct.evento_dineroat_id, 0) id_ev_din_at_ticket,
			ct.api_id proveedor_id, 
			a.name proveedor_name
		FROM tbl_televentas_clientes_transaccion ct
		JOIN tbl_televentas_proveedor a on a.id = ct.api_id
		WHERE ct.txn_id = '$id_bet' 
			AND ct.api_id = '$proveedor_id' 
			AND ct.tipo_id = 4 
			AND ct.estado IN (0,1)
		ORDER BY ct.estado DESC, ct.id ASC
		";
	$list_query_ticket_evento_dineroat = $mysqli->query($query_ticket_evento_dineroat);
	$list_ticket_dat = array();
	while ($li_1 = $list_query_ticket_evento_dineroat->fetch_assoc()) {
		$list_ticket_dat[] = $li_1;
	}
	if ($mysqli->error) {
		$result["query_ticket_evento_dineroat_error"] = $mysqli->error;
	}

	if ( isset($list_ticket_dat[0]["id_ev_din_at_ticket"]) ) {
		if ( (int)$list_ticket_dat[0]["id_ev_din_at_ticket"]>0 ) {
			if ( (int)$list_ticket_dat[0]["id_ev_din_at_ticket"] != $evento_dineroat_id ) {
				$result["http_code"] = 400;
				$result["id_ev_din_at_ticket"] = $list_ticket_dat[0]["id_ev_din_at_ticket"];
				$result["evento_dineroat_id"] = $evento_dineroat_id;
				if ( $evento_dineroat_id > 0 ) {
					$result["status"] = "¡El ticket tiene un evento promocional distinto!";
				} else {
					$result["status"] = "¡Estas intentando pagar una apuesta que fue creada con Dinero Promocional!";
				}
				echo json_encode($result);exit();
			}
		} else {
			if ( $evento_dineroat_id > 0 ) {
				$result["http_code"] = 400;
				$result["id_ev_din_at_ticket"] = $list_ticket_dat[0]["id_ev_din_at_ticket"];
				$result["evento_dineroat_id"] = $evento_dineroat_id;
				$result["status"] = "¡El ticket fue comprado con saldo real!";
				echo json_encode($result);exit();
			}
		}
	}
	// FIN - Consultar si el ticket fue registrado con dinero AT

	if (count($list_1) == 0) {
		$tipo_id = 5;

		// BET CONSTRUCT ******************************************************************************
		$array_bet_bc = array();
		if((int)$proveedor_id === 1) {
			$result["proveedor_name"] = 'BC';
			$array_bet_bc = api_betconstruct_get_bet($id_bet);
			if(!isset($array_bet_bc["http_code"])) {
				$result["http_code"] = 400;
				$result["status"] = "Ticket no encontrado.";
				echo json_encode($result);
				exit();
			}
			if((int)$array_bet_bc["http_code"]!==200) {
				$result["http_code"] = 400;
				$result["status"] = "Ticket no encontrado.";
				echo json_encode($result);
				exit();
			}

			// Validacion 30 dias
			$calc_date_bet_bc = substr($array_bet_bc["result"][0]["calc_date"], 0, 10);
			$diff_dias_bet_bc = (new DateTime($calc_date_bet_bc))->diff((new DateTime(date('Y-m-d'))));
			if((int)$diff_dias_bet_bc->format('%R%a')>30){
				$switch_continue = 1;
				$result["http_code"] = 400;
				$result["status"] = "El Ticket supera el limite de 30 días.";
				echo json_encode($result);
				exit();
			}

			$local_name_paid_bet_bc = mb_strtoupper($array_bet_bc["result"][0]["paid_betshop"], 'UTF-8');// Local PAGO
			$estado_bet_bc = $array_bet_bc["result"][0]["state"]; // Local PAGO
			// Validacion de estado
			if ((int) $estado_bet_bc === 4 || (int) $estado_bet_bc === 5 || (int) $estado_bet_bc === 2) {//Validacion de ticket pagado
				if (is_null($local_name_paid_bet_bc)) {
					$result["http_code"] = 400;
					$result["status"] = "El Ticket no ha sido marcado como pagado en Televentas.";
					echo json_encode($result);
					exit();
				} else {
					// Validacion de tls
					$findme = 'TELEVENTAS';
					$pos = strpos($local_name_paid_bet_bc, $findme);
					if ($pos === false) {
						$result["http_code"] = 400;
						$result["status"] = "Error: Apuesta Pagada en " . $local_name_paid_bet_bc;
						//$result["array_bet_bc"] = $array_bet_bc;
						echo json_encode($result);
						exit();
					} else {
						$result["amount"] = $array_bet_bc["result"][0]["winning"]; //Monto PAGADO
					}
				}
			} else {
				$result["http_code"] = 400;
				$result["status"] = "El Ticket no ha sido marcado como pagado.";
				echo json_encode($result);
				exit();
			}
			
		}


		// ALTENAR ******************************************************************************
		$array_bet_calimaco = array();
		$array_bet_calimaco_paid = array();
		if((int)$proveedor_id===5){
			$result["proveedor_name"] = "Altenar";
			//Validar si la apuesta pertenece al cliente correcto
			$query_txn = "
				SELECT
					tct.cliente_id
				FROM
					tbl_televentas_clientes_transaccion tct 
				WHERE
					tct.txn_id = '$id_bet' 
					AND tct.tipo_id = '4' 
					AND tct.api_id = '$proveedor_id' 
					AND tct.estado = '1' 
				ORDER BY tct.id desc 
				LIMIT 1
			";
			$list_query = $mysqli->query($query_txn);
			$list_1 = array();
			if ($mysqli->error) {
				$result["error_select_1"] = $mysqli->error;
				$result["http_code"] = 400;
				$result["status"] = "Ocurrió un error al consultar el cliente de la transacción.";
				echo json_encode($result);
				exit();
			}
			while ($li_1 = $list_query->fetch_assoc()) {
				$list_1[] = $li_1;
			}
			if(count($list_1)>0){
				if((int)$list_1[0]["cliente_id"]!==(int)$id_cliente){
					$result["http_code"] = 400;
					$result["status"] = "La apuesta fue registrada con un cliente diferente.";
					echo json_encode($result);exit();
				}
				//Validar estado del ticket
				$array_bet_calimaco = api_calimaco_get_bet($id_cliente, $id_bet);
				if(!(isset($array_bet_calimaco["result"]))) {
					$result["http_code"] = 400;
					$result["status"] = "Ticket no encontrado.";
					$result["result_api"] = $array_bet_calimaco;
					echo json_encode($result);exit();
				}
				if($array_bet_calimaco["result"]!=="OK") {
					$result["http_code"] = 400;
					$result["status"] = "Ticket no encontrado.";
					$result["result_api"] = $array_bet_calimaco;
					echo json_encode($result);exit();
				}

				// Validacion de estado
				if($array_bet_calimaco["bet"]["status"]==='WON' || 
					$array_bet_calimaco["bet"]["status"]==='REJECTED' || 
					$array_bet_calimaco["bet"]["status"]==='REJECTED_PAID' || 
					$array_bet_calimaco["bet"]["status"]==='PAID' || 
					$array_bet_calimaco["bet"]["status"]==='WON_PAID' || 
					$array_bet_calimaco["bet"]["status"]==='VOIDED' || 
					$array_bet_calimaco["bet"]["status"]==='VOIDED_PAID' || 
					($array_bet_calimaco["bet"]["status"]==='ALTER' && (int)$array_bet_calimaco["bet"]["winning"]>0) 
					) {

					if($array_bet_calimaco["bet"]["status"]!=='PAID' && 
						$array_bet_calimaco["bet"]["status"]!=='WON_PAID' && 
						$array_bet_calimaco["bet"]["status"]!=='VOIDED_PAID' && 
						$array_bet_calimaco["bet"]["status"]!=='REJECTED_PAID'){
						//Marcar como pagado
						$array_bet_calimaco_paid = api_calimaco_set_bet($id_cliente, $id_bet, $usuario_id);
						if(!(isset($array_bet_calimaco_paid["result"]))) {
							$result["http_code"] = 400;
							$result["status"] = "Ocurrió un error al marcar el ticket como pagado. *1";
							echo json_encode($result);
							exit();
						}
						if($array_bet_calimaco_paid["result"]!=="OK") {
							$result["http_code"] = 400;
							$result["status"] = "Ocurrió un error al marcar el ticket como pagado. *2";
							echo json_encode($result);
							exit();
						}
					}

					if($array_bet_calimaco["bet"]["status"]==='WON'){
						// Validacion 30 dias
						if(strlen($array_bet_calimaco["bet"]["resolved_date"])!==19){
							$result["http_code"] = 400;
							$result["status"] = "El Ticket aún no ha sido resuelto.";
							echo json_encode($result);exit();
						}
						$diff_dias_bet = (new DateTime($array_bet_calimaco["bet"]["resolved_date"]))->diff((new DateTime(date('Y-m-d'))));
						if((int)$diff_dias_bet->format('%R%a')>30){
							$result["http_code"] = 400;
							$result["status"] = "El Ticket supera el limite de 30 días.";
							echo json_encode($result);exit();
						}
					}

					if (in_array($array_bet_calimaco["bet"]["status"], array("VOIDED", "VOID", "VOIDED_PAID", "VOID_PAID"))) {
						$result["amount"] = $array_bet_calimaco["bet"]["wager"]/100;
						$tipo_id = 19;
					} else {
						if((double)($array_bet_calimaco["bet"]["winning"]/100)>0) {
							$result["amount"] = $array_bet_calimaco["bet"]["winning"]/100;
						} else {
							$result["amount"] = $array_bet_calimaco["bet"]["wager"]/100;
							$tipo_id = 19;
						}
					}

					if (in_array($array_bet_calimaco["bet"]["status"], array("REJECTED", "REJECTED_PAID", "CANCELLED"))) {
						$result["amount"] = $array_bet_calimaco["bet"]["wager"]/100;
						$tipo_id = 34;
					} else {
						if((double)($array_bet_calimaco["bet"]["winning"]/100)>0) {
							$result["amount"] = $array_bet_calimaco["bet"]["winning"]/100;
						} else {
							$result["amount"] = $array_bet_calimaco["bet"]["wager"]/100;
							$tipo_id = 34;
						}
					}

					if((double)$result["amount"]>0){
						$result["result_calimaco"] = $array_bet_calimaco;
					} else {
						$result["http_code"] = 400;
						$result["status"] = "El API respondio un monto no mayor a 0.";
						echo json_encode($result);exit();
					}
				} else {
					$res_calimaco_get_status = calimaco_get_status($array_bet_calimaco["bet"]["status"]);
					$array_bet_calimaco["bet"]["status"] = $res_calimaco_get_status["status"];

					$result["http_code"] = 400;
					$result["status"] = "El Ticket tiene el estado '".$array_bet_calimaco["bet"]["status"]."'.";
					echo json_encode($result);exit();
				}
			} else {
				$result["http_code"] = 400;
				$result["status"] = "La apuesta no fue registrada en nuestra BD.";
				echo json_encode($result);exit();
			}
		}


		// GOLDEN RACE ******************************************************************************
		if((int)$proveedor_id===3){
			$result["proveedor_name"] = 'Golden Race';
			// Validamos si la apuesta ha sido registrada antes y si el cliente es el correcto
			$query_txn = "
				SELECT
					tct.cliente_id
				FROM
					tbl_televentas_clientes_transaccion tct 
				WHERE
					tct.txn_id = '$id_bet' 
					AND tct.tipo_id = '4' 
					AND tct.api_id = 3 
					AND tct.estado = '1' 
				ORDER BY tct.id desc 
				LIMIT 1
			";
			$list_query = $mysqli->query($query_txn);
			$list_1 = array();
			if ($mysqli->error) {
				$result["error_select_1"] = $mysqli->error;
				$result["http_code"] = 400;
				$result["status"] = "Ocurrió un error al consultar el cliente de la transacción.";
				echo json_encode($result);
				exit();
			}
			while ($li_1 = $list_query->fetch_assoc()) {
				$list_1[] = $li_1;
			}
			if(count($list_1)>0){
				if((int)$list_1[0]["cliente_id"]!==(int)$id_cliente){
					$result["http_code"] = 400;
					$result["status"] = "La apuesta fue registrada con un cliente diferente.";
					echo json_encode($result);
					exit();
				}
				//Actualizar ticket
				$array_bet_gr = api_goldenrace_get_ticket($id_bet);
				if(!(isset($array_bet_gr["ticket_id"]))) {
					$result["http_code"] = 400;
					$result["status"] = "Ticket no encontrado";
					$result["result_api"] = $array_bet_gr;
					echo json_encode($result);exit();
				}
				// Validacion de estado
				if($array_bet_gr["status_text"]==='PAGADO' || $array_bet_gr["status_text"]==='CANCELADO') {
					if(strlen($array_bet_gr["paid_date"])>0) {
						// Validacion 30 dias
						$diff_dias_bet = (new DateTime($array_bet_gr["paid_date"]))->diff((new DateTime(date('Y-m-d'))));
						if((int)$diff_dias_bet->format('%R%a')>30){
							$result["http_code"] = 400;
							$result["status"] = "El Ticket supera el limite de 30 días.";
							echo json_encode($result);
							exit();
						}
						if($array_bet_gr["status_text"]==='PAGADO'){
							$result["amount"] = $array_bet_gr["winning_amount"];
						}
						if($array_bet_gr["status_text"]==='CANCELADO'){
							$result["amount"] = $array_bet_gr["stake_amount"];
						}
						$result["jackpot"] = $array_bet_gr["jackpot_amount"];
					} else {
						$result["http_code"] = 400;
						$result["status"] = "La apuesta no ha sido resuelta.";
						echo json_encode($result);
						exit();
					}
				} else {
					$result["http_code"] = 400;
					$result["status"] = "La apuesta tiene de estado ".$array_bet_gr["status_text"].".";
					echo json_encode($result);
					exit();
				}
			} else {
				$result["http_code"] = 400;
				$result["status"] = "La apuesta no ha sido registrada para algun cliente en nuestra BD.";
				echo json_encode($result);
				exit();
			}
		}

		// BINGO ******************************************************************************
		$array_bet_bingo = array();
		if((int)$proveedor_id===4){
			$result["proveedor_name"] = 'Bingo';
			$array_bet_bingo = api_bingo_get_bet($id_bet);
			if(isset($array_bet_bingo["ticket_id"])){
				// Validacion estado
				if(strlen($array_bet_bingo["paid_local_id"])===0) {
					$result["http_code"] = 400;
					$result["status"] = "La apuesta no ha sido marcado como pagado.";
					$result["result_api"] = $array_bet_bingo;
					echo json_encode($result);
					exit();
				}
				// Validacion 30 dias
				$diff_dias_bet = (new DateTime($array_bet_bingo["created"]))->diff((new DateTime(date('Y-m-d'))));
				if((int)$diff_dias_bet->format('%R%a')>30){
					$result["http_code"] = 400;
					$result["status"] = "El Ticket supera el limite de 30 días.";
					$result["result_api"] = $array_bet_bingo;
					echo json_encode($result);
					exit();
				}
				if((int)$array_bet_bingo["validate_tls_pagado"] === 1) {
					$result["amount"] = $array_bet_bingo["winning_amount"];
					$result["jackpot"] = $array_bet_bingo["jackpot_amount"];
				} else {
					$result["http_code"] = 400;
					$result["status"] = "El Ticket no pertenece a Televentas.";
					$result["result_api"] = $array_bet_bingo;
					echo json_encode($result);exit();
				}
			} else {
				$result["http_code"] = 400;
				$result["status"] = "Ticket no encontrado";
				$result["result_api"] = $array_bet_bingo;
				echo json_encode($result);exit();
			}
		}

		$insert_command = "
			INSERT INTO tbl_televentas_clientes_transaccion (
				tipo_id,
				api_id,
				txn_id,
				cliente_id,
				user_id,
				turno_id,
				monto,
				nuevo_balance,
				bono_monto,
				estado,
				id_tipo_balance,
				evento_dineroat_id,
				created_at
			) VALUES (
				" . $tipo_id . ",
				" . $proveedor_id . ",
				'" . $id_bet . "',
				" . $id_cliente . ",
				" . $usuario_id . ",
				" . $turno_id . ",
				" . $result["amount"] . ",
				0,
				0,
				0,
				$tipo_balance_id,
				$evento_dineroat_id,
				now()
			)";
		$mysqli->query($insert_command);

		$query_3 = "
			SELECT 
				ct.id, 
				IFNULL(ct2.id, 0) jackpot_id 
			FROM tbl_televentas_clientes_transaccion ct 
			LEFT JOIN tbl_televentas_clientes_transaccion ct2 ON ct2.transaccion_id = ct.id AND ct2.api_id = 20
			WHERE ct.tipo_id = '$tipo_id' 
			AND ct.txn_id = '$id_bet' 
			AND ct.user_id = '$usuario_id' 
			AND ct.turno_id = '$turno_id' 
			AND ct.estado = 0 
			";
		//$result["query_3"]=$query_3;
		$list_query = $mysqli->query($query_3);
		$list_transaccion = array();
		while ($li = $list_query->fetch_assoc()) {
			$list_transaccion[] = $li;
		}
		if (count($list_transaccion) == 0) {
			$result["http_code"] = 400;
			$result["status"] = "Se registro la apuesta, sin embargo no se pudo guardar la transacción.";
		} elseif (count($list_transaccion) === 1) {
			if((double)$result["jackpot"]>0 && (int)$list_transaccion[0]["jackpot_id"]===0){
				$insert_command = "
					INSERT INTO tbl_televentas_clientes_transaccion (
						tipo_id,
						api_id,
						txn_id,
						cliente_id,
						user_id,
						turno_id,
						monto,
						nuevo_balance,
						bono_monto,
						estado,
						evento_dineroat_id,
						created_at,
						transaccion_id
					) VALUES (
						20,
						" . $proveedor_id . ",
						'" . $id_bet . "',
						" . $id_cliente . ",
						" . $usuario_id . ",
						" . $turno_id . ",
						" . $result["jackpot"] . ",
						0,
						0,
						0,
						$evento_dineroat_id,
						now(),
						" . $list_transaccion[0]["id"] . "
					)";
				$mysqli->query($insert_command);
			}
			$result["http_code"] = 200;
			$result["status"] = "ok";
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Se registro la apuesta, sin embargo ocurrió un error al guardar la transacción.";
		}


	} elseif (count($list_1) > 0) {
		$estado = $list_1[0]["estado"];
		if ((int) $estado === 0) {
			if((int)$proveedor_id === 5) {
				if((int)$list_1[0]["cliente_id"]!==(int)$id_cliente){
					$result["http_code"] = 400;
					$result["status"] = "La apuesta le pertenece a un cliente diferente";
					$result["proveedor_name"] = $list_1[0]["proveedor_name"];
					echo json_encode($result);exit();
				}
			}
			$query_2 = " 
				UPDATE tbl_televentas_clientes_transaccion 
				SET
					cliente_id = '$id_cliente',
					id_tipo_balance = $tipo_balance_id,
					turno_id = '$turno_id',
					update_user_id = '$usuario_id',
					updated_at = now()
				WHERE txn_id = '$id_bet' AND tipo_id = '".$list_1[0]["tipo_id"]."' ";
			$mysqli->query($query_2);

			if(count($list_1)>1){
				$query_update_status = " 
					UPDATE tbl_televentas_clientes_transaccion 
					SET
						estado = '5',
						update_user_id = '$usuario_id',
						updated_at = now()
					WHERE 
						tipo_id = 5 
						AND estado = '0' 
						AND txn_id = '$id_bet' 
						AND api_id = '$proveedor_id' 
						AND id != '".$list_1[0]['id']."'
					";
				$mysqli->query($query_update_status);
			}
			$result["http_code"] = 200;
			$result["status"] = "ok";

			$result["amount"] = $list_1[0]["monto"];
			$result["jackpot"] = $list_1[0]["jackpot"];
			$result["proveedor_name"] = $list_1[0]["proveedor_name"];
			$result["result_"] = $list_1;
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Apuesta ya registrada";
			$result["amount"] = $list_1[0]["monto"];
			$result["jackpot"] = $list_1[0]["jackpot"];
			$result["proveedor_name"] = $list_1[0]["proveedor_name"];
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar la apuesta.";
	}
}


//*******************************************************************************************************************
// PAGAR APUESTA
//*******************************************************************************************************************
if ($_POST["accion"] === "pagar_apuesta") {

	$id_cliente = $_POST["id_cliente"];
	$cant_bet   = $_POST["cant_bet"];
	$array_bet  = $_POST["array_bet"];
	$total_bet  = $_POST["total_bet"];
	$evento_dineroat_id  = (int)$_POST["evento_dineroat_id"];

	$query_0= "
		SELECT 
			a.*
		FROM (
		SELECT
			ct.txn_id,
			MIN(ct.id) min_transaccion_id,
			COUNT( * ) cant 
		FROM
			tbl_televentas_clientes_transaccion ct 
		WHERE
			ct.tipo_id = 5 
			AND ct.estado = '0' 
			AND ct.txn_id IN ( " . $array_bet . " ) 
		GROUP BY ct.txn_id
		) a
		WHERE a.cant>1
	";
	$list_query = $mysqli->query($query_0);
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["error_select_0"] = $mysqli->error;
		$result["status"] = "Ocurrió un error al consultar los tickets.";
		echo json_encode($result);exit();
	}
	$list_0 = array();
	while ($li_0 = $list_query->fetch_assoc()) {
		$list_0[] = $li_0;
	}
	if (count($list_0) > 0) {
		foreach ($list_0 as $l) {
			$query_update_status = " 
				UPDATE tbl_televentas_clientes_transaccion 
				SET
					estado = '5',
					update_user_id = '$usuario_id',
					updated_at = now()
				WHERE 
					tipo_id = 5 
					AND estado = '0' 
					AND txn_id = '".$l['txn_id']."' 
					AND id != '".$l['min_transaccion_id']."'
				";
			$mysqli->query($query_update_status);
		}
	}
	/*
	$query_1 = "
		SELECT
			SUM( ct.monto ) monto_total,
			COUNT( * ) cantidad 
		FROM
			tbl_televentas_clientes_transaccion ct 
		WHERE
			ct.tipo_id IN (5,19) 
			AND ct.estado = '0' 
			AND ct.cliente_id = $id_cliente 
			AND ct.txn_id IN ( " . $array_bet . " ) 
		";
	*/
	$query_1 = "
		SELECT 
		  SUM(a.monto) monto_total, 
		  IFNULL(SUM(a.monto_tipo_5), 0) monto_tipo_5, 
		  IFNULL(SUM(a.monto_tipo_19), 0) monto_tipo_19, 
		  IFNULL(SUM(a.monto_tipo_34), 0) monto_tipo_34, 
		  IFNULL(SUM(a.monto_tipo_34_bal_4), 0) monto_tipo_34_bal_4, 
		  IFNULL(SUM(a.monto_tipo_34_bal_5), 0) monto_tipo_34_bal_5, 
		  IFNULL(SUM(a.monto_tipo_34_bal_6), 0) monto_tipo_34_bal_6, 
		  COUNT(a.id) cantidad 
		FROM 
		  (
		    SELECT 
		      ct.monto, 
		      IF(ct.tipo_id = 5, ct.monto, 0) monto_tipo_5, 
		      IF(ct.tipo_id = 19, ct.monto, 0) monto_tipo_19, 
		      IF(ct.tipo_id = 34, ct.monto, 0) monto_tipo_34, 
		      IF(ct.tipo_id = 34 AND cbt4.tipo_balance_id = 4, cbt4.monto, 0) monto_tipo_34_bal_4, 
		      IF(ct.tipo_id = 34 AND cbt5.tipo_balance_id = 5, cbt5.monto, 0) monto_tipo_34_bal_5, 
		      IF(ct.tipo_id = 34 AND cbt6.tipo_balance_id = 6, cbt6.monto, 0) monto_tipo_34_bal_6, 
		      ct.id 
		    FROM 
		      tbl_televentas_clientes_transaccion ct 
		      LEFT JOIN tbl_televentas_clientes_transaccion ct4 ON ct4.tipo_id = 4 
		      	and ct4.txn_id = ct.txn_id 
		      	and ct4.api_id = ct.api_id 
		      	and ct4.estado = 1
		      LEFT JOIN tbl_televentas_clientes_balance_transaccion cbt4 on cbt4.transaccion_id = ct4.id and cbt4.tipo_balance_id = 4 
		      LEFT JOIN tbl_televentas_clientes_balance_transaccion cbt5 on cbt5.transaccion_id = ct4.id and cbt5.tipo_balance_id = 5 
		      LEFT JOIN tbl_televentas_clientes_balance_transaccion cbt6 on cbt6.transaccion_id = ct4.id and cbt6.tipo_balance_id = 6 
		    WHERE 
		      ct.tipo_id IN (5, 19, 34) 
		      AND ct.estado = '0' 
		      AND ct.cliente_id = $id_cliente 
		      AND ct.txn_id IN ( " . $array_bet . " ) 
		  ) a
		";
	$list_query = $mysqli->query($query_1);
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["query_1"] = $query_1;
		$result["query_1_error"] = $mysqli->error;
		$result["status"] = "Ocurrió un error al consultar los tickets.";
		echo json_encode($result);exit();
	}
	$list_1 = array();
	while ($li_1 = $list_query->fetch_assoc()) {
		$list_1[] = $li_1;
	}
	if (count($list_1) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los tickets.";
		echo json_encode($result);exit();
	} elseif (count($list_1) > 0) {

		if ((double)$total_bet !== (double)$list_1[0]["monto_total"]) {
			$result["result"] = $list_1;
			$result["http_code"] = 400;
			$result["status"] = "Existen diferencias con el total enviado y el total en data.";
		}

		if ((double)$cant_bet !== (double)$list_1[0]["cantidad"]) {
			$result["result"] = $list_1;
			$result["http_code"] = 400;
			$result["status"] = "Existen diferencias con el total enviado y el total en data. (Cod. 2)";
		}

		if ((double)$list_1[0]["monto_total"] !== ((double)$list_1[0]["monto_tipo_5"]+(double)$list_1[0]["monto_tipo_19"]+(double)$list_1[0]["monto_tipo_34"])) {
			$result["result"] = $list_1;
			$result["http_code"] = 400;
			$result["status"] = "Existen diferencias con el total enviado y el total en data. (Cod. 3)";
		}

		if ((double)$list_1[0]["monto_tipo_34"] !== ((double)$list_1[0]["monto_tipo_34_bal_4"]+(double)$list_1[0]["monto_tipo_34_bal_5"])) {
			$result["result"] = $list_1;
			$result["http_code"] = 400;
			$result["status"] = "Existen diferencias con el total enviado y el total en data. (Cod. 4)";
		}

		if ($evento_dineroat_id > 0) {
			if ((double)$list_1[0]["monto_tipo_34"] !== ((double)$list_1[0]["monto_tipo_34_bal_6"])) {
				$result["result"] = $list_1;
				$result["http_code"] = 400;
				$result["status"] = "Existen diferencias con el total enviado y el total en data. (Cod. 5)";
			}
		}

		$list_balance = array();
		$list_balance = obtener_balances($id_cliente);

		if (count($list_balance) !== 1) {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al consultar el balance.";
			$result["result"] = $list_balance;
		}

		$balance_actual     = (double)$list_balance[0]["balance"];
		$balance_deposito   = (double)$list_balance[0]["balance_deposito"];
		$balance_retiro     = (double)$list_balance[0]["balance_retiro_disponible"];
		$balance_dineroat   = (double)$list_balance[0]["balance_dinero_at"];

		$balance_nuevo      = (double)$balance_actual + (double)$list_1[0]["monto_total"];
		$bal_deposito_nuevo = (double)$balance_deposito + (double)$list_1[0]["monto_tipo_34_bal_4"];
		$bal_retiro_nuevo   = (double)$balance_retiro + (double)$list_1[0]["monto_tipo_5"] + (double)$list_1[0]["monto_tipo_19"] + (double)$list_1[0]["monto_tipo_34_bal_5"];
		$bal_dineroat_nuevo = (double)$balance_dineroat + (double)$list_1[0]["monto_total"];
		
		$monto              = (double)$total_bet;

		if ($evento_dineroat_id > 0) {
			//*********************** UPDATE BALANCE DINERO AT
			query_tbl_televentas_clientes_balance('update', $id_cliente, 6, $bal_dineroat_nuevo);
		} else {
			//*********************** UPDATE BALANCE PRINCIPAL
			query_tbl_televentas_clientes_balance('update', $id_cliente, 1, $balance_nuevo);

			//*********************** UPDATE BALANCE RETIRO DISPONIBLE
			query_tbl_televentas_clientes_balance('update', $id_cliente, 4, $bal_deposito_nuevo);

			//*********************** UPDATE BALANCE RETIRO DISPONIBLE
			query_tbl_televentas_clientes_balance('update', $id_cliente, 5, $bal_retiro_nuevo);
		}

		//*********************** TRANSACCIONES USADAS
		$query_update_transacciones = " 
			UPDATE tbl_televentas_clientes_transaccion 
			SET
				estado = '1',
				cliente_id = '" . $id_cliente . "',
				turno_id = '" . $turno_id . "',
				cc_id = '" . $cc_id . "',
				update_user_id = '" . $usuario_id . "',
				updated_at = now(),
				created_at = now(),
				user_id = '" . $usuario_id . "'
			WHERE tipo_id IN (5,19,20,34) AND txn_id IN ( " . $array_bet . " ) and estado=0 
			";
		$mysqli->query($query_update_transacciones);

		$query_3 = "
			SELECT
				ct.id cod_transaccion,
				ct.tipo_id,
				ct.txn_id,
				ct.monto,
		    	IF(ct.tipo_id in (19,34) AND cbt4.tipo_balance_id = 4, cbt4.monto, 0) monto_tipo_19_bal_4,
		    	IF(ct.tipo_id in (19,34) AND cbt5.tipo_balance_id = 5, cbt5.monto, 0) monto_tipo_19_bal_5,
		    	IF(ct.tipo_id in (19,34) AND cbt6.tipo_balance_id = 6, cbt6.monto, 0) monto_tipo_19_bal_6
			FROM
				tbl_televentas_clientes_transaccion ct 
		      LEFT JOIN tbl_televentas_clientes_transaccion ct4 ON ct4.tipo_id = 4 and ct4.txn_id = ct.txn_id and ct4.api_id = ct.api_id 
		      LEFT JOIN tbl_televentas_clientes_balance_transaccion cbt4 on cbt4.transaccion_id = ct4.id and cbt4.tipo_balance_id = 4 
		      LEFT JOIN tbl_televentas_clientes_balance_transaccion cbt5 on cbt5.transaccion_id = ct4.id and cbt5.tipo_balance_id = 5 
		      LEFT JOIN tbl_televentas_clientes_balance_transaccion cbt6 on cbt6.transaccion_id = ct4.id and cbt6.tipo_balance_id = 6
			WHERE
				ct.tipo_id IN (5,19,20,34) 
				AND ct.estado = '1' 
				AND ct.cliente_id = '" . $id_cliente . "' 
				AND ct.update_user_id = '" . $usuario_id . "' 
				AND ct.txn_id IN ( " . $array_bet . " ) 
			ORDER BY ct.created_at ASC, ct.id ASC
			";
		$list_query = $mysqli->query($query_3);
		$list_transaccion = array();
		while ($li = $list_query->fetch_assoc()) {
			$list_transaccion[] = $li;
		}
		if (count($list_transaccion) == 0) {
			$result["http_code"] = 400;
			$result["status"] = "Se registro la apuesta, sin embargo no se pudo guardar la transacción.";
		} elseif (count($list_transaccion) > 0) {

			$temp_balance_actual          = $balance_actual;
			$temp_balance_deposito_actual = $balance_deposito;
			$temp_balance_retiro_actual   = $balance_retiro;
			$temp_balance_dineroat_actual = $balance_dineroat;

			foreach ($list_transaccion as $bet) {

				if((int)$bet["tipo_id"]===20){ // Jackpot
					$list_balance_j = array();
					$list_balance_j = obtener_balances($id_cliente);
					if (count($list_balance_j) === 1) {
						if ($evento_dineroat_id > 0) {
							//*********************** UPDATE BALANCE DINERO AT
							query_tbl_televentas_clientes_balance('update', $id_cliente, 6, ($list_balance_j[0]["balance_dinero_at"]+$bet['monto']));
						} else {
							//*********************** UPDATE BALANCE PRINCIPAL
							query_tbl_televentas_clientes_balance('update', $id_cliente, 1, ($list_balance_j[0]["balance"]+$bet['monto']));

							//*********************** UPDATE BALANCE RETIRO DISPONIBLE
							query_tbl_televentas_clientes_balance('update', $id_cliente, 5, ($list_balance_j[0]["balance_retiro_disponible"]+$bet['monto']));
						}
					}
				}

				$temp_balance_nuevo = (double)$temp_balance_actual + (double)$bet['monto'];
				$temp_balance_dineroat_nuevo = (double)$temp_balance_dineroat_actual + (double)$bet['monto'];

				if ($evento_dineroat_id > 0) {
					query_tbl_televentas_clientes_balance_transaccion('insert', $bet['cod_transaccion'], 
					$id_cliente, 6, $temp_balance_dineroat_actual, $bet['monto'], $temp_balance_dineroat_nuevo);

					$query_update_trans = " 
						UPDATE tbl_televentas_clientes_transaccion 
						SET nuevo_balance = '" . $temp_balance_dineroat_nuevo . "',
							updated_at = now(),
							created_at = now()
						WHERE id = " . $bet['cod_transaccion'] . " 
						";
					$mysqli->query($query_update_trans);
					if ($mysqli->error) {
						$result["error_update_dineroat_".$bet['cod_transaccion']] = $mysqli->error;
					}

					$temp_balance_dineroat_actual = $temp_balance_dineroat_nuevo;

				} else {
					query_tbl_televentas_clientes_balance_transaccion('insert', $bet['cod_transaccion'], 
					$id_cliente, 1, $temp_balance_actual, $bet['monto'], $temp_balance_nuevo);

					if((int)$bet["tipo_id"] === 19 || (int)$bet["tipo_id"] === 34){ // Apuestas Retornadas y Canceladas // tel-159 tel-177

						if((int)$bet["tipo_id"] === 19){
							
							$temp_balance_deposito_nuevo = (double)$temp_balance_deposito_actual;
							$temp_balance_retiro_nuevo   = (double)$temp_balance_retiro_actual + (double)$bet['monto'];

							query_tbl_televentas_clientes_balance_transaccion('insert', $bet['cod_transaccion'], 
								$id_cliente, 5, $temp_balance_retiro_actual, $bet['monto'], $temp_balance_retiro_nuevo);

						} else {

							$temp_balance_deposito_nuevo = (double)$temp_balance_deposito_actual + (double)$bet['monto_tipo_19_bal_4'];
							$temp_balance_retiro_nuevo   = (double)$temp_balance_retiro_actual + (double)$bet['monto_tipo_19_bal_5'];

							query_tbl_televentas_clientes_balance_transaccion('insert', $bet['cod_transaccion'], 
								$id_cliente, 4, $temp_balance_retiro_actual, $bet['monto_tipo_19_bal_4'], $temp_balance_deposito_nuevo);
							
							query_tbl_televentas_clientes_balance_transaccion('insert', $bet['cod_transaccion'], 
								$id_cliente, 5, $temp_balance_retiro_actual, $bet['monto_tipo_19_bal_5'], $temp_balance_retiro_nuevo);
						}

					} else {
						$temp_balance_deposito_nuevo = (double)$temp_balance_deposito_actual;
						$temp_balance_retiro_nuevo   = (double)$temp_balance_retiro_actual + (double)$bet['monto'];

						query_tbl_televentas_clientes_balance_transaccion('insert', $bet['cod_transaccion'], 
							$id_cliente, 5, $temp_balance_retiro_actual, $bet['monto'], $temp_balance_retiro_nuevo);
					}

					$query_update_trans = " 
						UPDATE tbl_televentas_clientes_transaccion 
						SET nuevo_balance = '" . $temp_balance_nuevo . "',
							updated_at = now(),
							created_at = now()
						WHERE id = " . $bet['cod_transaccion'] . " 
						";
					$mysqli->query($query_update_trans);
					if ($mysqli->error) {
						$result["error_update_".$bet['cod_transaccion']] = $mysqli->error;
					}
					$temp_balance_actual          = $temp_balance_nuevo;
					$temp_balance_deposito_actual = $temp_balance_deposito_nuevo;
					$temp_balance_retiro_actual   = $temp_balance_retiro_nuevo;
				}

			}
			sec_tlv_asignar_etiqueta_test($id_cliente);

			$result["http_code"] = 200;
			$result["status"] = "Ok";
			$result["list_transaccion"] = $list_transaccion;
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Se registro la apuesta, sin embargo ocurrió un error al guardar la transacción.";
		}

	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar la apuesta.";
	}
}



//*******************************************************************************************************************
// PAGAR TICKET KURAX
//*******************************************************************************************************************
if ($_POST["accion"] === "pagar_ticket_kurax") {

	$id_cliente = $_POST["id_cliente"];
	//$ticket_id  = $_POST["ticket_id"];
	$autenticacao  = $_POST["autenticacao"];
	$is_anonimo  = $_POST["is_anonimo"];

	$ticket_id = "";
	$cod_pago = "";
	//Conversión
	$datos = explode('.', $autenticacao);
	if (count($datos) === 5) {
		$ticket_id = $datos[1];
		$cod_pago = $datos[3];
	} elseif (count($datos) === 4) {
		$ticket_id = $datos[1];
		$cod_pago = $datos[2];
	} else {
		$result["http_code"] = 400;
		$result["status"] = "El formato del código no es válido.";
		echo json_encode($result); exit();
	}

	// Verificar si codigo apuesta esta registrado
	$query_vrf = "
			SELECT
				id
			FROM
				tbl_televentas_clientes_transaccion 
			WHERE 
				txn_id = '".$ticket_id."'  
				AND api_id = 10
				AND tipo_id = 5
				AND estado = 1
			ORDER BY id ASC
			";
		$list_verf = $mysqli->query($query_vrf);
		$list_encontrado = array();
		while ($li_vrf = $list_verf->fetch_assoc()) {
			$list_encontrado[] = $li_vrf;
		}
		if (count($list_encontrado) == 0) {
			$tipo_doc = '';
			$num_doc = '';
			if($is_anonimo == 0){
				$query_cli = "
					SELECT
						IF (tipo_doc = '0', 'dni' , IF (tipo_doc = '1', 'carneta' , 'passport')) as tipo_doc_nomb,
						IFNULL(num_doc, '') num_doc 
					FROM 
						wwwapuestatotal_gestion.tbl_televentas_clientes 
					WHERE 
						id = '".$id_cliente."'  
					ORDER BY id ASC
					";
				$list_cli = $mysqli->query($query_cli);
				$list_cli_encontrado = array();
				while ($li_cli = $list_cli->fetch_assoc()) {
					$list_cli_encontrado[] = $li_cli;
				}
				$tipo_doc = $list_cli_encontrado[0]['tipo_doc_nomb'];
				$num_doc = $list_cli_encontrado[0]['num_doc'];
			}else{
				$tipo_doc = 'dni';
				$num_doc = '1';
			}
			
			$array_ticket_kurax = array();
			$array_ticket_kurax = api_set_paid_ticket_kurax($tipo_doc, $num_doc, $ticket_id, $cod_pago);

			if($array_ticket_kurax["http_code"] == 200){

				$ticket = query_tbl_televentas_tickets_get_array();

				$ticket['ticket_id'] = $ticket_id;
				$ticket['proveedor_id'] = '10';
				$ticket['created'] = $array_ticket_kurax["ticket"]["result"]["momento"];
				$ticket['calc_date'] = '';
				$ticket['paid_date'] = $array_ticket_kurax["ticket"]["result"]["momentoPgto"];
				$ticket['external_id'] = '';
				$ticket['game'] = '';
				$ticket['sell_local_id'] = $array_ticket_kurax["ticket"]["result"]["localVendaId"];
				$ticket['paid_local_id'] = 0;
				$ticket['num_selections'] = $array_ticket_kurax["ticket"]["result"]["qtdEventos"];
				$ticket['price'] = $array_ticket_kurax["ticket"]["result"]["valorPago"]/100;
				$ticket['stake_amount'] = $array_ticket_kurax["ticket"]["result"]["totalPago"];
				$ticket['winning_amount'] = $array_ticket_kurax["ticket"]["result"]["totalPago"];
				$ticket['jackpot_amount'] = 0;
				$ticket['status'] = $array_ticket_kurax["ticket"]["result"]["idStatus"];
				$ticket['status_text'] = $array_ticket_kurax["ticket"]["result"]["nomeStatus"];

				$ticket_query = query_tbl_televentas_tickets($ticket);

				$list_balances = array();
				$list_balances = obtener_balances($id_cliente);

				if (count($list_balances) === 1) {

					$montopagokurax=$array_ticket_kurax["ticket"]["result"]["totalPago"];

					$insert_command = "
					INSERT INTO tbl_televentas_clientes_transaccion (
						tipo_id,
						api_id,
						txn_id,
						cliente_id,
						user_id,
						turno_id,
						monto,
						nuevo_balance,
						bono_monto,
						estado,
						id_tipo_balance,
						created_at
					) VALUES (
						'5',
						'10',
						'" . $ticket_id . "',
						" . $id_cliente . ",
						" . $usuario_id . ",
						" . $turno_id . ",
						" . $montopagokurax . ",
						" . ($list_balances[0]["balance"]+$montopagokurax). ",
						0,
						1,
						1,
						now()
					)";
					$mysqli->query($insert_command);

					$query_tr = "
						SELECT 
							id
						FROM 
							tbl_televentas_clientes_transaccion
						WHERE 
							tipo_id = '5' 
						AND txn_id = '" . $ticket_id . "' 
							AND api_id = 10
						LIMIT 1
					";
 
					$list_query = $mysqli->query($query_tr);
					$list_transaccion = array();
					while ($li = $list_query->fetch_assoc()) {
						$list_transaccion[] = $li;
					}
					if (count($list_transaccion) == 0) {
						$result["http_code"] = 400;
						$result["status"] = "Se registro el pago de la apuesta, sin embargo no se pudo guardar la transacción.";
					}else{

						//*********************** UPDATE BALANCE PRINCIPAL
						query_tbl_televentas_clientes_balance('update', $id_cliente, 1, ($list_balances[0]["balance"]+$montopagokurax));

						//*********************** UPDATE BALANCE RETIRO DISPONIBLE
						query_tbl_televentas_clientes_balance('update', $id_cliente, 5, ($list_balances[0]["balance_retiro_disponible"]+$montopagokurax));

						query_tbl_televentas_clientes_balance_transaccion('insert', $list_transaccion[0]['id'], 
						$id_cliente, 1, $list_balances[0]["balance"], $montopagokurax, ($list_balances[0]["balance"]+$montopagokurax));

						query_tbl_televentas_clientes_balance_transaccion('insert', $list_transaccion[0]['id'], 
						$id_cliente, 5, $list_balances[0]["balance_retiro_disponible"], $montopagokurax, ($list_balances[0]["balance_retiro_disponible"]+$montopagokurax));

						$result["ticket"] = $array_ticket_kurax;
						$result["http_code"] = 200;
						$result["status"] = "Pago realizado.";
					}

				}else{
					$result["http_code"] = 400;
					$result["status"] = "Se registro el pago, sin embargo ocurrio un error al consultar el balance.";
				}

			}else{
				$result["http_code"] = 400;
				$result["status"] = $array_ticket_kurax["status"];
				
			}

		}else{
			$result["http_code"] = 400;
			$result["status"] = "El código de apuesta ya se encuentra registrado.";
		}
	
}


//*******************************************************************************************************************
//*******************************************************************************************************************
// API KURAX
//*******************************************************************************************************************
//*******************************************************************************************************************
 
function api_set_paid_ticket_kurax($tipo_doc, $num_doc, $ticket_id, $autenticacao){

	$url = "https://api.apuestatotal.com/v2/kurax/setPaidTicket";
	$rq = ['tipo_doc' => $tipo_doc, 'num_doc' => $num_doc, 'ticket_id' => $ticket_id, 'autenticacao' => $autenticacao];
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

	$result = array();
	if ($err) {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consumir el API.";
		$result["result"] = $response;
		$result["error"] = "cURL Error #:" . $err;
	} else {
		$result = $response_arr;
	}
	return $result;
}






//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************
// BINGO
//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************


//*******************************************************************************************************************
// REGISTRAR BINGO VENTA DETALLE
//*******************************************************************************************************************
if ($_POST["accion"] === "sec_tlv_registrar_bingo_venta_detalle") {

	$id_cliente        = $_POST["id_cliente"];
	$balance_tipo      = $_POST["balance_tipo"];
	$id_bet            = $_POST["id_ticket"];
	$result["id_bet"]  = $id_bet;
	$result["amount"]  = 0;
	$result["soporte"] = 0;

	$query_1 = "
		SELECT 
			ct.id, 
			ROUND(bt.amount,1) AS monto, 
			ct.txn_id as ticket_id, 
			ct.estado as status,
			CASE 
				WHEN bt.status = 'Expired' THEN 'EXPIRADO'
				WHEN bt.status = 'Lost' THEN 'PERDIDO'
				WHEN bt.status = 'Paid' THEN 'PAGADO'
				WHEN bt.status = 'Pending' THEN 'PENDIENTE'
				WHEN bt.status = 'Refunded' THEN 'REINTEGRADO'
				WHEN bt.status = 'Won' THEN 'GANADO' 
			END AS estado
		FROM tbl_televentas_clientes_transaccion ct
		INNER JOIN tbl_repositorio_bingo_tickets bt ON ct.txn_id = bt.ticket_id
		WHERE ct.txn_id = '" . $id_bet . "' 
			AND ct.tipo_id = 4 
			AND ct.estado in (0,1) 
		LIMIT 1
	";
	$list_query = $mysqli->query($query_1);
	$list_1 = array();
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar el ticket.";
		$result["select_error"] = $mysqli->error;
		$result["select_query"] = $query_1;
		echo json_encode($result);exit();
	}
	while ($li_1 = $list_query->fetch_assoc()) {
		$list_1[] = $li_1;
	}
	if (count($list_1) == 0) {
		$switch_continue = 0;
		$monto = 0;

		$query_1 = "
			SELECT t.id, t.ticket_id, 
			ROUND(t.amount,1) as monto,
			CASE 
				WHEN t.status = 'Expired' THEN 'EXPIRADO'
				WHEN t.status = 'Lost' THEN 'PERDIDO'
				WHEN t.status = 'Paid' THEN 'PAGADO'
				WHEN t.status = 'Pending' THEN 'PENDIENTE'
				WHEN t.status = 'Refunded' THEN 'REINTEGRADO'
				WHEN t.status = 'Won' THEN 'GANADO' 
			END AS estado,
			IFNULL(l.nombre,'') AS local,
			IFNULL(t.sell_local_id,0) sell_local_id
			FROM tbl_repositorio_bingo_tickets t
			LEFT JOIN tbl_locales l ON t.sell_local_id = l.cc_id
			WHERE 
				ticket_id = '" . $id_bet . "'
			LIMIT 1;
			";
		$list_query = $mysqli->query($query_1);
		$list_detalle_ticket = array();
		if ($mysqli->error) {
			$result["select_error"] = $mysqli->error;
			$result["select_query"] = $query_1;
			//echo json_encode($result);exit();
		}
		while ($li_ticket = $list_query->fetch_assoc()) {
			$list_detalle_ticket[] = $li_ticket;
		}
		if (count($list_detalle_ticket) > 0){
			$local_name_temp = mb_strtoupper($list_detalle_ticket[0]["local"], 'UTF-8');
			$findme = 'TELEVENTAS';
			$pos = strpos($local_name_temp, $findme);
			if ($pos === false) {
				if($local_name_temp == ""){
					$result["http_code"] = 400;
					$result["status"] = "El local con Id Externo: " . $list_detalle_ticket[0]["sell_local_id"] . " asignado a este ticket no existe.";
					$result["soporte"] = 1;
				}else{
					$result["http_code"] = 400;
					$result["status"] = "El ticket pertenece al local: " . $local_name_temp;
				}
				echo json_encode($result);exit();
			} else {
				$local_name = $local_name_temp;
				$switch_continue = 1;
				$monto = $list_detalle_ticket[0]["monto"];
				if($monto >= 0.95 && $monto <= 0.99){
					$monto = 1;
				}
			}
		}

		if($switch_continue === 1){
			$insert_command = "
				INSERT INTO tbl_televentas_clientes_transaccion (
					tipo_id,
					txn_id,
					cliente_id,
					api_id,
					user_id,
					turno_id,
					monto,
					nuevo_balance,
					bono_monto,
					id_tipo_balance,
					estado,
					created_at
				) VALUES (
					4,
					'" . $id_bet . "',
					0,
					4,
					" . $usuario_id . ",
					" . $turno_id . ",
					" . $monto . ",
					0,
					0,
					" . $balance_tipo . ",
					0,
					now()
				)";
			$mysqli->query($insert_command);
			if ($mysqli->error) {
				//$result["insert_query"] = $insert_command;
				$result["insert_error"] = $mysqli->error;
			}
			$query_3 = "
				SELECT 
					tra.txn_id as ticket_id, 
					tra.monto, 
					tra.estado as status, 
					CASE 
						WHEN bt.status = 'Expired' THEN 'EXPIRADO'
						WHEN bt.status = 'Lost' THEN 'PERDIDO'
						WHEN bt.status = 'Paid' THEN 'PAGADO'
						WHEN bt.status = 'Pending' THEN 'PENDIENTE'
						WHEN bt.status = 'Refunded' THEN 'REINTEGRADO'
						WHEN bt.status = 'Won' THEN 'GANADO' 
					END AS estado 
				FROM tbl_televentas_clientes_transaccion tra 
				INNER JOIN tbl_repositorio_bingo_tickets bt ON tra.txn_id = bt.ticket_id 
				WHERE tra.tipo_id = 4 
				AND tra.txn_id='" . $id_bet . "' 
				AND tra.user_id='" . $usuario_id . "' 
				AND tra.api_id = 4 
				AND tra.turno_id='" . $turno_id . "' 
				AND tra.estado = '0' 
				LIMIT 1
				";
			$list_query = $mysqli->query($query_3);
			$list_transaccion = array();
			while ($li = $list_query->fetch_assoc()) {
				$list_transaccion[] = $li;
			}
			if (count($list_transaccion) == 0) {
				$result["http_code"] = 400;
				$result["status"] = "No se pudo registrar la transaccion";
				//$result["list_transaccion"] = $list_transaccion;
				//$result["query"] = $query_3;
			} elseif (count($list_transaccion) === 1) {
				$result["http_code"] = 200;
				$result["status"] = "ok";
				$result["amount"] = $monto;
				$result["result"] = $list_transaccion;
			} else {
				$result["http_code"] = 400;
				$result["status"] = "Error al registrar la transacción";
				//$result["list_transaccion"] = $list_transaccion;
				//$result["query"] = $query_3;
			}
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ticket no encontrado.";
			//$result["result_bc"] = $array_bet_bc;
			//$result["result_calimaco"] = $array_bet_calimaco;
			echo json_encode($result);exit();
		}
	}elseif (count($list_1) > 0) {
		$estado = $list_1[0]["status"];
		if ((int) $estado === 0) {
			$monto = $list_1[0]["monto"];
			if($monto >= 0.95 && $monto <= 0.99){
				$monto = 1;
			}
			$query_2 = " 
				UPDATE tbl_televentas_clientes_transaccion 
				SET
					turno_id = " . $turno_id . ",
					id_tipo_balance = " . $balance_tipo . ",
					update_user_id = " . $usuario_id . ",
					monto = " . $monto . ",
					created_at = now(),
					updated_at = now()
				WHERE txn_id = '" . $id_bet . "' AND tipo_id = 4 AND api_id = 4";
			$mysqli->query($query_2);

			$result["http_code"] = 200;
			$result["status"] = "ok";
			$result["amount"] = $monto;
			$result["result"] = $list_1;
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Apuesta ya registrada";
			$result["amount"] = $monto;
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar el ticket.";
	}

}

//*******************************************************************************************************************
// REGISTRAR BINGO VENTA
//*******************************************************************************************************************
if ($_POST["accion"] === "sec_tlv_registrar_bingo_venta") {

	$id_cliente         = $_POST["id_cliente"];
	$balance_tipo       = $_POST["balance_tipo"];
	$cant_bet           = $_POST["cant_bet"];
	$array_bet          = $_POST["array_ticket"];
	$array_monto        = $_POST["array_monto"];
	$total_bet          = $_POST["total_bet"];

	$query_1 = "
		SELECT
			SUM( round(t.amount,1) ) monto_total,
			COUNT( * ) cantidad 
		FROM
			tbl_repositorio_bingo_tickets t 
		WHERE t.ticket_id IN ( " . $array_bet . " ) 
		";
	//$result["query_1"] = $query_1;
	$list_query = $mysqli->query($query_1);
	$list_1 = array();
	if ($mysqli->error) {
		$result["error_select_1"] = $mysqli->error;
	}
	while ($li_1 = $list_query->fetch_assoc()) {
		$list_1[] = $li_1;
	}
	if (count($list_1) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los tickets.";
		echo json_encode($result);exit();
	} elseif (count($list_1) > 0) {

		$list_1_monto_total = $list_1[0]["monto_total"];
		$list_1_cantidad = $list_1[0]["cantidad"];

		if ((double) $total_bet === (double) $list_1_monto_total) {
			if ((double) $cant_bet === (double) $list_1_cantidad) {

				$list_balance = array();
				$list_balance = obtener_balances($id_cliente);
				
				if (count($list_balance) === 1) {

					$monto = (double)$list_1_monto_total;

					$balance_actual = (double)$list_balance[0]["balance"];
					$balance_deposito = (double)$list_balance[0]["balance_deposito"];
					$balance_retiro = (double)$list_balance[0]["balance_retiro_disponible"];

					$balance_nuevo = (double)$balance_actual - (double)$monto;
					$balance_deposito_nuevo = (double)$list_balance[0]["balance_deposito"];
					$balance_retiro_nuevo = (double)$list_balance[0]["balance_retiro_disponible"];

					if ($balance_actual > 0) {
						if ($balance_actual >= $list_1_monto_total) {

							//*********************** BALANCE DEPOSITO
							if((double)$balance_deposito>0){
								if((double)$balance_deposito_nuevo > (double)$monto){
									$balance_deposito_nuevo = (double)$balance_deposito_nuevo - (double)$monto;
									$monto = 0;
								} else {
									$monto = (double)$monto - (double)$balance_deposito_nuevo;
									$balance_deposito_nuevo = 0;
								}
								query_tbl_televentas_clientes_balance('update', $id_cliente, 4, $balance_deposito_nuevo);
							}

							//*********************** BALANCE RETIRO
							if((double)$monto>0){
								if((double)$balance_retiro>0){
									if((double)$balance_retiro_nuevo > (double)$monto){
										$balance_retiro_nuevo = $balance_retiro_nuevo-$monto;
										$monto = 0;
									} else {
										$monto = (double)$monto - (double)$balance_retiro_nuevo;
										$balance_retiro_nuevo = 0;
									}
									query_tbl_televentas_clientes_balance('update', $id_cliente, 5, $balance_retiro_nuevo);
								}
							}

							query_tbl_televentas_clientes_balance('update', $id_cliente, 1, $balance_nuevo);

							/*************** UPDATE TRANSACCIONES *************/
							$query = " 
								UPDATE tbl_televentas_clientes_transaccion 
								SET
									estado = '1',
									cliente_id = '" . $id_cliente . "',
									turno_id = '" . $turno_id . "',
									cc_id = '" . $cc_id . "',
									update_user_id = '" . $usuario_id . "',
									updated_at = now()
								WHERE tipo_id = 4 AND api_id = 4 AND txn_id IN ( " . $array_bet . " ) and estado = 0 
								";
							$mysqli->query($query);
							if ($mysqli->error) {
								$result["error_update_transacciones"] = $mysqli->error;
							}

							$query = "
								SELECT
									ct.id cod_transaccion,
									ct.txn_id,
									ct.monto
								FROM
									tbl_televentas_clientes_transaccion ct 
								WHERE
									ct.tipo_id = 4
									AND ct.api_id = 4
									AND ct.estado = '1' 
									AND ct.cliente_id = '" . $id_cliente . "' 
									AND ct.update_user_id = '" . $usuario_id . "' 
									AND ct.txn_id IN ( " . $array_bet . " )  
								ORDER BY ct.id ASC
							";
							$list_query = $mysqli->query($query);
							if ($mysqli->error) {
								$result["error_select_3"] = $mysqli->error;
							}
							$list_transaccion = array();
							while ($li = $list_query->fetch_assoc()) {
								$list_transaccion[] = $li;
							}
							if (count($list_transaccion) == 0) {
								$result["http_code"] = 400;
								$result["status"] = "No se pudo guardar la transacción.";
							} elseif (count($list_transaccion) > 0) {

								$temp_balance_actual = (double)$balance_actual;
								$temp_balance_deposito_actual = (double)$balance_deposito;
								$temp_balance_retiro_actual = (double)$balance_retiro;
								//$cont=0;

								foreach ($list_transaccion as $bet) {
									//$cont++;
									//$result[$cont] = $bet['cod_transaccion'];
									$temp_balance_nuevo = (double)$temp_balance_actual - (double)$bet['monto'];
									$temp_balance_deposito_nuevo = (double)$temp_balance_deposito_actual;
									$temp_balance_retiro_nuevo = (double)$temp_balance_retiro_actual;
									//$temp_balance_retiro_nuevo = $temp_balance_retiro_actual + $bet['monto'];

									$query = " 
										UPDATE tbl_televentas_clientes_transaccion 
										SET nuevo_balance = '" . $temp_balance_nuevo . "' 
										WHERE id = " . $bet['cod_transaccion'] . " 
										";
									$mysqli->query($query);
									if ($mysqli->error) {
										$result["error_update_".$bet['cod_transaccion']] = $mysqli->error;
									}

									query_tbl_televentas_clientes_balance_transaccion('insert', $bet['cod_transaccion'], 
										$id_cliente, 1, $temp_balance_actual, $bet['monto'], $temp_balance_nuevo);

									//*********************** BALANCE DEPOSITO
									if((double)$temp_balance_deposito_actual>0){
										$temp_monto_balance_tipo4 = 0;
										if((double)$temp_balance_deposito_actual>(double)$bet['monto']){
											$temp_balance_deposito_nuevo = (double)$temp_balance_deposito_nuevo - (double)$bet['monto'];
											$temp_monto_balance_tipo4 = (double)$bet['monto'];
											$bet['monto'] = 0;
										} else {
											$bet['monto'] = (double)$bet['monto'] - (double)$temp_balance_deposito_actual;
											$temp_monto_balance_tipo4 = (double)$temp_balance_deposito_actual;
											$temp_balance_deposito_nuevo = 0;
										}
										query_tbl_televentas_clientes_balance_transaccion('insert', $bet['cod_transaccion'], 
										$id_cliente, 4, $temp_balance_deposito_actual, $temp_monto_balance_tipo4, $temp_balance_deposito_nuevo);
									}

									//*********************** BALANCE RETIRO
									if((double)$bet['monto']>0){
										if((double)$temp_balance_retiro_actual>0){
											$temp_monto_balance_tipo5 = 0;
											if((double)$temp_balance_retiro_actual > (double)$bet['monto']){
												$temp_balance_retiro_nuevo = (double)$temp_balance_retiro_actual - (double)$bet['monto'];
												$temp_monto_balance_tipo5 = (double)$bet['monto'];
												$bet['monto'] = 0;
											} else {
												$bet['monto'] = (double)$bet['monto'] - (double)$temp_balance_retiro_actual;
												$temp_monto_balance_tipo5 = (double)$temp_balance_retiro_actual;
												$temp_balance_retiro_nuevo = 0;
											}
											query_tbl_televentas_clientes_balance_transaccion('insert', $bet['cod_transaccion'], 
											$id_cliente, 5, $temp_balance_retiro_actual, $temp_monto_balance_tipo5, $temp_balance_retiro_nuevo);
										}
									}

									$temp_balance_actual = $temp_balance_nuevo;
									$temp_balance_deposito_actual = $temp_balance_deposito_nuevo;
									$temp_balance_retiro_actual = $temp_balance_retiro_nuevo;
								}

								//$result["query_command"] = $insert_command;
								$result["http_code"] = 200;
								$result["status"] = "Ok";
								$result["list_transaccion"] = $list_transaccion;
							} else {
								$result["http_code"] = 400;
								$result["status"] = "Error al guardar la transacción.";
							}

							//*********************** VALIDACION BALANCE
						} else {
							$result["http_code"] = 400;
							$result["status"] = "El balance es menor al total.";
							$result["result"] = $list_balance;
						}
					} else {
						$result["http_code"] = 400;
						$result["status"] = "El balance esta en 0.";
						$result["result"] = $list_balance;
					}
				} else {
					$result["http_code"] = 400;
					$result["status"] = "Ocurrió un error al consultar el balance.";
					$result["result"] = $list_balance;
				}
			} else {
				$result["http_code"] = 400;
				$result["status"] = "Existen diferencias con el total de monto enviado y en data.";
			}
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Existen diferencias con el total de cantidad enviada y en data.";
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar la apuesta.";
	}

}



//*******************************************************************************************************************
// REGISTRAR BINGO PAGO DETALLE
//*******************************************************************************************************************
if ($_POST["accion"] === "sec_tlv_registrar_bingo_pago_detalle") {

	$id_cliente         = $_POST["id_cliente"];
	$id_bet             = $_POST["id_ticket"];
	$result["id_bet"]   = $id_bet;
	$result["amount"]   = 0;

	$query_1 = "
		SELECT 
			ct.id, 
			ct.estado as status, 
			bt.winning AS monto, 
			ct.txn_id as ticket_id,
			CASE 
				WHEN bt.status = 'Expired' THEN 'EXPIRADO'
				WHEN bt.status = 'Lost' THEN 'PERDIDO'
				WHEN bt.status = 'Paid' THEN 'PAGADO'
				WHEN bt.status = 'Pending' THEN 'PENDIENTE'
				WHEN bt.status = 'Refunded' THEN 'REINTEGRADO'
				WHEN bt.status = 'Won' THEN 'GANADO' 
			END AS estado,
			IFNULL(bt.jackpot_amount, 0) jackpot_amount
		FROM tbl_televentas_clientes_transaccion ct
		INNER JOIN tbl_repositorio_bingo_tickets bt ON ct.txn_id = bt.ticket_id
		WHERE ct.txn_id = '" . $id_bet . "' 
		AND ct.tipo_id = 5 
		AND estado in (0,1)
		LIMIT 1";
	//$result["query_1"] = $query_1;
	$list_query = $mysqli->query($query_1);
	$list_1 = array();
	while ($li_1 = $list_query->fetch_assoc()) {
		$list_1[] = $li_1;
	}
	if ($mysqli->error) {
		$result["query_1_error"] = $mysqli->error;
	}
	if (count($list_1) == 0) {
		$switch_continue = 0;
		$monto = 0;

		$query_1 = "
			SELECT t.id, t.ticket_id, 
			t.winning as monto,
			CASE 
				WHEN t.status = 'Expired' THEN 'EXPIRADO'
				WHEN t.status = 'Lost' THEN 'PERDIDO'
				WHEN t.status = 'Paid' THEN 'PAGADO'
				WHEN t.status = 'Pending' THEN 'PENDIENTE'
				WHEN t.status = 'Refunded' THEN 'REINTEGRADO'
				WHEN t.status = 'Won' THEN 'GANADO' 
			END AS estado,
			IFNULL(l.nombre,'') local,
			IFNULL(t.jackpot_amount, 0) jackpot_amount
			FROM tbl_repositorio_bingo_tickets t
			LEFT JOIN tbl_locales l ON t.paid_local_id = l.cc_id
			WHERE 
			t.ticket_id = '" . $id_bet . "' LIMIT 1;
		";

		$list_query = $mysqli->query($query_1);
		$list_1 = array();
		while ($li_1 = $list_query->fetch_assoc()) {
			$list_1[] = $li_1;
		}
		if (count($list_1) > 0){
			$result["jackpot_amount"] = $list_1[0]["jackpot_amount"];
			$local_name_temp = mb_strtoupper($list_1[0]["local"], 'UTF-8');
			$findme = 'TELEVENTAS';
			$pos = strpos($local_name_temp, $findme);
			if ($pos === false) {
				$result["http_code"] = 400;
				if($local_name_temp == ""){
					$result["status"] = "Error: El ticket no ha sido marcado como PAGADO";
				}else{
					$result["status"] = "Error: El ticket pertenece al local: " . $local_name_temp;	
				}
				echo json_encode($result);exit();
			} else {
				$local_name = $local_name_temp;
				$switch_continue = 1;
				$monto = $list_1[0]["monto"];
			}
		}

		if($switch_continue === 1){
			$insert_command = "
				INSERT INTO tbl_televentas_clientes_transaccion (
					tipo_id,
					txn_id,
					cliente_id,
					api_id,
					user_id,
					turno_id,
					monto,
					nuevo_balance,
					bono_monto,
					estado,
					created_at
				) VALUES (
					5,
					'" . $id_bet . "',
					0,
					4,
					" . $usuario_id . ",
					" . $turno_id . ",
					" . $monto . ",
					0,
					0,
					0,
					now()
				)";
			$mysqli->query($insert_command);

			$query_3 = "SELECT tra.id, tra.txn_id as ticket_id, tra.monto, tra.estado as status, 
			CASE 
				WHEN bt.status = 'Expired' THEN 'EXPIRADO'
				WHEN bt.status = 'Lost' THEN 'PERDIDO'
				WHEN bt.status = 'Paid' THEN 'PAGADO'
				WHEN bt.status = 'Pending' THEN 'PENDIENTE'
				WHEN bt.status = 'Refunded' THEN 'REINTEGRADO'
				WHEN bt.status = 'Won' THEN 'GANADO' 
			END AS estado ,
			IFNULL(bt.jackpot_amount, 0) jackpot_amount
			FROM tbl_televentas_clientes_transaccion tra ";
			$query_3 .= " INNER JOIN tbl_repositorio_bingo_tickets bt ON tra.txn_id = bt.ticket_id";
			$query_3 .= " WHERE tra.tipo_id = 5 AND api_id = 4 AND tra.txn_id='" . $id_bet . "' ";
			$query_3 .= " AND tra.user_id='" . $usuario_id . "' ";
			$query_3 .= " AND tra.turno_id='" . $turno_id . "' ";
			$query_3 .= " AND tra.estado = 0 LIMIT 1";
			$list_query = $mysqli->query($query_3);
			$list_transaccion = array();
			while ($li = $list_query->fetch_assoc()) {
				$list_transaccion[] = $li;
			}
			if (count($list_transaccion) == 0) {
				$result["http_code"] = 400;
				$result["status"] = "No se pudo guardar la transaccion.";
			} elseif (count($list_transaccion) === 1) {
				if((double)$result["jackpot_amount"] > 0){
					$res_pjp = sec_tlv_add_jackpot_transaction($id_cliente, 4, $id_bet, $turno_id, $result["jackpot_amount"], 0, 
																$usuario_id, $list_transaccion[0]["id"], 'insert');
					if(isset($res_pjp[0]["http_code"]) && $res_pjp[0]["http_code"] == 200){
						$result["http_code"] = 200;
						$result["status"] = "ok";
						$result["amount"] = $monto;
						$result["result"] = $list_transaccion;
						$result["res_pjp"] = $res_pjp;
					}else{
						$result["http_code"] = 400;
						$result["status"] = "Ocurrió un error al generar el registro del jackpot bingo.";
						$result["res_pjp"] = $res_pjp;
					}
				}
				$result["http_code"] = 200;
				$result["status"] = "ok";
				$result["amount"] = $monto;
				$result["result"] = $list_transaccion;
			} else {
				$result["http_code"] = 400;
				$result["status"] = "Ocurrio un error al guardar la transaccion.";
			}
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ticket no encontrado.";
		}

	} elseif (count($list_1) > 0) {
		$result["jackpot_amount"] = $list_1[0]["jackpot_amount"];
		$estado = $list_1[0]["status"];
		if ((int) $estado === 0) {
			$monto = $list_1[0]["monto"];
			$query_2 = " 
				UPDATE tbl_televentas_clientes_transaccion 
				SET
					turno_id = " . $turno_id . ",
					update_user_id = " . $usuario_id . ",
					monto = " . $list_1[0]["monto"] . ",
					created_at = now(),
					updated_at = now()
				WHERE txn_id = '" . $id_bet . "' AND tipo_id = 5 AND api_id = 4 ";
			$mysqli->query($query_2);

			$cmd_exist_jackpot_bingo = "
				SELECT ct.id, IFNULL(ct2.id, 0) jackpot_id 
				FROM tbl_televentas_clientes_transaccion ct 
				LEFT JOIN tbl_televentas_clientes_transaccion ct2 ON ct2.transaccion_id = ct.id AND ct2.tipo_id = 20
				WHERE ct.tipo_id = 5
				AND ct.txn_id = '" . $id_bet . "'
				AND ct.user_id = " . $usuario_id . " 
				AND ct.turno_id = " . $turno_id . " 
				AND ct.estado = 0 
			";

			$list_query_exist_jpj = $mysqli->query($cmd_exist_jackpot_bingo);
			$list_exist_jpj = array();
			while ($li_1 = $list_query_exist_jpj->fetch_assoc()) {
				$list_exist_jpj[] = $li_1;
			}
			if ($mysqli->error) {
				$result["cmd_exist_jackpot_bingo"] = $mysqli->error;
			}
			if(count($list_exist_jpj) == 0){
				$result["http_code"] = 400;
				$result["status"] = "Hubo un error al consultar la transacción, no se pudo guardar.";
			}else if(count($list_exist_jpj) === 1){
				if ((double)$result["jackpot_amount"] > 0 && (int)$list_exist_jpj[0]["jackpot_id"] === 0) {
					$res_pjp = sec_tlv_add_jackpot_transaction($id_cliente, 4, $id_bet, $turno_id, $result["jackpot_amount"], 0, 
																$usuario_id, $list_exist_jpj[0]["id"], 'insert');
					if(isset($res_pjp[0]["http_code"]) && $res_pjp[0]["http_code"] == 200){
						$result["http_code"] = 200;
						$result["status"] = "ok";
						$result["amount"] = $monto;
						$result["result"] = $list_transaccion;
						$result["res_pjp"] = $res_pjp;
					}else{
						$result["http_code"] = 400;
						$result["status"] = "Ocurrió un error al generar el registro del jackpot bingo.";
						$result["res_pjp"] = $res_pjp;
					}
				}else if((double)$result["jackpot_amount"] > 0 && (int)$list_exist_jpj[0]["jackpot_id"] > 0){
					$res_pjp = sec_tlv_add_jackpot_transaction($id_cliente, 4, $id_bet, $turno_id, $result["jackpot_amount"], 0, 
																$usuario_id, $list_exist_jpj[0]["id"], 'update');
					if(isset($res_pjp[0]["http_code"]) && $res_pjp[0]["http_code"] == 200){
						$result["http_code"] = 200;
						$result["status"] = "ok";
						$result["amount"] = $monto;
						$result["result"] = $list_transaccion;
						$result["res_pjp"] = $res_pjp;
					}else{
						$result["http_code"] = 400;
						$result["status"] = "Ocurrió un error al actualizar el registro de JackPot de Bingo.";
						$result["res_pjp"] = $res_pjp;
					}
				}
				$result["http_code"] = 200;
				$result["status"] = "ok";
				$result["amount"] = $list_1[0]["monto"];
				$result["result"] = $list_1;
			}else{
				$result["http_code"] = 400;
				$result["status"] = "Se registro la apuesta, sin embargo ocurrió un error al guardar la transacción.";
			}
		}else{
			$result["http_code"] = 400;
			$result["status"] = "Apuesta ya registrada";
			$result["amount"] = $list_1[0]["monto"];
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrio un error al consultar la apuesta.";
	}

}

function sec_tlv_add_jackpot_transaction($id_cliente, $proveedor, $id_bet, $turno_id, $monto, $estado, $usuario_id, $transaccion_id, $accion){
	global $login;
	global $mysqli;
	if($accion == "insert"){
		$cmd_ins_jackpot = "
			INSERT INTO tbl_televentas_clientes_transaccion (
				tipo_id,
				txn_id,
				cliente_id,
				api_id,
				user_id,
				turno_id,
				monto,
				nuevo_balance,
				bono_monto,
				estado,
				created_at,
				transaccion_id
			) VALUES (
				20,
				'" . $id_bet . "',
				" . $id_cliente . ",
				" . $proveedor . ",
				" . $usuario_id . ",
				" . $turno_id . ",
				" . $monto . ",
				0,
				0,
				" . $estado . ",
				now(),
				" . $transaccion_id . "
			)";
		$mysqli->query($cmd_ins_jackpot);

		$query = "
			SELECT *
			FROM tbl_televentas_clientes_transaccion tra
			WHERE tra.tipo_id = 20
			AND tra.api_id = " . $proveedor . "
			AND tra.user_id = " . $usuario_id . "
			AND tra.txn_id = '" . $id_bet . "' 
			AND tra.transaccion_id = " . $transaccion_id . "
		";

		$list_query = $mysqli->query($query);
		$list_1 = array();
		while ($li_1 = $list_query->fetch_assoc()) {
			$list_1[] = $li_1;
		}
		if ($mysqli->error) {
			$result["list_query_error"] = $mysqli->error;
		}
		if(count($list_1) == 0){
			$result["http_code"] = 400;
			$result["status"] = "No se guardó el registro del jackpot";
			$result["cmd_ins_jackpot"] = $cmd_ins_jackpot;
			$result["query"] = $query;
		}else if(count($list_1) == 1){
			$result["http_code"] = 200;
			$result["status"] = "Ok";
		}else{
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al realizar el registro del JackPot de Bingo";
		}
	}else if ($accion == "update"){
		$cmd_update = "
			UPDATE 
				tbl_televentas_clientes_transaccion tra
			SET 
				monto = " . $monto . "
			WHERE 
				tra.tipo_id = 20 
				AND tra.api_id = " . $proveedor . "
				AND tra.txn_id = '" . $id_bet . "'
				AND tra.estado = 0
				AND transaccion_id = " . $transaccion_id . "
		";
		$mysqli->query($cmd_update);
		$result["http_code"] = 200;
		$result["status"] = "Ok";
	}
	return $result;
}

//*******************************************************************************************************************
// REGISTRAR BINGO PAGO
//*******************************************************************************************************************
if ($_POST["accion"] === "sec_tlv_registrar_bingo_pago") {

	$id_cliente         = $_POST["id_cliente"];
	$cant_bet           = $_POST["cant_bet"];
	$array_bet          = $_POST["array_bet"];
	$array_monto        = $_POST["array_monto"];
	$total_bet          = $_POST["total_bet"];

	$query_1 = "
		SELECT
			SUM( ct.monto ) monto_total,
			COUNT( * ) cantidad 
		FROM
			tbl_televentas_clientes_transaccion ct 
		WHERE
			ct.tipo_id = 5
			AND ct.api_id = 4  
			AND ct.estado = '0' 
			AND ct.txn_id IN ( " . $array_bet . " ) 
	";
	//$result["query_1"] = $query_1;
	$list_query = $mysqli->query($query_1);
	$list_1 = array();
	while ($li_1 = $list_query->fetch_assoc()) {
		$list_1[] = $li_1;
	}
	if ($mysqli->error) {
		$result["query_1_error"] = $mysqli->error;
	}
	if (count($list_1) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los tickets.";
		echo json_encode($result);exit();
	} elseif (count($list_1) > 0) {

		$list_1_monto_total = $list_1[0]["monto_total"];
		$list_1_cantidad = $list_1[0]["cantidad"];

		if ((double) $total_bet === (double) $list_1_monto_total) {
			if ((double) $cant_bet === (double) $list_1_cantidad) {

				$list_balance = array();
				$list_balance = obtener_balances($id_cliente);

				if (count($list_balance) === 1) {

					$balance_actual = $list_balance[0]["balance"];
					$balance_nuevo = $balance_actual + $list_1_monto_total;
					$balance_retiro = $list_balance[0]["balance_retiro_disponible"];
					$monto = $total_bet;

					//*********************** UPDATE BALANCE PRINCIPAL
					query_tbl_televentas_clientes_balance('update', $id_cliente, 1, $balance_nuevo);

					//*********************** UPDATE BALANCE RETIRO DISPONIBLE
					query_tbl_televentas_clientes_balance('update', $id_cliente, 5, ($balance_retiro + $list_1_monto_total));

					//*********************** TRANSACCIONES USADAS
					$query_update_transacciones = " 
						UPDATE tbl_televentas_clientes_transaccion 
						SET
							estado = '1',
							cliente_id = '" . $id_cliente . "',
							turno_id = '" . $turno_id . "',
							cc_id = '" . $cc_id . "',
							update_user_id = '" . $usuario_id . "',
							updated_at = now()
						WHERE tipo_id IN (5,20) AND api_id = 4 AND txn_id IN ( " . $array_bet . " ) and estado = 0 
						";
					$mysqli->query($query_update_transacciones);

					$query_update_transacciones = " 
						UPDATE tbl_repositorio_bingo_tickets 
						SET
							status = 'Paid', updated_at = now()
						WHERE ticket_id IN ( " . $array_bet . " )";
					$mysqli->query($query_update_transacciones);

					$query_3 = "
						SELECT
							ct.id cod_transaccion,
							ct.txn_id,
							ct.monto,
							IFNULL(bt.created,'') as created_date,
							IFNULL(bt.updated_at, '') as paid_date,
							IFNULL(bt.at_unique_id, '') at_unique_id,
							IFNULL(bt.sell_local_id, 0) sell_local_id,
							IFNULL(bt.paid_local_id, 0) paid_local_id,
							IFNULL(bt.amount, 0) amount,
							IFNULL(bt.winning, 0) winning,
							IFNULL(bt.jackpot_amount, 0) jackpot_amount
						FROM
							tbl_televentas_clientes_transaccion ct
						INNER JOIN tbl_repositorio_bingo_tickets bt ON ct.txn_id = bt.ticket_id
						WHERE
							ct.tipo_id IN (5,20)
							AND ct.api_id = 4 
							AND ct.estado = '1' 
							AND ct.cliente_id = '" . $id_cliente . "' 
							AND ct.update_user_id = '" . $usuario_id . "' 
							AND ct.txn_id IN ( " . $array_bet . " ) 
					";
					$list_query = $mysqli->query($query_3);
					$list_transaccion = array();
					while ($li = $list_query->fetch_assoc()) {
						$list_transaccion[] = $li;
					}
					if (count($list_transaccion) == 0) {
						$result["http_code"] = 400;
						$result["status"] = "Se registro la apuesta, sin embargo no se pudo guardar la transacción.";
					} elseif (count($list_transaccion) > 0) {

						$temp_balance_actual = $balance_actual;
						$temp_balance_retiro_actual = $balance_retiro;

						foreach ($list_transaccion as $bet) {

							$temp_balance_nuevo = $temp_balance_actual + $bet['monto'];
							$temp_balance_retiro_nuevo = $temp_balance_retiro_actual + $bet['monto'];

							$query_update_trans = " 
								UPDATE tbl_televentas_clientes_transaccion 
								SET nuevo_balance = '" . $temp_balance_nuevo . "' 
								WHERE id = " . $bet['cod_transaccion'] . " 
							";
							$mysqli->query($query_update_trans);
							if ($mysqli->error) {
								$result["error_update_".$bet['cod_transaccion']] = $mysqli->error;
							}

							query_tbl_televentas_clientes_balance_transaccion('insert', $bet['cod_transaccion'], 
								$id_cliente, 1, $temp_balance_actual, $bet['monto'], $temp_balance_nuevo);


							query_tbl_televentas_clientes_balance_transaccion('insert', $bet['cod_transaccion'], 
								$id_cliente, 5, $temp_balance_retiro_actual, $bet['monto'], $temp_balance_retiro_nuevo);

							$temp_balance_actual = $temp_balance_nuevo;
							$temp_balance_retiro_actual = $temp_balance_retiro_nuevo;

							$ticket['ticket_id'] = $bet['txn_id'];
							$ticket['proveedor_id'] = '4';
							$ticket['created'] = $bet["created_date"];
							$ticket['calc_date'] = '';
							$ticket['paid_date'] = $bet["paid_date"];
							$ticket['external_id'] = $bet["at_unique_id"];
							$ticket['game'] = '';
							$ticket['sell_local_id'] = $bet["sell_local_id"];
							$ticket['paid_local_id'] = $bet["paid_local_id"];
							$ticket['num_selections'] = 1;
							$ticket['price'] = $bet["amount"];
							$ticket['stake_amount'] = $bet["amount"];
							$ticket['winning_amount'] = $bet["winning"];
							$ticket['jackpot_amount'] = $bet["jackpot_amount"];
							$ticket['status'] = 1;
							$ticket_query = query_tbl_televentas_tickets($ticket);
						}

						$result["http_code"] = 200;
						$result["status"] = "Ok";
					} else {
						$result["http_code"] = 400;
						$result["status"] = "Se registro la apuesta, sin embargo ocurrió un error al guardar la transacción.";
					}

					//*********************** VALIDACION BALANCE
				} else {
					$result["http_code"] = 400;
					$result["status"] = "Ocurrió un error al consultar el balance.";
					$result["result"] = $list_balance;
				}
			} else {
				$result["http_code"] = 400;
				$result["status"] = "Existen diferencias con el total enviado y el total en data.";
			}
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Existen diferencias con el total enviado y el total en data.";
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar la apuesta.";
	}

}







//*******************************************************************************************************************
// OBTENER TRANSACCIONES NUEVAS 
//*******************************************************************************************************************
if ($_POST["accion"] === "obtener_transacciones_nuevas") {

	$fecha_hora = $_POST["fecha_hora"];
	$clientes   = $_POST["clientes"];
	$hash       = $_POST["hash"];

	$query_1 = "
		SELECT
			t.id,
			t.tipo_id,
			t.user_id,
			IFNULL(t.api_id, 0) AS proveedor,
			t.update_user_id,
			cuenta_id,
			cu.cuenta_descripcion,
			t.estado,
			count( * ) AS cantidad,
			t.cliente_id,
			IFNULL(tj.nombre, '') AS tipo_jugada,
			IFNULL(CONCAT( c.nombre, ' ', IFNULL( c.apellido_paterno, '' ), ' ', IFNULL( c.apellido_materno, '' ) ), '') AS cliente
		FROM
			tbl_televentas_clientes_transaccion t
			LEFT JOIN tbl_televentas_clientes_tipo_transaccion tipo ON tipo.id = t.tipo_id
			LEFT JOIN tbl_televentas_clientes c ON c.id = t.cliente_id
			LEFT JOIN tbl_televentas_recargas_bono bon ON bon.id = t.bono_id
			LEFT JOIN tbl_cuentas_apt cu ON cu.id = t.cuenta_id
			LEFT JOIN tbl_televentas_tipo_juego tj ON t.id_juego_balance = tj.id
		WHERE
			t.cliente_id IN ( $clientes ) 
			AND t.estado IN (1,2,3,4,5,6) 
			AND t.tipo_id IN (1,2,3,4,5,9,11,12,13,14,17,18,21,22,24,25,26,28,29,30,31) 
			AND t.update_user_at >= '$fecha_hora' 
			AND c.block_hash = '$hash' 
		GROUP BY
			t.cliente_id 
		ORDER BY
			( IFNULL( t.update_user_at, t.created_at ) ) DESC
		";
	//$result["consulta_query1"] = $query_1;
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

	$query_2 = "
		SELECT 
			c.id client_id,
			c.block_user_id
		FROM tbl_televentas_clientes c
		WHERE 
			c.id IN (" . $clientes . ") 
			AND c.block_user_id !=" . $usuario_id . "
		ORDER BY c.id ASC
		";
	$list_query_2 = $mysqli->query($query_2);
	$list_desbloqueados = array();
	while ($li_2 = $list_query_2->fetch_assoc()) {
		$list_desbloqueados[] = $li_2;
	}
	$result["result_2"] = $list_desbloqueados;

	$query_2 = "
		SELECT 
			c.id client_id
		FROM tbl_televentas_clientes c
		WHERE 
			c.id IN ( $clientes ) 
			AND c.block_user_id = $usuario_id 
			AND c.block_hash != '$hash' 
		ORDER BY c.id ASC
		";
	$list_query_2 = $mysqli->query($query_2);
	$list_otra_pestana = array();
	while ($li_2 = $list_query_2->fetch_assoc()) {
		$list_otra_pestana[] = $li_2;
	}
	$result["result_otra_pestana"] = $list_otra_pestana;

	$result["result_tls_ultima_version"] = 0;
	$query_4 = "
		SELECT
			v.version
		FROM tbl_versiones v
		WHERE v.menu_id = 160
		ORDER BY v.id DESC
		LIMIT 1
		";
	$list_query_4 = $mysqli->query($query_4);
	while ($li_4 = $list_query_4->fetch_assoc()) {
		$result["result_tls_ultima_version"] = $li_4["version"];
	}
}




//*******************************************************************************************************************
// BLOQUEAR CLIENTE
//*******************************************************************************************************************
if ($_POST["accion"] === "bloquear_cliente") {

	$id_cliente = $_POST["cliente_id"];
	$hash       = $_POST["hash"];

	query_tbl_televentas_clientes_block($id_cliente, 1);

	$query_1 = "
	SELECT 
		c.id client_id
	FROM tbl_televentas_clientes c
	WHERE 
		c.id = " . $id_cliente . " 
		AND c.block_user_id = " . $usuario_id . " 
	ORDER BY c.id ASC
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
		$result["status"] = "No se pudo desbloquear al cliente.";
	} elseif (count($list_transaccion) > 0) {

        $query_update = "
                UPDATE tbl_televentas_clientes
                SET
                        block_hash = '$hash',
                        updated_at = now()
                WHERE id = '" . $list_transaccion[0]["client_id"] . "'
                ";
        $mysqli->query($query_update);

		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_transaccion;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al desbloquear al cliente.";
	}
}


if ($_POST["accion"]==="listar_motivos_balance_sube") {
	$query = "SELECT id, motivo as nombre
	FROM tbl_televentas_motivo_balances 
	WHERE tipo_balance = 1   AND estado = 1
	ORDER BY id ASC";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos";
	$result["result"] = $list;
}


if ($_POST["accion"]==="listar_motivos_balance_baja") {
	$query = "
		SELECT 
			id, 
			motivo as nombre
		FROM tbl_televentas_motivo_balances 
		WHERE tipo_balance = 2   AND estado = 1
		ORDER BY id ASC
	";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos";
	$result["result"] = $list;
}


if ($_POST["accion"]==="listar_juegos_balance") {
	$query = "
		SELECT id, nombre 
		FROM tbl_televentas_tipo_juego 
		WHERE estado = 1
		ORDER BY id ASC
		";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos";
	$result["result"] = $list;
}

if ($_POST["accion"]==="listar_cajeros_tlv") {
	$query = "
		SELECT 
		u.id,
		 CONCAT(
			IF
				( LENGTH( pl.apellido_paterno ) > 0, CONCAT( UPPER( pl.apellido_paterno ), ' ' ), '' ),
			IF
				( LENGTH( pl.apellido_materno ) > 0, CONCAT( UPPER( pl.apellido_materno ), ' ' ), '' ),
			IF
				( LENGTH( pl.nombre ) > 0, UPPER( pl.nombre ), '' ) 
			) nombre
		from tbl_usuarios u
		INNER JOIN tbl_personal_apt pl ON pl.id = u.personal_id
		WHERE u.estado = 1
		";

	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos";
	$result["result"] = $list;
}

	
if ($_POST["accion"]==="listar_supervisores_tlv") {
	$grupo_id = $login ? $login['grupo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;
	$where_usuario = "";
	$supervisor = 0;
	if($grupo_id == 31){
		$where_usuario = " AND u.id = " . $usuario_id;
		$supervisor = 1;
	}
	$query = "
		SELECT p.id, p.nombre 
		FROM tbl_usuarios u
		INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		WHERE u.grupo_id = 31
		" . $where_usuario .  "
		ORDER BY p.id ASC
		";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos";
	$result["result"] = $list;
	$result["supervisor"] = $supervisor;
}


//*******************************************************************************************************************
// DESBLOQUEAR CLIENTE
//*******************************************************************************************************************
if ($_POST["accion"] === "desbloquear_cliente") {

	$id_cliente = $_POST["cliente_id"];
	$query_1 = "
	SELECT 
		c.id client_id
	FROM tbl_televentas_clientes c
	WHERE 
		c.id = " . $id_cliente . " 
		AND c.block_user_id = " . $usuario_id . " 
	ORDER BY c.id ASC
	";
	//$result["consulta_query"] = $query_3;
	$list_query = $mysqli->query($query_1);
	$list_transaccion = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_transaccion[] = $li;
		query_tbl_televentas_clientes_block($li["client_id"], 0);
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




//*******************************************************************************************************************
// EDITAR CLIENTE
//*******************************************************************************************************************
if ($_POST["accion"] === "editar_cliente") {
	include("function_replace_invalid_caracters.php");
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;
	$id_cliente        = $_POST["id_cliente"];
	$tipo_doc          = $_POST["tipo_doc"];
	$num_doc           = $_POST["num_doc"];
	$idweb             = replace_invalid_caracters(trim($_POST["idweb"]));
	$id_jugador        = replace_invalid_caracters(trim($_POST["id_jugador"]));
	$nombre            = replace_invalid_caracters(trim($_POST["nombre"]));
	$apepaterno        = replace_invalid_caracters(trim($_POST["apepaterno"]));
	$apematerno        = replace_invalid_caracters(trim($_POST["apematerno"]));
	$cliente_actual_fc = $nombre.' '.$apepaterno.' '.$apematerno;
	$correo            = $_POST["correo"];
	$cc_id             = $_POST["cc_id"];
	$celular           = $_POST["celular"];
	$fec_nac           = $_POST["fec_nac"];
	$fecha_minima      = date("Y-m-d", strtotime("-18 years"));
	$bono_limite       = $_POST["bono_limite"];
	$cliente_tercero_titular = $_POST["cliente_tercero_titular"];
	$hash_block        = $_POST["block_hash"];
	$nacionalidad      = replace_invalid_caracters(trim($_POST["nacionalidad"]));
	$ubigeo 		   = replace_invalid_caracters(trim($_POST["ubigeo"]));
	$direccion 		   = replace_invalid_caracters(trim($_POST["direccion"]));
	$tyc 			   =  $_POST["tyc"];
	$pep 			   =  $_POST["pep"];

	$pep = $pep == ""? null : $pep;


	$result["result_dupli"] = array();
	$turno = get_turno();
	if (count($turno) > 0) {
		$local_id = $turno[0]["local_id"];
	}
	if (!strlen(trim($num_doc)) > 0) {
		$result["http_code"] = 400;
		$result["status"] = "El campo Num.Doc. no debe estar vacío.";
		echo json_encode($result);exit();
	}
	if (!empty($fec_nac)) {
		if ($fec_nac >  $fecha_minima) {
			$result["http_code"] = 400;
			$result["status"] = "Debe cumplir con la mayoria de edad";
			echo json_encode($result);exit();
		}
	}

	if (!empty($correo)) {
		$query_correo = "
			SELECT id 
			FROM tbl_televentas_clientes 
			WHERE
				num_doc <> '" . $num_doc . "' 
				AND correo = '" . $correo . "' 
		";
		$list_correo= $mysqli->query($query_correo);
		$list_c_correo = array();
		while ($li_correo = $list_correo->fetch_assoc()) {
			$list_c_correo[] = $li_correo;
		}
		if ($mysqli->error) {
			$result["consulta_error"] = $mysqli->error;
		}

		if (count($list_c_correo) > 0) {
			$result["http_code"] = 400;
			$result["status"] = "El correo ingresado ya se encuentra registrado";
			echo json_encode($result);exit();

		} 
	}

	$registroSIC= false;
	if (!empty($nacionalidad) && !empty($ubigeo) && !empty($direccion) && !empty($celular) && !empty($fec_nac) && 
		!empty($nombre) && !empty($apepaterno) && !empty($apematerno) && !empty($tyc) && ($pep=="0" || $pep=="1")) {	
		$registroSIC = true;
	} 


	$list_cliente_actual = array();
	if((int)$id_cliente > 0){
		$query_update_titular1 = "
				UPDATE tbl_televentas_titular_abono 
				SET estado = 0
				WHERE id_cliente = '" . $id_cliente. "'  and estado = 1
				";
		$mysqli->query($query_update_titular1);

		$query_update_titular = "
				UPDATE tbl_televentas_titular_abono 
				SET estado = 1 
				WHERE id_cliente = '" . $id_cliente. "' AND id='" . $cliente_tercero_titular. "'
				";
		$mysqli->query($query_update_titular);

		// Obtener datos actuales del cliente
		$query_datos_cliente = "
		SELECT 
			num_doc,
			telefono,
			IFNULL(web_id, '') web_id,
			IFNULL(web_full_name, '') web_full_name,
			IFNULL(fec_nac, '') fec_nac
			
		FROM
			tbl_televentas_clientes
		WHERE 
			id = '" . trim($id_cliente) . "'
		";

		$list_query_datos_cliente = $mysqli->query($query_datos_cliente);
		while ($li = $list_query_datos_cliente->fetch_assoc()) {
			$list_cliente_actual[] = $li;
		}
		
		$telefono_actual = $list_cliente_actual[0]["telefono"];
		$full_name_actual = $list_cliente_actual[0]["web_full_name"];
		$full_name_nuevo = "";
	}else{
		// Obtener datos actuales del cliente
		$query_datos_cliente = "
		SELECT 
			'" . $num_doc . "' num_doc,
			'" . $celular . "' telefono,
			'" . $idweb . "' web_id,
			'' web_full_name,
			'" . $fec_nac . "' fec_nac";
		$list_query_datos_cliente = $mysqli->query($query_datos_cliente);
		while ($li = $list_query_datos_cliente->fetch_assoc()) {
			$list_cliente_actual[] = $li;
		}
		$telefono_actual  = $list_cliente_actual[0]["telefono"];
		$full_name_actual = $list_cliente_actual[0]["web_full_name"];
		$full_name_nuevo  = "";
	}

	//Validar WEB-ID
	$update_web_full_name = "";
	if(strlen(trim($idweb))>0){
		if((isset($list_cliente_actual[0]["web_id"]) && $list_cliente_actual[0]["web_id"]!==$idweb) || (int)$id_cliente == 0){
			$array_user = array();
			$array_user = api_calimaco_check_user((int)$id_cliente, $idweb);
			if(isset($array_user["result"])){
				if($array_user["result"]==="OK") {
					$array_user["first_name"]  = strtoupper(replace_invalid_caracters(trim($array_user["first_name"])));
					$array_user["middle_name"] = strtoupper(replace_invalid_caracters(trim($array_user["middle_name"])));
					$array_user["last_name"]   = strtoupper(replace_invalid_caracters(trim($array_user["last_name"])));
					$full_name_nuevo .= (strlen($array_user["first_name"])>0)?$array_user["first_name"]:'';
					$full_name_nuevo .= (strlen($array_user["middle_name"])>0)?' '.$array_user["middle_name"]:'';
					$full_name_nuevo .= (strlen($array_user["last_name"])>0)?' '.$array_user["last_name"]:'';
					$update_web_full_name = " web_full_name = '" . $full_name_nuevo . "', ";
				} else {
					$result["http_code"] = 400;
					$result["status"] = "El WEB-ID no existe.";
					echo json_encode($result);
					exit();
				}
			} else {
				$result["http_code"] = 400;
				$result["status"] = "El WEB-ID no existe.";
				echo json_encode($result);
				exit();
			}
		}
	}
	// ***************************************************************************************
	$where_fec_nac  = "";
	$update_fec_nac = "";
	if (strlen(trim($fec_nac)) > 8) {
		$where_fec_nac  = " OR fec_nac = '" . trim($fec_nac) . "' ";
		$update_fec_nac = " fec_nac = '" . trim($fec_nac) . "', ";
	}

	$where_correo  = "";
	$update_correo= "";
	if (strlen(trim($correo)) > 0) {
		$where_correo  = " OR correo = '" . trim($correo) . "' ";
		$update_correo = " correo = '" . trim($correo) . "', ";
	}

	// Validar num_doc, telefono y idweb
	$where_celular  = "";
	$update_celular = "";
	if (strlen(trim($celular)) === 9) {
		$where_celular  = " OR telefono = '" . trim($celular) . "' ";
		$update_celular = " telefono = '" . trim($celular) . "', ";
	}
	$where_idweb  = "";
	$update_idweb = "";
	if(strlen(trim($idweb))>0){
		$where_idweb  = " OR web_id = '" . trim($idweb) . "' ";
		$update_idweb = " web_id = '" . trim($idweb) . "', ";
	}else{
		$update_idweb         = " web_id = null, ";
		$update_web_full_name = " web_full_name = null, ";
	}
	$where_player_id  = "";
	$update_player_id = "";
	if(strlen(trim($id_jugador))>0){
		$where_player_id  = " OR player_id = '" . trim($id_jugador) . "' ";
		$update_player_id = " player_id = '" . trim($id_jugador) . "', ";
	}

	$query_validar_duplicado = "
		SELECT 
			id, 
			IFNULL(num_doc, '') num_doc,
			IFNULL(telefono, '') telefono,
			IFNULL(fec_nac, '') fec_nac,
			IFNULL(correo, '') correo,
			IFNULL(web_id, '') web_id,
			IFNULL(player_id, '') player_id,
			CONCAT_WS(' ', nombre, apellido_paterno, apellido_materno) AS cliente
		FROM
			tbl_televentas_clientes
		WHERE 
			id != '" . trim($id_cliente) . "'
			AND 
			( num_doc = '" . trim($num_doc) . "' 
				".$where_idweb."
				" . $where_celular . " 
				" . $where_player_id . " 
			)
		";
	$list_query = $mysqli->query($query_validar_duplicado);
	$row_count = $list_query->num_rows;
	$result["status"] = '';
	while ($row = $list_query->fetch_assoc()) {
		$valid_webid = $row["web_id"];
		$cliente = trim($row["cliente"]);
		if ($cliente == '') {
			$cliente = 'con WEB-ID ' . $valid_webid;
		}

		$result["http_code"] = 400;

		if ($row["num_doc"] == $num_doc) {
			$result["status"] .= "El cliente " . $cliente . " tiene el Núm. Documento: " . $num_doc . ".\n";
		}
		if ($row["telefono"] == $celular) {
			$result["status"] .= "El cliente " . $cliente . " tiene el Celular: " . $celular . ".\n";
		}
		if ($row["web_id"] == $idweb) {
			$result["status"] .= "El cliente " . $cliente . " tiene el ID-WEB: " . $idweb . ".\n";
		}
		if ($row["player_id"] == $id_jugador) {
			$result["status"] .= "El cliente " . $cliente . " tiene el ID-JUGADOR: " . $id_jugador . ".\n";
		}
		$where_cliente='';
		$where_idweb='';
		$where_celular='';
		$where_id_jugador='';
	 
		if(!Empty($cliente_actual_fc)){		 
			$where_cliente = " OR c.nombre LIKE '%".$cliente_actual_fc."%'";
			$where_cliente .= " OR c.apellido_paterno LIKE '%".$cliente_actual_fc."%'";	
			$where_cliente .= " OR c.apellido_materno LIKE '%".$cliente_actual_fc."%' ";	 
		}
	 
		if(!Empty($idweb)){		 
			$where_idweb = " OR c.web_id ='" . $idweb . "'";	 
		}
		if(!Empty($celular)){		 
			$where_celular = " OR c.telefono ='" . $celular . "'";
		}
		if(!Empty($id_jugador)){		 
			$where_id_jugador = " OR c.player_id='" . $id_jugador . "'";	
		}
	
		$query_1 = "
			SELECT 
			c.id, 
				if (c.tipo_doc = '0', 'DNI' , if (c.tipo_doc = '1', 'CE/PTP' , 'PASAPORTE')) as tipo_doc_nomb,
				c.tipo_doc,
				IFNULL(c.num_doc, '') num_doc,
				IFNULL(c.telefono, '') telefono,
				IFNULL(c.fec_nac, '') fec_nac,
				IFNULL(c.correo, '') correo,
				IFNULL(c.web_id, '') web_id,
				IFNULL(c.player_id, '') player_id,
				IFNULL(c.web_full_name, '') web_full_name,
				CONCAT(IFNULL(c.nombre, ''), ' ', IFNULL(c.apellido_paterno, ''), ' ', IFNULL(c.apellido_materno, '')) AS cliente, 
				(SELECT COUNT(*) FROM tbl_televentas_clientes_transaccion tr WHERE tr.cliente_id = c.id) AS total_tran,
				(SELECT balance from tbl_televentas_clientes_balance tb WHERE tb.tipo_balance_id=1 and tb.cliente_id=c.id) AS total_balance

			FROM 
				tbl_televentas_clientes c
			WHERE
			(c.num_doc like '%" . $num_doc . "%' 
			" . $where_cliente . "
			" . $where_idweb . "
			" . $where_celular . "
			" . $where_id_jugador . "
		  )
		";
	 
		$list_query_1 = $mysqli->query($query_1);
		$list_1 = array();
		while ($li_1 = $list_query_1->fetch_assoc()) {
	 
			$list_1[] = $li_1;
		}
		if ($mysqli->error) {
			$result["consulta_error"] = $mysqli->error;
		}
		if (count($list_1) > 0) {
			$result["http_code"] = 400;
			$result["msj"] = "Clientes duplicados.";
			$result["result_dupli"] = $list_1;
		} else {
			$result["http_code"] = 200;
		 
		}
	}
	if ($result["status"] != '') {
		$result["datos_cliente"] = $list_cliente_actual;
		echo json_encode($result);
		exit();
	}
	// ***************************************************************************************
	// EDICIÓN
	$cliente_new = 0;
	if((int)$id_cliente > 0){
		$query_update = "
			UPDATE tbl_televentas_clientes 
			SET 
				tipo_doc = '" . $tipo_doc . "',
				num_doc = '" . $num_doc . "',
				apellido_paterno = '" . $apepaterno . "',
				apellido_materno = '" . $apematerno . "',
				nombre = '" . $nombre . "',
				" . $update_celular . "
				" . $update_fec_nac . "
				" . $update_correo . "
				" . $update_web_full_name . "
				" . $update_player_id . "
				" . $update_idweb . "
				cc_id = '" . $cc_id . "',
				bono_limite = '" . $bono_limite . "',
				nacionalidad = '" . $nacionalidad . "',
				ubigeo = '" . $ubigeo . "',
				direccion = '" . $direccion . "',
				terminos_condiciones  = $tyc,
				es_pep = " . ($pep === null ? 'null' : $pep) . "
				,updated_at = now()
			WHERE id = '" . $id_cliente . "'
			";
		$mysqli->query($query_update);
		if ($mysqli->error) {
			$result["update_error"] = $mysqli->error;
		}

		//HISTORIAL DE CAMBIOS CLIENTE
		
		$insert_hist_cli= " 
			INSERT INTO tbl_televentas_log_clientes
			(`origen_cambio`, `cliente_id`, `tipo_doc`, `num_doc`, `web_id`, `player_id`, `telefono`, `nombre`, `correo`, `apellido_paterno`, `apellido_materno`, `web_full_name`, `fec_nac`, `nacionalidad`,`ubigeo`,`direccion`,`terminos_condiciones`,`es_pep`, `usuario_id`, `created_at`)
			VALUES 
			( 0,'" . $id_cliente . "','" . $tipo_doc . "', '" . $num_doc . "', '" . trim($idweb) . "', '" . trim($id_jugador) . "', '" . trim($celular) . "', '" . $nombre . "', '" . trim($correo) . "', '" . $apepaterno . "',  '" . $apematerno . "', '" . $full_name_nuevo . "',  '" . trim($fec_nac) . "', '" . trim($nacionalidad) . "', '" . trim($ubigeo) . "', '" . $direccion . "', $tyc, $pep, '" . $usuario_id . "', now())";

		$mysqli->query($insert_hist_cli);

		if ($mysqli->error) {
			$result["insert_log_clientes_error"] = $mysqli->error;
		}

		//FIN HISTORIAL DE CAMBIOS CLIENTE

		// EDICIÓN BILLETERO
		$query_cli_bt = "
			SELECT 
				id 
			FROM at_deportivo.tbl_mibilletero_usuarios 
			WHERE usuario='" . $telefono_actual . "' 
			";
		$list_cli_bt = $mysqli->query($query_cli_bt);
		$cli_bt = array();
		if ($mysqli->error) {
			$result["consulta_error"] = $mysqli->error;
		} else {
			while ($li_bt = $list_cli_bt->fetch_assoc()) {
				$cli_bt[] = $li_bt;
			}
		}
		if (count($cli_bt) > 0) {
		 
			$query_update_bt = "
				UPDATE at_deportivo.tbl_mibilletero_usuarios
				SET 
					usuario = '" . $celular . "', 
					updated_at = now()
				WHERE id = '" . $cli_bt[0]["id"] . "'
				";
			$mysqli->query($query_update_bt);
			if ($mysqli->error) {
				$result["update_error"] = $mysqli->error;
			}
		}
		// FIN EDICIÓN BILLETERO 
	}else{
		$cliente_new = 1;
		$ins_wi = '';
		$ins_val_wi = '';
		if($idweb != ''){ 
			$ins_wi = 'web_id,'; 
			$ins_val_wi = $idweb.',';
		}

		$ins_cel = '';
		$ins_val_cel = '';
		if($celular != ''){ 
			$ins_cel = 'telefono,'; 
			$ins_val_cel = $celular.',';
		}
		$ins_corr = '';
		$ins_val_corr = '';
		if($correo != ''){ 
			$ins_corr = 'correo,'; 
			$ins_val_corr = "'".$correo."',";
		}
		$query_insert_web = "
			INSERT INTO tbl_televentas_clientes (
					tipo_doc,
					apellido_paterno,
					apellido_materno,
					nombre,
					num_doc,
					" . $ins_wi . "
					" . $ins_cel . "
					cc_id,
					created_user_id,
					created_at,
					updated_at,
					block_user_id,
					bono_limite,
					web_full_name,
					fec_nac,
					" . $ins_corr . "
					block_hash,
					nacionalidad,
					ubigeo,
					direccion,
					terminos_condiciones
					" . ($pep !== null ? ',es_pep' : '') . "
			) VALUES (
					'" . $tipo_doc . "',
					'" . $apepaterno . "',
					'" . $apematerno . "',
					'" . $nombre . "',
					'" . $num_doc . "',
					" . $ins_val_wi . "
					" . $ins_val_cel . "
					'3900',
					'" . $usuario_id . "',
					now(),
					now(),
					'" . $usuario_id . "',
					'10000',
					'" . $full_name_nuevo . "',
					'" . $fec_nac . "',
					" . $ins_val_corr . "
					'" . $hash_block . "',
					'" . $nacionalidad . "',
					'" . $ubigeo . "',
					'" . $direccion . "',
					$tyc
					" . ($pep !== null ? ',' . $pep : '') . "
		);
		";
		$mysqli->query($query_insert_web);
		if ($mysqli->error) {
			// $result["query_insert_web"] = $mysqli->error;
		}
		//$result["query_insert"] = $query_insert_web;

		// Consultar si el cliente pertenece a la promo de GR y asignar el balance, porque fue cliente nuevo
		$query_numdoc = "
			SELECT id
			FROM tbl_televentas_clientes
			WHERE 
				apellido_paterno = '" . $apepaterno . "'
				AND apellido_materno = '" . $apematerno . "'
				AND nombre = '" . $nombre . "'
				AND num_doc = '" . $num_doc . "'
				AND block_user_id = '" . $usuario_id . "'
				AND fec_nac = '" . $fec_nac . "'
		";
		$list_numdoc = $mysqli->query($query_numdoc);
		$list_numdoc_fn = array();
		if ($mysqli->error) {
			$result["http_code"] = 400;
			$result["status"] = "Error al consultar el numero de documento del cliente.";
			//$result["query"] = $query_numdoc;
			//$result["query_error"] = $mysqli->error;
			echo json_encode($result);exit();
		} else {
			while ($li_1 = $list_numdoc->fetch_assoc()) {
				$list_numdoc_fn[] = $li_1;
			}
		}

		//HISTORIAL DE CAMBIOS CLIENTE

		$insert_hist_cli_n= " 
			INSERT INTO tbl_televentas_log_clientes
			(`origen_cambio`, `cliente_id`, `tipo_doc`, `num_doc`, `web_id`, `player_id`, `telefono`, `nombre`, `correo`, 
			`apellido_paterno`, `apellido_materno`, `web_full_name`, `fec_nac`, `nacionalidad`,`ubigeo`,`direccion`, `terminos_condiciones`,
			`es_pep`, `usuario_id`, `created_at`)
			VALUES 
			( 0,'" . $list_numdoc_fn[0]["id"] . "','" . $tipo_doc . "', '" . $num_doc . "', '" . trim($idweb) . "', '" . trim($id_jugador) . "', '" . 
			 trim($celular) . "', '" . $nombre . "', '" . trim($correo) . "', '" . $apepaterno . "',  '" . $apematerno . "', '" . $full_name_nuevo . "',  '" . 
			 trim($fec_nac) . "', '" . trim($nacionalidad) . "', '" . trim($ubigeo) . "', '" . trim($direccion) . "', $tyc, " . (($pep !== null && strlen($pep) > 0) ? $pep : 'null') . ", '" . $usuario_id . "', now())";

		//$result["insert_hist_cli_n"] = $insert_hist_cli_n; echo json_encode($result); exit();
		$mysqli->query($insert_hist_cli_n);
		if ($mysqli->error) {
			//$result["insert_log_clientes_new_error"] = $mysqli->error;
		}

		//FIN HISTORIAL DE CAMBIOS CLIENTE

		$cliente_num_doc = "";
		if ( count($list_numdoc_fn) === 1 ) { 
			$cliente_id_new = $list_numdoc_fn[0]["id"];
			// $cliente_fecha_creacion = $list_numdoc_fn[0]["created_at"];
			// $result["cliente_numero_doc"] = $cliente_num_doc;
		} else {
			$result["http_code"] = 400;
			$result["status"] = "No se puede obtener el dni del cliente, para ver si tiene una promo.";
			echo json_encode($result);exit();
		}

		// BALANCE BILLETERO
		$list_balance = array();
		$list_balance = obtener_balances($cliente_id_new);

		if(!(count($list_balance)>0)){
			$result["result"] = "No se pudo obtener los balances, cuando es cliente nuevo.";
			echo json_encode($result);exit();
		} else {
			$post_fields = [
				"draw_slug" => "bonosvirtuales202311",
				"num_doc" => "$num_doc"
			];
			$request_json = json_encode($post_fields);

			$curl = curl_init();

			curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://sorteos.apuestatotal.com/api/teleservicios/set_bonus',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => $request_json,
			CURLOPT_HTTPHEADER => array(
				'Content-Type: application/json',
				'Authorization: Bearer ' .env('TOKEN_SORTEOS')
			),
			));

			$response = curl_exec($curl);
			curl_close($curl);

			$response_arr = json_decode($response, true);

			if ( !empty($response_arr) ) {
				if ( isset($response_arr["http_code"]) ){
					$result["resp_asignar_bonoAT"] = $response_arr;
					if ( (int)$response_arr["http_code"]===200 ) {
						$result["resp_asignar_bonoAT_alert"] = "Se le asignó correctamente el Bono promocional al cliente.";
					} else if ( (int)$response_arr["http_code"]===400 ) {
						$result["resp_asignar_bonoAT_alert_NO_ASIGNADO"] = "No se le asignó el Bono promocional al cliente, resultado: ".$response_arr['result'];
					}
				}
			} else {
				$result["resp_asignar_bonoAT"] = "Ocurrió un error con la API que asigna el BonoAT desde Sorteos.";
				$result["resp_asignar_bonoAT_json"] = $response_arr;
			}
		}
	}

	$where = "";
	if((int)$id_cliente > 0){
		$where = 
			" id = " . $id_cliente;
	}else{
		$where =
			" 
			apellido_paterno = '" . $apepaterno . "'
			AND apellido_materno = '" . $apematerno . "'
			AND nombre = '" . $nombre . "'
			AND num_doc = '" . $num_doc . "'
			AND block_user_id = '" . $usuario_id . "'
			AND fec_nac = '" . $fec_nac . "'
			";
	}

	$query_4 = "
		SELECT 
			id,
			IFNULL(tipo_doc, '') tipo_doc,
			IFNULL(num_doc, '') num_doc,
			UPPER(IFNULL(nombre, '')) nombre,
			UPPER(IFNULL(apellido_paterno, '')) apellido_paterno,
			UPPER(IFNULL(apellido_materno, '')) apellido_materno,
			IFNULL(web_id, '') web_id,
			IFNULL(player_id, '') player_id,
			IFNULL(web_full_name, '') web_full_name,
			IFNULL(telefono, '') telefono,
			IFNULL(fec_nac, '') fec_nac,
			IFNULL(correo, '') correo,
			IFNULL(block_user_id, '') block_user_id,
			IFNULL(cc_id, '') cc_id,
			IFNULL(bono_limite, '10000') bono_limite,
			1 tipo_balance_id,
			UPPER(IFNULL(nacionalidad, '')) nacionalidad,
			UPPER(IFNULL(ubigeo, '')) ubigeo,
			UPPER(IFNULL(direccion, '')) direccion,
			IFNULL(terminos_condiciones , false) tyc,
			IFNULL(es_pep, '') es_pep,
			'1' clienteExistente

		FROM tbl_televentas_clientes 
		WHERE " . $where;
	$list_query_4 = $mysqli->query($query_4);
	$list_4 = array();
	while ($li_4 = $list_query_4->fetch_assoc()) {
		$list_4[] = $li_4;
	}
	if ($mysqli->error) {
		//$result["consulta_error"] = $mysqli->error;
	}

	$is_min = cliente_mincetur($tipo_doc, $num_doc);
	

	if (count($list_4) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "El cliente no existe.";
		echo json_encode($result); exit();
	} elseif (count($list_4) > 0) {
		$list_4[0]["is_disabled_mincetur"] = $is_min;
		$list_4[0]["is_new_client"] = $cliente_new;

		$result["http_code"] = 200;
		$result["status"] = "Datos del cliente editado satisfactoriamente.";
		$result["result"] = $list_4[0];
	} else {
		$result["http_code"] = 400;
		$result["status"] = "El cliente no existe.";
		echo json_encode($result); exit();
	}
	$result["url_sorteo"] = "";
	if($registroSIC){

		$id_cliente = $list_4[0]["id"];
		$codigo_sorteo = "";
		$tipoDocSIC = $tipo_doc === "0" ? "DNI" : ($tipo_doc === "1" ? "CEX" : "PAS");
		$json_array = [
			"domain_id" => 0,
			"nacionalidad" => $nacionalidad,
			"tipo_documento" => $tipoDocSIC,
			"numero_documento" => $num_doc,
			"telefono" => $celular,
			"email" => $correo,
			"nombres" => $nombre,
			"apellido_paterno" => $apepaterno,
			"apellido_materno" => $apematerno,
			"fecha_nacimiento" => $fec_nac,
			"direccion" => $direccion,
			"sms_codigo" => "",
			"ubigeo" => $ubigeo,
			"xparameter" => "TELESERVICIOS",
			"politicamente_expuesto" => $pep  == "1" ? 1 : 0,
			"terminos_condiciones" => $tyc == "true" ? 1 : 0,
		];

		// Validar y Registrar en api SIC - TELESERVICIO
		$clientSIC = api_get_sic_findClient($tipo_doc, $num_doc, env('SIC_DOMAIN_ID_TELESERVICIO'),$id_cliente);
		//$result["clientSIC"] = $clientSIC;
		// if (($tipo_doc == "0" && $clientSIC["http_code"] === 200 && $clientSIC["status"] === "true") || ($tipo_doc == "1" && $clientSIC["http_code"] === 200)) {
		if ( $clientSIC["http_code"] === 200) {
			// if ((($clientSIC["origin"] === "100" && $tipo_doc == "0") ||  ($clientSIC["origin"] === "0" && $tipo_doc == "1"))) {
			if ($clientSIC["origin"] === "100" && $clientSIC["status"] === "true") {
					// actualizar con el api de vibra
					if($clientSIC["to_update"]==true){
						
						$json_array["domain_id"] = env('SIC_DOMAIN_ID_TELESERVICIO');
					
						$playerId = $clientSIC["result"]["player_id"];
						$to_update_string = $clientSIC["to_update"];
						$xstring = "TELESERVICIOS";
	
						$json_array["player_id"] = $playerId;
						$json_array["to_update_string"] = $to_update_string ? 'true' : 'false';
						$json_array["xstring"] = $xstring;					
	
						$clientSIC_Register = api_post_sic_editClient($json_array, $id_cliente);
	
						if ($clientSIC_Register["http_code"] == 204 && $clientSIC_Register["status"] == "true") {
							$result["http_code"] = 200;
							$result["status"] = "Registro exitoso en SIC.";
						} else {
							$result["status"] = "Error al editar los datos del cliente en SIC Teleservicio.";
							$result["messageApi"] = $clientSIC_Register;
							$result["jsonApi"] = $json_array;
							
						}
					}
					
					
			} else if ( 
						($tipo_doc == "0" && $clientSIC["status"] === "true" && ($clientSIC["origin"] === "105" || $clientSIC["origin"] === "107" || $clientSIC["origin"] === "109")) ||
						(($tipo_doc == "1" || $tipo_doc == "2" ) && $clientSIC["status"] === "true" && ($clientSIC["origin"] === "105" || $clientSIC["origin"] === "107")) ||
						(($tipo_doc == "1" || $tipo_doc == "2" ) && $clientSIC["status"] === "false" && $clientSIC["origin"] === "")
						) {
				// registrar con el api de SIC
				$json_array["domain_id"] = env('SIC_DOMAIN_ID_TELESERVICIO');
				//$result["json_array_tls"] = $json_array;
				$clientSIC_Register = api_post_sic_registerClient($json_array, $id_cliente);
				//$result_["clientSIC_Register"] = $clientSIC_Register;
				// if ($clientSIC_Register["http_code"] !== 200) {
				// 	$result["status"] = "Error al registar cliente en SIC Teleservicio.";
				// } else {
				// 	$result["http_code"] = 200;
				// 	$result["status"] = "Registro exitoso en SIC.";
				// 	if(isset($clientSIC_Register["codigo_sorteo"]) && $clientSIC_Register["codigo_sorteo"] != ""){
				// 		$codigo_sorteo = $clientSIC_Register["codigo_sorteo"];
				// 		$url_sorteo = env('SORTEOS_URL') . '/ruleta_v2/' . $codigo_sorteo;
				// 		$result["url_sorteo"] = $url_sorteo;
				// 	}
				// }

				if ($clientSIC_Register["http_code"] == 200 && $clientSIC_Register["status"] == "OK") {
					$result["http_code"] = 200;
					$result["status"] = "Registro exitoso en SIC.";
					if(isset($clientSIC_Register["codigo_sorteo"]) && $clientSIC_Register["codigo_sorteo"] != ""){
						$codigo_sorteo = $clientSIC_Register["codigo_sorteo"];
						$url_sorteo = env('SORTEOS_URL') . '/ruleta_v2/' . $codigo_sorteo;
						$result["url_sorteo"] = $url_sorteo;
					}
				} else {
					$result["status"] = "Error al registar cliente en SIC Teleservicio.";
					
				}
			}
		}
		else {
			$result["status"] = "Error al consultar cliente en SIC Teleservicio.";
		}

		if($codigo_sorteo != ""){
			//Insertar codigo sorteo cliente
			$query_insertar_premio = "
				INSERT INTO tbl_televentas_clientes_premios (
						cliente_id,
						codigo,
						type,
						status,
						user_id,
						created_at
				) VALUES (
						'" . $id_cliente . "',
						'" . $codigo_sorteo . "',
						'sorteo_registro_clientes_retail',
						0,
						'" . $usuario_id . "',
						now()
				);
				";
			$mysqli->query($query_insertar_premio);
			if ($mysqli->error) {
				$result["http_code"] = 400;
				$result["status"] = "Error al registrar el codigo de premio del cliente.";
			}
		}

		/*$clientSIC_Find = api_get_sic_findClient($tipo_doc, $num_doc, env('SIC_DOMAIN_ID_RETAIL'));
		$result["clientSIC_Find"] = $clientSIC_Find;
		if ($clientSIC_Find["http_code"] === 200 && $clientSIC_Find["status"] === "true") {
			if ($clientSIC_Find["origin"] === "100") {
				// actualizar
			}else{
				$json_array["domain_id"] = env('SIC_DOMAIN_ID_RETAIL');
				$clientSIC_Register2 = api_post_sic_registerClient($json_array);
				$result["clientSIC_Register2"] = $clientSIC_Register2;

				if ($clientSIC_Register2["http_code"] !== 200) {
					$result["http_code"] = $clientSIC_Register2["http_code"];
					$result["status"] = "Error al registrar cliente en SIC Retail.";
				} else {
					$result["http_code"] = 200;
					$result["status"] = "Registro exitoso en SIC.";
				}
			}
		} else {
			$result["http_code"] = $clientSIC_Find["http_code"];
			$result["status"] = "Error al consultar cliente en SIC Retail.";
		}*/

	}

	
}



//*******************************************************************************************************************
// FUSIONAR CLIENTES
//*******************************************************************************************************************
 if ($_POST["accion"] === "fusionar_clientes") {

	$clientes = $_POST["clientes"];
	$id_cliente = $_POST["id_cliente"];
	$tipo_doc_s = '';
	$num_doc_s = '';
	$cliente_s = '';
	$correo_s = '';
	$telefono_s = '';
	$web_id_s = '';
	$player_id_s = '';
	$full_name_s = '';
	$transa_s = '';
	$balance_s = '';
	$tipo_doc_f = '';
	$num_doc_f = '';
	$cliente_f = '';
	$correo_f = '';
	$telefono_f = '';
	$web_id_f = '';
	$player_id_f = '';
	$full_name_f = '';
	$transa_f = 0;
	$balance_f = 0;
 	$listado_f = '';

	if (!empty($clientes)) {

		foreach($clientes as $cliente){		

			foreach($cliente as $posicion=>$datos){
				
				if($posicion=='id' and $datos == $id_cliente){		
					$num_doc_s = $cliente["num_doc"];
					$tipo_doc_s = $cliente["tipo_doc"];	
					$cliente_s = $cliente["cliente"];
					$correo_s = $cliente["correo"];			 
					$telefono_s = $cliente["telefono"];
					$web_id_s = $cliente["web_id"];
					$player_id_s = $cliente["player_id"];
					$full_name_s = $cliente["web_full_name"];
					$transa_s = $cliente["total_tran"];
					$balance_s = $cliente["total_balance"];					
				}
				
				if($posicion=='id' and $datos <> $id_cliente){
					if (empty($num_doc_f)) {
						$num_doc_f =  $cliente["num_doc"];
					}				
					
					if (empty($cliente_f)) {
						$cliente_f = $cliente["cliente"];
					}

					if (empty($correo_f)) {
						$correo_f = $cliente["correo"];
					}

					if (empty($telefono_f)) {
						$telefono_f = $cliente["telefono"];
					}
					if (empty($web_id_f)) {
						$web_id_f = $cliente["web_id"];
					}
					if (empty($player_id_f)) {
						$player_id_f = $cliente["player_id"];
					}						
					if (empty($full_name_f)) {
						$full_name_f = $cliente["web_full_name"];
					}
				 
					$transa_f = number_format(($cliente["total_tran"] + $transa_f), 2, '.', '');
					$balance_f = number_format(($cliente["total_balance"] + $balance_f), 2, '.', '');			
				}
			}
		}

		if (empty($num_doc_s)) {
			$num_doc_s = $num_doc_f;
		}

	
		

		if (empty($cliente_s)) {
			$cliente_s = $cliente_f;	
		}

		if (empty($correo_s)) {
			$correo_s = $correo_f;	
		}

		if (empty($telefono_s)) {
			$telefono_s = $telefono_f;	
		}
		if (empty($web_id_s)) {
			$web_id_s = $web_id_f;	
		}
		if (empty($player_id_s)) {
			$player_id_s = $player_id_f;	
		}
		if (empty($full_name_s)) {
			$full_name_s = $full_name_f;	
		}

		$transa_s = number_format(($transa_f + $transa_s), 0, '.', '');	
		$balance_s = number_format(($balance_f + $balance_s), 2, '.', '');	
		$listado_f = array($id_cliente, $tipo_doc_s, $num_doc_s, $telefono_s, $cliente_s, $player_id_s, $web_id_s, $full_name_s, $transa_s,  $balance_s, $correo_s);

		if (count($listado_f) <> 0) {
			$result["http_code"] = 200;
			$result["status"] = "ok";
			$result["result"] = $listado_f;
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al fusionar clientes.";
			$result["result"] = $listado_f;
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Sesión perdida.";
	}

 
}

//*******************************************************************************************************************
// VERIFICAR DATOS AL FUSIONAR CLIENTES
//*******************************************************************************************************************
if ($_POST["accion"] === "verificar_datos_fusion_clientes") {
	$list_cli_dup = $_POST["list_cli_dup"];
	$cli_dup_select = $_POST["cli_dup_select"];
	$tipo_doc_s = $_POST["tipo_doc_s"];
	$num_doc_s = $_POST["num_doc_s"];
	$telefono_s = $_POST["telefono_s"];
	$cliente_s = $_POST["cliente_s"];
	$correo_s = $_POST["correo_s"];
	$player_id_s = $_POST["player_id_s"];
	$web_id_s = $_POST["web_id_s"];
	$web_name_s = $_POST["web_name_s"];
 
	$res_id1 = array();
	$res_id2 = array();
	if (!empty($list_cli_dup)) {
		 
		$where_idweb='';
		$where_celular='';
		$where_id_jugador='';
		$where_correo='';
	 
		 
		if(!Empty($web_id_s)){		 
			$where_idweb = " OR c.web_id ='" . $web_id_s . "'";	 
		}
		if(!Empty($telefono_s)){		 
			$where_celular = " OR c.telefono ='" . $telefono_s . "'";
		}
		if(!Empty($player_id_s)){		 
			$where_id_jugador = " OR c.player_id='" . $player_id_s . "'";	
		}

		if(!Empty($correo_s)){		 
			$where_correo = " OR c.correo='" . $correo_s . "'";	
		}
	
			$query_1 = "
			SELECT 
			c.id, 		
			IFNULL(c.num_doc, '') num_doc,
			IFNULL(c.telefono, '') telefono,
			IFNULL(c.web_id, '') web_id,
			IFNULL(c.player_id, '') player_id,
			IFNULL(c.web_full_name, '') web_full_name,
			CONCAT(IFNULL(c.nombre, ''), ' ', IFNULL(c.apellido_paterno, ''), ' ', IFNULL(c.apellido_materno, '')) AS cliente 
	
				FROM 
					tbl_televentas_clientes c
				WHERE
				(c.num_doc = '" . $num_doc_s . "' 
				" . $where_idweb . "
				" . $where_celular . "
				" . $where_id_jugador . "
				" . $where_correo . "
			  )
		";

		$list_query_1 = $mysqli->query($query_1);
		$list_1 = array();
		while ($li_1 = $list_query_1->fetch_assoc()) {

		$list_1[] = $li_1;
		} 
		
		foreach($list_1 as $clientes){					
			$res_id1[] = $clientes['id'];			 
		}
		foreach($list_cli_dup as $cliented){	
			$res_id2[] = $cliented['id'];			
		}

		$resultados_c = array_diff($res_id1, $res_id2);

		if (count($resultados_c) <> 0) {
			$result["http_code"] = 400;
			$result["status"] = "Cliente duplicado"; 
			$result["result"] = $list_1;
		} else {
			$result["http_code"] = 200;
			$result["status"] = "ok";
			$result["result"] = $list_cli_dup;
		}

	}
	
}


//*******************************************************************************************************************
// GUARDAR FUSIONAR CLIENTES
//*******************************************************************************************************************
if ($_POST["accion"] === "guardar_fusion_clientes") {

	$usuario_nombre = $login ? $login['usuario'] : '';
	$list_cli_dup = $_POST["list_cli_dup"];
	$cli_dup_select = $_POST["cli_dup_select"];
	$tipo_doc_s = $_POST["tipo_doc_s"];
	$num_doc_s = $_POST["num_doc_s"];
	$telefono_s = $_POST["telefono_s"];
	$cliente_s = $_POST["cliente_s"];
	$correo_s = $_POST["correo_s"];
	$player_id_s = $_POST["player_id_s"];
	$web_id_s = $_POST["web_id_s"];
	$web_name_s = $_POST["web_name_s"];
	$fec_nac_s = $_POST["fec_nac_s"];
	$balance_1_s = 0;
	$balance_2_s = 0;
	$balance_3_s = 0;
	$balance_4_s = 0;
	$balance_5_s = 0;
	$balance_6_s = 0;
 
	$transa_s = '';
	$balance_s = '';
	$tipo_doc_f = '';
	$num_doc_f = '';
	$cliente_f = '';
	$correo_f = '';
	$telefono_f = '';
	$web_id_f = '';
	$player_id_f = '';
	$full_name_f = '';
	$transa_f = 0;
	$balance_f = 0;

	$balance_1_f = 0;
	$balance_2_f = 0;
	$balance_3_f = 0;
	$balance_4_f = 0;
	$balance_5_f = 0;
	$balance_6_f = 0;

 	$listado_f = '';

	if (!empty($list_cli_dup)) {

		foreach($list_cli_dup as $cliente){		
				
			if($cliente['id'] <> $cli_dup_select["0"]){
				if (empty($num_doc_f)) {
					$num_doc_f =  $cliente["num_doc"];
				}
				if (empty($tipo_doc_f)) {
					$tipo_doc_f = intval($cliente["tipo_doc"]);
				}
				if (empty($cliente_f)) {
					$cliente_f = $cliente["cliente"];
				}
				if (empty($correo_f)) {
					$correo_f = $cliente["correo"];
				}
				if (empty($telefono_f)) {
					$telefono_f = $cliente["telefono"];
				}
				if (empty($web_id_f)) {
					$web_id_f = $cliente["web_id"];
				}
				if (empty($player_id_f)) {
					$player_id_f = $cliente["player_id"];
				}						
				if (empty($full_name_f)) {
					$full_name_f = $cliente["web_full_name"];
				}
				 
				$transa_f = number_format(($cliente["total_tran"] + $transa_f), 2, '.', '');
				$balance_f = number_format(($cliente["total_balance"] + $balance_f), 2, '.', '');						
					
				$query_bloqueo_f ="UPDATE tbl_televentas_clientes SET block_user_id = 0 WHERE id = '" . $cliente["id"] . "'  ";

				$mysqli->query($query_bloqueo_f);
				if ($mysqli->error) {
					$result["query_bloqueo_cli_error"] = $mysqli->error;
				}

				$query_balance_f = array();
				$query_balance_f = obtener_balances($cliente["id"]);
				
				if(count($query_balance_f)>0){

					$balance_1_f = number_format(((double)$query_balance_f[0]["balance"] + $balance_1_f), 2, '.', '');	
					$balance_2_f = number_format(((double)$query_balance_f[0]["balance_bono_disponible"] + $balance_2_f), 2, '.', '');	
					$balance_3_f = number_format(((double)$query_balance_f[0]["balance_bono_utilizado"] + $balance_3_f), 2, '.', '');	
					$balance_4_f = number_format(((double)$query_balance_f[0]["balance_deposito"] + $balance_4_f), 2, '.', '');	
					$balance_5_f = number_format(((double)$query_balance_f[0]["balance_retiro_disponible"] + $balance_5_f), 2, '.', '');	
					$balance_6_f = number_format(((double)$query_balance_f[0]["balance_dinero_at"] + $balance_6_f), 2, '.', '');

					$insert_hist_balance1= "INSERT INTO tbl_televentas_log_fusiones_cli_bal
						(`cliente_id`,`tipo_balance_id`,`balance`,`created_at`) 
						VALUES ('" . $cliente["id"]. "','1','" .(double)$query_balance_f[0]["balance"] . "',now())";

					$mysqli->query($insert_hist_balance1);

					if ($mysqli->error) {
						$result["insert_log_cli_bal_1"] = $mysqli->error;
					}

					$insert_hist_balance2= "INSERT INTO tbl_televentas_log_fusiones_cli_bal
						(`cliente_id`,`tipo_balance_id`,`balance`,`created_at`) 
						VALUES ('" . $cliente["id"]. "','2','" .(double)$query_balance_f[0]["balance_bono_disponible"] . "',now())";

					$mysqli->query($insert_hist_balance2);

					if ($mysqli->error) {
						$result["insert_log_cli_bal_2"] = $mysqli->error;
					}

					$insert_hist_balance3= "INSERT INTO tbl_televentas_log_fusiones_cli_bal
					(`cliente_id`,`tipo_balance_id`,`balance`,`created_at`) 
					VALUES ('" . $cliente["id"]. "','3','" .(double)$query_balance_f[0]["balance_bono_utilizado"] . "',now())";

					$mysqli->query($insert_hist_balance3);

					if ($mysqli->error) {
						$result["insert_log_cli_bal_3"] = $mysqli->error;
					}

					$insert_hist_balance4= "INSERT INTO tbl_televentas_log_fusiones_cli_bal
						(`cliente_id`,`tipo_balance_id`,`balance`,`created_at`) 
						VALUES ('" . $cliente["id"]. "','4','" .(double)$query_balance_f[0]["balance_deposito"] . "',now())";

					$mysqli->query($insert_hist_balance4);

					if ($mysqli->error) {
						$result["insert_log_cli_bal_4"] = $mysqli->error;
					}

					$insert_hist_balance5= "INSERT INTO tbl_televentas_log_fusiones_cli_bal
						(`cliente_id`,`tipo_balance_id`,`balance`,`created_at`) 
						VALUES ('" . $cliente["id"]. "','5','" .(double)$query_balance_f[0]["balance_retiro_disponible"] . "',now())";

					$mysqli->query($insert_hist_balance5);

					if ($mysqli->error) {
						$result["insert_log_cli_bal_5"] = $mysqli->error;
					}

					$insert_hist_balance6= "INSERT INTO tbl_televentas_log_fusiones_cli_bal
						(`cliente_id`,`tipo_balance_id`,`balance`,`created_at`) 
						VALUES ('" . $cliente["id"]. "','6','" .(double)$query_balance_f[0]["balance_dinero_at"] . "',now())";

					$mysqli->query($insert_hist_balance6);

					if ($mysqli->error) {
						$result["insert_log_cli_bal_6"] = $mysqli->error;
					}

				}
			}

			if($cliente['id'] == $cli_dup_select["0"]){	
					
				if (empty($tipo_doc_s)) {
					$tipo_doc_s = intval($cliente["tipo_doc"]);
				}
				if (empty($num_doc_s)) {
					$num_doc_s = $cliente["num_doc"];
				}
				if (empty($cliente_s)) {
					$cliente_s = $cliente["cliente"];
				}
				if (empty($correo_s)) {
					$correo_s = $cliente["correo"];
				}
				if (empty($telefono_s)) {
					$telefono_s = $cliente["telefono"];
				}
				if (empty($web_id_s)) {
					$web_id_s = $cliente["web_id"];
				}
				if (empty($player_id_s)) {
					$player_id_s = $cliente["player_id"];
				}
				if (empty($web_name_s)) {
					$web_name_s = $cliente["web_full_name"];
				}				 
				
				$transa_s = $cliente["total_tran"];
				$balance_s = $cliente["total_balance"];	
					
				$query_bloqueo_f ="UPDATE tbl_televentas_clientes SET block_user_id = 0 WHERE id = '" . $cli_dup_select["0"] . "'  ";

				$mysqli->query($query_bloqueo_f);
				
				if ($mysqli->error) {
					$result["query_bloqueo_cli_error"] = $mysqli->error;
				}

				$query_balance_f = array();
				$query_balance_f = obtener_balances($cliente["id"]);

				if(count($query_balance_f)>0){

					$balance_1_s = (double)$query_balance_f[0]["balance"];	
					$balance_2_s = (double)$query_balance_f[0]["balance_bono_disponible"];	
					$balance_3_s = (double)$query_balance_f[0]["balance_bono_utilizado"];	
					$balance_4_s = (double)$query_balance_f[0]["balance_deposito"];	
					$balance_5_s = (double)$query_balance_f[0]["balance_retiro_disponible"];	
					$balance_6_s = (double)$query_balance_f[0]["balance_dinero_at"];
				}

			}	
			
		}

		if (empty($num_doc_s)) {
			$num_doc_s = $num_doc_f;
		}
	 
		if (empty($cliente_s)) {
			$cliente_s = $cliente_f;	
		}
		if (empty($correo_s)) {
			$correo_s = $correo_f;	
		}
		if (empty($telefono_s)) {
			$telefono_s = $telefono_f;	
		}
		if (empty($web_id_s)) {
			$web_id_s = $web_id_f;	
		}
		if (empty($player_id_s)) {
			$player_id_s = $player_id_f;	
		}
		if (empty($web_name_s)) {
			$web_name_s = $full_name_f;	
		}

		$transa_s = number_format(($transa_f + $transa_s), 0, '.', '');	
		$balance_s = number_format(($balance_f + $balance_s), 2, '.', '');
		
		$balance_1_s =number_format(($balance_1_f + $balance_1_s), 2, '.', '');
		$balance_2_s =number_format(($balance_2_f + $balance_2_s), 2, '.', '');
		$balance_3_s =number_format(($balance_3_f + $balance_3_s), 2, '.', '');
		$balance_4_s =number_format(($balance_4_f + $balance_4_s), 2, '.', '');
		$balance_5_s =number_format(($balance_5_f + $balance_5_s), 2, '.', '');
		$balance_6_s =number_format(($balance_6_f + $balance_6_s), 2, '.', '');


		$query_balance1_s ="
		UPDATE tbl_televentas_clientes_balance 
		SET balance = '".$balance_1_s."' 
		WHERE cliente_id = '".$cli_dup_select["0"]."' 
		AND tipo_balance_id = '1' ";
		$mysqli->query($query_balance1_s);

		$query_balance2_s ="
		UPDATE tbl_televentas_clientes_balance 
		SET balance = '".$balance_2_s."' 
		WHERE cliente_id = '".$cli_dup_select["0"]."' 
		AND tipo_balance_id = '2' ";
		$mysqli->query($query_balance2_s);

		$query_balance3_s ="
		UPDATE tbl_televentas_clientes_balance 
		SET balance = '".$balance_3_s."' 
		WHERE cliente_id = '".$cli_dup_select["0"]."' 
		AND tipo_balance_id = '3' ";
		$mysqli->query($query_balance3_s);

		$query_balance4_s ="
		UPDATE tbl_televentas_clientes_balance 
		SET balance = '".$balance_4_s."' 
		WHERE cliente_id = '".$cli_dup_select["0"]."' 
		AND tipo_balance_id = '4' ";
		$mysqli->query($query_balance4_s);

		$query_balance5_s ="
		UPDATE tbl_televentas_clientes_balance 
		SET balance = '".$balance_5_s."' 
		WHERE cliente_id = '".$cli_dup_select["0"]."' 
		AND tipo_balance_id = '5' ";
		$mysqli->query($query_balance5_s);

		$query_balance6_s ="
		UPDATE tbl_televentas_clientes_balance 
		SET balance = '".$balance_6_s."' 
		WHERE cliente_id = '".$cli_dup_select["0"]."' 
		AND tipo_balance_id = '6' ";
		$mysqli->query($query_balance6_s);

		if ($mysqli->error) {
			$result["query_balance_s_error"] = $mysqli->error;
		}

 
		foreach($list_cli_dup as $cliente){	
			
			$insert_1 = " 
				INSERT INTO tbl_televentas_log_fusiones
					(
					`usuario_id`,
					`cliente_id_f`,
					`tipo_doc_f`,
					`num_doc_f`,
					`telefono_f`,
					`cliente_f`,
					`correo_f`,
					`player_id_f`,
					`web_id_f`,
					`web_full_name_f`,
					`transac_f`,
					`balance_f`,
					`cliente_id_s`,
					`created_at`
					 				
					) VALUES (
					'" . $usuario_id . "',
					'" . $cliente['id']. "',
					'" . $cliente['tipo_doc'] . "',
					'" . $cliente['num_doc'] . "',
					'" . $cliente['telefono'] . "',
					'" . $cliente['cliente'] . "',
					'" . $cliente['correo'] . "',
					'" . $cliente['player_id'] . "',
					'" . $cliente['web_id'] . "',
					'" . $cliente['web_full_name'] . "',
					'" . $cliente['total_tran'] . "',
					'" . $cliente['total_balance'] . "',
					'" . $cli_dup_select["0"]. "',
					now()
				
					)";
			
			$mysqli->query($insert_1);

			if ($mysqli->error) {
				$result["insert_1_error"] = $mysqli->error;
			}

			if($cliente['id'] <> $cli_dup_select["0"]){

				$query_borrado_c ="
				UPDATE tbl_televentas_clientes 
				SET num_doc = null,
				web_id = null,
				player_id = null,				
				telefono = null,
				correo = null,
				web_full_name = '' 

				WHERE id = '".$cliente['id']."'  ";
				$mysqli->query($query_borrado_c);
				if ($mysqli->error) {
					$result["query_borrado_c_error"] = $mysqli->error;
				}

				$query_borrado_b ="
				UPDATE tbl_televentas_clientes_balance 
				SET balance = 0
				WHERE cliente_id = '".$cliente['id']."'  ";
				$mysqli->query($query_borrado_b);
				if ($mysqli->error) {
					$result["query_borrado_b_error"] = $mysqli->error;
				}

				$query_id_b_tr ="SELECT id FROM tbl_televentas_clientes_balance_transaccion
				WHERE cliente_id = '".$cliente['id']."'  ";

				$list_id_b_tr = $mysqli->query($query_id_b_tr);
				$list_b_tr = array();
				while ($li_id_b_tr = $list_id_b_tr->fetch_assoc()) {

				$list_b_tr[] = $li_id_b_tr;
				}

				foreach($list_b_tr as $list_ids){

					$insert_cli_b_tra= " 
						INSERT INTO tbl_televentas_log_fusiones_cli_bal_tra
						(`id_cli_bal_tra`,`cliente_id`, `created_at`) 
							VALUES 
							('" . $list_ids['id']. "','" .$cliente['id'] . "',now())";

					$mysqli->query($insert_cli_b_tra);

					if ($mysqli->error) {
						$result["insert_log_cli_bal_tra"] = $mysqli->error;
					}
				}

				$query_borrado_h ="
				UPDATE tbl_televentas_clientes_balance_transaccion 
				SET cliente_id = '".$cli_dup_select["0"]."'
				WHERE cliente_id = '".$cliente['id']."'  ";
				$mysqli->query($query_borrado_h);
				if ($mysqli->error) {
					$result["query_borrado_h_error"] = $mysqli->error;
				}

				$query_id_tra ="SELECT id, web_id FROM tbl_televentas_clientes_transaccion
				WHERE cliente_id = '".$cliente['id']."'  ";

				$list_id_tra = $mysqli->query($query_id_tra);
				$list_tra = array();
				while ($li_id_tra = $list_id_tra->fetch_assoc()) {

				$list_tra[] = $li_id_tra;
				}

				foreach($list_tra as $list_ids_tra){

					$insert_cli_tra= " 
						INSERT INTO tbl_televentas_log_fusiones_cli_tra
							(`id_cli_tra`,`cliente_id`, `web_id`, `created_at`) 
							VALUES 
							('" . $list_ids_tra['id']. "','" .$cliente['id'] . "', '" .$list_ids_tra['web_id'] . "', now())";

					$mysqli->query($insert_cli_tra);

					if ($mysqli->error) {
						$result["insert_log_cli_tra"] = $mysqli->error;
					}
				}

				$query_borrado_tr ="
				UPDATE tbl_televentas_clientes_transaccion 
				SET cliente_id = '".$cli_dup_select["0"]."',
				web_id = '".$web_id_s."'
				WHERE cliente_id = '".$cliente['id']."'  ";
				$mysqli->query($query_borrado_tr);
				if ($mysqli->error) {
					$result["query_borrado_tr_error"] = $mysqli->error;
				}
			}					
  
		}

		$nombre_apellido = explode(" ", $cliente_s);
		$nombres = '';
		$apellido1 = '';
		$apellido2 = '';

		if(array_key_exists(0, $nombre_apellido)){
			if(array_key_exists(1, $nombre_apellido)){
				$nombres = strtoupper($nombre_apellido["0"].' '.$nombre_apellido["1"]);
			}else{
				$nombres = strtoupper($nombre_apellido["0"]);
				if(array_key_exists(1, $nombre_apellido)){
					$apellido1 =strtoupper($nombre_apellido["1"]);
				}
				if(array_key_exists(2, $nombre_apellido)){
					$apellido2 =strtoupper($nombre_apellido["2"]);
				}
			}
			if(array_key_exists(2, $nombre_apellido)){
				$apellido1 =strtoupper($nombre_apellido["2"]);
			}
			if(array_key_exists(3, $nombre_apellido)){
				$apellido2 =strtoupper($nombre_apellido["3"]);
			}
		}
		$set_telefono='';
		$set_web_id='';
		$set_fec_nac='';
		$set_correo='';
		$set_player_id_s='';

		if($telefono_s>0){		 
			$set_telefono = "telefono='" . $telefono_s . "',";
		}		
		if($web_id_s>0){	 
			$set_web_id = "web_id='" . $web_id_s . "',";
		}	
		
		if (!empty($fec_nac_s)) {	 
			$set_fec_nac = "fec_nac='" . $fec_nac_s . "',";
		}
		if (!empty($correo_s)) {	 
			$set_correo = "correo='" . $correo_s . "',";
		}
		if (!empty($player_id_s)) {	 
			$set_player_id_s = "player_id = '".$player_id_s."',";
		}

		$query_fusion_c ="
				UPDATE tbl_televentas_clientes 
				SET num_doc = '".$num_doc_s."',
				tipo_doc = ".$tipo_doc_s.",
				" . $set_web_id . "
				" . $set_player_id_s . " 
				nombre = '".$nombres."',			 
				apellido_paterno = '".$apellido1."',
				apellido_materno = '".$apellido2."',
				" . $set_telefono . " 
				" . $set_fec_nac . " 
				" . $set_correo . " 
				web_full_name = '".$web_name_s."' 		  
			 
				WHERE id = '".$cli_dup_select["0"]."'  ";
		
		$mysqli->query($query_fusion_c);
		
		if ($mysqli->error) {
			$result["query_fusion_c_error"] = $mysqli->error;
		}

		//HISTORIAL DE CAMBIOS FUSION CLIENTE

		$insert_cli_f_h = " 
			INSERT INTO tbl_televentas_log_fusiones
				(
				`usuario_id`,
				`cliente_id_f`,
				`tipo_doc_f`,
				`num_doc_f`,
				`telefono_f`,
				`cliente_f`,
				`correo_f`,
				`player_id_f`,
				`web_id_f`,
				`web_full_name_f`,
				`transac_f`,
				`balance_f`,
				`cliente_id_s`,
				`result`,
				`created_at`

				) VALUES (
				'" . $usuario_id . "',
				'" . $cli_dup_select["0"]. "',
				'" . $tipo_doc_s . "',
				'" . $num_doc_s . "',
				'" . $telefono_s . "',
				'" . $cliente_s . "',
				'" . $correo_s . "',
				'" . $player_id_s . "',
				'" . $web_id_s . "',
				'" . $web_name_s . "',
				'" . $transa_s . "',
				'" . $balance_s . "',
				'" . $cli_dup_select["0"]. "',
				1,
				now()

				)";

		$mysqli->query($insert_cli_f_h);

		if ($mysqli->error) {
			$result["insert_cli_f_h_error"] = $mysqli->error;
		}

		//HISTORIAL DE CAMBIOS CLIENTE

		$insert_hist_cli_f= " 
			INSERT INTO tbl_televentas_log_clientes
			(`origen_cambio`, `cliente_id`, `tipo_doc`, `num_doc`, `web_id`, `player_id`, `telefono`, `nombre`, `correo`, `apellido_paterno`, `apellido_materno`, `web_full_name`, `fec_nac`, `usuario_id`, `created_at`)
			VALUES 
			( 1,'" . $cli_dup_select["0"] . "','" . $tipo_doc_s . "', '" . $num_doc_s . "', '" . trim($web_id_s) . "', '" . trim($player_id_s) . "', '" . trim($telefono_s) . "', '" . $nombres . "', '" . trim($correo_s) . "', '" . $apellido1 . "',  '" . $apellido2 . "', '" . $web_name_s . "',  '" . trim($fec_nac_s) . "', '" . $usuario_id . "', now())";

		$mysqli->query($insert_hist_cli_f);

		if ($mysqli->error) {
			$result["insert_log_clientes_f_error"] = $mysqli->error;
		}

		if (count($list_cli_dup) <> 0) {

			send_mail_alert_fusion_clientes($list_cli_dup, $cli_dup_select, $num_doc_s, $tipo_doc_s, $web_id_s, $telefono_s, $nombres, $apellido1, $apellido2, $usuario_nombre, $correo_s,$transa_s,$balance_s);

			$result["http_code"] = 200;
			$result["status"] = "ok";
			$result["result"] = $list_cli_dup;
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al fusionar clientes.";
			$result["result"] = $list_cli_dup;
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Sesión perdida.";
	}

}



//CORREO ALERTA FUSION DE CLIENTES

function send_mail_alert_fusion_clientes($list_cli_dup, $cli_dup_select, $num_doc_s, $tipo_doc_s, $web_id_s, $telefono_s, $nombres, $apellido1, $apellido2, $usuario_nombre, $correo_s, $transa_s,$balance_s){

	//$lista_correos = array("jacqueline.santiago@testtest.apuestatotal.com", "noexisteelcorreo@testtest.apuestatotal.com", "angie.flores@testtest.apuestatotal.com");

	$lista_correos = array("kevin.tafur@testtest.apuestatotal.net", "ricardo.tafur@testtest.apuestatotal.net", "cris.quispe@testtest.apuestatotal.net", "pablo.melgar@testtest.apuestatotal.net", "tv.tasayco@testtest.apuestatotal.net", "tahiry.reyes@testtest.apuestatotal.net");
	$cant_correos = count($lista_correos);

	for($i=0; $i<$cant_correos; $i++){    

		$tipo_doc_n = '';
		if($tipo_doc_s==0){
			$tipo_doc_n = 'DNI';
		}else if($tipo_doc_s==1){
			$tipo_doc_n = 'CE/PTP';
		}else{
			$tipo_doc_n = 'PASAPORTE';
		}


		$body = "Se realizó una fusión de datos al cliente <b>".$nombres." ".$apellido1." ".$apellido2. "<b><br>" .		 
				"Tipo de Documento: " . $tipo_doc_n . " <br>" .
				"Número de Documento: " . $num_doc_s . " <br>" .
				"Cliente: " . $nombres . " " . $apellido1 . " " . $apellido2 . " <br>" .
				"Usuario que modifica: " . $usuario_nombre. " <br>" .
				"<br><br>" .
				"<table border='1' cellpadding='5' cellspacing='0'>" .
				"	<thead>" .
				"  <tr> " .
				"    <th style='background: #1a73e8;color: white;'>TIPO DOC</th> " .
				"    <th style='background: #1a73e8;color: white;'>NUM DOC</th> " .
				"    <th style='background: #1a73e8;color: white;'>CELULAR</th> " .
				"    <th style='background: #1a73e8;color: white;'>CLIENTE</th> " .
				"    <th style='background: #1a73e8;color: white;'>CORREO</th> " .
				"    <th style='background: #1a73e8;color: white;'>NUM TRANSAC</th> " .
				"    <th style='background: #1a73e8;color: white;'>BALANCE</th> " .
				"   </tr> " .
				" </thead> " .
				" <tbody> ";

				foreach($list_cli_dup as $cliente){		
					$tipo_doc_nomb = '';
					if($cliente['tipo_doc']==0){
						$tipo_doc_nomb = 'DNI';
					}else if($cliente['tipo_doc']==1){
						$tipo_doc_nomb = 'CE/PTP';
					}else{
						$tipo_doc_nomb = 'PASAPORTE';
					}


					$body .=	"   <tr> " .
					"     <td style='font-weight: 500;'>".$tipo_doc_nomb."</td> " .
					"     <td style='font-weight: 500;'>".$cliente['num_doc']."</td> " .
					"     <td style='font-weight: 500;'>".$cliente['telefono']."</td> " .
					"     <td style='font-weight: 500;'>".$cliente['cliente']."</td> " .
					"     <td style='font-weight: 500;'>".$cliente['correo']."</td> " .
					"     <td style='font-weight: 500;'>".$cliente['total_tran']."</td> " .
					"     <td style='font-weight: 500;'>".$cliente['total_balance']."</td> " .
					"   </tr> ";

				}

				$body .=" </tbody> " .
						" <tfoot> " .
						"<tr style='background: #6bed86cc;'>" .
						"<td align='center' colspan='7'>RESULTADO FUSIÓN</td>" .
						"</tr>" .
						"<tr>" .
						"     <td style='font-weight: 500;'>".$tipo_doc_n."</td> " .
						"     <td style='font-weight: 500;'>".$num_doc_s."</td> " .
						"     <td style='font-weight: 500;'>".$telefono_s."</td> " .
						"     <td style='font-weight: 500;'>".$nombres." ".$apellido1." ".$apellido2."</td> " .
						"     <td style='font-weight: 500;'>".$correo_s."</td> " .
						"     <td style='font-weight: 500;'>".$transa_s."</td> " .
						"     <td style='font-weight: 500;'>".$balance_s."</td> " .
						"   </tr> ".
						" </tfoot> " .
				"</table>";

		$cc = [
			//"bladimir.quispe@testtest.kurax.dev" 
			$lista_correos[$i]

		];

		$bcc = [
		//  "lila.melendez@testtest.apuestatotal.com",
		//	"jhonny.quispe@testtest.apuestatotal.com" 
		];

		$mail = [
			"subject" => "TELESERVICIOS - FUSIÓN CLIENTES -".$num_doc_s,
			"body" => $body,
			"cc" => $cc,
			"bcc" => $bcc,
			"attach" => [
			//$filepath . $file,
			],
		];

		send_email($mail);

	}
}






//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************
// ETIQUETAS
//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************


//*******************************************************************************************************************
// ACTUALIZAR ETIQUETAS
//*******************************************************************************************************************
if ($_POST["accion"] === "actualizar_etiqueta") {

	$query_1 = "
		SELECT id, UPPER(label) label, color 
		FROM tbl_televentas_etiqueta 
		WHERE status= 1 
		";
	$list_query_1 = $mysqli->query($query_1);
	$list_1 = array();
	while ($li_1 = $list_query_1->fetch_assoc()) {
		$list_1[] = $li_1;
	}
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}
	if (count($list_1) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "Etiquetas listadas.";
		$result["result"] = $list_1;
	} else {
		$result["http_code"] = 400;
		$result["result"] = "Sin etiquetas.";
	}
}



//*******************************************************************************************************************
// GUARDAR FECHA DE NACIMIENTO
//*******************************************************************************************************************
if ($_POST["accion"] === "guardar_fec_nac") {
	include("function_replace_invalid_caracters.php");

	$id_cliente = $_POST["id_cliente"];
	$fec_nac = $_POST["fec_nac"];
	$fecha_minima = date("Y-m-d", strtotime("-18 years"));

		if($fec_nac >  $fecha_minima ){

			$result["http_code"] = 400;
			$result["status"] = "Debe cumplir con la mayoria de edad";
	 
		}else{
	
			$query_update = "
		UPDATE tbl_televentas_clientes 
		SET 
			fec_nac = '" . $fec_nac . "',
			updated_at = now()
		WHERE id = '" . $id_cliente . "'
			";
		
			$mysqli->query($query_update);
			if ($mysqli->error) {
				$result["update_error"] = $mysqli->error;
			}else{
				$result["http_code"] = 200;
			$result["status"] = "La fecha comparada es igual o menor";
			}
			 
		}


	


}




//*******************************************************************************************************************
// GUARDAR ETIQUETA
//*******************************************************************************************************************
if ($_POST["accion"] === "guardar_etiqueta") {
	include("function_replace_invalid_caracters.php");

	$id_cliente = $_POST["id_cliente"];
	$i_etiqueta = strtoupper(trim(replace_invalid_caracters($_POST["i_etiqueta"])));
	$txa_observacion = strtoupper(trim(replace_invalid_caracters($_POST["txa_observacion"])));
	$i_color = $_POST["i_color"];
	$tipo = $_POST["tipo"];

	$query_1 = "
		SELECT 
			e.id,
			e.label
		FROM tbl_televentas_etiqueta e
		WHERE 
			e.label = '" . $i_etiqueta . "'  
		";
	//$result["consulta_query"] = $query_3;
	$list_query = $mysqli->query($query_1);
	$list_1 = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_1[] = $li;
	}
	if ($mysqli->error) {
		$result["query_1_error"] = $mysqli->error;
	}
	if (count($list_1) == 0) {

		$insert_1 = " 
			INSERT INTO tbl_televentas_etiqueta
				(
				`label`,
				`description`,
				`color`,
				`level`,
				`tipo`,
				`status`,
				`created_user_id`,
				`created_at`
				) VALUES (
				'" . $i_etiqueta . "',
				'" . $txa_observacion . "',
				'" . $i_color . "',
				'1',
				'" . $tipo . "',
				'1',
				'" . $usuario_id . "',
				now()
				)";
		$mysqli->query($insert_1);
		if ($mysqli->error) {
			$result["iinsert_1_error"] = $mysqli->error;
		}

		$query_2 = "
			SELECT 
				e.id,
				e.label,
				IFNULL(e.tipo, 1) tipo
			FROM tbl_televentas_etiqueta e
			WHERE 
				e.label = '" . $i_etiqueta . "'  
			";
		//$result["consulta_query"] = $query_3;
		$list_query = $mysqli->query($query_2);
		$list_2 = array();
		while ($li = $list_query->fetch_assoc()) {
			$list_2[] = $li;
		}
		if ($mysqli->error) {
			$result["query_2_error"] = $mysqli->error;
		}
		if (count($list_2) == 1) {
			$insert_2 = " 
				INSERT INTO tbl_televentas_clientes_etiqueta
					(
					`client_id`,
					`etiqueta_id`,
					`status`,
					`created_user_id`,
					`created_at`
					) VALUES (
					'" . $id_cliente . "',
					'" . $list_2[0]["id"] . "',
					'1',
					'" . $usuario_id . "',
					now()
					)";
			$mysqli->query($insert_2);
			if ($mysqli->error) {
				$result["insert_2_error"] = $mysqli->error;
			}

			$query_3 = "
				SELECT 
					ce.id
				FROM tbl_televentas_clientes_etiqueta ce
				WHERE 
					ce.client_id = '" . $id_cliente . "' 
					AND ce.etiqueta_id = '" . $list_2[0]["id"] . "' 
					AND ce.status = '1' 
				";
			//$result["consulta_query"] = $query_3;
			$list_query = $mysqli->query($query_3);
			$list_3 = array();
			while ($li = $list_query->fetch_assoc()) {
				$list_3[] = $li;
			}
			if ($mysqli->error) {
				$result["query_3_error"] = $mysqli->error;
			}
			if (count($list_3) == 1) {
				$result["http_code"] = 200;
				$result["status"] = "ok";
			} else {
				$result["http_code"] = 400;
				$result["status"] = "Ocurrió un error al registrar la etiqueta con el cliente.";
			}
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al registrar la etiqueta.";
		}
	} elseif (count($list_1) > 0) {
		$result["http_code"] = 400;
		$result["status"] = "Esta etiqueta ya existe.";
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar las coincidencias.";
	}

}


//*******************************************************************************************************************
// ELEGIR ETIQUETA
//*******************************************************************************************************************
if ($_POST["accion"] === "elegir_etiqueta") {

	$id_cliente = $_POST["id_cliente"];
	$id_etiqueta = $_POST["id_etiqueta"];

	$query_1 = "
		SELECT 
			ce.id, ce.status
		FROM tbl_televentas_clientes_etiqueta ce
		WHERE 
			ce.client_id = '" . $id_cliente . "' 
			AND ce.etiqueta_id = '" . $id_etiqueta . "' 
		";
	//$result["consulta_query"] = $query_3;
	$list_query = $mysqli->query($query_1);
	$list_1 = array();
	while ($li_1 = $list_query->fetch_assoc()) {
		$list_1[] = $li_1;
	}
	if ($mysqli->error) {
		$result["query_1_error"] = $mysqli->error;
	}
	if (count($list_1) == 0) {

		$insert_2 = " 
			INSERT INTO tbl_televentas_clientes_etiqueta
				(
				`client_id`,
				`etiqueta_id`,
				`status`,
				`created_user_id`,
				`created_at`
				) VALUES (
				'" . $id_cliente . "',
				'" . $id_etiqueta . "',
				'1',
				'" . $usuario_id . "',
				now()
				)";
		$mysqli->query($insert_2);
		if ($mysqli->error) {
			$result["insert_2_error"] = $mysqli->error;
		}

		$query_3 = "
			SELECT 
				ce.id
			FROM tbl_televentas_clientes_etiqueta ce
			WHERE 
				ce.client_id = '" . $id_cliente . "' 
				AND ce.etiqueta_id = '" . $id_etiqueta . "' 
				AND ce.status = '1' 
			";
		//$result["consulta_query"] = $query_3;
		$list_query = $mysqli->query($query_3);
		$list_3 = array();
		while ($li_3 = $list_query->fetch_assoc()) {
			$list_3[] = $li_3;
		}
		if ($mysqli->error) {
			$result["query_3_error"] = $mysqli->error;
		}
		if (count($list_3) == 1) {
			$result["http_code"] = 200;
			$result["status"] = "ok";
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al registrar la etiqueta con el cliente.";
		}
	} else {

		if($list_1[0]['status'] != 1 ){

			$query_update = "
			UPDATE tbl_televentas_clientes_etiqueta
			SET 
				status = 1, 
				updated_user_id = '".$usuario_id."', 
				updated_at = now()
			WHERE client_id = '".$id_cliente."' 
			AND etiqueta_id = '".$id_etiqueta."'
			";
			$mysqli->query($query_update);
			if ($mysqli->error) {
				$result["update_etiq_error"] = $mysqli->error;
			}

			$result["http_code"] = 200;
			$result["status"] = "Etiqueta reactivada";

		}else{
			$result["http_code"] = 400;
			$result["status"] = "El cliente ya cuenta con la etiqueta registrada y activa";
		}
	}

}


//*******************************************************************************************************************
// ELIMINAR ETIQUETA
//*******************************************************************************************************************
if ($_POST["accion"] === "eliminar_etiqueta") {

	$id_cliente = $_POST["id_cliente"];
	$id_cliente_etiqueta = $_POST["id_cliente_etiqueta"];

	$query_update_1 = "
		UPDATE tbl_televentas_clientes_etiqueta 
		SET status = 0, 
		created_user_id = '" . $usuario_id . "', 
		updated_at = now() 
		WHERE id = '" . $id_cliente_etiqueta . "' 
		AND client_id = '" . $id_cliente . "' 
		";
	$mysqli->query($query_update_1);
	if ($mysqli->error) {
		$result["query_update_1_error"] = $mysqli->error;
	}
	//$result["consulta_update"] = $query_update_1;
	$query_2 = "
		SELECT 
			ce.id
		FROM tbl_televentas_clientes_etiqueta ce
		WHERE 
			ce.client_id = '" . $id_cliente . "' 
			AND ce.id = '" . $id_cliente_etiqueta . "' 
			AND ce.status = '0' 
		";
	//$result["consulta_query"] = $query_2;
	$list_query = $mysqli->query($query_2);
	$list_2 = array();
	while ($li_2 = $list_query->fetch_assoc()) {
		$list_2[] = $li_2;
	}
	if ($mysqli->error) {
		$result["query_2_error"] = $mysqli->error;
	}
	if (count($list_2) == 1) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al eliminar la etiqueta.";
		$result["result"] = $list_2;
	}

}


//*******************************************************************************************************************
// CONSULTAR LUDOPATIA MINCETUR
//*******************************************************************************************************************
if ($_POST["accion"] === "consultar_mincetur_ludopatia") {
    $valores = [
        "numero_documento" => $_POST["numero_documento"],
        "tipo_documento" => $_POST["tipo_documento"]
    ];
	$result_api_mincetur = consultar_mincetur_ludopatia($valores);
	//$result["result_api_mincetur"] = $result_api_mincetur["success"]; echo json_encode($result); exit();
	if (isset($result_api_mincetur["success"])) {
		if($result_api_mincetur["success"] === true){
			$result["http_code"] = $result_api_mincetur["estado"];
			$result["status"]="OK";
			$result["result"]=$result_api_mincetur["success"];
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Datos NO obtenidos de la API de DNI.";
		$result["result"] = "Sin resultados.";
		$result["error"] = $result_api_mincetur;
	}
	echo json_encode($result); exit();
}





//*******************************************************************************************************************
// CONSULTAR DNI
//*******************************************************************************************************************
if ($_POST["accion"] === "consultar_dni") {

	$busqueda_valor = $_POST["num_doc"];
	$result_api_dni = get_cliente_por_dni($busqueda_valor);

	if (isset($result_api_dni["dni"])) {
		if ($result_api_dni["dni"] == $busqueda_valor) {
			$result["http_code"] = 200;
			$result["status"] = "Datos obtenidos de la API de DNI.";
			$result["result"] = $result_api_dni;
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Datos NO obtenidos de la API de DNI.";
			$result["result"] = "Error en los resultados.";
			$result["error"] = $result_api_dni;
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Datos NO obtenidos de la API de DNI.";
		$result["result"] = "Sin resultados.";
		$result["error"] = $result_api_dni;
	}
}













//*******************************************************************************************************************
// OBTENER TRANSACCIONES NUEVAS 
//*******************************************************************************************************************
if ($_POST["accion"] === "obtener_cuentas_x_cliente") {

	$cliente_id = $_POST["cliente_id"];

	$query_1 = "
		SELECT
			tcc.id cod,
			ifnull( b.nombre, 0 ) banco,
			ifnull(tcc.banco_id, 0) banco_id,
			substring(tcc.cuenta_num, -4) AS cuenta_num_cliente,
			lpad(substring(tcc.cuenta_num, -4), length(tcc.cuenta_num),'*') AS cuenta_num,
			lpad(substring(tcc.cci, -4), length(tcc.cci),'*') AS cci
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






//*******************************************************************************************************************
// GUARDAR CUENTA
//*******************************************************************************************************************
if ($_POST["accion"] === "guardar_cuenta_x_cliente") {
	include("function_replace_invalid_caracters.php");

	$id_cliente = $_POST["id_cliente"];
	$id_banco = $_POST["id_banco"];
	$cuenta_num = strtoupper(trim(replace_invalid_caracters($_POST["cuenta_num"])));
	$cci = strtoupper(trim(replace_invalid_caracters($_POST["cci"])));

	$query_1 = "
		SELECT 
			cc.id 
		FROM tbl_televentas_clientes_cuenta cc
		WHERE 
			cc.banco_id = '" . $id_banco . "' 
			AND cc.cuenta_num = '" . $cuenta_num . "' 
			AND cc.status = 1
		";
	//$result["consulta_query"] = $query_3;
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
		$result["status"] = "No se puede crear esta cuenta porque ya existe.";
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar las coincidencias.";
	}

}








//*******************************************************************************************************************
// OBTENER CUENTAS BANCARIAS DE LOS CAJEROS
//*******************************************************************************************************************
if ($_POST["accion"] === "obtener_cuentas_x_cajero") {

	$cliente_id = $_POST["cliente_id"];

	$query_1 = "
		SELECT
			tcc.id cod,
			ifnull( b.nombre, 0 ) banco,
			ifnull(tcc.banco_id, 0) banco_id,
			substring(tcc.cuenta_num, -4) AS cuenta_num_cliente,
			lpad(substring(tcc.cuenta_num, -4), length(tcc.cuenta_num),'*') AS cuenta_num,
			lpad(substring(tcc.cci, -4), length(tcc.cci),'*') AS cci
		FROM
			tbl_televentas_cajeros_cuenta tcc
			JOIN tbl_bancos b ON b.id = tcc.banco_id 
		WHERE
			tcc.`status` = 1 
			AND tcc.user_id = $usuario_id 
		";
	//$result["consulta_query"] = $query_3;
	$list_query = $mysqli->query($query_1);
	$list_cuentas_cajeros = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_cuentas_cajeros[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list_cuentas_cajeros) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay cuentas.";
	} elseif (count($list_cuentas_cajeros) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_cuentas_cajeros;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar las cuentas.";
	}

}


//*******************************************************************************************************************
// GUARDAR CUENTA DE CAJEROS
//*******************************************************************************************************************
if ($_POST["accion"] === "guardar_cuenta_x_cajero") {
	include("function_replace_invalid_caracters.php");

	$id_cliente = $_POST["id_cliente"];
	$id_banco = $_POST["id_banco"];
	$cuenta_num = strtoupper(trim(replace_invalid_caracters($_POST["cuenta_num"])));
	$cci = strtoupper(trim(replace_invalid_caracters($_POST["cci"])));

	$query_1 = "
		SELECT 
			cc.id 
		FROM tbl_televentas_cajeros_cuenta cc
		WHERE 
			cc.user_id = '" . $usuario_id . "' 
			AND cc.banco_id = '" . $id_banco . "' 
			AND cc.cuenta_num = '" . $cuenta_num . "' 
			AND cc.status = 1
		";
	//$result["consulta_query"] = $query_3;
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
			INSERT INTO tbl_televentas_cajeros_cuenta
				(
				`banco_id`,
				`cuenta_num`,
				`cci`,
				`status`,
				`user_id`,
				`created_at`
				) VALUES (
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

}




//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************
// PROPINAS PARA EL CAJERO
//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************



//*******************************************************************************************************************
// GUARDAR TRANSACCIÓN (PROPINA DEL CAJERO)
//*******************************************************************************************************************
if ($_POST["accion"] === "guardar_propina_cajero") {

	$id_cliente = $_POST["id_cliente"];
	$monto = $_POST["monto"];
	$observacion = $_POST["observacion"];
	$id_banco_cuenta_usar_propina = $_POST["id_banco_cuenta_usar_propina"];
	$id_cuenta_usar_propina = $_POST["id_cuenta_usar_propina"];
	$link_atencion = $_POST["link_atencion"];

	date_default_timezone_set("America/Lima");
	$date_time = date('Y-m-d H:i:s');

	//*******************************************************************************************************************
	// DESCONTAR DEL BALANCE DISPONIBLE
	//*******************************************************************************************************************

	if (!((double) $monto > 0)) {
		$result["http_code"] = 400;
		$result["status"] = "Monto incorrecto.";
		$result["monto_deposito"] = ((float) $monto_deposito > 0) ? true : false;
		echo json_encode($result);exit();
	}

	//********* VALIDACION BALANCES
	$query_balances = "
		SELECT 
			(SELECT balance FROM tbl_televentas_clientes_balance 
				WHERE tipo_balance_id=1 AND cliente_id=" . $id_cliente . " 
			) balance_total,
			IFNULL((SELECT balance FROM tbl_televentas_clientes_balance 
				WHERE tipo_balance_id=4 AND cliente_id=" . $id_cliente . " 
			), 0) balance_deposito,
			IFNULL((SELECT balance FROM tbl_televentas_clientes_balance 
				WHERE tipo_balance_id=5 AND cliente_id=" . $id_cliente . " 
			), 0) balance_disponible_retiro
		";
	$list_query = $mysqli->query($query_balances);
	$list_balance = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_balance[] = $li;
	}
	$balance_total = $list_balance[0]["balance_total"];
	$balance_deposito = $list_balance[0]["balance_deposito"];
	$balance_disponible_retiro = $list_balance[0]["balance_disponible_retiro"];

	$nuevo_balance = $balance_total - $monto;
	$nuevo_balance_retiro = $balance_disponible_retiro - $monto;
	$nuevo_balance_deposito = $balance_deposito - $monto;
	//********* VALIDACION BALANCES
	
	//********* VALIDACION SALDO DISPONIBLE DE RETIRO
	if ($balance_disponible_retiro > 0){
		if (count($list_balance) == 0) {
			$result["http_code"] = 400;
			$result["status"] = "Usted no tiene balance.";
			$result["result"] = $list_balance;
		} elseif (count($list_balance) > 0) {
			//********* VALIDACION BALANCE
			$insert_command = " 
				INSERT INTO tbl_televentas_clientes_transaccion (
					tipo_id,
					cliente_id,
					cajero_cuenta_id,
					turno_id,
					cc_id,
					num_operacion,
					bono_id,
					monto_deposito,
					comision_monto,
					monto,
					bono_monto,
					total_recarga,
					nuevo_balance,
					estado,
					observacion_cajero,
					bono_mensual_actual,
					user_id,
					created_at,
					observacion_supervisor
				) VALUES (
					21,
					'" . $id_cliente . "',
					'" . $id_cuenta_usar_propina . "',
					'" . $turno_id . "',
					'" . $cc_id . "',
					'',
					'0',
					'0',
					'0',
					'" . $monto . "',
					'0',
					'0',
					'" . $nuevo_balance . "',
					'1',
					'" . $observacion . "',
					'0',
					'" . $usuario_id . "',
					now(),
					'" . $link_atencion . "'
				)
				";
			$mysqli->query($insert_command);
			if ($mysqli->error) {
				$result["insert_query"] = $insert_command;
				$result["insert_error"] = $mysqli->error;
			} else {
				$query_3 = "
					SELECT 
					a.id, 
					LPAD((
						SELECT COUNT(b.id) 
						FROM tbl_televentas_clientes_transaccion b 
						WHERE b.tipo_id=21
						AND b.created_at<='$date_time' ),10,'0') correlativo 
					FROM tbl_televentas_clientes_transaccion a 
					WHERE tipo_id = 21 
					AND cliente_id = '" . $id_cliente . "' 
					AND user_id = '" . $usuario_id . "' 
					AND turno_id = '" . $turno_id . "' 
					AND cc_id = '" . $cc_id . "' 
					AND monto = '" . $monto . "' 
					ORDER BY id DESC LIMIT 1 
					";
				$list_query = $mysqli->query($query_3);
				if ($mysqli->error) {
					$result["consulta_error"] = $mysqli->error;
				} else {
					$list_transaccion = array();
					while ($li = $list_query->fetch_assoc()) {
						$list_transaccion[] = $li;
					}
					if (count($list_transaccion) === 0) {
						$result["http_code"] = 400;
						$result["status"] = "No se guardó la transacción.";
					} elseif (count($list_transaccion) === 1) {
						$transaccion_id = $list_transaccion[0]["id"];

						$query_update_balance = " 
						UPDATE tbl_televentas_clientes_balance 
						SET
							balance = balance - " . $monto . ",
							updated_at=now()
						WHERE tipo_balance_id = 5
							AND cliente_id=" . $id_cliente;
						$mysqli->query($query_update_balance);

						$query_update_balance = " 
						UPDATE tbl_televentas_clientes_balance 
						SET
							balance = balance - " . $monto . ",
							updated_at=now()
						WHERE tipo_balance_id = 1
							AND cliente_id=" . $id_cliente;
						$mysqli->query($query_update_balance);

						query_tbl_televentas_clientes_balance_transaccion('insert', $transaccion_id, 
							$id_cliente, 1, $balance_total, $monto, $nuevo_balance);

						query_tbl_televentas_clientes_balance_transaccion('insert', $transaccion_id, 
							$id_cliente, 5, $balance_disponible_retiro, $monto, $nuevo_balance_retiro);
						sec_tlv_asignar_etiqueta_test($id_cliente);
						$query = " 
							UPDATE tbl_televentas_clientes_transaccion 
							SET txn_id = '" . $list_transaccion[0]['correlativo'] . "' 
							WHERE id = '" . $list_transaccion[0]['id'] . "' 
							";
						$mysqli->query($query);


							// $transaccion_id = $list_transaccion[0]["id"];
						//**************************************************************************************************
						//**************************************************************************************************
						// IMAGEN
						//**************************************************************************************************
						$path = "/var/www/html/files_bucket/propinas/";
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
								$imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);

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
												'" . $date_time . "',
												1
												)";
							$mysqli->query($comando);
							$archivo_id = mysqli_insert_id($mysqli);
							$filepath = $path . $resizeFileName . "." . $fileExt;
						}
						//**************************************************************************************************
						//**************************************************************************************************





						$result["http_code"] = 200;
						$result["status"] = "ok";
						$result["result"] = "Solicitud de Propina Registrada";
					} else {
						$result["http_code"] = 400;
						$result["status"] = "Ocurrió un error al guardar la transacción.";
					}
				}
			}

		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al consultar el balance.";
			$result["result"] = $list_balance;
		}
	}else{
		$result["http_code"] = 400;
		$result["status"] = "Usted no tiene saldo disponible para brindar una propina.";
		$result["result"] = $list_balance;
	}

}






//*******************************************************************************************************************
// OBTENER FECHA Y HORA
//*******************************************************************************************************************
if ($_POST["accion"] === "obtener_fecha_hora") {

	$query = "SELECT now() fecha_hora, 1 estado ";
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




//*******************************************************************************************************************
// ELIMINAR APUESTA
//*******************************************************************************************************************
if ($_POST["accion"] === "eliminar_transaccion_apuesta") {
	include("function_replace_invalid_caracters.php");

	$id_cliente = $_POST["cliente_id"];
	$id_trans = $_POST["trans_id"];
	$tipo_id = $_POST["tipo_id"];
	$proveedor_id = $_POST["proveedor_id"];
	$tipo_balance_id = $_POST["tipo_balance_id"];
	$observacion = replace_invalid_caracters($_POST["observacion"]);

	$where_txn = " AND tct.id = '".$id_trans."' ";
	$where_cliente = " AND tct.cliente_id = '".$id_cliente."' ";
	if((int)$proveedor_id===5) {//Altenar
		$where_txn = " AND tct.txn_id = '".$id_trans."' ";
		$where_cliente = "";
	}

	$query_txn = "
			SELECT
				tct.id, 
				tct.cliente_id 
			FROM
				tbl_televentas_clientes_transaccion tct 
			WHERE 
				tct.tipo_id = 4 
				AND tct.estado = 1 
				$where_txn 
				$where_cliente 
			ORDER BY tct.id DESC
		";
	$result["consulta_query"] = $query_txn;
	$list_query = $mysqli->query($query_txn);
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar la transacción.";
	} else {
		$list_txn= array();
		while ($li = $list_query->fetch_assoc()) {
			$list_txn[] = $li;
		}
		if (count($list_txn) > 0) {
			$result = rollback_transaccion($list_txn[0]["cliente_id"], $list_txn[0]["id"], $usuario_id, $turno_id, $observacion,0);
			if((int)$tipo_balance_id === 6){
				$list_balance = obtener_balances($id_cliente);
				if ($list_balance[0]["balance_dinero_at"] > 0){
					$query = "
						SELECT id
						FROM wwwapuestatotal_gestion.tbl_televentas_clientes_etiqueta
						WHERE client_id = $id_cliente
						AND etiqueta_id = 43
						AND status = 1
						ORDER BY id DESC
						LIMIT 1
					";
					$list_query = $mysqli->query($query);
					if ($mysqli->error) {
						$result["consulta_error"] = $mysqli->error;
						$result["http_code"] = 400;
						$result["status"] = "Ocurrió un error al consultar la etiqueta.";
					} else {
						$list= array();
						while ($li = $list_query->fetch_assoc()) {
							$list[] = $li;
						}
						if (count($list)===0){

							$query_2 = "
								SELECT id
								FROM wwwapuestatotal_gestion.tbl_televentas_clientes_etiqueta
								WHERE client_id = $id_cliente
								AND etiqueta_id = 43
								AND status = 3
								ORDER BY id DESC
								LIMIT 1
							";
							$list_query_2 = $mysqli->query($query_2);
							if ($mysqli->error) {
								$result["consulta_error"] = $mysqli->error;
								$result["http_code"] = 400;
								$result["status"] = "Ocurrió un error al consultar la etiqueta.";
							} else {
								$list_2= array();
								while ($li = $list_query_2->fetch_assoc()) {
									$list_2[] = $li;
									$id_etiqueta = $li["id"];
								}

								$update = "
									UPDATE wwwapuestatotal_gestion.tbl_televentas_clientes_etiqueta
									SET status = 1
									WHERE id = $id_etiqueta
								";
								$mysqli->query($update);
							}
						}
					}
				}
			}
		} else {
			$result["http_code"] = 400;
			$result["status"] = "La transacción no existe.";
			$result["result"] = $list_txn;
		}
	}

}

//*******************************************************************************************************************
// ELIMINAR PAGO APUESTA
//*******************************************************************************************************************
if ($_POST["accion"] === "eliminar_transaccion_pago_apuesta") {
	include("function_replace_invalid_caracters.php");

	$id_cliente = $_POST["cliente_id"];
	$id_trans = $_POST["trans_id"];
	$tipo_id = $_POST["tipo_id"];
	$observacion = replace_invalid_caracters($_POST["observacion"]);

	$result = rollback_transaccion($id_cliente, $id_trans, $usuario_id, $turno_id, $observacion,0);

}








//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************
// RETIRO
//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************



//***************************************
// GUARDAR SOLICITUD RETIRO
//***************************************
if ($_POST["accion"] === "guardar_transaccion_solicitud_retiro") {
	include("function_replace_invalid_caracters.php");
	
	$id_cliente = $_POST["cliente_id"];
	$monto_solicitud = $_POST["monto_solicitud"];
	$id_banco_cuenta_usar_retiro = $_POST["id_banco_cuenta_usar_retiro"];
	$id_cuenta_usar_retiro = $_POST["id_cuenta_usar_retiro"];
	$razon = $_POST["razon"];
	$tipo = $_POST["tipo"];
	$motivo_devolucion = $_POST["motivo_devolucion"];
	$local_test = $_POST["local_test"];
	$link_atencion = $_POST["link_atencion"];

	if (!((float) $monto_solicitud > 0)) {
		$result["http_code"] = 400;
		$result["status"] = "Monto incorrecto.";
		$result["monto_deposito"] = ((float) $monto_deposito > 0) ? true : false;
		echo json_encode($result);exit();
	}
	$cmd_limit_amount_parameter = "
		SELECT 
			IFNULL(valor, 0) valor
		FROM 
			tbl_televentas_parametros
		WHERE 
			nombre_codigo = 'limit_amount_retiro_test'
			AND estado = 1
		LIMIT 1";
		 
	$list_cmd_l = $mysqli->query($cmd_limit_amount_parameter);
	$list_limit_amount = array();
	while ($li = $list_cmd_l->fetch_assoc()) {
		$list_limit_amount[] = $li;
	}
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}
	if(count($list_limit_amount) > 0){
		if((double)$list_limit_amount[0]["valor"] > 0){
			$limit = $list_limit_amount[0]["valor"];
			if($local_test == 1 && $monto_solicitud > $limit){
				$result["http_code"] = 400;
				$result["status"] = "El monto máximo de retiro es: " . $limit . " soles";
				$result["limite_retiro_test"] = ((double) $limit > 0) ? true : false;
				echo json_encode($result);exit();
			}
		}
	}
	
	//********* VALIDACION BALANCES
	$query_balances = "
		SELECT 
			(SELECT balance FROM tbl_televentas_clientes_balance 
				WHERE tipo_balance_id=1 AND cliente_id=" . $id_cliente . " 
			) balance_total,
			IFNULL((SELECT balance FROM tbl_televentas_clientes_balance 
				WHERE tipo_balance_id=4 AND cliente_id=" . $id_cliente . " 
			), 0) balance_deposito,
			IFNULL((SELECT balance FROM tbl_televentas_clientes_balance 
				WHERE tipo_balance_id=5 AND cliente_id=" . $id_cliente . " 
			), 0) balance_disponible_retiro
		";
	$list_query = $mysqli->query($query_balances);
	$list_balance = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_balance[] = $li;
	}
	$balance_total = $list_balance[0]["balance_total"];
	$balance_deposito = $list_balance[0]["balance_deposito"];
	$balance_disponible_retiro = $list_balance[0]["balance_disponible_retiro"];

	$nuevo_balance = $balance_total - $monto_solicitud;
	$nuevo_balance_retiro = $balance_disponible_retiro - $monto_solicitud;
	$nuevo_balance_deposito = $balance_deposito - $monto_solicitud;
	//********* VALIDACION BALANCES

	if($tipo == 1){
		//********* VALIDACION SALDO DISPONIBLE DE RETIRO
		if ($balance_disponible_retiro > 0){
			if (count($list_balance) == 0) {
				$result["http_code"] = 400;
				$result["status"] = "Usted no tiene balance.";
				$result["result"] = $list_balance;
			} elseif (count($list_balance) > 0) {
				if((double)$balance_disponible_retiro < (double)$monto_solicitud){
					$result["http_code"] = 400;
					$result["status"] = "El monto de la solicitud no puede ser mayor al balance disponible";
					echo json_encode($result);exit();
				}
				//********* VALIDACION BALANCE
				$insert_command = " 
					INSERT INTO tbl_televentas_clientes_transaccion (
						tipo_id,
						cliente_id,
						cuenta_id,
						turno_id,
						cc_id,
						num_operacion,
						bono_id,
						monto_deposito,
						comision_monto,
						monto,
						bono_monto,
						total_recarga,
						nuevo_balance,
						estado,
						observacion_supervisor,
						bono_mensual_actual,
						user_id,
						created_at,
						id_operacion_retiro,
						tipo_operacion
					) VALUES (
						9,
						'" . $id_cliente . "',
						'" . $id_cuenta_usar_retiro . "',
						'" . $turno_id . "',
						'" . $cc_id . "',
						'',
						'0',
						'0',
						'0',
						'" . $monto_solicitud . "',
						'0',
						'0',
						'" . $nuevo_balance . "',
						'1',
						'" . $link_atencion . "',
						'0',
						'" . $usuario_id . "',
						now(),
						'" . $razon . "',
						1
					)
					";
				$mysqli->query($insert_command);
				if ($mysqli->error) {
					$result["insert_query"] = $insert_command;
					$result["insert_error"] = $mysqli->error;
				} else {
					$query_3 = "SELECT * FROM tbl_televentas_clientes_transaccion  ";
					$query_3 .= " WHERE tipo_id = 9  ";
					$query_3 .= " AND cliente_id = '" . $id_cliente . "' ";
					$query_3 .= " AND user_id = '" . $usuario_id . "' ";
					$query_3 .= " AND turno_id = '" . $turno_id . "' ";
					$query_3 .= " AND cc_id = '" . $cc_id . "' ";
					$query_3 .= " AND monto = '" . $monto_solicitud . "' AND tipo_operacion = 1";
					$query_3 .= " ORDER BY id DESC LIMIT 1 ";
					$list_query = $mysqli->query($query_3);
					if ($mysqli->error) {
						$result["consulta_error"] = $mysqli->error;
					} else {
						$list_transaccion = array();
						while ($li = $list_query->fetch_assoc()) {
							$list_transaccion[] = $li;
						}
						if (count($list_transaccion) === 0) {
							$result["http_code"] = 400;
							$result["status"] = "No se guardó la transacción.";
						} elseif (count($list_transaccion) === 1) {
							$transaccion_id = $list_transaccion[0]["id"];

							$query_update_balance = " 
							UPDATE tbl_televentas_clientes_balance 
							SET
								balance = balance - " . $monto_solicitud . ",
								updated_at=now()
							WHERE tipo_balance_id = 5
								AND cliente_id=" . $id_cliente;
							$mysqli->query($query_update_balance);

							$query_update_balance = " 
							UPDATE tbl_televentas_clientes_balance 
							SET
								balance = balance - " . $monto_solicitud . ",
								updated_at=now()
							WHERE tipo_balance_id = 1
								AND cliente_id=" . $id_cliente;
							$mysqli->query($query_update_balance);

							query_tbl_televentas_clientes_balance_transaccion('insert', $transaccion_id, 
								$id_cliente, 1, $balance_total, $monto_solicitud, $nuevo_balance);

							query_tbl_televentas_clientes_balance_transaccion('insert', $transaccion_id, 
								$id_cliente, 5, $balance_disponible_retiro, $monto_solicitud, $nuevo_balance_retiro);

							$transaccion_id = $list_transaccion[0]["id"];
							sec_tlv_asignar_etiqueta_test($id_cliente);
							$result["http_code"] = 200;
							$result["status"] = "ok";
							$result["result"] = "Solicitud de Retiro Registrada";
						} else {
							$result["http_code"] = 400;
							$result["status"] = "Ocurrió un error al guardar la transacción.";
						}
					}
				}

			} else {
				$result["http_code"] = 400;
				$result["status"] = "Ocurrió un error al consultar el balance.";
				$result["result"] = $list_balance;
			}
		}else{
			$result["http_code"] = 400;
			$result["status"] = "Usted no tiene saldo disponible para retirar.";
			$result["result"] = $list_balance;
		}
	} elseif ($tipo == 2){
		if ($balance_deposito > 0){
			if (count($list_balance) == 0) {
				$result["http_code"] = 400;
				$result["status"] = "Usted no tiene balance.";
				$result["result"] = $list_balance;
			}elseif (count($list_balance) > 0){
				if((double)$balance_deposito < (double)$monto_solicitud){
					$result["http_code"] = 400;
					$result["status"] = "El monto de la solicitud no puede ser mayor al balance disponible";
					echo json_encode($result);exit();
				}
				$insert_command = " 
					INSERT INTO tbl_televentas_clientes_transaccion (
						tipo_id,
						cliente_id,
						cuenta_id,
						turno_id,
						cc_id,
						num_operacion,
						bono_id,
						monto_deposito,
						comision_monto,
						monto,
						bono_monto,
						total_recarga,
						nuevo_balance,
						estado,
						observacion_supervisor,
						bono_mensual_actual,
						user_id,
						created_at,
						id_operacion_retiro,
						tipo_operacion,
						id_motivo_dev
					) VALUES (
						28,
						'" . $id_cliente . "',
						'" . $id_cuenta_usar_retiro . "',
						'" . $turno_id . "',
						'" . $cc_id . "',
						'',
						'0',
						'0',
						'0',
						'" . $monto_solicitud . "',
						'0',
						'0',
						'" . $nuevo_balance . "',
						'1',
						'" . $link_atencion . "',
						'0',
						'" . $usuario_id . "',
						now(),
						'" . $razon . "',
						2,
						'" . $motivo_devolucion . "'
					)
					";
				$mysqli->query($insert_command);
				if ($mysqli->error) {
					$result["insert_query"] = $insert_command;
					$result["insert_error"] = $mysqli->error;
				} else {
					$query_3 = "SELECT * FROM tbl_televentas_clientes_transaccion  ";
					$query_3 .= " WHERE tipo_id = 28  ";
					$query_3 .= " AND cliente_id = '" . $id_cliente . "' ";
					$query_3 .= " AND user_id = '" . $usuario_id . "' ";
					$query_3 .= " AND turno_id = '" . $turno_id . "' ";
					$query_3 .= " AND cc_id = '" . $cc_id . "' ";
					$query_3 .= " AND monto = '" . $monto_solicitud . "' AND tipo_operacion = 2";
					$query_3 .= " ORDER BY id DESC LIMIT 1 ";
					$list_query = $mysqli->query($query_3);
					if ($mysqli->error) {
						$result["consulta_error"] = $mysqli->error;
					} else {
						$list_transaccion = array();
						while ($li = $list_query->fetch_assoc()) {
							$list_transaccion[] = $li;
						}
						if (count($list_transaccion) === 0) {
							$result["http_code"] = 400;
							$result["status"] = "No se guardó la transacción.";
						} elseif (count($list_transaccion) === 1) {
							$transaccion_id = $list_transaccion[0]["id"];

							$query_update_balance = " 
							UPDATE tbl_televentas_clientes_balance 
							SET
								balance = balance - " . $monto_solicitud . ",
								updated_at=now()
							WHERE tipo_balance_id = 4
								AND cliente_id=" . $id_cliente;
							$mysqli->query($query_update_balance);

							$query_update_balance = " 
							UPDATE tbl_televentas_clientes_balance 
							SET
								balance = balance - " . $monto_solicitud . ",
								updated_at=now()
							WHERE tipo_balance_id = 1
								AND cliente_id=" . $id_cliente;
							$mysqli->query($query_update_balance);

							query_tbl_televentas_clientes_balance_transaccion('insert', $transaccion_id, 
								$id_cliente, 1, $balance_total, $monto_solicitud, $nuevo_balance);

							query_tbl_televentas_clientes_balance_transaccion('insert', $transaccion_id, 
								$id_cliente, 4, $balance_deposito, $monto_solicitud, $nuevo_balance_deposito);

							$transaccion_id = $list_transaccion[0]["id"];
							sec_tlv_asignar_etiqueta_test($id_cliente);
							$result["http_code"] = 200;
							$result["status"] = "ok";
							$result["result"] = "Solicitud de Devolución Registrada";
						} else {
							$result["http_code"] = 400;
							$result["status"] = "Ocurrió un error al guardar la transacción.";
						}
					}
				}

			}else{
				$result["http_code"] = 400;
				$result["status"] = "Ocurrió un error al consultar el balance.";
				$result["result"] = $list_balance;
			}
		}else{
			$result["http_code"] = 400;
			$result["status"] = "Usted no tiene saldo disponible para devolver.";
			$result["result"] = $list_balance;
		}
	}

}

//*******************************************************************************************************************
// CANCELAR SOLICITUD RETIRO
//*******************************************************************************************************************
if ($_POST["accion"] === "cancelar_solicitud_retiro") {
	include("function_replace_invalid_caracters.php");

	$id_cliente = $_POST["cliente_id"];
	$id_trans = $_POST["trans_id"];
	$observacion = replace_invalid_caracters($_POST["observacion"]);
	$motivo_cancelacion = $_POST["motivo_cancelacion"];
	$tipo_operacion = $_POST["tipo_operacion"];

	$query = "
		SELECT tra.id, tra.cliente_id, tra.web_id,tra.monto,
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
			LEFT JOIN  tbl_televentas_clientes_balance ba ON ba.cliente_id=tra.cliente_id
		WHERE
			tra.cliente_id = $id_cliente
			AND tra.id = $id_trans 
			AND tra.estado = 1
		LIMIT 1
		";
	$list_query = $mysqli->query($query);
	if ($mysqli->error) {
		//print_r($mysqli->error);
		$result["consulta_error"] = $mysqli->error;
	}
	$list_transaccion = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_transaccion[] = $li;
	}
	if (count($list_transaccion) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No se puede cancelar esta solicitud.";
		$result["result"] = '';
	} elseif (count($list_transaccion) === 1) {
		$monto_solicitud =$list_transaccion[0]["monto"];
		$balance_total =$list_transaccion[0]["balance_total"];
		$balance_retiro_disponible =$list_transaccion[0]["balance_retiro_disponible"];
		$balance_deposito =$list_transaccion[0]["balance_deposito"];
		$balance_nuevo = $balance_total + $monto_solicitud;
		$balance_deposito_nuevo = $balance_deposito + $monto_solicitud;
		$balance_retiro_nuevo = $balance_retiro_disponible + $monto_solicitud;
		$tipo_id = 0;
		if($tipo_operacion == 1){
			$tipo_id = 13;
		}else if($tipo_operacion == 2){
			$tipo_id = 31;
		}
		$fecha_hora_actual = date('Y-m-d H:i:s');
		$insert_command = " 
			INSERT INTO tbl_televentas_clientes_transaccion (tipo_id,cliente_id,cuenta_id,turno_id,cc_id,web_id,
				num_operacion,bono_id,monto_deposito,comision_monto,monto,bono_monto,total_recarga,nuevo_balance,
				tipo_rechazo_id,estado,observacion_cajero,observacion_validador,bono_mensual_actual,user_id,created_at,
				transaccion_id,tipo_operacion,id_motivo_dev
			) 
			(SELECT $tipo_id, cliente_id, cuenta_id,'" . $turno_id . "','" . $cc_id . "',web_id,num_operacion,bono_id,
				monto_deposito,comision_monto,monto,bono_monto,total_recarga,'" . $balance_nuevo . "',
				" . $motivo_cancelacion . "," . 4 . ",'','" . $observacion . "', bono_mensual_actual,'" . $usuario_id . "','" . $fecha_hora_actual . "',id,
				tipo_operacion,id_motivo_dev
				FROM tbl_televentas_clientes_transaccion 
				WHERE id = " . $id_trans . " LIMIT 1)
			";

			$result["insert_command"] = $insert_command;
		$mysqli->query($insert_command);

		$error = '';
		if ($mysqli->error) {
			$result["insert_error"] = $mysqli->error;
			$error = $mysqli->error;
		}else{
			$cmd_valid = "SELECT id FROM tbl_televentas_clientes_transaccion  ";
			$cmd_valid .= " WHERE tipo_id = " . $tipo_id;
			$cmd_valid .= " AND cliente_id = '" . $id_cliente . "' ";
			$cmd_valid .= " AND user_id = '" . $usuario_id . "' ";
			$cmd_valid .= " AND turno_id = '" . $turno_id . "' ";
			$cmd_valid .= " AND cc_id = '" . $cc_id . "' ";
			$cmd_valid .= " AND nuevo_balance = '" . $balance_nuevo . "' ";
			$cmd_valid .= " AND created_at = '" . $fecha_hora_actual . "' ";
			$cmd_valid .= " ORDER BY id DESC LIMIT 1 ";
			$result["cmd_valid"] = $cmd_valid;
			$list_query = $mysqli->query($cmd_valid);
			if ($mysqli->error) {
				$result["consulta_error"] = $mysqli->error;
			} else {
				$list_tran_valid = array();
				while ($li = $list_query->fetch_assoc()) {
					$list_tran_valid[] = $li;
				}
				if (count($list_tran_valid) === 0) {
					$result["http_code"] = 400;
					$result["status"] = "No se guardó la transacción.";
				} elseif (count($list_tran_valid) === 1) {
					$trans_cancel_id = $list_tran_valid[0]["id"];
					$query_update = "
						UPDATE tbl_televentas_clientes_transaccion 
						SET 
							estado = '4', 
							update_user_id = " . $usuario_id . ", 
							observacion_supervisor = '" . $observacion . "', 
							updated_at = now() 
						WHERE id = '" . $id_trans . "'";
					$mysqli->query($query_update);

					$query_update_balance = " 
						UPDATE tbl_televentas_clientes_balance 
						SET
							balance = balance + " . $monto_solicitud . ",
							updated_at=now()
						WHERE tipo_balance_id = 1
							AND cliente_id=" . $id_cliente;
					$mysqli->query($query_update_balance);
					query_tbl_televentas_clientes_balance_transaccion('insert', $trans_cancel_id, 
						$id_cliente, 1, $balance_total, $monto_solicitud, $balance_nuevo);

					if($tipo_operacion == 1){
						$query_update_balance = " 
							UPDATE tbl_televentas_clientes_balance 
							SET
								balance = balance + " . $monto_solicitud . ",
								updated_at=now()
							WHERE tipo_balance_id = 5
								AND cliente_id=" . $id_cliente;
						$mysqli->query($query_update_balance);
						query_tbl_televentas_clientes_balance_transaccion('insert', $trans_cancel_id, 
							$id_cliente, 5, $balance_retiro_disponible, $monto_solicitud, $balance_retiro_nuevo);
					}else if ($tipo_operacion == 2){
						$query_update_balance = " 
							UPDATE tbl_televentas_clientes_balance 
							SET
								balance = balance + " . $monto_solicitud . ",
								updated_at=now()
							WHERE tipo_balance_id = 4
								AND cliente_id=" . $id_cliente;
						$mysqli->query($query_update_balance);
						query_tbl_televentas_clientes_balance_transaccion('insert', $trans_cancel_id, 
							$id_cliente, 4, $balance_retiro_disponible, $monto_solicitud, $balance_retiro_nuevo);
					}
					$result["http_code"] = 200;
					$result["status"] = "ok";
					$result["result"] = "La solicitud se canceló exitosamente.";
				}
			}
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar el balance.";
		$result["result"] = $list_transaccion;
	}

}

//*******************************************************************************************************************
// CANCELAR SOLICITUD PROPINA
//*******************************************************************************************************************
if ($_POST["accion"] === "cancelar_solicitud_propina") {
	include("function_replace_invalid_caracters.php");

	$id_cliente = $_POST["cliente_id"];
	$id_trans = $_POST["trans_id"];
	$observacion = replace_invalid_caracters($_POST["observacion"]);
	$motivo_cancelacion = $_POST["motivo_cancelacion"];
	$tipo_operacion = $_POST["tipo_operacion"];

	$query = "
		SELECT tra.id, tra.cliente_id, tra.web_id,tra.monto,
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
			LEFT JOIN  tbl_televentas_clientes_balance ba ON ba.cliente_id=tra.cliente_id
		WHERE
			tra.cliente_id = $id_cliente
			AND tra.id = $id_trans 
			AND tra.estado = 1
		LIMIT 1
		";
	$list_query = $mysqli->query($query);
	if ($mysqli->error) {
		//print_r($mysqli->error);
		$result["consulta_error"] = $mysqli->error;
	}
	$list_transaccion = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_transaccion[] = $li;
	}
	if (count($list_transaccion) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No se puede cancelar esta solicitud.";
		$result["result"] = '';
	} elseif (count($list_transaccion) === 1) {
		$monto_solicitud =$list_transaccion[0]["monto"];
		$balance_total =$list_transaccion[0]["balance_total"];
		$balance_retiro_disponible =$list_transaccion[0]["balance_retiro_disponible"];
		$balance_deposito =$list_transaccion[0]["balance_deposito"];
		$balance_nuevo = $balance_total + $monto_solicitud;
		$balance_deposito_nuevo = $balance_deposito + $monto_solicitud;
		$balance_retiro_nuevo = $balance_retiro_disponible + $monto_solicitud;
		$insert_command = " 
			INSERT INTO tbl_televentas_clientes_transaccion (tipo_id,cliente_id,cuenta_id,turno_id,cc_id,web_id,
				num_operacion,bono_id,monto_deposito,comision_monto,monto,bono_monto,total_recarga,nuevo_balance,
				tipo_rechazo_id,estado,observacion_cajero,observacion_validador,bono_mensual_actual,user_id,created_at,
				transaccion_id,tipo_operacion,id_motivo_dev
			) 
			(SELECT 22, cliente_id, cuenta_id,turno_id,cc_id,web_id,num_operacion,bono_id,
				monto_deposito,comision_monto,monto,bono_monto,total_recarga," . $balance_nuevo . ",
				" . $motivo_cancelacion . "," . 4 . ",'','" . $observacion . "', bono_mensual_actual,user_id,now(),id,
				tipo_operacion,id_motivo_dev
				FROM tbl_televentas_clientes_transaccion 
				WHERE id = " . $id_trans . " LIMIT 1)
			";
		$mysqli->query($insert_command);
		$error = '';
		if ($mysqli->error) {
			$result["insert_error"] = $mysqli->error;
			$error = $mysqli->error;
		}

		$query_update = "
			UPDATE tbl_televentas_clientes_transaccion 
			SET 
				estado = '4', 
				update_user_id = " . $usuario_id . ", 
				observacion_supervisor = '" . $observacion . "', 
				updated_at = now() 
			WHERE id = '" . $id_trans . "'";
		$mysqli->query($query_update);

		$query_update_balance = " 
			UPDATE tbl_televentas_clientes_balance 
			SET
				balance = balance + " . $monto_solicitud . ",
				updated_at=now()
			WHERE tipo_balance_id = 1
				AND cliente_id=" . $id_cliente;
		$mysqli->query($query_update_balance);
		query_tbl_televentas_clientes_balance_transaccion('insert', $id_trans, 
			$id_cliente, 1, $balance_total, $monto_solicitud, $balance_nuevo);

		if($tipo_operacion == 1){
			$query_update_balance = " 
				UPDATE tbl_televentas_clientes_balance 
				SET
					balance = balance + " . $monto_solicitud . ",
					updated_at=now()
				WHERE tipo_balance_id = 5
					AND cliente_id=" . $id_cliente;
			$mysqli->query($query_update_balance);
			query_tbl_televentas_clientes_balance_transaccion('insert', $id_trans, 
				$id_cliente, 5, $balance_retiro_disponible, $monto_solicitud, $balance_retiro_nuevo);
		}else if ($tipo_operacion == 2){
			$query_update_balance = " 
				UPDATE tbl_televentas_clientes_balance 
				SET
					balance = balance + " . $monto_solicitud . ",
					updated_at=now()
				WHERE tipo_balance_id = 4
					AND cliente_id=" . $id_cliente;
			$mysqli->query($query_update_balance);
			query_tbl_televentas_clientes_balance_transaccion('insert', $id_trans, 
				$id_cliente, 4, $balance_retiro_disponible, $monto_solicitud, $balance_retiro_nuevo);
		}
		sec_tlv_asignar_etiqueta_test($id_cliente);
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = "La solicitud se canceló exitosamente.";

	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar el balance.";
		$result["result"] = $list_transaccion;
	}

}

//*******************************************************************************************************************
// OBTENER IMAGEN TRANSACCION RETIRO
//*******************************************************************************************************************
if ($_POST["accion"] === "obtener_imagenes_x_transaccion_retiro") {
	$id_transaccion = $_POST["id_transaccion"];

	$query_1 = "
		SELECT
			tta.id,
			tta.archivo 
		FROM
			tbl_televentas_transaccion_archivos tta 
		WHERE
			tta.transaccion_id = " . $id_transaccion . "
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
if ($_POST["accion"] === "obtener_imagenes_x_transaccion_propina") {
	$id_transaccion = $_POST["id_transaccion"];

	$query_1 = "
		SELECT
			tta.id,
			tta.archivo 
		FROM
			tbl_televentas_transaccion_archivos tta 
		WHERE
			tta.transaccion_id = " . $id_transaccion . "
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
// OBTENER ULTIMAS TRANSACCIONES
//*******************************************************************************************************************
if ($_POST["accion"] === "obtener_ultimas_transacciones") {

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

if ($_POST["accion"] === "sec_tlv_cambiar_estado_enviar_comprobante") {

	$id_transaccion = $_POST["id_transaccion"];
	$error_update = "";
	$query_update = "
		UPDATE tbl_televentas_clientes_transaccion 
		SET enviar_comprobante = 1
		WHERE id = " . $id_transaccion;
	$mysqli->query($query_update);

	if($mysqli->error){
		$error_update .= 'Error al cambiar el estado de la transacción: ' . $mysqli->error . $query_update;
	}

	$query_c = "
		SELECT transaccion_id 
		FROM tbl_televentas_clientes_transaccion 
		where id = " . $id_transaccion;
	$list_query = $mysqli->query($query_c);
	$list = array();
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	$id_trans = $list[0]["transaccion_id"];


	$query_update = "
		UPDATE tbl_televentas_clientes_transaccion 
		SET enviar_comprobante = 1
		WHERE id = " . $id_trans;
	$mysqli->query($query_update);

	if ($error_update == '') {
		$result["http_code"] = 200;
	} else {
		$result["http_code"] = 400;
	}

	$result["status"] = "Datos obtenidos de gestion.";
	$result["error_update"] = $error_update;
}











//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************
// DONACIÓN CANCER
//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************



//*******************************************************************************************************************
// GUARDAR TRANSACCIÓN
//*******************************************************************************************************************
if ($_POST["accion"] === "guardar_donacion_cancer") {

	$id_cliente = $_POST["id_cliente"];
	$monto = $_POST["monto"];

	date_default_timezone_set("America/Lima");
	$date_time = date('Y-m-d H:i:s');

	$insert_command = "
		INSERT INTO tbl_televentas_clientes_transaccion (
			tipo_id,
			api_id,
			cliente_id,
			user_id,
			turno_id,
			cc_id,
			monto,
			nuevo_balance,
			estado,
			created_at
		) VALUES (
			15,
			6,
			'" . $id_cliente . "',
			'" . $usuario_id . "',
			'" . $turno_id . "',
			'" . $cc_id . "',
			'" . $monto . "',
			0,
			1,
			'" . $date_time . "'
		)";
	$mysqli->query($insert_command);
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al guardar la transacción.";
		//$result["insert_query"] = $insert_command;
		$result["insert_error"] = $mysqli->error;
		echo json_encode($result);exit();
	}
	$query_3 = "
		SELECT 
			a.id,
			LPAD((SELECT COUNT(b.id) FROM tbl_televentas_clientes_transaccion b WHERE b.tipo_id=15 AND b.api_id=6 AND b.created_at<='$date_time' ),10,'0') correlativo
		FROM tbl_televentas_clientes_transaccion a 
		WHERE tipo_id = 15 
			AND api_id = 6 
			AND cliente_id = '$id_cliente' 
			AND user_id = '$usuario_id' 
			AND turno_id = '$turno_id' 
			AND cc_id = '$cc_id' 
			AND estado = '1' 
			AND created_at = '$date_time' 
	";
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar la transacción.";
		$result['query'] = $query_3;
		echo json_encode($result);exit();
	} else {
		$list_query = $mysqli->query($query_3);
		$list_transaccion = array();
		while ($li = $list_query->fetch_assoc()) {
			$list_transaccion[] = $li;
		}
		if (count($list_transaccion) == 0) {
			$result["http_code"] = 400;
			$result["status"] = "No se pudo guardar la transacción.";
			//$result["list_transaccion"] = $list_transaccion;
			$result["query"] = $query_3;

		} elseif (count($list_transaccion) === 1) {

			$list_balance = array();
			$list_balance = obtener_balances($id_cliente);

			if (count($list_balance) === 1) {

				$balance_total_actual = (double)$list_balance[0]["balance"];
				$balance_deposito_actual = (double)$list_balance[0]["balance_deposito"];
				$balance_retiro_actual = (double)$list_balance[0]["balance_retiro_disponible"];

				$temp_monto = (double)$monto;
				$balance_total_nuevo = (double)$balance_total_actual - (double)$monto;
				$balance_deposito_nuevo = (double)$list_balance[0]["balance_deposito"];
				$balance_retiro_nuevo = (double)$list_balance[0]["balance_retiro_disponible"];

				//*********************** BALANCE PRINCIPAL
				query_tbl_televentas_clientes_balance('update', $id_cliente, 1, $balance_total_nuevo);
				query_tbl_televentas_clientes_balance_transaccion('insert', $list_transaccion[0]['id'], $id_cliente, 
					1, ((double)$balance_total_actual + (double)$monto), $temp_monto, $balance_total_actual);

				//*********************** BALANCE DEPOSITO
				if((double)$balance_deposito_actual>0){
					if((double)$balance_deposito_nuevo>(double)$temp_monto){
						$query_update_4 = " 
							UPDATE tbl_televentas_clientes_balance 
							SET
								balance = balance - $temp_monto,
								updated_at = now()
							WHERE cliente_id = $id_cliente AND tipo_balance_id = 4 
							";
						$balance_deposito_nuevo = (double)$balance_deposito_nuevo-(double)$temp_monto;
						$temp_monto = 0;
					} else {
						$temp_monto = (double)$temp_monto-(double)$balance_deposito_nuevo;
						$balance_deposito_nuevo = 0;
						$query_update_4 = " 
							UPDATE tbl_televentas_clientes_balance 
							SET
								balance = 0,
								updated_at = now()
							WHERE cliente_id = $id_cliente AND tipo_balance_id = 4 
							";
					}
					$mysqli->query($query_update_4);
					//query_tbl_televentas_clientes_balance('update', $id_cliente, 4, $balance_deposito_nuevo);
					$temp_monto_tipo4 = (double)$balance_deposito_actual-(double)$balance_deposito_nuevo;
					query_tbl_televentas_clientes_balance_transaccion('insert', $list_transaccion[0]['id'], $id_cliente, 
						4, $balance_deposito_actual, $temp_monto_tipo4, $balance_deposito_nuevo);
				}

				//*********************** BALANCE RETIRO
				if((double)$temp_monto>0){
					if((double)$balance_retiro_actual>0){
						if((double)$balance_retiro_nuevo>(double)$temp_monto){
							$query_update_5 = " 
								UPDATE tbl_televentas_clientes_balance 
								SET
									balance = balance - $temp_monto,
									updated_at = now()
								WHERE cliente_id = $id_cliente AND tipo_balance_id = 5 
								";
							$balance_retiro_nuevo = (double)$balance_retiro_nuevo-(double)$temp_monto;
							$temp_monto = 0;
						} else {
							$temp_monto = (double)$temp_monto-(double)$balance_retiro_nuevo;
							$balance_retiro_nuevo = 0;
							$query_update_5 = " 
								UPDATE tbl_televentas_clientes_balance 
								SET
									balance = 0,
									updated_at = now()
								WHERE cliente_id = $id_cliente AND tipo_balance_id = 5 
								";
						}
						$mysqli->query($query_update_5);
						//query_tbl_televentas_clientes_balance('update', $id_cliente, 5, $balance_retiro_nuevo);
						$temp_monto_tipo5 = (double)$balance_retiro_actual-(double)$balance_retiro_nuevo;
						query_tbl_televentas_clientes_balance_transaccion('insert', $list_transaccion[0]['id'], $id_cliente, 
							5, $balance_retiro_actual, $temp_monto_tipo5, $balance_retiro_nuevo);
					}
				}

				$query = " 
					UPDATE tbl_televentas_clientes_transaccion 
					SET nuevo_balance = '" . $balance_total_nuevo . "', 
						txn_id = '" . $list_transaccion[0]['correlativo'] . "' 
					WHERE id = '" . $list_transaccion[0]['id'] . "' 
					";
				$mysqli->query($query);
			}

			$transaccion_id = $list_transaccion[0]['id'];
			//**************************************************************************************************
			//**************************************************************************************************
			// IMAGEN
			//**************************************************************************************************
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
					$imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);

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
									'" . $date_time . "',
									1
									)";
				$mysqli->query($comando);
				$archivo_id = mysqli_insert_id($mysqli);
				$filepath = $path . $resizeFileName . "." . $fileExt;
			}
			//**************************************************************************************************
			//**************************************************************************************************

			$result["http_code"] = 200;
			$result["status"] = "ok";
			$result["amount"] = $monto;
			$result["list"] = $list_transaccion;
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Se registro la transacción, sin embargo ocurrió un error al consultarla.";
			//$result["list_transaccion"] = $list_transaccion;
			$result["query"] = $query_3;
			$result["query_insert"] = $insert_command;
		}
	}

}


//*******************************************************************************************************************
// ELIMINAR DONACION
//*******************************************************************************************************************
if ($_POST["accion"] === "eliminar_transaccion_donacion_cancer") {
	include("function_replace_invalid_caracters.php");

	$id_cliente = $_POST["cliente_id"];
	$id_trans = $_POST["trans_id"];
	$tipo_id = $_POST["tipo_id"];
	$proveedor_id = $_POST["proveedor_id"];
	$observacion = replace_invalid_caracters($_POST["observacion"]);

	$where_txn = " AND tct.id = '".$id_trans."' ";
	$where_cliente = " AND tct.cliente_id = '".$id_cliente."' ";

	$query_txn = "
			SELECT
				tct.id, 
				tct.cliente_id 
			FROM
				tbl_televentas_clientes_transaccion tct 
			WHERE 
				tct.tipo_id = 15 
				AND tct.estado = 1 
				$where_txn 
				$where_cliente 
			ORDER BY tct.id DESC
		";
	$result["consulta_query"] = $query_txn;
	$list_query = $mysqli->query($query_txn);
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar la transacción.";
	} else {
		$list_txn= array();
		while ($li = $list_query->fetch_assoc()) {
			$list_txn[] = $li;
		}
		if (count($list_txn) > 0) {
			$result = rollback_transaccion($list_txn[0]["cliente_id"], $list_txn[0]["id"], $usuario_id, $turno_id, $observacion,0);
		} else {
			$result["http_code"] = 400;
			$result["status"] = "La transacción no existe.";
			$result["result"] = $list_txn;
		}
	}

	//$result = rollback_transaccion($id_cliente, $id_trans, $usuario_id, $turno_id, $observacion);


}







//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************
// TORITO
//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************

//*******************************************************************************************************************
// OBTENER LINK TORITO
//*******************************************************************************************************************
if ($_POST["accion"] === "generar_url_torito") {

	$client_id     = $_POST["id_cliente"];
	$balance_tipo  = $_POST["balance_tipo"];
	$balance_monto = $_POST["balance_monto"];

	$balance_bd = 0;

	$list_balance = array();
	$list_balance = obtener_balances($client_id);

	if(count($list_balance)>0){
		if((int)$balance_tipo===1){
			$balance_bd = $list_balance[0]["balance"];
		}
		if((int)$balance_tipo===6){
			$balance_bd = $list_balance[0]["balance_dinero_at"];
		}
	}

	if((double)$balance_monto !== (double)$balance_bd){
		$result["http_code"] = 400;
		$result["status"] = "El balance no coincide con el de la Base de datos, F5 por favor.";
		echo json_encode($result);exit();
	}

	if(!((double)$balance_monto >= 2)){
		$result["http_code"] = 400;
		$result["status"] = "El monto del balance debe ser mayor o igual a 2 soles.";
		echo json_encode($result);exit();
	}

	/*
	if((int)$turno_local_id === 0){
		$result["http_code"] = 400;
		$result["status"] = "Por favor debe abrir turno.";
		echo json_encode($result);exit();
	}
	*/

    date_default_timezone_set("America/Lima");
    $var_timestamp = time();
    //$var_randomstring = substr(md5($var_timestamp),0,10);
    $var_randomstring = substr(md5(strval($usuario_id) . strval(rand(1000000000, 9999999999)) ),0,10);
    $var_seed         = env('TLS_TORITO_SEED');

    $var_partnertoken = $var_randomstring . $var_timestamp . hash('sha256', $var_randomstring . $var_timestamp . $var_seed);
    $var_idpartner    = env('TLS_TORITO_PARTNER');
    //$var_idstore= $login['cc_id'];
    $var_idstore      = str_pad($cc_id, 4, "0", STR_PAD_LEFT);
    $var_idcashier    = $usuario_id;
    $var_terminal     = $turno_local_id;
    $cashierfirstname = $usuario_nombre;
    $cashierlastname  = $usuario_ape_pat;

    $url=env('TLS_TORITO_URL').'?';
    $url.='idpartner='.$var_idpartner.'&';
    $url.='idstore='.$var_idstore.'&';
    $url.='idcashier='.$var_idcashier.'&';
    $url.='idterminal='.$var_terminal.'&';
    $url.='cashierfirstname='.$cashierfirstname.'&';
    $url.='cashierlastname='.$cashierlastname.'&';
    $url.='partnertoken='.$var_partnertoken;

    $query_insert = "
        INSERT INTO tbl_torito_acceso (
            `idpartner`,
            `idred`,
            `idstore`,
            `idcashier`,
            `idterminal`,
            `idexternal`,
            `cashierfirstname`,
            `cashierlastname`,
            `partnertoken`,
            `turno_id`,
            `status`,
            `created_at`
        ) VALUES (
            '". $var_idpartner ."',
            '". $turno_red_id ."',
            '". $var_idstore ."',
            '". $var_idcashier ."',
            '". $var_terminal ."',
            '". $client_id ."',
            '". $cashierfirstname ."',
            '". $cashierlastname ."',
            '". $var_partnertoken ."',
            '". $turno_id ."',
            '1',
            now()
        ); ";
    $mysqli->query($query_insert);
    if ($mysqli->error) {
		$result["turno_red_id"] = $turno_red_id;
		$result["consulta_error"] = $mysqli->error;
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar la transacción.";
		$result['query'] = $query_insert;
		echo json_encode($result);exit();
	} 

	$result["http_code"] = 200;
	$result["status"] = "ok";
	$result["result"] = $url;

}



//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************
// CALIMACO
//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************


//*******************************************************************************************************************
// OBTENER LINK CALIMACO
//*******************************************************************************************************************
if ($_POST["accion"] === "generar_url_calimaco") {

	$client_id     = $_POST["id_cliente"];
	$calimaco_id   = $_POST["calimaco_id"];
	$balance_tipo  = $_POST["balance_tipo"];
	$balance_monto = $_POST["balance_monto"];
	$timestamp     = $_POST["timestamp"];

	$balance_bd = 0;
	
	if(strlen($calimaco_id)===0){
		$calimaco_id = api_calimaco_get_calimaco_id($client_id);
		$result["calimaco_id_nuevo"] = $calimaco_id;
	}

	$list_balance = array();
	$list_balance = obtener_balances($client_id);
	
	if(count($list_balance)>0){
		if((int)$balance_tipo===1){
			$balance_bd = $list_balance[0]["balance"];
		}
		if((int)$balance_tipo===6){
			$balance_bd = $list_balance[0]["balance_dinero_at"];
		}
	}

	if((double)$balance_monto === (double)$balance_bd){
		$url = api_calimaco_get_url_sportbook($client_id, $balance_monto, $timestamp, $calimaco_id);

		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $url;
		$result["calimaco_id"] = $calimaco_id;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "El balance no coincide con el de la Base de datos, F5 por favor.";
	}


}

//*******************************************************************************************************************
// PORTAL CALIMACO - REGISTRAR APUESTA LOG
//*******************************************************************************************************************
if ($_POST["accion"] === "enviar_tipo_balance_calimaco") {

	$id_cliente = $_POST["id_cliente"];
	$tipo_balance_id = $_POST["tipo_balance_id"];

	$insert_command = "
		INSERT INTO tbl_televentas_clientes_tipo_balance (
			client_id,
			tipo_balance_id,
			user_id,
			created_at
		) VALUES (
			$id_cliente,
			$tipo_balance_id,
			$usuario_id,
			NOW()
		)";
	$mysqli->query($insert_command);
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["insert_error"] = $mysqli->error;
		$result["query"] = $insert_command;
		$result["status"] = "Error al registrar el tipo de balance.";
		echo json_encode($result);exit();
	}else{
		$result["http_code"] = 200;
		$result["status"] = "Registro exitoso del tipo de balance.";
	}

}

//*******************************************************************************************************************
// PORTAL CALIMACO - REGISTRAR APUESTA LOG
//*******************************************************************************************************************
if ($_POST["accion"] === "portal_calimaco_registrar_apuesta_log") {

	$id_cliente     = $_POST["id_cliente"];
	$balance_tipo   = $_POST["balance_tipo"];
	$id_bet         = $_POST["id_bet"];
	$monto          = $_POST["monto"];
	$array_placebet = (json_decode($_POST["place_bet"], true))[0];
	$calimaco_id    = substr($array_placebet["ExtId"], 2);

	$result["id_bet"] = $id_bet;
	$result["amount"] = $monto;


	// Calimaco_id
	$query_c = "
		SELECT c.id client_id
		FROM tbl_televentas_clientes c 
		WHERE c.calimaco_id = '" . $calimaco_id . "' 
		";
	$list_query_c = $mysqli->query($query_c);
	$list_cli = array();
	if ($mysqli->error) {
	} else {
		while ($li_cli = $list_query_c->fetch_assoc()) {
			$list_cli[] = $li_cli;
		}
		//$result["list_cli"] = $list_cli;
		if (count($list_cli) == 1) {
			$id_cliente = $list_cli[0]["client_id"];
		}
	}

	$insert_command = "
		INSERT INTO tbl_televentas_api_calimaco_response (
			proveedor_id,
			method,
			bet_id,
			client_id,
			response,
			turno_id,
			cc_id,
			status,
			user_id,
			created_at
		) VALUES (
			5,
			'event_placebet',
			'$id_bet',
			'$id_cliente',
			'" . json_encode($array_placebet) . "',
			'$turno_id',
			'',
			'1',
			'$usuario_id',
			now()
		)";
	$mysqli->query($insert_command);
}


//*******************************************************************************************************************
// PORTAL CALIMACO - REGISTRAR APUESTA
//*******************************************************************************************************************
if ($_POST["accion"] === "portal_calimaco_registrar_apuesta") {

	$id_cliente     = $_POST["id_cliente"];
	$balance_tipo   = $_POST["balance_tipo"];
	$evento_dineroat_id = $_POST["evento_dineroat_id"];
	$id_bet         = $_POST["id_bet"];
	$monto          = $_POST["monto"];
	$array_placebet = (json_decode($_POST["place_bet"], true))[0];
	$calimaco_id    = substr($array_placebet["ExtId"], 2);

	$result["id_bet"] = $id_bet;
	$result["amount"] = $monto;

	if ( (int)$_POST["evento_dineroat_id"] === 0 ) {
		$evento_dineroat_id = 'null';
	}

	// Calimaco_id
	$query_c = "
		SELECT c.id client_id
		FROM tbl_televentas_clientes c 
		WHERE c.calimaco_id = '" . $calimaco_id . "' 
		";
	$list_query_c = $mysqli->query($query_c);
	$list_cli = array();
	if ($mysqli->error) {
	} else {
		while ($li_cli = $list_query_c->fetch_assoc()) {
			$list_cli[] = $li_cli;
		}
		//$result["list_cli"] = $list_cli;
		if (count($list_cli) == 1) {
			$id_cliente = $list_cli[0]["client_id"];
		}
	}

	$insert_command = "
		INSERT INTO tbl_televentas_api_calimaco_response (
			method,
			bet_id,
			client_id,
			response,
			turno_id,
			cc_id,
			status,
			user_id,
			created_at
		) VALUES (
			'event_placebet',
			'" . $id_bet . "',
			'" . $id_cliente . "',
			'" . json_encode($array_placebet) . "',
			'0',
			'',
			'1',
			'" . $usuario_id . "',
			now()
		)";
	$mysqli->query($insert_command);

	$bet_is_bonus = 0;
	$array_bet_calimaco = api_calimaco_get_bet($id_cliente, $id_bet);
	$result["array_bet_calimaco"] = $array_bet_calimaco;
	if(isset($array_bet_calimaco["result"])){
		if($array_bet_calimaco["result"]==="OK") {
			if($array_bet_calimaco["bet"]["is_bonus"]===true){
				$bet_is_bonus = 1;
			}
		} else {
			$result["http_code"] = 400;
			$result["status"] = "El Proveedor indicó que la puesta no existe. *";
			$result["result_api"] = $array_bet_calimaco;
			echo json_encode($result);exit();
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] = "El Proveedor indicó que la puesta no existe.";
		$result["result_api"] = $array_bet_calimaco;
		echo json_encode($result);exit();
	}

	if($bet_is_bonus === 0) { // SI NO ES FREEBET
		$query = " 
			UPDATE tbl_televentas_clientes_balance 
			SET
				balance = balance - $monto,
				updated_at = now()
			WHERE cliente_id = " . $id_cliente . " AND tipo_balance_id = $balance_tipo 
			";
		$mysqli->query($query);
	}

	$proveedor_id = 5;
	$query_1 = "
		SELECT 
			ct.id, 
			ct.estado, 
			ct.monto, 
			ct.api_id proveedor_id, 
			a.name proveedor_name 
		FROM tbl_televentas_clientes_transaccion ct
		join tbl_televentas_proveedor a on a.id = ct.api_id
		WHERE ct.txn_id = '" . $id_bet . "' 
			AND ct.api_id = '" . $proveedor_id . "' 
			AND ct.tipo_id = 4 
			AND ct.estado in (0,1)
		";
	$list_query = $mysqli->query($query_1);
	$list_1 = array();
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["select_error"] = $mysqli->error;
		$result["select_query"] = $query_1;
		//echo json_encode($result);exit();
	} else {
		while ($li_1 = $list_query->fetch_assoc()) {
			$list_1[] = $li_1;
		}
		if (count($list_1) == 0) {
			$insert_command = "
				INSERT INTO tbl_televentas_clientes_transaccion (
					tipo_id,
					api_id,
					txn_id,
					cliente_id,
					user_id,
					turno_id,
					cc_id,
					monto,
					nuevo_balance,
					bono_monto,
					id_tipo_balance,
					evento_dineroat_id,
					estado,
					created_at
				) VALUES (
					4,
					'" . $proveedor_id . "',
					'" . $id_bet . "',
					'" . $id_cliente . "',
					'" . $usuario_id . "',
					'" . $turno_id . "',
					'" . $cc_id . "',
					'" . $monto . "',
					0,
					0,
					'" . $balance_tipo . "',
					$evento_dineroat_id,
					1,
					now()
				)";
			$mysqli->query($insert_command);
			if ($mysqli->error) {
				//$result["insert_query"] = $insert_command;
				$result["insert_error"] = $mysqli->error;
			}
			$query_3 = "
				SELECT id 
				FROM tbl_televentas_clientes_transaccion 
				WHERE tipo_id=4 
					AND api_id='" . $proveedor_id . "' 
					AND txn_id='" . $id_bet . "' 
					AND cliente_id='" . $id_cliente . "' 
					AND user_id='" . $usuario_id . "' 
					AND turno_id='" . $turno_id . "' 
					AND cc_id='" . $cc_id . "' 
					AND id_tipo_balance='" . $balance_tipo . "' 
					AND estado='1' 
				";
			$list_query = $mysqli->query($query_3);
			$list_transaccion = array();
			while ($li = $list_query->fetch_assoc()) {
				$list_transaccion[] = $li;
			}
			if (count($list_transaccion) == 0) {
				$result["http_code"] = 400;
				$result["status"] = "Se registro la apuesta, sin embargo no se pudo guardar la transacción.";
				//$result["list_transaccion"] = $list_transaccion;
				//$result["query"] = $query_3;
			} elseif (count($list_transaccion) === 1) {

				if($bet_is_bonus === 0) { // SI NO ES FREEBET
					$list_balance = array();
					$list_balance = obtener_balances($id_cliente);

					if (count($list_balance) === 1) {

						if ((int)$balance_tipo === 1) {

							$balance_total_actual = (double)$list_balance[0]["balance"];
							$balance_deposito_actual = (double)$list_balance[0]["balance_deposito"];
							$balance_retiro_actual = (double)$list_balance[0]["balance_retiro_disponible"];

							$temp_monto = (double)$monto;
							//$balance_total_nuevo = (double)$balance_total_actual - (double)$monto;
							$balance_deposito_nuevo = (double)$list_balance[0]["balance_deposito"];
							$balance_retiro_nuevo = (double)$list_balance[0]["balance_retiro_disponible"];

							//*********************** BALANCE PRINCIPAL
							//query_tbl_televentas_clientes_balance('update', $id_cliente, 1, $balance_total_nuevo);
							query_tbl_televentas_clientes_balance_transaccion('insert', $list_transaccion[0]['id'], $id_cliente, 
								1, ((double)$balance_total_actual + (double)$monto), $temp_monto, $balance_total_actual);

							//*********************** BALANCE DEPOSITO
							if((double)$balance_deposito_actual>0){
								if((double)$balance_deposito_nuevo>(double)$temp_monto){
									$query_update_4 = " 
										UPDATE tbl_televentas_clientes_balance 
										SET
											balance = balance - $temp_monto,
											updated_at = now()
										WHERE cliente_id = $id_cliente AND tipo_balance_id = 4 
										";
									$balance_deposito_nuevo = (double)$balance_deposito_nuevo-(double)$temp_monto;
									$temp_monto = 0;
								} else {
									$temp_monto = (double)$temp_monto-(double)$balance_deposito_nuevo;
									$balance_deposito_nuevo = 0;
									$query_update_4 = " 
										UPDATE tbl_televentas_clientes_balance 
										SET
											balance = 0,
											updated_at = now()
										WHERE cliente_id = $id_cliente AND tipo_balance_id = 4 
										";
								}
								$mysqli->query($query_update_4);
								//query_tbl_televentas_clientes_balance('update', $id_cliente, 4, $balance_deposito_nuevo);
								$temp_monto_tipo4 = (double)$balance_deposito_actual-(double)$balance_deposito_nuevo;
								query_tbl_televentas_clientes_balance_transaccion('insert', $list_transaccion[0]['id'], $id_cliente, 
									4, $balance_deposito_actual, $temp_monto_tipo4, $balance_deposito_nuevo);
							}

							//*********************** BALANCE RETIRO
							if((double)$temp_monto>0){
								if((double)$balance_retiro_actual>0){
									if((double)$balance_retiro_nuevo>(double)$temp_monto){
										$query_update_5 = " 
											UPDATE tbl_televentas_clientes_balance 
											SET
												balance = balance - $temp_monto,
												updated_at = now()
											WHERE cliente_id = $id_cliente AND tipo_balance_id = 5 
											";
										$balance_retiro_nuevo = (double)$balance_retiro_nuevo-(double)$temp_monto;
										$temp_monto = 0;
									} else {
										$temp_monto = (double)$temp_monto-(double)$balance_retiro_nuevo;
										$balance_retiro_nuevo = 0;
										$query_update_5 = " 
											UPDATE tbl_televentas_clientes_balance 
											SET
												balance = 0,
												updated_at = now()
											WHERE cliente_id = $id_cliente AND tipo_balance_id = 5 
											";
									}
									$mysqli->query($query_update_5);
									//query_tbl_televentas_clientes_balance('update', $id_cliente, 5, $balance_retiro_nuevo);
									$temp_monto_tipo5 = (double)$balance_retiro_actual-(double)$balance_retiro_nuevo;
									query_tbl_televentas_clientes_balance_transaccion('insert', $list_transaccion[0]['id'], $id_cliente, 
										5, $balance_retiro_actual, $temp_monto_tipo5, $balance_retiro_nuevo);
								}
							}

							$query = " 
								UPDATE tbl_televentas_clientes_transaccion 
								SET nuevo_balance = '" . $balance_total_actual . "', 
									turno_id = '" . $turno_id . "', 
									cc_id = '" . $cc_id . "' 
								WHERE id = '" . $list_transaccion[0]['id'] . "' 
								";
							$mysqli->query($query);
						}


						if ((int)$balance_tipo === 6) {
							$balance_dinero_at_actual = (double)$list_balance[0]["balance_dinero_at"];

							//*********************** BALANCE PRINCIPAL
							query_tbl_televentas_clientes_balance_transaccion('insert', $list_transaccion[0]['id'], $id_cliente, 
								6, ((double)$balance_dinero_at_actual + (double)$monto), $monto, $balance_dinero_at_actual);

								$query = " 
									UPDATE tbl_televentas_clientes_transaccion 
									SET nuevo_balance = '" . $balance_dinero_at_actual . "', 
										turno_id = '" . $turno_id . "', 
										cc_id = '" . $cc_id . "' 
									WHERE id = '" . $list_transaccion[0]['id'] . "' 
									";
								$mysqli->query($query);
						}

					}
				}

				$result["http_code"] = 200;
				$result["status"] = "ok";
				$result["amount"] = $monto;
				$result["list"] = $list_transaccion;
			} else {
				$result["http_code"] = 400;
				$result["status"] = "Se registro la apuesta, sin embargo ocurrió un error al guardar la transacción.";
				//$result["list_transaccion"] = $list_transaccion;
				//$result["query"] = $query_3;
			}
		} elseif (count($list_1) > 0) {
			$estado = $list_1[0]["estado"];
			if ((int) $estado === 0) {
				$query_2 = " 
					UPDATE tbl_televentas_clientes_transaccion 
					SET
						turno_id = " . $turno_id . ",
						update_user_id = " . $usuario_id . ",
						updated_at = now()
					WHERE txn_id = '" . $id_bet . "' AND tipo_id='4' AND api_id='" . $proveedor_id . "' ";
				$mysqli->query($query_2);

				$result["http_code"] = 200;
				$result["status"] = "ok";
				$result["amount"] = $list_1[0]["monto"];
				$result["proveedor_name"] = $list_1[0]["proveedor_name"];
				$result["result"] = $list_1[0];
			} else {
				$result["http_code"] = 400;
				$result["status"] = "Apuesta ya registrada";
				$result["amount"] = $list_1[0]["monto"];
				$result["proveedor_name"] = $list_1[0]["proveedor_name"];
			}
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al consultar la apuesta.";
		}
	}

	if($bet_is_bonus === 0) { // SI NO ES FREEBET
		if((int)$result["http_code"]===400){
			$query = " 
				UPDATE tbl_televentas_clientes_balance 
				SET
					balance = balance + $monto,
					updated_at = now()
				WHERE cliente_id = " . $id_cliente . " AND tipo_balance_id = $balance_tipo 
				";
			$mysqli->query($query);
		}
	}

}



//*******************************************************************************************************************
// OBTENER APUESTA ALTENAR
//*******************************************************************************************************************
if ($_POST["accion"] === "obtener_apuesta_altenar") {

	$id_cliente = $_POST["id_cliente"];
	$id_bet = $_POST["id_bet"];

	$array_bet_calimaco = array();
	$result["http_code"] = 400;
	$result["status"] = "Ticket no encontrado.";
	$result["status_color"] = "";
	$result["result_calimaco"] = $array_bet_calimaco;
	$result["ganacia_estimada"] = 0;
	$result["total_cuota"] = 0;

	try {
		$array_bet_calimaco = api_calimaco_get_bet($id_cliente, $id_bet);
		if(isset($array_bet_calimaco["result"])){
			if($array_bet_calimaco["result"]==="OK") {
				$result["http_code"] = 200;
				//$result["ticket"] = $array_bet_calimaco["ticket"];
				//$result["ticket_query"] = $array_bet_calimaco["ticket_query"];

				$res_calimaco_get_status = calimaco_get_status($array_bet_calimaco["bet"]["status"]);

				$result["status"] = $res_calimaco_get_status["status"];
				$result["status_color"] = $res_calimaco_get_status["color"];
				$i=0;
				foreach($array_bet_calimaco["bet"]["selections"] as $selection) {
					//"event_date": "2022-08-09 15:00:00",
					$array_bet_calimaco["bet"]["selections"][$i]["event_date"] = date('d/m/Y H:i', (strtotime ('-5 hour', strtotime($selection["event_date"]))));
					$i++;
				}
				$result["result_calimaco"] = $array_bet_calimaco["bet"]["selections"];
				if((float)$array_bet_calimaco["bet"]["total_prize"]>0 && (float)$array_bet_calimaco["bet"]["wager"]>0){
					$result["ganacia_estimada"] = $array_bet_calimaco["bet"]["total_prize"]/100;
					$result["total_cuota"] = ($array_bet_calimaco["bet"]["total_prize"]/$array_bet_calimaco["bet"]["wager"]);
				} else {
					$result["ganacia_estimada"] = 0;
					$result["total_cuota"] = 0;
				}
			}
		}
	} catch (Exception $e) {
		
	}

}



//*******************************************************************************************************************
//*******************************************************************************************************************
// GUARDAR RECARGAR WEB CALIMACO
//*******************************************************************************************************************
//*******************************************************************************************************************
if ($_POST["accion"] === "guardar_transaccion_recarga_web2_BCK_20230803") {
	$id_cliente     = $_POST["id_cliente"];
	$idweb          = $_POST["idweb"];
	$idweb_c        = $_POST["idweb_c"];
	$monto          = (double)$_POST["monto"];
	$bono           = (double)$_POST["bono"];
	$id_deposito    = $_POST["id_deposito"];
	$timestamp      = $_POST["timestamp"];
	$total_recarga  = 0;
	//$cc_id          = 0;

	if(!((float) $monto >= 2.00 && (float) $monto <= 50000.00 )){
		$result["http_code"] = 400;
		$result["status"] = "El monto debe ser mínimo de 2.00 y máximo de 50,000.00.";
		echo json_encode($result);exit();
	}

	if (!(((float) $monto > 0) && ((float) $monto > (float) $bono))) {
		$result["http_code"] = 400;
		$result["status"] = "Montos incorrectos.";
		echo json_encode($result);exit();
	}

	$nuevo_monto = 0;
	$nuevo_bono = 0;
	$bono_id = 0;

	if((double)$bono > 0){
		$query_1 = "
			SELECT
				trans.id AS codigo,
				IFNULL(trans.monto, 0) monto,
				IFNULL(trans.bono_id, 0) bono_id,
				IFNULL(trans.bono_monto, 0) bono_monto,
				ba.balance,
				rb.codigo AS codigo_bono
			FROM
				tbl_televentas_clientes_transaccion trans
				JOIN tbl_televentas_clientes_balance ba ON ba.cliente_id=trans.cliente_id AND tipo_balance_id = 1 
				 JOIN tbl_televentas_recargas_bono rb ON trans.bono_id = rb.id
			WHERE
				trans.id = $id_deposito 
				AND trans.tipo_id = 1 
				AND trans.estado = 1 
				AND trans.created_at > date_add(NOW(), INTERVAL -1 DAY) 
				AND ( trans.valid_bono = 0 or trans.valid_bono is null )
			ORDER BY
				trans.id DESC 
			LIMIT 1
		";
		$list_query = $mysqli->query($query_1);
		$list_transaccion_dep = array();
		if ($mysqli->error) {
			$result["consulta_query"] = $query_1;
			$result["consulta_error"] = $mysqli->error;
		} else {
			while ($li = $list_query->fetch_assoc()) {
				$list_transaccion_dep[] = $li;
				$bono_name = $li["codigo_bono"];
				$bono_id = $li["bono_id"];
				$nuevo_monto = $li["monto"];
				$nuevo_bono = $li["bono_monto"];
			}
		}

		$result["http_code_test"] = 200;
		$result["result_test"] = $list_transaccion_dep;

		if (!((float)$bono <= (float)$nuevo_bono)) {
			$result["http_code"] = 400;
			$result["status"] = "Montos del bono inválido.";
			echo json_encode($result);
			exit();
		}
	}

	$total_recarga = $monto;

	// BALANCE BILLETERO
	$list_balance = array();
	$list_balance = obtener_balances($id_cliente);

	if(count($list_balance) == 0){
		$result["http_code"] = 400;
		$result["status"] = "Usted no tiene balance.";
		$result["result"] = $list_balance;
	} else if(count($list_balance) > 0){
		$balance_actual            = (double)$list_balance[0]["balance"];
		$balance_bono_disponible   = (double)$list_balance[0]["balance_bono_disponible"];
		$balance_bono_utilizado    = (double)$list_balance[0]["balance_bono_utilizado"];
		$balance_deposito          = (double)$list_balance[0]["balance_deposito"];
		$balance_disponible_retiro = (double)$list_balance[0]["balance_retiro_disponible"];

		$nuevo_balance_deposito    = 0;
		$nuevo_balance_retiro      = 0;

		if (!((double) $balance_actual > 0)) {
			$result["http_code"] = 400;
			$result["status"] = "El balance esta en 0.";
			$result["result"] = $list_balance;
			echo json_encode($result);exit();
		}
		if (!((double) $balance_actual >= (double) $monto)) {
			$result["http_code"] = 400;
			$result["status"] = "El balance es menor al total a recargar.";
			$result["result"] = $list_balance;
			echo json_encode($result);exit();
		}

		//////////////////////////////////VALIDACION USUARIO
		$array_user = array();
		$array_user = api_calimaco_check_user($id_cliente, $idweb);
		$result["array_user"] = $array_user; echo json_encode($result); exit();
		if(isset($array_user["result"])){
			if($array_user["result"]==="OK") {
				//********* VALIDACION BALANCE
				if ((double) $balance_bono_disponible >= (double) $bono) {
					//*********VALIDACIÓN SOLICITUDES DE RECARGA PENDIENTES
					$cmd_sol_pend = "
						SELECT 
							id 
						FROM tbl_televentas_clientes_transaccion 
						WHERE tipo_id = 35 
						AND cliente_id = '$id_cliente' 
						AND web_id = '$idweb'
						AND estado = 0";
					$list_pend_query = $mysqli->query($cmd_sol_pend);
					$list_pend_transactions = array();
					while ($li = $list_pend_query->fetch_assoc()) {
						$list_pend_transactions[] = $li;
					}

					if (count($list_pend_transactions) > 0) {
						$result["http_code"] = 400;
						$result["status"] = "El cliente cuenta con solicitud de recarga pendiente";
						$result["result"] = $list_pend_transactions;
						echo json_encode($result);exit();
					}else{
						//********* VALIDACION BALANCE
						$fecha_hora_actual = date('Y-m-d H:i:s');
						$balance_nuevo     = (double) number_format(($balance_actual - $monto),2,".","");

						//Guardar Solicitud
						$ins_solicitud_recarga = "
							INSERT INTO tbl_televentas_clientes_transaccion (
								tipo_id,
								cliente_id,
								turno_id,
								cc_id,
								web_id, 
								api_id,
								bono_id,
								monto,
								bono_monto,
								total_recarga,
								nuevo_balance,
								estado,
								user_id,
								created_at,
								transaccion_id
							) VALUES (
								35,
								'$id_cliente',
								'$turno_id',
								'$cc_id',
								'$idweb',
								2,
								'$bono_id',
								$monto,
								0,
								$total_recarga,
								$balance_nuevo,
								0,
								$usuario_id,
								'$fecha_hora_actual',
								$id_deposito
							)";
						$mysqli->query($ins_solicitud_recarga);

						$cmd_verifi_solicitud = "
							SELECT 
								id 
							FROM tbl_televentas_clientes_transaccion 
							WHERE tipo_id = 35 
							AND cliente_id = '$id_cliente' 
							AND user_id = '$usuario_id' 
							AND turno_id = '$turno_id' 
							AND web_id = '$idweb' 
							AND nuevo_balance = '$balance_nuevo' 
							AND monto = '$monto' 
							AND total_recarga = '$total_recarga' 
							AND created_at = '$fecha_hora_actual'
							";
						$list_query = $mysqli->query($cmd_verifi_solicitud);
						
						$list_transaccion = array();
						while ($li = $list_query->fetch_assoc()) {
							$list_transaccion[] = $li;
						}
						
						if (count($list_transaccion) == 0) {
							$result["http_code"] = 400;
							$result["status"] = "No se pudo completar la solicitud de recarga.";
						} elseif (count($list_transaccion) === 1) {
							$operationId = $list_transaccion[0]["id"];

							//*********************** BALANCE TOTAL
							query_tbl_televentas_clientes_balance('update', $id_cliente, 1, $balance_nuevo);
							query_tbl_televentas_clientes_balance_transaccion('insert', $operationId, 
								$id_cliente, 1, $balance_actual, $monto, $balance_nuevo);

							$monto_restante = (double)$monto;

							//*********************** BALANCE DEPOSITO
							if((double)$balance_deposito>0){
								$balance_deposito_nuevo = (double)$balance_deposito;

								if((double)$balance_deposito>(double)$monto_restante){
									$balance_deposito_nuevo = (double)$balance_deposito_nuevo-(double)$monto_restante;
									$monto_restante = 0;
								} else {
									$monto_restante = (double)$monto_restante-(double)$balance_deposito_nuevo;
									$balance_deposito_nuevo = 0;
								}
								query_tbl_televentas_clientes_balance('update', $id_cliente, 4, $balance_deposito_nuevo);

								query_tbl_televentas_clientes_balance_transaccion('insert', $operationId, $id_cliente, 4, $balance_deposito, 
									((double)$balance_deposito-(double)$balance_deposito_nuevo), $balance_deposito_nuevo);
							}

							//*********************** BALANCE RETIRO
							if((double)$monto_restante>0){
								$balance_retiro_nuevo = (double)$balance_disponible_retiro;

								if((double)$balance_retiro_nuevo>(double)$monto_restante){
									$balance_retiro_nuevo = $balance_retiro_nuevo-$monto_restante;
									$monto_restante = 0;
								} else {
									$monto_restante = (double)$monto_restante-(double)$balance_retiro_nuevo;
									$balance_retiro_nuevo = 0;
								}
								query_tbl_televentas_clientes_balance('update', $id_cliente, 5, $balance_retiro_nuevo);

								query_tbl_televentas_clientes_balance_transaccion('insert', $operationId, $id_cliente, 5, $balance_disponible_retiro, 
									((double)$balance_disponible_retiro-(double)$balance_retiro_nuevo), $balance_retiro_nuevo);
							}

							$array_recarga_c = array();
							$array_recarga_c = api_calimaco_recarga_web($id_cliente, $idweb, $monto * 100, $operationId);
							if($array_recarga_c != ''){
								if(isset($array_recarga_c["result"])){
									if($array_recarga_c["result"]==="OK") {

										$cmd_transaccion = " UPDATE tbl_televentas_clientes_transaccion 
											SET txn_id = '" . $array_recarga_c["operationId"] . "',
											estado = 1
											WHERE id = " . $operationId;
										$mysqli->query($cmd_transaccion);
										$fec_hora_recarga = date('Y-m-d H:i:s');
										$fecha_hora_actual_rec = date('Y-m-d H:i:s', strtotime($fec_hora_recarga . ' +1 second'));


										//Guardar Transacción
										$insert_command = "
											INSERT INTO tbl_televentas_clientes_transaccion (
												tipo_id,
												cliente_id,
												turno_id,
												cc_id,
												web_id, 
												api_id,
												bono_id,
												monto,
												txn_id,
												bono_monto,
												total_recarga,
												nuevo_balance,
												estado,
												user_id,
												created_at,
												transaccion_id
											) VALUES (
												2,
												'$id_cliente',
												'$turno_id',
												'$cc_id',
												'$idweb',
												2,
												'$bono_id',
												$monto,
												'" . $array_recarga_c["operationId"] . "',
												0,
												$total_recarga,
												$balance_nuevo,
												1,
												$usuario_id,
												'$fecha_hora_actual_rec',
												$operationId
											)";
										$mysqli->query($insert_command);

										$query_3 = "
											SELECT 
												id 
											FROM tbl_televentas_clientes_transaccion 
											WHERE tipo_id = 2 
											AND cliente_id = '$id_cliente' 
											AND user_id = '$usuario_id' 
											AND turno_id = '$turno_id' 
											AND web_id = '$idweb' 
											AND nuevo_balance = '$balance_nuevo' 
											AND monto = '$monto' 
											AND total_recarga = '$total_recarga' 
											AND created_at = '$fecha_hora_actual_rec'
											";
										$list_query = $mysqli->query($query_3);

										// TRANSACCIÓN DEL BONO
										$txn_id_bono = 0;

										if ((double) $bono > 0) {
											$amount = $monto * 100; //se le envía el monto de la recarga, la api de calimaco hace el cálculo

											$array_bono_c = array();
											$array_bono_c = api_calimaco_asig_bono($id_cliente, $idweb, $bono_name, $amount, $timestamp);
											
											if(isset($array_bono_c["result"])){
												if($array_bono_c["result"] === "OK"){
													$txn_id_bono = $bono_id;

													// Bono Actual
													$balance_bono_disponible_NUEVO = $balance_bono_disponible - $bono;

													query_tbl_televentas_clientes_balance('update', $id_cliente, 2, $balance_bono_disponible_NUEVO);

													query_tbl_televentas_clientes_balance_transaccion('insert', $operationId, 
														$id_cliente, 2, $balance_bono_disponible, $bono, $balance_bono_disponible_NUEVO);

													// Bono Utilizado
													$balance_bono_utilizado_NUEVO = $balance_bono_utilizado + $bono;

													query_tbl_televentas_clientes_balance('update', $id_cliente, 3, $balance_bono_utilizado_NUEVO);

													query_tbl_televentas_clientes_balance_transaccion('insert', $operationId, 
														$id_cliente, 3, $balance_bono_utilizado, $bono, $balance_bono_utilizado_NUEVO);

													//Actualizar Transacción del depósito
													$query_3 = " 
														UPDATE tbl_televentas_clientes_transaccion 
														SET
															valid_bono = 1,
															updated_at = now()
														WHERE id = " . $id_deposito;
													//$result["tbl_televentas_clientes_transaccion"] = $query_3;
													$mysqli->query($query_3);

													//Insertar Transacción de Bono tipo 10
													$insert_command = "
														INSERT INTO tbl_televentas_clientes_transaccion (
															tipo_id,
															cliente_id,
															turno_id,
															cc_id,
															web_id,
															api_id,
															bono_id,
															monto,
															bono_monto,
															total_recarga,
															nuevo_balance,
															estado,
															user_id,
															created_at,
															transaccion_id
														) VALUES (
															10,
															" . $id_cliente . ",
															" . $turno_id . ",
															" . $cc_id . ",
															" . $idweb . ",
															2,
															" . $bono_id . ",
															0,
															" . $bono . ",
															0,
															" . $balance_nuevo . ",
															1,
															" . $usuario_id . ",
															now(),
															" . $id_deposito . "
														)
													";
													$mysqli->query($insert_command);

													sec_tlv_asignar_etiqueta_test($id_cliente);

													$result["http_code"] = 200;
													$result["status"] = "Ok";
													$result["description"] = $array_bono_c["description"];
												}else{
													$result["http_code"] = 400;
													$result["status"] = "La API respondio un error.";
													$result["result"] = $array_bono_c;
												}
											}else{
												$result["http_code"] = 400;
												$result["status"] = "La api respondio un error.";
												$result["array_bono_c"] = $array_bono_c;
											}
										}else{
											sec_tlv_asignar_etiqueta_test($id_cliente);

											$result["http_code"] = 200;
											$result["status"] = "OK";
											$result["result"] = $array_recarga_c;
										}
									} elseif($array_recarga_c["result"]==="error") {
										//rollback_transaccion($id_cliente, $operationId, $usuario_id, $turno_id, 'La api respondio un error..',0);
										$result["http_code"] = 400;
										$result["status"] = $array_recarga_c["description"];
										$result["array_recarga_c"] = $array_recarga_c;
									} else {
										//rollback_transaccion($id_cliente, $operationId, $usuario_id, $turno_id, 'La respondio un valor ilegible.',0);
										$result["http_code"] = 400;
										$result["status"] = "La respondio un valor ilegible.";
										$result["array_recarga_c"] = $array_recarga_c;
									}
								} else {
									//rollback_transaccion($id_cliente, $operationId, $usuario_id, $turno_id, 'La api respondio un error..',0);
									$result["http_code"] = 400;
									$result["status"] = "La api respondio un error..";
								}
							}else{
								//rollback_transaccion($id_cliente, $operationId, $usuario_id, $turno_id, 'No se obtuvo respuesta de la API',0);
								$result["http_code"] = 400;
								$result["status"] = "No se obtuvo respuesta de la API";
							}
						}
						//*********************** VALIDACION BALANCE
					}
				} else {
					$result["http_code"] = 400;
					$result["status"] = "El balance de bono disponible es menor al bono a recargar.";
					$result["result"] = $list_balance;
				}
			}else{
				$result["http_code"] = 400;
				$result["status"] = "No se encuentra el ID WEB del cliente";
				$result["result"] = $array_user;
			}
		}else{
			$result["http_code"] = 400;
			$result["status"] = "La API respondio un error al validar el usuario";
		}
		//////////////////////////////////VALIDACION USUARIO
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar el balance.";
		$result["result"] = $list_balance;
	}
}

if ($_POST["accion"] === "sec_tlv_reenvio_recarga_web_BCK_20230803") {
	$trans_id       = $_POST["trans_id"];
	$id_cliente     = $_POST["cliente_id"];
	$idweb          = $_POST["idweb"];
	$timestamp      = $_POST["timestamp"];

	$nuevo_monto = 0;
	$nuevo_bono = 0;
	$bono_id = 0;
	$bono = 0;
	$operationId = 0;
	$id_deposito = 0;

	$query_transaccion = "
		SELECT 
			id,
			monto,
			bono_monto,
			estado,
			IFNULL(transaccion_id, 0) transaccion_id
		FROM tbl_televentas_clientes_transaccion 
		WHERE 
		id = " . $trans_id 
		. " AND estado = 0 ";
	$list_query_trans = $mysqli->query($query_transaccion);
	$list_transaccion_pending = array();
	while ($li = $list_query_trans->fetch_assoc()) {
		$list_transaccion_pending[] = $li;
	}

	if(count($list_transaccion_pending) == 0){
		$result["http_code"] = 400;
		$result["status"] = "No se encontró la solicitud pendiente, vuelva a intentarlo.";
		$result["result"] = $list_transaccion_pending;
		echo json_encode($result);exit();
	}else if(count($list_transaccion_pending) == 1){
		$bono        = $list_transaccion_pending[0]["bono_monto"];
		$monto       = $list_transaccion_pending[0]["monto"];
		$operationId = $list_transaccion_pending[0]["id"];
		$id_deposito = $list_transaccion_pending[0]["transaccion_id"];
	}else{
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar la solicitud de recarga.";
		$result["result"] = $list_transaccion_pending;
		echo json_encode($result);exit();
	}

	if((double)$bono > 0){
		$query_1 = "
			SELECT
				trans.id AS codigo,
				IFNULL(trans.monto, 0) monto,
				IFNULL(trans.bono_id, 0) bono_id,
				IFNULL(trans.bono_monto, 0) bono_monto,
				ba.balance,
				rb.codigo AS codigo_bono
			FROM
				tbl_televentas_clientes_transaccion trans
				JOIN tbl_televentas_clientes_balance ba ON ba.cliente_id=trans.cliente_id AND tipo_balance_id = 1 
				 JOIN tbl_televentas_recargas_bono rb ON trans.bono_id = rb.id
			WHERE
				trans.id = $id_deposito 
				AND trans.tipo_id = 1 
				AND trans.estado = 1 
				AND trans.created_at > date_add(NOW(), INTERVAL -1 DAY) 
				AND ( trans.valid_bono = 0 or trans.valid_bono is null )
			ORDER BY
				trans.id DESC 
			LIMIT 1
		";
		$list_query = $mysqli->query($query_1);
		$list_transaccion_dep = array();
		if ($mysqli->error) {
			$result["consulta_query"] = $query_1;
			$result["consulta_error"] = $mysqli->error;
		} else {
			while ($li = $list_query->fetch_assoc()) {
				$list_transaccion_dep[] = $li;
				$bono_name = $li["codigo_bono"];
				$bono_id = $li["bono_id"];
				$nuevo_monto = $li["monto"];
				$nuevo_bono = $li["bono_monto"];
			}
		}

		$result["http_code_test"] = 200;
		$result["result_test"] = $list_transaccion_dep;

		if (!((float)$bono <= (float)$nuevo_bono)) {
			$result["http_code"] = 400;
			$result["status"] = "Montos del bono inválido.";
			echo json_encode($result);
			exit();
		}
	}

	$total_recarga = $monto;

	// BALANCE BILLETERO
	$list_balance = array();
	$list_balance = obtener_balances($id_cliente);

	if(count($list_balance) == 0){
		$result["http_code"] = 400;
		$result["status"] = "Usted no tiene balance.";
		$result["result"] = $list_balance;
	} else if(count($list_balance) > 0){
		$balance_actual            = (double)$list_balance[0]["balance"];
		$balance_bono_disponible   = (double)$list_balance[0]["balance_bono_disponible"];
		$balance_bono_utilizado    = (double)$list_balance[0]["balance_bono_utilizado"];
		$balance_deposito          = (double)$list_balance[0]["balance_deposito"];
		$balance_disponible_retiro = (double)$list_balance[0]["balance_retiro_disponible"];

		$array_user = array();
		$array_user = api_calimaco_check_user($id_cliente, $idweb);
		$result["array_user"] = $array_user;
		if(isset($array_user["result"])){
			if($array_user["result"]==="OK") {
				$fecha_hora_actual = date('Y-m-d H:i:s');
				$balance_nuevo     = $balance_actual;

				$array_recarga_c = array();
				$array_recarga_c = api_calimaco_recarga_web($id_cliente, $idweb, $monto * 100, $operationId);
				$result["array_recarga_c"] = $array_recarga_c;
				//echo json_encode($result); exit();
				if($array_recarga_c != ''){
					if(isset($array_recarga_c["result"])){
						if($array_recarga_c["result"]==="OK") {

							$cmd_transaccion = " UPDATE tbl_televentas_clientes_transaccion 
								SET txn_id = '" . $array_recarga_c["operationId"] . "',
								estado = 1
								WHERE id = " . $operationId;
							$mysqli->query($cmd_transaccion);

							//Guardar Transacción
							$insert_command = "
								INSERT INTO tbl_televentas_clientes_transaccion (
									tipo_id,
									cliente_id,
									turno_id,
									cc_id,
									web_id, 
									api_id,
									bono_id,
									monto,
									bono_monto,
									total_recarga,
									nuevo_balance,
									estado,
									user_id,
									created_at,
									transaccion_id
								) VALUES (
									2,
									'$id_cliente',
									'$turno_id',
									'$cc_id',
									'$idweb',
									2,
									'$bono_id',
									$monto,
									0,
									$total_recarga,
									$balance_nuevo,
									1,
									$usuario_id,
									'$fecha_hora_actual',
									$operationId
								)";
							$mysqli->query($insert_command);

							$query_3 = "
								SELECT 
									id 
								FROM tbl_televentas_clientes_transaccion 
								WHERE tipo_id = 2 
								AND cliente_id = '$id_cliente' 
								AND user_id = '$usuario_id' 
								AND turno_id = '$turno_id' 
								AND web_id = '$idweb' 
								AND nuevo_balance = '$balance_nuevo' 
								AND monto = '$monto' 
								AND total_recarga = '$total_recarga' 
								AND created_at = '$fecha_hora_actual'
								";
							$list_query = $mysqli->query($query_3);

							// TRANSACCIÓN DEL BONO
							$txn_id_bono = 0;

							if ((double) $bono > 0) {
								$amount = $monto * 100; //se le envía el monto de la recarga, la api de calimaco hace el cálculo

								$array_bono_c = array();
								$array_bono_c = api_calimaco_asig_bono($id_cliente, $idweb, $bono_name, $amount, $timestamp);
								
								if(isset($array_bono_c["result"])){
									if($array_bono_c["result"] === "OK"){
										$txn_id_bono = $bono_id;

										// Bono Actual
										$balance_bono_disponible_NUEVO = $balance_bono_disponible - $bono;

										query_tbl_televentas_clientes_balance('update', $id_cliente, 2, $balance_bono_disponible_NUEVO);

										query_tbl_televentas_clientes_balance_transaccion('insert', $operationId, 
											$id_cliente, 2, $balance_bono_disponible, $bono, $balance_bono_disponible_NUEVO);

										// Bono Utilizado
										$balance_bono_utilizado_NUEVO = $balance_bono_utilizado + $bono;

										query_tbl_televentas_clientes_balance('update', $id_cliente, 3, $balance_bono_utilizado_NUEVO);

										query_tbl_televentas_clientes_balance_transaccion('insert', $operationId, 
											$id_cliente, 3, $balance_bono_utilizado, $bono, $balance_bono_utilizado_NUEVO);

										//Actualizar Transacción del depósito
										$query_3 = " 
											UPDATE tbl_televentas_clientes_transaccion 
											SET
												valid_bono = 1,
												updated_at = now()
											WHERE id = " . $id_deposito;
										//$result["tbl_televentas_clientes_transaccion"] = $query_3;
										$mysqli->query($query_3);

										//Insertar Transacción de Bono tipo 10
										$insert_command = "
											INSERT INTO tbl_televentas_clientes_transaccion (
												tipo_id,
												cliente_id,
												turno_id,
												cc_id,
												web_id,
												api_id,
												bono_id,
												monto,
												bono_monto,
												total_recarga,
												nuevo_balance,
												estado,
												user_id,
												created_at,
												transaccion_id
											) VALUES (
												10,
												" . $id_cliente . ",
												" . $turno_id . ",
												" . $cc_id . ",
												" . $idweb . ",
												2,
												" . $bono_id . ",
												0,
												" . $bono . ",
												0,
												" . $balance_nuevo . ",
												1,
												" . $usuario_id . ",
												now(),
												" . $id_deposito . "
											)
										";
										$mysqli->query($insert_command);

										sec_tlv_asignar_etiqueta_test($id_cliente);

										$result["http_code"] = 200;
										$result["status"] = "Ok";
										$result["description"] = $array_bono_c["description"];
									}else{
										$result["http_code"] = 400;
										$result["status"] = "La API respondio un error.";
										$result["result"] = $array_bono_c;
									}
								}else{
									$result["http_code"] = 400;
									$result["status"] = "La api respondio un error.";
									$result["array_bono_c"] = $array_bono_c;
								}
							}else{
								sec_tlv_asignar_etiqueta_test($id_cliente);

								$result["http_code"] = 200;
								$result["status"] = "OK";
								$result["result"] = $array_recarga_c;
							}
						} elseif($array_recarga_c["result"]==="error") {
							$result["http_code"] = 400;
							$result["status"] = $array_recarga_c["description"];
							$result["array_recarga_c"] = $array_recarga_c;
						} else {
							$result["http_code"] = 400;
							$result["status"] = "La respondio un valor ilegible.";
							$result["array_recarga_c"] = $array_recarga_c;
						}
					} else {
						$result["http_code"] = 400;
						$result["status"] = "La api respondio un error..";
					}
				}else{
					$result["http_code"] = 400;
					$result["status"] = "No se obtuvo respuesta de la API";
				}
			}else{
				$result["http_code"] = 400;
				$result["status"] = "No se encuentra el ID WEB del cliente";
				$result["result"] = $array_user;
			}
		}else{
			$result["http_code"] = 400;
			$result["status"] = "La API respondio un error al validar el usuario";
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar el balance.";
		$result["result"] = $list_balance;
	}
}


//*******************************************************************************************************************
// GUARDAR RECARGAR WEB CALIMACO NUEVO
//*******************************************************************************************************************
if ($_POST["accion"] === "guardar_transaccion_recarga_web2") {
	$id_cliente     = $_POST["id_cliente"];
	$idweb          = $_POST["idweb"];
	$monto          = (double)$_POST["monto"];
	$id_deposito    = $_POST["id_deposito"];
	$total_recarga  = 0;

	$result = [];

    try {
		$url = "https://api.apuestatotal.com/v2/teleservicios/calimaco/setRechargeWeb";
		$rq = [
			'client_id'  => $id_cliente,
			'web_id'     => $idweb,
			'amount'     => $monto,
			'deposit_id' => $id_deposito,
			'user_id'    => $usuario_id,
			'turno_id'   => $turno_id,
			'cc_id'      => $cc_id
		];
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
			$result["status"] = 'Error API';
			$result["result"] = "Ocurrió un error al consumir el API de AT.";
			$respuesta_calimaco = '';
			if(isset($response_arr["calimaco"])){
				$texto = strtolower($response_arr["calimaco"]);
				$frase = strtolower('Not Privileged');
				if (strpos($texto, $frase) !== false) {
					$respuesta_calimaco = "Cuenta cerrada, el cliente debe contactarse con atencion al cliente";
				}else{
					$respuesta_calimaco = $response_arr["calimaco"];
				}
			}
			$result["calimaco_response"] = $respuesta_calimaco;
			echo json_encode($result);exit();
		} else {
			$result = $response_arr;
		}
    } catch (Exception $e) {
		$result["http_code"] = 400;
		$result["status"] = 'Error TRY-CATCH';
		$result["result"] = "Ocurrió un error al consumir el API de Recarga Web";
		echo json_encode($result);exit();
    }	

}

if ($_POST["accion"] === "sec_tlv_reenvio_recarga_web") {
	$trans_id       = $_POST["trans_id"];
	$id_cliente     = $_POST["cliente_id"];
	$idweb          = $_POST["idweb"];

	$monto = 0;
	$query = "
		SELECT 
			id,
			monto
		FROM tbl_televentas_clientes_transaccion 
		WHERE tipo_id = 35 
		AND id = '$trans_id' 
		AND web_id = '$idweb'
		AND estado = 0
		";
	$list_query = $mysqli->query($query);
	$list_transaccion = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_transaccion[] = $li;
	}
	if (count($list_transaccion) === 1) {
		$monto = $list_transaccion[0]['monto'];
	}else {
		$result["http_code"] = 400;
		$result["query"] = $query;
		$result["status"] = "Ocurrió un error al consultar el reintento.";
		echo json_encode($result);exit();
	}

	if(!((double)$monto>0)){
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultrat el monto del reintento.";
		echo json_encode($result);exit();
	}

	$result = [];
    try {
		$url = "https://api.apuestatotal.com/v2/teleservicios/calimaco/setRechargeWeb_retry";
		$rq = [
			'operation_id' => $trans_id,
			'client_id'    => $id_cliente,
			'web_id'       => $idweb,
			'amount'       => $monto,
			'user_id'      => $usuario_id,
			'turno_id'     => $turno_id,
			'cc_id'        => $cc_id
		];
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
			$result["status"] = "Ocurrió un error al consumir el API de AT.";
			echo json_encode($result);exit();
		} else {
			$result = $response_arr;
		}
    } catch (Exception $e) {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consumir el API de AT";
		echo json_encode($result);exit();
    }	
}








//*******************************************************************************************************************
// GUARDAR TERMINAL DEPOSIT
//*******************************************************************************************************************
if ($_POST["accion"] === "consultar_terminal_deposit") {
	$id_cliente     = $_POST["id_cliente"];
	$web_id         = $_POST["web_id"];
	$cod            = $_POST["cod"];

	$cmd_1 = "SELECT COUNT(*) cant
				FROM tbl_televentas_clientes_transaccion tra
				WHERE 
				txn_id = " . $cod ."
				AND tipo_id in (14)
				AND estado in (0, 1)";
	$list_query = $mysqli->query($cmd_1);
	$list_tra = array();
	if ($mysqli->error) {
		$result["consulta_query"] = $cmd_1;
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$list_tra[] = $li;
		}

		if((int)$list_tra[0]["cant"] == 0){
			$array_bet_bc = api_betconstruct_get_terminaldoc($cod);
			
			if($array_bet_bc["http_code"]==200) {
				$PaidByCashDeskId = $array_bet_bc["result"]["PaidByCashDeskId"];
				$query_local = "SELECT lp.id, lp.local_id, lp.proveedor_id, l.nombre
								FROM tbl_local_proveedor_id lp
								INNER JOIN tbl_locales l ON lp.local_id = l.id
								WHERE lp.proveedor_id = '" . $PaidByCashDeskId . "'";
				$list_query_l = $mysqli->query($query_local);
				$list_local = array();
				if ($mysqli->error) {
					$result["consulta_query"] = $query_local;
					$result["consulta_error"] = $mysqli->error;
				}
				while ($li = $list_query_l->fetch_assoc()) {
					$list_local[] = $li;
				}
				if(count($list_local) > 0){
					//VALID FECHA NO MAYOR A 5 MINUTOS
					$datetime1= new DateTime();
					$datetime2 = new DateTime($array_bet_bc["result"]["CreatedPeru"]);

					$diff = $datetime1->diff($datetime2);
					$totalMinutos=($diff->d * 24 * 60) + ($diff->h * 60) + $diff->i;
					
					//$result["datetime1"] = $datetime1;
					//$result["datetime2"] = $datetime2;

					//VALID FECHA NO MAYOR A 5 MINUTOS

					if((int)$totalMinutos > 5){
						$result["http_code"] = 400;
						$result["status"] = "El ticket consultado ha sido procesado hace mas de 5 minutos.";
						$result["totalMinutos"] = $totalMinutos;
					}else{
						$state_bet = $array_bet_bc["result"]["TicketState"];
						$monto = $array_bet_bc["result"]["Amount"];
						if ($state_bet == 0) {
							$result["http_code"] = 400;
							//$result["proveedor_name"] = $proveedor_name;
							$result["status"] = "Primero debe ser cobrado en BetShop";
						}else if($state_bet == 1){ // Pagado
							//Obtener balance actual
							$balance_actual = 0;
							$balance_nuevo = 0;
							$balance_retiro = 0;

							$list_balance = array();
							$list_balance = obtener_balances($id_cliente);

							if (count($list_balance) === 1) {
								$balance_actual = $list_balance[0]["balance"];
								$balance_nuevo = $balance_actual + $monto;
								$balance_retiro = $list_balance[0]["balance_retiro_disponible"];
								$balance_retiro_nuevo = $balance_retiro + $monto;
							}

							$insert_command = "
								INSERT INTO tbl_televentas_clientes_transaccion (
									tipo_id,
									api_id,
									txn_id,
									cliente_id,
									user_id,
									turno_id,
									monto,
									nuevo_balance,
									bono_monto,
									estado,
									created_at
								) VALUES (
									14,
									1,
									" . $cod . ",
									" . $id_cliente . ",
									" . $usuario_id . ",
									" . $turno_id . ",
									" . $monto . ",
									" . $balance_nuevo . ",
									0,
									1,
									now()
								)";
							$mysqli->query($insert_command);

							$query_3 = "SELECT id FROM tbl_televentas_clientes_transaccion  ";
							$query_3 .= " WHERE tipo_id = 14 AND txn_id='" . $cod . "' ";
							$query_3 .= " AND user_id='" . $usuario_id . "' ";
							$query_3 .= " AND turno_id='" . $turno_id . "' ";
							$query_3 .= " AND estado = 1 and cliente_id = " . $id_cliente;
							$list_query = $mysqli->query($query_3);
							$list_transaccion = array();
							while ($li = $list_query->fetch_assoc()) {
								$list_transaccion[] = $li;
							}
							if (count($list_transaccion) == 0) {
								$result["http_code"] = 400;
								$result["status"] = "No se pudo guardar la transacción.";
							} elseif (count($list_transaccion) === 1) {

								//*********************** UPDATE BALANCE PRINCIPAL
								query_tbl_televentas_clientes_balance('update', $id_cliente, 1, $balance_nuevo);

								//*********************** UPDATE BALANCE RETIRO DISPONIBLE
								query_tbl_televentas_clientes_balance('update', $id_cliente, 5, $balance_retiro_nuevo);

								query_tbl_televentas_clientes_balance_transaccion('insert', $list_transaccion[0]['id'], 
										$id_cliente, 1, $balance_actual, $monto, $balance_nuevo);


								query_tbl_televentas_clientes_balance_transaccion('insert', $list_transaccion[0]['id'], 
										$id_cliente, 5, $balance_retiro, $monto, $balance_retiro_nuevo);
								sec_tlv_asignar_etiqueta_test($id_cliente);
								$result["http_code"] = 200;
								$result["status"] = "ok";
								//$result["array_bet_bc"] = $array_bet_bc;
							} else {
								$result["http_code"] = 400;
								$result["status"] = "Ocurrió un error al guardar la transacción.";
								//$result["insert_command"] = $insert_command;
								//$result["list_transaccion"] = $list_transaccion;
							}
						}
					}   
				}else{
					$result["http_code"] = 400;
					$result["status"] = "El ticket consultado no se encuentra en ningun local registrado.";
					$result["list_local"] = $list_local;
				}
			}else{
				$result["http_code"] = 400;
				$result["status"] = "Error en la consulta, asegurese de ingresar el número de documento correcto.";
				$result["array_bet_bc"] = $array_bet_bc;
			}
		}else{
			$result["http_code"] = 400;
			$result["status"] = "Ya existe un registro";
		}
	}

}


//***************************************
// ELIMINAR TERMINAL DEPOSIT
//***************************************
if ($_POST["accion"] === "eliminar_transaccion_terminal") {
	include("function_replace_invalid_caracters.php");

	$id_cliente = $_POST["cliente_id"];
	$id_trans = $_POST["trans_id"];
	$tipo_id = $_POST["tipo_id"];
	$observacion = replace_invalid_caracters($_POST["observacion"]);

	$result = rollback_transaccion($id_cliente, $id_trans, $usuario_id, $turno_id, $observacion,0);

}







//***************************************
// EDITAR BALANCE
//***************************************
if ($_POST["accion"] === "editar_balance") {

	$cliente_id = $_POST["id_cliente"];
	$tipo_transaccion = $_POST["tipo_transaccion"];
	$tipo_balance = $_POST["tipo_balance"]; 
	
	$motivo_balance = $_POST["motivo_balance"];
	$juego_balance = $_POST["juego_balance"];
	$id_transaccion_juego = $_POST["id_transaccion_juego"];
	$supervisor_balance = $_POST["supervisor_balance"];
	$cajero_balance = $_POST["cajero_balance"];

	$monto = $_POST["monto"];
	$observacion = $_POST["observacion"];

	// BALANCE BILLETERO
	$list_balance = array();
	$list_balance = obtener_balances($cliente_id);

	if(count($list_balance)>0){

		$balance_total_actual = $list_balance[0]["balance"];
		if((int)$tipo_balance===4){
			$balance_detalle_actual = $list_balance[0]["balance_deposito"];
		} else if((int)$tipo_balance===5){
			$balance_detalle_actual = $list_balance[0]["balance_retiro_disponible"];
		} else if((int)$tipo_balance===6){
			$balance_detalle_actual = $list_balance[0]["balance_dinero_at"];
		} else {
			$result["http_code"] = 400;
			$result["status"] = 'Por favor actualice la página y vuelva a intentarlo.';
			echo json_encode($result);exit();
		}

		$balance_total_nuevo = 0;
		$balance_detalle_nuevo = 0;

		$cod_tipo_transaccion=0;

		if($tipo_transaccion === 'up_balance') {
			//INICIO -- Validar Id Transacción ingresado para Kurax (provicional)
			if($motivo_balance == 50 && $juego_balance == 9){ // KURAX ambas selecciones
				//motivo balance prod: 50

				//Existencia del Id Transaccion
				if(trim($id_transaccion_juego) != ""){
					$cmd_count_trans_kurax = "
						SELECT id 
						FROM tbl_televentas_clientes_transaccion 
						WHERE tipo_id = 17 
						AND estado = 1
						AND id_motivo_balance = '" . $motivo_balance . "'
						AND id_juego_balance = '" . $juego_balance . "'
						AND id_tj_balance = '" . $id_transaccion_juego . "'
						";
					$list_cmd_count_trans_kurax = $mysqli->query($cmd_count_trans_kurax);
					$list_trans_up_kurax = array();
					while ($li2 = $list_cmd_count_trans_kurax->fetch_assoc()) {
						$list_trans_up_kurax[] = $li2;
					}
					if (count($list_trans_up_kurax) > 0) {
						$result["http_code"] = 400;
						$result["status"] = 'El ID transacción de Kurax ya se encuentra registrado.';
						echo json_encode($result);exit();
					}
				}else{
					$result["http_code"] = 400;
					$result["status"] = 'Debe agregar el Id de la Transacción de Kurax.';
					echo json_encode($result);exit();
				}
			}
			//FIN -- Validar Id Transacción ingresado para Kurax (provicional)
			if((int)$tipo_balance===4 || (int)$tipo_balance===5){
				$balance_total_nuevo = $balance_total_actual + $monto;
			} else {
				$balance_total_nuevo = $balance_total_actual;
			}
			$balance_detalle_nuevo = $balance_detalle_actual + $monto;
			$cod_tipo_transaccion = 17;
		}
		if($tipo_transaccion === 'down_balance') {
			if((int)$tipo_balance===4 || (int)$tipo_balance===5){
				if($balance_total_actual <= 0){
					$result["http_code"] = 400;
					$result["status"] = 'No puede bajar el balance porque ya está en 0';
					echo json_encode($result);exit();
				}

				if($balance_total_actual < $monto){
					$result["http_code"] = 400;
					$result["status"] = 'El monto que desea disminuir no puede ser mayor al balance total';
					echo json_encode($result);exit();
				}
				$balance_total_nuevo = $balance_total_actual - $monto;
			} else {
				$balance_total_nuevo = $balance_total_actual;
			}

			if($balance_detalle_actual <= 0){
				$result["http_code"] = 400;
				$result["status"] = 'El balance que desea disminuir es menor o igual a 0';
				echo json_encode($result);exit();
			}

			if($balance_detalle_actual < $monto){
				$result["http_code"] = 400;
				$result["status"] = 'El monto que desea disminuir no puede ser mayor al balance disponible';
				echo json_encode($result);exit();
			}

			$balance_detalle_nuevo = $balance_detalle_actual - $monto;
			$cod_tipo_transaccion = 18;
		}

		$fecha_hora = date('Y-m-d H:i:s');

		$insert_command = "
			INSERT INTO tbl_televentas_clientes_transaccion (
				tipo_id,
				cliente_id,
				turno_id,
				monto,
				nuevo_balance,
				estado,
				observacion_cajero,
				user_id,
				created_at,
				id_motivo_balance,
				id_juego_balance,
				id_supervisor_balance,
				id_tipo_balance,
				id_cajero_balance,
				id_tj_balance
			) VALUES (
				$cod_tipo_transaccion,
				" . $cliente_id . ",
				" . $turno_id . ",
				" . $monto . ",
				" . $balance_total_nuevo . ",
				1,
				'" . $observacion . "',
				" . $usuario_id . ",
				'". $fecha_hora ."',

				'". $motivo_balance ."',
				'". $juego_balance ."', 
				'". $supervisor_balance ."',
				'". $tipo_balance ."',
				'". $cajero_balance ."',
				'". $id_transaccion_juego ."'
				 
			)";
		$mysqli->query($insert_command);

		$query_3 = "
			SELECT id 
			FROM tbl_televentas_clientes_transaccion 
			WHERE tipo_id = '$cod_tipo_transaccion' 
			AND cliente_id ='$cliente_id' 
			AND turno_id ='$turno_id' 
			AND user_id ='$usuario_id' 
			AND created_at ='$fecha_hora' 
			AND estado = 1
			";
		$list_query = $mysqli->query($query_3);
		$list_transaccion2 = array();
		while ($li2 = $list_query->fetch_assoc()) {
			$list_transaccion2[] = $li2;
		}
		if (count($list_transaccion2) === 1) {
			$transaccion_id = $list_transaccion2[0]["id"];

			query_tbl_televentas_clientes_balance('update', $cliente_id, 1, $balance_total_nuevo);
			query_tbl_televentas_clientes_balance('update', $cliente_id, $tipo_balance, $balance_detalle_nuevo);

			query_tbl_televentas_clientes_balance_transaccion('insert', $transaccion_id, 
				$cliente_id, 1, $balance_total_actual, $monto, $balance_total_nuevo);
			query_tbl_televentas_clientes_balance_transaccion('insert', $transaccion_id, 
				$cliente_id, $tipo_balance, $balance_detalle_actual, $monto, $balance_detalle_nuevo);
			sec_tlv_asignar_etiqueta_test($cliente_id);
			$result["http_code"] = 200;
			$result["status"] = "Ok.";

		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al editar el balance.";
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar el balance.";
	}

}











//***************************************
// GUARDAR DEPOSITO CAJA 7
//***************************************
if ($_POST["accion"] === "guardar_transaccion_deposito_c7") {
	include("function_replace_invalid_caracters.php");

	$id_cliente = $_POST["id_cliente"];
	$id_web = $_POST["idweb"];
	$tipo_contacto = $_POST["tipo_contacto"];
	$monto_deposito = $_POST["monto"];
	$total_bono_mes = $_POST["total_bono_mes"];
	$observacion = replace_invalid_caracters($_POST["observacion"]);
	$cuenta = $_POST["cuenta"];

	if (!((float) $monto_deposito > 0)) {
		$result["http_code"] = 400;
		$result["status"] = "Montos incorrectos.";
		$result["monto_deposito"] = ((float) $monto_deposito > 0) ? true : false;
		echo json_encode($result);exit();
	}

	$list_balance = array();
	$list_balance = obtener_balances($id_cliente);

	if (count($list_balance) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "Usted no tiene balance.";
		$result["result"] = $list_balance;
	} elseif (count($list_balance) > 0) {
		$balance_actual = $list_balance[0]["balance"];
		$balance_deposito = $list_balance[0]["balance_deposito"];

		$nuevo_balance = $balance_actual + $monto_deposito;
		$nuevo_balance_deposito = $balance_deposito + $monto_deposito;

		$insert_command = " 
			INSERT INTO tbl_televentas_clientes_transaccion (
				tipo_id,
				cliente_id,
				id_tipo_contacto,
				cuenta_id,
				turno_id,
				cc_id,
				web_id,
				num_operacion,
				bono_id,
				monto_deposito,
				comision_monto,
				monto,
				bono_monto,
				total_recarga,
				nuevo_balance,
				estado,
				observacion_validador,
				caja_vip,
				user_id,
				created_at
			) VALUES (
				1,
				'" . $id_cliente . "',
				'" . $tipo_contacto . "',
				'" . $cuenta . "',
				'" . $turno_id . "',
				'" . $cc_id . "',
				'" . $id_web . "',
				'',
				'0',
				'" . $monto_deposito . "',
				'0',
				'" . $monto_deposito . "',
				'0',
				'" . $monto_deposito . "',
				'" . $balance_actual . "',
				'1',
				'" . $observacion . "',
				'1',
				'" . $usuario_id . "',
				now()
			)
			";
		$mysqli->query($insert_command);
		$query_3 = "
			SELECT tra.id
			FROM tbl_televentas_clientes_transaccion tra
			WHERE tipo_id = 1  AND estado = 1 
			AND cliente_id='$id_cliente' AND user_id='$usuario_id' 
			AND turno_id='$turno_id' AND cc_id='$cc_id' 
			AND monto_deposito='$monto_deposito' 
			ORDER BY id DESC LIMIT 1 
		";
		$list_query = $mysqli->query($query_3);
		$list_transaccion = array();
		if ($mysqli->error) {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al consultar la transacción solicitud.";
			$result["error"] = $mysqli->error;
			$result["consulta_error"] = $query_3;
			echo json_encode($result);exit();
		}
		while ($li = $list_query->fetch_assoc()) {
			$list_transaccion[] = $li;
		}
		if (count($list_transaccion) === 0) {
			$result["http_code"] = 400;
			$result["status"] = "No se guardó la transacción solicitud.";
		} elseif (count($list_transaccion) === 1) {
			$transaccion_id = $list_transaccion[0]["id"];
			$insert_command = " 
				INSERT INTO tbl_televentas_clientes_transaccion (
					tipo_id,
					cliente_id,
					id_tipo_contacto,
					cuenta_id,
					turno_id,
					cc_id,
					web_id,
					num_operacion,
					bono_id,
					monto_deposito,
					comision_monto,
					monto,
					bono_monto,
					total_recarga,
					nuevo_balance,
					transaccion_id,
					estado,
					observacion_validador,
					caja_vip,
					user_id,
					created_at
				) VALUES (
					26,
					'" . $id_cliente . "',
					'" . $tipo_contacto . "',
					'" . $cuenta . "',
					'" . $turno_id . "',
					'" . $cc_id . "',
					'" . $id_web . "',
					'',
					'0',
					'" . $monto_deposito . "',
					'0',
					'" . $monto_deposito . "',
					'0',
					'" . $monto_deposito . "',
					'" . $nuevo_balance . "',
					'" . $transaccion_id . "',
					'1',
					'" . $observacion . "',
					'1',
					'" . $usuario_id . "',
					now()
				)
				";
			$mysqli->query($insert_command);

			$query_verifica_aprobacion = "
				SELECT tra.id
				FROM tbl_televentas_clientes_transaccion tra
				WHERE tipo_id = 26  AND estado = 1 
				AND cliente_id='$id_cliente' AND user_id='$usuario_id' 
				AND turno_id='$turno_id' AND cc_id='$cc_id' 
				AND monto_deposito='$monto_deposito' 
				AND transaccion_id ='$transaccion_id'
				ORDER BY id DESC LIMIT 1 
			";
			$list_query = $mysqli->query($query_verifica_aprobacion);
			$list_transaccion_aprob = array();
			if ($mysqli->error) {
				$result["http_code"] = 400;
				$result["status"] = "Ocurrió un error al consultar la transacción aprobación.";
				$result["error"] = $mysqli->error;
				$result["consulta_error"] = $query_verifica_aprobacion;
				echo json_encode($result);exit();
			}
			while ($li = $list_query->fetch_assoc()) {
				$list_transaccion_aprob[] = $li;
			}
			if (count($list_transaccion_aprob) === 0) {
				$result["http_code"] = 400;
				$result["status"] = "No se guardó la transacción aprobación.";
			} elseif (count($list_transaccion_aprob) === 1) {
				$transaccion_id_aprobacion = $list_transaccion_aprob[0]["id"];

				query_tbl_televentas_clientes_balance('update', $id_cliente, 1, $nuevo_balance);
				query_tbl_televentas_clientes_balance('update', $id_cliente, 4, $nuevo_balance_deposito);

				query_tbl_televentas_clientes_balance_transaccion('insert', $transaccion_id_aprobacion, $id_cliente, 1, 
					$balance_actual, $monto_deposito, $nuevo_balance);
				query_tbl_televentas_clientes_balance_transaccion('insert', $transaccion_id_aprobacion, $id_cliente, 4, 
					$balance_deposito, $monto_deposito, $nuevo_balance_deposito);
				sec_tlv_asignar_etiqueta_test($id_cliente);
				$result["http_code"] = 200;
				$result["status"] = "ok";
				$result["result"] = "Solicitud de Depósito Registrada";

				//**********************************
				//**********************************
				// IMAGEN
				//**********************************
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
						$imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);

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
					$archivo_id = mysqli_insert_id($mysqli);
					$filepath = $path . $resizeFileName . "." . $fileExt;
				}
				//**********************************
				//**********************************
			}
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar el balance.";
		$result["result"] = $list_balance;
	}
}

//***************************************
// GUARDAR SOLICITUD RETIRO C7
//***************************************
if ($_POST["accion"] === "guardar_transaccion_pago_retiro_c7") {
	include("function_replace_invalid_caracters.php");
	
	$id_cliente = $_POST["cliente_id"];
	$monto_solicitud = $_POST["monto_solicitud"];
	$id_banco_cuenta_usar_retiro = $_POST["id_banco_cuenta_usar_retiro"];
	$id_cuenta_usar_retiro = $_POST["id_cuenta_usar_retiro"];
	$razon = $_POST["razon"];
	$tipo = $_POST["tipo"];
	$id_cuenta_pago = $_POST["id_cuenta_pago"];
	$web_id = $_POST["web_id"];
	$num_operacion = $_POST["num_operacion"];
    $registro = str_replace('T', ' ', $_POST["registro"]);
    $monto_comision = $_POST["monto_comision"];
    $observacion = $_POST["observacion"];

	if (!((float) $monto_solicitud > 0)) {
		$result["http_code"] = 400;
		$result["status"] = "Monto incorrecto.";
		$result["monto_deposito"] = ((float) $monto_deposito > 0) ? true : false;
		echo json_encode($result);exit();
	}

	//********* VALIDACION BALANCES
	$query_balances = "
		SELECT 
			(SELECT balance FROM tbl_televentas_clientes_balance 
				WHERE tipo_balance_id=1 AND cliente_id=" . $id_cliente . " 
			) balance_total,
			IFNULL((SELECT balance FROM tbl_televentas_clientes_balance 
				WHERE tipo_balance_id=4 AND cliente_id=" . $id_cliente . " 
			), 0) balance_deposito,
			IFNULL((SELECT balance FROM tbl_televentas_clientes_balance 
				WHERE tipo_balance_id=5 AND cliente_id=" . $id_cliente . " 
			), 0) balance_disponible_retiro
		";
	$list_query = $mysqli->query($query_balances);
	$list_balance = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_balance[] = $li;
	}
	$balance_total = $list_balance[0]["balance_total"];
	$balance_deposito = $list_balance[0]["balance_deposito"];
	$balance_disponible_retiro = $list_balance[0]["balance_disponible_retiro"];

	$nuevo_balance = $balance_total - $monto_solicitud;
	$nuevo_balance_retiro = $balance_disponible_retiro - $monto_solicitud;
	$nuevo_balance_deposito = $balance_deposito - $monto_solicitud;
	//********* VALIDACION BALANCES

	if (!((double)$balance_disponible_retiro > 0)) {
		$result["http_code"] = 400;
		$result["status"] = "Usted no tiene saldo disponible para retirar.";
		$result["result"] = $list_balance;
		echo json_encode($result);exit();
	}
	if (!((double)$balance_disponible_retiro >= (double)$monto_solicitud)) {
		$result["http_code"] = 400;
		$result["status"] = "El monto de la solicitud no puede ser mayor al balance de retiro disponible.";
		$result["result"] = $list_balance;
		echo json_encode($result);exit();
	}
	if (count($list_balance) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "Usted no tiene balance.";
		$result["result"] = $list_balance;
	} elseif (count($list_balance) > 0) {
		//Validar num ope unico
		$cmd_valid_num = "
			SELECT 
				tra.num_operacion, tra.created_at
			FROM
				tbl_televentas_clientes_transaccion tra
			INNER JOIN tbl_televentas_cuentas_pago_retiro cp ON tra.cuenta_pago_id = cp.id
			WHERE
				tra.tipo_id = 11 
				and tra.num_operacion = '" . $num_operacion . "'
				and cuenta_pago_id = " . $id_cuenta_pago . "
				and IFNULL(valid_num_ope_unico, 0) = 1
				and DATE(tra.created_at) = DATE(now())
		";
		$list_valid_num = $mysqli->query($cmd_valid_num);
		$list_registers_num_ope = array();
		if ($mysqli->error) {
			$result["consulta_query"] = $cmd_valid_num;
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
		//********* VALIDACION BALANCE

		// Insertar solicitud
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
				tipo_rechazo_id,
				estado,
				observacion_cajero,
				observacion_validador,
				bono_mensual_actual,
				user_id,
				created_at,
				cuenta_pago_id,
				id_operacion_retiro,
				tipo_operacion,
				id_motivo_dev,
				user_valid_id,
				caja_vip
			) VALUES (
				'9',
				'". $id_cliente ."',
				'". $id_cuenta_usar_retiro ."',
				'". $turno_id ."',
				'". $cc_id ."',
				'". $web_id ."',
				'". $num_operacion ."',
				'". $registro ."',
				'0',
				'". $monto_solicitud ."',
				'". $monto_comision ."',
				'". $monto_solicitud ."',
				'0',
				'0',
				'". $nuevo_balance ."',
				'0',
				'2',
				'',
				'". $observacion ."',
				'0',
				'". $usuario_id ."',
				now(),
				'". $id_cuenta_pago ."',
				'". $razon ."',
				'1',
				'0',
				'" . $usuario_id . "',
				1
			) 
			";
		$mysqli->query($insert_command);
		$error = '';
		if ($mysqli->error) {
			$result["insert_error"] = $mysqli->error;
			$error = $mysqli->error;
		}
		if ($error === '') {
			$query_verifica = "SELECT id FROM tbl_televentas_clientes_transaccion  ";
			$query_verifica .= " WHERE tipo_id = 9 ";
			$query_verifica .= " AND cliente_id='" . $id_cliente . "'  ";
			$query_verifica .= " AND user_id='" . $usuario_id . "' ";
			$query_verifica .= " AND registro_deposito='" . $registro . "' ";
			$query_verifica .= " AND turno_id='" . $turno_id . "' ";
			$query_verifica .= " AND monto='" . $monto_solicitud . "' ";
			$query_verifica .= " AND cuenta_id='" . $id_cuenta_usar_retiro . "' ";
			$query_verifica .= " AND cuenta_pago_id='" . $id_cuenta_pago . "' ";
			$query_verifica .= " AND nuevo_balance='" . $nuevo_balance . "' ";
			$query_verifica .= " AND comision_monto='" . $monto_comision . "' ";
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
					$result["status"] = "No se guardó la transacción solicitud.";								
				} elseif (count($list_transaccion_verifica) === 1) {
					$transaccion_id_nuevo = $list_transaccion_verifica[0]["id"];

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
							tipo_rechazo_id,
							estado,
							observacion_cajero,
							observacion_validador,
							bono_mensual_actual,
							user_id,
							transaccion_id,
							created_at,
							cuenta_pago_id,
							id_operacion_retiro,
							tipo_operacion,
							id_motivo_dev,
							user_valid_id,
							caja_vip
						) VALUES (
							'11',
							'". $id_cliente ."',
							'". $id_cuenta_usar_retiro ."',
							'". $turno_id ."',
							'". $cc_id ."',
							'". $web_id ."',
							'". $num_operacion ."',
							'". $registro ."',
							'0',
							'". $monto_solicitud ."',
							'". $monto_comision ."',
							'". $monto_solicitud ."',
							'0',
							'0',
							'". $nuevo_balance ."',
							'0',
							'2',
							'',
							'". $observacion ."',
							'0',
							'". $usuario_id ."',
							'".$transaccion_id_nuevo."',
							now(),
							'". $id_cuenta_pago ."',
							'". $razon ."',
							'1',
							'0',
							'" . $usuario_id . "',
							1
						) 
						";
					$mysqli->query($insert_command);
					$error = '';
					if ($mysqli->error) {
						$result["insert_error"] = $mysqli->error;
						$error = $mysqli->error;
					}
					if ($error === '') {
						$query_verifica = "SELECT id FROM tbl_televentas_clientes_transaccion  ";
						$query_verifica .= " WHERE tipo_id = 11 ";
						$query_verifica .= " AND cliente_id='" . $id_cliente . "'  ";
						$query_verifica .= " AND user_id='" . $usuario_id . "' ";
						$query_verifica .= " AND registro_deposito='" . $registro . "' ";
						$query_verifica .= " AND turno_id='" . $turno_id . "' ";
						$query_verifica .= " AND monto='" . $monto_solicitud . "' ";
						$query_verifica .= " AND cuenta_id='" . $id_cuenta_usar_retiro . "' ";
						$query_verifica .= " AND cuenta_pago_id='" . $id_cuenta_pago . "' ";
						$query_verifica .= " AND nuevo_balance='" . $nuevo_balance . "' ";
						$query_verifica .= " AND comision_monto='" . $monto_comision . "' ";
						$query_verifica .= " AND transaccion_id='" . $transaccion_id_nuevo . "' ";
						$query_verifica .= " ORDER BY id DESC ";
						$list_query = $mysqli->query($query_verifica);
						$list_trans_ver_aprob = array();
						if ($mysqli->error) {
							$result["http_code"] = 400;
							$result["status"] = "Ocurrió un error al consultar la transacción.";	
							$result["query_verifica"] = $query_verifica;
							$result["query_error"] = $mysqli->error;
						} else {
							while ($li = $list_query->fetch_assoc()) {
								$list_trans_ver_aprob[] = $li;
							}
							if (count($list_trans_ver_aprob) === 0) {
								$result["http_code"] = 400;
								$result["status"] = "No se guardó la transacción.";								
							} elseif (count($list_trans_ver_aprob) === 1) {
								$transaccion_id_aprobacion = $list_trans_ver_aprob[0]["id"];


								$query_update_balance = " 
								UPDATE tbl_televentas_clientes_balance 
								SET
									balance = balance - " . $monto_solicitud . ",
									updated_at=now()
								WHERE tipo_balance_id = 5
									AND cliente_id=" . $id_cliente;
								$mysqli->query($query_update_balance);

								$query_update_balance = " 
								UPDATE tbl_televentas_clientes_balance 
								SET
									balance = balance - " . $monto_solicitud . ",
									updated_at=now()
								WHERE tipo_balance_id = 1
									AND cliente_id=" . $id_cliente;
								$mysqli->query($query_update_balance);

								query_tbl_televentas_clientes_balance_transaccion('insert', $transaccion_id_nuevo, 
									$id_cliente, 1, $balance_total, $monto_solicitud, $nuevo_balance);

								query_tbl_televentas_clientes_balance_transaccion('insert', $transaccion_id_nuevo, 
									$id_cliente, 5, $balance_disponible_retiro, $monto_solicitud, $nuevo_balance_retiro);

								sec_tlv_asignar_etiqueta_test($id_cliente);

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

								$filename = $_FILES['SecRetC7_input_file_voucher']['tmp_name'];
								$filenametem = $_FILES['SecRetC7_input_file_voucher']['name'];
								$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
								if ($filename != "") {
									$fileExt = pathinfo($_FILES['SecRetC7_input_file_voucher']['name'], PATHINFO_EXTENSION);
									$resizeFileName = $transaccion_id_aprobacion . "_" . date('YmdHis');
									$nombre_archivo = $resizeFileName . ".png"; //" . $fileExt;
									if ($fileExt == "pdf") {
										move_uploaded_file($_FILES['SecRetC7_input_file_voucher']['tmp_name'], $path . $nombre_archivo);
									} else {
										$sourceProperties = getimagesize($filename);
										$size = $_FILES['SecRetC7_input_file_voucher']['size'];
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
										$imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);

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
												'" . $transaccion_id_aprobacion . "',
												1,
												'" . $nombre_archivo . "',
												'" . date('Y-m-d H:i:s') . "',
												1
											)";
									$mysqli->query($comando);

									$query_verifica_2 = "SELECT * FROM tbl_televentas_transaccion_archivos
														WHERE transaccion_id = " . $transaccion_id_aprobacion ."
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
							} elseif (count($list_trans_ver_aprov) > 1) {
								$result["http_code"] = 400;
								$result["status"] = "Se duplicaron las transacciones, por favor informar a informática.";
							} else {
								$result["http_code"] = 400;
								$result["status"] = "Ocurrió un error al guardar la transacción.";	
							}
						}
					}else{
						$result["http_code"] = 400;
						$result["status"] = "Ocurrió un error al guardar el pago del retiro";	
					}
				}
			}
		}


		
		

	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar el balance.";
		$result["result"] = $list_balance;
	}

}


//*******************************************************************************************************************
//*******************************************************************************************************************
// APIS CAPTCHA VERIFICACION DNI
//*******************************************************************************************************************
//*******************************************************************************************************************


if ($_POST["accion"] === "cargar_img_captcha_api") {

	$user_id     = $usuario_id;
	$dni_cliente = $_POST["dni_cliente"];
	$id_txn = 0;

	$query_insert_dni_captcha = "
		INSERT INTO tbl_televentas_api_captcha_get_dni (
			dni,
			user_id,
			created_at 
		) VALUES (
			'" . $dni_cliente . "',
			$user_id,
			now()
		);
		";
	$mysqli->query($query_insert_dni_captcha);

	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los registros";
		$result["query_insert_dni_captcha"] = $mysqli->error;
		echo json_encode($result);exit();
	}

	$query_1 = "
		SELECT id 
		FROM tbl_televentas_api_captcha_get_dni
		WHERE dni = '". $dni_cliente ."' 
		AND user_id = '". $user_id ."'
		ORDER BY id DESC
		Limit 1 ";

	$list_query_1 = $mysqli->query($query_1);
	$list_1 = array();
	if ($mysqli->error) {
		$result["query_1_error"] = $mysqli->error;
	} else {
		while ($li_1 = $list_query_1->fetch_assoc()) {
			$id_txn = $li_1['id'];
		}
	}

	if (!((int)$id_txn > 0)) {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los registros";
		echo json_encode($result);exit();
	}

	$url = "https://serviciosbiometricos.reniec.gob.pe/appConsultaHuellas/captcha";
	$request_headers = array();
	//$request_headers[] = "Content-type: image/jpeg";
	$request_headers[] = "Connection: Keep-Alive";
	$request_headers[] = "Keep-Alive: timeout=5, max=100";
	$request_headers[] = "X-ORACLE-DMS-RID: 0:1";
	$request_headers[] = "Server: Oracle-HTTP-Server";
	$request_headers[] = "Content-type: application/x-www-form-urlencoded;charset=UTF-8";
	$request_headers[] = "Accept: image/gif, image/x-bitmap, image/jpeg, image/pjpeg";
	$request = Array();

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
	curl_setopt($ch, CURLOPT_HEADER, 1); 
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
	curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);	

	$data = curl_exec($ch); 
	$curl_error = curl_error($ch); 

	$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
	$header = substr($data, 0, $header_size);
	$body = substr($data, $header_size);

	preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $header, $matches);
	$cookies = array();
	foreach($matches[1] as $item) {
		parse_str($item, $cookie);
		$cookies = array_merge($cookies, $cookie);
	}
	
	curl_close($ch);


	$path = "/var/www/html/files_bucket/tls_captcha/";
	if(!is_dir($path)) mkdir($path, 0777, true);
	$img_temp = 'filename_'.$id_txn.'.png';
	$path_img = $path . $img_temp;
	$image_location = fopen($path_img, "wb");
	fwrite($image_location, $body, 8192);
	fclose($image_location);

    if (!empty($body)) {

		if(empty($cookies)){
			$result["http_code"] = 400;
			$result["status"] = "Error";
			$result["result"] = $header;
		}else{

			$query_update_capchat = "
			UPDATE tbl_televentas_api_captcha_get_dni 
			SET cookie = '" . $cookies["JSESSIONID"] . "',
				path_img = '$path_img',
				response_captcha = '" . json_encode($cookies) . "',
				updated_at = now()
			WHERE id ='".$id_txn."'
			";
			$mysqli->query($query_update_capchat);

			$result["http_code"] = 200;
			$result["status"] = "ok";		 
			$result["result"] = $cookies;
			$result["nom_img"] = $img_temp;

		}
		
    } else {

		$result["http_code"] = 400;
		$result["status"] = "Error";
		$result["result"] = $header;
    }
}


if ($_POST["accion"] === "verificar_captcha_api") {

	$dni_cliente = $_POST["dni_cliente"];
	$captcha = $_POST["captcha"];
	$cookie_session = $_POST["cookie_session"];
	$user_id     = $usuario_id;

	$url = "https://serviciosbiometricos.reniec.gob.pe/appConsultaHuellas/consultar";
	$request_headers = array();
	$request_headers[] = "Content-Type: application/json;charset=UTF-8";
	$request_headers[] = "Cookie: JSESSIONID=".$cookie_session;

	$request = Array();
	$request["numeroDni"] = "$dni_cliente";
	$request["codigoCaptcha"] = "$captcha";
	$request_json = json_encode($request);

	$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HTTPHEADER,$request_headers);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $request_json);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
		$response = curl_exec($curl);
		$curl_error = curl_error($curl);
		$response_array = json_decode($response, true);
		if(array_key_exists('error',$response_array)){

			$result["http_code"] = 400;
			$result["status"] = "error";
			$result["result"] = $response_array;

		}
		else{

			$query_update_verif_capcha = "
			UPDATE tbl_televentas_api_captcha_get_dni 
			SET messageCode = '". $response_array["messageCode"] ."',
				text_captcha = '$captcha',
				updated_at = now() 
			WHERE dni = '". $dni_cliente ."' 
			AND user_id = '". $user_id ."'
			";
			$mysqli->query($query_update_verif_capcha);

			$result["http_code"] = 200;
			$result["status"] = "ok";
			$result["result"] = $response;
		}
		curl_close($curl);

}



//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER LIMITE - CAJERO
//*******************************************************************************************************************
//*******************************************************************************************************************
if ($_POST["accion"] === "obtener_televentas_limite_cajero") {
	include("function_replace_invalid_caracters.php");

	$query = "SELECT IFNULL(valor, 0) limite,
			(SELECT IFNULL(valor, 0) max_aten             
			FROM tbl_televentas_parametros  
			WHERE estado = 1 AND nombre_codigo ='max_aten') max_aten
			FROM tbl_televentas_parametros  
			WHERE estado = 1 AND nombre_codigo ='limite' Limit 1";
		 
	$list_query = $mysqli->query($query);
	$list_register = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_register[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list_register) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
		$result["query"] = $query;
	} elseif (count($list_register) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_register;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los registros";
	}
}


if ($_POST["accion"] === "obtener_televentas_cant_terceros") {
	include("function_replace_invalid_caracters.php");

	$id_cliente = $_POST["id_cliente"];

	$query_1 = "SELECT 
		count(id) as cant
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

	if (count($list_1) > 0) {	
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_1;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los registros";
	}

}


if ($_POST["accion"] === "obtener_televentas_limite_terceros") {
	include("function_replace_invalid_caracters.php");

	$query = "SELECT IFNULL(valor, 0) limite_terc
			FROM tbl_televentas_parametros  
			WHERE estado = 1 AND nombre_codigo ='limite_terc' Limit 1";

	$list_query = $mysqli->query($query);
	$list_register = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_register[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list_register) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
		$result["query"] = $query;
	} elseif (count($list_register) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_register;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los registros";
	}
}



if ($_POST["accion"] === "obtener_televentas_cont_cajero") {
	include("function_replace_invalid_caracters.php");

	$query = "
		SELECT 
		IFNULL(cont_temp, 0) cont_temp
		FROM tbl_usuarios
		where id= '" . $usuario_id . "' ";
		 
	$list_query = $mysqli->query($query);
	$list_register = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_register[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list_register) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
		$result["query"] = $query;
	} elseif (count($list_register) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_register;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los registros";
	}
}

//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER CLIENTE - CAJERO
//*******************************************************************************************************************
//*******************************************************************************************************************
if ($_POST["accion"] === "obtener_televentas_cliente_cajero") {
	include("function_replace_invalid_caracters.php");

	$id_cajero_tlv = $_POST["id_cajero_tlv"];
	$hash = $_POST["hash"];
 
	$query_clientes_cajero = "
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
			IFNULL(c.block_hash, '') block_hash,
			1 tipo_balance_id
		FROM tbl_televentas_clientes c
		where block_user_id= " . $id_cajero_tlv . "
		ORDER BY id ASC ";
		 
	$list_query_1 = $mysqli->query($query_clientes_cajero);
	$list_1 = array();
	if ($mysqli->error) {
		$result["query_1_error"] = $mysqli->error;
	} else {
		while ($li_1 = $list_query_1->fetch_assoc()) {
			$list_1[] = $li_1;
		}
	}

	$total_c= count($list_1);
		if ($total_c > 0) { 
			$i =0; 
			while ($i < $total_c){
				$query_update = "
				UPDATE tbl_televentas_clientes
				SET
						block_hash = '$hash',
						updated_at = now()
				WHERE id = '" . $list_1[$i]["id"] . "'
				";
				$mysqli->query($query_update);

				$i = $i +1;
			}
			 

		$result["http_code"] = 200;
		$result["status"] = "Clientes encontrados en la BD de televentas.";
		$result["result"] = $list_1;
	}
 
}

if($_POST["accion"] === "sec_tlv_ingresar_tambo"){
	include("function_replace_invalid_caracters.php");

	$monto     		= $_POST["monto"];
	$id_cliente     = $_POST["id_cliente"];

	//Validar api y obtener barcode
	if((double)$monto < 0){
		$result["http_code"] = 400;
		$result["status"] = "El monto debe ser mayor a 0.";
		echo json_encode($result);exit();
	}

	$rr_data = Array();
	$rr_data = sec_tlv_api_terminal_tambo($monto, $id_cliente);
	if(isset($rr_data["http_code"])){
		if($rr_data["http_code"] == "200") {
			if(isset($rr_data["result"]["id"]) && isset($rr_data["result"]["barcode"])){
				$data_id_transaction = $rr_data["result"]["id"];
				$data_barcode = $rr_data["result"]["barcode"];

				$list_balance = array();
				$list_balance = obtener_balances($id_cliente);

				if (count($list_balance) === 1) {
					$balance_actual = (double)$list_balance[0]["balance"];
					$balance_nuevo = (double)$balance_actual - (double)$monto;
					$balance_deposito_actual = (double)$list_balance[0]["balance_deposito"];
					$balance_retiro_actual = (double)$list_balance[0]["balance_retiro_disponible"];
					$balance_deposito_nuevo = (double)$list_balance[0]["balance_deposito"];
					$balance_retiro_nuevo = (double)$list_balance[0]["balance_retiro_disponible"];

					if($balance_actual >= $monto){
						$insert_command = "
							INSERT INTO tbl_televentas_clientes_transaccion (
								tipo_id,
								api_id,
								txn_id,
								num_operacion,
								cliente_id,
								user_id,
								turno_id,
								monto,
								nuevo_balance,
								estado,
								created_at
							) VALUES (
								33,
								1,
								'" . $data_id_transaction . "',
								'" . $data_barcode . "',
								" . $id_cliente . ",
								" . $usuario_id . ",
								" . $turno_id . ",
								" . $monto . ",
								" . $balance_nuevo . ",
								1,
								now()
							)";
						$mysqli->query($insert_command);

						$query_3 = "SELECT id FROM tbl_televentas_clientes_transaccion  ";
						$query_3 .= " WHERE tipo_id = 33 AND txn_id='" . $data_id_transaction . "' ";
						$query_3 .= " AND user_id='" . $usuario_id . "' ";
						$query_3 .= " AND turno_id='" . $turno_id . "' ";
						$query_3 .= " AND num_operacion='" . $data_barcode . "' ";
						$query_3 .= " AND estado = 1 and cliente_id = " . $id_cliente;
						$list_query = $mysqli->query($query_3);
						$list_transaccion = array();
						while ($li = $list_query->fetch_assoc()) {
							$list_transaccion[] = $li;
						}
						if (count($list_transaccion) == 0) {
							$result["http_code"] = 400;
							$result["status"] = "No se pudo guardar la transacción.";
						} elseif (count($list_transaccion) === 1) {
							$id_transaccion = $list_transaccion[0]["id"];

							//*********************** BALANCE TOTAL
							query_tbl_televentas_clientes_balance('update', $id_cliente, 1, $balance_nuevo);
							query_tbl_televentas_clientes_balance_transaccion('insert', $id_transaccion, $id_cliente, 1, 
										$balance_actual, $monto, $balance_nuevo);

							//*********************** BALANCE DEPOSITO
							if((double)$balance_deposito_actual>0){
								if((double)$balance_deposito_nuevo>(double)$monto){
									$balance_deposito_nuevo = (double)$balance_deposito_nuevo-(double)$monto;
									$monto = 0;
								} else {
									$monto = (double)$monto-(double)$balance_deposito_nuevo;
									$balance_deposito_nuevo = 0;
								}
								query_tbl_televentas_clientes_balance('update', $id_cliente, 4, $balance_deposito_nuevo);
								query_tbl_televentas_clientes_balance_transaccion('insert', $id_transaccion, $id_cliente, 4, 
									$balance_deposito_actual, $monto, $balance_deposito_nuevo);
							}

							//*********************** BALANCE RETIRO
							if((double)$monto>0){
								if((double)$balance_retiro_actual>0){
									if((double)$balance_retiro_nuevo>(double)$monto){
										$balance_retiro_nuevo = $balance_retiro_nuevo-$monto;
										$monto = 0;
									} else {
										$monto = (double)$monto-(double)$balance_retiro_nuevo;
										$balance_retiro_nuevo = 0;
									}
									query_tbl_televentas_clientes_balance('update', $id_cliente, 5, $balance_retiro_nuevo);
									query_tbl_televentas_clientes_balance_transaccion('insert', $id_transaccion, $id_cliente, 5, 
									$balance_retiro_actual, $monto, $balance_retiro_nuevo);
								}
							}
							sec_tlv_asignar_etiqueta_test($id_cliente);
							$result["http_code"] = 200;
							$result["status"] = "OK";
							$result["data_barcode"] = $data_barcode;
						} else {
							$result["http_code"] = 400;
							$result["status"] = "Ocurrió un error al guardar la transacción.";
						}
					}else{
						$result["http_code"] = 400;
						$result["status"] = "El monto no puede ser mayor al balance del cliente.";
					}
				}else{
					$result["http_code"] = 400;
					$result["status"] = "Ocurrió un error al obtener el balance del cliente.";
				}
			}else{
				$result["http_code"] = 400;
				$result["status"] = "No se pudo leer los datos que responde la API.";
				$result["result"] = $rr_data;
			}
		}else{
			$result["http_code"] = 400;
			if($rr_data["http_code"] == 401){
				$result["status"] = "Token inválido";
			}else{
				$result["status"] = "La API respondió un error.";
			}
			$result["result"] = $rr_data;
		}
	}else{
		$result["http_code"] = 400;
		$result["status"] = "La API respondió un error ilegible.";
		$result["result"] = $rr_data;
	}

}	

function sec_tlv_api_terminal_tambo($amount, $id_cliente){
	//$shop_id = 0; $cashdesk_id = 0; $user_id = 0;, 
	$url = "https://api.apuestatotal.com/web/dev/teleservicios/terminal_deposit";
	//$rq = ['shop_id' => $shop_id,'cashdesk_id' => $cashdesk_id,'user_id' => $user_id,'amount' => $amount];
	$rq = ['amount' => $amount];
	$auditoria_id = api_calimaco_auditoria_insert('terminal_deposit', $amount, $id_cliente, $url);
	$request_headers = array();
	$request_headers[] = "Content-type: application/json";
	$request_headers[] = "Authorization:Bearer " . env('TERMINAL_API_TOKEN');
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
	$status = 0;
	$result = array();
	if ($err) {
		$status = 400;
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consumir el API.";
		$result["result"] = $response;
		$result["error"] = "cURL Error #:" . $err;
	} else {
		$status = 200;
		$result = $response_arr;
	}
	api_calimaco_auditoria_update($auditoria_id, $amount, $response, $status);
	return $result;
}

//***************************************
// GUARDAR DEVOLUCION CAJA 7
//***************************************
if ($_POST["accion"] === "guardar_transaccion_devolucion_c7") {
	include("function_replace_invalid_caracters.php");
	$id_banco_cuenta_usar_dev = $_POST["id_banco_cuenta_usar_dev"];
	$id_cuenta_usar_dev = $_POST["id_cuenta_usar_dev"];
	$cliente_id = $_POST["cliente_id"];
	$monto = $_POST["monto_solicitud"];
	$razon = $_POST["razon"];
	$tipo = $_POST["tipo"];
	$id_cuenta_pago = $_POST["id_cuenta_pago"];
	$web_id = $_POST["web_id"];
	$num_operacion = $_POST["num_operacion"];
	$registro = $_POST["registro"];
	$monto_comision = $_POST["monto_comision"];
	$observacion = $_POST["observacion"];
	$motivo_devolucion = $_POST["motivo_devolucion"];

	if (!(float) $monto > 0) {
		$result["http_code"] = 400;
		$result["status"] = "Monto incorrecto.";
		$result["monto"] = ((float) $monto > 0) ? true : false;
		echo json_encode($result);exit();
	}

	$list_balance = array();
	$list_balance = obtener_balances($cliente_id);
	if (count($list_balance) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "Usted no tiene balance.";
		$result["result"] = $list_balance;
	}else{
		$balance_total = $list_balance[0]["balance"];
		$balance_deposito = $list_balance[0]["balance_deposito"];

		$nuevo_balance = $balance_total - $monto;
		$nuevo_balance_deposito = $balance_deposito - $monto;
		if ($balance_total > 0 && $balance_total >= $monto){
			if($balance_deposito > 0 && $balance_deposito >= $monto){

				$ins_cmd_solicitud = " 
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
					user_valid_id,
					caja_vip
					) VALUES (
					28,
					'". $cliente_id ."',
					'". $id_cuenta_usar_dev ."',
					'". $turno_id ."',
					'". $cc_id ."',
					'". $web_id ."',
					'". $num_operacion ."',
					'". $registro ."',
					'0',
					'". $monto ."',
					'". $monto_comision ."',
					'". $monto ."',
					'0',
					'0',
					'". $nuevo_balance ."',
					'0',
					'2',
					'',
					'". $observacion ."',
					'0',
					'". $usuario_id ."',
					now(),
					'0',
					'". $id_cuenta_pago ."',
					'1',
					'" . $tipo ."',
					'" . $motivo_devolucion . "',
					'" . $usuario_id . "',
					1
					) 
					";
				$mysqli->query($ins_cmd_solicitud);
				$error = '';
				if ($mysqli->error) {
					$result["insert_error"] = $mysqli->error;
					$error = $mysqli->error;
				}

				if ($error === '') {
					$query_verifica = "SELECT id FROM tbl_televentas_clientes_transaccion  ";
					$query_verifica .= " WHERE tipo_id = 28  ";
					$query_verifica .= " AND cliente_id='" . $cliente_id . "'  ";
					$query_verifica .= " AND user_id='" . $usuario_id . "' ";
					$query_verifica .= " AND turno_id='" . $turno_id . "' ";
					$query_verifica .= " AND monto='" . $monto . "' ";
					$query_verifica .= " AND cuenta_id='" . $id_cuenta_usar_dev . "' ";
					$query_verifica .= " AND cuenta_pago_id='" . $id_cuenta_pago . "' ";
					$query_verifica .= " AND id_motivo_dev='" . $motivo_devolucion . "' ";
					$query_verifica .= " ORDER BY id DESC ";
					$list_query = $mysqli->query($query_verifica);
					$list_transaccion_verifica = array();
					if ($mysqli->error) {
						$result["http_code"] = 400;
						$result["status"] = "Ocurrió un error al consultar la transacción solicitud.";	
						$result["query_verifica"] = $query_verifica;
						$result["query_error"] = $mysqli->error;
					}else{
						while ($li = $list_query->fetch_assoc()) {
							$list_transaccion_verifica[] = $li;
						}
						if (count($list_transaccion_verifica) === 0) {
							$result["http_code"] = 400;
							$result["status"] = "No se guardó la transacción.";								
						} elseif (count($list_transaccion_verifica) === 1) {
							$transaccion_id_nuevo = $list_transaccion_verifica[0]["id"];
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
								tipo_rechazo_id,
								estado,
								observacion_cajero,
								observacion_validador,
								bono_mensual_actual,
								user_id,
								transaccion_id,
								created_at,
								cuenta_pago_id,
								id_operacion_retiro,
								tipo_operacion,
								id_motivo_dev,
								user_valid_id,
								caja_vip
								) VALUES (
								29,
								'". $cliente_id ."',
								'". $id_cuenta_usar_dev ."',
								'". $turno_id ."',
								'". $cc_id ."',
								'". $web_id ."',
								'". $num_operacion ."',
								'". $registro ."',
								'0',
								'". $monto ."',
								'". $monto_comision ."',
								'". $monto ."',
								'0',
								'0',
								'". $nuevo_balance ."',
								'0',
								'2',
								'',
								'". $observacion ."',
								'0',
								'". $usuario_id ."',
								'".$transaccion_id_nuevo."',
								now(),
								'". $id_cuenta_pago ."',
								'1',
								'" . $tipo ."',
								'" . $motivo_devolucion . "',
								'" . $usuario_id . "',
								1
								) 
								";
							$mysqli->query($insert_command);
							$error = '';
							if ($mysqli->error) {
								$result["insert_error"] = $mysqli->error;
								$error = $mysqli->error;
							}
							if ($error === '') {
								$query_verifica = "SELECT id FROM tbl_televentas_clientes_transaccion  ";
								$query_verifica .= " WHERE tipo_id = 29  ";
								$query_verifica .= " AND cliente_id='" . $cliente_id . "'  ";
								$query_verifica .= " AND user_id='" . $usuario_id . "' ";
								$query_verifica .= " AND turno_id='" . $turno_id . "' ";
								$query_verifica .= " AND monto='" . $monto . "' ";
								$query_verifica .= " AND cuenta_id='" . $id_cuenta_usar_dev . "' ";
								$query_verifica .= " AND cuenta_pago_id='" . $id_cuenta_pago . "' ";
								$query_verifica .= " AND id_motivo_dev='" . $motivo_devolucion . "' ";
								$query_verifica .= " AND transaccion_id='" . $transaccion_id_nuevo . "' ";
								$query_verifica .= " ORDER BY id DESC ";
								$list_query = $mysqli->query($query_verifica);
								$list_transaccion_verifica_aprobacion = array();
								if ($mysqli->error) {
									$result["http_code"] = 400;
									$result["status"] = "Ocurrió un error al consultar la transacción aprobación.";	
									$result["query_verifica"] = $query_verifica;
									$result["query_error"] = $mysqli->error;
								}else{
									while ($li = $list_query->fetch_assoc()) {
										$list_transaccion_verifica_aprobacion[] = $li;
									}
									if (count($list_transaccion_verifica_aprobacion) === 0) {
										$result["http_code"] = 400;
										$result["status"] = "No se guardó la transacción.";								
									} elseif (count($list_transaccion_verifica_aprobacion) === 1) {
										$transaccion_id_aprobacion = $list_transaccion_verifica_aprobacion[0]["id"];

										$query_update_balance = " 
										UPDATE tbl_televentas_clientes_balance 
										SET
											balance = balance - " . $monto . ",
											updated_at = now()
										WHERE tipo_balance_id = 4
											AND cliente_id=" . $cliente_id;
										$mysqli->query($query_update_balance);

										$query_update_balance = " 
										UPDATE tbl_televentas_clientes_balance 
										SET
											balance = balance - " . $monto . ",
											updated_at=now()
										WHERE tipo_balance_id = 1
											AND cliente_id=" . $cliente_id;
										$mysqli->query($query_update_balance);

										query_tbl_televentas_clientes_balance_transaccion('insert', $transaccion_id_nuevo, 
											$cliente_id, 1, $balance_total, $monto, $nuevo_balance);

										query_tbl_televentas_clientes_balance_transaccion('insert', $transaccion_id_nuevo, 
											$cliente_id, 4, $balance_deposito, $monto, $nuevo_balance_deposito);

										sec_tlv_asignar_etiqueta_test($cliente_id);

										// IMAGEN
										//**************************************************************************************************
										$path = "/var/www/html/files_bucket/retiros/";
										$file = [];
										$imageLayer = [];
										if (!is_dir($path))
											mkdir($path, 0777, true);
										$imageProcess = 0;

										$filename = $_FILES['SecDevC7_input_file_voucher']['tmp_name'];
										$filenametem = $_FILES['SecDevC7_input_file_voucher']['name'];
										$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
										if ($filename != "") {
											$fileExt = pathinfo($_FILES['SecDevC7_input_file_voucher']['name'], PATHINFO_EXTENSION);
											$resizeFileName = $transaccion_id_aprobacion . "_" . date('YmdHis');
											$nombre_archivo = $resizeFileName . ".png"; //" . $fileExt;
											if ($fileExt == "pdf") {
												move_uploaded_file($_FILES['SecDevC7_input_file_voucher']['tmp_name'], $path . $nombre_archivo);
											} else {
												$sourceProperties = getimagesize($filename);
												$size = $_FILES['SecDevC7_input_file_voucher']['size'];
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
												$imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);

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
														'" . $transaccion_id_aprobacion . "',
														1,
														'" . $nombre_archivo . "',
														'" . date('Y-m-d H:i:s') . "',
														1
													)";
											$mysqli->query($comando);

											$query_verifica_2 = "SELECT * FROM tbl_televentas_transaccion_archivos
																WHERE transaccion_id = " . $transaccion_id_aprobacion ."
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
												$result["result"] = "Solicitud de Devolución Registrada";
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

							}else {
								$result["http_code"] = 400;
								$result["status"] = "Ocurrió un error al guardar la validación.";
							}

						}
					}
				}
			}else{
				$result["http_code"] = 400;
				$result["status"] = "El balance disponible es menor al monto de la solicitud.";
			}
		}else{
			$result["http_code"] = 400;
			$result["status"] = "El balance es menor al monto de la solicitud.";
		}
	}

}

//*******************************************************************************************************************
//*******************************************************************************************************************
// DINERO AT
//*******************************************************************************************************************
//*******************************************************************************************************************
if ($_POST["accion"] === "consultar_transacciones_activas_evento") {

	$cliente_id = $_POST["cliente_id"];
	$fecha_actual = date('Y-m-d');
 
	// solo entran JV de GR desde MiBILLETERA
	$query = "
		SELECT
			e.id evento_dineroat_id,
			-- e.juegos_virtuales_activo juegos_virtuales,
			0 juegos_virtuales,
			e.bingo_activo bingo,
			e.sportbook_activo sportbook,
			IFNULL(ce.rollover_monto, 0) rollover_monto
		FROM
			tbl_televentas_dinero_at_eventos e
			INNER JOIN tbl_televentas_dinero_at_eventos_clientes ce ON e.id = ce.dinero_at_evento_id
		WHERE
			e.estado = 1
			AND ce.estado = 1
			AND ce.cliente_id = $cliente_id
			AND e.fecha_inicio <= '".$fecha_actual."'
			AND e.fecha_fin >= '".$fecha_actual."'
		LIMIT 1
	";
		 
	$list_query = $mysqli->query($query);
	$list = array();
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["status"] = "No se puede obtener transacciones activas, con Saldo Promocional.";
		$result["query"] = $query;
		$result["query_error"] = $mysqli->error;
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$list[] = $li;
		}
	}
	if (count($list) > 0) { 
		$result["http_code"] = 200;
		$result["status"] = "Transacciones dinero AT, obtenidas.";
		$result["evento_dineroat_id"] = $list[0]["evento_dineroat_id"];
		$result["result"] = $list;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "El cliente no cuenta con una promoción activa.";
	}
 
}

//*******************************************************************************************************************
// TRANSFERIR BALANCE DINERO AT HACIA EL BALANCE REAL
//*******************************************************************************************************************
if ($_POST["accion"] === "transferir_saldo_promocional") {

	$id_cliente         = $_POST["id_cliente"];
	$evento_dineroat_id = (int)$_POST["evento_dineroat_id"];

	// Consultar el monto en apuestas generadas con Dinero AT, por evento
	if ( !(int)$evento_dineroat_id > 0 ) {
		$result["http_code"] = 400;
		$result["status"] = "No se esta enviando un id válido para el Evento Promocional.";
		$result["evento_dineroat_id"] = $evento_dineroat_id;
		echo json_encode($result);exit();
	}

	$query_dineroat = "
		SELECT
			(	SELECT IFNULL(SUM(monto), 0) 
				FROM tbl_televentas_clientes_transaccion
				WHERE estado=1
					AND tipo_id=4
					AND id_tipo_balance=6
					AND cliente_id= $id_cliente
					AND evento_dineroat_id=$evento_dineroat_id
			) rollover_acumulado,
			IFNULL(dat_ec.rollover_monto, 0) rollover_meta,
			cb.balance balance_dineroat,
			dat_e.tipo_conversion,
			dat_e.conversion_maxima,
			dat_ec.monto bono_cliente
		FROM tbl_televentas_dinero_at_eventos dat_e 
			LEFT JOIN tbl_televentas_dinero_at_eventos_clientes dat_ec ON dat_ec.dinero_at_evento_id = dat_e.id
			LEFT JOIN tbl_televentas_clientes_balance cb ON dat_ec.cliente_id = cb.cliente_id AND cb.tipo_balance_id=6
		WHERE  dat_e.estado = 1
			AND dat_ec.estado = 1
			AND dat_ec.cliente_id = $id_cliente
			AND dat_ec.dinero_at_evento_id = $evento_dineroat_id	
		LIMIT 1
	";
	$list_query_dineroat = $mysqli->query($query_dineroat);
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["status"] = "No se pudo consultar los datos del rollover.";
		$result["query"] = $query_dineroat;
		$result["query_error"] = $mysqli->error;
		echo json_encode($result);exit();
	}
	$list_rollover = array();
	while ($li = $list_query_dineroat->fetch_assoc()) {
		$list_rollover[] = $li;
	}
	if ( count($list_rollover)===1 ) {
		$rollover_acumulado = (double)$list_rollover[0]["rollover_acumulado"];
		$rollover_meta      = (double)$list_rollover[0]["rollover_meta"];
		$balance_dineroat   = (double)$list_rollover[0]["balance_dineroat"];
		$bono_cliente       = (double)$list_rollover[0]["bono_cliente"];
		$tipo_conversion    = (int)$list_rollover[0]["tipo_conversion"];
		$conversion_maxima  = (double)$list_rollover[0]["conversion_maxima"];
		
		if ( $rollover_acumulado < $rollover_meta ) {
			$result["http_code"] = 400;
			$result["status"] = "El rollover acumulado ($rollover_acumulado) no a cumplido la meta correspondiente ($rollover_meta).";
			echo json_encode($result);exit();
		}
		if ( $tipo_conversion == 2 ) {
			$conversion_maxima = round( $bono_cliente*$conversion_maxima/100, 2);
		}
		$monto_transferencia = $balance_dineroat;
		if ( $monto_transferencia > $conversion_maxima ) {
			$monto_transferencia = $conversion_maxima;
		}

		$list_balance = array();
		$list_balance = obtener_balances($id_cliente);

		if (count($list_balance) !== 1) {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al consultar el balance.";
			$result["result"] = $list_balance;
		}
		$balance_actual          = (double)$list_balance[0]["balance"];
		$balance_retiro_actual   = (double)$list_balance[0]["balance_retiro_disponible"];
		$balance_dineroat_actual = (double)$list_balance[0]["balance_dinero_at"];

		if ( $balance_dineroat_actual <= 0 ) {
			$result["http_code"] = 400;
			$result["status"] = "No tienes balance Promocional para transferirlo.";
			echo json_encode($result);exit();
		}

		$balance_nuevo    = (double)$balance_actual + (double)$monto_transferencia;
		$bal_retiro_nuevo = (double)$balance_retiro_actual + (double)$monto_transferencia;

		$insert_command_0 = "
			INSERT INTO tbl_televentas_clientes_transaccion (
				tipo_id,
				cliente_id,
				user_id,
				turno_id,
				monto,
				nuevo_balance,
				id_tipo_balance,
				evento_dineroat_id,
				created_at
			) VALUES (
				37,
				" . $id_cliente . ",
				" . $usuario_id . ",
				" . $turno_id . ",
				$monto_transferencia,
				0,
				6,
				$evento_dineroat_id,
				now()
			)";
		$mysqli->query($insert_command_0);

		$query_3_0 = "
			SELECT 
				id cod_trans
			FROM tbl_televentas_clientes_transaccion 
			WHERE tipo_id = 37
			AND cliente_id = $id_cliente
			AND user_id = '$usuario_id' 
			AND turno_id = '$turno_id'
			AND monto = $monto_transferencia
			AND nuevo_balance = 0
			AND id_tipo_balance = 6
			AND evento_dineroat_id = $evento_dineroat_id
			";
		//$result["query_3"]=$query_3;
		$list_query_3_0 = $mysqli->query($query_3_0);
		if ($mysqli->error) {
			$result["http_code"] = 400;
			$result["status"] = "Error al consultar Transferencia de Dinero AT";
			$result["query"] = $query_3_0;
			$result["query_error"] = $mysqli->error;
			echo json_encode($result);exit();
		}
		$list_trans = array();
		while ($li = $list_query_3_0->fetch_assoc()) {
			$list_trans[] = $li;
		}

		if (count($list_trans) == 0) {
			$result["http_code"] = 400;
			$result["status"] = "No se guardó la transacción, Transferencia Dinero AT.";
		} 

		$insert_command = "
			INSERT INTO tbl_televentas_clientes_transaccion (
				tipo_id,
				cliente_id,
				user_id,
				turno_id,
				monto,
				nuevo_balance,
				id_tipo_balance,
				evento_dineroat_id,
				created_at
			) VALUES (
				36,
				" . $id_cliente . ",
				" . $usuario_id . ",
				" . $turno_id . ",
				$monto_transferencia,
				$balance_nuevo,
				1,
				$evento_dineroat_id,
				now()
			)";
		$mysqli->query($insert_command);

		$query_3 = "
			SELECT 
				id cod_transaccion
			FROM tbl_televentas_clientes_transaccion 
			WHERE tipo_id = 36
			AND cliente_id = $id_cliente
			AND user_id = '$usuario_id' 
			AND turno_id = '$turno_id'
			AND monto = $monto_transferencia
			AND nuevo_balance = $balance_nuevo
			AND id_tipo_balance = 1
			AND evento_dineroat_id = $evento_dineroat_id
			";
		$list_query = $mysqli->query($query_3);
		if ($mysqli->error) {
			$result["http_code"] = 400;
			$result["status"] = "Error al consultar Pago de Dinero AT";
			$result["query"] = $query_3;
			$result["query_error"] = $mysqli->error;
			echo json_encode($result);exit();
		}
		$list_transaccion = array();
		while ($li = $list_query->fetch_assoc()) {
			$list_transaccion[] = $li;
		}
		if (count($list_transaccion) == 0) {
			$result["http_code"] = 400;
			$result["status"] = "No se guardó la transacción, Pago Dinero AT.";
		} elseif (count($list_transaccion) === 1) {
			//*********************** UPDATE BALANCE PRINCIPAL
			query_tbl_televentas_clientes_balance('update', $id_cliente, 1, $balance_nuevo);
			//*********************** UPDATE BALANCE RETIRO DISPONIBLE
			query_tbl_televentas_clientes_balance('update', $id_cliente, 5, $bal_retiro_nuevo);
			//*********************** UPDATE BALANCE DINERO AT
			query_tbl_televentas_clientes_balance('update', $id_cliente, 6, 0);

			//*********************** INSERT BALANCE-TRANSACCIÓN DINERO AT
			query_tbl_televentas_clientes_balance_transaccion('insert', $list_transaccion[0]["cod_transaccion"], $id_cliente, 1, $balance_actual, $monto_transferencia, $balance_nuevo);
			query_tbl_televentas_clientes_balance_transaccion('insert', $list_transaccion[0]["cod_transaccion"], $id_cliente, 5, $balance_retiro_actual, $monto_transferencia, $bal_retiro_nuevo);
			query_tbl_televentas_clientes_balance_transaccion('insert', $list_trans[0]["cod_trans"], $id_cliente, 6, $balance_dineroat_actual, $balance_dineroat_actual, 0);

			$update_cmd = "
				UPDATE tbl_televentas_dinero_at_eventos_clientes
				SET estado = 2, updated_at = NOW(), updated_user_id = $usuario_id
				WHERE cliente_id = $id_cliente AND dinero_at_evento_id = $evento_dineroat_id
			";
			$mysqli->query($update_cmd);

			$result["http_code"] = 200;
			$result["status"] = "Transferencia a Saldo Real, exitosa.";
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al guardar la transacción.";
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Dinero AT, ya transferido.";
	}
}
//*******************************************************************************************************************
//*******************************************************************************************************************
// FIN -- DINERO AT
//*******************************************************************************************************************
//*******************************************************************************************************************


/******************************************************************************************************************
 ***********************************************GOLDEN RACE********************************************************
 ******************************************************************************************************************/

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_tlv_obtener_session_gr") {
	$client_id     = $_POST["id_cliente"];
	$cliente_name  = $_POST["cliente_name"];
	$balance       = $_POST["balance_total"];
	$timestamp     = $_POST["timestamp"];
	$continue = 0;
	$parentId = 0;
	//Verificar usuario
	$rr_status_client = api_goldenrace_verify_user($client_id);
	$result["rr_status_client"] = $rr_status_client;
	if($rr_status_client["http_code"] == 200){
		$parentId = $rr_status_client["response"]["id"];
		$continue = 1;
	}else{
		$continue = 0;
		$rr_status_client_create = [];
		$rr_status_client_create = api_goldenrace_create_user($client_id,$cliente_name);
		$result["rr_status_client_create"] = $rr_status_client_create;
		if($rr_status_client_create != ""){
			if(isset($rr_status_client_create["id"])){
				$parentId = $rr_status_client_create["id"];
				$continue = 1;
			}else{
				$result["http_code"] = 400;
				$result["status"] = "No se pudo crear el usuario.";
				$result["rr_status_client_create"] = $rr_status_client_create;
				echo json_encode($result); exit();
			}
		}else{
			$continue  = 0;
			$result["http_code"] = 400;
			$result["status"] = "El API no dió respuesta al crear el usuario";
			echo json_encode($result); exit();
		}
	}

	//Obtener Billetera
	if($continue == 1){
		$wallet = [];
		$wallet = api_goldenrace_get_wallet($parentId, $client_id);
		$result["wallet"] = $wallet;
		if(isset($wallet["http_code"]) && $wallet["http_code"] == 200){
			$continue = 1;
		}else if(isset($wallet["http_code"]) && $wallet["http_code"] == 204){
			$continue = 0;

			//Crear Billetera
			$wallet_c = [];
			$wallet_c = api_goldenrace_create_wallet($parentId, $client_id);
			$result["wallet_c"] = $wallet_c;
			if(isset($wallet_c["id"])){
				$continue = 1;
			}else{
				$result["http_code"] = 400;
				$result["status"] = "El API no dió respuesta al crear la billetera del cliente.";
				echo json_encode($result); exit();
			}
		}else{
			$result["http_code"] = 400;
			$result["status"] = "El API no dió respuesta al obtener la billetera del cliente.";
			echo json_encode($result); exit();
		}
	}else{
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error inesperado, no se pudo obtener la billetera.";
		echo json_encode($result); exit();
	}

	//Iniciar sesión
	if($continue == 1){
		$rr_status_session_login = api_goldenrace_get_onLineHash($parentId);
		$result["rr_status_session_login"] = $rr_status_session_login;
		if(isset($rr_status_session_login["onlineHash"])){
			if($rr_status_session_login["onlineHash"] != ""){
				$result["http_code"] = 200;
				$result["status"] = "OK";
				$result["parentId"] = $rr_status_session_login["unit"]["id"];
				$result["onlineHash"] = $rr_status_session_login["onlineHash"];
			}else{
				$result["http_code"] = 400;
				$result["status"] = "No se pudo obtener la sesion de usuario.";
			}
		}else{
			$result["http_code"] = 400;
			$result["status"] = "La API no devolvió el valor esperado.";
		}
	}else{
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error inesperado, no se pudo iniciar sesión";
		echo json_encode($result); exit();
	}
}

function api_goldenrace_verify_user($client_id){
	$API_header = [];
	$API_header["apiId"]        = env('GOLDENRACE_API_ID');
	$API_header["apiHash"]      = env('GOLDENRACE_API_HASH');
	$API_header["apiDomain"]    = env('GOLDENRACE_API_DOMAIN');
	$API_header["entityId"]     = env('GOLDENRACE_ENTITY_ID');

	$request_headers = array(
		'apiId: '.$API_header["apiId"],
		'apiHash: '.$API_header["apiHash"],
		'apiDomain: '.$API_header["apiDomain"]
	);

	$url = env('GOLDENRACE_API_URL') .'entity/findById?extId=' . $client_id . '&parentId=' . $API_header["entityId"];

	$curl = curl_init();
	curl_setopt($curl, CURLOPT_URL,$url);
	curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_ENCODING, '');
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
	curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
	$response = curl_exec($curl);
	$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
	curl_close($curl);

	$rr_result = [];
	$rr_result["url"] = $url;
	$rr_result["response"] = json_decode($response, true);
	$rr_result["http_code"] = $http_code;

	return $rr_result;
}

function api_goldenrace_create_user($client_id, $cliente_name) {
	$API_header = [];
	$API_header["apiId"]        = env('GOLDENRACE_API_ID');
	$API_header["apiHash"]      = env('GOLDENRACE_API_HASH');
	$API_header["apiDomain"]    = env('GOLDENRACE_API_DOMAIN');
	$API_header["entityId"]     = env('GOLDENRACE_ENTITY_ID');

	$url = env('GOLDENRACE_API_URL');
	$url .= 'entity/add?' 
			. 'entityParentId=' . $API_header["entityId"]
			. '&entityName='. str_replace(' ','%20', $cliente_name)
			. '&extId=' . $client_id 
			. '&client=false'
			. '&status=ENABLED'
			. '&profiles=Client,Cashier';

	$request_headers = array();
	$request_headers[] = 'Content-type: application/json';
	$request_headers[] = 'cache-control: no-cache';
	$request_headers[] = 'apiId: '.$API_header["apiId"];
	$request_headers[] = 'apiHash: '.$API_header["apiHash"];
	$request_headers[] = 'apiDomain: '.$API_header["apiDomain"];
	$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
	$response = curl_exec($curl);
	$err = curl_error($curl);
	$response_arr = json_decode($response, true);
	curl_close($curl);
	return $response_arr;
}

function api_goldenrace_get_onLineHash($parentId) {
	$API_header = [];
	$API_header["apiId"]        = env('GOLDENRACE_API_ID');
	$API_header["apiHash"]      = env('GOLDENRACE_API_HASH');
	$API_header["apiDomain"]    = env('GOLDENRACE_API_DOMAIN');

	$url = env('GOLDENRACE_API_URL') .'session/login?accountId=' . $parentId . '&userId=' . $parentId;

	$request_headers = array();
	$request_headers[] = 'Content-type: application/json';
	$request_headers[] = 'cache-control: no-cache';
	$request_headers[] = 'apiId: '.$API_header["apiId"];
	$request_headers[] = 'apiHash: '.$API_header["apiHash"];
	$request_headers[] = 'apiDomain: '.$API_header["apiDomain"];
	$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
	$response = curl_exec($curl);
	$err = curl_error($curl);
	$response_arr = json_decode($response, true);
	curl_close($curl);
	return $response_arr;
}

function api_goldenrace_get_wallet($parentId, $extId){
	$rr_result = [];
	try {
		$API_header = [];
		$API_header["apiId"]        = env('GOLDENRACE_API_ID');
		$API_header["apiHash"]      = env('GOLDENRACE_API_HASH');
		$API_header["apiDomain"]    = env('GOLDENRACE_API_DOMAIN');
		$API_header["entityId"]     = env('GOLDENRACE_ENTITY_ID');

		$request_headers = array(
			'apiId: '.$API_header["apiId"],
			'apiHash: '.$API_header["apiHash"],
			'apiDomain: '.$API_header["apiDomain"]
		);

		$url = env('GOLDENRACE_API_URL') .'wallet/findById?entityId=' . $parentId . '&extId=' . $extId . '&withChildren=false';

		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL,$url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_ENCODING, '');
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
		$response = curl_exec($curl);
		$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		curl_close($curl);

		$rr_result["response"] = json_decode($response, true);
		$rr_result["http_code"] = $http_code;
	} catch (Exception $e) {
		$rr_result["http_code"] = 400;
		$rr_result["error"] = 'Excepción capturada: '.  $e->getMessage();
		$rr_result["response"] = '';
    }
	return $rr_result;
}

function api_goldenrace_create_wallet($parentId, $extId) {
	$API_header = [];
	$API_header["apiId"]        = env('GOLDENRACE_API_ID');
	$API_header["apiHash"]      = env('GOLDENRACE_API_HASH');
	$API_header["apiDomain"]    = env('GOLDENRACE_API_DOMAIN');
	$API_header["entityId"]     = env('GOLDENRACE_ENTITY_ID');

	$url = env('GOLDENRACE_API_URL');
	$url .= 'wallet/create?' 
			. 'entityId=' . $parentId
			. '&extId='. $extId
			. '&currency=PEN'
			. '&balance=0';

	$request_headers = array();
	$request_headers[] = 'Content-type: application/json';
	$request_headers[] = 'cache-control: no-cache';
	$request_headers[] = 'apiId: '.$API_header["apiId"];
	$request_headers[] = 'apiHash: '.$API_header["apiHash"];
	$request_headers[] = 'apiDomain: '.$API_header["apiDomain"];
	$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
	$response = curl_exec($curl);
	$err = curl_error($curl);
	$response_arr = json_decode($response, true);
	curl_close($curl);
	return $response_arr;
}

/******************************************************************************************************************
 ***********************************************GOLDEN RACE********************************************************
 ******************************************************************************************************************/


//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************
//*****************************************************BINGO*********************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "sec_tlv_listar_salas_bingo") {
	$id_cliente = $_POST["id_cliente"];
	$usuario_id = $login ? $login['id'] : 0;
	if ((int) $usuario_id > 0) {
		$turno = get_turno();
		if (count($turno) > 0) {
			$turno_id = $turno[0]["id"];
			if ((int) $turno_id > 0) {
				$array_games_info = api_e2e($id_cliente);
				if(isset($array_games_info) && count($array_games_info) > 0){
					$result["array_games_info"] = $array_games_info;
					$result["http_code"] = 200;
				}else{
					$result["http_code"] = 400;
					$result["status"] = "No hay salas disponibles para comprar cartones";
					$result["array_games_info"] = $array_games_info;
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
		$result["status"] = "Sesión perdida.";
	}
}

function api_e2e($client_id){
	$method = 'gamesInfo';
	$accountIdentifier = env('END2END_BINGO_ACCOUNT_IDENTIFIER');

	$url = env('END2END_BINGO_URL') . $method . "/";
	$url .= "?accountIdentifier=" . $accountIdentifier;
	$auditoria_id = api_calimaco_auditoria_insert('gamesInfo', '', $client_id, $url);
    $response = null;
	$result = array();
	$status = 0;

    try {
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		));
		$response = curl_exec($curl);
		$err = curl_error($curl);
		$response_arr = json_decode($response, true);
		curl_close($curl);

		if ($err) {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al consumir el API.";
			$result["result"] = $response;
			$result["error"] = "cURL Error #:" . $err;
		} else {
			$result = $response_arr;
			if(isset($response_arr["result"])){
				$status = ($response_arr["result"]==="OK") ? 1 : 0;
			}
		}
    } catch (Exception $e) {
    	$response = 'Excepción capturada: '.  $e->getMessage();
    }
	api_calimaco_auditoria_update($auditoria_id, '', $response, $status);
	return $result;
}

//*******************************************************************************************************************
// BINGO
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "sec_tlv_comprar_bingo") {
	$room_id 				= $_POST["room_id"];
	$gameId 				= $_POST["gameId"];
	$quantity 			= $_POST["quantity"];
	$cardPrice 			= $_POST["cardPrice"];
	$cardHolderName = $_POST["cardHolderName"];
	$total_cobrar 	= $_POST["total_cobrar"];
	$cliente_id 		= $_POST["cliente_id"];
	$roomName 			= $_POST["roomName"];
	$game_type 			= $_POST["game_type"];
	$usuario_id 		= $login ? $login['id'] : 0;
	$balance_tipo       = (int)$_POST['temp_balance_tipo'];
	$evento_dineroat_id = (int)$_POST['evento_dineroat_id'];

	if ( (int)$_POST["evento_dineroat_id"] === 0 ) {
		$evento_dineroat_id = 'null';
	}

	if ((int) $usuario_id > 0) {
		$turno = get_turno();
		if (count($turno) > 0) {
			$turno_id = $turno[0]["id"];
			$cc_id = $turno[0]["cc_id"];
			if ((int) $turno_id > 0) {
				$list_balance = array();
				$list_balance = obtener_balances($cliente_id);
				if (count($list_balance) === 1) {
					if ( (int)$balance_tipo===6 ){
						$balance_actual = $list_balance[0]["balance_dinero_at"];
					} else {
						$balance_actual = $list_balance[0]["balance"];
					}
					$balance_deposito_actual = $list_balance[0]["balance_deposito"];
					$balance_retiro_actual = $list_balance[0]["balance_retiro_disponible"];
					$balance_deposito_nuevo = $balance_deposito_actual;
					$balance_retiro_nuevo = $balance_retiro_actual;
					if($total_cobrar >= 0.95 && $total_cobrar <= 0.99) {
						$total_cobrar = 1;
					}

					if($balance_actual > 0){
						if($balance_actual >= $total_cobrar){
							$balance_nuevo = (double)$balance_actual - (double)$total_cobrar;
							$fecha_hora_actual = date('Y-m-d H:i:s');
							//Guardar Transacción
							$insert_command = "
							INSERT INTO tbl_televentas_clientes_transaccion 
							(tipo_id,cliente_id,turno_id,cc_id, api_id,bono_id,monto,bono_monto,total_recarga,
								nuevo_balance,estado,user_id,created_at,id_tipo_balance,evento_dineroat_id) 
							VALUES 
							(4," . $cliente_id . "," . $turno_id . "," . $cc_id . ", 4, 0," . $total_cobrar . ",0," 
								. $total_cobrar . "," . $balance_nuevo . ",0," . $usuario_id . ", '" . $fecha_hora_actual . "', $balance_tipo, $evento_dineroat_id)";
							$mysqli->query($insert_command);

							$query_3 = "SELECT id FROM tbl_televentas_clientes_transaccion  ";
							$query_3 .= " WHERE tipo_id = 4";
							$query_3 .= " AND cliente_id = '" . $cliente_id . "' AND user_id = '" . $usuario_id . "' ";
							$query_3 .= " AND turno_id = '" . $turno_id . "'";
							$query_3 .= " AND nuevo_balance = '" . $balance_nuevo . "' ";
							$query_3 .= " AND monto = '" . $total_cobrar . "' ";
							$query_3 .= " AND total_recarga = '" . $total_cobrar . "'";
							$query_3 .= " AND created_at = '" . $fecha_hora_actual . "'";
							$query_3 .= " AND id_tipo_balance = '" . $balance_tipo . "'";
							$list_query = $mysqli->query($query_3);

							$list_transaccion = array();
							while ($li = $list_query->fetch_assoc()) {
								$list_transaccion[] = $li;
							}

							if (count($list_transaccion) == 0) {
								$result["http_code"] = 400;
								$result["status"] = "No se pudo completar la transacción.";
							}elseif (count($list_transaccion) === 1) {
								$monto = $total_cobrar;
								$guard_string = $list_transaccion[0]["id"];
								
								$usuario = "";
								$password = "";

								$rr_credenciales = array();
								$rr_credenciales = sec_tlv_get_credential_bingo_user();
								if(count($rr_credenciales) == 0){
									$result["http_code"] = 400;
									$result["status"] = "No se encontraron las credenciales del usuario.";
									$result["list_cred"] = $list_cred;
									echo json_encode($result);exit();
								}else{
									$usuario = $rr_credenciales[0]["user"];
									$password = $rr_credenciales[0]["password"];
								}

								$array_ticket_buy = array();
								$array_ticket_buy = api_e2e_buyBingoCardsOnRoom($room_id, $gameId, $quantity, $cardPrice, $cardHolderName, $guard_string, $cliente_id, $usuario, $password);

								if(isset($array_ticket_buy["result"]["gameId"])){
									if(isset($array_ticket_buy["result"]["gameId"])) {
										$gameId = $array_ticket_buy["result"]["gameId"];
										$startsAt = $array_ticket_buy["result"]["startsAt"];
										$rr_cards = array();
										$rr_cards = json_encode($array_ticket_buy["result"]["cards"],true);
										$cardHolderName = $array_ticket_buy["result"]["cardHolderName"];

										$id_txn = $array_ticket_buy["result"]["guestResponse"]["id"];
										$token = $array_ticket_buy["result"]["guestResponse"]["token"];

										$extraData = $array_ticket_buy["result"]["extraData"];
										$players = $array_ticket_buy["result"]["players"];


										$update_cmd = "
											UPDATE 
												tbl_televentas_clientes_transaccion
											SET
												txn_id = '" . $id_txn . "',
												estado = 1
											WHERE id = " . $guard_string;
										$mysqli->query($update_cmd);

										//*********************** BALANCE TOTAL
										query_tbl_televentas_clientes_balance('update', $cliente_id, $balance_tipo, $balance_nuevo);
										query_tbl_televentas_clientes_balance_transaccion('insert', $guard_string, 
														$cliente_id, $balance_tipo, $balance_actual, $monto, $balance_nuevo);

										if ( (int)$balance_tipo!=6 ) {
				
											//*********************** BALANCE DEPOSITO
											if((double)$balance_deposito_actual > 0){
												$resto = 0;
												if((double)$balance_deposito_nuevo > (double)$monto){
													$balance_deposito_nuevo = (double)$balance_deposito_nuevo - (double)$monto;
													$resto = $monto;
													$monto = 0;
												} else {
													$monto = (double)$monto - (double)$balance_deposito_nuevo;
													$resto = $balance_deposito_nuevo;
													$balance_deposito_nuevo = 0;
												}
												query_tbl_televentas_clientes_balance('update', $cliente_id, 4, $balance_deposito_nuevo);
												query_tbl_televentas_clientes_balance_transaccion('insert', $guard_string, 
															$cliente_id, 4, $balance_deposito_actual, $resto, $balance_deposito_nuevo);
											}

											//*********************** BALANCE RETIRO
											if((double)$monto > 0){
												if((double)$balance_retiro_actual > 0){
													$resto = 0;
													if((double)$balance_retiro_nuevo > (double)$monto){
														$balance_retiro_nuevo = $balance_retiro_nuevo - $monto;
														$resto = $monto;
														$monto = 0;
													} else {
														$monto = (double)$monto - (double)$balance_retiro_nuevo;
														$resto = $balance_retiro_nuevo;
														$balance_retiro_nuevo = 0;
													}
													query_tbl_televentas_clientes_balance('update', $cliente_id, 5, $balance_retiro_nuevo);
													query_tbl_televentas_clientes_balance_transaccion('insert', $guard_string, 
															$cliente_id, 5, $balance_retiro_actual, $resto, $balance_retiro_nuevo);
												}
											}
										}

										$ticket = query_tbl_televentas_tickets_get_array();
										$ticket['ticket_id'] = $id_txn;
										$ticket['proveedor_id'] = '4';
										$ticket['created'] =  $fecha_hora_actual;
										$ticket['calc_date'] = '';
										$ticket['paid_date'] = '';
										$ticket['external_id'] = $token;
										$ticket['game'] = $game_type;
										$ticket['sell_local_id'] = $cc_id;
										$ticket['paid_local_id'] = 0;
										$ticket['num_selections'] = $quantity;
										$ticket['price'] = $cardPrice;
										$ticket['stake_amount'] = $total_cobrar;
										$ticket['winning_amount'] = 0;
										$ticket['jackpot_amount'] = 0;
										$ticket['status'] = 1;
										query_tbl_televentas_tickets($ticket);

										//Insertar tabla tickets bingo
										$ticket_bingo_insert_cmd = "
										INSERT INTO tbl_televentas_tickets_bingo 
											(ticket_id,game_id,startsAt, cardHolderName, guestResponseToken, players, roomName, game_type, created_at)
										VALUES 
											('" . $id_txn . "','" . $gameId . "','" . $startsAt . "','" . $cardHolderName 
												. "','" . $token . "','" . $players . "','" . $roomName . "', '" . $game_type . "',now());
										";
										$mysqli->query($ticket_bingo_insert_cmd);

										//Insertar tabla tickets bingo cards
										$json = json_decode($rr_cards,true);
										foreach($json as $c){
											$id = $c["id"];
											$numbers = $c["numbers"];
											$numbers_string = implode(",",$numbers);
											$ticket_card_insert_cmd = "
											INSERT INTO tbl_televentas_tickets_bingo_cards (ticket_id,card_id,numbers, created_at)
											VALUES ('" . $id_txn . "','" . $id . "','" . $numbers_string . "',now());
											";
											$mysqli->query($ticket_card_insert_cmd);
										}

										$cmd_local = "SELECT nombre FROM tbl_locales WHERE cc_id = " . $cc_id;
										$list_query_local = $mysqli->query($cmd_local);

										$list_local = array();
										while ($li = $list_query_local->fetch_assoc()) {
											$list_local[] = $li;
										}

										sec_tlv_asignar_etiqueta_test($cliente_id);

										$result["status"] = "OK";
										$result["http_code"] = 200;
										$result["result"] = $array_ticket_buy;
										$result["list_local"] = $list_local[0]["nombre"];
										$result["cards"] = json_decode($rr_cards,true);
										$result["numbers"] = implode($numbers);
										$result["fecha_hora_actual"] = $fecha_hora_actual;
										$result["token"] = $token;
										$result["login"] = $login;
									}else{
										//rollback_transaccion($cliente_id, $guard_string, $usuario_id, $turno_id, 'No se pudo registrar la compra',0);
										$result["http_code"] = 400;
										$result["status"] = "No se pudo registrar la compra.";
									}
								}else{
									//rollback_transaccion($cliente_id, $guard_string, $usuario_id, $turno_id, 'Error al consultar la API',0);
									$httpcode = 400;
									if(isset($array_ticket_buy["http_code"])){
										$httpcode = $array_ticket_buy["http_code"];
										if($httpcode == 400 || $httpcode == 502){
											$upd_user_acount = "
												UPDATE 
													tbl_televentas_usuarios_bingo
												SET
													status = 0
												WHERE usuario_id = " . $usuario_id;
											$mysqli->query($upd_user_acount);
										}
									}
									$result["http_code"] = $httpcode;
									$result["status"] = "Error al consultar la API";
									$result["array_ticket_buy"] = $array_ticket_buy;
								}
							}else{
								$result["http_code"] = 400;
								$result["status"] = "Error al guardar la transacción.";
							}
						}else{
							$result["http_code"] = 400;
							$result["status"] = "El total a cobrar es mayor al balance actual.";
						}
					}else{
						$result["http_code"] = 400;
						$result["status"] = "No cuenta con balance disponible.";
					}
				}else{
					$result["http_code"] = 400;
					$result["status"] = "Error al consultar el balance.";
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
		$result["status"] = "Sesión perdida.";
	}
}


//*******************************************************************************************************************
// CONSULTAR BINGO
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "sec_tlv_consultar_bingo") {
	$id_ticket = $_POST["id_ticket"];
	$cliente_id = $_POST["cliente_id"];
	$evento_dineroat_id = $_POST["evento_dineroat_id"];
	$usuario_id = $login ? $login['id'] : 0;

	if ((int) $usuario_id > 0) {
		$turno = get_turno();
		if (count($turno) > 0) {
			$turno_id = $turno[0]["id"];
			$cc_id = $turno[0]["cc_id"];
			if ((int) $turno_id > 0) {

				//get status origin
				$cmd_consulta = "
					SELECT 
						tra.id, tra.txn_id, tra.estado,
						IFNULL(tra.evento_dineroat_id, 0) id_evento_dinero_at,
					    t.status ticket_status,
					    b.startsAt AS start_timestamp,
					    CASE 
							WHEN tra.estado = 4 THEN 3
					        ELSE 1
						END can_cancel_status,
					    t.price card_price, t.winning_amount, b.roomName, b.game_id,
					    IFNULL(b.one_line_jp, 0) one_line_jp, IFNULL(b.two_lines_jp, 0) two_lines_jp, IFNULL(b.three_lines_jp, 0) three_lines_jp,
					    b.game_type, 
					    '' game_winners,
					    '' cards
					FROM 
					tbl_televentas_clientes_transaccion tra
					INNER JOIN tbl_televentas_tickets t ON tra.txn_id = t.ticket_id
					INNER JOIN tbl_televentas_tickets_bingo b ON tra.txn_id = b.ticket_id
					LEFT JOIN tbl_televentas_dinero_at_eventos dat_e ON tra.evento_dineroat_id = dat_e.id
					WHERE 
						tra.txn_id = '" . $id_ticket . "'
					    AND tra.tipo_id = 4
					LIMIT 1;
				";
				$list_cmd_consulta = $mysqli->query($cmd_consulta);
				$list_register = array();
				while ($li = $list_cmd_consulta->fetch_assoc()) {
					$list_register[] = $li;
				} 
				if(count($list_register) > 0){
					$result["id_evento_dinero_at"] = $list_register[0]["id_evento_dinero_at"];
					if ( (int)$list_register[0]["id_evento_dinero_at"] > 0 ) {
						if ( (int)$evento_dineroat_id > 0 ) {
							if ( (int)$list_register[0]["id_evento_dinero_at"] != (int)$evento_dineroat_id ) {
								$result["http_code"] = 400;
								$result["status"] = "El ticket fue comprado en una promoción diferente.";
								echo json_encode($result);exit();
							}
						} else {
							$result["http_code"] = 400;
							$result["status"] = "El ticket fue comprado con Saldo Promocional, cosultarlo desde esa opción.";
							echo json_encode($result);exit();
						}
					} else if ( (int)$list_register[0]["id_evento_dinero_at"] === 0 ) {
						if ( (int)$evento_dineroat_id > 0 ) {
							$result["http_code"] = 400;
							$result["status"] = "El ticket fue comprado con Dinero Real, consultarlo desde esa opción.";
							echo json_encode($result);exit();
						}
					}
					if($list_register[0]["ticket_status"] == 3 || $list_register[0]["ticket_status"] == 5 || $list_register[0]["ticket_status"] == 7 || $list_register[0]["estado"] == 4){
						$cmd_cards = "
							SELECT 
								card_id
							FROM
								tbl_televentas_tickets_bingo_cards
							WHERE 
								ticket_id = '" . $id_ticket . "';
						";
						$list_cmd_cards = $mysqli->query($cmd_cards);
						$list_cards = array();
						while ($li = $list_cmd_cards->fetch_assoc()) {
							$list_cards[] = $li; 
						}

						$cmd_winners = "
							SELECT 
								card_id
							FROM
								tbl_televentas_tickets_bingo_cards
							WHERE 
								ticket_id = '" . $id_ticket . "'
								AND winner = 1;
						";
						$list_cmd_winners = $mysqli->query($cmd_winners);
						$list_winners = array();
						while ($li = $list_cmd_winners->fetch_assoc()) {
							$list_winners[] = $li; 
						}

						$result["http_code"] = 200;
						$result["result"] = $list_register;
						$result["status"] = "list_register";
						$result["cards"] = $list_cards;
						$result["gameWinners"] = $list_winners;
					}else {
						$array_infoByTokenId = array();
						$array_infoByTokenId = api_e2e_infoByTokenId($id_ticket, $cliente_id);
						if(isset($array_infoByTokenId["result"]["start_timestamp"])){
							$result["http_code"] = 200;
							$result["result"] = $array_infoByTokenId;
							$result["status"] = "array_infoByTokenId";
							$result["cards"] = "";
							$result["gameWinners"] = "";
						}else{
							$result["http_code"] = 400;
							$result["status"] = "No se encontró el ticket, vuelva a intertarlo.";
							$result["result"] = $array_infoByTokenId;
						}
					}
				}else{
					$result["http_code"] = 400;
					$result["status"] = "No se encontró el ticket";
					$result["list_register"] = $list_register;
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
		$result["status"] = "Sesión perdida.";
	}
}

//*******************************************************************************************************************
// MARCAR BINGO COMO PAGADO
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "sec_tlv_marcar_pago_bingo") {
	$id_ticket = $_POST["id_ticket"];
	$id_cliente = $_POST["id_cliente"];
	$monto = $_POST["monto"];
	$jackpot_amount = $_POST["jackpot"];
	$balance_tipo = (int)$_POST["balance_tipo"];
	$evento_dineroat_id = $_POST["evento_dineroat_id"];
	$usuario_id = $login ? $login['id'] : 0;

	if ( $evento_dineroat_id == 0 ) {
		$evento_dineroat_id = "NULL";
	}

	date_default_timezone_set("America/Lima");
	$fecha_actual = date('Y-m-d');

	if ((int) $usuario_id > 0) {
		$turno = get_turno();
		if (count($turno) > 0) {
			$turno_id = $turno[0]["id"];
			$cc_id = $turno[0]["cc_id"];
			if ((int) $turno_id > 0) {
				$usuario = "";
				$password = "";

				$rr_credenciales = array();
				$rr_credenciales = sec_tlv_get_credential_bingo_user();
				if(count($rr_credenciales) == 0){
					$result["http_code"] = 400;
					$result["status"] = "No se encontraron las credenciales del usuario.";
					$result["list_cred"] = $list_cred;
					echo json_encode($result);exit();
				}else{
					$usuario = $rr_credenciales[0]["user"];
					$password = $rr_credenciales[0]["password"];
				}

				$markTicketAsPaid = array();
				$markTicketAsPaid = api_e2e_markTicketAsPaid($id_ticket, $id_cliente, $usuario, $password);
				if($markTicketAsPaid == 200){
					$list_balance = array();
					$list_balance = obtener_balances($id_cliente);
					$fecha_hora_actual = date('Y-m-d H:i:s');
					if (count($list_balance) === 1) {
						if ( (int)$balance_tipo == 1 ) {
							$balance_total_actual = (double)$list_balance[0]["balance"];
						} else if ( (int)$balance_tipo == 6 ) {
							$balance_total_actual = (double)$list_balance[0]["balance_dinero_at"];
						}
						$balance_retiro_actual = (double)$list_balance[0]["balance_retiro_disponible"];
						
						$balance_total_nuevo = (double)$balance_total_actual + (double)$monto;
						$balance_retiro_nuevo = (double)$balance_retiro_actual + (double)$monto;
						$insert_command = "
						INSERT INTO tbl_televentas_clientes_transaccion (
							tipo_id,
							api_id,
							txn_id,
							cliente_id,
							user_id,
							turno_id,
							monto,
							nuevo_balance,
							bono_monto,
							estado,
							id_tipo_balance,
							evento_dineroat_id,
							created_at
						) VALUES (
							5,
							4,
							'" . $id_ticket . "',
							" . $id_cliente . ",
							" . $usuario_id . ",
							" . $turno_id . ",
							" . $monto . ",
							" . $balance_total_nuevo . ",
							0,
							1,
							$balance_tipo,
							$evento_dineroat_id,
							now()
						)";
						$mysqli->query($insert_command);

						$query_3 = "SELECT id FROM tbl_televentas_clientes_transaccion  ";
						$query_3 .= " WHERE tipo_id='5' AND api_id = 4 AND txn_id='" . $id_ticket . "' ";
						$query_3 .= " AND user_id='" . $usuario_id . "' ";
						$query_3 .= " AND turno_id='" . $turno_id . "' ";
						$query_3 .= " AND nuevo_balance='" . $balance_total_nuevo . "' ";
						$query_3 .= " AND estado = 1 ";
						$list_query = $mysqli->query($query_3);
						$list_transaccion = array();
						while ($li = $list_query->fetch_assoc()) {
							$list_transaccion[] = $li;
						}
						if (count($list_transaccion) == 0) {
							$result["http_code"] = 400;
							$result["status"] = "Se registro la apuesta, sin embargo no se pudo guardar la transacción.";
						} elseif (count($list_transaccion) === 1) {

							query_tbl_televentas_clientes_balance('update', $id_cliente, $balance_tipo, $balance_total_nuevo);
							query_tbl_televentas_clientes_balance_transaccion('insert', $list_transaccion[0]["id"], 
								$id_cliente, $balance_tipo, $balance_total_actual, $monto, $balance_total_nuevo);
							
							if ($balance_tipo == 1 ) {
								query_tbl_televentas_clientes_balance('update', $id_cliente, 5, $balance_retiro_nuevo);
								query_tbl_televentas_clientes_balance_transaccion('insert', $list_transaccion[0]["id"], 
									$id_cliente, 5, $balance_retiro_actual, $monto, $balance_retiro_nuevo);
							}

							$ticket = query_tbl_televentas_tickets_get_array();
							$ticket['ticket_id'] = $id_ticket;
							$ticket['proveedor_id']   = '4';
							$ticket['created']   = '';
							$ticket['calc_date'] = '';
							$ticket['paid_date'] = $fecha_hora_actual;
							$ticket['external_id']    = '';
							$ticket['game']           = '';
							$ticket['sell_local_id']  = '';
							$ticket['paid_local_id']  = $cc_id;
							$ticket['num_selections'] = 0;
							$ticket['price']          = 0;
							$ticket['stake_amount']   = 0;
							$ticket['winning_amount'] = $monto;
							$ticket['jackpot_amount'] = $jackpot_amount;
							$ticket['status']         = 7;
							$ticket['status_text']    = 'PAID';
							query_tbl_televentas_tickets($ticket);

							sec_tlv_asignar_etiqueta_test($id_cliente);

							$result["http_code"] = 200;
							$result["result"] = $markTicketAsPaid;
							$result["amount"] = $monto;
						} else {
							$result["http_code"] = 400;
							$result["status"] = "Error al registrar la transacción";
						}
					}else{
						$result["http_code"] = 400;
						$result["status"] = "No se pudo actualizar el balance.";
						$result["result"] = $markTicketAsPaid;
					}
				}else{
					$httpcode = 400;
					if(isset($markTicketAsPaid)){
						$httpcode = $markTicketAsPaid;
						if($httpcode == 400 || $httpcode == 502){
							$upd_user_acount = "
								UPDATE 
									tbl_televentas_usuarios_bingo
								SET
									status = 0
								WHERE usuario_id = " . $usuario_id;
							$mysqli->query($upd_user_acount);
						}

					}
					$result["http_code"] = $httpcode;
					$result["status"] = "No se pudo pagar el ticket.";
					$result["result"] = $markTicketAsPaid;
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
		$result["status"] = "Sesión perdida.";
	}
}


//*******************************************************************************************************************
// REEMBOLSAR BINGO
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "sec_tlv_reembolsar_bingo") {
	$id_ticket = $_POST["id_ticket"];
	$id_cliente = $_POST["id_cliente"];
	$monto = $_POST["monto"];
	$balance_tipo = (int)$_POST["balance_tipo"];
	$evento_dineroat_id = (int)$_POST["evento_dineroat_id"];
	$usuario_id = $login ? $login['id'] : 0;

	date_default_timezone_set("America/Lima");
	$fecha_actual = date('Y-m-d');

	/* // Consultar si es ticket generado con Dinero AT, para generar la conversion maxima
	$select_evento_at="
		SELECT			
			ct.txn_id,
			ct.estado,
			DATE(da_ev.fecha_fin) fecha_fin,
			da_ev.conversion_maxima
		FROM tbl_televentas_clientes_transaccion ct
			INNER JOIN tbl_televentas_dinero_at_eventos da_ev ON ct.evento_dineroat_id = da_ev.id
		WHERE ct.txn_id = '".$id_ticket."'
			AND ct.api_id = 4
			AND ct.cliente_id = '".$id_cliente."'
			AND ct.tipo_id = 4
			AND ct.estado = 1
			AND id_tipo_balance = 6
			AND ct.evento_dineroat_id > 0
		LIMIT 1
	";
	$ticket_event_at = $mysqli->query($select_evento_at);
	if ($mysqli->error) {
		$result["ticket_event_at"] = $select_evento_at;
		$result["ticket_event_at_error"] = $mysqli->error;
		echo json_encode($result);exit();
	}
	$list_ticket_event_at = array();
	while ($li = $ticket_event_at->fetch_assoc()) {
		$list_ticket_event_at[] = $li;
	}
	if ( count($list_ticket_event_at)>0 ) {
		$evento_dat_fecha_fin = $list_ticket_event_at[0]["fecha_fin"];
		$result["evento_dat_fecha_fin"] = $evento_dat_fecha_fin;
	}
	// Fin ==> Consultar si es ticket generado con Dinero AT, para generar la conversion maxima */

	if ((int) $usuario_id > 0) {
		$turno = get_turno();
		if (count($turno) > 0) {
			$turno_id = $turno[0]["id"];
			$cc_id = $turno[0]["cc_id"];
			if ((int) $turno_id > 0) {

				$usuario = "";
				$password = "";

				$rr_credenciales = array();
				$rr_credenciales = sec_tlv_get_credential_bingo_user();
				if(count($rr_credenciales) == 0){
					$result["http_code"] = 400;
					$result["status"] = "No se encontraron las credenciales del usuario.";
					$result["list_cred"] = $list_cred;
					echo json_encode($result);exit();
				}else{
					$usuario = $rr_credenciales[0]["user"];
					$password = $rr_credenciales[0]["password"];
				}

				$refundTicket = array();
				$refundTicket = api_e2e_refundTicket($id_ticket, $id_cliente, $usuario, $password);
				if($refundTicket == 200){
					$query_3 = "SELECT id FROM tbl_televentas_clientes_transaccion  ";
					$query_3 .= " WHERE tipo_id='4' AND api_id = 4 AND txn_id='" . $id_ticket . "' ";
					$query_3 .= " AND estado = 1 ";
					$list_query = $mysqli->query($query_3);
					$list_transaccion = array();
					while ($li = $list_query->fetch_assoc()) {
						$list_transaccion[] = $li;
					}
					if (count($list_transaccion) == 0) {
						$result["http_code"] = 400;
						$result["status"] = "No se actualizó la transacción";
					} elseif (count($list_transaccion) === 1) {
						$id_trans = $list_transaccion[0]["id"];

						/* if ( isset($evento_dat_fecha_fin) && $fecha_actual > $evento_dat_fecha_fin ){
							$result["http_code"] = 400;
							$result["result"] = "Promoción venciada el $evento_dat_fecha_fin";
							echo json_encode($result);exit();
						} */

						$result_rollback = rollback_transaccion($id_cliente, $id_trans, $usuario_id, $turno_id, 'Reembolso', 0);

						$update_cmd = "
						UPDATE 
							tbl_televentas_clientes_transaccion
						SET
							estado = 5,
							update_user_id = " . $usuario_id . ",
							updated_at = now()
						WHERE txn_id = '" . $id_ticket . "'";
						$mysqli->query($update_cmd);

						$upd_tlv_ticket = "
						UPDATE 
							tbl_televentas_tickets
						SET
							status = 5,
							updated_at = now()
						WHERE ticket_id = '" . $id_ticket . "'";
						$mysqli->query($upd_tlv_ticket);
						sec_tlv_asignar_etiqueta_test($id_cliente);
						$result["http_code"] = 200;
						$result["result"] = $refundTicket;
						$result["amount"] = $monto;
						$result["result_rollback"] = $result_rollback;
					} else {
						$result["http_code"] = 400;
						$result["status"] = "Error al registrar la transacción";
					}
				}else{
					$httpcode = 400;
					if($refundTicket){
						$httpcode = $refundTicket;
						if($httpcode == 400 || $httpcode == 502){
							$upd_user_acount = "
								UPDATE 
									tbl_televentas_usuarios_bingo
								SET
									status = 0
								WHERE usuario_id = " . $usuario_id;
							$mysqli->query($upd_user_acount);
						}
					}
					$result["http_code"] = 400;
					$result["status"] = "No se pudo reembolsar el ticket.";
					$result["result"] = $refundTicket;
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
		$result["status"] = "Sesión perdida.";
	}
}



//*******************************************************************************************************************
// CANCELAR BINGO
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "sec_tlv_cancelar_bingo") {
	$id_ticket = $_POST["id_ticket"];
	$id_cliente = $_POST["id_cliente"];
	$tipo_balance = $_POST["tipo_balance"];
	$monto = $_POST["monto"];
	$usuario_id = $login ? $login['id'] : 0;

	date_default_timezone_set("America/Lima");
	$fecha_actual = date('Y-m-d');
	$timestamp_actual = strtotime($fecha_actual);

	// Primero consultar si el ticket es con dinero Promocional
	$query_consult = "
		SELECT ct.id, dat_e.id, dat_e.fecha_fin
		FROM tbl_televentas_clientes_transaccion ct
		INNER JOIN tbl_televentas_dinero_at_eventos dat_e ON dat_e.id = ct.evento_dineroat_id
		WHERE ct.tipo_id='4' 
			AND ct.api_id = 4 
			AND ct.txn_id='" . $id_ticket . "'
			AND ct.estado = 1 
			AND ct.id_tipo_balance=6
		LIMIT 1
	";
	$list_id = $mysqli->query($query_consult);
	$list_trans = array();
	while ($li = $list_id->fetch_assoc()) {
		$list_trans[] = $li;
		$fecha_fin_evento = $li["fecha_fin"];
		$timestamp_fecha_fin_evento = strtotime($fecha_fin_evento);
	}
	if ( count($list_trans)===1 ) {
		if ( (int)$tipo_balance!=6 ){
			$result["http_code"] = 400;
			$result["id_transaccion"] = $list_trans[0]["id"];
			$result["result"] = "Este ticket fue registrado con Saldo Promocional, para realizar la cancelación del mismo, tenga seleccionada la opción de Saldo Promocional.";
			echo json_encode($result);exit();
		}
		if ( $timestamp_actual > $timestamp_fecha_fin_evento ) {
			$result["http_code"] = 400;
			$result["id_transaccion"] = $list_trans[0]["id"];
			$result["result"] = "Este ticket fue registrado con Saldo Promocional, no se puede revertir debido a que tiene la promoción caducada.";
			echo json_encode($result);exit();
		}
	}
	
	if ((int) $usuario_id > 0) {
		$turno = get_turno();
		if (count($turno) > 0) {
			$turno_id = $turno[0]["id"];
			$cc_id = $turno[0]["cc_id"];
			if ((int) $turno_id > 0) {
				$usuario = "";
				$password = "";

				$rr_credenciales = array();
				$rr_credenciales = sec_tlv_get_credential_bingo_user();
				if(count($rr_credenciales) == 0){
					$result["http_code"] = 400;
					$result["status"] = "No se encontraron las credenciales del usuario.";
					$result["list_cred"] = $list_cred;
					echo json_encode($result);exit();
				}else{
					$usuario = $rr_credenciales[0]["user"];
					$password = $rr_credenciales[0]["password"];
				}

				$cancelTicket = array();
				$cancelTicket = api_e2e_cancelTicket($id_ticket, $id_cliente, $usuario, $password);
				if($cancelTicket == 200){
					$cmd = "SELECT id FROM tbl_televentas_clientes_transaccion  ";
					$cmd .= " WHERE tipo_id='4' AND api_id = 4 AND txn_id='" . $id_ticket . "' ";
					$cmd .= " AND estado = 1 ";
					$list_query = $mysqli->query($cmd);
					$list_transaccion = array();
					while ($li = $list_query->fetch_assoc()) {
						$list_transaccion[] = $li;
					}
					if (count($list_transaccion) == 0) {
						$result["http_code"] = 400;
						$result["status"] = "El ticket no existe.";
					} elseif (count($list_transaccion) === 1) {
						$id_trans = $list_transaccion[0]["id"];
						//$result = rollback_transaccion_bingo($id_cliente, $id_trans, $usuario_id, $turno_id, '26', 4);

						$result_rollback = rollback_transaccion($id_cliente, $id_trans, $usuario_id, $turno_id, '', 0);

						// Consultar SI es un ticket generado con Saldo Promocional, si ya se le quitó la etiqueta, asignar nuevamente.
						if ( (int)$tipo_balance===6 ) {
							$list_balance = obtener_balances($id_cliente);
							if ($list_balance[0]["balance_dinero_at"] > 0){
								$query = "
									SELECT id
									FROM wwwapuestatotal_gestion.tbl_televentas_clientes_etiqueta
									WHERE client_id = $id_cliente
									AND etiqueta_id = 43
									AND status = 1
									ORDER BY id DESC
									LIMIT 1
								";
								$list_query = $mysqli->query($query);
								if ($mysqli->error) {
									$result["consulta_error"] = $mysqli->error;
									$result["http_code"] = 400;
									$result["status"] = "Ocurrió un error al consultar la etiqueta.";
								} else {
									$list= array();
									while ($li = $list_query->fetch_assoc()) {
										$list[] = $li;
									}
									if (count($list)===0){

										$query_2 = "
											SELECT id
											FROM wwwapuestatotal_gestion.tbl_televentas_clientes_etiqueta
											WHERE client_id = $id_cliente
											AND etiqueta_id = 43
											AND status = 3
											ORDER BY id DESC
											LIMIT 1
										";
										$list_query_2 = $mysqli->query($query_2);
										if ($mysqli->error) {
											$result["consulta_error"] = $mysqli->error;
											$result["http_code"] = 400;
											$result["status"] = "Ocurrió un error al consultar la etiqueta.";
										} else {
											$list_2= array();
											while ($li = $list_query_2->fetch_assoc()) {
												$list_2[] = $li;
												$id_etiqueta = $li["id"];
											}

											$update = "
												UPDATE wwwapuestatotal_gestion.tbl_televentas_clientes_etiqueta
												SET status = 1
												WHERE id = $id_etiqueta
											";
											$mysqli->query($update);
										}
									}
								}
							}
						}

						$upd_ctran = "
						UPDATE 
							tbl_televentas_clientes_transaccion
						SET
							estado = 4,
							update_user_id = " . $usuario_id . ",
							updated_at = now()
						WHERE txn_id = '" . $id_ticket . "'";
						$mysqli->query($upd_ctran);

						$upd_tlv_ticket = "
						UPDATE 
							tbl_televentas_tickets
						SET
							status = 4,
							updated_at = now()
						WHERE ticket_id = '" . $id_ticket . "'";
						$mysqli->query($upd_tlv_ticket);
						sec_tlv_asignar_etiqueta_test($id_cliente);
						$result["http_code"] = 200;
						$result["result"] = $cancelTicket;
						$result["amount"] = $monto;
						$result["result_rollback"] = $result_rollback;
					} else {
						$result["http_code"] = 400;
						$result["status"] = "Error al registrar la transacción";
					}
				}else{
					$result["http_code"] = 400;
					$result["status"] = "No se pudo reembolsar el ticket.";
					$result["result"] = $cancelTicket;
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
		$result["status"] = "Sesión perdida.";
	}
}

//*******************************************************************************************************************
// CONSULTAR BINGO COMPRADO
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "sec_tlv_consultar_bingo_comprado") {
	$id_tra = $_POST["trans_id"];
	$usuario_id = $login ? $login['id'] : 0;

	if ((int) $usuario_id > 0) {
		$consulta_cmd = "
		SELECT 
			tra.txn_id ticket_id,
			tra.created_at fecha_compra,
			IFNULL(tra.evento_dineroat_id, 0) id_evento_dinero_at,
		    ifnull(tb.game_id,'') id_jugada,
		    IFNULL(tb.roomName, '') roomName,
		    tb.game_type,
		    tb.startsAt,
		    t.num_selections cant,
		    l.nombre,
		    IFNULL(tb.guestResponseToken, '') guestResponseToken
		FROM tbl_televentas_clientes_transaccion tra
			LEFT JOIN tbl_televentas_tickets_bingo tb ON tra.txn_id = tb.ticket_id
			LEFT JOIN tbl_televentas_tickets t ON tra.txn_id = t.ticket_id AND t.proveedor_id = 4
			LEFT JOIN tbl_locales l ON tra.cc_id = l.cc_id
			LEFT JOIN tbl_televentas_dinero_at_eventos dat_e ON tra.evento_dineroat_id = dat_e.id
		WHERE tra.id = " . $id_tra;

		$list_query = $mysqli->query($consulta_cmd);
		$list_ticket = array();
		while ($li = $list_query->fetch_assoc()) {
			$list_ticket[] = $li;
		}

		if ($mysqli->error) {
			$result["consulta_error"] = $mysqli->error;
		}
		$continue = 0;
		if (count($list_ticket) === 0) {
			$result["http_code"] = 400;
			$result["status"] = "No hay datos de la transacción";
		} elseif (count($list_ticket) > 0) {
			$result["id_evento_dinero_at"] = $list_ticket[0]["id_evento_dinero_at"];
			$continue = 1;
		}

		if($continue == 1){
			$cmd_cards = "
			SELECT 
				tra.txn_id ticket_id,
				c.card_id,
				c.numbers
			FROM tbl_televentas_clientes_transaccion tra
				LEFT JOIN tbl_televentas_tickets_bingo_cards c ON tra.txn_id = c.ticket_id
			WHERE tra.id = " . $id_tra;
			$list_query = $mysqli->query($cmd_cards);
			$list_cards = array();
			while ($li = $list_query->fetch_assoc()) {
				$list_cards[] = $li;
			}

			if ($mysqli->error) {
				$result["consulta_error"] = $mysqli->error;
			}
			if (count($list_cards) === 0) {
				$result["http_code"] = 400;
				$result["status"] = "No hay datos de la transacción";
			} elseif (count($list_cards) > 0) {
				$result["http_code"] = 200;
				$result["list_ticket"] = $list_ticket;
				$result["list_cards"] = $list_cards;
			}
		}else{
			$result["http_code"] = 400;
			$result["status"] = "No hay datos de la transacción";
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Sesión perdida.";
	}
}

//*******************************************************************************************************************
// CONSULTAR BINGO COMPRADO
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "sec_tlv_bingo_historial") {
	$fecha_inicio = $_POST["fecha_inicio"];
	$fecha_fin = $_POST["fecha_fin"];
	//$limit = $_POST["limit"];
	$usuario_id = $login ? $login['id'] : 0;

	if ((int) $usuario_id > 0) {
		$consulta_cmd = "
			SELECT 
				tra.id,
				tb.startsAt,
				tb.roomName,
			    tb.game_type,
			    tb.game_id,
			    t.num_selections,
			    u.usuario,
			    tra.txn_id
			FROM tbl_televentas_clientes_transaccion tra
				INNER JOIN tbl_televentas_tickets_bingo tb ON tra.txn_id = tb.ticket_id
			    LEFT JOIN tbl_televentas_tickets t ON tra.txn_id = t.ticket_id AND t.proveedor_id = 4
			    LEFT JOIN tbl_usuarios u ON tra.user_id = u.id
			    LEFT JOIN tbl_personal_apt apt ON apt.id = u.personal_id
			WHERE 
				tra.tipo_id = 4 
				AND tra.api_id = 4
				AND tra.user_id = " . $usuario_id . "
				AND DATE(tra.created_at) >= '" . $fecha_inicio . "'
				AND DATE(tra.created_at) <= '" . $fecha_fin . "'
			ORDER BY tra.id DESC";

		$list_query = $mysqli->query($consulta_cmd);
		$list_transaction = array();
		while ($li = $list_query->fetch_assoc()) {
			$list_transaction[] = $li;
		}

		if ($mysqli->error) {
			$result["consulta_error"] = $mysqli->error;
		}

		if (count($list_transaction) === 0) {
			$result["http_code"] = 400;
			$result["status"] = "No hay datos";
		}elseif (count($list_transaction) > 0) {
			$result["http_code"] = 200;
			$result["result"] = $list_transaction;
			$result["count_transactions"] = count($list_transaction);
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Sesión perdida.";
	}
}

//*******************************************************************************************************************
// GET INFORME
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "sec_tlv_bingo_get_informe") {
	$fecha_inicio = $_POST["fecha_inicio"];
	$fecha_fin = $_POST["fecha_fin"];
	//$limit = $_POST["limit"];
	$usuario_id = $login ? $login['id'] : 0;

	if ((int) $usuario_id > 0) {
		$consulta_cmd = "
			SELECT
				IFNULL(SUM(IF(tra.tipo_id = 4 AND tra.estado = 1, 1, 0)), 0) AS cartones,
				IFNULL(SUM(IF(tra.tipo_id = 4 AND tra.estado IN (3,4), 1, 0)), 0) AS cartones_devueltos,
				IFNULL(SUM(IF(tra.tipo_id = 4 AND tra.estado = 1, tra.monto, 0)), 0) AS apuestas,
				IFNULL(SUM(IF(tra.tipo_id = 4 AND tra.estado IN (3,4), tra.monto, 0)), 0) AS devuelto,
				IFNULL(SUM(IF(tra.tipo_id = 5 AND tra.estado = 1, tra.monto, 0)), 0) AS pagado
			FROM tbl_televentas_clientes_transaccion tra
			INNER JOIN tbl_televentas_tickets_bingo tb ON tra.txn_id = tb.ticket_id
			WHERE 
				tra.tipo_id IN (4,5) 
				AND tra.api_id = 4
				AND tra.user_id = " . $usuario_id . "
				AND DATE(tra.created_at) >= '" . $fecha_inicio . "'
				AND DATE(tra.created_at) <= '" . $fecha_fin . "'";

		$list_query = $mysqli->query($consulta_cmd);
		$list_transaction = array();
		while ($li = $list_query->fetch_assoc()) {
			$list_transaction[] = $li;
		}

		if ($mysqli->error) {
			$result["consulta_error"] = $mysqli->error;
		}

		if (count($list_transaction) === 0) {
			$result["http_code"] = 400;
			$result["status"] = "No hay datos";
		}elseif (count($list_transaction) > 0) {
			$result["http_code"] = 200;
			$result["result"] = $list_transaction;
			$result["count_transactions"] = count($list_transaction);
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Sesión perdida.";
	}
}


function api_e2e_buyBingoCardsOnRoom($roomId, $gameId, $quantity, $cardPrice, $cardHolderName, $guard_string, $cliente_id, $usuario, $contraseña) {
	$url = env('END2END_BINGO_URL') . 'buyBingoCardsOnRoom';
	$accountIdentifier = env('END2END_BINGO_ACCOUNT_IDENTIFIER');
	$username = $usuario;
	$password = $contraseña;
	$status = 0;
	try {
		$rq = ['accountIdentifier' 	=> $accountIdentifier, 
				'username' 			=> $username, 
				'password' 			=> $password,
				'roomId' 			=> (int)$roomId,
				'gameId' 			=> (int)$gameId,
				'quantity' 			=> (int)$quantity,
				'cardPrice' 		=> $cardPrice,
				'cardHolderName' 	=> substr($cardHolderName,0,24),
				'guard_string' 		=> $guard_string
			];
		$request_headers = array();
		$request_headers[] = "Content-type: application/json";
		$request_json = json_encode($rq);
		$auditoria_id = api_calimaco_auditoria_insert('buyBingoCardsOnRoom', '', $cliente_id, $url . ' ' . $request_json);
		$curl = curl_init($url);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $request_json);
		$response = curl_exec($curl);
		$err = curl_error($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$response_arr = json_decode($response, true);
		curl_close($curl);

		if ($err) {
			$result["http_code"] = $httpcode;
			$result["status"] = "Ocurrió un error al consumir el API.";
			$result["result"] = $response;
			$result["error"] = "cURL Error #:" . $err;
		} else {
			if(!isset($response_arr["gameId"])){
				$result["http_code"] = $httpcode;
			}
			$result["result"] = $response_arr;
			$status = 1;
		}
    } catch (Exception $e) {
		$result["http_code"] = 500;
    	$result["err"] = 'Excepción capturada: '.  $e->getMessage();
    }
    api_calimaco_auditoria_update($auditoria_id, '', $response, $status);
	return $result;
}


function api_e2e_infoByTokenId($ticket_id, $cliente_id){
	global $mysqli;
	$method = 'infoByTokenId';
	$accountIdentifier = env('END2END_BINGO_ACCOUNT_IDENTIFIER');

	$url = env('END2END_BINGO_URL') . $method . '/?';
	$url .= "accountIdentifier=" . $accountIdentifier;
	$url .= "&ticketId=" . $ticket_id;
	$auditoria_id = api_calimaco_auditoria_insert('infoByTokenId', $ticket_id, $cliente_id, $url);
	$status = 0;
    try {
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		));
		$response = curl_exec($curl);
		$err = curl_error($curl);
		$response_arr = json_decode($response, true);
		curl_close($curl);

		if ($err) {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al consumir el API.";
			$result["result"] = $response;
			$result["error"] = "cURL Error #:" . $err;
		} else {
			$ticket = query_tbl_televentas_tickets_get_array();
			$ticket['ticket_id'] = $ticket_id;
			$ticket['proveedor_id']   = '4';
			$ticket['created']   = '';
			$ticket['calc_date'] = '';
			$ticket['paid_date'] = '';
			$ticket['external_id']    = '';
			$ticket['game']           = '';
			$ticket['sell_local_id']  = '';
			$ticket['paid_local_id']  = '';
			$ticket['num_selections'] = 0;
			$ticket['price']          = 0;
			$ticket['stake_amount']   = 0;
			$ticket['winning_amount'] = $response_arr["win_amount"];
			$ticket['jackpot_amount'] = 0;
			$ticket['status']         = $response_arr["ticket_status"];
			query_tbl_televentas_tickets($ticket);
			if($response_arr["gameWinners"] != false && $response_arr["gameWinners"] != '' && $response_arr["win_amount"] > 0){
				foreach($response_arr["gameWinners"] as $gw){
					$won_data = explode(',', $gw["won_data"]);
					foreach($won_data as $w){
						$carton = $w;
						$carton_ganador = preg_replace(array('/-1$/', '/-2$/', '/-4$/', '/-8$/', '/-16$/', '/-32$/'), array('-0', '-1', '-2', '-3', '-4', '-5'), $carton);
						$upd_to_card = "
								UPDATE 
									tbl_televentas_tickets_bingo_cards 
								SET 
									winner = 1,
									updated_at = now()
								WHERE 
									card_id = '" . $carton_ganador . "'
									AND ticket_id = '" . $ticket_id . "'
							";
						$mysqli->query($upd_to_card);	
					}
				}
			}
			$result["result"] = $response_arr;
			$status = 1;
		}
    } catch (Exception $e) {
    	$response = 'Excepción capturada: '.  $e->getMessage();
    }
    api_calimaco_auditoria_update($auditoria_id, $ticket_id, $response, $status);
	return $result;
}

function api_e2e_markTicketAsPaid($ticket_id, $cliente_id, $usuario, $contraseña){
	$method = 'markTicketAsPaid';
	$accountIdentifier = env('END2END_BINGO_ACCOUNT_IDENTIFIER');
	$username = $usuario;
	$password = $contraseña;

	$url = env('END2END_BINGO_URL') . $method . '/?';
	$url .= "accountIdentifier=" . $accountIdentifier;
	$url .= "&guestId=" . $ticket_id;
	$url .= "&username=" . $username;
	$url .= "&password=" . $password;
	$status = 0;
	$auditoria_id = api_calimaco_auditoria_insert('markTicketAsPaid', $ticket_id, $cliente_id, $url);
    try {
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		));
		$response = curl_exec($curl);
		$err = curl_error($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$response_arr = json_decode($response, true);
		curl_close($curl);

		if ($err) {
			$result["http_code"] = $httpcode;
			$result["status"] = "Ocurrió un error al consumir el API.";
			$result["result"] = $response;
			$result["error"] = "cURL Error #:" . $err;
		} else {
			$result = $httpcode;
			$status = 1;
		}
    } catch (Exception $e) {
    	$response = 'Excepción capturada: '.  $e->getMessage();
    }
    api_calimaco_auditoria_update($auditoria_id, $ticket_id, $response . " http_code: " . $httpcode, $status);
	return $result;
}

function api_e2e_refundTicket($ticket_id, $cliente_id, $usuario, $contraseña){
	$method = 'refundTicket';
	$accountIdentifier = env('END2END_BINGO_ACCOUNT_IDENTIFIER');
	$username = $usuario;
	$password = $contraseña;

	$url = env('END2END_BINGO_URL') . $method . '/?';
	$url .= "accountIdentifier=" . $accountIdentifier;
	$url .= "&guestId=" . $ticket_id;
	$url .= "&username=" . $username;
	$url .= "&password=" . $password;
	$status = 0;
	$auditoria_id = api_calimaco_auditoria_insert($method, $ticket_id, $cliente_id, $url);
    try {
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		));
		$response = curl_exec($curl);
		$err = curl_error($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$response_arr = json_decode($response, true);
		curl_close($curl);

		if ($err) {
			$result["http_code"] = $httpcode;
			$result["status"] = "Ocurrió un error al consumir el API.";
			$result["result"] = $response;
			$result["error"] = "cURL Error #:" . $err;
		} else {
			$result = $httpcode;
		}
    } catch (Exception $e) {
    	$response = 'Excepción capturada: '.  $e->getMessage();
    }
    api_calimaco_auditoria_update($auditoria_id, $ticket_id, $response . " http_code: " . $httpcode, $status);
	return $result;
}


function api_e2e_cancelTicket($ticket_id, $cliente_id, $usuario, $contraseña){
	$method = 'cancelTicket';
	$accountIdentifier = env('END2END_BINGO_ACCOUNT_IDENTIFIER');

	$url = env('END2END_BINGO_URL') . $method . '/?';
	$url .= "accountIdentifier=" . $accountIdentifier;
	$url .= "&guestId=" . $ticket_id;
	$url .= "&username=" . $usuario;
	$url .= "&password=" . $contraseña;
	$status = 0;
	$auditoria_id = api_calimaco_auditoria_insert($method, $ticket_id, $cliente_id, $url);
    try {
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'GET',
		));
		$response = curl_exec($curl);
		$err = curl_error($curl);
		$httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		$response_arr = json_decode($response, true);
		curl_close($curl);

		if ($err) {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al consumir el API.";
			$result["result"] = $response;
			$result["error"] = "cURL Error #:" . $err;
		} else {
			$result = $httpcode;
			$status = 1;
		}
    } catch (Exception $e) {
    	$response = 'Excepción capturada: '.  $e->getMessage();
    }
    api_calimaco_auditoria_update($auditoria_id, $ticket_id, $response . " http_code: " . $httpcode, $status);
	return $result;
}

function rollback_transaccion_bingo($cliente_id, $transaccion_id, $usuario_id, $turno_id, $tipo_id, $estado){
	global $mysqli;

	$respuesta = 0;

	$query = "
		SELECT
			cbt.transaccion_id,
			cbt.tipo_balance_id,
			cbt.monto,
			ct.tipo_id,
			IFNULL(ct.api_id,0) api_id,
			IFNULL(ct.txn_id,'')txn_id,
			'" . $tipo_id . "' AS rollback_tipo_id,
			ctt.operacion,
			cb.balance
		FROM
			tbl_televentas_clientes_balance_transaccion cbt
			JOIN tbl_televentas_clientes_transaccion ct ON ct.id = cbt.transaccion_id
			JOIN tbl_televentas_clientes_tipo_transaccion ctt ON ctt.id = ct.tipo_id 
			JOIN tbl_televentas_clientes_balance cb ON cb.tipo_balance_id = cbt.tipo_balance_id and cb.cliente_id = cbt.cliente_id
		WHERE
			cbt.transaccion_id = $transaccion_id 
			AND ct.estado in (0,1)
		ORDER BY ctt.id asc 
		";
	$list_query = $mysqli->query($query);
	$list_transacciones = array();

	$balance_principal=0;
	$rollback_transaccion_id = 0;
	$continue = 0;

	while ($li = $list_query->fetch_assoc()) { 
		$list_transacciones[] = $li;
		if((double)$li['balance']<(double)$li['monto']){
			$result["http_code"] = 400;
			$result["status"] = "El balance es menor al monto a retornar.";
			$result["balance"] = $li['balance'];
			$result["monto"] = $li['monto'];
			$result["tipo_id"] = $li['tipo_id'];
			$continue = 1;
		}
		if( (int)$li['api_id'] === 2 && (int)$li['tipo_id'] === 5 ){
			$result["http_code"] = 400;
			$result["status"] = "No se pueden eliminar apuestas del proveedor Calimaco.";
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
				$balance_principal = (double)$li['balance'] - (double)$li['monto'];
			}
			if($li['operacion']==='debit') { // debit - -> +
				$balance_principal = (double)$li['balance'] + (double)$li['monto'];
			}

			$insert_command = "
				INSERT INTO tbl_televentas_clientes_transaccion (
					tipo_id,
					cliente_id,
					api_id,
					txn_id,
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
					" . $li['api_id'] . ",
					'" . $li['txn_id'] . "',
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
						estado = '" . $estado . "', 
						update_user_id = " . $usuario_id . ", 
						observacion_supervisor = '', 
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

				if((double)$tra['balance']<(double)$tra['monto']){
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
						$temp_balance_nuevo = (double)$tra['balance'] - (double)$tra['monto'];
					}
					if($tra['operacion']==='debit') { // debit - -> +
						$temp_balance_nuevo = (double)$tra['balance'] + (double)$tra['monto'];
					}

					query_tbl_televentas_clientes_balance('update', $cliente_id, $temp_tipo_balance_id, $temp_balance_nuevo);

					query_tbl_televentas_clientes_balance_transaccion('insert', $rollback_transaccion_id, 
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

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_tlv_get_credenciales_bingo") {
	$cliente_id = $_POST["cliente_id"];
	$usuario_id = $login ? $login['id'] : 0;

	if ((int) $usuario_id > 0) {
		$consulta_cmd = "
			SELECT 
				ub.id,
				ub.user,
				ub.password
			FROM tbl_televentas_usuarios_bingo ub
			WHERE 
				ub.usuario_id = " . $usuario_id . "
				AND ub.status = 1
			";

		$list_query = $mysqli->query($consulta_cmd);
		$list_registers = array();
		while ($li = $list_query->fetch_assoc()) {
			$list_registers[] = $li;
		}

		if ($mysqli->error) {
			$result["consulta_error"] = $mysqli->error;
		}

		if (count($list_registers) === 0) {
			$result["http_code"] = 400;
			$result["status"] = "No hay credenciales de este usuario";
		}elseif (count($list_registers) > 0) {
			$result["http_code"] = 200;
			$result["result"] = $list_registers;
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Sesión perdida.";
	}
}

if ($_POST["accion"] === "sec_tlv_set_credenciales_bingo") {
	include("function_replace_invalid_caracters.php");
	$user = $_POST["user"];
	$pass = $_POST["pass"];
	$usuario_id = $login ? $login['id'] : 0;

	$upd_cmd = " 
		UPDATE 
			tbl_televentas_usuarios_bingo
		SET 
			status = 0
		WHERE
			usuario_id = " . $usuario_id;
	$mysqli->query($upd_cmd);

	$consulta_cmd = "
		SELECT 
			ub.id,
			ub.user,
			ub.password
		FROM tbl_televentas_usuarios_bingo ub
		WHERE 
			ub.usuario_id = " . $usuario_id . "
		ORDER BY ub.id DESC
		LIMIT 1
		";

	$list_query = $mysqli->query($consulta_cmd);
	$list_registers = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_registers[] = $li;
	}

	if(count($list_registers) > 0){
		$id_user = $list_registers[0]["id"];
		$upd_user_cmd = " 
			UPDATE 
				tbl_televentas_usuarios_bingo
			SET 
				user = '" . $user . "',
				password = '" . $pass . "',
				status = 1,
				updated_at = now()
			WHERE
				usuario_id = " . $usuario_id
				. " AND id = " . $id_user;
		$mysqli->query($upd_user_cmd);
		$error = '';
	}else{
		$insert_command = " 
			INSERT INTO tbl_televentas_usuarios_bingo (
				usuario_id,
				user,
				password,
				status,
				created_at
			) VALUES (
				'". $usuario_id ."',
				'". $user ."',
				'". $pass ."',
				1,
				now()
			) 
			";
		$mysqli->query($insert_command);
		$error = '';
	}

	
	if ($mysqli->error) {
		$result["insert_error"] = $mysqli->error;
		$error = $mysqli->error;
	}
	if ($error === '') {
		$query_verifica = "SELECT id, user, password FROM tbl_televentas_usuarios_bingo  ";
		$query_verifica .= " WHERE usuario_id = " . $usuario_id;
		$query_verifica .= " AND status = 1";
		$list_query = $mysqli->query($query_verifica);
		$list_transaccion_verifica = array();
		if ($mysqli->error) {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al consultar la transacción.";	
			$result["query_verifica"] = $query_verifica;
			$result["query_error"] = $mysqli->error;
		}else{
			while ($li = $list_query->fetch_assoc()) {
				$list_transaccion_verifica[] = $li;
			}
			if (count($list_transaccion_verifica) === 0) {
				$result["http_code"] = 400;
				$result["status"] = "No se guardó la transacción.";								
			} elseif (count($list_transaccion_verifica) === 1) {
				$result["http_code"] = 200;
				$result["result"] = $list_transaccion_verifica;
			} elseif (count($list_transaccion_verifica) > 1) {
				$result["http_code"] = 400;
				$result["status"] = "Se duplicaron las transacciones, por favor informar a informática.";
			} else {
				$result["http_code"] = 400;
				$result["status"] = "Ocurrió un error al guardar la transacción.";	
			}
		}
	}else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al guardar la validación.";
	}
}

function sec_tlv_get_credential_bingo_user(){
	global $mysqli;
	global $login;
	$usuario_id = $login ? $login['id'] : 0;
	$cmd_cred = "SELECT id, user, password 
				FROM tbl_televentas_usuarios_bingo 
				WHERE usuario_id = " . $usuario_id . " 
				AND status = 1 LIMIT 1";
	$list_cmd_cred = $mysqli->query($cmd_cred);

	$list_cred = array();
	while ($li = $list_cmd_cred->fetch_assoc()) {
		$list_cred[] = $li;
	}
	if(count($list_cred) == 0){
		$result["http_code"] = 400;
		$result["status"] = "No se encontraron las credenciales del usuario.";
		$result["list_cred"] = $list_cred;
	}else{
		$usuario = $list_cred[0]["user"];
		$password = $list_cred[0]["password"];
	}
	return $list_cred;
}

if ($_POST["accion"] === "obtener_televentas_cuentas_deposito") {
	include("function_replace_invalid_caracters.php");

	$consulta_cmd = "
		SELECT
			ca.id,
			ca.cuenta_descripcion,
			tc.bono,
			ifnull( tc.comision_monto, 0 ) comision_monto,
			IFNULL(ca.foreground, 'black') foreground,
			IFNULL(ca.background, '') background,
            IFNULL(tc.valid_cuenta_yape, 0) valid_cuenta_yape
		FROM
			tbl_televentas_cuentas tc
			JOIN tbl_cuentas_apt ca ON ca.id = tc.cuenta_apt_id 
		WHERE
			tc.status = 1 
			AND IFNULL(tc.valid_caja7, 0) = 0
			AND ca.id not in (12)
		ORDER BY ca.orden ASC
		";

	$list_query = $mysqli->query($consulta_cmd);
	$list_registers = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_registers[] = $li;
	}
	$error = '';
	if ($mysqli->error) {
		$result["insert_error"] = $mysqli->error;
		$error = $mysqli->error;
	}
	if ($error === '') {
		$result["http_code"] = 200;
		$result["result"] = $list_registers;
	}else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al obtener las cuentas.";
	}
}

if ($_POST["accion"] === "obtener_televentas_tipo_constancias") {
	include("function_replace_invalid_caracters.php");

	$consulta_cmd = "SELECT id, descripcion FROM tbl_televentas_tipo_constancia  
	WHERE estado = 1 AND tipo_cuenta = 2 or id = 5
	ORDER BY id ASC";

	$list_query = $mysqli->query($consulta_cmd);
	$list_registers = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_registers[] = $li;
	}
	$error = '';
	if ($mysqli->error) {
		$result["insert_error"] = $mysqli->error;
		$error = $mysqli->error;
	}
	if ($error === '') {
		$result["http_code"] = 200;
		$result["result"] = $list_registers;
	}else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al obtener los tipos.";
	}
}

if ($_POST["accion"] === "sec_tlv_get_info_autoexclusion_by_webid") {
	$web_id = $_POST["web_id"];
	$rr_get_autoexclusion_x_webid = array();
	$rr_get_autoexclusion_x_webid = api_calimaco_get_Autoexclusion_x_webId($web_id);
	$result["result"] = $rr_get_autoexclusion_x_webid;
}

function api_calimaco_get_Autoexclusion_x_webId($web_id){
	$result = [];
    try {
		$url = "https://api.apuestatotal.com/v2/teleservicios/calimaco/getAutoexclusion_x_webId";
		$rq = ['webId' => $web_id];
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
		$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
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

function sec_tlv_get_array_locales_test(){
	global $mysqli;
	global $login;
	$cmd_valid_exists = "
		SELECT 
			l.id
		FROM tbl_locales l
		WHERE 
			IFNULL(is_test, 0) = 1
		";
	$list_exists = $mysqli->query($cmd_valid_exists);
	$rr_locales_test = array();
	while ($li_3 = $list_exists->fetch_assoc()) {
		$rr_locales_test[] = $li_3["id"];
	}
	return $rr_locales_test;

	/*$rr_locales_test = array();
	$rr_locales_test[] = "201";
	$rr_locales_test[] = "1533";
	$rr_locales_test[] = "200";
	$rr_locales_test[] = "1471";
	$rr_locales_test[] = "1571";
	//$rr_locales_test[] = "481"; //Activaciones prueba local
	return $rr_locales_test;*/
}

function sec_tlv_asignar_etiqueta_test($cliente_id){
	global $mysqli;
	global $login;
	$turno = get_turno();
	if (count($turno) > 0) {
		$local_id = $turno[0]["local_id"];
	}else{
		$local_id = 0;
	}
	$array_locales_test = sec_tlv_get_array_locales_test();
	if(in_array($local_id, $array_locales_test)){
		$usuario_id = $login ? $login['id'] : 0;
		$area_id = $login ? $login['area_id'] : 0;
		if($area_id != 6){
			$id_etiqueta_test = 44; // saldo no retirable para clientes consultados en locales de test
			$cmd_valid_exists = "
				SELECT 
					ce.id
				FROM tbl_televentas_clientes_etiqueta ce
				WHERE 
					ce.client_id = '" . $cliente_id . "' 
					AND ce.etiqueta_id = '" . $id_etiqueta_test . "' 
					AND ce.status = '1' 
				";
			$list_exists = $mysqli->query($cmd_valid_exists);
			$list_etiqueta_existente = array();
			while ($li_3 = $list_exists->fetch_assoc()) {
				$list_etiqueta_existente[] = $li_3;
			}
			if(count($list_etiqueta_existente) == 0){
				$insert_2 = " 
					INSERT INTO tbl_televentas_clientes_etiqueta
						(
						`client_id`,
						`etiqueta_id`,
						`status`,
						`created_user_id`,
						`created_at`
						) VALUES (
						'" . $cliente_id . "',
						'" . $id_etiqueta_test . "',
						'1',
						'" . $usuario_id . "',
						now()
						)";
				$mysqli->query($insert_2);
				if ($mysqli->error) {
					$result["insert_error"] = $mysqli->error;
				}

				$cmd_consulta_valid = "
					SELECT 
						ce.id
					FROM tbl_televentas_clientes_etiqueta ce
					WHERE 
						ce.client_id = '" . $cliente_id . "' 
						AND ce.etiqueta_id = '" . $id_etiqueta_test . "' 
						AND ce.status = '1' 
					";
				$list_query = $mysqli->query($cmd_consulta_valid);
				$list_etiqueta_insertada = array();
				while ($li_3 = $list_query->fetch_assoc()) {
					$list_etiqueta_insertada[] = $li_3;
				}
				if ($mysqli->error) {
					$result["query_3_error"] = $mysqli->error;
				}
				if (count($list_etiqueta_insertada) == 1) {
					$result["http_code"] = 200;
					$result["status"] = "ok";
				} else {
					$result["http_code"] = 400;
					$result["status"] = "Ocurrió un error al registrar la etiqueta test al cliente.";
				}
			}
		}
	}
}

if ($_POST["accion"] === "guardar_imagen_perfil_cliente") {
	include "function_replace_invalid_caracters.php";

	$cliente_id         = $_POST["cliente_id"];
	$tipo_operacion_id  = trim($_POST["tipo_operacion_id"]);
	$imagen_id          = $_POST["imagen_id"];
	$imagen_nombre      = replace_invalid_caracters(trim($_POST["imagen_nombre"]));
	$actualizar_archivo = "";
	$actualizar_mensaje = 'Se actualizo el nombre de la imagen a: ' . $imagen_nombre;

	$path       = "/var/www/html/files_bucket/images_clientes_teleservicio/";
	$file       = [];
	$imageLayer = [];
	if (!is_dir($path)) {
		mkdir($path, 0777, true);
	}

	$imageProcess = 0;

	if (isset($_FILES['imagen_del_cliente']['name'])) {
		$filename    = $_FILES['imagen_del_cliente']['tmp_name'];
		$filenametem = $_FILES['imagen_del_cliente']['name'];
		$ext         = pathinfo($filenametem, PATHINFO_EXTENSION);
		if ($filename != "") {
			$fileExt        = pathinfo($_FILES['imagen_del_cliente']['name'], PATHINFO_EXTENSION);
			$resizeFileName = $cliente_id . "_" . date('YmdHis') . "_" . str_replace(' ', '-', $imagen_nombre);
			$nombre_archivo = $resizeFileName . "." . $fileExt;

			$sourceProperties  = getimagesize($filename);
			$size              = $_FILES['imagen_del_cliente']['size'];
			$uploadImageType   = $sourceProperties[2];
			$sourceImageWith   = $sourceProperties[0];
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

			$imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);

			$file[0] = imagegif($imageLayer[0], $path . $nombre_archivo);
			$file[1] = imagegif($imageLayer[1], $path . "min_" . $nombre_archivo);

			move_uploaded_file($file[0], $path . $nombre_archivo);
			move_uploaded_file($file[1], $path . $nombre_archivo);

			if ($tipo_operacion_id === 'new') {
				$fecha_actual   = date('Y-m-d H:i:s');
				$insert_command = "INSERT INTO tbl_televentas_clientes_imagenes(
					cliente_id,
					nombre,
					archivo,
					status,
					user_created_id,
					created_at
				) VALUES (
					$cliente_id,
					'$imagen_nombre',
					'$nombre_archivo',
					1,
					$usuario_id,
					'$fecha_actual'
				)";
				$mysqli->query($insert_command);

				if ($mysqli->error) {
					$result["http_code"]      = 400;
					$result["status"]         = "Ocurrió un error al guardar la imagen en la base de datos.";
					$result["insert_command"] = $insert_command;
					$result["insert_error"]   = $mysqli->error;
				} else {
					$result["http_code"] = 200;
					$result["status"]    = "La imagen ha sido guardada exitosamente";
				}
			}
			$actualizar_archivo = " archivo = '$nombre_archivo',";
			$actualizar_mensaje = 'Se actualizo la imagen';
		} else {
			if ($tipo_operacion_id === 'new') {
				$result["http_code"] = 400;
				$result["status"]    = "Ocurrió un error al guardar la imagen. No se puede reconocer la imagen ";
			}
		}
	}

	if ($tipo_operacion_id === 'edit') {
		$fecha_actual = date('Y-m-d H:i:s');

		$sql = "UPDATE tbl_televentas_clientes_imagenes
		SET $actualizar_archivo
		nombre = '$imagen_nombre',
		user_updated_id = $usuario_id,
		updated_at = '$fecha_actual'
		WHERE
		status = 1
		AND cliente_id = $cliente_id
		AND id = $imagen_id
		";

		$query = $mysqli->query($sql);

		if ($mysqli->error) {
			$result["http_code"]      = 400;
			$result["status"]         = 'Se produjo el siguiente error al actualizar la imagen del perfil del cliente';
			$result["error"]          = 'Error: ' . $mysqli->error;
			$result["consulta_error"] = $sql;
		} else {
			$result["http_code"] = 200;
			$result["status"]    = $actualizar_mensaje;
		}
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "listar_imagen_perfil_cliente") {

	$cliente_id          = $_POST["cliente_id"];
	$nombre_imagen       = trim($_POST["nombre_imagen"]);
	$where_nombre_imagen = '';

	if (strlen($nombre_imagen) > 0) {
		$where_nombre_imagen = " AND nombre LIKE '%$nombre_imagen%'";
	}

	$sql = "SELECT
	id,
	nombre,
	archivo
	FROM tbl_televentas_clientes_imagenes
	WHERE status = 1
	AND cliente_id = $cliente_id
	$where_nombre_imagen
	ORDER BY id DESC";

	$query = $mysqli->query($sql);

	if ($mysqli->error) {
		$result["http_code"]      = 400;
		$result["status"]         = 'Se produjo el siguiente error al listar las imagenes el cliente';
		$result["error"]          = 'Error: ' . $mysqli->error;
		$result["consulta_error"] = $sql;
	} else {
		$html      = '';
		$row_count = $query->num_rows;
		if ($row_count == 0) {
			$mensaje_nombre_imagen_buscada = '';
			if (strlen($nombre_imagen) > 0) {
				$where_nombre_imagen = " AND nombre LIKE '%$nombre_imagen%'";
				$mensaje_nombre_imagen_buscada = ' con el título <b>' . $nombre_imagen . '</b>';
			}
			$html .= '<tr class="text-center">';
			$html .= '<td colspan=2>No se encontraron imagenes del cliente en la base de datos' . $mensaje_nombre_imagen_buscada . '</td>';
			$html .= '</tr>';
		} else if ($row_count > 0) {
			while ($row = $query->fetch_assoc()) {
				$html .= '<tr>';
				$html .= '<td>' . $row["nombre"] . '</td>';
				$html .= '<td>';

				$html .= '<a onclick="sec_tlv_perfil_cliente_eliminar_imagen(' . $cliente_id . ',' . $row["id"] . ');" class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar imagen" style="margin-right: 4px;">';
				$html .= '<span class="fa fa-trash-o"></span>';
				$html .= '</a>';

				$html .= '<a onclick="sec_tlv_modal_perfil_cliente_agregar_imagen(\'edit\',' . $row["id"] . ',\'' . $row["nombre"] . '\');"  class="btn btn-warning btn-xs" data-toggle="tooltip" data-placement="top" title="Editar imagen" style="margin-right: 4px;">';
				$html .= '<span class="fa fa-edit"></span>';
				$html .= '</a>';

				$html .= '<a class="btn btn-primary btn-xs" data-toggle="tooltip" data-placement="top" title="Ver imagen" onclick="document.getElementById(\'tlv_img_client_' . $row["id"] . '\').click();">';
				$html .= '<span class="fa fa-eye" style="position: absolute; margin: 3px 0px 0px -1px;"></span>';
				$html .= '<img id="tlv_img_client_' . $row["id"] . '"  data-original="files_bucket/images_clientes_teleservicio/' . $row["archivo"] . '" src="files_bucket/images_clientes_teleservicio/min_' . $row["archivo"] . '" alt="' . $row["nombre"] . '" width="12" height="16" style="opacity: 0;">';
				$html .= '</a>';

				$html .= '</td>';
				$html .= '</tr>';
			}
		}
		$result["http_code"] = 200;
		$result["status"]    = "Datos obtenidos de gestion.";
		$result["result"]    = $html;
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "eliminar_imagen_perfil_cliente") {

	$cliente_id   = $_POST["cliente_id"];
	$imagen_id    = $_POST["imagen_id"];
	$fecha_actual = "'" . date('Y-m-d H:i:s') . "'";

	$sql = "UPDATE tbl_televentas_clientes_imagenes
	SET status = 0,
	user_deleted_id = $usuario_id,
	deleted_at = $fecha_actual
	WHERE
	status = 1
	AND cliente_id = $cliente_id
	AND id = $imagen_id
	";

	$query = $mysqli->query($sql);

	if ($mysqli->error) {
		$result["http_code"]      = 400;
		$result["status"]         = 'Se produjo el siguiente error al eliminar la imagen del perfil del cliente';
		$result["error"]          = 'Error: ' . $mysqli->error;
		$result["consulta_error"] = $sql;
	} else {
		$result["http_code"] = 200;
		$result["status"]    = "Datos obtenidos de gestion.";
	}
}

//*******************************************************************************************************************
// OBTENER TRANSACCIONES YAPE PENDIENTES 
//*******************************************************************************************************************
if ($_POST["accion"] === "sec_tlv_obtener_yapes_pendientes") {
	try {
		$cliente_nombre = $_POST["nombre"];
		$cliente_apepa  = $_POST["apepa"];
		$cliente_apema  = $_POST["apema"];
		$monto          = $_POST["monto"];
		$fecha          = str_replace('T', ' ', $_POST["fecha"]);
		$banco          = $_POST["banco"];
		$nombre_t       = $_POST["nombre_t"];

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

		$nombre_completo = $cliente_nombre . ' ' . 
							(strlen($cliente_apepa) > 0 ? ' ' . $cliente_apepa : '') . 
							(strlen($cliente_apema) > 0 ? ' ' . $cliente_apema : '');

		$nombre_completo_2 =  (strlen($cliente_apepa) > 0 ? '' . $cliente_apepa : '') . 
							(strlen($cliente_apema) > 0 ? ' ' . $cliente_apema : '') .
							' ' . $cliente_nombre;

		$where = " AND t.amount = '" . $monto . "' ";
		if($nombre_t == ""){
			$where .= 
				"AND
					(
						t.person LIKE '%" . $nombre_completo . "%' 
						OR t.person LIKE '%" . $nombre_completo_2 . "%'
			            OR t.person LIKE '%" . $cliente_nombre . ' ' . $cliente_apepa . "%'
			        ) " ;
		}else{
			$where .= 
				"AND
					(
						t.person LIKE '%" . $nombre_t . "%'
			        ) " ;
		}

		$query = "
			SELECT 
				t.id,
				IFNULL(t.description, '') yape,
				t.created_at registrado,
				t.person persona,
				t.amount monto
			FROM `at-yape`.transactions t
			WHERE 
				t.state = 'pending' 
			    AND t.created_at >=  date_add('" . $fecha . ":00', INTERVAL " . sec_tlv_get_value_parameter('minutes_yapes_pending_before') . " MINUTE) 
			    AND t.created_at <= date_add('" . $fecha . ":59', INTERVAL " . sec_tlv_get_value_parameter('minutes_yapes_pending_after') . " MINUTE) 
			    AND UPPER('" . $banco . "') LIKE CONCAT('%', UPPER(description), '%') "
			. $where;
		$result["consulta_query"] = $query;
		$list_query = $mysqli2->query($query);
		$list_transaccion = array();
		while ($li = $list_query->fetch_assoc()) {
			$list_transaccion[] = $li;
		}

		if ($mysqli2->error) {
			$result["consulta_error"] = $mysqli2->error;
		}

		if (count($list_transaccion) == 0) {
			$result["http_code"] = 400;
			//$result["status"] = "No hay yapes correspondientes a este cliente con el monto y fecha ingresados.";
			$result["status"] = "Depósito no encontrado.";
		} elseif (count($list_transaccion) > 0) {
			$result["http_code"] = 200;
			$result["status"] = "ok";
			$result["result"] = $list_transaccion;
		} else {
			$result["http_code"] = 404;
			$result["status"] = "Ocurrió un error al consultar los yapes";
		}
	} catch (Exception $e) {
		$result["http_code"] = 404;
		$result["status"] = 'Excepción capturada: '.  $e->getMessage();
    }
}

if (isset($_POST["accion"]) && $_POST["accion"] === "listar_comprobantes_de_pago_sin_notificar") {

	$administrar_vouchers_sin_enviar  = $_POST["administrar_vouchers_sin_enviar"];
	$rango_dias_consultar_voucher_sin_envio = $_POST["rango_dias_consultar_voucher_sin_envio"];
	$cargo_id = $_POST["cargo_id"];

	$query_promotores_activos     = "";
	$query_transacciones_asignada = "";

	if ($administrar_vouchers_sin_enviar == 1) {
		$array_promotores_en_linea    = array();
		$array_promotores_en_linea_id = array();

		$query_perfil = " p.area_id = 31 AND p.cargo_id = 5 ";

		if ($_SERVER['SERVER_NAME'] === 'localhost') {
			$query_perfil = " p.area_id = 6 ";
		}

		$sql_promotores_en_linea = "SELECT
		u.id,
		concat(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS nombre
		FROM tbl_personal_apt p
		INNER JOIN tbl_usuarios u ON p.id = u.personal_id
		INNER JOIN tbl_login l ON u.id = l.usuario_id
		INNER JOIN tbl_caja c ON u.id = c.usuario_id
		WHERE
		$query_perfil
		AND p.estado = 1
		AND u.estado = 1
		AND l.logout_datetime IS NULL
		AND l.expire_datetime > now()
		AND c.fecha_operacion >= DATE_SUB(CURDATE(), INTERVAL 1 DAY)
		AND c.estado = 0
		ORDER BY p.nombre ASC";

		$query_promotores_en_linea = $mysqli->query($sql_promotores_en_linea);

		if ($mysqli->error) {
			$result["http_code"]      = 400;
			$result["status"]         = 'Se produjo el siguiente error al listar los promotores en linea';
			$result["error"]          = 'Error: ' . $mysqli->error;
			$result["consulta_error"] = $sql_promotores_en_linea;
		} else {
			$row_count = $query_promotores_en_linea->num_rows;
			if ($row_count > 0) {
				while ($row = $query_promotores_en_linea->fetch_assoc()) {
					$array_promotores_en_linea[]    = $row;
					$array_promotores_en_linea_id[] = $row["id"];
				}
			}
		}

		if (count($array_promotores_en_linea_id) > 0) {
			$query_promotores_activos = "AND cts.user_id NOT IN (" . implode(",", $array_promotores_en_linea_id) . ")";
		}
	}

	$query_transacciones_asignadas_x_usuario = "";

	if ($administrar_vouchers_sin_enviar != 1) {
		$query_transacciones_asignadas_x_usuario = " AND at.usuario_id = " . $usuario_id;
	}

	$array_transacciones_asignadas = array();
	$array_transacciones_asignadas_ids = array();

	$sql = "SELECT 
	at.transaccion_id, 
	at.usuario_id, 
	IFNULL(ua.usuario, '') AS asignado_a,
	IFNULL(uc.usuario, '') AS creador_por,
	IFNULL(uu.usuario, '') AS actualizado_por,
	at.created_at, 
	at.updated_at
	FROM tbl_televentas_asignaciones_temporales at
	INNER JOIN tbl_televentas_clientes_transaccion tra ON at.transaccion_id = tra.id
	LEFT JOIN tbl_usuarios uc ON uc.id = at.user_created_id
	LEFT JOIN tbl_usuarios uu ON uu.id = at.user_updated_id
	LEFT JOIN tbl_usuarios ua ON ua.id = at.usuario_id
	WHERE (tra.tipo_id = 11 AND tra.estado IN (2))
	AND (tra.enviar_comprobante != 1 OR tra.enviar_comprobante IS NULL)
	AND at.status = 1
	$query_transacciones_asignadas_x_usuario
	";

	$query = $mysqli->query($sql);

	if ($mysqli->error) {
		$result["http_code"]      = 400;
		$result["status"]         = 'Se produjo el siguiente error al listar los promotores en linea';
		$result["error"]          = 'Error: ' . $mysqli->error;
		$result["consulta_error"] = $sql;
	} else {
		$row_count = $query->num_rows;
		if ($row_count == 0 && $administrar_vouchers_sin_enviar != 1) {
			$html = '<tr class="text-center">';
			$html .= '<td colspan=8>No se encontraron comprobantes de pago sin notificar en la base de datos</td>';
			$html .= '<td style="display: none;"></td>';
			$html .= '<td style="display: none;"></td>';
			$html .= '<td style="display: none;"></td>';
			$html .= '<td style="display: none;"></td>';
			$html .= '<td style="display: none;"></td>';
			$html .= '<td style="display: none;"></td>';
			$html .= '<td style="display: none;"></td>';
			$html .= '</tr>';
			$result["http_code"] = 200;
			$result["status"]    = "Datos obtenidos de gestion.";
			$result["result"]    = $html;
			echo json_encode($result);
			exit();
		} else if ($row_count > 0) {
			while ($row = $query->fetch_assoc()) {
				$array_transacciones_asignadas[] = $row;
				$array_transacciones_asignadas_ids[] = $row["transaccion_id"];
			}
		}
	}

	if ($administrar_vouchers_sin_enviar != 1 && count($array_transacciones_asignadas_ids) > 0) {
		$query_transacciones_asignada = " AND tra.id IN (" . implode(",", $array_transacciones_asignadas_ids) . ") ";
	}

	$sql = "SELECT
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
		IF(tra.id_tipo_balance = 6, 'Promocional', 'Real') saldo,
		tra.cliente_id,
		tc.tipo_doc,
		tc.num_doc,
		tc.telefono,
		IFNULL(CONCAT(IFNULL(tc.nombre, ''),' ', IFNULL(tc.apellido_paterno, ''), ' ', IFNULL(tc.apellido_materno, '')), '') AS cliente,
		usu_2.usuario AS usuario_solic,
		IFNULL(CONCAT( per_2.nombre, ' ', IFNULL( per_2.apellido_paterno, '' ), ' ', IFNULL( per_2.apellido_materno, '' ) ), '') AS cajero_solic,
		tra_2.created_at AS fecha_solicitud
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
		LEFT JOIN tbl_televentas_clientes tc ON tra.cliente_id = tc.id
		LEFT JOIN tbl_televentas_clientes_transaccion cts ON tra.transaccion_id = cts.id
		LEFT JOIN tbl_televentas_clientes_transaccion tra_2 ON tra_2.id = tra.transaccion_id AND tra_2.tipo_id = 9
        LEFT JOIN tbl_usuarios usu_2 ON usu_2.id = tra_2.user_id
		LEFT JOIN tbl_personal_apt per_2 ON per_2.id = usu_2.personal_id
	WHERE
		(tra.tipo_id = 11 AND tra.estado IN (2))
		AND tra.created_at >= date_add(now(),interval -$rango_dias_consultar_voucher_sin_envio day)
		AND tra.created_at <= now()
		AND (tra.enviar_comprobante != 1 OR tra.enviar_comprobante IS NULL)
		$query_promotores_activos
		$query_transacciones_asignada
	ORDER BY
		tra.created_at ASC, tra.nuevo_balance ASC";

	$query = $mysqli->query($sql);

	if ($mysqli->error) {
		$result["http_code"]      = 400;
		$result["status"]         = 'Se produjo el siguiente error al listar los comprobantes de pagos sin notificar';
		$result["error"]          = 'Error: ' . $mysqli->error;
		$result["consulta_error"] = $sql;
	} else {
		$html      = '';
		$row_count = $query->num_rows;
		if ($row_count == 0) {
			$html .= '<tr class="text-center">';
			$html .= '<td colspan=9>No se encontraron comprobantes de pago sin notificar en la base de datos</td>';
			$html .= '</tr>';
		} else if ($row_count > 0) {
			while ($row = $query->fetch_assoc()) {
				$gen_cliente_nombres = $row["cliente_id"];
				$obs_val             = preg_replace("/(\r\n|\n|\r)/", " ", $row["observacion_validador"]);
				$obs_sup             = preg_replace("/(\r\n|\n|\r)/", " ", $row["observacion_supervisor"]);

				$variables = "'" . $row["trans_id"] . "','" . $row["txn_id"] . "','" . $row["tipo_id"] . "','" . $row["web_id"] . "','" .
					$gen_cliente_nombres . "','" .
					$row["monto_deposito"] . "','" . $row["comision_monto"] . "','" . $row["monto"] . "','" . $row["bono_nombre"] . "','" .
					$row["bono_monto"] . "','" . $row["total_recarga"] . "','" .
					$row["fecha_creacion"] . "','" . $row["estado"] . "','" . $row["local"] . "','" . $row["observacion_cajero"] . "','" .
					$obs_val . "','" . $obs_sup . "','" .
					$row["tipo_rechazo"] . "','" . $row["registro_deposito"] . "','" . $row["num_operacion"] . "','" .
					$row["banco_pago"] . "','" . $row["proveedor_id"] . "','" . $row["proveedor_name"] . "','" . $row["usuario"] . "','" .
					$row["cajero"] . "','" . $row["usuario_solic"] . "','" .
					$row["cajero_solic"] . "','" . $row["tipo_operacion"] . "','" . $row["motivo_dev"] . "', '', ''";

				$html .= '<tr>';
				$html .= '<td>' . substr($row["fecha_solicitud"],0,16). '</td>';
				$html .= '<td>' . substr($row["fecha_creacion"],0,16). '</td>';
				$html .= '<td class="text-right">' . number_format($row["monto"], 2, '.', ',') . '</td>';

				$html .= '<td>';

				if (trim($row["num_doc"]) != "") {
					$html .= '<a onclick="navigator.clipboard.writeText(\'' . $row["num_doc"] . '\');" class="btn btn-info btn-xs" data-toggle="tooltip" data-placement="top" title="Copiar número de documento" style="margin-right: 4px;">';
					$html .= '<span class="fa fa-copy"></span> ';
					$html .= '</a>' . $row["num_doc"];
				}

				$html .= '</td>';

				$html .= '<td>';

				if (trim($row["cliente"]) != "") {
					$html .= '<a onclick="sec_tlv_ver_cliente_comprobante_de_pago_sin_notificar(\'' . $row["tipo_doc"] . '\',\'' . $row["num_doc"] . '\');" class="btn btn-success btn-xs" data-toggle="tooltip" data-placement="top" title="Ver cliente" style="margin-right: 4px;">';
					$html .= '<span class="fa fa-eye"></span> ';
					$html .= '</a>';
					$html .= '<a onclick="navigator.clipboard.writeText(\'' . $row["cliente"] . '\');" class="btn btn-info btn-xs" data-toggle="tooltip" data-placement="top" title="Copiar nombre del cliente" style="margin-right: 4px;">';
					$html .= '<span class="fa fa-copy"></span>';
					$html .= '</a>' . $row["cliente"];
					$html .= '</td>';
				}

				$html .= '<td>';

				if (trim($row["telefono"]) != "") {
					$html .= '<a onclick="navigator.clipboard.writeText(\'' . $row["telefono"] . '\');" class="btn btn-info btn-xs" data-toggle="tooltip" data-placement="top" title="Copiar teléfono" style="margin-right: 4px;">';
					$html .= '<span class="fa fa-copy"></span> ';
					$html .= '</a>' . $row["telefono"];
				}

				$html .= '</td>';

				$html .= '<td>';

				if (trim($row["cajero_solic"]) != "") {
					$html .= $row["cajero_solic"];
				}

				$html .= '</td>';

				$html .= '<td>';

				$html .= '<a onclick="ver_voucher(' . $variables . ');" class="btn btn-success btn-xs" data-toggle="tooltip" data-placement="top" title="Ver comprobante de pago" style="margin-right: 4px;">';
				$html .= '<span class="fa fa-eye"></span> Ver';
				$html .= '</a>';

				$html .= '</td>';

				$usuario_id_encontrado = "";
				$asignado_el = "";
				$asignado_por = "";
				$asignado_a = "";

				foreach ($array_transacciones_asignadas as $transaccion) {
					if ($transaccion['transaccion_id'] == $row["trans_id"]) {
						$usuario_id_encontrado = $transaccion['usuario_id'];
						$asignado_el = trim($transaccion['updated_at']) != "" ? trim($transaccion['updated_at'])  : trim($transaccion['created_at']);
						$asignado_por = trim($transaccion['actualizado_por']) != "" ? trim($transaccion['actualizado_por'])  : trim($transaccion['creador_por']);
						$asignado_a = trim($transaccion['asignado_a']);
						break;
					}
				}

				if ($administrar_vouchers_sin_enviar == 1) {
					$html .= '<td style="display:flex;">';
					$html .= '<select class="form-control sec_tlv_select2_promotores_asignar" id="trans_' . $row["trans_id"] . '">';
					$html .= '<option value="">-- Asignar a --</option>';

					foreach ($array_promotores_en_linea as $promotores) {
						$html .= '<option value="' . $promotores['id'] . '">' . $promotores['nombre'] . '</option>';
					}
					
					$html .= '</select>';
					$html .= '<a id="trans_btn_' . $row["trans_id"] . '" onclick="sec_tlv_asignar_transaccion_a_usuario(' . $row["trans_id"] . ');" class="btn btn-primary btn-xs" data-toggle="tooltip" data-placement="top" title="Asignar comprobante de pago" style="margin-left: 5px;">';
					$html .= '<span class="fa fa-save" style="padding-top: 7px;"></span>';
					$html .= '</a>';
					$html .= '</td>';

					$html .= '<td>';
					$html .= $asignado_a;
					$html .= '</td>';
				}

				$html .= '<td>';
				$html .= substr($asignado_el,0,16);
				$html .= '</td>';

				$html .= '<td>';
				$html .= $asignado_por;
				$html .= '</td>';

				$html .= '</tr>';
			}
		}
		$result["http_code"] = 200;
		$result["status"]    = "Datos obtenidos de gestion.";
		$result["result"]    = $html;
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "consultar_numero_de_comprobantes_de_pago_sin_notificar") {

	$administrar_vouchers_sin_enviar = $_POST["administrar_vouchers_sin_enviar"];
	$rango_dias_consultar_voucher_sin_envio = $_POST["rango_dias_consultar_voucher_sin_envio"];
	$cargo_id = $_POST["cargo_id"];

	$query_promotores_activos                     = "";
	$query_transacciones_asignada                 = "";
	$numero_de_comprobantes_de_pago_sin_notificar = 0;

	if ($administrar_vouchers_sin_enviar == 1) {
		$array_promotores_en_linea_id = array();

		$query_perfil = " p.area_id = 31 AND p.cargo_id = 5 ";

		if ($_SERVER['SERVER_NAME'] === 'localhost') {
			$query_perfil = " p.area_id = 6 ";
		}

		$sql_promotores_en_linea = "SELECT u.id
		FROM tbl_personal_apt p
		INNER JOIN tbl_usuarios u ON p.id = u.personal_id
		INNER JOIN tbl_login l ON u.id = l.usuario_id
		INNER JOIN tbl_caja c ON u.id = c.usuario_id
		WHERE
		$query_perfil
		AND p.estado = 1
		AND u.estado = 1
		AND l.logout_datetime IS NULL
		AND l.expire_datetime > now()
		AND c.fecha_operacion >= DATE_SUB(CURDATE(), INTERVAL 1 DAY)
		AND c.estado = 0";

		$query_promotores_en_linea = $mysqli->query($sql_promotores_en_linea);

		if ($mysqli->error) {
			$result["http_code"]      = 400;
			$result["status"]         = 'Se produjo el siguiente error al listar los promotores en linea';
			$result["error"]          = 'Error: ' . $mysqli->error;
			$result["consulta_error"] = $sql_promotores_en_linea;
		} else {
			$row_count = $query_promotores_en_linea->num_rows;
			if ($row_count > 0) {
				while ($row = $query_promotores_en_linea->fetch_assoc()) {
					$array_promotores_en_linea_id[] = $row["id"];
				}
			}
		}

		if (count($array_promotores_en_linea_id) > 0) {
			$query_promotores_activos = "AND cts.user_id NOT IN (" . implode(",", $array_promotores_en_linea_id) . ")";
		}
	}

	$array_transacciones_asignadas = array();

	if ($cargo_id == 5 && $administrar_vouchers_sin_enviar != 1) {

		$sql = "SELECT transaccion_id
		FROM tbl_televentas_asignaciones_temporales
		WHERE status = 1
		AND usuario_id = $usuario_id";

		$query = $mysqli->query($sql);

		if ($mysqli->error) {
			$result["http_code"]      = 400;
			$result["status"]         = 'Se produjo el siguiente error al listar las asignaciones de comprobante de pago sin notificar';
			$result["error"]          = 'Error: ' . $mysqli->error;
			$result["consulta_error"] = $sql;
		} else {
			$row_count = $query->num_rows;
			if ($row_count > 0) {
				while ($row = $query->fetch_assoc()) {
					$array_transacciones_asignadas[] = $row["transaccion_id"];
				}
			}
		}

		if (count($array_transacciones_asignadas) > 0) {
			$query_transacciones_asignada = " AND tra.id IN (" . implode(",", $array_transacciones_asignadas) . ") ";
		}
	}

	if ((count($array_transacciones_asignadas) > 0 && $cargo_id == 5) || ($administrar_vouchers_sin_enviar == 1) ){

		$sql = "SELECT COUNT(*) AS contador
		FROM
			tbl_televentas_clientes_transaccion tra
			LEFT JOIN tbl_televentas_clientes_transaccion cts ON tra.transaccion_id = cts.id
		WHERE
			(tra.tipo_id = 11 AND tra.estado IN (2))
			AND tra.created_at >= date_add(now(),interval -$rango_dias_consultar_voucher_sin_envio day)
			AND tra.created_at <= now()
			AND (tra.enviar_comprobante != 1 OR tra.enviar_comprobante IS NULL)
			$query_promotores_activos
			$query_transacciones_asignada
		";

		$query = $mysqli->query($sql);

		if ($mysqli->error) {
			$result["http_code"]      = 400;
			$result["status"]         = 'Se produjo el siguiente error al consultar el número de los comprobantes de pagos sin notificar';
			$result["error"]          = 'Error: ' . $mysqli->error;
			$result["consulta_error"] = $sql;
		} else {
			$row_count = $query->num_rows;
			if ($row_count > 0) {
				while ($row = $query->fetch_assoc()) {
					$numero_de_comprobantes_de_pago_sin_notificar = $row["contador"];
				}
			}

			$result["http_code"] = 200;
			$result["status"]    = "Datos obtenidos de gestion.";
			$result["result"]    = $numero_de_comprobantes_de_pago_sin_notificar;
		}
	} else {
		$result["http_code"] = 200;
		$result["status"]    = "Datos obtenidos de gestion.";
		$result["result"]    = $numero_de_comprobantes_de_pago_sin_notificar;
	}
}

if ($_POST["accion"] === "asignar_transaccion_a_usuario") {

	$transaccion_id_x_asignar = $_POST["transaccion_id"];
	$usuario_id_x_asignar     = $_POST["usuario_id"];
	$row_count_x_asignar      = 0;

	$sql = "SELECT id
	FROM tbl_televentas_asignaciones_temporales
	WHERE
	status = 1
	AND transaccion_id = $transaccion_id_x_asignar
	";

	$query = $mysqli->query($sql);

	if ($mysqli->error) {
		$result["http_code"]      = 400;
		$result["status"]         = 'Se produjo un error al consultar las asignaciones';
		$result["error"]          = 'Error: ' . $mysqli->error;
		$result["consulta_error"] = $sql;
	} else {
		$row_count_x_asignar = $query->num_rows;
	}

	$fecha_actual = date('Y-m-d H:i:s');

	if ($row_count_x_asignar == 0) {
		$insert_command = "INSERT INTO tbl_televentas_asignaciones_temporales(
			transaccion_id,
			usuario_id,
			status,
			user_created_id,
			created_at
		) VALUES (
			$transaccion_id_x_asignar,
			$usuario_id_x_asignar,
			1,
			$usuario_id,
			'$fecha_actual'
		)";
		$mysqli->query($insert_command);

		if ($mysqli->error) {
			$result["http_code"]      = 400;
			$result["status"]         = "Ocurrió un error al guardar la agignación en la base de datos.";
			$result["insert_command"] = $insert_command;
			$result["insert_error"]   = $mysqli->error;
		} else {
			$result["http_code"] = 200;
			$result["status"]    = "La asignacion se guardo exitosamente";
		}
	} else if ($row_count_x_asignar > 0) {
		$sql = "UPDATE tbl_televentas_asignaciones_temporales
		SET
		usuario_id = $usuario_id_x_asignar,
		user_updated_id = $usuario_id,
		updated_at = '$fecha_actual'
		WHERE
		status = 1
		AND transaccion_id = $transaccion_id_x_asignar
		";

		$query = $mysqli->query($sql);

		if ($mysqli->error) {
			$result["http_code"]      = 400;
			$result["status"]         = 'Se produjo el siguiente error al actualizar la asignación';
			$result["error"]          = 'Error: ' . $mysqli->error;
			$result["consulta_error"] = $sql;
		} else {
			$result["http_code"] = 200;
			$result["status"]    = "Datos obtenidos de gestion.";
		}
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "consultar_retiros_pagados_solicitados_por_mi") {

	$sql = "SELECT tra.id,
		tra.created_at,
		tc.tipo_doc,
		tc.num_doc,
		IFNULL(CONCAT(IFNULL(tc.nombre, ''),' ', IFNULL(tc.apellido_paterno, ''), ' ', IFNULL(tc.apellido_materno, '')), '') AS cliente
	FROM
		tbl_televentas_clientes_transaccion tra
		LEFT JOIN tbl_televentas_clientes_transaccion cts ON tra.transaccion_id = cts.id
		LEFT JOIN tbl_televentas_clientes tc ON tra.cliente_id = tc.id
	WHERE
		(tra.tipo_id = 11 AND tra.estado IN (2))
		AND tra.created_at >= date_add(now(),interval -2 day)
		AND tra.created_at <= now()
		AND (tra.enviar_comprobante != 1 OR tra.enviar_comprobante IS NULL)
		AND cts.user_id = $usuario_id
	";

	$query = $mysqli->query($sql);

	if ($mysqli->error) {
		$result["http_code"]      = 400;
		$result["status"]         = 'Se produjo el siguiente error al consultar mis comprobantes de pagos sin notificar';
		$result["error"]          = 'Error: ' . $mysqli->error;
		$result["consulta_error"] = $sql;
	} else {
		$array_retiros_pagados_solicitados_por_mi = array();

		$row_count = $query->num_rows;
		if ($row_count > 0) {
			while ($row = $query->fetch_assoc()) {
				$array_retiros_pagados_solicitados_por_mi[] = $row;
			}
		}

		$result["http_code"] = 200;
		$result["status"]    = "Datos obtenidos de gestion.";
		$result["result"]    = $array_retiros_pagados_solicitados_por_mi;
	}
}

function sec_tlv_get_value_parameter($parameter_cod){
	global $mysqli;
	global $usuario_id;

	$command = "
		SELECT 
			IFNULL(valor, 0) valor
		FROM 
			tbl_televentas_parametros
		WHERE 
			nombre_codigo = '" . $parameter_cod . "'
			AND estado = 1
		LIMIT 1
		";
	$list_query = $mysqli->query($command);
	$list = array();
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

if ($_POST["accion"] === "eliminar_transaccion_solicitud_recarga") {
	include("function_replace_invalid_caracters.php");
	$id_cliente   = $_POST["cliente_id"];
	$web_id       = $_POST["web_id"];
	$id_trans     = $_POST["trans_id"];
	$tipo_id      = $_POST["tipo_id"];
	$motivo_id    = $_POST["motivo_del_dep"];
	$observacion  = replace_invalid_caracters($_POST["observacion"]);
	$result = [];
	//Validar si la recarga ya se envió a calimaco
	$valid_recharge = array();
	$valid_recharge = api_get_recharge($id_cliente, $web_id, $id_trans);
	//$result["valid_recharge"] = $valid_recharge;
	
	if($valid_recharge){
		if(isset($valid_recharge["result"]) && isset($valid_recharge["result"][0]["operation"])) {
			//Si llegó la recarga
			$recarga_get = $valid_recharge["result"][0];
			$operation = $recarga_get["operation"];
			$amount = (double)$recarga_get["amount"] / 100;

			$cmd_transaccion = " 
				UPDATE tbl_televentas_clientes_transaccion 
				SET txn_id = '" . $operation . "',
				estado = 1
				WHERE id = " . $id_trans;
			$mysqli->query($cmd_transaccion);

			//Obtener Balance
			$list_balance = array();
			$list_balance = obtener_balances($id_cliente);
			$fecha_hora_actual = date('Y-m-d H:i:s');
			if (count($list_balance) === 1) {
				$balance_actual = (double)$list_balance[0]["balance"];
			}

			//Guardar Confirmación de la Transacción
			$insert_command = "
				INSERT INTO tbl_televentas_clientes_transaccion (
					tipo_id,
					cliente_id,
					turno_id,
					cc_id,
					web_id, 
					api_id,
					monto,
					txn_id,
					bono_monto,
					total_recarga,
					nuevo_balance,
					estado,
					user_id,
					created_at,
					transaccion_id
				) VALUES (
					2,
					'$id_cliente',
					'$turno_id',
					'$cc_id',
					'$web_id',
					2,
					$amount,
					'" . $operation . "',
					0,
					$amount,
					$balance_actual,
					1,
					$usuario_id,
					now(),
					$id_trans
				)";
			$mysqli->query($insert_command);

			$query_3 = "
				SELECT 
					id 
				FROM tbl_televentas_clientes_transaccion 
				WHERE tipo_id = 2 
				AND cliente_id = '$id_cliente' 
				AND user_id = '$usuario_id' 
				AND turno_id = '$turno_id' 
				AND web_id = '$web_id' 
				AND nuevo_balance = '$balance_actual' 
				AND monto = '$amount' 
				AND total_recarga = '$amount' 
				AND transaccion_id = '$id_trans'
				";

			$list_query = $mysqli->query($query_3);
			$list_transaccion = array();
			while ($li = $list_query->fetch_assoc()) {
				$list_transaccion[] = $li;
			}
			
			if (count($list_transaccion) == 0) {
				$result["http_code"] = 400;
				$result["status"] = "No se pudo completar la solicitud de recarga.";
			} elseif (count($list_transaccion) === 1) {
				$result["http_code"] = 200;
				$result["status"] = "La solicitud ya se encuentra en el proveedor.";
			}else{
				$result["http_code"] = 400;
				$result["status"] = "Error al consultar la confirmación de la solicitud insertada.";
			}
		}else{
			//No llegó la recarga
			$result = rollback_transaccion($id_cliente, $id_trans, $usuario_id, $turno_id, $observacion, $motivo_id);
		}
	}else{
		$result["http_code"] = 400;
		$result["status"] = "Hubo un error al validar la existencia de esta solicitud.";
	}
}

function api_get_recharge($id_cliente, $web_id, $id_trans){
	$url = "https://api.apuestatotal.com/v2/teleservicios/calimaco/getRechargeWeb";
	$rq = ['clientId' => $id_cliente, 'webId' => $web_id, 'txnId' => $id_trans];
	$auditoria_id = api_calimaco_auditoria_insert('getRechargeWeb', $id_trans, $id_cliente, $url);
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
	$status = 0;
	$result = array();
	if ($err) {
		$status = 0;
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consumir el API.";
		$result["result"] = $response;
		$result["error"] = "cURL Error #:" . $err;
	} else {
		$status = 1;
		$result = $response_arr;
	}
	api_calimaco_auditoria_update($auditoria_id, $id_trans, $response, $status);
	return $result;
}

function sec_tlv_log_error($message)
{
	$logFile   = '/var/www/html/logs/teleservicios_errores_mysql.log';
	$timestamp = date('Y-m-d H:i:s');
	$errorLog  = "$timestamp - $message" . PHP_EOL;

	if ($handle = @fopen($logFile, 'a')) {
		if (fwrite($handle, $errorLog)) {
			fclose($handle);
		}
	}
}

function sec_tlv_log($message)
{
	$fecha = date('Ymd');
	$logFile = '/var/www/html/logs/tls_solicitud_depositos_' . $fecha .'.log';
	$timestamp = date('Y-m-d H:i:s');
	$errorLog = "$timestamp - $message" . PHP_EOL;

	if ($handle = @fopen($logFile, 'a')) {
		if (fwrite($handle, $errorLog)) {
			fclose($handle);
		}
	}
}


if (isset($_POST["accion"]) && $_POST["accion"] === "marcar_premio_copiado") {
	$cliente_id = $_POST["cliente_id"];
	$query ="
		UPDATE 
			tbl_televentas_clientes_premios 
		SET 
			status = 1,
			updated_user_id = '" . $usuario_id . "',
			updated_at = now()
		WHERE cliente_id = '" . $cliente_id . "'
		AND status = 0 ";

	$mysqli->query($query);
	if ($mysqli->error) {
		$result["http_code"] = 400;
	}else{
		$result["http_code"] = 200;
	}
}

echo json_encode($result);
?>