<?php if ($sub_sec_id) : ?>
	<?php include("sec_archivos_" . $sub_sec_id . ".php"); ?>
<?php else : ?>
	<?php
	$menu_id = "";
	$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '" . $sec_id . "' LIMIT 1");
	while ($r = $result->fetch_assoc()) $menu_id = $r["id"];
	if (!array_key_exists($menu_id, $usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])) {
		echo "No tienes permisos para ver esta página";
		die;
	}
	$permiso_categorias = false;
	// if (!array_key_exists($menu_id, $usuario_permisos) || !in_array("administrar_categorias", $usuario_permisos[$menu_id])) {
	// 	$permiso_categorias = true;
	// }

	$categorias = [];
	$result = $mysqli->query("SELECT id, name, active FROM tbl_archivos_categoria ORDER BY name ASC ; -- WHERE active = 1");
	while ($r = $result->fetch_assoc()) $categorias[] = $r;

	$path_to_search = "/var/www/storage/website/landing-dinamico/";
	$path_to_open = opendir($path_to_search);
	?>

	<link rel="stylesheet" href="css/simplePagination.css">
	<div class="content container-fluid">
		<div class="page-header wide">
			<div class="row">
				<div class="col-xs-12 text-center">
					<div class="page-title"><i class="icon icon-inline fa fa-fw fa-file"></i> Archivos</div>
				</div>
			</div>
		</div>
		<?php if (array_key_exists($menu_id, $usuario_permisos) && in_array("import", $usuario_permisos[$menu_id])) : ?>
			<div class="row">
				<div class="col-lg-12">
					<button id="btnArchivoUpload" class="btn btn-success"><i class="fa fa-upload"></i> Subir Archivos</button>
					<?php if($permiso_categorias){?>
						<button id="btnCrearCategoria" class="btn btn-success"><i class="fa fa-tags" aria-hidden="true"></i> Administrar Categorías</button>
					<?php }?>
				</div>
			</div>
		<?php endif; ?>
		<div class="row" style="margin-top:10px">
			<div class="col-lg-4">
				<div class="form-group form-inline">
					Categoría:
					<select id="cbArchivosCategoria" name="cbArchivosCategoria" class="form-control" style="width: 60%">
						<option value="all">Todos</option>
						<?php foreach ($categorias as $categoria) : ?>
							<?php 
								if($categoria['active'] == 1){
									echo '<option value="'.$categoria["id"].'">'.$categoria["name"].'</option>';
								}
							?>
						<?php endforeach ?>
					</select>
				</div>
			</div>
			<div class="col-lg-4">
				<div class="form-group form-inline">
					Buscar:
					<input type="text" id="txtArchivosFilter" class="form-control" placeholder="" style="width: 80%">
				</div>
			</div>
			<div class="col-lg-2">
				<div class="form-group form-inline pull-right">
					Mostrar
					<select id="cbArchivosLimit" name="cbArchivosLimit" class="form-control">
						<option value="10">10</option>
						<option value="25">25</option>
						<option value="50">50</option>
						<option value="100">100</option>
					</select>
				</div>
			</div>
			<div class="col-lg-2">
				<button id="btnArchivosSearch" style="margin-top: -2px" class="btn btn-info pull-right"><i class="fa fa-search"></i> Buscar</button>
			</div>
		</div>

		<div id="digital_directory" class="row" style="margin-top:10px; display: none;">
			<div class="col-lg-4">
				<span style="font-weight: bold;">Directorio (Digital):</span>
				<select id="cbDigitalDirectory" name="cbDigitalDirectory" class="form-control" style="width: 30%;">
					<option value="all">Todos</option>
					<?php
					$dir_list = array();
					while ($dir_select = readdir($path_to_open)) {
						if ($dir_select != "." && $dir_select != "..") {
							if (is_dir($path_to_search . $dir_select)) {
								$dir_list[] = $dir_select;
							}
						}
					}
					natcasesort($dir_list);
					foreach ($dir_list as $d_list) {
						echo "<option value='$d_list'>$d_list</option>";
					}
					?>
				</select>
			</div>
		</div>

		<div class="row" style="margin-top:5px">
			<div class="col-lg-12">
				<table id="tblArchivos" class="table  table-hover">
					<thead>
						<tr>
							<th>Nombre del Archivo</th>
							<th style="width:15%" id="categoria_name">Categoría</th>
							<th style="width:15%">Formato</th>
							<th style="width:15%">Tamaño</th>
							<th style="width:10%">Acciones</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
				<div class="pull-right">
					<div id="paginationArchivos"></div>
				</div>
			</div>
		</div>
	</div>

	<div id="mdArchivos" class="modal fade">
		<div class="modal-dialog modal-md">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close pull-right" data-dismiss="modal">&times;</button>
					<h4 class="modal-title">Agregar Nuevo Archivo</h4>
				</div>
				<form id="formArchivosModal" method="POST" enctype="multipart/form-data">
					<div class="modal-body">
						<div class="row">
							<div class="col-lg-12">
								<label for="cbArchivosModalCategoria">Seleccionar Categoría</label>
								<div class="form-group mb-2">
									<select id="cbArchivosModalCategoria" name="cbArchivosModalCategoria" class="form-control" style="width:100%;">
										<?php foreach ($categorias as $categoria) : ?>
											<?php 
												if($categoria['active'] == 1){
													echo '<option value="'.$categoria["id"].'">'.$categoria["name"].'</option>';
												}
											?>
										<?php endforeach ?>
									</select>
								</div>

								<div id="form_SubirArchivoCategoria">
									<label for="fileArchivosModal">Seleccionar Archivo</label>
									<div class="input-container">
										<input type="file" id="fileArchivosModal" name="fileArchivosModal[]" multiple="multiple">
										<button class="browse-btn"> Buscar Archivos </button>
										<span class="file-info">Ningun Archivo Seleccionado...</span>
										<button type="reset" id="file_reset" style="display:none">
									</div>
								</div>
								<div class="alert alert-warning small mt--4"><i class="fa fa-info"></i> Se puede subir multiples archivos presionando la tecla ctrl al seleccionarlos.</div>

								<div id="form_SubirArchivoDigital" style="display: none;">
									<button id="btn_new_directory" class="btn btn-rounded btn-min-width btn-primary">
										<i class="glyphicon glyphicon-plus"></i> Crear carpeta
									</button>
									<button id="btn_delete_directory" class="btn btn-rounded btn-min-width btn-danger pull-right">
										<i class="glyphicon glyphicon-remove"></i> Eliminar carpeta
									</button>
									<div class="form-group mt-4" style="max-height:300px; overflow-y:scroll;">
										<!--
                                    <table class="table table-condensed table-bordered" id="tbl_carpetas_digital">
										<thead>
											<tr class="bg-secondary">
												<th class="w-30px text-center"></th>
												<th>Carpeta</th>
											</tr>
										</thead>
										<tbody>
										</tbody>
									</table>-->

										<div id="list_carpetas_digital">

										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-success"><i class="fa fa-upload"></i> Enviar</button>
						<button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
					</div>
				</form>
			</div>
		</div>
	</div>

	<!-- Modal para crear carpeta en directorio -->
	<div class="modal fade" id="modal_crear_carpeta">
		<div class="modal-dialog modal-sm">
			<div class="modal-content modal-rounded">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><span class="glyphicon glyphicon-folder-open"></span> Crear carpeta</h4>
				</div>
				<div class="modal-body">
					<form id="Archivosform_crear_carpeta">
						<label class="h4 strong block">Escriba el nombre de la carpeta:</label>
						<input type="text" name="Archivos_nombreCarpeta" id="Archivos_nombreCarpeta" style="width: 100%;">
					</form>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-info" form="Archivosform_crear_carpeta" id="btn_Archivos_creaCarpeta"><span class="glyphicon glyphicon-folder-open"></span> Crear</button>
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal para editar categoria -->
	<div class="modal fade" id="modal_editar_categoria">
		<div class="modal-dialog modal-sm">
			<div class="modal-content modal-rounded">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">Editar Categoría</h4>
				</div>
				<div class="modal-body">
					<form id="Archivosform_editar_categoria">
						<input type="hidden" name="archivos_id_categoria_edit" id="archivos_id_categoria_edit" value="" style="width: 100%;">
						<div class="form-group mb-2">
							<label class="h4 strong block">Escriba el nombre de la categoría:</label>
							<input type="text" name="archivos_nombre_categoria_edit" id="archivos_nombre_categoria_edit" style="width: 100%;">
						</div>
						<div class="form-group mb-2">
							<label class="h4 strong block">Estado de la categoría:</label>
							<select id="cbArchivosModalCategoriaEstado" name="cbArchivosModalCategoriaEstado" class="form-control" style="width:100%;">
								<option value="-1">- estado -</option>
								<option value="1">Activado</option>
								<option value="0">Desactivado</option>
							</select>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-info" form="Archivosform_editar_categoria" id="btn_Archivos_editar_categoria">
						Guardar</button>
					<button type="button" class="btn btn-secondary" data-dismiss="modal" id="btn_cerrar_modal_editar">Cerrar</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal para crear categoria  -->
	<div class="modal fade" id="modal_crear_categoria">
		<div class="modal-dialog modal-sm">
			<div class="modal-content modal-rounded">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><i class="fa fa-tags" aria-hidden="true"></i> Crear categoria</h4>
				</div>
				<div class="modal-body">
					<form id="Archivosform_crear_categoria">
						<label class="h4 strong block">Escriba el nombre de la categoria:</label>
						<input type="text" name="Archivos_nombre_categoria" id="nombre_categoria" style="width: 100%;">
					</form>

					<div class="form-group mt-3">
						<b>
							Categorías:
						</b>
						<table class="" style="width: 100%;">
							<tbody>
								<?php foreach ($categorias as $categoria) : ?>
									<tr id="list_categoria_<?php echo $categoria['id'] ?>" value="<?php echo $categoria["id"] ?>">
										<td>
											<?php echo $categoria["name"]; ?>
										</td>
										<td>
											<span type="button" class="badge badge-success badge-pill float-right" onclick='editarCategoria(<?php echo $categoria["id"];?>,"<?php echo $categoria["name"];?>",<?php echo $categoria["active"]; ?>)' style="cursor: pointer">
												<i class="fa fa-pencil-square-o" aria-hidden="true"></i>
											</span>
										</td>
										<td>
											<span type="button" class="badge badge-danger badge-pill float-right" onclick="removeCategoria(<?php echo $categoria['id']; ?>)" style="cursor: pointer">
												<i class="fa fa-trash-o" aria-hidden="true"></i>
											</span>
										</td>
									</tr>
								<?php endforeach ?>
							</tbody>
						</table>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-info" form="Archivosform_crear_categoria" id="btn_crear_categoria">Crear</button>
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
				</div>
			</div>
		</div>
	</div>

	<!-- Modal de respuesta al subir los archivos -->
	<div class="modal fade" id="modal_respuesta_subir_archivo">
		<div class="modal-dialog">
			<div class="modal-content modal-rounded">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title">ARCHIVOS AGREGADOS</h4>
				</div>
				<div class="modal-body" id="modal_respuesta_subir_archivo_body">

				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
				</div>
			</div>
		</div>
	</div>

<?php endif; ?>