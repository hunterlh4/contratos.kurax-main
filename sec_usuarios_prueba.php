<link rel="stylesheet" href="css/simplePagination.css">
<?php error_reporting(0); ?>
<script src="js/sweetalert2@11.js"></script>
<div class="content container-fluid">
	<div class="page-header wide">
		<div class="row">
			<div class="col-xs-12 text-center">
				<div class="page-title"><i class="icon icon-inline fa fa-fw fa-users"></i> <span id="title-text">Usuarios editado</span></div>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12">
				<div id="btns_opt_user" style="display: none">
					<button type="button" data-then="return" class="return_btn btn btn-rounded btn-default mb-1" data-button="return" style="margin-bottom: 12px;">
						<i class="glyphicon glyphicon-arrow-left"></i>
						Regresar
					</button>
					<button type="button" data-then="exit" class="save_btn btn btn-rounded btn-success mb-1" data-button="save">
						<i class="glyphicon glyphicon-floppy-save"></i>
						Guardar y Salir
					</button>
					<button type="button" data-then="reload" class="save_btn btn btn-rounded btn-success mb-1" data-button="save">
						<i class="glyphicon glyphicon-floppy-save"></i>
						Guardar
					</button>

					<!-- Este botón de permisos solo se muestra al editar un usuario -->

					<button class="btn btn-rounded btn-primary btn_permisos_usuario_seleccionado mb-1" data-button="permissions">
						<i class="glyphicon glyphicon-equalizer"></i>
						Permisos
					</button>

				</div>
				<div id="btns_list_user">
					<?php
					if (array_key_exists(26, $usuario_permisos) && in_array("new", $usuario_permisos[26])) {
					?>
						<button id="btn_new_user" class="btn btn-rounded btn-min-width btn-success mb-2">
							<i class="fa fa-user-plus"></i> Agregar
						</button>
					<?php
					}
					if (array_key_exists(26, $usuario_permisos) && in_array("new", $usuario_permisos[26])) {
					?>
						<!-- http://contratos.kurax-main.test:90/?sec_id=usuarios&sub_sec_id=usuarios -->
						<!-- se puede quitar o en la tabla, editado por alex (usuarios)-->
						<button id="btn_crear_grupo_usuarios" name="btn_crear_grupo_usuarios" class="btn btn-rounded btn-warning mb-2">
							<i class="fa fa-group"></i> Roles
						</button>
					<?php
					}

					if (array_key_exists(26, $usuario_permisos) && in_array("permissions", $usuario_permisos[26])) {
					?>
						<!-- <button id="btn_asignar_permisos_multiple_usuarios" class="btn btn-rounded btn_asignar_permisos_multiple_usuarios mb-2" data-button="permissions">
							<i class="glyphicon glyphicon-equalizer"></i> cargando ...
						</button> -->
					<?php
					}
					if (array_key_exists(26, $usuario_permisos) && in_array("permissions", $usuario_permisos[26])) {
					?>
						<!-- editado por alex (usuarios) -->
						<!-- <button id="btn_cerrar_usuarios_dni" class="btn btn-rounded btn-primary mb-2 btn_cerrar_usuarios_dni" data-button="permissions">
							<i class="fa fa-user-times"></i> Cerrar usuarios por DNI
						</button> -->
					<?php
					}
					?>
					<button id="btn_descarga_usuarios_activos" class="btn btn-rounded btn-success mb-2 btn_descarga_usuarios_activos" data-button="permissions">
						<i class="fa fa-download"></i> Descargar Usuarios Activos
					</button>
					<?php
					if (array_key_exists(26, $usuario_permisos) && in_array("new", $usuario_permisos[26])) {
					?>
						<!-- editado por alex (usuarios) -->
						<!-- <button id="btn_importacion_validacion_2fa" class="btn btn-rounded btn-primary mb-2 btn_importacion_validacion_2fa" data-button="2fa">
							<i class="fa fa-upload"></i> Importación para Validación 2FA
						</button> -->
					<?php
					}
					?>
					<button id="btnActivos" class="btn btn-rounded btn-default pull-right mb-2">Mostrar Inactivos</button>
					<!-- editado por alex (usuarios) -->
					<!-- <button id="btnPersonal_Inactivo" class="btn btn-rounded btn-danger pull-right mb-2">Filtrar por Personal inactivo</button> -->
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade" id="modal_grupo_user" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true"></div>
	<input type="hidden" id="hid_indicador_grupos_usuario" name="hid_indicador_grupos_usuario" value="0">
	<div id="div_usuarios_permisos"></div>
	<!-- Modal para GRUPO -->
	<?php //include("sys/set_usuarios_grupo.php"); 
	?>

	<!-- Modal para PERMISOS -->
	<?php //include("sys/set_usuarios_permisos.php"); 
	?>

	<!-- Para agregar nuevo usuario -->
	<?php
	$personal_arr = array();
	$personal_arr[0] = "Ninguno";
	$personal_command = "SELECT a.id, nombre, apellido_paterno, apellido_materno FROM tbl_personal_apt a LEFT JOIN tbl_usuarios b on a.id = b.personal_id  WHERE a.estado = 1  AND b.personal_id IS NULL;";
	$result = $mysqli->query($personal_command);
	while ($personal = $result->fetch_assoc()) {
		$personal_arr[$personal["id"]] = $personal["nombre"] . " " . $personal["apellido_paterno"] . " " . $personal["apellido_materno"];
	}

	$sistemas_arr = array();
	$sistemas_arr[0] = "-- Seleccione --";
	$sistemas_command = "SELECT id, nombre FROM tbl_sistemas";
	$result = $mysqli->query($sistemas_command);
	while ($sistema = $result->fetch_assoc()) {
		$sistemas_arr[$sistema["id"]] = $sistema["nombre"];
	}

	$grupos = [["id" => 0, "name" => "Sin Grupo"]];
	$grupos_command = "SELECT id, nombre FROM tbl_usuarios_grupos WHERE estado = 1";
	$result = $mysqli->query($grupos_command);
	foreach ($result as $row)
		$grupos[] = ["id" => $row["id"], "name" => $row["nombre"]];
	?>
	<input type="hidden" class="save_data" name="id" value="">
	<input type="hidden" class="save_data" name="type_user" value="new">

	<div id="opc_user" class="col-xs-12 col-sm-8 col-md-6 col-sm-offset-2 col-md-offset-3" style="display: none">
		<div class="panel">
			<div class="panel-heading">
				<div class="panel-title"><i class="icon fa fa-file-text-o muted"></i> Datos del Usuario</div>
			</div>
			<div id="panel-datos_2" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="panel-collapse-1-heading">
				<div class="panel-body wide">
					<div class="form-group">
						<label class="col-xs-4 control-label" for="varchar_usuario">Usuario</label>
						<div class="col-xs-8">
							<input type="text" oninput="validarTextoUsuario(this)" maxlength="25" class="form-control form-control-rounded save_data" name="usuario" value="" placeholder="Ingrese usuario">
							<label style="color: red;">Cantidad máxima de caracteres: 25</label>
							<label class="pull-right"><span id="save_data_contador_caracteres">0</span>/25</label>
						</div>
					</div>
					<div id="btn_contrasena_user" class="form-group" style="display: none">
						<label class="col-xs-4 control-label" for="varchar_usuario">Contraseña</label>
						<div class="col-xs-8">
							<button class="btn btn-danger btn-sm usuario_restaurar_pass_btn">Crear/restaurar contraseña</button>
						</div>
					</div>
					<div class="form-group">
						<label class="col-xs-4 control-label">Personal</label>
						<div class="col-xs-8">
							<select class="select2 save_data" id="personal_id" name="personal_id" style="width: 100%">
								<?php
								foreach ($personal_arr as $per_id => $per_nombre) {
								?>
									<option value="<?php echo $per_id; ?>"><?php echo $per_nombre; ?></option>
								<?php
								}
								?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-xs-4 control-label" for="varchar_usuario">Sistema</label>
						<div class="col-xs-8">
							<select class="select2 save_data" name="sistema_id" style="width: 100%">
								<?php
								foreach ($sistemas_arr as $sis_id => $sis_nombre) {
								?>
									<option value="<?php echo $sis_id; ?>"><?php echo $sis_nombre; ?></option>
								<?php
								}
								?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-xs-4 control-label" for="varchar_usuario">Grupo</label>
						<div class="col-xs-8">
							<select class="select2 save_data" name="grupo_id" style="width: 100%">
								<?php foreach ($grupos as $grupo): ?>
									<option value="<?php echo $grupo["id"]; ?>"><?php echo $grupo["name"]; ?></option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
					<div class="form-group">
						<label class="col-xs-4 control-label" for="varchar_usuario">Estado</label>
						<div class="col-xs-8">
							<input
								id="switch_estado"
								class="switch"
								type="checkbox"
								data-toggle="toggle"
								checked="checked"
								data-table="tbl_usuarios"
								data-col="estado"
								data-id=""
								data-on-value="1"
								data-off-value="0"
								data-on="activo" data-off="no activo" data-onstyle="success" data-offstyle="danger" data-size="mini" data-width="75"
								value="1">
						</div>
					</div>
					<br>
					<div class="form-group">
						<label class="col-xs-4 control-label mt-1" for="varchar_usuario">Lista Blanca</label>
						<div class="col-xs-8 mt-1">
							<input
								id="switch_ip_restrict"
								class="switch"
								type="checkbox"
								data-toggle="toggle"
								data-table="tbl_usuarios"
								data-col="ip_restrict"
								data-id=""
								data-on-value="1"
								data-off-value="0"
								data-on="activo" data-off="no activo" data-onstyle="success" data-offstyle="danger" data-size="mini" data-width="75">
						</div>
					</div>
					<div class="form-group">
						<label class="col-xs-4 control-label mt-1" for="varchar_usuario">Validación 2FA</label>
						<div class="col-xs-8 mt-1">
							<input
								id="switch_validacion_2fa"
								class="switch"
								type="checkbox"
								data-toggle="toggle"
								data-table="tbl_usuarios"
								data-col="validacion_2fa"
								data-id=""
								data-on-value="1"
								data-off-value="0"
								data-on="Activo" data-off="Inactivo" data-onstyle="success" data-offstyle="danger" data-size="mini" data-width="75">
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

	<div id="opc_tabla_usuario" class="mt-0p5">
		<input type="hidden" class="export_usuarios_filename" value="export_usuarios_<?php echo date("c"); ?>">

		<div class="row">
			<div class="col-xs-12 col-sm-8 col-md-8">
				<div class="form-group">
					<label for="txtSearchUser">Buscar usuario</label>
					<input type="text" id="txtSearchUser" name="txtSearchUser" class="form-control form-control-rounded" placeholder="Buscar usuario...">
				</div>
			</div>
			<div class="col-xs-12 col-sm-4 col-md-2">
				<div class="form-group">
					<label for="cbLimit">Mostrar: </label>
					<select id="cbLimit" name="cbLimit" class="form-control form-control-rounded">
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
			<div class="col-xs-12 mt-0p5">
				<div class="table-responsive">
					<table id="tbl_usuarios" class="table table-striped table-hover table-condensed table-bordered dt-responsive" cellspacing="0" width="100%">
					</table>
				</div>
			</div>
		</div>
		<div class="pull-right">
			<div id="pagination"></div>
		</div>
	</div>
	<!------------------------------------------------------------------ 
	Seccion para grid de auditoria 
---------------------------------------------------------------------->
	<link rel="stylesheet" type="text/css" href="js/ext-6.2.0/build/classic/theme-crisp/resources/theme-crisp-all.css" />
	<link rel="stylesheet" href="https://unpkg.com/leaflet@1.0.3/dist/leaflet.css" />
	<style type="text/css">
		.x-grid-cell:hover {
			font-weight: bold;
		}

		h1 {
			margin-top: 50px;
			text-align: center;
		}

		#fi-button-msg {
			border: 2px solid #ccc;
			padding: 5px 10px;
			background: #eee;
			margin: 5px;
			float: left;
		}

		.x-debug .x-form-file-wrap .x-form-file-input {
			filter: progid:DXImageTransform.Microsoft.Alpha(Opacity=0.6);
			opacity: 0.6;
			background-color: gray;
		}

		#map {
			width: 500px;
			height: 600px;
		}
	</style>
	<!-- ExtJS library: all widgets -->
	<script type="text/javascript" src="js/ext-6.2.0/build/ext-all.js"></script>
	<script type="text/javascript" src="js/ext-6.2.0/classic/locale/overrides/es/ext-locale-es.js"></script>
	<!-- overrides to base library -->
	<script>
		var protoAppAuditoria = {};
	</script>
	<script type="text/javascript" src="js/model/jsModel.js"></script>
	<script type="text/javascript" src="js/jsAuditoria.js"></script>
	<script type="text/javascript" src="https://unpkg.com/leaflet@1.0.3/dist/leaflet.js"></script>
	<input type="hidden" id="hid_usuario_id" name="hid_usuario_id" value="0" />
	<input type="hidden" id="hid_personal_id" name="hid_personal_id" value="0" />
	<input type="hidden" id="hid_usuario" name="hid_usuario" value="" />
	<div id="div_general_auditoria"></div>
	<div id="leafletmap"></div>
	<!------------------------------------------------------------------>

	<div id="opc_tbl_permisos_auditoria" class="mt-0p5" style="display: none; margin-top: 12px!important;">
		<div class="row">
			<div class="col-sm-6 col-md-6 col-lg-2">
				<div class="form-group">
					<div class="control-label">Fecha Inicio:</div>
					<div class="input-group">
						<input
							type="date"
							id="log_permisos_fecha_inicio"
							class="form_control input_text item_config"
							name="log_permisos_fecha_inicio"
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
							id="log_permisos_fecha_fin"
							class="form_control input_text item_config"
							name="log_permisos_fecha_fin"
							value="<?php echo date('Y-m-d') ?>" />
					</div>
				</div>
			</div>
			<div class="col-sm-5 col-md-5 col-lg-2 col-xs-offset-2 col-sm-6 col-lg-3 pull-left">
				<div class="form-group">
					<button class="btn btn-warning" type="button" onclick="protoAppAuditoria.interno();">Auditoria</button>
				</div>
			</div>
			<div class="col-xs-12 col-sm-6 col-md-6 col-lg-2 col-xs-offset-2 col-sm-6 col-lg-3 pull-right">
				<br>
				<div class="form-group">
					<button id="btn_consultar_log_permisos" class="btn btn-block btn-rounded btn-success">
						<span class="glyphicon glyphicon-search"></span>
						Consultar
					</button>
				</div>
			</div>
			<div class="col-md-12">
				<h3>Log del Usuario: <b id="log_de_usuario"></b></h3>
			</div>
		</div>

		<div class="row">
			<div class="col-xs-12 mt-0p5">
				<div class="table-responsive">
					<table id="table_tbl_permisos_auditoria" class="table table-striped table-hover table-condensed table-bordered dt-responsive" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th class="text-center">Fecha</th>
								<th class="text-center">Usuario</th>
								<th class="text-center">Menú</th>
								<th class="text-center">Permiso (botón)</th>
								<th class="text-center">Acción</th>
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

	<!-- Modal para cambiar la contraseña del usuario en sesión -->
	<div class="modal" id="sec_usuarios_change_pass_modal">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
				<form class="form" id="sec_usuarios_change_pass_form">
					<div class="modal-header">
						<?php if ($login["password_changed"]) { ?><button type="button" class="close close_btn" data-dismiss="modal"><span aria-hidden="true">&times;</span></button><?php } ?>
						<h4 class="modal-title">Cambiar Contraseña</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label>Contraseña actual:</label>
							<input class="form-control save_data" type="password" name="current_password" required="required">
						</div>
						<div class="form-group">
							<label>Nueva contraseña:</label>
							<input class="form-control save_data" type="password" name="new_password" required="required">
						</div>
						<div class="form-group">
							<label>Confirmar Nueva contraseña:</label>
							<input class="form-control save_data" type="password" name="new_repassword" required="required">
						</div>
						<div class="form-group">
							<label>Seguridad:</label>
							<div class="progress m-0">
								<div class="progress-bar" id="progress-bar-security" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
								</div>
								<div style="position: absolute; left: 0px; width: 100%; text-align: center; z-index: 1; padding: 0px; margin: 0px">
									<span id="progress-bar-text">Muy debil (0/100)</span>
								</div>
							</div>
						</div>
						<div class="w-100" id="container-alert-password"></div>
					</div>
					<div class="modal-footer">
						<div class="form-group ">
							<button id="button_change_password" class="btn btn-success" title="Abrir"><span class='glyphicon glyphicon-floppy-save'></span> Guardar</button>
							<?php if ($login["password_changed"]) { ?><button class="btn btn-default close_btn" data-dismiss="modal">Cancelar</button><?php } ?>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>

	<!-- Modal para cambiar la validación de 2 factores -->
	<div class="modal fade" id="importar_validacion_2fa_modal">
		<div class="modal-dialog">
			<div class="modal-content modal-rounded">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><span class="glyphicon glyphicon-import"></span> Importar para Validación 2FA</h4>
				</div>
				<div class="modal-body">
					<label class="h4 strong block">Archivo (CSV)</label>
					<label class="">En este orden las columnas:</label>
					<oL>
						<li>DNI</li>
						<li>Nuevo Celular Corporativo</li>
						<li>Id operador corporativo (1:AWS, 2:INTERMAX)</li>
						<li>Estado Validación 2FA (1: activo)</li>
					</ol>
					<input type="file" class="form-control" name="file_imp_2fa" id="file_imp_2fa" accept=".csv" style="display: none;" required>
					<label class="uploader_file_name" for="file_imp_2fa" style="width: unset;">
						<div class="btn btn-primary upload-btn active" data-form="imp_2fa_form"><span class="glyphicon glyphicon-import"></span> Seleccione Archivo</div>
					</label>
					<label class="h5" id="label_name_File_2fa"></label>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-success btn-rounded" id="btn_imp_2fa" disabled><span class="glyphicon glyphicon-cloud-upload"></span> Importar</button>
					<button type="button" class="btn btn-light" data-dismiss="modal">Cerrar</button>
				</div>
			</div>
		</div>
	</div>

	<div class="modal" id="sec_usuarios_modal_historial_claves">
		<div class="modal-dialog modal-md">
			<div class="modal-content">
				<form class="form" id="sec_usuarios_change_pass_form">
					<div class="modal-header">
						<button type="button" class="close close_btn" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title text-center">Historial de Cambio de Contraseñas</h4>
						<h5 class="modal-title text-center" id="title-modal-historial-cambio"></h5>
					</div>
					<div class="modal-body">
						<!-- Nav tabs -->
						<ul class="nav nav-tabs" role="tablist">
							<li role="presentation" class="active"><a href="#tab_cambio_contrasena" aria-controls="tab_cambio_contrasena" role="tab" data-toggle="tab">Cambios de Contraseñas</a></li>
							<li role="presentation"><a href="#tab_reseteos_contrasena" aria-controls="tab_reseteos_contrasena" role="tab" data-toggle="tab">Reseteos de Contraseñas</a></li>
						</ul>

						<!-- Tab panes -->
						<div class="tab-content">
							<div role="tabpanel" class="tab-pane active" id="tab_cambio_contrasena">
								<div class="table-responsive">
									<table id="tbl_historial_clave" class="table table-bordered table-condensed" cellspacing="0" width="100%">
										<thead>
											<tr>
												<th class="text-center">IP</th>
												<th class="text-center">Fecha Cambio</th>
											</tr>
										</thead>
									</table>
								</div>
							</div>
							<div role="tabpanel" class="tab-pane" id="tab_reseteos_contrasena">
								<div class="table-responsive">
									<table id="tbl_reseteos_clave" class="table table-bordered table-condensed" cellspacing="0" width="100%">
										<thead>
											<tr>
												<th class="text-center">IP</th>
												<th class="text-center">Fecha Reseteo</th>
												<th class="text-center">Usuario</th>
											</tr>
										</thead>
									</table>
								</div>
							</div>
						</div>


					</div>
					<div class="modal-footer">
						<div class="form-group ">
							<button class="btn btn-default close_btn" data-dismiss="modal">Cerrar</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>