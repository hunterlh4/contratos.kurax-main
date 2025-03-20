<?php
class Inmueble extends Model {

    private $data;
    private $id;
  
    public function registrar($data) {
        $query = '';
        try {
            
            $query = "INSERT INTO cont_inmueble(
                contrato_id,
                contrato_detalle_id,
                ubigeo_id,
                ubicacion,
                 
                num_partida_registral,
                oficina_registral,
                num_suministro_agua,
                tipo_compromiso_pago_agua,
                monto_o_porcentaje_agua,
                num_suministro_luz,
                tipo_compromiso_pago_luz,
                monto_o_porcentaje_luz,
                tipo_compromiso_pago_arbitrios,
                porcentaje_pago_arbitrios,
                latitud,
                longitud,
                inmueble_destinado,
                observaciones_mueble,
                user_created_id,
                created_at
            )VALUES(
                " . $data['contrato_id'] . ",
                '" . $data['contrato_detalle_id'] . "',
                '" . $data['ubigeo_id'] . "',
                '" . $this->replace_invalid_caracters($data['ubicacion']) . "',
              
                '" . $data["num_partida_registral"] . "',
                '" . $this->replace_invalid_caracters($data['oficina_registral']) . "',
                '" . $data["num_suministro_agua"] . "',
                " . $data["tipo_compromiso_pago_agua"] . ",
                " . $data['monto_o_porcentaje_agua']. ",
                '" . $data["num_suministro_luz"] . "',
                " . $data["tipo_compromiso_pago_luz"] . ",
                " . $data['monto_o_porcentaje_luz'] . ",
                " . $data["tipo_compromiso_pago_arbitrios"] . ",
                " . $data['porcentaje_pago_arbitrios'] . ",
                '" . $data["latitud"] . "',
                '" . $data["longitud"] . "',
                '" . $data["inmueble_destinado"] . "',
                '" . $data["observaciones_mueble"] . "',
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