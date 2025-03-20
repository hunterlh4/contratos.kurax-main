<?php
class HerrramientasTIProceso extends Model {

    private $data;
    private $id;
  
    public function obtener_proceso_detalle($data) {
        $query = '';
        try {
            
            $query = "SELECT hpd.id, hpd.columna FROM tbl_herramientas_ti_proceso_detalle AS hpd WHERE hpd.status = 1 AND hpd.proceso_id = ".$data['proceso_id'];
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
            $result['message'] = 'A ocurrido un error';
            return $result;
        }

    }

    public function buscar_reporte($data) {
        $query = '';
        try {
            

            $query_proceso = "SELECT hp.tabla, hp.filtro_fecha FROM tbl_herramientas_ti_proceso AS hp WHERE hp.id = ".$data['proceso_id'];
            $db_query = $this->db->prepare($query_proceso);
            $db_query->execute();
            $resultado_proceso = $db_query->fetch(PDO::FETCH_ASSOC);

            $ids_para_in = implode(',', $data['proceso_detalle_id']);

            $query_proceso_detalle = "SELECT hpd.columna FROM tbl_herramientas_ti_proceso_detalle AS hpd WHERE hpd.status = 1 AND hpd.id IN (".$ids_para_in.")";
            $db_query = $this->db->prepare($query_proceso_detalle);
            $db_query->execute();
            $resultado_proceso_detalle = $db_query->fetchAll(PDO::FETCH_ASSOC);


            
            $columnas = "";
            foreach ($resultado_proceso_detalle as $be) {
                $columnas .= !Empty($columnas) ? ', ':'';
                $columnas .= $be['columna'];
            }
            $query_reporte = "SELECT ".$columnas." FROM ".$resultado_proceso['tabla']." WHERE ".$resultado_proceso['filtro_fecha']." BETWEEN '".$data['fecha_inicio']."' AND '".$data['fecha_fin']."'";

            $db_query = $this->db->prepare($query_reporte);
            $db_query->execute();
            $resultado_data = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $nombres_columnas = array();
            for ($i = 0; $i < $db_query->columnCount(); $i++) {
                $meta = $db_query->getColumnMeta($i);
                $nombres_columnas[] = $meta['name'];
            }

            $result['status'] = 200;
            $result['result'] = ['data' => $resultado_data, 'columnas' => $nombres_columnas];
            $result['message'] = 'Datos obtenido de gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error';
            return $result;
        }

    }


}