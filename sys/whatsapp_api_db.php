<?php

class whatsapp_api_db
{
    /**
     * @throws Exception
     */
    public function get_proveedores(): array
    {
        include("db_connect.php");

        $sql_query = 'SELECT p.id, p.nombre FROM tbl_whatsapp_api_proveedor p WHERE p.estado = 1;';

        $stmt = $mysqli->prepare($sql_query);

        if ($mysqli->error) {
            throw new Exception($mysqli->error);
        }

        $rc = $stmt->execute();

        if (false === $rc) {
            throw new Exception($stmt->error);
        }

        $stmt_result = $stmt->get_result();

        $data = [];

        while ($row_data = $stmt_result->fetch_assoc()) {
            $data[] = $row_data;
        }

        return $data;
    }

    /**
     * @throws Exception
     */
    function get_telefonos_origen()
    {
        include("db_connect.php");

        $sql_query = 'SELECT o.id, o.nombre, o.numero FROM tbl_whatsapp_api_origen o WHERE o.estado = 1;';

        $stmt = $mysqli->prepare($sql_query);

        if ($mysqli->error) {
            throw new Exception($mysqli->error);
        }

        $rc = $stmt->execute();

        if (false === $rc) {
            throw new Exception($stmt->error);
        }

        $stmt_result = $stmt->get_result();

        $data = [];

        while ($row_data = $stmt_result->fetch_assoc()) {
            $data[] = $row_data;
        }
        return $data;
    }

    /**
     * @throws Exception
     */
    function get_telefonos_destino()
    {
        include("db_connect.php");

        $sql_query = 'SELECT d.id, d.celular as numero FROM tbl_personal_apt d WHERE d.wstp_alert = 1;';

        $stmt = $mysqli->prepare($sql_query);

        if ($mysqli->error) {
            throw new Exception($mysqli->error);
        }

        $rc = $stmt->execute();

        if (false === $rc) {
            throw new Exception($stmt->error);
        }

        $stmt_result = $stmt->get_result();

        $data = [];

        while ($row_data = $stmt_result->fetch_assoc()) {
            $data[] = $row_data;
        }

        return $data;
    }

    /**
     * @throws Exception
     */
    function get_origen_data_by_origen_id($origen_id)
    {

        include("db_connect.php");

        $sql_query = 'SELECT c.sid, o.numero, t.token FROM tbl_whatsapp_api_origen o INNER JOIN tbl_whatsapp_api_cuenta c ON o.id = c.origen_id INNER JOIN tbl_whatsapp_api_token t ON c.id = t.cuenta_id WHERE o.estado = 1 AND c.estado = 1 AND t.estado  = 1 AND c.id = ? LIMIT 1;';

        $stmt = $mysqli->prepare($sql_query);

        if ($mysqli->error) {
            throw new Exception($mysqli->error);
        }

        $rc = $stmt->bind_param('i', $origen_id);

        if ($rc === false) {
            throw new Exception($stmt->error);
        }

        $rc = $stmt->execute();

        if (false === $rc) {
            throw new Exception($stmt->error);
        }

        $stmt_result = $stmt->get_result();

        $first = $stmt_result->fetch_assoc();

        $stmt_result->data_seek(0);

        return $first;
    }

    function get_origen_data_by_numero_telefono($numero)
    {

        include("db_connect.php");

        $sql_query = 'SELECT c.id, c.sid, o.numero, t.token FROM tbl_whatsapp_api_origen o INNER JOIN tbl_whatsapp_api_cuenta c ON o.id = c.origen_id INNER JOIN tbl_whatsapp_api_token t ON c.id = t.cuenta_id WHERE o.estado = 1 AND c.estado = 1 AND t.estado  = 1 AND o.numero = ? LIMIT 1;';

        $stmt = $mysqli->prepare($sql_query);

        if ($mysqli->error) {
            throw new Exception($mysqli->error);
        }

        $rc = $stmt->bind_param('i', $numero);

        if ($rc === false) {
            throw new Exception($stmt->error);
        }

        $rc = $stmt->execute();

        if (false === $rc) {
            throw new Exception($stmt->error);
        }

        $stmt_result = $stmt->get_result();

        $first = $stmt_result->fetch_assoc();

        $stmt_result->data_seek(0);

        return $first;
    }

    function get_origen_data_by_numero_telefono_y_nombre_proveedor($numero, $proveedor)
    {

        include("db_connect.php");

        $sql_query = 'SELECT c.id, c.sid, o.numero, t.token  FROM tbl_whatsapp_api_origen o INNER JOIN tbl_whatsapp_api_cuenta c ON o.id = c.origen_id INNER JOIN tbl_whatsapp_api_proveedor p ON p.id = c.proveedor_id INNER JOIN tbl_whatsapp_api_token t ON c.id = t.cuenta_id  WHERE o.estado = 1 AND c.estado = 1 AND t.estado  = 1 AND o.numero = ? AND p.nombre = ? LIMIT 1;';

        $stmt = $mysqli->prepare($sql_query);

        if ($mysqli->error) {
            throw new Exception($mysqli->error);
        }

        $rc = $stmt->bind_param('is', $numero, $proveedor);

        if ($rc === false) {
            throw new Exception($stmt->error);
        }

        $rc = $stmt->execute();

        if (false === $rc) {
            throw new Exception($stmt->error);
        }

        $stmt_result = $stmt->get_result();

        $first = $stmt_result->fetch_assoc();

        $stmt_result->data_seek(0);

        return $first;
    }

    /**
     * @throws Exception
     */
    function get_telefono_destino_by_id($destino_id)
    {
        $numero = null;

        include("db_connect.php");

        $sql_query = 'SELECT d.numero FROM tbl_whatsapp_api_destino d WHERE d.estado = 1 and d.id = ?';

        $stmt = $mysqli->prepare($sql_query);

        if ($mysqli->error) {
            throw new Exception($mysqli->error);
        }

        $destino_id = intval($destino_id);

        $rc = $stmt->bind_param('i', $destino_id);

        if (false === $rc) {
            throw new Exception($stmt->error);
        }

        $rc = $stmt->execute();

        if (false === $rc) {
            throw new Exception($stmt->error);
        }

        $stmt_result = $stmt->get_result();

        if ($stmt_result) {
            $object = $stmt_result->fetch_object();
            if ($object) {
                $numero = $object->numero;
            }
        }

        return $numero;
    }

}