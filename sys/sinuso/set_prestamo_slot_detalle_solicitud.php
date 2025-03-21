<?php

date_default_timezone_set("America/Lima");
$result=array();
include("db_connect.php");
include("sys_login.php");

include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';	

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);


if (isset($_POST["accion"]) && $_POST["accion"]==="sec_prestamo_slot_detalle_validar_caja_abierta_tienda_receptor") 
{

	$prestamo_slot_id = $_POST["prestamo_slot_id"];

	$fecha_hoy = date('Y-m-d');

	// INICIO VALIDAR SI EXISTE UNA CAJA ABIERTA DEL LOCAL (DESTINO) A DONDE SE HARA EL PRESTAMO

	$existe_cajas = 0;

	$select_caja_abierta = "
				SELECT
					lc.id, lc.caja_tipo_id, c.id AS caja_id
				FROM tbl_caja_prestamo_slot cps
					INNER JOIN tbl_local_cajas lc
					ON cps.local_id_destino = lc.local_id
					INNER JOIN tbl_caja c
					ON lc.id = c.local_caja_id
				WHERE cps.id = '".$prestamo_slot_id."' 
					AND lc.caja_tipo_id = 1 
					AND c.estado = 0
					AND c.fecha_operacion = '".$fecha_hoy."'
			";

	$query_caja_abierta = $mysqli->query($select_caja_abierta);

	$cant_cajas = $query_caja_abierta->num_rows;


	if($cant_cajas > 0) 
	{
	    // SI EXISTE CAJA ABIERTA
	    $existe_cajas = 1;
	}

	// FIN VALIDAR SI EXISTE UNA CAJA ABIERTA DEL LOCAL (DESTINO) A DONDE SE HARA EL PRESTAMO
	
	if($mysqli->error)
	{
		$result["error"] = $mysqli->error;
	}
	
	if ($cant_cajas > 0) 
	{
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["existe_caja_abierta"] = $existe_cajas;
	} 
	else 
	{
		$result["http_code"] = 200;
		$result["existe_caja_abierta"] = $existe_cajas;
	}

	echo json_encode($result);
}

if (isset($_POST["accion"]) && $_POST["accion"]==="prestamo_slot_detalle_guardar_atencion") 
{
	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	$error = '';
	$query_update = "";

	$prestamo_slot_id = $_POST["sec_prestamo_slot_id"];
	$txt_situacion = $_POST["txt_situacion"];

	if((int)$usuario_id > 0)
	{
		// INICIO VALIDAR SI EXISTE UNA CAJA ABIERTA DEL LOCAL (DESTINO) A DONDE SE HARA EL PRESTAMO

		$existe_cajas = 0;

		$select_caja_abierta = "
					SELECT
						lc.id, lc.caja_tipo_id, c.id AS caja_id
					FROM tbl_caja_prestamo_slot cps
						INNER JOIN tbl_local_cajas lc
						ON cps.local_id_destino = lc.local_id
						INNER JOIN tbl_caja c
						ON lc.id = c.local_caja_id
					WHERE cps.id = '".$prestamo_slot_id."' AND lc.caja_tipo_id = 1 AND c.estado = 0
				";

		$query_caja_abierta = $mysqli->query($select_caja_abierta);

		$cant_cajas = $query_caja_abierta->num_rows;


		if($cant_cajas > 0) 
		{
		    // SI EXISTE CAJA ABIERTA
		    $row = $query_caja_abierta->fetch_assoc();
		    $id_caja_existente = $row["caja_id"];
		    $existe_cajas = 1;
		}
		else
		{
			$result["http_code"] = 400;
			$result["status"] = "";
			$result["error"] = "No se encontro caja abierta.";

			echo json_encode($result);
			exit();
		}

		// FIN VALIDAR SI EXISTE UNA CAJA ABIERTA DEL LOCAL (DESTINO) A DONDE SE HARA EL PRESTAMO

		$query_update = "
					UPDATE tbl_caja_prestamo_slot 
						SET caja_id_destino = '".$id_caja_existente."',
						usuario_atencion_id = '".$usuario_id."',
						fecha_atencion = '".date('Y-m-d H:i:s')."',
						situacion_atencion_etapa_id = 2
					WHERE id = '".$prestamo_slot_id."'
					";
	

		$mysqli->query($query_update);

		if($mysqli->error)
		{
			$error = $mysqli->error;
		}

		if ($error == '') 
		{
			$result["http_code"] = 200;
			$result["status"] = "Datos obtenidos de gestión.";
			$result["error"] = $error;
		} 
		else 
		{
			$result["http_code"] = 400;
			$result["status"] = "Error.";
			$result["error"] = $error;
		}
	}
	else
	{
		$result["http_code"] = 400;
        $result["status"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
	}

	echo json_encode($result);
}

if (isset($_POST["accion"]) && $_POST["accion"]==="prestamo_slot_detalle_eliminar_prestamo") 
{
	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	$error = '';
	$query_update = "";

	$prestamo_slot_id = $_POST["sec_prestamo_slot_id"];
	$txt_motivo = $_POST["txt_motivo"];

	if((int)$usuario_id > 0)
	{
		$query_update = "
					UPDATE tbl_caja_prestamo_slot
						SET situacion_atencion_etapa_id = 3,
						motivo_eliminacion = '".$txt_motivo."',
                        fecha_eliminacion = '".date('Y-m-d H:i:s')."'
					WHERE id = '".$prestamo_slot_id."'
					";
	

		$mysqli->query($query_update);

		if($mysqli->error)
		{
			$error = $mysqli->error;
		}

		if ($error == '') 
		{
			$result["http_code"] = 200;
			$result["status"] = "Datos registrados.";
			$result["error"] = $error;
			send_email_eliminar_prestamo_slot($prestamo_slot_id);
		} 
		else 
		{
			$result["http_code"] = 400;
			$result["status"] = "Error.";
			$result["error"] = $error;
		}
	}
	else
	{
		$result["http_code"] = 400;
        $result["status"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
	}

	echo json_encode($result);
}

function send_email_eliminar_prestamo_slot($id_prestamo)
{
    include("db_connect.php");
    include("sys_login.php");

    $respuesta_email = 0;

    $host = $_SERVER["HTTP_HOST"];
    $titulo_email = "";
    
    $sel_query = $mysqli->query("
        SELECT
            cps.id,
            lo.id AS local_origen_id,
            lo.nombre AS local_origen,
            cps.caja_id_origen,
            cps.monto,
            ld.id AS local_destino_id,
            ld.nombre AS local_destino,
            cps.caja_id_destino,
            cps.user_created_id,
            concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_solicitante,
            cps.created_at AS fecha_solicitud,
            cps.situacion_atencion_etapa_id,
            concat(IFNULL(tpa.nombre, ''),' ', IFNULL(tpa.apellido_paterno, ''), ' ', IFNULL(tpa.apellido_materno, '')) AS usuario_atencion,
            cps.fecha_atencion AS fecha_atencion,
            cps.motivo_eliminacion
        FROM tbl_caja_prestamo_slot cps
            INNER JOIN tbl_locales lo
            ON cps.local_id_origen = lo.id
            INNER JOIN tbl_locales ld
            ON cps.local_id_destino = ld.id
            INNER JOIN tbl_usuarios tu
            ON cps.user_created_id = tu.id
            INNER JOIN tbl_personal_apt tp
            ON tu.personal_id = tp.id
            LEFT JOIN tbl_usuarios tua
            ON cps.usuario_atencion_id = tua.id
            LEFT JOIN tbl_personal_apt tpa
            ON tua.personal_id = tpa.id
        WHERE cps.id = '".$id_prestamo."'
    ");

    $body = "";
    $body .= '<html>';

    $situacion_atencion = "";

    while($sel = $sel_query->fetch_assoc())
    {
        $id = $sel["id"];
        $local_origen_id = $sel["local_origen_id"];
        $local_origen = $sel["local_origen"];
        $caja_id_origen = $sel["caja_id_origen"];
        $monto = $sel["monto"];
        $local_destino_id = $sel["local_destino_id"];
        $local_destino = trim($sel["local_destino"]);
        $caja_id_destino = trim($sel["caja_id_destino"]);
        $user_created_id = trim($sel["user_created_id"]);
        $usuario_solicitante = trim($sel["usuario_solicitante"]);
        $fecha_solicitud = trim($sel["fecha_solicitud"]);
        $fecha_atencion = trim($sel["fecha_atencion"]);
        $situacion_atencion_etapa_id = trim($sel["situacion_atencion_etapa_id"]);
        $usuario_atencion = trim($sel["usuario_atencion"]);
        $motivo_eliminacion = trim($sel["motivo_eliminacion"]);

        if($situacion_atencion_etapa_id == 1)
        {
            $situacion_atencion = "Pendiente";
        }
        else if($situacion_atencion_etapa_id == 2)
        {
            $situacion_atencion = "Aprobado";
        }
        else if($situacion_atencion_etapa_id == 3)
        {
            $situacion_atencion = "Anulado";
        }
        

        $body .= '<div>';
        $body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

        $body .= '<thead>';
        
        $body .= '<tr>';
            $body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
                $body .= '<b>Atención Prestamo</b>';
            $body .= '</th>';
        $body .= '</tr>';

        $body .= '</thead>';

        $body .= '<tr>';
            $body .= '<td style="background-color: #ffffdd; width: 125px;"><b>Usuario Solicitante:</b></td>';
            $body .= '<td>'.$usuario_solicitante.'</td>';
        $body .= '</tr>';

        $body .= '<tr>';
            $body .= '<td style="background-color: #ffffdd"><b>Fecha Solicitud:</b></td>';
            $body .= '<td>'.$fecha_solicitud.'</td>';
        $body .= '</tr>';
        
        $body .= '<tr>';
            $body .= '<td style="background-color: #ffffdd; width: 125px;"><b>Tienda Origen:</b></td>';
            $body .= '<td>'.$local_origen.'</td>';
        $body .= '</tr>';
        
        $body .= '<tr>';
            $body .= '<td style="background-color: #ffffdd"><b>Caja Prestadora:</b></td>';
            $body .= '<td>'.$caja_id_origen.'</td>';
        $body .= '</tr>';

        $body .= '<tr>';
            $body .= '<td style="background-color: #ffffdd"><b>Monto:</b></td>';
            $body .= '<td>S/ '.number_format($monto, 2 , '.', ',').'</td>';
        $body .= '</tr>';

        $body .= '<tr>';
            $body .= '<td style="background-color: #ffffdd; width: 125px;"><b>Tienda Destino:</b></td>';
            $body .= '<td>'.$local_destino.'</td>';
        $body .= '</tr>';
        
        $body .= '<tr>';
            $body .= '<td style="background-color: #ffffdd"><b>Caja Receptora:</b></td>';
            $body .= '<td>'.$caja_id_destino.'</td>';
        $body .= '</tr>';

        $body .= '<tr>';
            $body .= '<td style="background-color: #ffffdd; width: 125px;"><b>Usuario Atención:</b></td>';
            $body .= '<td>'.$usuario_atencion.'</td>';
        $body .= '</tr>';

        $body .= '<tr>';
            $body .= '<td style="background-color: #ffffdd"><b>Fecha Atención:</b></td>';
            $body .= '<td>'.$fecha_atencion.'</td>';
        $body .= '</tr>';

        if($situacion_atencion_etapa_id == 3)
        {
        	$body .= '<tr>';
	            $body .= '<td style="background-color: #ffffdd"><b>Situación:</b></td>';
	            $body .= '<td style="color: red;">'.$situacion_atencion.'</td>';
	        $body .= '</tr>';

	        $body .= '<tr>';
	            $body .= '<td style="background-color: #ffffdd"><b>Motivo Eliminación:</b></td>';
	            $body .= '<td>'.$motivo_eliminacion.'</td>';
	        $body .= '</tr>';
        }
        else
        {
        	$body .= '<tr>';
	            $body .= '<td style="background-color: #ffffdd"><b>Situación:</b></td>';
	            $body .= '<td>'.$situacion_atencion.'</td>';
	        $body .= '</tr>';
        }
        
    
        $body .= '</table>';
        $body .= '</div>';

    }
        $body .= '<div>';
            $body .= '<br>';
        $body .= '</div>';

        $body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
            $body .= '<a href="'.$host.'/?sec_id=prestamo&sub_sec_id=slot_detalle_solicitud&id='.$id_prestamo.'&amp;param=2" target="_blank">';
                $body .= '<button style="background-color: green; color: white; cursor: pointer; width: 50%;">';
                    $body .= '<b>Ver Solicitud</b>';
                $body .= '</button>';
            $body .= '</a>';
        $body .= '</div>';

        $body .= '</html>';
        $body .= "";


    $titulo_email = "Préstamo entre tiendas - Tienda Receptora: ".$local_destino." - Anulación Prestamo ID: ".$id_prestamo;
    
    $cc = [
    ];

    $bcc = [
    ];

    // INICIO LISTAR USUARIOS DE LA TIENDAS ORIGEN Y DESTINO DEL PRESTAMO
    // USUARIOS: SUPERVISORES Y CAJEROS
    // AREA OPERACIONES: 21
    // CARGO CAJERO: 5
    // CARGO SUPERVISOR: 4
    // AREA: CONTROL INTERNO: 22
    $select_usuarios_enviar_a = 
    "
        SELECT DISTINCT
            p.correo
        FROM tbl_usuarios_locales ul
            LEFT JOIN tbl_usuarios u
            ON ul.usuario_id = u.id
            LEFT JOIN tbl_personal_apt p
            ON u.personal_id = p.id
        WHERE ul.local_id IN ('".$local_origen_id."', '".$local_destino_id."') AND ul.estado = 1 
            AND p.correo IS NOT NULL
            AND (p.area_id = 21 AND p.cargo_id IN (4 ,5) OR (p.area_id = 22))
    ";

    $sel_query_usuarios_enviar_a = $mysqli->query($select_usuarios_enviar_a);

    $row_count = $sel_query_usuarios_enviar_a->num_rows;

    if ($row_count > 0)
    {
        while($sel = $sel_query_usuarios_enviar_a->fetch_assoc())
        {
            if(!is_null($sel['correo']) AND !empty($sel['correo']))
            {
                array_push($cc, $sel['correo']);    
            }
        }
    }
    // FIN LISTAR USUARIOS DE LA TIENDAS ORIGEN Y DESTINO DEL PRESTAMO

    // INICIO LISTAR JEFE COMERCIALES DE LAS TIENDAS DEL PRESTAMO REALIZADO
    $select_usuarios_jc_enviar_a = 
    "
        SELECT
            p.correo
        FROM tbl_locales l
            INNER JOIN tbl_zonas z
            ON l.zona_id = z.id
            INNER JOIN tbl_personal_apt p
            ON p.id = z.jop_id
        WHERE l.id IN ('".$local_origen_id."', '".$local_destino_id."') AND p.correo IS NOT NULL
    ";
    
    $sel_query_usuarios_jc_enviar_a = $mysqli->query($select_usuarios_jc_enviar_a);

    $row_count_jc = $sel_query_usuarios_jc_enviar_a->num_rows;

    if ($row_count_jc > 0)
    {
        while($sel = $sel_query_usuarios_jc_enviar_a->fetch_assoc())
        {
            if(!is_null($sel['correo']) AND !empty($sel['correo']))
            {
                array_push($cc, $sel['correo']);    
            }
        }
    }
    // FIN LISTAR JEFE COMERCIALES DE LAS TIENDAS DEL PRESTAMO REALIZADO
    
    //INICIO: LISTAR USUARIOS DEL GRUPO - COPIA OCULTA
    $query_select_usuario_sistemas_cco = 
    "
        SELECT
            pg.id, pg.metodo, pg.status AS prestamo_grupo_estado,
            pu.usuario_id, p.nombre, p.correo
        FROM tbl_prestamo_mantenimiento_correo_grupo pg
            INNER JOIN tbl_prestamo_mantenimiento_correo_usuario pu
            ON pg.id = pu.tbl_prestamo_mantenimiento_correo_grupo_id
            LEFT JOIN tbl_usuarios u
            ON pu.usuario_id = u.id
            LEFT JOIN tbl_personal_apt p
            ON u.personal_id = p.id
        WHERE pg.metodo = 'prestamo_entre_tiendas_area_sistemas_cco' 
            AND pg.status = 1 
            AND pu.status = 1
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
    //FIN: LISTAR USUARIOS DEL GRUPO - COPIA OCULTA

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

        return $respuesta_email = true;

    }
    catch (Exception $e) 
    {
        return $respuesta_email = $e;
    }
}

?>