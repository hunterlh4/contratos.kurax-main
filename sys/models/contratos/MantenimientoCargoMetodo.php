<?php
class MantenimientoCargoMetodo extends Model {

    private $data;
    private $id;
  
    public function listar_areas($data) {
        $query = '';
        try {
            
            $query = "SELECT *FROM tbl_areas ta WHERE ta.estado = 1";
     
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
            $result['message'] = 'A ocurrido un error con la consulta sql (Correo Motivo)';
            return $result;
        }

    }

    public function obtener_por_id($data) {
        $query = '';
        try {
            
            $query = "SELECT *FROM tbl_areas ta WHERE ta.estado = 1 AND ta.id = ".$data["id"];
     
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
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Correo Motivo)';
            return $result;
        }

    }
    

    public function registrar($data) {
        $query = '';
        try {
            
            $query = "INSERT INTO cont_mantenimiento_correo_metodo (
                tipo_metodo_id,
                nombre,
                metodo,
                status,
                user_created_id,
                created_at
            )VALUES(
                " . $data['tipo_metodo_id'] . ",
                '" . $data['nombre'] . "',
                '" . $data['metodo'] . "',
                " . $data['status']. ",
                " . $data["user_created_id"]. ",
                '" . $data["created_at"]. "'
			)";
     
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();

            $result['status'] = 200;
            $result['result'] = $id;
            $result['message'] = 'Se ha registrado correctamente el registro';
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
            
            $query = "UPDATE cont_mantenimiento_correo_metodo 
                SET tipo_metodo_id = ".$data['tipo_metodo_id'].",
                nombre = '".$data['nombre']."',
                metodo = '".$data['metodo']."',
                status = '".$data['status']."',
                user_updated_id = ".$data['user_updated_id'].",
                updated_at = '".$data['updated_at']."'
            WHERE id = ".$data['id'];
     
            $db_query = $this->db->prepare($query);
            $db_query->execute();
        
            $result['status'] = 200;
            $result['result'] = $data;
            $result['message'] = 'Se ha modificado correctamente el registro';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Adelanto)';
            return $result;
        }

    }


    public function eliminar_por_id($data) {
        $query = '';
        try {
            
            $query = "UPDATE cont_mantenimiento_correo_metodo 
                SET  status = '9',
                user_updated_id = ".$data['user_updated_id'].",
                updated_at = '".$data['updated_at']."'
            WHERE id = ".$data['id'];
     
            $db_query = $this->db->prepare($query);
            $db_query->execute();
        
            $result['status'] = 200;
            $result['result'] = $data;
            $result['message'] = 'Se ha eliminado correctamente el registro';
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