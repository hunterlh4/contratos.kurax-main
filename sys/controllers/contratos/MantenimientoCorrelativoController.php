<?php

class MantenimientoCorrelativoController
{



    public function listar_correlativos()
    {
        try {

            $model =  new Correlativo();
            if (($model->get_usuario_id()) > 0) {
                // var_dump($_POST);
                // $data['metodo_id'] = $_POST['metodo_id'];
                $registro = $model->listar_correlativos();
                $data_result = [];
                $index = 1;
                foreach ($registro['result'] as $be) {
                    $data_row = $be;
                    $data_row['acciones'] = '<button type=button" class="btn btn-rounded btn-warning btn-xs" onclick="sec_contrato_mant_show_modal_correlativo(' . $be['id'] . ')">
                                <i class="fa fa-pencil"></i>												
                            </button>';
                    $index++;

                    $estado = "";
                    switch ($be['status']) {
                        case 1:
                            $estado = '<span class="badge bg-success">
                                Activo									
                        </span>';
                            break;
                        case 0:
                            $estado = '<span class="badge bg-warning">
                                Inactivo									
                        </span>';
                            break;
                    }
                    $data_row['estado'] = $estado;
                    array_push($data_result, $data_row);
                }
                $registro['result'] = $data_result;
                return json_encode($registro, JSON_UNESCAPED_UNICODE);
            }
            $result['status'] = 404;
            $result['result'] = '';
            $result['message'] = 'Su session ha finalizado, por favor ingrese de nuevo.';
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';

            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }

    public function obtener_por_id()
    {
        try {

            $model =  new Correlativo();
            if (($model->get_usuario_id()) > 0) {
                $data['id'] = $_POST['id'];
                $registro = $model->obtener_por_id($data);
                return json_encode($registro, JSON_UNESCAPED_UNICODE);
            }
            $result['status'] = 404;
            $result['result'] = '';
            $result['message'] = 'Su session ha finalizado, por favor ingrese de nuevo.';
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';

            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }

    public function modificar_correlativo()
    {
        try {

            $model =  new Correlativo();
            if (($model->get_usuario_id()) > 0) {
                $data['id'] = $_POST['id'];
                $data['sigla'] = $_POST['sigla'];
                $data['numero'] = $_POST['numero'];
                $data['user_updated_id'] = $model->get_usuario_id();
                $data['updated_at'] = date('Y-m-d H:i:s');
                $registro = $model->modificar_correlativo($data);
                return json_encode($registro, JSON_UNESCAPED_UNICODE);
            }
            $result['status'] = 404;
            $result['result'] = '';
            $result['message'] = 'Su session ha finalizado, por favor ingrese de nuevo.';
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';

            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }

    public function listar_tipos_contratos()
    {
        try {

            $model =  new Correlativo();
            if (($model->get_usuario_id()) > 0) {
                $data['not_tipo_contrato_id'] = '';
                $registro = $model->listar_tipos_contratos($data);
                return json_encode($registro, JSON_UNESCAPED_UNICODE);
            }
            $result['status'] = 404;
            $result['result'] = '';
            $result['message'] = 'Su session ha finalizado, por favor ingrese de nuevo.';
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';

            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }

    public function seleccionar_tipo_contrato()
    {
        try {

            $model =  new Correlativo();
            if (($model->get_usuario_id()) > 0) {
                $result = [];
                $data['not_tipo_contrato_id'] = $_POST['tipo_contrato_id'];
                $tipo_contrato = $model->listar_tipos_contratos($data);

                $data['tipo_contrato_id'] = $_POST['tipo_contrato_id'];
                $contratos = $model->obtener_contratos_por_tipo_contrato($data);


                $result['status'] = 200;
                $result['result']['tipo_contrato'] = $tipo_contrato['result'];
                $result['result']['contratos'] = $contratos['result'];
                return json_encode($result, JSON_UNESCAPED_UNICODE);
            }
            $result['status'] = 404;
            $result['result'] = '';
            $result['message'] = 'Su session ha finalizado, por favor ingrese de nuevo.';
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';

            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }

    public function obtener_correlativo_por_id()
    {
        try {

            $model =  new Correlativo();
            if (($model->get_usuario_id()) > 0) {
                $data['tipo_contrato_id'] = $_POST['tipo_contrato_id'];
                $registro = $model->obtener_correlativo_por_id($data);
                if ($registro['status'] == 200) {
                    $registro['result']['numero'] = (int) $registro['result']['numero'] + 1;
                }
                return json_encode($registro, JSON_UNESCAPED_UNICODE);
            }
            $result['status'] = 404;
            $result['result'] = '';
            $result['message'] = 'Su session ha finalizado, por favor ingrese de nuevo.';
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';

            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }

    public function guardar_cambio_tipo_contrato()
    {
        try {

            $model =  new Contrato();
            if (($model->get_usuario_id()) > 0) {
                $data['tipo_contrato_original_id'] = $_POST['tipo_contrato_id'];
                $data['tipo_contrato_nuevo_id'] = $_POST['cambiar_tipo_contrato_id'];
                $data['contrato_id'] = $_POST['contrato_id'];
                $data['codigo'] = $_POST['codigo'];
                $data['nro_ticket'] = $_POST['nro_ticket'];
                $data['responsable_id'] = $_POST['responsable_id'];
                $data['status'] = 1;
                $data['user_created_id'] = $model->get_usuario_id();
                $data['created_at'] = date('Y-m-d H:i:s');
                $registro = $model->guardar_cambio_tipo_contrato($data);
                return json_encode($registro, JSON_UNESCAPED_UNICODE);
            }

            $result['status'] = 404;
            $result['result'] = '';
            $result['message'] = 'Su session ha finalizado, por favor ingrese de nuevo.';
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';

            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }


    public function listar_cambio_tipo_contrato()
    {
        try {

            $model =  new Contrato();
            if (($model->get_usuario_id()) > 0) {
                $registro = $model->listar_cambio_tipo_contrato();
                $result = [];
                foreach ($registro['result'] as $be) {
                    $row_data = $be;
                    $estado = "";
                    switch ($be['status']) {
                        case 1:
                            $estado = '<span class="badge bg-success">
                                Activo									
                        </span>';
                            break;
                        case 0:
                            $estado = '<span class="badge bg-warning">
                                Inactivo									
                        </span>';
                            break;
                    }
                    $row_data['estado'] = $estado;
                    array_push($result, $row_data);
                }
                $registro['result'] = $result;
                return json_encode($registro, JSON_UNESCAPED_UNICODE);
            }

            $result['status'] = 404;
            $result['result'] = '';
            $result['message'] = 'Su session ha finalizado, por favor ingrese de nuevo.';
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';

            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }
}
