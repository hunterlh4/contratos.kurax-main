<?php
$result = array();
include("db_connect.php");
include("sys_login.php");
include("globalFunctions/generalInfo/functions_api_calimaco.php");
require("globalFunctions/generalInfo/parameterGeneral.php");
require("globalFunctions/generalInfo/local.php");
require("globalFunctions/generalInfo/functions_saldos_web.php");


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
if (!((int) $usuario_id > 0)) {
	$result["http_code"] = 400;
	$result["status"] = "Sesión perdida. Por favor vuelva a iniciar sesión.";
	echo json_encode($result);exit();
}

if(!isset($_POST["accion"]) && !isset($_POST["obtener_transaccion"])){
	$result["http_code"] = 400;
	$result["status"] = "Acción no válida.";
	echo json_encode($result);exit();
}

$turno_id = 0;
$cc_id = '';

$array_developers = get_array_developers();

if(!(in_array($login["usuario"], $array_developers))){

	$validate_turno_for_accion = get_array_validate_turno();

	if (in_array($_POST["accion"], $validate_turno_for_accion) || isset($_POST["obtener_transaccion"])) {
		$turno = get_turno();
		if (!(count($turno) > 0)) {
			$result["http_code"] = 400;
			$result["status"] = "Debe abrir un turno.";
			$result["result"] = $turno;
			echo json_encode($result);exit();
		}
		$turno_id = $turno[0]["id"];
		$cc_id = $turno[0]["cc_id"];
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
		$turno_id = $turno[0]["id"];
		$cc_id = $turno[0]["cc_id"];
	} else {
		$cc_id = '0666';
	}
}


function get_array_developers(){
	$array_developers = array();
	$array_developers[] = "bladimir.quispe";
	$array_developers[] = "jhonny.quispe";
	return $array_developers;
}

function get_array_validate_turno(){
	$validate_turno = array();
	$validate_turno[] = "obtener_cliente";
	$validate_turno[] = "obtener_transaccion";
	$validate_turno[] = "realizar_deposito";
	$validate_turno[] = "realizar_deposito_reintento";
	$validate_turno[] = "consultar_retiro";
	$validate_turno[] = "realizar_retiro";
	return $validate_turno;
}

function get_turno() {
	global $login;
	global $mysqli;
	$usuario_id = $login['id'];
	//$command ="SELECT id FROM tbl_caja WHERE estado=0 AND usuario_id=".$usuario_id;
	$command = "
		SELECT
			sqc.id,
			ssql.id local_id,
			ssql.cc_id,
			sqlc.id local_caja_id
		FROM
			tbl_caja sqc
			JOIN tbl_local_cajas sqlc ON sqlc.id = sqc.local_caja_id
			JOIN tbl_locales ssql ON ssql.id = sqlc.local_id 
		WHERE
			sqc.estado = 0 
			AND sqc.usuario_id = '" . $usuario_id . "' 
			AND sqc.fecha_operacion = '".date('Y-m-d')."' 
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

function api_calimaco_deposito($id_cliente, $cc_id, $amount, $transaccion_id){
	$msj = $amount.":".$cc_id.":".$id_cliente;
	$clavesecreta = env('CALIMACO_RETAIL_PASSWORD');
	$hash_encryp = hash_hmac('sha256', $msj, $clavesecreta, false);

	$url = env('CALIMACO_RETAIL_URL')."Red_AT";
	$url .= "&user=" . $id_cliente;
	$url .= "&amount=" . $amount;
	$url .= "&idTienda=" . $cc_id;
	$url .= "&hash=" . strtoupper($hash_encryp);
	$url .= "&at_operation=" . $transaccion_id;

	$auditoria_id = api_calimaco_auditoria_inset('Red_AT', $id_cliente, $url);

	$response = null;
	$result = array();
	$status = 0;
	$txn_id = 0;

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
				$txn_id = isset($response_arr["operationId"]) ? $response_arr["operationId"] : 0;
				if(isset($response_arr["result"])){
					$status = ($response_arr["result"]==="OK") ? 1 : 0;
				}
			}
		} catch (Exception $e) {
			$response = 'Excepción capturada: '.  $e->getMessage();
		}

		api_calimaco_auditoria_update($auditoria_id, $txn_id, ($amount/100), $response, $status);

	}

	//api_calimaco_auditoria('Red_AT', $id_cliente, $txn_id, ($amount/100), $url, $response, $status, $usuario_id);
	return $result;
}

function api_calimaco_consultar_retiro($id_cliente, $cc_id){
	$msj = $id_cliente.":".$cc_id;
	$clavesecreta = env('CALIMACO_RETAIL_PASSWORD');
	$hash_encryp = hash_hmac('sha256', $msj, $clavesecreta, false);

	$url = env('CALIMACO_RETAIL_URL')."checkCashoutUser";
	$url .= "&user=" . $id_cliente;
	$url .= "&idTienda=" . $cc_id;
	$url .= "&hash=" . $hash_encryp;

	$auditoria_id = api_calimaco_auditoria_inset('checkCashoutUser', $id_cliente, $url);

	$response = null;
	$result = array();
	$status = 0;
	$txn_id = 0;
	$amount = 0;

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
				$txn_id = isset($response_arr["IdTransacción"]) ? $response_arr["IdTransacción"] : 0;
				$amount = isset($response_arr["amount"]) ? $response_arr["amount"]/100 : 0;
				if(isset($response_arr["result"])){
					$status = ($response_arr["result"]==="OK") ? 1 : 0;
				}
			}
		} catch (Exception $e) {
			$response = 'Excepción capturada: '.  $e->getMessage();
		}

		api_calimaco_auditoria_update($auditoria_id, $txn_id, $amount, $response, $status);
		//api_calimaco_auditoria('checkCashoutUser', $id_cliente, $txn_id, $amount, $url, $response, $status, $usuario_id);
	}
	return $result;
}

function api_calimaco_realizar_retiro($id_cliente, $num_doc, $cc_id, $id_transaccion){
	$msj = $id_cliente.":".$cc_id.":".$id_transaccion.":".$num_doc;
	$clavesecreta = env('CALIMACO_RETAIL_PASSWORD');
	$hash_encryp = hash_hmac('sha256', $msj, $clavesecreta, false);

	$url = env('CALIMACO_RETAIL_URL')."cashout";
	$url .= "&user=" . $id_cliente;
	$url .= "&dni=" . $num_doc;
	$url .= "&idTienda=" . $cc_id;
	$url .= "&operation=" . $id_transaccion;
	$url .= "&hash=" . $hash_encryp;

	$auditoria_id = api_calimaco_auditoria_inset('cashout', $id_cliente, $url);

	$response = null;
	$result = array();
	$status = 0;
	$txn_id = $id_transaccion;
	$amount = 0;

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
				$amount = isset($response_arr["amount"]) ? $response_arr["amount"] : 0;
				if(isset($response_arr["result"])){
					$status = ($response_arr["result"]==="OK") ? 1 : 0;
				}
			}
		} catch (Exception $e) {
			$response = 'Excepción capturada: '.  $e->getMessage();
		}
		api_calimaco_auditoria_update($auditoria_id, $txn_id, $amount, $response, $status);
		//api_calimaco_auditoria('cashout', $id_cliente, $id_transaccion, $amount, $url, $response, $status, $usuario_id);
	}
	return $result;
}



//*******************************************************************************************************************
// OBTENER CLIENTE
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_cliente") {

	$id_cliente = $_POST["id_web"];

	$array_cliente = array();
	$array_cliente = api_calimaco_checkUser($id_cliente, 'checkUser');
	//echo json_encode($array_cliente);exit();
	if(isset($array_cliente["result"])){
		if($array_cliente["result"]==="OK") {
			$result["http_code"] = 200;
			$result["status"] = "ok";
			$result["cliente_name"] = mb_strtoupper($array_cliente["first_name"]. " " . $array_cliente["middle_name"] . " " . $array_cliente["last_name"], 'UTF-8');
		} else {
			$result["http_code"] = 400;
			//$result["response"] = $array_cliente;
			$result["status"] = "El ID-WEB no existe.";
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] = "La api respondio un error.";
	}
}



//*******************************************************************************************************************
// OBTENER TRANSACCION DEL CLIENTE DESPUES DE SU DEPOSITO
//*******************************************************************************************************************
if ((isset($_POST["accion"]) && $_POST["accion"] === "obtener_transaccion") || isset($_POST["obtener_transaccion"])) {

	date_default_timezone_set("America/Lima");
	if(isset($_POST["accion"]) ) {
		if(isset($_POST["obtener_transaccion"])){
			$data = $_POST['obtener_transaccion'];
			$txn_id = $data['txn_id'];
			$tipo_id = $data['tipo_id'];
		} else {
			$txn_id = $_POST["txn_id"];
			$tipo_id = $_POST["tipo_id"];
		}
	} else {
		$data = $_POST['obtener_transaccion'];
		$txn_id = $data['txn_id'];
		$tipo_id = $data['tipo_id'];
	}

	$query ="
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
	$list_query=$mysqli->query($query);
	$transaccion=array();
	while ($li=$list_query->fetch_assoc()) {
		$transaccion=$li;
	}

	$result["result"] = $transaccion;
	$result["http_code"] = 200;
	$result["status"] = "ok";
	$result["fecha_hora_actual"] = date('Y-m-d H:i:s');

}

if (isset($_POST["accion"]) && $_POST["accion"] === "verify_btns" && isset($_POST["btn"])) {
    if(!in_array($_POST['btn'],getPermisos($login))){
        $result["http_code"] = 400;
        $result["status"] = "No tiene permisos para realizar la operación, debe comunicarse con soporte.";
        $result["result"] = "";
        echo json_encode($result);
        exit();
    }
    $result["http_code"] = 200;
    echo json_encode($result);
    exit();
}
function getPermisos($login){
    global $mysqli;
    $permisos=[];
    $usuario_permisos_query = $mysqli->query("SELECT p.usuario_id, p.menu_id, b.boton
												    FROM tbl_permisos p
												    LEFT JOIN tbl_botones b ON (p.boton_id = b.id)
												    WHERE p.usuario_id = '".$login["id"]."' 
														AND p.estado = '1' 
														AND p.menu_id=229 
														AND grupo_id=".$login["grupo_id"]);
    while($usu_per = $usuario_permisos_query->fetch_assoc()) {
        $permisos[] = $usu_per["boton"];
    }
    return $permisos;
}
//*******************************************************************************************************************
// REALIZAR DEPÓSITO
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "realizar_deposito") {
    if(!in_array('deposit',getPermisos($login))){
        $result["http_code"] = 400;
        $result["status"] = "No tiene permisos para realizar la operación, debe comunicarse con soporte.";
        $result["result"] = "";
        echo json_encode($result);exit();
    }
	date_default_timezone_set("America/Lima");
	$date_time = date('Y-m-d H:i:s');

	$client_id = $_POST["id_web"];
	$client_name = $_POST["client_name"];
	$monto = (float)$_POST["monto"]*100;
	//$cc_id = $_POST["cc_id"];

	$max_monto_deposito = getParameterGeneral('monto_max_deposito_web');
	
	if(!((float) $_POST["monto"] >= 1.00 && (float) $_POST["monto"] <= $max_monto_deposito) ){
		$result["http_code"] = 400;
		$result["status"] = "El monto debe ser mínimo de 1.00 y máximo de " . number_format($max_monto_deposito,2) . ".";
		$result["result"] = '';
		echo json_encode($result);exit();
	}	

	$limite_global_transaccion = getLimite('transaccion_global', null);
	$monto_limite_global_transaccion = (float)$limite_global_transaccion['limite'] ;

	if(!((float) $_POST["monto"] >= 1.00 && (float) $_POST["monto"] <= $monto_limite_global_transaccion) ){
		$result["http_code"] = 400;
		$result["status"] = "El monto debe ser mínimo de 1.00 y máximo de " . number_format($monto_limite_global_transaccion, 2) . ".";
		$result["result"] = '';
		echo json_encode($result);exit();
	}	


	if(!(strlen($client_id)>0)){
		$result["http_code"] = 400;
		$result["status"] = "Debe ingresar un ID-WEB válido.";
		$result["result"] = '';
		echo json_encode($result);exit();
	}

	$validateAmounts = fncValidateLimits($cc_id,$client_id,$monto);
	if(isset($validateAmounts["http_code"]) && $validateAmounts["http_code"]==400){
		echo json_encode($validateAmounts);
		exit();
	}
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
			'" . ($monto/100) . "',
			'0',
			'" . $cc_id . "',
			'" . $turno_id . "',
			'" . $usuario_id . "',
			'" . $date_time . "'
		)";
	$mysqli->query($insert_command);

	$query = "
		SELECT
			t.id 
		FROM
			tbl_saldo_web_transaccion t 
		WHERE
			t.tipo_id = '1' 
			AND t.client_id = '$client_id' 
			AND t.client_name = '$client_name' 
			AND t.monto = '".($monto/100)."' 
			AND t.status = 0 
			AND t.cc_id = '$cc_id' 
			AND t.turno_id = '$turno_id' 
			AND t.user_id = '$usuario_id' 
			AND t.created_at = '$date_time' 
		ORDER BY
			t.id DESC 
		LIMIT 1
	";
	$list_query = $mysqli->query($query);
	$list = array();
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al registrar la transacción.";
		$result["query"] = $query;
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$list[] = $li;
		}
		if (count($list) >= 0) {
			$transaccion_id = $list[0]["id"];

			$array_cliente = array();
			$array_cliente = api_calimaco_deposito($client_id, $cc_id, $monto, $transaccion_id);
			//echo json_encode($array_cliente);exit();
			if(isset($array_cliente["result"])){
				if($array_cliente["result"]==="OK") {
					$result["http_code"] = 200;
					$result["status"] = "ok";
					$result["operationId"] = $array_cliente["operationId"];
					$result["cod_transaccion"] = $transaccion_id;

					$update_command = "
						UPDATE tbl_saldo_web_transaccion
						SET
							txn_id = '" . $result["operationId"] . "', 
							status = '1', 
							updated_at = '" . date('Y-m-d H:i:s') . "' 
						WHERE 
							id='" . $transaccion_id . "' 
						";
					$mysqli->query($update_command);

				} elseif($array_cliente["result"]==="error") {
					$result["http_code"] = 400;
					if ($array_cliente["description"] === "Not privileged") {
						$result["status"] = "Cuenta no activa, el jugador debe contactarse con atención al cliente.";
					} else {
						$result["status"] = isset($array_cliente["description"]) ? $array_cliente["description"] : 'La API respondió un mensaje ilegible';
					}
					$result["result"] = $array_cliente;
				} else {
					$result["http_code"] = 400;
					$result["status"] = "La API respondio un error.";
					$result["result"] = $array_cliente;
				}
			} else {
				$result["http_code"] = 400;
				$result["status"] = "Ocurrió un error con el API, comunícate con soporte para confirmar la operación.";
			}
		} else {
			$result["http_code"] = 400;
			$result["status"] = "No se pudo registrar la transacción.";
		}
	}

}



if (isset($_POST["accion"]) && $_POST["accion"] === "realizar_deposito_reintento") {

	date_default_timezone_set("America/Lima");
	$date_time = date('Y-m-d H:i:s');

	$client_id = $_POST["id_web"];
	$cod_txn = $_POST["cod_txn"];


	if(!(strlen($client_id)>0)){
		$result["http_code"] = 400;
		$result["status"] = "Debe ingresar un ID-WEB válido.";
		$result["result"] = '';
		echo json_encode($result);exit();
	}

	$query = "
		SELECT
			t.id,
			t.cc_id,
			t.user_id,
			t.monto
		FROM
			tbl_saldo_web_transaccion t 
		WHERE
			t.tipo_id = '1' 
			AND t.client_id = '$client_id' 
			AND t.id = '$cod_txn' 
			AND t.status = '0' 
		ORDER BY
			t.id DESC 
		LIMIT 1
	";
	$list_query = $mysqli->query($query);
	$list = array();
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al registrar la transacción.";
		$result["query"] = $query;
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$list[] = $li;
		}
		if (count($list) > 0) {
			$transaccion_id = $list[0]["id"];
			//$cc_id = $list[0]["cc_id"];
			$monto = $list[0]["monto"]*100;

			if((int)$usuario_id !== (int)$list[0]["user_id"]){
				$result["http_code"] = 400;
				$result["status"] = "La transacción fue generada por otro usuario.";
				echo json_encode($result);exit();
			}
			if($cc_id !== $list[0]["cc_id"]){
				$result["http_code"] = 400;
				$result["status"] = "La transacción fue generada en otro local.";
				echo json_encode($result);exit();
			}

			$array_cliente = array();
			$array_cliente = api_calimaco_deposito($client_id, $cc_id, $monto, $transaccion_id);
			//echo json_encode($array_cliente);exit();
			if(isset($array_cliente["result"])){
				if($array_cliente["result"]==="OK") {
					$result["http_code"] = 200;
					$result["status"] = "ok";
					$result["operationId"] = $array_cliente["operationId"];
					if(isset($array_cliente["idempotence_operationId"])){
						$result["operationId"] = $array_cliente["idempotence_operationId"];
					}

					$update_command = "
						UPDATE tbl_saldo_web_transaccion
						SET
							txn_id = '" . $result["operationId"] . "', 
							status = '1', 
							update_user_id = '$usuario_id', 
							updated_at = '" . date('Y-m-d H:i:s') . "' 
						WHERE 
							id='" . $transaccion_id . "' 
						";
					$mysqli->query($update_command);

				} elseif($array_cliente["result"]==="error") {
					$result["http_code"] = 400;
					$result["status"] = isset($array_cliente["description"])?$array_cliente["description"]:'La API respondio un mensaje ilegible';
					$result["result"] = $array_cliente;
				} else {
					$result["http_code"] = 400;
					$result["status"] = "La API respondio un error.";
					$result["result"] = $array_cliente;
				}
			} else {
				$result["http_code"] = 400;
				$result["status"] = "Ocurrió un error, comunícate con soporte para confirmar la operación.";
			}
		} else {
			$result["http_code"] = 400;
			$result["status"] = "No se pudo registrar la transacción.";
		}
	}

}






//*******************************************************************************************************************
// CONSULTAR RETIRO
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "consultar_retiro") {

	$id_cliente = $_POST["id_web"];

	$array_cliente = array();
	$array_cliente = api_calimaco_consultar_retiro($id_cliente, $cc_id);
	//echo json_encode($array_cliente);exit();
	//to tests descomment next line and comment the nexts
	//$result["http_code"] = 200; $result["status"] = "ok"; $result["idTransaccion"] = "00000000"; $result["amount"] = "2000000000";
	if(isset($array_cliente["result"])){
		if($array_cliente["result"]==="OK") {
			$result["http_code"] = 200;
			$result["status"] = "ok";
			$result["idTransaccion"] = $array_cliente["IdTransacción"];
			$result["amount"] = $array_cliente["amount"];
		} else if($array_cliente["result"]==="error") {
			$result["http_code"] = 400;
			$result["status"] = $array_cliente["description"];
		} else {
			$result["http_code"] = 400;
			$result["status"] = "La api respondio un error. 2";
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] = "La api respondio un error.";
	}
}



//*******************************************************************************************************************
// REALIZAR RETIRO
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "realizar_retiro") {
    if(!in_array('cash_out',getPermisos($login))){
        $result["http_code"] = 400;
        $result["status"] = "No tiene permisos para realizar la operación, debe comunicarse con soporte.";
        $result["result"] = "";
        echo json_encode($result);exit();
    }
	date_default_timezone_set("America/Lima");
	$date_time = date('Y-m-d H:i:s');

	$client_id = $_POST["id_web"];
	$client_name = $_POST["client_name"];
	$client_num_doc = $_POST["num_doc"];
	$txn_id = $_POST["id_transaccion"];
	$scan_doc = $_POST["scan_doc"] ?? "3";
	$observacion = $_POST["observacion"] ?? null;
	if (!in_array($scan_doc,["1","2"])){
		$scan_doc = "3";
	}

	$local_id = $turno[0]["local_id"] ?? 0;
	$local_caja_id = $turno[0]["local_caja_id"] ?? 0;

	$insert_command = "
		INSERT INTO tbl_saldo_web_transaccion (
			tipo_id,
			client_id,
			client_num_doc,
			client_name,
			txn_id,
			status,
			cc_id,
			turno_id,
			user_id,
			created_at,
			scan_doc,
		    observacion_scan_doc,
			local_id,
		   	local_caja_id
		) VALUES (
			'2',
			'" . $client_id . "',
			'" . $client_num_doc . "',
			'" . $client_name . "',
			'" . $txn_id . "',
			'0',
			'" . $cc_id . "',
			'" . $turno_id . "',
			'" . $usuario_id . "',
			'" . $date_time . "',
			'" . $scan_doc . "',
			'" . $observacion . "',
			'" . $local_id . "',
			'" . $local_caja_id . "'
		)";
	$mysqli->query($insert_command);

	$query = "
		SELECT
			t.id 
		FROM
			tbl_saldo_web_transaccion t 
		WHERE
			t.tipo_id = '2' 
			AND t.client_id = '$client_id' 
			AND t.client_num_doc = '$client_num_doc' 
			AND t.client_name = '$client_name' 
			AND t.txn_id = '".$txn_id."' 
			AND t.status = 0 
			AND t.cc_id = '$cc_id' 
			AND t.turno_id = '$turno_id' 
			AND t.user_id = '$usuario_id' 
			AND t.created_at = '$date_time' 
		ORDER BY
			t.id DESC 
		LIMIT 1
	";
	$list_query = $mysqli->query($query);
	$list = array();
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al registrar la transacción.";
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$list[] = $li;
		}
		if (count($list) >= 0) {
			$transaccion_id = $list[0]["id"];

			$array_cliente = array();
			$array_cliente = api_calimaco_realizar_retiro($client_id, $client_num_doc, $cc_id, $txn_id);
			//echo json_encode($array_cliente);exit();
			if(isset($array_cliente["result"])){
				if($array_cliente["result"]==="OK") {
					$result["http_code"] = 200;
					$result["status"] = "ok";
					$result["fechaPago"] = $array_cliente["fechaPago"];
					$result["amount"] = $array_cliente["amount"];
					$result["cod_transaccion"] = $transaccion_id;

					$update_command = "
						UPDATE tbl_saldo_web_transaccion
						SET
							monto = '" . ($result["amount"]/100) . "', 
							status = '1', 
							updated_at = '" . date('Y-m-d H:i:s') . "' 
						WHERE 
							id='" . $transaccion_id . "' 
						";
					$mysqli->query($update_command);

				} else if($array_cliente["result"]==="error") {
					$result["http_code"] = 400;
					$result["status"] = $array_cliente["description"];
				} else {
					$result["http_code"] = 400;
					$result["status"] = "La api respondio un error. 2";
				}
			} else {
				$result["http_code"] = 400;
				$result["status"] = "La api respondio un error.";
			}

		} else {
			$result["http_code"] = 400;
			$result["status"] = "No se pudo registrar la transacción.";
		}
	}

}


//*****************************
////SOLICITAR EXTORNO
if (isset($_POST["accion"]) && $_POST["accion"] === "solicitar_extorno") {
	date_default_timezone_set("America/Lima");
	$date_time = date('Y-m-d H:i:s');
	extract($_POST["solicitar_extorno"]);
	$command = "SELECT
            created_at
            FROM wwwapuestatotal_gestion.tbl_saldo_web_transaccion
            WHERE id = $id_saldo_web
            "
        ;
    $registro = $mysqli->query($command)->fetch_assoc();
	$now = strtotime("-10 minutes");
	if ($now > strtotime($registro["created_at"])){
		$return["error"] = "time";
		$return["error_msg"] = "Ya pasaron más de 10 minutos";
		die(json_encode($return));
	}
	if($motivo == "")
	{
		$return["error"] = "motivo";
		$return["error_msg"] = "Ingresar Motivo";
		$return["error_focus"] = "motivo";
		die(json_encode($return));
	}
	if(strlen($motivo) > 50)
	{
		$return["error"] = "motivo";
		$return["error_msg"] = "Motivo Max. 50 caracteres";
		$return["error_focus"] = "motivo";
		die(json_encode($return));
	}
	$usuario_id = $login["id"];
	$insert_command = "INSERT INTO `wwwapuestatotal_gestion`.`tbl_extorno`
			(
				`id_saldo_web`,
				`motivo`,
				`usuario_id`,
				`estado_extorno_id`,
				`created_at`,
				`updated_at`
			)
			VALUES
			(
				$id_saldo_web,
				'$motivo',
				$usuario_id,
				1,
				'$date_time',
				'$date_time'
			)";
	$mysqli->query($insert_command);

	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["error"] = $mysqli->error;
		$result["status"] = "Ocurrió un error al registrar la transacción.";
	}
	else
	{
		$result["insertado"] = true;
	}
}
//****************************



//*******************************************************************************************************************
// ANULAR TRANSACCION
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "anular_transaccion") {

	$tipo_id = $_POST["tipo_id"];
	$txn_id  = $_POST["txn_id"];

	$query = "
		SELECT 
		  m.id, 
		  p.id, 
		  b.id 
		FROM 
		  tbl_menu_sistemas m 
		  JOIN tbl_permisos p on p.menu_id = m.id 
		  	and p.estado = 1 
		  JOIN tbl_botones b ON b.id = p.boton_id 
		  	and b.estado = 1 
		  	and b.boton = 'delete' 
		WHERE 
		  m.sec_id = 'saldo_web' 
		  and p.usuario_id = $usuario_id 
		LIMIT 
		  1
		";
	$list_query = $mysqli->query($query);
	$list = array();
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["status"]    = "Ocurrió un error al consultar las transacciones.";
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$list[] = $li;
		}
		if (count($list) >= 0) {
			$query = "
				UPDATE tbl_saldo_web_transaccion 
				SET status = 2, 
					update_user_id = '$usuario_id', 
					updated_at = '" . date('Y-m-d H:i:s') . "' 
				WHERE tipo_id = '$tipo_id' 
				AND id = '$txn_id' 
				AND status IN (0,1) 
			";
			$mysqli->query($query);

			$result["http_code"] = 200;
			$result["status"] = "Ok";
		} else {
			$result["http_code"] = 400;
			$result["status"]    = "No tiene permisos para esta acción.";
		}
	}
}







//*******************************************************************************************************************
// OBTENER TRANSACCIONES
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_cliente_x_transacciones") {

	$id_cliente = $_POST["id_web"];
	$cargo_id   = $login ? $login['cargo_id'] : 0;
	$area_id    = $login ? $login['area_id'] : 0;

	$where_cajero = "";
	$where_fecha  = "";
	if((int)$area_id !== 6) {// diferente de sistemas
		if( (int) $cargo_id === 5 ){ // Si es cajero
			$where_cajero = " AND t.user_id = '".$usuario_id."' ";
			$where_fecha  = " AND TIMESTAMPDIFF(HOUR,t.created_at,now()) <= 24 ";
		}
	}


	$query = "
		SELECT
			t.id cod_transaccion,
			t.tipo_id,
			( CASE t.tipo_id WHEN 1 THEN 'Depósito' WHEN 2 THEN 'Retiro' WHEN 3 THEN 'Extorno' ELSE 'Otros' END ) tipo,
			IFNULL(t.txn_id, '') txn_id,
			IFNULL(t.monto, 0) monto,
			( CASE t.status WHEN 0 THEN 'Fallido' WHEN 1 THEN 'Completado' WHEN 2 THEN 'Anulado' ELSE '' END ) status,
			t.created_at registro,
			u.id cod_usuario,
			u.usuario ,
			(SELECT e.id FROM tbl_extorno e WHERE e.id_saldo_web = t.id) as id_extorno,
			u.usuario,
			( CASE t.scan_doc WHEN 1 THEN 'ESCANEADO' WHEN 2 THEN 'MANUAL' ELSE 'MANUAL' END ) scan_doc
		FROM
			tbl_saldo_web_transaccion t
			LEFT JOIN tbl_usuarios u ON u.id = t.user_id 
		WHERE
			t.`status` IN (0,1,2) 
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
		$result["status"]    = "Ocurrió un error al consultar las transacciones.";
		$result["result"]    = $mysqli->error;
		$result["query"]     = $query;
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$list[] = $li;
		}
		if (count($list) >= 0) {
			$result["http_code"] = 200;
			$result["status"]    = "ok";
			$result["result"]    = $list;
		} else {
			$result["http_code"] = 400;
			$result["status"]    = "Ocurrió un error al consultar las transacciones.";
		}
	}
}











echo json_encode($result);

function fncValidateLimits($cc_id,$idWeb,$amountValue){

	try {
		$depositAmount = $amountValue;
		$result=[];
		$local_id = getLocalIdfromCCid($cc_id);
		$monto_limite_global_local = 0;
		$monto_limite_global_cliente = 0;

		$limitTmp  	 = fncSelectQueryLimits(prepareQueryGetDataLimitsLocal($local_id));
		$limitTmpCliente = fncSelectQueryLimits(prepareQueryGetDataLimitsCliente($idWeb));
		$amountsTmp  = fncSelectQueryLimits(prepareQueryGetDataAmounts($cc_id,$idWeb));
		$limit =[];
		$limitCliente =[];
		$amount = [];
		if(count($limitTmp["data"]) > 0){
			foreach ($limitTmp["data"] as $key => $value) {
				$limit[$value["local_id"]]=($value["limit_amount"]*100);
			}
		} else {
			$limite_global_local = getLimite('local_global', null);
			$monto_limite_global_local = $limite_global_local['limite']*100 ;
		}

		if(count($limitTmpCliente["data"]) > 0 ){
			foreach ($limitTmpCliente["data"] as $key => $value) {
				$limitCliente[$value["idweb"]]=($value["limit_amount"]*100);
			}
		} else {
			$limite_global_cliente = getLimite('cliente_global', null);
			$monto_limite_global_cliente = $limite_global_cliente['limite']*100 ;
		}

		$dataLimits=[
			'client'=>(isset($limitCliente[$idWeb]))?$limitCliente[$idWeb] : $monto_limite_global_cliente,
			'local'=>(isset($limit[$local_id]))?$limit[$local_id] : $monto_limite_global_local
		];

		foreach ($amountsTmp["data"] as $key => $value) {
			$amount[$value["type"]]=($value["amount"]*100);
		}
		$dataAmounts=[
			'client'=>(isset($amount["client"]))?$amount["client"]:0,
			'local'=>(isset($amount["local"]))?$amount["local"]:0
		];

		$afterLocalDeposit = $dataAmounts["local"];
		$beforeLocalDeposit = $afterLocalDeposit + $depositAmount;
		if ($beforeLocalDeposit > $dataLimits['local']) {
			$result["http_code"] = 400;
			$result["status"] = "Monto de depósito por local excedido";
			$result["result"] = '';
			return $result;
		}

		$afterClientDeposit = $dataAmounts["client"];
		$beforeClientDeposit = $afterClientDeposit + $depositAmount;
		if ($beforeClientDeposit > $dataLimits['client']) {
			$result["http_code"] = 400;
			$result["status"] = "Monto de depósito por cliente excedido";
			$result["result"] = '';
			return $result;
		}			

		$result["http_code"] = 200;
		return $result;

	} catch (\Throwable $th) {
				$result["http_code"] = 200;
				return $result;
	}
	
}
function prepareQueryGetDataAmounts($cc_id,$idWeb){
 $query = " SELECT
				'client' type,
				sum(monto) amount
			from
				tbl_saldo_web_transaccion tw
			WHERE
				tw.client_id = $idWeb
				and tw.tipo_id=1
				and tw.status = 1
				and tw.created_at >= CURRENT_DATE()
				AND tw.created_at < DATE_ADD(CURRENT_DATE(), INTERVAL 1 DAY)
			GROUP BY
				client_id
			UNION ALL 
			select
				'local' type,
				sum(monto) amount
			from
				tbl_saldo_web_transaccion tw
			WHERE
				tw.cc_id = '{$cc_id}'
				and tw.tipo_id=1
				and tw.status = 1
				and tw.created_at >= CURRENT_DATE()
				AND tw.created_at < DATE_ADD(CURRENT_DATE(), INTERVAL 1 DAY)
			GROUP BY
				cc_id
 ";
 return $query;
}
function prepareQueryGetDataLimitsLocal($local_id){
	$query="	SELECT 
					l.id,
					l.item_id as 'local_id',
					l.limite as limit_amount
				from tbl_saldo_web_limites l
				inner join tbl_limites_tipos t on t.id = l.tipo_limite
				where 
					t.tipo = 'local' 
					and l.item_id = $local_id
					and l.estado = 1
				ORDER BY l.id DESC
			";
	return $query;
}

function prepareQueryGetDataLimitsCliente($idweb){
	$query="	SELECT 
					l.id,
					l.item_id as 'idweb',
					l.limite as limit_amount
				from tbl_saldo_web_limites l
				inner join tbl_limites_tipos t on t.id = l.tipo_limite
				where 
					t.tipo = 'cliente' 
					and l.item_id = $idweb
					and l.estado = 1
				ORDER BY l.id DESC
			";
	return $query;
}

function prepareQueryGetDataLimits($cc_id){
	$query="
	select
		t.id ,
		t.x_cc_id ,
		t.limit_amount
	from
		tbl_web_deposit_limits t
	where
		t.x_cc_id = '{$cc_id}'
	";
	return $query;
}

function fncSelectQueryLimits($query)
    {
		global $mysqli;
        $result = $mysqli->query($query);
        if ($result) {
            $data = [];
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            $result->free();
            return [
                'error' => false,
                'data' => $data
            ];
        } else {
            return [
                'error' => $mysqli->error,
                'query' => $query,
                'error_code' => $mysqli->errno,
                'error_message' => $mysqli->error
            ];
        }
    }
?>
