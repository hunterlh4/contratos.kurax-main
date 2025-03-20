<?php
$return = array();
$return["memory_init"] = memory_get_usage();
$return["time_init"] = microtime(true);
include("global_config.php");
include("db_connect.php");
include("sys_login.php");
require_once '/var/www/html/env.php';
require_once '/var/www/html/sys/helpers.php';
require_once '/var/www/html/phpexcel/classes/PHPExcel.php';

function log_incidencias($msg, $valid)
{
    if (is_array($msg)) {
        $msg = json_encode($msg, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    $msg = date("Y-m-d H:i:s ") . $msg;
    if (in_array(1, $valid)) {
        file_put_contents('../logs/incidencias.log', $msg . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
    if (in_array(2, $valid)) {
        file_put_contents('../logs/incidencias_bad.log', $msg . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}

function resizeImage($resourceType, $image_width, $image_height)
{
    $imagelayer = [];
    if ($image_width < 1920 && $image_height < 1080) {

        $imagelayer[0] = imagecreatetruecolor($image_width, $image_height);
        imagecopyresampled($imagelayer[0], $resourceType, 0, 0, 0, 0, $image_width, $image_height, $image_width, $image_height);

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
        $imagelayer[0] = imagecreatetruecolor($resizewidth, $resizeheight);
        imagecopyresampled($imagelayer[0], $resourceType, 0, 0, 0, 0, $resizewidth, $resizeheight, $image_width, $image_height);

    }
    return $imagelayer;
}

if (isset($_POST["sec_incidencias_save"])) {
    $data = $_POST["sec_incidencias_save"];

    if ($data["incidencia_txt"] == "") {
        $return["error"] = "incidencia_txt";
        $return["error_msg"] = "Debe ingresar Incidencia";
        $return["error_focus"] = "incidencia_txt";

    } elseif ($data["local_id"] == "" || $data["local_id"] == 0) {
        $return["error"] = "local_id";
        $return["error_msg"] = "Debe Seleccionar Tienda";
        $return["error_focus"] = "local_id";
    } elseif (!isset($data["selectProducto"]) || $data["selectProducto"] == "") {
        $return["error"] = "selectProducto";
        $return["error_msg"] = "Debe Seleccionar Producto";
        $return["error_focus"] = "selectProducto";
    } elseif (!isset($data["selectTipo"]) || $data["selectTipo"] == "") {
        $return["error"] = "selectTipo";
        $return["error_msg"] = "Debe Seleccionar Tipo";
        $return["error_focus"] = "selectTipo";
    } elseif (!isset($data["telefono2"]) || $data["telefono2"] == "") {
        $return["error"] = "telefono2";
        $return["error_msg"] = "Debe ingresar Teléfono";
        $return["error_focus"] = "telefono2";
    } elseif (!isset($data["reimpresion"]) || $data["reimpresion"] == "") {
        $return["error"] = "reimpresion";
        $return["error_msg"] = "Debe seleccionar Reimpresión";
    } else {
        $caracteres = strlen($data["incidencia_txt"]);
        if ($caracteres > 160) {

            $return["error"] = "incidencia_txt";
            $return["error_msg"] = "Máximo 160 caracteres,  ingresó " . $caracteres;
            $return["error_focus"] = "incidencia_txt";
        } else {
            $tienda_id = $data["local_id"];
            $command = " SELECT id,created_at,update_user_at
				FROM tbl_soporte_incidencias
				WHERE estado IN (0,2,3) AND local_id = $tienda_id
				ORDER BY id DESC
			";
            $list_query = $mysqli->query($command);
            $lista_pendiente = array();
            while ($li = $list_query->fetch_assoc()) {
                $lista_pendiente[] = $li;
            }
            $incidencias = count($lista_pendiente);
            if ($incidencias > 0) {
                //$text = $incidencias > 1 ? " incidencias" : " incidencia";
                //$return["error_msg"] = "Actualmente tienes " . count($lista_pendiente) . $text ."  en curso";
                $return["error_msg"] = "Ha alcanzado el límite de incidencias abiertas";
                $return["error"] = "local_id";
                $return["swal_type"] = "error";
                $return["swal_timeout"] = 5000;
            } else {
                $incidencia_txt = $mysqli->real_escape_string($data["incidencia_txt"]);
                $producto = $mysqli->real_escape_string($data["selectProducto"]);
                $tipo = $mysqli->real_escape_string($data["selectTipo"]);
                $telefono2 = $mysqli->real_escape_string($data["telefono2"]);
                $teamviewer_id = $data["teamviewer_id"] != "" ? "'" . $mysqli->real_escape_string($data["teamviewer_id"]) . "'" : 'null';
                $teamviewer_password = $data["teamviewer_password"] != "" ? "'" . $mysqli->real_escape_string($data["teamviewer_password"]) . "'" : 'null';
                $reimpresion = $data["reimpresion"];
                $insert_command = "
				INSERT INTO tbl_soporte_incidencias 
				(created_at
				,user_id
				,local_id
				,incidencia_txt
				,estado
				,producto
				,teamviewer_id
				,teamviewer_password
				,reimpresion
				,telefono2
				,tipo)
				VALUES(
				now()
				," . $login["id"] . "
				," . $data["local_id"] . "
				,'" . $incidencia_txt . "'
				,0
				,'$producto'
				,$teamviewer_id
				,$teamviewer_password
				,$reimpresion
				," . $telefono2 . "
				,'$tipo')
				";
                $mysqli->query($insert_command);
                if ($mysqli->error) {
                    print_r($mysqli->error);
                    echo "\n";
                    echo $insert_command;
                    exit();
                }
                $return["id"] = $mysqli->insert_id;
                $return["curr_login"] = $login;
                $return["mensaje"] = "Incidencia Enviada";
            }
        }
    }
}

if (isset($_POST["set_incidencias_get"])) {

    $return = [
        'error' => false,
        'status' => 'success',
        'mensaje' => 'Ok.'
    ];

    $incidencia_id = $_POST["set_incidencias_get"];
    $list = get_incidencia_by_id($incidencia_id);
    $objeto = $list[0];
    $estado = intval($objeto["estado"]);
    // 0 => Nuevo, 1 => Atendido, 2 => Asignado
    if ($estado == 0) {
        $return['mensaje'] = "El caso aún no ha asignado.";
    } else if ($estado == 1) {
        $return['mensaje'] = "El caso ya ha sido atendido.";
    }

    if ($estado == 0 || $estado == 1) {
        $return['status'] = 'warning';
        $return['error'] = true;
    }

    $return['incidencia'] = $objeto;
    echo json_encode($return);
    die();
}

if (isset($_POST["set_incidencias_get_estado"])) {

    $return = [
        'error' => false,
        'status' => 'success',
        'mensaje' => 'Ok.'
    ];

    $incidencia_id = $_POST["set_incidencias_get_estado"];
    $list = get_estado_incidencia_by_id($incidencia_id);
    $objeto = $list[0];
    $estado = intval($objeto["estado"]);
    // 0 => Nuevo, 1 => Atendido, 2 => Asignado
    $return['estado'] = $estado;
    echo json_encode($return);
    die();
}

if (isset($_POST["set_incidencias_asignar"])) {

    $incidencia_id = $_POST["set_incidencias_asignar"];

    $command = "SELECT 
                    COUNT(*) AS incidencias_asignadas  
                FROM 
                    tbl_soporte_incidencias
                WHERE 
                    CASE
                        WHEN agente_reasignado_id is null THEN agente_1_id = '" . $login["id"] . "'
                        ELSE agente_reasignado_id = '" . $login["id"] . "'
                    END = 1
                AND 
                    estado = 2";

    $res_query = $mysqli->query($command)->fetch_assoc();
    if ($mysqli->error) {
        print_r($mysqli->error);
    }

    if ($res_query["incidencias_asignadas"] >= 5) {
        $return["error"] = "Tiene 5 incidencias asignadas";
        $return["swal_type"] = "error";
        $return["swal_timeout"] = 3000;
        $return["login_id"] = $login["id"];
        echo json_encode($return);
        die();
    }

    $list = get_incidencia_by_id($incidencia_id);
    $objeto = $list[0];
    $estado = $objeto["estado"];
    $agente_id = $objeto["agente_1_id"];
    $status = "warning";
    $incidencia_ya_asignada = false;
    $incidencia_ya_atendida = false;

    // 0 => Nuevo, 1 => Atendido, 2 => Asignado
    if ($estado == 0) { //
        $udpate_command = "UPDATE tbl_soporte_incidencias SET 
            estado = 2
            ,update_user_at = now()
            ,fecha_asignada = now()
            ,update_user_id = '" . $login["id"] . "' 
            ,agente_1_id = '" . $login["id"] . "'  
                where id= " . $incidencia_id;
        $mysqli->query($udpate_command);
        $status = "success";
        $return["mensaje"] = "Incidencia " . $incidencia_id . " asignada";
    } else if ($estado == 2) {
        $return["mensaje"] = "El caso ya ha sido asignado.";
        $incidencia_ya_asignada = true;
    } else {
        $return["mensaje"] = "El caso ya ha sido atendido.";
        $incidencia_ya_atendida = true;
    }

    $return["login_id"] = $login["id"];
    $return["status"] = $status;
    $return["agente_id"] = $agente_id;
    $return["incidencia_ya_atendida"] = $incidencia_ya_atendida;
    $return["incidencia_ya_asignada"] = $incidencia_ya_asignada;
    $return["incidencia"] = $objeto;
}

if (isset($_POST['set_incidencias_reasignar'])) {
    $incidencia_id = $_POST["set_incidencias_reasignar"];

    $command = "SELECT 
                    COUNT(*) AS incidencias_asignadas  
                FROM 
                    tbl_soporte_incidencias
                WHERE 
                    CASE
                        WHEN agente_reasignado_id is null THEN agente_1_id = '" . $login["id"] . "'
                        ELSE agente_reasignado_id = '" . $login["id"] . "'
                    END = 1
                AND 
                    estado = 2";

    $res_query = $mysqli->query($command)->fetch_assoc();
    if ($mysqli->error) {
        print_r($mysqli->error);
    }

    if ($res_query["incidencias_asignadas"] >= 5) {
        $return["error"] = "Tiene 5 incidencias asignadas";
        $return["msg"] = "Tiene 5 incidencias asignadas";
        $return["swal_type"] = "error";
        $return["swal_timeout"] = 3000;
        $return["login_id"] = $login["id"];
        echo json_encode($return);
        die();
    }

    $return = [
        'error' => false,
        'msg' => '',
        'swal_type' => 'error',
        'swal_timeout' => 3000,
        "login_id" => $login['id'],
        "incidencia_id" => $incidencia_id
    ];
    $command = "SELECT inci.estado FROM tbl_soporte_incidencias inci where inci.id=" . $incidencia_id . " limit 1";
    $list_query = $mysqli->query($command);
    if ($mysqli->error) {
        $return['msg'] = $mysqli->error;
        $return['error'] = true;
        echo json_encode($return);
        die();
    }
    $list = array();
    while ($li = $list_query->fetch_assoc()) {
        $list[] = $li;
    }

    $res_query = $list[0];

    if ($res_query["estado"] == 0 || $res_query["estado"] == 1) {
        $return['error'] = true;
        $return['swal_type'] = 'warning';
    }

    if ($res_query["estado"] == 0) {
        $return["msg"] = "El caso aún no ha sido asignado.";
    } else if ($res_query["estado"] == 1) {
        $return["msg"] = "El caso ya ha sido atendido.";
    }

    if ($return['error']) {
        echo json_encode($return);
        die();
    }

    $update_command = "
			UPDATE tbl_soporte_incidencias set
				updated_at=now()
			    ,fecha_asignada = now()
				, update_user_id = '" . $login['id'] . "'
				, agente_reasignado_id = '" . $login['id'] . "' 
				where id={$incidencia_id}";

    $mysqli->query($update_command);
    if ($mysqli->error) {
        $return['msg'] = $mysqli->error;
        $return['error'] = true;
        echo json_encode($return);
        die();
    }

    $return["msg"] = 'El caso fue reasignado con éxito';
    $return["swal_type"] = 'success';
    echo json_encode($return);
    die();
}

if (isset($_POST["set_incidencias_solve"])) {
    extract($_POST);
    $msg_bad_incidencia = [];
    $delimiter_log = '-----------------------------------------------------------------------------------------------------';
    $msg_bad_incidencia[] = $delimiter_log;
    //log_incidencias($delimiter_log,[1]);
    $agente_id = $login["id"];
    //$data = $_POST["set_incidencias_solve"];
    $solucion_txt = $_POST["solucion_txt"];
    //$incidencia_id = $data["incidencia_id"];
    $recomendacion = isset($selectRecomendacion) ? $selectRecomendacion : '';
    $local = isset($local) ? $local : '';
    $sql_local_id = "SELECT id, nombre FROM tbl_locales 
                    WHERE nombre = '$local'";
    $locales = $mysqli->query($sql_local_id)->fetch_assoc();
    $local_id = $locales['id'];
    $incidencia_txt = isset($incidencia_txt) ? $incidencia_txt : '';
    $reimpresion = isset($reimpresion) ? $reimpresion : '';
    if($reimpresion == 'No'){
        $reimpresion = 0;
    }else{
        $reimpresion = 1;
    }

    $res_query = get_agente_asignado($incidencia_id);
    $error = false;

    if ($res_query["estado"] == 0) {
        $return["error_msg"] = "El caso aún no ha sido asignado.";
        $error = true;
    } else if ($res_query["estado"] == 1) {
        $return["error_msg"] = "El caso ya ha sido atendido.";
        $error = true;
    } else if ($res_query["estado"] == 2) {
        $puede_solucionar = false;
        if ($res_query["agente_reasignado_id"] != null) {
            if ($res_query["agente_reasignado_id"] == $login['id']) {
                $puede_solucionar = true;
            }
        } else {
            if ($res_query['agente_1_id'] == $login['id']) {
                $puede_solucionar = true;
            }
        }
        if (!$puede_solucionar) {
            $return["error_msg"] = "El caso no le pertenece, debe reasignarselo.";
            $error = true;
        }
    }

    if ($error) {
        $return["error"] = true;
        $return["swal_type"] = "error";
        $return["swal_timeout"] = 3000;
        echo json_encode($return);
        die();
    }

    $recomendacion = isset($recomendacion) ? $recomendacion : '';
    $producto = isset($producto) ? $producto : '';
    $tipo = isset($tipo) ? $tipo : '';

    $equipo_id = isset($equipo_id) ? $equipo_id : '';
    $equipo_temp = isset($equipo_id) ? ",equipo_id =" . $equipo_id : ",equipo_id = null ";
    $nota_tecnico = isset($nota_tecnico) ? $nota_tecnico : '';
    $nota_soporte = isset($nota_soporte) ? $nota_soporte : '';
    //$periferico = isset($data["periferico"]) ? implode(", ", $data["periferico"]) : '';
    //$periferico_q = $periferico != "" ? ", periferico = '" . implode (", ", $data["periferico"]) . "'" : "" ;

    $foto = "";
    if ($recomendacion === "Visita Técnica") {
        if (file_exists($_FILES['foto']['tmp_name'])) {
            $fecha_cierre = "now()";

            $path = "/var/www/html/files_bucket/files_incidencia/";
            $file = [];
            $imageLayer = [];
            if (!is_dir($path)) mkdir($path, 0777, true);

            $archivo = $_FILES['foto']["name"];
            $filename = $_FILES["foto"]['tmp_name'];
            $filenametem = $_FILES["foto"]['name'];

            $size = $_FILES["foto"]['size'];
            $ext = pathinfo($filenametem, PATHINFO_EXTENSION);
            $valid_extensions = array('jpeg', 'jpg', 'png', 'gif');

            if ($filename == "") {
                $return["error"] = "imagen";
                $return["error_msg"] = "Debe Ingresar un archivo de imagen";
                $return["error_focus"] = "foto";
                die(json_encode($return));
            }
            if (!in_array($ext, $valid_extensions)) {
                $return["error"] = "ext";
                $return["error_msg"] = "Solo es permitido las extensiones: 'jpeg', 'jpg', 'png', 'gif'";
                die(json_encode($return));
            }
            if ($size > 10485760) {//10 mb
                $return["error"] = "size";
                $return["error_msg"] = "Archivo supera la cantidad máxima permitida (10 MB)";
                die(json_encode($return));
            }
            $nombre_archivo = "";
            if ($filename != "") {
                $fileExt = pathinfo($_FILES["foto"]['name'], PATHINFO_EXTENSION);

                $resizeFileName = date('YmdHis');
                $nombre_archivo = $incidencia_id . "_" . $filenametem . "_" . $resizeFileName . "." . $fileExt;

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
            $foto = $nombre_archivo;
        }
    }

    if (trim($solucion_txt) === "") {
        $return["error"] = "solucion_txt";
        $return["error_msg"] = "Debe ingresar Solución";
        $return["error_focus"] = "solucion_txt";
    } else if ($recomendacion === "Visita Técnica" && trim($nota_tecnico) === "") {
        $return["error"] = "nota_tecnico";
        $return["error_msg"] = "Debe ingresar Nota para el técnico";
        $return["error_focus"] = "nota_tecnico";
    } else if ($tipo_inc === "") {
        $return["error"] = "tipo_inc";
        $return["error_msg"] = "Debe seleccionar Tipo Incidente";
        $return["error_focus"] = "tipo_inc";
    } else if ($detalle_inc === "") {
        $return["error"] = "detalle_inc";
        $return["error_msg"] = "Debe seleccionar Detalle Incidente";
        $return["error_focus"] = "detalle_inc";
    } else {
        $incidencia_txt = $mysqli->real_escape_string($incidencia_txt);
        $solucion_txt = $mysqli->real_escape_string($solucion_txt);
        $nota_tecnico = $mysqli->real_escape_string($nota_tecnico);
        $nota_soporte = $mysqli->real_escape_string($nota_soporte);

        if ($recomendacion === "Visita Técnica") {

            $insert_command = "INSERT INTO tbl_servicio_tecnico 
            (user_id,local_id,producto,incidencia_txt,estado,agente_1_id,fecha_asignada,solucion_txt,recomendacion,equipo_id,nota_tecnico,reimpresion,tipo,foto_terminado_vt,created_at,soporte_incidencias_id)
            VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

            try{
                $prepare_query = mysqli_prepare($mysqli, $insert_command);
                if(!$prepare_query){
                    // $return['error_query'] = $error_message;
                    $return['error_msg'] = "Error en inserción a tbl_servicio técnico";
                    $return['error_query'] = mysqli_error($mysqli);
                    echo json_encode($return);
                    die();
                }
                date_default_timezone_set('America/Lima');
                $fecha_actual = new DateTime();
                $formato = "Y-m-d H:i:s";
                $fecha_formateada = $fecha_actual->format($formato);

                $incidencia_txt = stripslashes($incidencia_txt); 
                $solucion_txt = stripslashes($solucion_txt);
                $nota_tecnico = stripslashes($nota_tecnico);

                $estado = 1;
                $fecha_actual = date("Y-m-d H:i:s");
                mysqli_stmt_bind_param($prepare_query, 'iissiisssisisssi', 
                    $agente_id, 
                    $local_id, 
                    $producto, 
                    $incidencia_txt, 
                    $estado, 
                    $agente_id, 
                    $fecha_formateada, 
                    $solucion_txt, 
                    $recomendacion, 
                    $equipo_id,
                    $nota_tecnico,
                    $reimpresion,
                    $tipo,
                    $foto,
                    $fecha_formateada,
                    $incidencia_id
                );
    
                $result = mysqli_stmt_execute($prepare_query);

                if (!$result) {
                    $return['error_query'] = mysqli_error($mysqli);
                    echo json_encode($return);
                    die();
                }

                mysqli_stmt_close($prepare_query);
                
            }catch (Exception $e) {
                $error_message = "Error en la inserción: " . $e->getMessage();
                $return['error_query'] = $error_message;
                echo json_encode($return);
                die();
            }

            // $mysqli->query($insert_command);
            // if ($mysqli->error) {
            //     print_r($mysqli->error);
            //     log_incidencias($mysqli->error, [2]); // 1 -> /incidencias.log  2 -> /incidencias_bad.log
            //     echo "\n";
            //     echo $update_command;
            //     log_incidencias($update_command, [2]); // 1 -> /incidencias.log  2 -> /incidencias_bad.log
            //     exit();
            // }

            $update_command = "UPDATE tbl_soporte_incidencias SET
            estado = ?,
            recomendacion = ?,
            solucion_txt = ?,
            nota_tecnico = ?
            WHERE id = ?";

            try{
                $prepare_query = mysqli_prepare($mysqli, $update_command);
                if(!$prepare_query){
                    // $return['error_query'] = $error_message;
                    $return['error_msg'] = "Error en actualizar tbl_soporte_incidencias";
                    $return['error_query'] = mysqli_error($mysqli);
                    echo json_encode($return);
                    die();
                }
                $estado = 1;
                mysqli_stmt_bind_param($prepare_query, 'isssi', 
                    $estado, 
                    $recomendacion, 
                    $solucion_txt, 
                    $nota_tecnico, 
                    $incidencia_id
                );
                $result = mysqli_stmt_execute($prepare_query);
                if (!$result) {
                    $return['error_query'] = mysqli_error($mysqli);
                    echo json_encode($return);
                    die();
                }
                mysqli_stmt_close($prepare_query);
            }catch(Exception $e){
                $error_message = "Error al updatear: " . $e->getMessage();
                $return['error_query'] = $error_message;
                echo json_encode($return);
                die();
            }


            // $update_estado_incidencia = "UPDATE tbl_soporte_incidencias SET
            // estado = 1,
            // recomendacion = '" . $recomendacion . "',
            // solucion_txt = '" . $solucion_txt . "',
            // nota_tecnico = '" . $nota_tecnico . "' 
            // WHERE id =" . $incidencia_id;

            // $mysqli->query($update_estado_incidencia);
            // if ($mysqli->error) {
            //     print_r($mysqli->error);
            //     log_incidencias($mysqli->error, [2]); // 1 -> /incidencias.log  2 -> /incidencias_bad.log
            //     echo "\n";
            //     // echo $update_command;
            //     // log_incidencias($update_command, [2]); // 1 -> /incidencias.log  2 -> /incidencias_bad.log
            //     exit();
            // }

            // HISTORIAL SERVICIO TECNICO
            $insert_command = "INSERT INTO `wwwapuestatotal_gestion`.`tbl_servicio_tecnico_historial`
                (
                    servicio_tecnico_id
                    ,servicio_tecnico_estado_id
                    ,equipo_id
                    ,user_id
                    ,foto_terminado
                    ,created_at
                )
                    VALUES
                (
                    $incidencia_id
                    ,2
                    ,$equipo_id
                    , " . $login["id"] . "
                    , '" . $foto . "'
                    ,NOW()
                )
                ";

            $return["insert_command"] = $insert_command;
            $mysqli->query($insert_command);
            if ($mysqli->error) {
                print_r($mysqli->error);
                echo "\n";
                echo $insert_command;
                exit();
            }

        } else {
            $vtipo_inc = $tipo_inc ?? "";
            $vdetalle_inc = $detalle_inc ?? "";
            $update_command = "
                UPDATE tbl_soporte_incidencias set 
                    estado=1
                    ,updated_at=now()
                    ,update_user_id= $agente_id
                    ,agente_2_id = $agente_id
                    ,fecha_solucion=now()
                    ,solucion_txt=  '$solucion_txt'
                    ,recomendacion =  '$recomendacion'
                    ,foto_terminado_vt = '$foto'
                    $equipo_temp
                    ,nota_tecnico =  '$nota_tecnico'
                    ,nota_soporte =  '$nota_soporte'
                    ,producto =  '$producto'
                    ,tipo =  '$tipo'
                    ,tipo_incidencia = '$vtipo_inc'
                    ,detalle_incidencia = '$vdetalle_inc'
                 where id=" . $incidencia_id;
            
            $mysqli->query($update_command);
            if ($mysqli->error) {
                print_r($mysqli->error);
                log_incidencias($mysqli->error, [2]); // 1 -> /incidencias.log  2 -> /incidencias_bad.log
                echo "\n";
                echo $update_command;
                log_incidencias($update_command, [2]); // 1 -> /incidencias.log  2 -> /incidencias_bad.log
                exit();
            }
        }

        /*mail*/
        //log_incidencias("get incidencia data",[1]); // 1 -> /incidencias.log  2 -> /incidencias_bad.log
        $msg_bad_incidencia[] = "get incidencia $incidencia_id data";
        $command = "
  			SELECT 
  				inc.id AS 'ID de incidencia',
				inc.update_user_at AS 'Fecha y hora de incidencia',
				l.nombre AS 'Tienda',
				l.email AS 'tienda_email',
				u.usuario AS 'Usuario',
				inc.incidencia_txt AS 'Descripción de la incidencia',
				inc.solucion_txt AS 'Observación',
				ste.nombre AS 'equipo',
				u_soporte.usuario AS 'Agente de Soporte',
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
					AND psop.estado = 1        limit 1       
				) AS 'supervisor_correo',
                papt.correo AS jefe_zona_correo
				FROM tbl_soporte_incidencias inc
				LEFT JOIN tbl_locales l ON l.id = inc.local_id
				LEFT JOIN tbl_usuarios u ON u.id = inc.user_id
				LEFT JOIN tbl_usuarios u_soporte ON u_soporte.id = inc.update_user_id
				LEFT JOIN tbl_servicio_tecnico_equipo ste ON ste.id = inc.equipo_id
                LEFT JOIN tbl_zonas zona ON l.zona_id = zona.id
                LEFT JOIN tbl_personal_apt papt ON zona.jop_id = papt.id
				LEFT JOIN tbl_servicio_tecnico tst ON tst.soporte_incidencias_id = inc.id 
				WHERE inc.id = " . $incidencia_id;
        $incidencia = $mysqli->query($command)->fetch_assoc();
        //log_incidencias($incidencia,[1]);
        $msg_bad_incidencia[] = $incidencia;

        $equipo = $incidencia["equipo"];
        $correo_supervisor = $incidencia["supervisor_correo"];
        $correo_tienda = $incidencia["tienda_email"];
        $correo_jefe_zona = $incidencia["jefe_zona_correo"];
        unset($incidencia["equipo"]);
        unset($incidencia["tienda_email"]);
        unset($incidencia["tienda_email"]);
        unset($incidencia["jefe_zona_correo"]);
        $body = "<table>";
        $body .= "<tbody>";
        foreach ($incidencia as $key => $value) {
            $body .= "<tr><td><b>" . $key . " :</b></td><td>" . $value . "</td><tr>";
        }
        if ($recomendacion == "Visita Técnica") {
            $body .= "<tr><td><b>Equipo a revisar :</b></td><td> " . $equipo . "</td></tr>";
            //$body .= $periferico != "" ? "<tr><td><b>Periférico : </b></td><td>" .$periferico ."</td></tr>" : "";
            $body .= "<tr><td><b>Nota para el técnico : </b></td><td>" . $nota_tecnico . "</td></tr>";
        } //-------------------------------
        elseif ($recomendacion == "Seguimiento Soporte" && $nota_soporte != "") {
            $body .= "<tr><td><b>Nota: </b></td><td>" . $nota_soporte . "</td></tr>";
        }
        //--------------------------------
        $body .= "</tbody>";
        $body .= "</table>";

        $cc = [];

        if ($correo_supervisor != "" && $recomendacion != "") {
            if (preg_match('/^[_ña-z0-9-]+(\.[_ña-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/', trim($correo_supervisor)) != 0) {//correo valid
                $cc[] = $correo_supervisor;
            }
        }
        if ($correo_jefe_zona != "" && $recomendacion == "Seguimiento Soporte") {
            if (preg_match('/^[_ña-z0-9-]+(\.[_ña-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/', trim($correo_jefe_zona)) != 0) {//correo valid
                $cc[] = $correo_jefe_zona;
            }
        }
        $bcc = [
        ];
        switch ($recomendacion) {
            case "Visita Técnica":
                if($local_red_id == 1){
                    // $cc[] = "victor.alayo@testtest.apuestatotal.com";
                    $cc[] = "jhossep.zamora@testtest.apuestatotal.com";
                    $cc[] = "jose.jimenez@testtest.apuestatotal.com";
                    $cc[] = "jose.rumay@testtest.apuestatotal.com";
                    // $cc[] = "antonio.cancino@testtest.kurax.dev";
                }else if($local_red_id == 16){
                    $cc[] = "pilar.martinez@testtest.igamingh.com";
                    $cc[] = "jose.jimenez@testtest.apuestatotal.com";
                    $cc[] = "jhossep.zamora@testtest.apuestatotal.com";
                    // $cc[] = "antonio.cancino@testtest.kurax.dev";
                }
                break;
            case "Proveedor de Internet":
            case "Capacitación":
                $cc[] = "capacitacion@testtest.apuestatotal.com";
                break;
            case "Seguimiento Soporte":
                $cc[] = "soporte@testtest.apuestatotal.com";
                if ($correo_tienda != "") {
                    $cc[] = $correo_tienda;
                }
                $bcc[] = "gorqui.chavez@testtest.kurax.dev";
                $bcc[] = "neil.flores@testtest.kurax.dev";
                break;
            case "RR.HH.";
                if($local_red_id == 1 || $local_red_id == 9){
                    $cc[] = "katia.yactayo@testtest.apuestatotal.com";
                    $cc[] = "stephanie.rodriguez@testtest.apuestatotal.com";
                    // $cc[] = "epoloreyes@testtest.hotmail.com";
                    // $cc[] = "antonio.cancino@testtest.kurax.dev";
                }else if($local_red_id == 16){
                    $cc[] = "daniel.rebaza@testtest.igamingh.pe";
                    // $cc[] = "erika.polo@testtest.apuestatotal.com";
                    // $cc[] = "antonio.cancino@testtest.kurax.dev";
                }
                break;
        }

        $mail = [
            "subject" => "Recomendación de Soporte | ID incidencia: " . $incidencia_id . " | " . $recomendacion . " | " . $incidencia["Tienda"],
            "body" => $body,
            "cc" => $cc,
            "bcc" => $bcc,
        ];

        $msg_bad_incidencia[] = $mail;

        $mail['Host'] = env('MAIL_GESTION_NET_HOST');
        $mail['Username'] = env('MAIL_GESTION_NET_USER');
        $mail['Password'] = env('MAIL_GESTION_NET_PASS');
        $mail['From'] = 'gestion@testtest.apuestatotal.com';
        $mail['FromName'] = env('MAIL_GESTION_NET_NAME');

        if (count($cc) == 0) {
            $return["mensaje"] = "Incidencia " . $incidencia_id . " Cerrada";
        } else {
            //log_incidencias("Enviando Mail...",[1]);
            $msg_bad_incidencia[] = "Enviando Mail...";
            ob_start();
            send_email_v6($mail);
            $msg = ob_get_contents();
            ob_end_clean();
            $return["mensaje"] = "Incidencia " . $incidencia_id . ": Atendida";
            if ($msg != "") {
                $return["mensaje"] .= "\n" . $msg;
                foreach ($msg_bad_incidencia as $msg_value) {
                    log_incidencias($msg_value, [2]);
                }
                log_incidencias("Error al enviar mail : ", [2]);
                log_incidencias($msg, [2]);
            } else {
                foreach ($msg_bad_incidencia as $msg_value) {
                    log_incidencias($msg_value, [1]);
                }
                log_incidencias("Mail Enviado", [1]);
            }
            if ($correo_supervisor == "") {
                $return["mensaje"] .= "\nCorreo de Supervisor no definido";
            } else {
                if (preg_match('/^[_ña-z0-9-]+(\.[_ña-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/', trim($correo_supervisor)) == 0) {//correo invalid
                    $return["mensaje"] .= "\n'" . $correo_supervisor . "' no es un Correo válido";
                }
            }

            $return["cc"] = $cc;
            $return["curr_login"] = $login;
            $return["correos"] = $mail;
            $return["recomendacion"] = $recomendacion;

        }
    }
    echo json_encode($return);
    exit();
}

if (isset($_POST["sec_incidencias_notas_save"])) {
    $data = $_POST["sec_incidencias_notas_save"];

    if ($_POST["nota_txt"] == "") {
        $return["error"] = "nota_txt";
        $return["error_msg"] = "Debe ingresar Nota";
        $return["error_focus"] = "nota_txt";

    } else {
        $path = "/var/www/html/files_bucket/incidencia_notas/";
        if (!is_dir($path)) mkdir($path, 0777, true);

        $nombre_archivo = "";
        if ($_FILES['nota_imagen']['name'] != "") {
            $_file = $_FILES['nota_imagen'];
            $filename = $_file['tmp_name'];
            $size = $_file['size'];
            $fileExt = pathinfo($_file['name'], PATHINFO_EXTENSION);
            $nombre_archivo = date('YmdHis') . "." . $fileExt;

            $sourceProperties = getimagesize($filename);
            $uploadImageType = $sourceProperties[2];
            $sourceImageWith = $sourceProperties[0];
            $sourceImageHeight = $sourceProperties[1];

            $file = [];
            $imageLayer = [];
            switch ($uploadImageType) {
                case IMAGETYPE_JPEG:
                    $resourceType = imagecreatefromjpeg($filename);
                    $imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);

                    $file[0] = imagejpeg($imageLayer[0], $path . $nombre_archivo);
                    break;
                case IMAGETYPE_PNG:
                    $resourceType = imagecreatefrompng($filename);
                    $imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);

                    $file[0] = imagepng($imageLayer[0], $path . $nombre_archivo);
                    break;
                case IMAGETYPE_GIF:
                    $resourceType = imagecreatefromgif($filename);
                    $imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);
                    $file[0] = imagegif($imageLayer[0], $path . $nombre_archivo);
                    break;
            }
            move_uploaded_file($file[0], $path . $nombre_archivo);
        }

        $nota_txt = $mysqli->real_escape_string($_POST["nota_txt"]);
        $insert_command = "
		INSERT INTO tbl_soporte_notas (created_at,user_id,nota_txt,imagen,estado)
		VALUES (now()," . $login["id"] . ",'" . $nota_txt . "','" . $nombre_archivo . "',1)
		";

        $mysqli->query($insert_command);
        if ($mysqli->error) {
            print_r($mysqli->error);
            echo "\n";
            echo $insert_command;
            exit();
        }
        $return["id"] = $mysqli->insert_id;
        $return["curr_login"] = $login;
        $return["mensaje"] = "Nota Registrada";
    }
}

if (isset($_POST["sec_incidencias_notas_update"])) {
    $user_id = $login["id"];
    $data = $_POST["sec_incidencias_notas_update"];
    $nota_txt = $_POST["nota_txt"];
    $nota_id = $_POST["nota_id"];

    if ($nota_txt == "") {
        $return["error"] = "nota_txt";
        $return["error_msg"] = "Debe ingresar Nota";
        $return["error_focus"] = "nota_txt";
    } else {
        $imagen_update = "";
        if ($_FILES['nota_imagen']['name'] != "") {
            $imagen_actual = $_POST["imagen_actual"];
            $path = "/var/www/html/files_bucket/incidencia_notas/";
            @unlink($path . $imagen_actual);
            $_file = $_FILES['nota_imagen'];
            $filename = $_file['tmp_name'];
            $size = $_file['size'];
            $fileExt = pathinfo($_file['name'], PATHINFO_EXTENSION);
            $nombre_archivo = date('YmdHis') . "." . $fileExt;

            $sourceProperties = getimagesize($filename);
            $uploadImageType = $sourceProperties[2];
            $sourceImageWith = $sourceProperties[0];
            $sourceImageHeight = $sourceProperties[1];

            $file = [];
            $imageLayer = [];
            switch ($uploadImageType) {
                case IMAGETYPE_JPEG:
                    $resourceType = imagecreatefromjpeg($filename);
                    $imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);

                    $file[0] = imagejpeg($imageLayer[0], $path . $nombre_archivo);
                    break;
                case IMAGETYPE_PNG:
                    $resourceType = imagecreatefrompng($filename);
                    $imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);

                    $file[0] = imagepng($imageLayer[0], $path . $nombre_archivo);
                    break;
                case IMAGETYPE_GIF:
                    $resourceType = imagecreatefromgif($filename);
                    $imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);
                    $file[0] = imagegif($imageLayer[0], $path . $nombre_archivo);
                    break;
            }
            move_uploaded_file($file[0], $path . $nombre_archivo);
            $imagen_update = ",imagen = '" . $nombre_archivo . "'";
        }

        $nota_txt = $mysqli->real_escape_string($_POST["nota_txt"]);
        $insert_command = "
			UPDATE tbl_soporte_notas set 
				updated_at=now()
				,update_user_id= $user_id
				,nota_txt=  '$nota_txt'
				$imagen_update
			where id=" . $nota_id;

        $mysqli->query($insert_command);
        if ($mysqli->error) {
            print_r($mysqli->error);
            echo "\n";
            echo $insert_command;
            exit();
        }
        $return["mensaje"] = "Nota " . $nota_id . ": Actualizada";
        $return["curr_login"] = $login;
    }
}

if (isset($_POST["sec_incidencias_list"])) {
    if ($login == false) {
        $response = array(
            "login" => $login
        );
        echo json_encode($response);
        die();
    }
    $TABLA = "tbl_soporte_incidencias";
    $ID_LOGIN = $login["id"];
    $data = $_POST["sec_incidencias_list"];
    $draw = $_POST['draw'];
    $row = $_POST['start'];
    $rowperpage = $_POST['length']; // Rows display per page
    $columnIndex = $_POST['order'][0]['column']; // Column index
    $columnName = $_POST['columns'][$columnIndex]['data']; // Column name
    $columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
    $searchValue = $_POST['search']['value']; // Search value
    $searchValue = $mysqli->real_escape_string($searchValue);

    $red_id = $_POST['red_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $red_query = $red_id == "-1" ? "" : " AND loc.red_id = '$red_id' ";

    ## Search
    $searchQuery = '';

    if ($searchValue != '') {
        $searchQuery = " and (inci.id like '%" . $searchValue . "%' or 
	        loc.nombre like '%" . $searchValue . "%' or
	        inci.created_at like '" . $searchValue . "%' or
	        loc.phone like '%" . $searchValue . "%' or
	        inci.telefono2 like '%" . $searchValue . "%' or
	        inci.incidencia_txt like '%" . $searchValue . "%' or
	        inci.satisfaccion like '%" . $searchValue . "%' or
	        inci.solucion_txt like '%" . $searchValue . "%' or
	        st.solucion_txt like '%" . $searchValue . "%' or
	        usu.usuario like'%" . $searchValue . "%' or
	        ag1.usuario like'%" . $searchValue . "%' or
	        ag2.usuario like'%" . $searchValue . "%' or
	        inci.fecha_asignada like'%" . $searchValue . "%' or
	        inci.fecha_solucion like'%" . $searchValue . "%' or
	        inci.producto like '%" . $searchValue . "%' or
	        inci.tipo like '%" . $searchValue . "%' or
	        inci.teamviewer_id LIKE '%" . $searchValue . "%' or
	        inci.recomendacion LIKE '%" . $searchValue . "%' or
	        tlr.nombre LIKE '%" . $searchValue . "%'
	       ) ";
    }
    $sel = $mysqli->query("SELECT count(*) AS allcount FROM $TABLA");
    $records = $sel->fetch_assoc();
    $totalRecords = $records['allcount'];

    $SELECT = "SELECT inci.id
			,loc.nombre as local
			,inci.created_at
			,inci.user_id
			,usu_area.id AS 'usuario_area'
			,usu_cargo.id AS 'usuario_cargo'
			,inci.local_id
			,loc.phone
			,inci.telefono2
			,inci.incidencia_txt
			,inci.estado
			,CASE
                WHEN inci.satisfaccion = 0 THEN 'Nada Satisfecho'
                WHEN inci.satisfaccion = 1 THEN 'Poco Satisfecho'
                WHEN inci.satisfaccion = 2 THEN 'Neutral'
                WHEN inci.satisfaccion = 3 THEN 'Muy Satisfecho'
                WHEN inci.satisfaccion = 4 THEN 'Totalmente Satisfecho'
                END AS satisfaccion
			,CASE 
			WHEN inci.estado=0 THEN 'Nuevo' 
            WHEN inci.estado=2 THEN 'Asignado'
            ELSE 'Atendido'       
            END AS EstadoCol
            ,IFNULL(st.estado_vt, '-') as estado_servicio_tecnico
            ,IFNULL(st.solucion_txt, inci.solucion_txt) as solucion_txt
			,usu.usuario
            ,if(inci.agente_1_id > 0,1,0) as assigned
			,IFNULL(ag1.usuario, usu_upd.usuario) as agente
            ,ag2.usuario as agente2
			,inci.agente_1_id as agente_id
            ,inci.agente_2_id as agente2_id
            ,inci.agente_reasignado_id
            ,usu_agr.usuario as agente_reasignado
            ,inci.fecha_asignada
            ,IFNULL(inci.fecha_solucion, st.created_at) as fecha_solucion
			,inci.producto
			,inci.tipo
			,IF(inci.reimpresion = 1, 'Si', 'No') as reimpresion
			,inci.teamviewer_id
			,inci.teamviewer_password
			,inci.recomendacion
            ,'{$login["id"]}' AS login_id
			,(SELECT 
            count(u.id) AS puede_reabrir
			FROM tbl_usuarios u
			LEFT JOIN tbl_personal_apt p ON p.id = u.personal_id
			WHERE p.area_id = 9
			AND u.estado  = 1
			AND p.cargo_id IN (4,19) 
			AND u.id = '{$login["id"]}' LIMIT 1) AS puede_reabrir
            ,tlr.nombre AS red,
            inci.tipo_incidencia, inci.detalle_incidencia
			FROM tbl_soporte_incidencias inci 
            LEFT join tbl_locales loc on  inci.local_id=loc.id
			LEFT join tbl_locales_redes tlr on loc.red_id = tlr.id
            LEFT join tbl_usuarios usu on usu.id= inci.user_id
			LEFT JOIN tbl_personal_apt usu_pers on usu_pers.id = usu.personal_id
            LEFT JOIN tbl_areas usu_area on usu_area.id = usu_pers.area_id
            LEFT JOIN tbl_cargos usu_cargo on usu_cargo.id = usu_pers.cargo_id
            left join tbl_usuarios ag1 on ag1.id= inci.agente_1_id
	        left join tbl_usuarios ag2 on ag2.id= inci.agente_2_id
            left join tbl_usuarios usu_upd on usu_upd.id= inci.update_user_id
            left join tbl_usuarios usu_agr on usu_agr.id= inci.agente_reasignado_id
            left join tbl_servicio_tecnico st on st.soporte_incidencias_id = inci.id
			";

    $estados = $_POST['columns'][12]['search']['value'];

    $searchEstadoAsignadoQuery = '';
    $searchEstadoReasignadoQuery = '';
    $searchEstadoNuevoAtendidoQuery = '';
    if ($estados !== '' && $estados !== 'null') {
        $estados = explode(',', $estados);

        if (in_array('2', $estados) && in_array('3', $estados)) {
            $searchEstadoAsignadoQuery = "(inci.estado in ('2') and ((inci.agente_reasignado_id IS NOT NULL AND CASE WHEN inci.agente_reasignado_id  = " . $login["id"] . " THEN 1 ELSE 0 END) OR (inci.agente_reasignado_id IS NULL AND CASE WHEN inci.agente_1_id = " . $login["id"] . " THEN 1 ELSE 0 END))) ";
            $searchEstadoReasignadoQuery = "(inci.estado in ('2') and ((inci.agente_reasignado_id IS NOT NULL AND CASE WHEN inci.agente_reasignado_id  <> " . $login["id"] . " THEN 1 ELSE 0 END) OR (inci.agente_reasignado_id IS NULL AND CASE WHEN inci.agente_1_id <> " . $login["id"] . " THEN 1 ELSE 0 END))) ";
            if (($index = array_search('3', $estados)) !== false) {
                unset($estados[$index]);
            }
            if (($index = array_search('2', $estados)) !== false) {
                unset($estados[$index]);
            }
        } else if (in_array('2', $estados)) {
            $searchEstadoAsignadoQuery = "(inci.estado in ('2') and ((inci.agente_reasignado_id IS NOT NULL AND CASE WHEN inci.agente_reasignado_id  = " . $login["id"] . " THEN 1 ELSE 0 END) OR (inci.agente_reasignado_id IS NULL AND CASE WHEN inci.agente_1_id = " . $login["id"] . " THEN 1 ELSE 0 END))) ";
            if (($index = array_search('2', $estados)) !== false) {
                unset($estados[$index]);
            }
        } else if (in_array('3', $estados)) {
            $searchEstadoReasignadoQuery = "(inci.estado in ('2') and ((inci.agente_reasignado_id IS NOT NULL AND CASE WHEN inci.agente_reasignado_id  <> " . $login["id"] . " THEN 1 ELSE 0 END) OR (inci.agente_reasignado_id IS NULL AND CASE WHEN inci.agente_1_id <> " . $login["id"] . " THEN 1 ELSE 0 END))) ";
            if (($index = array_search('3', $estados)) !== false) {
                unset($estados[$index]);
            }
        }

        if (in_array('0', $estados) || in_array('1', $estados)) {
            $estados = implode(',', $estados);
            $searchEstadoNuevoAtendidoQuery = "(inci.estado in (" . $estados . "))";
        }
    }

    $searchQuery .= " AND inci.created_at >= '$start_date' AND inci.created_at < DATE_ADD('$end_date', INTERVAL 1 DAY) ";

    $searchEstadoQueries = [];

    if ($searchEstadoAsignadoQuery != null) {
        $searchEstadoQueries[] = $searchEstadoAsignadoQuery;
    }

    if ($searchEstadoReasignadoQuery != null) {
        $searchEstadoQueries[] = $searchEstadoReasignadoQuery;
    }

    if ($searchEstadoNuevoAtendidoQuery != null) {
        $searchEstadoQueries[] = $searchEstadoNuevoAtendidoQuery;
    }

    if (count($searchEstadoQueries) > 0) {
        $searchQuery .= ' and (' . implode(' OR ', $searchEstadoQueries) . ') ';
    }

    if($login["usuario_locales"]) {
        $searchQuery .= " AND loc.id IN (".implode(",", $login["usuario_locales"]).") ";
    }

    $query = $SELECT . " WHERE 1 " . $searchQuery . $red_query . " GROUP BY inci.id ";

    $total_query = "SELECT count(*) AS allcount FROM (" . $query . ") AS subquery";
    $sel = $mysqli->query($total_query);
    $records = $sel->fetch_assoc();
    $totalRecordwithFilter = $records['allcount'];

    $limit = " limit " . $row . "," . $rowperpage;
    if ($rowperpage == -1) {
        $limit = "";
    }

    $order_by = "order by x." . $columnName . " " . $columnSortOrder . $limit;

    $new_query = "SELECT id
     ,local
     ,DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') as created_at
     ,user_id
     ,usuario_area
     ,usuario_cargo
     ,local_id
     ,phone
     ,telefono2
     ,incidencia_txt
     ,estado
     ,satisfaccion
     ,EstadoCol
     ,estado_servicio_tecnico
     ,solucion_txt
     ,usuario
     ,assigned
     ,agente
     ,agente2
     ,agente_id
     ,agente2_id
     ,agente_reasignado_id
     ,agente_reasignado
     ,DATE_FORMAT(fecha_asignada, '%d-%m-%Y %H:%i:%s') as fecha_asignada
     ,DATE_FORMAT(fecha_solucion, '%d-%m-%Y %H:%i:%s') as fecha_solucion
     ,producto
     ,tipo
     ,reimpresion
     ,teamviewer_id
     ,teamviewer_password
     ,recomendacion
     ,login_id
     ,puede_reabrir
     ,red
     ,tipo_incidencia, detalle_incidencia
     FROM (" . $query . ") AS x " . $order_by;

    $empRecords = $mysqli->query($new_query);
    $data = array();

    if ($mysqli->error) {
        print_r($mysqli->error);
        exit;
    }

    while ($row = $empRecords->fetch_assoc()) {
        $data[] = $row;
    }

    $response = array(
        "draw" => $draw,
        "iTotalRecords" => $totalRecords,
        "iTotalDisplayRecords" => $totalRecordwithFilter,
        "aaData" => $data,
        "login_id" => $login["id"]
    );

    echo json_encode($response);
    return;
}

if (isset($_POST["sec_incidencias_csv"])) {
    $data = $_POST["sec_incidencias_csv"];
    $start_date = $data["start_date"] ?? date("Y-m-d");
    $end_date = $data["end_date"] ?? date("Y-m-d");
    $login_id = $login["id"];

    $result_data = get_reporting_data($start_date, $end_date, $login['id']);
    if (count($result_data) > 0) {
   // CSV FILE
        $csv_table = array();
        $csv_table_columns = get_reporting_columns();

        $csv_table[] = $csv_table_columns;

        foreach ($result_data as $data_row) {
            $csv_table_row = array(
                "id" => $data_row["id"],
                "created_at" => $data_row["created_at"],
                "usuario" => $data_row["usuario"],
                "local" => $data_row["local"],
                "phone" => $data_row["phone"],
                "telefono2" => $data_row["telefono2"],
                "producto" => $data_row["producto"],
                "tipo" => $data_row["tipo"],
                "reimpresion" => $data_row["reimpresion"],
                "teamviewer_id" => $data_row["teamviewer_id"],
                "teamviewer_password" => $data_row["teamviewer_password"],
                "incidencia_txt" => $data_row["incidencia_txt"],
                "estadocol" => $data_row["estadocol"],
                "estado_servicio_tecnico" => $data_row["estado_servicio_tecnico"],
                "fecha_asignada" => $data_row["fecha_asignada"],
                "agente" => $data_row["agente"],
                "agente2" => $data_row["agente2"],
                "fecha_solucion" => $data_row["fecha_solucion"],
                "recomendacion" => $data_row["recomendacion"],
                "solucion_txt" => $data_row["solucion_txt"],
                "satisfaccion" => $data_row["satisfaccion"],
                "equipo" => $data_row["equipo"],
                "nota_tecnico" => $data_row["nota_tecnico"],
                "zona_comercial" => $data_row["zona_comercial"],
                "red" => $data_row["red"],
                "tipo_incidencia" => $data_row["tipo_incidencia"],
                "detalle_incidencia" => $data_row['detalle_incidencia']
            );
            $csv_table[] = $csv_table_row;
        }


        $exported_path = '/export/files_exported/incidencias';

        //$exported_path = '/export/servicio_tecnico_reporte';

        if (!file_exists('..' . $exported_path)) {
            mkdir('..' . $exported_path, 0777, true);
        }

        ob_clean();
        ob_start();
        $filename = "reporte_incidencias_{$start_date}_{$end_date}_" . date("YmdHis") . ".csv";
        header('Content-type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        $fp = fopen('..' . $exported_path . '/' . $filename, 'w');
        fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
        foreach ($csv_table as $fields) {
            fputcsv($fp, $fields);
        }
        fclose($fp);

        $response = array(
            "path" => $exported_path . '/' . $filename
        );

        echo json_encode($response);
        return;
    } else {
        $response = array(
            "error" => true,
            "error_msg" => "No hay registros en estas fechas"
        );
        echo json_encode($response);
        return;
    }
}

if (isset($_POST['sec_incidencias_xls'])) {
    $sec_incidencias_xls = $_POST["sec_incidencias_xls"];
    $start_date = $sec_incidencias_xls["start_date"] ?? date("Y-m-d");
    $end_date = $sec_incidencias_xls["end_date"] ?? date("Y-m-d");
    $letters = range('A', 'Z');
    $title = 'Reporte de Incidencias';
    $extension = ".xls";
    $file_name = "reporte_incidencias_{$start_date}_{$end_date}_" . date("YmdHis") . $extension;
    $objPHPExcel = new PHPExcel();
    $objPHPExcel->getProperties()->setCreator('ApuestaTotal')
        ->setDescription($title);
    $objPHPExcel->setActiveSheetIndex();
    $activeSheet = $objPHPExcel->getActiveSheet();
    $xls_table_column_names = get_reporting_columns();
    $result_data = get_reporting_data($start_date, $end_date, $login['id']);
    $base_letter = '';
    $index_base_letter = 0;
    $letter_index = 0;
    foreach ($xls_table_column_names as $column_title) {
        $letter = $letters[$letter_index];
        $xls_column = $base_letter . $letter;
        $activeSheet->setCellValue($xls_column . '1', $column_title);
        $letter_index++;
        if ($letter_index >= count($letters)) {
            $base_letter .= $letters[$index_base_letter];
            $index_base_letter++;
            $letter_index = 0;
        }
    }

    $base_letter = '';
    $index_base_letter = 0;
    $letter_index = 0;
    $index_row = 2;
    foreach ($result_data as $data_row) {
        foreach ($xls_table_column_names as $column_key => $column_title) {
            $column_value = $data_row[$column_key];
            $letter = $letters[$letter_index];
            $letter_index++;
            $xls_column = $base_letter . $letter;
            $activeSheet->setCellValue($xls_column . $index_row, $column_value);
            if ($letter_index >= count($letters)) {
                $base_letter .= $letters[$index_base_letter];
                $index_base_letter++;
                $letter_index = 0;
            }
        }

        $index_row++;
        $base_letter = '';
        $index_base_letter = 0;
        $letter_index = 0;
    }

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename=' . $file_name);
    header('Cache-Control: max-age=0');

    PHPExcel_Calculation::getInstance($objPHPExcel)->disableCalculationCache();
    PHPExcel_Calculation::getInstance($objPHPExcel)->clearCalculationCache();
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');

    $base_path = '/export/files_exported/incidencias';

    //$base_path = '/export/servicio_tecnico_reporte';

    $exported_path = '/var/www/html' . $base_path;

    $file_path = $exported_path . '/' . $file_name;

    ob_start();
    $objWriter->save($file_path);
    ob_end_clean();

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename=' . $file_name);
    header('Cache-Control: max-age=0');
    $objPHPExcel->disconnectWorksheets();
    unset($objWriter, $objPHPExcel);
    $response = array(
        'path' => $base_path . '/' . $file_name
    );

    echo json_encode($response);
    exit;
}

if (isset($_POST["set_incidencias_satisfaccion"])) {
    global $mysqli;
    $data = $_POST["set_incidencias_satisfaccion"];
    $query = "UPDATE tbl_soporte_incidencias SET satisfaccion = '$data[value]' WHERE id = '$data[incidencia_id]'";
    $mysqli->query($query);
    $return = true;
    if ($mysqli->error) {
        $return = false;
    }

    echo json_encode($return);
    return;
}

if (isset($_POST["set_incidencias_agente_asignado"])) {
    $incidencia_id = $_POST["set_incidencias_agente_asignado"];
    $return = get_agente_asignado($incidencia_id);
    $return['login_id'] = $login['id'];
    echo json_encode($return);
    die();
}

if (isset($_POST["set_incidencias_check_permiso_reabrir"])) {
    //$result = check_permiso_reabrir($login['id']);
    $puede_abrir = false;
    if ($login['area_id'] == 9 && ($login['cargo_id'] == 19 || $login['cargo_id'] == 4)) {
        $puede_abrir = true;
    }
    echo json_encode([
        'puede_reabrir' => $puede_abrir
    ]);
    die();
}

if (isset($_POST["set_incidencias_reabrir"])) {
    $incidencia_id = $_POST["set_incidencias_reabrir"];
    $return = [
        'error' => false,
        'swal_type' => 'error',
        'message' => '',
        'title' => ''
    ];

    if ($login['area_id'] == 9 && ($login['cargo_id'] == 19 || $login['cargo_id'] == 4)) {

        $command = "SELECT 
    inci.id,
    -- inci.user_id,
    inci.local_id,
    inci.incidencia_txt,
    inci.estado,
    inci.satisfaccion,
    -- inci.solucion_txt,
    -- inci.update_user_id,
    inci.agente_1_id,
    inci.agente_2_id,
    inci.agente_reasignado_id,
    DATE_FORMAT(inci.fecha_asignada, '%d-%m-%Y %H:%i:%s') as fecha_asignada,
    -- DATE_FORMAT(inci.fecha_solucion, '%d-%m-%Y %H:%i:%s') as fecha_solucion,
    -- inci.update_user_at,
    -- inci.created_at,
    -- inci.updated_at,
    inci.producto,
    inci.tipo,
    inci.equipo,
    inci.nota_tecnico,
    inci.recomendacion,
    inci.teamviewer_id,
    inci.teamviewer_password,
    loc.phone,
    inci.telefono2,
    inci.estado_vt,
    DATE_FORMAT(inci.fecha_cierre_vt, '%d-%m-%Y %H:%i:%s') as fecha_cierre_vt,
    inci.foto_terminado_vt,
    inci.comentario_vt,
    inci.periferico,
    inci.reimpresion,
    inci.nota_soporte,
    inci.motivo_observado_id,
    inci.estado_servicio_tecnico_id,
    inci.equipo_id,
    inci.nota_extra_observado_vt
    FROM tbl_soporte_incidencias inci 
    LEFT JOIN tbl_locales loc on  inci.local_id=loc.id
    WHERE inci.id='{$incidencia_id}' LIMIT 1";
        $list_query = $mysqli->query($command);
        if ($mysqli->error) {
            $return['message'] = $mysqli->error;
            $return['error'] = true;
            $return['title'] = 'Error de  Base de Datos.';
        } else {
            $list = array();
            while ($li = $list_query->fetch_assoc()) {
                $list[] = $li;
            }

            $res_query = $list[0];

            if ($res_query['estado'] != 1) {
                $return['error'] = true;
                $return['swal_type'] = 'warning';
                $return['message'] = 'El caso no esta atendido.';
            } else {
                //$query = "UPDATE tbl_soporte_incidencias SET estado = 2 WHERE id = '{$incidencia_id}'";
                $query = "INSERT INTO tbl_soporte_incidencias (
                                user_id,
                                local_id,
                                incidencia_txt,
                                estado,
                                satisfaccion,
                                solucion_txt,
                                update_user_id,
                                agente_1_id,
                                agente_2_id,
                                agente_reasignado_id,
                                fecha_asignada,
                                fecha_solucion,
                                update_user_at,
                                created_at,
                                updated_at,
                                producto,
                                tipo,
                                equipo,
                                nota_tecnico,
                                recomendacion,
                                teamviewer_id,
                                teamviewer_password,
                                telefono2,
                                estado_vt,
                                fecha_cierre_vt,
                                foto_terminado_vt,
                                comentario_vt,
                                periferico,
                                reimpresion,
                                nota_soporte,
                                motivo_observado_id,
                                estado_servicio_tecnico_id,
                                equipo_id,
                                nota_extra_observado_vt
                        ) VALUES (
                                {$login['id']},
                                {$res_query['local_id']},
                                '{$res_query['incidencia_txt']}',
                                0,
                                NULL,
                                NULL,
                                {$login['id']},
                                NULL,
                                NULL,
                                NULL,
                                now(),
                                NULL,
                                now(),
                                now(),
                                now(),
                                '{$res_query['producto']}',
                                '{$res_query['tipo']}',
                                NULL,
                                NULL,
                                NULL,
                                NULL,
                                NULL,
                                '{$res_query['telefono2']}',
                                NULL,
                                NULL,
                                NULL,
                                NULL,
                                NULL,
                                NULL,
                                NULL,
                                NULL,
                                NULL,
                                NULL,
                                NULL
                        )";
                $mysqli->query($query);
                $last_id = $mysqli->insert_id;
                if ($mysqli->error) {
                    $return['message'] = $mysqli->error;
                    $return['error'] = true;
                    $return['title'] = 'Error de  Base de Datos.';
                } else {
                    $return['message'] = 'El caso sido reabierto, se ha creado una nueva incidencia con código ' . $last_id;
                }
            }
        }
    } else {
        $return['error'] = true;
        $return['title'] = 'No Autorizado';
        $return['swal_type'] = 'warning';
        $returm['message'] = 'Usted no esta autorizado para reabrir el caso.';
    }

    echo json_encode($return);
    die();
}

if (isset($_POST['set_incidencias_get_agentes'])) {
    $result = get_agentes();
    echo json_encode($result);
    die();
}

if (isset($_POST['set_incidencias_reasignar_seleccionado'])) {
    $incidencia_id = $_POST['incidencia_id'];
    $usuario_id = $_POST['usuario_id'];

    $command = "SELECT 
                    COUNT(*) AS incidencias_asignadas  
                FROM 
                    tbl_soporte_incidencias
                WHERE 
                    CASE
                        WHEN agente_reasignado_id is null THEN agente_1_id = '" . $usuario_id . "'
                        ELSE agente_reasignado_id = '" . $usuario_id . "'
                    END = 1
                AND 
                    estado = 2";

    $res_query = $mysqli->query($command)->fetch_assoc();
    if ($mysqli->error) {
        print_r($mysqli->error);
    }

    if ($res_query["incidencias_asignadas"] >= 5) {
        $return["error"] = "Tiene 5 incidencias asignadas";
        $return["msg"] = "Tiene 5 incidencias asignadas";
        $return["swal_type"] = "error";
        $return["swal_timeout"] = 3000;
        $return["login_id"] = $login["id"];
        echo json_encode($return);
        die();
    }

    $return = [
        'error' => false,
        'msg' => 'El caso fue reasignado con éxito',
        'swal_type' => 'error'
    ];

    if ($login['area_id'] == 9 && ($login['cargo_id'] == 19 || $login['cargo_id'] == 4)) {
        $command = "SELECT inci.estado FROM tbl_soporte_incidencias inci where inci.id=" . $incidencia_id . " limit 1";
        $list_query = $mysqli->query($command);
        if ($mysqli->error) {
            $return['msg'] = $mysqli->error;
            $return['error'] = true;
        } else {
            $list = array();
            while ($li = $list_query->fetch_assoc()) {
                $list[] = $li;
            }

            if (count($list) > 0) {
                $res_query = $list[0];
                if ($res_query["estado"] != 2) {
                    $return['error'] = true;
                    $return['swal_type'] = 'warning';
                    $return['msg'] = 'El caso aún no está asignado';
                } else {
                    $update_command = "
			UPDATE tbl_soporte_incidencias set
				updated_at=now()
			    ,fecha_asignada = now()
				, update_user_id = '{$login['id']}'
				, agente_reasignado_id = '{$usuario_id}' 
				where id='{$incidencia_id}'";
                    $mysqli->query($update_command);
                    if ($mysqli->error) {
                        $return['msg'] = $mysqli->error;
                        $return['error'] = true;
                    } else {
                        $return["swal_type"] = 'success';
                    }
                }
            } else {
                $return['msg'] = 'No existe el caso con el código de incidencia ' . $incidencia_id;
                $return['error'] = true;
            }
        }
    } else {
        $return['msg'] = 'Solo los coordinadores y supervisores del área de soporte pueden resignar a un agente seleccionado.';
        $return['error'] = true;
    }

    echo json_encode($return);
    die();
}

if (isset($_POST['get_obtener_incidencia_por_id'])) {
    try {
        $incidencia_id = $_POST['id'];
        $result_incidencia = get_detail_incidencia_by_id($incidencia_id);
        
        if (isset($login) && $login['area_id'] == 9 && ($login['cargo_id'] == 19 || $login['cargo_id'] == 4)) {
            $check_area_cargo = true;
        }else{
            $check_area_cargo = false;
        }
        $result_incidencia[0]['check_area_cargo'] = $check_area_cargo;

        $result['status'] = 200;
        $result['result'] = $result_incidencia;
        $result['message'] = "Datos obtenidos de gestion";
        echo json_encode($result);
        die();
    } catch (\Excepcion $e) {
        $result['status'] = 404;
        $result['message'] = "Ha ocurrido un error";
        echo json_encode($result);
        die();
    }
}

if (isset($_POST['set_incidencias_check_coordinador_supervisor'])) {
    $return = [
        'msg' => 'El usuario logueado es coordinado o supervisor del área de soporte.',
        'swal_type' => 'success',
        'check_result' => true
    ];

    if (!(isset($login) && $login['area_id'] == 9 && ($login['cargo_id'] == 19 || $login['cargo_id'] == 4))) {
        $return['swal_type'] = 'warning';
        $return['check_result'] = false;
        $return['msg'] = 'El usuario logueado no es coordinado ni supervisor del área de soporte.';
    }

    echo json_encode($return);
    die();
}
$return["memory_end"] = memory_get_usage();
$return["time_end"] = microtime(true);
$return["memory_total"] = ($return["memory_end"] - $return["memory_init"]);
$return["time_total"] = ($return["time_end"] - $return["time_init"]);
print_r(json_encode($return));

function get_incidencia_by_id($incidencia_id): array
{
    global $mysqli;
    $command = "SELECT 
	inci.id 
	,loc.nombre as tienda
	,DATE_FORMAT(inci.created_at, '%d-%m-%Y %H:%i:%s') as created_at
	,inci.producto
	,inci.user_id
	,inci.local_id
	,loc.phone
	,inci.telefono2	
	,inci.incidencia_txt
	,inci.estado
	,inci.updated_at
	,inci.update_user_at
	,inci.update_user_id
    ,inci.agente_1_id
    ,inci.agente_2_id
	,IFNULL(st.solucion_txt, inci.solucion_txt) as solucion_txt
	,usu.usuario as usuario
	,usu_agente.usuario as usuario_agente
	,CONCAT(usu_age_pers.nombre,' ',usu_age_pers.apellido_paterno) as nombre_agente
	,inci.producto
	,inci.tipo
    ,IF(inci.reimpresion = 1, 'Si', 'No') as reimpresion
    ,rs.nombre as razon_social
    ,rs.id as razon_social_id
    ,lr.id as local_red_id
    ,lr.nombre as local_red_nombre
	FROM tbl_soporte_incidencias inci 
	left join tbl_locales loc on  inci.local_id=loc.id
	left join tbl_usuarios usu on usu.id= inci.user_id
	left join tbl_usuarios usu_agente on usu_agente.id= inci.agente_1_id
	left join tbl_personal_apt usu_age_pers on usu_age_pers.id= usu_agente.personal_id
	left join tbl_servicio_tecnico st on st.soporte_incidencias_id = inci.id
    left join tbl_zonas z on z.id = loc.zona_id
    left join tbl_razon_social rs on rs.id = z.razon_social_id
    left join tbl_locales_redes lr on lr.id = loc.red_id
	where inci.id=" . $incidencia_id;
    $list_query = $mysqli->query($command);
    if ($mysqli->error) {
        print_r($mysqli->error);
    }
    $list = array();
    while ($li = $list_query->fetch_assoc()) {
        $list[] = $li;
    }

    return $list;
}

function get_detail_incidencia_by_id($incidencia_id): array
{
    global $mysqli;
    $command = "
        SELECT
            inci.id,
            loc.nombre AS local,
            DATE_FORMAT(inci.created_at, '%d-%m-%Y %H:%i:%s') as created_at,
            inci.user_id,
            inci.local_id,
            loc.phone,
            inci.telefono2,
            inci.producto,
            inci.tipo,
            IF(inci.reimpresion = 1, 'Si', 'No') as reimpresion,
            inci.teamviewer_id,
            inci.teamviewer_password,
            inci.incidencia_txt,
            CASE WHEN inci.estado = 0 THEN 'Nuevo'
                 WHEN inci.estado = 2 THEN 'Asignado'
                 ELSE 'Atendido' END AS EstadoCol,
            IFNULL(st.estado_vt, '-') as estado_servicio_tecnico,
            DATE_FORMAT(inci.updated_at, '%d-%m-%Y %H:%i:%s') as updated_at,
            DATE_FORMAT(inci.fecha_asignada, '%d-%m-%Y %H:%i:%s') as fecha_asignada,
            IFNULL(st.solucion_txt, inci.solucion_txt) as solucion_txt,
            usu.usuario,
            ag1.usuario AS agente,
            ag2.usuario AS agente2,
            agre.usuario AS agente_reasignado,
            DATE_FORMAT(IFNULL(inci.fecha_solucion, st.created_at), '%d-%m-%Y %H:%i:%s') as fecha_solucion,
            inci.agente_1_id AS agente_id,
            inci.agente_2_id AS agente2_id,
			inci.recomendacion,
			 CASE
                WHEN inci.satisfaccion = 0 THEN 'Nada Satisfecho'
                WHEN inci.satisfaccion = 1 THEN 'Poco Satisfecho'
                WHEN inci.satisfaccion = 2 THEN 'Neutral'
                WHEN inci.satisfaccion = 3 THEN 'Muy Satisfecho'
                WHEN inci.satisfaccion = 4 THEN 'Totalmente Satisfecho'
                END AS satisfaccion,
			inci.equipo,
			inci.nota_tecnico,
			z.nombre AS zona_comercial,
            inci.tipo as tipo,
            tlr.nombre as red
        FROM
                tbl_soporte_incidencias inci
                LEFT JOIN tbl_locales loc ON inci.local_id = loc.id
                LEFT JOIN tbl_locales_redes tlr ON loc.red_id = tlr.id  
                LEFT JOIN tbl_zonas z ON loc.zona_id = z.id
                LEFT JOIN tbl_usuarios usu ON usu.id = inci.user_id
                LEFT JOIN tbl_usuarios ag1 ON ag1.id = inci.agente_1_id
                LEFT JOIN tbl_usuarios ag2 ON ag2.id = inci.agente_2_id
                LEFT JOIN tbl_usuarios agre ON agre.id = inci.agente_reasignado_id
                LEFT JOIN tbl_servicio_tecnico st ON st.soporte_incidencias_id = inci.id
        WHERE
            inci.id =" . $incidencia_id;

    $list_query = $mysqli->query($command);
    if ($mysqli->error) {
        print_r($mysqli->error);
    }
    $list = array();
    while ($li = $list_query->fetch_assoc()) {
        $list[] = $li;
    }

    return $list;
}

function get_estado_incidencia_by_id($incidencia_id): array
{
    global $mysqli;
    $command = "SELECT 
	inci.estado
	FROM tbl_soporte_incidencias inci 
	where inci.id=" . $incidencia_id;
    $list_query = $mysqli->query($command);
    if ($mysqli->error) {
        print_r($mysqli->error);
    }
    $list = array();
    while ($li = $list_query->fetch_assoc()) {
        $list[] = $li;
    }

    return $list;
}

function get_reporting_columns()
{
    return array(
        "id" => "Id",
        "created_at" => "Fecha y Hora",
        "usuario" => "Usuario",
        "local" => "Tienda",
        "phone" => "Telf. Tienda",
        "telefono2" => "Teléfono 2",
        "producto" => "Producto",
        "tipo" => "Tipo",
        "reimpresion" => "Reimpresion",
        "teamviewer_id" => "Id Teamviewer",
        "teamviewer_password" => "Contraseña Teamviewer",
        "incidencia_txt" => "Incidencia",
        "estadocol" => "Estado",
        "estado_servicio_tecnico" => "Estado Serv. Téc.",
        "fecha_asignada" => "Fecha Asignada",
        "agente" => "Agente",
        "agente2" => "Agente 2",
        "fecha_solucion" => "Fecha de Solución",
        "recomendacion" => "Recomendación",
        "solucion_txt" => "Observación",
        "satisfaccion" => "Satisfacción",
        "equipo" => "Equipo a Revisar",
        "nota_tecnico" => "Nota para el técnico",
        "zona_comercial" => "Zona Comercial",
        "red" => "Red",
        "tipo_incidencia" => "Tipo Inc.",
        "detalle_incidencia" => "Detalle Inc."
    );
}

function get_reporting_data($start_date, $end_date, $usuario_id): array
{
    global $mysqli;
    $query =
        "
        SELECT
            inci.id,
            loc.nombre AS local,
            DATE_FORMAT(inci.created_at, '%d-%m-%Y %H:%i:%s') as created_at,
            inci.user_id,
            inci.local_id,
            loc.phone,
            inci.telefono2,
            inci.producto,
            inci.tipo,
            IF(inci.reimpresion = 1, 'Si', 'No') as reimpresion,
            inci.teamviewer_id,
            inci.teamviewer_password,
            inci.incidencia_txt,
            CASE WHEN inci.estado = 0 THEN 'Nuevo'
                 WHEN inci.estado = 2 THEN 'Asignado'
                 ELSE 'Atendido' END AS estadocol,
            IFNULL(st.estado_vt, '-') as estado_servicio_tecnico,
            DATE_FORMAT(inci.updated_at, '%d-%m-%Y %H:%i:%s') as updated_at,
            DATE_FORMAT(inci.fecha_asignada, '%d-%m-%Y %H:%i:%s') as fecha_asignada,
            IFNULL(st.solucion_txt, inci.solucion_txt) as solucion_txt,
            usu.usuario,
            ag1.usuario AS agente,
            ag2.usuario AS agente2,
            DATE_FORMAT(IFNULL(inci.fecha_solucion, st.created_at), '%d-%m-%Y %H:%i:%s') as fecha_solucion,
            inci.agente_1_id AS agente_id,
            inci.agente_2_id AS agente2_id,
			inci.recomendacion,
			 CASE
                WHEN inci.satisfaccion = 0 THEN 'Nada Satisfecho'
                WHEN inci.satisfaccion = 1 THEN 'Poco Satisfecho'
                WHEN inci.satisfaccion = 2 THEN 'Neutral'
                WHEN inci.satisfaccion = 3 THEN 'Muy Satisfecho'
                WHEN inci.satisfaccion = 4 THEN 'Totalmente Satisfecho'
                END AS satisfaccion,
			inci.equipo,
			inci.nota_tecnico,
			z.nombre AS zona_comercial,
            inci.tipo as tipo,
            tlr.nombre as red,
            inci.tipo_incidencia, inci.detalle_incidencia
        FROM
                tbl_soporte_incidencias inci
                LEFT JOIN tbl_locales loc ON inci.local_id = loc.id
                LEFT JOIN tbl_locales_redes tlr ON loc.red_id = tlr.id  
                LEFT JOIN tbl_zonas z ON loc.zona_id = z.id
                LEFT JOIN tbl_usuarios usu ON usu.id = inci.user_id
                LEFT JOIN tbl_usuarios ag1 ON ag1.id = inci.agente_1_id
                LEFT JOIN tbl_usuarios ag2 ON ag2.id = inci.agente_2_id
                LEFT JOIN tbl_servicio_tecnico st ON st.soporte_incidencias_id = inci.id
        WHERE
            inci.created_at >= '$start_date' AND
            inci.created_at < DATE_ADD('$end_date', INTERVAL 1 DAY)
        ORDER BY
            created_at    
    ";

    $result = $mysqli->query($query);
    $result_data = array();
    while ($r = $result->fetch_assoc()) {
        $result_data[] = $r;
    }
    return $result_data;
}

function get_agente_asignado($incidencia_id)
{
    global $mysqli;
    $command = "SELECT inci.estado, inci.agente_reasignado_id, inci.agente_1_id FROM tbl_soporte_incidencias inci where inci.id=" . $incidencia_id . " limit 1";
    $list_query = $mysqli->query($command);
    if ($mysqli->error) {
        print_r($mysqli->error);
        die();
    }
    $list = array();
    while ($li = $list_query->fetch_assoc()) {
        $list[] = $li;
    }

    return $list[0];
}

function check_es_coordinado_supervisor_de_soporte($usuario_id)
{
    global $mysqli;

    $query = "SELECT 
            count(u.id) AS puede_reabrir
			FROM tbl_usuarios u
			LEFT JOIN tbl_personal_apt p ON p.id = u.personal_id
			WHERE p.area_id = 9
			AND u.estado  = 1
			AND p.cargo_id IN (4,19) -- Supervisores y Coordinadores
			AND u.id = '{$usuario_id}'
			LIMIT 1";

    $result = $mysqli->query($query);
    $result_data = array();
    while ($r = $result->fetch_assoc()) {
        $result_data[] = $r;
    }

    if (count($result_data) > 0 && intval($result_data[0]['puede_reabrir']) == 1) {
        return true;
    }

    return false;
}

function get_agentes()
{
    global $mysqli;

    $query = "SELECT 
       u.id,
       u.usuario,
       CONCAT(p.nombre, ' ', p.apellido_paterno, ' ', p.apellido_materno) AS nombre_completo
FROM tbl_usuarios u
LEFT JOIN tbl_personal_apt p ON p.id = u.personal_id
WHERE p.area_id = 9
AND u.estado  = 1;
-- AND p.cargo_id IN (18)";

    $result = $mysqli->query($query);
    $result_data = array();
    while ($r = $result->fetch_assoc()) {
        $result_data[] = $r;
    }

    return $result_data;
}
?>