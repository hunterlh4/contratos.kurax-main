<?php
require_once("sys/global_config.php");
require_once("sys/db_connect.php");
require_once("sys/db_connect_adodb.php");
//--------------------------------------------------------------------------------------//
//require_once('class/clsGrupoCorreo.php');
//require_once('class/clsGrupoCorreoUsuario.php');
require_once('class/clsAuditoria.php');
//--------------------------------------------------------------------------------------//
/**
 * @author Edwin Huarhua 
 * Controlador principal que administra/gestiona toda la logica de Usuarios Logs
 * ?sec_id=locales
 * ?sec_id=usuarios&sub_sec_id=usuarios
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
            echo file_get_contents("templates/tplAuditoria.tpl");
        break;
        case 'AJAX_OBTENER_ACTIVACION_X_USUARIO':
            //para procesos de (des)activacion 
            $objAuditoria = new clsAuditoria();
            //$usuario_id= (int)29;
            $dfecha_inicio= $_REQUEST['fecha_inicio']; //importante!!!
            $rs= $objAuditoria->mxObtener('OBTENER_ACTIVACION_X_USUARIO', $dfecha_inicio);
            //json_decode($json)
            /*
            $arr_auditoria=$rs->GetArray();
            for($i=0;$i<count($arr_auditoria);$i++){
                $arr_i = json_decode($arr_auditoria[$i]['data']);
                //echo $arr_i->id_switch."@".$arr_i->usuario_id."\n";
            }
            */
            $arr['results']=$rs->GetArray();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($arr);
        break;
        default:
        break;
    }
}            
?>