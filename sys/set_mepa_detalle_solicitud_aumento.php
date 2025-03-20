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


if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_detalle_solicitud_aumento_atencion") 
{
	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	$error = '';
	$query_update = "";

	$mepa_aumento_solicitud_id = $_POST["mepa_aumento_solicitud_id"];
	$txt_situacion = $_POST["txt_situacion"];
	$txt_motivo_rechazo = $_POST["txt_motivo_rechazo"];

	$sel_query_verificacion = $mysqli->query("
								SELECT
									aa.id, aa.tipo_solicitud_id, aa.asignacion_id, aa.monto
								FROM mepa_aumento_asignacion aa
								WHERE aa.id = '".$mepa_aumento_solicitud_id."'
							");

	while($sel = $sel_query_verificacion->fetch_assoc())
	{
		$id = $sel['id'];
		$tipo_solicitud_id = $sel['tipo_solicitud_id'];
		$asignacion_id = $sel['asignacion_id'];
		$monto = $sel['monto'];
	}


	//APROBADO
	if($txt_situacion == 6)
	{
		// SI ES AUMENTO
		if($tipo_solicitud_id == 9)
		{
			$query_update = "
					UPDATE mepa_aumento_asignacion 
						SET usuario_atencion_id = '".$usuario_id."', 
						situacion_etapa_id = '".$txt_situacion."'
					WHERE id = '".$mepa_aumento_solicitud_id."' ";	
		}
		// SI ES REDUCCION
		else
		{
			$query_update = "
					UPDATE mepa_aumento_asignacion 
						SET usuario_atencion_id = '".$usuario_id."', 
						situacion_etapa_id = '".$txt_situacion."',
						situacion_tesoreria_etapa_id = 11
					WHERE id = '".$mepa_aumento_solicitud_id."' ";

			$query_update_asignacion = "
							UPDATE mepa_asignacion_caja_chica 
								SET fondo_asignado = fondo_asignado - '".$monto."',
									saldo_disponible = saldo_disponible - '".$monto."'
							WHERE id = '".$asignacion_id."' 
							";
			$mysqli->query($query_update_asignacion);
		}
	}
	else
	{
		//RECHAZADO
		$query_update = "
					UPDATE mepa_aumento_asignacion 
						SET usuario_atencion_id = '".$usuario_id."', 
						situacion_etapa_id = '".$txt_situacion."',
						situacion_motivo = '".$txt_motivo_rechazo."'
					WHERE id = '".$mepa_aumento_solicitud_id."' ";
	}
	

	$mysqli->query($query_update);

	if($mysqli->error)
	{
		$error = $mysqli->error;
	}

	if ($error == '') 
	{
		$result["http_code"] = 200;
		$result["mepa_aumento_solicitud_id"] = $mepa_aumento_solicitud_id;
		$result["status"] = "Datos obtenidos de gestión.";
		$result["error"] = $error;
		//send_email_atencion_solicitud_asignacion_caja_chica($mepa_aumento_solicitud_id);
	} 
	else 
	{
		$result["http_code"] = 400;
		$result["mepa_aumento_solicitud_id"] = $mepa_aumento_solicitud_id;
		$result["status"] = "Error.";
		$result["error"] = $error;
	}
}

echo json_encode($result);

?>