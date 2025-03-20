<?php
$return = array();
$return["memory_init"]=memory_get_usage();
$return["time_init"] = microtime(true);
include("global_config.php");
include("db_connect.php");
include("sys_login.php");
include '/var/www/html/phpexcel/classes/PHPExcel.php';
require_once '/var/www/html/sys/helpers.php';

function resizeImage($resourceType, $image_width, $image_height)
{
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

if(isset($_POST["sec_servicio_tecnico_atencion_cargar_solicitud"])){
	$solicitud_id = $_POST["solicitud_id"];
	if($_POST["estado_id"] != null && $_POST["estado_id"] > 1)
	{
		$command ="SELECT sth.id AS id,
			inci.id AS incidencia_id,
			l.nombre AS local,
			IF(z.nombre IS NULL , '---', z.nombre ) AS zona,
			inci.incidencia_txt  as reporte,
			sth.equipo_id ,
			/*sth.servicio_tecnico_estado_id as estado ,*/
			/*ste.nombre as estado ,*/
			ste.id as estado ,
			sth.created_at,
			inci.updated_at,
			IF(sth.comentario IS NULL, '---', sth.comentario) AS comentario,
			IF(sth.comentario_terminado IS NULL , '---', sth.comentario_terminado ) AS comentario_terminado,
			sth.foto_terminado,
			sth.tecnico_id,
            inci.nota_tecnico
			FROM tbl_servicio_tecnico_historial sth
			LEFT JOIN tbl_soporte_incidencias inci ON inci.id = sth.servicio_tecnico_id
			LEFT JOIN tbl_locales l ON  l.id = inci.local_id
			LEFT JOIN tbl_zonas z ON  z.id = l.zona_id
			LEFT JOIN tbl_servicio_tecnico_estado  ste ON ste.id = sth.servicio_tecnico_estado_id
			WHERE sth.servicio_tecnico_id = {$solicitud_id}
			ORDER BY id DESC
			LIMIT 1";
		$list_query = $mysqli->query($command);
		$list = $list_query->fetch_assoc();
	}
	else{
		$command ="SELECT inci.id AS id,
			l.nombre AS local,
			IF(z.nombre IS NULL , '---', z.nombre ) AS zona,	 
			inci.id AS incidencia_id,
			inci.incidencia_txt  as reporte,	 
			inci.equipo ,
			inci.equipo_id ,
			IF(inci.estado_servicio_tecnico_id IS NULL OR inci.estado_vt = 'Derivado' , '' ,inci.estado_servicio_tecnico_id) as estado,
			inci.created_at,
			inci.updated_at,				 
			inci.comentario_vt as comentario,
			inci.foto_terminado_vt as foto_terminado,
            inci.nota_tecnico
			FROM tbl_soporte_incidencias inci
			LEFT JOIN tbl_locales l ON  l.id = inci.local_id
			LEFT JOIN tbl_zonas z ON  z.id = l.zona_id
			LEFT JOIN tbl_servicio_tecnico_equipo ste ON  ste.id = inci.equipo_id
			WHERE inci.id = {$solicitud_id}";
		$list_query = $mysqli->query($command);
		$list = $list_query->fetch_assoc();

	}
	$return["local"] = $list;
}

if(isset($_POST["set_servicio_tecnico_atencion_update_ant"])){
	$user_id = $login["id"];
	extract($_POST);
	if(!isset($estado) || $estado == ""){
		$return["error"] = "estado";
		$return["error_msg"] = "Debe Seleccionar Estado";
		$return["error_focus"] = "estado";
		die(json_encode($return));
	}
	$tecnico_id = "";
	$comentario = "";
	if( $estado == "Programado" ){
		if( !isset($tecnico_id) || $tecnico_id == "" ){
			$return["error"] = "tecnico_id";
			$return["error_msg"] = "Debe Seleccionar Técnico";
			$return["error_focus"] = "tecnico_id";
			die(json_encode($return));
		}
		$comentario = ",comentario_vt = '".$_POST["comentario"]."'";
		$tecnico_id = ",tecnico_id = '" . $_POST["tecnico_id"] . "'";
	}

	$fecha_cierre = "null";
	$foto_terminado = "";
	if($estado == "Terminado"){
		$fecha_cierre = "now()";

		$path = "/var/www/html/files_bucket/servicio_tecnico/";
		$file = [];
		$imageLayer = [];
		if (!is_dir($path)) mkdir($path, 0777, true);

		$archivo = $_FILES['foto_terminado_update']["name"];
		$filename = $_FILES["foto_terminado_update"]['tmp_name'];
		$filenametem = $_FILES["foto_terminado_update"]['name'];

		$size = $_FILES["foto_terminado_update"]['size'];
		$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
		$valid_extensions = array('jpeg', 'jpg', 'png', 'gif');

		if($filename == ""){
			$return["error"]="imagen";
			$return["error_msg"] = "Debe Ingresar un archivo de imagen";
			$return["error_focus"]="foto_terminado_update";
			die(json_encode($return));
		}
		if(!in_array($ext, $valid_extensions)) {
			$return["error"]="ext";
			$return["error_msg"] = "Solo es permitido las extensiones: 'jpeg', 'jpg', 'png', 'gif'";
			die(json_encode($return));
		}
		if($size > 10485760){//10 mb
			$return["error"]="size";
			$return["error_msg"] ="Archivo supera la cantidad máxima permitida (10 MB)";
			die(json_encode($return));
		}
		$nombre_archivo = "";
		if($filename != ""){
			$fileExt = pathinfo($_FILES["foto_terminado_update"]['name'], PATHINFO_EXTENSION);

			$resizeFileName =   date('YmdHis');
			$nombre_archivo = $id."_".$filenametem."_".$resizeFileName . "." . $fileExt;
			
			$sourceProperties = getimagesize($filename);
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
					break;
			}
			$imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);
			$file[0] = imagegif($imageLayer[0], $path . $nombre_archivo);
			move_uploaded_file($file[0], $path . $nombre_archivo);
		}
		$foto_terminado = ",foto_terminado_vt = '$nombre_archivo'";
	}
	$equipo = isset($equipo) ? ",equipo = '$equipo'" : "";

	$command = "UPDATE tbl_soporte_incidencias SET 
					 estado_vt = '$estado'			 
					,updated_at = now()
					,update_user_id = $user_id
					,fecha_cierre_vt = $fecha_cierre
					
					$equipo
					$comentario
					$foto_terminado
					$tecnico_id
				WHERE id = ".$id;
	$mysqli->query($command);
	if($mysqli->error){
		print_r($mysqli->error);
		echo "\n";
		echo $command;
		exit();
	}
	$return["mensaje"] = "Solicitud Servicio Técnico ".$id." Actualizada";
	$return["curr_login"]=$login;
}

if(isset($_POST["set_servicio_tecnico_atencion_update"])){
	$user_id = $login["id"];
	extract($_POST);
	if(!isset($estado) || $estado == ""){
		$return["error"] = "estado";
		$return["error_msg"] = "Debe Seleccionar Estado";
		$return["error_focus"] = "estado";
		die(json_encode($return));
	}
	if( $estado == 2 ){
		if( !isset($tecnico_id) || $tecnico_id == "" ){
			$return["error"] = "tecnico_id";
			$return["error_msg"] = "Debe Seleccionar Técnico";
			$return["error_focus"] = "tecnico_id";
			die(json_encode($return));
		}
		if( strlen($comentario) > 200)
		{
			$return["error"] = "comentario";
			$return["error_msg"] = "Comentario no mayor a 200 caracteres";
			$return["error_focus"] = "comentario";
			die(json_encode($return));
		}
		//$tecnico_id = ",tecnico_id = '" . $tecnico_id . "'";
	}
	if($estado == 4){
		if((!isset($comentario_terminado_editar) || $comentario_terminado_editar == "") && !file_exists($_FILES['foto_terminado_update']['tmp_name'])){
			$return["error"] = "comentario o foto terminado";
			$return["error_msg"] = "El campo comentario o foto es necesario";
			$return["error_focus"] = "comentario o foto terminado";
			die(json_encode($return));
		}
	}

	if( $estado == 4 ){
		$command = "SELECT inci.id AS id,
			inci.id AS incidencia_id,
			l.nombre AS local,
			IF(z.nombre IS NULL , '---', z.nombre ) AS zona,
			inci.incidencia_txt  as reporte,
			inci.equipo_id ,
			/*sth.servicio_tecnico_estado_id as estado ,*/
			/*ste.nombre as estado ,*/
			ste.id as estado ,
			inci.created_at,
			inci.updated_at,
			IF(sth.comentario_terminado IS NULL , '---', sth.comentario_terminado ) AS comentario_terminado,
			sth.foto_terminado,
			sth.tecnico_id,
			inci.nota_tecnico,
			inci.local_id,
			steq.nombre as equipo_nombre
			FROM tbl_soporte_incidencias inci
			LEFT JOIN tbl_locales l ON  l.id = inci.local_id
			LEFT JOIN tbl_zonas z ON  z.id = l.zona_id
			LEFT JOIN tbl_servicio_tecnico_estado  ste ON ste.id = inci.id
			LEFT JOIN tbl_servicio_tecnico_equipo steq ON steq.id = inci.equipo_id
			LEFT JOIN tbl_servicio_tecnico_historial sth ON sth.servicio_tecnico_id = inci.id 
			where inci.id = {$incidencia_id}
			order by id desc
			limit 1";
				
		$data_command = $mysqli->query($command)->fetch_assoc();
		$local_id = $data_command["local_id"];
		
		$command_supervisor_correos = "SELECT p.correo
		FROM tbl_usuarios_locales ul
		LEFT JOIN tbl_usuarios u ON (u.id = ul.usuario_id)
		LEFT JOIN tbl_personal_apt p ON (p.id = u.personal_id)
		WHERE ul.local_id = ". $local_id ." AND ul.estado = '1' 
		AND (p.area_id = ('21') AND p.cargo_id IN (4,5) OR (p.area_id = ('22') AND p.cargo_id != 3)
			OR (p.area_id = '31' AND p.cargo_id = 5)
			OR (p.area_id = '28' AND p.cargo_id = 5)
		)
		AND (p.cargo_id = 16 OR p.cargo_id = 4)";

		$correos = [];
		$data_command_supervisor_correos = $mysqli->query($command_supervisor_correos);				
		while($supervisores_correos = $data_command_supervisor_correos->fetch_assoc()){
			if($supervisores_correos['correo'] != ""){
				array_push($correos,$supervisores_correos['correo']);
			}
		}	
		if(empty($correos)){
			$return["error"] = "correo";
			$return["error_msg"] = "El jefe comercial y supervisor no cuentan con correos asignados";
			$return["error_focus"] = "correo";
			die(json_encode($return));
		}
	}
	$fecha_cierre = "null";
	$foto_terminado = "";
	if($estado == 4 )
	{
		$fecha_cierre = "now()";
		if(file_exists($_FILES['foto_terminado_update']['tmp_name'])){
			$path = "/var/www/html/files_bucket/servicio_tecnico/";
			$file = [];
			$imageLayer = [];
			if (!is_dir($path)) mkdir($path, 0777, true);

			$archivo = $_FILES['foto_terminado_update']["name"];
			$filename = $_FILES["foto_terminado_update"]['tmp_name'];
			$filenametem = $_FILES["foto_terminado_update"]['name'];

			$size = $_FILES["foto_terminado_update"]['size'];
			$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
			$valid_extensions = array('jpeg', 'jpg', 'png', 'gif');

			if($filename == ""){
				$return["error"]="imagen";
				$return["error_msg"] = "Debe Ingresar un archivo de imagen";
				$return["error_focus"]="foto_terminado_update";
				die(json_encode($return));
			}
			if(!in_array($ext, $valid_extensions)) {
				$return["error"]="ext";
				$return["error_msg"] = "Solo es permitido las extensiones: 'jpeg', 'jpg', 'png', 'gif'";
				die(json_encode($return));
			}
			if($size > 10485760){//10 mb
				$return["error"]="size";
				$return["error_msg"] ="Archivo supera la cantidad máxima permitida (10 MB)";
				die(json_encode($return));
			}
			$nombre_archivo = "";
			if($filename != ""){
				$fileExt = pathinfo($_FILES["foto_terminado_update"]['name'], PATHINFO_EXTENSION);

				$resizeFileName =   date('YmdHis');
				$nombre_archivo = $id."_".$filenametem."_".$resizeFileName . "." . $fileExt;
				
				$sourceProperties = getimagesize($filename);
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
						break;
				}
				$imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);
				$file[0] = imagegif($imageLayer[0], $path . $nombre_archivo);
				move_uploaded_file($file[0], $path . $nombre_archivo);
			}
			$foto_terminado = $nombre_archivo;
		}
	}

	$equipo_id = isset($equipo_id) ? "'$equipo_id'" : "null";
	$tecnico_id = isset($tecnico_id) && $tecnico_id != "" ? "'$tecnico_id'" : "null";
	$comentario_terminado = isset($comentario_terminado) && $comentario_terminado != "" ? "'$comentario_terminado'" : "---";
	$comentario_terminado_editar = isset($comentario_terminado_editar) && $comentario_terminado_editar != "" ? "$comentario_terminado_editar" : "---";

	$command ="SELECT estado_servicio_tecnico_id
			FROM tbl_soporte_incidencias inci		
			WHERE inci.id = {$incidencia_id}";
	$query_inci = $mysqli->query($command)->fetch_assoc();
	if($query_inci["estado_servicio_tecnico_id"] !=  $estado){ //insert
		$insert_command = "INSERT INTO `wwwapuestatotal_gestion`.`tbl_servicio_tecnico_historial`
		(
			servicio_tecnico_id
			,servicio_tecnico_estado_id
			,equipo_id
			,comentario
			,user_id
			,foto_terminado
			,comentario_terminado
			,tecnico_id
			,created_at
			,updated_at
		)
			VALUES
		(
			 $incidencia_id
			,$estado
			,$equipo_id
			,'" . $comentario . "'
			, " . $login["id"] . "
			, '" . $foto_terminado ."'
			, '" . $comentario_terminado_editar ."'
			,$tecnico_id
			,NOW()
			,NOW()
		)
		"
		;
		$return["insert_command"] = $insert_command;
		$mysqli->query($insert_command);
		if($mysqli->error){
			print_r($mysqli->error);
			echo "\n";
			echo $insert_command;
			exit();
		}
	}
	else
	{
		$insert_command = "UPDATE  `wwwapuestatotal_gestion`.`tbl_servicio_tecnico_historial` SET
			equipo_id = $equipo_id
			,tecnico_id = $tecnico_id
			,comentario = '" . $comentario . "'
			,comentario_terminado = '" . $comentario_terminado_editar . "'
			,user_id =  " . $login["id"] . "
			,foto_terminado =  '" . $foto_terminado ."'			
			,updated_at = NOW()
		WHERE id = $id
		"
		;
		$return["insert_command"] = $insert_command;
		$mysqli->query($insert_command);
		if($mysqli->error){
			print_r($mysqli->error);
			echo "\n";
			echo $insert_command;
			exit();
		}

	}

	$command = "UPDATE tbl_soporte_incidencias
				SET 
					 estado_vt = '$estado_vt'
					,estado_servicio_tecnico_id = $estado
					,equipo_id = $equipo_id
					,updated_at = now()
					,update_user_id = $user_id
					,fecha_cierre_vt = $fecha_cierre					
				WHERE id = " . $incidencia_id;
	$mysqli->query($command);
	$return["update_command"] = $command;
	if($mysqli->error){
		print_r($mysqli->error);
		echo "\n";
		echo $command;
		exit();
	}

	if($estado == 4 ){
		try{
			include('../sys/mailer/class.phpmailer.php');
			$mail = new PHPMailer(true);
			$mail->IsSMTP(); 
			$mail->SMTPDebug  = 1;                     
			$mail->SMTPAuth   = true;                  
			$mail->Host       = "smtp.gmail.com";     
			$mail->Port       = 465;  
			$mail->SMTPSecure = "ssl"; 
			foreach ($correos as $correo) {
				if($correo != ""){
					$mail->AddAddress($correo);
				}
			}
			// $mail->AddBCC("victor.alayo@testtest.apuestatotal.com");
			$mail->AddBCC("jhossep.zamora@testtest.apuestatotal.com");
			$mail->AddBCC("neil.flores@testtest.kurax.dev");  
			$mail->Username   =env('MAIL_GESTION_USER');  
			$mail->Password   =env('MAIL_GESTION_PASS');        
			$mail->FromName = "Apuesta Total";
			$mail->Subject    = "Solicitud Servicio Tecnico Terminado (id de la incidencia : ".$incidencia_id.")";
			$mail->Body = '<html>
			<head>
			<h1 style="font-family:arial;">Id de incidencia: '.$incidencia_id.'</h1>
			</head>
			<table border="2" style="border-collapse: collapse;border-radius: 1em;">
			<tr style="font-family:arial;background-color:#CDDDFF;color:#256AFF;font-size: 18px;">
				<th>Fecha Ingreso</th>
				<th>Zona</th>
				<th>Tienda</th>
				<th>Descr. Incidente</th>
				<th>Estado</th>
				<th>Equipo</th>
				<th>Nota para el técnico</th>
				<th>Comentario</th>
			</tr>
			<tr style="font-size: 18px;">
				<td>'.$data_command["created_at"].'</td>
				<td>'.$data_command["zona"].'</td>
				<td>'.$data_command["local"].'</td>
				<td>'.$data_command["reporte"].'</td>
				<td>Terminado</td>
				<td>'.$data_command["equipo_nombre"].'</td>
				<td>'.$data_command["nota_tecnico"].'</td>
				<td>'.$comentario_terminado_editar.'</td>
			</tr>
			</html>
			</head>';
	
			$mail->isHTML(true);
			if($mail->send()) $return["email_sent"]="ok";
		}catch(phpmailerException $ex){
			$return["email_error"]=$mail->ErrorInfo;
			$insert_data["is_error"]=$mail->ErrorInfo;
		}
	}

	$id_servicio_tecnico_historial = $mysqli->insert_id;
	$return["mensaje"] = "Solicitud Servicio Técnico ".$id." Actualizada";
	$return["curr_login"] = $login;
	$return["id_servicio_tecnico_historial"] = $id_servicio_tecnico_historial;
}


$return["memory_end"]=memory_get_usage();
$return["time_end"] = microtime(true);
$return["memory_total"]=($return["memory_end"]-$return["memory_init"]);
$return["time_total"]=($return["time_end"]-$return["time_init"]);
print_r(json_encode($return));
?>