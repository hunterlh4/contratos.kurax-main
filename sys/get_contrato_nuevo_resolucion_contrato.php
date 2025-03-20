<?php
$result=array();
include("db_connect.php");
include("sys_login.php");

include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';
include '/var/www/html/sys/function_replace_invalid_caracters_contratos.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_tipos_contrato") {
	$query = "SELECT id, nombre
	FROM cont_tipo_contrato
	WHERE id IN (1,2,5,6,7) AND status = 1
	ORDER BY id ASC";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_contratos") {
	// 1,2,5,6,7)
	if ($_POST['tipo_contrato_id'] == 1) {
		$query = "SELECT d.id, CONCAT(IFNULL(co.sigla,''), c.codigo_correlativo, ' | #', d.codigo, ' | ',  c.nombre_tienda) as nombre
				FROM cont_contrato_detalle AS d
				INNER JOIN cont_contrato AS c ON c.contrato_id = d.contrato_id
				LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
				WHERE c.status = 1 AND d.status = 1 AND c.tipo_contrato_id = 1 AND c.etapa_id = 5 
				AND (d.estado_resolucion = 0 OR d.estado_resolucion = 4)
				ORDER BY c.nombre_tienda ASC";
		$list_query = $mysqli->query($query);
	}
	if ($_POST['tipo_contrato_id'] == 2) {
		$query = "SELECT c.contrato_id as id, CONCAT(IFNULL(co.sigla,''), IFNULL(c.codigo_correlativo,''), ' | ',  IFNULL(c.razon_social,'')) as nombre
				FROM cont_contrato c
				LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
				WHERE c.tipo_contrato_id = 2 AND c.etapa_id = 5 
				AND (c.estado_resolucion = 0 OR c.estado_resolucion = 4)
				ORDER BY c.razon_social ASC";
		$list_query = $mysqli->query($query);
	}
	if ($_POST['tipo_contrato_id'] == 5) {
		$query = "SELECT c.contrato_id as id, CONCAT(IFNULL(co.sigla,''), IFNULL(c.codigo_correlativo,''), ' | ',  IFNULL(c.razon_social,'')) as nombre
					FROM cont_contrato c 
					LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
					WHERE tipo_contrato_id = 5
					AND (c.estado_resolucion = 0 OR c.estado_resolucion = 4)
					AND c.etapa_id = 5 ORDER BY c.razon_social";
		$list_query = $mysqli->query($query);
	}
	if ($_POST['tipo_contrato_id'] == 6) {
		$query = "SELECT c.contrato_id AS id, CONCAT(IFNULL(co.sigla,''), c.codigo_correlativo,  ' | ', IFNULL(c.nombre_agente,''), ' - ', IFNULL(c.c_costos,'')) AS nombre
		FROM cont_contrato c
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
		WHERE c.status = 1 AND c.tipo_contrato_id = 6 
		AND (c.estado_resolucion = 0 OR c.estado_resolucion = 4) AND c.etapa_id = 5";
		$list_query = $mysqli->query($query);
	}
	if ($_POST['tipo_contrato_id'] == 7) {
		$query = "SELECT c.contrato_id AS id, CONCAT(IFNULL(co.sigla,''), c.codigo_correlativo,  ' | ', c.detalle_servicio) AS nombre
				FROM cont_contrato c
				LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
				WHERE c.status = 1 AND c.tipo_contrato_id = 7 
				AND (c.estado_resolucion = 0 OR c.estado_resolucion = 4) AND c.etapa_id = 5";
		$list_query = $mysqli->query($query);
	}
				

	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_abogados") {

	$query = "SELECT  u.id ,
	CONCAT(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, ''),' ', IFNULL(p.apellido_materno, '')) AS nombre 
	FROM 
		tbl_usuarios u
		INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		INNER JOIN tbl_cargos AS c ON c.id = p.cargo_id
	WHERE 
		u.estado = 1
		AND p.estado = 1
		AND p.area_id IN (33)
	ORDER BY 
		p.nombre ASC,
		p.apellido_paterno ASC";
	
	$list_query = $mysqli->query($query);
	$list = array();
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	
	if(count($list) == 0){
		$result["http_code"] = 400;
		$result["result"] = "La provincia no existe.";
	} elseif (count($list) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $list;
	} else {
		$result["http_code"] = 400;
		$result["result"] = "La provincia no existe.";
	}
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_contratos") {
	
	$empresa_grupo_at_1 = $_POST['empresa_grupo_at_1'];
	$empresa_grupo_at_2 = $_POST['empresa_grupo_at_2'];

	$where_grupo_at_1 = "";
	$where_grupo_at_2 = "";
	if (!empty($empresa_grupo_at_1)) {
		$where_grupo_at_1 = " AND c.empresa_suscribe_id = ".$empresa_grupo_at_1;
	}
	if (!empty($empresa_grupo_at_2)) {
		$where_grupo_at_2 = " AND c.empresa_grupo_at_2 = ".$empresa_grupo_at_2;
	}
	$query = "SELECT c.contrato_id, c.fecha_inicio, c.detalle_servicio
	FROM cont_contrato c
	INNER JOIN tbl_razon_social rs1 ON c.empresa_suscribe_id = rs1.id
	INNER JOIN tbl_razon_social rs2 ON c.empresa_grupo_at_2 = rs2.id
	WHERE c.status = 1 AND c.tipo_contrato_id = 7 AND c.etapa_id = 5
	$where_grupo_at_1
	$where_grupo_at_2
	";
	$list_query = $mysqli->query($query);
	$list = [];

	if (!empty($empresa_grupo_at_1) || !empty($empresa_grupo_at_2)) {
		while ($li = $list_query->fetch_assoc()) {
			$date = new DateTime($li['fecha_inicio']);
			$fecha_inicio = $date->format('Y-m-d');
			array_push($list,array(
				'id' => $li['contrato_id'],
				'nombre' => $fecha_inicio.' - '.$li['detalle_servicio'],
			));
		}
	}
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_cargos") {

	$query = "SELECT c.id, c.nombre FROM tbl_cargos AS c WHERE c.estado = 1 ORDER BY c.nombre ASC";
	$list_query = $mysqli->query($query);
	
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_directores") {

	$query = "SELECT 
		u.id, 
		CONCAT(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, ''),' ', IFNULL(p.apellido_materno, '')) AS nombre 
	FROM 
		tbl_usuarios u
		INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		INNER JOIN cont_usuarios_directores ud ON u.id = ud.user_id
	WHERE 
		u.estado = 1
		AND ud.status = 1
	ORDER BY 
		p.nombre ASC,
		p.apellido_paterno ASC";
	$list_query = $mysqli->query($query);
	
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}



if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_cargo_aprobante") {

	
	$usuario_id = $_POST['aprobante_id'];
	if(isset( $_POST['aprobante_id']) && !($usuario_id == "0" || $usuario_id == "A") ){
		$query = "SELECT pe.id, pe.cargo_id FROM tbl_usuarios AS u 
		INNER JOIN tbl_personal_apt AS pe ON pe.id = u.personal_id AND pe.estado = 1
		WHERE u.id = ".$usuario_id;
		$list_query = $mysqli->query($query);
		$data = $list_query->fetch_assoc();

		if(isset($data['cargo_id'])){
			$result["status"] = 200;
			$result["message"] = "Datos obtenidos de gestion.";
			$result["result"] = $data['cargo_id'];
			echo json_encode($result);
			exit();
		}

		$result["status"] = 400;
		$result["message"] = "Datos obtenidos de gestion.";
		$result["result"] = 0;
		echo json_encode($result);
		exit();
	}else{
		$result["status"] = 400;
		$result["message"] = "Datos obtenidos de gestion.";
		$result["result"] = 0;
		echo json_encode($result);
		exit();
	}


}

?>