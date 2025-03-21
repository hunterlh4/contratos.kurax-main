<?php
include_once("globalFunctions/generalInfo/caja_detalle_tipo.php");

$menu_id = "";
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'caja' AND sub_sec_id = 'auditoria' LIMIT 1");
while($r = $result->fetch_assoc()) $menu_id = $r["id"];

?>

<?php if(isset($_POST["sec_caja_auditoria"])){
	if(!array_key_exists($menu_id,$usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])){
		echo "No tienes permisos para acceder a este recurso";
		die;
	}
	$get_data = $_POST["sec_caja_auditoria"];

	$local_id = $get_data["local_id"];
	$fecha_inicio = $get_data["fecha_inicio"];
	$fecha_inicio_pretty = date("d-m-Y",strtotime($get_data["fecha_inicio"]));

	$fecha_fin = date("Y-m-d",strtotime($get_data["fecha_fin"]." +1 day"));
	$fecha_fin_pretty = date("d-m-Y",strtotime($get_data["fecha_fin"]));
	$caja_arr = [];

	$caja_where = "";
	$busqueda_bingo_cc_id = '=';
	if(!empty($login["usuario_locales"])) $caja_where .= " AND u.id = {$login['id']}";
	if($local_id!="_all_") $caja_where.=" AND l.id = '".$local_id."'";
	if($local_id!="_all_")  $busqueda_bingo_cc_id = "LIKE";
	$caja_data = [];
	$table=[];
	$table["tbody"]=[];

	$t_date_web_get = strtotime($fecha_inicio);
	$t_date_web_ito = strtotime(date('2023-02-28'));
	$query_date_web = "";
	if ($t_date_web_get <= $t_date_web_ito) {
		$query_date_web = "
		+			
			(CAST((
				IFNULL((
					SELECT SUM(IF(swt.tipo_id=1,swt.monto,0))-SUM(IF(swt.tipo_id=2,swt.monto,0))
					FROM tbl_saldo_web_transaccion swt
					LEFT JOIN tbl_caja sqc ON sqc.id = swt.turno_id
					LEFT JOIN tbl_local_cajas sqlc ON sqlc.id = sqc.local_caja_id
					LEFT JOIN tbl_locales ssql ON ssql.id = sqlc.local_id
					WHERE
						swt.tipo_id IN (1,2) AND 
						swt.status = 1 AND 
						ssql.id = l.id AND 
						sqc.fecha_operacion = c.fecha_operacion 
				), 0)
				) AS DECIMAL (20 , 2 )))
		";

	}

	
	//  Obtener listado de operaciones de ingresos

		$op_ingreso = [];

		$op_ingreso_query = $mysqli->query("SELECT o.nombre
								FROM tbl_kasnet_operacion AS o
								WHERE o.status = 1  AND o.tipo_id = 1");
		while($op_ingresos = $op_ingreso_query->fetch_assoc())
		{
			$op_ingreso[] = "'" . $mysqli->real_escape_string($op_ingresos['nombre']) . "'";
		}

		$op_ingreso_list = implode(', ', $op_ingreso);

	//  Obtener listado de operaciones de salida

		$op_salida = [];

		$op_salida_query = $mysqli->query("SELECT o.nombre
								FROM tbl_kasnet_operacion AS o
								WHERE o.status = 1  AND o.tipo_id = 2");
		while($op_salidas = $op_salida_query->fetch_assoc())
		{
			$op_salida[] = "'" . $mysqli->real_escape_string($op_salidas['nombre']) . "'";
		}

		$op_salida_list = implode(', ', $op_salida);

	$mysqli->query("SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED");
	$queryAudit = "
	    SELECT 
            *, 
            (
				IFNULL(sub_dinero_sistema,0) 
				+ IFNULL(sistema_kurax_mvr,0) + IFNULL(sistema_kurax_billeteros,0) + IFNULL(sistema_kurax_depo_reti_terminal,0) 
				+ IFNULL(televentas_sistema,2) + IFNULL(torito_sistema,2) + IFNULL(sistema_altenar,0)
			) as dinero_sistema, 
            (IFNULL(sub_resultado_voucher,0) + IFNULL(televentas_sistema,0) ) as resultado_voucher 
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
				SELECT 
				cast(sum(IF((cd_cdf.tipo_id = 8), cd_cdf.valor, 0)) AS DECIMAL(20, 2))
				FROM tbl_caja cd_c
				LEFT JOIN tbl_local_cajas cd_lc ON (cd_lc.id = cd_c.local_caja_id)
				LEFT JOIN tbl_caja_datos_fisicos cd_cdf ON (cd_cdf.caja_id = cd_c.id)
				WHERE cd_lc.local_id = l.id
				AND cd_c.fecha_operacion = c.fecha_operacion
			) AS cajero_devolucion,
			(
				SELECT
				cast(sum(IF((cd_cdf.tipo_id = 28), cd_cdf.valor, 0)) AS DECIMAL(20, 2) )
				FROM tbl_caja cd_c
				LEFT JOIN tbl_local_cajas cd_lc ON (cd_lc.id = cd_c.local_caja_id)
				LEFT JOIN tbl_caja_datos_fisicos cd_cdf ON (cd_cdf.caja_id = cd_c.id)
				WHERE cd_lc.local_id = l.id
				AND cd_c.fecha_operacion = c.fecha_operacion
			) AS cajero_devolucion_carrera_caballos,

			(
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
					) AS DECIMAL (20 , 2 )) 
				+ 
				(CAST(
					(
						SELECT
							IFNULL(SUM(amount), 0)
						FROM tbl_repositorio_bingo_tickets
						WHERE
							sell_local_id ".$busqueda_bingo_cc_id." l.cc_id
							AND created >= c.fecha_operacion
							AND created < DATE_ADD(c.fecha_operacion, INTERVAL +1 DAY)
							AND ticket_id NOT LIKE 'pm_%'
					) - 
					(
						SELECT
							IFNULL(SUM(
								CASE WHEN status IN ('Refunded')
									THEN amount
									ELSE winning
								END
							), 0)
						FROM tbl_repositorio_bingo_tickets
						WHERE
							paid_local_id ".$busqueda_bingo_cc_id." l.cc_id
							AND paid_at >= c.fecha_operacion
							AND paid_at < DATE_ADD(c.fecha_operacion, INTERVAL +1 DAY)
							AND status IN ('Paid', 'Refunded')
							AND ticket_id NOT LIKE 'pm_%'
					) AS DECIMAL (20 , 2 ))
				) 
				+ 
				(CAST((
							SELECT
								(IFNULL(SUM(total), 0) - IFNULL(SUM(note_total), 0)) AS atsnacks_total
							FROM
								tbl_repositorio_atsnacks_resumen
							WHERE
								local_id = l.id
								AND DATEDIFF(created_at, c.fecha_operacion) = 0
					) AS DECIMAL (20 , 2 ))
				) 
				+ 
				(CAST((
						(
							SELECT
								IFNULL(SUM(monto_operacion), 0)
							FROM
								tbl_repositorio_kasnet_ventas
							WHERE
								local_id = l.id
								AND DATEDIFF(fecha_operacion, c.fecha_operacion) = 0
								AND descripcion_operacion IN (
									$op_ingreso_list
								)
								AND estado = 'Correcta'
						) 
						- 
						(
							SELECT
								IFNULL(SUM(monto_operacion), 0)
							FROM
								tbl_repositorio_kasnet_ventas
							WHERE
								local_id = l.id
								AND DATEDIFF(fecha_operacion, c.fecha_operacion) = 0
								AND descripcion_operacion IN (
									$op_salida_list
								)
								AND estado = 'Correcta'
						)
					) AS DECIMAL (20 , 2 ))
				)
				+
				(
					CAST(
						(
							IFNULL(
								(
									SELECT IFNULL(SUM(importe), 0)
									FROM   tbl_repositorio_disashop_ventas
									WHERE  local_id = l.id
										   AND fecha >= c.fecha_operacion 
										   AND fecha < DATE_ADD(c.fecha_operacion , INTERVAL + 1 DAY)
								),
								0
							)
						) AS DECIMAL(20, 2)
					)
				)
				+
				CAST((
					SELECT (
						IFNULL(
							(
								sum(IF(rtas.transaction_type = 4, rtas.amount, 0))
							) -(sum(IF(rtas.transaction_type = 5, rtas.amount, 0))),
							0
						)
					) AS sistema_simulcast
					FROM   tbl_repositorio_tickets_america_simulcast AS rtas
					WHERE  rtas.creation_date >= c.fecha_operacion
							AND rtas.creation_date < DATE_ADD(c.fecha_operacion, INTERVAL +1 DAY)
							AND rtas.local_id = l.id  and  ticket_id is not null AND (rtas.bet_status !=2 OR rtas.bet_status IS NULL)
				) AS DECIMAL(20,2))
				+
				(CAST((
					IFNULL((
						SELECT SUM(IF(swt.tipo_id=1,swt.monto,0))-SUM(IF(swt.tipo_id=2,swt.monto,0))
						FROM tbl_saldo_web_transaccion swt
						LEFT JOIN tbl_caja sqc ON sqc.id = swt.turno_id
						LEFT JOIN tbl_local_cajas sqlc ON sqlc.id = sqc.local_caja_id
						LEFT JOIN tbl_locales ssql ON ssql.id = sqlc.local_id
						WHERE
							swt.tipo_id IN (1,2) AND 
							swt.status = 1 AND 
							ssql.id = l.id AND 
							sqc.fecha_operacion = c.fecha_operacion 
					), 0)
					) AS DECIMAL (20 , 2 ))
				)
				+
				(
					CAST((
						IFNULL((
							SELECT SUM(IF(st.tipo_id=1,st.monto,0))-SUM(IF(st.tipo_id=2,st.monto,0))
							FROM tbl_saldo_teleservicios_transaccion st
							LEFT JOIN tbl_caja cc ON cc.id = st.turno_id
							LEFT JOIN tbl_local_cajas lc ON lc.id = cc.local_caja_id
							LEFT JOIN tbl_locales lo ON lo.id = lc.local_id
							WHERE
								st.tipo_id IN (1,2) AND 
								st.status = 1 AND 
								lo.id = l.id AND 
								cc.fecha_operacion = c.fecha_operacion
						), 0)
					) AS DECIMAL(20,2))
				)
			) AS sub_dinero_sistema,

			CAST(SUM(0) AS DECIMAL (20 , 2 )) AS no_reclamado,

			(
				CAST((
					SELECT
						SUM(tc.caja_fisico)
					FROM
						tbl_transacciones_cabecera tc
					WHERE
						tc.local_id = l.id
						AND tc.estado = '1'
						AND tc.fecha = c.fecha_operacion
					) AS DECIMAL (20 , 2 ))
			)
			+
			(
				CAST((
					SELECT
						(IFNULL(SUM(total), 0) - IFNULL(SUM(note_total), 0)) AS atsnacks_total
					FROM
						tbl_repositorio_atsnacks_resumen
					WHERE
						local_id = l.id
						AND DATEDIFF(created_at, c.fecha_operacion) = 0
					) AS DECIMAL (20 , 2 ))
			) 
			+ 
			(
				CAST((
						(
							SELECT
								IFNULL(SUM(monto_operacion), 0)
							FROM
								tbl_repositorio_kasnet_ventas
							WHERE
								local_id = l.id
								AND DATEDIFF(fecha_operacion, c.fecha_operacion) = 0
								AND descripcion_operacion IN (
										$op_ingreso_list
									)
								AND estado = 'Correcta'
						) 
						- 
						(
							SELECT
								IFNULL(SUM(monto_operacion), 0)
							FROM
								tbl_repositorio_kasnet_ventas
							WHERE
								local_id = l.id
								AND DATEDIFF(fecha_operacion, c.fecha_operacion) = 0
								AND descripcion_operacion IN (
										$op_salida_list
									)
								AND estado = 'Correcta'
						)
					) AS DECIMAL (20 , 2 ))
			)
			+
				(
					CAST(
						(
							IFNULL(
								(
									SELECT IFNULL(SUM(importe), 0)
									FROM   tbl_repositorio_disashop_ventas
									WHERE  local_id = l.id
										   AND fecha >= c.fecha_operacion 
										   AND fecha < DATE_ADD(c.fecha_operacion , INTERVAL + 1 DAY)
								),
								0
							)
						) AS DECIMAL(20, 2)
					)
				)
			{$query_date_web}
			AS sub_resultado_voucher,

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
			) AS DECIMAL (20 , 2 )) 
			AS sistema_devolucion,
			
			CAST((
				SELECT (
					IFNULL(	(sum(IF(rtas.transaction_type = 10, rtas.amount, 0))), 0	)
				)
				FROM   tbl_repositorio_tickets_america_simulcast AS rtas
				WHERE  rtas.creation_date >= c.fecha_operacion
						AND rtas.creation_date < DATE_ADD(c.fecha_operacion, INTERVAL +1 DAY)
						AND rtas.local_id = l.id  and  ticket_id is not null AND (rtas.bet_status !=2 OR rtas.bet_status IS NULL)
			) AS DECIMAL(20,2))
			AS sistema_devolucion_carrera_caballos,

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
					IFNULL(SUM(cashdesk_produccion),0) 
				FROM tbl_transacciones_cabecera
				WHERE
					fecha = c.fecha_operacion
					AND local_id = l.id
					AND canal_de_venta_id = 42
					AND estado = 1
			) AS DECIMAL(20,2)) AS sistema_kurax_mvr,
			
			CAST((
				SELECT
					IFNULL(SUM(subcd.ingreso),0) - IFNULL(SUM(subcd.salida),0)
				FROM tbl_caja_detalle subcd
				INNER JOIN tbl_local_caja_detalle_tipos sublcdt ON sublcdt.id = subcd.tipo_id
				INNER JOIN tbl_caja_detalle_tipos subcdt ON subcdt.id = sublcdt.detalle_tipos_id
				INNER JOIN tbl_caja subc ON subc.id = subcd.caja_id
				INNER JOIN tbl_local_cajas sublc ON sublc.id = subc.local_caja_id
				WHERE
					subcdt.id = 27
					AND subc.fecha_operacion = c.fecha_operacion
					AND sublc.local_id = l.id
			) AS DECIMAL(20,2)) AS cajero_kurax_mvr,

			CAST((
				SELECT
					IFNULL(SUM(deposito_terminal),0)
				FROM kx_transacciones_consolidado
				WHERE
					fecha_consolidado = c.fecha_operacion
					AND local_id = l.id
					AND canal_de_venta_id = 43
					AND estado = 1
			) AS DECIMAL(20,2)) AS sistema_kurax_billeteros,

			CAST((
				SELECT
					IFNULL(SUM(subcd.ingreso),0) - IFNULL(SUM(subcd.salida),0)
				FROM tbl_caja_detalle subcd
				INNER JOIN tbl_local_caja_detalle_tipos sublcdt ON sublcdt.id = subcd.tipo_id
				INNER JOIN tbl_caja_detalle_tipos subcdt ON subcdt.id = sublcdt.detalle_tipos_id
				INNER JOIN tbl_caja subc ON subc.id = subcd.caja_id
				INNER JOIN tbl_local_cajas sublc ON sublc.id = subc.local_caja_id
				WHERE
					subcdt.id = 28
					AND subc.fecha_operacion = c.fecha_operacion
					AND sublc.local_id = l.id
			) AS DECIMAL(20,2)) AS cajero_kurax_billeteros,

			CAST((
				SELECT
					IFNULL(SUM(caja_deposito_terminal),0) - IFNULL(SUM(caja_retiro_terminal),0)
				FROM kx_transacciones_consolidado
				WHERE
					fecha_consolidado = c.fecha_operacion
					AND local_id = l.id
					AND canal_de_venta_id = 42
					AND estado = 1
			) AS DECIMAL(20,2)) AS sistema_kurax_depo_reti_terminal,

			CAST((
				SELECT
					IFNULL(SUM(subcd.ingreso),0) - IFNULL(SUM(subcd.salida),0)
				FROM tbl_caja_detalle subcd
				INNER JOIN tbl_local_caja_detalle_tipos sublcdt ON sublcdt.id = subcd.tipo_id
				INNER JOIN tbl_caja_detalle_tipos subcdt ON subcdt.id = sublcdt.detalle_tipos_id
				INNER JOIN tbl_caja subc ON subc.id = subcd.caja_id
				INNER JOIN tbl_local_cajas sublc ON sublc.id = subc.local_caja_id
				WHERE
					subcdt.id = 29
					AND subc.fecha_operacion = c.fecha_operacion
					AND sublc.local_id = l.id
			) AS DECIMAL(20,2)) AS cajero_kurax_depo_reti_terminal,

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


			0 AS sistema_nsoft,
			CAST((
				SELECT
					IFNULL(SUM(subcd.ingreso),0) - IFNULL(SUM(subcd.salida),0)
				FROM tbl_caja_detalle subcd
				INNER JOIN tbl_local_caja_detalle_tipos sublcdt ON sublcdt.id = subcd.tipo_id
				INNER JOIN tbl_caja_detalle_tipos subcdt ON subcdt.id = sublcdt.detalle_tipos_id
				INNER JOIN tbl_caja subc ON subc.id = subcd.caja_id
				INNER JOIN tbl_local_cajas sublc ON sublc.id = subc.local_caja_id
				WHERE
					subcdt.id = 24
					AND subc.fecha_operacion = c.fecha_operacion
					AND sublc.local_id = l.id
			) AS DECIMAL(20,2)) AS cajero_nsoft,

			0 AS sistema_kiron,
			CAST((
				SELECT
					IFNULL(SUM(subcd.ingreso),0) - IFNULL(SUM(subcd.salida),0)
				FROM tbl_caja_detalle subcd
				INNER JOIN tbl_local_caja_detalle_tipos sublcdt ON sublcdt.id = subcd.tipo_id
				INNER JOIN tbl_caja_detalle_tipos subcdt ON subcdt.id = sublcdt.detalle_tipos_id
				INNER JOIN tbl_caja subc ON subc.id = subcd.caja_id
				INNER JOIN tbl_local_cajas sublc ON sublc.id = subc.local_caja_id
				WHERE
					subcdt.id = 23
					AND subc.fecha_operacion = c.fecha_operacion
					AND sublc.local_id = l.id
			) AS DECIMAL(20,2)) AS cajero_kiron,

			
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
				SELECT (
					IFNULL(
						(
							sum(IF(rtas.transaction_type = 4, rtas.amount, 0)) 
						) -(sum(IF(rtas.transaction_type = 5, rtas.amount, 0))),
						0
					)
				) AS sistema_simulcast
				FROM   tbl_repositorio_tickets_america_simulcast AS rtas
				WHERE  rtas.creation_date >= c.fecha_operacion
						AND rtas.creation_date < DATE_ADD(c.fecha_operacion, INTERVAL +1 DAY)
						AND rtas.local_id = l.id  and  ticket_id is not null AND (rtas.bet_status !=2 OR rtas.bet_status IS NULL)
			) AS DECIMAL(20,2)) AS sistema_carreradecaballos,

			CAST((
				SELECT
					IFNULL(SUM(subcd.ingreso),0) - IFNULL(SUM(subcd.salida),0)
				FROM tbl_caja_detalle subcd
				INNER JOIN tbl_local_caja_detalle_tipos sublcdt ON sublcdt.id = subcd.tipo_id
				INNER JOIN tbl_caja_detalle_tipos subcdt ON subcdt.id = sublcdt.detalle_tipos_id
				INNER JOIN tbl_caja subc ON subc.id = subcd.caja_id
				INNER JOIN tbl_local_cajas sublc ON sublc.id = subc.local_caja_id
				WHERE
					subcdt.id = 20
					AND subc.fecha_operacion = c.fecha_operacion
					AND sublc.local_id = l.id
			) AS DECIMAL(20,2)) AS cajero_carreradecaballos,

			0 AS sistema_dsvirtualgaming,

			CAST((
				SELECT
					IFNULL(SUM(subcd.ingreso),0) - IFNULL(SUM(subcd.salida),0)
				FROM tbl_caja_detalle subcd
				INNER JOIN tbl_local_caja_detalle_tipos sublcdt ON sublcdt.id = subcd.tipo_id
				INNER JOIN tbl_caja_detalle_tipos subcdt ON subcdt.id = sublcdt.detalle_tipos_id
				INNER JOIN tbl_caja subc ON subc.id = subcd.caja_id
				INNER JOIN tbl_local_cajas sublc ON sublc.id = subc.local_caja_id
				WHERE
					subcdt.id = 22
					AND subc.fecha_operacion = c.fecha_operacion
					AND sublc.local_id = l.id
			) AS DECIMAL(20,2)) AS cajero_dsvirtualgaming,

			CAST((
				IFNULL((
					SELECT
						-- IFNULL(SUM(deposit),0) - IFNULL(SUM(withdraw),0)
						-- FROM tbl_transacciones_detalle
						IFNULL(SUM(deposits),0) - IFNULL(SUM(withdrawals),0)
						FROM tbl_transacciones_repositorio
					WHERE
						created = c.fecha_operacion
						AND local_id = l.id
						AND tipo = 3
						AND canal_de_venta_id = 16
				), 0)
				+
				IFNULL((
					SELECT SUM(IF(swt.tipo_id=1,swt.monto,0))-SUM(IF(swt.tipo_id=2,swt.monto,0))
					FROM tbl_saldo_web_transaccion swt
					LEFT JOIN tbl_caja sqc ON sqc.id = swt.turno_id
					LEFT JOIN tbl_local_cajas sqlc ON sqlc.id = sqc.local_caja_id
					LEFT JOIN tbl_locales ssql ON ssql.id = sqlc.local_id
					WHERE
						swt.tipo_id IN (1,2) AND 
						swt.status = 1 AND 
						ssql.id = l.id AND 
						sqc.fecha_operacion = c.fecha_operacion 
				), 0)
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
				 IFNULL((
					SELECT SUM(IF(st.tipo_id=1,st.monto,0))-SUM(IF(st.tipo_id=2,st.monto,0))
					FROM tbl_saldo_teleservicios_transaccion st
					LEFT JOIN tbl_caja cc ON cc.id = st.turno_id
					LEFT JOIN tbl_local_cajas lc ON lc.id = cc.local_caja_id
					LEFT JOIN tbl_locales lo ON lo.id = lc.local_id
					WHERE
						st.tipo_id IN (1,2) AND 
						st.status = 1 AND 
						lo.id = l.id AND 
						cc.fecha_operacion = c.fecha_operacion 
				), 0)
			) AS DECIMAL(20,2)) AS sistema_saldo_teleservicios,

			CAST((
				SELECT
					IFNULL(SUM(subcd.ingreso),0) - IFNULL(SUM(subcd.salida),0)
				FROM tbl_caja_detalle subcd
				INNER JOIN tbl_local_caja_detalle_tipos sublcdt ON sublcdt.id = subcd.tipo_id
				INNER JOIN tbl_caja_detalle_tipos subcdt ON subcdt.id = sublcdt.detalle_tipos_id
				INNER JOIN tbl_caja subc ON subc.id = subcd.caja_id
				INNER JOIN tbl_local_cajas sublc ON sublc.id = subc.local_caja_id
				WHERE
					subcdt.id = 26
					AND subc.fecha_operacion = c.fecha_operacion
					AND sublc.local_id = l.id
			) AS DECIMAL(20,2)) AS cajero_saldo_teleservicios,

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
				IFNULL((
					SELECT IFNULL(SUM(ct.monto),0) monto 
					FROM tbl_televentas_clientes_transaccion ct
					JOIN tbl_televentas_clientes c ON c.id = ct.cliente_id
					LEFT JOIN tbl_caja sqc ON sqc.id = ct.turno_id
					LEFT JOIN tbl_local_cajas sqlc ON sqlc.id = sqc.local_caja_id
					LEFT JOIN tbl_locales ssql ON ssql.id = sqlc.local_id 
					WHERE c.tipo_doc = 1 
					AND LENGTH(c.num_doc) = 4 
					AND ct.tipo_id IN (4) 
					AND ct.estado = 1
					AND ssql.id = l.id  
					AND sqc.fecha_operacion = c.fecha_operacion
				),0) - IFNULL((
					SELECT IFNULL(SUM(ct.monto),0) monto 
					FROM tbl_televentas_clientes_transaccion ct
					JOIN tbl_televentas_clientes c ON c.id = ct.cliente_id
					LEFT JOIN tbl_caja sqc ON sqc.id = ct.turno_id
					LEFT JOIN tbl_local_cajas sqlc ON sqlc.id = sqc.local_caja_id
					LEFT JOIN tbl_locales ssql ON ssql.id = sqlc.local_id 
					WHERE c.tipo_doc = 1 
					AND LENGTH(c.num_doc) = 4 
					AND ct.tipo_id IN (5, 19, 34) 
					AND ct.estado = 1
					AND ssql.id =  l.id 
					AND sqc.fecha_operacion = c.fecha_operacion
				),0)
			) AS DECIMAL(20, 2)) AS sistema_altenar,


			CAST((
				SELECT
					IFNULL(SUM(subcd.ingreso),0) - IFNULL(SUM(subcd.salida),0)
					FROM tbl_caja_detalle subcd
					INNER JOIN tbl_local_caja_detalle_tipos sublcdt ON sublcdt.id = subcd.tipo_id
					INNER JOIN tbl_caja_detalle_tipos subcdt ON subcdt.id = sublcdt.detalle_tipos_id
					INNER JOIN tbl_caja subc ON subc.id = subcd.caja_id
					INNER JOIN tbl_local_cajas sublc ON sublc.id = subc.local_caja_id
				WHERE
					subcdt.id = 25
					AND subc.fecha_operacion = c.fecha_operacion
					AND sublc.local_id = l.id
			) AS DECIMAL(20,2)) AS cajero_altenar,


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
					IFNULL(SUM(total_in), 0)
				FROM tbl_repositorio_disashop_resumen
				WHERE
					local_id = l.id
					AND created_at = c.fecha_operacion
			) AS DECIMAL(20,2)) AS sistema_disashop,

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
					IFNULL(SUM(subcd.ingreso),0) - IFNULL(SUM(subcd.salida),0)
				FROM tbl_caja_detalle subcd
				INNER JOIN tbl_local_caja_detalle_tipos sublcdt ON sublcdt.id = subcd.tipo_id
				INNER JOIN tbl_caja_detalle_tipos subcdt ON subcdt.id = sublcdt.detalle_tipos_id
				INNER JOIN tbl_caja subc ON subc.id = subcd.caja_id
				INNER JOIN tbl_local_cajas sublc ON sublc.id = subc.local_caja_id
				WHERE
					subcdt.id = 21
					AND subc.fecha_operacion = c.fecha_operacion
					AND sublc.local_id = l.id
			) AS DECIMAL(20,2)) AS cajero_disashop,

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
					sell_local_id ".$busqueda_bingo_cc_id." l.cc_id
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
					paid_local_id ".$busqueda_bingo_cc_id." l.cc_id
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
                SELECT SUM(sqtct.total_recarga)
                FROM tbl_televentas_clientes_transaccion sqtct
	                LEFT JOIN tbl_caja sqc ON sqc.id = sqtct.turno_id
	                LEFT JOIN tbl_local_cajas sqlc ON sqlc.id = sqc.local_caja_id
	                LEFT JOIN tbl_locales ssql ON ssql.id = sqlc.local_id
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
                ) AS DECIMAL(20,2)) as televentas_cajero,
			
			CAST(IFNULL((
						SELECT
							IFNULL(
								(
									(SUM(IF(tt.id_torito_tipo_transaccion = 1, tt.amount, 0))-
									SUM(IF(tt.id_torito_tipo_transaccion = 2, tt.amount, 0)))+
									(SUM(IF(tt.id_torito_tipo_transaccion = 4, tt.amount, 0))-
									SUM(IF(tt.id_torito_tipo_transaccion = 5, tt.amount, 0)))
								), 0) res
						FROM
							tbl_torito_transaccion tt
							JOIN tbl_torito_acceso ta ON ta.partnertoken = tt.partnertoken 
							AND ta.idcashier = tt.user_id AND tt.cc_id=ta.idstore
							JOIN tbl_caja sqc ON sqc.id = ta.turno_id
							JOIN tbl_local_cajas sqlc ON sqlc.id = sqc.local_caja_id
							JOIN tbl_locales ssql ON ssql.id = sqlc.local_id -- AND ssql.cc_id = ta.idstore
						WHERE
							tt.status = 1 AND 
							ta.status = 1 AND 
							ssql.id = l.id AND 
							tt.date = c.fecha_operacion AND 
							sqc.fecha_operacion = c.fecha_operacion
                ),0) AS DECIMAL(20,2)) as torito_sistema,
            CAST((
                SELECT SUM(IFNULL(sqcd.ingreso, 0) - IFNULL(sqcd.salida, 0)) as resultado
                FROM tbl_local_caja_detalle_tipos sqlcdt
                LEFT JOIN tbl_caja_detalle sqcd ON (sqcd.tipo_id = sqlcdt.id)
                LEFT JOIN tbl_caja sqc ON sqcd.caja_id = sqc.id
                WHERE
                    sqlcdt.local_id = l.id AND
                    sqlcdt.detalle_tipos_id = 19 AND -- WEB TORITO;
                    sqc.fecha_operacion = c.fecha_operacion
                ) AS DECIMAL(20,2)) as torito_cajero
			
		FROM tbl_caja c
		LEFT JOIN tbl_local_cajas lc ON (lc.id = c.local_caja_id)
		LEFT JOIN tbl_locales l ON (l.id = lc.local_id)
		INNER JOIN tbl_usuarios_locales ul ON (ul.local_id = l.id AND ul.estado = 1)
		INNER JOIN tbl_usuarios u ON (u.id = ul.usuario_id)
		-- LEFT JOIN view_caja_auditoria ca ON (ca.local_id = l.id AND ca.fecha_operacion = c.fecha_operacion)
		WHERE
			-- (l.red_id = '1' OR l.red_id = '4' OR l.id = 200)
			(l.red_id IN (1,4,6,7,8,9,16) OR l.id = 200)
			AND l.operativo = 1
			AND l.estado = 1
			AND c.fecha_operacion >= '$fecha_inicio'
			AND c.fecha_operacion < '$fecha_fin'
			$caja_where
		GROUP BY c.fecha_operacion, l.id
		ORDER BY c.fecha_operacion ASC, l.nombre ASC ) AS auditoria
	";

	$result = $mysqli->query($queryAudit);


	if($mysqli->error){
		print_r($mysqli->error);
		exit();
	}
	$mysqli->query("COMMIT");
	while($r = $result->fetch_assoc()) $caja_data[] = $r;

	foreach ($caja_data as $key => $value) {
		$tr=[];
		$tr["local_id"]=$value["local_id"];
		$tr["fecha_operacion"]=$value["fecha_operacion"];
		$tr["local_nombre"]=$value["local_nombre"];

		$tr["resultado_sistema"]=$value["dinero_sistema"];
		$tr["dinero_cajero"]=$value["dinero_cajero"];
		$tr["diferencia_resultado"]=($tr["dinero_cajero"] - $tr["resultado_sistema"]);

		$tr["cajero_kurax_mvr"]=$value["cajero_kurax_mvr"];
		$tr["sistema_kurax_mvr"]=$value["sistema_kurax_mvr"];
		$tr["diferencia_kurax_mvr"]=($tr["cajero_kurax_mvr"]-$tr["sistema_kurax_mvr"]);

		$tr["cajero_kurax_billeteros"]=$value["cajero_kurax_billeteros"];
		$tr["sistema_kurax_billeteros"]=$value["sistema_kurax_billeteros"];
		$tr["diferencia_kurax_billeteros"]=($tr["cajero_kurax_billeteros"]-$tr["sistema_kurax_billeteros"]);

		$tr["cajero_kurax_depo_reti_terminal"]=$value["cajero_kurax_depo_reti_terminal"];
		$tr["sistema_kurax_depo_reti_terminal"]=$value["sistema_kurax_depo_reti_terminal"];
		$tr["diferencia_kurax_depo_reti_terminal"]=($tr["cajero_kurax_depo_reti_terminal"]-$tr["sistema_kurax_depo_reti_terminal"]);

		// INICIO: GRUPO DE PAGOS MANUALES
		$tr["cajero_pagos_manuales"]=$value["cajero_pagos_manuales"];
		$tr["sistema_pagos_manuales"]=$value["sistema_pagos_manuales"];
		$tr["diferencia_3"]=($tr["cajero_pagos_manuales"]-$tr["sistema_pagos_manuales"]);
		// FIN: GRUPO DE PAGOS MANUALES

		$tr["sistema_apuestas_deportivas"]=$value["sistema_apuestas_deportivas"];
		$tr["cajero_apuestas_deportivas"]=$value["cajero_apuestas_deportivas"];
		$tr["diferencia_apuestas_deportivas"]=($tr["cajero_apuestas_deportivas"] - $tr["sistema_apuestas_deportivas"]);

		$tr["sistema_billeteros"]=$value["sistema_billeteros"];
		$tr["cajero_billeteros"]=$value["cajero_billeteros"];
		$tr["diferencia_billeteros"]=($tr["cajero_billeteros"] - $tr["sistema_billeteros"]);

		$tr["sistema_nsoft"]=$value["sistema_nsoft"];
		$tr["cajero_nsoft"]=$value["cajero_nsoft"];
		$tr["diferencia_nsoft"]=($tr["cajero_nsoft"] - $tr["sistema_nsoft"]);

		$tr["sistema_kiron"]=$value["sistema_kiron"];
		$tr["cajero_kiron"]=$value["cajero_kiron"];
		$tr["diferencia_kiron"]=($tr["cajero_kiron"] - $tr["sistema_kiron"]);

		$tr["sistema_goldenrace"]=$value["sistema_goldenrace"];
		$tr["cajero_goldenrace"]=$value["cajero_goldenrace"];
		$tr["diferencia_goldenrace"]=($tr["cajero_goldenrace"] - $tr["sistema_goldenrace"]);

		$tr["sistema_carreradecaballos"]=$value["sistema_carreradecaballos"];
		$tr["cajero_carreradecaballos"]=$value["cajero_carreradecaballos"];
		$tr["diferencia_carreradecaballos"]=($tr["cajero_carreradecaballos"] - $tr["sistema_carreradecaballos"]);

		$tr["sistema_dsvirtualgaming"]=$value["sistema_dsvirtualgaming"];
		$tr["cajero_dsvirtualgaming"]=$value["cajero_dsvirtualgaming"];
		$tr["diferencia_dsvirtualgaming"]=($tr["cajero_dsvirtualgaming"] - $tr["sistema_dsvirtualgaming"]);

		$tr["sistema_bingo"]=$value["sistema_bingo"];
		$tr["cajero_bingo"]=$value["cajero_bingo"];
		$tr["diferencia_bingo"]=($tr["cajero_bingo"] - $tr["sistema_bingo"]);

		$tr["sistema_web"]=$value["sistema_web"];
		$tr["cajero_web"]=$value["cajero_web"];
		$tr["diferencia_web"]=($tr["cajero_web"] - $tr["sistema_web"]);

		$tr["sistema_saldo_teleservicios"]=$value["sistema_saldo_teleservicios"];
		$tr["cajero_saldo_teleservicios"]=$value["cajero_saldo_teleservicios"];
		$tr["diferencia_saldo_teleservicios"]=($tr["cajero_saldo_teleservicios"] - $tr["sistema_saldo_teleservicios"]);

		$tr["sistema_web_televentas"]=$value["televentas_sistema"];
		$tr["cajero_web_televentas"]=$value["televentas_cajero"];
		$tr["diferencia_web_televentas"]=($tr["sistema_web_televentas"] - $tr["cajero_web_televentas"]);

		$tr["sistema_cash"]=$value["sistema_cash"];
		$tr["cajero_cash"]=$value["cajero_cash"];
		$tr["diferencia_cash"]=($tr["cajero_cash"] - $tr["sistema_cash"]);

		$tr["sistema_altenar"]=$value["sistema_altenar"];
		$tr["cajero_altenar"]=$value["cajero_altenar"];
		$tr["diferencia_altenar"]=($tr["sistema_altenar"] - $tr["cajero_altenar"]);

		$tr["sistema_kasnet"]=$value["sistema_kasnet"];
		$tr["cajero_kasnet"]=$value["cajero_kasnet"];
		$tr["diferencia_kasnet"]=($tr["cajero_kasnet"] - $tr["sistema_kasnet"]);

		$tr["sistema_disashop"]=$value["sistema_disashop"];
		$tr["cajero_disashop"]=$value["cajero_disashop"];
		$tr["diferencia_disashop"]=($tr["cajero_disashop"] - $tr["sistema_disashop"]);

		$tr["sistema_atsnacks"]=$value["sistema_atsnacks"];
		$tr["cajero_atsnacks"]=$value["cajero_atsnacks"];
		$tr["diferencia_atsnacks"]=($tr["cajero_atsnacks"] - $tr["sistema_atsnacks"]);

		$tr["sistema_devolucion"]=$value["sistema_devolucion"];
		$tr["cajero_devolucion"]=$value["cajero_devolucion"];
		$tr["diferencia_4"]=($tr["cajero_devolucion"] - $tr["sistema_devolucion"]);

		$tr["sistema_devolucion_carrera_caballos"]=$value["sistema_devolucion_carrera_caballos"];
		$tr["cajero_devolucion_carrera_caballos"]=$value["cajero_devolucion_carrera_caballos"];
		$tr["diferencia_5"]=($tr["cajero_devolucion_carrera_caballos"] - $tr["sistema_devolucion_carrera_caballos"]);


		$tr["sistema_web_torito"]=$value["torito_sistema"];
		$tr["cajero_web_torito"]=$value["torito_cajero"];
		$tr["diferencia_web_torito"]=($tr["sistema_web_torito"] - $tr["cajero_web_torito"]);

		$tr["resultado_voucher"]=$value["resultado_voucher"];
		$tr["premios_no_reclamados"]=$value["premios_no_reclamados"];
		$tr["diferencia_2"]=($tr["resultado_sistema"] - ($tr["resultado_voucher"]+$tr["sistema_devolucion_carrera_caballos"]+$tr["sistema_devolucion"]+$tr["sistema_pagos_manuales"]));

		$table["tbody"][]=$tr;
	}

	$caja_detalle_tipos = getAllCajaDetalleTipos();

?>
	<?php if(array_key_exists($menu_id,$usuario_permisos) && in_array("export", $usuario_permisos[$menu_id])): ?>
		<div class="row">
			<div class="col-lg-12">
				<button type="submit" class="btn btn-warning btn-xs btn_export_caja_auditoria">
					<span class="glyphicon glyphicon-download-alt"></span>
					Exportar XLS
				</button>
			</div>
		</div>
	<?php endif; ?>
	<div class="row tablaHeight">
		<table id="tbl_auditoria" class="table table-condensed table-small table-bordered table-striped" style="table-layout: fixed">
			<thead>
				<tr>
					<th style="width:190px; height: 58px;" colspan="1" rowspan="2">Local</th>
					<th style="width:100px; height: 58px;" colspan="1" rowspan="2" class="text-center">Fecha</th>
					<th style="width:80px; height: 58px;" colspan="1" rowspan="2" class="bg-warning text-center">Resultado<br/>Sistema</th>
					<th style="width:80px; height: 58px;" colspan="1" rowspan="2" class="bg-warning text-center">Resultado<br/>Cajero</th>
					<th style="width:80px; height: 58px;" colspan="1" rowspan="2" class="bg-warning text-center">Resultado<br/>Diferencia</th>
					<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">
						Pagos Manuales
					</th>
					<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center"><?php echo $caja_detalle_tipos[27]['nombre']; ?></th>
					<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center"><?php echo $caja_detalle_tipos[28]['nombre']; ?></th>
					<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center"><?php echo $caja_detalle_tipos[29]['nombre']; ?></th>
					
					<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">Apuestas Deportivas</th>
					<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">Billeteros</th>
					<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">Golden Race</th>
					<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">Carrera de Caballos</th>
					<!-- <th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">DS Virtual Gaming</th> -->
					<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">Bingo</th>
					<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">Web</th>
					<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">Web Televentas</th>
					<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">Cash In/Out</th>
					<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">Apuestas Deportiva ALTENAR</th>
					<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">Kasnet</th>
					<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">Disashop</th>
					<!-- <th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">ATSnacks</th> -->
					<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">Devoluciones</th>
					<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">Devoluciones Carrera de Caballos</th>
					<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">Torito</th>
					<!-- <th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">Nsoft</th> -->
					<!-- <th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">Kiron</th> -->
					<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">Saldo Teleservicios</th>
					<th style="width:500px;" colspan="6" rowspan="1" class="text-center">Validacion Sistema</th>
					<th style="width:85px;" colspan="1" rowspan="2" class="text-center">Opt</th>
				</tr>

				<tr>
					<!-- Pagos Manuales -->
					<th>Sistema&nbsp; </th>
					<th>Cajero&nbsp;&nbsp;</th>
					<th>Diferencia</th>

					<!--Kurax MVR -->
					<th>Sistema&nbsp; </th>
					<th>Cajero&nbsp;&nbsp;</th>
					<th>Diferencia</th>

					<!-- Kurax -->
					<th>Sistema&nbsp; </th>
					<th>Cajero&nbsp;&nbsp;</th>
					<th>Diferencia</th>

					<!-- Kurax -->
					<th>Sistema&nbsp; </th>
					<th>Cajero&nbsp;&nbsp;</th>
					<th>Diferencia</th>

					<!-- Caja -->
					<th>Sistema&nbsp;</th>
					<th>Cajero&nbsp;&nbsp;</th>
					<th>Diferencia</th>

					<!-- Billeteros -->
					<th>Sistema&nbsp;</th>
					<th>Cajero&nbsp;&nbsp;</th>
					<th>Diferencia</th>

					<!-- Nsoft -->
					<th>Sistema&nbsp;</th>
					<th>Cajero&nbsp;&nbsp;</th>
					<th>Diferencia</th>

					<!-- Kiron -->
					<th>Sistema&nbsp;</th>
					<th>Cajero&nbsp;&nbsp;</th>
					<th>Diferencia</th>

					<!-- Golden Race -
					<th>Sistema&nbsp;</th>
					<th>Cajero&nbsp;&nbsp;</th>
					<th>Diferencia</th> -->

					<!-- Carrera de Caballos -->
					<th>Sistema&nbsp;</th>
					<th>Cajero&nbsp;&nbsp;</th>
					<th>Diferencia</th>

					<!-- DS Virtual Gaming -->
					<th>Sistema&nbsp;</th>
					<th>Cajero&nbsp;&nbsp;</th>
					<th>Diferencia</th>

					<!-- Bingo -->
					<th>Sistema&nbsp;</th>
					<th>Cajero&nbsp;&nbsp;</th>
					<th>Diferencia</th>

					<!-- Web -->
					<th>Sistema&nbsp;</th>
					<th>Cajero&nbsp;&nbsp;</th>
					<th>Diferencia</th>

                    <!-- Web Televentas-->
					<th>Sistema</th>
					<th>Cajero</th>
					<th>Diferencia</th>

					<!-- Cash In/Out -->
					<th>Sistema&nbsp;</th>
					<th>Cajero&nbsp;&nbsp;</th>
					<th>Diferencia</th>

					<!-- Apuestas Deportivas ALTERNAR -->
					<th>Sistema&nbsp;</th>
					<th>Cajero&nbsp;&nbsp;</th>
					<th>Diferencia</th>

					<!-- Kasnet -->
					<!-- <th>Sistema&nbsp;</th>
					<th>Cajero&nbsp;&nbsp;</th>
					<th>Diferencia</th> -->

					<!-- Disashop -->
					<th>Sistema&nbsp;</th>
					<th>Cajero&nbsp;&nbsp;</th>
					<th>Diferencia</th>

					<!-- ATSnacks -->
					<th>Sistema&nbsp;</th>
					<th>Cajero&nbsp;&nbsp;</th>
					<th>Diferencia</th>

					<!-- Devoluciones -->
					<th>Sistema&nbsp;</th>
					<th>Cajero&nbsp;&nbsp;</th>
					<th>Diferencia</th>

					<!-- Devoluciones Carrera de Caballos -->
					<!-- <th>Sistema&nbsp;</th>
					<th>Cajero&nbsp;&nbsp;</th>
					<th>Diferencia</th> -->

					<!-- Torito -->
					<!-- <th>Sistema&nbsp;</th>
					<th>Cajero&nbsp;&nbsp;</th>
					<th>Diferencia</th> -->

					<!-- Saldo Teleservicios -->
					<th>Sistema&nbsp;</th>
					<th>Cajero&nbsp;&nbsp;</th>
					<th>Diferencia</th>

					<!-- Validacion Sistema -->
					<th alt="Result Sistema">Resultado <br/> Sistema</th>
					<th alt="Resultado Voucher.">Resultado <br/> Voucher</th>
					<th alt="Devoluciones Sistema">Devoluciones <br/> Sistema</th>
					<th alt="Devoluciones Sistema Carrera de Caballos">Devoluciones <br/> Carrera de Caballos </th>
					<th alt="Pagos Manuales Sistema">Pagos Manuales <br/> Sistema</th>
					<th alt="Diferencia">Diferencia</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($table["tbody"] as $k => $tr): ?>
					<tr style="height: 25px">
						<td style="height: 25px"><?php echo $tr["local_nombre"];?></td>
						<td style="height: 25px"><?php echo $tr["fecha_operacion"];?></td>

						<!-- Resultado -->
						<td style="height: 25px"><?php echo number_format($tr["resultado_sistema"],2);?></td>
						<td style="height: 25px"><?php echo number_format($tr["dinero_cajero"],2);?></td>
						<td style="height: 25px" class="<?php echo ((number_format($tr["diferencia_resultado"],2) != 0.00) ? "bg-danger text-white text-bold":""); ?>"><?php echo number_format($tr["diferencia_resultado"],2);?></td>

						<!-- Pagos Manuales -->
						<td><?php echo number_format($tr["sistema_pagos_manuales"],2);?></td>
						<td><?php echo number_format($tr["cajero_pagos_manuales"],2);?></td>
						<td style="height: 25px" class="<?php echo ((number_format($tr["diferencia_3"],2) != 0.00) ? "bg-danger text-white text-bold":""); ?>"><?php echo number_format($tr["diferencia_3"],2);?></td>

						<!-- KURAX MVR -->
						<td><?php echo number_format($tr["sistema_kurax_mvr"],2);?></td>
						<td><?php echo number_format($tr["cajero_kurax_mvr"],2);?></td>
						<td style="height: 25px" class="<?php echo get_class_diff($tr["diferencia_kurax_mvr"]);?>"><?php echo number_format($tr["diferencia_kurax_mvr"],2);?></td>

						<!-- KURAX Billeteros -->
						<td><?php echo number_format($tr["sistema_kurax_billeteros"],2);?></td>
						<td><?php echo number_format($tr["cajero_kurax_billeteros"],2);?></td>
						<td style="height: 25px" class="<?php echo get_class_diff($tr["diferencia_kurax_billeteros"]);?>"><?php echo number_format($tr["diferencia_kurax_billeteros"],2);?></td>

						<!-- KURAX Depósito/Retiro Terminal -->
						<td><?php echo number_format($tr["sistema_kurax_depo_reti_terminal"],2);?></td>
						<td><?php echo number_format($tr["cajero_kurax_depo_reti_terminal"],2);?></td>
						<td style="height: 25px" class="<?php echo get_class_diff($tr["diferencia_kurax_depo_reti_terminal"]);?>"><?php echo number_format($tr["diferencia_kurax_depo_reti_terminal"],2);?></td>

						<!-- Apuestas Deportivas -->
						<td style="height: 25px"><?php echo number_format($tr["sistema_apuestas_deportivas"],2);?></td>
						<td style="height: 25px"><?php echo number_format($tr["cajero_apuestas_deportivas"],2);?></td>
						<td style="height: 25px" class="<?php echo ((number_format($tr["diferencia_apuestas_deportivas"],2) != 0.00) ? "bg-danger text-white text-bold":""); ?>"><?php echo number_format($tr["diferencia_apuestas_deportivas"],2);?></td>

						<!-- Billeteros -->
						<td style="height: 25px"><?php echo number_format($tr["sistema_billeteros"],2);?></td>
						<td style="height: 25px"><?php echo number_format($tr["cajero_billeteros"],2);?></td>
						<td style="height: 25px" class="<?php echo ((number_format($tr["diferencia_billeteros"],2) != 0.00) ? "bg-danger text-white text-bold":""); ?>"><?php echo number_format($tr["diferencia_billeteros"],2);?></td>


						

						<!-- Golden Race -->
						<td style="height: 25px"><?php echo number_format($tr["sistema_goldenrace"],2);?></td>
						<td style="height: 25px"><?php echo number_format($tr["cajero_goldenrace"],2);?></td>
						<td style="height: 25px" class="<?php echo ((number_format($tr["diferencia_goldenrace"],2) != 0.00) ? "bg-danger text-white text-bold":""); ?>"><?php echo number_format($tr["diferencia_goldenrace"],2);?></td>

						<!-- Carrera de Caballos -->
						<td style="height: 25px"><?php echo number_format($tr["sistema_carreradecaballos"],2);?></td>
						<td style="height: 25px"><?php echo number_format($tr["cajero_carreradecaballos"],2);?></td>
						<td style="height: 25px" class="<?php echo ((number_format($tr["diferencia_carreradecaballos"],2) != 0.00) ? "bg-danger text-white text-bold":""); ?>"><?php echo number_format($tr["diferencia_carreradecaballos"],2);?></td>

						<!-- DS Virtual Gaming -->
						<!-- <td style="height: 25px"><?php echo number_format($tr["sistema_dsvirtualgaming"],2);?></td>
						<td style="height: 25px"><?php echo number_format($tr["cajero_dsvirtualgaming"],2);?></td>
						<td style="height: 25px" class="<?php echo ((number_format($tr["diferencia_dsvirtualgaming"],2) != 0.00) ? "bg-danger text-white text-bold":""); ?>"><?php echo number_format($tr["diferencia_dsvirtualgaming"],2);?></td> -->


						<!-- Bingo -->
						<td style="height: 25px"><?php echo number_format($tr["sistema_bingo"],2);?></td>
						<td style="height: 25px"><?php echo number_format($tr["cajero_bingo"],2);?></td>
						<td style="height: 25px" class="<?php echo ((number_format($tr["diferencia_bingo"],2) != 0.00) ? "bg-danger text-white text-bold":""); ?>"><?php echo number_format($tr["diferencia_bingo"],2);?></td>

						<!-- Web -->
						<td style="height: 25px"><?php echo number_format($tr["sistema_web"],2);?></td>
						<td style="height: 25px"><?php echo number_format($tr["cajero_web"],2);?></td>
						<td style="height: 25px" class="<?php echo ((number_format($tr["diferencia_web"],2) != 0.00) ? "bg-danger text-white text-bold":""); ?>"><?php echo number_format($tr["diferencia_web"],2);?></td>

                        <!-- Web Televentas -->
                        <td style="height: 25px"><?php echo number_format($tr["sistema_web_televentas"],2);?></td>
                        <td style="height: 25px"><?php echo number_format($tr["cajero_web_televentas"],2);?></td>
                        <td style="height: 25px" class="<?php echo ((number_format($tr["diferencia_web_televentas"],2) != 0.00) ? "bg-danger text-white text-bold":""); ?>"><?php echo number_format($tr["diferencia_web_televentas"],2);?></td>

						<!-- Cash In/Out -->
						<td style="height: 25px"><?php echo number_format($tr["sistema_cash"],2);?></td>
						<td style="height: 25px"><?php echo number_format($tr["cajero_cash"],2);?></td>
						<td style="height: 25px" class="<?php echo ((number_format($tr["diferencia_cash"],2) != 0.00) ? "bg-danger text-white text-bold":""); ?>"><?php echo number_format($tr["diferencia_cash"],2);?></td>

						<!-- Apuestas Deportivas ALTENAR -->
						<td style="height: 25px"><?php echo number_format($tr["sistema_altenar"],2);?></td>
						<td style="height: 25px"><?php echo number_format($tr["cajero_altenar"],2);?></td>
						<td style="height: 25px" class="<?php echo ((number_format($tr["diferencia_altenar"],2) != 0.00) ? "bg-danger text-white text-bold":""); ?>"><?php echo number_format($tr["diferencia_altenar"],2);?></td>

						<!-- Kasnet -->
						<td style="height: 25px"><?php echo number_format($tr["sistema_kasnet"],2);?></td>
						<td style="height: 25px"><?php echo number_format($tr["cajero_kasnet"],2);?></td>
						<td style="height: 25px" class="<?php echo ((number_format($tr["diferencia_kasnet"],2) != 0.00) ? "bg-danger text-white text-bold":""); ?>"><?php echo number_format($tr["diferencia_kasnet"],2);?></td>

						<!-- Disashop -->
						<td style="height: 25px"><?php echo number_format($tr["sistema_disashop"],2);?></td>
						<td style="height: 25px"><?php echo number_format($tr["cajero_disashop"],2);?></td>
						<td style="height: 25px" class="<?php echo ((number_format($tr["diferencia_disashop"],2) != 0.00) ? "bg-danger text-white text-bold":""); ?>"><?php echo number_format($tr["diferencia_disashop"],2);?></td>

						<!-- ATSnacks -->
						<!-- <td style="height: 25px"><?php echo number_format($tr["sistema_atsnacks"],2);?></td>
						<td style="height: 25px"><?php echo number_format($tr["cajero_atsnacks"],2);?></td>
						<td style="height: 25px" class="<?php echo ((number_format($tr["diferencia_atsnacks"],2) != 0.00) ? "bg-danger text-white text-bold":""); ?>"><?php echo number_format($tr["diferencia_atsnacks"],2);?></td> -->

						<!-- Devoluciones -->
						<td style="height: 25px"><?php echo number_format($tr["sistema_devolucion"],2);?></td>
						<td style="height: 25px"><?php echo number_format($tr["cajero_devolucion"],2);?></td>
						<td style="height: 25px" class="<?php echo ((number_format($tr["diferencia_4"],2) != 0.00) ? "bg-danger text-white text-bold":""); ?>"><?php echo number_format($tr["diferencia_4"],2);?></td>

						<!-- Devoluciones Carrera de Caballos-->
						<td style="height: 25px"><?php echo number_format($tr["sistema_devolucion_carrera_caballos"],2);?></td>
						<td style="height: 25px"><?php echo number_format($tr["cajero_devolucion_carrera_caballos"],2);?></td>
						<td style="height: 25px" class="<?php echo ((number_format($tr["diferencia_5"],2) != 0.00) ? "bg-danger text-white text-bold":""); ?>"><?php echo number_format($tr["diferencia_5"],2);?></td>

						<!-- Torito -->
						<td style="height: 25px"><?php echo number_format($tr["sistema_web_torito"],2);?></td>
						<td style="height: 25px"><?php echo number_format($tr["cajero_web_torito"],2);?></td>
						<td style="height: 25px" class="<?php echo ((number_format($tr["diferencia_web_torito"],2) != 0.00) ? "bg-danger text-white text-bold":""); ?>"><?php echo number_format($tr["diferencia_web_torito"],2);?></td>


						<!-- NSOFT -->
						<!-- <td style="height: 25px"><?php echo number_format($tr["sistema_nsoft"],2);?></td>
						<td style="height: 25px"><?php echo number_format($tr["cajero_nsoft"],2);?></td>
						<td style="height: 25px" class="<?php echo ((number_format($tr["diferencia_nsoft"],2) != 0.00) ? "bg-danger text-white text-bold":""); ?>"><?php echo number_format($tr["diferencia_nsoft"],2);?></td> -->

						<!-- KIRON -->
						<!-- <td style="height: 25px"><?php echo number_format($tr["sistema_kiron"],2);?></td>
						<td style="height: 25px"><?php echo number_format($tr["cajero_kiron"],2);?></td>
						<td style="height: 25px" class="<?php echo ((number_format($tr["diferencia_kiron"],2) != 0.00) ? "bg-danger text-white text-bold":""); ?>"><?php echo number_format($tr["diferencia_kiron"],2);?></td> -->
						
						<!-- SALDO TELESERVICIOS -->
						<td style="height: 25px"><?php echo number_format($tr["sistema_saldo_teleservicios"],2);?></td>
						<td style="height: 25px"><?php echo number_format($tr["cajero_saldo_teleservicios"],2);?></td>
						<td style="height: 25px" class="<?php echo ((number_format($tr["diferencia_saldo_teleservicios"],2) != 0.00) ? "bg-danger text-white text-bold":""); ?>"><?php echo number_format($tr["diferencia_saldo_teleservicios"],2);?></td>

						<!-- Validacion Sistema -->
						<td style="height: 25px"><?php echo number_format($tr["resultado_sistema"],2);?></td>
						<td style="height: 25px"><?php echo number_format($tr["resultado_voucher"],2);?></td>
						<td style="height: 25px"><?php echo number_format($tr["sistema_devolucion"],2);?></td>
						<td style="height: 25px"><?php echo number_format($tr["sistema_devolucion_carrera_caballos"],2);?></td>
						<td style="height: 25px"><?php echo number_format($tr["sistema_pagos_manuales"],2);?></td>
						<td style="height: 25px" class="<?php echo ((number_format($tr["diferencia_2"],2) != 0.00) ? "bg-danger text-white text-bold":""  ); ?>"><?php echo number_format($tr["diferencia_2"],2);?></td>


						<!-- OPT -->
						<td style="padding: 0.3rem;">
							<button
								data-local_id="<?php echo $tr["local_id"];?>"
								data-fecha_inicio="<?php echo $tr["fecha_operacion"];?>"
								data-fecha_fin="<?php echo $tr["fecha_operacion"];?>"
								class="btn btn-secondary btn-sm detalle_btn btn-xs"><i class="glyphicon glyphicon-new-window"></i> Detalle
							</button>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php } 

function get_class_diff($diferencia){
	return ((number_format($diferencia, 2) != 0.00) ? "bg-danger text-white text-bold":""); 
}
?>