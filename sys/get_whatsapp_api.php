<?php
$_POST = json_decode(file_get_contents('php://input'), true);
$accion = $_POST ['accion'];
if ($accion != null) {
    if ($accion == 'get_proveedores') {
        get_proveedores();
    } else if ($accion == 'get_telefonos_origen') {
        get_telefonos_origen();
    } else if ($accion == 'get_telefonos_destino') {
        get_telefonos_destino();
    } else if ($accion == 'enviar_mensaje') {
        ini_set('memory_limit', '2G');
        ini_set('max_execution_time', '500');
        set_time_limit(0);
        $proveedor = strtoupper($_POST['proveedor']);
        $origen_id = $_POST['origen_id'];
        $destino_ids = json_decode(stripslashes($_POST['destino_ids']));
        $mensaje = $_POST['mensaje'];

        $origen_data = get_origen_data_by_origen_id($origen_id);
        $sid = $origen_data['sid'];
        $token = $origen_data['token'];
        $numero = $origen_data['numero'];

        include("whatsapp_api_helper.php");
        $api = new whatsapp_api_helper($proveedor, $sid, $token, $numero);
        $responses = [];
        foreach ($destino_ids as $destino_id) {
            $destino = get_telefono_destino_by_id($destino_id);
            if($destino) {
                $response = $api->send_message($destino, $mensaje);
                $responses[] = $response;
            }
        }

        json_data_response($responses);
    } else {
        json_data_response(['message' => 'Acción incorrecta'], 400);
    }
} else {
    json_data_response(['message' => 'Acción incorrecta'], 400);
}

function get_proveedores()
{
    try {
        include("db_connect.php");

        $sql_query = 'SELECT p.id, p.nombre FROM tbl_whatsapp_api_proveedor p WHERE p.estado = 1;';

        $stmt = $mysqli->prepare($sql_query);

        if ($mysqli->error) {
            json_data_response(['message' => htmlspecialchars($mysqli->error)], 500);
        }

        $rc = $stmt->execute();

        if (false === $rc) {
            json_data_response(['message' => htmlspecialchars($stmt->error)], 500);
        }

        $stmt_result = $stmt->get_result();

        $data = [];

        while ($row_data = $stmt_result->fetch_assoc()) {
            $data[] = $row_data;
        }
        json_data_response(['data' => $data]);
    } catch (Exception $ex) {
        json_data_response(['message' => htmlspecialchars($ex->getMessage())], $status_code = 200);
    }
}

function get_telefonos_origen()
{
    try {
        include("db_connect.php");

        $sql_query = 'SELECT o.id, o.nombre, o.numero FROM tbl_whatsapp_api_origen o WHERE o.estado = 1;';

        $stmt = $mysqli->prepare($sql_query);

        if ($mysqli->error) {
            json_data_response(['message' => htmlspecialchars($mysqli->error)], 500);
        }

        $rc = $stmt->execute();

        if (false === $rc) {
            json_data_response(['message' => htmlspecialchars($stmt->error)], 500);
        }

        $stmt_result = $stmt->get_result();

        $data = [];

        while ($row_data = $stmt_result->fetch_assoc()) {
            $data[] = $row_data;
        }
        json_data_response(['data' => $data]);
    } catch (Exception $ex) {
        json_data_response(['message' => htmlspecialchars($ex->getMessage())], $status_code = 200);
    }
}

function get_telefonos_destino()
{
    try {
        include("db_connect.php");

        //$sql_query = 'SELECT d.id, d.nombre, d.numero FROM tbl_whatsapp_api_destino d WHERE d.estado = 1;';

        $sql_query = 'SELECT d.id, d.celular as numero FROM tbl_personal_apt d WHERE d.wstp_alert = 1;';

        $stmt = $mysqli->prepare($sql_query);

        if ($mysqli->error) {
            json_data_response(['message' => htmlspecialchars($mysqli->error)], 500);
        }

        $rc = $stmt->execute();

        if (false === $rc) {
            json_data_response(['message' => htmlspecialchars($stmt->error)], 500);
        }

        $stmt_result = $stmt->get_result();

        $data = [];

        while ($row_data = $stmt_result->fetch_assoc()) {
            $data[] = $row_data;
        }
        json_data_response(['data' => $data]);
    } catch (Exception $ex) {
        json_data_response(['message' => htmlspecialchars($ex->getMessage())], $status_code = 200);
    }
}

function get_origen_data_by_origen_id($origen_id)
{
    //$data = [];
    $row = null;
    try {
        include("db_connect.php");

        $sql_query = 'SELECT c.sid, o.numero, t.token FROM tbl_whatsapp_api_origen o INNER JOIN tbl_whatsapp_api_cuenta c ON o.id = c.origen_id INNER JOIN tbl_whatsapp_api_token t ON c.id = t.cuenta_id WHERE o.estado = 1 AND c.estado = 1 AND t.estado  = 1 AND c.id = ? LIMIT 1;';

        $stmt = $mysqli->prepare($sql_query);

        if ($mysqli->error) {
            json_data_response(['message' => htmlspecialchars($mysqli->error)], 500);
        }

        $rc = $stmt->bind_param('i', $origen_id);

        if ($rc === false) {
            json_data_response(['message' => htmlspecialchars($stmt->error)], 500);
        }

        $rc = $stmt->execute();

        if (false === $rc) {
            json_data_response(['message' => htmlspecialchars($stmt->error)], 500);
        }

        $stmt_result = $stmt->get_result();

        $row = $stmt_result->fetch_assoc();

        $stmt_result->data_seek(0);

    } catch (Exception $ex) {
        json_data_response(['message' => htmlspecialchars($ex->getMessage())], 500);
    }
    return $row;
}

function get_telefono_destino_by_id($destino_id) {
    $numero = null;
    try {
        include("db_connect.php");

        $sql_query = 'SELECT d.numero FROM tbl_whatsapp_api_destino d WHERE d.estado = 1 and d.id = ?';

        $stmt = $mysqli->prepare($sql_query);

        if ($mysqli->error) {
            json_data_response(['message' => htmlspecialchars($mysqli->error)], 500);
        }

        $destino_id = intval($destino_id);

        $rc = $stmt->bind_param('i', $destino_id);

        if (false === $rc) {
            json_data_response(['message' => htmlspecialchars($stmt->error)], 500);
        }

        $rc = $stmt->execute();

        if (false === $rc) {
            json_data_response(['message' => htmlspecialchars($stmt->error)], 500);
        }

        $stmt_result = $stmt->get_result();

        if($stmt_result) {
            $object = $stmt_result->fetch_object();
            if($object) {
                $numero = $object->numero;
            }
        }
    } catch (Exception $ex) {
        json_data_response(['message' => htmlspecialchars($ex->getMessage())], $status_code = 500);
    }
    return $numero;
}

function json_data_response($data, $status_code = 200)
{
    $status_message = '200 Ok';
    if ($status_code == 400) {
        $status_message = '400 Bad Request';
    }
    if ($status_code == 500) {
        $status_message = '500 Internal Server Error';
    }
    http_response_code($status_code);
    header($_SERVER['SERVER_PROTOCOL'] . ' ' . $status_message, true, $status_code);
    header('Content-type: application/json');
    echo json_encode($data);
    exit();
}

