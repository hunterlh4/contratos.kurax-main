<?php
include("db_connect.php");
include("sys_login.php");


include_once("globalFunctions/generalInfo/local.php");

if (isset($_POST["reportes_precierre_get_locales"])) {
    $get_data = $_POST["reportes_precierre_get_locales"];
    $redes = $get_data["redes"];
    $locales = getLocalesByRed($redes, true);
    $return = [
        'status' => 200,
        'locales' => $locales,
    ];
    echo json_encode($return);
    exit();
} else 
if (isset($_POST["buscar_precierre"])) {
    $get_data = $_POST["buscar_precierre"];

    $result_transacciones = getTransacciones($get_data);
    $result = formatDataTableTransacciones($result_transacciones);

    echo json_encode($result);
    exit();
}



function formatDataTableTransacciones($result_transacciones)
{

    global $mysqli;
    $result = [];
    $data = [];

    if ($result_transacciones['status'] == 503) {
        error_log("Error en la consulta: " . $mysqli->error);
        $data[] = [
            "0" => "error",
            "1" => '',
            "2" => 'Comunicarse con Soporte, error: ' . $mysqli->error,
            "3" => '',
            "4" => '',
            "5" => '',
            "6" => '',
            "7" => '',
            "8" => '',
            "9" => '',
            "10" => '',
            "11" => '',
        ];

        $result = [
            "sEcho" => 1,
            "iTotalRecords" => 1,
            "iTotalDisplayRecords" => 1,
            "aaData" => $data
        ];
    } else if ($result_transacciones['status'] == 200) {

        if (!empty($result_transacciones['data'])) {
            foreach ($result_transacciones['data'] as $r) {
                $row = [
                    "0" => $r['cc_id'],
                    "1" => date('d/m/Y', strtotime($r['fecha_operacion'])),
                    "2" => $r['red_nombre'],
                    "3" => $r['zona_nombre'],
                    "4" => $r['local_nombre'], // date('d/m/Y H:i:s', strtotime($r['fecha_registro'])),
                    "5" => $r['ingreso_caja'],
                    "6" => $r['salida_caja'],
                    "7" => $r['devolucion_caja_df'], //  . ' - ' . $r['numero_telefono'],
                    "8" => $r['devolucion_hipica_caja_df'],
                    "9" => $r['pagos_manual_caja_df'],
                    "10" => $r['cabecera_1_RESULTADO_DEL_NEGOCIO'],
                    "11" => $r['cabecera_1_TK_PAGADO_EN_OTRO_PUNTO'],
                    "12" => $r['cabecera_1_TK_PAGADO_DE_OTRO_PUNTO'],
                    "13" => $r['kurax_RESULTADO_DEL_NEGOCIO'],
                    "14" => $r['kurax_TK_PAGADO_EN_OTRO_PUNTO_KURAX'],
                    "15" => $r['kurax_TK_PAGADO_DE_OTRO_PUNTO_KURAX'],
                    "16" => $r['torito_apostado'],
                    "17" => $r['torito_ganado'],
                    "18" => $r['DEPOSITADO_WEB_CALIMACO'],
                    "19" => $r['RETIRADO_WEB_CALIMACO'],
                    "20" => $r['resultado_conta'],
                    "21" => $r['diferencia_pago_manual'],
                    "22" => $r['condicional'],
                    "23" => $r['diferencia_real']
                ];

                $data[] = $row;
            }
        }

        $result['sEcho'] = 1;
        $result['iTotalRecords'] = count($data);
        $result['iTotalDisplayRecords'] = count($data);
        $result['aaData'] = $data;
    }

    return  $result;
}

function getTransacciones($filtros)
{
    global $mysqli;
    global $login;
    $result = [];

    $where = '';
    $$fecha_inicio = '';
    $$fecha_fin = '';
    $ids_locales = '';
    $cc_ids_locales = '';

    $usuario_locales_cc_id = [];
    if (array_key_exists("id", $login)) {
        $usuario_locales_cc_id_command = "  SELECT 
                                                ul.local_id,    
                                                l.cc_id 
                                            FROM tbl_usuarios_locales ul
                                            inner join tbl_locales l on l.id = ul.local_id
                                            WHERE 
                                                ul.usuario_id = '" . $login["id"] . "' 
                                                AND ul.estado = '1'
                                                -- AND l.cc_id is not null
                                        ";
        $usuario_locales_cc_id_query = $mysqli->query($usuario_locales_cc_id_command);
        if ($mysqli->error) {
            print_r($mysqli->error);
        }
        while ($ul = $usuario_locales_cc_id_query->fetch_assoc()) {
            if(!empty($ul["cc_id"])){
                $usuario_locales_cc_id[] = $ul["cc_id"];
            }
        }
    }

    if (!empty($filtros['fecha_inicio'])) {
        $fecha_inicio = $filtros['fecha_inicio'];
        $fecha_fin = $filtros['fecha_fin'];
    }
    if (!empty($filtros['local_id'])) {
        $local_id = $filtros['local_id'];

        $cc_query = " SELECT cc_id from tbl_locales where id = $local_id";
        $cc_id = $mysqli->query($cc_query)->fetch_assoc()['cc_id'];
        
        $ids_locales = "($local_id)";
        $cc_ids_locales = "($cc_id)";

    } else {

        $ids_locales = "(" .  implode(",", $login["usuario_locales"])  . ")";
        $cc_ids_locales = "(" .  implode(",", $usuario_locales_cc_id )  . ")";
    }

    $query = "  SELECT 
                    *, 
                    (
                        cabecera_1_RESULTADO_DEL_NEGOCIO 
                        + (
                            torito_apostado - torito_ganado
                        ) 
                        + kurax_RESULTADO_DEL_NEGOCIO 
                        - resultado_conta
                    ) as diferencia_pago_manual,
                    case
                        when (
                            devolucion_caja_df 
                            + pagos_manual_caja_df
                            + devolucion_hipica_caja_df 
                            + cabecera_1_RESULTADO_DEL_NEGOCIO 
                            + (
                                + (
                                    torito_apostado - torito_ganado
                                ) 
                                + kurax_RESULTADO_DEL_NEGOCIO 
                                - resultado_conta
                            )
                        ) >= 0 then 1
                    else 2
                    end as condicional,
                    (
                        devolucion_caja_df 
                        + pagos_manual_caja_df 
                        + devolucion_hipica_caja_df 
                        + cabecera_1_RESULTADO_DEL_NEGOCIO 
                        + (
                            torito_apostado - torito_ganado
                        ) 
                        + kurax_RESULTADO_DEL_NEGOCIO 
                        - resultado_conta
                    ) as diferencia_real
                from (
                        select
                            resumen_caja.local_id, 
                            l.cc_id, 
                            l.nombre as local_nombre,
                            r.nombre as red_nombre,
                            z.nombre as zona_nombre,
                            resumen_caja.fecha_operacion, 
                            cant_cajas, 
                            IFNULL(ingreso_caja, 0) AS ingreso_caja, 
                            IFNULL(salida_caja, 0) AS salida_caja, 
                            IFNULL(devolucion_caja_df, 0) AS devolucion_caja_df, 
                            IFNULL(devolucion_hipica_caja_df, 0) AS devolucion_hipica_caja_df, 
                            IFNULL(pagos_manual_caja_df, 0) AS pagos_manual_caja_df, 
                            IFNULL(cabecera_1_RESULTADO_DEL_NEGOCIO, 0) AS cabecera_1_RESULTADO_DEL_NEGOCIO, 
                            IFNULL(cabecera_1_TK_PAGADO_EN_OTRO_PUNTO, 0) AS cabecera_1_TK_PAGADO_EN_OTRO_PUNTO, 
                            IFNULL(cabecera_1_TK_PAGADO_DE_OTRO_PUNTO, 0) AS cabecera_1_TK_PAGADO_DE_OTRO_PUNTO, 
                            IFNULL(kurax_RESULTADO_DEL_NEGOCIO, 0) AS kurax_RESULTADO_DEL_NEGOCIO, 
                            IFNULL(kurax_TK_PAGADO_EN_OTRO_PUNTO_KURAX, 0) AS kurax_TK_PAGADO_EN_OTRO_PUNTO_KURAX, 
                            IFNULL(kurax_TK_PAGADO_DE_OTRO_PUNTO_KURAX, 0) AS kurax_TK_PAGADO_DE_OTRO_PUNTO_KURAX, 
                            IFNULL(torito_total_apostado, 0) as torito_apostado, 
                            IFNULL(torito_total_ganado, 0) as torito_ganado, 
                            IFNULL(DEPOSITADO_WEB_CALIMACO, 0) AS DEPOSITADO_WEB_CALIMACO, 
                            IFNULL(RETIRADO_WEB_CALIMACO, 0) AS RETIRADO_WEB_CALIMACO,
                            (
                                  IFNULL(ingreso_caja, 0) 
                                - IFNULL(salida_caja, 0) 
                                - IFNULL(cabecera_1_TK_PAGADO_EN_OTRO_PUNTO, 0) 
                                - IFNULL(kurax_TK_PAGADO_EN_OTRO_PUNTO_KURAX, 0) 
                                + IFNULL(cabecera_1_TK_PAGADO_DE_OTRO_PUNTO, 0) 
                                + IFNULL(kurax_TK_PAGADO_DE_OTRO_PUNTO_KURAX, 0) 
                                - IFNULL(DEPOSITADO_WEB_CALIMACO, 0) 
                                + IFNULL(RETIRADO_WEB_CALIMACO, 0)
                            ) as resultado_conta
                        from (
                                select
                                    q1.local_id, fecha_operacion, COUNT(caja_id) as cant_cajas, SUM(ingreso) as ingreso_caja, SUM(salida) as salida_caja, SUM(dev) as devolucion_caja_df, SUM(dev_hipica) as devolucion_hipica_caja_df, SUM(pago_manu) as pagos_manual_caja_df
                                from (
                                        select
                                            lc.local_id, cd.caja_id, c.fecha_operacion, SUM(ingreso) as ingreso, SUM(salida) as salida, IFNULL(cdf_devolucion.valor, 0) as dev, IFNULL(
                                                cdf_devolucion_hipica.valor, 0
                                            ) as dev_hipica, IFNULL(cdf_pagos_manuales.valor, 0) as pago_manu
                                        from
                                            tbl_caja_detalle cd
                                            inner join tbl_local_caja_detalle_tipos lcdt on lcdt.id = cd.tipo_id
                                            inner join tbl_caja_detalle_tipos cdt on cdt.id = lcdt.detalle_tipos_id
                                            inner join tbl_caja c on c.id = cd.caja_id
                                            inner join tbl_local_cajas lc on c.local_caja_id = lc.id
                                            left join tbl_caja_datos_fisicos cdf_devolucion on c.id = cdf_devolucion.caja_id and cdf_devolucion.tipo_id = 8
                                            left join tbl_caja_datos_fisicos cdf_devolucion_hipica on c.id = cdf_devolucion_hipica.caja_id and cdf_devolucion_hipica.tipo_id = 28
                                            left join tbl_caja_datos_fisicos cdf_pagos_manuales on cdf_pagos_manuales.caja_id = c.id and cdf_pagos_manuales.tipo_id = 9
                                        where
                                            c.fecha_operacion >= '$fecha_inicio'
                                            and cdt.id in (
                                                    1,4,5, -- BC
                                                    3, -- GR
                                                    2, -- SALDO WEB
                                                    15, -- BINGO
                                                    19, -- TORITO
                                                    20, -- HIPICA
                                                    25, -- ALTENAR
                                                    26, -- SALDO TELESERVICIOS
                                                    27,28,29 -- ATERAX
                                                )
                                            and c.fecha_operacion <= '$fecha_fin'
                                            and lc.local_id in $ids_locales
                                        group by
                                            cd.caja_id, c.fecha_operacion
                                    ) q1
                                GROUP BY
                                    fecha_operacion, local_id
                            ) resumen_caja
                            inner join tbl_locales l on l.id = resumen_caja.local_id
                            inner join tbl_locales_redes r on l.red_id = r.id
                            inner join tbl_zonas z on l.zona_id = z.id
                            left join (
                                select
                                    c.fecha, l.id AS local_id, CAST(
                                        SUM(c.total_produccion) as decimal(18, 2)
                                    ) AS cabecera_1_RESULTADO_DEL_NEGOCIO, CAST(
                                        SUM(c.pagado_en_otra_tienda) as decimal(18, 2)
                                    ) AS cabecera_1_TK_PAGADO_EN_OTRO_PUNTO, CAST(
                                        SUM(c.pagado_de_otra_tienda) as decimal(18, 2)
                                    ) AS cabecera_1_TK_PAGADO_DE_OTRO_PUNTO
                                from
                                    tbl_transacciones_cabecera as c
                                    inner join tbl_locales l on c.local_id = l.id
                                where
                                    c.fecha >= '$fecha_inicio'
                                    and c.fecha <= '$fecha_fin'
                                    and c.local_id in $ids_locales
                                    and c.producto_id in (1, 2, 3, 4, 8, 10)
                                    and c.estado = 1
                                GROUP BY
                                    l.id, c.fecha
                            ) cabecera_1 on cabecera_1.local_id = resumen_caja.local_id
                            and cabecera_1.fecha = resumen_caja.fecha_operacion
                            left join (
                                select
                                    c.local_id AS local_id, c.fecha, CAST(
                                        SUM(c.total_produccion) as decimal(18, 2)
                                    ) as kurax_RESULTADO_DEL_NEGOCIO, CAST(
                                        SUM(c.total_apostado) as decimal(18, 2)
                                    ) - CAST(
                                        SUM(c.total_ganado) as decimal(18, 2)
                                    ) as kurax_total_produccion, CAST(
                                        SUM(c.pagado_en_otra_tienda) as decimal(18, 2)
                                    ) AS kurax_TK_PAGADO_EN_OTRO_PUNTO_KURAX, CAST(
                                        SUM(c.pagado_de_otra_tienda) as decimal(18, 2)
                                    ) AS kurax_TK_PAGADO_DE_OTRO_PUNTO_KURAX
                                from tbl_transacciones_cabecera as c
                                where
                                    c.estado = 1
                                    and c.fecha >= '$fecha_inicio'
                                    and c.fecha <= '$fecha_fin'
                                    and c.local_id in $ids_locales
                                    and c.producto_id in (15, 16)
                                GROUP BY
                                    c.local_id, c.fecha
                            ) kurax on resumen_caja.local_id = kurax.local_id
                            and resumen_caja.fecha_operacion = kurax.fecha
                            left join (
                                SELECT
                                    fecha, cc_id, CAST(
                                        SUM(IfNULL(total_apostado, 0)) as decimal(18, 2)
                                    ) AS torito_total_apostado, CAST(
                                        SUM(IfNULL(total_ganado, 0)) as decimal(18, 2)
                                    ) AS torito_total_ganado
                                FROM (
                                        SELECT
                                            tt.date as fecha, tt.cc_id, CASE
                                                WHEN tt.id_torito_tipo_transaccion IN (1, 4) THEN sum(IfNULL(tt.amount, 0))
                                                ELSE 0
                                            END total_apostado, CASE
                                                WHEN tt.id_torito_tipo_transaccion IN (2, 5) THEN sum(IfNULL(tt.amount, 0))
                                                ELSE 0
                                            END total_ganado
                                        FROM tbl_torito_transaccion tt
                                        WHERE
                                            tt.status = 1
                                            AND tt.id_torito_tipo_transaccion IN (1, 2, 4, 5)
                                            and tt.date >= '$fecha_inicio'
                                            and tt.date <= '$fecha_fin'
                                            and tt.cc_id in $cc_ids_locales -- @cc_id
                                        GROUP BY
                                            tt.cc_id, tt.id_torito_tipo_transaccion
                                    ) TU
                                GROUP BY
                                    cc_id
                            ) torito on l.cc_id = torito.cc_id
                            and resumen_caja.fecha_operacion = torito.fecha
                            left join (
                                SELECT
                                    date(created_at) as fecha, cc_id, CAST(
                                        sum(
                                            case
                                                when tipo_id = 1 then monto
                                                else 0.00
                                            end
                                        ) as decimal(18, 2)
                                    ) AS DEPOSITADO_WEB_CALIMACO, CAST(
                                        sum(
                                            case
                                                when tipo_id = 2 then monto
                                                else 0.00
                                            end
                                        ) as decimal(18, 2)
                                    ) AS RETIRADO_WEB_CALIMACO
                                FROM tbl_saldo_web_transaccion
                                WHERE
                                    tipo_id in (1, 2) -- 1: deposito - 2: retiros 
                                    and cc_id in $cc_ids_locales -- @cc_id
                                    AND created_at >= '$fecha_inicio'
                                    and created_at <= DATE_ADD('$fecha_fin', INTERVAL + 1 DAY)
                                group by
                                    date(created_at), cc_id
                            ) calimaco on l.cc_id = calimaco.cc_id
                            and resumen_caja.fecha_operacion = calimaco.fecha
                    ) precierre;
                    ";

    $data = [];
    $query_data = $mysqli->query($query);
    if ($mysqli->error) {
        $result = [
            'status' => 503,
            'message' => 'ERROR:' . $mysqli->error . ' QUERY: ' . $query
        ];
    } else {
        while ($t = $query_data->fetch_assoc()) {
            $data[] = $t;
        }

        $result = [
            'status' => 200,
            'message' => 'OK',
            'data' => $data,
            'query' => $query
        ];
    }

    return $result;
}
