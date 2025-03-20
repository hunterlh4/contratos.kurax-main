<?php
$return = array();
$return["memory_init"]=memory_get_usage();
$return["time_init"] = microtime(true);
require_once '/var/www/html/sys/db_connect.php';
require_once '/var/www/html/sys/sys_login.php';
require_once '/var/www/html/sys/db_connect_vibra.php';
setlocale(LC_NUMERIC, 'en_US.UTF-8');

if ($_POST["action"] == "get_data_table") {
    $command_where = "";

    if ($_POST["filtro_local_creacion"] !== 'all') {
        $filtro_local_creacion_search = (int)$_POST["filtro_local_creacion"];
        $command_where.= " AND e.id = ".$filtro_local_creacion_search;
    }

    $cuota_menor_search = (float)$_POST["filtro_cuota_menor"];
    $cuota_mayor_search = (float)$_POST["filtro_cuota_mayor"];
    $command_where.= " AND b.total_ganho_possivel >= ".$cuota_menor_search;
    if ($cuota_mayor_search !== 0.00) {
        $command_where.= " AND b.total_ganho_possivel <= ".$cuota_mayor_search;
    }

    $monto_menor_search = (float)$_POST["filtro_monto_menor"];
    $monto_mayor_search = (float)$_POST["filtro_monto_mayor"];
    $command_where.= " AND b.valor_total_pago >= ".$monto_menor_search;
    if ($monto_mayor_search !== 0.00) {
        $command_where.= " AND b.valor_total_pago <= ".$monto_mayor_search;
    }

    if ($_POST["filtro_canal"] !== 'all') {
        $filtro_canal_search = (int)$_POST["filtro_canal"];
        $command_where.= " AND u.id_tipo_usuario = ".$filtro_canal_search;
    }
    if ($_POST["filtro_tipo"] !== 'all') {
        $filtro_tipo_search = (int)$_POST["filtro_tipo"];
        $command_where.= " AND tv.id = ".$filtro_tipo_search;
    }
    if ($_POST["filtro_vivo"] !== 'all') {
        $filtro_vivo_search = (int)$_POST["filtro_vivo"];
        $command_where.= " AND eb.aovivo IS ".(($filtro_vivo_search === 1) ? "TRUE" : "NOT TRUE");
    }
    if ($_POST["filtro_estado"] !== 'all') {
        $filtro_estado_search = (int)$_POST["filtro_estado"];
        $command_where.= " AND sb.id = ".$filtro_estado_search;
    }
    if ($_POST["filtro_pagado"] !== 'all') {
        $filtro_ganado_search = (int)$_POST["filtro_pagado"];
        $command_where.= " AND pp.momento_pago IS ".(($filtro_ganado_search === 1) ? "NOT NULL" : "NULL");
    }

    $fecha_creacion_inicio = date("Y-m-d", strtotime($_POST["filtro_fecha_creacion_inicio"]));
    $fecha_creacion_fin = date("Y-m-d", strtotime($_POST["filtro_fecha_creacion_fin"]."+1 days"));
    
    $command = "
        SELECT
            b.id as id_billete,
            e.nome as local_creacion,
            CASE
                WHEN u.id_tipo_usuario = 1 THEN 'Caja'
                WHEN u.id_tipo_usuario = 4 THEN 'Terminal'
            END AS canal,
            u.nome as cashdesk,
            tv.nome as tipo,
            CASE
            	WHEN b.id_tipo_venda = 1 AND eb.aovivo IS TRUE THEN 'Vivo'
            	ELSE 'Pre-Match'
            END AS vivo,
            b.valor_total_pago as apostado,
            b.total_ganho_possivel as cuota,
            sb.status as estado,
            pp.total_premio as ganado,
            b.momento_venda as fecha_creacion,
            b.momento_atualizacao as fecha_calculo,
            pp.momento_pago as fecha_pago,
            e2.nome as caja_pago
        FROM
            business_logic.bilhete AS b
            LEFT JOIN business_logic.usuario AS u ON u.id_usuario_sp = b.id_usuario
            LEFT JOIN business_logic.estabelecimento AS e ON e.id = u.id_estabelecimento
            LEFT JOIN business_logic.pagamento_premio pp ON pp.id_bilhete = b.id
            LEFT JOIN business_logic.tipo_venda tv ON tv.id = b.id_tipo_venda
            LEFT JOIN business_logic.status_bilhete sb ON sb.id = b.id_status
            LEFT JOIN business_logic.usuario AS u2 ON u2.id_usuario_sp = pp.id_usuario_pagamento 
            LEFT JOIN business_logic.estabelecimento AS e2 ON e2.id = u2.id_estabelecimento
            LEFT JOIN business_logic.evento_bilhete AS eb ON eb.id_bilhete = b.id AND b.id_tipo_venda = 1
        WHERE
            u.teste IS NOT true
            AND e.id != 4
            AND b.momento_venda >= '{$fecha_creacion_inicio}'
            AND b.momento_venda < '{$fecha_creacion_fin}'
            $command_where
    ";
    $data_query = pg_query($pgsql_vibra, $command);
    if (!$data_query) {
        $return['error'] = "Error en la consulta SQL: " . pg_last_error($pgsql_vibra);
        $return['error2'] = pg_last_error($pgsql_vibra);
    } else {
        $data = [];
        $total_apostado = 0.00;
        $total_ganado = 0.00;
        while ($d = pg_fetch_assoc($data_query)) {
            $data[] = $d;
            $total_apostado+= $d["apostado"];
            $total_ganado+= $d["ganado"];
        }
        $return['data'] = $data;
        $return['total_apostado'] = number_format($total_apostado, 2, ".", ",");
        $return['total_ganado'] = number_format($total_ganado, 2, ".", ",");
    }
    // $return['command'] = $command;
}
elseif ($_POST["action"] == "get_tipo") {
    $command_tipo = "SELECT id, nome FROM business_logic.tipo_venda";
    $data_tipo = pg_query($pgsql_vibra, $command_tipo);
    if (!$data_tipo) {
        $return['error'] = "Error en la consulta SQL: " . pg_last_error($pgsql_vibra);
        $return['error2'] = pg_last_error($pgsql_vibra);
    } else {
        $data = [];
        while ($d = pg_fetch_assoc($data_tipo)) {
            $data[] = $d;
        }
        $return['data_tipo'] = $data;
    }
}
elseif ($_POST["action"] == "get_estado") {
    $command_estado = "SELECT id, status FROM business_logic.status_bilhete";
    $data_estado = pg_query($pgsql_vibra, $command_estado);
    if (!$data_estado) {
        $return['error'] = "Error en la consulta SQL: " . pg_last_error($pgsql_vibra);
        $return['error2'] = pg_last_error($pgsql_vibra);
    } else {
        $data = [];
        while ($d = pg_fetch_assoc($data_estado)) {
            $data[] = $d;
        }
        $return['data_estado'] = $data;
    }
}
elseif ($_POST["action"] == "get_local_creacion") {
    $command_local_creacion = "SELECT id, nome FROM business_logic.estabelecimento";
    $data_local_creacion = pg_query($pgsql_vibra, $command_local_creacion);
    if (!$data_local_creacion) {
        $return['error'] = "Error en la consulta SQL: " . pg_last_error($pgsql_vibra);
        $return['error2'] = pg_last_error($pgsql_vibra);
    } else {
        $data = [];
        while ($d = pg_fetch_assoc($data_local_creacion)) {
            $data[] = $d;
        }
        $return['data_local_creacion'] = $data;
    }
}

$return["memory_end"]=memory_get_usage();
$return["time_end"] = microtime(true);
$return["memory_total"]=($return["memory_end"]-$return["memory_init"]);
$return["time_total"]=($return["time_end"]-$return["time_init"]);
print_r(json_encode($return));
?>