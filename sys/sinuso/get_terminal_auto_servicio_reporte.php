<?php
require_once('/var/www/html/sys/curl_helper.php');
require_once('/var/www/html/env.php');
require_once('/var/www/html/sys/helper_terminal_auto_servicio.php');
$_DATA = json_decode(file_get_contents('php://input'), true);
//var_dump($_DATA);
date_default_timezone_set("America/Lima");

$data_result = [
    "error" => true,
    "http_code" => 400
];

if (isset($_REQUEST['accion'])) {
    $accion = $_REQUEST['accion'];
    if ($accion == 'listar-cajeros') {
        $data_result = fnc_terminal_auto_servicio_reporte_listar_cajeros();
        if (count($data_result) && isset($data_result['data'])) {
            $data = $data_result['data'];
            $user_ids = array_column($data, 'user_created');
            $cajeros = fnc_terminal_auto_servicio_reporte_get_cajeros_by_ids($user_ids);
            $data_result['data'] = $cajeros;
        }
    } else if ($accion == 'listar-transacciones') {
        if (!isset($_POST['fecha_inicio'])) {
            $data_result["msj"] = 'Falta el parámetro fecha_inicio.';
        } else if (!isset($_POST['fecha_fin'])) {
            $data_result["msj"] = 'Falta el parámetro fecha_fin.';
        } else if (!isset($_POST['tipo'])) {
            $data_result["msj"] = 'Falta el parámetro tipo.';
        } else if (!isset($_POST['cc_id'])) {
            $data_result["msj"] = 'Falta el parámetro cc_id.';
        } else if (!isset($_POST['cajero'])) {
            $data_result["msj"] = 'Falta el parámetro cajero.';
        } else {
            $fecha_inicio = $_POST['fecha_inicio'];
            $fecha_fin = $_POST['fecha_fin'];

            $d1 = strtotime($fecha_inicio);
            $d2 = strtotime($fecha_fin);

            if ($d1 > $d2) {
                $data_result["msj"] = 'El parámetro fecha_inicio no puede ser mayor al parámetro fecha_fin.';
            } else {
                $tipo = $_POST['tipo'];
                $cc_id = $_POST['cc_id'];
                $cajero = $_POST['cajero'];
                $data_result = fnc_terminal_auto_servicio_reporte_listar_transacciones($tipo, $cc_id, $cajero, $fecha_inicio, $fecha_fin);

                /*if (!$data_result['error']) {
                    if (is_array($data_result['data']) && count($data_result['data'])) {
                        foreach ($data_result['data']['data_table'] as $index => $row) {
                            $query_result = fnc_terminal_auto_servicio_get_caja_by_caja_id($row['caja_id']);
                            if (!$query_result['error']) {
                                if (is_array($query_result['data']) && count($query_result['data'])) {
                                    $data_caja = $query_result['data'][0];
                                    $row['turno'] = $data_caja['turno'];
                                    $row['cajero'] = $data_caja['cajero'];
                                    $row['local_nombre'] = $data_caja['local_nombre'];
                                    $data_result['data']['data_table'][$index] = $row;
                                }
                            } else {
                                $data_result = $query_result;
                            }
                        }
                    }
                }*/

            }
        }
    } else if ($accion == 'listar-locales-by-user-id') {
        if (!isset($_POST['usuario_id'])) {
            $data_result["msj"] = 'Falta el parámetro usuario_id.';
        } else {
            $usuario_id = $_POST['usuario_id'];
            $locales = fnc_terminal_auto_servicio_reporte_get_locales_by_user_id($usuario_id);
            $data_result['error'] = false;
            $data_result['http_code'] = 200;
            $data_result['data'] = $locales;
        }
    } else if ($accion == 'listar-locales') {
        $locales = fnc_terminal_auto_servicio_reporte_get_locales();
        $data_result['error'] = false;
        $data_result['http_code'] = 200;
        $data_result['data'] = $locales;
    } else if ($accion == 'listar-transacciones-by-terminal') {
        if (!isset($_POST['fecha_inicio'])) {
            $data_result["msj"] = 'Falta el parámetro fecha_inicio.';
        } else if (!isset($_POST['fecha_fin'])) {
            $data_result["msj"] = 'Falta el parámetro fecha_fin.';
        } else if (!isset($_POST['id_terminal_auto_servicio'])) {
            $data_result["msj"] = 'Falta el parámetro id_terminal_auto_servicio.';
        } else if (!isset($_POST['local_id'])) {
            $data_result["msj"] = 'Falta el parámetro local_id.';
        } else {
            $id_terminal_auto_servicio = $_POST['id_terminal_auto_servicio'];
            $local_id = $_POST['local_id'];
            $fecha_inicio = $_POST['fecha_inicio'];
            $fecha_fin = $_POST['fecha_fin'];

            $d1 = strtotime($fecha_inicio);
            $d2 = strtotime($fecha_fin);

            if ($d1 > $d2) {
                $data_result["msj"] = 'El parámetro fecha_inicio no puede ser mayor al parámetro fecha_fin.';
            } else {
                $data_result = fnc_terminal_auto_servicio_reporte_listar_transacciones_by_terminal($id_terminal_auto_servicio, $local_id, $fecha_inicio, $fecha_fin);
            }
        }
    } else if ($accion == 'get-nombres-terminal-by-local-id') {
        if (!isset($_POST['local_id'])) {
            $data_result["msj"] = 'Falta el parámetro local_id.';
        } else {
            $local_id = $_POST['local_id'];
            $data_result = fnc_terminal_auto_servicio_reporte_get_nombres_terminal_by_local_id($local_id);
        }
    } else if ($accion == 'get-balance-transaccion') {
        if (!isset($_POST['transaccion_id'])) {
            $data_result["msj"] = 'Falta el parámetro transaccion_id.';
        } else {
            $transaccion_id = $_POST['transaccion_id'];
            $query_result = fnc_terminal_auto_servicio_get_balance_transaccion($transaccion_id);
            $data_result = $query_result;
        }
    } else if ($accion == 'get-ticket-id') {
        if (!isset($_POST['ticket_id'])) {
            $data_result["msj"] = 'Falta el parámetro ticket_id.';
        } else {
            $ticket_id = $_POST['ticket_id'];
            $data_result = fnc_terminal_auto_servicio_get_ticket_id($ticket_id);
        }
    } else {
        $data_result["msj"] = 'La acción no es valida.';
    }
} else {
    $data_result["msj"] = 'Debe especificar una acción.';
}

echo json_encode($data_result);
exit();

function fnc_terminal_auto_servicio_reporte_listar_transacciones($tipo, $cc_id, $cajero, $fecha_inicio, $fecha_fin): array
{
    /*$url = env('TERMINALES_URL') . 'reporte/list/?tipo=' . $tipo . '&cc_id=' . $cc_id . '&cajero=' . $cajero . '&fecha_inicio=' . $fecha_inicio . '&fecha_fin=' . $fecha_fin;
    $auth_token = env('TERMINALES_AUTH_TOKEN');
    $headers = array();
    $headers[] = "Content-type: application/json";
    $headers[] = "token: " . $auth_token;
    return curl_helper($url, null, $headers, true, 'GET');*/

    $query_result = [
        'error' => false,
        'data' => [
            'summary' => null,
            'data_table' => [],
        ],
    ];

    include("db_connect.php");
    $data_table = [];

    $date = new DateTime($fecha_fin);
    $fecha_fin = $date->modify("+1 day")->format("Y-m-d");
    $where_tipo = ($tipo != 0) ? 'AND tt.ext_id_terminal_transacciones_tipos = ' . $tipo : '';
    $where_local = ($cc_id != 0) ? "AND tt.ext_cc_id = '" . $cc_id . "'" : '';
    $where_cajero = ($cajero != 0) ? 'AND tt.ext_user_created = ' . $cajero : '';
    $where_fecha = "AND tt.ext_created_at >= '" . $fecha_inicio . "'";
    $where_fecha .= " AND tt.ext_created_at < '" . $fecha_fin . "'";
    $where = $where_tipo . ' ' . $where_local . ' ' . $where_cajero . ' ' . $where_fecha;

    $query_report = "SELECT tt.id,
       tt.ext_id_terminal_auto_servicio AS id_terminal_auto_servicio,
       DATE(tt.ext_created_at)             fecha,
       TIME(tt.ext_created_at)             hora,
       CASE
           WHEN tt.ext_id_terminal_transacciones_tipos = 1 THEN 'Depósito'
           WHEN tt.ext_id_terminal_transacciones_tipos = 2 THEN 'Retiro'
           END                          AS tipo_transaccion,
       CASE
           WHEN tt.ext_id_terminal_transacciones_tipos = 1 THEN tt.ext_monto_deposito
           WHEN tt.ext_id_terminal_transacciones_tipos = 2 THEN tt.ext_monto_retiro
           END                          AS monto,
       tt.ext_cc_id                     as cc_id,
       tt.ext_nombre_terminal           as nombre_terminal,
       tt.ext_user_created              as user_created,
       tt.ext_created_at                as created_at,
       tt.ext_updated_at                as updated_at,
       tt.ext_caja_id                   as caja_id,
       l.id                             as local_id,
       l.nombre                         as nombre_local
FROM tbl_terminal_transacciones AS tt
         INNER JOIN tbl_locales l ON tt.ext_cc_id = l.cc_id
WHERE tt.ext_estado = 1  {$where}
            order by tt.created_at desc";

    $list_query_report = $mysqli->query($query_report);;
    while ($li = $list_query_report->fetch_assoc()) {
        $data_table[] = $li;
    }

    $query_report_summary = "
        SELECT
            IFNULL( sum(IF(tt.ext_monto_deposito_total > 0, 1, 0)),0) AS transacciones_deposito,
            IFNULL(sum(IF(tt.ext_monto_retiro > 0, 1, 0)),0) AS transacciones_retiro,
            IFNULL(sum(tt.ext_monto_deposito_total),0.0) AS monto_deposito,
            IFNULL(sum(tt.ext_monto_retiro),0.0) AS monto_retiro
        FROM tbl_terminal_transacciones AS tt
        WHERE
            tt.ext_transaccion_rechazada = 0 AND
            tt.ext_estado = 1 {$where}";

    $summary = [];
    $list_query_report_summary = $mysqli->query($query_report_summary);
    while ($li = $list_query_report_summary->fetch_assoc()) {
        $summary[] = $li;
    }

    $query_result['data']['data_table'] = $data_table;
    $query_result['data']['summary'] = (count($summary) > 0) ? $summary[0] : [];

    return $query_result;
}

function fnc_terminal_auto_servicio_reporte_listar_transacciones_by_terminal($id_terminal_auto_servicio, $local_id, $fecha_inicio, $fecha_fin): array
{
    $url = env('TERMINALES_URL') . 'reporte/listtransaction/?id_terminal_auto_servicio=' . $id_terminal_auto_servicio . '&local_id=' . $local_id . '&fecha_inicio=' . $fecha_inicio . '&fecha_fin=' . $fecha_fin;
    $auth_token = env('TERMINALES_AUTH_TOKEN');
    $headers = array();
    $headers[] = "Content-type: application/json";
    $headers[] = "token: " . $auth_token;
    return curl_helper($url, null, $headers, true, 'GET');
}

function fnc_terminal_auto_servicio_reporte_listar_cajeros(): array
{
    $url = env('TERMINALES_URL') . 'reporte/listcajero';
    $auth_token = env('TERMINALES_AUTH_TOKEN');
    $headers = array();
    $headers[] = "Content-type: application/json";
    $headers[] = "token: " . $auth_token;
    return curl_helper($url, null, $headers, true, 'GET');
}

function fnc_terminal_auto_servicio_reporte_get_nombres_terminal_by_local_id($local_id): array
{
    $url = env('TERMINALES_URL') . 'terminalautoservicio/getnombresterminalbylocalid/?local_id=' . $local_id;
    $auth_token = env('TERMINALES_AUTH_TOKEN');
    $headers = array();
    $headers[] = "Content-type: application/json";
    $headers[] = "token: " . $auth_token;
    return curl_helper($url, null, $headers, true, 'GET');
}


function fnc_terminal_auto_servicio_reporte_get_cajeros_by_ids(array $user_ids): array
{
    include("db_connect.php");
    $cajeros = [];

    $query = "SELECT u.id AS cod_cajero, u.usuario, CONCAT(pa.nombre,' ',pa.apellido_paterno) as nombre_cajero
FROM   tbl_usuarios                AS u
       LEFT JOIN tbl_personal_apt  AS pa
            ON  pa.id = u.personal_id
            
WHERE u.id IN (" . implode(',', array_filter($user_ids)) . ")";

    $result = $mysqli->query($query);
    while ($r = $result->fetch_assoc()) {
        $cajeros[] = $r;
    }
    return $cajeros;
}

function fnc_terminal_auto_servicio_reporte_get_locales()
{
    include("db_connect.php");
    $list_lcls = [];
    $command_lcls = "SELECT tl.id,
       tl.cc_id,
       tl.nombre
FROM tbl_locales tl
WHERE tl.estado = 1
  -- AND tbl_locales.operativo in (1,2)
  AND tl.red_id IN (1, 9, 5, 8) -- 8=TELEVENTAS
  AND tl.zona_id IS NOT NULL
  AND tl.cc_id IS NOT NULL
ORDER BY tl.nombre";
    //SELECT local_id FROM tbl_usuarios_locales WHERE usuario_id = '".$login["id"]."' AND estado = '1'"
    $list_query = $mysqli->query($command_lcls);;
    while ($li = $list_query->fetch_assoc()) {
        $list_lcls[] = $li;
    }
    return $list_lcls;
}

function fnc_terminal_auto_servicio_reporte_get_locales_by_user_id($usuario_id)
{
    include("db_connect.php");
    $list_lcls = [];
    $command_lcls = "SELECT count(id) as result FROM tbl_usuarios as tu  WHERE (tu.grupo_id = 8 OR (tu.cargo_id = 4 AND tu.grupo_id = 12)) AND tu.id = 2287";
    $query_result = $mysqli->query($command_lcls);
    $object = $query_result->fetch_object();
    if ($object && $object->result == 1) {
        $command_lcls = "SELECT tl.id,
                            tl.cc_id,
                            tl.nombre
                        FROM tbl_locales tl
                        WHERE tl.estado = 1
                            AND tl.operativo in (1,2)
                            AND tl.red_id IN (1, 9, 5, 8)
                            AND tl.zona_id IS NOT NULL
                            AND tl.cc_id IS NOT NULL
                        ORDER BY tl.nombre";
    } else {
        $command_lcls = "SELECT tl.id,
                            tl.cc_id,
                            tl.nombre
                        FROM tbl_locales tl
                        INNER JOIN tbl_usuarios_locales as tul ON tl.id = tul.local_id
                        WHERE tl.estado = 1
                            AND tul.estado = 1
                            AND tl.operativo in (1,2)
                            AND tl.red_id IN (1, 9, 5, 8)
                            AND tl.zona_id IS NOT NULL
                            AND tl.cc_id IS NOT NULL AND  tul.usuario_id = {$usuario_id}
                        ORDER BY tl.nombre";
    }

    $list_query = $mysqli->query($command_lcls);
    while ($li = $list_query->fetch_assoc()) {
        $list_lcls[] = $li;
    }
    return $list_lcls;
}

function fnc_terminal_auto_servicio_get_balance_transaccion($transaccion_id)
{
    $url = env('TERMINALES_URL') . 'reporte/balancetransaccion/?transaccion_id=' . $transaccion_id;
    $auth_token = env('TERMINALES_AUTH_TOKEN');
    $headers = array();
    $headers[] = "Content-type: application/json";
    $headers[] = "token: " . $auth_token;
    return curl_helper($url, null, $headers, true, 'GET');
}

function fnc_terminal_auto_servicio_get_ticket_id($ticket_id)
{
    $url = env('TERMINALES_URL') . 'goldentickets/ticket/?ticket_id=' . $ticket_id;
    $auth_token = env('TERMINALES_AUTH_TOKEN');
    $headers = array();
    $headers[] = 'Content-type: application/json';
    $headers[] = 'Accept: application/json';
    $headers[] = 'token: ' . $auth_token;
    return curl_helper($url, null, $headers, true, 'GET');
}