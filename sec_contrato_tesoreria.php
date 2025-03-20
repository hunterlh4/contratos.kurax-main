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
	<!--TITULO-->
	<div class="page-header wide">
		<div class="row">
			<div class="col-xs-12 text-center">
				<h1 class="page-title titulosec_contrato"><i class="icon icon-inline fa fa-fw fa-money"></i>
					Programación de pagos				
				</h1>
			</div>
		</div>
	</div>
	<!--CREAR PROGRAMACION-->
	<div class="row mt-3 mb-2">
		<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
			<div class="form-group">
				<button 
					type="button" 
					name="btn_validar_tipo_cambio" 
					id="btn_validar_tipo_cambio"
					value="1" 
					class="btn btn-success btn-sm" 
					data-button="request" 
					data-toggle="tooltip" 
					data-placement="top" 
					title="Crear programación" 
					onclick="btn_validar_tipo_cambio()"
				>
					<i class="fa fa-plus"></i>
					Crear programación
				</button>
			</div>
		</div>
	</div>
	<!--PARAMETROS BUSQUEDA-->
	<div class="row mt-2 mb-2">
		<div class="page-header wide">
			<fieldset class="dhhBorder">
				<legend class="dhhBorder">Parámetros de búsqueda</legend>
				<form>
					<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
						<label>Tipo de Programación</label>
						<select
							class="form-control input_text select2"
							data-live-search="true"
							name="tipo_programacion_id" 
							id="tipo_programacion_id" 
							title="Seleccione el concepto">
							<option value="_all_">TODOS</option>
							<?php
							$sel_query = $mysqli->query("
								SELECT id, nombre
								FROM cont_tipo_programacion
								WHERE status = 1
								ORDER BY nombre ASC;");
							while($sel=$sel_query->fetch_assoc()){
							?>
								<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
								<?php
							}
							?>
						</select>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
						<label>Concepto</label>
						<select
							class="form-control input_text select2"
							data-live-search="true"
							name="tipo_concepto_id" 
							id="tipo_concepto_id" 
							title="Seleccione el concepto">
							<option value="_all_">TODOS</option>
							<?php
							$sel_query = $mysqli->query("
							SELECT 
								id, 
								nombre
							FROM 
								cont_tipo_concepto
							WHERE 
								status = 1
							ORDER BY nombre ASC
							");
							while($sel=$sel_query->fetch_assoc()){
							?>
								<option value="<?php echo $sel["id"];?>"><?php echo str_replace("PROVEEDORES Y ANTICIPOS", "", $sel["nombre"]);?></option>
								<?php
							}
							?>
						</select>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3" style="display: none;">
						<label>Tipo de pago</label>
						<select
							class="form-control input_text select2"
							data-live-search="true" 
							name="tipo_pago_id" 
							id="tipo_pago_id" 
							title="Seleccione el tipo de pago">
							<?php
							$sel_query = $mysqli->query("SELECT id, nombre
								FROM cont_tipo_pago_programacion
								WHERE status = 1
								ORDER BY nombre ASC;");
							while($sel=$sel_query->fetch_assoc()){
							?>
								<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
								<?php
							}
							?>							
						</select>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
						<label>Empresas: </label>
						<select
							class="form-control input_text select2"
							name="empresa_id" 
							id="empresa_id" 
							title="Seleccione el tipo de pago">
							<option value="_all_">- TODOS -</option>
							<?php
							$usuario_id = $login?$login['id']:null;

							$query = "SELECT  tul.usuario_id  FROM tbl_usuarios_locales tul WHERE tul.usuario_id=".$usuario_id;
						
							$list_query_permisos_empresas = $mysqli->query($query);
							$empresas_query = 'SELECT rs.id ,rs.nombre  from  tbl_razon_social rs  

							LEFT JOIN tbl_locales_redes tlr 
							ON tlr.id = rs.red_id 
							LEFT JOIN tbl_locales tl 
							ON tl.red_id = tlr.id
							LEFT JOIN tbl_usuarios_locales tul 
							on tul.local_id = tl.id
							WHERE rs.status = 1
							AND subdiario IS NOT NULL'; 
						
							if ($list_query_permisos_empresas->num_rows > 0) {
								$empresas_query.= ' AND  tul.usuario_id ='.$usuario_id.'   GROUP BY  rs.id';
							}else{
								$empresas_query.= '   GROUP BY  rs.id';
						
							} 

							$sel_query = $mysqli->query($empresas_query);
							while($sel=$sel_query->fetch_assoc()){
							?>
								<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
								<?php
							}
							?>	
						</select>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
						<label>Banco (Número de cuenta)</label>
						<select
							class="form-control input_text select2"
							data-live-search="true" 
							name="banco_id" 
							id="banco_id" 
							title="Seleccione el banco">
							<option value="_all_">TODOS</option>
							<?php
							$sel_query = $mysqli->query("SELECT c.id, CONCAT(b.nombre, ' ', c.num_cuenta_corriente) AS nombre
								FROM cont_num_cuenta c
									INNER JOIN tbl_bancos b ON c.banco_id = b.id
								WHERE c.status = 1
								ORDER BY nombre ASC");
							while($sel=$sel_query->fetch_assoc()){
							?>
								<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
								<?php
							}
							?>
						</select>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
						<label>Situación</label>
						<select
							class="form-control input_text select2"
							name="situacion_id" 
							id="situacion_id" 
							title="Seleccione la situación">
							<option value="_all_">TODOS</option>
							<?php
							$sel_query = $mysqli->query("SELECT id, nombre
								FROM cont_etapa_programacion
								WHERE status = 1
								ORDER BY nombre ASC");
							while($sel=$sel_query->fetch_assoc()){
							?>
								<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
								<?php
							}
							?>
						</select>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
						<label for="start">Fecha inicio de la programación:</label>
						<div class="input-group">
							<input
									type="text"
									name="fecha_inicio"
									id="fecha_inicio"
									class="form-control sec_contrato_tesoreria_datepicker"
									value="<?php echo date("01/01/Y");?>"
									style="height: 30px;"
									>
							<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="fecha_inicio"></label>
						</div>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
						<div class="form-group">
						<label for="start">Fecha fin de la programación:</label>
						<div class="input-group">
							<input
									type="text"
									name="fecha_fin"
									id="fecha_fin"
									class="form-control sec_contrato_tesoreria_datepicker"
									value="<?php echo date("t/m/Y");?>"
									style="height: 30px;"
									>
							<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="fecha_fin"></label>
						</div>
						</div>
					</div>

					<div class="col-xs-12 col-sm-6 col-md-3 col-lg-3">
						<button 
							type="button" 
							name="btn_buscar_programacion" 
							value="1" 
							class="btn btn-success btn-block btn-sm" 
							data-button="request" 
							data-toggle="tooltip" 
							data-placement="top" 
							title="Buscar programación" 
							onclick="sec_contrato_tesoreria_listar_programaciones();"
							style="position: relative; bottom: -19px; margin-bottom: 30px;">
							<i class="glyphicon glyphicon-search"></i>
							Buscar
						</button>
					</div>
				</form>
			</fieldset>
		</div>
	</div>
	<!--LISTADO DE PROGRAMACIONES-->
	<div class="row mt-3 mb-2">
		<div class="table-responsive">
			<table id="tabla_programaciones" class="table table-bordered table-hover" style="font-size: 11px">
				<thead>
					<tr>
						<th>N.° Programación</th>
						<th>F. Programación</th>
						<th>Concepto de la programación</th>
						<th>Tipo de pago</th>
						<th>Banco</th>
						<th>Moneda</th>
						<th>Importe</th>
						<th>Situación</th>
						<th>Opciones</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
	</div>
</div>


<!-- INICIO MODAL COMPROBANTE DE PAGO -->
<div id="modal_comprobante_de_pago" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="programacion_id_h4">Programación - Subir comprobante de pago</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form id="form_comprobante_pago" name="form_comprobante_pago" method="POST" enctype="multipart/form-data" autocomplete="off">
						<input type="hidden" id="programacion_id_comprobante">
						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="form-group">
								<label>Comprobante de pago subido al sistema:</label>
								<table id="tabla_comprobantes" class="table table-bordered">
									<thead>
										<th>Subido por</th>
										<th>Subido el</th>
										<th>Comprobante de pago</th>
									</thead>
									<tbody>
									</tbody>
								</table>
							</div>

							<div>
								<label for="fecha_de_pago">Seleccione la fecha de pago:</label>
								<div class="input-group">
									<input type="text" name="fecha_de_pago" id="fecha_de_pago"
											class="form-control sec_contrato_tesoreria_datepicker"
											value="<?php echo date("Y-m-d");?>">
									<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="fecha_de_pago"></label>
								</div>
							</div>
							<div>
								<label>Seleccione el comprobante de pago:</label>
								<input type="file" class="form-control" name="comprobante_de_pago" id="comprobante_de_pago" required accept=".jpg, .jpeg, .png, .pdf">
							</div>
						</div>
					</form>
				</div> 
			</div>
			<!--BOTONES-->
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" data-dismiss="modal">
					<i class="icon fa fa-close"></i>
					<span>Cerrar ventana</span>
				</button>

				<button type="button" class="btn btn-success" id="btnModalRegistrarComprobante" onclick="sec_contrato_tesoreria_subir_comprobante();">
					<i class="icon fa fa-cloud-upload"></i>
					<span>Subir comprobante de pago</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL COMPROBANTE DE PAGO -->

<!--MODAL VISUALIZACION COMPROBANTE-->
<div id="div_modal_archivo" class="modal fade" style="min-height: 500px;" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title">COMPROBANTE DE PAGO</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form>
						<div class="col-md-12">
							<div class="panel" id="div_contenido_archivo" style="display: none;">
								<div class="panel-heading">
									<div class="panel-title" id="div_heading_value_archivo">TEMPORAL</div>
								</div>
								<div class="panel-body" style="padding: 10px 0px 10px 0px;">
									<div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center; margin-bottom: 5px; display:none;" id="div_Ver_Pdf">
										<button type="button" class="btn btn-block btn-primary" data-toggle="modal" style="border-color: #aaf152;">
											<i class="fa fa-arrows-alt"></i>  Ver documento en toda la Pantalla
										</button>        
									</div>
									<div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center;margin-bottom: 5px; display:none;" id="divVisorPdfPrincipal">
									</div>
									<div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center;margin-bottom: 5px; display:none;" id="divVerImagenFullPantallaModal">
										<input type="hidden" name="midocu" id="midocu"/>
										<button type="button" class="btn btn-block btn-block btn-primary" id="btnVista" onclick="sec_contrato_tesoreria_verFullImagenComprobante();">
											<i class="fa fa-arrows-alt"></i>  Ver imagen en toda la Pantalla
										</button>
									</div>
									<div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center;margin-bottom: 5px; display:none;" id="divVisorImagen">
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

<!-- INICIO MODAL ELIMINAR PROGRAMACIÓN -->
<div id="modal_eliminar_programacion" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="sec_contrato_titulo_modal_eliminar">Eliminar programación</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form id="form_eliminar_programacion" autocomplete="off" >
						<input type="hidden" id="programacion_id_eliminar"/>
						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="form-group">
								<label id="pregunta_programacion_eliminar">¿Está seguro de eliminar la programación?</label>
							</div>
						</div>
					</form>
				</div> 
			</div>
			<div class="modal-footer">
				<button 
					type="button" 
					class="btn btn-success" 
					data-dismiss="modal"
				>
					<i class="icon fa fa-close"></i>
					<span>No, cancelar operación</span>
				</button>

				<button 
					type="button" 
					class="btn btn-danger" 
					id="btnEliminarProgramacion" 
					onclick="sec_contrato_tesoreria_eliminar_programacion();"
				>
					<i class="icon fa fa-trash-o"></i>
					<span>Si, eliminar la programación</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL ELIMINAR PROGRAMACIÓN -->

<!--INICIO MODAL DETALLE PROGRAMACION-->
<div id="sec_contrato_modal_detalle_programacion" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog" role="document" style="width: 80%">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="col-xs-12 text-center">
					<h1 class="page-title titulosec_contrato"><i class="icon icon-inline fa fa-fw fa-money"></i>
						Detalle de la programación de pago
					</h1>
				</div>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="content container-fluid">
						<input type="hidden" name="id_detalle_programacion_seleccionada" id="id_detalle_programacion_seleccionada">
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
											<th>N. Doc</th>
											<th>Acreedor</th>
											<th>Día de pago</th>
											<th>F. Vencimiento</th>
											<th>Moneda</th>
											<th>Programado</th>
											<th>Centro de costos</th>
										</tr>
									</thead>
									<tbody>
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
							<button type="button" class="btn btn-danger" title="Cancelar" data-dismiss="modal" aria-label="Close">
								<i class="fa fa-arrow-left"></i> Regresar
							</button>
						</div>

						
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL DETALLE PROGRAMACION-->

<!--INICIO MODAL VISUALIZACION DE COMPROBANTE-->
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
<!-- FIN MODAL VISUALIZACION DE COMPROBANTE-->

<!-- INICIO MODAL PROCESAR PROGRAMACION-->
<div id="sec_contrato_modal_procesar_programacion" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog" role="document" style="width: 80%">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<div class="col-xs-12 text-center">
					<h1 class="page-title"><i class="icon icon-inline fa fa-fw fa-cogs"></i>
						Procesar la programación de pago
					</h1>
				</div>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="content container-fluid">
						<input type="hidden" name="sec_proc_id_programacion_seleccionada" id="sec_proc_id_programacion_seleccionada">

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
														value="<?php echo date("d-m-Y");?>"
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
												id="tipo_de_cambio_datos_pago"
												name="tipo_de_cambio_datos_pago" 
												readonly="readonly"
												value=""
												style="margin-bottom: 15px;"
											>
										</div>

										<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
											<label>Subdiario</label>
											<input 
												type="text" 
												class="form-control" 
												name="subdiario" 
												readonly="readonly"
												value="2222"
												style="margin-bottom: 15px;"
											>
										</div>

									</form>
								</fieldset>
							</div>
						</div>

						<!-- <div class="row mt-2 mb-2">
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
						</div> -->

						<div class="row mt-2 mb-2" style="text-align: right;">
							<button 
								type="button" 
								class="btn btn-danger" 
								title="Cancelar" 
								data-dismiss="modal" 
								aria-label="Close">
								<i class="fa fa-arrow-left"></i> Regresar
							</button>

							<button 
								type="button"
								class="btn btn-success" 
								title="Cancelar" 
								onclick="sec_contrato_procesar_programacion_modal_confirmar_proceso();">
								<i class="fa fa-cogs"></i> Procesar programación
							</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL PROCESAR PROGRAMACION-->

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

<!-- INICIO MODAL EXPORTAR ASIENTO CONTABLE -->
<div id="modal_exportar_asiento" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="sec_contrato_titulo_modal_exportar_asiento_contable">Exportar Asiento Contable</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form id="form_asiento_contable_programacion" autocomplete="off" >
						<input type="hidden" id="programacion_id_asiento_contable"/>
						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="form-group">
								<label id="pregunta_programacion_eliminar">Ingresar correlativo CONCAR</label>
								<input type="text" name="num_docu_asiento_contable" id="num_docu_asiento_contable">
							</div>
						</div>
					</form>
				</div> 
			</div>
			<div class="modal-footer">
				<button 
					type="button" 
					class="btn btn-danger" 
					data-dismiss="modal"
				>
					<i class="icon fa fa-close"></i>
					<span>Cancelar</span>
				</button>

				<button 
					type="button" 
					class="btn btn-success" 
					id="btnEliminarProgramacion" 
					onclick="sec_contrato_tesoreria_exportar_asiento_contable();"
				>
					<i class="icon fa fa-trash-o"></i>
					<span>Exportar Asiento Contable</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL EXPORTAR ASIENTO CONTABLE -->