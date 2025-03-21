<?php
include("db_connect.php");
include("sys_login.php");

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
            //es mas ancha $resizeheight
            $resizewidth = $image_width * $escalaW;
            $resizeheight = $image_height * $escalaW;

        } else {
            //es mas larga
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

if (isset($_POST['get_archivos'])) {

    $id = $_POST['get_archivos']['id'];
    $tipo = $_POST['get_archivos']['tipo'];
    $consulta = [];
    $retorna = [];
    $query = "SELECT archivo from tbl_caja_clientes_depositos_archivos WHERE deposito_id = " . $id . " AND tipo= ".$tipo." ORDER BY created_at DESC";
    $result = $mysqli->query($query);
    while ($r = $result->fetch_assoc()) $consulta[] = $r;
    foreach ($consulta as $key => $value) {
        $retorna[$key] = $value;
    }
    print_r(json_encode($retorna));
};



if (isset($_POST["sec_registro_depositos_archivos"])) {

    $id = $_POST['id_deposito'];
    $tipo = $_POST['tipo'];
    $result = [];
    $cantidad=count($_FILES['files']['name']);

//estado  0 =pendiente , 1 = validado,  2 rechazado
    if($tipo==1){
        $estado=0;
    }else if($tipo==2){
        $estado=$_POST["estado_dep"];
    }
    //else if($tipo==3){$estado=2;}


    for ($i = 0; $i < $cantidad; $i++) {
        $path = "/var/www/html/files_bucket/depositos/";
        $file = [];
        $imageLayer = [];
        if (!is_dir($path)) mkdir($path, 0777, true);


        /* resize de la imagen */
        $imageProcess = 0;

        $filename = $_FILES['files']['tmp_name'][$i];
        $filenametem = $_FILES['files']['name'][$i];
        $ext = pathinfo($filenametem, PATHINFO_EXTENSION);
        if($filename!=""){
            $fileExt = pathinfo($_FILES['files']['name'][$i], PATHINFO_EXTENSION);


            $resizeFileName = $id. "_" . date('YmdHis');
            $nombre_archivo=$resizeFileName . $i . "." . $fileExt;
            if($fileExt=="pdf"){
                move_uploaded_file($_FILES['files']['tmp_name'][$i], $path. $nombre_archivo);
            }
            else{       
                $sourceProperties = getimagesize($filename);
                $size = $_FILES['files']['size'][$i];
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

                $file[0] = imagejpeg($imageLayer[0], $path . $nombre_archivo);
                $file[1] = imagejpeg($imageLayer[1], $path . "min_" . $nombre_archivo);
                move_uploaded_file($file[0], $path . $nombre_archivo);
                move_uploaded_file($file[1], $path . $nombre_archivo);
                $imageProcess = 1;
                /* resize de la imagen esto es una prueba */
            }

            $comando=" INSERT INTO tbl_caja_clientes_depositos_archivos
                        (deposito_id,tipo,archivo,created_at,estado)
                        VALUES(
                            '" . $id . "',
                            $tipo,
                            '" . $nombre_archivo . "',
                            '" . date('Y-m-d H:i:s') . "',
                            1
                            )";
            $mysqli->query($comando);
            $insert_id = mysqli_insert_id($mysqli);

            $filepath = $path . $resizeFileName . "." . $fileExt;
            $result = [
                'id' => $insert_id,
                'filename' => $nombre_archivo,
                'filepath' => $filepath,
                'mensaje' =>"Solicitud de Validación ".$id." - Archivo Registrado"
            ];
        }

 
    }
   if($tipo==2){//validador
            $udpate_command = " UPDATE tbl_caja_clientes_depositos SET 
                         estado=$estado
                        ,update_user_at=now()
                        ,update_user_id=".$login["id"]." 
                        WHERE id= ".$id." and estado!= 1";
            $mysqli->query($udpate_command);

        $mensaje=$estado==1?"Validada":"Pendiente";
        if($estado==2){$mensaje="Rechazada";}
        $result["mensaje"]="Solicitud de Validación ".$id." ".$mensaje;


    }

    $result["curr_login"]=$login;
    echo json_encode($result);

}


?>
