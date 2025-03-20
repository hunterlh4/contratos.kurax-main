<?php 

class HerramientasTIMantenimientoController
{

 
    public function obtener_modulos(){
        try {
            
            $model =  new HerrramientasTIMantenimiento();
            if (($model->get_usuario_id()) > 0) {
                $result_tablas = $model->obtener_modulos([]);
                return json_encode($result_tablas,JSON_UNESCAPED_UNICODE);
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

    public function obtener_tablas(){
        try {
            
            $model =  new HerrramientasTIMantenimiento();
            if (($model->get_usuario_id()) > 0) {
                $result_tablas = $model->obtener_tablas([]);
                return json_encode($result_tablas,JSON_UNESCAPED_UNICODE);
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

    public function obtener_columnas_de_tabla(){
        try {
            
            $model =  new HerrramientasTIMantenimiento();
            if (($model->get_usuario_id()) > 0) {
                $data = $_POST;
                $result_columnas = $model->obtener_columnas_de_tabla($data);
                return json_encode($result_columnas,JSON_UNESCAPED_UNICODE);
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

    public function registrar_proceso(){
        try {
    
            $model =  new HerrramientasTIMantenimiento();
            if (($model->get_usuario_id()) > 0) {
                $request = $_POST;
                $request['modulo_id'] = $request['modulo_id'];
                $request['nombre'] = $model->replace_invalid_caracters($request['nombre']);
                $request['tabla'] = $request['entidad'];
                $request['status'] = 1;
                $request['user_created_id'] = $model->get_usuario_id();
                $request['created_at'] = date('Y-m-d H:i:s');

                $paremtros_generales = $model->registrar_proceso($request);
                return json_encode($paremtros_generales,JSON_UNESCAPED_UNICODE);
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

    public function listar_procesos(){
        try {
            
            $model =  new HerrramientasTIMantenimiento();
            if (($model->get_usuario_id()) > 0) {
                $menu['sec_id'] = 'herramientas_ti';
                $menu['sub_sec_id'] = 'mantenimiento';
                $data_menu = $model->get_menu_id($menu);
                $usuario_permisos = $model->get_usuario_permisos();


                $result_proceso = $model->listar_procesos([]);
                $result = [];
                
                for ($i=0; $i < count($result_proceso['result']) ; $i++) { 
                    $proceso = $result_proceso['result'][$i];
                    $accion = '';
                    if (array_key_exists($data_menu['result'], $usuario_permisos) && in_array("edit", $usuario_permisos[$data_menu['result']])) {
                        $accion .= '<button type="button" title="Editar" onclick="sec_herramientas_ti_mantenimiento_modal_proceso('.$proceso['id'].',\'editar\')" class="btn btn-xs btn-warning mbt"><i class="fa fa-pencil"></i></button>';
                    }

                    $accion .= '<button type="button" title="Definir Columnas" onclick="sec_herramientas_ti_mantenimiento_modal_definir_columnas('.$proceso['id'].',\''.$proceso['tabla'].'\')" class="btn btn-xs btn-primary mbt"><i class="icon fa fa-fw fa-dedent"></i></button>';

                    if (array_key_exists($data_menu['result'], $usuario_permisos) && in_array("delete", $usuario_permisos[$data_menu['result']])) {
                        $accion .= '<button type="button" title="Eliminar" onclick="sec_herramientas_ti_mantenimiento_eliminar_proceso('.$proceso['id'].')" class="btn btn-xs btn-danger mbt"><i class="fa fa-trash"></i></button>';
                    }
                
                    $proceso['acciones'] = $accion;
                    $estado = '';
                    switch ($proceso['status']) {
                        case 1: $estado = '<span class="badge bg-success">Activo</span>'; break;
                        case 0: $estado = '<span class="badge bg-danger">Inactivo</span>'; break;
                    }
                    $proceso['estado'] = $estado;
                    array_push($result, $proceso);
                }
                $result_proceso['result'] = $result;

                return json_encode($result_proceso,JSON_UNESCAPED_UNICODE);
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

    public function obtener_proceso_por_id(){
        try {
            
            $model =  new HerrramientasTIMantenimiento();
            if (($model->get_usuario_id()) > 0) {
                $data = $_POST;
                $data['id'] = $data['proceso_id'];
                $result_proceso = $model->obtener_proceso_por_id($data);
                return json_encode($result_proceso,JSON_UNESCAPED_UNICODE);
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

    public function editar_proceso(){
        try {
    
            $model =  new HerrramientasTIMantenimiento();
            if (($model->get_usuario_id()) > 0) {
                $request = $_POST;
                $request['id'] = $request['id'];
                $request['modulo_id'] = $request['modulo_id'];
                $request['nombre'] = $model->replace_invalid_caracters($request['nombre']);
                $request['tabla'] = $request['tabla'];
                $request['status'] = $request['status'];
                $request['user_updated_id'] = $model->get_usuario_id();
                $request['updated_at'] = date('Y-m-d H:i:s');

                $paremtros_generales = $model->editar_proceso($request);
                return json_encode($paremtros_generales,JSON_UNESCAPED_UNICODE);
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

    public function eliminar_proceso(){
        try {
    
            $model =  new HerrramientasTIMantenimiento();
            if (($model->get_usuario_id()) > 0) {
                $request = $_POST;
                $request['id'] = $request['id'];
                $request['status'] = 9;
                $request['user_updated_id'] = $model->get_usuario_id();
                $request['updated_at'] = date('Y-m-d H:i:s');
                $paremtros_generales = $model->eliminar_proceso($request);
                return json_encode($paremtros_generales,JSON_UNESCAPED_UNICODE);
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

    public function registrar_proceso_detalle(){
        try {
    
            $model =  new HerrramientasTIMantenimiento();
            if (($model->get_usuario_id()) > 0) {
                $data = $_POST;
                $data['status'] = 1;
                $data['user_created_id'] = $model->get_usuario_id();
                $data['created_at'] = date('Y-m-d H:i:s');
                $result_proceso_detalle = $model->registrar_proceso_detalle($data);
                return json_encode($result_proceso_detalle,JSON_UNESCAPED_UNICODE);
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


    public function listar_procesos_detalle(){
        try {
            
            $model =  new HerrramientasTIMantenimiento();
            if (($model->get_usuario_id()) > 0) {
                $data = $_POST;

                $menu['sec_id'] = 'herramientas_ti';
                $menu['sub_sec_id'] = 'mantenimiento';
                $data_menu = $model->get_menu_id($menu);
                $usuario_permisos = $model->get_usuario_permisos();

                $data['id'] = $data['proceso_id'];
                $data_proceso = $model->obtener_proceso_por_id($data);
                $result_proceso_detalle = $model->listar_procesos_detalle($data);
                $result = [];
                if ($result_proceso_detalle['status'] == 200) {
                    for ($i=0; $i < count($result_proceso_detalle['result']) ; $i++) { 
                        $proceso = $result_proceso_detalle['result'][$i];
                        $accion = '';
                        if (array_key_exists($data_menu['result'], $usuario_permisos) && in_array("delete", $usuario_permisos[$data_menu['result']])) {
                            $accion .= '<button type="button" onclick="sec_herramientas_ti_mantenimiento_eliminar_procesos_detalle('.$proceso['id'].')" class="btn btn-xs btn-danger"><i class="fa fa-trash"></i></button>';
                        }
                        $proceso['acciones'] = $accion;
                        $estado = '';
                        switch ($proceso['status']) {
                            case 1: $estado = '<button type="button" proceso_detalle_id="'.$proceso['id'].'" estado="'.$proceso['status'].'" class="btn_state_proceso_detalle btn btn-info btn-xs">Activo</button>'; break;
                            case 0: $estado = '<button type="button" proceso_detalle_id="'.$proceso['id'].'" estado="'.$proceso['status'].'" class="btn_state_proceso_detalle btn btn-warning btn-xs">Inactivo</button>'; break;
                        }
                        $proceso['estado'] = $estado;
                        array_push($result, $proceso);
                    }
                    $result_proceso_detalle['result'] = [];
                    $result_proceso_detalle['result']['proceso'] = $data_proceso['result'];
                    $result_proceso_detalle['result']['proceso_detalle'] = $result;

                }

                return json_encode($result_proceso_detalle,JSON_UNESCAPED_UNICODE);
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

    public function actualizar_estado_proceso_detalle(){
        try {
    
            $model =  new HerrramientasTIMantenimiento();
            if (($model->get_usuario_id()) > 0) {
                $data = $_POST;
                $data['id'] = $data['proceso_detalle_id'];
                $data['status'] = (int) $data['estado'] == 1 ? 0:1;
                $data['user_updated_id'] = $model->get_usuario_id();
                $data['updated_at'] = date('Y-m-d H:i:s');
                $result_proceso_detalle = $model->actualizar_estado_proceso_detalle($data);
                return json_encode($result_proceso_detalle,JSON_UNESCAPED_UNICODE);
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


    public function eliminar_procesos_detalle(){
        try {
    
            $model =  new HerrramientasTIMantenimiento();
            if (($model->get_usuario_id()) > 0) {
                $data = $_POST;
                $data['id'] = $data['proceso_detalle_id'];
                $data['status'] = 9;
                $data['user_updated_id'] = $model->get_usuario_id();
                $data['updated_at'] = date('Y-m-d H:i:s');
                $result_proceso_detalle = $model->actualizar_estado_proceso_detalle($data);
                return json_encode($result_proceso_detalle,JSON_UNESCAPED_UNICODE);
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



    public function registrar_parametro_general(){
        try {
    
            $model =  new ParametrosGenerales();
            if (($model->get_usuario_id()) > 0) {
                $request = $_POST;
                $data['descripcion'] = $request['descripcion'];
                $data['codigo'] = $request['codigo'];
                $data['ticket'] = $request['ticket'];
                $data['valor'] = $request['valor'];
                $data['estado'] = $request['estado'];
                $data['id_user_created'] = $model->get_usuario_id();
                $data['created_at'] = date('Y-m-d H:i:s');
                $paremtros_generales = $model->registrar($data);
                return json_encode($paremtros_generales,JSON_UNESCAPED_UNICODE);
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
 