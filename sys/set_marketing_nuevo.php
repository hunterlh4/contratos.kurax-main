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


function send_email_requerimiento_marketing($solicitud_id)
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
	
	a.nombre AS nombre_area,
	p.nombre AS nombre_producto,
	ts.nombre AS nombre_solicitud,
	
	re1.nombre as nombre_re1,
	re2.nombre as nombre_re2,
	re3.nombre as nombre_re3,
	re4.nombre as nombre_re4,
	re5.nombre as nombre_re5,
	re6.nombre as nombre_re6,
	re7.nombre as nombre_re7,
	re8.nombre as nombre_re8,
	
	CONCAT(IFNULL(peg.nombre, ''),' ',IFNULL(peg.apellido_paterno, ''),' ',IFNULL(peg.apellido_materno, '')) AS nombre_usuario,
	peg.correo AS email_usuario	
		
	FROM mkt_solicitud as r
	INNER JOIN mkt_areas AS a ON a.id = r.area_id
	INNER JOIN mkt_productos AS p ON p.id = r.producto_id
	INNER JOIN mkt_tipo_solicitud AS ts ON ts.id = r.tipo_solicitud_id
	
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


		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

		$body .= '<thead>';
		
		$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>Nueva Solicitud</b>';
			$body .= '</th>';
		$body .= '</tr>';
		

		$body .= '</thead>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Área:</b></td>';
			$body .= '<td>'.$sel["nombre_area"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd;"><b>Producto:</b></td>';
			$body .= '<td>'.$sel["nombre_producto"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Solicitud:</b></td>';
			$body .= '<td>'.$sel["nombre_solicitud"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Objetivo:</b></td>';
			$body .= '<td style="white-space: pre-line;">'.$sel["objetivo"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Bullet 1:</b></td>';
			$body .= '<td>'.$sel["bullet_1"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Bullet 2:</b></td>';
			$body .= '<td>'.$sel["bullet_2"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Bullet 3:</b></td>';
			$body .= '<td>'.$sel["bullet_3"].'</td>';
		$body .= '</tr>';

		if (!Empty($sel["bullet_4"])) {
			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd"><b>Bullet 4:</b></td>';
				$body .= '<td>'.$sel["bullet_3"].'</td>';
			$body .= '</tr>';
		}

		if (!Empty($sel["bullet_5"])) {
			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd"><b>Bullet 5:</b></td>';
				$body .= '<td>'.$sel["bullet_5"].'</td>';
			$body .= '</tr>';
		}

		if (!Empty($sel["nombre_re1"])) {
			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd"><b>Requerimiento Estratégico 1:</b></td>';
				$body .= '<td>'.$sel["nombre_re1"].'</td>';
			$body .= '</tr>';
		}
		if (!Empty($sel["nombre_re2"])) {
			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd"><b>Requerimiento Estratégico 2:</b></td>';
				$body .= '<td>'.$sel["nombre_re2"].'</td>';
			$body .= '</tr>';
		}
		if (!Empty($sel["nombre_re3"])) {
			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd"><b>Requerimiento Estratégico 3:</b></td>';
				$body .= '<td>'.$sel["nombre_re3"].'</td>';
			$body .= '</tr>';
		}if (!Empty($sel["nombre_re4"])) {
			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd"><b>Requerimiento Estratégico 4:</b></td>';
				$body .= '<td>'.$sel["nombre_re4"].'</td>';
			$body .= '</tr>';
		}if (!Empty($sel["nombre_re5"])) {
			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd"><b>Requerimiento Estratégico 5:</b></td>';
				$body .= '<td>'.$sel["nombre_re5"].'</td>';
			$body .= '</tr>';
		}if (!Empty($sel["nombre_re6"])) {
			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd"><b>Requerimiento Estratégico 6:</b></td>';
				$body .= '<td>'.$sel["nombre_re6"].'</td>';
			$body .= '</tr>';
		}if (!Empty($sel["nombre_re7"])) {
			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd"><b>Requerimiento Estratégico 7:</b></td>';
				$body .= '<td>'.$sel["nombre_re7"].'</td>';
			$body .= '</tr>';
		}if (!Empty($sel["nombre_re8"])) {
			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd"><b>Requerimiento Estratégico 8:</b></td>';
				$body .= '<td>'.$sel["nombre_re8"].'</td>';
			$body .= '</tr>';
		}
		
		
		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Sustento Requerimiento Estratégico:</b></td>';
			$body .= '<td style="white-space: pre-line;">'.$sel["sustento_req_estrategico"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Usuario Solicitante:</b></td>';
			$body .= '<td style="white-space: pre-line;">'.$sel["nombre_usuario"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha de Solicitud:</b></td>';
			$body .= '<td style="white-space: pre-line;">'.$sel["created_at"].'</td>';
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

	if (!Empty($usuario_creacion_correo)) {
		array_push($correos_add,$usuario_creacion_correo);
	}

	$titulo_email = "Requerimiento Marketing Retail - ".ucfirst($nombre_area_solicitante).": Cód - ";


	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_requerimiento_marketing($correos_add);

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


if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_requerimiento_marketing") {

	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");

	if ($usuario_id == null) {
		$result["status"] = 404;
		$result["message"] = "Su sesion ha caducado, Ingrese de nuevo al sistema.";
		$result["result"] = '';
		echo json_encode($result);
		exit();
	}


	$errors = "";

	$query_update = "UPDATE mkt_correlativo SET numero = numero + 1 WHERE status = 1";
	$mysqli->query($query_update);
	if($mysqli->error)
	{
		$result["status"] = 404;
		$result["message"] = $mysqli->error;
		$result["result"] = '';
		echo json_encode($result);
		exit();
	}
	else
	{
		$numero_correlativo = "";
		$select_correlativo = "SELECT numero FROM mkt_correlativo WHERE status = 1 LIMIT 1";
		$list_query = $mysqli->query($select_correlativo);

		while($sel = $list_query->fetch_assoc())
		{
			$numero_correlativo = $sel["numero"];
		}

		$query_insert = "INSERT INTO mkt_solicitud
		(
			area_id,
			producto_id,
			tipo_solicitud_id,
			numero,
			objetivo,
			bullet_1,
			bullet_2,
			bullet_3,
			bullet_4,
			bullet_5,
			req_estrategico_1,
			req_estrategico_2,
			req_estrategico_3,
			req_estrategico_4,
			req_estrategico_5,
			req_estrategico_6,
			req_estrategico_7,
			req_estrategico_8,
			sustento_req_estrategico,
			etapa_id,
			status,
			user_created_id,
			created_at
		)
		VALUES
		(
			'" . $_POST["area_id"] . "',
			'" . $_POST["producto_id"] . "',
			'" . $_POST["tipo_solicitud_id"] . "',
			'" . $numero_correlativo . "',
			'" . replace_invalid_caracters($_POST["objetivo"]) . "',
			'" . replace_invalid_caracters($_POST["bullet_1"]) . "',
			'" . replace_invalid_caracters($_POST["bullet_2"]) . "',
			'" . replace_invalid_caracters($_POST["bullet_3"]) . "',
			'" . replace_invalid_caracters($_POST["bullet_4"]) . "',
			'" . replace_invalid_caracters($_POST["bullet_5"]) . "',
			'" . $_POST["req_estrategico_1"] . "',
			'" . $_POST["req_estrategico_2"] . "',
			'" . $_POST["req_estrategico_3"] . "',
			'" . $_POST["req_estrategico_4"] . "',
			'" . $_POST["req_estrategico_5"] . "',
			'" . $_POST["req_estrategico_6"] . "',
			'" . $_POST["req_estrategico_7"] . "',
			'" . $_POST["req_estrategico_8"] . "',
			'" . replace_invalid_caracters($_POST["sustento_req_estrategico"]) . "',
			1,
			1,
			" . $usuario_id . ",
			'" . $created_at . "'
		)";
		$mysqli->query($query_insert);
		$contrato_id = mysqli_insert_id($mysqli);
		$error = '';
		if($mysqli->error){
			$result["status"] = 404;
			$result["message"] = $mysqli->error;
			$result["result"] = '';
			echo json_encode($result);
			exit();
		} else {
			
			send_email_requerimiento_marketing($contrato_id);
	
			$result["status"] = 200;
			$result["message"] = 'La solicitud fue registrado correctamente';
			$result["result"] = $contrato_id;
			echo json_encode($result);
			exit();						
		}
	}
}

?>
