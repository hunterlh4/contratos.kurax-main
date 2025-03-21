<?php
$locales_arr = [];
$locales_command = "SELECT l.id, l.nombre FROM tbl_locales l";
$locales_command.=" WHERE   l.operativo = 1";
$locales_command.=" AND l.estado = 1";
if($login["usuario_locales"]){
	$locales_command.=" AND l.id IN (".implode(",", $login["usuario_locales"]).")";
}
$locales_command.=" ORDER BY l.nombre ASC";


$locales_query = $mysqli->query($locales_command);
if($mysqli->error){
	print_r($mysqli->error);
	echo "\n";
	echo $locales_command;
	exit();
}
$locales_arr["_all_"]="Seleccione un local";
while($l=$locales_query->fetch_assoc()){
	$locales_arr[$l["id"]]='['.$l["id"].'] '.$l["nombre"];
}


if($locales_arr){ ?>
<div class="container-fluid">
	<div class="row">
		<div class="panel">
			<div class="panel-heading">
				<h4 class="panel-title">
					Filtros
				</h4>
			</div>
			<div class="panel-body">
				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-2">
						<div class="form-group">
						<p class="text-center iniciocustom">Local </p>
							<select class="form-control item_config select2" name="local_id" id="local_id">
								<?php
								foreach ($locales_arr as $local_id => $local_name) {
									?><option value="<?php echo $local_id;?>"><?php echo $local_name;?></option><?php
								}
								?>
							</select>
						</div>
					</div>
					<div class="col-lg-2 col-xs-12">
							<p class="text-center iniciocustom">Fecha Inicio </p>
							<div class="form-group">
								<div class="input-group col-xs-12 ">
									<input class="fecha_inicio_enviar"
						    			type="hidden" 
										name="liq_filtro_inicio_fecha " 
										value="<?php //echo $liquidaciones_filtro["liq_filtro_inicio_fecha"];?>" 
						    			data-real-date="input_text-liq_filtro_inicio_fecha">
									<input 
										type="text" 
										class="form-control filtro_datepicker fecha_inicio_enviar " 
										id="input_text-liq_filtro_inicio_fecha" 
										value="<?php //echo date("d-m-Y", strtotime($liquidaciones_filtro["liq_filtro_inicio_fecha"]));?>">
									<label class="input-group-addon glyphicon glyphicon-calendar" for="input_text-liq_filtro_inicio_fecha"></label>
								</div>
							</div>
						</div>
						<div class="col-lg-2 col-xs-12">
								<p class="text-center fincustom">Fecha Fin</p>
								<div class="form-group form-group-fin_fecha">
									<div class="input-group col-xs-12 ">
										<input class="fecha_fin_enviar"
							    			type="hidden" 
											name="liq_filtro_fin_fecha" 
											value="<?php //echo $liquidaciones_filtro["liq_filtro_fin_fecha"];?>" 
							    			data-real-date="input_text-liq_filtro_fin_fecha">
										<input 
											type="text" 
											class="form-control filtro_datepicker fecha_fin_enviar " 
											id="input_text-liq_filtro_fin_fecha" 
											value="<?php //echo date("d-m-Y", strtotime($liquidaciones_filtro["liq_filtro_fin_fecha"]));?>" >
										<label class="input-group-addon glyphicon glyphicon-calendar" for="input_text-liq_filtro_fin_fecha"></label>
									</div>
								</div>
						</div>
					<!-- <div class="col-xs-12 col-sm-6 col-md-6 col-lg-2">
						<div class="form-group">
							<div class="control-label">Fecha Inicio:</div>
							<div class="input-group">
								<input
									type="hidden"
									id="sec_caja_reporte_fecha_inicio"
									class="input_text item_config"

									name="fecha_inicio"
									value="<?php echo date("Y-m-d");?>"
									data-real-date="input_text-sec_caja_reporte_fecha_inicio">
								<input
									type="text"
									class="form-control sec_caja_reporte_fecha_datepicker  filtro_datepicker  "
									id="input_text-sec_caja_reporte_fecha_inicio"
									value="<?php echo date("Y-m-d");?>"
									readonly="readonly"
									>
								<label class="input-group-addon glyphicon glyphicon-calendar" for="input_text-sec_caja_reporte_fecha_inicio"></label>
							</div>
						</div>
					</div>
					<div class="col-xs-12 col-sm-6 col-md-6 col-lg-2">
						<div class="form-group">
							<div class="control-label">Fecha Fin:</div>
							<div class="input-group">
								<input
									type="hidden"
									id="sec_caja_reporte_fecha_fin"
									class="input_text item_config"
									name="fecha_fin"
									value="<?php echo date("Y-m-d");?>"
									data-real-date="input_text-sec_caja_reporte_fecha_fin">
								<input
									type="text"
									class="form-control sec_caja_reporte_fecha_datepicker filtro_datepicker"
									id="input_text-sec_caja_reporte_fecha_fin"
									value="<?php echo date("d-m-Y");?>"
									readonly="readonly"
									>
								<label class="input-group-addon glyphicon glyphicon-calendar" for="input_text-sec_caja_reporte_fecha_fin"></label>
							</div>
						</div>
					</div> -->
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-6 text-rc">
						<br>
						<button class="btn btn-success btn_buscar" id="btn_buscar">
							<span class="glyphicon glyphicon-search"></span>
							Consultar
						</button>
					</div>
				</div>
			</div>
		</div>
		<div class="panel">
			<div class="panel-body table_container">
			</div>
		</div>
		<div class="btn_up"><i class="glyphicon glyphicon-triangle-top"></i></div>
	</div>
</div>
<?php
}
?>
<div class="row">
	<div class="col-xs-5">
		<div class="panel panel-default panel_datos_sistema">
			<div class="panel-heading">
				<h3 class="panel-title">Datos del Sistema</h3>
			</div>
			<div class="panel-body cajaTbl">
				<div class="row">
					<table class="table cajatable table-condensed table-bordered table_datos_del_sistema">
						<thead>
							<tr>
								<th class="col-xs-5">Tipo</th>
								<th class="">TT Apostado</th>
								<th class="">TT Pagado</th>
								<th class="col-xs-2">Resultado</th>
							</tr>
						</thead>
						<tbody>
							<tr class="lcdt" data-tipo_id="1226">
										<td> 
											Apuestas Deportivas	<br><small>(<i class="">Apuestas deportivas realizadas en caja</i>)</small>
										</td>
										<td class=" text-right apuestaDeportivaApostado"></td>
										<td class="text-right apuestaDeportivaPagado"></td>
										<td class="text-right line_total apuestaDeportivatotal">0.00</td>
							</tr>
							<tr class="lcdt" data-tipo_id="1226">
										<td> 
											Web	<br><small>(<i class="">Resultado de depósitos y retiros Web</i>)</small>
										</td>
										<td class=" text-right webApostado"></td>
										<td class="text-right webPagado"></td>
										<td class="text-right line_total webTotal">0.00</td>
							</tr>
							<tr class="lcdt" data-tipo_id="1226">
										<td> 
											Golden Race	<br><small>(<i class="">Juegos Virtuales</i>)</small>
										</td>
										<td class=" text-right goldenRaceApostado"></td>
										<td class="text-right goldenRacePagado"></td>
										<td class="text-right line_total goldenRaceTotal">0.00</td>
							</tr>	
							<tr class="lcdt" data-tipo_id="1226">
										<td> 
											Billetero Terminal	<br>
										</td>
										<td class="text-right terminalApostado"></td>
										<td class="text-right terminalPagado"></td>
										<td class="text-right line_total terminalTotal">0.00</td>
							</tr>
							<!-- <tr class="lcdt" data-tipo_id="1226">
										<td> 
											Billetero Terminal 02	<br>
										</td>
										<td class="text-right terminal2Apostado"></td>
										<td class="text-right terminal2Pagado"></td>
										<td class="text-right line_total terminal2Total">0.00</td>
							</tr> -->
							<tr class="lcdt" data-tipo_id="1226">
										<td> 
											Cash In/Out	<br>
										</td>
										<td class=" text-right cashApostado"></td>
										<td class="text-right cashPagado"></td>
										<td class="text-right line_total cashTotal">0.00</td>
							</tr>
							<tr class="lcdt" data-tipo_id="1226">
										<td> 
											La Polla	<br>
										</td>
										<td class=" text-right pollaApostado"></td>
										<td class="text-right pollaPagado"></td>
										<td class="text-right line_total pollaTotal">0.00</td>
							</tr>
							<tr class="lcdt" data-tipo_id="1226">
										<td> 
											Bingo	<br><small>(<i class="">Sistema de Bingo</i>)</small>
										</td>
										<td class=" text-right bingoApostado"></td>
										<td class="text-right bingoPagado"></td>
										<td class="text-right line_total bingoTotal">0.00</td>
							</tr>
					   </tbody>
						<tfoot>
							<tr>
																				<th>Total</th>
								<th class="text-right total_ingreso">0.00</th>
								<th class="text-right total_salida">0.00</th>
								<th class="text-right total_resultado">0.00</th>
							</tr>
						</tfoot>
					</table>
				</div>
			</div>
		</div>
	</div>
	<div class="col-xs-7 no-pad">
		<div class="panel panel-default panel_datos_fisicos">
			<div class="panel-heading">
				<h3 class="panel-title">Datos Fisicos</h3>
			</div>
			<div class="panel-body cajaTbl">
				<div class="row">
					<table class="table cajatable table-condensed table-bordered table_datos_fisicos">
						<thead>
							<tr>
								<th>Tipo</th>
								<th>Monto</th> 
							</tr>
						</thead>
						<tbody> 
							<tr>
								<th>Resultado del Negocio</th>
								<td class="diferencia text-right resultadoNegocioMonto" style="">0.00</td>
							</tr>
							<tr>
								<th>Participación cliente PBET</th>
								<td class="diferencia text-right participacionPbetMonto" style="">0.00</td>
							</tr>
							<tr>
								<th>Participación Cliente GR</th>
								<td class="diferencia text-right participacionGrMonto" style="">0.00</td>
							</tr>
							<tr>
								<th>Participación Bingo</th>
								<td class="diferencia text-right participacionBingoMonto" style="">0.00</td>
							</tr>
							<tr>
								<th>Participación Web</th>
								<td class="diferencia text-right participacionWebMonto" style="">0.00</td>
							</tr>											<tr>
								<th>Participación Total</th>
								<td class="diferencia text-right participacionTotalMonto" style="">0.00</td>
							</tr>
							<tr>
								<th>Participación OAT PBET</th>
								<td class="diferencia text-right participacionOatPbetMonto" style="">0.00</td>
							</tr>											<tr>
								<th>Participación OAT GR</th>
								<td class="diferencia text-right participacionOATGrMonto" style="">0.00</td>
							</tr>
							<tr>
								<th>Participación OAT Bingo</th>
								<td class="diferencia text-right participacionOATBingoMonto" style="">0.00</td>
							</tr>
							<tr>
								<th>Participación OAT Web</th>
								<td class="diferencia text-right participacionOATWebMonto" style="">0.00</td>
							</tr>
							<tr>
								<th>Participación TT OAT</th>
								<td class="diferencia text-right participacionOATmonto" style="">0.00</td>
							</tr>
							<!-- <tr>
								<th>Devoluciones <small>(<i class="">Juegos Virtuales</i>)</small> </th>
								<td class="diferencia text-right devolucionesMonto" style="">0.00</td>
							</tr>
							<tr>
								<th>Depósito Part.</th>
								<td class="diferencia text-right depositoPartMonto" style="">0.00</td>
							</tr>
							<tr>
								<th>Bonos y Promociones</th>
								<td class="diferencia text-right bonosPromocionesMonto" style="">0.00</td>
							</tr>
							<tr>
								<th>Pagos Manuales <small>(<i class="">Autorizados por Soporte</i>)</small></th>
								<td class="diferencia text-right pagosManualesMonto" style="">0.00</td>
							</tr>
							<tr>
								<th>TT Depositado</th>
								<td class="diferencia text-right ttDepositadoMonto" style="">0.00</td>
							</tr>
							<tr>
								<th>Pendiente Depósito</th>
								<td class="diferencia text-right pendienteDepositoMonto" style="">0.00</td>
							</tr> 
							<tr>
								<th>Diferencia</th>
								<td class="diferencia text-right diferenciaMonto" style="">0.00</td>
							</tr> -->
						</tbody>
						<tfoot>

						</tfoot>
					</table>
				</div>
			</div>
		</div>
	</div>
	<!-- <div class="col-xs-3">
		<div class="panel panel-default panel_info_turno">
			<div class="panel-heading">
				<h3 class="panel-title">Información del turno</h3>
			</div>
			<div class="panel-body cajaTbl">
				<div class="row">
					<table class="table cajatable table-condensed table-bordered table_info_turno">
						<tbody>
							<tr>
								<th>ID del turno</th>
								<td>676973</td>
							</tr>
							<tr>
								<th>Estado</th>
								<td class="bg-warning">Abierto</td>
							</tr>
							<tr>
								<th id="idcc" data-idcc="3146">Local</th>
								<td>[3146] Activaciones</td>
							</tr>
							<tr>
								<th>Caja</th>
								<td>Caja 1</td>
							</tr>
							<tr>
								<th>Turno</th>
								<td>1</td>
							</tr>
							<tr>
								<th>Fecha</th>
								<td id="th_fecha_operacion">2021-09-10</td>
							</tr>
							<tr>
								<th>Apertura</th>
								<td>2021-09-10 17:48:54</td>
							</tr>
							<tr>
								<th>Cierre</th>
								<td></td>
							</tr>
							<tr>
								<th>Usuario</th>
								<td>[test.cajero1] Cajero Test</td>
							</tr>
																		<tr title="Fondo de Caja">
								<th>Monto Inicial</th>
								<td>0.00</td>
							</tr>
																		<tr title="Monto mínimo a depositar">
								<th>Valla</th>
								<td>0.00</td>
							</tr>
							<tr title="Deposito del dia (Dinero Encontrado - Monto Inicial)">
								<th>Depósito</th>
								<td>0.00												</td>
							</tr>
							<tr title="Monto a depositar (Apertura - Monto Inicial - Desposito Venta)">
								<th>Depositar <br> <i class="small">(Del turno anterior)</i></th>
																				<td class="">0.00</td>
							</tr>
							<tr title="Deuda Slot">
																				<th>Deuda Slot</th>
								<td class="deuda_slot">0.00</td>
							</tr>
							<tr title="Deuda Boveda">
																				<th>Deuda Boveda</th>
								<td class="deuda_boveda">0.00</td>
							</tr>

							<tr title="Saldo Kasnet">
																				<th>Saldo Kasnet</th>
								<input type="hidden" id="fixed_saldo_kasnet" value="0">
								<td class="saldo_kasnet">0.00</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
		<div class="panel panel-default panel_observaciones_turno">
			<div class="panel-heading">
				<h3 class="panel-title">Observaciones</h3>
			</div>
			<div class="panel-body cajaTbl">
				<div class="row">
					<textarea class="form-control" name="observaciones"></textarea>		
			</div>
			</div>
		</div>
	</div> -->
</div>

