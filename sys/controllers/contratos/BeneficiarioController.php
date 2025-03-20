<?php 

class BeneficiarioController
{
    public function registrar(){
        try {
            $request = file_get_contents('php://input');
            $request = json_decode($request,true); 

            $model =  new Beneficiario();
            $forma_pago_id = $request["forma_pago_id"];
            if ( $forma_pago_id == '3') {
                $banco_id = "NULL";
                $num_cuenta_bancaria = "NULL";
                $num_cuenta_cci = "NULL";
            } else {
                $banco_id = $request["banco_id"];
                $num_cuenta_bancaria = "'" . $request["num_cuenta_bancaria"] . "'";
                $num_cuenta_cci = "'" . $request["num_cuenta_cci"] . "'";
            }

            if (empty($request["monto"])) {
                $monto = "NULL";
            } else {
                $monto = str_replace(",","",$request["monto"]);
            }

            $request['banco_id'] = $banco_id;
            $request['num_cuenta_bancaria'] = $num_cuenta_bancaria;
            $request['num_cuenta_cci'] = $num_cuenta_cci;
            $request['monto'] = $monto;
      
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
    
            $model =  new Beneficiario();
            $forma_pago_id = $request["forma_pago_id"];
            if ( $forma_pago_id == '3') {
                $banco_id = "NULL";
                $num_cuenta_bancaria = "NULL";
                $num_cuenta_cci = "NULL";
            } else {
                $banco_id = $request["banco_id"];
                $num_cuenta_bancaria = "'" . $request["num_cuenta_bancaria"] . "'";
                $num_cuenta_cci = "'" . $request["num_cuenta_cci"] . "'";
            }

            if (empty($request["monto"])) {
                $monto = "NULL";
            } else {
                $monto = str_replace(",","",$request["monto"]);
            }

            $request['banco_id'] = $banco_id;
            $request['num_cuenta_bancaria'] = $num_cuenta_bancaria;
            $request['num_cuenta_cci'] = $num_cuenta_cci;
            $request['monto'] = $monto;
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
 