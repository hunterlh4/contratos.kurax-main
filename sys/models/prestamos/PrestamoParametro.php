<?php
class PrestamoParametro extends Model {

    private $data;
    private $id;
  
    public function listar_prestamos_parametros($data) {
        $query = '';
        try {
            
            $query = "SELECT pp.*, CONCAT('') AS acciones FROM tbl_prestamo_parametro AS pp ORDER BY pp.id ASC";
     
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
            $result['message'] = 'A ocurrido un error con la consulta sql (Incremento)';
            return $result;
        }

    }

    public function registrar_prestamo_parametro($data) {
        $query = '';
        try {
            
            $query = "INSERT INTO tbl_prestamo_parametro (
                nombre,
                descripcion,
                valor,
                user_created_id,
                created_at
            )VALUES(
                '" . $data['nombre'] . "',
                '" . $data['descripcion'] . "',
                '" . $data['valor'] . "',
                '" . $data['user_created_id'] . "',
                '" . $data['created_at'] . "'
			)";
     
            $db_query = $this->db->prepare($query);
            $db_query->execute();

            $result['status'] = 200;
            $result['result'] = '';
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Cierre Efectivo)';
            return $result;
        }

    }

    public function obtener_prestamo_parametro($data) {
        $query = '';
        try {
            
            $query = "SELECT pp.* FROM tbl_prestamo_parametro AS pp WHERE pp.id = ".$data['id_parametro_configuracion'];
     
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
            $result['message'] = 'A ocurrido un error con la consulta sql (Incremento)';
            return $result;
        }

    }

    public function editar_prestamo_parametro($data) {
        $query = '';
        try {
            $query = "UPDATE tbl_prestamo_parametro SET 
                nombre = '" . $data['nombre'] . "',
                descripcion = '" . $data['descripcion'] . "',
                valor = '" . $data['valor'] . "',
                user_updated_id = '" . $data['user_updated_id'] . "',
                updated_at = '" . $data['updated_at'] . "'
                WHERE id = '" . $data['id'] . "'";
     
            $db_query = $this->db->prepare($query);
            $db_query->execute();

            $result['status'] = 200;
            $result['result'] = $data['id'];
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Cierre Efectivo)';
            return $result;
        }

    }


}