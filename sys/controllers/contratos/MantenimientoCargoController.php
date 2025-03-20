<?php 

class MantenimientoCargoController
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

    public function obtener_cargos(){
        try {
            $request = $_POST; 
            $model =  new MantenimientoCargo();
            if (($model->get_usuario_id()) > 0) {

                $data['area_id'] = $request['area_id'];
                $registro = $model->obtener_cargos($data);
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
    
            $model =  new MantenimientoCargo();
            if (($model->get_usuario_id()) > 0) {
                $data['metodo_id'] = $_POST['metodo_id'];
                $registro = $model->listar($data);
                $data_result = [];
                $index = 1;
                foreach ($registro['result'] as $be) {
                    $estado = "";
                    $acciones = "";
                    switch ($be['estado']) {
                        case 1: $estado = '<button type=button" class="btn btn-rounded btn-success btn-xs" onclick="sec_contrato_mant_corr_modal_cambiar_estado_cargo('.$be['id'].',0)">
                                Activo									
                        </button>'; break;
                        case 0: $estado = '<button type=button" class="btn btn-rounded btn-warning btn-xs" onclick="sec_contrato_mant_corr_modal_cambiar_estado_cargo('.$be['id'].',1)">
                                Inactivo									
                        </button>'; break;
                    }
                   

                 
                    $acciones .= '
                    <button type="button" class="btn btn-danger btn-xs mr-2" style="margin-right: 10px" onclick="sec_contrato_mant_corr_modal_historial('.$be['id'].')">
                        <i class="fa fa-trash"></i>
                    </button>';
    
 
    
    
                
                
                    
                    array_push($data_result, array(
                        'index' => $index,
                        'id' => $be['id'],
                        // 'personal' => $be['personal'],
                        // 'correo' => $be['correo'],
                        // 'usuario' => $be['usuario'],
                        'cargo' => $be['cargo_nombre'],
                        'usuario' => $be['usuario'],
                        'fecha_creacion' => $be['created_at'],
                        // 'area' => $be['area'],
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

    public function listar_historial(){
        try {
    
            $model =  new MantenimientoCargo();
            if (($model->get_usuario_id()) > 0) {
                $data['id_area'] = $_POST['id_area'];
                $registro = $model->listar_historial($data);
                $data_result = [];
                $index = 1;
                foreach ($registro['result'] as $be) {
                    $estado = "";
                    $acciones = "";
                    switch ($be['estado']) {
                        case 1: $estado = '<button type=button" class="btn btn-rounded btn-success btn-xs" onclick="sec_contrato_mant_corr_modal_cambiar_estado_cargo('.$be['id'].',0)">
                                Activo									
                        </button>'; break;
                        case 0: $estado = '<button type=button" class="btn btn-rounded btn-warning btn-xs" onclick="sec_contrato_mant_corr_modal_cambiar_estado_cargo('.$be['id'].',1)">
                                Inactivo									
                        </button>'; break;
                    }
                   

                 
                   
    
 
    
    
                
                
                    
                    array_push($data_result, array(
                        'index' => $index,
                        'id' => $be['id'],
                        // 'personal' => $be['personal'],
                        // 'correo' => $be['correo'],
                        // 'usuario' => $be['usuario'],
                        'cargo' => $be['cargo_nombre'],
                        'usuario' => $be['usuario_name'],
                        'fecha_creacion' => $be['fecha_actualizacion'],
                        // 'area' => $be['area'],
                        'estado_historial' => $be['estado_historial'],
        
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
    public function registrar(){
        try {
            $request = $_POST; 
            $model =  new MantenimientoCargo();
            if (($model->get_usuario_id()) > 0) {
                // $data['usuario_id'] = $request['usuario_id'];
                // $data['metodo_id'] = $request['metodo_id'];
                $data['status'] = $request['status'];
                $data['cargo_id'] = $request['cargo_id'];
                $data['area_id'] = $request['area_id'];
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

    public function cambiar_estado(){
        try {
    
            $model =  new MantenimientoCargo();
            if (($model->get_usuario_id()) > 0) {
                $data['id'] = $_POST['id'];
                $data['metodo_id'] = $_POST['area_id'];
                $data['estado'] = $_POST['estado'];
                $data['user_updated_id'] = $model->get_usuario_id();
                $data['updated_at'] = date('Y-m-d H:i:s');
                $registro = $model->cambiar_estado($data);
                if($registro['status']==200){
                        $listar_cargos = $model->listar($data);
                    // var_dump($listar_cargos);exit();

                        $data_result = [];
                    $index = 1;
                    foreach ($listar_cargos['result'] as $be) {
                        $estado = "";
                        $acciones = "";
                        switch ($be['estado']) {
                            case 1: $estado = '<button type=button" class="btn btn-rounded btn-success btn-xs" onclick="sec_contrato_mant_corr_modal_cambiar_estado_cargo('.$be['id'].',0)">
                                    Activo									
                            </button>'; break;
                            case 0: $estado = '<button type=button" class="btn btn-rounded btn-warning btn-xs" onclick="sec_contrato_mant_corr_modal_cambiar_estado_cargo('.$be['id'].',1)">
                                    Inactivo									
                            </button>'; break;
                        }
                        $acciones .= '
                        <button type="button" class="btn btn-danger btn-xs mr-2" style="margin-right: 10px" onclick="sec_contrato_mant_corr_modal_historial('.$be['id'].')">
                            <i class="fa fa-trash"></i>
                        </button>';
                        array_push($data_result, array(
                            'index' => $index,
                            'id' => $be['id'],
                            'cargo' => $be['cargo_nombre'],
                            'usuario' => $be['usuario'],
                            'fecha_creacion' => $be['created_at'],
                            'status' => $estado,
                            'acciones' => $acciones,
            
                        ));
                        $index++;
                    }
                    $registro['result'] = $data_result;

                }
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

    public function listar_personal_area_cargo(){
        try {
    
            $model =  new MantenimientoCargo();
            if (($model->get_usuario_id()) > 0) {
                $data['area_id'] = $_POST['area_id'];
                $modal_cargo_metodo = new MantenimientoCargoMetodo();
                $data_area = $modal_cargo_metodo->obtener_por_id(['id' => $data['area_id']]);
                $registro = $model->listar_personal_area_cargo($data);
                $data_result = [];
                $index = 1;
                foreach ($registro['result'] as $be) {
                    array_push($data_result, $be);
                    $index++;
                }
                $registro['result'] = [
                    'area' => $data_area['result'],
                    'personal' => $data_result,
                ];
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


    public function registrar_correo_area(){
        try {
            $request = $_POST; 
            $model =  new MantenimientoCargo();
            if (($model->get_usuario_id()) > 0) {
                $data['usuario_id'] = $request['usuario_id'];
                $data['area_id'] = $request['area_id'];
                $data['status'] = 1;
                $data['user_created_id'] = $model->get_usuario_id();
                $data['created_at'] = date('Y-m-d H:i:s');
                $registro = $model->registrar_correo_area($data);
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

    public function listar_correo_area(){
        try {
            $request = $_POST; 
            $model =  new MantenimientoCargo();
            if (($model->get_usuario_id()) > 0) {
                $data['area_id'] = $request['area_id'];
                $registro = $model->listar_correo_area($data);
                $result = [];
                foreach ($registro['result'] as $be) {
                    $response = $be;
                    $estado = "";
                    $acciones = "";
                    switch ($be['status']) {
                        case 1: $estado = '<button type=button" class="btn btn-rounded btn-success btn-xs" onclick="sec_contrato_mant_corr_area_cambiar_estado('.$be['id'].',0)">
                                Activo									
                        </button>'; break;
                        case 0: $estado = '<button type=button" class="btn btn-rounded btn-warning btn-xs" onclick="sec_contrato_mant_corr_area_cambiar_estado('.$be['id'].',1)">
                                Inactivo									
                        </button>'; break;
                    }
                   

                 
                    $acciones .= '
                    <button type="button" class="btn btn-danger btn-xs mr-2" style="margin-right: 10px" onclick="sec_contrato_mant_corr_area_eliminar('.$be['id'].')">
                        <i class="fa fa-trash"></i>
                    </button>';
                    $response['estado'] = $estado;
                    $response['acciones'] = $acciones;

                    array_push($result, $response);
                }
                $registro['result'] = $result;
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

    public function eliminar_correo_area(){
        try {
            $request = $_POST; 
            $model =  new MantenimientoCargo();
            if (($model->get_usuario_id()) > 0) {
                $data['correo_id'] = $request['correo_id'];
                $data['status'] = 9;
                $data['user_updated_id'] = $model->get_usuario_id();
                $data['updated_at'] = date('Y-m-d H:i:s');
                $registro = $model->modificar_correo_area($data);
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

    public function cambiar_estado_correo_area(){
        try {
            $request = $_POST; 
            $model =  new MantenimientoCargo();
            if (($model->get_usuario_id()) > 0) {
                $data['correo_id'] = $request['correo_id'];
                $data['status'] = $request['status'];
                $data['user_updated_id'] = $model->get_usuario_id();
                $data['updated_at'] = date('Y-m-d H:i:s');
                $registro = $model->modificar_correo_area($data);
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
 
