<?php
$return = array();
$return["memory_init"]=memory_get_usage();
$return["time_init"] = microtime(true);
include("db_connect.php");
if(isset($_POST["opt"])){
	if($_POST["opt"]=="transaccion_bancaria"){
		if(array_key_exists("data", $_POST)){
			if(array_key_exists("ids", $_POST["data"])){
				$trans_arr = array();
				$trans_command = "
					SELECT
						t.at_unique_id,
						t.banco_id,
						t.moneda_id,
						DATE_FORMAT(t.fecha_operacion,'%d/%m/%Y') AS fecha_operacion,
						IF(t.banco_id = 12,t.referencia,CONCAT(t.movimiento,': ',t.referencia,'')) AS referencia,
						IF(t.banco_id = 12, t.importe,t.abono) AS importe,
						t.itf,
						t.numero_movimiento,
						t.local_id,
						t.comentario,
						b.nombre AS banco_nombre,
						b.color_hex AS banco_color_hex,
						m.simbolo AS moneda_simbolo,
						m.sigla AS moneda_sigla,
						t.usado,
						-- IF(t.banco_id = 12, t.importe,t.abono) AS restante /* CAMBIAR ESTO! */
						t.restante
					FROM tbl_repositorio_transacciones_bancarias t 
					LEFT JOIN tbl_bancos b ON (b.id = t.banco_id)
					LEFT JOIN tbl_moneda m ON (m.id = t.moneda_id)
					WHERE t.id IS NOT NULL
					AND t.at_unique_id IN ('".implode("','", $_POST["data"]["ids"])."')
					ORDER BY t.restante DESC";
				$trans_query = $mysqli->query($trans_command);
				if($mysqli->error){
					echo $mysqli->error;
					echo $trans_command;
					exit();
				}
				$total_importe = 0;
				$total_usado = 0;
				$total_restante = 0;
				while ($t=$trans_query->fetch_assoc()) {
					$trans_arr[]=$t;
					$total_importe+=$t["importe"];
					$total_usado+=$t["usado"];
					$total_restante+=$t["restante"];
					// $trans=$t;
				}
				// exit;
				// $div_command = "
				// 	SELECT 
				// 		d.local_id,
				// 		d.monto,
				// 		l.nombre AS local_nombre
				// 	FROM tbl_transaccion_bancaria_division d
				// 	LEFT JOIN tbl_locales l ON (l.id = d.local_id)
				// 	WHERE d.trans_unique_id = '".$trans["at_unique_id"]."';
				// 			";
				// $div_query = $mysqli->query($div_command);
				// if($mysqli->error){
				// 	echo $mysqli->error;
				// 	echo $div_command;
				// 	exit();
				// }
				// while ($d=$div_query->fetch_assoc()) {
				// 	$trans["div"][]=$d;
				// }
				// 0ca4530f86e2d178c36437ba7c76d9b3,cc9c2c3b2f976a686df96dda9046f724,7b645e7d49fc8b603cee684de85f0c45
				$dt_arr = array();
				$dt_command = "
					SELECT
						nombre,
						codigo
					FROM tbl_deudas_tipos
					WHERE estado = '1'
					ORDER BY prioridad ASC
								";
				$dt_query = $mysqli->query($dt_command);
				if($mysqli->error){
					echo $mysqli->error;
					echo $dt_command;
					exit();
				}
				while ($dt=$dt_query->fetch_assoc()) {
					$dt_arr[$dt["codigo"]]=$dt["nombre"];
				}

				?>
				<!-- <pre><?php //print_r($trans_arr);?></pre> -->
				<div class="hidden">
					<input type="text" id="trans_at_unique_id" value="<?php echo implode(",",$_POST["data"]["ids"]);?>">
					<input type="text" id="trans_importe" value="<?php echo $total_importe;?>">
					<input type="text" id="trans_usado" value="<?php echo $total_usado;?>">
					<input type="text" id="trans_restante" value="<?php echo $total_restante;?>">
				</div>
				<div class="row">
					<div class="col-xs-12"> <!-- TRANS INFO -->
						<div class="panel panel-info make_me_collapse_body" id="panel_trans_info">
							<div class="panel-heading">
								<div class="panel-title">
									Transaccion<?php if(count($trans_arr>1)){ echo "es"; }?>							
								</div>
								<div class="panel-controls">
									<ul class="panel-buttons">
										<li><span class="">Disponible: <strong><?php echo number_format($total_restante,2);?></strong></span></li>
										<li>
											<button class="btn-panel-control icon icon-panel-collapse"></button>
										</li>
									</ul>
								</div>
							</div>
							<div class="panel-body collapse in">
								<table class="table table-bordered">
									<tr>
										<th>Banco</th>
										<th>Moneda</th>
										<th># Movimiento</th>
										<th>Fecha Operacion</th>
										<th>Referencia</th>
										<th>Importe</th>
										<th>Usado</th>
										<th>Restante</th>
									</tr>
									<?php
									foreach ($trans_arr as $key => $trans) {
										?>
										<tr>
											<td><span class="label" style="background-color: #<?php echo $trans["banco_color_hex"];?>;"><?php echo $trans["banco_id"];?> - <?php echo $trans["banco_nombre"];?></span></td>
											<td><?php echo $trans["moneda_simbolo"];?><?php echo $trans["moneda_id"];?> - <?php echo $trans["moneda_sigla"];?></td>	
											<td><?php echo $trans["numero_movimiento"];?></td>							
											<td><?php echo $trans["fecha_operacion"];?></td>								
											<td><?php echo $trans["referencia"];?></td>
											<td class="text-right"><?php echo number_format($trans["importe"],2);?></td>
											<td class="text-right <?php echo ($trans["usado"] > 0 ? "text-warning" : "text-success") ?>"><?php echo number_format($trans["usado"],2);?></td>
											<td class="text-right <?php echo ($trans["restante"] > 0 ? "text-success" : "text-danger") ?>"><?php echo number_format($trans["restante"],2);?></td>
										</tr>
										<?php
									}
									?>
										<tr>
											<th colspan="5">Total</th>
											<th class="text-right"><?php echo number_format($total_importe,2);?></th>
											<th class="text-right <?php echo ($total_usado > 0 ? "text-warning" : "text-success") ?>"><?php echo number_format($total_usado,2);?></th>
											<th class="text-right <?php echo ($total_restante > 0 ? "text-success" : "text-danger") ?>"><?php echo number_format($total_restante,2);?></th>
										</tr>
								</table>
							</div>
						</div>
					</div>
					<div class="col-xs-12"> <!-- DEUDA INFO -->
						<div class="panel panel-primary" id="panel_deuda">
							<div class="panel-heading">
								<div class="panel-title">Facturacion</div>
							</div>
							<div class="panel-body">
								<div class="form-inline">
									<div class="form-group">
										<label for="select_year">Año: </label>
										<select class="form-control make_me_select2" id="select_year">
											<?php
											for ($s_y=date("Y"); $s_y >= 2015 ; $s_y--) { 
												?><option <?php if($s_y==date("Y")){ ?> selected="selected"<?php } ?> value="<?php echo $s_y;?>"><?php echo $s_y;?></option><?php
											}
											?>
										</select>
									</div>
									<div class="form-group">
										<label for="select_mes">Mes: </label>
										<select class="form-control make_me_select2" id="select_mes">
											<?php
											for ($s_m=1; $s_m <= 12 ; $s_m++) { 
												if($s_m<10){
													$s_m="0".$s_m;
												}
												?><option <?php if($s_m==date("m",strtotime("-1 month"))){ ?> selected="selected"<?php } ?> value="<?php echo $s_m;?>"><?php echo $s_m;?></option><?php
											}
											?>
										</select>
									</div>
									<div class="form-group">
										<label for="select_periodo">Periodo: </label>
										<select class="form-control make_me_select2" id="select_periodo">
											<option><<<<<<</option>
										</select>
									</div>
									<button class="btn btn-primary" id="load_list_btn"><span class="glyphicon glyphicon-download-alt"></span> Cargar</button>
								</div>
								<div class="row"><br></div>
								<div class="row">
									<div class="col-xs-4">
										<div class="panel panel-primary" id="locales_list_panel">
											<div class="panel-heading">
												<input type="text" class="list_search_input form-control" data-holder="locales_list_table" placeholder="Ej: Omega" >
											</div>
											<div class="panel-body">
												<div class="locales_list_holder">
													<table id="locales_list_table" class="table table-condensed">												
													</table>
												</div>
											</div>
										</div>
									</div>
									<div class="col-xs-8">
										<div class="row">
											<div class="col-xs-8">
												<button class="btn btn-block btn-info repartir_btn"">Repartir</button>
											</div>
											<div class="col-xs-3">
												<button class="btn btn-block btn-warning limpiar_btn""><span class="glyphicon glyphicon-repeat"></span> Limpiar</button>
											</div>
											<div class="col-xs-1">
												<button class="btn btn-block btn-danger remove_locales_btn"><span class="glyphicon glyphicon-remove"></span></button>
											</div>
										</div>
										
										<div id="deudas_holder">
											<table class="table table-bordered table-condensed" id="deudas_holder_table">
												<thead>
													<tr>
														<th id="dt_codigo_local">Local</th>
														<?php
														foreach ($dt_arr as $dt_codigo => $dt_nombre) {
															?>
															<th id="dt_codigo_<?php echo $dt_codigo;?>"><?php echo $dt_nombre;?></th>
															<?php
														}
														?>
														<th id="dt_codigo_total">Total</th>
														<th id="dt_codigo_abono">Abono</th>
														<th id="dt_codigo_saldo">Saldo</th>
														<th id="dt_codigo_opt">OPT</th>
													</tr>
												</thead>
												<tbody id="deudas_holder_tbody">											
												</tbody>
											</table>
										</div>
										<table class="table table-bordered" id="trans_info_table">
											<tr>
												<th>Disponible</th>
												<th>Usado</th>
												<th>Restante</th>
											</tr>
											<tr>
												<td id="tit_importe"><?php echo number_format($total_importe,2);?></td>
												<td id="tit_usado"><?php echo number_format($total_usado,2);?></td>
												<td id="tit_restante"><?php echo number_format($total_restante,2);?></td>
											</tr>
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xs-12"></div>
				</div>
				<?php
			}else{
				echo "error";
				print_r($_POST);
			}
		}else{
			echo "error";
			print_r($_POST);
		}
	}
	if($_POST["opt"]=="load_locales"){
		$command = "
			SELECT
				d.local_id,
				d.tipo,
				l.nombre AS local_nombre
			FROM tbl_deudas d
			LEFT JOIN tbl_locales l ON (l.id = d.local_id)
			WHERE d.periodo_year = '".$_POST["data"]["year"]."'
			AND d.periodo_mes = '".$_POST["data"]["mes"]."'
			AND d.periodo_rango = '".$_POST["data"]["rango"]."'
			AND d.estado = '1'
			GROUP BY
				d.tipo,
				d.local_id
			ORDER BY l.nombre
			";
		$query = $mysqli->query($command);
		if($mysqli->error){
			print_r($mysqli->error);
			exit();
		}
		$locales_arr = array();
		while($l = $query->fetch_assoc()) {
			$locales_arr[$l["local_id"]]["local_nombre"]=$l["local_nombre"];
			$locales_arr[$l["local_id"]]["deudas"][$l["tipo"]]=$l;
		}
		foreach ($locales_arr as $local_id => $local_data) {
			?>
			<tr data-local_id="<?php echo $local_id;?>">
				<td>[<?php echo $local_id;?>]</td>
				<td class="strong"><?php echo $local_data["local_nombre"];?></td>
				<td><button class="btn btn-default btn-block"><span class="glyphicon glyphicon-chevron-right"></span></button></td>
			</tr>			
			<?php
		}
	}
	if($_POST["opt"]=="load_local_deuda"){
		$command = "
			SELECT
				-- d.at_unique_id,
				d.local_id,
				d.tipo,
				dt.nombre AS tipo_nombre,
				-- SUM(d.monto) AS monto, -- CAMBIAR ACÁ! // CAMBIADO,
				CAST(
						(
							SUM(d.monto) - 
						(
							IF
								(
									(
										SELECT
											SUM(p.abono) AS abono
										FROM tbl_pagos p
										WHERE p.periodo_year = d.periodo_year
										AND p.periodo_mes = d.periodo_mes
										AND p.periodo_rango = d.periodo_rango
										AND p.local_id = d.local_id
										AND p.deuda_tipo_id = d.tipo_id
									) > 0
									,(
										SELECT
											SUM(p.abono) AS abono
										FROM tbl_pagos p
										WHERE p.periodo_year = d.periodo_year
										AND p.periodo_mes = d.periodo_mes
										AND p.periodo_rango = d.periodo_rango
										AND p.local_id = d.local_id
										AND p.deuda_tipo_id = d.tipo_id
									)
									,0
								)
							)
						)  AS DECIMAL(20,2)
					) AS monto,
				l.nombre AS local_nombre
			FROM tbl_deudas d
			LEFT JOIN tbl_locales l ON (l.id = d.local_id)
			LEFT JOIN tbl_deudas_tipos dt ON (dt.codigo  = d.tipo)
			-- LEFT JOIN tbl_pagos p ON (p.periodo_year = d.periodo_year AND p.periodo_mes = d.periodo_mes AND p.periodo_rango = d.periodo_rango AND p.deuda_tipo_id = d.tipo_id)
			WHERE d.periodo_year = '".$_POST["data"]["year"]."'
			AND d.periodo_mes = '".$_POST["data"]["mes"]."'
			AND d.periodo_rango = '".$_POST["data"]["rango"]."'
			AND d.local_id = '".$_POST["data"]["local_id"]."'
			AND d.estado = '1'
			GROUP BY
				d.tipo,
				d.local_id
			ORDER BY l.nombre, dt.prioridad ASC
			";
		$query = $mysqli->query($command);
		if($mysqli->error){
			print_r($mysqli->error);
			exit();
		}
		$local_arr = array();
		while($l = $query->fetch_assoc()) {
			$local_arr["local_nombre"]=$l["local_nombre"];
			$local_arr["deudas"][$l["tipo"]]=$l;
		}
		$local_id = $_POST["data"]["local_id"];

		$dt_arr = array();
		$dt_command = "
			SELECT
				id,
				nombre,
				codigo
			FROM tbl_deudas_tipos
			WHERE estado = '1'
			ORDER BY prioridad ASC
						";
		$dt_query = $mysqli->query($dt_command);
		if($mysqli->error){
			echo $mysqli->error;
			echo $dt_command;
			exit();
		}
		while ($dt=$dt_query->fetch_assoc()) {
			$dt_arr[$dt["codigo"]]=$dt;
		}
		?>
		<tr 
			class="deuda_local" 
			id="deuda_local_<?php echo $local_id;?>" 
			data-local_id="<?php echo $local_id;?>">
			<td><?php echo $local_id; ?> - <?php echo $local_arr["local_nombre"];?></td>
			<?php
			$sum_deuda = 0;
			foreach ($dt_arr as $dt_codigo => $dt) {
				if(array_key_exists($dt_codigo, $local_arr["deudas"])){
					$deuda = $local_arr["deudas"][$dt_codigo];
					$sum_deuda+=$deuda["monto"];
					?><td>
					<?php echo $deuda["monto"]; ?>
						<div 
							class="form-group deuda_repartir" 
							data-deuda_tipo='<?php echo $dt_codigo;?>' 
							data-deuda_tipo_id='<?php echo $dt["id"];?>'>
							<input type="hidden" class="form-control monto" value="<?php echo $deuda["monto"]; ?>">
							<input type="text" class="form-control amort" value="">
						</div>
					</td><?php
				}else{
					?><td>0.00</td><?php
				}
			}
			?>
			<td class="td_deuda_total"><?php echo $sum_deuda;?></td>
			<td class="td_deuda_abono">0.00</td>
			<td class="td_deuda_saldo">0.00</td>
			<td>
				<button class="btn btn-xs btn-danger remove_local_btn" data-local_id="<?php echo $local_id;?>"><span class="glyphicon glyphicon-remove"></span></button>
			</td>
		</tr>
		<?php
	}
	if($_POST["opt"]=="load_deudas_OLD"){
		$command = "
			SELECT
				d.local_id,
				d.tipo,
				dt.nombre AS tipo_nombre,
				SUM(d.monto) AS monto,
				l.nombre AS local_nombre
			FROM tbl_deudas d
			LEFT JOIN tbl_locales l ON (l.id = d.local_id)
			LEFT JOIN tbl_deudas_tipos dt ON (dt.codigo  = d.tipo)
			WHERE d.periodo_year = '".$_POST["data"]["year"]."'
			AND d.periodo_mes = '".$_POST["data"]["mes"]."'
			AND d.periodo_rango = '".$_POST["data"]["rango"]."'
			AND d.estado = '1'
			GROUP BY
				d.tipo,
				d.local_id
			ORDER BY l.nombre, dt.prioridad ASC
			";
		$query = $mysqli->query($command);
		if($mysqli->error){
			print_r($mysqli->error);
			exit();
		}
		$locales_arr = array();
		while($l = $query->fetch_assoc()) {
			$locales_arr[$l["local_id"]]["local_nombre"]=$l["local_nombre"];
			$locales_arr[$l["local_id"]]["deudas"][$l["tipo"]]=$l;
		}
		foreach ($locales_arr as $local_id => $local_data) {
			?>
			<h3 id="local_h3_<?php echo $local_id;?>">[<?php echo $local_id;?>] <?php echo $local_data["local_nombre"];?></h3>
			<div class="local_holder" id="local_holder_<?php echo $local_id;?>" data-local_id="<?php echo $local_id;?>">
				<button class="btn btn-block btn-info repartir_btn" data-local_id="<?php echo $local_id;?>">Repartir</button>
				<table class="table table-bordered">
					<thead>
						<tr>
							<!-- <th></th> -->
							<th>Tipo</th>
							<th>Deuda</th>
							<th>Amorti.</th>
							<th>Diff</th>
						</tr>
					</thead>
					<tbody>
						<?php
						$sum_deuda = 0;
						foreach ($local_data["deudas"] as $deuda_key => $deuda) {
							$sum_deuda+=$deuda["monto"];
							?>
							<tr class="box_deuda">
								<!-- <td class="box_checkbox"><input type="checkbox" name="" checked="checked"></td> -->
								<td class="box_tipo_nombre"><?php echo $deuda["tipo_nombre"];?></td>
								<td class="box_monto"><?php echo $deuda["monto"];?></td>
								<td class="box_amort">
									<div class="input-group">
										<input type="text" class="form-control box_amort_input" name="" value="0">
										<div class="input-group-addon cursor_pointer repartir_update_btn"><i class="glyphicon glyphicon-refresh"></i></div>
									</div>
								</td>
								<td class="box_diff">0</td>
							</tr>
							<?php
						}
						?>
					</tbody>
						<tr>
							<th >Total</th>
							<th class="box_total_deuda"><?php echo $sum_deuda;?></th>
							<th class="box_total_amort">0</th>
							<th class="box_total_diff">0</th>
						</tr>
				</table>
			</div><?php
		}
	}
}


$return["memory_end"]=memory_get_usage();
$return["time_end"] = microtime(true);
$return["memory_total"]=($return["memory_end"]-$return["memory_init"]);
$return["time_total"]=($return["time_end"]-$return["time_init"]);
// print_r(json_encode($return));
?>