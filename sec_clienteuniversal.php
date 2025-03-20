<?php
require_once("sys/global_config.php");
require_once("sys/db_connect.php");
require_once("sys/db_connect_adodb.php");
//require_once("sys/db_connect_adodb_universal.php");
//--------------------------------------------------------------------------------------//
require_once('class/clsClienteUniversal.php');
require_once('class/clsEtiquetaTeleventas.php');
//require_once('class/clsGrupoCorreoUsuario.php');
//--------------------------------------------------------------------------------------//
/**
 * @author Edwin Huarhua 
 * GES-2082
 */
//--------------------------------------------------------------------------------------//
//se valida usuario logeado
if(!isset($_SESSION['usuario'])){
    $resultado=array("success"=>true, "msg"=>"not logged in.");
    echo json_encode($resultado);
    exit();
}else{
    $do = isset($_REQUEST['do']) ? $_REQUEST['do']:"" ;
    if($sub_sec_id){
        $do = 'INDEX';
    }
    switch($do){
        case 'INDEX':
            //se displaya el contenido html, o template en caso de q se use un template engine (VIEWS)
            echo file_get_contents("templates/tplClienteUniversal.tpl");
        break;
        case 'AJAX_OBTENER_ETIQUETAS_TELEVENTAS':
            $objEtiquetaTeleventas = new clsEtiquetaTeleventas();
            $rs = $objEtiquetaTeleventas->mxObtener('ETIQUETA_TELEVENTAS_UNIVERSAL');
            $arr['results']=$rs->GetArray();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($arr);
        break;
        case 'INFO':
            print_r($_SESSION);
        break;
        case 'SESSION':
            echo '{"usuario":"'.$_SESSION['usuario'].'"}';
        break;
        default:
        break;
    }
}            