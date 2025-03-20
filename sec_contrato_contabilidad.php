<?php  
$select_contabilidad_tipo_solicitud = [
	"1" => "Concar y Sispag",
	"3" => "Servicios Públicos",
	"2" => "Centro de costos"
];

$select_local_tienda = $mysqli->query("SELECT contrato_id, nombre_tienda
							FROM cont_contrato
							WHERE tipo_contrato_id = 1
							AND status = 1
							AND etapa_id = 5
							ORDER BY nombre_tienda ASC");


$select_tipo_moneda = $mysqli->query("SELECT id, nombre
							FROM tbl_moneda
							WHERE estado = 1 AND id in(1, 2)
							ORDER BY id ASC");
?>

<style>
	fieldset.dhhBorder{
		border: 1px solid #ddd;
		padding: 0 15px 5px 15px;
		margin: 0 0 15px 0;
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


<div class="content container-fluid contratos_form_etapas">
	<div class="page-header wide">
		<div class="row">
			<div class="col-xs-12 text-center">
				<h1 class="page-title titulosec_contrato"><i class="icon icon-inline fa fa-fw fa-money"></i> 	Contratos -
				<select style="width:220px;display:inline;font-size: 14px;"
						class="form-control input_text"
						data-col="estado"
						data-table="tbl_contratos"
						name="estado"
						id="select_contabilidad_tipo_solicitud"
						title="Seleccione el estado">
						<?php foreach($select_contabilidad_tipo_solicitud as $key => $value) :?>
							<option value="<?= $key ?>" ><?= $value ?></option>
						<?php endforeach; ?>
					</select>
				</h1>
			</div>
		</div>
	</div>

	<div class="row mt-4 mb-2" id="cont_contabilidad_div_parametros_reporte">
		<div class="page-header wide">
			<fieldset class="dhhBorder">
				<legend class="dhhBorder">Búsqueda</legend>

				<div class="col-xs-12 parametrosDeBusquedaRptContabilidad mb-2">
					
					<div class="col-lg-3 col-sm-6 col-md-6 col-xs-12">
						<label for="start">Periodo:</label>
						<div class="form-group form-group-reportes_venta_general_tienda_inicio_fecha">
							<div class="input-group col-xs-12">
								<input
										type="month"
										id="cont_contabilidad_fecha_mes"
										class="form-control input_text cont_contabilidad_fecha_mes"
										data-col="fecha_inicio"
										name="fecha_inicio"
										placeholder="yyyy-mm (Ejemplo: 2022-02)"
										autocomplete="off">
								   
							</div>
						</div>

					</div>

					<div class="col-lg-3 col-xs-12">
						<label>Tipo de Reporte</label>
						<select
							class="form-control input_text select2"
							data-live-search="true"
							name="tipo_reporte_contable" 
							id="tipo_reporte_contable" 
							title="Seleccione el tipo de reporte">
							<option value="0">- Seleccione -</option>
							<option value="1">CONCAR</option>
							<option value="2">SISPAG</option>
						</select>
					</div>

					<div class="col-lg-3 col-xs-12">
						<label>Tipo de Moneda</label>
						<select
							class="form-control input_text select2"
							data-live-search="true" 
							name="tipo_moneda_contable" 
							id="tipo_moneda_contable" 
							title="Seleccione el tipo de moneda">
							<option value="0">- Seleccione -</option>
							<option value="1">Sol</option>
							<option value="2">Dolar</option>
						</select>
					</div>

					<div class="col-lg-3 col-xs-12">
						
						<button type="button" name="reporte_filtro" value="1" class="btn_filtrar_reporte_venta_general_tienda btn btn-primary" data-button="request" data-toggle="tooltip" data-placement="top" 
						title="Consultar" onclick="buscarReporteContable();">
							<i class="glyphicon glyphicon-search"></i>
							Consultar
						</button>
					</div>

				
				</div>

				<div class="col-xs-12 parametrosDeBusquedaRptContabilidad">
					
					<div class="col-lg-3 col-sm-6 col-md-6 col-xs-12">
						<label>Fecha comprobante</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<input
										type="hidden"
										id="contrato_vigencia_inicio_fecha"
										class="input_text filtro"
										data-col="fecha_inicio"
										name="fecha_inicio"
										value="<?php echo date("Y-m-d", strtotime("+1 days")); ?>"
										data-real-date="cont_contabilidad_fecha_comprobante">
									<input
										type="text"
										class="form-control sec_contrato_contabilidad_datepicker"
										id="cont_contabilidad_fecha_comprobante"
										value="<?php echo date("d-m-Y");?>"
										readonly="readonly"
										style="height: 34px;"
										>
									<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="cont_contabilidad_fecha_comprobante"></label>

								<!--<input
									type="text"
									class="form-control sec_contrato_nuevo_datepicker"
									id="cont_contabilidad_fecha_comprobante"
									value="<?php echo date("d-m-Y");?>"
									readonly="readonly"
									>
								<label class="input-group-addon glyphicon glyphicon-calendar" for="reporte_fecha_inicio"></label>-->
							</div>
						</div>
					</div>

					<div class="col-lg-2 col-sm-6 col-md-6 col-xs-12">
						<label>Número comprobante</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<input
									type="text"
									class="form-control"
									id="cont_contabilidad_numero_comprobante"
									autocomplete="off"
									>
							</div>
						</div>
					</div>

					<div class="col-lg-2 col-sm-6 col-md-6 col-xs-12">
						<label>Tipo cambio</label>
						<div class="form-group form-group-reportes_venta_general_tienda_inicio_fecha">
							<div class="input-group col-xs-12">
								<input
									type="text"
									class="form-control sec_contrato_nuevo_datepicker"
									id="cont_contabilidad_tipo_cambio"
									placeholder="Ejemplo: 3.85"
									autocomplete="off"
									>
							</div>
						</div>
					</div>

					<div class="col-lg-2 col-xs-12">
						<label>Tipo conversion</label>
						<div class="form-group form-group-reportes_venta_general_tienda_inicio_fecha">
							<div class="input-group col-xs-12">
								<input
									type="text"
									class="form-control sec_contrato_nuevo_datepicker"
									id="cont_contabilidad_tipo_conversion"
									value="C"
									style = "text-transform:uppercase;">
							</div>
						</div>
					</div>

				</div>

				<div class="col-xs-12" id="cont_contable_alerta_filtrar">
							
				</div>

				
			</fieldset>
			
		</div>
	</div>

	<div class="row mt-4 mb-2" id="cont_contabilidad_div_parametros_reporte_centro_costos" style="display: none;">
		<div class="page-header wide">

			<fieldset class="dhhBorder">
				<legend class="dhhBorder">Búsqueda</legend>
				
				<div class="row">
					<div class="form-group col-lg-4 col-md-4 col-sm-6 col-xs-12">
						<label>
							Local:
						</label>
						<select id="cont_contabilidad_centro_costos_param_tienda" name="cont_contabilidad_centro_costos_param_tienda" class="form-control select2">
							<option value="0">Todos</option>
							<?php foreach ($select_local_tienda as $item): ?>
								<option value="<?php echo $item["contrato_id"] ?>"><?php echo $item["nombre_tienda"]; ?></option>
							<?php endforeach ?>
						</select>
					</div>

					<div class="form-group col-lg-2 col-md-3">
						<label>
							Tipo moneda:
						</label>
						<select id="cont_contabilidad_centro_costos_param_tipo_moneda" name="cont_contabilidad_centro_costos_param_tipo_moneda" class="form-control select2">
							<?php foreach ($select_tipo_moneda as $item): ?>
								<option value="<?php echo $item["id"] ?>"><?php echo $item["nombre"]; ?></option>
							<?php endforeach ?>
						</select>
					</div>

					<div class="col-lg-2 col-sm-6 col-md-6 col-xs-12">
						<label>
							Fecha inicio contrato:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<input
										type="hidden"
										id="contrato_vigencia_inicio_fecha"
										class="input_text filtro"
										data-col="fecha_inicio"
										name="fecha_inicio"
										value="<?php echo date("Y-m-d", strtotime("+1 days")); ?>"
										data-real-date="cont_contabilidad_centro_costos_param_fecha_inicio">
									<input
										type="text"
										class="form-control sec_contrato_contabilidad_datepicker"
										id="cont_contabilidad_centro_costos_param_fecha_inicio"
										value="<?php echo date("d-m-Y");?>"
										readonly="readonly"
										style="height: 34px;"
										>
									<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="cont_contabilidad_centro_costos_param_fecha_inicio"></label>
							</div>
						</div>
					</div>

					<div class="col-lg-2 col-sm-6 col-md-6 col-xs-12">
						<label>
							Fecha fin contrato:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<input
										type="hidden"
										id="contrato_vigencia_inicio_fecha"
										class="input_text filtro"
										data-col="fecha_inicio"
										name="fecha_inicio"
										value="<?php echo date("Y-m-d", strtotime("+1 days")); ?>"
										data-real-date="cont_contabilidad_centro_costos_param_fecha_fin">
									<input
										type="text"
										class="form-control sec_contrato_contabilidad_datepicker"
										id="cont_contabilidad_centro_costos_param_fecha_fin"
										value="<?php echo date("d-m-Y");?>"
										readonly="readonly"
										style="height: 34px;"
										>
									<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="cont_contabilidad_centro_costos_param_fecha_fin"></label>
							</div>
						</div>
					</div>

					<div class="col-lg-2 col-xs-12">
						<button type="button" name="buscar_contratos_contabilidad_CentroCosto" value="1" class="btn_filtrar_reporte_venta_general_tienda btn btn-primary" data-button="request" data-toggle="tooltip" data-placement="top" 
						title="Consultar" onclick="buscar_contratos_contabilidad_CentroCosto();">
							<i class="glyphicon glyphicon-search"></i>
							Consultar
						</button>
					</div>

				</div>

			</fieldset>
			
		</div>
	</div>

	<div class="row mt-4 mb-2" id="cont_contabilidad_div_parametros_reporte_servicio_publico" style="display: none;">
		<div class="page-header wide">

			<fieldset class="dhhBorder">
				<legend class="dhhBorder">Búsqueda</legend>

				<div class="col-xs-12 parametrosDeBusquedaRptContabilidadServicioPublico mb-2">
					
					<div class="col-lg-2 col-sm-2 col-md-2 col-xs-12">
						<label for="start">Año:</label>
						<div class="form-group form-group">
							
							<select 
								class="form-control input_text select2" 
								data-live-search="true"  
								id="cont_contabilidad_servicio_publico_anio" 
								name="cont_contabilidad_servicio_publico_anio"
								>
								
								<?php
								$command_anio_consumo = " SELECT
															YEAR(periodo_consumo) AS anio_consumo
														FROM cont_local_servicio_publico
														GROUP BY YEAR(periodo_consumo) 
														ORDER BY YEAR(periodo_consumo) DESC ";
								
								$query_anio_consumo = $mysqli->query($command_anio_consumo);
								
								while($lsp=$query_anio_consumo->fetch_assoc())
								{
									?>
									<option value="<?php echo $lsp["anio_consumo"];?>"><?php echo $lsp["anio_consumo"];?></option>
									<?php
								}
								?>

							</select>
						</div>
					</div>

					<!-- <div class="col-lg-2 col-sm-2 col-md-2 col-xs-12">
						<label for="start">Tipo servicio:</label>
						<div class="form-group form-group">
							<select style="width:150px;display:inline;font-size: 14px;" 
								class="form-control" 
								id="cont_contabilidad_servicio_publico_tipo_servicio" 
								name="cont_contabilidad_servicio_publico_tipo_servicio"
								>

								<?php
								$tipos_servicio_publico_command = "SELECT id, nombre FROM cont_tipo_servicio_publico WHERE status = '1' AND id in(1,2)";
								
								$tipos_query = $mysqli->query($tipos_servicio_publico_command);
								?>
									<option value="0"> --- TODOS --- </option>
								<?php

								while($tsp=$tipos_query->fetch_assoc())
								{
									?>
									<option value="<?php echo $tsp["id"];?>"><?php echo $tsp["nombre"];?></option>
									<?php
								}
								?>
							</select>
						</div>
					</div>-->

					<div class="col-lg-2 col-xs-12">

						<button type="button" name="reporte_filtro_servicio_publico" value="1" class="btn_filtrar_reporte_venta_general_tienda btn btn-primary" data-button="request" data-toggle="tooltip" data-placement="top" 
						title="Consultar" onclick="buscarReporteServiciosPublicos();">
							<i class="glyphicon glyphicon-search"></i>
							Consultar
						</button>
					</div>

					<div class="col-xs-12" id="cont_contable_alerta_boton_export_servicios_publicos">
					</div>

				</div>

			</fieldset>
			
		</div>
	</div>

	<div class="page-header wide" id="div_contabilidad_boton_export" style="display: none;">
		<div class="row mt-3 mb-2">
			<div class="row form-horizontal">
				<div class="col-md-2" id="cont_contabilidad_boton_excel_concar">
					
				</div>
			</div>
		</div>
	</div>

	<div class="page-header wide" id="div_contabilidad_boton_export_servicios_publicos" style="display: none;">
		<div class="row mt-3 mb-2">
			<div class="row form-horizontal">
				<div class="col-md-2" id="cont_contabilidad_boton_excel_concar_servicios_publicos">
					
				</div>
			</div>
		</div>
	</div>

	

	<div class="row mt-3" id="cont_contrato_contabilidad_div_tabla">
		<table id="cont_locales_contabilidad_datatable" class="table table-bordered dt-responsive" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th scope="col">Sub Diario</th>
					<th scope="col">Nº Comprobante</th>
					<th scope="col">F. Comprobante</th>
					<th scope="col">Glosa</th>
					<th scope="col">Tipo cambio</th>
					<th scope="col">Cuenta contable</th>
					<th scope="col">Codigo anexo</th>
					<th scope="col">Cod. Centro costo</th>
					<th scope="col">Importe</th>
				</tr>
			</thead>
			<tbody>
				
			</tbody>
			<tfoot>
				<tr>
					<th scope="col">Sub Diario</th>
					<th scope="col">Nº. Comprobante</th>
					<th scope="col">F. Comprobante</th>
					<th scope="col">Glosa</th>
					<th scope="col">Tipo cambio</th>
					<th scope="col">Cuenta contable</th>
					<th scope="col">Codigo anexo</th>
					<th scope="col">Cod. Centro costo</th>
					<th scope="col">Importe</th>
				</tr>
			</tfoot>
		</table>
	</div>

	<div class="row mt-3" id="cont_contrato_contabilidad_div_tabla_centro_costos" style="display: none;">
		<table id="cont_locales_contabilidad_centro_costos_datatable" class="table table-bordered dt-responsive" cellspacing="0" width="100%">
		<thead>
			<tr>
				<th scope="col">ID</th>
				<th scope="col">Tienda</th>
				<th scope="col">Centro costo</th>
				<th scope="col">Ubicacion inmueble</th>
				<th scope="col">Monto renta</th>
				<th scope="col">Tipo moneda</th>
				<th scope="col">F. inicio contrato</th>
				<th scope="col">F. fin contrato</th>
				<th scope="col">Ingresar centro costo</th>
			</tr>
		</thead>
		<tbody>
			
		</tbody>
		<tfoot>
			<tr>
				<th scope="col">ID</th>
				<th scope="col">Tienda</th>
				<th scope="col">Centro costo</th>
				<th scope="col">Ubicacion inmueble</th>
				<th scope="col">Monto renta</th>
				<th scope="col">Tipo moneda</th>
				<th scope="col">F. inicio contrato</th>
				<th scope="col">F. fin contrato</th>
				<th scope="col">Ingresar centro costo</th>
			</tr>
		</tfoot>
	</table>
	</div>

	<div class="row mt-3" id="cont_contrato_contabilidad_div_tabla_servicio_publico" style="display: none;">
		<table id="cont_locales_contabilidad_servicio_publico_datatable" class="table table-bordered dt-responsive" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th scope="col">ID</th>
					<th scope="col">Centro costo</th>
					<th scope="col">Punto</th>
					<th scope="col">Razon social</th>
					<th scope="col">Jefe Comercial</th>
					<th scope="col">Supervisor</th>
					<th scope="col">Agua Nº Suministro</th>
					<th scope="col">Luz Nº Suministro</th>
				</tr>
			</thead>
			<tbody>
				
			</tbody>
			<tfoot>
				<tr>
					<th scope="col">ID</th>
					<th scope="col">Centro costo</th>
					<th scope="col">Punto</th>
					<th scope="col">Razon social</th>
					<th scope="col">Jefe Comercial</th>
					<th scope="col">Supervisor</th>
					<th scope="col">Agua Nº Suministro</th>
					<th scope="col">Luz Nº Suministro</th>
				</tr>
			</tfoot>
		</table>
	</div>
	

	<!-- INICIO MODAL INGRESAR CENTRO DE COSTOS -->
	<div class="modal fade" id="configurarCentroCostos" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
					<h2 class="modal-title text-center" id="exampleModalLabel">
						<strong>Registro de Centro de costos</strong>
					</h2>
				</div>
				<div class="modal-body">

					<table id="tabla_datos_alerta" class="table table-striped table-bordered">
						<input type="hidden" name="contrato_id" id="contrato_id">
						<thead>
							<tr>
								<th class="text-center col-md-0" scope="col">ID</th>
								<th class="text-center col-md-4" scope="col">Nombre tienda</th>
								<th class="text-center col-md-4" scope="col">Ubicacion del inmueble</th>
								<th class="text-center col-md-2" scope="col">Fecha inicio</th>
								<th class="text-center col-md-2" scope="col">Fecha fin</th>
							</tr>
						</thead>
						<tbody id="contenido_modal_alerta">
							
						</tbody>
					</table>
					<div>
						<div class="col-md-2" style="text-align: right; padding: 0;">
							<label>Centro de costos</label>	
						</div>

						<div class="col-md-2" style="padding-bottom: 5px;">
							<input type="text" class="form-control input-sm" name="codigoCentroCostos" maxlength="10" id="codigoCentroCostos" min="0" onkeypress="soloNumeros(event)">
						</div>
					</div>
					<div class="col-md-12" id="divMensajeAlerta">
						
					</div>

				</div>
				<br>
				<div class="modal-footer">
					<button type="button" class="btn btn-danger" data-dismiss="modal">
						<i class="glyphicon glyphicon-remove-sign"></i>
						Cancelar
					</button>
					<button type="button" class="btn btn-success" onclick="registrar_centro_costos();">
					<i class="glyphicon glyphicon-saved" ></i>
					Registrar
					</button>
				</div>
			</div>
		</div>
	</div>
	<!-- FIN MODAL ALERTA CONTRATO -->

</div>