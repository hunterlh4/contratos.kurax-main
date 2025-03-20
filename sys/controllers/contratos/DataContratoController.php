<?php

class DataContratoController
{

    public function ObtenerTiposDeContratos()
    {
        $model =  new DataContrato();
        $nuevo_contrato = $model->ObtenerTiposDeContratos();
        return json_encode($nuevo_contrato, JSON_UNESCAPED_UNICODE);
    }

    public function ObtenerTipoCorreoMetodo()
    {
        $model =  new DataContrato();
        $nuevo_contrato = $model->ObtenerTipoCorreoMetodo();
        return json_encode($nuevo_contrato, JSON_UNESCAPED_UNICODE);
    }

    public function obtereAreaId()
    {
        $model =  new DataContrato();
        $nuevo_contrato = $model->obtereAreaId();
        return json_encode($nuevo_contrato, JSON_UNESCAPED_UNICODE);
    }

    public function ObtenerEmpresas()
    {
        $model =  new DataContrato();
        $nuevo_contrato = $model->obtener_empresas();
        $nuevo_contrato_utf8 = mb_convert_encoding($nuevo_contrato, 'UTF-8');

        return json_encode($nuevo_contrato_utf8, JSON_UNESCAPED_UNICODE);
    }

    public function obtener_personal_responsable()
    {
        $model =  new DataContrato();
        $personal_responsable = $model->obtener_personal_responsable();
        $nuevo_contrato_utf8 = mb_convert_encoding($personal_responsable, 'UTF-8');

        return json_encode($nuevo_contrato_utf8, JSON_UNESCAPED_UNICODE);
    }

    public function obtener_propietario()
    {
        $request = file_get_contents('php://input');
        $request = json_decode($request, true);

        $tipo_busqueda = $request['tipo_busqueda'];
        $ids = $request['ids'];
        $nombre_o_numdocu = $request['nombre_o_numdocu'];
        $model =  new DataContrato();
        $personal_responsable = $model->obtener_propietarios($tipo_busqueda, $ids, $nombre_o_numdocu);
        $nuevo_contrato_utf8 = mb_convert_encoding($personal_responsable, 'UTF-8');

        return json_encode($nuevo_contrato_utf8, JSON_UNESCAPED_UNICODE);
    }
    public function obtener_ocupante()
    {
        $request = file_get_contents('php://input');
        $request = json_decode($request, true);

        $tipo_busqueda = $request['tipo_busqueda'];
        $ids = $request['ids'];
        $nombre_o_numdocu = $request['nombre_o_numdocu'];
        $model =  new DataContrato();
        $personal_responsable = $model->obtener_ocupantes($tipo_busqueda, $ids, $nombre_o_numdocu);
        $nuevo_contrato_utf8 = mb_convert_encoding($personal_responsable, 'UTF-8');

        return json_encode($nuevo_contrato_utf8, JSON_UNESCAPED_UNICODE);
    }

    public function obtener_tipo_persona()
    {
        $model =  new DataContrato();
        $tipo_persona = $model->obtener_tipo_persona();
        $nuevo_contrato_utf8 = mb_convert_encoding($tipo_persona, 'UTF-8');

        return json_encode($nuevo_contrato_utf8, JSON_UNESCAPED_UNICODE);
    }

    public function obtener_tipo_doc_identidad()
    {
        $model =  new DataContrato();
        $tipo_persona = $model->obtener_tipo_doc_identidad();
        $nuevo_contrato_utf8 = mb_convert_encoding($tipo_persona, 'UTF-8');

        return json_encode($nuevo_contrato_utf8, JSON_UNESCAPED_UNICODE);
    }

    public function obtener_departartamentos()
    {
        $model =  new DataContrato();
        $departamentos = $model->obtener_departartamentos();
        $nuevo_contrato_utf8 = mb_convert_encoding($departamentos, 'UTF-8');

        return json_encode($nuevo_contrato_utf8, JSON_UNESCAPED_UNICODE);
    }

    public function obtener_provincias_segun_departamento()
    {
        $request = file_get_contents('php://input');
        $request = json_decode($request, true);

        $departamento_id = $request['departamento_id'];
        $model =  new DataContrato();
        $provincias = $model->obtener_provincias_segun_departamento($departamento_id);
        $provincia_utf8 = mb_convert_encoding($provincias, 'UTF-8');

        return json_encode($provincia_utf8, JSON_UNESCAPED_UNICODE);
    }

    public function obtener_distritos_segun_provincia()
    {
        $request = file_get_contents('php://input');
        $request = json_decode($request, true);

        $departamento_id = $request['departamento_id'];
        $provincia_id = $request['provincia_id'];
        $model =  new DataContrato();
        $distritos = $model->obtener_distritos_segun_provincia($provincia_id, $departamento_id);
        $provincia_utf8 = mb_convert_encoding($distritos, 'UTF-8');

        return json_encode($provincia_utf8, JSON_UNESCAPED_UNICODE);
    }

    public function obtener_tipo_compromiso_pago()
    {
        $model =  new DataContrato();
        $compromiso_pago = $model->obtener_tipo_compromiso_pago();
        $compromiso_pago = mb_convert_encoding($compromiso_pago, 'UTF-8');

        return json_encode($compromiso_pago, JSON_UNESCAPED_UNICODE);
    }

    public function obtener_tipo_compromiso_pago_arbitrio()
    {
        $model =  new DataContrato();
        $compromiso_pago = $model->obtener_tipo_compromiso_pago_arbitrio();
        $compromiso_pago = mb_convert_encoding($compromiso_pago, 'UTF-8');

        return json_encode($compromiso_pago, JSON_UNESCAPED_UNICODE);
    }
    public function obtener_moneda_de_contrato()
    {
        $model =  new DataContrato();
        $moneda_de_contrato = $model->obtener_moneda_de_contrato();
        return json_encode($moneda_de_contrato, JSON_UNESCAPED_UNICODE);
    }

    public function obtener_tipo_pago_renta()
    {
        $model =  new DataContrato();
        $tipo_pago_renta = $model->obtener_tipo_pago_renta();
        $tipo_pago_renta = mb_convert_encoding($tipo_pago_renta, 'UTF-8');
        return json_encode($tipo_pago_renta, JSON_UNESCAPED_UNICODE);
    }
    public function obtener_tipo_afectacion_igv()
    {
        $model =  new DataContrato();
        $tipo_afectacion_renta = $model->obtener_tipo_afectacion_igv();
        $tipo_afectacion_renta = mb_convert_encoding($tipo_afectacion_renta, 'UTF-8');

        return json_encode($tipo_afectacion_renta, JSON_UNESCAPED_UNICODE);
    }
    public function obtener_tipo_venta()
    {
        $model =  new DataContrato();
        $tipos_adelantos = $model->obtener_tipo_venta();
        $tipos_adelantos = mb_convert_encoding($tipos_adelantos, 'UTF-8');

        return json_encode($tipos_adelantos, JSON_UNESCAPED_UNICODE);
    }
    public function obtener_tipo_adelantos()
    {
        $model =  new DataContrato();
        $tipo_adelantos = $model->obtener_tipo_adelantos();
        $tipo_adelantos = mb_convert_encoding($tipo_adelantos, 'UTF-8');

        return json_encode($tipo_adelantos, JSON_UNESCAPED_UNICODE);
    }
    public function obtener_tipo_impuesto_a_la_renta()
    {
        $model =  new DataContrato();
        $tipo_impuesto_renta = $model->obtener_tipo_impuesto_a_la_renta();
        $tipo_impuesto_renta = mb_convert_encoding($tipo_impuesto_renta, 'UTF-8');

        return json_encode($tipo_impuesto_renta, JSON_UNESCAPED_UNICODE);
    }

    public function obtener_tipo_periodo_de_gracia()
    {
        $model =  new DataContrato();
        $tipo_periodo_de_gracia = $model->obtener_tipo_periodo_de_gracia();
        $tipo_periodo_de_gracia = mb_convert_encoding($tipo_periodo_de_gracia, 'UTF-8');

        return json_encode($tipo_periodo_de_gracia, JSON_UNESCAPED_UNICODE);
    }

    public function obtener_tipo_periodo()
    {
        $model =  new DataContrato();
        $tipo_periodo = $model->obtener_tipo_periodo();
        $tipo_periodo = mb_convert_encoding($tipo_periodo, 'UTF-8');

        return json_encode($tipo_periodo, JSON_UNESCAPED_UNICODE);
    }

    public function obtener_tipo_incrementos()
    {
        $model =  new DataContrato();
        $tipo_incrementos = $model->obtener_tipo_incrementos();
        $tipo_incrementos = mb_convert_encoding($tipo_incrementos, 'UTF-8');

        return json_encode($tipo_incrementos, JSON_UNESCAPED_UNICODE);
    }
    public function obtener_tipo_pago_incrementos()
    {
        $model =  new DataContrato();
        $tipo_pago_incrementos = $model->obtener_tipo_pago_incrementos();
        $tipo_pago_incrementos = mb_convert_encoding($tipo_pago_incrementos, 'UTF-8');

        return json_encode($tipo_pago_incrementos, JSON_UNESCAPED_UNICODE);
    }

    public function obtener_tipo_continuidad_pago()
    {
        $model =  new DataContrato();
        $tipo_tipo_continuidad_pago = $model->obtener_tipo_continuidad_pago();
        $tipo_tipo_continuidad_pago = mb_convert_encoding($tipo_tipo_continuidad_pago, 'UTF-8');

        return json_encode($tipo_tipo_continuidad_pago, JSON_UNESCAPED_UNICODE);
    }
    public function obtener_tipo_anios_incrementos()
    {
        $model =  new DataContrato();
        $tipo_tipo_anios_incrementos = $model->obtener_tipo_anios_incrementos();
        $tipo_tipo_anios_incrementos = mb_convert_encoding($tipo_tipo_anios_incrementos, 'UTF-8');

        return json_encode($tipo_tipo_anios_incrementos, JSON_UNESCAPED_UNICODE);
    }
    public function obtener_tipo_inflacion()
    {
        $model =  new DataContrato();
        $tipo_inflacion = $model->obtener_tipo_inflacion();
        $tipo_inflacion = mb_convert_encoding($tipo_inflacion, 'UTF-8');

        return json_encode($tipo_inflacion, JSON_UNESCAPED_UNICODE);
    }
    public function obtener_tipo_periodicidad()
    {
        $model =  new DataContrato();
        $tipo_periodicidad = $model->obtener_tipo_periodicidad();
        $tipo_periodicidad = mb_convert_encoding($tipo_periodicidad, 'UTF-8');

        return json_encode($tipo_periodicidad, JSON_UNESCAPED_UNICODE);
    }
    public function obtener_tipo_anio_mes()
    {
        $model =  new DataContrato();
        $tipo_anio_mes = $model->obtener_tipo_anio_mes();
        $tipo_anio_mes = mb_convert_encoding($tipo_anio_mes, 'UTF-8');

        return json_encode($tipo_anio_mes, JSON_UNESCAPED_UNICODE);
    }
    public function obtener_tipo_aplicacion()
    {
        $model =  new DataContrato();
        $tipo_aplicacion = $model->obtener_tipo_aplicacion();
        $tipo_aplicacion = mb_convert_encoding($tipo_aplicacion, 'UTF-8');

        return json_encode($tipo_aplicacion, JSON_UNESCAPED_UNICODE);
    }

    public function obtener_meses()
    {
        $model =  new DataContrato();
        $meses = $model->obtener_meses();
        $meses = mb_convert_encoding($meses, 'UTF-8');

        return json_encode($meses, JSON_UNESCAPED_UNICODE);
    }

    public function obtener_forma_pago()
    {
        $model =  new DataContrato();
        $forma_de_pago = $model->obtener_forma_pago();
        $forma_de_pago = mb_convert_encoding($forma_de_pago, 'UTF-8');

        return json_encode($forma_de_pago, JSON_UNESCAPED_UNICODE);
    }
    public function obtener_bancos()
    {
        $model =  new DataContrato();
        $obtener_bancos = $model->obtener_bancos();
        $obtener_bancos = mb_convert_encoding($obtener_bancos, 'UTF-8');

        return json_encode($obtener_bancos, JSON_UNESCAPED_UNICODE);
    }
    public function obtener_tipo_monto_a_depositar()
    {
        $model =  new DataContrato();
        $lista_tipos_montos_a_deposotar = $model->obtener_tipo_monto_a_depositar();
        $lista_tipos_montos_a_deposotar = mb_convert_encoding($lista_tipos_montos_a_deposotar, 'UTF-8');

        return json_encode($lista_tipos_montos_a_deposotar, JSON_UNESCAPED_UNICODE);
    }

    public function obtener_tipo_cuota_extraordinaria()
    {
        $model =  new DataContrato();
        $tipo_extraordinaria = $model->obtener_tipo_cuota_extraordinaria();
        $tipo_extraordinaria = mb_convert_encoding($tipo_extraordinaria, 'UTF-8');

        return json_encode($tipo_extraordinaria, JSON_UNESCAPED_UNICODE);
    }

    public function obtener_tipo_anexos()
    {
        $request = file_get_contents('php://input');
        $request = json_decode($request, true);
        $model =  new DataContrato();
        $tipo_anexos = $model->obtener_tipo_anexos($request);
        $tipo_anexos = mb_convert_encoding($tipo_anexos, 'UTF-8');
        return json_encode($tipo_anexos, JSON_UNESCAPED_UNICODE);
    }

    public function obtener_meses_adelantos()
    {
        $request = file_get_contents('php://input');
        $request = json_decode($request, true);
        $model =  new DataContrato();
        $meses_adelanto = $model->obtener_meses_adelantos($request);
        $meses_adelanto = mb_convert_encoding($meses_adelanto, 'UTF-8');
        return json_encode($meses_adelanto, JSON_UNESCAPED_UNICODE);
    }


    public function obtener_abogados()
    {
        $request = file_get_contents('php://input');
        $request = json_decode($request, true);
        $model =  new DataContrato();
        $abogados = $model->obtener_abogados($request);
        $abogados = mb_convert_encoding($abogados, 'UTF-8');
        return json_encode($abogados, JSON_UNESCAPED_UNICODE);
    }


    public function ObtenerAprobador()
    {
        $model =  new DataContrato();
        $nuevo_contrato = $model->obtener_aprobador();
        $nuevo_contrato_utf8 = mb_convert_encoding($nuevo_contrato, 'UTF-8');

        return json_encode($nuevo_contrato_utf8, JSON_UNESCAPED_UNICODE);
    }


    public function ObtenerCargoAprobador()
    {
        $model =  new DataContrato();
        $nuevo_contrato = $model->obtener_cargo_aprobador();
        $nuevo_contrato_utf8 = mb_convert_encoding($nuevo_contrato, 'UTF-8');

        return json_encode($nuevo_contrato_utf8, JSON_UNESCAPED_UNICODE);
    }
}
