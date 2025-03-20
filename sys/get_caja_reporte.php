<?php

if (isset($_POST["sec_caja_get_reporte"])) {
	if($login){
		$get_data = $_POST["sec_caja_get_reporte"];

		//print_r($get_data);
		//exit();
		$local_id = $get_data["local_id"];
		$fecha_inicio = $get_data["fecha_inicio"];
		$fecha_inicio_pretty = date("d-m-Y", strtotime($get_data["fecha_inicio"]));
		$fecha_inicio_siguiente = date("Y-m-d", strtotime($fecha_inicio." +1 day"));
		$fecha_fin_real = $get_data["fecha_fin"];
		$fecha_fin = date("Y-m-d", strtotime($get_data["fecha_fin"]." +1 day"));
		$fecha_fin_pretty = date("d-m-Y", strtotime($get_data["fecha_fin"]));
		// $fecha_inicio = $get_data["year"]."-".$get_data["month"];
		// $fecha_fin = $get_data["year"]."-".$get_data["month"];
		// $local_id = 203;
		// $local_id = 328;
		$where_id = $local_id == "all" ? "WHERE l.id != 1": "WHERE l.id = '".$local_id."'";
		$local_query = $mysqli->query("SELECT l.id, l.nombre FROM tbl_locales l ".$where_id);


		$locals = array();
		while ($loc = $local_query->fetch_assoc()) {
			$loc["caja_config"]=array();
			$local_caja_config_command = "SELECT campo, valor FROM tbl_local_caja_config WHERE local_id = '".$loc["id"]."' AND estado = '1'";


			$local_caja_config_query = $mysqli->query($local_caja_config_command);
			if ($mysqli->error) {
				print_r($mysqli->error);
				exit();
			}
			while ($lcc=$local_caja_config_query->fetch_assoc()) {
				$loc["caja_config"][$lcc["campo"]]=$lcc["valor"];
			}
			$locals[] = $loc;
		}
		$table = array();
		$table["datos_sistema"]=array();
		$cajas = array();

		foreach ($locals as $local) {
			$caja_command = "SELECT
			c.id AS caja_id,
			lc.id AS local_caja_id,
			c.fecha_operacion,
			c.turno_id,
			c.observaciones,
			c.estado,
			c.validar
			FROM tbl_caja c
			LEFT JOIN tbl_local_cajas lc ON(lc.id = c.local_caja_id)
			LEFT JOIN tbl_locales l ON (l.id = lc.local_id)
			WHERE c.id != 1
			AND l.id = '".$local["id"]."'
			AND c.fecha_operacion >= '".$fecha_inicio."'
			AND c.fecha_operacion < '".$fecha_fin."'
			ORDER BY c.fecha_operacion ASC, c.turno_id ASC";


			$caja_query = $mysqli->query($caja_command);
			if ($mysqli->error) {
				print_r($mysqli->error);
				exit();
			}

			$caja_data = array();
			while ($c=$caja_query->fetch_assoc()) {
				$c["datos_sistema"]=array();
				$ds_command = "SELECT
				cd.id,
				cdt.nombre,
				IFNULL(cd.ingreso,0) AS ingreso,
				IFNULL(cd.salida,0) AS salida,
				CAST((IFNULL(cd.ingreso,0) - IFNULL(cd.salida,0)) AS DECIMAL(20,2)) AS resultado,
				lcdt.detalle_tipos_id
				FROM tbl_local_caja_detalle_tipos lcdt
				LEFT JOIN tbl_caja_detalle cd ON (cd.tipo_id = lcdt.id AND cd.caja_id = '".$c["caja_id"]."')
				LEFT JOIN tbl_caja_detalle_tipos cdt ON (cdt.id = lcdt.detalle_tipos_id)
				WHERE lcdt.local_id = '".$local_id."' AND cdt.estado = 1 				
				AND cdt.id != 12
				ORDER BY cdt.ord ASC, lcdt.orden, lcdt.nombre ASC";


				//echo $ds_command; exit();
				$ds_query = $mysqli->query($ds_command);
				if ($mysqli->error) {
					print_r($mysqli->error);
					exit();
				}
				$array_tipos_id = array();
				while ($ds_db=$ds_query->fetch_assoc()) {
					// print "<pre>";print_r($ds_db);print "</pre>";
					$c["datos_sistema"][$ds_db["detalle_tipos_id"]][]=$ds_db;
					$array_tipos_id[$ds_db["detalle_tipos_id"]] = $ds_db["detalle_tipos_id"];
				}

				$c["datos_fisicos"]=array();
				$df_command = "SELECT
				df.tipo_id, IFNULL(df.valor,0) AS valor
				FROM tbl_caja_datos_fisicos df
				WHERE df.caja_id = '".$c["caja_id"]."'";


				$df_query = $mysqli->query($df_command);
				if ($mysqli->error) {
					print_r($mysqli->error);
					exit();
				}
				while ($df_db=$df_query->fetch_assoc()) {
					$c["datos_fisicos"][$df_db["tipo_id"]]=$df_db;
				}
				$caja_data[]=$c;
			}
			if (count($caja_data)) {
				// print "<pre>";print_r($caja_data);print "</pre>";
				$num_terminals = [
					4 => 0,   //BC terminales
					28 => 0,   // Kurax terminales
				];
				$table["datos_sistema"]["cols"]=array();
				foreach ($caja_data as $ck => $c) {
					$new_num_terminals = 0;
					$new_num_terminals_kurax = 0;
					foreach ($c["datos_sistema"] as $detalle_tipos_id => $ds) {
						if ($detalle_tipos_id==4) {
							foreach ($ds as $key => $value) {
								$new_num_terminals++;
							}
						}
						if ($detalle_tipos_id==28) {
							foreach ($ds as $key => $value) {
								$new_num_terminals_kurax++;
							}
						}
						$table["datos_sistema"]["cols"][$detalle_tipos_id]=$ds[0];
					}
					if ($new_num_terminals > $num_terminals[4]) {
						$num_terminals[4]=$new_num_terminals;
					}
					if ($new_num_terminals_kurax > $num_terminals[28]) {
						$num_terminals[28] = $new_num_terminals_kurax;
					}
				}
				// print "<pre>";print_r($table);print "</pre>";
				$table["datos_sistema"]["col_num"]=count($table["datos_sistema"]["cols"]);
				$table["datos_sistema"]["num_terminals"]=$num_terminals;
				// colspan se resetea a 0 y va aumentando de acuerdo a los valores de cada campo
				$table["datos_sistema"]["colspan"]=0; 
				foreach ($table["datos_sistema"]["cols"] as $ds_k => $ds_v) {
					if ($ds_k == 4 || $ds_k == 28) {
						$table["datos_sistema"]["colspan"]+=($table["datos_sistema"]["num_terminals"][$ds_k] + 1);
					}
					else if ($ds_k == 21)
					{//disashop
						$table["datos_sistema"]["colspan"]+=1;
					}
					else {
						$table["datos_sistema"]["colspan"]+=3;
					}
				}
				$report=array();
				$report["apertura"]=false;
				$report["efectivo_fisico"]=0;
				// print "<pre>";print_r($table);print "</pre>";
				// foreach ($table["datos_sistema"]["cols"] as $key => $value) {
				// 	$table["datos_sistema"]["colspan"]+=1;
				// }
				// ($table["datos_sistema"]["col_num"] ? ((($table["datos_sistema"]["col_num"])*3)+2):1)
				$table["tbody"]=array();
				// $table["default"][""]
				$no_data = "-";
				// $total_arr = array();
				foreach ($caja_data as $data_id => $data) {
					// $tr_in
					$tr = array();
					$tr["caja_id"]=$data["caja_id"];
					$tr["local_caja_id"]=$data["local_caja_id"];
					$tr["local_nombre"] = $local["nombre"];
					$tr["ano"] = substr($data["fecha_operacion"], 0, 4);
					$tr["mes"] = substr($data["fecha_operacion"], 5, 2);
					$tr["dia"] = substr($data["fecha_operacion"], 8, 2);
					$tr["turno_id"] = $data["turno_id"];
					$tr["apertura"]=(array_key_exists(1, $data["datos_fisicos"]) ? $data["datos_fisicos"][1]["valor"] : 0);

					// $total_arr[""]


					foreach ($table["datos_sistema"]["cols"] as $ds_k => $ds_v) {
						if (array_key_exists($ds_k, $data["datos_sistema"])) {
							if ($ds_k == 4 || $ds_k == 28) {
								$t_sum = 0;
								foreach ($data["datos_sistema"][$ds_k] as $key => $value) {
									$tr["ds_".$ds_k."_t_".$key."_"."_in"]=$value["ingreso"];
									$t_sum+=$value["ingreso"];
								}
								$tr["ds_".$ds_k."_res"]=$t_sum;
							}
							else if($ds_k == 21)
							{//disashop
								$ds = $data["datos_sistema"][$ds_k][0];
								$tr["ds_".$ds_k."_in"]=$ds["ingreso"];
							}
							else {
								$ds = $data["datos_sistema"][$ds_k][0];
								$tr["ds_".$ds_k."_in"]=$ds["ingreso"];
								$tr["ds_".$ds_k."_out"]=$ds["salida"];
								$tr["ds_".$ds_k."_res"]=$ds["resultado"];
							}
						} else {
							if ($ds_k == 4 || $ds_k == 28) {
								for ($t=1; $t <= $table["datos_sistema"]["num_terminals"][$ds_k]; $t++) {
									$tr["ds_".$ds_k."_t_".$t."_"."_in"]=$no_data;
								}
								$tr["ds_".$ds_k."_res"]=$no_data;
							}
							else if($ds_k == 21)
							{//disashop
								$tr["ds_".$ds_k."_in"]=$no_data;
							}
							else {
								$tr["ds_".$ds_k."_in"]=$no_data;
								$tr["ds_".$ds_k."_out"]=$no_data;
								$tr["ds_".$ds_k."_res"]=$no_data;
							}
						}
					}

					$tr["resultado"]="".(array_key_exists(5, $data["datos_fisicos"]) ? $data["datos_fisicos"][5]["valor"] : "0.00");
					$tr["devoluciones"]="".(array_key_exists(8, $data["datos_fisicos"]) ? $data["datos_fisicos"][8]["valor"] : "0.00");
					$tr["devoluciones_simulcast"]="".(array_key_exists(28, $data["datos_fisicos"]) ? $data["datos_fisicos"][28]["valor"] : "0.00");
					//$tr["devoluciones"] = $tr["devoluciones"] + $tr["devoluciones_simulcast"];					
					//unset($tr["devoluciones_simulcast"]);
                    $tr["ticket_tito_slots"]="".(array_key_exists(23, $data["datos_fisicos"]) ? $data["datos_fisicos"][23]["valor"] : "0.00");
                    $tr["bonos_promociones"]="".(array_key_exists(24, $data["datos_fisicos"]) ? $data["datos_fisicos"][24]["valor"] : "0.00");
					$tr["pagos_manuales"]="".(array_key_exists(9, $data["datos_fisicos"]) ? $data["datos_fisicos"][9]["valor"] : "0.00");
					$tr["resultado_real"]=$tr["resultado"]-$tr["pagos_manuales"]-$tr["devoluciones"];
					/*$tr["visa"]="".(array_key_exists(6, $data["datos_fisicos"]) ? $data["datos_fisicos"][6]["valor"] : "0.00");
					$tr["mastercard"]="".(array_key_exists(7, $data["datos_fisicos"]) ? $data["datos_fisicos"][7]["valor"] : "0.00");
					$tr["deposito_directo_clientes"]="".(array_key_exists(21, $data["datos_fisicos"]) ? $data["datos_fisicos"][21]["valor"] : "0.00");*/
					// $tr["deposito_directo_clientes"]="".(array_key_exists(21, $data["datos_fisicos"]) ? $data["datos_fisicos"][21]["valor"] : "0.00");
					$tr["reclamacion_terceros"]="".(array_key_exists(30, $data["datos_fisicos"]) ? $data["datos_fisicos"][30]["valor"] : "0.00");
					$tr["transferencia_igh"]="".(array_key_exists(32, $data["datos_fisicos"]) ? $data["datos_fisicos"][32]["valor"] : "0.00");
					$tr["yape"] = "".(array_key_exists(33, $data["datos_fisicos"]) ? $data["datos_fisicos"][33]["valor"] : "0.00");
					$tr["tarjeta_credito_niubiz"] = "".(array_key_exists(22, $data["datos_fisicos"]) ? $data["datos_fisicos"][22]["valor"] : "0.00");
					$tr["tarjeta_credito_izipay"] = "".(array_key_exists(31, $data["datos_fisicos"]) ? $data["datos_fisicos"][31]["valor"] : "0.00");

					$tr["aumento_fondo"]="".(array_key_exists(18, $data["datos_fisicos"]) ? $data["datos_fisicos"][18]["valor"] : "0.00");
					$tr["reduccion_fondo"]="".(array_key_exists(19, $data["datos_fisicos"]) ? $data["datos_fisicos"][19]["valor"] : "0.00");
					$tr["prestamo_slot"]="".(array_key_exists(12, $data["datos_fisicos"]) ? $data["datos_fisicos"][12]["valor"] : "0.00");
					$tr["prestamo_boveda"]="".(array_key_exists(2, $data["datos_fisicos"]) ? $data["datos_fisicos"][2]["valor"] : "0.00");
					$tr["devolucion_slot"]="".(array_key_exists(13, $data["datos_fisicos"]) ? $data["datos_fisicos"][13]["valor"] : "0.00");
					$tr["devolucion_boveda"]="".(array_key_exists(3, $data["datos_fisicos"]) ? $data["datos_fisicos"][3]["valor"] : "0.00");
					$tr["deposito_venta"]="".(array_key_exists(4, $data["datos_fisicos"]) ? $data["datos_fisicos"][4]["valor"] : "0.00");

					/*hermeticase boveda 26 tbl_caja_datos_fisicos_tipos*/
					$tr["hermeticase_boveda"]="".(array_key_exists(26, $data["datos_fisicos"]) ? $data["datos_fisicos"][26]["valor"] : "0.00");
					/*hermeticase venta 27 tbl_caja_datos_fisicos_tipos*/
					$tr["hermeticase_venta"]="".(array_key_exists(27, $data["datos_fisicos"]) ? $data["datos_fisicos"][27]["valor"] : "0.00");

					//$tr["deuda_slot"]="".(array_key_exists(16, $data["datos_fisicos"]) ? $data["datos_fisicos"][16]["valor"] : "0.00");
					$tr["deuda_boveda"]="".(array_key_exists(17, $data["datos_fisicos"]) ? $data["datos_fisicos"][17]["valor"] : "0.00");
					// $tr["fondo_fijo"]=($local["caja_config"]["monto_inicial"] ? $local["caja_config"]["monto_inicial"] : 0);
					// $tr["valla"]=$local["caja_config"]["valla_deposito"];
					$tr["fondo_fijo"]="".(array_key_exists(14, $data["datos_fisicos"]) ? $data["datos_fisicos"][14]["valor"] : ($local["caja_config"]["monto_inicial"] ? $local["caja_config"]["monto_inicial"] : 0));
					$tr["saldo_kasnet"]="".(array_key_exists(20, $data["datos_fisicos"]) ? $data["datos_fisicos"][20]["valor"] : "0.00");
					$tr["saldo_disashop"]="".(array_key_exists(25, $data["datos_fisicos"]) ? $data["datos_fisicos"][25]["valor"] : "0.00");
					$tr["valla"]="".(array_key_exists(15, $data["datos_fisicos"]) ? $data["datos_fisicos"][15]["valor"] : $local["caja_config"]["valla_deposito"]);

					$efectivo_fisico = (array_key_exists(11, $data["datos_fisicos"]) ? $data["datos_fisicos"][11]["valor"] : "0.00");
					// $deposito = $efectivo_fisico - $tr["fondo_fijo"];
					// if($deposito<0){ $deposito = 0; }
					$deposito = ($efectivo_fisico>$tr["fondo_fijo"] ? $efectivo_fisico - $tr["fondo_fijo"] : 0);
					$tr["deposito"]=round($deposito, 2);
					$depositar = $tr["apertura"]-$tr["fondo_fijo"];
					if($depositar < $tr["valla"]) $depositar = 0;
					$tr["depositar"]= ($depositar > 0 ? $depositar : 0);
					$tr["accion"]=($tr["depositar"] > 0 ? "" : "No ")."Depositar";
					$tr["robo_externo"]= "".(array_key_exists(29, $data["datos_fisicos"]) ? $data["datos_fisicos"][29]["valor"] : "0.00");
					$tr["efectivo_sistema"]="".(array_key_exists(10, $data["datos_fisicos"]) ? $data["datos_fisicos"][10]["valor"] : "0.00");
					$tr["efectivo_fisico"]="".round($efectivo_fisico, 2);
					$diff = ($tr["efectivo_fisico"]-$tr["efectivo_sistema"]);
					$tr["efectivo_sobrante"]=round($diff, 2);
					$tr["observaciones"]=$data["observaciones"];
					$tr["estado"]=($data["estado"]==1 ? "Cerrado" : "Abierto");

					$tr["validar"]=$data["validar"];

					$table["tbody"][]=$tr;

					  //
					  // print "<pre>";print_r($table["tbody"]);print "</pre>";

					if ($report["apertura"]===false) {
						$report["apertura"]=$tr["apertura"];
					}
					$report["efectivo_fisico"]=$tr["efectivo_fisico"];
				}
				$table_total=array();
				// $table_total["apertura"]=0;
				// $table_total["resultado"]=0;
				// $table_total["devolucion_slot"]=0;
				// $table_total["devolucion_boveda"]=0;
				// $table_total["deposito_venta"]=0;
				// $table_total["visa"]=0;
				// $table_total["mastercard"]=0;
				// $table_total["devoluciones"]=0;
				// $table_total["pagos_manuales"]=0;
				// $table_total["efectivo_sobrante"]=0;
				// $table_total["__________________"]=0;
				$table_total_ignore=array();
				$table_total_ignore[]="local_nombre";
				$table_total_ignore[]="ano";
				$table_total_ignore[]="mes";
				$table_total_ignore[]="dia";
				$table_total_ignore[]="turno_id";
				$table_total_ignore[]="apertura";
				$table_total_ignore[]="observaciones";
				$table_total_ignore[]="accion";
				$table_total_ignore[]="fondo_fijo";
				$table_total_ignore[]="valla";
				$table_total_ignore[]="deposito";
				$table_total_ignore[]="depositar";
				$table_total_ignore[]="efectivo_sistema";
				$table_total_ignore[]="efectivo_fisico";
				$table_total_ignore[]="deuda_slot";
				$table_total_ignore[]="saldo_kasnet";
				$table_total_ignore[]="saldo_disashop";
				$table_total_ignore[]="estado";
				$table_total_ignore[]="validar";
				$table_total_ignore[]="caja_id";
				foreach ($table["tbody"] as $tr_k => $tr_v) {
					foreach ($tr_v as $key => $value) {
						if (in_array($key, $table_total_ignore)) {
							if (in_array($key, array("turno_id","dia","mes","ano","apertura","accion","fondo_fijo","valla","observaciones","fondo_fijo","valla","deposito","depositar","efectivo_sistema","efectivo_fisico","deuda_slot","saldo_kasnet","estado","validar"))) {
								$value="";
							}
							if ($key != "caja_id") {
								$table_total[$key]=$value;
							}
						} else {
							if (array_key_exists($key, $table_total)) {
								$table_total[$key]+=$value;
							} else {
								$table_total[$key]=$value;
							}
							// $table_total[$key] = round($table_total[$key],2);
						}
					}
				}
				// $table_total_orden = array();
				$table["total"]=$table_total;
				// print_r($table_total);
				$resumen = array();
				$resumen["apertura"]=($report["apertura"]===false ? 0 : $report["apertura"]);
				$resumen["resultado"]=$table_total["resultado"];
				$resumen["transferencia_igh"]=$table_total["transferencia_igh"];
				$resumen["depositos"]=($table_total["deposito_venta"]);


				//$resumen["tarjetas"]=($table_total["visa"]+$table_total["mastercard"]+$table_total["tarjeta_credito"]);
				$resumen["tarjetas"]=($table_total["tarjeta_credito_niubiz"] + $table_total["tarjeta_credito_izipay"]);
				$resumen["yape"]=($table_total["yape"]);
				$resumen["reclamacion_terceros"]=($table_total["reclamacion_terceros"]);
				$resumen["transferencia_igh"]=($table_total["transferencia_igh"]);
				$resumen["devo_manuales"]=($table_total["devoluciones"]+$table_total["devoluciones_simulcast"]+$table_total["pagos_manuales"]);
				$resumen["sobra_falta"]=$table_total["efectivo_sobrante"];
				$resumen["efectivo_fisico"]=($report["efectivo_fisico"]===false ? 0 : $report["efectivo_fisico"]);
				$resumen["prestamo_slotboveda"] = ($table_total["prestamo_slot"]+$table_total["prestamo_boveda"]);
				$resumen["devolucion_slotboveda"] = ($table_total["devolucion_slot"]+$table_total["devolucion_boveda"]);

				$resumen["hermeticase_venta"] = $table_total["hermeticase_venta"];
				$resumen["hermeticase_boveda"] = $table_total["hermeticase_boveda"];
				$resumen["ticket_tito_slots"] = $table_total["ticket_tito_slots"];
				
				$resumen["deposito_directo_clientes"]=$table_total["deposito_directo_clientes"];

				$resumen["aumento_fondo"]=$table_total["aumento_fondo"];
				$resumen["reduccion_fondo"]=$table_total["reduccion_fondo"];
				$resumen["bonos_promociones"]=$table_total["bonos_promociones"];
				$resumen["diff_real"]=(
					$resumen["apertura"]
					+$resumen["resultado"]
					-$resumen["depositos"]
					-$resumen["tarjetas"]
					-$resumen["yape"]
					+$resumen["reclamacion_terceros"]
					+$resumen["transferencia_igh"]
					-$resumen["devo_manuales"]
					+$resumen["sobra_falta"]
					-$resumen["efectivo_fisico"]
					+$resumen["prestamo_slotboveda"]
					-$resumen["devolucion_slotboveda"]
					-$resumen["deposito_directo_clientes"]
					-$resumen["hermeticase_venta"]
					-$resumen["hermeticase_boveda"]
					-$resumen["ticket_tito_slots"]

					
					+$resumen["aumento_fondo"]
					-$resumen["reduccion_fondo"]
					-$resumen["bonos_promociones"]
				);

				//resumen kasnet
				$kasnet = [];
				$result = $mysqli->query("
					SELECT
						CAST(
							(
								SELECT
									IFNULL(sk.saldo_anterior,0)
								FROM
									tbl_saldo_kasnet sk
								INNER JOIN tbl_caja caja ON (caja.id = sk.caja_id)
								WHERE
									caja.fecha_operacion >= '$fecha_inicio'
									AND caja.fecha_operacion < '$fecha_inicio_siguiente'
									AND sk.local_id = $local_id
									AND sk.estado = 1
									ORDER BY sk.created_at
								LIMIT 1
							)
							-
							(
								SELECT
									IFNULL(SUM(saldo_incremento),0)
								FROM
									tbl_saldo_kasnet
								WHERE
									created_at >= '$fecha_inicio'
									AND created_at < '$fecha_inicio_siguiente'
									AND local_id = $local_id
									AND estado = 1
									AND sistema = 0
									AND tipo_id = 2
									AND saldo_incremento > 0
							) AS DECIMAL(12,2)
						) as saldo_inicial,
						CAST((
							SELECT
								IFNULL(SUM(saldo_incremento),0)
							FROM
								tbl_saldo_kasnet
							WHERE
								created_at >= '$fecha_inicio'
								AND created_at < '$fecha_fin'
								AND local_id = $local_id
								AND estado = 1
								AND sistema = 0
								AND tipo_id = 2
								AND saldo_incremento > 0
						) AS DECIMAL(12,2)) as incremento_recarga,
						CAST((
							SELECT
								IFNULL(SUM(saldo_incremento),0)
							FROM
								tbl_saldo_kasnet
							WHERE
								created_at >= '$fecha_inicio'
								AND created_at < '$fecha_fin'
								AND local_id = $local_id
								AND estado = 1
								AND sistema = 0
								AND tipo_id = 2
								AND saldo_incremento < 0
						) AS DECIMAL(12,2)) as devolucion_recarga,
						CAST((
							SELECT
								IFNULL(SUM(sk.saldo_incremento),0)*-1
							FROM
								tbl_saldo_kasnet sk
							INNER JOIN tbl_caja c ON sk.caja_id = c.id
							WHERE
								c.fecha_operacion >= '$fecha_inicio'
								AND c.fecha_operacion < '$fecha_fin'
								AND sk.local_id = $local_id
								AND sk.estado = 1
								AND sk.sistema = 0
								AND sk.tipo_id = 1
						) AS DECIMAL(12,2)) as resultado,
						CAST(
							(
								SELECT IFNULL(sk.saldo_final,0) as saldo_virtual
                            FROM tbl_saldo_kasnet sk
                            INNER JOIN tbl_caja c ON c.id = sk.caja_id
                            WHERE
                            c.fecha_operacion >= '$fecha_fin_real'
                            AND c.fecha_operacion < DATE_ADD('$fecha_fin_real', INTERVAL 1 DAY)
                            AND sk.local_id = '$local_id'
                            AND sk.estado = 1
                            order by c.turno_id desc
                            limit 1
							)
							AS DECIMAL(12,2)
						) as saldo_virtual
				");
				if($r = $result->fetch_assoc()) $kasnet = $r;

				$resumen_kasnet = [];
				$resumen_kasnet["saldo_inicial"] = $kasnet["saldo_inicial"];
				$resumen_kasnet["incremento_recarga"] = $kasnet["incremento_recarga"];
				$resumen_kasnet["devolucion_recarga"] = $kasnet["devolucion_recarga"];
				$resumen_kasnet["resultado"] = $kasnet["resultado"];
				$resumen_kasnet["saldo_virtual"] = $kasnet["saldo_virtual"];
				$resumen_kasnet["diferencia_real"] = ($kasnet["incremento_recarga"] + $kasnet["saldo_inicial"]) - ($kasnet["devolucion_recarga"] + $kasnet["resultado"] + $kasnet["saldo_virtual"]);


				//resumen disashop
				$disashop = [];
				$query_disashop = "SELECT
						CAST((
							SELECT
								IFNULL(saldo_anterior,0)
							FROM tbl_saldo_disashop sd
                            INNER JOIN tbl_caja c ON c.id = sd.caja_id
                            WHERE
								c.fecha_operacion >= '$fecha_inicio'
								AND c.fecha_operacion < '$fecha_fin'
								AND sd.local_id = $local_id
								AND sd.estado = 1
								ORDER BY created_at
							LIMIT 1
						) AS DECIMAL(12,2)) as saldo_inicial,
						CAST((
							SELECT
								IFNULL(SUM(saldo_incremento),0)
							FROM tbl_saldo_disashop sd
                            INNER JOIN tbl_caja c ON c.id = sd.caja_id
                            WHERE
								c.fecha_operacion >= '$fecha_inicio'
								AND c.fecha_operacion < '$fecha_fin'
								AND sd.local_id = $local_id
								AND sd.estado = 1
								AND sistema = 0
								AND tipo_id = 2
								AND saldo_incremento > 0
						) AS DECIMAL(12,2)) as incremento_recarga,
						CAST((
							SELECT
								IFNULL(SUM(saldo_incremento),0)
							FROM tbl_saldo_disashop sd
                            INNER JOIN tbl_caja c ON c.id = sd.caja_id
                            WHERE
								c.fecha_operacion >= '$fecha_inicio'
								AND c.fecha_operacion < '$fecha_fin'
								AND sd.local_id = $local_id
								AND sd.estado = 1
								AND sistema = 0
								AND tipo_id = 2
								AND saldo_incremento < 0
						) AS DECIMAL(12,2)) as devolucion_recarga,
						CAST((
							SELECT
								IFNULL(SUM(saldo_incremento),0)*-1
							FROM tbl_saldo_disashop sd
                            INNER JOIN tbl_caja c ON c.id = sd.caja_id
                            WHERE
								c.fecha_operacion >= '$fecha_inicio'
								AND c.fecha_operacion < '$fecha_fin'
								AND sd.local_id = $local_id
								AND sd.estado = 1
								AND sistema = 0
								AND tipo_id = 1
						) AS DECIMAL(12,2)) as resultado,
						CAST((SELECT IFNULL(sd.saldo_final,0) as saldo_virtual_disashop
                            FROM tbl_saldo_disashop sd
                            INNER JOIN tbl_caja c ON c.id = sd.caja_id
                            WHERE
                            c.fecha_operacion >= '$fecha_fin_real'
                            AND c.fecha_operacion < DATE_ADD('$fecha_fin_real', INTERVAL 1 DAY)
                            AND sd.local_id = '$local_id'
                            AND sd.estado = 1
                            order by c.turno_id desc
                            limit 1)
						AS DECIMAL(12,2)) as saldo_virtual
						";
						
				$result = $mysqli->query($query_disashop);

				if($r = $result->fetch_assoc()) $disashop = $r;
				$resumen_disashop = [];
				$resumen_disashop["saldo_inicial"] = $disashop["saldo_inicial"];
				$resumen_disashop["incremento_recarga"] = $disashop["incremento_recarga"];
				$resumen_disashop["devolucion_recarga"] = $disashop["devolucion_recarga"];
				$resumen_disashop["resultado"] = $disashop["resultado"];
				$resumen_disashop["saldo_virtual"] = $disashop["saldo_virtual"];
				$resumen_disashop["diferencia_real"] = ($disashop["incremento_recarga"] 
						+ $disashop["saldo_inicial"]) - ($disashop["devolucion_recarga"] 
						+ $disashop["resultado"] + $disashop["saldo_virtual"]);

				if ($get_data["group_by"]=="day") {
					$ds_id_res_total_x_dia_in = array();
					$ds_id_res_total_x_dia_out = array();
					$ds_id_res_total_x_dia_res = array();
					/*$visa_total_x_dia = array();
					$mastercard_total_x_dia = array();
					$deposito_directo_clientes_x_dia = array();*/
					$deposito_directo_clientes_total_x_dia = array();
					$tarjeta_credito_niubiz_total_x_dia = array();
					$tarjeta_credito_izipay_total_x_dia = array();
					$yape_total_x_dia = array();
					$reclamacion_terceros_x_dia = array();
					$transferencia_igh_x_dia = array();
					$devoluciones_total_x_dia = array();
					$devoluciones_simulcast_total_x_dia = array();
                    $ticket_tito_slots_total_x_dia = array();
                    $bonos_promociones_total_x_dia = array();
					$aumento_fondo_total_x_dia = array();
					$reduccion_fondo_total_x_dia = array();
					$pagos_manuales_total_x_dia = array();
					$resultado_real_total_x_dia = array();
					$prestamo_slot_total_x_dia = array();
					$prestamo_boveda_total_x_dia = array();
					$devolucion_slot_total_x_dia = array();

					$hermeticase_boveda_total_x_dia = array();
					$hermeticase_venta_total_x_dia = array();

					$devolucion_boveda_total_x_dia = array();
					$deposito_venta_total_x_dia = array();
					//$deuda_slot_total_x_dia = array();
					$deuda_boveda_total_x_dia = array();
					$fondo_fijo_total_x_dia = array();
					$saldo_kasnet_total_x_dia = array();
					$saldo_disashop_total_x_dia = array();
					$valla_total_x_dia = array();
					$deposito_total_x_dia = array();
					$accion_total_x_dia = array();
					$efectivo_sistema_total_x_dia = array();
					$efectivo_fisico_total_x_dia = array();
					$efectivo_sobrante_total_x_dia = array();
					$table_x_day = array();
					$apertura_x_day = array();
					$resultado_x_day = array();
					$ds_terminales_total_x_dia = array();

					foreach ($table["tbody"] as $key => $value) {
						$apertura_x_day[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["apertura"];
						$resultado_x_day[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["resultado"];
						foreach ($array_tipos_id as $id => $val_tipos_id) {
							if ($id == 4 || $id == 28) {
								for ($ds_terminales = 0; $ds_terminales < $table["datos_sistema"]['num_terminals'][$id]; $ds_terminales++) {
									if (isset($value["ds_".$id."_t_".$ds_terminales."__in"])) {
										$ds_terminales_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]]["ds_".$id."_t_".$ds_terminales."__in"][] = $value["ds_".$id."_t_".$ds_terminales."__in"];
									} else {
										$ds_terminales_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]]["ds_".$id."_t_".$ds_terminales."__in"][] = "-";
									}
								}
								if (isset($value["ds_".$id."_res"])) {
									$ds_terminales_res_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]]["ds_".$id."_res"][] = $value["ds_".$id."_res"];
								} else {
									$ds_terminales_res_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]]["ds_".$id."_res"][] = "-";
								}
							} else {
								if (isset($value["ds_".$id."_in"])) {
									$ds_id_res_total_x_dia_in[$value["ano"]."".$value["mes"]."".$value["dia"]][$id][] = $value["ds_".$id."_in"];
								} else {
									$ds_id_res_total_x_dia_in[$value["ano"]."".$value["mes"]."".$value["dia"]][$id][] = "-";
								}
								if (isset($value["ds_".$id."_out"])) {
									$ds_id_res_total_x_dia_out[$value["ano"]."".$value["mes"]."".$value["dia"]][$id][] = $value["ds_".$id."_out"];
								} else {
									$ds_id_res_total_x_dia_out[$value["ano"]."".$value["mes"]."".$value["dia"]][$id][] = "-";
								}
								if (isset($value["ds_".$id."_res"])) {
									$ds_id_res_total_x_dia_res[$value["ano"]."".$value["mes"]."".$value["dia"]][$id][] = $value["ds_".$id."_res"];
								} else {
									$ds_id_res_total_x_dia_res[$value["ano"]."".$value["mes"]."".$value["dia"]][$id][] = "-";
								}
							}
						}

						if (isset($value["aumento_fondo"])) {
							$aumento_fondo_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["aumento_fondo"];
						} else {
							$aumento_fondo_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
						}
						if (isset($value["reduccion_fondo"])) {
							$reduccion_fondo_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["reduccion_fondo"];
						} else {
							$aumento_fondo_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
						}
						/*if (isset($value["visa"])) {
							$visa_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["visa"];
						} else {
							$visa_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
						}
						if (isset($value["mastercard"])) {
							$mastercard_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["mastercard"];
						} else {
							$mastercard_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
						}
						if (isset($value["deposito_directo_clientes"])) {
							$deposito_directo_clientes_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["deposito_directo_clientes"];
						} else {
							$deposito_directo_clientes_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
						}*/
						if (isset($value["deposito_directo_clientes"])) {
							$deposito_directo_clientes_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["deposito_directo_clientes"];
						} else {
							$deposito_directo_clientes_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
						}
						if (isset($value["tarjeta_credito_niubiz"])) {
							$tarjeta_credito_niubiz_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["tarjeta_credito_niubiz"];
						} else {
							$tarjeta_credito_niubiz_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
						}
						if (isset($value["tarjeta_credito_izipay"])) {
							$tarjeta_credito_izipay_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["tarjeta_credito_izipay"];
						} else {
							$tarjeta_credito_izipay_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
						}
						if (isset($value["yape"])) {
							$yape_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["yape"];
						} else {
							$yape_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
						}
						if (isset($value["reclamacion_terceros"])) {
							$reclamacion_terceros_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["reclamacion_terceros"];
						} else {
							$reclamacion_terceros_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
						}
						if (isset($value["transferencia_igh"])) {
							$transferencia_igh_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["transferencia_igh"];
						} else {
							$transferencia_igh_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
						}
						if (isset($value["devoluciones"])) {
							$devoluciones_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["devoluciones"];
						} else {
							$devoluciones_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
						}

						if (isset($value["devoluciones_simulcast"])) {
							$devoluciones_simulcast_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["devoluciones_simulcast"];
						} else {
							$devoluciones_simulcast_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
						}


                        if (isset($value["ticket_tito_slots"])) {
                            $ticket_tito_slots_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["ticket_tito_slots"];
                        } else {
                            $ticket_tito_slots_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
                        }

                        if (isset($value["bonos_promociones"])) {
                            $bonos_promociones_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["bonos_promociones"];
                        } else {
                            $bonos_promociones_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
                        }


						if (isset($value["pagos_manuales"])) {
							$pagos_manuales_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["pagos_manuales"];
						} else {
							$pagos_manuales_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
						}
						if (isset($value["resultado_real"])) {
							$resultado_real_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["resultado_real"];
						} else {
							$resultado_real_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
						}
						if (isset($value["prestamo_slot"])) {
							$prestamo_slot_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["prestamo_slot"];
						} else {
							$prestamo_slot_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
						}
						if (isset($value["prestamo_boveda"])) {
							$prestamo_boveda_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["prestamo_boveda"];
						} else {
							$prestamo_boveda_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
						}
						if (isset($value["devolucion_slot"])) {
							$devolucion_slot_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["devolucion_slot"];
						} else {
							$devolucion_slot_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
						}


						if (isset($value["devolucion_boveda"])) {
							$devolucion_boveda_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["devolucion_boveda"];
						} else {
							$devolucion_boveda_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
						}
						if (isset($value["deposito_venta"])) {
							$deposito_venta_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["deposito_venta"];
						} else {
							$deposito_venta_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
						}
						if (isset($value["hermeticase_boveda"])) {
							$hermeticase_boveda_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["hermeticase_boveda"];
						} else {
							$hermeticase_boveda_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
						}
						if (isset($value["hermeticase_venta"])) {
							$hermeticase_venta_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["hermeticase_venta"];
						} else {
							$hermeticase_venta_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
						}
						/*if (isset($value["deuda_slot"])) {
							$deuda_slot_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["deuda_slot"];
						} else {
							$deuda_slot_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
						}*/
						if (isset($value["deuda_boveda"])) {
							$deuda_boveda_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["deuda_boveda"];
						} else {
							$deuda_boveda_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
						}
						if (isset($value["fondo_fijo"])) {
							$fondo_fijo_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["fondo_fijo"];
						} else {
							$fondo_fijo_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
						}
						if (isset($value["saldo_kasnet"])) {
							$saldo_kasnet_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["saldo_kasnet"];
						} else {
							$saldo_kasnet_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
						}
						if (isset($value["saldo_disashop"])) {
							$saldo_disashop_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["saldo_disashop"];
						} else {
							$saldo_disashop_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
						}
						if (isset($value["valla"])) {
							$valla_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["valla"];
						} else {
							$valla_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
						}
						if (isset($value["deposito"])) {
							$deposito_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["deposito"];
						} else {
							$deposito_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
						}
						if (isset($value["accion"])) {
							$accion_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["accion"];
						} else {
							$accion_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
						}
						if (isset($value["efectivo_sistema"])) {
							$efectivo_sistema_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["efectivo_sistema"];
						} else {
							$efectivo_sistema_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
						}
						if (isset($value["efectivo_fisico"])) {
							$efectivo_fisico_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["efectivo_fisico"];
						} else {
							$efectivo_fisico_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
						}
						if (isset($value["efectivo_sobrante"])) {
							$efectivo_sobrante_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["efectivo_sobrante"];
						} else {
							$efectivo_sobrante_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
						}
					}

					foreach ($table["tbody"] as $num => $val) {
						$array =array();
						$array["caja_id"] = $val["caja_id"];
						$array["local_nombre"] = $val["local_nombre"];
						$array["ano"] = $val["ano"];
						$array["mes"] = $val["mes"];
						$array["dia"] = $val["dia"];
						$array["turno_id"] = "";
						if (isset($apertura_x_day[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
							$array["apertura"] = current($apertura_x_day[$val["ano"]."".$val["mes"]."".$val["dia"]]);
						} else {
							$array["apertura"] = "-";
						}

						foreach ($array_tipos_id as $id => $val_tipos_id) {
							if ($id == 4 || $id == 28) {
								for ($i=0; $i < $table["datos_sistema"]["num_terminals"][$id]; $i++) {
									if (isset($ds_terminales_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]["ds_".$id."_t_".$i."__in"])) {
										$array["ds_".$id."_t_".$i."__in"] = array_sum($ds_terminales_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]["ds_".$id."_t_".$i."__in"]);
									} else {
										$array["ds_".$i."_t_".$i."__in"] = "-";
									}
								}
								if (isset($ds_terminales_res_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]["ds_".$id."_res"])) {
									$array["ds_".$id."_res"] = array_sum($ds_terminales_res_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]["ds_".$id."_res"]);
								} else {
									$array["ds_".$id."_res"] = "-";
								}
							}
							else if($id == 21) {//disashop
								if (isset($ds_id_res_total_x_dia_in[$val["ano"]."".$val["mes"]."".$val["dia"]][$id])) {
									$array["ds_".$id."_in"] = array_sum($ds_id_res_total_x_dia_in[$val["ano"]."".$val["mes"]."".$val["dia"]][$id]);
								} else {
									$array["ds_".$id."_in"] = "-";
								}
							}
							else {
								if (isset($ds_id_res_total_x_dia_in[$val["ano"]."".$val["mes"]."".$val["dia"]][$id])) {
									$array["ds_".$id."_in"] = array_sum($ds_id_res_total_x_dia_in[$val["ano"]."".$val["mes"]."".$val["dia"]][$id]);
								} else {
									$array["ds_".$id."_in"] = "-";
								}
								if (isset($ds_id_res_total_x_dia_out[$val["ano"]."".$val["mes"]."".$val["dia"]][$id])) {
									$array["ds_".$id."_out"] = array_sum($ds_id_res_total_x_dia_out[$val["ano"]."".$val["mes"]."".$val["dia"]][$id]);
								} else {
									$array["ds_".$id."_out"] = "-";
								}
								if (isset($ds_id_res_total_x_dia_res[$val["ano"]."".$val["mes"]."".$val["dia"]][$id])) {
									$array["ds_".$id."_res"] = array_sum($ds_id_res_total_x_dia_res[$val["ano"]."".$val["mes"]."".$val["dia"]][$id]);
								} else {
									$array["ds_".$id."_res"] = "-";
								}
							}
						}




						if (isset($resultado_x_day[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
							$array["resultado"] = array_sum($resultado_x_day[$val["ano"]."".$val["mes"]."".$val["dia"]]);
						} else {
							$array["resultado"] = "-";
						}
						if (isset($devoluciones_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
							$array["devoluciones"] = array_sum($devoluciones_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
						} else {
							$array["devoluciones"] = "-";
						}

						if (isset($devoluciones_simulcast_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
							$array["devoluciones_simulcast"] = array_sum($devoluciones_simulcast_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
						} else {
							$array["devoluciones_simulcast"] = "-";
						}

                        if (isset($ticket_tito_slots_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
                            $array["ticket_tito_slots"] = array_sum($ticket_tito_slots_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
                        } else {
                            $array["ticket_tito_slots"] = "-";
                        }

                        if (isset($bonos_promociones_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
                            $array["bonos_promociones"] = array_sum($bonos_promociones_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
                        } else {
                            $array["bonos_promociones"] = "-";
                        }

						if (isset($pagos_manuales_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
							$array["pagos_manuales"] = array_sum($pagos_manuales_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
						} else {
							$array["pagos_manuales"] = "-";
						}

						if (isset($resultado_real_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
							$array["resultado_real"] = array_sum($resultado_real_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
						} else {
							$array["resultado_real"] = "-";
						}

						/*if (isset($visa_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
							$array["visa"] = array_sum($visa_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
						} else {
							$array["visa"] = "-";
						}
						if (isset($mastercard_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
							$array["mastercard"] = array_sum($mastercard_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
						} else {
							$array["mastercard"] = "-";
						}

						if (isset($deposito_directo_clientes_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
							$array["deposito_directo_clientes"] = array_sum($deposito_directo_clientes_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
						} else {
							$array["deposito_directo_clientes"] = "-";
						}*/
						// if (isset($deposito_directo_clientes_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
						// 	$array["deposito_directo_clientes"] = array_sum($deposito_directo_clientes_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
						// } else {
						// 	$array["deposito_directo_clientes"] = "-";
						// }
						
						if (isset($tarjeta_credito_niubiz_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
							$array["tarjeta_credito_niubiz"] = array_sum($tarjeta_credito_niubiz_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
						} else {
							$array["tarjeta_credito_niubiz"] = "-";
						}

						if (isset($tarjeta_credito_izipay_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
							$array["tarjeta_credito_izipay"] = array_sum($tarjeta_credito_izipay_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
						} else {
							$array["tarjeta_credito_izipay"] = "-";
						}
						if (isset($yape_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
							$array["yape"] = array_sum($yape_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
						} else {
							$array["yape"] = "-";
						}

						/*
						if (isset($reclamacion_terceros_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
							$array["reclamacion_terceros"] = array_sum($reclamacion_terceros_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
						} else {
							$array["reclamacion_terceros"] = "-";
						}
						*/

						if (isset($aumento_fondo_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
							$array["aumento_fondo"] = array_sum($aumento_fondo_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
						} else {
							$array["aumento_fondo"] = "-";
						}

						if (isset($reduccion_fondo_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
							$array["reduccion_fondo"] = array_sum($reduccion_fondo_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
						} else {
							$array["reduccion_fondo"] = "-";
						}

						if (isset($prestamo_slot_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
							$array["prestamo_slot"] = array_sum($prestamo_slot_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
						} else {
							$array["prestamo_slot"] = "-";
						}
						if (isset($prestamo_boveda_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
							$array["prestamo_boveda"] = array_sum($prestamo_boveda_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
						} else {
							$array["prestamo_boveda"] = "-";
						}
						if (isset($devolucion_slot_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
							$array["devolucion_slot"] = array_sum($devolucion_slot_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
						} else {
							$array["devolucion_slot"] = "-";
						}

						if (isset($devolucion_boveda_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
							$array["devolucion_boveda"] = array_sum($devolucion_boveda_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
						} else {
							$array["devolucion_boveda"] = "-";
						}
						if (isset($deposito_venta_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
							$array["deposito_venta"] = array_sum($deposito_venta_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
						} else {
							$array["deposito_venta"] = "-";
						}


						if (isset($hermeticase_boveda_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
							$array["hermeticase_boveda"] = array_sum($hermeticase_boveda_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
						} else {
							$array["hermeticase_boveda"] = "-";
						}
						if (isset($hermeticase_venta_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
							$array["hermeticase_venta"] = array_sum($hermeticase_venta_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
						} else {
							$array["hermeticase_venta"] = "-";
						}

						/*if (isset($deuda_slot_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
							$array["deuda_slot"] = end($deuda_slot_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
						} else {
							$array["deuda_slot"] = "-";
						}*/
						if (isset($deuda_boveda_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
							$array["deuda_boveda"] = end($deuda_boveda_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
						} else {
							$array["deuda_boveda"] = "-";
						}
						if (isset($fondo_fijo_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
							$array["fondo_fijo"] = end($fondo_fijo_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
						} else {
							$array["fondo_fijo"] = "-";
						}
						if (isset($saldo_kasnet_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
							$array["saldo_kasnet"] = end($saldo_kasnet_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
						} else {
							$array["saldo_kasnet"] = "-";
						}
						if (isset($saldo_disashop_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
							$array["saldo_disashop"] = end($saldo_disashop_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
						} else {
							$array["saldo_disashop"] = "-";
						}
						if (isset($valla_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
							$array["valla"] = end($valla_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
						} else {
							$array["valla"] = "-";
						}
						if (isset($deposito_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
							$array["deposito"] = end($deposito_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
						} else {
							$array["deposito"] = "-";
						}
						$array["accion"] = "";
						if (isset($efectivo_sistema_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
							$array["efectivo_sistema"] = end($efectivo_sistema_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
						} else {
							$array["efectivo_sistema"] = "-";
						}
						if (isset($efectivo_fisico_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
							$array["efectivo_fisico"] = end($efectivo_fisico_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
						} else {
							$array["efectivo_fisico"] = "-";
						}
						if (isset($efectivo_sobrante_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
							$array["efectivo_sobrante"] = array_sum($efectivo_sobrante_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
						} else {
							$array["efectivo_sobrante"] = "-";
						}
						$array["observaciones"] = "";
						$array["estado"] = '';
						$array["validar"] = $val["validar"];
						$period = $val["ano"]."".$val["mes"]."".$val["dia"];
						$table_x_day[(int)$period] = $array;
					}
					array_multisort($table_x_day);
				}

				$master_width = (int)(($table["datos_sistema"]["num_terminals"][4]*64) + ($table["datos_sistema"]["num_terminals"][28]*64) + (count($table["datos_sistema"]["cols"]) * 220));
			}
		} 	?>
			<?php if (count($caja_data)) {
			?>
			<?php if ($local_id != "all"):?>
				<div class="row">
					<div class="col-lg-offset-2 col-lg-4 col-mg-12 col-xs-12 ">
						<div class="panel panel-success">
							<div class="panel-heading">
								<h3 class="panel-title">Resumen del <?php echo $fecha_inicio_pretty; ?> al <?php echo $fecha_fin_pretty; ?></h3>
							</div>
							<div class="panel-body rd-pad">
								<table class="table table-condensed table-bordered table-striped">
									<tr>
										<th>Apertura Efectivo</th>
										<td class="text-right"><?php echo number_format($resumen["apertura"], 2); ?></td>
										<td>+</td>
									</tr>
									<tr>
										<th>Resultado Periodo</th>
										<td class="text-right"><?php echo number_format($resumen["resultado"], 2); ?></td>
										<td>+</td>
									</tr>
									<tr>
										<th>Depósitos de Venta</th>
										<td class="text-right"><?php echo number_format($resumen["depositos"], 2); ?></td>
										<td>-</td>
									</tr>
									<!-- <tr>
										<th>Depósito directo de los clientes</th>
										<td class="text-right"><?php echo number_format($resumen["deposito_directo_clientes"], 2); ?></td>
										<td>-</td>
									</tr> -->
									<tr>
										<th>Reclamación Terceros</th>
										<td class="text-right"><?php echo number_format($resumen["reclamacion_terceros"], 2); ?></td>
										<td>+</td>
									</tr>
									<tr>
										<th>Ticket TITO/Slots</th>
										<td class="text-right"><?php echo number_format($resumen["ticket_tito_slots"], 2); ?></td>
										<td>-</td>
									</tr>
									<tr>
										<th>Ventas con Tarjetas(Niubiz, Izipay)</th>
										<td class="text-right"><?php echo number_format($resumen["tarjetas"], 2); ?></td>
										<td>-</td>
									</tr>
									<tr>
										<th>Devoluciones / Pagos Manuales</th>
										<td class="text-right"><?php echo number_format($resumen["devo_manuales"], 2); ?></td>
										<td>-</td>
									</tr>
									<tr>
										<th>Sobrante / Faltante</th>
										<td class="text-right"><?php echo number_format($resumen["sobra_falta"], 2); ?></td>
										<td>+</td>
									</tr>
									<tr>
										<th>Efectivo Fisico</th>
										<td class="text-right"><?php echo number_format($resumen["efectivo_fisico"], 2); ?></td>
										<td>-</td>
									</tr>
									<tr>
										<th>Prestamo Slot / Bóveda</th>
										<td class="text-right"><?php echo number_format($resumen["prestamo_slotboveda"], 2); ?></td>
										<td>+</td>
									</tr>
								
									<tr>
										<th>Devolución Slot / Bóveda</th>
										<td class="text-right"><?php echo number_format($resumen["devolucion_slotboveda"], 2); ?></td>
										<td>-</td>
									</tr>

									<tr>
										<th>Hermeticase Bóveda</th>
										<td class="text-right"><?php echo number_format($resumen["hermeticase_boveda"], 2); ?></td>
										<td>-</td>
									</tr>
									<tr>
										<th>Hermeticase Venta</th>
										<td class="text-right"><?php echo number_format($resumen["hermeticase_venta"], 2); ?></td>
										<td>-</td>
									</tr>
									<tr>
										<th>Aumento Fondo</th>
										<td class="text-right"><?php echo number_format($resumen["aumento_fondo"], 2); ?></td>
										<td>-</td>
									</tr>
									<tr>
										<th>Reduccion Fondo</th>
										<td class="text-right"><?php echo number_format($resumen["reduccion_fondo"], 2); ?></td>
										<td>-</td>
									</tr>
									<tr>
										<th>Bonos y Promociones</th>
										<td class="text-right"><?php echo number_format($resumen["bonos_promociones"], 2); ?></td>
										<td>=</td>
									</tr>
									<tr>
										<th>Diferencia Real</th>
										<td class="text-right"><?php echo number_format(round($resumen["diff_real"], 2), 2); ?></td>
										<td></td>
									</tr>
								</table>
							</div>
						</div>
					</div>
					<div class="col-xs-12 col-lg-4 col-md-12">
						<div class="panel panel-success">
							<div class="panel-heading">
								<h3 class="panel-title">RESUMEN KASNET del <?php echo $fecha_inicio_pretty; ?> al <?php echo $fecha_fin_pretty; ?></h3>
							</div>
							<div class="panel-body rd-pad">
								<table class="table table-condensed table-bordered table-striped">
									<tr>
										<th>Saldo Inicial</th>
										<td class="text-right"><?php echo number_format($resumen_kasnet["saldo_inicial"], 2) ?></td>
									</tr>
									<tr>
										<th>Incremento de Recarga</th>
										<td class="text-right"><?php echo number_format($resumen_kasnet["incremento_recarga"], 2) ?></td>
									</tr>
									<tr>
										<th>Devolución de Recarga</th>
										<td class="text-right"><?php echo number_format($resumen_kasnet["devolucion_recarga"], 2) ?></td>
									</tr>
									<tr>
										<th>Resultado</th>
										<td class="text-right"><?php echo number_format($resumen_kasnet["resultado"], 2) ?></td>
									</tr>
									<tr>
										<th>Saldo Virtual</th>
										<td class="text-right"><?php echo number_format($resumen_kasnet["saldo_virtual"], 2) ?></td>
									</tr>
									<tr>
										<th>Diferencia Real</th>
										<td class="text-right"><?php echo number_format($resumen_kasnet["diferencia_real"], 2) ?></td>
									</tr>
								</table>
							</div>
						</div>
					</div>

					<div class="col-xs-12 col-lg-4 col-md-12">
						<div class="panel panel-success">
							<div class="panel-heading">
								<h3 class="panel-title">RESUMEN DISASHOP del <?php echo $fecha_inicio_pretty; ?> al <?php echo $fecha_fin_pretty; ?></h3>
							</div>
							<div class="panel-body rd-pad">
								<table class="table table-condensed table-bordered table-striped">
									<tr>
										<th>Saldo Inicial</th>
										<td class="text-right"><?php echo number_format($resumen_disashop["saldo_inicial"], 2) ?></td>
									</tr>
									<tr>
										<th>Incremento de Recarga</th>
										<td class="text-right"><?php echo number_format($resumen_disashop["incremento_recarga"], 2) ?></td>
									</tr>
									<tr>
										<th>Devolución de Recarga</th>
										<td class="text-right"><?php echo number_format($resumen_disashop["devolucion_recarga"], 2) ?></td>
									</tr>
									<tr>
										<th>Resultado</th>
										<td class="text-right"><?php echo number_format($resumen_disashop["resultado"], 2) ?></td>
									</tr>
									<tr>
										<th>Saldo Virtual</th>
										<td class="text-right"><?php echo number_format($resumen_disashop["saldo_virtual"], 2) ?></td>
									</tr>
									<tr>
										<th>Diferencia Real</th>
										<td class="text-right"><?php echo number_format($resumen_disashop["diferencia_real"], 2) ?></td>
									</tr>
								</table>
							</div>
						</div>
					</div>

					<div class="col-sm-12 col-lg-12">
						<button type="submit" class="btn btn-warning btn-xs btn_export_caja_reporte pull-right">
							<span class="glyphicon glyphicon-download-alt"></span>
							Exportar XLS
						</button>
					</div>
				</div>
			<?php endif; ?>

			<?php if ($get_data["group_by"]=="day"): ?>

				<div class="row table-responsive fixMargin">
					<table id="tbl_reporte_resumen_dia" class="table table-small table-condensed table-bordered table-striped" style="table-layout: fixed">
						<thead>
							<tr>
								<th style="width:79px !important; height: 87px !important;" colspan="1" rowspan="3">Local</th>
								<th style="width:40px !important; height: 87px !important;" colspan="1" rowspan="3">Año</th>
								<th style="width:37px !important; height: 87px !important;" colspan="1" rowspan="3">Mes</th>
								<th style="width:32px !important; height: 87px !important;" colspan="1" rowspan="3">Dia</th>
								<th style="width:66px !important; height: 87px !important;" colspan="1" rowspan="3" class="bg-warning text-center">Apertura <br/> Efectivo</th>
								<th style="width:<?php echo $master_width ?>px !important;" colspan="<?php echo $table["datos_sistema"]["colspan"]; ?>" rowspan="1" class="text-center bg-">Datos del sistema</th>
								<th style="width:1515px !important;" colspan="20" rowspan="1" class="text-center bg-">Datos Fisicos</th>
								<th style="width:1300px !important;" colspan="18" rowspan="1" class="text-center bg-">Información</th>
								<!--
								<th style="width:34px !important;" colspan="1" rowspan="3">Opt</th>
								<th style="width:83px !important;" colspan="1" rowspan="3">Validar</th>-->
							</tr>
							<tr>
								<?php
								foreach ($table["datos_sistema"]["cols"] as $ds_k => $ds_v) {
									if($ds_k == 21)//disashop
									{
									?>
									<th colspan="1" rowspan="1" class="text-center bg-primary"><?php echo $ds_v["nombre"]; ?></th>
									<?php
									}
									else 
									{
									?><th colspan="<?php echo(($ds_k == 4 || $ds_k == 28) ? ($table["datos_sistema"]["num_terminals"][$ds_k] + 1) : 3 ); ?>" rowspan="1" class="text-center bg-primary"><?php echo $ds_v["nombre"]; ?></th><?php
									}
								} ?>
								<th style="width:74px !important;" colspan="1" rowspan="2" class="bg-success">Resultado <br/> del día</th>
								<th style="width:96px !important;" colspan="1" rowspan="2">Devoluciones</th>
								<th style="width:71px !important;" colspan="1" rowspan="2">Carrera de Caballos</th>
                                <th style="width:71px !important;" colspan="1" rowspan="2">Ticket TITO/Slots</th>
								
                                <th style="width:71px !important;" colspan="1" rowspan="2">Bonos y Promociones</th>
								<th style="width:71px !important;" colspan="1" rowspan="2">Pagos Manuales</th>
								<th style="width:74px !important;" colspan="1" rowspan="2">Resultado Real</th>
								<!--<th style="width:51px !important;" colspan="1" rowspan="2">Visa</th>
								<th style="width:81px !important;" colspan="1" rowspan="2">Mastercard</th>
								<th style="width:75px !important;" colspan="1" rowspan="2">Depósito directo de los clientes</th>-->
								<!-- <th style="width:75px !important;" colspan="1" rowspan="2">Depósito directo de los clientes</th> -->
								<th style="width:75px !important;" colspan="1" rowspan="2">Transferencia IGH</th>
								<th style="width:75px !important;" colspan="1" rowspan="2">YAPE</th>
								<th style="width:75px !important;" colspan="1" rowspan="2">Tarjeta de Crédito/Débito NIUBIZ</th>
								<th style="width:75px !important;" colspan="1" rowspan="2">Tarjeta de Crédito/Débito IZIPAY</th>

								<th colspan="2" rowspan="1">Fondo</th>
								<th colspan="2" rowspan="1">Prestamos</th>
								<th colspan="3" rowspan="1">Depósitos</th>
								<th class="text-center" colspan="2" rowspan="1">Hermeticase</th>

								<!--<th style="width:51px !important;" colspan="1" rowspan="2">Deuda Slot</th>-->
								<th style="width:58px !important;" colspan="1" rowspan="2">Deuda Boveda</th>
								<th style="width:58px !important;" colspan="1" rowspan="2">Fondo Fijo</th>
								<th style="width:51px !important;" colspan="1" rowspan="2">Saldo Kasnet</th>
								<th style="width:51px !important;" colspan="1" rowspan="2">Saldo Disashop</th>
								<th style="width:67px !important;" colspan="1" rowspan="2">Mínimo Depósito</th>
								<th style="width:67px !important;" colspan="1" rowspan="2">Depósito</th>
								<!--<th style="width:67px !important;" colspan="1" rowspan="2">Acción</th>-->
								<th style="width:150px !important;" colspan="3" rowspan="1" class="text-center">Efectivo</th>
								<!--
								<th style="width:105px !important;" colspan="1" rowspan="2">Observaciones</th>
								<th style="width:58px !important;" colspan="1" rowspan="2">Estado</th> -->
							</tr>
							<tr>
								<?php foreach ($table["datos_sistema"]["cols"] as $ds_k => $ds_v): ?>

									<?php if ($ds_k == 4 || $ds_k == 28) {
									?>
										<?php for ($t=1; $t <= $table["datos_sistema"]["num_terminals"][$ds_k]; $t++): ?>
											<th style="width:64px !important;" colspan="1">Billetero <?php print_r($t); ?></th>
										<?php endfor; ?>

										<th style="width:70px !important;">resultado</th>
									<?php
								}
								else if($ds_k == 21)//disashop
								{
								?>
									<th style="width:58px !important;">ingreso</th>
								<?php
								}

								else {
									?>
										<th style="width:58px !important;">ingreso</th>
										<th style="width:58px !important;">salida</th>
										<th style="width:70px !important;">resultado</th>
									<?php
								} ?>

								<?php endforeach; ?>
								<th style="width:40px !important;">Aumento</th>
								<th style="width:40px !important;">Reduccion</th>
								<th style="width:40px !important;">Slot / Tienda</th>
								<th style="width:58px !important;">Boveda</th>
								<th style="width:40px !important;">Slot / Tienda</th>
								<th style="width:58px !important;">Boveda</th>
								<th style="width:58px !important;">Venta</th>

								<th style="width:58px !important;">Bóveda </th>
								<th style="width:58px !important;">Venta </th>

								<th style="width:66px !important;">Sistema</th>
								<th style="width:51px !important;">Fisico</th>
								<th style="width:121px !important;">Sobrante/Faltante</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($table_x_day as $fecha_operacion => $tr): ?>
								<tr>
									<?php foreach ($tr as $key => $val): ?>

										<?php if ($key != "turno_id" && $key != "observaciones" && $key != "validar" && $key != "estado" && $key != "accion"): ?>
											<?php if ($key!="caja_id"): ?>
												<?php if ($key!="validar"): ?>
													<td data-nombre ="<?php echo $key;?>" style="<?php
													if($key == "local_nombre"  && strlen($val) < 10) $val .= "<br><br>";
													if ($key =="local_nombre" || $key=="ano" || $key=="mes" || $key=="dia") {
									echo 'background-color:#fff';
								} ?>" class="<?php if ($key=="estado") {
									echo $val=="Abierto" ?  "bg-danger": "bg-success";
								} ?>">
														<?php if ($key=="observaciones") {
									if (strlen($val)>10) {
										echo '<i class="view_more_btn" title="'.$val.'">'.substr($val, 0, 10).'...</i>';
									} else {
										echo  ( is_numeric($val) ? number_format($val,2) : $val);
									}
								} else {
									if($key != 'ano' && $key != 'mes' && $key != 'dia'){
									  echo ( is_numeric($val) ? number_format($val,2) : $val);
									}else{
									  echo $val;
									}

								} ?>
													</td>
												<?php endif; ?>
											<?php endif; ?>
										<?php endif; ?>
									<?php endforeach; ?>
								</tr>
							<?php endforeach; ?>
							<tr>
								<?php foreach ($table["total"] as $key => $t_val) : ?>
									<?php if ($key != "turno_id" && $key != "accion" && $key != 'depositar' && $key != "local_caja_id" ): ?>
										<td data-nombre ="<?php echo $key;?>">
											 <?php if($key != "local_nombre"): ?>
												<b><?php echo ($t_val != "") ? ( is_numeric($t_val) ? number_format($t_val,2) : $t_val) : ""; ?></b>
											<?php else: ?>
												<b><?php echo strlen($t_val) < 10 ? $t_val."<br><br>" : ( is_numeric($t_val) ? number_format($t_val,2) : $t_val); ?></b>
											<?php endif; ?>
										</td>
									<?php endif; ?>
								<?php endforeach; ?>
								<td></td>
							</tr>
						</tbody>
					</table>
				</div>
			<?php endif; ?>

			<?php if ($get_data["group_by"]=="turno_id"): ?>
				<div class="row table-responsive fixMargin">
					<table id="tbl_reporte_resumen_turno" class="table table-condensed table-bordered table-striped" style="table-layout: fixed">
						<thead>
							<tr>
								<th style="width:79px !important; height: 87px !important;" colspan="1" rowspan="3">Local</th>
								<th style="width:40px !important; height: 87px !important;" colspan="1" rowspan="3">Año</th>
								<th style="width:37px !important; height: 87px !important;" colspan="1" rowspan="3">Mes</th>
								<th style="width:32px !important; height: 87px !important;" colspan="1" rowspan="3">Dia</th>
								<th style="width:48px !important; height: 87px !important;" colspan="1" rowspan="3">Turno</th>

								<th style="width:66px !important; height: 56px;" colspan="1" rowspan="3" class="bg-warning text-center">Apertura Efectivo</th>
								<th style="width:<?php echo $master_width ?>px !important;" colspan="<?php echo $table["datos_sistema"]["colspan"]; ?>" rowspan="1" class="text-center bg-">Datos del sistema</th>
								<th style="width:1515px !important;" colspan="19" rowspan="1" class="text-center bg-">Datos Fisicos</th>
								<th style="width:1545px !important;" colspan="17" rowspan="1" class="text-center bg-">Información</th>

								<th style="width:77px !important;" colspan="1" rowspan="3">Archivos</th>
                                <th style="width:77px !important;" colspan="1" rowspan="3">Registro Premios</th>
								<th style="width:34px !important;" colspan="1" rowspan="3">Opt</th>
								<th style="width:83px !important;" colspan="1" rowspan="3">Validar</th>
							</tr>
							<tr>
								<?php
								foreach ($table["datos_sistema"]["cols"] as $ds_k => $ds_v) {
									if($ds_k == 21)//disashop
									{
										?>
									<th colspan="1" rowspan="1" class="text-center bg-primary"><?php echo $ds_v["nombre"]; ?></th>
									<?php 
									}
									else 
									{
										?>
									<th colspan="<?php echo(($ds_k == 4 || $ds_k == 28) ? ($table["datos_sistema"]["num_terminals"][$ds_k] + 1) : 3); ?>" rowspan="1" class="text-center bg-primary"><?php echo $ds_v["nombre"]; ?></th><?php 
									}
								} ?>
								<th style="width:74px !important;" colspan="1" rowspan="2" class="bg-success">Resultado del día</th>
								<th style="width:96px !important;" colspan="1" rowspan="2">Devoluciones</th>
								<th style="width:71px !important;" colspan="1" rowspan="2">Devoluciones <br>Carrera de Caballos</th>
                                <th style="width:71px !important;" colspan="1" rowspan="2">Ticket TITO/Slots</th>
                                <th style="width:71px !important;" colspan="1" rowspan="2">Bonos y Promociones</th>
								<th style="width:71px !important;" colspan="1" rowspan="2">Pagos Manuales</th>
								<th style="width:74px !important;" colspan="1" rowspan="2">Resultado Real</th>
								<!--<th style="width:51px !important;" colspan="1" rowspan="2">Visa</th>
								<th style="width:81px !important;" colspan="1" rowspan="2">Mastercard</th>
								<th style="width:75px !important;" colspan="1" rowspan="2">Depósito directo de los clientes</th>-->
								<!-- <th style="width:75px !important;" colspan="1" rowspan="2">Depósito directo de los clientes</th> -->
								<th style="width:75px !important;" colspan="1" rowspan="2">Reclamación a terceros</th>
								<th style="width:75px !important;" colspan="1" rowspan="2">Transferencia IGH</th>
								<th style="width:75px !important;" colspan="1" rowspan="2">YAPE</th>
								<th style="width:75px !important;" colspan="1" rowspan="2">Tarjeta de Crédito/Débito NIUBIZ</th>
								<th style="width:75px !important;" colspan="1" rowspan="2">Tarjeta de Crédito/Débito IZIPAY</th>
								<th colspan="2" rowspan="1">Fondo</th>
								<th colspan="2" rowspan="1">Prestamos</th>
								<th colspan="3" rowspan="1">Depósitos</th>
								<th class="text-center" colspan="2" rowspan="1">Hermeticase</th>
								<!--<th style="width:51px !important;" colspan="1" rowspan="2">Deuda Slot</th>-->
								<th style="width:58px !important;" colspan="1" rowspan="2">Deuda Boveda</th>
								<th style="width:58px !important;" colspan="1" rowspan="2">Fondo Fijo</th>
								<th style="width:51px !important;" colspan="1" rowspan="2">Saldo Kasnet</th>
								<th style="width:51px !important;" colspan="1" rowspan="2">Saldo Disashop</th>
								<th style="width:67px !important;" colspan="1" rowspan="2">Mínimo Depósito</th>
								<th style="width:67px !important;" colspan="1" rowspan="2">Depósito</th>
								<th style="width:67px !important;" colspan="1" rowspan="2">Depositar <small>(Turno Anterior)</small></th>
								<th style="width:67px !important;" colspan="1" rowspan="2">Acción</th>
								<th style="width:67px !important;" colspan="1" rowspan="2">Robo Externo</th>
								<th style="width:150px !important;" colspan="3" rowspan="1" class="text-center">Efectivo</th>
								<th style="width:105px !important;" colspan="1" rowspan="2">Observaciones</th>
								<th style="width:105px !important;" colspan="1" rowspan="2">Observaciones control interno</th>
								<th style="width:58px !important;" colspan="1" rowspan="2">Estado</th>
							</tr>
							<tr>
								<?php
								foreach ($table["datos_sistema"]["cols"] as $ds_k => $ds_v) {
									if ($ds_k == 4 || $ds_k == 28) {
										for ($t=1; $t <= $table["datos_sistema"]["num_terminals"][$ds_k]; $t++) {
											?><th style="width:64px !important;" colspan="1">Billetero <?php print_r($t); ?></th><?php
										} ?>
										<th style="width:70px !important;">resultado</th>
										<?php
									}
									else if($ds_k == 21)//disashop
									{ 
									?>
										<th style="width:66px !important;">ingreso</th>
									<?php 
									}
									else {
										?>
										<th style="width:66px !important;">ingreso</th>
										<th style="width:66px !important;">salida</th>
										<th style="width:70px !important;">resultado</th>
										<?php
									}
								} ?>
								<th style="width:40px !important;">Aumento</th>
								<th style="width:58px !important;">Reduccion</th>
								<th style="width:40px !important;">Slot / Tienda</th>
								<th style="width:58px !important;">Boveda</th>
								<th style="width:40px !important;">Slot / Tienda</th>
								<th style="width:58px !important;">Boveda</th>
								<th style="width:58px !important;">Venta</th>

								<th style="width:58px !important;">Bóveda </th>
								<th style="width:58px !important;">Venta </th>

								<th style="width:66px !important;">Sistema</th>
								<th style="width:51px !important;">Fisico</th>
								<th style="width:121px !important;">Sobrante / Faltante</th>
							</tr>
						</thead>
						<tbody>

							<?php
								// ****************************************************************************
								// ****************************************************************************
								$query     = "SELECT * FROM tbl_caja_observaciones_lista WHERE state = 1 order by titulo";
								$resultOci = $mysqli->query($query);
								$listOci   = array();

								foreach($resultOci AS $row){
									$item = array(
										"id"          => $row['id'],
										"titulo"      => $row['titulo'],
										"descripcion" => $row['descripcion'],
										"tituloorden" => $row['orden']
									);
									array_push($listOci, $item);
								}

								$jsonOci = json_encode($listOci);

								echo '<div id="sec_caja_valores_globales"
										listOciJson=\''.$jsonOci.'\'
										nombreLocal="'.$tr["local_nombre"].'">
									</div>';
								// ****************************************************************************
								// ****************************************************************************

								foreach ($table["tbody"] as $fecha_operacion => $tr):
									$fecha_operacion_agrupada = $tr['ano'].'-'.$tr['mes'].'-'.$tr['dia'];
									echo '<tr>';
									foreach($tr as $key => $val){
										if($key!="local_caja_id"){
											if($key!="caja_id"){
												if($key!="validar"){
													$style = "";
													$class = "bg-success";
													$value = "";
													if($key == "local_nombre"  && strlen($val) < 10) $val .= "<br><br>";
													if($key=="turno_id" || $key=="local_nombre" || $key=="ano" || $key=="mes" || $key=="dia"){
														$style = "background-color:#fff";
													}
													if($key=="estado" && $val=="Abierto"){
														$class = "bg-danger";
													}

													if($key=="observaciones"){
														$query  = "
														SELECT cbl.*
														FROM tbl_caja c
														INNER JOIN tbl_caja_observaciones_lista cbl
														ON cbl.id = c.id_oci
														WHERE c.id = ".$tr["caja_id"];

														$result      = $mysqli->query($query)->fetch_assoc();
														$idCaja      = $tr["caja_id"];
														$idOci       = 0;
														$tituloOci   = "";

														$existOci = '<div class="_add_oci" onclick="modalAddOci('.$idCaja.','.$idOci.',\''.$tituloOci.'\')">+ </div>';

														if($result != null){
															$idOci       = $result["id"];
															$tituloOci   = $result["titulo"];
															$existOci    = '
															<div onclick="modalAddOci('.$idCaja.','.$idOci.',\''.$tituloOci.'\')">
																<div class="_description_oci view_more_btn" title="'.$tituloOci.'">'.substr($tituloOci,0,14).'...</div>
															</div>
															';
														}

														if(strlen($val)>10){
															$value = '
																	<i class="view_more_btn" title="'.$val.'">'.substr($val, 0, 14).'...</i>
																</td>
																<td>
																	<div class="c_btn_add_oci">
																		<div class="c_btn_add_oci" id="caja_'.$idCaja.'_oci">
																			'.$existOci.'
																		</div>
																	</div>
															';
														}else{
																$value = $value.'
																	</td>
																	<td>
																		<div class="c_btn_add_oci" id="caja_'.$idCaja.'_oci">
																			'.$existOci.'
																		</div>
																';
															}
													}else{
															$value = $val;
														}
														if(($key == 'ano') || ($key == 'mes') || ($key == 'dia') || ($key == 'turno_id')){
														  echo'
															  <td style="'.$style.'" class="">
															  '.$value.'
															  </td>
														  ';
														}else{

														  echo '
															  <td data-nombre="'.$key.'" style="'.$style.'" class="">
															  '.
																(is_numeric($value) ? number_format($value,2) : $value)
																.'
															</td>
														  ';

														}
													}
												}
											}
										} // END IF local_caja_id
										?>
								   <td style="text-align: center;">
										<?php
											$caja_archivo_command = "SELECT id,ext,size, archivo FROM tbl_archivos WHERE item_id = '".$tr["caja_id"]."' AND estado = '1'";


											$archivo_respuesta = $mysqli->query($caja_archivo_command);
											$archivo_regitros = array();
											if(mysqli_num_rows($archivo_respuesta)>0){
												while($row_archivo_selected = $archivo_respuesta->fetch_assoc()) {
													array_push($archivo_regitros, $row_archivo_selected["ext"]."@".$row_archivo_selected["size"]."@".$row_archivo_selected["archivo"]);
												}
												?>
												<a target="_self" data-archivos="<?php echo implode(",", $archivo_regitros)?>" data-id="<?php echo $tr["caja_id"]; ?>" href="#" class="registro_caja_archivos" data-toggle="tooltip" data-placement="top" title="Ver Archivos"><i class="glyphicon glyphicon-level-up" style="font-size: 20px;"></i></a>
												<?php
											}
										?>
									</td>
                                    <td style="text-align: center;">
                                        <?php
                                        $caja_archivo_command = "
                                            SELECT
                                               rp.id                                               
                                            FROM
                                                tbl_archivos a
                                            INNER JOIN
                                                tbl_registro_premios rp ON a.item_id = rp.id
                                            INNER JOIN
                                                tbl_caja c  ON c.id = rp.caja_id
                                            WHERE
                                                  a.tipo LIKE 'foto_%'
                                              AND a.tabla ='tbl_registro_premios'
                                              AND c.id = $tr[caja_id]
                                        ";

                                        $archivo_respuesta = $mysqli->query($caja_archivo_command);
                                        $archivo_regitros = array();
                                        if(mysqli_num_rows($archivo_respuesta) > 0){
                                            ?>
                                            <span style="color: #659ce0; cursor: pointer" id="btn_show_images" data-id="<?= $tr["caja_id"] ?>"><i class="glyphicon glyphicon-level-up" style="font-size: 20px;"></i></span>
                                            <?php
                                        }
                                        ?>
                                    </td>
									<td>
										<a target="_blank" href="./?sec_id=caja&item_id=<?php echo $tr["caja_id"]; ?>"><i class="glyphicon glyphicon-new-window"></i></a>
									</td>
									<td>
										<?php if (($login['area_id'] == 22 && !$tr["validar"]) || ($login['area_id'] == 3 && $tr["validar"]) || $login['area_id'] == 6 || $login['area_id'] == 34 ): ?>
										<input
										name="estado"
										class="switch save_data"
										data-area="<?php echo $login['area_id']; ?>"
										type="checkbox"
										value="<?php if ($tr['validar']) {
									echo $tr['validar'];
								} else {
									?>0<?php
								} ?>"
										<?php if ($tr['validar']) {
									?>checked="checked"<?php
								} ?>
										data-table="tbl_caja"
										data-id="<?php echo $tr["caja_id"]; ?>"
										data-local="<?php echo $tr["local_caja_id"]; ?>"
										data-fecha_operacion="<?php echo $fecha_operacion_agrupada; ?>"
										data-col="validar"
										data-on-value="1"
										data-off-value="0">
										<?php else: ?>
											<p class="<?php echo $tr["validar"] ? "text-success" : "text-danger" ?>">
												<?php echo $tr["validar"] ? "Validado" : "No Validado" ?>
											</p>
										<?php endif; ?>
									</td>
								</tr>
							<?php endforeach ?>
							<tr>
								<!-- <th colspan="6">Total</th> -->
								<?php foreach ($table["total"] as $key => $t_val): ?>
									<?php if($key!="local_caja_id"){?>
										<td data-nombre="<?php echo $key;?>">
											<b><?php if($key=="deuda_boveda"){ echo '';}else{echo ($key == "local_nombre" && strlen($t_val) < 10) ? $t_val."<br><br>" : sprintf("%.2f", (double)$t_val);} ?></b>
										</td>
									<?php } ?>
								<?php endforeach; ?>
								<td></td>
								<td></td>
								<td></td>
							</tr>
						</tbody>
					</table>
				</div>
			<?php endif; ?>

		<?php
		} else {
			?>
			<div class="alert alert-danger alert-dismissible fade in" role="alert">
				<strong>No hay información para esta busqueda.</strong>
			</div>

			<?php
		}

		?>
		<!-- Modal -->
		<div id="modalAddOci" class="modal fade" role="dialog">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal">&times;</button>
						<h4 class="modal-title">Observaciones control interno:</h4>
					</div>
					<div class="modal-body">
						<div class="row" id="modalOciBody">
							<input type="hidden" id="modalIdCajaSeleccionada" value="">
							<div class="col-xs-12" style="font-weight: bold; font-size: 17px; margin-top: 5px;">
								Observación
							</div>
							<div class="col-xs-12" style="margin-top: 5px;">
								<div class="form-group">
									<select id="modalSelectListOci" class="form-control" style=" height: 30px;">
									</select>
								</div>
							</div>
							<div class="col-xs-12" style="font-weight: bold; font-size: 17px; margin-top: 10px;">
								Descripción
							</div>
							<div class="col-xs-12" style="margin-top: 5px;">
								<div id="modalDesOci"
									style="background: #fff; padding: 10px; font-size: 15px; border: 1px dotted #ddf;">
									Seleccionar observación
								</div>
							</div>
						</div>
						<div id="modalOciBodyMsg">
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-danger pull-left" style="display: none;" id="btnRemoveOci">Eliminar observación</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
						<button type="button" class="btn btn-success pull-right" id="btnAddOci">Guardar</button>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
} //FIN IF POST SEC CAJA REPORTE

if (isset($_POST["sec_caja_validado"])) {
	$get_data = $_POST["sec_caja_validado"];
	//print_r($get_data);
	//exit();
	$id = $get_data['id'];
	$sql = "SELECT validar FROM tbl_caja
				where id='".$id."'
				order by id desc
				limit 1";


	$result = $mysqli->query($sql);

	if($mysqli->error) {
		print_r($mysqli->error);
		exit();
	}

	if($result->num_rows>0){
		$registro = $result->fetch_assoc();
		echo $registro['validar'];
	}
	else{
		echo"no_existe";
	}
};

if (isset($_POST["sec_caja_existe_posterior"])) {
	$get_data = $_POST["sec_caja_existe_posterior"];
	//print_r($get_data);
	//exit();
	$local_id = $get_data['local'];
	$id = $get_data['id'];
	$sql = "SELECT c.id ,CONCAT('[',l.id,']',' ',l.nombre) AS local_nombre ,
			CASE
			WHEN u.usuario IS NOT NULL
			THEN IF(u.personal_id,CONCAT('[',u.usuario,']',' ',IFNULL(p.nombre,''),' ',IFNULL(p.apellido_paterno,'')),u.usuario)
			ELSE '' END AS usuario_nombre ,lc.nombre AS caja_nombre ,ct.nombre AS caja_tipo ,IFNULL(c.turno_id,'-') AS turno ,
			c.fecha_operacion ,c.fecha_apertura ,
			c.fecha_cierre ,c.estado ,
			IF(c.estado=1,'Cerrado',IF(c.estado=2,'Re-Abierto','Abierto')) as estado_nombre ,
			c.validar
			FROM tbl_caja c
			LEFT JOIN tbl_local_cajas lc ON (lc.id = c.local_caja_id)
			LEFT JOIN tbl_caja_tipos ct ON (ct.id = lc.caja_tipo_id)
			LEFT JOIN tbl_locales l ON (l.id = lc.local_id)
			LEFT JOIN tbl_usuarios u ON (u.id = c.usuario_id)
			LEFT JOIN tbl_personal_apt p ON (p.id = u.personal_id)
			WHERE lc.local_id = '".$local_id."' and c.id>'".$id."'
			ORDER BY c.fecha_operacion DESC, l.id ASC, c.fecha_apertura DESC LIMIT 10";


	$result = $mysqli->query($sql);
	if($mysqli->error) {
		print_r($mysqli->error);
		exit();
	}

	if($result->num_rows>0){
		echo $result->num_rows;
	}
	else{
		echo 0;
	}
}

if (isset($_POST["sec_caja_validado_anterior"])) {
	$get_data = $_POST["sec_caja_validado_anterior"];
	//print_r($get_data);
	//exit();
	$fecha = $get_data['fecha'];
	$local_id = $get_data['local'];
	$id = $get_data['id'];

	$sql = "SELECT validar FROM tbl_caja
				where fecha_operacion<='".$fecha."'
				and local_caja_id ='".$local_id."'
				and id<'".$id."'
				order by id desc
				limit 1";


	$result = $mysqli->query($sql);

	if($mysqli->error) {
		print_r($mysqli->error);
		exit();
	}

	if($result->num_rows>0){
		$registro = $result->fetch_assoc();
		echo $registro['validar'];
	}
	else{
		echo"no_registro";
	}
}

if (isset($_POST["sec_caja_validado_posterior"])) {
	$get_data = $_POST["sec_caja_validado_posterior"];
	//print_r($get_data);
	//exit();
	$fecha = $get_data['fecha'];
	$local_id = $get_data['local'];
	$id = $get_data['id'];

	$sql = "SELECT validar FROM tbl_caja
				where fecha_operacion>='".$fecha."'
				and local_caja_id ='".$local_id."'
				and id>'".$id."'
				order by id ASC
				limit 1";


	//echo $sql;exit();
	$result = $mysqli->query($sql);

	if($mysqli->error) {
		print_r($mysqli->error);
		exit();
	}

	if($result->num_rows>0){
		$registro = $result->fetch_assoc();
		echo $registro['validar'];
	}
	else{
		echo"no_registro";
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_caja_reporte_obtener_locales") {
	include("db_connect.php");
	include("sys_login.php");

    try {
		// Filtrado de locales por permisos

		$permiso_locales="";
			
		if($login && $login["usuario_locales"]){
			$permiso_locales .=" AND id IN (".implode(",", $login["usuario_locales"]).") ";
			}

		$query = "SELECT 
                    id, 
                    CONCAT('[',cc_id,'] ',nombre) AS nombre
                FROM tbl_locales 
                WHERE estado = '1' AND nombre IS NOT NULL AND cc_id IS NOT NULL
                $permiso_locales
                ORDER BY nombre ASC";

        $stmt = $mysqli->prepare($query);

        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $mysqli->error);
        }

        $stmt->execute();

        $list_query = $stmt->get_result();
        $list = $list_query->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        if (count($list) === 0) {
            $result["http_code"] = 400;
            $result["result"] = "El concepto no existe.";
        } else {
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestion.";
            $result["result"] = $list;
        }

        echo json_encode($result);
    } catch (Exception $e) {
        // Registra el error en un archivo de registro o en otro lugar seguro
        $result["consulta_error"] = $e->getMessage();
        $result["http_code"] = 500;
        $result["result"] = "Error en la consulta. Comunicarse con Soporte.";

        echo json_encode($result);
    }
    exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_caja_reporte_obtener_locales_all") {
	include("db_connect.php");
	include("sys_login.php");

    try {
		// Filtrado de locales por permisos

		$permiso_locales="";
			
		if($login && $login["usuario_locales"]){
			$permiso_locales .=" AND id IN (".implode(",", $login["usuario_locales"]).") ";
			}

		$query = "SELECT 
                    id
                FROM tbl_locales l
                WHERE
				(l.red_id IN (1,4,6,7,8,9,16) OR l.id = 200) 
				AND nombre IS NOT NULL 
				AND cc_id IS NOT NULL
				AND l.operativo = 1
				AND l.estado = 1
                $permiso_locales

                ORDER BY nombre ASC";

        $stmt = $mysqli->prepare($query);

        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $mysqli->error);
        }

        $stmt->execute();

        $list_query = $stmt->get_result();
        $list = $list_query->fetch_all(MYSQLI_ASSOC);

        $stmt->close();


		// CANTIDAD LOCALES
		$query_cantidad_locales = "SELECT 
                    count(id) as cantidad_locales
                FROM tbl_locales 
                WHERE estado = '1' AND nombre IS NOT NULL AND cc_id IS NOT NULL
                $permiso_locales
                ORDER BY nombre ASC";

        $stmt_cantidad_locales = $mysqli->prepare($query_cantidad_locales);

        if (!$stmt_cantidad_locales) {
            throw new Exception("Error en la preparación de la consulta: " . $mysqli->error);
        }

        $stmt_cantidad_locales->execute();

        $list_query_cantidad_locales = $stmt_cantidad_locales->get_result();
        $list_cantidad_locales = $list_query_cantidad_locales->fetch_all(MYSQLI_ASSOC);

        $stmt_cantidad_locales->close();

		$result_locales = [];
		$result_locales["locales"] = $list;
		$result_locales["cantidad_locales"] = $list_cantidad_locales;

        if (count($list) === 0) {
            $result["http_code"] = 400;
            $result["result"] = "El concepto no existe.";
        } else {
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestion.";
            $result["result"] = $result_locales;
			// $result["cantidad_locales"] = $list_cantidad_locales;
        }

        echo json_encode($result);
    } catch (Exception $e) {
        // Registra el error en un archivo de registro o en otro lugar seguro
        $result["consulta_error"] = $e->getMessage();
        $result["http_code"] = 500;
        $result["result"] = "Error en la consulta. Comunicarse con Soporte.";

        echo json_encode($result);
    }
    exit();
}

?>