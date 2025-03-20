<?php

function fncGetValidationCodeSmsLogin($user_id , $code){
	global $mysqli;	
	$hashUserValidation = md5($user_id);
	$selectQuery ="
	SELECT
	    tx.id,
	tx.codigo_verificacion,
	 IF(TIMESTAMPDIFF(SECOND, tx.created_at , CURRENT_TIMESTAMP()) > 120, 'time_out', 'time_in') AS verificacion
	FROM
		tbl_usuario_codigo_verificacion tx
	where
		tx.id_usuario = '".$hashUserValidation."'
		AND estado = 1
	ORDER BY
		created_at DESC
	limit 1
	";
	$user = $mysqli->query($selectQuery)->fetch_assoc();	 
	if(!empty($user)){
		$hashCode = hash('sha256',$user_id.$code);
		if($user["codigo_verificacion"]==$hashCode && $user["verificacion"]!="time_out"){
				return (int) $user['id'];
		}
		return 0;
	}	
	return 0;
}
function fncSendValidationCodeSmsLogin($user_id){
	fncSendValidationCodeSmsLoginCurl($user_id);	
}
function fncActiveValidationCodeSmsLogin(){
	global $mysqli; 
	$selet = "select tpg.valor from tbl_parametros_generales tpg  where tpg.codigo = '2doFactor_autenticacion' AND  tpg.estado = 1";
	$user = $mysqli->query($selet)->fetch_assoc();
	if(!empty($user)){
	 return true;
	}
	return false;
}

function fncCajeroValidationCodeSmsLogin($user_id){
	global $mysqli; 
	//$selet = "select usuario from tbl_usuarios where id=".$user_id." and grupo_id=11 ";
	$select =  "
	select 
		tu.usuario,
		tpa.cargo_id ,
		tpa.area_id  
	from tbl_usuarios tu
	inner join tbl_personal_apt tpa on tpa.id = tu.personal_id 
	where tu.id=".$user_id."
	and tu.grupo_id=11
	
	";
	$user = $mysqli->query($select)->fetch_assoc();
	if(!empty($user)){
	 if ($user["cargo_id"]==5&&$user["area_id"]==21) {
		return true;
	 }
	}
	return false;
}

function fncLocalValidationCodeSmsLogin($user_id){
	global $mysqli; 
	//$selet = "select usuario from tbl_usuarios where id=".$user_id." and grupo_id=11 ";
	$select =  "
	SELECT l.id as local_id,l.red_id  FROM tbl_usuarios_locales tul 
	LEFT JOIN tbl_locales l on l.id = tul.local_id 
	where 
	tul.usuario_id  = ".$user_id."
	and tul.estado = 1
	and l.operativo = 1	
	";
	$result = $mysqli->query($select);

	$userLocal = array();
	while ($row = $result->fetch_assoc()) {
		$userLocal[] = $row;
	}
	$validate = false;
	if(!empty($userLocal)){
	 $list_red_id = [1];
	 foreach ($userLocal as $key => $value) {		
		if (in_array($value["red_id"],$list_red_id)) {
			$validate = true;
			return $validate;
		}
		return $validate;
	 }
	}
	return $validate;
}

function fncSendValidationCodeSmsLoginCurl($user){
	
	require_once("/var/www/html/env.php");	

	$curl = curl_init();
    $url = env('V2_URL').'/web/second_authentication_step/send_sms';
	curl_setopt_array($curl, array(
	CURLOPT_URL => $url,
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => '',
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 0,
	CURLOPT_FOLLOWLOCATION => true,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => 'POST',
	CURLOPT_POSTFIELDS =>'{
		"user" : "'.$user.'"
	}',
	CURLOPT_HTTPHEADER => array(
		'Content-Type: application/json',
		'Authorization: Bearer '.env('V2_BEARER'),
		
	),
	));
	$response = curl_exec($curl);
	curl_close($curl);
	$validJson = jsonValidationReturn($response);
	if ($validJson&&!empty($response)) {
		$data = json_decode($response,true);
		if ($data["http_code"]!="200") {
			unset($_SESSION["tmp_code"]);
			header("Location: ./");
			exit();
		}
	}	
}

function jsonValidationReturn($cadena) {
    json_decode($cadena);
    return (json_last_error() === JSON_ERROR_NONE);
}

function fnc2FactoresValidation($user_id){
	global $mysqli; 
	$select_dni =  "
	select apt.dni from tbl_usuarios u
	left join tbl_personal_apt apt on apt.id = u.personal_id
	where u.id = {$user_id}
	and u.estado = 1
	and ((apt.area_id = 21 AND apt.cargo_id = 5) OR u.validacion_2fa = 1);";
	$result = $mysqli->query($select_dni);

	$userDni = array();
	while ($row = $result->fetch_assoc()) {
		$userDni[] = $row;
	}
	$validate = false;
	if(!empty($userDni)){
		$validate = true;
	}

	/*
	if(!empty($userDni) && $userDni[0]['dni'] !== null){
		$select_2_factores = "
		select dni, estado from tbl_2_factores
		where estado = 1 and dni = '".$userDni[0]['dni']."'
		";
		$result = $mysqli->query($select_2_factores);

		$dos_factores = array();
		while ($row = $result->fetch_assoc()) {
			$dos_factores[] = $row;
		}
		if(!empty($dos_factores)){
			$validate = true;
		}
	}
	*/
	return $validate;
}

function fncUpdatePasswordChanged($user_id){
	global $mysqli;

	$data_tiempo_caducidad = $mysqli->query("SELECT p.id, p.valor FROM tbl_parametros_generales p WHERE p.codigo = 'tiempo_caducidad_contraseña'")->fetch_assoc();
	if ($data_tiempo_caducidad) {
		if (isset($data_tiempo_caducidad['valor'])) {

			$data_user = $mysqli->query("SELECT p.ip, p.created_at FROM tbl_password_reset AS p 
										WHERE p.usuario_id = ".$user_id." ORDER BY p.created_at DESC LIMIT 1")->fetch_assoc();
			
			if (isset($data_user['created_at'])) {
				$fecha_caducidad = date("Y-m-d",strtotime($data_user['created_at']."+ ".$data_tiempo_caducidad['valor']." days"));
				$fecha_hoy = date('Y-m-d');

				if ($fecha_hoy >= $fecha_caducidad) {
					$query_update = "UPDATE tbl_usuarios SET password_changed = '0' WHERE id = ".$user_id;
					$mysqli->query($query_update);
				}
			}

		}
	}
}

?>