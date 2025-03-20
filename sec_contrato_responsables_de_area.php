<div class="panel p-5">
	<div class="panel-heading text-center">
		<div class="panel-title">Responsables de área</div>
	</div>

	<?php
	global $mysqli;
	$menu_id = "";
	$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'contrato' AND sub_sec_id = 'mantenimiento' LIMIT 1");
	while ($r = $result->fetch_assoc())
		$menu_id = $r["id"];

	if (!array_key_exists($menu_id, $usuario_permisos) || !in_array("nuevo_responsable", $usuario_permisos[$menu_id])) {
		//nuevo_director
	} else {
	?>

		<div class="row mt-2 mb-2">
			<fieldset class="dhhBorder">
				<legend class="dhhBorder">Registro</legend>
				<form id="frm_responsable_area" method="POST" enctype="multipart/form-data" autocomplete="off">
					<div class="row">
						<div class="col-md-10">
							<div class="form-group">
								<label for="ReponsableInputEmail1">Responsable de Area</label>
								<select id="sec_con_new_res_ar_responsable_area" class="form-control" id="ReponsableInputEmail1"></select>
							</div>
						</div>
						<div class="col-md-2">
							<div class="form-group">
								<label for="exampleInputEmail1"></label>
								<button type="submit" class="btn form-control btn-info">Registrar</button>
							</div>
						</div>
					</div>
				</form>
			</fieldset>
		</div>

	<?php
	}
	?>
	<hr>
	<div class="row mt-3">
		<div class="table-responsive">
			<table id="tbl-responsable-area" class="table table-condensed">
				<thead>
					<tr>
						<th class="text-center">#</th>
						<th class="text-center">Nombres</th>
						<th class="text-center">Estado</th>
					</tr>
				</thead>
			</table>
		</div>
	</div>
</div>