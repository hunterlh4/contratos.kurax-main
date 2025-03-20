<?php
include("db_connect.php");
include("sys_login.php");
//require("/var/www/html/cron/cron_pdo_connect.php");

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_locales_get_listado_promociones") {
	try {
		$fechaFiltro = $_POST['fechaPromocion'];
		$fechaCompleta = $fechaFiltro . "-01"; 

		$year = date("Y", strtotime($fechaCompleta));
		$month = date("m", strtotime($fechaCompleta));
		$error = '';
		$querySelect = "
		SELECT
			pm.id,
			pm.fecha,
			pm.nombre_promocion,
			pm.created_at,
			pm.updated_at
		FROM
			tbl_promocion_marketing AS pm
		WHERE 
			MONTH(pm.fecha) = '$month'
			AND YEAR(pm.fecha) = '$year'
			AND pm.estado=1
		";
		$resultQuery = $mysqli->query($querySelect);
		$arrayReturn = array();
		while ($li = $resultQuery->fetch_assoc()) {
			$temp = new stdClass();
			$temp->id = $li['id'];
			$temp->fechaPromocion = $li['fecha'];
			$temp->nombrePromocion = $li['nombre_promocion'];
			$temp->createdAt = $li['created_at'];
			$temp->updatedAt = $li['updated_at'];
			array_push($arrayReturn, $temp);
			unset($temp);
		}

		if ($mysqli->error) {
			$error .= 'Error: ' . $mysqli->error . $query;
		}
		if ($error == '') {
			$result["http_code"] = 200;
			$result["status"] = "Datos Listados.";
			$result["error"] = false;
			$result["data"] = $arrayReturn;
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrio un error";
			$result["error"] = true;
		}
	} catch (Exception $e) {
		http_response_code(500);
		$result["mensaje"]	= $e->getMessage();
		$result["error"]	= true;
	}
}
if (isset($_POST["accion"]) && $_POST["accion"] === "sec_locales_guardar_promocion") {
	try {
		$queryComprobar = "
		SELECT id FROM tbl_promocion_marketing_local AS pml
		WHERE pml.id_local = {$_POST['localId']} 
		AND pml.id_promocion_marketing = {$_POST['idPromocion']}
		AND pml.estado = 1
		";
		$error = "";
		$resultQuery = $mysqli->query($queryComprobar);
		$dataComprobar = array();
		while ($li = $resultQuery->fetch_assoc()) {
			$dataComprobar[] = $li;
		}
		if (count($dataComprobar) != 0) {
			$error = 'Promoción ya agregada';
		} else {
			$id_local = $_POST['localId'];
			$user_id = $_POST['user_id'];
			$idPromocionMarketing = $_POST['idPromocion'];
			$fechaPromocionCreacion = $_POST['fechaPromocionCreacion'];
			$valid_extensions = array('jpeg', 'jpg', 'png');
			$mysqli->query("START TRANSACTION");
			$queryInsertPromocion = "		
			INSERT INTO tbl_promocion_marketing_local
			(	
				id_promocion_marketing,
				id_local,
				usuario_id,
				fecha,
				estado,
				created_at
			)
			VALUES
			(
				{$idPromocionMarketing},
				{$id_local},
				{$user_id},
				now(),
				1,
				now()
			)
			";
			$mysqli->query($queryInsertPromocion);
			$errror = $mysqli->error;
			if ($mysqli->connect_errno) {
				$error .= $mysqli->error;
			}
			$idPromocionMarketingLocal = $mysqli->insert_id;
			if (is_numeric($idPromocionMarketingLocal) && $idPromocionMarketingLocal > 0) {
				$queryUpdateRegistroTruncados = "						
				UPDATE tbl_archivos
				SET	
					estado = 0
				WHERE 
				tabla = 'tbl_promocion_marketing_local' 
				AND item_id = {$idPromocionMarketingLocal}
				";
				$mysqli->query($queryUpdateRegistroTruncados);
			}
			for ($i = 0; $i < count($_FILES['filesMarketing']["name"]); $i++) {
				$file = $_FILES['filesMarketing']['name'][$i];
				$tmp = $_FILES['filesMarketing']['tmp_name'][$i];
				$size = $_FILES['filesMarketing']['size'][$i];
				$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
				$file_error = $_FILES['filesMarketing']['error'][$i];

				$filename = strtolower(preg_replace('/.[\w]+$/', '', $file) . "_" . date('YmdHis') . "." . $ext);

				if ($size <= 10485760) {
					if (in_array($ext, $valid_extensions)) {
						$filepath = '/var/www/html/files_bucket/promociones/marketing/' . $filename;
						move_uploaded_file($tmp, $filepath);
						$queryInsertArchivos = "
						INSERT INTO tbl_archivos
						(						
							tabla,
							item_id,
							tipo,
							ext,
							size,
							nombre,
							descripcion,
							archivo,
							fecha,
							orden,
							estado
						)
						VALUES
						(
							'tbl_promocion_marketing_local',
							{$idPromocionMarketingLocal},
							'img_mark',
							'{$ext}',
							{$size},
							'{$filename}',
							'imagen para promocion marketing',
							'{$filename}',
							now(),
							0,
							1
						)
						";
						$mysqli->query($queryInsertArchivos);
						if ($mysqli->connect_errno) {
							$error .= $mysqli->error;
						}
					}
				}
			}
			$mysqli->query("COMMIT");
		}


		if ($error == '') {
			$result["http_code"] = 200;
			$result["status"] = "OK";
			$result["message"] = "Agregado con Exito";
			$result["error"] = false;
			//$result["data"] = $arrayReturn;
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrio un error";
			$result["message"] = $error;
			$result["error"] = true;
		}
	} catch (Exception $e) {
		http_response_code(500);
		$result["mensaje"]	= $e->getMessage();
		$result["error"]	= true;
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_locales_listar_promocion_local") {
	try {
		$local_id = (isset($_POST['idLocal'])) ? $_POST['idLocal'] : 0  ;
		$error = '';
		if ($local_id=='new') {
			$local_id=0;
		}
		$querySelectPromocionesLocal = "
		SELECT
			pml.id,
			pml.id_promocion_marketing,
			pm.nombre_promocion,
			pml.id_local,
			pml.usuario_id,
			pm.fecha,
			pml.estado,
			(SELECT count(*) FROM tbl_archivos AS a 
			WHERE a.tabla = 'tbl_promocion_marketing_local' 
			AND a.item_id = pml.id 
			AND a.estado = 1) AS cantidad_archivos,
			
			pml.created_at,
			pml.updated_at
		FROM
			tbl_promocion_marketing_local AS pml
			INNER JOIN tbl_promocion_marketing AS pm ON pm.id = pml.id_promocion_marketing
		WHERE
			pml.id_local = {$local_id}
			AND pml.estado = 1
			AND pm.estado = 1
		ORDER BY pml.fecha DESC 
		";
		$resultQuery = $mysqli->query($querySelectPromocionesLocal);
		$arrayReturn = [];
		if ($resultQuery) {
			while ($li = $resultQuery->fetch_assoc()) {
				$temp = new stdClass();
				$temp->id = $li['id'];
				$temp->id_promocion_marketing = $li['id_promocion_marketing'];
				$temp->nombre_promocion = $li['nombre_promocion'];
				$temp->id_local = $li['id_local'];
				$temp->id_local = $li['usuario_id'];
				$temp->fecha = $li['fecha'];
				$temp->estado = $li['estado'];
				$temp->cantidad_archivos = $li['cantidad_archivos'];
				$temp->created_at = $li['created_at'];
				$temp->updated_at = $li['updated_at'];
				array_push($arrayReturn, $temp);
				unset($temp);
			}
		}
		if ($mysqli->error) {
			$error .= 'Error: ' . $mysqli->error . $querySelectPromocionesLocal;
		}
		if ($error == '') {
			$result["http_code"] = 200;
			$result["status"] = "Datos Listados.";
			$result["error"] = false;
			$result["data"] = $arrayReturn;
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrio un error";
			$result["error"] = true;
		}
	} catch (Exception $e) {
		http_response_code(500);
		$result["mensaje"]	= $e->getMessage();
		$result["error"]	= true;
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_locales_listar_archivos_promocion_local") {
	try {
		$idPromocionLocal = $_POST['idPromocionLocal'];
		$error = '';
		$querySelectPromocionesLocal = "
		SELECT
			a.id,
			a.tabla,
			a.item_id,
			a.tipo,
			a.ext,
			a.size,
			a.nombre,
			a.descripcion,
			a.archivo,
			a.fecha
		FROM
			tbl_archivos AS a
		WHERE 
			a.tabla = 'tbl_promocion_marketing_local'
		AND a.estado = 1
		AND a.item_id = {$idPromocionLocal}
		";
		$resultQuery = $mysqli->query($querySelectPromocionesLocal);
		$arrayReturn = array();
		while ($li = $resultQuery->fetch_assoc()) {
			$temp = new stdClass();
			$temp->id = $li['id'];
			$temp->tabla = $li['tabla'];
			$temp->item_id = $li['item_id'];
			$temp->tipo = $li['tipo'];
			$temp->ext = $li['ext'];
			$temp->size = $li['size'];
			$temp->nombre = $li['nombre'];
			$temp->descripcion = $li['descripcion'];
			$temp->archivo = $li['archivo'];
			$temp->fecha = $li['fecha'];
			array_push($arrayReturn, $temp);
			unset($temp);
		}

		if ($mysqli->error) {
			$error .= 'Error: ' . $mysqli->error . $querySelectPromocionesLocal;
		}
		if ($error == '') {
			$result["http_code"] = 200;
			$result["status"] = "Datos Listados.";
			$result["error"] = false;
			$result["data"] = $arrayReturn;
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrio un error";
			$result["error"] = true;
		}
	} catch (Exception $e) {
		http_response_code(500);
		$result["mensaje"]	= $e->getMessage();
		$result["error"]	= true;
	}
}
if (isset($_POST["accion"]) && $_POST["accion"] === "sec_locales_eliminar_archivos_promocion_local") {
	try {
		$idArchivo = $_POST['idArchivo'];
		$error = '';
		$queryEliminarArchivoPromocioneLocal = "
		UPDATE tbl_archivos
		SET	
			estado = 0
			
		WHERE id = {$idArchivo}
		";
		$resultQuery = $mysqli->query($queryEliminarArchivoPromocioneLocal);

		if ($mysqli->error) {
			$error .= 'Error: ' . $mysqli->error . $queryEliminarArchivoPromocioneLocal;
		}
		if ($error == '') {
			$result["http_code"] = 200;
			$result["status"] = "Eliminado.";
			$result["error"] = false;
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrio un error";
			$result["error"] = true;
		}
	} catch (Exception $e) {
		http_response_code(500);
		$result["mensaje"]	= $e->getMessage();
		$result["error"]	= true;
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_locales_eliminar_promocion_local") {
	try {
		$idPromocionLocal = $_POST['idPromocionLocal'];
		$error = '';
		$queryActualizarPromocioneLocal = "
		UPDATE tbl_promocion_marketing_local
		SET
			estado = 0,
			updated_at = now()
		WHERE id = {$idPromocionLocal}
		";
		$resultQuery = $mysqli->query($queryActualizarPromocioneLocal);

		if ($mysqli->error) {
			$error .= 'Error: ' . $mysqli->error . $queryActualizarPromocioneLocal;
		}
		if ($error == '') {
			$result["http_code"] = 200;
			$result["status"] = "Eliminado.";
			$result["error"] = false;
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrio un error";
			$result["error"] = true;
		}
	} catch (Exception $e) {
		http_response_code(500);
		$result["mensaje"]	= $e->getMessage();
		$result["error"]	= true;
	}
}
echo json_encode($result, JSON_UNESCAPED_UNICODE);
