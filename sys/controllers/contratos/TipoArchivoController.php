<?php 

class TipoArchivoController
{
    public function registrar(){
        try {
            $request = file_get_contents('php://input');
            $request = json_decode($request,true); 

            $model =  new TipoArchivo();
           
            $request['created_at'] = date('Y-m-d H:i:s');
            $registro = $model->registrar($request);
            return json_encode($registro,JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';
            
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
    }


    public function modificar(){
        try {

            $request = file_get_contents('php://input');
            $request = json_decode($request,true); 
    
            $model =  new TipoArchivo();
            $request['updated_at'] = date('Y-m-d H:i:s');
            $registro = $model->modificar($request);
            return json_encode($registro,JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';
            
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
    }

}
 