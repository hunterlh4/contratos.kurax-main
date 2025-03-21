<?php
date_default_timezone_set("America/Lima");

$result=array();
include("db_connect.php");
include("sys_login.php");

include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';

require_once '/var/www/html/env.php';
include '/var/www/html/sys/envio_correos.php';
include("function_replace_invalid_caracters_contratos.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



function send_email_cambio_marketing($auditoria_id)
{
	include("db_connect.php");
	include("sys_login.php");
	
	$host = $_SERVER["HTTP_HOST"];

	$sel_query = $mysqli->query("SELECT 
					a.nombre_tabla,
					a.valor_original,
					a.nombre_campo,
					a.nombre_menu_usuario,
					a.nombre_campo_usuario,
					a.tipo_valor,
					a.valor_varchar,
					a.valor_int,
					a.valor_date,
					a.valor_decimal,
					a.valor_select_option,
					a.valor_id_tabla,
					a.created_at AS fecha_del_cambio,
					CONCAT(IFNULL(p.nombre, ''), ' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS usuario_que_realizo_cambio,
					ar.nombre AS area,
					pjc.correo,
					s.solicitud_id,
					s.numero

				FROM
					mkt_auditoria a
						INNER JOIN
					tbl_usuarios u ON a.user_created_id = u.id
						INNER JOIN
					tbl_personal_apt p ON u.personal_id = p.id
						INNER JOIN
					tbl_areas ar ON p.area_id = ar.id
						INNER JOIN 
					mkt_solicitud s ON s.solicitud_id = a.solicitud_id
						INNER JOIN 
					tbl_usuarios ujc ON s.user_created_id = ujc.id
						INNER JOIN 
					tbl_personal_apt pjc ON ujc.personal_id = pjc.id
				WHERE
					a.status = 1 AND a.id = " . $auditoria_id . "
				ORDER BY a.id DESC");

	$body = "";
	$body .= '<html>';

	$usuario_creacion_correo = '';
	$solicitud_id = '';
	$numero = '';
	$correos_add = array();
	while($sel = $sel_query->fetch_assoc())
	{
		$tipo_valor = $sel["tipo_valor"];
		$usuario_creacion_correo = $sel["correo"];
		$solicitud_id = $sel["solicitud_id"];
		$numero = $sel["numero"];
		
		if ($tipo_valor == 'varchar') {
			$nuevo_valor = $sel['valor_varchar'];
		} else if ($tipo_valor == 'int') {
			$nuevo_valor = $sel['valor_int'];
		} else if ($tipo_valor == 'date') {
			$nuevo_valor = $sel['valor_date'];
		} else if ($tipo_valor == 'decimal') {
			$nuevo_valor = $sel['valor_decimal'];
		} else if ($tipo_valor == 'select_option') {
			$nuevo_valor = $sel['valor_select_option'];
		}

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

		$body .= '<thead>';
		
		$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>Datos Generales</b>';
			$body .= '</th>';
		$body .= '</tr>';
		

		$body .= '</thead>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Nombre del menú:</b></td>';
			$body .= '<td>'.$sel["nombre_menu_usuario"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Nombre del campo:</b></td>';
			$body .= '<td>'.$sel["nombre_campo_usuario"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Valor anterior:</b></td>';
			$body .= '<td>'.$sel["valor_original"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Nuevo valor:</b></td>';
			$body .= '<td>'.$nuevo_valor.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Usuario que realizó el cambio:</b></td>';
			$body .= '<td>'.$sel["usuario_que_realizo_cambio"].'</td>';
		$body .= '</tr>';
		
		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha del cambio:</b></td>';
			$body .= '<td>'.$sel["fecha_del_cambio"].'</td>';
		$body .= '</tr>';
	
	
		$body .= '</table>';
		$body .= '</div>';


	}
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$body .= '<div style="width: 700px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$host.'/?sec_id=marketing&sub_sec_id=detalle_solicitud&id='.$solicitud_id.'" target="_blank">';
		    	$body .= '<button style="background-color: green; color: white; cursor: pointer; width: 50%;">';
					$body .= '<b>Ver Solicitud</b>';
		    	$body .= '</button>';
		    $body .= '</a>';
		$body .= '</div>';

		$body .= '</html>';
		$body .= "";


	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));

	if (!Empty($usuario_creacion_correo)){
		array_push($correos_add,$usuario_creacion_correo);
	}
	
	$lista_correos = $correos->send_email_requerimiento_marketing($correos_add);

	$titulo_email = "Requerimiento Marketing Retail - Modificacion de Solicitud : Código - ";

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];

	$request = [
		"subject" => $titulo_email.$numero,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try 
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;        
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc) 
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc) 
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();
		//return true;

	} 
	catch (Exception $e) 
	{
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}

	
}

function send_email_cambio_estado_marketing($solicitud_id)
{

	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];


	$correos_add = [];
	$sel_query = $mysqli->query("SELECT 
	r.area_id,
	r.producto_id,
	r.tipo_solicitud_id,
	r.numero,
	r.objetivo,
	r.bullet_1,
	r.bullet_2,
	r.bullet_3,
	r.bullet_4,
	r.bullet_5,
	r.req_estrategico_1,
	r.req_estrategico_2,
	r.req_estrategico_3,
	r.req_estrategico_4,
	r.req_estrategico_5,
	r.req_estrategico_6,
	r.req_estrategico_7,
	r.req_estrategico_8,
	r.sustento_req_estrategico,
	r.etapa_id,
	r.status,
	r.user_created_id,
	r.created_at,
	r.respuesta,
	
	a.nombre AS nombre_area,
	p.nombre AS nombre_producto,
	ts.nombre AS nombre_solicitud,

	es.nombre as nombre_estado,
	DATE_FORMAT(r.fecha_entrega, '%d-%m-%Y') as fecha_entrega,
	DATE_FORMAT(r.fecha_cambio_estado, '%d-%m-%Y %H:%i:%s') as fecha_cambio_estado,
	
	re1.nombre as nombre_re1,
	re2.nombre as nombre_re2,
	re3.nombre as nombre_re3,
	re4.nombre as nombre_re4,
	re5.nombre as nombre_re5,
	re6.nombre as nombre_re6,
	re7.nombre as nombre_re7,
	re8.nombre as nombre_re8,
	
	CONCAT(IFNULL(peg.nombre, ''),' ',IFNULL(peg.apellido_paterno, ''),' ',IFNULL(peg.apellido_materno, '')) AS nombre_usuario,
	CONCAT(IFNULL(pegc.nombre, ''),' ',IFNULL(pegc.apellido_paterno, ''),' ',IFNULL(pegc.apellido_materno, '')) AS nombre_usuario_cambio,
	peg.correo AS email_usuario	
		
	FROM mkt_solicitud as r
	INNER JOIN mkt_areas AS a ON a.id = r.area_id
	INNER JOIN mkt_productos AS p ON p.id = r.producto_id
	INNER JOIN mkt_tipo_solicitud AS ts ON ts.id = r.tipo_solicitud_id
	
	INNER JOIN mkt_estado_solicitud AS es ON es.id = r.etapa_id
	
	LEFT JOIN mkt_tipo_requerimiento_estrategico AS re1 ON re1.id = r.req_estrategico_1
	LEFT JOIN mkt_tipo_requerimiento_estrategico AS re2 ON re2.id = r.req_estrategico_2
	LEFT JOIN mkt_tipo_requerimiento_estrategico AS re3 ON re3.id = r.req_estrategico_3
	LEFT JOIN mkt_tipo_requerimiento_estrategico AS re4 ON re4.id = r.req_estrategico_4
	LEFT JOIN mkt_tipo_requerimiento_estrategico AS re5 ON re5.id = r.req_estrategico_5
	LEFT JOIN mkt_tipo_requerimiento_estrategico AS re6 ON re6.id = r.req_estrategico_6
	LEFT JOIN mkt_tipo_requerimiento_estrategico AS re7 ON re7.id = r.req_estrategico_7
	LEFT JOIN mkt_tipo_requerimiento_estrategico AS re8 ON re8.id = r.req_estrategico_8
	
	LEFT JOIN tbl_usuarios us ON us.id = r.user_created_id
	LEFT JOIN tbl_personal_apt peg ON  peg.id = us.personal_id

	LEFT JOIN tbl_usuarios usc ON usc.id = r.id_usuario_cambio_estado
	LEFT JOIN tbl_personal_apt pegc ON  pegc.id = usc.personal_id
	
	WHERE r.solicitud_id = '".$solicitud_id."'
		");

	$body = "";
	$body .= '<html>';

	$usuario_creacion_correo = '';
	$nombre_area_solicitante = '';
	$codigo_correlativo = '';
	while($sel = $sel_query->fetch_assoc())
	{
		$nombre_area_solicitante = $sel['nombre_area'];
		$codigo_correlativo = $sel['numero'];
		$usuario_creacion_correo = $sel['email_usuario'];
		$estado_nombre = $sel['etapa_id'] == 2 ? 'En Proceso de Atención':$sel["nombre_estado"] ;

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

		$body .= '<thead>';
		
		$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>Cambio de Estado de Solicitud</b>';
			$body .= '</th>';
		$body .= '</tr>';
	
		$body .= '</thead>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Usuario que realizó el cambio:</b></td>';
			$body .= '<td style="white-space: pre-line;">'.$sel["nombre_usuario_cambio"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha de Cambio:</b></td>';
			$body .= '<td>'.$sel["fecha_cambio_estado"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Estado:</b></td>';
			$body .= '<td>'.$estado_nombre.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha de Entrega:</b></td>';
			$body .= '<td>'.$sel["fecha_entrega"].'</td>';
		$body .= '</tr>';

		if (!Empty($sel["respuesta"])) {
			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd"><b>Respuesta:</b></td>';
				$body .= '<td style="white-space: pre-line;">'.$sel["respuesta"].'</td>';
			$body .= '</tr>';
		}

		
		$body .= '</table>';
		$body .= '</div>';

	}
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$body .= '<div style="width: 700px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$host.'/?sec_id=contrato&sub_sec_id=detalle_solicitud_interno&id='.$solicitud_id.'" target="_blank">';
		    	$body .= '<button style="background-color: green; color: white; cursor: pointer; width: 50%;">';
					$body .= '<b>Ver Solicitud</b>';
		    	$body .= '</button>';
		    $body .= '</a>';
		$body .= '</div>';

		$body .= '</html>';
		$body .= "";

	if (!Empty($usuario_creacion_correo)) {
		array_push($correos_add,$usuario_creacion_correo);
	}

	$titulo_email = "Requerimiento Marketing Retail - ".ucfirst($nombre_area_solicitante).": Cód - ";


	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_cambio_marketing($correos_add);

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];

	$request = [
		"subject" => $titulo_email.$codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try 
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;        
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc) 
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc) 
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();
		//return true;

	} 
	catch (Exception $e) 
	{
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="editar_solicitud") {
	$usuario_id = $login?$login['id']:null;
	$area_id = $login ? $login['area_id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;

	if ((int) $usuario_id > 0) {
		$created_at = date("Y-m-d H:i:s");
		$solicitud_id = $_POST["solicitud_id"];
		$nombre_tabla = trim($_POST["nombre_tabla"]);
		$valor_original = trim($_POST["valor_original"]);
		$nombre_campo = trim($_POST["nombre_campo"]);
		$nombre_menu_usuario = trim($_POST["nombre_menu_usuario"]);
		$nombre_campo_usuario = trim($_POST["nombre_campo_usuario"]);
		$tipo_valor = trim($_POST["tipo_valor"]);

		if ($tipo_valor == 'varchar') {
			$valor_varchar = "'" . replace_invalid_caracters(trim($_POST["valor_varchar"]))  . "'";
			$valor_int = "NULL";
			$valor_date = "NULL";
			$valor_decimal = "NULL";
			$valor_select_option = "NULL";
			$valor_id_tabla = "NULL";
		}else if ($tipo_valor == 'textarea') {
			$tipo_valor = 'varchar';
			$valor_varchar = "'" . replace_invalid_caracters(trim($_POST["valor_textarea"]))  . "'";
			$valor_int = "NULL";
			$valor_date = "NULL";
			$valor_decimal = "NULL";
			$valor_select_option = "NULL";
			$valor_id_tabla = "NULL";
		} else if ($tipo_valor == 'int') {
			$valor_varchar = "NULL";
			$valor_int = trim($_POST["valor_int"]);
			$valor_date = "NULL";
			$valor_decimal = "NULL";
			$valor_select_option = "NULL";
			$valor_id_tabla = "NULL";
		} else if ($tipo_valor == 'date') {
			$valor_varchar = "NULL";
			$valor_int = "NULL";
			$valor_original = str_replace('/', '-', $valor_original);
			$valor_original = date("Y-m-d", strtotime($valor_original));
			$valor_date = "'" . date("Y-m-d", strtotime(trim($_POST["valor_date"]))) . "'";
			$valor_decimal = "NULL";
			$valor_select_option = "NULL";
			$valor_id_tabla = "NULL";
		} else if ($tipo_valor == 'decimal') {
			$valor_varchar = "NULL";
			$valor_int = "NULL";
			$valor_date = "NULL";
			$valor_decimal = str_replace(",","",trim($_POST["valor_decimal"]));
			$valor_select_option = "NULL";
			$valor_id_tabla = "NULL";
		} else if ($tipo_valor == 'select_option') {
			$valor_varchar = "NULL";
			$valor_int = trim($_POST["valor_select_option_id"]);
			$valor_date = "NULL";
			$valor_decimal = "NULL";
			$valor_select_option = trim($_POST["valor_select_option"]);
			$valor_id_tabla = "NULL";
		} else if ($tipo_valor == 'id_tabla') {
			$valor_varchar = "NULL";
			$valor_int = trim($_POST["valor_int"]);
			$valor_date = "NULL";
			$valor_decimal = "NULL";
			$valor_select_option = "NULL";
			$valor_id_tabla = trim($_POST["valor_id_tabla"]);
		}

		$query_insert = "
		INSERT INTO mkt_auditoria
		(
		solicitud_id,
		nombre_tabla,
		valor_original,
		nombre_campo,
		nombre_menu_usuario,
		nombre_campo_usuario,
		tipo_valor,
		valor_varchar,
		valor_int,
		valor_date,
		valor_decimal,
		valor_select_option,
		valor_id_tabla,
		user_created_id,
		created_at
		)
		VALUES
		(
		" . $solicitud_id . ",
		'" . $nombre_tabla . "',
		'" . replace_invalid_caracters($valor_original) . "',
		'" . $nombre_campo . "',
		'" . $nombre_menu_usuario . "',
		'" . $nombre_campo_usuario . "',
		'" . $tipo_valor . "',
		" . $valor_varchar . ",
		" . $valor_int . ",
		" . $valor_date . ",
		" . $valor_decimal . ",
		'" . $valor_select_option . "',
		" . $valor_id_tabla . ",
		" . $usuario_id . ",
		'" . $created_at . "'
		)";

		$mysqli->query($query_insert);
		$insert_id = mysqli_insert_id($mysqli);
		$error = '';
		if($mysqli->error){
			$result["insert_error"] = $mysqli->error . $query_insert;
		} else {
		
			$nombre_tabla = trim($_POST["nombre_tabla"]);
			$nombre_campo = trim($_POST["nombre_campo"]);
			$id_tabla = trim($_POST["id_tabla"]);

			if ($tipo_valor == 'varchar') {
				$nuevo_valor = $valor_varchar;
			} else if ($tipo_valor == 'int') {
				$nuevo_valor = $valor_int;
			} else if ($tipo_valor == 'date') {
				$nuevo_valor = $valor_date;
			} else if ($tipo_valor == 'decimal') {
				$nuevo_valor = $valor_decimal;
			} else if ($tipo_valor == 'select_option') {
				$nuevo_valor = $valor_int;
			}

			if ($id_tabla > 0) {
				$query_id_tabla = 'id';
				$valor_id_tabla = $id_tabla;
			} else {
				$query_id_tabla = 'solicitud_id';
				$valor_id_tabla = $solicitud_id;
			}

			if (empty($nombre_tabla) || empty($nombre_campo) || empty($nuevo_valor) || empty($query_id_tabla) || empty($valor_id_tabla)) {
				$result["update_error"] = 'Algunos del los campos esta vacío';
			} else if (substr($nombre_tabla,0,3) != 'mkt') {
				$result["update_error"] = 'La tabla a actualizar no pertenece al sistema de contratos';
			} else {
				$query_update = "
				UPDATE " . $nombre_tabla . " 
				SET 
					" . $nombre_campo . " = " . $nuevo_valor . "
				WHERE " . $query_id_tabla . " = '" . $valor_id_tabla . "'";
				$mysqli->query($query_update);

				if($mysqli->error){
					$result["update_error"] = $mysqli->error . $query_update;
				}
				send_email_cambio_marketing($insert_id);
			}
		}

		
		$result["result"] = 'ok';
		$result["http_code"] = 200;
		$result["status"] = "Se ha modificado correctamente la solicitud.";
	} else {
		$result["http_code"] = 400;
		$result["error"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y volver a hacer clic en el botón: Editar.";
	}

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_estado_solicitud_marketing") {
	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	if ((int) $usuario_id > 0) {
		$solicitud_id = $_POST["solicitud_id"];
		$estado = $_POST["estado"];
		$respuesta = $_POST["respuesta"];
		$fecha_entrega = "'" . date("Y-m-d", strtotime($_POST['fecha_entrega'])) . "'";
		$query_update = "
		UPDATE mkt_solicitud 
		SET 
			etapa_id = " . $estado . ",
			id_usuario_cambio_estado = " . $usuario_id . ",
			fecha_cambio_estado = '" . $created_at . "',
			fecha_entrega = " . $fecha_entrega . ",
			respuesta = '" . replace_invalid_caracters($respuesta) . "'
		WHERE solicitud_id = '" . $solicitud_id . "'";
		$mysqli->query($query_update);
		if($mysqli->error){
			$result["status"] = 404;
			$result["message"] = $mysqli->error . $query_update;
			exit();
		}
		send_email_cambio_estado_marketing($solicitud_id);
		
		$message = "Se ha modificado el estado de la solicitud a ";
		if ($estado == 1) {
			$message .= ' pendiente';
		}
		if ($estado == 2) {
			$message .= ' en proceso de atención';
		}
		if ($estado == 1) {
			$message .= ' entregado';
		}
		if ($estado == 1) {
			$message .= ' observado';
		}

		$result["status"] = 200;
		$result["message"] = $message;
	} else {
		$result["status"] = 400;
		$result["message"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y volver a hacer clic en el botón: Editar.";
	}

	echo json_encode($result);
	exit();
}

?>
