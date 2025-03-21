<?php
global $mysqli;
$menu_id = "";
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'contrato' AND sub_sec_id = 'nuevo' LIMIT 1");
while ($r = $result->fetch_assoc())
	$menu_id = $r["id"];
$usuario_id = $login ? $login['id'] : null;

$list_emp_cont = $mysqli->query("SELECT tpg.valor FROM tbl_parametros_generales tpg WHERE tpg.codigo = 'empresa_contacto_de_contratos' AND tpg.estado = 1 LIMIT 1");
$row_emp_cont = $list_emp_cont->fetch_assoc();
$valor_empresa_contacto = isset($row_emp_cont['valor']) && $row_emp_cont['valor'] == 'Kurax' ? '(Kurax)' : '(AT)';
?>
<style>
	.campo_obligatorio {
		font-size: 15px;
		color: red;
	}

	.campo_obligatorio_v2 {
		font-size: 13px;
		color: red;
	}
</style>


<div id="div_sec_contrato_nuevo">

	<div id="loader_"></div>

	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-12">
			<div class="alert alert-success alert-dismissible fade in" role="alert" style="margin: 0px;">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
				<p class="text-center">
					<?php
					$ultima_modificacion = "";
					$query_updated = $mysqli->query("SELECT updated_at FROM tbl_modificaciones WHERE status = 1 AND modulo = 'Contratos' ORDER BY updated_at DESC LIMIT 1");
					while ($sel2 = $query_updated->fetch_assoc()) {
						$ultima_modificacion = $sel2['updated_at'];
					}
					if (!empty($ultima_modificacion)) {
						$ultima_modificacion = !empty($ultima_modificacion) ? date("d/m/Y H:i A", strtotime($ultima_modificacion)) : '';
					}

					?>
					El sistema ha sido actualizado el <b> <?= $ultima_modificacion ?> </b> En caso de identificar un mal funcionamiento presionar:
					<b>Ctrl+F5</b> (Si estás en PC) o <b>Ctrl+Tecla Función +F5</b> (Si estás en laptop) o contactar con el área de sistemas.
				</p>
			</div>
		</div>
		<div class="col-xs-12 col-sm-12 col-md-12 text-center">
			<h1 class="page-title titulosec_contrato"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Nueva Solicitud - Acuerdo de Confidencialidad</h1>
		</div>
	</div>

	<div class="row">
		<div class="col-xs-12 col-md-12 col-lg-12">
			<!-- PANEL: Tipo contrato -->
			<div class="panel">
				<div class="panel-heading">
					<div class="panel-title">Nueva Solicitud <span class="campo_obligatorio_v2">(*) <small>Campos Obligatorios</small></span></div>
				</div>
				<div class="panel-body">
					<form id="form_contrato_acuerdo_confidencialidad" name="form_contrato_acuerdo_confidencialidad" method="POST" enctype="multipart/form-data" autocomplete="off">
						<input type="hidden" name="usuario_id_temporal" id="usuario_id_temporal" value="<?php echo $usuario_id; ?>">
						<input type="hidden" name="tipo_contrato_id" id="tipo_contrato_id" value="5">

						<div class="row">
							<div class="col-xs-12 col-md-4 col-lg-4">
								<div class="form-group">
									<label for="sec_con_nuevo_empresa_susbribe">Empresa que suscribe el contrato <span class="campo_obligatorio_v2">(*)</span>:</label>
									<select class="form-control input_text select2" name="sec_con_nuevo_empresa_susbribe" id="sec_con_nuevo_empresa_susbribe"
										title="Seleccione la empresa grupo AT 1">
									</select>
								</div>
							</div>
						</div>

						<div class="row">
							<br>
						</div>

						<div class="row">
							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="h4"><b>DATOS DE CONTACTO</b></div>
							</div>

							<div class="col-xs-12 col-md-3 col-lg-3">
								<div class="form-group">
									<div class="control-label">Persona contacto <?= $valor_empresa_contacto ?> <span class="campo_obligatorio_v2">(*)</span>:</div>
									<input type="text" name="sec_con_nuevo_persona_contacto_proveedor" id="sec_con_nuevo_persona_contacto_proveedor" maxlength=100 class="filtro"
										style="width: 100%; height: 28px;">
								</div>
							</div>

							<div class="col-xs-12 col-md-3 col-lg-3">
								<div class="form-group">
									<div class="control-label">Cargo Persona contacto <?= $valor_empresa_contacto ?> <span class="campo_obligatorio_v2">(*)</span>:</div>
									<select
										class="form-control input_text select2"
										name="cargo_id_persona_contacto"
										id="cargo_id_persona_contacto"
										title="Seleccione a el director">
									</select>
								</div>
							</div>

							<div class="col-xs-12 col-md-3 col-lg-3">
								<div class="form-group">

									<?php
									$campo_aprobacion_tooltip = '';
									$aprobacion_obligatoria_id = 1;
									$campo_aprobacion_mensaje = '<span class="campo_obligatorio_v2">(*)</span>';
									$query_directores = "SELECT user_id FROM cont_usuarios_directores WHERE status = 1";
									$sel_query = $mysqli->query($query_directores);
									while ($sel = $sel_query->fetch_assoc()) {
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
											onchange="sec_con_nuevo_acu_conf_select_cargo('aprobador')"
											name="director_aprobacion_id"
											id="director_aprobacion_id"
											title="Seleccione a el director">
										</select>
									</div>

								</div>
							</div>

							<div class="col-xs-12 col-md-3 col-lg-3">
								<div class="form-group">
									<div class="control-label">Cargo del Aprobador:</div>
									<select
										class="form-control input_text select2"
										name="cargo_id_aprobante"
										id="cargo_id_aprobante"
										title="Seleccione a el director">
									</select>
								</div>
							</div>



							<div class="col-xs-12 col-md-3 col-lg-3">
								<div class="form-group">

									<div class="control-label">
										Responsable de Área
										<span class="campo_obligatorio_v2">(*)</span>:
									</div>

									<select
										class="form-control input_text select2"
										name="gerente_area_id"
										id="gerente_area_id"
										onchange="sec_con_nuevo_acu_conf_select_cargo('responsable')"
										title="Seleccione el gerente">
									</select>

									<span>
										<i class="fa fa-arrow-up"></i>
										<b> Nota: Si no encuentra al Gerente, seleccione la opción "Otro".</b>
									</span>
								</div>
							</div>

							<div class="col-xs-12 col-md-3 col-lg-3" id="div_gerencia_area_nombre_gerente" style="display: none;">
								<div class="form-group">
									<div class="control-label">Nombre del Responsable de Área <span class="campo_obligatorio_v2">(*)</span>:</div>
									<input type="text" name="nombre_del_gerente_del_area" id="nombre_del_gerente_del_area" maxlength=150 class="filtro form-control">
								</div>
							</div>

							<div class="col-xs-12 col-md-3 col-lg-3" id="div_gerencia_area_email_gerente" style="display: none;">
								<div class="form-group">
									<div class="control-label">Email del Responsable de Área <span class="campo_obligatorio_v2">(*)</span>:</div>
									<input type="text" name="email_del_gerente_del_area" id="email_del_gerente_del_area" maxlength=150 class="filtro form-control">
								</div>
							</div>

							<div class="col-xs-12 col-md-3 col-lg-3">
								<div class="form-group">
									<div class="control-label">Cargo del Responsable <span class="campo_obligatorio_v2">(*)</span>:</div>
									<select
										class="form-control input_text select2"
										name="cargo_id_responsable"
										id="cargo_id_responsable"
										title="Seleccione a el director">
									</select>
								</div>
							</div>

							<div class="col-xs-12 col-md-3 col-lg-3">
								<div class="form-group">
									<div class="control-label">Correos Adjuntos: (Opcional)</div>
									<textarea style="width: 100%;" rows="2" name="correos_adjuntos"></textarea>
									<p>
										<i class="fa fa-arrow-up"></i>
										<b>Nota: Para más de un correo se debe separar por comas (,)</b>
									</p>
								</div>
							</div>

							<div class="col-xs-12 col-md-12 col-lg-12">
								<br>
							</div>

							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="h4"><b>INFORMACIÓN DEL PROVEEDOR</b></div>
							</div>

							<!--RUC PROVEEDOR-->
							<div class="col-xs-12 col-md-4 col-lg-4">
								<div class="form-group">
									<div class="control-label">N° de RUC del Proveedor <span class="campo_obligatorio_v2">(*)</span>:</div>
									<input type="text"
										id="sec_con_nuevo_ruc"
										name="sec_con_nuevo_ruc"
										oninput="this.value=this.value.replace(/[^0-9]/g,'');"
										maxlength=11
										class="filtro"
										style="width: 100%; height: 30px;">
								</div>
							</div>
							<!--RAZON SOCIAL PROVEEDOR-->
							<div class="col-xs-12 col-md-4 col-lg-4">
								<div class="form-group">
									<div class="control-label">Razón Social del Proveedor <span class="campo_obligatorio_v2">(*)</span>:</div>
									<input type="text" id="sec_con_nuevo_razon_social" name="sec_con_nuevo_razon_social" maxlength=50 class="filtro"
										style="width: 100%; height: 30px;">
								</div>
							</div>

							<div class="col-xs-12 col-md-4 col-lg-4">
								<div class="form-group">
									<div class="control-label">Nombre Comercial Proveedor <span class="campo_obligatorio_v2">(*)</span>:</div>
									<input type="text" id="sec_con_nuevo_nombre_comercial" name="sec_con_nuevo_nombre_comercial" maxlength=60 class="filtro"
										style="width: 100%; height: 30px;">
								</div>
							</div>

						</div>

						<div class="row">
							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="h4">
									<b>INFORMACIÓN DEL PROVEEDOR - REPRESENTANTES LEGALES</b>
									<input type="hidden" id="id_registro_proveedor_temporal">
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-xs-12 col-md-4 col-lg-6">
								<div class="form-group">
									<div class="control-label">DNI del Representante Legal <span class="campo_obligatorio_v2">(*)</span>:</div>
									<input type="text" name="sec_con_nuevo_dni_representante" id="sec_con_nuevo_dni_representante"
										maxlength=8
										class="filtro"
										style="width: 100%; height: 30px;"
										oninput="this.value=this.value.replace(/[^0-9]/g,'');">
								</div>
							</div>

							<!--NOMBRE COMPLETO REPRESENTANTE LEGAL-->
							<div class="col-xs-12 col-md-4 col-lg-6">
								<div class="form-group">
									<div class="control-label">Nombre Completo del Representante Legal <span class="campo_obligatorio_v2">(*)</span>:</div>
									<input type="text" name="sec_con_nuevo_nombre_representante" id="sec_con_nuevo_nombre_representante" maxlength=50 class="filtro"
										style="width: 100%; height: 30px;">
								</div>
							</div>

							<!--NRO CUENTA DE DETRACCIÓN-->
							<div class="col-xs-12 col-md-4 col-lg-4" style="display:none">
								<div class="form-group">
									<div class="control-label">Nro. Cuenta de Detracción (Banco de la Nación): </div>
									<input type="text" id="sec_con_nuevo_nro_cuenta_detraccion" name="sec_con_nuevo_nro_cuenta_detraccion" maxlength="50"
										style="width: 100%; height: 30px;" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
								</div>
							</div>

							<!--BANCO-->
							<div class="col-xs-12 col-md-4 col-lg-4" style="display:none">
								<div class="form-group">
									<div class="control-label">Banco : </div>
									<select class="form-control input_text select2" data-live-search="true"
										name="sec_con_nuevo_banco" id="sec_con_nuevo_banco" title="Seleccione el banco">
									</select>
								</div>
							</div>

							<!--NRO CUENTA-->
							<div class="col-xs-12 col-md-4 col-lg-4" style="display:none">
								<div class="form-group">
									<div class="control-label">Nro. de Cuenta : </div>
									<input type="text" id="sec_con_nuevo_nro_cuenta" name="sec_con_nuevo_nro_cuenta" maxlength="50"
										style="width: 100%; height: 30px;" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
								</div>
							</div>

							<!--NRO CCI-->
							<div class="col-xs-12 col-md-4 col-lg-4" style="display:none">
								<div class="form-group">
									<div class="control-label">Nro. de CCI : </div>
									<input type="text" id="sec_con_nuevo_nro_cci" name="sec_con_nuevo_nro_cci" maxlength="50"
										style="width: 100%; height: 30px;" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
								</div>
							</div>

						</div>

						<div class="col-xs-12 col-md-12 col-lg-12" id="div_sec_con_nuevo_nuevo_proveedor" style="margin-top: 10px;">
							<button type="button" class="btn btn-sm btn-info" onclick="sec_con_nuevo_acu_conf_agregar_proveedor();">
								<i class="fa fa-plus" style="margin-right: 10px;"></i> Agregar Representante Legal
							</button>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12" id="div_sec_con_nuevo_editar_proveedor" style="text-align: center; display: none;">
							<div class="form-group">
								<button type="button" class="btn btn-sm btn-success" onclick="sec_con_nuevo_acu_conf_modificar_proveedor();"><i class="fa fa-save"></i> Guardar</button>
								<button type="button" class="btn btn-sm btn-danger" onclick="sec_con_nuevo_acu_conf_cancelar_proveedor();"><i class="fa fa-close"></i> Cancelar</button>
							</div>
						</div>
						<!--TABLA-->
						<div class="row">
							<div class="col-md-12 table-responsive">
								<table class="table" id="sec_con_nuevo_tabla_proveedores">
									<thead>
										<th>Dni</th>
										<th>Nombres Completos</th>
										<th>Vigencia de Poder</th>
										<th>DNI</th>
										<th colspan="2" style="width:5%">Acciones</th>
									</thead>
									<tbody>

									</tbody>
								</table>
							</div>
						</div>


						<div class="row">
							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="h4">
									<b>CONDICIONES COMERCIALES</b>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="h4">1) Objeto del Contrato:</div>
							</div>

							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="form-group">
									<div class="control-label">Detalle de servicio a contratar. Detallar si existirán entregables, forma y plazo <span class="campo_obligatorio_v2">(*)</span>:</div>
									<textarea name="sec_con_nuevo_detalle_servicio" id="sec_con_nuevo_detalle_servicio" rows="5" cols="50" style="width: 100%;"></textarea>
								</div>
							</div>

							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="h4">2) Plazo del Contrato:</div>
							</div>


							<div class="col-xs-12 col-md-4 col-lg-4">
								<div class="form-group">
									<div class="control-label">Fecha de Inicio <span class="campo_obligatorio_v2">(*)</span>:</div>
									<div class="input-group">
										<input type="text" name="sec_con_nuevo_fecha_inicio" id="sec_con_nuevo_fecha_inicio" class="form-control fecha_datepicker_ac"
											value="<?php echo date("Y-m-d", strtotime("-1 days")); ?>"
											readonly="readonly" style="height: 30px;">
										<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="sec_con_nuevo_fecha_inicio"></label>
									</div>

								</div>
							</div>
						</div>

						<div class="row">
							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="h4">4) Observaciones:</div>
							</div>

							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="form-group">
									<textarea name="sec_con_nuevo_observaciones" id="sec_con_nuevo_observaciones" rows="5" cols="50"
										style="width: 100%;"></textarea>
								</div>
							</div>
						</div>



						<div class="row">

							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="h4"><b>ANEXOS</b></div>
							</div>

							<div class="col-xs-12 col-md-4 col-lg-4">
								<div class="form-group">
									<div class="control-label">Ficha RUC de la empresa proveedora:</div>
									<input type="file" name="archivo_ficha_ruc" accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip">
								</div>
							</div>

							<div class="col-xs-12 col-md-4 col-lg-4">
								<div class="form-group">
									<div class="control-label">Formato de contrato:</div>
									<input type="file" name="archivo_formato_contrato" accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip">
								</div>
							</div>
							<div class="col-xs-12 col-md-4 col-lg-4">
								<div class="form-group">
									<div class="control-label">Know your client (KYC):</div>
									<input type="file" name="archivo_know_your_client" accept=".jpg, .jpeg, .png, .pdf, .doc, .docx">
								</div>
							</div>

							<div class="col-xs-12 col-md-12 col-lg-12">
								<br>
							</div>

							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="h4"><b>OTROS ANEXOS</b></div>
							</div>

							<div class="col-xs-12 col-md-4 col-lg-4">
								<div class="form-group">
									<!--sec_nuevo_abrir_modal_nuevos_anexos-->
									<button type="button" class="btn btn-info btn-sm" id="sec_nuevo_btn_agregar_anexos_proveedores" onclick="sec_con_nuevo_acu_conf_abrir_modal_tipos_anexos()">
										<i class="icon fa fa-plus"></i>
										Agregar anexos
									</button>
								</div>
							</div>

							<div class="col-md-12" id="sec_con_nuevo_nuevos_anexos_listado">
								<input id="fileToUploadAnexo" accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip" type="file" multiple="" style="display:none">
							</div>

							<div class="col-xs-12 col-md-12 col-lg-12">
								<br>
							</div>
						</div>

						<div class="row">
							<div class="col-xs-12 col-md-12 col-lg-12">
								<button type="submit" class="btn btn-success btn-block" id="guardar_contrato_proveedor">
									<i class="icon fa fa-save" id="demo-button-icon-left" style="display: inline-block;"></i>
									<span id="demo-button-text">Solicitar Acuerdo de Confidencialidad</span>
								</button>
							</div>
						</div>
					</form>
				</div>
			</div>
			<!-- /PANEL: Tipo contrato -->
		</div>
	</div>
</div>


<!-- INICIO MODAL TIPOS ANEXOS -->
<div id="modaltiposanexos" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-sm" role="document" style="width: 30%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Selecciona tipo de Anexo</h4>
			</div>
			<div class="modal-body">
				<form id="sec_nuevo_form_modal_nuevo_anexo" name="sec_nuevo_form_modal_nuevo_anexo" method="POST" enctype="multipart/form-data" autocomplete="off">
					<div class="row">
						<div class="col-md-8">
							<input type="hidden" name="modal_nuevo_anexo_tipo_contrato_id" id="modal_nuevo_anexo_tipo_contrato_id">
							<select class="form-control input_text select2 col-5" name="modal_nuevo_anexo_select_tipos" id="modal_nuevo_anexo_select_tipos" title="Seleccione el tipo de anexo">
							</select>
						</div>
						<div class="col-md-4">
							<button type="button" class=" col-5 btn btn-sm btn-info" id="sec_con_nuevo_agregar_tipo_anexo" onclick="sec_con_nuevo_acu_conf_agregar_nuevo_tipo_archivo()">
								<i class="icon fa fa-plus"></i>
								<span>Agregar Tipo</span>
							</button>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" class="btn btn-success" data-dismiss="modal"><i class="icon fa fa-close"></i>Cancelar</button>
				<button type="button" class="btn btn-success" id="sec_nuevo_modal_guardar_archivo" onclick="sec_con_nuevo_acu_conf_anadirArchivo()">
					<i class="icon fa fa-save"></i><span>Elegir Tipo</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL TIPOS ANEXOS -->

<!-- INICIO MODAL AGREGAR TIPO ANEXO -->
<div id="sec_con_nuevo_agregar_nuevo_tipo_archivo" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-sm" role="document" style="width: 30%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Agregar nuevo tipo de Anexo</h4>
			</div>
			<div class="modal-body">
				<form id="sec_con_nuevo_agregar_tipo_anexo_form" name="sec_con_nuevo_agregar_tipo_anexo_form" method="POST" enctype="multipart/form-data" autocomplete="off">
					<div class="row">
						<div class="col-md-10">
							<input type="text" name="sec_con_nuevo_tipo_anexo_nombre" id="sec_con_nuevo_tipo_anexo_nombre" class="form-control" placeholder="Nombre del tipo de anexo" />
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" class="btn btn-success" data-dismiss="modal"><i class="icon fa fa-close"></i> Cancelar</button>
				<button type="button" class="btn btn-success" id="sec_nuevo_modal_guardar_archivo" onclick="sec_con_nuevo_acu_conf_guardarNuevoTipoAnexo()">
					<i class="icon fa fa-save"></i>
					<span>Guardar nuevo tipo de anexo</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL AGREGAR TIPO ANEXO -->