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
	<input type="hidden" name="id_detalle_programacion_seleccionada" id="id_detalle_programacion_seleccionada" value="<?php echo $_GET["id"]; ?>">
	<div class="page-header wide">
		<div class="row">
			<div class="col-xs-12 text-center">
				<h1 class="page-title titulosec_contrato"><i class="icon icon-inline fa fa-fw fa-money"></i>
					Detalle de la programación de pago
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
						<input id="sec_det_num_programacion" type="text" class="form-control" name="num_programacion" 
						readonly="readonly" placeholder="0000000431">
					</div>

					<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
						<label>Concepto</label>
						<input id="sec_det_concepto_programacion" type="text" class="form-control" name="num_programacion" 
							readonly="readonly" value="PROVEEDORES Y ANTICIPOS MN" >
					</div>

					<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
						<label>Tipo de pago</label>
						<input id="sec_det_tipo_pago_programacion" type="text" class="form-control" name="num_programacion" 
						readonly="readonly">
					</div>

					<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
						<label>Banco (Número de cuenta)</label>
						<input id="sec_det_banco_programacion" type="text" class="form-control" name="num_programacion" 
						readonly="readonly"value="10411001 (BBVA MN 01-00050192)">
					</div>

					<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
						<label for="start">Fecha de la programación:</label>
						<div class="input-group">
							<input type="text" name="fecha_inicio" id="sec_det_fecha_programacion" 
							class="form-control fecha_datepicker" readonly="readonly">
							<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="fecha_inicio"></label>
						</div>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
						<label>Tipo de cambio</label>
						<input id="sec_det_tipo_cambio_programacion" type="text" class="form-control" name="tipo_de_cambio" 
						readonly="readonly">
					</div>

					<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
						<label>Moneda</label>
						<input id="sec_det_moneda_programacion" type="text" class="form-control" name="moneda" 
						readonly="readonly">
					</div>

					<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
						<label>Situación</label>
						<input id="sec_det_situacion_programacion" type="text" class="form-control" name="moneda" 
						readonly="readonly" style="margin-bottom: 15px;">
					</div>
				</form>
			</fieldset>
		</div>
	</div>

	<div class="row mt-2 mb-2">
		<div class="table-responsive">
			<table id="sec_det_tabla_acreedores" class="table table-bordered" style="font-size: 11px">
				<thead>
					<tr>
						<th colspan="9" style="background-color: #E5E5E5;">
							<div class="col-xs-8 col-sm-8 col-md-8 col-lg-8" style="font-size: 14px" style="text-align: left;">
								Acreedores que integran la programación de pago:
							</div>

							<div id="sec_det_div_boton_ver_comprobante" class="col-xs-4 col-sm-4 col-md-4 col-lg-4" style="text-align: right;">
									
							</div>
						</th>
					</tr>
					<tr>
						<th>N.°</th>
						<th>Código</th>
						<th>Acreedor</th>
						<th>N.° doc</th>
						<th>Día de pago</th>
						<th>F. Vencimiento</th>
						<th>Moneda</th>
						<th>Programado</th>
						<th>Centro de costos</th>
					</tr>
				</thead>
				<tbody>
					<!--<tr>
						<td>1</td>
						<td>10093712582</td>
						<td>PALOMINO LOPEZ, PEDRO FRANCISCO</td>
						<td>1683-032022</td>
						<td>4</td>
						<td>01/03/2022</td>
						<td>MN</td>
						<td>2,500.00</td>
						<td>3123 - RED AT UPC PRIMAVERA</td>
					</tr>

					<tr>
						<td>2</td>
						<td>10101475293</td>
						<td>SUCESSION INDIVISA Y LLATOPA DAVILA</td>
						<td>1683-032022</td>
						<td>4</td>
						<td>01/03/2022</td>
						<td>MN</td>
						<td>2,400.00</td>
						<td>4724 - RED AT HUANUCO 01</td>
					</tr>

					<tr>
						<td>3</td>
						<td>10297305684</td>
						<td>ALVIS RODRIGUEZ RONY BACILIDES</td>
						<td>1683-032022</td>
						<td>4</td>
						<td>01/03/2022</td>
						<td>MN</td>
						<td>2,106.00</td>
						<td>3382 - RED AT PLAZA DE ARMAS AREQUIPA</td>
					</tr>

					<tr>
						<th colspan="9" style="text-align: right; background-color: #E5E5E5;">
						</th>
					</tr>

					<tr style="font-size: 13px;">
						<th colspan="8" style="text-align: right;">
							Total acreedores:
						</th>
						<th style="text-align: right;">
							3
						</th>
					</tr>

					<tr style="font-size: 13px;">
						<th colspan="8" style="text-align: right;">
							Total monto:
						</th>
						<th style="text-align: right;">
							7,006.00
						</th>
					</tr>-->
				</tbody>
			</table>
		</div>
	</div>

	<div class="row mt-2 mb-2">
		<div class="table-responsive">
			<table id="sec_det_tabla_auditoria" class="table table-bordered" style="font-size: 13px">
				<thead>
					<tr>
						<th colspan="4" style="background-color: #E5E5E5; text-align: center;">
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="font-size: 14px">
								Auditoría
							</div>
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
			class="btn btn-success" 
			title="Cancelar" 
			onclick="$(location).attr('href','?sec_id=contrato&sub_sec_id=tesoreria');"
		>
			<i class="fa fa-arrow-left"></i>
			Regresar
		</button>
	</div>

	<div id="sec_det_div_modal_archivo" class="modal fade" style="min-height: 500px;" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
					</button>
					<h4 id="sec_det_titulo_comprobante_pago" class="modal-title">COMPROBANTE DE PAGO</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<form>
							<div class="col-md-12">
								<div class="panel" id="sec_det_div_contenido_archivo" style="display: none;">
									<div class="panel-heading">
										<div class="panel-title" id="sec_det_div_heading_value_archivo">TEMPORAL</div>
									</div>
									<div class="panel-body" style="padding: 10px 0px 10px 0px;">
										<div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center; margin-bottom: 5px; display:none;" id="sec_det_div_Ver_Pdf">
											<button type="button" class="btn btn-block btn-primary" data-toggle="modal" style="border-color: #aaf152;">
												<i class="fa fa-arrows-alt"></i>  Ver documento en toda la Pantalla
											</button>        
										</div>
										<div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center;margin-bottom: 5px; display:none;" id="sec_det_divVisorPdfPrincipal">
										</div>
										<div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center;margin-bottom: 5px; display:none;" 
										id="sec_det_divVerImagenFullPantallaModal">
											<input type="hidden" name="sec_det_midocu" id="sec_det_midocu"/>
											<button type="button" class="btn btn-block btn-block btn-primary" 
											id="sec_det_btnVista" onclick="sec_det_contrato_tesoreria_verFullImagenComprobante();">
												<i class="fa fa-arrows-alt"></i>  Ver imagen en toda la Pantalla
											</button>
										</div>
										<div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center;margin-bottom: 5px; display:none;" id="sec_det_divVisorImagen">
											<img src="" class="img-responsive" style="border: 1px solid;">
										</div>
									</div>
								</div>
							</div>
						</form>
					</div> 
				</div>
			</div>
		</div>
	</div>
</div>