<?php 

class PrestamoController
{

    public function listar_prestamos_parametros(){
        try {
            $request = $_POST; 
            $model =  new PrestamoParametro();
            if (($model->get_usuario_id()) > 0) {
                $registro = $model->listar_prestamos_parametros([]);
                $result = $registro['result'];
                for ($i=0; $i < count($result); $i++) { 
                    $accion = '';
                    $accion .= '<button type="button" onclick="sec_prestamo_conf_show_modal_editar('.$result[$i]['id'].')" class="btn btn-sm btn-warning"><i class="glyphicon glyphicon-pencil"></i></button>';
                    $result[$i]['acciones'] = $accion;
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

    public function registrar_prestamo_parametro(){
        try {
            $request = $_POST; 
            $model =  new PrestamoParametro();
            if (($model->get_usuario_id()) > 0) {
                $request['user_created_id'] = $model->get_usuario_id();
                $request['created_at'] = date('Y-m-d H:i:s');
                $registro = $model->registrar_prestamo_parametro($request);
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

    public function obtener_prestamo_parametro(){
        try {
            $request = $_POST; 
            $model =  new PrestamoParametro();
            if (($model->get_usuario_id()) > 0) {
                $registro = $model->obtener_prestamo_parametro($request);
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

    public function editar_prestamo_parametro(){
        try {
            $request = $_POST; 
            $model =  new PrestamoParametro();
            if (($model->get_usuario_id()) > 0) {
                $request['user_updated_id'] = $model->get_usuario_id();
                $request['updated_at'] = date('Y-m-d H:i:s');
                $registro = $model->editar_prestamo_parametro($request);
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
 