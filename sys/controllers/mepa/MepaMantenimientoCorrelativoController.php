<?php 

class MepaMantenimientoCorrelativoController
{

 
    public function listar_correlativos(){
        try {
    
            
            $model =  new MepaCorrelativo();
            $usuario_locales = $model->get_usuario_locales();

            $data['usuario_locales'] = $usuario_locales;
            $empresa_at = $model->obtener_permiso_empresas(array('usuario_locales' => $usuario_locales,'empresa' => 'RED AT'));
            $empresa_igh = $model->obtener_permiso_empresas(array('usuario_locales' => $usuario_locales,'empresa' => 'RED IGH'));

        

            if (($model->get_usuario_id()) > 0) {
                $correlativos = [];
                if ($empresa_at['result']) {
                    array_push($correlativos,array(
                        'id' => 1,
                        'name' => 'Correlativo Reporte Contabilidad Concar Red AT',
                        'type' => 1,
                        'empresa_id' => 1,
                        'acciones' => '<button type=button" title="Resetear Correlativo" class="btn btn-rounded btn-success btn-xs" onclick="sec_mepa_mantenimiento_confirmar_resetear_correlativo(1)">Resetear Correlativo</button>
                                        <button type="button" title="Historial de Reseteos" class="btn btn-rounded btn-warning btn-xs" onclick="sec_mepa_mantenimiento_historial_reseteos(1)">
                                            <i class="fa fa-file"></i>												
                                        </button>',
                    ));
                }
                if ($empresa_igh['result']) {
                    array_push($correlativos,array(
                        'id' => 2,
                        'name' => 'Correlativo Reporte Contabilidad Concar Red IGH',
                        'type' => 1,
                        'empresa_id' => 30,
                        'acciones' => '<button type=button" title="Resetear Correlativo" class="btn btn-rounded btn-success btn-xs" onclick="sec_mepa_mantenimiento_confirmar_resetear_correlativo(2)">Resetear Correlativo</button>
                                        <button type="button" title="Historial de Reseteos" class="btn btn-rounded btn-warning btn-xs" onclick="sec_mepa_mantenimiento_historial_reseteos(2)">
                                            <i class="fa fa-file"></i>												
                                        </button>',
                    ));
                }
                if ($empresa_at['result']) {
                    array_push($correlativos,array(
                        'id' => 3,
                        'name' => 'Correlativo Rendición Liquidación Red AT',
                        'type' => 2,
                        'empresa_id' => 1,
                        'acciones' => '<button type=button" title="Resetear Correlativo" class="btn btn-rounded btn-success btn-xs" onclick="sec_mepa_mantenimiento_confirmar_resetear_correlativo(3)">Resetear Correlativo</button>
                                        <button type="button" title="Historial de Reseteos" class="btn btn-rounded btn-warning btn-xs" onclick="sec_mepa_mantenimiento_historial_reseteos(3)">
                                            <i class="fa fa-file"></i>												
                                        </button>',
                    ));
                }
                if ($empresa_igh['result']) {
                    array_push($correlativos,array(
                        'id' => 4,
                        'name' => 'Correlativo Rendición Liquidación Red IGH',
                        'type' => 2,
                        'empresa_id' => 30,
                        'acciones' => '<button type=button" title="Resetear Correlativo" class="btn btn-rounded btn-success btn-xs" onclick="sec_mepa_mantenimiento_confirmar_resetear_correlativo(4)">Resetear Correlativo</button>
                                        <button type="button" title="Historial de Reseteos" class="btn btn-rounded btn-warning btn-xs" onclick="sec_mepa_mantenimiento_historial_reseteos(4)">
                                            <i class="fa fa-file"></i>												
                                        </button>',
                    ));
                }
                if ($empresa_at['result']) {
                    array_push($correlativos,array(
                        'id' => 5,
                        'name' => 'Correlativo Planilla Movilidad Red AT',
                        'type' => 2,
                        'empresa_id' => 1,
                        'acciones' => '<button type=button" title="Resetear Correlativo" class="btn btn-rounded btn-success btn-xs" onclick="sec_mepa_mantenimiento_confirmar_resetear_correlativo(5)">Resetear Correlativo</button>
                                        <button type="button" title="Historial de Reseteos" class="btn btn-rounded btn-warning btn-xs" onclick="sec_mepa_mantenimiento_historial_reseteos(5)">
                                            <i class="fa fa-file"></i>												
                                        </button>',
                    ));
                }
                if ($empresa_igh['result']) {
                    array_push($correlativos,array(
                        'id' => 6,
                        'name' => 'Correlativo Planilla Movilidad Red IGH',
                        'type' => 2,
                        'empresa_id' => 30,
                        'acciones' => '<button type=button" title="Resetear Correlativo" class="btn btn-rounded btn-success btn-xs" onclick="sec_mepa_mantenimiento_confirmar_resetear_correlativo(6)">Resetear Correlativo</button>
                                        <button type="button" title="Historial de Reseteos" class="btn btn-rounded btn-warning btn-xs" onclick="sec_mepa_mantenimiento_historial_reseteos(6)">
                                            <i class="fa fa-file"></i>												
                                        </button>',
                    ));
                }
                $result['status'] = 200; 
                $result['result'] = $correlativos;
                $result['message'] = 'Se ha obtenido datos de gestión'; 

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


    public function resetear_correlativo(){
        try {
            $request = $_POST; 
            $model =  new MepaCorrelativo();
            if (($model->get_usuario_id()) > 0) {

                $data['id'] = $request['id'];
                $data['status'] = 1;
                $data['usuario_id'] = $model->get_usuario_id();
                $data['created_at'] = date('Y-m-d H:i:s');
                $registro = $model->resetear_correlativo($data);
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

    public function historial_reseteos(){
        try {
    
            $model =  new MepaCorrelativo();
            if (($model->get_usuario_id()) > 0) {
                $data['id'] = $_POST['id'];
                $registro = $model->historial_reseteos($data);

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
 