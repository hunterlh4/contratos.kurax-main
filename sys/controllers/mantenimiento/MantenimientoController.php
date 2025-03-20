<?php 

class MantenimientoController
{

 
    public function listar_parametros_generales(){
        try {
            
            $model =  new ParametrosGenerales();

            $menu['sec_id'] = 'adm_parametros_generales';
            $menu['sub_sec_id'] = 'parametros_generales';
            $data_menu = $model->get_menu_id($menu);
            $usuario_permisos = $model->get_usuario_permisos();

            if (($model->get_usuario_id()) > 0) {
                $paremtros_generales = $model->listar_parametros_generales([]);
                $result = $paremtros_generales['result'];
                for ($i=0; $i < count($result); $i++) { 
                    $accion = '';
                    $accion .= '<div class="button-group cl_gap">';
                    $accion .= '<button type="button" title="Ver" onclick="sec_adm_modal_ver_parametros_generales('.$result[$i]['id'].')" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i></button>';
                    if (array_key_exists($data_menu['result'], $usuario_permisos) && in_array("edit", $usuario_permisos[$data_menu['result']])) {
                        $accion .= '<button type="button" title="Editar" onclick="sec_adm_modal_editar_parametros_generales('.$result[$i]['id'].')" class="btn btn-xs btn-warning"><i class="fa fa-pencil"></i></button>';
                    }
                    $accion .= '<button type="button" title="Ver historial" onclick="sec_adm_modal_ver_historial_parametros_generales('.$result[$i]['id'].')" class="btn btn-xs btn-warning"><i class="fa fa-history"></i></button>';
                    $accion .= '</div>';
                    $result[$i]['acciones'] = $accion;
                    switch ($result[$i]['estado']) {
                        case 1: $estado = '<span class="badge bg-success">Activo</span>'; break;
                        case 0: $estado = '<span class="badge bg-danger">Inactivo</span>'; break;
                    }

                    $result[$i]['valor'] = strlen($result[$i]['valor']) < 30 ? $result[$i]['valor'] : substr ( $result[$i]['valor'], 0, 30)."...";
                    $result[$i]['estado'] = $estado;
                    $result[$i]['fecha'] = !Empty($result[$i]['updated_at']) ? $result[$i]['updated_at'] : $result[$i]['created_at'];
                }
             $paremtros_generales['result'] = $result;
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

    public function obtener_por_id(){
        try {
    
            $model =  new ParametrosGenerales();
            if (($model->get_usuario_id()) > 0) {
                $request = $_POST;
                $data['id'] = $request['id'];
                $paremtros_generales = $model->obtener_pod_id($data);
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


    public function modificar_parametro_general(){
        try {
    
            $model =  new ParametrosGenerales();
            if (($model->get_usuario_id()) > 0) {
                $request = $_POST;
                $data['id'] = $request['id'];
                $data['descripcion'] = $request['descripcion'];
                $data['codigo'] = $request['codigo'];
                $data['valor'] = $request['valor'];
                $data['ticket'] = $request['ticket'];
                $data['estado'] = $request['estado'];
                $data['id_user_updated'] = $model->get_usuario_id();
                $data['updated_at'] = date('Y-m-d H:i:s');
                $paremtros_generales = $model->modificar($data);
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

    public function listar_historial_parametros_generales() {
        try {
            $model =  new ParametrosGenerales();
    
            if (($model->get_usuario_id()) > 0) {
                $request = $_POST;
                $data['id'] = $request['parametro_general_id'];
                $parametros_generales = $model->listar_historial_parametros_generales([$data]);
    
                $counter = 1;
                $transformedData = array_map(function($item) use (&$counter) {
                    return [
                        $counter++,
                        $item['tbl_parametros_generales_id'],
                        $item['valor_anterior'],
                        $item['valor_nuevo'],
                        $item['created_at'],
                        $item['usuario_creador'],
                        $item['updated_at'],
                        $item['usuario_modificador']
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
 

}
 