<?php
class Modificaciones extends Model {

    private $data;
    private $id;
  
    public function listar_parametros_generales($data) {
        $query = '';
        try {
            
            $query = "SELECT * FROM tbl_modificaciones ORDER BY id ASC";
     
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
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
            return $result;
        }

    }

    public function obtener_pod_id($data) {
        $query = '';
        try {
            
            $query = "SELECT * FROM tbl_modificaciones WHERE id = ".$data['id'];
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
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
            return $result;
        }

    }


    public function registrar($data) {
        $query = '';
        try {
            
            $query = "INSERT INTO tbl_modificaciones (
                    modulo,
                    version,
                    comment,
                    status,
                    user_updated_id,
                    updated_at,
                    user_created_id,
                    created_at
            )VALUES(
                '".$data['modulo']."',
                '".$data['version']."',
                '".$data['comment']."',
                '".$data['status']."',
                '".$data['user_updated_id']."',
                '".$data['updated_at']."',
                '".$data['user_created_id']."',
                '".$data['created_at']."'
            )";
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $result['status'] = 200;
            $result['result'] = 0;
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


    public function modificar($data) {
        $query = '';
        try {
            
            $query = "UPDATE tbl_modificaciones SET 
                    modulo = '".$data['modulo']."',
                    version = '".$data['version']."',
                    comment = '".$data['comment']."',
                    status = '".$data['status']."',
                    updated_at = '".$data['updated_at']."',
                    user_updated_id = '".$data['user_updated_id']."'
                WHERE id = '".$data['id']."'
            ";
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $result['status'] = 200;
            $result['result'] = 0;
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