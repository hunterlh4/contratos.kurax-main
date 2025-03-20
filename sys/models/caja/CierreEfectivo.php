<?php
class CierreEfectivo extends Model {

    private $data;
    private $id;
  
    public function validar_cierre_efectivo_por_caja($data) {
        $query = '';
        try {
            
            $query = "SELECT cc.*  
                FROM tbl_caja_cierre_efectivo AS cc
                WHERE cc.status = 1 AND cc.caja_id = '".$data['caja_id']."' LIMIT 1";
     
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

            $result['status'] = 200;
            $result['result'] = $data['caja_id'];
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


    public function obtener_por_id($caja_id) {
        $query = '';
        try {
            
            $query = "SELECT ce.caja_id, ce.caja_datos_fisicos_tipo_id, ce.monto_boveda, ce.monto_efectivo, ce.monto_final, ce.status, l.red_id
            FROM tbl_caja_cierre_efectivo  AS ce
            INNER JOIN tbl_caja AS c ON c.id = ce.caja_id 
            INNER JOIN tbl_local_cajas AS lc ON lc.id = c.local_caja_id 
            INNER JOIN tbl_locales AS l ON l.id = lc.local_id 
            WHERE ce.caja_id = '".$caja_id."'";

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $cierre_caja = $db_query->fetchAll(PDO::FETCH_ASSOC);
            $cierre_caja = $cierre_caja[0];

            if (!isset($cierre_caja['caja_id'])) {
                $result['status'] = 404;
                $result['result'] = 0;
                $result['message'] = 'No se encuentra el cierre efectivo en la caja seleccionado';
                return $result;
            }

            $query_det = "SELECT ced.id, ced.caja_id, 
                ced.caja_id,
                ced.moneda_001_cant,
                ced.moneda_002_cant,
                ced.moneda_005_cant,
                ced.moneda_010_cant,
                ced.moneda_020_cant,
                ced.moneda_050_cant,
                ced.billete_10_cant,
                ced.billete_20_cant,
                ced.billete_50_cant,
                ced.billete_100_cant,
                ced.billete_200_cant,
                ced.moneda_001_total,
                ced.moneda_002_total,
                ced.moneda_005_total,
                ced.moneda_010_total,
                ced.moneda_020_total,
                ced.moneda_050_total,
                ced.billete_10_total,
                ced.billete_20_total,
                ced.billete_50_total,
                ced.billete_100_total,
                ced.billete_200_total
            FROM tbl_caja_cierre_efectivo_detalle AS ced
            WHERE ced.status = 1 AND ced.caja_id = '".$caja_id."';";
            $db_query = $this->db->prepare($query_det);
            $db_query->execute();
            $cierre_caja_detalle = $db_query->fetchAll(PDO::FETCH_ASSOC);
            $cierre_caja_detalle = $cierre_caja_detalle[0];

            $cierre_caja['caja_id'] = $cierre_caja['caja_id'];
            $cierre_caja['monto_boveda'] = $cierre_caja['monto_boveda'];
            $cierre_caja['monto_efectivo'] = $cierre_caja['monto_efectivo'];
            $cierre_caja['monto_final'] = $cierre_caja['monto_final'];
            $cierre_caja['red_id'] = $cierre_caja['red_id'];
            

            $cierre_caja['moneda_001_cant'] = $cierre_caja_detalle['moneda_001_cant'];
            $cierre_caja['moneda_002_cant'] = $cierre_caja_detalle['moneda_002_cant'];
            $cierre_caja['moneda_005_cant'] = $cierre_caja_detalle['moneda_005_cant'];
            $cierre_caja['moneda_010_cant'] = $cierre_caja_detalle['moneda_010_cant'];
            $cierre_caja['moneda_020_cant'] = $cierre_caja_detalle['moneda_020_cant'];
            $cierre_caja['moneda_050_cant'] = $cierre_caja_detalle['moneda_050_cant'];
            $cierre_caja['billete_10_cant'] = $cierre_caja_detalle['billete_10_cant'];
            $cierre_caja['billete_20_cant'] = $cierre_caja_detalle['billete_20_cant'];
            $cierre_caja['billete_50_cant'] = $cierre_caja_detalle['billete_50_cant'];
            $cierre_caja['billete_100_cant'] = $cierre_caja_detalle['billete_100_cant'];
            $cierre_caja['billete_200_cant'] = $cierre_caja_detalle['billete_200_cant'];
            $cierre_caja['moneda_001_total'] = $cierre_caja_detalle['moneda_001_total'];
            $cierre_caja['moneda_002_total'] = $cierre_caja_detalle['moneda_002_total'];
            $cierre_caja['moneda_005_total'] = $cierre_caja_detalle['moneda_005_total'];
            $cierre_caja['moneda_010_total'] = $cierre_caja_detalle['moneda_010_total'];
            $cierre_caja['moneda_020_total'] = $cierre_caja_detalle['moneda_020_total'];
            $cierre_caja['moneda_050_total'] = $cierre_caja_detalle['moneda_050_total'];
            $cierre_caja['billete_10_total'] = $cierre_caja_detalle['billete_10_total'];
            $cierre_caja['billete_20_total'] = $cierre_caja_detalle['billete_20_total'];
            $cierre_caja['billete_50_total'] = $cierre_caja_detalle['billete_50_total'];
            $cierre_caja['billete_100_total'] = $cierre_caja_detalle['billete_100_total'];
            $cierre_caja['billete_200_total'] = $cierre_caja_detalle['billete_200_total'];

            $cierre_caja['total_billete'] = $cierre_caja_detalle['billete_10_total'] + $cierre_caja_detalle['billete_20_total'] + $cierre_caja_detalle['billete_50_total'] + $cierre_caja_detalle['billete_100_total'] + $cierre_caja_detalle['billete_200_total'];
            $cierre_caja['total_moneda'] = $cierre_caja_detalle['moneda_001_total'] + $cierre_caja_detalle['moneda_002_total'] + $cierre_caja_detalle['moneda_005_total'] + $cierre_caja_detalle['moneda_010_total'] + $cierre_caja_detalle['moneda_020_total'] + $cierre_caja_detalle['moneda_050_total'];
            
            $cierre_caja['total_billete'] = number_format($cierre_caja['total_billete'],2,'.','');
            $cierre_caja['total_moneda'] = number_format($cierre_caja['total_moneda'],2,'.','');
                                 

            $result['status'] = 200;
            $result['result'] = $cierre_caja;
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

    public function obtener_por_id_eliminado($caja_id) {
        $query = '';
        try {
            
            $query = "SELECT ce.caja_id, ce.caja_datos_fisicos_tipo_id, ce.monto_boveda, ce.monto_efectivo, ce.monto_final, ce.status, l.red_id
            FROM tbl_caja_cierre_efectivo  AS ce
            INNER JOIN tbl_caja_eliminados AS c ON c.id = ce.caja_id 
            INNER JOIN tbl_local_cajas AS lc ON lc.id = c.local_caja_id 
            INNER JOIN tbl_locales AS l ON l.id = lc.local_id 
            WHERE ce.caja_id = '".$caja_id."'";

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $cierre_caja = $db_query->fetchAll(PDO::FETCH_ASSOC);
            $cierre_caja = $cierre_caja[0];

            if (!isset($cierre_caja['caja_id'])) {
                $result['status'] = 404;
                $result['result'] = 0;
                $result['message'] = 'No se encuentra el cierre efectivo en la caja seleccionado';
                return $result;
            }

            $query_det = "SELECT ced.id, ced.caja_id, 
                ced.caja_id,
                ced.moneda_001_cant,
                ced.moneda_002_cant,
                ced.moneda_005_cant,
                ced.moneda_010_cant,
                ced.moneda_020_cant,
                ced.moneda_050_cant,
                ced.billete_10_cant,
                ced.billete_20_cant,
                ced.billete_50_cant,
                ced.billete_100_cant,
                ced.billete_200_cant,
                ced.moneda_001_total,
                ced.moneda_002_total,
                ced.moneda_005_total,
                ced.moneda_010_total,
                ced.moneda_020_total,
                ced.moneda_050_total,
                ced.billete_10_total,
                ced.billete_20_total,
                ced.billete_50_total,
                ced.billete_100_total,
                ced.billete_200_total
            FROM tbl_caja_cierre_efectivo_detalle AS ced
            WHERE ced.status = 1 AND ced.caja_id = '".$caja_id."';";
            $db_query = $this->db->prepare($query_det);
            $db_query->execute();
            $cierre_caja_detalle = $db_query->fetchAll(PDO::FETCH_ASSOC);
            $cierre_caja_detalle = $cierre_caja_detalle[0];

            $cierre_caja['caja_id'] = $cierre_caja['caja_id'];
            $cierre_caja['monto_boveda'] = $cierre_caja['monto_boveda'];
            $cierre_caja['monto_efectivo'] = $cierre_caja['monto_efectivo'];
            $cierre_caja['monto_final'] = $cierre_caja['monto_final'];
            $cierre_caja['red_id'] = $cierre_caja['red_id'];
            

            $cierre_caja['moneda_001_cant'] = $cierre_caja_detalle['moneda_001_cant'];
            $cierre_caja['moneda_002_cant'] = $cierre_caja_detalle['moneda_002_cant'];
            $cierre_caja['moneda_005_cant'] = $cierre_caja_detalle['moneda_005_cant'];
            $cierre_caja['moneda_010_cant'] = $cierre_caja_detalle['moneda_010_cant'];
            $cierre_caja['moneda_020_cant'] = $cierre_caja_detalle['moneda_020_cant'];
            $cierre_caja['moneda_050_cant'] = $cierre_caja_detalle['moneda_050_cant'];
            $cierre_caja['billete_10_cant'] = $cierre_caja_detalle['billete_10_cant'];
            $cierre_caja['billete_20_cant'] = $cierre_caja_detalle['billete_20_cant'];
            $cierre_caja['billete_50_cant'] = $cierre_caja_detalle['billete_50_cant'];
            $cierre_caja['billete_100_cant'] = $cierre_caja_detalle['billete_100_cant'];
            $cierre_caja['billete_200_cant'] = $cierre_caja_detalle['billete_200_cant'];
            $cierre_caja['moneda_001_total'] = $cierre_caja_detalle['moneda_001_total'];
            $cierre_caja['moneda_002_total'] = $cierre_caja_detalle['moneda_002_total'];
            $cierre_caja['moneda_005_total'] = $cierre_caja_detalle['moneda_005_total'];
            $cierre_caja['moneda_010_total'] = $cierre_caja_detalle['moneda_010_total'];
            $cierre_caja['moneda_020_total'] = $cierre_caja_detalle['moneda_020_total'];
            $cierre_caja['moneda_050_total'] = $cierre_caja_detalle['moneda_050_total'];
            $cierre_caja['billete_10_total'] = $cierre_caja_detalle['billete_10_total'];
            $cierre_caja['billete_20_total'] = $cierre_caja_detalle['billete_20_total'];
            $cierre_caja['billete_50_total'] = $cierre_caja_detalle['billete_50_total'];
            $cierre_caja['billete_100_total'] = $cierre_caja_detalle['billete_100_total'];
            $cierre_caja['billete_200_total'] = $cierre_caja_detalle['billete_200_total'];

            $cierre_caja['total_billete'] = $cierre_caja_detalle['billete_10_total'] + $cierre_caja_detalle['billete_20_total'] + $cierre_caja_detalle['billete_50_total'] + $cierre_caja_detalle['billete_100_total'] + $cierre_caja_detalle['billete_200_total'];
            $cierre_caja['total_moneda'] = $cierre_caja_detalle['moneda_001_total'] + $cierre_caja_detalle['moneda_002_total'] + $cierre_caja_detalle['moneda_005_total'] + $cierre_caja_detalle['moneda_010_total'] + $cierre_caja_detalle['moneda_020_total'] + $cierre_caja_detalle['moneda_050_total'];
            
            $cierre_caja['total_billete'] = number_format($cierre_caja['total_billete'],2,'.','');
            $cierre_caja['total_moneda'] = number_format($cierre_caja['total_moneda'],2,'.','');
                                 

            $result['status'] = 200;
            $result['result'] = $cierre_caja;
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

    public function modificar($data) {
        $query = '';
        try {
            $query = "UPDATE tbl_caja_cierre_efectivo SET 
                monto_boveda = '" . $data['monto_boveda'] . "',
                monto_efectivo = '" . $data['monto_efectivo'] . "',
                monto_final = '" . $data['monto_final'] . "',
                updated_at = '" . $data["updated_at"]. "',
                user_updated_id =  " . $data["user_updated_id"]. "
                WHERE caja_id = '" . $data['caja_id'] . "'";
     
            $db_query = $this->db->prepare($query);
            $db_query->execute();

            $result['status'] = 200;
            $result['result'] = $data['caja_id'];
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

    public function modificar_por_vinculacion_caja_eliminada($data) {
        $query = '';
        try {
            $query = "UPDATE tbl_caja_cierre_efectivo SET 
                monto_boveda = '" . $data['monto_boveda'] . "',
                monto_efectivo = '" . $data['monto_efectivo'] . "',
                monto_final = '" . $data['monto_final'] . "',
                vinculacion = '" . $data['vinculacion'] . "',
                updated_at = '" . $data["updated_at"]. "',
                user_updated_id =  " . $data["user_updated_id"]. "
                WHERE caja_id = '" . $data['caja_id'] . "'";
     
            $db_query = $this->db->prepare($query);
            $db_query->execute();

            $result['status'] = 200;
            $result['result'] = $data['caja_id'];
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

    public function obtener_caja_por_id($caja_id) {
        $query = '';
        try {
            
            $query = "SELECT c.id, c.turno_id, c.local_caja_id, c.fecha_operacion 
            FROM tbl_caja AS c
            WHERE c.id = '".$caja_id."'";
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $cierre_caja = $db_query->fetchAll(PDO::FETCH_ASSOC);
            $cierre_caja = $cierre_caja[0];

            if (!isset($cierre_caja['id'])) {
                $result['status'] = 404;
                $result['result'] = 0;
                $result['message'] = 'No se ha encontrado la caja seleccionada';
                return $result;
            }

            $result['status'] = 200;
            $result['result'] = $cierre_caja;
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