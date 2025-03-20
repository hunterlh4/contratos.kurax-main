<?php
class ResponsableIR extends Model {

    private $data;
    private $id;
  
    public function registrar($data) {
        $query = '';
        try {
            
            $query = "INSERT INTO cont_responsable_ir (
                    tipo_documento_id,
                    num_documento,
                    nombres,
                    estado_emisor,
                    porcentaje,
                    status,
                    user_created_id,
                    created_at)
                    VALUES
                    (
                    " . $data["tipo_documento_id"] . ",
                    '" . $data["num_documento"] . "',
                    '" . $this->replace_invalid_caracters($data["nombres"]). "',
                    '" . $data["estado_emisor"]. "',
                    " . $data["porcentaje"]. ",
                    1,
                    " . $this->get_usuario_id() . ",
                    '" . $data["created_at"]  . "')";
                
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();



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
            WHERE r.id = ".$id.' LIMIT 1';
    
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);


            $result['status'] = 200;
            $result['result'] = $resultado[0];
            $result['message'] = 'Se ha registrado exitosamente el responsable IR';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Responsable IR)';
            return $result;
        }

    }



    public function modificar($data) {
        try {

            $query = "UPDATE cont_responsable_ir
                        SET
                            tipo_documento_id = '".$data['tipo_documento_id']."',
                            num_documento = '".$data['num_documento']."',
                            nombres = '".$this->replace_invalid_caracters($data['nombres'])."',
                            estado_emisor = '".$data['estado_emisor']."',
                            porcentaje = '".$data['porcentaje']."',
                            user_updated_id = '". $this->get_usuario_id() ."',
                            updated_at = '".$data['updated_at']."'
                        WHERE id = " . $data["id"];

            $db_query = $this->db->prepare($query);
            $db_query->execute();
      
            
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
            WHERE r.id = ".$data["id"].' LIMIT 1';
    
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado[0];
            $result['message'] = 'Se ha modificado exitosamente el representante IR';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }

    }

    public function asignar_contrato($data) {
        $query = '';
        try {

            $query = "UPDATE cont_responsable_ir
                        SET
                            contrato_id = " . $data["contrato_id"] . ",
                            contrato_detalle_id = '" . $data["contrato_detalle_id"] . "',
                            user_updated_id = " . $data["user_updated_id"] . ",
                            updated_at = '" . $data["updated_at"] . "'
                        WHERE id = " . $data["id"];

            $db_query = $this->db->prepare($query);
            $db_query->execute();
      
 
            $result['status'] = 200;
            $result['result'] = $data["id"];
            $result['message'] = 'Se ha modificado exitosamente el representate IR';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Representante IR)';
            return $result;
        }

    }


}