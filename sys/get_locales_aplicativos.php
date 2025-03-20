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
        case 'list_locales_aplicativos':
            if (!isset($inputs->local_id) || !is_numeric($inputs->local_id)) {
                $result['error'] = true;
                $result['message'] = 'El parámetro local_id no es valido.';
            } else {
                $local_id = $inputs->local_id;
                $result = fnc_sec_locales_aplicativos__list_locales_aplicativos_by_local_id($local_id);
            }
            break;
        case 'change_habilitado':
            if (!isset($inputs->local_id) || !is_numeric($inputs->local_id)) {
                $result['error'] = true;
                $result['message'] = 'El parámetro local_id no es valido.';
            } else if (!isset($inputs->aplicativo_id) || !is_numeric($inputs->aplicativo_id)) {
                $result['error'] = true;
                $result['message'] = 'El parámetro aplicativo_id no es valido.';
            } else if (!isset($inputs->habilitado) || !($inputs->habilitado == 0 || $inputs->habilitado == 1)) {
                $result['error'] = true;
                $result['message'] = 'El parámetro habilitado no es valido.';
            } else if (!isset($inputs->id)) {
                $result['error'] = true;
                $result['message'] = 'El parámetro id no es valido.';
            } else if (!isset($inputs->comentario)) {
                $result['error'] = true;
                $result['message'] = 'El parámetro comentario no es valido.';
            } else {
                $id = $inputs->id;
                $local_id = $inputs->local_id;
                $aplicativo_id = $inputs->aplicativo_id;
                $habilitado = $inputs->habilitado;
                $comentario = $inputs->comentario;
                $result = fnc_sec_locales_aplicativos__change_habilitado($id, $local_id, $aplicativo_id, $habilitado, $comentario);
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

function fnc_sec_locales_aplicativos__list_locales_aplicativos_by_local_id($local_id): array
{
    global $mysqli;
    $result = [
        'data' => [],
        'error' => false,
        'message' => ''
    ];
    $query_select = "SELECT
    la.id,
    a.id AS aplicativo_id,
    a.producto,
    a.servicio,
    {$local_id} AS local_id,
    IF(
                (
                    SELECT count(*)
                    FROM   tbl_locales_aplicativos AS la2
                    WHERE  la2.local_id = {$local_id} AND la2.habilitado = 1
                      AND la2.aplicativo_id = a.id
                ) = 0,
                '0',
                '1'
        )                                  AS habilitado
FROM tbl_aplicativos AS a
         LEFT JOIN tbl_locales_aplicativos AS la ON la.aplicativo_id = a.id
         LEFT JOIN tbl_locales l ON l.id = la.local_id
WHERE a.estado = 1 ORDER BY habilitado DESC, a.producto;";
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


function fnc_sec_locales_aplicativos__change_habilitado($id, $local_id, $aplicativo_id, $habilitado, $comentario): array
{
    global $login;

    $result = [
        'error' => false,
        'message' => '',
        'data' => []
    ];

    $usuario_id = $login ? $login['id'] : null;

    if ($usuario_id == null) {
        $result['error'] = true;
        $result['message'] = 'Vuelva a loguearse en el sistema.';
        $result['status'] = '401 Unauthorized';
        return $result;
    }

    if ($id) {
        //UPDATE
        $result = fnc_sec_locales_aplicativos__get_locales_aplicativos_by_id($id);
        if (!$result['error']) {
            if (count($result['data'])) {
                $result = fnc_sec_locales_aplicativos__update_locales_aplicativos_habilitado($id, $habilitado);
                fnc_sec_locales_aplicativos__insert_historial_locales_aplicativos($id, 'UPDATE', $usuario_id, $comentario);
            } else {
                $result['error'] = true;
                $result['message'] = "El registro con el Id {$id} no existe.";
            }
        }
    } else {
        //INSERT
        $result = fnc_sec_locales_aplicativos__get_locales_aplicativos_by_local_id_and_aplicativo_id($local_id, $aplicativo_id);
        if (!$result['error']) {
            if ($result['data'][0] == null) {
                $result = fnc_sec_locales_aplicativos__insert_locales_aplicativos($local_id, $aplicativo_id, $habilitado, 1);
                $id = $result['id'];
                fnc_sec_locales_aplicativos__insert_historial_locales_aplicativos($id, 'INSERT', $usuario_id, $comentario);
            } else {
                $result['error'] = true;
                $result['message'] = "Ya existe un registro con el local_id {$local_id} y con el aplicativo_id {$aplicativo_id}.";
            }
        }

    }

    return $result;

}

function fnc_sec_locales_aplicativos__get_locales_aplicativos_by_local_id_and_aplicativo_id(int $local_id, int $aplicativo_id): array
{
    global $mysqli;
    $result = [
        'data' => [],
        'error' => false,
        'message' => ''
    ];
    $query_select = "SELECT
        la.id,
        la.aplicativo_id,
        la.local_id,
        la.habilitado,
        la.estado
        FROM tbl_locales_aplicativos AS la
    WHERE la.estado = 1 AND la.local_id = {$local_id} AND la.aplicativo_id = {$aplicativo_id} LIMIT 1;";
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


function fnc_sec_locales_aplicativos__get_locales_aplicativos_by_id(int $id): array
{
    global $mysqli;
    $result = [
        'data' => [],
        'error' => false,
        'message' => ''
    ];
    $query_select = "SELECT
        la.id,
        la.aplicativo_id,
        la.local_id,
        la.habilitado,
        la.estado
        FROM tbl_locales_aplicativos AS la
    WHERE la.estado = 1 AND la.id = {$id} LIMIT 1;";
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

function fnc_sec_locales_aplicativos__insert_locales_aplicativos(int $local_id, int $aplicativo_id, int $habilitado, int $estado): array
{
    global $mysqli;
    $result = [
        'data' => [],
        'error' => false,
        'message' => 'Registro insertado con éxito.'
    ];
    $query_insert = "INSERT INTO tbl_locales_aplicativos (aplicativo_id, local_id, habilitado, estado, fecha_creacion, fecha_actualizacion) VALUES ({$aplicativo_id}, {$local_id}, {$habilitado}, {$estado}, NOW(), NOW());";
    $mysqli->query($query_insert);
    if ($mysqli->error) {
        $result['message'] = $mysqli->error;
        $result['error'] = true;
    }
    $result['id'] = $mysqli->insert_id;
    return $result;
}

function fnc_sec_locales_aplicativos__update_locales_aplicativos_habilitado(int $id, int $habilitado): array
{
    global $mysqli;
    $result = [
        'data' => [],
        'error' => false,
        'message' => 'Registro actualizado con éxito.'
    ];
    $query_insert = "UPDATE tbl_locales_aplicativos SET habilitado =  {$habilitado} WHERE estado = 1 AND id = {$id};";
    $mysqli->query($query_insert);
    if ($mysqli->error) {
        $result['message'] = $mysqli->error;
        $result['error'] = true;
    }

    return $result;
}

function fnc_sec_locales_aplicativos__insert_historial_locales_aplicativos(int $llocal_aplicativo_id, string $tipo_accion, int $usuario_id, string $comentario): array
{
    global $mysqli;
    $result = [
        'data' => [],
        'error' => false,
        'message' => 'Registro insertado con éxito.'
    ];

    $query_insert = "INSERT INTO tbl_locales_aplicativos_historial (local_aplicativo_id, tipo_accion, usuario_id, comentario, fecha_creacion) VALUES ({$llocal_aplicativo_id}, '{$tipo_accion}', {$usuario_id}, '{$comentario}', NOW());";
    $mysqli->query($query_insert);
    if ($mysqli->error) {
        $result['message'] = $mysqli->error;
        $result['error'] = true;
    }

    return $result;
}


