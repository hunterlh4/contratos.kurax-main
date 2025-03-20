<?php
global $mysqli;
$menu_id = "";
$area_id = $login ? $login['area_id'] : 0;

$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'contrato' AND sub_sec_id = 'solicitud' LIMIT 1");
while ($r = $result->fetch_assoc())
	$menu_id = $r["id"];
	$usuario_id = $login?$login['id']:null;

$permiso_solicitud = in_array("nuevo_contrato_interno", $usuario_permisos[$menu_id]);

if (!($area_id == 1 || $area_id == 6 || $area_id == 3  || $area_id == 2  || $area_id == 33 || $permiso_solicitud) ) {
	echo "No tienes permisos para acceder a este recurso";
}else {
	$list_emp_cont = $mysqli->query("SELECT tpg.valor FROM tbl_parametros_generales tpg WHERE tpg.codigo = 'empresa_contacto_de_contratos' AND tpg.estado = 1 LIMIT 1");
	$row_emp_cont = $list_emp_cont->fetch_assoc();
	$pref_empresa_contacto = isset($row_emp_cont['valor']) && $row_emp_cont['valor'] == 'Kurax' ? 'Kurax':'AT';
		
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
			<h1 class="page-title titulosec_contrato"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Nueva Solicitud - Adenda de Contrato Interno</h1>
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
						<input type="hidden" name="tipo_contrato_id"  id="tipo_contrato_id" value="7">

						<div class="row">
							<div class="col-xs-12 col-md-6 col-lg-6">
								<div class="form-group">
									<label for="sec_con_nuevo_empresa_grupo_at_1">Empresa Grupo <?=$pref_empresa_contacto?> 1:</label>
									<select onchange="sec_con_nuevo_aden_int_obtener_contratos()" class="form-control input_text select2" name="sec_con_nuevo_empresa_grupo_at_1" id="sec_con_nuevo_empresa_grupo_at_1" 
										title="Seleccione la empresa grupo AT 1">
									</select>
								</div>
							</div>
							<div class="col-xs-12 col-md-6 col-lg-6">
								<div class="form-group">
									<label for="sec_con_nuevo_empresa_grupo_at_2">Empresa Grupo <?=$pref_empresa_contacto?> 2:</label>
									<select onchange="sec_con_nuevo_aden_int_obtener_contratos()" class="form-control input_text select2" name="sec_con_nuevo_empresa_grupo_at_2" id="sec_con_nuevo_empresa_grupo_at_2" 
										title="Seleccione la empresa grupo AT 2">
									</select>
								</div>
							</div>

							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="form-group">
									<label for="exampleInputEmail1">Seleccione el contrato</label>
									<select onchange="sec_con_nuevo_aden_int_obtener_contratos_interno_id()" class="form-control select2" name="sec_con_nuevo_contrato_id" id="sec_con_nuevo_contrato_id">
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
														</div>



														<div class="col-md-12">
															<br>
															<button type="button" class="btn btn-success btn-xs btn-block" id="btnRegistrarAdenda" onclick="sec_con_nuevo_aden_int_guardar_adenda();">
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
				<button type="button" class="btn btn-success" id="btnModalRegistrarAdenda" onclick="sec_con_nuevo_aden_int_guardar_detalle_adenda('modalAgregar');">
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
				<h4 class="modal-title" id="modal_nuevo_proveedor_titulo_ap">Adenda - Nueva Cuenta Bancaria</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form autocomplete="off" id="frm_adenda_nuevo_proveedor">
				
						<div class="col-xs-12 col-md-4 col-lg-4" style="display:none">
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
						<div class="col-xs-12 col-md-4 col-lg-4" style="display:none">
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
				<button type="button" onclick="sec_con_nuevo_aden_int_guardar_nuevo_representante_legal()" class="btn btn-success" >
					<i class="icon fa fa-plus"></i>
					Agregar Cuenta Bancaria
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
				<button type="button" class="btn btn-success" onclick="sec_con_nuevo_aden_int_nuevo_contraprestacion()" id="btn_agregar_contraprestacion_ap">
					<i class="icon fa fa-plus"></i>
					Agregar Contraprestación
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL NUEVO PROPIETARIO AP -->

<?php 
}
?>








