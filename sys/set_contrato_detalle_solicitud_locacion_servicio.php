<?php
date_default_timezone_set("America/Lima");

$result = array();
include("db_connect.php");
include("sys_login.php");

include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';
require_once '/var/www/html/sys/helpers.php';

require_once '/var/www/html/env.php';
include '/var/www/html/sys/envio_correos.php';
include '/var/www/html/sys/function_replace_invalid_caracters_contratos.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


function send_email_observacion_contrato_interno($observacion_id, $correos_adjuntos)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$list_emp_cont = $mysqli->query("SELECT tpg.valor FROM tbl_parametros_generales tpg WHERE tpg.codigo = 'empresa_contacto_de_contratos' AND tpg.estado = 1 LIMIT 1");
	$row_emp_cont = $list_emp_cont->fetch_assoc();
	$pref_empresa_contacto = isset($row_emp_cont['valor']) && $row_emp_cont['valor'] == 'Kurax' ? 'Kurax' : 'AT';

	$html = '';

	$sql = "SELECT
				o.id,
				o.contrato_id,
				o.observaciones,
				rs1.nombre AS empresa_at1,
				rs2.nombre AS empresa_at2,
			    SUBSTRING(c.detalle_servicio, 1, 20) AS detalle_servicio,
			    c.fecha_inicio,
				concat(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, '')) AS usuario_creacion,
				p.correo AS usuario_creacion_correo,
				ar.id AS area_creacion_id,
				ar.nombre AS area_creacion,
				o.user_created_id,
				o.created_at,
				concat(IFNULL(ps.nombre, ''),' ', IFNULL(ps.apellido_paterno, '')) AS supervisor,
				concat(IFNULL(pjc.nombre, ''),' ', IFNULL(pjc.apellido_paterno, '')) AS jefe_comercial,
				pjc.correo AS usuario_solicitante_correo,
				co.sigla AS sigla_correlativo,
				c.codigo_correlativo,
				pdag.correo AS email_director_aprobacion,
				pap.correo AS email_aprobacion_gerencia
			FROM cont_observaciones o
				INNER JOIN tbl_usuarios u ON o.user_created_id = u.id
				INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
				INNER JOIN tbl_areas ar ON p.area_id = ar.id
				INNER JOIN cont_contrato c ON o.contrato_id = c.contrato_id
				INNER JOIN tbl_razon_social rs1 ON c.empresa_suscribe_id = rs1.id
				INNER JOIN tbl_razon_social rs2 ON c.empresa_grupo_at_2 = rs2.id
				LEFT JOIN tbl_usuarios us ON c.persona_responsable_id = us.id
				LEFT JOIN tbl_personal_apt ps ON us.personal_id = ps.id
				LEFT JOIN tbl_usuarios ujc ON c.user_created_id = ujc.id
				LEFT JOIN tbl_personal_apt pjc ON ujc.personal_id = pjc.id
				LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato

				LEFT JOIN tbl_usuarios udag ON c.director_aprobacion_id = udag.id
				LEFT JOIN tbl_personal_apt pdag ON udag.personal_id = pdag.id

				LEFT JOIN tbl_usuarios uap ON c.aprobado_por = uap.id
				LEFT JOIN tbl_personal_apt pap ON uap.personal_id = pap.id
			WHERE o.id = " . $observacion_id . " AND o.status = 1
			ORDER BY o.created_at ASC";


	$query = $mysqli->query($sql);

	$contrato_id_de_la_obs = "";

	$body = "";
	$body .= '<html>';
	$body .= '<div>';

	$usuario_creacion_correo = '';
	$email_director_aprobacion = '';
	$email_aprobacion_gerencia = '';
	while ($sel = $query->fetch_assoc()) {
		$sigla_correlativo = $sel['sigla_correlativo'];
		$codigo_correlativo = $sel['codigo_correlativo'];
		$area_creacion_id = $sel['area_creacion_id'];
		$usuario_creacion_correo = $sel['usuario_solicitante_correo'];
		$date = date_create($sel["fecha_inicio"]);
		$fecha_inicio_contrato = date_format($date, "Y-m-d");
		$contrato_id_de_la_obs = $sel["contrato_id"];
		$email_director_aprobacion = $sel["email_director_aprobacion"];
		$email_aprobacion_gerencia = $sel["email_aprobacion_gerencia"];

		if ($area_creacion_id == 33) {
			//EN PRODUCCION EL AREA LEGAL ES ID: 33
			//EN DEV EL AREA LEGAL ES ID: 26
			// LEGAL

			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

			$body .= '<thead>';
			$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
			$body .= '<b>Observacion en la Solicitud</b>';
			$body .= '</th>';
			$body .= '</tr>';
			$body .= '</thead>';

			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>De:</b></td>';
			$body .= '<td>' . $sel["area_creacion"] . ' </td>';
			$body .= '</tr>';

			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Empresa ' . $pref_empresa_contacto . ' 1:</b></td>';
			$body .= '<td>' . $sel["empresa_at1"] . '</td>';
			$body .= '</tr>';

			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Empresa ' . $pref_empresa_contacto . ' 2:</b></td>';
			$body .= '<td>' . $sel["empresa_at2"] . '</td>';
			$body .= '</tr>';

			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Detale servicio:</b></td>';
			$body .= '<td>' . $sel["detalle_servicio"] . '</td>';
			$body .= '</tr>';

			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Fecha inicio contrato:</b></td>';
			$body .= '<td>' . $fecha_inicio_contrato . '</td>';
			$body .= '</tr>';

			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Fecha observacion:</b></td>';
			$body .= '<td>' . $sel["created_at"] . '</td>';
			$body .= '</tr>';

			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Observacion:</b></td>';
			$body .= '<td>' . $sel["observaciones"] . '</td>';
			$body .= '</tr>';


			$body .= '</table>';
		} else {
			// CUANDO LOS USUARIOS NO FUERON CONFIGURADOS CORRECTAMENTE EN SUS AREAS RESPECTIVAS.

			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

			$body .= '<thead>';
			$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
			$body .= '<b>Observacion en la Solicitud</b>';
			$body .= '</th>';
			$body .= '</tr>';
			$body .= '</thead>';

			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>De:</b></td>';
			$body .= '<td>' . $sel["area_creacion"] . ' </td>';
			$body .= '</tr>';

			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Empresa ' . $pref_empresa_contacto . ' 1:</b></td>';
			$body .= '<td>' . $sel["empresa_at1"] . '</td>';
			$body .= '</tr>';

			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Empresa ' . $pref_empresa_contacto . ' 2:</b></td>';
			$body .= '<td>' . $sel["empresa_at2"] . '</td>';
			$body .= '</tr>';

			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Detale servicio:</b></td>';
			$body .= '<td>' . $sel["detalle_servicio"] . '</td>';
			$body .= '</tr>';

			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Fecha inicio contrato:</b></td>';
			$body .= '<td>' . $fecha_inicio_contrato . '</td>';
			$body .= '</tr>';

			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Fecha observacion:</b></td>';
			$body .= '<td>' . $sel["created_at"] . '</td>';
			$body .= '</tr>';

			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Observacion:</b></td>';
			$body .= '<td>' . $sel["observaciones"] . '</td>';
			$body .= '</tr>';


			$body .= '</table>';
		}
	}

	$body .= '</div>';

	$body .= '<div>';
	$body .= '<br>';
	$body .= '</div>';

	$body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
	$body .= '<a href="' . $host . '/?sec_id=contrato&sub_sec_id=detalle_solicitud_interno&id=' . $contrato_id_de_la_obs . '" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
	$body .= '<b>Ver Solicitud</b>';
	$body .= '</a>';
	$body .= '</div>';

	$body .= '</html>';
	$body .= "";



	$correos_add = [];
	if (!empty($usuario_creacion_correo)) {
		array_push($correos_add, $usuario_creacion_correo);
	}
	for ($i = 0; $i < count($correos_adjuntos); $i++) {
		if (!empty($correos_adjuntos[$i])) {
			array_push($correos_add, $correos_adjuntos[$i]);
		}
	}
	if (!empty($email_director_aprobacion)) {
		array_push($correos_add, $email_director_aprobacion);
	}
	if (!empty($email_aprobacion_gerencia) && $email_director_aprobacion != $email_aprobacion_gerencia) {
		array_push($correos_add, $email_aprobacion_gerencia);
	}

	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'), env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_observacion_contrato_interno($correos_add);

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];

	$request = [
		"subject" => "Gestion - Sistema Contratos - Observación en la Solicitud de Contrato Locación de Servicio: Código - " . $sigla_correlativo . $codigo_correlativo,
		//"subject" => "Solicitud de proveedor",
		//"subject" => "Solicitud de adenda",
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try {
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if (isset($request["cc"])) {
			foreach ($request["cc"] as $cc) {
				$mail->addAddress($cc);
			}
		}

		if (isset($request["bcc"])) {
			foreach ($request["bcc"] as $bcc) {
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

	} catch (Exception $e) {
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}
}


function send_email_observacion_contrato_interno_gerencia($observacion_id)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$list_emp_cont = $mysqli->query("SELECT tpg.valor FROM tbl_parametros_generales tpg WHERE tpg.codigo = 'empresa_contacto_de_contratos' AND tpg.estado = 1 LIMIT 1");
	$row_emp_cont = $list_emp_cont->fetch_assoc();
	$pref_empresa_contacto = isset($row_emp_cont['valor']) && $row_emp_cont['valor'] == 'Kurax' ? 'Kurax' : 'AT';

	$html = '';

	$sql = "SELECT
				o.id,
				o.contrato_id,
				o.observaciones,
				rs1.nombre AS empresa_at1,
				rs2.nombre AS empresa_at2,
			    SUBSTRING(c.detalle_servicio, 1, 20) AS detalle_servicio,
			    c.fecha_inicio,
				concat(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, '')) AS usuario_creacion,
				p.correo AS usuario_creacion_correo,
				ar.id AS area_creacion_id,
				ar.nombre AS area_creacion,
				o.user_created_id,
				o.created_at,
				concat(IFNULL(ps.nombre, ''),' ', IFNULL(ps.apellido_paterno, '')) AS supervisor,
				concat(IFNULL(pjc.nombre, ''),' ', IFNULL(pjc.apellido_paterno, '')) AS jefe_comercial,
				pjc.correo AS usuario_solicitante_correo,
				co.sigla AS sigla_correlativo,
				c.codigo_correlativo
			FROM cont_observaciones o
				INNER JOIN tbl_usuarios u ON o.user_created_id = u.id
				INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
				INNER JOIN tbl_areas ar ON p.area_id = ar.id
				INNER JOIN cont_contrato c ON o.contrato_id = c.contrato_id
				INNER JOIN tbl_razon_social rs1 ON c.empresa_suscribe_id = rs1.id
				INNER JOIN tbl_razon_social rs2 ON c.empresa_grupo_at_2 = rs2.id
				LEFT JOIN tbl_usuarios us ON c.persona_responsable_id = us.id
				LEFT JOIN tbl_personal_apt ps ON us.personal_id = ps.id
				LEFT JOIN tbl_usuarios ujc ON c.user_created_id = ujc.id
				LEFT JOIN tbl_personal_apt pjc ON ujc.personal_id = pjc.id
				LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			WHERE o.id = " . $observacion_id . " AND o.status = 1
			ORDER BY o.created_at ASC";


	$query = $mysqli->query($sql);

	$contrato_id_de_la_obs = "";

	$body = "";
	$body .= '<html>';
	$body .= '<div>';

	$usuario_creacion_correo = '';

	while ($sel = $query->fetch_assoc()) {
		$sigla_correlativo = $sel['sigla_correlativo'];
		$codigo_correlativo = $sel['codigo_correlativo'];
		$area_creacion_id = $sel['area_creacion_id'];
		$usuario_creacion_correo = $sel['usuario_solicitante_correo'];
		$date = date_create($sel["fecha_inicio"]);
		$fecha_inicio_contrato = date_format($date, "Y-m-d");
		$contrato_id_de_la_obs = $sel["contrato_id"];



		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

		$body .= '<thead>';
		$body .= '<tr>';
		$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
		$body .= '<b>Observacion en la Solicitud</b>';
		$body .= '</th>';
		$body .= '</tr>';
		$body .= '</thead>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>De:</b></td>';
		$body .= '<td>' . $sel["area_creacion"] . ' </td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Empresa ' . $pref_empresa_contacto . ' 1:</b></td>';
		$body .= '<td>' . $sel["empresa_at1"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Empresa ' . $pref_empresa_contacto . ' 2:</b></td>';
		$body .= '<td>' . $sel["empresa_at2"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Detale servicio:</b></td>';
		$body .= '<td>' . $sel["detalle_servicio"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Fecha inicio contrato:</b></td>';
		$body .= '<td>' . $fecha_inicio_contrato . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Fecha observacion:</b></td>';
		$body .= '<td>' . $sel["created_at"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Observacion:</b></td>';
		$body .= '<td>' . $sel["observaciones"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td colspan="2" style="background-color: #ffffdd"><b>Nota: Despues de Resolver la observación hacer click en el boton "Notificar a Lourdes Brito". </b></td>';
		$body .= '</tr>';


		$body .= '</table>';
	}

	$body .= '</div>';

	$body .= '<div>';
	$body .= '<br>';
	$body .= '</div>';

	$body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
	$body .= '<a href="' . $host . '/?sec_id=contrato&sub_sec_id=detalle_solicitud_interno&id=' . $contrato_id_de_la_obs . '" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
	$body .= '<b>Ver Solicitud</b>';
	$body .= '</a>';
	$body .= '</div>';

	$body .= '</html>';
	$body .= "";

	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'), env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_observacion_gerencia($usuario_creacion_correo);

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];

	$request = [
		"subject" => "Gestion - Sistema Contratos - Observación en la Solicitud de Contrato Locación de Servicio: Código - " . $sigla_correlativo . $codigo_correlativo,
		//"subject" => "Solicitud de proveedor",
		//"subject" => "Solicitud de adenda",
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try {
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if (isset($request["cc"])) {
			foreach ($request["cc"] as $cc) {
				$mail->addAddress($cc);
			}
		}

		if (isset($request["bcc"])) {
			foreach ($request["bcc"] as $bcc) {
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

	} catch (Exception $e) {
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}
}

function send_email_solicitud_contrato_interno_firmada($contrato_id, $correos_adjuntos)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];
	$empresa_suscribe_id = 0;

	$list_emp_cont = $mysqli->query("SELECT tpg.valor FROM tbl_parametros_generales tpg WHERE tpg.codigo = 'empresa_contacto_de_contratos' AND tpg.estado = 1 LIMIT 1");
	$row_emp_cont = $list_emp_cont->fetch_assoc();
	$pref_empresa_contacto = isset($row_emp_cont['valor']) && $row_emp_cont['valor'] == 'Kurax' ? 'Kurax' : 'AT';

	$sel_query = $mysqli->query("
	SELECT	
		c.empresa_suscribe_id,
		rs1.nombre AS empresa_at1,
		rs2.nombre AS empresa_at2,
		c.detalle_servicio,
		CONCAT(c.periodo_numero, ' ', p.nombre) AS periodo,
		concat(IFNULL(per.nombre, ''),' ', IFNULL(per.apellido_paterno, '')) AS usuario_creacion,
		per.correo AS usuario_creacion_correo,
		pab.correo AS correo_abogado,
		c.fecha_inicio,
		ar.nombre AS area_creacion,
		co.sigla AS sigla_correlativo,
		c.codigo_correlativo,
		c.empresa_grupo_at_2,
		c.renovacion_automatica
	FROM 
		cont_contrato c
		INNER JOIN cont_periodo p ON c.periodo = p.id
		INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
		INNER JOIN tbl_personal_apt per ON u.personal_id = per.id

		LEFT JOIN tbl_usuarios uab ON c.abogado_id = uab.id
		LEFT JOIN tbl_personal_apt pab ON uab.personal_id = pab.id

		INNER JOIN tbl_areas ar ON per.area_id = ar.id
		INNER JOIN tbl_razon_social rs1 ON c.empresa_suscribe_id = rs1.id
		INNER JOIN tbl_razon_social rs2 ON c.empresa_grupo_at_2 = rs2.id
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
	WHERE 
		c.contrato_id = $contrato_id
	");

	$body = "";
	$body .= '<html>';

	$usuario_creacion_correo = '';
	$correos_add = [];
	while ($sel = $sel_query->fetch_assoc()) {
		$empresa_suscribe_id = $sel['empresa_suscribe_id'];
		$empresa_grupo_at_2 = $sel['empresa_grupo_at_2'];

		$empresa_at1 = $sel['empresa_at1'];
		$empresa_at2 = $sel['empresa_at2'];

		if (!empty($sel['correo_abogado'])) {
			array_push($correos_add, $sel['correo_abogado']);
		}

		$renovacion_automatica = $sel['renovacion_automatica'] == 1 ? 'SI' : 'NO';

		$sigla_correlativo = $sel['sigla_correlativo'];
		$codigo_correlativo = $sel['codigo_correlativo'];

		$usuario_creacion_correo = $sel['usuario_creacion_correo'];

		$date = date_create($sel["fecha_inicio"]);
		$fecha_inicio_contrato = date_format($date, "Y/m/d");

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

		$body .= '<thead>';
		$body .= '<tr>';
		$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
		$body .= '<b>CONTRATO FIRMADO</b>';
		$body .= '</th>';
		$body .= '</tr>';
		$body .= '</thead>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Empresa ' . $pref_empresa_contacto . ' 1:</b></td>';
		$body .= '<td>' . $sel["empresa_at1"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Empresa ' . $pref_empresa_contacto . ' 2:</b></td>';
		$body .= '<td>' . $sel["empresa_at2"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Periodo:</b></td>';
		$body .= '<td>' . $sel["periodo"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Usuario solicitante:</b></td>';
		$body .= '<td>' . $sel["usuario_creacion"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Fecha inicio:</b></td>';
		$body .= '<td>' . $fecha_inicio_contrato . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Renovación Automática:</b></td>';
		$body .= '<td>' . $renovacion_automatica . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Detalle servicio:</b></td>';
		$body .= '<td>' . $sel["detalle_servicio"] . '</td>';
		$body .= '</tr>';

		$body .= '</table>';
		$body .= '</div>';
	}
	$body .= '<div>';
	$body .= '<br>';
	$body .= '</div>';

	$body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
	$body .= '<a href="' . $host . '/?sec_id=contrato&sub_sec_id=detalle_solicitud_interno&id=' . $contrato_id . '" target="_blank">';
	$body .= '<button style="background-color: green; color: white; cursor: pointer; width: 50%;">';
	$body .= '<b>Ver Contrato</b>';
	$body .= '</button>';
	$body .= '</a>';
	$body .= '</div>';

	$body .= '</html>';
	$body .= "";


	if (!empty($usuario_creacion_correo)) {
		array_push($correos_add, $usuario_creacion_correo);
	}
	for ($i = 0; $i < count($correos_adjuntos); $i++) {
		if (!empty($correos_adjuntos[$i])) {
			array_push($correos_add, $correos_adjuntos[$i]);
		}
	}
	if ($empresa_suscribe_id != 0) {
		$sql_contador_tesorero = "
		SELECT 
			p.correo
		FROM
			cont_usuarios_razones_sociales urs
			INNER JOIN tbl_usuarios u ON urs.user_id = u.id
			INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		WHERE
			urs.razon_social_id = " . $empresa_suscribe_id . "
			AND p.estado = 1  AND u.estado = 1 
		";
		$sel_query = $mysqli->query($sql_contador_tesorero);

		$row_count = $sel_query->num_rows;

		if ($row_count > 0) {
			while ($sel = $sel_query->fetch_assoc()) {
				if (!empty($sel['correo'])) {
					array_push($correos_add, $sel['correo']);
				}
			}
		}
	}


	if ($empresa_grupo_at_2 != 0) {
		$sql_contador_tesorero = "
		SELECT 
			p.correo
		FROM
			cont_usuarios_razones_sociales urs
			INNER JOIN tbl_usuarios u ON urs.user_id = u.id
			INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		WHERE
			urs.razon_social_id = " . $empresa_grupo_at_2 . "
			AND p.estado = 1  AND u.estado = 1 
		";
		$sel_query = $mysqli->query($sql_contador_tesorero);
		$row_count = $sel_query->num_rows;
		if ($row_count > 0) {
			while ($sel = $sel_query->fetch_assoc()) {
				if (!empty($sel['correo'])) {
					array_push($correos_add, $sel['correo']);
				}
			}
		}
	}



	$correos = new Correos(env('SEND_EMAIL'), env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_solicitud_contrato_interno_firmada($correos_add);

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];

	$request = [
		"subject" => "Gestion - Sistema Contratos - Contrato Interno Firmado - Código - " . $sigla_correlativo . $codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try {
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if (isset($request["cc"])) {
			foreach ($request["cc"] as $cc) {
				$mail->addAddress($cc);
			}
		}

		if (isset($request["bcc"])) {
			foreach ($request["bcc"] as $bcc) {
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

	} catch (Exception $e) {
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}
}

function send_email_adenda_contrato_interno_firmada($adenda_id, $reenvio = false)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$sel_query = $mysqli->query("
	SELECT 
		a.id, 
		a.created_at, 
		co.sigla, 
		c.codigo_correlativo, 
		c.contrato_id, 
		tc.nombre,
		concat( IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS usuario_solicitante,
		rs1.nombre AS empresa_at_1,
		rs2.nombre AS empresa_at_2,
		ar.nombre AS nombre_area, 
		tp.correo,
		pab.correo as correo_abogado
	FROM 
		cont_adendas AS a 
		INNER JOIN cont_contrato AS c ON c.contrato_id = a.contrato_id
		INNER JOIN tbl_razon_social rs1 ON c.empresa_suscribe_id = rs1.id
		INNER JOIN tbl_razon_social rs2 ON c.empresa_grupo_at_2 = rs2.id
		INNER JOIN cont_tipo_contrato AS tc ON tc.id = c.tipo_contrato_id
		INNER JOIN tbl_usuarios AS tu	ON tu.id = a.user_created_id
		INNER JOIN tbl_personal_apt AS tp ON tp.id = tu.personal_id

		LEFT JOIN tbl_usuarios AS uab ON uab.id = a.abogado_id
		LEFT JOIN tbl_personal_apt AS pab ON pab.id = uab.personal_id

		INNER JOIN tbl_areas AS ar ON tp.area_id = ar.id
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
	WHERE 
		a.id = $adenda_id
	");


	$body = "";
	$body .= '<html>';

	$usuario_creacion_correo = '';
	$correos_add = [];
	while ($sel = $sel_query->fetch_assoc()) {
		$contrato_id = $sel['contrato_id'];
		$sigla_correlativo = $sel['sigla'];
		$codigo_correlativo = $sel['codigo_correlativo'];
		$empresa_at_1 = $sel['empresa_at_1'];
		$empresa_at_2 = $sel['empresa_at_2'];
		$fecha_solicitud = $sel['created_at'];
		$usuario_solicitante = $sel['usuario_solicitante'];
		if (!empty($sel['correo'])) {
			array_push($correos_add, $sel['correo']);
		}

		if (!empty($sel['correo_abogado'])) {
			array_push($correos_add, $sel['correo_abogado']);
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
		$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Area Solicitante:</b></td>';
		$body .= '<td>' . $sel["nombre_area"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Usuario solicitante:</b></td>';
		$body .= '<td>' . $sel["usuario_solicitante"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Fecha Solicitud:</b></td>';
		$body .= '<td>' . $sel["created_at"] . '</td>';
		$body .= '</tr>';

		$body .= '</table>';
		$body .= '</div>';


		$query = $mysqli->query("
		SELECT id,
			adenda_id,
			nombre_tabla,
			valor_original,
			nombre_menu_usuario,
			nombre_campo_usuario,
			nombre_campo,
			tipo_valor,
			valor_varchar,
			valor_int,
			valor_date,
			valor_decimal,
			valor_select_option,
			status
		FROM cont_adendas_detalle
		WHERE 
			adenda_id = " . $adenda_id . "
			AND tipo_valor != 'id_tabla' AND tipo_valor != 'registro'
			AND status = 1
		");

		$row_count = $query->num_rows;
		$numero_adenda_detalle = 0;

		if ($row_count > 0) {
			$body .= '<div>';
			$body .= '<br>';
			$body .= '</div>';

			$body .= '<div>';
			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

			$body .= '<thead>';

			$body .= '<tr>';
			$body .= '<th colspan="5" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
			$body .= '<b>Detalle</b>';
			$body .= '</th>';
			$body .= '</tr>';


			$body .= '</thead>';

			$body .= '<tr>';
			$body .= '<td align="center" style="background-color: #ffffdd; width: 20px;"><b>#</b></td>';
			$body .= '<td align="center" style="background-color: #ffffdd;"><b>Menu:</b></td>';
			$body .= '<td align="center" style="background-color: #ffffdd;"><b>Campo:</b></td>';
			$body .= '<td align="center" style="background-color: #ffffdd;"><b>Valor Original:</b></td>';
			$body .= '<td align="center" style="background-color: #ffffdd;"><b>Nuevo Valor:</b></td>';
			$body .= '</tr>';

			while ($row = $query->fetch_assoc()) {
				$nombre_menu_usuario = $row["nombre_menu_usuario"];
				$nombre_campo_usuario = $row["nombre_campo_usuario"];
				$valor_original = $row["valor_original"];
				$tipo_valor = $row["tipo_valor"];
				if ($tipo_valor == 'varchar') {
					$nuevo_valor = $row['valor_varchar'];
				} else if ($tipo_valor == 'int') {
					$nuevo_valor = $row['valor_int'];
				} else if ($tipo_valor == 'date') {
					$nuevo_valor = $row['valor_date'];
				} else if ($tipo_valor == 'decimal') {
					$nuevo_valor = $row['valor_decimal'];
				} else if ($tipo_valor == 'select_option') {
					$nuevo_valor = $row['valor_select_option'];
				}
				$numero_adenda_detalle++;

				$body .= '<tr>';
				$body .= '<td>' . $numero_adenda_detalle . '</td>';
				$body .= '<td>' . $nombre_menu_usuario . '</td>';
				$body .= '<td>' . $nombre_campo_usuario . '</td>';
				$body .= '<td>' . $valor_original . '</td>';
				$body .= '<td>' . $nuevo_valor . '</td>';
				$body .= '</tr>';
			}
			$body .= '</table>';
			$body .= '</div>';
		}


		$query = $mysqli->query("
		SELECT id,
			adenda_id,
			nombre_tabla,
			valor_original,
			nombre_menu_usuario,
			nombre_campo_usuario,
			nombre_campo,
			tipo_valor,
			valor_varchar,
			valor_int,
			valor_date,
			valor_decimal,
			valor_select_option,
			status
		FROM cont_adendas_detalle
		WHERE adenda_id = " . $adenda_id . "
		AND tipo_valor = 'registro'
		AND status = 1");
		$row_count = $query->num_rows;
		$numero_adenda_detalle = 0;
		if ($row_count > 0) {
			while ($row = $query->fetch_assoc()) {
				if ($row["nombre_menu_usuario"] == 'Cuenta Bancaria') {
					$query_pro = "
					SELECT 
						rl.id, 
						rl.dni_representante, 
						rl.nombre_representante, 
						rl.nro_cuenta_detraccion,
						rl.id_banco, 
						b.nombre as banco_representante, 
						rl.nro_cuenta, 
						rl.nro_cci, 
						rl.vigencia_archivo_id,
						rl.dni_archivo_id
					FROM 
						cont_representantes_legales rl
						LEFT JOIN tbl_bancos b on b.id = rl.id_banco
					WHERE 
						rl.id IN ('" . $row["valor_int"] . "')
					";

					$valores_originales = [];
					$valores_nuevos = [];
					$list_query = $mysqli->query($query_pro);
					while ($li = $list_query->fetch_assoc()) {
						if ($li["id"] == $row["valor_int"]) {
							$valores_nuevos[] = $li;
						}
					}
					$body .= '<div>';
					$body .= '<br>';
					$body .= '</div>';

					$body .= '<div>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

					$body .= '<thead>';

					$body .= '<tr>';
					$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
					$body .= '<b>Nueva Cuenta Bancaria</b>';
					$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td align="center" style="background-color: #ffffdd;"><b>Campo:</b></td>';
					$body .= '<td align="center" style="background-color: #ffffdd;"><b>Nuevo Valor:</b></td>';
					$body .= '</tr>';

					$body .= '</thead>';

					$body .= '<tr>';
					$body .= '<td >Banco</td>';
					$body .= '<td >' . $valores_nuevos[0]["banco_representante"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Nro Cuenta</td>';
					$body .= '<td >' . $valores_nuevos[0]["nro_cuenta"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Nro CCI</td>';
					$body .= '<td >' . $valores_nuevos[0]["nro_cci"] . '</td>';
					$body .= '</tr>';



					$body .= '</table>';
					$body .= '</div>';
				}

				if ($row["nombre_menu_usuario"] == 'Contraprestación') {
					$query_cont = "
				SELECT 
					c.id,
					c.moneda_id,
					m.nombre AS tipo_moneda,
					m.simbolo AS tipo_moneda_simbolo,
					c.subtotal,
					c.igv,
					c.monto,
					c.forma_pago_detallado,
					c.tipo_comprobante_id,
					t.nombre AS tipo_comprobante,
					c.plazo_pago
				FROM 
					cont_contraprestacion c
					INNER JOIN tbl_moneda m ON c.moneda_id = m.id
					INNER JOIN cont_tipo_comprobante t ON c.tipo_comprobante_id = t.id
				WHERE 
					c.id IN ('" . $row["valor_int"] . "')
				";

					$valores_originales = [];
					$valores_nuevos = [];
					$list_query = $mysqli->query($query_cont);
					while ($li = $list_query->fetch_assoc()) {
						if ($li["id"] == $row["valor_int"]) {
							$valores_nuevos[] = $li;
						}
					}
					$body .= '<div>';
					$body .= '<br>';
					$body .= '</div>';

					$body .= '<div>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

					$body .= '<thead>';

					$body .= '<tr>';
					$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
					$body .= '<b>Nueva Contraprestación</b>';
					$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td align="center" style="background-color: #ffffdd;"><b>Campo:</b></td>';
					$body .= '<td align="center" style="background-color: #ffffdd;"><b>Nuevo Valor:</b></td>';
					$body .= '</tr>';

					$body .= '</thead>';

					$body .= '<tr>';
					$body .= '<td>Tipo de moneda</td>';
					$body .= '<td>' . $valores_nuevos[0]["tipo_moneda"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Subtotal</td>';
					$body .= '<td>' . $valores_nuevos[0]["subtotal"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>IGV</td>';
					$body .= '<td>' . $valores_nuevos[0]["igv"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Monto Bruto</td>';
					$body .= '<td>' . $valores_nuevos[0]["monto"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Tipo de comprobante a emitir</td>';
					$body .= '<td>' . $valores_nuevos[0]["tipo_comprobante"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Plazo de Pago</td>';
					$body .= '<td>' . $valores_nuevos[0]["plazo_pago"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Forma de pago</td>';
					$body .= '<td>' . $valores_nuevos[0]["forma_pago_detallado"] . '</td>';
					$body .= '</tr>';


					$body .= '</table>';
					$body .= '</div>';
				}
			}
		}
	}

	$body .= '<div>';
	$body .= '<br>';
	$body .= '</div>';

	$body .= '<div style="width: 700px; text-align: center; font-family: arial;">';
	$body .= '<a href="' . $host . '/?sec_id=contrato&sub_sec_id=detalle_solicitud_interno&id=' . $contrato_id . '" target="_blank">';
	$body .= '<button style="background-color: green; color: white; cursor: pointer; width: 50%;">';
	$body .= '<b>Ver Contrato y Adenda de Contrato Interno</b>';
	$body .= '</button>';
	$body .= '</a>';
	$body .= '</div>';

	$body .= '</html>';
	$body .= "";


	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'), env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_adenda_contrato_interno_firmada($correos_add);

	$cc = $lista_correos['cc'];

	$titulo_email = "Gestion - Sistema Contratos - Adenda Firmada de Contrato Interno: Código - ";
	if ($reenvio) {
		$titulo_email = "Gestion - Sistema Contratos - Reenvío de Adenda Firmada de Contrato Interno: Código - ";
	}

	$bcc = $lista_correos['bcc'];

	$request = [
		"subject" => $titulo_email . $sigla_correlativo . $codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try {
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if (isset($request["cc"])) {
			foreach ($request["cc"] as $cc) {
				$mail->addAddress($cc);
			}
		}

		if (isset($request["bcc"])) {
			foreach ($request["bcc"] as $bcc) {
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

	} catch (Exception $e) {
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "editar_solicitud") {
	$usuario_id = $login ? $login['id'] : null;
	if ((int) $usuario_id > 0) {
		$created_at = date("Y-m-d H:i:s");
		$contrato_id = $_POST["contrato_id"];
		$nombre_tabla = trim($_POST["nombre_tabla"]);
		$valor_original = trim($_POST["valor_original"]);
		$nombre_campo = trim($_POST["nombre_campo"]);
		$nombre_menu_usuario = trim($_POST["nombre_menu_usuario"]);
		$nombre_campo_usuario = trim($_POST["nombre_campo_usuario"]);
		$tipo_valor = trim($_POST["tipo_valor"]);

		$query_contrato = "SELECT c.tipo_contrato_id FROM cont_contrato AS c WHERE c.contrato_id = " . $contrato_id;
		$data_contrato = $mysqli->query($query_contrato);
		$data_contrato = $data_contrato->fetch_assoc();

		if ($tipo_valor == 'varchar') {
			$valor_varchar = "'" . replace_invalid_caracters(trim($_POST["valor_varchar"]))  . "'";
			$valor_int = "NULL";
			$valor_date = "NULL";
			$valor_decimal = "NULL";
			$valor_select_option = "NULL";
			$valor_id_tabla = "NULL";
		} else if ($tipo_valor == 'textarea') {
			$tipo_valor = 'varchar';
			$valor_varchar = "'" . replace_invalid_caracters(trim($_POST["valor_textarea"])) . "'";
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
			$valor_decimal = str_replace(",", "", trim($_POST["valor_decimal"]));
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

		if ($tipo_valor == 'select_option' && $nombre_tabla == 'cont_adelantos') {
			$valor_int = "NULL";
		} elseif ($tipo_valor == 'select_option' && $nombre_campo == 'ubigeo_id') {
			$valor_int = "NULL";
			$valor_select_option = trim($_POST["ubigeo_text_nuevo"]);
		} elseif ($tipo_valor == 'varchar' && $nombre_campo == 'cc_id') {
			if (!empty(trim($_POST["valor_varchar"]))) {
				$query_centro_de_costo = "
				SELECT 
					nombre
				FROM 
					tbl_locales
				WHERE 
					cc_id = $valor_varchar
				";

				$query = $mysqli->query($query_centro_de_costo);
				$row_count = $query->num_rows;

				if ($row_count > 0) {
					$row = $query->fetch_assoc();
					$nombre_local = $row["nombre"];

					$result["http_code"] = 400;
					$result["error"] = 'El local "' . $nombre_local . '" posee el centro de costos "' . trim($_POST["valor_varchar"]) . '"';
					exit(json_encode($result));
				}
			}
		} elseif ($tipo_valor == 'varchar' && $nombre_campo == 'nombre_tienda') {
			$valor_varchar = "'" . str_replace(" At ", " AT ", ucwords(strtolower(trim($_POST["valor_varchar"])))) . "'";
		}

		$query_insert = "
		INSERT INTO cont_auditoria
		(
		contrato_id,
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
		" . $contrato_id . ",
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

		if ($tipo_valor == 'select_option' && trim($_POST["nombre_tabla"]) == 'cont_adelantos') {
			$tipo_valor = 'varchar';
			$valor_varchar = "'" . trim($_POST["valor_select_option_id"]) . "'";
		} elseif ($tipo_valor == 'select_option' && $nombre_campo == 'ubigeo_id') {
			$tipo_valor = 'varchar';
			$valor_varchar = "'" . trim($_POST["ubigeo_id_nuevo"])  . "'";
		} elseif ($tipo_valor == 'varchar' && $nombre_campo == 'nombre_tienda') {
			$select_etapa = "
			SELECT 
				etapa_id
			FROM 
				cont_contrato
			WHERE
				contrato_id = $contrato_id
			";

			$query = $mysqli->query($select_etapa);
			$row = $query->fetch_assoc();
			$etapa_id = $row["etapa_id"];

			if ($etapa_id != 1) {
				$select_local_id = "
				SELECT
					id
				FROM
					tbl_locales
				WHERE 
					contrato_id = $contrato_id
				";

				$query = $mysqli->query($select_local_id);
				$row_count = $query->num_rows;

				if ($row_count == 1) {
					$row = $query->fetch_assoc();
					$local_id = $row["id"];

					$update_nombre_del_local = "
					UPDATE
						tbl_locales
					SET
						nombre = $valor_varchar
					WHERE 
						id = $local_id
					";

					$mysqli->query($update_nombre_del_local);
				}
			}
		} elseif ($tipo_valor == 'select_option' && ($nombre_campo == 'persona_responsable_id' || $nombre_campo == 'jefe_comercial_id')) {
			$usuario_comercial_nuevo_id = $valor_int;

			$select_usuario_antiguo = "
			SELECT 
				$nombre_campo,
				etapa_id
			FROM 
				cont_contrato
			WHERE
				contrato_id = $contrato_id
			";

			$resultado = $mysqli->query($select_usuario_antiguo);
			$fila = $resultado->fetch_row();
			$usuario_comercial_antiguo_id = $fila[0];
			$etapa_id = $fila[1];

			if ($etapa_id != 1) {
				$select_local_id = "
				SELECT
					id
				FROM
					tbl_locales
				WHERE 
					contrato_id = $contrato_id
				";

				$query = $mysqli->query($select_local_id);
				$row_count = $query->num_rows;

				if ($row_count == 1) {
					$row = $query->fetch_assoc();
					$local_id = $row["id"];

					if ($valor_original != 'Sin asignar' && $usuario_comercial_antiguo_id > 0) {
						$update_usuario_comercial_antiguo_quitar_local = "
						UPDATE 
							tbl_usuarios_locales
						SET 
							estado = 0
						WHERE 
							local_id = $local_id
							AND usuario_id = $usuario_comercial_antiguo_id
						";

						$mysqli->query($update_usuario_comercial_antiguo_quitar_local);
					}

					$select_usuario_comercial_nuevo_existe = "
					SELECT
						id
					FROM
						tbl_usuarios_locales
					WHERE 
						local_id = $local_id
						AND usuario_id = $usuario_comercial_nuevo_id
					";

					$query = $mysqli->query($select_usuario_comercial_nuevo_existe);
					$row_count = $query->num_rows;

					if ($row_count == 1) {
						$update_usuario_comercial_ya_existente_al_local = "
						UPDATE
							tbl_usuarios_locales
						SET
							estado = 1
						WHERE 
							local_id = $local_id
							AND usuario_id = $usuario_comercial_nuevo_id
						";

						$mysqli->query($update_usuario_comercial_ya_existente_al_local);
					} elseif ($row_count == 0) {
						$insert_usuario_comercial_al_local = "
						INSERT INTO tbl_usuarios_locales (
							usuario_id,
							local_id,
							estado
						) VALUES (
							$usuario_comercial_nuevo_id, 
							$local_id,
							1
						)";

						$mysqli->query($insert_usuario_comercial_al_local);
					}
				}
			}
		} elseif ($nombre_tabla == 'cont_contrato' && $nombre_campo == 'fecha_vencimiento_proveedor') {
			if ((int) $data_contrato['tipo_contrato_id'] == 7) {
				$update_alerta = "UPDATE cont_contrato SET alerta_enviada_id = NULL WHERE contrato_id = " . $contrato_id;
				$resultado = $mysqli->query($update_alerta);
			}
		}

		$mysqli->query($query_insert);
		$insert_id = mysqli_insert_id($mysqli);
		$error = '';
		if ($mysqli->error) {
			$result["insert_error"] = $mysqli->error . $query_insert;
		} else {

			// if($nombre_campo != 'abogado_id'){
			// 	send_email_cambio_contrato($insert_id);
			// }

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
				if ($nombre_tabla == "cont_contrato") {
					$query_id_tabla = 'cargo_aprobador_id';
				} else if ($nombre_tabla == 'cont_locacion') {
					$query_id_tabla = 'idlocacion';
					$valor_id_tabla = $id_tabla;
				} else {
					$query_id_tabla = 'id';
					$valor_id_tabla = $id_tabla;
				}
			} else {
				$query_id_tabla = 'contrato_id';
				$valor_id_tabla = $contrato_id;
			}

			if (empty($nombre_tabla) || empty($nombre_campo) || empty($nuevo_valor) || empty($query_id_tabla) || empty($valor_id_tabla)) {
				$result["update_error"] = 'Algunos del los campos esta vacío';
			} else if (substr($nombre_tabla, 0, 4) != 'cont') {
				$result["update_error"] = 'La tabla a actualizar no pertenece al sistema de contratos';
			} else {
				$query_update = "
				UPDATE " . $nombre_tabla . " 
				SET 
					" . $nombre_campo . " = " . $nuevo_valor . "
				WHERE " . $query_id_tabla . " = '" . $valor_id_tabla . "'";
				$mysqli->query($query_update);

				if ($mysqli->error) {
					$result["update_error"] = $mysqli->error . $query_update;
				}
			}
		}

		$result["result"] = 'ok';
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
	} else {
		$result["http_code"] = 400;
		$result["error"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y volver a hacer clic en el botón: Editar.";
	}

	echo json_encode($result);
	exit();
}


if (isset($_POST["accion"]) && $_POST["accion"] === "sec_con_detalle_agregar_representante_legal") {
	$usuario_id = $login ? $login['id'] : null;
	$contrato_id = $_POST["contrato_id"];
	$dniRepresentante = $_POST["dniRepresentante"];
	$nombreRepresentante = $_POST["nombreRepresentante"];
	$banco = $_POST["banco"];
	$nro_cuenta = $_POST["nro_cuenta"];
	$nro_cci = $_POST["nro_cci"];
	$vig_arch_id = 0;
	$dni_arch_id = 0;
	$error = '';


	$path = "/var/www/html/files_bucket/contratos/solicitudes/contratos_internos/";

	if (isset($_FILES['sec_con_det_prov_file_vigencia_nuevo_rl']) && $_FILES['sec_con_det_prov_file_vigencia_nuevo_rl']['error'] === UPLOAD_ERR_OK) {
		if (!is_dir($path)) mkdir($path, 0777, true);
		$filename = $_FILES['sec_con_det_prov_file_vigencia_nuevo_rl']['name'];
		$filenametem = $_FILES['sec_con_det_prov_file_vigencia_nuevo_rl']['tmp_name'];
		$filesize = $_FILES['sec_con_det_prov_file_vigencia_nuevo_rl']['size'];
		$fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		$valid_extensions = array('jpeg', 'jpg', 'png', 'pdf');
		if (in_array($fileExt, $valid_extensions)) {
			$nombre_archivo_guardar = "_VIG_";
			if ($nombre_archivo_guardar != "") {
				$nombre_archivo = $contrato_id . $nombre_archivo_guardar . date('YmdHis') . "." . $fileExt;
				move_uploaded_file($filenametem, $path . $nombre_archivo);
				$comando = "INSERT INTO cont_archivos 
							(contrato_id,tipo_archivo_id,nombre,extension,ruta,size,status,
								user_created_id, created_at, user_updated_id, updated_at)
							VALUES('" . $contrato_id . "','2','" . $nombre_archivo . "','" . $fileExt
					. "','" . $path . "','" . $filesize . "','1','" . $login["id"] . "', '"
					. date('Y-m-d H:i:s') . "','" . $login["id"] . "', '" . date('Y-m-d H:i:s') . "')";
				$mysqli->query($comando);
				$vig_arch_id = mysqli_insert_id($mysqli);
				$message = "Datos guardados correctamente";
				$status = true;
			} else {
				$message = "El tipo de archivo a guardar no fue identificado, por favor consulte con el area de sistemas";
				$status = false;
			}
		} else {
			$message = "La extension del file no es aceptado, solo son aceptado: jpeg, jpg, png, pdf";
			$status = false;
		}
	} else {
		$message = "Tiene que seleccionar un archivo :)";
		$status = false;
	}

	if (isset($_FILES['sec_con_det_prov_file_dni_nuevo_rl']) && $_FILES['sec_con_det_prov_file_dni_nuevo_rl']['error'] === UPLOAD_ERR_OK) {
		if (!is_dir($path)) mkdir($path, 0777, true);
		$filename = $_FILES['sec_con_det_prov_file_dni_nuevo_rl']['name'];
		$filenametem = $_FILES['sec_con_det_prov_file_dni_nuevo_rl']['tmp_name'];
		$filesize = $_FILES['sec_con_det_prov_file_dni_nuevo_rl']['size'];
		$fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		$valid_extensions = array('jpeg', 'jpg', 'png', 'pdf');
		if (in_array($fileExt, $valid_extensions)) {
			$nombre_archivo_guardar = "_DNI_";
			if ($nombre_archivo_guardar != "") {
				$nombre_archivo = $contrato_id . $nombre_archivo_guardar . date('YmdHis') . "." . $fileExt;
				move_uploaded_file($filenametem, $path . $nombre_archivo);
				$comando = "INSERT INTO cont_archivos 
							(contrato_id,tipo_archivo_id,nombre,extension,ruta,size,status,
								user_created_id, created_at, user_updated_id, updated_at)
							VALUES('" . $contrato_id . "','3','" . $nombre_archivo . "','" . $fileExt
					. "','" . $path . "','" . $filesize . "','1','" . $login["id"] . "', '"
					. date('Y-m-d H:i:s') . "','" . $login["id"] . "', '" . date('Y-m-d H:i:s') . "')";
				$mysqli->query($comando);
				$dni_arch_id = mysqli_insert_id($mysqli);
				$message = "Datos guardados correctamente";
				$status = true;
			} else {
				$message = "El tipo de archivo a guardar no fue identificado, por favor consulte con el area de sistemas";
				$status = false;
			}
		} else {
			$message = "La extension del file no es aceptado, solo son aceptado: jpeg, jpg, png, pdf";
			$status = false;
		}
	} else {
		$message = "Tiene que seleccionar un archivo :)";
		$status = false;
	}

	$query_insert = "INSERT INTO cont_representantes_legales 
					(contrato_id, dni_representante, nombre_representante, id_banco, nro_cuenta, nro_cci,
					vigencia_archivo_id, dni_archivo_id, id_user_created, created_at) 
					VALUES (" . $contrato_id . ",'" . $dniRepresentante . "','" . $nombreRepresentante . "',"
		. $banco . ",'" . $nro_cuenta . "','" . $nro_cci . "'," . $vig_arch_id . ","
		. $dni_arch_id . "," . $usuario_id . ",now())";
	$mysqli->query($query_insert);

	if ($mysqli->error) {
		$error .= $mysqli->error;
		echo $query_insert;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = "ok";
	$result["error"] = $error;

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_observaciones_contrato") {
	$usuario_id = $login ? $login['id'] : null;
	$created_at = date('Y-m-d H:i:s');
	$error = '';

	$correos_adjuntos = isset($_POST["correos_adjuntos"]) ? $_POST["correos_adjuntos"] : [];
	// INICIO INSERTAR OBSERVACIONES
	$query_insert = " INSERT INTO cont_observaciones(
	contrato_id,
	observaciones,
	status,
	user_created_id,
	created_at)
	VALUES(
	" . $_POST["contrato_id"] . ",
	'" . $_POST["observaciones"] . "',
	1,
	" . $usuario_id . ",
	'" . $created_at . "' )";
	$insert_id_observacion = 0;
	$mysqli->query($query_insert);
	if ($mysqli->error) {
		$error .= $mysqli->error;
		echo $query_insert;
	}
	$insert_id_observacion = $mysqli->insert_id;
	// FIN INSERTAR OBSERVACIONES

	$query = "SELECT email FROM cont_contrato_correos WHERE status = 1 AND contrato_id =" . $_POST["contrato_id"];
	$list_query = $mysqli->query($query);
	$correos_guardados = [];
	while ($li = $list_query->fetch_assoc()) {
		array_push($correos_guardados, $li['email']);
	}

	for ($i = 0; $i < count($correos_adjuntos); $i++) {
		if (!in_array($correos_adjuntos[$i], $correos_guardados)) {
			$query_insert = " INSERT INTO cont_contrato_correos(
				contrato_id,
				email,
				status,
				user_created_id,
				created_at)
				VALUES(
				" . $_POST["contrato_id"] . ",
				'" . $correos_adjuntos[$i] . "',
				1,
				" . $usuario_id . ",
				'" . $created_at . "' )";
			$mysqli->query($query_insert);
		}
	}

	if ($_POST["tipo_observacion"] === 'contrato_interno') {
		send_email_observacion_contrato_interno($insert_id_observacion, $correos_adjuntos);
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = "ok";
	$result["error"] = $error;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_observaciones_contrato_gerencia") {
	$usuario_id = $login ? $login['id'] : null;
	$created_at = date('Y-m-d H:i:s');
	$error = '';

	// INICIO INSERTAR OBSERVACIONES
	$query_insert = " INSERT INTO cont_observaciones(
	contrato_id,
	observaciones,
	status,
	user_created_id,
	created_at)
	VALUES(
	" . $_POST["contrato_id"] . ",
	'" . $_POST["observaciones"] . "',
	1,
	" . $usuario_id . ",
	'" . $created_at . "' )";
	$insert_id_observacion = 0;
	$mysqli->query($query_insert);
	if ($mysqli->error) {
		$error .= $mysqli->error;
		echo $query_insert;
	}
	$insert_id_observacion = $mysqli->insert_id;
	// FIN INSERTAR OBSERVACIONES

	if ($_POST["tipo_observacion"] === 'contrato_interno') {
		send_email_observacion_contrato_interno_gerencia($insert_id_observacion);
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = "ok";
	$result["error"] = $error;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_contrato_interno_firmado") {


	$correos_adjuntos = isset($_POST["correos_adjuntos"]) ? $_POST["correos_adjuntos"] : [];

	$contrato_id = $_POST["contrato_id"];
	$cont_detalle_proveedor_contrato_firmado_categoria_param = $_POST["cont_detalle_proveedor_contrato_firmado_categoria_param"];
	$cont_detalle_proveedor_contrato_firmado_tipo_contrato_param = $_POST["cont_detalle_proveedor_contrato_firmado_tipo_contrato_param"];
	$cont_detalle_proveedor_contrato_firmado_tipo_firma_param = $_POST["cont_detalle_proveedor_contrato_firmado_tipo_firma_param"];
	$cont_detalle_proveedor_renovacion_automatica = $_POST["cont_detalle_proveedor_renovacion_automatica"];

	$cont_detalle_proveedor_contrato_firmado_fecha_incio_param = $_POST["cont_detalle_proveedor_contrato_firmado_fecha_incio_param"];
	$cont_detalle_proveedor_contrato_firmado_fecha_incio_param = date("Y-m-d", strtotime($cont_detalle_proveedor_contrato_firmado_fecha_incio_param));

	$cont_detalle_proveedor_contrato_firmado_fecha_suscripcion_param = $_POST["cont_detalle_proveedor_contrato_firmado_fecha_suscripcion_param"];
	$cont_detalle_proveedor_contrato_firmado_fecha_suscripcion_param = date("Y-m-d", strtotime($cont_detalle_proveedor_contrato_firmado_fecha_suscripcion_param));

	$fecha_vencimiento_indefinida_id = $_POST["fecha_vencimiento_indefinida_id"];
	$cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param = 'NULL';

	if ($fecha_vencimiento_indefinida_id == 2) {
		$cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param = $_POST["cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param"];
		$cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param = "'" . date("Y-m-d", strtotime($cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param)) . "'";
	}

	// $query = "SELECT c.abogado_id FROM cont_contrato c WHERE c.contrato_id = $contrato_id";
	// $list_query = $mysqli->query($query);
	// $row = $list_query->fetch_assoc();

	// if (empty($row['abogado_id'])) {
	// 	$result["http_code"] = 400;
	// 	$result["error"] = 'sin_asignar';
	// 	$result["campo_incompleto"] = 'abogado';
	// 	exit(json_encode($result));
	// }

	$path = "/var/www/html/files_bucket/contratos/contratos_firmados/contratos_internos/";

	if (isset($_FILES['archivo_contrato_proveedor']) && $_FILES['archivo_contrato_proveedor']['error'] === UPLOAD_ERR_OK) {
		if (!is_dir($path)) mkdir($path, 0777, true);

		$filename = $_FILES['archivo_contrato_proveedor']['name'];
		$filenametem = $_FILES['archivo_contrato_proveedor']['tmp_name'];
		$filesize = $_FILES['archivo_contrato_proveedor']['size'];
		$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
		if ($filename != "") {
			$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
			$nombre_archivo = $contrato_id . "_CONTRATO_FIRMADO_" . date('YmdHis') . "." . $fileExt;
			move_uploaded_file($filenametem, $path . $nombre_archivo);
			$comando = "INSERT INTO cont_archivos 
						(
							contrato_id,
							tipo_archivo_id,
							nombre,
							extension,
							size,
							ruta,
							user_created_id,
							created_at
						)
						VALUES
						(
							'" . $contrato_id . "',
							19,
							'" . $nombre_archivo . "',
							'" . $fileExt . "',
							'" . $filesize . "',
							'" . $path . "',
							'" . $login['id'] . "',
							'" . date('Y-m-d H:i:s') . "'
						)";
			$mysqli->query($comando);
		}
	}

	$query_update = "
	UPDATE cont_contrato 
	SET 
		etapa_id = '5',
		categoria_id = '" . $cont_detalle_proveedor_contrato_firmado_categoria_param . "',
		tipo_contrato_proveedor_id = '" . $cont_detalle_proveedor_contrato_firmado_tipo_contrato_param . "',
		usuario_contrato_proveedor_aprobado_id = '" . $login['id'] . "',
		fecha_inicio = '" . $cont_detalle_proveedor_contrato_firmado_fecha_incio_param . "',
		fecha_suscripcion_proveedor = '" . $cont_detalle_proveedor_contrato_firmado_fecha_suscripcion_param . "',
		fecha_vencimiento_proveedor= " . $cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param . ",
		tipo_firma_id = '" . $cont_detalle_proveedor_contrato_firmado_tipo_firma_param . "',
		fecha_vencimiento_indefinida_id = " . $fecha_vencimiento_indefinida_id . ",
		renovacion_automatica = " . $cont_detalle_proveedor_renovacion_automatica . "
	WHERE contrato_id = '" . $contrato_id . "'
	";
	$mysqli->query($query_update);

	if ($mysqli->error) {
		$result["update_error"] = $mysqli->error;
	} else {
		send_email_solicitud_contrato_interno_firmada($_POST["contrato_id"], $correos_adjuntos);
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_estado_solicitud") {
	$usuario_id = $login ? $login['id'] : null;


	if ($usuario_id == null) {
		$result["http_code"] = 400;
		$result["message"] = 'No se puedo guardar el estado porque su sessión a caducado, inicie sesión nuevamente para poder guardar';
		echo json_encode($result);
		exit();
	}
	$contrato_id = $_POST["contrato_id"];
	$estado_solicitud = $_POST["estado_solicitud"];
	$motivo_estado_na = replace_invalid_caracters($_POST["motivo_estado_na"]);;
	$error = '';

	$query_verify = "SELECT dias_habiles, fecha_suscripcion_contrato,created_at FROM cont_contrato WHERE contrato_id=$contrato_id";
	$list_query = $mysqli->query($query_verify);
	if ($list_query->num_rows > 0) {
		while ($fila = $list_query->fetch_array()) {
			$dias_habiles = $fila['dias_habiles'];
			$fecha_solicitud = $fila['created_at'];
		}
		if (is_null($dias_habiles) || !is_numeric($dias_habiles)) {

			$fecha_solicitud = DateTime::createFromFormat('Y-m-d', date('Y-m-d', strtotime($fecha_solicitud)));
			$fecha_actual_string = date("Y-m-d");
			$fecha_actual = DateTime::createFromFormat('Y-m-d', $fecha_actual_string);

			$dias_habiles = 0;
			$fecha_solicitud->modify('+1 day'); // sumamos un dia para no contar el dia de suscripción 
			while ($fecha_solicitud <= $fecha_actual) {

				$dia_semana = $fecha_solicitud->format('N');  //retorna en formato numero  del los dias de la semana 1 hast a domingo
				if ($dia_semana >= 1 && $dia_semana <= 5) { // si esta dentro de los dias habiles 1,2,3,4,5 ; 6 y 7 son sabado y domingo
					$dias_habiles++;
				}
				// Avanzar al siguiente día
				$fecha_solicitud->modify('+1 day');
			}

			$query_update_dias_habiles = "
				UPDATE 
					cont_contrato 
				SET fecha_cambio_estado_solicitud = '$fecha_actual_string', 
					dias_habiles = $dias_habiles,
					usuario_responsable_estado_solicitud_primero =  $usuario_id
				WHERE contrato_id= $contrato_id ";

			$mysqli->query($query_update_dias_habiles);

			if ($mysqli->error) {
				$error .= 'Error al cambiar el estado de la solicitud: ' . $mysqli->error . $query_update_dias_habiles;
			}
		}
	}

	if ($estado_solicitud == 4) {
		$query_update = "
        UPDATE 
            cont_contrato 
        SET 
            estado_solicitud = $estado_solicitud,
            usuario_responsable_estado_solicitud = $usuario_id,
            motivo_estado_na = '$motivo_estado_na'
         
        WHERE 
            contrato_id = $contrato_id
        ";

		send_email_solicitud_estado_no_aplica($_POST["contrato_id"], $motivo_estado_na);
	} else {
		$query_update = "
		UPDATE 
			cont_contrato 
		SET 
			estado_solicitud = $estado_solicitud,
			usuario_responsable_estado_solicitud = $usuario_id
		WHERE 
			contrato_id = $contrato_id
		";
	}




	$mysqli->query($query_update);

	if ($mysqli->error) {
		$error .= 'Error al cambiar el estado de la solicitud: ' . $mysqli->error . $query_update;
	}

	$result["http_code"] = $error == '' ? 200 : 400;
	$result["message"] = $error == '' ? "Se ha cambiado el estado de la solicitud." : $error;
	$result["result"] = $contrato_id;
	// $result["http_code"] = $estado_solicitud == 0 ? 201:200;
	// $result["status"] = "Se ha cambiado el estado de la solicitud.";
	// $result["result"] = $contrato_id;
	// $result["error"] = $error;

	echo json_encode($result);
	exit();
}

if (isset($_POST["post_archivo_req_solicitud_arrendamiento"])) {
	$message = "";
	$status = false;

	$nombre_archivo_guardar = "";

	$id_archivo = $_POST["id_archivo"];
	$contrato_id = $_POST["contrato_id"];
	$id_tipo_archivo = $_POST["id_tipo_archivo"];
	$id_representante_legal = $_POST["id_representante_legal"];

	$tipo_archivo = "";
	$archivo_anterior = "Sin asignar";
	$archivo_nuevo = "";
	$adenda_id = 0;

	$sql = "
	SELECT 
		tipo_contrato_id
	FROM
		cont_contrato
	WHERE
	contrato_id = $contrato_id
	";

	$query = $mysqli->query($sql);
	$row_count = $query->num_rows;
	$path = "/var/www/html/files_bucket/contratos/solicitudes/contratos_internos/";

	if (isset($_FILES['fileArchivo_requisitos_arrendamiento']) && $_FILES['fileArchivo_requisitos_arrendamiento']['error'] === UPLOAD_ERR_OK) {

		if (!is_dir($path)) mkdir($path, 0777, true);

		$filename = $_FILES['fileArchivo_requisitos_arrendamiento']['name'];
		$filenametem = $_FILES['fileArchivo_requisitos_arrendamiento']['tmp_name'];
		$filesize = $_FILES['fileArchivo_requisitos_arrendamiento']['size'];
		$fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

		$valid_extensions = array('jpeg', 'jpg', 'png', 'pdf', 'doc', 'docx', 'odt', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', '7z', 'rar', 'zip');

		if (in_array($fileExt, $valid_extensions)) {
			// SE ELIMINA EL EL ARCHIVO, EXISTA O NO EXISTA EL ARCHIVO, IGUAL EJECUTAMOS EL DELETE, PORQUE NO TIENE QUE EXISTIR 
			// OTRO ARCHIVO, SOLO EXISTIRA EL QUE VAMOS A INSERTAR (LINEAS ABAJO)

			$comando_eliminar_archivo = "
			UPDATE cont_archivos 
			SET 
				status = 0
			WHERE
				archivo_id = $id_archivo
			";

			$mysqli->query($comando_eliminar_archivo);

			if ($id_archivo > 0) {
				$sql_archivo_ant = "
				SELECT a.nombre AS nombre_archivo, a.adenda_id 
				FROM cont_archivos AS a 
				WHERE a.archivo_id = " . $id_archivo;
				$list_query_arc = $mysqli->query($sql_archivo_ant);
				while ($row = $list_query_arc->fetch_assoc()) {
					$archivo_anterior = $row['nombre_archivo'];
					if ($row['adenda_id'] != "") {
						$adenda_id = $row['adenda_id'];
					}
				}
			}

			$sql_archivo_tip_ant = "
			SELECT a.nombre_tipo_archivo
			FROM cont_tipo_archivos AS a 
			WHERE a.tipo_archivo_id = " . $id_tipo_archivo;
			$list_query_tip_arc = $mysqli->query($sql_archivo_tip_ant);
			while ($row = $list_query_tip_arc->fetch_assoc()) {
				$tipo_archivo = $row['nombre_tipo_archivo'];
			}

			// SE INGRESA EL ARCHIVO COMO NUEVO, PORQUE NO EXISTE UN FILE.

			if ($id_tipo_archivo == 1) {
				$nombre_archivo_guardar = "_RUC_";
			} else if ($id_tipo_archivo == 2) {
				$nombre_archivo_guardar = "_VIG_";
			} else if ($id_tipo_archivo == 3) {
				$nombre_archivo_guardar = "_DNI_";
			} else if ($id_tipo_archivo == 8) {
				$nombre_archivo_guardar = "_PARTIDA_REGISTRAL_";
			} else if ($id_tipo_archivo == 9) {
				$nombre_archivo_guardar = "_RECIBO_AGUA_";
			} else if ($id_tipo_archivo == 10) {
				$nombre_archivo_guardar = "_RECIBO_LUZ_";
			} else if ($id_tipo_archivo == 11) {
				$nombre_archivo_guardar = "_DNI_PROPIETARIO_";
			} else if ($id_tipo_archivo == 12) {
				$nombre_archivo_guardar = "_VIGENCIA_PODER_";
			} else if ($id_tipo_archivo == 13) {
				$nombre_archivo_guardar = "_DNI_REPRESENTANTE_LEGAL_";
			} else if ($id_tipo_archivo == 14) {
				$nombre_archivo_guardar = "_HR_INMUEBLE_";
			} else if ($id_tipo_archivo == 15) {
				$nombre_archivo_guardar = "_PU_INMUEBLE_";
			} else if ($id_tipo_archivo == 19) {
				$nombre_archivo_guardar = "_FORMATO_CONTRATO_";
			} else {
				$nombre_archivo_guardar = "_OTROS_";
			}


			if ($nombre_archivo_guardar != "") {

				$nombre_archivo = $contrato_id . $nombre_archivo_guardar . date('YmdHis') . "." . $fileExt;
				move_uploaded_file($filenametem, $path . $nombre_archivo);

				$comando = "INSERT INTO cont_archivos 
							(
								contrato_id,
								adenda_id,
								tipo_archivo_id,
								nombre,
								extension,
								ruta,
								size,
								status,
								user_created_id, 
								created_at, 
								user_updated_id, 
								updated_at
							)
							VALUES
							(
								'" . $contrato_id . "',
								'" . $adenda_id . "',
								'" . $id_tipo_archivo . "',
								'" . $nombre_archivo . "',
								'" . $fileExt . "',
								'" . $path . "',
								'" . $filesize . "',
								'1',
								'" . $login["id"] . "', 
								'" . date('Y-m-d H:i:s') . "',
								'" . $login["id"] . "', 
								'" . date('Y-m-d H:i:s') . "'
							)";
				$mysqli->query($comando);
				$archivo_insert_id = mysqli_insert_id($mysqli);
				if ($id_representante_legal != "" && $id_representante_legal != 0) {
					if ($id_tipo_archivo == 2) {
						$comando_archivo_rl = "UPDATE cont_representantes_legales 
									SET vigencia_archivo_id = " . $archivo_insert_id . " 
									WHERE id = " . $id_representante_legal;
					} else if ($id_tipo_archivo == 3) {
						$comando_archivo_rl = "UPDATE cont_representantes_legales 
									SET dni_archivo_id = " . $archivo_insert_id . " 
									WHERE id = " . $id_representante_legal;
					}

					$mysqli->query($comando_archivo_rl);
				}

				if ($adenda_id > 0) {
					$comando_adenda = "UPDATE cont_adendas 
					SET archivo_id = " . $archivo_insert_id . " 
					WHERE id = " . $adenda_id;
					$mysqli->query($comando_adenda);
				}
				$message = "Datos guardados correctamente";
				$status = true;
			} else {
				$message = "El tipo de archivo a guardar no fue identificado, por favor consulte con el area de sistemas";
				$status = false;
			}
		} else {
			$message = "La extension del file no es aceptado, solo son aceptado: jpeg, jpg, png, pdf";
			$status = false;
		}
	} else {
		$message = "Tiene que seleccionar un archivo :)";
		$status = false;
	}

	$result["status"] = $status;
	$result["message"] = $message;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_adenda_contrato_interno_firmada") {
	$usuario_id = $login ? $login['id'] : null;
	$created_at = date("Y-m-d H:i:s");
	$contrato_id = $_POST["contrato_id"];
	$path = "/var/www/html/files_bucket/contratos/adendas/contratos_internos/";

	$adenda_id = $_POST["adenda_id"];

	// $query = "SELECT a.abogado_id FROM cont_adendas a WHERE a.id = $adenda_id";
	// $list_query = $mysqli->query($query);
	// $row = $list_query->fetch_assoc();

	// if (empty($row['abogado_id'])) {
	// 	$result["http_code"] = 400;
	// 	$result["error"] = 'sin_asignar';
	// 	$result["campo_incompleto"] = 'abogado';
	// 	exit(json_encode($result));
	// }

	$name_file = 'adenda_firmada_' . $_POST["adenda_id"];
	$archivo_id = 0;
	if (isset($_FILES[$name_file]) && $_FILES[$name_file]['error'] === UPLOAD_ERR_OK) {
		if (!is_dir($path)) mkdir($path, 0777, true);

		$filename = $_FILES[$name_file]['name'];
		$filenametem = $_FILES[$name_file]['tmp_name'];
		$filesize = $_FILES[$name_file]['size'];
		$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
		if ($filename != "") {
			$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
			$nombre_archivo = $contrato_id . "_ADENDA_FIRMADA_" . date('YmdHis') . "." . $fileExt;
			move_uploaded_file($filenametem, $path . $nombre_archivo);
			$comando = "INSERT INTO cont_archivos (
							contrato_id,
							adenda_id,
							tipo_archivo_id,
							nombre,
							extension,
							size,
							ruta,
							user_created_id,
							created_at)
						VALUES(
							'" . $contrato_id . "',
							'" . $_POST["adenda_id"] . "',
							98,
							'" . $nombre_archivo . "',
							'" . $fileExt . "',
							'" . $filesize . "',
							'" . $path . "',
							" . $usuario_id . ",
							'" . $created_at . "'
							)";
			$mysqli->query($comando);
			$archivo_id = mysqli_insert_id($mysqli);
		}
	}

	$query = $mysqli->query("
	SELECT contrato_id
	FROM cont_adendas
	WHERE id = " . $_POST["adenda_id"] . "
	AND status = 1
	AND procesado = 0");
	$row_count = $query->num_rows;
	if ($row_count > 0) {
		$row = $query->fetch_assoc();
		$contrato_id = $row["contrato_id"];

		$query = $mysqli->query("
		SELECT id,
			adenda_id,
			nombre_tabla,
			valor_original,
			nombre_campo_usuario,
			nombre_menu_usuario,
			nombre_campo,
			tipo_valor,
			valor_varchar,
			valor_int,
			valor_date,
			valor_decimal,
			valor_select_option,
			valor_id_tabla,
			id_del_registro_a_modificar,
			status
		FROM cont_adendas_detalle
		WHERE adenda_id = " . $_POST["adenda_id"] . "
		AND status = 1
		");
		$row_count = $query->num_rows;
		if ($row_count > 0) {
			while ($row = $query->fetch_assoc()) {
				$nombre_tabla = $row["nombre_tabla"];
				$nombre_campo = $row["nombre_campo"];
				$valor_original = $row["valor_original"];
				$valor_id_tabla = $row['valor_id_tabla'];
				$id_del_registro_a_modificar = $row['id_del_registro_a_modificar'];

				$tipo_valor = $row["tipo_valor"];


				if ($tipo_valor == "registro") {
					// agregar nuevos registros beneficiario y contraprestación
					if ($row['nombre_menu_usuario'] == "Cuenta Bancaria") {
						$query_update = "
						UPDATE " . $nombre_tabla . " 
						SET 
							" . $nombre_campo . " = '" . $contrato_id . "'
						WHERE id = " . $row['valor_int'];
						$mysqli->query($query_update);

						$query_rep = $mysqli->query("
							SELECT 	
							rl.vigencia_archivo_id,
							rl.dni_archivo_id
							FROM cont_representantes_legales as rl
							WHERE rl.id = " . $row['valor_int'] . "
							");
						while ($rl = $query_rep->fetch_assoc()) {
							if ($rl['vigencia_archivo_id'] > 0) {
								$query_update = "
									UPDATE cont_archivos 
									SET contrato_id = '" . $contrato_id . "'
									WHERE archivo_id = " . $rl['vigencia_archivo_id'];
								$mysqli->query($query_update);
							}
							if ($rl['dni_archivo_id'] > 0) {
								$query_update = "
									UPDATE cont_archivos 
									SET contrato_id = '" . $contrato_id . "'
									WHERE archivo_id = " . $rl['dni_archivo_id'];
								$mysqli->query($query_update);
							}
						}
					}

					if ($row['nombre_menu_usuario'] == "Contraprestación") {
						$query_update = "
						UPDATE " . $nombre_tabla . " 
						SET 
							" . $nombre_campo . " = '" . $contrato_id . "'
						WHERE id = " . $row['valor_int'];
						$mysqli->query($query_update);
					}

					if ($mysqli->error) {
						$result["update_error"] = $mysqli->error;
					}
				} else {
					//modificar tablas
					if ($tipo_valor == 'varchar') {
						$nuevo_valor = $row['valor_varchar'];
					} else if ($tipo_valor == 'int') {
						$nuevo_valor = $row['valor_int'];
					} else if ($tipo_valor == 'date') {
						$nuevo_valor = $row['valor_date'];
					} else if ($tipo_valor == 'decimal') {
						$nuevo_valor = $row['valor_decimal'];
					} else if ($tipo_valor == 'select_option') {
						if ($nombre_campo == "ubigeo_id") {
							$nuevo_valor = $row['valor_varchar'];
						} else {
							$nuevo_valor = $row['valor_int'];
						}
					} else if ($tipo_valor == 'id_tabla') {
						$nuevo_valor = $row['valor_int'];
					}

					if ($id_del_registro_a_modificar > 0) {
						$query_update = "
						UPDATE " . $nombre_tabla . " 
						SET 
							" . $nombre_campo . " = '" . $nuevo_valor . "'
						WHERE id = " . $id_del_registro_a_modificar;
					} else {
						$query_update = "
						UPDATE " . $nombre_tabla . " 
						SET 
							" . $nombre_campo . " = '" . $nuevo_valor . "'
						WHERE contrato_id = " . $contrato_id;
					}

					if ($nombre_tabla == "cont_contrato" && $nombre_campo == "fecha_vencimiento_proveedor") {
						$update_alerta = "UPDATE cont_contrato SET alerta_enviada_id = NULL WHERE contrato_id = " . $contrato_id;
						$mysqli->query($update_alerta);
					}

					$mysqli->query($query_update);

					if ($mysqli->error) {
						$result["update_error"] = $mysqli->error;
					}
				}
			}

			$query_update = "
			UPDATE cont_adendas 
			SET 
				procesado = 1,
				archivo_id = " . $archivo_id . "
			WHERE id = '" . $_POST["adenda_id"] . "'";
			$mysqli->query($query_update);

			send_email_adenda_contrato_interno_firmada($_POST["adenda_id"], false);

			if ($mysqli->error) {
				$result["update_error"] = $mysqli->error;
			}
		}
	}

	$result["result"] = 'ok';
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "reenviar_adenda_firmada") {

	try {
		if ($_POST['tipo_contrato'] == 7) {
			send_email_adenda_contrato_interno_firmada($_POST["adenda_id"], true);
		}

		$result['status'] = 200;
		$result['result'] = '';
		$result['message'] = 'Se ha reenviado correctamente la adenda firmada.';
		echo json_encode($result);
		exit();
	} catch (\Throwable $th) {
		$result['status'] = 404;
		$result['result'] = $th->getMessage();
		$result['message'] = 'A ocurrido un error, intentelo mas tarde.';
		echo json_encode($result);
		exit();
	}
}
if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_adenda_firmada") {
	$usuario_id = $login ? $login['id'] : null;



	if (!((int) $usuario_id > 0)) {
		$result["http_code"] = 500;
		$result["status"] = "error";
		$result["mensaje"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y hacer click e el boton verde: Agregar adenda firmada.";
		echo json_encode($result);
		die;
	}

	$created_at = date("Y-m-d H:i:s");
	$contrato_id = $_POST["contrato_id"];
	$adenda_id = $_POST["adenda_id"];

	// $query = "SELECT a.abogado_id FROM cont_adendas a WHERE a.id = $adenda_id";
	// $list_query = $mysqli->query($query);
	// $row = $list_query->fetch_assoc();

	// if (empty($row['abogado_id'])) {
	// 	$result["http_code"] = 400;
	// 	$result["error"] = 'sin_asignar';
	// 	$result["campo_incompleto"] = 'abogado';
	// 	exit(json_encode($result));
	// }

	$path = "/var/www/html/files_bucket/contratos/adendas/locales/";
	$name_file = 'adenda_firmada_' . $_POST["adenda_id"];
	$archivo_id = 0;
	if (isset($_FILES[$name_file]) && $_FILES[$name_file]['error'] === UPLOAD_ERR_OK) {
		if (!is_dir($path)) mkdir($path, 0777, true);

		$filename = $_FILES[$name_file]['name'];
		$filenametem = $_FILES[$name_file]['tmp_name'];
		$filesize = $_FILES[$name_file]['size'];
		$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
		if ($filename != "") {
			$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
			$nombre_archivo = $contrato_id . "_ADENDA_FIRMADA_" . date('YmdHis') . "." . $fileExt;
			move_uploaded_file($filenametem, $path . $nombre_archivo);
			$comando = "INSERT INTO cont_archivos (
							contrato_id,
							adenda_id,
							tipo_archivo_id,
							nombre,
							extension,
							size,
							ruta,
							user_created_id,
							created_at)
						VALUES(
							'" . $contrato_id . "',
							'" . $_POST["adenda_id"] . "',
							17,
							'" . $nombre_archivo . "',
							'" . $fileExt . "',
							'" . $filesize . "',
							'" . $path . "',
							" . $usuario_id . ",
							'" . $created_at . "'
							)";
			$mysqli->query($comando);
			$archivo_id = mysqli_insert_id($mysqli);
		}
	}

	$query = $mysqli->query("
	SELECT contrato_id
	FROM cont_adendas
	WHERE id = " . $_POST["adenda_id"] . "
	AND status = 1 AND procesado = 0");
	//  TEST: colocar AND procesado = 0;

	if (!$query) {
		die("Error en la consulta: " . $mysqli->error);
	}

	$row_count = $query->num_rows;
	if ($row_count > 0) {
		$row = $query->fetch_assoc();
		$contrato_id = $row["contrato_id"];

		$query = $mysqli->query("
		SELECT id,
			adenda_id,
			contrato_detalle_id,
			nombre_tabla,
			valor_original,
			nombre_campo_usuario,
			nombre_menu_usuario,
			nombre_campo,
			tipo_valor,
			valor_varchar,
			valor_int,
			valor_date,
			valor_decimal,
			valor_select_option,
			valor_id_tabla,
			id_del_registro_a_modificar,
			status
		FROM cont_adendas_detalle
		WHERE adenda_id = " . $_POST["adenda_id"] . "
		AND status = 1
		");

		$row_count = $query->num_rows;
		if ($row_count > 0) {
			// while ($row = $query->fetch_assoc()) {

			// 	$nombre_tabla = $row["nombre_tabla"];
			// 	$nombre_menu_usuario = $row["nombre_menu_usuario"];
			// 	$nombre_campo = $row["nombre_campo"];
			// 	$valor_original = $row["valor_original"];
			// 	$valor_id_tabla = $row['valor_id_tabla'];
			// 	$id_del_registro_a_modificar = $row['id_del_registro_a_modificar'];
			// 	$tipo_valor = $row["tipo_valor"];
			// 	if ($tipo_valor == 'varchar') {
			// 		$nuevo_valor = $row['valor_varchar'];
			// 	} else if ($tipo_valor == 'int') {
			// 		$nuevo_valor = $row['valor_int'];
			// 	} else if ($tipo_valor == 'date') {
			// 		$nuevo_valor = $row['valor_date'];
			// 	} else if ($tipo_valor == 'decimal') {
			// 		$nuevo_valor = $row['valor_decimal'];
			// 	} else if ($tipo_valor == 'select_option') {
			// 		if ($nombre_campo == "ubigeo_id") {
			// 			$nuevo_valor = $row['valor_varchar'];
			// 		} else {
			// 			$nuevo_valor = $row['valor_int'];
			// 		}
			// 	} else if ($tipo_valor == 'id_tabla') {
			// 		$nuevo_valor = $row['valor_int'];
			// 	} else if ($tipo_valor == 'registro') {
			// 		$nuevo_valor = $row['valor_int'];
			// 	} else if ($tipo_valor == 'eliminar') {
			// 		$nuevo_valor = $row['valor_int'];
			// 	}

			// 	if ($tipo_valor == 'id_tabla') {

			// 		if ($nombre_tabla == 'cont_propietario') {

			// 			$query_update = "
			// 			UPDATE " . $nombre_tabla . "
			// 			SET
			// 				persona_id = '" . $nuevo_valor . "'
			// 			WHERE propietario_id = '" . $valor_id_tabla . "'";
			// 			$mysqli->query($query_update);
			// 		} else if ($nombre_tabla == 'cont_beneficiarios' || $nombre_tabla == 'cont_responsable_ir') {
			// 			$query_update = "
			// 			UPDATE " . $nombre_tabla . "
			// 			SET
			// 				status = 0
			// 			WHERE id = '" . $valor_original . "'";
			// 			$mysqli->query($query_update);

			// 			$query_update = "
			// 			UPDATE " . $nombre_tabla . "
			// 			SET
			// 				status = 1
			// 			WHERE id = '" . $nuevo_valor . "'";
			// 			$mysqli->query($query_update);
			// 		} else if ($nombre_tabla == 'cont_incrementos') {
			// 			$query_update = "
			// 			UPDATE " . $nombre_tabla . "
			// 			SET
			// 				estado = 0
			// 			WHERE id = '" . $valor_original . "'";
			// 			$mysqli->query($query_update);

			// 			$query_update = "
			// 			UPDATE " . $nombre_tabla . "
			// 			SET
			// 				estado = 1
			// 			WHERE id = '" . $nuevo_valor . "'";
			// 			$mysqli->query($query_update);
			// 		}
			// 	} else if ($tipo_valor == 'registro') {
			// 		//En caso de nuevos registros en la adenda solo se necesita activar el estado a 1
			// 		if ($nombre_menu_usuario == 'Propietario') {
			// 			$query_update = "
			// 			UPDATE " . $nombre_tabla . " 
			// 			SET " . $nombre_campo . " = " . $contrato_id . "
			// 			WHERE propietario_id = '" . $nuevo_valor . "'";
			// 			$mysqli->query($query_update);
			// 		} else if ($nombre_menu_usuario == 'Beneficiario' || $nombre_menu_usuario == 'Inflación' || $nombre_menu_usuario == 'Cuota Extraordinaria' || $nombre_menu_usuario == 'Suministro' || $nombre_menu_usuario == 'Responsable IR') {
			// 			$query_update = "
			// 			UPDATE " . $nombre_tabla . "
			// 			SET " . $nombre_campo . " = '1'
			// 			WHERE id = '" . $nuevo_valor . "'";
			// 			$mysqli->query($query_update);
			// 		} else if ($nombre_menu_usuario == 'Incremento') {
			// 			$query_update = "
			// 			UPDATE " . $nombre_tabla . "
			// 			SET estado = '1'
			// 			WHERE id = '" . $nuevo_valor . "'";
			// 			$mysqli->query($query_update);
			// 		}
			// 	} else if ($tipo_valor == 'eliminar') {

			// 		if ($nombre_menu_usuario == 'Propietario') {
			// 			$query_update = "UPDATE cont_propietario SET status = 0 WHERE propietario_id = " . $nuevo_valor;
			// 			$mysqli->query($query_update);
			// 		} else if ($nombre_menu_usuario == 'Beneficiario') {
			// 			$query_update = "UPDATE cont_beneficiarios SET status = 0 WHERE id = " . $nuevo_valor;
			// 			$mysqli->query($query_update);
			// 		} else if ($nombre_menu_usuario == 'Incremento') {
			// 			$query_update = "UPDATE cont_incrementos SET estado = 0 WHERE id = " . $nuevo_valor;
			// 			$mysqli->query($query_update);
			// 		} else if ($nombre_menu_usuario == 'Inflación') {
			// 			$query_update = "UPDATE cont_inflaciones SET status = 0 WHERE id = " . $nuevo_valor;
			// 			$mysqli->query($query_update);
			// 		} else if ($nombre_menu_usuario == 'Cuota Extraordinaria') {
			// 			$query_update = "UPDATE cont_cuotas_extraordinarias SET status = 0 WHERE id = " . $nuevo_valor;
			// 			$mysqli->query($query_update);
			// 		} else if ($nombre_menu_usuario == 'Responsable IR') {
			// 			$query_update = "UPDATE cont_responsable_ir SET status = 0 WHERE id = " . $nuevo_valor;
			// 			$mysqli->query($query_update);
			// 		} else if ($nombre_menu_usuario == 'Suministro') {
			// 			$query_update = "UPDATE cont_inmueble_suministros SET status = 0 WHERE id = " . $nuevo_valor;
			// 			$mysqli->query($query_update);
			// 		}
			// 	} else {
			// 		if ($nombre_tabla == 'cont_persona') {
			// 			$query_update = "
			// 			UPDATE cont_propietario AS p
			// 			INNER JOIN cont_persona AS per ON per.id = p.persona_id
			// 			SET
			// 			per." . $nombre_campo . " = '" . $nuevo_valor . "'
			// 			WHERE p.contrato_id = '" . $contrato_id . "'";
			// 		} else if (
			// 			$nombre_tabla == 'cont_inmueble' || $nombre_tabla == 'cont_inmueble_suministros'
			// 			|| $nombre_tabla == 'cont_inmueble_suministros' || $nombre_tabla == 'cont_inflaciones' ||
			// 			$nombre_tabla == 'cont_cuotas_extraordinarias' || $nombre_tabla == 'cont_beneficiarios'
			// 			|| $nombre_tabla == 'cont_responsable_ir' || $nombre_tabla == 'cont_adendas_detalle'
			// 		) {
			// 			$query_update = "
			// 			UPDATE " . $nombre_tabla . "
			// 			SET
			// 				" . $nombre_campo . " = '" . $nuevo_valor . "'
			// 			WHERE id = '" . $id_del_registro_a_modificar . "'";
			// 		} else if ($nombre_tabla == 'cont_condicion_economica') {
			// 			if ($nombre_campo == 'fecha_fin') {
			// 				$update_alerta = "UPDATE cont_contrato SET alerta_enviada_id = NULL WHERE contrato_id = " . $contrato_id;
			// 				$mysqli->query($update_alerta);
			// 			}
			// 			$query_update = "
			// 			UPDATE " . $nombre_tabla . "
			// 			SET
			// 				" . $nombre_campo . " = '" . $nuevo_valor . "'
			// 			WHERE condicion_economica_id = '" . $id_del_registro_a_modificar . "'";
			// 		} else if ($nombre_tabla == 'cont_contrato_detalle') {
			// 			$query_update = "
			// 			UPDATE " . $nombre_tabla . "
			// 			SET
			// 				" . $nombre_campo . " = '" . $nuevo_valor . "'
			// 			WHERE id = '" . $id_del_registro_a_modificar . "'";
			// 		} else if ($nombre_tabla == 'cont_locacion') {
			// 			$query_update = "
			// 			UPDATE " . $nombre_tabla . "
			// 			SET
			// 				" . $nombre_campo . " = '" . $nuevo_valor . "'
			// 			WHERE idlocacion = '" . $id_del_registro_a_modificar . "'";
			// 		} else if ($nombre_tabla == 'cont_contraprestacion') {

			// 			$query_update = "
			// 			UPDATE " . $nombre_tabla . "
			// 			SET
			// 				" . $nombre_campo . " = '" . $nuevo_valor . "'
			// 			WHERE contrato_id = '" . $contrato_id . "'";
			// 			// var_dump(($query_update));
			// 			// die("fracasadooooo");
			// 		} else if ($nombre_tabla == 'cont_contrato' && ($nombre_campo == 'persona_responsable_id' || $nombre_campo == 'jefe_comercial_id')) {
			// 			$usuario_comercial_nuevo_id = $nuevo_valor;

			// 			$select_usuario_antiguo = "
			// 			SELECT
			// 				$nombre_campo,
			// 				etapa_id
			// 			FROM
			// 				cont_contrato
			// 			WHERE
			// 				contrato_id = $contrato_id
			// 			";

			// 			$resultado_antiguo = $mysqli->query($select_usuario_antiguo);
			// 			$fila = $resultado_antiguo->fetch_row();
			// 			$usuario_comercial_antiguo_id = $fila[0];
			// 			$etapa_id = $fila[1];

			// 			if ($etapa_id != 1) {
			// 				$select_local_id = "
			// 				SELECT
			// 					id
			// 				FROM
			// 					tbl_locales
			// 				WHERE
			// 					contrato_id = $contrato_id
			// 				";

			// 				$query_select_local_id = $mysqli->query($select_local_id);
			// 				$row_count = $query_select_local_id->num_rows;
			// 				$created_at = date('Y-m-d H:i:s');

			// 				if ($row_count == 1) {
			// 					$row = $query_select_local_id->fetch_assoc();
			// 					$local_id = $row["id"];

			// 					if ($valor_original != 'Sin asignar' && $usuario_comercial_antiguo_id > 0) {
			// 						$update_usuario_comercial_antiguo_quitar_local = "
			// 						UPDATE
			// 							tbl_usuarios_locales
			// 						SET
			// 							estado = 0,
			// 							user_updated_id = $usuario_id,
			// 							updated_at = '" . $created_at . "'
			// 						WHERE
			// 							local_id = $local_id
			// 							AND usuario_id = $usuario_comercial_antiguo_id
			// 						";

			// 						$mysqli->query($update_usuario_comercial_antiguo_quitar_local);
			// 					}

			// 					$select_usuario_comercial_nuevo_existe = "
			// 					SELECT
			// 						id
			// 					FROM
			// 						tbl_usuarios_locales
			// 					WHERE
			// 						local_id = $local_id
			// 						AND usuario_id = $usuario_comercial_nuevo_id
			// 					";

			// 					$query_usuario_com_exis = $mysqli->query($select_usuario_comercial_nuevo_existe);
			// 					$row_count = $query_usuario_com_exis->num_rows;
			// 					if ($row_count == 1) {
			// 						$update_usuario_comercial_ya_existente_al_local = "
			// 						UPDATE
			// 							tbl_usuarios_locales
			// 						SET
			// 							estado = 1 ,
			// 							user_updated_id = $usuario_id,
			// 							updated_at = '" . $created_at . "'
			// 						WHERE
			// 							local_id = $local_id
			// 							AND usuario_id = $usuario_comercial_nuevo_id
			// 						";

			// 						$mysqli->query($update_usuario_comercial_ya_existente_al_local);
			// 					} elseif ($row_count == 0) {
			// 						$insert_usuario_comercial_al_local = "
			// 						INSERT INTO tbl_usuarios_locales (
			// 							usuario_id,
			// 							local_id,
			// 							estado,
			// 							user_created_id,
			// 							created_at
			// 						) VALUES (
			// 							$usuario_comercial_nuevo_id,
			// 							$local_id,
			// 							1,
			// 							$usuario_id,
			// 							'" . $created_at . "'
			// 						)";

			// 						$mysqli->query($insert_usuario_comercial_al_local);
			// 					}
			// 					// ACTUALIZAMOS LA TABLA CONT_CONTRATO CONEL NUEVO USUARIO COMERCIAL
			// 					$query_update = "
			// 					UPDATE " . $nombre_tabla . "
			// 					SET
			// 						" . $nombre_campo . " = '" . $nuevo_valor . "'
			// 					WHERE contrato_id = " . $contrato_id;
			// 				}
			// 			}
			// 		} else if ($nombre_tabla == 'cont_contrato' && $nombre_campo == 'cc_id') {

			// 			$select_local_id = "
			// 					SELECT
			// 						id
			// 					FROM
			// 						tbl_locales
			// 					WHERE
			// 						contrato_id = $contrato_id
			// 					";

			// 			$query_select_local_id = $mysqli->query($select_local_id);
			// 			$row_count = $query_select_local_id->num_rows;
			// 			if ($row_count > 0) {
			// 				$row = $query_select_local_id->fetch_assoc();
			// 				$local_id = $row["id"];

			// 				$update_cc_id_local = "
			// 						UPDATE
			// 							tbl_locales
			// 						SET
			// 							cc_id = '" . $nuevo_valor . "'
			// 						WHERE
			// 							id = $local_id";

			// 				$mysqli->query($update_cc_id_local);
			// 			}
			// 			// ACTUALIZAMOS LA TABLA CONT_CONTRATO CONEL NUEVO USUARIO COMERCIAL
			// 			$query_update = "
			// 			UPDATE " . $nombre_tabla . "
			// 			SET
			// 				" . $nombre_campo . " = '" . $nuevo_valor . "'
			// 			WHERE contrato_id = " . $contrato_id;
			// 		} else {
			// 			$query_update = "
			// 			UPDATE " . $nombre_tabla . "
			// 			SET
			// 				" . $nombre_campo . " = '" . $nuevo_valor . "'
			// 			WHERE contrato_id = '" . $contrato_id . "'";
			// 		}
			// 		$mysqli->query($query_update);
			// 		if (!$mysqli) {
			// 			die("Error en la consulta: " . $mysqli->error);
			// 		}
			// 	}
			// 	if ($mysqli->error) {
			// 		$result["update_error"] = $mysqli->error;
			// 		var_dump("error");
			// 	}
			// }

			$fecha_aplicacion = str_replace("/", "-", $_POST["fecha_aplicacion"]);
			$fecha_aplicacion = date("Y-m-d", strtotime($fecha_aplicacion));
			$query_update = "
			UPDATE cont_adendas
			SET
				procesado = 1,
				archivo_id = " . $archivo_id . ",
				fecha_de_ejecucion_del_cambio = '" . $fecha_aplicacion . "'
			WHERE id = '" . $_POST["adenda_id"] . "'";
			$mysqli->query($query_update);

			// send_email_adenda_contrato_arrendamiento_firmada($_POST["adenda_id"], false);

			if ($mysqli->error) {
				$result["update_error"] = $mysqli->error;
			}
		}
	} else {
		if (!$row_count) {
			die("Error en la consulta: " . $mysqli->error);
		}
	}
	$result["result"] = 'ok';
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	echo json_encode($result);
	die;
}



function send_email_cambio_contrato($auditoria_id)
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
				c.contrato_id,
				co.sigla AS sigla_correlativo,
				c.codigo_correlativo,
				c.tipo_contrato_id,
				c.etapa_id,

				c.check_gerencia_proveedor,
				c.aprobacion_gerencia_proveedor,
				c.fecha_atencion_gerencia_proveedor,

				c.check_gerencia_interno,
				c.fecha_atencion_gerencia_interno,
				c.aprobacion_gerencia_interno

			FROM
				cont_auditoria a
					INNER JOIN
				tbl_usuarios u ON a.user_created_id = u.id
					INNER JOIN
				tbl_personal_apt p ON u.personal_id = p.id
					INNER JOIN
				tbl_areas ar ON p.area_id = ar.id
					INNER JOIN 
				cont_contrato c ON c.contrato_id = a.contrato_id
					INNER JOIN 
				tbl_usuarios ujc ON c.user_created_id = ujc.id
					INNER JOIN 
				tbl_personal_apt pjc ON ujc.personal_id = pjc.id
					LEFT JOIN 
				cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato

				
			WHERE
				a.status = 1 AND a.id = " . $auditoria_id . "
			ORDER BY a.id DESC");



	$body = "";
	$body .= '<html>';

	$usuario_creacion_correo = '';
	$contrato_id = '';
	$tipo_contrato_id = '';
	$envio_correo = true;
	while ($sel = $sel_query->fetch_assoc()) {
		$tipo_valor = $sel["tipo_valor"];
		$usuario_creacion_correo = $sel["correo"];
		$contrato_id = $sel["contrato_id"];

		$sigla_correlativo = $sel["sigla_correlativo"];
		$codigo_correlativo = $sel["codigo_correlativo"];
		$tipo_contrato_id = $sel["tipo_contrato_id"];


		if ($sel['tipo_contrato_id'] == 2) {
			if ($sel['check_gerencia_proveedor'] == 1 && $sel['etapa_id'] == 1) {
				if (is_null($sel['fecha_atencion_gerencia_proveedor'])) {
					$envio_correo = false;
				}
			}
		}

		if ($sel['tipo_contrato_id'] == 7) {
			if ($sel['check_gerencia_interno'] == 1 && $sel['etapa_id'] == 1) {
				if (is_null($sel['fecha_atencion_gerencia_interno'])) {
					$envio_correo = false;
				}
			}
		}

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
		$body .= '<td>' . $sel["nombre_menu_usuario"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Nombre del campo:</b></td>';
		$body .= '<td>' . $sel["nombre_campo_usuario"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Valor anterior:</b></td>';
		$body .= '<td>' . $sel["valor_original"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Nuevo valor:</b></td>';
		$body .= '<td>' . $nuevo_valor . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Usuario que realizó el cambio:</b></td>';
		$body .= '<td>' . $sel["usuario_que_realizo_cambio"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Fecha del cambio:</b></td>';
		$body .= '<td>' . $sel["fecha_del_cambio"] . '</td>';
		$body .= '</tr>';


		$body .= '</table>';
		$body .= '</div>';
	}
	$body .= '<div>';
	$body .= '<br>';
	$body .= '</div>';

	if ($tipo_contrato_id == 1) {
		$url_solicitud = $host . '/?sec_id=contrato&sub_sec_id=detalle_solicitud&id=' . $contrato_id;
	} else if ($tipo_contrato_id == 2) {
		$url_solicitud = $host . '/?sec_id=contrato&sub_sec_id=detalleSolicitudProveedor&id=' . $contrato_id;
	} else if ($tipo_contrato_id == 5) {
		$url_solicitud = $host . '/?sec_id=contrato&sub_sec_id=detalle_solicitud_acuerdo_confidencialidad&id=' . $contrato_id;
	} else if ($tipo_contrato_id == 6) {
		$url_solicitud = $host . '/?sec_id=contrato&sub_sec_id=detalle_agente&id=' . $contrato_id;
	} else if ($tipo_contrato_id == 7) {
		$url_solicitud = $host . '/?sec_id=contrato&sub_sec_id=detalle_solicitud_interno&id=' . $contrato_id;
	}

	$body .= '<div style="width: 700px; text-align: center; font-family: arial;">';
	$body .= '<a href="' . $url_solicitud . '" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
	$body .= '<b>Ver Solicitud</b>';
	$body .= '</a>';
	$body .= '</div>';

	$body .= '</html>';
	$body .= "";

	if ($envio_correo == false) {
		return false;
	}
	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'), env('EMAIL_TEST'));

	if ($tipo_contrato_id == 1) {
		$lista_correos = $correos->send_email_modificacion_contrato_arrendamiento([]);
	} else if ($tipo_contrato_id == 2) {
		$lista_correos = $correos->send_email_modificacion_contrato_proveedor([]);
	} else if ($tipo_contrato_id == 5) {
		$lista_correos = $correos->send_email_modificacion_acuerdo_confidencialidad([]);
	} else if ($tipo_contrato_id == 6) {
		$lista_correos = $correos->send_email_modificacion_contrato_agente([]);
	} else if ($tipo_contrato_id == 7) {
		$lista_correos = $correos->send_email_modificacion_contrato_interno([]);
	}


	$cc = $lista_correos['cc'];

	if ($tipo_contrato_id == 1) {
		$titulo_email = "Gestion - Sistema Contratos - Modificacion de Solicitud Contrato de Arrendamiento : Código - ";
	} else if ($tipo_contrato_id == 2) {
		$titulo_email = "Gestion - Sistema Contratos - Modificacion de Solicitud Contrato de Proveedor : Código - ";
	} else if ($tipo_contrato_id == 5) {
		$titulo_email = "Gestion - Sistema Contratos - Modificacion de Solicitud Acuerdo de confidencialidad : Código - ";
	} else if ($tipo_contrato_id == 6) {
		$titulo_email = "Gestion - Sistema Contratos - Modificacion de Solicitud Contrato de de Agente : Código - ";
	} else if ($tipo_contrato_id == 7) {
		$titulo_email = "Gestion - Sistema Contratos - Modificacion de Solicitud Contrato Interno : Código - ";
	}



	$bcc = $lista_correos['bcc'];

	$request = [
		"subject" => $titulo_email . $sigla_correlativo . $codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try {
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if (isset($request["cc"])) {
			foreach ($request["cc"] as $cc) {
				$mail->addAddress($cc);
			}
		}

		if (isset($request["bcc"])) {
			foreach ($request["bcc"] as $bcc) {
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

	} catch (Exception $e) {
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}
}

function send_email_solicitud_estado_no_aplica($contrato_id, $motivo_estado_na)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$html = '';

	$sql_vc = "SELECT 
    co.sigla AS sigla_correlativo,
    c.codigo_correlativo,
    tc.nombre as nombre_tipo_contrato,
    tc.ruta_detalle
    FROM cont_contrato c  
    LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
    LEFT JOIN cont_tipo_contrato tc ON c.tipo_contrato_id = tc.id
    WHERE c.contrato_id = " . $contrato_id;
	$query_vc = $mysqli->query($sql_vc);
	while ($sel_vc = $query_vc->fetch_assoc()) {
		$sigla_correlativo = $sel_vc['sigla_correlativo'];
		$codigo_correlativo = $sel_vc['codigo_correlativo'];
		$nombre_tipo_contrato = $sel_vc["nombre_tipo_contrato"];
		$ruta_detalle = $sel_vc["ruta_detalle"];
		$contrato_id_de_la_obs = $contrato_id;
	}

	$sql = "SELECT
                o.id,
                o.contrato_id,
                o.observaciones,                
                concat(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, '')) AS usuario_creacion,
                p.correo as correo_creacion,
                ar.id AS area_creacion_id,
                ar.nombre AS area_creacion,
                o.user_created_id,
                o.created_at,                 
                concat(IFNULL(pjc.nombre, ''),' ', IFNULL(pjc.apellido_paterno, '')) AS jefe_comercial,
                pjc.correo AS usuario_registro,
                co.sigla AS sigla_correlativo,
                c.codigo_correlativo,
                tc.nombre as nombre_tipo_contrato,
                tc.ruta_detalle
                FROM cont_observaciones o
                LEFT JOIN tbl_usuarios u ON o.user_created_id = u.id
                LEFT JOIN tbl_personal_apt p ON u.personal_id = p.id
                LEFT JOIN tbl_areas ar ON p.area_id = ar.id
                LEFT JOIN cont_contrato c ON o.contrato_id = c.contrato_id		 
                LEFT JOIN tbl_usuarios ujc ON c.user_created_id = ujc.id
                LEFT JOIN tbl_personal_apt pjc ON ujc.personal_id = pjc.id
                LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
                LEFT JOIN cont_tipo_contrato tc ON c.tipo_contrato_id = tc.id
            WHERE o.contrato_id = " . $contrato_id . " AND o.status = 1
            ORDER BY o.created_at ASC";


	$query = $mysqli->query($sql);
	$row_count = $query->num_rows;




	$body = "";
	$body .= '<html>';
	$body .= '<div>';

	$usuario_registro = '';
	$correo_del_usuario_creacion = '';

	while ($sel = $query->fetch_assoc()) {
		$sigla_correlativo = $sel['sigla_correlativo'];
		$codigo_correlativo = $sel['codigo_correlativo'];

		$usuario_registro = $sel["usuario_registro"];
		$correo_del_usuario_creacion = $sel["correo_creacion"];
		$area_creacion_id = $sel['area_creacion_id'];
		$contrato_id_de_la_obs = $sel["contrato_id"];
		$nombre_tipo_contrato = $sel["nombre_tipo_contrato"];
		$ruta_detalle = $sel["ruta_detalle"];

		if ($area_creacion_id == 21) {
			//EN PRODUCCION EL AREA OPERACIONES ES ID: 21
			// OPERACIONES

			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

			$body .= '<thead>';
			$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
			$body .= '<b>Observaciones previas de la Solicitud</b>';
			$body .= '</th>';
			$body .= '</tr>';
			$body .= '</thead>';

			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>De:</b></td>';
			$body .= '<td>' . $sel["area_creacion"] . ' </td>';
			$body .= '</tr>';

			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Para:</b></td>';
			$body .= '<td>Legal</td>';
			$body .= '</tr>';



			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Fecha observacion:</b></td>';
			$body .= '<td>' . $sel["created_at"] . '</td>';
			$body .= '</tr>';

			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Observacion:</b></td>';
			$body .= '<td>' . $sel["observaciones"] . '</td>';
			$body .= '</tr>';


			$body .= '</table>';
		} else if ($area_creacion_id == 33) {
			//EN PRODUCCION EL AREA LEGAL ES ID: 33
			//EN DEV EL AREA LEGAL ES ID: 33
			//LEGAL

			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

			$body .= '<thead>';
			$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
			$body .= '<b>Observaciones previas de la Solicitud</b>';
			$body .= '</th>';
			$body .= '</tr>';
			$body .= '</thead>';

			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>De:</b></td>';
			$body .= '<td>' . $sel["area_creacion"] . ' </td>';
			$body .= '</tr>';

			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Para:</b></td>';
			$body .= '<td>Operaciones</td>';
			$body .= '</tr>';



			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Fecha observacion:</b></td>';
			$body .= '<td>' . $sel["created_at"] . '</td>';
			$body .= '</tr>';

			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Observacion:</b></td>';
			$body .= '<td>' . $sel["observaciones"] . '</td>';
			$body .= '</tr>';


			$body .= '</table>';
		} else {
			// CUANDO LOS USUARIOS NO FUERON CONFIGURADOS CORRECTAMENTE EN SUS AREAS RESPECTIVAS.

			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

			$body .= '<thead>';
			$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
			$body .= '<b>Observaciones previas de la Solicitud</b>';
			$body .= '</th>';
			$body .= '</tr>';
			$body .= '</thead>';



			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Usuario quien observo:</b></td>';
			$body .= '<td>' . $sel["usuario_creacion"] . ' <strong>(' . $sel["area_creacion"] . ')</strong></td>';
			$body .= '</tr>';

			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Fecha observacion:</b></td>';
			$body .= '<td>' . $sel["created_at"] . '</td>';
			$body .= '</tr>';

			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Observacion:</b></td>';
			$body .= '<td>' . $sel["observaciones"] . '</td>';
			$body .= '</tr>';


			$body .= '</table>';
		}
	}

	$body .= '</div>';

	$body .= '<div>';
	$body .= '<br>';
	$body .= '</div>';

	$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';
	$body .= '<thead>';
	$body .= '<tr>';
	$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
	$body .= '<b>No Aplica</b>';
	$body .= '</th>';
	$body .= '</tr>';
	$body .= '</thead>';
	$body .= '<tr>';
	$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Motivo:</b></td>';
	$body .= '<td>' . $motivo_estado_na . ' </td>';
	$body .= '</tr>';
	$body .= '</table>';


	$body .= '<div>';
	$body .= '<br>';
	$body .= '</div>';

	$body .= '<div style="width: 500px; text-align: center; font-family: arial;">';

	$body .= '<a href="' . $host . '/?sec_id=contrato&sub_sec_id=' . $ruta_detalle . '&id=' . $contrato_id_de_la_obs . '" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
	$body .= '<b>Ver Solicitud</b>';
	$body .= '</a>';
	$body .= '</div>';

	$body .= '</html>';
	$body .= "";


	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'), env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_solicitud_no_aplica([$usuario_registro]);

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];

	$correos_adjuntos = $correo_del_usuario_creacion;

	if ($correos_adjuntos != "") {
		$validate = true;
		$emails = preg_split('[,|;]', $correos_adjuntos);
		foreach ($emails as $e) {
			if (preg_match('/^[_ña-z0-9-]+(\.[_ña-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/', trim($e)) == 0) {
				$error_msg = "'" . $e . "'" . " no es un Correo Adjunto válido";
				$result["http_code"] = 500;
				$result["status"] = "error";
				$result["mensaje"] = $error_msg;
				echo json_encode($result);
				die;
			} else {
				$cc[] = $e;
			}
		}
	}

	$request = [
		"subject" => "Gestion - Sistema Contratos - Solicitud de " . $nombre_tipo_contrato . " - No Aplica: Código - " . $sigla_correlativo . $codigo_correlativo,
		//"subject" => "Solicitud de proveedor",
		//"subject" => "Solicitud de adenda",
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try {
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if (isset($request["cc"])) {
			foreach ($request["cc"] as $cc) {
				$mail->addAddress($cc);
			}
		}

		if (isset($request["bcc"])) {
			foreach ($request["bcc"] as $bcc) {
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

	} catch (Exception $e) {
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}
}
