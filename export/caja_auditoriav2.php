<?php
include("../sys/db_connect.php");
include("../sys/sys_login.php");

$menu_id = "";
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'caja' AND sub_sec_id = 'auditoria' LIMIT 1");
while($r = $result->fetch_assoc()) $menu_id = $r["id"];
if(!array_key_exists($menu_id,$usuario_permisos) || !in_array("export", $usuario_permisos[$menu_id])){
	echo "No tienes permisos para acceder a este recurso";
	die;
}

date_default_timezone_set("America/Lima");
$post = array();
$post = array("sec_caja_auditoria"=>array(
	"local_id" => $_POST["local_id"],
	"fecha_inicio" => $_POST["fecha_inicio"],
	"fecha_fin" => $_POST["fecha_fin"]	
));


if(isset($post["sec_caja_auditoria"])){
	$get_data = $post["sec_caja_auditoria"];

	$local_id = $get_data["local_id"];
	$fecha_inicio = $get_data["fecha_inicio"];
	$fecha_inicio_pretty = date("d-m-Y",strtotime($get_data["fecha_inicio"]));

	$fecha_fin = date("Y-m-d",strtotime($get_data["fecha_fin"]." +1 day"));
	$fecha_fin_pretty = date("d-m-Y",strtotime($get_data["fecha_fin"]));
	$caja_arr = [];

	$caja_where = "";
	if(!empty($login["usuario_locales"])) $caja_where .= " AND u.id = {$login['id']}";
	if($local_id!="_all_") $caja_where.=" AND l.id = '".$local_id."'";

	$caja_data = [];
	$mysqli->query("SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED");
	$result = $mysqli->query("
	    SELECT 
            *, 
            (sub_dinero_sistema + televentas_sistema) as dinero_sistema, 
            (sub_resultado_voucher + televentas_sistema) as resultado_voucher 
	    FROM (
		SELECT
		    c.fecha_operacion,
		    l.id AS local_id,
		    l.nombre AS local_nombre,
		    -- ca.dinero_cajero,
		    -- ca.cajero_pagos_manuales,
		    -- ca.devoluciones_cajero AS cajero_devolucion,
		    (
				SELECT cast(sum(if((dc_cdf.tipo_id = 5),dc_cdf.valor,0)) AS DECIMAL(20,2))
				FROM tbl_caja dc_c
				LEFT JOIN tbl_local_cajas dc_lc ON (dc_lc.id = dc_c.local_caja_id)
				LEFT JOIN tbl_caja_datos_fisicos dc_cdf ON (dc_cdf.caja_id = dc_c.id)
				WHERE dc_lc.local_id = l.id
				AND dc_c.fecha_operacion = c.fecha_operacion
			) AS dinero_cajero,
			(
				SELECT cast(sum(if((cpm_cdf.tipo_id = 9),cpm_cdf.valor,0)) AS DECIMAL(20,2))
				FROM tbl_caja cpm_c
				LEFT JOIN tbl_local_cajas cpm_lc ON (cpm_lc.id = cpm_c.local_caja_id)
				LEFT JOIN tbl_caja_datos_fisicos cpm_cdf ON (cpm_cdf.caja_id = cpm_c.id)
				WHERE cpm_lc.local_id = l.id
				AND cpm_c.fecha_operacion = c.fecha_operacion
			) AS cajero_pagos_manuales,
			(
				SELECT cast(sum(if((cd_cdf.tipo_id = 8),cd_cdf.valor,0)) AS DECIMAL(20,2))
				FROM tbl_caja cd_c
				LEFT JOIN tbl_local_cajas cd_lc ON (cd_lc.id = cd_c.local_caja_id)
				LEFT JOIN tbl_caja_datos_fisicos cd_cdf ON (cd_cdf.caja_id = cd_c.id)
				WHERE cd_lc.local_id = l.id
				AND cd_c.fecha_operacion = c.fecha_operacion
			) AS cajero_devolucion,
		    CAST((
		   		SELECT
	                IFNULL(
		                CAST((SUM(
		                	IF(r.tipo = 3,
		                		(r.bets_amount + r.terminal_deposit_amount + r.deposits), IF(r.tipo = 2,
		                            (r.income_amount),
		                            IF(r.tipo = 4,
			                            r.stake,
			                            0
			                        )
			                    )
			                )
			            )) - (SUM(
				        	IF(r.tipo = 3,
				            	(r.paid_win_amount + r.terminal_withdraw_amount + r.withdrawals),
				                IF(r.tipo = 2,
				                	(0),
				                    IF(r.tipo = 4,
				                    	(IFNULL(r.paid_out_cash, 0) + IFNULL(r.jackpot_paid, 0) + IFNULL(r.mega_jackpot_paid, 0)),
				                        0
				                    )
				                )
				            ))
			        	) AS DECIMAL (20 , 2 )),
			        0) AS total_balance
	            FROM
	                tbl_transacciones_repositorio r
	            WHERE
                	r.local_id = l.id
                    AND r.created = c.fecha_operacion
                    AND r.tipo IN (2, 3, 4)
			    ) AS DECIMAL (20 , 2 )) + (CAST((
					SELECT 
						IFNULL(SUM(amount), 0)
					FROM tbl_repositorio_bingo_tickets
					WHERE 
						sell_local_id LIKE l.cc_id
						AND created >= c.fecha_operacion
						AND created < DATE_ADD(c.fecha_operacion, INTERVAL +1 DAY)
						AND ticket_id NOT LIKE 'pm_%'
				) - (
					SELECT 
						IFNULL(SUM(
							CASE WHEN status IN ('Refunded') 
								THEN amount 
								ELSE winning 
					        END
						), 0)
					FROM tbl_repositorio_bingo_tickets
					WHERE 
						paid_local_id LIKE l.cc_id
						AND paid_at >= c.fecha_operacion
						AND paid_at < DATE_ADD(c.fecha_operacion, INTERVAL +1 DAY)
						AND status IN ('Paid', 'Refunded')
				) AS DECIMAL (20 , 2 ))) + (CAST((
			        	SELECT
			                (IFNULL(SUM(total), 0) - IFNULL(SUM(note_total), 0)) AS atsnacks_total
			            FROM
			                tbl_repositorio_atsnacks_resumen
			            WHERE
			                local_id = l.id
			                AND DATEDIFF(created_at, c.fecha_operacion) = 0
			    ) AS DECIMAL (20 , 2 ))) + (CAST(((
		        SELECT
	                IFNULL(SUM(monto_operacion), 0)
	            FROM
	                tbl_repositorio_kasnet_ventas
	            WHERE
	                local_id = l.id
                    AND DATEDIFF(fecha_operacion, c.fecha_operacion) = 0
                    AND descripcion_operacion IN (
	                    'Depositos' ,
	                    'Pago de Recaudo',
	                    'Pago Cuota',
	                    'Depositos',
	                    'Pago de Tarj. Cred.',
	                    'Ext. Retiros'
                    )
                    AND estado = 'Correcta') - (
                SELECT
	                IFNULL(SUM(monto_operacion), 0)
	            FROM
	                tbl_repositorio_kasnet_ventas
	            WHERE
	                local_id = l.id
                    AND DATEDIFF(fecha_operacion, c.fecha_operacion) = 0
                    AND descripcion_operacion IN (
	                    'Retiros' ,
	                    'Pago Visa',
	                    'Ext. Pago de Recaudo',
	                    'Ext. Depositos',
	                    'Disp. Efectivo',
	                    'Cobro de Remesas'
                    )
                    AND estado = 'Correcta')
		    ) AS DECIMAL (20 , 2 ))) AS sub_dinero_sistema,

		    CAST(SUM(0) AS DECIMAL (20 , 2 )) AS no_reclamado,

		    CAST((
		    	SELECT
	                SUM(tc.caja_fisico)
	            FROM
	                tbl_transacciones_cabecera tc
	            WHERE
	                tc.local_id = l.id 
	                AND tc.estado = '1'
	                AND tc.fecha = c.fecha_operacion
		        ) AS DECIMAL (20 , 2 )) + (CAST((
		        	SELECT
		                (IFNULL(SUM(total), 0) - IFNULL(SUM(note_total), 0)) AS atsnacks_total
		            FROM
		                tbl_repositorio_atsnacks_resumen
		            WHERE
		                local_id = l.id
		                AND DATEDIFF(created_at, c.fecha_operacion) = 0
		        ) AS DECIMAL (20 , 2 ))) + (CAST(((
		        SELECT
	                IFNULL(SUM(monto_operacion), 0)
	            FROM
	                tbl_repositorio_kasnet_ventas
	            WHERE
	                local_id = l.id
                    AND DATEDIFF(fecha_operacion, c.fecha_operacion) = 0
                    AND descripcion_operacion IN ('Depositos' , 'Pago de Recaudo',
                    'Pago Cuota',
                    'Depositos',
                    'Pago de Tarj. Cred.',
                    'Ext. Retiros')
                    AND estado = 'Correcta') - (
                SELECT
	                IFNULL(SUM(monto_operacion), 0)
	            FROM
	                tbl_repositorio_kasnet_ventas
	            WHERE
	                local_id = l.id
                    AND DATEDIFF(fecha_operacion, c.fecha_operacion) = 0
                    AND descripcion_operacion IN ('Retiros' , 'Pago Visa',
                    'Ext. Pago de Recaudo',
                    'Ext. Depositos',
                    'Disp. Efectivo',
                    'Cobro de Remesas')
                    AND estado = 'Correcta')
		    ) AS DECIMAL (20 , 2 ))) AS sub_resultado_voucher,

		    CAST((
		    	SELECT
		    		(SUM(ABS(IFNULL(pm.monto, 0)))) AS monto
		        FROM
		        	tbl_pago_manual pm
		        WHERE
		        	pm.estado = '1' AND pm.local_id = l.id
		        	AND pm.fecha_pago = CONCAT(c.fecha_operacion, ' 00:00:00')
		    ) AS DECIMAL (20 , 2 )) AS sistema_pagos_manuales,

		    CAST((
		    	SELECT
	                SUM(tr.open_win)
	            FROM
	                tbl_transacciones_repositorio tr
	            WHERE
	                tr.local_id = l.id AND tr.tipo = 4
                    AND tr.servicio_id = 3
                    AND tr.created = c.fecha_operacion
		    ) AS DECIMAL (20 , 2 )) AS premios_no_reclamados,

		    CAST((
		    	SELECT
	                SUM(tr.cancelled)
	            FROM
	                tbl_transacciones_repositorio tr
	            WHERE
	                tr.local_id = l.id AND tr.tipo = 4
                    AND tr.servicio_id = 3
                    AND tr.created = c.fecha_operacion
		    ) AS DECIMAL (20 , 2 )) AS sistema_devolucion,

			CAST((
				SELECT IFNULL(SUM(cashdesk_produccion),0) FROM tbl_transacciones_cabecera
				WHERE
					fecha = c.fecha_operacion
					AND local_id = l.id
					AND canal_de_venta_id = 16
					AND estado = 1
			) AS DECIMAL(20,2)) AS sistema_apuestas_deportivas,

			CAST((
				SELECT
					IFNULL(SUM(subcd.ingreso),0) - IFNULL(SUM(subcd.salida),0)
				FROM tbl_caja_detalle subcd
				INNER JOIN tbl_local_caja_detalle_tipos sublcdt ON sublcdt.id = subcd.tipo_id
				INNER JOIN tbl_caja_detalle_tipos subcdt ON subcdt.id = sublcdt.detalle_tipos_id
				INNER JOIN tbl_caja subc ON subc.id = subcd.caja_id
				INNER JOIN tbl_local_cajas sublc ON sublc.id = subc.local_caja_id
				WHERE
					subcdt.id = 1
					AND subc.fecha_operacion = c.fecha_operacion
				    AND sublc.local_id = l.id
			) AS DECIMAL(20,2)) AS cajero_apuestas_deportivas,

			CAST((
				SELECT
				-- 	IFNULL(SUM(income),0)
				-- FROM tbl_transacciones_detalle
					IFNULL(SUM(income_amount),0)
				FROM tbl_transacciones_repositorio
				WHERE
					created = c.fecha_operacion
					AND local_id = l.id
					AND tipo = 2
					AND canal_de_venta_id = 17
			) AS DECIMAL(20,2)) AS sistema_billeteros,

			CAST((
				SELECT
					IFNULL(SUM(subcd.ingreso),0) - IFNULL(SUM(subcd.salida),0)
				FROM tbl_caja_detalle subcd
				INNER JOIN tbl_local_caja_detalle_tipos sublcdt ON sublcdt.id = subcd.tipo_id
				INNER JOIN tbl_caja_detalle_tipos subcdt ON subcdt.id = sublcdt.detalle_tipos_id
				INNER JOIN tbl_caja subc ON subc.id = subcd.caja_id
				INNER JOIN tbl_local_cajas sublc ON sublc.id = subc.local_caja_id
				WHERE
					subcdt.id = 4
					AND subc.fecha_operacion = c.fecha_operacion
				    AND sublc.local_id = l.id
			) AS DECIMAL(20,2)) AS cajero_billeteros,

			CAST((
				SELECT
					-- (IFNULL(SUM(income),0) - IFNULL(SUM(ganado),0))
				-- FROM tbl_transacciones_detalle
					(IFNULL(SUM(stake),0) - IFNULL(SUM(paid_out_cash+jackpot_paid+mega_jackpot_paid),0))
				FROM tbl_transacciones_repositorio
				WHERE
					created = c.fecha_operacion
					AND local_id = l.id
					AND canal_de_venta_id = 21
					AND tipo = 4
					AND servicio_id = 3
			) AS DECIMAL(20,2)) AS sistema_goldenrace,

			CAST((
				SELECT
					IFNULL(SUM(subcd.ingreso),0) - IFNULL(SUM(subcd.salida),0)
				FROM tbl_caja_detalle subcd
				INNER JOIN tbl_local_caja_detalle_tipos sublcdt ON sublcdt.id = subcd.tipo_id
				INNER JOIN tbl_caja_detalle_tipos subcdt ON subcdt.id = sublcdt.detalle_tipos_id
				INNER JOIN tbl_caja subc ON subc.id = subcd.caja_id
				INNER JOIN tbl_local_cajas sublc ON sublc.id = subc.local_caja_id
				WHERE
					subcdt.id = 3
					AND subc.fecha_operacion = c.fecha_operacion
				    AND sublc.local_id = l.id
			) AS DECIMAL(20,2)) AS cajero_goldenrace,

			CAST((
				SELECT
					IFNULL(SUM(deposit),0) - IFNULL(SUM(withdraw),0)
				    FROM tbl_transacciones_detalle
				WHERE
					created = c.fecha_operacion
					AND local_id = l.id
					AND tipo = 3
					AND canal_de_venta_id = 16
			) AS DECIMAL(20,2)) AS sistema_web,

			CAST((
				SELECT
					IFNULL(SUM(subcd.ingreso),0) - IFNULL(SUM(subcd.salida),0)
				FROM tbl_caja_detalle subcd
				INNER JOIN tbl_local_caja_detalle_tipos sublcdt ON sublcdt.id = subcd.tipo_id
				INNER JOIN tbl_caja_detalle_tipos subcdt ON subcdt.id = sublcdt.detalle_tipos_id
				INNER JOIN tbl_caja subc ON subc.id = subcd.caja_id
				INNER JOIN tbl_local_cajas sublc ON sublc.id = subc.local_caja_id
				WHERE
					subcdt.id = 2
					AND subc.fecha_operacion = c.fecha_operacion
				    AND sublc.local_id = l.id
			) AS DECIMAL(20,2)) AS cajero_web,

			CAST((
				SELECT
					-- IFNULL(SUM(deposit),0) - IFNULL(SUM(withdraw),0)
					-- FROM tbl_transacciones_detalle
					IFNULL(SUM(terminal_deposit_amount),0) - IFNULL(SUM(terminal_withdraw_amount),0)
					FROM tbl_transacciones_repositorio
				WHERE
					created = c.fecha_operacion
					AND local_id = l.id
					AND tipo = 3
					AND canal_de_venta_id = 16
			) AS DECIMAL(20,2)) AS sistema_cash,

			CAST((
				SELECT
					IFNULL(SUM(subcd.ingreso),0) - IFNULL(SUM(subcd.salida),0)
				FROM tbl_caja_detalle subcd
				INNER JOIN tbl_local_caja_detalle_tipos sublcdt ON sublcdt.id = subcd.tipo_id
				INNER JOIN tbl_caja_detalle_tipos subcdt ON subcdt.id = sublcdt.detalle_tipos_id
				INNER JOIN tbl_caja subc ON subc.id = subcd.caja_id
				INNER JOIN tbl_local_cajas sublc ON sublc.id = subc.local_caja_id
				WHERE
					subcdt.id = 5
					AND subc.fecha_operacion = c.fecha_operacion
					AND sublc.local_id = l.id
			) AS DECIMAL(20,2)) AS cajero_cash,

			CAST((
				SELECT
					(IFNULL(SUM(total_in), 0) - IFNULL(SUM(total_out), 0))
				FROM tbl_repositorio_kasnet_resumen
				WHERE
					local_id = l.id
					AND created_at = c.fecha_operacion
			) AS DECIMAL(20,2)) AS sistema_kasnet,

			CAST((
				SELECT
					IFNULL(SUM(subcd.ingreso),0) - IFNULL(SUM(subcd.salida),0)
				FROM tbl_caja_detalle subcd
				INNER JOIN tbl_local_caja_detalle_tipos sublcdt ON sublcdt.id = subcd.tipo_id
				INNER JOIN tbl_caja_detalle_tipos subcdt ON subcdt.id = sublcdt.detalle_tipos_id
				INNER JOIN tbl_caja subc ON subc.id = subcd.caja_id
				INNER JOIN tbl_local_cajas sublc ON sublc.id = subc.local_caja_id
				WHERE
					subcdt.id = 13
					AND subc.fecha_operacion = c.fecha_operacion
				    AND sublc.local_id = l.id
			) AS DECIMAL(20,2)) AS cajero_kasnet,

			CAST((
				SELECT
					(IFNULL(SUM(total), 0) - IFNULL(SUM(note_total), 0))
				FROM tbl_repositorio_atsnacks_resumen
				WHERE
					local_id = l.id
					AND created_at = c.fecha_operacion
			) AS DECIMAL(20,2)) AS sistema_atsnacks,

			CAST((
				SELECT
					IFNULL(SUM(subcd.ingreso),0) - IFNULL(SUM(subcd.salida),0)
				FROM tbl_caja_detalle subcd
				INNER JOIN tbl_local_caja_detalle_tipos sublcdt ON sublcdt.id = subcd.tipo_id
				INNER JOIN tbl_caja_detalle_tipos subcdt ON subcdt.id = sublcdt.detalle_tipos_id
				INNER JOIN tbl_caja subc ON subc.id = subcd.caja_id
				INNER JOIN tbl_local_cajas sublc ON sublc.id = subc.local_caja_id
				WHERE
					subcdt.id = 17		
					AND subc.fecha_operacion = c.fecha_operacion
				    AND sublc.local_id = l.id
			) AS DECIMAL(20,2)) AS cajero_atsnacks,

			(CAST((
				SELECT 
					IFNULL(SUM(amount), 0)
				FROM tbl_repositorio_bingo_tickets
				WHERE 
					sell_local_id LIKE l.cc_id
					AND created >= c.fecha_operacion
					AND created < DATE_ADD(c.fecha_operacion, INTERVAL +1 DAY)
					AND ticket_id NOT LIKE 'pm_%'
			) - (
				SELECT 
					IFNULL(SUM(
						CASE WHEN status IN ('Refunded') 
							THEN amount 
							ELSE winning 
				        END
					), 0)
				FROM tbl_repositorio_bingo_tickets
				WHERE 
					paid_local_id LIKE l.cc_id
					AND paid_at >= c.fecha_operacion
					AND paid_at < DATE_ADD(c.fecha_operacion, INTERVAL +1 DAY)
					AND status IN ('Paid', 'Refunded')
					AND ticket_id NOT LIKE 'pm_%'
			) AS DECIMAL(20,2))) AS sistema_bingo,

			CAST((
				SELECT
					IFNULL(SUM(subcd.ingreso),0) - IFNULL(SUM(subcd.salida),0)
				FROM tbl_caja_detalle subcd
				INNER JOIN tbl_local_caja_detalle_tipos sublcdt ON sublcdt.id = subcd.tipo_id
				INNER JOIN tbl_caja_detalle_tipos subcdt ON subcdt.id = sublcdt.detalle_tipos_id
				INNER JOIN tbl_caja subc ON subc.id = subcd.caja_id
				INNER JOIN tbl_local_cajas sublc ON sublc.id = subc.local_caja_id
				WHERE
					subcdt.id = 15
					AND subc.fecha_operacion = c.fecha_operacion
				    AND sublc.local_id = l.id
			) AS DECIMAL(20,2)) AS cajero_bingo,
			
			CAST(IFNULL((
                SELECT SUM(sqtct.monto)
                FROM tbl_televentas_clientes_transaccion sqtct
                LEFT JOIN tbl_caja sqc
                ON sqc.id = sqtct.turno_id
                LEFT JOIN tbl_local_cajas sqlc
                ON sqlc.id = sqc.local_caja_id
                LEFT JOIN tbl_locales ssql
                ON ssql.id = sqlc.local_id
                WHERE
                    sqtct.tipo_id = 2 AND
                    ssql.id = l.id AND
                    sqc.fecha_operacion = c.fecha_operacion
                ),0) AS DECIMAL(20,2)) as televentas_sistema,
            
            CAST((
                SELECT SUM(IFNULL(sqcd.ingreso, 0) - IFNULL(sqcd.salida, 0)) as resultado
                FROM tbl_local_caja_detalle_tipos sqlcdt
                LEFT JOIN tbl_caja_detalle sqcd ON (sqcd.tipo_id = sqlcdt.id)
                LEFT JOIN tbl_caja sqc ON sqcd.caja_id = sqc.id
                WHERE
                    sqlcdt.local_id = l.id AND
                    sqlcdt.detalle_tipos_id = 18 AND -- WEB TELEVENTAS;
                    sqc.fecha_operacion = c.fecha_operacion
                ) AS DECIMAL(20,2)) as televentas_cajero
		FROM tbl_caja c
        LEFT JOIN tbl_local_cajas lc ON (lc.id = c.local_caja_id)
        LEFT JOIN tbl_locales l ON (l.id = lc.local_id)
        INNER JOIN tbl_usuarios_locales ul ON (ul.local_id = l.id AND ul.estado = 1)
        INNER JOIN tbl_usuarios u ON (u.id = ul.usuario_id)
        -- LEFT JOIN view_caja_auditoria ca ON (ca.local_id = l.id AND ca.fecha_operacion = c.fecha_operacion)
        WHERE
			(l.red_id IN (1,4,6,7,8,9) OR l.id = 200)
	        AND c.fecha_operacion >= '$fecha_inicio'
			AND c.fecha_operacion < '$fecha_fin'
	        $caja_where
		GROUP BY c.fecha_operacion, l.id
		ORDER BY c.fecha_operacion ASC, l.nombre ASC ) AS auditoria
	");
	if($mysqli->error){
		print_r($mysqli->error);
		exit();
	}
	$mysqli->query("COMMIT");
	while($r = $result->fetch_assoc()) $caja_data[] = $r;


	$table=[];
	$table[] = [
		"#ID",
		"Local",
		"Fecha",

		"Sistema Resultado",
		"Sistema Cajero",
		"Sistema Diferencia",

		"Pagos Manuales Sistema",
		"Pagos Manuales Cajero",
		"Pagos Manuales Diferencia",

		"Apuestas Deportivas Sistema",
		"Apuestas Deportivas Cajero",
		"Apuestas Deportivas Diferencia",

		"Billeteros Sistema",
		"Billeteros Cajero",
		"Billeteros Diferencia",

		"Golden Race Sistema",
		"Golden Race Cajero",
		"Golden Race Diferencia",

		"Bingo Sistema",
		"Bingo Cajero",
		"Bingo Diferencia",

		"Web Sistema",
		"Web Cajero",
		"Web Diferencia",

        "Web Televentas Sistema",
        "Web Televentas Cajero",
        "Web Televentas Diferencia",

		"Cash In Out Sistema",
		"Cash In Out Cajero",
		"Cash In Out Diferencia",

		"Kasnet Sistema",
		"Kasnet Cajero",
		"Kasnet Diferencia",

		"ATSnacks Sistema",
		"ATSnacks Cajero",
		"ATSnacks Diferencia",

		"Devolucion Sistema",
		"Devolucion Cajero",
		"Devolucion Diferencia",

		"Sistema Resultado",
		"Resultado Voucher",
		"Devolucion Sistema",
		"Pagos Manuales Sistema",
		"Diferencia"
	];

	foreach ($caja_data as $key => $value) {
		$tr=[
			$value["local_id"],
			$value["local_nombre"],
			$value["fecha_operacion"],

			$value["dinero_sistema"],
			$value["dinero_cajero"],
			($value["dinero_cajero"] - $value["dinero_sistema"]),

			(double)$value["sistema_pagos_manuales"],
			(double)$value["cajero_pagos_manuales"],
			((double)$value["cajero_pagos_manuales"]-(double)$value["sistema_pagos_manuales"]),

			$value["sistema_apuestas_deportivas"],
			$value["cajero_apuestas_deportivas"],
			($value["cajero_apuestas_deportivas"] - $value["sistema_apuestas_deportivas"]),

			$value["sistema_billeteros"],
			$value["cajero_billeteros"],
			($value["cajero_billeteros"] - $value["sistema_billeteros"]),

			$value["sistema_goldenrace"],
			$value["cajero_goldenrace"],
			($value["cajero_goldenrace"] - $value["sistema_goldenrace"]),

			$value["sistema_bingo"],
			$value["cajero_bingo"],
			($value["cajero_bingo"] - $value["sistema_bingo"]),

			$value["sistema_web"],
			$value["cajero_web"],
			($value["cajero_web"] - $value["sistema_web"]),

            $value["televentas_sistema"],
            $value["televentas_cajero"],
            ($value["televentas_cajero"] - $value["televentas_sistema"]),

			$value["sistema_cash"],
			$value["cajero_cash"],
			($value["cajero_cash"] - $value["sistema_cash"]),

			$value["sistema_kasnet"],
			$value["cajero_kasnet"],
			($value["cajero_kasnet"] - $value["sistema_kasnet"]),

			$value["sistema_atsnacks"],
			$value["cajero_atsnacks"],
			($value["cajero_atsnacks"] - $value["sistema_atsnacks"]),

			$value["sistema_devolucion"],
			$value["cajero_devolucion"],
			($value["cajero_devolucion"] - $value["sistema_devolucion"]),

			$value["dinero_sistema"],
			$value["resultado_voucher"],
			$value["sistema_devolucion"],
			(double)$value["sistema_pagos_manuales"],
			($value["dinero_sistema"] - ($value["resultado_voucher"]+$value["sistema_devolucion"]+(double)$value["sistema_pagos_manuales"]))
		];

		$table[]=$tr;
	}

	if (!empty($table)) {				
		require_once('../phpexcel/classes/PHPExcel.php');

		$doc = new PHPExcel();
		$doc->setActiveSheetIndex(0);
		$doc->getActiveSheet()->fromArray($table, null, 'A1', true);
		 
		$filename = "reporte_auditoria".date("d-m-Y",strtotime($get_data["fecha_inicio"]))."_al_".date("d-m-Y",strtotime($get_data["fecha_fin"]))."_".date("Ymdhis").".xls";
		$excel_path = '/var/www/html/export/files_export/caja_auditoriav2/'.$filename;

		$objWriter = PHPExcel_IOFactory::createWriter($doc, 'Excel5');
		$objWriter->save($excel_path);

		echo json_encode(array(
			"path" => '/export/files_export/caja_auditoriav2/'.$filename,
			"tipo" => "excel",
			"ext" => "xls",
			"size" => filesize($excel_path),
			"fecha_registro" => date("d-m-Y h:i:s"),
		));

		exit; 

	}else{
		echo json_encode(array(
			"error" => 'No hay resultados para mostrar'
		));
          
	}
}	

?>