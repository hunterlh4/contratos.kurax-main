<?php
include '/var/www/html/sys/helpers.php';
include '/var/www/html/sys/db_connect.php';


$data = [
	'servicio_id'=>3,
	'proceso_id'=>'b2f64964fe33da34fd397429ea16a5a5',
	'fecha'=>$_GET["fecha"]
];
transacciones_build_liquidaciones($data);
function transacciones_build_liquidaciones($data){
	global $mysqli;
	global $return;
	global $login;
	//print_r($data); exit();
	if(array_key_exists("servicio_id", $data)){
		$date = date("Y-m-d H:i:s");
		$cabeceras = [];
		$data["fecha_inicio"] = date("Y-m-d",strtotime(substr($data["fecha"],0,15)));
		$data["fecha_fin"] = date("Y-m-d",strtotime($data["fecha_inicio"]." +1 day"));

		$return["data"]=$data;
		$return["servicio_id"] = $data["servicio_id"];

		$proceso_id = false;
		if(array_key_exists("proceso_id", $data)){
			$proceso_id = $data["proceso_id"];
			if($proceso_id=="false"){
				$proceso_id = false;
			}
		}

		$testing = false;
		// $testing = true;
	
		$return["proceso_id"]=$proceso_id;

		
		if((int)$data["servicio_id"] == 9){
			
		} elseif((int)$data["servicio_id"] == 12){ // TORITO
			
		}
		elseif((int)$data["servicio_id"] == 15){//calimaco
			
		}/*fin servicio 15 */
		
		else{
			$locales = [];
			$command_locales = "
				SELECT 
					canal_de_venta_id,
					local_id
				FROM tbl_local_proveedor_id 
				WHERE servicio_id = '".$data["servicio_id"]."'
			";
			$query_locales = $mysqli->query($command_locales);
			while($l_d=$query_locales->fetch_assoc()){
				if(array_key_exists($l_d["local_id"], $locales)){
					if(!in_array($l_d["canal_de_venta_id"], $locales[$l_d["local_id"]])){
						$locales[$l_d["local_id"]][]=$l_d["canal_de_venta_id"];
					}
				}else{
					$locales[$l_d["local_id"]][]=$l_d["canal_de_venta_id"];
				}
			}
			//$mysqli->next_result();
			
			ksort($locales);

			$call_param = "'".$data["fecha_inicio"]."','".$data["fecha_fin"]."','".$data["servicio_id"]."'";

			$total_sumas=[];
			$total_sumas_command = "CALL get_sumas_test(".$call_param.")";
			$total_sumas_query = $mysqli->query($total_sumas_command);
			while($ts=$total_sumas_query->fetch_assoc()){
				$total_sumas[$ts["local_id"]][$ts["canal_de_venta_id"]]["total_apostado"]=$ts["total_apostado"];
				$total_sumas[$ts["local_id"]][$ts["canal_de_venta_id"]]["total_ganado"]=$ts["total_ganado"];
			}
			$mysqli->next_result();


			$total_resumen_turnover=[];
			$get_terminal_turnover_command = "CALL get_resumen_turnover(".$call_param.")";
			$get_terminal_turnover_query = $mysqli->query($get_terminal_turnover_command);
			while($tt=$get_terminal_turnover_query->fetch_assoc()){
				$total_resumen_turnover[$tt["local_id"]]=$tt;
			}
			$mysqli->next_result();

			$total_terminal_turnover=[];
			$get_terminal_turnover_command = "CALL get_terminal_turnover(".$call_param.")";
			$get_terminal_turnover_query = $mysqli->query($get_terminal_turnover_command);
			while($tt=$get_terminal_turnover_query->fetch_assoc()){
				$total_terminal_turnover[$tt["local_id"]]=$tt;
			}
			$mysqli->next_result();

			$total_cashdesk_turnover=[];
			$get_cashdesk_turnover_command = "CALL get_cashdesk_turnover(".$call_param.")";
			$get_cashdesk_turnover_query = $mysqli->query($get_cashdesk_turnover_command);
			while($tt=$get_cashdesk_turnover_query->fetch_assoc()){
				$total_cashdesk_turnover[$tt["local_id"]]=$tt;
			}
			$mysqli->next_result();

			$num_tickets=[];
			$num_tickets_command = "CALL get_num_tickets_test(".$call_param.")";
			$num_tickets_query = $mysqli->query($num_tickets_command);
			while($nt=$num_tickets_query->fetch_assoc()){
				$num_tickets[$nt["local_id"]][$nt["canal_de_venta_id"]]=$nt["num_tickets"];
			}
			$mysqli->next_result();

			$num_tickets_resumen=[];
			$num_tickets_resumen_command = "CALL get_num_tickets_resumen(".$call_param.")";
			$num_tickets_resumen_query = $mysqli->query($num_tickets_resumen_command);
			while($ntr=$num_tickets_resumen_query->fetch_assoc()){
				$num_tickets_resumen[$ntr["local_id"]][$ntr["canal_de_venta_id"]]=$ntr["num_tickets"];
			}
			//echo var_dump($num_tickets_resumen);exit();
			$mysqli->next_result();

			$num_tickets_ganados=[];
			$num_tickets_ganados_command = "CALL get_num_tickets_ganados(".$call_param.")";
			$num_tickets_ganados_query = $mysqli->query($num_tickets_ganados_command);
			while($ntg=$num_tickets_ganados_query->fetch_assoc()){
				$num_tickets_ganados[$ntg["local_id"]][$ntg["canal_de_venta_id"]]=$ntg["num_tickets"];
			}
			$mysqli->next_result();

			$num_tickets_ganados_pagados=[];
			$num_tickets_ganados_pagados_command = "CALL get_num_tickets_ganados_pagados(".$call_param.")";
			$num_tickets_ganados_pagados_query = $mysqli->query($num_tickets_ganados_pagados_command);
			while($ntgp=$num_tickets_ganados_pagados_query->fetch_assoc()){
				$num_tickets_ganados_pagados[$ntgp["local_id"]][$ntgp["canal_de_venta_id"]]=$ntgp["num_tickets"];
			}
			$mysqli->next_result();

			$total_pagados=[];
			$get_pagados_command = "CALL get_pagados_test(".$call_param.")";
			$get_pagados_query = $mysqli->query($get_pagados_command);
			while($tp=$get_pagados_query->fetch_assoc()){
				$total_pagados[$tp["local_id"]][$tp["canal_de_venta_id"]]["total_ganado_pagado"]=$tp;
			}
			$mysqli->next_result();

			$total_ganados=[];
			$get_ganados_command = "CALL get_ganados(".$call_param.")";
			$get_ganados_query = $mysqli->query($get_ganados_command);
			while($tp=$get_ganados_query->fetch_assoc()){
				$total_ganados[$tp["local_id"]][$tp["canal_de_venta_id"]]=$tp["total_ganado"];
			}
			//echo var_dump($total_ganados);exit();
			$mysqli->next_result();

			$caja_total_pagados=[];
			$get_pagados_command = "CALL get_pbet_pagados(".$call_param.")";
			$get_pagados_query = $mysqli->query($get_pagados_command);
			while($tp=$get_pagados_query->fetch_assoc()){
				$caja_total_pagados[$tp["local_id"]]=$tp["total_ganado_pagado"];
			}
			$mysqli->next_result();		

			$total_pagados_en_otra_tiendad=[];
			$get_pagados_en_otra_tienda_command = "CALL get_pagados_en_otra_tienda_test(".$call_param.")";
			$get_pagados_en_otra_tienda_query = $mysqli->query($get_pagados_en_otra_tienda_command);
			while($tp=$get_pagados_en_otra_tienda_query->fetch_assoc()){
				$total_pagados_en_otra_tiendad[$tp["local_id"]][$tp["canal_de_venta_id"]]["total_pagado"]=$tp;				
			}
			$mysqli->next_result();

			$total_pagados_de_otra_tiendad=[];
			$get_pagados_de_otra_tienda_command = "CALL get_pagados_de_otra_tienda_test(".$call_param.")";
			$get_pagados_de_otra_tienda_query = $mysqli->query($get_pagados_de_otra_tienda_command);
			while($tp=$get_pagados_de_otra_tienda_query->fetch_assoc()){
				$total_pagados_de_otra_tiendad[$tp["paid_local_id"]][$tp["paid_canal_de_venta_id"]]["total_pagado"]=$tp;	
			}
			$mysqli->next_result();

			$cashdesk_balance=[];
			$get_cashdesk_balance_command = "CALL get_cashdesk_balance(".$call_param.")";
			$get_cashdesk_balance_query = $mysqli->query($get_cashdesk_balance_command);
			while($tp=$get_cashdesk_balance_query->fetch_assoc()){
				$cashdesk_balance[$tp["local_id"]]=$tp;	
			}
			$mysqli->next_result();

			$terminal_premios_pagados=[];
			$get_terminal_premios_pagados_command = "CALL get_terminal_premios_pagados(".$call_param.")";
			$get_terminal_premios_pagados_query = $mysqli->query($get_terminal_premios_pagados_command);
			while($tp=$get_terminal_premios_pagados_query->fetch_assoc()){
				$terminal_premios_pagados[$tp["local_id"]]=$tp["premios_pagados"];	
			}
			$mysqli->next_result();

			$local_formulas = [];
			$get_local_formulas_command = "CALL get_locales_formulas()";
			$get_local_formulas_query = $mysqli->query($get_local_formulas_command);
			while($lf=$get_local_formulas_query->fetch_assoc()){
				if($lf["tipo"]=="normal"){
					$local_formulas[$lf["local_id"]][$lf["canal_de_venta_id"]]=$lf;
				}elseif($lf["tipo"]=="quiebre"){
					$local_formulas[$lf["local_id"]][$lf["canal_de_venta_id"]][]=$lf;
				}elseif($lf["tipo"]=="modular"){
					$local_formulas[$lf["local_id"]][$lf["canal_de_venta_id"]][]=$lf;
				}
			}
			$mysqli->next_result();

			$apostado_odds = [];
			$get_get_apostado_odds_command = "CALL get_apostado_odds(".$call_param.")";
			$get_get_apostado_odds_query = $mysqli->query($get_get_apostado_odds_command);
			while($ao=$get_get_apostado_odds_query->fetch_assoc()){
				$apostado_odds[$ao["local_id"]][$ao["canal_de_venta_id"]][]=$ao;
			}
			$mysqli->next_result();			

		
			

			if($proceso_id){
				$cabeceras=[];
				
				foreach ($locales as $local_id => $local_csdv) {
					foreach ($local_csdv as $key => $cdv_id) {
						$zona = $mysqli->query("SELECT zona_id FROM tbl_locales WHERE id = ".$local_id)->fetch_assoc();
						$cabecera = [];
							$cabecera["at_unique_id"]=md5($proceso_id.$data["fecha_inicio"].$cdv_id.$data["servicio_id"].$local_id);
							$cabecera["proceso_unique_id"]=$proceso_id;
							$cabecera["fecha"]=$data["fecha_inicio"];
							$cabecera["fecha_proceso"]=date("Y-m-d H:i:s");
							$cabecera["local_id"]=$local_id;
							if(isset($zona["zona_id"])) $cabecera["zona_id"]=(int)$zona["zona_id"];
							$cabecera["servicio_id"]=$data["servicio_id"];
							$cabecera["canal_de_venta_id"]=$cdv_id;
							$cabecera["producto_id"] = in_array((int)$cabecera["canal_de_venta_id"], [15,16,17,19,23,24,25,26,27]) ? 1: 2;


							$cabecera["num_tickets"]=0;
							if(array_key_exists($local_id, $num_tickets)){
								if(array_key_exists($cdv_id, $num_tickets[$local_id])){
									$cabecera["num_tickets"]=$num_tickets[$local_id][$cdv_id];
								}
							}
							if(array_key_exists($local_id, $num_tickets_resumen)){
								if(array_key_exists($cdv_id, $num_tickets_resumen[$local_id])){
									if($cdv_id==18 || $cdv_id==21 || $cdv_id==24 || $cdv_id==25 || $cdv_id==26 || $cdv_id==27){
										$cabecera["num_tickets"]=$num_tickets_resumen[$local_id][$cdv_id];
									}
								}
							}
							$cabecera["num_tickets_ganados"]=0;
							if(array_key_exists($local_id, $num_tickets_ganados)){
								if(array_key_exists($cdv_id, $num_tickets_ganados[$local_id])){
									$cabecera["num_tickets_ganados"]=$num_tickets_ganados[$local_id][$cdv_id];
								}
							}
							$cabecera["num_tickets_ganados_pagados"]=0;
							if(array_key_exists($local_id, $num_tickets_ganados_pagados)){
								if(array_key_exists($cdv_id, $num_tickets_ganados_pagados[$local_id])){
									$cabecera["num_tickets_ganados_pagados"]=$num_tickets_ganados_pagados[$local_id][$cdv_id];
								}
							}

							$cabecera["num_registros"]=$cabecera["num_tickets"];
							$cabecera["moneda_id"]=1;
							$cabecera["total_apostado"]=0;	
							$cabecera["cashdesk_apostado"]=0;
							if(array_key_exists($local_id, $total_sumas)){
								if(array_key_exists($cdv_id, $total_sumas[$local_id])){
									$cabecera["total_apostado"]=$total_sumas[$local_id][$cdv_id]["total_apostado"];
									// $cabecera["total_ganado"]=$total_sumas[$local_id][$cdv_id]["total_ganado"];
								}
							}
							if($cdv_id==16){
								if(array_key_exists($local_id, $cashdesk_balance)){
									$cabecera["cashdesk_apostado"] = $cashdesk_balance[$local_id]["apostado"];
								}
							}
							$cabecera["total_ganado"]=0;
							if($cdv_id==17){ //SBT-NEGOCIOS
								if(array_key_exists($local_id, $total_ganados)){
									if(array_key_exists($cdv_id, $total_ganados[$local_id])){
										$cabecera["total_ganado"] = $total_ganados[$local_id][$cdv_id];
									}
								}						
							}elseif($cdv_id==16){ //PBET
								if(array_key_exists($local_id, $total_ganados)){
									if(array_key_exists($cdv_id, $total_ganados[$local_id])){
										$cabecera["total_ganado"] = $total_ganados[$local_id][$cdv_id];
									}
								}
								if(array_key_exists($local_id, $cashdesk_balance)){
									$cabecera["cashdesk_ganado"] = $cashdesk_balance[$local_id]["ganado"];
								}
							}else{
								if(array_key_exists($local_id, $total_sumas)){
									if(array_key_exists($cdv_id, $total_sumas[$local_id])){
										$cabecera["total_ganado"]=$total_sumas[$local_id][$cdv_id]["total_ganado"];
									}
								}
							}

							$cabecera["pagado_en_otra_tienda"]=0;
							$cabecera["pagado_de_otra_tienda"]=0;
							if(array_key_exists($local_id, $total_pagados_en_otra_tiendad)){
								if(array_key_exists($cdv_id, $total_pagados_en_otra_tiendad[$local_id])){
									$t_pagado_en_otra_tienda = $total_pagados_en_otra_tiendad[$local_id][$cdv_id]["total_pagado"];
									$cabecera["pagado_en_otra_tienda"]=$t_pagado_en_otra_tienda["total_pagado"];
								}
							}
							if(array_key_exists($local_id, $total_pagados_de_otra_tiendad)){
								if(array_key_exists($cdv_id, $total_pagados_de_otra_tiendad[$local_id])){
									$t_pagado_de_otra_tienda = $total_pagados_de_otra_tiendad[$local_id][$cdv_id]["total_pagado"];
									$cabecera["pagado_de_otra_tienda"]=$t_pagado_de_otra_tienda["total_pagado"];
								}
							}


							$cabecera["total_pagado"]=0;
							$cabecera["cashdesk_pagado"]=0;
							
							if($cdv_id==17){ //SBT-NEGOCIOS
								if(array_key_exists($local_id, $terminal_premios_pagados)){
									$cabecera["total_pagado"] = $terminal_premios_pagados[$local_id];
								}						
							}elseif($cdv_id==16){ //PBET
								if(array_key_exists($local_id, $caja_total_pagados)){
									$cabecera["total_pagado"] = $caja_total_pagados[$local_id];
								}
								if(array_key_exists($local_id, $cashdesk_balance)){
									$cabecera["cashdesk_pagado"] = $cashdesk_balance[$local_id]["pagado"];
								}
							}elseif($cdv_id==15 || $cdv_id==24 || $cdv_id==25 || $cdv_id==26 || $cdv_id==27){
								$cabecera["total_pagado"]=$cabecera["total_ganado"];
							}else{
								if(array_key_exists($local_id, $total_pagados)){
									if(array_key_exists($cdv_id, $total_pagados[$local_id])){
										$t_pagados = $total_pagados[$local_id][$cdv_id]["total_ganado_pagado"];
										$cabecera["total_pagado"]=$t_pagados["total_ganado_pagado"];
									}
								}
							}


							$cabecera["total_depositado"]=0;
							$cabecera["total_anulado_retirado"]=0;

							if($cdv_id==17){ //SBT-NEGOCIOS
								if(array_key_exists($local_id, $total_terminal_turnover)){
									$cabecera["total_depositado"]+=$total_terminal_turnover[$local_id]["total_income"];
									$cabecera["total_anulado_retirado"]+=$total_terminal_turnover[$local_id]["total_terminal_withdraw"];
								}
								if(array_key_exists($local_id, $total_cashdesk_turnover)){
									$cabecera["total_depositado"]+=$total_cashdesk_turnover[$local_id]["terminal_income"];
									$cabecera["total_anulado_retirado"]+=$total_cashdesk_turnover[$local_id]["terminal_withdraw"];
								}
								$cabecera["total_anulado_retirado"]-=$cabecera["pagado_de_otra_tienda"];
							}
							if($cdv_id==18){ //JV GLOBAL BET
								if(array_key_exists($local_id, $total_resumen_turnover)){
									$cabecera["total_depositado"]=$total_resumen_turnover[$local_id]["total_income"];
									$cabecera["total_anulado_retirado"]=$total_resumen_turnover[$local_id]["total_withdraw"];
								}
							}
							if($cdv_id==21){ //JV GOLDEN RACE
								if(array_key_exists($local_id, $total_resumen_turnover)){
									$cabecera["total_depositado"]=$total_resumen_turnover[$local_id]["total_income"];
									$cabecera["total_anulado_retirado"]=$total_resumen_turnover[$local_id]["total_withdraw"];
								}
							}
							
							$cabecera["total_depositado_web"]=0;
							$cabecera["total_retirado_web"]=0;
							$cabecera["total_caja_web"]=0;

							// if($cdv_id==16){ //PBET
							// 	if(array_key_exists($local_id, $total_cashdesk_turnover)){
							// 		$cabecera["total_depositado_web"]=$total_cashdesk_turnover[$local_id]["total_deposit"];
							// 		$cabecera["total_retirado_web"]=$total_cashdesk_turnover[$local_id]["total_withdraw"];
							// 	}
							// 	$cabecera["total_caja_web"]=($cabecera["total_depositado_web"]-$cabecera["total_retirado_web"]);
							// }

							$cabecera["total_pagos_fisicos"]=0;

							if($cdv_id==17){
								$cabecera["total_pagos_fisicos"]=($cabecera["total_pagado"]-$cabecera["pagado_en_otra_tienda"]);
							}else{
								// $cabecera["total_pagos_fisicos"]=($cabecera["total_pagado"]-$cabecera["pagado_en_otra_tienda"]+$cabecera["pagado_de_otra_tienda"]);
								$cabecera["total_pagos_fisicos"]=($cabecera["total_pagado"]+$cabecera["pagado_de_otra_tienda"]-$cabecera["pagado_en_otra_tienda"]);
							}

							$cabecera["caja_fisico"]=0;
							$cabecera["cashdesk_produccion"]=0;
							$cabecera["cashdesk_caja_fisico"]=0;
							if($cdv_id==15){		
								$cabecera["total_produccion"]=($cabecera["total_apostado"]-$cabecera["total_pagado"]);					
							}elseif($cdv_id==16){
								$cabecera["total_produccion"]=($cabecera["total_apostado"]-$cabecera["total_pagado"]);
								$cabecera["cashdesk_produccion"]=($cabecera["cashdesk_apostado"]-$cabecera["cashdesk_pagado"]);
								// $cabecera["caja_fisico"]=($cabecera["total_apostado"]-$cabecera["total_pagado"]-$cabecera["pagado_de_otra_tienda"]+$cabecera["pagado_en_otra_tienda"]);
								// $cabecera["caja_fisico"]=($cabecera["total_apostado"]-$cabecera["total_pagado"]+$cabecera["total_caja_web"]-$cabecera["pagado_de_otra_tienda"]+$cabecera["pagado_en_otra_tienda"]);
								$cabecera["caja_fisico"]=($cabecera["total_apostado"]-$cabecera["total_pagado"]+$cabecera["total_caja_web"]-$cabecera["pagado_de_otra_tienda"]+$cabecera["pagado_en_otra_tienda"]);
								$cabecera["cashdesk_caja_fisico"]=($cabecera["cashdesk_apostado"]-$cabecera["cashdesk_pagado"]-$cabecera["pagado_de_otra_tienda"]+$cabecera["pagado_en_otra_tienda"]);
							}elseif(in_array($cdv_id, [17,19])){
								$cabecera["total_produccion"]=($cabecera["total_depositado"]-$cabecera["total_anulado_retirado"]-$cabecera["total_pagado"]);
								$cabecera["caja_fisico"] = ($cabecera["total_depositado"] - $cabecera["total_anulado_retirado"] - $cabecera["total_pagado"] + $cabecera["pagado_en_otra_tienda"] - $cabecera["pagado_de_otra_tienda"]);
								// $cabecera["caja_fisico"] = ($cabecera["total_pagado"] - $cabecera["pagado_en_otra_tienda"]);
							}elseif($cdv_id==24 || $cdv_id==25 || $cdv_id==26 || $cdv_id==27){
								$cabecera["total_produccion"]=($cabecera["total_apostado"]-$cabecera["total_ganado"]);
								// $cabecera["caja_fisico"] = ($cabecera["total_pagado"] - $cabecera["pagado_en_otra_tienda"]);
							}
							else{
								$cabecera["total_produccion"]=($cabecera["total_apostado"]-$cabecera["total_pagado"]);
								$cabecera["caja_fisico"]=$cabecera["total_produccion"];
							}

							$cabecera["resultado_negocio"]=($cabecera["total_apostado"]-$cabecera["total_ganado"]);	



							$cabecera["cashdesk_balance"]=0;
							if($cdv_id==16){
								if(array_key_exists($local_id, $cashdesk_balance)){
									$cabecera["cashdesk_balance"]=$cashdesk_balance[$local_id]["balance"];
								}
							}

							$cabecera["porcentaje_cliente"]=0;
							$cabecera["total_cliente"]=0;
							$cabecera["porcentaje_freegames"]=0;
							$cabecera["total_freegames"]=0;

						
							if(array_key_exists($local_id, $local_formulas)){
								// $return["local_formula"]=$local_formulas[$local_id];
								if($cdv_id==16){
									if(array_key_exists(16, $local_formulas[$local_id])){
										$formula = $local_formulas[$local_id][16];
										if(array_key_exists(0, $formula)){
											// $return["formula"]=$formula[0];
											if($formula[0]["formula_id"]==17){
												$f = get_condicionales($formula[0]);
												if($formula[0]["tipo_contrato_id"]==4){ // Participacion FG
													$con = reset($f);
													$return["con_".$cdv_id]=$con;
													if($con["donde"]=="apostado"){
														// $cabecera["total_freegames"] = ($cabecera["total_apostado"] * $con["valor"] / 100) + $cabecera["total_caja_web"];
														$cabecera["total_freegames"] = ($cabecera["total_apostado"] * $con["valor"] / 100);
														$cabecera["porcentaje_freegames"] = $con["valor"];
														// $cabecera["porcentaje_cliente"] = (100 - $cabecera["porcentaje_freegames"]);
													}
													$cabecera["total_cliente"] = ( $cabecera["total_produccion"] - $cabecera["total_freegames"]);
												}else{
													$tickets = get_tickets($local_id,$data);
													foreach ($tickets as $key => $t) {
														$t["comision"] = get_comision($f,$t);
														$cabecera["total_cliente"]+=$t["comision"];
													}
													$cabecera["total_freegames"] = ( $cabecera["caja_fisico"] - $cabecera["total_cliente"] );
												}
											}else{
												if($formula[0]["fuente"]=="odds" || $formula[0]["columna"]=="apostado"){
													// $apostado_odds[$ao["local_id"]][$ao["canal_de_venta_id"]]=$ao;
													$quiebres = [];
													$grupos = [];
													$grupos_sumas = [];
													$grupos_calculados = [];
													$quiebres_to_db = [];
													foreach ($formula as $f_key => $f_val) {
														$next_key = $f_key+1;
														$new_quiebre = [];
														$new_quiebre["fuente"]=$formula[$f_key]["fuente"];
														$new_quiebre["columna"]=$formula[$f_key]["columna"];
														$new_quiebre["monto_cliente"]=$formula[$f_key]["monto_cliente"];
														$new_quiebre["monto_freegames"]=number_format((100 - $new_quiebre["monto_cliente"]),2);
														$new_quiebre["desde"]=$formula[$f_key]["desde"];
														if(array_key_exists($next_key, $formula)){
															$new_quiebre["hasta"]=$formula[$next_key]["desde"];
														}else{
															$new_quiebre["hasta"]=999999;
														}
														$quiebres[]=$new_quiebre;

														$quiebre_to_db = [];
														$quiebre_to_db["at_unique_id"]=md5($proceso_id.date("c").$local_id.$formula[$f_key]["desde"].$formula[$f_key]["hasta"].$new_quiebre["monto_cliente"].$new_quiebre["monto_freegames"]);
														$quiebre_to_db["proceso_unique_id"]=$proceso_id;
														$quiebre_to_db["local_id"]=$local_id;
														$quiebre_to_db["servicio_id"]=$data["servicio_id"];
														$quiebre_to_db["canal_de_venta_id"]=$cdv_id;
														$quiebre_to_db["fuente"]=$new_quiebre["fuente"];
														$quiebre_to_db["columna"]=$new_quiebre["columna"];
														$quiebre_to_db["desde"]=$formula[$f_key]["desde"];
														$quiebre_to_db["hasta"]=$formula[$f_key]["hasta"];
														$quiebre_to_db["porcentaje_cliente"]=$new_quiebre["monto_cliente"];
														$quiebre_to_db["porcentaje_freegames"]=$new_quiebre["monto_freegames"];

														$quiebre_to_db["contrato_id"]=$formula[$f_key]["contrato_id"];
														$quiebre_to_db["formula_id"]=$formula[$f_key]["formula_id"];
														$quiebre_to_db["tipo_contrato_id"]=$formula[$f_key]["tipo_contrato_id"];

														$quiebres_to_db[$new_quiebre["monto_cliente"]]=$quiebre_to_db;
													}
													// echo "formula";
													// print_r($formula);
													foreach ($quiebres as $q_key => $q_v) {
														if(array_key_exists($local_id, $apostado_odds)){
															if(array_key_exists($cdv_id, $apostado_odds[$local_id])){
																$tickets = $apostado_odds[$local_id][$cdv_id];
																// print_r($tickets);
																// exit();
																foreach ($tickets as $t_key => $t_val) {
																	if($t_val["odds"] >= $q_v["desde"] && $t_val["odds"] < $q_v["hasta"]){
																		$grupos[$q_v["monto_cliente"]][]=$t_val;
																	}
																}
															}
														}
													}
													// echo "quiebres";
													// print_r($quiebres);
													// exit();
													foreach ($grupos as $g_key => $g_val) {
														foreach ($g_val as $gt_key => $gt_val) {
															if(array_key_exists($g_key, $grupos_sumas)){
																$grupos_sumas[$g_key]+=$gt_val["apostado"];
															}else{
																$grupos_sumas[$g_key]=$gt_val["apostado"];
															}												
														}
													}
													// echo "grupos_sumas";
													// print_r($grupos_sumas);
													// exit();
													$prev_total_cliente = 0;
													foreach ($grupos_sumas as $gs_key => $gs_val) {
														$res = ($gs_val * $gs_key) / 100;
														$grupos_calculados[$gs_key]=$res;
														$prev_total_cliente+=$res;
														$quiebres_to_db[$gs_key]["total_cliente"]=$res;
														$quiebres_to_db[$gs_key]["total_freegames"]=($gs_val - $res);
														$quiebres_to_db[$gs_key]["columna_valor"]=$gs_val;
													}
													$cabecera["total_cliente"] = $prev_total_cliente;
													$cabecera["total_freegames"] = ($cabecera["caja_fisico"] - $prev_total_cliente);
													// echo "grupos_calculados";
													// print_r($grupos_calculados);

													// echo "quiebres_to_db";
													// print_r($quiebres_to_db);
													$mysqli->query("START TRANSACTION");
														foreach ($quiebres_to_db as $qtd_key => $qtd_val) {
															quiebres_insert(data_to_db($qtd_val));
														}
													$mysqli->query("COMMIT");
													// exit();
												}else{
													foreach ($formula as $f_key => $f_val) {
														$total_produccion = $cabecera["total_produccion"];
														$desde = $f_val["desde"];
														$hasta = $f_val["hasta"];
														$fuente = $f_val["fuente"];
														$columna = $f_val["columna"];
														$contrato_id = $f_val["contrato_id"];
														$formula_id = $f_val["formula_id"];
														$tipo_contrato_id = $f_val["tipo_contrato_id"];
														$f_val["monto_freegames"]=number_format((100 - $f_val["monto_cliente"]),2);
														if($hasta<=0){
															$hasta = 999999;
														}
														if($total_produccion >= $desde && $total_produccion < $hasta){
															$cabecera["porcentaje_cliente"]=$f_val["monto_cliente"];
														}
													}
													$cabecera["total_cliente"]=(($cabecera["total_produccion"] * $cabecera["porcentaje_cliente"]) / 100);
													$cabecera["porcentaje_freegames"] = (100 - $cabecera["porcentaje_cliente"]);
													$cabecera["total_freegames"] = ($cabecera["total_produccion"] - $cabecera["total_cliente"]);

													$quiebre_to_db = [];
														$quiebre_to_db["at_unique_id"]=md5($proceso_id.date("c").$local_id.$desde.$hasta.$cabecera["porcentaje_cliente"].$cabecera["porcentaje_freegames"]);
														$quiebre_to_db["proceso_unique_id"]=$proceso_id;
														$quiebre_to_db["local_id"]=$local_id;
														$quiebre_to_db["servicio_id"]=$data["servicio_id"];
														$quiebre_to_db["canal_de_venta_id"]=$cdv_id;
														$quiebre_to_db["fuente"]=$fuente;
														$quiebre_to_db["columna"]=$columna;
														$quiebre_to_db["desde"]=$desde;
														$quiebre_to_db["hasta"]=$hasta;
														$quiebre_to_db["porcentaje_cliente"]=$cabecera["porcentaje_cliente"];
														$quiebre_to_db["porcentaje_freegames"]=$cabecera["porcentaje_freegames"];

														$quiebre_to_db["contrato_id"]=$contrato_id;
														$quiebre_to_db["formula_id"]=$formula_id;
														$quiebre_to_db["tipo_contrato_id"]=$tipo_contrato_id;

														$quiebre_to_db["total_cliente"]=$cabecera["total_cliente"];
														$quiebre_to_db["total_freegames"]=$cabecera["total_freegames"];
														$quiebre_to_db["columna_valor"]=$cabecera["total_produccion"];
													quiebres_insert(data_to_db($quiebre_to_db));
												}
											}
										}else{
											// if($formula["formula_id"]==17){
											// }else{											
												if($formula["tipo"]=="normal"){
													if($formula["columna"]=="resultado"){
														if($formula["operador_id"]==1){
															$cabecera["porcentaje_cliente"]=$formula["monto_cliente"];
															$cabecera["total_cliente"]= (($cabecera["total_produccion"] * $cabecera["porcentaje_cliente"]) / 100);
															$cabecera["porcentaje_freegames"] = (100 - $cabecera["porcentaje_cliente"]);
															$cabecera["total_freegames"] = ($cabecera["total_produccion"] - $cabecera["total_cliente"]);


															$quiebre_to_db = [];
																$quiebre_to_db["at_unique_id"]=md5($proceso_id.date("c").$local_id.$formula["desde"].$formula["hasta"].$cabecera["porcentaje_cliente"].$cabecera["porcentaje_freegames"]);
																$quiebre_to_db["proceso_unique_id"]=$proceso_id;
																$quiebre_to_db["local_id"]=$local_id;
																$quiebre_to_db["servicio_id"]=$data["servicio_id"];
																$quiebre_to_db["canal_de_venta_id"]=$cdv_id;
																$quiebre_to_db["fuente"]=$formula["fuente"];
																$quiebre_to_db["columna"]=$formula["columna"];
																$quiebre_to_db["desde"]=$formula["desde"];
																$quiebre_to_db["hasta"]=$formula["hasta"];
																$quiebre_to_db["porcentaje_cliente"]=$cabecera["porcentaje_cliente"];
																$quiebre_to_db["porcentaje_freegames"]=$cabecera["porcentaje_freegames"];

																$quiebre_to_db["contrato_id"]=$formula["contrato_id"];
																$quiebre_to_db["formula_id"]=$formula["formula_id"];
																$quiebre_to_db["tipo_contrato_id"]=$formula["tipo_contrato_id"];

																$quiebre_to_db["total_cliente"]=$cabecera["total_cliente"];
																$quiebre_to_db["total_freegames"]=$cabecera["total_freegames"];
																$quiebre_to_db["columna_valor"]=$cabecera["total_produccion"];
															quiebres_insert(data_to_db($quiebre_to_db));
														}
													}elseif($formula["columna"]=="apostado"){
														if($formula["operador_id"]==1){
															$cabecera["porcentaje_cliente"]=$formula["monto_cliente"];
															$cabecera["total_cliente"]= (($cabecera["total_apostado"] * $cabecera["porcentaje_cliente"]) / 100);
															$cabecera["porcentaje_freegames"] = (100 - $cabecera["porcentaje_cliente"]);
															$cabecera["total_freegames"] = ($cabecera["caja_fisico"] - $cabecera["total_cliente"]);

															$quiebre_to_db = [];
																$quiebre_to_db["at_unique_id"]=md5($proceso_id.date("c").$local_id.$formula["desde"].$formula["hasta"].$cabecera["porcentaje_cliente"].$cabecera["porcentaje_freegames"]);
																$quiebre_to_db["proceso_unique_id"]=$proceso_id;
																$quiebre_to_db["local_id"]=$local_id;
																$quiebre_to_db["servicio_id"]=$data["servicio_id"];
																$quiebre_to_db["canal_de_venta_id"]=$cdv_id;
																$quiebre_to_db["fuente"]=$formula["fuente"];
																$quiebre_to_db["columna"]=$formula["columna"];
																$quiebre_to_db["desde"]=$formula["desde"];
																$quiebre_to_db["hasta"]=$formula["hasta"];
																$quiebre_to_db["porcentaje_cliente"]=$cabecera["porcentaje_cliente"];
																$quiebre_to_db["porcentaje_freegames"]=$cabecera["porcentaje_freegames"];

																$quiebre_to_db["contrato_id"]=$formula["contrato_id"];
																$quiebre_to_db["formula_id"]=$formula["formula_id"];
																$quiebre_to_db["tipo_contrato_id"]=$formula["tipo_contrato_id"];

																$quiebre_to_db["total_cliente"]=$cabecera["total_cliente"];
																$quiebre_to_db["total_freegames"]=$cabecera["total_freegames"];
																$quiebre_to_db["columna_valor"]=$cabecera["total_apostado"];
															quiebres_insert(data_to_db($quiebre_to_db));
														}
													}elseif($formula["columna"]=="nuevo_resultado"){
														if($formula["operador_id"]==1){
															$cabecera["porcentaje_cliente"]=$formula["monto_cliente"];
															$cabecera["total_cliente"]= (($cabecera["resultado_negocio"] * $cabecera["porcentaje_cliente"]) / 100);
															$cabecera["porcentaje_freegames"] = (100 - $cabecera["porcentaje_cliente"]);
															$cabecera["total_freegames"] = (($cabecera["resultado_negocio"]) - $cabecera["total_cliente"]);

															$quiebre_to_db = [];
																$quiebre_to_db["at_unique_id"]=md5($proceso_id.date("c").$local_id.$formula["desde"].$formula["hasta"].$cabecera["porcentaje_cliente"].$cabecera["porcentaje_freegames"]);
																$quiebre_to_db["proceso_unique_id"]=$proceso_id;
																$quiebre_to_db["local_id"]=$local_id;
																$quiebre_to_db["servicio_id"]=$data["servicio_id"];
																$quiebre_to_db["canal_de_venta_id"]=$cdv_id;
																$quiebre_to_db["fuente"]=$formula["fuente"];
																$quiebre_to_db["columna"]=$formula["columna"];
																$quiebre_to_db["desde"]=$formula["desde"];
																$quiebre_to_db["hasta"]=$formula["hasta"];
																$quiebre_to_db["porcentaje_cliente"]=$cabecera["porcentaje_cliente"];
																$quiebre_to_db["porcentaje_freegames"]=$cabecera["porcentaje_freegames"];

																$quiebre_to_db["contrato_id"]=$formula["contrato_id"];
																$quiebre_to_db["formula_id"]=$formula["formula_id"];
																$quiebre_to_db["tipo_contrato_id"]=$formula["tipo_contrato_id"];

																$quiebre_to_db["total_cliente"]=$cabecera["total_cliente"];
																$quiebre_to_db["total_freegames"]=$cabecera["total_freegames"];
																$quiebre_to_db["columna_valor"]=$cabecera["total_apostado"];
															quiebres_insert(data_to_db($quiebre_to_db));
														}
													}
												}
											// }
										}
									}
								}
								if($cdv_id==17){
									if(array_key_exists(17, $local_formulas[$local_id])){
										$formula = $local_formulas[$local_id][17];
										if(array_key_exists(0, $formula)){
											if($formula[0]["formula_id"]==17){
												$f = get_condicionales($formula[0]);
												if($formula[0]["tipo_contrato_id"]==4){ // Participacion FG
													$con = reset($f);
													$return["con_".$cdv_id]=$con;
													if($con["donde"]=="apostado"){
														$cabecera["total_freegames"] = ($cabecera["total_apostado"] * $con["valor"] / 100);
														$cabecera["porcentaje_freegames"] = $con["valor"];
														// $cabecera["porcentaje_cliente"] = (100 - $cabecera["porcentaje_freegames"]);
													}
													$cabecera["total_cliente"] = ( $cabecera["total_produccion"] - $cabecera["total_freegames"] );
												}
											}
										}else{
											if($formula["tipo"]=="normal"){
												if($formula["columna"]=="resultado"){
													if($formula["operador_id"]==1){
														$cabecera["porcentaje_cliente"]=$formula["monto_cliente"];
														$cabecera["total_cliente"]= (($cabecera["total_produccion"] * $cabecera["porcentaje_cliente"]) / 100);
														$cabecera["porcentaje_freegames"]=(100 - $cabecera["porcentaje_cliente"]);
														$cabecera["total_freegames"]=($cabecera["total_produccion"] - $cabecera["total_cliente"]);


														$quiebre_to_db = [];
															$quiebre_to_db["at_unique_id"]=md5($proceso_id.date("c").$local_id.$formula["desde"].$formula["hasta"].$cabecera["porcentaje_cliente"].$cabecera["porcentaje_freegames"]);
															$quiebre_to_db["proceso_unique_id"]=$proceso_id;
															$quiebre_to_db["local_id"]=$local_id;
															$quiebre_to_db["servicio_id"]=$data["servicio_id"];
															$quiebre_to_db["canal_de_venta_id"]=$cdv_id;
															$quiebre_to_db["fuente"]=$formula["fuente"];
															$quiebre_to_db["columna"]=$formula["columna"];
															$quiebre_to_db["desde"]=$formula["desde"];
															$quiebre_to_db["hasta"]=$formula["hasta"];
															$quiebre_to_db["porcentaje_cliente"]=$cabecera["porcentaje_cliente"];
															$quiebre_to_db["porcentaje_freegames"]=$cabecera["porcentaje_freegames"];

															$quiebre_to_db["contrato_id"]=$formula["contrato_id"];
															$quiebre_to_db["formula_id"]=$formula["formula_id"];
															$quiebre_to_db["tipo_contrato_id"]=$formula["tipo_contrato_id"];

															$quiebre_to_db["total_cliente"]=$cabecera["total_cliente"];
															$quiebre_to_db["total_freegames"]=$cabecera["total_freegames"];
															$quiebre_to_db["columna_valor"]=$cabecera["total_produccion"];
														quiebres_insert(data_to_db($quiebre_to_db));
													}
												}elseif($formula["columna"]=="nuevo_resultado"){
													if($formula["operador_id"]==1){
														$cabecera["porcentaje_cliente"]=$formula["monto_cliente"];
														$cabecera["total_cliente"]= (($cabecera["resultado_negocio"] * $cabecera["porcentaje_cliente"]) / 100);
														$cabecera["porcentaje_freegames"] = (100 - $cabecera["porcentaje_cliente"]);
														$cabecera["total_freegames"] = (($cabecera["resultado_negocio"]) - $cabecera["total_cliente"]);

														$quiebre_to_db = [];
															$quiebre_to_db["at_unique_id"]=md5($proceso_id.date("c").$local_id.$formula["desde"].$formula["hasta"].$cabecera["porcentaje_cliente"].$cabecera["porcentaje_freegames"]);
															$quiebre_to_db["proceso_unique_id"]=$proceso_id;
															$quiebre_to_db["local_id"]=$local_id;
															$quiebre_to_db["servicio_id"]=$data["servicio_id"];
															$quiebre_to_db["canal_de_venta_id"]=$cdv_id;
															$quiebre_to_db["fuente"]=$formula["fuente"];
															$quiebre_to_db["columna"]=$formula["columna"];
															$quiebre_to_db["desde"]=$formula["desde"];
															$quiebre_to_db["hasta"]=$formula["hasta"];
															$quiebre_to_db["porcentaje_cliente"]=$cabecera["porcentaje_cliente"];
															$quiebre_to_db["porcentaje_freegames"]=$cabecera["porcentaje_freegames"];

															$quiebre_to_db["contrato_id"]=$formula["contrato_id"];
															$quiebre_to_db["formula_id"]=$formula["formula_id"];
															$quiebre_to_db["tipo_contrato_id"]=$formula["tipo_contrato_id"];

															$quiebre_to_db["total_cliente"]=$cabecera["total_cliente"];
															$quiebre_to_db["total_freegames"]=$cabecera["total_freegames"];
															$quiebre_to_db["columna_valor"]=$cabecera["total_apostado"];
														quiebres_insert(data_to_db($quiebre_to_db));
													}
												}
											}
										}
									}
								}
								if($cdv_id==18 || $cdv_id==21){
									if(array_key_exists(18, $local_formulas[$local_id])){
										$formula = $local_formulas[$local_id][18];
										if(array_key_exists(0, $formula)){
											if($formula[0]["formula_id"]==17){
												$f = get_condicionales($formula[0]);
												if($formula[0]["tipo_contrato_id"]==4){ // Participacion FG
													$con = reset($f);
													$return["con_".$cdv_id]=$con;
													if($con["donde"]=="resultado"){
														$cabecera["total_freegames"] = ($cabecera["total_produccion"] * $con["valor"] / 100);
														$cabecera["porcentaje_freegames"] = $con["valor"];
														// $cabecera["porcentaje_cliente"] = (100 - $cabecera["porcentaje_freegames"]);
													}
													$cabecera["total_cliente"] = ( $cabecera["total_produccion"] - $cabecera["total_freegames"] );
												}
											}
										}else{
											if($formula["tipo"]=="normal"){
												if($formula["columna"]=="resultado"){
													if($formula["operador_id"]==1){
														$cabecera["porcentaje_cliente"]=$formula["monto_cliente"];
														$cabecera["total_cliente"]= (($cabecera["total_produccion"] * $cabecera["porcentaje_cliente"]) / 100);
														$cabecera["porcentaje_freegames"]=(100 - $cabecera["porcentaje_cliente"]);
														$cabecera["total_freegames"]=($cabecera["total_produccion"] - $cabecera["total_cliente"]);

														$quiebre_to_db = [];
															$quiebre_to_db["at_unique_id"]=md5($proceso_id.date("c").$local_id.$formula["desde"].$formula["hasta"].$cabecera["porcentaje_cliente"].$cabecera["porcentaje_freegames"]);
															$quiebre_to_db["proceso_unique_id"]=$proceso_id;
															$quiebre_to_db["local_id"]=$local_id;
															$quiebre_to_db["servicio_id"]=$data["servicio_id"];
															$quiebre_to_db["canal_de_venta_id"]=$cdv_id;
															$quiebre_to_db["fuente"]=$formula["fuente"];
															$quiebre_to_db["columna"]=$formula["columna"];
															$quiebre_to_db["desde"]=$formula["desde"];
															$quiebre_to_db["hasta"]=$formula["hasta"];
															$quiebre_to_db["porcentaje_cliente"]=$cabecera["porcentaje_cliente"];
															$quiebre_to_db["porcentaje_freegames"]=$cabecera["porcentaje_freegames"];

															$quiebre_to_db["contrato_id"]=$formula["contrato_id"];
															$quiebre_to_db["formula_id"]=$formula["formula_id"];
															$quiebre_to_db["tipo_contrato_id"]=$formula["tipo_contrato_id"];

															$quiebre_to_db["total_cliente"]=$cabecera["total_cliente"];
															$quiebre_to_db["total_freegames"]=$cabecera["total_freegames"];
															$quiebre_to_db["columna_valor"]=$cabecera["total_produccion"];
														//quiebres_insert(data_to_db($quiebre_to_db));
													}
												}elseif($formula["columna"]=="nuevo_resultado"){
													if($formula["operador_id"]==1){
														$cabecera["porcentaje_cliente"]=$formula["monto_cliente"];
														$cabecera["total_cliente"]= (($cabecera["resultado_negocio"] * $cabecera["porcentaje_cliente"]) / 100);
														$cabecera["porcentaje_freegames"] = (100 - $cabecera["porcentaje_cliente"]);
														$cabecera["total_freegames"] = (($cabecera["resultado_negocio"]) - $cabecera["total_cliente"]);

														$quiebre_to_db = [];
															$quiebre_to_db["at_unique_id"]=md5($proceso_id.date("c").$local_id.$formula["desde"].$formula["hasta"].$cabecera["porcentaje_cliente"].$cabecera["porcentaje_freegames"]);
															$quiebre_to_db["proceso_unique_id"]=$proceso_id;
															$quiebre_to_db["local_id"]=$local_id;
															$quiebre_to_db["servicio_id"]=$data["servicio_id"];
															$quiebre_to_db["canal_de_venta_id"]=$cdv_id;
															$quiebre_to_db["fuente"]=$formula["fuente"];
															$quiebre_to_db["columna"]=$formula["columna"];
															$quiebre_to_db["desde"]=$formula["desde"];
															$quiebre_to_db["hasta"]=$formula["hasta"];
															$quiebre_to_db["porcentaje_cliente"]=$cabecera["porcentaje_cliente"];
															$quiebre_to_db["porcentaje_freegames"]=$cabecera["porcentaje_freegames"];

															$quiebre_to_db["contrato_id"]=$formula["contrato_id"];
															$quiebre_to_db["formula_id"]=$formula["formula_id"];
															$quiebre_to_db["tipo_contrato_id"]=$formula["tipo_contrato_id"];

															$quiebre_to_db["total_cliente"]=$cabecera["total_cliente"];
															$quiebre_to_db["total_freegames"]=$cabecera["total_freegames"];
															$quiebre_to_db["columna_valor"]=$cabecera["total_apostado"];
														//quiebres_insert(data_to_db($quiebre_to_db));
													}
												}
											}
										}
									}
								}
								if($cdv_id==19){
									if(array_key_exists(19, $local_formulas[$local_id])){
										$formula = $local_formulas[$local_id][19];
										if(array_key_exists(0, $formula)){									
										}else{
											if($formula["tipo"]=="normal"){
												if($formula["columna"]=="resultado"){
													if($formula["operador_id"]==1){
														$cabecera["porcentaje_cliente"]=$formula["monto_cliente"];
														$cabecera["total_cliente"]=(($cabecera["total_produccion"] * $cabecera["porcentaje_cliente"]) / 100);
														$cabecera["porcentaje_freegames"]=(100 - $cabecera["porcentaje_cliente"]);
														$cabecera["total_freegames"]=($cabecera["total_produccion"] - $cabecera["total_cliente"]);
														
														$quiebre_to_db = [];
															$quiebre_to_db["at_unique_id"]=md5($proceso_id.date("c").$local_id.$formula["desde"].$formula["hasta"].$cabecera["porcentaje_cliente"].$cabecera["porcentaje_freegames"]);
															$quiebre_to_db["proceso_unique_id"]=$proceso_id;
															$quiebre_to_db["local_id"]=$local_id;
															$quiebre_to_db["servicio_id"]=$data["servicio_id"];
															$quiebre_to_db["canal_de_venta_id"]=$cdv_id;
															$quiebre_to_db["fuente"]=$formula["fuente"];
															$quiebre_to_db["columna"]=$formula["columna"];
															$quiebre_to_db["desde"]=$formula["desde"];
															$quiebre_to_db["hasta"]=$formula["hasta"];
															$quiebre_to_db["porcentaje_cliente"]=$cabecera["porcentaje_cliente"];
															$quiebre_to_db["porcentaje_freegames"]=$cabecera["porcentaje_freegames"];

															$quiebre_to_db["contrato_id"]=$formula["contrato_id"];
															$quiebre_to_db["formula_id"]=$formula["formula_id"];
															$quiebre_to_db["tipo_contrato_id"]=$formula["tipo_contrato_id"];

															$quiebre_to_db["total_cliente"]=$cabecera["total_cliente"];
															$quiebre_to_db["total_freegames"]=$cabecera["total_freegames"];
															$quiebre_to_db["columna_valor"]=$cabecera["total_produccion"];
														//quiebres_insert(data_to_db($quiebre_to_db));
													}
												}elseif($formula["columna"]=="nuevo_resultado"){
													if($formula["operador_id"]==1){
														$cabecera["porcentaje_cliente"]=$formula["monto_cliente"];
														$cabecera["total_cliente"]= (($cabecera["resultado_negocio"] * $cabecera["porcentaje_cliente"]) / 100);
														$cabecera["porcentaje_freegames"] = (100 - $cabecera["porcentaje_cliente"]);
														$cabecera["total_freegames"] = (($cabecera["resultado_negocio"]) - $cabecera["total_cliente"]);

														$quiebre_to_db = [];
															$quiebre_to_db["at_unique_id"]=md5($proceso_id.date("c").$local_id.$formula["desde"].$formula["hasta"].$cabecera["porcentaje_cliente"].$cabecera["porcentaje_freegames"]);
															$quiebre_to_db["proceso_unique_id"]=$proceso_id;
															$quiebre_to_db["local_id"]=$local_id;
															$quiebre_to_db["servicio_id"]=$data["servicio_id"];
															$quiebre_to_db["canal_de_venta_id"]=$cdv_id;
															$quiebre_to_db["fuente"]=$formula["fuente"];
															$quiebre_to_db["columna"]=$formula["columna"];
															$quiebre_to_db["desde"]=$formula["desde"];
															$quiebre_to_db["hasta"]=$formula["hasta"];
															$quiebre_to_db["porcentaje_cliente"]=$cabecera["porcentaje_cliente"];
															$quiebre_to_db["porcentaje_freegames"]=$cabecera["porcentaje_freegames"];

															$quiebre_to_db["contrato_id"]=$formula["contrato_id"];
															$quiebre_to_db["formula_id"]=$formula["formula_id"];
															$quiebre_to_db["tipo_contrato_id"]=$formula["tipo_contrato_id"];

															$quiebre_to_db["total_cliente"]=$cabecera["total_cliente"];
															$quiebre_to_db["total_freegames"]=$cabecera["total_freegames"];
															$quiebre_to_db["columna_valor"]=$cabecera["total_apostado"];
														//quiebres_insert(data_to_db($quiebre_to_db));
													}
												}
											}
										}
									}
								}
							}else{
								// $return["local_formula"]="ño";

							}

							



						$cabeceras[]=$cabecera;
						
						
					}
				}
				$cabeceras1=[];
				foreach ($cabeceras as $cabe_key => $cabe_val){			
					$cabecera_to_db = cabecera_to_db($cabe_val);
					$cabecera_id = cabecera_insert($cabecera_to_db);
					$cabeceras1[]=$cabecera_to_db;
				}
				echo(json_encode($cabeceras1));
			}
		}
	}else{
		$return["error"]="no_servicio_id";
	}
}

function cabecera_to_db($d){
	global $mysqli;
	$tmp=[];
	// $nulls=array("null","",false);
	foreach ($d as $k => $v) {
		// if($v===0){
		// 	$tmp[$k]=$v;
		// }elseif(in_array($v, $nulls)){
		// 	$tmp[$k]="NULL";
		// }else{
			if(is_float($v)){
				$tmp[$k]="'".$v."'";
			}elseif(is_int($v)){
				$tmp[$k]=$v;
			}else{
				$v=str_replace(",", ".", $v);
				$tmp[$k]="'".trim($mysqli->real_escape_string($v))."'";
			}
		// }
	}
	return $tmp;
}

function cabecera_insert($cabecera){
	global $mysqli;
	global $return;

	$command = "INSERT INTO tbl_transacciones_cabecera";
	$command.="(";
	$command.=implode(",", array_keys($cabecera));
	$command.=")";
	$command.=" VALUES ";
	$command.="(";
	$command.=implode(",", $cabecera);
	$command.=")";
	$command.=" ON DUPLICATE KEY UPDATE ";
	$uqn=0;
	foreach ($cabecera as $key => $value) {
		if($uqn>0) { $command.=","; }
		$command.= $key." = VALUES(".$key.")";
		$uqn++;
	}

	echo($command);
	

}

