<?php
$result=array();
include("db_connect.php");
include("sys_login.php");

include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_areas") {

	$query = "SELECT id, nombre FROM mkt_areas WHERE status = 1 ORDER BY id ASC";
	$list_query = $mysqli->query($query);
	$datos = array();
	while ($li = $list_query->fetch_assoc()) {
		$datos[] = $li;
	}
	$result["http_code"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $datos;
	echo json_encode($result);
	exit();

}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_productos") {

	$query = "SELECT id, nombre FROM mkt_productos WHERE status = 1 ORDER BY id ASC";
	$list_query = $mysqli->query($query);
	$datos = array();
	while ($li = $list_query->fetch_assoc()) {
		$datos[] = $li;
	}
	$result["http_code"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $datos;
	echo json_encode($result);
	exit();
	
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_tipo_requerimiento_estrategico") {

	$query = "SELECT id, nombre FROM mkt_tipo_requerimiento_estrategico WHERE status = 1 ORDER BY id ASC";
	$list_query = $mysqli->query($query);
	$datos = array();
	while ($li = $list_query->fetch_assoc()) {
		$datos[] = $li;
	}
	$result["http_code"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $datos;
	echo json_encode($result);
	exit();
	
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_tipo_solicitud") {

	$query = "SELECT id, nombre FROM mkt_tipo_solicitud WHERE status = 1 ORDER BY id ASC";
	$list_query = $mysqli->query($query);
	$datos = array();
	while ($li = $list_query->fetch_assoc()) {
		$datos[] = $li;
	}
	$result["http_code"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $datos;
	echo json_encode($result);
	exit();
	
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_estados") {

	$query = "SELECT id, nombre FROM mkt_estado_solicitud WHERE status = 1 ORDER BY id ASC";
	$list_query = $mysqli->query($query);
	$datos = array();
	while ($li = $list_query->fetch_assoc()) {
		$datos[] = $li;
	}
	$result["http_code"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $datos;
	echo json_encode($result);
	exit();
	
}

?>