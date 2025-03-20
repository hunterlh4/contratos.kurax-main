<?php
class CajaEliminada extends Model {

    private $data;
    private $id;

    public function obtener_caja_eliminada($data) {
        $query = '';
        try {
            
            $query = "SELECT ce.id 
            FROM tbl_caja_eliminados AS ce 
            WHERE ce.turno_id = '".$data['turno_id']."'
            AND ce.local_caja_id = '".$data['local_caja_id']."' 
            AND ce.fecha_operacion = '".$data['fecha_operacion']."'
            ORDER BY ce.fecha_apertura DESC
            LIMIT 1";

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $caja_eliminada = $db_query->fetchAll(PDO::FETCH_ASSOC);
            $caja_eliminada = $caja_eliminada[0];
            if (!isset($caja_eliminada['id'])) {
                $result['status'] = 404;
                $result['result'] = 0;
                $result['message'] = 'No se han encontrado cajas eliminadas';
                return $result;
            }
           
            $result['status'] = 200;
            $result['result'] = $caja_eliminada;
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