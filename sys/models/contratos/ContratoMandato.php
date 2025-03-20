<?php
class ContratoMandato extends Model
{

    public function registrar($data)
    {
        $query = '';
        try {

            $query = " INSERT INTO cont_contrato(
                tipo_contrato_id,
                codigo_correlativo,
                empresa_suscribe_id,
                area_responsable_id, 
                abogado_id,
                aprobador_id,
                cargo_aprobador_id,
                observaciones,
                fecha_suscripcion_contrato,
                status,
                etapa_id,
                user_created_id,
                created_at)
                VALUES(
                " . $data["tipo_contrato_id"] . ",
                " . $data["codigo_correlativo"] . ",
                " . $data["empresa_suscribe_id"] . ",
                " . $data["area_responsable_id"] . ", 
                " . $data["abogado_id"] . ", 
                NULL,
                NULL,
                '" . $data["observaciones"] . "',
                '" . $data["fecha_suscripcion"] . "',
                " . $data["status"] . ",
                " . $data["etapa_id"] . ",
                " . $data["user_created_id"] . ",
                '" . $data["created_at"] . "')";

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();

            $result['status'] = 200;
            $result['result'] = (int) $id;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error al registrar el contrato';
            return $result;
        }
    }
    public function registrar_mandato($data)
    {
        $query = '';
        try {
            $query = "INSERT INTO cont_mandato ( 
                        idcontrato,
                        mandante_antecedente,
                        mandataria_objetivo,
                        mandataria_retribucion,
                        plazo_duracion,
                        fecha_inicio,
                        fecha_fin
                     
                    ) VALUES ( 
                        " . (isset($data['idcontrato']) ? $data["idcontrato"] : "NULL") . ",
                        '" . (isset($data["mandante_antecedente"]) ? $data["mandante_antecedente"] : "") . "',
                        '" . (isset($data["mandataria_objetivo"]) ? $data["mandataria_objetivo"] : "") . "',
                        '" . (isset($data["mandataria_retribucion"]) ? $data["mandataria_retribucion"] : "NULL") . "',
                        '" . (isset($data["plazo_duracion"]) ? $data["plazo_duracion"] : "NULL") . "',
                        '" . (isset($data["fecha_inicio"]) ? $data["fecha_inicio"] : "NULL") . "',
                        '" . (isset($data["fecha_fin"]) ? $data["fecha_fin"] : "NULL") . "'
                    )";

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();

            $result['status'] = 200;
            $result['result'] = $id;
            $result['message'] = 'Se ha registrado exitosamente la locación';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'Ha ocurrido un error con la consulta SQL (Locación)';
            return $result;
        }
    }

    //////////////////////////////////////////////////////////////////////////
    public function obtener_solicitud_contrato_mandato_detallado($contrato_id) {
        try {
            // Inicializar el array que contendrá toda la data
            $contratos = array();
    
            // Obtener datos generales del contrato
            $query_contrato = "SELECT
                            c.contrato_id,
                            c.codigo_correlativo,
                            c.empresa_suscribe_id,
                            c.abogado_id,
                            c.user_created_id,
                            c.created_at,
                            c.fecha_suscripcion_contrato,
                            c.fecha_aprobacion,
                            c.estado_aprobacion,
                            c.ciudad,
                            rs.nombre AS empresa_suscribe,
                            rs.num_ruc AS empresa_ruc,
                            rs.direccion AS empresa_direccion,
                            rs.representante_legal AS empresa_representante,
                            u.usuario AS user_created,
                            CONCAT( p.nombre, ' ', p.apellido_paterno, ' ', p.apellido_materno ) AS abogado,
                            car.nombre AS cargo_abogado 
                        FROM
                            cont_contrato c
                            LEFT JOIN tbl_razon_social rs ON c.empresa_suscribe_id = rs.id
                            LEFT JOIN tbl_usuarios u ON c.user_created_id = u.id
                            LEFT JOIN tbl_usuarios ua ON c.abogado_id = ua.id
                            LEFT JOIN tbl_personal_apt p ON ua.personal_id = p.id
                            LEFT JOIN tbl_cargos car ON p.cargo_id = car.id 
                        WHERE
                            c.contrato_id = :contrato_id";
            $stmt_contrato = $this->db->prepare($query_contrato);
            $stmt_contrato->execute(['contrato_id' => $contrato_id]);
            $datos_generales = $stmt_contrato->fetch(PDO::FETCH_ASSOC);
    
            // Obtener datos del mandato
            $query_mandato = "SELECT
                            cm.mandante_antecedente,
                            cm.mandataria_objetivo,
                            cm.mandataria_retribucion,
                            cm.fecha_inicio,
                            cm.fecha_fin,
                            cm.plazo_duracion 
                        FROM
                            cont_mandato cm 
                        WHERE
                            cm.idcontrato = :contrato_id";
            $stmt_mandato = $this->db->prepare($query_mandato);
            $stmt_mandato->execute(['contrato_id' => $contrato_id]);
            $data_mandato = $stmt_mandato->fetchAll(PDO::FETCH_ASSOC);
    
            // Construir el array de respuesta
            $contratos = [
                'datos_generales' => $datos_generales,
                'data_mandato' => $data_mandato
            ];

            return $contratos;
        } catch (Exception $e) {
            // Manejar la excepción
            throw new Exception("Error al obtener los datos del contrato: " . $e->getMessage());
        }
    }
    ///////////////////////////////////////////////////////////////////////////
    public function registrar_contraprestacion($data)
    {
        $query_insert = '';
        try {


            $query_insert = "INSERT INTO cont_contraprestacion
            (
            contrato_id,
            moneda_id, 
            monto,
         
            status,
            user_created_id,
            created_at)
            VALUES
            (
            " . $data['contrato_id'] . ",
            1, 
            " . $data['monto'] . ",
            
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


    public function asignar_contratos_anexados($data)
    {
        $query = '';
        try {

            $query = "UPDATE cont_contrato
                        SET
                            contratos_adjuntos = '" . $data["contratos_adjuntos"] . "'
                        WHERE contrato_id = " . $data["contrato_id"];

            $db_query = $this->db->prepare($query);
            $db_query->execute();

            $result['status'] = 200;
            $result['result'] = $data["contrato_id"];
            $result['message'] = 'Se ha modificado exitosamente el contrato';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Contrato)';
            return $result;
        }
    }

    public function actualizar_codigo_contrato()
    {
        $query_luz = "";
        $query_agua = "";
        try {

            $db_query = $this->db->prepare("SELECT i.contrato_id, i.id AS inmueble_id, 
                CONCAT('2') AS id_agua, i.num_suministro_agua ,i.tipo_compromiso_pago_agua, i.monto_o_porcentaje_agua,
                CONCAT('1') AS id_luz, i.num_suministro_luz ,i.tipo_compromiso_pago_luz, i.monto_o_porcentaje_luz,
                c.user_created_id, i.created_at
                FROM cont_inmueble AS i 
                INNER JOIN cont_contrato AS c ON c.contrato_id = i.contrato_id
                WHERE c.status = 1");
            $db_query->execute();
            $inmuebles = $db_query->fetchAll(PDO::FETCH_ASSOC);

            foreach ($inmuebles as $inm) {
                $monto_o_porcentaje_agua = $inm['monto_o_porcentaje_agua'] == "" ? 0 : $inm['monto_o_porcentaje_agua'];
                $monto_o_porcentaje_luz = $inm['monto_o_porcentaje_luz'] == "" ? 0 : $inm['monto_o_porcentaje_luz'];
                $query_luz = "INSERT INTO cont_inmueble_suministros(
                    contrato_id,
                    inmueble_id,
                    tipo_servicio_id,
                    nro_suministro,
                    tipo_compromiso_pago_id,
                    monto_o_porcentaje,
                    status,
                    user_created_id,
                    created_at
                )VALUES(
                    " . $inm['contrato_id'] . ",
                    '" . $inm['inmueble_id'] . "',
                    1,
                    '" . $inm["num_suministro_luz"] . "',
                    '" . $inm["tipo_compromiso_pago_luz"] . "',
                    " . $monto_o_porcentaje_luz . ",
                    1,
                    " . $inm["user_created_id"] . ",
                    '" . $inm["created_at"] . "'
                )";

                $result_query_luz = $this->db->prepare($query_luz);
                $result_query_luz->execute();

                $query_agua = "INSERT INTO cont_inmueble_suministros(
                    contrato_id,
                    inmueble_id,
                    tipo_servicio_id,
                    nro_suministro,
                    tipo_compromiso_pago_id,
                    monto_o_porcentaje,
                    status,
                    user_created_id,
                    created_at
                )VALUES(
                    " . $inm['contrato_id'] . ",
                    '" . $inm['inmueble_id'] . "',
                    2,
                    '" . $inm["num_suministro_agua"] . "',
                    '" . $inm["tipo_compromiso_pago_agua"] . "',
                    " . $monto_o_porcentaje_agua . ",
                    1,
                    " . $inm["user_created_id"] . ",
                    '" . $inm["created_at"] . "'
                )";

                $result_query_agua = $this->db->prepare($query_agua);
                $result_query_agua->execute();
            }




            /*
            $db_query = $this->db->prepare('SELECT a.id, ce.contrato_detalle_id
            FROM cont_auditoria AS a 
            INNER JOIN cont_condicion_economica AS ce ON ce.contrato_id = a.contrato_id
            INNER JOIN cont_contrato AS c ON c.contrato_id = ce.contrato_id
            WHERE a.status = 1 AND c.tipo_contrato_id = 1   AND ce.contrato_detalle_id IS NOT NULL');
            $db_query->execute();
            $archivos = $db_query->fetchAll(PDO::FETCH_ASSOC);

            foreach ($archivos as $cont) {
                $query_con_eco = $this->db->prepare("UPDATE cont_auditoria SET contrato_detalle_id ='".$cont['contrato_detalle_id']."' WHERE  id = ".$cont['id']);
                $query_con_eco->execute();
                echo $cont['id']." <br>";
            }

            $db_query = $this->db->prepare('SELECT a.archivo_id, ce.contrato_detalle_id
            FROM cont_archivos AS a 
            INNER JOIN cont_condicion_economica AS ce ON ce.contrato_id = a.contrato_id
            INNER JOIN cont_contrato AS c ON c.contrato_id = ce.contrato_id
            WHERE a.status = 1  AND ce.contrato_detalle_id IS NOT NULL AND c.tipo_contrato_id = 1');
            $db_query->execute();
            $archivos = $db_query->fetchAll(PDO::FETCH_ASSOC);

            foreach ($archivos as $cont) {
                $query_con_eco = $this->db->prepare("UPDATE cont_archivos SET contrato_detalle_id ='".$cont['contrato_detalle_id']."' WHERE  archivo_id = ".$cont['archivo_id']);
                $query_con_eco->execute();
                echo $cont['archivo_id']." <br>";
            }
            */

            /*
            $db_query = $this->db->prepare('SELECT c.contrato_id, c.codigo_correlativo, c.user_created_id, c.tipo_inflacion_id, c.tipo_cuota_extraordinaria_id, c.observaciones FROM cont_contrato AS c WHERE c.status = 1 AND c.tipo_contrato_id = 1');
            $db_query->execute();
            $contratos = $db_query->fetchAll(PDO::FETCH_ASSOC);
            $data_contrato = array();
            $query_con_eco = '';
            foreach ($contratos as $cont) {

                $data_contrato_detalle['contrato_id'] = $cont['contrato_id'];
                $data_contrato_detalle['codigo'] = NULL;
                $data_contrato_detalle['observaciones'] = $cont['observaciones'];
                $data_contrato_detalle['status'] = 1;
                $data_contrato_detalle['user_created_id'] = $cont['user_created_id'];
                $data_contrato_detalle['created_at'] = "2023-07-16 ".date('H:i:s');
                $model_contrato_detalle = new ContratoArrendamientoDetalle();
                $insert_contrato_detalle = $model_contrato_detalle->registrar($data_contrato_detalle);
                if ($insert_contrato_detalle['status'] == 404) {
                    return $insert_contrato_detalle;
                }
                $contrato_detalle_id = $insert_contrato_detalle['result'];

                $tipo_inflacion_id = Empty($tipo_inflacion_id) ? 2 : $tipo_inflacion_id;
                $tipo_cuota_extraordinaria_id = Empty($tipo_cuota_extraordinaria_id) ? 2 : $tipo_cuota_extraordinaria_id;

                //inmueble
                $db_query_in = $this->db->prepare("UPDATE cont_inmueble SET contrato_detalle_id = '".$contrato_detalle_id."' WHERE status = 1 AND contrato_id = ".$cont['contrato_id']);
                $db_query_in->execute();

                //incrementos
                $query_con_eco = "UPDATE cont_condicion_economica SET contrato_detalle_id = '".$contrato_detalle_id."', tipo_inflacion_id = ".$tipo_inflacion_id.", tipo_cuota_extraordinaria_id = ".$tipo_cuota_extraordinaria_id."  WHERE status = 1 AND contrato_id = ".$cont['contrato_id'];
                $db_query_con_eco = $this->db->prepare($query_con_eco);
                $db_query_con_eco->execute();

                //incrementos
                $db_query_con_eco = $this->db->prepare("UPDATE cont_incrementos SET contrato_detalle_id = '".$contrato_detalle_id."' WHERE estado = 1 AND contrato_id = ".$cont['contrato_id']);
                $db_query_con_eco->execute();

                //inflaciones
                $db_query_con_eco = $this->db->prepare("UPDATE cont_inflaciones SET contrato_detalle_id = '".$contrato_detalle_id."' WHERE status = 1 AND contrato_id = ".$cont['contrato_id']);
                $db_query_con_eco->execute();

                //cuota extraordinaria
                $db_query_con_eco = $this->db->prepare("UPDATE cont_cuotas_extraordinarias SET contrato_detalle_id = '".$contrato_detalle_id."' WHERE status = 1 AND contrato_id = ".$cont['contrato_id']);
                $db_query_con_eco->execute();

                //beneficiario
                $db_query_con_eco = $this->db->prepare("UPDATE cont_beneficiarios SET contrato_detalle_id = '".$contrato_detalle_id."' WHERE status = 1 AND contrato_id = ".$cont['contrato_id']);
                $db_query_con_eco->execute();

                //responsable ir
                $db_query_con_eco = $this->db->prepare("UPDATE cont_responsable_ir SET contrato_detalle_id = '".$contrato_detalle_id."' WHERE status = 1 AND contrato_id = ".$cont['contrato_id']);
                $db_query_con_eco->execute();

                //adelantos
                $db_query_con_eco = $this->db->prepare("UPDATE cont_adelantos SET contrato_detalle_id = '".$contrato_detalle_id."' WHERE status = 1 AND contrato_id = ".$cont['contrato_id']);
                $db_query_con_eco->execute();


                $update_contrato_detalle = $model_contrato_detalle->update_codigo($cont['contrato_id']);
                if ($update_contrato_detalle['status'] == 404) {
                    return $update_contrato_detalle;
                }
                
            }
            */


            $result['status'] = 200;
            $result['result'] = [];
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['query'] = ['luz' => $query_luz, 'agua' => $query_agua];
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function obtener_datos_generales($contrato_id)
    {
        try {
            $query = "SELECT
                        c.empresa_suscribe_id,
                        c.persona_responsable_id,
                        c.verificar_giro,
                        c.fecha_verificacion_giro,
                        c.usuario_verificacion_giro,
                        c.jefe_comercial_id,
                        c.nombre_tienda,
                        c.observaciones,
                        c.user_created_id,
                        c.created_at,
                        c.cc_id,
                        c.contratos_adjuntos
                    FROM cont_contrato c WHERE c.contrato_id IN (" . $contrato_id . ")";

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado[0];
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }




    ///correos
    public function obtener_solicitud_contrato_local($contrato_id)
    {
        try {
            $query = "SELECT c.nombre_tienda, 
                                ce.fecha_suscripcion,
                                ce.created_at,
                                i.ubicacion, 
                                ce.fecha_inicio, 
                                ce.fecha_fin,
                                c.user_created_id,
                                concat( IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS usuario_solicitante,
                                co.sigla AS sigla_correlativo,
                                c.codigo_correlativo,
                                tpa.correo correo_aprobador,
                                tpa2.correo correo_aprobado_por,
                                p2.correo correo_supervisor, 
                                tp.correo correo_responsable,
                                p.correo correo_jefe_comercial,
                                c.fecha_aprobacion,
                                c.estado_aprobacion
                        FROM cont_contrato c
                        INNER JOIN cont_inmueble i ON c.contrato_id = i.contrato_id
                        INNER JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id AND ce.status = 1
                        INNER JOIN tbl_usuarios tu ON tu.id = c.user_created_id
                        INNER JOIN tbl_personal_apt tp ON tp.id = tu.personal_id
                        LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
                        LEFT JOIN tbl_usuarios tu2 ON tu2.id = c.aprobador_id
                        LEFT JOIN tbl_personal_apt tpa ON tpa.id = tu2.personal_id
                        LEFT JOIN tbl_usuarios tu3 ON tu3.id = c.aprobado_por
                        LEFT JOIN tbl_personal_apt tpa2 ON tpa2.id = tu3.personal_id
                        LEFT JOIN tbl_usuarios u2 ON c.persona_responsable_id = u2.id
                        LEFT JOIN tbl_personal_apt p2 ON u2.personal_id = p2.id
                        LEFT JOIN tbl_usuarios u ON c.jefe_comercial_id = u.id
                        LEFT JOIN tbl_personal_apt p ON u.personal_id = p.id
                        WHERE c.contrato_id = '" . $contrato_id . "' LIMIT 1";

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function obtener_solicitud_contrato_local_detallado($contrato_id)
    {
        try {


            $contratos = array();


            $query = "SELECT 
                c.empresa_suscribe_id,
                r.nombre AS empresa_suscribe,
                c.persona_responsable_id,
                CONCAT(IFNULL(p2.nombre, ''),' ',IFNULL(p2.apellido_paterno, ''),' ',IFNULL(p2.apellido_materno, '')) AS persona_responsable,
                c.observaciones,
                c.user_created_id,
                CONCAT(IFNULL(p.nombre, ''),' ',IFNULL(p.apellido_paterno, ''),' ',IFNULL(p.apellido_materno, '')) AS user_created,
                CONCAT(IFNULL(p3.nombre, ''),' ',IFNULL(p3.apellido_paterno, ''),' ',IFNULL(p3.apellido_materno, '')) AS abogado,
                c.created_at,
                co.sigla AS sigla_correlativo,
                c.codigo_correlativo,
                
                p.correo correo_responsable,
                p2.correo correo_supervisor, 
                tpa2.correo correo_aprobado_por,
                pjc.correo correo_jefe_comercial,
                
                c.fecha_aprobacion,
                c.estado_aprobacion

            FROM
                cont_contrato c
                INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id
                INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
                INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
                LEFT JOIN tbl_usuarios u2 ON c.persona_responsable_id = u2.id
                LEFT JOIN tbl_personal_apt p2 ON u2.personal_id = p2.id

                LEFT JOIN tbl_usuarios u3 ON c.abogado_id = u3.id
                LEFT JOIN tbl_personal_apt p3 ON u3.personal_id = p3.id
                LEFT JOIN tbl_usuarios tu3 ON tu3.id = c.aprobado_por
                LEFT JOIN tbl_personal_apt tpa2 ON tpa2.id = tu3.personal_id

                LEFT JOIN tbl_usuarios ujc ON c.jefe_comercial_id = ujc.id
                LEFT JOIN tbl_personal_apt pjc ON ujc.personal_id = pjc.id

                LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
            WHERE 
                c.contrato_id IN (" . $contrato_id . ")";
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $datos_generales = $db_query->fetchAll(PDO::FETCH_ASSOC);

            ///propietarios
            $query = "SELECT p.id AS persona_id,
            pr.propietario_id,
            tp.nombre AS tipo_persona,
            td.nombre AS tipo_docu_identidad,
            p.num_docu,
            p.nombre,
            p.direccion,
            p.representante_legal,
            p.num_partida_registral,
            p.contacto_nombre,
            p.contacto_telefono,
            p.contacto_email
            FROM cont_propietario pr
            INNER JOIN cont_persona p ON pr.persona_id = p.id
            INNER JOIN cont_tipo_persona tp ON p.tipo_persona_id = tp.id
            INNER JOIN cont_tipo_docu_identidad td ON p.tipo_docu_identidad_id = td.id
            WHERE pr.contrato_id IN (" . $contrato_id . ")";
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $data_propietarios = $db_query->fetchAll(PDO::FETCH_ASSOC);



            $query = "SELECT d.id, d.codigo, d.observaciones FROM cont_contrato_detalle d WHERE d.status = 1 AND d.contrato_id = " . $contrato_id . " ORDER BY d.id ASC";
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $contrato_detalle = $db_query->fetchAll(PDO::FETCH_ASSOC);

            foreach ($contrato_detalle as $cont_detalle) {
                ///inmuebles
                $query = "SELECT
                    i.id,
                    ude.nombre AS departamento, 
                    upr.nombre AS provincia,
                    udi.nombre AS distrito,
                    i.ubicacion,
                    i.area_arrendada,
                    i.num_partida_registral,
                    i.oficina_registral,
                    i.num_suministro_agua,
                    i.tipo_compromiso_pago_agua,
                    t1.nombre AS tipo_pago_agua,
                    i.monto_o_porcentaje_agua,
                    i.num_suministro_luz,
                    i.tipo_compromiso_pago_luz,
                    t2.nombre AS tipo_pago_luz,
                    i.monto_o_porcentaje_luz,
                    i.tipo_compromiso_pago_arbitrios,
                    ta.nombre AS tipo_pago_arbitrios,
                    i.porcentaje_pago_arbitrios
                FROM cont_inmueble i
                INNER JOIN tbl_ubigeo ude ON SUBSTRING(i.ubigeo_id, 1, 2) = ude.cod_depa AND ude.cod_prov = '00' AND ude.cod_dist = '00'
                INNER JOIN tbl_ubigeo upr ON SUBSTRING(i.ubigeo_id, 1, 2) = upr.cod_depa AND SUBSTRING(i.ubigeo_id, 3, 2) = upr.cod_prov AND upr.cod_dist = '00'
                INNER JOIN tbl_ubigeo udi ON SUBSTRING(i.ubigeo_id, 1, 2) = udi.cod_depa AND SUBSTRING(i.ubigeo_id, 3, 2) = udi.cod_prov AND SUBSTRING(i.ubigeo_id, 5, 2) = udi.cod_dist
                LEFT JOIN cont_tipo_pago_servicio t1 ON i.tipo_compromiso_pago_agua = t1.id
                LEFT JOIN cont_tipo_pago_servicio t2 ON i.tipo_compromiso_pago_luz = t2.id
                INNER JOIN cont_tipo_pago_arbitrios ta ON i.tipo_compromiso_pago_arbitrios = ta.id
                WHERE i.status = 1 AND i.contrato_detalle_id = " . $cont_detalle['id'] . " AND i.contrato_id = " . $contrato_id;
                $db_query = $this->db->prepare($query);
                $db_query->execute();
                $data_inmuebles = $db_query->fetchAll(PDO::FETCH_ASSOC);



                $query = "SELECT s.id, s.tipo_servicio_id, sp.nombre AS tipo_servicio, s.nro_suministro, s.tipo_compromiso_pago_id, ps.nombre AS tipo_compromiso, s.monto_o_porcentaje, s.nro_documento_beneficiario, s.nombre_beneficiario, s.nro_cuenta_soles, td.nombre as tipo_documento_beneficiario
                FROM cont_inmueble_suministros AS s
                LEFT JOIN cont_tipo_pago_servicio AS ps ON ps.id = s.tipo_compromiso_pago_id
                LEFT JOIN cont_tipo_servicio_publico AS sp ON sp.id = s.tipo_servicio_id
                INNER JOIN cont_inmueble AS i ON i.id = s.inmueble_id
                INNER JOIN cont_contrato_detalle AS cd ON cd.id = i.contrato_detalle_id
                LEFT JOIN tbl_tipo_documento AS td ON td.id = s.tipo_documento_beneficiario
                WHERE i.status = 1 AND s.status = 1 AND i.contrato_detalle_id = " . $cont_detalle['id'] . " AND i.contrato_id = " . $contrato_id . "
                ORDER BY s.tipo_servicio_id, s.id ASC";

                $db_query = $this->db->prepare($query);
                $db_query->execute();
                $data_inmuebles_suministros = $db_query->fetchAll(PDO::FETCH_ASSOC);

                //codiciones economicas
                $query = " SELECT 
                    c.condicion_economica_id,
                    c.contrato_id,
                    c.monto_renta,
                    c.tipo_moneda_id,
                    concat(m.nombre,' (', m.simbolo,')') AS moneda_contrato,
                    m.simbolo AS simbolo_moneda,
                    c.impuesto_a_la_renta_id,
                    i.nombre AS impuesto_a_la_renta,
                    c.numero_cuenta_detraccion,
                    c.garantia_monto,
                    c.tipo_adelanto_id,
                    a.nombre AS tipo_adelanto,
                    c.plazo_id,
                    tp.nombre AS nombre_plazo,
                    c.cant_meses_contrato,
                    c.fecha_inicio,
                    c.fecha_fin,
                    c.periodo_gracia_id,
                    p.nombre AS periodo_gracia,
                    c.periodo_gracia_numero,
                    c.periodo_gracia_inicio,
                    c.periodo_gracia_fin,
                    c.num_alerta_vencimiento,
                    c.cargo_mantenimiento,
                    c.fecha_suscripcion,
                    c.status,
                    c.user_created_id,
                    c.created_at,
                    c.user_updated_id,
                    c.updated_at
                FROM cont_condicion_economica c
                INNER JOIN tbl_moneda m ON c.tipo_moneda_id = m.id 
                INNER JOIN cont_tipo_impuesto_a_la_renta i ON c.impuesto_a_la_renta_id = i.id
                INNER JOIN cont_tipo_adelanto a ON c.tipo_adelanto_id = a.id
                LEFT JOIN cont_tipo_periodo_de_gracia p ON c.periodo_gracia_id = p.id
                LEFT JOIN cont_tipo_plazo tp ON c.plazo_id = tp.id
                WHERE c.status = 1 AND c.contrato_detalle_id = " . $cont_detalle['id'] . " AND c.contrato_id = " . $contrato_id;
                $db_query = $this->db->prepare($query);
                $db_query->execute();
                $data_condiciones_economicas = $db_query->fetchAll(PDO::FETCH_ASSOC);


                //codiciones economicas
                $query = "SELECT id, num_periodo
                    FROM cont_adelantos 
                    WHERE contrato_detalle_id = " . $cont_detalle['id'] . " AND status = 1 AND contrato_id =" . $contrato_id . "
                    ORDER BY id ASC";
                $db_query = $this->db->prepare($query);
                $db_query->execute();
                $data_adelantos = $db_query->fetchAll(PDO::FETCH_ASSOC);


                //incrementos
                $query = "SELECT i.id, i.valor, tp.nombre AS tipo_valor, i.tipo_continuidad_id, tc.nombre AS tipo_continuidad, i.a_partir_del_año
                FROM cont_incrementos i
                INNER JOIN cont_tipo_pago_incrementos tp ON i.tipo_valor_id = tp.id
                INNER JOIN cont_tipo_continuidad_pago tc ON i.tipo_continuidad_id = tc.id
                WHERE i.contrato_detalle_id = " . $cont_detalle['id'] . " AND i.estado = 1 AND i.contrato_id IN (" . $contrato_id . ")";
                $db_query = $this->db->prepare($query);
                $db_query->execute();
                $data_incrementos = $db_query->fetchAll(PDO::FETCH_ASSOC);


                //Inflaciones
                $query = "SELECT i.id, DATE_FORMAT(i.fecha, '%d-%m-%Y') as fecha, tp.nombre  AS tipo_periodicidad, i.numero, p.nombre as tipo_anio_mes, m.sigla AS moneda, i.porcentaje_anadido, i.tope_inflacion, i.minimo_inflacion
                FROM cont_inflaciones AS i
                INNER JOIN cont_tipo_periodicidad AS tp ON tp.id = i.tipo_periodicidad_id
                LEFT JOIN tbl_moneda AS m ON m.id = i.moneda_id
                LEFT JOIN cont_periodo as p ON p.id = i.tipo_anio_mes
                WHERE i.contrato_detalle_id = " . $cont_detalle['id'] . " AND i.contrato_id = $contrato_id AND i.status = 1
                ORDER BY i.id ASC";
                $db_query = $this->db->prepare($query);
                $db_query->execute();
                $data_inflaciones = $db_query->fetchAll(PDO::FETCH_ASSOC);


                //Cuota Extraordinaria
                $query = "SELECT c.id, m.nombre as mes, c.multiplicador, c.fecha
                FROM cont_cuotas_extraordinarias AS c
                INNER JOIN tbl_meses AS m ON m.id = c.mes
                    WHERE 
                        c.contrato_detalle_id = " . $cont_detalle['id'] . " AND c.contrato_id = $contrato_id
                        AND c.status = 1
                    ORDER BY c.id ASC";
                $db_query = $this->db->prepare($query);
                $db_query->execute();
                $data_cuota_extraordinarias = $db_query->fetchAll(PDO::FETCH_ASSOC);


                //Beneficiarios
                $query = "SELECT 
                    b.id,
                    tp.nombre AS tipo_persona,
                    td.nombre AS tipo_docu_identidad,
                    b.num_docu,
                    b.nombre,
                    b.forma_pago_id,
                    f.nombre AS forma_pago,
                    ba.nombre AS banco,
                    b.num_cuenta_bancaria,
                    b.num_cuenta_cci,
                    b.tipo_monto_id,
                    tm.nombre AS tipo_monto_a_depositar,
                    b.monto
                FROM
                    cont_beneficiarios b
                        LEFT JOIN
                    cont_tipo_persona tp ON b.tipo_persona_id = tp.id
                        LEFT JOIN
                    cont_tipo_docu_identidad td ON b.tipo_docu_identidad_id = td.id
                        INNER JOIN
                    cont_forma_pago f ON b.forma_pago_id = f.id
                        LEFT JOIN
                    tbl_bancos ba ON b.banco_id = ba.id
                        INNER JOIN
                    cont_tipo_monto_a_depositar tm ON b.tipo_monto_id = tm.id
                WHERE
                    b.contrato_detalle_id = " . $cont_detalle['id'] . " AND b.status = 1 AND b.contrato_id IN (" . $contrato_id . ")";
                $db_query = $this->db->prepare($query);
                $db_query->execute();
                $data_beneficiarios = $db_query->fetchAll(PDO::FETCH_ASSOC);

                //representante IR
                $query = "SELECT 
                    r.id,
                    r.contrato_id,
                    r.tipo_documento_id,
                    r.num_documento,
                    r.nombres,
                    r.estado_emisor,
                    r.porcentaje,
                    r.status,
                    r.user_created_id,
                    r.created_at,
                    td.nombre AS tipo_documento
                FROM cont_responsable_ir as r
                LEFT JOIN cont_tipo_docu_identidad AS td ON td.id = r.tipo_documento_id
                WHERE r.contrato_detalle_id = " . $cont_detalle['id'] . " AND r.status = 1 AND r.contrato_id IN (" . $contrato_id . ")";
                $db_query = $this->db->prepare($query);
                $db_query->execute();
                $data_representantes_ir = $db_query->fetchAll(PDO::FETCH_ASSOC);


                array_push($contratos, [
                    'codigo' => $cont_detalle['codigo'],
                    'data_inmuebles' => $data_inmuebles,
                    'data_inmuebles_suministros' => $data_inmuebles_suministros,
                    'data_condiciones_economicas' => $data_condiciones_economicas,
                    'data_adelantos' => $data_adelantos,
                    'data_incrementos' => $data_incrementos,
                    'data_inflaciones' => $data_inflaciones,
                    'data_cuota_extraordinarias' => $data_cuota_extraordinarias,
                    'data_beneficiarios' => $data_beneficiarios,
                    'data_representantes_ir' => $data_representantes_ir,
                    'observaciones' => $cont_detalle['observaciones'],
                ]);
            }

            $result['status'] = 200;
            $result['result'] = [
                'datos_generales' => $datos_generales,
                'data_propietarios' => $data_propietarios,
                'contratos' => $contratos,
            ];
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function obtener_solicitud_contrato_detallado($contrato_id, $contrato_detalle_id)
    {
        try {


            $contratos = array();


            $query = "SELECT 
                c.empresa_suscribe_id,
                r.nombre AS empresa_suscribe,
                c.persona_responsable_id,
                CONCAT(IFNULL(p2.nombre, ''),' ',IFNULL(p2.apellido_paterno, ''),' ',IFNULL(p2.apellido_materno, '')) AS persona_responsable,
                c.observaciones,
                c.user_created_id,
                CONCAT(IFNULL(p.nombre, ''),' ',IFNULL(p.apellido_paterno, ''),' ',IFNULL(p.apellido_materno, '')) AS user_created,
                c.created_at,
                co.sigla AS sigla_correlativo,
                c.codigo_correlativo
            FROM
                cont_contrato c
                INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id
                INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
                INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
                LEFT JOIN tbl_usuarios u2 ON c.persona_responsable_id = u2.id
                LEFT JOIN tbl_personal_apt p2 ON u2.personal_id = p2.id
                LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
            WHERE 
                c.contrato_id IN (" . $contrato_id . ")";
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $datos_generales = $db_query->fetchAll(PDO::FETCH_ASSOC);

            ///propietarios
            $query = "SELECT p.id AS persona_id,
            pr.propietario_id,
            tp.nombre AS tipo_persona,
            td.nombre AS tipo_docu_identidad,
            p.num_docu,
            p.nombre,
            p.direccion,
            p.representante_legal,
            p.num_partida_registral,
            p.contacto_nombre,
            p.contacto_telefono,
            p.contacto_email
            FROM cont_propietario pr
            INNER JOIN cont_persona p ON pr.persona_id = p.id
            INNER JOIN cont_tipo_persona tp ON p.tipo_persona_id = tp.id
            INNER JOIN cont_tipo_docu_identidad td ON p.tipo_docu_identidad_id = td.id
            WHERE pr.contrato_id IN (" . $contrato_id . ")";
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $data_propietarios = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $query_detalle_ids = "";
            for ($i = 0; $i < count($contrato_detalle_id); $i++) {
                $query_detalle_ids .= $contrato_detalle_id[$i];
                if (count($contrato_detalle_id) != ($i + 1)) {
                    $query_detalle_ids .= ", ";
                }
            }

            $query = "SELECT d.id, d.codigo, d.observaciones,  
            CONCAT(IFNULL(p.nombre, ''),' ',IFNULL(p.apellido_paterno, ''),' ',IFNULL(p.apellido_materno, '')) AS user_created,
            d.created_at
            FROM cont_contrato_detalle d 
            INNER JOIN tbl_usuarios u ON d.user_created_id = u.id
            INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
            WHERE d.status = 1 AND d.contrato_id = " . $contrato_id . " AND d.id IN (" . $query_detalle_ids . ") ORDER BY d.id ASC";
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $contrato_detalle = $db_query->fetchAll(PDO::FETCH_ASSOC);

            foreach ($contrato_detalle as $cont_detalle) {
                ///inmuebles
                $query = "SELECT
                    i.id,
                    ude.nombre AS departamento, 
                    upr.nombre AS provincia,
                    udi.nombre AS distrito,
                    i.ubicacion,
                    i.area_arrendada,
                    i.num_partida_registral,
                    i.oficina_registral,
                    i.num_suministro_agua,
                    i.tipo_compromiso_pago_agua,
                    t1.nombre AS tipo_pago_agua,
                    i.monto_o_porcentaje_agua,
                    i.num_suministro_luz,
                    i.tipo_compromiso_pago_luz,
                    t2.nombre AS tipo_pago_luz,
                    i.monto_o_porcentaje_luz,
                    i.tipo_compromiso_pago_arbitrios,
                    ta.nombre AS tipo_pago_arbitrios,
                    i.porcentaje_pago_arbitrios
                FROM cont_inmueble i
                INNER JOIN tbl_ubigeo ude ON SUBSTRING(i.ubigeo_id, 1, 2) = ude.cod_depa AND ude.cod_prov = '00' AND ude.cod_dist = '00'
                INNER JOIN tbl_ubigeo upr ON SUBSTRING(i.ubigeo_id, 1, 2) = upr.cod_depa AND SUBSTRING(i.ubigeo_id, 3, 2) = upr.cod_prov AND upr.cod_dist = '00'
                INNER JOIN tbl_ubigeo udi ON SUBSTRING(i.ubigeo_id, 1, 2) = udi.cod_depa AND SUBSTRING(i.ubigeo_id, 3, 2) = udi.cod_prov AND SUBSTRING(i.ubigeo_id, 5, 2) = udi.cod_dist
                LEFT JOIN cont_tipo_pago_servicio t1 ON i.tipo_compromiso_pago_agua = t1.id
                LEFT JOIN cont_tipo_pago_servicio t2 ON i.tipo_compromiso_pago_luz = t2.id
                INNER JOIN cont_tipo_pago_arbitrios ta ON i.tipo_compromiso_pago_arbitrios = ta.id
                WHERE i.status = 1 AND i.contrato_detalle_id = " . $cont_detalle['id'] . " AND i.contrato_id = " . $contrato_id;
                $db_query = $this->db->prepare($query);
                $db_query->execute();
                $data_inmuebles = $db_query->fetchAll(PDO::FETCH_ASSOC);


                $query = "SELECT s.id, s.tipo_servicio_id, sp.nombre AS tipo_servicio, s.nro_suministro, s.tipo_compromiso_pago_id, ps.nombre AS tipo_compromiso, s.monto_o_porcentaje
                FROM cont_inmueble_suministros AS s
                LEFT JOIN cont_tipo_pago_servicio AS ps ON ps.id = s.tipo_compromiso_pago_id
                LEFT JOIN cont_tipo_servicio_publico AS sp ON sp.id = s.tipo_servicio_id
                INNER JOIN cont_inmueble AS i ON i.id = s.inmueble_id
                INNER JOIN cont_contrato_detalle AS cd ON cd.id = i.contrato_detalle_id
                WHERE i.status = 1 AND s.status = 1 AND i.contrato_detalle_id = " . $cont_detalle['id'] . " AND i.contrato_id = " . $contrato_id . "
                ORDER BY s.tipo_servicio_id, s.id ASC";

                $db_query = $this->db->prepare($query);
                $db_query->execute();
                $data_inmuebles_suministros = $db_query->fetchAll(PDO::FETCH_ASSOC);

                //codiciones economicas
                $query = " SELECT 
                    c.condicion_economica_id,
                    c.contrato_id,
                    c.monto_renta,
                    c.tipo_moneda_id,
                    concat(m.nombre,' (', m.simbolo,')') AS moneda_contrato,
                    m.simbolo AS simbolo_moneda,
                    c.impuesto_a_la_renta_id,
                    i.nombre AS impuesto_a_la_renta,
                    c.numero_cuenta_detraccion,
                    c.garantia_monto,
                    c.tipo_adelanto_id,
                    a.nombre AS tipo_adelanto,
                    c.plazo_id,
                    tp.nombre AS nombre_plazo,
                    c.cant_meses_contrato,
                    c.fecha_inicio,
                    c.fecha_fin,
                    c.periodo_gracia_id,
                    p.nombre AS periodo_gracia,
                    c.periodo_gracia_numero,
                    c.periodo_gracia_inicio,
                    c.periodo_gracia_fin,
                    c.num_alerta_vencimiento,
                    c.cargo_mantenimiento,
                    c.fecha_suscripcion,
                    c.status,
                    c.user_created_id,
                    c.created_at,
                    c.user_updated_id,
                    c.updated_at
                FROM cont_condicion_economica c
                INNER JOIN tbl_moneda m ON c.tipo_moneda_id = m.id 
                INNER JOIN cont_tipo_impuesto_a_la_renta i ON c.impuesto_a_la_renta_id = i.id
                INNER JOIN cont_tipo_adelanto a ON c.tipo_adelanto_id = a.id
                LEFT JOIN cont_tipo_periodo_de_gracia p ON c.periodo_gracia_id = p.id
                LEFT JOIN cont_tipo_plazo tp ON c.plazo_id = tp.id
                WHERE c.status = 1 AND c.contrato_detalle_id = " . $cont_detalle['id'] . " AND c.contrato_id = " . $contrato_id;
                $db_query = $this->db->prepare($query);
                $db_query->execute();
                $data_condiciones_economicas = $db_query->fetchAll(PDO::FETCH_ASSOC);


                //codiciones economicas
                $query = "SELECT id, num_periodo
                    FROM cont_adelantos 
                    WHERE contrato_detalle_id = " . $cont_detalle['id'] . " AND status = 1 AND contrato_id =" . $contrato_id . "
                    ORDER BY id ASC";
                $db_query = $this->db->prepare($query);
                $db_query->execute();
                $data_adelantos = $db_query->fetchAll(PDO::FETCH_ASSOC);


                //incrementos
                $query = "SELECT i.id, i.valor, tp.nombre AS tipo_valor, i.tipo_continuidad_id, tc.nombre AS tipo_continuidad, i.a_partir_del_año
                FROM cont_incrementos i
                INNER JOIN cont_tipo_pago_incrementos tp ON i.tipo_valor_id = tp.id
                INNER JOIN cont_tipo_continuidad_pago tc ON i.tipo_continuidad_id = tc.id
                WHERE i.contrato_detalle_id = " . $cont_detalle['id'] . " AND i.estado = 1 AND i.contrato_id IN (" . $contrato_id . ")";
                $db_query = $this->db->prepare($query);
                $db_query->execute();
                $data_incrementos = $db_query->fetchAll(PDO::FETCH_ASSOC);


                //Inflaciones
                $query = "SELECT i.id, DATE_FORMAT(i.fecha, '%d-%m-%Y') as fecha, tp.nombre  AS tipo_periodicidad, i.numero, p.nombre as tipo_anio_mes, m.sigla AS moneda, i.porcentaje_anadido, i.tope_inflacion, i.minimo_inflacion
                FROM cont_inflaciones AS i
                INNER JOIN cont_tipo_periodicidad AS tp ON tp.id = i.tipo_periodicidad_id
                LEFT JOIN tbl_moneda AS m ON m.id = i.moneda_id
                LEFT JOIN cont_periodo as p ON p.id = i.tipo_anio_mes
                WHERE i.contrato_detalle_id = " . $cont_detalle['id'] . " AND i.contrato_id = $contrato_id AND i.status = 1
                ORDER BY i.id ASC";
                $db_query = $this->db->prepare($query);
                $db_query->execute();
                $data_inflaciones = $db_query->fetchAll(PDO::FETCH_ASSOC);


                //Cuota Extraordinaria
                $query = "SELECT c.id, m.nombre as mes, c.multiplicador, c.fecha
                FROM cont_cuotas_extraordinarias AS c
                INNER JOIN tbl_meses AS m ON m.id = c.mes
                    WHERE 
                        c.contrato_detalle_id = " . $cont_detalle['id'] . " AND c.contrato_id = $contrato_id
                        AND c.status = 1
                    ORDER BY c.id ASC";
                $db_query = $this->db->prepare($query);
                $db_query->execute();
                $data_cuota_extraordinarias = $db_query->fetchAll(PDO::FETCH_ASSOC);


                //Beneficiarios
                $query = "SELECT 
                    b.id,
                    tp.nombre AS tipo_persona,
                    td.nombre AS tipo_docu_identidad,
                    b.num_docu,
                    b.nombre,
                    b.forma_pago_id,
                    f.nombre AS forma_pago,
                    ba.nombre AS banco,
                    b.num_cuenta_bancaria,
                    b.num_cuenta_cci,
                    b.tipo_monto_id,
                    tm.nombre AS tipo_monto_a_depositar,
                    b.monto
                FROM
                    cont_beneficiarios b
                        LEFT JOIN
                    cont_tipo_persona tp ON b.tipo_persona_id = tp.id
                        LEFT JOIN
                    cont_tipo_docu_identidad td ON b.tipo_docu_identidad_id = td.id
                        INNER JOIN
                    cont_forma_pago f ON b.forma_pago_id = f.id
                        LEFT JOIN
                    tbl_bancos ba ON b.banco_id = ba.id
                        INNER JOIN
                    cont_tipo_monto_a_depositar tm ON b.tipo_monto_id = tm.id
                WHERE
                    b.contrato_detalle_id = " . $cont_detalle['id'] . " AND b.status = 1 AND b.contrato_id IN (" . $contrato_id . ")";
                $db_query = $this->db->prepare($query);
                $db_query->execute();
                $data_beneficiarios = $db_query->fetchAll(PDO::FETCH_ASSOC);

                //representante IR
                $query = "SELECT 
                    r.id,
                    r.contrato_id,
                    r.tipo_documento_id,
                    r.num_documento,
                    r.nombres,
                    r.estado_emisor,
                    r.porcentaje,
                    r.status,
                    r.user_created_id,
                    r.created_at,
                    td.nombre AS tipo_documento
                FROM cont_responsable_ir as r
                LEFT JOIN cont_tipo_docu_identidad AS td ON td.id = r.tipo_documento_id
                WHERE r.contrato_detalle_id = " . $cont_detalle['id'] . " AND r.status = 1 AND r.contrato_id IN (" . $contrato_id . ")";
                $db_query = $this->db->prepare($query);
                $db_query->execute();
                $data_representantes_ir = $db_query->fetchAll(PDO::FETCH_ASSOC);


                array_push($contratos, [
                    'codigo' => $cont_detalle['codigo'],
                    'data_inmuebles' => $data_inmuebles,
                    'data_inmuebles_suministros' => $data_inmuebles_suministros,
                    'data_condiciones_economicas' => $data_condiciones_economicas,
                    'data_adelantos' => $data_adelantos,
                    'data_incrementos' => $data_incrementos,
                    'data_inflaciones' => $data_inflaciones,
                    'data_cuota_extraordinarias' => $data_cuota_extraordinarias,
                    'data_beneficiarios' => $data_beneficiarios,
                    'data_representantes_ir' => $data_representantes_ir,
                    'observaciones' => $cont_detalle['observaciones'],
                    'user_created' => $cont_detalle['user_created'],
                    'created_at' => $cont_detalle['created_at'],
                ]);
            }

            $result['status'] = 200;
            $result['result'] = [
                'datos_generales' => $datos_generales,
                'data_propietarios' => $data_propietarios,
                'contratos' => $contratos,
            ];
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }



    public function registrar_email_enviados($data)
    {
        $query = '';
        try {

            $query = "INSERT INTO cont_emails_enviados(
                contrato_id,
                tipo_email_enviado_id,
                status,
                user_created_id,
                created_at)
                VALUES(
                " . $data['contrato_id'] . ",
                1,
                1,
                " . $data['usuario_id'] . ",
                '" . $data['created_at'] . "')";

            $db_query = $this->db->prepare($query);
            $db_query->execute();

            $result['status'] = 200;
            $result['result'] = $data["contrato_id"];
            $result['message'] = 'Se ha registrado exitosamente el email enviado';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Contrato)';
            return $result;
        }
    }

































    public function ObtenerTodosContratos()
    {
        try {
            $db_query = $this->db->prepare('SELECT contrato_id FROM cont_contrato');
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function obtener_datos_propietario($contrato_id)
    {
        try {
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query  = "SELECT p.id AS persona_id,
                            pr.propietario_id,
                            tp.nombre AS tipo_persona,
                            p.tipo_docu_identidad_id,
                            td.nombre AS tipo_docu_identidad,
                            p.num_docu,
                            p.num_ruc,
                            p.nombre,
                            p.direccion,
                            p.representante_legal,
                            p.num_partida_registral,
                            p.contacto_nombre,
                            p.contacto_telefono,
                            p.contacto_email
                        FROM cont_propietario pr
                        INNER JOIN cont_persona p ON pr.persona_id = p.id
                        INNER JOIN cont_tipo_persona tp ON p.tipo_persona_id = tp.id
                        INNER JOIN cont_tipo_docu_identidad td ON p.tipo_docu_identidad_id = td.id
                        WHERE pr.status = 1 AND pr.contrato_id IN (" . $contrato_id . ");";

            $db_query = $this->db->prepare($query);
            $db_query->execute();

            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $filas = array();
            foreach ($resultado as $val) {
                $filas[] = $val; // Agregar la fila al array
            }

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function obtener_datos_inmueble($contrato_id)
    {
        try {
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query  = "SELECT
                            i.id,
                            i.ubigeo_id,
                            ude.nombre AS departamento, 
                            upr.nombre AS provincia,
                            udi.nombre AS distrito,
                            i.ubicacion,
                            i.area_arrendada,
                            i.num_partida_registral,
                            i.oficina_registral,
                            i.num_suministro_agua,
                            i.tipo_compromiso_pago_agua,
                            t1.nombre AS tipo_pago_agua,
                            i.monto_o_porcentaje_agua,
                            i.num_suministro_luz,
                            i.tipo_compromiso_pago_luz,
                            t2.nombre AS tipo_pago_luz,
                            i.monto_o_porcentaje_luz,
                            i.tipo_compromiso_pago_arbitrios,
                            ta.nombre AS tipo_pago_arbitrios,
                            i.porcentaje_pago_arbitrios,
                            i.latitud,
                            i.longitud,
                            i.id_empresa_servicio_agua,
                            i.id_empresa_servicio_luz,
                            lspe1.razon_social as empresa_servicio_agua,
                            lspe2.razon_social as empresa_servicio_luz
                        FROM cont_inmueble i
                        INNER JOIN tbl_ubigeo ude ON SUBSTRING(i.ubigeo_id, 1, 2) = ude.cod_depa AND ude.cod_prov = '00' AND ude.cod_dist = '00'
                        INNER JOIN tbl_ubigeo upr ON SUBSTRING(i.ubigeo_id, 1, 2) = upr.cod_depa AND SUBSTRING(i.ubigeo_id, 3, 2) = upr.cod_prov AND upr.cod_dist = '00'
                        INNER JOIN tbl_ubigeo udi ON SUBSTRING(i.ubigeo_id, 1, 2) = udi.cod_depa AND SUBSTRING(i.ubigeo_id, 3, 2) = udi.cod_prov AND SUBSTRING(i.ubigeo_id, 5, 2) = udi.cod_dist
                        INNER JOIN cont_tipo_pago_servicio t1 ON i.tipo_compromiso_pago_agua = t1.id
                        INNER JOIN cont_tipo_pago_servicio t2 ON i.tipo_compromiso_pago_luz = t2.id
                        INNER JOIN cont_tipo_pago_arbitrios ta ON i.tipo_compromiso_pago_arbitrios = ta.id
                        
                        LEFT JOIN cont_local_servicio_publico_empresas lspe1 ON i.id_empresa_servicio_agua = lspe1.id
                        LEFT JOIN cont_local_servicio_publico_empresas lspe2 ON i.id_empresa_servicio_luz = lspe2.id

                        WHERE i.contrato_id = " . $contrato_id . ";";

            $db_query = $this->db->prepare($query);
            $db_query->execute();

            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $filas = array();
            foreach ($resultado as $val) {
                $filas[] = $val; // Agregar la fila al array
            }

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function obtener_datos_condiciones_economicas($contrato_id)
    {
        try {
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query  = "SELECT 
                                c.condicion_economica_id,
                                c.contrato_id,
                                c.monto_renta,
                                c.tipo_moneda_id,
                                m.nombre AS moneda_contrato,
                                concat(m.nombre,' (', m.simbolo,')') AS moneda_contrato_con_simbolo,
                                m.simbolo AS simbolo_moneda,
                                c.pago_renta_id,
                                c.cuota_variable,
                                tpr.nombre AS pago_renta,
                                tv.nombre AS tipo_venta,
                                tai.nombre AS igv_en_la_renta,
                                c.impuesto_a_la_renta_id,
                                i.nombre AS impuesto_a_la_renta,
                                c.carta_de_instruccion_id,
                                ci.nombre AS carta_de_instruccion,
                                c.numero_cuenta_detraccion,
                                c.garantia_monto,
                                c.tipo_adelanto_id,
                                a.nombre AS tipo_adelanto,
                                c.plazo_id,
                                c.cant_meses_contrato,
                                c.fecha_inicio,
                                c.fecha_fin,
                                c.periodo_gracia_id,
                                p.nombre AS periodo_gracia,
                                c.periodo_gracia_numero,
                                c.periodo_gracia_inicio,
                                c.periodo_gracia_fin,
                                dp.nombre AS dia_de_pago,
                                c.num_alerta_vencimiento,
                                c.cargo_mantenimiento,
                                c.renovacion_automatica,
                                c.fecha_suscripcion,
                                c.status,
                                c.user_created_id,
                                c.created_at,
                                c.user_updated_id,
                                c.updated_at
                            FROM cont_condicion_economica c
                            INNER JOIN tbl_moneda m ON c.tipo_moneda_id = m.id 
                            INNER JOIN cont_tipo_impuesto_a_la_renta i ON c.impuesto_a_la_renta_id = i.id
                            INNER JOIN cont_tipo_adelanto a ON c.tipo_adelanto_id = a.id
                            LEFT JOIN cont_tipo_periodo_de_gracia p ON c.periodo_gracia_id = p.id
                            LEFT JOIN cont_tipo_carta_de_instruccion ci ON c.carta_de_instruccion_id = ci.id
                            LEFT JOIN cont_tipo_dia_de_pago dp ON c.dia_de_pago_id = dp.id
                            LEFT JOIN cont_tipo_pago_renta tpr ON c.pago_renta_id = tpr.id
                            LEFT JOIN cont_tipo_venta tv ON c.tipo_venta_id = tv.id
                            LEFT JOIN cont_tipo_afectacion_igv tai ON c.afectacion_igv_id = tai.id
                            WHERE c.contrato_id = " . $contrato_id;

            $db_query = $this->db->prepare($query);
            $db_query->execute();

            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $filas = array();
            foreach ($resultado as $val) {
                $filas[] = $val; // Agregar la fila al array
            }

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function obtener_datos_adelantos($contrato_id)
    {
        try {
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query  = "SELECT 
                                    a.id, 
                                    t.nombre AS mes_adelanto
                                FROM
                                    cont_adelantos a
                                    INNER JOIN cont_tipo_mes_adelanto t ON a.num_periodo = t.id
                                WHERE
                                    a.contrato_id = $contrato_id
                                    AND a.status = 1
                                ORDER BY t.id ASC
                                ";

            $db_query = $this->db->prepare($query);
            $db_query->execute();

            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);


            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function obtener_datos_incrementos($contrato_id)
    {
        try {
            // $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query  = "SELECT 
                                i.id, 
                                i.valor, 
                                i.tipo_valor_id,
                                tp.nombre AS tipo_valor, 
                                i.tipo_continuidad_id, 
                                tc.nombre AS tipo_continuidad, 
                                i.a_partir_del_año
                            FROM 
                                cont_incrementos i
                                INNER JOIN cont_tipo_pago_incrementos tp ON i.tipo_valor_id = tp.id
                                INNER JOIN cont_tipo_continuidad_pago tc ON i.tipo_continuidad_id = tc.id
                            WHERE 
                                i.contrato_id = $contrato_id
                                AND i.estado = 1
                            ORDER BY i.id
                                   ";
            $this->db->exec("SET NAMES 'utf8mb4'");
            $db_query = $this->db->prepare($query);
            $db_query->execute();

            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);



            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';

            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }
    public function obtener_datos_beneficiarios($contrato_id)
    {
        try {
            // $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query  = "SELECT 
                                b.id,
                                tp.nombre AS tipo_persona,
                                td.nombre AS tipo_docu_identidad,
                                b.num_docu,
                                b.nombre,
                                b.forma_pago_id,
                                f.nombre AS forma_pago,
                                ba.nombre AS banco,
                                b.num_cuenta_bancaria,
                                b.num_cuenta_cci,
                                b.tipo_monto_id,
                                tm.nombre AS tipo_monto_a_depositar,
                                b.monto
                            FROM
                                cont_beneficiarios b
                                    LEFT JOIN
                                cont_tipo_persona tp ON b.tipo_persona_id = tp.id
                                    LEFT JOIN
                                cont_tipo_docu_identidad td ON b.tipo_docu_identidad_id = td.id
                                    INNER JOIN
                                cont_forma_pago f ON b.forma_pago_id = f.id
                                    LEFT JOIN
                                tbl_bancos ba ON b.banco_id = ba.id
                                    INNER JOIN
                                cont_tipo_monto_a_depositar tm ON b.tipo_monto_id = tm.id
                            WHERE
                                b.status = 1 AND contrato_id IN (" . $contrato_id . ")
                            ";
            $this->db->exec("SET NAMES 'utf8mb4'");
            $db_query = $this->db->prepare($query);
            $db_query->execute();

            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);



            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';

            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }
    public function obtener_datos_inflaciones($contrato_id)
    {
        try {
            // $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query  = "SELECT
                             i.id, DATE_FORMAT(i.fecha, '%d-%m-%Y') as fecha, tp.nombre  AS tipo_periodicidad, i.numero, p.nombre as tipo_anio_mes, m.sigla AS moneda, i.porcentaje_anadido, i.tope_inflacion, i.minimo_inflacion
                            FROM cont_inflaciones AS i
                            INNER JOIN cont_tipo_periodicidad AS tp ON tp.id = i.tipo_periodicidad_id
                            LEFT JOIN tbl_moneda AS m ON m.id = i.moneda_id
                            LEFT JOIN cont_periodo as p ON p.id = i.tipo_anio_mes
                                WHERE 
                                    i.contrato_id = $contrato_id
                                    AND i.status = 1
                                ORDER BY i.id ASC";
            $this->db->exec("SET NAMES 'utf8mb4'");
            $db_query = $this->db->prepare($query);
            $db_query->execute();

            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);



            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';

            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function obtener_datos_cuotas_extraordinarias($contrato_id)
    {
        try {
            // $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query  = "SELECT c.id, m.nombre as mes, c.multiplicador, c.fecha
                            FROM cont_cuotas_extraordinarias AS c
                            INNER JOIN tbl_meses AS m ON m.id = c.mes
                                WHERE 
                                    c.contrato_id = $contrato_id
                                    AND c.status = 1
                                ORDER BY c.id ASC
                            ";
            $this->db->exec("SET NAMES 'utf8mb4'");
            $db_query = $this->db->prepare($query);
            $db_query->execute();

            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);



            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';

            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    // CAMBIOS AUDITORIA 
    public function obtener_datos_cambios_auditoria($contrato_id)
    {
        try {
            // $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query  = "SELECT 
                            a.nombre_tabla,
                            a.valor_original,
                            a.nombre_campo,
                            a.nombre_menu_usuario,
                            a.nombre_campo_usuario,
                            a.tipo_valor,
                            a.valor_varchar,
                            a.valor_int,
                            a.valor_date,
                            a.valor_decimal,
                            a.valor_select_option,
                            a.valor_id_tabla,
                            a.created_at AS fecha_del_cambio,
                            CONCAT(IFNULL(p.nombre, ''), ' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS usuario_que_realizo_cambio,
                            ar.nombre AS area
                        FROM
                            cont_auditoria a
                                INNER JOIN
                            tbl_usuarios u ON a.user_created_id = u.id
                                INNER JOIN
                            tbl_personal_apt p ON u.personal_id = p.id
                                INNER JOIN
                            tbl_areas ar ON p.area_id = ar.id
                        WHERE
                            a.status = 1 AND a.contrato_id = " . $contrato_id . "
                        ORDER BY a.id DESC";

            $this->db->exec("SET NAMES 'utf8mb4'");
            $db_query = $this->db->prepare($query);
            $db_query->execute();

            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);
            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';

            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }
    // AUTORZACIONES MUNICIPALES 
    public function obtener_datos_direccion_municipal($contrato_id)
    {

        try {
            // $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query  = "SELECT 
                            i.id,i.contrato_id ,i.direccion_municipal  
                            FROM  cont_inmueble i
                            inner join cont_contrato cc 
                            on cc.contrato_id =i.contrato_id
                            where i.contrato_id =" . $contrato_id;

            $this->db->exec("SET NAMES 'utf8mb4'");
            $db_query = $this->db->prepare($query);
            $db_query->execute();

            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            // Aplicar htmlspecialchars al campo direccion_municipal
            foreach ($resultado as &$row) {
                $row['direccion_municipal'] = htmlspecialchars($row['direccion_municipal']);
            }

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';

            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }
    // LICNECIA DE FUNCIONAMIENTO
    public function obtener_datos_licencia_funcionamiento($contrato_id)
    {

        try {
            // $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query  = "SELECT 
                            lm.nombre_file,
                            lm.status_licencia,
                            lm.condicion,
                            lm.fecha_vencimiento,
                            lm.fecha_renovacion,
                            lm.extension,
                            lm.estado,
                            lm.created_at
                        FROM
                            cont_licencia_municipales lm
                            INNER JOIN cont_tipo_archivos ta ON lm.tipo_archivo_id = ta.tipo_archivo_id
                        WHERE
                            lm.contrato_id = '$contrato_id'
                            AND lm.tipo_archivo_id = 4
                        ORDER BY lm.id DESC  ";

            $this->db->exec("SET NAMES 'utf8mb4'");
            $db_query = $this->db->prepare($query);
            $db_query->execute();

            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);
            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';

            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }


    // CERTIFICADO DE INDECI 
    public function obtener_datos_certificado_indeci($contrato_id)
    {

        try {
            // $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query  = "SELECT 
                            lm.nombre_file,
                            lm.status_licencia,
                            lm.condicion,
                            lm.fecha_vencimiento,
                            lm.fecha_renovacion,
                            lm.extension,
                            lm.estado,
                            lm.created_at
                        FROM
                            cont_licencia_municipales lm
                            INNER JOIN cont_tipo_archivos ta ON lm.tipo_archivo_id = ta.tipo_archivo_id
                        WHERE
                            lm.contrato_id = '$contrato_id'
                            AND lm.tipo_archivo_id = 5
                        ORDER BY lm.id DESC ";

            $this->db->exec("SET NAMES 'utf8mb4'");
            $db_query = $this->db->prepare($query);
            $db_query->execute();

            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);
            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';

            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    // ANUNCIO PUBLICITARIO
    public function obtener_datos_anuncio_publicitario($contrato_id)
    {

        try {

            $query  = "SELECT 
                            lm.nombre_file,
                            lm.status_licencia,
                            lm.condicion,
                            lm.fecha_vencimiento,
                            lm.fecha_renovacion,
                            lm.extension,
                            lm.estado,
                            lm.created_at
                        FROM
                            cont_licencia_municipales lm
                            INNER JOIN cont_tipo_archivos ta ON lm.tipo_archivo_id = ta.tipo_archivo_id
                        WHERE
                            lm.contrato_id = '$contrato_id'
                            AND lm.tipo_archivo_id = 6
                        ORDER BY lm.id DESC
                        ";

            $this->db->exec("SET NAMES 'utf8mb4'");
            $db_query = $this->db->prepare($query);
            $db_query->execute();

            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);
            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';

            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function obtener_datos_declaracion_jurada($contrato_id)
    {
        try {

            $query  = "
               SELECT 
               lm.nombre_file,
               lm.extension,
               lm.created_at,
               dj.nombre as declaracion_jurada
               FROM
               cont_licencia_municipales lm
               INNER JOIN cont_tipo_archivos ta ON lm.tipo_archivo_id = ta.tipo_archivo_id
               INNER JOIN cont_contrato c ON lm.contrato_id = c.contrato_id
               LEFT JOIN cont_declaracion_jurada dj ON c.declaracion_jurada_id = dj.id
               WHERE
               lm.contrato_id = '$contrato_id'
               AND lm.tipo_archivo_id = 7
               AND lm.estado = 1
               ORDER BY lm.id DESC";

            $this->db->exec("SET NAMES 'utf8mb4'");
            $db_query = $this->db->prepare($query);
            $db_query->execute();

            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);
            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';

            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    // CONTRATO FIRMADO 
    public function obtener_datos_contratos_firmados($contrato_id)
    {
        try {

            $query  = "SELECT 
                            c.contrato_id,
                            c.nombre_tienda,
                            CONCAT(IFNULL(tpa.nombre, ''),
                                    ' ',
                                    IFNULL(tpa.apellido_paterno, ''),
                                    ' ',
                                    IFNULL(tpa.apellido_materno, '')) AS usuario_aprobado
                        FROM
                            cont_contrato c
                                INNER JOIN
                            cont_condicion_economica ce ON c.contrato_id = ce.contrato_id
                                INNER JOIN
                            tbl_usuarios tu ON ce.usuario_contrato_aprobado_id = tu.id
                                INNER JOIN
                            tbl_personal_apt tpa ON tu.personal_id = tpa.id
                        WHERE
                            c.etapa_id = 5
                            AND ce.status = 1
                            AND c.contrato_id = " . $contrato_id . " 
                        ";

            $this->db->exec("SET NAMES 'utf8mb4'");
            $db_query = $this->db->prepare($query);
            $db_query->execute();

            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);
            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';

            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    // CONTRATO FIRMADO 
    public function obtener_datos_archivos_contrato($contrato_id, $tipo_archivo_id)
    {
        try {

            $query  = "SELECT 
                            archivo_id,
                            contrato_id,
                            tipo_archivo_id,
                            nombre,
                            extension,
                            ruta,
                            size,
                            user_created_id,
                            status,
                            created_at
                        FROM
                            cont_archivos
                        WHERE
                            tipo_archivo_id = $tipo_archivo_id
                            AND status = 1
                            AND contrato_id = " . $contrato_id;

            $this->db->exec("SET NAMES 'utf8mb4'");
            $db_query = $this->db->prepare($query);
            $db_query->execute();

            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);
            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';

            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    // CONTRATO ADENDAS 
    public function obtener_datos_adendas($contrato_id)
    {
        try {

            $query  = "SELECT 
                            a.id,
                            a.codigo,
                            a.procesado,
                            a.created_at AS fecha_solicitud,
                            a.estado_solicitud_id,
                            concat(IFNULL(p.nombre, ''), ' ',  IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS solicitante,
                            ar.nombre AS area,
                            a.cancelado_id,
                            CONCAT(IFNULL(tpa3.nombre, ''),' ',IFNULL(tpa3.apellido_paterno, ''),	' ',	IFNULL(tpa3.apellido_materno, '')) AS cancelado_por,
                            a.cancelado_el,
                            a.cancelado_motivo
                        FROM 
                            cont_adendas a
                            INNER JOIN tbl_usuarios u ON a.user_created_id = u.id
                            INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
                            INNER JOIN tbl_areas ar ON p.area_id = ar.id
                            LEFT JOIN tbl_usuarios tu3 ON a.cancelado_por_id = tu3.id
                            LEFT JOIN tbl_personal_apt tpa3 ON tu3.personal_id = tpa3.id
                        WHERE a.contrato_id = " . $contrato_id . "
                        AND a.status = 1;";

            $this->db->exec("SET NAMES 'utf8mb4'");
            $db_query = $this->db->prepare($query);
            $db_query->execute();

            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);
            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';

            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }
    // CONTRATO ADENDAS DETALLE 
    public function obtener_datos_adendas_detalle($adenda_id)
    {
        try {

            $query  = "SELECT id,
                            adenda_id,
                            nombre_tabla,
                            valor_original,
                            nombre_menu_usuario,
                            nombre_campo_usuario,
                            nombre_campo,
                            tipo_valor,
                            valor_varchar,
                            valor_int,
                            valor_date,
                            valor_decimal,
                            valor_select_option,
                            status
                        FROM cont_adendas_detalle
                        WHERE adenda_id = " . $adenda_id . "
                        AND tipo_valor != 'id_tabla' AND tipo_valor != 'registro' AND tipo_valor != 'eliminar'
                        AND status = 1";

            $this->db->exec("SET NAMES 'utf8mb4'");
            $db_query = $this->db->prepare($query);
            $db_query->execute();

            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);
            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';

            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }
}
