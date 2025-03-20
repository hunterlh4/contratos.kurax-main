<?php
require_once '/var/www/html/cron/cron_db_connect.php';
include('/var/www/html/sys/mailer/class.phpmailer.php');
require_once '/var/www/html/cron/cron_pdo_connect.php';
require_once '/var/www/html/sys/helpers.php';
require_once '/var/www/html/cron/validate.php';

echo "Cron generar reporte comercial prestamo IGH init at " . date("Y-m-d H:i:s") . "\n";

$filepath     = '/var/www/html/export/files_exported/reporte_comercial_igh/';
if (!is_dir($filepath)) {
    mkdir($filepath, 0777, true);
}
$file_version = date('YmdHis');
$yesterday    = date("Y-m-d", strtotime("yesterday"));
$today        = date("Y-m-d");
if (isset($_GET['fecha'])) {
	$today       = date($_GET['fecha']);
	$yesterday 	= date("Y-m-d", strtotime('-1 day', strtotime($today)));
}
$fromDate = date("Y-m-d H:i:s", strtotime('+9 hour', strtotime($yesterday)));
$toDate    = date("Y-m-d H:i:s", strtotime('+9 hour', strtotime($today)));
$toDatePendientePago   = date("Y-m-d H:i:s", strtotime('+15 hour', strtotime($today)));
$fromDatePendientePago = date("Y-m-d H:i:s", strtotime('+9 hour', strtotime($today."- 14 days")));

$querySql = '
SELECT 
 cc_id AS Ceco ,
 zonacomercial AS "Zona Comercial",
 nombre_tienda AS "Nombre de Tienda",
 fecha AS "Fecha" ,
 usuario AS Supervisor,
 cierre_efectivo AS "Cierre Efectivo",
 fondo_fijo AS "Fondo Fijo",
 deposito_minimo AS "Depósito Mínimo",
 -- Depositado_Web "Depositado Web",
 Retiros_Web_Pendiente_Pago As "Retiros Web Pendiente Pago",
 Pendiente_Pago_Tienda AS "Pendiente de pago",
 Saldo,
 IF(Saldo<0,"Prestamo",if(saldo>deposito_minimo,"Depositar","No Depositar")) AS Accion
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
 LEFT JOIN wwwapuestatotal_gestion.tbl_local_caja_config AS cj1 ON l.id=cj1.local_id and cj1.campo=\'monto_inicial\'  AND cj1.estado = 1
 LEFT JOIN wwwapuestatotal_gestion.tbl_local_caja_config AS cj2 ON l.id=cj2.local_id and cj2.campo=\'valla_deposito\'  AND cj2.estado = 1
 LEFT JOIN wwwapuestatotal_gestion.tbl_personal_apt as psop ON psop.id = u.personal_id  
 LEFT JOIN (SELECT  
             l.id as LocalId,
             l.nombre AS Local,
             sum(D.col_Amount) AS Depositado
             FROM  bc_apuestatotal.at_ClientDeposits AS D
             LEFT JOIN  bc_apuestatotal .tbl_CashDesk AS CD ON D.col_CashDeskId=CD.col_id
             LEFT JOIN wwwapuestatotal_gestion.tbl_local_proveedor_id lp ON lp.proveedor_id = CD.col_id 
             LEFT JOIN wwwapuestatotal_gestion.tbl_locales l             ON l.id = lp.local_id 
             WHERE D.col_Created>= \'' . $fromDate . '\' AND D.col_Created< \'' . $toDate . '\'
             AND D.col_TypeId in (5)
             group by l.id) AS dw on l.id=dw.LocalId
 LEFT JOIN (SELECT l.id as LocalId,
             l.nombre as LocalIdNombre,
             sum(cr.col_Amount) as solicitud 
             from bc_apuestatotal.tbl_ClientRequest as cr
             INNER join bc_apuestatotal.tbl_CashDesk as c on c.col_BetshopId=cr.col_BetshopId
             INNER JOIN wwwapuestatotal_gestion.tbl_local_proveedor_id lp ON lp.proveedor_id = c.col_id 
             INNER JOIN wwwapuestatotal_gestion.tbl_locales l             ON l.id = lp.local_id 
             where cr.col_RequestTime>= \'' . $fromDatePendientePago . '\' and cr.col_RequestTime< \'' . $toDate . '\'
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
             WHERE D.col_CalcDate>= \'' . $fromDatePendientePago . '\' AND D.col_CalcDate< \'' . $toDatePendientePago  . '\'
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
                        where  fecha_operacion>=\'' . $yesterday . '\' and  fecha_operacion<\'' . $today . '\'
                        AND df.tipo_id = 11
                        group by  lc.local_id
                          order by local_id desc,turno desc
                        ) AS loc on loc.local_id = lc.local_id  and loc.turno=caja.turno_id
            where  fecha_operacion>=\'' . $yesterday . '\' and  fecha_operacion<\'' . $today . '\'
            AND df.tipo_id = 11 
           )  cierre on cierre.local_id =l.id
 where c.fecha>=\'' . $yesterday . '\' and c.fecha<\'' . $today . '\'
 AND l.red_id in (16)
  AND ul.estado = 1
 AND u.estado = 1
 AND psop.estado = 1 AND psop.area_id = 21 AND psop.cargo_id = 4
 group by l.id,z.id
 ) Comercial
';

$data = [];
$result_query = $mysqli->query($querySql);
if ($mysqli->error) { echo  $mysqli->error;die; }

/*bd calimaco */
$mysqli->close();
require("/var/www/html/cron/cron_bc_connect.php");
$command = "SELECT DO.shop, COALESCE(SUM(DO.amount)/100) AS 'Retiros Web Pendiente Pago'
FROM 	data.operations DO
WHERE	DO.TYPE = 'PAYOUT'
AND	DO.company = 'ATP'
AND DO.METHOD = 'ATPAYMENTSERVICE_PAYOUT'
AND	DO.STATUS IN ('NEW', 'ACCEPTED', 'TO_BE_PROCESSED')
AND	DO.shop IS NOT NULL
AND	DO.UPDATED_DATE >= CONCAT(CAST(DATE_ADD(NOW(), INTERVAL -30 DAY) AS NCHAR(10)), ' 05:00:00')	
AND DO.UPDATED_DATE <  CONCAT(CAST(NOW() AS NCHAR(10)), ' 11:00:00' )		 -- Se actualiza ULTIMOS 30 DIAS
GROUP BY 	DO.shop
";
$result_query_cal = $mysqli->query($command);
if ($mysqli->error) { echo  $mysqli->error;die; }
$operations = [];
while ($d = $result_query_cal->fetch_assoc()) {
  $operations[$d["shop"]] = $d["Retiros Web Pendiente Pago"];
}
/*bd calimaco*/
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

  $accion = "";
  if( $row["Saldo"] < 0 )
  {
    $accion = "Préstamo";
  }else if( $row["Saldo"] >= 0 && $row["Saldo"] < $row["Depósito Mínimo"] )
  {
    $accion = "No Depositar";
  }else if( $row["Saldo"] >= $row["Depósito Mínimo"] )
  {
    $accion = "Depositar";
  }
  $row["Accion"] = $accion;
  $temp[] = $row;
}

$file = "reporte_FLUJO_DE_EFECTIVO_IGH_de_" . $yesterday . "_al_" . $today . "_version_" . $file_version . ".csv";
$body = "Archivo {$file} adjunto.";
//array2csv($data, $filepath . $file);
array2csv($temp, $filepath . $file);
$request = [
	"subject" => "REPORTE - FLUJO DE EFECTIVO IGH desde " .$yesterday. " hasta ".$today." #" . date('YmdHis'),
	"body" => $body,
	"cc" => [
        "marco.fiestas@testtest.igamingh.pe",
        "ingrid.zamora@testtest.igamingh.pe",
        "flor.sampen@testtest.igamingh.pe",
        "julio.magallanes@testtest.igamingh.pe",
        "erlin.gutierrez@testtest.igamingh.pe",
        "kathia.guillermo@testtest.igamingh.pe",
        "segundo.alvan@testtest.igamingh.com",
        "vanessa.asencios@testtest.igamingh.com",
        "erika.atanacio@testtest.igamingh.com",
        "magyory.baquedano@testtest.igamingh.com",
        "simon.briceno@testtest.igamingh.com",
        "kevin.chauca@testtest.igamingh.com",
        "jhessett.chilon@testtest.igamingh.com",
        "roxana.coello@testtest.igamingh.com",
        "adrian.gonzales@testtest.igamingh.com",
        "carmen.guevara@testtest.igamingh.com",
        "diana.gutierrez@testtest.igamingh.com",
        "gissela.huaman@testtest.igamingh.com",
        "ritha.huapaya@testtest.igamingh.com",
        "rosa.larranaga@testtest.igamingh.com",
        "elizabeth.luza@testtest.igamingh.com",
        "nicol.mandujano@testtest.igamingh.com",
        "maryoline.meneses@testtest.igamingh.com",
        "yolanda.nizama@testtest.igamingh.com",
        "juan.paredes@testtest.igamingh.com",
        "yhosselin.parrales@testtest.igamingh.com",
        "hilary.quijano@testtest.igamingh.com",
        "jisselly.rengifo@testtest.igamingh.com",
        "jannet.reyes@testtest.igamingh.com",
        "cristina.salcedo@testtest.igamingh.com",
        "liz.saurino@testtest.igamingh.com",
        "karina.tudela@testtest.igamingh.com",
        "yeni.urtecho@testtest.igamingh.com",
        "nellsi.usca@testtest.igamingh.com",
        "carlos.vela@testtest.igamingh.com",
        "diego.villena@testtest.igamingh.com",
        "melany.zarzosa@testtest.igamingh.com",
        "hector.arroyo@testtest.igamingh.pe",
        "helsped.garcia@testtest.igamingh.pe",
        "anival.velarde@testtest.igamingh.pe",
        "danny.vassallo@testtest.igamingh.pe",
        "ruth.velasquez@testtest.igamingh.pe"
	],
	"bcc" => [
		"bladimir.quispe@testtest.kurax.dev",

    "gobiernodedatos@testtest.apuestatotal.com",
    "neil.flores@testtest.kurax.dev",
    "ricardo.lanchipa@testtest.kurax.dev",
    "gorqui.chavez@testtest.kurax.dev"
	],
	"attach"  => [
		$filepath . $file,
	]
];

if (isset($_GET["test"])) {


	$cols = ["Ceco", "ZONA COMERCIAL",  "Nombre de tienda", "Fecha", "SUPERVISOR", "Cierre Efectivo", "Fondo Fijo", "Depósito Mínimo","Retiros Web Pendiente Pago", "Pendiente de pago", "Saldo", "Accion"];
	$cols_len = count($cols);
	$col_w = 140;
	$cols_w = [80, 80, 200, 100, 135, 150, 150, 100, 100, 100, 100, 100];

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
  if (isset($_GET["mail_test"])) {
    $request = [
      "subject" => " TEST MAIL REPORTE - FLUJO DE EFECTIVO IGH desde " .$yesterday. " hasta ".$today." #" . date('YmdHis'),
      "body" => $body,
      "cc" => [		       
      ],
      "bcc" => [
        "ricardo.lanchipa@testtest.kurax.dev",
        "neil.flores@testtest.kurax.dev",
        "luis.chambilla@testtest.kurax.dev"
      ],
      "attach"  => [
        $filepath . $file,
      ]
    ];
    send_email($request);
  }
  
} else {
	echo ("<br>");
	echo "init send_email at " . date("Y-m-d H:i:s") . "\n";
	send_email($request);
}
echo ("<br>");
echo "Cron reporte_tienda_prestamo_igh.php finished at " . date("Y-m-d H:i:s") . "\n\n";
