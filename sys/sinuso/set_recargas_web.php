<?php
$result = array();
include("db_connect.php");
include("sys_login.php");
include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';

require("globalFunctions/generalInfo/local.php");
require("globalFunctions/generalInfo/parameterGeneral.php");
require("globalFunctions/templates/blackAlert.php");
require("globalFunctions/templates/objectsHttp.php");
require("globalFunctions/templates/emailSender.php");


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function get_turno()
{
	global $login;
	global $mysqli;
	$usuario_id = $login['id'];
	//$command ="SELECT id FROM tbl_caja WHERE estado=0 AND usuario_id=".$usuario_id;
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

function api_calimaco_checkUser($id_cliente)
{
	$msj = $id_cliente;
	$clavesecreta = env('CALIMACO_RETAIL_PASSWORD');
	$hash_encryp = hash_hmac('sha256', $msj, $clavesecreta, false);

	$url = env('CALIMACO_RETAIL_URL') . "checkUser";
	$url .= "&user=" . $id_cliente .  "&hash=" . $hash_encryp;

	$auditoria_id = api_calimaco_auditoria_inset('checkUser', $id_cliente, $url);

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
		$response = str_replace("'", " ", $response);
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
			if (isset($response_arr["result"])) {
				$status = ($response_arr["result"] === "OK") ? 1 : 0;
			}
		}
	} catch (Exception $e) {
		$response = 'Excepción capturada: ' .  $e->getMessage();
	}

	api_calimaco_auditoria_update($auditoria_id, 0, 0, $response, $status);
	//api_calimaco_auditoria_inset('checkUser', $id_cliente, 0, 0, $url, $response, $status);
	return $result;
}

function api_calimaco_auditoria_inset($method, $client_id, $body)
{
	global $mysqli;
	global $login;
	date_default_timezone_set("America/Lima");
	$date_time = date('Y-m-d H:i:s');
	$user_id = $login ? $login['id'] : 0;

	$insert_command = "
		INSERT INTO tbl_saldo_web_api_calimaco_response (
			method,
			client_id,
			body,
			status,
			user_id,
			created_at
		) VALUES (
			'" . $method . "',
			'" . $client_id . "',
			'" . $body . "',
			'0',
			'" . $user_id . "',
			'" . $date_time . "'
		)";
	$mysqli->query($insert_command);

	$query = "
		SELECT
			t.id 
		FROM
			tbl_saldo_web_api_calimaco_response t 
		WHERE
			t.method = '$method' 
			AND t.client_id = '$client_id' 
			AND t.body = '$body' 
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

function api_calimaco_auditoria_update($auditoria_id, $txn_id, $amount, $response, $status)
{
	global $mysqli;
	date_default_timezone_set("America/Lima");
	$date_time = date('Y-m-d H:i:s');

	$update_command = "
		UPDATE tbl_saldo_web_api_calimaco_response
		SET
			txn_id='" . $txn_id . "', 
			amount='" . $amount . "', 
			response='" . $response . "', 
			status='" . $status . "', 
			updated_at='" . $date_time . "' 
		WHERE 
			id='" . $auditoria_id . "' 
		";
	$mysqli->query($update_command);
}

function api_calimaco_deposito($id_cliente, $cc_id, $amount, $transaccion_id)
{
	$msj = $amount . ":" . $cc_id . ":" . $id_cliente;
	$clavesecreta = env('CALIMACO_RETAIL_PASSWORD');
	$hash_encryp = hash_hmac('sha256', $msj, $clavesecreta, false);

	// $url = env('CALIMACO_RETAIL_URL') . "Red_AT";
	$method = "Red_Agentes";
	// $method = "ATPAYMENTAGENTES";
	$url = env('CALIMACO_RETAIL_URL') . $method;
	$url .= "&user=" . $id_cliente;
	$url .= "&amount=" . $amount;
	$url .= "&idTienda=" . $cc_id;
	$url .= "&hash=" . strtoupper($hash_encryp);
	$url .= "&at_operation=" . $transaccion_id;

	$auditoria_id = api_calimaco_auditoria_inset($method, $id_cliente, $url);

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
			$txn_id = isset($response_arr["operationId"]) ? $response_arr["operationId"] : 0;
			if (isset($response_arr["result"])) {
				$status = ($response_arr["result"] === "OK") ? 1 : 0;
			}
		}
	} catch (Exception $e) {
		$response = 'Excepción capturada: ' .  $e->getMessage();
	}

	api_calimaco_auditoria_update($auditoria_id, $txn_id, ($amount / 100), $response, $status);
	//api_calimaco_auditoria('Red_AT', $id_cliente, $txn_id, ($amount/100), $url, $response, $status, $usuario_id);
	return $result;
}



//*******************************************************************************************************************
// OBTENER CLIENTE
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_cliente") {

	$id_cliente = $_POST["id_web"];

	$usuario_id = $login ? $login['id'] : 0;
	if ((int) $usuario_id > 0) {
		// $turno = get_turno();
		/* 		if (count($turno) > 0) {
			$turno_id = $turno[0]["id"];
			if ((int) $turno_id > 0) {
 */
		$array_cliente = array();
		$array_cliente = api_calimaco_checkUser($id_cliente);
		//echo json_encode($array_cliente);exit();
		if (isset($array_cliente["result"])) {
			if ($array_cliente["result"] === "OK") {
				$result = generateResultHttp([
					"http_code" => 200,
					"status" => "ok",
					"cliente_name" => mb_strtoupper($array_cliente["first_name"] . " " . $array_cliente["middle_name"] . " " . $array_cliente["last_name"], 'UTF-8'),
				]);
			} else {
				$result = generateResultHttp([
					"http_code" => 400,
					"status" => "El ID-WEB no existe.",
				]);
			}
		} else {
			$result = generateResultHttp([
				"http_code" => 400,
				"status" => "La api respondio un error.",
			]);
		}
	} else {
		$result = generateResultHttp([
			"http_code" => 400,
			"status" => "Sesión perdida.",
		]);
	}
}

//*******************************************************************************************************************
// OBTENER TRANSACCION DEL CLIENTE DESPUES DE SU DEPOSITO
//*******************************************************************************************************************
if ((isset($_POST["accion"]) && $_POST["accion"] === "obtener_transaccion") || isset($_POST["obtener_transaccion"])) {

	date_default_timezone_set("America/Lima");
	if (isset($_POST["accion"])) {
		$txn_id = $_POST["txn_id"];
		$tipo_id = $_POST["tipo_id"];
	} else {
		$data = $_POST['obtener_transaccion'];
		$txn_id = $data['txn_id'];
		$tipo_id = $data['tipo_id'];
	}

	$usuario_id = $login ? $login['id'] : 0;
	if (!((int) $usuario_id > 0)) {
		$result["http_code"] = 400;
		$result["status"] = "Sesión perdida. Por favor vuelva a iniciar sesión.";
		echo json_encode($result);
		exit();
	}
	/* 
	$turno = get_turno();
	if (!(count($turno) > 0)) {
		$result["http_code"] = 400;
		$result["status"] = "Debe abrir un turno.";
		$result["result"] = $turno;
		echo json_encode($result);
		exit();
	}
 */
	$query = "
			SELECT
				swt.txn_id,
				swt.cc_id,
				swt.created_at,
				IFNULL(swt.client_id, '') client_id,
				IFNULL(swt.client_num_doc, '') client_num_doc,
				IFNULL(swt.client_name, '') client_name,
				swt.monto,
				IFNULL(l.nombre, '') local_nombre,
				IFNULL(l.direccion, '') local_direccion
			FROM
				tbl_saldo_web_transaccion swt
			LEFT JOIN
				tbl_locales l ON l.cc_id = swt.cc_id
			WHERE
				swt.tipo_id = '" . $tipo_id . "' 
				AND swt.id = '" . $txn_id . "' 
				AND swt.status = 1
		";
	$list_query = $mysqli->query($query);
	$transaccion = array();
	while ($li = $list_query->fetch_assoc()) {
		$transaccion = $li;
	}

	$result["result"] = $transaccion;
	$result["http_code"] = 200;
	$result["status"] = "ok";
	$result["fecha_hora_actual"] = date('Y-m-d H:i:s');

	$result = json_encode($result);
}

//*******************************************************************************************************************
// REALIZAR DEPÓSITO
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "realizar_deposito") {

	date_default_timezone_set("America/Lima");
	$date_time = date('Y-m-d H:i:s');

	$client_id = $_POST["id_web"];
	$client_name = $_POST["client_name"];
	$monto = (float)$_POST["monto"];
	$cc_id = $_POST["cc_id"];
	$local_id = $_POST["local_id"];
	$tienda_nombre = $_POST["tienda_nombre"];

	$bloquear_agente_can_deposit = false;
	$enviar_alerta_mail = false;

	if (!(strlen($client_id) > 0)) {
		echo generateResultHttp(["http_code" => 400, "status" => "Debe ingresar un ID-WEB válido."]);
		exit();
	}

	$usuario_id = $login ? $login['id'] : 0;
	if (!((int) $usuario_id > 0)) {
		echo generateResultHttp(["http_code" => 400, "status" => "Sesión perdida. Por favor vuelva a iniciar sesión.",]);
		exit();
	}

	//validar monto

	$min_monto = getParameterGeneral('monto_min_recarga_web');
	$max_monto = getParameterGeneral('monto_max_recarga_web');
	$created_at = getLastActivacionSaldoWeb($local_id);
	$monto_local = getMontoLocalFromLastActivacion($cc_id, $created_at);

	if (!validarMontoRecarga($monto, $min_monto, $max_monto)) {
		//la validacion no procede
		echo generateResultHttp(["http_code" => 400, "status" => "El monto debe ser mínimo de $min_monto y máximo de $max_monto."]);
		exit();
	}

	//la validacion procede
	$monto_validacion = validarMontoRecargaLocal($cc_id, $monto, $local_id, $monto_local);
	//fin validar monto - JV

	if (is_numeric($monto_validacion) && $monto_validacion <= $max_monto) {
		// echo 'asdas';
		// exit();

		$insert_command = "
			INSERT INTO tbl_saldo_web_transaccion (
				tipo_id,
				client_id,
				client_name,
				monto,
				status,
				cc_id,
				turno_id,
				user_id,
				created_at
			) VALUES (
				'1',
				'" . $client_id . "',
				'" . $client_name . "',
				" . $monto . ",
				'0',
				'" . $cc_id . "',
				null,
				'" . $usuario_id . "',
				'" . $date_time . "'
			)";
		$mysqli->query($insert_command);
		$transaccion_id = $mysqli->insert_id;

		$result = realizarDeposito($transaccion_id, $monto, $client_id, $cc_id, $monto_validacion, $max_monto, $local_id);
		if (isset(json_decode($result)->enviar_alerta_mail)) {
			$enviar_alerta_mail = json_decode($result)->enviar_alerta_mail;
		}
	} elseif ($monto_validacion === false || $monto_validacion > $max_monto) {
		$lock_agente_can_deposit = false;
		//si el monto nuevo local es = al maximo: bloquear y enviar mail				
		if ($monto_local >= $max_monto) {
			$lock_agente_can_deposit = lockAgenteCanDeposit($local_id);
		}
		$credito = getCreditoLocal($local_id, $cc_id);
		$enviar_alerta_mail = true;
		$result = generateResultHttp([
			"http_code" => 400,
			"status" => "El monto que intenta recargar supera el límite máximo de recargas. \n Crédito disponible: S/ " . number_format($credito, 2),
			"alerta_limite" => true,
			'lock_agente_can_deposit' => $lock_agente_can_deposit
		]);
	}

	if ($enviar_alerta_mail) {
		$info_mail = getInfoAlertBlackMail($tienda_nombre, $local_id, $cc_id, $max_monto, $monto);
		emailSender($info_mail);
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "realizar_deposito_reintento") {

	date_default_timezone_set("America/Lima");
	$date_time = date('Y-m-d H:i:s');
	$enviar_alerta_mail = false;
	$client_id = $_POST["id_web"];
	$cc_id = $_POST["cc_id"];
	$transaccion_id = $_POST["cod_txn"];
	$tienda_nombre = $_POST["tienda_nombre"];

	if (!(strlen($client_id) > 0)) {
		$result = generateResultHttp([
			"http_code" => 400,
			"status" => "Debe ingresar un ID-WEB válido.",
			"result" => '',
		]);
		echo $result;
		exit();
	}

	$usuario_id = $login ? $login['id'] : 0;
	if (!((int) $usuario_id > 0)) {
		$result = generateResultHttp([
			"http_code" => 400,
			"status" => "Sesión perdida. Por favor vuelva a iniciar sesión.",
		]);
		echo $result;
		exit();
	}

	$transaccion = validarTransaccion($client_id, $transaccion_id, $cc_id);
	if (!is_array($transaccion)) {
		$result = $transaccion;
		echo $result;
		exit();
	}

	$cc_id = $transaccion['cc_id'];
	$monto = $transaccion['monto'];
	$local_id = getLocalIdfromCCid($cc_id);
	$max_monto = getParameterGeneral('monto_max_recarga_web');
	$created_at = getLastActivacionSaldoWeb($local_id);
	$monto_local = getMontoLocalFromLastActivacion($cc_id, $created_at);

	//la validacion procede
	$monto_validacion = validarMontoRecargaLocal($cc_id, $monto, $local_id, $monto_local);
	//fin validar monto - JV

	if (is_numeric($monto_validacion) && $monto_validacion <= $max_monto) {

		$result = realizarDeposito($transaccion_id, $monto, $client_id, $cc_id, $monto_validacion, $max_monto, $local_id);
		if (isset(json_decode($result)->enviar_alerta_mail)) {
			$enviar_alerta_mail = json_decode($result)->enviar_alerta_mail;		
		}
	} elseif ($monto_validacion === false || $monto_validacion > $max_monto) {
		$lock_agente_can_deposit = false;
		//si el monto nuevo local es = al maximo: bloquear y enviar mail				
		if ($monto_local >= $max_monto) {
			$lock_agente_can_deposit = lockAgenteCanDeposit($local_id);
		}
		$credito = getCreditoLocal($local_id, $cc_id);
		$enviar_alerta_mail = true;
		$result = generateResultHttp([
			"http_code" => 400,
			"status" => "El monto que intenta recargar supera el límite máximo de recargas. \n Crédito disponible: S/ " . number_format($credito, 2),
			"alerta_limite" => true,
			'lock_agente_can_deposit' => $lock_agente_can_deposit
		]);
	}

	if ($enviar_alerta_mail) {
		// echo getCreditoLocal($local_id, $cc_id);
		// exit();
		$info_mail = getInfoAlertBlackMail($tienda_nombre, $local_id, $cc_id, $max_monto, $monto);
		emailSender($info_mail);
	}
}

function validarTransaccion($client_id, $transaccion_id, $cc_id)
{
	global $mysqli;
	$result = false;

	$query = "
		SELECT
			t.id,
			t.cc_id,
			t.monto
		FROM
			tbl_saldo_web_transaccion t 
		WHERE
			t.tipo_id = '1' 
			AND t.client_id = '$client_id' 
			AND t.id = '$transaccion_id' 
			AND t.status = '0' 
			AND t.cc_id = $cc_id
		ORDER BY
			t.id DESC 
		LIMIT 1
	";
	$list_query = $mysqli->query($query);
	$list = array();
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if (count($list) == 0) {
		$result = generateResultHttp([
			"http_code" => 400,
			"status" => "Debe ingresar un ID-WEB y un ID de transacción válidos.",
		]);
	} elseif (count($list) == 1) {
		$result = $list[0];
	}

	return $result;
}
//*******************************************************************************************************************
// OBTENER TRANSACCIONES
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_cliente_x_transacciones") {

	$id_cliente = $_POST["id_web"];
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$where_cajero = "";
	$where_fecha = "";
	if ((int)$area_id !== 6) { // diferente de sistemas
		if ((int) $cargo_id === 5) { // Si es cajero
			$where_cajero = " AND t.user_id = '" . $usuario_id . "' ";
			$where_fecha = " AND TIMESTAMPDIFF(HOUR,t.created_at,now()) <= 24 ";
		} else {
		}
	}


	$query = "
		SELECT
			t.id cod_transaccion,
			t.tipo_id,
			( CASE t.tipo_id WHEN 1 THEN 'Depósito' WHEN 2 THEN 'Retiro' WHEN 3 THEN 'Extorno' ELSE 'Otros' END ) tipo,
			IFNULL(t.txn_id, '') txn_id,
			IFNULL(t.monto, 0) monto,
			IF(t.status=1, 'Completado', 'Fallido') status,
			t.created_at registro,
			u.id cod_usuario,
			u.usuario,
			t.cc_id
		FROM
			tbl_saldo_web_transaccion t
			LEFT JOIN tbl_usuarios u ON u.id = t.user_id 
		WHERE
			t.`status` IN (0,1) 
			AND t.client_id = '$id_cliente' 
			$where_cajero
			$where_fecha
		ORDER BY
			t.id DESC 
		LIMIT 10
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
			$result = generateResultHttp([
				"http_code" => 200,
				"status" => "ok",
				"result" => $list,
			]);
		} else {
			$result = generateResultHttp([
				"http_code" => 400,
				"status" => "Ocurrió un error al consultar las transacciones.",
			]);
		}
	}
}

//*******************************************************************************************************************
// OBTENER CREDITO DISPONIBLE DEL LOCAL
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_credito_disponible_local") {

	$cc_id = $_POST["cc_id"];
	$local_id = $_POST["local_id"];
	$credito = 0;
	$agente_can_deposit = getLocalWebConfig($local_id, 'agente_can_deposit');

	if ($agente_can_deposit) {
		$credito = getCreditoLocal($local_id, $cc_id);
	}

	$result = generateResultHttp([
		"http_code" => 200,
		"status" => "ok",
		"result" => $credito,
	]);
}

echo $result;

function realizarDeposito($transaccion_id, $monto, $client_id, $cc_id, $monto_validacion, $max_monto, $local_id)
{
	global $mysqli;

	$array_cliente = array();
	$enviar_alerta_mail = false;
	$monto_calimaco = $monto * 100;

	//deposito calimaco
	$array_cliente = api_calimaco_deposito($client_id, $cc_id, $monto_calimaco, $transaccion_id);

	if (isset($array_cliente["result"])) {
		if ($array_cliente["result"] === "OK") {

			$update_command = "
						UPDATE tbl_saldo_web_transaccion
						SET
							txn_id = '" . $array_cliente["operationId"] . "', 
							status = '1', 
							updated_at = '" . date('Y-m-d H:i:s') . "' 
						WHERE id='" . $transaccion_id . "'";
			$mysqli->query($update_command);
			$lock_agente_can_deposit = false;
			$alerta_limite = false;
			//si el monto nuevo local es = al maximo: bloquear y enviar mail					
			if ($monto_validacion == $max_monto) {
				$lock_agente_can_deposit = lockAgenteCanDeposit($local_id);
				$enviar_alerta_mail = true;
				$alerta_limite = true;
			}

			$result = generateResultHttp([
				"http_code" => 200,
				"status" => "ok",
				"operationId" => $array_cliente["operationId"],
				"cod_transaccion" => $transaccion_id,
				'lock_agente_can_deposit' => $lock_agente_can_deposit,
				"alerta_limite" => $alerta_limite,
				"enviar_alerta_mail" => $enviar_alerta_mail,
			]);
		} elseif ($array_cliente["result"] === "error") {
			$status = isset($array_cliente["description"]) ? $array_cliente["description"] : 'La API respondio un mensaje ilegible';
			$result = generateResultHttp([
				"http_code" => 400, "status" => $status,
				"result" => $array_cliente
			]);
		} else {
			$result = generateResultHttp([
				"http_code" => 400, "status" => "La API respondio un error.",
				"result" => $array_cliente
			]);
		}
	} else {
		$result = generateResultHttp(["http_code" => 400, "status" => "Ocurrió un error con el API, comunícate con soporte para confirmar la operación."]);
	}

	return $result;
}

function validarMontoRecargaLocal($cc_id, $monto_recarga, $local_id, $monto_local)
{
	global $mysqli;
	$result = false;
	$agente_can_deposit = getLocalWebConfig($local_id, 'agente_can_deposit');

	if ($agente_can_deposit) {
		//validacion de monto
		$new_monto = $monto_local + $monto_recarga;
		$result = $new_monto;
	}

	return $result;
}

function getInfoAlertBlackMail($tienda_nombre, $local_id, $cc_id, $max_monto, $monto)
{

	$info_mail = [];
	$info_mail['subject'] = 'ALERTA NEGRA RECARGA WEB ' . $tienda_nombre;
	$info_mail['address'] = [];
	$info_mail['cc'] = [];
	$info_mail['bcc'] = [];

	$supervisores = getSupervisores($local_id);
	$jefe_comercial = getJefeComercial($local_id);

	array_push(
		$info_mail['address'],
		$jefe_comercial['jefe_comercial_correo']
		// 'josue.vitate@testtest.apuestatotal.com',

	);

	$supervisor_nombre = '';

	foreach ($supervisores as $supervisor) {
		array_push(
			$info_mail['address'],
			$supervisor['supervisor_correo'],
		);

		$supervisor_nombre .= ($supervisor_nombre != '' ? ', ' : '') . $supervisor['supervisor_nombre'];
	}

	// $info_mail['cc'] = [
	// 	'murice.rafael@testtest.apuestatotal.com',
	// 	'soporte@testtest.apuestatotal.com',
	// ];

	$info_mail['bcc'] = [
		// 'josue.vitate@testtest.apuestatotal.com',
		'kevin.montes@testtest.kurax.dev',
		// 'joselin.burgos@testtest.apuestatotal.com',
	];

	$alert_info = [
		['Tienda', $tienda_nombre],
		['Centro de costos', $cc_id],
		['Jefe Comercial', $jefe_comercial['jefe_comercial_nombre']],
		['Supervisor', $supervisor_nombre],
	];

	$credito = getCreditoLocal($local_id, $cc_id);
	if ($credito == 0) {
		array_push(
			$alert_info,
			['Límite establecido alcanzado', 'S/ ' . number_format($max_monto, 2)],
		);
	} else if ($credito > 0) {
		array_push(
			$alert_info,
			['Límite establecido', 'S/ ' . number_format($max_monto, 2)],
			['Recarga que se desea realizar', 'S/ ' . number_format($monto, 2)]
		);
	}


	$info_mail['body'] = generateTableAlertBlack($alert_info);

	return $info_mail;
}

function validarMontoRecarga($monto_recarga, $min_monto, $max_monto)
{
	return ($monto_recarga >= $min_monto &&  $monto_recarga <= $max_monto);
}
