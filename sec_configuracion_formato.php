<?php
global $mysqli;
include '/var/www/html/sys/Pagination.php';
$menu_id = "";
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'configuracion' AND sub_sec_id = 'formato' LIMIT 1");
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
				<h1 class="page-title titulosec_contrato"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Formato para Contratos</h1>
			</div>
		</div>
	</div>

	<div class="page-header wide">
		<div class="row mt-4 mb-2">
			<form autocomplete="off">
				<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 text-right" style="margin-top: 15px; margin-bottom: 15px; float: right;">
					<button type="button" class="btn btn-warning float-left" id="btn_limpiar_filtros_de_busqueda">
						<i class="fa fa-eraser"></i>
						Limpiar filtros
					</button>
					<span id="cont_locales_excel" class="float-left" style="padding: inherit;"></span>
					<button type="button" class="btn btn-primary float-left" id="cont_locales_btn_buscar" onclick="buscarContratoPorParametros_locaciones();">
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
					<div class="col-md-2" id="cont_locales_excel">
					</div>+
				<?php } ?>

				<button type="button" id="btn_ordenes_de_pago" href="" class="btn btn-success btn-sm" style="display: none;"><span class="fa fa-th"></span> Órdenes de Pago</button>

				<button type="button" id="btn_generar_ordenes_de_pago" href="" class="btn btn-success btn-sm" style="display: none;"><span class="fa fa-th"></span> Generar Órdenes de Pago</button>

				<button type="button" id="btn_comprobante_varios_locales" href="" class="btn btn-success btn-sm" style="display: none;"><span class="fa fa-th"></span> Registro de comprobante de pago (Varios locales)</button>
			</div>
		</div>
	</div>
	<?php

	$estado_sol = '';


	$where_empresa = "";
	$where_area_solicitante = "";
	$where_ruc = "";
	$where_razon_social = "";
	$where_moneda = "";
	$where_ubigeo = '';
	$where_nombre_tienda = '';
	$query_fecha = '';
	$where_estado_sol = '';
	$where_estado_sol_v2 = '';
	$where_estado_cancelado = "";

	if (!empty($empresa)) {
		$where_empresa = " AND c.empresa_suscribe_id = '" . $empresa . "' ";
	}

	if (!empty($ruc)) {
		$where_ruc = " AND c.ruc LIKE '%" . $ruc . "%' ";
	}

	if (!empty($razon_social)) {
		$where_razon_social = " AND c.razon_social  LIKE '%" . $razon_social . "%' ";
	}

	if (!empty($moneda)) {
		$where_moneda = " AND ce.tipo_moneda_id = '" . $moneda . "'";
	}

	if (!empty($id_departamento)) {
		$where_ubigeo = " AND i.ubigeo_id LIKE '%" . $id_departamento . $id_provincia . $id_distrito . "%'";
	}

	if (!empty($nombre_tienda)) {
		$where_nombre_tienda = " AND c.nombre_tienda LIKE '%" . $nombre_tienda . "%' ";
	}

	if (!empty($fecha_inicio) && !empty($fecha_fin)) {
		$query_fecha = " AND a.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
	} elseif (!empty($fecha_inicio)) {
		$query_fecha = " AND a.created_at >= '$fecha_inicio 00:00:00'";
	} elseif (!empty($fecha_fin)) {
		$query_fecha = " AND a.created_at <= '$fecha_fin 23:59:59'";
	}

	if (!empty($estado_sol)) {
		if ((int) $estado_sol === 99) {
			$where_estado_sol_v2 = " AND a.cancelado_id = 1 ";
		} elseif ((int) $estado_sol === 1) {
			$where_estado_sol = " AND ( a.estado_solicitud_id = 1 OR a.estado_solicitud_id IS NULL ) ";
		} else {
			$where_estado_sol = " AND a.estado_solicitud_id = '" . $estado_sol . "' ";
		}
	}

	if (!empty($estado_sol_v2)) {
		if ((int) $estado_sol_v2 === 1) {
			$where_estado_sol_v2 = " AND ( a.cancelado_id != 1 || a.cancelado_id IS NULL )";
		} elseif ((int) $estado_sol_v2 === 2) {
			$where_estado_sol_v2 = " AND a.cancelado_id = 1 ";
		}
	}

	if ($login["area_id"] == 33) {
		$where_estado_cancelado = " AND ( a.cancelado_id != 1 OR a.cancelado_id IS NULL )";
	}

	$total_query = $mysqli->query("
		SELECT COUNT(c.contrato_id) as num_rows 
		FROM cont_adendas a
		INNER JOIN cont_contrato c ON a.contrato_id = c.contrato_id
		INNER JOIN tbl_usuarios u ON a.user_created_id = u.id
		INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		INNER JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id AND ce.status = 1 
		INNER JOIN cont_inmueble i ON c.contrato_id = i.contrato_id AND i.contrato_detalle_id = ce.contrato_detalle_id
		INNER JOIN cont_etapa e ON c.etapa_id = e.etapa_id
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
		WHERE c.tipo_contrato_id = 1
		AND a.procesado = 0
		AND a.status = 1
		" . $where_empresa . "
		" . $where_ruc . "
		" . $where_razon_social . "
		" . $where_moneda . "
		" . $where_ubigeo . "
		" . $where_nombre_tienda . "
		" . $query_fecha . "
		" . $where_estado_sol . "
		" . $where_estado_sol_v2 . "
		" . $where_estado_cancelado . "
		 
		");

	$resultNum = $total_query->fetch_assoc();
	$num_rows = $resultNum['num_rows'];
	$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
	$per_page = 10;
	$numLinks = 10;
	$total_paginate = ceil($num_rows / $per_page);
	$total_paginate = $total_paginate == 0 ? 1 : $total_paginate;
	$page = $page > $total_paginate ? $total_paginate : $page;
	$offset = ($page - 1) * $per_page;
	$paginate = new Pagination('sec_contratos_solicitud_cambiar_de_pagina', $num_rows, $per_page, $numLinks, $page);

	$sel_query = $mysqli->query("
SELECT
	ROW_NUMBER() OVER ( ORDER BY a.tipo_contrato_id ASC ) AS nro,
	idformato,
	tipo_contrato_id,
	a.nombre,
	descripcion,
	codigo,
	CONCAT('Versión ',codigo) as version,
	a.created_at,
	CONCAT(c.nombre,' ',c.apellido_paterno) usuario,
	b.personal_id
FROM
	cont_formato a
	LEFT JOIN tbl_usuarios b ON b.id = a.user_created_id
	LEFT JOIN tbl_personal_apt c ON c.id = b.personal_id
WHERE
	idformato IN ( SELECT MAX( idformato ) FROM cont_formato GROUP BY tipo_contrato_id ) ORDER BY a.tipo_contrato_id;");

	?>
	<div class="row mt-3" id="cont_contrato_div_tabla">
		<div class="table-responsive">
			<table class="table table-striped table-hover table-condensed table-bordered tabla_contratos_para_filtro" cellspacing="0" width="100%">
				<thead>
					<tr>
						<th class="text-center">Nro.</th>
						<th class="text-center">Denominación</th>
						<th class="text-center">Descripción</th>

						<th class="text-center">Modelo</th>
						<th class="text-center">Ultima actualización</th>
						<th class="text-center">Usuario actualizó</th>

						<th class="text-center">Acción</th>
					</tr>
				</thead>
				<tbody>
					<?php
					while ($sel = $sel_query->fetch_assoc()) {

					?>
						<tr>
							<td class="text-center"><?php echo $sel["nro"]; ?></td>

							<td><?php echo $sel["nombre"]; ?></td>
							<td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
								<?php echo $sel["descripcion"]; ?>
							</td>

							<td class="text-center"><?php echo $sel["version"]; ?></td>
							<td class="text-center"><?php echo $sel["created_at"]; ?></td>
							<td class="text-center"><?php echo $sel["usuario"]; ?></td>

							<td class="text-center">
								<a class="btn btn-rounded btn-warning btn-sm"
									href="./?sec_id=<?php echo $sec_id; ?>&amp;sub_sec_id=detalle_formato&id=<?php echo $sel["idformato"]; ?>"

									title="Editar formato">
									<i class="fa fa-pencil"></i>
								</a>
								<a class="btn btn-rounded btn-primary btn-sm" set_configuracion_detalle_formato
									href="./?sec_id=<?php echo $sec_id; ?>&amp;sub_sec_id=detalle_formato&id=<?php echo $sel["idformato"]; ?>"
									title="Ver formato">
									<i class="fa fa-eye"></i>
								</a>

							</td>
						</tr>
					<?php
					}
					?>
				</tbody>
			</table>
		</div>
	</div>


	<!-- INICIO MODAL ALERTA CONTRATO -->
	<div class="modal fade" id="configurarAlerta_locaciones" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
					<button type="button" class="btn btn-success" onclick="registrar_alerta_locacion();">
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