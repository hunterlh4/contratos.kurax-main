<?php
/*
    Consulta a la api de Calimaco
    INPUT: idweb, nombre del proceso para auditoria
    OUPUT: (array) info del usuario o vacío
 */
function api_calimaco_checkUser($id_cliente, $method_auditoria){
	$msj = $id_cliente;
	$clavesecreta = env('CALIMACO_RETAIL_PASSWORD');
	$hash_encryp = hash_hmac('sha256', $msj, $clavesecreta, false);

	$url = env('CALIMACO_RETAIL_URL')."checkUser";
	$url .= "&user=" . $id_cliente .  "&hash=" . $hash_encryp;

	$auditoria_id = api_calimaco_auditoria_inset($method_auditoria, $id_cliente, $url);

	$response = null;
	$result = array();
	$status = 0;

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
				if(isset($response_arr["result"])){
					$status = ($response_arr["result"]==="OK") ? 1 : 0;
				}
			}
		} catch (Exception $e) {
			$response = 'Excepción capturada: '.  $e->getMessage();
		}

		api_calimaco_auditoria_update($auditoria_id, 0, 0, $response, $status);

	}

	return $result;
}


function api_calimaco_auditoria_inset($method, $client_id, $body){
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

function api_calimaco_auditoria_update($auditoria_id, $txn_id, $amount, $response, $status){
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
