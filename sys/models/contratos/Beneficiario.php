<?php
class Beneficiario extends Model {

    private $data;
    private $id;
  
    public function registrar($data) {
        $query = "";
        try {
            
            $query = "INSERT INTO cont_beneficiarios
                    (
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
                    user_created_id,
                    created_at)
                    VALUES
                    (
                    " . $data["tipo_persona_id"] . ",
                    " . $data["tipo_docu_identidad_id"] . ",
                    '" . $data["num_docu"] . "',
                    '" . $this->replace_invalid_caracters($data["nombre"]). "',
                    " . $data["forma_pago_id"]. ",
                    " . $data["banco_id"]. ",
                    " . $data["num_cuenta_bancaria"]. ",
                    " . $data["num_cuenta_cci"]. ",
                    " . $data["tipo_monto_id"]. ",
                    " . $data["monto"]. ",
                    " . $this->get_usuario_id() . ",
                    '" . $data["created_at"]  . "')";
                
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();



            $query = "SELECT 
                b.id,
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
                b.user_created_id,
                b.created_at,
                tp.nombre AS tipo_persona,
                td.nombre AS tipo_doc_identidad,
                fp.nombre AS forma_pago,
                bc.nombre AS banco,
                tm.nombre AS tipo_monto
            FROM cont_beneficiarios as b
            LEFT JOIN cont_tipo_persona AS tp ON tp.id = b.tipo_persona_id
            LEFT JOIN cont_tipo_docu_identidad AS td ON td.id = b.tipo_docu_identidad_id
            LEFT JOIN cont_forma_pago AS fp ON fp.id = b.forma_pago_id
            LEFT JOIN tbl_bancos AS bc ON bc.id = b.banco_id
            LEFT JOIN cont_tipo_monto_a_depositar AS tm ON tm.id = b.tipo_monto_id
            WHERE b.id = ".$id.' LIMIT 1';
    
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);


            $result['status'] = 200;
            $result['result'] = $resultado[0];
            $result['message'] = 'Se ha registrado exitosamente el beneficiario';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['query'] = $query;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }

    }
    public function registrarExtendido($data) {
        $query = "";
        try {
            
            $query = "INSERT INTO cont_beneficiarios
                    (
                    contrato_id,
                    contrato_detalle_id,
                    tipo_persona_id,
                    tipo_docu_identidad_id,
                    num_docu,
                    nombre,
                    forma_pago_id,
                    tipo_monto_id,
                    banco_id,
                    num_cuenta_bancaria,  
                    user_created_id,
                    created_at)
                    VALUES
                    (
                    " . $data["contrato_id"] . ",
                    '" . $data["contrato_detalle_id"] . "',
                    " . $data["tipo_persona_id"] . ",
                    " . $data["tipo_docu_identidad_id"] . ",
                    '" . $data["num_docu"] . "',
                    '" . $this->replace_invalid_caracters($data["nombre"]). "',
                    " . $data["forma_pago_id"]. ",
                    " . $data["tipo_monto_id"]. ",
                    " . $data["banco_id"]. ",
                    " . $data["num_cuenta_bancaria"]. ",  
                    " . $this->get_usuario_id() . ",
                    '" . $data["created_at"]  . "')";
                
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();



            $query = "SELECT 
                b.id,
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
                b.user_created_id,
                b.created_at,
                tp.nombre AS tipo_persona,
                td.nombre AS tipo_doc_identidad,
                fp.nombre AS forma_pago,
                bc.nombre AS banco,
                tm.nombre AS tipo_monto
            FROM cont_beneficiarios as b
            LEFT JOIN cont_tipo_persona AS tp ON tp.id = b.tipo_persona_id
            LEFT JOIN cont_tipo_docu_identidad AS td ON td.id = b.tipo_docu_identidad_id
            LEFT JOIN cont_forma_pago AS fp ON fp.id = b.forma_pago_id
            LEFT JOIN tbl_bancos AS bc ON bc.id = b.banco_id
            LEFT JOIN cont_tipo_monto_a_depositar AS tm ON tm.id = b.tipo_monto_id
            WHERE b.id = ".$id.' LIMIT 1';
    
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);


            $result['status'] = 200;
            $result['result'] = $resultado[0];
            $result['message'] = 'Se ha registrado exitosamente el beneficiario';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['query'] = $query;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }

    }



    public function modificar($data) {
        try {

            $query = "UPDATE cont_beneficiarios
                        SET
                            tipo_persona_id = " . $data["tipo_persona_id"] . ",
                            tipo_docu_identidad_id = " . $data["tipo_docu_identidad_id"] . ",
                            num_docu = '" . $data["num_docu"] . "',
                            nombre = '" . $this->replace_invalid_caracters($data["nombre"]). "',
                            forma_pago_id = " . $data["forma_pago_id"]. ",
                            banco_id = " . $data["banco_id"]. ",
                            num_cuenta_bancaria = " . $data["num_cuenta_bancaria"]. ",
                            num_cuenta_cci = " . $data["num_cuenta_cci"]. ",
                            tipo_monto_id = " . $data["tipo_monto_id"]. ",
                            monto = " . $data["monto"]. ",
                            user_updated_id =  " . $this->get_usuario_id() . ",
                            updated_at = '" . $data["updated_at"]. "'
                        WHERE id = " . $data["id"];

            $db_query = $this->db->prepare($query);
            $db_query->execute();
      
            $query = "SELECT 
                b.id,
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
                b.user_created_id,
                b.created_at,
                tp.nombre AS tipo_persona,
                td.nombre AS tipo_doc_identidad,
                fp.nombre AS forma_pago,
                bc.nombre AS banco,
                tm.nombre AS tipo_monto
            FROM cont_beneficiarios as b
            LEFT JOIN cont_tipo_persona AS tp ON tp.id = b.tipo_persona_id
            LEFT JOIN cont_tipo_docu_identidad AS td ON td.id = b.tipo_docu_identidad_id
            LEFT JOIN cont_forma_pago AS fp ON fp.id = b.forma_pago_id
            LEFT JOIN tbl_bancos AS bc ON bc.id = b.banco_id
            LEFT JOIN cont_tipo_monto_a_depositar AS tm ON tm.id = b.tipo_monto_id
            WHERE b.id = ".$data["id"].' LIMIT 1';
        
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);


            $result['status'] = 200;
            $result['result'] = $resultado[0];
            $result['message'] = 'Se ha modificado exitosamente el beneficiario';
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

            $query = "UPDATE cont_beneficiarios
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
            $result['message'] = 'Se ha modificado exitosamente el beneficiario';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Beneficiario)';
            return $result;
        }

    }


}