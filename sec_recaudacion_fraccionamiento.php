<?php
if($sub_sec_id=="fraccionamiento"){
	$clientes_arr = array();
	$clientes_command = "SELECT id, nombre FROM tbl_clientes WHERE estado = '1'";
	$clientes_query = $mysqli->query($clientes_command);
	while($c=$clientes_query->fetch_assoc()){
		$clientes_arr[$c["id"]]=$c["nombre"];
	}
	?>
	<div class="modal fraccionamiento_modal" id="fraccionamiento_modal" data-backdrop="static">
		<div class="modal-dialog modal-lg">
			<div class="modal-content modal-rounded">
				<div class="modal-header">
					<button type="button" class="close close_btn"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">Fraccionamiento</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="form-inline col-">
							<div class="col-xs-12">
								<div class="form-group hidden">
									<label for="input_cliente">Cliente: </label>
									<select class="form-control make_me_select2" id="input_cliente" style="width: 100%;">
										<!-- <option value="0">Seleccione un Cliente</option>
										<?php
										foreach ($clientes_arr as $cli_id => $cli_nombre) {
											?>
											<option value="<?php echo $cli_id;?>"><?php echo $cli_nombre;?></option>
											<?php
										}
										?> -->
									</select>
								</div>
							</div>
							<div class="col-xs-12 col-sm-12">
								<div class="form-group ">
									<label for="input_local">Local:</label>
									<select class="form-control make_me_select2" id="input_local" style="width: 100%;">							
									</select>
								</div>
							</div>
							<div class="col-xs-12">
								<div class="form-group hidden">
									<label for="input_periodo">Periodo:</label>
									<select class="form-control make_me_select2" id="input_periodo">							
									</select>
								</div>
							</div>
							<div class="col-xs-12 mt-2">
								<button class="btn btn-default" id="frac_load_btn"><span class="glyphicon glyphicon-download-alt"></span> Cargar</button>
							</div>
						</div>
					</div>
					<div class="row hidden" id="frac_holder">
						<br>
						<div class="col-xs-12 col-sm-12 col-md-6 periodos_holder_scroller">
							<table class="table table-bordered">
								<thead>
									<tr>
										<th>Año</th>
										<th>Mes</th>
										<th>Periodo</th>
										<th>CDV</th>
										<th>Monto</th>
										<th>Opt</th>
									</tr>
								</thead>
								<tbody id="periodos_holder">
								</tbody>
							</table>
						</div>
						<div class="col-xs-12 col-sm-12 col-md-6 hidden" id="cuotas_maker_holder">
							<label class="control-label col-sm-4" for="proceso_id">ID de proceso:</label>
							<div class="col-sm-6">
								<input type="text" id="proceso_id">
							</div>
							
							<!-- <form class="form-inline"> -->
								<div class="form-group">
									<label class="control-label col-sm-4" for="input_monto">Monto: </label>
									<div class="col-sm-6">
										<input class="form-control" type="text" id="input_monto" readonly="readonly" value="300">
									</div>
								</div>
								<div class="form-group">
									<label class="control-label col-sm-4" for="input_cuotas">N° cuotas: </label>
									<div class="col-sm-6">
										<!-- <input class="form-control" type="text" id="input_cuotas" value="13"> -->
										<select class="form-control" id="input_cuotas">
											<?php
											for ($i=1; $i <= 12 ; $i++) { 
												?>
												<option value="<?php echo $i;?>"><?php echo $i;?></option>
												<?php
											}
											?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<label class="control-label col-sm-4" for="input_facturacion">Facturacion: </label>	
									<div class="col-sm-6">								
										<select class="form-control" id="input_facturacion">
											<option value="cut">Cortes</option>
											<option value="months" selected="selected">Mensual</option>
											<option value="fortnight">Quincenal</option>
											<option value="weeks">Semanal</option>
										</select>
									</div>
								</div>
								<div class="form-group  fac_fecha_hidden">
									<label class="control-label col-sm-4">Fecha Inicio:</label>
									<div class="col-sm-6">
										<div class="input-group">
											<input 
												type="hidden" 
												class=""
												name="fecha_inicio" 
												value="<?php echo date("Y-m-d",strtotime("+1 month"));?>" 
												id="fecha_inicio" 
												data-real-date="datepicker-fecha_inicio">
											<input 
												type="text" 
												class="form-control makeme_datepicker" 
												id="datepicker-fecha_inicio" 
												value="<?php echo date("d-m-Y",strtotime("+1 month"));?>" 
												readonly="readonly" 										 
												>
											<label class="input-group-addon glyphicon glyphicon-calendar" for="datepicker-fecha_inicio"></label>
										</div>
									</div>
									<br>
								</div>
								<div class="btn-group col-lg-6 col-lg-offset-3 m-2">
									<button class="btn btn-block btn-default frac_calc_cuotas_btn" title="Recalcular"><i class="icon fa fa-fw fa-refresh"></i> Calcular</button>
								</div>
							<!-- </form> -->
							<div class="col-xs-12">
								<table class="table table-bordered">
									<thead>
										<tr>
											<th>#</th>
											<th class="cuota_fecha_col  fac_fecha_hidden">Fecha</th>
											<th>Monto</th>
										</tr>
									</thead>
									<tbody id="cuotas_holder">
										<tr>
											<td>
											1
											</td>
											<td class="cuota_fecha_col  fac_fecha_hidden">
												<div class="input-group ">
													<input 
														type="hidden" 
														class=" "
														data-col="fecha_inicio"
														name="fecha_inicio" 
														value="<?php echo date("Y-m-d");?>" 
														id="input_fecha_operacion" 
														data-real-date="datepicker-fecha_inicio">
													<input 
														type="text" 
														class="form-control makeme_datepicker" 
														id="datepicker-fecha_inicio" 
														value="<?php echo date("d-m-Y");?>" 
														readonly="readonly" 										 
														>
													<label class="input-group-addon glyphicon glyphicon-calendar" for="datepicker-fecha_inicio"></label>
												</div>
											</td>
											<td>
												<input class="form-control cuota" type="text" id="cuota_0" value="300">
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-success save_btn" >Aprobar</button>
					<button class="btn btn-default close_btn">Cerrar</button>
				</div>
			</div>
		</div>
	</div>
	<?php
}
?>