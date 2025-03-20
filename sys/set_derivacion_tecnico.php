<?php
$return = array();
$return["memory_init"]=memory_get_usage();
$return["time_init"] = microtime(true);
include("global_config.php");
include("db_connect.php");
include("sys_login.php");
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
if(isset($_POST["sec_derivacion_tecnico_cargar_solicitud"])){
	$solicitud_id = $_POST["solicitud_id"];
	$command ="SELECT sm.id AS id,
				l.nombre AS local,
				z.nombre AS zona,
				sis.nombre AS sistema ,
				sm.reporte ,
				sm.tipo_mantenimiento,
				sm.estado,
				sm.created_at,
				sm.updated_at,
				sm.latitud,
				sm.longitud,
				sm.comentario,
				sm.foto_terminado,
                sm.tecnico_id,
                sm.criticidad,
				sm.comentario_terminado
				FROM .tbl_solicitud_mantenimiento sm
				LEFT JOIN tbl_locales l ON  l.id = sm.local_id
				LEFT JOIN tbl_zonas z ON  z.id = sm.zona_id
				LEFT JOIN tbl_solicitud_mantenimiento_sistema sis ON  sis.id = sm.sistema_id
				WHERE sm.id = {$solicitud_id}";
	$list_query=$mysqli->query($command);
	$list = array();
	while ($li=$list_query->fetch_assoc()) {
		$list[]=$li;
	}

	$command = "SELECT 
					sma.id,
					sma.solicitud_mantenimiento_id,
					sma.archivo
				FROM tbl_solicitud_mantenimiento_archivos sma
				WHERE sma.solicitud_mantenimiento_id = {$solicitud_id}";
	$list_query=$mysqli->query($command);
	$imagenes = array();
	while ($li=$list_query->fetch_assoc()) {
		$imagenes[]=$li;
	}
	$return["local"] = $list[0];
	$return["imagenes"] = $imagenes;
}
if(isset($_POST["set_derivacion_tecnico_update"])){
	$user_id = $login["id"];
	extract($_POST);
	if(!isset($tipo_mantenimiento) || $tipo_mantenimiento == ""){
		$return["error"]="tipo_mantenimiento";
		$return["error_msg"]="Debe Seleccionar Tipo Mantenimiento";
		$return["error_focus"]="tipo_mantenimiento";	
	}
	else if(!isset($estado) || $estado == ""){
		$return["error"]="estado";
		$return["error_msg"]="Debe Seleccionar Estado";
		$return["error_focus"]="estado";	
	}
	else{
		$fecha_cierre = "null";
		$foto_terminado = "";
		if($estado == "Terminado"){
			$fecha_cierre = "now()";

			$path = "/var/www/html/files_bucket/solicitud_mantenimiento/";
			$file = [];
			$imageLayer = [];
			if (!is_dir($path)) mkdir($path, 0777, true);

			$archivo = $_FILES['foto_terminado_update']["name"];
			$filename = $_FILES["foto_terminado_update"]['tmp_name'];
			$filenametem = $_FILES["foto_terminado_update"]['name'];

			$size = $_FILES["foto_terminado_update"]['size'];
			$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
			$valid_extensions = array('jpeg', 'jpg', 'png', 'gif');

			if($filename == "" && $comentario_terminado == ""){
				$return["error"]="imagen";
				$return["error_msg"] = "Debe Ingresar Imagen o Comentario ".$comentario_terminado;
				$return["error_focus"]="foto_terminado_update";
				print_r(json_encode($return));
				die();
			}
            if($filename != ""){
                if(!in_array($ext, $valid_extensions)) {
                    $return["error"]="ext";
                    $return["error_msg"] = "Solo es permitido las extensiones: 'jpeg', 'jpg', 'png', 'gif'";
                    print_r(json_encode($return));
                    die();
                }
                if($size > 10485760){//10 mb
                    $return["error"]="size";
                    $return["error_msg"] ="Archivo supera la cantidad máxima permitida (10 MB)";
                    print_r(json_encode($return));
                    die();
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
                $foto_terminado = ",foto_terminado = '$nombre_archivo'";
            }
		}

        $comentario_terminado = "";
		if($estado == "Terminado"){
			$comentario_terminado = ",comentario_terminado = '".$_POST["comentario_terminado"]."'";
		}
        $tecnico_id = isset($tecnico_id) ? $tecnico_id : "null";
		$command = "
			UPDATE tbl_solicitud_mantenimiento SET 
				 estado = '$estado'
				,tipo_mantenimiento = '$tipo_mantenimiento'
				,updated_at = now()
				,update_user_id = $user_id
				,fecha_cierre = $fecha_cierre
				 $foto_terminado
				 $comentario_terminado
			WHERE id = ".$id;
		$mysqli->query($command);
		if($mysqli->error){
			print_r($mysqli->error);
			echo "\n";
			echo $command;
			exit();
		}
		$return["mensaje"] = "Solicitud de Mantenimiento ".$id." Actualizada";
		$return["curr_login"]=$login;
	}
}

$return["memory_end"]=memory_get_usage();
$return["time_end"] = microtime(true);
$return["memory_total"]=($return["memory_end"]-$return["memory_init"]);
$return["time_total"]=($return["time_end"]-$return["time_init"]);
print_r(json_encode($return));
?>