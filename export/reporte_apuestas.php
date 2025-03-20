<?php
	include("../api/api_db_connect.php");
	include("../sys/sys_login.php");
	date_default_timezone_set("America/Lima");	
	$microtime_init=microtime(true);
	$post = [];

		if (empty($_POST["filtro"]["canales_de_venta"])) {
			$post["filtro"]["canales_de_venta"] = [];
		}else{
			$post["filtro"]["canales_de_venta"] = $_POST["filtro"]["canales_de_venta"];			
		}

		if (empty($_POST["filtro"]["locales"])) {
			$post["filtro"]["locales"] = [];
		}else{
			$post["filtro"]["locales"] = $_POST["filtro"]["locales"];
		}

		if (empty($_POST["filtro"]["red_id"])) {
			$post["filtro"]["red_id"] = [];
		}else{
			$post["filtro"]["red_id"] = $_POST["filtro"]["red_id"];			
		}

		if (empty($_POST["filtro"]["zona_id"])) {
			$post["filtro"]["zona_id"] = [];
		}else{
			$post["filtro"]["zona_id"] = $_POST["filtro"]["zona_id"];
		}

		$post["filtro"]["fecha_fin"] = $_POST["filtro"]["fecha_fin"];
		$post["filtro"]["fecha_inicio"] = $_POST["filtro"]["fecha_inicio"];
		$post["where"]="resultado_apuestas";

	if($post["where"]=="resultado_apuestas"){
		
		/********************************* data api *******************************************/
			// $locales_where="WHERE id IS NOT NULL";
			$filtro_where="WHERE d.id IS NOT NULL";

			$fecha_inicio = date("Y-m-d H:i:s",strtotime("-1 week"));
			$fecha_fin = date("Y-m-d H:i:s");

			$red_id = false;
			$zona_id = false;
			if(array_key_exists("filtro", $post)){
				$filtro=$post["filtro"];
				
				if(array_key_exists("red_id", $filtro)){
					if(is_array($filtro["red_id"])){				
						$red_id = $filtro["red_id"];
						sort($red_id);
					}
				}
				if(array_key_exists("zona_id", $filtro)){
					if(is_array($filtro["zona_id"])){				
						$zona_id = $filtro["zona_id"];
						sort($zona_id);
					}
				}
				if(array_key_exists("fecha_inicio", $filtro)){
					$fecha_inicio = $filtro["fecha_inicio"];
				}
				if(array_key_exists("fecha_fin", $filtro)){
					$fecha_fin = date("Y-m-d",strtotime($filtro["fecha_fin"]." +1 day"));
				}
				if(array_key_exists("canales_de_venta", $filtro)){
					if($filtro["canales_de_venta"]){
						// $liq_locales_where .= " AND canal_de_venta_id IN ('".implode("','", $filtro["canales_de_venta"])."')";
						$filtro_where .= " AND d.canal_de_venta_id IN ('".implode("','", $filtro["canales_de_venta"])."')";
					}
				}
				if(array_key_exists("locales", $filtro)){
					if($filtro["locales"]){
						if(in_array("all", $filtro["locales"])){
							$filtro_where .= " AND l.id IN ('".implode("','", $login["usuario_locales"])."')";
						}else{
							$filtro_where .= " AND l.id IN ('".implode("','", $filtro["locales"])."')";
						}
					}else{
						if($login["usuario_locales"]){
							$filtro_where .= " AND l.id IN ('".implode("','", $login["usuario_locales"])."')";
						}
					}
				}else{
					if($login["usuario_locales"]){
						$filtro_where .= " AND l.id IN ('".implode("','", $login["usuario_locales"])."')";
					}			
				}
				$return["filtro"]=$filtro;
			}else{
				if($login["usuario_locales"]){
					$filtro_where .= " AND l.id IN ('".implode("','", $login["usuario_locales"])."')";
				}
			}
			$return["login"]=$login;
			if($red_id){
				$filtro_where.=" AND (";
				foreach ($red_id as $red_id_key => $red_id_value) {
					if($red_id_key>0){
						$filtro_where.=" OR ";
					}
					if($red_id_value == 0){
						$filtro_where.= "l.red_id IS NULL OR l.red_id = '0'";
					}else{
						$filtro_where.= "l.red_id = '".$red_id_value."'";
					}
				}
				$filtro_where.=" )";
			}
			if($zona_id){
				$filtro_where.=" AND (";
				foreach ($zona_id as $zona_id_key => $zona_id_value) {
					if($zona_id_key>0){
						$filtro_where.=" OR ";
					}
					if($zona_id_value == 0){
						$filtro_where.= "l.zona_id IS NULL OR l.zona_id = '0'";
					}else{
						$filtro_where.= "l.zona_id = '".$zona_id_value."'";
					}
				}
				$filtro_where.=" )";
			}

			// $cdv_arr = array();
			// 	$cdv_command = "SELECT id, codigo FROM tbl_canales_venta WHERE estado = '1' ORDER BY codigo ASC";
			// 	$cdv_query = $mysqli->query($cdv_command);
			// 	while($cdv=$cdv_query->fetch_assoc()){
			// 		$cdv_arr[$cdv["id"]]=$cdv;
			// 	}
			// $locales_arr = array();
			// 	$locales_command = "SELECT l.id, l.nombre FROM tbl_locales l $locales_where";
			// 	$locales_query = $mysqli->query($locales_command);
			// 	while($l=$locales_query->fetch_assoc()) {
			// 		$locales_arr[$l["id"]]=$l;
			// 	}
			
			$totales=[];
			$resumen=[];

			$filtro_where.=" AND d.created >= '".$fecha_inicio."'";
			$filtro_where.=" AND d.created < '".$fecha_fin."'";
			$filtro_where.=" AND l.reportes_mostrar = '1'";

			$return["filtro_where"]=$filtro_where;

			$return_info["time_before_query"] = number_format(microtime(true) - $microtime_init,2)." s";
			$return_info["memory_before_query"]=round(memory_get_usage()/1024/1024,2)." MB";
			$apuestas_command = "
				SELECT
					YEAR(d.created) AS year,
					DATE_FORMAT(d.created,'%m') AS month,
					d.canal_de_venta_id,
					d.local_id,
					IF(l.administracion_tipo = 1, 'No Operada', IF(l.administracion_tipo = 2, 'Operada', '-')) AS 'administracion',
					CAST(SUM(d.apostado) / SUM(IF(d.canal_de_venta_id IN (18,21), num_tickets, 1)) AS DECIMAL(10,2)) AS apuesta_x_ticket,
					cdv.codigo AS canal_de_venta,
					DATE(d.created) AS fecha,
					-- CONCAT(ROUND(( (SUM(d.apostado) - SUM(d.ganado))/SUM(d.apostado) * 100 ),2),'%') AS hold,
					ROUND(( (SUM(d.apostado) - SUM(d.ganado))/SUM(d.apostado) * 100 ),2) AS hold,
					-- CAST(SUM(d.apostado) - SUM(d.ganado) AS DECIMAL(10,2)) AS net_win,
					CAST(SUM(d.apostado) - SUM(d.ganado) AS DECIMAL(10,2)) AS net_win,
					l.nombre AS nombre,
					-- CONCAT('[',l.id,']',' ',l.nombre) AS nombre,
					l.asesor_id,
					lp.nombre AS asesor_nombre,
					SUM(IF(d.canal_de_venta_id IN (18,21), num_tickets, 1)) AS num_tickets,
					COUNT(IF(d.state IN ('Won','Returned'), d.id, NULL)) AS num_tickets_ganados,
					COUNT(IF(d.canal_de_venta_id = 15, IF(d.state IN ('Won','Returned'), d.id, NULL), IF(d.state IN ('Won','Returned'), IF(d.paid_day IS NOT NULL, d.id, NULL), NULL))) AS num_tickets_ganados_pagados,
					COUNT(IF(d.canal_de_venta_id = 15, NULL, IF(d.state IN ('Won','Returned'), IF(d.paid_day IS NULL, d.id, NULL), NULL))) AS num_tickets_por_pagar,
					IF(l.propiedad_id = 1, 'Propia', IF(l.propiedad_id = 2, 'Terceros', IF(l.propiedad_id = 3, 'WEB', '-'))) AS 'propiedad',
					-- CONCAT(ROUND(( COUNT(IF(d.state='Won', d.id, NULL))/COUNT(d.id) * 100 ),2),'%') AS tickets_premiados,
					ROUND(( COUNT(IF(d.state IN ('Won','Returned'), d.id, NULL))/COUNT(d.id) * 100 ),2) AS tickets_premiados,
					lt.nombre AS tipo,
					SUM(d.apostado) AS total_apostado,
					-- SUM(d.ganado) AS total_ganado,
					SUM(IF(d.canal_de_venta_id IN (15,18,21), d.ganado, IF(d.state IN ('Won','Returned'), d.ganado, 0))) AS total_ganado,
					SUM(IF(d.canal_de_venta_id IN (15,18,21), d.ganado, IF(d.state IN ('Won','Returned'), IF(d.paid_day IS NOT NULL, d.ganado, NULL), NULL))) AS total_pagado,
					SUM(IF(d.canal_de_venta_id IN (15,18,21), 0, IF(d.state IN ('Won','Returned'), IF(d.paid_day IS NULL, d.ganado, NULL), NULL))) as por_pagar,
					IF(lqty.qty,lqty.qty,0) AS qty
				FROM tbl_transacciones_detalle d
				LEFT JOIN tbl_transacciones_repositorio r ON (r.at_unique_id = d.at_unique_id)
				LEFT JOIN tbl_locales l ON(l.id = d.local_id)
				LEFT JOIN tbl_local_tipo lt ON (lt.id = l.tipo_id)
				LEFT JOIN tbl_canales_venta cdv ON (cdv.id = d.canal_de_venta_id)
				LEFT JOIN tbl_personal_apt lp ON  (lp.id = l.asesor_id)
				LEFT JOIN tbl_local_qty lqty ON (lqty.local_id = d.local_id AND lqty.canal_de_venta_id = d.canal_de_venta_id)
				$filtro_where
				AND ( d.tipo = 1 OR d.tipo = 4 OR d.tipo = 5)
				-- AND (d.state != 'Returned' OR d.state IS NULL)
				-- AND (r.mannually_settled_user_name IS NULL)
				GROUP BY
					year ASC,
					month ASC,
					canal_de_venta_id ASC,
					local_id ASC
				-- ORDER BY 
				-- 	nombre ASC
			";
			$apuestas_query = $mysqli->query($apuestas_command);
			if($mysqli->error){
				print_r($mysqli->error);
				exit();
			}
			$return_info["time_query"] = number_format(microtime(true) - $microtime_init,2)." s";
			$return_info["memory_query"]=round(memory_get_usage()/1024/1024,2)." MB";

			while($apuestas_data = $apuestas_query->fetch_assoc()){
				$resumen[$apuestas_data["year"]][$apuestas_data["month"]][$apuestas_data["canal_de_venta_id"]][$apuestas_data["local_id"]]=$apuestas_data;
			}
			$return_info["time_while"] = number_format(microtime(true) - $microtime_init,2)." s";
			$return_info["memory_while"]=round(memory_get_usage()/1024/1024,2)." MB";


			$cashdesk_command = "
				SELECT
					YEAR(d.created) AS year,
					DATE_FORMAT(d.created,'%m') AS month,
					d.canal_de_venta_id,
					d.local_id,
					IF(l.administracion_tipo = 1,'No Operada',IF(l.administracion_tipo = 2, 'Operada', '-')) AS 'administracion',
					l.nombre AS nombre,
					l.asesor_id,
					lp.nombre AS asesor_nombre,
					IF(l.propiedad_id = 1, 'Propia', IF(l.propiedad_id = 2, 'Terceros', IF(l.propiedad_id = 3, 'WEB', '-'))) AS 'propiedad',
					cdv.codigo AS canal_de_venta,
					lt.nombre AS tipo,
					SUM(d.deposit) AS total_depositado_web,
					SUM(d.withdraw) AS total_retirado_web,
					IF(lqty.qty,lqty.qty,0) AS qty
				FROM tbl_transacciones_detalle d
				LEFT JOIN tbl_locales l ON(l.id = d.local_id)
				LEFT JOIN tbl_local_tipo lt ON (lt.id = l.tipo_id)
				LEFT JOIN tbl_canales_venta cdv ON (cdv.id = d.canal_de_venta_id)
				LEFT JOIN tbl_personal_apt lp ON  (lp.id = l.asesor_id)
				LEFT JOIN tbl_local_qty lqty ON (lqty.local_id = d.local_id AND lqty.canal_de_venta_id = d.canal_de_venta_id)
				$filtro_where
				AND ( d.tipo = 3)
				GROUP BY
					year ASC,
					month ASC,
					canal_de_venta_id ASC,
					local_id ASC
			";
			$cashdesk_query = $mysqli->query($cashdesk_command);
			if($mysqli->error){
				print_r($mysqli->error);
				exit();
			}
			$return_info["time_cashdesk_query"] = number_format(microtime(true) - $microtime_init,2)." s";
			$return_info["memory_cashdesk_query"]=round(memory_get_usage()/1024/1024,2)." MB";
			while($cashdesk_data = $cashdesk_query->fetch_assoc()){
				// if(array_key_exists(key, array))
				// $resumen[$cashdesk_data["year"]][$cashdesk_data["month"]][$cashdesk_data["canal_de_venta_id"]][$cashdesk_data["local_id"]]["total_depositado_web"]=$cashdesk_data["total_depositado_web"];
				// $resumen[$cashdesk_data["year"]][$cashdesk_data["month"]][$cashdesk_data["canal_de_venta_id"]][$cashdesk_data["local_id"]]["total_retirado_web"]=$cashdesk_data["total_retirado_web"];

				if(array_key_exists($cashdesk_data["year"], $resumen)){
					if(array_key_exists($cashdesk_data["month"], $resumen[$cashdesk_data["year"]])){
						if(array_key_exists($cashdesk_data["canal_de_venta_id"], $resumen[$cashdesk_data["year"]][$cashdesk_data["month"]])){
							if(array_key_exists($cashdesk_data["local_id"], $resumen[$cashdesk_data["year"]][$cashdesk_data["month"]][$cashdesk_data["canal_de_venta_id"]])){
								$resumen[$cashdesk_data["year"]][$cashdesk_data["month"]][$cashdesk_data["canal_de_venta_id"]][$cashdesk_data["local_id"]]=array_merge($resumen[$cashdesk_data["year"]][$cashdesk_data["month"]][$cashdesk_data["canal_de_venta_id"]][$cashdesk_data["local_id"]],$cashdesk_data);
							}else{
								$resumen[$cashdesk_data["year"]][$cashdesk_data["month"]][$cashdesk_data["canal_de_venta_id"]][$cashdesk_data["local_id"]]=$cashdesk_data;
							}
						}else{
							$resumen[$cashdesk_data["year"]][$cashdesk_data["month"]][$cashdesk_data["canal_de_venta_id"]][$cashdesk_data["local_id"]]=$cashdesk_data;
						}
					}else{
						$resumen[$cashdesk_data["year"]][$cashdesk_data["month"]][$cashdesk_data["canal_de_venta_id"]][$cashdesk_data["local_id"]]=$cashdesk_data;
					}
				}else{
					$resumen[$cashdesk_data["year"]][$cashdesk_data["month"]][$cashdesk_data["canal_de_venta_id"]][$cashdesk_data["local_id"]]=$cashdesk_data;
				}
				// $resumen[$cashdesk_data["year"]][$cashdesk_data["month"]][$cashdesk_data["canal_de_venta_id"]][$cashdesk_data["local_id"]]=array_merge($resumen[$cashdesk_data["year"]][$cashdesk_data["month"]][$cashdesk_data["canal_de_venta_id"]][$cashdesk_data["local_id"]],$cashdesk_data);
			}
			$return_info["time_cashdesk_while"] = number_format(microtime(true) - $microtime_init,2)." s";
			$return_info["memory_cashdesk_while"]=round(memory_get_usage()/1024/1024,2)." MB";


			$total_copy_me = [];
				// $total_copy_me["apuesta_x_ticket"]=0;
				// $total_copy_me["hold"]=0;
				// $total_copy_me["net_win"]=0;
				$total_copy_me["num_tickets"]=0;
				$total_copy_me["num_tickets_ganados"]=0;
				$total_copy_me["num_tickets_ganados_pagados"]=0;
				$total_copy_me["num_tickets_por_pagar"]=0;
				$total_copy_me["por_pagar"]=0;
				// $total_copy_me["tickets_premiados"]=0;
				$total_copy_me["total_apostado"]=0;
				$total_copy_me["total_depositado_web"]=0;
				$total_copy_me["total_ganado"]=0;
				$total_copy_me["total_pagado"]=0;
				$total_copy_me["total_retirado_web"]=0;

			foreach ($resumen as $year_key => $months) {
				$totales[$year_key]=[];
				$total_year = $total_copy_me;
				foreach ($months as $month_key => $cdv) {
					$totales[$year_key][$month_key]=[];
					$total_month = $total_copy_me;
					foreach ($cdv as $cdv_key => $locales) {
						$total_cdv = $total_copy_me;
						foreach ($locales as $local_id => $local_data) {
							foreach ($local_data as $data_key => $data_val) {
								if(array_key_exists($data_key, $total_copy_me)){
									$total_cdv[$data_key]+=$data_val;
									$total_month[$data_key]+=$data_val;
									$total_year[$data_key]+=$data_val;
								}
							}
						}
						if($total_cdv["num_tickets"]){
							$total_cdv["apuesta_x_ticket"]=($total_cdv["total_apostado"]/$total_cdv["num_tickets"]);
						}else{
							$total_cdv["apuesta_x_ticket"]=0;
						}
						$total_cdv["net_win"]=($total_cdv["total_apostado"] - $total_cdv["total_ganado"]);
						if($total_cdv["total_apostado"]){
							$total_cdv["hold"]=($total_cdv["net_win"] / $total_cdv["total_apostado"])*100;	
						}else{
							$total_cdv["hold"]=0;
						}
						
						if($total_cdv["num_tickets"]){
							$total_cdv["tickets_premiados"]=($total_cdv["num_tickets_ganados"] / $total_cdv["num_tickets"])*100;			
						}else{
							$total_cdv["tickets_premiados"]=0;
						}
						$totales[$year_key][$month_key][$cdv_key]=$total_cdv;
					}
					if($total_month["num_tickets"]){
						$total_month["apuesta_x_ticket"]=($total_month["total_apostado"]/$total_month["num_tickets"]);
					}else{
						$total_month["apuesta_x_ticket"]=0;
					}
					
					$total_month["net_win"]=($total_month["total_apostado"] - $total_month["total_ganado"]);
					if($total_month["total_apostado"]){
						$total_month["hold"]=($total_month["net_win"] / $total_month["total_apostado"])*100;
					}else{
						$total_month["hold"]=0;
					}
					if($total_month["num_tickets"]){
						$total_month["tickets_premiados"]=($total_month["num_tickets_ganados"] / $total_month["num_tickets"])*100;
					}else{
						$total_month["tickets_premiados"]=0;
					}
					$totales[$year_key][$month_key]["total"]=$total_month;
				}
				if($total_year["num_tickets"]){
					$total_year["apuesta_x_ticket"]=($total_year["total_apostado"]/$total_year["num_tickets"]);
				}else{
					$total_year["apuesta_x_ticket"]=0;
				}
				$total_year["net_win"]=($total_year["total_apostado"] - $total_year["total_ganado"]);
				if($total_year["total_apostado"]){
					$total_year["hold"]=($total_year["net_win"] / $total_year["total_apostado"])*100;
				}else{
					$total_year["hold"]=0;
				}
				if($total_year["num_tickets"]){
					$total_year["tickets_premiados"]=($total_year["num_tickets_ganados"] / $total_year["num_tickets"])*100;
				}else{
					$total_year["tickets_premiados"]=0;
				}
				$totales[$year_key]["total"]=$total_year;
			}
			$return_info["time_total_build"] = number_format(microtime(true) - $microtime_init,2)." s";
			$return_info["memory_total_build"]=round(memory_get_usage()/1024/1024,2)." MB";


			$format=[];
			$format["decimal"][]="total_apostado";
			$format["decimal"][]="hold";
			$format["decimal"][]="net_win";
			$format["decimal"][]="por_pagar";
			$format["decimal"][]="total_depositado_web";
			$format["decimal"][]="total_ganado";
			$format["decimal"][]="total_pagado";
			$format["decimal"][]="total_retirado_web";
			$format["decimal"][]="apuesta_x_ticket";
			$format["decimal"][]="tickets_premiados";
			$format["nodecimal"][]="num_tickets";
			$format["nodecimal"][]="num_tickets_ganados";
			$format["nodecimal"][]="num_tickets_ganados_pagados";
			$format["nodecimal"][]="num_tickets_por_pagar";
			$format["percent"][]="hold";
			$format["percent"][]="tickets_premiados";


			foreach ($resumen as $year_key => $months) {
				foreach ($months as $month_key => $cdv) {
					foreach ($cdv as $cdv_key => $locales){
						foreach ($locales as $local_id => $local_data){
							foreach ($local_data as $data_key => $data_val) {
								if(in_array($data_key, $format["decimal"])){
									$decimal = 2;
									$resumen[$year_key][$month_key][$cdv_key][$local_id][$data_key]=round($resumen[$year_key][$month_key][$cdv_key][$local_id][$data_key],$decimal);
								}
								if(in_array($data_key, $format["nodecimal"])){
									$decimal = 0;
									$resumen[$year_key][$month_key][$cdv_key][$local_id][$data_key]=round($resumen[$year_key][$month_key][$cdv_key][$local_id][$data_key],$decimal);
								}
								if(in_array($data_key, $format["percent"])){
									$resumen[$year_key][$month_key][$cdv_key][$local_id][$data_key]=$resumen[$year_key][$month_key][$cdv_key][$local_id][$data_key]."%";
								}
							}
						}
					}
				}
			}
			foreach ($totales as $year_key => $months) {
				foreach ($months as $month_key => $cdv) {
					foreach ($cdv as $cdv_key => $locales){
						if($month_key=="total"){
							$decimal = 0;
							if(in_array($cdv_key, $format["decimal"])){
								$decimal=2;
							}
							$totales[$year_key][$month_key][$cdv_key]=round($totales[$year_key][$month_key][$cdv_key],$decimal);
							if(in_array($cdv_key, $format["percent"])){
								$totales[$year_key][$month_key][$cdv_key]=$totales[$year_key][$month_key][$cdv_key]."%";
							}
						}else{
							foreach ($locales as $local_id => $local_data){
								$decimal = 0;
								if(in_array($local_id, $format["decimal"])){
									$decimal=2;
								}
								$totales[$year_key][$month_key][$cdv_key][$local_id]=round($totales[$year_key][$month_key][$cdv_key][$local_id],$decimal);
								if(in_array($local_id, $format["percent"])){
									$totales[$year_key][$month_key][$cdv_key][$local_id]=$totales[$year_key][$month_key][$cdv_key][$local_id]."%";
								}
							}
						}
					}
				}
			}


			$return["resumen"]=$resumen;
			$return["totales"]=$totales;

		/******************************* fin data api ****************************************/

		/************************************data***********************************/

			$cdv_nombre = [];
			$sql_command = "SELECT id,nombre FROM tbl_canales_venta";
			$sql_query = $mysqli->query($sql_command);
			while($itm=$sql_query->fetch_assoc()){
				$cdv_nombre[$itm["id"]] = $itm["nombre"];
			}

			$l_nombre = []; 
			$sql_command = "SELECT id,nombre FROM tbl_locales";
			$sql_query = $mysqli->query($sql_command);
			while($itm=$sql_query->fetch_assoc()){
				$l_nombre[$itm["id"]] = $itm["nombre"];
			}

			//Agregar a resumen canal 
			$year_x_month_cdv_l = [];
			$canales = [];
			$locales = [];
			$locales_x_canales = [];

			$detalles_x_local = [];
			foreach ($return["resumen"] as $index_year => $month_value) {
				foreach ($month_value as $index_month => $value_cdv) {
					foreach ($value_cdv as $index_cdv => $value_local) {
						foreach ($value_local as $index_local => $local_detalles) {
							$locales_x_canales[$index_cdv][$index_local] = $index_local;
							$canales[$index_cdv] = $index_cdv;
							$locales[$index_local] = $index_local;
							$year_x_month_cdv_l[$index_year][$index_month][$index_cdv][$index_local] = $index_local;
							$detalles_x_local[$index_year."".$index_month."".$index_cdv."".$index_local]= $local_detalles;
						}	
					}
				}
			}
			$cdv = array_unique($canales);
			$l = array_unique($locales);
			foreach ($return["resumen"] as $index_year => $month_value) {
				foreach ($month_value as $index_month => $value_cdv) {
					foreach ($value_cdv as $index_cdv => $value_local) {
						foreach ($value_local as $index_local => $local_detalles) {
							foreach ($cdv as $indexcdv => $valuecdv) {
								if (in_array($valuecdv,$value_cdv)) {
								}else{
									foreach ($locales_x_canales as $cdv_index => $locales_value) {
										foreach ($locales as $indexlocal => $local) {
											if (in_array($local, $locales_value)) {
												if(!empty($detalles_x_local[$index_year."".$index_month."".$cdv_index."".$local])){
													$return["resumen"][$index_year][$index_month][$cdv_index][$local] = $detalles_x_local[$index_year."".$index_month."".$cdv_index."".$local];
												}else{
													if (!empty($cdv_nombre[$cdv_index]) && !empty($l_nombre[$local])) {
														$return["resumen"][$index_year][$index_month][$cdv_index][$local] = [
																				"year" => $index_year,
																				"month"=> $index_month,
																				"canal_de_venta_id"=> $cdv_index,
																				"local_id" => $local,
																				"administracion"=> $local_detalles["administracion"],
																				"apuesta_x_ticket"=> "",
																				"canal_de_venta"=> $cdv_nombre[$cdv_index],
																				"fecha"=> "",
																				"hold"=> "",
																				"net_win"=> "",
																				"nombre"=> $l_nombre[$local],
																				"asesor_id"=> $local_detalles["asesor_id"],
																				"asesor_nombre"=> $local_detalles["asesor_nombre"],
																				"num_tickets"=> "",
																				"num_tickets_ganados"=> "",
																				"num_tickets_ganados_pagados"=> "",
																				"num_tickets_por_pagar"=> "",
																				"propiedad"=> $local_detalles["propiedad"],
																				"tickets_premiados" => "",
																				"tipo" => $local_detalles["tipo"],
																				"total_apostado" => "",
																				"total_ganado" => "",
																				"total_pagado" => "",
																				"por_pagar" => "",
																				"qty" => $local_detalles["qty"]
																			];
													}

								
												}
												
											}
										}
									}
								}
							}
						}	
					}
				}
			}	
			//Agregar a totales canal
			$year_x_month_cdv_l_totales = [];
			$canales_totales = [];
			foreach ($return["totales"] as $index_year => $month_value) {
				foreach ($month_value as $index_month => $value_cdv) {
					if ($index_month=="total") {
					}else{			
						foreach ($value_cdv as $index_cdv => $cdv_detalles) {
							if ($index_cdv=="total") {
							}else{
								$canales_totales[$index_cdv] = $index_cdv;
								$year_x_month_cdv_l_totales[$index_year][$index_month][$index_cdv] = $index_cdv;
								$detalles_x_local_totales[$index_year."".$index_month."".$index_cdv]= $local_detalles;
							}	
						}
					}
				}
			}
			$cdv_totales = array_unique($canales_totales);
			foreach ($return["totales"] as $index_year => $month_value) {
				foreach ($month_value as $index_month => $value_cdv) {
					if ($index_month=="total") {
					}else{					
						foreach ($value_cdv as $index_cdv => $cdv_detalles) {
							if ($index_cdv=="total") {
							}else{		
									foreach ($cdv_totales as $indexcdv => $valuecdv) {
										if (array_key_exists($valuecdv,$value_cdv)) {
										}else{	
											if(!empty($detalles_x_local_totales[$index_year."".$index_month."".$cdv_index])){
												if (!empty($detalles_x_local[$index_year."".$index_month."".$cdv_index])) {
													$return["totales"][$index_year][$index_month][$cdv_index] = $detalles_x_local[$index_year."".$index_month."".$cdv_index];
												}

											}else{

												$return["totales"][$index_year][$index_month][$cdv_index] = [
																"apuesta_x_ticket"=>"",
																"hold"=>"",
																"net_win"=>"",
																"num_tickets"=>"",
																"num_tickets_ganados"=>"",
																"num_tickets_ganados_pagados"=>"",
																"num_tickets_por_pagar"=>"",
																"por_pagar"=>"",
																"tickets_premiados"=>"",
																"total_apostado"=>"",
																"total_depositado_web"=>"",
																"total_ganado"=>"",
																"total_pagado"=>"",
																"total_retirado_web"=>""
															];
							
											}
											
										}


									}
							}	
						}
					}
				}
			}


			$totales_13_x_year = [];
			$totales_x_year = [];
			$total_13_merge = [];

			foreach ($return["totales"] as $year_index => $months_data) {
				foreach ($months_data as $month_index => $csdv_data) {
					if ($month_index=="total") {

						$total_x_year = []; 
						$total_x_year["administracion"]="-";
						$total_x_year["asesor_id"]="-";
						$total_x_year["asesor_nombre"]="-";
						$total_x_year["canal_de_venta"]="Total";
						$total_x_year["canal_de_venta_id"]="13";
						$total_x_year["fecha"]="-";
						$total_x_year["local_id"]="13";
						$total_x_year["nombre"]="-";
						$total_x_year["propiedad"]="-";
						$total_x_year["qty"]="-";
						$total_x_year["tipo"]="-";
						$total_13_merge = array_merge($csdv_data,$total_x_year);

						$totales_x_year[$year_index] = [];
						$totales_x_year[$year_index]["13"] = [];
						$totales_x_year[$year_index]["13"]["13"] = [];
						$totales_x_year[$year_index]["13"]["13"]["13"] = $total_13_merge;
						$totales_13_x_year[$year_index]["13"]["total"] = $total_13_merge;
					}else{
							$total_13 = [];
							$total_13["administracion"]="-";
							$total_13["apuesta_x_ticket"]="-";
							$total_13["hold"]="-";
							$total_13["net_win"]="-";
							$total_13["num_tickets"]="-";
							$total_13["num_tickets_ganados"]="-";
							$total_13["num_tickets_ganados_pagados"]="-";
							$total_13["num_tickets_por_pagar"]="-";
							$total_13["por_pagar"]="-";
							$total_13["tickets_premiados"]="-";
							$total_13["total_apostado"]="-";
							$total_13["total_depositado_web"]="-";
							$total_13["total_ganado"]="-";
							$total_13["total_pagado"]="-";
							$total_13["total_retirado_web"]="-";
							$total_13["administracion"]="-";
							$total_13["asesor_id"]="-";
							$total_13["asesor_nombre"]="-";
							$total_13["canal_de_venta"]="Total";
							$total_13["canal_de_venta_id"]="13";
							$total_13["fecha"]="-";
							$total_13["local_id"]="13";
							$total_13["month"]="13";
							$total_13["nombre"]="-";
							$total_13["propiedad"]="-";
							$total_13["qty"]="-";
							$total_13["tipo"]="-";
							$total_13["year"]="-";

							$totales_13_x_year[$year_index] = [];
							$totales_13_x_year[$year_index]["13"] = [];
							$totales_13_x_year[$year_index]["13"]["13"] = $total_13;
					}
				}
			}

			$resumen = [];
			foreach ($return["resumen"] as $year_index => $months_data) {
				$return["resumen"][$year_index]["13"]=$totales_x_year[$year_index]["13"];
			}
			$totales = [];
			foreach ($return["totales"] as $year_index => $months_data) {
				$return["totales"][$year_index]["13"]=$totales_13_x_year[$year_index]["13"];
			}


			$nombre_mes = [];
			$nombre_mes[1]="Enero"; 
			$nombre_mes[2]="Febrero"; 
			$nombre_mes[3]="Marzo"; 
			$nombre_mes[4]="Abril"; 		
			$nombre_mes[5]="Mayo"; 
			$nombre_mes[6]="Junio"; 
			$nombre_mes[7]="Julio"; 
			$nombre_mes[8]="Agosto"; 
			$nombre_mes[9]="Setiembre";
			$nombre_mes[10]="Octubre"; 
			$nombre_mes[11]="Noviembre";	
			$nombre_mes[12]="Diciembre";	
			$nombre_mes[13]="Total";

			$cdv = [];
			$sql_selected = "SELECT id,codigo FROM tbl_canales_venta";
			$result_selected = $mysqli->query($sql_selected);
			while($row_selected = $result_selected->fetch_assoc()) {
				$cdv[$row_selected['id']] = $row_selected["codigo"];
			} 				

			function sec_reporte_apuestas_leer_meses($periodo){
				GLOBAL $nombre_mes; 
				$mes = explode("_",$periodo);
				return $mes[0]." ".$nombre_mes[(int)$mes[1]];
			}


			$array_opciones_mes = [
				1=>"Dinero Apostado",
				2=>"Dinero Ganado",
				3=>"Dinero Pagado",
				4=>"Dinero por Pagar",
				5=>"Net Win T",
				6=>"Hold%",
				7=>"Tickets Emitidos",
				8=>"Tickets Ganados",
				9=>"Tickets Pagados",
				10=>"Tickets por Pagar",
				11=>"Apuesta x Ticket",
				12=>"% Ticket Premiados",
				13=>"Dinero Depositado Web",
				14=>"Dinero Retirado Web"
			];

			$cols = [];
				$cols["total_apostado"]="Dinero Apostado";
				$cols["total_ganado"]="Dinero Ganado";
				$cols["total_pagado"]="Dinero Pagado";
				$cols["por_pagar"]="Dinero por Pagar";					
				$cols["net_win"]="Net Win T";
				$cols["hold"]="Hold%";
				$cols["num_tickets"]="Tickets Emitidos ";
				$cols["num_tickets_ganados"]="Tickets Ganados";	
				$cols["num_tickets_ganados_pagados"]="Tickets Pagados";
				$cols["num_tickets_por_pagar"]="Tickets por Pagar";
				$cols["apuesta_x_ticket"]="Apuesta x Ticket";
				$cols["tickets_premiados"]="% Ticket Premiados";
				$cols["total_depositado_web"]="Dinero Depositado Web";
				$cols["total_retirado_web"]="Dinero Retirado Web";

			$period_inicio = false;
			$array_anios_meses_thead = [];
			$array_anios_meses_texto_thead = [];
			$array_anios_meses_texto_thead_event = [];
			$array_periodo_reorder = [];
			$period_arr = [];		
			$periodo_index = "";
			foreach ($return["resumen"] as $year_index => $year_data) {
				foreach ($year_data as $month_year => $month_data) {
					$periodo_index = $year_index."".$month_year;
					$array_anios_meses_thead[$periodo_index] = $year_index."".$month_year;
					$array_anios_meses_texto_thead[$periodo_index] = $year_index."_".$month_year;
					$array_anios_meses_texto_thead_event[$periodo_index] = $year_index."".$month_year;
					$period_arr[$year_index."".$month_year]=true;
				}
			}
			sort($array_anios_meses_thead);
			if (!empty($array_anios_meses_thead[0]) && isset($array_anios_meses_thead[0])) {
				$periodo_inicio = $array_anios_meses_thead[0];
			}
			// print $periodo_inicio;

			sort($array_anios_meses_texto_thead_event);
			sort($array_anios_meses_texto_thead);


			$new_obj = [];
			foreach ($return["resumen"] as $year_index => $year_value) {
				foreach ($year_value as $month_index => $month_value) {
					foreach ($month_value as $cdv_id => $cdv_value) {
						foreach ($cdv_value as $local_id => $local_value) {
							$local = [];
							$local = $local_value;
							$local["year"] = $year_index;
							$local["month"] = $month_index;
							$local["period"] = $year_index."".$month_index;
							array_push($new_obj, $local);
						}
					}
				}		
			}



			$obj_by_period = [];
			foreach ($new_obj as $n_in => $n_val) {
				if(!array_key_exists($n_val["period"], $obj_by_period)){
					$obj_by_period[$n_val["period"]]=[];
				}
				$obj_by_period[$n_val["period"]][$n_val["local_id"]."".$n_val["canal_de_venta_id"]]=$n_val;
			}


			$totales_array = [];
			foreach ($return["totales"] as $year_index => $year_value) {
				foreach ($year_value as $month_index => $month_value) {
					foreach ($month_value as $cdv_id => $total_local_data) {
						if ($cdv_id!="total") {
							if ($month_index!="total") {
								$total = [];
								$total = $total_local_data;
								$total["year"] = $year_index;
								$total["month"] = $month_index;
								$total["period"] = $year_index."".$month_index;
								$total["canal_de_venta_id"] = $cdv_id;
								array_push($totales_array, $total);
							}
						}
					}
				}		
			}


			$obj_total_by_period = [];
			foreach ($totales_array as $n_in => $n_val) {
				if(!array_key_exists($n_val["period"], $obj_total_by_period)){
					$obj_total_by_period[$n_val["period"]]=[];
				}
				$obj_total_by_period[$n_val["period"]][$n_val["canal_de_venta_id"]]=$n_val;
			}

			$super_total_array = [];
			foreach ($return["totales"] as $year_index => $months_data) {
				foreach ($months_data as $month_index => $csdv_data) {
					foreach ($csdv_data as $cdv_id => $total_local_data) {
						if ($cdv_id=="total") {
							if ($month_index!="total" ) {
								$super_total = [];
								$super_total = $total_local_data;
								$super_total["year"] = $year_index;
								$super_total["month"] = $month_index;
								$super_total["period"] = $year_index."".$month_index; 
								$super_total["canal_de_venta_id"]= $cdv_id;
								array_push($super_total_array, $super_total);
							}
						}
					}
				}
			}

			$obj_super_total_by_period = [];
			foreach ($super_total_array as $n_in => $n_val) {
				if(!array_key_exists($n_val["period"], $obj_super_total_by_period)){
					$obj_super_total_by_period[$n_val["period"]]=[];
				}
				$obj_super_total_by_period[$n_val["period"]][$n_val["canal_de_venta_id"]]=$n_val;
			}


		/********************************fin data***********************************/

		$l = [];
		$cantidad_de_columnas_a_crear=1000; 
		$contador=0; 
		$letra='A'; 
		while($contador<=$cantidad_de_columnas_a_crear){ 
			$l[$contador] =  $letra;
			$contador++; 
			$letra++; 
		} 
		$titulo_reporte_apuestas ="REPORTE APUESTAS ".date("d-m-Y",strtotime($_POST["filtro"]["fecha_inicio"]))." AL ".date("d-m-Y",strtotime($_POST["filtro"]["fecha_fin"]));
		$titulo_file_reporte_apuestas = "reporte_apuestas_".date("d-m-Y",strtotime($_POST["filtro"]["fecha_inicio"]))."_al_".date("d-m-Y",strtotime($_POST["filtro"]["fecha_fin"]))."_".date("Ymdhis");
		$array_datos_sistemas = [];

		if (isset($titulo_file_reporte_apuestas)) {
			require_once '../phpexcel/classes/PHPExcel.php';
			$objPHPExcel = new PHPExcel();
			$estiloTituloReporte = new PHPExcel_Style();

			$estiloTituloReporte = [
				'font' => [
					'name'      => 'Verdana',
					'bold'      => true,
					'italic'    => false,
					'strike'    => false,
					'size' =>16,
						'color'     => [
							'rgb' => '333333'
						]
				],
				'fill' => [
					'type'  => PHPExcel_Style_Fill::FILL_SOLID,
					'color' => ['argb' => 'FFFFFF']
				],
				'alignment' =>  [
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
						'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
						'rotation'   => 0,
						'wrap'          => TRUE
				],
				'borders' => [
					'allborders' => [
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => ['rgb' => 'dddddd']
					]
				]				
			];	
			
			$estilotitulosgenerales = new PHPExcel_Style();
			$estilotitulosgenerales = [
				'font' => [
					'name'      => 'Verdana',
					'bold'      => true,
					'italic'    => false,
					'strike'    => false,
					'size' =>10,
						'color'     => [
							'rgb' => 'FFFFFF'
						]
				],				
				'alignment' =>  [
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
						'rotation'   => 0,
						'wrap'          => TRUE
				],
				'fill'  => [
					'type'      => PHPExcel_Style_Fill::FILL_SOLID,
					'rotation'   => 90,
					'startcolor' => [
						'rgb' => '3777D9'
					],
					'endcolor'   => [
						'argb' => '3777D9'
					]
				],
				'borders' => [
					'allborders' => [
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => ['rgb' => 'dddddd']
					]
				]								
			];


			$estiloYears = new PHPExcel_Style();
			$estiloYears = [
				'font' => [
					'name'      => 'Verdana',
					'bold'      => true,
					'italic'    => false,
					'strike'    => false,
					'size' =>10,
						'color'     => [
							'rgb' => 'FFFFFF'
						]
				],				
				'alignment' =>  [
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
						'rotation'   => 0,
						'wrap'          => TRUE
				],
				'fill'  => [
					'type'      => PHPExcel_Style_Fill::FILL_SOLID,
					'rotation'   => 90,
					'startcolor' => [
						'rgb' => '3777D9'
					],
					'endcolor'   => [
						'argb' => '3777D9'
					]
				],
				'borders' => [
					'allborders' => [
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => ['rgb' => 'dddddd']
					]
				]								
			];
	

			$estiloMonths = new PHPExcel_Style();
			$estiloMonths = [
				'font' => [
					'name'      => 'Verdana',
					'bold'      => true,
					'italic'    => false,
					'strike'    => false,
					'size' =>10,
						'color'     => [
							'rgb' => '333333'
						]
				],				
				'alignment' =>  [
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
						'rotation'   => 0,
						'wrap'          => TRUE
				],
				'fill'  => [
					'type'      => PHPExcel_Style_Fill::FILL_SOLID,
					'rotation'   => 90,
					'startcolor' => [
						'rgb' => 'CECAC9'
					],
					'endcolor'   => [
						'argb' => 'CECAC9'
					]
				],
				'borders' => [
					'allborders' => [
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => ['rgb' => 'dddddd']
					]
				]								
			];			


			$estiloOpcionesDineroApostado = new PHPExcel_Style();
			$estiloOpcionesDineroApostado = [
				'font' => [
					'name'      => 'Verdana',
					'bold'      => true,
					'italic'    => false,
					'strike'    => false,
					'size' =>10,
						'color'     => [
							'rgb' => '333333'
						]
				],				
				'alignment' =>  [
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
						'rotation'   => 0,
						'wrap'          => TRUE
				],
				'fill'  => [
					'type'      => PHPExcel_Style_Fill::FILL_SOLID,
					'rotation'   => 90,
					'startcolor' => [
						'rgb' => '9BCB91'
					],
					'endcolor'   => [
						'argb' => '9BCB91'
					]
				],
				'borders' => [
					'allborders' => [
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => ['rgb' => 'dddddd']
					]
				]								
			];

			$estiloOpcionesDineroGanado = new PHPExcel_Style();
			$estiloOpcionesDineroGanado = [
				'font' => [
					'name'      => 'Verdana',
					'bold'      => true,
					'italic'    => false,
					'strike'    => false,
					'size' =>10,
						'color'     => [
							'rgb' => '333333'
						]
				],				
				'alignment' =>  [
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
						'rotation'   => 0,
						'wrap'          => TRUE
				],
				'fill'  => [
					'type'      => PHPExcel_Style_Fill::FILL_SOLID,
					'rotation'   => 90,
					'startcolor' => [
						'rgb' => '9BCB91'
					],
					'endcolor'   => [
						'argb' => '9BCB91'
					]
				],
				'borders' => [
					'allborders' => [
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => ['rgb' => 'dddddd']
					]
				]								
			];

			$estiloOpcionesDineroPagado = new PHPExcel_Style();
			$estiloOpcionesDineroPagado = [
				'font' => [
					'name'      => 'Verdana',
					'bold'      => true,
					'italic'    => false,
					'strike'    => false,
					'size' =>10,
						'color'     => [
							'rgb' => '333333'
						]
				],				
				'alignment' =>  [
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
						'rotation'   => 0,
						'wrap'          => TRUE
				],
				'fill'  => [
					'type'      => PHPExcel_Style_Fill::FILL_SOLID,
					'rotation'   => 90,
					'startcolor' => [
						'rgb' => '9BCB91'
					],
					'endcolor'   => [
						'argb' => '9BCB91'
					]
				],
				'borders' => [
					'allborders' => [
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => ['rgb' => 'dddddd']
					]
				]								
			];
			$estiloOpcionesDineroPorPagar = new PHPExcel_Style();
			$estiloOpcionesDineroPorPagar = [
				'font' => [
					'name'      => 'Verdana',
					'bold'      => true,
					'italic'    => false,
					'strike'    => false,
					'size' =>10,
						'color'     => [
							'rgb' => '333333'
						]
				],				
				'alignment' =>  [
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
						'rotation'   => 0,
						'wrap'          => TRUE
				],
				'fill'  => [
					'type'      => PHPExcel_Style_Fill::FILL_SOLID,
					'rotation'   => 90,
					'startcolor' => [
						'rgb' => '9BCB91'
					],
					'endcolor'   => [
						'argb' => '9BCB91'
					]
				],
				'borders' => [
					'allborders' => [
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => ['rgb' => 'dddddd']
					]
				]								
			];

			$estiloOpcionesNetWinT = new PHPExcel_Style();
			$estiloOpcionesNetWinT = [
				'font' => [
					'name'      => 'Verdana',
					'bold'      => true,
					'italic'    => false,
					'strike'    => false,
					'size' =>10,
						'color'     => [
							'rgb' => '333333'
						]
				],				
				'alignment' =>  [
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
						'rotation'   => 0,
						'wrap'          => TRUE
				],
				'fill'  => [
					'type'      => PHPExcel_Style_Fill::FILL_SOLID,
					'rotation'   => 90,
					'startcolor' => [
						'rgb' => 'AEB0AD'
					],
					'endcolor'   => [
						'argb' => 'AEB0AD'
					]
				],
				'borders' => [
					'allborders' => [
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => ['rgb' => 'dddddd']
					]
				]								
			];

			$estiloOpcionesHold = new PHPExcel_Style();
			$estiloOpcionesHold = [
				'font' => [
					'name'      => 'Verdana',
					'bold'      => true,
					'italic'    => false,
					'strike'    => false,
					'size' =>10,
						'color'     => [
							'rgb' => '333333'
						]
				],				
				'alignment' =>  [
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
						'rotation'   => 0,
						'wrap'          => TRUE
				],
				'fill'  => [
					'type'      => PHPExcel_Style_Fill::FILL_SOLID,
					'rotation'   => 90,
					'startcolor' => [
						'rgb' => 'AEB0AD'
					],
					'endcolor'   => [
						'argb' => 'AEB0AD'
					]
				],
				'borders' => [
					'allborders' => [
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => ['rgb' => 'dddddd']
					]
				]								
			];

			$estiloOpcionesTicketsEmitidos = new PHPExcel_Style();
			$estiloOpcionesTicketsEmitidos = [
				'font' => [
					'name'      => 'Verdana',
					'bold'      => true,
					'italic'    => false,
					'strike'    => false,
					'size' =>10,
						'color'     => [
							'rgb' => '333333'
						]
				],				
				'alignment' =>  [
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
						'rotation'   => 0,
						'wrap'          => TRUE
				],
				'fill'  => [
					'type'      => PHPExcel_Style_Fill::FILL_SOLID,
					'rotation'   => 90,
					'startcolor' => [
						'rgb' => '8FB9FB'
					],
					'endcolor'   => [
						'argb' => '8FB9FB'
					]
				],
				'borders' => [
					'allborders' => [
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => ['rgb' => 'dddddd']
					]
				]								
			];

			$estiloOpcionesTicketsGanados = new PHPExcel_Style();
			$estiloOpcionesTicketsGanados = [
				'font' => [
					'name'      => 'Verdana',
					'bold'      => true,
					'italic'    => false,
					'strike'    => false,
					'size' =>10,
						'color'     => [
							'rgb' => '333333'
						]
				],				
				'alignment' =>  [
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
						'rotation'   => 0,
						'wrap'          => TRUE
				],
				'fill'  => [
					'type'      => PHPExcel_Style_Fill::FILL_SOLID,
					'rotation'   => 90,
					'startcolor' => [
						'rgb' => '8FB9FB'
					],
					'endcolor'   => [
						'argb' => '8FB9FB'
					]
				],
				'borders' => [
					'allborders' => [
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => ['rgb' => 'dddddd']
					]
				]								
			];

			$estiloOpcionesTicketsPagados = new PHPExcel_Style();
			$estiloOpcionesTicketsPagados = [
				'font' => [
					'name'      => 'Verdana',
					'bold'      => true,
					'italic'    => false,
					'strike'    => false,
					'size' =>10,
						'color'     => [
							'rgb' => '333333'
						]
				],				
				'alignment' =>  [
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
						'rotation'   => 0,
						'wrap'          => TRUE
				],
				'fill'  => [
					'type'      => PHPExcel_Style_Fill::FILL_SOLID,
					'rotation'   => 90,
					'startcolor' => [
						'rgb' => '8FB9FB'
					],
					'endcolor'   => [
						'argb' => '8FB9FB'
					]
				],
				'borders' => [
					'allborders' => [
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => ['rgb' => 'dddddd']
					]
				]								
			];

			$estiloOpcionesTicketsPorPagar = new PHPExcel_Style();
			$estiloOpcionesTicketsPorPagar = [
				'font' => [
					'name'      => 'Verdana',
					'bold'      => true,
					'italic'    => false,
					'strike'    => false,
					'size' =>10,
						'color'     => [
							'rgb' => '333333'
						]
				],				
				'alignment' =>  [
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
						'rotation'   => 0,
						'wrap'          => TRUE
				],
				'fill'  => [
					'type'      => PHPExcel_Style_Fill::FILL_SOLID,
					'rotation'   => 90,
					'startcolor' => [
						'rgb' => '8FB9FB'
					],
					'endcolor'   => [
						'argb' => '8FB9FB'
					]
				],
				'borders' => [
					'allborders' => [
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => ['rgb' => 'dddddd']
					]
				]								
			];

			$estiloOpcionesTicketsApuestaXTickets = new PHPExcel_Style();
			$estiloOpcionesTicketsApuestaXTickets = [
				'font' => [
					'name'      => 'Verdana',
					'bold'      => true,
					'italic'    => false,
					'strike'    => false,
					'size' =>10,
						'color'     => [
							'rgb' => '333333'
						]
				],				
				'alignment' =>  [
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
						'rotation'   => 0,
						'wrap'          => TRUE
				],
				'fill'  => [
					'type'      => PHPExcel_Style_Fill::FILL_SOLID,
					'rotation'   => 90,
					'startcolor' => [
						'rgb' => '8FB9FB'
					],
					'endcolor'   => [
						'argb' => '8FB9FB'
					]
				],
				'borders' => [
					'allborders' => [
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => ['rgb' => 'dddddd']
					]
				]								
			];

			$estiloOpcionesPorcentajeTicketsPremiados = new PHPExcel_Style();
			$estiloOpcionesPorcentajeTicketsPremiados = [
				'font' => [
					'name'      => 'Verdana',
					'bold'      => true,
					'italic'    => false,
					'strike'    => false,
					'size' =>10,
						'color'     => [
							'rgb' => '333333'
						]
				],				
				'alignment' =>  [
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
						'rotation'   => 0,
						'wrap'          => TRUE
				],
				'fill'  => [
					'type'      => PHPExcel_Style_Fill::FILL_SOLID,
					'rotation'   => 90,
					'startcolor' => [
						'rgb' => '8FB9FB'
					],
					'endcolor'   => [
						'argb' => '8FB9FB'
					]
				],
				'borders' => [
					'allborders' => [
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => ['rgb' => 'dddddd']
					]
				]								
			];			

			$estiloOpcionesDineroDepositadoWeb = new PHPExcel_Style();
			$estiloOpcionesDineroDepositadoWeb = [
				'font' => [
					'name'      => 'Verdana',
					'bold'      => true,
					'italic'    => false,
					'strike'    => false,
					'size' =>10,
						'color'     => [
							'rgb' => '333333'
						]
				],				
				'alignment' =>  [
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
						'rotation'   => 0,
						'wrap'          => TRUE
				],
				'fill'  => [
					'type'      => PHPExcel_Style_Fill::FILL_SOLID,
					'rotation'   => 90,
					'startcolor' => [
						'rgb' => 'C1EFFC'
					],
					'endcolor'   => [
						'argb' => 'C1EFFC'
					]
				],
				'borders' => [
					'allborders' => [
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => ['rgb' => 'dddddd']
					]
				]								
			];

			$estiloOpcionesDineroRetiradoWeb = new PHPExcel_Style();
			$estiloOpcionesDineroRetiradoWeb = [
				'font' => [
					'name'      => 'Verdana',
					'bold'      => true,
					'italic'    => false,
					'strike'    => false,
					'size' =>10,
						'color'     => [
							'rgb' => '333333'
						]
				],				
				'alignment' =>  [
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
						'rotation'   => 0,
						'wrap'          => TRUE
				],
				'fill'  => [
					'type'      => PHPExcel_Style_Fill::FILL_SOLID,
					'rotation'   => 90,
					'startcolor' => [
						'rgb' => 'C1EFFC'
					],
					'endcolor'   => [
						'argb' => 'C1EFFC'
					]
				],
				'borders' => [
					'allborders' => [
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => ['rgb' => 'dddddd']
					]
				]								
			];

			$estiloTotales = new PHPExcel_Style();
			$estiloTotales = [
				'font' => [
					'name'      => 'Verdana',
					'bold'      => false,
					'italic'    => false,
					'strike'    => false,
					'size' =>11,
						'color'     => [
							'rgb' => '8a6d3b'
						]
				],				
				'fill'  => [
					'type'      => PHPExcel_Style_Fill::FILL_SOLID,
					'rotation'   => 90,
					'startcolor' => [
						'rgb' => 'fcf8e3'
					],
					'endcolor'   => [
						'argb' => 'fcf8e3'
					]
				],
				'borders' => [
					'allborders' => [
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => ['rgb' => 'dddddd']
					]
				]								
			];

			$estiloOpcionesMonthGeneralNWH = new PHPExcel_Style();
			$estiloOpcionesMonthGeneralNWH = [
				'font' => [
					'name'      => 'Verdana',
					'bold'      => true,
					'italic'    => false,
					'strike'    => false,
					'size' =>10,
						'color'     => [
							'rgb' => '333333'
						]
				],				
				'alignment' =>  [
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
						'rotation'   => 0,
						'wrap'          => TRUE
				],
				'fill'  => [
					'type'      => PHPExcel_Style_Fill::FILL_SOLID,
					'rotation'   => 90,
					'startcolor' => [
						'rgb' => 'AEB0AD'
					],
					'endcolor'   => [
						'argb' => 'AEB0AD'
					]
				],
				'borders' => [
					'allborders' => [
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => ['rgb' => 'dddddd']
					]
				]								
			];

			//Estilos condicionales numeros negativos/ menor que cero color rojo
			$objConditionalNegativeNumber = new PHPExcel_Style_Conditional();
			$objConditionalNegativeNumber->setConditionType(PHPExcel_Style_Conditional::CONDITION_CELLIS);
			$objConditionalNegativeNumber->setOperatorType(PHPExcel_Style_Conditional::OPERATOR_LESSTHAN);
			$objConditionalNegativeNumber->addCondition('0');
			$objConditionalNegativeNumber->getStyle()->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
			$objConditionalNegativeNumber->getStyle()->getFont()->setBold(false);
	
			$p = 2;
			$pp = 3;
			$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue($l[0].$p,"Canal de Venta")			
			->setCellValue($l[1].$p,"Nombre de Local")
			->setCellValue($l[2].$p,"Tipo")
			->setCellValue($l[3].$p,"Agente")
			->setCellValue($l[4].$p,"Tipo Admin.")                                                    
			->setCellValue($l[5].$p,"Tipo de Punto")
			->setCellValue($l[6].$p,"QTY");

			$array_merge_years= [];
			$count_inicial = 7;
			$count_final = 0;
			$rows = 2;
			foreach ($return["resumen"] as $year => $value) {
				$count_final = ($count_inicial+count($value)*14)-1;
				$array_merge_years[$year]["inicial"] = 	["0"=>$count_inicial,"1"=>$rows,"2"=>$year];
				$array_merge_years[$year]["final"] = ["0"=>$count_final,"1"=>$rows,"2"=>$year];
				$array_merge_years[$year]["merge"] = $l[$count_inicial]."".$rows.":".$l[$count_final]."".$rows;
				$count_inicial=$count_final+1;				
			}

			$array_amount_months_per_year= [];
			$count = 7;
			foreach ($return["resumen"] as $year => $year_value) {
					$array_amount_months_per_year[$year] = count($year_value);
			}


			$array_years= [];
			
			$count = 0;
			foreach ($return["resumen"] as $key => $value) {
				$array_years[$count] = $key;
				$count++;
			}


			$row = 2;
			$col = 7;
			$objPHPExcel->setActiveSheetIndex(0);
			foreach ($array_years as $key => $value) {
				$colf = 7;
				foreach($array_years as $key=>$value) {
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($array_merge_years[$value]["inicial"][0],$array_merge_years[$value]["inicial"][1],$array_merge_years[$value]["inicial"][2]);

					$objPHPExcel->setActiveSheetIndex(0)->mergeCells($array_merge_years[$value]["merge"]);
					$objPHPExcel->getActiveSheet()->getStyle($array_merge_years[$value]["merge"])->applyFromArray($estiloYears);
					$col=$col+14;
					$colf=$colf+14;	

				}
				break;
			}

			$array_months= [];
			$count = 7;
			foreach ($return["resumen"] as $year => $year_value) {
				foreach ($year_value as $months => $months_value) {
					$array_months[] = $year." ".$months;
				}
			}

			$row = 3;
			$col = 7; 		
			foreach ($array_months as $key => $value) {
				$colf = 7; 
				foreach($array_months as $key=>$value) {
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($colf, $row,   explode(" ",$value)[0]." ".$nombre_mes[(int)explode(" ",$value)[1]]);
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells($l[$col]."".$row.":".$l[$col+13]."".$row);
					$objPHPExcel->getActiveSheet()->getStyle($l[$col]."".$row.":".$l[$col+13]."".$row)->applyFromArray($estiloMonths);
					$col=$col+14;
					$colf=$colf+14;
				}
				break;
			}


			unset($array_opciones_mes[0]);
			$array_months= [];
			$array_first = [];
			$array_last = [];		
			$row = 4;
			$col = 7;
			$i=1;
			foreach ($return["resumen"] as $year => $year_value) {
				foreach ($year_value as $months => $months_value) {
					foreach ($array_opciones_mes as $id => $value_opciones) {
						if ($id==1) {
							$array_first[] = $col;
						}
						if ($id==count($array_opciones_mes)) {
							$array_last[] = $col;
						}					
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row,$value_opciones);
						if ($id=='1') {
							$objPHPExcel->getActiveSheet()->getStyle($l[$col]."".$row)->applyFromArray($estiloOpcionesDineroApostado);
						}
						if ($id =='2'){
							$objPHPExcel->getActiveSheet()->getStyle($l[$col]."".$row)->applyFromArray($estiloOpcionesDineroGanado);
						}
						if ($id =='3') {
							$objPHPExcel->getActiveSheet()->getStyle($l[$col]."".$row)->applyFromArray($estiloOpcionesDineroPagado);
						}
						if ($id=='4') {
							$objPHPExcel->getActiveSheet()->getStyle($l[$col]."".$row)->applyFromArray($estiloOpcionesDineroPorPagar);
						}
						if ($id=='5') {
							$objPHPExcel->getActiveSheet()->getStyle($l[$col]."".$row)->applyFromArray($estiloOpcionesNetWinT);
						}
						if ($id=='6') {
							$objPHPExcel->getActiveSheet()->getStyle($l[$col]."".$row)->applyFromArray($estiloOpcionesHold);
						}
						if ($id=='7') {
							$objPHPExcel->getActiveSheet()->getStyle($l[$col]."".$row)->applyFromArray($estiloOpcionesTicketsEmitidos);
						}
						if ($id=='8') {
							$objPHPExcel->getActiveSheet()->getStyle($l[$col]."".$row)->applyFromArray($estiloOpcionesTicketsGanados);
						}
						if ($id=='9') {
							$objPHPExcel->getActiveSheet()->getStyle($l[$col]."".$row)->applyFromArray($estiloOpcionesTicketsPagados);
						}
						if ($id=='10') {
							$objPHPExcel->getActiveSheet()->getStyle($l[$col]."".$row)->applyFromArray($estiloOpcionesTicketsPorPagar);
						}
						if ($id=='11') {
							$objPHPExcel->getActiveSheet()->getStyle($l[$col]."".$row)->applyFromArray($estiloOpcionesTicketsApuestaXTickets);
						}
						if ($id=='12') {
							$objPHPExcel->getActiveSheet()->getStyle($l[$col]."".$row)->applyFromArray($estiloOpcionesPorcentajeTicketsPremiados);
						}
						if ($id=='13') {
							$objPHPExcel->getActiveSheet()->getStyle($l[$col]."".$row)->applyFromArray($estiloOpcionesDineroDepositadoWeb);
						}
						if ($id=='14') {
							$objPHPExcel->getActiveSheet()->getStyle($l[$col]."".$row)->applyFromArray($estiloOpcionesDineroRetiradoWeb);
						}
																																																																																									
						$col++;
					}
				}
			}
			$objPHPExcel->getActiveSheet()->getRowDimension('4')->setRowHeight(45);

			/*******************************************************************************************************/
	
			$r = 5;
			foreach($cdv as $index_cdv => $nombre_cdv) {
				foreach($new_obj as $obj_index => $obj_data) {
					if($obj_data["canal_de_venta_id"] == $index_cdv){
						if($obj_data["period"] == $periodo_inicio){
							$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue('A'.$r,$nombre_cdv) 
							->setCellValue('B'.$r,$obj_data["nombre"])
							->setCellValue('C'.$r,$obj_data["propiedad"]) 
							->setCellValue('D'.$r,$obj_data["asesor_nombre"])					
							->setCellValue('E'.$r,$obj_data["administracion"])
							->setCellValue('F'.$r,$obj_data["tipo"])					
							->setCellValue('G'.$r,$obj_data["qty"]);
							
							$col_detalles = 7;
							foreach ($period_arr as $period_index => $period_val) {
								foreach ($cols as $col_index => $col_data) {
									if(isset($obj_by_period[$period_index][$obj_data["local_id"]."".$obj_data["canal_de_venta_id"]])){
										if(isset($obj_by_period[$period_index][$obj_data["local_id"]."".$obj_data["canal_de_venta_id"]][$col_index])){

											$objPHPExcel->getActiveSheet()->getStyle($l[$col_detalles]."".$r)->getNumberFormat()->setFormatCode('#,##0.00');
											
    										$objPHPExcel->getActiveSheet()->getRowDimension($r)->setOutlineLevel(1)->setVisible(true)->setCollapsed(true);									

											$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_detalles,$r,$obj_by_period[$period_index][$obj_data["local_id"]."".$obj_data["canal_de_venta_id"]][$col_index]);
												$objPHPExcel->getActiveSheet()->getColumnDimension($l[$col_detalles])->setWidth(12);	

											$conditionalStyles = $objPHPExcel->getActiveSheet()->getStyle($l[$col_detalles]."".$r.":".$l[$col_detalles]."".$r)->getConditionalStyles();
											array_push($conditionalStyles, $objConditionalNegativeNumber);
											$objPHPExcel->getActiveSheet()->getStyle($l[$col_detalles]."".$r.":".$l[$col_detalles]."".$r)->setConditionalStyles($conditionalStyles);	

												$col_detalles++;									
										}else{
											$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_detalles,$r,"-");
											$objPHPExcel->getActiveSheet()->getColumnDimension($l[$col_detalles])->setWidth(12);
											$col_detalles++;
										}								
									}else{
											$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_detalles,$r,"-");
											$objPHPExcel->getActiveSheet()->getColumnDimension($l[$col_detalles])->setWidth(12);
											$col_detalles++;
									}
								
								}
							}
							$r++;
							$row_subtotales = 0;
							if(count($new_obj)> $obj_index+1){
								$next_object = $new_obj[(int)$obj_index+1];
								if(strcoll($obj_data["canal_de_venta_id"],$next_object["canal_de_venta_id"]) != 0){

									$objPHPExcel->setActiveSheetIndex(0)
									->setCellValue('A'.$r,"Total Canal ".$nombre_cdv);
									$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A'.$r.':'.'G'.$r);									
									$objPHPExcel->getActiveSheet()->getStyle("A".$r.":"."G".$r)->applyFromArray($estiloTotales);
									$col_detalles = 7;
									foreach ($period_arr as $period_index => $period_val) {
										foreach ($cols as $col_index => $col_data) {
											if(isset($obj_total_by_period[$period_index][$obj_data["canal_de_venta_id"]])){
												if(isset($obj_total_by_period[$period_index][$obj_data["canal_de_venta_id"]][$col_index])){

													$objPHPExcel->getActiveSheet()->getStyle($l[$col_detalles]."".$r)->getNumberFormat()->setFormatCode('#,##0.00');

													$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_detalles,$r,$obj_total_by_period[$period_index][$obj_data["canal_de_venta_id"]][$col_index]);
													$objPHPExcel->getActiveSheet()->getColumnDimension($l[$col_detalles])->setWidth(12);
									
													$conditionalStyles = $objPHPExcel->getActiveSheet()->getStyle($l[$col_detalles]."".$r.":".$l[$col_detalles]."".$r)->getConditionalStyles();
															array_push($conditionalStyles, $objConditionalNegativeNumber);
															$objPHPExcel->getActiveSheet()->getStyle($l[$col_detalles]."".$r.":".$l[$col_detalles]."".$r)->setConditionalStyles($conditionalStyles);													
													$col_detalles++;									
												}else{
													$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_detalles,$r,"-");
													$objPHPExcel->getActiveSheet()->getColumnDimension($l[$col_detalles])->setWidth(12);
													$col_detalles++;
												}								
											}else{
													$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_detalles,$r,"-");
													$objPHPExcel->getActiveSheet()->getColumnDimension($l[$col_detalles])->setWidth(12);
													$col_detalles++;
											}
										}
									}
									$objPHPExcel->getActiveSheet()->getStyle($l[7]."".$r.":".$l[$col_detalles-1]."".$r)->applyFromArray($estiloTotales);
									$r++;
								}
							}

							if(count($new_obj)-1 == $obj_index){
								$objPHPExcel->setActiveSheetIndex(0)
								->setCellValue('A'.$r,"Total Canal ".$nombre_cdv); 
									$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A'.$r.':'.'G'.$r);
									$objPHPExcel->getActiveSheet()->getStyle("A".$r.":"."G".$r)->applyFromArray($estiloTotales);									
								$col_detalles = 7;
								foreach ($period_arr as $period_index => $period_val) {
									foreach ($cols as $col_index => $col_data) {
										if(isset($obj_total_by_period[$period_index][$obj_data["canal_de_venta_id"]])){
											if(isset($obj_total_by_period[$period_index][$obj_data["canal_de_venta_id"]][$col_index])){
												$objPHPExcel->getActiveSheet()->getStyle($l[$col_detalles]."".$r)->getNumberFormat()->setFormatCode('#,##0.00');

												$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_detalles,$r,$obj_total_by_period[$period_index][$obj_data["canal_de_venta_id"]][$col_index]);
													$objPHPExcel->getActiveSheet()->getColumnDimension($l[$col_detalles])->setWidth(12);

													$conditionalStyles = $objPHPExcel->getActiveSheet()->getStyle($l[$col_detalles]."".$r.":".$l[$col_detalles]."".$r)->getConditionalStyles();
															array_push($conditionalStyles, $objConditionalNegativeNumber);
															$objPHPExcel->getActiveSheet()->getStyle($l[$col_detalles]."".$r.":".$l[$col_detalles]."".$r)->setConditionalStyles($conditionalStyles);

													$col_detalles++;									
											}else{
												$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_detalles,$r,"-");
												$objPHPExcel->getActiveSheet()->getColumnDimension($l[$col_detalles])->setWidth(12);
												$col_detalles++;
											}								
										}else{
												$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_detalles,$r,"-");
												$objPHPExcel->getActiveSheet()->getColumnDimension($l[$col_detalles])->setWidth(12);

												$col_detalles++;
										}

									}
								}
								$objPHPExcel->getActiveSheet()->getStyle($l[7]."".$r.":".$l[$col_detalles-1]."".$r)->applyFromArray($estiloTotales);
								$r++;
							}


							$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue('A'.$r,"Total Canal");
								$col_detalles = 7;
								foreach ($period_arr as $period_index => $period_val) {
									foreach ($cols as $col_index => $col_data) {
										if(isset($obj_super_total_by_period[$period_index]["total"])){
											if(isset($obj_super_total_by_period[$period_index]["total"][$col_index])){
												$objPHPExcel->getActiveSheet()->getStyle($l[$col_detalles]."".$r)->getNumberFormat()->setFormatCode('#,##0.00');
												
												$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_detalles,$r,$obj_super_total_by_period[$period_index]["total"][$col_index]);

												$conditionalStyles = $objPHPExcel->getActiveSheet()->getStyle($l[$col_detalles]."".$r.":".$l[$col_detalles]."".$r)->getConditionalStyles();
														array_push($conditionalStyles, $objConditionalNegativeNumber);
														$objPHPExcel->getActiveSheet()->getStyle($l[$col_detalles]."".$r.":".$l[$col_detalles]."".$r)->setConditionalStyles($conditionalStyles);

													$col_detalles++;									
											}else{
												$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_detalles,$r,"-");
												$col_detalles++;
											}								
										}else{
												$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_detalles,$r,"-");
												$col_detalles++;
										}
									}
								}
						}
					}		
				}
			}
			$objPHPExcel->getActiveSheet()->freezePane('C5');
			$objPHPExcel->getActiveSheet()->getStyle($l[7]."".$r.":".$l[$col_detalles-1]."".$r)->applyFromArray($estiloTotales);
			$objPHPExcel->getActiveSheet()->getStyle("A".$r.":"."G".$r)->applyFromArray($estiloTotales);
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A'.$r.':'.'G'.$r);

			$objPHPExcel->getActiveSheet()->getStyle("A2:A4")->applyFromArray($estilotitulosgenerales);
			$objPHPExcel->getActiveSheet()->getStyle("B2:B4")->applyFromArray($estilotitulosgenerales);
			$objPHPExcel->getActiveSheet()->getStyle("C2:C4")->applyFromArray($estilotitulosgenerales);
			$objPHPExcel->getActiveSheet()->getStyle("D2:D4")->applyFromArray($estilotitulosgenerales);
			$objPHPExcel->getActiveSheet()->getStyle("E2:E4")->applyFromArray($estilotitulosgenerales);
			$objPHPExcel->getActiveSheet()->getStyle("F2:F4")->applyFromArray($estilotitulosgenerales);
			$objPHPExcel->getActiveSheet()->getStyle("G2:G4")->applyFromArray($estilotitulosgenerales);

			$objPHPExcel->setActiveSheetIndex(0)->mergeCells("A2:A4");

			$objPHPExcel->setActiveSheetIndex(0)->mergeCells("A2:A4");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells("B2:B4");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells("C2:C4");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells("D2:D4");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells("E2:E4");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells("F2:F4");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells("G2:G4");

			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(25);
			$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(15);						
		

			//Estilos condicionales numeros negativos/ menor que cero color rojo
			$objConditional1 = new PHPExcel_Style_Conditional();
			$objConditional1->setConditionType(PHPExcel_Style_Conditional::CONDITION_CELLIS);
			$objConditional1->setOperatorType(PHPExcel_Style_Conditional::OPERATOR_LESSTHAN);
			$objConditional1->addCondition('0');
			$objConditional1->getStyle()->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
			$objConditional1->getStyle()->getFont()->setBold(true);

			//Estilos condicionales numeros positivos /mayor que 0 color verde
			$objConditional2 = new PHPExcel_Style_Conditional();
			$objConditional2->setConditionType(PHPExcel_Style_Conditional::CONDITION_CELLIS);
			$objConditional2->setOperatorType(PHPExcel_Style_Conditional::OPERATOR_GREATERTHANOREQUAL);
			$objConditional2->addCondition('0');
			$objConditional2->getStyle()->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_DARKBLUE);
			$objConditional2->getStyle()->getFont()->setBold(true); 

			// Se asigna el nombre a la hoja
			$objPHPExcel->getActiveSheet()->setTitle("Libro 1");

			// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
			$objPHPExcel->setActiveSheetIndex(0);

			// Se manda el archivo al navegador web, con el nombre que se indica (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'.$titulo_file_reporte_apuestas.'.xls"');
			header('Cache-Control: max-age=0');

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$excel_path = '/var/www/html/export/files_exported/'.$titulo_file_reporte_apuestas.'.xls';
			$excel_path_download = '/export/files_exported/'.$titulo_file_reporte_apuestas.'.xls';
			$url = $titulo_file_reporte_apuestas.'.xls';			
			$objWriter->save($excel_path);

			$insert_cmd = "INSERT INTO tbl_exported_files (url,tipo,ext,size,fecha_registro,usuario_id)";
			$insert_cmd.= " VALUES ('".$url."','excel','xls','".filesize($excel_path)."','".date("Y-m-d h:i:s")."','".$login["id"]."')";
			$mysqli->query($insert_cmd);

			echo json_encode([
			    "path" => $excel_path_download,
			    "url" => $titulo_file_reporte_apuestas.'.xls',
			    "tipo" => "excel",
			    "ext" => "xls",
			    "size" => filesize($excel_path),
			    "fecha_registro" => date("d-m-Y h:i:s"),
			    "sql" => $insert_cmd
			]);

			exit;  			
		}else{
			print_r('No hay resultados para mostrar');            
		}  
	}
?>