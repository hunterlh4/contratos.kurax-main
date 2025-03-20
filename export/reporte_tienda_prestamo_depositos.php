<?php
require_once '/var/www/html/cron/cron_db_connect.php';
//include('/var/www/html/sys/mailer/class.phpmailer.php');
require_once '/var/www/html/cron/cron_pdo_connect.php';
require_once '/var/www/html/sys/helpers.php';
require_once '/var/www/html/cron/validate.php';

if (isset($_GET["action"])) {
    echo "Cron generar reporte tienda prestamo depositos init at " . date("Y-m-d H:i:s") . "\n";

    $filepath     = '/var/www/html/export/files_exported/reporte_comercial_depositos/';

    $file_version = date('YmdHis');

    $afterTomorrow = date("Y-m-d");
    $tomorrow = date("Y-m-d", strtotime('-1 day', strtotime($afterTomorrow)));
    $today = date("Y-m-d", strtotime('-2 day', strtotime($afterTomorrow)));
    if (isset($_GET['today'])) {
        $today       = date($_GET['today']);
        $tomorrow = date("Y-m-d", strtotime('+1 day', strtotime($today)));
        $afterTomorrow = date("Y-m-d", strtotime('+2 day', strtotime($today)));
    }
    $fromDate = date("Y-m-d H:i:s", strtotime('+9 hour', strtotime($today)));
    $toDate    = date("Y-m-d H:i:s", strtotime('+9 hour', strtotime($tomorrow)));
    $fromDatePendientePago = date("Y-m-d H:i:s", strtotime('+9 hour', strtotime($tomorrow."- 14 days")));
    $toDatePendientePago   = date("Y-m-d H:i:s", strtotime('+15 hour', strtotime($tomorrow)));

    $fromDateRetiros = date("Y-m-d H:i:s", strtotime('+5 hour', strtotime($tomorrow."- 30 days")));
    $toDateRetiros   = date("Y-m-d H:i:s", strtotime('+11 hour', strtotime($tomorrow)));

    $querySql = "
        SELECT 
            cc_id AS Ceco ,
            zonacomercial AS 'Zona Comercial',
            nombre_tienda AS 'Nombre de Tienda',
            fecha AS 'Fecha' ,
            usuario AS Supervisor,
            cierre_efectivo AS 'Cierre Efectivo',
            fondo_fijo AS 'Fondo Fijo',
            deposito_minimo AS 'Depósito Mínimo',
            -- Depositado_Web 'Depositado Web',
            Retiros_Web_Pendiente_Pago As 'Retiros Web Pendiente Pago',
            Pendiente_Pago_Tienda AS 'Pendiente de pago',
            Saldo,
            IF(Saldo<0,'Prestamo',if(saldo>deposito_minimo,'Depositar','No Depositar')) AS Accion
        FROM (
            SELECT l.cc_id,
                z.nombre as zonacomercial,
                l.nombre as nombre_tienda,
                c.fecha,
                u.usuario,   
                IFNULL(cierre.valor,0) as cierre_efectivo,
                IFNULL(cj1.valor,0)  as fondo_fijo,
                IFNULL(cj2.valor,0)  as deposito_minimo,
                IFNULL(dw.Depositado,0)  as Depositado_Web ,
                IFNULL(s.solicitud,0)  AS Retiros_Web_Pendiente_Pago,
                IFNULL(pd.Pendiente_Pago,0)  AS Pendiente_Pago_Tienda,
                (IFNULL(cierre.valor,0) - IFNULL(cj1.valor,0) - IFNULL(s.solicitud,0) - IFNULL(pd.Pendiente_Pago,0)) AS Saldo
            FROM wwwapuestatotal_gestion.tbl_transacciones_cabecera AS c
            LEFT JOIN wwwapuestatotal_gestion.tbl_locales  as l ON c.local_id=l.id
            LEFT join wwwapuestatotal_gestion.tbl_zonas as z on c.zona_id=z.id
            LEFT JOIN wwwapuestatotal_gestion.tbl_usuarios_locales as ul ON ul.local_id = l.id
            LEFT JOIN wwwapuestatotal_gestion.tbl_usuarios as  u ON u.id = ul.usuario_id 
            LEFT JOIN wwwapuestatotal_gestion.tbl_local_caja_config AS cj1 ON l.id=cj1.local_id and cj1.campo='monto_inicial'  AND cj1.estado = 1
            LEFT JOIN wwwapuestatotal_gestion.tbl_local_caja_config AS cj2 ON l.id=cj2.local_id and cj2.campo='valla_deposito'  AND cj2.estado = 1
            LEFT JOIN wwwapuestatotal_gestion.tbl_personal_apt as psop ON psop.id = u.personal_id  
            LEFT JOIN (SELECT  
                        l.id as LocalId,
                        l.nombre AS Local,
                        sum(D.col_Amount) AS Depositado
                        FROM  bc_apuestatotal.at_ClientDeposits AS D
                        LEFT JOIN  bc_apuestatotal .tbl_CashDesk AS CD ON D.col_CashDeskId=CD.col_id
                        LEFT JOIN wwwapuestatotal_gestion.tbl_local_proveedor_id lp ON lp.proveedor_id = CD.col_id 
                        LEFT JOIN wwwapuestatotal_gestion.tbl_locales l             ON l.id = lp.local_id 
                        WHERE D.col_Created>= '$fromDate' AND D.col_Created< '$toDate'
                        AND D.col_TypeId in (5)
                        group by l.id) AS dw on l.id=dw.LocalId
            LEFT JOIN (SELECT l.id as LocalId,
                        l.nombre as LocalIdNombre,
                        sum(cr.col_Amount) as solicitud 
                        from bc_apuestatotal.tbl_ClientRequest as cr
                        INNER join bc_apuestatotal.tbl_CashDesk as c on c.col_BetshopId=cr.col_BetshopId
                        INNER JOIN wwwapuestatotal_gestion.tbl_local_proveedor_id lp ON lp.proveedor_id = c.col_id 
                        INNER JOIN wwwapuestatotal_gestion.tbl_locales l             ON l.id = lp.local_id 
                        where cr.col_RequestTime>= '$fromDatePendientePago' and cr.col_RequestTime< '$toDate'
                        AND cr.col_State= 1 AND cr.col_BetshopId is not null
                        group by l.id
                        ) AS s on l.id=s.LocalId
            LEFT JOIN (SELECT  
                        l.id as LocalId,
                        l.nombre AS Local,
                        sum(D.col_WinningAmount) AS Pendiente_Pago
                        FROM  bc_apuestatotal.at_BetPendingPay AS D
                        LEFT JOIN  bc_apuestatotal.tbl_CashDesk AS CD ON D.col_CashDeskId=CD.col_id
                        LEFT JOIN wwwapuestatotal_gestion.tbl_local_proveedor_id lp ON lp.proveedor_id = CD.col_id 
                        LEFT JOIN wwwapuestatotal_gestion.tbl_locales l             ON l.id = lp.local_id 
                        WHERE D.col_CalcDate>= '$fromDatePendientePago' AND D.col_CalcDate< '$toDatePendientePago'
                        AND D.col_CashDeskId is not null
                        group by l.id,l.nombre
                        ) AS pd on l.id=pd.LocalId
            INNER JOIN (SELECT
                        lc.local_id ,
                        df.valor AS valor
                        FROM  wwwapuestatotal_gestion.tbl_local_cajas as lc 
                        inner join wwwapuestatotal_gestion.tbl_caja as caja on lc.id=caja.local_caja_id and caja.estado=1
                        inner join  wwwapuestatotal_gestion.tbl_caja_datos_fisicos df on caja.id=df.caja_id   
                        INNER JOIN (SELECT
                                    lc.local_id ,
                                    max(caja.turno_id) as turno
                                    FROM  wwwapuestatotal_gestion.tbl_local_cajas as lc 
                                    inner join wwwapuestatotal_gestion.tbl_caja as caja on lc.id=caja.local_caja_id and caja.estado=1
                                    inner join  wwwapuestatotal_gestion.tbl_caja_datos_fisicos df on caja.id=df.caja_id   
                                    where  fecha_operacion>='$today' and  fecha_operacion<'$tomorrow'
                                    AND df.tipo_id = 11
                                    group by  lc.local_id
                                    order by local_id desc,turno desc
                                    ) AS loc on loc.local_id = lc.local_id  and loc.turno=caja.turno_id
                        where  fecha_operacion>='$today' and  fecha_operacion<'$tomorrow'
                        AND df.tipo_id = 11 
            )  cierre on cierre.local_id =l.id
        where c.fecha>='$today' and c.fecha<'$tomorrow'
        AND l.red_id in (9,1,16)
        AND ul.estado = 1
        AND u.estado = 1
        AND psop.estado = 1 AND psop.area_id = 21 AND psop.cargo_id = 4
        group by l.id,z.id
        ) Comercial
    ";

    $data = [];
    $result_query = $mysqli->query($querySql);
    if ($mysqli->error) { echo  $mysqli->error;die; }

    /*bd calimaco */
    $mysqli->close();
    require("/var/www/html/cron/cron_bc_connect.php");
    $command = "
        SELECT DO.shop, COALESCE(SUM(DO.amount)/100) AS 'Retiros Web Pendiente Pago'
        FROM    data.operations DO
        WHERE   DO.TYPE = 'PAYOUT'
            AND DO.company = 'ATP'
            AND DO.METHOD = 'ATPAYMENTSERVICE_PAYOUT'
            AND DO.STATUS IN ('NEW', 'ACCEPTED', 'TO_BE_PROCESSED')
            AND DO.shop IS NOT NULL
            AND DO.UPDATED_DATE >= '$fromDateRetiros'
            AND DO.UPDATED_DATE <  '$toDateRetiros'      -- Se actualiza ULTIMOS 30 DIAS
        GROUP BY DO.shop
    ";
    $result_query_cal = $mysqli->query($command);
    if ($mysqli->error) { echo  $mysqli->error;die; }
    $operations = [];
    while ($d = $result_query_cal->fetch_assoc()) {
        echo "bd calimaco data\n";
        $operations[$d["shop"]] = $d["Retiros Web Pendiente Pago"];
    }
    /*bd calimaco*/
    $mysqli->close();
    require("/var/www/html/cron/cron_db_connect.php");
    $data = [];
    while ($d = $result_query->fetch_assoc()) {
        $d["Retiros Web Pendiente Pago"] = isset( $operations[$d["Ceco"]] ) ? $operations[$d["Ceco"]] : 0;
        $d["Cierre Efectivo"] = $d["Cierre Efectivo"] == null ? 0 : $d["Cierre Efectivo"];
        $d["Pendiente de pago"] = $d["Pendiente de pago"] == null ? 0 : $d["Pendiente de pago"];
        $data[] = $d;
    }
    $temp = [];
    foreach($data as $row){
        $row["Saldo"] = $row["Cierre Efectivo"] 
                    - $row["Fondo Fijo"] 
                    - ( $row["Pendiente de pago"] ? : 0 ) 
                    - $row["Retiros Web Pendiente Pago"];

        $row["Retiros Web Pendiente Pago"] =  number_format($row["Retiros Web Pendiente Pago"], 2, '.', ',');
        $row["Saldo"] = round($row["Saldo"], 2);

        /*
        $accion = "";
        if( $row["Saldo"] < 0 ){
            $accion = "Préstamo";
        } else if( $row["Saldo"] >= 0 && $row["Saldo"] < $row["Depósito Mínimo"] ){
            $accion = "No Depositar";
        } else if( $row["Saldo"] >= $row["Depósito Mínimo"] ){
            $accion = "Depositar";
        }
        $row["Accion"] = $accion;
        */

        /********************************************************************************
         * ******************************************************************************
         * INICIO: BUSQUEDA DEPOSITOS AL DÍA SIGUIENTE
         * ******************************************************************************
         * ******************************************************************************
         */

        $row["Deposito Boveda"] = 0;
        $row["Deposito Venta"] = 0;
        $row["Hermeticase Bóveda"] = 0;
        $row["Hermeticase Venta"] = 0;
        $total_dpositado = 0;
        $caja_busqueda_sql = "
        SELECT
            df.tipo_id,
            IFNULL(sum(df.valor),0) AS valor
        FROM tbl_caja c
            LEFT JOIN tbl_local_cajas lc ON(lc.id = c.local_caja_id)
            LEFT JOIN tbl_locales l ON (l.id = lc.local_id)
            LEFT JOIN tbl_caja_datos_fisicos df ON (df.caja_id = c.id AND df.tipo_id IN (3,4,26,27))
        WHERE c.id != 1
            AND l.cc_id = '".$row["Ceco"]."'
            AND c.fecha_operacion >= '$tomorrow'
            AND c.fecha_operacion < '$afterTomorrow'
        GROUP BY df.tipo_id
        ";
        $caja_busqueda_query = $mysqli->query($caja_busqueda_sql);
        if ($mysqli->error) { echo  $mysqli->error;die; }
        while ($cb = $caja_busqueda_query->fetch_assoc()) {
            if ($cb["tipo_id"] == 3) {
                $row["Deposito Boveda"] = $cb["valor"];
            } elseif ($cb["tipo_id"] == 4) {
                $row["Deposito Venta"] = $cb["valor"];
            } elseif ($cb["tipo_id"] == 26) {
                $row["Hermeticase Bóveda"] = $cb["valor"];
            } elseif ($cb["tipo_id"] == 27) {
                $row["Hermeticase Venta"] = $cb["valor"];
            }
            $total_dpositado += $cb["valor"];
        }
        $row["Depósito Total"] = $total_dpositado;

        /********************************************************************************
         * ******************************************************************************
         * FIN: BUSQUEDA DEPOSITOS AL DÍA SIGUIENTE
         * ******************************************************************************
         * ******************************************************************************
         */
        $temp[] = [
            "Ceco"              => $row["Ceco"],
            "Zona Comercial"    => $row["Zona Comercial"],
            "Nombre de Tienda"  => $row["Nombre de Tienda"],
            "Fecha"             => $row["Fecha"],
            "Supervisor"        => $row["Supervisor"],
            "Saldo"             => $row["Saldo"],
            "Deposito Boveda"   => $row["Deposito Boveda"],
            "Deposito Venta"    => $row["Deposito Venta"],
            "Hermeticase Bóveda"=> $row["Hermeticase Bóveda"],
            "Hermeticase Venta" => $row["Hermeticase Venta"],
            "Depósito Total"    => $row["Depósito Total"],
            "Análisis"          => $row["Saldo"] - $row["Depósito Total"]
        ];
    }

    $file = "reporte_FLUJO_DE_EFECTIVO_de_" . $today . "_al_" . $tomorrow . "_version_" . $file_version . ".csv";
    $body = "Archivo {$file} adjunto.";
    array2csv($temp, $filepath . $file);
    $request = [
        "subject" => "REPORTE - EFECTIVO DEPOSITADO desde " .$today. " hasta ".$tomorrow." #" . date('YmdHis'),
        "body" => $body,
        "cc" => [
        ],
        "bcc" => [
        ],
        "attach"  => [
            $filepath . $file,
        ]
    ];

    if ($_GET["action"] == 'ver') {
        $cols = ["CECO", "Zona Comercial",  "Nombre de tienda", "Fecha", "Supervisor", "Saldo", "Deposito Boveda", "Deposito Venta", "Hermeticase Bóveda", "Hermeticase Venta", "Depósito Total", "Análisis"];
        $cols_len = count($cols);
        $col_w = 140;
        $cols_w = [80, 80, 200, 100, 135, 150, 100, 100, 100, 100, 150, 150];

        $body = "";
        $body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family: arial" width="' . array_sum($cols_w) . 'px">';
        $body .= '<thead>';
        $body .= '<tr>';
        $body .= '<th colspan="' . $cols_len . '" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
        $body .= '<b>Reporte comercial de ' . $fromDate  . ' al ' . $toDate . '</b>';
        $body .= '</th>';
        $body .= '</tr>';
        $body .= '</thead>';
        $body .= '<tbody>';

        $body .= '';


        foreach ($cols as $col_k => $col) {
            $body .= '<td style="background-color: #ffffdd;  font-size: 14px; width:' . ($cols_w[$col_k]) . 'px;">';
            $body .= '<b>';
            $body .= $col;
            $body .= '</b>';
            $body .= '</td>';
        }
        $body .= '</tr>';

        foreach ($temp as $d) {
            $body .= '<tr>';
            foreach ($d as $v) {
                $body .= '<td style="font-size: 12px; text-align:' . (is_numeric($v) ? 'right' : 'left') . ';">';
                $body .= $v;
                $body .= '</td>';
            }
            $body .= '</tr>';
        }
        $body .= '</tbody>';
        $body .= '</table>';

        echo($body);

        echo('<hr>');
        echo('<textarea>'.$querySql.'</textarea>');
        echo('<hr>'); 

        echo('<hr>');
        echo('<textarea>'.$command.'</textarea>');
        echo('<hr>'); 
        if ($_GET["action"] == 'enviar') {
            $request = [
                "subject" => " TEST MAIL REPORTE - EFECTIVO DEPOSITADO desde " .$today. " hasta ".$tomorrow." #" . date('YmdHis'),
                "body" => $body,
                "cc" => [
                ],
                "bcc" => [
                    "ricardo.lanchipa@testtest.kurax.dev"
                ],
                "attach"  => [
                    $filepath . $file,
                ]
            ];
            echo ("<br>");
            echo "init send_email at " . date("Y-m-d H:i:s") . "\n";
            send_email_v6($request);
        }
    } else if ($_GET["action"] == 'enviar') {
        echo ("<br>");
        echo "init send_email at " . date("Y-m-d H:i:s") . "\n";
        if (isset($_GET["test_ok"])) {
            $request = [
                "subject" => " TEST MAIL REPORTE - EFECTIVO DEPOSITADO desde " .$today. " hasta ".$tomorrow." #" . date('YmdHis'),
                "body" => $body,
                "cc" => [
                ],
                "bcc" => [
                    "ricardo.lanchipa@testtest.kurax.dev"
                ],
                "attach"  => [
                    $filepath . $file,
                ]
            ];
        }
        send_email_v6($request);
    }
    echo ("<br>");
    echo "Cron reporte_tienda_prestamo_depositos.php finished at " . date("Y-m-d H:i:s") . "\n\n";
} else {
?>
    <b>Reporte Depósitos Tiendas</b>
    <br>
	<div class="container">
        <div class="col-sm-12">
            <form method="GET" action="#">
                <div class="form-group">
                    <label for="today">Fecha del Reporte <small>(Sólo se puede obtener un reporte de hace dos días)</small></label><br>
                    <input type="date" id="today" name="today" class="form-control" placeholder=""
                    value="<?php echo isset($_GET['today']) ? $_GET['today'] : date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="action">Acción</label>
                    <select id="action" name="action" class="form-control" required>
                        <option value="" disabled>Please Choose</option>
                        <option value="ver" <?php if (isset($_GET['action']) && "ver" == $_GET['action']) {echo "selected";}?>>Ver Resultado</option>
                        <option value="enviar" <?php if (isset($_GET['action']) && "enviar" == $_GET['action']) {echo "selected";}?>>Enviar Email</option>
                    </select>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" <?php if (isset($_GET['test_ok'])) {echo "checked";}?> name="test_ok" class="form-check-input" id="exampleCheck1">
                    <label class="form-check-label" for="exampleCheck1">Test</label>
                </div>
                <div class="form-check mb-3">
                    <input type="checkbox" <?php if ($correction_ok) {echo "checked";}?> name="correction_ok" class="form-check-input" id="exampleCheck1">
                    <label class="form-check-label" for="exampleCheck1">Correccion</label>
                </div>
                <button type="submit" class="btn btn-success">Ejecutar Acción</button>
            </form>
        </div>
    </div>
<?php } ?>