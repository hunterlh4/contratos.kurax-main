<link rel="stylesheet" href="css/simplePagination.css">
<div class="content container-fluid">
	<div class="page-header wide">
		<div class="row">
			<div class="col-xs-12 text-center">
				<div class="page-title"><i class="icon icon-inline fa fa-fw fa-users"></i> <span id="title-text">Personal</span></div>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12" style="padding-bottom:12px;">
				<?php
				if ($item_id) {
				?>
					<a class="btn btn-default mt-2" href="./?sec_id=<?php echo $sec_id; ?>" style="">
						<i class="glyphicon glyphicon-arrow-left"></i>
						Regresar
					</a>
					<?php
					if ($sub_sec_id !== 'log_personal') {
					?>
						<button type="button" data-then="exit" class="save_btn btn btn-success mt-2" data-button="save">
							<i class="glyphicon glyphicon-floppy-save"></i>
							Guardar y Salir
						</button>
						<button type="button" data-then="reload" class="save_btn btn btn-success mt-2" data-button="save">
							<i class="glyphicon glyphicon-floppy-save"></i>
							Guardar
						</button>
						<?php if ($item_id != "new") { ?>
							<button
								type="button"
								data-then="exit"
								data-button="delete"
								data-table="tbl_personal_apt"
								data-id="<?php echo $item_id; ?>"
								class="del_btn btn btn-danger mt-2 pull-right">
								<i class="glyphicon glyphicon-remove"></i>
								Eliminar
							</button>
						<?php } ?>
					<?php } ?>
				<?php } else { ?>
					<a
						href="./?sec_id=<?php echo $sec_id; ?>&amp;item_id=new"
						id=""
						data-sec="<?php echo $sec_id; ?>"
						data-table="tbl_personal_apt"
						class="btn btn-rounded mt-2 btn-min-width btn-success btn-add"><i class="glyphicon glyphicon-plus"></i> Agregar</a>
					<button
						class="btn btn-primary mt-2 personal_import_btn" data-button="import">
						<span class="glyphicon glyphicon-import"></span>
						Importar masivamente
					</button>
					<button
						class="btn btn-warning mt-2 massive_download_btn" data-button="descarga_masivo">
						<span class="glyphicon glyphicon-download-alt"></span>
						Descargar registros importados masivamente
					</button>
					<button
						class="btn btn-danger mt-2 user_to_dni_btn" data-button="user_to_dni">
						<span class="glyphicon glyphicon-search"></span>
						Comprobar usuarios por DNI
					</button>
					<button
						class="btn btn-info mt-2 download_personal_btn" data-button="descarga_masivo">
						<span class="glyphicon glyphicon-download-alt"></span>
						Descarga personal registrado
					</button>
				<?php } ?>
				<?php
				if ($sub_sec_id !== 'log_personal') {
				?>
					<button id="btnActivos" class="btn btn-default mt-2 pull-right">Mostrar Inactivos</button>
				<?php } ?>
			</div>

		</div>
	</div>
	<?php
	if ($sub_sec_id == 'log_personal' && $item_id != '') {
	?>

		<div id="opc_tbl_personal_auditoria" class="mt-0p5" style="margin-top: 12px!important">

			<div class="row">
				<div class="col-sm-6 col-md-6 col-lg-2">
					<div class="form-group">
						<div class="control-label">Fecha Inicio:</div>
						<div class="input-group">
							<input
								type="date"
								id="log_personal_fecha_inicio"
								class="form_control input_text item_config"
								name="log_personal_fecha_inicio"
								value="<?php echo date('Y-m-d', strtotime(date('Y-m-d') . '-7 days')) ?>" />
						</div>
					</div>
				</div>
				<div class="col-sm-6 col-md-6 col-lg-2">
					<div class="form-group">
						<div class="control-label">Fecha Fin:</div>
						<div class="input-group">
							<input
								type="date"
								id="log_personal_fecha_fin"
								class="form_control input_text item_config"
								name="log_personal_fecha_fin"
								value="<?php echo date('Y-m-d') ?>" />
						</div>
					</div>
				</div>
				<div class="col-xs-12 col-sm-6 col-md-6 col-lg-2 col-xs-offset-2 col-sm-6 col-lg-3 pull-right">
					<br>
					<div class="form-group">
						<button id="btn_consultar_log_personal" class="btn btn-block btn-rounded btn-success">
							<span class="glyphicon glyphicon-search"></span>
							Consultar
						</button>
					</div>
				</div>
				<div class="col-md-12">
					<h3>Log del Personal: <b id="log_de_personal"></b> </h3>
				</div>
			</div>

			<div class="row">
				<div class="col-xs-12 mt-0p5">
					<div class="table-responsive">
						<table id="table_tbl_personal_auditoria" class="table table-striped table-hover table-condensed table-bordered dt-responsive" cellspacing="0" width="100%">
							<thead>
								<tr>
									<th class="text-center">Fecha</th>
									<th class="text-center">Modificado por:</th>
									<th class="text-center">Campo modificado</th>
									<th class="text-center">Valor anterior</th>
									<th class="text-center">Valor ingresado</th>
									<th class="text-center">IP</th>
								</tr>
							</thead>
							<tbody id="">
							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>

	<?php
	} elseif ($item_id) {
		$item = $mysqli->query("SELECT 
									id,
									area_id,
									nombre,
									apellido_paterno,
									apellido_materno,
									fecha_ingreso_laboral,
									consorcio_id,
									dni,
									telefono,
									celular,
									celular_coorporativo,
									correo,
									cargo_id,
									sistema_id,
									zona_id,
									razon_social_id,
									estado,
									id_operador_coorporativo
									FROM tbl_personal_apt
									WHERE id = '" . $item_id . "'")->fetch_assoc();

	?>
		<input type="hidden" class="save_data" data-col="table" value="tbl_personal_apt">
		<input type="hidden" class="save_data" data-col="id" value="<?php echo $item_id; ?>">

		<div class="col-md-12">
			<div class="tab-content">
				<div class="tab-pane active" id="tab_usuario">
					<div class="col-xs-12 col-md-2 col-lg-2"></div>
					<div class="col-xs-12 col-md-8 col-lg-8">
						<div class="panel" id="datos_1">
							<div class="panel-heading">
								<div class="panel-title"><i class="icon fa fa-file-text-o muted"></i> Datos Personales</div>
							</div>
							<!-- FORMULARIO PERSONAL -->
							<div id="panel-datos_1" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="panel-collapse-1-heading">
								<div class="panel-body">
									<?php
									$form_items = array();
									$form_items["input_text"] = array();
									$form_items["input_text"]["nombre"] = array("NOMBRE", "Ingrese caracteres alfanuméricos", "Ingrese el nombre del personal");
									$form_items["input_text"]["apellido_paterno"] = array("APELLIDO PATERNO", "Ingrese caracteres alfanuméricos", "Ingrese el primer apellido del personal");
									$form_items["input_text"]["apellido_materno"] = array("APELLIDO MATERNO", "Ingrese caracteres alfanuméricos", "Ingrese el segundo apellido del personal");
									$form_items["input_text"]["dni"] = array("DNI", "Ingrese números (8)", "Ingrese numero de DNI del personal");
									$form_items["input_text"]["correo"] = array("CORREO", "Ingresar formato tipo email@testtest.gmail.com", "Ingrese el correo electronico del personal");
									$form_items["input_text"]["telefono"] = array("TELEFONO", "Ingresar números", "Ingrese el telefono del personal");
									$form_items["input_text"]["celular"] = array("CELULAR", "Ingresar números", "Ingrese el numero celular del personal");
									$form_items["input_text"]["celular_coorporativo"] = array("CELULAR COORPORATIVO", "Ingresar números", "Ingrese el numero celular");
									foreach ($form_items["input_text"] as $vc_k => $vc_v) {
										build_input_text($vc_k, $vc_v);
									}

									?>
									<div class="form-group">
										<label class="col-xs-4 control-label">OPERADOR CORPORATIVO</label>
										<div class="input-group col-xs-8">
											<select
												class="form-control input_text select2"
												id="select-id_operador_coorporativo"
												data-col="id_operador_coorporativo">
												<option value="0">--- Seleccione Operador ---</option>
												<option value="1" <?php if (isset($item["id_operador_coorporativo"]) && $item["id_operador_coorporativo"] == 1) { ?> selected="selected" <?php } ?>>AWS</option>
												<option value="2" <?php if (isset($item["id_operador_coorporativo"]) && $item["id_operador_coorporativo"] == 2) { ?> selected="selected" <?php } ?>>INTERMAX</option>
											</select>
										</div>
									</div>
									<?php

									$form_items["input_select"]["area_id"] = array();
									$form_items["input_select"]["area_id"]["name"] = "AREA";
									$form_items["input_select"]["area_id"]["title"] = "Seleccione el area al que pertenece este personal";
									$form_items["input_select"]["area_id"]["table"] = "tbl_areas";
									$form_items["input_select"]["area_id"]["cols"] = array("nombre");

									$form_items["input_select"]["cargo_id"] = array();
									$form_items["input_select"]["cargo_id"]["name"] = "CARGO";
									$form_items["input_select"]["cargo_id"]["title"] = "Seleccione cargo del personal";
									$form_items["input_select"]["cargo_id"]["table"] = "tbl_cargos";
									$form_items["input_select"]["cargo_id"]["cols"] = array("nombre");

									foreach ($form_items["input_select"] as $vc_k => $vc_v) {
										build_input_select($vc_k, $vc_v);
									}

									?>

									<div class="form-group">
										<label class="control-label col-xs-4">EMPRESA</label>
										<div class="input-group col-xs-8">
											<select
												class="form-control input_text select2"
												data-col="razon_social_id"
												id="select-razon_social_id"
												title="Seleccione la empresa del personal">
												<option value="0">--- Seleccione</option>
												<?php
												$sel_query = $mysqli->query("SELECT e.id, e.nombre FROM tbl_razon_social e 
								    				ORDER BY e.nombre");

												while ($sel = $sel_query->fetch_assoc()) {
												?>
													<option
														value="<?php if (isset($sel["id"])) {
																	echo $sel["id"];
																} ?>" <?php if (isset($sel["id"]) && isset($item["razon_social_id"])) {
																			if ($item["razon_social_id"] == $sel["id"]) { ?>
														selected <?php }
																		} ?>><?php if (isset($sel["nombre"])) {
																					echo $sel["nombre"];
																				} ?>
													</option>
												<?php
												}
												?>
											</select>
										</div>
									</div>

									<div class="form-group">
										<label class="control-label col-xs-4">Zona</label>
										<div class="input-group col-xs-8">
											<select
												class="form-control input_text select2"
												data-col="zona_id"
												id="select-zona_id"
												title="Seleccione la Zona a la que pertenece esta persona">
												<option value="0">--- Sin Zona</option>
												<?php


												$where = 'AND z.razon_social_id != 30';
												if (isset($item["razon_social_id"]) && $item["razon_social_id"] == 30) {
													$where = 'AND z.razon_social_id = ' . $item['razon_social_id'];
												}
												$sel_query = $mysqli->query("SELECT z.id, z.nombre
												FROM tbl_zonas z 
												WHERE 1 = 1 " . $where . "
												ORDER BY z.ord");
												while ($sel = $sel_query->fetch_assoc()) {
												?><option value="<?php if (isset($sel["id"])) {
																		echo $sel["id"];
																	} ?>" <?php if (isset($sel["id"]) && isset($item["zona_id"])) {
																				if ($item["zona_id"] == $sel["id"]) { ?> selected <?php }
																															} ?>><?php if (isset($sel["nombre"])) {
																																		echo $sel['razon_social'] . " - " . $sel["nombre"];
																																	} ?></option>
												<?php
												}
												?>
											</select>
										</div>
									</div>
									<?php
									$form_items = array();
									$form_items["checkbox"] = array("estado" => "ESTADO");
									foreach ($form_items["checkbox"] as $vc_k => $vc_v) {
										build_checkbox($vc_k, $vc_v, "tbl_personal_apt");
									}
									?>

								</div>
							</div>
						</div>
					</div>
					<div class="col-xs-12 col-md-2 col-lg-2"></div>

				</div>

			</div>
		</div>

	<?php
	} else {
		$list_query = $mysqli->query("SELECT 
							    tpa.id,
							    tpa.nombre,
							    tpa.apellido_paterno,
							    tpa.apellido_materno,
							    tpa.fecha_ingreso_laboral,
							    tpa.consorcio_id,
							    tpa.dni,
							    tpa.telefono,
							    tpa.celular,
								tpa.celular_coorporativo,
							    tpa.correo,
							    tbl_cargos.nombre as cargo_nombre,
							    tbl_areas.nombre as area_nombre,									
							    tpa.sistema_id,
							    e.nombre AS empresa,
							    tpa.zona_id,
							    tbl_zonas.nombre as zona_nombre,
							    tpa.estado
							FROM
							    tbl_personal_apt  tpa
							LEFT JOIN
							    tbl_areas ON tpa.area_id  = tbl_areas.id
							LEFT JOIN
							    tbl_cargos ON tpa.cargo_id=tbl_cargos.id
							LEFT JOIN
							    tbl_zonas ON tpa.zona_id=tbl_zonas.id
							LEFT JOIN 
								tbl_razon_social e ON tpa.razon_social_id = e.id");
		$list = array();
		while ($li = $list_query->fetch_assoc()) {
			$list[] = $li;
		}

		$list_cols = array();
		$list_cols["id"] = "ID";

		$list_cols["nombre"] = "NOMBRE";
		$list_cols["apellido_paterno"] = "AP. PATERNO";
		$list_cols["apellido_materno"] = "AP. MATERNO";
		$list_cols["fecha_ingreso_laboral"] = "F. INGRESO";
		/*
			$list_cols["consorcio_id"]="CONSORCIO";
		*/
		$list_cols["dni"] = "DNI";
		$list_cols["telefono"] = "TELEFONO";
		$list_cols["celular"] = "CELULAR";
		$list_cols["celular_cooporativo"] = "CELULAR";
		$list_cols["correo"] = "CORREO";

		$list_cols["cargo_nombre"] = "CARGO";
		$list_cols["area_nombre"] = "AREA";
		$list_cols["empresa"] = "EMPRESA";
		$list_cols["zona_nombre"] = "ZONA";
		//$list_cols["sistema_id"]="SISTEMA";
		$list_cols["estado"] = "ESTADO";
		$list_cols["opciones"] = "OPCIONES";

	?>
		<input type="hidden" class="export_personal_filename" value="export_personal_<?php echo date("c"); ?>">

		<div class="row">
			<div class="col-xs-12 col-sm-8 col-md-10 mt-3">
				<div class="form-group">
					<label for="txtSearchUser">Buscar usuario</label>
					<input type="text" id="txtSearchUser" name="txtSearchUser" class="form-control" placeholder="Buscar usuario...">
				</div>
			</div>
			<div class="col-xs-12 col-sm-4 col-md-2 mt-3">
				<div class="form-group">
					<label for="cbLimit">Mostrar: </label>
					<select id="cbLimit" name="cbLimit" class="form-control">
						<option value="10">10</option>
						<option value="25">25</option>
						<option value="50">50</option>
						<option value="100">100</option>
						<option value="250">250</option>
						<option value="500">500</option>
						<option value="1000">1000 (No hagas eso)</option>
					</select>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-xs-12 mt-3">
				<div class="table-responsive">
					<table id="tbl_personal" class="table table-striped table-hover table-condensed table-bordered dt-responsive" cellspacing="0" width="100%">
					</table>
				</div>
			</div>
		</div>
		<div class="col-xs-12 mt-3">
			<div class="pull-right" id="pagination"></div>
		</div>
	<?php
	}
	?>
</div>

<!-- Modal para importar personal y usuarios masivamente -->
<div class="modal fade" id="personal_import_modal">
	<div class="modal-dialog">
		<div class="modal-content modal-rounded">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"><span class="glyphicon glyphicon-import"></span> Importar Personal masivamente</h4>
			</div>
			<div class="modal-body no-pad">
				<form method="POST" action="sys/set_personal_descarga.php" enctype="multipart/form-data">
					<label class="h4 strong block">Descargar formato de importación</label>
					<input type="hidden" name="tipo_descarga" value="descarga_formato">
					<button type="submit" class="btn btn-info active"><span class="glyphicon glyphicon-download-alt"></span> Descargar</button>
				</form>
				<br>
				<p>
					<button class="btn btn-dark" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
						<span class="glyphicon glyphicon-info-sign"></span> Notas a considerar para completar el formato (click para ver)
					</button>
				</p>
				<div class="collapse" id="collapseExample">
					<div class="card card-body" style="font-size: 14px;">
						<p align="justify"><b>NOTA 1:</b> Para la creación de nuevo personal y usuarios, todos los campos son OBLIGATORIOS, a excepción de las columnas de: ID_AREA, ID_CARGO, CORREO, TELEFONO, CELULAR, ID_ZONA, USUARIO, COMPLEMENTO.</p>
						<p align="justify"><b>NOTA 2:</b> Para la creación de usuarios de personal ya existente, sólo es OBLIGATORIO completar las columnas de DNI, ID_SISTEMA, ID_GRUPO</p>
						<p align="justify"><b>NOTA 3:</b> Si se quiere cambiar el cargo de personal ya existente, a parte de completar las columnas mencionadas en la NOTA 2, se puede colocar el ID_CARGO del nuevo cargo a actualizar.</p>
						<p align="justify"><b>NOTA 4:</b> El campo USUARIO no es obligatorio completarlo, en caso de no hacerlo, se genera automaticamente siguiendo el patrón: <i>nombre.apellido_paterno</i></p>
						<p align="justify"><b>NOTA 5:</b> El campo COMPLEMENTO ayuda si se quiere diferenciar al campo USUARIO, si se escribe admin en COMPLEMENTO, el usuario se crearía así: <i>nombre.apellido_paterno.admin</i></p>
					</div>
				</div>
				<br>
				<div>
					<label class="h4 strong block">IDs para completar el formato</label>
					<button type="button" class="btn btn-danger active info_to_view_import"><span class="glyphicon glyphicon-modal-window"></span> Ver información</button>
				</div>
				<br>
				<form method="POST" action="sys/set_personal_importar.php" enctype="multipart/form-data" id="personal_import_form">
					<label class="h4 strong block">Archivo (CSV)</label>
					<input type="file" class="form-control" name="file_personal_import" id="file_personal_import" accept=".csv" style="display: none;" required>
					<label class="uploader_file_name" for="file_personal_import" style="width: unset;">
						<div class="btn btn-primary upload-btn active" data-form="personal_import_form"><span class="glyphicon glyphicon-import"></span> Seleccione Archivo</div>
					</label>
					<label class="h5" id="label_name_File"></label>
				</form>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-success" id="personal_import_btn_upload" form="personal_import_form" disabled><span class="glyphicon glyphicon-cloud-upload"></span> Subir</button>
				<button type="button" class="btn btn-light" data-dismiss="modal">Cerrar</button>
			</div>
		</div>
	</div>
</div>

<!-- Modal para descargar los archivos importados por día -->
<?php
$dt_fecha = new DateTime();
$dt_fecha_inicio = $dt_fecha->format('Y-m-d\T00:00');
$dt_fecha_final = $dt_fecha->format('Y-m-d\TH:i');
?>
<div class="modal fade" id="massive_download_btn">
	<div class="modal-dialog">
		<div class="modal-content modal-rounded">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"><span class="glyphicon glyphicon-download-alt"></span> Descargar registros importados masivamente</h4>
			</div>
			<div class="modal-body">
				<form id="form_personal_export_massive" method="POST" action="sys/set_personal_descarga.php" enctype="multipart/form-data">
					<label class="h4 strong block">Elija un rango de fechas</label>
					<div class="col-sm-6">
						<label class="h5">Inicio:</label>
						<input type="datetime-local" name="fecha_registro_masivo_inicio" value="<?php echo $dt_fecha_inicio; ?>">
					</div>
					<div class="col-sm-6">
						<label class="h5">Final:</label>
						<input type="datetime-local" name="fecha_registro_masivo_final" value="<?php echo $dt_fecha_final; ?>">
					</div>
					<input type="hidden" name="tipo_descarga" value="descarga_registro_masivo">
					<br>
					<br>
				</form>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-info" form="form_personal_export_massive"><span class="glyphicon glyphicon-download-alt"></span> Descargar</button>
				<button type="button" class="btn btn-warning" data-dismiss="modal">Cerrar</button>
			</div>
		</div>
	</div>
</div>

<!-- Modal para ver los usuarios de cada dni -->
<div class="modal fade" id="modal_user_to_dni">
	<div class="modal-dialog modal-sm">
		<div class="modal-content modal-rounded">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"><span class="glyphicon glyphicon-search"></span> Comprobar usuarios por DNI</h4>
			</div>
			<div class="modal-body">
				<form id="form_dni_to_search" method="POST" action="sys/set_personal_dni_to_user.php">
					<label class="h4 strong block">Copie los DNI a buscar aquí abajo</label>
					<p>Coloque los DNI uno debajo del otro</p>
					<textarea name="dni_to_search_textarea" id="dni_to_search_textarea" cols="34" rows="10"></textarea>
				</form>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-info" form="form_dni_to_search" id="dni_to_search_submit"><span class="glyphicon glyphicon-search"></span> Buscar</button>
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
			</div>
		</div>
	</div>
</div>

<!-- Para descargar el formato -->
<?php
$area_command = "SELECT id, nombre FROM tbl_areas";
$area_datos = $mysqli->query($area_command);

$cargo_command = "SELECT id, nombre FROM tbl_cargos";
$cargo_datos = $mysqli->query($cargo_command);

$zona_command = "SELECT id, nombre FROM tbl_zonas";
$zona_datos = $mysqli->query($zona_command);

$sistema_command = "SELECT id, nombre FROM tbl_sistemas";
$sistema_datos = $mysqli->query($sistema_command);

$grupo_command = "SELECT id, nombre FROM tbl_usuarios_grupos WHERE id != 7";
$grupo_datos = $mysqli->query($grupo_command);

?>
<div class="modal fade" id="personal_formatos_modal" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content modal-rounded">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Visualización de ID's</h4>
			</div>
			<div class="modal-body">
				<div class="container">
					<div class="row">
						<div class="col-sm-3">
							<h4>Área</h4>
							<div style="height: 500px;overflow-y: scroll;">
								<table class="table table-condensed table-bordered">
									<thead>
										<tr class="bg-secondary">
											<th>ID</th>
											<th>Área</th>
										</tr>
									</thead>
									<tbody>
										<?php
										foreach ($area_datos as $areas) {
										?>
											<tr>
												<td><?php echo $areas['id']; ?></td>
												<td><?php echo $areas['nombre']; ?></td>
											</tr>
										<?php
										}
										?>
									</tbody>
								</table>
							</div>
						</div>
						<div class="col-sm-2">
							<h4>Cargo</h4>
							<div style="height: 500px;overflow-y: scroll;">
								<table class="table table-condensed table-bordered">
									<thead>
										<tr class="bg-secondary">
											<th>ID</th>
											<th>Cargo</th>
										</tr>
									</thead>
									<tbody>
										<?php
										foreach ($cargo_datos as $cargos) {
										?>
											<tr>
												<td><?php echo $cargos['id']; ?></td>
												<td><?php echo $cargos['nombre']; ?></td>
											</tr>
										<?php
										}
										?>
									</tbody>
								</table>
							</div>
						</div>
						<div class="col-sm-2">
							<h4>Zona</h4>
							<div style="height: 500px;overflow-y: scroll;">
								<table class="table table-condensed table-bordered">
									<thead>
										<tr class="bg-secondary">
											<th>ID</th>
											<th>Zona</th>
										</tr>
									</thead>
									<tbody>
										<?php
										foreach ($zona_datos as $zonas) {
										?>
											<tr>
												<td><?php echo $zonas['id']; ?></td>
												<td><?php echo $zonas['nombre']; ?></td>
											</tr>
										<?php
										}
										?>
									</tbody>
								</table>
							</div>
						</div>
						<div class="col-sm-2">
							<h4>Sistema</h4>
							<div style="height: 500px;overflow-y: scroll;">
								<table class="table table-condensed table-bordered">
									<thead>
										<tr class="bg-secondary">
											<th>ID</th>
											<th>Sistema</th>
										</tr>
									</thead>
									<tbody>
										<?php
										foreach ($sistema_datos as $sistemas) {
										?>
											<tr>
												<td><?php echo $sistemas['id']; ?></td>
												<td><?php echo $sistemas['nombre']; ?></td>
											</tr>
										<?php
										}
										?>
									</tbody>
								</table>
							</div>
						</div>
						<div class="col-sm-3">
							<h4>Grupo</h4>
							<div style="height: 500px;overflow-y: scroll;">
								<table class="table table-condensed table-bordered">
									<thead>
										<tr class="bg-secondary">
											<th>ID</th>
											<th>Grupo</th>
										</tr>
									</thead>
									<tbody>
										<?php
										foreach ($grupo_datos as $grupos) {
										?>
											<tr>
												<td><?php echo $grupos['id']; ?></td>
												<td><?php echo $grupos['nombre']; ?></td>
											</tr>
										<?php
										}
										?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-warning" data-dismiss="modal">Cerrar</button>
			</div>
		</div>
	</div>
</div>

<!-- Para descargar de registros de personal -->
<?php

?>
<!-- Modal para descargar registros de personal -->
<div class="modal fade" id="personal_download_modal">
	<div class="modal-dialog modal-sm">
		<div class="modal-content modal-rounded">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"><span class="glyphicon glyphicon-import"></span> Descarga de registros</h4>
			</div>
			<div class="modal-body">
				<label class="h4 strong block">Filtrar por:</label>
				<form method="POST" action="sys/set_personal_descarga.php" enctype="multipart/form-data" id="personal_download_form">
					<label class="h4 strong block">Área</label>
					<select class="select2 filter_donwload_data" name="area_download_id" style="width: 100%">
						<option value="all">--- TODOS ---</option>
						<?php
						foreach ($area_datos as $areas) {
						?>
							<option value="<?php echo $areas['id']; ?>"><?php echo $areas['nombre']; ?></option>
						<?php
						}
						?>
					</select>
					<label class="h4 strong block">Cargo</label>
					<select class="select2 filter_donwload_data" name="cargo_download_id" style="width: 100%">
						<option value="all">--- TODOS ---</option>
						<?php
						foreach ($cargo_datos as $cargos) {
						?>
							<option value="<?php echo $cargos['id']; ?>"><?php echo $cargos['nombre']; ?></option>
						<?php
						}
						?>
					</select>
					<label class="h4 strong block">Zona</label>
					<select class="select2 filter_donwload_data" name="zona_download_id" style="width: 100%">
						<option value="all">--- TODOS ---</option>
						<?php
						foreach ($zona_datos as $zonas) {
						?>
							<option value="<?php echo $zonas['id']; ?>"><?php echo $zonas['nombre']; ?></option>
						<?php
						}
						?>
					</select>
					<label class="h4 strong block">Estado</label>
					<select class="select2 filter_donwload_data" name="estado_download_id" style="width: 100%">
						<option value="all">--- TODOS ---</option>
						<option value="1">Activos</option>
						<option value="0">Inactivos</option>
					</select>
					<input type="hidden" name="tipo_descarga" value="descarga_registro_personal">
				</form>
			</div>
			<div class="modal-footer">
				<button type="submit" class="btn btn-success" id="personal_download_btn" form="personal_download_form"><span class="glyphicon glyphicon-cloud-upload"></span> Descargar</button>
				<button type="button" class="btn btn-light" data-dismiss="modal">Cerrar</button>
			</div>
		</div>
	</div>
</div>