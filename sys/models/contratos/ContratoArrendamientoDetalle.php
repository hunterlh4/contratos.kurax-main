<?php
class ContratoArrendamientoDetalle extends Model
{

    public function registrar($data)
    {
        $query = '';
        try {
            $query = " INSERT INTO cont_contrato_detalle(
                contrato_id,
                codigo,
                observaciones,
                status,
                user_created_id,
                created_at)
                VALUES(
                " . $data["contrato_id"] . ",
                '" . $data["codigo"] . "',
                '" . $data["observaciones"] . "',
                " . $data["status"] . ",
                " . $data["user_created_id"] . ",
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

    public function update_codigo($contrato_id)
    {
        $query = '';
        try {

            //codigo
            $query = "SELECT id FROM cont_contrato_detalle WHERE status = 1 AND contrato_id = ".$contrato_id." ORDER BY id ASC";
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $contrato_detalle = $db_query->fetchAll(PDO::FETCH_ASSOC);
            $numero = 1;
            foreach ($contrato_detalle as $cont) {
                $query = "UPDATE cont_contrato_detalle SET codigo = '".str_pad($numero, 3, "0", STR_PAD_LEFT)."' WHERE id =". $cont['id'];
                $db_query = $this->db->prepare($query);
                $db_query->execute();
                $numero++;
            }
            //incremento e inflacion



            $result['status'] = 200;
            $result['result'] = $contrato_id;
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
