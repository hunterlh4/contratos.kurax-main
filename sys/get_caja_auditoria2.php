<?php

$menu_id = "";
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'caja' AND sub_sec_id = 'auditoria2' LIMIT 1");
while($r = $result->fetch_assoc()) $menu_id = $r["id"];

?>

<?php if(isset($_POST["sec_caja_auditoria2"]) && $_POST["sec_caja_auditoria2"]["local_id"] != "_all_"){
	if(!array_key_exists($menu_id,$usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])){
		echo "No tienes permisos para acceder a este recurso";
		die;
	}

	$data_final = [];

	function sub_dinero_sistema($array1, $array2, $valor){
		global $array_merge, $fecha_inicio, $fecha_fin_bingo;
		$rango_fechas = [];
		$current_date = new DateTime($fecha_inicio);
		$max_date = new DateTime($fecha_fin_bingo);
		while ($current_date <= $max_date) {
			$rango_fechas[] = $current_date->format('Y-m-d');
			$current_date->modify('+1 day');
		}
		foreach ($rango_fechas as $fecha) {
			$encontrado = false;
			foreach ($array1 as $item) {
				if ($item['fecha_operacion'] === $fecha) {
					$encontrado = true;
					break;
				}
			}
			if (!$encontrado) {
				$array1[] = array('fecha_operacion' => $fecha, $valor => "0.00");
			}
		}
		usort($array1, function($a, $b) {
			return strtotime($a['fecha_operacion']) - strtotime($b['fecha_operacion']);
		});
		if(empty($arr2)){
			$arr2[0]['fecha_operacion'] = '1998-06-12';
			$arr2[0][$valor] = 0;
		}
		$result = array();
		foreach ($array1 as $item1) {
			$fecha1 = $item1['fecha_operacion'];
			$sub_dinero_sistema1 = floatval($item1[$valor]);
			$found = false;
			foreach ($array2 as $item2) {
				if ($item2['fecha_operacion'] == $fecha1) {
					$sub_dinero_sistema2 = floatval($item2[$valor]);
					$found = true;
					break;
				}
			}
			if ($found) {
				if($valor === "sistema_web"){
					$diferencia = sprintf("%.2f", ($sub_dinero_sistema1 + $sub_dinero_sistema2));
				}else{
					$diferencia = sprintf("%.2f", ($sub_dinero_sistema1 - $sub_dinero_sistema2));
				}
			} else {
				$diferencia = sprintf("%.2f", $sub_dinero_sistema1);
			}
			$result[] = array(
				'fecha_operacion' => $fecha1,
				$valor => $diferencia
			);
		}
		$datos_con_claves_personalizadas = [];
		foreach ($result as $dato) {
			$valor1 = $dato;
			$nuevo_dato = [$valor => $valor1];
			$datos_con_claves_personalizadas[] = $nuevo_dato;
		}
		return $datos_con_claves_personalizadas;
	}

	

	function sistema_bingo($array1, $array2){
		global $array_merge, $fecha_inicio, $fecha_fin_bingo;


		$rango_fechas = [];
		$current_date = new DateTime($fecha_inicio);
		$max_date = new DateTime($fecha_fin_bingo);
		while ($current_date <= $max_date) {
			$rango_fechas[] = $current_date->format('Y-m-d');
			$current_date->modify('+1 day');
		}
		// Completar el array1 con fechas faltantes
		foreach ($rango_fechas as $fecha) {
			$encontrado = false;
			foreach ($array1 as $item) {
				if ($item['fecha_operacion'] === $fecha) {
					$encontrado = true;
					break;
				}
			}
			if (!$encontrado) {
				$array1[] = array('fecha_operacion' => $fecha, 'sistema_bingo' => "0.00");
			}
		}
		// Ordenar el array1 por fecha
		usort($array1, function($a, $b) {
			return strtotime($a['fecha_operacion']) - strtotime($b['fecha_operacion']);
		});
		// Mostrar el array1 completo con las fechas faltantes agregadas
		// print_r($array1);


		if(empty($arr2)){
			$arr2[0]['fecha_operacion'] = '1998-06-12';
			$arr2[0]['sistema_bingo'] = 0;
		}
		// $contador = 0;
		$result = array();
		foreach ($array1 as $item1) {
			$fecha1 = $item1['fecha_operacion'];
			$sistema_bingo1 = floatval($item1['sistema_bingo']);
			$found = false;
			foreach ($array2 as $item2) {
				if ($item2['fecha_operacion'] == $fecha1) {
					$sistema_bingo2 = floatval($item2['sistema_bingo']);
					$found = true;
					break;
				}
			}
			if ($found) {
				$diferencia = sprintf("%.2f", ($sistema_bingo1 - $sistema_bingo2));;
			} else {
				$diferencia = sprintf("%.2f", $sistema_bingo1);
			}
			$result[] = array(
				'fecha_operacion' => $fecha1,
				'sistema_bingo' => $diferencia
			);
		}

		$datos_con_claves_personalizadas = [];
		foreach ($result as $dato) {
			$valor = $dato;
			$nuevo_dato = ["sistema_bingo" => $valor];
			$datos_con_claves_personalizadas[] = $nuevo_dato;
		}
		return $datos_con_claves_personalizadas;
	}

	// FUNCION PARA COMPLETAR FECHAS CON 0 CUANDO NO SALEN EN LA CONSULTA
	function completar_fechas_array($array, $fecha_inicio, $fecha_fin, $valor) {
		$indexed_array = [];
		foreach ($array as $element) {
			$indexed_array[$element['fecha_operacion']] = $element[$valor];
		}

		// Creamos un array para almacenar las fechas completas
		$fechas_completas = [];
		$fecha_actual = new DateTime($fecha_inicio);
		$fecha_fin = new DateTime($fecha_fin);
		// $fecha_fin->modify('+1 day'); // Asegura que la fecha fin también se incluya

		// Creamos un intervalo de fechas
		$intervalo = new DateInterval('P1D');
		$periodo = new DatePeriod($fecha_actual, $intervalo, $fecha_fin);

		// Iteramos sobre el intervalo y completamos las fechas
		foreach ($periodo as $fecha) {
			$fecha_str = $fecha->format('Y-m-d');
			$fechas_completas[] = [
				'fecha_operacion' => $fecha_str,
				$valor => $indexed_array[$fecha_str] ?? '0.00'
			];
		}

		return $fechas_completas;
	}

	// LOCAL NOMBRE
    function completarLocalNombre($local_id){
		$array_fecha_operacion = [];
        $query_local_nombre = "SELECT nombre as local_nombre from tbl_locales where id = {$local_id}";
        $result_local_nombre = extractDataCastQuery($query_local_nombre);
		while($row = $result_local_nombre->fetch_assoc()){
			$array_fecha_operacion[] = $row;
		}
		return $array_fecha_operacion;
    }

	// COMPLETAR LOCALES NOMBRE FECHAS
    function completarLocalesNombreFechas($array, $fecha_inicio, $fecha_fin) {
		// Creamos un array para almacenar las fechas completas
		$fechas_completas = [];
		$fecha_actual = new DateTime($fecha_inicio);
		$fecha_fin = new DateTime($fecha_fin);
		// $fecha_fin->modify('+1 day'); // Asegura que la fecha fin también se incluya

		// Creamos un intervalo de fechas
		$intervalo = new DateInterval('P1D');
		$periodo = new DatePeriod($fecha_actual, $intervalo, $fecha_fin);

		// Iteramos sobre el intervalo y completamos las fechas
		foreach ($periodo as $fecha) {
			$fecha_str = $fecha->format('Y-m-d');
			$fechas_completas[] = [
				'fecha_operacion' => $fecha_str,
				'local_nombre' => $array[0]['local_nombre']
			];
		}

		return $fechas_completas;
	}

	// SOLO FECHA
    function completarSoloFechas($array, $fecha_inicio, $fecha_fin) {
		// $indexed_array = [];
		// foreach ($array as $element) {
		// 	$indexed_array[$element['fecha_operacion']] = $element['fecha_operacion'];
		// }

		// Creamos un array para almacenar las fechas completas
		$fechas_completas = [];
		$fecha_actual = new DateTime($fecha_inicio);
		$fecha_fin = new DateTime($fecha_fin);
		// $fecha_fin->modify('+1 day'); // Asegura que la fecha fin también se incluya

		// Creamos un intervalo de fechas
		$intervalo = new DateInterval('P1D');
		$periodo = new DatePeriod($fecha_actual, $intervalo, $fecha_fin);

		// Iteramos sobre el intervalo y completamos las fechas
		foreach ($periodo as $fecha) {
			$fecha_str = $fecha->format('Y-m-d');
			$fechas_completas[] = [
				'fecha_operacion' => $fecha_str
			];
		}

		return $fechas_completas;
	}

	// FUNCION PARA EJECUTAR LAS QUERYS PARA SACAR LA DATA CAST
	function extractDataCastQuery($query){
		global $mysqli;
		$result = $mysqli->query($query);
		if($mysqli->error){
			print_r($mysqli->error);
			exit();
		}
		return $result;
	}

	$get_data = $_POST["sec_caja_auditoria2"];

	if($get_data["local_id"] == ""){
		echo "Debe seleccionar un local como mínimo";
		die;
	}

	$count_locales = count($get_data["local_id"]);
	if($count_locales >= 1){
		foreach($get_data["local_id"] as $local_id){
			$array1 = array();
			$array2 = array();
			$array_merge = array();

			$fecha_inicio = $get_data["fecha_inicio"];
			$fecha_inicio_pretty = date("d-m-Y",strtotime($get_data["fecha_inicio"]));

			$fecha_fin = date("Y-m-d",strtotime($get_data["fecha_fin"]." +1 day"));
			$fecha_fin_bingo = date("Y-m-d",strtotime($get_data["fecha_fin"]));
			$fecha_fin_pretty = date("d-m-Y",strtotime($get_data["fecha_fin"]));
			$caja_arr = [];

			$caja_where = "";
			$busqueda_bingo_cc_id = 'in';
			if(!empty($login["usuario_locales"])) $caja_where .= " AND u.id = {$login['id']}";
			if($local_id!="_all_") $caja_where.=" AND l.id = '".$local_id."'";

			$array_cc_ids = array();
			if($local_id!="_all_"){
				$query_cc_id = "SELECT cc_id FROM tbl_locales WHERE id = {$local_id}";
				$result_cc_id = $mysqli->query($query_cc_id);
				if($mysqli->error){
					print_r($mysqli->error);
					exit();
				}
				$row_cc_id = mysqli_fetch_assoc($result_cc_id);
				$cc_id = "'" . $row_cc_id["cc_id"] . "'";
			}else{
				foreach($login['usuario_locales'] as $locales_id){
					$query_cc_ids = "SELECT cc_id FROM tbl_locales WHERE id = '$locales_id'";
					$result_cc_ids = $mysqli->query($query_cc_ids);
					while($row_cc_ids = $result_cc_ids->fetch_assoc()){
						$array_cc_ids[] = $row_cc_ids;
					}
				}
				$datos = [];
				foreach ($array_cc_ids as $valor) {
					if($valor['cc_id'] != null){ // algunos cc_ids son nulos
						$datos[] = $valor['cc_id'];
					}
				}
				$cc_id = "(".implode(",", $datos).")";
			}
			// print_r($array_cc_ids);

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

			// SISTEMA BINGO PRMERO
			$query_sistema_bingo1 = "SELECT
				date(created) AS fecha_operacion,
				IFNULL(SUM(amount), 0) as sistema_bingo
			FROM tbl_repositorio_bingo_tickets 
			WHERE
				sell_local_id ".$busqueda_bingo_cc_id." $cc_id
				AND created >= '$fecha_inicio'
				AND created < '$fecha_fin'
				AND ticket_id NOT LIKE 'pm_%'
			group by date(created)
			order by created";
			$result_sistema_bingo1 = extractDataCastQuery($query_sistema_bingo1);
			while($row1 = $result_sistema_bingo1->fetch_assoc()){
				$array1[] = $row1;
			}
			$final_sistema_bingo1 = completar_fechas_array($array1, $fecha_inicio, $fecha_fin, 'sistema_bingo');

			// SISTEMA BINGO SEGUNDO
			$query_sistema_bingo2 = "SELECT
				date(paid_at) as fecha_operacion,
				IFNULL(SUM(
					CASE WHEN status IN ('Refunded')
						THEN amount
						ELSE winning
					END
				), 0) as sistema_bingo
			FROM tbl_repositorio_bingo_tickets
			WHERE
				paid_local_id ".$busqueda_bingo_cc_id." $cc_id
				AND paid_at >= '$fecha_inicio'
				AND paid_at < '$fecha_fin'
				AND status IN ('Paid', 'Refunded')
				AND ticket_id NOT LIKE 'pm_%'
			group by date(paid_at)
			order by paid_at";
			$result_sistema_bingo2 = extractDataCastQuery($query_sistema_bingo2);
			while($row2 = $result_sistema_bingo2->fetch_assoc()){
				$array2[] = $row2;
			}
			$final_sistema_bingo2 = completar_fechas_array($array2, $fecha_inicio, $fecha_fin, 'sistema_bingo');

			$sistema_bingo_resultado = sistema_bingo($final_sistema_bingo1, $final_sistema_bingo2);

			// FECHA OPERACION 1
			$array_fecha_operacion = array();
			$query_fecha_operacion = "SELECT
			c.fecha_operacion
			FROM tbl_caja c
			LEFT JOIN tbl_local_cajas lc ON (lc.id = c.local_caja_id)
			LEFT JOIN tbl_locales l ON (l.id = lc.local_id)
			INNER JOIN tbl_usuarios_locales ul ON (ul.local_id = l.id AND ul.estado = 1)
			INNER JOIN tbl_usuarios u ON (u.id = ul.usuario_id)
			WHERE
				(l.red_id IN (1,4,6,7,8,9,16) OR l.id = 200)
				AND l.operativo = 1
				AND l.estado = 1
				AND c.fecha_operacion >= '$fecha_inicio'
				AND c.fecha_operacion < '$fecha_fin'
				{$caja_where}
			GROUP BY c.fecha_operacion, l.id
			ORDER BY c.fecha_operacion ASC, l.nombre ASC";
			$result_fecha_operacion = extractDataCastQuery($query_fecha_operacion);
			while($row = $result_fecha_operacion->fetch_assoc()){
				$array_fecha_operacion[] = $row;
			}
			$final_array_fecha_operacion = completarSoloFechas($array_fecha_operacion, $fecha_inicio, $fecha_fin);

			// LOCAL NOMBRE 3
			$query_local_nombre = "SELECT
			c.fecha_operacion,
			l.nombre AS local_nombre
			FROM tbl_caja c
			LEFT JOIN tbl_local_cajas lc ON (lc.id = c.local_caja_id)
			LEFT JOIN tbl_locales l ON (l.id = lc.local_id)
			INNER JOIN tbl_usuarios_locales ul ON (ul.local_id = l.id AND ul.estado = 1)
			INNER JOIN tbl_usuarios u ON (u.id = ul.usuario_id)
			WHERE
				(l.red_id IN (1,4,6,7,8,9,16) OR l.id = 200)
				AND l.operativo = 1
				AND l.estado = 1
				AND c.fecha_operacion >= '$fecha_inicio'
				AND c.fecha_operacion < '$fecha_fin'
				{$caja_where}
			GROUP BY c.fecha_operacion, l.id
			ORDER BY c.fecha_operacion ASC, l.nombre ASC";
			$result_local_nombre = extractDataCastQuery($query_local_nombre);

			$final_array_completar_nombre = completarLocalNombre($local_id);
			// print_r($final_array_completar_nombre);
			$final_array_local_nombre = completarLocalesNombreFechas($final_array_completar_nombre, $fecha_inicio, $fecha_fin);
			// print_r($final_array_local_nombre);

			$where_caja_id = "";
			if($local_id!="_all_") $where_caja_id.=" AND dc_lc.local_id = '".$local_id."'";	
			$array_dinero_cajero = array();
			// DINERO CAJERO 4
			$query_dinero_cajero = "SELECT 
				dc_c.fecha_operacion,
				cast(
					sum(
						if((dc_cdf.tipo_id = 5),dc_cdf.valor,0)
					) AS DECIMAL(20,2)
				) as dinero_cajero
			FROM tbl_caja dc_c
			LEFT JOIN tbl_local_cajas dc_lc ON (dc_lc.id = dc_c.local_caja_id)
			LEFT JOIN tbl_caja_datos_fisicos dc_cdf ON (dc_cdf.caja_id = dc_c.id)
			WHERE dc_c.fecha_operacion >= '$fecha_inicio'
			AND dc_c.fecha_operacion < '$fecha_fin' 
			{$where_caja_id} 
			GROUP BY dc_c.fecha_operacion, dc_lc.id
			ORDER BY dc_c.fecha_operacion ASC, dc_lc.nombre ASC";
			$result_dinero_cajero = extractDataCastQuery($query_dinero_cajero);
			while($row = $result_dinero_cajero->fetch_assoc()){
				$array_dinero_cajero[] = $row;
			}
			$final_array_dinero_cajero = completar_fechas_array($array_dinero_cajero, $fecha_inicio, $fecha_fin, 'dinero_cajero');
			// print_r($final_array_dinero_cajero);

			$where_caja_id = "";
			if($local_id!="_all_") $where_caja_id.=" AND cpm_lc.local_id = '".$local_id."'";
			$array_cajero_pagos_manuales = array();
			// CAJERO PAGOS MENSUALES 5
			$query_cajero_pagos_manuales = "SELECT 
					cpm_c.fecha_operacion,
					cast(sum(if((cpm_cdf.tipo_id = 9),cpm_cdf.valor,0)) AS DECIMAL(20,2)) as cajero_pagos_manuales
				FROM tbl_caja cpm_c
				LEFT JOIN tbl_local_cajas cpm_lc ON (cpm_lc.id = cpm_c.local_caja_id)
				LEFT JOIN tbl_caja_datos_fisicos cpm_cdf ON (cpm_cdf.caja_id = cpm_c.id)
				WHERE cpm_c.fecha_operacion >= '$fecha_inicio'
				AND cpm_c.fecha_operacion < '$fecha_fin'
				{$where_caja_id}
			group by cpm_c.fecha_operacion, cpm_lc.local_id";
			$result_cajero_pagos_manuales = extractDataCastQuery($query_cajero_pagos_manuales);
			while($row = $result_cajero_pagos_manuales->fetch_assoc()){
				$array_cajero_pagos_manuales[] = $row;
			}
			$final_array_cajero_pagos_manuales = completar_fechas_array($array_cajero_pagos_manuales, $fecha_inicio, $fecha_fin, 'cajero_pagos_manuales');
			// print_r($final_array_cajero_pagos_manuales);

			// CAJERO DEVOLUCION 6
			$array_cajero_devolucion = array();
			$query_cajero_devolucion = "SELECT
			c.fecha_operacion,
			(
				SELECT cast(sum(IF((cd_cdf.tipo_id = 8), cd_cdf.valor, 0)) AS DECIMAL(20, 2))
				FROM tbl_caja cd_c
				LEFT JOIN tbl_local_cajas cd_lc ON (cd_lc.id = cd_c.local_caja_id)
				LEFT JOIN tbl_caja_datos_fisicos cd_cdf ON (cd_cdf.caja_id = cd_c.id)
				WHERE cd_lc.local_id = l.id
				AND cd_c.fecha_operacion = c.fecha_operacion
			) AS cajero_devolucion
			FROM tbl_caja c
			LEFT JOIN tbl_local_cajas lc ON (lc.id = c.local_caja_id)
			LEFT JOIN tbl_locales l ON (l.id = lc.local_id)
			INNER JOIN tbl_usuarios_locales ul ON (ul.local_id = l.id AND ul.estado = 1)
			INNER JOIN tbl_usuarios u ON (u.id = ul.usuario_id)
			WHERE
				(l.red_id IN (1,4,6,7,8,9,16) OR l.id = 200)
				AND l.operativo = 1
				AND l.estado = 1
				AND c.fecha_operacion >= '$fecha_inicio'
				AND c.fecha_operacion < '$fecha_fin'
				{$caja_where}
			GROUP BY c.fecha_operacion, l.id
			ORDER BY c.fecha_operacion ASC, l.nombre ASC";
			$result_cajero_devolucion = extractDataCastQuery($query_cajero_devolucion);
			while($row = $result_cajero_devolucion->fetch_assoc()){
				$array_cajero_devolucion[] = $row;
			}
			$final_array_cajero_devolucion = completar_fechas_array($array_cajero_devolucion, $fecha_inicio, $fecha_fin, 'cajero_devolucion');

			// CAJERO DEVOLUCION CARRERA CABALLOS 7
			$array_cajero_devolucion_carrera_caballos = array();
			$query_cajero_devolucion_carrera_caballos = "SELECT
			c.fecha_operacion,
			(
				SELECT cast(sum(IF((cd_cdf.tipo_id = 28), cd_cdf.valor, 0)) AS DECIMAL(20, 2) )
				FROM tbl_caja cd_c
				LEFT JOIN tbl_local_cajas cd_lc ON (cd_lc.id = cd_c.local_caja_id)
				LEFT JOIN tbl_caja_datos_fisicos cd_cdf ON (cd_cdf.caja_id = cd_c.id)
				WHERE cd_lc.local_id = l.id
				AND cd_c.fecha_operacion = c.fecha_operacion
			) AS cajero_devolucion_carrera_caballos
			FROM tbl_caja c
			LEFT JOIN tbl_local_cajas lc ON (lc.id = c.local_caja_id)
			LEFT JOIN tbl_locales l ON (l.id = lc.local_id)
			INNER JOIN tbl_usuarios_locales ul ON (ul.local_id = l.id AND ul.estado = 1)
			INNER JOIN tbl_usuarios u ON (u.id = ul.usuario_id)
			WHERE
				(l.red_id IN (1,4,6,7,8,9,16) OR l.id = 200)
				AND l.operativo = 1
				AND l.estado = 1
				AND c.fecha_operacion >= '$fecha_inicio'
				AND c.fecha_operacion < '$fecha_fin'
				{$caja_where}
			GROUP BY c.fecha_operacion, l.id
			ORDER BY c.fecha_operacion ASC, l.nombre ASC";
			$result_cajero_devolucion_carrera_caballos = extractDataCastQuery($query_cajero_devolucion_carrera_caballos);
			while($row = $result_cajero_devolucion_carrera_caballos->fetch_assoc()){
				$array_cajero_devolucion_carrera_caballos[] = $row;
			}
			$final_array_cajero_devolucion_carrera_caballos = completar_fechas_array($array_cajero_devolucion_carrera_caballos, $fecha_inicio, $fecha_fin, 'cajero_devolucion_carrera_caballos');
			// print_r($final_array_cajero_devolucion_carrera_caballos);

			// SUB DINERO SISTEMA

			$where_caja_id = "";
			if($local_id!="_all_") $where_caja_id.=" AND r.local_id = '".$local_id."'";

			
			$array_sub_dinero_sistema1 = array();
			$array_sub_dinero_sistema2_1 = array();
			$array_sub_dinero_sistema2_2 = array();
			$array_sub_dinero_sistema3 = array();
			$array_sub_dinero_sistema4_1 = array();
			$array_sub_dinero_sistema4_2 = array();
			$array_sub_dinero_sistema5 = array();
			$array_sub_dinero_sistema6 = array();
			$array_sub_dinero_sistema7 = array();
			// SUB DINERO SISTEMA 1
			$query_sub_dinero_sistema1 = "SELECT 
			date(r.created) as fecha_operacion,
			CAST((
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
				0) 
			) AS DECIMAL (20 , 2 )) as sub_dinero_sistema
			from tbl_transacciones_repositorio r 
			where 
			r.created >= '$fecha_inicio'
			AND r.created < '$fecha_fin'
			{$where_caja_id}
			AND r.tipo in (2,3,4)
			GROUP BY date(r.created), r.local_id";
			$result_sub_dinero_sistema1 = extractDataCastQuery($query_sub_dinero_sistema1);
			while($row1 = $result_sub_dinero_sistema1->fetch_assoc()){
				$array_sub_dinero_sistema1[] = $row1;
			}
			$final_array_sub_dinero_sistema1 = completar_fechas_array($array_sub_dinero_sistema1, $fecha_inicio, $fecha_fin, 'sub_dinero_sistema');

			// SUB DINERO SISTEMA 2
			$query_sub_dinero_sistema2_1 = "SELECT
				date(created) as fecha_operacion, IFNULL(SUM(amount), 0) as sub_dinero_sistema
			FROM tbl_repositorio_bingo_tickets
			WHERE
				sell_local_id ".$busqueda_bingo_cc_id." $cc_id
				AND created >= '$fecha_inicio'
				AND created < '$fecha_fin'
				AND ticket_id NOT LIKE 'pm_%'
			group by date(created), sell_local_id";
			$result_sub_dinero_sistema2_1 = extractDataCastQuery($query_sub_dinero_sistema2_1);
			while($row2_1 = $result_sub_dinero_sistema2_1->fetch_assoc()){
				$array_sub_dinero_sistema2_1[] = $row2_1;
			}
			$final_array_sub_dinero_sistema2_1 = completar_fechas_array($array_sub_dinero_sistema2_1, $fecha_inicio, $fecha_fin, 'sub_dinero_sistema');

			$query_sub_dinero_sistema2_2 = "SELECT
				date(created) as fecha_operacion,
				IFNULL(SUM(
					CASE WHEN status IN ('Refunded')
						THEN amount
						ELSE winning
					END
				), 0) as sub_dinero_sistema
			FROM tbl_repositorio_bingo_tickets
			WHERE
				paid_local_id ".$busqueda_bingo_cc_id." $cc_id
				AND created >= '$fecha_inicio'
				AND created < '$fecha_fin'
				and status IN ('Paid', 'Refunded')
				AND ticket_id NOT LIKE 'pm_%'
			group by date(created), paid_local_id";
			$result_sub_dinero_sistema2_2 = extractDataCastQuery($query_sub_dinero_sistema2_2);
			while($row2_2 = $result_sub_dinero_sistema2_2->fetch_assoc()){
				$array_sub_dinero_sistema2_2[] = $row2_2;
			}
			$final_array_sub_dinero_sistema2_2 = completar_fechas_array($array_sub_dinero_sistema2_2, $fecha_inicio, $fecha_fin, 'sub_dinero_sistema');

			$sub_dinero_sistema_resultado2 = sub_dinero_sistema($final_array_sub_dinero_sistema2_1, $final_array_sub_dinero_sistema2_2, 'sub_dinero_sistema');
			// if(empty($array_sub_dinero_sistema2_2)){
			// 	$array_sub_dinero_sistema2_2[0]['fecha'] = '1998-06-12';
			// 	$array_sub_dinero_sistema2_2[0]['sub_dinero_sistema'] = '0.00';
			// }

			// SUB DINERO SISTEMA 3
			$query_sub_dinero_sistema3 = "SELECT
				c.fecha_operacion,
				(
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
				) AS sub_dinero_sistema
				FROM tbl_caja c
				LEFT JOIN tbl_local_cajas lc ON (lc.id = c.local_caja_id)
				LEFT JOIN tbl_locales l ON (l.id = lc.local_id)
				INNER JOIN tbl_usuarios_locales ul ON (ul.local_id = l.id AND ul.estado = 1)
				INNER JOIN tbl_usuarios u ON (u.id = ul.usuario_id)
				WHERE
					c.fecha_operacion >= '$fecha_inicio'
					AND c.fecha_operacion < '$fecha_fin'
					{$caja_where}
				GROUP BY c.fecha_operacion, l.id
				ORDER BY c.fecha_operacion ASC, l.nombre ASC";
			$result_sub_dinero_sistema3 = extractDataCastQuery($query_sub_dinero_sistema3);
			while($row3 = $result_sub_dinero_sistema3->fetch_assoc()){
				$array_sub_dinero_sistema3[] = $row3;
			}
			$final_array_sub_dinero_sistema3 = completar_fechas_array($array_sub_dinero_sistema3, $fecha_inicio, $fecha_fin, 'sub_dinero_sistema');

			$where_caja_id = "";
			if($local_id!="_all_") $where_caja_id.=" AND local_id = '".$local_id."'";
			// SUB DINERO SISTEMA 4
			$query_sub_dinero_sistema4_1 = "SELECT
				date(fecha_operacion) as fecha_operacion, IFNULL(SUM(monto_operacion), 0) as sub_dinero_sistema
			FROM
				tbl_repositorio_kasnet_ventas
			WHERE
				fecha_operacion >= '$fecha_inicio'
				AND fecha_operacion < '$fecha_fin'
				{$where_caja_id}
				AND descripcion_operacion IN (
					'Depositos' ,
					'Pago de Recaudo',
					'Pago Cuota',
					'Depositos',
					'Pago de Tarj. Cred.',
					'Ext. Retiros'
				)
				AND estado = 'Correcta'
			group by date(fecha_operacion), local_id";
			$result_sub_dinero_sistema4_1 = extractDataCastQuery($query_sub_dinero_sistema4_1);
			while($row4_1 = $result_sub_dinero_sistema4_1->fetch_assoc()){
				$array_sub_dinero_sistema4_1[] = $row4_1;
			}
			$final_array_sub_dinero_sistema4_1 = completar_fechas_array($array_sub_dinero_sistema4_1, $fecha_inicio, $fecha_fin, 'sub_dinero_sistema');

			$query_sub_dinero_sistema4_2 = "SELECT
				date(fecha_operacion) as fecha_operacion, IFNULL(SUM(monto_operacion), 0) as sub_dinero_sistema
			FROM
				tbl_repositorio_kasnet_ventas
			WHERE
				fecha_operacion >= '$fecha_inicio'
				AND fecha_operacion < '$fecha_fin'
				{$where_caja_id}
				AND descripcion_operacion IN (
					'Cobro de Orden de Pago',
					'Retiros' ,
					'Pago Visa',
					'Ext. Pago de Recaudo',
					'Ext. Depositos',
					'Disp. Efectivo',
					'Cobro de Remesas'
				)
				AND estado = 'Correcta'
			group by date(fecha_operacion), local_id";
			$result_sub_dinero_sistema4_2 = extractDataCastQuery($query_sub_dinero_sistema4_2);
			while($row4_2 = $result_sub_dinero_sistema4_2->fetch_assoc()){
				$array_sub_dinero_sistema4_2[] = $row4_2;
			}
			$final_array_sub_dinero_sistema4_2 = completar_fechas_array($array_sub_dinero_sistema4_2, $fecha_inicio, $fecha_fin, 'sub_dinero_sistema');

			$sub_dinero_sistema_resultado4 = sub_dinero_sistema($final_array_sub_dinero_sistema4_1, $final_array_sub_dinero_sistema4_2, 'sub_dinero_sistema');
			
			// SUB DINERO SISTEMA 5
			$query_sub_dinero_sistema5 = "SELECT
			c.fecha_operacion,
			(
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
			) AS sub_dinero_sistema
			FROM tbl_caja c
			LEFT JOIN tbl_local_cajas lc ON (lc.id = c.local_caja_id)
			LEFT JOIN tbl_locales l ON (l.id = lc.local_id)
			INNER JOIN tbl_usuarios_locales ul ON (ul.local_id = l.id AND ul.estado = 1)
			INNER JOIN tbl_usuarios u ON (u.id = ul.usuario_id)
			WHERE
				(l.red_id IN (1,4,6,7,8,9,16) OR l.id = 200)
				AND l.operativo = 1
				AND l.estado = 1
				AND c.fecha_operacion >= '$fecha_inicio'
				AND c.fecha_operacion < '$fecha_fin'
				{$caja_where}
			GROUP BY c.fecha_operacion, l.id
			ORDER BY c.fecha_operacion ASC, l.nombre ASC";
			$result_sub_dinero_sistema5 = extractDataCastQuery($query_sub_dinero_sistema5);
			while($row5 = $result_sub_dinero_sistema5->fetch_assoc()){
				$array_sub_dinero_sistema5[] = $row5;
			}
			$final_array_sub_dinero_sistema5 = completar_fechas_array($array_sub_dinero_sistema5, $fecha_inicio, $fecha_fin, 'sub_dinero_sistema');

			$where_caja_id = "";
			if($local_id!="_all_") $where_caja_id.=" AND rtas.local_id = '".$local_id."'";
			// SUB DINERO SISTEMA 6
			$query_sub_dinero_sistema6 = "SELECT 
				date(rtas.creation_date) as fecha_operacion,
				CAST(
					IFNULL(
						(
							sum(IF(rtas.transaction_type = 4, rtas.amount, 0))
						) -(sum(IF(rtas.transaction_type = 5, rtas.amount, 0))),
						0
					) AS DECIMAL(20,2)
				) AS sub_dinero_sistema
			FROM   tbl_repositorio_tickets_america_simulcast AS rtas
			WHERE  rtas.creation_date >= '$fecha_inicio'
					AND rtas.creation_date < '$fecha_fin'
					{$where_caja_id}
					and rtas.ticket_id is not null
			group by date(rtas.creation_date), rtas.local_id";
			$result_sub_dinero_sistema6 = extractDataCastQuery($query_sub_dinero_sistema6);
			while($row6 = $result_sub_dinero_sistema6->fetch_assoc()){
				$array_sub_dinero_sistema6[] = $row6;
			}
			$final_array_sub_dinero_sistema6 = completar_fechas_array($array_sub_dinero_sistema6, $fecha_inicio, $fecha_fin, 'sub_dinero_sistema');

			$where_caja_id = "";
			if($local_id!="_all_") $where_caja_id.=" AND ssql.id = '".$local_id."'";
			// SUB DINERO SISTEMA 7
			$query_sub_dinero_sistema7 = "SELECT 
			fecha_operacion, SUM(IF(swt.tipo_id=1,swt.monto,0))-SUM(IF(swt.tipo_id=2,swt.monto,0)) as sub_dinero_sistema
			FROM tbl_saldo_web_transaccion swt
			LEFT JOIN tbl_caja sqc ON sqc.id = swt.turno_id
			LEFT JOIN tbl_local_cajas sqlc ON sqlc.id = sqc.local_caja_id
			LEFT JOIN tbl_locales ssql ON ssql.id = sqlc.local_id
			WHERE
				swt.tipo_id IN (1,2) AND 
				swt.status = 1 
				{$where_caja_id} AND  
				sqc.fecha_operacion >= '$fecha_inicio' AND
				sqc.fecha_operacion < '$fecha_fin'
			group by date(sqc.fecha_operacion), ssql.id";
			$result_sub_dinero_sistema7 = extractDataCastQuery($query_sub_dinero_sistema7);
			while($row7 = $result_sub_dinero_sistema7->fetch_assoc()){
				$array_sub_dinero_sistema7[] = $row7;
			}
			$final_array_sub_dinero_sistema7 = completar_fechas_array($array_sub_dinero_sistema7, $fecha_inicio, $fecha_fin, 'sub_dinero_sistema');
			
			// RESULTADO DE TODOS LOS VALORES SUB_DINERO_SISTEMA
			$final_sub_dinero_sistema = [];
			$cantidad_final_array_sub_dinero_sistema1 = count($final_array_sub_dinero_sistema1);
			
			for($i = 0; $i < $cantidad_final_array_sub_dinero_sistema1; $i++){
				$final_sub_dinero_sistema[$i]['fecha_operacion'] = $final_array_sub_dinero_sistema1[$i]['fecha_operacion'];
				$final_sub_dinero_sistema[$i]['sub_dinero_sistema'] = $final_array_sub_dinero_sistema1[$i]['sub_dinero_sistema']
																	+ $sub_dinero_sistema_resultado2[$i]['sub_dinero_sistema']['sub_dinero_sistema']
																	+ $final_array_sub_dinero_sistema3[$i]['sub_dinero_sistema']
																	+ $sub_dinero_sistema_resultado4[$i]['sub_dinero_sistema']['sub_dinero_sistema']
																	+ $final_array_sub_dinero_sistema5[$i]['sub_dinero_sistema']
																	+ $final_array_sub_dinero_sistema6[$i]['sub_dinero_sistema']
																	+ $final_array_sub_dinero_sistema7[$i]['sub_dinero_sistema'];
			}

			// print_r($final_sub_dinero_sistema);

			// NO RECLAMADO 9
			$array_no_reclamado = array();
			$query_no_reclamado = "SELECT 
				c.fecha_operacion,
				CAST(SUM(0) AS DECIMAL (20 , 2 )) AS no_reclamado
			FROM tbl_caja c
			LEFT JOIN tbl_local_cajas lc ON (lc.id = c.local_caja_id)
			LEFT JOIN tbl_locales l ON (l.id = lc.local_id)
			INNER JOIN tbl_usuarios_locales ul ON (ul.local_id = l.id AND ul.estado = 1)
			INNER JOIN tbl_usuarios u ON (u.id = ul.usuario_id)
			WHERE
				(l.red_id IN (1,4,6,7,8,9,16) OR l.id = 200)
				AND l.operativo = 1
				AND l.estado = 1
				AND c.fecha_operacion >= '$fecha_inicio'
				AND c.fecha_operacion < '$fecha_fin'
				{$caja_where}
			GROUP BY c.fecha_operacion, l.id
			ORDER BY c.fecha_operacion ASC, l.nombre ASC";
			$result_no_reclamado = extractDataCastQuery($query_no_reclamado);
			while($row7 = $result_no_reclamado->fetch_assoc()){
				$array_no_reclamado[] = $row7;
			}
			$final_array_no_reclamado = completar_fechas_array($array_no_reclamado, $fecha_inicio, $fecha_fin, 'no_reclamado');

			$array_sub_resultado_voucher1 = array();

			$where_caja_id = "";
			if($local_id!="_all_") $where_caja_id.=" AND tc.local_id = '".$local_id."'";
			// SUB RESULTADO VOUCHER 1
			$query_sub_resultado_voucher1 = "SELECT transac_cabe.fecha as fecha_operacion, sum(transac_cabe.caja_fisico) as sub_resultado_voucher
			from 
				(SELECT
					tc.local_id,
					tc.fecha,
					tc.caja_fisico,
					tc.estado
				FROM
					tbl_transacciones_cabecera tc
				WHERE
					tc.fecha >= '$fecha_inicio'
				AND tc.fecha < '$fecha_fin'
				{$where_caja_id}
				) as transac_cabe
			where 
			transac_cabe.estado = 1
			group by transac_cabe.fecha, transac_cabe.local_id
			order by transac_cabe.fecha asc";
			$result_sub_resultado_voucher1 = extractDataCastQuery($query_sub_resultado_voucher1);
			while($row1 = $result_sub_resultado_voucher1->fetch_assoc()){
				$array_sub_resultado_voucher1[] = $row1;
			}
			$final_array_sub_resultado_voucher1 = completar_fechas_array($array_sub_resultado_voucher1, $fecha_inicio, $fecha_fin, 'sub_resultado_voucher');
			// $final_array_sub_resulado_voucher1 = completar_fechas_array($array_sub_resultado_voucher1, $fecha_inicio, $fecha_fin);

			for($i = 0; $i < $cantidad_final_array_sub_dinero_sistema1; $i++){
				$final_sub_resultado_voucher[$i]['fecha_operacion'] = $final_array_sub_dinero_sistema1[$i]['fecha_operacion'];
				$final_sub_resultado_voucher[$i]['sub_resultado_voucher'] = $final_array_sub_resultado_voucher1[$i]['sub_resultado_voucher']
																			+ $final_array_sub_dinero_sistema3[$i]['sub_dinero_sistema']
																			+ $sub_dinero_sistema_resultado4[$i]['sub_dinero_sistema']['sub_dinero_sistema']
																			+ $final_array_sub_dinero_sistema5[$i]['sub_dinero_sistema'];
				if ($t_date_web_get <= $t_date_web_ito) {
					$final_sub_resultado_voucher[$i]['sub_resultado_voucher'] += $final_array_sub_dinero_sistema7[$i]['sub_dinero_sistema'];
				}
			}

			// print_r($final_sub_resultado_voucher);

			// SISTEMA PAGOS MANUALES 11
			$array_sistema_pagos_manuales = array();
			$query_sistema_pagos_manuales = "SELECT
			c.fecha_operacion,
			IFNULL(CAST((
				SELECT
					(SUM(ABS(IFNULL(pm.monto, 0)))) AS monto
				FROM
					tbl_pago_manual pm
				WHERE
					pm.estado = '1' AND pm.local_id = l.id
					AND pm.fecha_pago = CONCAT(c.fecha_operacion, ' 00:00:00')
			) AS DECIMAL (20 , 2 )), 0) AS sistema_pagos_manuales
			FROM tbl_caja c
			LEFT JOIN tbl_local_cajas lc ON (lc.id = c.local_caja_id)
			LEFT JOIN tbl_locales l ON (l.id = lc.local_id)
			INNER JOIN tbl_usuarios_locales ul ON (ul.local_id = l.id AND ul.estado = 1)
			INNER JOIN tbl_usuarios u ON (u.id = ul.usuario_id)
			WHERE
				(l.red_id IN (1,4,6,7,8,9,16) OR l.id = 200)
				AND l.operativo = 1
				AND l.estado = 1
				AND c.fecha_operacion >= '$fecha_inicio'
				AND c.fecha_operacion < '$fecha_fin'
				{$caja_where}
			GROUP BY c.fecha_operacion, l.id
			ORDER BY c.fecha_operacion ASC, l.nombre ASC";
			$result_sistema_pagos_manuales = extractDataCastQuery($query_sistema_pagos_manuales);
			while($row1 = $result_sistema_pagos_manuales->fetch_assoc()){
				$array_sistema_pagos_manuales[] = $row1;
			}
			$final_array_sistema_pagos_manuales = completar_fechas_array($array_sistema_pagos_manuales, $fecha_inicio, $fecha_fin, 'sistema_pagos_manuales');

			$array_premios_no_reclamados = [];
			$where_caja_id = "";
			if($local_id!="_all_") $where_caja_id.=" AND tr.local_id = '".$local_id."'";
			// PREMIOS NO RECLAMADOS 12
			$query_premios_no_reclamados = "SELECT
				date(tr.created) as fecha_operacion,
				CAST(
					SUM(tr.open_win) AS DECIMAL (20 , 2 )
				) AS premios_no_reclamados
			FROM
				tbl_transacciones_repositorio tr
			WHERE
				tr.created >= '$fecha_inicio'
				and tr.created < '$fecha_fin'
				{$where_caja_id} 
				AND tr.tipo = 4
				AND tr.servicio_id = 3
			group by date(tr.created), tr.local_id
			ORDER BY tr.created ASC";
			$result_premios_no_reclamados = extractDataCastQuery($query_premios_no_reclamados);
			while($row1 = $result_premios_no_reclamados->fetch_assoc()){
				$array_premios_no_reclamados[] = $row1;
			}
			$final_premios_no_reclamados = completar_fechas_array($array_premios_no_reclamados, $fecha_inicio, $fecha_fin, 'premios_no_reclamados');
			// print_r($final_premios_no_reclamados);

			$array_sistema_devolucion = [];	
			// SISTEMA DEVOLUCION 13
			$query_sistema_devolucion = "SELECT
				date(tr.created) as fecha_operacion,
				CAST(SUM(tr.cancelled)AS DECIMAL (20 , 2 )) as sistema_devolucion
			FROM
				tbl_transacciones_repositorio tr
			WHERE
				tr.created >= '$fecha_inicio'
				and tr.created < '$fecha_fin'
				{$where_caja_id}
				AND tr.tipo = 4
				AND tr.servicio_id = 3
			group by date(tr.created), tr.local_id
			order by tr.created";
			$result_sistema_devolucion = extractDataCastQuery($query_sistema_devolucion);
			while($row1 = $result_sistema_devolucion->fetch_assoc()){
				$array_sistema_devolucion[] = $row1;
			}
			$final_sistema_devolucion = completar_fechas_array($array_sistema_devolucion, $fecha_inicio, $fecha_fin, 'sistema_devolucion');

			$array_sistema_devolucion_carrera_caballos = [];
			$where_caja_id = "";
			if($local_id!="_all_") $where_caja_id.=" AND rtas.local_id = '".$local_id."'";	
			// SISTEMA DEVOLUCION CARRERA CABALLOS 14
			$query_sistema_devolucion_carrera_caballos = "SELECT 
			date(rtas.creation_date) as fecha_operacion,
			(
				CAST(IFNULL(	
					(sum(IF(rtas.transaction_type = 10, rtas.amount, 0))), 0	
				)AS DECIMAL(20,2))
			) as sistema_devolucion_carrera_caballos
			FROM   tbl_repositorio_tickets_america_simulcast AS rtas
			WHERE  rtas.creation_date >= '$fecha_inicio'
					AND rtas.creation_date < '$fecha_fin'
					{$where_caja_id}
					and  ticket_id is not null
			group by date(rtas.creation_date), rtas.local_id
			order by rtas.creation_date asc";
			$result_sistema_devolucion_carrera_caballos = extractDataCastQuery($query_sistema_devolucion_carrera_caballos);
			while($row1 = $result_sistema_devolucion_carrera_caballos->fetch_assoc()){
				$array_sistema_devolucion_carrera_caballos[] = $row1;
			}
			$final_sistema_devolucion_carrera_caballos = completar_fechas_array($array_sistema_devolucion_carrera_caballos, $fecha_inicio, $fecha_fin, 'sistema_devolucion_carrera_caballos');

			// SISTEMA APUESTAS DEPORTIVAS 15
			$array_sistema_apuestas_deportivas = array();
			$query_sistema_apuestas_deportivas = "SELECT
			c.fecha_operacion,
			CAST((
				SELECT IFNULL(SUM(cashdesk_produccion),0) FROM tbl_transacciones_cabecera
				WHERE
					fecha = c.fecha_operacion
					AND local_id = l.id
					AND canal_de_venta_id = 16
					AND estado = 1
			) AS DECIMAL(20,2)) AS sistema_apuestas_deportivas
			FROM tbl_caja c
			LEFT JOIN tbl_local_cajas lc ON (lc.id = c.local_caja_id)
			LEFT JOIN tbl_locales l ON (l.id = lc.local_id)
			INNER JOIN tbl_usuarios_locales ul ON (ul.local_id = l.id AND ul.estado = 1)
			INNER JOIN tbl_usuarios u ON (u.id = ul.usuario_id)
			WHERE
				(l.red_id IN (1,4,6,7,8,9,16) OR l.id = 200)
				AND l.operativo = 1
				AND l.estado = 1
				AND c.fecha_operacion >= '$fecha_inicio'
				AND c.fecha_operacion < '$fecha_fin'
				{$caja_where}
			GROUP BY c.fecha_operacion, l.id
			ORDER BY c.fecha_operacion ASC, l.nombre ASC";
			$result_sistema_apuestas_deportivas = extractDataCastQuery($query_sistema_apuestas_deportivas);
			while($row1 = $result_sistema_apuestas_deportivas->fetch_assoc()){
				$array_sistema_apuestas_deportivas[] = $row1;
			}
			$final_array_sistema_apuestas_deportivas = completar_fechas_array($array_sistema_apuestas_deportivas, $fecha_inicio, $fecha_fin, 'sistema_apuestas_deportivas');

			// CAJERO APUESTAS DEPORTIVAS 16
			$array_cajero_apuestas_deportivas = array();
			if($local_id!="_all_"){
				$query_cajero_apuestas_deportivas = "SELECT
					subc.fecha_operacion,
					(IFNULL(SUM(subcd.ingreso),0) - IFNULL(SUM(subcd.salida),0)) AS cajero_apuestas_deportivas
				FROM tbl_caja_detalle subcd
				INNER JOIN tbl_local_caja_detalle_tipos sublcdt ON sublcdt.id = subcd.tipo_id
				INNER JOIN tbl_caja_detalle_tipos subcdt ON subcdt.id = sublcdt.detalle_tipos_id
				INNER JOIN tbl_caja subc ON subc.id = subcd.caja_id
				INNER JOIN tbl_local_cajas sublc ON sublc.id = subc.local_caja_id
				WHERE
					subcdt.id = 1
					AND subc.fecha_operacion >= '$fecha_inicio'
					AND subc.fecha_operacion < '$fecha_fin'
					AND sublc.local_id = $local_id
				group by subc.fecha_operacion
				order by subc.fecha_operacion";
			}else{
				$query_cajero_apuestas_deportivas = "SELECT
					IFNULL(SUM(subcd.ingreso),0) - IFNULL(SUM(subcd.salida),0)
				FROM tbl_caja_detalle subcd
				INNER JOIN tbl_local_caja_detalle_tipos sublcdt ON sublcdt.id = subcd.tipo_id
				INNER JOIN tbl_caja_detalle_tipos subcdt ON subcdt.id = sublcdt.detalle_tipos_id
				INNER JOIN tbl_caja subc ON subc.id = subcd.caja_id
				INNER JOIN tbl_local_cajas sublc ON sublc.id = subc.local_caja_id
				WHERE
					subcdt.id = 1
					AND subc.fecha_operacion >= '$fecha_inicio'
					AND subc.fecha_operacion < '$fecha_fin'
					-- AND sublc.local_id = 362
				group by subc.fecha_operacion
				order by subc.fecha_operacion";
			}
			$result_cajero_apuestas_deportivas = extractDataCastQuery($query_cajero_apuestas_deportivas);
			while($row1 = $result_cajero_apuestas_deportivas->fetch_assoc()){
				$array_cajero_apuestas_deportivas[] = $row1;
			}
			$final_array_cajero_apuestas_deportivas = completar_fechas_array($array_cajero_apuestas_deportivas, $fecha_inicio, $fecha_fin, 'cajero_apuestas_deportivas');

			$where_caja_id = "";
			if($local_id!="_all_") $where_caja_id.=" AND local_id = '".$local_id."'";	
			$array_sistema_billeteros = [];
			// SISTEMA BILLETEROS 17
			$query_sistema_billeteros = "SELECT
				date(created) as fecha_operacion,
				CAST(IFNULL(SUM(income_amount),0) AS DECIMAL(20,2)) as sistema_billeteros
			FROM tbl_transacciones_repositorio
			WHERE
				created >= '$fecha_inicio'
				and created < '$fecha_fin'
				{$where_caja_id}
				AND tipo = 2
				AND canal_de_venta_id = 17
			group by date(created)
			order by created";
			$result_sistema_billeteros = extractDataCastQuery($query_sistema_billeteros);
			while($row1 = $result_sistema_billeteros->fetch_assoc()){
				$array_sistema_billeteros[] = $row1;
			}
			$final_sistema_billeteros = completar_fechas_array($array_sistema_billeteros, $fecha_inicio, $fecha_fin, 'sistema_billeteros');

			$where_caja_id = "";
			if($local_id!="_all_") $where_caja_id.=" AND sublc.local_id = '".$local_id."'";	
			$array_cajero_billeteros = array();
			// CAJERO BILLETEROS 18
			$query_cajero_billeteros = "SELECT
				subc.fecha_operacion,
				IFNULL(SUM(subcd.ingreso),0) - IFNULL(SUM(subcd.salida),0) as cajero_billeteros
			FROM tbl_caja_detalle subcd
			INNER JOIN tbl_local_caja_detalle_tipos sublcdt ON sublcdt.id = subcd.tipo_id
			INNER JOIN tbl_caja_detalle_tipos subcdt ON subcdt.id = sublcdt.detalle_tipos_id
			INNER JOIN tbl_caja subc ON subc.id = subcd.caja_id
			INNER JOIN tbl_local_cajas sublc ON sublc.id = subc.local_caja_id
			WHERE
				subcdt.id = 4
				AND subc.fecha_operacion >= '$fecha_inicio'
				and subc.fecha_operacion < '$fecha_fin'
				{$where_caja_id}
			group by subc.fecha_operacion
			order by subc.fecha_operacion";
			$result_cajero_billeteros = extractDataCastQuery($query_cajero_billeteros);
			while($row1 = $result_cajero_billeteros->fetch_assoc()){
				$array_cajero_billeteros[] = $row1;
			}
			$final_array_cajero_billeteros = completar_fechas_array($array_cajero_billeteros, $fecha_inicio, $fecha_fin, 'cajero_billeteros');


			// SISTEMA NSOFT = 0

			$array_cajero_nsoft = [];
			// CAJERO NSOFT 19
			$query_cajero_nsoft = "SELECT
				subc.fecha_operacion,
				IFNULL(SUM(subcd.ingreso),0) - IFNULL(SUM(subcd.salida),0) as cajero_nsoft
			FROM tbl_caja_detalle subcd
			INNER JOIN tbl_local_caja_detalle_tipos sublcdt ON sublcdt.id = subcd.tipo_id
			INNER JOIN tbl_caja_detalle_tipos subcdt ON subcdt.id = sublcdt.detalle_tipos_id
			INNER JOIN tbl_caja subc ON subc.id = subcd.caja_id
			INNER JOIN tbl_local_cajas sublc ON sublc.id = subc.local_caja_id
			WHERE
				subcdt.id = 24
				AND subc.fecha_operacion >= '$fecha_inicio'
				and subc.fecha_operacion < '$fecha_fin'
				{$where_caja_id}
			group by subc.fecha_operacion";
			$result_cajero_nsoft = extractDataCastQuery($query_cajero_nsoft);
			while($row1 = $result_cajero_nsoft->fetch_assoc()){
				$array_cajero_nsoft[] = $row1;
			}
			$final_cajero_soft = completar_fechas_array($array_cajero_nsoft, $fecha_inicio, $fecha_fin, 'cajero_nsoft');

			// SISTEMA KIRON = 0;

			// CAJERO KIRON 20
			$array_cajero_kiron = array();
			$query_cajero_kiron = "SELECT
			c.fecha_operacion,
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
			) AS DECIMAL(20,2)) AS cajero_kiron
			FROM tbl_caja c
			LEFT JOIN tbl_local_cajas lc ON (lc.id = c.local_caja_id)
			LEFT JOIN tbl_locales l ON (l.id = lc.local_id)
			INNER JOIN tbl_usuarios_locales ul ON (ul.local_id = l.id AND ul.estado = 1)
			INNER JOIN tbl_usuarios u ON (u.id = ul.usuario_id)
			WHERE
				(l.red_id IN (1,4,6,7,8,9,16) OR l.id = 200)
				AND l.operativo = 1
				AND l.estado = 1
				AND c.fecha_operacion >= '$fecha_inicio'
				AND c.fecha_operacion < '$fecha_fin'
				{$caja_where}
			GROUP BY c.fecha_operacion, l.id
			ORDER BY c.fecha_operacion ASC, l.nombre ASC";
			$result_cajero_kiron = extractDataCastQuery($query_cajero_kiron);
			while($row1 = $result_cajero_kiron->fetch_assoc()){
				$array_cajero_kiron[] = $row1;
			}
			$final_array_cajero_kiron = completar_fechas_array($array_cajero_kiron, $fecha_inicio, $fecha_fin, 'cajero_kiron');


			$where_caja_id = "";
			if($local_id!="_all_") $where_caja_id.=" AND local_id = '".$local_id."'";
			$array_sistema_goldenrace = [];
			// SISTEMA GOLDENRACE 21
			$query_sistema_goldenrace = "SELECT
				date(created) as fecha_operacion,
				CAST(IFNULL(SUM(stake),0) - IFNULL(SUM(paid_out_cash+jackpot_paid+mega_jackpot_paid),0) as decimal(20,2)) as sistema_goldenrace
			FROM tbl_transacciones_repositorio
			WHERE
				created >= '$fecha_inicio'
				and created < '$fecha_fin'
				{$where_caja_id}
				AND canal_de_venta_id = 21
				AND tipo = 4
				AND servicio_id = 3
			group by date(created)
			order by created";
			$result_sistema_goldenrace = extractDataCastQuery($query_sistema_goldenrace);
			while($row1 = $result_sistema_goldenrace->fetch_assoc()){
				$array_sistema_goldenrace[] = $row1;
			}
			$final_sistema_goldenrace = completar_fechas_array($array_sistema_goldenrace, $fecha_inicio, $fecha_fin, 'sistema_goldenrace');

			$where_caja_id = "";
			if($local_id!="_all_") $where_caja_id.=" AND sublc.local_id = '".$local_id."'";	
			// CAJERO GOLDENRACE 22
			$array_cajero_goldenrace = array();
			$query_cajero_goldenrace = "SELECT
				subc.fecha_operacion,
				IFNULL(SUM(subcd.ingreso),0) - IFNULL(SUM(subcd.salida),0) as cajero_goldenrace
			FROM tbl_caja_detalle subcd
			INNER JOIN tbl_local_caja_detalle_tipos sublcdt ON sublcdt.id = subcd.tipo_id
			INNER JOIN tbl_caja_detalle_tipos subcdt ON subcdt.id = sublcdt.detalle_tipos_id
			INNER JOIN tbl_caja subc ON subc.id = subcd.caja_id
			INNER JOIN tbl_local_cajas sublc ON sublc.id = subc.local_caja_id
			WHERE
				subcdt.id = 3
				AND subc.fecha_operacion >= '$fecha_inicio'
				and subc.fecha_operacion < '$fecha_fin'
				{$where_caja_id}
			group by subc.fecha_operacion";
			$result_cajero_goldenrace = extractDataCastQuery($query_cajero_goldenrace);
			while($row1 = $result_cajero_goldenrace->fetch_assoc()){
				$array_cajero_goldenrace[] = $row1;
			}
			$final_array_cajero_goldenrace = completar_fechas_array($array_cajero_goldenrace, $fecha_inicio, $fecha_fin, 'cajero_goldenrace');


			$where_caja_id = "";
			if($local_id!="_all_") $where_caja_id.=" AND rtas.local_id = '".$local_id."'";
			$array_sistema_carreradecaballos = [];
			// SISTEMA CARRERA DE CABALLOS 23
			$query_sistema_carreradecaballos = "SELECT 
				date(rtas.creation_date) as fecha_operacion,
				CAST((
					IFNULL(
						(
							sum(IF(rtas.transaction_type = 4, rtas.amount, 0)) 
						) -(sum(IF(rtas.transaction_type = 5, rtas.amount, 0))),
						0
					)
				) as decimal(20,2)) AS sistema_carreradecaballos
			FROM   tbl_repositorio_tickets_america_simulcast AS rtas
			WHERE  rtas.creation_date >= '$fecha_inicio'
					AND rtas.creation_date < '$fecha_fin'
					{$where_caja_id} 
					and ticket_id is not null
			group by date(rtas.creation_date)";
			$result_sistema_carreradecaballos = extractDataCastQuery($query_sistema_carreradecaballos);
			while($row1 = $result_sistema_carreradecaballos->fetch_assoc()){
				$array_sistema_carreradecaballos[] = $row1;
			}
			$final_sistema_carreradecaballos = completar_fechas_array($array_sistema_carreradecaballos, $fecha_inicio, $fecha_fin, 'sistema_carreradecaballos');

			$where_caja_id = "";
			if($local_id!="_all_") $where_caja_id.=" AND sublc.local_id = '".$local_id."'";
			// CAJERO CARRERADECABALLOS 24
			$array_cajero_carreradecaballos = array();
			$query_cajero_carreradecaballos = "SELECT
				subc.fecha_operacion,
				IFNULL(SUM(subcd.ingreso),0) - IFNULL(SUM(subcd.salida),0) as cajero_carreradecaballos
			FROM tbl_caja_detalle subcd
			INNER JOIN tbl_local_caja_detalle_tipos sublcdt ON sublcdt.id = subcd.tipo_id
			INNER JOIN tbl_caja_detalle_tipos subcdt ON subcdt.id = sublcdt.detalle_tipos_id
			INNER JOIN tbl_caja subc ON subc.id = subcd.caja_id
			INNER JOIN tbl_local_cajas sublc ON sublc.id = subc.local_caja_id
			WHERE
				subcdt.id = 20
				AND subc.fecha_operacion >= '$fecha_inicio'
				AND subc.fecha_operacion < '$fecha_fin'
				{$where_caja_id}
			group by subc.fecha_operacion";
			$result_cajero_carreradecaballos = extractDataCastQuery($query_cajero_carreradecaballos);
			while($row1 = $result_cajero_carreradecaballos->fetch_assoc()){
				$array_cajero_carreradecaballos[] = $row1;
			}
			$final_array_cajero_carreradecaballos = completar_fechas_array($array_cajero_carreradecaballos, $fecha_inicio, $fecha_fin, 'cajero_carreradecaballos');

			// SISTEMA DSVIRTUALGAMING = 0

			// CAJERO DSVIRTUALGAMING 25
			$array_cajero_dsvirtualgaming = array();
			$query_cajero_dsvirtualgaming = "SELECT
			c.fecha_operacion,
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
			) AS DECIMAL(20,2)) AS cajero_dsvirtualgaming
			FROM tbl_caja c
			LEFT JOIN tbl_local_cajas lc ON (lc.id = c.local_caja_id)
			LEFT JOIN tbl_locales l ON (l.id = lc.local_id)
			INNER JOIN tbl_usuarios_locales ul ON (ul.local_id = l.id AND ul.estado = 1)
			INNER JOIN tbl_usuarios u ON (u.id = ul.usuario_id)
			WHERE
				(l.red_id IN (1,4,6,7,8,9,16) OR l.id = 200)
				AND l.operativo = 1
				AND l.estado = 1
				AND c.fecha_operacion >= '$fecha_inicio'
				AND c.fecha_operacion < '$fecha_fin'
				{$caja_where}
			GROUP BY c.fecha_operacion, l.id
			ORDER BY c.fecha_operacion ASC, l.nombre ASC";
			$result_cajero_dsvirtualgaming = extractDataCastQuery($query_cajero_dsvirtualgaming);
			while($row1 = $result_cajero_dsvirtualgaming->fetch_assoc()){
				$array_cajero_dsvirtualgaming[] = $row1;
			}
			$final_array_cajero_dsvirtualgaming = completar_fechas_array($array_cajero_dsvirtualgaming, $fecha_inicio, $fecha_fin, 'cajero_dsvirtualgaming');


			$array_sistema_web1 = [];
			$array_sistema_web2 = [];
			$where_caja_id = "";
			if($local_id!="_all_") $where_caja_id.=" AND local_id = '".$local_id."'";
			// SISTEMA WEB 26
			$query_sistema_web1 = "SELECT
				date(created) as fecha_operacion,
				IFNULL(SUM(deposits),0) - IFNULL(SUM(withdrawals),0) as sistema_web
				FROM tbl_transacciones_repositorio
			WHERE
				created >= '$fecha_inicio'
				and created < '$fecha_fin'
				{$where_caja_id}
				AND tipo = 3
				AND canal_de_venta_id = 16
			group by date(created)";
			$result_sistema_web1 = extractDataCastQuery($query_sistema_web1);
			while($row1 = $result_sistema_web1->fetch_assoc()){
				$array_sistema_web1[] = $row1;
			}
			$final_sistema_web1 = completar_fechas_array($array_sistema_web1, $fecha_inicio, $fecha_fin, 'sistema_web');

			$where_caja_id = "";
			if($local_id!="_all_") $where_caja_id.=" AND ssql.id = '".$local_id."'";
			$query_sistema_web2 = "SELECT 
				sqc.fecha_operacion,
				SUM(IF(swt.tipo_id=1,swt.monto,0))-SUM(IF(swt.tipo_id=2,swt.monto,0)) as sistema_web
			FROM tbl_saldo_web_transaccion swt
			LEFT JOIN tbl_caja sqc ON sqc.id = swt.turno_id
			LEFT JOIN tbl_local_cajas sqlc ON sqlc.id = sqc.local_caja_id
			LEFT JOIN tbl_locales ssql ON ssql.id = sqlc.local_id
			WHERE
				sqc.fecha_operacion >= '$fecha_inicio' and
				sqc.fecha_operacion < '$fecha_fin' 
				{$where_caja_id} and
				swt.tipo_id IN (1,2) AND 
				swt.status = 1 
			group by sqc.fecha_operacion";
			$result_sistema_web2 = extractDataCastQuery($query_sistema_web2);
			while($row1 = $result_sistema_web2->fetch_assoc()){
				$array_sistema_web2[] = $row1;
			}
			$final_sistema_web2 = completar_fechas_array($array_sistema_web2, $fecha_inicio, $fecha_fin, 'sistema_web');
			$final_sistema_web = sub_dinero_sistema($final_sistema_web1, $final_sistema_web2, 'sistema_web');

			$where_caja_id = "";
			if($local_id!="_all_") $where_caja_id.=" AND sublc.local_id = '".$local_id."'";
			// CAJERO WEB 27
			$array_cajero_web = array();
			$query_cajero_web = "SELECT
				subc.fecha_operacion,
				IFNULL(SUM(subcd.ingreso),0) - IFNULL(SUM(subcd.salida),0) as cajero_web
			FROM tbl_caja_detalle subcd
			INNER JOIN tbl_local_caja_detalle_tipos sublcdt ON sublcdt.id = subcd.tipo_id
			INNER JOIN tbl_caja_detalle_tipos subcdt ON subcdt.id = sublcdt.detalle_tipos_id
			INNER JOIN tbl_caja subc ON subc.id = subcd.caja_id
			INNER JOIN tbl_local_cajas sublc ON sublc.id = subc.local_caja_id
			WHERE
				subcdt.id = 2
				AND subc.fecha_operacion >= '$fecha_inicio'
				and subc.fecha_operacion < '$fecha_fin'
				{$where_caja_id}
			group by subc.fecha_operacion";
			$result_cajero_web = extractDataCastQuery($query_cajero_web);
			while($row1 = $result_cajero_web->fetch_assoc()){
				$array_cajero_web[] = $row1;
			}
			$final_array_cajero_web = completar_fechas_array($array_cajero_web, $fecha_inicio, $fecha_fin, 'cajero_web');


			$where_caja_id = "";
			if($local_id!="_all_") $where_caja_id.=" AND local_id = '".$local_id."'";
			$array_sistema_cash = [];
			// SISTEMA CASH 28
			$query_sistema_cash = "SELECT
				date(created) as fecha_operacion,
				CAST(IFNULL(SUM(terminal_deposit_amount),0) - IFNULL(SUM(terminal_withdraw_amount),0) as decimal(20,2)) as sistema_cash
				FROM tbl_transacciones_repositorio
			WHERE
				created >= '$fecha_inicio'
				and created < '$fecha_fin'
				{$where_caja_id}
				AND tipo = 3
				AND canal_de_venta_id = 16
			group by date(created)";
			$result_sistema_cash = extractDataCastQuery($query_sistema_cash);
			while($row1 = $result_sistema_cash->fetch_assoc()){
				$array_sistema_cash[] = $row1;
			}
			$final_sistema_cash = completar_fechas_array($array_sistema_cash, $fecha_inicio, $fecha_fin, 'sistema_cash');

			$where_caja_id = "";
			if($local_id!="_all_") $where_caja_id.=" AND sublc.local_id = '".$local_id."'";
			// CAJERO CASH 29
			$array_cajero_cash = array();
			$query_cajero_cash = "SELECT
				subc.fecha_operacion,
				IFNULL(SUM(subcd.ingreso),0) - IFNULL(SUM(subcd.salida),0) as cajero_cash
			FROM tbl_caja_detalle subcd
			INNER JOIN tbl_local_caja_detalle_tipos sublcdt ON sublcdt.id = subcd.tipo_id
			INNER JOIN tbl_caja_detalle_tipos subcdt ON subcdt.id = sublcdt.detalle_tipos_id
			INNER JOIN tbl_caja subc ON subc.id = subcd.caja_id
			INNER JOIN tbl_local_cajas sublc ON sublc.id = subc.local_caja_id
			WHERE
				subcdt.id = 5
				AND subc.fecha_operacion >= '$fecha_inicio'
				and subc.fecha_operacion < '$fecha_fin'
				{$where_caja_id}
			group by subc.fecha_operacion";
			$result_cajero_cash = extractDataCastQuery($query_cajero_cash);
			while($row1 = $result_cajero_cash->fetch_assoc()){
				$array_cajero_cash[] = $row1;
			}
			$final_array_cajero_cash = completar_fechas_array($array_cajero_cash, $fecha_inicio, $fecha_fin, 'cajero_cash');

			$where_caja_id = "";
			if($local_id!="_all_") $where_caja_id.=" AND local_id = '".$local_id."'";
			$array_sistema_kasnet = [];
			// SISTEMA KASNET 30
			$query_sistema_kasnet = "SELECT
				date(created_at) as fecha_operacion,
				(IFNULL(SUM(total_in), 0) - IFNULL(SUM(total_out), 0)) as sistema_kasnet
			FROM tbl_repositorio_kasnet_resumen
			WHERE
				created_at >= '$fecha_inicio'
				and created_at < '$fecha_fin'
				{$where_caja_id}
			group by date(created_at)";
			$result_sistema_kasnet = extractDataCastQuery($query_sistema_kasnet);
			while($row1 = $result_sistema_kasnet->fetch_assoc()){
				$array_sistema_kasnet[] = $row1;
			}
			$final_sistema_kasnet = completar_fechas_array($array_sistema_kasnet, $fecha_inicio, $fecha_fin, 'sistema_kasnet');

			// SISTEMA DISASHOP 31
			$array_sistema_disashop = array();
			$query_sistema_disashop = "SELECT
				c.fecha_operacion,
				CAST((
					SELECT
						IFNULL(SUM(total_in), 0)
					FROM tbl_repositorio_disashop_resumen
					WHERE
						local_id = l.id
						AND created_at = c.fecha_operacion
				) AS DECIMAL(20,2)) AS sistema_disashop
			FROM tbl_caja c
			LEFT JOIN tbl_local_cajas lc ON (lc.id = c.local_caja_id)
			LEFT JOIN tbl_locales l ON (l.id = lc.local_id)
			INNER JOIN tbl_usuarios_locales ul ON (ul.local_id = l.id AND ul.estado = 1)
			INNER JOIN tbl_usuarios u ON (u.id = ul.usuario_id)
			WHERE
				(l.red_id IN (1,4,6,7,8,9,16) OR l.id = 200)
				AND l.operativo = 1
				AND l.estado = 1
				AND c.fecha_operacion >= '$fecha_inicio'
				AND c.fecha_operacion < '$fecha_fin'
				{$caja_where}
			GROUP BY c.fecha_operacion, l.id
			ORDER BY c.fecha_operacion ASC, l.nombre ASC";
			$result_sistema_disashop = extractDataCastQuery($query_sistema_disashop);
			while($row1 = $result_sistema_disashop->fetch_assoc()){
				$array_sistema_disashop[] = $row1;
			}
			$final_array_sistema_disashop = completar_fechas_array($array_sistema_disashop, $fecha_inicio, $fecha_fin, 'sistema_disashop');


			$where_caja_id = "";
			if($local_id!="_all_") $where_caja_id.=" AND sublc.local_id = '".$local_id."'";
			$array_cajero_kasnet = [];
			// CAJERO KASNET 32
			$query_cajero_kasnet = "SELECT
				subc.fecha_operacion,
				IFNULL(SUM(subcd.ingreso),0) - IFNULL(SUM(subcd.salida),0) as cajero_kasnet
			FROM tbl_caja_detalle subcd
			INNER JOIN tbl_local_caja_detalle_tipos sublcdt ON sublcdt.id = subcd.tipo_id
			INNER JOIN tbl_caja_detalle_tipos subcdt ON subcdt.id = sublcdt.detalle_tipos_id
			INNER JOIN tbl_caja subc ON subc.id = subcd.caja_id
			INNER JOIN tbl_local_cajas sublc ON sublc.id = subc.local_caja_id
			WHERE
				subc.fecha_operacion >= '$fecha_inicio'
				and subc.fecha_operacion < '$fecha_fin'
				{$where_caja_id}
				and subcdt.id = 13
			group by subc.fecha_operacion";
			$result_cajero_kasnet = extractDataCastQuery($query_cajero_kasnet);
			while($row1 = $result_cajero_kasnet->fetch_assoc()){
				$array_cajero_kasnet[] = $row1;
			}
			$final_cajero_kasnet = completar_fechas_array($array_cajero_kasnet, $fecha_inicio, $fecha_fin, 'cajero_kasnet');

			// CAJERO DISASHOP 33
			$array_cajero_disashop = [];
			$query_cajero_disashop = "SELECT
				subc.fecha_operacion,
				IFNULL(SUM(subcd.ingreso),0) - IFNULL(SUM(subcd.salida),0) as cajero_disashop
			FROM tbl_caja_detalle subcd
			INNER JOIN tbl_local_caja_detalle_tipos sublcdt ON sublcdt.id = subcd.tipo_id
			INNER JOIN tbl_caja_detalle_tipos subcdt ON subcdt.id = sublcdt.detalle_tipos_id
			INNER JOIN tbl_caja subc ON subc.id = subcd.caja_id
			INNER JOIN tbl_local_cajas sublc ON sublc.id = subc.local_caja_id
			WHERE
				subcdt.id = 21
				AND subc.fecha_operacion >= '$fecha_inicio'
				and subc.fecha_operacion < '$fecha_fin'
				{$where_caja_id}
			group by subc.fecha_operacion";
			$result_cajero_disashop = extractDataCastQuery($query_cajero_disashop);
			while($row1 = $result_cajero_disashop->fetch_assoc()){
				$array_cajero_disashop[] = $row1;
			}
			$final_cajero_disashop = completar_fechas_array($array_cajero_disashop, $fecha_inicio, $fecha_fin, 'cajero_disashop');

			// SISTEMA ATSNACKS 34
			$array_sistema_atsnacks = [];
			$query_sistema_atsnacks = "SELECT
				c.fecha_operacion,
				CAST((
					SELECT
						(IFNULL(SUM(total), 0) - IFNULL(SUM(note_total), 0))
					FROM tbl_repositorio_atsnacks_resumen
					WHERE
						local_id = l.id
						AND created_at = c.fecha_operacion
				) AS DECIMAL(20,2)) AS sistema_atsnacks
			FROM tbl_caja c
			LEFT JOIN tbl_local_cajas lc ON (lc.id = c.local_caja_id)
			LEFT JOIN tbl_locales l ON (l.id = lc.local_id)
			INNER JOIN tbl_usuarios_locales ul ON (ul.local_id = l.id AND ul.estado = 1)
			INNER JOIN tbl_usuarios u ON (u.id = ul.usuario_id)
			WHERE
				(l.red_id IN (1,4,6,7,8,9,16) OR l.id = 200)
				AND l.operativo = 1
				AND l.estado = 1
				AND c.fecha_operacion >= '$fecha_inicio'
				AND c.fecha_operacion < '$fecha_fin'
				{$caja_where}
			GROUP BY c.fecha_operacion, l.id
			ORDER BY c.fecha_operacion ASC, l.nombre ASC";
			$result_sistema_atsnacks = extractDataCastQuery($query_sistema_atsnacks);
			while($row1 = $result_sistema_atsnacks->fetch_assoc()){
				$array_sistema_atsnacks[] = $row1;
			}
			$final_sistema_atsnacks = completar_fechas_array($array_sistema_atsnacks, $fecha_inicio, $fecha_fin, 'sistema_atsnacks');

			// CAJERO ATSNACKS 35
			$array_cajero_atsnacks = [];
			$query_cajero_atsnacks = "SELECT
				subc.fecha_operacion,
				IFNULL(SUM(subcd.ingreso),0) - IFNULL(SUM(subcd.salida),0) as cajero_atsnacks
			FROM tbl_caja_detalle subcd
			INNER JOIN tbl_local_caja_detalle_tipos sublcdt ON sublcdt.id = subcd.tipo_id
			INNER JOIN tbl_caja_detalle_tipos subcdt ON subcdt.id = sublcdt.detalle_tipos_id
			INNER JOIN tbl_caja subc ON subc.id = subcd.caja_id
			INNER JOIN tbl_local_cajas sublc ON sublc.id = subc.local_caja_id
			WHERE
				subc.fecha_operacion >= '$fecha_inicio'
				and subc.fecha_operacion < '$fecha_fin'
				{$where_caja_id}
				and subcdt.id = 17
			group by subc.fecha_operacion";
			$result_cajero_atsnacks = extractDataCastQuery($query_cajero_atsnacks);
			while($row1 = $result_cajero_atsnacks->fetch_assoc()){
				$array_cajero_atsnacks[] = $row1;
			}
			$final_cajero_atsnacks = completar_fechas_array($array_cajero_atsnacks, $fecha_inicio, $fecha_fin, 'cajero_atsnacks');

			// CAJERO BINGO 37
			$array_cajero_bingo = array();
			$query_cajero_bingo = "SELECT
				subc.fecha_operacion,
				IFNULL(SUM(subcd.ingreso),0) - IFNULL(SUM(subcd.salida),0) as cajero_bingo
			FROM tbl_caja_detalle subcd
			INNER JOIN tbl_local_caja_detalle_tipos sublcdt ON sublcdt.id = subcd.tipo_id
			INNER JOIN tbl_caja_detalle_tipos subcdt ON subcdt.id = sublcdt.detalle_tipos_id
			INNER JOIN tbl_caja subc ON subc.id = subcd.caja_id
			INNER JOIN tbl_local_cajas sublc ON sublc.id = subc.local_caja_id
			WHERE
				subcdt.id = 15
				AND subc.fecha_operacion >= '$fecha_inicio'
				and subc.fecha_operacion < '$fecha_fin'
				{$where_caja_id}
			group by subc.fecha_operacion";
			$result_cajero_bingo = extractDataCastQuery($query_cajero_bingo);
			while($row1 = $result_cajero_bingo->fetch_assoc()){
				$array_cajero_bingo[] = $row1;
			}
			$final_array_cajero_bingo = completar_fechas_array($array_cajero_bingo, $fecha_inicio, $fecha_fin, 'cajero_bingo');


			// TELEVENTAS SISTEMA 38
			$array_televentas_sistema = array();
			$query_televentas_sistema = "SELECT
				c.fecha_operacion,
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
					),0) AS DECIMAL(20,2)) as televentas_sistema
			FROM tbl_caja c
			LEFT JOIN tbl_local_cajas lc ON (lc.id = c.local_caja_id)
			LEFT JOIN tbl_locales l ON (l.id = lc.local_id)
			INNER JOIN tbl_usuarios_locales ul ON (ul.local_id = l.id AND ul.estado = 1)
			INNER JOIN tbl_usuarios u ON (u.id = ul.usuario_id)
			WHERE
				(l.red_id IN (1,4,6,7,8,9,16) OR l.id = 200)
				AND l.operativo = 1
				AND l.estado = 1
				AND c.fecha_operacion >= '$fecha_inicio'
				AND c.fecha_operacion < '$fecha_fin'
				{$caja_where}
			GROUP BY c.fecha_operacion, l.id
			ORDER BY c.fecha_operacion ASC, l.nombre ASC";
			$result_televentas_sistema = extractDataCastQuery($query_televentas_sistema);
			while($row1 = $result_televentas_sistema->fetch_assoc()){
				$array_televentas_sistema[] = $row1;
			}
			$final_array_televentas_sistema = completar_fechas_array($array_televentas_sistema, $fecha_inicio, $fecha_fin, 'televentas_sistema');


			// TELEVENTAS CAJERO 39
			$array_televentas_cajero = array();
			$query_televentas_cajero = "SELECT
				c.fecha_operacion,
				IFNULL(CAST((
					SELECT SUM(IFNULL(sqcd.ingreso, 0) - IFNULL(sqcd.salida, 0)) as resultado
					FROM tbl_local_caja_detalle_tipos sqlcdt
						LEFT JOIN tbl_caja_detalle sqcd ON (sqcd.tipo_id = sqlcdt.id)
						LEFT JOIN tbl_caja sqc ON sqcd.caja_id = sqc.id
					WHERE
						sqlcdt.local_id = l.id AND
						sqlcdt.detalle_tipos_id = 18 AND -- WEB TELEVENTAS;
						sqc.fecha_operacion = c.fecha_operacion
					) AS DECIMAL(20,2)),0) as televentas_cajero
			FROM tbl_caja c
			LEFT JOIN tbl_local_cajas lc ON (lc.id = c.local_caja_id)
			LEFT JOIN tbl_locales l ON (l.id = lc.local_id)
			INNER JOIN tbl_usuarios_locales ul ON (ul.local_id = l.id AND ul.estado = 1)
			INNER JOIN tbl_usuarios u ON (u.id = ul.usuario_id)
			WHERE
				(l.red_id IN (1,4,6,7,8,9,16) OR l.id = 200)
				AND l.operativo = 1
				AND l.estado = 1
				AND c.fecha_operacion >= '$fecha_inicio'
				AND c.fecha_operacion < '$fecha_fin'
				{$caja_where}
			GROUP BY c.fecha_operacion, l.id
			ORDER BY c.fecha_operacion ASC, l.nombre ASC";
			$result_televentas_cajero = extractDataCastQuery($query_televentas_cajero);
			while($row1 = $result_televentas_cajero->fetch_assoc()){
				$array_televentas_cajero[] = $row1;
			}
			$final_array_televentas_cajero = completar_fechas_array($array_televentas_cajero, $fecha_inicio, $fecha_fin, 'televentas_cajero');


			$where_caja_id = "";
			if($local_id!="_all_") $where_caja_id.=" AND ssql.id = '".$local_id."'";
			$array_torito_sistema = [];
			// TORITO SISTEMA 40
			$query_torito_sistema = "SELECT
				sqc.fecha_operacion,
				IFNULL(
					(
						(SUM(IF(tt.id_torito_tipo_transaccion = 1, tt.amount, 0))-
						SUM(IF(tt.id_torito_tipo_transaccion = 2, tt.amount, 0)))+
						(SUM(IF(tt.id_torito_tipo_transaccion = 4, tt.amount, 0))-
						SUM(IF(tt.id_torito_tipo_transaccion = 5, tt.amount, 0)))
					), 0) as torito_sistema
			FROM
				tbl_torito_transaccion tt
				JOIN tbl_torito_acceso ta ON ta.partnertoken = tt.partnertoken 
				AND ta.idcashier = tt.user_id AND tt.cc_id=ta.idstore
				JOIN tbl_caja sqc ON sqc.id = ta.turno_id
				JOIN tbl_local_cajas sqlc ON sqlc.id = sqc.local_caja_id
				JOIN tbl_locales ssql ON ssql.id = sqlc.local_id -- AND ssql.cc_id = ta.idstore
			WHERE
				tt.status = 1 AND 
				ta.status = 1 
				{$where_caja_id} AND 
				tt.date >= '$fecha_inicio' and 
				tt.date < '$fecha_fin' and
				sqc.fecha_operacion >= '$fecha_inicio' and
				sqc.fecha_operacion < '$fecha_fin'
			group by sqc.fecha_operacion";
			$result_torito_sistema = extractDataCastQuery($query_torito_sistema);
			while($row1 = $result_torito_sistema->fetch_assoc()){
				$array_torito_sistema[] = $row1;
			}
			$final_torito_sistema = completar_fechas_array($array_torito_sistema, $fecha_inicio, $fecha_fin, 'torito_sistema');

			$where_caja_id = "";
			if($local_id!="_all_") $where_caja_id.=" AND sqlcdt.local_id = '".$local_id."'";
			// TORITO CAJERO 41
			$array_torito_cajero = array();
			$query_torito_cajero = "SELECT
				sqc.fecha_operacion,
				SUM(IFNULL(sqcd.ingreso, 0) - IFNULL(sqcd.salida, 0)) as torito_cajero
			FROM tbl_local_caja_detalle_tipos sqlcdt
			LEFT JOIN tbl_caja_detalle sqcd ON (sqcd.tipo_id = sqlcdt.id)
			LEFT JOIN tbl_caja sqc ON sqcd.caja_id = sqc.id
			WHERE
				sqc.fecha_operacion >= '$fecha_inicio' and
				sqc.fecha_operacion < '$fecha_fin'
				{$where_caja_id} AND
				sqlcdt.detalle_tipos_id = 19 -- WEB TORITO;
			group by sqc.fecha_operacion";
			$result_torito_cajero = extractDataCastQuery($query_torito_cajero);
			while($row1 = $result_torito_cajero->fetch_assoc()){
				$array_torito_cajero[] = $row1;
			}
			$final_array_torito_cajero = completar_fechas_array($array_torito_cajero, $fecha_inicio, $fecha_fin, 'torito_cajero');


			// SISTEMA ALTENAR
			$array_altenar_sistema = [];
			$query_sistema_altenar = "SELECT
				c.fecha_operacion,
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
				) AS DECIMAL(20, 2)) AS sistema_altenar
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
				and l.id = $local_id and u.id = {$login['id']}
			GROUP BY c.fecha_operacion, l.id
			order by c.fecha_operacion asc";
			$result_sistema_altenar = extractDataCastQuery($query_sistema_altenar);
			while($row1 = $result_sistema_altenar->fetch_assoc()){
				$array_altenar_sistema[] = $row1;
			}
			$final_altenar_sistema = completar_fechas_array($array_altenar_sistema, $fecha_inicio, $fecha_fin, 'sistema_altenar');

			// CAJERO ALTENAR
			$array_altenar_cajero = [];
			$query_cajero_altenar = "SELECT
				c.fecha_operacion,
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
				) AS DECIMAL(20, 2)) AS cajero_altenar
				
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
				and l.id = $local_id and u.id = {$login['id']}
			GROUP BY c.fecha_operacion, l.id
			order by c.fecha_operacion asc";
			$result_cajero_altenar = extractDataCastQuery($query_cajero_altenar);
			while($row1 = $result_cajero_altenar->fetch_assoc()){
				$array_altenar_cajero[] = $row1;
			}
			$final_altenar_cajero = completar_fechas_array($array_altenar_cajero, $fecha_inicio, $fecha_fin, 'cajero_altenar');

			// CONCATENACION DE DATOS
			$datos_combinados = array();
			$cantidad_datos_combinados = count($final_array_fecha_operacion);
			// $cantidad_sistema_bingo_resultado = count($sistema_bingo_resultado) * $count_locales;
			for($i = 0; $i < $cantidad_datos_combinados; $i++){
				// for($j = 0; $j < $cantidad_sistema_bingo_resultado; $j++){
					$datos_combinados[$i]['local_id'] = $local_id;
					$datos_combinados[$i]['fecha_operacion'] = $final_array_fecha_operacion[$i]['fecha_operacion'];
					$datos_combinados[$i]['local_nombre'] = $final_array_local_nombre[$i]['local_nombre'];
		
					$datos_combinados[$i]['sistema_nsoft'] = 0;
					$datos_combinados[$i]['sistema_kiron'] = 0;
					$datos_combinados[$i]['sistema_dsvirtualgaming'] = 0;
		
					// if($datos_combinados[$i]['fecha_operacion'] == $sistema_bingo_resultado[$j]['sistema_bingo']['fecha']){
					// }
					$datos_combinados[$i]['sistema_bingo'] = $sistema_bingo_resultado[$i]['sistema_bingo']['sistema_bingo'];
					$datos_combinados[$i]['sub_dinero_sistema'] = $final_sub_dinero_sistema[$i]['sub_dinero_sistema'];
					$datos_combinados[$i]['sub_resultado_voucher'] = $final_sub_resultado_voucher[$i]['sub_resultado_voucher'];
					$datos_combinados[$i]['premios_no_reclamados'] = $final_premios_no_reclamados[$i]['premios_no_reclamados'];
					$datos_combinados[$i]['sistema_devolucion'] = $final_sistema_devolucion[$i]['sistema_devolucion'];
					$datos_combinados[$i]['sistema_devolucion_carrera_caballos'] = $final_sistema_devolucion_carrera_caballos[$i]['sistema_devolucion_carrera_caballos'];
					$datos_combinados[$i]['cajero_nsoft'] = $final_cajero_soft[$i]['cajero_nsoft'];
					$datos_combinados[$i]['sistema_goldenrace'] = $final_sistema_goldenrace[$i]['sistema_goldenrace'];
					$datos_combinados[$i]['sistema_carreradecaballos'] = $final_sistema_carreradecaballos[$i]['sistema_carreradecaballos'];
					$datos_combinados[$i]['sistema_web'] = $final_sistema_web[$i]['sistema_web']['sistema_web'];
					$datos_combinados[$i]['sistema_cash'] = $final_sistema_cash[$i]['sistema_cash'];
					$datos_combinados[$i]['sistema_kasnet'] = $final_sistema_kasnet[$i]['sistema_kasnet'];
					$datos_combinados[$i]['torito_sistema'] = $final_torito_sistema[$i]['torito_sistema'];
					$datos_combinados[$i]['sistema_billeteros'] = $final_sistema_billeteros[$i]['sistema_billeteros'];
					$datos_combinados[$i]['sistema_altenar'] = $final_altenar_sistema[$i]['sistema_altenar'];
					$datos_combinados[$i]['cajero_altenar'] = $final_altenar_cajero[$i]['cajero_altenar'];
					$datos_combinados[$i]['cajero_disashop'] = $final_cajero_disashop[$i]['cajero_disashop'];
					$datos_combinados[$i]['cajero_kasnet'] = $final_cajero_kasnet[$i]['cajero_kasnet'];
					$datos_combinados[$i]['sistema_atsnacks'] = $final_sistema_atsnacks[$i]['sistema_atsnacks'];
					$datos_combinados[$i]['cajero_atsnacks'] = $final_cajero_atsnacks[$i]['cajero_atsnacks'];
					$datos_combinados[$i]['dinero_cajero'] = $final_array_dinero_cajero[$i]['dinero_cajero'];
					$datos_combinados[$i]['cajero_pagos_manuales'] = $final_array_cajero_pagos_manuales[$i]['cajero_pagos_manuales'];
					$datos_combinados[$i]['cajero_devolucion'] = $final_array_cajero_devolucion[$i]['cajero_devolucion'];
					$datos_combinados[$i]['cajero_devolucion_carrera_caballos'] = $final_array_cajero_devolucion_carrera_caballos[$i]['cajero_devolucion_carrera_caballos'];
					$datos_combinados[$i]['no_reclamado'] = $final_array_no_reclamado[$i]['no_reclamado'];
					$datos_combinados[$i]['sistema_pagos_manuales'] = $final_array_sistema_pagos_manuales[$i]['sistema_pagos_manuales'];
					$datos_combinados[$i]['sistema_apuestas_deportivas'] = $final_array_sistema_apuestas_deportivas[$i]['sistema_apuestas_deportivas'];
					$datos_combinados[$i]['cajero_apuestas_deportivas'] = $final_array_cajero_apuestas_deportivas[$i]['cajero_apuestas_deportivas'];
					$datos_combinados[$i]['cajero_billeteros'] = $final_array_cajero_billeteros[$i]['cajero_billeteros'];
					$datos_combinados[$i]['cajero_kiron'] = $final_array_cajero_kiron[$i]['cajero_kiron'];
					$datos_combinados[$i]['cajero_goldenrace'] = $final_array_cajero_goldenrace[$i]['cajero_goldenrace'];
					$datos_combinados[$i]['cajero_carreradecaballos'] = $final_array_cajero_carreradecaballos[$i]['cajero_carreradecaballos'];
					$datos_combinados[$i]['cajero_dsvirtualgaming'] = $final_array_cajero_dsvirtualgaming[$i]['cajero_dsvirtualgaming'];
					$datos_combinados[$i]['cajero_web'] = $final_array_cajero_web[$i]['cajero_web'];
					$datos_combinados[$i]['cajero_cash'] = $final_array_cajero_cash[$i]['cajero_cash'];
					$datos_combinados[$i]['sistema_disashop'] = $final_array_sistema_disashop[$i]['sistema_disashop'];
					$datos_combinados[$i]['cajero_bingo'] = $final_array_cajero_bingo[$i]['cajero_bingo'];
					$datos_combinados[$i]['televentas_sistema'] = $final_array_televentas_sistema[$i]['televentas_sistema'];
					$datos_combinados[$i]['televentas_cajero'] = $final_array_televentas_cajero[$i]['televentas_cajero'];
					$datos_combinados[$i]['torito_cajero'] = $final_array_torito_cajero[$i]['torito_cajero'];
				// }
			}
			$data_merged = [];
			$data_merged[] = $datos_combinados;
			

			foreach($data_merged as $data_local){
				foreach($data_local as $data){
					$data_final[] = $data;
				}
			}
		}
	}

	mysqli_free_result($result_fecha_operacion);
	// mysqli_free_result($result_local_id);
	mysqli_free_result($result_local_nombre);
	mysqli_free_result($result_dinero_cajero);
	mysqli_free_result($result_cajero_pagos_manuales);
	mysqli_free_result($result_cajero_devolucion);
	mysqli_free_result($result_cajero_devolucion_carrera_caballos);
	// mysqli_free_result($result_sub_dinero_sistema);
	// mysqli_free_result($result_sub_dinero_sistema1_1);
	// mysqli_free_result($result_sub_dinero_sistema1_2);
	mysqli_free_result($result_sub_dinero_sistema1);
	mysqli_free_result($result_sub_dinero_sistema2_1);
	mysqli_free_result($result_sub_dinero_sistema2_2);
	mysqli_free_result($result_sub_dinero_sistema3);
	mysqli_free_result($result_sub_dinero_sistema4_1);
	mysqli_free_result($result_sub_dinero_sistema4_2);
	mysqli_free_result($result_sub_dinero_sistema5);
	mysqli_free_result($result_sub_dinero_sistema6);
	mysqli_free_result($result_sub_dinero_sistema7);
	mysqli_free_result($result_no_reclamado);
	// mysqli_free_result($result_sub_resultado_voucher);
	mysqli_free_result($result_sistema_pagos_manuales);
	mysqli_free_result($result_premios_no_reclamados);
	mysqli_free_result($result_sistema_devolucion);
	mysqli_free_result($result_sistema_devolucion_carrera_caballos);
	mysqli_free_result($result_sistema_apuestas_deportivas);
	mysqli_free_result($result_cajero_apuestas_deportivas);
	mysqli_free_result($result_sistema_billeteros);
	mysqli_free_result($result_cajero_billeteros);
	mysqli_free_result($result_cajero_nsoft);
	mysqli_free_result($result_cajero_kiron);
	mysqli_free_result($result_sistema_goldenrace);
	mysqli_free_result($result_cajero_goldenrace);
	mysqli_free_result($result_sistema_carreradecaballos);
	mysqli_free_result($result_cajero_carreradecaballos);
	mysqli_free_result($result_cajero_dsvirtualgaming);
	// mysqli_free_result($result_sistema_web);
	mysqli_free_result($result_cajero_web);
	mysqli_free_result($result_sistema_cash);
	mysqli_free_result($result_cajero_cash);
	mysqli_free_result($result_sistema_kasnet);
	mysqli_free_result($result_sistema_disashop);
	mysqli_free_result($result_cajero_kasnet);
	mysqli_free_result($result_cajero_disashop);
	mysqli_free_result($result_sistema_atsnacks);
	mysqli_free_result($result_cajero_atsnacks);
	// mysqli_free_result($result_sistema_bingo);
	mysqli_free_result($result_sistema_bingo1);
	mysqli_free_result($result_sistema_bingo2);
	mysqli_free_result($result_cajero_bingo);
	mysqli_free_result($result_televentas_sistema);
	mysqli_free_result($result_televentas_cajero);
	mysqli_free_result($result_torito_sistema);
	mysqli_free_result($result_torito_cajero);
	mysqli_close($mysqli);

	foreach ($data_final as $key => $value) {
		$tr=[];
		$tr["local_id"]=$value["local_id"];
		$tr["fecha_operacion"]=$value["fecha_operacion"];
		$tr["local_nombre"]=$value["local_nombre"];

		$tr["resultado_sistema"]=($value["televentas_sistema"] + $value["torito_sistema"]) + $value["sub_dinero_sistema"] + $value["sistema_altenar"];
		$tr["dinero_cajero"]=$value["dinero_cajero"];
		$tr["diferencia_resultado"]=($tr["dinero_cajero"] - $tr["resultado_sistema"]);

		// ALTENAR
		$tr["sistema_altenar"]=$value["sistema_altenar"];
		$tr["cajero_altenar"]=$value["cajero_altenar"];
		$tr["diferencia_altenar"]=($tr["sistema_altenar"]-$tr["cajero_altenar"]);

		$tr["cajero_pagos_manuales"]=$value["cajero_pagos_manuales"];
		$tr["sistema_pagos_manuales"]=$value["sistema_pagos_manuales"];
		$tr["diferencia_3"]=($tr["cajero_pagos_manuales"]-$tr["sistema_pagos_manuales"]);

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

		$tr["sistema_web_televentas"]=$value["televentas_sistema"];
		$tr["cajero_web_televentas"]=$value["televentas_cajero"];
		$tr["diferencia_web_televentas"]=($tr["sistema_web_televentas"] - $tr["cajero_web_televentas"]);

		$tr["sistema_cash"]=$value["sistema_cash"];
		$tr["cajero_cash"]=$value["cajero_cash"];
		$tr["diferencia_cash"]=($tr["cajero_cash"] - $tr["sistema_cash"]);

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

		$tr["resultado_voucher"]=$value["televentas_sistema"] + $value["sub_resultado_voucher"] + $value["cajero_altenar"];
		$tr["premios_no_reclamados"]=$value["premios_no_reclamados"];
		$tr["diferencia_2"]=($tr["resultado_sistema"] - ($tr["resultado_voucher"]+$tr["sistema_devolucion_carrera_caballos"]+$tr["sistema_devolucion"]+$tr["sistema_pagos_manuales"]));

		$table["tbody"][]=$tr;
	}
?>
	<?php if(array_key_exists($menu_id,$usuario_permisos) && in_array("export", $usuario_permisos[$menu_id])): ?>
		<div class="row">
			<div class="col-lg-12">
				<button type="submit" class="btn btn-warning btn-xs btn_export_caja_auditoria2">
					<span class="glyphicon glyphicon-download-alt"></span>
					Exportar XLS
				</button>
			</div>
		</div>
	<?php endif; ?>
	<div class="row tablaHeight">
		<div class="table_container2">
			<table id="tbl_auditoria2" name="tbl_auditoria_name" class="table table-condensed table-small table-bordered table-striped" style="table-layout: fixed">
				<thead>
					<tr>
						<th style="width:190px; height: 58px;" colspan="1" rowspan="2" class="stuck">Local</th>
						<th style="width:100px; height: 58px;" colspan="1" rowspan="2" class="text-center stuck">Fecha</th>
						<th style="width:80px; height: 58px;" colspan="1" rowspan="2" class="bg-warning text-center stuck">Resultado<br/>Cajero</th>
						<th style="width:80px; height: 58px;" colspan="1" rowspan="2" class="bg-warning text-center stuck">Resultado<br/>Sistema</th>
						<th style="width:80px; height: 58px;" colspan="1" rowspan="2" class="bg-warning text-center stuck">Resultado<br/>Diferencia</th>
						<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">Pagos Manuales</th>
						<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">Apuestas Deportivas</th>
						<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">Billeteros</th>
						<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">Golden Race</th>
						<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">Carrera de Caballos</th>
						<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">DS Virtual Gaming</th>
						<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">Bingo</th>
						<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">Web</th>
						<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">Web Televentas</th>
						<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">Cash In/Out</th>
						<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">Apuestas Deportiva ALTENAR</th>
						<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">Kasnet</th>
						<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">Disashop</th>
						<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">ATSnacks</th>
						<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">Devoluciones</th>
						<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">Devoluciones Carrera de Caballos</th>
						<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">Torito</th>
						<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">Nsoft</th>
						<th style="width:260px;" colspan="3" rowspan="1" class="bg-primary text-center">Kiron</th>
						<th style="width:500px;" colspan="6" rowspan="1" class="text-center">Validacion Sistema</th>
						<th style="width:85px;" colspan="1" rowspan="2" class="text-center">Opt</th>
					</tr>

					<tr>
						<!-- Pagos Manuales -->
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

						<!-- Golden Race -->
						<th>Sistema&nbsp;</th>
						<th>Cajero&nbsp;&nbsp;</th>
						<th>Diferencia</th>

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
						<th>Sistema&nbsp;</th>
						<th>Cajero&nbsp;&nbsp;</th>
						<th>Diferencia</th>

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
						<th>Sistema&nbsp;</th>
						<th>Cajero&nbsp;&nbsp;</th>
						<th>Diferencia</th>

						<!-- Torito -->
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
							<td style="height: 25px" class="stuck"><?php echo $tr["local_nombre"];?></td>
							<td style="height: 25px" class="stuck"><?php echo $tr["fecha_operacion"];?></td>

							<!-- Resultado -->
							<td style="height: 25px" class="stuck"><?php echo number_format($tr["resultado_sistema"],2);?></td>
							<td style="height: 25px" class="stuck"><?php echo number_format($tr["dinero_cajero"],2);?></td>
							<td style="height: 25px" class="stuck <?php echo ((number_format($tr["diferencia_resultado"],2) != 0.00) ? "bg-danger text-white text-bold":""); ?>"><?php echo number_format($tr["diferencia_resultado"],2);?></td>

							<!-- Pagos Manuales -->
							<td style="height: 25px"><?php echo number_format($tr["sistema_pagos_manuales"],2);?></td>
							<td style="height: 25px"><?php echo number_format($tr["cajero_pagos_manuales"],2);?></td>
							<td style="height: 25px" class="<?php echo ((number_format($tr["diferencia_3"],2) != 0.00) ? "bg-danger text-white text-bold":""); ?>"><?php echo number_format($tr["diferencia_3"],2);?></td>

							<!-- Caja -->
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
							<td style="height: 25px"><?php echo number_format($tr["sistema_dsvirtualgaming"],2);?></td>
							<td style="height: 25px"><?php echo number_format($tr["cajero_dsvirtualgaming"],2);?></td>
							<td style="height: 25px" class="<?php echo ((number_format($tr["diferencia_dsvirtualgaming"],2) != 0.00) ? "bg-danger text-white text-bold":""); ?>"><?php echo number_format($tr["diferencia_dsvirtualgaming"],2);?></td>


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
							<td style="height: 25px"><?php echo number_format($tr["sistema_atsnacks"],2);?></td>
							<td style="height: 25px"><?php echo number_format($tr["cajero_atsnacks"],2);?></td>
							<td style="height: 25px" class="<?php echo ((number_format($tr["diferencia_atsnacks"],2) != 0.00) ? "bg-danger text-white text-bold":""); ?>"><?php echo number_format($tr["diferencia_atsnacks"],2);?></td>

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
							<td style="height: 25px"><?php echo number_format($tr["sistema_nsoft"],2);?></td>
							<td style="height: 25px"><?php echo number_format($tr["cajero_nsoft"],2);?></td>
							<td style="height: 25px" class="<?php echo ((number_format($tr["diferencia_nsoft"],2) != 0.00) ? "bg-danger text-white text-bold":""); ?>"><?php echo number_format($tr["diferencia_nsoft"],2);?></td>

							<!-- KIRON -->
							<td style="height: 25px"><?php echo number_format($tr["sistema_kiron"],2);?></td>
							<td style="height: 25px"><?php echo number_format($tr["cajero_kiron"],2);?></td>
							<td style="height: 25px" class="<?php echo ((number_format($tr["diferencia_kiron"],2) != 0.00) ? "bg-danger text-white text-bold":""); ?>"><?php echo number_format($tr["diferencia_kiron"],2);?></td>

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
	</div>
<?php } ?>