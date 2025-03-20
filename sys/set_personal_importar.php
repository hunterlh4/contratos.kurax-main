<?php
// Para la exportación en Excel
header('Content-Encoding: UTF-8');
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
$filename = "documento_exportado_".date('Y:m:d:m:s').".xls";
header("Content-Disposition: attachment; filename=\"$filename\"");

include("global_config.php");
include("db_connect.php");
include("sys_login.php");
require("globalFunctions/generalInfo/menu.php");
// error_reporting (E_ALL ^ E_NOTICE);
// funcion para eliminar tildes
function eliminar_tildes($cadena){
    //Ahora reemplazamos las letras
    $cadena = str_replace(
        array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
        array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
        $cadena );
    $cadena = str_replace(
        array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
        array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
        $cadena );
    $cadena = str_replace(
        array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
        array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
        $cadena );
    $cadena = str_replace(
        array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
        array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
        $cadena );
    $cadena = str_replace(
        array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
        array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
        $cadena );
    return $cadena;
}

// informacion básica del excel
$tipo       = $_FILES['file_personal_import']['type'];
$tamanio    = $_FILES['file_personal_import']['size'];
$archivotmp = $_FILES['file_personal_import']['tmp_name'];
$lineas     = file($archivotmp);
$output = "
<table>
    <thead>
        <tr style='background-color:#1F4E78; color: #FFFFFF;'>
            <th>ID personal</th>
            <th>DNI</th>
            <th>NOMBRE</th>
            <th>A. PATERNO</th>
            <th>A. MATERNO</th>
            <th>ID usuario</th>
            <th>USUARIO</th>
            <th>CONTRASEÑA</th>
            <th style='width:900px;'>OBSERVACIONES</th>
        </tr>
    </thead>
    <tbody>
";

$i = 0;
$h = 0;
$s3 = 0;
$fecha_create = date("Y-m-d H:i:s");

foreach ($lineas as $linea) {
    $dni = "";
    $nombre = "";
    $apellido_paterno = "";
    $apellido_materno = "";
    $area_id = "";
    $cargo_id = "";
    $sistema_id = "";
    $grupo_id = "";
    $complemento = "";
    $usuario = "";
    $correo = "";
    $telefono = "";
    $celular = "";
    $zona_id = "";
    $complemento = '';
    $id_nuevo_personal = array();

    $cantidad_registros = count((array)$linea);
    $cantidad_regist_agregados =  ($cantidad_registros - 1);
    $flag_dni = 'nuevo';
    $flag_cargo_id = 0;
    $adicionales = array();
    $errores = array();
    $ob_final = array();
    $observaciones = '';
    $estilo_fila = "";

    if ($i != 0) {        
        $datos = explode(";", $linea);
        // Si el csv no está separado por ; entonces lo separamos por ,
        // Normalmente el array debería tener 13 elementos
        if (count((array)$datos) < 3) {
            $datos = explode(",", $linea);
        }

        if ($datos[0] != 'NOMBRE' AND $datos[3] != 'DNI'){
            //Validación DNI
            if (!empty($datos[3])) {
                
                //$dni = iconv("ISO-8859-1", "UTF-8", preg_replace('/\s+/', '',($datos[3])));
                $dni = utf8_encode(trim($datos[3]));
                $dni = preg_replace('/\s+/', '',$dni);
                if (strlen($dni) > 8 or !is_numeric($dni)) {
                    $errores['dni'] = '| DNI contiene letras o más de 8 dígitos | ';
                } else {
                    if (strlen($dni) < 8) {
                        $n_ceros = 8 - strlen($dni);
                        if ($n_ceros == 2) {
                            $dni = '00'.$dni;
                        } else if ($n_ceros == 1) {
                            $dni = '0'.$dni;
                        } else {
                            $errores['dni'] = '| DNI contiene menos de 6 dígitos | ';
                        }
                    }
                    $exist_dni = $mysqli->query("SELECT id, nombre, apellido_paterno, apellido_materno FROM tbl_personal_apt WHERE dni = '".$dni."' LIMIT 1")->fetch_assoc();
                    if ($exist_dni) {
                        $flag_dni = 'existe';
                        $adicionales['dni'] = '| DNI de personal existente, sólo se creó nuevo usuario | ';

                        $datos[0] = utf8_decode($exist_dni['nombre']);
                        $datos[1] = utf8_decode($exist_dni['apellido_paterno']);
                        $datos[2] = utf8_decode($exist_dni['apellido_materno']);
                    }
                }
            } else {
                $errores['dni'] = '| Campo DNI está vacío | ';
            }

            //Validación NOMBRE
            $nombre = trim(utf8_encode($datos[0]));
            if (empty($datos[0])) {
                $errores['nombre'] = '| Campo Nombre está vacío | ';
            }

            // Validación APELLIDO PATERNO
            $apellido_paterno = trim(utf8_encode($datos[1]));
            if (empty($datos[1])) {
                $errores['apellido_paterno'] = '| Campo Apellido Paterno está vacío | ';
            }

            //Validación APELLIDO MATERNO
            $apellido_materno = trim(utf8_encode($datos[2]));
            /*
            if (empty($datos[2])) {
                $errores['apellido_materno'] = '| Campo Apellido Materno está vacío | ';
            }
            */

            //Validación AREA_ID
            $area_id = trim(utf8_encode($datos[4]));
            if (!empty($datos[4])) {
                if (!is_numeric($area_id)) {
                    $errores['area_id'] = '| ID de Area mal escrito: contiene letras o símbolos | ';
                } else {
                    $exist_area = $mysqli->query("SELECT id FROM tbl_areas WHERE id = '".$area_id."'")->fetch_assoc();
                    if (empty($exist_area)) {
                        $errores['area_id'] = '| ID de area no existe | ';
                    }
                }
            } else if ($flag_dni == 'nuevo') {
                $errores['area_id'] = '| Campo ID de Area está vacío | ';
            }

            //Validacion CARGO_ID
            $cargo_id = trim(utf8_encode($datos[5]));
            if (!empty($datos[5])) {
                if (!is_numeric($cargo_id)) {
                    $errores['cargo_id'] = '| ID de cargo mal escrito: contiene letras o símbolos | ';
                } else {
                    $exist_cargo = $mysqli->query("SELECT id FROM tbl_cargos WHERE id = '".$cargo_id."'")->fetch_assoc();
                    if (empty($exist_cargo)) {
                        $errores['cargo_id'] = '| ID de cargo no existe | ';
                    } else if ($flag_dni == 'existe') {
                        $flag_cargo_id = 1;
                    }
                }
            } else if ($flag_dni == 'nuevo') {
                $errores['cargo_id'] = '| Campo ID de cargo está vacío | ';
            }

            //Validacion CORREO
            $correo = trim(utf8_encode($datos[6]));
            if (!empty($datos[6])) {
                if ((strpos($correo, '@') === false)) {
                    $errores['correo'] = '| Correo mal escrito: falta @ | ';
                }
            }

            //Validacion telefono
            $telefono = trim(utf8_encode($datos[7]));
            if (!empty($datos[7])) {
                if (!is_numeric($telefono)) {
                    $errores['telefono'] = '| Telefono mal escrito: contiene letras o símbolos | ';
                }
            }

            //Validacion celular
            $celular = trim(utf8_encode($datos[8]));
            if (!empty($datos[8])) {
                if (!is_numeric($celular)) {
                    $errores['celular'] = '| Celular mal escrito: contiene letras o símbolos | ';
                }
            }

            //Validación ZONA ID
            $zona_id = $datos[9];
            if (!empty($datos[9])) {
                if (!is_numeric($zona_id)) {
                    $errores['zona_id'] = '| ID de Zona mal escrito: contiene letras o símbolos | ';
                } else {
                    $exist_zona = $mysqli->query("SELECT id FROM tbl_zonas WHERE id = '".$zona_id."'")->fetch_assoc();
                    if (empty($exist_zona)) {
                        $errores['zona_id'] = '| ID de zona no existe | ';
                    }
                }
            } else {
                $zona_id = "NULL";
            }

            //Validacion SISTEMA_ID
            $sistema_id = trim((utf8_encode($datos[12])));
            if (!empty($datos[12])) {
                if (!is_numeric($sistema_id)) {
                    $errores['sistema_id'] = '| ID de sistema mal escrito: contiene letras o símbolos | ';
                } else {
                    $exist_sistema = $mysqli->query("SELECT id FROM tbl_sistemas WHERE id = '".$sistema_id."'")->fetch_assoc();
                    if (empty($exist_sistema)) {
                        $errores['sistema_id'] = '| ID de sistema no existe | ';
                    }
                }
            } else {
                $errores['sistema_id'] = '| Campo ID de sistema está vacío | ';
            }

            //Validacion GRUPO_ID
            $grupo_id = trim(utf8_encode($datos[13]));
            if (!empty($datos[13])) {
                if (!is_numeric($grupo_id)) {
                    $errores['grupo_id'] = '| ID de grupo mal escrito: Contiene letras o símbolos | ';
                } else {
                    $exist_grupo = $mysqli->query("SELECT id FROM tbl_usuarios_grupos WHERE id = '".$grupo_id."'")->fetch_assoc();
                    if (empty($exist_grupo)) {
                        $errores['grupo_id'] = '| ID de grupo no existe | ';
                    }
                }
            } else {
                $errores['grupo_id'] = '| Campo ID de grupo está vacío | ';
            }

            if (!empty($errores)) {
                $id_nuevo_personal['id'] = '';
                $id_user_nuevo['id'] = '';
                $usuario = '';
                $password = '';
                $se = 0;
                if ($se == 0) {
                    $estilo_fila = "style='background-color:#FFFF00;'";
                    $se = 1;
                } else {
                    $estilo_fila = "style='background-color:#FFFF99;'";
                    $se = 0;
                }
                $ob_final = $errores;
            } else {
                // Creamos el nuevo personal si el dni no es duplicado
                if ($flag_dni == 'nuevo') {
                    $mysqli->query("START TRANSACTION");
                    $insert_personal_command = "INSERT INTO tbl_personal_apt (nombre, apellido_paterno, apellido_materno, dni, area_id, cargo_id, correo, telefono, celular, zona_id, estado,created_at,user_created_id ) VALUES ('".$nombre."','".$apellido_paterno."','".$apellido_materno."','".$dni."',".$area_id.",".$cargo_id.",'".$correo."','".$telefono."','".$celular."',".$zona_id.",'1','".$fecha_create."',".$login["id"].")";
                    $mysqli->query($insert_personal_command);
                }

                //Creación de usuario
                $usuario = !empty($datos[10])  ? (utf8_encode($datos[10])) : strtolower($nombre).'.'.strtolower($apellido_paterno);
                if (!empty($datos[11])) {
                    $complemento = '.'.strtolower(utf8_encode($datos[11]));
                    $complemento = trim($complemento);
                }
                $usuario = $usuario.$complemento;
                $usuario = preg_replace('/\s+/', '',eliminar_tildes($usuario)); // Eliminamos los espacios en blanco
                //Contamos la cantidad de registros para ver si hay duplicados
                $exist_usuario = $mysqli->query("SELECT COUNT(*) AS total FROM tbl_usuarios WHERE usuario = '".$usuario."'")->fetch_assoc();
                if ($exist_usuario['total']>0) {
                    $pre_usuario = $usuario;
                    $exist_usuario_2 = $mysqli->query("SELECT COUNT(*) AS total FROM tbl_usuarios WHERE usuario LIKE '".$pre_usuario."_'")->fetch_assoc();
                    if ($exist_usuario_2['total']>0) {
                        $usuario = $usuario.($exist_usuario_2['total']+1);
                    } else {
                        $usuario = $usuario.$exist_usuario['total'];
                    }
                }
                $usuario = preg_replace('/\s+/', '',eliminar_tildes($usuario)); // Eliminamos los espacios en blanco

                // Buscamos el id del personal
                $id_nuevo_personal = $mysqli->query("SELECT id FROM tbl_personal_apt WHERE dni = '".$dni."' ORDER BY id DESC LIMIT 1")->fetch_assoc();
                $insert_user_command = "INSERT INTO tbl_usuarios (usuario,personal_id,sistema_id,grupo_id,estado,fecha_masivo,created_at,user_created_id ) ";
                $insert_user_command.= " VALUES ('".$usuario."','".$id_nuevo_personal['id']."',".$sistema_id.",".$grupo_id.",'1',NOW(),'".$fecha_create."',".$login["id"].")";
                $mysqli->query($insert_user_command);

                // Actualizamos el cargo del personal
                if ($flag_cargo_id == 1) {
                    $mysqli->query("START TRANSACTION");
                    $insert_personal_command = "UPDATE tbl_personal_apt SET cargo_id = ".$cargo_id." WHERE id = ".$id_nuevo_personal['id']."";
                    $mysqli->query($insert_personal_command);

                    $adicionales['cargo_id'] = "| ID_CARGO ingresado, cargo actualizado | ";
                }

                // Buscamos el id del nuevo usuario creado
                $id_user_nuevo = array();
                $id_user_nuevo = $mysqli->query("SELECT id FROM tbl_usuarios WHERE usuario = '".$usuario."' ORDER BY id DESC LIMIT 1")->fetch_assoc();
                if ($grupo_id != 0) {
                    $insert_perms = "";
                    $insert_perms = "INSERT INTO tbl_permisos (grupo_id,menu_id,boton_id,boton_nombre,estado,usuario_id)
                            SELECT grupo_id,menu_id,boton_id,boton_nombre,estado,'". $id_user_nuevo['id']."' FROM tbl_permisos
                            WHERE grupo_id = ".$grupo_id." AND usuario_id=0";
                    $mysqli->query($insert_perms);

                    $menu_manual_usuario_id = getMenuBySecId('manual_usuarios');
                    $query = "UPDATE tbl_permisos SET estado = 1
                    WHERE usuario_id = '".$id_user_nuevo['id']."' AND boton_id = 1 AND menu_id = '".$menu_manual_usuario_id."'";
                    $result = $mysqli->query($query);
                }
                // Creamos una contraseña para los usuarios
                $password = "";
                $password = $usuario.'.123456';
                $password_md5 = "";
                $password_md5 = md5($password);
                $update_password_command = "UPDATE 
                tbl_usuarios 
                SET password_md5 = '".$password_md5."'
                , password_changed = NULL 
                WHERE id = '".$id_user_nuevo["id"]."'";
                $mysqli->query($update_password_command);

                $update_login_command = "UPDATE 
                tbl_login
                SET logout_datetime = '".date("Y-m-d H:i:s")."',
                logout_ip = 'pass_restored' 
                WHERE usuario_id = '".$id_user_nuevo["id"]."' 
                AND logout_datetime IS NULL";
                $mysqli->query($update_login_command);
                
                $mysqli->query("COMMIT");

                if ($h == 0) {
                    $estilo_fila = "style='background-color:#F2F2F2;'";
                    $h = 1;
                } else {
                    $estilo_fila = "";
                    $h = 0;
                }
                $ob_final = $adicionales;
            }

            foreach ($ob_final as $ob_datos) {
                $observaciones.= $ob_datos;
            }

            $output.= "
            <tr ".$estilo_fila.">
                <td>".$id_nuevo_personal['id']."</td>
                <td style=\"mso-number-format:'@';\">".$dni."</td>
                <td>".$nombre."</td>
                <td>".$apellido_paterno."</td>
                <td>".$apellido_materno."</td>
                <td>".$id_user_nuevo['id']."</td>
                <td>".$usuario."</td>
                <td>".$password."</td>
                <td>".$observaciones."</td>
            </tr>
            ";
        }
    }
    $i++;
}
$output.= "
    </tbody>
</table>
";
$output = iconv("UTF-8", "ISO-8859-1", $output);
echo $output;

?>