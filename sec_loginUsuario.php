<?php
require_once("sys/global_config.php");
require_once("sys/db_connect.php");
require_once("sys/db_connect_adodb.php");
//--------------------------------------------------------------------------------------//
require_once('class/clsLoginGestion.php');
//--------------------------------------------------------------------------------------//
/**
 * Verifica el logueo del usuario, si existen las credenciales en la bd.
 */
//--------------------------------------------------------------------------------------//

$do = isset($_REQUEST['do']) ? $_REQUEST['do']:"" ;
$usuario = isset($_REQUEST['usuario']) ? $_REQUEST['usuario']:"" ;
$password = isset($_REQUEST['password']) ? $_REQUEST['password']:"" ;
switch($do){
    case 'loginGestion':
        if((isset($usuario) && $usuario !== '') && (isset($password) && $password !== '')){
            $objLoginGestion = new clsLoginGestion();
            $obtenerUsuarioId = $objLoginGestion->obtenerUsuarioId($usuario);
            if(!$obtenerUsuarioId){
                $err['error_usuario'] = 'Usuario incorrecto.';
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($err);
                die();
            }else if($obtenerUsuarioId === true){
                $err['error_usuario'] = 'Usuario inactivo.';
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($err);
                die();
            }
            $verificarPermiso = $objLoginGestion->verificarPermiso($obtenerUsuarioId);
            if(!$verificarPermiso){
                $err['error_permiso'] = 'No cuenta con este permiso. Comuniquese con soporte.';
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($err);
                die();
            }
            $rs = $objLoginGestion->loginGestion($usuario, $password);
            $arr['results'] = $rs->GetArray();
            if(empty($arr['results'])){
                $err['error_password'] = 'Contraseña incorrecta.';
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode($err);
                die();
            }
            $arr['results'][0]['id'] = intval($arr['results'][0]['id']);
            $arr['results'][0]['personal_id'] = intval($arr['results'][0]['personal_id']);
            $arr['results'][0]['estado'] = intval($arr['results'][0]['estado']);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($arr);
        }else{
            $err['error_validacion'] = 'Debe ingresar el usuario y contraseña.';
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($err);
        }
    break;
    
    default:
    break;
}
     