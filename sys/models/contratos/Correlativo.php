<?php
class Correlativo extends Model {

    private $data;
    private $id;
  
    public function obtener_correlativo($tipo_contrato_id) {
        try {
            
            //actualizar correlativo
            $query = "UPDATE cont_correlativo SET numero = numero + 1 WHERE tipo_contrato = ".$tipo_contrato_id." AND status = 1";
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            //obtener correlativo
            $query = "SELECT tipo_contrato, sigla, numero,  status FROM cont_correlativo
            WHERE tipo_contrato = ".$tipo_contrato_id." AND status = 1 LIMIT 1";
            $db_query = $this->db->prepare($query);
            $db_query->execute();
          
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado[0];
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }

    }

    public function listar_correlativos() {
        try {
            
            //actualizar correlativo
            $query = "SELECT c.*, t.nombre AS tipo_contrato FROM cont_correlativo AS c 
            INNER JOIN cont_tipo_contrato AS t ON t.id = c.tipo_contrato
            ORDER BY c.id ASC";
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }

    }

    public function obtener_por_id($data) {
        try {
            
            //actualizar correlativo
            $query = "SELECT c.*, t.nombre AS tipo_contrato FROM cont_correlativo AS c 
            INNER JOIN cont_tipo_contrato AS t ON t.id = c.tipo_contrato
            WHERE c.id = ".$data['id'];
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetch(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }

    }

    public function modificar_correlativo($data) {
        try {
            
            //actualizar correlativo
            $query = "UPDATE cont_correlativo SET 
                sigla = '".$data['sigla']."',
                numero = '".$data['numero']."',
                user_updated_id = '".$data['user_updated_id']."',
                updated_at = '".$data['updated_at']."'
            WHERE id = ".$data['id'];
            $db_query = $this->db->prepare($query);
            $db_query->execute();

            $result['status'] = 200;
            $result['result'] = [];
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }

    }
    
    public function listar_tipos_contratos($data) {
        try {
            
            //actualizar correlativo
            $where_not_in = "";
            if (isset($data['not_tipo_contrato_id']) && !Empty($data['not_tipo_contrato_id'])) {
                $where_not_in = "  AND t.id NOT IN (".$data['not_tipo_contrato_id'].")";
            }
            $query = "SELECT t.id, t.nombre FROM cont_tipo_contrato AS t
            WHERE t.status = 1 AND t.id IN (2,5) ".$where_not_in;
            
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }

    }

    public function obtener_contratos_por_tipo_contrato($data) {
        try {
            
            $query = "SELECT
                c.contrato_id as id, 
                CONCAT(IFNULL(co.sigla,''),c.codigo_correlativo, ' | ', c.ruc,' - ',c.razon_social) AS nombre
            FROM 
                cont_contrato c
                LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
            WHERE 
                c.status = 1 
                AND c.etapa_id = 5 
                AND c.tipo_contrato_id = ".$data['tipo_contrato_id']."
            ORDER BY c.contrato_id ASC
            ";
            
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }

    }

    public function obtener_correlativo_por_id($data) {
        try {
        
            $query = "SELECT tipo_contrato, sigla, numero,  status FROM cont_correlativo
            WHERE tipo_contrato = ".$data['tipo_contrato_id']." AND status = 1 LIMIT 1";
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetch(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }

    }


    


  


}