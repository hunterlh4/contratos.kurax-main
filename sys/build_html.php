<?php
function build_input($i, $css = false)
{
	global $m, $item, $mysqli;

	$missing = array("input_plugin");
	foreach ($missing as $m_v) {
		if (!array_key_exists($m_v, $i)) {
			$i[$m_v] = false;
		}
	}
	if (!$css) {
		$css["label"] = "col-xs-5";
		$css["input"] = "col-xs-7";
	}

	if ($i["input_type"] == "select") {
?>
		<div class="form-group form-group_<?php echo $i["input_col"]; ?>">
			<label class="col-xs-5 control-label" for="select-<?php echo $i["input_col"]; ?>"><?php echo $i["input_label"]; ?></label>
			<div class="input-group col-xs-7">
				<select
					class="form-control input_text"
					data-col="<?php echo $i["input_col"]; ?>"
					id="select-<?php echo $i["input_col"]; ?>"
					data-toggle="tooltip"
					title="<?php echo $i["input_title"]; ?>">
					<option value="0">-- Seleccione --</option>
					<?php
					$sel_query = $mysqli->query("SELECT id," . $i["input_cols"] . " FROM " . $i["input_table"] . " WHERE estado = '1' ORDER BY " . (strstr($i["input_cols"], ',') ? strstr($i["input_cols"], ',', true) : $i["input_cols"]) . " ASC");
					while ($sel = $sel_query->fetch_assoc()) {
						//explode(delimiter, string)
					?>
						<option
							value="<?php echo $sel["id"]; ?>"
							<?php if ($sel["id"] == $item[$i["input_col"]]) { ?>selected<?php } ?>><?php
																									foreach (explode(",", $i["input_cols"]) as $col_key => $col_val) {
																										echo $sel[$col_val];
																										echo " ";
																									}
																									?></option><?php
																											}
																												?>
				</select>
				<label
					class="input-group-addon glyphicon glyphicon-new-window select_add_dialog_btn"
					data-name="<?php echo $i["input_label"]; ?>"
					data-table="<?php echo $i["input_table"]; ?>"
					data-cols="<?php echo $i["input_cols"]; ?>"
					data-select="select-<?php echo $i["input_col"]; ?>"></label>
			</div>
		</div>
	<?php
	} elseif ($i["input_type"] == "text") {
	?>
		<div class="form-group form-group_<?php echo $i["input_col"]; ?>">
			<label class="<?php echo $css["label"]; ?> control-label" for="varchar_<?php echo $i["input_col"]; ?>"><?php echo $i["input_label"]; ?></label>
			<div class="input-group <?php echo $css["input"]; ?>" <?php if ($i["input_plugin"] == "datepicker") { ?>data-provide="datepicker" <?php } ?>>
				<input
					type="text"
					class="form-control input_text <?php if ($i["input_plugin"] == "datepicker") { ?>datepicker<?php } ?>"
					data-col="<?php echo $i["input_col"]; ?>"
					id="varchar_<?php echo $i["input_col"]; ?>"
					value="<?php echo htmlentities($item[$i["input_col"]]); ?>"
					placeholder="<?php echo $i["input_title"]; ?>"
					title="<?php echo $i["input_title"]; ?>"
					data-toggle="tooltip"
					data-placement="bottom">
				<?php
				if ($i["input_plugin"] == "datepicker") { ?><label class="input-group-addon glyphicon glyphicon-calendar" for="varchar_<?php echo $i["input_col"]; ?>"></label><?php } else {
																																												?><label class="input-group-addon glyphicon glyphicon-pencil" for="varchar_<?php echo $i["input_col"]; ?>"></label><?php
																																																																								}
																																																																									?>
			</div>
		</div>
	<?php
	}
}
function build_input_text($vc_k = false, $vc_v = false, $input_type = false)
{
	global $m, $item;
	?>
	<div class="form-group">
		<label class="col-xs-4 control-label" for="varchar_<?php echo $vc_k; ?>"><?php echo ucfirst($vc_v[0]); ?></label>
		<div class="input-group col-xs-8">
			<?php
			if ($input_type == "datepicker") {
			?>
				<input
					type="hidden"
					class="input_text"
					data-col="<?php echo $vc_k; ?>"
					value="<?php echo $item[$vc_k]; ?>"
					data-real-date="varchar_<?php echo $vc_k; ?>">
				<input
					type="text"
					class="form-control <?php if ($input_type == "datepicker") { ?>datepicker<?php } ?>"
					id="varchar_<?php echo $vc_k; ?>"
					value="<?php if ($item[$vc_k]) {
								echo date("d-m-Y", strtotime($item[$vc_k]));
							} ?>"
					placeholder="<?php echo @$vc_v[1]; ?>"
					title="<?php echo @$vc_v[1]; ?>"
					data-fake-date=""
					readonly="readonly">
			<?php
			} else {
			?>
				<input
					type="text"
					class="form-control input_text <?php if ($input_type == "datepicker") { ?>datepicker<?php } ?>"
					data-col="<?php echo $vc_k; ?>"
					id="varchar_<?php echo $vc_k; ?>"
					value="<?php echo isset($item[$vc_k]) ? htmlentities($item[$vc_k]) : ""; ?>"
					placeholder="<?php echo @$vc_v[1]; ?>"
					title="<?php echo @$vc_v[1]; ?>">
			<?php
			}
			?>

			<?php
			if ($input_type == "datepicker") { ?><label class="input-group-addon glyphicon glyphicon-calendar" for="varchar_<?php echo $vc_k; ?>"></label><?php } else {
																																							?><label class="input-group-addon glyphicon glyphicon-pencil" for="varchar_<?php echo $vc_k; ?>"></label><?php
																																																																	}
																																																																		?>
		</div>
	</div>
<?php
}
function build_checkbox($vc_k = false, $vc_v = false, $table = false)
{
	global $m, $item;
?>
	<div class="form-group switch-box">
		<label for="checkbox_<?php echo $vc_k; ?>" class="col-xs-4 control-label"><?php echo $vc_v; ?></label>
		<div class="col-xs-8">
			<input
				class="switch"
				id="checkbox_<?php echo $vc_k; ?>"
				type="checkbox"
				data-button="state"
				<?php //if($item["estado"]){ 
				?>checked="checked" <?php //} 
									?>
				data-table="<?php echo $table ?: $m["tabla"]; ?>"
				data-id="<?php echo $item["id"]; ?>"
				data-col="estado"
				data-on-value="1"
				data-off-value="0">
		</div>
	</div>
<?php
}
function build_input_select($vc_k = false, $vc_v = false)
{
	global $m, $item, $mysqli;

	// Validar que los parámetros sean correctos
	if (!$vc_k || !$vc_v || !isset($vc_v["name"]) || !isset($vc_v["cols"]) || !isset($vc_v["table"])) {
		return;
	}

	// Limpiar el nombre de la tabla para evitar inyección SQL
	$table = preg_replace('/[^a-zA-Z0-9_]/', '', $vc_v["table"]);
?>
	<div class="form-group">
		<label class="col-xs-4 control-label" for="select-<?php echo htmlspecialchars($vc_k); ?>">
			<?php echo htmlspecialchars($vc_v["name"]); ?>
		</label>
		<div class="input-group col-xs-8">
			<select class="form-control input_text" data-col="<?php echo htmlspecialchars($vc_k); ?>" id="select-<?php echo htmlspecialchars($vc_k); ?>" title="Seleccione el personal relacionado a este usuario">
				<option value="0">-- Seleccione --</option>
				<?php
				// Preparar la consulta SQL
				$sql = "SELECT id, " . implode(",", $vc_v["cols"]) . " FROM $table WHERE estado = '1' ORDER BY " . $vc_v["cols"][0] . " ASC";
				$sel_query = $mysqli->query($sql);

				if (!$sel_query) {
					error_log("Error en la consulta SQL: " . $mysqli->error);
				} else {
					while ($sel = $sel_query->fetch_assoc()) {
				?>
						<option value="<?php echo htmlspecialchars($sel["id"]); ?>"
							<?php echo ($sel["id"] == ($item[$vc_k] ?? '')) ? 'selected' : ''; ?>>

							<?php
							foreach ($vc_v["cols"] as $col_val) {
								echo htmlspecialchars($sel[$col_val]) . " ";
							}
							?>
						</option>
				<?php
					}
				}
				?>
			</select>
			<label class="input-group-addon glyphicon glyphicon-new-window select_add_dialog_btn" data-name="<?php echo htmlspecialchars($vc_v["name"]); ?>" data-table="<?php echo htmlspecialchars($vc_v["table"]); ?>" data-cols="<?php echo htmlspecialchars(implode(",", $vc_v["cols"])); ?>" data-select="select-<?php echo htmlspecialchars($vc_k); ?>">
			</label>
		</div>
	</div>
<?php
}
if (isset($_GET["opt"])) {
	include("db_connect.php");
	if ($_GET["opt"] == "select_add_dialog") {
		select_add_dialog($_GET["data"]);
	}
	if ($_GET["opt"] == "select_ubigeo_departamento") {
		select_ubigeo_departamento($_GET["data"]);
	}
	if ($_GET["opt"] == "select_ubigeo_provincia") {
		select_ubigeo_provincia($_GET["data"]);
	}
	if ($_GET["opt"] == "select_cliente_id") {
		select_cliente_id($_GET["data"]);
	}
	if ($_GET["opt"] == "preview_item") {
		preview_item($_GET["data"]);
	}
	if ($_GET["opt"] == "contrato_add_formula_modal") {
		contrato_add_formula_modal($_GET["data"]);
	}
	if ($_GET["opt"] == "get_formula_data") {
		get_formula_data($_GET["data"]);
	}

	//print_r($_GET);
}
function select_add_dialog($data)
{
	global $m, $item, $mysqli, $date;
	//print_r($data);
?>
	<div class="modal" id="select_add_dialog_modal" tabindex="-1" role="dialog" aria-labelledby="select_add_dialog_modal">
		<div class="modal-dialog " role="document">
			<div class="modal-content modal-rounded">
				<form>
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="select_add_dialog_modal">Agregar <?php echo $data["name"]; ?></h4>
					</div>
					<div class="modal-body col-xs-12">
						<input type="hidden" class="select_new_save_data" data-col="table" value="<?php echo $data["table"]; ?>">
						<input type="hidden" class="select_new_save_data" data-col="id" value="new">
						<input type="hidden" class="input_text" data-col="<?php echo $data["table"]; ?>_estado" value="1">

						<?php

						//print_r($data);
						if ($data["table"] == "tbl_clientes") {
						?>
							<div class="locales_form_local">
								<div class="col-xs-12 col-md-10 col-md-offset-1 ">
									<div class="form-group form-group_tipo_cliente">
										<label class="col-xs-5 control-label" for="select-tipo_cliente_id">Tipo de Cliente</label>
										<div class="input-group col-xs-7">
											<select
												required="required"
												class="form-control input_text"
												data-col="tbl_clientes_tipo_cliente_id"
												data-table="tbl_clientes"
												name="tipo_cliente_id"
												id="select-tipo_cliente_id"
												data-toggle="tooltip"
												title="Seleccione el tipo de cliente">
												<option value="">- Seleccione -</option>
												<?php
												$sel_query = $mysqli->query("SELECT id,nombre FROM tbl_cliente_tipos WHERE estado = '1' ORDER BY nombre ASC");
												while ($sel = $sel_query->fetch_assoc()) {
												?><option value="<?php echo $sel["id"]; ?>"><?php echo $sel["nombre"]; ?></option><?php
																																}
																																	?>
											</select>
										</div>
									</div>

									<div class="form-group form-group-dni ">
										<label class="col-xs-5 control-label" for="input_text-dni">DNI</label>
										<div class="input-group col-xs-7">
											<input
												type="text"
												class="form-control input_text "
												data-col="tbl_clientes_dni"
												data-table="tbl_clientes"
												name="dni"
												id="input_text-dni"
												placeholder="Ingrese el DNI"
												title=""
												data-toggle="tooltip"
												data-placement="bottom"
												data-original-title="Ingrese el DNI"
												required="required">
										</div>
									</div>
									<div class="form-group form-group-nombre ">
										<label class="col-xs-5 control-label" for="input_text-nombre">Nombre</label>
										<div class="input-group col-xs-7">
											<input
												type="text"
												class="form-control input_text "
												data-col="tbl_clientes_nombre"
												data-table="tbl_clientes"
												name="nombre"
												id="input_text-nombre"
												placeholder="Ingrese el Nombre"
												title=""
												data-toggle="tooltip"
												data-placement="bottom"
												data-original-title="Ingrese el Nombre"
												required="required">
										</div>
									</div>
									<div class="form-group form-group-ruc ">
										<label class="col-xs-5 control-label" for="input_text-ruc">RUC</label>
										<div class="input-group col-xs-7">
											<input
												type="text"
												class="form-control input_text "
												data-col="tbl_clientes_ruc"
												name="ruc"
												id="input_text-ruc"
												placeholder="Ingrese el RUC"
												title=""
												data-toggle="tooltip"
												data-placement="bottom"
												data-original-title="Ingrese el RUC"
												required="required">
										</div>
									</div>
									<div class="form-group form-group-razon_social ">
										<label class="col-xs-5 control-label" for="input_text-razon_social">Razón Social</label>
										<div class="input-group col-xs-7">
											<input
												type="text"
												class="form-control input_text "
												data-col="tbl_clientes_razon_social"
												name="razon_social"
												id="input_text-razon_social"
												placeholder="Ingrese la Razón Social"
												title=""
												data-toggle="tooltip"
												data-placement="bottom"
												data-original-title="Ingrese la Razón Social"
												required="required">
										</div>
									</div>

									<div class="form-group form-group-email">
										<label class="col-xs-5 control-label" for="input_text-email">Correo</label>
										<div class="input-group col-xs-7">
											<input
												type="text"
												class="form-control input_text "
												data-col="tbl_clientes_email"
												name="email"
												id="input_text-email"
												placeholder="Ingrese el Correo Electronico"
												title=""
												data-toggle="tooltip"
												data-placement="bottom"
												data-original-title="Ingrese el Correo Electronico"
												required="required">
										</div>
									</div>
									<div class="form-group form-group-telefono">
										<label class="col-xs-5 control-label" for="input_text-telefono">Telefono</label>
										<div class="input-group col-xs-7">
											<input
												type="text"
												class="form-control input_text "
												data-col="tbl_clientes_telefono"
												name="telefono"
												id="input_text-telefono"
												placeholder="Ingrese el Telefono"
												title=""
												data-toggle="tooltip"
												data-placement="bottom"
												data-original-title="Ingrese el Telefono"
												required="required">
										</div>
									</div>
									<div class="form-group form-group-celular">
										<label class="col-xs-5 control-label" for="input_text-celular">Celular</label>
										<div class="input-group col-xs-7">
											<input
												type="text"
												class="form-control input_text "
												data-col="tbl_clientes_celular"
												name="celular"
												id="input_text-celular"
												placeholder="Ingrese el Celular"
												title=""
												data-toggle="tooltip"
												data-placement="bottom"
												data-original-title="Ingrese el Celular"
												required="required">
										</div>
									</div>
									<div class="form-group form-group_direccion">
										<label class="col-xs-5 control-label" for="input_text-infocorp">¿está en INFOCORP?</label>
										<div class="input-group col-xs-7">
											<div class="radio">
												<label>
													<input type="radio" name="tbl_clientes_infocorp" value="0" required="required"> No, nunca
												</label>
											</div>
											<div class="radio">
												<label>
													<input type="radio" name="tbl_clientes_infocorp" value="1" required="required"> Si, estoy en INFOCORP
												</label>
											</div>
											<div class="radio">
												<label>
													<input type="radio" name="tbl_clientes_infocorp" value="2" required="required"> No, pero SI estuve en el pasado
												</label>
											</div>
										</div>
									</div>
									<div class="form-group form-group-banco_id">
										<label class="col-xs-5 control-label" for="select-banco_id">Banco</label>
										<div class="input-group col-xs-7">
											<select
												required="required"
												class="form-control input_text"
												data-col="tbl_clientes_banco_id"
												data-table="tbl_clientes"
												name="banco_id"
												id="select-banco_id"
												data-toggle="tooltip"
												title="Seleccione un Bancp">
												<option value="">- Seleccione -</option>
												<?php
												$sel_query = $mysqli->query("SELECT id,nombre FROM tbl_bancos WHERE estado = '1' ORDER BY nombre ASC");
												while ($sel = $sel_query->fetch_assoc()) {
												?><option value="<?php echo $sel["id"]; ?>"><?php echo $sel["nombre"]; ?></option><?php
																																}
																																	?>
											</select>
										</div>
									</div>
									<div class="form-group form-group-moneda_id">
										<label class="col-xs-5 control-label" for="select-moneda_id">Moneda</label>
										<div class="input-group col-xs-7">
											<select
												required="required"
												class="form-control input_text"
												data-col="tbl_clientes_moneda_id"
												data-table="tbl_clientes"
												name="moneda_id"
												id="select-moneda_id"
												data-toggle="tooltip"
												title="Seleccione el tipo de Contrato">
												<option value="">- Seleccione -</option>
												<?php
												$sel_query = $mysqli->query("SELECT id,nombre,simbolo,sigla FROM tbl_moneda WHERE estado = '1' ORDER BY nombre ASC");
												while ($sel = $sel_query->fetch_assoc()) {
												?><option value="<?php echo $sel["id"]; ?>"><?php echo $sel["sigla"]; ?> - <?php echo $sel["nombre"]; ?> (<?php echo $sel["simbolo"]; ?>)</option><?php
																																																}
																																																	?>
											</select>
										</div>
									</div>
									<div class="form-group form-group-numero_cuenta">
										<label class="col-xs-5 control-label" for="input_text-numero_cuenta">Numero de cuenta</label>
										<div class="input-group col-xs-7">
											<input
												type="text"
												class="form-control input_text "
												data-col="tbl_clientes_numero_cuenta"
												name="numero_cuenta"
												id="input_text-numero_cuenta"
												value=""
												placeholder="Ingrese el numero de cuenta"
												title=""
												required="required">
										</div>
									</div>
								</div>
							</div>
						<?php
						} elseif ($data["table"] == "tbl_locales") {
						?>
							<div class="col-xs-10 col-xs-offset-1 ">
								<input type="hidden" class="input_text" data-col="<?php echo $data["table"]; ?>_cliente_id" value="<?php echo $data["cliente_id"]; ?>">
								<div class="form-group form-local_tipo_id">
									<label class="col-xs-5 control-label" for="select-local_tipo_id">Tipo de Local</label>
									<div class="input-group col-xs-7">
										<select
											class="form-control input_text"
											data-col="<?php echo $data["table"]; ?>_tipo_id"
											name="tipo_id"
											id="select-local_tipo_id"
											data-toggle="tooltip"
											title="Seleccione el tipo de local"
											required="required">
											<option value="">- Seleccione -</option>
											<?php
											$sel_query = $mysqli->query("SELECT id,nombre FROM tbl_local_tipo WHERE estado = '1' ORDER BY nombre ASC");
											while ($sel = $sel_query->fetch_assoc()) {
											?><option value="<?php echo $sel["id"]; ?>"><?php echo $sel["nombre"]; ?></option><?php
																															}
																																?>
										</select>
									</div>
								</div>
								<div class="form-group form-estado_legal_id">
									<label class="col-xs-5 control-label" for="select-estado_legal_id">Estado Legal</label>
									<div class="input-group col-xs-7">
										<select
											class="form-control input_text"
											data-col="<?php echo $data["table"]; ?>_estado_legal_id"
											name="estado_legal_id"
											id="select-estado_legal_id"
											data-toggle="tooltip"
											title="Seleccione el tipo de local"
											required="required">
											<option value="">- Seleccione -</option>
											<?php
											$sel_query = $mysqli->query("SELECT id,nombre FROM tbl_local_estado_legal WHERE estado = '1' ORDER BY nombre ASC");
											while ($sel = $sel_query->fetch_assoc()) {
											?><option value="<?php echo $sel["id"]; ?>"><?php echo $sel["nombre"]; ?></option><?php
																															}
																																?>
										</select>
									</div>
								</div>
								<div class="form-group form-group-local_nombre">
									<label class="col-xs-5 control-label" for="input_text-local_nombre">Nombre del local</label>
									<div class="input-group col-xs-7">
										<input
											type="text"
											class="form-control input_text "
											data-col="<?php echo $data["table"]; ?>_nombre"
											name="local_nombre"
											id="input_text-local_nombre"
											placeholder="Ingrese el Nombre del local"
											title=""
											required="required">
									</div>
								</div>
								<div class="form-group form-group_direccion">
									<label class="col-xs-5 control-label" for="varchar_direccion">Dirección</label>
									<div class="input-group col-xs-7">
										<textarea
											class="form-control input_text "
											data-col="<?php echo $data["table"]; ?>_direccion"
											name="direccion"
											id="varchar_direccion"
											placeholder="Ingrese la Dirección"
											title=""
											data-toggle="tooltip"
											data-placement="bottom"
											data-original-title="Ingrese la Dirección"
											required="required"></textarea>
									</div>
								</div>

								<div class="form-group form-ubigeo_departamento">
									<label class="col-xs-5 control-label" for="select-ubigeo_departamento">Departamento</label>
									<div class="input-group col-xs-7">
										<select
											class="form-control input_text"
											data-col="<?php echo $data["table"]; ?>_ubigeo_cod_depa"
											name="ubigeo_departamento"
											id="select-ubigeo_departamento"
											data-toggle="tooltip"
											title="Seleccione el Departamento"
											required="required">
											<option value="">- Seleccione un Departamento -</option>
											<?php
											$sel_query = $mysqli->query("SELECT id,nombre,cod_depa FROM tbl_ubigeo WHERE cod_prov = '00' AND cod_dist = '00' AND estado = '1' ORDER BY nombre ASC");
											while ($sel = $sel_query->fetch_assoc()) {
											?><option value="<?php echo $sel["cod_depa"]; ?>"><?php echo $sel["nombre"]; ?></option><?php
																																}
																																	?>
										</select>
									</div>
								</div>
								<div class="form-group form-ubigeo_provincia">
									<label class="col-xs-5 control-label" for="select-ubigeo_provincia">Provincia</label>
									<div class="input-group col-xs-7">
										<select
											disabled="disabled"
											class="form-control input_text"
											data-col="<?php echo $data["table"]; ?>_ubigeo_cod_prov"
											name="ubigeo_provincia"
											id="select-ubigeo_provincia"
											data-toggle="tooltip"
											title="Seleccione la Provincia"
											required="required">
											<option value="">- Seleccione un Departamento -</option>
										</select>
									</div>
								</div>
								<div class="form-group form-ubigeo_distrito">
									<label class="col-xs-5 control-label" for="select-ubigeo_distrito">Distrito</label>
									<div class="input-group col-xs-7">
										<select
											disabled="disabled"
											class="form-control input_text"
											data-col="<?php echo $data["table"]; ?>_ubigeo_cod_dist"
											name="ubigeo_distrito"
											id="select-ubigeo_distrito"
											data-toggle="tooltip"
											title="Seleccione el Distrito"
											required="required">
											<option value="">- Seleccione un Departamento -</option>
										</select>
									</div>
								</div>

								<div class="form-group form-group_local_area">
									<label class="col-xs-5 control-label" for="varchar_local_area">Area del local</label>
									<div class="input-group col-xs-7">
										<input
											type="text"
											class="form-control input_text "
											data-col="<?php echo $data["table"]; ?>_area"
											name="area"
											id="varchar_local_area"
											placeholder="Ingrese el Area del local"
											title="Ingrese el Area del local"
											data-toggle="tooltip"
											data-placement="bottom"
											required="required">
										<div class="input-group-addon" title="Metros Cuadrados">m2</div>
									</div>
								</div>

								<div class="row row-no-margin">
									<div class="form-group form-otra_casa_apuestas">
										<label class="col-xs-5 control-label">¿El local cuenta con otra casa de apuestas?</label>
										<div class="input-group col-xs-7">
											<div class="radio-inline">
												<label>
													<input type="radio" name="<?php echo $data["table"]; ?>_otra_casa_apuestas" value="1" required="required"> Si
												</label>
											</div>
											<div class="radio-inline">
												<label>
													<input type="radio" name="<?php echo $data["table"]; ?>_otra_casa_apuestas" value="0" required="required"> No
												</label>
											</div>
											<div class="hidden_form hide_form_otra_casa_apuestas_des">
												<textarea
													placeholder="Detallar aquí cual es"
													id="form-otra_casa_apuestas_des"
													class="form-control"
													name="<?php echo $data["table"]; ?>_otra_casa_apuestas_des"
													rows="2"></textarea>
											</div>
										</div>
									</div>
								</div>
								<div class="row row-no-margin">
									<div class="form-group form-experiencia_casa_apuestas">
										<label class="col-xs-5 control-label">¿Ha trabajado anteriormente con una casa de apuestas?</label>
										<div class="input-group col-xs-7">
											<div class="radio-inline">
												<label>
													<input type="radio" name="<?php echo $data["table"]; ?>_experiencia_casa_apuestas" value="1" required="required"> Si
												</label>
											</div>
											<div class="radio-inline">
												<label>
													<input type="radio" name="<?php echo $data["table"]; ?>_experiencia_casa_apuestas" value="0" required="required"> No
												</label>
											</div>
											<div class="hidden_form hidden_form_experiencia_casa_apuestas_des">
												<textarea
													placeholder="Detallar cual"
													id="form-experiencia_casa_apuestas_des"
													class="form-control"
													name="<?php echo $data["table"]; ?>_experiencia_casa_apuestas_des"
													rows="2"></textarea>
											</div>
										</div>
									</div>
								</div>
							</div>
						<?php
						} elseif ($data["table"] == "tbl_facturacion_formulas") {
						?>
							<input type="hidden" class="input_text" data-col="<?php echo $data["table"]; ?>_fecha_creacion" value="<?php echo $date; ?>">
							<div class="row">
								<div class="col-xs-5 col-xs-offset-1">
									<div class="form-group form-group-nombre">
										<label class="col-xs-5 control-label" for="input_text-nombre">Nombre</label>
										<div class="input-group col-xs-7">
											<input
												type="text"
												class="form-control input_text "
												data-col="<?php echo $data["table"]; ?>_nombre"
												data-table="<?php echo $data["table"]; ?>"
												name="nombre"
												id="input_text-nombre">
										</div>
									</div>
									<div class="form-group form-participante_id">
										<label class="col-xs-5 control-label" for="select-participante_id">Participante</label>
										<div class="input-group col-xs-7">
											<select
												required="required"
												class="form-control input_text"
												data-col="<?php echo $data["table"]; ?>_participante_id"
												data-table="<?php echo $data["table"]; ?>"
												name="participante_id"
												id="select-participante_id">
												<option value="">- Seleccione -</option>
												<?php
												$sel_query = $mysqli->query("SELECT id,nombre FROM tbl_facturacion_participantes WHERE estado = '1' ORDER BY nombre ASC");
												while ($sel = $sel_query->fetch_assoc()) {
												?><option value="<?php echo $sel["id"]; ?>"><?php echo $sel["nombre"]; ?></option><?php
																																}
																																	?>
											</select>
										</div>
									</div>
									<div class="form-group form-moneda_id">
										<label class="col-xs-5 control-label" for="select-moneda_id">Moneda</label>
										<div class="input-group col-xs-7">
											<select
												required="required"
												class="form-control input_text"
												data-col="<?php echo $data["table"]; ?>_moneda_id"
												data-table="<?php echo $data["table"]; ?>"
												name="moneda_id"
												id="select-moneda_id">
												<option value="">- Seleccione -</option>
												<?php
												$sel_query = $mysqli->query("SELECT id,nombre,simbolo FROM tbl_moneda WHERE estado = '1' ORDER BY nombre ASC");
												while ($sel = $sel_query->fetch_assoc()) {
												?><option value="<?php echo $sel["id"]; ?>">(<?php echo $sel["simbolo"]; ?>) <?php echo $sel["nombre"]; ?></option><?php
																																								}
																																									?>
											</select>
										</div>
									</div>
									<?php if (1 == 2) { ?><div class="form-group form-aplica">
											<label class="col-xs-5 control-label" for="select-aplica">Aplica</label>
											<div class="input-group col-xs-7">
												<select
													required="required"
													class="form-control input_text"
													data-col="<?php echo $data["table"]; ?>_aplica"
													data-table="<?php echo $data["table"]; ?>"
													name="aplica"
													id="select-aplica">
													<option value="">- Seleccione -</option>
													<option value="1">Si</option>
													<option value="0">No</option>
												</select>
											</div>
										</div><?php } ?>
								</div>
								<div class="col-xs-5">
									<div class="form-group form-tipo_id">
										<label class="col-xs-5 control-label" for="select-tipo_id">Tipo</label>
										<div class="input-group col-xs-7">
											<select
												required="required"
												class="form-control input_text"
												data-col="<?php echo $data["table"]; ?>_tipo_id"
												data-table="<?php echo $data["table"]; ?>"
												name="tipo_id"
												id="select-tipo_id">
												<option value="">- Seleccione -</option>
												<?php
												$sel_query = $mysqli->query("SELECT id,nombre FROM tbl_facturacion_tipos WHERE estado = '1' ORDER BY nombre ASC");
												while ($sel = $sel_query->fetch_assoc()) {
												?><option value="<?php echo $sel["id"]; ?>"><?php echo $sel["nombre"]; ?></option><?php
																																}
																																	?>
											</select>
										</div>
									</div>

									<div class="form-group form-operador_id">
										<label class="col-xs-5 control-label" for="select-operador_id">Operador</label>
										<div class="input-group col-xs-7">
											<select
												required="required"
												class="form-control input_text"
												data-col="<?php echo $data["table"]; ?>_operador_id"
												data-table="<?php echo $data["table"]; ?>"
												name="operador_id"
												id="select-operador_id">
												<option value="">- Seleccione -</option>
												<?php
												$sel_query = $mysqli->query("SELECT id,operador FROM tbl_facturacion_operadores WHERE estado = '1' ORDER BY operador ASC");
												while ($sel = $sel_query->fetch_assoc()) {
												?><option value="<?php echo $sel["id"]; ?>"><?php echo $sel["operador"]; ?></option><?php
																																}
																																	?>
											</select>
										</div>
									</div>
									<div class="form-group form-fuente_id">
										<label class="col-xs-5 control-label" for="select-fuente_id">Fuente</label>
										<div class="input-group col-xs-7">
											<select
												required="required"
												class="form-control input_text"
												data-col="<?php echo $data["table"]; ?>_fuente_id"
												data-table="<?php echo $data["table"]; ?>"
												name="fuente_id"
												id="select-fuente_id">
												<option value="">- Seleccione -</option>
												<?php
												$sel_query = $mysqli->query("SELECT id,nombre,descripcion FROM tbl_facturacion_fuente WHERE estado = '1' ORDER BY nombre ASC");
												while ($sel = $sel_query->fetch_assoc()) {
												?><option value="<?php echo $sel["id"]; ?>" title="<?php echo $sel["descripcion"]; ?>"><?php echo $sel["nombre"]; ?></option><?php
																																											}
																																												?>
											</select>
										</div>
									</div>
									<div class="form-group form-sobre_id">
										<label class="col-xs-5 control-label" for="select-sobre_id">Aplica</label>
										<div class="input-group col-xs-7">
											<select
												required="required"
												class="form-control input_text"
												data-col="<?php echo $data["table"]; ?>_sobre_id"
												data-table="<?php echo $data["table"]; ?>"
												name="sobre_id"
												id="select-sobre_id">
												<option value="">- Seleccione -</option>
												<?php
												$sel_query = $mysqli->query("SELECT id,nombre,descripcion FROM tbl_facturacion_sobre WHERE estado = '1' ORDER BY nombre ASC");
												while ($sel = $sel_query->fetch_assoc()) {
												?><option value="<?php echo $sel["id"]; ?>" title="<?php echo $sel["descripcion"]; ?>"><?php echo $sel["nombre"]; ?></option><?php
																																											}
																																												?>
											</select>
										</div>
									</div>

								</div>
							</div>
						<?php
						} else {
						?>
							<div class="col-md-8 col-xs-12">
								<?php
								//print_r($data);
								$form_items = array();
								$form_items["input_text"][$data["table"] . "_nombre"] = array("Nombre", "Ingrese el nombre del " . $data["name"]);
								foreach ($form_items["input_text"] as $vc_k => $vc_v) {
									build_input_text($vc_k, $vc_v);
								}
								?>
							</div>
						<?php
						}
						?>
					</div>
					<div class="modal-footer">
						<button
							type="submit"
							class="btn btn-primary select_add_btn"
							data-select="<?php echo $data["select"]; ?>">Agregar</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
					</div>
				</form>
			</div>
		</div>
	</div>
<?php
}
function select_ubigeo_departamento($data)
{
	global $m, $item, $mysqli;
	$sel_query = $mysqli->query("SELECT id,nombre,cod_prov 
								FROM tbl_ubigeo 
								WHERE cod_depa = '" . $data["departamento_id"] . "' 
								AND cod_dist = '00' 
								AND cod_prov != '00' 
								AND estado = '1' 
								ORDER BY nombre");
	$ret = array();
	while ($sel = $sel_query->fetch_assoc()) {
		$ret[] = array("cod" => $sel["cod_prov"], "nombre" => $sel["nombre"]);
	}
	print_r(json_encode($ret));
}
function select_ubigeo_provincia($data)
{
	global $m, $item, $mysqli;
	$sel_query = $mysqli->query("SELECT id,nombre,cod_dist
								FROM tbl_ubigeo 
								WHERE cod_prov = '" . $data["provincia_id"] . "'
								AND cod_depa = '" . $data["departamento_id"] . "'
								AND cod_dist != '00' 
								AND estado = '1' ORDER BY nombre ASC");
	$ret = array();
	while ($sel = $sel_query->fetch_assoc()) {
		//$ret[$sel["cod_dist"]]=$sel["nombre"];
		$ret[] = array("cod" => $sel["cod_dist"], "nombre" => $sel["nombre"]);
	}
	print_r(json_encode($ret));
}
function select_cliente_id($data)
{
	global $m, $item, $mysqli;
	$sel_query = $mysqli->query("SELECT id,nombre FROM tbl_locales WHERE cliente_id = '" . $data["cliente_id"] . "' AND estado = '1' ORDER BY nombre ASC");
	$ret = array();
	$ret["cliente"] = $mysqli->query("SELECT id,tipo_cliente_id,ruc,dni,nombre,razon_social FROM tbl_clientes WHERE id = '" . $data["cliente_id"] . "'")->fetch_assoc();
	if ($ret["cliente"]["tipo_cliente_id"] == 2) {
		$ret["cliente"]["dni_o_ruc"] = $ret["cliente"]["ruc"];
		$ret["cliente"]["dni_o_ruc_label"] = "RUC";
	} elseif ($ret["cliente"]["tipo_cliente_id"] == 1) {
		$ret["cliente"]["dni_o_ruc"] = $ret["cliente"]["dni"];
		$ret["cliente"]["dni_o_ruc_label"] = "DNI";
	}
	$ret["options"] = array();
	while ($sel = $sel_query->fetch_assoc()) {
		$ret["options"][$sel["id"]] = $sel["nombre"];
	}
	print_r(json_encode($ret));
}
function preview_item($data)
{
	global $mysqli;
	//print_r($data);
	$query_cols = array();
	array_push($query_cols, "id");
	if ($data["table"] == "tbl_bancos") {
		array_push($query_cols, "nombre");
		array_push($query_cols, "observacion");
		array_push($query_cols, "telefono");
		array_push($query_cols, "direccion");
		array_push($query_cols, "contacto");
	}/*elseif($data["table"]=="tbl_usuarios"){
		array_push($query_cols, "nombre");
		array_push($query_cols, "descripcion");
		array_push($query_cols, "simbolo");
		array_push($query_cols, "sigla");
		array_push($query_cols, "predeterminada");
	}*/ else {
		$query_cols = array("*");
	}
	array_push($query_cols, "estado");

	if ($data["table"] == "tbl_usuarios") {
		$item = $mysqli->query("SELECT u.* ,
								a.nombre AS area_id,
								s.nombre AS sistema_id
								FROM " . $data["table"] . " u 
								LEFT JOIN tbl_areas a ON (a.id = u.area_id)
								LEFT JOIN tbl_sistemas s ON (s.id = u.sistema_id)
								WHERE u.id = '" . $data["id"] . "'")->fetch_assoc();
	} elseif ($data["table"] == "tbl_personal_apt") {
		$item = $mysqli->query("SELECT u.* ,
								a.nombre AS area_id,
								s.nombre AS sistema_id,
								c.nombre AS cargo_id
								FROM " . $data["table"] . " u 
								LEFT JOIN tbl_areas a ON (a.id = u.area_id)
								LEFT JOIN tbl_sistemas s ON (s.id = u.sistema_id)
								LEFT JOIN tbl_cargos c ON (c.id = u.cargo_id)
								WHERE u.id = '" . $data["id"] . "'")->fetch_assoc();
	} else {
		$item = $mysqli->query("SELECT " . implode(",", $query_cols) . " FROM " . $data["table"] . " WHERE id = '" . $data["id"] . "'")->fetch_assoc();
	}
	//print_r($item);
?>

	<div class="modal fade" id="prev_info" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
		<div class="modal-dialog" role="document">
			<div class="modal-content modal-rounded">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title" id="exampleModalLabel">Vista previa</h4>
				</div>
				<div class="modal-body pre-scrollable">
					<div class="row">
						<div class="col-xs-8 col-xs-offset-2">
							<table class="table table-striped">
								<tbody>
									<?php
									foreach ($item as $key => $value) {
										if ($key == "estado") {
											if ($value == 1) {
												$value = "activo";
											} elseif ($value == 0) {
												$value = "inactivo";
											}
										}
										if ($key == "predeterminada" || $key == "consultas" || $key == "consultas_web") {
											if ($value == 1) {
												$value = "Si";
											} elseif ($value == 0) {
												$value = "No";
											}
										}
										if ($key == "apellido_paterno") {
											$key = "Apellido Paterno";
										}
										if ($key == "apellido_materno") {
											$key = "Apellido Materno";
										}
										if ($key == "area_id") {
											$key = "Area";
										}
										if ($key == "sistema_id") {
											$key = "Sistema";
										}
										if ($key == "cargo_id") {
											$key = "Cargo";
										}
										if ($key == "consultas_web") {
											$key = "Consultas Web";
										}
										if (!in_array($key, array("password_md5", "password", "personal_id", "acceso", "personal", "consorcio_id"))) {
									?>
											<tr>
												<td class="strong"><?php echo ucfirst($key); ?>:</td>
												<td><?php echo htmlentities($value); ?></td>
											</tr>
									<?php
										}
									}
									?>

								</tbody>
							</table>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
				</div>
			</div>
		</div>
	</div>

<?php
}
function contrato_add_formula_modal($data)
{
	global $mysqli;
?>
	<div class="modal" id="contrato_add_formula_modal" tabindex="-1" role="dialog" aria-labelledby="contrato_add_formula_modal">
		<input type="hidden" class="save_data" data-col="table" value="tbl_contrato_formulas">
		<input type="hidden" class="save_data" data-col="id" value="new">
		<input type="hidden" class="input_text" name="contrato_id" value="<?php echo $data["id"]; ?>">
		<div class="modal-dialog " role="document">
			<div class="modal-content modal-rounded">
				<form>
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="contrato_add_formula_modal">Agregar Formula</h4>
					</div>
					<div class="modal-body col-xs-12">
						<div class="col-xs-12">
							<select>
								<option>Seleccione una formula plantilla</option>
								<option>f1</option>
								<option>f2</option>
							</select>
						</div>
						<div class="row">
							<div class="col-xs-6">
								<div class="form-group form-participante_id">
									<label class="col-xs-5 control-label" for="select-participante_id">Participante</label>
									<div class="input-group col-xs-7">
										<select
											required="required"
											class="form-control input_text"
											data-col="participante_id"
											data-table="tbl_contrato_formulas"
											name="participante_id"
											id="select-participante_id">
											<option value="">- Seleccione -</option>
											<?php
											$sel_query = $mysqli->query("SELECT id,nombre FROM tbl_facturacion_participantes WHERE estado = '1' ORDER BY nombre ASC");
											while ($sel = $sel_query->fetch_assoc()) {
											?><option value="<?php echo $sel["id"]; ?>"><?php echo $sel["nombre"]; ?></option><?php
																															}
																																?>
										</select>
									</div>
								</div>
								<div class="form-group form-servicio_id">
									<label class="col-xs-5 control-label" for="select-servicio_id">Servicio</label>
									<div class="input-group col-xs-7">
										<select
											required="required"
											class="form-control input_text"
											data-col="servicio_id"
											data-table="tbl_contrato_formulas"
											name="servicio_id"
											id="select-servicio_id">
											<option value="">- Seleccione -</option>
											<?php
											$sel_query = $mysqli->query("SELECT id,nombre FROM tbl_facturacion_servicios WHERE estado = '1' ORDER BY nombre ASC");
											while ($sel = $sel_query->fetch_assoc()) {
											?><option value="<?php echo $sel["id"]; ?>"><?php echo $sel["nombre"]; ?></option><?php
																															}
																																?>
										</select>
									</div>
								</div>
								<div class="form-group form-aplica">
									<label class="col-xs-5 control-label" for="select-aplica">Aplica</label>
									<div class="input-group col-xs-7">
										<select
											required="required"
											class="form-control input_text"
											data-col="aplica"
											data-table="tbl_contrato_formulas"
											name="aplica"
											id="select-aplica">
											<option value="">- Seleccione -</option>
											<option value="1">Si</option>
											<option value="0">No</option>
										</select>
									</div>
								</div>
								<div class="form-group form-tipo_id">
									<label class="col-xs-5 control-label" for="select-tipo_id">Tipo</label>
									<div class="input-group col-xs-7">
										<select
											required="required"
											class="form-control input_text"
											data-col="tipo_id"
											data-table="tbl_contrato_formulas"
											name="tipo_id"
											id="select-tipo_id">
											<option value="">- Seleccione -</option>
											<?php
											$sel_query = $mysqli->query("SELECT id,nombre FROM tbl_facturacion_tipos WHERE estado = '1' ORDER BY nombre ASC");
											while ($sel = $sel_query->fetch_assoc()) {
											?><option value="<?php echo $sel["id"]; ?>"><?php echo $sel["nombre"]; ?></option><?php
																															}
																																?>
										</select>
									</div>
								</div>
							</div>
							<div class="col-xs-6">

								<div class="form-group form-operador_id">
									<label class="col-xs-5 control-label" for="select-operador_id">Operador</label>
									<div class="input-group col-xs-7">
										<select
											required="required"
											class="form-control input_text"
											data-col="operador_id"
											data-table="tbl_contrato_formulas"
											name="operador_id"
											id="select-operador_id">
											<option value="">- Seleccione -</option>
											<?php
											$sel_query = $mysqli->query("SELECT id,operador FROM tbl_facturacion_operadores WHERE estado = '1' ORDER BY operador ASC");
											while ($sel = $sel_query->fetch_assoc()) {
											?><option value="<?php echo $sel["id"]; ?>"><?php echo $sel["operador"]; ?></option><?php
																															}
																																?>
										</select>
									</div>
								</div>
								<div class="form-group form-sobre_id">
									<label class="col-xs-5 control-label" for="select-sobre_id">Sobre</label>
									<div class="input-group col-xs-7">
										<select
											required="required"
											class="form-control input_text"
											data-col="sobre_id"
											data-table="tbl_contrato_formulas"
											name="sobre_id"
											id="select-sobre_id">
											<option value="">- Seleccione -</option>
											<?php
											$sel_query = $mysqli->query("SELECT id,nombre FROM tbl_facturacion_sobre WHERE estado = '1' ORDER BY nombre ASC");
											while ($sel = $sel_query->fetch_assoc()) {
											?><option value="<?php echo $sel["id"]; ?>"><?php echo $sel["nombre"]; ?></option><?php
																															}
																																?>
										</select>
									</div>
								</div>
								<div class="form-group form-moneda_id">
									<label class="col-xs-5 control-label" for="select-moneda_id">Moneda</label>
									<div class="input-group col-xs-7">
										<select
											required="required"
											class="form-control input_text"
											data-col="moneda_id"
											data-table="tbl_contrato_formulas"
											name="moneda_id"
											id="select-moneda_id">
											<option value="">- Seleccione -</option>
											<?php
											$sel_query = $mysqli->query("SELECT id,nombre,simbolo FROM tbl_moneda WHERE estado = '1' ORDER BY nombre ASC");
											while ($sel = $sel_query->fetch_assoc()) {
											?><option value="<?php echo $sel["id"]; ?>">(<?php echo $sel["simbolo"]; ?>) <?php echo $sel["nombre"]; ?></option><?php
																																							}
																																								?>
										</select>
									</div>
								</div>
								<div class="form-group form-desde">
									<label class="col-xs-5 control-label" for="input_text-desde">Desde</label>
									<div class="input-group col-xs-7">
										<input
											type="text"
											class="form-control input_text "
											data-table="tbl_contrato_formulas"
											data-col="desde"
											name="desde"
											id="input_text-desde">
										<div class="input-group-addon">>=</div>
									</div>
								</div>
								<div class="form-group form-hasta">
									<label class="col-xs-5 control-label" for="input_text-hasta">Hasta</label>
									<div class="input-group col-xs-7">
										<input
											type="text"
											class="form-control input_text "
											data-table="tbl_contrato_formulas"
											data-col="hasta"
											name="hasta"
											id="input_text-hasta">
										<div class="input-group-addon">
											<=< /div>
										</div>
									</div>
									<div class="form-group form-monto">
										<label class="col-xs-5 control-label" for="input_text-monto">Monto</label>
										<div class="input-group col-xs-7">
											<input
												type="text"
												class="form-control input_text "
												data-table="tbl_contrato_formulas"
												data-col="monto"
												name="monto"
												id="input_text-monto">
										</div>
									</div>


								</div>
							</div>

						</div>
						<div class="modal-footer">
							<button
								type="submit"
								class="btn btn-primary contrato_add_formula_btn"
								data-select="">Agregar</button>
							<button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>

						</div>
				</form>
			</div>
		</div>
	</div>
<?php
}
function get_formula_data($data)
{
	global $m, $item, $mysqli, $date;
	$formula = $mysqli->query("SELECT ff.id, ff.nombre, ff.aplica, ff.tipo_id
									,participante.nombre AS participante_nombre
									,servicio.nombre AS servicio_nombre
									,tipo.nombre AS tipo_nombre
									,operador.operador AS operador_nombre
									,sobre.nombre AS sobre_nombre
									,sobre.descripcion AS sobre_descripcion
									,fuente.nombre AS fuente_nombre
									,fuente.descripcion AS fuente_descripcion
									,moneda.simbolo AS moneda_simbolo
							FROM tbl_facturacion_formulas ff
							LEFT JOIN tbl_facturacion_participantes participante ON (participante.id = ff.participante_id)
							LEFT JOIN tbl_facturacion_servicios servicio ON (servicio.id = ff.servicio_id)
							LEFT JOIN tbl_facturacion_tipos tipo ON (tipo.id = ff.tipo_id)
							LEFT JOIN tbl_facturacion_operadores operador ON (operador.id = ff.operador_id)
							LEFT JOIN tbl_facturacion_fuente fuente ON (fuente.id = ff.fuente_id)
							LEFT JOIN tbl_facturacion_sobre sobre ON (sobre.id = ff.sobre_id)
							LEFT JOIN tbl_moneda moneda ON (moneda.id = ff.moneda_id)
							WHERE ff.id = '" . $data["id"] . "'")->fetch_assoc();
	//print_r($data);
?>
	<br>
	<div class="col-xs-12 panel">
		<table class="table table-striped table-user-information hidden-xs hidden-sm">
			<tr>
				<td class="td-title">Nombre</td>
				<td><?php echo $formula["nombre"]; ?></td>
				<td class="td-title">Tipo</td>
				<td><?php echo $formula["tipo_nombre"]; ?></td>
			</tr>
			<tr>
				<td class="td-title">Participante</td>
				<td><?php echo $formula["participante_nombre"]; ?></td>
				<td class="td-title">Operador</td>
				<td><?php echo $formula["operador_nombre"]; ?></td>
			</tr>
			<tr>
				<td class="td-title">Sobre</td>
				<td><?php echo $formula["sobre_nombre"]; ?></td>
				<td class="td-title">Moneda</td>
				<td><?php echo $formula["moneda_simbolo"]; ?></td>
			</tr>
			<tr>
				<td class="td-title">Fuente</td>
				<td title="<?php echo $formula["fuente_descripcion"]; ?>"><?php echo $formula["fuente_nombre"]; ?></td>
			</tr>
		</table>
		<table class="table table-striped table-user-information  hidden-md hidden-lg">
			<tr>
				<td class="td-title">Nombre</td>
				<td><?php echo $formula["nombre"]; ?></td>
			</tr>
			<tr>
				<td class="td-title">Tipo</td>
				<td><?php echo $formula["tipo_nombre"]; ?></td>
			</tr>
			<tr>
				<td class="td-title">Participante</td>
				<td><?php echo $formula["participante_nombre"]; ?></td>
			</tr>
			<tr>
				<td class="td-title">Operador</td>
				<td><?php echo $formula["operador_nombre"]; ?></td>
			</tr>
			<tr>
				<td class="td-title">Sobre</td>
				<td><?php echo $formula["sobre_nombre"]; ?></td>
			</tr>
			<tr>
				<td class="td-title">Moneda</td>
				<td><?php echo $formula["moneda_simbolo"]; ?></td>
			</tr>
		</table>
	</div>
	<div class="col-xs-12 ">
		<?php
		if ($formula["id"] == 17) {
		?>

			<?php
		} else {
			if ($formula["tipo_id"] == 1) {
			?>
				<div class="form-group detalle_item" data-detalle-num="0" data-pro-id="<?php echo $data["pro_id"]; ?>">
					<input type="hidden" name="desde" value="">
					<input type="hidden" name="hasta" value="">
					<label class="col-xs-5 control-label" for="input_text-monto">Monto</label>
					<div class="input-group col-xs-7">
						<input
							type="text"
							class="form-control "
							name="monto"
							id="input_text-monto"
							autofocus>
					</div>
				</div>
			<?php
			} else {
			?>
				<div class="detalle_holder">
					<table class="table table-striped" data-pro-id="<?php echo $data["pro_id"]; ?>">
						<tr>
							<td>Desde</td>
							<td>Hasta</td>
							<td>%</td>
							<td>Opt</td>
						</tr>
						<tr class=" form-group detalle_item" data-detalle-num="0" data-pro-id="<?php echo $data["pro_id"]; ?>">
							<td><input
									type="text"
									class="form-control "
									name="desde"
									id="input_text-desde"
									value="0"></td>
							<td><input
									type="text"
									class="form-control "
									name="hasta"
									id="input_text-hasta"
									value="100"></td>
							<td><input
									type="text"
									class="form-control "
									name="monto"
									id="input_text-monto"
									value="50"></td>
							<td><button class="btn btn-sm rem_btn hidden" data-pro-id="<?php echo $data["pro_id"]; ?>"><i class="icon fa fa-fw fa-trash-o"></i></button></td>
						</tr>
					</table>
					<button class="btn btn-block contrato_formula_add_quiebre_btn" data-pro-id="<?php echo $data["pro_id"]; ?>">Agregar quiebre</button>
				</div>
		<?php
			}
		}
		?>
		<br>
	</div>
<?php
}
?>