<?php
$return = array();
$return["memory_init"] = memory_get_usage();
$return["time_init"] = microtime(true);
include("global_config.php");
include("db_connect.php");
include("sys_login.php");


if (isset($_POST['accion']) &&   $_POST['accion'] == 'eliminar_indice_inflacion') {

	$id_indice_inflacion = $_POST['id_indice'];
	$estado = $_POST['estado_actual'] == 1 ? 0 : 1;
	$estado_otros_meses = $_POST['estado_actual'];
	$mes = $_POST['mes'];
	$anio = $_POST['anio'];
	$mensaje = $_POST['estado_actual'] == 1 ? 'Índice Deshabilitado' : 'Índice Habilitado';
	$udpate_command = "update cont_indice_inflacion  set estado = $estado where id = " . $id_indice_inflacion;
	$mysqli->query($udpate_command);
	// en caso se habilite un indice desabilitado , el otro que este activo en caso lo haya se desabilitará
	if ($estado_otros_meses == 0) {

		$udpate_command_meses = "UPDATE  cont_indice_inflacion  SET estado = 0 WHERE  mes = '$mes' AND anio = '$anio' and id <> " . $id_indice_inflacion;
		$mysqli->query($udpate_command_meses);
		$return["mensaje"] = "Indice de inflación para:  $mes de $anio - Deshabilitado";;
	} else {
		$return["mensaje"] = $mensaje;
	}
}

if (isset($_POST['accion']) &&   $_POST['accion'] == 'validar_indices_activos_id') {

	$estado_otros_meses = $_POST['estado_actual'];
	$mes = $_POST['mes'];
	$anio = $_POST['anio'];
	$id = $_POST['id'];
	$comando_select =
		"SELECT
		ti.estado,
		ti.id
	FROM cont_indice_inflacion ti 
	WHERE ti.estado=1 AND ti.mes = '$mes' AND ti.anio = '$anio' and ti.id!=" . $id;
	$query = $mysqli->query($comando_select);
	// Verificar si hay al menos un registro

	if ($query->num_rows > 0) {
		$return["mensaje"] = 'Hay un registro habilitado para ' . $anio . ' y mes ' . $mes . ', se deshabilitará ';
	} else {
		$return['mensaje'] = 'No hay registro con el mes y año ingresado';
		$return["status"] = 0;
	}
}

if (isset($_POST["sec_adm_indice_inflacion_save"])) {
	$data = $_POST["sec_adm_indice_inflacion_save"];
	$mes = $data["mes"];
	$anio = $data["anio"];
	$valor_porc = $data["valor_porc"];
	$id = $data["id"];

	if ($valor_porc <= 0) {
		$return["error"] = true;
		$return["error_msg"] = "Ingresar índice de inflación mayor a 0.0: ";
	} else {
		if ($data["id"] == "new") {
			// validacion si existe indice para el mes y año recivido
			$exists = $mysqli->query("SELECT id, valor_porc from cont_indice_inflacion WHERE anio = '$anio' AND mes = '$mes'")->fetch_assoc();


			if ($exists) {

				$return["error"] = true;
				$return["error_msg"] = "Ya existe un índice de inflación activo para: " . $anio . " de mes : " . $mes . " con índice de inflación " . $exists['valor_porc'];
			} else {
				$insert_command = "
				INSERT INTO cont_indice_inflacion (
					anio,
					mes,
					valor_porc,
					estado,
					user_created_id,
					created_at,
					user_updated_id,
					updated_at
				) VALUES (
					'" . mysqli_real_escape_string($mysqli, $anio) . "',
					'" . mysqli_real_escape_string($mysqli, $mes) . "',
					" . $valor_porc . ",
					1,
					" . $login["id"] . ",
					NOW(),
					" . $login["id"] . ",
					NOW()
				)";
				$mysqli->query($insert_command);
				$id_indice_inflacion = $mysqli->insert_id;




				if ($id_indice_inflacion == 0) {

					$return["id"] = $id_indice_inflacion;
					$return["error"] = "Indice de inflación para:  $mes de $anio - No insertado";
					$return["error_msg"] = "Indice de inflación para:  $mes de $anio - No insertado";
				} else {
					$insert_command = "INSERT INTO cont_indice_inflacion_historial(
						indice_inflacion_id , 
						valor_porc,
						created_at,
						user_id)
						VALUES (  '" . $id_indice_inflacion . "',
									'" . $valor_porc . "',
									  now(),
									  " . $login["id"] . "
							   )";

					$mysqli->query($insert_command);
					if ($mysqli->error) {
						print_r($mysqli->error);
						echo $insert_command;
						exit();
					}
					$return["id"] = $id_indice_inflacion;
					$return["mensaje"] = "Indice de inflación para:  $mes de $anio - Insertado";
				}
			}
		} else {

			$exists = $mysqli->query("SELECT id from cont_indice_inflacion WHERE anio = '$anio' AND mes = '$mes' AND id!=" . $data["id"])->fetch_assoc();


			if ($exists) {
				$return["error"] = true;
				$return["error_msg"] = "Ya existe un índice de inflación activo para: " . $anio . " de mes : " . $mes;
			} else {
				$udpate_command = "UPDATE cont_indice_inflacion SET
						 valor_porc = '" . $valor_porc . "'
						,updated_at=NOW()
						,user_updated_id = '" . $login["id"] . "'
						WHERE id = '" . $data["id"] . "'  and anio ='" . $anio . "' and mes ='" . $mes . "' ";
				$response = $mysqli->query($udpate_command);




				if ($response === true) {
					if ($mysqli->affected_rows > 0) {
						$indice_inflacion_id = $data["id"];

						$insert_command = "INSERT INTO cont_indice_inflacion_historial(
							indice_inflacion_id , 
							valor_porc,
							created_at,
							user_id)
							VALUES (  '" . $indice_inflacion_id . "',
										'" . $valor_porc . "',
										  now(),
										  " . $login["id"] . "
								   )";

						$mysqli->query($insert_command);
						if ($mysqli->error) {
							print_r($mysqli->error);
							echo $insert_command;
							exit();
						}
						$return["mensaje"] = "Índice de inflación para: $mes de $anio - Actualizado";
					} else {
						$return["mensaje"] = "Índice de inflación para: $mes de $anio - No se actualizó";
					}
				} else {
					$return["mensaje"] = "Índice de inflación para: $mes de $anio - No se pudo actualizar";
				}
			}
		}
	}
}
if (isset($_POST["sec_indice_inflacion_historial_list"])) {
	$data = $_POST["sec_indice_inflacion__historial_list"];

	$comando_select = "
	SELECT h.valor_porc,u.usuario,h.created_at 
	FROM cont_indice_inflacion_historial h
	LEFT  JOIN tbl_usuarios u
	ON h.user_id = u.id
	LEFT JOIN cont_indice_inflacion i
	ON i.id=h.indice_inflacion_id
	WHERE h.indice_inflacion_id =" . $_POST['id'] . "
	ORDER BY h.created_at DESC";
	$query = $mysqli->query($comando_select);
	$lista = [];
	while ($d = $query->fetch_assoc()) {
		$lista[] = $d;
	}
	$return["lista"] = $lista;
	$return["mensaje"] = "lista realizada correctamente";
}

if (isset($_POST["sec_indice_inflacion_list"])) {
	$data = $_POST["sec_indice_inflacion_list"];
	$comando_select = "
    SELECT
		ti.estado,
		ti.id,
        ti.created_at,
	    ti.mes AS mes,
        ti.anio AS year,
		ti.updated_at as fecha,
		u.usuario as usuario_updated,
        ti.valor_porc as indice
	FROM cont_indice_inflacion ti
	LEFT JOIN tbl_usuarios u on u.id = ti.user_updated_id
	ORDER BY year DESC, ti.id DESC
    ";
	$query = $mysqli->query($comando_select);
	$lista = [];
	while ($d = $query->fetch_assoc()) {
		$lista[] = $d;
	}
	$return["lista"] = $lista;
	$return["mensaje"] = "lista realizada correctamente";
}



$return["memory_end"] = memory_get_usage();
$return["time_end"] = microtime(true);
$return["memory_total"] = ($return["memory_end"] - $return["memory_init"]);
$return["time_total"] = ($return["time_end"] - $return["time_init"]);
print_r(json_encode($return));
