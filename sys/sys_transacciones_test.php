<?php
include '/var/www/html/sys/helpers.php';
include '/var/www/html/sys/db_connect.php';


$data = [
	'servicio_id'=>1,
	'proceso_id'=>'false',
	'fecha'=>'Wed Mar 08 2023 19:00:00 GMT-0500 (hora estándar de Perú)'
];
transacciones_build_liquidaciones($data);
function transacciones_build_liquidaciones($data){
	global $mysqli;
	global $return;
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

		$proceso_id = md5(date("c").$data["servicio_id"]);

		
		
		$return["proceso_id"]=$proceso_id;
		if((int)$data["servicio_id"] == 9){
			
		} elseif((int)$data["servicio_id"] == 12){ // TORITO
			

		}
		elseif((int)$data["servicio_id"] == 13){ // CARRERA DE CABALLOS
			
		}
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
			$mysqli->next_result();
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

			$total_saldo_web = [];
			$total_saldo_web_command = "SELECT
											l.id as local_id,
											l.cc_id,
											SUM(swt.monto) AS monto,
											swt.tipo_id,
											IF(swt.tipo_id = 1 ,'Dep','Retiro')
										FROM  tbl_saldo_web_transaccion AS swt
										LEFT JOIN tbl_locales l on l.cc_id = swt.cc_id
										WHERE swt.created_at >= '" . $data["fecha_inicio"] . "'
											AND swt.created_at < '" . $data["fecha_fin"] . "'
										GROUP BY l.id , swt.tipo_id";
			var_dump($total_saldo_web_command);
			$total_saldo_web_query = $mysqli->query($total_saldo_web_command);
			while( $tt = $total_saldo_web_query->fetch_assoc() ){
				$total_saldo_web[$tt["local_id"]]["depositos"] = (isset($total_saldo_web[$tt["local_id"]]["depositos"]) && $total_saldo_web[$tt["local_id"]]["depositos"]!=0)?$total_saldo_web[$tt["local_id"]]["depositos"]:0;
				$total_saldo_web[$tt["local_id"]]["retiros"] = (isset($total_saldo_web[$tt["local_id"]]["retiros"]) && $total_saldo_web[$tt["local_id"]]["retiros"]!=0)?$total_saldo_web[$tt["local_id"]]["retiros"]:0;
				if( $tt["tipo_id"] == 1 )
				{//DEP
					$total_saldo_web[$tt["local_id"]]["depositos"] = $tt["monto"];
				}
				if( $tt["tipo_id"] == 2 )
				{//RET
					$total_saldo_web[$tt["local_id"]]["retiros"] = $tt["monto"];
				}
			}
			var_dump($total_saldo_web);

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

							if( $cdv_id == 16 ) { //PBET}
								var_dump($local_id);
								if( array_key_exists($local_id, $total_saldo_web) ){
									$cabecera["total_depositado_web"] = $total_saldo_web[$local_id]["depositos"];
									$cabecera["total_retirado_web"] = $total_saldo_web[$local_id]["retiros"];
								}
								
								$cabecera["total_caja_web"] = ( $cabecera["total_depositado_web"] - $cabecera["total_retirado_web"] );
							}							

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

							



						$cabeceras[]=$cabecera;
						// print_r($cabecera);
					}
				}

				// $mysqli->query("START TRANSACTION");
				// 	foreach ($cabeceras as $cabe_key => $cabe_val){			
				// 		$cabecera_to_db = cabecera_to_db($cabe_val);
				// 		$cabecera_id = cabecera_insert($cabecera_to_db);
				// 	}
				// $mysqli->query("COMMIT");	
				
			}
			
		}
		var_dump($cabeceras);	
	}else{
		$return["error"]="no_servicio_id";
	}
}