<?php 	
$result = array();
include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER PAGADORES
//*******************************************************************************************************************
//*******************************************************************************************************************

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_pagadores") {

	$query = "	
			SELECT 
				u.id,
				CONCAT(p.nombre, ' ', p.apellido_paterno) as pagador,
				c.nombre AS cargo,
				g.nombre AS grupo,
				g.id grupo_id,
				u.estado
			FROM tbl_usuarios  u
				LEFT JOIN tbl_personal_apt p ON (p.id = u.personal_id)
				LEFT JOIN tbl_cargos c ON (c.id = p.cargo_id)
				LEFT JOIN tbl_usuarios_grupos g ON (g.id = u.grupo_id)
			WHERE u.estado = 1 
			#and u.grupo_id = 31 # televentas - pagador
				and c.id = 4
				ORDER BY CONCAT(p.nombre, ' ', p.apellido_paterno) ASC";

	$list_query = $mysqli->query($query);
	$list_register = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_register[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list_register) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
	} elseif (count($list_register) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_register;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los registros";
	}
}

//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER PROGRAMACIONES
//*******************************************************************************************************************
//*******************************************************************************************************************

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_programaciones_pagadores") {
	$pagador = $_POST["pagador"];
	$desde = $_POST["desde"];
	$hasta = $_POST["hasta"];

	$desde_time = str_replace('T', ' ', $desde);
    $hasta_time = str_replace('T', ' ', $hasta);

    $where_pagador = '';
    if($pagador > 0){
    	$where_pagador = ' AND tp.user_id = ' . $pagador;
    }

	$query = "	
			SELECT 
				IFNULL(tp.id, 0) id, 
				tp.user_id,
				CONCAT(IFNULL(p.nombre, ''), ' ', IFNULL(p.apellido_paterno,'')) as pagador,
				desde, hasta, created_at, created_user_id
			FROM tbl_televentas_programaciones tp
				INNER JOIN tbl_usuarios u ON tp.user_id = u.id
				INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
			WHERE tipo_programacion = 2 AND tp.desde >= '" . $desde_time . "' AND tp.hasta <= '" . $hasta_time . "' " . $where_pagador ;

	$list_query = $mysqli->query($query);
	$list_register = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_register[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list_register) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
	} elseif (count($list_register) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_register;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los registros";
	}
}

//*******************************************************************************************************************
//*******************************************************************************************************************
// GUARDAR PROGRAMACION
//*******************************************************************************************************************
//*******************************************************************************************************************

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_programacion_horario_pagador") {
	$pagador = $_POST["pagador"];
	$desde = $_POST["desde"];
	$hasta = $_POST["hasta"];

	$desde_time = str_replace('T', ' ', $desde);
    $hasta_time = str_replace('T', ' ', $hasta);

	$usuario_id = $login ? $login['id'] : null;
	$error = '';
	$query = "
				INSERT INTO tbl_televentas_programaciones 
				(user_id, desde, hasta,tipo_programacion, created_at, created_user_id, status)
				VALUES (" 
				. $pagador . ",'" 
				. $desde_time . "','" 
				. $hasta_time . "',
					2,
					now()," 
				. $usuario_id . ",1)";

	$mysqli->query($query);
	$result["query"] = $query;
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
		$error = $mysqli->error;
	}

	if ($error != '') {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
	} elseif ($error == '') {
		$result["http_code"] = 200;
		$result["status"] = "ok";
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_programaciones_pagadores_guardadas") {
	$desde = $_POST["desde"];
	$hasta = $_POST["hasta"];

	$desde_time = str_replace('T', ' ', $desde);
    $hasta_time = str_replace('T', ' ', $hasta);

	$query = "	
			SELECT 
				IFNULL(tp.id, 0) id, 
				tp.user_id,
				CONCAT(IFNULL(p.nombre, ''), ' ', IFNULL(p.apellido_paterno,'')) as pagador,
				desde, hasta, created_at, created_user_id
			FROM tbl_televentas_programaciones tp
				INNER JOIN tbl_usuarios u ON tp.user_id = u.id
				INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
			WHERE ((tp.desde >= '" . $desde_time . "' AND tp.desde <= '" . $hasta_time . "') 
				OR (tp.hasta >= '" . $desde_time . "' AND tp.hasta <= '" . $hasta_time . "'))
				AND  tipo_programacion = 2 ";

	$list_query = $mysqli->query($query);
	$list_register = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_register[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list_register) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
		$result["query"] = $query;
	} elseif (count($list_register) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_register;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los registros";
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_programaciones_pagadores_edicion") {
	$desde = $_POST["desde"];
	$hasta = $_POST["hasta"];
	$programacion = $_POST["programacion"];

	$desde_time = str_replace('T', ' ', $desde);
    $hasta_time = str_replace('T', ' ', $hasta);

	$query = "	
			SELECT 
				IFNULL(tp.id, 0) id, 
				tp.user_id,
				CONCAT(IFNULL(p.nombre, ''), ' ', IFNULL(p.apellido_paterno,'')) as pagador,
				desde, hasta, created_at, created_user_id
			FROM tbl_televentas_programaciones tp
				INNER JOIN tbl_usuarios u ON tp.user_id = u.id
				INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
			WHERE ((tp.desde >= '" . $desde_time . "' AND tp.desde <= '" . $hasta_time . "') 
				OR (tp.hasta >= '" . $desde_time . "' AND tp.hasta <= '" . $hasta_time . "')) 
				AND tp.id not in (" . $programacion . ")
				AND  tipo_programacion = 2";

	$list_query = $mysqli->query($query);
	$list_register = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_register[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list_register) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
		$result["query"] = $query;
	} elseif (count($list_register) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_register;
		$result["query"] = $query;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los registros";
	}
}

//*******************************************************************************************************************
//*******************************************************************************************************************
// ACTUALIZAR PROGRAMACION
//*******************************************************************************************************************
//*******************************************************************************************************************

if (isset($_POST["accion"]) && $_POST["accion"] === "actualizar_programacion_horario_pagador") {
	$programacion = $_POST["programacion"];
	$pagador = $_POST["pagador"];
	$desde = $_POST["desde"];
	$hasta = $_POST["hasta"];

	$desde_time = str_replace('T', ' ', $desde);
    $hasta_time = str_replace('T', ' ', $hasta);

	$usuario_id = $login ? $login['id'] : null;
	$error = '';
	$query = "
				UPDATE tbl_televentas_programaciones 
					SET desde = '" . $desde .  "', 
						hasta = '" . $hasta . "', 
						updated_at = now(), 
						updated_user_id = " . $usuario_id . "
				WHERE id = " . $programacion;

	$mysqli->query($query);
	$result["query"] = $query;
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
		$error = $mysqli->error;
	}

	if ($error != '') {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
	} elseif ($error == '') {
		$result["http_code"] = 200;
		$result["status"] = "ok";
	}
}





echo json_encode($result);
?>