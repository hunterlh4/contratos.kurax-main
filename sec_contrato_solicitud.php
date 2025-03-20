<!-- http://contratos.kurax-main.test:90/?sec_id=contrato&sub_sec_id=solicitud -->
<?php
global $mysqli;
$menu_id = "";
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'contrato' AND sub_sec_id = 'solicitud' LIMIT 1");
while ($r = $result->fetch_assoc())
	$menu_id = $r["id"];

if (!array_key_exists($menu_id, $usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])) {
	echo "No tienes permisos para acceder a este recurso";
	die;
}

$menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'contrato' AND sub_sec_id = 'solicitud' LIMIT 1")->fetch_assoc();
$menu_consultar = $menu_id_consultar["id"];
// $area_id = $login ? $login['area_id'] : 0;
$area_id = $login ? 0 : 0;
// $cargo_id = $login ? $login['cargo_id'] : 0;
$cargo_id = $login ? 0 : 0;
?>

<style>
	fieldset.dhhBorder {
		border: 1px solid #ddd;
		padding: 0 15px 5px 15px;
		margin: 0 0 15px 0;
		box-shadow: 0px 0px 0px 0px #000;
		border-radius: 5px;
	}

	legend.dhhBorder {
		font-size: 14px;
		text-align: left;
		width: auto;
		padding: 0 10px;
		border-bottom: none;
		margin-bottom: 10px;
		text-transform: capitalize;
	}

	.solicitud {
		font-weight: bold;
		font-size: 16px;
	}

	.cont_proveedor_datepicker {
		min-height: 28px !important;
	}
</style>

<div class="content container-fluid">
	<div>
		<div class="row">
			<div class="col-lg-2 col-md-3 col-sm-12 col-xs-12" style="padding: 0px 0px 15px 0px;">
				<button class="btn btn-info dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
					<i class="fa fa-plus"></i>
					Nueva Solicitud
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu contrato-dropdown-menu" id="contrato-dropdown-menu" aria-labelledby="dropdownMenu1">
				</ul>
			</div>
			<div class="col-lg-9 col-md-9 col-sm-12 col-xs-12" style="padding: 0px; float: right;">
				<div class="alert alert-success alert-dismissible fade in" role="alert" style="margin: 0px; padding: 10px 20px 10px 5px; font-size: 11px;">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close" style="right: 0px;">
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
							$ultima_modificacion = !empty($ultima_modificacion) ? date("d/m/Y H:i", strtotime($ultima_modificacion)) : '';
						}

						?>
						Sistema actualizado el <b> <?= $ultima_modificacion ?></b>. Presione Ctrl+F5 (PC) o Ctrl+Tecla Función+F5 (Laptop) en caso de fallos, o contáctese con Sistemas.
					</p>
				</div>
			</div>
		</div>

		<div class="row">
			<fieldset class="dhhBorder">

				<legend class="dhhBorder">Búsqueda</legend>
				<form autocomplete="off">
					<input type="hidden" id="menu_consultar" value="<?= $menu_consultar ?>">
					<input type="hidden" id="area_id" value="<?= $area_id ?>">
					<input type="hidden" id="cargo_id" value="<?= $cargo_id ?>">
					<input type="hidden" id="menu_id" value="<?= $menu_id ?>">
					<input type="hidden" id="sec_id" value="<?= $sec_id ?>">
					<input type="hidden" id="currentPage" value="1">

					<div class="col-lg-3 col-md-6 col-sm-12 col-xs-12" style="height: 55px;">
						<label>
							Tipo de Solicitud:
						</label>
						<select onchange="sec_contratos_solicitud_listar_solicitudes(), sec_contratos_solicitud_parametros_de_busqueda()" class="form-control form-search-tipo-contrato-id select2" id="tipo_contrato_id">
							<?php
							$query = "SELECT * FROM cont_tipo_contrato WHERE status = 1";

							if (!(array_key_exists($menu_consultar, $usuario_permisos) && in_array("see_all", $usuario_permisos[$menu_consultar])) || true) {
								$query .= " AND id IN('2','4','5','9','11'";

								if ((array_key_exists($menu_consultar, $usuario_permisos) && in_array("nuevo_contrato_arrendamiento", $usuario_permisos[$menu_consultar]))) {
									$query .= ",'1','3','12'";
								}
								if ((array_key_exists($menu_consultar, $usuario_permisos) && in_array("nuevo_contrato_locacion", $usuario_permisos[$menu_consultar]))) {
									$query .= ",'13','16'";
								}
								if ((array_key_exists($menu_consultar, $usuario_permisos) && in_array("nuevo_contrato_mandato", $usuario_permisos[$menu_consultar]))) {
									$query .= ",'14','17'";
								}
								if ((array_key_exists($menu_consultar, $usuario_permisos) && in_array("nuevo_contrato_mutuodinero", $usuario_permisos[$menu_consultar]))) {
									$query .= ",'15','18'";
								}

								$query .= " )";
							}

							$query .= " ORDER BY num_orden";

							$list_query = $mysqli->query($query);
							$list = [];
							while ($li = $list_query->fetch_assoc()) {
							?>
								<option value="<?php echo $li["id"]; ?>"><?php echo $li["nombre"]; ?></option>
							<?php
							}

							?>
						</select>
						<?php

						?>
					</div>

					<div class="form-search-empresa col-lg-3 col-md-6 col-sm-12 col-xs-12" style="height: 55px;">
						<label>
							Empresa:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<select class="form-control select2" name="cont_proveedor_param_empresa" id="cont_proveedor_param_empresa">
									<option value="">-- TODOS --</option>
									<?php
									$sel_query = $mysqli->query(
										"
										SELECT 
											id, nombre
										FROM
											tbl_razon_social
										WHERE status = 1
										ORDER BY nombre ASC
										"
									);

									while ($sel = $sel_query->fetch_assoc()) {
									?>
										<option value="<?php echo $sel["id"]; ?>"><?php echo $sel["nombre"]; ?></option>
									<?php
									}
									?>
								</select>
								<a class="btn btn-xs btn-default limpiar_select2" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="cont_proveedor_param_empresa" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>

					<div class="form-search-area col-lg-3 col-md-6 col-sm-12 col-xs-12" style="height: 55px;">
						<label>
							Área Solicitante:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<select class="form-control input_text select2" data-live-search="true" name="cont_proveedor_param_area_solicitante" id="cont_proveedor_param_area_solicitante">

								</select>
								<a class="btn btn-xs btn-default limpiar_select2" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="cont_proveedor_param_area_solicitante" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>

					<div class="form-search-ruc-proveedor col-lg-3 col-md-6 col-sm-12 col-xs-12" style="height: 55px;">
						<label>
							RUC del Proveedor:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<input type="text" name="cont_proveedor_param_ruc" class="form-control cont_proveedor_param_ruc" id="cont_proveedor_param_ruc" placeholder="RUC" maxlength="11" style="border: 1px solid #aaa; border-radius: 1px;">
								<a class="btn btn-xs btn-default limpiar_input" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="cont_proveedor_param_ruc" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>

					<div class="form-search-razon-social col-lg-3 col-md-6 col-sm-12 col-xs-12" style="height: 55px;">
						<label>
							Razón Social del Proveedor:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<input type="text" name="cont_proveedor_param_razon_social" class="form-control cont_proveedor_param_razon_social" id="cont_proveedor_param_razon_social" placeholder="Razón Social" style="border: 1px solid #aaa; border-radius: 1px;">
								<a class="btn btn-xs btn-default limpiar_input" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="cont_proveedor_param_razon_social" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>

					<div class="form-search-moneda col-lg-3 col-md-6 col-sm-12 col-xs-12">
						<label>
							Moneda:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<select class="form-control input_text select2" data-live-search="true" name="cont_proveedor_param_moneda" id="cont_proveedor_param_moneda">
									<option value="">-- TODOS --</option>
									<?php
									$sel_query = $mysqli->query(
										"
											SELECT 
												id, CONCAT(nombre,' (',simbolo,')') AS nombre
											FROM tbl_moneda
											WHERE estado = 1 AND id IN(1,2)
											ORDER BY id ASC
										"
									);

									while ($sel = $sel_query->fetch_assoc()) {
									?>
										<option value="<?php echo $sel["id"]; ?>"><?php echo $sel["nombre"]; ?></option>
									<?php
									}
									?>
								</select>
								<a class="btn btn-xs btn-default limpiar_select2" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="cont_proveedor_param_moneda" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>

					<div style="display: none;">
						<div class="form-search-departamento col-lg-3 col-md-6 col-sm-12 col-xs-12" style="height: 55px; display: none;">
							<div class="form-group">
								<label>
									DepartamentOo:
								</label>
								<div class="form-group">
									<div class="input-group col-xs-12">
										<select class="form-control input_text select2" data-live-search="true" onchange="sec_contratos_solicitud_obtener_provincias()" name="search_id_departamento" id="search_id_departamento">
											<option value="">-- TODOS --</option>
										</select>
										<a class="btn btn-xs btn-default limpiar_select2" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="search_id_departamento" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
									</div>
								</div>
							</div>
						</div>
						<div class="form-search-provincia col-lg-3 col-md-6 col-sm-12 col-xs-12" style="height: 55px; display: none;">
							<div class="form-group">
								<label>
									Provincia:
								</label>
								<div class="form-group">
									<div class="input-group col-xs-12">
										<select class="form-control input_text select2" data-live-search="true" onchange="sec_contratos_solicitud_obtener_distritos()" name="search_id_provincia" id="search_id_provincia">
											<option value="">-- TODOS --</option>
										</select>
										<a class="btn btn-xs btn-default limpiar_select2" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="search_id_provincia" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
									</div>
								</div>
							</div>
						</div>
						<div class="form-search-distrito col-lg-3 col-md-6 col-sm-12 col-xs-12" style="height: 55px; display: none;">
							<div class="form-group">
								<label>
									Distrito:
								</label>
								<div class="form-group">
									<div class="input-group col-xs-12">
										<select class="form-control input_text select2" data-live-search="true" name="search_id_distrito" id="search_id_distrito">
											<option value="">-- TODOS --</option>
										</select>
										<a class="btn btn-xs btn-default limpiar_select2" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="search_id_distrito" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
									</div>
								</div>
							</div>
						</div>
					</div>


					<div class="col-lg-3 col-md-6 col-sm-12 col-xs-12" style="height: 55px;">
						<label>
							F. Solicitud desde:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<input type="text" class="form-control sec_contrato_solicitud_datepicker" id="search_fecha_inicio" value="" readonly="readonly" style="border: 1px solid #aaa; border-radius: 1px; min-height: 28px !important;">
								<a class="btn btn-xs btn-default" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" onclick="document.getElementById('search_fecha_inicio').click();"><i class="icon fa fa-calendar"></i></a>
								<a class="btn btn-xs btn-default limpiar_input" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="search_fecha_inicio" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>

					<div class="col-lg-3 col-md-6 col-sm-12 col-xs-12" style="height: 55px;">
						<label>
							F. Solicitud hasta:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<input type="text" class="form-control sec_contrato_solicitud_datepicker" id="search_fecha_fin" value="" readonly="readonly" style="border: 1px solid #aaa; border-radius: 1px; min-height: 28px !important;">
								<a class="btn btn-xs btn-default" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" onclick="document.getElementById('search_fecha_fin').click();"><i class="icon fa fa-calendar"></i></a>
								<a class="btn btn-xs btn-default limpiar_input" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="search_fecha_fin" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>

					<div class="form-search-nombre-de-tienda col-lg-3 col-md-6 col-sm-12 col-xs-12" style="height: 55px; display: none;">
						<label>
							Nombre de la Tienda:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<input type="text" class="form-control" id="nombre_tienda" style="border: 1px solid #aaa; border-radius: 1px;">
								<a class="btn btn-xs btn-default limpiar_input" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="nombre_tienda" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>

					<div class="form-search-nombre-del-agente col-lg-3 col-md-6 col-sm-12 col-xs-12" style="height: 55px;">
						<label>
							Nombre del Agente:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<input type="text" class="form-control" id="nombre_agente" style="border: 1px solid #aaa; border-radius: 1px;">
								<a class="btn btn-xs btn-default limpiar_input" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="nombre_agente" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>

					<?php
					if ($area_id == '33') {
					?>
						<div class="form-estado-solicitud col-lg-3 col-md-3 col-sm-3 col-xs-12" style="height: 55px;">
							<?php
							$query = "SELECT * FROM cont_estado_solicitud WHERE status = 1";
							?>
							<label>
								Estado:
							</label>
							<div class="form-group">
								<div class="input-group col-xs-12">
									<select id="sec_sol_estado_solicitud" class="form-control select2">
										<option value="">-- TODOS --</option>
										<?php
										$list_query = $mysqli->query($query);

										while ($li = $list_query->fetch_assoc()) {
										?>
											<option value="<?= $li["id"]; ?>"><?= $li["nombre"]; ?></option>
										<?php
										}
										?>
										<!-- <option value="99">Cancelado</option> -->
									</select>
								</div>
							</div>
						</div>

						<div class="form-estado-solicitud-v2 col-lg-3 col-md-3 col-sm-3 col-xs-12" style="display:none;">
							<input type="hidden" id="sec_sol_estado_solicitud_v2" name="sec_sol_estado_solicitud_v2">
						</div>

					<?php
					} else {
					?>

						<div class="form-estado-solicitud-v3 col-lg-3 col-md-3 col-sm-3 col-xs-12" style="display:none;">
							<input type="hidden" id="sec_sol_estado_solicitud" name="sec_sol_estado_solicitud">
						</div>

						<div style="display: none;">
							<div class="form-estado-solicitud col-lg-3 col-md-3 col-sm-3 col-xs-12" style="height: 55px;;">
								<label>
									Estado de la Solicitud:
								</label>
								<div class="form-group">
									<div class="input-group col-xs-12">
										<select id="sec_sol_estado_solicitud_v2" class="form-control select2">
											<option value="">-- TODOS --</option>
											<option value="1">En proceso</option>
											<option value="2">Cancelado</option>
										</select>
										<a class="btn btn-xs btn-default limpiar_select2" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="sec_sol_estado_solicitud_v2" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
									</div>
								</div>
							</div>
						</div>

					<?php
					}
					?>

					<div class="form-search-aprobante col-lg-3 col-md-3 col-sm-3 col-xs-12" style="height: 55px;">
						<label>
							Aprobante:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<select class="form-control select2" name="director_aprobacion_id" id="director_aprobacion_id" title="Seleccione a el director">
								</select>
								<a class="btn btn-xs btn-default limpiar_select2" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="director_aprobacion_id" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>

					<div class="form-search-estado_aprobacion col-lg-3 col-md-3 col-sm-3 col-xs-12" style="height: 55px;">
						<label>
							Estado de la Aprobación:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<select id="sec_sol_estado_aprobacion" class="form-control select2">
									<option value="">-- TODOS --</option>
									<option value="1">Aprobado</option>
									<option value="2">Rechazado</option>
									<option value="3">Pendiente</option>
								</select>
								<a class="btn btn-xs btn-default limpiar_select2" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="sec_sol_estado_aprobacion" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>

					<div class="form-search-fecha_aprobacion_desde col-lg-3 col-md-6 col-sm-12 col-xs-12" style="height: 55px;">
						<label>
							F. Aprobación desde:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<input type="text" class="form-control sec_contrato_solicitud_datepicker" id="search_fecha_inicio_aprobacion" value="" readonly="readonly" style="border: 1px solid #aaa; border-radius: 1px; min-height: 28px !important;">
								<a class="btn btn-xs btn-default" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" onclick="document.getElementById('search_fecha_inicio_aprobacion').click();"><i class="icon fa fa-calendar"></i></a>
								<a class="btn btn-xs btn-default limpiar_input" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="search_fecha_inicio_aprobacion" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>

					<div class="form-search-fecha_aprobacion_hasta col-lg-3 col-md-6 col-sm-12 col-xs-12" style="height: 55px;">
						<label>
							F. Aprobación hasta:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<input type="text" class="form-control sec_contrato_solicitud_datepicker" id="search_fecha_fin_aprobacion" value="" readonly="readonly" style="border: 1px solid #aaa; border-radius: 1px; min-height: 28px !important;">
								<a class="btn btn-xs btn-default" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" onclick="document.getElementById('search_fecha_fin_aprobacion').click();"><i class="icon fa fa-calendar"></i></a>
								<a class="btn btn-xs btn-default limpiar_input" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="search_fecha_fin_aprobacion" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>

					<div class="form-tipo-solicitud col-lg-3 col-md-3 col-sm-3 col-xs-12" style="height: 55px;">
						<?php
						$query = "SELECT * FROM cont_tipo_contrato WHERE status = 1 AND id IN (1,2,5,6,7) ORDER BY id ASC";
						?>
						<label>
							Tipo de Contrato:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<select id="sec_sol_tipo_contrato" class="form-control select2">
									<option value="">-- TODOS --</option>
									<?php
									$list_query = $mysqli->query($query);

									while ($li = $list_query->fetch_assoc()) {
									?>
										<option value="<?= $li["id"]; ?>"><?= $li["nombre"]; ?></option>
									<?php
									}
									?>
								</select>
								<a class="btn btn-xs btn-default limpiar_select2" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="sec_sol_tipo_contrato" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>

					<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 text-right" style="margin-top: 15px; margin-bottom: 15px; float: right;">
						<button type="button" class="btn btn-warning float-left" id="btn_limpiar_filtros_de_busqueda">
							<i class="fa fa-eraser"></i>
							Limpiar filtros
						</button>
						<span id="cont_contrato_excel" class="float-left" style="padding: inherit;">
							<a class="btn btn-success export_list_btn"><span class="fa fa-file-excel-o"></span> Exportar excel</a>
						</span>
						<button type="button" class="btn btn-primary float-left" id="cont_proveedor_btn_buscar" onclick="sec_contratos_solicitud_listar_solicitudes();">
							<i class="glyphicon glyphicon-search"></i>
							Buscar
						</button>
					</div>


					<div class="col-md-12" id="cont_locales_alerta_filtrar_por">

					</div>
				</form>
			</fieldset>
		</div>
	</div>
</div>

<div class="col-md-12" id="block-resultado-tabla" style="display: none;">
</div>

<textarea style="display:none" id="usuario_permisos" cols="30" rows="10"><?php echo json_encode($usuario_permisos); ?></textarea>

<!-- INICIO MODAL CANCELAR SOLICITUD -->
<div id="modal_cancelar_solicitud" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_cancelar_solicitud_titulo">Cancelar Solicitud</h4>
			</div>
			<div class="modal-body">
				<form id="form_cancelar_solicitud" name="form_cancelar_solicitud" enctype="multipart/form-data" autocomplete="off">
					<div class="row">

						<input type="hidden" id="tipo_de_solicitud" name="tipo_de_solicitud">
						<input type="hidden" id="solicitud_id_temporal" name="solicitud_id_temporal">

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="form-group">
								<div class="control-label" id="label_subtotal">Motivo <span class="campo_obligatorio_v2">(*)</span>:</div>
								<textarea name="cancelado_motivo" id="cancelado_motivo" class="filtro" style="width: 100%;"></textarea>
							</div>
						</div>

					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">
					Desistir
				</button>
				<button type="button" class="btn btn-danger" onclick="sec_contrato_solicitud_cancelar_solicitud()">
					<i class="icon fa fa-close"></i>
					<span id="modal_cancelar_solicitud_titulo_boton">Cancelar Solicitud</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL CANCELAR SOLICITUD -->