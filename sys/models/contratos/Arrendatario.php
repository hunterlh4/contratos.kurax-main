<?php
class Arrendatario extends Model
{

    private $data;
    private $id;

    public function registrar_arrendatario($data)
    {
        try {
            $query = "INSERT INTO tbl_razon_social (
                nombre,
                status,
                nro_partida_registral,
                tipo_docu_identidad_id,
                num_docu,
                num_ruc,
                direccion,
                representante_legal,
                created_at,
                user_created_id
            ) VALUES (
                '" . $data['nombre'] . "',
                1,
                '" . $data['num_partida_registral'] . "',
                " . $data['tipo_docu_identidad_id'] . ",
                '" . $data['num_docu'] . "',
                '" . $data['num_ruc'] . "',
                '" . $data['direccion'] . "',
                '" . $data['representante_legal'] . "',
                '" . date('Y-m-d H:i:s') . "',
                " . $this->get_usuario_id() . "
            )";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $id = $this->db->lastInsertId();

            return [
                'status' => 200,
                'result' => ['id' => $id],
                'message' => 'Arrendatario registrado exitosamente.'
            ];
        } catch (\Throwable $th) {
            return [
                'status' => 404,
                'message' => 'Error al registrar el arrendatario.'
            ];
        }
    }


    public function existeArrendatario($num_docu, $num_ruc)
    {

        $query = "SELECT COUNT(*) as count FROM tbl_razon_social WHERE num_docu = '" . $num_docu . "' OR num_ruc = '" . $num_ruc . "';";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$num_docu, $num_ruc]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result['count'] > 0;
    }

    // $query = "SELECT c.contrato_id, p.id AS persona_id, pr.propietario_id
    // FROM cont_contrato AS c
    // INNER JOIN cont_propietario pr ON pr.contrato_id = c.contrato_id
    // INNER JOIN cont_persona p ON pr.persona_id = p.id
    // WHERE c.status = 1 AND p.id = ".$persona_id;


    public function validar_personal_antiguo($data, $tipo = 'Registro')
    {
        try {
            $query = "";
            if ($tipo == "Registro") {
                if ($data['tipo_docu_identidad_id'] == 2) { //RUC
                    $query = "SELECT 
                    id, 
                    tipo_persona_id, 
                    nombre, 
                    tipo_docu_identidad_id, 
                    num_docu, 
                    num_ruc,
                    direccion, 
                    representante_legal, 
                    num_partida_registral, 
                    contacto_nombre, 
                    contacto_telefono, 
                    contacto_email 
                    FROM cont_persona WHERE num_ruc like '" . $data["num_ruc"] . "' AND status = 1";
                } else { //DNI, PASAPORTE, CARNET EXTRANJERIA
                    $query = "SELECT 
                    id, 
                    tipo_persona_id, 
                    nombre, 
                    tipo_docu_identidad_id, 
                    num_docu, 
                    num_ruc,
                    direccion, 
                    representante_legal, 
                    num_partida_registral, 
                    contacto_nombre, 
                    contacto_telefono, 
                    contacto_email 
                    FROM cont_persona WHERE num_docu like '" . $data["num_docu"] . "' AND status = 1";
                }
            } else {
                if ($data['tipo_docu_identidad_id'] == 2) { //RUC
                    $query = "SELECT 
                    id, 
                    tipo_persona_id, 
                    nombre, 
                    tipo_docu_identidad_id, 
                    num_docu, 
                    num_ruc,
                    direccion, 
                    representante_legal, 
                    num_partida_registral, 
                    contacto_nombre, 
                    contacto_telefono, 
                    contacto_email 
                    FROM cont_persona WHERE id NOT IN (" . $data["id"] . ") AND num_ruc like '" . $data["num_ruc"] . "' AND status = 1";
                } else { //DNI, PASAPORTE, CARNET EXTRANJERIA
                    $query = "SELECT 
                    id, 
                    tipo_persona_id, 
                    nombre, 
                    tipo_docu_identidad_id, 
                    num_docu, 
                    num_ruc,
                    direccion, 
                    representante_legal, 
                    num_partida_registral, 
                    contacto_nombre, 
                    contacto_telefono, 
                    contacto_email 
                    FROM cont_persona WHERE id NOT IN (" . $data["id"] . ") AND num_docu like '" . $data["num_docu"] . "' AND status = 1";
                }
            }


            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->rowCount();
            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function validar_personal($data, $tipo = 'Registro')
    {
        try {
            $query = "";
            if ($tipo == "Registro") {
                if ($data['tipo_docu_identidad_id'] == 2) { // RUC
                    $query = "SELECT 
                    id, 
                    nombre, 
                    tipo_docu_identidad_id, 
                    num_docu, 
                    num_ruc,
                    direccion, 
                    representante_legal, 
                    nro_partida_registral
                FROM tbl_razon_social 
                WHERE num_ruc LIKE :num_ruc AND status = 1";
                } else { // DNI, PASAPORTE, CARNET EXTRANJERÍA
                    $query = "SELECT 
                    id, 
                    nombre, 
                    tipo_docu_identidad_id, 
                    num_docu, 
                    num_ruc,
                    direccion, 
                    representante_legal, 
                    nro_partida_registral
                FROM tbl_razon_social 
                WHERE num_docu LIKE :num_docu AND status = 1";
                }
            } else { // Validación en Edición
                if ($data['tipo_docu_identidad_id'] == 2) { // RUC
                    $query = "SELECT 
                    id, 
                    nombre, 
                    tipo_docu_identidad_id, 
                    num_docu, 
                    num_ruc,
                    direccion, 
                    representante_legal, 
                    nro_partida_registral
                FROM tbl_razon_social 
                WHERE id NOT IN (:id) AND num_ruc LIKE :num_ruc AND status = 1";
                } else { // DNI, PASAPORTE, CARNET EXTRANJERÍA
                    $query = "SELECT 
                    id, 
                    nombre, 
                    tipo_docu_identidad_id, 
                    num_docu, 
                    num_ruc,
                    direccion, 
                    representante_legal, 
                    nro_partida_registral
                FROM tbl_razon_social 
                WHERE id NOT IN (:id) AND num_docu LIKE :num_docu AND status = 1";
                }
            }

            $stmt = $this->db->prepare($query);
            if ($data['tipo_docu_identidad_id'] == 2) {
                $stmt->bindParam(':num_ruc', $data["num_ruc"]);
            } else {
                $stmt->bindParam(':num_docu', $data["num_docu"]);
            }
            if ($tipo !== "Registro") {
                $stmt->bindParam(':id', $data["id"]);
            }

            $stmt->execute();
            $resultado = $stmt->rowCount();

            return [
                'status' => 200,
                'result' => $resultado,
                'message' => 'Datos obtenidos de Gestión'
            ];
        } catch (\Throwable $th) {
            return [
                'status' => 404,
                'result' => $th->getMessage(),
                'message' => 'Ha ocurrido un error con la consulta SQL'
            ];
        }
    }

    public function registrar_personal($data)
    {
        try {

            $query = "INSERT INTO cont_persona
                    (
                    tipo_persona_id,
                    tipo_docu_identidad_id,
                    num_docu,
                    num_ruc,
                    nombre,
                    direccion,
                    representante_legal,
                    num_partida_registral,
                    contacto_nombre,
                    contacto_telefono,
                    contacto_email,
                    user_created_id,
                    created_at)
                    VALUES
                    (
                    " . $data["tipo_persona_id"] . ",
                    " . $data["tipo_docu_identidad_id"] . ",
                    '" . $data["num_docu"] . "',
                    '" . $data["num_ruc"] . "',
                    '" . $this->replace_invalid_caracters($data["nombre"]) . "',
                    '" . $this->replace_invalid_caracters($data["direccion"]) . "',
                    '" . $this->replace_invalid_caracters($data["representante_legal"]) . "',
                    '" . $this->replace_invalid_caracters($data["num_partida_registral"]) . "',
                    '" . $this->replace_invalid_caracters($data["contacto_nombre"]) . "',
                    '" . $data["contacto_telefono"] . "',
                    '" . $data["contacto_email"] . "',
                    " . $this->get_usuario_id() . ",
                    '" . date('Y-m-d H:i:s') . "')";

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();



            $query = "SELECT 
                id, 
                tipo_persona_id, 
                nombre, 
                tipo_docu_identidad_id, 
                num_docu, 
                num_ruc,
                direccion, 
                representante_legal, 
                num_partida_registral, 
                contacto_nombre, 
                contacto_telefono, 
                contacto_email
            FROM cont_persona WHERE id = " . $id . ' LIMIT 1';

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);


            $result['status'] = 200;
            $result['result'] = $resultado[0];
            $result['message'] = 'Se ha registrado exitosamente el propietario';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function validar_arrendatario_modificar($persona_id)
    {
        // try {

        //     $query = "SELECT c.contrato_id, p.id AS persona_id, pr.propietario_id
        //     FROM cont_contrato AS c
        //     INNER JOIN cont_propietario pr ON pr.contrato_id = c.contrato_id
        //     INNER JOIN cont_persona p ON pr.persona_id = p.id
        //     WHERE c.status = 1 AND p.id = " . $persona_id;

        //     $db_query = $this->db->prepare($query);
        //     $db_query->execute();
        //     $resultado = $db_query->rowCount();
        //     $result['status'] = 200;
        //     $result['result'] = $resultado;
        //     $result['message'] = 'Datos Obtenidos de Gestion';
        //     return $result;
        // } catch (\Throwable $th) {
        //     $result['status'] = 404;
        //     $result['result'] = $th->getMessage();
        //     $result['message'] = 'A ocurrido un error con la consulta sql';
        //     return $result;
        // }

        try {

            $query = "SELECT c.contrato_id, p.id AS persona_id, pr.propietario_id
            FROM cont_contrato AS c
            INNER JOIN cont_propietario pr ON pr.contrato_id = c.contrato_id
            INNER JOIN cont_persona p ON pr.persona_id = p.id
            WHERE c.status = 1 AND p.id = " . $persona_id;

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->rowCount();
            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos Obtenidos de Gestion';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function modificar_personal_antiguo($data)
    {
        try {



            $query = "UPDATE cont_persona
                    SET
                    tipo_persona_id = " . $data["tipo_persona_id"] . ",
                    tipo_docu_identidad_id =  " . $data["tipo_docu_identidad_id"] . ",
                    num_docu = '" . $data["num_docu"] . "',
                    num_ruc = '" . $data["num_ruc"] . "',
                    nombre = '" . $this->replace_invalid_caracters($data["nombre"]) . "',
                    direccion = '" . $this->replace_invalid_caracters($data["direccion"]) . "',
                    representante_legal = '" . $this->replace_invalid_caracters($data["representante_legal"]) . "',
                    num_partida_registral = '" . $this->replace_invalid_caracters($data["num_partida_registral"]) . "',
                    contacto_nombre = '" . $this->replace_invalid_caracters($data["contacto_nombre"]) . "',
                    contacto_telefono = '" . $data["contacto_telefono"] . "',
                    contacto_email = '" . $data["contacto_email"] . "',
                    user_created_id = " . $this->get_usuario_id() . ",
                    updated_at = '" . date('Y-m-d H:i:s') . "'
                    WHERE id = " . $data["id"];

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $data["id"];

            $query = "SELECT 
                id, 
                tipo_persona_id, 
                nombre, 
                tipo_docu_identidad_id, 
                num_docu, 
                num_ruc,
                direccion, 
                representante_legal, 
                num_partida_registral, 
                contacto_nombre, 
                contacto_telefono, 
                contacto_email,
                CONCAT('') AS tipo_persona_contacto
            FROM cont_persona WHERE id = " . $id . ' LIMIT 1';

            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);


            $result['status'] = 200;
            $result['result'] = $resultado[0];
            $result['message'] = 'Se ha modificado exitosamente el arrendatario';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error con la consulta sql';
            return $result;
        }
    }

    public function modificar_personal($data)
    {
        try {
            $query = "UPDATE tbl_razon_social
                SET
                tipo_docu_identidad_id = :tipo_docu_identidad_id,
                num_docu = :num_docu,
                num_ruc = :num_ruc,
                nombre = :nombre,
                direccion = :direccion,
                representante_legal = :representante_legal,
                nro_partida_registral = :nro_partida_registral,
                user_created_id = :user_created_id,
                updated_at = :updated_at
                WHERE id = :id";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':tipo_docu_identidad_id', $data["tipo_docu_identidad_id"]);
            $stmt->bindParam(':num_docu', $data["num_docu"]);
            $stmt->bindParam(':num_ruc', $data["num_ruc"]);
            $stmt->bindParam(':nombre', $this->replace_invalid_caracters($data["nombre"]));
            $stmt->bindParam(':direccion', $this->replace_invalid_caracters($data["direccion"]));
            $stmt->bindParam(':representante_legal', $this->replace_invalid_caracters($data["representante_legal"]));
            $stmt->bindParam(':nro_partida_registral', $this->replace_invalid_caracters($data["num_partida_registral"]));
            $stmt->bindParam(':user_created_id', $this->get_usuario_id());
            $stmt->bindParam(':updated_at', date('Y-m-d H:i:s'));
            $stmt->bindParam(':id', $data["id"]);

            $stmt->execute();

            // Obtener los datos modificados
            $query = "SELECT 
            id, 
            nombre, 
            tipo_docu_identidad_id, 
            num_docu, 
            num_ruc,
            direccion, 
            representante_legal, 
            nro_partida_registral
        FROM tbl_razon_social 
        WHERE id = :id LIMIT 1";

            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $data["id"]);
            $stmt->execute();
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

            return [
                'status' => 200,
                'result' => $resultado,
                'message' => 'Se ha modificado exitosamente el arrendatario'
            ];
        } catch (\Throwable $th) {
            return [
                'status' => 404,
                'result' => $th->getMessage(),
                'message' => 'Ha ocurrido un error con la consulta SQL'
            ];
        }
    }

    public function registrar_propietario($data)
    {
        $query = '';
        try {

            $query = "INSERT INTO cont_propietario(
                        contrato_id,
                        persona_id,
                        user_created_id
                    ) VALUES (
                    " . $data["contrato_id"] . ",
                    " . $data["persona_id"] . ",
                    '" . $data["user_created_id"] . "')";
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $id = $this->db->lastInsertId();

            $result['status'] = 200;
            $result['result'] = $id;
            $result['message'] = 'Se ha registrado exitosamente el propietario';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Propietario)';
            return $result;
        }
    }

    public function obtener_propietarios($contrato_id)
    {
        $query = '';
        try {

            $query = "SELECT 
                pe.id, 
                pe.tipo_persona_id, 
                pe.nombre, 
                pe.tipo_docu_identidad_id, 
                pe.num_docu, 
                pe.num_ruc,
                pe.direccion, 
                pe.representante_legal, 
                pe.num_partida_registral, 
                pe.contacto_nombre, 
                pe.contacto_telefono, 
                pe.contacto_email
            FROM cont_propietario AS pr
            INNER JOIN cont_persona AS pe ON pe.id = pr.persona_id
            WHERE pr.status = 1 AND pe.status = 1
            AND pr.contrato_id = " . $contrato_id;
            $db_query = $this->db->prepare($query);
            $db_query->execute();
            $resultado = $db_query->fetchAll(PDO::FETCH_ASSOC);

            $result['status'] = 200;
            $result['result'] = $resultado;
            $result['message'] = 'Datos obtenidos de Gestión';
            return $result;
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['query'] = $query;
            $result['message'] = 'A ocurrido un error con la consulta sql (Propietario)';
            return $result;
        }
    }
}
