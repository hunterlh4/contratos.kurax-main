<?php
date_default_timezone_set("America/Lima");

include("db_connect.php");
include("sys_login.php");
include '/var/www/html/sys/function_replace_invalid_caracters_contratos.php';

if (isset($_POST['sec_contrato_licencias_list'])) {
	$contrato_id	=	isset($_POST['contrato_id'])?$_POST['contrato_id']:'';
	$autorizacion_municipal_id	=	$_POST['tipo_autorizacion'];
	
	$query_detalle_funcionamiento =
		$mysqli->query(
			"
				SELECT 
					lm.id, lm.contrato_id, ta.nombre_tipo_archivo AS tipo_archivo, 
					lm.tipo_archivo_id,
					lm.nombre_file, lm.status_licencia, lm.condicion, 
					lm.fecha_vencimiento, lm.fecha_renovacion, dj.nombre as giro,
				lm.extension, lm.download_file, lm.alerta_enviada, lm.estado, lm.created_at
				FROM cont_licencia_municipales lm
				LEFT JOIN cont_declaracion_jurada dj
					ON dj.id=lm.dj_id
				INNER JOIN cont_tipo_archivos ta
					ON lm.tipo_archivo_id = ta.tipo_archivo_id
				WHERE lm.contrato_id = '" . $contrato_id . "' 
				AND	lm.tipo_archivo_id	=	'".$autorizacion_municipal_id."'
			 	ORDER BY lm.id DESC
														"
		);

		$lista 	= [];
		$status	=	false;
		if (!$query_detalle_funcionamiento) {
			$message = "Error al realizar la consulta: " . $mysqli->error;
		} else {
			while ($d = $query_detalle_funcionamiento->fetch_assoc()) {
				$lista[] = $d;
			}
			if (count($lista) == 0) {
				$message = "No se encontraron resultados.";
			} else {
				$message = "Lista de licencias";
				$status	=	true;

			}
		}

		echo json_encode([
			"lista" => $lista,
			'status' => $status,
			'message' => $message
		]);



		// $lista = [];
		// while ($d = $query_detalle_funcionamiento->fetch_assoc()) {

		// 	$lista[] = $d;
		// }
		
		// $message	=	"Lista de licencias de funcionamiento";
		// echo json_encode([
		// 	"lista"	=>	$lista,
		// 	'status' => 1,
		// 	'message' => $message
		// ]);
}
if (isset($_POST['get_listado_giros'])) {
	
	$squery_declaracion_jurada = $mysqli->query("SELECT id, nombre AS nombre_declaracion_jurada FROM cont_declaracion_jurada WHERE status = 1");

	$lista 	= [];
	$status	=	false;
	if (!$squery_declaracion_jurada) {
		$message = "Error al realizar la consulta: " . $mysqli->error;
		 
	} else {
		while ($d = $squery_declaracion_jurada->fetch_assoc()) {
			$lista[] = $d;
		}
		if (count($lista) == 0) {
			$message = "No se encontraron resultados.";
			 
		} else {
			$message = "Lista de Giros";
			 
		}
		$status	=	true;
	
		 
	}

	echo json_encode([
		"lista" => $lista,
		'status' => $status,
		'message' => $message
	]);
}
if (isset($_POST["accion"]) && $_POST["accion"] === "actualizar_licencias_declaracion_jurada") {
	$message = "";
	$status = true;

	$contrato_id = $_POST["contrato_id"];

	$txtDeclaracionJurada = $_POST["txtDeclaracionJurada"];
	$txtNuevoDeclaracionJurada = $_POST["txtNuevoDeclaracionJurada"];


	if ($_POST["txtNuevoDeclaracionJurada"] == !"") {
		$query_insert = "INSERT INTO cont_declaracion_jurada 
						(
							nombre, 
							status, 
							user_created_id, 
							created_at, 
							user_updated_id, 
							updated_at
						) 
						VALUES 
						(
							'" . $txtNuevoDeclaracionJurada . "', 
							1, 
							'" . $login["id"] . "', 
							'" . date('Y-m-d H:i:s') . "',
							'" . $login["id"] . "', 
							'" . date('Y-m-d H:i:s') . "'
						)";

		$mysqli->query($query_insert);

		$lastid = mysqli_insert_id($mysqli);

		$query_update = "UPDATE cont_contrato SET declaracion_jurada_id = '" . $lastid . "' 
						WHERE contrato_id = '" . $contrato_id . "' ";

		$mysqli->query($query_update);
	} else {
		$query_update = "UPDATE cont_contrato SET declaracion_jurada_id = '" . $txtDeclaracionJurada . "' 
						WHERE contrato_id = '" . $contrato_id . "' ";

		$mysqli->query($query_update);
	}

	if ($mysqli->error) {
		$status = false;
		$message = $mysqli->error;
	} else {
		$message = "Datos guardados correctamente";
		$status = true;
	}

	echo json_encode([
		'id'	=> $lastid,
		'status' => $status,
		'message' => $message
	]);
}

if (isset($_POST["post_archivo_declaracion_jurada"])) {
	$message = "";
	$status = true;

	$contrato_id = $_POST["contrato_id"];
	$contrato_nombre_local = $_POST["contrato_nombre_local"];

	$txtDeclaracionJurada = $_POST["txtDeclaracionJurada"];
	$txtNuevoDeclaracionJurada = $_POST["txtNuevoDeclaracionJurada"];

	$dj_id	=	0;

	$query_update = "
							UPDATE cont_contrato SET declaracion_jurada_id = '" . $txtDeclaracionJurada . "' 
							WHERE contrato_id = '" . $contrato_id . "' 
						";

	$mysqli->query($query_update);
	$dj_id	=	$txtDeclaracionJurada;
	if ($mysqli->error) {
		$status = false;
		$message = $mysqli->error;
	} else {
		$message = "Datos guardados correctamente";
		$status = true;
	}

	if (!empty($_FILES['fileArchivoLicDeclaracionJurada']['name'])) {
		$fileLicDeclaracionJurada = $_FILES['fileArchivoLicDeclaracionJurada']['name'];
		$tmpLicDeclaracionJurada = $_FILES['fileArchivoLicDeclaracionJurada']['tmp_name'];
		$sizeLicDeclaracionJurada = $_FILES['fileArchivoLicDeclaracionJurada']['size'];
		$extLicDeclaracionJurada = strtolower(pathinfo($fileLicDeclaracionJurada, PATHINFO_EXTENSION));

		$valid_extensions = array('jpeg', 'jpg', 'png', 'pdf');

		if (in_array($extLicDeclaracionJurada, $valid_extensions)) {
			$path = "/var/www/html/files_bucket/contratos/licencias_municipales/dj/";
			$download = "/files_bucket/contratos/licencias_municipales/dj/";

			if (!is_dir($path)) {
				mkdir($path, 0777, true);
			}

			$nombreFileUpload = "LicDeclaracionJurada" . $contrato_nombre_local . date('YmdHis') . "." . $extLicDeclaracionJurada;
			$nombreDownload = $download . $nombreFileUpload;
			move_uploaded_file($tmpLicDeclaracionJurada, $path . $nombreFileUpload);


			$query_update = "
								UPDATE cont_licencia_municipales SET estado = 0 
								WHERE contrato_id = '" . $contrato_id . "' AND tipo_archivo_id = 7
							";

			$mysqli->query($query_update);

			$query_insert = "INSERT INTO cont_licencia_municipales 
							(
								contrato_id, 
								tipo_archivo_id, 
								nombre_file, 
								extension, 
								size, 
								ruta, 
								download_file, 
								dj_id,
								estado, 
								user_created_id, 
								created_at, 
								user_updated_id, 
								updated_at
							)
							VALUES 
							(
								'" . $contrato_id . "', 
								7, 
								'" . $nombreFileUpload . "', 
								'" . $extLicDeclaracionJurada . "', 
								'" . $sizeLicDeclaracionJurada . "', 
								'" . $path . "', 
								'" . $nombreDownload . "', 
								" . $dj_id . ", 
								1, 
								'" . $login["id"] . "', 
								'" . date('Y-m-d H:i:s') . "', 
								'" . $login["id"] . "', 
								'" . date('Y-m-d H:i:s') . "'
							)";

			$mysqli->query($query_insert);

			if ($mysqli->error) {
				$status = false;
				$message = $mysqli->error;
			} else {
				$message = "Datos guardados correctamente";
				$status = true;
			}
		} else {
			$message = "La extension del file no es aceptado, solo son aceptado: jpeg, jpg, png, pdf";
			$status = false;
			//return;
		}
	}

	echo json_encode([
		'status' => $status,
		'message' => $message
	]);
}

if (isset($_POST["post_archivo_declaracion_jurada_edit"])) {
	$message = "";
	$status = true;

	$contrato_id = $_POST["contrato_id"];
	$contrato_nombre_local = $_POST["contrato_nombre_local"];
	$declaracion_id = $_POST["declaracion_id"];



	if (!empty($_FILES['fileArchivoLicDeclaracionJurada_edit']['name'])) {
		$fileLicDeclaracionJurada = $_FILES['fileArchivoLicDeclaracionJurada_edit']['name'];
		$tmpLicDeclaracionJurada = $_FILES['fileArchivoLicDeclaracionJurada_edit']['tmp_name'];
		$sizeLicDeclaracionJurada = $_FILES['fileArchivoLicDeclaracionJurada_edit']['size'];
		$extLicDeclaracionJurada = strtolower(pathinfo($fileLicDeclaracionJurada, PATHINFO_EXTENSION));

		$valid_extensions = array('jpeg', 'jpg', 'png', 'pdf');

		if (in_array($extLicDeclaracionJurada, $valid_extensions)) {
			$path = "/var/www/html/files_bucket/contratos/licencias_municipales/dj/";
			$download = "/files_bucket/contratos/licencias_municipales/dj/";

			if (!is_dir($path)) {
				mkdir($path, 0777, true);
			}

			$nombreFileUpload = "LicDeclaracionJurada" . $contrato_nombre_local . date('YmdHis') . "." . $extLicDeclaracionJurada;
			$nombreDownload = $download . $nombreFileUpload;
			move_uploaded_file($tmpLicDeclaracionJurada, $path . $nombreFileUpload);

 
			
			$query_update = "UPDATE cont_licencia_municipales 
							SET 
							
							nombre_file 	=  	'" . $nombreFileUpload . "' ,  
							extension 		= 	'" . $extLicDeclaracionJurada . "',  
							size			=	'" . $sizeLicDeclaracionJurada . "', 
							ruta 			=	'" . $path . "',
							download_file	=	'" . $nombreDownload . "',
							estado			=	1, 
							user_updated_id	=	'" . $login["id"] . "', 
							updated_at		=	'" . date('Y-m-d H:i:s') . "'

							WHERE	id	='" . $declaracion_id . "' AND 
									contrato_id	=	'" . $contrato_id . "'
							";
			$mysqli->query($query_update);

			if ($mysqli->error) {
				$status = false;
				$message = $mysqli->error;
			} else {
				$message = "Datos guardados correctamente";
				$status = true;
			}
		} else {
			$message = "La extension del file no es aceptado, solo son aceptado: jpeg, jpg, png, pdf";
			$status = false;
			//return;
		}
	}

	echo json_encode([
		'status' => $status,
		'message' => $message
	]);
}


if (isset($_POST['accion']) &&  $_POST['accion'] === 'get_licencia') {
	$contrato_id = $_POST["contrato_id"];
	$licencia_id = $_POST["licencia_id"];
	$tipo_archivo = $_POST["tipo_archivo"];
	$query_detalle_funcionamiento =
		$mysqli->query(
			"
				SELECT 
					lm.id, lm.contrato_id, ta.nombre_tipo_archivo AS tipo_archivo, 
					lm.tipo_archivo_id,
					lm.nombre_file, lm.status_licencia, lm.condicion, 
					lm.fecha_vencimiento, lm.fecha_renovacion,
					lm.extension, lm.download_file, lm.alerta_enviada, lm.estado, lm.created_at
				FROM cont_licencia_municipales lm
					INNER JOIN cont_tipo_archivos ta
					ON lm.tipo_archivo_id = ta.tipo_archivo_id
				WHERE lm.contrato_id = '" . $contrato_id . "' AND lm.id ='" . $licencia_id . "'  AND lm.tipo_archivo_id = '".$tipo_archivo."' ORDER BY lm.id DESC"
		);

	$lista 	= [];
	$status	=	false;
	$http_code	=	400;

	if (!$query_detalle_funcionamiento) {
		$message = "Error al realizar la consulta: " . $mysqli->error;
	} else {
		$http_code = 200;
		$d = $query_detalle_funcionamiento->fetch_assoc(); 
		if (!$d) {
			$message = "No se encontraron resultados.";
			$status	=	false;

		} else {
			$lista = $d; 
			$message = "Lista de licencias";
			$status	=	true;

		}
	}

	echo json_encode([
		"data" => $lista,
		'status' => $status,
		'message' => $message,
		'http_code' => $http_code

	]);

}
if (isset($_POST['accion']) &&  $_POST['accion'] === 'guardar_direccion_municipal') {
	$usuario_id = $login ? $login["id"] : null;

	if ((int) $usuario_id > 0) {
		$contrato_id = $_POST["contrato_id"];
		$direccion_municipal = $_POST["direccion_municipal"];

		$direccion_municipal = preg_replace(['/[ \t]+/', '/^[\p{Z}\s]+|[\p{Z}\s]+$/u', '/\n{2,}/'], [' ', '', "\n"], trim($direccion_municipal));



		$direccion_municipal =  replace_invalid_caracters($direccion_municipal);

		$error = '';
		$query_update = "UPDATE
							cont_inmueble
						SET direccion_municipal ='$direccion_municipal',
							user_updated_id = " . $usuario_id . ",
							updated_at = NOW()
						WHERE contrato_id=" . $contrato_id;

		$mysqli->query($query_update);

		$http_code	=	400;
		$error		=	'';
		$status = false;
		if ($mysqli->error) {
			$http_code = 200;
			$error .= 'Error al actualizar dirección: ' . $mysqli->error . $query_update;
			$message = 'Error al consultar';
		}
		if ($error == '') {
			$http_code = 200;
			$status = true;
			$message = $direccion_municipal;
		} else {
			$http_code = 400;
			$message = "No se encontraron datos";
		}

		$error = $error;
	} else {
		$http_code = 400;
		$error = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y volver a hacer clic en el botón: Agregar contrato firmado.";
	}
	echo json_encode([
		'status' => $status,
		'message' => $message,
		"error" => $error,
		'http_code' => $http_code
	]);
}

if (isset($_POST["post_archivo_funcionamiento_edit"])) {

	$message = "";
	$status = true;

	$contrato_id = $_POST["contrato_id_edit"];
	$contrato_nombre_local = $_POST["contrato_nombre_local_edit"];
	$contrato_licencia_id = $_POST["contrato_licencia_id"];

	$txtLicFuncionamiento = $_POST["txtLicFuncionamiento_edit"];
	$txtCondicionLicFuncionamiento = $_POST["txtCondicionLicFuncionamiento_edit"];

	if ($txtLicFuncionamiento == "CONCLUIDO") {
		$txtFechaVencimientoLicFuncionamiento = $_POST["txtFechaVencimientoLicFuncionamiento_edit"];
		$txtFechaVencimientoLicFuncionamiento = date("Y-m-d", strtotime($txtFechaVencimientoLicFuncionamiento));
		$txtFechaRenovacionLicFuncionamiento = $_POST["txtFechaRenovacionLicFuncionamiento_edit"];
		$txtFechaRenovacionLicFuncionamiento = date("Y-m-d", strtotime($txtFechaRenovacionLicFuncionamiento));

		$fileLicFuncionamiento = $_FILES['fileArchivoLicFuncionamiento_edit']['name'];
		$tmpLicFuncionamiento = $_FILES['fileArchivoLicFuncionamiento_edit']['tmp_name'];
		$sizeLicFuncionamiento = $_FILES['fileArchivoLicFuncionamiento_edit']['size'];
		$extLicFuncionamiento = strtolower(pathinfo($fileLicFuncionamiento, PATHINFO_EXTENSION));


		$path = "/var/www/html/files_bucket/contratos/licencias_municipales/funcionamiento/";
		$download = "/files_bucket/contratos/licencias_municipales/funcionamiento/";

		if (!is_dir($path)) {
			mkdir($path, 0777, true);
		}

		$nombreFileUpload = "LicFuncion" . $contrato_nombre_local . date('YmdHis') . "." . $extLicFuncionamiento;
		$nombreDownload = $download . $nombreFileUpload;
		move_uploaded_file($tmpLicFuncionamiento, $path . $nombreFileUpload);

		// $query_update = "UPDATE cont_licencia_municipales SET estado = 0 WHERE contrato_id = '". $contrato_id ."' AND tipo_archivo_id = 4";

		// $mysqli->query($query_update);

		if ($txtCondicionLicFuncionamiento == "TEMPORAL") {
			$query_update = "UPDATE cont_licencia_municipales 
							SET 
							status_licencia = 	'" . $txtLicFuncionamiento . "', 
							condicion		= 	'" . $txtCondicionLicFuncionamiento . "', 
							fecha_vencimiento = '" . $txtFechaVencimientoLicFuncionamiento . "', 
							fecha_renovacion = 	'" . $txtFechaRenovacionLicFuncionamiento . "',  
							nombre_file 	=  	'" . $nombreFileUpload . "' ,  
							extension 		= 	'" . $extLicFuncionamiento . "',  
							size			=	'" . $sizeLicFuncionamiento . "', 
							ruta 			=	'" . $path . "',
							download_file	=	'" . $nombreDownload . "',
							estado			=	1, 
							user_updated_id	=	'" . $login["id"] . "', 
							updated_at		=	'" . date('Y-m-d H:i:s') . "'

							WHERE	id	='" . $contrato_licencia_id . "' AND 
									contrato_id	=	'" . $contrato_id . "'
							";

			$mysqli->query($query_update);

			if ($mysqli->error) {
				$status = false;
				$message = $mysqli->error;
			} else {
				$message = "Datos guardados correctamente";
				$status = true;
			}
		} else if ($txtCondicionLicFuncionamiento == "INDEFINIDA") {
			$query_update = "UPDATE cont_licencia_municipales 
							SET 
							status_licencia = 	'" . $txtLicFuncionamiento . "', 
							condicion		= 	'" . $txtCondicionLicFuncionamiento . "', 
							fecha_vencimiento = NULL, 
							fecha_renovacion = 	NULL,  
							nombre_file 	=  	'" . $nombreFileUpload . "' ,  
							extension 		= 	'" . $extLicFuncionamiento . "',  
							size			=	'" . $sizeLicFuncionamiento . "', 
							ruta 			=	'" . $path . "', 
							download_file	=	'" . $nombreDownload . "',
							estado			=	1, 
							user_updated_id	=	'" . $login["id"] . "', 
							updated_at		=	'" . date('Y-m-d H:i:s') . "'

							WHERE	id	='" . $contrato_licencia_id . "' AND 
									contrato_id	=	'" . $contrato_id . "'
							";


			$mysqli->query($query_update);

			if ($mysqli->error) {
				$status = false;
				$message = $mysqli->error;
			} else {
				$message = "Datos guardados correctamente";
				$status = true;
			}
		}
	} else {
		// $query_update = "UPDATE cont_licencia_municipales SET estado = 0 WHERE contrato_id = '". $contrato_id ."' AND tipo_archivo_id = 4";

		$mysqli->query($query_update);

		$query_update = "UPDATE cont_licencia_municipales 
							SET 
							status_licencia = 	'" . $txtLicFuncionamiento . "', 
							condicion		= 	NULL,
							fecha_vencimiento = NULL, 
							fecha_renovacion = 	NULL,  
							nombre_file 	=  	NULL,  
							extension 		= 	NULL,  
							size			=	NULL, 
							ruta 			=	NULL, 
							download_file	=	NULL,
							estado			=	1, 
							user_updated_id	=	'" . $login["id"] . "', 
							updated_at		=	'" . date('Y-m-d H:i:s') . "'

							WHERE	id	='" . $contrato_licencia_id . "' AND 
									contrato_id	=	'" . $contrato_id . "'
							";

		$mysqli->query($query_update);

		if ($mysqli->error) {
			$status = false;
			$message = $mysqli->error;
		} else {
			$message = "Datos guardados correctamente";
			$status = true;
		}
	}

	echo json_encode([
		'status' => $status,
		'message' => $message
	]);
}

if (isset($_POST["post_archivo_funcionamiento"])) {
	$message = "";
	$status = true;

	$contrato_id = $_POST["contrato_id"];
	$contrato_nombre_local = $_POST["contrato_nombre_local"];

	$txtLicFuncionamiento = $_POST["txtLicFuncionamiento"];
	$txtCondicionLicFuncionamiento = $_POST["txtCondicionLicFuncionamiento"];

	if ($txtLicFuncionamiento == "CONCLUIDO") {
		$txtFechaVencimientoLicFuncionamiento = $_POST["txtFechaVencimientoLicFuncionamiento"];
		$txtFechaVencimientoLicFuncionamiento = date("Y-m-d", strtotime($txtFechaVencimientoLicFuncionamiento));
		$txtFechaRenovacionLicFuncionamiento = $_POST["txtFechaRenovacionLicFuncionamiento"];
		$txtFechaRenovacionLicFuncionamiento = date("Y-m-d", strtotime($txtFechaRenovacionLicFuncionamiento));

		$fileLicFuncionamiento = $_FILES['fileArchivoLicFuncionamiento']['name'];
		$tmpLicFuncionamiento = $_FILES['fileArchivoLicFuncionamiento']['tmp_name'];
		$sizeLicFuncionamiento = $_FILES['fileArchivoLicFuncionamiento']['size'];
		$extLicFuncionamiento = strtolower(pathinfo($fileLicFuncionamiento, PATHINFO_EXTENSION));


		$path = "/var/www/html/files_bucket/contratos/licencias_municipales/funcionamiento/";
		$download = "/files_bucket/contratos/licencias_municipales/funcionamiento/";

		if (!is_dir($path)) {
			mkdir($path, 0777, true);
		}

		$nombreFileUpload = "LicFuncion" . $contrato_nombre_local . date('YmdHis') . "." . $extLicFuncionamiento;
		$nombreDownload = $download . $nombreFileUpload;
		move_uploaded_file($tmpLicFuncionamiento, $path . $nombreFileUpload);

		$query_update = "UPDATE cont_licencia_municipales SET estado = 0 WHERE contrato_id = '" . $contrato_id . "' AND tipo_archivo_id = 4";

		$mysqli->query($query_update);

		if ($txtCondicionLicFuncionamiento == "TEMPORAL") {
			$query_insert = "INSERT INTO cont_licencia_municipales 
							(
								contrato_id, 
								tipo_archivo_id, 
								status_licencia, 
								condicion, 											 
								fecha_vencimiento, 
								fecha_renovacion, 
							 	nombre_file, 
							 	extension, 
							 	size, 
							 	ruta, 
							 	download_file, 
							 	estado, 
							 	user_created_id, 
							 	created_at, 
							 	user_updated_id, 
							 	updated_at)
							VALUES 
							(
								'" . $contrato_id . "', 
								4, 
								'" . $txtLicFuncionamiento . "', 
								'" . $txtCondicionLicFuncionamiento . "', 
								'" . $txtFechaVencimientoLicFuncionamiento . "', 
								'" . $txtFechaRenovacionLicFuncionamiento . "', 
								'" . $nombreFileUpload . "', 
								'" . $extLicFuncionamiento . "', 
								'" . $sizeLicFuncionamiento . "', 
								'" . $path . "', 
								'" . $nombreDownload . "', 1, 
								'" . $login["id"] . "', 
								'" . date('Y-m-d H:i:s') . "',
								'" . $login["id"] . "', 
								'" . date('Y-m-d H:i:s') . "'
							)";

			$mysqli->query($query_insert);
			 

			if ($mysqli->error) {
				$status = false;
				$message = $mysqli->error;
			} else {
				$message = "Datos guardados correctamente";
				$status = true;
			}
		} else if ($txtCondicionLicFuncionamiento == "INDEFINIDA") {
			$query_insert = "INSERT INTO cont_licencia_municipales 
							(
								contrato_id, 
								tipo_archivo_id, 
								status_licencia, 
								condicion,
							 	nombre_file, 
							 	extension, 
							 	size, 
							 	ruta, 
							 	download_file, 
							 	estado, 
							 	user_created_id, 
							 	created_at, 
							 	user_updated_id, 
							 	updated_at)
							VALUES 
							(
								'" . $contrato_id . "', 
								4, 
								'" . $txtLicFuncionamiento . "', 
								'" . $txtCondicionLicFuncionamiento . "',
								'" . $nombreFileUpload . "', 
								'" . $extLicFuncionamiento . "', 
								'" . $sizeLicFuncionamiento . "', 
								'" . $path . "', 
								'" . $nombreDownload . "', 1, 
								'" . $login["id"] . "', 
								'" . date('Y-m-d H:i:s') . "',
								'" . $login["id"] . "', 
								'" . date('Y-m-d H:i:s') . "'
							)";

			$mysqli->query($query_insert);

			if ($mysqli->error) {
				$status = false;
				$message = $mysqli->error;
			} else {
				$message = "Datos guardados correctamente";
				$status = true;
			}
		}
	} else {
		$query_update = "UPDATE cont_licencia_municipales SET estado = 0 WHERE contrato_id = '" . $contrato_id . "' AND tipo_archivo_id = 4";

		$mysqli->query($query_update);

		$query_insert = "INSERT INTO cont_licencia_municipales
						(
							contrato_id, 
							tipo_archivo_id, 
							status_licencia, 								 	
						 	estado, 
						 	user_created_id, 
						 	created_at, 
						 	user_updated_id, 
						 	updated_at
						 )
						VALUES 
						(
							'" . $contrato_id . "', 
							4, 
							'" . $txtLicFuncionamiento . "',
							1, 
							'" . $login["id"] . "', 
							'" . date('Y-m-d H:i:s') . "',
							'" . $login["id"] . "', 
							'" . date('Y-m-d H:i:s') . "'
						)";

		$mysqli->query($query_insert);

		if ($mysqli->error) {
			$status = false;
			$message = $mysqli->error;
		} else {
			$message = "Datos guardados correctamente";
			$status = true;
		}
	}

	echo json_encode([
		'status' => $status,
		'message' => $message
	]);
}

if (isset($_POST["post_archivo_indeci"])) {
	$message = "";
	$status = true;

	$contrato_id = $_POST["contrato_id"];
	$contrato_nombre_local = $_POST["contrato_nombre_local"];

	$txtLicIndeci = $_POST["txtLicIndeci"];

	if ($txtLicIndeci == "CONCLUIDO") {
		$txtFechaVencimientoLicIndeci = $_POST["txtFechaVencimientoLicIndeci"];
		$txtFechaVencimientoLicIndeci = date("Y-m-d", strtotime($txtFechaVencimientoLicIndeci));
		$txtFechaRenovacionLicIndeci = $_POST["txtFechaRenovacionLicIndeci"];
		$txtFechaRenovacionLicIndeci = date("Y-m-d", strtotime($txtFechaRenovacionLicIndeci));

		$fileLicIndeci = $_FILES['fileArchivoLicIndeci']['name'];
		$tmpLicIndeci = $_FILES['fileArchivoLicIndeci']['tmp_name'];
		$sizeLicIndeci = $_FILES['fileArchivoLicIndeci']['size'];
		$extLicIndeci = strtolower(pathinfo($fileLicIndeci, PATHINFO_EXTENSION));

		$path = "/var/www/html/files_bucket/contratos/licencias_municipales/indeci/";
		$download = "/files_bucket/contratos/licencias_municipales/indeci/";

		if (!is_dir($path)) {
			mkdir($path, 0777, true);
		}

		$nombreFileUpload = "LicIndeci" . $contrato_nombre_local . date('YmdHis') . "." . $extLicIndeci;
		$nombreDownload = $download . $nombreFileUpload;
		move_uploaded_file($tmpLicIndeci, $path . $nombreFileUpload);

		$query_update = "UPDATE cont_licencia_municipales SET estado = 0 WHERE contrato_id = '" . $contrato_id . "' AND tipo_archivo_id = 5";

		$mysqli->query($query_update);

		$query_insert = "INSERT INTO cont_licencia_municipales 
						(
							contrato_id, 
							tipo_archivo_id, 
							status_licencia, 										 
							fecha_vencimiento, 
							fecha_renovacion, 
							nombre_file, 
							extension, 
							size, 
							ruta, 
							download_file, 
							estado, 
							user_created_id, 
							created_at, 
							user_updated_id, 
							updated_at)
						VALUES 
						(
							'" . $contrato_id . "', 
							5, 
							'" . $txtLicIndeci . "',
							'" . $txtFechaVencimientoLicIndeci . "', 
							'" . $txtFechaRenovacionLicIndeci . "', 
							'" . $nombreFileUpload . "', 
							'" . $extLicIndeci . "', 
							'" . $sizeLicIndeci . "', 
							'" . $path . "', 
							'" . $nombreDownload . "', 
							1, 
							'" . $login["id"] . "', 
							'" . date('Y-m-d H:i:s') . "',
							'" . $login["id"] . "', 
							'" . date('Y-m-d H:i:s') . "'
						)";

		$mysqli->query($query_insert);

		if ($mysqli->error) {
			$status = false;
			$message = $mysqli->error;
		} else {
			$message = "Datos guardados correctamente";
			$status = true;
		}
	} else {
		$query_update = "UPDATE cont_licencia_municipales SET estado = 0 WHERE contrato_id = '" . $contrato_id . "' AND tipo_archivo_id = 5";

		$mysqli->query($query_update);

		$query_insert = "INSERT INTO cont_licencia_municipales 
						(
							contrato_id, 
							tipo_archivo_id, 
							status_licencia,
							estado,
							user_created_id, 
							created_at, 
							user_updated_id, 
							updated_at
						)
						VALUES 
						(
							'" . $contrato_id . "', 
							5, 
							'" . $txtLicIndeci . "',
							1, 
							'" . $login["id"] . "', 
							'" . date('Y-m-d H:i:s') . "', 
							'" . $login["id"] . "', 
							'" . date('Y-m-d H:i:s') . "'
						)";

		$mysqli->query($query_insert);

		if ($mysqli->error) {
			$status = false;
			$message = $mysqli->error;
		} else {
			$message = "Datos guardados correctamente";
			$status = true;
		}
	}

	echo json_encode([
		'status' => $status,
		'message' => $message
	]);
}
if (isset($_POST["post_archivo_indeci_edit"])) {
	$message = "";
	$status = true;

	$contrato_nombre_local = $_POST["contrato_nombre_local_indeci_dit"];
	$contrato_cert_indeci_id = $_POST["contrato_cert_indeci_id"];
	$cert_indeci_id = $_POST["cert_indeci_id"];

	$txtLicIndeci_edit = $_POST["txtLicIndeci_edit"];

	if ($txtLicIndeci_edit == "CONCLUIDO") {
		$txtFechaVencimientoLicIndeci = $_POST["txtFechaVencimientoLicFuncionamiento_edit"];
		$txtFechaVencimientoLicIndeci = date("Y-m-d", strtotime($txtFechaVencimientoLicIndeci));
		$txtFechaRenovacionLicIndeci = $_POST["txtFechaRenovacionLicFuncionamiento_edit"];
		$txtFechaRenovacionLicIndeci = date("Y-m-d", strtotime($txtFechaRenovacionLicIndeci));

		$fileLicIndeci = $_FILES['fileArchivoLicIndeci_edit']['name'];
		$tmpLicIndeci = $_FILES['fileArchivoLicIndeci_edit']['tmp_name'];
		$sizeLicIndeci = $_FILES['fileArchivoLicIndeci_edit']['size'];
		$extLicIndeci = strtolower(pathinfo($fileLicIndeci, PATHINFO_EXTENSION));

		$path = "/var/www/html/files_bucket/contratos/licencias_municipales/indeci/";
		$download = "/files_bucket/contratos/licencias_municipales/indeci/";

		if (!is_dir($path)) {
			mkdir($path, 0777, true);
		}

		$nombreFileUpload = "LicIndeci" . $contrato_nombre_local . date('YmdHis') . "." . $extLicIndeci;
		$nombreDownload = $download . $nombreFileUpload;
		move_uploaded_file($tmpLicIndeci, $path . $nombreFileUpload);

 

		$query_update = "UPDATE cont_licencia_municipales 
							SET 
							status_licencia = 	'" . $txtLicIndeci_edit . "', 
							fecha_vencimiento = '" . $txtFechaVencimientoLicIndeci . "', 
							fecha_renovacion = 	'" . $txtFechaRenovacionLicIndeci . "',  
							nombre_file 	=  	'" . $nombreFileUpload . "' ,  
							extension 		= 	'" . $extLicIndeci . "',  
							size			=	'" . $sizeLicIndeci . "', 
							ruta 			=	'" . $path . "',
							download_file	=	'" . $nombreDownload . "',
							estado			=	1, 
							user_updated_id	=	'" . $login["id"] . "', 
							updated_at		=	'" . date('Y-m-d H:i:s') . "'

							WHERE	id	='" . $cert_indeci_id . "' AND 
									contrato_id	=	'" . $contrato_cert_indeci_id . "'
							";
		$mysqli->query($query_update);

		if ($mysqli->error) {
			$status = false;
			$message = $mysqli->error;
		} else {
			$message = "Datos guardados correctamente";
			$status = true;
		}
	} else {
		 
		$query_update = "UPDATE cont_licencia_municipales 
							SET 
							status_licencia = 	'" . $txtLicIndeci_edit . "', 
							fecha_vencimiento = NULL, 
							fecha_renovacion = 	NULL,  
							nombre_file 	=  	NULL,  
							extension 		= 	NULL,  
							size			=	NULL, 
							ruta 			=	NULL,
							download_file	=	NULL,
							estado			=	1, 
							user_updated_id	=	'" . $login["id"] . "', 
							updated_at		=	'" . date('Y-m-d H:i:s') . "'

							WHERE	id	='" . $cert_indeci_id . "' AND 
									contrato_id	=	'" . $contrato_cert_indeci_id . "'
							";
		$mysqli->query($query_update);

		if ($mysqli->error) {
			$status = false;
			$message = $mysqli->error;
		} else {
			$message = "Datos guardados correctamente";
			$status = true;
		}
	}

	echo json_encode([
		'status' => $status,
		'message' => $message
	]);
}

if (isset($_POST["post_archivo_publicidad"])) {
	$message = "";
	$status = true;


	$contrato_id = $_POST["contrato_id"];
	$contrato_nombre_local = $_POST["contrato_nombre_local"];

	$txtLicPublicidad = $_POST["txtLicPublicidad"];
	$txtCondicionLicPublicidad = $_POST["txtCondicionLicPublicidad"];

	if ($txtLicPublicidad == "CONCLUIDO") {
		$txtFechaVencimientoLicPublicidad = $_POST["txtFechaVencimientoLicPublicidad"];
		$txtFechaVencimientoLicPublicidad = date("Y-m-d", strtotime($txtFechaVencimientoLicPublicidad));
		$txtFechaRenovacionLiPublicidad = $_POST["txtFechaRenovacionLiPublicidad"];
		$txtFechaRenovacionLiPublicidad = date("Y-m-d", strtotime($txtFechaRenovacionLiPublicidad));

		$fileLicPublicidad = $_FILES['fileArchivoLicPublicidad']['name'];
		$tmpLicPublicidad = $_FILES['fileArchivoLicPublicidad']['tmp_name'];
		$sizeLicPublicidad = $_FILES['fileArchivoLicPublicidad']['size'];
		$extLicPublicidad = strtolower(pathinfo($fileLicPublicidad, PATHINFO_EXTENSION));

		$path = "/var/www/html/files_bucket/contratos/licencias_municipales/publicidad/";
		$download = "/files_bucket/contratos/licencias_municipales/publicidad/";

		if (!is_dir($path)) {
			mkdir($path, 0777, true);
		}

		$nombreFileUpload = "LicPublicidad" . $contrato_nombre_local . date('YmdHis') . "." . $extLicPublicidad;
		$nombreDownload = $download . $nombreFileUpload;
		move_uploaded_file($tmpLicPublicidad, $path . $nombreFileUpload);


		$query_update = "UPDATE cont_licencia_municipales SET estado = 0 WHERE contrato_id = '" . $contrato_id . "' AND tipo_archivo_id = 6";

		$mysqli->query($query_update);

		if ($txtCondicionLicPublicidad == "TEMPORAL") {
			$query_insert = "INSERT INTO cont_licencia_municipales 
							(
								contrato_id, 
								tipo_archivo_id, 
								status_licencia, 
								condicion, 											 
								fecha_vencimiento, 
								fecha_renovacion, 
								nombre_file, 
								extension, 
								size, 
								ruta, 
								download_file, 
								estado, 
								user_created_id, 
								created_at, 
								user_updated_id, 
								updated_at
							)
							VALUES 
							(
								'" . $contrato_id . "', 
								6, 
								'" . $txtLicPublicidad . "', 
								'" . $txtCondicionLicPublicidad . "', 
								'" . $txtFechaVencimientoLicPublicidad . "', 
								'" . $txtFechaRenovacionLiPublicidad . "', 
								'" . $nombreFileUpload . "', 
								'" . $extLicPublicidad . "', 
								'" . $sizeLicPublicidad . "', 
								'" . $path . "', 
								'" . $nombreDownload . "', 1, 
								'" . $login["id"] . "', 
								'" . date('Y-m-d H:i:s') . "',
								'" . $login["id"] . "', 
								'" . date('Y-m-d H:i:s') . "'
							)";

			$mysqli->query($query_insert);

			if ($mysqli->error) {
				$status = false;
				$message = $mysqli->error;
			} else {
				$message = "Datos guardados correctamente";
				$status = true;
			}
		} else if ($txtCondicionLicPublicidad == "INDEFINIDA") {
			$query_insert = "INSERT INTO cont_licencia_municipales 
							(
								contrato_id, 
								tipo_archivo_id, 
								status_licencia, 
								condicion, 											 
								nombre_file, 
								extension, 
								size, 
								ruta, 
								download_file, 
								estado, 
								user_created_id, 
								created_at, 
								user_updated_id, 
								updated_at
							)
							VALUES 
							(
								'" . $contrato_id . "', 
								6, 
								'" . $txtLicPublicidad . "', 
								'" . $txtCondicionLicPublicidad . "', 
								'" . $nombreFileUpload . "', 
								'" . $extLicPublicidad . "', 
								'" . $sizeLicPublicidad . "', 
								'" . $path . "', 
								'" . $nombreDownload . "', 
								1, 
								'" . $login["id"] . "', 
								'" . date('Y-m-d H:i:s') . "',
								'" . $login["id"] . "', 
								'" . date('Y-m-d H:i:s') . "'
							)";

			$mysqli->query($query_insert);

			if ($mysqli->error) {
				$status = false;
				$message = $mysqli->error;
			} else {
				$message = "Datos guardados correctamente";
				$status = true;
			}
		}
	} else {
		$query_update = "UPDATE cont_licencia_municipales SET estado = 0 WHERE contrato_id = '" . $contrato_id . "' AND tipo_archivo_id = 6";

		$mysqli->query($query_update);

		$query_insert = "INSERT INTO cont_licencia_municipales 
						(
							contrato_id, 
							tipo_archivo_id, 
							status_licencia, 
							estado, 
							user_created_id, 
							created_at, 
							user_updated_id, 
							updated_at
						)
						VALUES 
						(
							'" . $contrato_id . "', 
							6, 
							'" . $txtLicPublicidad . "', 
							1, 
							'" . $login["id"] . "', 
							'" . date('Y-m-d H:i:s') . "',
							'" . $login["id"] . "', 
							'" . date('Y-m-d H:i:s') . "'
						)";

		$mysqli->query($query_insert);

		if ($mysqli->error) {
			$status = false;
			$message = $mysqli->error;
		} else {
			$message = "Datos guardados correctamente";
			$status = true;
		}
	}

	echo json_encode([
		'status' => $status,
		'message' => $message
	]);
}

if (isset($_POST["post_archivo_publicidad_edit"])) {
	$message = "";
	$status = true;


	$contrato_id = $_POST["contrato_id_autorizacion"];
	$contrato_nombre_local = $_POST["contrato_nombre_local_autorizacion"];
	$autorizacion_id = $_POST["autorizacion_id"];

	$txtLicPublicidad = $_POST["txtLicPublicidad_edit"];
	$txtCondicionLicPublicidad = $_POST["txtCondicionLicPublicidad_edit"];

	if ($txtLicPublicidad == "CONCLUIDO") {
		$txtFechaVencimientoLicPublicidad = $_POST["txtFechaVencimientoLicPublicidad_edit"];
		$txtFechaVencimientoLicPublicidad = date("Y-m-d", strtotime($txtFechaVencimientoLicPublicidad));
		$txtFechaRenovacionLiPublicidad = $_POST["txtFechaRenovacionLiPublicidad_edit"];
		$txtFechaRenovacionLiPublicidad = date("Y-m-d", strtotime($txtFechaRenovacionLiPublicidad));

		$fileLicPublicidad = $_FILES['fileArchivoLicPublicidad_edit']['name'];
		$tmpLicPublicidad = $_FILES['fileArchivoLicPublicidad_edit']['tmp_name'];
		$sizeLicPublicidad = $_FILES['fileArchivoLicPublicidad_edit']['size'];
		$extLicPublicidad = strtolower(pathinfo($fileLicPublicidad, PATHINFO_EXTENSION));

		$path = "/var/www/html/files_bucket/contratos/licencias_municipales/publicidad/";
		$download = "/files_bucket/contratos/licencias_municipales/publicidad/";

		if (!is_dir($path)) {
			mkdir($path, 0777, true);
		}

		$nombreFileUpload = "LicPublicidad" . $contrato_nombre_local . date('YmdHis') . "." . $extLicPublicidad;
		$nombreDownload = $download . $nombreFileUpload;
		move_uploaded_file($tmpLicPublicidad, $path . $nombreFileUpload);

		if ($txtCondicionLicPublicidad == "TEMPORAL") {
			$query_update = "UPDATE cont_licencia_municipales 
							SET 
							status_licencia = 	'" . $txtLicPublicidad . "', 
							condicion		= 	'" . $txtCondicionLicPublicidad . "', 
							fecha_vencimiento = '" . $txtFechaVencimientoLicPublicidad . "', 
							fecha_renovacion = 	'" . $txtFechaRenovacionLiPublicidad . "',  
							nombre_file 	=  	'" . $nombreFileUpload . "' ,  
							extension 		= 	'" . $extLicPublicidad . "',  
							size			=	'" . $sizeLicPublicidad . "', 
							ruta 			=	'" . $path . "',
							download_file	=	'" . $nombreDownload . "',
							estado			=	1, 
							user_updated_id	=	'" . $login["id"] . "', 
							updated_at		=	'" . date('Y-m-d H:i:s') . "'

							WHERE	id	='" . $autorizacion_id . "' AND 
									contrato_id	=	'" . $contrato_id . "'
							";

			// var_dump($query_update);exit();
			$mysqli->query($query_update);

			if ($mysqli->error) {
				$status = false;
				$message = $mysqli->error;
			} else {
				$message = "Datos guardados correctamente";
				$status = true;
			}
		} else if ($txtCondicionLicPublicidad == "INDEFINIDA") {
			$query_update = "UPDATE cont_licencia_municipales 
							SET 
							status_licencia = 	'" . $txtLicPublicidad . "', 
							condicion		= 	'" . $txtCondicionLicPublicidad . "', 
							fecha_vencimiento = 	NULL, 
							fecha_renovacion = 		NULL,  
							nombre_file 	=  	'" . $nombreFileUpload . "' ,  
							extension 		= 	'" . $extLicPublicidad . "',  
							size			=	'" . $sizeLicPublicidad . "', 
							ruta 			=	'" . $path . "',
							download_file	=	'" . $nombreDownload . "',
							estado			=	1, 
							user_updated_id	=	'" . $login["id"] . "', 
							updated_at		=	'" . date('Y-m-d H:i:s') . "'

							WHERE	id	='" . $autorizacion_id . "' AND 
									contrato_id	=	'" . $contrato_id . "'
							";


			$mysqli->query($query_update);

			if ($mysqli->error) {
				$status = false;
				$message = $mysqli->error;
			} else {
				$message = "Datos guardados correctamente";
				$status = true;
			}
		}
	} else {


		$query_update = "UPDATE cont_licencia_municipales 
							SET 
							status_licencia = 	'" . $txtLicPublicidad . "', 
							condicion		= 	NULL, 
							fecha_vencimiento = NULL, 
							fecha_renovacion = 	NULL,  
							nombre_file 	=  	NULL,  
							extension 		= 	NULL,  
							size			=	NULL, 
							ruta 			=	NULL, 
							download_file	=	NULL,
							estado			=	1, 
							user_updated_id	=	'" . $login["id"] . "', 
							updated_at		=	'" . date('Y-m-d H:i:s') . "'

							WHERE	id	='" . $autorizacion_id . "' AND 
									contrato_id	=	'" . $contrato_id . "'
							";

		$mysqli->query($query_update);

		if ($mysqli->error) {
			$status = false;
			$message = $mysqli->error;
		} else {
			$message = "Datos guardados correctamente";
			$status = true;
		}
	}

	echo json_encode([
		'status' => $status,
		'message' => $message
	]);
}

