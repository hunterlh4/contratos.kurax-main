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


//*******************************************************************************************************************
//*******************************************************************************************************************
// LISTAR ABONOS RESUMEN
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "listar_abonos_resumen") {

	$busqueda_fecha_inicio  = $_POST["fecha_inicio"];
	$busqueda_fecha_fin     = $_POST["fecha_fin"];
	$busqueda_abonador      = $_POST["abonador"];
	$busqueda_cuenta_origen = $_POST["cuenta_origen"];

	$usuario_id = $login ? $login['id'] : null;
	if (!((int) $usuario_id > 0)) {
		$result["http_code"] = 400;
		$result["status"] = "Sesión perdida. Por favor vuelva a iniciar sesión.";
		echo json_encode($result);exit();
	}

	$where_cuenta_origen = "";
	if ((int) $busqueda_cuenta_origen > 0) {
		$where_cuenta_origen = " AND tar.cuentas_pago_id = '" . $busqueda_cuenta_origen . "' ";
	}

	$where_abonador = '';
	if ((int) $busqueda_abonador > 0) {
		$where_abonador = ' AND IFNULL(tar.user_id, 0) = ' . $busqueda_abonador . ' ';
	}

	$where_fecha_inicio = " AND tar.fecha_operacion >= '" . $busqueda_fecha_inicio . "' ";
	$where_fecha_fin    = " AND tar.fecha_operacion <= '" . $busqueda_fecha_fin . "' ";

	$query_1 = "
		SELECT 
			tar.id AS cod_transaccion,
			tar.fecha_operacion,
			tar.hora_operacion,
			tar.nro_corte,
			IFNULL(tar.name_file, '') AS nombre_imagen,
			tar.created_at AS fecha_registro,
			tar.cuentas_pago_id,
			IFNULL(ca.cuenta_descripcion, '') AS nombre_banco,
			IFNULL((
				SELECT tar2.importe 
				FROM tbl_televentas_abonos_resumen tar2 
				WHERE tar2.fecha_operacion = DATE_SUB(tar.fecha_operacion, INTERVAL 1 DAY)
				AND tar2.cuentas_pago_id = tar.cuentas_pago_id
				AND tar2.status = 1 
				ORDER BY tar2.fecha_operacion DESC 
				LIMIT 1 
			), 0) monto_apertura,
			IFNULL(tar.importe, 0) AS importe,
			IFNULL(tar.fondo_pagos, 0) AS fondo_para_pagos,
			TRUNCATE((tar.importe - tar.fondo_pagos ), 2) as monto_para_abonar,
			IFNULL(tar.observacion, '') as observacion,
			IFNULL(u.usuario, '') AS usuario,
			(
				SELECT IFNULL(SUM(importe), 0) 
				FROM tbl_televentas_abonos_transaccion tat 
				WHERE tat.fecha_operacion = tar.fecha_operacion 
				AND tat.cuentas_pago_id_origen = tar.cuentas_pago_id
				AND tat.nro_corte_id = tar.nro_corte
				AND tat.status = 1
			) monto_abonado
		FROM
			tbl_televentas_abonos_resumen tar
			INNER JOIN tbl_cuentas_apt ca ON ca.id = tar.cuentas_pago_id
			INNER JOIN tbl_usuarios u ON u.id = tar.user_id
		WHERE
			tar.status = 1
			$where_cuenta_origen
			$where_abonador
			$where_fecha_inicio
			$where_fecha_fin
		ORDER BY 
			tar.fecha_operacion DESC,
			tar.nro_corte ASC
	";
	$list_query = $mysqli->query($query_1);
	$list_transaccion = array();
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["consulta_error"] = $mysqli->error;
		$result["status"] = "Ocurrió un error al consultar transacciones.";
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$list_transaccion[] = $li;
		}
		if (count($list_transaccion) == 0) {
			$result["http_code"] = 400;
			$result["status"] = "No hay transacciones.";
		} elseif (count($list_transaccion) > 0) {
			$result["http_code"] = 200;
			$result["status"] = "ok";
			$result["result"] = $list_transaccion;
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al consultar transacciones.";
		}
	}

}


//*******************************************************************************************************************
//*******************************************************************************************************************
// LISTAR ABONOS DETALLE
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "listar_abonos_detalle") {
	$busqueda_fecha_inicio  = $_POST["fecha_inicio"];
	$busqueda_fecha_fin     = $_POST["fecha_fin"];
	$busqueda_abonador      = $_POST["abonador"];
	$busqueda_cuenta_origen = $_POST["cuenta_origen"];
	$busqueda_cuenta_destino = $_POST["cuenta_destino"];

	$usuario_id = $login ? $login['id'] : null;
	if (!((int) $usuario_id > 0)) {
		$result["http_code"] = 400;
		$result["status"] = "Sesión perdida. Por favor vuelva a iniciar sesión.";
		echo json_encode($result);exit();
	}

	$where_cuenta_origen = "";
	if ((int) $busqueda_cuenta_origen > 0) {
		$where_cuenta_origen = " AND tat.cuentas_pago_id_origen = '" . $busqueda_cuenta_origen . "' ";
	}
	$where_cuenta_destino = "";
	if ((int) $busqueda_cuenta_destino > 0) {
		$where_cuenta_destino = " AND tat.cuentas_pago_id_destino = '" . $busqueda_cuenta_destino . "' ";
	}

	$where_abonador = '';
	if ((int) $busqueda_abonador > 0) {
		$where_abonador = ' AND IFNULL(tat.user_id, 0) = ' . $busqueda_abonador . ' ';
	}

	$where_fecha_inicio = " AND tat.fecha_operacion >= '" . $busqueda_fecha_inicio . "' ";
	$where_fecha_fin    = " AND tat.fecha_operacion <= '" . $busqueda_fecha_fin . "' ";

	$query_1 = "
		SELECT 
			tat.id AS cod_transaccion,
			tat.fecha_operacion AS fecha_operacion,
			tat.nro_corte_id AS nro_corte_id,
			tat.created_at AS fecha_registro,
			tat.cuentas_pago_id_origen,
			tat.cuentas_pago_id_destino,
			IFNULL(tat.name_file, '') AS nombre_imagen,
			IFNULL(ca.cuenta_descripcion, '') AS cuenta_origen,
			IFNULL(ca2.cuenta_descripcion, '') AS cuenta_destino,
			IFNULL(tat.nro_operacion, 0) AS nro_operacion,
			IFNULL(tat.importe, 0) AS importe,
			IFNULL(tat.comision_id, 0) AS comision_id,
			IFNULL(u.usuario, '') AS usuario,
			IFNULL(tat.observacion, '') as observacion
		FROM
			tbl_televentas_abonos_transaccion tat
			INNER JOIN tbl_cuentas_apt ca ON ca.id = tat.cuentas_pago_id_origen
			INNER JOIN tbl_cuentas_apt ca2 ON ca2.id = tat.cuentas_pago_id_destino
			INNER JOIN tbl_usuarios u ON u.id = tat.user_id
		WHERE
			tat.status = 1
			$where_cuenta_origen
			$where_cuenta_destino
			$where_abonador
			$where_fecha_inicio
			$where_fecha_fin
		ORDER BY 
			fecha_operacion DESC,
			nro_corte_id ASC
	";
	$list_query = $mysqli->query($query_1);
	$list_transaccion = array();
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["consulta_error"] = $mysqli->error;
		$result["status"] = "Ocurrió un error al consultar transacciones.";
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$list_transaccion[] = $li;
		}
		if (count($list_transaccion) == 0) {
			$result["http_code"] = 400;
			$result["status"] = "No hay transacciones.";
		} elseif (count($list_transaccion) > 0) {
			$result["http_code"] = 200;
			$result["status"] = "ok";
			$result["result"] = $list_transaccion;
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al consultar transacciones nuevas.";
		}
	}

}


//*******************************************************************************************************************
// GUARDAR ABONO RESUMEN
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_abono_resumen") {

	include("function_replace_invalid_caracters.php");

	$cod_transaccion = $_POST["cod_transaccion"];
	$fecha_operacion = $_POST["fecha_operacion"];
	$hora_operacion  = $_POST["hora_operacion"];
	$nro_corte       = $_POST["nro_corte"];
	$importe         = $_POST["importe"];
	$fondo_pago      = $_POST["fondo_pago"];
	$observacion     = replace_invalid_caracters($_POST["observacion"]);
	$cuenta_id       = $_POST["cuenta_id"];

	$date_time = date('Y-m-d H:i:s');
	
	if (!((double) $importe > 0)) {
		$result["http_code"] = 400;
		$result["status"] = "Importe incorrecto.";
		echo json_encode($result);exit();
	}

	if (!((double) $fondo_pago > 0)) {
		$result["http_code"] = 400;
		$result["status"] = "Importe incorrecto.";
		echo json_encode($result);exit();
	}

	if ( ((double) $fondo_pago != 100000 && (int) $cuenta_id !== 22 ) ) {
		$result["http_code"] = 400;
		$result["status"] = "El importe debe ser 100,000.00 No: ".$fondo_pago;
		echo json_encode($result);exit();
	}

	if ( ((double) $fondo_pago != 100000 && (int) $cuenta_id == 22  && $fecha_operacion < '2022-12-12') ){
		$result["http_code"] = 400;
		$result["status"] = 'El importe antes del "12-12-2022" para Yape Temporal debe ser 100,000.00 No: '.$fondo_pago;
		echo json_encode($result);exit();
	}

	if ( ((int) $cuenta_id == 22  && $fecha_operacion >= '2022-12-12') ){
		if ( (double) $fondo_pago > (double) $importe ) {
			$result["http_code"] = 400;
			$result["status"] = 'El fondo para pagos NO debe ser mayor al importe, para Yape Temporal desde el "12-12-2022".';
			echo json_encode($result);exit();
		}
	}

	if (!((int) $nro_corte > 0)) {
		$result["http_code"] = 400;
		$result["status"] = "Número de corte incorrecto.";
		echo json_encode($result);exit();
	}

	$usuario_id = $login ? $login['id'] : null;
	if (!((int) $usuario_id > 0)) {
		$result["http_code"] = 400;
		$result["status"] = "Sesión perdida. Por favor vuelva a iniciar sesión.";
		echo json_encode($result);exit();
	}

	$query_select_valid = " 
			SELECT count(id) cant
			FROM tbl_televentas_abonos_resumen 
			WHERE fecha_operacion = '$fecha_operacion' 
			AND nro_corte = '$nro_corte' 
			AND id != '$cod_transaccion' 
			AND cuentas_pago_id = '$cuenta_id' 
			AND status = 1
			";
	$list_query_select_valid = $mysqli->query($query_select_valid);
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["consulta_error"] = $mysqli->error;
		$result["consulta_query"] = $query_select_valid;
		$result["status"] = "Ocurrió un error al validar la fecha de operación y la cuenta de banco.";
		echo json_encode($result);exit();
	} else {
		$list_transaccion_val = array();
		while ($li = $list_query_select_valid->fetch_assoc()) {
			$list_transaccion_val[] = $li;
		}
		if (count($list_transaccion_val) === 1) {
			$cant_valid = $list_transaccion_val[0]["cant"];
			if((int)$cant_valid>0){
				$result["http_code"] = 400;
				$result["status"] = "Ya existe un registro a la cuenta de banco con la misma fecha de operación y el mismo número de corte.";
				echo json_encode($result);exit();
			}
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al validar la fecha de operación y la cuenta de banco.2";
			echo json_encode($result);exit();
		}
	}

	if((int)$cod_transaccion===0){
		if(!(isset($_FILES['imagen_voucher']['tmp_name']))){
			$result["http_code"] = 400;
			$result["status"] = "No se pudo validar la imágen.";
			echo json_encode($result);exit();
		}
		$query = " 
			INSERT INTO tbl_televentas_abonos_resumen (
				fecha_operacion,
				hora_operacion,
				nro_corte,
				cuentas_pago_id,
				importe,
				fondo_pagos,
				observacion,
				status,
				user_id,
				created_at,
				updated_user_id,
				updated_at
			) VALUES (
				'" . $fecha_operacion . "',
				'" . $hora_operacion . "',
				'" . $nro_corte . "',
				'" . $cuenta_id . "',
				'" . $importe . "',
				'" . $fondo_pago . "',
				'" . $observacion . "',
				'1',
				'" . $usuario_id . "',
				'" . $date_time . "',
				'" . $usuario_id . "',
				'" . $date_time . "'
			)
			";
	}
	if((int)$cod_transaccion>0){
		$query = " 
			UPDATE
				tbl_televentas_abonos_resumen 
			SET 
				fecha_operacion = '" . $fecha_operacion . "',
				hora_operacion  = '" . $hora_operacion . "',
				nro_corte       = '" . $nro_corte . "',
				cuentas_pago_id = '" . $cuenta_id . "',
				importe         = '" . $importe . "',
				fondo_pagos     = '" . $fondo_pago . "',
				observacion     = '" . $observacion . "',
				updated_at      = '" . $date_time . "',
				updated_user_id = '" . $usuario_id . "'
			WHERE
				id = $cod_transaccion
			";
	}

	$mysqli->query($query);
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["insert_query"] = $query;
		$result["insert_error"] = $mysqli->error;
		$result["status"] = "Erro al registrar la transacción.";
		echo json_encode($result);exit();
	}

	$query_select = " SELECT id FROM tbl_televentas_abonos_resumen ";
	$query_select .= " WHERE fecha_operacion = '" . $fecha_operacion . "' ";
	$query_select .= " AND hora_operacion = '" . $hora_operacion . "' ";
	$query_select .= " AND nro_corte = '" . $nro_corte . "' ";
	$query_select .= " AND cuentas_pago_id = '" . $cuenta_id . "' ";
	$query_select .= " AND importe = '" . $importe . "' ";
	$query_select .= " AND status = 1 ";
	$query_select .= " AND updated_user_id = '" . $usuario_id . "' ";
	$query_select .= " AND updated_at = '" . $date_time . "' ";
	$query_select .= " ORDER BY id DESC LIMIT 1 ";
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
							tbl_televentas_abonos_resumen 
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


//***************************************
// GUARDAR ABONO DETALLE
//***************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_abono_detalle") {

	include("function_replace_invalid_caracters.php");

	$cod_transaccion = $_POST["cod_transaccion"];
	$fecha_operacion = $_POST["fecha_operacion"];
	$nro_corte       = $_POST["nro_corte"];
	$cuenta_origen   = $_POST["cuenta_origen"];
	$cuenta_destino  = $_POST["cuenta_destino"];
	$num_operacion   = $_POST["num_operacion"];
	$importe         = $_POST["importe"];
	$comision_id     = $_POST["comision_id"];
	$observacion     = replace_invalid_caracters($_POST["observacion"]);

	$date_time = date('Y-m-d H:i:s');
	
	if (!((double) $importe > 0)) {
		$result["http_code"] = 400;
		$result["status"] = "Importe incorrecto.";
		echo json_encode($result);exit();
	}

	if (!((int) $nro_corte > 0)) {
		$result["http_code"] = 400;
		$result["status"] = "Número de corte incorrecto.";
		echo json_encode($result);exit();
	}

	$usuario_id = $login ? $login['id'] : null;
	if (!((int) $usuario_id > 0)) {
		$result["http_code"] = 400;
		$result["status"] = "Sesión perdida. Por favor vuelva a iniciar sesión.";
		echo json_encode($result);exit();
	}


	$query_registro = "
		SELECT 
			tar.id,
			tar.fecha_operacion,
			tar.cuentas_pago_id,
			(IFNULL(tar.importe, 0) - IFNULL(tar.fondo_pagos, 0)) monto_abonado_maximo,
			IFNULL((
				SELECT 
					SUM(importe) importe
				FROM
					tbl_televentas_abonos_transaccion tat
				WHERE
					status = 1 
					AND id != '".$cod_transaccion."' 
					AND fecha_operacion = tar.fecha_operacion 
					AND nro_corte_id = tar.nro_corte 
					AND cuentas_pago_id_origen = tar.cuentas_pago_id 
			), 0) monto_abonado_actual
		FROM
			tbl_televentas_abonos_resumen tar
		WHERE
			tar.status = 1
			AND tar.fecha_operacion = '".$fecha_operacion."'
			AND tar.nro_corte = '".$nro_corte."'
			AND tar.cuentas_pago_id = '".$cuenta_origen."'
		ORDER BY
			tar.id DESC
		LIMIT 1
	";
	$query_sel = $mysqli->query($query_registro);
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["consulta_error"] = $mysqli->error;
		$result["consulta_query"] = $query_registro;
		$result["status"] = "Error al consultar el abono resumen.";
		echo json_encode($result);exit();
	} else {
		$registro_abono_resumen = array();
		while ($li = $query_sel->fetch_assoc()) {
			$registro_abono_resumen[] = $li;
		}
		if (count($registro_abono_resumen) === 0) {
			$result["http_code"] = 400;
			$result["consulta_query"] = $query_registro;
			$result["status"] = "No existe un resumen registrado con la fecha de operacion '".$fecha_operacion."', corte '".$nro_corte."' y cuenta de origen seleccionada";
			echo json_encode($result);exit();
		} elseif (count($registro_abono_resumen) === 1) {
			if ( !(((double)$importe + (double)$registro_abono_resumen[0]["monto_abonado_actual"]) <= (double)$registro_abono_resumen[0]["monto_abonado_maximo"]) ) {
				$result["http_code"] = 400;
				$result["RES_registro_abono_resumen"] = $registro_abono_resumen;
				$result["importe"] = (double)$importe;
				$result["monto_abonado_actual"] = (double)$registro_abono_resumen[0]["monto_abonado_actual"];
				$result["monto_abonado_maximo"] = (double)$registro_abono_resumen[0]["monto_abonado_maximo"];
				$result["status"] = "El importe que se generará para abono de depósito, excederá al abono de venta permitido";
				echo json_encode($result);exit();
			}
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al consultar el abono de resumen.";
			echo json_encode($result);exit();
		}
	}
	

	if((int)$cod_transaccion===0){

		if(!(isset($_FILES['imagen_voucher']['tmp_name']))){
			$result["http_code"] = 400;
			$result["status"] = "No se pudo validar la imágen.";
			echo json_encode($result);exit();
		}
		$query = " 
			INSERT INTO tbl_televentas_abonos_transaccion (
				fecha_operacion,
				nro_corte_id,
				cuentas_pago_id_origen,
				cuentas_pago_id_destino,
				nro_operacion,
				importe,
				comision_id,
				observacion,
				status,
				user_id,
				created_at,
				updated_user_id,
				updated_at
			) VALUES (
				'" . $fecha_operacion . "',
				'" . $nro_corte . "',
				'" . $cuenta_origen . "',
				'" . $cuenta_destino . "',
				'" . $num_operacion . "',
				'" . $importe . "',
				'" . $comision_id . "',
				'" . $observacion . "',
				'1',
				'" . $usuario_id . "',
				'" . $date_time . "',
				'" . $usuario_id . "',
				'" . $date_time . "'
			)
			";
	}
	if((int)$cod_transaccion>0){

		$query = " 
			UPDATE
				tbl_televentas_abonos_transaccion 
			SET 
				fecha_operacion         = '" . $fecha_operacion . "',
				nro_corte_id            = '" . $nro_corte . "',
				cuentas_pago_id_origen  = '" . $cuenta_origen . "',
				cuentas_pago_id_destino = '" . $cuenta_destino . "',
				nro_operacion           = '" . $num_operacion . "',
				importe                 = '" . $importe . "',
				comision_id             = '" . $comision_id . "',
				observacion             = '" . $observacion . "',
				updated_at              = '" . $date_time . "',
				updated_user_id         = '" . $usuario_id . "'
			WHERE
				id = $cod_transaccion
			";
	}

	$mysqli->query($query);
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["insert_query"] = $query;
		$result["insert_error"] = $mysqli->error;
		$result["status"] = "Erro al registrar la transacción.";
		echo json_encode($result);exit();
	}

	$query_select = " SELECT id FROM tbl_televentas_abonos_transaccion ";
	$query_select .= " WHERE cuentas_pago_id_origen = '" . $cuenta_origen . "' ";
	$query_select .= " AND fecha_operacion = '" . $fecha_operacion . "' ";
	$query_select .= " AND nro_corte_id = '" . $nro_corte . "' ";
	$query_select .= " AND cuentas_pago_id_destino = '" . $cuenta_destino . "' ";
	$query_select .= " AND nro_operacion = '" . $num_operacion . "' ";
	$query_select .= " AND importe = '" . $importe . "' ";
	$query_select .= " AND comision_id = '" . $comision_id . "' ";
	$query_select .= " AND observacion = '" . $observacion . "' ";
	$query_select .= " AND status = 1 ";
	$query_select .= " AND updated_user_id = '" . $usuario_id . "' ";
	$query_select .= " AND updated_at = '" . $date_time . "' ";
	$query_select .= " ORDER BY id DESC LIMIT 1 ";
	$list_query_select = $mysqli->query($query_select);
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["consulta_error"] = $mysqli->error;
		$result["consulta_query"] = $query_select;
		$result["status"] = "Erro al consultar la transacción.";
		echo json_encode($result);exit();
	} else {
		$list_transaccion = array();
		while ($li = $list_query_select->fetch_assoc()) {
			$list_transaccion[] = $li;
		}
		if (count($list_transaccion) === 0) {
			$result["http_code"] = 400;
			$result["consulta_query"] = $query_select;
			$result["status"] = "No se guardó la transacción.";
		} elseif (count($list_transaccion) === 1) {
			$transaccion_id = $list_transaccion[0]["id"];

			if(isset($_FILES['imagen_voucher']['tmp_name'])){
				//**********************************
				//**********************************
				// IMAGEN
				//**********************************
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
							tbl_televentas_abonos_transaccion 
						SET
							name_file = '" . $nombre_archivo . "'
						WHERE
							id = '" . $transaccion_id . "'
					";
					$mysqli->query($comando);
					$archivo_id = mysqli_insert_id($mysqli);
					$filepath = $path . $resizeFileName . "." . $fileExt;
				}
				//**********************************
				//**********************************
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
// ELIMINAR DATOS DEL ABONO RESUMEN
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "eliminar_abono_resumen") {

	$abono_id = $_POST["abono_id"];

	$date_time = date('Y-m-d H:i:s');

	$usuario_id = $login ? $login['id'] : null;
	if ((int) $usuario_id > 0) {		
		$insert_command = " 
			UPDATE
				tbl_televentas_abonos_resumen 
			SET 
				status = 0,
				updated_at = '" . $date_time . "',
				updated_user_id = '" . $usuario_id . "'
			WHERE
				id = $abono_id
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
// ELIMINAR DATOS DEL ABONO DETALLE
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "eliminar_abono_detalle") {

	$abono_id = $_POST["abono_id"];

	$date_time = date('Y-m-d H:i:s');

	$usuario_id = $login ? $login['id'] : null;
	if ((int) $usuario_id > 0) {
		$insert_command = " 
			UPDATE
				tbl_televentas_abonos_transaccion 
			SET 
				status = 0,
				updated_at = '" . $date_time . "',
				updated_user_id = '" . $usuario_id . "'
			WHERE
				id = $abono_id
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
// EXPORTAR DATOS DEL ABONO RESUMEN
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"]==="listar_transacciones_abonos_resumen_export_xls") {
	global $mysqli;
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$busqueda_fecha_inicio = $_POST["fecha_inicio"];
	$busqueda_fecha_fin    = $_POST["fecha_fin"];
	$busqueda_abonador       = $_POST["abonador"];
	$busqueda_cuenta_origen    = $_POST["cuenta_origen"];

	if (isset($_POST["cuenta"]) && $_POST["cuenta"] !== '') {
		$busqueda_cuenta = implode(",",$_POST["cuenta"]) ;
	}

	$where_cuenta_origen = "";
	if ((int) $busqueda_cuenta_origen > 0) {
		$where_cuenta_origen = " AND tar.cuentas_pago_id = '" . $busqueda_cuenta_origen . "' ";
	}

	$where_abonador = '';
	if ((int) $busqueda_abonador > 0) {
		$where_abonador = ' AND IFNULL(tar.user_id, 0) = ' . $busqueda_abonador . ' ';
	}

	$where_fecha_inicio = " AND tar.fecha_operacion >= '" . $busqueda_fecha_inicio . "' ";
	$where_fecha_fin    = " AND tar.fecha_operacion <= '" . $busqueda_fecha_fin . "' ";

	// Lista
	$query_1 ="
		SELECT 
			tar.fecha_operacion,
			tar.hora_operacion,
			tar.nro_corte,
			tar.created_at AS fecha_registro,
			IFNULL(ca.cuenta_descripcion, '') AS nombre_banco,
			IFNULL(ca.cuenta_num, '') AS cuenta_num,
			CONCAT('S/ ', FORMAT (IFNULL(tar.importe, 0), 2)) AS importe,
			CONCAT('S/ ', FORMAT (IFNULL(tar.importe, 0), 2)) AS importe,
			CONCAT('S/ ', FORMAT (IFNULL(tar.fondo_pagos, 0), 2)) AS fondo_para_pagos,
			CONCAT('S/ ', FORMAT (TRUNCATE((tar.importe - tar.fondo_pagos ), 2), 2)) as monto_para_abonar,
			CONCAT('S/ ', FORMAT ( (
				SELECT IFNULL(SUM(importe), 0) 
				FROM tbl_televentas_abonos_transaccion tat 
				WHERE tat.fecha_operacion = tar.fecha_operacion 
				AND tat.cuentas_pago_id_origen = tar.cuentas_pago_id
				AND tat.nro_corte_id = tar.nro_corte
				AND tat.status = 1
			), 2)) monto_abonado,
			IFNULL(u.usuario, '') AS usuario,
			IFNULL(tar.observacion, '') as observacion
		FROM
			tbl_televentas_abonos_resumen tar
			INNER JOIN tbl_cuentas_apt ca ON ca.id = tar.cuentas_pago_id
			INNER JOIN tbl_usuarios u ON u.id = tar.user_id
		WHERE
			tar.status = 1
			$where_cuenta_origen
			$where_abonador
			$where_fecha_inicio
			$where_fecha_fin
		ORDER BY 
			tar.fecha_operacion DESC,
			tar.nro_corte ASC
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

		$headers = [
			"fecha_operacion" => "Fecha Operación",
			"hora_operacion" => "Hora Operación",
			"nro_corte" => "N° de Corte",
			"fecha_registro" => "Fecha Registro",
			"nombre_banco" => "Cuenta",
			"cuenta_num" => "Nro. Cuenta",
			"importe" => "Monto de Apertura",
			"importe" => "Monto de Cierre",
			"fondo_para_pagos" => "Fondo para Pagos",
			"monto_para_abonar" => "Abono de ventas",
			"monto_abonado" => "Monto Abonado",
			"usuario" => "Abonador",
			"observacion" => "Observacion"
		];
		array_unshift($result_data, $headers);

		ini_set('display_errors', 0);
		ini_set('display_startup_errors', 0);
		error_reporting(0);

		require_once '../phpexcel/classes/PHPExcel.php';
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getActiveSheet()->fromArray($result_data, null, 'A1');
		$date = new DateTime();
		$file_title = "reporte_televentas_abonos_" . $date->getTimestamp();

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

//*******************************************************************************************************************
// EXPORTAR DATOS DEL ABONO DETALLE
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"]==="listar_transacciones_abonos_detalle_export_xls") {
	
	global $mysqli;
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$busqueda_fecha_inicio  = $_POST["fecha_inicio"];
	$busqueda_fecha_fin     = $_POST["fecha_fin"];
	$busqueda_abonador      = $_POST["abonador"];
	$busqueda_cuenta_origen = $_POST["cuenta_origen"];
	$busqueda_cuenta_destino = $_POST["cuenta_destino"];

	$where_cuenta_origen = "";
	if ((int) $busqueda_cuenta_origen > 0) {
		$where_cuenta_origen = " AND tat.cuentas_pago_id_origen = '" . $busqueda_cuenta_origen . "' ";
	}
	$where_cuenta_destino = "";
	if ((int) $busqueda_cuenta_destino > 0) {
		$where_cuenta_destino = " AND tat.cuentas_pago_id_destino = '" . $busqueda_cuenta_destino . "' ";
	}

	$where_abonador = '';
	if ((int) $busqueda_abonador > 0) {
		$where_abonador = ' AND IFNULL(tat.user_id, 0) = ' . $busqueda_abonador . ' ';
	}

	$where_fecha_inicio = " AND tat.fecha_operacion >= '" . $busqueda_fecha_inicio . "' ";
	$where_fecha_fin    = " AND tat.fecha_operacion <= '" . $busqueda_fecha_fin . "' ";

	// Lista
	$query_1 ="
		SELECT 
			tat.fecha_operacion AS fecha_operacion,
			tat.nro_corte_id AS nro_corte_id,
			tat.created_at AS fecha_registro,
			IFNULL(ca.cuenta_descripcion, '') AS cuenta_origen,
			IFNULL(ca.cuenta_num, '') AS cuenta_num_origen,
			IFNULL(ca2.cuenta_descripcion, '') AS cuenta_destino,
			IFNULL(ca2.cuenta_num, '') AS cuenta_num_destino,
			IFNULL(tat.nro_operacion, 0) AS nro_operacion,
			CONCAT('S/ ', FORMAT (IFNULL(tat.importe, 0), 2)) AS importe,
			CONCAT('S/ ', FORMAT (IFNULL(tat.comision_id, 0), 2)) AS comision_id,
			IFNULL(u.usuario, '') AS usuario,
			IFNULL(tat.observacion, '') as observacion
		FROM
			tbl_televentas_abonos_transaccion tat
			INNER JOIN tbl_cuentas_apt ca ON ca.id = tat.cuentas_pago_id_origen
			INNER JOIN tbl_cuentas_apt ca2 ON ca2.id = tat.cuentas_pago_id_destino
			INNER JOIN tbl_usuarios u ON u.id = tat.user_id
		WHERE
			tat.status = 1
			$where_cuenta_origen
			$where_cuenta_destino
			$where_abonador
			$where_fecha_inicio
			$where_fecha_fin
		ORDER BY 
			fecha_operacion DESC,
			nro_corte_id ASC
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

		$headers = [
			"fecha_operacion" => "Fecha Operacion",
			"nro_corte_id" => "N° Corte",
			"fecha_registro" => "Fecha Registro",
			"cuenta_origen" => "Cuenta Origen",
			"cuenta_num_origen" => "Nro. Cuenta Origen",
			"cuenta_destino" => "Cuenta Destino",
			"cuenta_num_destino" => "Nro. Cuenta Destino",
			"nro_operacion" => "Nro. Operacion",
			"importe" => "Importe",
			"comision_id" => "Comisión",
			"usuario" => "Abonador",
			"observacion" => "Observacion"
		];
		array_unshift($result_data, $headers);

		ini_set('display_errors', 0);
		ini_set('display_startup_errors', 0);
		error_reporting(0);

		require_once '../phpexcel/classes/PHPExcel.php';
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getActiveSheet()->fromArray($result_data, null, 'A1');
		$date = new DateTime();
		$file_title = "reporte_televentas_abonos_" . $date->getTimestamp();

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