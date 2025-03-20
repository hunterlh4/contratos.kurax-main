<?php
$id_padre = 0;
$m = $mysqli->query("SELECT nombre, tabla, opciones, input_text, switch FROM adm_mantenimientos WHERE tabla = 'tbl_" . $sec_id . "'")->fetch_assoc();
?>
<div class="content container-fluid">
	<div class="page-header wide">
		<div class="row">
			<div class="col-xs-12 text-center">
				<div class="page-title"><i class="icon icon-inline fa fa-fw fa-users"></i> Bancos</div>

			</div>
		</div>
		<!-- BOTON AGREGAR NUEVO CLIENTE Y FORMULARIO-->
		<div class="row">
			<div class="col-xs-12">
				<?php
				$this_menu = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '" . $sec_id . "' LIMIT 1")->fetch_assoc();

				if ($item_id) {

				?>

					<a class="btn btn-default" href="./?sec_id=<?php echo $sec_id; ?>">
						<i class="glyphicon glyphicon-arrow-left"></i>

						Regresar
					</a>
					<button type="button" data-then="exit" class=" save_btn_bancos btn btn-success" data-button="save">
						<i class="glyphicon glyphicon-floppy-save"></i>
						Guardar y Salir
					</button>
					<button type="button" data-then="reload" class=" save_btn_bancos btn btn-success" data-button="save">
						<i class="glyphicon glyphicon-floppy-save"></i>
						Guardar
					</button>


				<?php
				} else {
				?>
					<a
						href="./?sec_id=<?php echo $sec_id; ?>&amp;item_id=new"
						id=""
						data-sec="<?php echo $sec_id; ?>"
						data-table="tbl_clientes"
						class="btn btn-rounded btn-min-width btn-success btn-add"><i class="glyphicon glyphicon-plus"></i> Agregar nuevo</a>

					<!--<button class="btn_asignar_permisos_multiple_usuarios" ><i class="glyphicon glyphicon-equalizer"></i>Permisos</button>-->
				<?php
				}
				?>
			</div>
		</div>
		<!-- FIN BOTON AGREGAR NUEVO BANCO Y FORMULARIO-->
	</div>
	<?php

	if ($item_id) {
		$item = $mysqli->query("SELECT  id,
										nombre,
										descripcion,
										observacion,
										telefono,
										direccion,
										contacto,
										color_hex,
										estado 
										FROM tbl_bancos WHERE id = '" . $item_id . "'")->fetch_assoc();

	?>
		<input type="hidden" class="save_data" data-col="table" value="<?php echo $m["tabla"]; ?>" />
		<input type="hidden" class="save_data" data-col="id" value="<?php echo $item_id; ?>" />
		<!-- FORMULARIO CREAR NUEVO/EDITAR BANCO -->
		<div class="row">
			<div class="col-xs-12 ">
				<?php

				//include("sec_adm_mantenimientos_form.php");
				if ($m["tabla"] == "tbl_bancos") {
				?>
					<div class="col-xs-12 col-md-6 col-md-offset-3 clientes_form_bancos">
						<div class="panel" id="datos_de_bancos">
							<div class="panel-heading">
								<div class="panel-title"><i class="icon fa fa-file-text-o muted"></i> Datos del Banco</div>
							</div>
							<div id="panel-datos_de_contrato" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="panel-collapse-1-heading">
								<div class="panel-body">

									<div class="form-group form-group-nombre">
										<label class="col-xs-5 control-label" for="input_text_nombre-id">Nombre</label>
										<div class="input-group col-xs-7">
											<input
												type="text"
												class="form-control input_text"
												id="input_text_nombre"
												data-col="nombre"

												value="<?php echo isset($item['nombre']) ? $item['nombre'] : ''; ?>">
											<!-- editado por alex (bancos) -->
											<!-- value="<?php  //echo $item ['nombre']; 
														?>"> -->
											<label class="input-group-addon glyphicon glyphicon-pencil"></label>
										</div>
									</div>
									<div class="form-group form-group-descripcion">
										<label class="col-xs-5 control-label" for="input_text_descripcion">Descripción</label>
										<div class="input-group col-xs-7">
											<input
												type="text"
												class="form-control input_text"
												id="input_text_descripcion"
												data-col="descripcion"

												value="<?php echo isset($item['descripcion']) ? $item['descripcion'] : ''; ?>">
											<!-- editado por alex (bancos) -->
											<!-- value="<?php //echo $item['descripcion']; 
														?>"> -->
											<label class="input-group-addon glyphicon glyphicon-pencil"></label>
										</div>
									</div>
									<div class="form-group form-group-observacion">
										<label class="col-xs-5 control-label" for="input_text_observacion">Observación</label>
										<div class="input-group col-xs-7">
											<input
												type="text"
												class="form-control input_text"
												id="input_text_observacion"
												data-col="observacion"

												value="<?php echo isset($item['observacion']) ? $item['observacion'] : ''; ?>">
											<!-- editado por alex (bancos) -->
											<!-- value="<?php //echo $item['observacion']; 
														?>"> -->
											<label class="input-group-addon glyphicon glyphicon-pencil"></label>
										</div>
									</div>
									<div class="form-group form-group-telefono">
										<label class="col-xs-5 control-label" for="input_text_telefono">Telefono</label>
										<div class="input-group col-xs-7">
											<input
												type="text"
												class="form-control input_text"
												id="input_text_telefono"
												data-col="telefono"

												value="<?php echo isset($item['telefono']) ? $item['telefono'] : ''; ?>">
											<!-- editado por alex (bancos) -->
											<!-- value="<?php //echo $item['telefono']; 
														?>"> -->
											<label class="input-group-addon glyphicon glyphicon-pencil"></label>
										</div>
									</div>
									<div class="form-group form-group-direccion">
										<label class="col-xs-5 control-label" for="input_text_direccion">Dirección</label>
										<div class="input-group col-xs-7">
											<input
												type="text"
												class="form-control input_text"
												id="input_text_direccion"
												data-col="direccion"

												value="<?php echo isset($item['direccion']) ? $item['direccion'] : ''; ?>">
											<!-- editado por alex (bancos) -->
											<!-- value="<?php //echo $item['direccion']; 
														?>"> -->
											<label class="input-group-addon glyphicon glyphicon-pencil"></label>
										</div>
									</div>
									<div class="form-group form-group-contacto">
										<label class="col-xs-5 control-label" for="input_text_contacto">Contacto</label>
										<div class="input-group col-xs-7">
											<input
												type="text"
												class="form-control input_text"
												id="input_text_contacto"
												data-col="contacto"
												value="<?php echo isset($item['contacto']) ? $item['contacto'] : ''; ?>">

											<!-- editado por alex (bancos) -->
											<!-- value="<?php //echo $item['contacto']; 
														?>"> -->
											<label class="input-group-addon glyphicon glyphicon-pencil"></label>
										</div>
									</div>
									<div class="form-group form-group-color_hexadecimal">
										<label class="col-xs-5 control-label" for="input_text_color_hexadecimal">Color Hexadecimal</label>
										<div class="input-group col-xs-7">
											<input type="text"
												id="input_text_color_hexadecimal"
												class="form-control input_text"
												data-control="hue"
												data-col="color_hex"
												value="<?php echo isset($item['color_hex']) ? $item['color_hex'] : ''; ?>">

											<!-- editado por alex (bancos) -->
											<!-- value="<?php //echo $item['color_hex']; 
														?>"> -->
										</div>


									</div>
									<div class="form-group switch-box">
										<label for="checkbox" class="col-xs-5 control-label" for="switch_estado">Estado</label>
										<div class="col-xs-7">
											<input
												class="switch"
												id="switch_estado"
												type="checkbox"
												data-button="state"
												<?php echo isset($item["estado"]) && $item["estado"] ? 'checked="checked"' : ''; ?>
												data-table="tbl_bancos"
												data-id="<?php echo isset($item["id"]) ? $item["id"] : ''; ?>"
												color_hex
												data-col="estado"
												data-on-value="1"
												data-off-value="0"
												value="<?php echo isset($item['estado']) ? $item['estado'] : '0'; ?>">

											<!-- editado por alex (bancos) -->

											<!-- <?php //if ($item["estado"]) { 
													?>checked="checked" <?php //} 
																		?>
											data-id="<?php //echo $item["id"]; 
														?>"
											value="<?php //echo isset($item['estado']) ? $item['estado'] : '0'; 
													?>"> -->
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				<?php
				}
				?>
			</div>
		</div>
		<!-- FIN FORMULARIO CREAR NUEVO/EDITAR BANCO -->
	<?php
	} else {
		$list_query = $mysqli->query("SELECT  id,
										nombre,
										descripcion,
										observacion,
										telefono,
										direccion,
										contacto,
										color_hex,
										estado 
										FROM tbl_bancos ORDER BY id DESC");


		$list = array();
		while ($li = $list_query->fetch_assoc()) {
			$list[] = $li;
		}
		if ($mysqli->error) {
			print_r($mysqli->error);
		}
		$list_cols = array();

		$list_cols["id"] = "ID";
		$list_cols["nombre"] = "NOMBRE";
		$list_cols["descripcion"] = "DESCRIPCION";
		$list_cols["observacion"] = "OBSERVACION";
		$list_cols["telefono"] = "TELEFONO";
		$list_cols["direccion"] = "DIRECCION";
		$list_cols["color_hex"] = "COLOR HEX";
		$list_cols["estado"] = "ESTADO";
		$list_cols["opciones"] = "OPCIONES";
	?>
		<!-- TABLA BANCOS -->
		<div class="row">
			<div class="col-xs-12">
				<input type="hidden" class="export_bancos_filename" value="export_bancos_<?php echo date("c"); ?>">
				<table
					id="bancos_list"
					class="table table-striped table-hover table-condensed table-bordered dt-responsive" cellspacing="0" width="100%">
					<thead>
						<tr>
							<?php
							foreach ($list_cols as $key => $value) {
								if ($key == "id") {
							?><th class="w-20px">ID</th><?php
													} elseif ($key == "estado") {
														?><th class="w-85px text-center">ESTADO</th><?php
																								} elseif ($key == "opciones") {
																									?><th class="w-85px text-center">OPCIONES</th><?php
																																				} elseif ($key == "color_hex") {
																																					?><th class="w-85px text-center">COLOR HEX</th><?php
																																																} else {
																																																	?>
									<th><?php echo $value; ?></th>
							<?php
																																																}
																																															}
							?>
						</tr>

					</thead>
					<tbody>
						<?php
						foreach ($list as $l_k => $l_v) {
						?>
							<tr>
								<?php
								foreach ($list_cols as $key => $value) {



									if ($key == "opciones") {
								?>
										<td class="text-center">
											<a
												class="btn btn-rounded btn-default btn-sm btn-edit btn_editar_bancos"
												title="Editar"
												data-button="edit"
												data-href="./?sec_id=<?php echo $sec_id; ?>&amp;item_id=<?php echo $l_v["id"]; ?>"
												href="./?sec_id=<?php echo $sec_id; ?>&amp;item_id=<?php echo $l_v["id"]; ?>">
												<i class="glyphicon glyphicon-edit"></i>
											</a>
											<!-- <button 
												class="btn btn-rounded btn-default btn-sm btn-edit btn-preview btn_vista_previa_bancos" 
												title="Vista Previa"
												data-button="information" 
												data-table="<?php //echo 'tbl_bancos'; 
															?>"
												data-id="<?php //echo $l_v["id"]; 
															?>"
												>
												<i class="glyphicon glyphicon-info-sign"></i>
											</button>	 -->
										</td>
									<?php
									} elseif ($key == "estado") {
									?><td class="text-center"><?php
																if ($l_v["estado"]) {
																?><div class="btn btn-sm btn-default text-success btn-estado"><span class="glyphicon glyphicon-ok-circle"></span></div><?php
																																													} else {
																																														?><div class="btn btn-sm btn-default text-danger btn-estado"><span class="glyphicon glyphicon-remove-circle"></span></div><?php
																																																																												}
																																																																													?></td><?php
																																																																														} elseif ($key == "id") {
																																																																															?>
										<td class="text-right "><?php echo $l_v[$key]; ?></td>
									<?php
																																																																														} elseif ($key == "color_hex") {
									?>
										<td class="text-right color_hexadecimal_bancos" style="background-color:#<?php echo $l_v[$key]; ?> !important;"></td>
									<?php
																																																																														} else {
									?>
										<td><?php echo $l_v[$key]; ?></td>
								<?php
																																																																														}
																																																																													}
								?>
							</tr>
						<?php
						}
						?>
					</tbody>
				</table>
			</div>
		</div>
		<!-- FIN TABLA BANCOS -->
	<?php
	}
	?>
</div>