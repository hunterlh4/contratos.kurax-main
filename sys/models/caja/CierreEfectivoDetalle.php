<?php
class CierreEfectivoDetalle extends Model {

    private $data;
    private $id;
  
    public function registrar($data) {
        $query = '';
        try {
            
            $query = "INSERT INTO tbl_caja_cierre_efectivo_detalle (
	            caja_id,
	            moneda_001_cant,
	            moneda_002_cant,
	            moneda_005_cant,
	            moneda_010_cant,
	            moneda_020_cant,
	            moneda_050_cant,
	            billete_10_cant,
	            billete_20_cant,
	            billete_50_cant,
	            billete_100_cant,
	            billete_200_cant,
	            moneda_001_total,
	            moneda_002_total,
	            moneda_005_total,
	            moneda_010_total,
	            moneda_020_total,
	            moneda_050_total,
	            billete_10_total,
	            billete_20_total,
	            billete_50_total,
	            billete_100_total,
	            billete_200_total,
	            status
            )VALUES(
                " . $data['caja_id'] . ",
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                '0',
                " . $data['status']. "
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


    public function modificar($data) {
        $query = '';
        try {
            
            $query = "UPDATE tbl_caja_cierre_efectivo_detalle SET
                moneda_001_cant = '".$data['moneda_001_cant']."',
                moneda_002_cant = '".$data['moneda_002_cant']."',
                moneda_005_cant = '".$data['moneda_005_cant']."',
                moneda_010_cant = '".$data['moneda_010_cant']."',
                moneda_020_cant = '".$data['moneda_020_cant']."',
                moneda_050_cant = '".$data['moneda_050_cant']."',
                billete_10_cant = '".$data['billete_10_cant']."',
                billete_20_cant = '".$data['billete_20_cant']."',
                billete_50_cant = '".$data['billete_50_cant']."',
                billete_100_cant = '".$data['billete_100_cant']."',
                billete_200_cant = '".$data['billete_200_cant']."',
                moneda_001_total = '".$data['moneda_001_total']."',
                moneda_002_total = '".$data['moneda_002_total']."',
                moneda_005_total = '".$data['moneda_005_total']."',
                moneda_010_total = '".$data['moneda_010_total']."',
                moneda_020_total = '".$data['moneda_020_total']."',
                moneda_050_total = '".$data['moneda_050_total']."',
                billete_10_total = '".$data['billete_10_total']."',
                billete_20_total = '".$data['billete_20_total']."',
                billete_50_total = '".$data['billete_50_total']."',
                billete_100_total = '".$data['billete_100_total']."',
                billete_200_total = '".$data['billete_200_total']."'
                WHERE caja_id = '".$data['caja_id']."'";
     
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