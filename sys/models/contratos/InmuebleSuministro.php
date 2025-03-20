<?php
class InmuebleSuministro extends Model {

    private $data;
    private $id;
  
    public function registrar($data) {
        $query = '';
        try {
            
            $query = "INSERT INTO cont_inmueble_suministros(
                contrato_id,
                inmueble_id,
                tipo_servicio_id,
                nro_suministro,
                tipo_compromiso_pago_id,
                monto_o_porcentaje,
                tipo_documento_beneficiario,
                nro_documento_beneficiario,
                nombre_beneficiario,
                nro_cuenta_soles,
                status,
                user_created_id,
                created_at
            )VALUES(
                " . $data['contrato_id'] . ",
                '" . $data['inmueble_id'] . "',
                " . $data["tipo_servicio_id"] . ",
                '" . $data["nro_suministro"] . "',
                '" . $data["tipo_compromiso_pago_id"] . "',
                " . $data['monto_o_porcentaje']. ",
                '" . $data["tipo_documento_beneficiario"] . "',
                '" . $data["nro_documento_beneficiario"] . "',
                '" . $data["nombre_beneficiario"] . "',
                '" . $data["nro_cuenta_soles"] . "',
                '" . $data["status"] . "',
                " . $data["user_created_id"]. ",
                '" . $data["created_at"]. "'
			)";
     
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();

            $result['status'] = 200;
            $result['result'] = $id;
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['query'] = $query;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql (Inmueble)';
            return $result;
        }

    }





}