<?php
date_default_timezone_set("America/Lima");

$result = array();
include "db_connect.php";
include "sys_login.php";

$usuario_id = $login ? $login['id'] : 0;

if (!((int) $usuario_id > 0)) {
	$result["http_code"] = 400;
	$result["status"]    = "Sesión perdida. Por favor vuelva a iniciar sesión.";
	echo json_encode($result);exit();
}

if (!isset($_POST["accion"])) {
	$result["http_code"] = 400;
	$result["status"]    = "Acción no válida.";
	$result["result"]    = $turno;
	echo json_encode($result);exit();
}

if ($_POST["accion"] === "guardar_version") {
	include "function_replace_invalid_caracters.php";

	$tipo_operacion_id = $_POST["tipo_operacion_id"];
	$menu_id           = $_POST["menu_id"];
	$comentario        = replace_invalid_caracters(trim($_POST["comentario"]));
	$version           = 0;

	$sql = "SELECT version
	FROM tbl_versiones
	WHERE menu_id = $menu_id
	";

	$query = $mysqli->query($sql);

	if ($mysqli->error) {
		$result["http_code"]      = 400;
		$result["status"]         = 'Se produjo el siguiente error al consultar la version existente';
		$result["error"]          = 'Error: ' . $mysqli->error;
		$result["consulta_error"] = $sql;
	} else {
		$html      = '';
		$row_count = $query->num_rows;
		if ($row_count == 0) {
		} else if ($row_count > 0) {
			while ($row = $query->fetch_assoc()) {
				$version = floatval($row["version"]) + 0.01;
			}
		}

		if ($tipo_operacion_id === 'new' && $version > 0) {
			$fecha_actual   = date('Y-m-d H:i:s');
			$insert_command = "INSERT INTO tbl_versiones_detalle(
			menu_id,
			version,
			comentario,
			status,
			user_created_id,
			created_at
		) VALUES (
			$menu_id,
			'$version',
			'$comentario',
			1,
			$usuario_id,
			'$fecha_actual'
		)";
			$mysqli->query($insert_command);

			if ($mysqli->error) {
				$result["http_code"]      = 400;
				$result["status"]         = "Ocurrió un error al guardar la version en la base de datos.";
				$result["insert_command"] = $insert_command;
				$result["insert_error"]   = $mysqli->error;
			}

			$sql = "UPDATE tbl_versiones
		SET version = $version
		WHERE menu_id = $menu_id
		";

			$query = $mysqli->query($sql);

			if ($mysqli->error) {
				$result["http_code"]      = 400;
				$result["status"]         = 'Se produjo el siguiente error al actualizar la version';
				$result["error"]          = 'Error: ' . $mysqli->error;
				$result["consulta_error"] = $sql;
			} else {
				$result["http_code"] = 200;
				$result["status"]    = "Datos obtenidos de gestion.";
			}
		}

	}
}

echo json_encode($result);
?>