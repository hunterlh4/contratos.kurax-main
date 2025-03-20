<?php
class MepaCorrelativo extends Model {

    private $data;
    private $id;
  
    public function obtener_permiso_empresas($data) {
        $query = '';
        try {
            $where_empresa = "";
            if ($data['empresa'] == "RED AT") {
                $where_empresa = " AND l.red_id NOT IN (16)";
            } else if ($data['empresa'] == "RED IGH") {
                $where_empresa = " AND l.red_id IN (16)";
            }
            if($data["usuario_locales"]){
                $query = "SELECT l.razon_social_id, r.nombre FROM tbl_locales AS l 
                INNER JOIN tbl_razon_social AS r ON r.id = l.razon_social_id
                WHERE l.estado = 1 AND r.status = 1 
                AND l.id IN (".implode(",", $data["usuario_locales"]).")
                ".$where_empresa."
                GROUP BY l.razon_social_id 
                ORDER BY l.nombre ASC;";
                
            }else{
                $query = "SELECT r.id, r.nombre FROM tbl_razon_social AS r
                WHERE r.status = 1 
                ORDER BY r.nombre ASC";
            }
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = count($resultado) == 0 ? false: true;
            $result['message'] = 'Se ah resetado correctamente el correlativo.';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Incremento)';
            return $result;
        }

    }

    public function resetear_correlativo($data) {
        $query = '';
        try {

            $query_val = "SELECT h.* FROM mepa_documento_correlativo_historial AS h
            WHERE h.id_tipo = ".$data['id']." AND DATE_FORMAT(h.created_at,'%Y') = '".date('Y')."' AND h.status = 1";
            $db_query = $this->db->prepare($query_val);
            $db_query->execute();
            $registros = $db_query->fetchAll(PDO::FETCH_ASSOC);

            if (count($registros) > 0) {
                $result['status'] = 404;
                $result['result'] = '';
                $result['query'] = $query_val;
                $result['message'] = 'Ya se ha reseteado el correlativo para el '.date('Y');
                return $result;
            }

            if ($data['id'] == 1) { //Correlativo Reporte Contabilidad Concar Red AT
                $query = "UPDATE mepa_documento_correlativo  SET num_correlativo = '1' WHERE tipo_solicitud = 4";
            }else if($data['id'] == 2){ //Correlativo Reporte Contabilidad Concar Red IGH
                $query = "UPDATE mepa_documento_correlativo  SET num_correlativo = '1' WHERE tipo_solicitud = 19";
            }else if($data['id'] == 3){ //Correlativo Rendición Liquidación Red AT
                $query = "UPDATE mepa_documento_correlativo AS c 
                INNER JOIN mepa_asignacion_caja_chica AS ac ON ac.id = c.asignacion_id
                SET c.num_correlativo = '1' 
                WHERE ac.empresa_id NOT IN (30)";
            }else if($data['id'] == 4){ //Correlativo Rendición Liquidación Red IGH
                $query = "UPDATE mepa_documento_correlativo AS c 
                INNER JOIN mepa_asignacion_caja_chica AS ac ON ac.id = c.asignacion_id
                SET c.num_correlativo = '1' 
                WHERE ac.empresa_id IN (30)";
            }else if($data['id'] == 5){ //Correlativo Plantilla movilidad Red AT
                $query = "UPDATE mepa_documento_correlativo AS dc 
                INNER JOIN mepa_asignacion_caja_chica AS ac ON dc.id_usuario = ac.usuario_asignado_id AND ac.status = 1
                    SET dc.num_correlativo = 0
                WHERE 1 = 1
                AND dc.tipo_solicitud = 3 
                AND ac.empresa_id NOT IN (30)
                AND ac.situacion_etapa_id IN (6)";
            }else if($data['id'] == 6){ //Correlativo Plantilla movilidad  Red IGH
                $query = "UPDATE mepa_documento_correlativo AS dc 
                INNER JOIN mepa_asignacion_caja_chica AS ac ON dc.id_usuario = ac.usuario_asignado_id AND ac.status = 1
                    SET dc.num_correlativo = 0
                WHERE 1 = 1
                AND dc.tipo_solicitud = 3 
                AND ac.empresa_id IN (30)
                AND ac.situacion_etapa_id IN (6)";
            }
     
            $db_query = $this->db->prepare($query);
            $db_query->execute();

            $query = "INSERT INTO mepa_documento_correlativo_historial (
                id_tipo,
                usuario_id,
                status,
                created_at
            ) VALUES (
                '".$data['id']."',
                '".$data['usuario_id']."',
                '".$data['status']."',
                '".$data['created_at']."'
            )";
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();

            $result['status'] = 200;
            $result['result'] = $id;
            $result['message'] = 'Se ah resetado correctamente el correlativo.';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Incremento)';
            return $result;
        }

    }


    public function historial_reseteos($data) {
        $query = '';
        try {
            $tipo = '';
            switch ($data['id']) {
                case '1': $tipo = 'Correlativo Reporte Contabilidad Concar Red AT'; break;
                case '2': $tipo = 'Correlativo Reporte Contabilidad Concar Red IGH'; break;
                case '3': $tipo = 'Correlativo Rendición Liquidación Red AT'; break;
                case '4': $tipo = 'Correlativo Rendición Liquidación Red IGH'; break;
                case '5': $tipo = 'Correlativo Planilla Movilidad Red AT'; break;
                case '6': $tipo = 'Correlativo Planilla Movilidad Red IGH'; break;
                default: $tipo = ''; break;
            }

            $query = "SELECT h.id,
            CONCAT(IFNULL(apt.nombre,''),' ', IFNULL(apt.apellido_paterno,''), ' ',IFNULL(apt.apellido_materno,'')) AS usuario,
            h.created_at
            FROM mepa_documento_correlativo_historial AS h
            INNER JOIN tbl_usuarios AS us ON us.id = h.usuario_id 
            INNER JOIN tbl_personal_apt AS apt ON apt.id = us.personal_id AND apt.estado = 1
            WHERE h.id_tipo = ".$data['id']." ORDER BY h.created_at DESC";
     
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);
           
            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['title'] = $tipo;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Incremento)';
            return $result;
        }

    }



}