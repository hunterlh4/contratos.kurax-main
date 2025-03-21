<?php
	date_default_timezone_set("America/Lima");
	header('Access-Control-Allow-Origin: *');
	$tiempo_minimo = 5;
	$continue = true;
	$return = array();
	$return_info = array();
	//$return_info["memory_init"]=round(memory_get_usage()/1024/1024,2)." MB";
	$microtime_init=microtime(true);
	include("../api/api_db_connect.php");
	// include("global_config.php");
	include("sys_login.php");	
	// include("../api/index.php");
	include("../api/where_resultado_apuestas.php");

	$cdv_nombre = array();
	$sql_command = "SELECT id,nombre FROM tbl_canales_venta";
	$sql_query = $mysqli->query($sql_command);
	while($itm=$sql_query->fetch_assoc()){
		$cdv_nombre[$itm["id"]] = $itm["nombre"];
	}

	$l_nombre = array(); 
	$sql_command = "SELECT id,nombre FROM tbl_locales";
	$sql_query = $mysqli->query($sql_command);
	while($itm=$sql_query->fetch_assoc()){
		$l_nombre[$itm["id"]] = $itm["nombre"];
	}

	//Agregar a resumen canal 
	$year_x_month_cdv_l = array();
	$canales = array();
	$locales = array();
	$locales_x_canales = array();

	$detalles_x_local = array();
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
												$return["resumen"][$index_year][$index_month][$cdv_index][$local] = array(
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
																	);
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
	$year_x_month_cdv_l_totales = array();
	$canales_totales = array();
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

										$return["totales"][$index_year][$index_month][$cdv_index] = array(
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
													);
					
									}
									
								}


							}
					}	
				}
			}
		}
	}


	$totales_13_x_year = array();
	$totales_x_year = array();
	$total_13_merge = array();

	foreach ($return["totales"] as $year_index => $months_data) {
		foreach ($months_data as $month_index => $csdv_data) {
			if ($month_index=="total") {

				$total_x_year = array(); 
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

				$totales_x_year[$year_index] = array();
				$totales_x_year[$year_index]["13"] = array();
				$totales_x_year[$year_index]["13"]["13"] = array();
				$totales_x_year[$year_index]["13"]["13"]["13"] = $total_13_merge;
				$totales_13_x_year[$year_index]["13"]["total"] = $total_13_merge;
			}else{
					$total_13 = array();
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

					$totales_13_x_year[$year_index] = array();
					$totales_13_x_year[$year_index]["13"] = array();
					$totales_13_x_year[$year_index]["13"]["13"] = $total_13;
			}
		}
	}

	$resumen = array();
	foreach ($return["resumen"] as $year_index => $months_data) {
		$return["resumen"][$year_index]["13"]=$totales_x_year[$year_index]["13"];
	}
	$totales = array();
	foreach ($return["totales"] as $year_index => $months_data) {
		$return["totales"][$year_index]["13"]=$totales_13_x_year[$year_index]["13"];
	}


	$nombre_mes = array();
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

	$cdv = array();
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

	$cols = array();
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
	$array_anios_meses_thead = array();
	$array_anios_meses_texto_thead = array();
	$array_anios_meses_texto_thead_event = array();
	$array_periodo_reorder = array();
	$period_arr = array();		
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


	$new_obj = array();
	foreach ($return["resumen"] as $year_index => $year_value) {
		foreach ($year_value as $month_index => $month_value) {
			foreach ($month_value as $cdv_id => $cdv_value) {
				foreach ($cdv_value as $local_id => $local_value) {
					$local = array();
					$local = $local_value;
					$local["year"] = $year_index;
					$local["month"] = $month_index;
					$local["period"] = $year_index."".$month_index;
					array_push($new_obj, $local);
				}
			}
		}		
	}



	$obj_by_period = array();
	foreach ($new_obj as $n_in => $n_val) {
		if(!array_key_exists($n_val["period"], $obj_by_period)){
			$obj_by_period[$n_val["period"]]=array();
		}
		$obj_by_period[$n_val["period"]][$n_val["local_id"]."".$n_val["canal_de_venta_id"]]=$n_val;
	}


	$totales_array = array();
	foreach ($return["totales"] as $year_index => $year_value) {
		foreach ($year_value as $month_index => $month_value) {
			foreach ($month_value as $cdv_id => $total_local_data) {
				if ($cdv_id!="total") {
					if ($month_index!="total") {
						$total = array();
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


	$obj_total_by_period = array();
	foreach ($totales_array as $n_in => $n_val) {
		if(!array_key_exists($n_val["period"], $obj_total_by_period)){
			$obj_total_by_period[$n_val["period"]]=array();
		}
		$obj_total_by_period[$n_val["period"]][$n_val["canal_de_venta_id"]]=$n_val;
	}

	$super_total_array = array();
	foreach ($return["totales"] as $year_index => $months_data) {
		foreach ($months_data as $month_index => $csdv_data) {
			foreach ($csdv_data as $cdv_id => $total_local_data) {
				if ($cdv_id=="total") {
					if ($month_index!="total" ) {
						$super_total = array();
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

	$obj_super_total_by_period = array();
	foreach ($super_total_array as $n_in => $n_val) {
		if(!array_key_exists($n_val["period"], $obj_super_total_by_period)){
			$obj_super_total_by_period[$n_val["period"]]=array();
		}
		$obj_super_total_by_period[$n_val["period"]][$n_val["canal_de_venta_id"]]=$n_val;
	}

	$local_send = "";
	if (!empty($filtro["locales"]) && isset($filtro["locales"])) {
		foreach ($filtro["locales"] as $key => $id_local) {
			$local_send = $local_send."".$id_local.",";
		}
	}

	$cdv_send = "";
	if (!empty($filtro["canales_de_venta"]) && isset($filtro["canales_de_venta"])) {
		foreach ($filtro["canales_de_venta"] as $key => $id_cdv) {
			$cdv_send = $cdv_send."".$id_cdv.",";
		}
	}		


	$red_id_send = "";
	if (!empty($filtro["red_id"]) && isset($filtro["red_id"])) {
		foreach ($filtro["red_id"] as $key => $id_red_id) {
			$red_id_send = $red_id_send."".$id_red_id.",";
		}
	}	

	if(1==2){
		?><input type="hidden" value="<?php echo $apuestas_command;?>"><?php
	}
	?>
	<button type="submit" class="btn btn-success btn-xs btn_export_resultado_apuestas" >Exportar XLS</button>
	<table class='tabla_reportes' id='reporte_apuestas' width="100%">
		<thead >
			<tr>
				<th rowspan="3" class="cabecera_collapse_expand">
					<button type="button" class="btn_collapse_expand_row_reporte_apuestas all_parent">
						<span class="glyphicon glyphicon-plus"></span>
					</button>	
				</th>
				
				<th rowspan="2" class="cabecera_canal_venta">Canal de Venta</th>
				<th rowspan="2" class="cabecera_local">Nombre de Local</th>
				<th rowspan="2" class="cabecera_tipo">Tipo</th>
				<th rowspan="2" class="cabecera_asesor">Agente</th>
				<th rowspan="2" class="cabecera_tipo_administracion">Tipo admin.</th>
				<th rowspan="2" class="cabecera_tipo_punto">Tipo de Punto</th>
				<th rowspan="2" class="cabecera_qty">QTY</th>
				<?php 
				foreach ($return["resumen"] as $year_index => $year_data) {
				?>	
					<th class='cabecera_anio' rowspan='1' id='cabeceraanio_<?php echo $year_index; ?>' colspan='<?php echo count($year_data)*count($cols); ?>'>
						<?php echo $year_index; ?>
					</th>
				<?php	
				}
				?>	
			</tr>
			<tr>
				<?php 
				$count_final_month_names=0;				
				foreach ($return["resumen"] as $year_index => $year_data) {
					foreach ($year_data as $month_year => $month_data) {
						?>	
							<th class='cabecera_mes cabecera_meses_del_anio' colspan='<?php echo count($cols); ?>' 
								data-period='<?php echo $array_anios_meses_texto_thead_event[$count_final_month_names]; ?>'
								id='cabecera_<?php echo $array_anios_meses_texto_thead_event[$count_final_month_names]; ?>' >
								<?php echo sec_reporte_apuestas_leer_meses($array_anios_meses_texto_thead[$count_final_month_names]); ?>
							</th>
						<?php
						$count_final_month_names++;
					}	
				}
				?>	
			</tr>
			<tr>
				<th rowspan="1" class="cabecera_canal_venta">
					<div class="inner-addon right-addon">
						<i class="glyphicon glyphicon-remove icon_filter_reporte_apuestas " id="nombre_canal_de_venta_reporte" ></i>
						<input type="text" id="buscar_canal_venta" class="filter_reporte_apuestas buscador_canal_venta form-control" placeholder="Buscar" data-filter-name="nombre_canal_de_venta_reporte"/>
					</div>
				</th>				
				<th rowspan="1" class="cabecera_local">
					<div class="inner-addon right-addon">
						<i class="glyphicon glyphicon-remove icon_filter_reporte_apuestas" id="nombre_local_reporte" ></i>
						<input type="text" id="buscar_local" class="filter_reporte_apuestas buscador_local form-control" placeholder="Buscar" data-filter-name="nombre_local_reporte" />
					</div>
				</th>
				<th rowspan="1" class="cabecera_tipo">
					<div class="inner-addon right-addon">
						<i class="glyphicon glyphicon-remove icon_filter_reporte_apuestas" id="col_local_propiedad"></i>
						<input type="text" id="buscar_tipo" class="filter_reporte_apuestas buscador_tipo form-control" placeholder="Buscar" data-filter-name="col_local_propiedad"/>
					</div>
				</th>
				<th rowspan="1" class="cabecera_asesor">
					<div class="inner-addon right-addon">
						<i class="glyphicon glyphicon-remove icon_filter_reporte_apuestas" id="col_local_asesor_nombre"></i>
						<input type="text" id="buscar_asesor" class="filter_reporte_apuestas buscador_asesor form-control" placeholder="Buscar" data-filter-name="col_local_asesor_nombre" />
					</div>
				</th>								

				<th rowspan="1" class="cabecera_tipo_administracion">
					<div class="inner-addon right-addon">
						<i class="glyphicon glyphicon-remove icon_filter_reporte_apuestas" id="col_local_administracion" ></i>
						<input type="text" id="buscar_tipo_admin" class="filter_reporte_apuestas buscador_tipo_admin form-control" placeholder="Buscar" data-filter-name="col_local_administracion" />
					</div>
				</th>

				<th rowspan="1" class="cabecera_tipo_punto">
					<div class="inner-addon right-addon">
						<i class="glyphicon glyphicon-remove icon_filter_reporte_apuestas" id="col_local_tipo_de_punto" ></i>
						<input type="text" id="buscar_tipo_punto" class="filter_reporte_apuestas buscador_tipo_punto form-control" placeholder="Buscar" data-filter-name="col_local_tipo_de_punto" ></input>
					</div>
				</th>

				<th rowspan="1" class="cabecera_qty">
					<div class="inner-addon right-addon">
						<i class="glyphicon glyphicon-remove icon_filter_reporte_apuestas" id="col_qty" ></i>
						<input type="text" id="buscar_qty" class="filter_reporte_apuestas buscador_qty form-control" placeholder="Buscar" data-filter-name="col_qty" />
					</div>
				</th>
				<?php
					foreach ($return["resumen"] as $year_index => $year_data) {
						foreach ($year_data as $month_year => $month_data) {
							foreach ($cols as $col_index => $col_data) {
								if ($col_index=="total_apostado") {
								?>	
									<th class='cabecera_dinero_apostado'>
										<?php echo $col_data; ?>										
									</th>      
								<?php		
								}
								else if ($col_index=="total_ganado") {
								?>
									<th data-period="<?php echo $year_index.''.$month_year; ?>" class='cabecera_total_ganado  cabeceras<?php echo $year_index.''.$month_year; ?> oculto'>
										<?php echo $col_data; ?>
									</th>                
								<?php		
								}		
								else if($col_index=="total_pagado"){
								?>	
									<th data-period="<?php echo $year_index.''.$month_year; ?>" class='cabecera_premios_pagados cabeceras<?php echo $year_index.''.$month_year; ?> oculto'>
										<?php echo $col_data; ?>										
									</th> 
								<?php		
								}
								else if ($col_index=="por_pagar") {
								?>	
									<th data-period="<?php echo $year_index.''.$month_year; ?>" class='cabecera_premios_pagados_por_pagar 			cabeceras<?php echo $year_index.''.$month_year; ?>" oculto'>
										<?php echo $col_data; ?>									 
									</th>	
								<?php		
								}
								else if($col_index=="net_win"){
								?>	
									<th data-period="<?php echo $year_index.''.$month_year; ?>" class='cabecera_net_win_t cabeceras<?php 
										echo $year_index.''.$month_year; ?> oculto'>
										<?php echo $col_data; ?>										
									</th>	
								<?php	                 
								}else if($col_index=="hold"){
								?>	
									<th data-period="<?php echo $year_index.''.$month_year; ?>" class='cabecera_hold cabeceras<?php 
										echo $year_index.''.$month_year; ?>" oculto'> 
										<?php echo $col_data; ?>										 
									</th>	
								<?php	              
								}else if($col_index=="num_tickets"){
								?>	
									<th data-period="<?php echo $year_index.''.$month_year; ?>" class='cabecera_tickets_emitidos cabeceras<?php 
										echo $year_index.''.$month_year; ?> oculto'>
										<?php echo $col_data; ?>										
									</th>	
								<?php	                
								}else if($col_index=="apuesta_x_ticket"){
								?>	
									<th data-period="<?php echo $year_index.''.$month_year; ?>" class='cabecera_apuesta_por_ticket cabeceras<?php 
										echo $year_index.''.$month_year; ?> oculto'> 
										<?php echo $col_data; ?>										                
									</th>	
								<?php		
								}else if($col_index=="num_tickets_ganados"){
								?>	
									<th data-period="<?php echo $year_index.''.$month_year; ?>" class='cabecera_ticket_premiados cabeceras<?php 
										echo $year_index.''.$month_year; ?> oculto'>  
										<?php echo $col_data; ?>										              
									</th>	
								<?php		
								}
								else if($col_index=="num_tickets_ganados_pagados"){
								?>	
									<th data-period="<?php echo $year_index.''.$month_year; ?>" class='cabecera_num_tickets_ganados_pagados 		cabeceras<?php echo $year_index.''.$month_year; ?> oculto'>                
										<?php echo $col_data; ?>										
									</th>	
								<?php		
								}
								else if($col_index=="num_tickets_por_pagar"){
								?>	
									<th data-period="<?php echo $year_index.''.$month_year; ?>" class='cabecera_num_tickets_por_pagar cabeceras		<?php echo $year_index.''.$month_year; ?>" oculto'>
										<?php echo $col_data; ?>										
									</th>	
								<?php	                
								}
								else if($col_index=="tickets_premiados"){
								?>	
									<th data-period="<?php echo $year_index.''.$month_year; ?>" class='cabecera_ticket_premiados cabeceras<?php 
										echo $year_index.''.$month_year; ?>" oculto'> 
										<?php echo $col_data; ?>										               
									</th>
								<?php		
								}else if($col_index=="total_depositado_web"){
								?>	
									<th data-period="<?php echo $year_index.''.$month_year; ?>" class='cabecera_dinero_depositado_web cabeceras		<?php echo $year_index.''.$month_year; ?>" oculto'>
										<?php echo $col_data; ?>									                
									</th>	
								<?php
								}else if($col_index=="total_retirado_web"){
								?>
									<th data-period="<?php echo $year_index.''.$month_year; ?>" class='cabecera_dinero_retirado_web cabeceras<?php echo $year_index.''.$month_year; ?>" oculto'>
										<?php echo $col_data; ?>										
									</th>  
								<?php	               
								}								
							}
						}
					}
					?>
			</tr>
		</thead>
		<tbody class="tbody_table_reporte_apuestas">
			<?php
				foreach($cdv as $cdv_index => $cdv_nombre) {
					foreach($new_obj as $obj_index => $obj_data) {
						if($obj_data["canal_de_venta_id"] == $cdv_index){
							if($obj_data["period"] == $periodo_inicio){
							?>
								 <tr class="clickable-row rows_hidden children children_row_collapse_expand_<?php echo $cdv_index; ?>"  
									id="<?php echo $cdv_index; ?>">
									<td class="td_btn_collapse_expand">
										<button class="btn_collapse_expand_row_reporte_apuestas parent parent_row_collapse_expand_<?php echo $cdv_index; ?>" 
											id="<?php echo $cdv_index; ?>">
											<span class="glyphicon glyphicon-plus"></span>
										</button>
									</td>					
									<td class="nombre_canal_de_venta_reporte"><?php echo $cdv_nombre; ?></td>
									<td class="nombre_local_reporte"><?php echo $obj_data["nombre"]; ?></td>
									<td class="col_local_propiedad"><?php echo $obj_data["propiedad"]; ?></td>
										<?php
										if ($obj_data["asesor_nombre"]==null) {
											?>
											<td class="col_local_asesor_nombre"></td>
											<?php
										}else{
											?>
											<td class="col_local_asesor_nombre"><?php echo $obj_data["asesor_nombre"]; ?></td>
										<?php	
										}
									?>
									<td class="col_local_administracion"><?php echo $obj_data["administracion"]; ?></td>
									<td class="col_local_tipo_de_punto"><?php echo $obj_data["tipo"]; ?></td>
									<td class="col_qty"><?php echo $obj_data["qty"]; ?></td>
									<?php

									foreach ($period_arr as $period_index => $period_val) {
										foreach ($cols as $col_index => $col_data) {
											if(isset($obj_by_period[$period_index][$obj_data["local_id"]."".$obj_data["canal_de_venta_id"]])){
												if(isset($obj_by_period[$period_index][$obj_data["local_id"]."".$obj_data["canal_de_venta_id"]][$col_index])){

														if($col_index=="total_apostado"){
														?>	
															<td class="mostrado">
															<?php 
																echo $obj_by_period[$period_index][$obj_data["local_id"]."".$obj_data["canal_de_venta_id"]][$col_index]; 
															?>
															</td>
														<?php	
														}else if($col_index=="total_pagado"){
															if($obj_data["local_id"]==1){
															?>	
																<td class="<?php echo $period_index; ?> oculto">
																<?php 
																	echo $obj_by_period[$period_index][$obj_data["local_id"]."".$obj_data["canal_de_venta_id"]]["total_ganado"]; 
																?>
																</td>
															<?php	
															}else{
															?>
																<td class="<?php echo $period_index; ?> oculto">
																<?php	
																	echo $obj_by_period[$period_index][$obj_data["local_id"]."".$obj_data["canal_de_venta_id"]][$col_index]; ?>
																</td>
																
														<?php		
															}
														}else{
														?>	
															<td class="<?php echo $period_index; ?> oculto">
															<?php	
																echo $obj_by_period[$period_index][$obj_data["local_id"]."".$obj_data["canal_de_venta_id"]][$col_index]; 
															?>
															</td>
														<?php	
														}
										
												}else{
													if($col_index=="total_apostado"){
													?>	
														<td class="mostrado">-</td>
													<?php	
													}else{
													?>	
														<td class="<?php echo $period_index; ?> oculto">-</td>
													<?php	
													}

												}								
											}else{
												if($col_index=="total_apostado"){
												?>	
													<td class="mostrado">-</td>
												<?php	
												}else{
												?>
													<td class="<?php echo $period_index; ?> oculto">-</td>
												<?php
												}
											}
										}
									}
									?>
								</tr>
								<?php	
								if(count($new_obj)> $obj_index+1){
									$next_object = $new_obj[(int)$obj_index+1];
									if(strcoll($obj_data["canal_de_venta_id"],$next_object["canal_de_venta_id"]) != 0){
										?>
										<tr class="total_reporte clickable-row" >
											<td>
												<button class="btn_collapse_expand_row_reporte_apuestas parent parent_row_collapse_expand_<?php echo $cdv_index; ?>" id="<?php echo $cdv_index; ?>" >
													<span class="glyphicon glyphicon-plus"></span>
												</button>
											</td>							
											<td colspan="7" class="etiqueta_total nombre_canal_de_venta_reporte">
												Total Canal <?php echo $cdv_nombre; ?>
											</td>
											<?php	
												foreach ($period_arr as $period_index => $period_val) {
													foreach ($cols as $col_index => $col_data) {
														if(isset($obj_total_by_period[$period_index][$obj_data["canal_de_venta_id"]])){
															if(isset($obj_total_by_period[$period_index][$obj_data["canal_de_venta_id"]][$col_index])){


																if($col_index=="total_apostado"){
																?>	
																	<td class="mostrado">
																		<?php	
																			echo $obj_total_by_period[$period_index][$obj_data["canal_de_venta_id"]][$col_index]; 
																		?>
																	</td>
																<?php	
																}else{
																	?>
																	<td class="<?php echo $period_index; ?> oculto">
																		<?php	
																			echo $obj_total_by_period[$period_index][$obj_data["canal_de_venta_id"]][$col_index];
																		?>
																	</td>
																<?php	
																}
															}else{
																if ($col_index=="total_apostado") {
																?>		
																	<td class="mostrado">-</td>
																<?php	
																}else{
																?>	
																	<td class="<?php echo $period_index; ?> oculto">-</td>
																<?php	
																}
															}								
														}else{

															if($col_index=="total_apostado"){
															?>	
																<td class="mostrado">-</td>  
															<?php	
															}else{
															?>	
																<td class="<?php echo $period_index; ?> oculto">-</td>
															<?php	
															}
														}
													}
												}
										?>
										</tr>
									<?php	
									}
								}
								if(count($new_obj)-1 == $obj_index){
									?>
									<tr class="total_reporte clickable-row" id="<?php echo $cdv_index; ?>">
										<td>
											<button class="btn_collapse_expand_row_reporte_apuestas parent parent_row_collapse_expand_<?php echo $cdv_index; ?>" id="<?php echo $cdv_index; ?>">
												<span class="glyphicon glyphicon-plus"></span>
											</button>
										</td>
										<td colspan="7" class="etiqueta_total nombre_canal_de_venta_reporte">
											Total Canal <?php echo $cdv_nombre; ?>								
										</td>
										<?php
												foreach ($period_arr as $period_index => $period_val) {
													foreach ($cols as $col_index => $col_data) {
														if(isset($obj_total_by_period[$period_index][$obj_data["canal_de_venta_id"]])){
															if(isset($obj_total_by_period[$period_index][$obj_data["canal_de_venta_id"]][$col_index])){

						
																if($col_index=="total_apostado"){
																?>	
																	<td class="mostrado">
																		<?php	
																			echo $obj_total_by_period[$period_index][$obj_data["canal_de_venta_id"]][$col_index]; 
																		?>	
																	</td>
																<?php	
																}else{
																	?>
																	<td class="<?php echo $period_index; ?> oculto" >
																		<?php
																		echo $obj_total_by_period[$period_index][$obj_data["canal_de_venta_id"]][$col_index]; ?>
																	</td> 
																<?php	   
																}
															}else{
																if($col_index=="total_apostado") {
																?>	
																	<td class="mostrado">-</td>
																<?php	
																}else{
																?>	
																	<td class="<?php echo $period_index; ?> oculto" >-</td>
																<?php	
																}
															}								
														}else{
															if($col_index=="total_apostado") {
															?>	
																<td class="mostrado">-</td>
															<?php	
															}else{
															?>	
																<td class="<?php echo $period_index; ?> oculto" >-</td>
															<?php	
															}
														}
													}
												}
											?>
									</tr>
									<?php	
								}
							}
						}		
					}
				}
			?>
		</tbody>
		<tfoot>
			<tr class='total_reporte clickable-row'>
				<td class="td_btn_collapse_expand nombre_canal_de_venta_reporte nombre_local_reporte col_local_propiedad col_local_asesor_nombre col_local_administracion col_local_tipo_de_punto col_qty"></td>	
				<td colspan="7" class="etiqueta_total td_btn_collapse_expand nombre_canal_de_venta_reporte nombre_local_reporte col_local_propiedad col_local_asesor_nombre col_local_administracion col_local_tipo_de_punto col_qty">Total Canales</td>
				<?php
					foreach ($obj_super_total_by_period as $index_stotal => $val_stotal) {
						?>
						<td class="mostrado">
							<?php echo $val_stotal["total"]["total_apostado"]; ?>
						</td>
						<td class="<?php echo $val_stotal["total"]["period"]; ?> oculto">
							<?php echo $val_stotal["total"]["total_ganado"]; ?>
						</td>		
						<td class="<?php echo $val_stotal["total"]["period"]; ?> oculto">
							<?php echo $val_stotal["total"]["total_pagado"]; ?>
						</td>
						<td class="<?php echo $val_stotal["total"]["period"]; ?> oculto">
							<?php echo $val_stotal["total"]["por_pagar"]; ?>
						</td>		
						<td class="<?php echo $val_stotal["total"]["period"]; ?> oculto">
							<?php echo $val_stotal["total"]["net_win"]; ?>
						</td>
						<td class="<?php echo $val_stotal["total"]["period"]; ?> oculto">
							<?php echo $val_stotal["total"]["hold"]; ?>
						</td>
						<td class="<?php echo $val_stotal["total"]["period"]; ?> oculto">
							<?php echo $val_stotal["total"]["num_tickets"]; ?>
						</td>
						<td class="<?php echo $val_stotal["total"]["period"]; ?> oculto">
							<?php echo $val_stotal["total"]["num_tickets_ganados"]; ?>
						</td>
						<td class="<?php echo $val_stotal["total"]["period"]; ?> oculto">
							<?php echo $val_stotal["total"]["num_tickets_ganados_pagados"]; ?>
						</td>
						<td class="<?php echo $val_stotal["total"]["period"]; ?> oculto">
							<?php echo $val_stotal["total"]["num_tickets_por_pagar"]; ?>
						</td>						
						<td class="<?php echo $val_stotal["total"]["period"]; ?> oculto">
							<?php echo $val_stotal["total"]["apuesta_x_ticket"]; ?>
						</td>		
						<td class="<?php echo $val_stotal["total"]["period"]; ?> oculto">
							<?php echo $val_stotal["total"]["tickets_premiados"]; ?>
						</td>
						<td class="<?php echo $val_stotal["total"]["period"]; ?> oculto">
							<?php echo $val_stotal["total"]["total_depositado_web"]; ?>
						</td>
						<td class="<?php echo $val_stotal["total"]["period"]; ?> oculto">
							<?php echo $val_stotal["total"]["total_retirado_web"]; ?>
						</td>
						<?php		
					}	
				?>
			</tr>			
		</tfoot>
	</table>
