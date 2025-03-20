<?php

class ArrendatarioController
{

    public function registrar_arrendatario()
    {
        try {
            $request = json_decode(file_get_contents('php://input'), true);
            $model = new Arrendatario();

            if ($model->existeArrendatario($request['num_docu'], $request['num_ruc'])) {
                return json_encode([
                    'status' => 404,
                    'message' => $request['tipo_docu_identidad_id'] == 2 ? 'El RUC ya está registrado.' : 'El Número de Documento ya está regsitrado.'
                ], JSON_UNESCAPED_UNICODE);
            }

            $request['created_at'] = date('Y-m-d H:i:s');
            $registro = $model->registrar_arrendatario($request);
            return json_encode($registro, JSON_UNESCAPED_UNICODE);
        } catch (Exception $e) {
            return json_encode([
                'status' => 404,
                'message' => 'Ha ocurrido un error.'
            ], JSON_UNESCAPED_UNICODE);
        }
    }

    public function registrar_personal()
    {
        try {

            $request = file_get_contents('php://input');
            $request = json_decode($request, true);
            $model =  new Propietario();
            $validacion = $model->validar_personal($request);
            if ($validacion['result'] > 0) {
                $result['status'] = 404;
                $result['result'] = $validacion['result'];
                $result['message'] = $request['tipo_docu_identidad_id'] == 2 ? 'El RUC ya esta registrado.' : 'El Nro de Documento ya esta registrado.';
                return json_encode($result, JSON_UNESCAPED_UNICODE);
            }

            if ($request["tipo_persona_contacto"] == 1) {
                $request['contacto_nombre'] = $request['nombre'];
            } else {
                $request['contacto_nombre'] = $request['contacto_nombre'];
            }

            $request['created_at'] = date('Y-m-d H:i:s');
            $registro = $model->registrar_personal($request);
            return json_encode($registro, JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';

            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }

    public function validar_arrendatario_modificacion()
    {
        try {
            $request = file_get_contents('php://input');
            $request = json_decode($request, true);
            $model =  new Arrendatario();
            $validacion = $model->validar_arrendatario_modificar($request['id']);
            if ($validacion['result'] > 0) {
                $result['status'] = 404;
                $result['result'] = $validacion['result'];
                $result['message'] = 'No se puede editar este propietario, porque ya tiene asignado un contrato.';
                return json_encode($result, JSON_UNESCAPED_UNICODE);
            }
            return json_encode($validacion, JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';

            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }

    public function modificar_arrendatario()
    {
        try {

            $request = file_get_contents('php://input');
            $request = json_decode($request, true);
            $model =  new Arrendatario();

            $validacion = $model->validar_personal($request, 'modificar');
            if ($validacion['result'] > 0) {
                $result['status'] = 404;
                $result['result'] = $validacion['result'];
                $result['message'] = $request['tipo_docu_identidad_id'] == 2 ? 'El RUC ya esta registrado.' : 'El Nro de Documento ya esta registrado.';
                return json_encode($result, JSON_UNESCAPED_UNICODE);
            }

            if ($request["tipo_persona_contacto"] == 1) {
                $request['contacto_nombre'] = $request['nombre'];
            } else {
                $request['contacto_nombre'] = $request['contacto_nombre'];
            }

            $request['created_at'] = date('Y-m-d H:i:s');
            $registro = $model->modificar_personal($request);
            return json_encode($registro, JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';

            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }
}
