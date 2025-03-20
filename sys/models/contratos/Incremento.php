<?php
class Incremento extends Model {

    private $data;
    private $id;
  
    public function registrar($data) {
        $query = '';
        try {
            
            $query = "INSERT INTO cont_incrementos(
                contrato_id,
                contrato_detalle_id,
                valor,
                tipo_valor_id,
                tipo_continuidad_id,
                a_partir_del_año,
                fecha_cambio,
                estado,
                user_created_id,
                created_at
            )VALUES(
                " . $data['contrato_id'] . ",
                '" . $data["contrato_detalle_id"] . "',
                '" . $data['valor'] . "',
                " . $data["tipo_valor_id"] . ",
                '" . $data["tipo_continuidad_id"] . "',
                '" . $data["a_partir_del_anio_id"] . "',
                " . $data["fecha_cambio"] . ",
                " . $data['estado']. ",
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
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Incremento)';
            return $result;
        }

    }





}