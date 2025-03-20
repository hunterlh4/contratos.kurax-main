<?php
include("db_connect.php");
include("sys_login.php");
require_once('/var/www/html/sys/helpers.php');
require_once('/var/www/html/sys/curl_helper.php');
require_once('/var/www/html/env.php');
require_once('/var/www/html/sys/helper_terminal_auto_servicio.php');
date_default_timezone_set("America/Lima");

$data_result = [];
$data_result["error"] = true;
$data_result["http_code"] = 400;

if (isset($_REQUEST['accion'])) {
    $accion = $_REQUEST['accion'];
    if ($accion == 'get_terminal_list') {
        if (!isset($_REQUEST['local_id'])) {
            $data_result["msj"] = 'Falta el parámetro local_id.';
        } else {
            $local_id = intval($_REQUEST['local_id']);
            $data_result = fnc_terminal_auto_servicio_listar_terminales($local_id);
        }
    } else if ($accion == 'get_terminal_balance') {
        if (!isset($_REQUEST['id_terminal_auto_servicio'])) {
            $data_result["msj"] = 'Falta el parámetro id_terminal_auto_servicio.';
        } else {
            $id_terminal_auto_servicio = intval($_REQUEST['id_terminal_auto_servicio']);
            $data_result = fnc_terminal_auto_servicio_obtener_terminal_balance($id_terminal_auto_servicio);
        }
    } else if ($accion == 'deposito_retiro') {
        $msj = fnc_validar_inputs_desposito_retiro($_POST);
        if ($msj) {
            $data_result["msj"] = $msj;
        } else {
            $query_result = fnc_terminal_auto_servicio_verificar_estado_terminal($_POST['id_terminal_auto_servicio']);
            if (!$query_result['error']) {
                if ($query_result['data'] != null) {
                    $query_result = fnc_terminal_auto_servicio_verificar_proveedores_activos($_POST['id_terminal_auto_servicio']);
                    if (!$query_result['error']) {
                        if ($query_result['data'] != null) {
                            $usuario_id = intval($_POST['usuario_id']);
                            $query_result = fnc_terminal_auto_servicio_verificar_caja($usuario_id);
                            if (!$query_result['error']) {
                                $data = [];
                                if (is_array($query_result['data'])) {
                                    $data = $query_result['data'];
                                }
                                $estado_caja = count($data) > 0;
                                if ($estado_caja) {
                                    $data_result = fnc_terminal_auto_servicio_guardar_deposito_retiro($_POST);
                                    if (!$data_result['error']) {
                                        $transaccion = $data_result['data']['objTransaction'];
                                        $transaccion['nombre_terminal'] = $_POST['nombre_terminal'];
                                        fnc_terminal_auto_servicio_guardar_transaccion($transaccion);
                                    }
                                } else {
                                    $data_result["msj"] = 'Usted no tiene una caja abieta o la fecha de operación de la caja no coincide con la fecha de operación actual.';
                                }
                            } else {
                                $data_result = $query_result;
                            }
                        } else {
                            $data_result["msj"] = 'El terminal no tiene proveedores activos.';
                        }
                    } else {
                        $data_result = $query_result;
                    }

                } else {
                    $data_result["msj"] = 'El terminal no está activo.';
                }
            } else {
                $data_result = $query_result;
            }
        }
    } else if ($accion == 'listar_transacciones') {
        if (!isset($_REQUEST['id_terminal_auto_servicio'])) {
            $data_result["msj"] = 'Falta el parámetro id_terminal_auto_servicio.';
        } else {
            $id_terminal_auto_servicio = intval($_REQUEST['id_terminal_auto_servicio']);
            $data_result = fnc_terminal_auto_servicio_listar_transacciones($id_terminal_auto_servicio);
            if (!$data_result['error']) {
                if (is_array($data_result['data']) && count($data_result['data'])) {
                    foreach ($data_result['data'] as $index => $row) {
                        $caja_id = intval($row['caja_id']);
                        $query_result = fnc_terminal_auto_servicio_get_caja_by_caja_id($caja_id);
                        if (!$query_result['error']) {
                            if (is_array($query_result['data']) && count($query_result['data']) > 0) {
                                $data_caja = $query_result['data'][0];
                                $row['turno'] = $data_caja['turno'];
                                $row['cajero'] = $data_caja['cajero'];
                                $row['local_nombre'] = $data_caja['local_nombre'];
                                $data_result['data'][$index] = $row;
                            }
                        } else {
                            $data_result = $query_result;
                        }
                    }
                }
            }
        }
    } else if ($accion == 'reporte_caja') {
        if (!isset($_REQUEST['caja_id'])) {
            $data_result["msj"] = 'Falta el parámetro caja_id.';
        } else {
            $caja_id = intval($_REQUEST['caja_id']);
            $data_result = fnc_terminal_auto_servicio_reporte_caja($caja_id);
            if (!$data_result['error']) {
                if (is_array($data_result['data']) && count($data_result['data']) > 0) {
                    $row = $data_result['data'];
                    $query_result = fnc_terminal_auto_servicio_get_caja_by_caja_id($caja_id);
                    if (!$query_result['error']) {
                        if (is_array($query_result['data']) && count($query_result['data']) > 0) {
                            $data_caja = $query_result['data'][0];
                            $row['fecha_operacion'] = $data_caja['fecha_operacion'];
                            $row['usuario'] = $data_caja['usuario'];
                            $row['local_nombre'] = $data_caja['local_nombre'];
                            $data_result['data'] = $row;
                        }
                    } else {
                        $data_result = $query_result;
                    }
                } else {
                    $data_result['error'] = true;
                    $data_result['http_code'] = 400;
                    $data_result['msj'] = 'Usted no tiene transacciones en esta caja.';
                }

            }
        }
    } else if ($accion == 'block_terminal') {
        if (!isset($_POST['id_terminal_auto_servicio'])) {
            $data_result["msj"] = 'Falta el parámetro id_terminal_auto_servicio del terminal.';
        } else if (!isset($_POST['hold'])) {
            $data_result["msj"] = 'Falta el parámetro hold.';
        } else {
            $inputs = [
                'id_terminal_auto_servicio' => $_POST['id_terminal_auto_servicio'],
                //'key_firebase' => $_POST['key_firebase'],
                'hold' => $_POST['hold']
            ];
            $data_result = fnc_terminal_auto_servicio_block_terminal($inputs);
        }
    } else if ($accion == 'verificar_caja') {
        if (!isset($_POST['usuario_id'])) {
            $data_result["msj"] = 'Falta el parámetro usuario_id.';
        } else {
            $usuario_id = intval($_POST['usuario_id']);
            $query_result = fnc_terminal_auto_servicio_verificar_caja($usuario_id);
            if (!$query_result['error']) {
                if (is_array($query_result['data']) && count($query_result['data']) > 0) {
                    $data_result['data'] = ['estado_caja' => true];
                    $data_result["error"] = false;
                    $data_result["http_code"] = 200;
                } else {
                    $data_result["msj"] = 'Usted no tiene una caja abieta o la fecha de operación de la caja no coincide con la fecha de operación actual.';
                }
            } else {
                $data_result = $query_result;
            }
        }
    } else if ($accion == 'verificar_estado_terminal') {
        if (!isset($_POST['id_terminal_auto_servicio'])) {
            $data_result["msj"] = 'Falta el parámetro id_terminal_auto_servicio.';
        } else {
            $id_terminal_auto_servicio = $_POST['id_terminal_auto_servicio'];
            $query_result = fnc_terminal_auto_servicio_verificar_estado_terminal($id_terminal_auto_servicio);
            if (!$query_result['error']) {
                if ($query_result['data'] != null) {
                    $data_result['data'] = ['estado_terminal' => true];
                    $data_result["error"] = false;
                    $data_result["http_code"] = 200;
                } else {
                    $data_result["msj"] = 'El terminal no está activo.';
                }
            } else {
                $data_result = $query_result;
            }
        }
    } else if ($accion == 'verificar_proveedores_activos') {
        if (!isset($_POST['id_terminal_auto_servicio'])) {
            $data_result["msj"] = 'Falta el parámetro id_terminal_auto_servicio.';
        } else {
            $id_terminal_auto_servicio = $_POST['id_terminal_auto_servicio'];
            $query_result = fnc_terminal_auto_servicio_verificar_proveedores_activos($id_terminal_auto_servicio);
            if (!$query_result['error']) {
                if ($query_result['data'] != null) {
                    $data_result['data'] = ['estado_terminal' => true];
                    $data_result["error"] = false;
                    $data_result["http_code"] = 200;
                } else {
                    $data_result["msj"] = 'El terminal no tiene proveedores activos.';
                }
            } else {
                $data_result = $query_result;
            }
        }
    } else {
        $data_result["msj"] = 'La acción no es valida.';
    }
} else {
    $data_result["msj"] = 'Debe especificar una acción.';
}

echo json_encode($data_result);

function fnc_terminal_auto_servicio_listar_terminales(int $local_id): array
{
    $url = env('TERMINALES_URL') . 'terminalautoservicio/listcaja/?local_id=' . $local_id;
    $auth_token = env('TERMINALES_AUTH_TOKEN');
    $headers = array();
    $headers[] = "Content-type: application/json";
    $headers[] = "token: " . $auth_token;
    return curl_helper($url, null, $headers, true, 'GET');
}

function fnc_terminal_auto_servicio_obtener_terminal_balance(int $id_terminal_auto_servicio): array
{
    $url = env('TERMINALES_URL') . 'terminalBalance/list/?id_termina_auto_servicio=' . $id_terminal_auto_servicio;
    $auth_token = env('TERMINALES_AUTH_TOKEN');
    $headers = array();
    $headers[] = "Content-type: application/json";
    $headers[] = "token: " . $auth_token;
    return curl_helper($url, null, $headers, true, 'GET');
}

function fnc_terminal_auto_servicio_guardar_deposito_retiro(array $inputs): array
{
    $url = env('TERMINALES_URL') . 'terminaltransaccion/add';
    $auth_token = env('TERMINALES_AUTH_TOKEN');
    $headers = [
        'Content-type: application/json', 'token: ' . $auth_token
    ];
    return curl_helper($url, $inputs, $headers);
}

function fnc_terminal_auto_servicio_listar_transacciones(int $id_terminal_auto_servicio): array
{
    $url = env('TERMINALES_URL') . 'terminaltransaccion/getlasttransacctions/?id=' . $id_terminal_auto_servicio;
    $auth_token = env('TERMINALES_AUTH_TOKEN');
    $headers = [
        'Content-type: application/json', 'token: ' . $auth_token
    ];
    return curl_helper($url, null, $headers, true, 'GET');
}

function fnc_terminal_auto_servicio_reporte_caja(int $caja_id): array
{
    $url = env('TERMINALES_URL') . 'terminaltransaccion/getreportecaja/?id_caja=' . $caja_id;
    $auth_token = env('TERMINALES_AUTH_TOKEN');
    $headers = [
        'Content-type: application/json', 'token: ' . $auth_token
    ];
    return curl_helper($url, null, $headers, true, 'GET');
}

function fnc_terminal_auto_servicio_block_terminal(array $inputs): array
{
    $url = env('TERMINALES_URL') . 'terminalautoservicio/blockterminal/';
    $auth_token = env('TERMINALES_AUTH_TOKEN');
    $headers = [
        'Content-type: application/json', 'token: ' . $auth_token
    ];
    return curl_helper($url, $inputs, $headers);
}

function fnc_validar_inputs_desposito_retiro(array $inputs): string
{
    $message = '';
    if (!isset($inputs['id_terminal_auto_servicio'])) {
        $message = 'Falta el parámetro id_terminal_auto_servicio.';
    } else if (!isset($inputs['id_terminal_transaccion_tipo'])) {
        $message = 'Falta el parámetro id_terminal_transaccion_tipo.';
    } else if (!isset($inputs['monto_transaccion'])) {
        $message = 'Falter el parámetro monto_transaccion.';
    } else if (!isset($inputs['cc_id'])) {
        $message = 'Falta el parámetro cc_id.';
    } else if (!isset($inputs['user_created'])) {
        $message = 'Falta el parámetro user_created.';
    } else if (!isset($inputs['caja_id'])) {
        $message = 'Falta el parámetro caja_id.';
    } else if (!isset($inputs['local_id'])) {
        $message = 'Falta el parámetro local_id.';
    } else if (!isset($inputs['usuario_id'])) {
        $message = 'Falta el parámetro usuario_id.';
    } else if (!isset($inputs['nombre_terminal'])) {
        $message = 'Falta el parámetro nombre_terminal.';
    }
    return $message;
}

function fnc_terminal_auto_servicio_verificar_caja(int $usuario_id): array
{
    global $mysqli;
    $query_result = [
        'error' => false
    ];
    $query_select = "SELECT
    sqc.id
FROM
    tbl_caja sqc
    JOIN tbl_local_cajas sqlc ON sqlc.id = sqc.local_caja_id
    JOIN tbl_locales ssql ON ssql.id = sqlc.local_id
WHERE
  sqc.estado = 0
  AND sqc.usuario_id = '" . $usuario_id . "'
  AND sqc.fecha_operacion = DATE(NOW())
ORDER BY sqc.id DESC
    LIMIT 1;";
    $result = $mysqli->query($query_select);
    if ($mysqli->error) {
        $query_result['msj'] = $mysqli->error;
        $query_result['error'] = true;
        $query_result['query'] = $query_select;
    } else {
        $query_result['data'] = [];
        while ($row = $result->fetch_assoc()) {
            $query_result['data'][] = $row;
        }
    }

    return $query_result;
}

function fnc_terminal_auto_servicio_verificar_estado_terminal(int $id_terminal_auto_servicio): array
{
    $url = env('TERMINALES_URL') . 'terminalautoservicio/verificarestado/?id_terminal_auto_servicio=' . $id_terminal_auto_servicio;
    $auth_token = env('TERMINALES_AUTH_TOKEN');
    $headers = array();
    $headers[] = "Content-type: application/json";
    $headers[] = "token: " . $auth_token;
    return curl_helper($url, null, $headers, true, 'GET');
}

function fnc_terminal_auto_servicio_verificar_proveedores_activos(int $id_terminal_auto_servicio): array
{
    $url = env('TERMINALES_URL') . 'terminalproveedores/verificarProveedoresActivos/?id_terminal_auto_servicio=' . $id_terminal_auto_servicio;
    $auth_token = env('TERMINALES_AUTH_TOKEN');
    $headers = array();
    $headers[] = "Content-type: application/json";
    $headers[] = "token: " . $auth_token;
    return curl_helper($url, null, $headers, true, 'GET');
}

function fnc_terminal_auto_servicio_guardar_transaccion($transaccion): array
{
    $query_result = [
        'error' => true,
        'data' => []
    ];

    if (!isset($transaccion['id'])) {
        $query_result["msj"] = 'Falta el parámetro id.';
    } else if (!isset($transaccion['id_terminal_auto_servicio'])) {
        $query_result["msj"] = 'Falta el parámetro id_terminal_auto_servicio.';
    } else if (!isset($transaccion['id_terminal_transacciones_tipos'])) {
        $query_result["msj"] = 'Falta el parámetro id_terminal_transacciones_tipos.';
    } else if (!isset($transaccion['caja_id'])) {
        $query_result["msj"] = 'Falta el parámetro caja_id.';
    } else if (!isset($transaccion['cc_id'])) {
        $query_result["msj"] = 'Falta el parámetro cc_id.';
    } else if (!isset($transaccion['monto_deposito'])) {
        $query_result["msj"] = 'Falta el parámetro monto_deposito.';
    } else if (!isset($transaccion['monto_deposito_bono'])) {
        $query_result["msj"] = 'Falta el parámetro monto_deposito_bono.';
    } else if (!isset($transaccion['monto_deposito_total'])) {
        $query_result["msj"] = 'Falta el parámetro monto_deposito_total.';
    } else if (!isset($transaccion['deposito_observacion'])) {
        $query_result["msj"] = 'Falta el parámetro deposito_observacion.';
    } else if (!isset($transaccion['monto_retiro'])) {
        $query_result["msj"] = 'Falta el parámetro monto_retiro.';
    } else if (!isset($transaccion['retiro_observacion'])) {
        $query_result["msj"] = 'Falta el parámetro retiro_observacion.';
    } else if (!isset($transaccion['transaccion_rechazada'])) {
        $query_result["msj"] = 'Falta el parámetro transaccion_rechazada.';
    } else if (!isset($transaccion['user_created'])) {
        $query_result["msj"] = 'Falta el parámetro user_created.';
    } else if (!isset($transaccion['created_at'])) {
        $query_result["msj"] = 'Falta el parámetro created_at.';
    } else if (!isset($transaccion['updated_at'])) {
        $query_result["msj"] = 'Falta el parámetro updated_at.';
    } else if (!isset($transaccion['estado'])) {
        $query_result["msj"] = 'Falta el parámetro estado.';
    } else if (!isset($transaccion['nombre_terminal'])) {
        $query_result["msj"] = 'Falta el parámetro nombre_terminal.';
    } else {
        global $mysqli;

        $query_insert = "insert into tbl_terminal_transacciones (
            ext_id,
            ext_id_terminal_auto_servicio,
            ext_id_terminal_transacciones_tipos,
            ext_caja_id,
            ext_cc_id,
            ext_monto_deposito,
            ext_monto_deposito_bono,
            ext_monto_deposito_total,
            ext_deposito_observacion,
            ext_monto_retiro,
            ext_retiro_observacion,
            ext_transaccion_rechazada,
            ext_nombre_terminal,
            ext_user_created,
            ext_created_at,
            ext_updated_at,
            ext_estado,
            created_at,
            updated_at
        )
values (
            {$transaccion['id']},
            {$transaccion['id_terminal_auto_servicio']},
            {$transaccion['id_terminal_transacciones_tipos']},
            {$transaccion['caja_id']},
            '{$transaccion['cc_id']}',
            {$transaccion['monto_deposito']},
            {$transaccion['monto_deposito_bono']},
            {$transaccion['monto_deposito_total']},
            '{$transaccion['deposito_observacion']}',
            {$transaccion['monto_retiro']},
            '{$transaccion['retiro_observacion']}',
            {$transaccion['transaccion_rechazada']},
            '{$transaccion['nombre_terminal']}',
            {$transaccion['user_created']},
             STR_TO_DATE('{$transaccion['created_at']}', '%Y-%m-%dT%H:%i:%s.%fZ'),
             STR_TO_DATE('{$transaccion['updated_at']}', '%Y-%m-%dT%H:%i:%s.%fZ'),
            {$transaccion['estado']},
            NOW(),
            NOW()
       );";
        $result = $mysqli->query($query_insert);
        $query_result['data'] = ['result' => $result];
        if ($mysqli->error) {
            $query_result['msj'] = $mysqli->error;
            $query_result['query'] = $query_insert;
        } else {
            $query_result['error'] = false;
            $query_result['msj'] = 'Ok.';

        }
    }

    return $query_result;
}





