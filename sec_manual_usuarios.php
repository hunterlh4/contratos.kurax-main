<?php

include '/var/www/html/sys/globalFunctions/generalInfo/parameterGeneral.php';

$areas_habilitadas_para_ver_manual_aceso = getParameterGeneral('areas_habilitadas_para_ver_manual_aceso');
$areas_permitidas_array = explode(",", $areas_habilitadas_para_ver_manual_aceso);

$menu_id = "";
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '" . $sec_id . "' LIMIT 1");
while ($r = $result->fetch_assoc()) $menu_id = $r["id"];

if (!array_key_exists($menu_id, $usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])) {
	echo "No tienes permisos para acceder a este recurso";
	die;
} else {
	echo    '';
}

if ($sec_id = 'manual_usuarios') {
?>
	<link rel="stylesheet" href="css/sec_manual_usuarios.css?<?php echo $css_cache; ?>">
<?php
}
?>
<div class="container-fluid">
	<div class="row">

		<div class="page-header wide" style="margin-bottom: 10px;">
			<div class="row">
				<div class="col-xs-12 text-center">
					<h1 class="page-title titulosec_contrato">
						<i class="fa fa-book"></i>
						Manuales de usuario y de acceso
					</h1>
				</div>
			</div>
		</div>

		<div class="page-header wide">
			<div class="row mt-4 mb-2">
				<div class="col-xs-12 text-right">
					<div class="form-group ">
						<label for="filter_tbl_manuales_busqueda" class="text-center">Buscar: </label>
						<input type="text" id="filter_tbl_manuales_busqueda" class="has-success" placeholder="Buscar...">
					</div>
				</div>
			</div>
		</div>

	</div>
	<div class="row">
		<div class="col-xs-12 col-md-12 col-lg-12">
			<div class="contenedor_permisos_menus_sub_menus_copiar">
				<div id="collapseTwo_menus">
					<div class="panel-body-2">
						<input type="hidden" class="valor_menu_id" />
						<input type="hidden" class="valor_usuario_id" />
						<div class="col-md-12">
							<div id="filename"></div>
							<div id="progress"></div>
							<div id="progressBar"></div>
						</div>
						<div class="container_table_menus_sub_menus_botones" style="height: 100% !important">
							<table id="tbl_menu_sub_menu_botones" class="table tbl_menu_sub_menu_botones" width="100%">
								<thead>
									<tr>
										<th>Menú del sistema</th>
										<th>Manual de usuario</th>
										<?php
										if (in_array($login['area_id'], $areas_permitidas_array)) {
										?>
											<th>Guía de acceso</th>
										<?php
										}
										?>
										<th>Opciones</th>
									</tr>
								</thead>
								<?php
								$tipo_menu = 'permisos';
								$idsmenus = array_keys($usuario_permisos);

								if ($login['area_id'] === 9) {
									$condicion1 = "";
									$condicion2 = "";
								} else {
									$condicion1 = "and b.id in (" . implode(",", $idsmenus) . ")";
									$condicion2 = "and a.id in (" . implode(",", $idsmenus) . ")";
								}
								//Busca el menu padre
								$query_menu_sistemas = "SELECT
															a.id, 
															a.titulo, 
															b.id as id_relacionado, 
															b.titulo as titulo_relacionado, 
															b.relacion_id,
															am1.filepath as path_manual_usuario,
															am2.filepath as path_manual_acceso,
															am3.filepath as relacionado_path_manual_usuario,
															am4.filepath as relacionado_path_manual_acceso
														FROM tbl_menu_sistemas AS  a
														LEFT JOIN tbl_menu_sistemas AS b ON (b.relacion_id = a.id and b.estado = 1 " . $condicion1 . ")
														LEFT JOIN tbl_archivos_manuales am1 ON am1.menu_sistemas_id = a.id and am1.estado = 1 and am1.tipo = 1
														LEFT JOIN tbl_archivos_manuales am2 ON am2.menu_sistemas_id = a.id and am2.estado = 1 and am2.tipo = 2
														LEFT JOIN tbl_archivos_manuales am3 ON am3.menu_sistemas_id = b.id and am3.estado = 1 and am3.tipo = 1
														LEFT JOIN tbl_archivos_manuales am4 ON am4.menu_sistemas_id = b.id and am4.estado = 1 and am4.tipo = 2
														WHERE 
															(a.estado = 1 
															AND a.relacion_id IS NULL
															OR a.relacion_id=0)
															" . $condicion2 . "
														ORDER BY a.id";
								$result_menu_sistemas = $mysqli->query($query_menu_sistemas);
								?>

								<tbody>
									<?php
									if ($tipo_menu == 'permisos') {
										$id_tipo_menu = 'p_';
									} else {
										$id_tipo_menu = '';
									}
									$b_row = 'a';
									foreach ($result_menu_sistemas as $row_menu) {
										if ($row_menu['id'] != $b_row) {
									?>
											<tr>
												<td class="tbl_menu_sub_menu_botones_primer_td">
													<button type="button" class="parent_tbl_sub_menu_botones_padres" data-id="<?php echo $row_menu['id'] ?>">
														<span class="icon expand-icon glyphicon tbl_menu_sub_menu_botones_icon_expand_collapse_abuelo glyphicon-plus"></span>
													</button>
													<span class="icon node-icon glyphicon glyphicon-list tbl_menu_sub_menu_botones_icon_lista_abuelo"></span>
													<span class="tbl_menu_sub_menu_botones_texto">
														<?php echo $row_menu['titulo'] ?>
													</span>
												</td>
												<td class="text-center">
													<?php if (!empty($row_menu['path_manual_usuario'])) {
														echo substr($row_menu['path_manual_usuario'], 23, strlen($row_menu['path_manual_usuario']) - 23);
													} else {
														echo '----------------';
													} ?>
												</td>
												<?php if (in_array($login['area_id'], $areas_permitidas_array)) {
												?>
													<td class="text-center">
														<?php if (!empty($row_menu['path_manual_acceso'])) {
															echo substr($row_menu['path_manual_acceso'], 23, strlen($row_menu['path_manual_acceso']) - 23);
														} else {
															echo '----------------';
														} ?>
													</td>
												<?php
												} ?>

												<td class="tbl_menu_sub_menu_botones_ultimo_td" style="">
													<div class="opciones">
														<?php
														if (in_array("subir_archivo", $usuario_permisos[$menu_id])) {
														?>
															<div class="btn-group">
																<button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
																	<span class="glyphicon glyphicon-cloud-upload"></span>
																</button>
																<div class="dropdown-menu">
																	<a class="dropdown-item" href="#">
																		<div class="form-group form-group-upload-btn">
																			<label style="width: 100%" class="btn btn-warning upload-manual-btn" for="file-mu-<?php echo $row_menu['id'] ?>" data-tipo-manual="1" data-menu-titulo="<?php echo $row_menu['titulo'] ?>" data-menu-id="<?php echo $row_menu['id'] ?>"><span class="glyphicon glyphicon-user"></span> Manual de usuario</label>
																			<input type="file" id="file-mu-<?php echo $row_menu['id'] ?>" name="file" style="display: none;">
																		</div>
																	</a>
																	<a class="dropdown-item" href="#">
																		<div class="form-group form-group-upload-btn">
																			<label style="width: 100%" class="btn btn-warning upload-manual-btn" for="file-ma-<?php echo $row_menu['id'] ?>" data-tipo-manual="2" data-menu-titulo="<?php echo $row_menu['titulo'] ?>" data-menu-id="<?php echo $row_menu['id'] ?>"><span class="glyphicon glyphicon-import"></span> Guía de acceso</label>
																			<input type="file" id="file-ma-<?php echo $row_menu['id'] ?>" name="file" style="display: none;">
																		</div>
																	</a>
																</div>
															</div>
														<?php } ?>
														<div class="btn-group">
															<?php
															$disabled = 'disabled';
															if ($row_menu['path_manual_usuario'] || $row_menu['path_manual_acceso']) {
																$disabled = '';
															}
															if (in_array($login['area_id'], $areas_permitidas_array)) {
															?>
																<button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" <?php echo $disabled ?>>
																	<span class="glyphicon glyphicon-download-alt"></span>
																</button>
																<div class="dropdown-menu">

																	<?php
																	if ($row_menu['path_manual_usuario']) {
																	?>
																		<a class="dropdown-item btn-download-manual" href="#" data-tipo-manual="1" data-menu-id="<?php echo $row_menu['id'] ?>">
																			Manual de usuario
																		</a>
																	<?php
																	} else {
																	?>
																		<a href="#" class="dropdown-item text-danger">No hay manual de usuario</a>
																	<?php
																	}
																	if ($row_menu['path_manual_acceso']) {
																	?>
																		<a class="dropdown-item btn-download-manual" href="#" data-tipo-manual="2" data-menu-id="<?php echo $row_menu['id'] ?>">
																			Guía de acceso
																		</a>
																	<?php
																	} else {
																	?>
																		<a href="#" class="dropdown-item text-danger">No hay guía de acceso</a>
																	<?php
																	}
																	?>
																</div>

															<?php
															} else {
															?>
																<button class="btn btn-success btn-download-manual" href="#" data-tipo-manual="1" data-menu-id="<?php echo $row_menu['id'] ?>" <?php echo $disabled ?>>
																	<span class="glyphicon glyphicon-download-alt"></span>
																</button>
															<?php
															}
															?>
														</div>
														<div class="btn-group">
															<?php
															if (in_array("delete", $usuario_permisos[$menu_id])) {
															?>
																<?php
																$disabled = 'disabled';
																if ($row_menu['path_manual_usuario'] || $row_menu['path_manual_acceso']) {
																	$disabled = '';
																} else {
																}
																?>
																<button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" <?php echo $disabled; ?>>
																	<span class="glyphicon glyphicon-trash"></span>
																</button>
																<div class="dropdown-menu">
																	<?php
																	if ($row_menu['path_manual_usuario']) {
																	?>
																		<a class="dropdown-item btn-delete-manual" href="#" data-tipo-manual="1" data-menu-id="<?php echo $row_menu['id'] ?>">
																			Manual de usuario
																		</a>
																	<?php
																	}
																	if ($row_menu['path_manual_acceso']) {
																	?>
																		<a class="dropdown-item btn-delete-manual" href="#" data-tipo-manual="2" data-menu-id="<?php echo $row_menu['id'] ?>">
																			Guía de acceso
																		</a>
																	<?php
																	}
																	?>
																</div>

															<?php
															}
															?>
														</div>
														<div class="btn-group">
															<?php
															if (in_array($login['area_id'], $areas_permitidas_array)) {
															?>
																<button type="button" class="btn btn-warning dropdown-toggle" title="Historial de cambios" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
																	<span class="fa fa-history"></span>
																</button>
																<div class="dropdown-menu">
																	<a class="dropdown-item btn-historico-cambios-manual" href="#" data-tipo-manual="1" data-menu-titulo="<?php echo $row_menu['titulo'] ?>" data-menu-id="<?php echo $row_menu['id'] ?>">
																		Manual de usuario
																	</a>
																	<a class="dropdown-item btn-historico-cambios-manual" href="#" data-tipo-manual="2" data-menu-titulo="<?php echo $row_menu['titulo'] ?>" data-menu-id="<?php echo $row_menu['id'] ?>">
																		Guía de acceso
																	</a>
																</div>
															<?php
															} else {
															?>
																<button class="btn btn-warning btn-historico-cambios-manual" href="#" data-tipo-manual="1" data-menu-titulo="<?php echo $row_menu['titulo'] ?>" data-menu-id="<?php echo $row_menu['id'] ?>">
																	<span class="fa fa-history"></span>
																</button>
															<?php
															}
															?>
														</div>
													</div>
												</td>
											</tr>
											<!-- <tr class="tbl_menu_sub_menu_botones_padres_detalles_<?php echo $row_menu['id']; ?> rows_hidden_usuarios_permisos">
												<td class="tbl_menu_sub_menu_botones_ultimo_td" colspan="2">
													<table width="100%" class="tabla_menu_sub_menu_botones_checkbox_botones_padre">
														<tbody>
															<?php
															$query_botones = "SELECT boton,nombre FROM tbl_menu_sistemas_botones WHERE menu_id  ='" . $row_menu['id'] . "'";
															$result_btns = $mysqli->query($query_botones);
															while ($row_btns = $result_btns->fetch_assoc()) {
															?>
																<tr>
																	<td class="tbl_menu_sub_menu_botones_botones_padre_td">
																		<span class="glyphicon glyphicon-th-large tbl_menu_sub_menu_botones_iconos"></span>
																		<span class="tbl_menu_sub_menu_botones_texto_botones_padre">
																			[<?php echo $row_btns['boton']; ?>] <?php echo $row_btns['nombre']; ?>
																		</span>
																	</td>
																	<td class="tbl_menu_sub_menu_botones_checbox" style="width:120px !important;">
																		<div class="opciones">
																			<div class="btn-group">
																				<button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
																					<span class="glyphicon glyphicon-cloud-upload"></span>

																				</button>
																				<div class="dropdown-menu">
																					<a class="dropdown-item" href="#">
																						<div class="form-group form-group-upload-btn">
																							<label style="width: 100%" class="btn btn-warning upload-manual-btn" for="file-btn-<?php echo $row_btns['boton']; ?>" data-tipo-manual="1" data-btn-id="<?php echo $row_btns['boton']; ?>"><span class="glyphicon glyphicon-user"></span> Manual de usuario</label>
																							<input type="file" id="file" name="file-btn-<?php echo $row_btns['boton']; ?>" style="display: none;">
																						</div>
																					</a>
																					<a class="dropdown-item" href="#">
																						<div class="form-group form-group-upload-btn">
																							<label style="width: 100%" class="btn btn-warning upload-manual-btn" for="file-btn-<?php echo $row_btns['boton']; ?>" data-tipo-manual="2" data-btn-id="<?php echo $row_btns['boton']; ?>"><span class="glyphicon glyphicon-import"></span> Guía de acceso</label>
																							<input type="file" id="file" name="file-btn-<?php echo $row_btns['boton']; ?>" style="display: none;">
																						</div>
																					</a>
																				</div>
																			</div>
																			<div class="btn-group">
																				<button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
																					<span class="glyphicon glyphicon-download-alt"></span>
																				</button>
																				<div class="dropdown-menu">
																					<a class="dropdown-item" href="#">
																						Manual de usuario</label>
																					</a>
																					<a class="dropdown-item" href="#">
																						Guía de acceso</label>
																					</a>
																				</div>
																			</div>
																			<div class="btn-group">
																				<button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
																					<span class="glyphicon glyphicon-trash"></span>
																				</button>
																				<div class="dropdown-menu">
																					<a class="dropdown-item" href="#">

																						Manual de usuario </a>
																					<a class="dropdown-item" href="#">
																						Guía de acceso
																					</a>
																				</div>
																			</div>
																		</div>
																	</td>
																</tr>
															<?php
															}
															mysqli_free_result($result_btns);
															?>
														</tbody>
													</table>
												</td>
											</tr> -->
										<?php
										}
										if ($row_menu['id'] == $row_menu['relacion_id']) {
										?>
											<tr class="tbl_menu_sub_menu_botones_padres_detalles tbl_menu_sub_menu_botones_padres_detalles_<?php echo $row_menu['id']; ?> rows_hidden_usuarios_permisos">
												<td>
													<!-- <button type='button' class='parent_tbl_sub_menu_botones' data-id='<?php echo $row_menu["id_relacionado"]; ?>'>
														<span class="icon expand-icon glyphicon glyphicon-plus tbl_menu_sub_menu_botones_icon_expand_collapse_hijos"></span>
													</button> -->
													<span class="icon node-icon glyphicon glyphicon-list tbl_menu_sub_menu_botones_icon_lista_hijos"></span>
													<span class="tbl_menu_sub_menu_botones_texto">
														<?php echo $row_menu['titulo_relacionado']; ?>
													</span>
												</td>
												<td class="text-center">
													<?php if (!empty($row_menu['relacionado_path_manual_usuario'])) {
														echo substr($row_menu['relacionado_path_manual_usuario'], 23, strlen($row_menu['relacionado_path_manual_usuario']) - 23);
													} else {
														echo '----------------';
													} ?>
												</td>
												<?php
												if (in_array($login['area_id'], $areas_permitidas_array)) {
												?>
													<td class="text-center">
														<?php if (!empty($row_menu['relacionado_path_manual_acceso'])) {
															echo substr($row_menu['relacionado_path_manual_acceso'], 23, strlen($row_menu['relacionado_path_manual_acceso']) - 23);
														} else {
															echo '----------------';
														} ?>
													</td>
												<?php
												}
												?>
												<td class="tbl_menu_sub_menu_botones_ultimo_td" style="">
													<div class="opciones">
														<?php
														if (in_array("subir_archivo", $usuario_permisos[$menu_id])) {
														?>
															<div class="btn-group">
																<button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
																	<span class="glyphicon glyphicon-cloud-upload"></span>
																</button>
																<div class="dropdown-menu">
																	<a class="dropdown-item" href="#">
																		<div class="form-group form-group-upload-btn">
																			<label style="width: 100%" class="btn btn-warning upload-manual-btn" for="file-menu-<?php echo $row_menu['id_relacionado'] ?>" data-tipo-manual="1" data-menu-titulo="<?php echo $row_menu['titulo_relacionado'] ?>" data-menu-id="<?php echo $row_menu['id_relacionado'] ?>"><span class="glyphicon glyphicon-import"></span> Manual de usuario</label>
																			<input type="file" id="file" name="file-menu-<?php echo $row_menu['id_relacionado'] ?>" style="display: none;">
																		</div>
																	</a>
																	<a class="dropdown-item" href="#">
																		<div class="form-group form-group-upload-btn">
																			<label style="width: 100%" class="btn btn-warning upload-manual-btn" for="file-menu-<?php echo $row_menu['id_relacionado'] ?>" data-tipo-manual="2" data-menu-titulo="<?php echo $row_menu['titulo_relacionado'] ?>" data-menu-id="<?php echo $row_menu['id_relacionado'] ?>"><span class="glyphicon glyphicon-import"></span> Guía de acceso</label>
																			<input type="file" id="file" name="file-menu-<?php echo $row_menu['id_relacionado'] ?>" style="display: none;">
																		</div>
																	</a>
																</div>
															</div>
														<?php } ?>
														<div class="btn-group">
															<?php
															$disabled = 'disabled';
															if ($row_menu['relacionado_path_manual_usuario'] || $row_menu['relacionado_path_manual_acceso']) {
																$disabled = '';
															}
															if (in_array($login['area_id'], $areas_permitidas_array)) {
															?>
																<button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="false" aria-expanded="false" <?php echo $disabled ?>>
																	<span class="glyphicon glyphicon-download-alt"></span>
																</button>
																<div class="dropdown-menu">

																	<?php
																	if ($row_menu['relacionado_path_manual_usuario']) {
																	?>
																		<a class="dropdown-item btn-download-manual" href="#" data-tipo-manual="1" data-menu-id="<?php echo $row_menu['id_relacionado'] ?>">
																			Manual de usuario
																		</a>
																	<?php
																	} else {
																	?>
																		<a href="#" class="dropdown-item text-danger">No hay manual de usuario</a>
																	<?php
																	}

																	if ($row_menu['relacionado_path_manual_acceso']) {

																	?>

																		<a class="dropdown-item btn-download-manual" href="#" data-tipo-manual="2" data-menu-id="<?php echo $row_menu['id_relacionado'] ?>">
																			Guía de acceso
																		</a>
																	<?php
																	} else {
																	?>
																		<a href="#" class="dropdown-item text-danger">No hay guía de acceso</a>
																	<?php
																	}
																	?>
																</div>
															<?php
															} else {
															?>
																<button class="btn btn-success btn-download-manual" href="#" data-tipo-manual="1" data-menu-id="<?php echo $row_menu['id_relacionado'] ?>" <?php echo $disabled ?>>
																	<span class="glyphicon glyphicon-download-alt"></span>
																</button>
															<?php
															}
															?>
														</div>
														<div class="btn-group">
															<?php
															if (in_array("delete", $usuario_permisos[$menu_id])) {
															?>
																<?php
																$disabled = "disabled";
																if ($row_menu['relacionado_path_manual_usuario'] || $row_menu['relacionado_path_manual_acceso']) {
																	$disabled = '';
																}
																?>
																<button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" <?php echo $disabled; ?>>
																	<span class="glyphicon glyphicon-trash"></span>
																</button>
																<div class="dropdown-menu">
																	<?php
																	if ($row_menu['relacionado_path_manual_usuario']) {
																	?>
																		<a class="dropdown-item btn-delete-manual" href="#" data-tipo-manual="1" data-menu-id="<?php echo $row_menu['id_relacionado'] ?>">
																			Manual de usuario
																		</a>
																	<?php
																	}
																	if ($row_menu['relacionado_path_manual_acceso']) {
																	?>
																		<a class="dropdown-item btn-delete-manual" href="#" data-tipo-manual="2" data-menu-id="<?php echo $row_menu['id_relacionado'] ?>">
																			Guía de acceso
																		</a>
																	<?php
																	}
																	?>
																</div>
															<?php
															}
															?>
														</div>
														<div class="btn-group">
															<?php
															if (in_array($login['area_id'], $areas_permitidas_array)) {
															?>
																<button type="button" class="btn btn-warning dropdown-toggle" title="Historial de cambios" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
																	<span class="fa fa-history"></span>
																</button>
																<div class="dropdown-menu">
																	<a class="dropdown-item btn-historico-cambios-manual" href="#" data-tipo-manual="1" data-menu-titulo="<?php echo $row_menu['titulo_relacionado'] ?>" data-menu-id="<?php echo $row_menu['id_relacionado'] ?>">
																		Manual de usuario
																	</a>
																	<a class="dropdown-item btn-historico-cambios-manual" href="#" data-tipo-manual="2" data-menu-titulo="<?php echo $row_menu['titulo_relacionado'] ?>" data-menu-id="<?php echo $row_menu['id_relacionado'] ?>">
																		Guía de acceso
																	</a>
																</div>
															<?php
															} else {
															?>
																<button class="btn btn-warning btn-historico-cambios-manual" title="Historial de cambios" href="#" data-tipo-manual="1" data-menu-titulo="<?php echo $row_menu['titulo_relacionado'] ?>" data-menu-id="<?php echo $row_menu['id_relacionado'] ?>">
																	<span class="fa fa-history"></span>
																</button>
															<?php
															}
															?>
														</div>
													</div>
												</td>
											</tr>
											<!-- <tr class="tbl_menu_sub_menu_botones_detalles tbl_menu_sub_menu_botones_detalles_<?php echo $row_menu["id_relacionado"]; ?> rows_hidden_usuarios_permisos">
												<td class="tbl_menu_sub_menu_botones_ultimo_td " colspan="2">
													<table class="tbl_sub_menu_botones" width="100%">
														<tbody>
															<?php
															$query_botones = "SELECT boton,nombre FROM tbl_menu_sistemas_botones WHERE menu_id  ='" . $row_menu['id_relacionado'] . "'";
															$result_btns = $mysqli->query($query_botones);
															while ($row_btns = $result_btns->fetch_assoc()) {
															?>
																<tr class="tr_tbl_sub_menu_botones">
																	<td class="tbl_menu_sub_menu_botones_botones_hijos_td">
																		<span class="glyphicon glyphicon-th-large tbl_menu_sub_menu_botones_iconos_hijo"></span>
																		<span class="tbl_menu_sub_menu_botones_texto_botones_hijo">
																			[<?php echo $row_btns['boton']; ?>] <?php echo $row_btns['nombre']; ?>
																		</span>
																	</td>
																	<td class="td_menu_sub_menu_botones_checkbox_botones_hijo" style="width:100px !important;">
																		<div class="opciones">
																			<div class="btn-group">
																				<button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
																					<span class="glyphicon glyphicon-cloud-upload"></span>
																				</button>
																				<div class="dropdown-menu">
																					<a class="dropdown-item" href="#">
																						<div class="form-group form-group-upload-btn">
																							<label style="width: 100%" class="btn btn-warning upload-manual-btn" for="file-btn-<?php echo $row_btns['boton']; ?>" data-tipo-manual="1" data-btn-id="<?php echo $row_btns['boton']; ?>"><span class="glyphicon glyphicon-user"></span> Manual de usuario</label>
																							<input type="file" id="file-btn-<?php echo $row_btns['boton']; ?>" name="file" style="display: none;">
																						</div>
																					</a>
																					<a class="dropdown-item" href="#">
																						<div class="form-group form-group-upload-btn">
																							<label style="width: 100%" class="btn btn-warning upload-manual-btn" for="file-btn-<?php echo $row_btns['boton']; ?>" data-tipo-manual="2" data-btn-id="<?php echo $row_btns['boton']; ?>"><span class="glyphicon glyphicon-import"></span> Guía de acceso</label>
																							<input type="file" id="file-btn-<?php echo $row_btns['boton']; ?>" name="file" style="display: none;">
																						</div>
																					</a>
																				</div>
																			</div>
																			<div class="btn-group">
																				<button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
																					<span class="glyphicon glyphicon-download-alt"></span>
																				</button>
																				<div class="dropdown-menu">
																					<a class="dropdown-item" href="#">
																						Manual de usuario </a>
																					<a class="dropdown-item" href="#">
																						Guía de acceso
																					</a>

																				</div>
																			</div>
																			<div class="btn-group">
																				<button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
																					<span class="glyphicon glyphicon-trash"></span>
																				</button>
																				<div class="dropdown-menu">
																					<a class="dropdown-item" href="#">
																						Manual de usuario </a>
																					<a class="dropdown-item" href="#">
																						Guía de acceso
																					</a>

																				</div>
																			</div>
																		</div>
																	</td>
																</tr>
															<?php
															}
															mysqli_free_result($result_btns);
															?>
														</tbody>
													</table>
												</td>
											</tr> -->
									<?php
										}
										$b_row = $row_menu['id'];
									}
									?>
								</tbody>
								<tfoot class="tbl_menu_sub_menu_botones_tfoot"></tfoot>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>


<!-- INICIO MODAL HISTORICO CAMBIOS -->
<div id="modalManualUsuarioHistoricoCambios" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_title_manual_usuario_historico_cambios"></h4>
			</div>
			<div class="modal-body">

				<div class="col-md-12">
					<div class="table-responsive" id="mantenimiento_num_cuenta_subdiario_div_tabla">
						<table class="table display responsive" style="width:100%" id="mmantenimiento_num_cuenta_subdiario_datatable">
							<thead>
								<tr>
									<th class="text-center">#</th>
									<th class="text-center">Fecha Creación</th>
									<th class="text-center">Usuario Creador</th>
									<th class="text-center">Estado</th>
									<th class="text-center">Fecha Modificación</th>
									<th class="text-center">Usuario Modificador</th>
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="modal-footer">

			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL HISTORICO CAMBIOS -->