<?php
if($sub_sec_id=="procesos"){
	?>
    <link rel="stylesheet" href="css/simplePagination.css">
	<div class="modal" id="recaudacion_generar_liquidacion_modal" >
		<div class="modal-dialog">
			<div class="modal-content modal-rounded">
				<div class="modal-header">
					<button type="button" class="close generar_cerrar_btn"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">Generar Liquidaciones</h4>
				</div>
				<div class="modal-body">
					<label class="h4 strong block">Servicio/Proveedor</label>
					<div class="btn-group" data-toggle="buttons">
						<?php
						foreach ($servicios as $srv_k => $srv_v) {
							?>
							<label class="btn btn-success <?php if($srv_k==1){ ?>active<?php } ?>">
								<input type="radio" class="generar_data" name="gen_servicio" value="<?php echo $srv_v["id"];?>" required <?php if($srv_k==1){ ?> checked="checked"<?php } ?>> <?php echo $srv_v["nombre"];?>
							</label>
							<?php
						}
						?>
					</div>
					<div class="fecha_rango">
						<label class="h4 strong block">Rango</label>
						<div class="panel-body">
							<div class="col-xs-6">
								<p class="text-center"><strong>Inicio</strong></p>
								<div class="form-group form-group-inicio_fecha">
									<label class="col-xs-4 control-label" for="input_text-inicio_fecha">Fecha</label>
									<div class="input-group col-xs-8">
										<input 
							    			type="hidden" 
							    			class="input_text generar_data"
							    			data-col="inicio_fecha"
											name="inicio_fecha" 
											value="<?php echo date("Y-m-d", strtotime("last week last tuesday"));?>" 
							    			data-real-date="input_text-inicio_fecha">
										<input 
											type="text" 
											class="form-control gen_liq_datepicker" 
											id="input_text-inicio_fecha" 
											value="<?php echo date("d-m-Y", strtotime("last week last tuesday"));?>" 
											readonly="readonly" 										 
											>
										<label class="input-group-addon glyphicon glyphicon-calendar" for="input_text-inicio_fecha"></label>
									</div>
								</div>
							</div>
							<div class="col-xs-6">
								<p class="text-center"><strong>Fin</strong></p>
								<div class="form-group form-group-fin_fecha">
									<label class="col-xs-4 control-label" for="input_text-fin_fecha">Fecha</label>
									<div class="input-group col-xs-8">
										<input 
							    			type="hidden" 
							    			class="input_text generar_data"
							    			data-col="fin_fecha"
											name="fin_fecha" 
											value="<?php echo date("Y-m-d",strtotime("last monday"));?>" 
							    			data-real-date="input_text-fin_fecha">
										<input 
											type="text" 
											class="form-control gen_liq_datepicker" 
											id="input_text-fin_fecha" 
											value="<?php echo date("d-m-Y",strtotime("last monday"));?>" 
											readonly="readonly" 										 
											>
										<label class="input-group-addon glyphicon glyphicon-calendar" for="input_text-fin_fecha"></label>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-success generar_btn" >Generar</button>
					<button class="btn btn-default generar_cerrar_btn">Cerrar</button>
				</div>
			</div>
		</div>
	</div>
	<?php




	?>
    <input type="hidden" value="<?= $last_days_count ?>" id="recaudacion_last_days_count">
	<div class="col-xs-12">
		<table class="table table-bordered table-condensed table-liquidaciones" id="datatable_liquidaciones">
			<thead>
				<tr>
					<th class="">#</th>
					<th class="col-xs-2">Proceso ID</th>
					<th class="">Fecha Proceso</th>
					<th class="col-xs-1">Servicio</th>
					<th class="">Fecha Inicio</th>
					<th class="">Fecha Fin</th>
					<th class="col-xs-1">Usuario</th>
					<th class="col-xs-1">Estado</th>
					<th class="col-xs-2 text-right">Opciones</th>
				</tr>
			</thead>
			<tbody id="procesos_tbody">
			</tbody>
		</table>
        <div class="pull-right">
            <div id="pagination_recaudacion_procesos"></div>
        </div>
	</div>
	<?php
}
?>