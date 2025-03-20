<?php
$result = array();
include "db_connect.php";
include "sys_login.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$result = array();

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_info_alerta") {

    $contrato_id = $_POST["contrato_id"];

    $query = "
	SELECT
	c.nombre_agente,
	c.fecha_inicio_agente,
	c.fecha_fin_agente,
	c.num_dias_para_alertar_vencimiento
	FROM cont_contrato c
	WHERE c.status = 1 AND c.contrato_id = '$contrato_id'
	";

    $list_query = $mysqli->query($query);
    $list = array();

	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

    $result["http_code"] = 200;
    $result["result"] = $list;
	$result["status"] = "Datos obtenidos de gestion.";

}

echo json_encode($result);
