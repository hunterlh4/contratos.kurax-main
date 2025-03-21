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


if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_cuenta_bancaria_listar")
{
	$login_empresa_id = $login?$login['empresa_id']:null;

	$usuario_id = $login?$login['id']:null;
	$usuario_area_id = $login?$login['area_id']:null;

	$param_usuario = $_POST['param_usuario'];
	$param_situacion = $_POST['param_situacion'];
	
	$where_usuario_asignado = "";
	$where_situacion = "";

	$red_id = 0;

	$select_red =
	"
	    SELECT
	        red_id
	    FROM tbl_razon_social
	    WHERE id = '".$login_empresa_id."'
	    LIMIT 1
	";

	$data_select_red = $mysqli->query($select_red);

	while($row = $data_select_red->fetch_assoc())
	{
	    $red_id = $row["red_id"];
	}

	if($param_usuario != 0)
	{
		$where_usuario_asignado = " AND a.usuario_asignado_id = '".$param_usuario."' ";
	}

	if($param_situacion != 0)
	{
		$where_situacion = " AND ac.verificado_tesoreria = '".$param_situacion."' ";
	}

	$query = "
		SELECT
			ac.id, ac.asignacion_id,
		    a.usuario_asignado_id,
		    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario,
		    rs.nombre AS empresa, 
		    za.nombre AS zona,
		    ac.banco_id, tb.nombre AS nombre_banco, 
		    ac.num_cuenta,
		    ac.created_at AS fecha_solicitud,
		    ac.verificado_tesoreria,
		    e.situacion,
		    ac.usuario_atencion_id, ac.fecha_atencion,
		    ac.status
		FROM mepa_asignacion_cuenta_bancaria ac
			INNER JOIN mepa_asignacion_caja_chica a
			ON ac.asignacion_id = a.id
			INNER JOIN tbl_usuarios tu
			ON a.usuario_asignado_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN tbl_razon_social rs
			ON a.empresa_id = rs.id
			INNER JOIN tbl_razon_social rse
			ON tp.razon_social_id = rse.id
			INNER JOIN mepa_zona_asignacion za
			ON a.zona_asignacion_id = za.id
			INNER JOIN tbl_bancos tb
			ON ac.banco_id = tb.id
			INNER JOIN cont_etapa e
			ON e.etapa_id = ac.verificado_tesoreria
		WHERE rse.red_id = '".$red_id."' 
		".$where_usuario_asignado."
		".$where_situacion."
		ORDER BY concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) ASC
	";

	$list_query = $mysqli->query($query);

	$data =  array();
	
	while($reg = $list_query->fetch_object()) 
	{
		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->usuario,
			"2" => $reg->empresa,
			"3" => $reg->zona,
			"4" => $reg->nombre_banco,
			"5" => $reg->num_cuenta,
			"6" => $reg->fecha_solicitud,
			"7" => $reg->situacion,
			"8" => ($reg->status == 1) 
				? 
					'<span class="badge badge-success">Activo</span>' 
				: 
					'<span class="badge badge-danger">Inactivo</span>',
            "9" => ($reg->verificado_tesoreria == 1)
            	?
            		'<a
						class="btn btn-info btn-xs"
						onclick="sec_mepa_cuenta_bancaria_atender_solicitud('.$reg->id.', '.$reg->asignacion_id.');";
                        data-toggle="tooltip" 
                        data-placement="top" 
                        title="Atender Solicitud">
                        <span class="fa fa-envelope"></span>
                         Atender Solicitud
                    </a>'
                :

	                '<a
							class="btn btn-default btn-xs"
	                        data-toggle="tooltip" 
	                        data-placement="top" 
	                        title="Ya se atendió">
	                        <span class="fa fa-envelope-open-o"></span>
	                         Ya se atendió
	                    </a>'

		);
	}
	
	$resultado = array(
		"sEcho" => 1,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);

	echo json_encode($resultado);
}

if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_cuenta_bancaria_atender_solicitud")
{
	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	$error = '';
	$query_update = "";

	if((int) $usuario_id > 0)
	{
		extract($_POST);
		
		$modal_form_id_asignacion = $_POST["modal_form_id_asignacion"];
		$modal_form_id_cuenta_bancaria = $_POST["modal_form_id_cuenta_bancaria"];
		$param_situacion = $_POST["mepa_cuenta_bancaria_modal_form_param_situacion"];
		$param_situacion_motivo = $_POST["mepa_cuenta_bancaria_modal_form_param_situacion_motivo"];

	
		if($param_situacion == 6)
		{
			// APROBADO
			$query_update = "
							UPDATE mepa_asignacion_cuenta_bancaria 
								SET verificado_tesoreria = '".$param_situacion."',
								    usuario_atencion_id = '".$usuario_id."',
								    fecha_atencion = '".$created_at."',
								    status = 0
							WHERE asignacion_id = '".$modal_form_id_asignacion."' AND status = 1
							";

			$mysqli->query($query_update);

			if($mysqli->error)
			{
				$error .= $mysqli->error;
				$result["http_code"] = 400;
				$result["status"] = "Ocurrio un error.";
				$result["error"] = $error;

				echo json_encode($result);
				exit();
			}

			$query_update_activar = "
							UPDATE mepa_asignacion_cuenta_bancaria 
								SET verificado_tesoreria = '".$param_situacion."',
								    usuario_atencion_id = '".$usuario_id."',
								    fecha_atencion = '".$created_at."',
								    status = 1
							WHERE id = '".$modal_form_id_cuenta_bancaria."'
							";

			$mysqli->query($query_update_activar);

			if($mysqli->error)
			{
				$error .= $mysqli->error;
				$result["http_code"] = 400;
				$result["status"] = "Ocurrio un error.";
				$result["error"] = $error;

				echo json_encode($result);
				exit();
			}
		}
		else
		{
			// RECHAZADO
			$query_update = "
							UPDATE mepa_asignacion_cuenta_bancaria 
								SET verificado_tesoreria = '".$param_situacion."',
								    usuario_atencion_id = '".$usuario_id."',
								    fecha_atencion = '".$created_at."',
								    observacion_atencion = '".$param_situacion_motivo."',
								    status = 0
							WHERE id = '".$modal_form_id_cuenta_bancaria."'
							";

			$mysqli->query($query_update);

			if($mysqli->error)
			{
				$error .= $mysqli->error;
				$result["http_code"] = 400;
				$result["status"] = "Ocurrio un error.";
				$result["error"] = $error;

				echo json_encode($result);
				exit();
			}
		}

		if ($error == '') 
		{
			$result["http_code"] = 200;
			$result["status"] = "Datos registrados correctamente.";
			$result["error"] = $error;

			send_email_atencion_cuenta_bancaria($modal_form_id_cuenta_bancaria);

			echo json_encode($result);
			exit();
		}
	}
	else
	{
		$result["http_code"] = 400;
        $result["error"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
        echo json_encode($resultado);
        exit();
	}
}

function send_email_atencion_cuenta_bancaria($cuenta_bancaria_id)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];
	$titulo_email = "";
	
	$sel_query = $mysqli->query("
		SELECT
			concat(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, '')) AS usuario_atencion,
			p.correo,
			tb.nombre AS nombre_banco, 
			ac.num_cuenta,
			ac.verificado_tesoreria,
    		e.situacion,
		    ac.created_at AS fecha_solicitud,
		    ac.fecha_atencion,
		    ac.observacion_atencion
		FROM mepa_asignacion_cuenta_bancaria ac
			INNER JOIN mepa_asignacion_caja_chica a
			ON ac.asignacion_id = a.id
		    LEFT JOIN tbl_usuarios u
			ON a.usuario_asignado_id = u.id
		    LEFT JOIN tbl_personal_apt p
			ON u.personal_id = p.id
		    INNER JOIN tbl_bancos tb
			ON ac.banco_id = tb.id
			INNER JOIN cont_etapa e
			ON e.etapa_id = ac.verificado_tesoreria
		WHERE ac.id = '".$cuenta_bancaria_id."'
	");

	$body = "";
	$body .= '<html>';

	while($sel = $sel_query->fetch_assoc())
	{
		$usuario_atencion = $sel["usuario_atencion"];
		$correo = $sel["correo"];
		$nombre_banco = $sel["nombre_banco"];
		$num_cuenta = $sel["num_cuenta"];
		$verificado_tesoreria = $sel["verificado_tesoreria"];
		$situacion = $sel["situacion"];
		$fecha_solicitud = $sel["fecha_solicitud"];
		$fecha_atencion = $sel["fecha_atencion"];
		$observacion_atencion = $sel["observacion_atencion"];
		
		$body .= '<div>';
			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

			$body .= '<thead>';
			
			$body .= '<tr>';
				$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
					$body .= '<b>Atención Solicitud Cuenta Bancaria</b>';
				$body .= '</th>';
			$body .= '</tr>';

			$body .= '</thead>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Banco:</b></td>';
				$body .= '<td>'.$nombre_banco.'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd"><b>Cuenta Bancaria:</b></td>';
				$body .= '<td>'.$num_cuenta.'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd"><b>Fecha Solicitud:</b></td>';
				$body .= '<td>'.$fecha_solicitud.'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Atención:</b></td>';
				$body .= '<td>'.$usuario_atencion.'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Fecha Atención:</b></td>';
				$body .= '<td>'.$fecha_atencion.'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Situación:</b></td>';
				$body .= '<td>'.$situacion.'</td>';
			$body .= '</tr>';

			if($verificado_tesoreria == 7)
			{
				$body .= '<tr>';
					$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Motivo Rechazo:</b></td>';
					$body .= '<td>'.$observacion_atencion.'</td>';
				$body .= '</tr>';
			}

			$body .= '</table>';
		$body .= '</div>';

	}
	$body .= '</html>';
	$body .= "";


	$titulo_email = "Gestion - Sistema Mesa de Partes - Atención Tesoreria Cuenta Bancaria";
	
	if(env('SEND_EMAIL') == 'test')
	{
		$cc = [
			$correo,
			//TESORERIA USUARIOS TEST
			"tesoreria.at.test@testtest.gmail.com"
		];
	}
	else
	{
		$cc = [
			$correo,
			//TESORERIA USUARIOS PRODUCCION
			"jorge.paima@testtest.apuestatotal.com",
			"katherine.zegarra@testtest.apuestatotal.com"
		];
	}
	
	$bcc = [
		"gestion@testtest.apuestatotal.com"
	];

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

?>