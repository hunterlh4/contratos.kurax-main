<?php 
date_default_timezone_set("America/Lima");
$result=array();
include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_contrato_procesar_programacion_procesar"){
	//debugger;
	$programacion_id = $_POST["programacion_id"];
	$subdiario_id = $_POST["subdiario_id"];
	$id_user = $login?$login['id']:null;
	$fecha_transaccion = date("Y-m-d H:i:s");
	$error = '';

	$query = "UPDATE cont_programacion 
				set fecha_transaccion = '" . $fecha_transaccion
				. "', etapa_id = 3" 
				. ", subdiario_id = " . $subdiario_id 
				. ", user_updated_id = " . $id_user 
				. ", updated_at = '" . $fecha_transaccion 
				. "', user_process_id = " . $id_user 
				. ", process_at = '" . $fecha_transaccion 
				. "'  where id = " . $programacion_id;

	$mysqli->query($query);
	if($mysqli->error){
		$error .= 'Error al procesar la programación: ' . $mysqli->error . $query;
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