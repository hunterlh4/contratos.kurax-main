<?php
global $mysqli;
$menu_id = "";
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'contrato' AND sub_sec_id = 'locales' LIMIT 1");
while ($r = $result->fetch_assoc())
	$menu_id = $r["id"];

if (!array_key_exists($menu_id, $usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])) {
	echo "No tienes permisos para acceder a este recurso";
	die;
}

$area_id = $login ? $login['area_id'] : 0;
$user_id = $login ? $login['id'] : null;

$select_campo_busqueda = array(
	array("id" => "0", "Nombre" => "Todos"),
	array("id" => "fecha_suscripcion", "Nombre" => "Fecha suscripción contrato"),
	array("id" => "fecha_solicitud", "Nombre" => "Fecha solicitud contrato"),
	array("id" => "fecha_inicio", "Nombre" => "Fecha inicio contrato"),
	array("id" => "fecha_fin", "Nombre" => "Fecha fin contrato")
);


?>

<div class="content container-fluid contratos_form_etapas">
	<div class="page-header wide">
		<div class="row">
			<div class="col-xs-12 text-center">
				<h1 class="page-title titulosec_contrato"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Contratos de Mandatos</h1>
			</div>
		</div>
	</div>

	<div class="page-header wide">
		<div class="row mt-4 mb-2">
			<form autocomplete="off">
				<div class="form-group col-lg-3 col-md-6 col-sm-6 col-xs-12" style="height: 55px;">
					<label>
						Empresa:
					</label>
					<div class="form-group">
						<div class="input-group col-xs-12">
							<select class="form-control input_text select2 change-contrato-locales-firmado"
								data-live-search="true"
								id="search_id_empresa">
								<option value="">-- TODOS --</option>
								<?php

								$query_empresa = "";
								if ($login["usuario_locales"]) {
									$query_empresa = "SELECT l.razon_social_id, r.nombre FROM tbl_locales AS l 
										INNER JOIN tbl_razon_social AS r ON r.id = l.razon_social_id
										WHERE l.estado = 1 AND r.status = 1 
										AND l.id IN (" . implode(",", $login["usuario_locales"]) . ")
										GROUP BY l.razon_social_id 
										ORDER BY l.nombre ASC;";
								} else {
									$query_empresa = "SELECT id as razon_social_id, nombre FROM tbl_razon_social WHERE status = 1 ORDER BY nombre ASC";
								}

								$sel_query = $mysqli->query($query_empresa);

								while ($sel = $sel_query->fetch_assoc()) {
								?>
									<option value="<?php echo $sel["razon_social_id"]; ?>"><?php echo $sel["nombre"]; ?></option>
								<?php
								}
								?>
							</select>
							<a class="btn btn-xs btn-default limpiar_select2" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="search_id_empresa" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
						</div>
					</div>
				</div>

				<div class="form-group col-lg-3 col-md-3 col-sm-6 col-xs-12" style="height: 55px; display: none;">
					<label>
						Nombre de la tienda:
					</label>
					<div class="form-group">

						<div class="input-group col-xs-12">
							<select class="form-control input_text select2 change-contrato-locales-firmado"
								data-live-search="true"
								id="search_nombre_tienda">
								<option value="">-- TODOS --</option>
								<?php

								$query_tienda = "";
								if ($login["usuario_locales"]) {
									$query_tienda = "SELECT c.contrato_id as id, c.nombre_tienda as nombre
										FROM cont_contrato AS c
										INNER JOIN tbl_locales AS l ON l.cc_id = c.cc_id
										WHERE c.tipo_contrato_id = 1
										AND l.id IN (" . implode(",", $login["usuario_locales"]) . ")
										AND c.status = 1
										AND c.etapa_id = 5
										ORDER BY c.nombre_tienda ASC";
								} else {
									$query_tienda = "SELECT contrato_id as id, nombre_tienda as nombre
										FROM cont_contrato
										WHERE tipo_contrato_id = 1
										AND status = 1
										AND etapa_id = 5
										ORDER BY nombre_tienda ASC";
								}

								$sel_query = $mysqli->query($query_tienda);

								while ($sel = $sel_query->fetch_assoc()) {
								?>
									<option value="<?php echo $sel["nombre"]; ?>"><?php echo $sel["nombre"]; ?></option>
								<?php
								}
								?>
							</select>
							<!-- <input type="text" class="form-control" id="search_nombre_tienda" placeholder="Nombre de la tienda" style="border: 1px solid #aaa; border-radius: 1px;"> -->
							<a class="btn btn-xs btn-default limpiar_select2" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="search_nombre_tienda" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
						</div>
					</div>
				</div>

				<div class="form-group col-lg-3 col-md-3 col-sm-6 col-xs-12" style="height: 55px; display: none;">
					<label>
						Centro de costos:
					</label>
					<div class="form-group">
						<div class="input-group col-xs-12">
							<select class="form-control input_text select2 change-contrato-locales-firmado"
								data-live-search="true"
								id="search_centro_costos">
								<option value="">-- TODOS --</option>
								<?php

								$query_cc_id = "";
								if ($login["usuario_locales"]) {
									$query_cc_id = "SELECT c.contrato_id as id, c.cc_id
										FROM cont_contrato AS c
										INNER JOIN tbl_locales AS l ON l.cc_id = c.cc_id
										WHERE c.tipo_contrato_id = 1
										AND l.id IN (" . implode(",", $login["usuario_locales"]) . ")
										AND c.status = 1
										AND c.etapa_id = 5
										ORDER BY c.nombre_tienda ASC";
								} else {
									$query_cc_id = "SELECT contrato_id as id, cc_id
										FROM cont_contrato
										WHERE tipo_contrato_id = 1
										AND status = 1
										AND etapa_id = 5
										ORDER BY nombre_tienda ASC";
								}

								$sel_query = $mysqli->query($query_cc_id);

								while ($sel = $sel_query->fetch_assoc()) {
								?>
									<option value="<?php echo $sel["cc_id"]; ?>"><?php echo $sel["cc_id"]; ?></option>
								<?php
								}
								?>
							</select>
							<!-- <input type="text" class="form-control" id="search_centro_costos" placeholder="Centro de costos" style="border: 1px solid #aaa; border-radius: 1px;"> -->
							<a class="btn btn-xs btn-default limpiar_select2" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="search_centro_costos" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
						</div>
					</div>
				</div>

				<div class="form-group col-lg-3 col-md-3 col-sm-6 col-xs-12" style="height: 55px; display: none;">
					<label>
						Moneda:
					</label>
					<div class="form-group">
						<div class="input-group col-xs-12">
							<select
								class="form-control input_text select2 change-contrato-locales-firmado"
								data-live-search="true"
								id="search_moneda">
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
							<a class="btn btn-xs btn-default limpiar_select2" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="search_moneda" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
						</div>
					</div>
				</div>

				<div class="form-group col-lg-2 col-md-3 col-sm-4 col-xs-12" style="height: 55px; display: none;">
					<label>Departamento:</label>
					<div class="form-group">
						<div class="input-group col-xs-12">
							<select
								class="form-control input_text select2 change-contrato-locales-firmado"
								data-live-search="true"
								onchange="sec_contrato_locales_obtener_provincias()"
								name="search_id_departamento"
								id="search_id_departamento">
								<option value="">-- TODOS --</option>
							</select>
							<a class="btn btn-xs btn-default limpiar_select2" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="search_id_departamento" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
						</div>
					</div>
				</div>

				<div class="form-group col-lg-2 col-md-3 col-sm-4 col-xs-12" style="height: 55px; display: none;">
					<label>Provincia:</label>
					<div class="form-group">
						<div class="input-group col-xs-12">
							<select class="form-control input_text select2 change-contrato-locales-firmado"
								data-live-search="true"
								onchange="sec_contrato_locales_obtener_distritos()"
								name="search_id_provincia"
								id="search_id_provincia">
								<option value="">-- TODOS --</option>
							</select>
							<a class="btn btn-xs btn-default limpiar_select2" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="search_id_provincia" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
						</div>
					</div>
				</div>

				<div class="form-group col-lg-2 col-md-3 col-sm-4 col-xs-12" style="height: 55px; display: none;">
					<label>Distrito:</label>
					<div class="form-group">
						<div class="input-group col-xs-12">
							<select class="form-control input_text select2 change-contrato-locales-firmado"
								data-live-search="true"
								name="search_id_distrito"
								id="search_id_distrito">
								<option value="">-- TODOS --</option>
							</select>
							<a class="btn btn-xs btn-default limpiar_select2" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="search_id_distrito" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
						</div>
					</div>
				</div>


				<div class="form-group col-lg-3 col-md-3 col-sm-6 col-xs-12" style="height: 55px;">
					<label>
						F. Solicitud desde:
					</label>
					<div class="form-group">
						<div class="input-group col-xs-12">
							<input
								type="text"
								class="form-control cont_locales_datepicker"
								id="fecha_inicio_solicitud"
								value=""
								readonly="readonly"
								style="border: 1px solid #aaa; border-radius: 1px; min-height: 28px !important;">
							<a class="btn btn-xs btn-default" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" onclick="document.getElementById('fecha_inicio_solicitud').click();"><i class="icon fa fa-calendar"></i></a>
							<a class="btn btn-xs btn-default limpiar_input" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="fecha_inicio_solicitud" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
						</div>
					</div>
				</div>

				<div class="form-group col-lg-3 col-md-3 col-sm-6 col-xs-12" style="height: 55px;">
					<label>
						F. Solicitud hasta:
					</label>
					<div class="form-group">
						<div class="input-group col-xs-12">
							<input
								type="text"
								class="form-control cont_locales_datepicker"
								id="fecha_fin_solicitud"
								value=""
								readonly="readonly"
								style="border: 1px solid #aaa; border-radius: 1px; min-height: 28px !important;">
							<a class="btn btn-xs btn-default" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" onclick="document.getElementById('fecha_fin_solicitud').click();"><i class="icon fa fa-calendar"></i></a>
							<a class="btn btn-xs btn-default limpiar_input" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="fecha_fin_solicitud" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
						</div>
					</div>
				</div>

				<div class="form-group col-lg-3 col-md-3 col-sm-6 col-xs-12" style="height: 55px; display: none;">
					<label>
						F. Inicio desde:
					</label>
					<div class="form-group">
						<div class="input-group col-xs-12">
							<input
								type="text"
								class="form-control cont_locales_datepicker"
								id="fecha_inicio_inicio"
								value=""
								readonly="readonly"
								style="border: 1px solid #aaa; border-radius: 1px; min-height: 28px !important;">
							<a class="btn btn-xs btn-default" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" onclick="document.getElementById('fecha_inicio_inicio').click();"><i class="icon fa fa-calendar"></i></a>
							<a class="btn btn-xs btn-default limpiar_input" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="fecha_inicio_inicio" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
						</div>
					</div>
				</div>

				<div class="form-group col-lg-3 col-md-3 col-sm-6 col-xs-12" style="height: 55px; display: none;">
					<label>
						F. Inicio hasta:
					</label>
					<div class="form-group">
						<div class="input-group col-xs-12">
							<input
								type="text"
								class="form-control cont_locales_datepicker"
								id="fecha_fin_inicio"
								value=""
								readonly="readonly"
								style="border: 1px solid #aaa; border-radius: 1px; min-height: 28px !important;">
							<a class="btn btn-xs btn-default" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" onclick="document.getElementById('fecha_fin_inicio').click();"><i class="icon fa fa-calendar"></i></a>
							<a class="btn btn-xs btn-default limpiar_input" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="fecha_fin_inicio" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
						</div>
					</div>
				</div>

				<div class="form-group col-lg-3 col-md-3 col-sm-6 col-xs-12" style="height: 55px; display: none;">
					<label>
						F. Suscripción desde:
					</label>
					<div class="form-group">
						<div class="input-group col-xs-12">
							<input
								type="text"
								class="form-control cont_locales_datepicker"
								id="fecha_inicio_suscripcion"
								value=""
								readonly="readonly"
								style="border: 1px solid #aaa; border-radius: 1px; min-height: 28px !important;">
							<a class="btn btn-xs btn-default" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" onclick="document.getElementById('fecha_inicio_suscripcion').click();"><i class="icon fa fa-calendar"></i></a>
							<a class="btn btn-xs btn-default limpiar_input" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="fecha_inicio_suscripcion" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
						</div>
					</div>
				</div>

				<div class="form-group col-lg-3 col-md-3 col-sm-6 col-xs-12" style="height: 55px; display: none;">
					<label>
						F. Suscripción hasta:
					</label>
					<div class="form-group">
						<div class="input-group col-xs-12">
							<input
								type="text"
								class="form-control cont_locales_datepicker"
								id="fecha_fin_suscripcion"
								value=""
								readonly="readonly"
								style="border: 1px solid #aaa; border-radius: 1px; min-height: 28px !important;">
							<a class="btn btn-xs btn-default" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" onclick="document.getElementById('fecha_fin_suscripcion').click();"><i class="icon fa fa-calendar"></i></a>
							<a class="btn btn-xs btn-default limpiar_input" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="fecha_fin_suscripcion" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
						</div>
					</div>
				</div>

				<div class="form-group col-lg-3 col-md-3 col-sm-6 col-xs-12" style="height: 55px; display: none;">
					<label>
						Etapa:
					</label>
					<div class="form-group">
						<div class="input-group col-xs-12">
							<select class="form-control input_text select2 change-contrato-locales-firmado"
								data-live-search="true"
								name="search_etapa"
								id="search_etapa">
								<option value="">-- TODOS --</option>
								<option value="1">Firmado</option>
								<option value="2">Resuelto</option>
							</select>
							<a class="btn btn-xs btn-default limpiar_select2" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="search_etapa" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
						</div>
					</div>
				</div>

				<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 text-right" style="margin-top: 15px; margin-bottom: 15px; float: right;">
					<button type="button" class="btn btn-warning float-left" id="btn_limpiar_filtros_de_busqueda">
						<i class="fa fa-eraser"></i>
						Limpiar filtros
					</button>
					<span id="cont_locales_excel_mandato" class="float-left" style="padding: inherit;"></span>
					<button type="button" class="btn btn-primary float-left" id="cont_locales_btn_buscar" onclick="buscarContratoPorParametros_mandato();">
						<i class="glyphicon glyphicon-search"></i>
						Buscar
					</button>
				</div>

				<div class="col-md-12" id="cont_locales_alerta_filtrar_por">

				</div>
			</form>
		</div>
	</div>

	<div class="page-header wide">
		<div class="row mt-3 mb-2">
			<div class="row form-horizontal">
				<?php if (in_array("xlsx", $usuario_permisos[$menu_id])) { ?>
					<div class="col-md-2" id="cont_locales_excel_mandato">
					</div>+
				<?php } ?>

				<button type="button" id="btn_ordenes_de_pago" href="" class="btn btn-success btn-sm" style="display: none;"><span class="fa fa-th"></span> Órdenes de Pago</button>

				<button type="button" id="btn_generar_ordenes_de_pago" href="" class="btn btn-success btn-sm" style="display: none;"><span class="fa fa-th"></span> Generar Órdenes de Pago</button>

				<button type="button" id="btn_comprobante_varios_locales" href="" class="btn btn-success btn-sm" style="display: none;"><span class="fa fa-th"></span> Registro de comprobante de pago (Varios locales)</button>
			</div>
		</div>
	</div>

	<div class="row mt-3" id="cont_contrato_div_tabla">
		<table id="cont_locales_datatable" class="table table-bordered dt-responsive table-condensed" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th scope="col">Código</th>
					<?php if (false) { ?>
						<th scope="col">CC</th>
						<th scope="col">Tienda</th>
						<th scope="col">Área (m2)</th>
					<?php } ?>

					<th scope="col">Mandante</th>
					<th scope="col">Mandatario</th>
					<th scope="col">F. solicitud</th>
					<th scope="col">F. suscripcion</th>
					<th scope="col">Fecha Inicio</th>
					<th scope="col">Fecha Fin</th>
					<th scope="col">Firma</th>
					<?php if (!($area_id == '2'  || $area_id == '6')) { ?>
						<th scope="col">Etapa</th>
					<?php } ?>
					<th scope="col">Detalle</th>
					<th scope="col">Alerta</th>
					<th scope="col">Estado alerta</th>


				</tr>
			</thead>
			<tbody>

			</tbody>
			<tfoot>
				<tr>
					<th scope="col">Código</th>
					<?php if (false) { ?>
						<th scope="col">CC</th>
						<th scope="col">Tienda</th>
						<th scope="col">Área (m2)</th>
					<?php } ?>

					<th scope="col">Mandante</th>
					<th scope="col">Mandatario</th>
					<th scope="col">F. solicitud</th>
					<th scope="col">F. suscripcion</th>
					<th scope="col">Fecha Inicio</th>
					<th scope="col">Fecha Fin</th>
					<th scope="col">Firma</th>

					<?php if (!($area_id == '2'  || $area_id == '6')) { ?>
						<th scope="col">Etapa</th>
					<?php } ?>
					<th scope="col">Detalle</th>
					<th scope="col">Alerta</th>
					<th scope="col">Estado alerta</th>


				</tr>
			</tfoot>
		</table>
	</div>


	<!-- INICIO MODAL ALERTA CONTRATO -->
	<div class="modal fade" id="configurarAlerta_mandato" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-lg" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
					<h2 class="modal-title text-center" id="exampleModalLabel">
						<strong>Registro de alerta</strong>
					</h2>
				</div>
				<div class="modal-body">

					<table id="tabla_datos_alerta" class="table table-striped table-bordered table-responsive">
						<input type="hidden" name="condicion_economica_id" id="condicion_economica_id">
						<thead>
							<tr>
								<th class="text-center">ID</th>

								<th class="text-center">Fecha inicio</th>
								<th class="text-center">Fecha fin</th>
								<th class="text-center">Alerta</th>
							</tr>
						</thead>
						<tbody id="contenido_modal_alerta">

						</tbody>
					</table>
					<div>
						<div class="col-md-2" style="text-align: right; padding: 0;">
							<label>Notificar</label>
						</div>

						<div class="col-md-2" style="padding-bottom: 5px;">
							<input type="text" class="form-control input-sm" name="numAlerta" maxlength="3" id="numAlerta" min="0" max="999" onkeypress="soloNumeros(event)">
						</div>

						<div class="col-md-8" style="text-align: left; padding: 0;">
							<label>días antes de finalizar el contrato.</label>
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
					<button type="button" class="btn btn-success" onclick="registrar_alerta_mandato();">
						<i class="glyphicon glyphicon-saved"></i>
						Registrar alerta
					</button>
				</div>
			</div>
		</div>
	</div>
	<!-- FIN MODAL ALERTA CONTRATO -->


	<div id="modal_ordenes_de_pago" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
		<div class="modal-dialog" role="document" style="width:1250px;">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="myModalLabel">Ordenes de Pago - <span id="nombre_del_local"></span></h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<form autocomplete="off">
							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="form-group">
									<div id="div_tabla_ordenes">
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


	<div id="modal_agregar_pago" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
		<div class="modal-dialog modal-sm" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="modal_agregar_pago_titulo">Registrar datos del pago</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<form id="form_comprobante_de_pago" name="form_comprobante_de_pago" method="POST" enctype="multipart/form-data">
							<input type="hidden" id="orden_detalle_id">
							<input type="hidden" id="contrato_id_temp">
							<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
								<div class="form-group">
									<div class="control-label">Fecha de pago:</div>
									<div class="input-group">
										<input
											type="text"
											class="form-control"
											id="cont_locales_fecha_pago"
											value="<?php echo date("d-m-Y", strtotime("-1 days")); ?>"
											readonly="readonly"
											style="height: 30px;">
										<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="cont_locales_fecha_pago" id="label_cont_locales_fecha_pago"></label>
									</div>
								</div>
							</div>
							<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
								<div class="form-group">
									<div class="control-label">Comprobante de pago:</div>
									<input type="file" id="archivo_comprobante_de_pago" name="archivo_comprobante_de_pago" class="filtro txt_filter_style">
								</div>
							</div>
							<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_propietario_mensaje" style="display: none">
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
					<button type="button" onclick="guardar_comprobante_pago();" class="btn btn-success" id="btn_agregar_pago">
						<i class="icon fa fa-plus"></i>
						Registrar datos de pago
					</button>
				</div>
			</div>
		</div>
	</div>



	<div id="modal_comprobante_varios_locales" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
		<div class="modal-dialog" role="document" style="width: 98%;">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="modal_agregar_pago_titulo">Registrar fecha y comprobante de pago - Varios locales</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-xs-5 col-md-5 col-lg-5" style="border-right-style: solid; border-right-color: #bfbfbf;">
							<form id="form_comprobante_de_pago_varios_locales" name="form_comprobante_de_pago_varios_locales" method="POST" enctype="multipart/form-data">
								<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
									<div class="form-group">
										<div class="control-label">Fecha de pago:</div>
										<div class="input-group">
											<input
												type="text"
												class="form-control"
												id="cont_locales_fecha_pago_varios_locales"
												value="<?php echo date("d-m-Y", strtotime("-1 days")); ?>"
												readonly="readonly"
												style="height: 30px;">
											<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="cont_locales_fecha_pago_varios_locales" id="label_cont_locales_fecha_pago_varios_locales"></label>
										</div>
									</div>
								</div>
								<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
									<div class="form-group">
										<div class="control-label">Comprobante de pago:</div>
										<input type="file" id="archivo_comprobante_de_pago_varios_locales" name="archivo_comprobante_de_pago_varios_locales" class="filtro txt_filter_style">
									</div>
								</div>
								<div class="col-xs-12 col-md-12 col-lg-12" style="padding: 0 5px;">
									<div>
										<div class="control-label">Lista de locales:</div>
										<div id="tbl_ordenes_lista">
										</div>
									</div>
								</div>
								<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_comprobante_varios_locales_mensaje" style="display: none">
									<div class="form-group">
										<div class="alert alert-danger" role="alert">
											<strong id="modal_comprobante_varios_locales_mensaje"></strong>
										</div>
									</div>
								</div>

								<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
									<button type="button" onclick="guardar_comprobante_pago_varios_locales();" class="btn btn-success btn-block" id="btn_agregar_pago">
										<i class="icon fa fa-plus"></i>
										Registrar datos de pago
									</button>
								</div>

							</form>
						</div>
						<div class="col-xs-7 col-md-7 col-lg-7">
							<div class="row" style="margin-bottom: 10px;">
								<form autocomplete="off" id="form_buscador_locales_orden_detalle">
									<div class="col-md-12" style="padding-right: 0px; padding-left: 0px;">
										<div class="col-md-3" style="padding-right: 5px; padding-left: 5px;">
											<input type="text" id="varios_locales_nombre_local" name="varios_locales_nombre_local" class="form-control" placeholder="Nombre del local...">
										</div>
										<div class="col-md-3" style="padding-right: 5px; padding-left: 5px;">
											<select id="varios_locales_periodo" name="varios_locales_periodo" class="form-control">
												<option value="0">Seleccione el Periodo</option>
												<?php
												$fecha_inicio = date("19-m-Y");

												for ($i = 1; $i <= 6; $i++) {
													$fecha_fin = date("d-m-Y", strtotime($fecha_inicio . "+ 1 month"));
													$fecha_fin_menos_un_dia =  date("d-m-Y", strtotime($fecha_fin . "- 1 days"));
													echo '<option value="' . str_replace("-", "", $fecha_inicio . $fecha_fin_menos_un_dia) . '">' . str_replace("-", "/", $fecha_inicio) . '-' . str_replace("-", "/", $fecha_fin_menos_un_dia) . '</option>';

													$fecha_inicio = date("d-m-Y", strtotime($fecha_inicio . "- 1 month"));
												}
												?>
											</select>
										</div>
										<div class="col-md-4" style="padding-right: 5px; padding-left: 5px;">
											<select id="varios_locales_tipo_renta" name="varios_locales_tipo_renta" class="form-control">
												<option value="0">Seleccione el tipo de renta</option>
												<?php
												$sel_query = $mysqli->query("SELECT id, nombre
												FROM cont_tipo_orden
												WHERE status = 1
												ORDER BY nombre ASC;");
												while ($sel = $sel_query->fetch_assoc()) {
													if ($sel["id"] == 15) {
														$selected_area = 'selected';
													} else {
														$selected_area = '';
													} ?>
													<option value="<?php echo $sel["id"]; ?>" <?php echo $selected_area; ?>><?php echo $sel["nombre"]; ?></option>
												<?php
												}
												?>
											</select>
										</div>
										<div class="col-md-2" style="padding-right: 5px; padding-left: 5px;">
											<div class="form-group">
												<div class="col-md-5" style="padding-right: 2px; padding-left: 2px;">
													<label for="varios_locales_numero_registros_a_mostrar">Mostrar: </label>
												</div>
												<div class="col-md-7" style="padding-right: 2px; padding-left: 2px;">
													<select id="varios_locales_numero_registros_a_mostrar" name="varios_locales_numero_registros_a_mostrar" class="form-control">
														<option value="10">10</option>
														<option value="25">25</option>
														<option value="50">50</option>
														<option value="100">100</option>
														<option value="250">250</option>
														<option value="500">500</option>
													</select>
												</div>
											</div>
										</div>
									</div>
								</form>

							</div>
							<div id="tbl_ordenes_busqueda">
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">

				</div>
			</div>
		</div>
	</div>

</div>


<!-- INICIO LECTOR PANTALLA COMPLETA -->
<div class="modal fade right" id="modal_visor_pdf" tabindex="-1" role="dialog" aria-labelledby="exampleModalPreviewLabel" aria-hidden="true" style="width: 100%;padding-left: 0px;">
	<div class="modal-dialog-full-width modal-dialog momodel modal-fluid" role="document" style="width: 100%; margin: 10px auto;">
		<div class="modal-content-full-width modal-content " style="background-color: rgb(0 0 0 / 0%) !important;">
			<div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center;margin-top: 0px; margin-bottom:10px;">
				<button type="button" class="btn btn-block btn-danger" data-dismiss="modal"><i class="fa fa-close"></i> Cerrar Visor</button>
			</div>
			<div class="modal-body" style="padding: 0px;" id="div_modal_visor_pdf">
			</div>
			<div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center;margin-top: 10px; margin-bottom:10px;">
				<button type="button" class="btn btn-block btn-danger" data-dismiss="modal"><i class="fa fa-close"></i> Cerrar Visor</button>
			</div>
			<div class="col-xs-12 col-md-12 col-sm-12"></div>
		</div>
	</div>
</div>
<!-- FIN LECTOR PANTALLA COMPLETA -->