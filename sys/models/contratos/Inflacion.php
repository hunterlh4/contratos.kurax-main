<?php
class Inflacion extends Model {

    private $data;
    private $id;
  
    public function registrar($data) {
        $query = '';
        try {
            
            $query = "INSERT INTO cont_inflaciones(
                contrato_id,
                contrato_detalle_id,
                fecha,
                tipo_periodicidad_id,
                numero,
                tipo_anio_mes,
                moneda_id,
                porcentaje_anadido,
                tope_inflacion,
                minimo_inflacion,
                tipo_aplicacion_id,
                status,
                user_created_id,
                created_at
            )VALUES(
                " . $data['contrato_id'] . ",
                '" . $data["contrato_detalle_id"] . "',
                " . $data['fecha'] . ",
                '" . $data["tipo_periodicidad_id"] . "',
                '" . $data["numero"] . "',
                '" . $data["tipo_anio_mes"] . "',
                '" . $data["moneda_id"] . "',
                '" . $data["porcentaje_anadido"] . "',
                '" . $data["tope_inflacion"] . "',
                '" . $data["minimo_inflacion"] . "',
                '" . $data["tipo_aplicacion_id"] . "',
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
            $result['message'] = 'A ocurrido un error con la consulta sql (Inflación)';
            return $result;
        }

    }





}