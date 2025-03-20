<?php 
date_default_timezone_set("America/Lima");
$result=array();
include("db_connect.php");
include("sys_login.php");

include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';
require_once '/var/www/html/sys/helpers.php';

require_once '/var/www/html/env.php';
include '/var/www/html/sys/envio_correos.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_comprobante_pago") {
	// INICIO CARGAR COMPROBANTE DE PAGO
	$path = "/var/www/html/files_bucket/contratos/comprobantes_de_pago/";
	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");

	$programacion_id = $_POST["programacion_id"];
	$error = '';

	if (isset($_FILES['comprobante_de_pago']) && $_FILES['comprobante_de_pago']['error'] === UPLOAD_ERR_OK) {
		if (!is_dir($path)) mkdir($path, 0777, true);

		$fecha_de_pago = $_POST["fecha_pago"];
		$filename = $_FILES['comprobante_de_pago']['name'];
		$filenametem = $_FILES['comprobante_de_pago']['tmp_name'];
		$filesize = $_FILES['comprobante_de_pago']['size'];
		$ext = pathinfo($filenametem, PATHINFO_EXTENSION);

		if($filename != ""){
			$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
			$nombre_archivo = "COMPROBANTE_PAGO_" . date('YmdHis') . "." . $fileExt;
			move_uploaded_file($filenametem, $path . $nombre_archivo);
			$comando = "
			INSERT INTO cont_comprobantes_pago ( 
				nombre, 
				extension, 
				size,
				ruta, 
				user_created_id, 
				created_at
			) VALUES (
				'" . $nombre_archivo . "',
				'" . $fileExt . "',
				'" . $filesize . "',
				'" . $path . "',
				" . $usuario_id . ",
				'" . $created_at . "'
			)";

			$mysqli->query($comando);
			$insert_id = mysqli_insert_id($mysqli);
	
			// OBTENER LAS PROVIOSIONES DE LA PROGRMACION 
			$query_provisiones = "SELECT cpd.provision_id
                     FROM cont_programacion cp  
                     INNER JOIN cont_programacion_detalle cpd ON cpd.programacion_id = cp.id 
                     WHERE cp.id = ?";

			$stmt = $mysqli->prepare($query_provisiones);
			$stmt->bind_param("i", $programacion_id);
			$stmt->execute();
			$list_query = $stmt->get_result();

			$ids_de_acreedores = array();

			while ($li = $list_query->fetch_assoc()) {
				$ids_de_acreedores[] = $li["provision_id"];
			}

			$stmt->close();

			$ids_de_acreedores_string = implode(",", $ids_de_acreedores);
			$update_query = "UPDATE
								cont_provision
							SET etapa_id = 5
							WHERE id in (" . $ids_de_acreedores_string . ")";
							$mysqli->query($update_query);
						
							if ($mysqli->error) {
								$error = 'Error al actualizar la provisiones a pagadas: ' . $mysqli->error . $update_query;
								$result["error"] = $error;
							}
			// $query_provisiones = "SELECT 
			// 				cpd.provision_id
			// 		FROM 
			// 			cont_programacion cp  
						
			// 		INNER JOIN cont_programacion_detalle cpd 
			// 		ON cpd.programacion_id = cp.id 
			// 		WHERE	cp.id =".$programacion_id;
					
			// 		$list_query = $mysqli->query($query_provisiones);
			// 		$list_comprobante = array();
			// 		$num = 1;$ids_de_acreedores='';
			// 		while ($li = $list_query->fetch_assoc()) {
			// 			if ($num == 1) {
			// 				$ids_de_acreedores .= $li["provision_id"];
			// 			} else {
			// 				$ids_de_acreedores .= "," . $li["provision_id"];
			// 			}
			// 			$num += 1;
			// 			// $list_comprobante[] = $li;
			// 		}


			$comando2 = "
			UPDATE 
				cont_programacion 
			SET 
				etapa_id = 5, 
				fecha_pago = '$fecha_de_pago',
				comprobante_pago_id = $insert_id
			WHERE 
				id = $programacion_id
			";

			$mysqli->query($comando2);

			if($mysqli->error){
				$error .= 'Error al actualizar la programacion de pago: ' . $mysqli->error . $comando2;
			}

			// send_email_comprobante_de_pago($programacion_id, $path . $nombre_archivo, $fecha_de_pago);
		}
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

	$result["created_at"] = $created_at;
	echo json_encode($result);
}

function send_email_comprobante_de_pago($programacion_id, $path, $fecha_de_pago)
{
	include("db_connect.php");
	include("sys_login.php");

	$sql = "
	SELECT 
		p.moneda_id,
		p.tipo_anticipo_id,
		p.num_ruc AS num_ruc,
		b.nombre AS acreedor,
		p.mes,
		p.anio,
		p.periodo_fin AS fec_venc,
		p.importe AS importe_original
	FROM
		cont_programacion_detalle pd
		INNER JOIN cont_provision p ON pd.provision_id = p.id
		INNER JOIN cont_beneficiarios b ON p.contrato_id = b.contrato_id
	WHERE 
		pd.programacion_id = $programacion_id
		AND pd.status = 1
		AND p.status = 1
		AND b.status = 1
	";
	// var_dump($sql);exit();
	$query = $mysqli->query($sql);

	$body = "";
	$body .= '<html>';
	$body .= '<div>';

	while($sel = $query->fetch_assoc())
	{
		$acreedor = $sel['acreedor'];
		$mes = $sel['mes'];
		$anio = $sel["anio"];
		$fec_venc = $sel['fec_venc'];
		$importe_original = $sel["importe_original"];
		$moneda_id = $sel["moneda_id"];

		if ($moneda_id == 1) {
			$simbolo_moneda = 'S/.';
		} elseif ($moneda_id == 2) {
			$simbolo_moneda = 'USD';
		} 

		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

		$body .= '<thead>';
		$body .= '<tr>';
		$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
		$body .= '<b>Pago del servicio de Arrendamiento</b>';
		$body .= '</th>';
		$body .= '</tr>';
		$body .= '</thead>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Acreedor:</b></td>';
			$body .= '<td>'.$acreedor.' </td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Periodo</b></td>';
			$body .= '<td>'.str_pad($mes, 2, "0", STR_PAD_LEFT) . '-' . $anio.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Fecha de Pago:</b></td>';
			$body .= '<td>'.$fecha_de_pago.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Importe:</b></td>';
			$body .= '<td>'.$simbolo_moneda . ' ' . number_format($importe_original, 2, '.', ',').'</td>';
		$body .= '</tr>';
		
		$body .= '</table>';

		break;
	}

	$body .= '</div>';

	$body .= '</html>';
	$body .= "";

	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	// $lista_correos = $correos->send_email_comprobante_de_pago([]);
	
	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];

	$correos_adjuntos = "";

	if($correos_adjuntos != ""){
		$validate = true;
		$emails = preg_split('[,|;]',$correos_adjuntos);
		foreach($emails as $e){
		    if(preg_match('/^[_ña-z0-9-]+(\.[_ña-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/',trim($e)) == 0){
				$error_msg = "'" . $e . "'" ." no es un Correo Adjunto válido";
		        $result["http_code"] = 500;
				$result["status"] = "error";
				$result["mensaje"] = $error_msg;
				echo json_encode($result);
				die;
		    }
		    else
		    {
				$cc[] = $e;
		    }
		}
	}

	$request = [
		"subject" => "APUESTA TOTAL TEST - ¡Buenas noticias! Ya se realizo el pago de su renta",
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			$path
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
		$mail->AddAttachment($request["attach"][0]);

		$mail->send();

	} 
	catch (Exception $e) 
	{
		$resultado = $mail->ErrorInfo;
		echo json_encode($resultado);
	}

}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_comprobantes_x_programacion") {
	$usuario_id = $login?$login['id']:null;
	
	$programacion_id = $_POST["id_programacion"];
	$query1 = "
	SELECT 
		p.id AS programacion_id, 
		rs.nombre AS arrendatario,
		cp.id AS comprobante_id, 
		CONCAT(pa.nombre, ' ', pa.apellido_paterno, ' ', COALESCE(CONCAT(' ', pa.apellido_materno), '')) AS nombre_usuario,
		cp.created_at AS fecha_creacion,
		cp.extension, cp.nombre AS nombre_archivo, cp.ruta
	FROM 
		cont_programacion p
		INNER JOIN tbl_razon_social rs ON p.razon_social_id = rs.id
		INNER JOIN cont_comprobantes_pago cp ON p.comprobante_pago_id = cp.id
		INNER JOIN tbl_usuarios u ON cp.user_created_id = u.id
		INNER JOIN tbl_personal_apt pa ON u.personal_id = pa.id
	WHERE 
		p.id = $programacion_id
		AND cp.status = 1
	";
	
	$list_query = $mysqli->query($query1);
	$list_comprobante = array();

	while ($li = $list_query->fetch_assoc()) {
		$list_comprobante[] = $li;
	}

	$result2=array();

	if ($mysqli->error) {
		$result2["error"] = $mysqli->error;
	}
	
	if (count($list_comprobante) == 0) {
		$result2["http_code"] = 400;
		$result2["status"] = "No hay comprobantes.";
	} elseif (count($list_comprobante) > 0) {
		$result2["http_code"] = 200;
		$result2["status"] = "OK";
		$result2["result"] = $list_comprobante;
	} else {
		$result2["http_code"] = 400;
		$result2["status"] = "Ocurrió un error al consultar los comprobantes de la programacion.";
	}

	echo json_encode($result2);
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_lista_programaciones") {
	$usuario_id = $login?$login['id']:null;
	
	$wtipo_programacion_id = "";
	$wconcepto_id = "";
	$wtipo_pago_id = "";
	$wempresa_id = "";
	$wbanco_id = "";
	$wsituacion = "";
	$wetapa_id = "";

	if($_POST["tipo_programacion_id"] > 0){
		$wtipo_programacion_id = " and p.tipo_programacion_id = " .  $_POST["tipo_programacion_id"];	
	}
	if($_POST["id_concepto"] > 0){
		$wconcepto_id = " and p.tipo_concepto_id = " .  $_POST["id_concepto"];	
	}
	if($_POST["tipo_pago_id"] > 0){
		$wtipo_pago_id = " and p.tipo_pago_id = " .  $_POST["tipo_pago_id"];	
	}
	if($_POST["empresa_id"] > 0){
		$wempresa_id = " AND p.razon_social_id = " .  $_POST["empresa_id"];
	}
	if($_POST["banco_id"] > 0){
		$wbanco_id = " and p.num_cuenta_id = " .  $_POST["banco_id"];
	}
	if ($_POST["situacion_id"] > 0) {
		$wetapa_id = " AND p.etapa_id = " .  $_POST["situacion_id"];
	}

	$fecha_inicio = trim($_POST["fecha_inicio"]);
	$fecha_fin = trim($_POST["fecha_fin"]);

	$fecha_desde = substr($fecha_inicio,6,4) . '-' . substr($fecha_inicio,3,2) . '-' . substr($fecha_inicio,0,2);
	$fecha_hasta = substr($fecha_fin,6,4) . '-' . substr($fecha_fin,3,2) . '-' . substr($fecha_fin,0,2);

	$query1 = "
	SELECT 
		p.id AS programacion_id,
		p.numero programacion_numero,
		tpp.nombre AS tipo_programacion,
		p.fecha_programacion,
		p.tipo_concepto_id,
		tc.nombre concepto,
		p.tipo_pago_id,
		tp.nombre tipo_pago,
		rs.nombre AS arrendatario,
		p.num_cuenta_id,
		CONCAT(b.nombre , ' ', nc.num_cuenta_corriente) AS banco,
		p.moneda_id,
		(
			CASE
				WHEN p.moneda_id = 1 THEN 'MN'
				WHEN p.moneda_id = 2 THEN 'ME'
			END
		) AS moneda,
		p.importe AS importe,
		p.etapa_id,
		ep.nombre etapa
	FROM 
		cont_programacion p
		INNER JOIN cont_tipo_concepto tc on p.tipo_concepto_id = tc.id
		INNER JOIN cont_tipo_pago_programacion tp on p.tipo_pago_id = tp.id
		INNER JOIN cont_num_cuenta nc on p.num_cuenta_id = nc.id
		INNER JOIN tbl_razon_social rs ON p.razon_social_id = rs.id
		INNER JOIN tbl_bancos b ON nc.banco_id = b.id
		INNER JOIN cont_etapa_programacion ep on p.etapa_id = ep.id
		INNER JOIN cont_tipo_programacion tpp ON p.tipo_programacion_id = tpp.id
	WHERE 
		p.status = 1 AND 
		p.fecha_programacion BETWEEN '$fecha_desde' AND '$fecha_hasta' 
		$wtipo_programacion_id 
		$wconcepto_id 
		$wtipo_pago_id 
		$wempresa_id 
		$wbanco_id 
		$wetapa_id 
	ORDER BY programacion_id DESC"
	;
	
	$list_query = $mysqli->query($query1);
	$lista_programaciones = array();

	while ($li = $list_query->fetch_assoc()) {
		$lista_programaciones[] = $li;
	}

	$result3=array();

	if ($mysqli->error) {
		$result3["error"] = $mysqli->error;
	}
	
	if (count($lista_programaciones) == 0) {
		$result3["http_code"] = 400;
		$result3["status"] = "No hay programaciones.";
	} elseif (count($lista_programaciones) > 0) {
		$result3["http_code"] = 200;
		$result3["status"] = "OK";
		$result3["result"] = $lista_programaciones;
	} else {
		$result3["http_code"] = 400;
		$result3["status"] = "Ocurrió un error al consultar las programaciones.";
	}

	$result3["query1"] = $query1;
	echo json_encode($result3);
}

if (isset($_POST["accion"]) && $_POST["accion"]==="eliminar_programacion"){
	$programacion_id = $_POST["programacion_id"];
	$id_user = $login?$login['id']:null;
	$detele_at = date("Y-m-d H:i:s");
	$error = '';

	$query = "
	UPDATE 
		cont_programacion 
	SET 
		etapa_id = 4, 
		user_delete_id = $id_user, 
		delete_at = '$detele_at'
	WHERE id = $programacion_id
	";

	$mysqli->query($query);

	if($mysqli->error){
		$error .= 'Error al eliminar la programacion de pago: ' . $mysqli->error . $query;
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

	$result["query"] = $query;
	echo json_encode($result);
}
 ?>