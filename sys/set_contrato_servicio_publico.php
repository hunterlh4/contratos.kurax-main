<?php 

date_default_timezone_set("America/Lima");

include("db_connect.php");
include("sys_login.php");

require_once '/var/www/html/env.php';
include '/var/www/html/sys/envio_correos.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


function NombreMes($fecha){
	$anio = date("Y", strtotime($fecha));
	$mes = date("m", strtotime($fecha));
	$nombre_mes = "";
	switch ($mes) {
		case '01': $nombre_mes = "Enero"; break;
		case '02': $nombre_mes = "Febrero"; break;
		case '03': $nombre_mes = "Marzo"; break;
		case '04': $nombre_mes = "Abril"; break;
		case '05': $nombre_mes = "Mayo"; break;
		case '06': $nombre_mes = "Junio"; break;
		case '07': $nombre_mes = "Julio"; break;
		case '08': $nombre_mes = "Agosto"; break;
		case '09': $nombre_mes = "Septiembre"; break;
		case '10': $nombre_mes = "Octubre"; break;
		case '11': $nombre_mes = "Noviembre"; break;
		case '12': $nombre_mes = "Diciembre"; break;
	}
	return $nombre_mes." del ".$anio;
}

function send_email_contrato_servicio_publico_observacion($archivo_id,$correos_list,$correo_creador)
{
	include("db_connect.php");
	include("sys_login.php");
	include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
	include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';

	$sel_query = $mysqli->query("SELECT o.id, o.id_recibo, o.created_at, o.observacion, IFNULL(p.nombre,'')  AS nombre, 
	IFNULL(p.apellido_paterno,'') AS apellido_paterno, 
	IFNULL(p.apellido_materno,'') AS apellido_materno,	
	ar.nombre AS area,
	l.periodo_consumo,
	l.numero_recibo,
	t.nombre AS nombre_servicio,
	lc.nombre AS nombre_local,
	lc.cc_id AS centro_costo	
	FROM cont_local_servicio_publico_observaciones AS o
	INNER JOIN tbl_usuarios u ON o.user_created_id = u.id
	INNER JOIN tbl_personal_apt p ON p.id = u.personal_id
	INNER JOIN tbl_areas ar ON p.area_id = ar.id
	INNER JOIN cont_local_servicio_publico AS l ON l.id = o.id_recibo 
	INNER JOIN cont_tipo_servicio_publico AS t ON t.id = l.id_tipo_servicio_publico
	INNER JOIN tbl_locales AS lc ON lc.id = l.id_local
	WHERE o.id_recibo = ".$archivo_id."
	AND o.status = 1
	ORDER BY o.created_at DESC LIMIT 1");
	$body = "";
	$body .= '<html>';

	$email_user_created = '';
	$centro_costo = "";
	$id_recibo = "";
	$host = $_SERVER["HTTP_HOST"];
	while($sel = $sel_query->fetch_assoc())
	{
	
		$centro_costo = $sel["centro_costo"];
		$nombre_local = $sel["nombre_local"];
	$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

	$body .= '<thead>';
	$body .= '<tr>';
	$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
	$body .= '<b>OBSERVACIÓN DEL SERVICIO PÚBLICO </b>';
	$body .= '</th>';
	$body .= '</tr>';
	$body .= '</thead>';

	$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>De:</b></td>';
		$body .= '<td>'.$sel["area"].'</td>';
	$body .= '</tr>';

	$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Local:</b></td>';
		$body .= '<td>'.$sel["nombre_local"].'</td>';
	$body .= '</tr>';

	$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Centro de Costo:</b></td>';
		$body .= '<td>'.$sel["centro_costo"].'</td>';
	$body .= '</tr>';

	$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Tipo de Servicio:</b></td>';
		$body .= '<td>'.$sel["nombre_servicio"].'</td>';
	$body .= '</tr>';

	$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Periodo:</b></td>';
		$body .= '<td>'.NombreMes($sel["periodo_consumo"]).'</td>';
	$body .= '</tr>';

	$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Fecha observación:</b></td>';
		$body .= '<td>'.$sel["created_at"].'</td>';
	$body .= '</tr>';

	$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Observación:</b></td>';
		$body .= '<td>'.$sel["observacion"].'</td>';
	$body .= '</tr>';

	$body .= '</table>';

	}

	$body .= '<div>';
		$body .= '<br>';
	$body .= '</div>';

	$body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
	    $body .= '<a href="'.$host.'/?sec_id=contrato&sub_sec_id=detalle_servicio_publico&id='.$archivo_id.'" target="_blank">';
	    	$body .= '<button style="background-color: green; color: white; cursor: pointer; width: 50%;">';
				$body .= '<b>Ver Detalle del Recibo</b>';
	    	$body .= '</button>';
	    $body .= '</a>';
	$body .= '</div>';

	$body .= '</html>';
	$body .= "";


	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_contrato_servicio_publico_observaciones([$correos]);

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];
	
	$sub_titulo_email = "";

	if (env('SEND_EMAIL') == 'test')
	{
		$sub_titulo_email = "TEST SISTEMAS: ";
	}

	$titulo_email = $sub_titulo_email."Gestión - Sistema Contratos - ".$centro_costo." ".$nombre_local." - Servicio Público - Observación";

	$request = [
		"subject" => $titulo_email,
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
			
			if($correos_list != null){
				foreach ($correos_list as $email) {
					$mail->addAddress($email);
				}
			}
			if($correo_creador != null) $mail->addAddress($correo_creador);
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

if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_monto_servicio_publico")
{
	$archivo_id = $_POST["id_archivo"];
	$monto_total = $_POST["monto_total"];
	$num_recibo = $_POST["num_recibo"];
	$serie = $_POST["serie"];
	$aplica_caja_chica = $_POST["aplica_caja_chica"];
	$correo_persona_pagar = $_POST["nombre_persona_pagar"];
	$total_pagar = $_POST["total_pagar"];
	$estado = $_POST["estado"];

	$periodo_consumo = $_POST["periodo_consumo"]."-01";
	$fecha_emision = date("Y-m-d", strtotime($_POST['fecha_emision']));
	$fecha_vencimiento = date("Y-m-d", strtotime($_POST['fecha_vencimiento']));
	
	$id_user = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	$error = '';



	//validacion del perioodo
	$new_periodo_consumo = date("Y-m", strtotime($periodo_consumo));
	$id_tipo_servicio_publico = "";
	$id_local = "";
	$old_periodo_consumo = "";
	$validar_modificacion = true;
	$sel_query = $mysqli->query("SELECT sp.id, sp.id_local, sp.id_tipo_servicio_publico, sp.periodo_consumo
	FROM cont_local_servicio_publico AS sp
	WHERE sp.id = ".$archivo_id);
	while($sel = $sel_query->fetch_assoc()){
		$old_periodo_consumo = $sel["periodo_consumo"];
		$id_tipo_servicio_publico = $sel["id_tipo_servicio_publico"];
		$id_local = $sel["id_local"];
	}
	$old_periodo_consumo = date("Y-m", strtotime($old_periodo_consumo));
	
	if ($new_periodo_consumo != $old_periodo_consumo) {
		$registros = 0;
		$query = "SELECT sp.id, DATE_FORMAT(sp.periodo_consumo, '%Y-%m') as periodo_consumo
		FROM cont_local_servicio_publico AS sp
		WHERE sp.status = 1
		AND sp.id_tipo_servicio_publico = ".$id_tipo_servicio_publico."
		AND sp.id_local = ".$id_local."
		AND DATE_FORMAT(sp.periodo_consumo, '%Y-%m') = '".$new_periodo_consumo."'";
		$sel_query = $mysqli->query($query);
		
		while($sel = $sel_query->fetch_assoc()){
			$registros++;
		}
		if ($registros > 0) {
			
			$result["http_code"] = 400;
			$result["status"] = "Datos obtenidos de gestion.";
			$result["error"] = 'Ya se encuentra registrado un servicio publico en el periodo ingresado';
			echo json_encode($result);
			exit();
		}
	}

	if($estado == 2){
		$fecha_validacion_contabilidad = "fecha_validacion_contabilidad = '".date("Y-m-d H:i:s")."', ";
	}else{
		$fecha_validacion_contabilidad = "";
	}
	$query = 
	"
		UPDATE cont_local_servicio_publico 
		SET monto_total = '".$monto_total."',
			numero_recibo = '" . $num_recibo . "',
			serie = '" . $serie . "',
			aplica_caja_chica = '".$aplica_caja_chica."',
			nombre_paga_caja_chica = '" . $correo_persona_pagar . "',
			total_pagar = '".$total_pagar."',
			periodo_consumo = '" . $periodo_consumo. "',
			fecha_emision = '" . $fecha_emision. "',
			fecha_vencimiento = '" . $fecha_vencimiento. "',
			user_atencion_contabilidad = '".$id_user."',
			fecha_atencion_contabilidad = '".$created_at."',
			$fecha_validacion_contabilidad
			user_updated_id = '".$id_user."',
			updated_at = now(), 
			estado = '".$estado."'
			where id = '".$archivo_id."'
	";
	
	if($aplica_caja_chica == 1)
	{
		sendEmailCajaChica($archivo_id,$correo_persona_pagar);
	}

	$mysqli->query($query);
	$result=array();
	if($mysqli->error){
		$error .= 'Error al guardar el monto del servicio: ' . $mysqli->error . $query;
	}
	if ($error == '') {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["error"] = $error;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["error"] = $error;
	}
	echo json_encode($result);
}

if (isset($_POST["accion"]) && $_POST["accion"] === "observar_servicio_publico") {
    $result = [
        "http_code" => 200,
        "status" => "Datos obtenidos de gestión",
        "error" => "",
    ];

    $archivo_id = isset($_POST["id_archivo"]) ? intval($_POST["id_archivo"]) : 0;
    $observacion = isset($_POST["observacion"]) ? $_POST["observacion"] : "";
    $correos = isset($_POST["correos"]) ? (array) $_POST["correos"] : [];

    $login = isset($login) ? $login : null;
    $id_user = $login['id'];

    $error = '';
	// Editar estado
	$updateReciboQuery = "UPDATE cont_local_servicio_publico 
            SET estado = 6, user_updated_id = ?, updated_at = NOW()
            WHERE id = ?";

    $updateReciboStmt = $mysqli->prepare($updateReciboQuery);
    $updateReciboStmt->bind_param("ii", $id_user, $archivo_id);
    $updateReciboStmt->execute();
    $updateReciboStmt->close();

    // Consultar si ya existe un registro para el archivo
    $selectQuery = "SELECT id FROM cont_local_servicio_publico_observaciones WHERE id_recibo = ? AND status = 1 LIMIT 1";

    $selectStmt = $mysqli->prepare($selectQuery);
    $selectStmt->bind_param("i", $archivo_id);
    $selectStmt->execute();
    $selectStmt->store_result();

    if ($selectStmt->num_rows > 0) {
        // Actualizar observación existente
        $updateObservationQuery = "UPDATE cont_local_servicio_publico_observaciones 
            SET observacion = ?, user_created_id = ?, created_at = NOW()
            WHERE id_recibo = ?";

        $updateObservationStmt = $mysqli->prepare($updateObservationQuery);
        $updateObservationStmt->bind_param("sii", $observacion, $id_user, $archivo_id);
        $updateObservationStmt->execute();
        $updateObservationStmt->close();
    } else {
        // Si no existe, registrar nueva observación
        $insertObservationQuery = "INSERT INTO cont_local_servicio_publico_observaciones (
            id_recibo,
            observacion,
            status,
            user_created_id,
            created_at
        ) VALUES (?, ?, 1, ?, NOW())";

        $insertObservationStmt = $mysqli->prepare($insertObservationQuery);
        $insertObservationStmt->bind_param("isi", $archivo_id, $observacion, $id_user);
        $insertObservationStmt->execute();
        $insertObservationStmt->close();
    }

    // Consultar si ya existen registros de correos para el archivo
    $selectEmailsQuery = "SELECT correo FROM cont_local_servicio_publico_correos WHERE id_recibo = ? AND status = 1";

    $selectEmailsStmt = $mysqli->prepare($selectEmailsQuery);
    $selectEmailsStmt->bind_param("i", $archivo_id);
    $selectEmailsStmt->execute();
    $selectEmailsStmt->store_result();

    $existingEmails = [];
    $selectEmailsStmt->bind_result($existingEmail);

    while ($selectEmailsStmt->fetch()) {
        $existingEmails[] = $existingEmail;
    }

    $selectEmailsStmt->close();

    // Actualizar correos existentes, registrar nuevos correos y dar de baja los no incluidos
    foreach ($existingEmails as $existingEmail) {
        if (!in_array($existingEmail, $correos)) {
            // Dar de baja el correo cambiando su estado a 0
            $updateStatusQuery = "UPDATE cont_local_servicio_publico_correos 
                SET status = 0
                WHERE id_recibo = ? AND correo = ?";

            $updateStatusStmt = $mysqli->prepare($updateStatusQuery);
            $updateStatusStmt->bind_param("is", $archivo_id, $existingEmail);
            $updateStatusStmt->execute();
            $updateStatusStmt->close();
        }
    }

    foreach ($correos as $correo) {
        if (!empty($correo)) {
            if (in_array($correo, $existingEmails)) {
                // Actualizar correo existente
                $updateEmailQuery = "UPDATE cont_local_servicio_publico_correos 
                    SET correo = ?, user_created_id = ?, created_at = NOW(), status = 1
                    WHERE id_recibo = ? AND correo = ?";

                $updateEmailStmt = $mysqli->prepare($updateEmailQuery);
                $updateEmailStmt->bind_param("siis", $correo, $id_user, $archivo_id, $correo);
                $updateEmailStmt->execute();
                $updateEmailStmt->close();
            } else {
                // Registrar nuevo correo
                $insertEmailQuery = "INSERT INTO cont_local_servicio_publico_correos (
                    id_recibo,
                    correo,
                    status,
                    user_created_id,
                    created_at
                ) VALUES (?, ?, 1, ?, NOW())";

                $insertEmailStmt = $mysqli->prepare($insertEmailQuery);
                $insertEmailStmt->bind_param("isi", $archivo_id, $correo, $id_user);
                $insertEmailStmt->execute();
                $insertEmailStmt->close();
            }
        }
    }

    if ($mysqli->error) {
        $error = 'Error al guardar la observación del servicio: ' . $mysqli->error;
    }

    if ($error == '') {
		// Obtener el correo del usuario creador
		$selectQuery = "SELECT p.correo
							FROM cont_local_servicio_publico AS sp
							INNER JOIN tbl_usuarios u ON sp.user_created_id = u.id
							INNER JOIN tbl_personal_apt p ON u.personal_id = p.id 
							WHERE sp.id = ? AND sp.status = 1 LIMIT 1";

		$selectStmt = $mysqli->prepare($selectQuery);
		$selectStmt->bind_param("i", $archivo_id);
		$selectStmt->execute();
		$selectStmt->store_result();
	
		if ($selectStmt->num_rows > 0) {
            $selectStmt->bind_result($correo_creador);
            $selectStmt->fetch();
        
			send_email_contrato_servicio_publico_observacion($archivo_id, $correos,$correo_creador);
		}
        
    } else {
        $result["http_code"] = 400;
    }

    $result["error"] = $error;
    echo json_encode($result);
}

if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_archivo_servicio_publico") {
	$id_recibo = $_POST["id_recibo"];
	$periodo = $_POST["periodo"];
	$id_local = $_POST["id_local"];
	$local = $_POST["local"];
	$fecha_emision = $_POST["fecha_emision"];
	$fecha_vencimiento = $_POST["fecha_vencimiento"];
	$comentario = $_POST["comentario"];
	$monto = $_POST["monto"];
	$recibo = $_POST["recibo"];
	$error = '';

	$message = "";
	$status = true;
	$nombre_tipo_servicio = "";
	$nombre_servicio = "";
	$txt_nombre_tienda = $local;

	if($id_recibo == 1)
	{
		$nombre_tipo_servicio = "LUZ";
		$path = "/var/www/html/files_bucket/contratos/servicios_publicos/luz/";
		$nombre_servicio = "luz";
	}
	else
	{
		$nombre_tipo_servicio = "AGUA";
		$path = "/var/www/html/files_bucket/contratos/servicios_publicos/agua/";
		$nombre_servicio = "agua";
	}

	$fecha_emision = date("Y-m-d", strtotime($fecha_emision));
	$fecha_vencimiento = date("Y-m-d", strtotime($fecha_vencimiento));
	$periodo = date("Y-m-d", strtotime($periodo));
	$anio = substr($periodo, 0 , 4);
	$mes = substr($periodo, 5, 2);	

	$anio_mes = $anio . "-" . $mes;
	$result=array();
	if (isset($_FILES['sec_serv_pub_file_archivo_recibo']) && $_FILES['sec_serv_pub_file_archivo_recibo']['error'] === UPLOAD_ERR_OK) {
		$fileServicioPublico = $_FILES['sec_serv_pub_file_archivo_recibo']['name'];
		$tmpServicioPublico = $_FILES['sec_serv_pub_file_archivo_recibo']['tmp_name'];
		$sizeServicioPublico = $_FILES['sec_serv_pub_file_archivo_recibo']['size'];
		$extServicioPublico = strtolower(pathinfo($fileServicioPublico, PATHINFO_EXTENSION));
		$download = "/files_bucket/contratos/servicios_publicos/".$nombre_servicio."/";

		if (!is_dir($path)) 
		{
			mkdir($path, 0777, true);
		}

		$nombreFileUpload = $txt_nombre_tienda."_".$nombre_tipo_servicio."_".$anio_mes.".".$extServicioPublico;
		$nombreDownload = $download.$nombreFileUpload;
		move_uploaded_file($tmpServicioPublico, $path. $nombreFileUpload);

		$query = "INSERT INTO cont_local_servicio_publico 
							(
								id_local, 
								id_tipo_servicio_publico, 
								fecha_emision, 
								fecha_vencimiento, 
								monto_total,
								periodo_consumo, 
								comentario, 
								numero_recibo,
								estado,
								nombre_file, 
								extension, 
								size, 
								ruta_upload, 
								ruta_download_file, 
								status,
								user_created_id,
								created_at, 
								user_updated_id, 
								updated_at
                             )
						VALUES 
							(
								'".$id_local."', 
								'".$id_recibo."', 
								'".$fecha_emision."',
								'".$fecha_vencimiento."',
								'".$monto."',
								'".$periodo."',
								'".$comentario."',
								'".$recibo."',
								2,
								'".$nombreFileUpload."',
								'".$extServicioPublico."',
								'".$sizeServicioPublico."',
								'".$path."',
								'".$nombreDownload."',
								1,
								'".$login["id"]."', 
								'".date('Y-m-d')."', 
								'".$login["id"]."', 
								'".date('Y-m-d')."'
							)";

		$mysqli->query($query);
		
		if($mysqli->error){
			$error .= 'Error al guardar el recibo: ' . $mysqli->error . $query;
		}
		if ($error == '') {
			$result["http_code"] = 200;
			$result["status"] = "Datos obtenidos de gestion.";
			$result["error"] = $error;
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Datos obtenidos de gestion.";
			$result["error"] = $error;
		}
	}
	echo json_encode($result);
}


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_correos_observacion_tesoreria") {
	//tesoreria observa el recibo y se muestra los correos de contabilidad
	if(env('SEND_EMAIL') == 'test')
	{
		// TEST - LOCAL
		$correos = "erick.arias@testtest.apuestatotal.com";
	}
	else
	{
		// PRODUCCION
		$correos = "prishlina.ramirez@testtest.apuestatotal.com, wendy.aguilar@testtest.apuestatotal.com";	
	}
	
	$result["http_code"] = 200;
	$result["result"] = $correos;
	echo json_encode($result);
}

if (isset($_POST["accion"]) && $_POST["accion"]==="observar_servicio_publico_tesoreria") {
	$archivo_id = $_POST["id_archivo"];
	$observacion = $_POST["observacion"];
	$correos = isset($_POST["correos"]) ? $_POST["correos"]:[];
	
	$id_user = $login?$login['id']:null;
	$error = '';
	///actualizar estado
	$query = "UPDATE cont_local_servicio_publico set 
			user_observacion_id = " . $id_user . ", 
			observacion_created_at = now(), 
			estado = 6
			where id = " . $archivo_id;
	$mysqli->query($query);

	// registrar observacion
	$query = "INSERT cont_local_servicio_publico_observaciones  (
		id_recibo,
		observacion,
		status,
		user_created_id,
		created_at
	) VALUES (
		".$archivo_id.",
		'".$observacion."',
		1,
		".$id_user.",
		now()
	)";
	$mysqli->query($query);


	$query = "SELECT p.correo 
	FROM tbl_usuarios AS u
	INNER JOIN tbl_personal_apt AS p ON p.id = u.personal_id
	WHERE p.estado = 1 AND u.id = ".$id_user;
	$mysqli->query($query);
	$list_query = $mysqli->query($query);
	while ($sp = $list_query->fetch_assoc()) {
		if (!Empty($sp['correo'])) {
			//registrar correo
			$query = "INSERT cont_local_servicio_publico_correos  (
				id_recibo,
				correo,
				status,
				user_created_id,
				created_at
			) VALUES (
				".$archivo_id.",
				'".$sp['correo']."',
				1,
				".$id_user.",
				now()
			)";
			$mysqli->query($query);
		}
	}


	$result=array();
	if($mysqli->error){
		$error .= 'Error al guardar la observacion del servicio: ' . $mysqli->error . $query;
	}
	if ($error == '') {
		//ENVIO DE CORREOS
		send_email_contrato_servicio_publico_observacion($archivo_id,$correos,null);
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["error"] = $error;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["error"] = $error;
	}
	echo json_encode($result);
}


if (isset($_POST["accion"]) && $_POST["accion"]==="pagar_servicio_publico") {

	$id_recibo = $_POST["sec_con_serv_pub_id_recibo"];
	$fecha_pago = $_POST["sec_con_serv_pub_fecha_pago"];
	$numero_operacion = $_POST["sec_con_serv_pub_numero_operacion"];

	$fecha_pago = date("Y-m-d", strtotime($fecha_pago));
	$error = '';
	$id_tipo_servicio_publico = "";
	$estado = "";
	$nombre_tipo_servicio = "";
	$nombre_local = "";
	$periodo_consumo = "";
	$message = "";
	$status = true;
	$sel_query = $mysqli->query("SELECT sp.id, sp.id_tipo_servicio_publico, sp.periodo_consumo, sp.estado, t.nombre AS  nombre_tipo_servicio, lc.nombre AS  nombre_local
	FROM cont_local_servicio_publico AS sp
	INNER JOIN cont_tipo_servicio_publico AS t ON t.id = sp.id_tipo_servicio_publico
	INNER JOIN tbl_locales AS lc ON lc.id = sp.id_local
	WHERE sp.id = ".$id_recibo);
	while($sel = $sel_query->fetch_assoc()){
		$id_tipo_servicio_publico = $sel["id_tipo_servicio_publico"];
		$estado = $sel["estado"];
		$nombre_tipo_servicio = $sel["nombre_tipo_servicio"];
		$nombre_local = $sel["nombre_local"];
		$periodo_consumo = date("Y-m", strtotime($sel["periodo_consumo"]));
	}

	if ($estado != 2) {
		$result["http_code"] = 400;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["error"] = 'No se puede realizar el pago';
		echo json_encode($result);
		exit();
	}

	if($id_tipo_servicio_publico == 1)
	{
		$nombre_tipo_servicio = "VOUCHER LUZ";
		$path = "/var/www/html/files_bucket/contratos/servicios_publicos/luz/";
		$nombre_servicio = "voucher luz";
	} else if($id_tipo_servicio_publico == 2){
		$nombre_tipo_servicio = "VOUCHER AGUA";
		$path = "/var/www/html/files_bucket/contratos/servicios_publicos/agua/";
		$nombre_servicio = "voucher agua";
	}

	if(!empty($_FILES['sec_con_serv_pub_voucher_pago']['name']))
	{
		$fileServicioPublico = $_FILES['sec_con_serv_pub_voucher_pago']['name'];
		$tmpServicioPublico = $_FILES['sec_con_serv_pub_voucher_pago']['tmp_name'];
		$sizeServicioPublico = $_FILES['sec_con_serv_pub_voucher_pago']['size'];
		$extServicioPublico = strtolower(pathinfo($fileServicioPublico, PATHINFO_EXTENSION));

		$valid_extensions = array('jpeg', 'jpg', 'png', 'pdf');

		if(in_array($extServicioPublico, $valid_extensions))
		{
			//$path = "/var/www/html/files_bucket/contratos/servicios_publicos/luz/";
			$download = "/files_bucket/contratos/servicios_publicos/".$nombre_servicio."/";

			if (!is_dir($path)) 
			{
				mkdir($path, 0777, true);
			}

			$nombreFileUpload = $nombre_local."_".$nombre_tipo_servicio."_".$periodo_consumo.".".$extServicioPublico;
			$nombreDownload = $download.$nombreFileUpload;
			move_uploaded_file($tmpServicioPublico, $path. $nombreFileUpload);

			$query_update = "UPDATE cont_local_servicio_publico SET
					nombre_file_voucher = '".$nombreFileUpload."',
					extension_voucher = '".$extServicioPublico."',
					size_voucher = '".$sizeServicioPublico."',
					ruta_upload_voucher = '".$path."', 
					ruta_download_file_voucher = '".$nombreDownload."',
					fecha_pago = '".$fecha_pago."',
					fecha_carga_pago = '".date('Y-m-d H:i:s')."',
					numero_operacion = '".$numero_operacion."',
					estado = 3,
					user_updated_id = '".$login["id"]."',
					updated_at = '".date('Y-m-d')."' 
					WHERE id = ".$id_recibo;
			$mysqli->query($query_update);

			if($mysqli->error)
			{
				$status = false;
				$message = $mysqli->error;
			}
			else
			{
				$message = "Datos guardados correctamente";
				$status = true;
			}

		}
		else
		{
			$message = "La extension del file no es aceptado, solo son aceptado: jpeg, jpg, png, pdf";
			$status = false;
		}
	}
	else
	{
		$message = "Tiene que seleccionar un archivo";
		$status = false;

	}


	$return["status"] = $status;
	$return["message"] = $message;
	echo json_encode($return);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_observaciones") {

	$id_recibo = $_POST["id_recibo"];
	$observacion = $_POST["observacion"];
	$id_user = $login?$login['id']:null;
	$error = '';

	$query = "INSERT cont_local_servicio_publico_observaciones  (
		id_recibo,
		observacion,
		status,
		user_created_id,
		created_at
	) VALUES (
		".$id_recibo.",
		'".$observacion."',
		1,
		".$id_user.",
		now()
	)";
	
	$mysqli->query($query);
	$result=array();
	if($mysqli->error){
		$error .= 'Error al guardar la observacion del servicio: ' . $mysqli->error . $query;
	}

	$area = '';
	$correos = [];

	// obtener area
	$query = "SELECT ar.nombre
	FROM tbl_usuarios u 
	INNER JOIN tbl_personal_apt p ON p.id = u.personal_id
	INNER JOIN tbl_areas ar ON p.area_id = ar.id
	WHERE u.id = ".$id_user;
	$sel_query = $mysqli->query($query);
	while($sel = $sel_query->fetch_assoc()){
		$area = !Empty($sel["nombre"]) ? $sel["nombre"]:"";
	}

	// obtener correos
	$query = "SELECT c.correo
	FROM cont_local_servicio_publico_correos c
	WHERE c.status = 1 AND c.id_recibo = ".$id_recibo;
	$sel_query = $mysqli->query($query);
	
	// en caso que la observacion sea de contabilidad se notificara a los correos registros cuando se observo y caso contrario solo se notifica a contabilidad
	if ($area == "Contabilidad") {
		while($sel = $sel_query->fetch_assoc()){
			if (!Empty($sel["correo"])) {
				array_push($correos, $sel["correo"]);
			}
		}
	}
	
	if ($error == '') {
		//ENVIO DE CORREOS
		send_email_contrato_servicio_publico_observacion($id_recibo,$correos,null);
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["error"] = $error;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["error"] = $error;
	}
	echo json_encode($result);
	exit();
}

function sendEmailCajaChica($archivo_id,$correo) {
	include '/var/www/html/sys/mailer/class.phpmailer.php';

	include("db_connect.php");
	include("sys_login.php");

    try {
	//
	$sel_query = $mysqli->query("SELECT 
	l.periodo_consumo,
	l.numero_recibo,
	t.nombre AS nombre_servicio,
	lc.nombre AS nombre_local,
	lc.cc_id AS centro_costo	
	FROM cont_local_servicio_publico AS l
	INNER JOIN cont_tipo_servicio_publico AS t ON t.id = l.id_tipo_servicio_publico
	INNER JOIN tbl_locales AS lc ON lc.id = l.id_local
	WHERE l.id = ".$archivo_id."
	AND l.status = 1
	LIMIT 1");
	$body = "";
	$body .= '<html>';

	$email_user_created = '';
	$centro_costo = "";
	$id_recibo = "";
	$host = $_SERVER["HTTP_HOST"];
	while($sel = $sel_query->fetch_assoc())
	{
	
		$centro_costo = $sel["centro_costo"];
		$nombre_local = $sel["nombre_local"];
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

		$body .= '<thead>';
		$body .= '<tr>';
		$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
		$body .= '<b>Notificacion de pago de servicio para caja chica </b>';
		$body .= '</th>';
		$body .= '</tr>';
		$body .= '</thead>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Local:</b></td>';
			$body .= '<td>'.$sel["nombre_local"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Centro de Costo:</b></td>';
			$body .= '<td>'.$sel["centro_costo"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Tipo de Servicio:</b></td>';
			$body .= '<td>'.$sel["nombre_servicio"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Periodo:</b></td>';
			$body .= '<td>'.NombreMes($sel["periodo_consumo"]).'</td>';
		$body .= '</tr>';

		$body .= '</table>';

		}
		$body .= '</html>';
		$body .= "";
		//
        $mail = new PHPMailer(true);
        $mail->IsSMTP();
        $mail->CharSet    = 'utf-8';
        $mail->SMTPDebug  = 1;
        $mail->SMTPAuth   = true;
		$mail->Host       = "smtp.gmail.com";
		$mail->Port       = 465;
		$mail->SMTPSecure = "ssl";

        $sub_titulo_email = "";

		if (env('SEND_EMAIL') == 'test')
		{
			$sub_titulo_email = "TEST SISTEMAS: ";
		}

		$subject = $sub_titulo_email."Gestión - Caja chica - ".$centro_costo." ".$nombre_local." - Servicio Público";


        $mail->AddAddress($correo);
        
		$mail->Username = env('MAIL_GESTION_USER');
        $mail->Password = env('MAIL_GESTION_PASS');
        
        $mail->FromName = env('MAIL_GESTION_NAME');
        $mail->Subject  = $subject;
        $mail->Body     = $body;
        $mail->isHTML(true);
        $mail->send();

    } catch (phpmailerException $e) {
        $mail = new PHPMailer(true);
        $mail->IsSMTP();
        $mail->CharSet    = 'utf-8';
        $mail->SMTPDebug  = 1;
        $mail->SMTPAuth   = true;
        $mail->Host       = "smtp.gmail.com";
        $mail->Port       = 465;
        $mail->SMTPSecure = "ssl";

        $mail->Username = env('MAIL_GESTION_USER');
        $mail->Password = env('MAIL_GESTION_PASS');

		$mail->AddAddress($correo);
      
        $mail->FromName = "Apuesta Total - Solicitud Kasnet";
        $mail->Subject  = "Error de envio de emails :: Alertas phpmailerException";
        $mail->Body     = $e->errorMessage();
        $mail->send();

    } catch (Exception $e) {
        $mail = new PHPMailer(true);
        $mail->IsSMTP();
        $mail->CharSet    = 'utf-8';
        $mail->SMTPDebug  = 1;
        $mail->SMTPAuth   = true;
		$mail->Host       = "smtp.gmail.com";
		$mail->Port       = 465;
		$mail->SMTPSecure = "ssl";

		$mail->AddAddress($correo);
        
        $mail->Username = env('MAIL_GESTION_USER');
        $mail->Password = env('MAIL_GESTION_PASS');
       
        $mail->FromName = "Apuesta Total - Solicitud Kasnet - Fail";
        $mail->Subject  = "Error de envio de emails :: Kasnet";
        $mail->Body     = $e->getMessage();
        $mail->send();
    }
}
?>