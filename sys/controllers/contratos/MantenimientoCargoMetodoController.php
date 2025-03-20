<?php 

class MantenimientoCargoMetodoController
{

    public function listar_areas(){
        try {
    
            $model =  new MantenimientoCargoMetodo();
            if (($model->get_usuario_id()) > 0) {
                // $edicion_metodo_correo = $_POST['edicion_metodo_correo'];
                $registro = $model->listar_areas([]);
                $data_result = [];
                $index = 1;
                foreach ($registro['result'] as $be) {
                    $estado = "";
                    $acciones = "";
             
                    $acciones .= '<div class="text-center">
                                <button type=button" class="btn btn-rounded btn-info btn-xs" style="margin-right: 10px" title="Ver"  onclick="sec_contrato_mant_met_modal_correos_cargos('.$be['id'].')">
                                    <i class="fa fa-eye"></i>												
                                </button>';

                    $acciones .= '<button type=button" class="btn btn-rounded btn-warning btn-xs" style="margin-right: 10px"  title="Ver Personal Activo" onclick="sec_contrato_mant_met_modal_personal_area_cargo('.$be['id'].')">
                                    <i class="fa fa-file"></i>												
                                </button>';
                    
                    $acciones .= '<button type=button" class="btn btn-rounded btn-primary btn-xs" style="margin-right: 10px" title="Ver Historial de Cambios" onclick="sec_contrato_mant_met_modal_correos_cargos_historial('.$be['id'].')">
                                        Historial												
                                </button></div>';

                    array_push($data_result, array(
                        'index' => $index,
                        'id' => $be['id'],
                        'nombre' => $be['nombre'],
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
    public function listar(){
        try {
    
            $model =  new MantenimientoCorreoMetodo();
            if (($model->get_usuario_id()) > 0) {
                $edicion_metodo_correo = $_POST['edicion_metodo_correo'];
                $registro = $model->listar([]);
                $data_result = [];
                $index = 1;
                foreach ($registro['result'] as $be) {
                    $estado = "";
                    $acciones = "";
                    switch ($be['status']) {
                        case 1: $estado = '<span class="badge bg-success">Activo</span>'; break;
                        case 0: $estado = '<span class="badge bg-danger">Inactivo</span>'; break;
                    }

                    $acciones .= '<button type=button" class="btn btn-rounded btn-info btn-xs" onclick="sec_contrato_mant_met_modal_correos_cargos('.$be['id'].')">
                    <i class="fa fa-eye"></i>												
                </button>';
                    if ($edicion_metodo_correo == 1) { // funciona con permisos
                        $acciones .= '<button type=button" class="btn btn-rounded btn-primary btn-xs" onclick="sec_contrato_mant_met_obtener_por_id('.$be['id'].')">
                                        <i class="fa fa-pencil"></i>												
                                    </button>';
                        $acciones .= '<button type=button" class="btn btn-rounded btn-danger btn-xs" onclick="sec_contrato_mant_met_eliminar_por_id('.$be['id'].')">
                                    <i class="fa fa-trash"></i>												
                                </button>';
                    }
                    
                    
                    array_push($data_result, array(
                        'index' => $index,
                        'id' => $be['id'],
                        'tipo_contrato' => $be['tipo_contrato'],
                        'nombre' => $be['nombre'],
                        'metodo' => $be['metodo'],
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
   
    public function obtener_por_id(){
        try {
    
            $model =  new MantenimientoCargoMetodo();
            if (($model->get_usuario_id()) > 0) {
                $data['id'] = $_POST['id'];
                $registro = $model->obtener_por_id($data);
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
            $model =  new MantenimientoCorreoMetodo();
            if (($model->get_usuario_id()) > 0) {

                $data['tipo_metodo_id'] = $request['tipo_metodo_id'];
                $data['nombre'] = $request['nombre'];
                $data['metodo'] = $request['metodo'];
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

    public function modificar(){
        try {

            $request = $_POST; 
            $model =  new MantenimientoCorreoMetodo();
            if (($model->get_usuario_id()) > 0) {

                $data['id'] = $request['id'];
                $data['tipo_metodo_id'] = $request['tipo_metodo_id'];
                $data['nombre'] = $request['nombre'];
                $data['metodo'] = $request['metodo'];
                $data['status'] = $request['status'];
                $data['user_updated_id'] = $model->get_usuario_id();
                $data['updated_at'] = date('Y-m-d H:i:s');

                $registro = $model->modificar($data);
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
    
            $model =  new MantenimientoCorreoMetodo();
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
 