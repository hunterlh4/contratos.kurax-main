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

	.sec_contrato_nuevo_resolucion_datepicker {
    	min-height: 28px !important;
	}
	
</style>


<div id="div_sec_contrato_nuevo">

	<div id="loader_"></div>

	<div class="row">
		<div class="col-xs-12 text-center">
			<h1 class="page-title titulosec_contrato"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Nueva Solicitud - Resolucion de Contrato</h1>
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
					<form id="form_resolucion_contrato" name="form_resolucion_contrato" method="POST" enctype="multipart/form-data" autocomplete="off">
						<input type="hidden" name="usuario_id_temporal" id="usuario_id_temporal" value="<?php echo $usuario_id;?>">

						<div class="row">
							<div class="col-xs-12 col-md-4 col-lg-4">
								<div class="form-group">
									<label for="sec_con_nuevo_resol_tipo_contrato_id">Tipo de Contrato: <span class="text-danger">(*)</span></label>
									<select onchange="sec_con_nuevo_resol_obtener_contratos()" class="form-control input_text select2" name="sec_con_nuevo_resol_tipo_contrato_id" id="sec_con_nuevo_resol_tipo_contrato_id">
									</select>
								</div>
							</div>

							<div class="col-xs-12 col-md-8 col-lg-8">
								<div class="form-group">
									<label for="sec_con_nuevo_resol_contrato_id">Contrato: <span class="text-danger">(*)</span></label>
									<select class="form-control input_text select2" name="sec_con_nuevo_resol_contrato_id" id="sec_con_nuevo_resol_contrato_id">
										<option value="0">- Seleccione -</option>
									</select>
								</div>
							</div>

					
							

							<div class="col-xs-12 col-md-3 col-lg-3">
								<div class="form-group">
									<label for="sec_con_nuevo_resol_aprobante_id">Aprobaciòn de :<span class="text-danger">(*)</span></label>
									<select onchange="sec_con_nuevo_resol_obtener_cargo_aprobante()" class="form-control input_text select2" name="sec_con_nuevo_resol_aprobante_id" id="sec_con_nuevo_resol_aprobante_id">
									</select>
								</div>
							</div>

							<div class="col-xs-12 col-md-2 col-lg-2">
								<div class="form-group">
									<label for="sec_con_nuevo_resol_cargo_aprobante_id">Cargo del Aprobante : <span class="text-danger">(*)</span></label>
									<select class="form-control input_text select2" name="sec_con_nuevo_resol_cargo_aprobante_id" id="sec_con_nuevo_resol_cargo_aprobante_id">
									</select>
								</div>
							</div>

							<div class="col-xs-12 col-md-2 col-lg-2" id="div_fecha_carta" style="display:none">
								<div class="form-group">
									<label for="sec_con_nuevo_resol_fecha_carta">Fecha de Carta: <span class="text-danger">(*)</span></label>
									<div class="input-group">
										<input type="text" name="sec_con_nuevo_resol_fecha_carta" id="sec_con_nuevo_resol_fecha_carta" class="form-control sec_contrato_nuevo_resolucion_datepicker"
												value="<?php echo date("d-m-Y");?>"
												readonly="readonly" style="height: 30px;">
										<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="sec_con_nuevo_resol_fecha_carta"></label>
									</div>
								</div>
							</div>

							<div class="col-xs-12 col-md-2 col-lg-2">
								<div class="form-group">
									<label for="sec_con_nuevo_resol_fecha_resolucion">Fecha Resolución: <span class="text-danger">(*)</span></label>
									<div class="input-group">
										<input type="text" name="sec_con_nuevo_resol_fecha_resolucion" id="sec_con_nuevo_resol_fecha_resolucion" class="form-control sec_contrato_nuevo_resolucion_datepicker"
												value="<?php echo date("d-m-Y");?>"
												readonly="readonly" style="height: 30px;">
										<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="sec_con_nuevo_resol_fecha_resolucion"></label>
									</div>
								</div>
							</div>

							<div class="col-xs-12 col-md-5 col-lg-5" id="div_anexo_resolucion_contrato">
								<div class="form-group">
									<label for="sec_con_nuevo_resol_anexo">Anexo :</label>
									<input type="file" class="form-control" name="sec_con_nuevo_resol_anexo" id="sec_con_nuevo_resol_anexo">
								</div>
							</div>

							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="form-group">
									<label for="sec_con_nuevo_resol_motivo">Motivo:</label>
									<textarea class="form-control" name="sec_con_nuevo_resol_motivo" id="sec_con_nuevo_resol_motivo" cols="30" rows="5"></textarea>
								</div>
							</div>
							
							<div class="col-xs-12 col-md-12 col-lg-12">
								<br>
								<button type="submit" class="btn btn-success btn-block" id="guardar_contrato_proveedor">
									<i class="icon fa fa-save" id="demo-button-icon-left" style="display: inline-block;"></i>
									<span id="demo-button-text">Solicitar Resolución de Contrato</span>
								</button>
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
														</div>

														<button type="button" class="btn btn-success btn-xs btn-block" id="btnRegistrarAdenda" onclick="sec_con_nuevo_resol_guardar_adenda();">
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
				<button type="button" onclick="sec_con_detalle_aden_prov_guardar_nuevo_representante_legal()" class="btn btn-success" >
					<i class="icon fa fa-plus"></i>
					Agregar Representante Legal
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL NUEVO PROPIETARIO AP -->

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
				<button type="button" class="btn btn-success" id="btnModalRegistrarAdenda" onclick="sec_con_nuevo_resol_guardar_detalle_adenda('modalAgregar');">
					<i class="icon fa fa-plus"></i>
					<span id="demo-button-text">Agregar solicitud de edición a la Adenda</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL SOLICITUD DE ADENDA -->

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
				<button type="button" class="btn btn-success" onclick="sec_con_nuevo_resol_nuevo_contraprestacion()" id="btn_agregar_contraprestacion_ap">
					<i class="icon fa fa-plus"></i>
					Agregar Contraprestación
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL NUEVO PROPIETARIO AP -->

<!-- INICIO MODAL BUSCAR PROPIETARIO CA -->
<div id="modalBuscarPropietario_ca" class="modal" data-keyboard="false">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_buscar_propietario_titulo">Buscar Propietario del Inmueble</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form id="frmBuscarRemitente" autocomplete="off">

						<input type="hidden" id="modal_id_propietario_old">
						<input type="hidden" id="modal_id_persona_old">
						<input type="hidden" id="modal_buscar_propietario_tipo_solicitud">

						<div class="col-md-12 col-sm-12 col-xs-12" style="padding: 10px 15px 10px 15px;">
							<div class="form-group">
								<div class="col-md-4 col-sm-4 col-xs-12" style="padding: 0px;">
									<select name="modal_propietario_tipo_busqueda_ca" id="modal_propietario_tipo_busqueda_ca" class="form-control">
										<option value="1">Buscar por Nombre de Propietario</option>
										<option value="2">Buscar por Numero de Documento (DNI o RUC)</option>
									</select>
								</div>
								<div class="col-md-5 col-sm-5 col-xs-12">
									<input type="text" name="modal_propietario_nombre_o_numdocu_ca" id="modal_propietario_nombre_o_numdocu_ca" class="form-control" placeholder="Ingrese el nombre, despues los apellidos">
								</div>
								<div class="col-md-3 col-sm-3 col-xs-12" style="padding: 0px;">
									<button type="button" class="btn btn-success btn-sm btn-block" id="btnBuscarPropietario_ca" onclick="sec_con_nuevo_resol_buscar_propietario()">
										<i class="icon fa fa-search"></i>
										<span id="demo-button-text">Buscar Propietario</span>
									</button>
								</div>
							</div>
						</div>

						<div class="col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 10px">
							<div class="form-group" id="tlbPropietariosxBusqueda_ca">
							</div>
						</div>

						<div id="divNoSeEncontroPropietario_ca" class="col-md-12 col-sm-12 col-xs-12" style="display: none; margin-bottom: 10px">
							<div class="form-group">
								<div class="alert alert-warning" role="alert">
									<div class="h4 strong">Resultados de la busqueda:</div>
									<p>
										No existe en la base de datos el propietario con <a href="#" class="alert-link" id="valoresDeBusqueda_ca"></a>. Clic en el boton Registrar nuevo propietario para registrarlo en nuestra base de datos.
									</p>
									<p>
										
									</p>
								</div>
							</div>
						</div>

						<div id="divRegistrarNuevoPropietario_ca" class="col-md-12 col-sm-12 col-xs-12" style="display: none;">
							<div class="form-group">
								<button type="button" onclick="sec_con_nuevo_resol_propietario_modal()" class="btn btn-success btn-sm btn-block">
									<i class="icon fa fa-plus"></i>
									<span id="demo-button-text">Registrar Nuevo Propietario</span>
								</button>
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12" id="div_modal_buscar_propietario_mensaje" style="display: none">
							<div class="form-group">
								<div class="alert alert-danger" role="alert">
									<strong id="modal_buscar_propietario_mensaje"></strong>
								</div>
							</div>
						</div>

					</form>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
			</div>             
		</div>
	</div>
</div>
<!-- FIN MODAL BUSCAR PROPIETARIO CA -->

<!-- INICIO MODAL NUEVO PROPIETARIO CA -->
<div id="modalNuevoPropietario_aa" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_nuevo_propietario_titulo_aa">Registrar Nuevo Propietario</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form autocomplete="off" id="frm_nuevo_propietario_aa">
						<input type="hidden" id="modal_nuevo_propietario_tipo_solicitud_aa">
						<input type="hidden" id="modal_propietaria_id_para_cambios_aa">
						<input type="hidden" id="modal_propietaria_id_persona_para_cambios_aa">
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Tipo de Persona:</div>
								<select
									class="form-control select2"
									name="modal_propietario_tipo_persona_aa" 
									id="modal_propietario_tipo_persona_aa">
									<option value="0">- Seleccione -</option>
									<?php
									$sel_query = $mysqli->query("SELECT id, nombre
									FROM cont_tipo_persona
									WHERE estado = 1
									ORDER BY id ASC;");
									while($sel=$sel_query->fetch_assoc()){?>
									<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Nombre / Razón Social del propietario:</div>
								<input type="text" id="modal_propietario_nombre_aa" name="modal_propietario_nombre" class="filtro txt_filter_style" maxlength="50" style="width: 100%; height: 30px;">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Tipo de documento de identidad:</div>
								<select class="form-control" id="modal_propietario_tipo_docu_aa">
									<option value="0">- Seleccione -</option>
									<?php
									$sel_query = $mysqli->query("
									SELECT 
										id, 
										nombre
									FROM 
										cont_tipo_docu_identidad
									WHERE 
										estado = 1
									ORDER BY id ASC
									");
									while($sel=$sel_query->fetch_assoc()){?>
									<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_num_docu_propietario_aa" style="display: none;">
							<div class="form-group">
								<div class="control-label" id="label_num_docu_propietario_aa">Número de documento de identidad del propietario:</div>
								<input type="number" id="modal_propietario_num_docu_aa" name="modal_propietario_num_docu" class="filtro mask_dni_agente txt_filter_style" maxlength="11" style="width: 100%; height: 30px;">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_num_ruc_propietario_aa">
							<div class="form-group">
								<div class="control-label">Número de RUC del propietario:</div>
								<input type="number" id="modal_propietario_num_ruc_aa" name="modal_propietario_num_ruc" class="filtro mask_ruc_agente txt_filter_style" maxlength="11" style="width: 100%; height: 30px;">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Domicilio del propietario:</div>
								<input type="text" id="modal_propietario_direccion_aa" name="modal_propietario_direccion" class="filtro txt_filter_style" maxlength="100" style="width: 100%; height: 30px;" autocomplete="off">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_propietario_representante_legal_aa" style="display: none;">
							<div class="form-group">
								<div class="control-label">Representante Legal:</div>
								<input type="text" id="modal_propietario_representante_legal_aa" class="filtro txt_filter_style" maxlength="50" style="width: 100%; height: 30px;">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_propietario_num_partida_registral_aa" style="display: none;">
							<div class="form-group">
								<div class="control-label">N° Partida Registral de la empresa:</div>
								<input type="text" id="modal_propietario_num_partida_registral_aa" class="filtro txt_filter_style" maxlength="50" style="width: 100%; height: 30px;">
							</div>
						</div>
						
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_propietario_persona_contacto_aa">
							<div class="form-group">
								<div class="control-label">Persona de contacto</div>
								<select class="form-control" id="modal_propietario_tipo_persona_contacto_aa">
									<option value="0">Seleccione la persona contacto</option>
									<option value="1">El propietario es la persona de contacto</option>
									<option value="2">El propietario no es la persona de contacto</option>
								</select>
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_propietario_contacto_nombre_aa" style="display: none;">
							<div class="form-group">
								<div class="control-label">Nombre de la persona de contacto:</div>
								<input type="text" id="modal_propietario_contacto_nombre_aa" name="modal_propietario_contacto_nombre" class="filtro txt_filter_style" maxlength="50" style="width: 100%; height: 30px;">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Teléfono de la persona de contacto:</div>
								<input type="number" id="modal_propietario_contacto_telefono_aa" name="modal_propietario_contacto_telefono" class="filtro mask_telefono_agente txt_filter_style" maxlength="9" style="width: 100%; height: 30px;" autocomplete="off">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Mail de la persona de contacto:</div>
								<input type="text" id="modal_propietario_contacto_email_aa" name="modal_propietario_contacto_email" class="filtro txt_filter_style" maxlength="50" style="width: 100%; height: 30px;">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_propietario_mensaje_aa" style="display: none">
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
				<button type="button" class="btn btn-success" onclick="sec_con_nuevo_resol_nuevo_propietario()">
					<i class="icon fa fa-plus"></i>
					Agregar propietario
				</button>				
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL NUEVO PROPIETARIO CA -->

<!-- INICIO MODAL NUEVO BENEFICIARIO -->
<div id="modalNuevoBeneficiario" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel" style="overflow: inherit;">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_beneficiario_titulo">Registrar Beneficiario</h4>
			</div>
			<div class="modal-body">
				<div class="row">
						<input type="hidden" id="modal_beneficiario_id_beneficiario_para_cambios">
						<input type="hidden" id="modal_beneficiario_tipo_solicitud">
					<form id="frmNuevoBeneficiario" autocomplete="off" >
						
						<input type="hidden" id="beneficiario_id_actual_adenda">
						<input type="hidden" id="beneficiario_id_adenda">
						
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Tipo de Persona:</div>
								<select 
									class="form-control select2"
									id="modal_beneficiario_tipo_persona">
									<option value="0">- Seleccione -</option>
									<?php
									$sel_query = $mysqli->query("SELECT id, nombre
									FROM cont_tipo_persona
									WHERE estado = 1
									ORDER BY id ASC;");
									while($sel=$sel_query->fetch_assoc()){?>
									<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Nombre / Razón Social del beneficiario:</div>
								<input type="text" id="modal_beneficiario_nombre" name="modal_beneficiario_nombre" class="filtro txt_filter_style" style="width: 100%; height: 30px;">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Tipo de documento de identidad:</div>
								<select 
									class="form-control select2" 
									id="modal_beneficiario_tipo_docu">
									<option value="0">- Seleccione -</option>
									<?php
									$sel_query = $mysqli->query("
									SELECT 
										id, nombre
									FROM
										cont_tipo_docu_identidad
									WHERE
										id IN (1 , 2) AND estado = 1
									ORDER BY id ASC
									");
									while($sel=$sel_query->fetch_assoc()){?>
									<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Número de Documento de Identidad:</div>
								<input type="text" id="modal_beneficiario_num_docu" name="modal_propietario_num_docu" class="filtro txt_filter_style" style="width: 100%; height: 30px;">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Tipo de forma de pago:</div>
								<select 
									class="form-control select2" 
									id="modal_beneficiario_id_forma_pago">
									<option value="0">- Seleccione -</option>
									<?php
									$sel_query = $mysqli->query("SELECT id, nombre
									FROM cont_forma_pago
									WHERE estado = 1
									ORDER BY id ASC;");
									while($sel=$sel_query->fetch_assoc()){?>
									<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_beneficiario_nombre_banco">
							<div class="form-group">
								<div class="control-label">Nombre del Banco:</div>
								<select 
									class="form-control select2"
									id="modal_beneficiario_id_banco" 
									title="Seleccione el banco">
									<option value="0">- Seleccione -</option>
									<?php
									$sel_query = $mysqli->query("SELECT id, nombre
									FROM tbl_bancos
									WHERE estado = 1
									ORDER BY id ASC;");
									while($sel=$sel_query->fetch_assoc()){?>
									<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_beneficiario_numero_cuenta_bancaria">
							<div class="form-group">
								<div class="control-label">N° de cuenta bancaria:</div>
								<input type="text" id="modal_beneficiario_num_cuenta_bancaria" class="filtro txt_filter_style" style="width: 100%; height: 30px;">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_beneficiario_numero_CCI">
							<div class="form-group">
								<div class="control-label">N° de CCI bancario:</div>
								<input type="text" id="modal_beneficiario_num_cuenta_cci" class="filtro txt_filter_style" style="width: 100%; height: 30px;">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Monto a depositar:</div>
								<select 
									class="form-control select2" 
									id="modal_beneficiario_tipo_monto">
									<option value="0">Seleccione el tipo de monto</option>
									<?php
									$sel_query = $mysqli->query("SELECT id, nombre
									FROM cont_tipo_monto_a_depositar
									WHERE status = 1
									ORDER BY id ASC;");
									while($sel=$sel_query->fetch_assoc()){?>
									<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_beneficiario_monto">
							<div class="form-group">
								<div class="control-label" id="label_beneficiario_tipo_pago">Monto:</div>
								<input type="text" id="modal_beneficiario_monto" class="filtro txt_filter_style" style="width: 100%; height: 30px;">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_beneficiario_mensaje">
							<div class="form-group">
								<div class="alert alert-danger" role="alert">
									<strong id="modal_beneficiario_mensaje"></strong>
								</div>
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_beneficiario_mensaje" style="display: none">
							<div class="form-group">
								<div class="alert alert-danger" role="alert">
									<strong id="modal_beneficiario_mensaje"></strong>
								</div>
							</div>
						</div>
					</form>
				</div> 
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
				<button type="button" class="btn btn-success" onclick="sec_con_nuevo_resol_guardar_beneficiario()">Agregar beneficiario</button>				
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL NUEVO BENEFICIARIO -->


<!-- INICIO MODAL AGREGAR INCREMENTOS -->
<div id="modal_adenda_agregar_incrementos" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_adenda_incremento_titulo">Agregar un Nuevo Incremento</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form autocomplete="off" id="frm_adenda_incremento">

						<div class="col-xs-12 col-md-12 col-lg-12">
							<table class="table table-bordered table-striped no-mb" style="font-size:12px">
								<thead>
									<th>Valor</th>
									<th>Tipo Valor</th>
									<th>Continuidad</th>
									<th id="titulo_adenda_incremento_a_partir">A partir del</th>
								</thead>
								<tbody>
									<tr>
										<td>
											<input 
												type="hidden" 
												id="contrato_adenda_incrementos_id_incremento_para_cambios" 
												name="contrato_adenda_incrementos_id_incremento_para_cambios">
											<input 
												type="text" 
												id="contrato_adenda_incrementos_monto_o_porcentaje" 
												class="filtro" 
												style="width: 60px; height: 30px; text-align: right;">
										</td>
										<td>
											<select class="form-control select2" id="contrato_adenda_incrementos_en" style="width: 100%; height: 30px;">
												<option value="0">- Seleccione -</option>
												<?php
												$sel_query = $mysqli->query("
												SELECT 
													id, 
													nombre
												FROM 
													cont_tipo_pago_incrementos
												WHERE 
													estado = 1
												ORDER BY nombre ASC
												");
												while($sel=$sel_query->fetch_assoc()){?>
												<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
												<?php
												}
												?>
											</select>
										</td>
										<td>
											<select 
												class="form-control select2" 
												id="contrato_adenda_incrementos_continuidad" 
												style="width: 100%; height: 30px;">
												<option value="0">- Seleccione -</option>
												<?php
												$sel_query = $mysqli->query("
												SELECT 
													id, 
													nombre
												FROM 
													cont_tipo_continuidad_pago
												WHERE 
													estado = 1
												ORDER BY nombre ASC
												");
												while($sel=$sel_query->fetch_assoc()){?>
												<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
												<?php
												}
												?>
											</select>
										</td>
										<td id="td_contrato_adenda_incrementos_a_partir_de_año">
											<select 
												class="form-control select2" 
												id="contrato_adenda_incrementos_a_partir_de_año" 
												style="width: 100%; height: 30px;">
												<option value="0">- Seleccione el año -</option>
												<option value="1">Primer año</option>
												<option value="2">Segundo año</option>
												<option value="3">Tercer año</option>
												<option value="4">Cuarto año</option>
												<option value="5">Quinto año</option>
												<option value="6">Sexto año</option>
												<option value="7">Septimo año</option>
												<option value="8">Octavo año</option>
											</select>
										</td>
									</tr>
								</tbody>
							</table> 
						</div>
					</form>
				</div> 
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
				<button 
					type="button" 
					class="btn btn-success" 
					id="btn_adenda_agregar_incremento" 
					onclick="sec_con_nuevo_resol_solicitud_guardar_incremento()">
					<i class="icon fa fa-plus"></i>
					<span>Agregar el incremento</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL AGREGAR INCREMENTOS -->



<!-- INICIO MODAL AGREGAR INFLACION -->
<div id="modalAgregarInflacion" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_inflacion_titulo">Registrar Inflación</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form autocomplete="off" id="frm_inflacion">
						<input type="hidden" name="modal_if_inflacion_id" id="modal_if_inflacion_id" class="form-control text-center">
						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Fecha:</div>
								<div class="input-group">
									<input name="modal_if_fecha" readonly id="modal_if_fecha" class="form-control sec_contrato_nuevo_adenda_datepicker text-center">
									<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="modal_if_fecha"></label>
								</div>
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Tipo Valor:</div>
								<select name="modal_if_tipo_periodicidad_id" id="modal_if_tipo_periodicidad_id" class="form-control select2" style="width: 100%;">
									<option value=""></option>
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-6 col-lg-6">
							<div class="form-group">
								<div class="control-label">Periodicidad del ajuste (Ejemplo: 1 año, 6 meses.):  <span class="campo_obligatorio_v2">(*)</span>:</div>
								<div class="p-1">
									<div class="col-md-6" style="padding:0px;">
										<input type="number" id="modal_if_numero" name="modal_if_numero" class="form-control">
									</div>
									<div class="col-md-6" style="padding:0px;">
										<select name="modal_if_tipo_anio_mes" id="modal_if_tipo_anio_mes" class="form-control select2" style="width: 100%;">
											<option value=""></option>
										</select>
									</div>
								</div>
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Curva:</div>
								<select name="modal_if_moneda_id" id="modal_if_moneda_id" class="form-control select2" style="width: 100%;">
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Porcentaje Añadido:</div>
								<input type="number" step="any" name="modal_if_porcentaje_anadido" id="modal_if_porcentaje_anadido" class="form-control text-right">
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Tipo de Inflación:</div>
								<input type="number"  name="modal_if_tipo_inflacion" id="modal_if_tipo_inflacion" class="form-control">
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Minimo de Inflación:</div>
								<input type="number" step="any" name="modal_if_minimo_inflacion" id="modal_if_minimo_inflacion" class="form-control text-right">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>
					</form>
				</div> 
			</div>
			<div class="modal-footer">			
				<button id="btn_modal_if_agregar_agregar" type="button" class="btn btn-success" onclick="sec_con_nuevo_resol_agregar_inflacion()" >
						<i class="icon fa fa-plus"></i>
						<span id="demo-button-text">Agregar Inflación</span>
				</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL AGREGAR INFLACION -->


<!-- INICIO MODAL AGREGAR CUOTA EXTRAORDINARIA -->
<div id="modalAgregarCuotaExtraordinaria" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_cuota_extraordinaria_titulo">Registrar Cuota Extraordinaria</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form autocomplete="off" id="frm_inflacion">
						<input type="hidden" name="modal_ce_cuota_extraordinaria_id" id="modal_ce_cuota_extraordinaria_id" class="form-control">

						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Mes:</div>
								<select name="modal_ce_mes" id="modal_ce_mes" class="form-control select2" style="width: 100%;">
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Multiplicador:</div>
								<input type="number" step="any" name="modal_ce_multiplicador" id="modal_ce_multiplicador" class="form-control">
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Meses del Prox. Pago:</div>
								<input type="number" name="modal_ce_meses_prox_pago" id="modal_ce_meses_prox_pago" class="form-control">
							</div>
						</div>


						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>
					</form>
				</div> 
			</div>
			<div class="modal-footer">
				<button id="btn_modal_ce_agregar_agregar" type="button" class="btn btn-success" onclick="sec_con_nuevo_resol_agregar_cuota_extraordinaria()" >
						<i class="icon fa fa-plus"></i>
						<span id="demo-button-text">Agregar Cuota Extraordinaria</span>
				</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL AGREGAR CUOTA EXTRAORDINARIA -->


<!-- INICIO MODAL AGREGAR CAMBIO DE CUOTA O MONEDA -->
<div id="modalAgregarTerminacionRenovacion" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_terminacion_renovacion_titulo">Registrar Terminación y Renovación</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form autocomplete="off" id="frm_inflacion">
						<input type="hidden" name="modal_tr_terminacion_renovacion_id" id="modal_tr_terminacion_renovacion_id" class="form-control">

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Tipo:</div>
								<select name="modal_tr_tipo" id="modal_tr_tipo" class="form-control select2" style="width: 100%;">
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Esta incluido en el contrato?:</div>
								<select name="modal_tr_incluido" id="modal_tr_incluido" class="form-control select2" style="width: 100%;">
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">La Ejecución depende unicamente del arrendatario?:</div>
								<select name="modal_tr_ejecucion_arrendatario" id="modal_tr_ejecucion_arrendatario" class="form-control select2" style="width: 100%;">
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">La Gerencia ejecutará la opción?:</div>
								<select name="modal_tr_gerencia_ejecutara" id="modal_tr_gerencia_ejecutara" class="form-control select2" style="width: 100%;">
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Fecha en la que gerencia decide ejecutar la opción:</div>
								<div class="input-group">
									<input readonly name="modal_tr_fecha_ejecucion_gerencia" id="modal_tr_fecha_ejecucion_gerencia" class="form-control sec_contrato_nuevo_adenda_datepicker text-center">
									<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="modal_tr_fecha_ejecucion_gerencia"></label>
								</div>
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Nueva fecha de vencimiento del contrato:</div>
								<div class="input-group">
									<input readonly name="modal_tr_nueva_fecha_vencimiento_contrato" id="modal_tr_nueva_fecha_vencimiento_contrato" class="form-control sec_contrato_nuevo_adenda_datepicker text-center">
									<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="modal_tr_nueva_fecha_vencimiento_contrato"></label>
								</div>
							</div>
						</div>



						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Importe de la penalización por ejecutar la opción:</div>
								<input type="number" step="any" name="modal_tr_importe_penalizacion" id="modal_tr_importe_penalizacion" class="form-control">
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Periodo de pago de la penalización:</div>
								<input type="text" name="modal_tr_periodo_penalizacion" id="modal_tr_periodo_penalizacion" class="form-control">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>
					</form>
				</div> 
			</div>
			<div class="modal-footer">
				<button id="btn_modal_tr_agregar_agregar" type="button" class="btn btn-success" onclick="sec_con_nuevo_resol_agregar_terminacion_renovacion()" >
						<i class="icon fa fa-plus"></i>
						<span id="demo-button-text">Agregar Terminación y Renovación</span>
				</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL AGREGAR CAMBIO DE CUOTA O MONEDA -->


