<?php 

class MantenimientoCorreoController
{

    public function obtener_usuarios(){
        try {
            $request = $_POST; 
            $model =  new MantenimientoCorreo();
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

    public function obtener_area_grupo_id(){
        try {
            $request = $_POST; 
            $model =  new MantenimientoCorreo();
            if (($model->get_usuario_id()) > 0) {

                $data['search'] = $request['search'];
                $registro = $model->obtener_area_grupo_id($data);
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

    public function listar(){
        try {
    
            $model =  new MantenimientoCorreo();
            if (($model->get_usuario_id()) > 0) {
                $data['metodo_id'] = $_POST['metodo_id'];
                $registro = $model->listar($data);
                $data_result = [];
                $index = 1;
                foreach ($registro['result'] as $be) {
                    $estado = "";
                    $acciones = "";
                    switch ($be['status']) {
                        case 1: $estado = '<button type=button" class="btn btn-rounded btn-success btn-xs" onclick="sec_contrato_mant_corr_modal_cambiar_estado('.$be['id'].',0)">
                                Activo									
                        </button>'; break;
                        case 0: $estado = '<button type=button" class="btn btn-rounded btn-warning btn-xs" onclick="sec_contrato_mant_corr_modal_cambiar_estado('.$be['id'].',1)">
                                Inactivo									
                        </button>'; break;
                    }
                   

                 
                    $acciones .= '<button type=button" class="btn btn-rounded btn-danger btn-xs" title="Eliminar" onclick="sec_contrato_mant_corr_modal_eliminar_por_id('.$be['id'].')">
                                <i class="fa fa-trash"></i>												
                            </button>';
                    
                    array_push($data_result, array(
                        'index' => $index,
                        'id' => $be['id'],
                        'personal' => $be['personal'],
                        'correo' => $be['correo'],
                        'usuario' => $be['usuario'],
                        'status' => $estado,
                        'acciones' => $acciones,
        
                    ));
                    $index++;
                }
                $registro['result'] = $data_result;
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

    public function listar_por_area(){

        try {
            $model =  new MantenimientoCorreo();
    
            if (($model->get_usuario_id()) > 0) {
                $data['metodo_id'] = $_POST['metodo_id'];
                $registro = $model->listar_por_area($data);
                $counter = 1;
                $transformedData = array_map(function($item) use (&$counter) {

                    $estado = "";
                    $acciones = "";
                    switch ($item['status']) {
                        case 1:
                            $estado = '<button type=button" class="btn btn-rounded btn-success btn-xs" onclick="sec_contrato_mant_corr_modal_cambiar_estado_por_area('.$item['id'].',0)">
                                Activo									
                            </button>';
                            break;
                        case 0:
                            $estado = '<button type=button" class="btn btn-rounded btn-warning btn-xs" onclick="sec_contrato_mant_corr_modal_cambiar_estado_por_area('.$item['id'].',1)">
                                Inactivo									
                            </button>';
                            break;
                    }

                    $acciones .= '<button type="button" class="btn btn-rounded btn-primary btn-xs" title="Ver" style="margin-right: 5px;" onclick="sec_con_mant_not_cont_ver_area('.$item['area_id'].')">
                        <i class="fa fa-eye"></i>
                    </button>';
                    
                    $acciones .= '<button type=button" class="btn btn-rounded btn-danger btn-xs" title="Eliminar" onclick="sec_con_mant_not_cont_eliminar_por_area_id('.$item['id'].')">
                                <i class="fa fa-trash"></i>												
                            </button>';
                    
                    return [
                        $counter++,
                        $item['nombre'],
                        $estado,
                        $acciones
                    ];
                }, $registro['aaData']);
    
                $registro['aaData'] = $transformedData;
    
                return json_encode($registro, JSON_UNESCAPED_UNICODE);
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

    public function listar_por_usuarios_por_area(){

        try {
            $model =  new MantenimientoCorreo();
    
            if (($model->get_usuario_id()) > 0) {
                $data['area_id'] = $_POST['area_id'];
                $registro = $model->listar_por_usuarios_por_area($data);
                $area_nombre = count($registro['aaData']) > 0 ? $registro['aaData'][0]['area'] : '';
                $counter = 1;
                $transformedData = array_map(function($item) use (&$counter) {

                    $estado = "";
                    switch ($item['status']) {
                        case 1:
                            $estado = '<span class="badge badge-success">Activo</span>';
                        break;
                        case 0:
                        case 9:
                            $estado = '<span class="badge badge-warning">Inactivo</span>';
                        break;
                    }
                    
                    return [
                        $counter++,
                        $item['personal'],
                        $item['created_at'],
                        $estado
                    ];
                }, $registro['aaData']);
    
                $registro['aaData'] = $transformedData;
                $registro['area'] = $area_nombre;
                return json_encode($registro, JSON_UNESCAPED_UNICODE);
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

    public function eliminar_por_area_id(){
        try {
    
            $model =  new MantenimientoCorreo();
            if (($model->get_usuario_id()) > 0) {
                $data['id'] = $_POST['id'];
                $data['user_updated_id'] = $model->get_usuario_id();
                $data['updated_at'] = date('Y-m-d H:i:s');
                $registro = $model->eliminar_por_area_id($data);
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

    public function registrar(){
        try {
            $request = $_POST; 
            $model =  new MantenimientoCorreo();
            if (($model->get_usuario_id()) > 0) {
                $data['usuario_id'] = $request['usuario_id'];
                $data['metodo_id'] = $request['metodo_id'];
                $data['status'] = $request['status'];
                $data['user_created_id'] = $model->get_usuario_id();
                $data['created_at'] = date('Y-m-d H:i:s');

                $registro = $model->registrar($data);
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
    
    public function registrar_area(){
        try {
            $request = $_POST; 
            $model =  new MantenimientoCorreo();
            if (($model->get_usuario_id()) > 0) {
                $data['area_id'] = $request['area_id'];
                $data['metodo_id'] = $request['metodo_id'];
                $data['status'] = $request['status'];
                $data['user_created_id'] = $model->get_usuario_id();
                $data['created_at'] = date('Y-m-d H:i:s');

                $registro = $model->registrar_area($data);
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

    public function cambiar_estado(){
        try {
    
            $model =  new MantenimientoCorreo();
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

    public function cambiar_estado_por_area(){
        try {
    
            $model =  new MantenimientoCorreo();
            if (($model->get_usuario_id()) > 0) {
                $data['id'] = $_POST['id'];
                $data['estado'] = $_POST['estado'];
                $data['user_updated_id'] = $model->get_usuario_id();
                $data['updated_at'] = date('Y-m-d H:i:s');
                $registro = $model->cambiar_estado_por_area($data);
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

    public function eliminar_por_id(){
        try {
    
            $model =  new MantenimientoCorreo();
            if (($model->get_usuario_id()) > 0) {
                $data['id'] = $_POST['id'];
                $data['user_updated_id'] = $model->get_usuario_id();
                $data['updated_at'] = date('Y-m-d H:i:s');
                $registro = $model->eliminar_por_id($data);
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
 