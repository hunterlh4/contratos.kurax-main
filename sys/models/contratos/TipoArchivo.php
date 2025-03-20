<?php
class TipoArchivo extends Model {

    private $data;
    private $id;
  
    public function registrar($data) {
        try {
            
            $query = "INSERT INTO cont_tipo_archivos
                    (
                    nombre_tipo_archivo,
                    tipo_contrato_id,
                    status,
                    user_created_id,
                    created_at)
                    VALUES
                    (
                    '". $this->replace_invalid_caracters($data["nombre"]). "',
                    '". $data["tipo_contrato_id"] . "',
                    1,
                    " . $this->get_usuario_id() . ",
                    '" . $data["created_at"]  . "')";
                
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();

            $result['status'] = 200;
            $result['result'] = $id;
            $result['message'] = 'Se ha registrado exitosamente el tipo de anexo';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }

    }



    public function modificar($data) {
        try {

            $query = "UPDATE cont_tipo_archivos
                        SET
                            nombre_tipo_archivo = '" . $this->replace_invalid_caracters($data["nombre"]). "',
                            tipo_contrato_id = " . $data["tipo_contrato_id"]. ",
                            user_updated_id =  " . $this->get_usuario_id() . ",
                            updated_at = '" . $data["updated_at"]. "'
                        WHERE tipo_archivo_id = " . $data["id"];

            $db_query = $this->db->prepare($query);
            $db_query->execute();


            $result['status'] = 200;
            $result['result'] = $data["id"];
            $result['message'] = 'Se ha modificado exitosamente el tipo de anexo';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }

    }


}