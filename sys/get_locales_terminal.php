<?php
include("db_connect.php");
include("sys_login.php");
require_once('/var/www/html/sys/helpers.php');
require_once('/var/www/html/sys/curl_helper.php');
require_once('/var/www/html/env.php');

$result = [];
if (isset($_POST['accion'])) {
    $inputs = json_decode(json_encode($_POST));
    switch ($_POST['accion']) {
        case 'guardar_local_terminal':
            $result = fnc_save_local_terminal($inputs);
            break;
        case 'actualizar_local_terminal':
            $result = fnc_modify_local_terminal($inputs);
            break;
        case 'list_terminal':
            $result = fnc_list_terminal_by_local($inputs);
            break;
        case 'update_estado_local_terminal':
            $result = fnc_update_estado_local_terminal($inputs);
            break;
        case 'generate_token_auto_servicio':
            $result = fnc_update_token_terminal($inputs);
            break;
        case 'listar_proveedores_by_terminal':
            $result = fnc_list_proveedores_by_terminal($inputs);
            break;
        case 'update_proveedores_by_terminal':
            $result = fnc_update_proveedores_by_terminal($inputs);
            break;
        case 'mac_requerida':
            $result = fnc_mac_requerida($inputs);
            break;
        case 'get_proveedores_api_habilitada':
            $result = fnc_get_proveedores_api_habilitada();
            break;
        case 'get_entity_parent_id_by_servicio_id_and_local_id':
            $result = fnc_get_entity_parent_id_by_servicio_id_and_local_id($inputs);
            break;
        case 'get_entity_parent_id_by_canal_de_venta_id_and_local_id':
            $result = fnc_get_entity_parent_id_by_canal_de_venta_id_and_local_id($inputs);
            break;
        case 'get_entities_by_entity_parent_id':
            $result = fnc_get_entities_by_entity_parent_id($inputs);
            break;
        case 'get_entities_by_servicio_id_and_local_id':
            $result = get_entities_by_servicio_id_and_local_id($inputs);
            break;
        case 'get_entities_by_canal_de_venta_id_and_local_id':
            $result = get_entities_by_canal_de_venta_id_and_local_id($inputs);
            break;
        case 'get_canal_de_venta_id_by_servicio_id':
            $result = fnc_get_canal_de_venta_id_by_servicio_id($inputs);
            break;
        default:
            # code...
            break;
    }
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
}

function fnc_save_local_terminal($inputs): array
{
    $data_result = [];
    $data_result["error"] = true;
    $data_result["http_code"] = 400;

    if (!isset($inputs->name) || $inputs->name === '') {
        $data_result["message"] = 'Falta el parámetro nombre_terminal.';
        return $data_result;
    }

    if (!isset($inputs->local_id) || $inputs->local_id === '') {
        $data_result["message"] = 'Falta el parámetro local_id.';
        return $data_result;
    }

    if (!isset($inputs->user_id_created) || $inputs->user_id_created === '') {
        $data_result["message"] = 'Falta el parámetro user_id_created.';
        return $data_result;
    }

    $nombre_local = fnc_get_nombre_local_by_local_id($inputs->local_id);

    $inputs->local_name = $nombre_local;

    return fnc_insert_local_terminal($inputs);
}

function fnc_modify_local_terminal($inputs): array
{
    $data_result = [];
    $data_result["http_code"] = 400;
    $data_result["error"] = true;

    if (!isset($inputs->name) || $inputs->name == '') {
        $data_result["message"] = 'Falta el parámetro nombre_terminal.';
        return $data_result;
    }

    if (!isset($inputs->id) || $inputs->id == '') {
        $data_result["message"] = 'Falta el parámetro id.';
        return $data_result;
    }

    return fnc_update_local_terminal($inputs);
}

function fnc_list_terminal_by_local($inputs): array
{
    $data_result = [];

    if (!isset($inputs->local_id) || $inputs->local_id == '') {
        $data_result["error"] = true;
        $data_result["http_code"] = 400;
        $data_result["message"] = 'Falta el parámetro local_id.';
        return $data_result;
    }

    return fnc_list_terminal_by_service($inputs);
}

function update_require_mac($inputs): array
{
    $url = env('TERMINALES_URL') . 'terminals/' . $inputs->id;
    $auth_token = env('TERMINALES_AUTH_TOKEN');
    $headers = [
        'Content-type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $auth_token
    ];
    $request = [
        'id' => $inputs->id,
        'require_mac' => $inputs->require_mac
    ];
    return curl_helper($url, $request, $headers, true, 'PUT');
}

function fnc_insert_local_terminal($inputs): array
{
    $url = env('TERMINALES_URL') . 'terminals';
    $auth_token = env('TERMINALES_AUTH_TOKEN');
    $headers = [
        'Content-type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $auth_token
    ];
    $data_result = curl_helper($url, [
        'local_id' => $inputs->local_id,
        'name' => $inputs->name,
        'local_name' => $inputs->local_name,
        'user_id_created' => $inputs->user_id_created,
        'user_name_created' => $inputs->user_name_created
    ], $headers);

    if (!$data_result["error"]) {
        $data_result['message'] = 'Los datos de registraron correctamente.';
    }

    return $data_result;
}

function fnc_update_local_terminal($inputs): array
{
    $url = env('TERMINALES_URL') . 'terminals/' . $inputs->id;
    $auth_token = env('TERMINALES_AUTH_TOKEN');
    $headers = [
        'Content-type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $auth_token
    ];
    $data_result = curl_helper($url, [
        'id' => $inputs->id,
        'name' => $inputs->name
    ], $headers, true, 'PUT');

    if (!$data_result["error"]) {
        $data_result['message'] = 'Los datos de actualizaron correctamente.';
    }

    return $data_result;
}

function fnc_list_terminal_by_service($inputs): array
{
    $url = env('TERMINALES_URL') . 'terminals/list_by_local_id?local_id=' . $inputs->local_id;
    $auth_token = env('TERMINALES_AUTH_TOKEN');
    $headers = [
        'Content-type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $auth_token
    ];
    return curl_helper($url, null, $headers, true, 'GET');
}

function fnc_get_nombre_local_by_local_id($local_id)
{
    global $mysqli;
    $query_result = [];
    $query_select = "
	SELECT l.nombre AS nombre_local
	FROM tbl_locales AS l
	WHERE l.id = '{$local_id}';";
    $result = $mysqli->query($query_select);
    if ($mysqli->error) {
        $query_result['query_message_error'] = $mysqli->error;
        $query_result['error'] = true;
        $query_result['query'] = $query_select;
        return $query_result;
    }
    $object = $result->fetch_object();
    if ($object) {
        return $object->nombre_local;
    }
    return null;
}

function fnc_update_estado_local_terminal($inputs): array
{
    $url = env('TERMINALES_URL') . 'terminals/' . $inputs->id;
    $auth_token = env('TERMINALES_AUTH_TOKEN');
    $headers = [
        'Content-type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $auth_token
    ];
    $request = [
        'id' => $inputs->id,
        'status' => $inputs->status
    ];
    return curl_helper($url, $request, $headers, true, 'PUT');
}

function fnc_update_token_terminal($inputs): array
{
    $url = env('TERMINALES_URL') . 'auth/terminal/generate_token';
    $auth_token = env('TERMINALES_AUTH_TOKEN');
    $headers = [
        'Content-type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $auth_token
    ];
    $request = [
        'id' => $inputs->id
    ];
    return curl_helper($url, $request, $headers);
}

function fnc_list_proveedores_by_terminal($inputs): array
{
    $data_result = [];
    $data_result["error"] = true;
    $data_result["http_code"] = 400;
    if (!isset($inputs->id_terminal_auto_servicio) || !$inputs->id_terminal_auto_servicio) {
        $data_result["message"] = 'Falta el parámetro id_terminal_auto_servicio.';
        return $data_result;
    }
    $url = env('TERMINALES_URL') . 'terminal_providers/list_providers_by_terminal_id?terminal_id=' . $inputs->id_terminal_auto_servicio;
    $auth_token = env('TERMINALES_AUTH_TOKEN');
    $headers = [
        'Content-type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $auth_token
    ];
    return curl_helper($url, null, $headers, true, 'GET');
}

function fnc_update_proveedores_by_terminal($inputs): array
{
    $data_result = [];
    $data_result["error"] = true;
    $data_result["http_code"] = 400;

    if (!isset($inputs->proveedores) || count($inputs->proveedores) < 1) {
        $data_result["message"] = 'Falta el parámetro poveedores.';
        return $data_result;
    }

    $url = env('TERMINALES_URL') . 'terminal_providers';
    $auth_token = env('TERMINALES_AUTH_TOKEN');

    $headers = [
        'Content-type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $auth_token
    ];

    $request = array();
    $request['items'] = $inputs->proveedores;
    return curl_helper($url, $request, $headers);
}

function fnc_mac_requerida($inputs): array
{
    $data_result = [];
    $data_result["error"] = true;
    $data_result["http_code"] = 400;

    if (!isset($inputs->usuario_id)) {
        $data_result["message"] = 'Falta el parámetro usuario_id.';
        return $data_result;
    }

    $query_result = fnc_check_permisos_required_mac($inputs->usuario_id);

    $tiene_permiso = false;
    if (!$query_result['error']) {
        if (is_array($query_result['data']) && count($query_result['data']) > 0) {
            $tiene_permiso = $query_result['data'][0]['tiene_permiso'] == 1;
        }
    }

    if (!$tiene_permiso) {
        $data_result["message"] = 'Usted no tiene permiso para realizar esta acción.';
        return $data_result;
    }

    if (!isset($inputs->require_mac)) {
        $data_result["message"] = 'Falta el parámetro require_mac.';
        return $data_result;
    }

    if (!isset($inputs->id)) {
        $data_result["message"] = 'Falta el parámetro id.';
        return $data_result;
    }

    return update_require_mac($inputs);
}

function fnc_check_permisos_required_mac($usuario_id): array
{
    global $mysqli;
    $query_result = [
        'data' => [],
        'query_message_error' => '',
        'error' => false,
        'query' => '',
        'message' => ''
    ];
    $query_select = "SELECT count(p.id) as tiene_permiso
FROM tbl_permisos p
         LEFT JOIN tbl_botones b ON (p.boton_id = b.id)
WHERE p.usuario_id = '" . $usuario_id . "' AND p.estado = '1' AND b.boton = 'mac_requerido_autoservcios' LIMIT 1;";
    $result = $mysqli->query($query_select);
    if ($mysqli->error) {
        $query_result['query_message_error'] = $mysqli->error;
        $query_result['message'] = $mysqli->error;
        $query_result['error'] = true;
        $query_result['query'] = $query_select;
        return $query_result;
    }

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $query_result['data'] = $data;
    return $query_result;
}

function fnc_get_canal_de_venta_id_by_servicio_id($inputs): array
{
    global $mysqli;
    $query_result = [
        'data' => [],
        'query_message_error' => '',
        'error' => false,
        'query' => '',
        'message' => ''
    ];
    $query_select = "SELECT id as canal_de_venta_id  
FROM tbl_canales_venta 
WHERE servicio_id = {$inputs->servicio_id} 
  AND estado = 1 
  AND (codigo LIKE '%autoservicio%' OR nombre LIKE '%autoservicio%') ORDER BY id DESC LIMIT 1;";
    $result = $mysqli->query($query_select);
    if ($mysqli->error) {
        $query_result['query_message_error'] = $mysqli->error;
        $query_result['message'] = $mysqli->error;
        $query_result['error'] = true;
        $query_result['query'] = $query_select;
        return $query_result;
    }

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $query_result['data'] = $data;
    return $query_result;
}

function fnc_get_proveedores_api_habilitada(): array
{
    $url = env('TERMINALES_URL') . 'proveedores/api_habilitada';
    $auth_token = env('TERMINALES_AUTH_TOKEN');
    $headers = [
        'Content-type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $auth_token
    ];
    return curl_helper($url, null, $headers, true, 'GET');
}

function fnc_get_entity_parent_id_by_servicio_id_and_local_id($inputs): array
{
    global $mysqli;
    $query_result = [
        'data' => [],
        'error' => false,
        'query' => '',
        'message' => ''
    ];
    $sql_query = "SELECT
    lpi.proveedor_id AS entity_id
FROM  tbl_local_proveedor_id AS lpi
WHERE lpi.servicio_id = {$inputs->servicio_id}
    AND lpi.local_id = {$inputs->local_id}
    AND lpi.estado = 1
ORDER BY lpi.proveedor_id LIMIT 1";
    $result = $mysqli->query($sql_query);
    if ($mysqli->error) {
        $query_result['message'] = $mysqli->error;
        $query_result['error'] = true;
        $query_result['query'] = $sql_query;
        return $query_result;
    }

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $query_result['data'] = $data;
    return $query_result;
}

function fnc_get_entity_parent_id_by_canal_de_venta_id_and_local_id($inputs): array
{
    global $mysqli;
    $query_result = [
        'data' => [],
        'error' => false,
        'query' => '',
        'message' => ''
    ];
    $sql_query = "SELECT
    lpi.proveedor_id AS entity_id
FROM  tbl_local_proveedor_id AS lpi
WHERE lpi.canal_de_venta_id = {$inputs->canal_de_venta_id}
    AND lpi.local_id = {$inputs->local_id}
    AND lpi.estado = 1
ORDER BY lpi.proveedor_id LIMIT 1";
    $result = $mysqli->query($sql_query);
    if ($mysqli->error) {
        $query_result['message'] = $mysqli->error;
        $query_result['error'] = true;
        $query_result['query'] = $sql_query;
        return $query_result;
    }

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    $query_result['data'] = $data;
    return $query_result;
}

function fnc_get_entities_by_entity_parent_id($inputs): array
{
    $url = env('TERMINALES_URL') . 'terminalautoservicio/entitygetparent?idEntity=' . $inputs->entity_id;
    $auth_token = env('TERMINALES_AUTH_TOKEN');
    $headers = [
        'Content-type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $auth_token
    ];
    return curl_helper($url, null, $headers, true, 'GET');
}

function get_entities_by_servicio_id_and_local_id($inputs): array
{
    $result = fnc_get_entity_parent_id_by_servicio_id_and_local_id($inputs);
    if (!$result['error']) {
        if (count($result['data'])) {
            $inputs = (object)$result['data'][0];
            $result = fnc_get_entities_by_entity_parent_id($inputs);
        } else {
            $result['message'] = 'No existe la entidad principal.';
        }
    }
    return $result;
}

function get_entities_by_canal_de_venta_id_and_local_id($inputs): array
{
    $result = fnc_get_entity_parent_id_by_canal_de_venta_id_and_local_id($inputs);
    if (!$result['error']) {
        if (count($result['data'])) {
            $inputs = (object)$result['data'][0];
            $result = fnc_get_entities_by_entity_parent_id($inputs);
        } else {
            $result['message'] = 'No existe la entidad principal.';
        }
    }
    return $result;
}

function fnc_save_conf_entity_local($inputs): array
{
    $url = env('TERMINALES_URL') . 'terminalautoservicio/confEntityLocal';
    $auth_token = env('TERMINALES_AUTH_TOKEN');
    $headers = [
        'Content-type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $auth_token
    ];
    $request = array();
    $request['config'] = $inputs->config;
    return curl_helper($url, $request, $headers);
}

function fnc_get_entity_local($inputs): array
{
    $url = env('TERMINALES_URL') . 'terminalautoservicio/entityLocal?local_id=' . $inputs->local_id;
    $auth_token = env('TERMINALES_AUTH_TOKEN');
    $headers = [
        'Content-type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $auth_token
    ];
    return curl_helper($url, null, $headers, true, 'GET');
}


function fnc_entity_get_tree($entity_id): array
{
    $url = env('TERMINALES_URL') . 'terminalautoservicio/entitygettree?idEntity=' . $entity_id;
    $auth_token = env('TERMINALES_AUTH_TOKEN');
    $headers = [
        'Content-type: application/json',
        'Accept: application/json',
        'Authorization: Bearer ' . $auth_token
    ];
    return curl_helper($url, null, $headers, true, 'GET');
}

function fnc_get_entities_by_servicio_id_and_local_id_through_canal_de_venta_id($inputs)
{
    $result = fnc_get_canal_de_venta_id_by_servicio_id($inputs);
    if (!$result['error']) {
        if (count($result['data'])) {
            $inputs->{'canal_de_venta_id'} = $result['data'][0]['canal_de_venta_id'];
            $result = fnc_get_entity_parent_id_by_canal_de_venta_id_and_local_id($inputs);
            if (!$result['error']) {
                if (count($result['data'])) {
                    $inputs = (object)$result['data'][0];
                    $result = fnc_get_entities_by_entity_parent_id($inputs);
                } else {
                    $result['message'] = 'No existe la entidad principal.';
                }
            }
        } else {
            $result['message'] = 'No existe el canal de venta.';
        }
    }
    return $result;
}

function fnc_get_entity_parent_id_by_servicio_id_and_local_id_through_canal_de_venta_id($inputs)
{
    $result = fnc_get_canal_de_venta_id_by_servicio_id($inputs);
    if (!$result['error']) {
        if (count($result['data'])) {
            $inputs->{'canal_de_venta_id'} = $result['data'][0]['canal_de_venta_id'];
            $result = fnc_get_entity_parent_id_by_canal_de_venta_id_and_local_id($inputs);
        } else {
            $result['message'] = 'No existe el canal de venta.';
        }
    }
    return $result;
}