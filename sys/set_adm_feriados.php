<?php
include '/var/www/html/sys/function_replace_invalid_caracters_contratos.php';
$return = array();
$return["memory_init"] = memory_get_usage();
$return["time_init"] = microtime(true);
include("global_config.php");
include("db_connect.php");
include("sys_login.php");


if (isset($_POST['accion']) &&   $_POST['accion'] == 'eliminar_feriados') {

	$id_feriados = $_POST['id_indice'];
	$estado = $_POST['estado_actual'] == 1 ? 0 : 1;
	$estado_otros_meses = $_POST['estado_actual'];
	$mes = $_POST['mes'];
	$anio = $_POST['anio'];
	$mensaje = $_POST['estado_actual'] == 1 ? 'Índice Deshabilitado' : 'Índice Habilitado';
	$udpate_command = "update cont_feriados  set estado = $estado where id = " . $id_feriados;
	$mysqli->query($udpate_command);
	// en caso se habilite un indice desabilitado , el otro que este activo en caso lo haya se desabilitará
	if ($estado_otros_meses == 0) {

		$udpate_command_meses = "UPDATE  cont_feriados  SET estado = 0 WHERE  mes = '$mes' AND anio = '$anio' and id <> " . $id_feriados;
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
	FROM cont_feriados ti 
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

if (isset($_POST["sec_adm_feriados_save"])) {
	$data = $_POST["sec_adm_feriados_save"];
	$fecha_feriado = $data["fecha_feriado"];
	$nombre_feriado = trim($data["nombre_feriado"]);
	$nombre_feriado = replace_invalid_caracters($nombre_feriado);

	if ($data["id"] == "new") {


		// validacion si existe la fecha , ademas que no debe existir feriados con el mismo nombre 
		$nombre_minisculas = $mysqli->real_escape_string(mb_strtolower($nombre_feriado));
		$select_command = "SELECT * FROM tbl_feriados WHERE LOWER(descripcion) = '$nombre_minisculas'";
		$result = $mysqli->query($select_command);

		$exists = $mysqli->query("SELECT *from tbl_feriados WHERE fecha = '$fecha_feriado'")->fetch_assoc();
		if ($exists) {
			$return["error"] = true;
			$return["error_msg"] = "Ya existe un feriado  para la fecha  '" . $fecha_feriado . "' con nombre de feriado:  '" . $exists['descripcion']."'";

			
		}
		// else if ($result->num_rows > 0 ) {
		// 	$exists_feriado = $result->fetch_assoc();
		// 	$return["error"] = true;
		// 	$return["error_msg"] = "Ya existe un feriado  con nombre '" . $exists_feriado['descripcion']."'  con fecha '".$exists_feriado['fecha']."'";
		// } 
		else {

			$insert_command = "
                INSERT INTO tbl_feriados (
                fecha ,
                descripcion,
                status,
                user_created_id,
                created_at,
                user_updated_id,
                updated_at
                )
                VALUES (
                '" . mysqli_real_escape_string($mysqli, $fecha_feriado) . "',
                '" . $nombre_feriado . "',
                1,
                " . $login["id"] . ",
                NOW(),
                " . $login["id"] . ",
                NOW()
                )";
			// var_dump($insert_command);exit();
			$mysqli->query($insert_command);
			$id_feriados = $mysqli->insert_id;




			if ($id_feriados == 0) {

				$return["id"] = $id_feriados;
				$return["error"] = "Feriado  para:  $fecha_feriado con nombre $nombre_feriado - No insertado";
				$return["error_msg"] = "Feriado  para:  $fecha_feriado con nombre $nombre_feriado - No insertado";
			} else {
				
				$return["id"] = $id_feriados;
				$return["mensaje"] = "Feriado  para:  $fecha_feriado con nombre $nombre_feriado - Insertado";
			}
		}
	} else {

		$nombre_minisculas = $mysqli->real_escape_string(mb_strtolower($nombre_feriado));
		$select_command = "SELECT * FROM tbl_feriados WHERE LOWER(descripcion) = '$nombre_minisculas' AND id!=".$data["id"];
		$result = $mysqli->query($select_command);

		$exists = $mysqli->query("SELECT *from tbl_feriados WHERE fecha = '$fecha_feriado' AND id!=".$data["id"])->fetch_assoc();
		if ($exists) {
			$return["error"] = true;
			$return["error_msg"] = "Ya existe un feriado  para la fecha '" . $fecha_feriado . "' con nombre de feriado '" . $exists['descripcion']."'";
		} 
		// else if ($result->num_rows > 0 ) {
		// 	$exists_feriado = $result->fetch_assoc();
		// 	$return["error"] = true;
		// 	$return["error_msg"] = "Ya existe un feriado  con nombre '" . $exists_feriado['descripcion']."'  con fecha '".$exists_feriado['fecha']."'";
		// } 
		else {

			$udpate_command = "UPDATE tbl_feriados SET
				descripcion = '" . $nombre_feriado . "',
				fecha = '".$fecha_feriado."',
				updated_at=NOW(),
				user_updated_id = '" . $login["id"] . "',
				status = '".  $data['estado_feriado']."'
			   WHERE id = " . $data["id"];
			$response = $mysqli->query($udpate_command);
			
			if ($response === true) {
				if ($mysqli->affected_rows > 0) {
				
					$return["mensaje"] = "Datos para: $nombre_feriado con fecha : $fecha_feriado - Actualizado";
				} else {
					$return["error_msg"] = "Datos para: $nombre_feriado con fecha : $fecha_feriado - Actualizado";
				}
			} else {
				$return["error_msg"] = "Datos para: $nombre_feriado con fecha : $fecha_feriado - NO se actualizo";
			}
		}
	}
}
 
if (isset($_POST["sec_feriados_list"])) {
	$data = $_POST["sec_feriados_list"];
	$comando_select = "
    select  
		tf.id,
		tf.fecha ,
		month(tf.fecha) AS mes,
		year(tf.fecha) AS anio,
		descripcion,
		IF(tf.status=1,'Activo','Inactivo') as status_cadena,
		tf.status,
		tu.usuario as usuario_created,
		tf.user_created_id  ,
		tu2.usuario as usuario_updated,
		tf.created_at ,
		tf.updated_at

	from tbl_feriados tf 
    left join tbl_usuarios tu 
    	on tu.id = tf.user_created_id
	left join tbl_usuarios tu2 
    	on tu2.id = tf.user_updated_id
    ORDER  BY tf.fecha ASC  
    ";
	$query = $mysqli->query($comando_select);
	$lista = [];
	while ($d = $query->fetch_assoc()) {
		$lista[] = $d;
	}
	$commando_select_meses = "
	SELECT *from tbl_meses";
	$query = $mysqli->query($commando_select_meses);
	$lista_meses = [];
	while ($d = $query->fetch_assoc()) {
		$lista_meses[] = $d;
	}
	$return["lista"] = $lista;
	$return["lista_meses"] = $lista_meses;
	$return["mensaje"] = "lista realizada correctamente";
}



$return["memory_end"] = memory_get_usage();
$return["time_end"] = microtime(true);
$return["memory_total"] = ($return["memory_end"] - $return["memory_init"]);
$return["time_total"] = ($return["time_end"] - $return["time_init"]);
print_r(json_encode($return));
