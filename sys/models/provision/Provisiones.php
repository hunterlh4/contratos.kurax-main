<?php 

class Provision extends Model {
     // PARA CALCULO DE PROVISIONES COONTABLES
     public function obtener_datos_generales_para_provision($contrato_id,$ultimo_dia_mes_pasado){
        try {

            $query  = "SELECT
                            c.contrato_id,
                            p.num_ruc,
                            c.empresa_suscribe_id,
                            ce.condicion_economica_id,
                            ce.monto_renta,
                            ce.tipo_moneda_id,
                            ce.fecha_inicio,
                            ce.fecha_fin,
                            ce.garantia_monto,
                            ce.impuesto_a_la_renta_id,
                            ce.carta_de_instruccion_id,
                            dp.nombre AS dia_de_pago,
                            dp.id AS dia_de_pago_id,
                            b.forma_pago_id,
                            c.nombre_tienda ,
                            b.nombre,b.num_docu,c.cc_id

                        FROM 
                            cont_contrato c
                            INNER JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id AND ce.status = 1
                            INNER JOIN tbl_moneda m ON ce.tipo_moneda_id = m.id
                            INNER JOIN cont_inmueble i ON c.contrato_id = i.contrato_id AND i.status = 1
                            INNER JOIN cont_etapa e ON c.etapa_id = e.etapa_id
                            INNER JOIN tbl_areas a ON e.area_id = a.id
                            LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
                            LEFT JOIN cont_beneficiarios b ON c.contrato_id = b.contrato_id  AND b.status = 1
                            LEFT JOIN cont_propietario pr ON pr.contrato_id = c.contrato_id
                            LEFT JOIN cont_persona p ON pr.persona_id = p.id
                            LEFT JOIN cont_tipo_dia_de_pago dp ON ce.dia_de_pago_id = dp.id
                            LEFT JOIN cont_tipo_persona tp ON p.tipo_persona_id = tp.id
                        WHERE 
                            c.contrato_id = $contrato_id
                            AND c.status = 1
                            AND tp.id = 1
                            AND c.etapa_id=5
                            and ce.fecha_fin > '$ultimo_dia_mes_pasado' 
                            GROUP BY c.contrato_id ";

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

public function verificar_provisiones_en_contato($contrato_id){
    try {

        $query  = "SELECT cp.id from cont_provision cp where cp.contrato_id=".$contrato_id;

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

    public function obtener_contratos($ultimo_dia_mes_pasado, $ids_de_contratos_con_ipct = null)
    {
        try {
            // esta es la misma consulat parra traer los contratos locales en la vista 
            $query = '';
            $query  .= "SELECT c.contrato_id
                    FROM
                    cont_contrato c
                    INNER JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id AND ce.status = 1
                    INNER JOIN tbl_moneda m ON ce.tipo_moneda_id = m.id
                    INNER JOIN cont_inmueble i ON c.contrato_id = i.contrato_id AND i.status = 1
                    INNER JOIN cont_etapa e ON c.etapa_id = e.etapa_id
                    INNER JOIN tbl_areas a ON e.area_id = a.id
                    LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
                    LEFT JOIN cont_beneficiarios b ON c.contrato_id = b.contrato_id
                    LEFT JOIN cont_propietario pr ON pr.contrato_id = c.contrato_id
					LEFT JOIN cont_persona p ON pr.persona_id = p.id
                    LEFT JOIN cont_tipo_persona tp ON p.tipo_persona_id = tp.id
                    INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id 
                    
                    WHERE c.status = 1 AND c.etapa_id = 5
                    AND tp.id = 1
                    AND ce.fecha_fin > '$ultimo_dia_mes_pasado' 
                    -- AND c.contrato_id in ( 180,178,177,1961,1960)  
                    
                    AND r.estado_tesoreria=1 ";
            if (!empty($ids_de_contratos_con_ipct)) {
                $query .= "  AND c.contrato_id NOT IN ($ids_de_contratos_con_ipct) ";
            }

            $query .= " GROUP BY c.contrato_id;  ";

            //AND contrato_id IN (1042,336,474,1010)";

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
            $result['result'] = $th->getMessage() . $query;
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }


    public function obtener_contratos_con_ipc($ultimo_dia_mes_pasado,$contrato_id=null)
    {
        try {
            // esta es la misma consulat parra traer los contratos locales en la vista 
            if($contrato_id == null){
                $query  = "SELECT c.contrato_id, ce.monto_renta,ce.fecha_inicio ,ce.fecha_fin  ,c.cc_id ,ci.contrato_id,r.nombre
                FROM
                cont_contrato c
                LEFT JOIN cont_inflaciones ci ON ci.contrato_id = c.contrato_id
                INNER JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id AND ce.status = 1
                
                LEFT JOIN cont_propietario pr ON pr.contrato_id = c.contrato_id
                LEFT JOIN cont_persona p ON pr.persona_id = p.id
                LEFT JOIN cont_tipo_persona tp ON p.tipo_persona_id = tp.id
                INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id 
                
                WHERE c.status = 1 AND c.etapa_id = 5
                AND tp.id = 1
                AND ce.fecha_fin > '$ultimo_dia_mes_pasado'
                AND r.estado_tesoreria=1
                AND ci.contrato_id IS NOT NULL 
                -- AND c.contrato_id in ( 180,178,177,1961,1960)   



                GROUP BY c.contrato_id;";
            }else{
                $query  = "SELECT c.contrato_id, ce.monto_renta,ce.fecha_inicio ,ce.fecha_fin  ,c.cc_id ,ci.contrato_id,r.nombre
                FROM
                cont_contrato c
                LEFT JOIN cont_inflaciones ci ON ci.contrato_id = c.contrato_id
                INNER JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id AND ce.status = 1
                
                LEFT JOIN cont_propietario pr ON pr.contrato_id = c.contrato_id
                LEFT JOIN cont_persona p ON pr.persona_id = p.id
                LEFT JOIN cont_tipo_persona tp ON p.tipo_persona_id = tp.id
                INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id 
                
                WHERE c.status = 1 AND c.etapa_id = 5
                AND tp.id = 1
                AND ce.fecha_fin > '$ultimo_dia_mes_pasado'
                AND r.estado_tesoreria=1
                AND ci.contrato_id IS NOT NULL 
                -- AND c.contrato_id in ( 180,178,177,1961,1960) 



                GROUP BY c.contrato_id;";
            }
            
                    

            //AND contrato_id IN (1042,336,474,1010)";

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



    public function insertar_provision($provisiones)
    {
        try {
                $query = "INSERT INTO cont_provision (
                    contrato_id,
                    etapa_id,
                    empresa_id,
                    renta_bruta,
                    importe,
                    num_cuota,
                    periodo_inicio,
                    periodo_fin,
                    dia_de_pago,
                    condicion_economica_id,
                    tipo_id,
                    moneda_id,
                    anio,mes,status,forma_de_pago_id,fecha_actual,valor_ipc,valor_incremento,
                    AT_paga_impuesto,incrementos,tipo_anticipo_id,num_adelanto_id,
                    user_created_id,
                    created_at
                    ) 
                VALUES (:contrato_id, :etapa_id,:empresa_id,:renta_bruta,
                :importe,:num_cuota,:periodo_inicio,:periodo_fin,:dia_de_pago,:condicion_economica_id,
                :tipo_id,:moneda_id,:anio,:mes,:status,:forma_de_pago_id,:fecha_actual,:valor_ipc,:valor_incremento,
                :AT_paga_impuesto,:incrementos,:tipo_anticipo_id,:num_adelanto_id,:user_created_id,:created_at)";

                // var_dump(json_encode($provision,JSON_UNESCAPED_UNICODE));
                $query_detalle = "INSERT INTO cont_provision_detalle (
                                provision_id,
                                monto_renta,
                                total_calculado,
                                descuento_IR,
                                total_pagar,
                                status,
                                user_created_id,
                                created_at
                                ) 
                                VALUES (
                                :provision_id,
                                :monto_renta,
                                :total_calculado,
                                :descuento_IR,
                                :total_pagar,
                                :status,
                                :user_created_id,
                                :created_at)";
                $this->db->beginTransaction();
                $db_query = $this->db->prepare($query);
                $db_query_detalle = $this->db->prepare($query_detalle);
                foreach ($provisiones as $provision) {
                    $db_query->bindParam(':contrato_id', $provision['contrato_id']);
                    $db_query->bindParam(':etapa_id', $provision['estado_pago']);
                    $db_query->bindParam(':empresa_id', $provision['empresa_suscribe_id']);
                    $db_query->bindParam(':renta_bruta', $provision['renta_bruta']);
                    $db_query->bindParam(':importe', $provision['importe']);
                    $db_query->bindParam(':num_cuota', $provision['num_cuota']);
                    $db_query->bindParam(':periodo_inicio', $provision['periodo_inicio']);
                    $db_query->bindParam(':periodo_fin', $provision['periodo_fin_tmp']);
                    $db_query->bindParam(':dia_de_pago', $provision['dia_de_pago']);
                    $db_query->bindParam(':condicion_economica_id', $provision['condicion_economica_id']);
                    $db_query->bindParam(':tipo_id', $provision['tipo_id']);
                    $db_query->bindParam(':moneda_id', $provision['tipo_moneda_id']);
                    $db_query->bindParam(':anio', $provision['anio']);
                    $db_query->bindParam(':mes', $provision['mes']);
                    $db_query->bindParam(':status', $provision['status']);
                    $db_query->bindParam(':created_at', $provision['created_at']);
                    $db_query->bindParam(':user_created_id',$provision['status']);
                    $db_query->bindParam(':tipo_anticipo_id', $provision['tipo_anticipo_id']);
                    $db_query->bindParam(':num_adelanto_id', $provision['num_adelanto_id']);
                    $db_query->bindParam(':fecha_actual', $provision['fecha_actual']);
                    $db_query->bindParam(':forma_de_pago_id', $provision['status']);
                    $db_query->bindParam(':AT_paga_impuesto', $provision['carta_de_instruccion_id']);
                    $db_query->bindParam(':incrementos', $provision['posee_incrementos']);
                    $db_query->bindParam(':valor_ipc', $provision['ajuste_inflacion']);
                    $db_query->bindParam(':valor_incremento', $provision['valor_incremento']);

                    $db_query->execute();

                    //Obtener el último ID insertado y realizar cualquier otra lógica necesaria
                    $lastInsertId = $this->db->lastInsertId();




                    $db_query_detalle->bindParam(':provision_id', $lastInsertId);
                    $db_query_detalle->bindParam(':monto_renta', $provision['monto_renta']); //aun no esta pagada
                    $db_query_detalle->bindParam(':total_calculado', $provision['total_calculado']);
                    $db_query_detalle->bindParam(':descuento_IR', $provision['descuento_IR']);
                    $db_query_detalle->bindParam(':total_pagar', $provision['importe']);
                    $db_query_detalle->bindParam(':status',$provision['status']);
                    $db_query_detalle->bindParam(':user_created_id', $provision['status']);
                    $db_query_detalle->bindParam(':created_at',$provision['created_at']);
                    $db_query_detalle->execute();

                 // ...
                }
            $this->db->commit();


            // var_dump( $lastInsertId);
            $result['status'] = 200;
            $result['message'] = 'Inserción exitosa en cont_provision';
            $result['status'] = 1;
            
            return $result;
        } catch (Exception $th) {
            $this->db->rollback();
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }
public function eliminar_provisiones_por_contato($contrato_id,$ultimo_dia_mes_pasado){
    try {
        $query = "DELETE FROM cont_provision WHERE contrato_id = ".$contrato_id." AND fecha_actual = '$ultimo_dia_mes_pasado' ";
        
        $this->db->exec("SET NAMES 'utf8mb4'"); 
        $db_query = $this->db->prepare($query);
        $db_query->execute();
    
        $result['status'] = 200;
        $result['result'] = 1;
        $result['message'] = 'Registros eliminados correctamente';
        
        return $result;
    } catch (\Throwable $th) {
        $result['status'] = 404;
        $result['result'] = $th->getMessage();
        $result['message'] = 'Ha ocurrido un error con la consulta SQL';
        return $result;
    }
    
}

public function obtener_provisiones_por_periodo($fecha_limite,$fecha_actual_año_actual,$tipo_provisiones){
    try {

        // $query  = "select *from cont_provision cp where cp.periodo_inicio < '$fecha_limite' and contrato_id <> 'NULL'";
        $query  = "SELECT  tul.usuario_id  FROM tbl_usuarios_locales tul WHERE tul.usuario_id=".$this->get_usuario_id() ;
        // var_dump($query);exit();
       
        $this->db->exec("SET NAMES 'utf8mb4'"); 
        $db_query = $this->db->prepare($query);
        $db_query->execute();

        $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);
        $cantidadRegistros = count($resultado);
        

        $query = '';
        if($tipo_provisiones == '0'){
            $query.= " SELECT cp.id as provision_id,cp.tipo_anticipo_id, cp.contrato_id ,rs.nombre AS empresa_suscribe ,rs.subdiario,cp.num_cuota
            ,cp.created_at,c.nombre_tienda ,cp.periodo_inicio, cp.periodo_fin,cp.etapa_id,cp.dia_de_pago,cp.fecha_actual,total_calculado,
            pd.monto_renta,total_pagar ,cp.moneda_id,c.cc_id ,cp.moneda_id,cp.periodo_inicio,cp.periodo_fin,cp.valor_ipc,cp.valor_incremento,incrementos
            from cont_provision cp
            LEFT JOIN cont_provision_detalle pd ON cp.id =pd.provision_id
            LEFT JOIN cont_contrato c ON c.contrato_id =cp.contrato_id 
            LEFT JOIN tbl_razon_social rs ON rs.id =c.empresa_suscribe_id
            LEFT JOIN tbl_locales tl ON tl.cc_id  =  c.cc_id  
            LEFT JOIN tbl_usuarios_locales tul ON tul.local_id  = tl.id 
            where cp.status = 1
            AND cp.fecha_actual = '$fecha_limite'
            AND cp.valor_ipc IS NULL
            AND rs.estado_tesoreria=1 ";
    
        }else{
            $query.= " SELECT DISTINCT cp.id as provision_id,cp.tipo_anticipo_id, cp.contrato_id ,rs.nombre AS empresa_suscribe ,rs.subdiario,cp.num_cuota
            ,cp.created_at,c.nombre_tienda ,cp.periodo_inicio, cp.periodo_fin,cp.etapa_id,cp.dia_de_pago,cp.fecha_actual,total_calculado,
            pd.monto_renta,total_pagar ,cp.moneda_id,c.cc_id ,cp.moneda_id,cp.periodo_inicio,cp.periodo_fin,cp.valor_ipc,cp.valor_incremento,incrementos
            from cont_provision cp
            LEFT JOIN cont_provision_detalle pd ON cp.id =pd.provision_id
            LEFT JOIN cont_contrato c ON c.contrato_id =cp.contrato_id 
            LEFT JOIN cont_inflaciones ci ON ci.contrato_id = c.contrato_id
            LEFT JOIN tbl_razon_social rs ON rs.id =c.empresa_suscribe_id 
            LEFT JOIN tbl_locales tl ON tl.cc_id  =  c.cc_id  
            LEFT JOIN tbl_usuarios_locales tul ON tul.local_id  = tl.id
            where cp.status = 1
            AND cp.fecha_actual = '$fecha_limite' 
            AND ci.contrato_id IS NOT NULL 
            AND rs.estado_tesoreria=1 ";
        }
        if($cantidadRegistros > 0){
            $query.= ' AND  tul.usuario_id ='.$this->get_usuario_id().'  GROUP BY cp.id';
        }else{
            $query.= '  GROUP BY cp.id';

        }
        // var_dump($query);exit();
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

public function obtener_plantilla_asiento_contable($fecha_actual_año_actual,$fecha_limite,$tipo_provisiones){
    try {
        $query  = "SELECT  tul.usuario_id  FROM tbl_usuarios_locales tul WHERE tul.usuario_id=".$this->get_usuario_id() ;
        // var_dump($query);exit();
       
        $this->db->exec("SET NAMES 'utf8mb4'"); 
        $db_query = $this->db->prepare($query);
        $db_query->execute();

        $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);
        $cantidadRegistros = count($resultado);
        

        if($tipo_provisiones == '0'){
            $query  = "SELECT cp.contrato_id ,cp.etapa_id ,cp.tipo_id,cp.AT_paga_impuesto,rs.nombre AS empresa_suscribe ,rs.subdiario
            ,cp.created_at,c.nombre_tienda ,cp.AT_paga_impuesto,cp.incrementos,cp.AT_paga_impuesto,
            pd.monto_renta,total_pagar ,cp.moneda_id,c.cc_id,b.num_docu,cp.renta_bruta,pd.monto_renta ,pd.total_calculado,
            pd.descuento_IR,pd.total_pagar,cp.periodo_inicio,cp.periodo_fin,cp.dia_de_pago,cp.num_cuota
            ,rs.subdiario_contabilidad
    
            from cont_provision cp
            LEFT JOIN cont_provision_detalle pd ON cp.id =pd.provision_id
            LEFT JOIN cont_contrato c ON c.contrato_id =cp.contrato_id 
            LEFT JOIN cont_inflaciones i ON c.contrato_id = i.contrato_id
            LEFT JOIN tbl_razon_social rs ON rs.id =c.empresa_suscribe_id 
            LEFT JOIN cont_beneficiarios b ON c.contrato_id = b.contrato_id
            LEFT JOIN tbl_locales tl ON tl.cc_id  =  c.cc_id  
            LEFT JOIN tbl_usuarios_locales tul ON tul.local_id  = tl.id
            where cp.status =1 
            AND i.contrato_id IS NULL 
            AND cp.fecha_actual = '".$fecha_limite.
            "'  AND rs.estado_tesoreria=1 ";
        }else{
            $query = "SELECT DISTINCT cp.id as provision_id ,cp.etapa_id ,cp.tipo_id,cp.AT_paga_impuesto,rs.nombre AS empresa_suscribe ,rs.subdiario
            ,cp.created_at,c.nombre_tienda ,cp.AT_paga_impuesto,cp.incrementos,cp.AT_paga_impuesto,
            pd.monto_renta,total_pagar ,cp.moneda_id,c.cc_id,b.num_docu,cp.renta_bruta,pd.monto_renta ,pd.total_calculado,
            pd.descuento_IR,pd.total_pagar,cp.periodo_inicio,cp.periodo_fin,cp.dia_de_pago,cp.num_cuota
            ,rs.subdiario_contabilidad
    
            from cont_provision cp
            LEFT JOIN cont_provision_detalle pd ON cp.id =pd.provision_id
            LEFT JOIN cont_contrato c ON c.contrato_id =cp.contrato_id 
            LEFT JOIN cont_inflaciones i ON c.contrato_id = i.contrato_id
            LEFT JOIN tbl_razon_social rs ON rs.id =c.empresa_suscribe_id 
            LEFT JOIN cont_beneficiarios b ON c.contrato_id = b.contrato_id
            LEFT JOIN tbl_locales tl ON tl.cc_id  =  c.cc_id  
            LEFT JOIN tbl_usuarios_locales tul ON tul.local_id  = tl.id
            where cp.status =1 
            AND i.contrato_id IS NOT NULL 
            AND cp.fecha_actual = '$fecha_limite'   AND rs.estado_tesoreria=1";
        }
        if($cantidadRegistros > 0){
            $query.= ' AND  tul.usuario_id ='.$this->get_usuario_id().'  GROUP BY cp.id';
        }else{
            $query.= '  GROUP BY cp.id';

        }
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
 
    public function enviar_tesoreria_provisiones($provision_idIs,$tipo_provisiones){
        try {
            
            $query  = "UPDATE cont_provision SET etapa_id = 1 
                        WHERE id IN (".$provision_idIs.") 
                         ";
                        // var_dump($query);exit();
            $this->db->exec("SET NAMES 'utf8mb4'"); 
            $db_query = $this->db->prepare($query);
            $db_query->execute();

            // $db_query = $this->db->prepare($query);
            // $db_query->bindParam(':id1', $provision_idIs);
            // $db_query->bindParam(':id2', $id2);
            $result['status'] = 200;
            $result['result'] = 1;
            $result['message'] = 'Datos Obtenidos de Gestion';
            
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function validar_envio_tesoreria($provision_idIs){
        try {
            
            $query  = "SELECT * FROM cont_provision 
                        WHERE id IN (".$provision_idIs.") 
                        AND etapa_id = 0
                         ";
                        // var_dump($query);exit();
            $this->db->exec("SET NAMES 'utf8mb4'"); 
            $db_query = $this->db->prepare($query);
            $db_query->execute();

            // $db_query = $this->db->prepare($query);
            // $db_query->bindParam(':id1', $provision_idIs);
            // $db_query->bindParam(':id2', $id2);
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

    public function obtener_valor_subdario($codigo){
        try {

            $query  = "SELECT valor FROM tbl_parametros_generales 
                        WHERE codigo = '".$codigo ."'
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
    public function obtener_datos_adelantos($contrato_id){
        try {
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    $query  = "SELECT 
                                    a.id, 
                                    t.nombre AS mes_adelanto,num_periodo
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
    public function validar_existen_anticipos($contrato_id,$anio_mes_actual){
        try {
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            if($anio_mes_actual!=-1){ // SE TRATA DE CUOTAS NORMALES
                $query  = "SELECT MAX(fecha_actual) AS max_fecha FROM cont_provision
                WHERE contrato_id =".$contrato_id;
            }else{// SE TRATA DE ANTICIPOS
                $query  = "SELECT *from cont_provision cp
                                 where (cp.tipo_anticipo_id =1 OR cp.tipo_anticipo_id =2 ) 
                                 AND contrato_id =".$contrato_id;
            }
            $db_query = $this->db->prepare($query);
            $db_query->execute();

            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $cantidadRegistros = count($resultado);
            $result['status'] = 200;
            $result['anticipos_bool'] = $cantidadRegistros>0?true:false;
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

    public function obtener_datos_incrementos($contrato_id){
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
                                   " ;
            $this->db->exec("SET NAMES 'utf8mb4'"); 
            $db_query = $this->db->prepare($query);
            $db_query->execute();

            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

           
            $cantidadRegistros = count($resultado);
            $result['num_incrementos']= $cantidadRegistros; 
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

    function validar_si_tiene_IPC($contrato_id,$ultimo_dia_mes_pasado){
        try {
            $query = "SELECT *from cont_inflaciones ci 
                                left join cont_contrato c on ci.contrato_id = c.contrato_id
                                INNER JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id AND ce.status = 1
                                LEFT JOIN cont_propietario pr ON pr.contrato_id = c.contrato_id
                                LEFT JOIN cont_persona p ON pr.persona_id = p.id
                                LEFT JOIN cont_tipo_persona tp ON p.tipo_persona_id = tp.id
                                INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id 
                        WHERE c.status = 1 
                                AND c.etapa_id = 5
                                AND tp.id = 1
                                AND r.estado_tesoreria=1
                                AND ce.fecha_fin > '$ultimo_dia_mes_pasado' 
                                AND c.contrato_id =" . $contrato_id;

            $this->db->exec("SET NAMES 'utf8mb4'");

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            if ($db_query->rowCount() > 0) {
                $resultado = 1;
            } else {
                $resultado = 0;

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

    function obtener_ipc_del_mes($mes,$anio){
        try {
            // $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $query  =  "SELECT
                    ti.mes AS mes,
                    ti.anio AS year,
                    ti.valor_porc as indice
                FROM cont_indice_inflacion ti  
                where anio='$anio' and mes = '$mes'" ;
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

    /// PROVISIONES CON ADENDAS
    // OBTENER CONTRATOS CON ADENDASD
    public function  obtener_todos_contratos_con_adendas($ultimo_dia_mes_pasado){
        try {

            $query  =  "SELECT DISTINCT a.contrato_id ,r.estado_tesoreria ,a.id AS adenda_id
                        FROM cont_adendas a 
                        LEFT JOIN cont_contrato c  ON  c.contrato_id = a.contrato_id
                        INNER JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id AND ce.status = 1
                        
                        INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id 
                            
                        WHERE c.status = 1 AND c.etapa_id = 5
                        AND ce.fecha_fin > '$ultimo_dia_mes_pasado'
                        AND r.estado_tesoreria=1
                        AND  a.usuario_responsable_estado_solicitud_id is not NULL 
                        AND a.status = 1 order by a.contrato_id" ;
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

    // OBBTENER ADENDAS APROBADAS POR CONT
    public function obtener_adendas_de_contrato($contrato_id){
        try {

            $query  =  "SELECT *FROM cont_control_provision_adenda a 
                        WHERE a.contrato_id =".$contrato_id;

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

    public function validar_contratos_vigentes($contrato_id,$ultimo_dia_mes_pasado){
        try {
            // esta es la misma consulat parra traer los contratos locales en la vista 
            $query = "SELECT c.contrato_id, ce.monto_renta, ce.fecha_inicio, ce.fecha_fin
            FROM cont_contrato c
            INNER JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id 
            AND ce.status = 1
            WHERE ce.fecha_fin > '$ultimo_dia_mes_pasado' 
            AND c.contrato_id =" . $contrato_id;

            $this->db->exec("SET NAMES 'utf8mb4'");

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            if ($db_query->rowCount() > 0) {
                // Se obtuvo al menos un registro
                // var_dump($query);

                $resultado = 1;
            } else {
                $resultado = 0;

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
    public function actualizar_dia_de_pago($contrato_id,$valor){
        try {
            $contrato_id_response = null;
            $response = null;
            $query  = "UPDATE cont_condicion_economica SET dia_de_pago_id = :valor 
                         WHERE contrato_id = :contrato_id";

            $this->db->exec("SET NAMES 'utf8mb4'");
            $db_query = $this->db->prepare($query);

             
            $db_query->bindParam(':valor', $valor, PDO::PARAM_INT);
            $db_query->bindParam(':contrato_id', $contrato_id, PDO::PARAM_INT);

            // Ejecutar la consulta
            if ($db_query->execute()) {
                // La consulta se ejecutó con éxito
                $filas_actualizadas = $db_query->rowCount();

                if ($filas_actualizadas === 0) {
                    // No se actualizó ningún registro, el contrato_id no existe o no fue actualizado
                    $contrato_id_response = $contrato_id;
                    $response = 0;
                } else {
                    $response = 1;
                    $contrato_id_response = $contrato_id;

                }
            } else {
                $response = 2;
                    // Error al ejecutar la consulta
                $contrato_id_response = $contrato_id;
            }

            $result['status'] = 200;
            $result['result'] = $response;
            $result['contrato_id'] = $contrato_id_response;
            $result['message'] = 'Datos Obtenidos de Gestion';
            
            return $result;
        } catch (PDOException $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function actualizar_data_beneficiario($contrato_id,$tipo_docu_identidad_id,
    $num_docu,$nombre,$banco_id,$num_cuenta_cci){
        try {
        
            $conrato_id_response = null;
            $response = null;
            $query  = "UPDATE cont_beneficiarios
                        SET 
                        tipo_docu_identidad_id = :tipo_docu_identidad_id ,
                        num_docu = :num_docu ,
                        nombre = :nombre ,
                        banco_id= :banco_id,
                        num_cuenta_cci=:num_cuenta_cci
                        
                        WHERE contrato_id = :contrato_id";

            $this->db->exec("SET NAMES 'utf8mb4'");
            $db_query = $this->db->prepare($query);

            // Asignar los valores de los parámetros
            
            $db_query->bindParam(':tipo_docu_identidad_id', $tipo_docu_identidad_id, PDO::PARAM_INT);
            $db_query->bindParam(':num_docu', $num_docu, PDO::PARAM_STR);
            $db_query->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            $db_query->bindParam(':banco_id', $banco_id, PDO::PARAM_INT);
            $db_query->bindParam(':num_cuenta_cci', $num_cuenta_cci, PDO::PARAM_STR);

            $db_query->bindParam(':contrato_id', $contrato_id, PDO::PARAM_INT);


            // Ejecutar la consulta
            if ($db_query->execute()) {
                // La consulta se ejecutó con éxito
                $filas_actualizadas = $db_query->rowCount();

                if ($filas_actualizadas === 0) {
                    // No se actualizó ningún registro, el contrato_id no existe o no fue actualizado
                    $conrato_id_response = $contrato_id;
                    $response = 0;


                } else {
                    // Se actualizó al menos un registro
                    $response = 1;
                    $conrato_id_response = $contrato_id;
                }
            } else {
                $response = 2;
                $conrato_id_response = $contrato_id;
            }

            // $db_query = $this->db->prepare($query);
            // $db_query->bindParam(':id1', $provision_idIs);
            // $db_query->bindParam(':id2', $id2);
            $result['status'] = 200;
            $result['result'] = $response;
            $result['contrato_id'] = $conrato_id_response;
            $result['message'] = 'Datos Obtenidos de Gestion';
            
            return $result;
        } catch (PDOException $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    // ELIMANR DATA PROVISIONES 
    public function limpiar_data_provisones(){
        try {
        
            $conrato_id_response = null;
            $response = null;
            $query = "DELETE FROM cont_provision";

            $this->db->exec("SET NAMES 'utf8mb4'");
            $db_query = $this->db->prepare($query);

            $query = "DELETE FROM cont_provision_detalle";

            $this->db->exec("SET NAMES 'utf8mb4'");
            $db_query = $this->db->prepare($query);

            $result['status'] = 200;
            $result['result'] = $response;
            $result['contrato_id'] = $conrato_id_response;
            $result['message'] = 'Datos Obtenidos de Gestion';
            
            return $result;
        } catch (PDOException $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function actualizar_data_beneficiario_cuenta_bancaria($contrato_id,$num_cuenta_bancaria){
        try {
        
            $conrato_id_response = null;
            $response = null;
            $query  = "UPDATE cont_beneficiarios
                        SET
                        num_cuenta_bancaria =:num_cuenta_bancaria
                        WHERE contrato_id = :contrato_id";

            $this->db->exec("SET NAMES 'utf8mb4'");
            $db_query = $this->db->prepare($query);

            // Asignar los valores de los parámetros
            
            $db_query->bindParam(':num_cuenta_bancaria', $num_cuenta_bancaria, PDO::PARAM_STR);


            $db_query->bindParam(':contrato_id', $contrato_id, PDO::PARAM_INT);


            // Ejecutar la consulta
            if ($db_query->execute()) {
                // La consulta se ejecutó con éxito
                $filas_actualizadas = $db_query->rowCount();

                if ($filas_actualizadas === 0) {
                    // No se actualizó ningún registro, el contrato_id no existe o no fue actualizado
                    $conrato_id_response = $contrato_id;
                    $response = 0;


                } else {
                    // Se actualizó al menos un registro
                    $response = 1;
                    $conrato_id_response = $contrato_id;
                }
            } else {
                $response = 2;
                $conrato_id_response = $contrato_id;
            }

            // $db_query = $this->db->prepare($query);
            // $db_query->bindParam(':id1', $provision_idIs);
            // $db_query->bindParam(':id2', $id2);
            $result['status'] = 200;
            $result['result'] = $response;
            $result['contrato_id'] = $conrato_id_response;
            $result['message'] = 'Datos Obtenidos de Gestion';
            
            return $result;
        } catch (PDOException $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }



}