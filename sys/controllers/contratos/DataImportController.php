<?php 

class DataImportController
{
    public function contrato_proveedores(){
        $db = Database::getInstance();
        $connection = $db->getConnection();
        try {


            $data = file_get_contents("../../controllers/data/data-contrato-proveedor-v1.md");
            $data = json_decode($data, true);
            
            $connection->beginTransaction();

            $data_contratos = $data['Contrato_Proveedores'];
            $data_representantes = $data['Representante_Legal'];
            $data_contraprestaciones = $data['Contraprestaciones'];

            $user_created_id = 4014;
            $created_at = date('Y-m-d H:i:s');

            ///IMPORTACION CONTRATOS
            $data = $data_contratos;
            for ($i=0; $i < count($data); $i++) { 
                // contrato
                $codigo = !isset($data[$i]['codigo']) ? '':$data[$i]['codigo'];
                $area_responsable_id = !isset($data[$i]['area_responsable_id']) ? '':$data[$i]['area_responsable_id'];
                $empresa_suscribe_id = !isset($data[$i]['empresa_suscribe_id']) ? '':$data[$i]['empresa_suscribe_id'];
                $persona_contacto_proveedor = !isset($data[$i]['persona_contacto_proveedor']) ? '':$data[$i]['persona_contacto_proveedor'];
                $gerente_area_id = !isset($data[$i]['gerente_area_id']) ? '':$data[$i]['gerente_area_id'];
                $gerente_area_nombre = !isset($data[$i]['gerente_area_nombre']) ? '':$data[$i]['gerente_area_nombre'];
                $gerente_area_email = !isset($data[$i]['gerente_area_email']) ? '':$data[$i]['gerente_area_email'];
                $ruc = !isset($data[$i]['ruc']) ? '':$data[$i]['ruc'];
                $razon_social = !isset($data[$i]['razon_social']) ? '':$data[$i]['razon_social'];
                $detalle_servicio = !isset($data[$i]['detalle_servicio']) ? '':$data[$i]['detalle_servicio'];
                $periodo_numero = !isset($data[$i]['periodo_numero']) ? '':$data[$i]['periodo_numero'];
                $periodo = !isset($data[$i]['periodo']) ? '':$data[$i]['periodo'];
                $fecha_inicio = !isset($data[$i]['fecha_inicio']) ? '':$data[$i]['fecha_inicio'];
                $alcance_servicio = !isset($data[$i]['alcance_servicio']) ? '':$data[$i]['alcance_servicio'];
                $tipo_terminacion_anticipada_id = !isset($data[$i]['tipo_terminacion_anticipada_id']) ? '':$data[$i]['tipo_terminacion_anticipada_id'];
                $terminacion_anticipada = !isset($data[$i]['terminacion_anticipada']) ? '':$data[$i]['terminacion_anticipada'];
                $observaciones = !isset($data[$i]['observaciones']) ? '':$data[$i]['observaciones'];
                $etapa_id = !isset($data[$i]['etapa_id']) ? '':$data[$i]['etapa_id'];
                $categoria_id = !isset($data[$i]['categoria_id']) ? '':$data[$i]['categoria_id'];
                $tipo_contrato_proveedor_id = !isset($data[$i]['tipo_contrato_proveedor_id']) ? '':$data[$i]['tipo_contrato_proveedor_id'];
                $tipo_firma_id = !isset($data[$i]['tipo_firma_id']) ? '':$data[$i]['tipo_firma_id'];
                $fecha_suscripcion_proveedor = !isset($data[$i]['fecha_suscripcion_proveedor']) ? '':$data[$i]['fecha_suscripcion_proveedor'];
                $fecha_vencimiento_indefinida_id = !isset($data[$i]['fecha_vencimiento_indefinida_id']) ? '':$data[$i]['fecha_vencimiento_indefinida_id'];
                $fecha_vencimiento_proveedor = !isset($data[$i]['fecha_vencimiento_proveedor']) ? '':$data[$i]['fecha_vencimiento_proveedor'];
                $status = 1;
                $usuario_contrato_proveedor_aprobado_id = !isset($data[$i]['usuario_contrato_proveedor_aprobado_id']) ? '':$data[$i]['usuario_contrato_proveedor_aprobado_id'];

                if (!Empty($area_responsable_id)) {
                    $area_responsable_id = explode("|", $area_responsable_id);
                    $area_responsable_id = trim($area_responsable_id[0]);
                }
                if (!Empty($empresa_suscribe_id)) {
                    $empresa_suscribe_id = explode("|", $empresa_suscribe_id);
                    $empresa_suscribe_id = trim($empresa_suscribe_id[0]);
                }
                if (!Empty($gerente_area_id)) {
                    $gerente_area_id = explode("|", $gerente_area_id);
                    $gerente_area_id = trim($gerente_area_id[0]);
                }
                if (!Empty($periodo)) {
                    $periodo = explode("|", $periodo);
                    $periodo = trim($periodo[0]);
                }
                if (!Empty($tipo_terminacion_anticipada_id)) {
                    $tipo_terminacion_anticipada_id = explode("|", $tipo_terminacion_anticipada_id);
                    $tipo_terminacion_anticipada_id = trim($tipo_terminacion_anticipada_id[0]);
                }
                if (!Empty($etapa_id)) {
                    $etapa_id = explode("|", $etapa_id);
                    $etapa_id = trim($etapa_id[0]);
                }
                if (!Empty($categoria_id)) {
                    $categoria_id = explode("|", $categoria_id);
                    $categoria_id = trim($categoria_id[0]);
                }
                if (!Empty($tipo_contrato_proveedor_id)) {
                    $tipo_contrato_proveedor_id = explode("|", $tipo_contrato_proveedor_id);
                    $tipo_contrato_proveedor_id = trim($tipo_contrato_proveedor_id[0]);
                }
                if (!Empty($tipo_firma_id)) {
                    $tipo_firma_id = explode("|", $tipo_firma_id);
                    $tipo_firma_id = trim($tipo_firma_id[0]);
                }
                if (!Empty($fecha_vencimiento_indefinida_id)) {
                    $fecha_vencimiento_indefinida_id = explode("|", $fecha_vencimiento_indefinida_id);
                    $fecha_vencimiento_indefinida_id = trim($fecha_vencimiento_indefinida_id[0]);
                }
                if (!Empty($usuario_contrato_proveedor_aprobado_id)) {
                    $usuario_contrato_proveedor_aprobado_id = explode("|", $usuario_contrato_proveedor_aprobado_id);
                    $usuario_contrato_proveedor_aprobado_id = trim($usuario_contrato_proveedor_aprobado_id[0]);
                }
        
        
                $fecha_inicio = !Empty($fecha_inicio) ? "'".$fecha_inicio." ".date('H:i:s')."'":"NULL";
                $fecha_suscripcion_proveedor = !Empty($fecha_suscripcion_proveedor) ? "'".$fecha_suscripcion_proveedor."'":"NULL";
                $fecha_vencimiento_proveedor = !Empty($fecha_vencimiento_proveedor) ? "'".$fecha_vencimiento_proveedor."'":"NULL";
        
                
                $gerente_area_id = $gerente_area_id == "" ? "NULL" : $gerente_area_id;
                $tipo_terminacion_anticipada_id = $tipo_terminacion_anticipada_id == "" ? 0 : $tipo_terminacion_anticipada_id;
                $categoria_id = $categoria_id == "" ? 0 : $categoria_id;
                $tipo_contrato_proveedor_id = $tipo_contrato_proveedor_id == "" ? 0 : $tipo_contrato_proveedor_id;
                $tipo_firma_id = $tipo_firma_id == "" ? 0 : $tipo_firma_id;
                $fecha_vencimiento_indefinida_id = $fecha_vencimiento_indefinida_id == "" ? 0 : $fecha_vencimiento_indefinida_id;
                
                
                if ($gerente_area_id == 'A') {
                    $gerente_area_id = "NULL";
                    $gerente_area_nombre = trim($gerente_area_nombre);
                    $gerente_area_email = trim($gerente_area_email);
                }else{
                    $gerente_area_nombre = '';
                    $gerente_area_email = '';
                }
        
            

                
                $data_proveedor['codigo'] = $codigo;
                $data_proveedor['area_responsable_id'] = $area_responsable_id;
                $data_proveedor['empresa_suscribe_id'] = $empresa_suscribe_id;
                $data_proveedor['persona_contacto_proveedor'] = $persona_contacto_proveedor;
                $data_proveedor['gerente_area_id'] = $gerente_area_id;
                $data_proveedor['gerente_area_nombre'] = $gerente_area_nombre;
                $data_proveedor['gerente_area_email'] = $gerente_area_email;
                $data_proveedor['ruc'] = $ruc;
                $data_proveedor['razon_social'] = $razon_social;
                $data_proveedor['detalle_servicio'] = $detalle_servicio;
                $data_proveedor['periodo_numero'] = $periodo_numero;
                $data_proveedor['periodo'] = $periodo;
                $data_proveedor['fecha_inicio'] = $fecha_inicio;
                $data_proveedor['alcance_servicio'] = $alcance_servicio;
                $data_proveedor['tipo_terminacion_anticipada_id'] = $tipo_terminacion_anticipada_id;
                $data_proveedor['terminacion_anticipada'] = $terminacion_anticipada;
                $data_proveedor['observaciones'] = $observaciones;
                $data_proveedor['etapa_id'] = $etapa_id;
                $data_proveedor['categoria_id'] = $categoria_id;
                $data_proveedor['tipo_contrato_proveedor_id'] = $tipo_contrato_proveedor_id;
                $data_proveedor['tipo_firma_id'] = $tipo_firma_id;
                $data_proveedor['fecha_suscripcion_proveedor'] = $fecha_suscripcion_proveedor;
                $data_proveedor['fecha_vencimiento_indefinida_id'] = $fecha_vencimiento_indefinida_id;
                $data_proveedor['fecha_vencimiento_proveedor'] = $fecha_vencimiento_proveedor;
                $data_proveedor['status'] = $status;
                $data_proveedor['usuario_contrato_proveedor_aprobado_id'] = $usuario_contrato_proveedor_aprobado_id;
                $data_proveedor['user_created_id'] = $user_created_id;
                $data_proveedor['created_at'] = date("Y-m-d H:i:s");

                

                if (!Empty($codigo)) {
                    $correlativo = new Correlativo();
                    $correlativo = $correlativo->obtener_correlativo(2);
                    if ($correlativo['status'] == 404) {
                        $connection->rollback();
                        return json_encode($correlativo,JSON_UNESCAPED_UNICODE);
                    }

                    $data_proveedor['numero_correlativo'] = $correlativo['result']['numero'];

                    $contrato_proveedor = new DataImport();
                    $contrato_insert = $contrato_proveedor->registrar_contrato_proveedor($data_proveedor) ;
                    if ($contrato_insert['status'] == 404) {
                        $contrato_insert['code'] = $codigo;
                        $connection->rollback();
                        return json_encode($contrato_insert,JSON_UNESCAPED_UNICODE);
                    }
                        
                    // INICIO INSERTAR EN CONTRATO
                    
        
    
                }
            }

            $data_error = [];
            $data = $data_representantes;
            for ($i=0; $i < count($data); $i++) {
                $codigo = !isset($data[$i]['codigo']) ? '':$data[$i]['codigo'];
                $dni_representante = !isset($data[$i]['dni_representante']) ? '':$data[$i]['dni_representante'];
                $nombre_representante = !isset($data[$i]['nombre_representante']) ? '':$data[$i]['nombre_representante'];
                $id_banco = !isset($data[$i]['id_banco']) ? '':$data[$i]['id_banco'];
                $nro_cuenta = !isset($data[$i]['nro_cuenta']) ? '':$data[$i]['nro_cuenta'];
                $nro_cci = !isset($data[$i]['nro_cci']) ? '':$data[$i]['nro_cci'];

                if (!Empty($id_banco)) {
                    $id_banco = explode("|", $id_banco);
                    $id_banco = trim($id_banco[0]);
                }else{
                    $id_banco = 0;
                }

                if (!Empty($codigo)) {
                    $data_repressentate_legal['codigo'] = $codigo;
                    $data_repressentate_legal['dni_representante'] = $dni_representante;
                    $data_repressentate_legal['nombre_representante'] = $nombre_representante;
                    $data_repressentate_legal['id_banco'] = $id_banco;
                    $data_repressentate_legal['nro_cuenta'] = $nro_cuenta;
                    $data_repressentate_legal['nro_cci'] = $nro_cci;
                    $data_repressentate_legal['user_created_id'] = $user_created_id;
                    $data_repressentate_legal['created_at'] = $created_at;

                    $contrato_representante = new DataImport();
                    $res_contrato = $contrato_representante->obtener_contrato_por_codigo($data_repressentate_legal['codigo']);
                    if ($res_contrato['status'] == 404) {
                        $connection->rollback();
                        return json_encode($res_contrato,JSON_UNESCAPED_UNICODE);
                    }
                    $data_repressentate_legal['contrato_id'] = $res_contrato['result']['contrato_id'];
                    
                    $contrato_insert = $contrato_representante->registrar_representante_legal($data_repressentate_legal);
                    if ($contrato_insert['status'] == 404) {
                        $connection->rollback();
                        return json_encode($contrato_insert,JSON_UNESCAPED_UNICODE);
                    }

                }else{
                    array_push($data_error,$codigo);
                }
            }

            $data = $data_contraprestaciones;
            for ($i=0; $i < count($data); $i++) {

                $codigo = !isset($data[$i]['codigo']) ? '':$data[$i]['codigo'];
                $moneda_id = !isset($data[$i]['moneda_id']) ? '':$data[$i]['moneda_id'];
                $subtotal = !isset($data[$i]['subtotal']) ? 0:$data[$i]['subtotal'];
                $igv = !isset($data[$i]['igv']) ? 0:$data[$i]['igv'];
                $monto = !isset($data[$i]['monto']) ? 0:$data[$i]['monto'];
                $tipo_comprobante_id = !isset($data[$i]['tipo_comprobante_id']) ? '':$data[$i]['tipo_comprobante_id'];
                $plazo_pago = !isset($data[$i]['plazo_pago']) ? '':$data[$i]['plazo_pago'];
                $forma_pago_detallado = !isset($data[$i]['forma_pago_detallado']) ? '':$data[$i]['forma_pago_detallado'];
        
        
                if (!Empty($moneda_id)) {
                    $moneda_id = explode("|", $moneda_id);
                    $moneda_id = trim($moneda_id[0]);
                }
                if (!Empty($tipo_comprobante_id)) {
                    $tipo_comprobante_id = explode("|", $tipo_comprobante_id);
                    $tipo_comprobante_id = trim($tipo_comprobante_id[0]);
                }

                if (!Empty($codigo)) {

                    $data_contraprestacion['codigo'] = $codigo;
                    $data_contraprestacion['moneda_id'] = $moneda_id;
                    $data_contraprestacion['subtotal'] = $subtotal;
                    $data_contraprestacion['igv'] = $igv;
                    $data_contraprestacion['monto'] = $monto;
                    $data_contraprestacion['tipo_comprobante_id'] = $tipo_comprobante_id;
                    $data_contraprestacion['plazo_pago'] = $plazo_pago;
                    $data_contraprestacion['forma_pago_detallado'] = $forma_pago_detallado;
                    $data_contraprestacion['user_created_id'] = $user_created_id;
                    $data_contraprestacion['created_at'] = $created_at;

                    $contrato_representante = new DataImport();
                    $res_contrato = $contrato_representante->obtener_contrato_por_codigo($data_contraprestacion['codigo']);
                    if ($res_contrato['status'] == 404) {
                        $connection->rollback();
                        return json_encode($res_contrato,JSON_UNESCAPED_UNICODE);
                    }
                    $data_contraprestacion['contrato_id'] = $res_contrato['result']['contrato_id'];
                    
                    $contrato_insert = $contrato_representante->registrar_contraprestacion($data_contraprestacion);
                    if ($contrato_insert['status'] == 404) {
                        $connection->rollback();
                        return json_encode($contrato_insert,JSON_UNESCAPED_UNICODE);
                    }

                }else{
                    array_push($data_error,$codigo);
                }
            }

            $connection->commit();
            $result['status'] = 200; 
            $result['message'] = 'Se ha registrado con exito';
            $result['data_error'] = $data_error;
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $connection->rollback();
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error';
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
    }

    public function update_contrato_arrendamiento(){
        $db = Database::getInstance();
        $connection = $db->getConnection();
        try {


            $data = file_get_contents("../../controllers/data/data-contrato-arrendamiento-v1.md");
            
            $data = json_decode($data, true);
            
            $connection->beginTransaction();

            $data_contratos = $data['Actualizacion_Contratos'];
            $data_responsable_ir = $data['Registro_Responsable_IR'];
            $data_update_incremento = $data['Actualizacion_Incrementos'];
            $data_registro_incremento = $data['Registro_Incrementos'];
            $data_registro_inflaciones = $data['Registro_Inflaciones'];

            $user_created_id = 4014;
            $created_at = date('Y-m-d H:i:s');
            $data_error = [];
            $data_error_condicion_enconomica = [];
            $data_error_responsable_ir = [];
            $data_error_registro_incremento = [];
            $data_error_registro_inflaciones = [];
            ///IMPORTACION CONTRATOS
            $data = $data_contratos;
            for ($i=0; $i < count($data); $i++) { 
                // contrato
                if (isset($data[$i]['cc_id']) && !Empty($data[$i]['cc_id'])) {
                    $cc_id = !isset($data[$i]['cc_id']) ? '':$data[$i]['cc_id'];
                    //$condicion_economica_id = !isset($data[$i]['condicion_economica_id']) ? '': $data[$i]['condicion_economica_id'];
                    //$contrato_id = !isset($data[$i]['contrato_id']) ? '':$data[$i]['contrato_id'];
                    $moneda = !isset($data[$i]['moneda']) ? '':$data[$i]['moneda'];
                    $monto_renta = !isset($data[$i]['monto_renta']) ? '':$data[$i]['monto_renta'];
                    $periodo = !isset($data[$i]['periodo']) ? '':$data[$i]['periodo'];
                    $fecha_inicio = !isset($data[$i]['fecha_inicio']) ? '':$data[$i]['fecha_inicio'];
                    $fecha_fin = !isset($data[$i]['fecha_fin']) ? '':$data[$i]['fecha_fin'];
                    $impuesto_a_la_renta = !isset($data[$i]['impuesto_a_la_renta']) ? '':$data[$i]['impuesto_a_la_renta'];
                    $carta_instruccion = !isset($data[$i]['carta_instruccion']) ? '':$data[$i]['carta_instruccion'];
                
                    if (!Empty($moneda)) {
                        $moneda = explode("|", $moneda);
                        $moneda = trim($moneda[0]);
                    }
                    if (!Empty($impuesto_a_la_renta)) {
                        $impuesto_a_la_renta = explode("|", $impuesto_a_la_renta);
                        $impuesto_a_la_renta = trim($impuesto_a_la_renta[0]);
                    }
                    if (!Empty($impuesto_a_la_renta)) {
                        $periodo = explode("|", $periodo);
                        $periodo = trim($periodo[0]);
                    }
                    if (!Empty($carta_instruccion)) {
                        $carta_instruccion = explode("|", $carta_instruccion);
                        $carta_instruccion = trim($carta_instruccion[0]);
                    }
                    $fecha_inicio = !Empty($fecha_inicio) ? "'".$fecha_inicio."'": 'NULL';
                    $fecha_fin = !Empty($fecha_fin) ? "'".$fecha_fin."'": 'NULL';
                    
                    $data_condicion_economica['cc_id'] = $cc_id;
                    //$data_condicion_economica['contrato_id'] = $contrato_id;
                    //$data_condicion_economica['condicion_economica_id'] = $condicion_economica_id;
                    $data_condicion_economica['moneda'] = $moneda;
                    $data_condicion_economica['monto_renta'] = $monto_renta;
                    $data_condicion_economica['periodo'] = $periodo;
                    $data_condicion_economica['fecha_inicio'] = $fecha_inicio;
                    $data_condicion_economica['fecha_fin'] = $fecha_fin;
                    $data_condicion_economica['impuesto_a_la_renta'] = $impuesto_a_la_renta;
                    $data_condicion_economica['carta_instruccion'] = $carta_instruccion;
                    $data_condicion_economica['user_updated_id'] = $user_created_id;
                    $data_condicion_economica['updated_at'] = $created_at;
            

                    $contrato_import = new DataImport();
                    $resul_codigo = $contrato_import->obtener_condicion_economica_por_cc_id($cc_id);
                    

                    if (isset($resul_codigo['result']['condicion_economica_id']) && !Empty($resul_codigo['result']['condicion_economica_id'])) {
                        $data_condicion_economica['contrato_id'] = $resul_codigo['result']['contrato_id'];
                        $data_condicion_economica['condicion_economica_id'] = $resul_codigo['result']['condicion_economica_id'];
    
                        $contrato_import = new DataImport();
                        $contrato_update = $contrato_import->update_condicion_economica_v1($data_condicion_economica) ;
                        if ($contrato_update['status'] == 404) {
                            $contrato_update['code'] = $data_condicion_economica;
                            $connection->rollback();
                            return json_encode($contrato_update,JSON_UNESCAPED_UNICODE);
                        }
                    }else{
                        array_push($data_error_condicion_enconomica,$data_condicion_economica);
                    }

                   
             
                }
            }
            /*
            $data = $data_responsable_ir;
            for ($i=0; $i < count($data); $i++) { 
                // contrato
                if (isset($data[$i]['cc_id']) && !Empty($data[$i]['cc_id'])) {
                    $cc_id = !isset($data[$i]['cc_id']) ? '':$data[$i]['cc_id'];
                    $tipo_documento_id = !isset($data[$i]['tipo_documento_id']) ? '':$data[$i]['tipo_documento_id'];
                    $num_documento = !isset($data[$i]['num_documento']) ? '':$data[$i]['num_documento'];
                    if (!Empty($tipo_documento_id)) {
                        $tipo_documento_id = explode("|", $tipo_documento_id);
                        $tipo_documento_id = trim($tipo_documento_id[0]);
                    }
    
                    $data_responasble_ir['cc_id'] = $cc_id;
                    $data_responasble_ir['tipo_documento_id'] = $tipo_documento_id;
                    $data_responasble_ir['num_documento'] = $num_documento;
                    $data_responasble_ir['user_created_id'] = $user_created_id;
                    $data_responasble_ir['created_at'] = $created_at;
            
                    $contrato_import = new DataImport();
                    $resul_codigo = $contrato_import->obtener_contrato_por_cc_id($cc_id);
                    if ($resul_codigo['status'] == 404) {
                        $resul_codigo['cc_id'] = $cc_id;
                        $connection->rollback();
                        return json_encode($resul_codigo,JSON_UNESCAPED_UNICODE);
                    }

                    if (isset($resul_codigo['result']['contrato_detalle_id']) && !Empty($resul_codigo['result']['contrato_detalle_id'])) {
                        $data_responasble_ir['contrato_id'] = $resul_codigo['result']['contrato_id'];
                        $data_responasble_ir['contrato_detalle_id'] = $resul_codigo['result']['contrato_detalle_id'];
                        $contrato_update = $contrato_import->registrar_responsable_ir_v1($data_responasble_ir) ;
                        if ($contrato_update['status'] == 404) {
                            $connection->rollback();
                            return json_encode($contrato_update,JSON_UNESCAPED_UNICODE);
                        }
                    }else{
                        array_push($data_error_responsable_ir,$data_responasble_ir);
                    }
                    
             
                }
            }

            $data = $data_update_incremento;
            for ($i=0; $i < count($data); $i++) { 
                // contrato
                if (isset($data[$i]['id']) && !Empty($data[$i]['id'])) {
                    $id = !isset($data[$i]['id']) ? '':$data[$i]['id'];
                    $eliminado = !isset($data[$i]['eliminado']) ? 0:$data[$i]['eliminado'];
                    $valor = !isset($data[$i]['valor']) ? '':$data[$i]['valor'];
                    $tipo_valor = !isset($data[$i]['tipo_valor']) ? '':$data[$i]['tipo_valor'];
                    $tipo_continuidad = !isset($data[$i]['tipo_continuidad']) ? '':$data[$i]['tipo_continuidad'];
                    $anio = !isset($data[$i]['anio']) ? '':$data[$i]['anio'];
                
                    if (!Empty($tipo_valor)) {
                        $tipo_valor = explode("|", $tipo_valor);
                        $tipo_valor = trim($tipo_valor[0]);
                    }
                    if (!Empty($tipo_continuidad)) {
                        $tipo_continuidad = explode("|", $tipo_continuidad);
                        $tipo_continuidad = trim($tipo_continuidad[0]);
                    }
                    if (!Empty($anio)) {
                        $anio = explode("|", $anio);
                        $anio = trim($anio[0]);
                    }
            
                    $data_incremento['id'] = $id;
                    $data_incremento['valor'] = $valor;
                    $data_incremento['tipo_valor'] = $tipo_valor;
                    $data_incremento['tipo_continuidad'] = $tipo_continuidad;
                    $data_incremento['anio'] = $anio;
                    $data_incremento['user_updated_id'] = $user_created_id;
                    $data_incremento['updated_at'] = $created_at;
                    $data_incremento['estado'] = $eliminado == 1 ? 0 : 1;

                    $contrato_import = new DataImport();
                    $contrato_update = $contrato_import->update_incremento_v1($data_incremento) ;
                    if ($contrato_update['status'] == 404) {
                        $contrato_update['code'] = $id;
                        $connection->rollback();
                        return json_encode($contrato_update,JSON_UNESCAPED_UNICODE);
                    }
             
                }
            }

            $data = $data_registro_incremento;
            for ($i=0; $i < count($data); $i++) { 
                // contrato
                if (isset($data[$i]['cc_id']) && !Empty($data[$i]['cc_id'])) {
                    $cc_id = !isset($data[$i]['cc_id']) ? '':$data[$i]['cc_id'];
                    $valor = !isset($data[$i]['valor']) ? '':$data[$i]['valor'];
                    $tipo_valor = !isset($data[$i]['tipo_valor']) ? '':$data[$i]['tipo_valor'];
                    $tipo_continuidad = !isset($data[$i]['tipo_continuidad']) ? '':$data[$i]['tipo_continuidad'];
                    $anio = !isset($data[$i]['anio']) ? '':$data[$i]['anio'];
                
                    if (!Empty($tipo_valor)) {
                        $tipo_valor = explode("|", $tipo_valor);
                        $tipo_valor = trim($tipo_valor[0]);
                    }
                    if (!Empty($tipo_continuidad)) {
                        $tipo_continuidad = explode("|", $tipo_continuidad);
                        $tipo_continuidad = trim($tipo_continuidad[0]);
                    }
                    if (!Empty($anio)) {
                        $anio = explode("|", $anio);
                        $anio = trim($anio[0]);
                    }
            
                    $data_incremento['cc_id'] = $cc_id;
                    $data_incremento['valor'] = $valor;
                    $data_incremento['tipo_valor'] = $tipo_valor;
                    $data_incremento['tipo_continuidad'] = $tipo_continuidad;
                    $data_incremento['anio'] = $anio;
                    $data_incremento['user_created_id'] = $user_created_id;
                    $data_incremento['created_at'] = $created_at;

                    $contrato_import = new DataImport();
                    $resul_codigo = $contrato_import->obtener_contrato_por_cc_id($cc_id);
                    if ($resul_codigo['status'] == 404) {
                        $resul_codigo['cc_id'] = $cc_id;
                        $connection->rollback();
                        return json_encode($resul_codigo,JSON_UNESCAPED_UNICODE);
                    }

                    if (isset($resul_codigo['result']['contrato_detalle_id']) && !Empty($resul_codigo['result']['contrato_detalle_id'])) {
                        $data_incremento['contrato_id'] = $resul_codigo['result']['contrato_id'];
                        $data_incremento['contrato_detalle_id'] = $resul_codigo['result']['contrato_detalle_id'];
    
                        $contrato_import = new DataImport();
                        $contrato_update = $contrato_import->registrar_incremento_v1($data_incremento) ;
                        if ($contrato_update['status'] == 404) {
                            $contrato_update['code'] = $id;
                            $connection->rollback();
                            return json_encode($contrato_update,JSON_UNESCAPED_UNICODE);
                        }
                    }else{
                        array_push($data_error_registro_incremento,$data_incremento);
                    }

                   
             
                }
            }

            $data = $data_registro_inflaciones;
            for ($i=0; $i < count($data); $i++) { 
                // contrato
                if (isset($data[$i]['cc_id']) && !Empty($data[$i]['cc_id'])) {
                    $cc_id = !isset($data[$i]['cc_id']) ? '':$data[$i]['cc_id'];
                    $tipo_periodicidad_id = !isset($data[$i]['tipo_periodicidad_id']) ? '':$data[$i]['tipo_periodicidad_id'];
                    $numero = !isset($data[$i]['numero']) ? '':$data[$i]['numero'];
                    $fecha = !isset($data[$i]['fecha']) ? '':$data[$i]['fecha'];
                    $moneda_id = !isset($data[$i]['moneda_id']) ? '':$data[$i]['moneda_id'];
                    $tipo_anio_mes = !isset($data[$i]['tipo_anio_mes']) ? '':$data[$i]['tipo_anio_mes'];
                    $tipo_aplicacion_id = !isset($data[$i]['tipo_aplicacion_id']) ? '':$data[$i]['tipo_aplicacion_id'];
                
                    if (!Empty($tipo_periodicidad_id)) {
                        $tipo_periodicidad_id = explode("|", $tipo_periodicidad_id);
                        $tipo_periodicidad_id = trim($tipo_periodicidad_id[0]);
                    }
                    if (!Empty($tipo_anio_mes)) {
                        $tipo_anio_mes = explode("|", $tipo_anio_mes);
                        $tipo_anio_mes = trim($tipo_anio_mes[0]);
                    }
                    if (!Empty($tipo_aplicacion_id)) {
                        $tipo_aplicacion_id = explode("|", $tipo_aplicacion_id);
                        $tipo_aplicacion_id = trim($tipo_aplicacion_id[0]);
                    }
                    if (!Empty($moneda_id)) {
                        $moneda_id = explode("|", $moneda_id);
                        $moneda_id = trim($moneda_id[0]);
                    }
            
                    $data_inflacion['tipo_periodicidad_id'] = $tipo_periodicidad_id;
                    $data_inflacion['fecha'] = $fecha;
                    $data_inflacion['numero'] = $numero;
                    $data_inflacion['tipo_anio_mes'] = $tipo_anio_mes;
                    $data_inflacion['tipo_aplicacion_id'] = $tipo_aplicacion_id;
                    $data_inflacion['moneda_id'] = $moneda_id;
                    $data_inflacion['user_created_id'] = $user_created_id;
                    $data_inflacion['created_at'] = $created_at;

                    $contrato_import = new DataImport();
                    $resul_codigo = $contrato_import->obtener_contrato_por_cc_id($cc_id);
                    if ($resul_codigo['status'] == 404) {
                        $resul_codigo['cc_id'] = $cc_id;
                        $connection->rollback();
                        return json_encode($resul_codigo,JSON_UNESCAPED_UNICODE);
                    }

                    if (isset($resul_codigo['result']['contrato_detalle_id']) && !Empty($resul_codigo['result']['contrato_detalle_id'])) {

                        $data_inflacion['contrato_id'] = $resul_codigo['result']['contrato_id'];
                        $data_inflacion['contrato_detalle_id'] = $resul_codigo['result']['contrato_detalle_id'];
    
                        $contrato_update = $contrato_import->registrar_inflacion_v1($data_inflacion) ;
                        if ($contrato_update['status'] == 404) {
                            $contrato_update['code'] = $id;
                            $connection->rollback();
                            return json_encode($contrato_update,JSON_UNESCAPED_UNICODE);
                        }
    
                    }else{
                        array_push($data_error_registro_inflaciones,$data_inflacion);
                    }
             

                    
                }
            }
           */


            $connection->commit();
            $result['status'] = 200; 
            $result['message'] = 'Se ha actualizado con exito';
            $result['data_error'] = [
                'data_error_condicion_enconomica' => $data_error_condicion_enconomica,
                'data_error_responsable_ir' => $data_error_responsable_ir,
                'data_error_registro_incremento' => $data_error_registro_incremento,
                'data_error_registro_inflaciones' => $data_error_registro_inflaciones
            ];

            return json_encode($result,JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $connection->rollback();
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error';
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
    }


    public function update_adenda_contrato_arrendamiento(){
        $db = Database::getInstance();
        $connection = $db->getConnection();
        try {
            $connection->beginTransaction();

            $model = new DataImport();
            $update_adenda = $model->update_adenda_detalle([]);
            if ($update_adenda['status'] == 404) {
                $connection->rollback();
                return json_encode($update_adenda,JSON_UNESCAPED_UNICODE);
            }

            $connection->commit();
            return json_encode($update_adenda,JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $connection->rollback();
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error';
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
    }



    public function obtener_contratos_arrendamiento_v2(){
        try {
          

            $response_contrato_success = [];
            $response_contrato_error = [];
            $response_contrato_error_message = [];

            $request = file_get_contents('php://input');
            $request = json_decode($request,true); 


            $file_name = $request['file_name'];
            $path_file = "../../controllers/data/get_nuevos_locales/".$file_name;
            if (!file_exists($path_file)) {
                $result['status'] = 404;
                $result['result'] = "";
                $result['message'] = 'El archivo no existe';
                return json_encode($result,JSON_UNESCAPED_UNICODE);
            }
            $data = file_get_contents($path_file);
            $data = json_decode($data, true);

            foreach ($data as $be) {
                $model = new ContratoArrendamientoV2();
                $data_local['cc_id'] = $be['cc_id'];
                $data_local['new_cc_id'] = $be['new_cc_id'];
                $data_local['zona_id'] = $be['zona_id'];
          
                $contrato_arrendamiento = $model->obtener_contrato_por_cc_id($data_local);
                if ($contrato_arrendamiento['status'] == 404) {
                    array_push($response_contrato_error, $data_local['cc_id']);
                    array_push($response_contrato_error_message, $contrato_arrendamiento);
                }else{
                    array_push($response_contrato_success, $contrato_arrendamiento['result']);
                }
            }

            $response = [
                'status' => 200,
                'result' => [
                    'response_contrato_success' => $response_contrato_success,
                    'response_contrato_error' => $response_contrato_error,
                    'response_contrato_error_message' => $response_contrato_error_message,
                    'cantidad_success' => count($response_contrato_success),
                    'cantidad_error' => count($response_contrato_error),
                ],
                'message' => 'Se ah obtenido datos de gestion',
            ];
                
            // $connection->commit();
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            // $connection->rollback();
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error';
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
    }


    public function registrar_contratos_arrendamiento_v2(){
        $db = Database::getInstance();
        $connection = $db->getConnection();
        try {
            $connection->beginTransaction();
            $data = file_get_contents("../../controllers/data/nuevos_contratos_arrendamiento.md");
            $data = json_decode($data, true);

            $response_contrato_success = [];
            $response_contrato_error = [];
            foreach ($data as $data_contrato) {
                
                $correlativo = new Correlativo();
                $correlativo = $correlativo->obtener_correlativo(1);

                $contrato = $data_contrato['contrato'];

                
                $nombre_tienda = !Empty($contrato['nombre_tienda']) ? "'".$contrato['nombre_tienda']."'" : "null";
                $nombre_tienda = str_replace("Red AT", "Red IGH", $nombre_tienda);

                $dt_contrato['contrato_id'] = !Empty($contrato['contrato_id']) ? "'".$contrato['contrato_id']."'" : "null";
                $dt_contrato['tipo_contrato_id'] = !Empty($contrato['tipo_contrato_id']) ? "'".$contrato['tipo_contrato_id']."'" : "null";
                $dt_contrato['empresa_suscribe_id'] = 30; // NUEVA EMPRESA INVERSIONES GAMING HOUSE SAC
                $dt_contrato['area_responsable_id'] = !Empty($contrato['area_responsable_id']) ? "'".$contrato['area_responsable_id']."'" : "null";
                $dt_contrato['persona_responsable_id'] = !Empty($contrato['persona_responsable_id']) ? "'".$contrato['persona_responsable_id']."'" : "null";
                $dt_contrato['nombre_tienda'] = $nombre_tienda;
                $dt_contrato['observaciones'] = !Empty($contrato['observaciones']) ? "'".$contrato['observaciones']."'" : "null";
                $dt_contrato['etapa_id'] = !Empty($contrato['etapa_id']) ? "'".$contrato['etapa_id']."'" : "null";
                $dt_contrato['estado_resolucion'] = !Empty($contrato['estado_resolucion']) ? "'".$contrato['estado_resolucion']."'" : "0";
                $dt_contrato['user_created_id'] = !Empty($contrato['user_created_id']) ? "'".$contrato['user_created_id']."'" : "null";
                $dt_contrato['created_at'] = !Empty($contrato['created_at']) ? "'".$contrato['created_at']."'" : "null";
                $dt_contrato['plazo_id'] = !Empty($contrato['plazo_id']) ? "'".$contrato['plazo_id']."'" : "null";
                $dt_contrato['declaracion_jurada_id'] = !Empty($contrato['declaracion_jurada_id']) ? "'".$contrato['declaracion_jurada_id']."'" : "null";
                $dt_contrato['verificar_giro'] = !Empty($contrato['verificar_giro']) ? "'".$contrato['verificar_giro']."'" : "null";
                $dt_contrato['fecha_verificacion_giro'] = !Empty($contrato['fecha_verificacion_giro']) ? "'".$contrato['fecha_verificacion_giro']."'" : "null";
                $dt_contrato['usuario_verificacion_giro'] = !Empty($contrato['usuario_verificacion_giro']) ? "'".$contrato['usuario_verificacion_giro']."'" : "null";
                $dt_contrato['cc_id'] = $data_contrato['contrato']['new_cc_id'];
                $dt_contrato['jefe_comercial_id'] = !Empty($contrato['jefe_comercial_id']) ? "'".$contrato['jefe_comercial_id']."'" : "null";
                $dt_contrato['estado_solicitud'] = !Empty($contrato['estado_solicitud']) ? "'".$contrato['estado_solicitud']."'" : "null";
                $dt_contrato['usuario_responsable_estado_solicitud'] = !Empty($contrato['usuario_responsable_estado_solicitud']) ? "'".$contrato['usuario_responsable_estado_solicitud']."'" : "null";
                $dt_contrato['alerta_enviada_id'] = !Empty($contrato['alerta_enviada_id']) ? "'".$contrato['alerta_enviada_id']."'" : "null";
                $dt_contrato['motivo_estado_na'] = !Empty($contrato['motivo_estado_na']) ? "'".$contrato['motivo_estado_na']."'" : "null";
                $dt_contrato['fecha_cambio_estado_solicitud'] = !Empty($contrato['fecha_cambio_estado_solicitud']) ? "'".$contrato['fecha_cambio_estado_solicitud']."'" : "null";
                $dt_contrato['dias_habiles'] = !Empty($contrato['dias_habiles']) ? "'".$contrato['dias_habiles']."'" : "0";
                $dt_contrato['usuario_responsable_estado_solicitud_primero'] = !Empty($contrato['usuario_responsable_estado_solicitud_primero']) ? "'".$contrato['usuario_responsable_estado_solicitud_primero']."'" : "null";
                $dt_contrato['codigo_correlativo'] = $correlativo['result']['numero'];
                $dt_contrato['migracion_id'] = 1;
                $dt_contrato['status'] = 1;
                
                $model = new ContratoArrendamientoV2();
                $res_contrato = $model->registrar_contrato($dt_contrato);
                if ($res_contrato['status'] == 404) {
                    $connection->rollback();
                    return json_encode($res_contrato,JSON_UNESCAPED_UNICODE);
                }
                $contrato_id = $res_contrato['result'];

                $contrato_detalle = $data_contrato['contrato_detalle'];
                foreach ($contrato_detalle as $data_contrato_detalle) {
                    $dt_contrato_detalle['contrato_id'] = $contrato_id;
                    $dt_contrato_detalle['codigo'] = $data_contrato_detalle['codigo'];
                    $dt_contrato_detalle['observaciones'] = !Empty($data_contrato_detalle['observaciones']) ? "'".$data_contrato_detalle['observaciones']."'" : "null";
                    $dt_contrato_detalle['estado_resolucion'] = !Empty($data_contrato_detalle['estado_resolucion']) ? "'".$data_contrato_detalle['estado_resolucion']."'" : "0";
                    $dt_contrato_detalle['status'] = 1;
                    $dt_contrato_detalle['user_created_id'] = $data_contrato_detalle['user_created_id'];
                    $dt_contrato_detalle['created_at'] = $data_contrato_detalle['created_at'];
                    $res_contrato_detalle = $model->registrar_contrato_detalle($dt_contrato_detalle);
                    if ($res_contrato_detalle['status'] == 404) {
                        $connection->rollback();
                        return json_encode($res_contrato_detalle,JSON_UNESCAPED_UNICODE);
                    }
                    $contrato_detalle_id = $res_contrato_detalle['result'];

                    //INMUEBLE
                    $inmueble = $data_contrato_detalle['inmueble'];
                    foreach ($inmueble as $data_inmueble) {
                        $dt_inmueble['contrato_id'] = $contrato_id;
                        $dt_inmueble['contrato_detalle_id'] = $contrato_detalle_id;
                        $dt_inmueble['ubigeo_id'] = !Empty($data_inmueble['ubigeo_id']) ? "'".$data_inmueble['ubigeo_id']."'":'null';
                        $dt_inmueble['ubicacion'] = !Empty($data_inmueble['ubicacion']) ? "'".$data_inmueble['ubicacion']."'":'null';
                        $dt_inmueble['area_arrendada'] = !Empty($data_inmueble['area_arrendada']) ? "'".$data_inmueble['area_arrendada']."'":"'0'";
                        $dt_inmueble['num_partida_registral'] = !Empty($data_inmueble['num_partida_registral']) ? "'".$data_inmueble['num_partida_registral']."'":"''";
                        $dt_inmueble['oficina_registral'] = !Empty($data_inmueble['oficina_registral']) ? "'".$data_inmueble['oficina_registral']."'":"''";
                        $dt_inmueble['num_suministro_agua'] = !Empty($data_inmueble['num_suministro_agua']) ? "'".$data_inmueble['num_suministro_agua']."'":"''";
                        $dt_inmueble['tipo_compromiso_pago_agua'] = !Empty($data_inmueble['tipo_compromiso_pago_agua']) ? "'".$data_inmueble['tipo_compromiso_pago_agua']."'":'0';
                        $dt_inmueble['monto_o_porcentaje_agua'] = !Empty($data_inmueble['monto_o_porcentaje_agua']) ? "'".$data_inmueble['monto_o_porcentaje_agua']."'":'0';
                        $dt_inmueble['ruc_agua'] = !Empty($data_inmueble['ruc_agua']) ? "'".$data_inmueble['ruc_agua']."'":"''";
                        $dt_inmueble['id_empresa_servicio_agua'] = !Empty($data_inmueble['id_empresa_servicio_agua']) ? "'".$data_inmueble['id_empresa_servicio_agua']."'":'0';
                        $dt_inmueble['num_suministro_luz'] = !Empty($data_inmueble['num_suministro_luz']) ? "'".$data_inmueble['num_suministro_luz']."'":"''";
                        $dt_inmueble['tipo_compromiso_pago_luz'] = !Empty($data_inmueble['tipo_compromiso_pago_luz']) ? "'".$data_inmueble['tipo_compromiso_pago_luz']."'":'0';
                        $dt_inmueble['monto_o_porcentaje_luz'] = !Empty($data_inmueble['monto_o_porcentaje_luz']) ? "'".$data_inmueble['monto_o_porcentaje_luz']."'":'0';
                        $dt_inmueble['ruc_luz'] = !Empty($data_inmueble['ruc_luz']) ? "'".$data_inmueble['ruc_luz']."'":"''";
                        $dt_inmueble['id_empresa_servicio_luz'] = !Empty($data_inmueble['id_empresa_servicio_luz']) ? "'".$data_inmueble['id_empresa_servicio_luz']."'":'0';
                        $dt_inmueble['tipo_compromiso_pago_arbitrios'] = !Empty($data_inmueble['tipo_compromiso_pago_arbitrios']) ? "'".$data_inmueble['tipo_compromiso_pago_arbitrios']."'":'0';
                        $dt_inmueble['porcentaje_pago_arbitrios'] = !Empty($data_inmueble['porcentaje_pago_arbitrios']) ? "'".$data_inmueble['porcentaje_pago_arbitrios']."'":'0';
                        $dt_inmueble['latitud'] = !Empty($data_inmueble['latitud']) ? "'".$data_inmueble['latitud']."'":"''";
                        $dt_inmueble['longitud'] = !Empty($data_inmueble['longitud']) ? "'".$data_inmueble['longitud']."'":"''";
                        $dt_inmueble['direccion_municipal'] = !Empty($data_inmueble['direccion_municipal']) ? "'".$data_inmueble['direccion_municipal']."'":"''";
                        $dt_inmueble['status'] = !Empty($data_inmueble['status']) ? "'".$data_inmueble['status']."'":'0';
                        $dt_inmueble['user_created_id'] = !Empty($data_inmueble['user_created_id']) ? "'".$data_inmueble['user_created_id']."'":'null';
                        $dt_inmueble['created_at'] = !Empty($data_inmueble['created_at']) ? "'".$data_inmueble['created_at']."'":'null';
                        $res_inmueble = $model->registrar_inmueble($dt_inmueble);
                        if ($res_inmueble['status'] == 404) {
                            $connection->rollback();
                            return json_encode($res_inmueble,JSON_UNESCAPED_UNICODE);
                        }
                        $inmueble_id = $res_inmueble['result'];

                        $suministro = $data_inmueble['suministros'];
                        foreach ($suministro as $data_suministro) {
                            $dt_suministro['contrato_id'] = $contrato_id;
                            $dt_suministro['inmueble_id'] = $inmueble_id;
                            $dt_suministro['tipo_servicio_id'] = !Empty($data_suministro['tipo_servicio_id']) ? "'".$data_suministro['tipo_servicio_id']."'":"0";
                            $dt_suministro['nro_suministro'] = !Empty($data_suministro['nro_suministro']) ? "'".$data_suministro['nro_suministro']."'":"''";
                            $dt_suministro['tipo_compromiso_pago_id'] = !Empty($data_suministro['tipo_compromiso_pago_id']) ? "'".$data_suministro['tipo_compromiso_pago_id']."'":"0";
                            $dt_suministro['monto_o_porcentaje'] = !Empty($data_suministro['monto_o_porcentaje']) ? "'".$data_suministro['monto_o_porcentaje']."'":"0";
                            $dt_suministro['status'] = !Empty($data_suministro['status']) ? "'".$data_suministro['status']."'":"0";
                            $dt_suministro['created_at'] = !Empty($data_suministro['created_at']) ? "'".$data_suministro['created_at']."'":"null";
                            $dt_suministro['user_created_id'] = !Empty($data_suministro['user_created_id']) ? "'".$data_suministro['user_created_id']."'":"null";
                            $res_suministro = $model->registrar_suministro($dt_suministro);
                            if ($res_suministro['status'] == 404) {
                                $connection->rollback();
                                return json_encode($res_suministro,JSON_UNESCAPED_UNICODE);
                            }
                        }   
                    }

                    //CONDICION ECONOMICA
                    $condicion_economica = $data_contrato_detalle['condicion_economica'];
                    foreach ($condicion_economica as $data_condicion_economica) {
                        $dt_condicion_economica['contrato_id'] = $contrato_id;
                        $dt_condicion_economica['contrato_detalle_id'] = $contrato_detalle_id;
                        $dt_condicion_economica['monto_renta'] = !Empty($data_condicion_economica['monto_renta']) ? "'".$data_condicion_economica['monto_renta']."'" : "0";
                        $dt_condicion_economica['tipo_moneda_id'] = !Empty($data_condicion_economica['tipo_moneda_id']) ? "'".$data_condicion_economica['tipo_moneda_id']."'" : "0";
                        $dt_condicion_economica['garantia_monto'] = !Empty($data_condicion_economica['garantia_monto']) ? "'".$data_condicion_economica['garantia_monto']."'" : "0";
                        $dt_condicion_economica['tipo_adelanto_id'] = !Empty($data_condicion_economica['tipo_adelanto_id']) ? "'".$data_condicion_economica['tipo_adelanto_id']."'" : "0";
                        $dt_condicion_economica['plazo_id'] = !Empty($data_condicion_economica['plazo_id']) ? "'".$data_condicion_economica['plazo_id']."'" : "null";
                        $dt_condicion_economica['cant_meses_contrato'] = !Empty($data_condicion_economica['cant_meses_contrato']) ? "'".$data_condicion_economica['cant_meses_contrato']."'" : "null";
                        $dt_condicion_economica['fecha_inicio'] = !Empty($data_condicion_economica['fecha_inicio']) ? "'".$data_condicion_economica['fecha_inicio']."'" : "null";
                        $dt_condicion_economica['fecha_fin'] = !Empty($data_condicion_economica['fecha_fin']) ? "'".$data_condicion_economica['fecha_fin']."'" : "null";
                        $dt_condicion_economica['num_alerta_vencimiento'] = !Empty($data_condicion_economica['num_alerta_vencimiento']) ? "'".$data_condicion_economica['num_alerta_vencimiento']."'" : "null";
                        $dt_condicion_economica['se_enviara_alerta'] = !Empty($data_condicion_economica['se_enviara_alerta']) ? "'".$data_condicion_economica['se_enviara_alerta']."'" : "null";
                        $dt_condicion_economica['cargo_mantenimiento'] = !Empty($data_condicion_economica['cargo_mantenimiento']) ? "'".$data_condicion_economica['cargo_mantenimiento']."'" : "null";
                        $dt_condicion_economica['fecha_suscripcion'] = !Empty($data_condicion_economica['fecha_suscripcion']) ? "'".$data_condicion_economica['fecha_suscripcion']."'" : "null";
                        $dt_condicion_economica['renovacion_automatica'] = !Empty($data_condicion_economica['renovacion_automatica']) ? "'".$data_condicion_economica['renovacion_automatica']."'" : "null";
                        $dt_condicion_economica['impuesto_a_la_renta_id'] = !Empty($data_condicion_economica['impuesto_a_la_renta_id']) ? "'".$data_condicion_economica['impuesto_a_la_renta_id']."'" : "0";
                        $dt_condicion_economica['carta_de_instruccion_id'] = !Empty($data_condicion_economica['carta_de_instruccion_id']) ? "'".$data_condicion_economica['carta_de_instruccion_id']."'" : "null";
                        $dt_condicion_economica['numero_cuenta_detraccion'] = !Empty($data_condicion_economica['numero_cuenta_detraccion']) ? "'".$data_condicion_economica['numero_cuenta_detraccion']."'" : "null";
                        $dt_condicion_economica['periodo_gracia_id'] = !Empty($data_condicion_economica['periodo_gracia_id']) ? "'".$data_condicion_economica['periodo_gracia_id']."'" : "null";
                        $dt_condicion_economica['periodo_gracia_numero'] = !Empty($data_condicion_economica['periodo_gracia_numero']) ? "'".$data_condicion_economica['periodo_gracia_numero']."'" : "null";
                        $dt_condicion_economica['periodo_gracia_inicio'] = !Empty($data_condicion_economica['periodo_gracia_inicio']) ? "'".$data_condicion_economica['periodo_gracia_inicio']."'" : "null";
                        $dt_condicion_economica['periodo_gracia_fin'] = !Empty($data_condicion_economica['periodo_gracia_fin']) ? "'".$data_condicion_economica['periodo_gracia_fin']."'" : "null";
                        $dt_condicion_economica['tipo_incremento_id'] = !Empty($data_condicion_economica['tipo_incremento_id']) ? "'".$data_condicion_economica['tipo_incremento_id']."'" : "0";
                        $dt_condicion_economica['tipo_inflacion_id'] = !Empty($data_condicion_economica['tipo_inflacion_id']) ? "'".$data_condicion_economica['tipo_inflacion_id']."'" : "null";
                        $dt_condicion_economica['tipo_cuota_extraordinaria_id'] = !Empty($data_condicion_economica['tipo_cuota_extraordinaria_id']) ? "'".$data_condicion_economica['tipo_cuota_extraordinaria_id']."'" : "null";
                        $dt_condicion_economica['usuario_contrato_aprobado_id'] = !Empty($data_condicion_economica['usuario_contrato_aprobado_id']) ? "'".$data_condicion_economica['usuario_contrato_aprobado_id']."'" : "null";
                        $dt_condicion_economica['aprobado_at'] = !Empty($data_condicion_economica['aprobado_at']) ? "'".$data_condicion_economica['aprobado_at']."'" : "null";
                        $dt_condicion_economica['dia_de_pago_id'] = !Empty($data_condicion_economica['dia_de_pago_id']) ? "'".$data_condicion_economica['dia_de_pago_id']."'" : "null";
                        $dt_condicion_economica['renta_adelantada_id'] = !Empty($data_condicion_economica['renta_adelantada_id']) ? "'".$data_condicion_economica['renta_adelantada_id']."'" : "null";
                        $dt_condicion_economica['pago_renta_id'] = !Empty($data_condicion_economica['pago_renta_id']) ? "'".$data_condicion_economica['pago_renta_id']."'" : "null";
                        $dt_condicion_economica['cuota_variable'] = !Empty($data_condicion_economica['cuota_variable']) ? "'".$data_condicion_economica['cuota_variable']."'" : "null";
                        $dt_condicion_economica['tipo_venta_id'] = !Empty($data_condicion_economica['tipo_venta_id']) ? "'".$data_condicion_economica['tipo_venta_id']."'" : "null";
                        $dt_condicion_economica['afectacion_igv_id'] = !Empty($data_condicion_economica['afectacion_igv_id']) ? "'".$data_condicion_economica['afectacion_igv_id']."'" : "null";
                        $dt_condicion_economica['status'] = !Empty($data_condicion_economica['status']) ? "'".$data_condicion_economica['status']."'" : "0";
                        $dt_condicion_economica['user_created_id'] = !Empty($data_condicion_economica['user_created_id']) ? "'".$data_condicion_economica['user_created_id']."'" : "0";
                        $dt_condicion_economica['created_at'] = !Empty($data_condicion_economica['created_at']) ? "'".$data_condicion_economica['created_at']."'" : "null";
                        $res_condicion_economica = $model->registrar_condicion_economica($dt_condicion_economica);
                        if ($res_condicion_economica['status'] == 404) {
                            $connection->rollback();
                            return json_encode($res_condicion_economica,JSON_UNESCAPED_UNICODE);
                        }

                    }

                    //INCREMENTO
                    $incremento = $data_contrato_detalle['incremento'];
                    foreach ($incremento as $data_incremento) {
                        $dt_incremento['contrato_id'] = $contrato_id;
                        $dt_incremento['contrato_detalle_id'] = $contrato_detalle_id;
                        $dt_incremento['valor'] = !Empty($data_incremento['valor']) ? "'".$data_incremento['valor']."'" : "0";
                        $dt_incremento['tipo_valor_id'] = !Empty($data_incremento['tipo_valor_id']) ? "'".$data_incremento['tipo_valor_id']."'" : "0";
                        $dt_incremento['tipo_continuidad_id'] = !Empty($data_incremento['tipo_continuidad_id']) ? "'".$data_incremento['tipo_continuidad_id']."'" : "0";
                        $dt_incremento['a_partir_del_año'] = !Empty($data_incremento['a_partir_del_año']) ? "'".$data_incremento['a_partir_del_año']."'" : "0";
                        $dt_incremento['fecha_cambio'] = !Empty($data_incremento['fecha_cambio']) ? "'".$data_incremento['fecha_cambio']."'" : "null";
                        $dt_incremento['estado'] = !Empty($data_incremento['estado']) ? "'".$data_incremento['estado']."'" : "null";
                        $dt_incremento['user_created_id'] = !Empty($data_incremento['user_created_id']) ? "'".$data_incremento['user_created_id']."'" : "null";
                        $dt_incremento['created_at'] = !Empty($data_incremento['created_at']) ? "'".$data_incremento['created_at']."'" : "null";
                        $res_incremento = $model->registrar_incremento($dt_incremento);
                        if ($res_incremento['status'] == 404) {
                            $connection->rollback();
                            return json_encode($res_incremento,JSON_UNESCAPED_UNICODE);
                        }
                    }


                    //INFLACION
                    $inflacion = $data_contrato_detalle['inflacion'];
                    foreach ($inflacion as $data_inflacion) {
                        $dt_inflacion['contrato_id'] = $contrato_id;
                        $dt_inflacion['contrato_detalle_id'] = $contrato_detalle_id;
                        $dt_inflacion['fecha'] = !Empty($data_inflacion['fecha']) ? "'".$data_inflacion['fecha']."'" : "null";
                        $dt_inflacion['tipo_periodicidad_id'] = !Empty($data_inflacion['tipo_periodicidad_id']) ? "'".$data_inflacion['tipo_periodicidad_id']."'" : "0";
                        $dt_inflacion['numero'] = !Empty($data_inflacion['numero']) ? "'".$data_inflacion['numero']."'" : "0";
                        $dt_inflacion['tipo_anio_mes'] = !Empty($data_inflacion['tipo_anio_mes']) ? "'".$data_inflacion['tipo_anio_mes']."'" : "0";
                        $dt_inflacion['moneda_id'] = !Empty($data_inflacion['moneda_id']) ? "'".$data_inflacion['moneda_id']."'" : "0";
                        $dt_inflacion['porcentaje_anadido'] = !Empty($data_inflacion['porcentaje_anadido']) ? "'".$data_inflacion['porcentaje_anadido']."'" : "0";
                        $dt_inflacion['tope_inflacion'] = !Empty($data_inflacion['tope_inflacion']) ? "'".$data_inflacion['tope_inflacion']."'" : "0";
                        $dt_inflacion['minimo_inflacion'] = !Empty($data_inflacion['minimo_inflacion']) ? "'".$data_inflacion['minimo_inflacion']."'" : "0";
                        $dt_inflacion['tipo_aplicacion_id'] = !Empty($data_inflacion['tipo_aplicacion_id']) ? "'".$data_inflacion['tipo_aplicacion_id']."'" : "0";
                        $dt_inflacion['status'] = !Empty($data_inflacion['status']) ? "'".$data_inflacion['status']."'" : "null";
                        $dt_inflacion['created_at'] = !Empty($data_inflacion['created_at']) ? "'".$data_inflacion['created_at']."'" : "null";
                        $dt_inflacion['user_created_id'] = !Empty($data_inflacion['user_created_id']) ? "'".$data_inflacion['user_created_id']."'" : "null";
                        $res_inflacion = $model->registrar_inflacion($dt_inflacion);
                        if ($res_inflacion['status'] == 404) {
                            $connection->rollback();
                            return json_encode($res_inflacion,JSON_UNESCAPED_UNICODE);
                        }
                    }


                    //CUOTA EXTRAORDINARIA
                    $cuota_extraordinaria = $data_contrato_detalle['cuota_extraordinaria'];
                    foreach ($cuota_extraordinaria as $data_cuota_extraordinaria) {
                        $dt_cuota_extraordinaria['contrato_id'] = $contrato_id;
                        $dt_cuota_extraordinaria['contrato_detalle_id'] = $contrato_detalle_id;
                        $dt_cuota_extraordinaria['mes'] = !Empty($data_cuota_extraordinaria['mes']) ? "'".$data_cuota_extraordinaria['mes']."'" :"null";
                        $dt_cuota_extraordinaria['multiplicador'] = !Empty($data_cuota_extraordinaria['multiplicador']) ? "'".$data_cuota_extraordinaria['multiplicador']."'" :"null";
                        $dt_cuota_extraordinaria['meses_despues'] = !Empty($data_cuota_extraordinaria['meses_despues']) ? "'".$data_cuota_extraordinaria['meses_despues']."'" :"null";
                        $dt_cuota_extraordinaria['fecha'] = !Empty($data_cuota_extraordinaria['fecha']) ? "'".$data_cuota_extraordinaria['fecha']."'" :"null";
                        $dt_cuota_extraordinaria['status'] = !Empty($data_cuota_extraordinaria['status']) ? "'".$data_cuota_extraordinaria['status']."'" :"null";
                        $dt_cuota_extraordinaria['user_created_id'] = !Empty($data_cuota_extraordinaria['user_created_id']) ? "'".$data_cuota_extraordinaria['user_created_id']."'" :"null";
                        $dt_cuota_extraordinaria['created_at'] = !Empty($data_cuota_extraordinaria['created_at']) ? "'".$data_cuota_extraordinaria['created_at']."'" :"null";
                        $res_cuota_extraordinaria = $model->registrar_cuota_extraordinaria($dt_cuota_extraordinaria);
                        if ($res_cuota_extraordinaria['status'] == 404) {
                            $connection->rollback();
                            return json_encode($res_cuota_extraordinaria,JSON_UNESCAPED_UNICODE);
                        }
                    }

                    //BENEFICIARIO
                    $beneficiario = $data_contrato_detalle['beneficiario'];
                    foreach ($beneficiario as $data_beneficiario) {
                        $dt_beneficiario['contrato_id'] = $contrato_id;
                        $dt_beneficiario['contrato_detalle_id'] = $contrato_detalle_id;
                        $dt_beneficiario['tipo_persona_id'] = !Empty($data_beneficiario['tipo_persona_id']) ? "'".$data_beneficiario['tipo_persona_id']."'" : "null";
                        $dt_beneficiario['tipo_docu_identidad_id'] = !Empty($data_beneficiario['tipo_docu_identidad_id']) ? "'".$data_beneficiario['tipo_docu_identidad_id']."'" : "0";
                        $dt_beneficiario['num_docu'] = !Empty($data_beneficiario['num_docu']) ? "'".$data_beneficiario['num_docu']."'" : "''";
                        $dt_beneficiario['nombre'] = !Empty($data_beneficiario['nombre']) ? "'".$data_beneficiario['nombre']."'" : "'";
                        $dt_beneficiario['forma_pago_id'] = !Empty($data_beneficiario['forma_pago_id']) ? "'".$data_beneficiario['forma_pago_id']."'" : "null";
                        $dt_beneficiario['banco_id'] = !Empty($data_beneficiario['banco_id']) ? "'".$data_beneficiario['banco_id']."'" : "null";
                        $dt_beneficiario['num_cuenta_bancaria'] = !Empty($data_beneficiario['num_cuenta_bancaria']) ? "'".$data_beneficiario['num_cuenta_bancaria']."'" : "null";
                        $dt_beneficiario['num_cuenta_cci'] = !Empty($data_beneficiario['num_cuenta_cci']) ? "'".$data_beneficiario['num_cuenta_cci']."'" : "null";
                        $dt_beneficiario['tipo_monto_id'] = !Empty($data_beneficiario['tipo_monto_id']) ? "'".$data_beneficiario['tipo_monto_id']."'" : "0";
                        $dt_beneficiario['monto'] = !Empty($data_beneficiario['monto']) ? "'".$data_beneficiario['monto']."'" : "0";
                        $dt_beneficiario['status'] = !Empty($data_beneficiario['status']) ? "'".$data_beneficiario['status']."'" : "null";
                        $dt_beneficiario['user_created_id'] = !Empty($data_beneficiario['user_created_id']) ? "'".$data_beneficiario['user_created_id']."'" : "null";
                        $dt_beneficiario['created_at'] = !Empty($data_beneficiario['created_at']) ? "'".$data_beneficiario['created_at']."'" : "null";
                        $res_beneficiario = $model->registrar_beneficiario($dt_beneficiario);
                        if ($res_beneficiario['status'] == 404) {
                            $connection->rollback();
                            return json_encode($res_beneficiario,JSON_UNESCAPED_UNICODE);
                        }
                    }


                    //RESPONSABLE IR
                    $responsable_ir = $data_contrato_detalle['responsable_ir'];
                    foreach ($responsable_ir as $data_responsable_ir) {
                        $dt_responsable_ir['contrato_id'] = $contrato_id;
                        $dt_responsable_ir['contrato_detalle_id'] = $contrato_detalle_id;
                        $dt_responsable_ir['tipo_documento_id'] = !Empty($data_responsable_ir['tipo_documento_id']) ? "'".$data_responsable_ir['tipo_documento_id']."'" : "null";
                        $dt_responsable_ir['num_documento'] = !Empty($data_responsable_ir['num_documento']) ? "'".$data_responsable_ir['num_documento']."'" : "null";
                        $dt_responsable_ir['nombres'] = !Empty($data_responsable_ir['nombres']) ? "'".$data_responsable_ir['nombres']."'" : "null";
                        $dt_responsable_ir['estado_emisor'] = !Empty($data_responsable_ir['estado_emisor']) ? "'".$data_responsable_ir['estado_emisor']."'" : "null";
                        $dt_responsable_ir['porcentaje'] = !Empty($data_responsable_ir['porcentaje']) ? "'".$data_responsable_ir['porcentaje']."'" : "null";
                        $dt_responsable_ir['status'] = !Empty($data_responsable_ir['status']) ? "'".$data_responsable_ir['status']."'" : "null";
                        $dt_responsable_ir['user_created_id'] = !Empty($data_responsable_ir['user_created_id']) ? "'".$data_responsable_ir['user_created_id']."'" : "null";
                        $dt_responsable_ir['created_at'] = !Empty($data_responsable_ir['created_at']) ? "'".$data_responsable_ir['created_at']."'" : "null";
                        $res_responsable_ir = $model->registrar_responsable_ir($dt_responsable_ir);
                        if ($res_responsable_ir['status'] == 404) {
                            $connection->rollback();
                            return json_encode($res_responsable_ir,JSON_UNESCAPED_UNICODE);
                        }
                    }

                    //ADELANTO
                    $adelanto = $data_contrato_detalle['adelanto'];
                    foreach ($adelanto as $data_adelanto) {
                        $dt_adelanto['contrato_id'] = $contrato_id;
                        $dt_adelanto['contrato_detalle_id'] = $contrato_detalle_id;
                        $dt_adelanto['num_periodo'] = !Empty($data_adelanto['num_periodo']) ? "'".$data_adelanto['num_periodo']."'" : "null";
                        $dt_adelanto['status'] = !Empty($data_adelanto['status']) ? "'".$data_adelanto['status']."'" : "null";
                        $dt_adelanto['user_created_id'] = !Empty($data_adelanto['user_created_id']) ? "'".$data_adelanto['user_created_id']."'" : "null";
                        $dt_adelanto['created_at'] = !Empty($data_adelanto['created_at']) ? "'".$data_adelanto['created_at']."'" : "null";
                        $res_adelanto = $model->registrar_adelanto($dt_adelanto);
                        if ($res_adelanto['status'] == 404) {
                            $connection->rollback();
                            return json_encode($res_adelanto,JSON_UNESCAPED_UNICODE);
                        }
                    }

                    //RARCHIVOS
                    $archivo = $data_contrato_detalle['archivo'];
                    foreach ($archivo as $data_archivo) {
                        $dt_archivo['contrato_id'] = $contrato_id;
                        $dt_archivo['contrato_detalle_id'] = $contrato_detalle_id;
                        $dt_archivo['resolucion_contrato_id'] = !Empty($data_archivo['resolucion_contrato_id']) ? "'".$data_archivo['resolucion_contrato_id']."'" : "null";
                        $dt_archivo['tipo_archivo_id'] = !Empty($data_archivo['tipo_archivo_id']) ? "'".$data_archivo['tipo_archivo_id']."'" : "0";
                        $dt_archivo['nombre'] = !Empty($data_archivo['nombre']) ? "'".$data_archivo['nombre']."'" : "''";
                        $dt_archivo['extension'] = !Empty($data_archivo['extension']) ? "'".$data_archivo['extension']."'" : "''";
                        $dt_archivo['ruta'] = !Empty($data_archivo['ruta']) ? "'".$data_archivo['ruta']."'" : "''";
                        $dt_archivo['size'] = !Empty($data_archivo['size']) ? "'".$data_archivo['size']."'" : "''";
                        $dt_archivo['status'] = !Empty($data_archivo['status']) ? "'".$data_archivo['status']."'" : "1";
                        $dt_archivo['user_created_id'] = !Empty($data_archivo['user_created_id']) ? "'".$data_archivo['user_created_id']."'" : "null";
                        $dt_archivo['created_at'] = !Empty($data_archivo['created_at']) ? "'".$data_archivo['created_at']."'" : "null";
                        $res_archivo = $model->registrar_archivos($dt_archivo);
                        if ($res_archivo['status'] == 404) {
                            $connection->rollback();
                            return json_encode($res_archivo,JSON_UNESCAPED_UNICODE);
                        }
                    }

                    

                }
                
                $locales = $data_contrato['locales'];
                foreach ($locales as $data_locales) {

                    $nombre_tienda = !Empty($data_locales['nombre']) ? "'".$data_locales['nombre']."'" : "null";
                    $nombre_tienda = str_replace("Red AT", "Red IGH", $nombre_tienda);



                    $dt_local['id'] = !Empty($data_locales['id']) ? "'".$data_locales['id']."'": "null";
                    $dt_local['canal_id'] = !Empty($data_locales['canal_id']) ? "'".$data_locales['canal_id']."'": "null";
                    $dt_local['cc_id'] = "'".$data_contrato['contrato']['new_cc_id']."'"; // Nuevo CC ID
                    $dt_local['red_id'] = 16; ///RED IGH
                    $dt_local['tipo_id'] = !Empty($data_locales['tipo_id']) ? "'".$data_locales['tipo_id']."'": "null";
                    $dt_local['propiedad_id'] = !Empty($data_locales['propiedad_id']) ? "'".$data_locales['propiedad_id']."'": "null";
                    $dt_local['cliente_id'] = !Empty($data_locales['cliente_id']) ? "'".$data_locales['cliente_id']."'": "null";
                    $dt_local['razon_social_id'] = 30; // NUEVA EMPRESA INVERSIONES GAMING HOUSE SAC
                    $dt_local['nombre'] = $nombre_tienda;
                    $dt_local['descripcion'] = !Empty($data_locales['descripcion']) ? "'".$data_locales['descripcion']."'": "null";
                    $dt_local['zona_id'] = $data_contrato['contrato']['zona_id'];
                    $dt_local['ubigeo_id'] = !Empty($data_locales['ubigeo_id']) ? "'".$data_locales['ubigeo_id']."'": "null";
                    $dt_local['ubigeo_cod_depa'] = !Empty($data_locales['ubigeo_cod_depa']) ? "'".$data_locales['ubigeo_cod_depa']."'": "null";
                    $dt_local['ubigeo_cod_prov'] = !Empty($data_locales['ubigeo_cod_prov']) ? "'".$data_locales['ubigeo_cod_prov']."'": "null";
                    $dt_local['ubigeo_cod_dist'] = !Empty($data_locales['ubigeo_cod_dist']) ? "'".$data_locales['ubigeo_cod_dist']."'": "null";
                    $dt_local['direccion'] = !Empty($data_locales['direccion']) ? "'".$data_locales['direccion']."'": "null";
                    $dt_local['email'] = !Empty($data_locales['email']) ? "'".$data_locales['email']."'": "null";
                    $dt_local['phone'] = !Empty($data_locales['phone']) ? "'".$data_locales['phone']."'": "null";
                    $dt_local['fecha_registro'] = !Empty($data_locales['fecha_registro']) ? "'".$data_locales['fecha_registro']."'": "null";
                    $dt_local['representante_id'] = !Empty($data_locales['representante_id']) ? "'".$data_locales['representante_id']."'": "null";
                    $dt_local['asesor_id'] = !Empty($data_locales['asesor_id']) ? "'".$data_locales['asesor_id']."'": "null";
                    $dt_local['estado_legal_id'] = !Empty($data_locales['estado_legal_id']) ? "'".$data_locales['estado_legal_id']."'": "null";
                    $dt_local['area'] = !Empty($data_locales['area']) ? "'".$data_locales['area']."'": "null";
                    $dt_local['otra_casa_apuestas'] = !Empty($data_locales['otra_casa_apuestas']) ? "'".$data_locales['otra_casa_apuestas']."'": "null";
                    $dt_local['administracion_tipo'] = !Empty($data_locales['administracion_tipo']) ? "'".$data_locales['administracion_tipo']."'": "null";
                    $dt_local['otra_casa_apuestas_des'] = !Empty($data_locales['otra_casa_apuestas_des']) ? "'".$data_locales['otra_casa_apuestas_des']."'": "null";
                    $dt_local['experiencia_casa_apuestas'] = !Empty($data_locales['experiencia_casa_apuestas']) ? "'".$data_locales['experiencia_casa_apuestas']."'": "null";
                    $dt_local['experiencia_casa_apuestas_des'] = !Empty($data_locales['experiencia_casa_apuestas_des']) ? "'".$data_locales['experiencia_casa_apuestas_des']."'": "null";
                    $dt_local['reportes_mostrar'] = !Empty($data_locales['reportes_mostrar']) ? "'".$data_locales['reportes_mostrar']."'": "null";
                    $dt_local['username'] = !Empty($data_locales['username']) ? "'".$data_locales['username']."'": "null";
                    $dt_local['password'] = !Empty($data_locales['password']) ? "'".$data_locales['password']."'": "null";
                    $dt_local['password_md5'] = !Empty($data_locales['password_md5']) ? "'".$data_locales['password_md5']."'": "null";
                    $dt_local['estado'] = !Empty($data_locales['estado']) ? "'".$data_locales['estado']."'": "null";
                    $dt_local['fecha_inicio_operacion'] = !Empty($data_locales['fecha_inicio_operacion']) ? "'".$data_locales['fecha_inicio_operacion']."'": "null";
                    $dt_local['fecha_fin_operacion'] = !Empty($data_locales['fecha_fin_operacion']) ? "'".$data_locales['fecha_fin_operacion']."'": "null";
                    $dt_local['operativo'] = !Empty($data_locales['operativo']) ? "'".$data_locales['operativo']."'": "null";
                    $dt_local['latitud'] = !Empty($data_locales['latitud']) ? "'".$data_locales['latitud']."'": "null";
                    $dt_local['longitud'] = !Empty($data_locales['longitud']) ? "'".$data_locales['longitud']."'": "null";
                    $dt_local['zona_financiera'] = !Empty($data_locales['zona_financiera']) ? "'".$data_locales['zona_financiera']."'": "null";
                    $dt_local['show_web'] = !Empty($data_locales['show_web']) ? "'".$data_locales['show_web']."'": "null";
                    $dt_local['contrato_id'] = $contrato_id;
                    $dt_local['is_test'] = !Empty($data_locales['is_test']) ? "'".$data_locales['is_test']."'": "null";
                    $dt_local['fecha_sorteo_tienda_nueva'] = !Empty($data_locales['fecha_sorteo_tienda_nueva']) ? "'".$data_locales['fecha_sorteo_tienda_nueva']."'": "null";
                    $dt_local['status_cont_ag'] = !Empty($data_locales['status_cont_ag']) ? "'".$data_locales['status_cont_ag']."'": "null";
                    $dt_local['trastienda'] = !Empty($data_locales['trastienda']) ? "'".$data_locales['trastienda']."'": "null";
                    $dt_local['estado_terminal_kasnet'] = !Empty($data_locales['estado_terminal_kasnet']) ? "'".$data_locales['estado_terminal_kasnet']."'": "null";
                    $dt_local['fecha_inicio_garantia'] = !Empty($data_locales['fecha_inicio_garantia']) ? "'".$data_locales['fecha_inicio_garantia']."'": "null";
                    $dt_local['fecha_fin_garantia'] = !Empty($data_locales['fecha_fin_garantia']) ? "'".$data_locales['fecha_fin_garantia']."'": "null";
                    $dt_local['user_created_id'] = !Empty($data_locales['user_created_id']) ? "'".$data_locales['user_created_id']."'": "null";
                    $dt_local['created_at'] = "'".date('Y-m-d H:i:s')."'";
                    $dt_local['migracion_id'] = 1;
                    $res_local = $model->registrar_local($dt_local);
                    if ($res_local['status'] == 404) {
                        $connection->rollback();
                        return json_encode($res_local,JSON_UNESCAPED_UNICODE);
                    }
                    $local_id = $res_local['result'];

                    //USUARIO LOCALES
                    $usuario_locales = $data_locales['usuario_locales'];
                    foreach ($usuario_locales as $data_usuario_locales) {
                        $dt_usuario_local['usuario_id'] = !Empty($data_usuario_locales['usuario_id']) ? "'".$data_usuario_locales['usuario_id']."'": "null";
                        $dt_usuario_local['local_id'] = $local_id;
                        $dt_usuario_local['estado'] = $data_usuario_locales['estado'];
                        $dt_usuario_local['user_created_id'] = !Empty($data_usuario_locales['user_created_id']) ? "'".$data_usuario_locales['user_created_id']."'": "null";
                        $dt_usuario_local['created_at'] = "'".date('Y-m-d H:i:s')."'";
                        $res_usuario_local = $model->registrar_usuario_locales($dt_usuario_local);
                        if ($res_usuario_local['status'] == 404) {
                            $connection->rollback();
                            return json_encode($res_usuario_local,JSON_UNESCAPED_UNICODE);
                        }
                    }

                    //HORARIO LOCAL
                    $horario_local = $data_locales['horario_local'];
                    foreach ($horario_local as $data_horario_local) {
                        $dt_horario_local['local_id'] = $local_id;
                        $dt_horario_local['horario_id'] = !Empty($data_horario_local['horario_id']) ? "'".$data_horario_local['horario_id']."'": "null";
                        $dt_horario_local['started_at'] = !Empty($data_horario_local['started_at']) ? "'".$data_horario_local['started_at']."'": "null";
                        $res_local_horario = $model->registrar_local_horario($dt_horario_local);
                        if ($res_local_horario['status'] == 404) {
                            $connection->rollback();
                            return json_encode($res_local_horario,JSON_UNESCAPED_UNICODE);
                        }
                    }


                    //HORARIO LOCAL
                    $local_config = $data_locales['local_config'];
                    foreach ($local_config as $data_local_config) {
                        $dt_local_config['local_id'] = $local_id;
                        $dt_local_config['config_id'] = !Empty($data_local_config['config_id']) ? "'".$data_local_config['config_id']."'": "null";
                        $dt_local_config['config_param'] = !Empty($data_local_config['config_param']) ? "'".$data_local_config['config_param']."'": "null";
                        $dt_local_config['created_at'] = "'".date('Y-m-d H:i:s')."'";
                        $dt_local_config['updated_at'] = !Empty($data_local_config['updated_at']) ? "'".$data_local_config['updated_at']."'": "null";
                        $res_local_config = $model->registrar_local_config($dt_local_config);
                        if ($res_local_config['status'] == 404) {
                            $connection->rollback();
                            return json_encode($res_local_config,JSON_UNESCAPED_UNICODE);
                        }
                    }

                }


                $propietarios = $data_contrato['propietarios'];
                foreach ($propietarios as $data_propietarios) {
                    $dt_propietario['contrato_id'] = $contrato_id;
                    $dt_propietario['persona_id'] = !Empty($data_propietarios['persona_id']) ? "'".$data_propietarios['persona_id']."'":"'0'";
                    $dt_propietario['encargado_impuesto_renta_id'] = !Empty($data_propietarios['encargado_impuesto_renta_id']) ? "'".$data_propietarios['encargado_impuesto_renta_id']."'":"null";
                    $dt_propietario['status'] = !Empty($data_propietarios['status']) ? "'".$data_propietarios['status']."'":"'0'";
                    $dt_propietario['user_created_id'] = !Empty($data_propietarios['user_created_id']) ? "'".$data_propietarios['user_created_id']."'":"null";
                    $dt_propietario['created_at'] = !Empty($data_propietarios['created_at']) ? "'".$data_propietarios['created_at']."'":"null";
                    $res_propietario = $model->registrar_propietario($dt_propietario);
                    if ($res_propietario['status'] == 404) {
                        $connection->rollback();
                        return json_encode($res_propietario,JSON_UNESCAPED_UNICODE);
                    }
                }

                $licencia_municipal = $data_contrato['licencia_municipal'];
                foreach ($licencia_municipal as $data_licencia_municipal) {
                    $dt_licencia_municipal['contrato_id'] = $contrato_id;
                    $dt_licencia_municipal['tipo_archivo_id'] = !Empty($data_licencia_municipal['tipo_archivo_id']) ? "'".$data_licencia_municipal['tipo_archivo_id']."'" : "null";
                    $dt_licencia_municipal['status_licencia'] = !Empty($data_licencia_municipal['status_licencia']) ? "'".$data_licencia_municipal['status_licencia']."'" : "null";
                    $dt_licencia_municipal['condicion'] = !Empty($data_licencia_municipal['condicion']) ? "'".$data_licencia_municipal['condicion']."'" : "null";
                    $dt_licencia_municipal['fecha_vencimiento'] = !Empty($data_licencia_municipal['fecha_vencimiento']) ? "'".$data_licencia_municipal['fecha_vencimiento']."'" : "null";
                    $dt_licencia_municipal['fecha_renovacion'] = !Empty($data_licencia_municipal['fecha_renovacion']) ? "'".$data_licencia_municipal['fecha_renovacion']."'" : "null";
                    $dt_licencia_municipal['nombre_file'] = !Empty($data_licencia_municipal['nombre_file']) ? "'".$data_licencia_municipal['nombre_file']."'" : "null";
                    $dt_licencia_municipal['extension'] = !Empty($data_licencia_municipal['extension']) ? "'".$data_licencia_municipal['extension']."'" : "null";
                    $dt_licencia_municipal['size'] = !Empty($data_licencia_municipal['size']) ? "'".$data_licencia_municipal['size']."'" : "null";
                    $dt_licencia_municipal['ruta'] = !Empty($data_licencia_municipal['ruta']) ? "'".$data_licencia_municipal['ruta']."'" : "null";
                    $dt_licencia_municipal['download_file'] = !Empty($data_licencia_municipal['download_file']) ? "'".$data_licencia_municipal['download_file']."'" : "null";
                    $dt_licencia_municipal['alerta_enviada'] = !Empty($data_licencia_municipal['alerta_enviada']) ? "'".$data_licencia_municipal['alerta_enviada']."'" : "null";
                    $dt_licencia_municipal['dj_id'] = !Empty($data_licencia_municipal['dj_id']) ? "'".$data_licencia_municipal['dj_id']."'" : "null";
                    $dt_licencia_municipal['estado'] = !Empty($data_licencia_municipal['estado']) ? "'".$data_licencia_municipal['estado']."'" : "null";
                    $dt_licencia_municipal['user_created_id'] = !Empty($data_licencia_municipal['user_created_id']) ? "'".$data_licencia_municipal['user_created_id']."'" : "null";
                    $dt_licencia_municipal['created_at'] = !Empty($data_licencia_municipal['created_at']) ? "'".$data_licencia_municipal['created_at']."'" : "null";
                    $res_licencia_municipal = $model->registrar_licencia_municipal($dt_licencia_municipal);
                    if ($res_licencia_municipal['status'] == 404) {
                        $connection->rollback();
                        return json_encode($res_licencia_municipal,JSON_UNESCAPED_UNICODE);
                    }
                }

                $observaciones = $data_contrato['observaciones'];
                foreach ($observaciones as $data_observaciones) {
                    $dt_observacion['contrato_id'] = $contrato_id;
                    $dt_observacion['observaciones'] = !Empty($data_observaciones['observaciones']) ? "'".$data_observaciones['observaciones']."'" : "''";
                    $dt_observacion['status'] = !Empty($data_observaciones['status']) ? "'".$data_observaciones['status']."'" : "null";
                    $dt_observacion['user_created_id'] = !Empty($data_observaciones['user_created_id']) ? "'".$data_observaciones['user_created_id']."'" : "null";
                    $dt_observacion['created_at'] = !Empty($data_observaciones['created_at']) ? "'".$data_observaciones['created_at']."'" : "null";
                    $res_observacion = $model->registrar_observacion($dt_observacion);
                    if ($res_observacion['status'] == 404) {
                        $connection->rollback();
                        return json_encode($res_observacion,JSON_UNESCAPED_UNICODE);
                    }
                }
                
            }

            $response = [
                'status' => 200,
                'result' => [],
                'message' => 'Se ah obtenido datos de gestion',
            ];
                
            $connection->commit();
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $connection->rollback();
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error';
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
    }



    public function registrar_local_caja(){
        $db = Database::getInstance();
        $connection = $db->getConnection();
        try {
            $connection->beginTransaction();
           
            $request = file_get_contents('php://input');
            $request = json_decode($request,true); 

            $response_local_caja_success = [];
            $response_local_caja_error = [];
            $response_local_caja_error_message = [];

            $response_local_success = [];
            $response_local_error = [];
            $response_local_error_message = [];

            $file_name = $request['file_name'];
            $path_file = "../../controllers/data/get_nuevos_locales/".$file_name;
            if (!file_exists($path_file)) {
                $result['status'] = 404;
                $result['result'] = "";
                $result['message'] = 'El archivo no existe';
                return json_encode($result,JSON_UNESCAPED_UNICODE);
            }
            $data = file_get_contents($path_file);
            $data = json_decode($data, true);

         

            foreach ($data as $be) {
                $model = new ContratoArrendamientoV2();
                $data_local['cc_id'] = $be['cc_id'];
                $data_local['new_cc_id'] = $be['new_cc_id'];
           
                $res_local_caja = $model->obtener_local_cajas_por_cc_id($data_local);
                if ($res_local_caja['status'] == 404) {
                    array_push($response_local_caja_error, $data_local['cc_id']);
                    // array_push($response_local_caja_error_message, $res_local_caja);
                }else{
                    array_push($response_local_caja_success, $data_local['cc_id']);

                    foreach ($res_local_caja['result'] as $local_caja) {

                        $res_local = $model->obtener_local_por_cc_id($data_local['new_cc_id']);
                        if ($res_local['status'] == 404) {
                            array_push($response_local_error, $data_local['new_cc_id']);
                            // array_push($response_local_error_message, $res_local);
                        }else{
                            array_push($response_local_success, $data_local['new_cc_id']);
                            //$dt_local_caja['id'] = !Empty($local_caja['id']) ? "'".$local_caja['id']."'" : "''";
                            $dt_local_caja['local_id'] = !Empty($res_local['result']['id']) ? "'".$res_local['result']['id']."'" : "null";
                            $dt_local_caja['caja_tipo_id'] = !Empty($local_caja['caja_tipo_id']) ? "'".$local_caja['caja_tipo_id']."'" : "null";
                            $dt_local_caja['nombre'] = !Empty($local_caja['nombre']) ? "'".$local_caja['nombre']."'" : "''";
                            $dt_local_caja['descripcion'] = !Empty($local_caja['descripcion']) ? "'".$local_caja['descripcion']."'" : "''";
                            $dt_local_caja['proveedor_id'] = !Empty($local_caja['proveedor_id']) ? "'".$local_caja['proveedor_id']."'" : "''";
                            $dt_local_caja['estado'] = !Empty($local_caja['estado']) ? "'".$local_caja['estado']."'" : "null";
                            $res_local_caja = $model->registrar_local_caja($dt_local_caja);
                            if ($res_local_caja['status'] == 404) {
                                $connection->rollback();
                                return json_encode($res_local_caja,JSON_UNESCAPED_UNICODE);
                            }
                        }
                    }
                }
            }

            $response = [
                'status' => 200,
                'result' => [
                    'response_local_caja_success' => $response_local_caja_success,
                    'response_local_caja_error' => $response_local_caja_error,
                    'response_local_caja_error_message' => $response_local_caja_error_message,

                    'response_local_success' => $response_local_success,
                    'response_local_error' => $response_local_error,
                    'response_local_error_message' => $response_local_error_message
                ],
                'message' => 'Se han obtenido datos de gestion',
            ];
                
            $connection->commit();
            return json_encode($response,JSON_UNESCAPED_UNICODE);
            
        } catch (Exception $th) {
            $connection->rollback();
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error';
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
    }

    public function migracion_de_tiendas(){
        $db = Database::getInstance();
        $connection = $db->getConnection();
        try {
            $connection->beginTransaction();
            $data = file_get_contents("../../controllers/data/migracion_de_tiendas.md");
            $data = json_decode($data, true);

            $response_local_success = [];
            foreach ($data as $data_local) {
              $model = new DataImport();
              $result = $model->migracion_de_tiendas($data_local);
              if ($result['status'] == 404) {
                $connection->rollback();
                return json_encode($result,JSON_UNESCAPED_UNICODE);
              }
              array_push($response_local_success,$result['result']);
            }

            $response = [
                'status' => 200,
                'result' => $response_local_success,
                'result_count' => count($response_local_success),
                'message' => 'Se ah obtenido datos de gestion',
            ];
                
            $connection->commit();
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $connection->rollback();
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error';
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
    }


    public function importacion_codigo_sap(){
        $db = Database::getInstance();
        $connection = $db->getConnection();
        try {
            $request = file_get_contents('php://input');
            $request = json_decode($request,true); 

            $data_update = [];
            $data_not_update = [];

            $connection->beginTransaction();
            $data = $request['personal'];

            foreach ($data as $data_sap) {
              $model = new DataImport();
              $data_sap['TIPO_DOCUMENTO'] = isset($data_sap['TIPO_DOCUMENTO']) ? $data_sap['TIPO_DOCUMENTO'] : '';
              $data_sap['NRO_DOCUMENTO'] = isset($data_sap['NRO_DOCUMENTO']) ? $data_sap['NRO_DOCUMENTO'] : '';
              $data_sap['NOMBRES'] = isset($data_sap['NOMBRES']) ? ucwords(strtolower($data_sap['NOMBRES'])) : '';
              $data_sap['APELLIDO_PATERNO'] = isset($data_sap['APELLIDO_PATERNO']) ? ucwords(strtolower($data_sap['APELLIDO_PATERNO'])) : '';
              $data_sap['APELLIDO_MATERNO'] = isset($data_sap['APELLIDO_MATERNO']) ? ucwords(strtolower($data_sap['APELLIDO_MATERNO'])) : '';
              $data_sap['FECHA_INGRESO'] = isset($data_sap['FECHA_INGRESO']) ? $data_sap['FECHA_INGRESO'] : '';
              $data_sap['CODIGO_SAP'] = isset($data_sap['CODIGO_SAP']) ? $data_sap['CODIGO_SAP'] : '';
              $data_sap['ESTADO_SAP'] = isset($data_sap['ESTADO_SAP']) ? $data_sap['ESTADO_SAP'] : '';
              $data_sap['CODIGO_PUESTO_SAP'] = isset($data_sap['CODIGO_PUESTO_SAP']) ? $data_sap['CODIGO_PUESTO_SAP'] : '';
              $data_sap['CODIGO_LIDER_SAP'] = isset($data_sap['CODIGO_LIDER_SAP']) ? $data_sap['CODIGO_LIDER_SAP'] : '';
              $data_sap['CODIGO_DIVISION_SAP'] = isset($data_sap['CODIGO_DIVISION_SAP']) ? $data_sap['CODIGO_DIVISION_SAP'] : '';
              $data_sap['CODIGO_SUBDIVISION_SAP'] = isset($data_sap['CODIGO_SUBDIVISION_SAP']) ? $data_sap['CODIGO_SUBDIVISION_SAP'] : '';
              $data_sap['CENTRO_COSTO_SAP'] = isset($data_sap['CENTRO_COSTO_SAP']) ? $data_sap['CENTRO_COSTO_SAP'] : '';
              $data_sap['CODIGO_RAZON_SOCIAL'] = isset($data_sap['CODIGO_RAZON_SOCIAL']) ? $data_sap['CODIGO_RAZON_SOCIAL'] : '';
              $data_sap['CORREO_PERSONAL'] = isset($data_sap['CORREO_PERSONAL']) ? $data_sap['CORREO_PERSONAL'] : '';
              $data_sap['CORREO_COORPORATIVO'] = isset($data_sap['CORREO_COORPORATIVO']) ? $data_sap['CORREO_COORPORATIVO'] : '';

              $data_sap['action_data'] = isset($request['action_data']) ? $request['action_data'] : '';
              
              
              $result = $model->importacion_codigo_sap($data_sap);
              if ($result['status'] == 200) {
                array_push($data_update, $result['result']);
              }else if($result['status'] == 400){
                array_push($data_not_update, $result['result']);
              }else{
                $connection->rollback();
                return json_encode($result,JSON_UNESCAPED_UNICODE);
              }
            }

            $response = [
                'status' => 200,
                'result' => [
                    'update' => $data_update,
                    'data_not_update' => $data_not_update,
                ],
                'result_count' => count($data),
                'message' => 'Se ah obtenido datos de gestion',
            ];
            $connection->commit();
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $connection->rollback();
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error';
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
    }


    public function actualizar_area_contrato_proveedores(){
        $db = Database::getInstance();
        $connection = $db->getConnection();
        try {
            $request = file_get_contents('php://input');
            $request = json_decode($request,true); 

            $data_update = [];
            $data_not_update = [];

            $connection->beginTransaction();
            $data = $request['data'];


            foreach ($data as $data_contrato) {
              $model = new DataImport();
              $data_contrato['contrato_id'] = isset($data_contrato['contrato_id']) ? $data_contrato['contrato_id'] : '';
              $data_contrato['area_responsable_nuevo'] = isset($data_contrato['area_responsable_nuevo']) ? $data_contrato['area_responsable_nuevo'] : '';
              $data_contrato['area_responsable_nuevo'] = explode("|", $data_contrato['area_responsable_nuevo']);
              $data_contrato['area_id'] = (int) $data_contrato['area_responsable_nuevo'][0];
              $data_contrato['valor_select_option'] = isset($data_contrato['area_responsable_nuevo'][1]) ? $data_contrato['area_responsable_nuevo'][1] : '';
              $result = $model->actualizar_area_contrato_proveedores($data_contrato);
              if ($result['status'] == 200) {
                array_push($data_update, $result['result']);
              }else if($result['status'] == 400){
                array_push($data_not_update, $result['result']);
              }else{
                $connection->rollback();
                return json_encode($result,JSON_UNESCAPED_UNICODE);
              }
            }

            $response = [
                'status' => 200,
                'result' => [
                    'update' => $data_update,
                    'data_not_update' => $data_not_update,
                ],
                'result_count' => count($data),
                'message' => 'Se ah obtenido datos de gestion',
            ];
            $connection->commit();
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $connection->rollback();
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error';
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
    }


    public function migracion_tienda_igh_freegames(){
        $db = Database::getInstance();
        $connection = $db->getConnection();
        try {
            $request = file_get_contents('php://input');
            $request = json_decode($request,true); 
            
            $data_update = [];
            $data_not_update = [];

            $connection->beginTransaction();
            $data = $request['locales'];
            
            $data_migracion['migracion_id'] = $request['migracion_id'];
            $data_migracion['nombre'] = $request['nombre'];
            $data_migracion['fecha'] = date('Y-m-d');
            $data_migracion['status'] = 1;
            $data_migracion['created_at'] = date('Y-m-d H:i:s');
            $model = new DataImport();
            $rpta_migracion = $model->registrar_migracion($data_migracion);
            if ($rpta_migracion['status'] != 200) {
                return json_encode($rpta_migracion,JSON_UNESCAPED_UNICODE);
            }

            foreach ($data as $local) {
              $model = new DataImport();
              $local['migracion_id'] = $request['migracion_id'];
              $local['ceco'] = isset($local['ceco']) ? $local['ceco'] : '';
              $local['razon_social_id'] = isset($local['razon_social_id']) ? $local['razon_social_id'] : '';
              $local['red_id'] = isset($local['red_id']) ? $local['red_id'] : '';
              $local['zona_id'] = isset($local['zona_id']) ? $local['zona_id'] : '';
              $result = $model->mover_locales_igh_freegames($local);
              if ($result['status'] == 200) {
                array_push($data_update, $result['result']);
              }else if($result['status'] == 400){
                array_push($data_not_update, $result['result']);
              }else{
                $connection->rollback();
                return json_encode($result,JSON_UNESCAPED_UNICODE);
              }
            }
            $response = [
                'status' => 200,
                'result' => [
                    'update' => $data_update,
                    'data_not_update' => $data_not_update,
                ],
                'result_count' => count($data),
                'message' => 'Se ah obtenido datos de gestion',
            ];
            $connection->commit();
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $connection->rollback();
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error';
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
    }

}
 