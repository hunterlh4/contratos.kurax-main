<?php
if(isset($_POST["sec_caja_compare"])){
	// print_r($_POST);
	$get_data = $_POST["sec_caja_compare"];
	// print_r($get_data);
	// exit();
	$local_id = $get_data["local_id"];
	$fecha_inicio = $get_data["fecha_inicio"];
	$fecha_inicio_pretty = date("d-m-Y",strtotime($get_data["fecha_inicio"]));
	// $fecha_fin = $get_data["fecha_fin"];
	$fecha_fin = date("Y-m-d",strtotime($get_data["fecha_fin"]." +1 day"));
	$fecha_fin_pretty = date("d-m-Y",strtotime($get_data["fecha_fin"]));
	$caja_arr = [];
	$caja_command = "SELECT 
	    c.fecha_operacion,
	    l.id AS local_id,
	    l.nombre AS local_nombre,
	    ca.dinero_cajero,
	    CAST((SELECT 
				CAST((SUM(IF(r.tipo = 3,
							(r.bets_amount + r.terminal_deposit_amount + r.deposits),
							IF(r.tipo = 2,
								(r.income_amount),
								IF(r.tipo = 4, r.stake, 0))))) - (SUM(IF(r.tipo = 3,
							(r.paid_win_amount + r.terminal_withdraw_amount + r.withdrawals),
							IF(r.tipo = 2,
								(0),
								IF(r.tipo = 4,
									(IFNULL(r.paid_out_cash, 0) + IFNULL(r.jackpot_paid, 0) + IFNULL(r.mega_jackpot_paid, 0)),
									0)))))
						AS DECIMAL (20 , 2 )) AS total_balance
	            FROM tbl_transacciones_repositorio r
	            WHERE
	                r.local_id = l.id
					AND r.created = c.fecha_operacion
					AND r.tipo IN (2 , 3, 4))
	        AS DECIMAL (20 , 2 )) + 
			(CAST((SELECT (IFNULL(SUM(total),0) - IFNULL(SUM(note_total),0)) AS atsnacks_total FROM tbl_repositorio_atsnacks_resumen WHERE local_id = l.id AND datediff(created_at,c.fecha_operacion) = 0) AS DECIMAL(20,2))) + 
	        (CAST(((SELECT 
	                IFNULL(SUM(monto_operacion),0)
	            FROM tbl_repositorio_kasnet_ventas
	            WHERE
	                local_id = l.id
					AND datediff(fecha_operacion,c.fecha_operacion) = 0
					AND descripcion_operacion IN('Depositos','Pago de Recaudo', 'Pago Cuota', 'Depositos', 'Pago de Tarj. Cred.', 'Ext. Retiros','Enviar Dinero')
                    AND estado = 'Correcta') - (SELECT 
	                IFNULL(SUM(monto_operacion),0)
	            FROM tbl_repositorio_kasnet_ventas
	            WHERE
	                local_id = l.id
					AND datediff(fecha_operacion,c.fecha_operacion) = 0
					AND descripcion_operacion IN('Retiros','Pago Visa','Ext. Pago de Recaudo', 'Ext. Depositos', 'Disp. Efectivo', 'Cobro de Remesas')
                    AND estado = 'Correcta'))
	        AS DECIMAL (20 , 2 ))) AS dinero_sistema,
	    ca.cajero_pagos_manuales,
	    ca.devoluciones_cajero AS cajero_devolucion,
	    CAST(SUM(0) AS DECIMAL (20 , 2 )) AS no_reclamado,
	    CAST((SELECT 
	                SUM(tc.caja_fisico)
	            FROM tbl_transacciones_cabecera tc
	            WHERE
	                tc.local_id = l.id AND tc.estado = '1'
	                AND tc.fecha = c.fecha_operacion)
	        AS DECIMAL (20 , 2 )) + 
			(CAST((SELECT (IFNULL(SUM(total),0) - IFNULL(SUM(note_total),0)) AS atsnacks_total FROM tbl_repositorio_atsnacks_resumen WHERE local_id = l.id AND datediff(created_at,c.fecha_operacion) = 0) AS DECIMAL(20,2))) + 
	        (CAST(((SELECT 
	                IFNULL(SUM(monto_operacion),0)
	            FROM tbl_repositorio_kasnet_ventas
	            WHERE
	                local_id = l.id
					AND datediff(fecha_operacion,c.fecha_operacion) = 0
					AND descripcion_operacion IN('Depositos','Pago de Recaudo', 'Pago Cuota', 'Depositos', 'Pago de Tarj. Cred.', 'Ext. Retiros','Enviar Dinero')
                    AND estado = 'Correcta') - (SELECT 
	                IFNULL(SUM(monto_operacion),0)
	            FROM tbl_repositorio_kasnet_ventas
	            WHERE
	                local_id = l.id
					AND datediff(fecha_operacion,c.fecha_operacion) = 0
					AND descripcion_operacion IN('Retiros','Pago Visa','Ext. Pago de Recaudo', 'Ext. Depositos', 'Disp. Efectivo', 'Cobro de Remesas')
                    AND estado = 'Correcta'))
	        AS DECIMAL (20 , 2 ))) AS resultado_voucher,
	    CAST((SELECT 
	                (SUM(ABS(IFNULL(pm.monto, 0)))) AS monto
	            FROM tbl_pago_manual pm
	            WHERE
	                pm.estado = '1' AND pm.local_id = l.id
	                AND pm.fecha_pago = CONCAT(c.fecha_operacion, ' 00:00:00'))
	        AS DECIMAL (20 , 2 )) AS sistema_pagos_manuales,
	    CAST((SELECT 
	                SUM(tr.open_win)
	            FROM
	                tbl_transacciones_repositorio tr
	            WHERE
	                tr.local_id = l.id AND tr.tipo = 4
	                    AND tr.servicio_id = 3
	                    AND tr.created = c.fecha_operacion)
	        AS DECIMAL (20 , 2 )) AS premios_no_reclamados,
	    CAST((SELECT 
	                SUM(tr.cancelled)
	            FROM tbl_transacciones_repositorio tr
	            WHERE
	                tr.local_id = l.id AND tr.tipo = 4
					AND tr.servicio_id = 3
					AND tr.created = c.fecha_operacion)
	        AS DECIMAL (20 , 2 )) AS sistema_devolucion
	FROM tbl_caja c
	    LEFT JOIN tbl_local_cajas lc ON (lc.id = c.local_caja_id)
	    LEFT JOIN tbl_locales l ON (l.id = lc.local_id)
	    INNER JOIN tbl_usuarios_locales ul ON (ul.local_id = l.id AND ul.estado = 1)
	    INNER JOIN tbl_usuarios u ON (u.id = ul.usuario_id)
	    LEFT JOIN view_caja_auditoria ca ON (ca.local_id = l.id
	    AND ca.fecha_operacion = c.fecha_operacion)";
	$caja_command.=" WHERE (l.red_id = '1' OR l.id = 200)";
	if(!empty($login["usuario_locales"])) $caja_command .= "AND u.id = {$login['id']}";
	if($local_id=="_all_"){
		// $caja_command.=" WHERE l.id != 1";
	}else{
		$caja_command.=" AND l.id = '".$local_id."'";
	}
	$caja_command.=" AND c.fecha_operacion >= '".$fecha_inicio."'
					AND c.fecha_operacion < '".$fecha_fin."'
					GROUP BY c.fecha_operacion, l.id
					ORDER BY c.fecha_operacion ASC, l.nombre ASC
						";
	// echo $caja_command; exit();
	$mysqli->query("SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED");
	$caja_query = $mysqli->query($caja_command);
	if($mysqli->error){
		print_r($mysqli->error);
		exit();
	}
	$mysqli->query("COMMIT");
	$table=[];
	$table["tbody"]=[];
	$caja_data = [];
	while($c=$caja_query->fetch_assoc()){
		$caja_data[]=$c;
	}
	foreach ($caja_data as $key => $value) {
		// foreach ($value["datos_sistema"] as $kkey => $vvalue) {
		$tr=[];
		$tr["local_id"]=$value["local_id"];
		$tr["fecha_operacion"]=$value["fecha_operacion"];
		$tr["local_nombre"]=$value["local_nombre"];

		$tr["resultado_sistema"]=$value["dinero_sistema"];
		$tr["dinero_cajero"]=$value["dinero_cajero"];
		$tr["diferencia_resultado"]=($tr["dinero_cajero"] - $tr["resultado_sistema"]);


		$tr["cajero_pagos_manuales"]=$value["cajero_pagos_manuales"];
		$tr["sistema_pagos_manuales"]=$value["sistema_pagos_manuales"];
		$tr["diferencia_3"]=($tr["cajero_pagos_manuales"]-$tr["sistema_pagos_manuales"]);
		
		$tr["sistema_devolucion"]=$value["sistema_devolucion"];
		$tr["cajero_devolucion"]=$value["cajero_devolucion"];
		$tr["diferencia_4"]=($tr["sistema_devolucion"]-$tr["cajero_devolucion"]);

		$tr["resultado_voucher"]=$value["resultado_voucher"];
		$tr["premios_no_reclamados"]=$value["premios_no_reclamados"];
		$tr["diferencia_2"]=($tr["resultado_sistema"] - ($tr["resultado_voucher"]+$tr["sistema_devolucion"]+$tr["sistema_pagos_manuales"]));

		// $tr["canal_nombre"]=$value["lcdt_id"]." - ".$value["canal_nombre"];
		// $tr["s_ingreso"]=$value["s_ingreso"];
		// $tr["s_salida"]=$value["s_salida"];
		// $tr["opt"]="opt";
		$table["tbody"][]=$tr;
		// }
	}
	?>
	<!-- <pre><?php
	// echo $caja_command; exit();
	// print_r($caja_data); 
	?></pre> -->
	<?php
	// exit();
	?>
		<button type="submit" class="btn btn-warning btn-xs pull-right btn_export_caja_compare">
			<span class="glyphicon glyphicon-download-alt"></span>
			Exportar XLS
		</button>
	<table class="table table-condensed table-bordered ">
		<tr>
			<th colspan="1" rowspan="2">Fecha</th>
			<th colspan="1" rowspan="2">Local</th>

			<th colspan="3" rowspan="1">Resultado</th>
			<th colspan="5" rowspan="1">Validacion Sistema </th>
			<th colspan="3" rowspan="1">Pagos Manuales</th>
			<th colspan="3" rowspan="1">Devoluciones</th>

			


			<th colspan="2" rowspan="2">Opt</th>
		</tr>

		<tr>
			<th>Sistema</th>
			<th>Cajero</th>
			<th>Diferencia</th>


			<th>Resultado Sistema</th>
			<th>Resultado Voucher</th>
			<th>Devoluciones Sistema</th>
			<th>Pagos Manuales Sistema</th>
			<th>Diferencia</th>


			<th>Sistema</th>
			<th>Cajero</th>
			<th>Diferencia</th>


			<th>Sistema</th>
			<th>Cajero</th>
			<th>Diferencia</th>
		</tr>
		<?php
		foreach ($table["tbody"] as $k => $tr) {
			?>						
			<tr>
				<td><?php echo $tr["fecha_operacion"];?></td>
				<td><?php echo $tr["local_nombre"];?></td>

				<td><?php echo number_format($tr["resultado_sistema"],2);?></td>
				<td><?php echo number_format($tr["dinero_cajero"],2);?></td>
				<td class="<?php echo ($tr["diferencia_resultado"] != 0 ? "bg-danger text-white text-bold":"");?>"><?php echo number_format($tr["diferencia_resultado"],2);?></td>

				<td><?php echo number_format($tr["resultado_sistema"],2);?></td>
				<td><?php echo number_format($tr["resultado_voucher"],2);?></td>
				<td><?php echo number_format($tr["sistema_devolucion"],2);?></td>
				<td><?php echo number_format($tr["sistema_pagos_manuales"],2);?></td>
				<td class="<?php echo (intval($tr["diferencia_2"]) != 0 ?"bg-danger text-white text-bold":"");?>"><?php echo number_format($tr["diferencia_2"],2);?></td>

				<td><?php echo number_format($tr["sistema_pagos_manuales"],2);?></td>
				<td><?php echo number_format($tr["cajero_pagos_manuales"],2);?></td>
				<td class="<?php echo (intval($tr["diferencia_3"]) != 0 ?"bg-danger text-white text-bold":"");?>"><?php echo number_format($tr["diferencia_3"],2);?></td>

				<td><?php echo number_format($tr["sistema_devolucion"],2);?></td>
				<td><?php echo number_format($tr["cajero_devolucion"],2);?></td>
				<td class="<?php echo (intval($tr["diferencia_4"]) != 0 ?"bg-danger text-white text-bold":"");?>"><?php echo number_format($tr["diferencia_4"],2);?></td>
				<td><button 
						data-local_id="<?php echo $tr["local_id"];?>"
						data-fecha_inicio="<?php echo $tr["fecha_operacion"];?>"
						data-fecha_fin="<?php echo $tr["fecha_operacion"];?>"
						class="btn btn-default btn-xs detalle_btn"><i class="glyphicon glyphicon-new-window"></i> Detalle</button></td>
			</tr>
			<?php
		}
		?>
	</table>
	<?php
}
?>