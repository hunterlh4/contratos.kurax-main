<?php 

class ModificacionesController
{

 
    public function listar(){
        try {
            
            $model =  new Modificaciones();

            $menu['sec_id'] = 'adm_versiones';
            $menu['sub_sec_id'] = 'versiones';
            $data_menu = $model->get_menu_id($menu);
            $usuario_permisos = $model->get_usuario_permisos();

            if (($model->get_usuario_id()) > 0) {
                $modificaciones = $model->listar_parametros_generales([]);
                $result = $modificaciones['result'];
                for ($i=0; $i < count($result); $i++) { 
                    $accion = '';
                    $accion .= '<button type="button" onclick="sec_adm_modal_ver_modificaciones('.$result[$i]['id'].')" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i></button>';
                    if (array_key_exists($data_menu['result'], $usuario_permisos) && in_array("edit", $usuario_permisos[$data_menu['result']])) {
                        $accion .= '<button type="button" onclick="sec_adm_modal_editar_modificaciones('.$result[$i]['id'].')" style="margin-left: 4px;" class="btn btn-xs btn-warning"><i class="fa fa-pencil"></i></button>';
                    }
                    $result[$i]['acciones'] = $accion;
                    switch ($result[$i]['status']) {
                        case 1: $estado = '<span class="badge bg-success">Activo</span>'; break;
                        case 0: $estado = '<span class="badge bg-danger">Inactivo</span>'; break;
                    }
                    $result[$i]['estado'] = $estado;
                }
             $modificaciones['result'] = $result;
                return json_encode($modificaciones,JSON_UNESCAPED_UNICODE);
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
    
            $model =  new Modificaciones();
            if (($model->get_usuario_id()) > 0) {
                $request = $_POST;
                $data['id'] = $request['id'];
                $modificacion = $model->obtener_pod_id($data);
                return json_encode($modificacion,JSON_UNESCAPED_UNICODE);
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
    
            $model =  new Modificaciones();
            if (($model->get_usuario_id()) > 0) {
                $request = $_POST;
                $data['id'] = $request['id'];
                $data['modulo'] = $model->replace_invalid_caracters($request['modulo']);
                $data['version'] = $request['version'];
                $data['comment'] = $model->replace_invalid_caracters($request['descripcion']);
                $data['updated_at'] = $request['fecha_modificacion'];
                $data['status'] = $request['estado'];
                $data['user_updated_id'] = $model->get_usuario_id();
                $modificacion = $model->modificar($data);
                return json_encode($modificacion,JSON_UNESCAPED_UNICODE);
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
    
            $model =  new Modificaciones();
            if (($model->get_usuario_id()) > 0) {
                $request = $_POST;
                $data['modulo'] = $model->replace_invalid_caracters($request['modulo']);
                $data['version'] = $request['version'];
                $data['comment'] = $model->replace_invalid_caracters($request['descripcion']);
                $data['status'] = 1;
                $data['user_updated_id'] = $model->get_usuario_id();
                $data['updated_at'] = date('Y-m-d H:i:s');
                $data['user_created_id'] = $model->get_usuario_id();
                $data['created_at'] = date('Y-m-d H:i:s');
                $modificacion = $model->registrar($data);
                return json_encode($modificacion,JSON_UNESCAPED_UNICODE);
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
 