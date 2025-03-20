<?php
date_default_timezone_set("America/Lima");

$result=array();
include("db_connect.php");
include("sys_login.php");

include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';

require_once '/var/www/html/env.php';
include '/var/www/html/sys/envio_correos.php';
include '/var/www/html/sys/function_replace_invalid_caracters_contratos.php';
include '/var/www/html/sys/set_contrato_seguimiento_proceso.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_resolucion_contrato") {
	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	$error = '';

	if ($usuario_id == null) {
		$result["status"] = 400;
		$result["message"] = 'Su sesion ha caducado, Ingrese de nuevo al sistema';
		$result["result"] = "error";
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}

	$tipo_contrato_id = $_POST['sec_con_nuevo_resol_tipo_contrato_id'];
	$contrato_id = 0;
	$contrato_detalle_id = 0;
	if ($tipo_contrato_id == 1) {
		$contrato_detalle_id = $_POST["sec_con_nuevo_resol_contrato_id"];

		$query_sel = "SELECT d.contrato_id FROM cont_contrato_detalle AS d WHERE d.id = ".$contrato_detalle_id;
		$list_query = $mysqli->query($query_sel);
		$result = $list_query->fetch_assoc();
		if (isset($result['contrato_id']) && !Empty($result['contrato_id'])) {
			$contrato_id = $result['contrato_id'];	
		}
	}else{
		$contrato_id = $_POST["sec_con_nuevo_resol_contrato_id"];
	}

	//fecha de carta solo para contrato de arrendamiento y agente
	$fecha_carta = 'NULL';
	if ($tipo_contrato_id == 1 || $tipo_contrato_id == 6) {
		if (isset($_POST['sec_con_nuevo_resol_fecha_carta']) && !Empty($_POST['sec_con_nuevo_resol_fecha_carta'])) {
			$fecha_carta = date("Y-m-d", strtotime($_POST["sec_con_nuevo_resol_fecha_carta"]));
			$fecha_carta = "'".$fecha_carta."'";
		}
	}

	$fecha_resolucion = date("Y-m-d", strtotime($_POST["sec_con_nuevo_resol_fecha_resolucion"]));
	$anexo_archivo_id = 0;
	// INICIO INSERTAR EN ADENDA
	$query_insert = "	INSERT INTO cont_resolucion_contrato
						(
							tipo_contrato_id,
							contrato_id,
							contrato_detalle_id,
							motivo,
							fecha_resolucion,
							fecha_carta,
							anexo_archivo_id,
							estado_solicitud_id,

							aprobacion_gerencia_id,
							cargo_aprobante_id,
							estado_aprobacion_gerencia,
					
							status,
							user_created_id,
							created_at,
							user_updated_id,
							updated_at
						)
						VALUES
						(
							'" . $tipo_contrato_id . "',
							'" . $contrato_id . "',
							'" . $contrato_detalle_id . "',
							'" . replace_invalid_caracters($_POST["sec_con_nuevo_resol_motivo"]). "',
							'" . $fecha_resolucion . "',
							" . $fecha_carta . ",
							'" . $anexo_archivo_id . "',
							1,

							'".$_POST['sec_con_nuevo_resol_aprobante_id']."',
							'".$_POST['sec_con_nuevo_resol_cargo_aprobante_id']."',
							0,

							1,
							" . $usuario_id . ",
							'" . $created_at . "',
							" . $usuario_id . ",
							'" . $created_at . "'
						)
						";

	$mysqli->query($query_insert);
	if($mysqli->error){
		$error .= $mysqli->error;
		$result["status"] = 400;
		$result["message"] = $error;
		$result["result"] = "error";
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}
	$resolucion_contrato_id = mysqli_insert_id($mysqli);
	
	// if ($tipo_contrato_id == 1) {
	// 	$query_update_contrato = "UPDATE 
	// 		cont_contrato_detalle 
	// 	SET 
	// 		estado_resolucion = 1
	// 	WHERE 
	// 		id = ".$contrato_detalle_id;

	// 	$mysqli->query($query_update_contrato);
	// }else{
	// 	$query_update_contrato = "UPDATE 
	// 		cont_contrato 
	// 	SET 
	// 		estado_resolucion = 1
	// 	WHERE 
	// 		contrato_id = ".$contrato_id;

	// 	$mysqli->query($query_update_contrato);
	// }
	


	$path = "/var/www/html/files_bucket/contratos/solicitudes/resolucion_contrato/";
	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	if (isset($_FILES['sec_con_nuevo_resol_anexo']) && $_FILES['sec_con_nuevo_resol_anexo']['error'] === UPLOAD_ERR_OK) {
		if (!is_dir($path)) mkdir($path, 0777, true);

		$filename = $_FILES['sec_con_nuevo_resol_anexo']['name'];
		$filenametem = $_FILES['sec_con_nuevo_resol_anexo']['tmp_name'];
		$filesize = $_FILES['sec_con_nuevo_resol_anexo']['size'];
		$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
		if($filename != ""){
			$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
			$nombre_archivo = $resolucion_contrato_id . "_RESOLUCION_CONTRATO_" . date('YmdHis') . "." . $fileExt;
			move_uploaded_file($filenametem, $path . $nombre_archivo);
			$comando = "INSERT INTO cont_archivos (
							contrato_id,
							contrato_detalle_id,
							resolucion_contrato_id,
							tipo_archivo_id,
							nombre,
							extension,
							size,
							ruta,
							user_created_id,
							created_at)
						VALUES(
							0,
							'" . $contrato_detalle_id . "',
							'" . $resolucion_contrato_id . "',
							104,
							'" . $nombre_archivo . "',
							'" . $fileExt . "',
							'" . $filesize . "',
							'" . $path . "',
							" . $usuario_id . ",
							'" . $created_at . "'
							)";
			$mysqli->query($comando);
			$archivo_id = mysqli_insert_id($mysqli);
			if($mysqli->error){
				$error .= $mysqli->error;
				$result["status"] = 400;
				$result["message"] = $error;
				$result["result"] = "error";
				$result["error"] = $error;
				echo json_encode($result);
				exit();
			}
			$query_update = "UPDATE cont_resolucion_contrato SET anexo_archivo_id = $archivo_id 
			WHERE id =".$resolucion_contrato_id;
			$mysqli->query($query_update);
			
		}
	}
	
	if ($tipo_contrato_id == 2) { // Contrato de proveedores
		
		$query_contrato = "SELECT area_responsable_id, tipo_contrato_id FROM cont_contrato WHERE contrato_id = ".$contrato_id;
		$sel_contrato = $mysqli->query($query_contrato);
		$data_resolucion = $sel_contrato->fetch_assoc();

		$APROBACION_DEL_DOCUMENTO = 1;
		$INICIO_DE_PROCESO_LEGAL = 2;
		$AREA_LEGAL_ID = 33;
		$RESOLUCION_DE_CONTRATO = 3;

		if (!Empty($data_resolucion['area_responsable_id'])) {
			$seg_proceso = new SeguimientoProceso();
			$data_proceso['tipo_documento_id'] = $RESOLUCION_DE_CONTRATO;
			$data_proceso['proceso_id'] = $resolucion_contrato_id;
			$data_proceso['proceso_detalle_id'] = 0;
			$data_proceso['area_id'] = $data_resolucion['area_responsable_id'];
			$data_proceso['etapa_id'] = $APROBACION_DEL_DOCUMENTO;
			$data_proceso['status'] = 1;
			$data_proceso['created_at'] = date('Y-m-d H:i:s');
			$data_proceso['user_created_id'] = $usuario_id;
			$resp_proceso = $seg_proceso->registrar_proceso($data_proceso);
		}
	}
	
	send_email_confirmacion_solicitud_resolucion_contrato($resolucion_contrato_id);

	$result["status"] = 200;
	$result["message"] = "Se ha registrado exitosamente la resolucion de contrato.";
	$result["result"] = "ok";
	$result["error"] = $error;
	echo json_encode($result);
	exit();
}

function send_email_confirmacion_solicitud_resolucion_contrato($resolucion_id){
	include("db_connect.php");
	include("sys_login.php");
	
	$host = $_SERVER["HTTP_HOST"];

	
	$sel_query = $mysqli->query("
	SELECT r.id, r.contrato_id, 
	c.tipo_contrato_id, 
	r.motivo, 
	r.fecha_solicitud, 
	DATE_FORMAT(r.fecha_resolucion,'%d-%m-%Y') AS fecha_resolucion,
	DATE_FORMAT(r.fecha_carta,'%d-%m-%Y') AS fecha_carta,
	CONCAT(IFNULL(tpa.nombre, ''),' ',IFNULL(tpa.apellido_paterno, ''),	' ',	IFNULL(tpa.apellido_materno, '')) AS usuario_solicitud,
	r.anexo_archivo_id,
	r.archivo_id,
	r.status,
	DATE_FORMAT(r.created_at,'%d-%m-%Y %H:%i:%s') AS created_at,
	c.nombre_tienda,
	co.sigla, c.codigo_correlativo, 
	tpa.correo, 
	ar.nombre AS nombre_area,
	c.gerente_area_email,
	puap.correo AS email_del_aprobante
		
	FROM cont_resolucion_contrato AS r
	INNER JOIN tbl_usuarios tu ON r.user_created_id = tu.id
	INNER JOIN tbl_personal_apt tpa ON tu.personal_id = tpa.id
	INNER JOIN tbl_areas AS ar ON tpa.area_id = ar.id

	INNER JOIN cont_contrato AS c ON c.contrato_id = r.contrato_id
	LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato

	LEFT JOIN tbl_usuarios uap ON r.aprobacion_gerencia_id = uap.id
	LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id

	WHERE r.id = ".$resolucion_id);
	


	$body = "";
	$body .= '<html>';

	$usuario_creacion_correo = '';
	$contrato_id = 0;
	$tipo_contrato_id = 0;
	$url_solicitud = "";
	$nombre_tienda = "";
	$correos_adicionales = [];
	while($sel = $sel_query->fetch_assoc())
	{
		$contrato_id = $sel['contrato_id']; 
		$tipo_contrato_id = $sel['tipo_contrato_id']; 
		$sigla_correlativo = $sel['sigla'];
		$fecha_carta = $sel['fecha_carta'];
		$codigo_correlativo = $sel['codigo_correlativo'];
		$nombre_tienda = $sel['nombre_tienda'];

		if(!Empty($sel['email_del_aprobante'])){
			array_push($correos_adicionales, $sel['email_del_aprobante']);
		}

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

		$body .= '<thead>';
		
		$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>Nueva solicitud</b>';
			$body .= '</th>';
		$body .= '</tr>';
		

		$body .= '</thead>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Area Solicitante:</b></td>';
			$body .= '<td>'.$sel["nombre_area"].'</td>';
		$body .= '</tr>';
		
		if((int) $tipo_contrato_id == 1){
			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Nombre de tienda:</b></td>';
			$body .= '<td>'.$nombre_tienda.'</td>';
			$body .= '</tr>';
		}
		
		if (!Empty($fecha_carta)) {
			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha de Carta:</b></td>';
			$body .= '<td>'.$fecha_carta.'</td>';
			$body .= '</tr>';
		}

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha de Resolución:</b></td>';
			$body .= '<td>'.$sel["fecha_resolucion"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Motivo:</b></td>';
			$body .= '<td>'.$sel["motivo"].'</td>';
		$body .= '</tr>';


		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Usuario solicitante:</b></td>';
		$body .= '<td>'.$sel["usuario_solicitud"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha Solicitud:</b></td>';
			$body .= '<td>'.$sel["created_at"].'</td>';
		$body .= '</tr>';
		
		$body .= '</table>';
		$body .= '</div>';

	}
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		switch ($tipo_contrato_id) {
			case '1': $url_solicitud = "detalle_solicitud"; break;
			case '2': $url_solicitud = "detalleSolicitudProveedor"; break;
			case '5': $url_solicitud = "detalle_solicitud_acuerdo_confidencialidad"; break;
			case '6': $url_solicitud = "detalle_agente"; break;
			case '7': $url_solicitud = "detalle_solicitud_interno"; break;
		}

		$body .= '<div style="width: 700px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$host.'/?sec_id=contrato&sub_sec_id='.$url_solicitud.'&id='.$contrato_id.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
				$body .= '<b>Ver Solicitud</b>';
		    $body .= '</a>';
		$body .= '</div>';

		$body .= '</html>';
		$body .= "";

	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_confirmacion_solicitud_resolucion_contrato($correos_adicionales);

	$cc = $lista_correos['cc'];
	$nombre_tipo_contrato = "";
	switch ($tipo_contrato_id) {
		case '1': $nombre_tipo_contrato = "Contrato de Arrendamiento"; break;
		case '2': $nombre_tipo_contrato = "Contrato de Proveedor"; break;
		case '5': $nombre_tipo_contrato = "Contrato de Acuerdo de Confidencialidad"; break;
		case '6': $nombre_tipo_contrato = "Contrato de Agente"; break;
		case '7': $nombre_tipo_contrato = "Contrato Interno"; break;
	}
	$titulo_email = "Gestion - Sistema Contratos - Aprobación del Documento - Confirmación de Solicitud de Resolución de ".$nombre_tipo_contrato.": ".$nombre_tienda." Código - ";

	$bcc = $lista_correos['bcc'];

	$request = [
		"subject" => $titulo_email.$sigla_correlativo.$codigo_correlativo,
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



function send_email_solicitud_resolucion_contrato($resolucion_id)
{
	include("db_connect.php");
	include("sys_login.php");
	
	$host = $_SERVER["HTTP_HOST"];

	
	$sel_query = $mysqli->query("
	SELECT r.id, r.contrato_id, c.tipo_contrato_id, r.motivo, r.fecha_solicitud, r.fecha_resolucion ,
	CONCAT(IFNULL(tpa.nombre, ''),' ',IFNULL(tpa.apellido_paterno, ''),	' ',	IFNULL(tpa.apellido_materno, '')) AS usuario_solicitud,
	r.anexo_archivo_id,
	r.archivo_id,
	CONCAT(IFNULL(tpa2.nombre, ''),' ',IFNULL(tpa2.apellido_paterno, ''),	' ',	IFNULL(tpa2.apellido_materno, '')) AS usuario_aprobado,
	r.fecha_resolucion_contrato_aprobado,
	r.status,
	r.created_at,
	c.nombre_tienda,
	co.sigla, c.codigo_correlativo, tpa.correo, ar.nombre AS nombre_area,
	
	per.correo AS email_usuario_creacion_contrato,
	c.gerente_area_email,
	peg.correo AS email_del_gerente_area,
	puap.correo AS email_del_aprobante
		
	FROM cont_resolucion_contrato AS r
	INNER JOIN tbl_usuarios tu ON r.user_created_id = tu.id
	INNER JOIN tbl_personal_apt tpa ON tu.personal_id = tpa.id
	INNER JOIN tbl_areas AS ar ON tpa.area_id = ar.id
	
	LEFT JOIN tbl_usuarios tu2 ON r.usuario_resolucion_contrato_aprobado_id = tu2.id
	LEFT JOIN tbl_personal_apt tpa2 ON tu2.personal_id = tpa2.id

	INNER JOIN cont_contrato AS c ON c.contrato_id = r.contrato_id
	LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato

	LEFT JOIN tbl_usuarios u ON c.user_created_id = u.id
	LEFT JOIN tbl_personal_apt per ON u.personal_id = per.id
	
	LEFT JOIN tbl_usuarios ug ON c.gerente_area_id = ug.id
	LEFT JOIN tbl_personal_apt peg ON ug.personal_id = peg.id

	LEFT JOIN tbl_usuarios uap ON c.aprobado_por = uap.id
	LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id

	WHERE r.id = ".$resolucion_id);
	


	$body = "";
	$body .= '<html>';

	$usuario_creacion_correo = '';
	$contrato_id = 0;
	$tipo_contrato_id = 0;
	$url_solicitud = "";
	$nombre_tienda = "";
	$correos_adicionales = [];
	while($sel = $sel_query->fetch_assoc())
	{
		$contrato_id = $sel['contrato_id']; 
		$tipo_contrato_id = $sel['tipo_contrato_id']; 
		$sigla_correlativo = $sel['sigla'];
		$codigo_correlativo = $sel['codigo_correlativo'];
		$motivo = $sel['motivo'];
		$usuario_solicitud = $sel['usuario_solicitud'];
		$fecha_solicitud = $sel['created_at'];
		$usuario_aprobado = $sel['usuario_aprobado'];
		$fecha_resolucion = $sel['fecha_resolucion'];
		$nombre_tienda = $sel['nombre_tienda'];

		
		if(!Empty($sel['correo'])){
			array_push($correos_adicionales, $sel['correo']);
		}

		if ($tipo_contrato_id == 2) { // contrato de proveedor
			if(!Empty($sel['email_usuario_creacion_contrato'])){
				array_push($correos_adicionales, $sel['email_usuario_creacion_contrato']);
			}
			if(!Empty($sel['gerente_area_email'])){
				array_push($correos_adicionales, $sel['gerente_area_email']);
			}
			if(!Empty($sel['email_del_gerente_area'])){
				array_push($correos_adicionales, $sel['email_del_gerente_area']);
			}
			if(!Empty($sel['email_del_aprobante'])){
				array_push($correos_adicionales, $sel['email_del_aprobante']);
			}
		}


		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

		$body .= '<thead>';
		
		$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>Nueva solicitud</b>';
			$body .= '</th>';
		$body .= '</tr>';
		

		$body .= '</thead>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Area Solicitante:</b></td>';
			$body .= '<td>'.$sel["nombre_area"].'</td>';
		$body .= '</tr>';
		
		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Usuario solicitante:</b></td>';
			$body .= '<td>'.$sel["usuario_solicitud"].'</td>';
		$body .= '</tr>';
	
		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha Solicitud:</b></td>';
			$body .= '<td>'.$sel["created_at"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Motivo:</b></td>';
			$body .= '<td>'.$sel["motivo"].'</td>';
		$body .= '</tr>';
		if($tipo_contrato_id == 1){
			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Nombre de tienda:</b></td>';
			$body .= '<td>'.$nombre_tienda.'</td>';
			$body .= '</tr>';
		}
		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha de Resolución:</b></td>';
			$body .= '<td>'.$sel["fecha_resolucion"].'</td>';
		$body .= '</tr>';

		
		$body .= '</table>';
		$body .= '</div>';

	}
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		switch ($tipo_contrato_id) {
			case '1': $url_solicitud = "detalle_solicitud"; break;
			case '2': $url_solicitud = "detalleSolicitudProveedor"; break;
			case '5': $url_solicitud = "detalle_solicitud_acuerdo_confidencialidad"; break;
			case '6': $url_solicitud = "detalle_agente"; break;
			case '7': $url_solicitud = "detalle_solicitud_interno"; break;
		}

		$body .= '<div style="width: 700px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$host.'/?sec_id=contrato&sub_sec_id='.$url_solicitud.'&id='.$contrato_id.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
				$body .= '<b>Ver Solicitud</b>';
		    $body .= '</a>';
		$body .= '</div>';

		$body .= '</html>';
		$body .= "";

	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_solicitud_resolucion_contrato($correos_adicionales);

	$cc = $lista_correos['cc'];
	$nombre_tipo_contrato = "";
	switch ($tipo_contrato_id) {
		case '1': $nombre_tipo_contrato = "Contrato de Arrendamiento"; break;
		case '2': $nombre_tipo_contrato = "Contrato de Proveedor"; break;
		case '5': $nombre_tipo_contrato = "Contrato de Acuerdo de Confidencialidad"; break;
		case '6': $nombre_tipo_contrato = "Contrato de Agente"; break;
		case '7': $nombre_tipo_contrato = "Contrato Interno"; break;
	}
	$titulo_email = "Gestion - Sistema Contratos - Nueva Solicitud de Resolución de ".$nombre_tipo_contrato.": ".$nombre_tienda." Código - ";

	$bcc = $lista_correos['bcc'];

	$request = [
		"subject" => $titulo_email.$sigla_correlativo.$codigo_correlativo,
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





?>
