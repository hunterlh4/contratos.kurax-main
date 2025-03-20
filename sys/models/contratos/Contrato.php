<?php
class Contrato extends Model
{

    public function guardar_cambio_tipo_contrato($data)
    {
        $query = '';
        try {

            $model_correlativo = new Correlativo();
            $correlativo = $model_correlativo->obtener_correlativo($data['tipo_contrato_nuevo_id']);
            if ($correlativo['status'] != 200) {
                return $correlativo;
            }


            $query = "UPDATE cont_contrato SET 
                tipo_contrato_id = '".$data['tipo_contrato_nuevo_id']."',
                codigo_correlativo = '".$correlativo['result']['numero']."'
                WHERE  contrato_id = ".$data['contrato_id'];
            $db_query = $this->db->prepare($query);
            $db_query->execute();


            $query = " INSERT INTO cont_mantenimiento_cambio_tipo_contrato(
                tipo_contrato_original_id,
                tipo_contrato_nuevo_id,
                contrato_id,
                codigo,
                nro_ticket,
                responsable_id,
                status,
                user_created_id,
                created_at)
                VALUES(
                '" . $data["tipo_contrato_original_id"] . "',
                '" . $data["tipo_contrato_nuevo_id"] . "',
                '" . $data["contrato_id"] . "',
                '" . $data["codigo"] . "',
                '" . $data["nro_ticket"] . "',
                '" . $data["responsable_id"] . "',
                '" . $data["status"] . "',
                '" . $data["user_created_id"] . "',
                '" . $data["created_at"] . "')";

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();

            $result['status'] = 200;
            $result['result'] = (int) $id;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error al registrar el contrato';
            return $result;
        }
    }


    public function listar_cambio_tipo_contrato()
    {
        $query = '';
        try {

            $query = "SELECT tc.id, 
                t1.nombre AS tipo_contrato_original,
                t2.nombre AS tipo_contrato_nuevo,
                CONCAT(cor.sigla,tc.codigo) AS codigo,
                tc.nro_ticket,
                CONCAT(IFNULL(per.nombre,''),' ',IFNULL(per.apellido_paterno,''), ' ', IFNULL(per.apellido_materno,'')) AS responsable,
                CONCAT('') AS estado,
                tc.status,
                tc.created_at
                
                FROM cont_mantenimiento_cambio_tipo_contrato AS tc 
                INNER JOIN cont_tipo_contrato AS t1 ON t1.id = tc.tipo_contrato_original_id 
                INNER JOIN cont_tipo_contrato AS t2 ON t2.id = tc.tipo_contrato_nuevo_id 
                INNER JOIN tbl_usuarios AS us ON us.id = tc.responsable_id 
                INNER JOIN tbl_personal_apt AS per ON per.id = us.personal_id
                INNER JOIN cont_correlativo AS cor ON cor.tipo_contrato = tc.tipo_contrato_nuevo_id
                WHERE tc.status = 1";

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error al registrar el contrato';
            return $result;
        }
    }


    

}
