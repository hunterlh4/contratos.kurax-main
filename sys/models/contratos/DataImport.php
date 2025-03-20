<?php
class DataImport extends Model {

    private $data;
    private $id;
  
    public function registrar_contrato_proveedor($data) {
        $query_insert = '';
        try {
            
          
            $query_insert = "INSERT INTO cont_contrato
            (
                tipo_contrato_id
                , codigo_correlativo 
                , empresa_suscribe_id
                , area_responsable_id
                , persona_responsable_id
                , etapa_id
                , ruc
                , razon_social
                , check_gerencia_proveedor
                , persona_contacto_proveedor
                , detalle_servicio
                , periodo_numero
                , periodo
                , fecha_inicio
                , alcance_servicio
                , tipo_terminacion_anticipada_id
                , terminacion_anticipada
                , observaciones
                , status
                , user_created_id
                , created_at
                , gerente_area_id
                , gerente_area_nombre
                , gerente_area_email
                , categoria_id
                , tipo_contrato_proveedor_id
                , tipo_firma_id
                , fecha_suscripcion_proveedor
                , fecha_vencimiento_indefinida_id
                , fecha_vencimiento_proveedor
                , usuario_contrato_proveedor_aprobado_id
                , codigo_importacion_proveedor
                
            )
            VALUES
            (
                2,
                " . $data['numero_correlativo'] . ",
                " . $data['empresa_suscribe_id'] . ",
                ".$data['area_responsable_id'].",
                0,
                ".$data['etapa_id'].",
                '" . $data['ruc'] . "',
                '" . $this->replace_invalid_caracters($data['razon_social']) . "',
                '0',
                '" . $this->replace_invalid_caracters($data['persona_contacto_proveedor']) . "',
                '" . $this->replace_invalid_caracters($data['detalle_servicio']) . "',
                '" . $data['periodo_numero']. "',
                '" . $data['periodo'] . "',
                " . $data['fecha_inicio'] . ",
                '" . $this->replace_invalid_caracters($data['alcance_servicio']) . "',
                " . $data['tipo_terminacion_anticipada_id'] . ",
                '" . $this->replace_invalid_caracters($data['terminacion_anticipada']) . "',
                '" . $this->replace_invalid_caracters($data['observaciones']) . "',
                1,
                " . $data['user_created_id'] . ",
                '" . $data['created_at'] . "',
                " . $data['gerente_area_id'] . ",
                '" . $data['gerente_area_nombre'] . "',
                '" . $data['gerente_area_email'] . "',
                '" . $data['categoria_id'] . "',
                '" . $data['tipo_contrato_proveedor_id'] . "',
                '" . $data['tipo_firma_id'] . "',
                " . $data['fecha_suscripcion_proveedor'] . ",
                '" . $data['fecha_vencimiento_indefinida_id'] . "',
                " . $data['fecha_vencimiento_proveedor'] . ",
                " . $data['usuario_contrato_proveedor_aprobado_id'] . ",
                " . $data['codigo'] . "
                
            )";

            $db_query = $this->db->prepare($query_insert);
            $db_query->execute();
            $id = $this->db->lastInsertId();
          

            $result['status'] = 200;
            $result['result'] = $id;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query_insert;
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }

    }

    public function obtener_contrato_por_codigo($codigo){
        $query_insert = '';
        try {
                $query_insert = "SELECT contrato_id FROM cont_contrato WHERE codigo_importacion_proveedor = '".$codigo."'";
                $db_query = $this->db->prepare($query_insert);
                $db_query->execute();
                
                $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

                $result['status'] = 200;
                $result['result'] = $resultado[0];
                $result['message'] = 'Datos obtenido de gestión';
                return $result;
            } catch (\Throwable $th) {
                $result['status'] = 404;
                $result['result'] = $th->getMessage();
                $result['query'] = $query_insert;
                $result['message'] = 'A ocurrido un error con la consulta sql';
                return $result;
            }
    }

    public function registrar_representante_legal($data){
        $query_insert = '';
        try {
            
        
            $query_insert = " INSERT INTO cont_representantes_legales
                    (
                    contrato_id,
                    dni_representante,
                    nombre_representante,
                    id_banco,
                    nro_cuenta,
                    nro_cci,
                    vigencia_archivo_id,
                    dni_archivo_id,
                    id_user_created,
                    created_at)
                    VALUES
                    (
                    " . $data['contrato_id'] . ",
                    '" . $data['dni_representante']. "',
                    '" . $data['nombre_representante'] . "',
                    '" . $data['id_banco'] . "',
                    '" . $data['nro_cuenta'] . "',
                    '" . $data['nro_cci'] . "',
                    0,
                    0,
                    " . $data['user_created_id'] . ",
                    '" . $data['created_at'] . "')";

            $db_query = $this->db->prepare($query_insert);
            $db_query->execute();
            $id = $this->db->lastInsertId();
          

            $result['status'] = 200;
            $result['result'] = $id;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query_insert;
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }

    }

    public function registrar_contraprestacion($data){
        $query_insert = '';
        try {
            
        
            $query_insert = "INSERT INTO cont_contraprestacion
            (
            contrato_id,
            moneda_id,
            forma_pago_id,
            tipo_comprobante_id,
            plazo_pago,
            subtotal,
            igv,
            monto,
            forma_pago_detallado,
            status,
            user_created_id,
            created_at)
            VALUES
            (
            " . $data['contrato_id'] . ",
            " . $data['moneda_id'] . ",
            0,
            " . $data['tipo_comprobante_id'] . ",
            '" . $data['plazo_pago'] . "',
            " . $data['subtotal'] . ",
            " . $data['igv'] . ",
            " . $data['monto'] . ",
            '" . $data['forma_pago_detallado'] . "',
            1,
            " . $data['user_created_id'] . ",
            '" . $data['created_at'] . "')";

            $db_query = $this->db->prepare($query_insert);
            $db_query->execute();
            $id = $this->db->lastInsertId();
          

            $result['status'] = 200;
            $result['result'] = $id;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query_insert;
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }

    }




    public function update_condicion_economica_v1($data) {
        $query_updated = '';
        try {
            
          
            $query_updated = "UPDATE cont_condicion_economica SET 
                tipo_moneda_id  = '".$data['moneda']."' ,
                monto_renta  = '".$data['monto_renta']."' ,
                plazo_id  = '".$data['periodo']."' ,
                fecha_inicio  = ".$data['fecha_inicio'].",
                fecha_fin  = ".$data['fecha_fin'].",
                impuesto_a_la_renta_id  = '".$data['impuesto_a_la_renta']."' ,
                carta_de_instruccion_id  = '".$data['carta_instruccion']."' ,
                user_updated_id  = '".$data['user_updated_id']."' ,
                updated_at  = '".$data['updated_at']."' 
            WHERE  condicion_economica_id = ".$data['condicion_economica_id'];

            $db_query = $this->db->prepare($query_updated);
            $db_query->execute();

            $result['status'] = 200;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query_updated;
            $result['message'] = 'A ocurrido un error con la consulta sql (update condicion economica)';
            return $result;
        }

    }

    public function obtener_contrato_por_cc_id($codigo){
        $query_insert = '';
        try {
                $query_insert = "SELECT d.contrato_id, d.id AS contrato_detalle_id FROM cont_contrato_detalle as d
                INNER JOIN cont_contrato AS c ON c.contrato_id = d.contrato_id
                WHERE c.etapa_id = 5 AND c.status = 1 AND c.cc_id = '".$codigo."'";
                $db_query = $this->db->prepare($query_insert);
                $db_query->execute();
                
                $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

                $result['status'] = 200;
                $result['result'] = $resultado[0];
                $result['message'] = 'Datos obtenido de gestión';
                return $result;
            } catch (\Throwable $th) {
                $result['status'] = 404;
                $result['result'] = $th->getMessage();
                $result['query'] = $query_insert;
                $result['message'] = 'A ocurrido un error con la consulta sql';
                return $result;
            }
    }

    public function obtener_condicion_economica_por_cc_id($codigo){
        $query_insert = '';
        try {
                $query_insert = "SELECT ce.condicion_economica_id, ce.contrato_id 
                FROM cont_condicion_economica AS ce 
                INNER JOIN cont_contrato AS c ON c.contrato_id = ce.contrato_id AND ce.status = 1
                WHERE c.etapa_id = 5 AND c.status = 1 AND c.cc_id = '".$codigo."'";
                $db_query = $this->db->prepare($query_insert);
                $db_query->execute();
                
                $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

                $result['status'] = 200;
                $result['result'] = $resultado[0];
                $result['message'] = 'Datos obtenido de gestión';
                return $result;
            } catch (\Throwable $th) {
                $result['status'] = 404;
                $result['result'] = $th->getMessage();
                $result['query'] = $query_insert;
                $result['message'] = 'A ocurrido un error con la consulta sql';
                return $result;
            }
    }

    public function registrar_responsable_ir_v1($data){
        $query_insert = '';
        try {
                $query_insert = "INSERT INTO cont_responsable_ir (contrato_id, contrato_detalle_id, tipo_documento_id, num_documento, nombres, porcentaje, status, user_created_id, created_at) VALUES (
                    '".$data['contrato_id']."', 
                    '".$data['contrato_detalle_id']."', 
                    '".$data['tipo_documento_id']."', 
                    '".$data['num_documento']."', 
                    '', 
                    '0', 
                    1, 
                    '".$data['user_created_id']."', 
                    '".$data['created_at']."');";
                $db_query = $this->db->prepare($query_insert);
                $db_query->execute();
                $id = $this->db->lastInsertId();
          
                $result['status'] = 200;
                $result['result'] = $id;
                $result['message'] = 'Datos obtenido de gestión';
                return $result;
            } catch (\Throwable $th) {
                $result['status'] = 404;
                $result['result'] = $th->getMessage();
                $result['query'] = $query_insert;
                $result['message'] = 'A ocurrido un error con la consulta sql (registro responsable ir)';
                return $result;
            }
    }

    public function update_incremento_v1($data) {
        $query_updated = '';
        try {
            
            $query_updated = "UPDATE cont_incrementos SET 
                valor  = '".$data['valor']."' ,
                tipo_valor_id  = '".$data['tipo_valor']."' ,
                tipo_continuidad_id  = '".$data['tipo_continuidad']."' ,
                a_partir_del_año  = ".$data['anio'].",
                estado  = ".$data['estado'].",
                user_updated_id  = '".$data['user_updated_id']."' ,
                updated_at  = '".$data['updated_at']."' 
            WHERE  id = ".$data['id'];

            $db_query = $this->db->prepare($query_updated);
            $db_query->execute();

            $result['status'] = 200;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query_updated;
            $result['message'] = 'A ocurrido un error con la consulta sql (update incremento)';
            return $result;
        }

    }

    public function registrar_incremento_v1($data){
        $query_insert = '';
        try {
                $query_insert = "INSERT INTO cont_incrementos (contrato_id, contrato_detalle_id, valor, tipo_valor_id, tipo_continuidad_id, a_partir_del_año, estado, user_created_id, created_at) VALUES (
                    '".$data['contrato_id']."', 
                    '".$data['contrato_detalle_id']."', 
                    '".$data['valor']."', 
                    '".$data['tipo_valor']."', 
                    '".$data['tipo_continuidad']."', 
                    '".$data['anio']."',  
                    1, 
                    '".$data['user_created_id']."', 
                    '".$data['created_at']."');";
                $db_query = $this->db->prepare($query_insert);
                $db_query->execute();
                $id = $this->db->lastInsertId();
          
                $result['status'] = 200;
                $result['result'] = $id;
                $result['message'] = 'Datos obtenido de gestión';
                return $result;
            } catch (\Throwable $th) {
                $result['status'] = 404;
                $result['result'] = $th->getMessage();
                $result['query'] = $query_insert;
                $result['message'] = 'A ocurrido un error con la consulta sql (registro incremento)';
                return $result;
            }
    }

    public function registrar_inflacion_v1($data){
        $query_insert = '';
        try {
                $query_insert = "INSERT INTO cont_inflaciones (contrato_id, contrato_detalle_id, fecha, tipo_periodicidad_id, numero, tipo_anio_mes, moneda_id, 
                                porcentaje_anadido, tope_inflacion, minimo_inflacion, tipo_aplicacion_id, status, user_created_id, created_at) VALUES (
                    '".$data['contrato_id']."', 
                    '".$data['contrato_detalle_id']."', 
                    '".$data['fecha']."', 
                    '".$data['tipo_periodicidad_id']."', 
                    '".$data['numero']."', 
                    '".$data['tipo_anio_mes']."', 
                    '".$data['moneda_id']."',  
                    '0',  
                    '0',  
                    '0',  
                    '".$data['tipo_aplicacion_id']."',  
                    1, 
                    '".$data['user_created_id']."', 
                    '".$data['created_at']."');";
                $db_query = $this->db->prepare($query_insert);
                $db_query->execute();
                $id = $this->db->lastInsertId();
          
                $result['status'] = 200;
                $result['result'] = $id;
                $result['message'] = 'Datos obtenido de gestión';
                return $result;
            } catch (\Throwable $th) {
                $result['status'] = 404;
                $result['result'] = $th->getMessage();
                $result['query'] = $query_insert;
                $result['message'] = 'A ocurrido un error con la consulta sql (registro inflacion)';
                return $result;
            }
    }



    // adendas
    public function update_adenda_detalle($data) {
        $query_select = '';
        $query_update = '';
        $query_contrato = '';
        try {
            
            $query_select = "SELECT d.id, d.adenda_id, c.contrato_id, d.nombre_tabla, d.tipo_valor, d.id_del_registro_a_modificar FROM cont_adendas_detalle AS d 
            INNER JOIN cont_adendas AS a ON a.id = d.adenda_id 
            INNER JOIN cont_contrato AS c ON c.contrato_id = a.contrato_id
            WHERE c.tipo_contrato_id = 1 AND c.status = 1 AND a.cancelado_id IS NULL;";
            $db_query = $this->db->prepare($query_select);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            foreach ($resultado as $be) {
                if (!($be['nombre_tabla'] == "cont_propietario" || $be['nombre_tabla'] == "cont_persona" || $be['nombre_tabla'] == "cont_contrato")) {
                    $query_contrato = "SELECT * FROM cont_contrato_detalle AS cd 
                    WHERE cd.contrato_id = ".$be['contrato_id'];
                    $db_query_cont = $this->db->prepare($query_contrato);
                    $db_query_cont->execute();
                    $resultado_cont = $db_query_cont->fetchAll(PDO::FETCH_ASSOC);
                    $resultado_cont = $resultado_cont[0];

                    $data_update['id'] = $be['id'];
                    $data_update['contrato_detalle_id'] = $resultado_cont['id'];

                    $query_update = "UPDATE cont_adendas_detalle SET contrato_detalle_id = ".$data_update['contrato_detalle_id']." WHERE id = ".$data_update['id'];
                    $db_query_update = $this->db->prepare($query_update);
                    $db_query_update->execute();
                }

                if ($be['tipo_valor'] == "decimal" || $be['tipo_valor'] == "int" || $be['tipo_valor'] == "varchar" || $be['tipo_valor'] == "date" || $be['tipo_valor'] == "select_option") {

                    if ( intval($be['id_del_registro_a_modificar']) == 0) {
                        
                        if ($be['nombre_tabla'] == "cont_condicion_economica") {
                            $query_contrato = "SELECT * FROM cont_condicion_economica AS ce 
                            WHERE ce.contrato_id = ".$be['contrato_id'];
                            $db_query_cont = $this->db->prepare($query_contrato);
                            $db_query_cont->execute();
                            $resultado_cont = $db_query_cont->fetchAll(PDO::FETCH_ASSOC);
                            $resultado_cont = $resultado_cont[0];
        
                            $data_update['id'] = $be['id'];
                            $data_update['condicion_economica_id'] = $resultado_cont['condicion_economica_id'];
        
                            $query_update = "UPDATE cont_adendas_detalle SET id_del_registro_a_modificar = ".$data_update['condicion_economica_id']." WHERE id = ".$data_update['id'];
                            $db_query_update = $this->db->prepare($query_update);
                            $db_query_update->execute();
                        }
    
                        if ($be['nombre_tabla'] == "cont_inmueble") {
                            $query_contrato = "SELECT * FROM cont_inmueble AS i 
                            WHERE i.contrato_id = ".$be['contrato_id'];
                            $db_query_cont = $this->db->prepare($query_contrato);
                            $db_query_cont->execute();
                            $resultado_cont = $db_query_cont->fetchAll(PDO::FETCH_ASSOC);
                            $resultado_cont = $resultado_cont[0];
        
                            $data_update['id'] = $be['id'];
                            $data_update['inmueble_id'] = $resultado_cont['id'];
        
                            $query_update = "UPDATE cont_adendas_detalle SET id_del_registro_a_modificar = ".$data_update['inmueble_id']." WHERE id = ".$data_update['id'];
                            $db_query_update = $this->db->prepare($query_update);
                            $db_query_update->execute();
                        }
    
                        if ($be['nombre_tabla'] == "cont_incrementos" ) {
                            $query_contrato = "SELECT * FROM cont_incrementos AS i 
                            WHERE i.contrato_id = ".$be['contrato_id'];
                            $db_query_cont = $this->db->prepare($query_contrato);
                            $db_query_cont->execute();
                            $resultado_cont = $db_query_cont->fetchAll(PDO::FETCH_ASSOC);
                            $resultado_cont = $resultado_cont[0];
        
                            $data_update['id'] = $be['id'];
                            $data_update['incremento_id'] = $resultado_cont['id'];
        
                            $query_update = "UPDATE cont_adendas_detalle SET id_del_registro_a_modificar = ".$data_update['incremento_id']." WHERE id = ".$data_update['id'];
                            $db_query_update = $this->db->prepare($query_update);
                            $db_query_update->execute();
                        }
    
                        if ($be['nombre_tabla'] == "cont_persona") {
                            $query_contrato = "SELECT p.id FROM cont_persona AS p
                            INNER JOIN cont_propietario AS pr ON pr.persona_id = p.id
                            WHERE pr.status = 1 AND pr.contrato_id = ".$be['contrato_id'];
                            $db_query_cont = $this->db->prepare($query_contrato);
                            $db_query_cont->execute();
                            $resultado_cont = $db_query_cont->fetchAll(PDO::FETCH_ASSOC);
                            $resultado_cont = $resultado_cont[0];
        
                            $data_update['id'] = $be['id'];
                            $data_update['personal_id'] = $resultado_cont['id'];
        
                            $query_update = "UPDATE cont_adendas_detalle SET id_del_registro_a_modificar = ".$data_update['personal_id']." WHERE id = ".$data_update['id'];
                            $db_query_update = $this->db->prepare($query_update);
                            $db_query_update->execute();
                        }
                    }
                }
                
                


            }

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query_update'] = $query_update;
            $result['query_contrato'] = $query_contrato;
            $result['message'] = 'A ocurrido un error con la consulta sql (update adendas)';
            return $result;
        }

    }

     // adendas
    public function migracion_de_tiendas($data) {
        $query_select = '';
        $query_insert = '';
        $query_update = '';
        try {
            
   
            $query_select = "SELECT l.id, l.cc_id, l.red_id, l.zona_id, l.razon_social_id FROM tbl_locales AS l WHERE l.cc_id = '".$data['cc_id']."'";
            $db_query = $this->db->prepare($query_select);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);
            if (!$resultado) {
                $result['status'] = 404;
                $result['result'] = $data['cc_id'];
                $result['querys'] = [
                    'query_select' => $query_select,
                    'query_insert' => $query_insert,
                    'query_update' => $query_update,
                ];
                $result['message'] = 'No se encontro el local';
                return $result;
            }
            foreach ($resultado as $be) {

                $red_id = !Empty(trim($be['red_id'])) ? $be['red_id'] : NULL;
                $zona_id = !Empty(trim($be['zona_id'])) ? $be['zona_id'] : NULL;
                $razon_social_id = !Empty(trim($be['razon_social_id'])) ? $be['razon_social_id'] : NULL;

                $query_insert = "INSERT INTO tbl_migraciones_detalle_locales (
                    migracion_id, 
                    local_id, 
                    cc_id, 
                    red_id_old, 
                    zona_id_old,
                    razon_social_id_old, 
                    red_id_new, 
                    zona_id_new,
                    razon_social_id_new, 
                    status, 
                    created_at) VALUES (
                        '1',
                        '".$be['id']."',
                        '".$be['cc_id']."',
                        ".$red_id.",
                        ".$zona_id.",
                        ".$razon_social_id.",

                        '".$data['red_id']."',
                        '".$data['zona_id']."',
                        '".$data['razon_social_id']."',
                        '1',
                        '".date('Y-m-d H:i:s')."')";
                $db_query_insert = $this->db->prepare($query_insert);
                $db_query_insert->execute();

                $query_update = "UPDATE tbl_locales SET zona_id = ".$data['zona_id'].", red_id = ".$data['red_id'].", razon_social_id = ".$data['razon_social_id'].", migracion_id = 1 WHERE cc_id = '".$be['cc_id']."' ";
                $db_query_update = $this->db->prepare($query_update);
                $db_query_update->execute();
            }

            $result['status'] = 200;
            $result['result'] = $data['cc_id'];
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['querys'] = [
                'query_select' => $query_select,
                'query_insert' => $query_insert,
                'query_update' => $query_update,
            ];
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }

    }


    public function importacion_codigo_sap($data) {
        $query_select = '';
        try {
            
            $query_select = "SELECT * FROM tbl_personal_apt AS p   WHERE p.dni = '".$data['NRO_DOCUMENTO']."' AND p.estado = 1";
            $db_query = $this->db->prepare($query_select);
            $db_query->execute();
            $rowCount = $db_query->rowCount();
            if ($rowCount > 0) {
                if ($data['action_data'] == "Update") {
                    $query_update = "UPDATE tbl_personal_apt SET 
                        tipo_documento = '1', 
                        nombre = '".$data['NOMBRES']."', 
                        apellido_paterno = '".$data['APELLIDO_PATERNO']."', 
                        apellido_materno = '".$data['APELLIDO_MATERNO']."', 
                        fecha_ingreso_laboral = '".$data['FECHA_INGRESO']."', 
                        codigo_sap = '".$data['CODIGO_SAP']."', 
                        estado_codigo_sap = 'A', 
                        puesto_codigo_sap = '".$data['CODIGO_PUESTO_SAP']."', 
                        lider_codigo_sap = '".$data['CODIGO_LIDER_SAP']."', 
                        division_codigo_sap = '".$data['CODIGO_DIVISION_SAP']."', 
                        subdivision_codigo_sap = '".$data['CODIGO_SUBDIVISION_SAP']."', 
                        centro_costo_sap = '".$data['CENTRO_COSTO_SAP']."', 
                        razon_social_codigo_sap = '".$data['CODIGO_RAZON_SOCIAL']."', 
                        correo_personal_sap = '".$data['CORREO_PERSONAL']."', 
                        correo_corporativo_sap = '".$data['CORREO_COORPORATIVO']."',
                        user_updated_id = '8929',
                        updated_at = '".date('Y-m-d H:i:s')."'
                    WHERE dni = '".$data['NRO_DOCUMENTO']."'";
                    $db_query = $this->db->prepare($query_update);
                    $db_query->execute();

                    $result['status'] = 200;
                    $result['result'] = $data['NRO_DOCUMENTO'];
                    $result['message'] = 'Se ha modificado correctamente el personal';
                    return $result;
                }
                $result['status'] = 200;
                $result['result'] = $data['NRO_DOCUMENTO'];
                $result['message'] = 'El personal si existe en gestión';
                return $result;
            }else{
                $result['status'] = 400;
                $result['result'] = $data['NRO_DOCUMENTO'];
                $result['message'] = 'El DNI no se encuentra registrado en gestión';
                return $result;
            }
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['querys'] = $query_select;
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }

    }

    public function actualizar_area_contrato_proveedores($data) {
        $query_select = '';
        try {
            
            $query_contrato = "SELECT c.area_responsable_id, a.nombre AS valor_original 
            FROM cont_contrato AS c 
            INNER JOIN tbl_areas AS a ON a.id = c.area_responsable_id
            WHERE c.contrato_id = '".$data['contrato_id']."'";
            $db_query = $this->db->prepare($query_contrato);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);
            $resultado = $resultado[0];

            $query_update = "UPDATE cont_contrato SET area_responsable_id = '".$data['area_id']."' WHERE  contrato_id = '".$data['contrato_id']."'";
            $db_query = $this->db->prepare($query_update);
            $db_query->execute();

            $query_insert = "INSERT INTO cont_auditoria (contrato_id, nombre_tabla, valor_original, nombre_campo, nombre_menu_usuario, nombre_campo_usuario, tipo_valor, valor_int, valor_select_option, user_created_id, created_at) 
            VALUES ('".$data['contrato_id']."', 'cont_contrato', '".$resultado['valor_original']."', 'area_responsable_id', 'Datos Generales', 'Área responsable', 'select_option', '".$data['area_id']."', '".$data['valor_select_option']."', '4014', '".date('Y-m-d H:i:s')."')";
             $db_query = $this->db->prepare($query_insert);
             $db_query->execute();

            $result['status'] = 200;
            $result['result'] = $data['contrato_id'];
            $result['message'] = 'Se ha actualizado el contrato';
            return $result;
           
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['querys'] = $query_select;
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }

    }

    public function registrar_migracion($data) {
        $query_select = '';
        $query_insert = '';
        try {
            
            $query_select = "SELECT * FROM tbl_migraciones AS tm WHERE tm.id = '".$data['migracion_id']."'";
            $db_query = $this->db->prepare($query_select);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);
            if (!$resultado) {
                $query_insert = "INSERT INTO tbl_migraciones (nombre, fecha, status, created_at) VALUES (
                    '".$data['nombre']."',
                    '".$data['fecha']."',
                    '".$data['status']."',
                    '".$data['created_at']."'
                )";
                $db_query_insert = $this->db->prepare($query_insert);
                $db_query_insert->execute();
            }
          
            $result['status'] = 200;
            $result['result'] = $data['migracion_id'];
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }

    }

    public function mover_locales_igh_freegames($data) {
        $query_select = '';
        $query_insert = '';
        $query_update = '';
        try {
            
   
            $query_select = "SELECT l.id, l.cc_id, l.red_id, l.zona_id, l.razon_social_id FROM tbl_locales AS l WHERE l.cc_id = '".$data['ceco']."'";
            $db_query = $this->db->prepare($query_select);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);
            if (!$resultado) {
                $result['status'] = 404;
                $result['result'] = $data['ceco'];
                $result['querys'] = [
                    'query_select' => $query_select,
                    'query_insert' => $query_insert,
                    'query_update' => $query_update,
                ];
                $result['message'] = 'No se encontro el local';
                return $result;
            }
            foreach ($resultado as $be) {

                $red_id = !Empty(trim($be['red_id'])) ? $be['red_id'] : NULL;
                $zona_id = !Empty(trim($be['zona_id'])) ? $be['zona_id'] : NULL;
                $razon_social_id = !Empty(trim($be['razon_social_id'])) ? $be['razon_social_id'] : NULL;

                //insert migracion detalle
                $query_insert = "INSERT INTO tbl_migraciones_detalle_locales (
                    migracion_id, 
                    local_id, 
                    cc_id, 
                    red_id_old, 
                    zona_id_old,
                    razon_social_id_old, 
                    red_id_new, 
                    zona_id_new,
                    razon_social_id_new, 
                    status, 
                    created_at) VALUES (
                        '".$data['migracion_id']."',
                        '".$be['id']."',
                        '".$be['cc_id']."',
                        ".$red_id.",
                        ".$zona_id.",
                        ".$razon_social_id.",
                        '".$data['red_id']."',
                        '".$data['zona_id']."',
                        '".$data['razon_social_id']."',
                        '1',
                        '".date('Y-m-d H:i:s')."')";
                $db_query_insert = $this->db->prepare($query_insert);
                $db_query_insert->execute();

                //Update Local
                $query_update = "UPDATE tbl_locales SET zona_id = ".$data['zona_id'].", red_id = ".$data['red_id'].", razon_social_id = ".$data['razon_social_id'].", migracion_id = ".$data['migracion_id']." WHERE id = '".$be['id']."' ";
                $db_query_update = $this->db->prepare($query_update);
                $db_query_update->execute();
            }

            $result['status'] = 200;
            $result['result'] = $data['ceco'];
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['querys'] = [
                'query_select' => $query_select,
                'query_insert' => $query_insert,
                'query_update' => $query_update,
                'query_update' => $query_update,
            ];
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }

    }
  

}