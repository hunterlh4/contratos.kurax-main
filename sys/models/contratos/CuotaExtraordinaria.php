<?php
class CuotaExtraordinaria extends Model {

    private $data;
    private $id;
  
    public function registrar($data) {
        $query = '';
        try {
            
            $query = "INSERT INTO cont_cuotas_extraordinarias(
                contrato_id,
                contrato_detalle_id,
                mes,
                multiplicador,
                status,
                user_created_id,
                created_at
            )VALUES(
                " . $data['contrato_id'] . ",
                '" . $data["contrato_detalle_id"] . "',
                '" . $data['mes'] . "',
                '" . $data["multiplicador"] . "',
                " . $data['status']. ",
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
            $result['message'] = 'A ocurrido un error con la consulta sql (Cuota Extraordinaria)';
            return $result;
        }

    }





}