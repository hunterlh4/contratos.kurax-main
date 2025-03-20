<?php
global $mysqli;
$menu_id = "";
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas 
							WHERE sec_id = 'mantenimientos' 
							AND sub_sec_id = 'programacion_pagador' LIMIT 1");
while ($r = $result->fetch_assoc())
	$menu_id = $r["id"];

if (array_key_exists($menu_id, $usuario_permisos) && in_array("view", $usuario_permisos[$menu_id])) {

	/*$permiso_eliminar='false';
	if(in_array("delete", $usuario_permisos[$menu_id])){
		$permiso_eliminar='true';
	}
	$permiso_editar='false';
	if(in_array("edit", $usuario_permisos[$menu_id])){
		$permiso_editar='true';
	}
	$permiso_guardar='false';
	if(in_array("save", $usuario_permisos[$menu_id])){
		$permiso_guardar='true';
	}*/

?>

<style type="text/css">
	body{
		background: none !important;
	}
	.colorpicker{
		z-index: 100000;
	}
</style>

<div class="sec_mantenimiento_programacion_pagador">
	<div id="loader_"></div>
	<div class="row">
		<div class="col-md-12">
			<div class="panel" style="border-color: transparent;">
				<div class="panel-heading" style="border-color: #01579b;background: #fff;">
					<div class="panel-title" style="color: #000;text-align: center;font-size: 22px;">
						<h2>Programación de Horarios - Pagador</h2>
					</div>
				</div>

				<div class="panel-body">
					
					<div class="row">
						<!--FILTROS-->
						<div class="col-md-12" style="margin-bottom: 30px; margin-top: 10px;">
							<div class="form-group col-md-3">
								<label>Pagador: </label>
								<select id="sec_pro_pag_select_pagadores" class="form-control select2" style="width: 100%;">
								</select>
							</div>
							<div class="form-group col-md-3">
								<label>Desde: </label>
								<input type="datetime-local" class="form-control" id="sec_pro_pag_desde_fecha" style="color: black;font-size: 15px;">
							</div>
							<div class="form-group col-md-3">
								<label>Hasta: </label>
								<input type="datetime-local" class="form-control" id="sec_pro_pag_hasta_fecha" style="color: black;font-size: 15px;">
							</div>
							<div class="col-md-3" style="vertical-align: center;">
								<label></label>
								<div class="form-control" style="border: 0px;">
									<button type="button" class="btn btn-success" style="border: none;" 
											onclick="sec_mant_pro_pag_agregar_programacion();">
										<i class="fa fa-plus"></i>
										Agregar
									</button>
									<button type="button" class="btn btn-info" style="border: none;" 
											onclick="sec_mant_pro_pag_listarProgramaciones();">
										<i class="fa fa-search"></i>
										Buscar
									</button>
								</div>
							</div>
						</div>

						<div class="row">
							<div class="form-group col-md-12" id="sec_pro_pag_div_tabla_programaciones">
								<table class="table" id="sec_pro_pag_table_programaciones" style="width: 100%;">
									<thead>
										<th>#</th>
										<th>Pagador</th>
										<th>Desde</th>
										<th>Hasta</th>
										<th>Fecha de Asignación</th>
										<th>Editar</th>
									</thead>
									<tbody>
									</tbody>
								</table>
							</div>
						</div>
					</div>

				</div>

			</div>
		</div>
	</div>

	<!--**************************************************************************************************-->
	<!-- MODAL EDITAR PROGRAMACION DE HORARIOS -->
	<!--**************************************************************************************************-->
	<div class="modal fade" id="sec_pro_pag_modal_edit_programacion_pago" role="dialog" 
		aria-labelledby="myModalLabel">
	  <div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12" style="text-align: center;">
						<h3 style="color: black;">Edición de Programación</h3>
					</div>
				</div>
				<div class="row">
					<div class="col-md-4 form-group">
						<label>Supervisor</label>
						<input type="text" readonly id="sec_pro_pag_input_pagador_edit" class="form-control">
						<input type="hidden" readonly id="sec_pro_pag_input_id_pagador_edit" class="form-control">
						<input type="hidden" readonly id="sec_pro_pag_input_id_programacion_edit" class="form-control">
					</div>
					<div class="col-md-4 form-group">
						<label>Desde: </label>
						<input type="datetime-local" class="form-control" id="sec_pro_pag_input_desde_edit" style="color: black;font-size: 15px;">
					</div>
					<div class="col-md-4 form-group">
						<label>Hasta: </label>
						<input type="datetime-local" class="form-control" id="sec_pro_pag_input_hasta_edit" style="color: black;font-size: 15px;">
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default pull-left" onclick="sec_mant_pro_pag_cancelar_edicion_programacion();">
					<b><i class="fa fa-close"></i> CANCELAR</b>
				</button>
				<button type="button" class="btn btn-success pull-right" onclick="sec_mant_pro_pag_validar_actualizacion_programacion();">
					<b><i class="fa fa-close"></i> ACTUALIZAR</b>
				</button>
			</div>
		</div>
	  </div>
	</div>

</div>

<?php
} else {
	echo "No tienes permiso para acceder a este recurso.";
}
?>