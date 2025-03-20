<?php 

class CierreEfectivoDenominacionController
{

    public function obtener_cierre_denominaciones_por_tipo(){
        try {
            $request = $_POST; 
            $model =  new CierreEfectivoDenominacion();
            if (($model->get_usuario_id()) > 0) {
                $data['tipo_id'] = 1;
                $registro = $model->obtener_cierre_denominaciones_por_tipo($data);
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
 