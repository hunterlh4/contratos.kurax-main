<?php 

class NotificacionContratoController
{

    public function registrar(){
        try {
            $model =  new NotificacionContrato();
            if (($model->get_usuario_id()) > 0) {
                $request = $_POST;
                $data['usuario_id'] = $request['usuario_id'];
                $data['area_id'] = $request['area_id'];
                $data['id_user_created'] = $model->get_usuario_id();
                $data['created_at'] = date('Y-m-d H:i:s');
                $notificacion_contrato = $model->registrar($data);
                return json_encode($notificacion_contrato,JSON_UNESCAPED_UNICODE);
            }
            $result['status'] = 404; 
            $result['result'] = '';
            $result['message'] = 'Su session ha finalizado, por favor ingrese de nuevo.'; 
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404; 
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';
            
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
    }

    public function listar_notificacion_contrato(){
        try {
            $model =  new NotificacionContrato();
    
            if (($model->get_usuario_id()) > 0) {
                $request = $_POST;
                $notificacion_contrato = $model->listar_notificacion_contrato();
                $counter = 1;
                $transformedData = array_map(function($item) use (&$counter) {

                    $estado = "";
                    $editar = "";
                    $historial = "";
                    $eliminar = "";
                    switch ($item['status']) {
                        case 1:
                            $estado = '<button type="button" class="btn btn-rounded btn-success btn-xs" onclick="sec_contrato_mant_not_cont_cambiar_estado('.$item['id'].',0)">
                                Activo
                            </button>';
                            break;
                        case 0:
                        case 9:
                            $estado = '<button type="button" class="btn btn-rounded btn-warning btn-xs" onclick="sec_contrato_mant_not_cont_cambiar_estado('.$item['id'].',1)">
                                Inactivo
                            </button>';
                            break;
                    }

                    $editar = '<button type="button" class="btn btn-rounded btn-primary btn-xs" title="Editar" onclick="sec_con_mant_not_cont_editar('.$item['id'].')">
                        <i class="fa fa-edit"></i>
                    </button>';

                    $historial = '<button type="button" title="Ver historial" onclick="sec_con_mant_not_cont_ver_historial('.$item['id'].')" class="btn btn-xs btn-warning"><i class="fa fa-history"></i></button>';

                    $eliminar .= '<button type=button" class="btn btn-rounded btn-danger btn-xs" title="Eliminar" onclick="sec_con_mant_not_cont_eliminar_notificacion_por_area_id('.$item['id'].')">
                            <i class="fa fa-trash"></i>												
                        </button>';

                    $acciones = $estado . ' ' . $editar. ' ' . $historial. ' ' . $eliminar;
                    return [
                        $counter++,
                        $item['personal'],
                        $item['area'],
                        $item['creador'],
                        $item['created_at'],
                        $item['modificador'],
                        $item['modified_at'],
                        $acciones
                    ];
                }, $notificacion_contrato['aaData']);
    
                $notificacion_contrato['aaData'] = $transformedData;
    
                return json_encode($notificacion_contrato, JSON_UNESCAPED_UNICODE);
            }
    
            $result['sEcho'] = 1; 
            $result['iTotalRecords'] = 0;
            $result['iTotalDisplayRecords'] = 0;
            $result['aaData'] = [];
            $result['status'] = 404; 
            $result['message'] = 'Su sesión ha finalizado, por favor ingrese de nuevo.'; 
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['sEcho'] = 1; 
            $result['iTotalRecords'] = 0;
            $result['iTotalDisplayRecords'] = 0;
            $result['aaData'] = [];
            $result['status'] = 404; 
            $result['error'] = $th->getMessage();
            $result['message'] = 'Ha ocurrido un error.';
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }

    public function cambiar_estado(){
        try {
    
            $model =  new NotificacionContrato();
            if (($model->get_usuario_id()) > 0) {
                $data['id'] = $_POST['id'];
                $data['estado'] = $_POST['estado'];
                $data['user_updated_id'] = $model->get_usuario_id();
                $data['updated_at'] = date('Y-m-d H:i:s');
                $registro = $model->cambiar_estado($data);
                return json_encode($registro,JSON_UNESCAPED_UNICODE);
            }
            $result['status'] = 404; 
            $result['result'] = '';
            $result['message'] = 'Su session ha finalizado, por favor ingrese de nuevo.'; 
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404; 
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';
            
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
    }


    public function obtener_usuarios(){
        try {
            $request = $_POST; 
            $model =  new NotificacionContrato();
            if (($model->get_usuario_id()) > 0) {

                $data['search'] = $request['search'];
                $registro = $model->obtener_usuarios($data);
                return json_encode($registro,JSON_UNESCAPED_UNICODE);
            }
            $result['status'] = 404; 
            $result['result'] = '';
            $result['message'] = 'Su session ha finalizado, por favor ingrese de nuevo.'; 
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404; 
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';
            
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
    }

    public function obtener_areas(){
        try {
            $request = $_POST; 
            $model =  new NotificacionContrato();
            if (($model->get_usuario_id()) > 0) {
                $data['search'] = $request['search'];
                $registro = $model->obtener_areas($data);
                return json_encode($registro,JSON_UNESCAPED_UNICODE);
            }
            $result['status'] = 404; 
            $result['result'] = '';
            $result['message'] = 'Su session ha finalizado, por favor ingrese de nuevo.'; 
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404; 
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';
            
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
    }
    
    public function obtener_area_por_usuario(){
        try {
            $request = $_POST; 
            $model =  new NotificacionContrato();
            if (($model->get_usuario_id()) > 0) {
                $data['usuario_id'] = $request['usuario_id'];
                $registro = $model->obtener_area_por_usuario($data);
                return json_encode($registro,JSON_UNESCAPED_UNICODE);
            }
            $result['status'] = 404; 
            $result['result'] = '';
            $result['message'] = 'Su session ha finalizado, por favor ingrese de nuevo.'; 
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404; 
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';
            
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
    }

    public function obtener_por_id(){
        try {
            $model = new NotificacionContrato();
            $request = $_POST;
            $id = $request['id'];
            $data = $model->obtener_por_id($id);
    
            if ($data) {
                // Obtener los datos para los select2
                $usuarioData = $model->obtener_usuarios(['search' => '']);
                $areaData = $model->obtener_areas(['search' => '']);
    
                // Encontrar el texto para el usuario y el área seleccionados
                $usuarioText = '';
                foreach ($usuarioData['result'] as $usuario) {
                    if ($usuario['id'] == $data['user_id']) {
                        $usuarioText = $usuario['text'];
                        break;
                    }
                }
    
                $areaText = '';
                foreach ($areaData['result'] as $area) {
                    if ($area['id'] == $data['area_id']) {
                        $areaText = $area['text'];
                        break;
                    }
                }
    
                $result['status'] = 200;
                $result['result'] = [
                    'usuario_id' => $data['usuario_id'],
                    'area_id' => $data['area_id'],
                    'usuario_text' => $usuarioText,
                    'area_text' => $areaText
                ];
            } else {
                $result['status'] = 404;
                $result['message'] = 'No se encontró el registro.';
            }
    
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['message'] = 'Ha ocurrido un error.';
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }

    public function modificar(){
        try {
            $model = new NotificacionContrato();
            $request = $_POST;
    
            $data = [
                'id' => $request['id'],
                'area_id' => $request['area_id'],
                'id_user_updated' => $model->get_usuario_id(),
                'updated_at' => date('Y-m-d H:i:s')
            ];
    
            $result = $model->modificar($data);
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['message'] = 'Ha ocurrido un error.';
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }

    public function listar_historial() {
        try {
            $model =  new NotificacionContrato();
    
            if (($model->get_usuario_id()) > 0) {
                $request = $_POST;
                $data['id'] = $request['parametro_general_id'];
                $parametros_generales = $model->listar_historial([$data]);
    
                $counter = 1;
                $transformedData = array_map(function($item) use (&$counter) {
                    return [
                        $counter++,
                        $item['nuevo_estado'],
                        $item['anterior_estado'],
                        $item['usuario'],
                        $item['created_at']
                    ];
                }, $parametros_generales['aaData']);
    
                $parametros_generales['aaData'] = $transformedData;
    
                return json_encode($parametros_generales, JSON_UNESCAPED_UNICODE);
            }
    
            $result['sEcho'] = 1; 
            $result['iTotalRecords'] = 0;
            $result['iTotalDisplayRecords'] = 0;
            $result['aaData'] = [];
            $result['status'] = 404; 
            $result['message'] = 'Su sesión ha finalizado, por favor ingrese de nuevo.'; 
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['sEcho'] = 1; 
            $result['iTotalRecords'] = 0;
            $result['iTotalDisplayRecords'] = 0;
            $result['aaData'] = [];
            $result['status'] = 404; 
            $result['error'] = $th->getMessage();
            $result['message'] = 'Ha ocurrido un error.';
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }

    public function eliminar_notificacion_por_area_id(){
        try {
    
            $model =  new NotificacionContrato();
            if (($model->get_usuario_id()) > 0) {
                $data['id'] = $_POST['id'];
                $data['user_updated_id'] = $model->get_usuario_id();
                $data['updated_at'] = date('Y-m-d H:i:s');
                $registro = $model->eliminar_notificacion_por_area_id($data);
                return json_encode($registro,JSON_UNESCAPED_UNICODE);
            }
            $result['status'] = 404; 
            $result['result'] = '';
            $result['message'] = 'Su session ha finalizado, por favor ingrese de nuevo.'; 
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404; 
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';
            
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
    }
}
 