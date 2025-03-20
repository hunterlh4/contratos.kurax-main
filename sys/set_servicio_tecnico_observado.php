<?php
$return = array();
$return["memory_init"]=memory_get_usage();
$return["time_init"] = microtime(true);
include("global_config.php");
include("db_connect.php");
include("sys_login.php");
include '/var/www/html/phpexcel/classes/PHPExcel.php';
require_once '/var/www/html/sys/helpers.php';
require_once '/var/www/html/class/clsServicioTecnico.php';

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

if(isset($_POST["sec_servicio_tecnico_observado_cargar_solicitud"])){
	$incidencia_id = $_POST["solicitud_id"];
		$command = "SELECT
					sth.id AS sth_id				
					FROM tbl_servicio_tecnico_historial sth
					WHERE sth.servicio_tecnico_id = {$incidencia_id} 
					AND sth.servicio_tecnico_estado_id = 3
					ORDER BY sth.id DESC LIMIT 1" ;
	$sth = $mysqli->query($command)->fetch_assoc();
	$sth_id = $sth["sth_id"];
    if (!$sth_id){
        $return["error"] = true;
        $return["error_msg"] = "No se encontró el registro en el historial de servicio técnico.";
        die(json_encode($return));
    }

	$command = "SELECT
				sth.id AS id,
				inci.id AS incidencia_id,
				l.nombre AS local,
				IF(z.nombre IS NULL , '---', z.nombre ) AS zona,
				inci.incidencia_txt  as reporte,
				sth.equipo_id ,
				ste.nombre as estado_nombre,
				ste.id as estado,
				inci.estado as motivo_observado,
				sth.created_at,
				sth.tecnico_id,
				sth.comentario,
				rs.nombre as razon_social,
				rs.id as razon_social_id,
				IF(inci.comentario_vt IS NULL , '---', inci.comentario_vt ) AS comentario_mantenimiento,
				sth.comentario_terminado
				FROM tbl_servicio_tecnico_historial sth
				LEFT JOIN tbl_servicio_tecnico inci on inci.id = sth.servicio_tecnico_id
				LEFT JOIN tbl_servicio_tecnico_estado ste ON ste.id = sth.servicio_tecnico_estado_id
				LEFT JOIN tbl_locales l ON  l.id = inci.local_id
				LEFT JOIN tbl_zonas z ON  z.id = l.zona_id
				LEFT JOIN tbl_razon_social rs ON rs.id = z.razon_social_id
				WHERE sth.id = {$sth_id}" ;
	$list_query = $mysqli->query($command);
    $list = $list_query->fetch_assoc();

	$return["local"] = $list;
}

if(isset($_POST["set_servicio_tecnico_observado_update_ant"])){
	$user_id = $login["id"];
	extract($_POST);
	if(!isset($estado) || $estado == ""){
		$return["error"] = "estado";
		$return["error_msg"] = "Debe Seleccionar Estado";
		$return["error_focus"] = "estado";
		die(json_encode($return));
	}

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
    $equipo = isset($equipo) ? ",equipo = '" . $equipo . "'" : "";
    
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
	
	$command = "UPDATE tbl_soporte_incidencias SET 
					 estado_vt = '$estado'			 
					,updated_at = now()
					,update_user_id = $user_id
					,fecha_cierre_vt = $fecha_cierre					
                    $equipo
					$comentario
					$foto_terminado
				WHERE id = ".$id;
	$mysqli->query($command);
	if($mysqli->error){
		print_r($mysqli->error);
		echo "\n";
		echo $command;
		exit();
	}

    if($accion_observado == "MANTENIMIENTO")
    {
        $command ="SELECT inci.id AS id,
            l.nombre AS local,
            z.id as zona_id,
            l.id as local_id,
            inci.incidencia_txt,	 
            inci.equipo ,
            IF(inci.estado_vt IS NULL , 'Derivado' ,inci.estado_vt) as estado,
            inci.created_at,
            inci.updated_at,				 
            inci.comentario_vt as comentario,
            inci.foto_terminado_vt as foto_terminado,
            inci.tecnico_id
            FROM .tbl_soporte_incidencias inci
            LEFT JOIN tbl_locales l ON  l.id = inci.local_id
            LEFT JOIN tbl_zonas z ON  z.id = l.zona_id
            WHERE inci.id = {$id}";
        $list_query = $mysqli->query($command);
        $incidencia = $list_query->fetch_assoc();

        $insert_command = "INSERT INTO tbl_solicitud_mantenimiento 
			(created_at
			,user_id
			,zona_id
			,local_id
			,reporte
			,estado
			)
			VALUES(
			now()
			," . $login["id"] . "
			," . ($incidencia["zona_id"] != "" ? $incidencia["zona_id"] : "null") . "
			," . $incidencia["local_id"] . "
			,'". $incidencia["incidencia_txt"] . "'
			,'Solicitud'
			)
		";
		$mysqli->query($insert_command);
		if($mysqli->error){
			print_r($mysqli->error);
			echo "\n";
			echo $insert_command;
			exit();
		}
		$id_solicitud_mantenimiento = $mysqli->insert_id;
	    $return["id_solicitud_mantenimiento"] = $id_solicitud_mantenimiento;

    }
    if($accion_observado == "REPUESTOS SUP Y JC")
    {
        $command ="SELECT inci.id AS id,
			l.nombre AS 'Local',
			IF(z.nombre IS NULL , '---', z.nombre ) AS zona,	 
			inci.incidencia_txt  as reporte,	 
			inci.equipo ,
			IF(inci.estado_vt IS NULL , 'Derivado' ,inci.estado_vt) as estado,
			inci.created_at,
			inci.updated_at,				 
			inci.comentario_vt as comentario,
			inci.foto_terminado_vt as foto_terminado,
			inci.tecnico_id,
			( SELECT psop.correo
			FROM tbl_usuarios_locales ul
			LEFT JOIN tbl_locales l_sup on l_sup.id = ul.local_id
			LEFT JOIN tbl_usuarios  u_superv ON u_superv.id = ul.usuario_id
			LEFT JOIN tbl_personal_apt psop ON psop.id = u_superv.personal_id
			WHERE
				ul.local_id = l.id
				AND ul.estado = 1
				AND u_superv.estado = 1
				AND psop.area_id = 21
				AND psop.cargo_id = 4
				AND psop.estado = 1 LIMIT 1       
			) AS 'supervisor_correo',

			( SELECT psop.correo
			FROM tbl_usuarios_locales ul
			LEFT JOIN tbl_locales l_sup ON l_sup.id = ul.local_id
			LEFT JOIN tbl_usuarios  u_superv ON u_superv.id = ul.usuario_id
			LEFT JOIN tbl_personal_apt psop ON psop.id = u_superv.personal_id
			WHERE
				ul.local_id = l.id
				AND ul.estado = 1
				AND u_superv.estado = 1
				AND psop.area_id = 21
				AND psop.cargo_id = 16
				AND psop.estado = 1 LIMIT 1       
			) AS 'jefe_comercial_correo'
		FROM .tbl_soporte_incidencias inci
		LEFT JOIN tbl_locales l ON  l.id = inci.local_id
		LEFT JOIN tbl_zonas z ON  z.id = l.zona_id
		WHERE inci.id =" .$id;
		$registro_sol = $mysqli->query($command)->fetch_assoc();

		$correo_supervisor = $registro_sol["supervisor_correo"];
		$jefe_comercial_correo = $registro_sol["jefe_comercial_correo"];
		unset($registro_sol["supervisor_correo"]);
		unset($registro_sol["jefe_comercial_correo"]);
		
		$body = "<table>";
		$body .= "<tbody>";
		foreach ($registro_sol as $key => $value) {
			$body .= "<tr><td><b>" . $key. " :</b></td><td>" .$value ."</td><tr>";
		}
		$body .= "</tbody>";
		$body .= "</table>";
	
		$cc = [];
		if( env("SEND_EMAIL") == "produccion")
		{
			if($correo_supervisor != "") 
			{
				$cc[] = $correo_supervisor;
			}
			if($jefe_comercial_correo != "") 
			{
				$cc[] = $jefe_comercial_correo;
			}
	
		}
		$bcc = [
			"neil.flores@testtest.kurax.dev",
			"rockypsn@testtest.gmail.com"];
		$mail = [
			"subject" => "Servicio Técnico Sol. Observada - " .$registro_sol["Local"],
			"body"    => $body,
			"cc"      => $cc,
			"bcc"     => $bcc,
		];
		//send_email_v6($mail);
    }

	$return["mensaje"] = "Solicitud Servidio Técnico ".$id." Actualizada";
	$return["curr_login"]=$login;
}

if(isset($_POST["set_servicio_tecnico_observado_update"])){
	$user_id = $login["id"];
	extract($_POST);
	if(!isset($estado) || $estado == ""){
		$return["error"] = "estado";
		$return["error_msg"] = "Debe Seleccionar Estado";
		$return["error_focus"] = "estado";
		die(json_encode($return));
	}
	
	if( !isset($motivo_observado) || $motivo_observado == "" ){
		$return["error"] = "motivo_observado";
		$return["error_msg"] = "Debe Seleccionar Motivo Observado";
		$return["error_focus"] = "motivo_observado";
		die(json_encode($return));
	}
	//$comentario = "";
	if( $estado == 2 ){
		if( !isset($tecnico_id) || $tecnico_id == "" ){
			$return["error"] = "tecnico_id";
			$return["error_msg"] = "Debe Seleccionar Técnico";
			$return["error_focus"] = "tecnico_id";
			die(json_encode($return));
		}
		//$comentario = ",comentario = '".$comentario."'";
		//$tecnico_id = ",tecnico_id = '" . $tecnico_id . "'";
	}

	if( $estado == 4 ){
		if(!isset($comentario_terminado) || $comentario_terminado == ""){
			$return["error"] = "estado";
			$return["error_msg"] = "Debe Seleccionar Estado";
			$return["error_focus"] = "estado";
			die(json_encode($return));
		}
		$comentario_terminado_correo = $comentario_terminado;

		$command = "SELECT sth.id AS id,
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
				IF(sth.comentario_terminado IS NULL , '---', sth.comentario_terminado ) AS comentario_terminado,
				sth.foto_terminado,
				sth.tecnico_id,
				inci.nota_tecnico,
				inci.local_id,
				steq.nombre as equipo_nombre
				FROM tbl_servicio_tecnico_historial sth
				LEFT JOIN tbl_servicio_tecnico inci ON inci.id = sth.servicio_tecnico_id
				LEFT JOIN tbl_locales l ON  l.id = inci.local_id
				LEFT JOIN tbl_zonas z ON  z.id = l.zona_id
				LEFT JOIN tbl_servicio_tecnico_estado  ste ON ste.id = sth.servicio_tecnico_estado_id
				LEFT JOIN tbl_servicio_tecnico_equipo steq ON steq.id = sth.equipo_id
				WHERE sth.servicio_tecnico_id = {$incidencia_id}
				ORDER BY id DESC
				LIMIT 1";
				
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
	
	$equipo_id = isset($equipo_id) ? "'$equipo_id'" : "";
	$tecnico_temp = isset($tecnico_id) && $tecnico_id != "" ? ",tecnico_id" : "";
	$tecnico_id = isset($tecnico_id) && $tecnico_id != "" ? ",'$tecnico_id'" : "";

	$comentario_terminado_temp = isset($comentario_terminado) && $comentario_terminado != "" ? ",comentario_terminado" : "";
	$comentario_terminado =      isset($comentario_terminado) && $comentario_terminado != "" ? ",'$comentario_terminado'" : "";
	$comentario_mantenimiento =  (isset($comentario_mantenimiento) || $comentario_mantenimiento != "") ? "'$comentario_mantenimiento'" : "'---'";
	
	$insert_command = "INSERT INTO `wwwapuestatotal_gestion`.`tbl_servicio_tecnico_historial`
	(
		servicio_tecnico_id
		,servicio_tecnico_estado_id
		,motivo_observado_id
		,equipo_id
		,comentario
		$comentario_terminado_temp
		,user_id
		,foto_terminado
		$tecnico_temp
		,created_at
		,updated_at
	)
		VALUES
	(
		 $incidencia_id
		,$estado
		,$motivo_observado
		,$equipo_id
		,'" . $comentario . "'
		$comentario_terminado
		, " . $login["id"] . "
		, '" . $foto_terminado ."'
		$tecnico_id
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

	if($estado == 4){
		$command = "UPDATE tbl_servicio_tecnico 
				SET 
					 estado_vt = '$estado_vt'
					,estado_servicio_tecnico_id = $estado		 
					,equipo_id = $equipo_id
					,updated_at = NOW()
					,motivo_observado_id = $motivo_observado
					,update_user_id = $user_id
					,fecha_cierre_vt = NOW()
					,comentario_vt = ".$comentario_mantenimiento."
				WHERE id = " . $incidencia_id;
		$mysqli->query($command);
		$return["update_command"] = $command;
		if($mysqli->error){
			print_r($mysqli->error);
			echo "\n";
			echo $command;
			exit();
		}
	}else{
		$command = "UPDATE tbl_servicio_tecnico 
					SET 
						 estado_vt = '$estado_vt'
						,estado_servicio_tecnico_id = $estado		 
						,equipo_id = $equipo_id
						,updated_at = NOW()
						,motivo_observado_id = $motivo_observado
						,update_user_id = $user_id
						,fecha_cierre_vt = $fecha_cierre
						,comentario_vt = ".$comentario_mantenimiento."
					WHERE id = " . $incidencia_id;
		$mysqli->query($command);
		$return["update_command"] = $command;
		if($mysqli->error){
			print_r($mysqli->error);
			echo "\n";
			echo $command;
			exit();
		}
	}
	

	if($estado == 4 )
	{
		$fecha_cierre = "now()";

		/*$path = "/var/www/html/files_bucket/servicio_tecnico/";
		$file = [];
		$imageLayer = [];
		if (!is_dir($path)) mkdir($path, 0777, true);

		$archivo = $_FILES['foto_terminado_update']["name"];
		$filename = $_FILES["foto_terminado_update"]['tmp_name'];
		$filenametem = $_FILES["foto_terminado_update"]['name'];

		$size = $_FILES["foto_terminado_update"]['size'];
		$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
		$valid_extensions = array('jpeg', 'jpg', 'png', 'gif');

		/*if($filename == ""){
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
		$foto_terminado = "'$nombre_archivo'";*/

		//MANDAR CORREO AL SUPERVISOR
		$razon_social_id = $razon_social_id;
		$obj_correo = new clsServicioTecnico();
		$response_mail = $obj_correo->enviar_correo_terminado($correos, $incidencia_id, $data_command, $comentario_terminado_correo, $razon_social_id);

		if($response_mail == "ok"){
			$return["email_sent"]="ok";
		}else{
			$return["error"]=$response_mail;
			$return["error_msg"]=$response_mail;
			$return["email_error"]=$response_mail;
		}
	}

    if($motivo_observado == 1)//MANTENIMIENTO
    {
        $command ="SELECT inci.id AS id,
            l.nombre AS local,
            z.id as zona_id,
            l.id as local_id,
            inci.incidencia_txt,	 
            inci.equipo_id ,
            IF(inci.estado_vt IS NULL , 'Derivado' ,inci.estado_vt) as estado,
            inci.created_at,
            inci.updated_at,				 
            inci.comentario_vt as comentario,
            inci.foto_terminado_vt as foto_terminado
            FROM .tbl_servicio_tecnico inci
            LEFT JOIN tbl_locales l ON  l.id = inci.local_id
            LEFT JOIN tbl_zonas z ON  z.id = l.zona_id
            WHERE inci.id = {$incidencia_id}";
        $list_query = $mysqli->query($command);
        $incidencia = $list_query->fetch_assoc();

        $insert_command = "INSERT INTO tbl_solicitud_mantenimiento 
			(created_at
			,user_id
			,zona_id
			,local_id
			,reporte
			,estado
			,comentario
			$comentario_terminado_temp
			)
			VALUES(
			now()
			," . $login["id"] . "
			," . ($incidencia["zona_id"] != "" ? $incidencia["zona_id"] : "null") . "
			," . $incidencia["local_id"] . "
			,'". $incidencia["comentario"] . "'
			,'Solicitud'
			,'" . $incidencia["comentario"] ."'
			$comentario_terminado
			)
		";
		$mysqli->query($insert_command);
		if($mysqli->error){
			print_r($mysqli->error);
			echo "\n";
			echo $insert_command;
			exit();
		}
		$id_solicitud_mantenimiento = $mysqli->insert_id;
	    $return["id_solicitud_mantenimiento"] = $id_solicitud_mantenimiento;

    }
    if($motivo_observado == 2 ) //REPUESTOS SUP Y JC
    {
		$razon_social_id = $razon_social_id;
        $command ="SELECT inci.id AS id,
			l.nombre AS 'Local',
			IF(z.nombre IS NULL , '---', z.nombre ) AS zona,	 
			inci.incidencia_txt  as reporte,	 
			inci.equipo_id ,
			IF(inci.estado_vt IS NULL , 'Derivado' ,inci.estado_vt) as estado,
			inci.created_at,
			inci.updated_at,				 
			inci.comentario_vt as comentario,
			inci.foto_terminado_vt as foto_terminado,
			( SELECT psop.correo
			FROM tbl_usuarios_locales ul
			LEFT JOIN tbl_locales l_sup on l_sup.id = ul.local_id
			LEFT JOIN tbl_usuarios  u_superv ON u_superv.id = ul.usuario_id
			LEFT JOIN tbl_personal_apt psop ON psop.id = u_superv.personal_id
			WHERE
				ul.local_id = l.id
				AND ul.estado = 1
				AND u_superv.estado = 1
				AND psop.area_id = 21
				AND psop.cargo_id = 4
				AND psop.estado = 1 LIMIT 1       
			) AS 'supervisor_correo',

			( SELECT psop.correo
			FROM tbl_usuarios_locales ul
			LEFT JOIN tbl_locales l_sup ON l_sup.id = ul.local_id
			LEFT JOIN tbl_usuarios  u_superv ON u_superv.id = ul.usuario_id
			LEFT JOIN tbl_personal_apt psop ON psop.id = u_superv.personal_id
			WHERE
				ul.local_id = l.id
				AND ul.estado = 1
				AND u_superv.estado = 1
				AND psop.area_id = 21
				AND psop.cargo_id = 16
				AND psop.estado = 1 LIMIT 1       
			) AS 'jefe_comercial_correo'
		FROM .tbl_servicio_tecnico inci
		LEFT JOIN tbl_locales l ON  l.id = inci.local_id
		LEFT JOIN tbl_zonas z ON  z.id = l.zona_id
		WHERE inci.id =" .$incidencia_id;
		$registro_sol = $mysqli->query($command)->fetch_assoc();

		$correo_supervisor = $registro_sol["supervisor_correo"];
		$jefe_comercial_correo = $registro_sol["jefe_comercial_correo"];
		unset($registro_sol["supervisor_correo"]);
		unset($registro_sol["jefe_comercial_correo"]);
		
		require_once('../sys/mailer/class.phpmailer.php');
		$mail1 = new PHPMailer(true);
		$mail1->IsSMTP(); 
		$mail1->SMTPDebug  = 1;                     
		$mail1->SMTPAuth   = true;                  
		$mail1->Host       = "smtp.gmail.com";     
		$mail1->Port       = 465;  
		$mail1->SMTPSecure = "ssl";
		if($correo_supervisor != ""){
			$mail1->AddAddress($correo_supervisor);
		}
		if($jefe_comercial_correo != ""){
			$mail1->AddAddress($jefe_comercial_correo);
		}
		if($razon_social_id == 5){
			$mail1->AddBCC("jose.jimenez@testtest.apuestatotal.com");
			$mail1->AddBCC("jose.rumay@testtest.apuestatotal.com");
			$mail1->AddBCC("jhossep.zamora@testtest.apuestatotal.com");
			// $mail1->AddBCC("victor.alayo@testtest.apuestatotal.com");
			// $mail1->AddBCC("antonio.cancino@testtest.kurax.dev");
		}else if($razon_social_id == 30){
			$mail1->AddBCC("pilar.martinez@testtest.igamingh.com");
			$mail1->AddBCC("jose.jimenez@testtest.apuestatotal.com");
			$mail1->AddBCC("jhossep.zamora@testtest.apuestatotal.com");
			// $mail1->AddBCC("antonio.cancino@testtest.kurax.dev");
		}
		// $mail1->AddBCC("victor.alayo@testtest.apuestatotal.com");
		$mail1->AddBCC("neil.flores@testtest.kurax.dev");  
		$mail1->Username   =env('MAIL_GESTION_USER');  
		$mail1->Password   =env('MAIL_GESTION_PASS');        
		$mail1->FromName = "Apuesta Total";
		$mail1->Subject = "Servicio Tecnico Sol. Observada - " .$registro_sol["Local"];
		$mail1->Body = "";
		$mail1->Body .= "<table>";
		$mail1->Body .= "<tbody>";
		//$body = "<table>";
		//$body .= "<tbody>";
		foreach ($registro_sol as $key => $value) {
			$mail1->Body .= "<tr><td><b>" . $key. " :</b></td><td>" .$value ."</td><tr>";
		}
		$mail1->Body .= "</table>";
		$mail1->Body .= "</tbody>";

		$mail1->isHTML(true);
		if($mail1->send()) $return["email_sent"]="ok";
    }

	$return["mensaje"] = "Solicitud Servicio Técnico ".$id." Actualizada";
	$return["curr_login"]=$login;
}

$return["memory_end"]=memory_get_usage();
$return["time_end"] = microtime(true);
$return["memory_total"]=($return["memory_end"]-$return["memory_init"]);
$return["time_total"]=($return["time_end"]-$return["time_init"]);
print_r(json_encode($return));
?>