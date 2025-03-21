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
	$validate_turno[] = "obtener_cliente_tls";
	$validate_turno[] = "obtener_transaccion";
	$validate_turno[] = "realizar_deposito";
	$validate_turno[] = "consultar_retiro";
	$validate_turno[] = "realizar_retiro";
	$validate_turno[] = "anular_transaccion";
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


function api_calimaco_checkUser($id_cliente){
	$msj = $id_cliente;
	$clavesecreta = env('CALIMACO_RETAIL_PASSWORD');
	$hash_encryp = hash_hmac('sha256', $msj, $clavesecreta, false);

	$url = env('CALIMACO_RETAIL_URL')."checkUser";
	$url .= "&user=" . $id_cliente .  "&hash=" . $hash_encryp;

	$auditoria_id = api_calimaco_auditoria_inset('checkUser', $id_cliente, $url);

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
		//api_calimaco_auditoria_inset('checkUser', $id_cliente, 0, 0, $url, $response, $status);
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

 

//*******************************************************************************************************************
// OBTENER CLIENTE
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_cliente_tls") {

	$busqueda_tipo  = $_POST["tipo_doc"];
    $busqueda_valor = $_POST["num_doc"];
	$where = "tipo_doc='" . $busqueda_tipo . "' AND num_doc='" . $busqueda_valor . "'";

	$query_1 = "
			SELECT 
				c.id,
				IFNULL(c.nombre, '') as nombre,
				IFNULL(c.apellido_paterno, '') as apellido_paterno,
				IFNULL(c.web_id, '') as web_id
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
	//echo json_encode($array_cliente);exit();
	if (count($list_1) == 0){
		$result["http_code"] = 400;
		$result["status"] ="El número de cliente no existe.";
	
	} else {
		$nombre = explode(" ", $list_1[0]['nombre']);
		$nombre_apellido=$nombre[0].' '.$list_1[0]['apellido_paterno'];
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $nombre_apellido;
		$result["id"] = $list_1[0]['id'];
		$result["web_id"] = $list_1[0]['web_id'];
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
			$transaccion_id = $data['txn_id'];
			$tipo_id = $data['tipo_id'];
		} else {
			$transaccion_id = $_POST["txn_id"];
			$tipo_id = $_POST["tipo_id"];
		}
	} else {
		$data = $_POST['obtener_transaccion'];
		$transaccion_id = $data['txn_id'];
		$tipo_id = $data['tipo_id'];
	}

	$query ="
			SELECT
				stt.txn_id,
				stt.cc_id,
				stt.created_at,
				IFNULL(stt.client_id, '') client_id,
				IFNULL(stt.client_num_doc, '') client_num_doc,			 
				IFNULL(stt.client_name, '') client_name,
				stt.monto,
				IFNULL(l.nombre, '') local_nombre,
				IFNULL(l.direccion, '') local_direccion
			FROM
				tbl_saldo_teleservicios_transaccion stt
			LEFT JOIN
				tbl_locales l ON l.cc_id = stt.cc_id				 
			WHERE
				stt.tipo_id = '" . $tipo_id . "' 
				AND stt.txn_id = '" . $transaccion_id . "' 
				
		";

		//AND tra.status = 1
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

	$id_cliente = $_POST["id_cli"];
	$id_web = $_POST["webid"];
	$tipo_doc = $_POST["tipo_doc"];
	$num_doc = $_POST["num_doc"];
	$client_name = $_POST["client_name"];
	$id_cajero = $_POST["id_cajero"];
	$monto = (float)$_POST["monto"]*100;
	$local_id = $turno[0]["local_id"] ?? 0;
	$local_caja_id = $turno[0]["local_caja_id"] ?? 0;
	$cc_id = $_POST["cc_id"];

	$query_loc = "
		SELECT
			l.nombre
		FROM
			tbl_locales l
		WHERE
			l.id = $local_id  
		";
	$list_query_loc = $mysqli->query($query_loc);
	while ($loc = $list_query_loc->fetch_assoc()) { 
		$local_nombre = $loc['nombre'];		 
	}

	if(!((float) $_POST["monto"] >= 1.00 && (float) $_POST["monto"] <= 3000.00 )){
		$result["http_code"] = 400;
		$result["status"] = "El monto debe ser mínimo de 1.00 y máximo de 3,000.00.";
		$result["result"] = '';
		echo json_encode($result);exit();
	}	

	if(!(strlen($num_doc)>0)){
		$result["http_code"] = 400;
		$result["status"] = "Debe ingresar un Documento válido.";
		$result["result"] = '';
		echo json_encode($result);exit();
	}

	//$validateAmounts = fncValidateLimits($cc_id,$client_id,$monto);
	//if(isset($validateAmounts["http_code"]) && $validateAmounts["http_code"]==400){
	//	echo json_encode($validateAmounts);exit();
//	}

	$list_balance = array();
	$list_balance = obtener_balances($id_cliente);
	$monto_deposito = ($monto/100);


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
				turno_id,
				cc_id,
				web_id,				
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
				created_at,
				update_user_at
			) VALUES (
				1,
				'" . $id_cliente . "',			 
				'" . $turno_id . "',  
				'" . $cc_id . "',  
				'" . $id_web . "', 			
				'0',
				'" . $monto_deposito . "', 
				'0',
				'" . $monto_deposito . "',
				'0',
				'" . $monto_deposito . "',
				'" . $balance_actual . "',
				'1',
				'Retail - [" .$local_nombre. "]', 
				'3',
				'" . $usuario_id . "', 
				now(),
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
					turno_id,
					cc_id,
					web_id,					
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
					created_at,
					update_user_at
				) VALUES (
					26,
					'" . $id_cliente . "',				
					'" . $turno_id . "',
					'" . $cc_id . "',
					'" . $id_web . "',					
					'0',
					'" . $monto_deposito . "',
					'0',
					'" . $monto_deposito . "',
					'0',
					'" . $monto_deposito . "',
					'" . $nuevo_balance . "',
					'" . $transaccion_id . "',
					'1',
					'Retail - [" .$local_nombre. "]', 
					'3',
					'" . $usuario_id . "',
					now(),
					now()
				)
				";
			$mysqli->query($insert_command);

			$insert_saldo_tls = " 
					INSERT INTO tbl_saldo_teleservicios_transaccion (
						tipo_id,						
						client_id,
						client_tipo_doc,
						client_num_doc,		
						client_name,
						txn_id,					
						monto,
						status,
						cc_id,
						turno_id,
						user_id,
						created_at,
						update_user_id,
						updated_at,
						local_id,
						local_caja_id
					) VALUES (
						1,
						'" . $id_cliente . "',				
						'" . $tipo_doc . "',
						'" . $num_doc . "',
						'" . $client_name . "',
						'" . $transaccion_id . "',			
						'" . $monto_deposito . "',
						'1',
						'" . $cc_id . "',
						'" . $turno_id . "',
						'" . $usuario_id . "',
						now(),
						'" . $usuario_id . "',
						now(),						
						'" . $local_id . "',
						'" . $local_caja_id . "'						
					)
					";
				$mysqli->query($insert_saldo_tls);

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

				query_tbl_saldo_tls_balance('update', $id_cliente, 1, $nuevo_balance);
				query_tbl_saldo_tls_balance('update', $id_cliente, 4, $nuevo_balance_deposito);

				query_tbl_saldo_tls_balance_transaccion('insert', $transaccion_id_aprobacion, $id_cliente, 1, 
					$balance_actual, $monto_deposito, $nuevo_balance);
					query_tbl_saldo_tls_balance_transaccion('insert', $transaccion_id_aprobacion, $id_cliente, 4, 
					$balance_deposito, $monto_deposito, $nuevo_balance_deposito);
			 
				$result["http_code"] = 200;
				$result["status"] = "ok";
				$result["result"] = "Solicitud de Depósito Registrada";
			}
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar el balance.";
		$result["result"] = $list_balance;
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
				query_tbl_saldo_tls_balance('insert', $cliente_id, 1, 0);
				$list_balance[0]["balance"] = number_format(0, 2, '.', '');
			}
			if((float)$list_balance[0]["balance_bono_disponible"]<-9999999) {
				query_tbl_saldo_tls_balance('insert', $cliente_id, 2, 0);
				$list_balance[0]["balance_bono_disponible"] = number_format(0, 2, '.', '');
			}
			if((float)$list_balance[0]["balance_bono_utilizado"]<-9999999) {
				query_tbl_saldo_tls_balance('insert', $cliente_id, 3, 0);
				$list_balance[0]["balance_bono_utilizado"] = number_format(0, 2, '.', '');
			}
			if((float)$list_balance[0]["balance_deposito"]<-9999999) {
				query_tbl_saldo_tls_balance('insert', $cliente_id, 4, 0);
				$list_balance[0]["balance_deposito"] = number_format(0, 2, '.', '');
			}
			if((float)$list_balance[0]["balance_retiro_disponible"]<-9999999) {
				query_tbl_saldo_tls_balance('insert', $cliente_id, 5, 0);
				$list_balance[0]["balance_retiro_disponible"] = number_format(0, 2, '.', '');
			}
			if((float)$list_balance[0]["balance_dinero_at"]<-9999999) {
				query_tbl_saldo_tls_balance('insert', $cliente_id, 6, 0);
				$list_balance[0]["balance_dinero_at"] = number_format(0, 2, '.', '');
			}
		}
	}
	return $list_balance;
}

function query_tbl_saldo_tls_balance_transaccion($action, $transaccion_id, $cliente_id, $tipo_balance_id, $balance_actual, $monto, $balance_nuevo){
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


function query_tbl_saldo_tls_balance($action, $cliente_id, $tipo_id, $balance){
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


//*******************************************************************************************************************
// CONSULTAR RETIRO
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "consultar_retiro") {

	$id_cliente = $_POST["id_cli"];

	$query_loc = "
		SELECT
			l.nombre
		FROM
			tbl_locales l
		WHERE
			l.cc_id = $cc_id  
		";
	$list_query_loc = $mysqli->query($query_loc);
	while ($loc = $list_query_loc->fetch_assoc()) { 
		$local_nombre = $loc['nombre'];		 
	}
 
	$query_solic_ret = "
	SELECT
		t.id,
		t.created_at
	FROM 
		tbl_televentas_clientes_transaccion t
	WHERE
		(t.cc_id = '$cc_id' or t.cc_id is null) 
		AND t.cliente_id = '$id_cliente' 
		AND t.estado in (1,5) 
		AND t.caja_vip = '3' 
		AND t.tipo_id = '9'
	group by t.id
	ORDER BY
		t.id DESC
	";
	$list_query_solic_ret = $mysqli->query($query_solic_ret);
	$list_solic_ret = array();
					
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al registrar la transacción.";
	} else {
		while ($li_solic_ret = $list_query_solic_ret->fetch_assoc()) {
			$list_solic_ret[] = $li_solic_ret;
		}
		if (count($list_solic_ret) > 0) {
			 
			$result["http_code"] = 200;
			$result["status"] = "Tiene solicitudes pendientes";
			$result["data"] = $list_solic_ret;
			$result["name"] = $local_nombre;
		} else {
			$result["http_code"] = 400;
			$result["status"] = "No hay solicitudes pendientes";
		}
	}
}

//*******************************************************************************************************************
// UPDATE RETIRO SIN CONCLUIR
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "update_solic_retiro") {

	$id = $_POST["id_trans"];
	$solicitud = $_POST["solicitud"];
	
	if($solicitud==1){

		$query_solic_ret = "
		SELECT
			t.id,
			t.monto,
			t.estado
		FROM 
			tbl_televentas_clientes_transaccion t 		
			
		WHERE
			t.id = '$id' 
		";
		$list_query_solic_ret = $mysqli->query($query_solic_ret);
		$list_solic_ret = array();
						
		if ($mysqli->error) {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al registrar la transacción.";
		} else {
			while ($li_solic_ret = $list_query_solic_ret->fetch_assoc()) {
				$list_solic_ret[] = $li_solic_ret;
			}
		
			if ($list_solic_ret[0]['estado'] == 1 || $list_solic_ret[0]['estado'] == 5) {

				$query_status_solic = "
				UPDATE tbl_televentas_clientes_transaccion 
					SET estado = 1, 
					update_user_id = '$usuario_id', 
					updated_at = now(), 
					update_user_at = now()
				WHERE id = '$id'
				";
				$mysqli->query($query_status_solic);
				
				$result["http_code"] = 200;
				$result["status"] = "Solicitud actualizada";
				$result["data"] = $list_solic_ret[0]['monto'];
			} else {

				if ($list_solic_ret[0]['estado'] == 4){
					$result["http_code"] = 400;
					$result["status"] = "La solicitud fue cancelada";
				}
				else{
					$result["http_code"] = 400;
					$result["status"] = "La solicitud ya fue atendida";
				}

			}
		}

	}else{
		$result["http_code"] = 400;
		$result["status"] = "Sin solicitud pendiente";
	}

	
}


//*******************************************************************************************************************
// CONSULTAR RETIRO
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "consultar_solic_retiro") {

	$id = $_POST["id"];

	$query_solic_ret = "
	SELECT
		t.id,
		t.monto,
		t.nuevo_balance,
		t.estado
	FROM 
		tbl_televentas_clientes_transaccion t 		
		 
	WHERE
		t.id = '$id'
	";
	$list_query_solic_ret = $mysqli->query($query_solic_ret);
	$list_solic_ret = array();
					
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al registrar la transacción.";
	} else {
		while ($li_solic_ret = $list_query_solic_ret->fetch_assoc()) {
			$list_solic_ret[] = $li_solic_ret;
		}

		if ($list_solic_ret[0]['estado'] == 1 || $list_solic_ret[0]['estado'] == 5) {

			$query_status_solic = "
			UPDATE tbl_televentas_clientes_transaccion 
				SET estado = 5, 
				update_user_id = '$usuario_id', 
				updated_at = now(), 
				update_user_at = now()
			WHERE id = '$id'
			";
			$mysqli->query($query_status_solic);
			 
			$result["http_code"] = 200;
			$result["status"] = "Tiene solicitudes pendientes";
			$result["data"] = $list_solic_ret[0]['monto'];
			$result["nuevo_balance"] = $list_solic_ret[0]['nuevo_balance'];
		} else {

			if ($list_solic_ret[0]['estado'] == 4){
				$result["http_code"] = 400;
				$result["status"] = "La solicitud fue cancelada";
			}
			else{
				$result["http_code"] = 400;
				$result["status"] = "La solicitud ya fue atendida";
			}
			
		}
	}
}

//*******************************************************************************************************************
// VERIFICAR CANTIDAD DE RETIROS
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "verificar_cantidad_retiro_diario") {

	$id_cli = $_POST["id_cli"];
	$monto = $_POST["monto"];
	
	$query = "
		SELECT
			SUM(st.monto) monto
		FROM 
			tbl_saldo_teleservicios_transaccion st 		
		WHERE
			st.client_id = '$id_cli'
			AND DATE(created_at) = DATE(now())
			AND tipo_id = 2
			AND solicitud = 0
	";
		$list_query_solic_ret = $mysqli->query($query);
		$list_solic_ret = array();
						
		if ($mysqli->error) {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al registrar la transacción.";
		} else {
			while ($li_solic_ret = $list_query_solic_ret->fetch_assoc()) {
				$list_solic_ret[] = $li_solic_ret;
			}
		
			if ($list_solic_ret[0]['monto'] >= 100) {

				$result["http_code"] = 400;
				$result["status"] = "Ya ha registrado el monto limite diario de retiro.";
				$result["monto"] = $list_solic_ret[0]['monto'];

			} else {

				$monto_nuevo = $list_solic_ret[0]['monto'] + $monto;

				if ($monto_nuevo > 100) {

					$diferencia = $monto_nuevo - 100;

					$result["http_code"] = 300;
					$result["status"] = "El monto ingresado supera el limite diario de retiro.";
					$result["monto"] = $monto_nuevo;
	
				}else{
					$result["http_code"] = 200;
					$result["status"] = "Monto validado";
					$result["monto"] = $list_solic_ret[0]['monto'];
				}

			}
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

	$id_cliente = $_POST["id_cli"];
	$num_doc = $_POST["num_doc"];
	$tipo_doc = $_POST["tipo_doc"];
	$client_name = $_POST["client_name"];
	$web_id = $_POST["web_id"];
	$client_num_doc = $_POST["num_doc_ing"];
	$monto_solicitud = $_POST["monto"];
	$solicitud = $_POST["solicitud"];
	$transaccion_id_nuevo = $_POST["id_transaccion"];
	$nuevo_balance = $_POST["nuevo_balance"];
	$scan_doc = $_POST["scan_doc"] ?? "3";
	$observacion = $_POST["observacion"] ?? null;
	if (!in_array($scan_doc,["1","2"])){
		$scan_doc = "3";
	}

	$local_id = $turno[0]["local_id"] ?? 0;
	$local_caja_id = $turno[0]["local_caja_id"] ?? 0;

	$query_loc = "
		SELECT
			l.nombre
		FROM
			tbl_locales l
		WHERE
			l.id = $local_id  
		";
	$list_query_loc = $mysqli->query($query_loc);
	while ($loc = $list_query_loc->fetch_assoc()) { 
		$local_nombre = $loc['nombre'];		 
	}


	if (!((float) $monto_solicitud > 0)) {
		$result["http_code"] = 400;
		$result["status"] = "Monto incorrecto.";
		$result["monto_deposito"] = ((float) $monto_deposito > 0) ? true : false;
		echo json_encode($result);exit();
	}

	if ($num_doc <> $client_num_doc) {
		$result["http_code"] = 400;
		$result["status"] = "El DNI ingresado no corresponde al cliente";	 
		echo json_encode($result);exit();
	}

	if($transaccion_id_nuevo == ''){

		//********* VALIDACION BALANCES

		$list_balance = array();
		$list_balance = obtener_balances($id_cliente);


		if (count($list_balance) == 0) {
			$result["http_code"] = 400;
			$result["status"] = "Usted no tiene balance.";
			$result["result"] = $list_balance;
		} elseif (count($list_balance) > 0) {

			$balance_total = $list_balance[0]["balance"];
			$balance_deposito = $list_balance[0]["balance_deposito"];
			$balance_disponible_retiro = $list_balance[0]["balance_retiro_disponible"];

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

			
			//********* VALIDACION BALANCE
		

		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al consultar el balance.";
			$result["result"] = $list_balance;
		}

		// Insertar solicitud
		$insert_solic = " 
			INSERT INTO tbl_televentas_clientes_transaccion (
				tipo_id,
				cliente_id,
				cuenta_id,
				turno_id,
				cc_id,
				web_id,				
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
				update_user_at,
				cuenta_pago_id,
				id_operacion_retiro,
				tipo_operacion,
				id_motivo_dev,
				user_valid_id,
				caja_vip
			) VALUES (
				'9',
				'". $id_cliente ."',
				'0',
				'". $turno_id ."',
				'". $cc_id ."',
				'". $web_id ."',					
				'". $date_time ."',
				'0',
				'". $monto_solicitud ."',
				'0',
				'". $monto_solicitud ."',
				'0',
				'0',
				'". $nuevo_balance ."',
				'0',
				'2',
				'". $observacion ."',
				'Retail - [" .$local_nombre. "]', 
				'0',
				'". $usuario_id ."',
				now(),
				now(),
				'0',
				'2',
				'1',
				'0',
				'" . $usuario_id . "',
				3
			) 
			";
		$mysqli->query($insert_solic);
		$error = '';
		if ($mysqli->error) {
			$result["insert_error"] = $mysqli->error;
			$error = $mysqli->error;
		}
	
	
		$query_verif_solic = "SELECT id FROM tbl_televentas_clientes_transaccion  ";
		$query_verif_solic .= " WHERE tipo_id = 9 ";
		$query_verif_solic .= " AND cliente_id='" . $id_cliente . "'  ";
		$query_verif_solic .= " AND user_id='" . $usuario_id . "' ";
		$query_verif_solic .= " AND registro_deposito='" . $date_time . "' ";
		$query_verif_solic .= " AND turno_id='" . $turno_id . "' ";
		$query_verif_solic .= " AND monto='" . $monto_solicitud . "' ";	
		$query_verif_solic .= " AND nuevo_balance='" . $nuevo_balance . "' ";
		$query_verif_solic .= " ORDER BY id DESC ";
		$list_query_solic = $mysqli->query($query_verif_solic);
		$list_transaccion_verifica = array();
		if ($mysqli->error) {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al consultar la transacción.";	
			$result["query_verifica"] = $query_verif_solic;
			$result["query_error"] = $mysqli->error;
		} else {
			while ($li_solic = $list_query_solic->fetch_assoc()) {
				$list_transaccion_verifica[] = $li_solic;
			}
			if (count($list_transaccion_verifica) === 0) {
				$result["http_code"] = 400;
				$result["status"] = "No se guardó la transacción solicitud.";								
			} elseif (count($list_transaccion_verifica) === 1) {
				$transaccion_id_nuevo = $list_transaccion_verifica[0]["id"];
				query_tbl_saldo_tls_balance('update', $id_cliente, 1, $nuevo_balance);
				query_tbl_saldo_tls_balance('update', $id_cliente, 5, $nuevo_balance_retiro);

				query_tbl_saldo_tls_balance_transaccion('insert', $transaccion_id_nuevo, 
				$id_cliente, 1, $balance_total, $monto_solicitud, $nuevo_balance);

				query_tbl_saldo_tls_balance_transaccion('insert', $transaccion_id_nuevo, 
				$id_cliente, 5, $balance_disponible_retiro, $monto_solicitud, $nuevo_balance_retiro);
			}
		}
	
	}

	$insert_aprob = " 
		INSERT INTO tbl_televentas_clientes_transaccion (
			tipo_id,
			cliente_id,
			cuenta_id,
			turno_id,
			cc_id,
			web_id,							
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
			update_user_at,
			cuenta_pago_id,
			id_operacion_retiro,
			tipo_operacion,
			id_motivo_dev,
			user_valid_id,
			caja_vip
		) VALUES (
			'11',
			'". $id_cliente ."',
			'0',
			'". $turno_id ."',
			'". $cc_id ."',
			'". $web_id ."',							
			'". $date_time ."',
			'0',
			'". $monto_solicitud ."',
			'0',
			'". $monto_solicitud ."',
			'0',
			'0',
			'". $nuevo_balance ."',
			'0',
			'2',
			'". $observacion ."',
			'Retail - [" .$local_nombre. "]', 
			'0',
			'". $usuario_id ."',
			'".$transaccion_id_nuevo."',
			now(),
			now(),
			'0',
			'2',
			'1',
			'0',
			'" . $usuario_id . "',
			3
		) 
		";
		$mysqli->query($insert_aprob);
		$error = '';
		if ($mysqli->error) {
		$result["insert_error"] = $mysqli->error;
		$error = $mysqli->error;
		}

		$query_verif_aprob = "SELECT id FROM tbl_televentas_clientes_transaccion  ";
		$query_verif_aprob .= " WHERE tipo_id = 11 ";
		$query_verif_aprob .= " AND cliente_id='" . $id_cliente . "'  ";
		$query_verif_aprob .= " AND user_id='" . $usuario_id . "' ";
		$query_verif_aprob .= " AND registro_deposito='" . $date_time . "' ";
		$query_verif_aprob .= " AND turno_id='" . $turno_id . "' ";
		$query_verif_aprob .= " AND monto='" . $monto_solicitud . "' ";				
		$query_verif_aprob .= " AND nuevo_balance='" . $nuevo_balance . "' ";						
		$query_verif_aprob .= " AND transaccion_id='" . $transaccion_id_nuevo . "' ";
		$query_verif_aprob .= " ORDER BY id DESC ";
		$list_query_aprob = $mysqli->query($query_verif_aprob);
		$list_trans_ver_aprob = array();

	if ($mysqli->error) {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al consultar la transacción.";	
			$result["query_verifica"] = $query_verif_aprob;
			$result["query_error"] = $mysqli->error;
	}else {
			while ($li_aprob = $list_query_aprob->fetch_assoc()) {
			$list_trans_ver_aprob[] = $li_aprob;
			}
			if (count($list_trans_ver_aprob) === 0) {
				$result["http_code"] = 400;
				$result["status"] = "No se guardó la transacción.";								
			} elseif (count($list_trans_ver_aprob) === 1) {
				$transaccion_id_aprobacion = $list_trans_ver_aprob[0]["id"];

				$query_status_solic = "
					UPDATE tbl_televentas_clientes_transaccion 
					SET estado = 2, 
						update_user_id = '$usuario_id', 
						updated_at = now(), 
						update_user_at = now()
					WHERE id = '$transaccion_id_nuevo'
				";
				$mysqli->query($query_status_solic);

				$insert_saldo_tls_ret = "
				INSERT INTO tbl_saldo_teleservicios_transaccion (
					tipo_id,						
					client_id,
					client_tipo_doc,
					client_num_doc,		
					client_name,
					txn_id,					
					monto,
					status,
					cc_id,
					turno_id,
					user_id,
					created_at,
					scan_doc,
					observacion_scan_doc,
					update_user_id,
					updated_at,
					local_id,
					local_caja_id,
					solicitud
				) VALUES (
					'2',
					'" . $id_cliente . "',
					'" . $tipo_doc . "',
					'" . $num_doc . "',
					'" . $client_name . "',
					'" . $transaccion_id_nuevo . "',
					'" . $monto_solicitud . "',
					'1',
					'" . $cc_id . "',
					'" . $turno_id . "',
					'" . $usuario_id . "',
					'" . $date_time . "',
					'" . $scan_doc . "',
					'" . $observacion . "',
					'" . $usuario_id . "',
					'" . $date_time . "',
					'" . $local_id . "',
					'" . $local_caja_id . "',
					'" . $solicitud . "'
				)";
				$mysqli->query($insert_saldo_tls_ret);

				$query_saldo_tls_ret = "
				SELECT
					t.id 
				FROM
					tbl_saldo_teleservicios_transaccion t 
				WHERE
					t.tipo_id = '2' 
				AND t.client_id = '$id_cliente' 
				AND t.client_num_doc = '$num_doc' 
				AND t.client_name = '$client_name' 
				AND t.txn_id = '".$transaccion_id_nuevo."' 
				AND t.status = 1 
				AND t.cc_id = '$cc_id' 
				AND t.turno_id = '$turno_id' 
				AND t.user_id = '$usuario_id' 
				AND t.created_at = '$date_time' 
				ORDER BY
					t.id DESC 
				LIMIT 1
				";
				$list_query_ret = $mysqli->query($query_saldo_tls_ret);
				$list_ret = array();
				
				if ($mysqli->error) {
					$result["http_code"] = 400;
					$result["status"] = "Ocurrió un error al registrar la transacción.";
				} else {
					while ($li_ret = $list_query_ret->fetch_assoc()) {
						$list_ret[] = $li_ret;
					}
					if (count($list_ret) > 0) {
						$transaccion_id = $transaccion_id_nuevo;
						$result["http_code"] = 200;
						$result["status"] = "ok";
						$result["cod_transaccion"] = $transaccion_id;

					}else {
						$result["http_code"] = 400;
						$result["status"] = "No se pudo registrar la transacción.";
					}
				}
						  
			}elseif (count($list_trans_ver_aprov) > 1) {
				$result["http_code"] = 400;
				$result["status"] = "Se duplicaron las transacciones, por favor informar a informática.";
			}else {
				$result["http_code"] = 400;
				$result["status"] = "Ocurrió un error al guardar la transacción.";	
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
	$id_cliente  = $_POST["client_id"];
	$id_cc  = $_POST["cc_id"];
	$observacion = 'Anulación RETAIL';

	$query_tra = "
	SELECT
		t.id
	FROM
		tbl_televentas_clientes_transaccion t
	WHERE
		t.transaccion_id = $txn_id  
	";

	$list_query_tra = $mysqli->query($query_tra);
		while ($tra = $list_query_tra->fetch_assoc()) { 
			$transacc = $tra['id'];		 
		}

	$tra_pro = rollback_transaccion($id_cliente, $transacc, $usuario_id, $turno_id, $observacion, 18, $id_cc);

	if($tra_pro["http_code"]==200){

		$query_per = "
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
		$list_query_per = $mysqli->query($query_per);
		$list_per = array();
		if ($mysqli->error) {
			$result["http_code"] = 400;
			$result["status"]    = "Ocurrió un error al consultar las transacciones.";
		} else {
			while ($li_per = $list_query_per->fetch_assoc()) {
				$list_per[] = $li_per;
			}
			if (count($list_per) >= 0) {
				$query = "
					UPDATE tbl_saldo_teleservicios_transaccion 
					SET status = 2, 
						update_user_id = '$usuario_id', 
						updated_at = '" . date('Y-m-d H:i:s') . "' 
					WHERE tipo_id = '$tipo_id' 
					AND txn_id = '$txn_id' 
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

	}else{
		$result["http_code"] = 400;
		$result["status"]    = $tra_pro["status"];
	}
	
}

function rollback_transaccion($cliente_id, $transaccion_id, $usuario_id, $turno_id, $observacion, $motivo_id, $id_cc){
	global $mysqli;

	date_default_timezone_set("America/Lima");
	$date_time = date('Y-m-d H:i:s');

	$respuesta = 0;

	$query_loc = "
		SELECT
			l.nombre
		FROM
			tbl_locales l
		WHERE
			l.cc_id = $id_cc 
		";
	$list_query_loc = $mysqli->query($query_loc);
	while ($loc = $list_query_loc->fetch_assoc()) { 
		$local_nombre = $loc['nombre'];		 
	}

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
					created_at,
					observacion_validador,
					caja_vip
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
					'" . $date_time . "',
					'Retail - [" .$local_nombre. "]', 
					3
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

					query_tbl_saldo_tls_balance('update', $cliente_id, $temp_tipo_balance_id, $temp_balance_nuevo);

					query_tbl_saldo_tls_balance_transaccion('insert', $rollback_transaccion_id, 
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







//*******************************************************************************************************************
// OBTENER TRANSACCIONES
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_cliente_x_transacciones") {

	$id_cliente = $_POST["id_cli"];
	$cargo_id   = $login ? $login['cargo_id'] : 0;
	$area_id    = $login ? $login['area_id'] : 0;
	$btn_anular = 0;
	$this_menu = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'saldo_tls' LIMIT 1")->fetch_assoc();
	$menu_id = $this_menu["id"];
	if (in_array("anular_dep_saldo_tls", $usuario_permisos[$menu_id])) {
        $btn_anular = 1;
    }else{
		$btn_anular = 0;
	}

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
				stt.created_at AS fecha_creacion,
				stt.client_id,
				stt.tipo_id,
				(CASE stt.tipo_id WHEN 1 THEN 'Depósito Aprobado' WHEN 2 THEN 'Retiro Pagado' END ) tipo_tra,
				IFNULL(stt.txn_id, '') transaccion_id,				 
				IFNULL(stt.monto, 0) monto,
				stt.status AS estado,
				usu.usuario AS usuario
			FROM
				wwwapuestatotal_gestion.tbl_saldo_teleservicios_transaccion stt
				LEFT JOIN wwwapuestatotal_gestion.tbl_usuarios usu ON usu.id = stt.user_id
				LEFT JOIN wwwapuestatotal_gestion.tbl_personal_apt per ON per.id = usu.personal_id
			 
			WHERE 
			stt.client_id= '$id_cliente' 
		ORDER BY
			stt.id DESC 
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
			$result["btn_anular"] = $btn_anular;

		} else {
			$result["http_code"] = 400;
			$result["status"]    = "Ocurrió un error al consultar las transacciones.";
			$result["btn_anular"] = $btn_anular;
		}
	}
}











echo json_encode($result);

function fncValidateLimits($cc_id,$idWeb,$amountValue){

	try {
		$depositAmount = $amountValue;
		$result=[];
		$limitTmp  	 = fncSelectQueryLimits(prepareQueryGetDataLimits($cc_id));
		$amountsTmp  = fncSelectQueryLimits(prepareQueryGetDataAmounts($cc_id,$idWeb));
		$limit =[];
		$amount = [];
		foreach ($limitTmp["data"] as $key => $value) {
			$limit[$value["x_cc_id"]]=($value["limit_amount"]*100);
		}
		$dataLimits=[
			'client'=>5000*100,
			'local'=>(isset($limit[$cc_id]))?$limit[$cc_id]:20000*100
		];

		foreach ($amountsTmp["data"] as $key => $value) {
			$amount[$value["type"]]=($value["amount"]*100);
		}
		$dataAmounts=[
			'client'=>(isset($amount["client"]))?$amount["client"]:0,
			'local'=>(isset($amount["local"]))?$amount["local"]:0
		];

		if (!empty($amount)) {
			$afterLocalDeposit= $dataAmounts["local"];
			$beforeLocalDeposit= $dataAmounts["local"]+$depositAmount;
			if ($beforeLocalDeposit>$dataLimits['local']) {
				$result["http_code"] = 400;
				$result["status"] = "Monto de deposito por Local Excedido";
				$result["result"] = '';
				return $result;
			}

			$afterClientDeposit= $dataAmounts["client"];
			$beforeClientDeposit= $dataAmounts["client"]+$depositAmount;
			if ($beforeClientDeposit>$dataLimits['client']) {
				$result["http_code"] = 400;
				$result["status"] = "Monto de deposito por Cliente Excedido";
				$result["result"] = '';
				return $result;
			}			
		}
		$result["http_code"] = 200;
		return $result;

	} catch (\Throwable $th) {
				$result["http_code"] = 200;
				return $result;
	}
	
}
function prepareQueryGetDataAmounts($cc_id,$idWeb){
 $query = "
    select
	'client' type,
	sum(monto) amount
	from
	tbl_saldo_web_transaccion tw
	WHERE
	tw.client_id = $idWeb
	and tw.tipo_id=1
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
	and tw.created_at >= CURRENT_DATE()
	AND tw.created_at < DATE_ADD(CURRENT_DATE(), INTERVAL 1 DAY)
	GROUP BY
	cc_id
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
