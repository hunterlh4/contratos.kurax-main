<?php  
date_default_timezone_set("America/Lima");

include("db_connect.php");
include("sys_login.php");

if (isset($_POST["anuncios_formulario_anuncios"])) {
	$message = "";
	$status = true;

	$anuncios_texto = $_POST["anuncios_texto"];
	
	$anuncios_fecha_desde = $_POST["anuncios_fecha_desde"];
	$anuncios_fecha_desde = date("Y-m-d", strtotime($anuncios_fecha_desde));

	$anuncios_fecha_hasta = $_POST["anuncios_fecha_hasta"];
	$anuncios_fecha_hasta = date("Y-m-d", strtotime($anuncios_fecha_hasta));
    $anuncios_dias_semana = $_POST["anuncios_dias_semana"];
	$anuncios_tipo_archivo = $_POST["anuncios_tipo_archivo"];
	$anuncios_tipo_reproduccion = $_POST["anuncios_tipo_reproduccion"];
	$anuncios_tiempo_anuncio = $_POST["anuncios_tiempo_anuncio"];
	$anuncios_horario_desde = $_POST["anuncios_horario_desde"];
	$anuncios_horario_hasta = $_POST["anuncios_horario_hasta"];

	$anuncios_area_select_filtro = $_POST["anuncios_area_select_filtro"];
	$anuncios_grupo_select_filtro = ','.$_POST["anuncios_grupo_select_filtro"].',';
	
	$detalle_horarios = $_POST["detalle_horarios"];
	$detalle_horarios = json_decode($detalle_horarios);
	
	$verificacion_create = sec_anuncios_validacion_horario_anuncio($anuncios_fecha_desde, $anuncios_fecha_hasta, $anuncios_horario_desde, $anuncios_horario_hasta, $anuncios_tiempo_anuncio, "save", $anuncios_dias_semana);

	if($verificacion_create['status'] == 0) {
		if($anuncios_tipo_archivo == 6)
		{
            if(isset($_FILES['anuncios_imagen'])){
                $anuncios_file_name = $_FILES['anuncios_imagen']['name'];
                $anuncios_file_tmp = $_FILES['anuncios_imagen']['tmp_name'];
                $anuncios_file_size = $_FILES['anuncios_imagen']['size'];
                $anuncios_file_extension = strtolower(pathinfo($anuncios_file_name, PATHINFO_EXTENSION));

                $path = "/var/www/html/anuncios/imagen/";
                $download = "/anuncios/imagen/";

                if (!is_dir($path))
                {
                    mkdir($path, 0777, true);
                }

                $nombreFileUpload = "imagen".date('YmdHis'). ".".$anuncios_file_extension;
                $nombreDownload = $download.$nombreFileUpload;
                move_uploaded_file($anuncios_file_tmp, $path. $nombreFileUpload);
            }
            if (isset($_FILES['anuncios_imagenes'])){
                $anuncios_files = $_FILES['anuncios_imagenes'];
                $files_formats = [];
                $imagenes = '';
                $downloads = '';
                $sizes = '';
                $extensions = '';
                $file_names = $_FILES["anuncios_imagenes"]["name"];
                if (count($file_names )>1){
                    for ($i = 0; $i < count($file_names); $i++) {
                        $anuncios_file_name = $anuncios_files['name'][$i];
                        $anuncios_file_tmp = $anuncios_files['tmp_name'][$i];
                        $anuncios_file_size = $anuncios_files['size'][$i];
                        $anuncios_file_extension = strtolower(pathinfo($anuncios_file_name, PATHINFO_EXTENSION));

                        $path = "/var/www/html/anuncios/imagen/";
                        $download = "/anuncios/imagen/";

                        if (!is_dir($path))
                        {
                            mkdir($path, 0777, true);
                        }
                        //create unique name for each image
                        //$unique_name = uniqid($anuncios_file_name."".$anuncios_file_extension."-".$anuncios_file_size);
                        $unique_name = substr(urlencode(md5(uniqid($anuncios_file_name."".$anuncios_file_extension."-".$anuncios_file_size))), 0, 20);
                        $nombreFileUpload = "imagen".$unique_name. ".".$anuncios_file_extension;
                        $nombreDownload = $download.$nombreFileUpload;
                        move_uploaded_file($anuncios_file_tmp, $path . $nombreFileUpload);
                        //sleep(1);
                        $imagenes = $imagenes.''.$nombreFileUpload.',';
                        $downloads = $downloads.''.$nombreDownload.',';
                        $sizes = $sizes.''.$anuncios_file_size.',';
                        $extensions = $extensions.''.$anuncios_file_extension.',';
                        /*$docs[] =  [
                            "anuncios_file_name"=>$anuncios_file_name,
                            "anuncios_file_tmp"=>$anuncios_file_tmp,
                            "anuncios_file_size"=>$anuncios_file_size,
                            "anuncios_file_extension"=>$anuncios_file_extension,
                            "path"=>$path,
                            "download"=>$download,
                            "nombreFileUpload"=>$nombreFileUpload,
                            "nombreDownload"=>$nombreDownload,
                        ];*/
                    }
                    $nombreFileUpload = trim($imagenes, ',');
                    $nombreDownload = trim($downloads, ',');
                    $anuncios_file_size = trim($sizes, ',');
                    $anuncios_file_extension = trim($extensions, ',');
                }
                /*echo json_encode([
                    'data' => $anuncios_files,
                    'count_flies' => count($file_names),
                    'imagenes' => $nombreFileUpload,
                    'downloads' => $nombreDownload,
                    'sizes' => $anuncios_file_size,
                    'extensions' => $anuncios_file_extension
                ]); return;*/
            }

		}
		else if($anuncios_tipo_archivo == 7)
		{
			$anuncios_file_name = $_FILES['anuncios_audio']['name'];
			$anuncios_file_tmp = $_FILES['anuncios_audio']['tmp_name'];
			$anuncios_file_size = $_FILES['anuncios_audio']['size'];
			$anuncios_file_extension = strtolower(pathinfo($anuncios_file_name, PATHINFO_EXTENSION));

			$path = "/var/www/html/anuncios/audio/";
			$download = "/anuncios/audio/";

			if (!is_dir($path)) 
			{
				mkdir($path, 0777, true);
			}

			$nombreFileUpload = "audio".date('YmdHis'). ".".$anuncios_file_extension;
			$nombreDownload = $download.$nombreFileUpload;
			move_uploaded_file($anuncios_file_tmp, $path. $nombreFileUpload);
		}
		else if($anuncios_tipo_archivo == 8)
		{
			$anuncios_file_name = $_FILES['anuncios_video']['name'];
			$anuncios_file_tmp = $_FILES['anuncios_video']['tmp_name'];
			$anuncios_file_size = $_FILES['anuncios_video']['size'];
			$anuncios_file_extension = strtolower(pathinfo($anuncios_file_name, PATHINFO_EXTENSION));

			$path = "/var/www/html/anuncios/video/";
			$download = "/anuncios/video/";

			if (!is_dir($path)) 
			{
				mkdir($path, 0777, true);
			}
			
			$nombreFileUpload = "video".date('YmdHis'). ".".$anuncios_file_extension;
			$nombreDownload = $download.$nombreFileUpload;
			$contenido = move_uploaded_file($anuncios_file_tmp, $path. $nombreFileUpload);
		}

		$query_insert = "INSERT INTO tbl_gestion_anuncios
						(
							text, 
							imagen, 
						 	extension, 
						 	size, 
						 	ruta, 
						 	download,
						 	id_tipo_archivo,
						 	id_tipo_reproduccion,
						 	tiempo_anuncio,
						 	fecha_desde,
						 	fecha_hasta,
						 	horario_desde,
						 	horario_hasta,
						 	estado, 
						 	user_created_id, 
						 	created_at,
							id_area,
							id_grupos,
						    dias
						 )
						VALUES 
						(
							'".mysqli_real_escape_string($mysqli,$anuncios_texto)."',
							'".$nombreFileUpload."',
							'".$anuncios_file_extension."',
							'".$anuncios_file_size."', 
							'".$path."', 
							'".$nombreDownload."', 
							'".$anuncios_tipo_archivo."', 
							'".$anuncios_tipo_reproduccion."',
							'".$anuncios_tiempo_anuncio."',
							'".$anuncios_fecha_desde."',
							'".$anuncios_fecha_hasta."',
							'".$anuncios_horario_desde.":00"."',
							'".$anuncios_horario_hasta.":00"."',
							10,
							'".$login["id"]."', 
							'".date('Y-m-d H:i:s')."',
							'".$anuncios_area_select_filtro."',
							'".$anuncios_grupo_select_filtro."',
							'".$anuncios_dias_semana."'
						)";

		$mysqli->query($query_insert);
		$gestion_anuncio_id = mysqli_insert_id($mysqli);

		if($mysqli->error)
		{
			$status = false;
			$message = $mysqli->error;
		}
		else
		{
			$message = "Datos guardados correctamente";
			$status = true;
		}

		// PARA EL DETALLE - TIPO REPRODUCCION: HORAS FIJAS:
		if($anuncios_tipo_reproduccion == 1)
		{
			$num_elementos = 0;
			while ($num_elementos < count($detalle_horarios)) 
			{
				$query_insert_detalle_horas_fijas = "INSERT INTO tbl_gestion_anuncio_detalle
										(
											id_tbl_gestion_anuncio,
											minuto_reproduccion, 
										 	se_reproducio, 
										 	status, 
										 	user_created_id, 
										 	created_at
										 )
										VALUES 
										(
											'".$gestion_anuncio_id."', 
											'".$detalle_horarios[$num_elementos].":00"."', 
											0,
											1,
											'".$login["id"]."', 
											'".date('Y-m-d H:i:s')."'
										)";
				$num_elementos ++;
				$mysqli->query($query_insert_detalle_horas_fijas);
			}
		}
		// PARA EL DETALLE - TIPO REPRODUCCION: CADA CIERTO TIEMPO:
		else if($anuncios_tipo_reproduccion == 2)
		{
            $minutos_de_reproduccion = $verificacion_create["minutos_de_reproduccion"];
            foreach ($minutos_de_reproduccion as $key => $value) {
                $query_insert_detalle = "INSERT INTO tbl_gestion_anuncio_detalle
										(
											id_tbl_gestion_anuncio,
											minuto_reproduccion, 
										 	se_reproducio, 
										 	status, 
										 	user_created_id, 
										 	created_at
										 )
										VALUES 
										(
											'".$gestion_anuncio_id."', 
											'".$value."',
											0,
											1,
											'".$login["id"]."', 
											'".date('Y-m-d H:i:s')."'
										)";
                $mysqli->query($query_insert_detalle);
            }
		}

		if($mysqli->error)
		{
			$status = false;
			$message = $mysqli->error;
		}
		else
		{
			$message = "Datos guardados correctamente";
			$status = true;
		}

		$respuesta = sec_anuncios_actualiazar_fecha_del_ultimo_cambio();

		if(!($respuesta === true)){
			$status = false;
			$message = $respuesta;
		}

	}
	else {
		$status = false;
		$message = "El anuncio con el ID: ".$verificacion_create["anuncio_id"]." está programado en el mismo horario. Por favor, verificar los horarios.";
	}

	echo json_encode([
		'status' => $status,
		'verificacion_create' => $verificacion_create,
		'message' => $message
	]);

}


function sec_anuncios_actualiazar_fecha_del_ultimo_cambio() {
	include("db_connect.php");
	include("sys_login.php");

	$created_at = "'" . date('Y-m-d H:i:s') . "'";

	$query_update = "
	UPDATE tbl_gestion_anuncios_ultimo_cambio
	SET 
		updated_at = $created_at
	WHERE id = 1
	";

	$mysqli->query($query_update);

	if($mysqli->error)
	{
		return $mysqli->error;
	}
	else
	{
		return true;
	}

}

if (isset($_POST["anuncios_comprobar_hora"])) {
	$anuncios_fecha_desde = $_POST["anuncios_fecha_desde"];
	$anuncios_fecha_hasta = $_POST["anuncios_fecha_hasta"];
	$anuncios_horario_desde = $_POST["anuncios_horario_desde"];
	$anuncios_horario_hasta = $_POST["anuncios_horario_hasta"];
	$anuncios_tiempo_anuncio = $_POST["anuncios_tiempo_anuncio"];
    $anuncios_dias_semana = $_POST["anuncios_dias_semana"];

	echo $rpta = sec_anuncios_validacion_horario_anuncio($anuncios_fecha_desde, $anuncios_fecha_hasta, $anuncios_horario_desde, $anuncios_horario_hasta, $anuncios_tiempo_anuncio, "verif", $anuncios_dias_semana);
}

function sec_anuncios_validacion_horario_anuncio($anuncios_fecha_desde, $anuncios_fecha_hasta, $anuncios_horario_desde, $anuncios_horario_hasta, $anuncios_tiempo_anuncio, $anuncio_estado, $dias) {
	include("db_connect.php");
	include("sys_login.php");

    $array_dias = explode(",", $dias);
	$variable_retorno['status'] = 0;
	$res_validacion_horario['status'] = 0;
	$anuncios_fecha_desde = date("Y:m:d", strtotime($anuncios_fecha_desde));
	$anuncios_fecha_hasta = date("Y:m:d", strtotime($anuncios_fecha_hasta));

	$hora_inicio = date("H:i:s", strtotime($anuncios_horario_desde));
	$hora_final = date("H:i:s", strtotime($anuncios_horario_hasta));

    $aux_hora_inicio = new DateTime($hora_inicio);
    $aux_hora_final = new DateTime($hora_final);
    $minutos_total = $aux_hora_inicio->diff($aux_hora_final)->format("%H:%I:%S");
    $minutos_total_entero = ($aux_hora_inicio->diff($aux_hora_final)->format("%H") * 60) + ($aux_hora_inicio->diff($aux_hora_final)->format("%I"));
    $numero_reproducciones = floor($minutos_total_entero / $anuncios_tiempo_anuncio);
    $minutos_de_reproduccion = [];
    $minutos_de_reproduccion[0] = $hora_inicio;
    for ($i = 1; $i <= $numero_reproducciones; $i++) {
        $minutos_de_reproduccion[$i] = date("H:i:s", strtotime($minutos_de_reproduccion[$i - 1]) + ($anuncios_tiempo_anuncio * 60));
    }
    $minutos_de_reproduccion_string = implode("','", $minutos_de_reproduccion);
    $variable_retorno['minutos_de_reproduccion'] = $minutos_de_reproduccion;

    foreach ($array_dias as $dia) {
        $query_exist = "
            SELECT
                gad.id_tbl_gestion_anuncio, gad.user_created_id, ga.text
            FROM tbl_gestion_anuncio_detalle gad
            INNER JOIN tbl_gestion_anuncios ga ON gad.id_tbl_gestion_anuncio = ga.id
            WHERE
                (ga.fecha_desde BETWEEN '$anuncios_fecha_desde' AND '$anuncios_fecha_hasta')
                AND ga.dias like '%$dia%'
                AND gad.minuto_reproduccion IN ('$minutos_de_reproduccion_string')
            LIMIT 1";
            //$res['query_exist'] = $query_exist;

        $exist = $mysqli->query($query_exist);
        $row_exist = $exist->num_rows;
        if ($row_exist > 0) {
            while($sel_hora = $exist->fetch_assoc()){
                $sel_anuncio_id_select = $sel_hora["id_tbl_gestion_anuncio"];
                $sel_anuncio_text = $sel_hora["text"];
                $sel_anuncio_hora = $hora_inicio;
            }
            $res_validacion_horario['status'] = 1;
            $res_validacion_horario["sel_anuncio_id_select"] = $sel_anuncio_id_select;
            $res_validacion_horario["sel_anuncio_text"] = $sel_anuncio_text;
            $res_validacion_horario["sel_anuncio_hora"] = $sel_anuncio_hora;
            $variable_retorno['status'] = 1;
            $variable_retorno['anuncio_id'] = $sel_anuncio_id_select;
            break;
        }
    }
	if ($anuncio_estado == 'verif') {
		return json_encode($res_validacion_horario);
	} else if ($anuncio_estado == 'save') {
		return $variable_retorno;
	}
}

if (isset($_POST["anuncios_obtener_anuncio_disponible"]))
{
	$message = "";
	$status = true;
	$param_min_reproduccion_actual = date("H:i:");
	//$param_min_reproduccion_actual = "17:05:";

	$id_tipo_archivo_select = 0;
	$minuto_reproduccion_select = "";
	$anuncio_texto_select = "";
	$anuncio_select = "";

	$minuto_reproduccion = "";
	$anuncio_texto = "";
	$anuncio = "";

	//area 21: operaciones - cargo 5: cajero
	//if($login["area_id"] == 21 AND $login["cargo_id"] == 5)
	//area 6: sistemas - cargo 9: desarrollador
	if($login["area_id"] == 6 AND $login["cargo_id"] == 9)
	{
		$query_select = $mysqli->query(
		"
			SELECT
				gad.id AS tbl_gestion_anuncio_detalle_id, gad.id_tbl_gestion_anuncio, 
			    gad.minuto_reproduccion, gad.se_reproducio, 
			    gad.status, gad.user_created_id, gad.created_at,
			    ga.id_tipo_archivo, ga.text AS anuncio_texto, ga.download AS anuncio
			FROM tbl_gestion_anuncio_detalle gad
				INNER JOIN tbl_gestion_anuncios ga ON gad.id_tbl_gestion_anuncio = ga.id
			WHERE (CURDATE() BETWEEN ga.fecha_desde AND ga.fecha_hasta) AND gad.minuto_reproduccion = '".$param_min_reproduccion_actual."00' LIMIT 1
		");

		$row_count_anuncio_detalle = $query_select->num_rows;

		if($row_count_anuncio_detalle > 0)
		{
			// HACER UN INSERT A LA TABLA DETALLE CON EL USUARIO QUIEN ESTA EJECUTANDO Y MOSTRARLO Y HACER UN CHECK DE EJECUTADO

			while($sel = $query_select->fetch_assoc())
			{
				$tbl_gestion_anuncio_detalle_id_select = $sel["tbl_gestion_anuncio_detalle_id"];
				$id_tbl_gestion_anuncio = $sel["id_tbl_gestion_anuncio"];
				$minuto_reproduccion_select = $sel["minuto_reproduccion"];
				$se_reproducio = $sel["se_reproducio"];
				$status = $sel["status"];
				$user_created_id = $sel["user_created_id"];
				$created_at = $sel["created_at"];

				$id_tipo_archivo_select = $sel["id_tipo_archivo"];
				$anuncio_texto_select = $sel["anuncio_texto"];
				$anuncio_select = $sel["anuncio"];
			}

			//VERIFICAR SI AL USUARIO YA SE MOSTRO EL ANUNCIO.

			$select_verificar_user_anuncio_ejecutado = $mysqli->query(
			"
				SELECT
					gad.id AS tbl_gestion_anuncio_detalle_id, gad.id_tbl_gestion_anuncio, 
				    gad.minuto_reproduccion, gad.se_reproducio, 
				    gad.status, gad.user_created_id, gad.created_at,
				    ga.id_tipo_archivo, ga.text AS anuncio_texto, ga.download AS anuncio
				FROM tbl_gestion_anuncio_detalle gad
					INNER JOIN tbl_gestion_anuncios ga ON gad.id_tbl_gestion_anuncio = ga.id
				WHERE gad.id_tbl_gestion_anuncio = '".$id_tbl_gestion_anuncio."' AND gad.id_user_reproduccion = '".$login["id"]."' AND gad.se_reproducio = '1' AND gad.minuto_reproduccion = '".$minuto_reproduccion_select."'
			");

			$row_count_select_verificar_user_anuncio_ejecutado = $select_verificar_user_anuncio_ejecutado->num_rows;

			if($row_count_select_verificar_user_anuncio_ejecutado == 0)
			{
				//INSERT DETALLE
				$query_insert_detalle = "INSERT INTO tbl_gestion_anuncio_detalle
										(
											id_tbl_gestion_anuncio,
											minuto_reproduccion, 
										 	se_reproducio,
										 	id_user_reproduccion,
										 	status, 
										 	user_created_id, 
										 	created_at
										 )
										VALUES 
										(
											'".$id_tbl_gestion_anuncio."', 
											'".$minuto_reproduccion_select."',
											1,
											'".$login["id"]."',
											'".$status."',
											'".$user_created_id."',
											'".$created_at."'
										)";

				$mysqli->query($query_insert_detalle);

				while($sel = $query_select->fetch_assoc())
				{
					$tbl_gestion_anuncio_detalle_id_select = $sel["tbl_gestion_anuncio_detalle_id"];
					$id_tipo_archivo_select = $sel["id_tipo_archivo"];
					$minuto_reproduccion_select = $sel["minuto_reproduccion"];
					$anuncio_texto_select = $sel["anuncio_texto"];
					$anuncio_select = $sel["anuncio"];
				}
			}
			else
			{
				$id_tipo_archivo_select = 0;
				$minuto_reproduccion_select = "";
				$anuncio_texto_select = "";
				$anuncio_select = "";
			}
			
		}
		else
		{
			$query_delete_from_user_execute_anuncio = $mysqli->query(
			"
				SELECT
					gad.id AS tbl_gestion_anuncio_detalle_id, gad.id_tbl_gestion_anuncio, 
				    gad.minuto_reproduccion, gad.se_reproducio, 
				    gad.status, gad.user_created_id, gad.created_at,
				    ga.id_tipo_archivo, ga.text AS anuncio_texto, ga.download AS anuncio
				FROM tbl_gestion_anuncio_detalle gad
					INNER JOIN tbl_gestion_anuncios ga ON gad.id_tbl_gestion_anuncio = ga.id
				WHERE gad.id_user_reproduccion = '".$login["id"]."' AND gad.se_reproducio = '1'
			");

			while($sel = $query_delete_from_user_execute_anuncio->fetch_assoc())
			{
				$tbl_gestion_anuncio_detalle_id_select = $sel["tbl_gestion_anuncio_detalle_id"];

				$query_delete = "DELETE FROM tbl_gestion_anuncio_detalle WHERE id = '".$tbl_gestion_anuncio_detalle_id_select."' ";
		
				$mysqli->query($query_delete);

			}

			$id_tipo_archivo_select = 0;
			$minuto_reproduccion_select = "";
			$anuncio_texto_select = "";
			$anuncio_select = "";
		}
	}
	

	echo json_encode([
		'status' => $status,
		'message' => $message,

		'id_tipo_archivo' => $id_tipo_archivo_select,
		'minuto_reproduccion' => $minuto_reproduccion_select,
		'anuncio_texto' => $anuncio_texto_select,
		'anuncio' => $anuncio_select
	]);

}

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_anuncio_eliminar_anuncio") 
{
	$anuncio_id = $_POST["anuncio_id"];

	$query_delete_from_anuncio_detalle = $mysqli->query(
	"
		SELECT
			gad.id AS tbl_gestion_anuncio_detalle_id, gad.id_tbl_gestion_anuncio
		FROM tbl_gestion_anuncio_detalle gad
		WHERE gad.id_tbl_gestion_anuncio = '".$anuncio_id."'
	");

	while($sel = $query_delete_from_anuncio_detalle->fetch_assoc())
	{
		$tbl_gestion_anuncio_detalle_id_select = $sel["tbl_gestion_anuncio_detalle_id"];

		$query_delete = "DELETE FROM tbl_gestion_anuncio_detalle WHERE id = '".$tbl_gestion_anuncio_detalle_id_select."' ";

		$mysqli->query($query_delete);
	}

	$query_delete_anuncio = "DELETE FROM tbl_gestion_anuncios WHERE id = '".$anuncio_id."' ";

	$mysqli->query($query_delete_anuncio);
	
	if($mysqli->error)
	{
		$status = false;
		$message = $mysqli->error;
	} 
	else 
	{
		$status = true;
		$message = "Datos registrados correctamente";

	}

	$respuesta = sec_anuncios_actualiazar_fecha_del_ultimo_cambio();

	if(!($respuesta === true)){
		$status = false;
		$message = $respuesta;
	}

	echo json_encode([
		'status' => $status,
		'message' => $message
	]);
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_fecha_del_ultimo_cambio") 
{
	$query = "
	SELECT
		updated_at
	FROM 
		tbl_gestion_anuncios_ultimo_cambio 
	WHERE 
		id = 1
	";

	$query_select = $mysqli->query($query);
	$row_count = $query_select->num_rows;

	if($mysqli->error){
		$result["http_code"] = 500;
		$result["error"] = $mysqli->error;
	}

	if($row_count > 0)
	{
		$row = $query_select->fetch_assoc();
		$result["http_code"] = 200;
		$result["fecha"] = $row["updated_at"];
	} 
	else 
	{
		$result["http_code"] = 400;
		$result["error"] = "No se pudo obtener la fecha del último cambio.";
	}

	echo json_encode($result);
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_anuncios_del_dia_de_hoy") 
{
	$anuncios_area_id = $_POST["anuncios_area_id"];
	$anuncios_grupo_id = $_POST["anuncios_grupo_id"];

	$query = "
	SELECT 
		TIME_FORMAT(gad.minuto_reproduccion, '%H:%i') AS minuto,
		ga.id_tipo_archivo AS tipo,
		ga.text AS texto,
		ga.download AS anuncio
	FROM
		tbl_gestion_anuncio_detalle gad INNER JOIN tbl_gestion_anuncios ga ON gad.id_tbl_gestion_anuncio = ga.id
	WHERE
		CURDATE() BETWEEN ga.fecha_desde AND ga.fecha_hasta
	    AND ga.dias LIKE CONCAT('%', DAYOFWEEK(CURDATE()), '%')
		AND gad.status = 1
        AND gad.minuto_reproduccion >= DATE_ADD(CURTIME(), INTERVAL -1 MINUTE)
		AND gad.status = 1
		AND (ga.id_area = '$anuncios_area_id' OR ga.id_area = 0)
		AND (ga.id_grupos LIKE '%,$anuncios_grupo_id,%' OR ga.id_grupos LIKE '%,0,%')
	ORDER BY gad.minuto_reproduccion ASC
	";
	
	$query_select = $mysqli->query($query);
	$row_count = $query_select->num_rows;

	$list = array();

	if($row_count > 0) {
		while ($li = $query_select->fetch_assoc()) {
			$list[] = $li;
		}
	}

	$result["http_code"] = 200;
    $result["query"] = $query;
	$result["array_anuncios"] = $list;

	echo json_encode($result);
}
?>