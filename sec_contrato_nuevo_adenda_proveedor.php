<?php
global $mysqli;
$menu_id = "";
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'contrato' AND sub_sec_id = 'nuevo' LIMIT 1");
while ($r = $result->fetch_assoc())
	$menu_id = $r["id"];
	$usuario_id = $login?$login['id']:null;
?>
<style>
	.campo_obligatorio{
		font-size: 15px;
		color: red;
	}

	.campo_obligatorio_v2{
		font-size: 13px;
		color: red;
	}
</style>


<div id="div_sec_contrato_nuevo">

	<div id="loader_"></div>

	<div class="row">
		<div class="col-xs-12 text-center">
			<h1 class="page-title titulosec_contrato"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Nueva Solicitud - Adenda de Contrato Proveedores</h1>
		</div>
	</div>


	<div class="row">
		<div class="col-xs-12 col-md-12 col-lg-12">
			<!-- PANEL: Tipo contrato -->
			<div class="panel">
				<div class="panel-heading">
					<div class="panel-title">Nueva Solicitud</div>
				</div>
				<div class="panel-body">
					<form id="form_contrato_interno" name="form_contrato_interno" method="POST" enctype="multipart/form-data" autocomplete="off">
						<input type="hidden" name="usuario_id_temporal" id="usuario_id_temporal" value="<?php echo $usuario_id;?>">
						<input type="hidden" name="tipo_contrato_id"  id="tipo_contrato_id" value="2">

						<div class="row">
							<div class="col-xs-12 col-md-5 col-lg-5">
								<div class="form-group">
									<label for="sec_con_nuevo_proveedor">Nombre del Proveedor:</label>
									<select onchange="sec_con_nuevo_aden_prov_obtener_contratos()" class="form-control input_text select2" name="sec_con_nuevo_proveedor" id="sec_con_nuevo_proveedor">
									</select>
								</div>
							</div>
						
							<div class="col-xs-12 col-md-7 col-lg-7">
								<div class="form-group">
									<label for="exampleInputEmail1">Seleccione el contrato</label>
									<select onchange="sec_con_nuevo_aden_prov_obtener_contratos_interno_id()" class="form-control select2" name="sec_con_nuevo_contrato_id" id="sec_con_nuevo_contrato_id">
									</select>
								</div>
							</div>
						</div>

						
						<div class="row">
							<div class="col-md-12">
								<br>
							</div>
							<div class="col-xs-12 col-md-12 col-lg-7">
								<div id="div_contrato_interno"></div>
							</div>

							<div class="col-xs-12 col-md-12 col-lg-5" id="div_detalle_solicitud_derecha" style="display:none">
								<div class="panel" id="divDetalleSolicitud">
									<div class="panel-body" style="padding: 5px 10px 5px 10px;">
										<div class="panel-group no-mb" id="accordion" role="tablist" aria-multiselectable="true">
											<div class="panel">
												<div class="panel-heading" role="tab" id="browsers-this-week-heading">
													<div class="panel-title">
														<a href="#browsers-this-week" role="button" data-toggle="collapse"
														data-parent="#accordion" aria-expanded="true" aria-controls="browsers-this-week">
															Adenda - Cambios solicitados
														</a>
													</div>
												</div>

												<div id="browsers-this-week" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="browsers-this-week-heading">
													<div class="panel-body">
														<input type="hidden" id="contrato_id" value="">

														<div id="divTablaAdendas" tabindex="0">
															<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">
																<thead>
																	<tr>
																		<th>#</th>
																		<th>Menú</th>
																		<th>Campo</th>
																		<th>Valor Actual</th>
																		<th>Nuevo Valor</th>
																		<th></th>
																	</tr>
																</thead>
																<tbody>
																	<tr>
																		<td colspan="6" style="text-align: center;">
																			<i class="icon fa fa-fw fa-arrow-left"></i> Clic en el botón verde "Editar" para añadir el cambio esta solicitud.
																		</td>
																	</tr>
																</tbody>
															</table>
														</div>

														<div class="form-group" style="margin-bottom: 10px; margin-top: 10px;" >

															<?php
															$campo_aprobacion_tooltip = '';
															$aprobacion_obligatoria_id = 1;
															$campo_aprobacion_mensaje = '<span class="campo_obligatorio_v2">(*)</span>';
															$query_directores = "SELECT user_id FROM cont_usuarios_directores WHERE status = 1";
															$sel_query = $mysqli->query($query_directores);
															while($sel=$sel_query->fetch_assoc()){
																if ($sel["user_id"] == $usuario_id) {
																	$campo_aprobacion_tooltip = ' data-toggle="tooltip" data-placement="left" title="Opcional para directores" ';
																	$campo_aprobacion_mensaje = '(Opcional)';
																	$aprobacion_obligatoria_id = 0;
																}
															}
															?>

															<input type="hidden" id="aprobacion_obligatoria_id" name="aprobacion_obligatoria_id" value="<?php echo $aprobacion_obligatoria_id; ?>">

															<div class="control-label" <?php echo $campo_aprobacion_tooltip; ?>>
																Aprobación de: <?php echo $campo_aprobacion_mensaje; ?>:
															</div>

															<div <?php echo $campo_aprobacion_tooltip; ?>>
																<select 
																	class="form-control input_text select2"
																	name="director_aprobacion_id" 
																	id="director_aprobacion_id" 
																	title="Seleccione a el director">
																</select>
															</div>

															
															
															
														</div>

														<br>
														<button type="button" class="btn btn-success btn-xs btn-block" id="btnRegistrarAdenda" onclick="sec_con_nuevo_aden_prov_guardar_adenda();">
															<i class="icon fa fa-save"></i>
															<span id="demo-button-text">Registrar Solicitud de Adenda</span>
														</button>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>

							<!-- <div class="col-md-5">
								<div class="panel">
									<div class="panel-heading">
										<div class="panel-title">DETALLE DE LA SOLICITUD DE CONTRATO INTERNO</div>
										<input type="hidden" value="'.$contrato_id.'" id="id_registro_contrato_id">
									</div>
									<div class="panel-body">
										<div id="divTablaAdendas"></div>
									</div>
								</div>
								
							</div> -->


						</div>
						
					</form>
				</div>
			</div>
			<!-- /PANEL: Tipo contrato -->
		</div>
	</div>
</div>


<!-- INICIO MODAL SOLICITUD DE ADENDA -->
<div id="modal_adenda" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Adenda - Solicitud de edición</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form id="form_adenda" autocomplete="off" >
						<input type="hidden" id="adenda_nombre_tabla">
						<input type="hidden" id="adenda_nombre_campo">
						<input type="hidden" id="adenda_tipo_valor">
						<input type="hidden" id="adenda_id_del_registro">
						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="form-group">
								<table class="table table-bordered">
									<tr>
										<td><b>Nombre del Menú:</b></td>
										<td id="adenda_nombre_menu_usuario"></td>
									</tr>
									<tr>
										<td><b>Nombre del Campo:</b></td>
										<td id="adenda_nombre_campo_usuario"></td>
									</tr>
									<tr>
										<td><b>Valor Actual:</b></td>
										<td id="adenda_valor_actual"></td>
									</tr>
									<tr>
										<td><b>Nuevo Valor:</b></td>
										<td>
											<div id="div_adenda_valor_varchar">
												<input type="text" id="adenda_valor_varchar" class="filtro txt_filter_style" style="width: 100%;">
											</div>
											<div id="div_adenda_valor_textarea">
												<textarea id="adenda_valor_textarea" class="form-control" rows="5"></textarea>
											</div>
											<div id="div_adenda_valor_int">
												<input type="text" id="adenda_valor_int" class="filtro txt_filter_style" style="width: 100%;">
											</div>
											<div id="div_adenda_valor_date">
												<input
												type="text"
												class="form-control sec_contrato_nuevo_datepicker"
												id="adenda_valor_date"
												value="<?php echo date("d-m-Y", strtotime("+1 days"));?>"
												readonly="readonly"
												style="height: 34px;"
												>
											</div>
											<div id="div_adenda_valor_decimal">
												<input type="text" id="adenda_valor_decimal" class="filtro txt_filter_style money" style="width: 100%;">
											</div>
											<div id="div_adenda_valor_select_option">
												<select  class="form-control" id="adenda_valor_select_option" name="adenda_valor_select_option">
												</select>
											</div>

											<div id="div_adenda_solicitud_departamento" class="col-xs-12 col-md-12 col-lg-12" >
												<div class="form-group">
													<div class="control-label">Departamento:</div>
													<select class="form-control select2" name="adenda_inmueble_id_departamento" 
														id="adenda_inmueble_id_departamento">
													</select>
												</div>
											</div>
											<div id="div_adenda_solicitud_provincias" class="col-xs-12 col-md-12 col-lg-12">
												<div class="form-group">
													<div class="control-label">Provincia:</div>
													<select class="form-control input_text select2" name="adenda_inmueble_id_provincia" 
														id="adenda_inmueble_id_provincia">
													</select>
												</div>
											</div>
											<div id="div_adenda_solicitud_distrito" class="col-xs-12 col-md-12 col-lg-12">
												<div class="form-group">
													<div class="control-label">Distrito:</div>
													<select class="form-control select2"	name="adenda_inmueble_id_distrito" 
														id="adenda_inmueble_id_distrito">
													</select>
												</div>
											</div>
											<input type="hidden" id="ubigeo_id_nuevo">
											<input type="hidden" id="ubigeo_text_nuevo">
										</td>
									</tr>
								</table>
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_adenda_mensaje" style="display: none">
							<div class="form-group">
								<div class="alert alert-danger" role="alert">
									<strong id="modal_adenda_mensaje"></strong>
								</div>
							</div>
						</div>
					</form>
				</div> 
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
				<button type="button" class="btn btn-success" id="btnModalRegistrarAdenda" onclick="sec_con_nuevo_aden_prov_guardar_detalle_adenda('modalAgregar');">
					<i class="icon fa fa-plus"></i>
					<span id="demo-button-text">Agregar solicitud de edición a la Adenda</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL SOLICITUD DE ADENDA -->

<!-- INICIO MODAL NUEVO PROPIETARIO AP -->
<div id="modalNuevoProveedor" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_nuevo_proveedor_titulo_ap">Adenda - Nueva Representante Legal</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form autocomplete="off" id="frm_adenda_nuevo_proveedor">
				
						<div class="col-xs-12 col-md-6 col-lg-6">
							<div class="form-group">
								<div class="control-label">DNI del Representante Legal :</div>
								<input type="text" name="modal_prov_ade_int_dni_representante" id="modal_prov_ade_int_dni_representante" 
								maxlength=8
								class="filtro" 
								style="width: 100%; height: 30px;"
								oninput="this.value=this.value.replace(/[^0-9]/g,'');"
								>
							</div>
						</div>

						<!--NOMBRE COMPLETO REPRESENTANTE LEGAL-->
						<div class="col-xs-12 col-md-6 col-lg-6">
							<div class="form-group">
								<div class="control-label">Nombre Completo del Representante Legal :</div>
								<input type="text" name="modal_prov_ade_int_nombre_representante" id="modal_prov_ade_int_nombre_representante" maxlength=50 class="filtro"
								 style="width: 100%; height: 30px;">
							</div>
						</div>
					</div>
					<div class="row">
						<!--BANCO-->
						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Banco : </div>
								<select class="form-control input_text select2" data-live-search="true" 
									name="modal_prov_ade_int_prov_banco" id="modal_prov_ade_int_prov_banco" title="Seleccione el banco">
									<?php 
									$banco_query = $mysqli->query("SELECT id, ifnull(nombre, '') nombre_banco
																FROM tbl_bancos
																WHERE estado = 1");
									while($row=$banco_query->fetch_assoc()){
										?>
										<option value="<?php echo $row["id"] ?>"><?php echo $row["nombre_banco"] ?></option>
										<?php
									}
									?>
								</select>
							</div>
						</div>

						<!--NRO CUENTA-->
						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Nro Cuenta : </div>
								<input type="text" id="modal_prov_ade_int_nro_cuenta" name="modal_prov_ade_int_nro_cuenta" maxlength="50"
								 style="width: 100%; height: 30px;" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
							</div>
						</div>

						<!--NRO CCI-->
						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Nro CCI : </div>
								<input type="text" id="modal_prov_ade_int_nro_cci" name="modal_prov_ade_int_nro_cci" maxlength="50"
								 style="width: 100%; height: 30px;" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
							</div>
						</div>
						<div class="col-xs-12 col-md-6 col-lg-6" style="display:none">
							<div class="form-group">
								<div class="control-label">
									Vigencia
								</div>
								<input type="file" name="modal_prov_ade_int_file_vigencia_nuevo_rl" accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip">
							</div>
						</div>
						<div class="col-xs-12 col-md-6 col-lg-6" style="display:none">
							<div class="form-group">
								<div class="control-label">
									DNI
								</div>
								<input type="file" name="modal_prov_ade_int_file_dni_nuevo_rl" accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_propietario_mensaje_ap" style="display: none">
							<div class="form-group">
								<div class="alert alert-danger" role="alert">
									<strong id="modal_propietario_mensaje"></strong>
								</div>
							</div>
						</div>
					</form>
				</div> 
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
				<button type="button" onclick="sec_con_nuevo_aden_prov_guardar_nuevo_representante_legal()" class="btn btn-success" >
					<i class="icon fa fa-plus"></i>
					Agregar Representante Legal
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL NUEVO PROPIETARIO AP -->


<!-- INICIO MODAL NUEVO PROPIETARIO AP -->
<div id="modalNuevoContraprestacion" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_nuevo_contraprestacion_titulo_ap">Adenda - Nueva Contraprestacion</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form autocomplete="off" id="frm_nuevo_contraprestacion">
						<div class="col-xs-12 col-md-4 col-lg-6">
							<div class="form-group">
								<div class="control-label">Tipo de Moneda <span class="campo_obligatorio_v2">(*)</span>:</div>
								<select name="modal_contr_ade_int_moneda_id" id="modal_contr_ade_int_moneda_id" class="form-control select2" style="width: 100%;">
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-6">
							<div class="form-group">
								<div class="control-label">Monto Bruto <span class="campo_obligatorio_v2">(*)</span>:</div>
								<input type="text" name="modal_contr_ade_int_monto" id="modal_contr_ade_int_monto" class="filtro" style="width: 100%; height: 28px;">
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-6">
							<div class="form-group">
								<div class="control-label">IGV (18%):</div>
								<select 
									name="modal_contr_ade_int_tipo_igv_id" 
									id="modal_contr_ade_int_tipo_igv_id" 
									class="form-control select2"
									style="width: 100%;">
									<option value="0">Seleccione</option>
									<option value="1">SI</option>
									<option value="2">No</option>
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-6">
							<div class="form-group">
								<div class="control-label" id="label_subtotal">Subtotal <span class="campo_obligatorio_v2">(*)</span>:</div>
								<input type="text" name="modal_contr_ade_int_subtotal" id="modal_contr_ade_int_subtotal" class="filtro" style="width: 100%; height: 28px;">
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-6">
							<div class="form-group">
								<div class="control-label" id="label_igv">Monto del IGV :</div>
								<input type="text" name="modal_contr_ade_int_igv" id="modal_contr_ade_int_igv" class="filtro" style="width: 100%; height: 28px;">
							</div>
						</div>						

						<div class="col-xs-12 col-md-4 col-lg-6" style="display: none;">
							<div class="form-group">
								<div class="control-label">Forma de Pago <span class="campo_obligatorio_v2">(*)</span>:</div>
								<select class="form-control select2" name="modal_contr_ade_int_forma_pago" id="modal_contr_ade_int_forma_pago" style="width: 100%;">
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-6">
							<div class="form-group">
								<div class="control-label">Tipo de Comprobante a Emitir <span class="campo_obligatorio_v2">(*)</span>:</div>
								<select 
									name="modal_contr_ade_int_tipo_comprobante" 
									id="modal_contr_ade_int_tipo_comprobante" 
									class="form-control select2" 
									style="width: 100%; height: 28px;">
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-6">
							<div class="form-group">
								<div class="control-label">Plazo de Pago <span class="campo_obligatorio_v2">(*)</span>:</div>
								<input type="text" name="modal_contr_ade_int_plazo_pago" id="modal_contr_ade_int_plazo_pago" class="filtro" 
								style="width: 100%; height: 28px;">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="form-group">
								<div class="control-label">Forma de Pago<span class="campo_obligatorio_v2">(*)</span>:</div>
								<textarea name="modal_contr_ade_int_forma_pago_detallado" id="modal_contr_ade_int_forma_pago_detallado" rows="3" style="width: 100%;" placeholder="Ingrese  el detalle de la forma de pago"></textarea>
							</div>
						</div>
					</form>
				</div> 
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
				<button type="button" class="btn btn-success" onclick="sec_con_nuevo_aden_prov_nuevo_contraprestacion()" id="btn_agregar_contraprestacion_ap">
					<i class="icon fa fa-plus"></i>
					Agregar Contraprestación
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL NUEVO PROPIETARIO AP -->


<!-- INICIO MODAL NUEVO PROPIETARIO AP -->
<div id="modalNuevoArchivoAnexo" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-md" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_nuevo_contraprestacion_titulo_ap">Adenda - Nueva Archivo Anexo</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form autocomplete="off" id="sec_nuevo_nuevos_anexos_listado">
						<div class="col-md-12">
							<div class="form-group">
								<div class="control-label">Tipo de Anexo:</div>
								<select name="modal_nuevo_select_archivo_anexo" id="modal_nuevo_select_archivo_anexo" class="form-control select2" style="width: 100%;">
								
								</select>
							</div>
						</div>
					</form>
				</div> 
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
				<button type="button" class="btn btn-success" onclick="sec_con_nuevo_aden_anadir_archivo_proveedor()" id="btn_agregar_contraprestacion_ap">
					<i class="icon fa fa-plus"></i>
					Agregar Archivo Anexo
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL NUEVO PROPIETARIO AP -->











