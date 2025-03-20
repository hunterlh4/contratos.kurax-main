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




function send_email_observacion_contrato_interno_gerencia2($observacion_id)
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
if (isset($_POST["accion"]) && $_POST["accion"] === "actualizar_formato") {
	$usuario_id = $login ? $login['id'] : null;
	$result = array();
	if ((int) $usuario_id > 0) {
		$created_at = date("Y-m-d H:i:s");
		$contrato_tipo_id = $_POST["contrato_tipo_id"];
		$nombre = $_POST["nombre"];
		$contenido = trim($_POST["contenido"]);
		$descripcion = trim($_POST["descripcion"]);

		$query_contrato = "SELECT MAX(CAST(c.codigo AS UNSIGNED)) AS codigo_maximo FROM cont_formato AS c WHERE c.tipo_contrato_id = ?";
		$stmt = $mysqli->prepare($query_contrato);
		$stmt->bind_param("i", $contrato_tipo_id);
		$stmt->execute();
		$result_query = $stmt->get_result();
		$row = $result_query->fetch_assoc();
		$codigo_maximo = isset($row["codigo_maximo"]) ? (int)$row["codigo_maximo"] : 0;

		$nuevo_codigo = $codigo_maximo + 1;

		$query_insert = "INSERT INTO cont_formato (nombre, codigo, tipo_contrato_id, descripcion, contenido, user_created_id, created_at) 
                         VALUES (?, ?, ?, ?, ?, ?, ?)";

		$stmt_insert = $mysqli->prepare($query_insert);
		$stmt_insert->bind_param("ssissis", $nombre, $nuevo_codigo, $contrato_tipo_id, $descripcion, $contenido, $usuario_id, $created_at);

		if ($stmt_insert->execute()) {
			$result["result"] = 'ok';
			$result["http_code"] = 200;
			$result["message"] = "Datos obtenidos de gestion.";
		} else {
			$result["http_code"] = 400;
			$result["message"] = "No se pudo insertar correctamente.";
		}

		$stmt_insert->close();
		$stmt->close();
	}
	echo json_encode($result);
	exit();
}
if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_formato") {
	$usuario_id = $login ? $login['id'] : null;
	$result = array();

	if ((int) $usuario_id > 0) {
		if (isset($_POST["idcontrato"])) { // Si se envía un idcontrato, lo obtenemos en lugar de insertar
			$idContrato = intval($_POST["idcontrato"]);
			$query = "SELECT a.nombre, a.codigo, a.descripcion, a.tipo_contrato_id, a.contenido FROM cont_formato as a INNER JOIN cont_contrato as cc ON cc.idformato = a.idformato WHERE cc.contrato_id = ?";
			$stmt = $mysqli->prepare($query);
			$stmt->bind_param("i", $idContrato);
			$stmt->execute();
			$result_query = $stmt->get_result();
			$row = $result_query->fetch_assoc();

			if ($row) {
				$result["result"] = 'ok';
				$result["http_code"] = 200;
				$result["nombre"] = $row["nombre"];
				$result["codigo"] = $row["codigo"];
				$result["descripcion"] = $row["descripcion"];
				$result["tipo_contrato_id"] = $row["tipo_contrato_id"];
				$contenido = $row["contenido"];
				// $result["contenido"] = $row["contenido"];

				// CONTRATO ARRENDAMIENTO
				if ($row["tipo_contrato_id"] == 1) {
					// Consulta para obtener los datos adicionales
					$queryDetalles = "SELECT
					a.contrato_id,
					c.num_ruc AS arrendador_ruc,
					c.direccion AS arrendador_direccion,
					c.representante_legal AS arrendatario_denominacion_social,
					-- CONCAT(IFNULL( c.nombre, '' ),' ',IFNULL( c.apellido_paterno, '' ),' ',IFNULL( c.apellido_materno, '' )) AS arrendador_nombre_completo,
					c.nombre as arrendador_nombre_completo,
					c.num_docu AS arrendador_dni,
					c.num_partida_registral AS arrendador_partida_electronica,
					c.num_partida_registral_sede AS arrendador_sede_partida_electronica 
				FROM
					cont_contrato a
					LEFT JOIN cont_propietario b ON b.contrato_id = a.contrato_id
					LEFT JOIN cont_persona c ON c.id = b.persona_id 
				WHERE a.contrato_id = ?";
					$stmtDetalles = $mysqli->prepare($queryDetalles);
					$stmtDetalles->bind_param("i", $idContrato);
					$stmtDetalles->execute();
					$resultDetalles = $stmtDetalles->get_result();
					$detalles = $resultDetalles->fetch_assoc();

					if ($detalles) {
						// Array con los placeholders y sus valores
						$placeholders = [
							"@ARRENDADOR_RUC" => $detalles["arrendador_ruc"],
							"@ARRENDADOR_DIRECCION" => $detalles["arrendador_direccion"],
							"@ARRENDADOR_NOMBRE_COMPLETO" => $detalles["arrendador_nombre_completo"],
							"@ARRENDADOR_DNI" => $detalles["arrendador_dni"],
							"@ARRENDADOR_PARTIDA_ELECTRONICA" => $detalles["arrendador_partida_electronica"],
							"@ARRENDADOR_SEDE_PARTIDA_ELECTRONICA" => $detalles["arrendador_sede_partida_electronica"],
							// "@ARRENDATARIO_DENOMINACION_SOCIAL" => $detalles["arrendatario_denominacion_social"],
							// "@ARRENDATARIO_RUC" => $detalles["arrendatario_ruc"]
						];

						// Reemplazar en el contenido
						$contenido = str_replace(array_keys($placeholders), array_values($placeholders), $contenido);
					}
				}
				// CONTRATO 
				if ($row["tipo_contrato_id"] == 13) {
				}

				// asasasasasasasassasas
				$result["contenido"] = $contenido;
			} else {
				$result["http_code"] = 404;
				$result["message"] = "Contrato no encontrado." . $_POST["idcontrato"];
			}
		}
	} else {
		$result["http_code"] = 403;
		$result["message"] = "Acceso no autorizado.";
	}

	echo json_encode($result);
	exit();
}
