<?php

class MantenimientoServicioPublicoController
{



    public function listar_empresas()
    {
        try {

            $model =  new ServicioPublico();
            if (($model->get_usuario_id()) > 0) {
                // $data['metodo_id'] = $_POST['metodo_id'];
                $registro = $model->listar_empresas();
                $data_result = [];
                $index = 1;
                foreach ($registro['result'] as $be) {
                    $data_row = $be;
                    $data_row['acciones'] = '<button type=button" class="btn btn-rounded btn-warning btn-xs" onclick="sec_contrato_mant_show_modal_servicio_publico(' . $be['id'] . ')">
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

    public function obtener_empresa_por_id()
    {
        try {

            $model =  new ServicioPublico();
            if (($model->get_usuario_id()) > 0) {
                $data['id'] = $_POST['id'];
                $registro = $model->obtener_empresa_por_id($data);
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

    public function modificar_empresa()
    {
        try {

            $model =  new ServicioPublico();
            if (($model->get_usuario_id()) > 0) {
                $data['id'] = $_POST['id'];
                $data['ruc'] = $_POST['ruc'];
                $data['razon_social'] = $_POST['razon_social'];
                $data['nombre_comercial'] = $_POST['nombre_comercial'];
                $data['status'] = $_POST['status'];
                $data['user_updated_id'] = $model->get_usuario_id();
                $data['updated_at'] = date('Y-m-d H:i:s');
                $registro = $model->modificar_empresa($data);
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

    public function registrar_empresa()
    {
        try {

            $model =  new ServicioPublico();
            if (($model->get_usuario_id()) > 0) {
                $data['ruc'] = $_POST['ruc'];
                $data['razon_social'] = $_POST['razon_social'];
                $data['nombre_comercial'] = $_POST['nombre_comercial'];
                $data['status'] = $_POST['status'];
                $data['user_created_id'] = $model->get_usuario_id();
                $data['created_at'] = date('Y-m-d H:i:s');
                $registro = $model->registrar_empresa($data);
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
