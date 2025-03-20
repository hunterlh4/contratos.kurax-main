<style>
	fieldset.dhhBorder{
		border: 1px solid #ddd;
		padding: 0 15px 5px 15px;
		margin: 0 0 10px 0;
		box-shadow: 0px 0px 0px 0px #000;
		border-radius: 5px;
	}

	legend.dhhBorder{
		font-size: 14px;
		text-align: left;
		width: auto;
		padding: 0 10px;
		border-bottom: none;
		margin-bottom: 10px;
		text-transform: capitalize;
	}
</style>

<div class="content container-fluid">
	<input type="hidden" name="sec_proc_id_programacion_seleccionada" id="sec_proc_id_programacion_seleccionada" value="<?php echo $_GET["id"]; ?>">
	<div class="page-header wide">
		<div class="row">
			<div class="col-xs-12 text-center">
				<h1 class="page-title titulosec_contrato"><i class="icon icon-inline fa fa-fw fa-money"></i>
					Procesar programación de pago
				</h1>
			</div>
		</div>
	</div>

	<div class="row mt-3 mb-2">
		<div class="page-header wide">
			<fieldset class="dhhBorder">
				<legend class="dhhBorder">Parámetros</legend>
				<form>
					<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
						<label>N.° de programación</label>
						<input id="sec_proc_num_programacion" type="text" class="form-control" name="num_programacion"
						readonly="readonly">
					</div>

					<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
						<label>Concepto</label>
						<input id="sec_proc_concepto_programacion" type="text" class="form-control" name="num_programacion" 
						readonly="readonly">
					</div>

					<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
						<label>Tipo de pago</label>
						<input id="sec_proc_tipo_pago_programacion" type="text" class="form-control" name="num_programacion" 
						readonly="readonly">
					</div>

					<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
						<label>Banco (Número de cuenta)</label>
						<input id="sec_proc_banco_programacion" type="text" class="form-control" name="num_programacion" 
						readonly="readonly">
					</div>

					<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
						<label for="start">Fecha de la programación:</label>
						<div class="input-group">
							<input id="sec_proc_fecha_programacion" type="text" name="fecha_inicio" id="fecha_inicio"
							class="form-control fecha_datepicker" 
							readonly="readonly">
							<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="fecha_inicio"></label>
						</div>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
						<label>Tipo de cambio</label>
						<input id="sec_proc_tipo_cambio_programacion" type="text" class="form-control" name="tipo_de_cambio"
						readonly="readonly">
					</div>

					<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
						<label>Moneda</label>
						<input id="sec_proc_moneda_programacion" type="text" class="form-control" name="moneda"
						readonly="readonly">
					</div>

					<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
						<label>Situación</label>
						<input id="sec_proc_situacion_programacion" type="text" class="form-control" name="moneda"
						readonly="readonly" style="margin-bottom: 15px;">
					</div>
				</form>
			</fieldset>
		</div>
	</div>

	<div class="row mt-3 mb-2">
		<div class="page-header wide">
			<fieldset class="dhhBorder">
				<legend class="dhhBorder">Datos de pago</legend>
				<form>

					<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
						<label for="start">Fecha de transacción:</label>
						<div class="input-group">
							<input type="text" name="sec_proc_fecha_transaccion" id="sec_proc_fecha_transaccion" class="form-control fecha_datepicker"
									value="<?php echo date("Y-m-d", strtotime("-1 days"));?>"
									readonly="readonly">
							<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="sec_proc_fecha_transaccion"></label>
						</div>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
						<label>Área</label>
						<select disabled class="form-control input_text select2" data-live-search="true" name="banco_id" 
						id="banco_id" title="Seleccione el banco">
							<option value="1">244 - EGR ALQUILER PREDIOS 201</option>
						</select>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
						<label>Medio pago tributario</label>
						<select disabled class="form-control input_text select2" data-live-search="true" name="banco_id" 
						id="banco_id" title="Seleccione el banco">
							<option value="1">TRANSFERENCIAS DE FONDOS</option>
						</select>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
						<label>Tipo de cambio</label>
						<input 
							type="text" 
							class="form-control" 
							name="tipo_de_cambio" 
							readonly="readonly"
							value="3.7350"
							style="margin-bottom: 15px;"
						>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
						<label>Subdiario</label>
						<select id="sec_proc_select_subdiario" class="form-control input_text select2" data-live-search="true"
						name="sec_proc_select_subdiario" title="Seleccione el subdiario">
						</select>
					</div>

				</form>
			</fieldset>
		</div>
	</div>

	<div class="row mt-2 mb-2">
		<div class="table-responsive">
			<table id="sec_proc_tabla_auditoria" class="table table-bordered" style="font-size: 13px">
				<thead>
					<tr>
						<th colspan="2" style="background-color: #E5E5E5; text-align: center;">
							Auditoría
						</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
	</div>

	<div class="row mt-2 mb-2" style="text-align: right;">
		<button 
			type="button"
			class="btn btn-danger" 
			title="Cancelar" 
			onclick="$(location).attr('href','?sec_id=contrato&sub_sec_id=tesoreria');">
			<i class="fa fa-times"></i>
			Cancelar
		</button>

		<button 
			type="button"
			class="btn btn-success" 
			title="Cancelar" 
			onclick="sec_contrato_procesar_programacion_modal_confirmar_proceso();">
			<i class="fa fa-cogs"></i>
			Procesar programación
		</button>
	</div>
</div>


<!-- INICIO MODAL CONFIRMAR PROCESAR PROGRAMACION -->
<div id="sec_proc_modal_confirmar_proceso" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Procesar programación</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form id="form_adenda" autocomplete="off" >
						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="form-group">
								<label>¿Esta seguro de procesar la programación?</label>
							</div>
						</div>
					</form>
				</div> 
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" data-dismiss="modal">
					<i class="icon fa fa-close"></i>
					<span>No, cancelar operación</span>
				</button>

				<button type="button" class="btn btn-success" id="sec_proc_btn_procesar" 
					onclick="sec_contrato_procesar_programacion_procesar();">
					<i class="icon fa fa-cogs"></i>
					<span>Si, procesar la programación</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL CONFIRMAR PROCESAR PROGRAMACION -->