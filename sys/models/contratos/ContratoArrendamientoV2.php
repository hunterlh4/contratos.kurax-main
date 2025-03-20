<?php
class ContratoArrendamientoV2 extends Model {

    private $data;
    private $id;
  


    public function obtener_contrato_por_cc_id($data) {
        $query = '';
        try {
            
            $response = [];
            //CONTRATOS
            $query = "SELECT 
            c.contrato_id,
            c.tipo_contrato_id,
            c.empresa_suscribe_id,
            c.area_responsable_id,
            c.persona_responsable_id,
            c.nombre_tienda,
            c.observaciones,
            c.etapa_id,
            c.estado_resolucion,
            c.user_created_id,
            c.created_at,
            c.plazo_id,
            c.declaracion_jurada_id,
            c.verificar_giro,
            c.fecha_verificacion_giro,
            c.usuario_verificacion_giro,
            c.cc_id,
            c.jefe_comercial_id,
            c.estado_solicitud,
            c.usuario_responsable_estado_solicitud,
            c.alerta_enviada_id,
            c.motivo_estado_na,
            c.fecha_cambio_estado_solicitud,
            c.dias_habiles,
            c.usuario_responsable_estado_solicitud_primero
            FROM cont_contrato AS c
            WHERE c.tipo_contrato_id = 1 AND c.status = 1 
            AND c.cc_id = '".$data['cc_id']."'
            ORDER BY c.contrato_id DESC
            ";
     
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $contrato = $db_query->fetchAll(PDO::FETCH_ASSOC);
            if (isset($contrato[0])) {
                $contrato = $contrato[0];

                $contrato['new_cc_id'] = $data['new_cc_id'];
                $contrato['zona_id'] = $data['zona_id'];
                
    
                //CONTRATO DETALLE
                $query = "SELECT d.id, d.contrato_id, d.codigo, d.observaciones, d.estado_resolucion, d.user_created_id, d.created_at
                FROM cont_contrato_detalle AS d
                WHERE d.contrato_id = ".$contrato['contrato_id'];
                $db_query = $this->db->prepare($query);
                $db_query->execute();
                $contrato_detalle = $db_query->fetchAll(PDO::FETCH_ASSOC);
    
                $data_contrato_detalle = [];
                foreach ($contrato_detalle as $cont_det) {
    
                    $mdetalle = $cont_det;
                     //INMUEBLE
                    $query = "SELECT 
                    i.id,
                    i.contrato_id,
                    i.contrato_detalle_id,
                    i.ubigeo_id,
                    i.ubicacion,
                    i.area_arrendada,
                    i.num_partida_registral,
                    i.oficina_registral,
                    i.num_suministro_agua,
                    i.tipo_compromiso_pago_agua,
                    i.monto_o_porcentaje_agua,
                    i.ruc_agua,
                    i.id_empresa_servicio_agua,
                    i.num_suministro_luz,
                    i.tipo_compromiso_pago_luz,
                    i.monto_o_porcentaje_luz,
                    i.ruc_luz,
                    i.id_empresa_servicio_luz,
                    i.tipo_compromiso_pago_arbitrios,
                    i.porcentaje_pago_arbitrios,
                    i.latitud,
                    i.longitud,
                    i.direccion_municipal,
                    i.status,
                    i.user_created_id,
                    i.created_at
                    FROM cont_inmueble AS i
                    WHERE  i.status = 1 AND i.contrato_detalle_id =  ".$cont_det['id'];
                    $db_query = $this->db->prepare($query);
                    $db_query->execute();
                    $inmuebles = $db_query->fetchAll(PDO::FETCH_ASSOC);
                    
    
                    $data_inmueble = [];
                    foreach ($inmuebles as $inmu) {
    
                        $inmueble = $inmu;
                        //suministros
                        $query = "SELECT 
                        s.id,
                        s.contrato_id,
                        s.inmueble_id,
                        s.tipo_servicio_id,
                        s.nro_suministro,
                        s.tipo_compromiso_pago_id,
                        s.monto_o_porcentaje,
                        s.status,
                        s.created_at,
                        s.user_created_id
                        FROM cont_inmueble_suministros AS s
                        WHERE  s.status = 1 AND s.inmueble_id =  ".$inmu['id'];
                        $db_query = $this->db->prepare($query);
                        $db_query->execute();
                        $suministros = $db_query->fetchAll(PDO::FETCH_ASSOC);
                        $inmueble['suministros'] = $suministros;
    
                        array_push($data_inmueble, $inmueble);
                    }
                    $mdetalle['inmueble'] = $data_inmueble;
    
                    //CONDICIONES ECONOMICAS
                    $query = "SELECT 
                    ce.condicion_economica_id,
                    ce.contrato_id,
                    ce.contrato_detalle_id,
                    ce.monto_renta,
                    ce.tipo_moneda_id,
                    ce.garantia_monto,
                    ce.tipo_adelanto_id,
                    ce.plazo_id,
                    ce.cant_meses_contrato,
                    ce.fecha_inicio,
                    ce.fecha_fin,
                    ce.num_alerta_vencimiento,
                    ce.se_enviara_alerta,
                    ce.cargo_mantenimiento,
                    ce.fecha_suscripcion,
                    ce.renovacion_automatica,
                    ce.impuesto_a_la_renta_id,
                    ce.carta_de_instruccion_id,
                    ce.numero_cuenta_detraccion,
                    ce.periodo_gracia_id,
                    ce.periodo_gracia_numero,
                    ce.periodo_gracia_inicio,
                    ce.periodo_gracia_fin,
                    ce.tipo_incremento_id,
                    ce.tipo_inflacion_id,
                    ce.tipo_cuota_extraordinaria_id,
                    ce.usuario_contrato_aprobado_id,
                    ce.aprobado_at,
                    ce.dia_de_pago_id,
                    ce.renta_adelantada_id,
                    ce.pago_renta_id,
                    ce.cuota_variable,
                    ce.tipo_venta_id,
                    ce.afectacion_igv_id,
                    ce.status,
                    ce.user_created_id,
                    ce.created_at
                    FROM cont_condicion_economica AS ce
                    WHERE  ce.status = 1 AND ce.contrato_detalle_id =  ".$cont_det['id'];
                    $db_query = $this->db->prepare($query);
                    $db_query->execute();
                    $condicion_economica = $db_query->fetchAll(PDO::FETCH_ASSOC);
                    $mdetalle['condicion_economica'] = $condicion_economica;
    
    
    
                    //INCREMENTOS
                    $query = "SELECT 
                    i.id,
                    i.contrato_id,
                    i.contrato_detalle_id,
                    i.valor,
                    i.tipo_valor_id,
                    i.tipo_continuidad_id,
                    i.a_partir_del_año,
                    i.fecha_cambio,
                    i.estado,
                    i.user_created_id,
                    i.created_at
                    FROM cont_incrementos AS i
                    WHERE  i.estado = 1 AND i.contrato_detalle_id =  ".$cont_det['id'];
                    $db_query = $this->db->prepare($query);
                    $db_query->execute();
                    $incremento = $db_query->fetchAll(PDO::FETCH_ASSOC);
                    $mdetalle['incremento'] = $incremento;
    
    
                    //INFLACIONES
                    $query = "SELECT 
                    i.id,
                    i.contrato_id,
                    i.contrato_detalle_id,
                    i.fecha,
                    i.tipo_periodicidad_id,
                    i.numero,
                    i.tipo_anio_mes,
                    i.moneda_id,
                    i.porcentaje_anadido,
                    i.tope_inflacion,
                    i.minimo_inflacion,
                    i.tipo_aplicacion_id,
                    i.status,
                    i.created_at,
                    i.user_created_id
                    FROM cont_inflaciones AS i
                    WHERE i.status = 1 AND i.contrato_detalle_id =  ".$cont_det['id'];
                    $db_query = $this->db->prepare($query);
                    $db_query->execute();
                    $inflacion = $db_query->fetchAll(PDO::FETCH_ASSOC);
                    $mdetalle['inflacion'] = $inflacion;
    
    
    
                    //CUOTA EXTRAORDINARIA
                    $query = "SELECT 
                    ce.id,
                    ce.contrato_id,
                    ce.contrato_detalle_id,
                    ce.mes,
                    ce.multiplicador,
                    ce.meses_despues,
                    ce.fecha,
                    ce.status,
                    ce.user_created_id,
                    ce.created_at
                    FROM cont_cuotas_extraordinarias AS ce
                    WHERE  ce.status = 1 AND ce.contrato_detalle_id =  ".$cont_det['id'];
                    $db_query = $this->db->prepare($query);
                    $db_query->execute();
                    $cuota_extraordinaria = $db_query->fetchAll(PDO::FETCH_ASSOC);
                    $mdetalle['cuota_extraordinaria'] = $cuota_extraordinaria;
    
    
    
                    //BENEFICIARIO
                    $query = "SELECT 
                    b.id,
                    b.contrato_id,
                    b.contrato_detalle_id,
                    b.tipo_persona_id,
                    b.tipo_docu_identidad_id,
                    b.num_docu,
                    b.nombre,
                    b.forma_pago_id,
                    b.banco_id,
                    b.num_cuenta_bancaria,
                    b.num_cuenta_cci,
                    b.tipo_monto_id,
                    b.monto,
                    b.status,
                    b.user_created_id,
                    b.created_at
                    FROM cont_beneficiarios AS b
                    WHERE b.status = 1 AND b.contrato_detalle_id =  ".$cont_det['id'];
                    $db_query = $this->db->prepare($query);
                    $db_query->execute();
                    $beneficiario = $db_query->fetchAll(PDO::FETCH_ASSOC);
                    $mdetalle['beneficiario'] = $beneficiario;
    
    
                    //RESPONSABLE IR
                    $query = "SELECT 
                    r.id,
                    r.contrato_id,
                    r.contrato_detalle_id,
                    r.tipo_documento_id,
                    r.num_documento,
                    r.nombres,
                    r.estado_emisor,
                    r.porcentaje,
                    r.status,
                    r.user_created_id,
                    r.created_at
                    FROM cont_responsable_ir AS r
                    WHERE  r.status = 1 AND r.contrato_detalle_id =  ".$cont_det['id'];
                    $db_query = $this->db->prepare($query);
                    $db_query->execute();
                    $responsable_ir = $db_query->fetchAll(PDO::FETCH_ASSOC);
                    $mdetalle['responsable_ir'] = $responsable_ir;
    
    
                     //ADELANTO
                     $query = "SELECT 
                     a.id,
                     a.contrato_id,
                     a.contrato_detalle_id,
                     a.num_periodo,
                     a.status,
                     a.user_created_id,
                     a.created_at
                     FROM cont_adelantos AS a
                     WHERE a.status = 1 AND a.contrato_detalle_id =  ".$cont_det['id'];
                     $db_query = $this->db->prepare($query);
                     $db_query->execute();
                     $adelanto = $db_query->fetchAll(PDO::FETCH_ASSOC);
                     $mdetalle['adelanto'] = $adelanto;
    
    
    
                     //ARCHIVOS
                     $query = "SELECT 
                    a.archivo_id,
                    a.contrato_id,
                    a.contrato_detalle_id,
                    a.adenda_id,
                    a.resolucion_contrato_id,
                    a.tipo_archivo_id,
                    a.nombre,
                    a.extension,
                    a.ruta,
                    a.size,
                    a.status,
                    a.user_created_id,
                    a.created_at
                     FROM cont_archivos AS a
                     WHERE a.status = 1 AND a.tipo_archivo_id NOT IN (17) AND a.contrato_detalle_id =  ".$cont_det['id'];
                     $db_query = $this->db->prepare($query);
                     $db_query->execute();
                     $archivo = $db_query->fetchAll(PDO::FETCH_ASSOC);
                     $mdetalle['archivo'] = $archivo;
    
                    
    
                    array_push($data_contrato_detalle, $mdetalle);
                }
    
    
                //PROPIETARIO
                $query = "SELECT pr.persona_id, pr.encargado_impuesto_renta_id, pr.status, pr.user_created_id, pr.created_at 
                FROM cont_propietario AS pr
                WHERE pr.contrato_id = ".$contrato['contrato_id'];
                $db_query = $this->db->prepare($query);
                $db_query->execute();
                $propietarios = $db_query->fetchAll(PDO::FETCH_ASSOC);
    
    
    
                //LICENCIA MUNICIPAL
                $query = "SELECT 
                l.id,
                l.contrato_id,
                l.tipo_archivo_id,
                l.status_licencia,
                l.condicion,
                l.fecha_vencimiento,
                l.fecha_renovacion,
                l.nombre_file,
                l.extension,
                l.size,
                l.ruta,
                l.download_file,
                l.alerta_enviada,
                l.dj_id,
                l.estado,
                l.user_created_id,
                l.created_at
                FROM cont_licencia_municipales AS l
                WHERE l.contrato_id = ".$contrato['contrato_id'];
                $db_query = $this->db->prepare($query);
                $db_query->execute();
                $licencia_municipal = $db_query->fetchAll(PDO::FETCH_ASSOC);
    
    
    
    
                //OBSERVACIONES
                $query = "SELECT 
                o.id,
                o.contrato_id,
                o.adenda_id,
                o.observaciones,
                o.status,
                o.user_created_id,
                o.created_at
                FROM cont_observaciones AS o
                WHERE o.contrato_id = ".$contrato['contrato_id'];
                $db_query = $this->db->prepare($query);
                $db_query->execute();
                $observaciones = $db_query->fetchAll(PDO::FETCH_ASSOC);
                
    
                $query = "SELECT
                l.id,
                l.canal_id,
                l.cc_id,
                l.red_id,
                l.tipo_id,
                l.propiedad_id,
                l.cliente_id,
                l.razon_social_id,
                l.nombre,
                l.descripcion,
                l.zona_id,
                l.ubigeo_id,
                l.ubigeo_cod_depa,
                l.ubigeo_cod_prov,
                l.ubigeo_cod_dist,
                l.direccion,
                l.email,
                l.phone,
                l.fecha_registro,
                l.representante_id,
                l.asesor_id,
                l.estado_legal_id,
                l.area,
                l.otra_casa_apuestas,
                l.administracion_tipo,
                l.otra_casa_apuestas_des,
                l.experiencia_casa_apuestas,
                l.experiencia_casa_apuestas_des,
                l.reportes_mostrar,
                l.username,
                l.password,
                l.password_md5,
                l.estado,
                l.fecha_inicio_operacion,
                l.fecha_fin_operacion,
                l.operativo,
                l.latitud,
                l.longitud,
                l.zona_financiera,
                l.show_web,
                l.contrato_id,
                l.is_test,
                l.fecha_sorteo_tienda_nueva,
                l.status_cont_ag,
                l.trastienda,
                l.estado_terminal_kasnet,
                l.fecha_inicio_garantia,
                l.fecha_fin_garantia,
                l.user_created_id,
                l.created_at
                FROM tbl_locales AS l 
                WHERE l.contrato_id = ".$contrato['contrato_id']." ORDER BY l.id DESC LIMIT 1";
    
                $db_query = $this->db->prepare($query);
                $db_query->execute();
                $locales = $db_query->fetchAll(PDO::FETCH_ASSOC);
                $data_local = [];
                foreach ($locales as $local) {
    
                    $mlocal = $local;
                    //usuario locales
                    $query = "SELECT 
                        ul.id,
                        ul.usuario_id,
                        ul.local_id,
                        ul.estado,
                        ul.user_created_id,
                        ul.created_at
                    FROM tbl_usuarios_locales AS ul
                    WHERE  ul.estado = 1 AND ul.local_id =  ".$local['id'];
                    $db_query = $this->db->prepare($query);
                    $db_query->execute();
                    $usuario_locales = $db_query->fetchAll(PDO::FETCH_ASSOC);
                    $mlocal['usuario_locales'] = $usuario_locales;
    
                    //locales horarios
                    $query = "SELECT 
                        lh.id,
                        lh.local_id,
                        lh.horario_id,
                        lh.started_at
                    FROM tbl_locales_horarios AS lh
                    WHERE lh.local_id =  ".$local['id'];
                    $db_query = $this->db->prepare($query);
                    $db_query->execute();
                    $horario_local = $db_query->fetchAll(PDO::FETCH_ASSOC);
                    $mlocal['horario_local'] = $horario_local;
    
    
                    //local config
                    $query = "SELECT 
                            lc.id,
                            lc.local_id,
                            lc.config_id,
                            lc.config_param,
                            lc.created_at,
                            lc.updated_at
                    FROM tbl_local_config AS lc
                    WHERE lc.local_id =  ".$local['id'];
                    $db_query = $this->db->prepare($query);
                    $db_query->execute();
                    $local_config = $db_query->fetchAll(PDO::FETCH_ASSOC);
                    $mlocal['local_config'] = $local_config;
    
                    array_push($data_local,$mlocal);
                }
    
    
                $result['status'] = 200;
                $result['result'] = [
                    'contrato' => $contrato,
                    'contrato_detalle' => $data_contrato_detalle,
                    'propietarios' => $propietarios,
                    'licencia_municipal' => $licencia_municipal,
                    'observaciones' => $observaciones,
                    'locales' => $data_local,
                ];
                $result['message'] = 'Datos obtenido de gestión';
                return $result;
            }
            $result['status'] = 404;
            $result['result'] = [
                'contrato' => [],
                'contrato_detalle' => [],
                'propietarios' => [],
                'licencia_municipal' => [],
                'observaciones' => [],
                'locales' => [],
            ];
            $result['message'] = 'A ocurrido un error';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
            return $result;
        }

    }

    public function registrar_contrato($data) {
        $query = '';
        try {
            
            $query = "INSERT INTO cont_contrato(
                tipo_contrato_id,
                codigo_correlativo,
                empresa_suscribe_id,
                area_responsable_id,
                persona_responsable_id,
                nombre_tienda,
                observaciones,
                etapa_id,
                status,
                estado_resolucion,
                user_created_id,
                created_at,
                plazo_id,
                declaracion_jurada_id,
                verificar_giro,
                fecha_verificacion_giro,
                usuario_verificacion_giro,
                cc_id,
                jefe_comercial_id,
                estado_solicitud,
                usuario_responsable_estado_solicitud,
                alerta_enviada_id,
                motivo_estado_na,
                fecha_cambio_estado_solicitud,
                dias_habiles,
                usuario_responsable_estado_solicitud_primero,
                migracion_id
            )VALUES(
                ".$data['tipo_contrato_id'].",
                '".$data['codigo_correlativo']."',
                ".$data['empresa_suscribe_id'].",
                ".$data['area_responsable_id'].",
                ".$data['persona_responsable_id'].",
                ".$data['nombre_tienda'].",
                ".$data['observaciones'].",
                ".$data['etapa_id'].",
                ".$data['status'].",
                ".$data['estado_resolucion'].",
                ".$data['user_created_id'].",
                ".$data['created_at'].",
                ".$data['plazo_id'].",
                ".$data['declaracion_jurada_id'].",
                ".$data['verificar_giro'].",
                ".$data['fecha_verificacion_giro'].",
                ".$data['usuario_verificacion_giro'].",
                ".$data['cc_id'].",
                ".$data['jefe_comercial_id'].",
                ".$data['estado_solicitud'].",
                ".$data['usuario_responsable_estado_solicitud'].",
                ".$data['alerta_enviada_id'].",
                ".$data['motivo_estado_na'].",
                ".$data['fecha_cambio_estado_solicitud'].",
                ".$data['dias_habiles'].",
                ".$data['usuario_responsable_estado_solicitud_primero'].",
                ".$data['migracion_id']."
			)";
     
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();

            $result['status'] = 200;
            $result['result'] = $id;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
            return $result;
        }

    }

    public function registrar_contrato_detalle($data) {
        $query = '';
        try {
            
            $query = "INSERT INTO cont_contrato_detalle(
                contrato_id,
                codigo,
                observaciones,
                estado_resolucion,
                status,
                user_created_id,
                created_at
            )VALUES(
                ".$data['contrato_id'].",
                '".$data['codigo']."',
                ".$data['observaciones'].",
                ".$data['estado_resolucion'].",
                ".$data['status'].",
                ".$data['user_created_id'].",
                '".$data['created_at']."'
			)";
     
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();

            $result['status'] = 200;
            $result['result'] = $id;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
            return $result;
        }

    }

    public function registrar_inmueble($data) {
        $query = '';
        try {
            
            $query = "INSERT INTO cont_inmueble (
                contrato_id,
                contrato_detalle_id,
                ubigeo_id,
                ubicacion,
                area_arrendada,
                num_partida_registral,
                oficina_registral,
                num_suministro_agua,
                tipo_compromiso_pago_agua,
                monto_o_porcentaje_agua,
                ruc_agua,
                id_empresa_servicio_agua,
                num_suministro_luz,
                tipo_compromiso_pago_luz,
                monto_o_porcentaje_luz,
                ruc_luz,
                id_empresa_servicio_luz,
                tipo_compromiso_pago_arbitrios,
                porcentaje_pago_arbitrios,
                latitud,
                longitud,
                direccion_municipal,
                status,
                user_created_id,
                created_at
            )VALUES(
                ".$data['contrato_id'].",
                ".$data['contrato_detalle_id'].",
                ".$data['ubigeo_id'].",
                ".$data['ubicacion'].",
                ".$data['area_arrendada'].",
                ".$data['num_partida_registral'].",
                ".$data['oficina_registral'].",
                ".$data['num_suministro_agua'].",
                ".$data['tipo_compromiso_pago_agua'].",
                ".$data['monto_o_porcentaje_agua'].",
                ".$data['ruc_agua'].",
                ".$data['id_empresa_servicio_agua'].",
                ".$data['num_suministro_luz'].",
                ".$data['tipo_compromiso_pago_luz'].",
                ".$data['monto_o_porcentaje_luz'].",
                ".$data['ruc_luz'].",
                ".$data['id_empresa_servicio_luz'].",
                ".$data['tipo_compromiso_pago_arbitrios'].",
                ".$data['porcentaje_pago_arbitrios'].",
                ".$data['latitud'].",
                ".$data['longitud'].",
                ".$data['direccion_municipal'].",
                ".$data['status'].",
                ".$data['user_created_id'].",
                ".$data['created_at']."
			)";

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();

            $result['status'] = 200;
            $result['result'] = $id;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
            return $result;
        }

    }

    public function registrar_suministro($data) {
        $query = '';
        try {
            
            $query = "INSERT INTO cont_inmueble_suministros (
                contrato_id,
                inmueble_id,
                tipo_servicio_id,
                nro_suministro,
                tipo_compromiso_pago_id,
                monto_o_porcentaje,
                status,
                created_at,
                user_created_id
            )VALUES(
                ".$data['contrato_id'].",
                ".$data['inmueble_id'].",
                ".$data['tipo_servicio_id'].",
                ".$data['nro_suministro'].",
                ".$data['tipo_compromiso_pago_id'].",
                ".$data['monto_o_porcentaje'].",
                ".$data['status'].",
                ".$data['created_at'].",
                ".$data['user_created_id']."
			)";

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();

            $result['status'] = 200;
            $result['result'] = $id;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
            return $result;
        }

    }

    public function registrar_condicion_economica($data) {
        $query = '';
        try {
            
            $query = "INSERT INTO cont_condicion_economica (
                contrato_id,
                contrato_detalle_id,
                monto_renta,
                tipo_moneda_id,
                garantia_monto,
                tipo_adelanto_id,
                plazo_id,
                cant_meses_contrato,
                fecha_inicio,
                fecha_fin,
                num_alerta_vencimiento,
                se_enviara_alerta,
                cargo_mantenimiento,
                fecha_suscripcion,
                renovacion_automatica,
                impuesto_a_la_renta_id,
                carta_de_instruccion_id,
                numero_cuenta_detraccion,
                periodo_gracia_id,
                periodo_gracia_numero,
                periodo_gracia_inicio,
                periodo_gracia_fin,
                tipo_incremento_id,
                tipo_inflacion_id,
                tipo_cuota_extraordinaria_id,
                usuario_contrato_aprobado_id,
                aprobado_at,
                dia_de_pago_id,
                renta_adelantada_id,
                pago_renta_id,
                cuota_variable,
                tipo_venta_id,
                afectacion_igv_id,
                status,
                user_created_id,
                created_at
            )VALUES(
                ".$data['contrato_id'].",
                ".$data['contrato_detalle_id'].",
                ".$data['monto_renta'].",
                ".$data['tipo_moneda_id'].",
                ".$data['garantia_monto'].",
                ".$data['tipo_adelanto_id'].",
                ".$data['plazo_id'].",
                ".$data['cant_meses_contrato'].",
                ".$data['fecha_inicio'].",
                ".$data['fecha_fin'].",
                ".$data['num_alerta_vencimiento'].",
                ".$data['se_enviara_alerta'].",
                ".$data['cargo_mantenimiento'].",
                ".$data['fecha_suscripcion'].",
                ".$data['renovacion_automatica'].",
                ".$data['impuesto_a_la_renta_id'].",
                ".$data['carta_de_instruccion_id'].",
                ".$data['numero_cuenta_detraccion'].",
                ".$data['periodo_gracia_id'].",
                ".$data['periodo_gracia_numero'].",
                ".$data['periodo_gracia_inicio'].",
                ".$data['periodo_gracia_fin'].",
                ".$data['tipo_incremento_id'].",
                ".$data['tipo_inflacion_id'].",
                ".$data['tipo_cuota_extraordinaria_id'].",
                ".$data['usuario_contrato_aprobado_id'].",
                ".$data['aprobado_at'].",
                ".$data['dia_de_pago_id'].",
                ".$data['renta_adelantada_id'].",
                ".$data['pago_renta_id'].",
                ".$data['cuota_variable'].",
                ".$data['tipo_venta_id'].",
                ".$data['afectacion_igv_id'].",
                ".$data['status'].",
                ".$data['user_created_id'].",
                ".$data['created_at']."
			)";

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();

            $result['status'] = 200;
            $result['result'] = $id;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
            return $result;
        }

    }

    public function registrar_incremento($data) {
        $query = '';
        try {
            
            $query = "INSERT INTO cont_incrementos (
                contrato_id,
                contrato_detalle_id,
                valor,
                tipo_valor_id,
                tipo_continuidad_id,
                a_partir_del_año,
                fecha_cambio,
                estado,
                user_created_id,
                created_at
            )VALUES(
                ".$data['contrato_id'].",
                ".$data['contrato_detalle_id'].",
                ".$data['valor'].",
                ".$data['tipo_valor_id'].",
                ".$data['tipo_continuidad_id'].",
                ".$data['a_partir_del_año'].",
                ".$data['fecha_cambio'].",
                ".$data['estado'].",
                ".$data['user_created_id'].",
                ".$data['created_at']."
			)";

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();

            $result['status'] = 200;
            $result['result'] = $id;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
            return $result;
        }

    }

    public function registrar_inflacion($data) {
        $query = '';
        try {
            
            $query = "INSERT INTO cont_inflaciones (
                 contrato_id,
                contrato_detalle_id,
                fecha,
                tipo_periodicidad_id,
                numero,
                tipo_anio_mes,
                moneda_id,
                porcentaje_anadido,
                tope_inflacion,
                minimo_inflacion,
                tipo_aplicacion_id,
                status,
                created_at,
                user_created_id
            )VALUES(
                ".$data['contrato_id'].",
                ".$data['contrato_detalle_id'].",
                ".$data['fecha'].",
                ".$data['tipo_periodicidad_id'].",
                ".$data['numero'].",
                ".$data['tipo_anio_mes'].",
                ".$data['moneda_id'].",
                ".$data['porcentaje_anadido'].",
                ".$data['tope_inflacion'].",
                ".$data['minimo_inflacion'].",
                ".$data['tipo_aplicacion_id'].",
                ".$data['status'].",
                ".$data['created_at'].",
                ".$data['user_created_id']."
			)";

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();

            $result['status'] = 200;
            $result['result'] = $id;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
            return $result;
        }

    }

    public function registrar_cuota_extraordinaria($data) {
        $query = '';
        try {
            
            $query = "INSERT INTO cont_cuotas_extraordinarias (
                contrato_id,
                contrato_detalle_id,
                mes,
                multiplicador,
                meses_despues,
                fecha,
                status,
                user_created_id,
                created_at
            )VALUES(
                ".$data['contrato_id'].",
                ".$data['contrato_detalle_id'].",
                ".$data['mes'].",
                ".$data['multiplicador'].",
                ".$data['meses_despues'].",
                ".$data['fecha'].",
                ".$data['status'].",
                ".$data['user_created_id'].",
                ".$data['created_at']."
			)";

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();

            $result['status'] = 200;
            $result['result'] = $id;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
            return $result;
        }

    }

    public function registrar_beneficiario($data) {
        $query = '';
        try {
            
            $query = "INSERT INTO cont_beneficiarios (
                contrato_id,
                contrato_detalle_id,
                tipo_persona_id,
                tipo_docu_identidad_id,
                num_docu,
                nombre,
                forma_pago_id,
                banco_id,
                num_cuenta_bancaria,
                num_cuenta_cci,
                tipo_monto_id,
                monto,
                status,
                user_created_id,
                created_at
            )VALUES(
                ".$data['contrato_id'].",
                ".$data['contrato_detalle_id'].",
                ".$data['tipo_persona_id'].",
                ".$data['tipo_docu_identidad_id'].",
                ".$data['num_docu'].",
                ".$data['nombre'].",
                ".$data['forma_pago_id'].",
                ".$data['banco_id'].",
                ".$data['num_cuenta_bancaria'].",
                ".$data['num_cuenta_cci'].",
                ".$data['tipo_monto_id'].",
                ".$data['monto'].",
                ".$data['status'].",
                ".$data['user_created_id'].",
                ".$data['created_at']."
			)";

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();

            $result['status'] = 200;
            $result['result'] = $id;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
            return $result;
        }

    }

    public function registrar_responsable_ir($data) {
        $query = '';
        try {
            
            $query = "INSERT INTO cont_responsable_ir (
                contrato_id,
                contrato_detalle_id,
                tipo_documento_id,
                num_documento,
                nombres,
                estado_emisor,
                porcentaje,
                status,
                user_created_id,
                created_at
            )VALUES(
                ".$data['contrato_id'].",
                ".$data['contrato_detalle_id'].",
                ".$data['tipo_documento_id'].",
                ".$data['num_documento'].",
                ".$data['nombres'].",
                ".$data['estado_emisor'].",
                ".$data['porcentaje'].",
                ".$data['status'].",
                ".$data['user_created_id'].",
                ".$data['created_at']."
			)";

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();

            $result['status'] = 200;
            $result['result'] = $id;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
            return $result;
        }

    }

    public function registrar_adelanto($data) {
        $query = '';
        try {
            
            $query = "INSERT INTO cont_adelantos (
                contrato_id,
                contrato_detalle_id,
                num_periodo,
                status,
                user_created_id,
                created_at
            )VALUES(
                ".$data['contrato_id'].",
                ".$data['contrato_detalle_id'].",
                ".$data['num_periodo'].",
                ".$data['status'].",
                ".$data['user_created_id'].",
                ".$data['created_at']."
			)";

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();

            $result['status'] = 200;
            $result['result'] = $id;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
            return $result;
        }

    }

    public function registrar_archivos($data) {
        $query = '';
        try {
            
            $query = "INSERT INTO cont_archivos (
                contrato_id,
                contrato_detalle_id,
                resolucion_contrato_id,
                tipo_archivo_id,
                nombre,
                extension,
                ruta,
                size,
                status,
                user_created_id,
                created_at
            )VALUES(
                ".$data['contrato_id'].",
                ".$data['contrato_detalle_id'].",
                ".$data['resolucion_contrato_id'].",
                ".$data['tipo_archivo_id'].",
                ".$data['nombre'].",
                ".$data['extension'].",
                ".$data['ruta'].",
                ".$data['size'].",
                ".$data['status'].",
                ".$data['user_created_id'].",
                ".$data['created_at']."
			)";

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();

            $result['status'] = 200;
            $result['result'] = $id;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
            return $result;
        }

    }

    public function registrar_propietario($data) {
        $query = '';
        try {
            
            $query = "INSERT INTO cont_propietario (
                contrato_id,
                persona_id,
                encargado_impuesto_renta_id,
                status,
                user_created_id,
                created_at
            )VALUES(
                ".$data['contrato_id'].",
                ".$data['persona_id'].",
                ".$data['encargado_impuesto_renta_id'].",
                ".$data['status'].",
                ".$data['user_created_id'].",
                ".$data['created_at']."
			)";

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();

            $result['status'] = 200;
            $result['result'] = $id;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
            return $result;
        }

    }

    public function registrar_licencia_municipal($data) {
        $query = '';
        try {
            
            $query = "INSERT INTO cont_licencia_municipales (
                contrato_id,
                tipo_archivo_id,
                status_licencia,
                condicion,
                fecha_vencimiento,
                fecha_renovacion,
                nombre_file,
                extension,
                size,
                ruta,
                download_file,
                alerta_enviada,
                dj_id,
                estado,
                user_created_id,
                created_at
            )VALUES(
                ".$data['contrato_id'].",
                ".$data['tipo_archivo_id'].",
                ".$data['status_licencia'].",
                ".$data['condicion'].",
                ".$data['fecha_vencimiento'].",
                ".$data['fecha_renovacion'].",
                ".$data['nombre_file'].",
                ".$data['extension'].",
                ".$data['size'].",
                ".$data['ruta'].",
                ".$data['download_file'].",
                ".$data['alerta_enviada'].",
                ".$data['dj_id'].",
                ".$data['estado'].",
                ".$data['user_created_id'].",
                ".$data['created_at']."
			)";

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();

            $result['status'] = 200;
            $result['result'] = $id;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
            return $result;
        }

    }

    public function registrar_observacion($data) {
        $query = '';
        try {
            
            $query = "INSERT INTO cont_observaciones (
                contrato_id,
                observaciones,
                status,
                user_created_id,
                created_at
            )VALUES(
                ".$data['contrato_id'].",
                ".$data['observaciones'].",
                ".$data['status'].",
                ".$data['user_created_id'].",
                ".$data['created_at']."
			)";

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();

            $result['status'] = 200;
            $result['result'] = $id;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
            return $result;
        }

    }

    public function registrar_local($data) {
        $query = '';
        try {
            
            $query = "INSERT INTO tbl_locales (
                canal_id,
                cc_id,
                red_id,
                tipo_id,
                propiedad_id,
                cliente_id,
                razon_social_id,
                nombre,
                descripcion,
                zona_id,
                ubigeo_id,
                ubigeo_cod_depa,
                ubigeo_cod_prov,
                ubigeo_cod_dist,
                direccion,
                email,
                phone,
                fecha_registro,
                representante_id,
                asesor_id,
                estado_legal_id,
                area,
                otra_casa_apuestas,
                administracion_tipo,
                otra_casa_apuestas_des,
                experiencia_casa_apuestas,
                experiencia_casa_apuestas_des,
                reportes_mostrar,
                username,
                password,
                password_md5,
                estado,
                fecha_inicio_operacion,
                fecha_fin_operacion,
                operativo,
                latitud,
                longitud,
                zona_financiera,
                show_web,
                contrato_id,
                is_test,
                fecha_sorteo_tienda_nueva,
                status_cont_ag,
                trastienda,
                estado_terminal_kasnet,
                fecha_inicio_garantia,
                fecha_fin_garantia,
                user_created_id,
                created_at,
                migracion_id
            )VALUES(";
            $query2 = "
                ".$data['canal_id'].",
                ".$data['cc_id'].",
                ".$data['red_id'].",
                ".$data['tipo_id'].",
                ".$data['propiedad_id'].",
                ".$data['cliente_id'].",
                ".$data['razon_social_id'].",
                ".$data['nombre'].",
                ".$data['descripcion'].",
                ".$data['zona_id'].",
                ".$data['ubigeo_id'].",
                ".$data['ubigeo_cod_depa'].",
                ".$data['ubigeo_cod_prov'].",
                ".$data['ubigeo_cod_dist'].",
                ".$data['direccion'].",
                ".$data['email'].",
                ".$data['phone'].",
                ".$data['fecha_registro'].",
                ".$data['representante_id'].",
                ".$data['asesor_id'].",
                ".$data['estado_legal_id'].",
                ".$data['area'].",
                ".$data['otra_casa_apuestas'].",
                ".$data['administracion_tipo'].",
                ".$data['otra_casa_apuestas_des'].",
                ".$data['experiencia_casa_apuestas'].",
                ".$data['experiencia_casa_apuestas_des'].",
                ".$data['reportes_mostrar'].",
                ".$data['username'].",
                ".$data['password'].",
                ".$data['password_md5'].",
                ".$data['estado'].",
                ".$data['fecha_inicio_operacion'].",
                ".$data['fecha_fin_operacion'].",
                ".$data['operativo'].",
                ".$data['latitud'].",
                ".$data['longitud'].",
                ".$data['zona_financiera'].",
                ".$data['show_web'].",
                ".$data['contrato_id'].",
                ".$data['is_test'].",
                ".$data['fecha_sorteo_tienda_nueva'].",
                ".$data['status_cont_ag'].",
                ".$data['trastienda'].",
                ".$data['estado_terminal_kasnet'].",
                ".$data['fecha_inicio_garantia'].",
                ".$data['fecha_fin_garantia'].",
                ".$data['user_created_id'].",
                ".$data['created_at'].",
                ".$data['migracion_id']."
			)";
            
            $query .= $query2;
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();

            $result['status'] = 200;
            $result['result'] = $id;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
            return $result;
        }

    }

    public function registrar_usuario_locales($data) {
        $query = '';
        try {
            
            $query = "INSERT INTO tbl_usuarios_locales (
                usuario_id,
                local_id,
                estado,
                user_created_id,
                created_at
            )VALUES(
                ".$data['usuario_id'].",
                ".$data['local_id'].",
                ".$data['estado'].",
                ".$data['user_created_id'].",
                ".$data['created_at']."
			)";

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();

            $result['status'] = 200;
            $result['result'] = $id;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
            return $result;
        }

    }

    public function registrar_local_horario($data) {
        $query = '';
        try {
            
            $query = "INSERT INTO tbl_locales_horarios (
                local_id,
                horario_id,
                started_at
            )VALUES(
                ".$data['local_id'].",
                ".$data['horario_id'].",
                ".$data['started_at']."
			)";

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();

            $result['status'] = 200;
            $result['result'] = $id;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
            return $result;
        }

    }

    public function registrar_local_config($data) {
        $query = '';
        try {
            
            $query = "INSERT INTO tbl_local_config (
                local_id,
                config_id,
                config_param,
                created_at,
                updated_at
            )VALUES(
                ".$data['local_id'].",
                ".$data['config_id'].",
                ".$data['config_param'].",
                ".$data['created_at'].",
                ".$data['updated_at']."
			)";

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();

            $result['status'] = 200;
            $result['result'] = $id;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
            return $result;
        }

    }

    public function obtener_local_cajas_por_cc_id($data) {
        $query = '';
        try {
            
            $query = "SELECT 
                lc.id,
                lc.local_id,
                lc.caja_tipo_id,
                lc.nombre,
                lc.descripcion,
                lc.proveedor_id,
                lc.estado
            FROM tbl_local_cajas AS lc
            INNER JOIN tbl_locales AS l ON l.id = lc.local_id
            WHERE l.cc_id = '".$data['cc_id']."' ORDER BY lc.id ASC";

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $contrato = $db_query->fetchAll(PDO::FETCH_ASSOC);
            
            $result['status'] = 200;
            $result['result'] = $contrato;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
            return $result;
        }

    }

    public function obtener_local_por_cc_id($cc_id) {
        $query = '';
        try {
            
            $query = "SELECT 
                lc.id,
                lc.cc_id
            FROM tbl_locales AS lc
            WHERE lc.cc_id = '".$cc_id."' ORDER BY lc.id DESC";

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $contrato = $db_query->fetchAll(PDO::FETCH_ASSOC);
            if (!isset($contrato[0])) {
                $result['status'] = 404;
                $result['result'] = '';
                $result['query'] = '';
                $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
                return $result;
            }
            
            $result['status'] = 200;
            $result['result'] = $contrato[0];
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
            return $result;
        }

    }

    public function registrar_local_caja($data) {
        $query = '';
        try {
            
            $query = "INSERT INTO tbl_local_cajas (
                local_id,
                caja_tipo_id,
                nombre,
                descripcion,
                proveedor_id,
                estado
            ) VALUES (
                ".$data['local_id'].",
                ".$data['caja_tipo_id'].",
                ".$data['nombre'].",
                ".$data['descripcion'].",
                ".$data['proveedor_id'].",
                ".$data['estado']."
            )";

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();

            $result['status'] = 200;
            $result['result'] = $id;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
            return $result;
        }

    }





}