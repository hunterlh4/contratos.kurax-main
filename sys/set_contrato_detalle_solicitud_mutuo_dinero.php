<?php
date_default_timezone_set("America/Lima");

$result=array();
include("db_connect.php");
include("sys_login.php");

include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';
require_once '/var/www/html/sys/helpers.php';

require_once '/var/www/html/env.php';
include '/var/www/html/sys/envio_correos.php';
include '/var/www/html/sys/set_contrato_seguimiento_proceso.php';
include '/var/www/html/sys/function_replace_invalid_caracters_contratos.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST["accion"]) && $_POST["accion"] == "insert_nuevo_local") {
	
	insertar_nuevo_local_en_tbl_locales(1445);
	insertar_nuevo_local_en_tbl_locales(1413);
	insertar_nuevo_local_en_tbl_locales(1419);
	insertar_nuevo_local_en_tbl_locales(1406);
	insertar_nuevo_local_en_tbl_locales(1397);
	insertar_nuevo_local_en_tbl_locales(1384);
	insertar_nuevo_local_en_tbl_locales(1383);

	$result['result'] =  "insertados...";
}


if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_observaciones_contrato") {
	$usuario_id = $login?$login['id']:null;

	if ( !((int) $usuario_id > 0) ) {
		$result["http_code"] = 500;
		$result["status"] = "error";
		$result["mensaje"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y hacer click e el boton verde: Agregar observaciónes.";
		echo json_encode($result);
		die;
	}

	$created_at = date('Y-m-d H:i:s');
	$error = '';

	if(isset($_POST["correos_adjuntos"])  && $_POST["correos_adjuntos"] != ""){
		$validate = true;
		$emails = preg_split('[,|;]',$_POST["correos_adjuntos"]);
		foreach($emails as $e){
		    if(preg_match('/^[_ña-z0-9-]+(\.[_ña-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/',trim($e)) == 0){
				$error_msg = "'" . $e . "'" ." no es un Correo Adjunto válido";
				if($e == "")
				{
					$error_msg = "Formato de Correo Incorrecto";
				}
		        $result["http_code"] = 500;
				$result["status"] = "error";
				$result["mensaje"] = $error_msg;
				echo json_encode($result);
				die;
		    }
		}
	}

	// INICIO INSERTAR OBSERVACIONES
	$query_insert = " INSERT INTO cont_observaciones(
	contrato_id,
	observaciones,
	status,
	user_created_id,
	created_at)
	VALUES(
	" . $_POST["contrato_id"] . ",
	'" . $_POST["observaciones"] . "',
	1,
	" . $usuario_id . ",
	'" . $created_at . "' )";

	$mysqli->query($query_insert);

	if($mysqli->error){
		$error .= $mysqli->error;
		echo $query_insert;
	}
	// FIN INSERTAR OBSERVACIONES

	if($_POST["tipo_observacion"] === 'local')
	{
		send_email_observacion_contrato_arrendamiento($mysqli->insert_id , $_POST["correos_adjuntos"]);
	}
	else if($_POST["tipo_observacion"] === 'proveedor')
	{
		send_email_observacion_contrato_proveedor($mysqli->insert_id , $_POST["correos_adjuntos"]);
	}
	else if($_POST["tipo_observacion"] === 'acuerdo_confidencialidad')
	{
		send_email_observacion_acuerdo_confidencialidad($mysqli->insert_id , $_POST["correos_adjuntos"]);
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = "ok";
	$result["error"] = $error;
}

if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_observaciones_contrato_gerencia") {
	$usuario_id = $login?$login['id']:null;

	if ( !((int) $usuario_id > 0) ) {
		$result["http_code"] = 500;
		$result["status"] = "error";
		$result["mensaje"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y hacer click e el boton Amarillo: Enviar observación.";
		echo json_encode($result);
		die;
	}

	$created_at = date('Y-m-d H:i:s');
	$error = '';

	// INICIO INSERTAR OBSERVACIONES
	$query_insert = " INSERT INTO cont_observaciones(
	contrato_id,
	observaciones,
	status,
	user_created_id,
	created_at)
	VALUES(
	" . $_POST["contrato_id"] . ",
	'" . $_POST["observaciones"] . "',
	1,
	" . $usuario_id . ",
	'" . $created_at . "' )";

	$mysqli->query($query_insert);

	if($mysqli->error){
		$error .= $mysqli->error;
		echo $query_insert;
	}
	// FIN INSERTAR OBSERVACIONES


	send_email_observacion_proveedor_gerencia($mysqli->insert_id);



	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = "ok";
	$result["error"] = $error;
}



if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_observaciones_contrato_agente") {
	$usuario_id = $login?$login['id']:null;

	if ( !((int) $usuario_id > 0) ) {
		$result["http_code"] = 500;
		$result["status"] = "error";
		$result["mensaje"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y hacer click e el boton verde: Agregar observaciónes.";
		echo json_encode($result);
		die;
	}

	$created_at = date('Y-m-d H:i:s');
	$error = '';

	if(isset($_POST["correos_adjuntos"])  && $_POST["correos_adjuntos"] != ""){
		$validate = true;
		$emails = preg_split('[,|;]',$_POST["correos_adjuntos"]);
		foreach($emails as $e){
		    if(preg_match('/^[_ña-z0-9-]+(\.[_ña-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/',trim($e)) == 0){
				$error_msg = "'" . $e . "'" ." no es un Correo Adjunto válido";
				if($e == "")
				{
					$error_msg = "Formato de Correo Incorrecto";
				}
		        $result["http_code"] = 500;
				$result["status"] = "error";
				$result["mensaje"] = $error_msg;
				echo json_encode($result);
				die;
		    }
		}
	}

	// INICIO INSERTAR OBSERVACIONES
	$observ_det_agente = str_replace( "'", " ", $_POST["observaciones"]);

	$query_insert = " INSERT INTO cont_observaciones(
	contrato_id,
	observaciones,
	status,
	user_created_id,
	created_at)
	VALUES(
	" . $_POST["contrato_id"] . ",
	'" . $observ_det_agente . "',
	1,
	" . $usuario_id . ",
	'" . $created_at . "' )";

	$mysqli->query($query_insert);

	if($mysqli->error){
		$error .= $mysqli->error;
		echo $query_insert;
	}
	// FIN INSERTAR OBSERVACIONES

	send_email_observacion_contrato_agente($mysqli->insert_id , $_POST["correos_adjuntos"]);

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = "ok";
	$result["error"] = $error;
}


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_observaciones") {

	$contrato_id = $_POST["contrato_id"];
	$html = '';

	$sql = "
	SELECT 
		o.id,
		o.observaciones,
		CONCAT(IFNULL(p.nombre, ''), ' ', IFNULL(p.apellido_paterno, '')) AS usuario,
		ar.nombre AS area,
		o.user_created_id,
		o.created_at,
		a.codigo AS num_adenda
	FROM
		cont_observaciones o
		INNER JOIN tbl_usuarios u ON o.user_created_id = u.id
		INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		INNER JOIN tbl_areas ar ON p.area_id = ar.id
		LEFT JOIN cont_adendas a ON o.adenda_id = a.id
	WHERE
		o.contrato_id = $contrato_id
		AND o.status = 1
	ORDER BY o.created_at ASC
	";

	$query = $mysqli->query($sql);
	$row_count = $query->num_rows;

	if ($row_count > 0)
	{
		while ($row = $query->fetch_assoc())
		{
			$date = date_create($row["created_at"]);
			$created_at = date_format($date,"d/m/Y h:i a");

			if($row["user_created_id"] == $login['id'])
			{
				// ESTE DIV ES PARA EL USUARIO LOGUEADO
				$html .= '<div class="col-sm-offset-1 col-sm-11 caja_usuario_aprobacion alert alert-success" style="padding-bottom:10px;color:#333;box-shadow: 4px 4px 4px rgba(0,0,0,0.15);">';

				$html .= '<div class="col-sm-7" style="margin-bottom:5px;">';
				$html .= '<strong>'. $row["usuario"] .' (' . $row["area"] .')</strong>';

				if (trim($row["num_adenda"]) != "") {
					$html .= '<span style="color:blue;font-weight: bold;"> Adenda N. ' . $row["num_adenda"] . '</span>';
				}

				$html .= '</div>';

				$html .= '<div class="col-sm-4 text-right" style="color:rgba(0,0,0,0.5); font-size: 11px;">';
				$html .= '<span class="time"><i class="fa fa-clock-o"></i> ' . $created_at . '</span>';
				$html .= '</div>';

				$html .= '<div class="col-sm-1 text-right" style="color:rgba(0,0,0,0.5); font-size: 11px;">';
				$html .= '<a onclick="sec_contrato_detalle_solicitudv2_eliminar_observacion('.$row['id'].')"><i class="fa fa-trash text-danger"></i></a>';
				$html .= '</div>';

				$html .= '<div class="col-xs-12" style="padding-top: 5px; background:rgba(255,255,255,0.7); margin-bottom:2px;">';
				$html .= $row["observaciones"];
				$html .= '</div>';

				$html .= '</div>';
			}
			else
			{
				// ESTE DIV ES PARA OTROS USUARIOS
				$html .= '<div class="col-sm-11 caja_usuario_creador alert alert-info" style="padding-bottom:10px;color:#333;box-shadow: 4px 4px 4px rgba(0,0,0,0.15);">';

				$html .= '<div class="col-sm-8" style="margin-bottom:5px;">';
				$html .= '<strong>'. $row["usuario"] .' (' . $row["area"] .')</strong>';

				if (trim($row["num_adenda"]) != "") {
					$html .= '<span style="color:blue;font-weight: bold;"> Adenda N. ' . $row["num_adenda"] . '</span>';
				}

				$html .= '</div>';

				$html .= '<div class="col-sm-4 text-right" style="color:rgba(0,0,0,0.5); font-size: 11px;">';
				$html .= '<span class="time"><i class="fa fa-clock-o"></i> <strong>' . $created_at . '</strong></span>';
				$html .= '</div>';

				$html .= '<div class="col-xs-12" style="padding-top: 5px; background:rgba(255,255,255,0.7); margin-bottom:2px;">';
				$html .= $row["observaciones"];
				$html .= '</div>';

				$html .= '</div>';
			}
		}
	}


	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}

	$result["http_code"] = 200;
	$result["cant_mensaje"] = $row_count;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $html;
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_observaciones_gerencia") {

	$contrato_id = $_POST["contrato_id"];
	$html = '';

	$sql = "SELECT
	o.observaciones,
	concat(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, '')) AS usuario,
	ar.nombre AS area,
	o.user_created_id,
	o.created_at
	FROM cont_observaciones o
	INNER JOIN tbl_usuarios u ON o.user_created_id = u.id
	INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
	INNER JOIN tbl_areas ar ON p.area_id = ar.id
	WHERE o.contrato_id = " . $contrato_id . "
	AND o.status = 1
	AND o.user_created_id IN (SELECT ud.user_id FROM cont_usuarios_directores ud WHERE ud.status = 1)
	ORDER BY o.created_at ASC";

	$query = $mysqli->query($sql);
	$row_count = $query->num_rows;

	if ($row_count > 0)
	{
		while ($row = $query->fetch_assoc())
		{
			$date = date_create($row["created_at"]);
			$created_at = date_format($date,"d/m/Y h:i a");

			if($row["user_created_id"] == $login['id'])
			{
				// ESTE DIV ES PARA EL USUARIO LOGUEADO
				$html .= '<div class="col-sm-offset-1 col-sm-11 caja_usuario_aprobacion alert alert-success" style="padding-bottom:10px;color:#333;box-shadow: 4px 4px 4px rgba(0,0,0,0.15);">';

				$html .= '<div class="col-sm-8" style="margin-bottom:5px;">';
				$html .= '<strong>'. $row["usuario"] .'(' . $row["area"] .')</strong>';
				$html .= '</div>';

				$html .= '<div class="col-sm-4 text-right" style="color:rgba(0,0,0,0.5); font-size: 11px;">';
				$html .= '<span class="time"><i class="fa fa-clock-o"></i> ' . $created_at . '</span>';
				$html .= '</div>';

				$html .= '<div class="col-xs-12" style="padding-top: 5px; background:rgba(255,255,255,0.7); margin-bottom:2px;">';
				$html .= $row["observaciones"];
				$html .= '</div>';

				$html .= '</div>';
			}
			else
			{
				// ESTE DIV ES PARA OTROS USUARIOS
				$html .= '<div class="col-sm-11 caja_usuario_creador alert alert-info" style="padding-bottom:10px;color:#333;box-shadow: 4px 4px 4px rgba(0,0,0,0.15);">';

				$html .= '<div class="col-sm-8" style="margin-bottom:5px;">';
				$html .= '<strong>'. $row["usuario"] .'(' . $row["area"] .')</strong>';
				$html .= '</div>';

				$html .= '<div class="col-sm-4 text-right" style="color:rgba(0,0,0,0.5); font-size: 11px;">';
				$html .= '<span class="time"><i class="fa fa-clock-o"></i> <strong>' . $created_at . '</strong></span>';
				$html .= '</div>';

				$html .= '<div class="col-xs-12" style="padding-top: 5px; background:rgba(255,255,255,0.7); margin-bottom:2px;">';
				$html .= $row["observaciones"];
				$html .= '</div>';

				$html .= '</div>';
			}
		}
	}


	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}

	$result["http_code"] = 200;
	$result["cant_mensaje"] = $row_count;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $html;
}


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_documentos_incompletos") {

	$contrato_id = $_POST["contrato_id"];
	$html = '';

	$sql = "
	SELECT
		a.archivo_id,
		a.contrato_id,
		t.tipo_archivo_id,
		t.nombre_tipo_archivo,
		a.nombre,
		a.extension,
		a.ruta
	FROM
		cont_tipo_archivos t
			LEFT JOIN
		cont_archivos a ON t.tipo_archivo_id = a.tipo_archivo_id
			AND a.contrato_id = '$contrato_id'
			AND a.status = 1
	WHERE
		t.tipo_archivo_id IN (8 , 9, 10, 11, 12, 13, 14, 15, 20, 21, 22, 23)
		AND a.nombre IS NULL
		AND a.contrato_detalle_id = ".$_POST["contrato_detalle_id"]."
	ORDER BY t.tipo_archivo_id ASC
	";

	$query = $mysqli->query($sql);
	$row_count = $query->num_rows;

	if ($row_count > 0)
	{
		$contador = 1;

		$html = '<table class="table table-bordered table-hover no-mb" style="font-size:11px; margin-top: 10px;">';
		$html .= '<thead>';

		$html .= '<tr>';
		$html .= '<th>#</th>';
		$html .= '<th>Documento</th>';
		$html .= '</tr>';

		$html .= '</thead>';
		$html .= '<tbody>';

		while ($row = $query->fetch_assoc()) {

			$html .= '<tr>';
			$html .= '<td>' . $contador . '</td>';
			$html .= '<td>' . $row["nombre_tipo_archivo"] . '</td>';
			$html .= '</tr>';

			$contador++;
		}

		$html .= '</tbody>';
		$html .= '</table>';


	}


	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}

	$result["http_code"] = 200;
	$result["cant_mensaje"] = $row_count;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $html;
}


if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_contrato_firmado") {
	$usuario_id = $login?$login['id']:null;

	if ((int) $usuario_id > 0) {
		$nombre_tienda = sec_contrato_detalle_solicitudv2_formato_nombre_de_tienda(replace_invalid_caracters(trim($_POST["nombre_tienda"])));
		$contrato_id = $_POST["contrato_id"];
		$contrato_detalle_id = $_POST["contrato_detalle_id"];
		$plazo_id = $_POST["plazo_id"];
		if($plazo_id == 1){
			$fecha_fin_sin_formato = str_replace("/","-",$_POST["fecha_fin"]);
			$fecha_fin = "'".date("Y-m-d", strtotime($fecha_fin_sin_formato))."'";
		}else{
			$fecha_fin = "NULL";
		}


		$fecha_inicio_sin_formato = str_replace("/","-",$_POST["fecha_inicio"]);
		$fecha_suscripcion_sin_formato = str_replace("/","-",$_POST["fecha_suscripcion"]);

		$fecha_inicio = date("Y-m-d", strtotime($fecha_inicio_sin_formato));
		$fecha_suscripcion = date("Y-m-d", strtotime($fecha_suscripcion_sin_formato));
		$renovacion_automatica = $_POST["renovacion_automatica"];

		$query = "
		SELECT
			c.persona_responsable_id,
			c.jefe_comercial_id,
			c.abogado_id,
			c.nombre_tienda,
			i.latitud,
			i.longitud,
			c.cc_id
		FROM
			cont_contrato c
			INNER JOIN cont_inmueble i ON c.contrato_id = i.contrato_id
		WHERE
			i.contrato_detalle_id = $contrato_detalle_id
			AND c.contrato_id = $contrato_id
		";

		$list_query = $mysqli->query($query);
		$row = $list_query->fetch_assoc();

		if (empty($row['cc_id'])) {
			$result["http_code"] = 400;
			$result["error"] = 'sin_asignar';
			$result["campo_incompleto"] = 'centro_de_costos';
			exit(json_encode($result));
		// } elseif (empty($row['abogado_id'])) {
		// 	$result["http_code"] = 400;
		// 	$result["error"] = 'sin_asignar';
		// 	$result["campo_incompleto"] = 'abogado';
		// 	exit(json_encode($result));
		} elseif (empty($row['latitud'])) {
			$result["http_code"] = 400;
			$result["error"] = 'sin_asignar';
			$result["campo_incompleto"] = 'latitud';
			exit(json_encode($result));
		} elseif (empty($row['longitud'])) {
			$result["http_code"] = 400;
			$result["error"] = 'sin_asignar';
			$result["campo_incompleto"] = 'longitud';
			exit(json_encode($result));
		} elseif (empty($row['persona_responsable_id'])) {
			$result["http_code"] = 400;
			$result["error"] = 'sin_asignar';
			$result["campo_incompleto"] = 'supervisor';
			exit(json_encode($result));
		} elseif (empty($row['jefe_comercial_id'])) {
			$result["http_code"] = 400;
			$result["error"] = 'sin_asignar';
			$result["campo_incompleto"] = 'jefe_comercial';
			exit(json_encode($result));
		}

		$created_at = date("Y-m-d H:i:s");
		$error = '';

		$path = "/var/www/html/files_bucket/contratos/contratos_firmados/locales/";

		if (isset($_FILES['archivo_contrato_'.$contrato_detalle_id]) && $_FILES['archivo_contrato_'.$contrato_detalle_id]['error'] === UPLOAD_ERR_OK) {
			if (!is_dir($path)) mkdir($path, 0777, true);

			$filename = $_FILES['archivo_contrato_'.$contrato_detalle_id]['name'];
			$filenametem = $_FILES['archivo_contrato_'.$contrato_detalle_id]['tmp_name'];
			$filesize = $_FILES['archivo_contrato_'.$contrato_detalle_id]['size'];
			$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
			if($filename != ""){
				$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
				$nombre_archivo = $contrato_id . "_CONTRATO_FIRMADO_" . date('YmdHis') . "." . $fileExt;
				move_uploaded_file($filenametem, $path . $nombre_archivo);
				$comando = "INSERT INTO cont_archivos (
								contrato_id,
								contrato_detalle_id,
								tipo_archivo_id,
								nombre,
								extension,
								size,
								ruta,
								user_created_id,
								created_at)
							VALUES(
								'" . $contrato_id . "',
								'" . $contrato_detalle_id . "',
								16,
								'" . $nombre_archivo . "',
								'" . $fileExt . "',
								'" . $filesize . "',
								'" . $path . "',
								" . $usuario_id . ",
								'" . $created_at . "'
								)";
				$mysqli->query($comando);

				if($mysqli->error){
					$error .= 'Error al guardar el archivo: ' . $mysqli->error . $comando;
				}
			}
		}

		$query_update_condicion_economica = "
		UPDATE
			cont_condicion_economica
		SET
			usuario_contrato_aprobado_id = $usuario_id,
			plazo_id = $plazo_id,
			fecha_inicio = '".$fecha_inicio."',
			fecha_fin = ".$fecha_fin.",
			fecha_suscripcion = '".$fecha_suscripcion."',
			renovacion_automatica = '".$renovacion_automatica."'
		WHERE
			status = 1
			AND contrato_detalle_id = $contrato_detalle_id
			AND contrato_id = $contrato_id
		";

		$mysqli->query($query_update_condicion_economica);

		if($mysqli->error){
			$error .= 'Error al guardar el usuario que aprueba el contrato: ' . $mysqli->error . $query_update_condicion_econimica;
		}

		//INICIO INFLACIONES
		$sql = "SELECT c.tipo_moneda_id, c.fecha_inicio FROM  cont_condicion_economica AS c
				WHERE c.status = 1 AND c.contrato_detalle_id = ".$contrato_detalle_id." AND c.contrato_id = " . $contrato_id;
		$cont_eco_query = $mysqli->query($sql);
		$row_ce = $cont_eco_query->fetch_assoc();


		$sql = "SELECT * FROM  cont_inflaciones AS i
				WHERE i.status = 1 AND i.contrato_detalle_id =  ".$contrato_detalle_id." AND i.contrato_id = " . $contrato_id;
		$inf_query = $mysqli->query($sql);
		while($sel=$inf_query->fetch_assoc()){
			$inflacion = "
			UPDATE cont_inflaciones
			SET
				fecha = '".$row_ce['fecha_inicio']."',
				moneda_id = '".$row_ce['tipo_moneda_id']."'
			WHERE id = ".$sel['id'];
			$mysqli->query($inflacion);
			if($mysqli->error){
				$error .= 'Error al guardar el usuario que aprueba el contrato: ' . $mysqli->error . $query_update_condicion_econimica;
			}
		}
		// END INFLACIONES


		// INICIO CUOTA EXTRAORINARIA
		$sql = "SELECT * FROM  cont_cuotas_extraordinarias AS c
				WHERE c.status = 1 and c.contrato_detalle_id =  ".$contrato_detalle_id." AND c.contrato_id = " . $contrato_id;
		$ce_query = $mysqli->query($sql);
		while($sel=$ce_query->fetch_assoc()){
			$cuota_extra = "
			UPDATE cont_cuotas_extraordinarias
			SET
				fecha = '".$row_ce['fecha_inicio']."'
			WHERE id = ".$sel['id'];
			$mysqli->query($cuota_extra);
			if($mysqli->error){
				$error .= 'Error al guardar el usuario que aprueba el contrato: ' . $mysqli->error . $query_update_condicion_econimica;
			}
		}
		// FIN CUOTA EXTRAORDINARIA






		//actualizar a etapa 5 cuando todos los contratos esten firmados
		$sql = "SELECT c.usuario_contrato_aprobado_id FROM  cont_condicion_economica AS c
				WHERE c.status = 1 AND c.contrato_id = " . $contrato_id;
		$cont_eco_query = $mysqli->query($sql);
		$cant_cont = mysqli_num_rows($cont_eco_query);
		$cant_cont_firmados = 0;
		while($sel = $cont_eco_query->fetch_assoc()){
			if ( (int) $sel['usuario_contrato_aprobado_id'] > 0){
				$cant_cont_firmados++;
			}
		}
		

		if ($cant_cont == $cant_cont_firmados){
			$query_update = "
			UPDATE
				cont_contrato
			SET
				nombre_tienda = '$nombre_tienda',
				etapa_id = '5'
			WHERE
				contrato_id = $contrato_id
			";
			$mysqli->query($query_update);
	
			if($mysqli->error){
				$error .= 'Error al cambiar la etapa del contrato: ' . $mysqli->error . $query_update;
			}
			else
			{
				send_email_solicitud_contrato_arrendamiento_firmada($_POST["contrato_id"]);
				// registrarFileConcar_Sispag($_POST["contrato_id"]);
				insertar_nuevo_local_en_tbl_locales($_POST["contrato_id"]);
			}
		}
		
		if ($error == '') {
			$result["http_code"] = 200;
		} else {
			$result["http_code"] = 400;
		}
	
		$result["error"] = $error;
		$result["status"] = "Datos obtenidos de gestion.";
	} else {
		$result["http_code"] = 400;
		$result["error"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y volver a hacer clic en el botón: Agregar contrato firmado.";
	}
}


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_propietario_detalle_agente") {

	$nombre_o_numdocu = $_POST["nombre_o_numdocu"];
	$tipo_busqueda = $_POST["tipo_busqueda"];
	$tipo_solicitud = $_POST["tipo_solicitud"];
	$contador_array_ids = 0;

	if($tipo_busqueda == '3' || $tipo_busqueda == '5'){
		$data = json_decode($nombre_o_numdocu);
		$ids = '';

		foreach ($data as $value) {
			if ($contador_array_ids > 0) {
				$ids .= ',';
			}
			$ids .= $value;
			$contador_array_ids++;
		}

		if($contador_array_ids == 0){
			$ids = 0;
		}
	}

	$html = '<table class="table table-bordered table-hover no-mb" style="font-size:11px; margin-top: 10px;">';
	$html .= '<thead>';

	$html .= '<tr>';

	if ($tipo_busqueda == '1' || $tipo_busqueda == '2') {
		$html .= '<th>#</th>';
		$html .= '<th>Nombre / Razón Social</th>';
		$html .= '<th>DNI o Pasaporte</th>';
		$html .= '<th>RUC</th>';
		$html .= '<th>Domicilio</th>';
		$html .= '<th>Opciones</th>';
	} else if ($tipo_busqueda == '3') {
		$html .= '<th colspan=5 style="text-align:center;">DATOS DEL MUTUARIO</th>';
		$html .= '<th colspan=2 style="text-align:center;">DATOS EN EL CASO DE SER EMPRESA</th>';
		$html .= '<th colspan=3 style="text-align:center;">DATOS DEL CONTACTO</th>';
		$html .= '<th style="text-align:center;"></th>';
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= '<th>#</th>';
		$html .= '<th>Nombre</th>';
		$html .= '<th>N.° de DNI o Pasaporte</th>';
		$html .= '<th>N.° de RUC</th>';
		$html .= '<th>Domicilio</th>';
		$html .= '<th>Representante Legal</th>';
		$html .= '<th>N° Partida Registral</th>';
		$html .= '<th>Nombre</th>';
		$html .= '<th>Teléfono</th>';
		$html .= '<th>Email</th>';
		$html .= '<th>Opciones</th>';
	} else if ($tipo_busqueda == '5') {
		$html .= '<th>#</th>';
		$html .= '<th>Nombre / Razón Social</th>';
		$html .= '<th>DNI o Pasaporte</th>';
		$html .= '<th>RUC</th>';
		$html .= '<th>Opciones</th>';
	}

	$html .= '</tr>';

	$html .= '</thead>';
	$html .= '<tbody>';

	$sql = "SELECT
	id,
	tipo_persona_id,
	nombre,
	tipo_docu_identidad_id,
	num_docu,
	num_ruc,
	direccion,
	representante_legal,
	num_partida_registral,
	contacto_nombre,
	contacto_telefono,
	contacto_email
	FROM cont_persona ";
	if($tipo_busqueda == '3' || $tipo_busqueda == '5'){
		$sql .= " WHERE id IN(" . $ids . ") ";
	} else if ($tipo_busqueda == '1') {
		$sql .= " WHERE nombre like '" . $nombre_o_numdocu . "%' ";
	} else if ($tipo_busqueda == '4'){
		$sql .= " WHERE id = '" . $nombre_o_numdocu . "'";
	} else {
		$sql .= " WHERE num_docu like '" . $nombre_o_numdocu . "%'";
	}

	$query = $mysqli->query($sql);

	if ($tipo_busqueda == '4'){
		$list = array();
		while ($li = $query->fetch_assoc()) {
			$list[] = $li;
		}
	}

	$row_count = $query->num_rows;

	if ($row_count > 0) {
		$contador = 1;

		while ($row = $query->fetch_assoc()) {
			$html .= '<tr>';
			$html .= '<td>' . $contador . '</td>';
			$html .= '<td>' . $row["nombre"] . '</td>';
			$html .= '<td>' . $row["num_docu"] . '</td>';
			$html .= '<td>' . $row["num_ruc"] . '</td>';

			if ($tipo_busqueda != '5') {
				$html .= '<td>' . $row["direccion"] . '</td>';
			}

			if ($tipo_busqueda == '1' || $tipo_busqueda == '2') {
				if ($tipo_solicitud == 'adenda') {
					$html .= '<td>';
					$html .= '<a class="btn btn-success btn-xs" onclick="sec_contrato_nuevo_agente_asignar_otros_detalles_a_la_adenda_ca(\'propietario\',' . $row["id"] . ', \'modalBuscarPropietario\')">';
					$html .= '<i class="fa fa-plus"></i> Agregar a la adenda como propietario';
					$html .= '</a>';
					$html .= '</td>';
				} else {
					$html .= '<td>';
					$html .= '<a class="btn btn-success btn-xs" onclick="sec_contrato_detalle_agente_asignar_propietario_al_contrato(' . $row["id"] . ', ' . $tipo_solicitud . ')">';
					$html .= '<i class="fa fa-plus"></i> Agregar como propietario';
					$html .= '</a>';
					$html .= '</td>';
				}
			} else if ($tipo_busqueda == '3') {
				$html .= '<td>' . $row["representante_legal"] . '</td>';
				$html .= '<td>' . $row["num_partida_registral"] . '</td>';
				$html .= '<td>' . $row["contacto_nombre"] . '</td>';
				$html .= '<td>' . $row["contacto_telefono"] . '</td>';
				$html .= '<td>' . $row["contacto_email"] . '</td>';
				$html .= '<td>';
				$html .= '<a class="btn btn-success btn-xs" data-toggle="tooltip" data-placement="top" title="Editar" style="width: 24px;" onclick="sec_contrato_nuevo_agente_editar_propietario_ca(' . $row["id"] . ')">';
				$html .= '<i class="fa fa-edit"></i>';
				$html .= '</a>';
				$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar del contrato" style="width: 24px;" onclick="sec_contrato_nuevo_agente_eliminar_propietario_agente(' . $row["id"] . ')">';
				$html .= '<i class="fa fa-trash"></i>';
				$html .= '</a>';
				$html .= '</td>';
			} else if ($tipo_busqueda == '5') {
				$html .= '<td>';
				$html .= '<a class="btn btn-success btn-xs" onclick="sec_contrato_nuevo_agente_asignar_pre_beneficiario_al_contrato(' . $row["id"] . ', \'modalBuscar\')">';
				$html .= '<i class="fa fa-plus"></i> Agregar al propietario como beneficiario';
				$html .= '</a>';
				$html .= '</td>';
			}

			$html .= '</tr>';

			$contador++;
		}
	}

	if ($row_count == 0 && $tipo_busqueda == '1') {
		$html .= '<tr>';
		$html .= '<td colspan=5 style="text-align:center;">';
		$html .= 'No existe un propietario con el nombre ' . $nombre_o_numdocu . ' en nuestra base de datos';
		$html .= '</td>';
		$html .= '</tr>';
	}

	if ($row_count == 0 && $tipo_busqueda == '2') {
		$html .= '<tr>';
		$html .= '<td colspan=5 style="text-align:center;">';
		$html .= 'No existe un propietario con el número de documento ' . $nombre_o_numdocu . ' en nuestra base de datos';
		$html .= '</td>';
		$html .= '</tr>';
	}

	if ($tipo_busqueda == '3') {
		$html .= '<tr>';
		$html .= '<td colspan=11 style="text-align:center;">';
		$html .= '<button type="button" class="btn btn-success" onclick="sec_contrato_nuevo_agente_buscar_propietario_modal_ca(\'agente\')" style="width: 180px;">';
		$html .= '<i class="icon fa fa-plus"></i>';
		$html .= '<span id="demo-button-text"> Agregar propietario</span>';
		$html .= '</button>';
		$html .= '</td>';
		$html .= '</tr>';
	}

	$html .= '</tbody>';
	$html .= '</table>';

	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	if ($tipo_busqueda == '4'){
		$result["result"] = $list;
	} else {
		$result["result"] = $html;
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_contrato_proveedor_firmado") {

	$usuario_id = $login?$login['id']:null;

	if ( !((int) $usuario_id > 0) ) {
		$result["http_code"] = 500;
		$result["status"] = "error";
		$result["mensaje"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y hacer click e el boton verde: Agregar contrato firmado.";
		echo json_encode($result);
		die;
	}

	if(isset($_POST["correos_adjuntos"]) && $_POST["correos_adjuntos"] != ""){
		$validate = true;
		$emails = preg_split('[,|;]',$_POST["correos_adjuntos"]);
		foreach($emails as $e){
		    if(preg_match('/^[_ña-z0-9-]+(\.[_ña-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/',trim($e)) == 0){
				$error_msg = "'" . $e . "'" ." no es un Correo Adjunto válido";
				if($e == "")
				{
					$error_msg = "Formato de Correo Incorrecto";
				}
		        $result["http_code"] = 500;
				$result["status"] = "error";
				$result["mensaje"] = $error_msg;
				echo json_encode($result);
				die;
		    }
		}
	}


	$contrato_id = $_POST["contrato_id"];
	$cont_detalle_proveedor_contrato_firmado_categoria_param = $_POST["cont_detalle_proveedor_contrato_firmado_categoria_param"];
	$cont_detalle_proveedor_contrato_firmado_tipo_contrato_param = $_POST["cont_detalle_proveedor_contrato_firmado_tipo_contrato_param"];
	$cont_detalle_proveedor_contrato_firmado_tipo_firma_param = $_POST["cont_detalle_proveedor_contrato_firmado_tipo_firma_param"];

	$cont_detalle_proveedor_contrato_firmado_fecha_incio_param = $_POST["cont_detalle_proveedor_contrato_firmado_fecha_incio_param"];
	$cont_detalle_proveedor_contrato_firmado_fecha_incio_param = date("Y-m-d", strtotime($cont_detalle_proveedor_contrato_firmado_fecha_incio_param));

	$cont_detalle_proveedor_contrato_firmado_fecha_suscripcion_param = $_POST["cont_detalle_proveedor_contrato_firmado_fecha_suscripcion_param"];
	$cont_detalle_proveedor_contrato_firmado_fecha_suscripcion_param = date("Y-m-d", strtotime($cont_detalle_proveedor_contrato_firmado_fecha_suscripcion_param));

	$cont_detalle_proveedor_renovacion_automatica = $_POST["cont_detalle_proveedor_renovacion_automatica"];
	$fecha_vencimiento_indefinida_id = $_POST["fecha_vencimiento_indefinida_id"];
	$cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param = 'NULL';

	if ($fecha_vencimiento_indefinida_id == 2) {
		$cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param = $_POST["cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param"];
		$cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param = "'" . date("Y-m-d", strtotime($cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param)) . "'";
	}

	// $query = "SELECT c.abogado_id FROM cont_contrato c WHERE c.contrato_id = $contrato_id";
	// $list_query = $mysqli->query($query);
	// $row = $list_query->fetch_assoc();

	// if (empty($row['abogado_id'])) {
	// 	$result["http_code"] = 400;
	// 	$result["error"] = 'sin_asignar';
	// 	$result["campo_incompleto"] = 'abogado';
	// 	exit(json_encode($result));
	// }

	$path = "/var/www/html/files_bucket/contratos/contratos_firmados/proveedores/";

	if (isset($_FILES['archivo_contrato_proveedor']) && $_FILES['archivo_contrato_proveedor']['error'] === UPLOAD_ERR_OK) {
		if (!is_dir($path)) mkdir($path, 0777, true);

		$filename = $_FILES['archivo_contrato_proveedor']['name'];
		$filenametem = $_FILES['archivo_contrato_proveedor']['tmp_name'];
		$filesize = $_FILES['archivo_contrato_proveedor']['size'];
		$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
		if($filename != ""){
			$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
			$nombre_archivo = $contrato_id . "_CONTRATO_FIRMADO_" . date('YmdHis') . "." . $fileExt;
			move_uploaded_file($filenametem, $path . $nombre_archivo);
			$comando = "INSERT INTO cont_archivos
						(
							contrato_id,
							tipo_archivo_id,
							nombre,
							extension,
							size,
							ruta,
							user_created_id,
							created_at
						)
						VALUES
						(
							'" . $contrato_id . "',
							19,
							'" . $nombre_archivo . "',
							'" . $fileExt . "',
							'" . $filesize . "',
							'" . $path . "',
							'".$login['id']."',
							'" . date('Y-m-d H:i:s') . "'
						)";
			$mysqli->query($comando);
		}
	}

	$query_update = "
	UPDATE cont_contrato
	SET
		etapa_id = '5',
		categoria_id = '".$cont_detalle_proveedor_contrato_firmado_categoria_param."',
		tipo_contrato_proveedor_id = '".$cont_detalle_proveedor_contrato_firmado_tipo_contrato_param."',
		usuario_contrato_proveedor_aprobado_id = '".$login['id']."',
		fecha_inicio = '".$cont_detalle_proveedor_contrato_firmado_fecha_incio_param."',
		fecha_suscripcion_proveedor = '".$cont_detalle_proveedor_contrato_firmado_fecha_suscripcion_param."',
		fecha_vencimiento_proveedor= ".$cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param.",
		tipo_firma_id = '".$cont_detalle_proveedor_contrato_firmado_tipo_firma_param."',
		fecha_vencimiento_indefinida_id = ".$fecha_vencimiento_indefinida_id.",
		renovacion_automatica = ".$cont_detalle_proveedor_renovacion_automatica."
	WHERE contrato_id = '" . $contrato_id . "'
	";
	$mysqli->query($query_update);

	if($mysqli->error)
	{
		$result["http_code"] = 500;
		$result["status"] = "error";
		$result["mensaje"] = $mysqli->error;
		echo json_encode($result);
		die;
	}
	else
	{
		//INICIO REGISTRAR SEGUIMIENTO DE PROCESO CUANDO LA ADENDA HAYA SIDO SUBIDO POR UN USUARIO DE LEGAL
		// $CONTRATO = 1;
		// $query_seguimiento = "SELECT id FROM cont_seguimiento_proceso_legal WHERE tipo_documento_id = ".$CONTRATO." AND proceso_id = ".$contrato_id." AND status IN (1,2)";
		// $list_query_seguimiento = $mysqli->query($query_seguimiento);
		// $cant_seguimientos = $list_query_seguimiento->num_rows;


		// $query_adenda = "SELECT cc.area_responsable_id, cc.tipo_contrato_id , tpa.area_id
		// FROM cont_contrato cc 
		// LEFT JOIN tbl_usuarios tu ON
		// 	tu.id = cc.user_created_id 
		// LEFT JOIN tbl_personal_apt tpa ON
		// 	tpa.id = tu.personal_id
		// WHERE cc.contrato_id  = ".$contrato_id." LIMIT 1";
		// $sel_adenda = $mysqli->query($query_adenda);
		// $data_contrato = $sel_adenda->fetch_assoc();

		// $APROBACION_DEL_DOCUMENTO = 1;
		// $INICIO_DE_PROCESO_LEGAL = 2;
		// $AREA_LEGAL_ID = 33;
		// $CONTRATO = 1;

		// if ($cant_seguimientos == 0 || $data_contrato['area_id'] == $AREA_LEGAL_ID) {
		// 	$seg_proceso = new SeguimientoProceso();
		// 	if ($cant_seguimientos == 0) {
		// 		$data_proceso_reg_ipc['tipo_documento_id'] = $CONTRATO;
		// 		$data_proceso_reg_ipc['proceso_id'] = $contrato_id;
		// 		$data_proceso_reg_ipc['proceso_detalle_id'] = 0;
		// 		$data_proceso_reg_ipc['area_id'] = $AREA_LEGAL_ID;
		// 		$data_proceso_reg_ipc['etapa_id'] = $INICIO_DE_PROCESO_LEGAL;
		// 		$data_proceso_reg_ipc['status'] = 1; //Pendiente
		// 		$data_proceso_reg_ipc['created_at'] = date('Y-m-d H:i:s');
		// 		$data_proceso_reg_ipc['user_created_id'] = $usuario_id;
		// 		$result_reg_conf_user = $seg_proceso->registrar_proceso($data_proceso_reg_ipc);
		// 	}
			
		// 	$data_proceso['tipo_documento_id'] = $CONTRATO; //Adenda
		// 	$data_proceso['proceso_id'] = $contrato_id;
		// 	$data_proceso['proceso_detalle_id'] = 0;
		// 	$data_proceso['usuario_id'] = $usuario_id;
		// 	$resp_proceso = $seg_proceso->fin_seguimiento_proceso_alterno($data_proceso);
		// }
		//FIN REGISTRAR SEGUIMIENTO DE PROCESO CUANDO LA ADENDA HAYA SIDO SUBIDO POR UN USUARIO DE LEGAL
		guardar_comprobante_proveedor($_POST['contrato_id']); // GUARDAR PROVEEDOR EN TBL_COMPROBANTE_PROVEEDOR
		send_email_solicitud_contrato_proveedor_firmada($_POST["contrato_id"] ,$_POST["correos_adjuntos"]);
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
}


if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_contrato_acuerdo_confidencialidad_firmado") {

	$usuario_id = $login?$login['id']:null;

	if ( !((int) $usuario_id > 0) ) {
		$result["http_code"] = 500;
		$result["status"] = "error";
		$result["mensaje"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y hacer click e el boton verde: Agregar contrato firmado.";
		echo json_encode($result);
		die;
	}

	$contrato_id = $_POST["contrato_id"];
	$cont_detalle_proveedor_contrato_firmado_categoria_param = $_POST["cont_detalle_proveedor_contrato_firmado_categoria_param"];
	$cont_detalle_proveedor_contrato_firmado_tipo_contrato_param = $_POST["cont_detalle_proveedor_contrato_firmado_tipo_contrato_param"];
	$cont_detalle_proveedor_contrato_firmado_tipo_firma_param = $_POST["cont_detalle_proveedor_contrato_firmado_tipo_firma_param"];

	$cont_detalle_proveedor_contrato_firmado_fecha_incio_param = $_POST["cont_detalle_proveedor_contrato_firmado_fecha_incio_param"];
	$cont_detalle_proveedor_contrato_firmado_fecha_incio_param = date("Y-m-d", strtotime($cont_detalle_proveedor_contrato_firmado_fecha_incio_param));

	$cont_detalle_proveedor_contrato_firmado_fecha_suscripcion_param = $_POST["cont_detalle_proveedor_contrato_firmado_fecha_suscripcion_param"];
	$cont_detalle_proveedor_contrato_firmado_fecha_suscripcion_param = date("Y-m-d", strtotime($cont_detalle_proveedor_contrato_firmado_fecha_suscripcion_param));

	$fecha_vencimiento_indefinida_id = $_POST["fecha_vencimiento_indefinida_id"];
	$cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param = 'NULL';

	if ($fecha_vencimiento_indefinida_id == 2) {
		$cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param = $_POST["cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param"];
		$cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param = "'" . date("Y-m-d", strtotime($cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param)) . "'";
	}

	// $query = "
	// SELECT
	// 	c.abogado_id
	// FROM
	// 	cont_contrato c
	// WHERE c.contrato_id = $contrato_id
	// ";

	// $list_query = $mysqli->query($query);
	// $row = $list_query->fetch_assoc();

	// if (empty($row['abogado_id'])) {
	// 	$result["http_code"] = 400;
	// 	$result["error"] = 'sin_asignar';
	// 	$result["campo_incompleto"] = 'abogado';
	// 	exit(json_encode($result));
	// }

	$path = "/var/www/html/files_bucket/contratos/contratos_firmados/acuerdos/";

	if (isset($_FILES['archivo_contrato_proveedor']) && $_FILES['archivo_contrato_proveedor']['error'] === UPLOAD_ERR_OK) {
		if (!is_dir($path)) mkdir($path, 0777, true);

		$filename = $_FILES['archivo_contrato_proveedor']['name'];
		$filenametem = $_FILES['archivo_contrato_proveedor']['tmp_name'];
		$filesize = $_FILES['archivo_contrato_proveedor']['size'];
		$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
		if($filename != ""){
			$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
			$nombre_archivo = $contrato_id . "_CONTRATO_FIRMADO_" . date('YmdHis') . "." . $fileExt;
			move_uploaded_file($filenametem, $path . $nombre_archivo);
			$comando = "INSERT INTO cont_archivos
						(
							contrato_id,
							tipo_archivo_id,
							nombre,
							extension,
							size,
							ruta,
							user_created_id,
							created_at
						)
						VALUES
						(
							'" . $contrato_id . "',
							19,
							'" . $nombre_archivo . "',
							'" . $fileExt . "',
							'" . $filesize . "',
							'" . $path . "',
							'".$login['id']."',
							'" . date('Y-m-d H:i:s') . "'
						)";
			$mysqli->query($comando);
		}
	}

	$query_update = "
	UPDATE cont_contrato
	SET
		etapa_id = '5',
		categoria_id = '".$cont_detalle_proveedor_contrato_firmado_categoria_param."',
		tipo_contrato_proveedor_id = '".$cont_detalle_proveedor_contrato_firmado_tipo_contrato_param."',
		usuario_contrato_proveedor_aprobado_id = '".$login['id']."',
		fecha_inicio = '".$cont_detalle_proveedor_contrato_firmado_fecha_incio_param."',
		fecha_suscripcion_proveedor = '".$cont_detalle_proveedor_contrato_firmado_fecha_suscripcion_param."',
		fecha_vencimiento_proveedor= ".$cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param.",
		tipo_firma_id = '".$cont_detalle_proveedor_contrato_firmado_tipo_firma_param."',
		fecha_vencimiento_indefinida_id = ".$fecha_vencimiento_indefinida_id."
	WHERE contrato_id = '" . $contrato_id . "'
	";
	$mysqli->query($query_update);

	if($mysqli->error)
	{
		$result["update_error"] = $mysqli->error;
	}
	else
	{
		send_email_solicitud_acuerdo_confidencialidad_firmada($_POST["contrato_id"]);
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
}

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_contrato_detalleSolicitudProveedor_aprobar_solicitud_gerencia")
{
	$message = "";
	$usuario_id = $login?$login['id']:null;

	if ( !((int) $usuario_id > 0) ) {
		$result["http_code"] = 500;
		$result["status"] = "error";
		$result["mensaje"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y hacer click e el boton verde: Aceptar solicitud.";
		echo json_encode($result);
		die;
	}

	$contrato_id = $_POST["contrato_id"];
	$cont_detalle_proveedor_aprobacion_gerencia_param = $_POST['cont_detalle_proveedor_aprobacion_gerencia_param'];

	$query_update =
		"
			UPDATE cont_contrato
				SET
				aprobacion_gerencia_proveedor = '".$cont_detalle_proveedor_aprobacion_gerencia_param."',
				fecha_atencion_gerencia_proveedor = '" .date("Y-m-d H:i:s")."',
				aprobado_por = $usuario_id
			WHERE contrato_id = '" . $contrato_id . "'
		";

	$mysqli->query($query_update);

	if($mysqli->error)
	{
		$result["status"] = false;
		$result["message"] = $mysqli->error;
	}
	else
	{
	
		$CONTRATO = 1;
		//actualizar seguimiento proceso
		$seg_proceso = new SeguimientoProceso();
		$data_proceso['usuario_id'] = $usuario_id;
		$data_proceso['tipo_documento_id'] = $CONTRATO;
		$data_proceso['proceso_id'] = $contrato_id;
		$data_proceso['proceso_detalle_id'] = 0;
		$data_proceso['estado_aprobacion'] = $cont_detalle_proveedor_aprobacion_gerencia_param;
		$seg_proceso->aprobar_rechazar_seguimiento_proceso($data_proceso);

		$result["status"] = true;
		$result["message"] = "Datos registrados correctamente";
	}
}


if (isset($_POST["accion"]) && $_POST["accion"]==="sec_contrato_detalle_aprobar_solicitud")
{
	$message = "";
	$usuario_id = $login?$login['id']:null;

	if ( !((int) $usuario_id > 0) ) {
		$result["http_code"] = 500;
		$result["status"] = "error";
		$result["mensaje"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y hacer click e el boton verde: Aceptar solicitud.";
		echo json_encode($result);
		die;
	}

	$contrato_id = $_POST["contrato_id"];
	$cont_detalle_aprobacion_param = $_POST['cont_detalle_aprobacion_param'];

	$query_update =
		"
			UPDATE cont_contrato
				SET
				estado_aprobacion = '".$cont_detalle_aprobacion_param."',
				-- etapa_id = 5,
				fecha_aprobacion = '" .date("Y-m-d H:i:s")."',
				aprobado_por = $usuario_id
			WHERE contrato_id = '" . $contrato_id . "'
		";

	$mysqli->query($query_update);

	if($mysqli->error)
	{
		$result["status"] = false;
		$result["message"] = $mysqli->error;
	}
	else
	{
		$result["status"] = true;
		$result["message"] = "Datos registrados correctamente";
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_propietario_existe_detalle_ag") {

	$nombre_o_numdocu = $_POST["nombre_o_numdocu"];


	$sql = "SELECT
	id,
	tipo_persona_id,
	nombre,
	tipo_docu_identidad_id,
	num_docu,
	num_ruc,
	direccion,
	representante_legal,
	num_partida_registral,
	contacto_nombre,
	contacto_telefono,
	contacto_email
	FROM cont_persona WHERE num_docu like '" . $nombre_o_numdocu . "'";


	$query = $mysqli->query($sql);

	$row_count = $query->num_rows;

	if(($row_count) == 0){
		$result["http_code"] = 400;
		$result["result"] = "El DNI no existe.";
	} elseif (($row_count) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $row_count;
	} else {
		$result["http_code"] = 400;
		$result["result"] = "El DNI no existe.";
	}


}


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_propietario_ruc_existe_detalle_ag") {

	$nombre_o_numdocu = $_POST["nombre_o_numdocu"];


	$sql = "SELECT
	id,
	tipo_persona_id,
	nombre,
	tipo_docu_identidad_id,
	num_docu,
	num_ruc,
	direccion,
	representante_legal,
	num_partida_registral,
	contacto_nombre,
	contacto_telefono,
	contacto_email
	FROM cont_persona WHERE num_ruc like '" . $nombre_o_numdocu . "'";


	$query = $mysqli->query($sql);

	$row_count = $query->num_rows;

	if(($row_count) == 0){
		$result["http_code"] = 400;
		$result["result"] = "El RUC no existe.";
	} elseif (($row_count) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $row_count;
	} else {
		$result["http_code"] = 400;
		$result["result"] = "El RUC no existe.";
	}


}

if (isset($_POST["accion"]) && $_POST["accion"]==="registrar_cambio_propietario_agente")
{
	$usuario_id = $login?$login['id']:null;
	$created_at = date('Y-m-d H:i:s');
	$idpersona = $_POST["idpersona"];
	$idcontrato = $_POST['idcontrato'];


	$query_insert_p = "INSERT INTO cont_propietario
		(contrato_id, persona_id, status, user_created_id, created_at) 			VALUES
		('" . $idcontrato . "' , '".$idpersona."', 1, '".$usuario_id."', '".$created_at."') ";



	$mysqli->query($query_insert_p);

	if($mysqli->error)
	{
		$result["status"] = false;
		$result["message"] = $mysqli->error;
	}
	else
	{
		$result["status"] = true;
		$result["message"] = "Datos registrados correctamente";


	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_contrato_detalle_solicitudv2_verificar_giro")
{
	$message = "";

	$contrato_id = $_POST["contrato_id"];
	$cont_detalle_solicitudv2_giro_boton_param = $_POST['cont_detalle_solicitudv2_giro_boton_param'];
	$cont_detalle_solicitudv2_param_texto_motivo_giro = $_POST['cont_detalle_solicitudv2_param_texto_motivo_giro'];

	$parametro_insert = "";

	if($cont_detalle_solicitudv2_giro_boton_param == "1")
	{
		$parametro_insert = $cont_detalle_solicitudv2_giro_boton_param;
	}
	else
	{
		$parametro_insert = $cont_detalle_solicitudv2_param_texto_motivo_giro;
	}

	$query_update =
		"
			UPDATE cont_contrato
				SET
				verificar_giro = '".trim(str_replace("<br>", "", $parametro_insert))."',
				fecha_verificacion_giro = '".date("Y-m-d H:i:s")."',
				usuario_verificacion_giro = '".$login["id"]."'
			WHERE contrato_id = '" . $contrato_id . "'
		";

	$mysqli->query($query_update);

	if($mysqli->error)
	{
		$result["status"] = false;
		$result["message"] = $mysqli->error;
	}
	else
	{
		$result["status"] = true;
		$result["message"] = "Datos registrados correctamente";

		send_email_confirmacion_giro_local($contrato_id);
	}
}

function send_email_confirmacion_giro_local($contrato_id)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$sel_query = $mysqli->query("SELECT
									c.nombre_tienda,
									ce.fecha_suscripcion,
									ce.created_at,
									i.ubicacion,
									ce.fecha_inicio,
									ce.fecha_fin,
									c.user_created_id,
									concat( IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS usuario_solicitante,
									tp.correo,
									c.verificar_giro,
									c.fecha_verificacion_giro,
									c.usuario_verificacion_giro,
								    CONCAT(IFNULL(pgiro.nombre, ''),' ',IFNULL(pgiro.apellido_paterno, ''),' ',IFNULL(pgiro.apellido_materno, '')) AS persona_verificaciongiro,
								    co.sigla AS sigla_correlativo,
									c.codigo_correlativo
								FROM cont_contrato c
									INNER JOIN cont_inmueble i
									ON c.contrato_id = i.contrato_id
									INNER JOIN cont_condicion_economica ce
									ON c.contrato_id = ce.contrato_id AND ce.status = 1
									INNER JOIN tbl_usuarios tu
									ON tu.id = c.user_created_id
									INNER JOIN tbl_personal_apt tp
									ON tp.id = tu.personal_id
									INNER JOIN tbl_usuarios ugiro
									ON c.usuario_verificacion_giro = ugiro.id
									INNER JOIN tbl_personal_apt pgiro
									ON ugiro.personal_id = pgiro.id
									LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
								WHERE c.contrato_id = '".$contrato_id."' LIMIT 1
				");

	$body = "";
	$body .= '<html>';

	$email_user_created = '';
	$asunto = "";
	$asunto = "";

	while($sel = $sel_query->fetch_assoc())
	{
		$sigla_correlativo = $sel['sigla_correlativo'];
		$codigo_correlativo = $sel['codigo_correlativo'];

		$date = date_create($sel["created_at"]);
		$created_at = date_format($date, "Y-m-d");

		$verificar_giro = $sel["verificar_giro"];

		if($verificar_giro == "1")
		{
			$asunto = "Si";
		}
		else
		{
			$asunto = "No";
		}

	$body .= '<div>';
	$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

	$body .= '<thead>';
	$body .= '<tr>';
	$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
	$body .= '<b>Verificación de Giro</b>';
	$body .= '</th>';
	$body .= '</tr>';
	$body .= '</thead>';

	$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Ubicación:</b></td>';
		$body .= '<td>'.$sel["ubicacion"].'</td>';
	$body .= '</tr>';

	$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Fecha solicitud:</b></td>';
		$body .= '<td>'.$created_at.'</td>';
	$body .= '</tr>';

	$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Usuario solicitante:</b></td>';
		$body .= '<td>'.$sel["usuario_solicitante"].'</td>';
	$body .= '</tr>';

	$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Giro:</b></td>';
		$body .= '<td>'.$asunto.'</td>';
	$body .= '</tr>';

	if($verificar_giro != "1")
	{
		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Motivo:</b></td>';
		$body .= '<td>'.$verificar_giro.'</td>';
	$body .= '</tr>';
	}

	$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Confirmado por:</b></td>';
		$body .= '<td>'.$sel["persona_verificaciongiro"].'</td>';
	$body .= '</tr>';

	$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Fecha confirmación:</b></td>';
		$body .= '<td>'.$sel["fecha_verificacion_giro"].'</td>';
	$body .= '</tr>';

	$body .= '</table>';
	$body .= '</div>';

	$email_user_created = $sel["correo"];
	}

	$body .= '<div>';
		$body .= '<br>';
	$body .= '</div>';

	$body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
	    $body .= '<a href="'.$host.'/?sec_id=contrato&sub_sec_id=detalle_solicitudv2&id='.$contrato_id.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
			$body .= '<b>Ver Solicitud</b>';
	    $body .= '</a>';
	$body .= '</div>';
	$body .= '</html>';
	$body .= "";

	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_solicitud_contrato_arrendamiento_firmada([$email_user_created]);

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];

	$request = [
		"subject" => "Gestion - Sistema Contratos - Confirmación de Giro de Arrendamiento: Código - " .$sigla_correlativo.$codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');
		$mail->CharSet = 'UTF-8';

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc)
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc)
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();
		//return true;

	}
	catch (Exception $e)
	{
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}
}

function insertar_nuevo_local_en_tbl_locales($contrato_id)
{
	include("db_connect.php");
	include("sys_login.php");
	
	$usuario_id = $login?$login['id']:null;
	$created_at = date('Y-m-d H:i:s');

	$query = "
	SELECT
		c.contrato_id,
		c.empresa_suscribe_id,
		c.persona_responsable_id,
		c.jefe_comercial_id,
		c.user_created_id,
		c.nombre_tienda,
		i.ubicacion,
		i.ubigeo_id,
		i.latitud,
		i.longitud,
		c.cc_id
	FROM
		cont_contrato c
		INNER JOIN cont_inmueble i ON c.contrato_id = i.contrato_id
	WHERE
		c.contrato_id = $contrato_id
	";

	$list_query = $mysqli->query($query);

	$li = $list_query->fetch_assoc();


	//INGRESAMOS EL LA TABLA LOCALES:
	$insert_contrato_local = "
	INSERT INTO tbl_locales (
		canal_id,
		cc_id,
		red_id,
		tipo_id,
		razon_social_id,
		nombre,
		contrato_id,
		direccion,
		ubigeo_id,
		latitud,
		longitud,
		fecha_registro,
		operativo,
		user_created_id,
		created_at
	) VALUES (
		1,
		'".trim($li["cc_id"])."',
		1,
		15,
		".$li["empresa_suscribe_id"].",
		'".$li["nombre_tienda"]."',
		".$contrato_id.",
		'".$li["ubicacion"]."',
		'".$li["ubigeo_id"]."',
		'".$li["latitud"]."',
		'".$li["longitud"]."',
		'".$created_at."',
		1,
		".$usuario_id.",
		'".$created_at."'
	)";

	$mysqli->query($insert_contrato_local);
	$inserted=$mysqli->insert_id;


	//INGRESAMOS EL LA TABLA tbl_usuarios_locales:

	$insert_contrato_usuarios_locales_supervisor = "
	INSERT INTO tbl_usuarios_locales
	(
		usuario_id,
		local_id,
		estado,
		user_created_id,
		created_at
	)
	VALUES
	(
		'".$li["persona_responsable_id"]."',
		'".$inserted."',
		'1',
		$usuario_id,
		'".$created_at."'
	)";

	$mysqli->query($insert_contrato_usuarios_locales_supervisor);

	$insert_contrato_usuarios_locales_jefe_comercial = "
	INSERT INTO tbl_usuarios_locales
	(
		usuario_id,
		local_id,
		estado,
		user_created_id,
		created_at
	)
	VALUES
	(
		'".$li["jefe_comercial_id"]."',
		'".$inserted."',
		'1',
		$usuario_id,
		'".$created_at."'
	)";

	$mysqli->query($insert_contrato_usuarios_locales_jefe_comercial);


	//INGRESAMOS EL LA TABLA tbl_locales_horarios:

	$insert_contrato_locales_horarios = " INSERT INTO tbl_locales_horarios
					(
						local_id,
						horario_id,
						started_at
					)
					VALUES
					(
						'".$inserted."',
						'1',
						'" .date("Y-m-d")."',
					)";

	$mysqli->query($insert_contrato_locales_horarios);


	//INGRESAMOS EL LA TABLA tbl_local_config:

	$insert_contrato_tbl_local_config = " INSERT INTO tbl_local_config (local_id,config_id,config_param,created_at,updated_at) VALUES 
	('".$inserted."','alerta_gr_turnover','1',NOW(),NOW()),
	('".$inserted."','alerta_terminal_deposit','1',NOW(),NOW()),
	('".$inserted."','alerta_deposito_web','1',NOW(),NOW()),
	('".$inserted."','alerta_simulcast_red_at','1',NOW(),NOW()),
	('".$inserted."','alerta_betshop_retail','1',NOW(),NOW()),
	('".$inserted."','alerta_betshop','1',NOW(),NOW());";
	$mysqli->query($insert_contrato_tbl_local_config);

	
	return   $insert_contrato_local;
}

function send_email_solicitud_contrato_arrendamiento_firmada($contrato_id)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$nombre_tienda = "";
	$sel_query = $mysqli->query("
	SELECT
		c.nombre_tienda,
		c.cc_id AS centro_de_costo,
		i.ubicacion,
		ce.fecha_inicio,
		ce.fecha_fin,
		ce.fecha_suscripcion,
		tp.correo,
		co.sigla AS sigla_correlativo,
		c.codigo_correlativo,
		ce.renovacion_automatica
	FROM
		cont_contrato c
		INNER JOIN cont_inmueble i ON c.contrato_id = i.contrato_id
		INNER JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id AND ce.status = 1
		INNER JOIN tbl_usuarios tu ON tu.id = c.user_created_id
		INNER JOIN tbl_personal_apt tp ON tp.id = tu.personal_id
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
	WHERE
		c.contrato_id = '".$contrato_id."'
	LIMIT 1
	");

	$body = "";
	$body .= '<html>';

	$email_user_created = '';
	$centro_de_costo = '';

	while($sel = $sel_query->fetch_assoc())
	{
		$sigla_correlativo = $sel['sigla_correlativo'];
		$codigo_correlativo = $sel['codigo_correlativo'];

		$renovacion_automatica = $sel['renovacion_automatica'] == 1 ? 'SI':'NO';

		$nombre_tienda = $sel['nombre_tienda'];
		$centro_de_costo = $sel['centro_de_costo'];

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

		$body .= '<thead>';
		$body .= '<tr>';
		$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
		$body .= '<b>Nuevo local</b>';
		$body .= '</th>';
		$body .= '</tr>';
		$body .= '</thead>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Tienda:</b></td>';
			$body .= '<td>'.$sel["nombre_tienda"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Dirección:</b></td>';
			$body .= '<td>'.$sel["ubicacion"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha inicio:</b></td>';
			$body .= '<td>'.$sel["fecha_inicio"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha fin:</b></td>';
			$body .= '<td>'.$sel["fecha_fin"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha suscripcion:</b></td>';
			$body .= '<td>'.$sel["fecha_suscripcion"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Renovación Automática:</b></td>';
			$body .= '<td>'.$renovacion_automatica.'</td>';
		$body .= '</tr>';

		$body .= '</table>';
		$body .= '</div>';

		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$host.'/?sec_id=contrato&sub_sec_id=detalle_solicitudv2&id='.$contrato_id.'" target="_blank">';
		    	$body .= '<button style="background-color: green; color: white; cursor: pointer; width: 50%;">';
					$body .= '<b>Ver Contrato</b>';
		    	$body .= '</button>';
		    $body .= '</a>';
		$body .= '</div>';

		$email_user_created = $sel["correo"];

	}

	$body .= '</html>';
	$body .= "";

	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_solicitud_contrato_arrendamiento_firmada([$email_user_created]);

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];

	$request = [
		"subject" => "Gestion - Sistema Contratos - Contrato Firmado de Arrendamiento - Nuevo Local: ".$nombre_tienda.": Centro de Costo - " .$centro_de_costo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc)
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc)
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();
		//return true;

	}
	catch (Exception $e)
	{
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}
}

function send_email_solicitud_contrato_proveedor_firmada($contrato_id  , $correos_adjuntos = "")
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];
	$empresa_suscribe_id = 0;

	$sel_query = $mysqli->query("
	SELECT
		c.empresa_suscribe_id,
		rs.nombre AS empresa_suscribe,
		c.ruc AS proveedor_ruc,
		c.nombre_comercial AS proveedor_nombre_comercial,
		c.razon_social AS proveedor_razon_social,
		c.detalle_servicio,
		CONCAT(IFNULL(c.periodo_numero,''), ' ', IFNULL(p.nombre,'')) AS periodo,
		concat(IFNULL(per.nombre, ''),' ', IFNULL(per.apellido_paterno, '')) AS usuario_creacion,
		per.correo AS usuario_creacion_correo,
		c.fecha_inicio,
		ar.nombre AS area_creacion,
		co.sigla AS sigla_correlativo,
		c.codigo_correlativo,
		c.renovacion_automatica,
		c.gerente_area_id,
		c.gerente_area_email,
		peg.correo AS email_del_gerente_area,
		puap.correo AS email_del_aprobante,
		pab.correo AS email_del_abogado
	FROM
		cont_contrato c
		LEFT JOIN cont_periodo p ON c.periodo = p.id
		INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
		INNER JOIN tbl_personal_apt per ON u.personal_id = per.id
		INNER JOIN tbl_areas ar ON per.area_id = ar.id
		INNER JOIN tbl_razon_social rs ON c.empresa_suscribe_id = rs.id
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato

		LEFT JOIN tbl_usuarios ug ON c.gerente_area_id = ug.id
		LEFT JOIN tbl_personal_apt peg ON ug.personal_id = peg.id

		LEFT JOIN tbl_usuarios uap ON c.aprobado_por = uap.id
		LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id

		LEFT JOIN tbl_usuarios uab ON c.abogado_id = uab.id
		LEFT JOIN tbl_personal_apt pab ON uab.personal_id = pab.id
	WHERE
		c.contrato_id = $contrato_id
	");

	$body = "";
	$body .= '<html>';

	$usuario_creacion_correo = '';
	$correos_adicionales = [];

	while($sel = $sel_query->fetch_assoc())
	{
		$empresa_suscribe_id = $sel['empresa_suscribe_id'];
		$empresa_suscribe = $sel['empresa_suscribe'];

		$sigla_correlativo = $sel['sigla_correlativo'];
		$codigo_correlativo = $sel['codigo_correlativo'];

		$proveedor_ruc = $sel['proveedor_ruc'];
		$usuario_creacion_correo = $sel['usuario_creacion_correo'];

		$renovacion_automatica = $sel['renovacion_automatica'] == 1 ? 'SI':'NO';

		$date = date_create($sel["fecha_inicio"]);
		$fecha_inicio_contrato = date_format($date, "Y/m/d");

		$gerente_area_id = $sel['gerente_area_id'];
		if (empty($gerente_area_id)) {
			$gerente_area_email = trim($sel["gerente_area_email"]);
			array_push($correos_adicionales, $gerente_area_email); //Correo Responsable de Area
		} else {
			$gerente_area_email = trim($sel["email_del_gerente_area"]);
			array_push($correos_adicionales, $gerente_area_email); //Correo Responsable de Area
		}
		array_push($correos_adicionales, trim($sel['usuario_creacion_correo'])); //Correo Persona de Contacto
		if(!Empty($sel['email_del_aprobante'])){
			array_push($correos_adicionales, trim($sel['email_del_aprobante'])); //Correo del Aprobante
		}
		if(!Empty($sel['email_del_abogado'])){
			array_push($correos_adicionales, trim($sel['email_del_abogado'])); //Correo del Abogado
		}

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

		$body .= '<thead>';
		$body .= '<tr>';
		$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
		$body .= '<b>CONTRATO FIRMADO</b>';
		$body .= '</th>';
		$body .= '</tr>';
		$body .= '</thead>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Empresa Contratante:</b></td>';
			$body .= '<td>'.$sel["empresa_suscribe"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>RUC Proveedor:</b></td>';
			$body .= '<td>'.$sel["proveedor_ruc"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Razon social Proveedor:</b></td>';
			$body .= '<td>'.$sel["proveedor_razon_social"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Nombre Comercial Proveedor:</b></td>';
			$body .= '<td>'.$sel["proveedor_nombre_comercial"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Periodo:</b></td>';
			$body .= '<td>'.$sel["periodo"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Usuario solicitante:</b></td>';
			$body .= '<td>'.$sel["usuario_creacion"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha inicio:</b></td>';
			$body .= '<td>'.$fecha_inicio_contrato.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Renovación Automática:</b></td>';
			$body .= '<td>'.$renovacion_automatica.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Detalle servicio:</b></td>';
			$body .= '<td>'.$sel["detalle_servicio"].'</td>';
		$body .= '</tr>';

		$body .= '</table>';
		$body .= '</div>';

	}
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$host.'/?sec_id=contrato&sub_sec_id=detalleSolicitudProveedor&id='.$contrato_id.'" target="_blank">';
		    	$body .= '<button style="background-color: green; color: white; cursor: pointer; width: 50%;">';
					$body .= '<b>Ver Contrato</b>';
		    	$body .= '</button>';
		    $body .= '</a>';
		$body .= '</div>';

		$body .= '</html>';
		$body .= "";

	
	if ($empresa_suscribe_id != 0) {
		$sql_contador_tesorero = "
		SELECT
			p.correo
		FROM
			cont_usuarios_razones_sociales urs
			INNER JOIN tbl_usuarios u ON urs.user_id = u.id
			INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		WHERE urs.status = 1 AND u.estado = 1
			AND urs.razon_social_id = ".$empresa_suscribe_id."
			AND p.estado = 1  AND u.estado = 1 
		";
		$sel_query = $mysqli->query($sql_contador_tesorero);

		$row_count = $sel_query->num_rows;

		if ($row_count > 0) {
			while($sel = $sel_query->fetch_assoc()){
				if(!Empty($sel['correo'])){
					$email = filter_var($sel['correo'], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($correos_adicionales, $sel['correo']); //Correo del Abogado
					}
				}
				
			}
		}
	}

	if($correos_adjuntos != ""){
		$validate = true;
		$emails = preg_split('[,|;]',$correos_adjuntos);
		foreach($emails as $e){
		    if(preg_match('/^[_ña-z0-9-]+(\.[_ña-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/',trim($e)) == 0){
				$error_msg = "'" . $e . "'" ." no es un Correo Adjunto válido";
		        $result["http_code"] = 500;
				$result["status"] = "error";
				$result["mensaje"] = $error_msg;
				echo json_encode($result);
				die;
		    }
		    else
		    {
				array_push($correos_adicionales, $e); //Correo del Abogado
		    }
		}
	}

	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_solicitud_contrato_proveedor_firmada($correos_adicionales);

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];


	$request = [
		"subject" => "Gestion - Sistema Contratos - Contrato Firmado de Proveedor - RUC: " .$proveedor_ruc." : Código - " .$sigla_correlativo.$codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc)
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc)
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();
		//return true;

	}
	catch (Exception $e)
	{
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}
}

function send_email_solicitud_acuerdo_confidencialidad_firmada($contrato_id  , $correos_adjuntos = "")
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];
	$empresa_suscribe_id = 0;

	$sel_query = $mysqli->query("
	SELECT
		c.empresa_suscribe_id,
		rs.nombre AS empresa_suscribe,
		c.ruc AS proveedor_ruc,
		c.nombre_comercial AS proveedor_nombre_comercial,
		c.razon_social AS proveedor_razon_social,
		c.detalle_servicio,
		CONCAT(IFNULL(c.periodo_numero,''), ' ', IFNULL(p.nombre,'')) AS periodo,
		concat(IFNULL(per.nombre, ''),' ', IFNULL(per.apellido_paterno, '')) AS usuario_creacion,
		per.correo AS usuario_creacion_correo,
		c.fecha_inicio,
		ar.nombre AS area_creacion,
		co.sigla AS sigla_correlativo,
		c.codigo_correlativo,
		c.renovacion_automatica,
		c.gerente_area_id,
		c.gerente_area_email,
		peg.correo AS email_del_gerente_area,
		puap.correo AS email_del_aprobante,
		pab.correo AS email_del_abogado
		
	FROM
		cont_contrato c
		LEFT JOIN cont_periodo p ON c.periodo = p.id
		INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
		INNER JOIN tbl_personal_apt per ON u.personal_id = per.id
		INNER JOIN tbl_areas ar ON per.area_id = ar.id
		INNER JOIN tbl_razon_social rs ON c.empresa_suscribe_id = rs.id
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato

		LEFT JOIN tbl_usuarios ug ON c.gerente_area_id = ug.id
		LEFT JOIN tbl_personal_apt peg ON ug.personal_id = peg.id

		LEFT JOIN tbl_usuarios uap ON c.aprobado_por = uap.id
		LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id

		LEFT JOIN tbl_usuarios uab ON c.abogado_id = uab.id
		LEFT JOIN tbl_personal_apt pab ON uab.personal_id = pab.id
	WHERE
		c.contrato_id = $contrato_id
	");

	$body = "";
	$body .= '<html>';

	$usuario_creacion_correo = '';
	$correos_adicionales = [];

	while($sel = $sel_query->fetch_assoc())
	{
		$empresa_suscribe_id = $sel['empresa_suscribe_id'];
		$empresa_suscribe = $sel['empresa_suscribe'];

		$sigla_correlativo = $sel['sigla_correlativo'];
		$codigo_correlativo = $sel['codigo_correlativo'];

		$proveedor_ruc = $sel['proveedor_ruc'];
		$usuario_creacion_correo = $sel['usuario_creacion_correo'];

		$renovacion_automatica = $sel['renovacion_automatica'] == 1 ? 'SI':'NO';

		$date = date_create($sel["fecha_inicio"]);
		$fecha_inicio_contrato = date_format($date, "Y/m/d");

		$gerente_area_id = $sel['gerente_area_id'];
		if (empty($gerente_area_id)) {
			$gerente_area_email = trim($sel["gerente_area_email"]);
			array_push($correos_adicionales, $gerente_area_email); //Correo Responsable de Area
		} else {
			$gerente_area_email = trim($sel["email_del_gerente_area"]);
			array_push($correos_adicionales, $gerente_area_email); //Correo Responsable de Area
		}
		array_push($correos_adicionales, trim($sel['usuario_creacion_correo'])); //Correo Persona de Contacto
		if(!Empty($sel['email_del_aprobante'])){
			array_push($correos_adicionales, trim($sel['email_del_aprobante'])); //Correo del Aprobante
		}
		if(!Empty($sel['email_del_abogado'])){
			array_push($correos_adicionales, trim($sel['email_del_abogado'])); //Correo del Aprobante
		}

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

		$body .= '<thead>';
		$body .= '<tr>';
		$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
		$body .= '<b>CONTRATO FIRMADO</b>';
		$body .= '</th>';
		$body .= '</tr>';
		$body .= '</thead>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Empresa Contratante:</b></td>';
			$body .= '<td>'.$sel["empresa_suscribe"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>RUC Proveedor:</b></td>';
			$body .= '<td>'.$sel["proveedor_ruc"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Razon social Proveedor:</b></td>';
			$body .= '<td>'.$sel["proveedor_razon_social"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Nombre Comercial Proveedor:</b></td>';
			$body .= '<td>'.$sel["proveedor_nombre_comercial"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Periodo:</b></td>';
			$body .= '<td>'.$sel["periodo"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Usuario solicitante:</b></td>';
			$body .= '<td>'.$sel["usuario_creacion"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha inicio:</b></td>';
			$body .= '<td>'.$fecha_inicio_contrato.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Renovación Automática:</b></td>';
			$body .= '<td>'.$renovacion_automatica.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Detalle servicio:</b></td>';
			$body .= '<td>'.$sel["detalle_servicio"].'</td>';
		$body .= '</tr>';

		$body .= '</table>';
		$body .= '</div>';

	}
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$host.'/?sec_id=contrato&sub_sec_id=detalleSolicitudProveedor&id='.$contrato_id.'" target="_blank">';
		    	$body .= '<button style="background-color: green; color: white; cursor: pointer; width: 50%;">';
					$body .= '<b>Ver Contrato</b>';
		    	$body .= '</button>';
		    $body .= '</a>';
		$body .= '</div>';

		$body .= '</html>';
		$body .= "";

	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_solicitud_contrato_proveedor_firmada($correos_adicionales);

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];

	if ($empresa_suscribe_id != 0) {
		$sql_contador_tesorero = "
		SELECT
			p.correo
		FROM
			cont_usuarios_razones_sociales urs
			INNER JOIN tbl_usuarios u ON urs.user_id = u.id
			INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		WHERE urs.status = 1 AND u.estado = 1
			AND urs.razon_social_id = ".$empresa_suscribe_id."
			AND p.estado = 1  AND u.estado = 1 
		";
		$sel_query = $mysqli->query($sql_contador_tesorero);

		$row_count = $sel_query->num_rows;

		if ($row_count > 0) {
			while($sel = $sel_query->fetch_assoc()){
				if(!Empty($sel['correo'])){
					$email = filter_var($sel['correo'], FILTER_SANITIZE_EMAIL);
					if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
						array_push($cc, $sel['correo']);
					}
				}
				
			}
		}
	}

	if($correos_adjuntos != ""){
		$validate = true;
		$emails = preg_split('[,|;]',$correos_adjuntos);
		foreach($emails as $e){
		    if(preg_match('/^[_ña-z0-9-]+(\.[_ña-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/',trim($e)) == 0){
				$error_msg = "'" . $e . "'" ." no es un Correo Adjunto válido";
		        $result["http_code"] = 500;
				$result["status"] = "error";
				$result["mensaje"] = $error_msg;
				echo json_encode($result);
				die;
		    }
		    else
		    {
				$cc[] = $e;
		    }
		}
	}

	$request = [
		"subject" => "Gestion - Sistema Contratos - Contrato Firmado de Acuerdo de Confidencialidad - RUC: " .$proveedor_ruc." : Código - " .$sigla_correlativo.$codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc)
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc)
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();
		//return true;

	}
	catch (Exception $e)
	{
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}
}

function registrarFileConcar_Sispag($contrato_id)
{
	include("db_connect.php");
	include("sys_login.php");

	$error = '';

	$num_fecha_corte = 18;

	// $fecha_actual = date("Y-m-d");

	$fecha_registro_contrato = "";

	$obtener_dia_fecha_registro_contrato = "";

	$obtener_mes_fecha_registro_contrato = "";

	$obtener_anio_fecha_registro_contrato = "";

	$fecha_dia_guardar = "";

	$fecha_mes_guardar = "";

	$fecha_anio_guardar = "";

	$fecha_guardar = "";

	//$fecha_anio = date("Y");
	$fecha_mes_calcular_ultimo_dia = date("m");

	// $num_incremento_mes = 1;
	// $fecha_mes = date("m", strtotime("+".$num_incremento_mes." month"));

	// $fecha_dia = date("d");

	$fecha_anio_actual = date("Y");
	$fecha_mes_actual = date("m");
	//$fecha_dia_actual = date("d");

	//$fecha_dia_actual = date('d', mktime(0,0,0, $fecha_mes_calcular_ultimo_dia + 1, 0, $fecha_anio));


	// INICIO OBTENER ADELANTOS
	$array_adelantos = [];
	$query = $mysqli->query("
	SELECT
	num_periodo
	FROM cont_adelantos
	WHERE contrato_id = " . $contrato_id . "
	AND status = 1");
	$row_count = $query->num_rows;
	if ($row_count > 0) {
		$cont_incremento = 0;
		while($row = $query->fetch_assoc()){
			$array_adelantos[] = $row["num_periodo"];
		}
	}
	$num_adelantos = count($array_adelantos);

	if($mysqli->error){
		$error .= $mysqli->error;
	}
	// FIN OBTENER ADELANTOS


	// INICIO OBTENER INCREMENTOS
	$array_incrementos = [];
	$query = $mysqli->query("
	SELECT
	valor,
	tipo_valor_id,
	tipo_continuidad_id,
	a_partir_del_año
	FROM cont_incrementos
	WHERE contrato_id = " . $contrato_id . "
	AND estado = 1");
	$row_count = $query->num_rows;
	if ($row_count > 0) {
		$cont_incremento = 0;
		while($row = $query->fetch_assoc()){
			$array_incrementos[$cont_incremento][0] = $row["valor"];
			$array_incrementos[$cont_incremento][1] = $row["tipo_valor_id"];
			$array_incrementos[$cont_incremento][2] = $row["tipo_continuidad_id"];
			$array_incrementos[$cont_incremento][3] = $row["a_partir_del_año"];
			$cont_incremento++;
		}
	}
	$num_incrementos = count($array_incrementos);
	if($mysqli->error){
		$error .= $mysqli->error;
	}
	// FIN OBTENER INCREMENTOS

	// INICIO INICIALIZACION DE VARIABLES
	$user_id = $login?$login['id']:null;
	$created_at = date('Y-m-d H:i:s');

	$num_dias_excedentes = 0;
	$incrementos = 0;
	$descuento = 0;
	// FIN INICIALIZACION DE VARIABLES

	$query = "SELECT
				c.contrato_id,
				c.nombre_tienda,
				ce.condicion_economica_id,
				ce.cant_meses_contrato,
				ce.impuesto_a_la_renta_id,
				ce.monto_renta,
				ce.fecha_inicio AS fecha_inicio_contrato,
				ce.periodo_gracia_id,
				ce.periodo_gracia_inicio,
				ce.periodo_gracia_fin
			 FROM cont_contrato c
				 INNER JOIN cont_condicion_economica ce
				 ON c.contrato_id = ce.contrato_id
			 WHERE c.contrato_id = '".$contrato_id."' AND c.status = 1 AND ce.status = 1 ";

	$list_query = $mysqli->query($query);

	if($mysqli->error){
		$error .= $mysqli->error . $query;
	}

	$li = $list_query->fetch_assoc();

	$cant_meses_contrato = $li["cant_meses_contrato"];

	$igv_paga_empresa = $li["impuesto_a_la_renta_id"];

	$monto_renta = $li["monto_renta"];

	$fecha_inicio_contrato = $li["fecha_inicio_contrato"];

	$periodo_gracia = $li["periodo_gracia_id"];

	$periodo_gracia_inicio = $li["periodo_gracia_inicio"];

	$periodo_gracia_fin = $li["periodo_gracia_fin"];

	//$cant_meses_contrato = 5;

	if ($periodo_gracia == "1")
	{
		$fecha_registro_contrato = date("d-m-Y", strtotime($periodo_gracia_fin."+ 1 days"));

	}
	else
	{
		$fecha_registro_contrato = $fecha_inicio_contrato;
	}

	$fecha_guardar = $fecha_registro_contrato;

	//echo $fecha_guardar;
	//$fecha_dia_actual = date('d', mktime(0,0,0, $fecha_mes_calcular_ultimo_dia + 1, 0, $fecha_anio));

	// EL strtotime ES PARA CONVERTIR A ENTERO, Y PODER OBTENER EL MES, DIA, ANIO.
	if(date("d", strtotime($fecha_guardar)) > $num_fecha_corte)
	{
		$fecha_guardar = date('d-m-Y', mktime(0,0,0, date("m", strtotime($fecha_guardar)), 1, date("Y", strtotime($fecha_guardar))));

		$fecha_guardar = date("d-m-Y", strtotime($fecha_guardar."+ 1 month"));
	}

	//echo $fecha_guardar;

	$fecha_guardar = date('d-m-Y', mktime(0,0,0, date("m", strtotime($fecha_guardar)), 1, date("Y", strtotime($fecha_guardar))));

	//echo 'resetear a primero de cada mes: '.$fecha_guardar;

	for ($i=1; $i <= $cant_meses_contrato; $i++)
	{
		$descuento = 0;

		//INICIO INCREMENTOS

		$contador_incremento = 0;

		for ($cant_incremento = 0; $cant_incremento < $num_incrementos; $cant_incremento++)
		{
			$valor = $array_incrementos[$cant_incremento][0];	// es el numero ingresdo
			$tipo_valor = $array_incrementos[$cant_incremento][1];	// porcentaje / soles o dolares
			$tipo_continuidad =  $array_incrementos[$cant_incremento][2];	// (2) cada año / (1) solo una vez
			$a_partir_del_anio_en_meses = ($array_incrementos[$cant_incremento][3] * 12) + 1;	// Primer año, segundo año, etc

			if($tipo_continuidad == 1)
			{
				if ($i == ($a_partir_del_anio_en_meses))
				{
					if ($contador_incremento == 0)
					{
						$monto_renta = $monto_renta + $incrementos;
						$incrementos = 0;
						$contador_incremento++;
					}

					if ($tipo_valor == 1)
					{
						$incrementos += $valor;
					}
					else if ($tipo_valor == 2)
					{
						$incrementos += ($monto_renta * $valor)/100;
					}
				}
			}
			elseif($tipo_continuidad == 2)
			{
				for ($j=$a_partir_del_anio_en_meses; $j <= $i; $j+=12)
				{
					if($i == $j)
					{
						if ($contador_incremento == 0)
						{
							$monto_renta = $monto_renta + $incrementos;
							$incrementos = 0;
							$contador_incremento++;
						}

						if ($tipo_valor == 1)
						{
							$incrementos += $valor;
						}
						else if ($tipo_valor == 2)
						{
							$incrementos += ($monto_renta * $valor)/100;
						}
					}
				}
			}
		}

		// FIN INCREMENTOS

		// INICIO DESCUENTO (ADELANTO)
		foreach ($array_adelantos as $value)
		{
			if($i == $value)
			{
				$descuento = $monto_renta;
			}
		}
		// FIN DESCUENTO (ADELANTO)

		$monto_total_renta = ($monto_renta + $incrementos) - $descuento;


		//EN EL IMPUESTO A LA RENTA SE INGRESA EL RUC DEL BENEFICIARIOS

		if ($igv_paga_empresa == 2)
		{
			$monto_registro_igv = ($monto_total_renta * 5) / 100;

			$monto_total = $monto_total_renta + $monto_registro_igv;

			//INGRESAR EL DEBER DEL IMPUESTO A LA RENTA (OSEA EL REGISTRO DEL PORCENTAJE DEL 5%)
			//EN EL DEBER SE INGRESA EL RUC DEL ARRENDADOR

			$query_benefi_impuesto = " SELECT
											cb.contrato_id,
											cb.num_docu,
											-- cb.persona_id,
											-- cp.num_docu,
											trs.codigo_empresa
										FROM cont_beneficiarios cb
											-- INNER JOIN cont_persona cp
											-- ON cb.persona_id = cp.id
											INNER JOIN cont_contrato cc
											ON cb.contrato_id = cc.contrato_id
											INNER JOIN tbl_razon_social trs
											ON cc.empresa_suscribe_id = trs.id
										WHERE cb.contrato_id = '".$contrato_id."' ";

			$list_query_benefi_impuesto = $mysqli->query($query_benefi_impuesto);

			if($mysqli->error){
				$error .= $mysqli->error . $query_benefi_impuesto;
			}

			//$beneficiario_ruc = $list_query_beneficiario->fetch_assoc();

			$cant_reg_beneficiario_impuesto = mysqli_num_rows($list_query_benefi_impuesto);

			$monto_registro_entre_igv_beneficiarios = ($monto_registro_igv / $cant_reg_beneficiario_impuesto);

			foreach($list_query_benefi_impuesto as $benefi_impuesto)
			{
				$insert_impuesto_renta = "INSERT INTO cont_file_concar
							(
								id_condicion_economica,
								num_cuota,
								sub_diario,
								num_comprobante,
								flag_conversion_moneda,
								cuenta_contable,
								codigo_anexo,
								debe_haber,
								importe_original,
								tipo_documento,
								num_documento,

								fecha_documento,
								fecha_vencimiento,

								codigo_anexo_auxiliar,
								registro_mes,
								status,
								user_created_id,
								created_at,
								user_updated_id,
								updated_at
							)
							VALUES
							(
								'".$li["condicion_economica_id"]."',
								'".$i."',
								'".$benefi_impuesto["codigo_empresa"]."',
								'".date("m", strtotime($fecha_guardar))."',
								'S',
								641909,
								'".$benefi_impuesto["num_docu"]."',
								'D',
								" . round($monto_registro_entre_igv_beneficiarios, 2) . ",
								'RA',
								'1683-".date("m", strtotime($fecha_guardar)).date("Y", strtotime($fecha_guardar))."',

								'".date('Y-m-d', mktime(0,0,0, date("m", strtotime($fecha_guardar."+1 month")), 0, date("Y", strtotime($fecha_guardar))))."',
								'".date('Y-m-d', mktime(0,0,0, date("m", strtotime($fecha_guardar."+1 month")), 0, date("Y", strtotime($fecha_guardar))))."',

								'01',
								'".date('Y-m-d', strtotime($fecha_guardar))."',
								1,
								'" .$login["id"]."',
								'" .date("Y-m-d H:i:s")."',
								'" .$login["id"]."',
								'" .date("Y-m-d H:i:s")."'
							)";
				$mysqli->query($insert_impuesto_renta);

				if($mysqli->error){
					$error .= $mysqli->error . $insert_impuesto_renta;
				}

			}

			//EN EL DEBER SE INGRESA EL RUC DEL ARRENDADOR (PROPIETARIOS DEL LOCAL)

			$query_arrendadores = "SELECT
										cp.contrato_id,
										cp.persona_id,
										cpe.num_docu,
										trs.codigo_empresa
									FROM cont_propietario cp
									INNER JOIN cont_persona cpe
									ON cp.persona_id = cpe.id
									INNER JOIN cont_contrato cc
									ON cp.contrato_id = cc.contrato_id
									INNER JOIN tbl_razon_social trs
									ON cc.empresa_suscribe_id = trs.id
									WHERE cp.contrato_id = '".$contrato_id."' ";

			$list_query_arrendador = $mysqli->query($query_arrendadores);

			if($mysqli->error){
				$error .= $mysqli->error . $query_arrendadores;
			}

			//$arrendador_ruc = $list_query_arrendador->fetch_assoc();

			foreach($list_query_arrendador as $arren)
			{
				$insert_deber = "INSERT INTO cont_file_concar
							(
								id_condicion_economica,
								num_cuota,
								sub_diario,
								num_comprobante,
								flag_conversion_moneda,
								cuenta_contable,
								codigo_anexo,
								debe_haber,
								importe_original,
								tipo_documento,
								num_documento,

								fecha_documento,
								fecha_vencimiento,

								codigo_anexo_auxiliar,
								registro_mes,
								status,
								user_created_id,
								created_at,
								user_updated_id,
								updated_at
							)
							VALUES
							(
								'".$li["condicion_economica_id"]."',
								'".$i."',
								'".$arren["codigo_empresa"]."',
								'".date("m", strtotime($fecha_guardar))."',
								'S',
								635206,
								'".$arren["num_docu"]."',
								'D',
								" . round($monto_total_renta, 2) . ",
								'RA',
								'1683-".date("m", strtotime($fecha_guardar)).date("Y", strtotime($fecha_guardar))."',

								'".date('Y-m-d', mktime(0,0,0, date("m", strtotime($fecha_guardar."+1 month")), 0, date("Y", strtotime($fecha_guardar))))."',
								'".date('Y-m-d', mktime(0,0,0, date("m", strtotime($fecha_guardar."+1 month")), 0, date("Y", strtotime($fecha_guardar))))."',

								'01',
								'".date('Y-m-d', strtotime($fecha_guardar))."',
								1,
								'" .$login["id"]."',
								'" .date("Y-m-d H:i:s")."',
								'" .$login["id"]."',
								'" .date("Y-m-d H:i:s")."'
							)";
				$mysqli->query($insert_deber);

				if($mysqli->error){
					$error .= $mysqli->error . $insert_deber;
				}
			}

			//EN EL HABER SE INGRESA EL RUC DEL BENEFICIARIOS

			$query_beneficiario = " SELECT
										cb.contrato_id,
										cb.num_docu,
										cb.tipo_monto_id,
										cb.monto,
										trs.codigo_empresa
									FROM cont_beneficiarios cb
									-- INNER JOIN cont_persona cp
									-- ON cb.persona_id = cp.id
									INNER JOIN cont_contrato cc
									ON cb.contrato_id = cc.contrato_id
									INNER JOIN tbl_razon_social trs
									ON cc.empresa_suscribe_id = trs.id
									WHERE cb.contrato_id = '".$contrato_id."' ";

			$list_query_beneficiario = $mysqli->query($query_beneficiario);

			if($mysqli->error){
				$error .= $mysqli->error . $query_beneficiario;
			}

			//$beneficiario_ruc = $list_query_beneficiario->fetch_assoc();

			$cant_reg_beneficiario = mysqli_num_rows($list_query_beneficiario);

			$monto_total_beneficiario = $monto_total_renta; //+ $monto_registro_entre_igv_beneficiarios;

			foreach($list_query_beneficiario as $benefi)
			{
				if($cant_reg_beneficiario > 1)
				{
					// CUANDO LOS BENEFICIARIOS SE LES PAGA EL MONTO DE RENTA TOTAL
					// EN ESTE CASO NO SERIA DABLE, PORQUE A CADA BENEFICIARIO SE LE TENDRIA QUE PAGAR EL MONTO TOTAL DE LA RENTA
					// (EJEMPLO: Benefi #1: S/1000(TOTAL RENTA), Benefi #2: S/1000(TOTAL RENTA), total monto = S/2000,
					// SUPERA A LA RENTA MENSUAL)
					if($benefi["tipo_monto_id"] == 3)
					{
						$monto_total_beneficiario = $monto_total_renta + ($monto_registro_igv / $cant_reg_beneficiario);
					}
					// CUANDO LOS BENEFICIARIOS SE LES PAGA EL PORCENTAJE DE LA RENTA
					// (EJEMPLO: Benefi #1: 30%, Benefi #2: 70%, total monto = 100%)
					else if($benefi["tipo_monto_id"] == 2)
					{
						$monto_total_beneficiario = ($monto_total_renta * $benefi["monto"] / 100) + ($monto_registro_igv / $cant_reg_beneficiario);
					}
					// CUANDO LOS BENEFICIARIOS SE LES PAGA MONTO FIJO DE LA RENTA
					// (EJEMPLO: Benefi #1: S/300, Benefi #2: S/700, total monto = S/1000)
					else if($benefi["tipo_monto_id"] == 1)
					{
						$monto_total_beneficiario = $benefi["monto"];
					}

					//$monto_total_beneficiario += $monto_registro_entre_igv_beneficiarios;
				}
				else
				{
					$monto_total_beneficiario += $monto_registro_igv;
				}

				$insert_haber = "INSERT INTO cont_file_concar
							(
								id_condicion_economica,
								num_cuota,
								sub_diario,
								num_comprobante,
								flag_conversion_moneda,
								cuenta_contable,
								codigo_anexo,
								debe_haber,
								importe_original,
								tipo_documento,
								num_documento,

								fecha_documento,
								fecha_vencimiento,

								codigo_anexo_auxiliar,
								registro_mes,
								status,
								user_created_id,
								created_at,
								user_updated_id,
								updated_at
							)
							VALUES
							(
								'".$li["condicion_economica_id"]."',
								'".$i."',
								'".$benefi["codigo_empresa"]."',
								'".date("m", strtotime($fecha_guardar))."',
								'S',
								421201,
								'".$benefi["num_docu"]."',
								'H',
								" . round($monto_total_beneficiario, 2) . ",
								'RA',
								'1683-".date("m", strtotime($fecha_guardar)).date("Y", strtotime($fecha_guardar))."',

								'".date('Y-m-d', mktime(0,0,0, date("m", strtotime($fecha_guardar."+1 month")), 0, date("Y", strtotime($fecha_guardar))))."',
								'".date('Y-m-d', mktime(0,0,0, date("m", strtotime($fecha_guardar."+1 month")), 0, date("Y", strtotime($fecha_guardar))))."',

								'01',
								'".date('Y-m-d', strtotime($fecha_guardar))."',
								1,
								'" .$login["id"]."',
								'" .date("Y-m-d H:i:s")."',
								'" .$login["id"]."',
								'" .date("Y-m-d H:i:s")."'
							)";
				$mysqli->query($insert_haber);

				if($mysqli->error){
					$error .= $mysqli->error . $insert_haber;
				}

				$insert_haber_sispag = "INSERT INTO cont_file_sispag
										(
											id_condicion_economica,
											num_cuota,
											cp_cvanexo,
											cp_ccodigo,
											cp_ctipdoc,
											cp_cnum_doc,

											cp_cfecdoc,
											cp_cfecven,
											cp_cfec_rec,
											cp_cfeccom,

											cp_csubdia,
											cp_ccompro,
											cp_cdebhab,

											cp_nimpomn,

											cp_nigvmn,
											cp_nigvus,
											cp_nimp2mn,
											cp_nimp2us,
											cp_nimpaju,

											cp_ccuenta,

											cp_dfeccre,
											cp_dfecmod,

											cp_cuser,
											cp_ninafec,

											cp_dfecdoc,
											cp_dfecven,
											cp_dfecrec,
											cp_dfeccom,

											cp_cimagen,

											cp_ctipo,

											cp_nporre,

											registro_mes,
											status,
											user_created_id,
											created_at,
											user_updated_id,
											updated_at
										)
										VALUES
										(
											'".$li["condicion_economica_id"]."',
											'".$i."',
											'P',
											'".$benefi["num_docu"]."',
											'FT',
											'1683-".date("m", strtotime($fecha_guardar)).date("Y", strtotime($fecha_guardar))."',

											'".date('Y-m-d', strtotime($fecha_guardar))."',
											'".date('Y-m-d', strtotime($fecha_guardar))."',
											'".date('Y-m-d', strtotime($fecha_guardar))."',
											'".date('Y-m-d', strtotime($fecha_guardar))."',

											'".$benefi["codigo_empresa"]."',
											'".date("m", strtotime($fecha_guardar))."',
											'H',

											" . round($monto_total_beneficiario, 2) . ",

											'0',
											'0',
											'0',
											'0',
											'0',

											'421201',

											'".date('Y-m-d', strtotime($fecha_guardar))."',
											'".date('Y-m-d', strtotime($fecha_guardar))."',

											'SUSY',
											'0',

											'".date('Y-m-d', strtotime($fecha_guardar))."',
											'".date('Y-m-d', strtotime($fecha_guardar))."',
											'".date('Y-m-d', strtotime($fecha_guardar))."',
											'".date('Y-m-d', strtotime($fecha_guardar))."',

											'0',

											'V',

											'0',

											'".date('Y-m-d', strtotime($fecha_guardar))."',
											1,
											'" .$login["id"]."',
											'" .date("Y-m-d H:i:s")."',
											'" .$login["id"]."',
											'" .date("Y-m-d H:i:s")."'
										)";
				$mysqli->query($insert_haber_sispag);

				if($mysqli->error){
					$error .= $mysqli->error . $insert_haber_sispag;
				}

			}

		}
		else
		{
			//INGRESAR EL DEBER
			//EN EL DEBER SE INGRESA EL RUC DEL ARRENDADOR
			$query_arrendadores = "SELECT
										cp.contrato_id,
										cp.persona_id,
										cpe.num_docu,
										trs.codigo_empresa
									FROM cont_propietario cp
									INNER JOIN cont_persona cpe
									ON cp.persona_id = cpe.id
									INNER JOIN cont_contrato cc
									ON cp.contrato_id = cc.contrato_id
									INNER JOIN tbl_razon_social trs
									ON cc.empresa_suscribe_id = trs.id
									WHERE cp.contrato_id = '".$contrato_id."' ";

			$list_query_arrendador = $mysqli->query($query_arrendadores);

			if($mysqli->error){
				$error .= $mysqli->error . $query_arrendadores;
			}

			//$arrendador_ruc = $list_query_arrendador->fetch_assoc();

			foreach($list_query_arrendador as $arren)
			{
				$insert_deber = "INSERT INTO cont_file_concar
							(
								id_condicion_economica,
								num_cuota,
								sub_diario,
								num_comprobante,
								flag_conversion_moneda,
								cuenta_contable,
								codigo_anexo,
								debe_haber,
								importe_original,
								tipo_documento,
								num_documento,
								fecha_documento,
								fecha_vencimiento,
								codigo_anexo_auxiliar,
								registro_mes,
								status,
								user_created_id,
								created_at,
								user_updated_id,
								updated_at
							)
							VALUES
							(
								'".$li["condicion_economica_id"]."',
								'".$i."',
								'".$arren["codigo_empresa"]."',
								'".date("m", strtotime($fecha_guardar))."',
								'S',
								635206,
								'".$arren["num_docu"]."',
								'D',
								" . round($monto_total_renta, 2) . ",
								'RA',
								'1683-".date("m", strtotime($fecha_guardar)).date("Y", strtotime($fecha_guardar))."',

								'".date('Y-m-d', mktime(0,0,0, date("m", strtotime($fecha_guardar."+1 month")), 0, date("Y", strtotime($fecha_guardar))))."',
								'".date('Y-m-d', mktime(0,0,0, date("m", strtotime($fecha_guardar."+1 month")), 0, date("Y", strtotime($fecha_guardar))))."',

								'01',
								'".date('Y-m-d', strtotime($fecha_guardar))."',
								1,
								'" .$login["id"]."',
								'" .date("Y-m-d H:i:s")."',
								'" .$login["id"]."',
								'" .date("Y-m-d H:i:s")."'
							)";
				$mysqli->query($insert_deber);

				if($mysqli->error){
					$error .= $mysqli->error . $insert_deber;
				}
			}


			//INGRESAR EL HABER
			//EN EL HABER SE INGRESA EL RUC DEL BENEFICIARIOS

			$query_beneficiario = " SELECT
										cb.contrato_id,
										cb.num_docu,
										cb.tipo_monto_id,
										cb.monto,
										trs.codigo_empresa
									FROM cont_beneficiarios cb
										-- INNER JOIN cont_persona cp
										-- ON cb.persona_id = cp.id
										INNER JOIN cont_contrato cc
										ON cb.contrato_id = cc.contrato_id
										INNER JOIN tbl_razon_social trs
										ON cc.empresa_suscribe_id = trs.id
									WHERE cb.contrato_id = '".$contrato_id."' ";

			$list_query_beneficiario = $mysqli->query($query_beneficiario);

			if($mysqli->error){
				$error .= $mysqli->error . $query_beneficiario;
			}

			$cant_reg_beneficiario = mysqli_num_rows($list_query_beneficiario);

			$monto_total_beneficiario = $monto_total_renta;

			foreach($list_query_beneficiario as $benefi)
			{
				if($cant_reg_beneficiario > 1)
				{
					// CUANDO LOS BENEFICIARIOS SE LES PAGA EL MONTO DE RENTA TOTAL
					// EN ESTE CASO NO SERIA DABLE, PORQUE A CADA BENEFICIARIO SE LE TENDRIA QUE PAGAR EL MONTO TOTAL DE LA RENTA
					// (EJEMPLO: Benefi #1: S/1000(TOTAL RENTA), Benefi #2: S/1000(TOTAL RENTA), total monto = S/2000,
					// SUPERA A LA RENTA MENSUAL)
					if($benefi["tipo_monto_id"] == 3)
					{
						$monto_total_beneficiario = ($monto_total_renta / $cant_reg_beneficiario);
					}
					// CUANDO LOS BENEFICIARIOS SE LES PAGA EL PORCENTAJE DE LA RENTA
					// (EJEMPLO: Benefi #1: 30%, Benefi #2: 70%, total monto = 100%)
					else if($benefi["tipo_monto_id"] == 2)
					{
						$monto_total_beneficiario = ($monto_total_renta * $benefi["monto"]) / 100;
					}
					// CUANDO LOS BENEFICIARIOS SE LES PAGA MONTO FIJO DE LA RENTA
					// (EJEMPLO: Benefi #1: S/300, Benefi #2: S/700, total monto = S/1000)
					else if($benefi["tipo_monto_id"] == 1)
					{
						$monto_total_beneficiario = $benefi["monto"];
					}
				}

				$insert_haber = "INSERT INTO cont_file_concar
							(
								id_condicion_economica,
								num_cuota,
								sub_diario,
								num_comprobante,
								flag_conversion_moneda,
								cuenta_contable,
								codigo_anexo,
								debe_haber,
								importe_original,
								tipo_documento,
								num_documento,
								fecha_documento,
								fecha_vencimiento,
								codigo_anexo_auxiliar,
								registro_mes,
								status,
								user_created_id,
								created_at,
								user_updated_id,
								updated_at
							)
							VALUES
							(
								'".$li["condicion_economica_id"]."',
								'".$i."',
								'".$benefi["codigo_empresa"]."',
								'".date("m", strtotime($fecha_guardar))."',
								'S',
								421201,
								'".$benefi["num_docu"]."',
								'H',
								" . round($monto_total_beneficiario, 2) . ",
								'RA',
								'1683-".date("m", strtotime($fecha_guardar)).date("Y", strtotime($fecha_guardar))."',

								'".date('Y-m-d', mktime(0,0,0, date("m", strtotime($fecha_guardar."+1 month")), 0, date("Y", strtotime($fecha_guardar))))."',
								'".date('Y-m-d', mktime(0,0,0, date("m", strtotime($fecha_guardar."+1 month")), 0, date("Y", strtotime($fecha_guardar))))."',

								'01',
								'".date('Y-m-d', strtotime($fecha_guardar))."',
								1,
								'" .$login["id"]."',
								'" .date("Y-m-d H:i:s")."',
								'" .$login["id"]."',
								'" .date("Y-m-d H:i:s")."'
							)";
				$mysqli->query($insert_haber);

				if($mysqli->error){
					$error .= $mysqli->error . $insert_haber;
				}

				//INGRESAR EN EL SISPAG
				//EN EL SISPAG SE INGRESA EL HABER DEL CONCAR (PORQUE EL HABER ES EL MONTO A PAGAR)

				$insert_haber_sispag = "INSERT INTO cont_file_sispag
										(
											id_condicion_economica,
											num_cuota,
											cp_cvanexo,
											cp_ccodigo,
											cp_ctipdoc,
											cp_cnum_doc,

											cp_cfecdoc,
											cp_cfecven,
											cp_cfec_rec,
											cp_cfeccom,

											cp_csubdia,
											cp_ccompro,
											cp_cdebhab,

											cp_nimpomn,

											cp_nigvmn,
											cp_nigvus,
											cp_nimp2mn,
											cp_nimp2us,
											cp_nimpaju,

											cp_ccuenta,

											cp_dfeccre,
											cp_dfecmod,

											cp_cuser,
											cp_ninafec,

											cp_dfecdoc,
											cp_dfecven,
											cp_dfecrec,
											cp_dfeccom,

											cp_cimagen,

											cp_ctipo,

											cp_nporre,

											registro_mes,
											status,
											user_created_id,
											created_at,
											user_updated_id,
											updated_at
										)
										VALUES
										(
											'".$li["condicion_economica_id"]."',
											'".$i."',
											'P',
											'".$benefi["num_docu"]."',
											'FT',
											'1683-".date("m", strtotime($fecha_guardar)).date("Y", strtotime($fecha_guardar))."',

											'".date('Y-m-d', strtotime($fecha_guardar))."',
											'".date('Y-m-d', strtotime($fecha_guardar))."',
											'".date('Y-m-d', strtotime($fecha_guardar))."',
											'".date('Y-m-d', strtotime($fecha_guardar))."',

											'".$benefi["codigo_empresa"]."',
											'".date("m", strtotime($fecha_guardar))."',
											'H',

											" . round($monto_total_beneficiario, 2) . ",

											'0',
											'0',
											'0',
											'0',
											'0',

											'421201',

											'".date('Y-m-d', strtotime($fecha_guardar))."',
											'".date('Y-m-d', strtotime($fecha_guardar))."',

											'SUSY',
											'0',

											'".date('Y-m-d', strtotime($fecha_guardar))."',
											'".date('Y-m-d', strtotime($fecha_guardar))."',
											'".date('Y-m-d', strtotime($fecha_guardar))."',
											'".date('Y-m-d', strtotime($fecha_guardar))."',

											'0',

											'V',

											'0',

											'".date('Y-m-d', strtotime($fecha_guardar))."',
											1,
											'" .$login["id"]."',
											'" .date("Y-m-d H:i:s")."',
											'" .$login["id"]."',
											'" .date("Y-m-d H:i:s")."'
										) ";

				$mysqli->query($insert_haber_sispag);

				if($mysqli->error){
					$error .= $mysqli->error . $insert_haber_sispag;
				}

			}



		}

		$fecha_guardar = date("d-m-Y", strtotime($fecha_guardar."+ 1 month"));

	}
	$result["error"] = $error;
}

function send_email_observacion_contrato_agente($observacion_id , $correos_adjuntos = "")
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$html = '';

	$sql = "SELECT
				o.id,
				o.contrato_id,
				o.observaciones,
				i.ubicacion,
				concat(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, '')) AS usuario_creacion,
				ar.id AS area_creacion_id,
				ar.nombre AS area_creacion,
				o.user_created_id,
				o.created_at,
				concat(IFNULL(ps.nombre, ''),' ', IFNULL(ps.apellido_paterno, '')) AS supervisor,
				concat(IFNULL(pjc.nombre, ''),' ', IFNULL(pjc.apellido_paterno, '')) AS jefe_comercial,
				pjc.correo AS jefe_comercial_correo,
				co.sigla AS sigla_correlativo,
				c.codigo_correlativo,
				pdag.correo AS email_director_aprobacion,
				pap.correo AS email_aprobacion_gerencia
			FROM cont_observaciones o
				INNER JOIN tbl_usuarios u ON o.user_created_id = u.id
				INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
				INNER JOIN tbl_areas ar ON p.area_id = ar.id
				INNER JOIN cont_contrato c ON o.contrato_id = c.contrato_id
				INNER JOIN cont_inmueble i ON c.contrato_id = i.contrato_id AND i.status = 1
				LEFT JOIN tbl_usuarios us ON c.persona_responsable_id = us.id
				LEFT JOIN tbl_personal_apt ps ON us.personal_id = ps.id
				LEFT JOIN tbl_usuarios ujc ON c.user_created_id = ujc.id
				LEFT JOIN tbl_personal_apt pjc ON ujc.personal_id = pjc.id
				LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato

				LEFT JOIN tbl_usuarios udag ON c.director_aprobacion_id = udag.id
				LEFT JOIN tbl_personal_apt pdag ON udag.personal_id = pdag.id

				LEFT JOIN tbl_usuarios uap ON c.aprobado_por = uap.id
				LEFT JOIN tbl_personal_apt pap ON uap.personal_id = pap.id

			WHERE o.id = " . $observacion_id . " AND o.status = 1
			ORDER BY o.created_at ASC";


	$query = $mysqli->query($sql);
	//$row_count = $query->num_rows;

	$contrato_id_de_la_obs = "";

	$body = "";
	$body .= '<html>';
	$body .= '<div>';

	$correo_del_jefe_comercial = '';
	$email_director_aprobacion = '';
	$email_aprobacion_gerencia = '';
	while($sel = $query->fetch_assoc())
	{
		$sigla_correlativo = $sel['sigla_correlativo'];
		$codigo_correlativo = $sel['codigo_correlativo'];

		//	$correo_del_jefe_comercial = $sel["jefe_comercial_correo"];
		$correo_del_jefe_comercial = "";
		$area_creacion_id = $sel['area_creacion_id'];
		$contrato_id_de_la_obs = $sel["contrato_id"];
		$email_director_aprobacion = $sel["email_director_aprobacion"];
		$email_aprobacion_gerencia = $sel["email_aprobacion_gerencia"];

		if ($area_creacion_id == 21)
		{
			//EN PRODUCCION EL AREA OPERACIONES ES ID: 21
			// OPERACIONES

			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

			$body .= '<thead>';
			$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
			$body .= '<b>Observacion en la Solicitud - (ID Solicitud: ' . $contrato_id_de_la_obs . ')</b>';
			$body .= '</th>';
			$body .= '</tr>';
			$body .= '</thead>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>De:</b></td>';
				$body .= '<td>'.$sel["area_creacion"].' </td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Para:</b></td>';
				$body .= '<td>Legal</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Ubicacion del inmueble:</b></td>';
				$body .= '<td>'.$sel["ubicacion"].'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Jefe Comercial:</b></td>';
				$body .= '<td>'.$sel["jefe_comercial"].'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Fecha observacion:</b></td>';
				$body .= '<td>'.$sel["created_at"].'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd"><b>Observacion:</b></td>';
				$body .= '<td>'.$sel["observaciones"].'</td>';
			$body .= '</tr>';


			$body .= '</table>';

		}
		else if($area_creacion_id == 33)
		{
			//EN PRODUCCION EL AREA LEGAL ES ID: 33
			//EN DEV EL AREA LEGAL ES ID: 33
			//LEGAL

			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

			$body .= '<thead>';
			$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
			$body .= '<b>Observacion en la Solicitud - (ID Solicitud: ' . $contrato_id_de_la_obs . ')</b>';
			$body .= '</th>';
			$body .= '</tr>';
			$body .= '</thead>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>De:</b></td>';
				$body .= '<td>'.$sel["area_creacion"].' </td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Para:</b></td>';
				$body .= '<td>Operaciones</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Ubicacion del inmueble:</b></td>';
				$body .= '<td>'.$sel["ubicacion"].'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Jefe Comercial:</b></td>';
				$body .= '<td>'.$sel["jefe_comercial"].'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Fecha observacion:</b></td>';
				$body .= '<td>'.$sel["created_at"].'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd"><b>Observacion:</b></td>';
				$body .= '<td>'.$sel["observaciones"].'</td>';
			$body .= '</tr>';


			$body .= '</table>';

		}
		else
		{
			// CUANDO LOS USUARIOS NO FUERON CONFIGURADOS CORRECTAMENTE EN SUS AREAS RESPECTIVAS.

			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

			$body .= '<thead>';
			$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
			$body .= '<b>Observacion en la Solicitud - (ID Solicitud: ' . $contrato_id_de_la_obs . ')</b>';
			$body .= '</th>';
			$body .= '</tr>';
			$body .= '</thead>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Ubicacion del inmueble:</b></td>';
				$body .= '<td>'.$sel["ubicacion"].' <strong>(ID Solicitud: ' . $contrato_id_de_la_obs . ')</strong></td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Jefe Comercial:</b></td>';
				$body .= '<td>'.$sel["jefe_comercial"].'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Usuario quien observo:</b></td>';
				$body .= '<td>'.$sel["usuario_creacion"].' <strong>('.$sel["area_creacion"].')</strong></td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Fecha observacion:</b></td>';
				$body .= '<td>'.$sel["created_at"].'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd"><b>Observacion:</b></td>';
				$body .= '<td>'.$sel["observaciones"].'</td>';
			$body .= '</tr>';


			$body .= '</table>';

		}


	}

	$body .= '</div>';

	$body .= '<div>';
		$body .= '<br>';
	$body .= '</div>';

	$body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
	    $body .= '<a href="'.$host.'/?sec_id=contrato&sub_sec_id=detalle_solicitudv2&id='.$contrato_id_de_la_obs.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
			$body .= '<b>Ver Solicitud</b>';
	    $body .= '</a>';
	$body .= '</div>';

	$body .= '</html>';
	$body .= "";

	$array_email = [];
	if (!Empty($correo_del_jefe_comercial)) {
		array_push($array_email,$correo_del_jefe_comercial);
	}
	if (!Empty($email_director_aprobacion)) {
		array_push($array_email,$email_director_aprobacion);
	}
	if (!Empty($email_aprobacion_gerencia) && $email_director_aprobacion != $email_aprobacion_gerencia) {
		array_push($array_email,$email_aprobacion_gerencia);
	}
	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_observacion_contrato_agente($array_email);

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];

	if($correos_adjuntos != ""){
		$validate = true;
		$emails = preg_split('[,|;]',$correos_adjuntos);
		foreach($emails as $e){
		    if(preg_match('/^[_ña-z0-9-]+(\.[_ña-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/',trim($e)) == 0){
				$error_msg = "'" . $e . "'" ." no es un Correo Adjunto válido";
		        $result["http_code"] = 500;
				$result["status"] = "error";
				$result["mensaje"] = $error_msg;
				echo json_encode($result);
				die;
		    }
		    else
		    {
				$cc[] = $e;
		    }
		}
	}

	$request = [
		"subject" => "Gestion - Sistema Contratos - Observación en la solicitud de Agente: Código - " .$sigla_correlativo.$codigo_correlativo,
		//"subject" => "Solicitud de proveedor",
		//"subject" => "Solicitud de adenda",
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc)
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc)
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();
		//return true;

	}
	catch (Exception $e)
	{
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}

}

function send_email_observacion_contrato_arrendamiento($observacion_id , $correos_adjuntos = "")
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$html = '';

	$sql = "SELECT
				o.id,
				o.contrato_id,
				o.observaciones,
				MAX(i.ubicacion) AS ubicacion,
				concat(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, '')) AS usuario_creacion,
				ar.id AS area_creacion_id,
				ar.nombre AS area_creacion,
				o.user_created_id,
				o.created_at,
				concat(IFNULL(ps.nombre, ''),' ', IFNULL(ps.apellido_paterno, '')) AS supervisor,
				concat(IFNULL(pjc.nombre, ''),' ', IFNULL(pjc.apellido_paterno, '')) AS jefe_comercial,
				pjc.correo AS jefe_comercial_correo,
				co.sigla AS sigla_correlativo,
				c.codigo_correlativo,
				pdag.correo AS email_director_aprobacion,
				pap.correo AS email_aprobacion_gerencia

			FROM cont_observaciones o
				INNER JOIN tbl_usuarios u ON o.user_created_id = u.id
				INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
				INNER JOIN tbl_areas ar ON p.area_id = ar.id
				INNER JOIN cont_contrato c ON o.contrato_id = c.contrato_id
				INNER JOIN cont_inmueble i ON c.contrato_id = i.contrato_id AND i.status = 1
				LEFT JOIN tbl_usuarios us ON c.persona_responsable_id = us.id
				LEFT JOIN tbl_personal_apt ps ON us.personal_id = ps.id
				LEFT JOIN tbl_usuarios ujc ON c.user_created_id = ujc.id
				LEFT JOIN tbl_personal_apt pjc ON ujc.personal_id = pjc.id
				LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato

				LEFT JOIN tbl_usuarios udag ON c.director_aprobacion_id = udag.id
				LEFT JOIN tbl_personal_apt pdag ON udag.personal_id = pdag.id

				LEFT JOIN tbl_usuarios uap ON c.aprobado_por = uap.id
				LEFT JOIN tbl_personal_apt pap ON uap.personal_id = pap.id


			WHERE o.id = " . $observacion_id . " AND o.status = 1
			GROUP BY o.id, co.sigla
			ORDER BY o.created_at ASC";


	$query = $mysqli->query($sql);
	//$row_count = $query->num_rows;

	$contrato_id_de_la_obs = "";

	$body = "";
	$body .= '<html>';
	$body .= '<div>';

	$correo_del_jefe_comercial = '';
	$email_director_aprobacion = '';
	$email_aprobacion_gerencia = '';
	while($sel = $query->fetch_assoc())
	{
		$sigla_correlativo = $sel['sigla_correlativo'];
		$codigo_correlativo = $sel['codigo_correlativo'];

		$correo_del_jefe_comercial = $sel["jefe_comercial_correo"];
		$area_creacion_id = $sel['area_creacion_id'];
		$contrato_id_de_la_obs = $sel["contrato_id"];
		$email_director_aprobacion = $sel["email_director_aprobacion"];
		$email_aprobacion_gerencia = $sel["email_aprobacion_gerencia"];
		if ($area_creacion_id == 21)
		{
			//EN PRODUCCION EL AREA OPERACIONES ES ID: 21
			// OPERACIONES

			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

			$body .= '<thead>';
			$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
			$body .= '<b>Observacion en la Solicitud - (ID Solicitud: ' . $contrato_id_de_la_obs . ')</b>';
			$body .= '</th>';
			$body .= '</tr>';
			$body .= '</thead>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>De:</b></td>';
				$body .= '<td>'.$sel["area_creacion"].' </td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Para:</b></td>';
				$body .= '<td>Legal</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Ubicacion del inmueble:</b></td>';
				$body .= '<td>'.$sel["ubicacion"].'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Jefe Comercial:</b></td>';
				$body .= '<td>'.$sel["jefe_comercial"].'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Fecha observacion:</b></td>';
				$body .= '<td>'.$sel["created_at"].'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd"><b>Observacion:</b></td>';
				$body .= '<td>'.$sel["observaciones"].'</td>';
			$body .= '</tr>';


			$body .= '</table>';

		}
		else if($area_creacion_id == 33)
		{
			//EN PRODUCCION EL AREA LEGAL ES ID: 33
			//EN DEV EL AREA LEGAL ES ID: 33
			//LEGAL

			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

			$body .= '<thead>';
			$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
			$body .= '<b>Observacion en la Solicitud - (ID Solicitud: ' . $contrato_id_de_la_obs . ')</b>';
			$body .= '</th>';
			$body .= '</tr>';
			$body .= '</thead>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>De:</b></td>';
				$body .= '<td>'.$sel["area_creacion"].' </td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Para:</b></td>';
				$body .= '<td>Operaciones</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Ubicacion del inmueble:</b></td>';
				$body .= '<td>'.$sel["ubicacion"].'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Jefe Comercial:</b></td>';
				$body .= '<td>'.$sel["jefe_comercial"].'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Fecha observacion:</b></td>';
				$body .= '<td>'.$sel["created_at"].'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd"><b>Observacion:</b></td>';
				$body .= '<td>'.$sel["observaciones"].'</td>';
			$body .= '</tr>';


			$body .= '</table>';

		}
		else
		{
			// CUANDO LOS USUARIOS NO FUERON CONFIGURADOS CORRECTAMENTE EN SUS AREAS RESPECTIVAS.

			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

			$body .= '<thead>';
			$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
			$body .= '<b>Observacion en la Solicitud - (ID Solicitud: ' . $contrato_id_de_la_obs . ')</b>';
			$body .= '</th>';
			$body .= '</tr>';
			$body .= '</thead>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Ubicacion del inmueble:</b></td>';
				$body .= '<td>'.$sel["ubicacion"].' <strong>(ID Solicitud: ' . $contrato_id_de_la_obs . ')</strong></td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Jefe Comercial:</b></td>';
				$body .= '<td>'.$sel["jefe_comercial"].'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Usuario quien observo:</b></td>';
				$body .= '<td>'.$sel["usuario_creacion"].' <strong>('.$sel["area_creacion"].')</strong></td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Fecha observacion:</b></td>';
				$body .= '<td>'.$sel["created_at"].'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd"><b>Observacion:</b></td>';
				$body .= '<td>'.$sel["observaciones"].'</td>';
			$body .= '</tr>';


			$body .= '</table>';

		}


	}

	$body .= '</div>';

	$body .= '<div>';
		$body .= '<br>';
	$body .= '</div>';

	$body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
	    $body .= '<a href="'.$host.'/?sec_id=contrato&sub_sec_id=detalle_solicitudv2&id='.$contrato_id_de_la_obs.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
			$body .= '<b>Ver Solicitud</b>';
	    $body .= '</a>';
	$body .= '</div>';

	$body .= '</html>';
	$body .= "";


	$array_email = [];
	if (!Empty($email_director_aprobacion)) {
		array_push($array_email,$email_director_aprobacion);
	}
	if (!Empty($email_aprobacion_gerencia) && $email_director_aprobacion != $email_aprobacion_gerencia) {
		array_push($array_email,$email_aprobacion_gerencia);
	}
	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_observacion_contrato_arrendamiento($array_email);

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];

	if($correos_adjuntos != ""){
		$validate = true;
		$emails = preg_split('[,|;]',$correos_adjuntos);
		foreach($emails as $e){
		    if(preg_match('/^[_ña-z0-9-]+(\.[_ña-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/',trim($e)) == 0){
				$error_msg = "'" . $e . "'" ." no es un Correo Adjunto válido";
		        $result["http_code"] = 500;
				$result["status"] = "error";
				$result["mensaje"] = $error_msg;
				echo json_encode($result);
				die;
		    }
		    else
		    {
				$cc[] = $e;
		    }
		}
	}

	$request = [
		"subject" => "Gestion - Sistema Contratos - Observación en la solicitud de Arrendamiento: Código - " .$sigla_correlativo.$codigo_correlativo,
		//"subject" => "Solicitud de proveedor",
		//"subject" => "Solicitud de adenda",
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc)
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc)
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();
		//return true;

	}
	catch (Exception $e)
	{
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}

}

function send_email_observacion_contrato_proveedor($observacion_id , $correos_adjuntos = "")
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$html = '';

	$sql = "SELECT
				o.id,
				o.contrato_id,
				o.observaciones,
			    c.razon_social as proveedor_razon_social,
				c.nombre_comercial as proveedor_nombre_comercial,
			    SUBSTRING(c.detalle_servicio, 1, 20) AS detalle_servicio,
			    c.fecha_inicio,
				concat(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, '')) AS usuario_creacion,
				p.correo AS usuario_creacion_correo,
				ar.id AS area_creacion_id,
				ar.nombre AS area_creacion,
				o.user_created_id,
				o.created_at,
				concat(IFNULL(ps.nombre, ''),' ', IFNULL(ps.apellido_paterno, '')) AS supervisor,
				concat(IFNULL(pjc.nombre, ''),' ', IFNULL(pjc.apellido_paterno, '')) AS jefe_comercial,
				pjc.correo AS usuario_solicitante_correo,
				co.sigla AS sigla_correlativo,
				c.codigo_correlativo,
				c.gerente_area_id,
				c.gerente_area_nombre,
				c.gerente_area_email,
				CONCAT(IFNULL(peg.nombre, ''),' ',IFNULL(peg.apellido_paterno, ''),' ',IFNULL(peg.apellido_materno, '')) AS nombre_del_gerente_area,
				peg.correo AS email_del_gerente_area,
				pdag.correo AS email_director_aprobacion,
				pap.correo AS email_aprobacion_gerencia
			FROM cont_observaciones o
				INNER JOIN tbl_usuarios u ON o.user_created_id = u.id
				INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
				INNER JOIN tbl_areas ar ON p.area_id = ar.id
				INNER JOIN cont_contrato c ON o.contrato_id = c.contrato_id
				LEFT JOIN tbl_usuarios us ON c.persona_responsable_id = us.id
				LEFT JOIN tbl_personal_apt ps ON us.personal_id = ps.id
				LEFT JOIN tbl_usuarios ujc ON c.user_created_id = ujc.id
				LEFT JOIN tbl_personal_apt pjc ON ujc.personal_id = pjc.id
				LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
				LEFT JOIN tbl_usuarios ug ON c.gerente_area_id = ug.id
				LEFT JOIN tbl_personal_apt peg ON ug.personal_id = peg.id

				LEFT JOIN tbl_usuarios udag ON c.director_aprobacion_id = udag.id
				LEFT JOIN tbl_personal_apt pdag ON udag.personal_id = pdag.id

				LEFT JOIN tbl_usuarios uap ON c.aprobado_por = uap.id
				LEFT JOIN tbl_personal_apt pap ON uap.personal_id = pap.id


			WHERE o.id = " . $observacion_id . " AND o.status = 1
			ORDER BY o.created_at ASC";


	$query = $mysqli->query($sql);

	$contrato_id_de_la_obs = "";

	$body = "";
	$body .= '<html>';
	$body .= '<div>';

	$usuario_creacion_correo = '';
	$email_director_aprobacion = '';
	$email_aprobacion_gerencia = '';
	while($sel = $query->fetch_assoc())
	{
		$sigla_correlativo = $sel['sigla_correlativo'];
		$codigo_correlativo = $sel['codigo_correlativo'];
		$area_creacion_id = $sel['area_creacion_id'];
		$usuario_creacion_correo = $sel['usuario_solicitante_correo'];
		$date = date_create($sel["fecha_inicio"]);
		$fecha_inicio_contrato = date_format($date, "Y-m-d");
		$contrato_id_de_la_obs = $sel["contrato_id"];
		$gerente_area_id = trim($sel["gerente_area_id"]);
		$email_director_aprobacion = $sel["email_director_aprobacion"];
		$email_aprobacion_gerencia = $sel["email_aprobacion_gerencia"];
		if (empty($gerente_area_id)) {
			$gerente_area_nombre = trim($sel["gerente_area_nombre"]);
			$gerente_area_email = trim($sel["gerente_area_email"]);
		} else {
			$gerente_area_nombre = trim($sel["nombre_del_gerente_area"]);
			$gerente_area_email = trim($sel["email_del_gerente_area"]);
		}

		if($area_creacion_id == 33)
		{
			//EN PRODUCCION EL AREA LEGAL ES ID: 33
			//EN DEV EL AREA LEGAL ES ID: 26
			// LEGAL

			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

			$body .= '<thead>';
			$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
			$body .= '<b>Observacion en la Solicitud</b>';
			$body .= '</th>';
			$body .= '</tr>';
			$body .= '</thead>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>De:</b></td>';
				$body .= '<td>'.$sel["area_creacion"].' </td>';
			$body .= '</tr>';

			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Razón Social Proveedor:</b></td>';
				$body .= '<td>'.$sel["proveedor_razon_social"].'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Nombre Comercial Proveedor:</b></td>';
				$body .= '<td>'.$sel["proveedor_nombre_comercial"].'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Detale servicio:</b></td>';
				$body .= '<td>'.$sel["detalle_servicio"].'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Fecha inicio contrato:</b></td>';
				$body .= '<td>'.$fecha_inicio_contrato.'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Fecha observacion:</b></td>';
				$body .= '<td>'.$sel["created_at"].'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd"><b>Observacion:</b></td>';
				$body .= '<td>'.$sel["observaciones"].'</td>';
			$body .= '</tr>';


			$body .= '</table>';

		}
		else
		{
			// CUANDO LOS USUARIOS NO FUERON CONFIGURADOS CORRECTAMENTE EN SUS AREAS RESPECTIVAS.

			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

			$body .= '<thead>';
			$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
			$body .= '<b>Observacion en la Solicitud - (ID Solicitud proveedor: '.$sel["contrato_id"].')</b>';
			$body .= '</th>';
			$body .= '</tr>';
			$body .= '</thead>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>De:</b></td>';
				$body .= '<td>'.$sel["area_creacion"].' </td>';
			$body .= '</tr>';

			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Razón Social Proveedor:</b></td>';
				$body .= '<td>'.$sel["proveedor_razon_social"].'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Nombre Comercial Proveedor:</b></td>';
				$body .= '<td>'.$sel["proveedor_nombre_comercial"].'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Detale servicio:</b></td>';
				$body .= '<td>'.$sel["detalle_servicio"].'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Fecha inicio contrato:</b></td>';
				$body .= '<td>'.$fecha_inicio_contrato.'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Fecha observacion:</b></td>';
				$body .= '<td>'.$sel["created_at"].'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd"><b>Observacion:</b></td>';
				$body .= '<td>'.$sel["observaciones"].'</td>';
			$body .= '</tr>';


			$body .= '</table>';

		}


	}

	$body .= '</div>';

	$body .= '<div>';
		$body .= '<br>';
	$body .= '</div>';

	$body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
	    $body .= '<a href="'.$host.'/?sec_id=contrato&sub_sec_id=detalleSolicitudProveedor&id='.$contrato_id_de_la_obs.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
			$body .= '<b>Ver Solicitud</b>';
	    $body .= '</a>';
	$body .= '</div>';

	$body .= '</html>';
	$body .= "";

	$array_correos = [];
	if(!Empty($usuario_creacion_correo)){
		array_push($array_correos,$usuario_creacion_correo);
	}
	if(!Empty($gerente_area_email)){
		array_push($array_correos,$gerente_area_email);
	}
	if (!Empty($email_aprobacion_gerencia)) {
		array_push($array_correos,$email_aprobacion_gerencia);
	}

	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_observacion_contrato_proveedor($array_correos);

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];

	if($correos_adjuntos != ""){
		$validate = true;
		$emails = preg_split('[,|;]',$correos_adjuntos);
		foreach($emails as $e){
		    if(preg_match('/^[_ña-z0-9-]+(\.[_ña-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/',trim($e)) == 0){
				$error_msg = "'" . $e . "'" ." no es un Correo Adjunto válido";
		        $result["http_code"] = 500;
				$result["status"] = "error";
				$result["mensaje"] = $error_msg;
				echo json_encode($result);
				die;
		    }
		    else
		    {
				$cc[] = $e;
		    }
		}
	}

	if (env('SEND_EMAIL') == 'produccion') {
		if ( !(empty($gerente_area_email)) ) {
			if ( sec_contrato_detalle_solicitudv2_is_valid_email($gerente_area_email) ) {
				array_push($cc, $gerente_area_email );
			}
		}
	}

	$request = [
		"subject" => "Gestion - Sistema Contratos - Observación en la Solicitud de Proveedor: Código - " .$sigla_correlativo.$codigo_correlativo,
		//"subject" => "Solicitud de proveedor",
		//"subject" => "Solicitud de adenda",
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc)
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc)
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();
		//return true;

	}
	catch (Exception $e)
	{
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}

}

function send_email_observacion_proveedor_gerencia($observacion_id)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$html = '';

	$sql = "SELECT
				o.id,
				o.contrato_id,
				o.observaciones,
			    c.razon_social as proveedor_razon_social,
				c.nombre_comercial as proveedor_nombre_comercial,
			    SUBSTRING(c.detalle_servicio, 1, 20) AS detalle_servicio,
			    c.fecha_inicio,
				concat(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, '')) AS usuario_creacion,
				p.correo AS usuario_creacion_correo,
				ar.id AS area_creacion_id,
				ar.nombre AS area_creacion,
				o.user_created_id,
				o.created_at,
				concat(IFNULL(ps.nombre, ''),' ', IFNULL(ps.apellido_paterno, '')) AS supervisor,
				concat(IFNULL(pjc.nombre, ''),' ', IFNULL(pjc.apellido_paterno, '')) AS jefe_comercial,
				pjc.correo AS usuario_solicitante_correo,
				co.sigla AS sigla_correlativo,
				c.codigo_correlativo,
				c.gerente_area_id,
				c.gerente_area_nombre,
				c.gerente_area_email,
				CONCAT(IFNULL(peg.nombre, ''),' ',IFNULL(peg.apellido_paterno, ''),' ',IFNULL(peg.apellido_materno, '')) AS nombre_del_gerente_area,
				peg.correo AS email_del_gerente_area
			FROM cont_observaciones o
				INNER JOIN tbl_usuarios u ON o.user_created_id = u.id
				INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
				INNER JOIN tbl_areas ar ON p.area_id = ar.id
				INNER JOIN cont_contrato c ON o.contrato_id = c.contrato_id
				LEFT JOIN tbl_usuarios us ON c.persona_responsable_id = us.id
				LEFT JOIN tbl_personal_apt ps ON us.personal_id = ps.id
				LEFT JOIN tbl_usuarios ujc ON c.user_created_id = ujc.id
				LEFT JOIN tbl_personal_apt pjc ON ujc.personal_id = pjc.id
				LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
				LEFT JOIN tbl_usuarios ug ON c.gerente_area_id = ug.id
				LEFT JOIN tbl_personal_apt peg ON ug.personal_id = peg.id
			WHERE o.id = " . $observacion_id . " AND o.status = 1
			ORDER BY o.created_at ASC";


	$query = $mysqli->query($sql);

	$contrato_id_de_la_obs = "";

	$body = "";
	$body .= '<html>';
	$body .= '<div>';

	$usuario_creacion_correo = '';

	while($sel = $query->fetch_assoc())
	{
		$sigla_correlativo = $sel['sigla_correlativo'];
		$codigo_correlativo = $sel['codigo_correlativo'];
		$area_creacion_id = $sel['area_creacion_id'];
		$usuario_creacion_correo = $sel['usuario_solicitante_correo'];
		$date = date_create($sel["fecha_inicio"]);
		$fecha_inicio_contrato = date_format($date, "Y-m-d");
		$contrato_id_de_la_obs = $sel["contrato_id"];
		$gerente_area_id = trim($sel["gerente_area_id"]);

		if (empty($gerente_area_id)) {
			$gerente_area_nombre = trim($sel["gerente_area_nombre"]);
			$gerente_area_email = trim($sel["gerente_area_email"]);
		} else {
			$gerente_area_nombre = trim($sel["nombre_del_gerente_area"]);
			$gerente_area_email = trim($sel["email_del_gerente_area"]);
		}

		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

		$body .= '<thead>';
		$body .= '<tr>';
		$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
		$body .= '<b>Observacion en la Solicitud</b>';
		$body .= '</th>';
		$body .= '</tr>';
		$body .= '</thead>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>De:</b></td>';
			$body .= '<td>'.$sel["area_creacion"].' </td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Razón Social Proveedor:</b></td>';
			$body .= '<td>'.$sel["proveedor_razon_social"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Nombre Comercial Proveedor:</b></td>';
			$body .= '<td>'.$sel["proveedor_nombre_comercial"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Detale servicio:</b></td>';
			$body .= '<td>'.$sel["detalle_servicio"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Fecha inicio contrato:</b></td>';
			$body .= '<td>'.$fecha_inicio_contrato.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Fecha observacion:</b></td>';
			$body .= '<td>'.$sel["created_at"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Observacion:</b></td>';
			$body .= '<td>'.$sel["observaciones"].'</td>';
		$body .= '</tr>';


		$body .= '<tr>';
			$body .= '<td colspan="2" style="background-color: #ffffdd"><b>Nota: Despues de Resolver la observación hacer click en el boton de color amarillo "Notificar a Director(a)". </b></td>';

		$body .= '</tr>';


		$body .= '</table>';
	}

	$body .= '</div>';

	$body .= '<div>';
		$body .= '<br>';
	$body .= '</div>';

	$body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
	    $body .= '<a href="'.$host.'/?sec_id=contrato&sub_sec_id=detalleSolicitudProveedor&id='.$contrato_id_de_la_obs.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
			$body .= '<b>Ver Solicitud</b>';
	    $body .= '</a>';
	$body .= '</div>';

	$body .= '</html>';
	$body .= "";


	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_observacion_gerencia([$usuario_creacion_correo]);

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];

	$request = [
		"subject" => "Gestion - Sistema Contratos - Observación en la Solicitud de Proveedor: Código - " .$sigla_correlativo.$codigo_correlativo,
		//"subject" => "Solicitud de proveedor",
		//"subject" => "Solicitud de adenda",
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc)
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc)
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();
		//return true;

	}
	catch (Exception $e)
	{
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}

}

function send_email_observacion_acuerdo_confidencialidad($observacion_id , $correos_adjuntos = "")
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$html = '';

	$sql = "SELECT
				o.id,
				o.contrato_id,
				o.observaciones,
			    c.razon_social as proveedor_razon_social,
				c.nombre_comercial as proveedor_nombre_comercial,
			    SUBSTRING(c.detalle_servicio, 1, 20) AS detalle_servicio,
			    c.fecha_inicio,
				concat(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, '')) AS usuario_creacion,
				p.correo AS usuario_creacion_correo,
				ar.id AS area_creacion_id,
				ar.nombre AS area_creacion,
				o.user_created_id,
				o.created_at,
				concat(IFNULL(ps.nombre, ''),' ', IFNULL(ps.apellido_paterno, '')) AS supervisor,
				concat(IFNULL(pjc.nombre, ''),' ', IFNULL(pjc.apellido_paterno, '')) AS jefe_comercial,
				pjc.correo AS usuario_solicitante_correo,
				co.sigla AS sigla_correlativo,
				c.codigo_correlativo,
				pdag.correo AS email_director_aprobacion,
				pap.correo AS email_aprobacion_gerencia
			FROM cont_observaciones o
				INNER JOIN tbl_usuarios u ON o.user_created_id = u.id
				INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
				INNER JOIN tbl_areas ar ON p.area_id = ar.id
				INNER JOIN cont_contrato c ON o.contrato_id = c.contrato_id
				LEFT JOIN tbl_usuarios us ON c.persona_responsable_id = us.id
				LEFT JOIN tbl_personal_apt ps ON us.personal_id = ps.id
				LEFT JOIN tbl_usuarios ujc ON c.user_created_id = ujc.id
				LEFT JOIN tbl_personal_apt pjc ON ujc.personal_id = pjc.id
				LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato

				LEFT JOIN tbl_usuarios udag ON c.director_aprobacion_id = udag.id
				LEFT JOIN tbl_personal_apt pdag ON udag.personal_id = pdag.id

				LEFT JOIN tbl_usuarios uap ON c.aprobado_por = uap.id
				LEFT JOIN tbl_personal_apt pap ON uap.personal_id = pap.id
			WHERE o.id = " . $observacion_id . " AND o.status = 1
			ORDER BY o.created_at ASC";


	$query = $mysqli->query($sql);

	$contrato_id_de_la_obs = "";

	$body = "";
	$body .= '<html>';
	$body .= '<div>';

	$usuario_creacion_correo = '';
	$email_director_aprobacion = '';
	$email_aprobacion_gerencia = '';
	while($sel = $query->fetch_assoc())
	{
		$sigla_correlativo = $sel['sigla_correlativo'];
		$codigo_correlativo = $sel['codigo_correlativo'];
		$area_creacion_id = $sel['area_creacion_id'];
		$usuario_creacion_correo = $sel['usuario_solicitante_correo'];
		$date = date_create($sel["fecha_inicio"]);
		$fecha_inicio_contrato = date_format($date, "Y-m-d");
		$contrato_id_de_la_obs = $sel["contrato_id"];
		$email_director_aprobacion = $sel["email_director_aprobacion"];
		$email_aprobacion_gerencia = $sel["email_aprobacion_gerencia"];
		if($area_creacion_id == 33)
		{
			//EN PRODUCCION EL AREA LEGAL ES ID: 33
			//EN DEV EL AREA LEGAL ES ID: 26
			// LEGAL

			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

			$body .= '<thead>';
			$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
			$body .= '<b>Observacion en la Solicitud</b>';
			$body .= '</th>';
			$body .= '</tr>';
			$body .= '</thead>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>De:</b></td>';
				$body .= '<td>'.$sel["area_creacion"].' </td>';
			$body .= '</tr>';

			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Razón Social Proveedor:</b></td>';
				$body .= '<td>'.$sel["proveedor_razon_social"].'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Nombre Comercial Proveedor:</b></td>';
				$body .= '<td>'.$sel["proveedor_nombre_comercial"].'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Detale servicio:</b></td>';
				$body .= '<td>'.$sel["detalle_servicio"].'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Fecha inicio contrato:</b></td>';
				$body .= '<td>'.$fecha_inicio_contrato.'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Fecha observacion:</b></td>';
				$body .= '<td>'.$sel["created_at"].'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd"><b>Observacion:</b></td>';
				$body .= '<td>'.$sel["observaciones"].'</td>';
			$body .= '</tr>';


			$body .= '</table>';

		}
		else
		{
			// CUANDO LOS USUARIOS NO FUERON CONFIGURADOS CORRECTAMENTE EN SUS AREAS RESPECTIVAS.

			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

			$body .= '<thead>';
			$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
			$body .= '<b>Observacion en la Solicitud</b>';
			$body .= '</th>';
			$body .= '</tr>';
			$body .= '</thead>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>De:</b></td>';
				$body .= '<td>'.$sel["area_creacion"].' </td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Proveedor:</b></td>';
				$body .= '<td>'.$sel["proveedor_razon_social"].'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Detale servicio:</b></td>';
				$body .= '<td>'.$sel["detalle_servicio"].'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Fecha inicio contrato:</b></td>';
				$body .= '<td>'.$fecha_inicio_contrato.'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Fecha observacion:</b></td>';
				$body .= '<td>'.$sel["created_at"].'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd"><b>Observacion:</b></td>';
				$body .= '<td>'.$sel["observaciones"].'</td>';
			$body .= '</tr>';


			$body .= '</table>';

		}


	}

	$body .= '</div>';

	$body .= '<div>';
		$body .= '<br>';
	$body .= '</div>';

	$body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
	    $body .= '<a href="'.$host.'/?sec_id=contrato&sub_sec_id=detalle_solicitudv2_acuerdo_confidencialidad&id='.$contrato_id_de_la_obs.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
			$body .= '<b>Ver Solicitud</b>';
	    $body .= '</a>';
	$body .= '</div>';

	$body .= '</html>';
	$body .= "";

	$array_email = [];

	if(!Empty($usuario_creacion_correo)){
		array_push($array_email,$usuario_creacion_correo);
	}
	if (!Empty($email_director_aprobacion)) {
		array_push($array_email,$email_director_aprobacion);
	}
	if (!Empty($email_aprobacion_gerencia) && $email_director_aprobacion != $email_aprobacion_gerencia) {
		array_push($array_email,$email_aprobacion_gerencia);
	}
	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_observacion_acuerdo_confidencialidad($array_email);

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];

	// if($correos_adjuntos != ""){
	// 	$validate = true;
	// 	$emails = preg_split('[,|;]',$correos_adjuntos);
	// 	foreach($emails as $e){
	// 	    if(preg_match('/^[_ña-z0-9-]+(\.[_ña-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/',trim($e)) == 0){
	// 			$error_msg = "'" . $e . "'" ." no es un Correo Adjunto válido";
	// 	        $result["http_code"] = 500;
	// 			$result["status"] = "error";
	// 			$result["mensaje"] = $error_msg;
	// 			echo json_encode($result);
	// 			die;
	// 	    }
	// 	    else
	// 	    {
	// 			$cc[] = $e;
	// 	    }
	// 	}
	// }

	$request = [
		"subject" => "Gestion - Sistema Contratos - Observación en la Solicitud de Acuerdo de Confidencialidad: Código - " .$sigla_correlativo.$codigo_correlativo,
		//"subject" => "Solicitud de proveedor",
		//"subject" => "Solicitud de adenda",
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc)
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc)
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();
		//return true;

	}
	catch (Exception $e)
	{
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}

}

if (isset($_POST["post_archivo_req_solicitud_arrendamiento"]))
{
	$message = "";
	$status = false;

	$area_id = $login ? $login['area_id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;

	$nombre_archivo_guardar = "";

	$id_archivo = $_POST["id_archivo"];
	$contrato_id = $_POST["contrato_id"];
	$contrato_detalle_id = isset($_POST["contrato_detalle_id"]) && !Empty($_POST["contrato_detalle_id"]) ? $_POST["contrato_detalle_id"]:0;
	$id_tipo_archivo = $_POST["id_tipo_archivo"];
	$id_representante_legal = $_POST["id_representante_legal"];

	$tipo_archivo = "";
	$archivo_anterior = "Sin asignar";
	$archivo_nuevo = "";
	$adenda_id = 0;
	$sql = "
	SELECT
		tipo_contrato_id
	FROM
		cont_contrato
	WHERE
	contrato_id = $contrato_id
	";

	$query = $mysqli->query($sql);
	$row_count = $query->num_rows;
	if ($row_count == 1) {
		$sel = $query->fetch_assoc();
		$tipo_contrato_id = $sel['tipo_contrato_id'];
		if ($tipo_contrato_id == 2) {
			$path = "/var/www/html/files_bucket/contratos/solicitudes/proveedores/";
		} else if ($tipo_contrato_id == 1) {
			$path = "/var/www/html/files_bucket/contratos/solicitudes/locales/";
		} else if ($tipo_contrato_id == 6) {
			$path = "/var/www/html/files_bucket/contratos/solicitudes/agentes/";
		} else if ($tipo_contrato_id == 7) {
			$path = "/var/www/html/files_bucket/contratos/solicitudes/contratos_internos/";
		} else if ($tipo_contrato_id == 5) {
			$path = "/var/www/html/files_bucket/contratos/solicitudes/acuerdos/";
		} else {
			$path = "/var/www/html/files_bucket/contratos/solicitudes/locales/";
		}
	} else {
		$path = "/var/www/html/files_bucket/contratos/solicitudes/locales/";
	}

	if (isset($_FILES['fileArchivo_requisitos_arrendamiento']) && $_FILES['fileArchivo_requisitos_arrendamiento']['error'] === UPLOAD_ERR_OK)
	{

		if (!is_dir($path)) mkdir($path, 0777, true);

		$filename = $_FILES['fileArchivo_requisitos_arrendamiento']['name'];
		$filenametem = $_FILES['fileArchivo_requisitos_arrendamiento']['tmp_name'];
		$filesize = $_FILES['fileArchivo_requisitos_arrendamiento']['size'];
		$fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

		$valid_extensions = array('jpeg', 'jpg', 'png', 'pdf', 'doc', 'docx', 'odt', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', '7z', 'rar', 'zip');

		if(in_array($fileExt, $valid_extensions))
		{
			// SE ELIMINA EL EL ARCHIVO, EXISTA O NO EXISTA EL ARCHIVO, IGUAL EJECUTAMOS EL DELETE, PORQUE NO TIENE QUE EXISTIR
			// OTRO ARCHIVO, SOLO EXISTIRA EL QUE VAMOS A INSERTAR (LINEAS ABAJO)

			$comando_eliminar_archivo = "
			UPDATE cont_archivos
			SET
				status = 0
			WHERE
				archivo_id = $id_archivo
			";

			$mysqli->query($comando_eliminar_archivo);

			if ($id_archivo > 0) {
				$sql_archivo_ant = "
				SELECT a.nombre AS nombre_archivo, a.adenda_id
				FROM cont_archivos AS a
				WHERE a.archivo_id = ".$id_archivo;
				$list_query_arc = $mysqli->query($sql_archivo_ant);
				while ($row = $list_query_arc->fetch_assoc()) {
					$archivo_anterior = $row['nombre_archivo'];
					if($row['adenda_id'] != ""){
						$adenda_id = $row['adenda_id'];
					}
				}
			}

			$sql_archivo_tip_ant = "
			SELECT a.nombre_tipo_archivo
			FROM cont_tipo_archivos AS a
			WHERE a.tipo_archivo_id = ".$id_tipo_archivo;
			$list_query_tip_arc = $mysqli->query($sql_archivo_tip_ant);
			while ($row = $list_query_tip_arc->fetch_assoc()) {
				$tipo_archivo = $row['nombre_tipo_archivo'];
			}
			// SE INGRESA EL ARCHIVO COMO NUEVO, PORQUE NO EXISTE UN FILE.

			if ($id_tipo_archivo == 1)
			{
				$nombre_archivo_guardar = "_RUC_";
			}
			else if ($id_tipo_archivo == 2)
			{
				$nombre_archivo_guardar = "_VIG_";
			}
			else if ($id_tipo_archivo == 3)
			{
				$nombre_archivo_guardar = "_DNI_";
			}
			else if ($id_tipo_archivo == 8)
			{
				$nombre_archivo_guardar = "_PARTIDA_REGISTRAL_";
			}
			else if ($id_tipo_archivo == 9)
			{
				$nombre_archivo_guardar = "_RECIBO_AGUA_";
			}
			else if ($id_tipo_archivo == 10)
			{
				$nombre_archivo_guardar = "_RECIBO_LUZ_";
			}
			else if ($id_tipo_archivo == 11)
			{
				$nombre_archivo_guardar = "_DNI_PROPIETARIO_";
			}
			else if ($id_tipo_archivo == 12)
			{
				$nombre_archivo_guardar = "_VIGENCIA_PODER_";
			}
			else if ($id_tipo_archivo == 13)
			{
				$nombre_archivo_guardar = "_DNI_REPRESENTANTE_LEGAL_";
			}
			else if ($id_tipo_archivo == 14)
			{
				$nombre_archivo_guardar = "_HR_INMUEBLE_";
			}
			else if ($id_tipo_archivo == 15)
			{
				$nombre_archivo_guardar = "_PU_INMUEBLE_";
			}
			else if ($id_tipo_archivo == 19)
			{
				$nombre_archivo_guardar = "_FORMATO_CONTRATO_";
			}
			else {
				$nombre_archivo_guardar = "_OTROS_";
			}


			if ($nombre_archivo_guardar != "")
			{

				$nombre_archivo = $contrato_id . $nombre_archivo_guardar . date('YmdHis') . "." . $fileExt;
				move_uploaded_file($filenametem, $path . $nombre_archivo);

				$comando = "INSERT INTO cont_archivos
							(
								contrato_id,
								contrato_detalle_id,
								adenda_id,
								tipo_archivo_id,
								nombre,
								extension,
								ruta,
								size,
								status,
								user_created_id,
								created_at,
								user_updated_id,
								updated_at
							)
							VALUES
							(
								'" . $contrato_id . "',
								'" . $contrato_detalle_id . "',
								'" . $adenda_id . "',
								'" . $id_tipo_archivo . "',
								'" . $nombre_archivo . "',
								'" . $fileExt . "',
								'" . $path . "',
								'" . $filesize . "',
								'1',
								'".$login["id"]."',
								'" . date('Y-m-d H:i:s') . "',
								'".$login["id"]."',
								'" . date('Y-m-d H:i:s') . "'
							)";
				$mysqli->query($comando);
				$archivo_insert_id = mysqli_insert_id($mysqli);
				if($id_representante_legal != "" && $id_representante_legal != 0){
					if($id_tipo_archivo == 2){
						$comando_archivo_rl = "UPDATE cont_representantes_legales
									SET vigencia_archivo_id = " . $archivo_insert_id . "
									WHERE id = " . $id_representante_legal;
					}else if($id_tipo_archivo == 3){
						$comando_archivo_rl = "UPDATE cont_representantes_legales
									SET dni_archivo_id = " . $archivo_insert_id . "
									WHERE id = " . $id_representante_legal;
					}

					$mysqli->query($comando_archivo_rl);
				}



				if($adenda_id > 0){
					$comando_adenda = "UPDATE cont_adendas
					SET archivo_id = " . $archivo_insert_id . "
					WHERE id = " . $adenda_id;
					$mysqli->query($comando_adenda);
				}






				$message = "Datos guardados correctamente";
				$archivo_nuevo = $nombre_archivo;
				$status = true;


				$data = array(
					'contrato_id' => $contrato_id,
					'tipo_archivo' => $tipo_archivo,
					'archivo_anterior' => $archivo_anterior,
					'archivo_nuevo' => $archivo_nuevo,
					'archivo_insert_id' => $archivo_insert_id,
				);

				if ( !( $area_id == 33 && $cargo_id != 25 ) ) {
					send_email_cambio_anexo_contrato($data);
				}
			}
			else
			{
				$message = "El tipo de archivo a guardar no fue identificado, por favor consulte con el area de sistemas";
				$status = false;
			}
		}
		else
		{
			$message = "La extension del file no es aceptado, solo son aceptado: jpeg, jpg, png, pdf";
			$status = false;
		}
	}
	else
	{
		$message = "Tiene que seleccionar un archivo :)";
		$status = false;
	}

	$result["status"] = $status;
	$result["message"] = $message;
}

if (isset($_POST["accion"]) && $_POST["accion"]==="editar_solicitud") {
	$usuario_id = $login?$login['id']:null;
	$area_id = $login ? $login['area_id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;

	if ((int) $usuario_id > 0) {
		$created_at = date("Y-m-d H:i:s");
		$contrato_id = $_POST["contrato_id"];
		$nombre_tabla = trim($_POST["nombre_tabla"]);
		$valor_original = trim($_POST["valor_original"]);
		$nombre_campo = trim($_POST["nombre_campo"]);
		$nombre_menu_usuario = trim($_POST["nombre_menu_usuario"]);
		$nombre_campo_usuario = trim($_POST["nombre_campo_usuario"]);
		$tipo_valor = trim($_POST["tipo_valor"]);

		$query_contrato = "SELECT c.tipo_contrato_id, IFNULL(c.aprobado_por,0) AS aprobado_por  FROM cont_contrato AS c WHERE c.contrato_id = ". $contrato_id;
		$data_contrato = $mysqli->query($query_contrato);
		$data_contrato = $data_contrato->fetch_assoc();
		
		if ($tipo_valor == 'varchar') {
			$valor_varchar = "'" . replace_invalid_caracters(trim($_POST["valor_varchar"]))  . "'";
			$valor_int = "NULL";
			$valor_date = "NULL";
			$valor_decimal = "NULL";
			$valor_select_option = "NULL";
			$valor_id_tabla = "NULL";
		}else if ($tipo_valor == 'textarea') {
			$tipo_valor = 'varchar';
			$valor_varchar = "'" . replace_invalid_caracters(trim($_POST["valor_textarea"]))  . "'";
			$valor_int = "NULL";
			$valor_date = "NULL";
			$valor_decimal = "NULL";
			$valor_select_option = "NULL";
			$valor_id_tabla = "NULL";
		} else if ($tipo_valor == 'int') {
			$valor_varchar = "NULL";
			$valor_int = trim($_POST["valor_int"]);
			$valor_date = "NULL";
			$valor_decimal = "NULL";
			$valor_select_option = "NULL";
			$valor_id_tabla = "NULL";
		} else if ($tipo_valor == 'date') {
			$valor_varchar = "NULL";
			$valor_int = "NULL";
			$valor_original = str_replace('/', '-', $valor_original);
			$valor_original = date("Y-m-d", strtotime($valor_original));
			$valor_date = "'" . date("Y-m-d", strtotime(trim($_POST["valor_date"]))) . "'";
			$valor_decimal = "NULL";
			$valor_select_option = "NULL";
			$valor_id_tabla = "NULL";
		} else if ($tipo_valor == 'decimal') {
			$valor_varchar = "NULL";
			$valor_int = "NULL";
			$valor_date = "NULL";
			$valor_decimal = str_replace(",","",trim($_POST["valor_decimal"]));
			$valor_select_option = "NULL";
			$valor_id_tabla = "NULL";
		} else if ($tipo_valor == 'select_option') {
			$valor_varchar = "NULL";
			$valor_int = trim($_POST["valor_select_option_id"]);
			$valor_date = "NULL";
			$valor_decimal = "NULL";
			$valor_select_option = trim($_POST["valor_select_option"]);
			$valor_id_tabla = "NULL";
		} else if ($tipo_valor == 'id_tabla') {
			$valor_varchar = "NULL";
			$valor_int = trim($_POST["valor_int"]);
			$valor_date = "NULL";
			$valor_decimal = "NULL";
			$valor_select_option = "NULL";
			$valor_id_tabla = trim($_POST["valor_id_tabla"]);
		}

		if($tipo_valor == 'select_option' && $nombre_tabla == 'cont_adelantos'){
			$valor_int = "NULL";
		} elseif ($tipo_valor == 'select_option' && $nombre_campo == 'ubigeo_id'){
			$valor_int = "NULL";
			$valor_select_option = trim($_POST["ubigeo_text_nuevo"]);
		} elseif ($tipo_valor == 'varchar' && $nombre_campo == 'cc_id') {
			if (!empty(trim($_POST["valor_varchar"]))) {
				$query_centro_de_costo = "
				SELECT
					nombre
				FROM
					tbl_locales
				WHERE
					cc_id = $valor_varchar
				";

				$query = $mysqli->query($query_centro_de_costo);
				$row_count = $query->num_rows;

				if ($row_count > 0) {
					$row = $query->fetch_assoc();
					$nombre_local = $row["nombre"];

					$result["http_code"] = 400;
					$result["error"] = 'El local "' . $nombre_local . '" posee el centro de costos "' . trim($_POST["valor_varchar"]) . '"';
					exit(json_encode($result));
				}
			}
		} elseif ($tipo_valor == 'varchar' && $nombre_campo == 'nombre_tienda') {
			if (!empty(trim($_POST["valor_varchar"]))) {

				$nombre_tienda = sec_contrato_detalle_solicitudv2_formato_nombre_de_tienda(replace_invalid_caracters(trim($_POST["valor_varchar"])));

				$valor_varchar = "'" . $nombre_tienda . "'";
			}
		}

		$query_insert = "
		INSERT INTO cont_auditoria
		(
		contrato_id,
		nombre_tabla,
		valor_original,
		nombre_campo,
		nombre_menu_usuario,
		nombre_campo_usuario,
		tipo_valor,
		valor_varchar,
		valor_int,
		valor_date,
		valor_decimal,
		valor_select_option,
		valor_id_tabla,
		user_created_id,
		created_at
		)
		VALUES
		(
		" . $contrato_id . ",
		'" . $nombre_tabla . "',
		'" . replace_invalid_caracters($valor_original) . "',
		'" . $nombre_campo . "',
		'" . $nombre_menu_usuario . "',
		'" . $nombre_campo_usuario . "',
		'" . $tipo_valor . "',
		" . $valor_varchar . ",
		" . $valor_int . ",
		" . $valor_date . ",
		" . $valor_decimal . ",
		'" . $valor_select_option . "',
		" . $valor_id_tabla . ",
		" . $usuario_id . ",
		'" . $created_at . "'
		)";

		if($tipo_valor == 'select_option' && trim($_POST["nombre_tabla"]) == 'cont_adelantos'){
			$tipo_valor = 'varchar';
			$valor_varchar = "'" . trim($_POST["valor_select_option_id"]) . "'";
		} elseif($tipo_valor == 'select_option' && $nombre_campo == 'ubigeo_id'){
			$tipo_valor = 'varchar';
			$valor_varchar = "'" . trim($_POST["ubigeo_id_nuevo"])  . "'";
		} elseif ($tipo_valor == 'varchar' && $nombre_campo == 'nombre_tienda') {
			$select_etapa = "
			SELECT
				etapa_id
			FROM
				cont_contrato
			WHERE
				contrato_id = $contrato_id
			";

			$query = $mysqli->query($select_etapa);
			$row = $query->fetch_assoc();
			$etapa_id = $row["etapa_id"];

			if ($etapa_id != 1) {
				$select_local_id = "
				SELECT
					id
				FROM
					tbl_locales
				WHERE
					contrato_id = $contrato_id
				";

				$query = $mysqli->query($select_local_id);
				$row_count = $query->num_rows;

				if ($row_count == 1) {
					$row = $query->fetch_assoc();
					$local_id = $row["id"];

					$update_nombre_del_local = "
					UPDATE
						tbl_locales
					SET
						nombre = $valor_varchar
					WHERE
						id = $local_id
					";

					$mysqli->query($update_nombre_del_local);
				}
			}
		} elseif ($nombre_tabla == 'cont_condicion_economica' && $nombre_campo == 'fecha_fin' && (int) $data_contrato['tipo_contrato_id'] == 1) {
			$update_alerta = "UPDATE cont_contrato SET alerta_enviada_id = NULL WHERE contrato_id = ".$contrato_id;
			$resultado = $mysqli->query($update_alerta);	
		} elseif ($nombre_tabla == 'cont_contrato' && $nombre_campo == 'fecha_vencimiento_proveedor') {
			if ((int) $data_contrato['tipo_contrato_id'] == 2 || (int) $data_contrato['tipo_contrato_id'] == 5){
				$update_alerta = "UPDATE cont_contrato SET alerta_enviada_id = NULL WHERE contrato_id = ".$contrato_id;
				$resultado = $mysqli->query($update_alerta);	
			}
		} elseif ($nombre_tabla == 'cont_contrato' && $nombre_campo == 'fecha_fin_agente' && (int) $data_contrato['tipo_contrato_id'] == 6) {
				$update_alerta = "UPDATE cont_contrato SET alerta_enviada_id = NULL WHERE contrato_id = ".$contrato_id;
				$resultado = $mysqli->query($update_alerta);	
		} elseif ($tipo_valor == 'select_option' && ($nombre_campo == 'persona_responsable_id' || $nombre_campo == 'jefe_comercial_id')) {
			$usuario_comercial_nuevo_id = $valor_int;

			$select_usuario_antiguo = "
			SELECT
				$nombre_campo,
				etapa_id
			FROM
				cont_contrato
			WHERE
				contrato_id = $contrato_id
			";

			$resultado = $mysqli->query($select_usuario_antiguo);
			$fila = $resultado->fetch_row();
			$usuario_comercial_antiguo_id = $fila[0];
			$etapa_id = $fila[1];

			if ($etapa_id != 1) {
				$select_local_id = "
				SELECT
					id
				FROM
					tbl_locales
				WHERE
					contrato_id = $contrato_id
				";

				$query = $mysqli->query($select_local_id);
				$row_count = $query->num_rows;
				$created_at = date('Y-m-d H:i:s');
				if ($row_count == 1) {
					$row = $query->fetch_assoc();
					$local_id = $row["id"];

					if ($valor_original != 'Sin asignar' && $usuario_comercial_antiguo_id > 0) {
						$update_usuario_comercial_antiguo_quitar_local = "
						UPDATE
							tbl_usuarios_locales
						SET
							estado = 0,
							user_updated_id = $usuario_id,
							updated_at = '".$created_at."'
						WHERE
							local_id = $local_id
							AND usuario_id = $usuario_comercial_antiguo_id
						";

						$mysqli->query($update_usuario_comercial_antiguo_quitar_local);
					}

					$select_usuario_comercial_nuevo_existe = "
					SELECT
						id
					FROM
						tbl_usuarios_locales
					WHERE
						local_id = $local_id
						AND usuario_id = $usuario_comercial_nuevo_id
					";

					$query = $mysqli->query($select_usuario_comercial_nuevo_existe);
					$row_count = $query->num_rows;

					if ($row_count == 1) {
						$update_usuario_comercial_ya_existente_al_local = "
						UPDATE
							tbl_usuarios_locales
						SET
							estado = 1,
							user_updated_id = $usuario_id,
							updated_at = '".$created_at."'
						WHERE
							local_id = $local_id
							AND usuario_id = $usuario_comercial_nuevo_id
						";

						$mysqli->query($update_usuario_comercial_ya_existente_al_local);
					} elseif ($row_count == 0) {
						$insert_usuario_comercial_al_local = "
						INSERT INTO tbl_usuarios_locales (
							usuario_id,
							local_id,
							estado,
							user_created_id,
							created_at
						) VALUES (
							$usuario_comercial_nuevo_id,
							$local_id,
							1,
							$usuario_id,
							'".$created_at."'
						)";

						$mysqli->query($insert_usuario_comercial_al_local);
					}
				}
			}
		}

		$mysqli->query($query_insert);
		$insert_id = mysqli_insert_id($mysqli);
		$error = '';
		if($mysqli->error){
			$result["insert_error"] = $mysqli->error . $query_insert;
		} else {
			
			$nombre_tabla = trim($_POST["nombre_tabla"]);
			$nombre_campo = trim($_POST["nombre_campo"]);
			$id_tabla = trim($_POST["id_tabla"]);

			if ($tipo_valor == 'varchar') {
				$nuevo_valor = $valor_varchar;
			} else if ($tipo_valor == 'int') {
				$nuevo_valor = $valor_int;
			} else if ($tipo_valor == 'date') {
				$nuevo_valor = $valor_date;
			} else if ($tipo_valor == 'decimal') {
				$nuevo_valor = $valor_decimal;
			} else if ($tipo_valor == 'select_option') {
				$nuevo_valor = $valor_int;
			}

			if ($id_tabla > 0) {
				if($nombre_tabla == "cont_condicion_economica"){
					$query_id_tabla = 'condicion_economica_id';
				}else if($nombre_tabla == 'cont_mutuodinero'){
					$query_id_tabla = 'idmutuodinero';
				
				}else if($nombre_tabla == 'cont_contraprestacion'){
					$query_id_tabla = 'moneda_id';
				}else if($nombre_tabla == 'cont_beneficiarios'){
					$query_id_tabla = 'contrato_id';		
				}else if($nombre_tabla == 'cont_contrato'){
				$query_id_tabla = 'contrato_id';
				}
				else{
					$query_id_tabla = 'id';
				}
				$valor_id_tabla = $id_tabla;
			} else {
				$query_id_tabla = 'contrato_id';
				$valor_id_tabla = $contrato_id;
			}
			$requiere_aprobacion = 0;
			if (empty($nombre_tabla) || empty($nombre_campo) || empty($nuevo_valor) || empty($query_id_tabla) || empty($valor_id_tabla)) {
				$result["update_error"] = 'Algunos del los campos esta vacío';
			} else if (substr($nombre_tabla,0,4) != 'cont' && substr($nombre_tabla, 0,4) != 'tbl_') {
				$result["update_error"] = 'La tabla a actualizar no pertenece al sistema de contratos';
			} else {

			
				// INICIO DE CAMBIOS QUE REQUIERAN APROBACION DEL DIRECTOR
				$cambios_requiere_aprobacion = array(
					'cont_contraprestacion' => ['subtotal','igv','monto']  /// nombre_tabla => [columnas]
				);

				if ($data_contrato['aprobado_por'] > 0 && isset($cambios_requiere_aprobacion[$nombre_tabla]) && in_array($nombre_campo, $cambios_requiere_aprobacion[$nombre_tabla])) { // Si requiere aprobacion

					$query_update_auditoria = "UPDATE cont_auditoria SET status = 0 WHERE id = ".$insert_id;
					$mysqli->query($query_update_auditoria);

					$query_insert_cambio = "
					INSERT INTO cont_contrato_cambios (
						auditoria_id,
						director_id,
						estado_aprobacion,
						contrato_id,
						nombre_tabla,
						valor_original,
						nombre_campo,
						nombre_menu_usuario,
						nombre_campo_usuario,
						tipo_valor,
						valor_varchar,
						valor_int,
						valor_date,
						valor_decimal,
						valor_select_option,
						valor_id_tabla,
						status,
						user_created_id,
						created_at
					)VALUES(
						".$insert_id.",
						".$data_contrato['aprobado_por'].",
						1,
						" . $contrato_id . ",
						'" . $nombre_tabla . "',
						'" . replace_invalid_caracters($valor_original) . "',
						'" . $nombre_campo . "',
						'" . $nombre_menu_usuario . "',
						'" . $nombre_campo_usuario . "',
						'" . $tipo_valor . "',
						" . $valor_varchar . ",
						" . $valor_int . ",
						" . $valor_date . ",
						" . $valor_decimal . ",
						'" . $valor_select_option . "',
						" . $valor_id_tabla . ",
						1,
						" . $usuario_id . ",
						'" . $created_at . "'
					)";
					$mysqli->query($query_insert_cambio);
					$cambio_id = mysqli_insert_id($mysqli);

					send_email_cambio_contrato_confirmacion($cambio_id);
					$requiere_aprobacion = 1;

				}else{ // No requiere aprobacion

					$query_update = "
					UPDATE " . $nombre_tabla . "
					SET
						" . $nombre_campo . " = " . $nuevo_valor . "
					WHERE " . $query_id_tabla . " = '" . $valor_id_tabla . "'";
					$mysqli->query($query_update);

					if($mysqli->error){
						$result["update_error"] = $mysqli->error . $query_update;
					}

					//OBTENER EL IDE DEL DETALLE DE CAMBIO
					$query_cont_detalle = "SELECT contrato_detalle_id FROM $nombre_tabla WHERE $query_id_tabla = $valor_id_tabla";
					$result_cont_detalle = $mysqli->query($query_cont_detalle);
					if($result_cont_detalle){
						$row_count = $result_cont_detalle->num_rows;
						if ($row_count > 0){
							$data_detalle = $result_cont_detalle->fetch_assoc();
							$query_update = "
							UPDATE cont_auditoria
							SET
								contrato_detalle_id = " . $data_detalle['contrato_detalle_id'] . "
							WHERE id = '" . $insert_id . "'";
							$mysqli->query($query_update);
						}
					}

					// if($nombre_campo != 'abogado_id'){
					// 	send_email_cambio_contrato($insert_id);
					// }
					// $requiere_aprobacion = 0;
				}
				// FIN DE CAMBIOS QUE REQUIERAN APROBACION DEL DIRECTOR

				
			}
		}

		$result["result"] = 'ok';
		$result["http_code"] = 200;
		$result["requiere_aprobacion"] = $requiere_aprobacion;
		$result["status"] = "Datos obtenidos de gestion.";
	} else {
		$result["http_code"] = 400;
		$result["error"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y volver a hacer clic en el botón: Editar.";
	}
}


function guardar_cambios_personalizados_en_auditoria($contrato_id, $nombre_tabla, $mensaje, $nombre_campo, $nombre_menu_usuario, $nombre_campo_usuario, $valor_nuevo,$contrato_detalle_id = 0) {
	include("db_connect.php");
	include("sys_login.php");

	$usuario_id = $login?$login['id']:null;
	$created_at = "'" . date("Y-m-d H:i:s") . "'";
	$contrato_id = $contrato_id;
	$contrato_detalle_id = $contrato_detalle_id;
	$nombre_tabla = "'" . $nombre_tabla . "'";
	$valor_original = "'No existía. " . $mensaje . "'";
	$nombre_campo = "'" . $nombre_campo . "'";
	$nombre_menu_usuario = "'" . $nombre_menu_usuario. "'";
	$nombre_campo_usuario = "'" . $nombre_campo_usuario . "'";
	$tipo_valor = "'varchar'";
	$valor_nuevo = "'" . $valor_nuevo . "'";

	$query_insert = "
	INSERT INTO cont_auditoria(
		contrato_id,
		contrato_detalle_id,
		nombre_tabla,
		valor_original,
		nombre_campo,
		nombre_menu_usuario,
		nombre_campo_usuario,
		tipo_valor,
		valor_varchar,
		valor_int,
		valor_date,
		valor_decimal,
		valor_select_option,
		valor_id_tabla,
		user_created_id,
		created_at
	) VALUES (
		$contrato_id,
		$contrato_detalle_id,
		$nombre_tabla,
		$valor_original,
		$nombre_campo,
		$nombre_menu_usuario,
		$nombre_campo_usuario,
		$tipo_valor,
		$valor_nuevo,
		NULL,
		NULL,
		NULL,
		NULL,
		NULL,
		$usuario_id,
		$created_at
	)";

	$mysqli->query($query_insert);

	if($mysqli->error){
		return $mysqli->error;
	} else {
		return 'ok';
	}

}


if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_adenda_firmada") {
	$usuario_id = $login?$login['id']:null;

	if ( !((int) $usuario_id > 0) ) {
		$result["http_code"] = 500;
		$result["status"] = "error";
		$result["mensaje"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y hacer click e el boton verde: Agregar adenda firmada.";
		echo json_encode($result);
		die;
	}

	$created_at = date("Y-m-d H:i:s");
	$contrato_id = $_POST["contrato_id"];
	$adenda_id = $_POST["adenda_id"];
	
	$path = "/var/www/html/files_bucket/contratos/adendas/locales/";
	$name_file = 'adenda_firmada_'.$_POST["adenda_id"];
	$archivo_id = 0;
	if (isset($_FILES[$name_file]) && $_FILES[$name_file]['error'] === UPLOAD_ERR_OK) {
		if (!is_dir($path)) mkdir($path, 0777, true);

		$filename = $_FILES[$name_file]['name'];
		$filenametem = $_FILES[$name_file]['tmp_name'];
		$filesize = $_FILES[$name_file]['size'];
		$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
		if($filename != ""){
			$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
			$nombre_archivo = $contrato_id . "_ADENDA_FIRMADA_" . date('YmdHis') . "." . $fileExt;
			move_uploaded_file($filenametem, $path . $nombre_archivo);
			$comando = "INSERT INTO cont_archivos (
							contrato_id,
							adenda_id,
							tipo_archivo_id,
							nombre,
							extension,
							size,
							ruta,
							user_created_id,
							created_at)
						VALUES(
							'" . $contrato_id . "',
							'" . $_POST["adenda_id"] . "',
							17,
							'" . $nombre_archivo . "',
							'" . $fileExt . "',
							'" . $filesize . "',
							'" . $path . "',
							" . $usuario_id . ",
							'" . $created_at . "'
							)";
			$mysqli->query($comando);
			$archivo_id = mysqli_insert_id($mysqli);
		}
	}

	$query = $mysqli->query("
	SELECT contrato_id
	FROM cont_adendas
	WHERE id = " . $_POST["adenda_id"] . "
	AND status = 1
	AND 'procesado' = 0");
	$row_count = $query->num_rows;
	if ($row_count > 0) {
		$row = $query->fetch_assoc();
		$contrato_id = $row["contrato_id"];

		$query = $mysqli->query("
		SELECT id,
			adenda_id,
			contrato_detalle_id,
			nombre_tabla,
			valor_original,
			nombre_campo_usuario,
			nombre_menu_usuario,
			nombre_campo,
			tipo_valor,
			valor_varchar,
			valor_int,
			valor_date,
			valor_decimal,
			valor_select_option,
			valor_id_tabla,
			id_del_registro_a_modificar,
			status
		FROM cont_adendas_detalle
		WHERE adenda_id = " . $_POST["adenda_id"] . "
		AND status = 1
		");
		$row_count = $query->num_rows;
		if ($row_count > 0) {
			while($row = $query->fetch_assoc()){
				$nombre_tabla = $row["nombre_tabla"];
				$nombre_menu_usuario = $row["nombre_menu_usuario"];
				$nombre_campo = $row["nombre_campo"];
				$valor_original = $row["valor_original"];
				$valor_id_tabla = $row['valor_id_tabla'];
				$id_del_registro_a_modificar = $row['id_del_registro_a_modificar'];
				$tipo_valor = $row["tipo_valor"];
				if ($tipo_valor == 'varchar') {
					$nuevo_valor = $row['valor_varchar'];
				} else if ($tipo_valor == 'int') {
					$nuevo_valor = $row['valor_int'];
				} else if ($tipo_valor == 'date') {
					$nuevo_valor = $row['valor_date'];
				} else if ($tipo_valor == 'decimal') {
					$nuevo_valor = $row['valor_decimal'];
				} else if ($tipo_valor == 'select_option') {
					if($nombre_campo == "ubigeo_id"){
						$nuevo_valor = $row['valor_varchar'];
					}else{
						$nuevo_valor = $row['valor_int'];
					}
				} else if ($tipo_valor == 'id_tabla') {
					$nuevo_valor = $row['valor_int'];
				} else if ($tipo_valor == 'registro') {
					$nuevo_valor = $row['valor_int'];
				} else if ($tipo_valor == 'eliminar') {
					$nuevo_valor = $row['valor_int'];
				}

				if ($tipo_valor == 'id_tabla'){
					
					if ($nombre_tabla == 'cont_propietario') {
						
						$query_update = "
						UPDATE " . $nombre_tabla . "
						SET
							persona_id = '" . $nuevo_valor . "'
						WHERE propietario_id = '" . $valor_id_tabla . "'";
						$mysqli->query($query_update);

					} else if ($nombre_tabla == 'cont_beneficiarios' || $nombre_tabla == 'cont_responsable_ir') {
						$query_update = "
						UPDATE " . $nombre_tabla . "
						SET
							status = 0
						WHERE id = '" . $valor_original . "'";
						$mysqli->query($query_update);

						$query_update = "
						UPDATE " . $nombre_tabla . "
						SET
							status = 1
						WHERE id = '" . $nuevo_valor . "'";
						$mysqli->query($query_update);

					} else if ($nombre_tabla == 'cont_incrementos') {
						$query_update = "
						UPDATE " . $nombre_tabla . "
						SET
							estado = 0
						WHERE id = '" . $valor_original . "'";
						$mysqli->query($query_update);

						$query_update = "
						UPDATE " . $nombre_tabla . "
						SET
							estado = 1
						WHERE id = '" . $nuevo_valor . "'";
						$mysqli->query($query_update);
					}
				} else if ($tipo_valor == 'registro'){
					//En caso de nuevos registros en la adenda solo se necesita activar el estado a 1
					if ($nombre_menu_usuario == 'Propietario'){
						$query_update = "
						UPDATE " . $nombre_tabla . " 
						SET " . $nombre_campo . " = ".$contrato_id."
						WHERE propietario_id = '" . $nuevo_valor . "'";
						$mysqli->query($query_update);
					}else if ($nombre_menu_usuario == 'Beneficiario' || $nombre_menu_usuario == 'Inflación' || $nombre_menu_usuario == 'Cuota Extraordinaria' || $nombre_menu_usuario == 'Suministro' || $nombre_menu_usuario == 'Responsable IR' ){
						$query_update = "
						UPDATE " . $nombre_tabla . "
						SET " . $nombre_campo . " = '1'
						WHERE id = '" . $nuevo_valor . "'";
						$mysqli->query($query_update);
					}else if ($nombre_menu_usuario == 'Incremento'){
						$query_update = "
						UPDATE " . $nombre_tabla . "
						SET estado = '1'
						WHERE id = '" . $nuevo_valor . "'";
						$mysqli->query($query_update);
					}
				} else if ($tipo_valor == 'eliminar'){

					if ($nombre_menu_usuario == 'Propietario'){
						$query_update = "UPDATE cont_propietario SET status = 0 WHERE propietario_id = " . $nuevo_valor;
						$mysqli->query($query_update);
					}else if ($nombre_menu_usuario == 'Beneficiario'){
						$query_update = "UPDATE cont_beneficiarios SET status = 0 WHERE id = " . $nuevo_valor;
						$mysqli->query($query_update);
					}else if ($nombre_menu_usuario == 'Incremento'){
						$query_update = "UPDATE cont_incrementos SET estado = 0 WHERE id = " . $nuevo_valor;
						$mysqli->query($query_update);
					}else if ($nombre_menu_usuario == 'Inflación'){
						$query_update = "UPDATE cont_inflaciones SET status = 0 WHERE id = " . $nuevo_valor;
						$mysqli->query($query_update);
					}else if ($nombre_menu_usuario == 'Cuota Extraordinaria'){
						$query_update = "UPDATE cont_cuotas_extraordinarias SET status = 0 WHERE id = " . $nuevo_valor;
						$mysqli->query($query_update);
					}else if ($nombre_menu_usuario == 'Responsable IR'){
						$query_update = "UPDATE cont_responsable_ir SET status = 0 WHERE id = " . $nuevo_valor;
						$mysqli->query($query_update);
					}else if ($nombre_menu_usuario == 'Suministro'){
						$query_update = "UPDATE cont_inmueble_suministros SET status = 0 WHERE id = " . $nuevo_valor;
						$mysqli->query($query_update);
					}
				}else {
					if($nombre_tabla == 'cont_persona'){
						$query_update = "
						UPDATE cont_propietario AS p
						INNER JOIN cont_persona AS per ON per.id = p.persona_id
						SET
						per." . $nombre_campo . " = '" . $nuevo_valor . "'
						WHERE p.contrato_id = '" . $contrato_id . "'";
					}else if ($nombre_tabla == 'cont_inmueble' || $nombre_tabla == 'cont_inmueble_suministros'
						|| $nombre_tabla == 'cont_inmueble_suministros' || $nombre_tabla == 'cont_inflaciones' || 
						$nombre_tabla == 'cont_cuotas_extraordinarias' || $nombre_tabla == 'cont_beneficiarios'
						|| $nombre_tabla == 'cont_responsable_ir' || $nombre_tabla == 'cont_adendas_detalle'){ 
						$query_update = "
						UPDATE " . $nombre_tabla . "
						SET
							" . $nombre_campo . " = '" . $nuevo_valor . "'
						WHERE id = '" . $id_del_registro_a_modificar . "'";
					}else if($nombre_tabla == 'cont_condicion_economica') {
						if ($nombre_campo == 'fecha_fin'){
							$update_alerta = "UPDATE cont_contrato SET alerta_enviada_id = NULL WHERE contrato_id = ".$contrato_id;
							$mysqli->query($update_alerta);	
						}
						$query_update = "
						UPDATE " . $nombre_tabla . "
						SET
							" . $nombre_campo . " = '" . $nuevo_valor . "'
						WHERE condicion_economica_id = '" . $id_del_registro_a_modificar . "'";
					}else if($nombre_tabla == 'cont_contrato_detalle'){
						$query_update = "
						UPDATE " . $nombre_tabla . "
						SET
							" . $nombre_campo . " = '" . $nuevo_valor . "'
						WHERE id = '" . $id_del_registro_a_modificar . "'";
					}else if($nombre_tabla == 'cont_mutuodinero'){
						$query_update = "
						UPDATE " . $nombre_tabla . "
						SET
							" . $nombre_campo . " = '" . $nuevo_valor . "'
						WHERE idmutuodinero = '" . $id_del_registro_a_modificar . "'";
					}
					else if($nombre_tabla == 'cont_contrato' && ($nombre_campo == 'persona_responsable_id' || $nombre_campo == 'jefe_comercial_id')){
						$usuario_comercial_nuevo_id = $nuevo_valor;
	
						$select_usuario_antiguo = "
						SELECT
							$nombre_campo,
							etapa_id
						FROM
							cont_contrato
						WHERE
							contrato_id = $contrato_id
						";
			
						$resultado_antiguo = $mysqli->query($select_usuario_antiguo);
						$fila = $resultado_antiguo->fetch_row();
						$usuario_comercial_antiguo_id = $fila[0];
						$etapa_id = $fila[1];
			
						if ($etapa_id != 1) {
							$select_local_id = "
							SELECT
								id
							FROM
								tbl_locales
							WHERE
								contrato_id = $contrato_id
							";
			
							$query_select_local_id = $mysqli->query($select_local_id);
							$row_count = $query_select_local_id->num_rows;
							$created_at = date('Y-m-d H:i:s');
			
							if ($row_count == 1) {
								$row = $query_select_local_id->fetch_assoc();
								$local_id = $row["id"];
			
								if ($valor_original != 'Sin asignar' && $usuario_comercial_antiguo_id > 0) {
									$update_usuario_comercial_antiguo_quitar_local = "
									UPDATE
										tbl_usuarios_locales
									SET
										estado = 0,
										user_updated_id = $usuario_id,
										updated_at = '".$created_at."'
									WHERE
										local_id = $local_id
										AND usuario_id = $usuario_comercial_antiguo_id
									";
			
									$mysqli->query($update_usuario_comercial_antiguo_quitar_local);
								}
			
								$select_usuario_comercial_nuevo_existe = "
								SELECT
									id
								FROM
									tbl_usuarios_locales
								WHERE
									local_id = $local_id
									AND usuario_id = $usuario_comercial_nuevo_id
								";
			
								$query_usuario_com_exis = $mysqli->query($select_usuario_comercial_nuevo_existe);
								$row_count = $query_usuario_com_exis->num_rows;
								if ($row_count == 1) {
									$update_usuario_comercial_ya_existente_al_local = "
									UPDATE
										tbl_usuarios_locales
									SET
										estado = 1 ,
										user_updated_id = $usuario_id,
										updated_at = '".$created_at."'
									WHERE
										local_id = $local_id
										AND usuario_id = $usuario_comercial_nuevo_id
									";
			
									$mysqli->query($update_usuario_comercial_ya_existente_al_local);
								} elseif ($row_count == 0) {
									$insert_usuario_comercial_al_local = "
									INSERT INTO tbl_usuarios_locales (
										usuario_id,
										local_id,
										estado,
										user_created_id,
										created_at
									) VALUES (
										$usuario_comercial_nuevo_id,
										$local_id,
										1,
										$usuario_id,
										'".$created_at."'
									)";
			
									$mysqli->query($insert_usuario_comercial_al_local);
								}
								// ACTUALIZAMOS LA TABLA CONT_CONTRATO CONEL NUEVO USUARIO COMERCIAL
								$query_update = "
								UPDATE " . $nombre_tabla . "
								SET
									" . $nombre_campo . " = '" . $nuevo_valor . "'
								WHERE contrato_id = ".$contrato_id;
							}
						}
						
	
					}else if($nombre_tabla == 'cont_contrato' && $nombre_campo == 'cc_id'){
							
								$select_local_id = "
								SELECT
									id
								FROM
									tbl_locales
								WHERE
									contrato_id = $contrato_id
								";
				
								$query_select_local_id = $mysqli->query($select_local_id);
								$row_count = $query_select_local_id->num_rows;
								if ($row_count > 0) {
									$row = $query_select_local_id->fetch_assoc();
									$local_id = $row["id"];

									$update_cc_id_local = "
									UPDATE
										tbl_locales
									SET
										cc_id = '" . $nuevo_valor . "'
									WHERE
										id = $local_id";
		
									$mysqli->query($update_cc_id_local);
								}
						// ACTUALIZAMOS LA TABLA CONT_CONTRATO CONEL NUEVO USUARIO COMERCIAL
						$query_update = "
						UPDATE " . $nombre_tabla . "
						SET
							" . $nombre_campo . " = '" . $nuevo_valor . "'
						WHERE contrato_id = ".$contrato_id;
					}else{
						$query_update = "
						UPDATE " . $nombre_tabla . "
						SET
							" . $nombre_campo . " = '" . $nuevo_valor . "'
						WHERE contrato_id = '" . $contrato_id . "'";
					}

					$mysqli->query($query_update);
				}
				if($mysqli->error){
					$result["update_error"] = $mysqli->error;
				}
			}

			$fecha_aplicacion = str_replace("/","-",$_POST["fecha_aplicacion"]);
			$fecha_aplicacion = date("Y-m-d", strtotime($fecha_aplicacion));
			$query_update = "
			UPDATE cont_adendas
			SET
				procesado = 1,
				archivo_id = ".$archivo_id.",
				fecha_de_ejecucion_del_cambio = '".$fecha_aplicacion."'
			WHERE id = '". $_POST["adenda_id"] ."'";
			$mysqli->query($query_update);

			send_email_adenda_contrato_arrendamiento_firmada($_POST["adenda_id"],false);

			if($mysqli->error){
				$result["update_error"] = $mysqli->error;
			}
		}
	}

	$result["result"] = 'ok';
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
}


if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_adenda_proveedor_firmada") {
	$usuario_id = $login?$login['id']:null;

	if ( !((int) $usuario_id > 0) ) {
		$result["http_code"] = 500;
		$result["status"] = "error";
		$result["mensaje"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y hacer click e el boton verde: Agregar adenda firmada.";
		echo json_encode($result);
		die;
	}

	$created_at = date("Y-m-d H:i:s");
	$contrato_id = $_POST["contrato_id"];
	$adenda_id = $_POST["adenda_id"];

	// $query = "SELECT a.abogado_id FROM cont_adendas a WHERE a.id = $adenda_id";
	// $list_query = $mysqli->query($query);
	// $row = $list_query->fetch_assoc();

	// if (empty($row['abogado_id'])) {
	// 	$result["http_code"] = 400;
	// 	$result["error"] = 'sin_asignar';
	// 	$result["campo_incompleto"] = 'abogado';
	// 	exit(json_encode($result));
	// }

	$path = "/var/www/html/files_bucket/contratos/adendas/proveedores/";
	$name_file = 'adenda_firmada_'.$_POST["adenda_id"];
	$archivo_id = 0;
	if (isset($_FILES[$name_file]) && $_FILES[$name_file]['error'] === UPLOAD_ERR_OK) {
		if (!is_dir($path)) mkdir($path, 0777, true);

		$filename = $_FILES[$name_file]['name'];
		$filenametem = $_FILES[$name_file]['tmp_name'];
		$filesize = $_FILES[$name_file]['size'];
		$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
		if($filename != ""){
			$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
			$nombre_archivo = $contrato_id . "_ADENDA_FIRMADA_" . date('YmdHis') . "." . $fileExt;
			move_uploaded_file($filenametem, $path . $nombre_archivo);
			$comando = "INSERT INTO cont_archivos (
							contrato_id,
							adenda_id,
							tipo_archivo_id,
							nombre,
							extension,
							size,
							ruta,
							user_created_id,
							created_at)
						VALUES(
							'" . $contrato_id . "',
							'" . $_POST["adenda_id"] . "',
							63,
							'" . $nombre_archivo . "',
							'" . $fileExt . "',
							'" . $filesize . "',
							'" . $path . "',
							" . $usuario_id . ",
							'" . $created_at . "'
							)";
			$mysqli->query($comando);
			$archivo_id = mysqli_insert_id($mysqli);
		}
	}

	$query = $mysqli->query("
	SELECT contrato_id
	FROM cont_adendas
	WHERE id = " . $_POST["adenda_id"] . "
	AND status = 1
	AND procesado = 0");
	$row_count = $query->num_rows;
	if ($row_count > 0) {
		$row = $query->fetch_assoc();
		$contrato_id = $row["contrato_id"];

		$query = $mysqli->query("
		SELECT id,
			adenda_id,
			nombre_tabla,
			valor_original,
			nombre_campo_usuario,
			nombre_menu_usuario,
			nombre_campo,
			tipo_valor,
			valor_varchar,
			valor_int,
			valor_date,
			valor_decimal,
			valor_select_option,
			valor_id_tabla,
			id_del_registro_a_modificar,
			status
		FROM cont_adendas_detalle
		WHERE adenda_id = " . $_POST["adenda_id"] . "
		AND status = 1
		");
		$row_count = $query->num_rows;
		if ($row_count > 0) {
			while($row = $query->fetch_assoc()){
				$nombre_tabla = $row["nombre_tabla"];
				$nombre_campo = $row["nombre_campo"];
				$valor_original = $row["valor_original"];
				$valor_id_tabla = $row['valor_id_tabla'];
				$id_del_registro_a_modificar = $row['id_del_registro_a_modificar'];

				$tipo_valor = $row["tipo_valor"];


				if ($tipo_valor == "registro"){
					// agregar nuevos registros beneficiario y contraprestación
					if ($row['nombre_menu_usuario'] == "Representate Legal"){
						$query_update = "
						UPDATE " . $nombre_tabla . "
						SET
							" . $nombre_campo . " = '" . $contrato_id . "'
						WHERE id = " . $row['valor_int'];
						$mysqli->query($query_update);

						$query_rep = $mysqli->query("
							SELECT
							rl.vigencia_archivo_id,
							rl.dni_archivo_id
							FROM cont_representantes_legales as rl
							WHERE rl.id = " . $row['valor_int'] . "
							");
							while($rl = $query_rep->fetch_assoc()){
								if($rl['vigencia_archivo_id'] > 0){
									$query_update = "
									UPDATE cont_archivos
									SET contrato_id = '" . $contrato_id . "'
									WHERE archivo_id = " . $rl['vigencia_archivo_id'];
									$mysqli->query($query_update);
								}
								if($rl['dni_archivo_id'] > 0){
									$query_update = "
									UPDATE cont_archivos
									SET contrato_id = '" . $contrato_id . "'
									WHERE archivo_id = " . $rl['dni_archivo_id'];
									$mysqli->query($query_update);
								}
							}


					}

					if ($row['nombre_menu_usuario'] == "Contraprestación"){
						$query_update = "
						UPDATE " . $nombre_tabla . "
						SET
							" . $nombre_campo . " = '" . $contrato_id . "'
						WHERE id = " . $row['valor_int'];
						$mysqli->query($query_update);
					}

					if($mysqli->error){
						$result["update_error"] = $mysqli->error;
					}
				}else {
					//modificar tablas
					if ($tipo_valor == 'varchar') {
						$nuevo_valor = $row['valor_varchar'];
					} else if ($tipo_valor == 'int') {
						$nuevo_valor = $row['valor_int'];
					} else if ($tipo_valor == 'date') {
						$nuevo_valor = $row['valor_date'];
					} else if ($tipo_valor == 'decimal') {
						$nuevo_valor = $row['valor_decimal'];
					} else if ($tipo_valor == 'select_option') {
						if($nombre_campo == "ubigeo_id"){
							$nuevo_valor = $row['valor_varchar'];
						}else{
							$nuevo_valor = $row['valor_int'];
						}
					} else if ($tipo_valor == 'id_tabla') {
						$nuevo_valor = $row['valor_int'];
					}

					if ($id_del_registro_a_modificar > 0) {
						$query_update = "
						UPDATE " . $nombre_tabla . "
						SET
							" . $nombre_campo . " = '" . $nuevo_valor . "'
						WHERE id = " . $id_del_registro_a_modificar;
					} else {

						if($nombre_tabla == "cont_contrato" && $nombre_campo == "objeto_adenda"){
							$query_insert_objeto = "INSERT INTO cont_contrato_objetos (contrato_id, contrato_detalle_id, adenda_id, archivo_id, objeto, status, created_at, user_created_id) VALUES (
								'".$contrato_id."', 
								'0', 
								'".$_POST["adenda_id"]."',
								'0', 
								'".replace_invalid_caracters($nuevo_valor)."', 
								'1', 
								'".date('Y-m-d H:i:s')."', 
								'".$login['id']."'
							);";
							$mysqli->query($query_insert_objeto);
							$contrato_objeto_id = mysqli_insert_id($mysqli);
						}else{

							if($nombre_tabla == "cont_contrato" && $nombre_campo == "fecha_vencimiento_proveedor"){
								$update_alerta = "UPDATE cont_contrato SET alerta_enviada_id = NULL WHERE contrato_id = ".$contrato_id;
								$mysqli->query($update_alerta);	
							}

							$query_update = "
							UPDATE " . $nombre_tabla . "
							SET
								" . $nombre_campo . " = '" . $nuevo_valor . "'
							WHERE contrato_id = " . $contrato_id;
							$mysqli->query($query_update);

							if($mysqli->error){
								$result["update_error"] = $mysqli->error;
							}
						}
					}

					
				}

			}

			$query_update = "
			UPDATE cont_adendas
			SET
				procesado = 1,
				archivo_id = ".$archivo_id."
			WHERE id = '". $_POST["adenda_id"] ."'";
			$mysqli->query($query_update);

			send_email_adenda_contrato_proveedor_firmada($_POST["adenda_id"], false);

			if($mysqli->error){
				$result["update_error"] = $mysqli->error;
			}
		}
	}
	

	//INICIO REGISTRAR SEGUIMIENTO DE PROCESO CUANDO LA ADENDA HAYA SIDO SUBIDO POR UN USUARIO DE LEGAL
	// $ADENDA_DE_CONTRATO = 2;
	// $query_seguimiento = "SELECT id FROM cont_seguimiento_proceso_legal WHERE tipo_documento_id = ".$ADENDA_DE_CONTRATO." AND proceso_id = ".$_POST['adenda_id']." AND status IN (1,2)";
	// $list_query_seguimiento = $mysqli->query($query_seguimiento);
	// $cant_seguimientos = $list_query_seguimiento->num_rows;


	// $query_adenda = "SELECT cc.area_responsable_id, cc.tipo_contrato_id , tpa.area_id
	// FROM cont_adendas ca 
	// INNER JOIN cont_contrato cc ON cc.contrato_id = ca.contrato_id 
	// LEFT JOIN tbl_usuarios tu ON
	// 	tu.id = ca.user_created_id 
	// LEFT JOIN tbl_personal_apt tpa ON
	// 	tpa.id = tu.personal_id
	// WHERE ca.id  = ".$_POST['adenda_id']." LIMIT 1";
	// $sel_adenda = $mysqli->query($query_adenda);
	// $data_adenda = $sel_adenda->fetch_assoc();

	// $APROBACION_DEL_DOCUMENTO = 1;
	// $INICIO_DE_PROCESO_LEGAL = 2;
	// $AREA_LEGAL_ID = 33;
	// $ADENDA_DE_CONTRATO = 2;

	// if ($cant_seguimientos == 0 || $data_adenda['area_id'] == $AREA_LEGAL_ID) {
	// 	$seg_proceso = new SeguimientoProceso();
	// 	if ($cant_seguimientos == 0) {
	// 		$data_proceso_reg_ipc['tipo_documento_id'] = $ADENDA_DE_CONTRATO;
	// 		$data_proceso_reg_ipc['proceso_id'] = $_POST['adenda_id'];
	// 		$data_proceso_reg_ipc['proceso_detalle_id'] = 0;
	// 		$data_proceso_reg_ipc['area_id'] = $AREA_LEGAL_ID;
	// 		$data_proceso_reg_ipc['etapa_id'] = $INICIO_DE_PROCESO_LEGAL;
	// 		$data_proceso_reg_ipc['status'] = 1; //Pendiente
	// 		$data_proceso_reg_ipc['created_at'] = date('Y-m-d H:i:s');
	// 		$data_proceso_reg_ipc['user_created_id'] = $usuario_id;
	// 		$result_reg_conf_user = $seg_proceso->registrar_proceso($data_proceso_reg_ipc);
	// 	}
		
	// 	$data_proceso['tipo_documento_id'] = $ADENDA_DE_CONTRATO; //Adenda
	// 	$data_proceso['proceso_id'] = $_POST['adenda_id'];
	// 	$data_proceso['proceso_detalle_id'] = 0;
	// 	$data_proceso['usuario_id'] = $usuario_id;
	// 	$resp_proceso = $seg_proceso->fin_seguimiento_proceso_alterno($data_proceso);
	// }
	//FIN REGISTRAR SEGUIMIENTO DE PROCESO CUANDO LA ADENDA HAYA SIDO SUBIDO POR UN USUARIO DE LEGAL

	$result["result"] = 'ok';
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
}

if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_adenda_acuerdo_confidencialidad_firmada") {
	$usuario_id = $login?$login['id']:null;

	if ( !((int) $usuario_id > 0) ) {
		$result["http_code"] = 500;
		$result["status"] = "error";
		$result["mensaje"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y hacer click e el boton verde: Agregar adenda firmada.";
		echo json_encode($result);
		die;
	}

	$created_at = date("Y-m-d H:i:s");
	$contrato_id = $_POST["contrato_id"];
	$adenda_id = $_POST["adenda_id"];

	// $query = "SELECT a.abogado_id FROM cont_adendas a WHERE a.id = $adenda_id";
	// $list_query = $mysqli->query($query);
	// $row = $list_query->fetch_assoc();

	// if (empty($row['abogado_id'])) {
	// 	$result["http_code"] = 400;
	// 	$result["error"] = 'sin_asignar';
	// 	$result["campo_incompleto"] = 'abogado';
	// 	exit(json_encode($result));
	// }

	$path = "/var/www/html/files_bucket/contratos/adendas/acuerdo_confidencialidad/";
	$name_file = 'adenda_firmada_'.$_POST["adenda_id"];
	$archivo_id = 0;
	if (isset($_FILES[$name_file]) && $_FILES[$name_file]['error'] === UPLOAD_ERR_OK) {
		if (!is_dir($path)) mkdir($path, 0777, true);

		$filename = $_FILES[$name_file]['name'];
		$filenametem = $_FILES[$name_file]['tmp_name'];
		$filesize = $_FILES[$name_file]['size'];
		$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
		if($filename != ""){
			$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
			$nombre_archivo = $contrato_id . "_ADENDA_FIRMADA_" . date('YmdHis') . "." . $fileExt;
			move_uploaded_file($filenametem, $path . $nombre_archivo);
			$comando = "INSERT INTO cont_archivos (
							contrato_id,
							adenda_id,
							tipo_archivo_id,
							nombre,
							extension,
							size,
							ruta,
							user_created_id,
							created_at)
						VALUES(
							'" . $contrato_id . "',
							'" . $_POST["adenda_id"] . "',
							96,
							'" . $nombre_archivo . "',
							'" . $fileExt . "',
							'" . $filesize . "',
							'" . $path . "',
							" . $usuario_id . ",
							'" . $created_at . "'
							)";
			$mysqli->query($comando);
			$archivo_id = mysqli_insert_id($mysqli);
		}
	}

	$query = $mysqli->query("
	SELECT contrato_id
	FROM cont_adendas
	WHERE id = " . $_POST["adenda_id"] . "
	AND status = 1
	AND procesado = 0");
	$row_count = $query->num_rows;
	if ($row_count > 0) {
		$row = $query->fetch_assoc();
		$contrato_id = $row["contrato_id"];

		$query = $mysqli->query("
		SELECT id,
			adenda_id,
			nombre_tabla,
			valor_original,
			nombre_campo_usuario,
			nombre_menu_usuario,
			nombre_campo,
			tipo_valor,
			valor_varchar,
			valor_int,
			valor_date,
			valor_decimal,
			valor_select_option,
			valor_id_tabla,
			id_del_registro_a_modificar,
			status
		FROM cont_adendas_detalle
		WHERE adenda_id = " . $_POST["adenda_id"] . "
		AND status = 1
		");
		$row_count = $query->num_rows;
		if ($row_count > 0) {
			while($row = $query->fetch_assoc()){
				$nombre_tabla = $row["nombre_tabla"];
				$nombre_menu_usuario = $row["nombre_menu_usuario"];
				$nombre_campo = $row["nombre_campo"];
				$valor_original = $row["valor_original"];
				$valor_id_tabla = $row['valor_id_tabla'];
				$id_del_registro_a_modificar = $row['id_del_registro_a_modificar'];

				$tipo_valor = $row["tipo_valor"];
				if ($tipo_valor == 'varchar') {
					$nuevo_valor = $row['valor_varchar'];
				} else if ($tipo_valor == 'int') {
					$nuevo_valor = $row['valor_int'];
				} else if ($tipo_valor == 'date') {
					$nuevo_valor = $row['valor_date'];
				} else if ($tipo_valor == 'decimal') {
					$nuevo_valor = $row['valor_decimal'];
				} else if ($tipo_valor == 'select_option') {
					if($nombre_campo == "ubigeo_id"){
						$nuevo_valor = $row['valor_varchar'];
					}else{
						$nuevo_valor = $row['valor_int'];
					}
				} else if ($tipo_valor == 'id_tabla') {
					$nuevo_valor = $row['valor_int'];
				} else if ($tipo_valor == 'registro') {
					$nuevo_valor = $row['valor_int'];
				} else if ($tipo_valor == 'eliminar') {
					$nuevo_valor = $row['valor_int'];
				}

				if ($tipo_valor == 'id_tabla'){
					if ($nombre_tabla == 'cont_propietario') {
						$query_update = "
						UPDATE " . $nombre_tabla . "
						SET
							persona_id = '" . $nuevo_valor . "'
						WHERE propietario_id = '" . $valor_id_tabla . "'";
						$mysqli->query($query_update);
					} else if ($nombre_tabla == 'cont_beneficiarios') {
						$query_update = "
						UPDATE " . $nombre_tabla . "
						SET
							status = 0
						WHERE contrato_id = '" . $contrato_id . "'
						AND id = '" . $valor_original . "'";
						$mysqli->query($query_update);

						$query_update = "
						UPDATE " . $nombre_tabla . "
						SET
							contrato_id = '" . $contrato_id . "'
						WHERE id = '" . $nuevo_valor . "'";
						$mysqli->query($query_update);

					} else if ($nombre_tabla == 'cont_incrementos') {
						$query_update = "
						UPDATE " . $nombre_tabla . "
						SET
						contrato_id = ".$contrato_id."
						WHERE id = " . $nuevo_valor;
						$mysqli->query($query_update);
					}
				} else if ($tipo_valor == 'registro'){


					if ($nombre_menu_usuario == 'Propietario'){
						$query_update = "
						UPDATE cont_propietario AS p
						INNER JOIN cont_persona AS per ON per.id = p.persona_id
						SET
						per." . $nombre_campo . " = '" . $nuevo_valor . "'
						WHERE p.contrato_id = '" . $contrato_id . "'";
						$mysqli->query($query_update);
					}else if ($nombre_menu_usuario == 'Representante Legal'){
						$query_update = "
						UPDATE cont_representantes_legales
						SET " . $nombre_campo . " = '" . $contrato_id . "'
						WHERE id = '" . $nuevo_valor . "'";
						$mysqli->query($query_update);
					}else if ($nombre_menu_usuario == 'Beneficiario'){
						$query_update = "
						UPDATE " . $nombre_tabla . "
						SET " . $nombre_campo . " = '" . $contrato_id . "'
						WHERE id = '" . $nuevo_valor . "'";
						$mysqli->query($query_update);
					}else if ($nombre_menu_usuario == 'Incremento'){
						$query_update = "
						UPDATE " . $nombre_tabla . "
						SET " . $nombre_campo . " = '" . $contrato_id . "'
						WHERE id = '" . $nuevo_valor . "'";
						$mysqli->query($query_update);
					}
				} else if ($tipo_valor == 'eliminar'){
					if ($nombre_menu_usuario == 'Propietario'){
						$query_update = "UPDATE cont_propietario SET status = 0 WHERE propietario_id = " . $nuevo_valor;
						$mysqli->query($query_update);
					}else if ($nombre_menu_usuario == 'Beneficiario'){
						$query_update = "UPDATE cont_beneficiarios SET status = 0 WHERE id = " . $nuevo_valor;
						$mysqli->query($query_update);
					}else if ($nombre_menu_usuario == 'Incremento'){
						$query_update = "UPDATE cont_incrementos SET status = 0 WHERE id = " . $nuevo_valor;
						$mysqli->query($query_update);
					}
				} else {

					if($nombre_tabla == "cont_contrato" && $nombre_campo == "fecha_vencimiento_proveedor"){
						$update_alerta = "UPDATE cont_contrato SET alerta_enviada_id = NULL WHERE contrato_id = ".$contrato_id;
						$mysqli->query($update_alerta);	
					}
					
					if($nombre_tabla == 'cont_persona'){
						$query_update = "
						UPDATE cont_propietario AS p
						INNER JOIN cont_persona AS per ON per.id = p.persona_id
						SET
						per." . $nombre_campo . " = '" . $nuevo_valor . "'
						WHERE p.contrato_id = '" . $contrato_id . "'";
					}else{
						$query_update = "
						UPDATE " . $nombre_tabla . "
						SET
							" . $nombre_campo . " = '" . $nuevo_valor . "'
						WHERE contrato_id = '" . $contrato_id . "'";
					}

					$mysqli->query($query_update);
				}

				if($mysqli->error){
					$result["update_error"] = $mysqli->error;
				}
			}

			$query_update = "
			UPDATE cont_adendas
			SET
				procesado = 1,
				archivo_id = ".$archivo_id."
			WHERE id = '". $_POST["adenda_id"] ."'";
			$mysqli->query($query_update);

			send_email_adenda_acuerdo_confidencialidad_firmada($_POST["adenda_id"], false);

			if($mysqli->error){
				$result["update_error"] = $mysqli->error;
			}
		}
	}

	$result["result"] = 'ok';
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
}

if (isset($_POST["accion"]) && $_POST["accion"]==="generar_ordenes_de_pago_manual") {

	if ($_POST["contrato_id"] > 0) {
		generar_ordenes_de_pago($_POST["contrato_id"]);
	}

	$result["http_code"] = 200;
	$result["error"] = '';
	$result["status"] = "Datos obtenidos de gestion.";
}


function generar_ordenes_de_pago($contrato_id)
{
	include("db_connect.php");
	include("sys_login.php");

	// INICIO GET VARIABLES
	$query = $mysqli->query("
	SELECT
	ce.monto_renta,
	ce.tipo_moneda_id,
	ce.fecha_inicio,
	ce.fecha_fin,
	ce.garantia_monto,
	ce.impuesto_a_la_renta_id,
	ce.periodo_gracia_id,
	ce.periodo_gracia_inicio,
	ce.periodo_gracia_fin,
	b.forma_pago_id
	FROM cont_contrato c
	INNER JOIN cont_condicion_economica ce
	ON c.contrato_id = ce.contrato_id AND ce.status = 1
	INNER JOIN cont_beneficiarios b
	ON c.contrato_id = b.contrato_id
	WHERE c.contrato_id = " . $contrato_id . "
	AND c.status = 1");
	$row_count = $query->num_rows;
	if ($row_count > 0) {
		while($row = $query->fetch_assoc()){
			$renta = $row["monto_renta"];
			$tipo_moneda_id = $row["tipo_moneda_id"];
			$fecha_inicio = $row["fecha_inicio"];
			$fecha_fin = $row["fecha_fin"];
			$garantia_monto = $row["garantia_monto"];
			$impuesto_a_la_renta_id = $row["impuesto_a_la_renta_id"];
			$periodo_gracia_id = $row["periodo_gracia_id"];
			$periodo_gracia_inicio = $row["periodo_gracia_inicio"];
			$periodo_gracia_fin = $row["periodo_gracia_fin"];
			$forma_pago_id = $row["forma_pago_id"];
		}
	}
	// FIN GET VARIABLES


	// INICIO OBTENER ADELANTOS
	$array_adelantos = [];
	$query = $mysqli->query("
	SELECT
	num_periodo
	FROM cont_adelantos
	WHERE contrato_id = " . $contrato_id . "
	AND status = 1");
	$row_count = $query->num_rows;
	if ($row_count > 0) {
		$cont_incremento = 0;
		while($row = $query->fetch_assoc()){
			$array_adelantos[] = $row["num_periodo"];
		}
	}
	$num_adelantos = count($array_adelantos);
	// FIN OBTENER ADELANTOS

	$array_incrementos = [];
	// INICIO OBTENER INCREMENTOS
	$query = $mysqli->query("
	SELECT
	valor,
	tipo_valor_id,
	tipo_continuidad_id,
	a_partir_del_año
	FROM cont_incrementos
	WHERE contrato_id = " . $contrato_id . "
	AND estado = 1");
	$row_count = $query->num_rows;
	if ($row_count > 0) {
		$cont_incremento = 0;
		while($row = $query->fetch_assoc()){
			$array_incrementos[$cont_incremento][0] = $row["valor"];
			$array_incrementos[$cont_incremento][1] = $row["tipo_valor_id"];
			$array_incrementos[$cont_incremento][2] = $row["tipo_continuidad_id"];
			$array_incrementos[$cont_incremento][3] = $row["a_partir_del_año"];
			$cont_incremento++;
		}
	}
	$num_incrementos = count($array_incrementos);
	// FIN OBTENER INCREMENTOS


	// INICIO INICIALIZACION DE VARIABLES
	$user_id = $login?$login['id']:null;
	$created_at = date('Y-m-d H:i:s');

	$num_dias_excedentes = 0;
	$incrementos = 0;
	$descuento = 0;
	$tipo_orden_id = 1;
	// FIN INICIALIZACION DE VARIABLES


	// INICIO GENERAR ORDEN
	$query = "INSERT INTO cont_orden
	(contrato_id,
	status,
	user_created_id,
	created_at)
	VALUES (
	" . $contrato_id . ",
	1,
	" . $user_id . ",
	'" . $created_at . "')";

	$mysqli->query($query);
	$orden_id = mysqli_insert_id($mysqli);
	// FIN GENERAR ORDEN


	// INICIO INTERVALO DE FECHA INICIO Y FIN
	$datetime_inicio = new DateTime($fecha_inicio);
	$datetime_fin = new DateTime($fecha_fin);

	$intervalo = $datetime_fin->diff($datetime_inicio);

	$intervalo_dias = $intervalo->format("%d");
	$intervalo_meses = $intervalo->format("%m");
	$intervalo_anios = $intervalo->format("%y") * 12;

	$intervalo_meses_final = $intervalo_meses + $intervalo_anios;
	// FIN INTERVALO DE FECHA INICIO Y FIN


	// INICIO GENERAR ORDEN DE TIPO GARANTIA
	$query = "INSERT INTO cont_orden_detalle(
	orden_id,
	tipo_orden_id,
	num_cuota,
	periodo_inicio,
	periodo_fin,
	forma_pago_id,
	moneda_id,
	renta,
	incrementos,
	descuento,
	total,
	status,
	user_created_id,
	created_at)
	VALUES (
	" . $orden_id . ",
	3,
	1,
	'" . $datetime_inicio->format('Y-m-d') . "',
	'" . $datetime_inicio->format('Y-m-d') . "',
	" . $forma_pago_id . ",
	" . $tipo_moneda_id . ",
	" . round($garantia_monto, 2) . ",
	" . round(0, 2) . ",
	" . round(0, 2) . ",
	" . round($garantia_monto, 2) . ",
	1,
	" . $user_id . ",
	'" . $created_at . "')";
	// echo $sql;
	$mysqli->query($query);
	// FIN GENERAR ORDEN DE TIPO GARANTIA


	// INICIO GENERAR ORDEN DE TIPO ADELANTO
	$query = "INSERT INTO cont_orden_detalle(
	orden_id,
	tipo_orden_id,
	num_cuota,
	periodo_inicio,
	periodo_fin,
	forma_pago_id,
	moneda_id,
	renta,
	incrementos,
	descuento,
	total,
	status,
	user_created_id,
	created_at)
	VALUES (
	" . $orden_id . ",
	2,
	1,
	'" . $datetime_inicio->format('Y-m-d') . "',
	'" . $datetime_inicio->format('Y-m-d') . "',
	" . $forma_pago_id . ",
	" . $tipo_moneda_id . ",
	" . round(($renta * $num_adelantos), 2) . ",
	" . round(0, 2) . ",
	" . round(0, 2) . ",
	" . round(($renta * $num_adelantos), 2) . ",
	1,
	" . $user_id . ",
	'" . $created_at . "')";
	// echo $sql;
	$mysqli->query($query);
	// FIN GENERAR ORDEN DE TIPO ADELANTO


	for ($num_cuota = 1; $num_cuota <= $intervalo_meses_final; $num_cuota++) {

		// INICIO RESET VARIALES LOCALES
		$descuento = 0;
		// FIN RESET VARIALES LOCALES


		// INICIO PERIODO INICIO Y FIN
		$periodo_inicio = $datetime_inicio->format('Y-m-d');
		$datetime_inicio->modify('+1 month');

		$intervalo_dias_excedente = $datetime_fin->diff($datetime_inicio);
		$num_dias_excedentes = $intervalo_dias_excedente->format('%R%a');
		if($num_dias_excedentes>0){
			$periodo_fin = $datetime_fin->format('Y-m-d');
			$descuento = $renta - (($renta*$num_dias_excedentes)/30);
			break;
		} else {
			// $periodo_fin = $datetime_inicio->format('Y-m-d');


			$datetime_inicio->modify('-1 day');
			$periodo_fin_tmp = $datetime_inicio->format('Y-m-d');
		}
		 // FIN PERIODO INICIO Y FIN


		// INICIO INCREMENTOS
		$contador_incremento_a_la_renta = 0;
		for ($i = 0; $i < $num_incrementos; $i++) {
			$valor = $array_incrementos[$i][0];
			$tipo_valor = $array_incrementos[$i][1];
			$tipo_continuidad =  $array_incrementos[$i][2];
			$a_partir_del_anio_en_meses = (($array_incrementos[$i][3] - 1) * 12) + 1;

			if ($tipo_continuidad == 1) { // EL
				if ($num_cuota == $a_partir_del_anio_en_meses) {
					if ($contador_incremento_a_la_renta == 0) {
						$renta = $renta + $incrementos;
						$incrementos = 0;
						$contador_incremento_a_la_renta++;
					}

					if ($tipo_valor == 1) {
						$incrementos += $valor;
					} else if ($tipo_valor == 2) {
						$incrementos += ($renta * $valor)/100;
					}
				}
				if ($num_cuota == ($a_partir_del_anio_en_meses + 12) ) {
					$renta = $renta + $incrementos;
					$incrementos = 0;
				}
			} elseif ($tipo_continuidad == 2 || $tipo_continuidad == 3) { // ANUAL A PARTIR DEL
				for ($j = $a_partir_del_anio_en_meses; $j <= $num_cuota; $j+=12) {
					if ($num_cuota == $j) {
						if ($contador_incremento_a_la_renta == 0) {
							$renta = $renta + $incrementos;
							$incrementos = 0;
							$contador_incremento_a_la_renta++;
						}

						if ($tipo_valor == 1) {
							$incrementos += $valor;
						} else if ($tipo_valor == 2) {
							$incrementos += ($renta * $valor)/100;
						}
					}
				}
			}
		}
		// FIN INCREMENTOS


		// INICIO DESCUENTO (ADELANTO)
		foreach ($array_adelantos as $value) {
			if($num_cuota == $value){
				$descuento = $renta;
			}
		}
		// FIN DESCUENTO (ADELANTO)

		$total = ($renta + $incrementos) - $descuento;

		$query = "INSERT INTO cont_orden_detalle(
		orden_id,
		tipo_orden_id,
		num_cuota,
		periodo_inicio,
		periodo_fin,
		forma_pago_id,
		moneda_id,
		renta,
		incrementos,
		descuento,
		total,
		status,
		user_created_id,
		created_at)
		VALUES (
		" . $orden_id . ",
		" . $tipo_orden_id . ",
		" . $num_cuota . ",
		'" . $periodo_inicio . "',
		'" . $periodo_fin_tmp . "',
		" . $forma_pago_id . ",
		" . $tipo_moneda_id . ",
		" . round($renta, 2) . ",
		" . round($incrementos, 2) . ",
		" . round($descuento, 2) . ",
		" . round($total, 2) . ",
		1,
		" . $user_id . ",
		'" . $created_at . "')";
		// echo $sql;
		$mysqli->query($query);

		if($impuesto_a_la_renta_id == '2' || $impuesto_a_la_renta_id == '3'){
			$query = "INSERT INTO cont_orden_detalle(
			orden_id,
			tipo_orden_id,
			num_cuota,
			periodo_inicio,
			periodo_fin,
			forma_pago_id,
			moneda_id,
			renta,
			incrementos,
			descuento,
			total,
			status,
			user_created_id,
			created_at)
			VALUES (
			" . $orden_id . ",
			4,
			" . $num_cuota . ",
			'" . $periodo_inicio . "',
			'" . $periodo_fin_tmp . "',
			" . $forma_pago_id . ",
			" . $tipo_moneda_id . ",
			" . round(($total * 0.05), 2) . ",
			" . round(0, 2) . ",
			" . round(0, 2) . ",
			" . round(($total * 0.05), 2) . ",
			1,
			" . $user_id . ",
			'" . $created_at . "')";
			// echo $sql;
			$mysqli->query($query);
		}
		$datetime_inicio->modify('+1 day');
	}
	// FIN DETALLE ORDEN


	// INICIO ULTIMO MES
	if($intervalo_dias > 0 && $num_dias_excedentes < 0){
		$periodo_inicio = $datetime_inicio->format('Y-m-d');
		$periodo_fin = $datetime_fin->format('Y-m-d');

		$descuento = ($renta + $incrementos) - ((($renta + $incrementos) * $intervalo_dias)/30);
		$total = ($renta + $incrementos) - $descuento;

		// guardar_detalle_orden($orden_id, $tipo_orden_id, $num_cuota, $periodo_inicio, $periodo_fin, $forma_pago_id, $tipo_moneda_id, $renta, $incrementos, $descuento);

		$query = "INSERT INTO cont_orden_detalle(
		orden_id,
		tipo_orden_id,
		num_cuota,
		periodo_inicio,
		periodo_fin,
		forma_pago_id,
		moneda_id,
		renta,
		incrementos,
		descuento,
		total,
		status,
		user_created_id,
		created_at)
		VALUES (
		" . $orden_id . ",
		" . $tipo_orden_id . ",
		" . $num_cuota . ",
		'" . $periodo_inicio . "',
		'" . $periodo_fin . "',
		" . $forma_pago_id . ",
		" . $tipo_moneda_id . ",
		" . round($renta, 2) . ",
		" . round($incrementos, 2) . ",
		" . round($descuento, 2) . ",
		" . round($total, 2) . ",
		1,
		" . $user_id . ",
		'" . $created_at . "')";
		// echo $sql;
		$mysqli->query($query);

		if($impuesto_a_la_renta_id == '2' || $impuesto_a_la_renta_id == '3'){
			$query = "INSERT INTO cont_orden_detalle(
			orden_id,
			tipo_orden_id,
			num_cuota,
			periodo_inicio,
			periodo_fin,
			forma_pago_id,
			moneda_id,
			renta,
			incrementos,
			descuento,
			total,
			status,
			user_created_id,
			created_at)
			VALUES (
			" . $orden_id . ",
			4,
			" . $num_cuota . ",
			'" . $periodo_inicio . "',
			'" . $periodo_fin . "',
			" . $forma_pago_id . ",
			" . $tipo_moneda_id . ",
			" . round(($total * 0.05), 2) . ",
			" . round(0, 2) . ",
			" . round(0, 2) . ",
			" . round(($total * 0.05), 2) . ",
			1,
			" . $user_id . ",
			'" . $created_at . "')";
			// echo $sql;
			$mysqli->query($query);
		}
	}
	// FIN ULTIMO MES

	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}

	// $result["http_code"] = 200;
	// $result["status"] = "Datos obtenidos de gestión.";
	// $result["result"] = 'ok';
	// $result["error"] = $error;

	// echo json_encode($result);
}

if (isset($_POST["accion"]) && $_POST["accion"]==="cont_detalle_solicitudv2_proveedor_obtener_tipo_contrato")
{

	$cont_detalle_proveedor_contrato_firmado_categoria_param = $_POST["cont_detalle_proveedor_contrato_firmado_categoria_param"];

	$query =
		"
			SELECT id, nombre
			FROM cont_tipo_categoria_servicio
			WHERE status = 1 AND categoria_servicio_id = '".$cont_detalle_proveedor_contrato_firmado_categoria_param."'
		";

	$list_query = $mysqli->query($query);
	$list = array();
	while ($li = $list_query->fetch_assoc())
	{
		$list[] = $li;
	}

	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}

	if(count($list) == 0){
		$result["http_code"] = 400;
		$result["result"] = "El departamento no existe.";
	} elseif (count($list) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $list;
	} else {
		$result["http_code"] = 400;
		$result["result"] = "El departamento no existe.";
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_con_det_prov_agregar_representante_legal") {
	$usuario_id = $login?$login['id']:null;
	$contrato_id = $_POST["contrato_id"];
	$dniRepresentante = $_POST["dniRepresentante"];
	$nombreRepresentante = $_POST["nombreRepresentante"];
	$banco = $_POST["banco"];
	$nro_cuenta = $_POST["nro_cuenta"];
	$nro_cci = $_POST["nro_cci"];
	$vig_arch_id = 0;
	$dni_arch_id = 0;
	$error = '';


	$path = "/var/www/html/files_bucket/contratos/solicitudes/proveedores/";

	if (isset($_FILES['sec_con_det_prov_file_vigencia_nuevo_rl']) && $_FILES['sec_con_det_prov_file_vigencia_nuevo_rl']['error'] === UPLOAD_ERR_OK)
	{
		if (!is_dir($path)) mkdir($path, 0777, true);
		$filename = $_FILES['sec_con_det_prov_file_vigencia_nuevo_rl']['name'];
		$filenametem = $_FILES['sec_con_det_prov_file_vigencia_nuevo_rl']['tmp_name'];
		$filesize = $_FILES['sec_con_det_prov_file_vigencia_nuevo_rl']['size'];
		$fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		$valid_extensions = array('jpeg', 'jpg', 'png', 'pdf');
		if(in_array($fileExt, $valid_extensions))
		{
			$nombre_archivo_guardar = "_VIG_";
			if ($nombre_archivo_guardar != "")
			{
				$nombre_archivo = $contrato_id . $nombre_archivo_guardar . date('YmdHis') . "." . $fileExt;
				move_uploaded_file($filenametem, $path . $nombre_archivo);
				$comando = "INSERT INTO cont_archivos
							(contrato_id,tipo_archivo_id,nombre,extension,ruta,size,status,
								user_created_id, created_at, user_updated_id, updated_at)
							VALUES('" . $contrato_id . "','2','" . $nombre_archivo . "','" . $fileExt
									. "','" . $path . "','" . $filesize . "','1','".$login["id"]."', '"
									. date('Y-m-d H:i:s') . "','".$login["id"]."', '" . date('Y-m-d H:i:s') . "')";
				$mysqli->query($comando);
				$vig_arch_id = mysqli_insert_id($mysqli);
				$message = "Datos guardados correctamente";
				$status = true;
			}
			else
			{
				$message = "El tipo de archivo a guardar no fue identificado, por favor consulte con el area de sistemas";
				$status = false;
			}
		}
		else
		{
			$message = "La extension del file no es aceptado, solo son aceptado: jpeg, jpg, png, pdf";
			$status = false;
		}
	}
	else
	{
		$message = "Tiene que seleccionar un archivo :)";
		$status = false;
	}

	if (isset($_FILES['sec_con_det_prov_file_dni_nuevo_rl']) && $_FILES['sec_con_det_prov_file_dni_nuevo_rl']['error'] === UPLOAD_ERR_OK)
	{
		if (!is_dir($path)) mkdir($path, 0777, true);
		$filename = $_FILES['sec_con_det_prov_file_dni_nuevo_rl']['name'];
		$filenametem = $_FILES['sec_con_det_prov_file_dni_nuevo_rl']['tmp_name'];
		$filesize = $_FILES['sec_con_det_prov_file_dni_nuevo_rl']['size'];
		$fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		$valid_extensions = array('jpeg', 'jpg', 'png', 'pdf');
		if(in_array($fileExt, $valid_extensions))
		{
			$nombre_archivo_guardar = "_DNI_";
			if ($nombre_archivo_guardar != "")
			{
				$nombre_archivo = $contrato_id . $nombre_archivo_guardar . date('YmdHis') . "." . $fileExt;
				move_uploaded_file($filenametem, $path . $nombre_archivo);
				$comando = "INSERT INTO cont_archivos
							(contrato_id,tipo_archivo_id,nombre,extension,ruta,size,status,
								user_created_id, created_at, user_updated_id, updated_at)
							VALUES('" . $contrato_id . "','3','" . $nombre_archivo . "','" . $fileExt
									. "','" . $path . "','" . $filesize . "','1','".$login["id"]."', '"
									. date('Y-m-d H:i:s') . "','".$login["id"]."', '" . date('Y-m-d H:i:s') . "')";
				$mysqli->query($comando);
				$dni_arch_id = mysqli_insert_id($mysqli);
				$message = "Datos guardados correctamente";
				$status = true;
			}
			else
			{
				$message = "El tipo de archivo a guardar no fue identificado, por favor consulte con el area de sistemas";
				$status = false;
			}
		}
		else
		{
			$message = "La extension del file no es aceptado, solo son aceptado: jpeg, jpg, png, pdf";
			$status = false;
		}
	}
	else
	{
		$message = "Tiene que seleccionar un archivo :)";
		$status = false;
	}

	$query_insert = "INSERT INTO cont_representantes_legales
					(contrato_id, dni_representante, nombre_representante, id_banco, nro_cuenta, nro_cci,
					vigencia_archivo_id, dni_archivo_id, id_user_created, created_at)
					VALUES (" .$contrato_id . ",'" . $dniRepresentante . "','" . $nombreRepresentante . "',"
					. $banco . ",'" . $nro_cuenta . "','" . $nro_cci . "'," . $vig_arch_id . ","
					. $dni_arch_id . "," . $usuario_id . ",now())";
	$mysqli->query($query_insert);

	if($mysqli->error){
		$error .= $mysqli->error;
		echo $query_insert;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = "ok";
	$result["error"] = $error;
}

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_con_det_prov_agregar_representante_legal_acuerdo_confidencialidad") {
	$usuario_id = $login?$login['id']:null;
	$contrato_id = $_POST["contrato_id"];
	$dniRepresentante = $_POST["dniRepresentante"];
	$nombreRepresentante = $_POST["nombreRepresentante"];
	$banco = $_POST["banco"];
	$nro_cuenta = $_POST["nro_cuenta"];
	$nro_cci = $_POST["nro_cci"];
	$vig_arch_id = 0;
	$dni_arch_id = 0;
	$error = '';


	$path = "/var/www/html/files_bucket/contratos/solicitudes/acuerdos/";

	if (isset($_FILES['sec_con_det_prov_file_vigencia_nuevo_rl']) && $_FILES['sec_con_det_prov_file_vigencia_nuevo_rl']['error'] === UPLOAD_ERR_OK)
	{
		if (!is_dir($path)) mkdir($path, 0777, true);
		$filename = $_FILES['sec_con_det_prov_file_vigencia_nuevo_rl']['name'];
		$filenametem = $_FILES['sec_con_det_prov_file_vigencia_nuevo_rl']['tmp_name'];
		$filesize = $_FILES['sec_con_det_prov_file_vigencia_nuevo_rl']['size'];
		$fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		$valid_extensions = array('jpeg', 'jpg', 'png', 'pdf');
		if(in_array($fileExt, $valid_extensions))
		{
			$nombre_archivo_guardar = "_VIG_";
			if ($nombre_archivo_guardar != "")
			{
				$nombre_archivo = $contrato_id . $nombre_archivo_guardar . date('YmdHis') . "." . $fileExt;
				move_uploaded_file($filenametem, $path . $nombre_archivo);
				$comando = "INSERT INTO cont_archivos
							(contrato_id,tipo_archivo_id,nombre,extension,ruta,size,status,
								user_created_id, created_at, user_updated_id, updated_at)
							VALUES('" . $contrato_id . "','2','" . $nombre_archivo . "','" . $fileExt
									. "','" . $path . "','" . $filesize . "','1','".$login["id"]."', '"
									. date('Y-m-d H:i:s') . "','".$login["id"]."', '" . date('Y-m-d H:i:s') . "')";
				$mysqli->query($comando);
				$vig_arch_id = mysqli_insert_id($mysqli);
				$message = "Datos guardados correctamente";
				$status = true;
			}
			else
			{
				$message = "El tipo de archivo a guardar no fue identificado, por favor consulte con el area de sistemas";
				$status = false;
			}
		}
		else
		{
			$message = "La extension del file no es aceptado, solo son aceptado: jpeg, jpg, png, pdf";
			$status = false;
		}
	}
	else
	{
		$message = "Tiene que seleccionar un archivo :)";
		$status = false;
	}

	if (isset($_FILES['sec_con_det_prov_file_dni_nuevo_rl']) && $_FILES['sec_con_det_prov_file_dni_nuevo_rl']['error'] === UPLOAD_ERR_OK)
	{
		if (!is_dir($path)) mkdir($path, 0777, true);
		$filename = $_FILES['sec_con_det_prov_file_dni_nuevo_rl']['name'];
		$filenametem = $_FILES['sec_con_det_prov_file_dni_nuevo_rl']['tmp_name'];
		$filesize = $_FILES['sec_con_det_prov_file_dni_nuevo_rl']['size'];
		$fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
		$valid_extensions = array('jpeg', 'jpg', 'png', 'pdf');
		if(in_array($fileExt, $valid_extensions))
		{
			$nombre_archivo_guardar = "_DNI_";
			if ($nombre_archivo_guardar != "")
			{
				$nombre_archivo = $contrato_id . $nombre_archivo_guardar . date('YmdHis') . "." . $fileExt;
				move_uploaded_file($filenametem, $path . $nombre_archivo);
				$comando = "INSERT INTO cont_archivos
							(contrato_id,tipo_archivo_id,nombre,extension,ruta,size,status,
								user_created_id, created_at, user_updated_id, updated_at)
							VALUES('" . $contrato_id . "','3','" . $nombre_archivo . "','" . $fileExt
									. "','" . $path . "','" . $filesize . "','1','".$login["id"]."', '"
									. date('Y-m-d H:i:s') . "','".$login["id"]."', '" . date('Y-m-d H:i:s') . "')";
				$mysqli->query($comando);
				$dni_arch_id = mysqli_insert_id($mysqli);
				$message = "Datos guardados correctamente";
				$status = true;
			}
			else
			{
				$message = "El tipo de archivo a guardar no fue identificado, por favor consulte con el area de sistemas";
				$status = false;
			}
		}
		else
		{
			$message = "La extension del file no es aceptado, solo son aceptado: jpeg, jpg, png, pdf";
			$status = false;
		}
	}
	else
	{
		$message = "Tiene que seleccionar un archivo :)";
		$status = false;
	}

	$query_insert = "INSERT INTO cont_representantes_legales
					(contrato_id, dni_representante, nombre_representante, id_banco, nro_cuenta, nro_cci,
					vigencia_archivo_id, dni_archivo_id, id_user_created, created_at)
					VALUES (" .$contrato_id . ",'" . $dniRepresentante . "','" . $nombreRepresentante . "',"
					. $banco . ",'" . $nro_cuenta . "','" . $nro_cci . "'," . $vig_arch_id . ","
					. $dni_arch_id . "," . $usuario_id . ",now())";
	$mysqli->query($query_insert);

	if($mysqli->error){
		$error .= $mysqli->error;
		echo $query_insert;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = "ok";
	$result["error"] = $error;
}


if (isset($_POST["accion"]) && $_POST["accion"]==="llamar_funcion_send_email_formato_de_pago") {
	$contrato_id = $_POST["contrato_id"];
	send_email_formato_de_pago($contrato_id);
}

function send_email_adenda_contrato_arrendamiento_firmada($adenda_id, $reenvio = false)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$sel_query = $mysqli->query("
	SELECT a.id, a.fecha_cambio_estado_solicitud, a.created_at, co.sigla, c.codigo_correlativo, c.nombre_tienda, c.contrato_id, tc.nombre,
	concat( IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS usuario_solicitante,
	r.nombre AS empresa_suscribe, ar.nombre AS nombre_area, tp.correo, c.cc_id, tp.correo AS correo_abogado
	FROM cont_adendas AS a
	INNER JOIN cont_contrato AS c ON c.contrato_id = a.contrato_id
	INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id
	INNER JOIN cont_tipo_contrato AS tc ON tc.id = c.tipo_contrato_id
	INNER JOIN tbl_usuarios AS tu	ON tu.id = a.user_created_id
	INNER JOIN tbl_personal_apt AS tp ON tp.id = tu.personal_id

	LEFT JOIN tbl_usuarios AS uab	ON uab.id = a.abogado_id
	LEFT JOIN tbl_personal_apt AS pab ON pab.id = uab.personal_id

	INNER JOIN tbl_areas AS ar ON tp.area_id = ar.id
	LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
	WHERE a.id = $adenda_id
	");


	$body = "";
	$body .= '<html>';

	$usuario_creacion_correo = '';
	$nombre_tienda = '';
	$correos_add = [];
	while($sel = $sel_query->fetch_assoc())
	{
		$contrato_id = $sel['contrato_id'];
		$sigla_correlativo = $sel['sigla'];
		$codigo_correlativo = $sel['codigo_correlativo'];
		$empresa_suscribe = $sel['empresa_suscribe'];
		$fecha_solicitud = $sel['created_at'];
		$usuario_solicitante = $sel['usuario_solicitante'];
		$fecha_cambio_estado_solicitud = $sel['fecha_cambio_estado_solicitud'];
		$nombre_tienda = $sel['nombre_tienda'];
		
		
		if(!Empty($sel['correo_abogado'])){
			array_push($correos_add, $sel['correo_abogado']);
		}

		if(!Empty($sel['correo'])){
			array_push($correos_add, $sel['correo']);
		}


		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

		$body .= '<thead>';

		$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>Datos Generales</b>';
			$body .= '</th>';
		$body .= '</tr>';


		$body .= '</thead>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Centro de Costo:</b></td>';
			$body .= '<td>'.$sel["cc_id"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Nombre de Tienda:</b></td>';
			$body .= '<td>'.$sel["nombre_tienda"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Area Solicitante:</b></td>';
			$body .= '<td>'.$sel["nombre_area"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Usuario solicitante:</b></td>';
			$body .= '<td>'.$sel["usuario_solicitante"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha Solicitud:</b></td>';
			$body .= '<td>'.$sel["created_at"].'</td>';
		$body .= '</tr>';

		$body .= '</table>';
		$body .= '</div>';


		$query = $mysqli->query("
		SELECT a.id,
			a.adenda_id,
			a.nombre_tabla,
			a.valor_original,
			a.nombre_menu_usuario,
			a.nombre_campo_usuario,
			a.nombre_campo,
			a.tipo_valor,
			a.valor_varchar,
			a.valor_int,
			a.valor_date,
			a.valor_decimal,
			a.valor_select_option,
			a.status,
			cd.codigo
		FROM cont_adendas_detalle AS a
		LEFT JOIN cont_contrato_detalle AS cd ON cd.id = a.contrato_detalle_id
		WHERE a.adenda_id = " . $adenda_id . "
		AND a.tipo_valor != 'id_tabla' AND a.tipo_valor != 'registro' AND a.tipo_valor != 'eliminar'
		AND a.status = 1");
		$row_count = $query->num_rows;
		$numero_adenda_detalle = 0;
		if ($row_count > 0) {
			$body .= '<div>';
			$body .= '<br>';
			$body .= '</div>';

			$body .= '<div>';
			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

			$body .= '<thead>';

			$body .= '<tr>';
				$body .= '<th colspan="5" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
					$body .= '<b>Detalle</b>';
				$body .= '</th>';
			$body .= '</tr>';


			$body .= '</thead>';

			$body .= '<tr>';
				$body .= '<td align="center" style="background-color: #ffffdd; width: 20px;"><b>#</b></td>';
				$body .= '<td align="center" style="background-color: #ffffdd;"><b>Menu:</b></td>';
				$body .= '<td align="center" style="background-color: #ffffdd;"><b>Campo:</b></td>';
				$body .= '<td align="center" style="background-color: #ffffdd;"><b>Valor Original:</b></td>';
				$body .= '<td align="center" style="background-color: #ffffdd;"><b>Nuevo Valor:</b></td>';
			$body .= '</tr>';

			while($row = $query->fetch_assoc()){
				$nombre_menu_usuario = $row["nombre_menu_usuario"];
				$nombre_campo_usuario = $row["nombre_campo_usuario"];
				$valor_original = $row["valor_original"];
				$tipo_valor = $row["tipo_valor"];
				if ($tipo_valor == 'varchar') {
					$nuevo_valor = $row['valor_varchar'];
				} else if ($tipo_valor == 'int') {
					$nuevo_valor = $row['valor_int'];
				} else if ($tipo_valor == 'date') {
					$nuevo_valor = $row['valor_date'];
				} else if ($tipo_valor == 'decimal') {
					$nuevo_valor = $row['valor_decimal'];
				} else if ($tipo_valor == 'select_option') {
					$nuevo_valor = $row['valor_select_option'];
				}
				$numero_adenda_detalle++;
				$codigo = !Empty($row["codigo"]) ? '(#'.$row["codigo"].')' : '';

				$body .= '<tr>';
					$body .= '<td>'.$numero_adenda_detalle.'</td>';
					$body .= '<td>'.$nombre_menu_usuario.' '.$codigo.'</td>';
					$body .= '<td>'.$nombre_campo_usuario.'</td>';
					$body .= '<td>'.$valor_original.'</td>';
					$body .= '<td>'.$nuevo_valor.'</td>';
				$body .= '</tr>';

			}
			$body .= '</table>';
			$body .= '</div>';
		}

		$query_otros = "SELECT a.id, a.nombre_menu_usuario, a.valor_original,  a.valor_int, a.valor_id_tabla, cd.codigo
		FROM cont_adendas_detalle AS a
		LEFT JOIN cont_contrato_detalle AS cd ON cd.id = a.contrato_detalle_id
		WHERE a.tipo_valor = 'id_tabla'
			AND a.adenda_id = " . $adenda_id . "
			AND a.status = 1";

		$list_query_otros = $mysqli->query($query_otros);
		$row_count_otros = $list_query_otros->num_rows;

		if ($row_count_otros > 0) {
			$body .= '<div>';
			$body .= '<br>';
			$body .= '</div>';
			while ($row = $list_query_otros->fetch_assoc()) {
				$codigo_contrato = !Empty($row['codigo']) ? '- Contrato #'.$row['codigo']:'';
				$valores_originales = [];
				$valores_nuevos = [];
				
				if ($row["nombre_menu_usuario"] == 'Propietario') {
					$query = "
					SELECT
						p.id,
						tp.nombre AS tipo_persona,
						td.nombre AS tipo_docu_identidad,
						p.tipo_persona_id,
						p.tipo_docu_identidad_id,
						p.num_docu,
						p.nombre,
						p.direccion,
						p.representante_legal,
						p.num_partida_registral,
						p.contacto_nombre,
						p.contacto_telefono,
						p.contacto_email
					FROM
						cont_persona p
						INNER JOIN cont_tipo_persona tp ON p.tipo_persona_id = tp.id
						INNER JOIN cont_tipo_docu_identidad td ON p.tipo_docu_identidad_id = td.id
					WHERE
						p.id IN ('" . $row["valor_original"] . "' , '" . $row["valor_int"] . "')
					";

					$valores_originales = [];
					$valores_nuevos = [];
					$list_query = $mysqli->query($query);
					while ($li = $list_query->fetch_assoc()) {
						if ($li["id"] == $row["valor_original"]) {
							$valores_originales[] = $li;
						} else if ($li["id"] == $row["valor_int"]) {
							$valores_nuevos[] = $li;
						}
					}


					$body .= '<div>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

					$body .= '<thead>';

					$body .= '<tr>';
						$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
							$body .= '<b>Cambio de Propietario '.$codigo_contrato.'</b>';
						$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
						$body .= '<td align="center" style="background-color: #ffffdd;"><b>Campo:</b></td>';
						$body .= '<td align="center" style="background-color: #ffffdd;"><b>Valor Original:</b></td>';
						$body .= '<td align="center" style="background-color: #ffffdd;"><b>Nuevo Valor:</b></td>';
					$body .= '</tr>';

					$body .= '</thead>';

					$body .= '<tr>';
						$body .= '<td >Tipo de documento de persona</td>';
						$body .= '<td >'.$valores_originales[0]["tipo_persona"].'</td>';
						$body .= '<td >'.$valores_nuevos[0]["tipo_persona"].'</td>';
					$body .= '</tr>';

					$body .= '<tr>';
						$body .= '<td >Nombre</td>';
						$body .= '<td >'.$valores_originales[0]["nombre"].'</td>';
						$body .= '<td >'.$valores_nuevos[0]["nombre"].'</td>';
					$body .= '</tr>';

					$body .= '<tr>';
						$body .= '<td >Tipo de documento de identidad</td>';
						$body .= '<td >'.$valores_originales[0]["num_docu"].'</td>';
						$body .= '<td >'.$valores_nuevos[0]["num_docu"].'</td>';
					$body .= '</tr>';

					$body .= '<tr>';
						$body .= '<td >Direccion</td>';
						$body .= '<td >'.$valores_originales[0]["direccion"].'</td>';
						$body .= '<td >'.$valores_nuevos[0]["direccion"].'</td>';
					$body .= '</tr>';

					$body .= '<tr>';
						$body .= '<td >Representante legal</td>';
						$body .= '<td >'.$valores_originales[0]["representante_legal"].'</td>';
						$body .= '<td >'.$valores_nuevos[0]["representante_legal"].'</td>';
					$body .= '</tr>';

					$body .= '<tr>';
						$body .= '<td >N° de Partida Registral de la empresa</td>';
						$body .= '<td >'.$valores_originales[0]["num_partida_registral"].'</td>';
						$body .= '<td >'.$valores_nuevos[0]["num_partida_registral"].'</td>';
					$body .= '</tr>';

					$body .= '<tr>';
						$body .= '<td >Contacto - Nombre</td>';
						$body .= '<td >'.$valores_originales[0]["contacto_nombre"].'</td>';
						$body .= '<td >'.$valores_nuevos[0]["contacto_nombre"].'</td>';
					$body .= '</tr>';

					$body .= '<tr>';
						$body .= '<td >Contacto - Teléfono</td>';
						$body .= '<td >'.$valores_originales[0]["contacto_telefono"].'</td>';
						$body .= '<td >'.$valores_nuevos[0]["contacto_telefono"].'</td>';
					$body .= '</tr>';

					$body .= '<tr>';
						$body .= '<td >Contacto - Email</td>';
						$body .= '<td >'.$valores_originales[0]["contacto_email"].'</td>';
						$body .= '<td >'.$valores_nuevos[0]["contacto_email"].'</td>';
					$body .= '</tr>';

					$body .= '</table>';
					$body .= '</div>';
				}

				if ($row["nombre_menu_usuario"] == 'Beneficiario') {
					$query = "
						SELECT
							b.id,
							tp.nombre AS tipo_persona,
							td.nombre AS tipo_docu_identidad,
							b.num_docu,
							b.nombre,
							f.nombre AS forma_pago,
							ba.nombre AS banco,
							b.num_cuenta_bancaria,
							b.num_cuenta_cci,
							b.tipo_monto_id,
							tm.nombre AS tipo_monto,
							b.monto
						FROM
							cont_beneficiarios b
							LEFT JOIN cont_tipo_persona tp ON b.tipo_persona_id = tp.id
							LEFT JOIN cont_tipo_docu_identidad td ON b.tipo_docu_identidad_id = td.id
							INNER JOIN cont_forma_pago f ON b.forma_pago_id = f.id
							LEFT JOIN tbl_bancos ba ON b.banco_id = ba.id
							INNER JOIN cont_tipo_monto_a_depositar tm ON b.tipo_monto_id = tm.id
						WHERE
							b.id IN ('" . $row["valor_original"] . "' , '" . $row["valor_int"] . "')
						";

						$valores_originales = [];
						$valores_nuevos = [];
						$list_query = $mysqli->query($query);
						while ($li = $list_query->fetch_assoc()) {
							if ($li["id"] == $row["valor_original"]) {
								$valores_originales[] = $li;
							} else if ($li["id"] == $row["valor_int"]) {
								$valores_nuevos[] = $li;
							}
						}

						$body .= '<div>';
						$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

						$body .= '<thead>';

						$body .= '<tr>';
							$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
								$body .= '<b>Cambio de Beneficiario '.$codigo_contrato.'</b>';
							$body .= '</th>';
						$body .= '</tr>';

						$body .= '<tr>';
							$body .= '<td align="center" style="background-color: #ffffdd;"><b>Campo:</b></td>';
							$body .= '<td align="center" style="background-color: #ffffdd;"><b>Valor Original:</b></td>';
							$body .= '<td align="center" style="background-color: #ffffdd;"><b>Nuevo Valor:</b></td>';
						$body .= '</tr>';

						$body .= '</thead>';

						$body .= '<tr>';
							$body .= '<td >Tipo de documento de persona</td>';
							$body .= '<td >'.$valores_originales[0]["tipo_persona"].'</td>';
							$body .= '<td >'.$valores_nuevos[0]["tipo_persona"].'</td>';
						$body .= '</tr>';

						$body .= '<tr>';
							$body .= '<td >Nombre</td>';
							$body .= '<td >'.$valores_originales[0]["nombre"].'</td>';
							$body .= '<td >'.$valores_nuevos[0]["nombre"].'</td>';
						$body .= '</tr>';

						$body .= '<tr>';
							$body .= '<td >Tipo de documento de identidad</td>';
							$body .= '<td >'.$valores_originales[0]["tipo_docu_identidad"].'</td>';
							$body .= '<td >'.$valores_nuevos[0]["tipo_docu_identidad"].'</td>';
						$body .= '</tr>';

						$body .= '<tr>';
							$body .= '<td >Número de documento de identidad</td>';
							$body .= '<td >'.$valores_originales[0]["num_docu"].'</td>';
							$body .= '<td >'.$valores_nuevos[0]["num_docu"].'</td>';
						$body .= '</tr>';

						$body .= '<tr>';
							$body .= '<td >Tipo de forma de pago</td>';
							$body .= '<td >'.$valores_originales[0]["forma_pago"].'</td>';
							$body .= '<td >'.$valores_nuevos[0]["forma_pago"].'</td>';
						$body .= '</tr>';

						$body .= '<tr>';
							$body .= '<td >Nombre del Banco</td>';
							$body .= '<td >'.$valores_originales[0]["banco"].'</td>';
							$body .= '<td >'.$valores_nuevos[0]["banco"].'</td>';
						$body .= '</tr>';

						$body .= '<tr>';
							$body .= '<td >N° de la cuenta bancaria</td>';
							$body .= '<td >'.$valores_originales[0]["num_cuenta_bancaria"].'</td>';
							$body .= '<td >'.$valores_nuevos[0]["num_cuenta_bancaria"].'</td>';
						$body .= '</tr>';

						$body .= '<tr>';
							$body .= '<td >N° de CCI bancario</td>';
							$body .= '<td >'.$valores_originales[0]["num_cuenta_cci"].'</td>';
							$body .= '<td >'.$valores_nuevos[0]["num_cuenta_cci"].'</td>';
						$body .= '</tr>';

						$body .= '<tr>';
							$body .= '<td >Tipo de monto a depositar</td>';
							$body .= '<td >'.$valores_originales[0]["tipo_monto"].'</td>';
							$body .= '<td >'.$valores_nuevos[0]["tipo_monto"].'</td>';
						$body .= '</tr>';

						$body .= '<tr>';
							$body .= '<td >Monto</td>';
							$body .= '<td >'.$valores_originales[0]["monto"].'</td>';
							$body .= '<td >'.$valores_nuevos[0]["monto"].'</td>';
						$body .= '</tr>';

						$body .= '</table>';
						$body .= '</div>';
				}

				if ($row["nombre_menu_usuario"] == 'Incremento') {
					$query = "
						SELECT
							b.id,
							tp.nombre AS tipo_persona,
							td.nombre AS tipo_docu_identidad,
							b.num_docu,
							b.nombre,
							f.nombre AS forma_pago,
							ba.nombre AS banco,
							b.num_cuenta_bancaria,
							b.num_cuenta_cci,
							b.tipo_monto_id,
							tm.nombre AS tipo_monto,
							b.monto
						FROM
							cont_beneficiarios b
							LEFT JOIN cont_tipo_persona tp ON b.tipo_persona_id = tp.id
							LEFT JOIN cont_tipo_docu_identidad td ON b.tipo_docu_identidad_id = td.id
							INNER JOIN cont_forma_pago f ON b.forma_pago_id = f.id
							LEFT JOIN tbl_bancos ba ON b.banco_id = ba.id
							INNER JOIN cont_tipo_monto_a_depositar tm ON b.tipo_monto_id = tm.id
						WHERE
							b.id IN ('" . $row["valor_original"] . "' , '" . $row["valor_int"] . "')
						";

						$valores_originales = [];
						$valores_nuevos = [];
						$list_query = $mysqli->query($query);
						while ($li = $list_query->fetch_assoc()) {
							if ($li["id"] == $row["valor_original"]) {
								$valores_originales[] = $li;
							} else if ($li["id"] == $row["valor_int"]) {
								$valores_nuevos[] = $li;
							}
						}

						$body .= '<div>';
						$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

						$body .= '<thead>';

						$body .= '<tr>';
							$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
								$body .= '<b>Cambio de Incremento '.$codigo_contrato.'</b>';
							$body .= '</th>';
						$body .= '</tr>';

						$body .= '<tr>';
							$body .= '<td align="center" style="background-color: #ffffdd;"><b>Campo:</b></td>';
							$body .= '<td align="center" style="background-color: #ffffdd;"><b>Valor Original:</b></td>';
							$body .= '<td align="center" style="background-color: #ffffdd;"><b>Nuevo Valor:</b></td>';
						$body .= '</tr>';

						$body .= '</thead>';

						$body .= '<tr>';
							$body .= '<td >Tipo de documento de persona</td>';
							$body .= '<td >'.$valores_originales[0]["tipo_persona"].'</td>';
							$body .= '<td >'.$valores_nuevos[0]["tipo_persona"].'</td>';
						$body .= '</tr>';

						$body .= '<tr>';
							$body .= '<td >Nombre</td>';
							$body .= '<td >'.$valores_originales[0]["nombre"].'</td>';
							$body .= '<td >'.$valores_nuevos[0]["nombre"].'</td>';
						$body .= '</tr>';

						$body .= '<tr>';
							$body .= '<td >Tipo de documento de identidad</td>';
							$body .= '<td >'.$valores_originales[0]["tipo_docu_identidad"].'</td>';
							$body .= '<td >'.$valores_nuevos[0]["tipo_docu_identidad"].'</td>';
						$body .= '</tr>';

						$body .= '<tr>';
							$body .= '<td >Número de documento de identidad</td>';
							$body .= '<td >'.$valores_originales[0]["num_docu"].'</td>';
							$body .= '<td >'.$valores_nuevos[0]["num_docu"].'</td>';
						$body .= '</tr>';

						$body .= '<tr>';
							$body .= '<td >Tipo de forma de pago</td>';
							$body .= '<td >'.$valores_originales[0]["forma_pago"].'</td>';
							$body .= '<td >'.$valores_nuevos[0]["forma_pago"].'</td>';
						$body .= '</tr>';

						$body .= '<tr>';
							$body .= '<td >Nombre del Banco</td>';
							$body .= '<td >'.$valores_originales[0]["banco"].'</td>';
							$body .= '<td >'.$valores_nuevos[0]["banco"].'</td>';
						$body .= '</tr>';

						$body .= '<tr>';
							$body .= '<td >N° de la cuenta bancaria</td>';
							$body .= '<td >'.$valores_originales[0]["num_cuenta_bancaria"].'</td>';
							$body .= '<td >'.$valores_nuevos[0]["num_cuenta_bancaria"].'</td>';
						$body .= '</tr>';

						$body .= '<tr>';
							$body .= '<td >N° de CCI bancario</td>';
							$body .= '<td >'.$valores_originales[0]["num_cuenta_cci"].'</td>';
							$body .= '<td >'.$valores_nuevos[0]["num_cuenta_cci"].'</td>';
						$body .= '</tr>';

						$body .= '<tr>';
							$body .= '<td >Tipo de monto a depositar</td>';
							$body .= '<td >'.$valores_originales[0]["tipo_monto"].'</td>';
							$body .= '<td >'.$valores_nuevos[0]["tipo_monto"].'</td>';
						$body .= '</tr>';

						$body .= '<tr>';
							$body .= '<td >Monto</td>';
							$body .= '<td >'.$valores_originales[0]["monto"].'</td>';
							$body .= '<td >'.$valores_nuevos[0]["monto"].'</td>';
						$body .= '</tr>';

						$body .= '</table>';
						$body .= '</div>';
				}

				if ($row["nombre_menu_usuario"] == 'Responsable IR') {
					$query = "
						SELECT 
							r.id,
							r.contrato_id,
							r.tipo_documento_id,
							r.num_documento,
							r.nombres,
							r.estado_emisor,
							r.porcentaje,
							r.status,
							r.user_created_id,
							r.created_at,
							td.nombre AS tipo_documento
						FROM cont_responsable_ir as r
						LEFT JOIN cont_tipo_docu_identidad AS td ON td.id = r.tipo_documento_id
						WHERE r.id IN ('" . $row["valor_original"] . "' , '" . $row["valor_int"] . "')";

						$valores_originales = [];
						$valores_nuevos = [];
						$list_query = $mysqli->query($query);
						while ($li = $list_query->fetch_assoc()) {
							if ($li["id"] == $row["valor_original"]) {
								$valores_originales[] = $li;
							} else if ($li["id"] == $row["valor_int"]) {
								$valores_nuevos[] = $li;
							}
						}

						$body .= '<div>';
						$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

						$body .= '<thead>';

						$body .= '<tr>';
							$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
								$body .= '<b>Cambio Responsable IR '.$codigo_contrato.'</b>';
							$body .= '</th>';
						$body .= '</tr>';

						$body .= '<tr>';
							$body .= '<td align="center" style="background-color: #ffffdd;"><b>Campo:</b></td>';
							$body .= '<td align="center" style="background-color: #ffffdd;"><b>Valor Original:</b></td>';
							$body .= '<td align="center" style="background-color: #ffffdd;"><b>Nuevo Valor:</b></td>';
						$body .= '</tr>';

						$body .= '</thead>';

						$body .= '<tr>';
						$body .= '<td>Tipo de Documento de Identidad</td>';
						$body .= '<td>' . $valores_originales[0]["tipo_documento"] . '</td>';
						$body .= '<td>' . $valores_nuevos[0]["tipo_documento"] . '</td>';
						$body .= '</tr>';

						$body .= '<tr>';
						$body .= '<td>Nro Documento</td>';
						$body .= '<td>' . $valores_originales[0]["num_documento"] . '</td>';
						$body .= '<td>' . $valores_nuevos[0]["num_documento"] . '</td>';
						$body .= '</tr>';

						$body .= '<tr>';
						$body .= '<td>Nombres</td>';
						$body .= '<td>' . $valores_originales[0]["nombres"] . '</td>';
						$body .= '<td>' . $valores_nuevos[0]["nombres"] . '</td>';
						$body .= '</tr>';

						$body .= '<tr>';
						$body .= '<td>Porcentaje</td>';
						$body .= '<td>' . $valores_originales[0]["porcentaje"] . '</td>';
						$body .= '<td>' . $valores_nuevos[0]["porcentaje"] . '</td>';
						$body .= '</tr>';
						$body .= '</table>';
						$body .= '</div>';
				}



			}
		}

		// NUEVOS PROPIETARIOS Y BENEFICIARIOS Y INCREMENTOS
		$query_otros = "SELECT a.id, a.nombre_menu_usuario, a.valor_original,  a.valor_int, a.valor_id_tabla, cd.codigo
		FROM cont_adendas_detalle AS a
		LEFT JOIN cont_contrato_detalle AS cd ON cd.id = a.contrato_detalle_id
		WHERE a.tipo_valor = 'registro'
			AND a.adenda_id = " . $adenda_id . "
			AND a.status = 1";

		$list_query_otros = $mysqli->query($query_otros);
		$row_count_otros = $list_query_otros->num_rows;

		if ($row_count_otros > 0) {
			while ($row = $list_query_otros->fetch_assoc()) {
				$codigo_contrato = !Empty($row['codigo']) ? '- Contrato #'.$row['codigo']:'';
				$valores_originales = [];
				$valores_nuevos = [];

				if ($row["nombre_menu_usuario"] == 'Inflación') {
					$query = "SELECT i.id, DATE_FORMAT(i.fecha, '%d-%m-%Y') as fecha, tp.nombre  AS tipo_periodicidad, i.numero, p.nombre as tipo_anio_mes, m.sigla AS moneda, i.porcentaje_anadido, i.tope_inflacion, i.minimo_inflacion
					FROM cont_inflaciones AS i
					INNER JOIN cont_tipo_periodicidad AS tp ON tp.id = i.tipo_periodicidad_id
					LEFT JOIN tbl_moneda AS m ON m.id = i.moneda_id
					LEFT JOIN cont_periodo as p ON p.id = i.tipo_anio_mes
					WHERE i.id IN ('" . $row["valor_int"] . "')
					";

					$valores_originales = [];
					$valores_nuevos = [];
					$list_query = $mysqli->query($query);
					while ($li = $list_query->fetch_assoc()) {
						if ($li["id"] == $row["valor_int"]) {
							$valores_nuevos[] = $li;
						}
					}

					$body .= '<br>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<thead>';

					$body .= '<tr>';
						$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
							$body .= '<b>Nueva Inflación '.$codigo_contrato.'</b>';
						$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<th>Campo</th>';
					$body .= '<th>Nuevo Valor</th>';
					$body .= '</tr>';

					$body .= '</thead>';
					$body .= '<tbody>';

					$body .= '<tr>';
					$body .= '<td>Fecha de Ajuste</td>';
					$body .= '<td>' . $valores_nuevos[0]["fecha"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Periodicidad</td>';
					$body .= '<td>' . $valores_nuevos[0]['tipo_periodicidad'].' '.$valores_nuevos[0]['numero'].' '.$valores_nuevos[0]['tipo_anio_mes'] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Curva</td>';
					$body .= '<td>' . $valores_nuevos[0]["moneda"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Porcentaje Añadido</td>';
					$body .= '<td>' . $valores_nuevos[0]["porcentaje_anadido"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Tope de Inflación</td>';
					$body .= '<td>' . $valores_nuevos[0]["tope_inflacion"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Minimo de Inflación</td>';
					$body .= '<td>' . $valores_nuevos[0]["minimo_inflacion"] . '</td>';
					$body .= '</tr>';


					$body .= '</tbody>';
					$body .= '</table>';
				}

				if ($row["nombre_menu_usuario"] == 'Cuota Extraordinaria') {
					$query = "SELECT c.id, m.nombre as mes, c.multiplicador, c.fecha
					FROM cont_cuotas_extraordinarias AS c
					INNER JOIN tbl_meses AS m ON m.id = c.mes
					WHERE c.id IN ('" . $row["valor_int"] . "')
					";

					$valores_originales = [];
					$valores_nuevos = [];
					$list_query = $mysqli->query($query);
					while ($li = $list_query->fetch_assoc()) {
						if ($li["id"] == $row["valor_int"]) {
							$valores_nuevos[] = $li;
						}
					}

					$body .= '<br>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<thead>';

					$body .= '<tr>';
						$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
							$body .= '<b>Nueva Cuota Extraordinaria '.$codigo_contrato.'</b>';
						$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<th>Campo</th>';
					$body .= '<th>Nuevo Valor</th>';
					$body .= '</tr>';

					$body .= '</thead>';
					$body .= '<tbody>';

					$body .= '<tr>';
					$body .= '<td>Fecha de Ajuste</td>';
					$body .= '<td>' . $valores_nuevos[0]["fecha"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Mes</td>';
					$body .= '<td>' . $valores_nuevos[0]["mes"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Multiplicador</td>';
					$body .= '<td>' . $valores_nuevos[0]["multiplicador"] . '</td>';
					$body .= '</tr>';

					$body .= '</tbody>';
					$body .= '</table>';
				}

				if ($row["nombre_menu_usuario"] == 'Propietario') {
					$query = "
						SELECT p.id AS persona_id,
							pr.propietario_id,
							tp.nombre AS tipo_persona,
							p.tipo_docu_identidad_id,
							td.nombre AS tipo_docu_identidad,
							p.num_docu,
							p.num_ruc,
							p.nombre,
							p.direccion,
							p.representante_legal,
							p.num_partida_registral,
							p.contacto_nombre,
							p.contacto_telefono,
							p.contacto_email
						FROM cont_propietario pr
						INNER JOIN cont_persona p ON pr.persona_id = p.id
						INNER JOIN cont_tipo_persona tp ON p.tipo_persona_id = tp.id
						INNER JOIN cont_tipo_docu_identidad td ON p.tipo_docu_identidad_id = td.id
						WHERE pr.propietario_id IN ('" . $row["valor_int"] . "')
					";

					$valores_originales = [];
					$valores_nuevos = [];
					$list_query = $mysqli->query($query);
					while ($li = $list_query->fetch_assoc()) {
						if ($li["propietario_id"] == $row["valor_int"]) {
							$valores_nuevos[] = $li;
						}
					}

					$body .= '<br>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<thead>';

					$body .= '<tr>';
						$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
							$body .= '<b>Nuevo Propietario '.$codigo_contrato.'</b>';
						$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<th>Campo</th>';
					$body .= '<th>Nuevo Valor</th>';
					$body .= '</tr>';

					$body .= '</thead>';
					$body .= '<tbody>';

					$body .= '<tr>';
					$body .= '<td>Tipo Persona</td>';
					$body .= '<td>' . $valores_nuevos[0]["tipo_persona"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Nombre</td>';
					$body .= '<td>' . $valores_nuevos[0]["nombre"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Tipo de Documento de Identidad</td>';
					$body .= '<td>' . $valores_nuevos[0]["tipo_docu_identidad"] . '</td>';
					$body .= '</tr>';

					if ($valores_nuevos[0]["tipo_docu_identidad_id"] != "2") {
						$body .= '<tr>';
						$body .= '<td>'.$valores_nuevos[0]["tipo_docu_identidad"].'</td>';
						$body .= '<td>' . $valores_nuevos[0]["num_docu"] . '</td>';
						$body .= '</tr>';
					}

					$body .= '<tr>';
					$body .= '<td>Número de RUC</td>';
					$body .= '<td>' . $valores_nuevos[0]["num_ruc"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Domicilio del propietario</td>';
					$body .= '<td>' . $valores_nuevos[0]["direccion"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Representante Legal</td>';
					$body .= '<td>' . $valores_nuevos[0]["representante_legal"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>N° de Partida Registral de la empresa</td>';
					$body .= '<td>' . $valores_nuevos[0]["num_partida_registral"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Persona de contacto</td>';
					$body .= '<td>' . $valores_nuevos[0]["contacto_nombre"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Teléfono de la persona de contacto</td>';
					$body .= '<td>' . $valores_nuevos[0]["contacto_telefono"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>E-mail de la persona de contacto</td>';
					$body .= '<td>' . $valores_nuevos[0]["contacto_email"] . '</td>';
					$body .= '</tr>';

					$body .= '</tbody>';
					$body .= '</table>';
				}

				if ($row["nombre_menu_usuario"] == 'Beneficiario') {
					$query = "
							SELECT b.id,
							tp.nombre AS tipo_persona,
							td.nombre AS tipo_docu_identidad,
							b.num_docu,
							b.nombre,
							f.nombre AS forma_pago,
							ba.nombre AS banco,
							b.num_cuenta_bancaria,
							b.num_cuenta_cci,
							b.tipo_monto_id,
							b.monto
						FROM cont_beneficiarios b
						LEFT JOIN cont_tipo_persona tp ON b.tipo_persona_id = tp.id
						LEFT JOIN cont_tipo_docu_identidad td ON b.tipo_docu_identidad_id = td.id
						INNER JOIN cont_forma_pago f ON b.forma_pago_id = f.id
						LEFT JOIN tbl_bancos ba ON b.banco_id = ba.id
						WHERE b.id = " . $row["valor_int"];

					$valores_originales = [];
					$valores_nuevos = [];
					$list_query = $mysqli->query($query);
					while ($li = $list_query->fetch_assoc()) {
						if ($li["id"] == $row["valor_int"]) {
							$valores_nuevos[] = $li;
						}
					}

					$beneficiario_id = $valores_nuevos[0]["id"];
					$ben_tipo_persona = $valores_nuevos[0]["tipo_persona"];
					$ben_tipo_docu_identidad = $valores_nuevos[0]["tipo_docu_identidad"];
					$ben_num_docu = $valores_nuevos[0]["num_docu"];
					$ben_nombre = $valores_nuevos[0]["nombre"];
					$ben_direccion = '';
					$ben_forma_pago = $valores_nuevos[0]["forma_pago"];
					$ben_banco = $valores_nuevos[0]["banco"];
					$ben_num_cuenta_bancaria = $valores_nuevos[0]["num_cuenta_bancaria"];
					$ben_num_cuenta_cci = $valores_nuevos[0]["num_cuenta_cci"];
					$ben_tipo_monto_id = $valores_nuevos[0]["tipo_monto_id"];


					$ben_monto_beneficiario = number_format($valores_nuevos[0]["monto"], 2, '.', ',');


					$body .= '<br>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<thead>';

					$body .= '<tr>';
						$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
							$body .= '<b>Nuevo Beneficario '.$codigo_contrato.'</b>';
						$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<th>Campo</th>';
					$body .= '<th>Nuevo Valor</th>';
					$body .= '</tr>';

					$body .= '</thead>';
					$body .= '<tbody>';

					$body .= '<tr>';
					$body .= '<td>Tipo de Persona</td>';
					$body .= '<td>' . $ben_tipo_persona . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Nombre</td>';
					$body .= '<td>' . $ben_nombre . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Tipo de Documento de Identidad</td>';
					$body .= '<td>' . $ben_tipo_docu_identidad . '</td>';
					$body .= '</tr>';


					$body .= '<tr>';
					$body .= '<td>Número de Documento de Identidad</td>';
					$body .= '<td>' . $ben_num_docu . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Tipo de forma de pago</td>';
					$body .= '<td>' . $ben_forma_pago . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Nombre del Banco</td>';
					$body .= '<td>' . $ben_banco . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>N° de la cuenta bancaria</td>';
					$body .= '<td>' . $ben_num_cuenta_bancaria . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>N° de CCI bancario</td>';
					$body .= '<td>' . $ben_num_cuenta_cci . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Monto</td>';
					$body .= '<td>' . $ben_monto_beneficiario . '</td>';
					$body .= '</tr>';



					$body .= '</tbody>';
					$body .= '</table>';
				}

				if ($row["nombre_menu_usuario"] == 'Incremento') {
					$query = "
					SELECT
						i.id,
						i.valor,
						i.tipo_valor_id,
						tp.nombre AS tipo_valor,
						i.tipo_continuidad_id,
						tc.nombre AS tipo_continuidad,
						i.a_partir_del_año
					FROM
						cont_incrementos i
						INNER JOIN cont_tipo_pago_incrementos tp ON i.tipo_valor_id = tp.id
						INNER JOIN cont_tipo_continuidad_pago tc ON i.tipo_continuidad_id = tc.id
					WHERE
						i.id = " . $row["valor_int"];


					$list_query = $mysqli->query($query);
					while ($li = $list_query->fetch_assoc()) {
						$valor_nuevo = $li;
					}

					$body .= '<br>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<thead>';

					$body .= '<tr>';
						$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
							$body .= '<b>Nuevo Incremento '.$codigo_contrato.'</b>';
						$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<th>Campo</th>';
					$body .= '<th>Valor Actual</th>';
					$body .= '<th>Nuevo Valor</th>';
					$body .= '</tr>';

					$body .= '</thead>';
					$body .= '<tbody>';

					$body .= '<tr>';
					$body .= '<td>Valor</td>';
					$body .= '<td></td>';
					$body .= '<td>' . $valor_nuevo["valor"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Tipo Valor</td>';
					$body .= '<td></td>';
					$body .= '<td>' . $valor_nuevo["tipo_valor"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Continuidad</td>';
					$body .= '<td></td>';
					$body .= '<td>' . $valor_nuevo["tipo_continuidad"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Apartir del</td>';
					$body .= '<td></td>';
					$body .= '<td>' . $valor_nuevo["a_partir_del_año"] . '</td>';
					$body .= '</tr>';

					$body .= '</tbody>';
					$body .= '</table>';
				}

				if ($row["nombre_menu_usuario"] == 'Responsable IR') {
					$query = "
					SELECT 
						r.id,
						r.contrato_id,
						r.tipo_documento_id,
						r.num_documento,
						r.nombres,
						r.estado_emisor,
						r.porcentaje,
						r.status,
						r.user_created_id,
						r.created_at,
						td.nombre AS tipo_documento
					FROM cont_responsable_ir as r
					LEFT JOIN cont_tipo_docu_identidad AS td ON td.id = r.tipo_documento_id
					WHERE r.id = " . $row["valor_int"];


					$list_query = $mysqli->query($query);
					while ($li = $list_query->fetch_assoc()) {
						$valor_nuevo = $li;
					}

					$body .= '<br>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<thead>';

					$body .= '<tr>';
						$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
							$body .= '<b>Nuevo Responsable IR '.$codigo_contrato.'</b>';
						$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<th>Campo</th>';
					$body .= '<th>Nuevo Valor</th>';
					$body .= '</tr>';

					$body .= '</thead>';
					$body .= '<tbody>';

					$body .= '<tr>';
					$body .= '<td>Tipo de Documento de Identidad</td>';
					$body .= '<td>' . $valor_nuevo["tipo_documento"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Nro Documento</td>';
					$body .= '<td>' . $valor_nuevo["num_documento"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Nombres</td>';
					$body .= '<td>' . $valor_nuevo["nombres"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Porcentaje</td>';
					$body .= '<td>' . $valor_nuevo["porcentaje"] . '</td>';
					$body .= '</tr>';

					$body .= '</tbody>';
					$body .= '</table>';
				}

				if ($row["nombre_menu_usuario"] == 'Suministro') {
					$query = "SELECT s.id, s.tipo_servicio_id, sp.nombre AS tipo_servicio, s.nro_suministro, s.tipo_compromiso_pago_id, ps.nombre AS tipo_compromiso, s.monto_o_porcentaje
					FROM cont_inmueble_suministros AS s
					LEFT JOIN cont_tipo_pago_servicio AS ps ON ps.id = s.tipo_compromiso_pago_id
					LEFT JOIN cont_tipo_servicio_publico AS sp ON sp.id = s.tipo_servicio_id
					INNER JOIN cont_inmueble AS i ON i.id = s.inmueble_id
					INNER JOIN cont_contrato_detalle AS cd ON cd.id = i.contrato_detalle_id
					WHERE s.id = " . $row["valor_int"];


					$list_query = $mysqli->query($query);
					while ($li = $list_query->fetch_assoc()) {
						$valor_nuevo = $li;
					}

					$body .= '<br>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<thead>';

					$body .= '<tr>';
						$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
							$body .= '<b>Nuevo Suministro '.$codigo_contrato.'</b>';
						$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<th>Campo</th>';
					$body .= '<th>Nuevo Valor</th>';
					$body .= '</tr>';

					$body .= '</thead>';
					$body .= '<tbody>';

					$body .= '<tr>';
					$body .= '<td>Tipo de Servicio</td>';
					$body .= '<td>' . $valor_nuevo["tipo_servicio"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>N° de Suministro</td>';
					$body .= '<td>' . $valor_nuevo["nro_suministro"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Compromiso de pago</td>';
					$body .= '<td>' . $valor_nuevo["tipo_compromiso"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Monto/Porcentaje</td>';
					$body .= '<td>' . $valor_nuevo["monto_o_porcentaje"] . '</td>';
					$body .= '</tr>';

					$body .= '</tbody>';
					$body .= '</table>';
				}
			}
		}


		// eliminar PROPIETARIOS Y BENEFICIARIOS Y INCREMENTOS
		$query_otros = "SELECT a.id, a.nombre_menu_usuario, a.valor_original,  a.valor_int, a.valor_id_tabla, cd.codigo
		FROM cont_adendas_detalle AS a
		LEFT JOIN cont_contrato_detalle AS cd ON cd.id = a.contrato_detalle_id
		WHERE a.tipo_valor = 'eliminar'
			AND a.adenda_id = " . $adenda_id . "
			AND a.status = 1";

		$list_query_otros = $mysqli->query($query_otros);
		$row_count_otros = $list_query_otros->num_rows;

		if ($row_count_otros > 0) {
			while ($row = $list_query_otros->fetch_assoc()) {
				$codigo_contrato = !Empty($row['codigo']) ? '- Contrato #'.$row['codigo']:'';
				$valores_originales = [];
				$valores_nuevos = [];

				if ($row["nombre_menu_usuario"] == 'Inflación') {
					$query = "SELECT i.id, DATE_FORMAT(i.fecha, '%d-%m-%Y') as fecha, tp.nombre  AS tipo_periodicidad, i.numero, p.nombre as tipo_anio_mes, m.sigla AS moneda, i.porcentaje_anadido, i.tope_inflacion, i.minimo_inflacion
					FROM cont_inflaciones AS i
					INNER JOIN cont_tipo_periodicidad AS tp ON tp.id = i.tipo_periodicidad_id
					LEFT JOIN tbl_moneda AS m ON m.id = i.moneda_id
					LEFT JOIN cont_periodo as p ON p.id = i.tipo_anio_mes
					WHERE i.id IN ('" . $row["valor_int"] . "')
					";

					$valores_originales = [];
					$valores_nuevos = [];
					$list_query = $mysqli->query($query);
					while ($li = $list_query->fetch_assoc()) {
						if ($li["id"] == $row["valor_int"]) {
							$valores_nuevos[] = $li;
						}
					}

					$body .= '<br>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<thead>';

					$body .= '<tr>';
						$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
							$body .= '<b>Eliminar Inflación '.$codigo_contrato.'</b>';
						$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<th>Campo</th>';
					$body .= '<th>Nuevo Valor</th>';
					$body .= '</tr>';

					$body .= '</thead>';
					$body .= '<tbody>';

					$body .= '<tr>';
					$body .= '<td>Fecha de Ajuste</td>';
					$body .= '<td>' . $valores_nuevos[0]["fecha"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Periodicidad</td>';
					$body .= '<td>' . $valores_nuevos[0]['tipo_periodicidad'].' '.$valores_nuevos[0]['numero'].' '.$valores_nuevos[0]['tipo_anio_mes'] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Curva</td>';
					$body .= '<td>' . $valores_nuevos[0]["moneda"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Porcentaje Añadido</td>';
					$body .= '<td>' . $valores_nuevos[0]["porcentaje_anadido"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Tope de Inflación</td>';
					$body .= '<td>' . $valores_nuevos[0]["tope_inflacion"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Minimo de Inflación</td>';
					$body .= '<td>' . $valores_nuevos[0]["minimo_inflacion"] . '</td>';
					$body .= '</tr>';


					$body .= '</tbody>';
					$body .= '</table>';
				}

				if ($row["nombre_menu_usuario"] == 'Cuota Extraordinaria') {
					$query = "SELECT c.id, m.nombre as mes, c.multiplicador, c.fecha
					FROM cont_cuotas_extraordinarias AS c
					INNER JOIN tbl_meses AS m ON m.id = c.mes
					WHERE c.id IN ('" . $row["valor_int"] . "')
					";

					$valores_originales = [];
					$valores_nuevos = [];
					$list_query = $mysqli->query($query);
					while ($li = $list_query->fetch_assoc()) {
						if ($li["id"] == $row["valor_int"]) {
							$valores_nuevos[] = $li;
						}
					}

					$body .= '<br>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<thead>';

					$body .= '<tr>';
						$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
							$body .= '<b>Eliminar Cuota Extraordinaria '.$codigo_contrato.'</b>';
						$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<th>Campo</th>';
					$body .= '<th>Nuevo Valor</th>';
					$body .= '</tr>';

					$body .= '</thead>';
					$body .= '<tbody>';

					$body .= '<tr>';
					$body .= '<td>Fecha de Ajuste</td>';
					$body .= '<td>' . $valores_nuevos[0]["fecha"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Mes</td>';
					$body .= '<td>' . $valores_nuevos[0]["mes"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Multiplicador</td>';
					$body .= '<td>' . $valores_nuevos[0]["multiplicador"] . '</td>';
					$body .= '</tr>';

					$body .= '</tbody>';
					$body .= '</table>';
				}

				if ($row["nombre_menu_usuario"] == 'Propietario') {
					$query = "
						SELECT p.id AS persona_id,
							pr.propietario_id,
							tp.nombre AS tipo_persona,
							p.tipo_docu_identidad_id,
							td.nombre AS tipo_docu_identidad,
							p.num_docu,
							p.num_ruc,
							p.nombre,
							p.direccion,
							p.representante_legal,
							p.num_partida_registral,
							p.contacto_nombre,
							p.contacto_telefono,
							p.contacto_email
						FROM cont_propietario pr
						INNER JOIN cont_persona p ON pr.persona_id = p.id
						INNER JOIN cont_tipo_persona tp ON p.tipo_persona_id = tp.id
						INNER JOIN cont_tipo_docu_identidad td ON p.tipo_docu_identidad_id = td.id
						WHERE pr.propietario_id IN ('" . $row["valor_int"] . "')
					";

					$valores_originales = [];
					$valores_nuevos = [];
					$list_query = $mysqli->query($query);
					while ($li = $list_query->fetch_assoc()) {
						if ($li["propietario_id"] == $row["valor_int"]) {
							$valores_nuevos[] = $li;
						}
					}

					$body .= '<br>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<thead>';

					$body .= '<tr>';
						$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
							$body .= '<b>Eliminar Propietario '.$codigo_contrato.'</b>';
						$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<th>Campo</th>';
					$body .= '<th>Valor</th>';
					$body .= '</tr>';

					$body .= '</thead>';
					$body .= '<tbody>';

					$body .= '<tr>';
					$body .= '<td>Tipo Persona</td>';
					$body .= '<td>' . $valores_nuevos[0]["tipo_persona"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Nombre</td>';
					$body .= '<td>' . $valores_nuevos[0]["nombre"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Tipo de Documento de Identidad</td>';
					$body .= '<td>' . $valores_nuevos[0]["tipo_docu_identidad"] . '</td>';
					$body .= '</tr>';

					if ($valores_nuevos[0]["tipo_docu_identidad_id"] != "2") {
						$body .= '<tr>';
						$body .= '<td>'.$valores_nuevos[0]["tipo_docu_identidad"].'</td>';
						$body .= '<td>' . $valores_nuevos[0]["num_docu"] . '</td>';
						$body .= '</tr>';
					}

					$body .= '<tr>';
					$body .= '<td>Número de RUC</td>';
					$body .= '<td>' . $valores_nuevos[0]["num_ruc"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Domicilio del propietario</td>';
					$body .= '<td>' . $valores_nuevos[0]["direccion"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Representante Legal</td>';
					$body .= '<td>' . $valores_nuevos[0]["representante_legal"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>N° de Partida Registral de la empresa</td>';
					$body .= '<td>' . $valores_nuevos[0]["num_partida_registral"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Persona de contacto</td>';
					$body .= '<td>' . $valores_nuevos[0]["contacto_nombre"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Teléfono de la persona de contacto</td>';
					$body .= '<td>' . $valores_nuevos[0]["contacto_telefono"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>E-mail de la persona de contacto</td>';
					$body .= '<td>' . $valores_nuevos[0]["contacto_email"] . '</td>';
					$body .= '</tr>';

					$body .= '</tbody>';
					$body .= '</table>';
				}

				if ($row["nombre_menu_usuario"] == 'Beneficiario') {
					$query = "
							SELECT b.id,
							tp.nombre AS tipo_persona,
							td.nombre AS tipo_docu_identidad,
							b.num_docu,
							b.nombre,
							f.nombre AS forma_pago,
							ba.nombre AS banco,
							b.num_cuenta_bancaria,
							b.num_cuenta_cci,
							b.tipo_monto_id,
							b.monto
						FROM cont_beneficiarios b
						LEFT JOIN cont_tipo_persona tp ON b.tipo_persona_id = tp.id
						LEFT JOIN cont_tipo_docu_identidad td ON b.tipo_docu_identidad_id = td.id
						INNER JOIN cont_forma_pago f ON b.forma_pago_id = f.id
						LEFT JOIN tbl_bancos ba ON b.banco_id = ba.id
						WHERE b.id = " . $row["valor_int"];

					$valores_originales = [];
					$valores_nuevos = [];
					$list_query = $mysqli->query($query);
					while ($li = $list_query->fetch_assoc()) {
						if ($li["id"] == $row["valor_int"]) {
							$valores_nuevos[] = $li;
						}
					}

					$beneficiario_id = $valores_nuevos[0]["id"];
					$ben_tipo_persona = $valores_nuevos[0]["tipo_persona"];
					$ben_tipo_docu_identidad = $valores_nuevos[0]["tipo_docu_identidad"];
					$ben_num_docu = $valores_nuevos[0]["num_docu"];
					$ben_nombre = $valores_nuevos[0]["nombre"];
					$ben_direccion = '';
					$ben_forma_pago = $valores_nuevos[0]["forma_pago"];
					$ben_banco = $valores_nuevos[0]["banco"];
					$ben_num_cuenta_bancaria = $valores_nuevos[0]["num_cuenta_bancaria"];
					$ben_num_cuenta_cci = $valores_nuevos[0]["num_cuenta_cci"];
					$ben_tipo_monto_id = $valores_nuevos[0]["tipo_monto_id"];


					$ben_monto_beneficiario = number_format($valores_nuevos[0]["monto"], 2, '.', ',');


					$body .= '<br>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<thead>';

					$body .= '<tr>';
						$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
							$body .= '<b>Eliminar Beneficario '.$codigo_contrato.'</b>';
						$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<th>Campo</th>';
					$body .= '<th>Valor</th>';
					$body .= '</tr>';

					$body .= '</thead>';
					$body .= '<tbody>';

					$body .= '<tr>';
					$body .= '<td>Tipo de Persona</td>';
					$body .= '<td>' . $ben_tipo_persona . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Nombre</td>';
					$body .= '<td>' . $ben_nombre . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Tipo de Documento de Identidad</td>';
					$body .= '<td>' . $ben_tipo_docu_identidad . '</td>';
					$body .= '</tr>';


					$body .= '<tr>';
					$body .= '<td>Número de Documento de Identidad</td>';
					$body .= '<td>' . $ben_num_docu . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Tipo de forma de pago</td>';
					$body .= '<td>' . $ben_forma_pago . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Nombre del Banco</td>';
					$body .= '<td>' . $ben_banco . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>N° de la cuenta bancaria</td>';
					$body .= '<td>' . $ben_num_cuenta_bancaria . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>N° de CCI bancario</td>';
					$body .= '<td>' . $ben_num_cuenta_cci . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Monto</td>';
					$body .= '<td>' . $ben_monto_beneficiario . '</td>';
					$body .= '</tr>';



					$body .= '</tbody>';
					$body .= '</table>';
				}

				if ($row["nombre_menu_usuario"] == 'Responsable IR') {
					$query = "
					SELECT 
						r.id,
						r.contrato_id,
						r.tipo_documento_id,
						r.num_documento,
						r.nombres,
						r.estado_emisor,
						r.porcentaje,
						r.status,
						r.user_created_id,
						r.created_at,
						td.nombre AS tipo_documento
					FROM cont_responsable_ir as r
					LEFT JOIN cont_tipo_docu_identidad AS td ON td.id = r.tipo_documento_id
					WHERE r.id = " . $row["valor_int"];


					$list_query = $mysqli->query($query);
					while ($li = $list_query->fetch_assoc()) {
						$valor_nuevo = $li;
					}

					$body .= '<br>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<thead>';

					$body .= '<tr>';
						$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
							$body .= '<b>Eliminar Responsable IR '.$codigo_contrato.'</b>';
						$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<th>Campo</th>';
					$body .= '<th>Valor</th>';
					$body .= '</tr>';

					$body .= '</thead>';
					$body .= '<tbody>';

					$body .= '<tr>';
					$body .= '<td>Tipo de Documento de Identidad</td>';
					$body .= '<td>' . $valor_nuevo["tipo_documento"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Nro Documento</td>';
					$body .= '<td>' . $valor_nuevo["num_documento"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Nombres</td>';
					$body .= '<td>' . $valor_nuevo["nombres"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Porcentaje</td>';
					$body .= '<td>' . $valor_nuevo["porcentaje"] . '</td>';
					$body .= '</tr>';

					$body .= '</tbody>';
					$body .= '</table>';
				}

				if ($row["nombre_menu_usuario"] == 'Suministro') {
					$query = "SELECT s.id, s.tipo_servicio_id, sp.nombre AS tipo_servicio, s.nro_suministro, s.tipo_compromiso_pago_id, ps.nombre AS tipo_compromiso, s.monto_o_porcentaje
					FROM cont_inmueble_suministros AS s
					LEFT JOIN cont_tipo_pago_servicio AS ps ON ps.id = s.tipo_compromiso_pago_id
					LEFT JOIN cont_tipo_servicio_publico AS sp ON sp.id = s.tipo_servicio_id
					INNER JOIN cont_inmueble AS i ON i.id = s.inmueble_id
					INNER JOIN cont_contrato_detalle AS cd ON cd.id = i.contrato_detalle_id
					WHERE s.id = " . $row["valor_int"];


					$list_query = $mysqli->query($query);
					while ($li = $list_query->fetch_assoc()) {
						$valor_nuevo = $li;
					}

					$body .= '<br>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<thead>';

					$body .= '<tr>';
						$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
						$body .= '<b>Eliminar Suministro '.$codigo_contrato.'</b>';
						$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<th>Campo</th>';
					$body .= '<th>Valor</th>';
					$body .= '</tr>';

					$body .= '</thead>';
					$body .= '<tbody>';

					$body .= '<tr>';
					$body .= '<td>Tipo de Servicio</td>';
					$body .= '<td>' . $valor_nuevo["tipo_servicio"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>N° de Suministro</td>';
					$body .= '<td>' . $valor_nuevo["nro_suministro"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Compromiso de pago<</td>';
					$body .= '<td>' . $valor_nuevo["tipo_compromiso"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Monto/Porcentaje</td>';
					$body .= '<td>' . $valor_nuevo["monto_o_porcentaje"] . '</td>';
					$body .= '</tr>';

					$body .= '</tbody>';
					$body .= '</table>';
				}
			}
		}

	}
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$body .= '<div style="width: 700px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$host.'/?sec_id=contrato&sub_sec_id=detalle_solicitudv2&id='.$contrato_id.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
				$body .= '<b>Ver Solicitud</b>';
		    $body .= '</a>';
		$body .= '</div>';

		$body .= '</html>';
		$body .= "";

	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_adenda_contrato_arrendamiento_firmada($correos_add);

	$cc = $lista_correos['cc'];

	$titulo_email = "Gestion - Sistema Contratos - Adenda Firmada de Contrato de Arrendamiento: ".$nombre_tienda." Código - ";
	if($reenvio){
		$titulo_email = "Gestion - Sistema Contratos - Reenvío de Adenda Firmada de Contrato de Arrendamiento: ".$nombre_tienda." Código - ";
	}

	$bcc = $lista_correos['bcc'];

	$request = [
		"subject" => $titulo_email.$sigla_correlativo.$codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc)
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc)
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();
		//return true;

	}
	catch (Exception $e)
	{
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}


}

function send_email_adenda_acuerdo_confidencialidad_firmada($adenda_id, $reenvio = false)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$sel_query = $mysqli->query("
	SELECT
		a.id,
		a.created_at,
		co.sigla,
		c.codigo_correlativo,
		c.contrato_id,
		tc.nombre,

		concat( IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS usuario_solicitante,
		rs1.nombre AS empresa_contratante,
		ar.nombre AS nombre_area, tp.correo, pab.correo as  correo_abogado
	FROM
		cont_adendas AS a
		INNER JOIN cont_contrato AS c ON c.contrato_id = a.contrato_id
		INNER JOIN tbl_razon_social rs1 ON c.empresa_suscribe_id = rs1.id
		INNER JOIN cont_tipo_contrato AS tc ON tc.id = c.tipo_contrato_id
		INNER JOIN tbl_usuarios AS tu	ON tu.id = a.user_created_id
		INNER JOIN tbl_personal_apt AS tp ON tp.id = tu.personal_id
		INNER JOIN tbl_areas AS ar ON tp.area_id = ar.id
		LEFT JOIN tbl_usuarios AS uab ON uab.id = a.abogado_id
		LEFT JOIN tbl_personal_apt AS pab ON pab.id = uab.personal_id
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
	WHERE a.id = $adenda_id
	");


	$body = "";
	$body .= '<html>';

	$usuario_creacion_correo = '';
	$contrato_id = 0;
	$correos_add = [];
	while($sel = $sel_query->fetch_assoc())
	{
		$contrato_id = $sel['contrato_id'];
		$sigla_correlativo = $sel['sigla'];
		$codigo_correlativo = $sel['codigo_correlativo'];
		$empresa_contratante = $sel['empresa_contratante'];
		$fecha_solicitud = $sel['created_at'];
		$usuario_solicitante = $sel['usuario_solicitante'];
		if(!Empty($sel['correo'])){
			array_push($correos_add, $sel['correo']);
		}
		if(!Empty($sel['correo_abogado'])){
			array_push($correos_add, $sel['correo_abogado']);
		}
		

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

		$body .= '<thead>';

		$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>DATOS GENERALES</b>';
			$body .= '</th>';
		$body .= '</tr>';


		$body .= '</thead>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Area Solicitante:</b></td>';
			$body .= '<td>'.$sel["nombre_area"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Usuario solicitante:</b></td>';
			$body .= '<td>'.$sel["usuario_solicitante"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha Solicitud:</b></td>';
			$body .= '<td>'.$sel["created_at"].'</td>';
		$body .= '</tr>';

		$body .= '</table>';
		$body .= '</div>';


		$query = $mysqli->query("
		SELECT id,
			adenda_id,
			nombre_tabla,
			valor_original,
			nombre_menu_usuario,
			nombre_campo_usuario,
			nombre_campo,
			tipo_valor,
			valor_varchar,
			valor_int,
			valor_date,
			valor_decimal,
			valor_select_option,
			status
		FROM cont_adendas_detalle
		WHERE adenda_id = " . $adenda_id . "
		AND tipo_valor != 'id_tabla' AND tipo_valor != 'registro'
		AND status = 1");
		$row_count = $query->num_rows;
		$numero_adenda_detalle = 0;
		if ($row_count > 0) {
			$body .= '<div>';
			$body .= '<br>';
			$body .= '</div>';

			$body .= '<div>';
			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

			$body .= '<thead>';

			$body .= '<tr>';
				$body .= '<th colspan="5" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
					$body .= '<b>Detalle</b>';
				$body .= '</th>';
			$body .= '</tr>';


			$body .= '</thead>';

			$body .= '<tr>';
				$body .= '<td align="center" style="background-color: #ffffdd; width: 20px;"><b>#</b></td>';
				$body .= '<td align="center" style="background-color: #ffffdd;"><b>Menu:</b></td>';
				$body .= '<td align="center" style="background-color: #ffffdd;"><b>Campo:</b></td>';
				$body .= '<td align="center" style="background-color: #ffffdd;"><b>Valor Original:</b></td>';
				$body .= '<td align="center" style="background-color: #ffffdd;"><b>Nuevo Valor:</b></td>';
			$body .= '</tr>';

			while($row = $query->fetch_assoc()){
				$nombre_menu_usuario = $row["nombre_menu_usuario"];
				$nombre_campo_usuario = $row["nombre_campo_usuario"];
				$valor_original = $row["valor_original"];
				$tipo_valor = $row["tipo_valor"];
				if ($tipo_valor == 'varchar') {
					$nuevo_valor = $row['valor_varchar'];
				} else if ($tipo_valor == 'int') {
					$nuevo_valor = $row['valor_int'];
				} else if ($tipo_valor == 'date') {
					$nuevo_valor = $row['valor_date'];
				} else if ($tipo_valor == 'decimal') {
					$nuevo_valor = $row['valor_decimal'];
				} else if ($tipo_valor == 'select_option') {
					$nuevo_valor = $row['valor_select_option'];
				}
				$numero_adenda_detalle++;

				$body .= '<tr>';
					$body .= '<td>'.$numero_adenda_detalle.'</td>';
					$body .= '<td>'.$nombre_menu_usuario.'</td>';
					$body .= '<td>'.$nombre_campo_usuario.'</td>';
					$body .= '<td>'.$valor_original.'</td>';
					$body .= '<td>'.$nuevo_valor.'</td>';
				$body .= '</tr>';

			}
			$body .= '</table>';
			$body .= '</div>';
		}

		$query = $mysqli->query("
		SELECT id,
			adenda_id,
			nombre_tabla,
			valor_original,
			nombre_menu_usuario,
			nombre_campo_usuario,
			nombre_campo,
			tipo_valor,
			valor_varchar,
			valor_int,
			valor_date,
			valor_decimal,
			valor_select_option,
			status
		FROM cont_adendas_detalle
		WHERE adenda_id = " . $adenda_id . "
		AND tipo_valor = 'registro'
		AND status = 1");
		$row_count = $query->num_rows;
		$numero_adenda_detalle = 0;
		if ($row_count > 0) {
			while($row = $query->fetch_assoc()){
				if ($row["nombre_menu_usuario"] == 'Representante Legal') {
					$query_pro = "
					SELECT
						rl.id,
						rl.dni_representante,
						rl.nombre_representante,
						rl.nro_cuenta_detraccion,
						rl.id_banco,
						b.nombre as banco_representante,
						rl.nro_cuenta,
						rl.nro_cci,
						rl.vigencia_archivo_id,
						rl.dni_archivo_id
					FROM
						cont_representantes_legales rl
						LEFT JOIN tbl_bancos b on b.id = rl.id_banco
					WHERE
						rl.id IN ('" . $row["valor_int"] . "')
					";

					$valores_originales = [];
					$valores_nuevos = [];
					$list_query = $mysqli->query($query_pro);
					while ($li = $list_query->fetch_assoc()) {
						if ($li["id"] == $row["valor_int"]) {
							$valores_nuevos[] = $li;
						}
					}
					$body .= '<div>';
					$body .= '<br>';
					$body .= '</div>';

					$body .= '<div>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

					$body .= '<thead>';

					$body .= '<tr>';
						$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
							$body .= '<b>Nuevo Representante Legal</b>';
						$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
						$body .= '<td align="center" style="background-color: #ffffdd;"><b>Campo:</b></td>';
						$body .= '<td align="center" style="background-color: #ffffdd;"><b>Nuevo Valor:</b></td>';
					$body .= '</tr>';

					$body .= '</thead>';

					$body .= '<tr>';
						$body .= '<td >DNI</td>';
						$body .= '<td >'.$valores_nuevos[0]["dni_representante"].'</td>';
					$body .= '</tr>';

					$body .= '<tr>';
						$body .= '<td >Nombre Completo</td>';
						$body .= '<td >'.$valores_nuevos[0]["nombre_representante"].'</td>';
					$body .= '</tr>';


					$body .= '</table>';
					$body .= '</div>';
				}

				if ($row["nombre_menu_usuario"] == 'Contraprestación') {
					$query_cont = "
					SELECT
						c.id,
						c.moneda_id,
						m.nombre AS tipo_moneda,
						m.simbolo AS tipo_moneda_simbolo,
						c.subtotal,
						c.igv,
						c.monto,
						c.forma_pago_detallado,
						c.tipo_comprobante_id,
						t.nombre AS tipo_comprobante,
						c.plazo_pago
					FROM
						cont_contraprestacion c
						INNER JOIN tbl_moneda m ON c.moneda_id = m.id
						INNER JOIN cont_tipo_comprobante t ON c.tipo_comprobante_id = t.id
					WHERE
						c.id IN ('" . $row["valor_int"] . "')
					";

					$valores_originales = [];
					$valores_nuevos = [];
					$list_query = $mysqli->query($query_cont);
					while ($li = $list_query->fetch_assoc()) {
						if ($li["id"] == $row["valor_int"]) {
							$valores_nuevos[] = $li;
						}
					}
					$body .= '<div>';
					$body .= '<br>';
					$body .= '</div>';

					$body .= '<div>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

					$body .= '<thead>';

					$body .= '<tr>';
						$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
							$body .= '<b>Nueva Contraprestación</b>';
						$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
						$body .= '<td align="center" style="background-color: #ffffdd;"><b>Campo:</b></td>';
						$body .= '<td align="center" style="background-color: #ffffdd;"><b>Nuevo Valor:</b></td>';
					$body .= '</tr>';

					$body .= '</thead>';

					$body .= '<tr>';
					$body .= '<td>Tipo de moneda</td>';
					$body .= '<td>' . $valores_nuevos[0]["tipo_moneda"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Subtotal</td>';
					$body .= '<td>' . $valores_nuevos[0]["subtotal"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>IGV</td>';
					$body .= '<td>' . $valores_nuevos[0]["igv"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Monto Bruto</td>';
					$body .= '<td>' . $valores_nuevos[0]["monto"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Tipo de comprobante a emitir</td>';
					$body .= '<td>' . $valores_nuevos[0]["tipo_comprobante"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Plazo de Pago</td>';
					$body .= '<td>' . $valores_nuevos[0]["plazo_pago"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Forma de pago</td>';
					$body .= '<td>' . $valores_nuevos[0]["forma_pago_detallado"] . '</td>';
					$body .= '</tr>';


					$body .= '</table>';
					$body .= '</div>';
				}
			}
		}



	}
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$body .= '<div style="width: 700px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$host.'/?sec_id=contrato&sub_sec_id=detalle_solicitudv2_acuerdo_confidencialidad&id='.$contrato_id.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
				$body .= '<b>Ver Solicitud</b>';
		    $body .= '</a>';
		$body .= '</div>';

		$body .= '</html>';
		$body .= "";

	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_adenda_acuerdo_confidencialidad_firmada($correos_add);

	$cc = $lista_correos['cc'];

	$titulo_email = "Gestion - Sistema Contratos - Adenda Firmada de Acuerdo de Confidencialidad: Código - ";
	if($reenvio){
		$titulo_email = "Gestion - Sistema Contratos - Reenvio de Adenda Firmada de Acuerdo de Confidencialidad: Código - ";
	}

	$bcc = $lista_correos['bcc'];

	$request = [
		"subject" => $titulo_email.$sigla_correlativo.$codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc)
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc)
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();
		//return true;

	}
	catch (Exception $e)
	{
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}


}
function send_email_adenda_contrato_proveedor_firmada($adenda_id, $reenvio = false)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$sel_query = $mysqli->query("
	SELECT
		a.id,
		a.created_at,
		co.sigla,
		c.codigo_correlativo,
		c.contrato_id,
		tc.nombre,
		concat( IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS usuario_solicitante,
		r.nombre AS empresa_suscribe,
		ar.nombre AS nombre_area,
		c.gerente_area_email as email_gerente_area_2,
		tp.correo as email_creacion_adenda,
		per.correo AS email_creacion_contrato,
		peg.correo AS email_del_gerente_area,
		puap.correo AS email_del_aprobante,
		pab.correo AS email_del_abogado_adenda,
		pera.correo AS email_del_aprobante_adenda
	FROM
		cont_adendas AS a
		INNER JOIN cont_contrato AS c ON c.contrato_id = a.contrato_id
		INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id
		INNER JOIN cont_tipo_contrato AS tc ON tc.id = c.tipo_contrato_id
		INNER JOIN tbl_usuarios AS tu	ON tu.id = a.user_created_id
		INNER JOIN tbl_personal_apt AS tp ON tp.id = tu.personal_id
		INNER JOIN tbl_areas AS ar ON tp.area_id = ar.id
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato

		LEFT JOIN tbl_usuarios uaa ON a.aprobado_por_id = uaa.id
		LEFT JOIN tbl_personal_apt pera ON uaa.personal_id = pera.id

		LEFT JOIN tbl_usuarios u ON c.user_created_id = u.id
		LEFT JOIN tbl_personal_apt per ON u.personal_id = per.id

		LEFT JOIN tbl_usuarios ug ON c.gerente_area_id = ug.id
		LEFT JOIN tbl_personal_apt peg ON ug.personal_id = peg.id
		
		LEFT JOIN tbl_usuarios uap ON c.aprobado_por = uap.id
		LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id

		LEFT JOIN tbl_usuarios uab ON a.abogado_id = uab.id
		LEFT JOIN tbl_personal_apt pab ON uap.personal_id = pab.id
	WHERE
		a.id = $adenda_id
	");


	$body = "";
	$body .= '<html>';

	$usuario_creacion_correo = '';
	$correos_adicionales = [];

	while($sel = $sel_query->fetch_assoc())
	{
		$contrato_id = $sel['contrato_id'];
		$sigla_correlativo = $sel['sigla'];
		$codigo_correlativo = $sel['codigo_correlativo'];
		$empresa_suscribe = $sel['empresa_suscribe'];
		$fecha_solicitud = $sel['created_at'];
		$usuario_solicitante = $sel['usuario_solicitante'];
		if(!Empty($sel['email_creacion_adenda'])){
			array_push($correos_adicionales, $sel['email_creacion_adenda']);
		}
		if(!Empty($sel['email_creacion_contrato'])){
			array_push($correos_adicionales, $sel['email_creacion_contrato']);
		}
		if(!Empty($sel['email_del_gerente_area'])){
			array_push($correos_adicionales, $sel['email_del_gerente_area']);
		}
		if(!Empty($sel['email_gerente_area_2'])){
			array_push($correos_adicionales, $sel['email_gerente_area_2']);
		}
		if(!Empty($sel['email_del_aprobante'])){
			array_push($correos_adicionales, $sel['email_del_aprobante']);
		}
		if(!Empty($sel['email_del_aprobante_adenda'])){
			array_push($correos_adicionales, $sel['email_del_aprobante_adenda']);
		}
		if(!Empty($sel['email_del_abogado_adenda'])){
			array_push($correos_adicionales, $sel['email_del_abogado_adenda']);
		}
		

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

		$body .= '<thead>';

		$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>Datos Generales</b>';
			$body .= '</th>';
		$body .= '</tr>';


		$body .= '</thead>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Area Solicitante:</b></td>';
			$body .= '<td>'.$sel["nombre_area"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Usuario solicitante:</b></td>';
			$body .= '<td>'.$sel["usuario_solicitante"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha Solicitud:</b></td>';
			$body .= '<td>'.$sel["created_at"].'</td>';
		$body .= '</tr>';

		$body .= '</table>';
		$body .= '</div>';


		$query = $mysqli->query("
		SELECT id,
			adenda_id,
			nombre_tabla,
			valor_original,
			nombre_menu_usuario,
			nombre_campo_usuario,
			nombre_campo,
			tipo_valor,
			valor_varchar,
			valor_int,
			valor_date,
			valor_decimal,
			valor_select_option,
			status
		FROM cont_adendas_detalle
		WHERE
			adenda_id = " . $adenda_id . "
			AND tipo_valor != 'id_tabla' AND tipo_valor != 'registro'
			AND status = 1
		");

		$row_count = $query->num_rows;
		$numero_adenda_detalle = 0;

		if ($row_count > 0) {
			$body .= '<div>';
			$body .= '<br>';
			$body .= '</div>';

			$body .= '<div>';
			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

			$body .= '<thead>';

			$body .= '<tr>';
				$body .= '<th colspan="5" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
					$body .= '<b>Detalle</b>';
				$body .= '</th>';
			$body .= '</tr>';


			$body .= '</thead>';

			$body .= '<tr>';
				$body .= '<td align="center" style="background-color: #ffffdd; width: 20px;"><b>#</b></td>';
				$body .= '<td align="center" style="background-color: #ffffdd;"><b>Menu:</b></td>';
				$body .= '<td align="center" style="background-color: #ffffdd;"><b>Campo:</b></td>';
				$body .= '<td align="center" style="background-color: #ffffdd;"><b>Valor Original:</b></td>';
				$body .= '<td align="center" style="background-color: #ffffdd;"><b>Nuevo Valor:</b></td>';
			$body .= '</tr>';

			while($row = $query->fetch_assoc()){
				$nombre_menu_usuario = $row["nombre_menu_usuario"];
				$nombre_campo_usuario = $row["nombre_campo_usuario"];
				$valor_original = $row["valor_original"];
				$tipo_valor = $row["tipo_valor"];
				if ($tipo_valor == 'varchar') {
					$nuevo_valor = $row['valor_varchar'];
				} else if ($tipo_valor == 'int') {
					$nuevo_valor = $row['valor_int'];
				} else if ($tipo_valor == 'date') {
					$nuevo_valor = $row['valor_date'];
				} else if ($tipo_valor == 'decimal') {
					$nuevo_valor = $row['valor_decimal'];
				} else if ($tipo_valor == 'select_option') {
					$nuevo_valor = $row['valor_select_option'];
				}
				$numero_adenda_detalle++;

				$body .= '<tr>';
					$body .= '<td>'.$numero_adenda_detalle.'</td>';
					$body .= '<td>'.$nombre_menu_usuario.'</td>';
					$body .= '<td>'.$nombre_campo_usuario.'</td>';
					$body .= '<td>'.$valor_original.'</td>';
					$body .= '<td>'.$nuevo_valor.'</td>';
				$body .= '</tr>';

			}
			$body .= '</table>';
			$body .= '</div>';
		}


		$query = $mysqli->query("
		SELECT id,
			adenda_id,
			nombre_tabla,
			valor_original,
			nombre_menu_usuario,
			nombre_campo_usuario,
			nombre_campo,
			tipo_valor,
			valor_varchar,
			valor_int,
			valor_date,
			valor_decimal,
			valor_select_option,
			status
		FROM cont_adendas_detalle
		WHERE adenda_id = " . $adenda_id . "
		AND tipo_valor = 'registro'
		AND status = 1");
		$row_count = $query->num_rows;
		$numero_adenda_detalle = 0;
		if ($row_count > 0) {
			while($row = $query->fetch_assoc()){
				if ($row["nombre_menu_usuario"] == 'Representate Legal') {
					$query_pro = "
					SELECT
						rl.id,
						rl.dni_representante,
						rl.nombre_representante,
						rl.nro_cuenta_detraccion,
						rl.id_banco,
						b.nombre as banco_representante,
						rl.nro_cuenta,
						rl.nro_cci,
						rl.vigencia_archivo_id,
						rl.dni_archivo_id
					FROM
						cont_representantes_legales rl
						LEFT JOIN tbl_bancos b on b.id = rl.id_banco
					WHERE
						rl.id IN ('" . $row["valor_int"] . "')
					";

					$valores_originales = [];
					$valores_nuevos = [];
					$list_query = $mysqli->query($query_pro);
					while ($li = $list_query->fetch_assoc()) {
						if ($li["id"] == $row["valor_int"]) {
							$valores_nuevos[] = $li;
						}
					}
					$body .= '<div>';
					$body .= '<br>';
					$body .= '</div>';

					$body .= '<div>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

					$body .= '<thead>';

					$body .= '<tr>';
						$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
							$body .= '<b>Nuevo Representante Legal</b>';
						$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
						$body .= '<td align="center" style="background-color: #ffffdd;"><b>Campo:</b></td>';
						$body .= '<td align="center" style="background-color: #ffffdd;"><b>Nuevo Valor:</b></td>';
					$body .= '</tr>';

					$body .= '</thead>';

					$body .= '<tr>';
						$body .= '<td >DNI del representante legal</td>';
						$body .= '<td >'.$valores_nuevos[0]["dni_representante"].'</td>';
					$body .= '</tr>';

					$body .= '<tr>';
						$body .= '<td >Nombre completo del representante legal</td>';
						$body .= '<td >'.$valores_nuevos[0]["nombre_representante"].'</td>';
					$body .= '</tr>';

					$body .= '<tr>';
						$body .= '<td >Nro cuenta de detraccion (Banco de la nación)</td>';
						$body .= '<td >'.$valores_nuevos[0]["nro_cuenta_detraccion"].'</td>';
					$body .= '</tr>';

					$body .= '<tr>';
						$body .= '<td >Banco</td>';
						$body .= '<td >'.$valores_nuevos[0]["banco_representante"].'</td>';
					$body .= '</tr>';

					$body .= '<tr>';
						$body .= '<td >Nro Cuenta</td>';
						$body .= '<td >'.$valores_nuevos[0]["nro_cuenta"].'</td>';
					$body .= '</tr>';

					$body .= '<tr>';
						$body .= '<td >Nro CCI</td>';
						$body .= '<td >'.$valores_nuevos[0]["nro_cci"].'</td>';
					$body .= '</tr>';



					$body .= '</table>';
					$body .= '</div>';
				}

				if ($row["nombre_menu_usuario"] == 'Contraprestación') {
					$query_cont = "
				SELECT
					c.id,
					c.moneda_id,
					m.nombre AS tipo_moneda,
					m.simbolo AS tipo_moneda_simbolo,
					c.subtotal,
					c.igv,
					c.monto,
					c.forma_pago_detallado,
					c.tipo_comprobante_id,
					t.nombre AS tipo_comprobante,
					c.plazo_pago
				FROM
					cont_contraprestacion c
					INNER JOIN tbl_moneda m ON c.moneda_id = m.id
					INNER JOIN cont_tipo_comprobante t ON c.tipo_comprobante_id = t.id
				WHERE
					c.id IN ('" . $row["valor_int"] . "')
				";

					$valores_originales = [];
					$valores_nuevos = [];
					$list_query = $mysqli->query($query_cont);
					while ($li = $list_query->fetch_assoc()) {
						if ($li["id"] == $row["valor_int"]) {
							$valores_nuevos[] = $li;
						}
					}
					$body .= '<div>';
					$body .= '<br>';
					$body .= '</div>';

					$body .= '<div>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

					$body .= '<thead>';

					$body .= '<tr>';
						$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
							$body .= '<b>Nueva Contraprestación</b>';
						$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
						$body .= '<td align="center" style="background-color: #ffffdd;"><b>Campo:</b></td>';
						$body .= '<td align="center" style="background-color: #ffffdd;"><b>Nuevo Valor:</b></td>';
					$body .= '</tr>';

					$body .= '</thead>';

					$body .= '<tr>';
					$body .= '<td>Tipo de moneda</td>';
					$body .= '<td>' . $valores_nuevos[0]["tipo_moneda"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Subtotal</td>';
					$body .= '<td>' . $valores_nuevos[0]["subtotal"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>IGV</td>';
					$body .= '<td>' . $valores_nuevos[0]["igv"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Monto Bruto</td>';
					$body .= '<td>' . $valores_nuevos[0]["monto"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Tipo de comprobante a emitir</td>';
					$body .= '<td>' . $valores_nuevos[0]["tipo_comprobante"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Plazo de Pago</td>';
					$body .= '<td>' . $valores_nuevos[0]["plazo_pago"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Forma de pago</td>';
					$body .= '<td>' . $valores_nuevos[0]["forma_pago_detallado"] . '</td>';
					$body .= '</tr>';


					$body .= '</table>';
					$body .= '</div>';
				}




			}
		}





	}

	$body .= '<div>';
		$body .= '<br>';
	$body .= '</div>';

	$body .= '<div style="width: 700px; text-align: center; font-family: arial;">';
	    $body .= '<a href="'.$host.'/?sec_id=contrato&sub_sec_id=detalleSolicitudProveedor&id='.$contrato_id.'" target="_blank">';
	    	$body .= '<button style="background-color: green; color: white; cursor: pointer; width: 50%;">';
				$body .= '<b>Ver Contrato y Adenda de Proveedor</b>';
	    	$body .= '</button>';
	    $body .= '</a>';
	$body .= '</div>';

	$body .= '</html>';
	$body .= "";

	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_adenda_contrato_proveedor_firmada($correos_adicionales);

	$cc = $lista_correos['cc'];

	$titulo_email = "Gestion - Sistema Contratos - Adenda Firmada de Contrato de Proveedor: Código - ";
	if ($reenvio){
		$titulo_email = "Gestion - Sistema Contratos - Reenvío de Adenda Firmada de Contrato de Proveedor: Código - ";
	}

	$bcc = $lista_correos['bcc'];

	$request = [
		"subject" => $titulo_email.$sigla_correlativo.$codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc)
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc)
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();
		//return true;

	}
	catch (Exception $e)
	{
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}

}

function send_email_formato_de_pago($contrato_id)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];


	$body = '';

	$query = "
	SELECT
		r.nombre AS empresa_suscribe,
		c.nombre_tienda,
		co.sigla AS sigla_correlativo,
		c.codigo_correlativo
	FROM
		cont_contrato c
		INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
	WHERE
		c.contrato_id = $contrato_id
	";

	$sel_query = $mysqli->query($query);

	while($sel = $sel_query->fetch_assoc()){
		$empresa_suscribe = $sel["empresa_suscribe"];
		$nombre_tienda = trim($sel["nombre_tienda"]);
		$sigla_correlativo = $sel['sigla_correlativo'];
		$codigo_correlativo = $sel['codigo_correlativo'];
	}


	$query = "
	SELECT
		p.num_ruc
	FROM
		cont_propietario pr
		INNER JOIN cont_persona p ON pr.persona_id = p.id
	WHERE
		pr.contrato_id = $contrato_id
	";

	$sel_query = $mysqli->query($query);

	while($sel = $sel_query->fetch_assoc()){
		$num_ruc_propietario = $sel["num_ruc"];
	}


	$body .= '<div>';
	$body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
	$body .= '<tr>';
	$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">FORMATO DE PAGO NUEVAS TIENDAS</th>';
	$body .= '</tr>';
	$body .= '<tr>';
	$body .= '<td style="background-color:#ffffdd;"><b>Nombre de la tienda</b></td>';
	$body .= '<td>' . $nombre_tienda . '</td>';
	$body .= '</tr>';
	$body .= '<tr>';
	$body .= '<td style="background-color:#ffffdd;"><b>Arrendataria</b></td>';
	$body .= '<td>' . $empresa_suscribe . '</td>';
	$body .= '</tr>';

	$query_detalle = "SELECT d.* FROM cont_contrato_detalle AS d WHERE d.contrato_id = $contrato_id";
	$result_query_detalle = $mysqli->query($query_detalle);
	while($sel_det = $result_query_detalle->fetch_assoc()){
		$query = "
		SELECT
			c.monto_renta,
			concat(m.nombre,' (', m.simbolo,')') AS moneda_contrato,
			m.simbolo AS simbolo_moneda,
			c.garantia_monto,
			c.tipo_adelanto_id,
			a.nombre AS tipo_adelanto
		FROM
			cont_condicion_economica c
			INNER JOIN tbl_moneda m ON c.tipo_moneda_id = m.id
			INNER JOIN cont_tipo_adelanto a ON c.tipo_adelanto_id = a.id
		WHERE
			c.contrato_id = $contrato_id
			AND c.contrato_detalle_id = ".$sel_det['id'];

		$sel_query = $mysqli->query($query);

		while($row=$sel_query->fetch_assoc()){
			$simbolo_moneda = $row["simbolo_moneda"];
			$moneda_contrato = $row["moneda_contrato"];
			$monto_renta = $simbolo_moneda . ' ' . number_format($row["monto_renta"], 2, '.', ',');
			$monto_renta_sin_formato = $row["monto_renta"];
			$garantia_monto = $simbolo_moneda . ' ' . number_format($row["garantia_monto"], 2, '.', ',');
			$garantia_monto_sin_formato = $row["garantia_monto"];
			$tipo_adelanto_id = $row["tipo_adelanto_id"];
			$tipo_adelanto = $row["tipo_adelanto"];

			$num_de_meses_de_adelanto = 0;
			if($tipo_adelanto_id == '1'){
				$meses_de_adelanto = '';

				$sql = "
				SELECT
					a.id,
					t.nombre AS mes_adelanto
				FROM
					cont_adelantos a
					INNER JOIN cont_tipo_mes_adelanto t ON a.num_periodo = t.id
				WHERE
					a.contrato_id = $contrato_id
					AND a.contrato_detalle_id = ".$sel_det['id']."
					AND a.status = 1
				ORDER BY t.id ASC
				";

				$query = $mysqli->query($sql);
				$num_de_meses_de_adelanto = $query->num_rows;

				if ($num_de_meses_de_adelanto > 0) {
					$contador = 1;
					while ($row = $query->fetch_assoc()) {
						$mes_adelanto = $row["mes_adelanto"];

						if ($contador == 1) {
							$meses_de_adelanto = $mes_adelanto;
						} else if($contador == $num_de_meses_de_adelanto) {
							$meses_de_adelanto .= ' y el ' . $mes_adelanto;
						} else {
							$meses_de_adelanto .= ', ' . $mes_adelanto;
						}

						$contador++;
					}

					if ($num_de_meses_de_adelanto == 1) {
						$cant_meses_adelando = 'mes';
						$adelantos_texto = 'Adelanto';
					} else {
						$cant_meses_adelando = 'meses';
						$adelantos_texto = 'Adelantos';
					}

					$meses_de_adelanto = $num_de_meses_de_adelanto . ' ' . $cant_meses_adelando . ' de adelanto (' . $adelantos_texto . ' del ' . $meses_de_adelanto . ')';
				}


			}
		}


		$query = "
		SELECT
			tp.nombre AS tipo_persona,
			td.nombre AS tipo_docu_identidad,
			b.num_docu,
			b.nombre,
			ba.nombre AS banco,
			b.num_cuenta_bancaria,
			b.num_cuenta_cci
		FROM
			cont_beneficiarios b
				LEFT JOIN
			cont_tipo_persona tp ON b.tipo_persona_id = tp.id
				LEFT JOIN
			cont_tipo_docu_identidad td ON b.tipo_docu_identidad_id = td.id
				LEFT JOIN
			tbl_bancos ba ON b.banco_id = ba.id
		WHERE
			b.status = 1 AND contrato_id = $contrato_id
			AND b.contrato_detalle_id = ".$sel_det['id']."
		";

		$sel_query = $mysqli->query($query);

		while($sel=$sel_query->fetch_assoc()){
			$tipo_persona_beneficiario = $sel["tipo_persona"];
			$nombre_beneficiario = $sel["nombre"];
			$num_docu_beneficiario = $sel["num_docu"];
			$banco_beneficiario = $sel["banco"];
			$num_cuenta_bancaria_beneficiario = $sel["num_cuenta_bancaria"];
			$num_cuenta_cci_beneficiario = $sel["num_cuenta_cci"];
		}


		if($tipo_adelanto_id == '1') {
			if ($num_de_meses_de_adelanto == 0)
			{
				$adelantos_final = 'Cero meses de adelanto.';
			}
			else
			{
				$adelantos_final = $meses_de_adelanto;
			}
		} else {
			$adelantos_final = $tipo_adelanto;
		}

		$importe_a_girar = $garantia_monto_sin_formato + ( $monto_renta_sin_formato * $num_de_meses_de_adelanto );


		
		$body .= '<tr>';
		$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">CONTRATO #'.$sel_det['codigo'].'</th>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Tipo de Persona</b></td>';
		$body .= '<td>' . $tipo_persona_beneficiario . '</td>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;"><b>Adelanto</b></td>';
		$body .= '<td>' . $adelantos_final . '</td>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;"><b>Garantía</b></td>';
		$body .= '<td>' . $garantia_monto . '</td>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;"><b>Número de RUC del Arrendador</b></td>';
		$body .= '<td>' . $num_ruc_propietario . '</td>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;"><b>Número de DNI y/o RUC del Beneficiario</b></td>';
		$body .= '<td>' . $num_docu_beneficiario . '</td>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;"><b>Nombre y Apellidos del Beneficiario</b></td>';
		$body .= '<td>' . $nombre_beneficiario . '</td>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;"><b>Nombre del Banco</b></td>';
		$body .= '<td>' . $banco_beneficiario . '</td>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;"><b>Número de la cuenta bancaria</b></td>';
		$body .= '<td>' . $num_cuenta_bancaria_beneficiario . '</td>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;"><b>Número de CCI bancario</b></td>';
		$body .= '<td>' . $num_cuenta_cci_beneficiario . '</td>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;"><b>Moneda</b></td>';
		$body .= '<td>' . $moneda_contrato . '</td>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;"><b>Importe a girar y/o depositar</b></td>';
		$body .= '<td>' . $simbolo_moneda . ' ' . number_format($importe_a_girar, 2, '.', ',') . '</td>';
		$body .= '</tr>';
	
	}

	$body .= '</table>';
	$body .= '</div>';

	$body .= '<div>';
	$body .= '<br>';

	$body .= '<span>*PREVIA PROVISION CONTABLE</span>';

	$body .= '<br>';
	$body .= '</div>';

	$body .= '<div style="width: 700px; text-align: center; font-family: arial;">';
	$body .= '<a href="' . $host . '/?sec_id=contrato&sub_sec_id=detalle_solicitudv2&id=' . $contrato_id . '" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
	$body .= '<b>Ver Solicitud</b>';
	$body .= '</a>';
	$body .= '</div>';

	$body .= '<div>';
	$body .= '<br>';
	$body .= '</div>';

	$pre_asunto = '';

	$reenvio = false;

	$sql = "
	SELECT
		id
	FROM
		cont_emails_enviados
	WHERE
		contrato_id = $contrato_id
		AND status = 1
	";

	$query = $mysqli->query($sql);
	$num_de_email_enviados = $query->num_rows;

	if ($num_de_email_enviados > 0) {
		$reenvio = true;
	}

	if ($reenvio) {
		$pre_asunto = 'Reenvío N° ' . $num_de_email_enviados . ' - ';
	}

	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_formato_de_pago([]);

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];

	$request = [
		"subject" => $pre_asunto . "Gestion - Sistema Contratos - Formato de Pago - " . $nombre_tienda . ": Código - " .$sigla_correlativo.$codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc)
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc)
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();

		$usuario_id = $login?$login['id']:null;
		$created_at = date('Y-m-d H:i:s');
	
		$query_insert = "INSERT INTO cont_emails_enviados(
		contrato_id,
		tipo_email_enviado_id,
		status,
		user_created_id,
		created_at)
		VALUES(
		" . $contrato_id . ",
		2,
		1,
		" . $usuario_id . ",
		'" . $created_at . "' )";

		$mysqli->query($query_insert);

		if($mysqli->error){
			$result["http_code"] = 400;
			$result["status"] = "Datos obtenidos de gestion.";
			$result["error"] = $mysqli->error;
			echo json_encode($result);
			exit();
		}

		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		echo json_encode($result);
		exit();

	}
	catch (Exception $e)
	{
		$result["http_code"] = 400;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["error"] = $mail->ErrorInfo;
		echo json_encode($result);
		exit();
	}

}


if (isset($_POST["accion"]) && $_POST["accion"]==="consultar_asignacion_del_nombre_de_tienda") {
	$contrato_id = $_POST["contrato_id"];
	$html = '';

	$sel_query = $mysqli->query("
	SELECT
		c.nombre_tienda
	FROM
		cont_contrato c
	WHERE
		c.contrato_id = $contrato_id
	");

	$row_count = $sel_query->num_rows;

	if ($row_count == 0) {
		$result["http_code"] = 400;
	} else if ($row_count > 0) {
		while($sel=$sel_query->fetch_assoc()){
			if ($sel["nombre_tienda"] == "") {
				$result["http_code"] = 400;
				$result["error"] = "sin_asignar";
			} else {
				$result["http_code"] = 200;
			}
		}
	}

	$result["status"] = "Datos obtenidos de gestion.";
}


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_emails_enviados_formato_de_pago") {
	$contrato_id = $_POST["contrato_id"];
	$contador_de_envios = 1;
	$html = '';

	$sel_query = $mysqli->query("
	SELECT
		e.tipo_email_enviado_id,
		CONCAT(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, ''),' ', IFNULL(p.apellido_materno, '')) AS user_created,
		e.created_at
	FROM
		cont_emails_enviados e
		INNER JOIN tbl_usuarios u ON e.user_created_id = u.id
		INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
	WHERE
		e.contrato_id = $contrato_id
		AND e.status = 1
	ORDER BY e.id ASC
	");

	$row_count = $sel_query->num_rows;

	if ($row_count == 0) {
		$html .= '<span>Aún no se envía el Formato de Pago por email.</span>';
	} else if ($row_count > 0) {

		$html .= '<table class="table table-responsive table-hover no-mb" style="font-size: 11px;">';
		$html .= '<thead>';
		$html .= '<th>N°</th>';
		$html .= '<th>Usuario que realizó el envío del email</th>';
		$html .= '<th>Fecha de envío</th>';
		$html .= '</thead>';
		$html .= '<tbody>';

		while($sel=$sel_query->fetch_assoc()){
			$html .= '<tr style="text-transform: none;">';
			$html .= '<td>' . $contador_de_envios . '</td>';
			$html .= '<td>' . $sel["user_created"] . '</td>';
			$html .= '<td>' . $sel["created_at"] . '</td>';
			$html .= '</tr>';

			$contador_de_envios++;
		}

		$html .= '</tbody>';
		$html .= '</table>';

	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $html;

}


if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_nuevo_adelanto")
{

	$user_id = $login?$login['id']:null;

	if ( !((int) $user_id > 0) ) {
		$result["http_code"] = 500;
		$result["status"] = "error";
		$result["mensaje"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y hacer click e el boton verde: Agregar adenda firmada.";
		echo json_encode($result);
		die;
	}

	$created_at = "'" . date('Y-m-d H:i:s') . "'";
	$contrato_id = $_POST["contrato_id"];
	$contrato_detalle_id = isset($_POST["contrato_detalle_id"]) ? $_POST["contrato_detalle_id"]:0;
	$num_periodo = "'" . trim($_POST["num_periodo_id"]) . "'";
	$status = 1;
	$error = '';

	$sql_validar_existencia = "
	SELECT
		id
	FROM
		cont_adelantos
	WHERE
		contrato_id = $contrato_id
		AND contrato_detalle_id = $contrato_detalle_id
		AND num_periodo = $num_periodo
		AND status = $status
	";

	$sel_query = $mysqli->query($sql_validar_existencia);
	$row_count = $sel_query->num_rows;

	if ($row_count == 0) {
		$query_insert = "
		INSERT INTO cont_adelantos(
			contrato_id,
			contrato_detalle_id,
			num_periodo,
			status,
			user_created_id,
			created_at
		) VALUES (
			$contrato_id,
			$contrato_detalle_id,
			$num_periodo,
			$status,
			$user_id,
			$created_at
		)";

		$mysqli->query($query_insert);

		if($mysqli->error){
			$error = $mysqli->error;
		}

		$valor_nuevo = trim($_POST["num_periodo"]);

		$msg_auditoria = guardar_cambios_personalizados_en_auditoria($contrato_id, 'cont_adelantos', 'Se agregó un nuevo adelanto al contrato.', 'num_periodo', 'Condiciones económicas y comerciales', 'Adelantos', $valor_nuevo, $contrato_detalle_id);

		if($msg_auditoria != 'ok'){
			$error = $msg_auditoria;
		}
	} else {
		$error = 'Ya existe un adelanto del ' . $_POST["num_periodo"];
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["error"] = $error;

}

if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_nuevo_suministro")
{

	$user_id = $login?$login['id']:null;

	if ( !((int) $user_id > 0) ) {
		$result["http_code"] = 500;
		$result["status"] = "error";
		$result["mensaje"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y hacer click e el boton verde: Agregar adenda firmada.";
		echo json_encode($result);
		die;
	}

	$created_at = "'" . date('Y-m-d H:i:s') . "'";
	$contrato_id = $_POST["contrato_id"];
	$contrato_detalle_id = isset($_POST["contrato_detalle_id"]) ? $_POST["contrato_detalle_id"]:0;
	$inmueble_id = isset($_POST["inmueble_id"]) ? $_POST["inmueble_id"]:0;
	$tipo_servicio_id = $_POST["tipo_servicio_id"];
	$nro_suministro = trim($_POST["nro_suministro"]);
	$compromiso_pago_id = $_POST["compromiso_pago_id"];
	$monto_o_porcentaje = Empty($_POST["monto_o_porcentaje"]) ? 0 :$_POST["monto_o_porcentaje"];
	$status = 1;
	$error = '';


	$query_insert = "
	INSERT INTO cont_inmueble_suministros (
		contrato_id,
		inmueble_id,
		tipo_servicio_id,
		nro_suministro,
		tipo_compromiso_pago_id,
		monto_o_porcentaje,
		status,
		user_created_id,
		created_at
	) VALUES (
		".$contrato_id.",
		".$inmueble_id.",
		".$tipo_servicio_id.", 
		'".$nro_suministro."', 
		".$compromiso_pago_id.", 
		".$monto_o_porcentaje.", 
		$status, 
		$user_id,
		".$created_at."
	)";

	$mysqli->query($query_insert);

	if($mysqli->error){
		$error = $mysqli->error;
	}

	$valor_nuevo = trim($_POST["nro_suministro"]);

	$msg_auditoria = guardar_cambios_personalizados_en_auditoria($contrato_id, 'cont_inmueble_suministros', 'Se agregó un nuevo suministro al contrato.', 'nro_suministro', 'Inmuebles', 'Suministros', $valor_nuevo, $contrato_detalle_id);

	if($msg_auditoria != 'ok'){
		$error = $msg_auditoria;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["error"] = $error;

}


if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_incremento") {
	$usuario_id = $login?$login['id']:null;

	if ( !((int) $usuario_id > 0) ) {
		$result["http_code"] = 500;
		$result["status"] = "error";
		$result["mensaje"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y hacer click e el boton verde: Agregar el incremento.";
		echo json_encode($result);
		die;
	}

	$created_at = "'" . date("Y-m-d H:i:s") . "'";
	$contrato_id = $_POST["contrato_id"];
	$contrato_detalle_id = $_POST["contrato_detalle_id"];
	$incremento_monto_o_porcentaje = str_replace(",","",$_POST["incremento_monto_o_porcentaje"]);
	$incrementos_en = $_POST["incrementos_en"];
	$incrementos_continuidad = $_POST["incrementos_continuidad"];
	$error = '';

	if (empty($_POST["incrementos_a_partir_de_año"])) {
		$incrementos_a_partir_de_año = "2";
	} else {
		$incrementos_a_partir_de_año = $_POST["incrementos_a_partir_de_año"];
	}

	$query_insert = "
	INSERT INTO cont_incrementos(
		contrato_id,
		contrato_detalle_id,
		valor,
		tipo_valor_id,
		tipo_continuidad_id,
		a_partir_del_año,
		user_created_id,
		created_at
	) VALUES (
		$contrato_id,
		'".$contrato_detalle_id."',
		$incremento_monto_o_porcentaje,
		$incrementos_en,
		$incrementos_continuidad,
		$incrementos_a_partir_de_año,
		$usuario_id,
		$created_at
	)";

	$mysqli->query($query_insert);

	if($mysqli->error){
		$error=$mysqli->error . $query_insert;
	}

	if ($incrementos_en == 1) {
		$tipo_valor = ' soles o dolares (según el tipo de moneda del contrato)';
	} else if ($incrementos_en == 2) {
		$tipo_valor = '%';
		if (substr($incremento_monto_o_porcentaje,-3,3) == ".00") {
			$incremento_monto_o_porcentaje = substr($incremento_monto_o_porcentaje,0,-3);
		}
	}

	$a_partir_del_año = $_POST["incrementos_a_partir_de_año_text"];

	if ($incrementos_continuidad == 3) {
		$a_partir_del_año = '';
	}

	$valor_nuevo = $incremento_monto_o_porcentaje . $tipo_valor . ' ' . $_POST["incrementos_continuidad_text"] . ' ' . $a_partir_del_año;

	$msg_auditoria = guardar_cambios_personalizados_en_auditoria($contrato_id, 'cont_incrementos', 'Se agregó un nuevo incremento al contrato.', '', 'Incrementos', 'Incrementos', $valor_nuevo,$contrato_detalle_id);

	if($msg_auditoria != 'ok'){
		$error = $msg_auditoria;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["error"] = $error;
}


if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_incremento_adenda") {
	$usuario_id = $login?$login['id']:null;
	$created_at = "'" . date("Y-m-d H:i:s") . "'";
	// $contrato_id = $_POST["contrato_id"];
	$incremento_monto_o_porcentaje = str_replace(",","",$_POST["incremento_monto_o_porcentaje"]);
	$incrementos_en = $_POST["incrementos_en"];
	$incrementos_continuidad = $_POST["incrementos_continuidad"];
	$error = '';

	if (empty($_POST["incrementos_a_partir_de_año"])) {
		$incrementos_a_partir_de_año = "2";
	} else {
		$incrementos_a_partir_de_año = $_POST["incrementos_a_partir_de_año"];
	}

	$query_insert = "
	INSERT INTO cont_incrementos(
		valor,
		tipo_valor_id,
		tipo_continuidad_id,
		a_partir_del_año,
		user_created_id,
		created_at
	) VALUES (
		$incremento_monto_o_porcentaje,
		$incrementos_en,
		$incrementos_continuidad,
		$incrementos_a_partir_de_año,
		$usuario_id,
		$created_at
	)";

	$mysqli->query($query_insert);
	$id_incrementos = mysqli_insert_id($mysqli);

	if($mysqli->error){
		$error=$mysqli->error . $query_insert;
	}

	if ($incrementos_en == 1) {
		$tipo_valor = ' soles o dolares (según el tipo de moneda del contrato)';
	} else if ($incrementos_en == 2) {
		$tipo_valor = '%';
		if (substr($incremento_monto_o_porcentaje,-3,3) == ".00") {
			$incremento_monto_o_porcentaje = substr($incremento_monto_o_porcentaje,0,-3);
		}
	}

	$a_partir_del_año = $_POST["incrementos_a_partir_de_año_text"];

	if ($incrementos_continuidad == 3) {
		$a_partir_del_año = '';
	}

	$valor_nuevo = $incremento_monto_o_porcentaje . $tipo_valor . ' ' . $_POST["incrementos_continuidad_text"] . ' ' . $a_partir_del_año;

	$result["http_code"] = 200;
	$result["id"] = $id_incrementos;
	$result["nuevo_valor"] = $valor_nuevo;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["error"] = $error;
}


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_incrementos") {

	$incremento_id = $_POST["incremento_id"];

	$sql = "
	SELECT
		i.id,
		i.valor,
		i.tipo_valor_id,
		tp.nombre AS tipo_valor,
		i.tipo_continuidad_id,
		tc.nombre AS tipo_continuidad,
		i.a_partir_del_año
	FROM
		cont_incrementos i
		INNER JOIN cont_tipo_pago_incrementos tp ON i.tipo_valor_id = tp.id
		INNER JOIN cont_tipo_continuidad_pago tc ON i.tipo_continuidad_id = tc.id
	WHERE
		i.id = $incremento_id
		AND i.estado = 1
	";

	$list_query = $mysqli->query($sql);
	$list = array();

	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}

	if(count($list) == 0){
		$result["http_code"] = 400;
		$result["result"] = "El incremento no existe.";
	} elseif (count($list) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $list;
	} else {
		$result["http_code"] = 400;
		$result["result"] = "El incremento no existe.";
	}

}

if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_cambios_incremento") {
	$incremento_monto_o_porcentaje = str_replace(",","",$_POST["incremento_monto_o_porcentaje"]);
	$incrementos_en = $_POST["incrementos_en"];
	$incrementos_continuidad = $_POST["incrementos_continuidad"];
	$incrementos_a_partir_de_año = $_POST["incrementos_a_partir_de_año"];
	$id_incremento_para_cambios = $_POST["id_incremento_para_cambios"];
	$usuario_id = $login?$login['id']:null;
	$created_at = "'" . date("Y-m-d H:i:s") . "'";

	if ($incrementos_continuidad == "3") {
		$incrementos_a_partir_de_año = "0";
	}

	$query_update = "
	UPDATE
		cont_incrementos
	SET
		valor = $incremento_monto_o_porcentaje,
		tipo_valor_id = $incrementos_en,
		tipo_continuidad_id = $incrementos_continuidad,
		a_partir_del_año = $incrementos_a_partir_de_año,
		user_updated_id = $usuario_id,
		updated_at = $created_at
	WHERE
		id = $id_incremento_para_cambios
	";
	$mysqli->query($query_update);

	$error = '';
	if($mysqli->error){
		$error=$mysqli->error;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = 'ok';
	$result["error"] = $error;
}


if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_beneficiario") {
	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");

	$nombre = str_replace("'", "",trim($_POST["nombre"]));
	$contrato_id = $_POST["contrato_id"];
	$contrato_detalle_id = $_POST["contrato_detalle_id"];

	$id_forma_pago = $_POST["id_forma_pago"];

	if ( $id_forma_pago == '3') {
		$id_banco = "NULL";
		$num_cuenta_bancaria = "NULL";
		$num_cuenta_cci = "NULL";
	} else {
		$id_banco = $_POST["id_banco"];
		$num_cuenta_bancaria = "'" . $_POST["num_cuenta_bancaria"] . "'";
		$num_cuenta_cci = "'" . $_POST["num_cuenta_cci"] . "'";
	}

	if (empty($_POST["monto"])) {
		$monto = "NULL";
	} else {
		$monto = str_replace(",","",$_POST["monto"]);
	}

	$query_insert = "INSERT INTO cont_beneficiarios
	(
	contrato_id,
	contrato_detalle_id,
	tipo_persona_id,
	tipo_docu_identidad_id,
	num_docu,
	nombre,
	forma_pago_id,
	banco_id,
	num_cuenta_bancaria,
	num_cuenta_cci,
	tipo_monto_id,
	monto,
	user_created_id,
	created_at)
	VALUES
	(
	" . $_POST["contrato_id"] . ",
	'" . $_POST["contrato_detalle_id"] . "',
	" . $_POST["tipo_persona"] . ",
	" . $_POST["tipo_docu"] . ",
	'" . $_POST["num_docu"] . "',
	'" . $nombre . "',
	" . $id_forma_pago . ",
	" . $id_banco . ",
	" . $num_cuenta_bancaria . ",
	" . $num_cuenta_cci . ",
	" . $_POST["tipo_monto"] . ",
	" . $monto . ",
	" . $usuario_id . ",
	'" . $created_at . "')";

	$mysqli->query($query_insert);
	$insert_id = mysqli_insert_id($mysqli);
	$error = '';

	if($mysqli->error){
		$error .= $mysqli->error;
	}

	$msg_auditoria = guardar_cambios_personalizados_en_auditoria($contrato_id, 'cont_beneficiarios', 'Se agregó un nuevo beneficiario al contrato.', '', 'Beneficiarios', 'Beneficiarios', $nombre,$contrato_detalle_id);

	if($msg_auditoria != 'ok'){
		$error .= $msg_auditoria;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $insert_id;
	$result["error"] = $error;
}

if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_responsable_ir") {
	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");

	
	$contrato_id = $_POST["contrato_id"];
	$contrato_detalle_id = $_POST["contrato_detalle_id"];
	$tipo_docu = $_POST["tipo_docu"];
	$num_docu = $_POST["num_docu"];
	$nombres = str_replace("'", "",trim($_POST["nombres"]));
	$porcentaje = $_POST["porcentaje"];

	$query_insert = "INSERT INTO cont_responsable_ir
	(
		contrato_id,
		contrato_detalle_id,
		tipo_documento_id,
		num_documento,
		nombres,
		estado_emisor,
		porcentaje,
		status,
		user_created_id,
		created_at
	)
	VALUES
	(
	" . $_POST["contrato_id"] . ",
	'" . $_POST["contrato_detalle_id"] . "',
	'" . $tipo_docu . "',
	'" . $num_docu . "',
	'" . $nombres . "',
	'1',
	" . $porcentaje . ",
	1,
	" . $usuario_id . ",
	'" . $created_at . "')";

	$mysqli->query($query_insert);
	$insert_id = mysqli_insert_id($mysqli);
	$error = '';

	if($mysqli->error){
		$error .= $mysqli->error;

	}

	$msg_auditoria = guardar_cambios_personalizados_en_auditoria($contrato_id, 'cont_responsable_ir', 'Se agregó un nuevo responsable IR al contrato.', '', 'Responsable IR', 'Responsable IR', $nombres, $contrato_detalle_id);

	if($msg_auditoria != 'ok'){
		$error .= $msg_auditoria;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $insert_id;
	$result["error"] = $error;
	$result["query_insert"] = $query_insert;
	
}


if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_propietario_agente") {
	$usuario_id = $login?$login['id']:null;
	$created_at = "'" . date("Y-m-d H:i:s") . "'";

	if ($_POST["tipo_persona_contacto"] == 1) {
		$contacto_nombre = $_POST["nombre"];
	} else {
		$contacto_nombre = $_POST["contacto_nombre"];
	}

	$contrato_id = $_POST["contrato_id"];
	$tipo_persona = $_POST["tipo_persona"];
	$tipo_docu = $_POST["tipo_docu"];
	$num_docu = "'" . $_POST["num_docu"] . "'";
	$num_ruc = "'" . $_POST["num_ruc"] . "'";
	$nombre = "'" . str_replace("'", "",trim($_POST["nombre"])). "'";
	$direccion = "'" . str_replace("'", "",trim($_POST["direccion"])). "'";
	$representante_legal = "'" . str_replace("'", "",trim($_POST["representante_legal"])). "'";
	$num_partida_registral = "'" . $_POST["num_partida_registral"] . "'";
	$contacto_nombre = "'" . str_replace("'", "",trim($contacto_nombre)). "'";
	$contacto_telefono = "'" . $_POST["contacto_telefono"] . "'";
	$contacto_email = "'" . $_POST["contacto_email"] . "'";


	if ($tipo_docu != 2){
		$sql_existe = "SELECT
		id,
		tipo_persona_id,
		nombre,
		tipo_docu_identidad_id,
		num_docu,
		num_ruc,
		direccion,
		representante_legal,
		num_partida_registral,
		contacto_nombre,
		contacto_telefono,
		contacto_email
		FROM cont_persona WHERE num_docu like '" . $_POST["num_docu"]."' AND status = 1 ";

		$query_existe = $mysqli->query($sql_existe);
		$row_existe = $query_existe->num_rows;

		if(($row_existe) > 0){
			$result["http_code"] = 400;
			$result["status"] = "El N° de documento ya esta registrado.";
			$result["result"] = $row_existe;
			echo json_encode($result);
			exit();
		}

	}

	if ($tipo_docu == 2){
		$sql_existe = "SELECT
		id,
		tipo_persona_id,
		nombre,
		tipo_docu_identidad_id,
		num_docu,
		num_ruc,
		direccion,
		representante_legal,
		num_partida_registral,
		contacto_nombre,
		contacto_telefono,
		contacto_email
		FROM cont_persona WHERE num_ruc like '" . $_POST["num_ruc"] . "' AND status = 1";

		$query_existe = $mysqli->query($sql_existe);
		$row_existe = $query_existe->num_rows;
		if(($row_existe) > 0){
			$result["http_code"] = 400;
			$result["status"] = "El RUC ya esta registrado.";
			$result["result"] = $row_existe;
			echo json_encode($result);
		}
	}


	$query_insert = "INSERT INTO cont_persona(
		tipo_persona_id,
		tipo_docu_identidad_id,
		num_docu,
		num_ruc,
		nombre,
		direccion,
		representante_legal,
		num_partida_registral,
		contacto_nombre,
		contacto_telefono,
		contacto_email,
		user_created_id,
		created_at
	) VALUES (
		$tipo_persona,
		$tipo_docu,
		$num_docu,
		$num_ruc,
		$nombre,
		$direccion,
		$representante_legal,
		$num_partida_registral,
		$contacto_nombre,
		$contacto_telefono,
		$contacto_email,
		$usuario_id,
		$created_at
	)";
	$mysqli->query($query_insert);
	$persona_id = mysqli_insert_id($mysqli);
	$error = '';

	if($mysqli->error){
		$error .= $mysqli->error . $query_insert;
	}

	$query_insert = "
	INSERT INTO cont_propietario(
		contrato_id,
		persona_id,
		user_created_id,
		created_at
	) VALUES (
		$contrato_id,
		$persona_id,
		$usuario_id,
		$created_at
	)";

	$mysqli->query($query_insert);
	$insert_id = mysqli_insert_id($mysqli);

	if($mysqli->error){
		$error .= $mysqli->error . $query_insert;
	}

	$msg_auditoria = guardar_cambios_personalizados_en_auditoria($contrato_id, 'cont_propietario', 'Se agregó un nuevo propietario al contrato.', '', 'Propietarios', 'Propietarios', $_POST["nombre"],0);

	if($msg_auditoria != 'ok'){
		$error .= $msg_auditoria;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $insert_id;
	$result["error"] = $error;

}

if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_propietario") {
	$usuario_id = $login?$login['id']:null;
	$created_at = "'" . date("Y-m-d H:i:s") . "'";

	if ($_POST["tipo_persona_contacto"] == 1) {
		$contacto_nombre = $_POST["nombre"];
	} else {
		$contacto_nombre = $_POST["contacto_nombre"];
	}

	if ($_POST["tipo_docu"] != 2){
		$sql_existe = "SELECT
		id,
		tipo_persona_id,
		nombre,
		tipo_docu_identidad_id,
		num_docu,
		num_ruc,
		direccion,
		representante_legal,
		num_partida_registral,
		contacto_nombre,
		contacto_telefono,
		contacto_email
		FROM cont_persona WHERE num_docu like '" . $_POST["num_docu"] . "' AND status = 1";

		$query_existe = $mysqli->query($sql_existe);
		$row_existe = $query_existe->num_rows;
		if(($row_existe) > 0) {
			$result["http_code"] = 400;
			$result["status"] = "El Nro de Documento ya esta registrado.";
			$result["result"] = $row_existe;
			echo json_encode($result);
			exit();
		}

	} else if($_POST["tipo_docu"] == 2){
		$sql_existe = "SELECT
		id,
		tipo_persona_id,
		nombre,
		tipo_docu_identidad_id,
		num_docu,
		num_ruc,
		direccion,
		representante_legal,
		num_partida_registral,
		contacto_nombre,
		contacto_telefono,
		contacto_email
		FROM cont_persona WHERE num_ruc like '" . $_POST["num_ruc"] . "' AND status = 1";

		$query_existe = $mysqli->query($sql_existe);
		$row_existe = $query_existe->num_rows;
		if(($row_existe) > 0) {
			$result["http_code"] = 400;
			$result["status"] = "El RUC ya esta registrado.";
			$result["result"] = $row_existe;
			echo json_encode($result);
			exit();
		}
	}



	$contrato_id = $_POST["contrato_id"];
	$tipo_persona = $_POST["tipo_persona"];
	$tipo_docu = $_POST["tipo_docu"];
	$num_docu = "'" . $_POST["num_docu"] . "'";
	$num_ruc = "'" . $_POST["num_ruc"] . "'";
	$nombre = "'" . str_replace("'", "",trim($_POST["nombre"])). "'";
	$direccion = "'" . str_replace("'", "",trim($_POST["direccion"])). "'";
	$representante_legal = "'" . str_replace("'", "",trim($_POST["representante_legal"])). "'";
	$num_partida_registral = "'" . $_POST["num_partida_registral"] . "'";
	$contacto_nombre = "'" . str_replace("'", "",trim($contacto_nombre)). "'";
	$contacto_telefono = "'" . $_POST["contacto_telefono"] . "'";
	$contacto_email = "'" . $_POST["contacto_email"] . "'";

	$query_insert = "
	INSERT INTO cont_persona(
		tipo_persona_id,
		tipo_docu_identidad_id,
		num_docu,
		num_ruc,
		nombre,
		direccion,
		representante_legal,
		num_partida_registral,
		contacto_nombre,
		contacto_telefono,
		contacto_email,
		user_created_id,
		created_at
	) VALUES (
		$tipo_persona,
		$tipo_docu,
		$num_docu,
		$num_ruc,
		$nombre,
		$direccion,
		$representante_legal,
		$num_partida_registral,
		$contacto_nombre,
		$contacto_telefono,
		$contacto_email,
		$usuario_id,
		$created_at
	)";
	$mysqli->query($query_insert);
	$persona_id = mysqli_insert_id($mysqli);
	$error = '';

	if($mysqli->error){
		$error .= $mysqli->error . $query_insert;
	}

	$query_insert = "
	INSERT INTO cont_propietario(
		contrato_id,
		persona_id,
		user_created_id,
		created_at
	) VALUES (
		$contrato_id,
		$persona_id,
		$usuario_id,
		$created_at
	)";

	$mysqli->query($query_insert);
	$insert_id = mysqli_insert_id($mysqli);

	if($mysqli->error){
		$error .= $mysqli->error . $query_insert;
	}

	$msg_auditoria = guardar_cambios_personalizados_en_auditoria($contrato_id, 'cont_propietario', 'Se agregó un nuevo propietario al contrato.', '', 'Propietarios', 'Propietarios', $_POST["nombre"], 0);

	if($msg_auditoria != 'ok'){
		$error .= $msg_auditoria;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $insert_id;
	$result["error"] = $error;
}


if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_estado_solicitud") {
    $usuario_id = $login?$login['id']:null;

    if ($usuario_id == null) {
		$result["http_code"] = 400;
		$result["message"] = 'No se puedo guardar el estado porque su sessión a caducado, inicie sesión nuevamente para poder guardar';
		echo json_encode($result);
		exit();
	}




	$fecha_actual = date("Y-m-d");
    $contrato_id = $_POST["contrato_id"];
    $estado_solicitud = $_POST["estado_solicitud"];
    $motivo_estado_na = replace_invalid_caracters($_POST["motivo_estado_na"]);
    $error = '';
	$query_verify = "SELECT tipo_contrato_id, dias_habiles, fecha_suscripcion_contrato, fecha_atencion_gerencia_proveedor, fecha_atencion_gerencia_interno, created_at FROM cont_contrato WHERE contrato_id=$contrato_id";
	$list_query = $mysqli->query($query_verify);
	$tipo_contrato_id = 0;
	if ($list_query->num_rows > 0) {
    	while ($fila = $list_query->fetch_array()) {
        	$dias_habiles = $fila['dias_habiles'];
			$tipo_contrato_id = $fila['tipo_contrato_id'];
			$fecha_solicitud =  isset($fila['fecha_atencion_gerencia_proveedor']) ? $fila['fecha_atencion_gerencia_proveedor'] :
								(isset($fila['fecha_atencion_gerencia_interno']) ? $fila['fecha_atencion_gerencia_interno'] :
								$fila['created_at']);
    	}
    	if (!is_numeric($dias_habiles)) {
			$dias_habiles = sec_contrato_detalle_solicitudv2_num_dias_habiles($fecha_solicitud);

			$query_update_dias_habiles = "
			UPDATE
				cont_contrato
			SET fecha_cambio_estado_solicitud = '$fecha_actual',
				dias_habiles = $dias_habiles,
				usuario_responsable_estado_solicitud_primero =  $usuario_id
			WHERE contrato_id= $contrato_id ";

			$mysqli->query($query_update_dias_habiles);

		 	if($mysqli->error){
			 	$error .= 'Error al cambiar el estado de la solicitud: ' . $mysqli->error . $query_update_dias_habiles;
		 	}

			if ($tipo_contrato_id == 2) {
				//Inicio Seguimiento Proceso
				$REVISION_AREA_USUARIA = 3;
				$NO_HAY_SEGUIMIENTO = 8;
				$OBSERVADO = 9;
				$CONTRATO = 1;
				$data_proceso['usuario_id'] = $usuario_id;
				$data_proceso['tipo_documento_id'] = $CONTRATO;
				$data_proceso['proceso_id'] = $contrato_id;
				$data_proceso['proceso_detalle_id'] = 0;
				if ($estado_solicitud == 2) { // En proceso
					$data_proceso['nueva_etapa_id'] = $REVISION_AREA_USUARIA;
					$data_proceso['status_nueva_etapa_id'] = 1; // En Proceso
					$seg_proceso = new SeguimientoProceso();
					$res_proceso = $seg_proceso->atender_inicio_proceso_legal($data_proceso);
				}else if ($estado_solicitud == 3){ // Observado
					$data_proceso['nueva_etapa_id'] = $OBSERVADO;
					$data_proceso['status_nueva_etapa_id'] = 2; // En Proceso
					$seg_proceso = new SeguimientoProceso();
					$res_proceso = $seg_proceso->atender_inicio_proceso_legal($data_proceso);
				}else if ($estado_solicitud == 4){ // No Aplica
					$data_proceso['nueva_etapa_id'] = $NO_HAY_SEGUIMIENTO;
					$data_proceso['status_nueva_etapa_id'] = 2; // 
					$seg_proceso = new SeguimientoProceso();
					$res_proceso = $seg_proceso->atender_inicio_proceso_legal($data_proceso);
				}
			//Fin Seguimiento Proceso
			}
			
   		}
	}

	if ($estado_solicitud == 4){
        $query_update = "
        UPDATE
            cont_contrato
        SET
            estado_solicitud = $estado_solicitud,
            usuario_responsable_estado_solicitud = $usuario_id,
            motivo_estado_na = '$motivo_estado_na'

        WHERE
            contrato_id = $contrato_id
        ";
        send_email_solicitud_estado_no_aplica($_POST["contrato_id"], $motivo_estado_na);
    }else{
        $query_update = "
        UPDATE
            cont_contrato
        SET
            estado_solicitud = $estado_solicitud,
            usuario_responsable_estado_solicitud = $usuario_id
        WHERE
            contrato_id = $contrato_id
        ";
    }

        $mysqli->query($query_update);

        if($mysqli->error){
            $error .= 'Error al cambiar el estado de la solicitud: ' . $mysqli->error . $query_update;
        }

    $result["http_code"] = $error == '' ? 200:400;
    $result["message"] =$error == ''? "Se ha cambiado el estado de la solicitud.":$error;
    $result["result"] = $contrato_id;
    $result["error"] = $error;
}

if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_estado_solicitud_adenda") {
	$usuario_id = $login?$login['id']:null;

	if ($usuario_id == null) {
		$result["http_code"] = 400;
		$result["message"] = 'No se puedo guardar el estado porque su sessión a caducado, inicie sesión nuevamente para poder guardar';
		echo json_encode($result);
		exit();
	}

	$fecha_actual = date("Y-m-d");
	$adenda_id = $_POST["adenda_id"];
	$estado_solicitud = $_POST["estado_solicitud"];
	$error = '';

	$query_verify = "SELECT dias_habiles, aprobado_el, created_at FROM cont_adendas WHERE id = $adenda_id";
	$list_query = $mysqli->query($query_verify);
	if ($list_query->num_rows > 0) {
		while ($fila = $list_query->fetch_array()) {
			$dias_habiles = $fila['dias_habiles'];
			$fecha_solicitud = isset($fila['aprobado_el']) ? $fila['aprobado_el'] : $fila['created_at'];
		}
		if (!is_numeric($dias_habiles)) {
			$dias_habiles = sec_contrato_detalle_solicitudv2_num_dias_habiles($fecha_solicitud);

			$query_update_dias_habiles = "
			UPDATE
				cont_adendas
			SET fecha_cambio_estado_solicitud = '$fecha_actual',
				dias_habiles = $dias_habiles,
				usuario_responsable_estado_solicitud_primero =  $usuario_id
			WHERE id = $adenda_id ";

			$mysqli->query($query_update_dias_habiles);

			if($mysqli->error){
				$error .= 'Error al cambiar el estado de la solicitud: ' . $mysqli->error . $query_update_dias_habiles;
			}

			//Inicio Seguimiento Proceso
			$REVISION_AREA_USUARIA = 3;
			$NO_HAY_SEGUIMIENTO = 8;
			$OBSERVADO = 9;
			$ADENDA_DE_CONTRATO = 2;
			$data_proceso['usuario_id'] = $usuario_id;
			$data_proceso['tipo_documento_id'] = $ADENDA_DE_CONTRATO;
			$data_proceso['proceso_id'] = $adenda_id;
			$data_proceso['proceso_detalle_id'] = 0;
			if ($estado_solicitud == 2) { //En Proceso
				$data_proceso['nueva_etapa_id'] = $REVISION_AREA_USUARIA;
				$data_proceso['status_nueva_etapa_id'] = 1; 
				$seg_proceso = new SeguimientoProceso();
				$res_proceso = $seg_proceso->atender_inicio_proceso_legal($data_proceso);
			}else if ($estado_solicitud == 3){ // Observado
				$data_proceso['nueva_etapa_id'] = $OBSERVADO;
				$data_proceso['status_nueva_etapa_id'] = 2; 
				$seg_proceso = new SeguimientoProceso();
				$res_proceso = $seg_proceso->atender_inicio_proceso_legal($data_proceso);
			}else if ($estado_solicitud == 4){ // No Aplica
				$data_proceso['nueva_etapa_id'] = $NO_HAY_SEGUIMIENTO;
				$data_proceso['status_nueva_etapa_id'] = 2;
				$seg_proceso = new SeguimientoProceso();
				$res_proceso = $seg_proceso->atender_inicio_proceso_legal($data_proceso);
			}
			//Fin Seguimiento Proceso
		}
	}

	$query_update = "
	UPDATE
		cont_adendas
	SET
		estado_solicitud_id = $estado_solicitud,
		usuario_responsable_estado_solicitud_id = $usuario_id
	WHERE
		id = $adenda_id
	";

	$mysqli->query($query_update);

	if($mysqli->error){
		$error .= 'Error al cambiar el estado de la solicitud: ' . $mysqli->error . $query_update;
	}

	$result["http_code"] = 200;
	$result["status"] = "Se ha cambiado el estado de la solicitud.";
	$result["result"] = $adenda_id;
	$result["error"] = $error;
}

if (isset($_POST["accion"]) && $_POST["accion"]==="atender_seguimiento_proceso") {
	$usuario_id = $login?$login['id']:null;

	if ($usuario_id == null) {
		$result["http_code"] = 400;
		$result["message"] = 'No se puedo guardar el estado porque su sessión a caducado, inicie sesión nuevamente para poder guardar';
		echo json_encode($result);
		exit();
	}
	$data_proceso['seguimiento_id'] = $_POST['seguimiento_id'];
	$data_proceso['nueva_etapa_id'] = $_POST['nueva_etapa_id'];
	$data_proceso['usuario_id'] = $usuario_id;

	$seg_proceso = new SeguimientoProceso();
	$rpta_seg_proceso = $seg_proceso->atender_seguimiento_proceso($data_proceso);
	echo json_encode($rpta_seg_proceso);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="reiniciar_seguimiento_proceso") {
	$usuario_id = $login?$login['id']:null;

	if ($usuario_id == null) {
		$result["http_code"] = 400;
		$result["message"] = 'No se puedo guardar el estado porque su sessión a caducado, inicie sesión nuevamente para poder guardar';
		echo json_encode($result);
		exit();
	}
	$data_proceso['tipo_documento_id'] = $_POST['tipo_documento_id'];
	$data_proceso['proceso_id'] = $_POST['proceso_id'];
	$data_proceso['proceso_detalle_id'] = $_POST['proceso_detalle_id'];
	$data_proceso['nueva_etapa_id'] = $_POST['nueva_etapa_id'];
	$data_proceso['usuario_id'] = $usuario_id;

	$seg_proceso = new SeguimientoProceso();
	$rpta_seg_proceso = $seg_proceso->reiniciar_seguimiento_proceso($data_proceso);
	echo json_encode($rpta_seg_proceso);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="eliminar_observacion_contrato") {
	$usuario_id = $login?$login['id']:null;

	if ($usuario_id == null) {
		$result["status"] = 400;
		$result["message"] = 'No se puedo guardar el estado porque su sessión a caducado, inicie sesión nuevamente para poder guardar';
		echo json_encode($result);
		exit();
	}
	
	$query_update = "UPDATE cont_observaciones SET
		status = 0
	WHERE id = ".$_POST['id_observacion'];
	$mysqli->query($query_update);

	if($mysqli->error){
		$result['status'] = 400;
		$result['message'] = 'Error al eliminar la observación: ' . $mysqli->error . $query_update;
		echo json_encode($result);
		exit();
	}

	$result['status'] = 200;
	$result['message'] = 'Se ha eliminado la observación correctamente.';
	echo json_encode($result);
	exit();
}

function sec_contrato_detalle_solicitudv2_is_valid_email($str) {
	return (false !== filter_var($str, FILTER_VALIDATE_EMAIL));
}

function send_email_cambio_contrato($auditoria_id)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$sel_query = $mysqli->query("SELECT
				a.nombre_tabla,
				a.valor_original,
				a.nombre_campo,
				a.nombre_menu_usuario,
				a.nombre_campo_usuario,
				a.tipo_valor,
				a.valor_varchar,
				a.valor_int,
				a.valor_date,
				a.valor_decimal,
				a.valor_select_option,
				a.valor_id_tabla,
				a.created_at AS fecha_del_cambio,
				CONCAT(IFNULL(p.nombre, ''), ' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS usuario_que_realizo_cambio,
				ar.nombre AS area,
				pjc.correo,
				c.contrato_id,
				co.sigla AS sigla_correlativo,
				c.codigo_correlativo,
				c.tipo_contrato_id,
				c.etapa_id,

				c.check_gerencia_proveedor,
				c.aprobacion_gerencia_proveedor,
				c.fecha_atencion_gerencia_proveedor,

				c.check_gerencia_interno,
				c.fecha_atencion_gerencia_interno,
				c.aprobacion_gerencia_interno,
				CONCAT('#',d.codigo) AS codigo,

				per.correo AS usuario_creacion_correo,
				c.gerente_area_email as email_del_gerente_area_2,
				peg.correo AS email_del_gerente_area,
				puap.correo AS email_del_aprobante


			FROM
				cont_auditoria a
					INNER JOIN
				tbl_usuarios u ON a.user_created_id = u.id
					INNER JOIN
				tbl_personal_apt p ON u.personal_id = p.id
					INNER JOIN
				tbl_areas ar ON p.area_id = ar.id
					INNER JOIN
				cont_contrato c ON c.contrato_id = a.contrato_id
					INNER JOIN
				tbl_usuarios ujc ON c.user_created_id = ujc.id
					INNER JOIN
				tbl_personal_apt pjc ON ujc.personal_id = pjc.id
					LEFT JOIN
				cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
					LEFT JOIN 
				cont_contrato_detalle d ON d.id = a.contrato_detalle_id

				LEFT JOIN tbl_usuarios uc ON c.user_created_id = uc.id
				LEFT JOIN tbl_personal_apt per ON u.personal_id = per.id

				LEFT JOIN tbl_usuarios ug ON c.gerente_area_id = ug.id
				LEFT JOIN tbl_personal_apt peg ON ug.personal_id = peg.id

				LEFT JOIN tbl_usuarios uap ON c.aprobado_por = uap.id
				LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id


			WHERE
				a.status = 1 AND a.id = " . $auditoria_id . "
			ORDER BY a.id DESC");



	$body = "";
	$body .= '<html>';

	$usuario_creacion_correo = '';
	$contrato_id = '';
	$tipo_contrato_id = '';
	$envio_correo = true;
	$correos_adicionales = [];
	$nombre_campo = '';
	while($sel = $sel_query->fetch_assoc())
	{
		$tipo_valor = $sel["tipo_valor"];
		$usuario_creacion_correo = $sel["correo"];
		$contrato_id = $sel["contrato_id"];
		$nombre_campo = $sel["nombre_campo"];

		if(!Empty($sel['correo'])){
			array_push($correos_adicionales, trim($sel['correo'])); 
		}
		if(!Empty($sel['usuario_creacion_correo'])){
			array_push($correos_adicionales, trim($sel['usuario_creacion_correo'])); 
		}
		if(!Empty($sel['email_del_gerente_area_2'])){
			array_push($correos_adicionales, trim($sel['email_del_gerente_area_2'])); 
		}
		if(!Empty($sel['email_del_gerente_area'])){
			array_push($correos_adicionales, trim($sel['email_del_gerente_area'])); 
		}
		if(!Empty($sel['email_del_aprobante'])){
			array_push($correos_adicionales, trim($sel['email_del_aprobante'])); 
		}

		$sigla_correlativo = $sel["sigla_correlativo"];
		$codigo_correlativo = $sel["codigo_correlativo"];
		$tipo_contrato_id = $sel["tipo_contrato_id"];

		
		if($sel['tipo_contrato_id'] == 2){
			if($sel['check_gerencia_proveedor'] == 1 && $sel['etapa_id'] == 1){
				if(is_null($sel['fecha_atencion_gerencia_proveedor'])){
					$envio_correo = false;
				}
			}
		}

		if($sel['tipo_contrato_id'] == 7){
			if($sel['check_gerencia_interno'] == 1 && $sel['etapa_id'] == 1){
				if(is_null($sel['fecha_atencion_gerencia_interno'])){
					$envio_correo = false;
				}
			}
		}

		if ($tipo_valor == 'varchar') {
			$nuevo_valor = $sel['valor_varchar'];
		} else if ($tipo_valor == 'int') {
			$nuevo_valor = $sel['valor_int'];
		} else if ($tipo_valor == 'date') {
			$nuevo_valor = $sel['valor_date'];
		} else if ($tipo_valor == 'decimal') {
			$nuevo_valor = $sel['valor_decimal'];
		} else if ($tipo_valor == 'select_option') {
			$nuevo_valor = $sel['valor_select_option'];
		}

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

		$body .= '<thead>';

		$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>Datos Generales</b>';
			$body .= '</th>';
		$body .= '</tr>';


		$body .= '</thead>';
		if(!Empty($sel["codigo"])){
			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Contrato:</b></td>';
			$body .= '<td>'.$sel["codigo"].'</td>';
			$body .= '</tr>';
		}
		

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Nombre del menú:</b></td>';
			$body .= '<td>'.$sel["nombre_menu_usuario"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Nombre del campo:</b></td>';
			$body .= '<td>'.$sel["nombre_campo_usuario"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Valor anterior:</b></td>';
			$body .= '<td>'.$sel["valor_original"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Nuevo valor:</b></td>';
			$body .= '<td>'.$nuevo_valor.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Usuario que realizó el cambio:</b></td>';
			$body .= '<td>'.$sel["usuario_que_realizo_cambio"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha del cambio:</b></td>';
			$body .= '<td>'.$sel["fecha_del_cambio"].'</td>';
		$body .= '</tr>';


		$body .= '</table>';
		$body .= '</div>';


	}
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		if ($tipo_contrato_id == 1){
			$url_solicitud = $host.'/?sec_id=contrato&sub_sec_id=detalle_solicitudv2&id='.$contrato_id;
		}else if ($tipo_contrato_id == 2){
			$url_solicitud = $host.'/?sec_id=contrato&sub_sec_id=detalleSolicitudProveedor&id='.$contrato_id;
		}else if ($tipo_contrato_id == 5){
			$url_solicitud = $host.'/?sec_id=contrato&sub_sec_id=detalle_solicitudv2_acuerdo_confidencialidad&id='.$contrato_id;
		}else if ($tipo_contrato_id == 6){
			$url_solicitud = $host.'/?sec_id=contrato&sub_sec_id=detalle_agente&id='.$contrato_id;
		}else if ($tipo_contrato_id == 7){
			$url_solicitud = $host.'/?sec_id=contrato&sub_sec_id=detalle_solicitudv2_interno&id='.$contrato_id;
		}

		$body .= '<div style="width: 700px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$url_solicitud.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
				$body .= '<b>Ver Solicitud</b>';
		    $body .= '</a>';
		$body .= '</div>';

		$body .= '</html>';
		$body .= "";

	if ($envio_correo == false){
		return false;
	}
	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));

	if ($tipo_contrato_id == 1){
		$lista_correos = $correos->send_email_modificacion_contrato_arrendamiento([]);
	}else if ($tipo_contrato_id == 2){
		$lista_correos = $correos->send_email_modificacion_contrato_proveedor($correos_adicionales);
	}else if ($tipo_contrato_id == 5){
		$lista_correos = $correos->send_email_modificacion_acuerdo_confidencialidad([]);
	}else if ($tipo_contrato_id == 6){
		$lista_correos = $correos->send_email_modificacion_contrato_agente([]);
	}else if ($tipo_contrato_id == 7){
		$lista_correos = $correos->send_email_modificacion_contrato_interno([]);
	}


	$cc = $lista_correos['cc'];

	if ($tipo_contrato_id == 1){
		if($nombre_campo == 'abogado_id'){
			$titulo_email = "Gestion - Sistema Contratos - Asignación de abogado : Código - ";
		}else{
			$titulo_email = "Gestion - Sistema Contratos - Modificacion de Solicitud Contrato de Arrendamiento : Código - ";
		}
	}else if($tipo_contrato_id == 2){
		$titulo_email = "Gestion - Sistema Contratos - Modificacion de Solicitud Contrato de Proveedor : Código - ";
	}else if($tipo_contrato_id == 5){
		$titulo_email = "Gestion - Sistema Contratos - Modificacion de Solicitud Acuerdo de confidencialidad : Código - ";
	}else if($tipo_contrato_id == 6){
		$titulo_email = "Gestion - Sistema Contratos - Modificacion de Solicitud Contrato de de Agente : Código - ";
	}else if($tipo_contrato_id == 7){
		$titulo_email = "Gestion - Sistema Contratos - Modificacion de Solicitud Contrato Interno : Código - ";
	}



	$bcc = $lista_correos['bcc'];

	$request = [
		"subject" => $titulo_email.$sigla_correlativo.$codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc)
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc)
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();
		//return true;

	}
	catch (Exception $e)
	{
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}


}

function send_email_cambio_contrato_confirmacion($cambio_id)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$sel_query = $mysqli->query("SELECT
		ccc.nombre_tabla,
		ccc.valor_original,
		ccc.nombre_campo,
		ccc.nombre_menu_usuario,
		ccc.nombre_campo_usuario,
		ccc.tipo_valor,
		ccc.valor_varchar,
		ccc.valor_int,
		ccc.valor_date,
		ccc.valor_decimal,
		ccc.valor_select_option,
		ccc.valor_id_tabla,
		ccc.created_at AS fecha_del_cambio,
		CONCAT(IFNULL(pc.nombre, ''), ' ', IFNULL(pc.apellido_paterno, ''), ' ', IFNULL(pc.apellido_materno, '')) AS usuario_creacion,
		CONCAT(IFNULL(p.nombre, ''), ' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS aprobante,
		p.correo AS correo_aprobante,
		ar.nombre AS area,
		c.contrato_id,
		co.sigla AS sigla_correlativo,
		c.codigo_correlativo,
		c.tipo_contrato_id,
		c.etapa_id
	FROM
		cont_contrato_cambios ccc 
			INNER JOIN
		tbl_usuarios u ON ccc.director_id = u.id
			INNER JOIN
		tbl_personal_apt p ON u.personal_id = p.id
			INNER JOIN
		tbl_usuarios uc ON ccc.user_created_id = uc.id
			INNER JOIN
		tbl_personal_apt pc ON uc.personal_id = pc.id
			INNER JOIN
		tbl_areas ar ON p.area_id = ar.id
			INNER JOIN
		cont_contrato c ON c.contrato_id = ccc.contrato_id
			LEFT JOIN
		cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			LEFT JOIN 
		cont_contrato_detalle d ON d.id = ccc.contrato_detalle_id
	WHERE
		ccc.status = 1 AND ccc.id = " . $cambio_id . "
	ORDER BY ccc.id DESC");



	$body = "";
	$body .= '<html>';

	$usuario_creacion_correo = '';
	$contrato_id = '';
	$tipo_contrato_id = '';
	$envio_correo = true;
	$correos_adicionales = [];
	$nombre_campo = '';
	while($sel = $sel_query->fetch_assoc())
	{
		$tipo_valor = $sel["tipo_valor"];
		$contrato_id = $sel["contrato_id"];
		$nombre_campo = $sel["nombre_campo"];

		if(!Empty($sel['correo_aprobante'])){
			array_push($correos_adicionales, trim($sel['correo_aprobante'])); 
		}

		$sigla_correlativo = $sel["sigla_correlativo"];
		$codigo_correlativo = $sel["codigo_correlativo"];
		$tipo_contrato_id = $sel["tipo_contrato_id"];

		if ($tipo_valor == 'varchar') {
			$nuevo_valor = $sel['valor_varchar'];
		} else if ($tipo_valor == 'int') {
			$nuevo_valor = $sel['valor_int'];
		} else if ($tipo_valor == 'date') {
			$nuevo_valor = $sel['valor_date'];
		} else if ($tipo_valor == 'decimal') {
			$nuevo_valor = $sel['valor_decimal'];
		} else if ($tipo_valor == 'select_option') {
			$nuevo_valor = $sel['valor_select_option'];
		}

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

		$body .= '<thead>';

		$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>Datos Generales</b>';
			$body .= '</th>';
		$body .= '</tr>';


		$body .= '</thead>';
		if(!Empty($sel["codigo"])){
			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Contrato:</b></td>';
			$body .= '<td>'.$sel["codigo"].'</td>';
			$body .= '</tr>';
		}
		

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Nombre del menú:</b></td>';
			$body .= '<td>'.$sel["nombre_menu_usuario"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Nombre del campo:</b></td>';
			$body .= '<td>'.$sel["nombre_campo_usuario"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Valor anterior:</b></td>';
			$body .= '<td>'.$sel["valor_original"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Nuevo valor:</b></td>';
			$body .= '<td>'.$nuevo_valor.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Usuario que realizó el cambio:</b></td>';
			$body .= '<td>'.$sel["usuario_creacion"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha del cambio:</b></td>';
			$body .= '<td>'.$sel["fecha_del_cambio"].'</td>';
		$body .= '</tr>';


		$body .= '</table>';
		$body .= '</div>';


	}
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		if ($tipo_contrato_id == 1){
			$url_solicitud = $host.'/?sec_id=contrato&sub_sec_id=detalle_solicitudv2&id='.$contrato_id;
		}else if ($tipo_contrato_id == 2){
			$url_solicitud = $host.'/?sec_id=contrato&sub_sec_id=detalleSolicitudProveedor&id='.$contrato_id;
		}else if ($tipo_contrato_id == 5){
			$url_solicitud = $host.'/?sec_id=contrato&sub_sec_id=detalle_solicitudv2_acuerdo_confidencialidad&id='.$contrato_id;
		}else if ($tipo_contrato_id == 6){
			$url_solicitud = $host.'/?sec_id=contrato&sub_sec_id=detalle_agente&id='.$contrato_id;
		}else if ($tipo_contrato_id == 7){
			$url_solicitud = $host.'/?sec_id=contrato&sub_sec_id=detalle_solicitudv2_interno&id='.$contrato_id;
		}

		$body .= '<div style="width: 700px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$url_solicitud.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
				$body .= '<b>Ver Solicitud</b>';
		    $body .= '</a>';
		$body .= '</div>';

		$body .= '</html>';
		$body .= "";

	if ($envio_correo == false){
		return false;
	}
	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_cambio_contrato_confirmacion($correos_adicionales);
	
	$cc = $lista_correos['cc'];

	if (env('SEND_EMAIL') == 'TEST') { // Imprimir lista de correos que se enviarian en producción pero solo se visualizara en Desarrollo 
		$correos_produccion = implode(", ", $lista_correos['cc_dev']);

		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
		$body .= '<thead>';
		$body .= '<tr>';
			$body .= '<th style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>Lista de Correos</b>';
			$body .= '</th>';
		$body .= '</tr>';
		$body .= '</thead>';
		$body .= '<tbody>';
		$body .= '<tr>';
			$body .= '<td>'.$correos_produccion.'</td>';
		$body .= '</tr>';
		$body .= '</tfoot>';
		$body .= '<tr>';
			$body .= '<td style="text-align: center;"><small>*** Esta sección solo se envia en desarroollo ***</small></td>';
		$body .= '</tr>';
		$body .= '</tfoot>';
		$body .= '</table>';
		$body .= '</div>';
	}

	if ($tipo_contrato_id == 1){
		$titulo_email = "Gestion - Sistema Contratos - Confirmación de Modificación - Solicitud Contrato de Arrendamiento : Código - ";
	}else if($tipo_contrato_id == 2){
		$titulo_email = "Gestion - Sistema Contratos - Confirmación de Modificación - Solicitud Contrato de Proveedor : Código - ";
	}else if($tipo_contrato_id == 5){
		$titulo_email = "Gestion - Sistema Contratos - Confirmación de Modificación - Solicitud Acuerdo de confidencialidad : Código - ";
	}else if($tipo_contrato_id == 6){
		$titulo_email = "Gestion - Sistema Contratos - Confirmación de Modificación - Solicitud Contrato de de Agente : Código - ";
	}else if($tipo_contrato_id == 7){
		$titulo_email = "Gestion - Sistema Contratos - Confirmación de Modificación - Solicitud Contrato Interno : Código - ";
	}

	$bcc = $lista_correos['bcc'];

	$request = [
		"subject" => $titulo_email.$sigla_correlativo.$codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc)
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc)
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();
		//return true;

	}
	catch (Exception $e)
	{
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}


}

function send_email_cambio_contrato_rechazado($auditoria_id)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$sel_query = $mysqli->query("SELECT
				a.nombre_tabla,
				a.valor_original,
				a.nombre_campo,
				a.nombre_menu_usuario,
				a.nombre_campo_usuario,
				a.tipo_valor,
				a.valor_varchar,
				a.valor_int,
				a.valor_date,
				a.valor_decimal,
				a.valor_select_option,
				a.valor_id_tabla,
				a.created_at AS fecha_del_cambio,
				CONCAT(IFNULL(p.nombre, ''), ' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS usuario_que_realizo_cambio,
				ar.nombre AS area,
				pjc.correo,
				c.contrato_id,
				co.sigla AS sigla_correlativo,
				c.codigo_correlativo,
				c.tipo_contrato_id,
				c.etapa_id,

				c.check_gerencia_proveedor,
				c.aprobacion_gerencia_proveedor,
				c.fecha_atencion_gerencia_proveedor,

				c.check_gerencia_interno,
				c.fecha_atencion_gerencia_interno,
				c.aprobacion_gerencia_interno,
				CONCAT('#',d.codigo) AS codigo,

				per.correo AS usuario_creacion_correo,
				c.gerente_area_email as email_del_gerente_area_2,
				peg.correo AS email_del_gerente_area,
				puap.correo AS email_del_aprobante


			FROM
				cont_auditoria a
					INNER JOIN
				tbl_usuarios u ON a.user_created_id = u.id
					INNER JOIN
				tbl_personal_apt p ON u.personal_id = p.id
					INNER JOIN
				tbl_areas ar ON p.area_id = ar.id
					INNER JOIN
				cont_contrato c ON c.contrato_id = a.contrato_id
					INNER JOIN
				tbl_usuarios ujc ON c.user_created_id = ujc.id
					INNER JOIN
				tbl_personal_apt pjc ON ujc.personal_id = pjc.id
					LEFT JOIN
				cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
					LEFT JOIN 
				cont_contrato_detalle d ON d.id = a.contrato_detalle_id

				LEFT JOIN tbl_usuarios uc ON c.user_created_id = uc.id
				LEFT JOIN tbl_personal_apt per ON u.personal_id = per.id

				LEFT JOIN tbl_usuarios ug ON c.gerente_area_id = ug.id
				LEFT JOIN tbl_personal_apt peg ON ug.personal_id = peg.id

				LEFT JOIN tbl_usuarios uap ON c.aprobado_por = uap.id
				LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id


			WHERE a.id = " . $auditoria_id . "
			ORDER BY a.id DESC");



	$body = "";
	$body .= '<html>';

	$usuario_creacion_correo = '';
	$contrato_id = '';
	$tipo_contrato_id = '';
	$envio_correo = true;
	$correos_adicionales = [];
	$nombre_campo = '';
	while($sel = $sel_query->fetch_assoc())
	{
		$tipo_valor = $sel["tipo_valor"];
		$usuario_creacion_correo = $sel["correo"];
		$contrato_id = $sel["contrato_id"];
		$nombre_campo = $sel["nombre_campo"];

		if(!Empty($sel['correo'])){
			array_push($correos_adicionales, trim($sel['correo'])); 
		}
		if(!Empty($sel['usuario_creacion_correo'])){
			array_push($correos_adicionales, trim($sel['usuario_creacion_correo'])); 
		}
		if(!Empty($sel['email_del_gerente_area_2'])){
			array_push($correos_adicionales, trim($sel['email_del_gerente_area_2'])); 
		}
		if(!Empty($sel['email_del_gerente_area'])){
			array_push($correos_adicionales, trim($sel['email_del_gerente_area'])); 
		}
		if(!Empty($sel['email_del_aprobante'])){
			array_push($correos_adicionales, trim($sel['email_del_aprobante'])); 
		}

		$sigla_correlativo = $sel["sigla_correlativo"];
		$codigo_correlativo = $sel["codigo_correlativo"];
		$tipo_contrato_id = $sel["tipo_contrato_id"];

		
		if($sel['tipo_contrato_id'] == 2){
			if($sel['check_gerencia_proveedor'] == 1 && $sel['etapa_id'] == 1){
				if(is_null($sel['fecha_atencion_gerencia_proveedor'])){
					$envio_correo = false;
				}
			}
		}

		if($sel['tipo_contrato_id'] == 7){
			if($sel['check_gerencia_interno'] == 1 && $sel['etapa_id'] == 1){
				if(is_null($sel['fecha_atencion_gerencia_interno'])){
					$envio_correo = false;
				}
			}
		}

		if ($tipo_valor == 'varchar') {
			$nuevo_valor = $sel['valor_varchar'];
		} else if ($tipo_valor == 'int') {
			$nuevo_valor = $sel['valor_int'];
		} else if ($tipo_valor == 'date') {
			$nuevo_valor = $sel['valor_date'];
		} else if ($tipo_valor == 'decimal') {
			$nuevo_valor = $sel['valor_decimal'];
		} else if ($tipo_valor == 'select_option') {
			$nuevo_valor = $sel['valor_select_option'];
		}

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

		$body .= '<thead>';

		$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>Datos Generales</b>';
			$body .= '</th>';
		$body .= '</tr>';


		$body .= '</thead>';
		if(!Empty($sel["codigo"])){
			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Contrato:</b></td>';
			$body .= '<td>'.$sel["codigo"].'</td>';
			$body .= '</tr>';
		}
		

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Nombre del menú:</b></td>';
			$body .= '<td>'.$sel["nombre_menu_usuario"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Nombre del campo:</b></td>';
			$body .= '<td>'.$sel["nombre_campo_usuario"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Valor anterior:</b></td>';
			$body .= '<td>'.$sel["valor_original"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Nuevo valor:</b></td>';
			$body .= '<td>'.$nuevo_valor.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Usuario que realizó el cambio:</b></td>';
			$body .= '<td>'.$sel["usuario_que_realizo_cambio"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha del cambio:</b></td>';
			$body .= '<td>'.$sel["fecha_del_cambio"].'</td>';
		$body .= '</tr>';


		$body .= '</table>';
		$body .= '</div>';


	}
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		if ($tipo_contrato_id == 1){
			$url_solicitud = $host.'/?sec_id=contrato&sub_sec_id=detalle_solicitudv2&id='.$contrato_id;
		}else if ($tipo_contrato_id == 2){
			$url_solicitud = $host.'/?sec_id=contrato&sub_sec_id=detalleSolicitudProveedor&id='.$contrato_id;
		}else if ($tipo_contrato_id == 5){
			$url_solicitud = $host.'/?sec_id=contrato&sub_sec_id=detalle_solicitudv2_acuerdo_confidencialidad&id='.$contrato_id;
		}else if ($tipo_contrato_id == 6){
			$url_solicitud = $host.'/?sec_id=contrato&sub_sec_id=detalle_agente&id='.$contrato_id;
		}else if ($tipo_contrato_id == 7){
			$url_solicitud = $host.'/?sec_id=contrato&sub_sec_id=detalle_solicitudv2_interno&id='.$contrato_id;
		}

		$body .= '<div style="width: 700px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$url_solicitud.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
				$body .= '<b>Ver Solicitud</b>';
		    $body .= '</a>';
		$body .= '</div>';

		$body .= '</html>';
		$body .= "";

	if ($envio_correo == false){
		return false;
	}
	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));

	if ($tipo_contrato_id == 1){
		$lista_correos = $correos->send_email_modificacion_contrato_arrendamiento([]);
	}else if ($tipo_contrato_id == 2){
		$lista_correos = $correos->send_email_modificacion_contrato_proveedor($correos_adicionales);
	}else if ($tipo_contrato_id == 5){
		$lista_correos = $correos->send_email_modificacion_acuerdo_confidencialidad([]);
	}else if ($tipo_contrato_id == 6){
		$lista_correos = $correos->send_email_modificacion_contrato_agente([]);
	}else if ($tipo_contrato_id == 7){
		$lista_correos = $correos->send_email_modificacion_contrato_interno([]);
	}


	$cc = $lista_correos['cc'];

	if ($tipo_contrato_id == 1){
		if($nombre_campo == 'abogado_id'){
			$titulo_email = "Gestion - Sistema Contratos - Asignación de abogado : Código - ";
		}else{
			$titulo_email = "Gestion - Sistema Contratos - Modificacion de Solicitud Rechazada - Contrato de Arrendamiento : Código - ";
		}
	}else if($tipo_contrato_id == 2){
		$titulo_email = "Gestion - Sistema Contratos - Modificacion de Solicitud Rechazada - Contrato de Proveedor : Código - ";
	}else if($tipo_contrato_id == 5){
		$titulo_email = "Gestion - Sistema Contratos - Modificacion de Solicitud Rechazada - Acuerdo de confidencialidad : Código - ";
	}else if($tipo_contrato_id == 6){
		$titulo_email = "Gestion - Sistema Contratos - Modificacion de Solicitud Rechazada - Contrato de de Agente : Código - ";
	}else if($tipo_contrato_id == 7){
		$titulo_email = "Gestion - Sistema Contratos - Modificacion de Solicitud Rechazada - Contrato Interno : Código - ";
	}



	$bcc = $lista_correos['bcc'];

	$request = [
		"subject" => $titulo_email.$sigla_correlativo.$codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc)
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc)
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();
		//return true;

	}
	catch (Exception $e)
	{
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}


}



function send_email_cambio_anexo_contrato($data)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];


	$contrato_id = $data['contrato_id'];
	$tipo_archivo = $data['tipo_archivo'];
	$archivo_anterior = $data['archivo_anterior'];
	$archivo_nuevo = $data['archivo_nuevo'];
	$archivo_insert_id = $data['archivo_insert_id'];

	$sel_query = $mysqli->query("SELECT a.nombre AS nombre_archivo, t.nombre_tipo_archivo,
	CONCAT(IFNULL(pjc.nombre, ''), ' ', IFNULL(pjc.apellido_paterno, ''), ' ', IFNULL(pjc.apellido_materno, '')) AS usuario_que_realizo_cambio,
	pc.correo,
	co.sigla AS sigla_correlativo,
	c.codigo_correlativo,
	c.tipo_contrato_id,
	c.contrato_id,

	c.etapa_id,
	c.check_gerencia_proveedor,
	c.aprobacion_gerencia_proveedor,
	c.fecha_atencion_gerencia_proveedor,

	c.check_gerencia_interno,
	c.fecha_atencion_gerencia_interno,
	c.aprobacion_gerencia_interno,



	a.created_at AS fecha_del_cambio
	FROM cont_archivos AS a
	INNER JOIN cont_tipo_archivos AS t ON t.tipo_archivo_id = a.tipo_archivo_id
	INNER JOIN tbl_usuarios ujc ON a.user_created_id = ujc.id
	INNER JOIN tbl_personal_apt pjc ON ujc.personal_id = pjc.id
	INNER JOIN cont_contrato AS c ON c.contrato_id = a.contrato_id
	INNER JOIN tbl_usuarios us ON c.user_created_id = us.id
	INNER JOIN tbl_personal_apt pc ON us.personal_id = pc.id
	LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
	WHERE a.archivo_id = " . $archivo_insert_id);

	$body = "";
	$body .= '<html>';

	$usuario_creacion_correo = '';
	$contrato_id = '';
	$tipo_contrato_id = '';
	$envio_correo = true;
	while($sel = $sel_query->fetch_assoc())
	{

		$usuario_creacion_correo = $sel["correo"];
		$contrato_id = $sel["contrato_id"];
		$sigla_correlativo = $sel["sigla_correlativo"];
		$codigo_correlativo = $sel["codigo_correlativo"];
		$tipo_contrato_id = $sel["tipo_contrato_id"];
		$nombre_tipo_archivo = $sel["nombre_tipo_archivo"];

		if($sel['tipo_contrato_id'] == 2){
			if($sel['check_gerencia_proveedor'] == 1 && $sel['etapa_id'] == 1){
				if(is_null($sel['fecha_atencion_gerencia_proveedor'])){
					$envio_correo = false;
				}
			}
		}

		if($sel['tipo_contrato_id'] == 7){
			if($sel['check_gerencia_interno'] == 1 && $sel['etapa_id'] == 1){
				if(is_null($sel['fecha_atencion_gerencia_interno'])){
					$envio_correo = false;
				}
			}
		}

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

		$body .= '<thead>';

		$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>Datos de la modificación</b>';
			$body .= '</th>';
		$body .= '</tr>';

		$body .= '</thead>';


		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Anexo Reemplazado:</b></td>';
			$body .= '<td>'.$nombre_tipo_archivo.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Usuario que realizó el cambio:</b></td>';
			$body .= '<td>'.$sel["usuario_que_realizo_cambio"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha del cambio:</b></td>';
			$body .= '<td>'.$sel["fecha_del_cambio"].'</td>';
		$body .= '</tr>';


		$body .= '</table>';
		$body .= '</div>';


	}
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		if ($tipo_contrato_id == 1){
			$url_solicitud = $host.'/?sec_id=contrato&sub_sec_id=detalle_solicitudv2&id='.$contrato_id;
		}else if ($tipo_contrato_id == 2){
			$url_solicitud = $host.'/?sec_id=contrato&sub_sec_id=detalleSolicitudProveedor&id='.$contrato_id;
		}else if ($tipo_contrato_id == 5){
			$url_solicitud = $host.'/?sec_id=contrato&sub_sec_id=detalle_solicitudv2_acuerdo_confidencialidad&id='.$contrato_id;
		}else if ($tipo_contrato_id == 6){
			$url_solicitud = $host.'/?sec_id=contrato&sub_sec_id=detalle_agente&id='.$contrato_id;
		}else if ($tipo_contrato_id == 7){
			$url_solicitud = $host.'/?sec_id=contrato&sub_sec_id=detalle_solicitudv2_interno&id='.$contrato_id;
		}

		$body .= '<div style="width: 700px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$url_solicitud.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
				$body .= '<b>Ver Solicitud</b>';
		    $body .= '</a>';
		$body .= '</div>';

		$body .= '</html>';
		$body .= "";

	if ($envio_correo == false){
		return false;
	}
	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));

	if ($tipo_contrato_id == 1){
		$lista_correos = $correos->send_email_modificacion_contrato_arrendamiento([]);
	}else if ($tipo_contrato_id == 2){
		$lista_correos = $correos->send_email_modificacion_contrato_proveedor([]);
	}else if ($tipo_contrato_id == 5){
		$lista_correos = $correos->send_email_modificacion_acuerdo_confidencialidad([]);
	}else if ($tipo_contrato_id == 6){
		$lista_correos = $correos->send_email_modificacion_contrato_agente([]);
	}else if ($tipo_contrato_id == 7){
		$lista_correos = $correos->send_email_modificacion_contrato_interno([]);
	}


	$cc = $lista_correos['cc'];

	if ($tipo_contrato_id == 1){
		$titulo_email = "Gestion - Sistema Contratos - Modificacion de Solicitud Contrato de Arrendamiento : Código - ";
	}else if($tipo_contrato_id == 2){
		$titulo_email = "Gestion - Sistema Contratos - Modificacion de Solicitud Contrato de Proveedor : Código - ";
	}else if($tipo_contrato_id == 5){
		$titulo_email = "Gestion - Sistema Contratos - Modificacion de Solicitud Acuerdo de confidencialidad : Código - ";
	}else if($tipo_contrato_id == 6){
		$titulo_email = "Gestion - Sistema Contratos - Modificacion de Solicitud Contrato de de Agente : Código - ";
	}else if($tipo_contrato_id == 7){
		$titulo_email = "Gestion - Sistema Contratos - Modificacion de Solicitud Contrato Interno : Código - ";
	}



	$bcc = $lista_correos['bcc'];

	$request = [
		"subject" => $titulo_email.$sigla_correlativo.$codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc)
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc)
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();
		//return true;

	}
	catch (Exception $e)
	{
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}


}


function send_email_solicitud_estado_no_aplica($contrato_id, $motivo_estado_na)
{
    include("db_connect.php");
    include("sys_login.php");

    $host = $_SERVER["HTTP_HOST"];

    $html = '';

    $sql_vc = "SELECT
    co.sigla AS sigla_correlativo,
    c.codigo_correlativo,
    tc.nombre as nombre_tipo_contrato,
    tc.ruta_detalle
    FROM cont_contrato c
    LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
    LEFT JOIN cont_tipo_contrato tc ON c.tipo_contrato_id = tc.id
    WHERE c.contrato_id = " . $contrato_id;
    $query_vc = $mysqli->query($sql_vc);
    while($sel_vc = $query_vc->fetch_assoc())
    {
        $sigla_correlativo = $sel_vc['sigla_correlativo'];
        $codigo_correlativo = $sel_vc['codigo_correlativo'];
        $nombre_tipo_contrato = $sel_vc["nombre_tipo_contrato"];
        $ruta_detalle = $sel_vc["ruta_detalle"];
        $contrato_id_de_la_obs = $contrato_id;

    }

    $sql = "SELECT
                o.id,
                o.contrato_id,
                o.observaciones,
                concat(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, '')) AS usuario_creacion,
                p.correo as correo_creacion,
                ar.id AS area_creacion_id,
                ar.nombre AS area_creacion,
                o.user_created_id,
                o.created_at,
                concat(IFNULL(pjc.nombre, ''),' ', IFNULL(pjc.apellido_paterno, '')) AS jefe_comercial,
                pjc.correo AS usuario_registro,
                co.sigla AS sigla_correlativo,
                c.codigo_correlativo,
                tc.nombre as nombre_tipo_contrato,
                tc.ruta_detalle
                FROM cont_observaciones o
                LEFT JOIN tbl_usuarios u ON o.user_created_id = u.id
                LEFT JOIN tbl_personal_apt p ON u.personal_id = p.id
                LEFT JOIN tbl_areas ar ON p.area_id = ar.id
                LEFT JOIN cont_contrato c ON o.contrato_id = c.contrato_id
                LEFT JOIN tbl_usuarios ujc ON c.user_created_id = ujc.id
                LEFT JOIN tbl_personal_apt pjc ON ujc.personal_id = pjc.id
                LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
                LEFT JOIN cont_tipo_contrato tc ON c.tipo_contrato_id = tc.id
            WHERE o.contrato_id = " . $contrato_id . " AND o.status = 1
            ORDER BY o.created_at ASC";


    $query = $mysqli->query($sql);
    $row_count = $query->num_rows;




    $body = "";
    $body .= '<html>';
    $body .= '<div>';

    $usuario_registro = '';
    $correo_del_usuario_creacion = '';

    while($sel = $query->fetch_assoc())
    {
        $sigla_correlativo = $sel['sigla_correlativo'];
        $codigo_correlativo = $sel['codigo_correlativo'];

        $usuario_registro = $sel["usuario_registro"];
        $correo_del_usuario_creacion = $sel["correo_creacion"];
        $area_creacion_id = $sel['area_creacion_id'];
        $contrato_id_de_la_obs = $sel["contrato_id"];
        $nombre_tipo_contrato = $sel["nombre_tipo_contrato"];
        $ruta_detalle = $sel["ruta_detalle"];

        if ($area_creacion_id == 21)
        {
            //EN PRODUCCION EL AREA OPERACIONES ES ID: 21
            // OPERACIONES

            $body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

            $body .= '<thead>';
            $body .= '<tr>';
            $body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
            $body .= '<b>Observaciones previas de la Solicitud</b>';
            $body .= '</th>';
            $body .= '</tr>';
            $body .= '</thead>';

            $body .= '<tr>';
                $body .= '<td style="background-color: #ffffdd; width: 160px;"><b>De:</b></td>';
                $body .= '<td>'.$sel["area_creacion"].' </td>';
            $body .= '</tr>';

            $body .= '<tr>';
                $body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Para:</b></td>';
                $body .= '<td>Legal</td>';
            $body .= '</tr>';



            $body .= '<tr>';
                $body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Fecha observacion:</b></td>';
                $body .= '<td>'.$sel["created_at"].'</td>';
            $body .= '</tr>';

            $body .= '<tr>';
                $body .= '<td style="background-color: #ffffdd"><b>Observacion:</b></td>';
                $body .= '<td>'.$sel["observaciones"].'</td>';
            $body .= '</tr>';


            $body .= '</table>';

        }
        else if($area_creacion_id == 33)
        {
            //EN PRODUCCION EL AREA LEGAL ES ID: 33
            //EN DEV EL AREA LEGAL ES ID: 33
            //LEGAL

            $body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

            $body .= '<thead>';
            $body .= '<tr>';
            $body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
            $body .= '<b>Observaciones previas de la Solicitud</b>';
            $body .= '</th>';
            $body .= '</tr>';
            $body .= '</thead>';

            $body .= '<tr>';
                $body .= '<td style="background-color: #ffffdd; width: 160px;"><b>De:</b></td>';
                $body .= '<td>'.$sel["area_creacion"].' </td>';
            $body .= '</tr>';

            $body .= '<tr>';
                $body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Para:</b></td>';
                $body .= '<td>Operaciones</td>';
            $body .= '</tr>';



            $body .= '<tr>';
                $body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Fecha observacion:</b></td>';
                $body .= '<td>'.$sel["created_at"].'</td>';
            $body .= '</tr>';

            $body .= '<tr>';
                $body .= '<td style="background-color: #ffffdd"><b>Observacion:</b></td>';
                $body .= '<td>'.$sel["observaciones"].'</td>';
            $body .= '</tr>';


            $body .= '</table>';

        }
        else
        {
            // CUANDO LOS USUARIOS NO FUERON CONFIGURADOS CORRECTAMENTE EN SUS AREAS RESPECTIVAS.

            $body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

            $body .= '<thead>';
            $body .= '<tr>';
            $body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
            $body .= '<b>Observaciones previas de la Solicitud</b>';
            $body .= '</th>';
            $body .= '</tr>';
            $body .= '</thead>';



            $body .= '<tr>';
                $body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Usuario quien observo:</b></td>';
                $body .= '<td>'.$sel["usuario_creacion"].' <strong>('.$sel["area_creacion"].')</strong></td>';
            $body .= '</tr>';

            $body .= '<tr>';
                $body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Fecha observacion:</b></td>';
                $body .= '<td>'.$sel["created_at"].'</td>';
            $body .= '</tr>';

            $body .= '<tr>';
                $body .= '<td style="background-color: #ffffdd"><b>Observacion:</b></td>';
                $body .= '<td>'.$sel["observaciones"].'</td>';
            $body .= '</tr>';


            $body .= '</table>';

        }


    }

    $body .= '</div>';

    $body .= '<div>';
        $body .= '<br>';
    $body .= '</div>';

    $body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';
    $body .= '<thead>';
    $body .= '<tr>';
    $body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
    $body .= '<b>No Aplica</b>';
    $body .= '</th>';
    $body .= '</tr>';
    $body .= '</thead>';
    $body .= '<tr>';
    $body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Motivo:</b></td>';
    $body .= '<td>'.$motivo_estado_na.' </td>';
    $body .= '</tr>';
    $body .= '</table>';


$body .= '<div>';
$body .= '<br>';
$body .= '</div>';

    $body .= '<div style="width: 500px; text-align: center; font-family: arial;">';

    $body .= '<a href="'.$host.'/?sec_id=contrato&sub_sec_id='.$ruta_detalle.'&id='.$contrato_id_de_la_obs.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
    $body .= '<b>Ver Solicitud</b>';
    $body .= '</a>';
    $body .= '</div>';

    $body .= '</html>';
    $body .= "";


    //lista de correos
    $correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
    $lista_correos = $correos->send_email_solicitud_no_aplica([$usuario_registro]);

    $cc = $lista_correos['cc'];
    $bcc = $lista_correos['bcc'];

    $correos_adjuntos = $correo_del_usuario_creacion;

    if($correos_adjuntos != ""){
        $validate = true;
        $emails = preg_split('[,|;]',$correos_adjuntos);
        foreach($emails as $e){
            if(preg_match('/^[_ña-z0-9-]+(\.[_ña-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/',trim($e)) == 0){
                $error_msg = "'" . $e . "'" ." no es un Correo Adjunto válido";
                $result["http_code"] = 500;
                $result["status"] = "error";
                $result["mensaje"] = $error_msg;
                echo json_encode($result);
                die;
            }
            else
            {
                $cc[] = $e;
            }
        }
    }

    $request = [
        "subject" => "Gestion - Sistema Contratos - Revisión Área Legal - Solicitud de " .$nombre_tipo_contrato." - No Aplica: Código - " .$sigla_correlativo.$codigo_correlativo,
        //"subject" => "Solicitud de proveedor",
        //"subject" => "Solicitud de adenda",
        "body"    => $body,
        "cc"      => $cc,
        "bcc"     => $bcc,
        "attach"  => [
            // $filepath . $file,
        ],
    ];

    $mail = new PHPMailer(true);

    try
    {
        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;


        $mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
        $mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
        $mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

        if(isset($request["cc"]))
        {
            foreach ($request["cc"] as $cc)
            {
                $mail->addAddress($cc);
            }
        }

        if(isset($request["bcc"]))
        {
            foreach ($request["bcc"] as $bcc)
            {
                $mail->addBCC($bcc);
            }
        }

        $mail->isHTML(true);
        $mail->Subject  = $request["subject"];
        $mail->Body     = $request["body"];
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';

        $mail->send();
        //return true;

    }
    catch (Exception $e)
    {
        $resultado = $mail->ErrorInfo;
        //echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        //return false;
        echo json_encode($resultado);
    }



}

function sec_contrato_detalle_solicitudv2_formato_nombre_de_tienda($nombre_de_tienda)
{
	$nombre_de_la_tienda = preg_replace(['/\s+/', '/^\s|\s$/'], [' ', ''], trim($nombre_de_tienda));
	$nombre_de_la_tienda = mb_convert_case(mb_strtolower($nombre_de_la_tienda), MB_CASE_TITLE, "UTF-8");
	$primeras_7_letras   = mb_substr($nombre_de_la_tienda, 0, 7);
	if ($primeras_7_letras != "Red At ") {
		$primeras_3_letras = mb_substr($nombre_de_la_tienda, 0, 3);
		if ($primeras_3_letras == "At ") {
			$nombre_de_la_tienda = "Red " . $nombre_de_la_tienda;
		} else {
			$nombre_de_la_tienda = "Red AT " . $nombre_de_la_tienda;
		}
	}
	$nombre_de_la_tienda = str_replace(" At ", " AT ", $nombre_de_la_tienda);
	$nombre_de_la_tienda = str_replace(" De La ", " de la ", $nombre_de_la_tienda);
	$nombre_de_la_tienda = str_replace(" De ", " de ", $nombre_de_la_tienda);
	$nombre_de_la_tienda = str_replace(" Del ", " del ", $nombre_de_la_tienda);
	$nombre_de_la_tienda = str_replace(" Y ", " y ", $nombre_de_la_tienda);
	return $nombre_de_la_tienda;
}

if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_autorizacion_municipal")
{
	$usuario_id = $login["id"];
	if ((int) $usuario_id > 0) {
		$name_file = "";
		$tmp_name_file = "";
		$size_file = "NULL";
		$extension_file = "";
		$path = "";
		$file_name_upload = "";
		$name_download = "";

		if(!empty($_FILES['archivo_autorizacion_municipal']['name'])) {
			$name_file = $_FILES['archivo_autorizacion_municipal']['name'];
			$tmp_name_file = $_FILES['archivo_autorizacion_municipal']['tmp_name'];
			$size_file = $_FILES['archivo_autorizacion_municipal']['size'];
			$extension_file = strtolower(pathinfo($name_file, PATHINFO_EXTENSION));

			$valid_extensions = array('jpeg', 'jpg', 'png', 'pdf');

			if(!in_array($extension_file, $valid_extensions)) {
				$result["http_code"] = 400;
				$result["error"] ="La extension del file no es aceptado, solo son aceptado: jpeg, jpg, png, pdf";
				echo json_encode($result);
				exit();
			}
		}

		$fecha_actual = "'" . date('Y-m-d H:i:s') . "'";
		$autorizacion_municipal_id = trim($_POST["autorizacion_municipal_id"]);
		$contrato_id = $_POST["contrato_id"];
		$giro_id = $_POST["giro_id"];
		$estado_id = $_POST["estado_id"];
		$condicion_id = $_POST["condicion_id"];
		$fecha_vencimiento = "NULL";
		$fecha_renovacion = "NULL";

		if ($estado_id == "CONCLUIDO") {
			if($condicion_id == "TEMPORAL") {
				$fecha_vencimiento = $_POST["fecha_vencimiento"];
				$fecha_vencimiento = "'" . date("Y-m-d", strtotime($fecha_vencimiento)) . "'";
				$fecha_renovacion = $_POST["fecha_renovacion"];
				$fecha_renovacion = "'" . date("Y-m-d", strtotime($fecha_renovacion)) . "'";
			}

			$condicion_id = "'" . $condicion_id . "'";
		} else {
			$condicion_id = "NULL";
		}

		if ($autorizacion_municipal_id == "7") {
			$query_update = "UPDATE cont_contrato SET declaracion_jurada_id = '$giro_id' WHERE contrato_id = '$contrato_id'";
			$mysqli->query($query_update);
			if($mysqli->error) {
				$result["http_code"] = 400;
				$result["error"] = "Error al asignar giro al contrato: " . $mysqli->error;
				echo json_encode($result);
				exit();
			}
		}

		if(!empty($_FILES['archivo_autorizacion_municipal']['name'])) {
			if ($autorizacion_municipal_id == "7") {
				$nombre_carpeta = 'dj';
			} elseif ($autorizacion_municipal_id == "6"){
				$nombre_carpeta = 'publicidad';
			} elseif ($autorizacion_municipal_id == "5"){
				$nombre_carpeta = 'indeci';
			} elseif ($autorizacion_municipal_id == "4"){
				$nombre_carpeta = 'funcionamiento';
			} else {
				$result["http_code"] = 400;
				$result["error"] = "No se pudo guardar el archivo. Error: tipo_archivo_id nuevo, agregar la ruta de la carpeta en el codigo.";
				echo json_encode($result);
				exit();
			}

			$path = "/var/www/html/files_bucket/contratos/licencias_municipales/" . $nombre_carpeta . "/";
			$download = "/files_bucket/contratos/licencias_municipales/" . $nombre_carpeta . "/";

			$file_name_upload = $nombre_carpeta . "-" . date('YmdHis') . "." . $extension_file;
			$name_download = $download . $file_name_upload;
			$uploaded = move_uploaded_file($tmp_name_file, $path . $file_name_upload);

			if(!$uploaded){
				$result["http_code"] = 400;
				$result["error"] = "Nos se pudo guardar el archivo en la siguiente ruta del servidor: " . $download;
				echo json_encode($result);
				exit();
			}
		}

		$query_update = "UPDATE cont_licencia_municipales SET estado = 0 WHERE contrato_id = '$contrato_id' AND tipo_archivo_id = $autorizacion_municipal_id";
		$mysqli->query($query_update);

		if($mysqli->error) {
			$result["http_code"] = 400;
			$result["error"] = "Error al actualizar el estado de las licencias antiguas: " . $mysqli->error;
			echo json_encode($result);
			exit();
		}

		$query_insert = "
		INSERT INTO cont_licencia_municipales (
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
			created_at
		) VALUES (
			$contrato_id,
			$autorizacion_municipal_id,
			'$estado_id',
			$condicion_id,
			$fecha_vencimiento,
			$fecha_renovacion,
			'$file_name_upload',
			'$extension_file',
			$size_file,
			'$path',
			'$name_download',
			1,
			$usuario_id,
			$fecha_actual
		)";

		$mysqli->query($query_insert);

		if($mysqli->error) {
			$result["http_code"] = 400;
			$result["error"] = "Error al registrar la licencias: " . $mysqli->error;
			echo json_encode($result);
			exit();
		}

		$result["http_code"] = 200;

	} else {
		$result["http_code"] = 400;
		$result["error"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y volver a hacer clic en el botón: Agregar.";
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="actualizar_tabla_autorizaciones_municipales") {

	$contrato_id = $_POST["contrato_id"];
	$autorizacion_municipal_id = $_POST["autorizacion_municipal_id"];

	if ($autorizacion_municipal_id == "7") {
		$nombre_carpeta = 'dj';
		$nombre_autorización = 'Declaración Jurada de Actividades Simultáneas';
	} elseif ($autorizacion_municipal_id == "6"){
		$nombre_carpeta = 'publicidad';
		$nombre_autorización = 'Autorización de Anuncio Publicitario';
	} elseif ($autorizacion_municipal_id == "5"){
		$nombre_carpeta = 'indeci';
		$nombre_autorización = 'Certificado de Indeci';
	} elseif ($autorizacion_municipal_id == "4"){
		$nombre_carpeta = 'funcionamiento';
		$nombre_autorización = 'Licencia de Funcionamiento';
	}

	$body = '';

	if ($autorizacion_municipal_id == "7") {
		$sql_autorizaciones_municipales = "
		SELECT
		lm.nombre_file,
		lm.extension,
		lm.created_at,
		dj.nombre as declaracion_jurada
		FROM
		cont_licencia_municipales lm
		INNER JOIN cont_tipo_archivos ta ON lm.tipo_archivo_id = ta.tipo_archivo_id
		INNER JOIN cont_contrato c ON lm.contrato_id = c.contrato_id
		LEFT JOIN cont_declaracion_jurada dj ON c.declaracion_jurada_id = dj.id
		WHERE
		lm.contrato_id = '$contrato_id'
		AND lm.tipo_archivo_id = '$autorizacion_municipal_id'
		AND lm.estado = 1
		ORDER BY lm.id DESC
		";
	} else {
		$sql_autorizaciones_municipales = "
		SELECT
			lm.nombre_file,
			lm.status_licencia,
			lm.condicion,
			lm.fecha_vencimiento,
			lm.fecha_renovacion,
			lm.extension,
			lm.estado,
			lm.created_at
		FROM
			cont_licencia_municipales lm
			INNER JOIN cont_tipo_archivos ta ON lm.tipo_archivo_id = ta.tipo_archivo_id
		WHERE
			lm.contrato_id = '$contrato_id'
			AND lm.tipo_archivo_id = '$autorizacion_municipal_id'
		ORDER BY lm.id DESC
		";
	}

	$resultado = $mysqli->query($sql_autorizaciones_municipales);

	if($mysqli->error){
		$body .= '<tr><td colspan="5">Se genero el error: '. $mysqli->error .'  en la siguiente consulta: ' . $sql_autorizaciones_municipales . '</td></tr>';
	} else {
		$num_registros = $resultado->num_rows;

		if ($num_registros == 0) {
			$body .= '<tr><td colspan="5" class="text-center">La licencia de funcionamiento todavía no se carga al sistema.</td></tr>';
		} else if ($num_registros > 0) {
			while ($fila = $resultado->fetch_assoc()) {
				if ($autorizacion_municipal_id == "7") {
					$otros_detalles = 'Registrado el: ' . $fila["created_at"];
					$body .= '<tr>';
					$body .= '<td>' . $fila["declaracion_jurada"] . '</td>';
					$body .= '<td>';
					$body .= '<a class="btn btn-rounded btn-primary btn-xs" onclick="sec_contrato_detalle_solicitudv2_ver_documento_en_visor(\'/files_bucket/contratos/licencias_municipales/DJ/\',\'' . $fila["nombre_file"] . '\', \'' . $fila["extension"] . '\' , \'Declaración Jurada de Actividades Simultáneas\');" data-toggle="tooltip" data-placement="left" title="' . $otros_detalles . '">';
					$body .= '<i class="fa fa-eye"></i>';
					$body .= '</a>';
					$body .= '</td>';
					$body .= '</tr>';
				} else {
					$vigente = $fila["estado"] == 1 ? 'SuyI' : 'NO';
					$fecha_de_renovacion = trim($fila["fecha_renovacion"]) == "" ? '' : ' | Fecha de renovación: ' . $fila["fecha_renovacion"];
					$otros_detalles = 'Registrado el: ' . $fila["created_at"] . $fecha_de_renovacion;
					$body .= '<tr>';
					$body .= '<td>' . ucfirst(strtolower($fila["status_licencia"])) . '</td>';
					$body .= '<td>' . ucfirst(strtolower($fila["condicion"])) . '</td>';
					$body .= '<td>' . $fila["fecha_vencimiento"] . '</td>';
					$body .= '<td>' . $vigente . '</td>';
					$body .= '<td>';

					if ($fila["status_licencia"] == "CONCLUIDO") {
						$body .= '<a class="btn btn-rounded btn-primary btn-xs" onclick="sec_contrato_detalle_solicitudv2_ver_documento_en_visor(\'/files_bucket/contratos/licencias_municipales/' . $nombre_carpeta . '/\',\'' . $fila["nombre_file"] . '\', \'' . $fila["extension"] . '\' , \'' . $nombre_autorización . '\');" data-toggle="tooltip" data-placement="left" title="' . $otros_detalles . '">';
					} else {
						$body .= '<a class="btn btn-rounded btn-default btn-xs" data-toggle="tooltip" data-placement="left" title="Sin documento | ' . $otros_detalles . '">';
					}

					$body .= '<i class="fa fa-eye"></i>';
					$body .= '</a>';
					$body .= '</td>';
					$body .= '</tr>';
				}

			}
		}
	}

	$result["http_code"] = 200;
	$result["body"] = $body;
}

if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_resolucion_contrato_firmado") {
	$usuario_id = $login?$login['id']:null;
	if ((int) $usuario_id > 0) {

		$contrato_id = $_POST["contrato_id"];
		$tipo_contrato_id = $_POST["resolucion_tipo_contrato_id"];
		$resolucion_contrato_id = $_POST["resolucion_contrato_id"];
		$fecha_resolucion = str_replace("/","-",$_POST["fecha_resolucion"]);
		$fecha_resolucion = date("Y-m-d", strtotime($fecha_resolucion));
		$created_at = date("Y-m-d H:i:s");
		$error = '';
		$archivo_id = 0;
		$contrato_detalle_id = 0;
		

		// $query_select = "SELECT * FROM cont_resolucion_contrato WHERE id = ".$resolucion_contrato_id;
		// $sel_query = $mysqli->query($query_select);
		// $resol = $sel_query->fetch_assoc();

		// if (empty($resol['abogado_id'])) {
		// 	$result["http_code"] = 400;
		// 	$result["error"] = 'sin_asignar';
		// 	$result["campo_incompleto"] = 'abogado';
		// 	exit(json_encode($result));
		// }

		if(isset($resol['contrato_detalle_id']) && !Empty($resol['contrato_detalle_id'])){
			$contrato_detalle_id = $resol['contrato_detalle_id'];
		}

		$path = "";
		switch ($tipo_contrato_id) {
			case '1': $path = "/var/www/html/files_bucket/contratos/resolucion_contratos_firmados/locales/"; break;
			case '2': $path = "/var/www/html/files_bucket/contratos/resolucion_contratos_firmados/proveedores/"; break;
			case '5': $path = "/var/www/html/files_bucket/contratos/resolucion_contratos_firmados/acuerdo_confidencialidad/"; break;
			case '6': $path = "/var/www/html/files_bucket/contratos/resolucion_contratos_firmados/agentes/";break;
			case '7': $path = "/var/www/html/files_bucket/contratos/resolucion_contratos_firmados/contratos_internos/"; break;
		}

		$nombre_file = "archivo_resolucion_contrato";
		if ($tipo_contrato_id == 1){
			$nombre_file = "archivo_resolucion_contrato_".$resolucion_contrato_id;
		}

		if (isset($_FILES[$nombre_file]) && $_FILES[$nombre_file]['error'] === UPLOAD_ERR_OK) {
			if (!is_dir($path)) mkdir($path, 0777, true);

			$filename = $_FILES[$nombre_file]['name'];
			$filenametem = $_FILES[$nombre_file]['tmp_name'];
			$filesize = $_FILES[$nombre_file]['size'];
			$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
			if($filename != ""){
				$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
				$nombre_archivo = $contrato_id . "_RESOLUCION_CONTRATO_FIRMADO_" . date('YmdHis') . "." . $fileExt;
				move_uploaded_file($filenametem, $path . $nombre_archivo);
				$comando = "INSERT INTO cont_archivos (
								contrato_id,
								contrato_detalle_id,
								resolucion_contrato_id,
								tipo_archivo_id,
								nombre,
								extension,
								size,
								ruta,
								user_created_id,
								created_at)
							VALUES(
								'" . $contrato_id . "',
								'" . $contrato_detalle_id . "',
								'" . $resolucion_contrato_id . "',
								105,
								'" . $nombre_archivo . "',
								'" . $fileExt . "',
								'" . $filesize . "',
								'" . $path . "',
								" . $usuario_id . ",
								'" . $created_at . "'
								)";
				$mysqli->query($comando);
				$archivo_id = mysqli_insert_id($mysqli);
				if($mysqli->error){
					$error .= 'Error al guardar el archivo: ' . $mysqli->error . $comando;
					$result["http_code"] = 404;
					$result["error"] = $error;
					echo json_encode($result);
					exit();
				}
			}
		}

		$query_update_resolucion = "
		UPDATE
			cont_resolucion_contrato
		SET
			usuario_resolucion_contrato_aprobado_id = $usuario_id,
			fecha_resolucion_contrato_aprobado = '".$created_at."',
			fecha_resolucion = '".$fecha_resolucion."',
			archivo_id = '".$archivo_id."',
			estado_solicitud_id = 2
		WHERE
			id = $resolucion_contrato_id
		";

		$mysqli->query($query_update_resolucion);


		if($mysqli->error){
			$error .= 'Error al guardar la resolucion del contrato: ' . $mysqli->error . $query_update_resolucion;
			$result["http_code"] = 404;
			$result["error"] = $error;
			echo json_encode($result);
			exit();
		}


		if ($tipo_contrato_id == 1){ // contrato de arrendamiento

			$query_update_contrato_detalle = "
			UPDATE
			cont_contrato_detalle
			SET
				estado_resolucion = 2
			WHERE
				id = $contrato_detalle_id
			";
			$mysqli->query($query_update_contrato_detalle);
			if($mysqli->error){
				$error .= 'Error al guardar resolucion del contrato: ' . $mysqli->error . $query_update_contrato_detalle;
				$result["http_code"] = 404;
				$result["error"] = $error;
				echo json_encode($result);
				exit();
			}


			$query_select = "SELECT * FROM cont_contrato_detalle WHERE contrato_id = ".$contrato_id." AND ( estado_resolucion = 0 OR estado_resolucion = 4)";
			$sel_query_detalle = $mysqli->query($query_select);
			if ($sel_query_detalle->num_rows == 0) {
				$query_update_contrato = "
				UPDATE
					cont_contrato
				SET
					estado_resolucion = 2
				WHERE
					contrato_id = $contrato_id
				";
				$mysqli->query($query_update_contrato);
		
				if($mysqli->error){
					$error .= 'Error al guardar resolucion del contrato: ' . $mysqli->error . $query_update_contrato;
					$result["http_code"] = 404;
					$result["error"] = $error;
					echo json_encode($result);
					exit();
				}
			}

			
		}else{
			$query_update_contrato = "
			UPDATE
				cont_contrato
			SET
				estado_resolucion = 2
			WHERE
				contrato_id = $contrato_id
			";
	
			$mysqli->query($query_update_contrato);



	
			if($mysqli->error){
				$error .= 'Error al guardar resolucion del contrato: ' . $mysqli->error . $query_update_contrato;
				$result["http_code"] = 404;
				$result["error"] = $error;
				echo json_encode($result);
				exit();
			}
		}



		// if ($tipo_contrato_id == 2) {//Resolucion de contrato de proveedores
		// 	//INICIO REGISTRAR SEGUIMIENTO DE PROCESO CUANDO LA ADENDA HAYA SIDO SUBIDO POR UN USUARIO DE LEGAL
		// 	$RESOLUCION_DE_CONTRATO = 3;
		// 	$query_seguimiento = "SELECT id FROM cont_seguimiento_proceso_legal WHERE tipo_documento_id = ".$RESOLUCION_DE_CONTRATO." AND proceso_id = ".$resolucion_contrato_id." AND status IN (1,2)";
		// 	$list_query_seguimiento = $mysqli->query($query_seguimiento);
		// 	$cant_seguimientos = $list_query_seguimiento->num_rows;


		// 	$query_adenda = "SELECT cc.area_responsable_id, cc.tipo_contrato_id , tpa.area_id
		// 	FROM cont_resolucion_contrato crc 
		// 	INNER JOIN cont_contrato cc ON cc.contrato_id = crc.contrato_id 
		// 	LEFT JOIN tbl_usuarios tu ON
		// 		tu.id = crc.user_created_id 
		// 	LEFT JOIN tbl_personal_apt tpa ON
		// 		tpa.id = tu.personal_id
		// 	WHERE crc.id  = ".$resolucion_contrato_id." LIMIT 1";
		// 	$sel_adenda = $mysqli->query($query_adenda);
		// 	$data_resolucion = $sel_adenda->fetch_assoc();

		// 	$APROBACION_DEL_DOCUMENTO = 1;
		// 	$INICIO_DE_PROCESO_LEGAL = 2;
		// 	$AREA_LEGAL_ID = 33;
		// 	$RESOLUCION_DE_CONTRATO = 3;

		// 	if ($cant_seguimientos == 0 || $data_resolucion['area_id'] == $AREA_LEGAL_ID) {
		// 		$seg_proceso = new SeguimientoProceso();
		// 		if ($cant_seguimientos == 0) {
		// 			$data_proceso_reg_ipc['tipo_documento_id'] = $RESOLUCION_DE_CONTRATO;
		// 			$data_proceso_reg_ipc['proceso_id'] = $resolucion_contrato_id;
		// 			$data_proceso_reg_ipc['proceso_detalle_id'] = 0;
		// 			$data_proceso_reg_ipc['area_id'] = $AREA_LEGAL_ID;
		// 			$data_proceso_reg_ipc['etapa_id'] = $INICIO_DE_PROCESO_LEGAL;
		// 			$data_proceso_reg_ipc['status'] = 1; //Pendiente
		// 			$data_proceso_reg_ipc['created_at'] = date('Y-m-d H:i:s');
		// 			$data_proceso_reg_ipc['user_created_id'] = $usuario_id;
		// 			$result_reg_conf_user = $seg_proceso->registrar_proceso($data_proceso_reg_ipc);
		// 		}
				
		// 		$data_proceso['tipo_documento_id'] = $RESOLUCION_DE_CONTRATO; //Adenda
		// 		$data_proceso['proceso_id'] = $resolucion_contrato_id;
		// 		$data_proceso['proceso_detalle_id'] = 0;
		// 		$data_proceso['usuario_id'] = $usuario_id;
		// 		$resp_proceso = $seg_proceso->fin_seguimiento_proceso_alterno($data_proceso);
		// 	}
		// 	//FIN REGISTRAR SEGUIMIENTO DE PROCESO CUANDO LA ADENDA HAYA SIDO SUBIDO POR UN USUARIO DE LEGAL
		// }

		send_email_solicitud_resolucion_contrato_firmado($resolucion_contrato_id, false);

		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestión.";
		$result["error"] = $error;
		echo json_encode($result);
		exit();

	} else {
		$result["http_code"] = 400;
		$result["status"] = "Datos obtenidos de gestión.";
		$result["error"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y volver a hacer clic en el botón: Agregar contrato firmado.";
		echo json_encode($result);
		exit();
	}
}

function send_email_solicitud_resolucion_contrato_firmado($resolucion_id, $reenvio = false)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];


	$sel_query = $mysqli->query("
	SELECT r.id, r.contrato_id, c.tipo_contrato_id, r.motivo, r.fecha_solicitud, 
	DATE_FORMAT(r.fecha_resolucion,'%d-%m-%Y') AS fecha_resolucion, 
	DATE_FORMAT(r.fecha_carta,'%d-%m-%Y') AS fecha_carta,
	CONCAT(IFNULL(tpa.nombre, ''),' ',IFNULL(tpa.apellido_paterno, ''),	' ',	IFNULL(tpa.apellido_materno, '')) AS usuario_solicitud,
	r.anexo_archivo_id,
	r.archivo_id,
	CONCAT(IFNULL(tpa2.nombre, ''),' ',IFNULL(tpa2.apellido_paterno, ''),	' ',	IFNULL(tpa2.apellido_materno, '')) AS usuario_aprobado,
	CONCAT(IFNULL(pab.nombre, ''),' ',IFNULL(pab.apellido_paterno, ''),	' ',	IFNULL(pab.apellido_materno, '')) AS abogado,
	r.fecha_resolucion_contrato_aprobado,
	r.status,
	DATE_FORMAT(r.created_at,'%d-%m-%Y %H:%i:%s') AS created_at,
	co.sigla, c.codigo_correlativo, tpa.correo, ar.nombre AS nombre_area, d.codigo,

	per.correo AS usuario_creacion_correo,
	c.gerente_area_email as email_del_gerente_area_2,
	peg.correo AS email_del_gerente_area,
	pab.correo AS correo_abogado,
	puap.correo AS email_del_aprobante


	FROM cont_resolucion_contrato AS r
	INNER JOIN tbl_usuarios tu ON r.user_created_id = tu.id
	INNER JOIN tbl_personal_apt tpa ON tu.personal_id = tpa.id
	INNER JOIN tbl_areas AS ar ON tpa.area_id = ar.id

	LEFT JOIN tbl_usuarios tu2 ON r.usuario_resolucion_contrato_aprobado_id = tu2.id
	LEFT JOIN tbl_personal_apt tpa2 ON tu2.personal_id = tpa2.id

	INNER JOIN cont_contrato AS c ON c.contrato_id = r.contrato_id
	LEFT JOIN cont_contrato_detalle AS d ON d.id = r.contrato_detalle_id
	LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato

	LEFT JOIN tbl_usuarios u ON c.user_created_id = u.id
	LEFT JOIN tbl_personal_apt per ON u.personal_id = per.id

	LEFT JOIN tbl_usuarios uab ON r.abogado_id = uab.id
	LEFT JOIN tbl_personal_apt pab ON uab.personal_id = pab.id

	LEFT JOIN tbl_usuarios ug ON c.gerente_area_id = ug.id
	LEFT JOIN tbl_personal_apt peg ON ug.personal_id = peg.id

	LEFT JOIN tbl_usuarios uap ON c.aprobado_por = uap.id
	LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id



	WHERE r.id = ".$resolucion_id);



	$body = "";
	$body .= '<html>';

	$usuario_creacion_correo = '';
	$contrato_id = 0;
	$tipo_contrato_id = 0;
	$url_solicitud = "";
	$correos_adicionales = [];
	while($sel = $sel_query->fetch_assoc())
	{
		$contrato_id = $sel['contrato_id'];
		$tipo_contrato_id = $sel['tipo_contrato_id'];
		$sigla_correlativo = $sel['sigla'];
		$codigo_correlativo = $sel['codigo_correlativo'];
		$codigo = $sel['codigo'];
		

		if(!Empty($sel['correo'])){
			array_push($correos_adicionales, trim($sel['correo'])); //Correo del Aprobante
		}

		if(!Empty($sel['correo_abogado'])){
			array_push($correos_adicionales, trim($sel['correo_abogado'])); //Correo del Aprobante
		}

		if((int) $sel['tipo_contrato_id'] == 2){
			if(!Empty($sel['email_del_aprobante'])){
				array_push($correos_adicionales, trim($sel['email_del_aprobante'])); 
			}
			if(!Empty($sel['usuario_creacion_correo'])){
				array_push($correos_adicionales, trim($sel['usuario_creacion_correo'])); 
			}
			if(!Empty($sel['email_del_gerente_area_2'])){
				array_push($correos_adicionales, trim($sel['email_del_gerente_area_2'])); 
			}
			if(!Empty($sel['email_del_gerente_area'])){
				array_push($correos_adicionales, trim($sel['email_del_gerente_area'])); 
			}
		}
		

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

		$body .= '<thead>';

		$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>Nueva solicitud</b>';
			$body .= '</th>';
		$body .= '</tr>';


		$body .= '</thead>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Area Solicitante:</b></td>';
			$body .= '<td>'.$sel["nombre_area"].'</td>';
		$body .= '</tr>';

		if (!Empty($sel["codigo"])){
			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Código Contrato:</b></td>';
			$body .= '<td>'.$sel["codigo"].'</td>';
			$body .= '</tr>';
		}

		if (!Empty($sel["fecha_carta"])){
			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd"><b>Fecha de Carta:</b></td>';
				$body .= '<td>'.$sel["fecha_carta"].'</td>';
			$body .= '</tr>';
		}

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha de Resolución:</b></td>';
			$body .= '<td>'.$sel["fecha_resolucion"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Motivo:</b></td>';
			$body .= '<td>'.$sel["motivo"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Usuario solicitante:</b></td>';
			$body .= '<td>'.$sel["usuario_solicitud"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha Solicitud:</b></td>';
			$body .= '<td>'.$sel["created_at"].'</td>';
		$body .= '</tr>';

		$body .= '</table>';
		$body .= '</div>';

	}
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		switch ($tipo_contrato_id) {
			case '1': $url_solicitud = "detalle_solicitudv2"; break;
			case '2': $url_solicitud = "detalleSolicitudProveedor"; break;
			case '5': $url_solicitud = "detalle_solicitudv2_acuerdo_confidencialidad"; break;
			case '6': $url_solicitud = "detalle_agente"; break;
			case '7': $url_solicitud = "detalle_solicitudv2_interno"; break;
		}

		$body .= '<div style="width: 700px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$host.'/?sec_id=contrato&sub_sec_id='.$url_solicitud.'&id='.$contrato_id.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
				$body .= '<b>Ver Solicitud</b>';
		    $body .= '</a>';
		$body .= '</div>';

		$body .= '</html>';
		$body .= "";

	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_solicitud_resolucion_contrato_firmado($correos_adicionales);

	$cc = $lista_correos['cc'];
	$nombre_tipo_contrato = "";
	switch ($tipo_contrato_id) {
		case '1': $nombre_tipo_contrato = "Contrato de Arrendamiento"; break;
		case '2': $nombre_tipo_contrato = "Contrato de Proveedor"; break;
		case '5': $nombre_tipo_contrato = "Contrato de Acuerdo de Confidencialidad"; break;
		case '6': $nombre_tipo_contrato = "Contrato de Agente"; break;
		case '7': $nombre_tipo_contrato = "Contrato Interno"; break;
	}
	$titulo_email = "Gestion - Sistema Contratos - Solicitud de Resolución de ".$nombre_tipo_contrato." Firmada: Código - ";
	if($reenvio){
		$titulo_email = "Gestion - Sistema Contratos - Reenvío Solicitud de Resolución de ".$nombre_tipo_contrato." Firmada: Código - ";
	}
	$bcc = $lista_correos['bcc'];

	$request = [
		"subject" => $titulo_email.$sigla_correlativo.$codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc)
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc)
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();
		//return true;

	}
	catch (Exception $e)
	{
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}


}


// INICIO CONTRAPRESTACIÓN
if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_contraprestacion")
{
	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	$contrato_id = $_POST["contrato_id"];
	$moneda_id = $_POST["moneda_id"];
	$forma_pago = 0;
	$forma_pago_detallado = "'" . replace_invalid_caracters(trim($_POST["forma_pago_detallado"])) . "'";
	$tipo_comprobante = $_POST["tipo_comprobante"];
	$plazo_pago = "'" . replace_invalid_caracters(trim($_POST["plazo_pago"])) . "'";

	if (empty($_POST["subtotal"])) {
		$subtotal = 0;
	} else {
		$subtotal = str_replace(",","",$_POST["subtotal"]);
	}

	if (empty($_POST["igv"])) {
		$igv = 0;
	} else {
		$igv = str_replace(",","",$_POST["igv"]);
	}

	$monto = $subtotal + $igv;

	$query_insert = "INSERT INTO cont_contraprestacion
	(
	contrato_id,
	moneda_id,
	forma_pago_id,
	forma_pago_detallado,
	tipo_comprobante_id,
	plazo_pago,
	subtotal,
	igv,
	monto,
	user_created_id,
	created_at)
	VALUES
	(
	" . $contrato_id . ",
	" . $moneda_id . ",
	" . $forma_pago . ",
	" . $forma_pago_detallado . ",
	" . $tipo_comprobante . ",
	" . $plazo_pago . ",
	" . $subtotal . ",
	" . $igv . ",
	" . $monto . ",
	" . $usuario_id . ",
	'" . $created_at . "')";

	$mysqli->query($query_insert);
	$insert_id = mysqli_insert_id($mysqli);
	$error = '';
	if($mysqli->error){
		$error=$mysqli->error;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $insert_id;
	$result["error"] = $error;
}

if (isset($_POST["accion"]) && $_POST["accion"]==="cancelar_solicitud")
{
	$usuario_id = $login?$login['id']:null;

	if ( !((int) $usuario_id > 0) ) {
		$error = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y hacer click e el boton: Cancelar Solicitud.";
		sec_contrato_detalle_solicitudv2_enviar_error($error);
	}

	$contrato_id = trim($_POST["contrato_id"]);

	if ( empty($contrato_id) ) {
		$error = "contrato_id invalido";
		sec_contrato_detalle_solicitudv2_enviar_error($error);
	}

	$created_at = date('Y-m-d H:i:s');

	$cancelado_motivo = replace_invalid_caracters( trim($_POST["cancelado_motivo"]) );

	$query_update = "
	UPDATE cont_contrato
	SET
		cancelado_id = 1,
		cancelado_por_id = $usuario_id,
		cancelado_el = '$created_at',
		cancelado_motivo = '$cancelado_motivo'
	WHERE contrato_id = $contrato_id";

	$mysqli->query($query_update);

	if($mysqli->error){
		sec_contrato_detalle_solicitudv2_enviar_error($mysqli->error . $query_update);
	}

	send_email_cancelar_solicitud($contrato_id, true);
}

function send_email_cancelar_solicitud($contrato_id)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$query_solicitud_cancelada = "
	SELECT
		c.tipo_contrato_id,
		CONCAT(IFNULL(tpa.nombre, ''),' ',IFNULL(tpa.apellido_paterno, ''),	' ',	IFNULL(tpa.apellido_materno, '')) AS cancelado_por,
		c.cancelado_el,
		c.cancelado_motivo,
		co.sigla,
		c.codigo_correlativo,
		tpa.correo AS correo_cancelador,
		tpa2.correo AS correo_aprobador,
		tpa3.correo AS correo_gerente,
		tpa4.correo AS correo_director,
		c.gerente_area_email,
		c.fecha_atencion_gerencia_proveedor,
		c.aprobacion_gerencia_proveedor,
		c.check_gerencia_proveedor
	FROM
		cont_contrato AS c
		LEFT JOIN tbl_usuarios tu ON c.cancelado_por_id = tu.id
		LEFT JOIN tbl_personal_apt tpa ON tu.personal_id = tpa.id
		LEFT JOIN tbl_usuarios tu2 ON c.aprobado_por = tu2.id
		LEFT JOIN tbl_personal_apt tpa2 ON tu2.personal_id = tpa2.id
		LEFT JOIN tbl_usuarios tu3 ON c.gerente_area_id = tu3.id
		LEFT JOIN tbl_personal_apt tpa3 ON tu3.personal_id = tpa3.id
		LEFT JOIN tbl_usuarios tu4 ON c.director_aprobacion_id = tu4.id
		LEFT JOIN tbl_personal_apt tpa4 ON tu4.personal_id = tpa4.id
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
	WHERE
		c.contrato_id = $contrato_id
	";

	$sel_query = $mysqli->query($query_solicitud_cancelada);

	if($mysqli->error){
		sec_contrato_detalle_solicitudv2_enviar_error($mysqli->error . $query_solicitud_cancelada);
	}

	if (!($sel_query->num_rows > 0)) {
		$error = "Al enviar email de confirmacion el num_rows is 0.";
		sec_contrato_detalle_solicitudv2_enviar_error($error);
	}

	$body = "";
	$body .= '<html>';

	$tipo_contrato_id = 0;
	$url_solicitud = "";
	$fecha_atencion_gerencia_proveedor = "";
	$aprobacion_gerencia_proveedor = "";
	$correos_add = [];

	while($sel = $sel_query->fetch_assoc())
	{
		$tipo_contrato_id = $sel['tipo_contrato_id'];
		$cancelado_por = $sel['cancelado_por'];
		$cancelado_el = $sel['cancelado_el'];
		$cancelado_motivo = $sel['cancelado_motivo'];
		$sigla_correlativo = $sel['sigla'];
		$codigo_correlativo = $sel['codigo_correlativo'];
		$correo_cancelador = trim($sel['correo_cancelador']);
		$correo_aprobador = trim($sel['correo_aprobador']);
		$correo_director = trim($sel['correo_director']);
		$correo_gerente = trim($sel['correo_gerente']);
		$correo_gerente_2 = $sel['gerente_area_email'];

		$fecha_atencion_gerencia_proveedor = $sel['fecha_atencion_gerencia_proveedor'];
		$aprobacion_gerencia_proveedor = $sel['aprobacion_gerencia_proveedor'];
		$check_gerencia_proveedor = $sel['check_gerencia_proveedor'];

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

		$body .= '<thead>';

		$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>Solicitud Cancelada</b>';
			$body .= '</th>';
		$body .= '</tr>';

		$body .= '</thead>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Cancelado por:</b></td>';
			$body .= '<td>'. $cancelado_por .'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Cancelado el:</b></td>';
			$body .= '<td>'. $cancelado_el .'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Motivo:</b></td>';
			$body .= '<td>'. $cancelado_motivo .'</td>';
		$body .= '</tr>';

		$body .= '</table>';
		$body .= '</div>';

		if (sec_contrato_detalle_solicitudv2_is_valid_email($correo_cancelador)) {
			array_push($correos_add,$correo_cancelador);
		}

		if (sec_contrato_detalle_solicitudv2_is_valid_email($correo_aprobador)) {
			array_push($correos_add,$correo_aprobador);
		} else {
			if (sec_contrato_detalle_solicitudv2_is_valid_email($correo_director)) {
				array_push($correos_add,$correo_director);
			}
		}

		if (sec_contrato_detalle_solicitudv2_is_valid_email($correo_gerente)) {
			array_push($correos_add,$correo_gerente);
		} else {
			if (sec_contrato_detalle_solicitudv2_is_valid_email($correo_gerente_2)) {
				array_push($correos_add,$correo_gerente_2);
			}
		}

		if( (!is_null($fecha_atencion_gerencia_proveedor) && $aprobacion_gerencia_proveedor == 1) || ( $check_gerencia_proveedor == 0 ) ) { // Si la solicitud fue aprobada o se envio de frente a legal
			// Usuarios del área Legal
			array_push($correos_add, "mayra.duffoo@testtest.apuestatotal.com" );
			array_push($correos_add, "sandra.murrugarra@testtest.apuestatotal.com" );
			array_push($correos_add, "carolina.cano@testtest.apuestatotal.com" );
			array_push($correos_add, "ingrid.escobar@testtest.apuestatotal.com" );
		}

	}
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		switch ($tipo_contrato_id) {
			case '1': $url_solicitud = "detalle_solicitudv2"; break;
			case '2': $url_solicitud = "detalleSolicitudProveedor"; break;
			case '5': $url_solicitud = "detalle_solicitudv2_acuerdo_confidencialidad"; break;
			case '6': $url_solicitud = "detalle_agente"; break;
			case '7': $url_solicitud = "detalle_solicitudv2_interno"; break;
		}

		$body .= '<div style="width: 700px; text-align: center; font-family: arial;">';
			$body .= '<a href="'.$host.'/?sec_id=contrato&sub_sec_id='.$url_solicitud.'&id='.$contrato_id.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
				$body .= '<b>Ver Solicitud</b>';
			$body .= '</a>';
		$body .= '</div>';

		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$body .= '</html>';
		$body .= "";

	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_cancelar_solicitud($correos_add);

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];
	$nombre_tipo_contrato = "";

	switch ($tipo_contrato_id) {
		case '1': $nombre_tipo_contrato = "Contrato de Arrendamiento"; break;
		case '2': $nombre_tipo_contrato = "Contrato de Proveedor"; break;
		case '5': $nombre_tipo_contrato = "Contrato de Acuerdo de Confidencialidad"; break;
		case '6': $nombre_tipo_contrato = "Contrato de Agente"; break;
		case '7': $nombre_tipo_contrato = "Contrato Interno"; break;
	}

	$titulo_email = "Gestion - Sistema Contratos - Solicitud de " . $nombre_tipo_contrato . " Cancelada: Código - ";

	$request = [
		"subject" => $titulo_email . $sigla_correlativo . $codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;
		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc)
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc)
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->send();

		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		echo json_encode($result);
		exit();
	}
	catch (Exception $e)
	{
		$result["http_code"] = 400;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["error"] = $mail->ErrorInfo;
		echo json_encode($result);
		exit();
	}
}

function sec_contrato_detalle_solicitudv2_enviar_error($error) {
	$result["http_code"] = 500;
	$result["status"] = "error";
	$result["mensaje"] = $error;
	echo json_encode($result);
	die;
}

if (isset($_POST["accion"]) && $_POST["accion"]==="aprobar_adenda")
{
	$message = "";
	$usuario_id = $login?$login['id']:null;

	if ( !((int) $usuario_id > 0) ) {
		$result["http_code"] = 500;
		$result["status"] = "error";
		$result["mensaje"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y hacer click en: Aceptar solicitud o Rechazar solicitud.";
		echo json_encode($result);
		die;
	}

	$adenda_id = $_POST["adenda_id"];
	$aprobar_id = $_POST['aprobar_id'];
	$fecha_actual = date("Y-m-d H:i:s");

	$query_update = "
	UPDATE cont_adendas
	SET
	aprobado_estado_id = $aprobar_id,
	aprobado_el = '$fecha_actual',
	aprobado_por_id = $usuario_id
	WHERE id = $adenda_id
	";

	$mysqli->query($query_update);

	if($mysqli->error)
	{
		$result["status"] = false;
		$result["message"] = $mysqli->error;
	}
	else
	{


		$ADENDA_DE_CONTRATO = 2;
		$query_seguimiento = "SELECT id FROM cont_seguimiento_proceso_legal WHERE tipo_documento_id = ".$ADENDA_DE_CONTRATO." AND proceso_id = ".$_POST['adenda_id']." AND status IN (1,2)";
		$list_query_seguimiento = $mysqli->query($query_seguimiento);
		$cant_seguimientos = $list_query_seguimiento->num_rows;

		if ($cant_seguimientos > 0) {
			$ADENDA_DE_CONTRATO = 2;
			//actualizar seguimiento proceso
			$seg_proceso = new SeguimientoProceso();
			$data_proceso['usuario_id'] = $usuario_id;
			$data_proceso['tipo_documento_id'] = $ADENDA_DE_CONTRATO;
			$data_proceso['proceso_id'] = $adenda_id;
			$data_proceso['proceso_detalle_id'] = 0;
			$data_proceso['estado_aprobacion'] = $aprobar_id;
			$seg_proceso->aprobar_rechazar_seguimiento_proceso($data_proceso);
		}
		

		$result["status"] = true;
		$result["message"] = "Se guardo correctamente";
	}

}

if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_observaciones_contrato_adenda_gerencia") {
	$usuario_id = $login?$login['id']:null;

	if ( !((int) $usuario_id > 0) ) {
		$result["http_code"] = 500;
		$result["status"] = "error";
		$result["mensaje"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y hacer click e el boton Amarillo: Enviar observación.";
		echo json_encode($result);
		die;
	}

	$created_at = date('Y-m-d H:i:s');
	$error = '';

	// INICIO INSERTAR OBSERVACIONES
	$query_insert = " INSERT INTO cont_observaciones(
	contrato_id,
	adenda_id,
	observaciones,
	status,
	user_created_id,
	created_at)
	VALUES(
	" . $_POST["contrato_id"] . ",
	" . $_POST["adenda_id"] . ",
	'" . $_POST["observaciones"] . "',
	1,
	" . $usuario_id . ",
	'" . $created_at . "' )";

	$mysqli->query($query_insert);

	if($mysqli->error){
		$error .= $mysqli->error;
		echo $query_insert;
	}
	// FIN INSERTAR OBSERVACIONES

	if($_POST["tipo_observacion"] === 'proveedor')
	{
		send_email_observacion_adenda_proveedor_gerencia($mysqli->insert_id);
	}
	else if($_POST["tipo_observacion"] === 'acuerdo_confidencialidad')
	{
		send_email_observacion_adenda_acuerdo_confidencialidad_gerencia($mysqli->insert_id);
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = "ok";
	$result["error"] = $error;
}

function send_email_observacion_adenda_proveedor_gerencia($observacion_id)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$html = '';

	$sql = "SELECT
				o.id,
				o.contrato_id,
				o.observaciones,
			    c.razon_social as proveedor_razon_social,
				c.nombre_comercial as proveedor_nombre_comercial,
			    SUBSTRING(c.detalle_servicio, 1, 20) AS detalle_servicio,
			    c.fecha_inicio,
				concat(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, '')) AS usuario_creacion,
				p.correo AS usuario_creacion_correo,
				ar.id AS area_creacion_id,
				ar.nombre AS area_creacion,
				o.user_created_id,
				o.created_at,
				concat(IFNULL(ps.nombre, ''),' ', IFNULL(ps.apellido_paterno, '')) AS supervisor,
				concat(IFNULL(pjc.nombre, ''),' ', IFNULL(pjc.apellido_paterno, '')) AS jefe_comercial,
				pjc.correo AS usuario_solicitante_correo,
				co.sigla AS sigla_correlativo,
				c.codigo_correlativo,
				c.gerente_area_id,
				c.gerente_area_nombre,
				c.gerente_area_email,
				CONCAT(IFNULL(peg.nombre, ''),' ',IFNULL(peg.apellido_paterno, ''),' ',IFNULL(peg.apellido_materno, '')) AS nombre_del_gerente_area,
				peg.correo AS email_del_gerente_area,
				o.adenda_id,
				a.codigo AS num_adenda
			FROM cont_observaciones o
				INNER JOIN tbl_usuarios u ON o.user_created_id = u.id
				INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
				INNER JOIN tbl_areas ar ON p.area_id = ar.id
				INNER JOIN cont_contrato c ON o.contrato_id = c.contrato_id
				INNER JOIN cont_adendas a ON o.adenda_id = a.id
				LEFT JOIN tbl_usuarios us ON c.persona_responsable_id = us.id
				LEFT JOIN tbl_personal_apt ps ON us.personal_id = ps.id
				LEFT JOIN tbl_usuarios ujc ON c.user_created_id = ujc.id
				LEFT JOIN tbl_personal_apt pjc ON ujc.personal_id = pjc.id
				LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
				LEFT JOIN tbl_usuarios ug ON c.gerente_area_id = ug.id
				LEFT JOIN tbl_personal_apt peg ON ug.personal_id = peg.id
			WHERE o.id = " . $observacion_id . " AND o.status = 1
			ORDER BY o.created_at ASC";


	$query = $mysqli->query($sql);

	$contrato_id_de_la_obs = "";

	$body = "";
	$body .= '<html>';
	$body .= '<div>';

	$usuario_creacion_correo = '';

	while($sel = $query->fetch_assoc())
	{
		$sigla_correlativo = $sel['sigla_correlativo'];
		$codigo_correlativo = $sel['codigo_correlativo'];
		$area_creacion_id = $sel['area_creacion_id'];
		$usuario_creacion_correo = $sel['usuario_solicitante_correo'];
		$date = date_create($sel["fecha_inicio"]);
		$fecha_inicio_contrato = date_format($date, "Y-m-d");
		$contrato_id_de_la_obs = $sel["contrato_id"];
		$gerente_area_id = trim($sel["gerente_area_id"]);
		$adenda_id = trim($sel["adenda_id"]);
		$num_adenda = trim($sel["num_adenda"]);

		if (empty($gerente_area_id)) {
			$gerente_area_nombre = trim($sel["gerente_area_nombre"]);
			$gerente_area_email = trim($sel["gerente_area_email"]);
		} else {
			$gerente_area_nombre = trim($sel["nombre_del_gerente_area"]);
			$gerente_area_email = trim($sel["email_del_gerente_area"]);
		}

		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

		$body .= '<thead>';
		$body .= '<tr>';
		$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
		$body .= '<b>Observacion en la Solicitud de Adenda</b>';
		$body .= '</th>';
		$body .= '</tr>';
		$body .= '</thead>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>De:</b></td>';
			$body .= '<td>'.$sel["area_creacion"].' </td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Razón Social Proveedor:</b></td>';
			$body .= '<td>'.$sel["proveedor_razon_social"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Nombre Comercial Proveedor:</b></td>';
			$body .= '<td>'.$sel["proveedor_nombre_comercial"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Detale servicio:</b></td>';
			$body .= '<td>'.$sel["detalle_servicio"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Fecha inicio contrato:</b></td>';
			$body .= '<td>'.$fecha_inicio_contrato.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 160px;"><b>Fecha observacion:</b></td>';
			$body .= '<td>'.$sel["created_at"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Observacion:</b></td>';
			$body .= '<td>'.$sel["observaciones"].'</td>';
		$body .= '</tr>';

		$body .= '</table>';
	}

	$body .= '</div>';

	$body .= '<div>';
		$body .= '<br>';
	$body .= '</div>';

	$body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
	    $body .= '<a href="'.$host.'/?sec_id=contrato&sub_sec_id=detalleSolicitudProveedor&id='.$contrato_id_de_la_obs.'&adenda_id=' . $adenda_id . '" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
			$body .= '<b>Ver Solicitud de Adenda</b>';
	    $body .= '</a>';
	$body .= '</div>';

	$body .= '</html>';
	$body .= "";


	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_observacion_gerencia([$usuario_creacion_correo]);

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];

	$request = [
		"subject" => "Gestion - Sistema Contratos - Observación en la Solicitud de Adenda N°. " . $num_adenda . " de Proveedor: Código - " .$sigla_correlativo.$codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
		],
	];

	$mail = new PHPMailer(true);

	try
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc)
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc)
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();

	}
	catch (Exception $e)
	{
		$resultado = $mail->ErrorInfo;
		echo json_encode($resultado);
	}

}

function send_email_observacion_adenda_acuerdo_confidencialidad_gerencia($observacion_id)
{
	include "db_connect.php";
	include "sys_login.php";

	$host = $_SERVER["HTTP_HOST"];

	$html = '';

	$sql = "
	SELECT
		o.contrato_id,
		o.observaciones,
		c.razon_social as proveedor_razon_social,
		c.nombre_comercial as proveedor_nombre_comercial,
		SUBSTRING(c.detalle_servicio, 1, 50) AS detalle_servicio,
		c.fecha_inicio,
		concat(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, '')) AS usuario_creacion,
		p.correo AS usuario_creacion_correo,
		ar.nombre AS area_creacion,
		o.created_at,
		pjc.correo AS usuario_solicitante_correo,
		co.sigla AS sigla_correlativo,
		c.codigo_correlativo,
		c.gerente_area_id,
		c.gerente_area_nombre,
		c.gerente_area_email,
		CONCAT(IFNULL(peg.nombre, ''),' ',IFNULL(peg.apellido_paterno, ''),' ',IFNULL(peg.apellido_materno, '')) AS nombre_del_gerente_area,
		peg.correo AS email_del_gerente_area,
		o.adenda_id,
		a.codigo AS num_adenda
	FROM cont_observaciones o
		INNER JOIN tbl_usuarios u ON o.user_created_id = u.id
		INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		INNER JOIN tbl_areas ar ON p.area_id = ar.id
		INNER JOIN cont_contrato c ON o.contrato_id = c.contrato_id
		INNER JOIN cont_adendas a ON o.adenda_id = a.id
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
		LEFT JOIN tbl_usuarios ug ON c.gerente_area_id = ug.id
		LEFT JOIN tbl_personal_apt peg ON ug.personal_id = peg.id
		LEFT JOIN tbl_usuarios ujc ON c.user_created_id = ujc.id
		LEFT JOIN tbl_personal_apt pjc ON ujc.personal_id = pjc.id
	WHERE 
		o.id = $observacion_id 
		AND o.status = 1
	ORDER BY o.created_at ASC
	";

	$query = $mysqli->query($sql);

	$contrato_id_de_la_obs = "";

	$body = "";
	$body .= '<html>';
	$body .= '<div>';

	$usuario_creacion_correo = '';

	while ($sel = $query->fetch_assoc()) {
		$sigla_correlativo       = $sel['sigla_correlativo'];
		$codigo_correlativo      = $sel['codigo_correlativo'];
		$usuario_creacion_correo = $sel['usuario_solicitante_correo'];
		$date                    = date_create($sel["fecha_inicio"]);
		$fecha_inicio_contrato   = date_format($date, "Y-m-d");
		$contrato_id_de_la_obs   = $sel["contrato_id"];
		$gerente_area_id         = trim($sel["gerente_area_id"]);
		$adenda_id               = trim($sel["adenda_id"]);
		$num_adenda              = trim($sel["num_adenda"]);

		if (empty($gerente_area_id)) {
			$gerente_area_nombre = trim($sel["gerente_area_nombre"]);
			$gerente_area_email  = trim($sel["gerente_area_email"]);
		} else {
			$gerente_area_nombre = trim($sel["nombre_del_gerente_area"]);
			$gerente_area_email  = trim($sel["email_del_gerente_area"]);
		}

		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

		$body .= '<thead>';
		$body .= '<tr>';
		$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
		$body .= '<b>Observacion en la Solicitud de Adenda</b>';
		$body .= '</th>';
		$body .= '</tr>';
		$body .= '</thead>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>De:</b></td>';
		$body .= '<td>' . $sel["area_creacion"] . ' </td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Razón Social Proveedor:</b></td>';
		$body .= '<td>' . $sel["proveedor_razon_social"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Nombre Comercial Proveedor:</b></td>';
		$body .= '<td>' . $sel["proveedor_nombre_comercial"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Detale servicio:</b></td>';
		$body .= '<td>' . $sel["detalle_servicio"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Fecha inicio contrato:</b></td>';
		$body .= '<td>' . $fecha_inicio_contrato . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Fecha observacion:</b></td>';
		$body .= '<td>' . $sel["created_at"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Observacion:</b></td>';
		$body .= '<td>' . $sel["observaciones"] . '</td>';
		$body .= '</tr>';

		$body .= '</table>';
	}

	$body .= '</div>';

	$body .= '<div>';
	$body .= '<br>';
	$body .= '</div>';

	$body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
	$body .= '<a href="' . $host . '/?sec_id=contrato&sub_sec_id=detalle_solicitudv2_acuerdo_confidencialidad&id=' . $contrato_id_de_la_obs . '&adenda_id=' . $adenda_id . '" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
	$body .= '<b>Ver Solicitud de Adenda</b>';
	$body .= '</a>';
	$body .= '</div>';

	$body .= '</html>';
	$body .= "";

	$correos       = new Correos(env('SEND_EMAIL'), env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_observacion_gerencia([$usuario_creacion_correo]);

	$cc  = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];

	$request = [
		"subject" => "Gestion - Sistema Contratos - Observación en la Solicitud de Adenda N°. " . $num_adenda . " de Acuerdo de Confidencialidad: Código - " . $sigla_correlativo . $codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [],
	];

	$mail = new PHPMailer(true);

	try {
		$mail->isSMTP();
		$mail->Host     = "smtp.gmail.com";
		$mail->SMTPAuth = true;

		$mail->Username   = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password   = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port       = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if (isset($request["cc"])) {
			foreach ($request["cc"] as $cc) {
				$mail->addAddress($cc);
			}
		}

		if (isset($request["bcc"])) {
			foreach ($request["bcc"] as $bcc) {
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet  = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();
	} catch (Exception $e) {
		$resultado = $mail->ErrorInfo;
		echo json_encode($resultado);
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_observaciones_adenda_gerencia") {

	$contrato_id = $_POST["contrato_id"];
	$adenda_id = $_POST["adenda_id"];
	$html = '';

	$sql = "SELECT
	o.observaciones,
	concat(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, '')) AS usuario,
	ar.nombre AS area,
	o.user_created_id,
	o.created_at
	FROM cont_observaciones o
	INNER JOIN tbl_usuarios u ON o.user_created_id = u.id
	INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
	INNER JOIN tbl_areas ar ON p.area_id = ar.id
	WHERE o.contrato_id = " . $contrato_id . "
	AND o.adenda_id = " . $adenda_id . "
	AND o.status = 1
	AND o.user_created_id IN (SELECT ud.user_id FROM cont_usuarios_directores ud WHERE ud.status = 1)
	ORDER BY o.created_at ASC";

	$query = $mysqli->query($sql);
	$row_count = $query->num_rows;

	if ($row_count > 0)
	{
		while ($row = $query->fetch_assoc())
		{
			$date = date_create($row["created_at"]);
			$created_at = date_format($date,"d/m/Y h:i a");

			if($row["user_created_id"] == $login['id'])
			{
				// ESTE DIV ES PARA EL USUARIO LOGUEADO
				$html .= '<div class="col-sm-offset-1 col-sm-11 caja_usuario_aprobacion alert alert-success" style="padding-bottom:10px;color:#333;box-shadow: 4px 4px 4px rgba(0,0,0,0.15);">';

				$html .= '<div class="col-sm-8" style="margin-bottom:5px;">';
				$html .= '<strong>'. $row["usuario"] .' (' . $row["area"] .')</strong>';
				$html .= '</div>';

				$html .= '<div class="col-sm-4 text-right" style="color:rgba(0,0,0,0.5); font-size: 11px;">';
				$html .= '<span class="time"><i class="fa fa-clock-o"></i> ' . $created_at . '</span>';
				$html .= '</div>';

				$html .= '<div class="col-xs-12" style="padding-top: 5px; background:rgba(255,255,255,0.7); margin-bottom:2px;">';
				$html .= $row["observaciones"];
				$html .= '</div>';

				$html .= '</div>';
			}
			else
			{
				// ESTE DIV ES PARA OTROS USUARIOS
				$html .= '<div class="col-sm-11 caja_usuario_creador alert alert-info" style="padding-bottom:10px;color:#333;box-shadow: 4px 4px 4px rgba(0,0,0,0.15);">';

				$html .= '<div class="col-sm-8" style="margin-bottom:5px;">';
				$html .= '<strong>'. $row["usuario"] .' (' . $row["area"] .')</strong>';
				$html .= '</div>';

				$html .= '<div class="col-sm-4 text-right" style="color:rgba(0,0,0,0.5); font-size: 11px;">';
				$html .= '<span class="time"><i class="fa fa-clock-o"></i> <strong>' . $created_at . '</strong></span>';
				$html .= '</div>';

				$html .= '<div class="col-xs-12" style="padding-top: 5px; background:rgba(255,255,255,0.7); margin-bottom:2px;">';
				$html .= $row["observaciones"];
				$html .= '</div>';

				$html .= '</div>';
			}
		}
	}


	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}

	$result["http_code"] = 200;
	$result["cant_mensaje"] = $row_count;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $html;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "agregar_formato_al_nombre_tienda") {
	$usuario_id = $login ? $login['id'] : null;

	if ((int) $usuario_id > 0) {
		$nombre_tienda = sec_contrato_detalle_solicitudv2_formato_nombre_de_tienda(replace_invalid_caracters(trim($_POST["nombre_tienda"])));
		$contrato_id   = $_POST["contrato_id"];

		$query_update = "
		UPDATE
			cont_contrato
		SET
			nombre_tienda = '$nombre_tienda'
		WHERE
			contrato_id = $contrato_id
		";
		$mysqli->query($query_update);

		if ($mysqli->error) {
			$result["http_code"] = 400;
			$result["error"]     = 'Error al actualizar el nombre de tienda del contrato: ' . $mysqli->error . $query_update;
		} else {
			$result["http_code"] = 200;
		}
	} else {
		$result["http_code"] = 400;
		$result["error"]     = "La sesión ha caducado.";
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "reenviar_notificacion_seguimiento_proceso") {

	$query_seguimiento = "SELECT cspl.tipo_documento_id, cspl.proceso_id 
	FROM cont_seguimiento_proceso_legal cspl where cspl.id = ".$_POST['seguimiento_id'];
	$sel_seg_query = $mysqli->query($query_seguimiento);
	$data_seguimiento = $sel_seg_query->fetch_assoc();

	$seguimiento = new SeguimientoProceso();
	$data['seguimiento_id'] = $_POST['seguimiento_id'];
	$data['tipo_contrato_id'] = $_POST['tipo_contrato_id'];
	$data['proceso_id'] = $data_seguimiento['proceso_id'];
	$data['tipo_documento_id'] = $data_seguimiento['tipo_documento_id'];
	$response = $seguimiento->reenviar_notificacion_seguimiento_proceso($data);
	echo json_encode($response);
	exit();

}

function sec_contrato_detalle_solicitudv2_num_dias_habiles($fecha_cambio_estado_solicitud, $fecha_actual_string = '')
{
	$fecha_del_cambio = DateTime::createFromFormat('Y-m-d', date('Y-m-d', strtotime($fecha_cambio_estado_solicitud)));

	if (!$fecha_del_cambio) {
		return 505; // Formato de fecha no es válido
	}

	if (empty($fecha_actual_string)) {
		$fecha_actual = new DateTime();
	} else {
		$fecha_actual = DateTime::createFromFormat('Y-m-d', $fecha_actual_string);
	}

	if (!$fecha_actual) {
		$fecha_actual = new DateTime();
	}

	$intervalo_en_dias = $fecha_del_cambio->diff($fecha_actual)->days;

	if ($intervalo_en_dias >= 84) {
		return 60; // Más de 60 dias
	}

	global $mysqli;

	$fecha_del_cambio_string = $fecha_del_cambio->format('Y-m-d');
	$fecha_actual_string = $fecha_actual->format('Y-m-d');

	$query_feriados = "SELECT fecha FROM tbl_feriados WHERE status = 1 AND fecha BETWEEN '$fecha_del_cambio_string' AND '$fecha_actual_string'";
	$list_query     = $mysqli->query($query_feriados);
	$feriados       = array();

	while ($li = $list_query->fetch_assoc()) {
		$feriados[] = $li['fecha'];
	}

	$dias_habiles = 0;
	$fecha_del_cambio->modify('+1 day');

	while ($fecha_del_cambio <= $fecha_actual) {
		if ($fecha_del_cambio->format('N') >= 1 && $fecha_del_cambio->format('N') <= 5) {
			if (!in_array($fecha_del_cambio->format('Y-m-d'), $feriados)) {
				$dias_habiles++;
			}
		}
		$fecha_del_cambio->modify('+1 day');
	}

	return $dias_habiles;
}

if (isset($_POST["accion"]) && $_POST["accion"]==="corregir_dias_habiles") {
	$usuario_id = $login?$login['id']:null;

	if ($usuario_id == null) {
		$result["http_code"] = 400;
		$result["message"] = 'No se puedo guardar el estado porque su sessión a caducado, inicie sesión nuevamente para poder guardar';
		echo json_encode($result);
		exit();
	}

	$contrato_id = $_POST["contrato_id"];
	$error = '';

	$query_verify = "SELECT dias_habiles, fecha_atencion_gerencia_proveedor, fecha_atencion_gerencia_interno, created_at, fecha_cambio_estado_solicitud FROM cont_contrato WHERE contrato_id = $contrato_id";
	$list_query = $mysqli->query($query_verify);
	if ($list_query->num_rows > 0) {
		while ($fila = $list_query->fetch_array()) {
			$dias_habiles = $fila['dias_habiles'];

			$fecha_solicitud =  isset($fila['fecha_atencion_gerencia_proveedor']) ? $fila['fecha_atencion_gerencia_proveedor'] :
								(isset($fila['fecha_atencion_gerencia_interno']) ? $fila['fecha_atencion_gerencia_interno'] :
								$fila['created_at']);
			$fecha_cambio_de_estado = $fila['fecha_cambio_estado_solicitud'];
		}
		if (is_numeric($dias_habiles)) {
			$dias_habiles = sec_contrato_detalle_solicitudv2_num_dias_habiles($fecha_solicitud, $fecha_cambio_de_estado);

			$query_update_dias_habiles = "
			UPDATE cont_contrato
			SET dias_habiles = $dias_habiles
			WHERE contrato_id = $contrato_id ";

			$mysqli->query($query_update_dias_habiles);

			if($mysqli->error){
				$error .= 'Error al cambiar el estado de la solicitud: ' . $mysqli->error . $query_update_dias_habiles;
			}
		}
	}

	$result["http_code"] = $error == '' ? 200:400;
	$result["message"] =$error == ''? "Se ha cambiado el estado de la solicitud.":$error;
	$result["result"] = $contrato_id;
	$result["error"] = $error;
}

// NIF16
if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_inflacion") {

	$usuario_id = $login?$login['id']:null;
	$created_at = date('Y-m-d H:i:s');


	$sql = "SELECT c.tipo_moneda_id, c.fecha_inicio, c.contrato_id
	FROM  cont_condicion_economica AS c
	WHERE c.contrato_id = " . $_POST['contrato_id'];
	$query = $mysqli->query($sql);
	$row_ce = $query->fetch_assoc();


	$porcentaje_anadido = !Empty($_POST['porcentaje_anadido']) ? $_POST['porcentaje_anadido']:'NULL';
	$numero = !Empty($_POST['numero']) ? $_POST['numero']:'NULL';
	$tope_inflacion = !Empty($_POST['tope_inflacion']) ? $_POST['tope_inflacion']:'NULL';
	$minimo_inflacion = !Empty($_POST['minimo_inflacion']) ? $_POST['minimo_inflacion']:'NULL';
	$tipo_aplicacion_id = !Empty($_POST['tipo_aplicacion_id']) ? $_POST['tipo_aplicacion_id']:'NULL';

	
	$query_insert = " INSERT INTO cont_inflaciones
		(
			contrato_id,
			contrato_detalle_id,
			tipo_periodicidad_id,
			numero,
			tipo_anio_mes,
			moneda_id,
			porcentaje_anadido,
			tope_inflacion,
			minimo_inflacion,
			tipo_aplicacion_id,
			status,
			created_at,
			user_created_id)
		VALUES(
			".$_POST['contrato_id'] .",
			'" . $_POST['contrato_detalle_id'] . "',
			'" . $_POST['tipo_periodicidad_id'] . "',
			" . $numero . ",
			'" . $_POST['tipo_anio_mes'] . "',
			'".$row_ce['tipo_moneda_id']."',
			" . $porcentaje_anadido. ",
			" . $tope_inflacion . ",
			" . $minimo_inflacion . ",
			" . $tipo_aplicacion_id . ",
			1,
			'" . $created_at . "',
			'" . $usuario_id . "')";

		$mysqli->query($query_insert);
		$insert_id = mysqli_insert_id($mysqli);
		$error = '';
		if($mysqli->error){
			$error = $mysqli->error. " | ".$query_insert;
			$result["http_code"] = 404;
			$result["error"] = $error;
			echo json_encode($result);
			exit();
		}

		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestión.";
		$result["result"] = $insert_id;
		$result["error"] = $error;
		echo json_encode($result);
		exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="eliminar_inflacion") {

	$usuario_id = $login?$login['id']:null;
	$created_at = date('Y-m-d H:i:s');

	$query_update = " UPDATE cont_inflaciones SET
		status = 0
	WHERE id = ".$_POST['inflacion_id'];

	$mysqli->query($query_update);
	$error = '';
	if($mysqli->error){
		$error = $mysqli->error. " | ".$query_update;
		$result["http_code"] = 404;
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["error"] = $error;
	echo json_encode($result);
	exit();
}




if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_cuota_extraordinaria") {

	$usuario_id = $login?$login['id']:null;
	$created_at = date('Y-m-d H:i:s');

	$query_insert = " INSERT INTO cont_cuotas_extraordinarias
		(
			contrato_id,
			contrato_detalle_id,
			mes,
			multiplicador,
			status,
			created_at,
			user_created_id)
		VALUES(
			'" . $_POST['contrato_id'] . "',
			'" . $_POST['contrato_detalle_id'] . "',
			'" . $_POST['mes'] . "',
			'" . $_POST['multiplicador'] . "',
			1,
			'" . $created_at . "',
			'" . $usuario_id . "')";

		$mysqli->query($query_insert);
		$insert_id = mysqli_insert_id($mysqli);
		$error = '';
		if($mysqli->error){
			$error = $mysqli->error. " | ".$query_insert;
			$result["http_code"] = 404;
			$result["error"] = $error;
			echo json_encode($result);
			exit();
		}

		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestión.";
		$result["result"] = $insert_id;
		$result["error"] = $error;
		echo json_encode($result);
		exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="eliminar_cuota_extraordinaria") {

	$query_update = " UPDATE cont_cuotas_extraordinarias SET
		status = 0
	WHERE id = ".$_POST['cuota_extraordinaria_id'];

	$mysqli->query($query_update);
	$error = '';
	if($mysqli->error){
		$error = $mysqli->error. " | ".$query_update;
		$result["http_code"] = 404;
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["error"] = $error;
	echo json_encode($result);
	exit();
}


if (isset($_POST["accion"]) && $_POST["accion"]==="subir_documento_adenda") {

	$usuario_id = $login?$login['id']:null;

	if ( !((int) $usuario_id > 0) ) {
		$result["http_code"] = 500;
		$result["status"] = "error";
		$result["mensaje"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y hacer click e el boton verde: Agregar adenda firmada.";
		echo json_encode($result);
		die;
	}

	$created_at = date("Y-m-d H:i:s");
	$contrato_id = $_POST["contrato_id"];

	$query_contrato = "SELECT cc.tipo_contrato_id FROM cont_contrato cc where cc.contrato_id  = ".$contrato_id;
	$sel_contrato = $mysqli->query($query_contrato);
	$row = $sel_contrato->fetch_assoc();

	$tipo_archivo_id = 0;
	$path = "";
	if ($row['tipo_contrato_id'] == 1) { // Contrato arrendamiento
		$path = "/var/www/html/files_bucket/contratos/adendas/locales/";
		$tipo_archivo_id = 17;
	}else if ($row['tipo_contrato_id'] == 2){
		$path = "/var/www/html/files_bucket/contratos/adendas/proveedores/";
		$tipo_archivo_id = 152;
	}

	if ($tipo_archivo_id == 0){
		$result['status'] = 404;
		$result['message'] = 'No se logro subir un documento';
		echo json_encode($result);
		exit();
	}

	
	$name_file = 'fileArchivo_adenda';
	$archivo_id = 0;
	if (isset($_FILES[$name_file]) && $_FILES[$name_file]['error'] === UPLOAD_ERR_OK) {
		if (!is_dir($path)) mkdir($path, 0777, true);

		$filename = $_FILES[$name_file]['name'];
		$filenametem = $_FILES[$name_file]['tmp_name'];
		$filesize = $_FILES[$name_file]['size'];
		$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
		if($filename != ""){
			$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
			$nombre_archivo = $contrato_id . "_ADENDA_FIRMADA_" . date('YmdHis') . "." . $fileExt;
			move_uploaded_file($filenametem, $path . $nombre_archivo);
			$comando = "INSERT INTO cont_archivos (
							contrato_id,
							tipo_archivo_id,
							nombre,
							extension,
							size,
							ruta,
							user_created_id,
							created_at)
						VALUES(
							'" . $contrato_id . "',
							'".$tipo_archivo_id."',
							'" . $nombre_archivo . "',
							'" . $fileExt . "',
							'" . $filesize . "',
							'" . $path . "',
							" . $usuario_id . ",
							'" . $created_at . "'
							)";
			$mysqli->query($comando);
			$archivo_id = mysqli_insert_id($mysqli);

			$result['status'] = 200;
			$result['message'] = 'Se ha subido el documento de la adenda correctamente';
			$result['result'] = $archivo_id;
			echo json_encode($result);
			exit();


		}
	}

	$result['status'] = 404;
	$result['message'] = 'No se logro subir un documento';
	echo json_encode($result);
	exit();

}

if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_estado_contrato") {

	$usuario_id = $login?$login['id']:null;

	if ( !((int) $usuario_id > 0) ) {
		$result["http_code"] = 500;
		$result["status"] = "error";
		$result["mensaje"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y hacer click e el boton verde: Agregar adenda firmada.";
		echo json_encode($result);
		die;
	}

	$usuario_id = $login?$login['id']:null;
	$created_at = date('Y-m-d H:i:s');


	$select_contrato = "SELECT c.status FROM cont_contrato as c WHERE c.contrato_id = ". $_POST['contrato_id'];
	$sel_query = $mysqli->query($select_contrato);
	$contrato = $sel_query->fetch_assoc();
	if (!isset($contrato['status'])){
		$result["http_code"] = 404;
		$result["error"] = '';
		$result["message"] = 'A ocurrido un error. Intenlo mas tarde.';
		echo json_encode($result);
		exit();
	}

	if ( (int) $contrato['status'] == (int) $_POST['estado']){
		$result["http_code"] = 404;
		$result["error"] = '';
		$result["message"] = 'Verifique que el estado del contrato sea diferente al actual.';
		echo json_encode($result);
		exit();
	}



	$query_insert = " INSERT INTO cont_contrato_estado
		(
			contrato_id,
			user_created_id,
			estado,
			motivo,
			status,
			created_at)
		VALUES(
			'" . $_POST['contrato_id'] . "',
			'" . $usuario_id . "',
			'" . $_POST['estado'] . "',
			'" . trim(replace_invalid_caracters($_POST['motivo'])) . "',
			1,
			'" . $created_at . "'
		)";

	$mysqli->query($query_insert);
	$insert_id = mysqli_insert_id($mysqli);
	$error = '';
	if($mysqli->error){
		$error = $mysqli->error. " | ".$query_insert;
		$result["http_code"] = 404;
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}

	$query_updated = "UPDATE cont_contrato SET status = ". $_POST['estado']." WHERE  `contrato_id`= ".$_POST['contrato_id'];

	$mysqli->query($query_updated);
	if($mysqli->error){
		$error .= $mysqli->error. " | ".$query_updated;
		$result["http_code"] = 404;
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}

	send_email_cambiar_estado_contrato($insert_id, false);

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $insert_id;
	$result["error"] = $error;
	echo json_encode($result);
	exit();

}

if (isset($_POST["accion"]) && $_POST["accion"]==="reenviar_adenda_firmada") {
	
	try{
		if($_POST['tipo_contrato'] == 1){
			send_email_adenda_contrato_arrendamiento_firmada($_POST["adenda_id"], true);
		}
		if($_POST['tipo_contrato'] == 2){
			send_email_adenda_contrato_proveedor_firmada($_POST["adenda_id"], true);
		}
		if($_POST['tipo_contrato'] == 5){
			send_email_adenda_acuerdo_confidencialidad_firmada($_POST["adenda_id"], true);
		}
		

		$result['status'] = 200;
		$result['result'] = '';
		$result['message'] = 'Se ha reenviado correctamente la adenda firmada.';
		echo json_encode($result);
		exit();
	} catch (\Throwable $th) {
		$result['status'] = 404;
		$result['result'] = $th->getMessage();
		$result['message'] = 'A ocurrido un error, intentelo mas tarde.';
		echo json_encode($result);
		exit();
	}
}


if (isset($_POST["accion"]) && $_POST["accion"]==="reenviar_resolucion_firmada") {
	
	try{
		if($_POST['tipo_contrato'] == 1 || $_POST['tipo_contrato'] == 2  || $_POST['tipo_contrato'] == 5  || $_POST['tipo_contrato'] == 6 || $_POST['tipo_contrato'] == 7){
			send_email_solicitud_resolucion_contrato_firmado($_POST["resolucion_id"], true);
		}

		$result['status'] = 200;
		$result['result'] = '';
		$result['message'] = 'Se ha reenviado correctamente la resolución de contrato firmada.';
		echo json_encode($result);
		exit();
	} catch (\Throwable $th) {
		$result['status'] = 404;
		$result['result'] = $th->getMessage();
		$result['message'] = 'A ocurrido un error, intentelo mas tarde.';
		echo json_encode($result);
		exit();
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="cambiar_estado_legal_resolucion_contrato") {

	try{

		$usuario_id = $login?$login['id']:null;

		if ( !((int) $usuario_id > 0) ) {
			$result["http_code"] = 500;
			$result["status"] = "error";
			$result["mensaje"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y hacer click e el boton verde: Agregar adenda firmada.";
			echo json_encode($result);
			die;
		}

		$resolucion_id = $_POST['resolucion_id'];
		$fecha_actual = date('Y-m-d');
		$estado_solicitud = $_POST['estado_solicitud'];

		$query_verify = "SELECT dias_habiles, created_at FROM cont_resolucion_contrato WHERE id = $resolucion_id";
		$list_query = $mysqli->query($query_verify);
		if ($list_query->num_rows > 0) {
			while ($fila = $list_query->fetch_array()) {
				$dias_habiles = $fila['dias_habiles'];
				$fecha_solicitud =  $fila['created_at'];
			}
			if (!is_numeric($dias_habiles)) {
				$dias_habiles = sec_contrato_detalle_solicitudv2_num_dias_habiles($fecha_solicitud);

				$query_update_dias_habiles = "
				UPDATE
					cont_resolucion_contrato
				SET fecha_cambio_estado_solicitud = '$fecha_actual',
					dias_habiles = $dias_habiles,
					usuario_responsable_estado_solicitud_primero =  $usuario_id
				WHERE id = $resolucion_id ";

				$mysqli->query($query_update_dias_habiles);

				if($mysqli->error){
					$error .= 'Error al cambiar el estado de la solicitud: ' . $mysqli->error . $query_update_dias_habiles;
				}

				//Inicio Seguimiento Proceso
				$REVISION_AREA_USUARIA = 3;
				$NO_HAY_SEGUIMIENTO = 8;
				$OBSERVADO = 9;
				$RESOLUCION_CONTRATO = 3;
				$data_proceso['usuario_id'] = $usuario_id;
				$data_proceso['tipo_documento_id'] = $RESOLUCION_CONTRATO;
				$data_proceso['proceso_id'] = $resolucion_id;
				$data_proceso['proceso_detalle_id'] = 0;
				if ($estado_solicitud == 2) { // En proceso
					$data_proceso['nueva_etapa_id'] = $REVISION_AREA_USUARIA;
					$data_proceso['status_nueva_etapa_id'] = 1; // En Proceso
					$seg_proceso = new SeguimientoProceso();
					$res_proceso = $seg_proceso->atender_inicio_proceso_legal($data_proceso);
				}else if ($estado_solicitud == 3){ // Observado
					$data_proceso['nueva_etapa_id'] = $OBSERVADO;
					$data_proceso['status_nueva_etapa_id'] = 2; // En Proceso
					$seg_proceso = new SeguimientoProceso();
					$res_proceso = $seg_proceso->atender_inicio_proceso_legal($data_proceso);
				}else if ($estado_solicitud == 4){ // No Aplica
					$data_proceso['nueva_etapa_id'] = $NO_HAY_SEGUIMIENTO;
					$data_proceso['status_nueva_etapa_id'] = 2; // 
					$seg_proceso = new SeguimientoProceso();
					$res_proceso = $seg_proceso->atender_inicio_proceso_legal($data_proceso);
				}
				//Fin Seguimiento Proceso
			}
		}

		$query_update = "
		UPDATE
			cont_resolucion_contrato
		SET
			estado_solicitud_legal = $estado_solicitud,
			usuario_responsable_estado_solicitud_id = $usuario_id
		WHERE
			id = $resolucion_id
		";
		$mysqli->query($query_update);

		$result['status'] = 200;
		$result['result'] = '';
		$result['message'] = 'Se ha cambiado de estado de legal a la resolución de contrato.';
		echo json_encode($result);
		exit();
	} catch (\Throwable $th) {
		$result['status'] = 404;
		$result['result'] = $th->getMessage();
		$result['message'] = 'A ocurrido un error, intentelo mas tarde.';
		echo json_encode($result);
		exit();
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="subir_documento_adenda_escision_arrendamiento") {

	$usuario_id = $login?$login['id']:null;

	if ( !((int) $usuario_id > 0) ) {
		$result["http_code"] = 500;
		$result["status"] = "error";
		$result["mensaje"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y hacer click e el boton verde: Agregar adenda firmada.";
		echo json_encode($result);
		die;
	}



	try{


		$created_at = date("Y-m-d H:i:s");
		$contrato_id = $_POST["contrato_id"];
		$empresa_id = $_POST["empresa_id"];
		$fecha_escision = $_POST["fecha_escision"];
		$path = "/var/www/html/files_bucket/contratos/adendas/locales/";
		$name_file = 'fileArchivo_arrendamiento_adenda_escision';
		$archivo_id = 0;
	
	
		$query_select = "SELECT c.empresa_suscribe_id, r.nombre AS empresa
		FROM cont_contrato AS c
		LEFT JOIN tbl_razon_social AS r ON r.id = c.empresa_suscribe_id
		WHERE c.contrato_id = ".$contrato_id;
		$sel_query = $mysqli->query($query_select);
		$data_contrato = $sel_query->fetch_assoc();
	
		if (!isset($data_contrato['empresa_suscribe_id'])){
			$result['status'] = 404;
			$result['message'] = 'A ocurrido un error, al subir la escisión';
			$result['result'] = $archivo_id;
			echo json_encode($result);
			exit();
		}
	
	
		$query_select_empresa = "SELECT r.id, r.nombre AS empresa
		FROM tbl_razon_social AS r
		WHERE r.id = ".$empresa_id;
		$sel_query = $mysqli->query($query_select_empresa);
		$data_empresa = $sel_query->fetch_assoc();
	
		if (!isset($data_empresa['id'])){
			$result['status'] = 404;
			$result['message'] = 'A ocurrido un error, al subir la escisión';
			$result['result'] = $archivo_id;
			echo json_encode($result);
			exit();
		}
	
	
		if (isset($_FILES[$name_file]) && $_FILES[$name_file]['error'] === UPLOAD_ERR_OK) {
			if (!is_dir($path)) mkdir($path, 0777, true);
	
			$filename = $_FILES[$name_file]['name'];
			$filenametem = $_FILES[$name_file]['tmp_name'];
			$filesize = $_FILES[$name_file]['size'];
			$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
			if($filename != ""){
				$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
				$nombre_archivo = $contrato_id . "_ADENDA_FIRMADA_" . date('YmdHis') . "." . $fileExt;
				move_uploaded_file($filenametem, $path . $nombre_archivo);
				$comando = "INSERT INTO cont_archivos (
								contrato_id,
								tipo_archivo_id,
								nombre,
								extension,
								size,
								ruta,
								user_created_id,
								created_at)
							VALUES(
								'" . $contrato_id . "',
								154,
								'" . $nombre_archivo . "',
								'" . $fileExt . "',
								'" . $filesize . "',
								'" . $path . "',
								" . $usuario_id . ",
								'" . $created_at . "'
								)";
				$mysqli->query($comando);
				if($mysqli->error){
					$result['status'] = 404;
					$result['message'] = 'A ocurrido un error, al subir el documento adjunto';
					$result['result'] = $comando;
					echo json_encode($result);
					exit();
				}

				$archivo_id = mysqli_insert_id($mysqli);
			}
		}
	
		/// Update campos de auditoria
		$query_insert = "INSERT INTO cont_auditoria
			(
			contrato_id,
			nombre_tabla,
			valor_original,
			nombre_campo,
			nombre_menu_usuario,
			nombre_campo_usuario,
			tipo_valor,
			valor_varchar,
			valor_int,
			valor_date,
			valor_decimal,
			valor_select_option,
			valor_id_tabla,
			user_created_id,
			created_at
			)
			VALUES
			(
			'".$contrato_id."',
			'cont_contrato',
			'".$data_contrato['empresa']."',
			'empresa_suscribe_id',
			'Generales',
			'Empresa Arrendataria',
			'select_option',
			NULL,
			".$data_empresa['id'].",
			NULL,
			NULL,
			'".$data_empresa['empresa']."',
			NULL,
			".$usuario_id.",
			'".$created_at."'
			)";
	
			$mysqli->query($query_insert);
	
		if($mysqli->error){
			$result['status'] = 404;
			$result['message'] = 'A ocurrido un error, al subir la escisión';
			$result['result'] = $query_insert;
			echo json_encode($result);
			exit();
		}
	
		/// Update campos de auditoria
		$query_insert_escision = "INSERT INTO cont_contrato_escision (
			contrato_id,
			empresa_anterior_id,
			empresa_nueva_id,
			fecha,
			status,
			archivo_id,
			user_created_id,
			 created_at) VALUES (
				'" . $contrato_id . "',
				'".$data_contrato['empresa_suscribe_id']."',
				'".$data_empresa['id']."',
				'".$fecha_escision."',
				'1',
				".$archivo_id.",
				".$usuario_id.",
				'".$created_at."'
			 )";
	
		$mysqli->query($query_insert_escision);
		$contrato_escision_id = mysqli_insert_id($mysqli);
		/// Update empresa que suscribe
		$query_update = "UPDATE cont_contrato
		SET
			empresa_suscribe_id = ".$data_empresa['id']."
		WHERE contrato_id = '".$contrato_id."'";
		$mysqli->query($query_update);

		send_email_adenda_de_escision($contrato_escision_id);

		$result['status'] = 200;
		$result['message'] = 'Se ha subido el documento de la adenda correctamente';
		$result['result'] = $archivo_id;
		echo json_encode($result);
		exit();
	} catch (\Throwable $th) {
		$result['status'] = 404;
		$result['result'] = $th->getMessage();
		$result['message'] = 'A ocurrido un error, intentelo mas tarde.';
		echo json_encode($result);
		exit();
	}

	


}

if (isset($_POST["accion"]) && $_POST["accion"]==="eliminar_anexo") {

	$usuario_id = $login?$login['id']:null;

	if ( !((int) $usuario_id > 0) ) {
		$result["http_code"] = 500;
		$result["status"] = "error";
		$result["mensaje"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y hacer click e el boton verde: Agregar adenda firmada.";
		echo json_encode($result);
		die;
	}



	try{


		$updated_at = date("Y-m-d H:i:s");
		$archivo_id = $_POST["archivo_id"];
	
		$query_update = "UPDATE cont_archivos SET status = '0', user_updated_id = '".$usuario_id."', updated_at = '".$updated_at."' WHERE archivo_id = ".$archivo_id;
		$mysqli->query($query_update);

		$result['status'] = 200;
		$result['message'] = 'Se ha eliminado el documento correctamente';
		$result['result'] = $archivo_id;
		echo json_encode($result);
		exit();
	} catch (\Throwable $th) {
		$result['status'] = 404;
		$result['result'] = $th->getMessage();
		$result['message'] = 'A ocurrido un error, intentelo mas tarde.';
		echo json_encode($result);
		exit();
	}

	


}

if (isset($_POST["accion"]) && $_POST["accion"]==="eliminar_adenda_escision") {

	$usuario_id = $login?$login['id']:null;

	if ( !((int) $usuario_id > 0) ) {
		$result["http_code"] = 500;
		$result["status"] = "error";
		$result["mensaje"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y hacer click e el boton verde: Agregar adenda firmada.";
		echo json_encode($result);
		die;
	}



	try{


		$updated_at = date("Y-m-d H:i:s");
		$adenda_escision_id = $_POST["adenda_escision_id"];
		$archivo_id = 0;


		$query_select = "SELECT es.id, es.contrato_id, es.empresa_anterior_id, es.empresa_nueva_id, es.archivo_id, r1.nombre AS razon_social_nueva, r2.nombre AS razon_social_anterior
		FROM cont_contrato_escision AS es
		LEFT JOIN tbl_razon_social AS r1 ON r1.id = es.empresa_anterior_id
		LEFT JOIN tbl_razon_social AS r2 ON r2.id = es.empresa_nueva_id
		WHERE es.id = ".$adenda_escision_id;
		$sel_query = $mysqli->query($query_select);
		$data_escision = $sel_query->fetch_assoc();

		if (!isset($data_escision['id'])){
			$result['status'] = 404;
			$result['message'] = 'A ocurrido un error, al eliminar la adenda de escisión';
			$result['result'] = $archivo_id;
			echo json_encode($result);
			exit();
		}

		$update_contrato_escision = "UPDATE cont_contrato_escision SET status= '0', user_delete_id = '".$usuario_id."', delete_at = '".$updated_at."' WHERE  id = ".$data_escision['id'];
		$mysqli->query($update_contrato_escision);

		$update_contrato_escision = "UPDATE cont_archivos SET status = '0' WHERE archivo_id = ".$data_escision['archivo_id'];
		$mysqli->query($update_contrato_escision);

		/// Update campos de auditoria
		$query_insert = "INSERT INTO cont_auditoria
			(
			contrato_id,
			nombre_tabla,
			valor_original,
			nombre_campo,
			nombre_menu_usuario,
			nombre_campo_usuario,
			tipo_valor,
			valor_varchar,
			valor_int,
			valor_date,
			valor_decimal,
			valor_select_option,
			valor_id_tabla,
			user_created_id,
			created_at
			)
			VALUES
			(
			'".$data_escision['contrato_id']."',
			'cont_contrato',
			'".$data_escision['razon_social_anterior']."',
			'empresa_suscribe_id',
			'Generales',
			'Empresa Arrendataria',
			'select_option',
			NULL,
			'".$data_escision['empresa_nueva_id']."',
			NULL,
			NULL,
			'".$data_escision['razon_social_nueva']."',
			NULL,
			".$usuario_id.",
			'".$updated_at."'
			)";

			$mysqli->query($query_insert);

		if($mysqli->error){
			$result['status'] = 404;
			$result['message'] = 'A ocurrido un error, al eliminar la adenda de escición';
			$result['result'] = $query_insert;
			echo json_encode($result);
			exit();
		}

		/// Update empresa que suscribe
		$query_update = "UPDATE cont_contrato
		SET
			empresa_suscribe_id = ".$data_escision['empresa_anterior_id']."
		WHERE contrato_id = '".$data_escision['contrato_id']."'";
		$mysqli->query($query_update);

		send_email_eliminacion_adenda_de_escision($adenda_escision_id,false);

		$result['status'] = 200;
		$result['message'] = 'Se ha eliminado correctamente la adenda de escisión';
		$result['result'] = $archivo_id;
		echo json_encode($result);
		exit();
	} catch (\Throwable $th) {
		$result['status'] = 404;
		$result['result'] = $th->getMessage();
		$result['message'] = 'A ocurrido un error, intentelo mas tarde.';
		echo json_encode($result);
		exit();
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_objeto_adenda") {

	$usuario_id = $login?$login['id']:null;

	if ( !((int) $usuario_id > 0) ) {
		$result["http_code"] = 500;
		$result["status"] = "error";
		$result["mensaje"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y hacer click e el boton verde: Agregar adenda firmada.";
		echo json_encode($result);
		die;
	}



	try{


		$created_at = date("Y-m-d H:i:s");
		$contrato_id = $_POST["contrato_id"];
		$contrato_detalle_id = $_POST["contrato_detalle_id"];
		$objeto_adenda = $_POST["objeto_adenda"];
		$path = "/var/www/html/files_bucket/contratos/adendas/locales/";
		$name_file = 'modal_archivo_objeto_adenda';
		$archivo_id = 0;
	

	
		if (isset($_FILES[$name_file]) && $_FILES[$name_file]['error'] === UPLOAD_ERR_OK) {
			if (!is_dir($path)) mkdir($path, 0777, true);
	
			$filename = $_FILES[$name_file]['name'];
			$filenametem = $_FILES[$name_file]['tmp_name'];
			$filesize = $_FILES[$name_file]['size'];
			$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
			if($filename != ""){
				$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
				$nombre_archivo = $contrato_id . "_OBJETO_ADENDA_" . date('YmdHis') . "." . $fileExt;
				move_uploaded_file($filenametem, $path . $nombre_archivo);
				$comando = "INSERT INTO cont_archivos (
								contrato_id,
								tipo_archivo_id,
								nombre,
								extension,
								size,
								ruta,
								user_created_id,
								created_at)
							VALUES(
								'" . $contrato_id . "',
								174,
								'" . $nombre_archivo . "',
								'" . $fileExt . "',
								'" . $filesize . "',
								'" . $path . "',
								" . $usuario_id . ",
								'" . $created_at . "'
								)";
				$mysqli->query($comando);
				if($mysqli->error){
					$result['status'] = 404;
					$result['message'] = 'A ocurrido un error, al subir el documento adjunto';
					$result['result'] = $comando;
					echo json_encode($result);
					exit();
				}

				$archivo_id = mysqli_insert_id($mysqli);
			}
		}
	
		$query_insert_objeto = "INSERT INTO cont_contrato_objetos (contrato_id, contrato_detalle_id, archivo_id, objeto, status, created_at, user_created_id) VALUES (
			'".$contrato_id."', 
			'".$contrato_detalle_id."', 
			'".$archivo_id."', 
			'".replace_invalid_caracters($objeto_adenda)."', 
			'1', 
			'".date('Y-m-d H:i:s')."', 
			'".$login['id']."'
		);";
		$mysqli->query($query_insert_objeto);
		$contrato_objeto_id = mysqli_insert_id($mysqli);
	

		send_email_objeto_de_adenda($contrato_objeto_id);

		$result['status'] = 200;
		$result['message'] = 'Se ha registrado correctamente el objeto de la adenda';
		$result['result'] = $contrato_objeto_id;
		echo json_encode($result);
		exit();
	} catch (\Throwable $th) {
		$result['status'] = 404;
		$result['result'] = $th->getMessage();
		$result['message'] = 'A ocurrido un error, intentelo mas tarde.';
		echo json_encode($result);
		exit();
	}

	


}

if (isset($_POST["accion"]) && $_POST["accion"]==="subir_nuevo_contrato_firmado") {

	$usuario_id = $login?$login['id']:null;

	if ( !((int) $usuario_id > 0) ) {
		$result["http_code"] = 500;
		$result["status"] = "error";
		$result["mensaje"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y hacer click e el boton verde: Agregar adenda firmada.";
		echo json_encode($result);
		die;
	}

	try{


		$created_at = date("Y-m-d H:i:s");
		$contrato_id = (int) $_POST["contrato_id"];
		$contrato_detalle_id = (int) $_POST["contrato_detalle_id"];
		$tipo_contrato_id = (int) $_POST["tipo_contrato_id"];
		$tipo_archivo_id = (int) $_POST["tipo_archivo_id"];


		$path = "";
		$name_file = 'fileArchivo_contrato_firmado';
		$archivo_id = 0;

		if ($tipo_contrato_id == 1){
			$path = "/var/www/html/files_bucket/contratos/adendas/locales/";
		}else if($tipo_contrato_id == 2){
			$path = "/var/www/html/files_bucket/contratos/adendas/proveedores/";
		}else if($tipo_contrato_id == 5){
			$path = "/var/www/html/files_bucket/contratos/adendas/acuerdos/";
		}else if($tipo_contrato_id == 6){
			$path = "/var/www/html/files_bucket/contratos/adendas/agentes/";
		}else if($tipo_contrato_id == 7){
			$path = "/var/www/html/files_bucket/contratos/adendas/contratos_internos/";
		}


		if (isset($_FILES[$name_file]) && $_FILES[$name_file]['error'] === UPLOAD_ERR_OK) {
			if (!is_dir($path)) mkdir($path, 0777, true);

			$filename = $_FILES[$name_file]['name'];
			$filenametem = $_FILES[$name_file]['tmp_name'];
			$filesize = $_FILES[$name_file]['size'];
			$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
			if($filename != ""){
				$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
				$nombre_archivo = $contrato_id . "_CONTRATO_FIRMADO_" . date('YmdHis') . "." . $fileExt;
				move_uploaded_file($filenametem, $path . $nombre_archivo);
				$comando = "INSERT INTO cont_archivos (
								contrato_id,
								contrato_detalle_id,
								tipo_archivo_id,
								nombre,
								extension,
								size,
								ruta,
								user_created_id,
								created_at)
							VALUES(
								'" . $contrato_id . "',
								'" . $contrato_detalle_id . "',
								'" . $tipo_archivo_id . "',
								'" . $nombre_archivo . "',
								'" . $fileExt . "',
								'" . $filesize . "',
								'" . $path . "',
								" . $usuario_id . ",
								'" . $created_at . "'
								)";
				$mysqli->query($comando);
				if($mysqli->error){
					$result['status'] = 404;
					$result['message'] = 'A ocurrido un error, al subir el documento adjunto';
					$result['result'] = $comando;
					echo json_encode($result);
					exit();
				}

				$archivo_id = mysqli_insert_id($mysqli);
			}
		}

		send_email_adjuntar_contrato_firmado($archivo_id);

		$result['status'] = 200;
		$result['message'] = 'Se ha registrado correctamente el contrato firmado';
		$result['result'] = 0;
		echo json_encode($result);
		exit();
	} catch (\Throwable $th) {
		$result['status'] = 404;
		$result['result'] = $th->getMessage();
		$result['message'] = 'A ocurrido un error, intentelo mas tarde.';
		echo json_encode($result);
		exit();
	}




}

if (isset($_POST["accion"]) && $_POST["accion"]==="aprobar_rechazar_resolucion_contrato") 
{
	$usuario_id = $login?$login['id']:null;

	if ( !((int) $usuario_id > 0) ) {
		$error = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y hacer click e el boton: Cancelar Solicitud.";

		$result["status"] = 404;
		$result["result"] = [];
		$result["message"] = $error;
		echo json_encode($result);
		exit();
	}

	try {
		$query_resolucion = "SELECT r.estado_aprobacion_gerencia, r.contrato_detalle_id FROM cont_resolucion_contrato AS r WHERE r.id = ".$_POST["resolucion_contrato_id"];
		$result_resolucion = $mysqli->query($query_resolucion);
		$data_resolucion = $result_resolucion->fetch_assoc();
	
		if($data_resolucion['estado_aprobacion_gerencia'] == 1 || $data_resolucion['estado_aprobacion_gerencia'] == 2){
			$result["status"] = 404;
			$result["result"] = [];
			$result["message"] = 'La resolución de contrato ya fue atendida. Por favor actualize la pagina.';
			echo json_encode($result);
			exit();
		}
	
	
		$query_contrato = "SELECT c.contrato_id, c.tipo_contrato_id FROM cont_contrato AS c WHERE c.contrato_id = ".$_POST["contrato_id"];
		$result_contrato = $mysqli->query($query_contrato);
		$data_contrato = $result_contrato->fetch_assoc();
	
		
		$query_update_resolucion = "UPDATE cont_resolucion_contrato SET
			 aprobado_por = '".$usuario_id."',
			 estado_aprobacion_gerencia = '".$_POST['estado_resolucion']."',
			 fecha_aprobacion_gerencia = '".date('Y-m-d H:i:s')."'
			WHERE id = ".$_POST["resolucion_contrato_id"];
		$mysqli->query($query_update_resolucion);


		if ((int) $data_contrato['tipo_contrato_id'] == 1) {
			$query_update_contrato = "UPDATE 
				cont_contrato_detalle 
			SET 
				estado_resolucion = 1
			WHERE 
				id = ".$data_resolucion['contrato_detalle_id'];

			$mysqli->query($query_update_contrato);
		}else{
			$query_update_contrato = "UPDATE 
				cont_contrato 
			SET 
				estado_resolucion = 1
			WHERE 
				contrato_id = ".$data_contrato['contrato_id'];

			$mysqli->query($query_update_contrato);
		}
	


		$RESOLUCION_DE_CONTRATO = 3;
		$query_seguimiento = "SELECT id FROM cont_seguimiento_proceso_legal WHERE tipo_documento_id = ".$RESOLUCION_DE_CONTRATO." AND proceso_id = ".$_POST['resolucion_contrato_id']." AND status IN (1,2)";
		$list_query_seguimiento = $mysqli->query($query_seguimiento);
		$cant_seguimientos = $list_query_seguimiento->num_rows;
		if ($cant_seguimientos > 0) {
			//actualizar seguimiento proceso
			$seg_proceso = new SeguimientoProceso();
			$data_proceso['usuario_id'] = $usuario_id;
			$data_proceso['tipo_documento_id'] = $RESOLUCION_DE_CONTRATO;
			$data_proceso['proceso_id'] = $_POST["resolucion_contrato_id"];
			$data_proceso['proceso_detalle_id'] = 0;
			$data_proceso['estado_aprobacion'] = $_POST['estado_resolucion'];
			$seg_proceso->aprobar_rechazar_seguimiento_proceso($data_proceso);
		}

		if ((int) $_POST['estado_resolucion'] == 1){ // Cuando se aprueba la resolución de contrato
			send_email_solicitud_resolucion_contrato($_POST["resolucion_contrato_id"]);
		}
		

		$result["status"] = 200;
		$result["result"] = [];
		$result["message"] = 'Se ha modificado el estado de la aprobación de gerencia de la resolución de contrato';
		echo json_encode($result);
		exit();


	} catch (\Exception $e) {
		$result["status"] = 404;
		$result["result"] = [];
		$result["message"] = 'A ocurrido un error. Intentelo mas tarde.';
		echo json_encode($result);
		exit();
	}




}


if (isset($_POST["accion"]) && $_POST["accion"]==="subir_documento_resolucion_contrato") {

	$usuario_id = $login?$login['id']:null;

	if ( !((int) $usuario_id > 0) ) {
		$result["http_code"] = 500;
		$result["status"] = "error";
		$result["mensaje"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y hacer click e el boton verde: Agregar adenda firmada.";
		echo json_encode($result);
		die;
	}

	$created_at = date("Y-m-d H:i:s");
	$contrato_id = $_POST["contrato_id"];
	$query_select_empresa = "SELECT c.contrato_id, c.tipo_contrato_id FROM  cont_contrato as c WHERE c.contrato_id = ".$contrato_id;
	$sel_query = $mysqli->query($query_select_empresa);
	$data_contrato = $sel_query->fetch_assoc();

	$path = "";
	switch ($data_contrato['tipo_contrato_id']) {
		case '1': $path = "/var/www/html/files_bucket/contratos/resolucion_contratos_firmados/locales/"; break;
		case '2': $path = "/var/www/html/files_bucket/contratos/resolucion_contratos_firmados/proveedores/"; break;
		case '5': $path = "/var/www/html/files_bucket/contratos/resolucion_contratos_firmados/acuerdo_confidencialidad/"; break;
		case '6': $path = "/var/www/html/files_bucket/contratos/resolucion_contratos_firmados/agentes/";break;
		case '7': $path = "/var/www/html/files_bucket/contratos/resolucion_contratos_firmados/contratos_internos/"; break;
	}
	$name_file = 'fileArchivo_resolucion_contrato';
	$archivo_id = 0;
	if (isset($_FILES[$name_file]) && $_FILES[$name_file]['error'] === UPLOAD_ERR_OK) {
		if (!is_dir($path)) mkdir($path, 0777, true);

		$filename = $_FILES[$name_file]['name'];
		$filenametem = $_FILES[$name_file]['tmp_name'];
		$filesize = $_FILES[$name_file]['size'];
		$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
		if($filename != ""){
			$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
			$nombre_archivo = $contrato_id . "_RESOLUCION_CONTRATO_" . date('YmdHis') . "." . $fileExt;
			move_uploaded_file($filenametem, $path . $nombre_archivo);
			$comando = "INSERT INTO cont_archivos (
							contrato_id,
							tipo_archivo_id,
							nombre,
							extension,
							size,
							ruta,
							user_created_id,
							created_at)
						VALUES(
							'" . $contrato_id . "',
							105,
							'" . $nombre_archivo . "',
							'" . $fileExt . "',
							'" . $filesize . "',
							'" . $path . "',
							" . $usuario_id . ",
							'" . $created_at . "'
							)";
			$mysqli->query($comando);
			$archivo_id = mysqli_insert_id($mysqli);

			send_email_subir_documento_resolucion_contrato($archivo_id);

			$result['status'] = 200;
			$result['message'] = 'Se ha subido la resolución de contrato correctamente';
			$result['result'] = $archivo_id;
			echo json_encode($result);
			exit();


		}
	}

	$result['status'] = 404;
	$result['message'] = 'No se logro subir un documento';
	echo json_encode($result);
	exit();

}



if (isset($_POST["accion"]) && $_POST["accion"] === "subir_documento_autorizacion_mincetur") {
    $usuario_id = $login ? $login['id'] : null;

    if (!((int) $usuario_id > 0)) {
        $result["http_code"] = 500;
        $result["status"] = "error";
        $result["mensaje"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y hacer click e el boton verde: Agregar adenda firmada.";
        echo json_encode($result);
        die;
    }

    try {
        $created_at = date("Y-m-d H:i:s");
        $contrato_id = (int) $_POST["contrato_id"];
        $tipo_archivo_id = (int) $_POST["tipo_archivo_id_mincetur"];

        $path = "/var/www/html/files_bucket/contratos/solicitudes/locales/";
        $name_file = 'fileArchivo_requisitos_autorizacion_mincetur';

        if (isset($_FILES[$name_file]) && $_FILES[$name_file]['error'] === UPLOAD_ERR_OK) {
            if (!is_dir($path)) mkdir($path, 0777, true);

            $filename = $_FILES[$name_file]['name'];
            $filenametem = $_FILES[$name_file]['tmp_name'];
            $filesize = $_FILES[$name_file]['size'];
            $fileExt = pathinfo($filename, PATHINFO_EXTENSION);
            $nombre_archivo = $contrato_id . "_AUTORIZACION_MINCETUR_" . date('YmdHis') . "." . $fileExt;

					$comando = "INSERT INTO cont_archivos (
						contrato_id,
						tipo_archivo_id,
						nombre,
						extension,
						size,
						ruta,
						user_created_id,
						created_at)
					VALUES($contrato_id,
							$tipo_archivo_id,
							'$nombre_archivo',
							'$fileExt',
							$filesize,
							'$path',
							$usuario_id,
							'$created_at')";

			$accion = "Subir";

			$nombre_archivo_escaped = mysqli_real_escape_string($mysqli, $nombre_archivo);
			$descripcion = "Se ha subido el archivo.";

            $mysqli->query($comando);
			$archivo_id = mysqli_insert_id($mysqli);

			send_email_autorizacion_mincetur($archivo_id);

            if ($mysqli->error) {
                $result['status'] = 404;
                $result['message'] = 'Ocurrió un error al subir el documento adjunto.';
                $result['result'] = $comando;
                echo json_encode($result);
                exit();
            }

            move_uploaded_file($filenametem, $path . $nombre_archivo);

            $result['status'] = 200;
            $result['message'] = $mysqli->error ? 'Ocurrió un error al subir el archivo.' : 'Se ha subido correctamente el archivo.';
            $result['result'] = 0;
            echo json_encode($result);
            exit();
        }
    } catch (\Throwable $th) {
        $result['status'] = 404;
        $result['result'] = $th->getMessage();
        $result['message'] = 'Ha ocurrido un error, inténtelo más tarde.';
        echo json_encode($result);
        exit();
    }
}

if (isset($_POST["accion"]) && $_POST["accion"] === "reemplazar_documento_autorizacion_mincetur") {
    $usuario_id = $login ? $login['id'] : null;

    if (!((int) $usuario_id > 0)) {
        $result["http_code"] = 500;
        $result["status"] = "error";
        $result["mensaje"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y hacer click e el boton verde: Reemplazar.";
        echo json_encode($result);
        die;
    }

    try {
        $archivo_id = (int) $_POST["archivo_id"];
        $created_at = date("Y-m-d H:i:s");

        $path = "/var/www/html/files_bucket/contratos/solicitudes/locales/";
        $name_file = 'fileArchivo_reemplazar_autorizacion_mincetur';
        if (isset($_FILES[$name_file]) && $_FILES[$name_file]['error'] === UPLOAD_ERR_OK) {
            if (!is_dir($path)) mkdir($path, 0777, true);

            $filename = $_FILES[$name_file]['name'];
            $filenametem = $_FILES[$name_file]['tmp_name'];
            $filesize = $_FILES[$name_file]['size'];
            $fileExt = pathinfo($filename, PATHINFO_EXTENSION);
            $nombre_archivo = $archivo_id . "_AUTORIZACION_MINCETUR_" . date('YmdHis') . "." . $fileExt;

            // Actualizar el archivo en la base de datos
            $comando = "UPDATE cont_archivos SET 
                            nombre = '$nombre_archivo',
                            extension = '$fileExt',
                            size = $filesize,
                            ruta = '$path',
                            user_updated_id = $usuario_id,
                            updated_at = '$created_at'
                        WHERE archivo_id = $archivo_id";

            $accion = "Reemplazar";
            $descripcion = "Se ha reemplazado el archivo.";

            $mysqli->query($comando);

            if ($mysqli->error) {
                $result['status'] = 404;
                $result['message'] = 'Ocurrió un error al reemplazar el documento adjunto.';
                $result['result'] = $comando;
                echo json_encode($result);
                exit();
            }

            // Mover el nuevo archivo a la ubicación correcta
            move_uploaded_file($filenametem, $path . $nombre_archivo);

            $result['status'] = 200;
            $result['message'] = $mysqli->error ? 'Ocurrió un error al reemplazar el archivo.' : 'Se ha reemplazado correctamente el archivo.';
            $result['result'] = 0;
            echo json_encode($result);
            exit();
        }
    } catch (\Throwable $th) {
        $result['status'] = 404;
        $result['result'] = $th->getMessage();
        $result['message'] = 'Ha ocurrido un error, inténtelo más tarde.';
        echo json_encode($result);
        exit();
    }
}

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_eliminar_autorizacion_mincetur") {
    $usuario_id = $login ? $login['id'] : null;

    if (!((int) $usuario_id > 0)) {
        $result["http_code"] = 500;
        $result["status"] = "error";
        $result["mensaje"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y hacer click e el boton verde: Agregar adenda firmada.";
        echo json_encode($result);
        die;
    }

    try {
        $created_at = date("Y-m-d H:i:s");
        $archivo_id = (int) $_POST["archivo_id"];

        $query = "SELECT archivo_id, ruta, nombre FROM cont_archivos WHERE archivo_id = $archivo_id";
        $result_query = $mysqli->query($query);

        if ($result_query->num_rows > 0) {
            $row = $result_query->fetch_assoc();
            $archivo_id = $row['archivo_id'];
            $file_path = $row['ruta'] . $row['nombre'];

            if (file_exists($file_path)) {
                unlink($file_path);
            }

            $comando = "UPDATE cont_archivos SET status = 0 WHERE archivo_id = $archivo_id";
            $mysqli->query($comando);

            $nombre_archivo = $row['nombre'];

            $result['status'] = 200;
            $result['message'] = 'Se ha eliminado correctamente el archivo.';
            echo json_encode($result);
            exit();
        } else {
            $result['status'] = 404;
            $result['message'] = 'No se encontró ningún archivo activo para eliminar.';
            echo json_encode($result);
            exit();
        }
    } catch (\Throwable $th) {
        $result['status'] = 404;
        $result['result'] = $th->getMessage();
        $result['message'] = 'Ha ocurrido un error, inténtelo más tarde.';
        echo json_encode($result);
        exit();
    }
}

if (isset($_POST["accion"]) && $_POST["accion"] === "aprobar_rechazar_cambio") {
    $usuario_id = $login ? $login['id'] : null;

    if (!((int) $usuario_id > 0)) {
        $result["http_code"] = 500;
        $result["status"] = "error";
        $result["mensaje"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y hacer click e el boton verde: Agregar adenda firmada.";
        echo json_encode($result);
        die;
    }

    try {
        $created_at = date("Y-m-d H:i:s");
        $cambio_id = (int) $_POST["cambio_id"];
		$estado = (int) $_POST["estado"];
		if ($estado == 1) {
			$nombre_estado = 'Aprobado';
			$estado_aprobacion = 2;
		}else{
			$nombre_estado = 'Rechazado';
			$estado_aprobacion = 3;
		}

        $query_select = "SELECT * FROM cont_contrato_cambios WHERE id = $cambio_id";
        $result_query = $mysqli->query($query_select);

        if ($result_query->num_rows > 0) {
            $row = $result_query->fetch_assoc();
		
			if ($estado_aprobacion == 2) {//Aprobado

				$nombre_tabla = trim($row["nombre_tabla"]);
				$tipo_valor = trim($row["tipo_valor"]);
				$nombre_campo = trim($row["nombre_campo"]);
				$id_tabla = trim($row["valor_id_tabla"]);
				$contrato_id = $row['contrato_id'];

				if ($tipo_valor == 'varchar') {
					$nuevo_valor = $row['valor_varchar'];
				} else if ($tipo_valor == 'int') {
					$nuevo_valor = $row['valor_int'];
				} else if ($tipo_valor == 'date') {
					$nuevo_valor = $row['valor_date'];
				} else if ($tipo_valor == 'decimal') {
					$nuevo_valor = $row['valor_decimal'];
				} else if ($tipo_valor == 'select_option') {
					$nuevo_valor = $row['valor_int'];
				}

				if ($id_tabla > 0) {
					if($nombre_tabla == "cont_condicion_economica"){
						$query_id_tabla = 'condicion_economica_id';
					}else{
						$query_id_tabla = 'id';
					}
					$valor_id_tabla = $id_tabla;
				} else {
					$query_id_tabla = 'contrato_id';
					$valor_id_tabla = $contrato_id;
				}
				
				if (empty($nombre_tabla) || empty($nombre_campo) || empty($nuevo_valor) || empty($query_id_tabla) || empty($valor_id_tabla)) {
					$result['status'] = 404;
					$result['message'] = 'Algunos del los campos esta vacío.';
					echo json_encode($result);
					exit();
				} else if (substr($nombre_tabla,0,4) != 'cont') {
					$result['status'] = 404;
					$result['message'] = 'La tabla a actualizar no pertenece al sistema de contratos';
					echo json_encode($result);
					exit();
				} else {

					$query_update_contrato = "
					UPDATE " . $nombre_tabla . "
					SET
						" . $nombre_campo . " = " . $nuevo_valor . "
					WHERE " . $query_id_tabla . " = '" . $valor_id_tabla . "'";
					$mysqli->query($query_update_contrato);

					if($mysqli->error){
						$result['status'] = 404;
						$result['message'] = 'A ocurrido un error al actualizar el campo';
						echo json_encode($result);
						exit();
					}

					//OBTENER EL IDE DEL DETALLE DE CAMBIO
					$query_cont_detalle = "SELECT contrato_detalle_id FROM $nombre_tabla WHERE $query_id_tabla = $valor_id_tabla";
					$result_cont_detalle = $mysqli->query($query_cont_detalle);
					if($result_cont_detalle){
						$row_count = $result_cont_detalle->num_rows;
						if ($row_count > 0){
							$data_detalle = $result_cont_detalle->fetch_assoc();
							$query_update = "
							UPDATE cont_auditoria
							SET
								contrato_detalle_id = " . $data_detalle['contrato_detalle_id'] . "
							WHERE id = '" . $row['auditoria_id'] . "'";
							$mysqli->query($query_update);
						}
					}


					$query_update = "UPDATE cont_contrato_cambios SET 
						estado_aprobacion = '".$estado_aprobacion."',
						aprobado_por = '".$usuario_id."', 
						fecha_atencion = '".$created_at."' 
					WHERE id = ".$cambio_id;
					$mysqli->query($query_update);
				
					$query_update_auditoria = "UPDATE cont_auditoria SET status = 1 WHERE id = ".$row['auditoria_id'];
					$mysqli->query($query_update_auditoria);	
				}


				if($nombre_campo != 'abogado_id'){
					send_email_cambio_contrato($row['auditoria_id']);
				}
			}else{
				$query_update = "UPDATE cont_contrato_cambios SET 
					estado_aprobacion = '".$estado_aprobacion."',
					aprobado_por = '".$usuario_id."', 
					fecha_atencion = '".$created_at."' 
				WHERE id = ".$cambio_id;
				$mysqli->query($query_update);

				send_email_cambio_contrato_rechazado($row['auditoria_id']);
			}
			

            $result['status'] = 200;
            $result['message'] = 'Se ha '.$nombre_estado.' el cambio solicitado.';
            echo json_encode($result);
            exit();
        } 
		$result['status'] = 404;
		$result['message'] = 'Ha ocurrido un error, inténtelo más tarde.';
		echo json_encode($result);
		exit();
        
    } catch (\Throwable $th) {
        $result['status'] = 404;
        $result['result'] = $th->getMessage();
        $result['message'] = 'Ha ocurrido un error, inténtelo más tarde.';
        echo json_encode($result);
        exit();
    }
}

function send_email_solicitud_resolucion_contrato($resolucion_id)
{
	include("db_connect.php");
	include("sys_login.php");
	
	$host = $_SERVER["HTTP_HOST"];

	
	$sel_query = $mysqli->query("
	SELECT r.id, r.contrato_id, c.tipo_contrato_id, r.motivo, r.fecha_solicitud, 
	DATE_FORMAT(r.fecha_resolucion,'%d-%m-%Y') AS fecha_resolucion,
	DATE_FORMAT(r.fecha_carta,'%d-%m-%Y') AS fecha_carta,
	CONCAT(IFNULL(tpa.nombre, ''),' ',IFNULL(tpa.apellido_paterno, ''),	' ',	IFNULL(tpa.apellido_materno, '')) AS usuario_solicitud,
	r.anexo_archivo_id,
	r.archivo_id,
	CONCAT(IFNULL(tpa2.nombre, ''),' ',IFNULL(tpa2.apellido_paterno, ''),	' ',	IFNULL(tpa2.apellido_materno, '')) AS usuario_aprobado,
	r.fecha_resolucion_contrato_aprobado,
	r.status,
	DATE_FORMAT(r.created_at,'%d-%m-%Y %H:%i:%s') AS created_at,
	c.nombre_tienda,
	co.sigla, c.codigo_correlativo, tpa.correo, ar.nombre AS nombre_area,
	
	per.correo AS email_usuario_creacion_contrato,
	c.gerente_area_email,
	peg.correo AS email_del_gerente_area,
	puap.correo AS email_del_aprobante
		
	FROM cont_resolucion_contrato AS r
	INNER JOIN tbl_usuarios tu ON r.user_created_id = tu.id
	INNER JOIN tbl_personal_apt tpa ON tu.personal_id = tpa.id
	INNER JOIN tbl_areas AS ar ON tpa.area_id = ar.id
	
	LEFT JOIN tbl_usuarios tu2 ON r.usuario_resolucion_contrato_aprobado_id = tu2.id
	LEFT JOIN tbl_personal_apt tpa2 ON tu2.personal_id = tpa2.id

	INNER JOIN cont_contrato AS c ON c.contrato_id = r.contrato_id
	LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato

	LEFT JOIN tbl_usuarios u ON c.user_created_id = u.id
	LEFT JOIN tbl_personal_apt per ON u.personal_id = per.id

	LEFT JOIN tbl_usuarios uab ON r.abogado_id = uab.id
	LEFT JOIN tbl_personal_apt pab ON uab.personal_id = pab.id
	
	LEFT JOIN tbl_usuarios ug ON c.gerente_area_id = ug.id
	LEFT JOIN tbl_personal_apt peg ON ug.personal_id = peg.id

	LEFT JOIN tbl_usuarios uap ON c.aprobado_por = uap.id
	LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id

	WHERE r.id = ".$resolucion_id);
	


	$body = "";
	$body .= '<html>';

	$usuario_creacion_correo = '';
	$contrato_id = 0;
	$tipo_contrato_id = 0;
	$url_solicitud = "";
	$nombre_tienda = "";
	$correos_adicionales = [];
	while($sel = $sel_query->fetch_assoc())
	{
		$contrato_id = $sel['contrato_id']; 
		$tipo_contrato_id = $sel['tipo_contrato_id']; 
		$sigla_correlativo = $sel['sigla'];
		$codigo_correlativo = $sel['codigo_correlativo'];
		$motivo = $sel['motivo'];
		$usuario_solicitud = $sel['usuario_solicitud'];
		$fecha_solicitud = $sel['created_at'];
		$usuario_aprobado = $sel['usuario_aprobado'];
		$fecha_resolucion = $sel['fecha_resolucion'];
		$fecha_carta = $sel['fecha_carta'];
		$nombre_tienda = $sel['nombre_tienda'];

		
		if(!Empty($sel['correo'])){
			array_push($correos_adicionales, $sel['correo']);
		}
		

		if ($tipo_contrato_id == 2) { // contrato de proveedor
			if(!Empty($sel['email_usuario_creacion_contrato'])){
				array_push($correos_adicionales, $sel['email_usuario_creacion_contrato']);
			}
			if(!Empty($sel['gerente_area_email'])){
				array_push($correos_adicionales, $sel['gerente_area_email']);
			}
			if(!Empty($sel['email_del_gerente_area'])){
				array_push($correos_adicionales, $sel['email_del_gerente_area']);
			}
			if(!Empty($sel['email_del_aprobante'])){
				array_push($correos_adicionales, $sel['email_del_aprobante']);
			}
		}


		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

		$body .= '<thead>';
		
		$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>Nueva solicitud</b>';
			$body .= '</th>';
		$body .= '</tr>';
		
		$body .= '</thead>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Area Solicitante:</b></td>';
			$body .= '<td>'.$sel["nombre_area"].'</td>';
		$body .= '</tr>';
		
		
		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Motivo:</b></td>';
			$body .= '<td>'.$sel["motivo"].'</td>';
		$body .= '</tr>';
		if($tipo_contrato_id == 1){
			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Nombre de tienda:</b></td>';
			$body .= '<td>'.$nombre_tienda.'</td>';
			$body .= '</tr>';
		}

		if (!Empty($fecha_carta)) {
			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha de Carta:</b></td>';
			$body .= '<td>'.$fecha_carta.'</td>';
			$body .= '</tr>';
		}
		
		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha de Resolución:</b></td>';
			$body .= '<td>'.$sel["fecha_resolucion"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Usuario solicitante:</b></td>';
		$body .= '<td>'.$sel["usuario_solicitud"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha Solicitud:</b></td>';
			$body .= '<td>'.$sel["created_at"].'</td>';
		$body .= '</tr>';

		
		$body .= '</table>';
		$body .= '</div>';

	}
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		switch ($tipo_contrato_id) {
			case '1': $url_solicitud = "detalle_solicitudv2"; break;
			case '2': $url_solicitud = "detalleSolicitudProveedor"; break;
			case '5': $url_solicitud = "detalle_solicitudv2_acuerdo_confidencialidad"; break;
			case '6': $url_solicitud = "detalle_agente"; break;
			case '7': $url_solicitud = "detalle_solicitudv2_interno"; break;
		}

		$body .= '<div style="width: 700px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$host.'/?sec_id=contrato&sub_sec_id='.$url_solicitud.'&id='.$contrato_id.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
				$body .= '<b>Ver Solicitud</b>';
		    $body .= '</a>';
		$body .= '</div>';

		$body .= '</html>';
		$body .= "";

	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_solicitud_resolucion_contrato($correos_adicionales);

	$cc = $lista_correos['cc'];
	$nombre_tipo_contrato = "";
	switch ($tipo_contrato_id) {
		case '1': $nombre_tipo_contrato = "Contrato de Arrendamiento"; break;
		case '2': $nombre_tipo_contrato = "Contrato de Proveedor"; break;
		case '5': $nombre_tipo_contrato = "Contrato de Acuerdo de Confidencialidad"; break;
		case '6': $nombre_tipo_contrato = "Contrato de Agente"; break;
		case '7': $nombre_tipo_contrato = "Contrato Interno"; break;
	}
	$titulo_email = "Gestion - Sistema Contratos - Inicio Proceso Legal - Nueva Solicitud de Resolución de ".$nombre_tipo_contrato.": ".$nombre_tienda." Código - ";

	$bcc = $lista_correos['bcc'];

	$request = [
		"subject" => $titulo_email.$sigla_correlativo.$codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try 
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;        
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc) 
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc) 
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();
		//return true;

	} 
	catch (Exception $e) 
	{
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}

	
}

function send_email_subir_documento_resolucion_contrato($archivo_id)
{
	include("db_connect.php");
	include("sys_login.php");
	
	$host = $_SERVER["HTTP_HOST"];

	
	$sel_query = $mysqli->query("SELECT ca.archivo_id, 

		DATE_FORMAT(ca.created_at,'%d-%m-%Y %H:%i:%s') AS created_at,
		CONCAT(IFNULL(tpa.nombre, ''),' ',IFNULL(tpa.apellido_paterno, ''),	' ',	IFNULL(tpa.apellido_materno, '')) AS usuario_solicitud_archivo,
		CONCAT(IFNULL(per.nombre, ''),' ',IFNULL(per.apellido_paterno, ''),	' ',	IFNULL(per.apellido_materno, '')) AS usuario_solicitud_contrato,
		c.contrato_id,
		c.tipo_contrato_id,
		CONCAT(IFNULL(co.sigla,''), c.codigo_correlativo) as codigo,
		tpa.correo as correo_solicitante_contrato,
		per.correo as correo_solicitante_archivo,
		c.nombre_tienda,
		c.gerente_area_email,
		peg.correo AS email_del_gerente_area,
		pab.correo AS correo_abogado,
		puap.correo AS email_del_aprobante
			
		FROM cont_archivos ca 
		INNER JOIN tbl_usuarios tu ON ca.user_created_id = tu.id
		INNER JOIN tbl_personal_apt tpa ON tu.personal_id = tpa.id

		INNER JOIN cont_contrato AS c ON c.contrato_id = ca.contrato_id
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato

		LEFT JOIN tbl_usuarios u ON c.user_created_id = u.id
		LEFT JOIN tbl_personal_apt per ON u.personal_id = per.id

		LEFT JOIN tbl_usuarios ug ON c.gerente_area_id = ug.id
		LEFT JOIN tbl_personal_apt peg ON ug.personal_id = peg.id

		LEFT JOIN tbl_usuarios uab ON c.abogado_id = uab.id
		LEFT JOIN tbl_personal_apt pab ON uab.personal_id = pab.id

		LEFT JOIN tbl_usuarios uap ON c.aprobado_por = uap.id
		LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id

		WHERE ca.archivo_id = ".$archivo_id);

	$body = "";
	$body .= '<html>';

	$usuario_creacion_correo = '';
	$contrato_id = 0;
	$tipo_contrato_id = 0;
	$url_solicitud = "";
	$nombre_tienda = "";
	$correos_adicionales = [];
	$codigo_contrato = "";
	while($sel = $sel_query->fetch_assoc())
	{
		$contrato_id = $sel['contrato_id']; 
		$tipo_contrato_id = $sel['tipo_contrato_id']; 
		$nombre_tienda = $sel['nombre_tienda'];
		$codigo_contrato = $sel['codigo'];

		
		if(!Empty($sel['correo_solicitante_contrato'])){
			array_push($correos_adicionales, $sel['correo_solicitante_contrato']);
		}
		if(!Empty($sel['correo_solicitante_archivo'])){
			array_push($correos_adicionales, $sel['correo_solicitante_archivo']);
		}
		if(!Empty($sel['correo_abogado'])){
			array_push($correos_adicionales, $sel['correo_abogado']);
		}

		if ($tipo_contrato_id == 2) { // contrato de proveedor
			if(!Empty($sel['gerente_area_email'])){
				array_push($correos_adicionales, $sel['gerente_area_email']);
			}
			if(!Empty($sel['email_del_gerente_area'])){
				array_push($correos_adicionales, $sel['email_del_gerente_area']);
			}
			if(!Empty($sel['email_del_aprobante'])){
				array_push($correos_adicionales, $sel['email_del_aprobante']);
			}
		}


		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

		$body .= '<thead>';
		
		$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>Nuevo Archivo - Resolución de Contrato</b>';
			$body .= '</th>';
		$body .= '</tr>';
		
		$body .= '</thead>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Código:</b></td>';
			$body .= '<td>'.$sel["codigo"].'</td>';
		$body .= '</tr>';
		
		if($tipo_contrato_id == 1){
			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Nombre de tienda:</b></td>';
			$body .= '<td>'.$nombre_tienda.'</td>';
			$body .= '</tr>';
		}

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Usuario solicitante:</b></td>';
		$body .= '<td>'.$sel["usuario_solicitud_archivo"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha:</b></td>';
			$body .= '<td>'.$sel["created_at"].'</td>';
		$body .= '</tr>';

		
		$body .= '</table>';
		$body .= '</div>';

	}
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		switch ($tipo_contrato_id) {
			case '1': $url_solicitud = "detalle_solicitudv2"; break;
			case '2': $url_solicitud = "detalleSolicitudProveedor"; break;
			case '5': $url_solicitud = "detalle_solicitudv2_acuerdo_confidencialidad"; break;
			case '6': $url_solicitud = "detalle_agente"; break;
			case '7': $url_solicitud = "detalle_solicitudv2_interno"; break;
		}

		$body .= '<div style="width: 700px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$host.'/?sec_id=contrato&sub_sec_id='.$url_solicitud.'&id='.$contrato_id.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
				$body .= '<b>Ver Contrato</b>';
		    $body .= '</a>';
		$body .= '</div>';

		$body .= '</html>';
		$body .= "";

	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_subir_documento_resolucion_contrato($correos_adicionales);

	$cc = $lista_correos['cc'];
	$nombre_tipo_contrato = "";
	switch ($tipo_contrato_id) {
		case '1': $nombre_tipo_contrato = "Contrato de Arrendamiento"; break;
		case '2': $nombre_tipo_contrato = "Contrato de Proveedor"; break;
		case '5': $nombre_tipo_contrato = "Contrato de Acuerdo de Confidencialidad"; break;
		case '6': $nombre_tipo_contrato = "Contrato de Agente"; break;
		case '7': $nombre_tipo_contrato = "Contrato Interno"; break;
	}
	$titulo_email = "Gestion - Sistema Contratos - Nueva Archivo - Resolución de Contrato - ".$nombre_tipo_contrato.": ".$nombre_tienda." Código - ";

	$bcc = $lista_correos['bcc'];

	$request = [
		"subject" => $titulo_email.$codigo_contrato,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try 
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;        
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc) 
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc) 
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();
		//return true;

	} 
	catch (Exception $e) 
	{
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}

	
}

function send_email_adjuntar_contrato_firmado($archivo_id, $reenvio = false)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$query_contrato_firmado = "SELECT 
		a.archivo_id,
		c.nombre_tienda,
		c.cc_id,
		c.contrato_id,
		c.tipo_contrato_id,
		CONCAT(IFNULL(co.sigla,''),c.codigo_correlativo) AS codigo_contrato,
		c.nombre_agente,
		c.c_costos,
		concat(IFNULL(per.nombre, ''),' ', IFNULL(per.apellido_paterno, '')) AS usuario_creacion,
		a.created_at,
		per.correo
	FROM cont_archivos AS a
	INNER JOIN cont_contrato AS c ON c.contrato_id = a.contrato_id
	LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
	INNER JOIN tbl_usuarios u ON a.user_created_id = u.id
	INNER JOIN tbl_personal_apt per ON u.personal_id = per.id
	WHERE a.archivo_id = ". $archivo_id;
	$sel_query = $mysqli->query($query_contrato_firmado);

	$body = "";
	$body .= '<html>';

	$envio_correo = true;
	$contrato_id = 0;
	$tipo_contrato_id = 0;
	$cc_id = "";
	$nombre = "";
	$codigo = "";
	$usuario_correo = "";

	while($sel = $sel_query->fetch_assoc())
	{

		$contrato_id = $sel['contrato_id'];
		$tipo_contrato_id = $sel['tipo_contrato_id'];
		$codigo = $sel['codigo_contrato'];
		$usuario_correo = $sel['correo'];

		if ($tipo_contrato_id == 6){
			$cc_id = $sel['c_costos'];
			$nombre = $sel['nombre_agente'];
		}

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

		$body .= '<thead>';

		$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>Datos Generales</b>';
			$body .= '</th>';
		$body .= '</tr>';


		$body .= '</thead>';

		if ($tipo_contrato_id == 6){
			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Centro de Costo:</b></td>';
				$body .= '<td>'.$cc_id.'</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Nombre del Agente:</b></td>';
				$body .= '<td>'.$nombre.'</td>';
			$body .= '</tr>';
		}



		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Usuario Responsable:</b></td>';
			$body .= '<td>'.$sel["usuario_creacion"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha de Registro:</b></td>';
			$body .= '<td>'.$sel["created_at"].'</td>';
		$body .= '</tr>';


		$body .= '</table>';
		$body .= '</div>';


	}
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		if ($tipo_contrato_id == 6){
			$url_solicitud = $host.'/?sec_id=contrato&sub_sec_id=detalle_agente&id='.$contrato_id;
		}



		$body .= '<div style="width: 700px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$url_solicitud.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
				$body .= '<b>Ver Contrato</b>';
		    $body .= '</a>';
		$body .= '</div>';

		$body .= '</html>';
		$body .= "";

	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));

	$add_correos = [];
	if (!Empty($usuario_correo)){
		array_push($add_correos, $usuario_correo);
	}

	if ($tipo_contrato_id == 6){
		$lista_correos = $correos->send_email_solicitud_contrato_agente_firmada($add_correos);
		$titulo_email = "Gestion - Sistema Contratos - Nuevo Contrato Firmado Adjunto de Agentes: ".$nombre.": Centro de Costo - ".$cc_id.": Código - ".$codigo;
		if ($reenvio){
			$titulo_email = "Gestion - Sistema Contratos - Reenviar Contrato Firmado Adjunto de Agentes: ".$nombre.": Centro de Costo - ".$cc_id.": Código - ".$codigo;
		}
	}

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];

	$request = [
		"subject" => $titulo_email,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc)
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc)
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();
		//return true;

	}
	catch (Exception $e)
	{
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}


}

function send_email_adenda_de_escision($contrato_escision_id, $reenvio = false)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$query_escision = "SELECT ces.id, ces.contrato_id,
						CONCAT(IFNULL(p.nombre, ''), ' ',  IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS usuario,
						rz1.nombre AS empresa_anterior,
						rz2.nombre AS empresa_nueva,
						ces.fecha AS fecha_escision,
						ces.created_at,
						c.nombre_tienda,
						c.cc_id,
						CONCAT(IFNULL(co.sigla,''),c.codigo_correlativo) codigo
						
						FROM cont_contrato_escision AS ces
						INNER JOIN cont_contrato AS c ON c.contrato_id = ces.contrato_id
						LEFT JOIN cont_correlativo AS co ON co.tipo_contrato = c.tipo_contrato_id
						INNER JOIN tbl_razon_social AS rz1 ON rz1.id = ces.empresa_anterior_id
						INNER JOIN tbl_razon_social AS rz2 ON rz2.id = ces.empresa_nueva_id
						INNER JOIN tbl_usuarios u ON ces.user_created_id = u.id
						INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
						WHERE ces.id = ". $contrato_escision_id;
						$sel_query = $mysqli->query($query_escision);

	$body = "";
	$body .= '<html>';

	$envio_correo = true;
	$contrato_id = 0;
	$nombre_tienda = "";
	$codigo = "";
	while($sel = $sel_query->fetch_assoc())
	{
		
		$contrato_id = $sel['contrato_id'];
		$nombre_tienda = $sel['nombre_tienda'];
		$codigo = $sel['codigo'];

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

		$body .= '<thead>';

		$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>Datos Generales</b>';
			$body .= '</th>';
		$body .= '</tr>';


		$body .= '</thead>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Centro de Costo:</b></td>';
			$body .= '<td>'.$sel["cc_id"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Nombre de la Tienda:</b></td>';
			$body .= '<td>'.$sel["nombre_tienda"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Empresa Anterior:</b></td>';
			$body .= '<td>'.$sel["empresa_anterior"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Empresa Nueva:</b></td>';
			$body .= '<td>'.$sel["empresa_nueva"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Fecha de Escisión:</b></td>';
			$body .= '<td>'.$sel["fecha_escision"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Usuario Responsable:</b></td>';
			$body .= '<td>'.$sel["usuario"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha de Registro:</b></td>';
			$body .= '<td>'.$sel["created_at"].'</td>';
		$body .= '</tr>';


		$body .= '</table>';
		$body .= '</div>';


	}
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$url_solicitud = $host.'/?sec_id=contrato&sub_sec_id=detalle_solicitudv2&id='.$contrato_id;
	

		$body .= '<div style="width: 700px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$url_solicitud.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
				$body .= '<b>Ver Adenda</b>';
		    $body .= '</a>';
		$body .= '</div>';

		$body .= '</html>';
		$body .= "";

	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_adenda_arrendamiento_escision([]);

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];

	$titulo_email = "Gestion - Sistema Contratos - Adenda de Escisión Firmada - ".$nombre_tienda." : Código - ";
	if ($reenvio){
		$titulo_email = "Gestion - Sistema Contratos - Reenvio - Adenda de Escisión Firmada - ".$nombre_tienda." : Código - ";
	}

	$request = [
		"subject" => $titulo_email.$codigo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc)
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc)
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();
		//return true;

	}
	catch (Exception $e)
	{
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}


}

function send_email_eliminacion_adenda_de_escision($contrato_escision_id, $reenvio = false)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$query_select = "SELECT es.id, es.contrato_id, es.empresa_anterior_id, es.empresa_nueva_id, es.archivo_id, 
	r1.nombre AS empresa_nueva, r2.nombre AS empresa_anterior, c.cc_id, c.nombre_tienda,
	CONCAT(IFNULL(p.nombre, ''), ' ',  IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS usuario,
	es.delete_at,	CONCAT(IFNULL(co.sigla,''),c.codigo_correlativo) codigo
	FROM cont_contrato_escision AS es
	INNER JOIN cont_contrato AS c ON c.contrato_id = es.contrato_id	
	LEFT JOIN cont_correlativo AS co ON co.tipo_contrato = c.tipo_contrato_id
	LEFT JOIN tbl_razon_social AS r1 ON r1.id = es.empresa_anterior_id
	LEFT JOIN tbl_razon_social AS r2 ON r2.id = es.empresa_nueva_id
	INNER JOIN tbl_usuarios u ON es.user_delete_id = u.id
	INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
	WHERE es.id = ".$contrato_escision_id;
    $sel_query = $mysqli->query($query_select);

	$body = "";
	$body .= '<html>';

	$contrato_id = 0;
	$nombre_tienda = "";
	$codigo = "";
	while($sel = $sel_query->fetch_assoc())
	{

		$contrato_id = $sel['contrato_id'];
		$nombre_tienda = $sel['nombre_tienda'];
		$codigo = $sel['codigo'];

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

		$body .= '<thead>';

		$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>Datos Generales</b>';
			$body .= '</th>';
		$body .= '</tr>';

		$body .= '</thead>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Centro de Costo:</b></td>';
			$body .= '<td>'.$sel["cc_id"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Nombre de la Tienda:</b></td>';
			$body .= '<td>'.$sel["nombre_tienda"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Empresa Anterior:</b></td>';
			$body .= '<td>'.$sel["empresa_anterior"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Empresa Nueva:</b></td>';
			$body .= '<td>'.$sel["empresa_nueva"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Usuario Responsable:</b></td>';
			$body .= '<td>'.$sel["usuario"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha de Eliminación:</b></td>';
			$body .= '<td>'.$sel["delete_at"].'</td>';
		$body .= '</tr>';


		$body .= '</table>';
		$body .= '</div>';


	}
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$url_solicitud = $host.'/?sec_id=contrato&sub_sec_id=detalle_solicitudv2&id='.$contrato_id;


		$body .= '<div style="width: 700px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$url_solicitud.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
				$body .= '<b>Ver Adenda</b>';
		    $body .= '</a>';
		$body .= '</div>';

		$body .= '</html>';
		$body .= "";

	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_eliminacion_adenda_de_escision([]);

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];

	$titulo_email = "Gestion - Sistema Contratos - Eliminación de Adenda de Arrendamiento de Escisión ".$nombre_tienda." : Código - ";
	if ($reenvio){
		$titulo_email = "Gestion - Sistema Contratos - Reenvio - Eliminación de Adenda de Arrendamiento de Escisión ".$nombre_tienda." : Código - ";
	}

	$request = [
		"subject" => $titulo_email.$codigo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc)
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc)
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();
		//return true;

	}
	catch (Exception $e)
	{
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}


}

function send_email_objeto_de_adenda($objeto_adenda_id, $reenvio = false)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$query_objeto = "SELECT oa.id, oa.contrato_id,
	CONCAT(IFNULL(p.nombre, ''), ' ',  IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS usuario,
	oa.created_at,
	CONCAT(IFNULL(co.sigla,''),c.codigo_correlativo) codigo,
	oa.objeto
	
	FROM cont_contrato_objetos AS oa
	INNER JOIN cont_contrato AS c ON c.contrato_id = oa.contrato_id
	LEFT JOIN cont_correlativo AS co ON co.tipo_contrato = c.tipo_contrato_id
	INNER JOIN tbl_usuarios u ON oa.user_created_id = u.id
	INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
	WHERE oa.id = ". $objeto_adenda_id;
	$sel_query = $mysqli->query($query_objeto);

	$body = "";
	$body .= '<html>';

	$envio_correo = true;
	$contrato_id = 0;
	$nombre_tienda = "";
	$codigo = "";
	while($sel = $sel_query->fetch_assoc())
	{
		
		$contrato_id = $sel['contrato_id'];
		$codigo = $sel['codigo'];

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

		$body .= '<thead>';

		$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>Datos Generales</b>';
			$body .= '</th>';
		$body .= '</tr>';


		$body .= '</thead>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Objeto de Adenda:</b></td>';
			$body .= '<td>'.$sel["objeto"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Usuario Responsable:</b></td>';
			$body .= '<td>'.$sel["usuario"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha de Registro:</b></td>';
			$body .= '<td>'.$sel["created_at"].'</td>';
		$body .= '</tr>';


		$body .= '</table>';
		$body .= '</div>';


	}
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$url_solicitud = $host.'/?sec_id=contrato&sub_sec_id=detalleSolicitudProveedor&id='.$contrato_id;
	

		$body .= '<div style="width: 700px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$url_solicitud.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
				$body .= '<b>Ver Contrato Proveedor</b>';
		    $body .= '</a>';
		$body .= '</div>';

		$body .= '</html>';
		$body .= "";

	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_objeto_adenda_proveedor([]);

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];

	$titulo_email = "Gestion - Sistema Contratos - Nuevo Objeto de Adenda de Proveedor: Código - ";
	if ($reenvio){
		$titulo_email = "Gestion - Sistema Contratos - Renvío de Nuevo Objeto de Adenda de Proveedor: Código - ";
	}

	$request = [
		"subject" => $titulo_email.$codigo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc)
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc)
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();
		//return true;

	}
	catch (Exception $e)
	{
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}


}

function send_email_cambiar_estado_contrato($contrato_estado_id, $reenvio = false)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$query_objeto = "SELECT ce.id, ce.contrato_id,
	CONCAT(IFNULL(p.nombre, ''), ' ',  IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS usuario,
	DATE_FORMAT(ce.created_at,'%d-%m-%Y %H:%i:%s') AS created_at,
	CONCAT(IFNULL(co.sigla,''),c.codigo_correlativo) codigo,
	ce.motivo,
	c.tipo_contrato_id,
	c.status,
	tc.nombre AS tipo_contrato

	
	FROM cont_contrato_estado AS ce
	INNER JOIN cont_contrato AS c ON c.contrato_id = ce.contrato_id
	INNER JOIN cont_tipo_contrato AS tc ON tc.id = c.tipo_contrato_id
	LEFT JOIN cont_correlativo AS co ON co.tipo_contrato = c.tipo_contrato_id
	INNER JOIN tbl_usuarios u ON ce.user_created_id = u.id
	INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
	WHERE ce.id = ". $contrato_estado_id;
	$sel_query = $mysqli->query($query_objeto);

	$body = "";
	$body .= '<html>';

	$envio_correo = true;
	$contrato_id = 0;
	$nombre_tienda = "";
	$nombre_contrato = "";
	$codigo = "";
	while($sel = $sel_query->fetch_assoc())
	{
		
		$contrato_id = $sel['contrato_id'];
		$codigo = $sel['codigo'];
		$tipo_contrato_id = $sel['tipo_contrato_id'];
		$estado = $sel['status'] == 1 ? 'Activo':'Inactivo';
		

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

		$body .= '<thead>';

		$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>Datos Generales</b>';
			$body .= '</th>';
		$body .= '</tr>';


		$body .= '</thead>';


		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Usuario Responsable:</b></td>';
			$body .= '<td>'.$sel["usuario"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Estado:</b></td>';
			$body .= '<td>'.$estado.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Motivo:</b></td>';
			$body .= '<td>'.$sel["motivo"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha de Registro:</b></td>';
			$body .= '<td>'.$sel["created_at"].'</td>';
		$body .= '</tr>';


		$body .= '</table>';
		$body .= '</div>';


	}
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';


		if ($tipo_contrato_id == 1){
			$nombre_tipo_contrato = "Contrato de Arrendamiento";
			$url_solicitud = $host.'/?sec_id=contrato&sub_sec_id=detalle_solicitudv2&id='.$contrato_id;	
		}else if($tipo_contrato_id == 2){
			$nombre_tipo_contrato = "Contrato de Proveedor";
			$url_solicitud = $host.'/?sec_id=contrato&sub_sec_id=detalleSolicitudProveedor&id='.$contrato_id;
		}else if($tipo_contrato_id == 5){
			$nombre_tipo_contrato = "Acuerdo de Confidencialidad";
			$url_solicitud = $host.'/?sec_id=contrato&sub_sec_id=detalle_solicitudv2_acuerdo_confidencialidad&id='.$contrato_id;
		}else if($tipo_contrato_id == 6){
			$nombre_tipo_contrato = "Contrato de Agente";
			$url_solicitud = $host.'/?sec_id=contrato&sub_sec_id=detalle_agente&id='.$contrato_id;
		}else if($tipo_contrato_id == 7){
			$nombre_tipo_contrato = "Contrato Interno";
			$url_solicitud = $host.'/?sec_id=contrato&sub_sec_id=detalle_solicitudv2_interno&id='.$contrato_id;
		}

		
	

		$body .= '<div style="width: 700px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$url_solicitud.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
				$body .= '<b>Ver Contrato Proveedor</b>';
		    $body .= '</a>';
		$body .= '</div>';

		$body .= '</html>';
		$body .= "";

	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_cambiar_estado_contrato([]);

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];

	$titulo_email = "Gestion - Sistema Contratos - Cambio Estado - ".$nombre_tipo_contrato.": Código - ";
	if ($reenvio){
		$titulo_email = "Gestion - Sistema Contratos - Reenvio Cambio Estado - ".$nombre_tipo_contrato.": Código - ";
	}

	$request = [
		"subject" => $titulo_email.$codigo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc)
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc)
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();
		//return true;

	}
	catch (Exception $e)
	{
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}


}

function send_email_autorizacion_mincetur($archivo_id, $reenvio = false)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];


	$query_mincetur = "SELECT 
			a.archivo_id,
			a.contrato_id,
			concat(IFNULL(per.nombre, ''),' ', IFNULL(per.apellido_paterno, '')) AS usuario_creacion,
			a.created_at,
			per.correo,
			COALESCE(cc.nombre_tienda, cc.nombre_agente) AS nombre_tienda,
			cc.cc_id,
			CONCAT(IFNULL(co.sigla,''),cc.codigo_correlativo) AS codigo_contrato
		FROM cont_archivos AS a
		INNER JOIN tbl_usuarios u ON a.user_created_id = u.id
		INNER JOIN tbl_personal_apt per ON u.personal_id = per.id
		INNER JOIN cont_contrato cc ON a.contrato_id = cc.contrato_id
		LEFT JOIN cont_correlativo co ON cc.tipo_contrato_id = co.tipo_contrato
		WHERE a.archivo_id = ". $archivo_id;

	$sel_query = $mysqli->query($query_mincetur);
	$contrato_id = 0;
	$nombre_tienda = "";
	$codigo = "";
	$body = "";
	$body .= '<html>';

	$envio_correo = true;
	while($sel = $sel_query->fetch_assoc())
	{
		$contrato_id = $sel['contrato_id'];
		$nombre_tienda = $sel['nombre_tienda'];
		$codigo = $sel['codigo_contrato'];

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

		$body .= '<thead>';

		$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>Datos Generales</b>';
			$body .= '</th>';
		$body .= '</tr>';
		$body .= '</thead>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Nombre de la Tienda:</b></td>';
			$body .= '<td>'.$sel["nombre_tienda"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Usuario Responsable:</b></td>';
			$body .= '<td>'.$sel["usuario_creacion"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha de Registro:</b></td>';
			$body .= '<td>'.$sel["created_at"].'</td>';
		$body .= '</tr>';


		$body .= '</table>';
		$body .= '</div>';


	}
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$url_solicitud = $host.'/?sec_id=contrato&sub_sec_id=detalle_solicitudv2&id='.$contrato_id;
	

		$body .= '<div style="width: 700px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$url_solicitud.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
				$body .= '<b>Ver Autorización Mincetur</b>';
		    $body .= '</a>';
		$body .= '</div>';

		$body .= '</html>';
		$body .= "";

	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_autorizacion_mincetur([]);

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];

	$titulo_email = "Gestion - Sistema Contratos - Autorización Mincetur - ".$nombre_tienda." : Código - ";
	if ($reenvio){
		$titulo_email = "Gestion - Sistema Contratos - Reenvio - Autorización Mincetur - ".$nombre_tienda." : Código - ";
	}

	$request = [
		"subject" => $titulo_email.$codigo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc)
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc)
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();
		//return true;

	}
	catch (Exception $e)
	{
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}


}

function guardar_comprobante_proveedor($contrato_id)
{
	include("db_connect.php");
	include("sys_login.php");

	$usuario_id = $login?$login['id']:null;

	if($usuario_id == null){
		return false;
	}

	$query = "SELECT cc.ruc, cc.razon_social FROM cont_contrato cc 
	WHERE cc.contrato_id  = ".$contrato_id;
	$sel_query = $mysqli->query($query);
	$proveedor = $sel_query->fetch_assoc();
	if (isset($proveedor['ruc']) && isset($proveedor['razon_social']) && !empty($proveedor['ruc']) && !empty($proveedor['razon_social'])) {
		$sel_query_proveedor = "SELECT  * FROM tbl_comprobante_proveedor tcp where tcp.status = 1 AND tcp.ruc  = '".$proveedor['ruc']."'";
		$query_proveedor = $mysqli->query($sel_query_proveedor);
		$row_count = $query_proveedor->num_rows;
		if ($row_count > 0) {
			return false;
		}
		if (strlen($proveedor['ruc']) != 11){
			return false;
		}

		$query_insert = "INSERT INTO tbl_comprobante_proveedor (
				nombre,
				ruc,
				status,
				user_created_id,
				created_at)
			VALUES (
				'".$proveedor['razon_social']."',
				'".$proveedor['ruc']."',
				1,
				".$usuario_id.",
				'".date('Y-m-d H:i:s')."'
			)";
		$mysqli->query($query_insert);
	}
	return true;
}

echo json_encode($result);
?>