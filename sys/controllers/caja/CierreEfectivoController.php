<?php 

class CierreEfectivoController
{

    public function obtener_cierre_efectivo_por_caja_id(){
        try {
            $request = $_POST; 
            $model =  new CierreEfectivo();
            if (($model->get_usuario_id()) > 0) {
                $created_at = date('Y-m-d H:i:s');
                $data['caja_id'] = $request['caja_id'];

                $registro = $model->validar_cierre_efectivo_por_caja($data);
                if (count($registro['result']) > 0) { //cuando ya existe un cierre de efectivo en la caja
                    $data_cierre_caja = $registro['result'][0];
                    $cierre_caja = $model->obtener_por_id($data_cierre_caja['caja_id']);
                    return json_encode($cierre_caja,JSON_UNESCAPED_UNICODE);
                }else{ //cuando no existe un cierre de efectivo en la caja || registro de cierre efectivo

                    $data_ce['caja_id'] = $request['caja_id'];
                    $data_ce['caja_datos_fisicos_tipo_id'] = 1;
                    $data_ce['monto_boveda'] = 0;
                    $data_ce['monto_efectivo'] = 0;
                    $data_ce['monto_final'] = 0;
                    $data_ce['vinculacion'] = 0;
                    $data_ce['status'] = 1;
                    $data_ce['user_created_id'] = $model->get_usuario_id();
                    $data_ce['created_at'] = $created_at;
                    $cierre_caja = $this->registrar_cierre_efectivo($data_ce);
                    $cierre_caja = $model->obtener_por_id($data['caja_id']);
                    return json_encode($cierre_caja,JSON_UNESCAPED_UNICODE);
                }
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

    public function registrar_cierre_efectivo($data){
        try {
        
            $model =  new CierreEfectivo();
            $data_ce['caja_id'] = $data['caja_id'];
            $data_ce['caja_datos_fisicos_tipo_id'] = $data['caja_datos_fisicos_tipo_id'];
            $data_ce['monto_boveda'] = $data['monto_boveda'];
            $data_ce['monto_efectivo'] = $data['monto_efectivo'];
            $data_ce['monto_final'] = $data['monto_final'];
            $data_ce['vinculacion'] = $data['vinculacion'];
            $data_ce['status'] = $data['status'];
            $data_ce['user_created_id'] = $data['user_created_id'];
            $data_ce['created_at'] = $data['created_at'];
            $cierre_caja = $model->registrar($data_ce);
            if ($cierre_caja['status'] == 404) {
                return $cierre_caja;
            }
        
            $model_cierre_detalle = new CierreEfectivoDetalle();
            $data_ced['caja_id'] = $cierre_caja['result'];
            $data_ced['status'] = 1;
            $cierre_caja_detalle = $model_cierre_detalle->registrar($data_ced);
            if ($cierre_caja_detalle['status'] == 404) {
                return $cierre_caja_detalle;
            }
            
            return $cierre_caja;
            
        } catch (Exception $th) {
            $result['status'] = 404; 
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';
            return $result;
        }
    }

    public function guardar_cierre_efectivo(){
        try {
            $request = $_POST; 
            $model =  new CierreEfectivo();
            if (($model->get_usuario_id()) > 0) {
                $updated_at = date('Y-m-d H:i:s');

                //calcular de nuevo la totalizacion
                $met_total_ce = $this->calcular_totales($request);
                if ($met_total_ce['status'] == 404) {
                    return json_encode($met_total_ce,JSON_UNESCAPED_UNICODE);
                }

                $total_ce = $met_total_ce['result'];

                if (floatval($total_ce['total']) != floatval($request['total'])) {
                    $result['status'] = 404;
                    $result['result'] = '';
                    $result['message'] = 'El total ingresado no coincide con los datos ingresados, por favor intentalo de nuevo.';
                }

                $data['caja_id'] = $request['caja_id'];
                $data['monto_efectivo'] = $total_ce['total_efectivo'];
                $data['monto_boveda'] = $total_ce['total_boveda'];
                $data['monto_final'] = $total_ce['total'];
                $data['user_updated_id'] = $model->get_usuario_id();
                $data['updated_at'] = $updated_at;
                $upd_cierre_efectivo = $model->modificar($data);
                if ($upd_cierre_efectivo['status'] == 404) {
                    return json_encode($upd_cierre_efectivo,JSON_UNESCAPED_UNICODE);
                }

                $data['moneda_001_cant'] = $request['moneda_001'];
                $data['moneda_002_cant'] = $request['moneda_002'];
                $data['moneda_005_cant'] = $request['moneda_005'];
                $data['moneda_010_cant'] = $request['moneda_010'];
                $data['moneda_020_cant'] = $request['moneda_020'];
                $data['moneda_050_cant'] = $request['moneda_050'];
                $data['billete_10_cant'] = $request['billete_10'];
                $data['billete_20_cant'] = $request['billete_20'];
                $data['billete_50_cant'] = $request['billete_50'];
                $data['billete_100_cant'] = $request['billete_100'];
                $data['billete_200_cant'] = $request['billete_200'];

                $data['moneda_001_total'] = $total_ce['total_moneda_001'];
                $data['moneda_002_total'] = $total_ce['total_moneda_002'];
                $data['moneda_005_total'] = $total_ce['total_moneda_005'];
                $data['moneda_010_total'] = $total_ce['total_moneda_010'];
                $data['moneda_020_total'] = $total_ce['total_moneda_020'];
                $data['moneda_050_total'] = $total_ce['total_moneda_050'];
                $data['billete_10_total'] = $total_ce['total_billete_10'];
                $data['billete_20_total'] = $total_ce['total_billete_20'];
                $data['billete_50_total'] = $total_ce['total_billete_50'];
                $data['billete_100_total'] = $total_ce['total_billete_100'];
                $data['billete_200_total'] = $total_ce['total_billete_200'];

            
                $model_cierre_efectivo_detalle = new CierreEfectivoDetalle();
                $update_cierre_efe_det = $model_cierre_efectivo_detalle->modificar($data);
                if ($update_cierre_efe_det['status'] == 404) {
                    return json_encode($update_cierre_efe_det,JSON_UNESCAPED_UNICODE);
                }
           
                $result['status'] = 200;
                $result['result'] = '';
                $result['message'] = 'Se ha guardo el cierre efectivo de caja.'; 
                return json_encode($result,JSON_UNESCAPED_UNICODE);
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

    public function calcular_totales($data_ce){
        try {
           
                //totales
                $total_billete_10 = $data_ce['billete_10'] * 10;
                $total_billete_20 = $data_ce['billete_20'] * 20;
                $total_billete_50 = $data_ce['billete_50'] * 50;
                $total_billete_100 = $data_ce['billete_100'] * 100;
                $total_billete_200 = $data_ce['billete_200'] * 200;
                $total_billletes = $total_billete_10 + $total_billete_20 + $total_billete_50 + $total_billete_100 + $total_billete_200;

                $total_moneda_001 = $data_ce['moneda_001'] * 0.1;
                $total_moneda_002 = $data_ce['moneda_002'] * 0.2;
                $total_moneda_005 = $data_ce['moneda_005'] * 0.5;
                $total_moneda_010 = $data_ce['moneda_010'] * 1;
                $total_moneda_020 = $data_ce['moneda_020'] * 2;
                $total_moneda_050 = $data_ce['moneda_050'] * 5;
                $total_monedas = $total_moneda_001 + $total_moneda_002 + $total_moneda_005 + $total_moneda_010 + $total_moneda_020 + $total_moneda_050;

           
                $total_efectivo = $total_billletes + $total_monedas;
                $total_boveda = $data_ce['importe_boveda'];
                $total = $total_efectivo + $total_boveda;

                $data['total_billete_10'] = number_format($total_billete_10,2,'.','');
                $data['total_billete_20'] = number_format($total_billete_20,2,'.','');
                $data['total_billete_50'] = number_format($total_billete_50,2,'.','');
                $data['total_billete_100'] = number_format($total_billete_100,2,'.','');
                $data['total_billete_200'] = number_format($total_billete_200,2,'.','');
                $data['total_billletes'] = number_format($total_billletes,2,'.','');
                $data['total_moneda_001'] = number_format($total_moneda_001,2,'.','');
                $data['total_moneda_002'] = number_format($total_moneda_002,2,'.','');
                $data['total_moneda_005'] = number_format($total_moneda_005,2,'.','');
                $data['total_moneda_010'] = number_format($total_moneda_010,2,'.','');
                $data['total_moneda_020'] = number_format($total_moneda_020,2,'.','');
                $data['total_moneda_050'] = number_format($total_moneda_050,2,'.','');
                $data['total_monedas'] = number_format($total_monedas,2,'.','');

                $data['total_efectivo'] = number_format($total_efectivo,2,'.','');
                $data['total_boveda'] = number_format($total_boveda,2,'.','');
                $data['total'] = number_format($total,2,'.','');

                $result['status'] = 200; 
                $result['result'] = $data;
                $result['message'] = '';
                return $result;

        } catch (Exception $th) {
            $result['status'] = 404; 
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';
            
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
    }

    public function obtener_cierre_efectivo_eliminado_por_caja_id(){
        try {
            $request = $_POST; 
            $model =  new CierreEfectivo();
            if (($model->get_usuario_id()) > 0) {
                $data['caja_id'] = $request['caja_id'];
                $cierre_caja = $model->obtener_por_id_eliminado($data['caja_id']);
                return json_encode($cierre_caja,JSON_UNESCAPED_UNICODE);
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

    public function vincular_caja_eliminada_detalle_efectivo(){
        try {
            $request = $_POST; 
            $model =  new CajaEliminada();
            if (($model->get_usuario_id()) > 0) {
                $created_at = date('Y-m-d H:i:s');
                $updated_at = date('Y-m-d H:i:s');

                //obtener datos de la caja seleccionada
                $model_cierre_efectivo = new CierreEfectivo;
                $cierre_caja = $model_cierre_efectivo->obtener_caja_por_id($request['caja_id']); 
                if ($cierre_caja['status'] == 404) {
                    return json_encode($cierre_caja,JSON_UNESCAPED_UNICODE);  
                }
                $cierre_caja = $cierre_caja['result'];

                //Verificamos si existe cajas eliminadas anteriomente
                $data['turno_id'] = $cierre_caja['turno_id'];
                $data['local_caja_id'] = $cierre_caja['local_caja_id'];
                $data['fecha_operacion'] = $cierre_caja['fecha_operacion'];
                $caja_elim = $model->obtener_caja_eliminada($data);
                if ($caja_elim['status'] == 404) {
                    return json_encode($caja_elim,JSON_UNESCAPED_UNICODE);  
                }

                //obtener detalle de cierre de efectivo de la caja eliminada
                $model_cierre_efectivo = new CierreEfectivo;
                $cierre_efectivo_elim = $model_cierre_efectivo->obtener_por_id_eliminado($caja_elim['result']['id']);
                if ($cierre_efectivo_elim['status'] == 404) {
                    return json_encode($cierre_efectivo_elim,JSON_UNESCAPED_UNICODE);  
                }

                //Verificar si la nueva caja cuenta con un cierre de caja
                $data['caja_id'] = $request['caja_id'];
                $cierre_efectivo = $model_cierre_efectivo->validar_cierre_efectivo_por_caja($data);
                
                if (count($cierre_efectivo['result']) == 0) { // en caso que no exista un cierre de efectivo, se creara uno nuevo
                    $data_ce['caja_id'] = $request['caja_id'];
                    $data_ce['caja_datos_fisicos_tipo_id'] = 1;
                    $data_ce['monto_boveda'] = 0;
                    $data_ce['monto_efectivo'] = 0;
                    $data_ce['monto_final'] = 0;
                    $data_ce['vinculacion'] = 1;
                    $data_ce['status'] = 1;
                    $data_ce['user_created_id'] = $model->get_usuario_id();
                    $data_ce['created_at'] = $created_at;
                    $cierre_caja = $this->registrar_cierre_efectivo($data_ce);
                    //return json_encode($cierre_caja,JSON_UNESCAPED_UNICODE);
                }
             
                //actualizamos el nuevo cierre de efectivo con los datos de cierre de efectivo eliminada
                $data_ce['caja_id'] = $request['caja_id'];
                $data_ce['monto_boveda'] = $cierre_efectivo_elim['result']['monto_boveda'];
                $data_ce['monto_efectivo'] = $cierre_efectivo_elim['result']['monto_efectivo'];
                $data_ce['monto_final'] = $cierre_efectivo_elim['result']['monto_final'];
                $data_ce['vinculacion'] = 1;
                $data_ce['user_updated_id'] = $model->get_usuario_id();
                $data_ce['updated_at'] = $updated_at;
                $upd_cierre_efectivo = $model_cierre_efectivo->modificar_por_vinculacion_caja_eliminada($data_ce);
                if ($upd_cierre_efectivo['status'] == 404) {
                    return json_encode($upd_cierre_efectivo,JSON_UNESCAPED_UNICODE);
                }
                //detalle de cierre de efectivo
                $data_ce['moneda_001_cant'] = $cierre_efectivo_elim['result']['moneda_001_cant'];
                $data_ce['moneda_002_cant'] = $cierre_efectivo_elim['result']['moneda_002_cant'];
                $data_ce['moneda_005_cant'] = $cierre_efectivo_elim['result']['moneda_005_cant'];
                $data_ce['moneda_010_cant'] = $cierre_efectivo_elim['result']['moneda_010_cant'];
                $data_ce['moneda_020_cant'] = $cierre_efectivo_elim['result']['moneda_020_cant'];
                $data_ce['moneda_050_cant'] = $cierre_efectivo_elim['result']['moneda_050_cant'];
                $data_ce['billete_10_cant'] = $cierre_efectivo_elim['result']['billete_10_cant'];
                $data_ce['billete_20_cant'] = $cierre_efectivo_elim['result']['billete_20_cant'];
                $data_ce['billete_50_cant'] = $cierre_efectivo_elim['result']['billete_50_cant'];
                $data_ce['billete_100_cant'] = $cierre_efectivo_elim['result']['billete_100_cant'];
                $data_ce['billete_200_cant'] = $cierre_efectivo_elim['result']['billete_200_cant'];
                $data_ce['moneda_001_total'] = $cierre_efectivo_elim['result']['moneda_001_total'];
                $data_ce['moneda_002_total'] = $cierre_efectivo_elim['result']['moneda_002_total'];
                $data_ce['moneda_005_total'] = $cierre_efectivo_elim['result']['moneda_005_total'];
                $data_ce['moneda_010_total'] = $cierre_efectivo_elim['result']['moneda_010_total'];
                $data_ce['moneda_020_total'] = $cierre_efectivo_elim['result']['moneda_020_total'];
                $data_ce['moneda_050_total'] = $cierre_efectivo_elim['result']['moneda_050_total'];
                $data_ce['billete_10_total'] = $cierre_efectivo_elim['result']['billete_10_total'];
                $data_ce['billete_20_total'] = $cierre_efectivo_elim['result']['billete_20_total'];
                $data_ce['billete_50_total'] = $cierre_efectivo_elim['result']['billete_50_total'];
                $data_ce['billete_100_total'] = $cierre_efectivo_elim['result']['billete_100_total'];
                $data_ce['billete_200_total'] = $cierre_efectivo_elim['result']['billete_200_total'];
                $model_cierre_efectivo_detalle = new CierreEfectivoDetalle();
                $update_cierre_efectivo = $model_cierre_efectivo_detalle->modificar($data_ce);
                if ($update_cierre_efectivo['status'] == 404) {
                    return json_encode($update_cierre_efectivo,JSON_UNESCAPED_UNICODE);
                }

                //obtener el cierre de efectivo actualizado
                $cierre_caja = $model_cierre_efectivo->obtener_por_id($request['caja_id']);
                if ($cierre_caja['status'] == 404) {
                    return json_encode($cierre_caja,JSON_UNESCAPED_UNICODE);
                }
                $cierre_caja['message'] = 'La vinculación de Cierre de Efectivo fue exitosa.';
                return json_encode($cierre_caja,JSON_UNESCAPED_UNICODE);
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
 