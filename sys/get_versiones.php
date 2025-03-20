<?php
date_default_timezone_set("America/Lima");

$result=array();
include("db_connect.php");
include("sys_login.php");

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_menus_con_versiones") {
	$query = "SELECT 
		ms.id, 
		CONCAT(
			'[',
			ms.id,
			'] ', 
			IF(ms2.titulo IS NULL, '', CONCAT(ms2.titulo,' -> ')),
			ms.titulo
		) AS nombre
	FROM
		tbl_menu_sistemas ms
		LEFT JOIN tbl_menu_sistemas ms2 ON ms.relacion_id = ms2.id
		INNER JOIN tbl_versiones v ON ms.id = v.menu_id
	WHERE ms.titulo IS NOT NULL
	ORDER BY ms.titulo ASC";
	
	$list_query = $mysqli->query($query);
	$list = array();
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	if($mysqli->error){
		$result["http_code"] = 400;
		$result["result"] = "Ocurio un error al consultar los menús con versiones en la base de datos.";
		$result["consulta_error"] = $mysqli->error;
	} else if(count($list) == 0){
		$result["http_code"] = 400;
		$result["result"] = "No existen menús con versiones.";
	} elseif (count($list) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $list;
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_menus") {
	$query = "SELECT 
		ms.id, 
		CONCAT(
			'[',
			ms.id,
			'] ', 
			IF(ms2.titulo IS NULL, '', CONCAT(ms2.titulo,' -> ')),
			ms.titulo
		) AS nombre
	FROM
		tbl_menu_sistemas ms
		LEFT JOIN tbl_menu_sistemas ms2 ON ms.relacion_id = ms2.id
	WHERE ms.titulo IS NOT NULL
	ORDER BY ms.titulo ASC";
	
	$list_query = $mysqli->query($query);
	$list = array();
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	if($mysqli->error){
		$result["http_code"] = 400;
		$result["result"] = "Ocurio un error al consultar los menús con versiones en la base de datos.";
		$result["consulta_error"] = $mysqli->error;
	} else if(count($list) == 0){
		$result["http_code"] = 400;
		$result["result"] = "No existen menús con versiones.";
	} elseif (count($list) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $list;
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_creadores_de_versiones") {
	$query = "SELECT 
		u.id,
		CONCAT(IFNULL(p.nombre, ''),' ',IFNULL(p.apellido_paterno, ''),' ',IFNULL(p.apellido_materno, '')) AS nombre
	FROM
		tbl_personal_apt p
		INNER JOIN tbl_usuarios u ON p.id = u.personal_id
	WHERE p.area_id = 6
	ORDER BY nombre ASC";
	
	$list_query = $mysqli->query($query);
	$list = array();
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	if($mysqli->error){
		$result["http_code"] = 400;
		$result["result"] = "Ocurio un error al consultar a los creadores de versiones en la base de datos.";
		$result["consulta_error"] = $mysqli->error;
	} else if(count($list) == 0){
		$result["http_code"] = 400;
		$result["result"] = "No existen creadores de versiones.";
	} elseif (count($list) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $list;
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "listar_versiones_por_menu") {

	$sql = "SELECT 
		ms.titulo AS nombre, v.version
	FROM
		tbl_menu_sistemas ms
		INNER JOIN tbl_versiones v ON ms.id = v.menu_id
	ORDER BY titulo ASC";

	$query = $mysqli->query($sql);

	if ($mysqli->error) {
		$result["http_code"]      = 400;
		$result["status"]         = 'Se produjo el siguiente error al listar las imagenes el cliente';
		$result["error"]          = 'Error: ' . $mysqli->error;
		$result["consulta_error"] = $sql;
	} else {
		$html      = '';
		$row_count = $query->num_rows;
		if ($row_count == 0) {
			$html .= '<tr class="text-center">';
			$html .= '<td colspan=2>No se encontraron versiones por menú</td>';
			$html .= '</tr>';
		} else if ($row_count > 0) {
			while ($row = $query->fetch_assoc()) {
				$html .= '<tr>';
				$html .= '<td>' . $row["nombre"] . '</td>';
				$html .= '<td>' . $row["version"] . '</td>';
				$html .= '</tr>';
			}
		}
		$result["http_code"] = 200;
		$result["status"]    = "Datos obtenidos de gestion.";
		$result["result"]    = $html;
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "listar_historial_de_versiones") {

	$sql = "SELECT 
		ms.titulo AS menu,
		vd.version,
		vd.comentario,
		u.usuario AS creado_por,
		vd.created_at AS creado_el
	FROM
		tbl_menu_sistemas ms
		INNER JOIN tbl_versiones_detalle vd ON ms.id = vd.menu_id
		INNER JOIN tbl_usuarios u ON vd.user_created_id = u.id
	ORDER BY vd.id DESC";

	$query = $mysqli->query($sql);

	if ($mysqli->error) {
		$result["http_code"]      = 400;
		$result["status"]         = 'Se produjo el siguiente error al listar las imagenes el cliente';
		$result["error"]          = 'Error: ' . $mysqli->error;
		$result["consulta_error"] = $sql;
	} else {
		$html      = '';
		$row_count = $query->num_rows;
		if ($row_count == 0) {
			$html .= '<tr class="text-center">';
			$html .= '<td colspan=5>No se encontraron versiones por menú</td>';
			$html .= '</tr>';
		} else if ($row_count > 0) {
			while ($row = $query->fetch_assoc()) {
				$html .= '<tr>';
				$html .= '<td>' . $row["menu"] . '</td>';
				$html .= '<td>' . $row["version"] . '</td>';
				$html .= '<td>' . $row["comentario"] . '</td>';
				$html .= '<td>' . $row["creado_por"] . '</td>';
				$html .= '<td>' . $row["creado_el"] . '</td>';
				$html .= '</tr>';
			}
		}
		$result["http_code"] = 200;
		$result["status"]    = "Datos obtenidos de gestion.";
		$result["result"]    = $html;
	}
}

echo json_encode($result);
?>