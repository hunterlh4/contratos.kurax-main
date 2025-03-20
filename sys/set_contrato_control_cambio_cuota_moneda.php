<?php
date_default_timezone_set("America/Lima");


include("db_connect.php");
include("sys_login.php");


function ActualizarCambioCuotaMoneda($contrato_id){

	include("db_connect.php");
	include("sys_login.php");
	
	
	// INICIO
	// $contrato_id = 873;
	$query_contrato = "SELECT 
		c.contrato_id,
		c.tipo_inflacion_id,
		c.empresa_suscribe_id
	FROM cont_contrato AS c 
	WHERE c.contrato_id = ".$contrato_id;
	$data_cont = $mysqli->query($query_contrato);
	$row_cont = $data_cont->fetch_assoc();

	
	$query_cond_econ = "SELECT 
	c.condicion_economica_id,
	c.contrato_id,
	c.monto_renta,
	c.tipo_moneda_id,
	m.nombre AS moneda_contrato,
	concat(m.nombre,' (', m.simbolo,')') AS moneda_contrato_con_simbolo,
	m.simbolo AS simbolo_moneda,
	c.pago_renta_id,
	c.afectacion_igv_id,
	c.cuota_variable,
	tpr.nombre AS pago_renta,
	tv.nombre AS tipo_venta,
	tai.nombre AS igv_en_la_renta,
	c.impuesto_a_la_renta_id,
	i.nombre AS impuesto_a_la_renta,
	c.carta_de_instruccion_id,
	ci.nombre AS carta_de_instruccion,
	c.numero_cuenta_detraccion,
	c.garantia_monto,
	c.tipo_adelanto_id,
	a.nombre AS tipo_adelanto,
	c.plazo_id,
	tp.nombre as nombre_plazo,
	c.cant_meses_contrato,
	c.fecha_inicio,
	c.fecha_fin,
	c.periodo_gracia_id,
	p.nombre AS periodo_gracia,
	c.periodo_gracia_numero,
	c.periodo_gracia_inicio,
	c.periodo_gracia_fin,
	dp.nombre AS dia_de_pago,
	c.num_alerta_vencimiento,
	c.cargo_mantenimiento,
	c.fecha_suscripcion,
	c.status,
	c.user_created_id,
	c.created_at,
	c.user_updated_id,
	c.updated_at,
	c.tipo_incremento_id
	FROM cont_condicion_economica c
	INNER JOIN tbl_moneda m ON c.tipo_moneda_id = m.id 
	INNER JOIN cont_tipo_impuesto_a_la_renta i ON c.impuesto_a_la_renta_id = i.id
	INNER JOIN cont_tipo_adelanto a ON c.tipo_adelanto_id = a.id
	LEFT JOIN cont_tipo_periodo_de_gracia p ON c.periodo_gracia_id = p.id
	LEFT JOIN cont_tipo_carta_de_instruccion ci ON c.carta_de_instruccion_id = ci.id
	LEFT JOIN cont_tipo_dia_de_pago dp ON c.dia_de_pago_id = dp.id
	LEFT JOIN cont_tipo_plazo tp ON c.plazo_id = tp.id
	LEFT JOIN cont_tipo_pago_renta tpr ON c.pago_renta_id = tpr.id
	LEFT JOIN cont_tipo_venta tv ON c.tipo_venta_id = tv.id
	LEFT JOIN cont_tipo_afectacion_igv tai ON c.afectacion_igv_id = tai.id
	WHERE c.status = 1 AND c.contrato_id = " . $contrato_id;
	$data_cond_eco = $mysqli->query($query_cond_econ);
	$row_cond_eco = $data_cond_eco->fetch_assoc();

	$tipo_inflacion_id = $row_cont['tipo_inflacion_id'];
	$tipo_incremento_id = $row_cond_eco['tipo_incremento_id'];

	//DATOS DE CONDICIONES ECONOMICAS
	$monto_renta = $row_cond_eco['monto_renta'];
	$impuesto_a_la_renta_id = $row_cond_eco['impuesto_a_la_renta_id'];
	$afectacion_igv_id = $row_cond_eco['afectacion_igv_id'];
	$tipo_moneda_id = $row_cond_eco['tipo_moneda_id'];
	$plazo_id = $row_cond_eco['plazo_id'];
	$cant_meses_contrato = $row_cond_eco['cant_meses_contrato'];
	$fecha_inicio = $row_cond_eco['fecha_inicio'];
	$fecha_fin = $row_cond_eco['fecha_fin'];
	$pago_renta_id = $row_cond_eco['pago_renta_id'];

	


	//ELIMINAR CONTROL DE CAMBIO PARA ACTUALIZAR 
	$query_delete_contorl = "DELETE FROM cont_control_cambio_cuota_moneda WHERE contrato_id = ".$contrato_id;
	$mysqli->query($query_delete_contorl);



	// DETERMINAR CANTIDAD DE AÑOS DEL CONTRATO
	$date1 = new DateTime($row_cond_eco['fecha_inicio']);
	$date2 = new DateTime($row_cond_eco['fecha_fin']);
	$diff = $date1->diff($date2);
	$anios_contrato = $diff->y;
	$meses_contrato = ($anios_contrato * 12) + $diff->m;

	// INCREMENTOS
	$query_incr = "SELECT i.id,i.valor,i.tipo_valor_id,i.tipo_continuidad_id,i.a_partir_del_año,i.fecha_cambio
	FROM cont_incrementos i
	WHERE 1 = 1 AND i.tipo_valor_id = 2 AND i.fecha_cambio  IS NOT NULL AND (i.tipo_continuidad_id = 3 OR i.tipo_continuidad_id = 2) AND i.contrato_id = ".$contrato_id." AND i.estado = 1 ORDER BY i.fecha_cambio ASC";
	$data_incr = $mysqli->query($query_incr);
	while($li=$data_incr->fetch_assoc()){
		
		if ($li['tipo_continuidad_id'] == 3) { // INCREMENTO ANUAL
			for ($i=0; $i < $anios_contrato ; $i++) { 
				$nueva_fecha = date("Y-m-d",strtotime($li['fecha_cambio']."+ ".$i." years")); 
				if ($row_cond_eco['fecha_fin'] > $nueva_fecha) {
					$id_control = VerificarControlCambio($contrato_id,$nueva_fecha);
					if ($id_control == 0) {
						//REGISTRO DEL CONTROL EN CASO QUE NO EXISTA
						$query_inc = "INSERT INTO cont_control_cambio_cuota_moneda (
							contrato_id,fecha,incremento,porcentaje_incremento,status) VALUES (
							".$contrato_id.", '".$nueva_fecha."', 'SI', ".$li['valor'].",1) ";
						$query = $mysqli->query($query_inc);
					}
				}
			}
		}else if ($li['tipo_continuidad_id'] == 2) { // ANUAL A PARTIR DEL X AÑOS
			$nueva_fecha = $li['fecha_cambio']; 
			if ($row_cond_eco['fecha_fin'] > $nueva_fecha) {
				$id_control = VerificarControlCambio($contrato_id,$nueva_fecha);
				if ($id_control == 0) {
					//REGISTRO DEL CONTROL EN CASO QUE NO EXISTA
					$query_inc = "INSERT INTO cont_control_cambio_cuota_moneda (
						contrato_id,fecha,incremento,porcentaje_incremento,status) VALUES (
						".$contrato_id.", '".$nueva_fecha."', 'SI', ".$li['valor'].",1) ";
					$query = $mysqli->query($query_inc);
				}
			}
		}
	}
	

	// INFLACION
	$query_inf = "SELECT i.id, i.fecha, i.tipo_periodicidad_id, i.numero, i.tipo_anio_mes, i.moneda_id, i.porcentaje_anadido, i.tope_inflacion, i.minimo_inflacion 
	FROM cont_inflaciones AS i
	WHERE i.status = 1 AND i.contrato_id = ".$contrato_id." ORDER BY i.fecha ASC";
	$data_inf = $mysqli->query($query_inf);
	$indice_inflacion = 3; // INDICE DE INFLACION
	while($li=$data_inf->fetch_assoc()){
		if ($li['porcentaje_anadido'] > 0) {
			$indice_inflacion = $indice_inflacion + $li['porcentaje_anadido']; 
		}
		if($li['tipo_periodicidad_id'] == 1){ //CADA
			if ($li['tipo_anio_mes'] == 1) { // AÑO
				for ($i=0; $i < $anios_contrato ; $i++) { 
					$nueva_fecha = date("Y-m-d",strtotime($li['fecha']."+ ".$i." year")); 
					if ($row_cond_eco['fecha_fin'] > $nueva_fecha) {
						$id_control = VerificarControlCambio($contrato_id,$nueva_fecha);
						if ($id_control == 0) {
							//REGISTRO DEL CONTROL EN CASO QUE NO EXISTA
							$query_inc = "INSERT INTO cont_control_cambio_cuota_moneda (
								contrato_id,fecha,inflacion,porcentaje_inflacion,status) VALUES (
								".$contrato_id.", '".$nueva_fecha."', 'SI', ".$indice_inflacion.",1) ";
							$query = $mysqli->query($query_inc);
						}
					}
				}
			}
			if ($li['tipo_anio_mes'] == 2) { // MESES
				$nueva_fecha = $li['fecha'];
				$id_control = VerificarControlCambio($contrato_id,$nueva_fecha);
				if ($id_control == 0) {
					//REGISTRO DEL CONTROL EN CASO QUE NO EXISTA
					$query_inc = "INSERT INTO cont_control_cambio_cuota_moneda (
						contrato_id,fecha,inflacion,porcentaje_inflacion,status) VALUES (
						".$contrato_id.", '".$nueva_fecha."', 'SI', ".$indice_inflacion.",1) ";
					$query = $mysqli->query($query_inc);
				}
				for ($i=0; $i < intval($meses_contrato / $li['numero']) ; $i++) { 
					$nueva_fecha = date("Y-m-d",strtotime($nueva_fecha."+ ".$li['numero']." month")); 
					if ($row_cond_eco['fecha_fin'] > $nueva_fecha) {
						$id_control = VerificarControlCambio($contrato_id,$nueva_fecha);
						if ($id_control == 0) {
							//REGISTRO DEL CONTROL EN CASO QUE NO EXISTA
							$query_inc = "INSERT INTO cont_control_cambio_cuota_moneda (
								contrato_id,fecha,inflacion,porcentaje_inflacion,status) VALUES (
								".$contrato_id.", '".$nueva_fecha."', 'SI', ".$indice_inflacion.",1) ";
							$query = $mysqli->query($query_inc);
						}
						
					}
				}
			}
		}
		if($li['tipo_periodicidad_id'] == 2){ //AL INICIO DE CADA AÑO
			for ($i=0; $i < $anios_contrato ; $i++) { 
				$nueva_fecha = date("Y",strtotime($li['fecha']."+ ".$i." year"));
				$nueva_fecha = $nueva_fecha."-01-01";
				if ($row_cond_eco['fecha_fin'] > $nueva_fecha) {
					$id_control = VerificarControlCambio($contrato_id,$nueva_fecha);
					if ($id_control == 0) {
						//REGISTRO DEL CONTROL EN CASO QUE NO EXISTA
						$query_inc = "INSERT INTO cont_control_cambio_cuota_moneda (
							contrato_id,fecha,inflacion,porcentaje_inflacion,status) VALUES (
							".$contrato_id.", '".$nueva_fecha."','SI', ".$indice_inflacion.",1) ";
						$query = $mysqli->query($query_inc);
					}
				}
			}
		}
	}
	


	// INCREMENTOS
	$query_incr = "SELECT i.id,i.valor,i.tipo_valor_id,i.tipo_continuidad_id,i.a_partir_del_año,i.fecha_cambio
	FROM cont_incrementos i
	WHERE 1 = 1 AND i.tipo_valor_id = 2 AND i.fecha_cambio  IS NOT NULL AND (i.tipo_continuidad_id = 3 OR i.tipo_continuidad_id = 2) AND i.contrato_id = ".$contrato_id." AND i.estado = 1 ORDER BY i.fecha_cambio ASC";
	$data_incr = $mysqli->query($query_incr);
	while($li=$data_incr->fetch_assoc()){
		
		if ($li['tipo_continuidad_id'] == 3) { // INCREMENTO ANUAL
			for ($i=0; $i < $anios_contrato ; $i++) { 
				$nueva_fecha = date("Y-m-d",strtotime($li['fecha_cambio']."+ ".$i." years")); 
				if ($row_cond_eco['fecha_fin'] > $nueva_fecha) {
					$id_control = VerificarControlCambio($contrato_id,$nueva_fecha);
					if ($id_control > 0) {
						//ACTUALIZACION DE LA INFORMACION DE INCREMENTO
						$query_inc = "UPDATE cont_control_cambio_cuota_moneda SET 
									incremento = 'SI',
									porcentaje_incremento = ".$li['valor']."
								WHERE id = ".$id_control;
						$query = $mysqli->query($query_inc);
					}
				}
			}
		}else if ($li['tipo_continuidad_id'] == 2) { // ANUAL A PARTIR DEL X AÑOS
			$nueva_fecha = $li['fecha_cambio']; 
			if ($row_cond_eco['fecha_fin'] > $nueva_fecha) {
				$id_control = VerificarControlCambio($contrato_id,$nueva_fecha);
				if ($id_control > 0) {
					//ACTUALIZACION DE LA INFORMACION DE INCREMENTO
					$query_inc = "UPDATE cont_control_cambio_cuota_moneda SET 
								incremento = 'SI',
								porcentaje_incremento = ".$li['valor']."
							WHERE id = ".$id_control;
					$query = $mysqli->query($query_inc);
				}
			}
		}
	}

	
	// INFLACION
	$query_inf = "SELECT i.id, i.fecha, i.tipo_periodicidad_id, i.numero, i.tipo_anio_mes, i.moneda_id, i.porcentaje_anadido, i.tope_inflacion, i.minimo_inflacion 
	FROM cont_inflaciones AS i
	WHERE i.status = 1 AND i.contrato_id = ".$contrato_id." ORDER BY i.fecha ASC";
	$data_inf = $mysqli->query($query_inf);
	$indice_inflacion = 3; // INDICE DE INFLACION
	while($li=$data_inf->fetch_assoc()){
		if ($li['porcentaje_anadido'] > 0) {
			$indice_inflacion = $indice_inflacion + $li['porcentaje_anadido']; 
		}
		if($li['tipo_periodicidad_id'] == 1){ //CADA
			if ($li['tipo_anio_mes'] == 1) { // AÑO
				for ($i=0; $i < $anios_contrato ; $i++) { 
					$nueva_fecha = date("Y-m-d",strtotime($li['fecha']."+ ".$i." year")); 
					if ($row_cond_eco['fecha_fin'] > $nueva_fecha) {
						$id_control = VerificarControlCambio($contrato_id,$nueva_fecha);
						if ($id_control > 0) {
							//ACTUALIZACION DE LA INFORMACION DE INFLACION
							$query_inc = "UPDATE cont_control_cambio_cuota_moneda SET 
										inflacion = 'SI',
										porcentaje_inflacion = ".$indice_inflacion."
									WHERE id = ".$id_control;
							$query = $mysqli->query($query_inc);
						}
					}
				}
			}
			if ($li['tipo_anio_mes'] == 2) { // MESES
			
				$nueva_fecha = $li['fecha'];
				$id_control = VerificarControlCambio($contrato_id,$nueva_fecha);
				if ($id_control > 0) {
					//REGISTRO DEL CONTROL EN CASO QUE NO EXISTA
					$query_inc = "UPDATE cont_control_cambio_cuota_moneda SET 
								inflacion = 'SI',
								porcentaje_inflacion = ".$indice_inflacion."
							WHERE id = ".$id_control;
					$query = $mysqli->query($query_inc);
				}
				for ($i=0; $i < intval($meses_contrato / $li['numero']) ; $i++) { 
					$nueva_fecha = date("Y-m-d",strtotime($nueva_fecha."+ ".$li['numero']." month")); 
					if ($row_cond_eco['fecha_fin'] > $nueva_fecha) {
						$id_control = VerificarControlCambio($contrato_id,$nueva_fecha);
						if ($id_control > 0) {
							//ACTUALIZACION DE LA INFORMACION DE INFLACION
							$query_inc = "UPDATE cont_control_cambio_cuota_moneda SET 
										inflacion = 'SI',
										porcentaje_inflacion = ".$indice_inflacion."
									WHERE id = ".$id_control;
							$query = $mysqli->query($query_inc);
						}
						
					}
				}
			}
		}
		if($li['tipo_periodicidad_id'] == 2){ //AL INICIO DE CADA AÑO
			for ($i=0; $i < $anios_contrato ; $i++) { 
				$nueva_fecha = date("Y",strtotime($li['fecha']."+ ".$i." year"));
				$nueva_fecha = $nueva_fecha."-01-01";
				if ($row_cond_eco['fecha_fin'] > $nueva_fecha) {
					$id_control = VerificarControlCambio($contrato_id,$nueva_fecha);
					if ($id_control > 0) {
						//ACTUALIZACION DE LA INFORMACION DE INFLACION
						$query_inc = "UPDATE cont_control_cambio_cuota_moneda SET 
									inflacion = 'SI',
									porcentaje_inflacion = ".$indice_inflacion."
								WHERE id = ".$id_control;
						$query = $mysqli->query($query_inc);
					}
				}
			}
		}
	}

	$monto_base_inicial = $monto_renta;
	$moneda_base_inicial = $tipo_moneda_id;
	///ACTUALIZACION DEL PRIMERO MONTO DE CAMBIO DE CUOTA O MONEDA
	$query_control = "SELECT c.* FROM cont_control_cambio_cuota_moneda AS c 
	WHERE c.status = 1 AND c.contrato_id = ".$contrato_id."
	ORDER BY c.fecha ASC LIMIT 1";
	$data_control = $mysqli->query($query_control);
	while($li=$data_control->fetch_assoc()){
		
		//OBTENIENDO PRIMERA ADENDA DE CAMBIO DE CUOTA 
		$query_monto_inicial = "SELECT ad.* FROM cont_adendas_detalle AS ad
		INNER JOIN cont_adendas AS a ON a.id = ad.adenda_id
		WHERE a.procesado = 1 AND a.status = 1 AND a.fecha_de_ejecucion_del_cambio  IS NOT NULL AND ad.nombre_tabla = 'cont_condicion_economica' AND ad.nombre_campo = 'monto_renta' AND a.contrato_id = ".$contrato_id."
		ORDER BY a.fecha_de_ejecucion_del_cambio ASC LIMIT 1";
		$data_monto_inicial = $mysqli->query($query_monto_inicial);
		while($la=$data_monto_inicial->fetch_assoc()){
			$monto_base_inicial = str_replace(",","",$la['valor_original']);
			$monto_base_inicial = floatval(substr($monto_base_inicial,4));
		}

		//OBTENIENDO PRIMERA ADENDA DE CAMBIO DE CUOTA 
		$query_moneda_incial = "SELECT ad.* FROM cont_adendas_detalle AS ad
		INNER JOIN cont_adendas AS a ON a.id = ad.adenda_id
		WHERE a.procesado = 1 AND a.status = 1 AND a.fecha_de_ejecucion_del_cambio  IS NOT NULL AND ad.nombre_tabla = 'cont_condicion_economica' AND ad.nombre_campo = 'tipo_moneda_id' AND a.contrato_id = ".$contrato_id."
		ORDER BY a.fecha_de_ejecucion_del_cambio ASC LIMIT 1";
		$data_moneda_inicial = $mysqli->query($query_moneda_incial);
		while($la=$data_moneda_inicial->fetch_assoc()){
			$moneda_base_inicial = $la['valor_int'];
		}

		if ($afectacion_igv_id == 1) {
			$monto_base_inicial = $monto_base_inicial / 1.18;
		}else if($afectacion_igv_id == 2){
			$monto_base_inicial = $monto_base_inicial;
		}
		
		$query_cuota = "UPDATE cont_control_cambio_cuota_moneda SET 
					cambio_cuota = 'NO',
					monto_base = ".$monto_base_inicial."
				WHERE id = ".$li['id'];
		$query = $mysqli->query($query_cuota);


		$query_cuota = "UPDATE cont_control_cambio_cuota_moneda SET 
					cambio_moneda = 'NO',
					moneda_id = ".$moneda_base_inicial."
				WHERE contrato_id = ".$contrato_id;
		$query = $mysqli->query($query_cuota);
	}






	// ADENDAS DE CAMBIO DE CUOTA O MONEDA
	$query_adenda = "SELECT ad.* , a.fecha_de_ejecucion_del_cambio FROM cont_adendas_detalle AS ad
	INNER JOIN cont_adendas AS a ON a.id = ad.adenda_id
	WHERE a.procesado = 1 AND a.status = 1 AND a.fecha_de_ejecucion_del_cambio  IS NOT NULL AND ad.nombre_tabla = 'cont_condicion_economica' AND (ad.nombre_campo = 'monto_renta' OR ad.nombre_campo = 'tipo_moneda_id') AND a.contrato_id = ".$contrato_id."
	ORDER BY a.fecha_de_ejecucion_del_cambio ASC";
	$data_adenda = $mysqli->query($query_adenda);
	while($li=$data_adenda->fetch_assoc()){
		$id_control = VerificarControlCambio($contrato_id,$li['fecha_de_ejecucion_del_cambio']);
		if ($id_control == 0) {
			//REGISTRO DEL CONTROL EN CASO QUE NO EXISTA
			if ($li['nombre_campo'] == "monto_renta") {
				$query_renta = "INSERT INTO cont_control_cambio_cuota_moneda (
					contrato_id,fecha,cambio_cuota,monto_base,status) VALUES (
					".$contrato_id.", '".$nueva_fecha."', 'SI', ".$li['valor_decimal'].",1) ";
				$query = $mysqli->query($query_renta);
			}else if($li['nombre_campo'] == "tipo_moneda_id"){
				$query_renta = "INSERT INTO cont_control_cambio_cuota_moneda (
					contrato_id,fecha,cambio_moneda,moneda_id,status) VALUES (
					".$contrato_id.", '".$nueva_fecha."', 'SI', ".$li['valor_int'].",1) ";
				$query = $mysqli->query($query_renta);
				
				// ACTUALIZAR LAS MONEDAS
				$query_moneda = "UPDATE cont_control_cambio_cuota_moneda SET 
							cambio_moneda = 'NO',
							moneda_id = ".$moneda."
						WHERE contrato_id = ".$contrato_id." AND fecha > '".$nueva_fecha."'";
				$query = $mysqli->query($query_moneda);
			}
			
		}else if ($id_control > 0){
			//ACTUALIZACION DE LA INFORMACION DE CAMBIO DE CUOTA
			if ($li['nombre_campo'] == "monto_renta") {
				$query_cuota = "UPDATE cont_control_cambio_cuota_moneda SET 
							cambio_cuota = 'SI',
							monto_base = ".$li['valor_decimal']."
						WHERE id = ".$id_control;
				$query = $mysqli->query($query_cuota);
			}else if($li['nombre_campo'] == "tipo_moneda_id"){
				$query_moneda = "UPDATE cont_control_cambio_cuota_moneda SET 
							cambio_moneda = 'SI',
							moneda_id = ".$li['valor_int']."
						WHERE id = ".$id_control;
				$query = $mysqli->query($query_moneda);
				
				// ACTUALIZAR LAS MONEDAS
				$query_moneda = "UPDATE cont_control_cambio_cuota_moneda SET 
							cambio_moneda = 'NO',
							moneda_id = ".$moneda."
						WHERE contrato_id = ".$contrato_id." AND fecha > '".$nueva_fecha."'";
				$query = $mysqli->query($query_moneda);
			}
			
		}
		
	}


	// CALCULAR CUADRO DE CAMBIO DE CUOTA O MONEDA
	$query_control = "SELECT c.* FROM cont_control_cambio_cuota_moneda AS c 
	WHERE c.status = 1 AND c.contrato_id = ".$contrato_id."
	ORDER BY c.fecha ASC";
	$data_control = $mysqli->query($query_control);

	// CALCULAR CUADRO DE CAMBIO DE CUOTA O MONEDA
	$html = '
	<table class="table">
		<thead>
		<tr>
			<th>#</th>
			<th>Fecha</th>
			<th>Moneda</th>
			<th>Monto Base</th>
			<th>Incremento</th>
			<th>Porcentaje</th>
			<th>Monto de Incremento</th>
			<th>Monto R + I</th>
			<th>Inflacion</th>
			<th>Porcentaje</th>
			<th>Monto Inflacion</th>
			<th>Monto R + I + I</th>
			<th>Impuuesto Renta</th>
			<th>Porcentaje</th>
			<th>Monto R + I + I + IR</th>
			<th>IGV</th>
			<th>Porcentaje</th>
			<th>Monto IGV</th>
			<th>Monto Final</th>
		</tr>
		</thead>
		<tbody>';
		$index = 1;
		$monto_base = 0;
		while($li=$data_control->fetch_assoc()){
			
			if ($index == 1) {
				$monto_base = $li['monto_base'];
				$cambio_cuota = "NO";
			}else{ 
				$cambio_cuota = "NO";
				if ($li['cambio_cuota'] == "SI"){
					$cambio_cuota = "SI";
					$monto_base = $li['monto_base'];
				}
			}
			//INCREMENTOS
			$monto_incremento = 0;
			$incremento = "NO";
			if ($li['incremento'] == 'SI') {
				$incremento = "SI";
				$monto_incremento = $monto_base * ($li['porcentaje_incremento']/100);
			} 
			$monto_renta_inc = $monto_base + $monto_incremento;
			//INFLACIONES
			$monto_inflacion = 0;
			$inflacion = "NO";
			if ($li['inflacion'] == 'SI') {
				$inflacion = "SI";
				$monto_inflacion = $monto_renta_inc * ($li['porcentaje_inflacion']/100);
			} 
			$monto_renta_inc_inf = $monto_renta_inc + $monto_inflacion;
			//IMPUESTO A LA RENTA
			$monto_impuesto_renta = 0;
			$impuesto_renta = "NO";
			if ($impuesto_a_la_renta_id == 2) {
				$impuesto_renta = "SI";
				$monto_impuesto_renta = $monto_renta_inc_inf * (5/100);
			} 
			$monto_renta_inc_inf_ir = $monto_renta_inc_inf + $monto_impuesto_renta;

			// AFECTACION IGV
			$monto_igv = 0;
			$afectacion_igv = "NO";
			if ($afectacion_igv_id == 1 || $afectacion_igv_id == 2) {
				$afectacion_igv = "SI";
				$monto_igv = $monto_renta_inc_inf_ir * 0.18;
			}
			$monto_renta_inc_inf_ir_igv = $monto_renta_inc_inf_ir + $monto_igv;
			
			
			$query_update = "
			";
	$html .= '
		<tr>
			<td>'.$index.'</td>
			<td>'.$li['fecha'].'</td>
			<td>'.$li['moneda_id'].'</td>
			<td>'.number_format($monto_base,2,'.','').'</td>
			<td>'.$incremento.'</td>
			<td>'.$li['porcentaje_incremento'].'</td>
			<td>'.number_format($monto_incremento).'</td>
			<td>'.number_format($monto_renta_inc,2,'.','').'</td>
			<td>'.$inflacion.'</td>
			<td>'.$li['porcentaje_inflacion'].'</td>
			<td>'.number_format($monto_inflacion,2,'.','').'</td>
			<td>'.number_format($monto_renta_inc_inf,2,'.','').'</td>
			<td>'.$impuesto_renta.'</td>
			<td>5</td>
		
			<td>'.number_format($monto_renta_inc_inf_ir,2,'.','').'</td>
			<td>'.$afectacion_igv.'</td>
			<td>18</td>
			<td>'.number_format($monto_igv,2,'.','').'</td>
			<td>'.number_format($monto_renta_inc_inf_ir_igv,2,'.','').'</td>
		</tr>
	';

			$monto_base = $monto_renta_inc_inf_ir;
			$index++;


			
			$query_update_control = "UPDATE cont_control_cambio_cuota_moneda SET 
							cambio_cuota = '".$cambio_cuota."',
							monto_base = ".$monto_base.",
							incremento = '".$incremento."',
							porcentaje_incremento = ".$li['porcentaje_incremento'].",
							monto_incremento = ".$monto_incremento.",
							monto_renta_incremento = ".$monto_renta_inc.",
							inflacion = '".$inflacion."',
							porcentaje_inflacion = ".$li['porcentaje_inflacion'].",
							monto_inflacion = ".$monto_inflacion.",
							monto_renta_incremento_inflacion = ".$monto_renta_inc_inf.",
							impuesto_renta = '".$impuesto_renta."',
							porcentaje_impuesto_renta = 5,
							monto_impuesto_renta = ".$monto_impuesto_renta.",
							monto_renta_inc_inf_ir = ".$monto_renta_inc_inf_ir.",
							igv = '".$afectacion_igv."',
							porcentaje_igv = 18,
							monto_procentaje_igv = ".$monto_igv.",
							monto_renta_inc_inf_ir_igv = ".$monto_renta_inc_inf_ir_igv.",
							monto_final = ".$monto_renta_inc_inf_ir_igv."
						WHERE id = ".$li['id'];
				$query = $mysqli->query($query_update_control);
				
	}
	$html .= '
		</tbody>
	</table>';
	
	

	return $html;
	
}


function VerificarControlCambio($contrato_id,$fecha){
	include("db_connect.php");
	include("sys_login.php");
	$query_sel = "SELECT c.* FROM cont_control_cambio_cuota_moneda AS c WHERE c.status = 1 AND c.contrato_id = ".$contrato_id." AND c.fecha = '".$fecha."' LIMIT 1";
	$query = $mysqli->query($query_sel);
	$row_count = $query->num_rows;
	if ($row_count > 0) {
		$row = $query->fetch_assoc();
		return $row['id'];
	}
	return 0;
}

?>
