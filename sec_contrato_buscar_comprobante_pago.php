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
	<div class="page-header wide">
		<div class="row">
			<div class="col-xs-12 text-center">
				<h1 class="page-title titulosec_contrato"><i class="icon icon-inline fa fa-fw fa-money"></i>
					Búsqueda de comprobantes de pago				
				</h1>
			</div>
		</div>
	</div>

	<div class="row mt-2 mb-2">
		<div class="page-header wide">
			<fieldset class="dhhBorder">
				<legend class="dhhBorder">Parámetros de búsqueda</legend>
				<form>
					<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
						<label>N.° de documento de identidad del acreedor:</label>
						<input 
							type="text" 
							class="form-control" 
							name="tipo_de_cambio" 
							value="10736722506"
						>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
						<label>Nombre del acreedor:</label>
						<input 
							type="text" 
							class="form-control" 
							name="tipo_de_cambio" 
							value="Juan Carlos Garcia Perez"
						>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
						<label>Nombre del local</label>
						<select
							class="form-control input_text select2"
							data-live-search="true" 
							name="banco_id" 
							id="banco_id" 
							title="Seleccione el local">
							<option value="0">- Seleccione -</option>
							<option value="1">4041 - RED AT MEXICO</option>
							<option value="2">4042 - RED AT MARIA ELENA MOYANO</option>
							<option value="3">4098 - RED AT TORRES PAZ</option>
						</select>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
						<label for="start">Fecha inicio de vencimiento:</label>
						<div class="input-group">
							<input
									type="text"
									name="fecha_inicio"
									id="fecha_inicio"
									class="form-control fecha_datepicker"
									value="<?php echo date("Y-m-d", strtotime("-1 days"));?>"
									readonly="readonly"
									>
							<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="fecha_inicio"></label>
						</div>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
						<label for="start">Fecha fin de vencimiento:</label>
						<div class="input-group">
							<input
									type="text"
									name="fecha_inicio"
									id="fecha_inicio"
									class="form-control fecha_datepicker"
									value="<?php echo date("Y-m-d", strtotime("-1 days"));?>"
									readonly="readonly"
									>
							<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="fecha_inicio"></label>
						</div>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
						<label for="start">Fecha inicio de pago:</label>
						<div class="input-group">
							<input
									type="text"
									name="fecha_inicio"
									id="fecha_inicio"
									class="form-control fecha_datepicker"
									value="<?php echo date("Y-m-d", strtotime("-1 days"));?>"
									readonly="readonly"
									>
							<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="fecha_inicio"></label>
						</div>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
						<label for="start">Fecha fin de pago:</label>
						<div class="input-group">
							<input
									type="text"
									name="fecha_inicio"
									id="fecha_inicio"
									class="form-control fecha_datepicker"
									value="<?php echo date("Y-m-d", strtotime("-1 days"));?>"
									readonly="readonly"
									>
							<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="fecha_inicio"></label>
						</div>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
						<button 
							type="button" 
							name="btn_buscar_programacion" 
							value="1" 
							class="btn btn-success btn-block btn-sm" 
							data-button="request" 
							data-toggle="tooltip" 
							data-placement="top" 
							title="Buscar programación" 
							onclick="sec_contrato_tesoreria_buscar_programacion();"
							style="position: relative; bottom: -19px; margin-bottom: 30px;">
							<i class="glyphicon glyphicon-search"></i>
							Buscar
						</button>
					</div>
				</form>
			</fieldset>
		</div>
	</div>

	<div class="row mt-3 mb-2">
		<div class="table-responsive">
			<table class="table table-bordered" style="font-size: 11px">
				<thead>
					<tr>
						<th>N.°</th>
						<th>Código</th>
						<th>Acreedor</th>
						<th>N.° doc.</th>
						<th>Día pago</th>
						<th>F. vcto.</th>
						<th>F. pago</th>
						<th>Moneda</th>
						<th>Programado</th>
						<th>Centro de costos</th>
						<th>Comprobante</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>1</td>
						<td>10093712582</td>
						<td>PALOMINO LOPEZ, PEDRO FRANCISCO</td>
						<td>1683-032022</td>
						<td>4</td>
						<td>01/03/2022</td>
						<td>05/03/2022</td>
						<td>MN</td>
						<td>2,500.00</td>
						<td>3123 - RED AT UPC PRIMAVERA</td>
						<td>
							<button 
								type="button"
								class="btn btn-danger btn-xs" 
								title="Ver comprobante de pago" 
								onclick="$(location).attr('href','?sec_id=contrato&sub_sec_id=detalle_programacion');"
								style="width: 77px;"
							>
								<i class="fa fa-file-pdf-o"></i>
								Ver
							</button>
						</td>
					</tr>

					<tr>
						<td>2</td>
						<td>10101475293</td>
						<td>SUCESSION INDIVISA Y LLATOPA DAVILA AURISTELA</td>
						<td>1683-032022</td>
						<td>4</td>
						<td>01/03/2022</td>
						<td>05/03/2022</td>
						<td>MN</td>
						<td>2,400.00</td>
						<td>4724 - RED AT HUANUCO 01</td>
						<td>
							<button 
								type="button"
								class="btn btn-danger btn-xs" 
								title="Ver comprobante de pago" 
								onclick="$(location).attr('href','?sec_id=contrato&sub_sec_id=detalle_programacion');"
								style="width: 77px;" 
							>
								<i class="fa fa-file-pdf-o"></i>
								Ver
							</button>
						</td>
					</tr>

					<tr>
						<td>3</td>
						<td>10297305684</td>
						<td>ALVIS RODRIGUEZ RONY BACILIDES</td>
						<td>1683-032022</td>
						<td>4</td>
						<td>01/03/2022</td>
						<td>05/03/2022</td>
						<td>MN</td>
						<td>2,106.00</td>
						<td>3382 - RED AT PLAZA DE ARMAS AREQUIPA</td>
						<td>
							<button 
								type="button"
								class="btn btn-warning btn-xs" 
								title="No posee comprobante de pago" 
								onclick="$(location).attr('href','?sec_id=contrato&sub_sec_id=detalle_programacion');"
								style="width: 77px;" 
							>
								<i class="fa fa-file-o"></i>
								No posee
							</button>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</div>