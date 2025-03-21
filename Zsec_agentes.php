<?php
require_once("sys/global_config.php");
require_once("sys/db_connect.php");
require_once("sys/db_connect_adodb.php");
//--------------------------------------------------------------------------------------//
//require_once('class/clsGrupoCorreo.php');
//require_once('class/clsGrupoCorreoUsuario.php');
require_once('class/clsLocales.php');
//--------------------------------------------------------------------------------------//
/**
 * @author Edwin Huarhua 
 * Controlador principal Agentes Reportes
 */
//--------------------------------------------------------------------------------------//
//se valida usuario logeado
if(!isset($_SESSION['usuario'])){
    $resultado=array("success"=>true, "msg"=>"not logged in.");
    echo json_encode($resultado);
    exit();
}else{
    $do = isset($_REQUEST['do']) ? $_REQUEST['do']:"" ;
    if($sub_sec_id || $do==""){
        $do = 'INDEX';
    }
    switch($do){
        case 'INDEX':
            //se displaya el contenido html, o template en caso de q se use un template engine (VIEWS)
            echo file_get_contents("templates/tplAgentes.tpl");
        break;
        case 'AJAX_OBTENER_AGENTES':
            //
            $objLocales= new clsLocales();
            $rs=$objLocales->mxObtener(('AGENTES'));
            $arr['results']=$rs->GetArray();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($arr);
        break;
        default:
        break;
    }
}            
?>