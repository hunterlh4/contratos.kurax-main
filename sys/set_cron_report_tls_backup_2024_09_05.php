<?php

$result = array();
include("db_connect.php");
//include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//*******************************************************************************************************************
//*******************************************************************************************************************
// LISTAR TRANSACCIONES
//*******************************************************************************************************************
//*******************************************************************************************************************

function listar_transacciones($fecha_inicial, $fecha_final)
{
    global $mysqli;
    //$_GET["fecha_inicial"] = date('Y-m-d', strtotime('+1 days', strtotime($fecha_inicial)));

    //*******************************************************************************************************************
    //*******************************************************************************************************************
    // DIARIO, MENSUAL, ANUAL
    //*******************************************************************************************************************
    //*******************************************************************************************************************

    /**
     * NEW TELESERVICIOS QUERY  BY DATA
     **/
    $query_teleservicios_ventas_x_producto = "
      SELECT
        descripcion,
          sum(tickets) tickets,
          sum(apostado) apostado,
          IFNULL(sum(apostado)/sum(tickets), 0) promedio,
          sum(tickets_calculados) tickets_calculados,
          sum(calculado) calculado,
          IFNULL(sum(apostado)-sum(calculado), 0) resultado,
          IFNULL((sum(apostado)-sum(calculado))/sum(apostado), 0) hold,
          sum(TicketsPagado) TicketsPagado,
          sum(Pagado) Pagado,
          IFNULL((sum(TicketsPagado)/sum(tickets_calculados))*100, 0)PorcentajeTksPagado,
          0 AS Distrib_Apostado,
        0 AS Distrib_Resultado
      FROM (
          SELECT case when ca.producto_id = 1 then 'Apuestas Deportivas BC'
                      when ca.producto_id = 2 then 'Juegos Virtuales'
                      when ca.producto_id = 4 then 'Bingo'
                          end as descripcion,
              IFNULL(SUM(ca.num_tickets), 0) AS tickets,
              IFNULL(SUM(ca.total_apostado), 0) AS apostado,
              IFNULL(sum(ca.num_tickets_ganados), 0) tickets_calculados,
              IFNULL(sum(ca.total_ganado), 0) calculado,
              IFNULL(sum(num_tickets_ganados_pagados), 0)TicketsPagado,
              IFNULL(sum(total_pagado), 0)Pagado
              FROM wwwapuestatotal_gestion.tbl_transacciones_cabecera ca
              INNER JOIN wwwapuestatotal_gestion.tbl_locales l ON ca.local_id = l.id
              WHERE ca.fecha >= '" . $fecha_inicial . "'
              AND ca.fecha < '" . $fecha_final . "'
              AND l.red_id = 8
              AND ca.producto_id in (1,2,4)
              AND ca.estado = 1
              AND UPPER(l.nombre) NOT LIKE '%TEST%' 
              AND UPPER(l.nombre) NOT like '%CAPACI%'
              AND UPPER(l.nombre) != 'API TLS GoldenRace'
              group by case when 
              ca.producto_id = 1 then 'Apuestas Deportivas BC'
              when ca.producto_id = 2 then 'Juegos Virtuales'
                      when ca.producto_id = 4 then 'Bingo'
                          end
          
              UNION ALL
              
              SELECT 
              'Torito'descripcion
              ,sum(case when tt.id_torito_tipo_transaccion IN (1,4) then 1 else 0 end)tickets
              ,sum(case when tt.id_torito_tipo_transaccion IN (1,4) then IFNULL(tt.amount,0) else 0 end) apostado
              ,sum(case when tt.id_torito_tipo_transaccion IN (2,5) then 1 else 0 end) tickets_calculados
              ,sum(case when tt.id_torito_tipo_transaccion IN (2,5) then IFNULL(tt.amount,0) else 0 end) calculado
              ,sum(case when tt.id_torito_tipo_transaccion IN (2,5) then 1 else 0 end) TicketsPagado
              ,sum(case when tt.id_torito_tipo_transaccion IN (2,5) then IFNULL(tt.amount,0) else 0 end) Pagado
            FROM   wwwapuestatotal_gestion.tbl_torito_transaccion tt  
            INNER JOIN wwwapuestatotal_gestion.tbl_locales l ON tt.cc_id = l.cc_id
            INNER JOIN wwwapuestatotal_gestion.tbl_zonas z ON z.id = l.zona_id
            WHERE tt.date >= '" . $fecha_inicial . "'
            AND tt.date < '" . $fecha_final . "'
            AND tt.status = 1
            AND tt.id_torito_tipo_transaccion IN (1,2,4,5) 
            AND l.red_id in (8) 
            AND UPPER(l.nombre) NOT like '%TEST%'
            AND UPPER(l.nombre) NOT like '%CAPACI%'
            AND l.nombre != 'API TLS GoldenRace'
    )a
    GROUP BY descripcion
    ORDER BY FIELD (descripcion, 'Apuestas Deportivas BC', 'Juegos Virtuales', 'Bingo', 'Torito')
    ";

    $res_query_teleservicios_ventas_x_producto = $mysqli->query($query_teleservicios_ventas_x_producto);

    if ($mysqli->error) {
        $result["error_query_teleservicios_ventas_x_producto"] = $mysqli->error;
    }

    $list_teleservicios_tabla_ventas_x_producto = array();

    foreach ($res_query_teleservicios_ventas_x_producto as $value) {
        $value['query'] = $query_teleservicios_ventas_x_producto;
        $list_teleservicios_tabla_ventas_x_producto[] = $value;
    }

    $query_teleservicios_apuestas_calimaco_ventas_x_producto = "
        select Fecha
         ,sum(TicketsApostado)TksApostado
         ,sum(TicketsApostado)tickets
            ,ROUND(sum(Apostado),2)apostado
            ,ROUND(sum(Apostado)/sum(TicketsApostado),2)promedio
            ,sum(TicketsGanado)tickets_calculados
            ,ROUND(sum(Ganado),2)calculado
            ,ROUND(sum(Apostado)-sum(Ganado),2)resultado
            ,ROUND((sum(Apostado)-sum(Ganado))/sum(Apostado),2)hold
            ,sum(TicketsPagado)TicketsPagado
            ,sum(Pagado)Pagado
            ,ROUND((sum(TicketsPagado)/sum(TicketsGanado))*100,2)PorcentajeTksPagado,
            'Apuestas deportivas - Altenar' descripcion
            ,0 AS Distrib_Apostado
            ,0 AS Distrib_Resultado
        from (
         SELECT CAST(Fecha AS DATE) Fecha
          ,count(distinct TicketId)TicketsApostado
          ,sum(Apostado) as Apostado
          ,0 TicketsGanado
          ,0 AS Ganado
                ,0 TicketsPagado
                ,0 Pagado-- select *
         FROM at_altenar.Daily_summary_alt_bet CT
            inner join wwwapuestatotal_gestion.tbl_locales CL ON CT.CC_ID = CL.cc_id
         WHERE  CAST(Fecha AS DATE)>= '" . $fecha_inicial . "' 
         AND CAST(Fecha AS DATE) < '" . $fecha_final . "'
            AND UPPER(CL.nombre) NOT LIKE '%TEST%' 
            AND UPPER(CL.nombre) NOT like '%CAPACI%'
         group by CAST(Fecha AS DATE)
         UNION ALL
         SELECT  CAST(FechaCalculo AS DATE) Fecha
          ,0 TicketsApostado
          ,0 AS Apostado
          ,count(distinct TicketId)TicketsGanado
          ,sum(Ganado) as Ganado
                ,0 TicketsPagado
                ,0 Pagado
         FROM at_altenar.Daily_summary_alt_bet CT
            inner join wwwapuestatotal_gestion.tbl_locales CL ON CT.CC_ID = CL.cc_id
         WHERE CAST(FechaCalculo AS DATE)>= '" . $fecha_inicial . "' 
         AND CAST(FechaCalculo AS DATE) < '" . $fecha_final . "'
            AND UPPER(CL.nombre) NOT LIKE '%TEST%' 
            AND UPPER(CL.nombre) NOT like '%CAPACI%'
            group by CAST(FechaCalculo AS DATE)
            union all
            select cast(CT.created_at as date) Fecha
          ,0 TicketsApostado
                ,0 Apostado
                ,0 TicketsGanado
                ,0 Ganado
                ,COUNT(CT.id) AS TicketsPagado
          ,SUM(CT.monto) AS Pagado 
         FROM wwwapuestatotal_gestion.tbl_televentas_clientes_transaccion CT 
         INNER JOIN wwwapuestatotal_gestion.tbl_locales CL ON CT.cc_id = CL.cc_id
         WHERE CT.tipo_id = 5  -- Apuesta Pagada
         AND CT.estado = 1 -- aceptado
         AND CT.created_at  >= '" . $fecha_inicial . " 00:00:00'     /* Parametro a cambiar*/
         AND CT.created_at < '" . $fecha_final . " 00:00:00'     /* Parametro a cambiar*/
         AND CL.red_id = 8   -- TELESERVICIOS
         AND UPPER(CL.nombre) NOT LIKE '%TEST%' 
            AND UPPER(CL.nombre) NOT like '%CAPACI%'
            group by cast(CT.created_at as date)
            ) a
        -- group by Fecha;
    ";

    $res_query_teleservicios_apuestas_calimaco_ventas_x_producto = $mysqli->query($query_teleservicios_apuestas_calimaco_ventas_x_producto);

    if ($mysqli->error) {
        $result["error_query_teleservicios_apuestas_calimaco_ventas_x_producto"] = $mysqli->error;
    }

    $list_teleservicios_apuestas_calimaco_tabla_ventas_x_producto = array();

    if ($res_query_teleservicios_apuestas_calimaco_ventas_x_producto) {
        foreach ($res_query_teleservicios_apuestas_calimaco_ventas_x_producto as $value) {
            $value['query'] = $query_teleservicios_apuestas_calimaco_ventas_x_producto;
            $list_teleservicios_apuestas_calimaco_tabla_ventas_x_producto[] = $value;
        }
    }

    array_splice($list_teleservicios_tabla_ventas_x_producto , 0, 0, $list_teleservicios_apuestas_calimaco_tabla_ventas_x_producto);


    //array_unshift($list_teleservicios_tabla_ventas_x_producto, $list_teleservicios_apuestas_calimaco_tabla_ventas_x_producto);
   // array_splice($list_teleservicios_apuestas_calimaco_tabla_ventas_x_producto, 1, 0, $list_teleservicios_tabla_ventas_x_producto);

    if (count($list_teleservicios_tabla_ventas_x_producto) > 0) {

        foreach ($list_teleservicios_tabla_ventas_x_producto as $key => $value) {
            $resultado = (float)$value['resultado'];
            $apostado = (float)$value['apostado'];
            $value['hold'] = 0;

            if ($resultado <> 0 && $apostado <> 0) {
                $value['hold'] = ($resultado / $apostado) * 100;
            }

            $list_teleservicios_tabla_ventas_x_producto[$key] = $value;
        }

        $result_temp = array();

        $result_temp['descripcion'] = 'Total';
        $result_temp['tickets'] = 0;
        $result_temp['apostado'] = 0;
        $result_temp['promedio'] = 0;
        $result_temp['tickets_calculados'] = 0;
        $result_temp['calculado'] = 0;
        $result_temp['resultado'] = 0;
        $result_temp['hold'] = 0;
        $result_temp['TicketsPagado'] = 0;
        $result_temp['Pagado'] = 0;
        $result_temp['PorcentajeTksPagado'] = 0;
        $result_temp['query'] = '';
        $result_temp['Distrib_Apostado'] = 100;
        $result_temp['Distrib_Resultado'] = 100;

        foreach ($list_teleservicios_tabla_ventas_x_producto as $value) {
            $result_temp['tickets'] += $value["tickets"];
            $result_temp['apostado'] += $value["apostado"];
            $result_temp['calculado'] += $value["calculado"];
            $result_temp['resultado'] += $value["resultado"];
            $result_temp['tickets_calculados'] += $value["tickets_calculados"];
            $result_temp['TicketsPagado'] += $value["TicketsPagado"];
            $result_temp['Pagado'] += $value["Pagado"];
        }
        foreach ($list_teleservicios_tabla_ventas_x_producto as $key => $value) {
            $apostado = (float)$value['apostado'];
            $resultado = (float)$value['resultado'];
            if ($result_temp['apostado'] != 0) {
                $list_teleservicios_tabla_ventas_x_producto[$key]['Distrib_Apostado'] = ($apostado / $result_temp['apostado']) * 100;
            }
            if ($result_temp['resultado'] != 0) {
                $list_teleservicios_tabla_ventas_x_producto[$key]['Distrib_Resultado'] = ($resultado / $result_temp['resultado']) * 100;
            }
        }

        if ((float)$result_temp['apostado'] <> 0 && (float)$result_temp['tickets'] <> 0) {
            $result_temp['promedio'] = ((float)$result_temp['apostado'] / (float)$result_temp['tickets']);
        }

        if ((float)$result_temp['TicketsPagado'] <> 0 && (float)$result_temp['tickets_calculados'] <> 0) {
            $result_temp['PorcentajeTksPagado'] = ((float)$result_temp['TicketsPagado'] / (float)$result_temp['tickets_calculados']) * 100;
        }

        if ((float)$result_temp['resultado'] <> 0 && (float)$result_temp['apostado'] <> 0) {
            $result_temp['hold'] = ((float)$result_temp['resultado'] / (float)$result_temp['apostado']) * 100;
        }

        $list_teleservicios_tabla_ventas_x_producto[] = $result_temp;
    }

    $query_teleservicios_tabla_ventas_AD_x_caja = "
        select Fecha
            ,Caja
            ,sum(TicketsApostado)TksApostado
            ,ROUND(sum(Apostado),2)Apostado
            ,ROUND(sum(Apostado)/sum(TicketsApostado),2)Promedio
            ,sum(TicketsGanado)TksCalculados
            ,ROUND(sum(Ganado),2)Calculado
            ,ROUND(sum(Apostado)-sum(Ganado),2)Resultado
            ,ROUND((sum(Apostado)-sum(Ganado))/sum(Apostado),2)Hold
            ,sum(TicketsPagado)TicketsPagado
            ,ROUND(sum(Pagado),2)Pagado
            ,ROUND((sum(TicketsPagado)/sum(TicketsGanado))*100,2)PorcentajeTksPagado
            ,0 AS Distrib_Apostado
            ,0 AS Distrib_Resultado
        from (
         SELECT CAST(Fecha AS DATE) Fecha
          ,case when LocalId in (802,1522) then 'CAJA 7' else 'CAJA TV' end Caja
          ,count(distinct TicketId)TicketsApostado
          ,sum(Apostado) as Apostado
          ,0 TicketsGanado
          ,0 AS Ganado
                ,0 TicketsPagado
                ,0 Pagado-- select *
         FROM at_altenar.Daily_summary_alt_bet CT
            inner join wwwapuestatotal_gestion.tbl_locales CL ON CT.CC_ID = CL.cc_id
         WHERE  CAST(Fecha AS DATE)>= '" . $fecha_inicial . "'
         AND CAST(Fecha AS DATE) < '" . $fecha_final . "'
            AND UPPER(CL.nombre) NOT LIKE '%TEST%' 
            AND UPPER(CL.nombre) NOT like '%CAPACI%'
         group by CAST(Fecha AS DATE),case when LocalId in (802,1522) then 'CAJA 7' else 'CAJA TV' end
         UNION ALL
         SELECT  CAST(FechaCalculo AS DATE) Fecha
          ,case when LocalId in (802,1522) then 'CAJA 7' else 'CAJA TV' end Caja
          ,0 TicketsApostado
          ,0 AS Apostado
          ,count(distinct TicketId)TicketsGanado
          ,sum(Ganado) as Ganado
                ,0 TicketsPagado
                ,0 Pagado
         FROM at_altenar.Daily_summary_alt_bet CT
            inner join wwwapuestatotal_gestion.tbl_locales CL ON CT.CC_ID = CL.cc_id
         WHERE CAST(FechaCalculo AS DATE)>= '" . $fecha_inicial . "'
         AND CAST(FechaCalculo AS DATE) < '" . $fecha_final . "'
            AND UPPER(CL.nombre) NOT LIKE '%TEST%' 
            AND UPPER(CL.nombre) NOT like '%CAPACI%'
            group by CAST(FechaCalculo AS DATE),case when LocalId in (802,1522) then 'CAJA 7' else 'CAJA TV' end
            union all
            select cast(CT.created_at as date) Fecha
          ,case when CL.id in (802,1522) then 'CAJA 7' else 'CAJA TV' end Caja
          ,0 TicketsApostado
                ,0 Apostado
                ,0 TicketsGanado
                ,0 Ganado
                ,COUNT(CT.id) AS TicketsPagado
          ,SUM(CT.monto) AS Pagado -- select *
         FROM wwwapuestatotal_gestion.tbl_televentas_clientes_transaccion CT 
         INNER JOIN wwwapuestatotal_gestion.tbl_locales CL ON CT.cc_id = CL.cc_id
         WHERE CT.tipo_id = 5  -- Apuesta Pagada
         AND CT.estado = 1 -- aceptado
         AND CT.created_at  >= '" . $fecha_inicial . " 00:00:00'     /* Parametro a cambiar*/
         AND CT.created_at < '" . $fecha_final . " 00:00:00'    /* Parametro a cambiar*/
         AND CL.red_id = 8   -- TELESERVICIOS
         AND UPPER(CL.nombre) NOT LIKE '%TEST%' 
            AND UPPER(CL.nombre) NOT like '%CAPACI%'
            group by cast(CT.created_at as date),case when CL.id in (802,1522) then 'CAJA 7' else 'CAJA TV' end
            )a
        group by Caja;
    ";

    $res_query_teleservicios_tabla_ventas_AD_x_caja = $mysqli->query($query_teleservicios_tabla_ventas_AD_x_caja);

    if ($mysqli->error) {
        $result["error_query_teleservicios_tabla_ventas_AD_x_caja"] = $mysqli->error;
    }

    $list_teleservicios_tabla_ventas_AD_x_caja = array();

    if ($res_query_teleservicios_tabla_ventas_AD_x_caja) {
        foreach ($res_query_teleservicios_tabla_ventas_AD_x_caja as $value) {
            $value['query'] = $query_teleservicios_tabla_ventas_AD_x_caja;
            $list_teleservicios_tabla_ventas_AD_x_caja[] = $value;
        }
    } else {
        $value = [
            'Fecha' => '',
            'Caja' => '',
            'TksApostado' => 0.00,
            'Apostado' => 0.00,
            'Promedio' => 0.00,
            'TksCalculados' => 0.00,
            'Calculado' => 0.00,
            'Resultado' => 0.00,
            'Hold' => 0.00,
            'TicketsPagado' => 0.00,
            'Pagado' => 0.00,
            'PorcentajeTksPagado' => 0.00,
            'query' => $query_teleservicios_tabla_ventas_AD_x_caja
        ];
        $list_teleservicios_tabla_ventas_AD_x_caja[] = $value;
    }

    $query_teleservicios_tabla_ventas_jv_x_juego_update = "select Producto
    ,sum(num_tickets_apostado)num_tickets_apostado
        ,sum(total_apostado)total_apostado
    ,IFNULL(sum(total_apostado)/sum(num_tickets_apostado), 0) Promedio
        ,sum(num_tickets_calculado)num_tickets_calculado
        ,sum(total_tickets_calculado)Apostado
        ,IFNULL(sum(total_apostado),0)-IFNULL(sum(total_tickets_calculado), 0) Resultado
        ,IFNULL((sum(total_apostado)-sum(total_tickets_calculado))/sum(total_apostado), 0) hold
        ,sum(num_tickets_pagado)num_tickets_pagado
        ,sum(pagado)pagado
        ,IFNULL((sum(num_tickets_pagado)/sum(num_tickets_calculado))*100, 0)PorcentajeTksPagado 
        ,0 AS Distrib_Apostado
        ,0 AS Distrib_Resultado
    from (
    SELECT case when c.game not in ('dog racing','dog','Spin2Win Royale','World Cup') then 'S2W'
          when c.game in ('dog racing', 'dog') then 'Dog Racing'
                when c.game in ('Spin2Win Royale') then 'Spin2Win Royale'
                when c.game in ('World Cup') then 'World Cup' end Producto
    ,count(c.ticket_id) as num_tickets_apostado
    ,sum(c.stake_amount) as total_apostado
    -- ,(count(c.ticket_id)/ sum(c.stake_amount)) * 100 as Promedio
        ,0 num_tickets_calculado
    ,0 total_tickets_calculado
        ,0 num_tickets_pagado
    ,0 pagado 
    -- select l.*
    FROM wwwapuestatotal_gestion.tbl_repositorio_tickets_goldenrace as c
    inner join wwwapuestatotal_gestion.tbl_locales as l on c.local_id = l.id 
        where c.time_played >= '" . $fecha_inicial . " 00:00:00'
    and c.time_played < '" . $fecha_final . " 00:00:00'
    and c.ticket_status not in ('CANCELLED')
    and l.red_id = 8
    AND UPPER(l.nombre) NOT LIKE '%TEST%' 
    AND UPPER(l.nombre) NOT like '%CAPACI%'
    AND UPPER(l.nombre) != 'API TLS GoldenRace'
    group by case when c.game not in ('dog racing','dog','Spin2Win Royale','World Cup') then 'S2W'
          when c.game in ('dog racing', 'dog') then 'Dog Racing'
                when c.game in ('Spin2Win Royale') then 'Spin2Win Royale'
                when c.game in ('World Cup') then 'World Cup' end

    union all
    SELECT case when c.game not in ('dog racing','dog','Spin2Win Royale','World Cup') then 'S2W'
          when c.game in ('dog racing', 'dog') then 'Dog Racing'
                when c.game in ('Spin2Win Royale') then 'Spin2Win Royale'
                when c.game in ('World Cup') then 'World Cup' end
    ,0 as num_tickets_apostado
    ,0 as total_apostado
    -- ,0 Promedio
        ,sum(case when c.ticket_status in ('WON','PAIDOUT','PAID OUT') then 1 else 0 end) num_tickets_calculado
    ,sum(case when c.ticket_status in ('WON','PAIDOUT','PAID OUT') then c.winning_amount else 0 end) total_tickets_calculado
        ,sum(case when c.ticket_status in ('PAIDOUT','PAID OUT') then 1 else 0 end) num_tickets_pagado
    ,sum(case when c.ticket_status in ('PAIDOUT','PAID OUT') then c.winning_amount else 0 end) pagado 
    -- select l.*
    FROM wwwapuestatotal_gestion.tbl_repositorio_tickets_goldenrace as c
    inner join wwwapuestatotal_gestion.tbl_locales as l on c.local_id = l.id 
        where c.paid_out_time >= '" . $fecha_inicial . " 00:00:00'
    and c.paid_out_time < '" . $fecha_final . " 00:00:00'
    and c.estado = 0 
    and l.red_id = 8
    AND UPPER(l.nombre) NOT LIKE '%TEST%' 
    AND UPPER(l.nombre) NOT like '%CAPACI%'
    AND UPPER(l.nombre) != 'API TLS GoldenRace'
    group by case when c.game not in ('dog racing','dog','Spin2Win Royale','World Cup') then 'S2W'
          when c.game in ('dog racing', 'dog') then 'Dog Racing'
                when c.game in ('Spin2Win Royale') then 'Spin2Win Royale'
                when c.game in ('World Cup') then 'World Cup' end
    )a
    group by Producto;";

    $res_query_teleservicios_tabla_ventas_jv_x_juego_update = $mysqli->query($query_teleservicios_tabla_ventas_jv_x_juego_update);

    if ($mysqli->error) {
        $result["error_query_teleservicios_tabla_ventas_jv_x_juego_update"] = $mysqli->error;
    }

    $list_teleservicios_tabla_ventas_jv_x_juego_update = array();

    foreach ($res_query_teleservicios_tabla_ventas_jv_x_juego_update as $value) {
        $value['query'] = $query_teleservicios_tabla_ventas_jv_x_juego_update;
        $list_teleservicios_tabla_ventas_jv_x_juego_update[] = $value;
    }

    $query_teleservicios_tabla_ventas_recargas_web = "
    SELECT  'Recargas Web' AS concepto
    ,SUM(TB1.Transacciones) AS num_tickets_apostado
        ,SUM(TB1.monto) AS total_tickets_apostado
        ,SUM(TB1.Transacciones)/SUM(TB1.Monto) AS promedio
    FROM (
    SELECT COUNT(CT.id) AS Transacciones
      ,SUM(CT.total_Recarga) AS monto
    FROM wwwapuestatotal_gestion.tbl_televentas_clientes_transaccion CT 
        INNER JOIN  wwwapuestatotal_gestion.tbl_locales CL ON CT.cc_id = CL.cc_id
    WHERE CT.tipo_id = 2  -- Recarga Web
    AND CT.estado = 1 -- aceptado
    AND CT.created_at >= '" . $fecha_inicial . " 00:00:00'     /* Parametro a cambiar*/
    AND CT.created_at < '" . $fecha_final . " 00:00:00'    /* Parametro a cambiar*/
    AND CL.red_id = 8 -- TELESERVICIOS
    AND UPPER(CL.nombre) NOT LIKE '%TEST%' 
    GROUP BY CL.id
    ) TB1
    ORDER BY concepto;
    ;";

    $res_query_teleservicios_tabla_ventas_recargas_web = $mysqli->query($query_teleservicios_tabla_ventas_recargas_web);

    if ($mysqli->error) {
        $result["error_query_teleservicios_tabla_ventas_recargas_web"] = $mysqli->error;
    }

    $list_teleservicios_tabla_ventas_recargas_web = array();

    if ($res_query_teleservicios_tabla_ventas_recargas_web) {
        foreach ($res_query_teleservicios_tabla_ventas_recargas_web as $value) {
            $value['query'] = $query_teleservicios_tabla_ventas_recargas_web;
            $list_teleservicios_tabla_ventas_recargas_web[] = $value;
        }
    } else {
        $value = [
            'concepto' => 'Recargas Web',
            'num_tickets_apostado' => 0.00,
            'total_tickets_apostado' => 0.00,
            'promedio' => 0.00,
            'query' => $query_teleservicios_tabla_ventas_recargas_web
        ];
        $list_teleservicios_tabla_ventas_recargas_web[] = $value;
    }

    $query_teleservicios_tabla_ventas_terminales = "SELECT 
        'Terminal Deposit Tambo' AS concepto, 
        SUM(TB1.Transacciones) AS num_tickets_apostado, 
        SUM(TB1.monto) AS total_tickets_apostado,  
        SUM(TB1.Transacciones)/SUM(TB1.Monto) AS promedio
        FROM (
          SELECT COUNT(CT.id) AS Transacciones , SUM(CT.monto) AS monto
          FROM wwwapuestatotal_gestion.tbl_televentas_clientes_transaccion CT
          WHERE CT.tipo_id = 33  -- Terminal Deposit - Tambo
          AND	CT.estado = 1	-- aceptado
          AND	CT.created_at  >= '" . $fecha_inicial . " 00:00:00'  	/* Parametro a cambiar*/
          AND CT.created_at < '" . $fecha_final . " 00:00:00'	/* Parametro a cambiar*/
        ) TB1
        ORDER BY concepto
        ;";

    $res_query_teleservicios_tabla_ventas_terminales = $mysqli->query($query_teleservicios_tabla_ventas_terminales);

    if ($mysqli->error) {
        $result["error_query_teleservicios_tabla_ventas_terminales"] = $mysqli->error;
    }

    $list_teleservicios_tabla_ventas_terminales = array();

    if ($res_query_teleservicios_tabla_ventas_terminales) {
        foreach ($res_query_teleservicios_tabla_ventas_terminales as $value) {
            $value['query'] = $query_teleservicios_tabla_ventas_terminales;
            $list_teleservicios_tabla_ventas_terminales[] = $value;
        }
    } else {
        $value = [
            'concepto' => 'Terminal Deposit Tambo',
            'num_tickets_apostado' => 0.00,
            'total_tickets_apostado' => 0.00,
            'promedio' => 0.00,
            'query' => $query_teleservicios_tabla_ventas_terminales
        ];
        $list_teleservicios_tabla_ventas_terminales[] = $value;
    }

    $query_teleservicios_tabla_ventas_otros_pagos = "SELECT 
    descripcion AS concepto, 
    SUM(Transacciones) AS num_tickets_apostado, 
    SUM(ifnull(monto, 0)) AS total_tickets_apostado,  
      ifnull(SUM(ifnull(monto,0))/SUM(Transacciones), 0) as promedio
    FROM 	(
      SELECT	'Pagos Transacciones Tiendas y Terminales'  as descripcion,
        count(d.id) as Transacciones,
        sum(d.ganado) as monto
      FROM 	wwwapuestatotal_gestion.tbl_transacciones_detalle d LEFT JOIN wwwapuestatotal_gestion.tbl_locales l 
      ON 	(l.id = d.local_id) LEFT JOIN wwwapuestatotal_gestion.tbl_locales lp 
      ON 	(lp.id = d.paid_local_id)
      WHERE 	d.paid_day >= '" . $fecha_inicial . " 00:00:00' 			/* Parametro a cambiar*/
      AND 	d.paid_day < '" . $fecha_final . " 00:00:00'			/* Parametro a cambiar*/
      AND 	d.paid_local_id IS NOT NULL
      AND 	d.local_id != d.paid_local_id
      AND 	d.tipo = '1'	-- 1 ticket, 2 terminal, 3 cashdesk
      AND 	l.red_id != 7	-- Local generado
      AND 	lp.red_id = 8 	-- Televentas 
      AND	UPPER(lp.nombre) NOT LIKE '%TEST%' 
      AND l.zona_id != 9 -- Tiendas pago sur (glosario)  se deberia de quitar
      UNION ALL
      SELECT	'Pagos Transacciones Terminales Tambo'  as descripcion,
        count(d.id) as Transacciones,
        sum(d.ganado) as monto
      FROM 	wwwapuestatotal_gestion.tbl_transacciones_detalle d LEFT JOIN wwwapuestatotal_gestion.tbl_locales l 
      ON 	(l.id = d.local_id) LEFT JOIN wwwapuestatotal_gestion.tbl_locales lp 
      ON 	(lp.id = d.paid_local_id)
      WHERE 	d.paid_day >= '" . $fecha_inicial . " 00:00:00' 			/* Parametro a cambiar*/
      AND 	d.paid_day < '" . $fecha_final . " 00:00:00'			/* Parametro a cambiar*/
      AND 	d.paid_local_id IS NOT NULL
      AND 	d.local_id != d.paid_local_id
      AND 	d.tipo = '1'	-- 1 ticket, 2 terminal, 3 cashdesk
      AND 	l.red_id = 7	-- Local generado
      AND 	lp.red_id = 8 	-- Televentas
      AND	UPPER(lp.nombre) NOT LIKE '%TEST%'
      AND 	l.zona_id != 9 -- Tiendas pago sur (glosario)  se deberia de quitar
    ) TBREG
    GROUP BY descripcion";

    $res_query_teleservicios_tabla_ventas_otros_pagos = $mysqli->query($query_teleservicios_tabla_ventas_otros_pagos);

    if ($mysqli->error) {
        $result["error_query_teleservicios_tabla_ventas_otros_pagos"] = $mysqli->error;
    }

    $list_teleservicios_tabla_ventas_otros_pagos = array();

    if ($res_query_teleservicios_tabla_ventas_otros_pagos) {
        foreach ($res_query_teleservicios_tabla_ventas_otros_pagos as $value) {
            $value['query'] = $query_teleservicios_tabla_ventas_otros_pagos;
            $list_teleservicios_tabla_ventas_otros_pagos[] = $value;
        }
    } else {
        $value = [
            'concepto' => 'Pagos tickets Terminales',
            'num_tickets_apostado' => 0.00,
            'total_tickets_apostado' => 0.00,
            'promedio' => 0.00,
            'query' => $query_teleservicios_tabla_ventas_otros_pagos
        ];
        $list_teleservicios_tabla_ventas_otros_pagos[] = $value;
    }

    /**
     * END TELESERVICIOS
     */


    if (count($list_teleservicios_tabla_ventas_jv_x_juego_update) > 0) {


        foreach ($list_teleservicios_tabla_ventas_jv_x_juego_update as $key => $value) {
            $value['hold'] = ((float)$value['hold']) * 100;
            $list_teleservicios_tabla_ventas_jv_x_juego_update[$key] = $value;
        }

        $result_temp = array();
        $result_temp['Producto'] = 'Total';
        $result_temp['num_tickets_apostado'] = 0;
        $result_temp['total_apostado'] = 0;
        $result_temp['Promedio'] = 0;
        $result_temp['num_tickets_calculado'] = 0;
        $result_temp['Apostado'] = 0;
        $result_temp['Resultado'] = 0;
        $result_temp['hold'] = 0;
        $result_temp['num_tickets_pagado'] = 0;
        $result_temp['pagado'] = 0;
        $result_temp['PorcentajeTksPagado'] = 0;
        $result_temp['query'] = '';
        $result_temp['Distrib_Apostado'] = 100;
        $result_temp['Distrib_Resultado'] = 100;

        foreach ($list_teleservicios_tabla_ventas_jv_x_juego_update as $value) {
            $result_temp['num_tickets_apostado'] += $value["num_tickets_apostado"];
            $result_temp['total_apostado'] += $value["total_apostado"];
            $result_temp['Apostado'] += $value["Apostado"];
            $result_temp['Resultado'] += $value["Resultado"];
            $result_temp['num_tickets_calculado'] += $value["num_tickets_calculado"];
            $result_temp['num_tickets_pagado'] += $value["num_tickets_pagado"];
            $result_temp['pagado'] += $value["pagado"];
        }
        foreach ($list_teleservicios_tabla_ventas_jv_x_juego_update as $key => $value) {
            $apostado = (float)$value['Apostado'];
            $resultado = (float)$value['Resultado'];
            if ($result_temp['Apostado'] != 0) {
                $list_teleservicios_tabla_ventas_jv_x_juego_update[$key]['Distrib_Apostado'] = ($apostado / $result_temp['Apostado']) * 100;
            }
            if ($result_temp['Resultado'] != 0) {
                $list_teleservicios_tabla_ventas_jv_x_juego_update[$key]['Distrib_Resultado'] = ($resultado / $result_temp['Resultado']) * 100;
            }
        }

        if ((float)$result_temp['total_apostado'] <> 0 && (float)$result_temp['num_tickets_apostado'] <> 0) {
            $result_temp['Promedio'] = ((float)$result_temp['total_apostado'] / (float)$result_temp['num_tickets_apostado']);
        }

        if ((float)$result_temp['num_tickets_pagado'] <> 0 && (float)$result_temp['num_tickets_calculado'] <> 0) {
            $result_temp['PorcentajeTksPagado'] = ((float)$result_temp['num_tickets_pagado'] / (float)$result_temp['num_tickets_calculado']) * 100;
        }

        if ((float)$result_temp['Resultado'] <> 0 && (float)$result_temp['total_apostado'] <> 0) {
            $result_temp['hold'] = ((float)$result_temp['Resultado'] / (float)$result_temp['total_apostado']) * 100;
        }

        $list_teleservicios_tabla_ventas_jv_x_juego_update[] = $result_temp;
    }

    if (count($list_teleservicios_tabla_ventas_AD_x_caja) > 1) {

        $result_temp = array();
        $result_temp['Fecha'] =  date('Y-m-d');
        $result_temp['Caja'] = 'Total';
        $result_temp['TksApostado'] = 0;
        $result_temp['Apostado'] = 0;
        $result_temp['Promedio'] = 0;
        $result_temp['TksCalculados'] = 0;
        $result_temp['Calculado'] = 0;
        $result_temp['Resultado'] = 0;
        $result_temp['Hold'] = 0;
        $result_temp['TicketsPagado'] = 0;
        $result_temp['Pagado'] = 0;
        $result_temp['PorcentajeTksPagado'] = 0;
        $result_temp['query'] = '';
        $result_temp['Distrib_Apostado'] = 100;
        $result_temp['Distrib_Resultado'] = 100;

        foreach ($list_teleservicios_tabla_ventas_AD_x_caja as $value) {
            $result_temp['TksApostado'] += $value["TksApostado"];
            $result_temp['Apostado'] += $value["Apostado"];
            $result_temp['Promedio'] += $value["Promedio"];
            $result_temp['TksCalculados'] += $value["TksCalculados"];
            $result_temp['Calculado'] += $value["Calculado"];
            $result_temp['Resultado'] += $value["Resultado"];
            $result_temp['Hold'] += $value["Hold"];
            $result_temp['TicketsPagado'] += $value["TicketsPagado"];
            $result_temp['Pagado'] += $value["Pagado"];
            $result_temp['PorcentajeTksPagado'] += $value["PorcentajeTksPagado"];
        }
        foreach ($list_teleservicios_tabla_ventas_AD_x_caja as $key => $value) {
            $apostado = (float)$value['Apostado'];
            $resultado = (float)$value['Resultado'];
            if ($result_temp['Apostado'] != 0) {
                $list_teleservicios_tabla_ventas_AD_x_caja[$key]['Distrib_Apostado'] = ($apostado / $result_temp['Apostado']) * 100;
            }
            if ($result_temp['Resultado'] != 0) {
                $list_teleservicios_tabla_ventas_AD_x_caja[$key]['Distrib_Resultado'] = ($resultado / $result_temp['Resultado']) * 100;
            }
        }
        $list_teleservicios_tabla_ventas_AD_x_caja[] = $result_temp;
    }

    //*******************************************************************************************************************
    //*******************************************************************************************************************
    // RESULTADO
    //*******************************************************************************************************************
    //*******************************************************************************************************************
    $result = [];

    $result["result_teleservicios_tabla_ventas_x_producto"] = [];
    $result["result_teleservicios_tabla_ventas_AD_x_caja"] = [];
    $result["result_teleservicios_tabla_ventas_jv_x_juego_update"] = [];
    $result["result_teleservicios_tabla_ventas_recargas_web"] = [];
    $result["result_teleservicios_tabla_ventas_terminales"] = [];
    $result["result_teleservicios_tabla_ventas_otros_pagos"] = [];

    if (count($list_teleservicios_tabla_ventas_x_producto) == 0) {
        $result["http_code"] = 204;
        $result["status"] = "No hay transacciones.";
    } elseif (count($list_teleservicios_tabla_ventas_x_producto) > 0) {
        $result["http_code"] = 200;
        $result["status"] = "ok";
        $result["result_teleservicios_tabla_ventas_x_producto"] = $list_teleservicios_tabla_ventas_x_producto;
    }

    if (count($list_teleservicios_tabla_ventas_AD_x_caja) == 0) {
        $result["http_code"] = 204;
        $result["status"] = "No hay transacciones.";
    } elseif (count($list_teleservicios_tabla_ventas_AD_x_caja) > 0) {
        $result["http_code"] = 200;
        $result["status"] = "ok";
        $result["result_teleservicios_tabla_ventas_AD_x_caja"] = $list_teleservicios_tabla_ventas_AD_x_caja;
    }

    if (count($list_teleservicios_tabla_ventas_jv_x_juego_update) == 0) {
        $result["http_code"] = 204;
        $result["status"] = "No hay transacciones.";
    } elseif (count($list_teleservicios_tabla_ventas_jv_x_juego_update) > 0) {
        $result["http_code"] = 200;
        $result["status"] = "ok";
        $result["result_teleservicios_tabla_ventas_jv_x_juego_update"] = $list_teleservicios_tabla_ventas_jv_x_juego_update;
    }

    if (count($list_teleservicios_tabla_ventas_recargas_web) == 0) {
        $result["http_code"] = 204;
        $result["status"] = "No hay transacciones.";
    } elseif (count($list_teleservicios_tabla_ventas_recargas_web) > 0) {
        $result["http_code"] = 200;
        $result["status"] = "ok";
        $result["result_teleservicios_tabla_ventas_recargas_web"] = $list_teleservicios_tabla_ventas_recargas_web;
    }

    if (count($list_teleservicios_tabla_ventas_terminales) == 0) {
        $result["http_code"] = 204;
        $result["status"] = "No hay transacciones.";
    } elseif (count($list_teleservicios_tabla_ventas_terminales) > 0) {
        $result["http_code"] = 200;
        $result["status"] = "ok";
        $result["result_teleservicios_tabla_ventas_terminales"] = $list_teleservicios_tabla_ventas_terminales;
    }

    if (count($list_teleservicios_tabla_ventas_otros_pagos) == 0) {
        $result["http_code"] = 204;
        $result["status"] = "No hay transacciones.";
    } elseif (count($list_teleservicios_tabla_ventas_otros_pagos) > 0) {
        $result["http_code"] = 200;
        $result["status"] = "ok";
        $result["result_teleservicios_tabla_ventas_otros_pagos"] = $list_teleservicios_tabla_ventas_otros_pagos;
    }

    return $result;
}

//*******************************************************************************************************************
//*******************************************************************************************************************
// GENERAR TRANSACCIONES
//*******************************************************************************************************************
//*******************************************************************************************************************
function generar_transacciones($fecha)
{

    global $login;
    global $mysqli;
    $usuario_id = $login ? $login['id'] : 0;

    $_GET["today"] = date('Y-m-d', strtotime('+1 days', strtotime($fecha)));

    $dates = (object)[
        "today" => date('Y-m-d', strtotime($_GET["today"])),
        "yesterday" => date('Y-m-d', strtotime('-1 days', strtotime($_GET["today"]))),
        "yearmonth" => date('Y-m-01', strtotime('-1 days', strtotime($_GET["today"]))),
        "year" => date('Y-01-01', strtotime('-1 days', strtotime($_GET["today"]))),
        "startmonth" => date('Y-m-01', strtotime('-1 days', strtotime($_GET["today"]))),
        "startyear" => date('Y-01-01', strtotime('-1 days', strtotime($_GET["today"]))),
        "onlyday" => (int)date('d', strtotime('-1 days', strtotime($_GET["today"]))),
        "onlylastday" => (int)date("t", strtotime('-1 days', strtotime($_GET["today"]))),
    ];

    if (isset($_POST["log"])) {
        cron_print_log("Inicio");
    }

    //************************************************************************************************************
    // LOCALES
    $query = "
		SELECT 
		  lp.proveedor_id, 
		  lp.servicio_id,
		  lp.local_id,
		  l.cc_id
		FROM 
		  wwwapuestatotal_gestion.tbl_locales l 
		  LEFT JOIN wwwapuestatotal_gestion.tbl_local_proveedor_id lp ON (lp.local_id = l.id) 
		WHERE 
		  l.red_id = 8 
		  -- AND lp.estado = 1 
		  AND lp.proveedor_id > 0 
			";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["consulta_error_locales"] = $mysqli->error;
    }
    $rlocal_serv_prov = [];
    $rlocal_id = [];
    $rlocal_cc_id = [];
    while ($t = $temp->fetch_assoc()) {
        $rlocal_serv_prov[$t["servicio_id"]][] = $t["proveedor_id"];
        $rlocal_id[] = $t["local_id"];
        $rlocal_cc_id[] = $t["cc_id"];
    }
    $rlocal_id = array_unique($rlocal_id);
    $rlocal_cc_id = array_unique($rlocal_cc_id);
    if (isset($_POST["log"])) {
        cron_print_log("consulta_error_locales");
    }


    //************************************************************************************************************
    $resultado = [];

    //************************************************************************************************************
    // APUESTAS DEPORTIVAS
    $query = "
		SELECT
			1 as id_concepto,
			'Apuestas Deportivas' as descripcion,
			IFNULL(SUM(num_tickets), 0) AS tickets,
			IFNULL(SUM(total_apostado), 0) AS apostado,
			0 promedio,
			0 tickets_calculados,
			0 calculado,
			0 resultado,
			0 hold,
			0 tickets_ganados,
			IFNULL(SUM(pagado_en_otra_tienda)+(SUM(total_pagos_fisicos)-SUM(pagado_de_otra_tienda)), 0) AS ganado,
			0 porcentaje_pagados
		FROM wwwapuestatotal_gestion.tbl_transacciones_cabecera ca
		INNER JOIN tbl_locales l ON ca.local_id = l.id
		WHERE
		ca.fecha >= '{$dates->yesterday}' AND ca.fecha < '{$dates->today}'
		-- AND l.zona_id = 9
		AND l.red_id = 8
		AND ca.producto_id = 1
		AND ca.estado = 1
		-- AND l.estado = 1
		-- AND l.operativo = 1
		AND (l.fecha_fin_operacion IS NULL OR l.fecha_fin_operacion >= '{$dates->startmonth}')
		";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["ad_consulta"] = $query;
        $result["ad_error"] = $mysqli->error;
    } else {
        $resultado["dia"][] = $temp->fetch_assoc();
    }

    $query = "
			SELECT 
			  /*'Deportivas', */
			  count(b.col_Id) TicketsCalculados, 
			  IFNULL(sum(b.col_WinningAmount), 0) AS Calculado 
			from 
			  bc_apuestatotal.tbl_Bet as b 
			where 
			  b.col_state in (4,2) 
			  and b.col_CalcDate >= '{$dates->yesterday} 09:00:00' 
			  and b.col_CalcDate < '{$dates->today} 09:00:00' 
			  AND b.col_CashDeskId IN (" . implode(',', $rlocal_serv_prov[1]) . ")
		";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["ad_consulta_1"] = $query;
        $result["ad_error_1"] = $mysqli->error;
    } else {
        $temp_res_array = $temp->fetch_assoc();
        $resultado["dia"][0]["tickets_calculados"] = $temp_res_array["TicketsCalculados"];
        $resultado["dia"][0]["calculado"] = $temp_res_array["Calculado"];
    }

    $query = "
			SELECT 
			  /*'Deportivas', */
			  count(b.col_Id) TicketsPagados 
			from 
			  bc_apuestatotal.tbl_Bet as b 
			where 
			  b.col_state in (4,2) 
			  and b.col_PaidDate >= '{$dates->yesterday} 09:00:00' 
			  and b.col_PaidDate < '{$dates->today} 09:00:00' 
			  /* and l.red_id = 8*/
			  AND b.col_CashDeskId IN (" . implode(',', $rlocal_serv_prov[1]) . ")
		";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["ad_consulta_2"] = $query;
        $result["ad_error_2"] = $mysqli->error;
    } else {
        $resultado["dia"][0]["tickets_ganados"] = $temp->fetch_assoc()["TicketsPagados"];
    }

    if (isset($_POST["log"])) {
        cron_print_log("consulta_error_ad");
    }

    //************************************************************************************************************
    // Bingo
    $query = "
		SELECT
			2 as id_concepto,
			'Bingo' as descripcion,
			IFNULL(SUM(num_tickets), 0) AS tickets,
			IFNULL(SUM(total_apostado), 0) AS apostado,
			0 promedio,
			0 tickets_calculados,
			0 calculado,
			0 resultado,
			0 hold,
			0 tickets_ganados,
			-- IFNULL(SUM(pagado_en_otra_tienda)+(SUM(total_pagos_fisicos)-SUM(pagado_de_otra_tienda)), 0) AS ganado,
			0 ganado,
			0 porcentaje_pagados
		FROM wwwapuestatotal_gestion.tbl_transacciones_cabecera ca
		INNER JOIN wwwapuestatotal_gestion.tbl_locales l ON ca.local_id = l.id
		WHERE ca.fecha >= '{$dates->yesterday}' AND ca.fecha < '{$dates->today}'
		-- AND l.zona_id = 9
		AND l.red_id = 8
		AND ca.producto_id = 4
		AND ca.estado = 1
		-- AND l.estado = 1
		-- AND l.operativo = 1
		AND (l.fecha_fin_operacion IS NULL OR l.fecha_fin_operacion >= '{$dates->startmonth}')
		";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["bingo_consulta"] = $query;
        $result["bingo_error"] = $mysqli->error;
    } else {
        $resultado["dia"][] = $temp->fetch_assoc();
    }

    $query = "
			SELECT 
			  /*'Bingo', */
			  count(bt.ticket_id) as TicketsCalculados, 
			  IFNULL(sum(bt.winning), 0) as Calculado 
			FROM 
			  tbl_repositorio_bingo_tickets bt 
			  LEFT JOIN tbl_repositorio_bingo_games bg ON (bg.game_id = bt.game_id) 
			WHERE 
			  bg.finished_at >= '{$dates->yesterday}' 
			  AND bg.finished_at < '{$dates->today}' 
			  AND bt.status IN ('Paid', 'Won', 'Expired') 
			  /* and l.red_id = 8*/
			  AND bt.sell_local_id IN (" . implode(',', $rlocal_cc_id) . ")
		";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["bingo_consulta_1"] = $query;
        $result["bingo_error_1"] = $mysqli->error;
    } else {
        $temp_res_array = $temp->fetch_assoc();
        $resultado["dia"][1]["tickets_calculados"] = $temp_res_array["TicketsCalculados"];
        $resultado["dia"][1]["calculado"] = $temp_res_array["Calculado"];
    }

    $query = "
			SELECT 
			  /*'Bingo', */
			  IFNULL(sum(c.winning), 0) TotalTicketsPagados, count(c.ticket_id) TicketsPagados 
			FROM 
			  tbl_repositorio_bingo_tickets as c 
			  /* left join tbl_locales as l on c.sell_local_id = l.cc_id */
			where 
			  c.paid_at >= '{$dates->yesterday}' 
			  and c.paid_at < '{$dates->today}' 
			  and c.status = 'Paid' 
			  /* and l.red_id = 8*/
			  AND c.sell_local_id IN (" . implode(',', $rlocal_cc_id) . ")
		";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["bingo_consulta_2"] = $query;
        $result["bingo_error_2"] = $mysqli->error;
    } else {
        $temp_res_array = $temp->fetch_assoc();
        $resultado["dia"][1]["ganado"] = $temp_res_array["TotalTicketsPagados"];
        $resultado["dia"][1]["tickets_ganados"] = $temp_res_array["TicketsPagados"];
    }

    if (isset($_POST["log"])) {
        cron_print_log("consulta_error_bingo");
    }

    //************************************************************************************************************
    // S2W
    $query = "
		SELECT 
			3 as id_concepto,
			'Juegos Virtuales' as descripcion,
			IFNULL(SUM(num_tickets), 0) AS tickets,
			IFNULL(SUM(total_apostado), 0) AS apostado,
			0 promedio,
			0 tickets_calculados,
			0 calculado,
			0 resultado,
			0 hold,
			0 tickets_ganados,
			IFNULL(SUM(pagado_en_otra_tienda)+(SUM(total_pagos_fisicos)-SUM(pagado_de_otra_tienda)), 0) AS ganado,
			0 porcentaje_pagados
		FROM wwwapuestatotal_gestion.tbl_transacciones_cabecera ca
		INNER JOIN wwwapuestatotal_gestion.tbl_locales l ON ca.local_id = l.id
		WHERE ca.fecha >= '{$dates->yesterday}' AND ca.fecha < '{$dates->today}'
		-- AND l.zona_id = 9
		AND l.red_id = 8
		AND ca.producto_id = 2
		AND ca.estado = 1
		-- AND l.estado = 1
		-- AND l.operativo = 1
		AND (l.fecha_fin_operacion IS NULL OR l.fecha_fin_operacion >= '{$dates->startmonth}')
		";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["jv_consulta"] = $query;
        $result["jv_error"] = $mysqli->error;
    } else {
        $resultado["dia"][] = $temp->fetch_assoc();
    }

    $query = "
			SELECT 
			  /*'Virtuales', */
			  count(c.ticket_id) TicketsCalculados, 
			  IFNULL(sum(c.winning_amount), 0) AS Calculado 
			FROM 
			  tbl_repositorio_tickets_goldenrace as c 
			where 
			  c.time_played >= '{$dates->yesterday}' 
			  and c.time_played < '{$dates->today}' 
			  and c.ticket_status IN ('WON', 'PAIDOUT', 'EXPIRED', 'PAID OUT') 
			  and c.estado = 0 
			  AND c.local_id IN (" . implode(',', $rlocal_id) . ")
		";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["jv_consulta_1"] = $query;
        $result["jv_error_1"] = $mysqli->error;
    } else {
        $temp_res_array = $temp->fetch_assoc();
        $resultado["dia"][2]["tickets_calculados"] = $temp_res_array["TicketsCalculados"];
        $resultado["dia"][2]["calculado"] = $temp_res_array["Calculado"];
    }

    $query = "
			SELECT 
			  /*'Virtuales', */
			  count(c.ticket_id) TicketsPagados 
			FROM 
			  tbl_repositorio_tickets_goldenrace as c 
			where 
			  c.paid_out_time >= '{$dates->yesterday}' 
			  and c.paid_out_time < '{$dates->today}' 
			  and c.ticket_status in ('Paid Out', 'PAIDOUT') 
			  /*and l.red_id = 8*/
			  AND c.local_id IN (" . implode(',', $rlocal_id) . ")
		";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["jv_consulta_2"] = $query;
        $result["jv_error_2"] = $mysqli->error;
    } else {
        $resultado["dia"][2]["tickets_ganados"] = $temp->fetch_assoc()["TicketsPagados"];
    }

    if (isset($_POST["log"])) {
        cron_print_log("consulta_error_s2w");
    }


    //************************************************************************************************************
    // Caja TV
    $query = "
		SELECT
			5 as id_concepto,
			'Cajas TV' as descripcion,
			IFNULL(SUM(num_tickets), 0) AS tickets,
			IFNULL(SUM(total_apostado), 0) AS apostado,
			0 promedio,
			0 tickets_calculados,
			0 calculado,
			0 resultado,
			0 hold,
			0 tickets_ganados,
			IFNULL(SUM(pagado_en_otra_tienda)+(SUM(total_pagos_fisicos)-SUM(pagado_de_otra_tienda)), 0) AS ganado,
			0 porcentaje_pagados
		FROM wwwapuestatotal_gestion.tbl_transacciones_cabecera ca
		INNER JOIN tbl_locales l ON ca.local_id = l.id
		WHERE ca.fecha >= '{$dates->yesterday}' AND ca.fecha < '{$dates->today}'
			-- AND l.zona_id = 9
			AND l.red_id = 8
			AND ca.estado = 1
			AND ca.producto_id = 1
			-- AND l.estado = 1
			-- AND l.operativo = 1
			AND (l.fecha_fin_operacion IS NULL OR l.fecha_fin_operacion >= '{$dates->startmonth}')
			AND l.id!=802
		";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["cajastv_consulta"] = $query;
        $result["cajastv_error"] = $mysqli->error;
    } else {
        $resultado["dia"][] = $temp->fetch_assoc();
    }

    $query = "
			SELECT 
			  /*'Caja TV', */
			  count(b.col_Id) TicketsCalculados, 
			  IFNULL(sum(b.col_WinningAmount), 0) AS Calculado 
			from 
			  bc_apuestatotal.tbl_Bet as b 
			  left join bc_apuestatotal.tbl_CashDesk as c on b.col_CashDeskId = c.col_Id 
			  left join bc_apuestatotal.tbl_Betshop as bs on c.col_BetshopId = bs.col_Id 
			where 
			  b.col_state in (4,2) 
			  and b.col_CalcDate >= '{$dates->yesterday} 09:00:00' 
			  and b.col_CalcDate < '{$dates->today} 09:00:00' 
			  and bs.col_Id <> 109959 
			  /* and l.red_id = 8 */
			  AND b.col_CashDeskId IN (" . implode(',', $rlocal_serv_prov[1]) . ")
		";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["cajastv_consulta_1"] = $query;
        $result["cajastv_error_1"] = $mysqli->error;
    } else {
        $temp_res_array = $temp->fetch_assoc();
        $resultado["dia"][3]["tickets_calculados"] = $temp_res_array["TicketsCalculados"];
        $resultado["dia"][3]["calculado"] = $temp_res_array["Calculado"];
    }

    $query = "
			SELECT 
			  /*'Caja TV', */
			  count(b.col_Id) TicketsPagados 
			from 
			  bc_apuestatotal.tbl_Bet as b 
			  left join bc_apuestatotal.tbl_CashDesk as c on b.col_CashDeskId = c.col_Id 
			  left join bc_apuestatotal.tbl_Betshop as bs on c.col_BetshopId = bs.col_Id 
			where 
			  b.col_state in (4,2) 
			  and b.col_PaidDate >= '{$dates->yesterday} 09:00:00' 
			  and b.col_PaidDate < '{$dates->today} 09:00:00' 
			  and bs.col_Id <> 109959 
			  /* and l.red_id = 8 */
			  AND b.col_CashDeskId IN (" . implode(',', $rlocal_serv_prov[1]) . ")
		";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["cajastv_consulta_2"] = $query;
        $result["cajastv_error_2"] = $mysqli->error;
    } else {
        $resultado["dia"][3]["tickets_ganados"] = $temp->fetch_assoc()["TicketsPagados"];
    }

    if (isset($_POST["log"])) {
        cron_print_log("consulta_error_cajastv");
    }

    //************************************************************************************************************
    // Caja 7
    $query = "
		SELECT
			6 as id_concepto,
			'Caja 7' as descripcion,
			IFNULL(SUM(num_tickets), 0) AS tickets,
			IFNULL(SUM(total_apostado), 0) AS apostado,
			0 promedio,
			0 tickets_calculados,
			IFNULL(SUM(total_ganado), 0) AS calculado,
			0 resultado,
			0 hold,
			0 tickets_ganados,
			SUM(pagado_en_otra_tienda)+(SUM(total_pagos_fisicos)-SUM(pagado_de_otra_tienda)) AS ganado,
			0 porcentaje_pagados
		FROM wwwapuestatotal_gestion.tbl_transacciones_cabecera ca
		INNER JOIN tbl_locales l ON ca.local_id = l.id
		WHERE ca.fecha >= '{$dates->yesterday}' AND ca.fecha < '{$dates->today}'
			-- AND l.zona_id = 9
			AND l.red_id = 8
			AND ca.producto_id = 1
			AND ca.estado = 1
			-- AND l.estado = 1
			-- AND l.operativo = 1
			AND (l.fecha_fin_operacion IS NULL OR l.fecha_fin_operacion >= '{$dates->startmonth}')
			AND l.id=802
		";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["cajas7_consulta"] = $query;
        $result["cajas7_error"] = $mysqli->error;
    } else {
        $resultado["dia"][] = $temp->fetch_assoc();
    }

    $query = "
			SELECT 
			  /*'Caja 7', */
			  count(b.col_Id) TicketsCalculados 
			from 
			  bc_apuestatotal.tbl_Bet as b 
			  left join bc_apuestatotal.tbl_CashDesk as c on b.col_CashDeskId = c.col_Id 
			  left join bc_apuestatotal.tbl_Betshop as bs on c.col_BetshopId = bs.col_Id 
			where 
			  b.col_state in (4,2) 
			  and b.col_CalcDate >= '{$dates->yesterday} 09:00:00' 
			  and b.col_CalcDate < '{$dates->today} 09:00:00' 
			  and bs.col_Id = 109959 
			  /* and l.red_id = 8 */
			  AND b.col_CashDeskId IN (" . implode(',', $rlocal_serv_prov[1]) . ")
		";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["cajas7_consulta_1"] = $query;
        $result["cajas7_error_1"] = $mysqli->error;
    } else {
        $resultado["dia"][4]["tickets_calculados"] = $temp->fetch_assoc()["TicketsCalculados"];
    }

    $query = "
			SELECT 
			  /*'Caja 7', */
			  count(b.col_Id) TicketsPagados 
			from 
			  bc_apuestatotal.tbl_Bet as b 
			  left join bc_apuestatotal.tbl_CashDesk as c on b.col_CashDeskId = c.col_Id 
			  left join bc_apuestatotal.tbl_Betshop as bs on c.col_BetshopId = bs.col_Id 
			where 
			  b.col_state in (4,2) 
			  and b.col_PaidDate >= '{$dates->yesterday} 09:00:00' 
			  and b.col_PaidDate < '{$dates->today} 09:00:00' 
			  and bs.col_Id = 109959 
			  /* and l.red_id = 8 */
			  AND b.col_CashDeskId IN (" . implode(',', $rlocal_serv_prov[1]) . ")
		";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["cajas7_consulta_2"] = $query;
        $result["cajas7_error_2"] = $mysqli->error;
    } else {
        $resultado["dia"][4]["tickets_ganados"] = $temp->fetch_assoc()["TicketsPagados"];
    }

    if (isset($_POST["log"])) {
        cron_print_log("consulta_error_cajas7");
    }


    //************************************************************************************************************
    // JUEGOS VIRTUALES
    $result_temp = array();
    $result_temp['id_concepto'] = 12;
    $result_temp['descripcion'] = 'Total';
    $result_temp['tickets'] = 0;
    $result_temp['apostado'] = 0;
    $result_temp['promedio'] = 0;
    $result_temp['tickets_calculados'] = 0;
    $result_temp['calculado'] = 0;
    $result_temp['resultado'] = 0;
    $result_temp['hold'] = 0;
    $result_temp['tickets_ganados'] = 0;
    $result_temp['ganado'] = 0;
    $result_temp['porcentaje_pagados'] = 0;

    //************************************************************************************************************
    //************************************************************************************************************
    // S2W
    $result_temp['descripcion'] = 'S2W';
    $resultado["dia"][] = $result_temp;
    /*
    SELECT
      count(c.ticket_id) 'Tks Apostado',
      sum(c.stake_amount) as Apostado,
      (
        count(c.ticket_id)/ sum(c.stake_amount)
      ) * 100 as Promedio
    FROM
      tbl_repositorio_tickets_goldenrace as c
      left join tbl_locales as l on c.local_id = l.id
    where
      c.time_played >= '2022-05-02'
      and c.time_played < '2022-05-03'
      -- and c.ticket_status not in ('CANCELLED','Expired','PENDING')
      and l.red_id = 8
      and c.estado = 0
      -- and c.game not in ('dog racing', 'dog')
      and c.game not in ('dog racing','dog','Spin2Win Royale','World Cup')
    */
    $query = "
			SELECT 
			  count(c.ticket_id) as num_tickets_apostado, 
			  sum(c.stake_amount) as total_apostado, 
			  (
			    count(c.ticket_id)/ sum(c.stake_amount)
			  ) * 100 as Promedio 
			FROM 
			  tbl_repositorio_tickets_goldenrace as c 
			  -- left join tbl_locales as l on c.local_id = l.id 
			where 
			  c.time_played >= '{$dates->yesterday}' 
			  and c.time_played < '{$dates->today}' 
			  -- and c.ticket_status not in ('CANCELLED') 
			  -- and c.ticket_status not in ('CANCELLED','Expired','PENDING') 
			  -- and l.red_id = 8 
			  and c.estado = 0 
			  -- and c.game <> 'dog'
			  -- and c.game not in ('dog racing', 'dog')
			  and c.game not in ('dog racing','dog','Spin2Win Royale','World Cup')
			  AND c.local_id IN (" . implode(',', $rlocal_id) . ") 
		";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["s2w_consulta"] = $query;
        $result["s2w_error"] = $mysqli->error;
    } else {
        $temp_res_array = $temp->fetch_assoc();
        $resultado["dia"][5]["tickets"] = $temp_res_array["num_tickets_apostado"];
        $resultado["dia"][5]["apostado"] = $temp_res_array["total_apostado"];
    }
    /*
    SELECT
      -- 'Virtuales',
      count(c.ticket_id) 'Tks Calculados',
      sum(c.winning_amount) as calculado
    FROM
      tbl_repositorio_tickets_goldenrace as c
      left join tbl_locales as l on c.local_id = l.id
    where
      c.paid_out_time >= '2022-06-01'
      and c.paid_out_time < '2022-06-02'
      and c.ticket_status in ('WON', 'PAIDOUT', 'PAID OUT')
      and l.red_id = 8
      and c.estado = 0
      -- and c.game not in ('dog racing', 'dog')
      and c.game not in ('dog racing','dog','Spin2Win Royale','World Cup')

    */

    $query = "
			SELECT 
			  
			  /*'Virtuales', */
			  count(c.ticket_id) as num_tickets_calculado, 
			  sum(c.winning_amount) as total_tickets_calculado 
			FROM 
			  tbl_repositorio_tickets_goldenrace as c 
			  -- left join tbl_locales as l on c.local_id = l.id 
			where 
			  c.paid_out_time >= '{$dates->yesterday}' 
			  and c.paid_out_time < '{$dates->today}' 
			  -- and c.ticket_status in ('WON') 
			  and c.ticket_status in ('WON','PAIDOUT',  'PAID OUT' ) 
			  -- and l.red_id = 8 
			  and c.estado = 0 
			  -- and c.game <> 'dog racing'
			  -- and c.game not in ('dog racing', 'dog')
			  and c.game not in ('dog racing','dog','Spin2Win Royale','World Cup')
			  AND c.local_id IN (" . implode(',', $rlocal_id) . ") 
		";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["s2w_consulta_1"] = $query;
        $result["s2w_error_1"] = $mysqli->error;
    } else {
        $temp_res_array = $temp->fetch_assoc();
        $resultado["dia"][5]["tickets_calculados"] = $temp_res_array["num_tickets_calculado"];
        $resultado["dia"][5]["calculado"] = $temp_res_array["total_tickets_calculado"];
    }
    /*
    SELECT
      -- 'Virtuales',
      count(c.ticket_id) 'Tks Pag.',
      sum(c.winning_amount) as pagos
    FROM
      tbl_repositorio_tickets_goldenrace as c
      left join tbl_locales as l on c.local_id = l.id
    where
      c.paid_out_time >= '2022-06-01'
      and c.paid_out_time < '2022-06-02'
      and c.ticket_status in ('PAIDOUT', 'PAID OUT')
      and l.red_id = 8
      and c.estado = 0
      -- and c.game not in ('dog racing', 'dog')
      and c.game  not in ('dog racing','dog','Spin2Win Royale','World Cup')
    */
    $query = "
			SELECT 
			  /*'Virtuales', */
			  count(c.ticket_id) num_tickets_pagado, 
			  sum(c.winning_amount) as pagado
			FROM 
			  tbl_repositorio_tickets_goldenrace as c 
			  -- left join tbl_locales as l on c.local_id = l.id 
			where 
			  c.paid_out_time >= '{$dates->yesterday}' 
			  and c.paid_out_time < '{$dates->today}' 
			  and c.ticket_status in ('PAIDOUT', 'PAID OUT') 
			  -- and l.red_id = 8 
			  and c.estado = 0 
			  -- and c.game <> 'dog'
			  -- and c.game not in ('dog racing', 'dog')
			  and c.game  not in ('dog racing','dog','Spin2Win Royale','World Cup')
			  AND c.local_id IN (" . implode(',', $rlocal_id) . ") 
		";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["s2w_consulta_2"] = $query;
        $result["s2w_error_2"] = $mysqli->error;
    } else {
        $temp_res_array = $temp->fetch_assoc();
        $resultado["dia"][5]["tickets_ganados"] = $temp_res_array["num_tickets_pagado"];
        $resultado["dia"][5]["ganado"] = $temp_res_array["pagado"];
    }

    //************************************************************************************************************
    //************************************************************************************************************
    // DOG RACING
    $result_temp['descripcion'] = 'Dog Racing';
    $result_temp['id_concepto'] = 13;
    $resultado["dia"][] = $result_temp;
    /*
    SELECT
      count(c.ticket_id) 'Tks Apostado',
      sum(c.stake_amount) as apostado,
      (
        count(c.ticket_id)/ sum(c.stake_amount)
      ) * 100 as promedio
    FROM
      tbl_repositorio_tickets_goldenrace as c
      left join tbl_locales as l on c.local_id = l.id
    where
      c.time_played >= '2022-05-02'
      and c.time_played < '2022-05-03'
      -- and c.ticket_status not in ('CANCELLED','Expired','PENDING')
      and l.red_id = 8
      and c.estado = 0
      and c.game in ('dog racing', 'dog')

    */
    $query = "
			SELECT 
			  count(c.ticket_id) as num_tickets_apostado,
			  sum(c.stake_amount) as total_apostado, 
			  (
			    count(c.ticket_id)/ sum(c.stake_amount)
			  ) * 100 as promedio 
			FROM 
			  tbl_repositorio_tickets_goldenrace as c 
			  -- left join tbl_locales as l on c.local_id = l.id 
			where 
			  c.time_played >= '{$dates->yesterday}' 
			  and c.time_played < '{$dates->today}' 
			  -- and c.ticket_status not in ('CANCELLED','Expired','PENDING') 
			  -- and c.ticket_status not in ('CANCELLED') 
			  -- and l.red_id = 8 
			  and c.estado = 0 
			  -- and c.game = 'dog'
			  and c.game in ('dog racing', 'dog')
			  AND c.local_id IN (" . implode(',', $rlocal_id) . ") 
		";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["dog_consulta"] = $query;
        $result["dog_error"] = $mysqli->error;
    } else {
        $temp_res_array = $temp->fetch_assoc();
        $resultado["dia"][6]["tickets"] = $temp_res_array["num_tickets_apostado"];
        $resultado["dia"][6]["apostado"] = $temp_res_array["total_apostado"];
    }

    /*
    SELECT
      -- 'Virtuales',
      count(c.ticket_id) tickets,
      sum(c.winning_amount) as calculado
    FROM
      tbl_repositorio_tickets_goldenrace as c
      left join tbl_locales as l on c.local_id = l.id
    where
      c.paid_out_time >= '2022-06-01'
      and c.paid_out_time < '2022-06-02'
      and c.ticket_status in ('WON', 'PAIDOUT', 'PAID OUT')
      and l.red_id = 8
      and c.estado = 0
      and c.game in ('dog racing', 'dog')

    */
    $query = "
			SELECT 
			  /*'Virtuales', */
			  count(c.ticket_id) num_tickets_calculado, 
			  sum(c.winning_amount) as total_tickets_calculado 
			FROM 
			  tbl_repositorio_tickets_goldenrace as c 
			  -- left join tbl_locales as l on c.local_id = l.id 
			where 
			  c.paid_out_time >= '{$dates->yesterday}' 
			  and c.paid_out_time < '{$dates->today}' 
			  -- and c.ticket_status in ('WON') 
			  and c.ticket_status in ('WON','PAIDOUT',  'PAID OUT' ) 
			  -- and l.red_id = 8 
			  and c.estado = 0 
			  -- and c.game = 'dog racing' 
			  and c.game in ('dog racing', 'dog')
			  AND c.local_id IN (" . implode(',', $rlocal_id) . ") 
		";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["dog_consulta_1"] = $query;
        $result["dog_error_1"] = $mysqli->error;
    } else {
        $temp_res_array = $temp->fetch_assoc();
        $resultado["dia"][6]["tickets_calculados"] = $temp_res_array["num_tickets_calculado"];
        $resultado["dia"][6]["calculado"] = $temp_res_array["total_tickets_calculado"];
    }
    /*
    SELECT
      -- 'Virtuales',
      count(c.ticket_id) 'Tks Pag.',
      sum(c.winning_amount) as pagos
    FROM
      tbl_repositorio_tickets_goldenrace as c
      left join tbl_locales as l on c.local_id = l.id
    where
      c.paid_out_time >= '2022-06-01'
      and c.paid_out_time < '2022-06-02'
      and c.ticket_status in ('PAIDOUT', 'PAID OUT')
      and l.red_id = 8
      and c.estado = 0
      and c.game in ('dog racing', 'dog')

    */
    $query = "
			SELECT 
			  /*'Virtuales', */
			  count(c.ticket_id) num_tickets_pagado, 
			  sum(c.winning_amount) as pagado 
			FROM 
			  tbl_repositorio_tickets_goldenrace as c 
			  -- left join tbl_locales as l on c.local_id = l.id 
			where 
			  c.paid_out_time >= '{$dates->yesterday}' 
			  and c.paid_out_time < '{$dates->today}' 
			  and c.ticket_status in ('PAIDOUT', 'PAID OUT') 
			  -- and l.red_id = 8 
			  and c.estado = 0 
			  -- and c.game = 'dog' 
			  and c.game in ('dog racing', 'dog')
			  AND c.local_id IN (" . implode(',', $rlocal_id) . ") 
		";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["dog_consulta_2"] = $query;
        $result["dog_error_2"] = $mysqli->error;
    } else {
        $temp_res_array = $temp->fetch_assoc();
        $resultado["dia"][6]["tickets_ganados"] = $temp_res_array["num_tickets_pagado"];
        $resultado["dia"][6]["ganado"] = $temp_res_array["pagado"];
    }
    //var_dump($resultado["dia"][6]);
    //************************************************************************************************************
    //************************************************************************************************************
    // SPIN2WIN ROYALE
    $result_temp['descripcion'] = 'S2W Royale';
    $result_temp['id_concepto'] = 14;
    $resultado["dia"][] = $result_temp;
    /*
    SELECT
      count(c.ticket_id) 'Tks Apostado',
      sum(c.stake_amount) as apostado,
      (
        count(c.ticket_id)/ sum(c.stake_amount)
      ) * 100 as promedio
    FROM
      tbl_repositorio_tickets_goldenrace as c
      left join tbl_locales as l on c.local_id = l.id
    where
      c.time_played >= '2022-05-02'
      and c.time_played < '2022-05-03' #and c.ticket_status not in ('CANCELLED','Expired','PENDING')
      and l.red_id = 8
      and c.estado = 0
      and c.game in ('Spin2Win Royale')

    */
    $query = "
			SELECT 
			  count(c.ticket_id) as num_tickets_apostado,
			  sum(c.stake_amount) as total_apostado, 
			  (
			    count(c.ticket_id)/ sum(c.stake_amount)
			  ) * 100 as promedio 
			FROM 
			  tbl_repositorio_tickets_goldenrace as c 
			  -- left join tbl_locales as l on c.local_id = l.id 
			where 
			  c.time_played >= '{$dates->yesterday}' 
			  and c.time_played < '{$dates->today}'  
			  -- and l.red_id = 8 
			  and c.estado = 0 
			  and c.game in ('Spin2Win Royale')
			  AND c.local_id IN (" . implode(',', $rlocal_id) . ") 
		";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["s2wroyale_consulta"] = $query;
        $result["s2wroyale_error"] = $mysqli->error;
    } else {
        $temp_res_array = $temp->fetch_assoc();
        $resultado["dia"][7]["tickets"] = $temp_res_array["num_tickets_apostado"];
        $resultado["dia"][7]["apostado"] = $temp_res_array["total_apostado"];
    }

    /*
    SELECT
      count(c.ticket_id) tickets,
      sum(c.winning_amount) as calculado
    FROM
      tbl_repositorio_tickets_goldenrace as c
      left join tbl_locales as l on c.local_id = l.id
    where
      c.paid_out_time >= '2022-06-01'
      and c.paid_out_time < '2022-06-02'
      and c.ticket_status in ('WON', 'PAIDOUT', 'PAID OUT')
      and l.red_id = 8
      and c.estado = 0
      and c.game in ('Spin2Win Royale')


    */
    $query = "
			SELECT 
			  /*'Virtuales', */
			  count(c.ticket_id) num_tickets_calculado, 
			  sum(c.winning_amount) as total_tickets_calculado 
			FROM 
			  tbl_repositorio_tickets_goldenrace as c 
			  -- left join tbl_locales as l on c.local_id = l.id 
			where 
			  c.paid_out_time >= '{$dates->yesterday}' 
			  and c.paid_out_time < '{$dates->today}' 
			  -- and c.ticket_status in ('WON') 
			  and c.ticket_status in ('WON','PAIDOUT',  'PAID OUT' ) 
			  -- and l.red_id = 8 
			  and c.estado = 0 
			  and c.game in ('Spin2Win Royale')
			  AND c.local_id IN (" . implode(',', $rlocal_id) . ") 
		";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["s2wroyale_consulta_1"] = $query;
        $result["s2wroyale_error_1"] = $mysqli->error;
    } else {
        $temp_res_array = $temp->fetch_assoc();
        $resultado["dia"][7]["tickets_calculados"] = $temp_res_array["num_tickets_calculado"];
        $resultado["dia"][7]["calculado"] = $temp_res_array["total_tickets_calculado"];
    }
    /*
    SELECT
      count(c.ticket_id) 'Tks Pag.',
      sum(c.winning_amount) as pagos
    FROM
      tbl_repositorio_tickets_goldenrace as c
      left join tbl_locales as l on c.local_id = l.id
    where
      c.paid_out_time >= '2022-06-01'
      and c.paid_out_time < '2022-06-02'
      and c.ticket_status in ('PAIDOUT', 'PAID OUT')
      and l.red_id = 8
      and c.estado = 0
      and c.game in ('Spin2Win Royale')

    */
    $query = "
			SELECT 
			  /*'Virtuales', */
			  count(c.ticket_id) num_tickets_pagado, 
			  sum(c.winning_amount) as pagado 
			FROM 
			  tbl_repositorio_tickets_goldenrace as c 
			  -- left join tbl_locales as l on c.local_id = l.id 
			where 
			  c.paid_out_time >= '{$dates->yesterday}' 
			  and c.paid_out_time < '{$dates->today}' 
			  and c.ticket_status in ('PAIDOUT', 'PAID OUT') 
			  -- and l.red_id = 8 
			  and c.estado = 0 
			  and c.game in ('Spin2Win Royale')
			  AND c.local_id IN (" . implode(',', $rlocal_id) . ") 
		";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["s2wroyale_consulta_2"] = $query;
        $result["s2wroyale_error_2"] = $mysqli->error;
    } else {
        $temp_res_array = $temp->fetch_assoc();
        $resultado["dia"][7]["tickets_ganados"] = $temp_res_array["num_tickets_pagado"];
        $resultado["dia"][7]["ganado"] = $temp_res_array["pagado"];
    }

    //************************************************************************************************************
    //************************************************************************************************************
    // World Cup
    $result_temp['id_concepto'] = 15;
    $result_temp['descripcion'] = 'World Cup';
    $resultado["dia"][] = $result_temp;
    /*
    SELECT
      count(c.ticket_id) 'Tks Apostado',
      sum(c.stake_amount) as Apostado,
      (
        count(c.ticket_id)/ sum(c.stake_amount)
      ) * 100 as Promedio
    FROM
      tbl_repositorio_tickets_goldenrace as c
      left join tbl_locales as l on c.local_id = l.id
    where
      c.time_played >= '2022-05-02'
      and c.time_played < '2022-05-03' #and c.ticket_status not in ('CANCELLED','Expired','PENDING')
      and l.red_id = 8
      and c.estado = 0
      and c.game not in ('World Cup')

    */
    $query = "
			SELECT 
			  count(c.ticket_id) as num_tickets_apostado,
			  sum(c.stake_amount) as total_apostado, 
			  (
			    count(c.ticket_id)/ sum(c.stake_amount)
			  ) * 100 as promedio 
			FROM 
			  tbl_repositorio_tickets_goldenrace as c 
			  -- left join tbl_locales as l on c.local_id = l.id 
			where 
			  c.time_played >= '{$dates->yesterday}' 
			  and c.time_played < '{$dates->today}' 
			  -- and l.red_id = 8 
			  and c.estado = 0 
			  and c.game in ('World Cup')
			  AND c.local_id IN (" . implode(',', $rlocal_id) . ") 
		";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["worldcup_consulta"] = $query;
        $result["worldcup_error"] = $mysqli->error;
    } else {
        $temp_res_array = $temp->fetch_assoc();
        $resultado["dia"][8]["tickets"] = $temp_res_array["num_tickets_apostado"];
        $resultado["dia"][8]["apostado"] = $temp_res_array["total_apostado"];
    }

    /*
    SELECT
      count(c.ticket_id) tickets,
      sum(c.winning_amount) as calculado
    FROM
      tbl_repositorio_tickets_goldenrace as c
      left join tbl_locales as l on c.local_id = l.id
    where
      c.paid_out_time >= '2022-06-01'
      and c.paid_out_time < '2022-06-02'
      and c.ticket_status in ('WON', 'PAIDOUT', 'PAID OUT')
      and l.red_id = 8
      and c.estado = 0
      and c.game in ('World Cup')

    */
    $query = "
			SELECT 
			  /*'Virtuales', */
			  count(c.ticket_id) num_tickets_calculado, 
			  sum(c.winning_amount) as total_tickets_calculado 
			FROM 
			  tbl_repositorio_tickets_goldenrace as c 
			  -- left join tbl_locales as l on c.local_id = l.id 
			where 
			  c.paid_out_time >= '{$dates->yesterday}' 
			  and c.paid_out_time < '{$dates->today}' 
			  -- and c.ticket_status in ('WON') 
			  and c.ticket_status in ('WON','PAIDOUT',  'PAID OUT' ) 
			  -- and l.red_id = 8 
			  and c.estado = 0 
			  and c.game in ('World Cup')
			  AND c.local_id IN (" . implode(',', $rlocal_id) . ") 
		";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["worldcup_consulta_1"] = $query;
        $result["worldcup_error_1"] = $mysqli->error;
    } else {
        $temp_res_array = $temp->fetch_assoc();
        $resultado["dia"][8]["tickets_calculados"] = $temp_res_array["num_tickets_calculado"];
        $resultado["dia"][8]["calculado"] = $temp_res_array["total_tickets_calculado"];
    }
    /*
    SELECT
      count(c.ticket_id) 'Tks Pag.',
      sum(c.winning_amount) as pagos
    FROM
      tbl_repositorio_tickets_goldenrace as c
      left join tbl_locales as l on c.local_id = l.id
    where
      c.paid_out_time >= '2022-06-01'
      and c.paid_out_time < '2022-06-02'
      and c.ticket_status in ('PAIDOUT', 'PAID OUT')
      and l.red_id = 8
      and c.estado = 0
      and c.game in ('World Cup')

    */
    $query = "
			SELECT 
			  /*'Virtuales', */
			  count(c.ticket_id) num_tickets_pagado, 
			  sum(c.winning_amount) as pagado 
			FROM 
			  tbl_repositorio_tickets_goldenrace as c 
			  -- left join tbl_locales as l on c.local_id = l.id 
			where 
			  c.paid_out_time >= '{$dates->yesterday}' 
			  and c.paid_out_time < '{$dates->today}' 
			  and c.ticket_status in ('PAIDOUT', 'PAID OUT') 
			  -- and l.red_id = 8 
			  and c.estado = 0 
			  and c.game in ('World Cup')
			  AND c.local_id IN (" . implode(',', $rlocal_id) . ") 
		";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["worldcup_consulta_2"] = $query;
        $result["worldcup_error_2"] = $mysqli->error;
    } else {
        $temp_res_array = $temp->fetch_assoc();
        $resultado["dia"][8]["tickets_ganados"] = $temp_res_array["num_tickets_pagado"];
        $resultado["dia"][8]["ganado"] = $temp_res_array["pagado"];
    }


    //echo var_dump($resultado);

    //************************************************************************************************************
    $query_update = "
		UPDATE tbl_reporte_teleservicios_ventas_x_producto 
		SET 
			id_estado = 0,
			id_user_updated = '$usuario_id',
			updated_at = now()
		WHERE fecha = '$fecha'
			AND id_estado = 1
    	";
    $mysqli->query($query_update);

    if (isset($_POST["log"])) {
        cron_print_log("query_update");
    }

    //************************************************************************************************************
    foreach ($resultado as $key => $value) {
        for ($i = 0; $i < count($value); $i++) {

            $resultado[$key][$i]["resultado"] = $resultado[$key][$i]["apostado"] - $resultado[$key][$i]["calculado"];

            //CAST(A.apostado/A.tickets AS DECIMAL(10,2)) promedio
            if ((float)$resultado[$key][$i]["apostado"] <> 0 && (float)$resultado[$key][$i]["tickets"] <> 0) {
                $resultado[$key][$i]["promedio"] = ((float)$resultado[$key][$i]["apostado"] / (float)$resultado[$key][$i]["tickets"]);
            }
            //CAST((A.apostado - A.ganado) * 100 / A.apostado AS DECIMAL(10,2)) AS hold
            if ((float)$resultado[$key][$i]["resultado"] <> 0 && (float)$resultado[$key][$i]["apostado"] <> 0) {
                $resultado[$key][$i]["hold"] = (((float)$resultado[$key][$i]["resultado"] / (float)$resultado[$key][$i]["apostado"]) * 100);
            }
            //CAST(A.tickets_ganados/A.tickets_calculados AS DECIMAL(10,2)) porcentaje_pagados
            if ((float)$resultado[$key][$i]["tickets_ganados"] <> 0 && (float)$resultado[$key][$i]["tickets_calculados"] <> 0) {
                $resultado[$key][$i]["porcentaje_pagados"] = (((float)$resultado[$key][$i]["tickets_ganados"] / (float)$resultado[$key][$i]["tickets_calculados"]) * 100);
            }

            $resultado[$key][$i]["tickets"] = number_format($resultado[$key][$i]["tickets"], 0, ".", "");
            $resultado[$key][$i]["apostado"] = number_format($resultado[$key][$i]["apostado"], 2, ".", "");
            $resultado[$key][$i]["promedio"] = number_format($resultado[$key][$i]["promedio"], 2, ".", "");
            $resultado[$key][$i]["tickets_calculados"] = number_format($resultado[$key][$i]["tickets_calculados"], 0, ".", "");
            $resultado[$key][$i]["calculado"] = number_format($resultado[$key][$i]["calculado"], 2, ".", "");
            $resultado[$key][$i]["resultado"] = number_format($resultado[$key][$i]["resultado"], 2, ".", "");
            $resultado[$key][$i]["hold"] = number_format($resultado[$key][$i]["hold"], 2, ".", "");
            $resultado[$key][$i]["tickets_ganados"] = number_format($resultado[$key][$i]["tickets_ganados"], 0, ".", "");
            $resultado[$key][$i]["ganado"] = number_format($resultado[$key][$i]["ganado"], 2, ".", "");
            $resultado[$key][$i]["porcentaje_pagados"] = number_format($resultado[$key][$i]["porcentaje_pagados"], 2, ".", "");

            $query_insert = "
                INSERT INTO tbl_reporte_teleservicios_ventas_x_producto ( 
                	id_reporte_teleservicios_concepto,
                	fecha,
					num_tickets_apostado,
					total_tickets_apostado,
					promedio,
					num_tickets_calculado,
					total_tickets_calculado,
					resultado,
					hold,
					num_tickets_pagado,
					total_tickets_pagado,
					porcentaje_tickets_pagado,
					id_estado,
                	id_user_created,
                	created_at
                ) VALUES ( 
                    '" . $resultado[$key][$i]["id_concepto"] . "', 
                    '" . $fecha . "', 
                    '" . $resultado[$key][$i]["tickets"] . "', 
                    '" . $resultado[$key][$i]["apostado"] . "', 
                    '" . $resultado[$key][$i]["promedio"] . "', 
                    '" . $resultado[$key][$i]["tickets_calculados"] . "', 
                    '" . $resultado[$key][$i]["calculado"] . "', 
                    '" . $resultado[$key][$i]["resultado"] . "', 
                    '" . $resultado[$key][$i]["hold"] . "', 
                    '" . $resultado[$key][$i]["tickets_ganados"] . "', 
                    '" . $resultado[$key][$i]["ganado"] . "', 
                    '" . $resultado[$key][$i]["porcentaje_pagados"] . "', 
                    '1', 
                    '" . $usuario_id . "', 
                    now()
                );
            ";
            $mysqli->query($query_insert);
            if ($mysqli->error) {
                $result["insert_query_1"] = $query;
                $result["insert_error_1"] = $mysqli->error;
            }
        }
    }
    if (isset($_POST["log"])) {
        cron_print_log("foreach");
    }

    //************************************************************************************************************
    //************************************************************************************************************
    //************************************************************************************************************
    //************************************************************************************************************

    //************************************************************************************************************
    // TELESERVICIOS - Ventas Transaccionales (Torito)
    $result_torito = [];

    $result_temp = array();
    $result_temp['id_concepto'] = 0;
    $result_temp['descripcion'] = '';
    $result_temp['tickets'] = 0;
    $result_temp['apostado'] = 0;
    $result_temp['promedio'] = 0;
    $result_temp['tickets_ganados'] = 0;
    $result_temp['ganado'] = 0;

    $query = "
		  SELECT
			4 as id_concepto,
			'Torito' as descripcion,
			SUM(num_tickets) AS tickets,
			SUM(total_apostado) AS apostado,
			CAST(SUM(total_apostado)/SUM(num_tickets) AS DECIMAL(10,2)) AS promedio,
			SUM(num_tickets_ganados) AS tickets_ganados,
			SUM(pagado_en_otra_tienda)+(SUM(total_pagos_fisicos)-SUM(pagado_de_otra_tienda)) AS ganado
		FROM wwwapuestatotal_gestion.tbl_transacciones_cabecera ca
		INNER JOIN wwwapuestatotal_gestion.tbl_locales l ON ca.local_id = l.id
		WHERE
			ca.fecha >= '{$dates->yesterday}' AND ca.fecha < '{$dates->today}'
			-- AND l.zona_id = 9
			AND l.red_id = 8
			AND ca.producto_id = 9
			AND ca.estado = 1
			-- AND l.estado = 1
			-- AND l.operativo = 1
			AND (l.fecha_fin_operacion IS NULL OR l.fecha_fin_operacion >= '{$dates->startmonth}')
		";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["torito_consulta"] = $query;
        $result["torito_error"] = $mysqli->error;
    } else {
        $temp_torito = $temp->fetch_assoc();
        if (count($temp_torito) > 0) {
            $result_torito["dia"][] = $temp_torito;
        } else {
            $result_temp['id_concepto'] = 4;
            $result_torito["dia"][] = $result_temp;
        }
    }

    if (isset($_POST["log"])) {
        cron_print_log("consulta_error_torito");
    }

    //************************************************************************************************************
    foreach ($result_torito as $key => $value) {
        for ($i = 0; $i < count($value); $i++) {
            $result_torito[$key][$i]["tickets"] = number_format($result_torito[$key][$i]["tickets"], 0, ".", "");
            $result_torito[$key][$i]["apostado"] = number_format($result_torito[$key][$i]["apostado"], 2, ".", "");
            $result_torito[$key][$i]["promedio"] = number_format($result_torito[$key][$i]["promedio"], 2, ".", "");

            $result_torito[$key][$i]["tickets_ganados"] = number_format($result_torito[$key][$i]["tickets_ganados"], 0, ".", "");
            $result_torito[$key][$i]["ganado"] = number_format($result_torito[$key][$i]["ganado"], 2, ".", "");

            $query_insert = " 
				INSERT INTO tbl_reporte_teleservicios_ventas_x_producto ( 
                	id_reporte_teleservicios_concepto,
                	fecha,
					num_tickets_apostado,
					total_tickets_apostado,
					promedio,
					num_tickets_calculado,
					total_tickets_calculado,
					resultado,
					hold,
					num_tickets_pagado,
					total_tickets_pagado,
					porcentaje_tickets_pagado,
					id_estado,
                	id_user_created,
                	created_at
                ) VALUES ( 
                    '" . $result_torito[$key][$i]["id_concepto"] . "', 
                    '" . $fecha . "', 
                    '" . $result_torito[$key][$i]["tickets"] . "', 
                    '" . $result_torito[$key][$i]["apostado"] . "', 
                    '" . $result_torito[$key][$i]["promedio"] . "', 
                    0,
                    0,
                    0,
                    0,
                    '" . $result_torito[$key][$i]["tickets_ganados"] . "', 
                    '" . $result_torito[$key][$i]["ganado"] . "', 
                    0,
                    '1', 
                    '" . $usuario_id . "', 
                    now()
                );
            ";
            $mysqli->query($query_insert);
            if ($mysqli->error) {
                $result["torito_insert_query"] = $query;
                $result["torito_insert_error"] = $mysqli->error;
            }
        }
    }
    if (isset($_POST["log"])) {
        cron_print_log("foreach");
    }


    //************************************************************************************************************
    //************************************************************************************************************
    //************************************************************************************************************
    //************************************************************************************************************

    $otrosingresos = [];

    $result_temp = array();
    $result_temp['id_concepto'] = 0;
    $result_temp['descripcion'] = '';
    $result_temp['cantidad'] = 0;
    $result_temp['monto'] = 0;
    $result_temp['promedio'] = 0;

    //************************************************************************************************************
    //Recargas Web Gestion diario
    $query = "
		SELECT
			7 as id_concepto,
			'Recargas Web Gestion' as descripcion,
			COUNT(d.col_id) AS cantidad,
			SUM(d.col_Amount) AS monto,
			CAST(SUM(d.col_Amount) / COUNT(d.col_id)AS DECIMAL(10,2)) AS promedio
		from bc_apuestatotal.at_ClientDeposits as d
		LEFT JOIN bc_apuestatotal.tbl_DocumentType as dt  ON d.col_TypeId=dt.col_Id
		LEFT JOIN bc_apuestatotal.tbl_TranslationEntry AS T ON dt.col_NameId=T.col_TranslationId AND T.col_LanguageId='en'
			where d.col_TypeId in (3) AND d.col_PaymentSystemId=1630
			AND d.col_Created >= '{$dates->yesterday} 09:00:00'
			AND d.col_Created <= '{$dates->today} 09:00:00'
		GROUP BY d.col_TypeId,dt.col_Id,T.col_TranslationId
		";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["recwebges_consulta"] = $query;
        $result["recwebges_error"] = $mysqli->error;
    } else {
        $temp_recargaswebgestion = $temp->fetch_assoc();
        if (!empty($temp_recargaswebgestion)) {
            $otrosingresos["dia"][] = $temp_recargaswebgestion;
        } else {
            $result_temp['id_concepto'] = 7;
            $otrosingresos["dia"][] = $result_temp;
        }
    }
    if (isset($_POST["log"])) {
        cron_print_log("consulta_error_recargaswebgestion");
    }

    //************************************************************************************************************
    //Query Web Bethop diario
    $query = "
		SELECT
			8 as id_concepto,
			'Recargas Web BetShop' as descripcion,
			COUNT(d.col_id) AS cantidad,
			SUM(d.col_Amount) AS monto,
			CAST(SUM(d.col_Amount) / COUNT(d.col_id)AS DECIMAL(10,2)) AS promedio
		from bc_apuestatotal.at_ClientDeposits as d
		LEFT JOIN bc_apuestatotal.tbl_DocumentType as dt  ON d.col_TypeId=dt.col_Id
		LEFT JOIN bc_apuestatotal.tbl_TranslationEntry AS T ON dt.col_NameId=T.col_TranslationId AND T.col_LanguageId='en'
		LEFT JOIN bc_apuestatotal.tbl_CashDesk as cs ON d.col_CashDeskId=cs.col_Id
		LEFT JOIN bc_apuestatotal.tbl_Betshop as b ON b.col_Id=cs.col_BetshopId
		where d.col_TypeId in (5)
			AND d.col_Created >= '{$dates->yesterday} 09:00:00'
			AND d.col_Created <  '{$dates->today} 09:00:00'
			AND b.col_Id in (SELECT col_Id FROM  bc_apuestatotal.tbl_Betshop  WHERE col_Name like '%televentas%')
		GROUP BY d.col_TypeId,dt.col_Id,T.col_TranslationId
	";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["recwebbet_consulta"] = $query;
        $result["recwebbet_error"] = $mysqli->error;
    } else {
        $temp_recargaswebBetShop = $temp->fetch_assoc();
        if (!empty($temp_recargaswebBetShop)) {
            $otrosingresos["dia"][] = $temp_recargaswebBetShop;
        } else {
            $result_temp['id_concepto'] = 8;
            $otrosingresos["dia"][] = $result_temp;
        }
    }
    if (isset($_POST["log"])) {
        cron_print_log("consulta_error_recargaswebBetShop");
    }

    //************************************************************************************************************
    //Query  Terminal deposit diario
    $query = "
		SELECT 
			9 as id_concepto,
			'TerminalDeposit' as descripcion,
			COUNT(d.col_id) AS cantidad,
			SUM(d.col_Amount) AS monto,
			CAST(SUM(d.col_Amount) / COUNT(d.col_id)AS DECIMAL(10,2)) AS promedio
		FROM bc_apuestatotal.at_ClientDeposits as d
		LEFT JOIN bc_apuestatotal.tbl_DocumentType as dt  ON d.col_TypeId=dt.col_Id
		LEFT JOIN bc_apuestatotal.tbl_TranslationEntry AS T ON dt.col_NameId=T.col_TranslationId AND T.col_LanguageId='en'
		LEFT JOIN bc_apuestatotal.tbl_CashDesk as cs ON d.col_CashDeskId=cs.col_Id
		LEFT JOIN bc_apuestatotal.tbl_Betshop as b ON b.col_Id=cs.col_BetshopId -- and b.col_Name like '%televentas%'
		WHERE 
			d.col_TypeId in (701)
			AND d.col_Created >= '{$dates->yesterday} 09:00:00' 
			AND d.col_Created < '{$dates->today} 09:00:00'
			AND b.col_Id in (SELECT col_Id FROM  bc_apuestatotal.tbl_Betshop  WHERE col_Name like '%televentas%28%')
		GROUP BY d.col_TypeId,dt.col_Id,T.col_TranslationId;
	";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["terminal_consulta"] = $query;
        $result["terminal_error"] = $mysqli->error;
    } else {
        $temp_TerminalDeposit = $temp->fetch_assoc();
        if (!empty($temp_TerminalDeposit)) {
            $otrosingresos["dia"][] = $temp_TerminalDeposit;
        } else {
            $result_temp['id_concepto'] = 9;
            $otrosingresos["dia"][] = $result_temp;
        }
    }
    if (isset($_POST["log"])) {
        cron_print_log("consulta_error_TerminalDeposit");
    }

    //************************************************************************************************************
    //Query de Pagos TK Tiendas diario
    $query = "
		SELECT
			10 as id_concepto,
			'Pagos Transacciones Tiendas'  as descripcion,
			count(d.ganado) as cantidad,
			sum(d.ganado) as monto,
			cast(sum(d.ganado)/count(d.ganado)AS DECIMAL(10,2)) as promedio
		FROM tbl_transacciones_detalle d
		LEFT JOIN tbl_locales l ON (l.id = d.local_id)
		LEFT JOIN tbl_locales lp ON (lp.id = d.paid_local_id)
		WHERE d.paid_day >= '{$dates->yesterday}'
			AND d.paid_day < '{$dates->today}'
			AND d.paid_local_id IS NOT NULL
			AND d.local_id != d.paid_local_id
			AND d.tipo = '1'
			AND lp.cc_id in (3907,3938,3939,3940,3941,3942,3943,3944,3945,3946,3947,3948,3950,3951,3952,3953)
			-- AND lp.cc_id in (3938,3939,3940,3941,3942,3943,3919,3908,3906,3920,3909,3902,3905,3903,3925)
			AND l.zona_id!=9
	";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["ptktiendas_consulta"] = $query;
        $result["ptktiendas_error"] = $mysqli->error;
    } else {
        $temp_PagosTicketsTiendas = $temp->fetch_assoc();
        if (!empty($temp_PagosTicketsTiendas)) {
            $otrosingresos["dia"][] = $temp_PagosTicketsTiendas;
        } else {
            $result_temp['id_concepto'] = 10;
            $otrosingresos["dia"][] = $result_temp;
        }
    }
    if (isset($_POST["log"])) {
        cron_print_log("consulta_error_PagosTicketsTiendas");
    }

    //************************************************************************************************************
    //Query de pagos tk terminales diario
    $query = "
		SELECT
			11 as id_concepto,
			 'Pagos Transacciones Terminales'  as descripcion,
			COUNT(d.col_id) AS cantidad,
			SUM(d.col_Amount) AS monto,
			CAST(SUM(d.col_Amount)/COUNT(d.col_id)AS DECIMAL(10,2)) AS promedio
		from bc_apuestatotal.at_ClientDeposits as d
		LEFT JOIN bc_apuestatotal.tbl_CashDesk as cs ON d.col_CashDeskId=cs.col_Id
		LEFT JOIN bc_apuestatotal.tbl_Betshop as b ON b.col_Id=cs.col_BetshopId
		where d.col_TypeId in (702)
			AND d.col_Created >= '{$dates->yesterday} 09:00:00'
			AND d.col_Created < '{$dates->today} 09:00:00'
			AND b.col_Id in (SELECT col_Id FROM  bc_apuestatotal.tbl_Betshop  WHERE col_Name like '%televentas%28%')
		GROUP BY b.col_Id
	";
    $temp = $mysqli->query($query);
    if ($mysqli->error) {
        $result["ptkterm_consulta"] = $query;
        $result["ptkterm_error"] = $mysqli->error;
    } else {
        $temp_PagosTicketsTerminales = $temp->fetch_assoc();
        if (!empty($temp_PagosTicketsTerminales)) {
            $otrosingresos["dia"][] = $temp_PagosTicketsTerminales;
        } else {
            $result_temp['id_concepto'] = 11;
            $otrosingresos["dia"][] = $result_temp;
        }
    }
    if (isset($_POST["log"])) {
        cron_print_log("consulta_error_PagosTicketsTerminales");
    }

    //************************************************************************************************************
    foreach ($otrosingresos as $key => $value) {
        for ($i = 0; $i < count($value); $i++) {
            $otrosingresos[$key][$i]["cantidad"] = number_format($otrosingresos[$key][$i]["cantidad"], 0, ".", "");
            $otrosingresos[$key][$i]["monto"] = number_format($otrosingresos[$key][$i]["monto"], 2, ".", "");
            $otrosingresos[$key][$i]["promedio"] = number_format($otrosingresos[$key][$i]["promedio"], 2, ".", "");

            $query_insert = " 
				INSERT INTO tbl_reporte_teleservicios_ventas_x_producto ( 
                	id_reporte_teleservicios_concepto,
                	fecha,
					num_tickets_apostado,
					total_tickets_apostado,
					promedio,
					num_tickets_calculado,
					total_tickets_calculado,
					resultado,
					hold,
					num_tickets_pagado,
					total_tickets_pagado,
					porcentaje_tickets_pagado,
					id_estado,
                	id_user_created,
                	created_at
                ) VALUES ( 
                    '" . $otrosingresos[$key][$i]["id_concepto"] . "', 
                    '" . $fecha . "', 
                    '" . $otrosingresos[$key][$i]["cantidad"] . "', 
                    '" . $otrosingresos[$key][$i]["monto"] . "', 
                    '" . $otrosingresos[$key][$i]["promedio"] . "', 
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    '1', 
                    '" . $usuario_id . "', 
                    now()
                );
            ";
            $mysqli->query($query_insert);
            if ($mysqli->error) {
                $result["otrosingresos_insert_query"] = $query;
                $result["otrosingresos_insert_error"] = $mysqli->error;
            }
        }
    }
    if (isset($_POST["log"])) {
        cron_print_log("foreach");
    }

    //************************************************************************************************************
    //************************************************************************************************************
    //************************************************************************************************************
    //************************************************************************************************************

    $result["http_code"] = 200;
    $result["status"] = "ok.";
    //$result["dates"] = $dates;
    $result["resultado"] = $resultado;
    $result["otrosingresos"] = $otrosingresos;
    return $result;
}



