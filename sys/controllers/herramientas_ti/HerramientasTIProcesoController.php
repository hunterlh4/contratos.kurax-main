<?php 

class HerramientasTIProcesoController
{

 
    public function obtener_proceso_detalle(){
        try {
            
            $model =  new HerrramientasTIProceso();
            if (($model->get_usuario_id()) > 0) {
                $data = $_POST;

                $result_tablas = $model->obtener_proceso_detalle($data);
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



    public function buscar_reporte(){
        try {
            
            $model =  new HerrramientasTIProceso();
            if (($model->get_usuario_id()) > 0) {
                $data = $_POST;
                $result_reporte = $model->buscar_reporte($data);
                return json_encode($result_reporte,JSON_UNESCAPED_UNICODE);
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
 