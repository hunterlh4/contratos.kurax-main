<?php
include('/var/www/html/sys/mailer/class.phpmailer.php');
require_once '/var/www/html/cron/cron_pdo_connect.php';
require_once '/var/www/html/sys/helpers.php';
require_once '/var/www/html/cron/cron_db_connect.php';
require_once '/var/www/html/cron/validate.php';

echo "Cron generar reporte comercial prestamo init at " . date("Y-m-d H:i:s") . "\n";

$filepath     = '/var/www/html/export/files_exported/reporte_comercial/';
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

$querySql = "SELECT 
  cc_id AS Ceco ,
  z.nombre AS 'Zona Comercial',
  loc.nombre AS 'Nombre de Tienda',
  c.fecha AS 'Fecha' ,
  u.usuario AS Supervisor,
  (SELECT	IFNULL(df.valor,0) AS valor
    FROM 	wwwapuestatotal_gestion.tbl_caja_datos_fisicos df
    LEFT JOIN wwwapuestatotal_gestion.tbl_caja_datos_fisicos_tipos DT ON df.tipo_id = DT.id
    WHERE df.caja_id = (
      SELECT MAX(c.id) AS caja_id
      FROM 	wwwapuestatotal_gestion.tbl_caja c
      LEFT JOIN wwwapuestatotal_gestion.tbl_local_cajas lc ON (lc.id = c.local_caja_id)
      LEFT JOIN wwwapuestatotal_gestion.tbl_locales l ON (l.id = lc.local_id)
      WHERE 	c.id != 1
      AND l.id = loc.id -- Red AT Pecsa Piura		-- Se actualiza data segun el foreach de locales
      AND c.fecha_operacion >= '$yesterday'
      AND c.fecha_operacion < '$today'	
    )
    AND	df.tipo_id = 11
  ) AS 'Cierre Efectivo',
  (SELECT co.valor 
    FROM 	wwwapuestatotal_gestion.tbl_local_caja_config co inner join wwwapuestatotal_gestion.tbl_locales l
    ON co.local_id = l.id
    WHERE co.local_id = loc.id 	 -- Red AT Pecsa Piura	--Se actualiza data segun el foreach de locales
    AND co.campo in ('monto_inicial')    -- monto inicial: es el fondo fijo  -- valla_deposito: Deposito minimo
    and co.estado = 1
  ) AS 'Fondo Fijo',
  (SELECT co.valor 
    FROM 	wwwapuestatotal_gestion.tbl_local_caja_config co inner join wwwapuestatotal_gestion.tbl_locales l
    ON co.local_id = l.id
    WHERE co.local_id = loc.id 	 -- Red AT Pecsa Piura	--Se actualiza data segun el foreach de locales
    AND co.campo in ('valla_deposito')    -- monto inicial: es el fondo fijo  -- valla_deposito: Deposito minimo
    and co.estado = 1
  ) AS 'Depósito Mínimo',
  /*0 AS 'Depositado Web',*/
  0 AS 'Retiros Web Pendiente Pago',
  (
    SELECT   
             l.id as LocalId,
             l.nombre AS Local,
             sum(D.col_WinningAmount)  AS PendientePago
      FROM 	bc_apuestatotal.at_BetPendingPay AS D
      LEFT JOIN bc_apuestatotal.tbl_CashDesk AS CD ON  D.col_CashDeskId = CD.col_id
      LEFT JOIN wwwapuestatotal_gestion.tbl_local_proveedor_id lp	ON  lp.proveedor_id = CD.col_id
      LEFT JOIN wwwapuestatotal_gestion.tbl_locales l ON l.id = lp.local_id 
      WHERE 	D.col_CalcDate >= '$fromDatePendientePago'
      AND	D.col_CalcDate < '$toDatePendientePago'
      AND D.col_CashDeskId IS NOT null
      AND lp.local_id = loc.id
  ) AS 'Pendiente de pago'
      
FROM wwwapuestatotal_gestion.tbl_transacciones_cabecera AS c
 LEFT JOIN wwwapuestatotal_gestion.tbl_locales  AS loc ON c.local_id=loc.id
 LEFT join wwwapuestatotal_gestion.tbl_zonas AS z on c.zona_id=z.id
 LEFT JOIN wwwapuestatotal_gestion.tbl_usuarios_locales AS ul ON ul.local_id = loc.id
 LEFT JOIN wwwapuestatotal_gestion.tbl_usuarios AS  u ON u.id = ul.usuario_id 
 LEFT JOIN wwwapuestatotal_gestion.tbl_local_caja_config AS cj1 ON loc.id = cj1.local_id AND cj1.campo = \"monto_inicial\"  AND cj1.estado = 1
 LEFT JOIN wwwapuestatotal_gestion.tbl_local_caja_config AS cj2 ON loc.id = cj2.local_id AND cj2.campo = \"valla_deposito\"  AND cj2.estado = 1
 LEFT JOIN wwwapuestatotal_gestion.tbl_personal_apt AS psop ON psop.id = u.personal_id  
 WHERE c.fecha >= '$yesterday' AND c.fecha < '$today'
  AND loc.red_id in (9,1)
  AND ul.estado = 1
  AND u.estado = 1
  AND psop.estado = 1
  AND psop.area_id = 21 
  AND psop.cargo_id = 4
  GROUP BY loc.id,z.id
";
$data = [];
echo "<pre>";print_r($querySql);echo "</pre>";
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
AND DO.UPDATED_DATE <  CONCAT(CAST(NOW() AS NCHAR(10)), ' 05:00:00' )		 -- Se actualiza ULTIMOS 30 DIAS
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
  $row["SALDO"] = $row["Cierre Efectivo"] 
                - $row["Fondo Fijo"] 
                - ( $row["Pendiente de pago"] ? : 0 ) 
                - $row["Retiros Web Pendiente Pago"];

  $accion = "";
  if( $row["SALDO"] < 0 )
  {
    $accion = "Préstamo";
  }
  if( $row["SALDO"] >= 0 && $row["SALDO"] < $row["Depósito Mínimo"] )
  {
    $accion = "No Depositar";
  }
  if( $row["SALDO"] >= $row["Depósito Mínimo"] )
  {
    $accion = "Depositar";
  }
  $row["Accion"] = $accion;
  
  $temp[] = $row;
  
}

$file = "reporte_FLUJO_DE_EFECTIVO_de_" . $yesterday . "_al_" . $today . "_version_" . $file_version . ".csv";
$body = "Archivo {$file} adjunto.";
//array2csv($data, $filepath . $file);
array2csv($temp, $filepath . $file);
$request = [
	"subject" => "REPORTE - FLUJO DE EFECTIVO desde " .$yesterday. " hasta ".$today." #" . date('YmdHis'),
	"body" => $body,
	"cc" => [		
		/*"jc.redat@testtest.apuestatotal.com",
		"supervisores@testtest.apuestatotal.net",
		"roxana.sanchez@testtest.apuestatotal.com",
		// "ruth.velasquez@testtest.apuestatotal.com",
    // "dick.flores@testtest.apuestatotal.com",
    // "enrique.lopez@testtest.apuestatotal.com"*/
    // 'dania.coba@testtest.apuestatotal.net',
    // 'alexandra.leguia@testtest.apuestatotal.com',
    'denis.guzman@testtest.apuestatotal.com',
    'gobiernodedatos@testtest.apuestatotal.com',
    'neil.flores@testtest.kurax.dev',
    'gorqui.chavez@testtest.kurax.de'
	],
	"bcc" => [
		/*"ernesto.osma@testtest.apuestatotal.com",
		"bladimir.quispe@testtest.apuestatotal.com",
    // "carlos.mesta@testtest.apuestatotal.com",
    "denis.guzman@testtest.apuestatotal.com",
    "luistar3@testtest.gmail.com"*/
	],
	"attach"  => [
		$filepath . $file,
	]
];

if (isset($_GET["test"])) {


	$cols = ["Ceco", "ZONA COMERCIAL",  "Nombre de tienda", "Fecha", "SUPERVISOR", "Cierre Efectivo", "Fondo Fijo", "Mínimo de deposito", /*"Depositado Web",*/"Retiros Web Pendiente Pago", "Pendiente de pago", "SALDO", "Accion"];
	$cols_len = count($cols);
	$col_w = 140;
	$cols_w = [80, 80, 200, 100, 135, 150, 150, 100, 100,100, 100, 100, 100];

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
      "subject" => " TEST MAIL REPORTE - FLUJO DE EFECTIVO desde " .$yesterday. " hasta ".$today." #" . date('YmdHis'),
      "body" => $body,
      "cc" => [		       
      ],
      "bcc" => [
        "denis.guzman@testtest.apuestatotal.com",
        "luistar3@testtest.gmail.com"
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
echo "Cron reporte_tienda_prestamo.php finished at " . date("Y-m-d H:i:s") . "\n\n";
