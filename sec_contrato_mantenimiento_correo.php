<!-- INICIO MODAL AGREGAR CAMBIO DE CUOTA O MONEDA -->
<div id="modalMantemientoCorreos" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_title_mantenimiento_correo">Correos</h4>
			</div>
			<div class="modal-body">

				<!-- Nav tabs -->
				<ul class="nav nav-tabs" role="tablist" id="tab_mantenimiento">
					<li role="presentation" class="active">
						<a href="#tab-usuarios" aria-controls="tab-usuarios" role="tab" data-toggle="tab">Usuarios</a>
					</li>
					<li role="presentation">
						<a href="#tab-area" aria-controls="tab-area" role="tab" data-toggle="tab">Grupo de Área</a>
					</li>
				</ul>

				<!-- Tab panes -->
				<div class="tab-content">
					<div role="tabpanel" class="tab-pane active" id="tab-usuarios">
						<?php
						global $mysqli;
						$menu_id = "";
						$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'contrato' AND sub_sec_id = 'mantenimiento' LIMIT 1");
						while ($r = $result->fetch_assoc())
							$menu_id = $r["id"];

						if (!array_key_exists($menu_id, $usuario_permisos) || !in_array("nuevo_correo", $usuario_permisos[$menu_id])) {
							//nuevo_director
						} else {
						?>
						<fieldset class="dhhBorder">
							<legend class="dhhBorder">Registro de Usuarios</legend>
							<form method="POST" id="Frm_RegistroCorreo" enctype="multipart/form-data" autocomplete="off">
								<input id="modal_mant_corr_id" type="hidden" class="form-control">
								<input id="modal_mant_corr_metodo_id" type="hidden" class="form-control">

								<div class="row">
									<div class="col-md-10">
										<div class="form-group">
											<label>Usuario</label>
											<select id="modal_mant_corr_usuario_id" name="modal_mant_corr_usuario_id" class="form-control select2"></select>
										</div>
									</div>
									<div class="col-md-2">
										<div class="form-group">
											<label></label>
											<button type="submit" class="btn form-control btn-info" id="btn-form-registro-correo-metodo-registrar">Registrar</button>
										</div>
									</div>
								</div>
							</form>
						</fieldset>
						<?php
						}
						?>
						<hr>
						<div class="col-md-12">
							<div class="table-responsive">
								<table class="table display responsive" style="width:100%" id="tbl_modal_correo">
									<thead>
										<tr>
											<th class="text-center">#</th>
											<th class="text-center">Nombres</th>
											<th class="text-center">Usuario</th>
											<th class="text-center">Correo</th>
											<th class="text-center">Estado</th>
											<th class="text-center">Acciones</th>
										</tr>
									</thead>
									<tbody>
									</tbody>
								</table>
							</div>
						</div>
					</div>

					<div role="tabpanel" class="tab-pane" id="tab-area">
						<!-- Aquí iría el contenido relacionado con el Grupo de Área -->
						<fieldset class="dhhBorder">
							<legend class="dhhBorder">Registro de Grupo de Área</legend>
							<!-- Formulario para el registro de área -->
							<form method="POST" id="Frm_RegistroArea" enctype="multipart/form-data" autocomplete="off">
								<input id="modal_mant_area_id" type="hidden" class="form-control">

								<div class="row">
									<div class="col-md-10">
										<div class="form-group">
											<label>Grupo de Área</label>
											<select id="modal_mant_area_grupo_id" name="modal_mant_area_grupo_id" class="form-control select2"></select>
										</div>
									</div>
									<div class="col-md-2">
										<div class="form-group">
											<label></label>
											<button type="submit" class="btn form-control btn-info" id="btn-form-registro-area-registrar">Registrar</button>
										</div>
									</div>
								</div>
							</form>
						</fieldset>
						<hr>
                        <div class="col-md-12">
							<div class="table-responsive" id="correo_por_area_div_tabla">
								<table class="table display responsive" style="width:100%"  id="correo_por_area_datatable">
									<thead>
										<tr>
                                            <th class="text-center">#</th>
											<th class="text-center">Grupo de Área</th>
											<th class="text-center">Estado</th>
											<th class="text-center">Acciones</th>
										</tr>
									</thead>
									<tbody>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>

			</div>
			<div class="modal-footer">
				<!-- Footer opcional -->
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL AGREGAR CAMBIO DE CUOTA O MONEDA -->


<!-- INICIO MODAL VER USUARIOS -->
<div id="modalMantemientoCorreosUsuariosPorArea" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" aria-label="Close" id="closeModalUsuariosPorArea"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_title_mantenimiento_usuarios_por_area">Usuarios Registrados</h4>
			</div>
			<div class="modal-body">
				<div class="tab-content">
					<div class="col-md-12">
						<div class="table-responsive" id="usuarios_registrados_div_tabla">
							<table class="table display responsive" style="width:100%" id="usuarios_registrados_datatable">
								<thead>
									<tr>
										<th class="text-center">#</th>
										<th class="text-center">Usuario</th>
										<th class="text-center">Fecha de Registro</th>
										<th class="text-center">Estado</th>
									</tr>
								</thead>
								<tbody>
									<!-- Aquí se llenarán los datos con AJAX -->
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<!-- Footer opcional -->
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL VER USUARIOS -->