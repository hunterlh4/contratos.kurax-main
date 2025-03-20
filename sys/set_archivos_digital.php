<?php
include("/var/www/html/sys/db_connect.php");
include("/var/www/html/sys/sys_login.php");
include '/var/www/html/fastreport/helper.php';

if(!isset($login['id'])){
    $resultado=array("success"=>true, "msg"=>"not logged in.");
	header("Location: ../index.php");
    //echo json_encode($resultado);
    exit();
}
$return = array();
$return["memory_init"]=memory_get_usage();
$return["time_init"] = microtime(true);
include("global_config.php");
include("db_connect.php");
include("sys_login.php");
include '/var/www/html/sys/globalFunctions/templates/crud.php';

$digitalpath = "/var/www/storage/website/landing-dinamico/";

if (isset($_GET["action"])) {
    if ($_GET["action"] === "carga_carpetas") {
        $lista_carpetas = "";
	   /** $directorio_digital = opendir($digitalpath);

        $table = "";
        $list_element = array();
        while($element_d = readdir($directorio_digital)) {
            if ($element_d != "." && $element_d != ".." && $element_d != "PAPELERA" ) {
                if (is_dir($digitalpath.$element_d)){
                    $list_element[] = $element_d;
                }
            }
        }
        natcasesort($list_element);
        foreach ($list_element as $li_e) {
            $table .= "<tr id='tbl_carpetas_$li_e'>";
            $table .= "<td class='text-center'><input type='checkbox' class='chk_file' data-name='$li_e' ></td>";
            $table .= "<td id='tbl_carpetas_td_$li_e'>$li_e <button type='button' class='btn btn-secondary btn-sm p-1' onclick='cargarSubcarpetas('".$li_e.")'>Subcarpetas</button></td>";
            $table .= "</tr>";
        }
        echo $table;
        exit();*/
        $carpetas = listar_directorios($digitalpath);
        echo json_encode($carpetas);
        exit();
    } else if ($_GET["action"] === "cargar_subcarpetas") {
        $carpeta = $_GET["carpeta"] ?? null;
        if ($carpeta == null) {
            echo "No se ha seleccionado carpeta";
            exit();
        }

        $nombre_carpeta = basename($_POST["nombre_carpeta"]);
        $carpeta = basename($carpeta);
        $path_to_search = realpath($digitalpath . $nombre_carpeta) . "/";
        if (!is_dir($path_to_search)) {
            echo "No existe la carpeta";
            exit();
        }
        $lista_carpetas = "";
        $directorio_digital = opendir($path_to_search);

        $html_list = "<ul class='list-group'>";
        $list_element = array();
        while($element_d = readdir($directorio_digital)) {
            if ($element_d != "." && $element_d != ".." && $element_d != "PAPELERA" ) {
                if (is_dir($path_to_search.$element_d)){
                    $list_element[] = $element_d;
                }
            }
        }
        natcasesort($list_element);
        foreach ($list_element as $li_e) {
            /*$table .= "<tr>";
            $table .= "<td class='text-center'><input type='checkbox' class='chk_file' data-name='$li_e' ></td>";
            $table .= "<td>$li_e <button type='button' class='btn btn-secondary btn-sm p-1' onclick=''>Subcarpetas</button></td>";
            $table .= "</tr>";*/
            $html_list .= "<li class='list-group-item d-flex justify-content-between align-items-center' data-name='$li_e' data-path='$path_to_search$li_e'>";
            $html_list .= "$li_e <button type='button' class='btn btn-secondary btn-sm p-1' onclick=''>Subcarpetas</button>";
            $html_list .= "</li>";
        }
        echo $html_list."</ul>";
        exit();
    } else if ($_GET["action"] === "crea_subcarpeta") {
        $nombre_carpeta = basename($_POST["nombre_carpeta"]);
        $nombre_subcarpeta = basename($_POST["nombre_subcarpeta"]);
        $dir_path = $digitalpath . $nombre_carpeta . "/" . $nombre_subcarpeta;
        if (is_dir($dir_path)) {
            $return["error"]="Atención";
            $return["error_type"]="warning";
            $return["error_msg"]="La carpeta ya existe en el directorio";
        } else if (strpos($dir_path, " ")){
            $return["error"]="Atención";
            $return["error_type"]="warning";
            $return["error_msg"]="El nombre de la carpeta no puede tener espacios en blanco";
        } else {
            if (mkdir($dir_path)) {
                $return["filepath"]=$dir_path;
                $return["filename"]= $nombre_subcarpeta;
            } else {
                $return["error"]="Error";
                $return["error_type"]="error";
                $return["error_msg"]="No se pudo crear la carpeta";
            }
        }
    } else if ($_GET["action"] === "crea_carpeta") {
        $nombre_carpeta = basename($_POST["nombre_carpeta"]);
        $dir_path = $digitalpath . $nombre_carpeta;
        if (is_dir($dir_path)) {
            $return["error"]="Atención";
            $return["error_type"]="warning";
			$return["error_msg"]="La carpeta ya existe en el directorio";
        } else if (strpos($dir_path, " ")){
            $return["error"]="Atención";
            $return["error_type"]="warning";
			$return["error_msg"]="El nombre de la carpeta no puede tener espacios en blanco";
        } else {
            if (mkdir($dir_path)) {
                $return["filepath"]=$dir_path;
                $return["filename"]= $nombre_carpeta;
            } else {
                $return["error"]="Error";
                $return["error_type"]="error";
			    $return["error_msg"]="No se pudo crear la carpeta";
            }
        }
    } else if ($_GET["action"] === "crea_categoria") {
        if(!empty($_POST['nombre_categoria'])){

            $insert_categoria =  insertTable('tbl_archivos_categoria', [
                'name' => $_POST['nombre_categoria'],
                'active' => '1',
                'created_at' => date('Y-m-d H:i:s'),
                'user_created_id' => $login['id']    
            ]);
            
            if (!empty($insert_categoria['mysqli_error'])) {
                // print_r('ok');
                $return = $insert_categoria;
                $return["error"]="Error";
                $return["error_type"]=$insert_categoria['query'];
			    $return["error_msg"]=$insert_categoria['mysqli_error'];
            }
        } else {
            $return["error"]="Error";
            $return["error_type"]="error";
            $return["error_msg"]="El nombre de la categoría no puede quedar vacío.";
        }
    } else if ($_GET["action"] === "editar_categoria") {
        if(!empty($_POST['nombre_categoria_edit']) && !empty($_POST['id_categoria_edit']) && isset($_POST['estado_categoria_edit']) ){

            $id_categoria = $_POST['id_categoria_edit'];
            $nombre_categoria = $_POST['nombre_categoria_edit'];
            $estado_categoria = $_POST['estado_categoria_edit'];
            $query = "UPDATE tbl_archivos_categoria 
                        SET name = '$nombre_categoria', 
                            active = '$estado_categoria',
                            updated_at = '".date('Y-m-d H:i:s')."', 
                            user_updated_id = '".$login["id"]."' 
                        WHERE id = $id_categoria ;";
            $mysqli->query($query);
            $mysqli_error = $mysqli->error;

            if($mysqli_error){
                $return["error"]="Error";
                $return["error_type"]=$mysqli_error['query'];
			    $return["error_msg"]=$mysqli_error;
            }
        } else {
            $return["error"]="Error";
            $return["error_type"]=$_POST['nombre_categoria_edit'];
            $return["error_msg"]="El nombre o el estado de la categoría no puede quedar vacío.";
        }

    } else if ($_GET["action"] === "elimina_carpeta") {
        $nombre_carpeta = basename($_POST["nombre_carpeta"]);
        $dir_path = $digitalpath . $nombre_carpeta;
        $dir_papelera = $digitalpath . "PAPELERA/" . $nombre_carpeta."-".date("d-m-Y h:i:s");
        full_copy_digital($dir_path, $dir_papelera);
        deleteDirectory_digital($dir_path);
        $query = "SELECT id FROM tbl_archivos_drive WHERE category_id = 10 AND file_subdirectory = '". $nombre_carpeta ."'";
        $result = $mysqli->query($query);
        foreach ($result as $res){
            $mysqli->query("
                UPDATE tbl_archivos_drive 
                SET active = 0,
                updated_at='".date('Y-m-d H:i:s')."'
                WHERE id = ".$res["id"]."
            ");
        }
    } else if ($_GET["action"] === "remove_categoria") {
        $categoria_id = $_POST['categoria_id'];
        $query = "DELETE FROM tbl_archivos_categoria WHERE id = $categoria_id ;";
        $result = $mysqli->query($query);
        $mysqli_error = $mysqli->error;

        if($mysqli_error){
            $result["error"]="mysql";
            $result["mysqli_error"]=$mysqli_error;
            $result["query"]=$query;
        } 
    }
}

function deleteDirectory_digital($dir) {
    if(!$dh = opendir($dir)) return;
    while (false !== ($current = readdir($dh))) {
        if($current != '.' && $current != '..') {
            if (!unlink($dir.'/'.$current)) 
                deleteDirectory_digital($dir.'/'.$current);
        }
    }
    closedir($dh);
    rmdir($dir);
}

//Crear nuevos directorios completos
function full_copy_digital( $source, $target ) {
    if (is_dir($source)) {
        mkdir($target);
        $d = dir($source);
        while (FALSE !== ($entry = $d->read())) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            $Entry = $source.'/'.$entry; 
            if (is_dir($Entry)) {
                full_copy_digital($Entry, $target.'/'.$entry);
                continue;
            }
            copy($Entry, $target.'/'.$entry);
        }

        $d->close();
    }else {
        copy($source, $target);
    }
}

function listar_directorios($path, $posicion = 0){
    $array_directorios = array();
    if (is_dir($path)) {
        if ($directorio = opendir($path)) {
            $aux_posicion = 0;
            while (($archivo = readdir($directorio)) !== false) {
                if ($archivo != '.' && $archivo != '..' && $archivo != 'PAPELERA') {
                    if (is_dir($path.$archivo)) {
                        $array_directorios[$aux_posicion]["nombre"] = $archivo;
                        $array_directorios[$aux_posicion]["ruta"] = $path.$archivo;
                        $array_directorios[$aux_posicion]["posicion"] = $posicion;
                        $array_directorios[$aux_posicion]["subdirectorios"] = listar_directorios($path.$archivo."/", $posicion+1);
                        $aux_posicion++;
                    }
                }
            }
            closedir($directorio);
        }
        return $array_directorios;
    } else {
        echo "No es un directorio";
    }
}

$return["memory_end"]=memory_get_usage();
$return["time_end"] = microtime(true);
$return["memory_total"]=($return["memory_end"]-$return["memory_init"]);
$return["time_total"]=($return["time_end"]-$return["time_init"]);
print_r(json_encode($return));
