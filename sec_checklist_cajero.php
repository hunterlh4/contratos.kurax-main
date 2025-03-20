<?php
require_once("sys/global_config.php");
require_once("sys/db_connect.php");
require_once("sys/db_connect_adodb.php");
//--------------------------------------------------------------------------------------//
require_once('class/clsCheckList.php');
require_once('class/clsCheckListUsuario.php');
require_once('class/clsLocales.php');
require_once('class/clsZonas.php');
require_once('class/clsGrupoCorreoUsuario.php');

//require_once('class/clsGrupoCorreoUsuario.php');
include 'phpexcel/classes/PHPExcel.php';
//--------------------------------------------------------------------------------------//
/**
 * @author Edwin Huarhua 
 * Controlador principal CHECKLISTCAJERO
 */
//--------------------------------------------------------------------------------------//
//Se define la constante televentas cuyos locales estan EXONERADOS del checklist
const TELEVENTAS_ID = 8;
/**
 * Obtiene el ultimo valor del mes del anio consultado
 */
function fxUltimoDia($anio,$mes){
    if (((fmod($anio,4)==0) and (fmod($anio,100)!=0)) or (fmod($anio,400)==0)) {
        $dias_febrero = 29;
    } else {
        $dias_febrero = 28;
    }
    switch($mes) {
        case 1: return 31; break;
        case 2: return $dias_febrero; break;
        case 3: return 31; break;
        case 4: return 30; break;
        case 5: return 31; break;
        case 6: return 30; break;
        case 7: return 31; break;
        case 8: return 31; break;
        case 9: return 30; break;
        case 10: return 31; break;
        case 11: return 30; break;
        case 12: return 31; break;
    }
}

/**
 * Funcionalidad que envia Mails (en caso de respuestas negativas) a 2 arreglos de correos,
 * $arrCC : arreglo de correos con copia, obligatorio al menos 1
 * $arrBCC: arreglo de correos ocultos, opcional, puede estar vacio
 */
function fxSendEmailCheckList($_vusuario, $_irespuestas_negativas, $arrCC=array(), $arrBCC=array(), $_local=""){
    $local=$_local;
    $vusuario = $_vusuario;
    $dfecha = date('Y-m-d');
    $dtfecha = date('Y-m-d h:i:s a');
    $irespuestas_negativas = $_irespuestas_negativas;
    $return = array();
    try{
        require_once('sys/mailer/class.phpmailer.php');
        $mail = new PHPMailer(true);
        $mail->IsSMTP(); 
        $mail->SMTPDebug  = 1;                     
        $mail->SMTPAuth   = true;                  
        $mail->Host       = "smtp.gmail.com";     
        $mail->Port       = 465;  
        $mail->SMTPSecure = "ssl"; 
        
        //Se necesita almenos 1 correo en CC, caso contrario no se envia nada
        if(count($arrCC)>0){
            foreach ($arrCC as $cc){
                $mail->AddAddress($cc);
            }
            foreach ($arrBCC as $bcc){
                $mail->AddBCC($bcc);
            }
            //$mail->AddBCC("edwin.huarhua@testtest.kurax.dev");
            //$mail->AddBCC("ehuarhua@testtest.gmail.com");  
            $mail->Username   =env('MAIL_GESTION_USER');  
            $mail->Password   =env('MAIL_GESTION_PASS');        
            $mail->FromName = "Apuesta Total";
            $mail->Subject    = "Registro Incidencia local:$local, Cajero: $vusuario, $dtfecha";
            $mail->Body = "<html>
            <head>
            <h1 style='font-family:arial;'> </h1>
            </head>
            <body>
                Local: $local. Usuario : $vusuario, Registro : $irespuestas_negativas respuestas negativas, $dtfecha
            </body>
            </html>
            </head>";
            $mail->isHTML(true);
            if($mail->send()) 
                $return["email_sent"]="ok";
            else 
                $return["email_error"] = $mail->ErrorInfo;
            return $return;
        }else{
            $return["email_sent"] = "false:"  ;
            return $return;
        }
	}catch(phpmailerException $ex){
        $return["email_error"]=$mail->ErrorInfo;
        return $return;
	}
}

//se valida usuario logeado
if(!isset($_SESSION['usuario'])){
    $resultado=array("success"=>true, "msg"=>"not logged in.");
    echo json_encode($resultado);
    exit();
}else{
    $do = isset($_REQUEST['do']) ? $_REQUEST['do']:"" ;
    if($sub_sec_id){
        //Quiere decir que se esta llamando desde menu sistema Gestion
        $do= strtoupper($sub_sec_id);
    }
    switch($do){
        case 'INDEX':
            //se displaya el contenido html, o template en caso de q se use un template engine (VIEWS)
            //$vusuario  = isset($_SESSION['usuario']) ? $_SESSION['usuario']:"";
            //echo $vusuario;
            //print_r($_SESSION);
            echo file_get_contents("templates/tplCheckList.tpl");
        break;
        case 'MANTENIMIENTO':
            echo file_get_contents("templates/tplCheckListMantenimiento.tpl");
        break;
        case 'CHECKLISTREPORTE':
            echo file_get_contents("templates/tplCheckListReporte.tpl");
        break;
        case 'AJAX_GRABAR_CHECKLIST':
            //Para guardar las preguntas que seran llenadas por el usuario mas adelante
            $vusuario  = isset($_SESSION['usuario']) ? $_SESSION['usuario']:"";
            $usuario_id = isset($_SESSION['id']) ? $_SESSION['id']:"";
            $objCheckList = new clsCheckList();
            if((int)$_REQUEST['hid_indicador']==0){
                //nuevo registro
                $objCheckList->estado=1;

                $objCheckList->user_created = $vusuario;
                $objCheckList->created_at =date('Y-m-d H:i:s');
            }else{
                //registro ya existe
                $objCheckList->load("id=".(int)$_REQUEST["id"]);
                $objCheckList->user_updated =$vusuario;
                $objCheckList->updated_at =date('Y-m-d H:i:s');
            }
            $objCheckList->item_numero = 0;
            $objCheckList->item_nombre = $_REQUEST['item_nombre'];
            $objCheckList->item_descripcion = $_REQUEST['item_descripcion'];
            $objCheckList->indicador = $_REQUEST['indicador'];
            $objCheckList->save();
            header('Content-Type: application/json; charset=utf-8');
            $resultado=array("success"=>true, "msg"=>"Operacion Exitosa.");
            echo json_encode($resultado);
        break;
        case 'AJAX_ASIGNAR_GRUPO_CORREO_CHECKLIST':
            //Se asigna una unica vez el grupo de correo a todos los checklist activos
            $objCheckList = new clsCheckList();
            $grupo_correo_id = (int)$_REQUEST['grupo_correo_id'];
            $brespuesta = $objCheckList->mxAsignarGrupoCorreo($grupo_correo_id);
            if($brespuesta){
                $resultado=array("success"=>true, "msg"=>"Asignacion Grupo Correo Exitosa.");
            }else{
                $resultado=array("success"=>false, "msg"=>"Error en proceso Asignacion Grupo Correo");
            }
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($resultado);
        break;
        case 'AJAX_OBTENER_CHECKLIST':
            //se obtiene todos los usuarios con nombres completos
            $objCheckList = new clsCheckList();
            $rs = $objCheckList->mxObtener('CHECKLIST');
            $arr['results']=$rs->GetArray();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($arr);
        break;
        case 'AJAX_GRABAR_CHECKLIST_USUARIO':
            //Una vez llenado el checklist x parte del cajero, se almacena todas sus respuestas
            $vusuario  = isset($_SESSION['usuario']) ? $_SESSION['usuario']:"";
            $usuario_id = isset($_SESSION['id']) ? $_SESSION['id']:"";
            $local_id = isset($_SESSION['local_id']) ? $_SESSION['local_id']:"";
            
            $arreglo=$_REQUEST["modificados"];
            
            $objZona = new clsZonas();
            $objLocales = new clsLocales();
            $objLocales->load("id=".(int)$local_id);
            $nombre_local = $objLocales->nombre;
            $zona_id = $objLocales->zona_id;
            $objZona->load("id=".(int)$zona_id);
            $nombre_zona = $objZona->nombre;
            //---------------------------------------------------------------------//
            $icontador_respuesta_negativa=0;
            try{
                $v=json_decode($arreglo);
                for($i=0;$i<count($v);$i++){
                    $objCheckListUsuario = new clsCheckListUsuario();
                    $objCheckListUsuario->usuario_id = $usuario_id; 
                    $objCheckListUsuario->usuario = $vusuario;
                    $objCheckListUsuario->fecha_registro = date('Y-m-d');
                    $objCheckListUsuario->local_id = $local_id; 
                    $objCheckListUsuario->local = $nombre_local; 
                    $objCheckListUsuario->zona = $nombre_zona; 
                    $objCheckListUsuario->estado=1;
                    $objCheckListUsuario->user_created = $vusuario;
                    $objCheckListUsuario->created_at =date('Y-m-d H:i:s');
                    $objCheckListUsuario->item_nombre = $v[$i]->item_nombre;  
                    $objCheckListUsuario->item_respuesta = $v[$i]->chk_respuesta;
                    $objCheckListUsuario->save();
                    $icontador_respuesta_negativa += ($v[$i]->chk_respuesta ==0 ? 1:0);
                }

                $resultado=array("success"=>true, "msg"=>"Operacion Exitosa.", "respuesta_negativa" =>$icontador_respuesta_negativa);
            }catch(exception $loErr){
                $resultado=array("success"=>false, "msg"=>"Error en el proceso" . $loErr->getMessage());
            }
            //----------------------------------------------------------------------------------------------------//
            //En caso de registrar alguna (almenos 1) respuesta negativa, se envia correo 
            //electronico a quien corresponda
            //----------------------------------------------------------------------------------------------------//

            if($icontador_respuesta_negativa>0){
                //Se Obtiene los arregalos de CC y BCC desde grupo Correos
                
                $objLocales = new clsLocales();
                //$rs_correos_supervisores=$objLocales->mxObtener('SUPERVISOR_X_LOCAL_ID', $local_id);
                $arr_correos_supervisoresCC = $objLocales->mxObtener('SUPERVISOR_X_LOCAL_ID', $local_id);
                $arrBCC = array();
                if(count($arr_correos_supervisoresCC)>0){
                    $respuesta_email = fxSendEmailCheckList($vusuario, $icontador_respuesta_negativa, $arr_correos_supervisoresCC, $arrBCC, $nombre_local);
                }else{
                    $respuesta_email = "Mails no enviados";
                }
                /*
                $objCheckList = new clsCheckList();
                $respuesta_email="";
                //Metodo load siempre carga 1 solo registro, en su defecto el 1ero
                if($arrObjCheckList = $objCheckList->load("estado=1")){
                    $objGrupoCorreoUsuario = new clsGrupoCorreoUsuario();
                    $parametro['grupo_correo_id'] = (int)$objCheckList->grupo_correo_id;
                    $parametro['tipo_receptor'] = 'CC';
                    $arrCC = $objGrupoCorreoUsuario->mxObtener('USUARIOS_X_GRUPO_CORREO_X_RECEPTOR', $parametro);
                    $parametro['tipo_receptor'] = 'BCC';
                    $arrBCC = $objGrupoCorreoUsuario->mxObtener('USUARIOS_X_GRUPO_CORREO_X_RECEPTOR', $parametro);

                    //----------------------------------------------------------------------------------------------------//
                    $respuesta_email = fxSendEmailCheckList($vusuario, $icontador_respuesta_negativa, $arrCC, $arrBCC, $nombre_local);
                    //---------------------------------------------------------------------//
                }else{
                    //no se puede enviar correo x q no hay registro con estado=1 en checklist
                    $respuesta_email = "Mails no enviados";
                }
                */
                $resultado["email"] = $respuesta_email;
            }
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($resultado);
        break;
        case 'AJAX_ELIMINAR_CHECKLIST':
            $id = (int)$_REQUEST['id'];
            $vusuario  = isset($_SESSION['usuario']) ? $_SESSION['usuario']:"";
            $objCheckList = new clsCheckList();
            $objCheckList->load("id=".$id);
            $objCheckList->estado = 0;
            $objCheckList->user_updated =$vusuario;
            $objCheckList->updated_at =date('Y-m-d H:i:s');
            $objCheckList->save();
            $resultado=array("success"=>true, "msg"=>"Eliminacion Exitosa.");
            echo json_encode($resultado);
        break;
        case 'AJAX_OBTENER_CHECKLIST_PIVOT':
            //$anio = (int)$_REQUEST['anio'];
            //$mes  = (int)$_REQUEST['mes'];
            $vusuario  = isset($_SESSION['usuario']) ? $_SESSION['usuario']:"";
            //$anio = 2023;
            //$mes  = 12;
            //$local_id = 1433; //session
            $anio = (int)$_REQUEST['ianio'];
            $mes = (int)$_REQUEST['imes'];
            $local_id = (int)$_REQUEST['local_id'];

            $dias_mes = fxUltimoDia($anio, $mes); //solo cambia cuando es febrero
            $arr_parametro['usuario']=$vusuario;
            $arr_parametro['anio']=$anio;
            $arr_parametro['mes']=$mes;
            $arr_parametro['dias_mes'] = $dias_mes;
            $arr_parametro['local_id'] =$local_id;
            $objCheckList= new clsCheckListUsuario();
            $rs= $objCheckList->mxObtener('CHECKLIST_PIVOT', $arr_parametro);
            $cols = $rs->fieldCount();
            $arr['results']=$rs->GetArray();
            $arr['cols']= $cols;
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($arr);
        break;
        case 'AJAX_OBTENER_LOCALES':
            //se obtiene todos los usuarios con nombres completos
            $objLocales = new clsLocales();
            $rs = $objLocales->mxObtener('LOCALES');
            $arr['results']=$rs->GetArray();
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($arr);
        break;
        case 'AJAX_EXISTE_CHECKLIST':
            //Para comprobar si existe Checklist llenado para actual : dia, usuario, local
            $vusuario  = isset($_SESSION['usuario']) ? $_SESSION['usuario']:"";
            $local_id = isset($_SESSION['local_id']) ? $_SESSION['local_id']:"";
            $fecha = date('Y-m-d');
            $resultado="";
            try{
                $objCheckListUsuario = new clsCheckListUsuario();
                $arr_checklistusuario = $objCheckListUsuario->Find("usuario='$vusuario' and fecha_registro='$fecha' and local_id=$local_id and estado=1");
                if(count($arr_checklistusuario)>0){
                    //existen registros
                    $resultado=array("success"=>true, "cantidad"=>count($arr_checklistusuario));
                }else{
                    //no existen registros
                    //cabe la posiblidad de que sea el caso de TELEVENTAS
                    $objLocales = new clsLocales();
                    $objLocales->Load("id=$local_id");
                    if( (int)$objLocales->red_id == TELEVENTAS_ID){
                        //se trata de una tienda de la red TELEVENTAS, online
                        $resultado=array("success"=>true, "cantidad"=>count($arr_checklistusuario));
                    }else{
                        $resultado=array("success"=>false, "cantidad" => count($arr_checklistusuario));
                    }
                }
            }catch(exception $loErr){
                $resultado=array("success"=>false, "cantidad" => -1, "error"=>"Error en el proceso" . $loErr->getMessage());
            }
            echo json_encode($resultado);
        break;
        case 'AJAX_CORREGIR_CHECKLIST_USUARIO':
            //en caso de que desde reporte se decida cambiar una respuesta negativoa x una corregida
            $id = (int)$_REQUEST['checklistusuario_id'];
            $vusuario  = isset($_SESSION['usuario']) ? $_SESSION['usuario']:"";
            $objCheckListUsuario = new clsCheckListUsuario();
            $objCheckListUsuario->load("id=".$id);
            $objCheckListUsuario->item_respuesta=2;
            $objCheckListUsuario->user_updated =$vusuario;
            $objCheckListUsuario->updated_at =date('Y-m-d H:i:s');
            $objCheckListUsuario->save();
            $resultado=array("success"=>true, "msg"=>"solucionado");
            echo json_encode($resultado);
        break;
        default:
        break;
    }
}            
?>