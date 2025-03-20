<?php
class ServicioPublico extends Model
{

    private $data;
    private $id;

    public function listar_empresas()
    {
        $query = '';
        try {

            $query = "SELECT s.* FROM cont_local_servicio_publico_empresas AS s ORDER BY s.id ASC";

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $empresas = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $empresas;
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

    public function obtener_empresa_por_id($data)
    {
        $query = '';
        try {

            $query = "SELECT s.* FROM cont_local_servicio_publico_empresas AS s WHERE s.id = " . $data['id'];

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $empresa = $db_query->fetch(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $empresa;
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


    public function modificar_empresa($data)
    {
        $query = '';
        try {

            $query = "UPDATE cont_local_servicio_publico_empresas SET 
                ruc = '" . $data['ruc'] . "',
                razon_social = '" . $data['razon_social'] . "',
                nombre_comercial = '" . $data['nombre_comercial'] . "',
                status = '" . $data['status'] . "',
                user_updated_id = '" . $data['user_updated_id'] . "',
                updated_at = '" . $data['updated_at'] . "'
            WHERE id = " . $data['id'];

            $db_query = $this->db->prepare($query);
            $db_query->execute();

            $result['status'] = 200;
            $result['result'] = $data['id'];
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


    public function registrar_empresa($data)
    {
        $query = '';
        try {

            $query = "INSERT INTO cont_local_servicio_publico_empresas (
                ruc,
                razon_social,
                nombre_comercial,
                status,
                user_created_id,
                created_at
            ) VALUES (
                '" . $data['ruc'] . "',
                '" . $data['razon_social'] . "',
                '" . $data['nombre_comercial'] . "',
                '" . $data['status'] . "',
                '" . $data['user_created_id'] . "',
                '" . $data['created_at'] . "'
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
