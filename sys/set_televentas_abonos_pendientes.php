<?php

$result = array();
include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('America/Lima');

/*
  ini_set('display_errors', 0);
  ini_set('display_startup_errors', 0);
  error_reporting(0);
*/

function get_turno() {
	global $login;
	global $mysqli;
	$usuario_id = $login['id'];
	//$command ="SELECT id FROM tbl_caja WHERE estado=0 AND usuario_id=".$usuario_id;
	$command = "
		SELECT
			sqc.id,
			ssql.id local_id,
			ssql.cc_id
		FROM
			tbl_caja sqc
			JOIN tbl_local_cajas sqlc ON sqlc.id = sqc.local_caja_id
			JOIN tbl_locales ssql ON ssql.id = sqlc.local_id 
		WHERE
			sqc.estado = 0 
			AND sqc.usuario_id = '" . $usuario_id . "' 
		ORDER BY sqc.id DESC
		LIMIT 1 
		";
	$list_query = $mysqli->query($command);
	$list = array();
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if ($mysqli->error) {
		print_r($mysqli->error);
	}
	return $list;
}

function resizeImage($resourceType, $image_width, $image_height) {
	$imagelayer = [];
	if ($image_width < 1920 && $image_height < 1080) {
		//mini
		$resizewidth_mini = 100;
		$resizeheight_mini = 100;
		$imagelayer[0] = imagecreatetruecolor($image_width, $image_height);
		imagecopyresampled($imagelayer[0], $resourceType, 0, 0, 0, 0, $image_width, $image_height, $image_width, $image_height);
		//mini
		$imagelayer[1] = imagecreatetruecolor($resizewidth_mini, $resizeheight_mini);
		imagecopyresampled($imagelayer[1], $resourceType, 0, 0, 0, 0, $resizewidth_mini, $resizeheight_mini, $image_width, $image_height);
	} else {
		$ratio = $image_width / $image_height;
		$escalaW = 1920 / $image_width;
		$escalaH = 1080 / $image_height;
		if ($ratio > 1) {
			$resizewidth = $image_width * $escalaW;
			$resizeheight = $image_height * $escalaW;
		} else {
			$resizeheight = $image_height * $escalaH;
			$resizewidth = $image_width * $escalaH;
		}
		//mini
		$resizewidth_mini = 100;
		$resizeheight_mini = 100;
		$imagelayer[0] = imagecreatetruecolor($resizewidth, $resizeheight);
		imagecopyresampled($imagelayer[0], $resourceType, 0, 0, 0, 0, $resizewidth, $resizeheight, $image_width, $image_height);
		//mini
		$imagelayer[1] = imagecreatetruecolor($resizewidth_mini, $resizeheight_mini);
		imagecopyresampled($imagelayer[1], $resourceType, 0, 0, 0, 0, $resizewidth_mini, $resizeheight_mini, $image_width, $image_height);
	}
	return $imagelayer;
}

$con_db_name   = env('DB_DATABASE_YAPE');
$con_host      = env('DB_HOST_YAPE');
$con_user      = env('DB_USERNAME_YAPE');
$con_pass      = env('DB_PASSWORD_YAPE');
$mysqli2 = new mysqli($con_host,$con_user,$con_pass,$con_db_name,3306);
if ($mysqli2->connect_error){
	$result["http_code"] = 500;
	$result["status"] = "Conexión fallida: %s\n" . $mysqli2->connect_error;
	throw new Exception("Conexión fallida: %s\n" . $mysqli2->connect_error);
	echo json_encode($result);exit();
}


//*******************************************************************************************************************
//*******************************************************************************************************************
// LISTAR ABONOS (PENDIENTES - VALIDADOS)
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "listar_abonos") {

	$busqueda_fecha_inicio  = $_POST["fecha_inicio"];
	$busqueda_fecha_fin     = $_POST["fecha_fin"];
	$busqueda_banco         = $_POST["banco"];
	$busqueda_estado        = $_POST["estado"];
	$nro_operacion          = $_POST["nro_operacion"];

	$usuario_id = $login ? $login['id'] : null;
	if (!((int) $usuario_id > 0)) {
		$result["http_code"] = 400;
		$result["status"] = "Sesión perdida. Por favor vuelva a iniciar sesión.";
		echo json_encode($result);exit();
	}

	$where_banco = "";
	$yape_where_banco = "";
	if ((int) $busqueda_banco > 0) {
		$where_banco = " AND tap.banco_id = '" . $busqueda_banco . "' ";

		switch ( (int)$busqueda_banco ) {
			case 3:
				$yape_where_banco = " AND t.description = 'Business' ";
				break;
			case 4:
				$yape_where_banco = " AND t.description = 'Mulfood' ";
				break;
			case 14:
				$yape_where_banco = " AND t.description = 'Televentas' ";
				break;
			case 15:
				$yape_where_banco = " AND t.description = 'Teleservicios' ";
				break;
			default;
				break;
		}
	}

	$where_estado = '';
	$yape_where_estado = '';
	if ((int) $busqueda_estado > 0) {
		if ( $busqueda_estado == 3 ) {
			$where_estado = " AND 1 = 0 ";
		} else {
			$where_estado = " AND tap.estado_abono_id = '" . $busqueda_estado . "' ";
		}

		switch ( (int)$busqueda_estado ) {
			case 1:
				$yape_where_estado = " AND t.state = 'pending' ";
				break;
			case 2:
				$yape_where_estado = " AND t.state = 'validated' ";
				break;
			case 3:
				$yape_where_estado = " AND t.state = 'cancelled' ";
				break;
			default;
				break;
		}
	}

	$where_busqueda_nro_operacion = '';
	$yape_where_busqueda_nro_operacion = '';
	if ( strlen($nro_operacion) > 0) {
		$where_busqueda_nro_operacion = " AND tap.nro_operacion = '" . $nro_operacion . "' ";

		$yape_where_busqueda_nro_operacion = " AND 1 = 0 ";
	}

	$menu_t = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'televentas_abonos_pendientes' LIMIT 1")->fetch_assoc();
	$menu_id = $menu_t["id"];

	$tls_continuar = false;
	$diff = 0;
	if (!in_array("consulta_anteriores", $usuario_permisos[$menu_id])) {
		$date1 = new DateTime("now");
		$date2 = new DateTime($busqueda_fecha_inicio);
		$diff = $date1->diff($date2);
		$diff = $diff->days;
	}else{
		$diff = 0;
	}

	if($diff > 1){
		$result["http_code"] = 400;
		$result["status"] = "No puede consultar registros de más de 1 dia de antiguedad.";
		echo json_encode($result);exit();
	}

	$where_fecha_inicio = " AND tap.fecha_operacion >= '" . $busqueda_fecha_inicio . "' ";
	$where_fecha_fin    = " AND tap.fecha_operacion <= '" . $busqueda_fecha_fin . "' ";
	
	$yape_where_fecha_inicio = " AND DATE(t.register_date) >= '" . $busqueda_fecha_inicio . "' ";
	$yape_where_fecha_fin    = " AND DATE(t.register_date) <= '" . $busqueda_fecha_fin . "' ";

	$con_db_name   = env('DB_DATABASE_YAPE');
	$con_host      = env('DB_HOST_YAPE');
	$con_user      = env('DB_USERNAME_YAPE');
	$con_pass      = env('DB_PASSWORD_YAPE');
	$mysqli2 = new mysqli($con_host,$con_user,$con_pass,$con_db_name,3306);
	if ($mysqli2->connect_error){
		$result["http_code"] = 500;
		$result["status"] = "Conexión fallida: %s\n" . $mysqli2->connect_error;
		throw new Exception("Conexión fallida: %s\n" . $mysqli2->connect_error);
		echo json_encode($result);exit();
	}


	$cmd_valid_yape_pending = "
		SELECT 
			t.id cod_transaccion,
			DATE(t.register_date) fecha_operacion,
			TIME(t.register_date) hora_operacion,
			'2' medio_id,
			'Yape - APK' nombre_medio,
			'' banco_id,
			CONCAT('APK', ' - ', t.description) nombre_banco,
			'' nombre_imagen,
			t.created_at fecha_registro,
			t.amount monto,
			'' nro_operacion,
			'0.00' comision_id,
			'' observacion,
			CASE
				WHEN t.state = 'pending' THEN 1
				WHEN t.state = 'validated' THEN 2
				WHEN t.state = 'cancelled' THEN 3
				ELSE ''
			END estado_abono,
			'' usuario,

			'' cliente_id,
			t.person cliente,
			
			'' usuario_validador,
			'' fecha_validacion,
			'APK' origen_abono_pendiente
		FROM `at-yape`.transactions t
		WHERE t.state IN ( 'pending', 'validated', 'cancelled' )
			$yape_where_banco
			$yape_where_estado
			$yape_where_fecha_inicio
			$yape_where_fecha_fin
			$yape_where_busqueda_nro_operacion
		ORDER BY id DESC
	";
	$result["cmd_valid_yape_pending"] = $cmd_valid_yape_pending;
	$list_yape_pending = $mysqli2->query($cmd_valid_yape_pending);
	if ($mysqli2->error) {
		// sec_tlv_log($hash . 'Error Consultar en bd yape - solicitud');
		// sec_tlv_log($hash . $mysqli2->error);
		$result["consulta_error"] = $mysqli2->error;
		echo json_encode($result);exit();
	}
	$list_transaction_pending = array();
	while ($li = $list_yape_pending->fetch_assoc()) {
		$list_transaction_pending[] = $li;
		$result["list_transaction_pending"] = $list_transaction_pending;
	}

	$query_1 = "
		SELECT 
			tap.id AS cod_transaccion,
			tap.fecha_operacion,
			tap.hora_operacion,
			tap.medio_id AS medio_id,
			tapm.nombre AS nombre_medio,
			IFNULL(tap.banco_id, '') AS banco_id,
			IFNULL(ca.cuenta_descripcion, '') AS nombre_banco,
			IFNULL(tap.name_file, '') AS nombre_imagen,
			tap.created_at AS fecha_registro,
			IFNULL(tap.monto, 0) AS monto,
			IFNULL(tap.nro_operacion, 0) AS nro_operacion,
			IFNULL(tap.comision_id, 0) AS comision_id,
			IFNULL(tap.observacion, '') as observacion,
			IFNULL(tap.estado_abono_id, '') as estado_abono,
			IFNULL(u.usuario, '') AS usuario,
			IFNULL(tap.cliente_id, '') AS cliente_id,
			IF(tap.cliente_id > 0 OR isnull(tap.cliente_id),
				IFNULL(CONCAT( tc.nombre, ' ', IFNULL( tc.apellido_paterno, '' ), ' ', IFNULL( tc.apellido_materno, '' ) ), ''),
				'') AS cliente,
			IFNULL(u_2.usuario, '') AS usuario_validador,
			IFNULL(tct.created_at, '') AS fecha_validacion,
			'TLS' origen_abono_pendiente
		FROM
			tbl_televentas_abonos_pendientes tap
			INNER JOIN tbl_cuentas_apt ca ON ca.id = tap.banco_id
			INNER JOIN tbl_televentas_abonos_pendientes_medio tapm ON tapm.id = tap.medio_id
			INNER JOIN tbl_usuarios u ON u.id = tap.user_id
			LEFT JOIN tbl_televentas_clientes tc ON tc.id = tap.cliente_id
			LEFT JOIN tbl_televentas_clientes_transaccion tct ON tct.id_abono_pendiente = tap.id AND tct.tipo_id = 26
            LEFT JOIN tbl_usuarios u_2 ON u_2.id = tct.user_id
		WHERE
			tap.estado_abono_id in (1,2)
			AND tap.status = 1
			$where_banco
			$where_estado
			$where_fecha_inicio
			$where_fecha_fin
			$where_busqueda_nro_operacion
		ORDER BY 
			tap.fecha_operacion DESC,
			tap.hora_operacion DESC,
			tap.created_at DESC
	";
	$list_query = $mysqli->query($query_1);
	$list_transaccion = array();
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["consulta_error"] = $mysqli->error;
		$result["status"] = "Ocurrió un error al consultar los abonos.";
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$list_transaccion[] = $li;
		}
	}

	$array_merge = array_merge($list_transaction_pending, $list_transaccion);
	
	if (count($array_merge) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay abonos pendientes.";
		$result["dif"] = $diff;
	} elseif (count($array_merge) > 0) {
		usort($array_merge, 'sortByDate');
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["cantidad"] = count($array_merge);
		$result["result"] = $array_merge;
		// $result["result"] = $list_transaccion;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los abonos pendientes.";
	}

}

function sortByDate($a, $b) {
    $dateA = strtotime($a['fecha_registro']);
    $dateB = strtotime($b['fecha_registro']);
    return $dateB - $dateA;
}


//*******************************************************************************************************************
// GUARDAR ABONO PENDIENTE
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_abono_pendiente") {

	include("function_replace_invalid_caracters.php");

	$cod_abono_pendiente = $_POST["cod_abono_pendiente"];
	$cliente_id_tls_abonos_pendientes = $_POST["cliente_id_tls_abonos_pendientes"];
	$fecha_operacion = $_POST["fecha_operacion"];
	$hora_operacion  = $_POST["hora_operacion"];
	$medio_id        = $_POST["medio_id"];
	$nro_operacion   = $_POST["nro_operacion"];
	$monto           = $_POST["monto"];
	$comision_id	 = $_POST["comision_id"];
	$observacion     = replace_invalid_caracters($_POST["observacion"]);
	$banco_id        = $_POST["banco_id"];
	$is_valid_yape_banco = $_POST["is_valid_yape_banco"];
	$permitir_dupl   = $_POST["permitir_dupl"];
	$result["question_yape"] = 0;

	$date_time = date('Y-m-d H:i:s');
	
	if (!((double) $monto > 0)) {
		$result["http_code"] = 400;
		$result["status"] = "Monto incorrecto.";
		echo json_encode($result);exit();
	}

	if (!((double) $comision_id >= 0)) {
		$result["http_code"] = 400;
		$result["status"] = "Comisión incorrecta.";
		echo json_encode($result);exit();
	}

	if (!((int) $medio_id > 0)) {
		$result["http_code"] = 400;
		$result["status"] = "Medio incorrecto.";
		echo json_encode($result);exit();
	}

	$usuario_id = $login ? $login['id'] : null;
	if (!((int) $usuario_id > 0)) {
		$result["http_code"] = 400;
		$result["status"] = "Sesión perdida. Por favor vuelva a iniciar sesión.";
		echo json_encode($result);exit();
	}

	/* if(!(isset($_FILES['imagen_voucher']['tmp_name']))){
		$result["http_code"] = 400;
		$result["status"] = "No se pudo validar la imágen.";
		echo json_encode($result);exit();
	} */

	$where_valid_tra = "";
	if($is_valid_yape_banco == 1){ //Si es yape
		$where_valid_tra = 
						" AND tra.cliente_id = " . $cliente_id_tls_abonos_pendientes .
						" AND DATE(tra.registro_deposito) = '" . $fecha_operacion . "' ";
	}else{
		$where_valid_tra = 
						" AND DATE(tra.registro_deposito) = '" . $fecha_operacion . "' ";
	}

	//Consultar si ya existe el numero de operacion en clientes_transaccion
	$query_valid = "
				SELECT 
					tra.num_operacion
				FROM tbl_televentas_clientes_transaccion tra
				WHERE tra.tipo_id = 1 
					AND tra.id != '$cod_abono_pendiente' 
					AND tra.estado = 1 
					AND tra.num_operacion = '". $nro_operacion ."' 
					AND tra.cuenta_id = '". $banco_id ."' 
					" . $where_valid_tra;
	$result["consulta_query_valid"] = $query_valid;
	$list_query = $mysqli->query($query_valid);
	$list_valid = array();
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["query_valid"] = $mysqli->error;
		$result["status"] = "Ocurrió un error al consultar la existencia del Número de Operación.";
		echo json_encode($result);exit();
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$list_valid[] = $li;
		}
		if (count($list_valid) > 0 && $permitir_dupl == 0) {
			$result["http_code"] = 400;
			if($is_valid_yape_banco == 1){
				$result["question_yape"] = 1;
				$result["status"] = "Este número de operación ya fue registrado en las transacciones del cliente en el día ¿Desea registrar la operación?";	
			}else{
				$result["status"] = "El Número de Operación para este Banco ya está registrado en transacciones del cliente.";
			}
			echo json_encode($result);exit();
		}
	}

	$where_valid_abon = "";
	if($is_valid_yape_banco == 1){ //Si es yape
		$where_valid_abon = 
						" AND tap.cliente_id = " . $cliente_id_tls_abonos_pendientes .
						" AND tap.fecha_operacion = '" . $fecha_operacion . "' " .
						" AND tap.hora_operacion = '" . $hora_operacion . "' ";
	}else{
		$where_valid_abon = 
						" AND tap.fecha_operacion = '" . $fecha_operacion . "'";
	}

	//Consultar si ya existe el numero de operacion en abonos pendientes
	$query_validar = "
				SELECT 
					tap.nro_operacion
				FROM tbl_televentas_abonos_pendientes tap
				WHERE tap.estado_abono_id IN (1,2)
					AND tap.id != '$cod_abono_pendiente' 
					AND tap.nro_operacion = '". $nro_operacion ."' 
					AND tap.banco_id = '". $banco_id ."' 
			" . $where_valid_abon;
	$result["consulta_query_valid"] = $query_validar;
	$list_query_1 = $mysqli->query($query_validar);
	$list_valid_1 = array();
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["query_valid"] = $mysqli->error;
		$result["status"] = "Ocurrió un error al consultar la existencia del Número de Operación.";
		echo json_encode($result);exit();
	} else {
		while ($li = $list_query_1->fetch_assoc()) {
			$list_valid_1[] = $li;
		}
		if (count($list_valid_1) > 0 && $permitir_dupl == 0) {
			$result["http_code"] = 400;
			if($is_valid_yape_banco == 1){
				$result["question_yape"] = 1;
				$result["status"] = "Este número de operación ya fue registrado al cliente en el día ¿Desea registrar la operación?";	
			}else{
				$result["status"] = "El Número de Operación para este Banco ya está registrado en los abonos pendientes.";
			}
			echo json_encode($result);exit();
		}
	}

	if((int)$cod_abono_pendiente===0){
		$query = " 
			INSERT INTO tbl_televentas_abonos_pendientes (
				cliente_id,
				fecha_operacion,
				hora_operacion,
				banco_id,
				medio_id,
				nro_operacion,
				monto,
				comision_id,
				observacion,
				estado_abono_id,
				status,
				user_id,
				created_at,
				updated_user_id,
				updated_at
			) VALUES (
				'" . $cliente_id_tls_abonos_pendientes . "',
				'" . $fecha_operacion . "',
				'" . $hora_operacion . "',
				'" . $banco_id . "',
				'" . $medio_id . "',
				'" . $nro_operacion . "',
				'" . $monto . "',
				'" . $comision_id . "',
				'" . $observacion . "',
				'1',
				'1',
				'" . $usuario_id . "',
				'" . $date_time . "',
				'" . $usuario_id . "',
				'" . $date_time . "'
			)
		";
	}

	if((int)$cod_abono_pendiente>0){
		$query = " 
			UPDATE
				tbl_televentas_abonos_pendientes 
			SET 
				cliente_id      = '" . $cliente_id_tls_abonos_pendientes . "',
				fecha_operacion = '" . $fecha_operacion . "',
				hora_operacion  = '" . $hora_operacion . "',
				banco_id        = '" . $banco_id . "',
				medio_id        = '" . $medio_id . "',
				nro_operacion   = '" . $nro_operacion . "',
				monto           = '" . $monto . "',
				comision_id     = '" . $comision_id . "',
				observacion     = '" . $observacion . "',
				updated_at      = '" . $date_time . "',
				updated_user_id = '" . $usuario_id . "'
			WHERE
				id = $cod_abono_pendiente
			";
	}

	$mysqli->query($query);
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["insert_query"] = $query;
		$result["insert_error"] = $mysqli->error;
		$result["status"] = "Error al registrar la transacción.";
		echo json_encode($result);exit();
	}

	$query_select = "
		SELECT id 
		FROM tbl_televentas_abonos_pendientes
		WHERE cliente_id        = '" . $cliente_id_tls_abonos_pendientes . "' 
			AND fecha_operacion = '" . $fecha_operacion . "'
			AND hora_operacion  = '" . $hora_operacion . "'
			AND banco_id        = '" . $banco_id . "'
			AND medio_id        = '" . $medio_id . "'
			AND nro_operacion   = '" . $nro_operacion . "'
			AND monto           = '" . $monto . "'
			AND comision_id     = '" . $comision_id . "'
			AND observacion     = '" . $observacion . "'
			AND estado_abono_id = 1
			AND status          = 1
			AND updated_user_id = '" . $usuario_id . "'
			AND updated_at      = '" . $date_time . "'
		ORDER BY id DESC LIMIT 1
	";
	$list_query_select = $mysqli->query($query_select);
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["consulta_error"] = $mysqli->error;
		$result["consulta_query"] = $query_select;
		$result["status"] = "Error al consultar la transacción.";
		echo json_encode($result);exit();
	} else {
		$list_transaccion = array();
		while ($li = $list_query_select->fetch_assoc()) {
			$list_transaccion[] = $li;
		}
		if (count($list_transaccion) === 0) {
			$result["http_code"] = 400;
			$result["status"] = "No se guardó la transacción.";
		} elseif (count($list_transaccion) === 1) {
			$transaccion_id = $list_transaccion[0]["id"];

			if(isset($_FILES['imagen_voucher']['tmp_name'])){
				//**************************************************************************************************
				//**************************************************************************************************
				// IMAGEN
				//**************************************************************************************************
				$path = "/var/www/html/files_bucket/depositos/";
				$file = [];
				$imageLayer = [];
				if (!is_dir($path))
					mkdir($path, 0777, true);
				$imageProcess = 0;

				$filename = $_FILES['imagen_voucher']['tmp_name'];
				$filenametem = $_FILES['imagen_voucher']['name'];
				$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
				if ($filename != "") {
					$fileExt = pathinfo($_FILES['imagen_voucher']['name'], PATHINFO_EXTENSION);
					$resizeFileName = $transaccion_id . "_" . date('YmdHis');
					$nombre_archivo = $resizeFileName . "." . $fileExt;
					if ($fileExt == "pdf") {
						move_uploaded_file($_FILES['imagen_voucher']['tmp_name'], $path . $nombre_archivo);
					} else {
						$sourceProperties = getimagesize($filename);
						$size = $_FILES['imagen_voucher']['size'];
						$uploadImageType = $sourceProperties[2];
						$sourceImageWith = $sourceProperties[0];
						$sourceImageHeight = $sourceProperties[1];
						switch ($uploadImageType) {
							case IMAGETYPE_JPEG:
								$resourceType = imagecreatefromjpeg($filename);
								break;
							case IMAGETYPE_PNG:
								$resourceType = imagecreatefrompng($filename);
								break;
							case IMAGETYPE_GIF:
								$resourceType = imagecreatefromgif($filename);
								break;
							default:
								$imageProcess = 0;
								break;
						}
						$imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);

						$file[0] = imagegif($imageLayer[0], $path . $nombre_archivo);
						$file[1] = imagegif($imageLayer[1], $path . "min_" . $nombre_archivo);
						move_uploaded_file($file[0], $path . $nombre_archivo);
						move_uploaded_file($file[1], $path . $nombre_archivo);
						$imageProcess = 1;
					}

					$comando = " 
						UPDATE
							tbl_televentas_abonos_pendientes 
						SET
							name_file = '" . $nombre_archivo . "'
						WHERE
							id = '" . $transaccion_id . "'
					";
					$mysqli->query($comando);
					$archivo_id = mysqli_insert_id($mysqli);
					$filepath = $path . $resizeFileName . "." . $fileExt;
				}
				//**************************************************************************************************
				//**************************************************************************************************
			}

			$result["http_code"] = 200;
			$result["status"] = "ok";
			$result["result"] = "Resumen de Abono Registrado";
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al guardar la transacción.";
		}
	}

}


//*******************************************************************************************************************
// ELIMINAR DATOS DEL ABONO PENDIENTE
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "eliminar_abono_pendiente") {

	$abono_pendiente_id = $_POST["abono_pendiente_id"];

	$date_time = date('Y-m-d H:i:s');

	$usuario_id = $login ? $login['id'] : null;
	if ((int) $usuario_id > 0) {		
		$insert_command = " 
			UPDATE
				tbl_televentas_abonos_pendientes 
			SET 
				status = 0,
				updated_at = '" . $date_time . "',
				updated_user_id = '" . $usuario_id . "'
			WHERE
				id = $abono_pendiente_id
				AND status = 1
		";
		$mysqli->query($insert_command);
		if ($mysqli->error) {
			$result["insert_query"] = $insert_command;
			$result["insert_error"] = $mysqli->error;
		} else {
			$result["http_code"] = 200;
			$result["status"] = "ok";
			$result["result"] = "Resumen de Abono Eliminado";
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Sesión perdida. Por favor vuelva a iniciar sesión.";
	}
}

//*******************************************************************************************************************
// CANCELAR DATOS DEL YAPE PENDIENTE
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "cancelar_yape_pendiente") {

	$yape_pendiente_id = $_POST["yape_pendiente_id"];

	$date_time = date('Y-m-d H:i:s');

	$usuario_id = $login ? $login['id'] : null;
	if ((int) $usuario_id > 0) {
		$usuario = $login ? $login['usuario'] : '';	

		$con_db_name   = env('DB_DATABASE_YAPE');
		$con_host      = env('DB_HOST_YAPE');
		$con_user      = env('DB_USERNAME_YAPE');
		$con_pass      = env('DB_PASSWORD_YAPE');
		$mysqli2 = new mysqli($con_host,$con_user,$con_pass,$con_db_name,3306);
		if ($mysqli2->connect_error){
			$result["http_code"] = 500;
			$result["status"] = "Conexión fallida: %s\n" . $mysqli2->connect_error;
			throw new Exception("Conexión fallida: %s\n" . $mysqli2->connect_error);
			echo json_encode($result);exit();
		}
		
		$query_update_cancelled = " 
			UPDATE `at-yape`.transactions
			SET
				state = 'cancelled',
				updated_at = '$date_time',
				external_user = '" . $usuario . "', 
				updated_by = 1
			WHERE id =  $yape_pendiente_id
				AND state = 'pending'
		";
		$mysqli2->query($query_update_cancelled);
		if ($mysqli2->error) {
			$result["query"] = $query_update_cancelled;
			$result["error"] = $mysqli2->error;
			echo json_encode($result);exit();
		} else {
			$result["http_code"] = 200;
			$result["status"] = "ok";
			$result["result"] = "Yape Pendiente Cancelado";
		}
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Sesión perdida. Por favor vuelva a iniciar sesión.";
	}
}


//*******************************************************************************************************************
// AUTO-COMPLETAR EL NOMBRE DEL CLIENTE
//*******************************************************************************************************************
if (isset($_GET['action']) && $_GET["action"] === "buscar"){
	$query = "
		SELECT 
			id AS cliente_id,
			IFNULL(nombre, '') AS nombre,
			IFNULL(apellido_paterno, '') AS apellido_paterno,
			IFNULL(apellido_materno, '') AS apellido_materno
		FROM
			tbl_televentas_clientes
		WHERE
			CONCAT(
				UPPER( nombre ),
				' ',
				UPPER( IFNULL(apellido_paterno, '') ),
				' ',
				UPPER( IFNULL(apellido_materno, '') )
			) COLLATE utf8_unicode_ci 
		LIKE '%". strtoupper(trim($_GET["term"])) ."%' 
		ORDER BY 
		nombre ASC
		LIMIT 10
	";
    $resultado = $mysqli->query($query);
    $total_rows = $resultado->num_rows;
    $result = array();
    if ($total_rows > 0) {
        while ($row = $resultado->fetch_assoc()) {
            $temp_array['codigo'] = $row['cliente_id'];
            $temp_array['value'] = strtoupper(utf8_encode($row['nombre'])).' '. strtoupper(utf8_encode($row['apellido_paterno'])).' '.strtoupper(utf8_encode($row['apellido_materno']));
            $temp_array['label'] = $temp_array['value'];
            array_push($result, $temp_array);
        }
    } else {
        $result['value'] = '';
        $result['label'] = 'No se encontraron coincidencias.';
    }
}


//*******************************************************************************************************************
// EXPORTAR DATOS DEL ABONO PENDIENTE
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"]==="listar_abonos_export_xls") {
	global $mysqli;
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$busqueda_fecha_inicio  = $_POST["fecha_inicio"];
	$busqueda_fecha_fin     = $_POST["fecha_fin"];
	$busqueda_banco         = $_POST["banco"];
	$busqueda_estado        = $_POST["estado"];
	$nro_operacion          = $_POST["nro_operacion"];

	$usuario_id = $login ? $login['id'] : null;
	if (!((int) $usuario_id > 0)) {
		$result["http_code"] = 400;
		$result["status"] = "Sesión perdida. Por favor vuelva a iniciar sesión.";
		echo json_encode($result);exit();
	}

	$where_banco = "";
	$yape_where_banco = "";
	if ((int) $busqueda_banco > 0) {
		$where_banco = " AND tap.banco_id = '" . $busqueda_banco . "' ";

		switch ( (int)$busqueda_banco ) {
			case 3:
				$yape_where_banco = " AND t.description = 'Business' ";
				break;
			case 4:
				$yape_where_banco = " AND t.description = 'Mulfood' ";
				break;
			case 14:
				$yape_where_banco = " AND t.description = 'Televentas' ";
				break;
			case 15:
				$yape_where_banco = " AND t.description = 'Teleservicios' ";
				break;
			default;
				break;
		}
	}

	$where_estado = '';
	$yape_where_estado = '';
	if ((int) $busqueda_estado > 0) {
		if ( $busqueda_estado == 3 ) {
			$where_estado = " AND 1 = 0 ";
		} else {
			$where_estado = " AND tap.estado_abono_id = '" . $busqueda_estado . "' ";
		}

		switch ( (int)$busqueda_estado ) {
			case 1:
				$yape_where_estado = " AND t.state = 'pending' ";
				break;
			case 2:
				$yape_where_estado = " AND t.state = 'validated' ";
				break;
			case 3:
				$yape_where_estado = " AND t.state = 'cancelled' ";
				break;
			default;
				break;
		}
	}

	$where_busqueda_nro_operacion = '';
	$yape_where_busqueda_nro_operacion = '';
	if ( strlen($nro_operacion)>0) {
		$where_busqueda_nro_operacion = " AND tap.nro_operacion = '" . $nro_operacion . "' ";

		$yape_where_busqueda_nro_operacion = " AND 1 = 0 ";
	}

	$where_fecha_inicio = " AND tap.fecha_operacion >= '" . $busqueda_fecha_inicio . "' ";
	$where_fecha_fin    = " AND tap.fecha_operacion <= '" . $busqueda_fecha_fin . "' ";

	$yape_where_fecha_inicio = " AND DATE(t.register_date) >= '" . $busqueda_fecha_inicio . "' ";
	$yape_where_fecha_fin    = " AND DATE(t.register_date) <= '" . $busqueda_fecha_fin . "' ";

	/* $con_db_name   = env('DB_DATABASE_YAPE');
	$con_host      = env('DB_HOST_YAPE');
	$con_user      = env('DB_USERNAME_YAPE');
	$con_pass      = env('DB_PASSWORD_YAPE');
	$mysqli2 = new mysqli($con_host,$con_user,$con_pass,$con_db_name,3306);
	if ($mysqli2->connect_error){
		$result["http_code"] = 500;
		$result["status"] = "Conexión fallida: %s\n" . $mysqli2->connect_error;
		throw new Exception("Conexión fallida: %s\n" . $mysqli2->connect_error);
		echo json_encode($result);exit();
	} */

	$cmd_valid_yape_pending = "
		SELECT 
			DATE(t.register_date) fecha_operacion,
			TIME(t.register_date) hora_operacion,
			CONCAT('APK', ' - ', t.description) nombre_banco,
			t.amount monto,
			'0.00' comision_id,
			'Yape - APK' nombre_medio,
			'' nro_operacion,
			CASE
				WHEN t.state = 'pending' THEN 'PENDIENTE'
				WHEN t.state = 'validated' THEN 'VALIDADO'
				WHEN t.state = 'cancelled' THEN 'CANCELADO'
				ELSE ''
			END estado_nombre,
			t.person cliente,
			t.created_at AS fecha_registro,
			'' usuario,
			'' usuario_validador,
			'' fecha_validacion,
			'' observacion
		FROM `at-yape`.transactions t
		WHERE t.state IN ( 'pending', 'validated', 'cancelled' )
			$yape_where_banco
			$yape_where_estado
			$yape_where_fecha_inicio
			$yape_where_fecha_fin
			$yape_where_busqueda_nro_operacion
		ORDER BY id DESC
	";
	$result["cmd_valid_yape_pending"] = $cmd_valid_yape_pending;
	$list_yape_pending = $mysqli2->query($cmd_valid_yape_pending);
	if ($mysqli2->error) {
		// sec_tlv_log($hash . 'Error Consultar en bd yape - solicitud');
		// sec_tlv_log($hash . $mysqli2->error);
		$result["consulta_error"] = $mysqli2->error;
		echo json_encode($result);exit();
	}
	$list_transaction_pending = array();
	while ($li = $list_yape_pending->fetch_assoc()) {
		$list_transaction_pending[] = $li;
		$result["list_transaction_pending"] = $list_transaction_pending;
	}

	$query_1 = "
		SELECT 
			tap.fecha_operacion,
			tap.hora_operacion,
			IFNULL(ca.cuenta_descripcion, '') AS nombre_banco,
			IFNULL(tap.monto, 0) AS monto,
			IFNULL(tap.comision_id, 0) AS comision_id,
			tapm.nombre AS nombre_medio,
			IFNULL(tap.nro_operacion, 0) AS nro_operacion,
			CASE 
				WHEN tap.estado_abono_id = 1 THEN 'PENDIENTE'
				WHEN tap.estado_abono_id = 2 THEN 'VALIDADO'
				ELSE ''
			END AS estado_nombre,
			IF(tap.cliente_id > 0 OR isnull(tap.cliente_id),
				IFNULL(CONCAT( tc.nombre, ' ', IFNULL( tc.apellido_paterno, '' ), ' ', IFNULL( tc.apellido_materno, '' ) ), ''),
				'') AS cliente,
			tap.created_at AS fecha_registro,
			IFNULL(u.usuario, '') AS usuario,
			IFNULL(u_2.usuario, '') AS usuario_validador,
			IFNULL(tct.created_at, '') AS fecha_validacion,
			IFNULL(tap.observacion, '') as observacion
		FROM
			tbl_televentas_abonos_pendientes tap
			INNER JOIN tbl_cuentas_apt ca ON ca.id = tap.banco_id
			INNER JOIN tbl_televentas_abonos_pendientes_medio tapm ON tapm.id = tap.medio_id
			INNER JOIN tbl_usuarios u ON u.id = tap.user_id
			LEFT JOIN tbl_televentas_clientes tc ON tc.id = tap.cliente_id
			LEFT JOIN tbl_televentas_clientes_transaccion tct ON tct.id_abono_pendiente = tap.id AND tct.tipo_id = 26
            LEFT JOIN tbl_usuarios u_2 ON u_2.id = tct.user_id
		WHERE
			tap.estado_abono_id in (1,2)
			AND tap.status = 1
			$where_banco
			$where_estado
			$where_fecha_inicio
			$where_fecha_fin
			$where_busqueda_nro_operacion
		ORDER BY 
			tap.fecha_operacion DESC,
			tap.hora_operacion DESC,
			tap.created_at DESC
	";
	$list_query = $mysqli->query($query_1);
	$result_data = array();
	if ($mysqli->error) {
		//$result["consulta_error"] = $mysqli->error;
		echo json_encode([
			"error" => "Export error"
		]);
		exit;
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$result_data[] = $li;
		}

		$result_merge = array_merge($list_transaction_pending, $result_data);
		usort($result_merge, 'sortByDate');

		$headers = [
			"fecha_operacion" => "Fecha Operación",
			"hora_operacion" => "Hora Operación",
			"nombre_banco" => "Cuenta",
			"monto" => "Monto",
			"comision_id" => "Comisión",
			"nombre_medio" => "Medio",
			"nro_operacion" => "N° operación",
			"estado_nombre" => "Estado",
			"cliente" => "Cliente",
			"fecha_registro" => "Fecha Registro",
			"usuario" => "Abonador",
			"usuario validador" => "usuario_validador",
			"fecha validación" => "fecha_validacion",
			"observacion" => "Observacion"
		];
		array_unshift($result_merge, $headers);

		ini_set('display_errors', 0);
		ini_set('display_startup_errors', 0);
		error_reporting(0);

		require_once '../phpexcel/classes/PHPExcel.php';
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getActiveSheet()->fromArray($result_merge, null, 'A1');
		$date = new DateTime();
		$file_title = "reporte_televentas_abonos_pendientes" . $date->getTimestamp();

		if (!file_exists('/var/www/html/export/files_exported/reporte_premios/')) {
			mkdir('/var/www/html/export/files_exported/reporte_premios/', 0777, true);
		}

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . $file_title . '.xls"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$excel_path = '/var/www/html/export/files_exported/reporte_premios/' . $file_title . '.xls';
		$excel_path_download = '/export/files_exported/reporte_premios/' . $file_title . '.xls';
		$url = $file_title . '.xls';

		try {
			$objWriter->save($excel_path);
		} catch (PHPExcel_Writer_Exception $e) {
			echo json_encode(["error" => $e]);
			exit;
		}

		$insert_cmd = "INSERT INTO tbl_exported_files (url,tipo,ext,size,fecha_registro,usuario_id)";
		$insert_cmd .= " VALUES ('" . $url . "','excel','xls','" . filesize($excel_path) . "','" . date("Y-m-d h:i:s") . "','" . $login["id"] . "')";
		$mysqli->query($insert_cmd);

		echo json_encode(array(
			"path" => $excel_path_download,
			"url" => $file_title . '.xls',
			"tipo" => "excel",
			"ext" => "xls",
			"size" => filesize($excel_path),
			"fecha_registro" => date("d-m-Y h:i:s"),
			"sql" => $insert_cmd
		));
		exit;
	}
}




echo json_encode($result);
?>