<?php
require_once("sys/global_config.php");
require_once("sys/db_connect.php");
require_once("sys/db_connect_adodb.php");
//--------------------------------------------------------------------------------------//
require_once('class/clsGrupoCorreo.php');
require_once('class/clsGrupoCorreoUsuario.php');
//--------------------------------------------------------------------------------------//
/**
 * @author Edwin Huarhua 
 * Controlador principal que administra/gestiona toda la logica de Grupos de Correos
 * y grupos de correos-usuarios. Similar a grupos de gmail, se adhieren usuarios a grupos
 * para que el envio masivo de correos sea administrable y se evite el uso de correos
 * en codigo fuente.
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
            echo file_get_contents("templates/tplGrupoCorreo.tpl");
        break;
        case 'AJAX_OBTENER_USUARIO':
            //se obtiene todos los usuarios con nombres completos
            $objGrupoCorreoUsuario = new clsGrupoCorreoUsuario();
            $rs = $objGrupoCorreoUsuario->mxObtenerUsuario();
            $arr['results']=$rs->GetArray();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($arr);
        break;
        case 'AJAX_OBTENER_USUARIOS_X_GRUPO_CORREO':
            //obtener los usuarios pertenecientes a un grupo de correos
            $objGrupoCorreoUsuario = new clsGrupoCorreoUsuario();
            $rs = $objGrupoCorreoUsuario->mxObtener('USUARIOS_X_GRUPO_CORREO', (int) $_REQUEST['id_grupo_correo']);
            $arr['results']=$rs->GetArray();
            echo json_encode($arr);
        break;
        case 'AJAX_OBTENER_GRUPO_CORREO':
            $sql = "select id, nombre, descripcion, estado from tbl_grupo_correo";
            $rs= $_adb->Execute($sql);
            $arr['results']=$rs->GetArray();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($arr);
        break;
        case 'AJAX_GRABAR_GRUPO_CORREO':
            $objGrupoCorreo = new clsGrupoCorreo();
            $vusuario  = $_SESSION['usuario'];
            if((int)$_REQUEST['hid_indicador']==0){
                //nuevo registro
                $objGrupoCorreo->usuario_creacion=$vusuario;
                $objGrupoCorreo->usuario_actualizacion=$vusuario;
                $objGrupoCorreo->fecha_creacion=date('Y-m-d H:i:s');
                $objGrupoCorreo->fecha_actualizacion=date('Y-m-d H:i:s');
            }else{
                //registro ya existe
                $objGrupoCorreo->load("id=".(int)$_REQUEST["id"]);
                $objGrupoCorreo->usuario_actualizacion=$vusuario;
                $objGrupoCorreo->fecha_actualizacion=date('Y-m-d H:i:s');
            }
            $objGrupoCorreo->nombre=$_REQUEST["nombre"];
            $objGrupoCorreo->descripcion = $_REQUEST["descripcion"];
            $objGrupoCorreo->estado = 1;
            $objGrupoCorreo->save();
            $resultado=array("success"=>true, "msg"=>"Operacion Exitosa.");
            echo json_encode($resultado);
        break;
        case 'AJAX_GRABAR_GRUPO_CORREO_USUARIO':
            //guarda el detalle de grupo de correo 
            $objGCU= new clsGrupoCorreoUsuario();
            $vusuario= $_SESSION['usuario'];
            if((int)$_REQUEST['hid_indicador_usuario']==0){
                //nuevo registro
                $objGCU->usuario_creacion=$vusuario;
                $objGCU->usuario_actualizacion =$vusuario;
                $objGCU->fecha_creacion = date('Y-m-d H:i:s');
                $objGCU->fecha_actualizacion  =date('Y-m-d H:i:s');
            }else{
                //registro ya existe
                $objGCU->load("id=".$_REQUEST['gcu_id']);
                $objGCU->usuario_actualizacion =$vusuario;
                $objGCU->fecha_actualizacion  =date('Y-m-d H:i:s');
            }
            $objGCU->id_grupo_correo = $_REQUEST['id_grupo_correo'];
            $objGCU->id_usuario = (INT)$_REQUEST['id_usuario'];
            $objGCU->tipo_receptor = $_REQUEST['tipo_receptor'];
            $objGCU->estado = 1;
            $objGCU->save();
            $resultado=array("success"=>true, "msg"=>"Operacion Exitosa.");
            echo json_encode($resultado);
        break;
        case 'AJAX_ELIMINAR_GRUPO_CORREO_USUARIO':
            //se elimina/desactiva un usuario perteneciente a un grupo correo
            $objGCU= new clsGrupoCorreoUsuario();
            $objGCU->load("id=".(int)$_REQUEST['gcu_id']);
            $objGCU->estado = 0;
            $objGCU->usuario_actualizacion =$vusuario;
            $objGCU->fecha_eliminacion =date('Y-m-d H:i:s');
            $objGCU->save();
            $resultado=array("success"=>true, "msg"=>"Operacion Exitosa.");
            echo json_encode($resultado);
        break;
        case 'USUARIOS_X_NOMBRE_GRUPO_CORREO_X_RECEPTOR':
            //Codigo de ejemplo para ser usado desde otros archivos, solo con el nombre del grupo de correo
            $objGrupoCorreoUsuario = new clsGrupoCorreoUsuario();
            $arr_parametro['nombre_grupo_correo']='grupo QA';
            $arr_parametro['tipo_receptor']='CC';
            //$arr_correo= $objGrupoCorreoUsuario->mxObtener('USUARIOS_X_GRUPO_CORREO_X_RECEPTOR',$arr_parametro);
            $arr_correo= $objGrupoCorreoUsuario->mxObtener('USUARIOS_X_NOMBRE_GRUPO_CORREO_X_RECEPTOR',$arr_parametro);
            header('Content-Type: application/json; charset=utf-8');
            $arr['results']=$arr_correo;
            echo json_encode($arr);
        break;
        default:
        break;
    }
}            