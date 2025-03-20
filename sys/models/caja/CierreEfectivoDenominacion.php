<?php
class CierreEfectivoDenominacion extends Model {

    private $data;
    private $id;
  

    public function registrar($data) {
        $query = '';
        try {
            
            $query = "INSERT INTO tbl_caja_cierre_efectivo (
                caja_id,
                caja_datos_fisicos_tipo_id,
                monto_boveda,
                monto_efectivo,
                monto_final,
                status,
                user_created_id,
                created_at
            )VALUES(
                " . $data['caja_id'] . ",
                '" . $data['caja_datos_fisicos_tipo_id'] . "',
                '" . $data['monto_boveda'] . "',
                '" . $data['monto_efectivo'] . "',
                '" . $data['monto_final'] . "',
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
            $result['message'] = 'A ocurrido un error con la consulta sql (Cierre Efectivo)';
            return $result;
        }

    }
    
    public function obtener_cierre_denominaciones_por_tipo($tipo_id) {
        $query = '';
        try {
            
            $query = "SELECT 
                    d.id,
                    d.denominacion_tipo_id,
                    d.valor,
                    d.status,
                    t.nombre AS denominacion
                    FROM tbl_caja_cierre_efectivo_denominacion AS d
                    INNER JOIN tbl_caja_cierre_efectivo_denominacion_tipo AS t ON t.id = d.denominacion_tipo_id
                    WHERE d.status = 1
                    AND d.denominacion_tipo_id = ".$tipo_id."
                    ORDER BY d.valor ASC";
     
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





}