<?php

require_once '/var/www/html/sys/db_connect.php';
require_once '/var/www/html/sys/sys_login.php';

$result = [
    'error' => false,
    'message' => '',
    'data' => []
];
$inputs = json_decode(json_encode($_POST));
$existe_accion = true;

if (isset($inputs->accion)) {
    switch ($inputs->accion) {
        case 'get_aplicativos':
            $result = fnc_sec_aplicativos__get_aplicativos();
            break;
        case 'insert_or_update_aplicativo':
            if (!isset($inputs->producto) || strlen(trim($inputs->producto)) === 0) {
                $result['error'] = true;
                $result['message'] = 'El parámetro producto no es valido.';
            } else if (!isset($inputs->servicio)) {
                $result['error'] = true;
                $result['message'] = 'El parámetro servicio no es valido.';
            } else if (!isset($inputs->id)) {
                $result['error'] = true;
                $result['message'] = 'El parámetro id no es valido.';
            } else {
                $producto = $inputs->producto;
                $servicio = $inputs->servicio;
                $id = $inputs->id;
                if ($id) {
                    $estado = 1;
                    if (isset($inputs->estado)) {
                        $estado = $inputs->estado;
                    }
                    $result = fnc_sec_aplicativos__update_aplicativo($id, $producto, $servicio, $estado);
                } else {
                    $result = fnc_sec_aplicativos__insert_aplicativo($producto, $servicio);
                }

            }
            break;
        case 'get_aplicativo':
            if (!isset($inputs->id) || !is_numeric($inputs->id)) {
                $result['error'] = true;
                $result['message'] = 'El parámetro id no es valido.';
            } else {
                $id = $inputs->id;
                $result = fnc_sec_aplicativos__get_aplicativo($id);
            }
            break;
        default:
            $existe_accion = false;
            break;
    }
} else {
    $existe_accion = false;
}


if (!$existe_accion) {
    $result['error'] = true;
    $result['message'] = 'Debe especificar una acción.';
}

echo json_encode($result, JSON_UNESCAPED_UNICODE);

function fnc_sec_aplicativos__get_aplicativos() {
    global $mysqli;
    $result = [
        'data' => [],
        'error' => false,
        'message' => 'Ok..'
    ];

    $query_select = "SELECT id, producto, servicio, estado FROM tbl_aplicativos;";
    $query_result = $mysqli->query($query_select);
    if ($mysqli->error) {
        $result['message'] = $mysqli->error;
        $result['error'] = true;
        return $result;
    }

    $data = [];
    while ($row = $query_result->fetch_assoc()) {
        $data[] = $row;
    }
    $result['data'] = $data;
    return $result;
}

function fnc_sec_aplicativos__insert_aplicativo($producto, $servicio): array
{
    global $mysqli;
    $result = [
        'data' => [],
        'error' => false,
        'message' => 'Registro insertado con éxito.'
    ];

    $query_insert = "INSERT INTO tbl_aplicativos (producto, servicio, estado, fecha_creacion, fecha_actualizacion) VALUES ('{$producto}', '{$servicio}', 1, NOW(), NOW());";
    $mysqli->query($query_insert);
    if ($mysqli->error) {
        $result['message'] = $mysqli->error;
        $result['error'] = true;
    }

    return $result;
}

function fnc_sec_aplicativos__get_aplicativo($id): array
{
    global $mysqli;
    $result = [
        'data' => [],
        'error' => false,
        'message' => 'Ok..'
    ];

    $query_select = "SELECT id, producto, servicio FROM tbl_aplicativos WHERE id = {$id} LIMIT 1;";
    $query_result = $mysqli->query($query_select);
    if ($mysqli->error) {
        $result['message'] = $mysqli->error;
        $result['error'] = true;
        return $result;
    }

    $row = $query_result->fetch_assoc();

    $result['data'][] = $row;

    return $result;
}

function fnc_sec_aplicativos__update_aplicativo($id, $producto, $servicio, $estado): array
{
    global $mysqli;
    $result = [
        'data' => [],
        'error' => false,
        'message' => 'Registro actualizado con éxito.'
    ];

    $query_insert = "UPDATE tbl_aplicativos SET producto = '{$producto}', servicio = '{$servicio}', estado = {$estado} WHERE id = {$id};";
    $mysqli->query($query_insert);
    if ($mysqli->error) {
        $result['message'] = $mysqli->error;
        $result['error'] = true;
    }

    return $result;
}
