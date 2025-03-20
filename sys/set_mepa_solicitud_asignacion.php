<?php 

date_default_timezone_set("America/Lima");

$result=array();
include("db_connect.php");
include("sys_login.php");

include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';	

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_mepa_solicitud_asignacion_check_guardar_solo_check") 
{
	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	$error = '';

	$arreglo_data = $_POST["array_check_asignacion_aprobar"];
	$ids_data = json_decode($arreglo_data);

	foreach($ids_data as $item)
	{

		$query_update = "
					UPDATE mepa_asignacion_caja_chica 
						SET usuario_atencion_id = '".$usuario_id."', 
						situacion_etapa_id = 6,
						fecha_asignado = '".date('Y-m-d H:i:s')."'
					WHERE id = '".$item->item_id."' ";

		$mysqli->query($query_update);
	}

	if($mysqli->error)
	{
		$error .= $mysqli->error;
	}


	if ($error == '') 
	{
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestión.";
		$result["error"] = $error;
		
		foreach($ids_data as $item)
		{
			send_email_solicitud_asignacion_check_guardar_solo_check($item->item_id);
		}
	}
	else 
	{
		$result["http_code"] = 400;
		$result["status"] = "Datos obtenidos de gestión.";
		$result["error"] = $error;
	}
}

function send_email_solicitud_asignacion_check_guardar_solo_check($mepa_asignacion_caja_chica_id)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];
	$titulo_email = "";
	$where_enviar_email_con_cargos = "";
	
	$sel_query = $mysqli->query("
	SELECT
		ma.id, mts.nombre AS tipo_solicitud, concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, '')) AS usuario_asignado, tp.correo AS correo_usuario_asignado, tps.correo AS correo_usuario_solicitante,
	    concat(IFNULL(tpa.nombre, ''),' ', IFNULL(tpa.apellido_paterno, '')) AS usuario_atencion,
		ta.nombre AS usuario_area, tc.nombre AS usuario_cargo, ma.situacion_etapa_id, ce.situacion AS estado_solicitud, ma.situacion_motivo, ma.fondo_asignado,
	    ma.fecha_asignado, ma.motivo, tb.nombre AS nombre_banco, 
		ac.num_cuenta, ma.created_at AS fecha_solicitud, 
		uada.usuario_id AS usuario_aprobador_id
	FROM mepa_asignacion_caja_chica ma
		INNER JOIN mepa_tipos_solicitud mts
		ON ma.tipo_solicitud_id = mts.id
		INNER JOIN tbl_usuarios tu
		ON ma.usuario_asignado_id = tu.id
		INNER JOIN tbl_personal_apt tp
		ON tu.personal_id = tp.id
		INNER JOIN tbl_usuarios tus
		ON ma.user_created_id = tus.id
		INNER JOIN tbl_personal_apt tps
		ON tus.personal_id = tps.id
	    LEFT JOIN tbl_usuarios tua
		ON ma.usuario_atencion_id = tua.id
		LEFT JOIN tbl_personal_apt tpa
		ON tua.personal_id = tpa.id
		INNER JOIN tbl_areas ta
		ON tp.area_id = ta.id
		INNER JOIN tbl_cargos tc
		ON tp.cargo_id = tc.id
		INNER JOIN cont_etapa ce
		ON ma.situacion_etapa_id = ce.etapa_id
		INNER JOIN mepa_asignacion_cuenta_bancaria ac
		ON ac.asignacion_id = ma.id
		INNER JOIN tbl_bancos tb
		ON ac.banco_id = tb.id
		INNER JOIN mepa_usuario_asignacion_detalle uad
		ON ma.usuario_asignado_id = uad.usuario_id 
		AND uad.mepa_asignacion_rol_id = 3 AND uad.status = 1
		INNER JOIN mepa_usuario_asignacion ua
		ON uad.mepa_usuario_asignacion_id = ua.id
		INNER JOIN mepa_usuario_asignacion_detalle uada
		ON ua.id = uada.mepa_usuario_asignacion_id 
		AND uada.mepa_asignacion_rol_id = 2 AND uada.status = 1
	WHERE ma.id = '".$mepa_asignacion_caja_chica_id."' AND ac.status = 1
	");

	$body = "";
	$body .= '<html>';

	$usuario_creacion_correo = '';

	while($sel = $sel_query->fetch_assoc())
	{
		$tipo_solicitud = $sel["tipo_solicitud"];
		$usuario_asignado = $sel["usuario_asignado"];
		$correo_usuario_asignado = $sel["correo_usuario_asignado"];
		$correo_usuario_solicitante = $sel["correo_usuario_solicitante"];
		$usuario_atencion = $sel["usuario_atencion"];
		$usuario_area = $sel["usuario_area"];
		$usuario_cargo = trim($sel["usuario_cargo"]);
		$situacion_etapa_id = trim($sel["situacion_etapa_id"]);
		$estado_solicitud = trim($sel["estado_solicitud"]);
		$situacion_motivo = trim($sel["situacion_motivo"]);
		$fondo_asignado = trim($sel["fondo_asignado"]);
		$fecha_asignado = trim($sel["fecha_asignado"]);
		$motivo = $sel["motivo"];
		$nombre_banco = $sel["nombre_banco"];
		$num_cuenta = $sel["num_cuenta"];
		$fecha_solicitud = $sel["fecha_solicitud"];
		$usuario_aprobador_id = $sel["usuario_aprobador_id"];
		

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

		$body .= '<thead>';
		
		$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>Solicitud atendido</b>';
			$body .= '</th>';
		$body .= '</tr>';

		$body .= '</thead>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Atendido por:</b></td>';
			$body .= '<td>'.$sel["usuario_atencion"].'</td>';
		$body .= '</tr>';
		
		if($situacion_etapa_id == 6)
		{
			//APROBADO
			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd"><b>Fondo asignado:</b></td>';
				$body .= '<td>S/ '.$sel["fondo_asignado"].'</td>';
			$body .= '</tr>';
		}
		
		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Situacion:</b></td>';
			$body .= '<td>'.$sel["estado_solicitud"].'</td>';
		$body .= '</tr>';
		
		if($situacion_etapa_id == 7)
		{
			//RECHAZADO
			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Motivo:</b></td>';
				$body .= '<td>'.$sel["situacion_motivo"].'</td>';
			$body .= '</tr>';
		}

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha atención:</b></td>';
			$body .= '<td>'.$sel["fecha_asignado"].'</td>';
		$body .= '</tr>';

		if($situacion_etapa_id == 6)
		{
			//APROBADO
			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd"><b>Asunto:</b></td>';
				$body .= '<td>La actual solicitud esta aprobado por su área correspondiente, Tesoreria registrara el deposito correspondiente a su solicitud y lo notificara</td>';
			$body .= '</tr>';
		}
	
		$body .= '</table>';
		$body .= '</div>';

	}
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$host.'/?sec_id=mepa&sub_sec_id=detalle_solicitud_asignacion&id='.$mepa_asignacion_caja_chica_id.'" target="_blank">';
		    	$body .= '<button style="background-color: green; color: white; cursor: pointer; width: 50%;">';
					$body .= '<b>Ver Solicitud</b>';
		    	$body .= '</button>';
		    $body .= '</a>';
		$body .= '</div>';

		$body .= '</html>';
		$body .= "";

	$sub_titulo_email = "";

	if (env('SEND_EMAIL') == 'test')
	{
		$sub_titulo_email = "TEST SISTEMAS: ";
	}

	$titulo_email = $sub_titulo_email."Gestion - Sistema Mesa de Partes - Atención Solicitud Asignación Caja Chica";
	
	if($situacion_etapa_id == 6)
	{
		//APROBADO
		$titulo_email = $sub_titulo_email."Gestion - Sistema Mesa de Partes - Atención Solicitud Asignación Caja Chica - APROBADO";
	}
	else if($situacion_etapa_id == 7)
	{
		//RECHAZADO
		$titulo_email = $sub_titulo_email."Gestion - Sistema Mesa de Partes - Atención Solicitud Asignación Caja Chica - RECHAZADO";
	}

	$cc = [
		$correo_usuario_asignado,
		$correo_usuario_solicitante
	];

	$bcc = [
	];

	//INICIO: LISTAR USUARIOS DE ATENCION DE SOLICITUD DE ASIGNACION
	$query_select_usuario = 
	"
		SELECT
			cg.id, cg.metodo, cg.status AS mepa_grupo_estado,
			cu.usuario_id, p.nombre, p.correo
		FROM mepa_mantenimiento_correo_grupo cg
			INNER JOIN mepa_mantenimiento_correo_usuario cu
			ON cg.id = cu.mepa_mantenimiento_correo_grupo_id
			LEFT JOIN tbl_usuarios u
			ON cu.usuario_id = u.id
			LEFT JOIN tbl_personal_apt p
			ON u.personal_id = p.id
		WHERE cg.metodo = 'mepa_atencion_solicitud_asignacion' 
			AND cg.status = 1 
			AND cu.status = 1
	";

	$sel_query_select_usuario = $mysqli->query($query_select_usuario);

	$row_count = $sel_query_select_usuario->num_rows;

	if ($row_count > 0)
	{
		while($sel = $sel_query_select_usuario->fetch_assoc())
		{
			if(!is_null($sel['correo']) AND !empty($sel['correo']))
			{
				array_push($cc, $sel['correo']);
			}
		}
	}
	//FIN: LISTAR USUARIOS DE ATENCION DE SOLICITUD DE ASIGNACION

	//INICIO: LISTAR USUARIOS SISTEMAS DEL GRUPO - COPIA OCULTA
	$query_select_usuario_sistemas_cco = 
	"
		SELECT
			cg.id, cg.metodo, cg.status AS mepa_grupo_estado,
			cu.usuario_id, p.nombre, p.correo
		FROM mepa_mantenimiento_correo_grupo cg
			INNER JOIN mepa_mantenimiento_correo_usuario cu
			ON cg.id = cu.mepa_mantenimiento_correo_grupo_id
			LEFT JOIN tbl_usuarios u
			ON cu.usuario_id = u.id
			LEFT JOIN tbl_personal_apt p
			ON u.personal_id = p.id
		WHERE cg.metodo = 'mepa_area_sistemas_cco' 
			AND cg.status = 1 
			AND cu.status = 1
	";

	$sel_query_select_usuario_sistemas_cco = $mysqli->query($query_select_usuario_sistemas_cco);
	
	$row_count = $sel_query_select_usuario_sistemas_cco->num_rows;

	if ($row_count > 0)
	{
		while($sel = $sel_query_select_usuario_sistemas_cco->fetch_assoc())
		{
			if(!is_null($sel['correo']) AND !empty($sel['correo']))
			{
				array_push($bcc, $sel['correo']);
			}
		}
	}
	//FIN: LISTAR USUARIOS SISTEMAS DEL GRUPO - COPIA OCULTA

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
		return true;

	} 
	catch (Exception $e) 
	{
		return false;
	}
}

echo json_encode($result);

?>