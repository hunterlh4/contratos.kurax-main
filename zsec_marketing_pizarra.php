<?php
if($sub_sec_id=="pizarra"){
	?>
<div class="modal" id="marketing_pizarra_add_modal" data-backdrop="static">
	<div class="modal-dialog modal-sm">
		<div class="modal-content modal-rounded">
			<div class="modal-header">
				<button type="button" class="close add_pizarra_cerrar_btn"><span
						aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Agregar</h4>
			</div>
			<div class="modal-body">
				<form id="form_add_deuda">
					<div class="form-group">
						<label for="add_deuda_input_linea_1">Linea 1</label>
						<input type="text" data-col="linea_1" value="" class="form-control add_col"
							id="add_deuda_input_linea_1" placeholder="Team 1 vs Team 2">
					</div>
					<div class="form-group">
						<label for="add_deuda_input_linea_2">Linea 2</label>
						<input type="text" data-col="linea_2" value="" class="form-control add_col"
							id="add_deuda_input_linea_2" placeholder="07:00PM">
					</div>
					<div class="form-group form-group-fecha">
						<label class=" control-label" for="input_text-fecha">Fecha de publicación</label>
						<div class="input-group col-xs-12">
							<input data-col="fecha" type="text" class="form-control pizarra_datepicker add_col"
								id="input_text-fecha" value="<?php echo date(" Y-m-d");?>"
							readonly="readonly"
							>
							<label class="input-group-addon glyphicon glyphicon-calendar"
								for="input_text-fecha"></label>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button class="btn btn-success add_pizarra_btn">Agregar</button>
				<button class="btn btn-default add_pizarra_cerrar_btn">Cerrar</button>
			</div>
		</div>
	</div>
</div>

<div class="content container-fluid">
	<div class="page-header wide">
		<div class="row">
			<div class="col-xs-12 text-center">
				<h1 class="page-title"><i class="icon fa fa-fw fa-bullhorn"></i> Marketing - <i
						class="glyphicon glyphicon-blackboard"></i> Pizarra</h1>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12">
				<button class="btn btn-warning add_button" data-button="add">Agregar fecha</button>
			</div>
		</div>
	</div>
	<?php
		$piz_arr = array();
			$piz_command = "SELECT 
								p.id,
								p.fecha,
								p.linea_1, 
								p.linea_2, 
								p.fecha_ingreso, 
								p.usuario_id, 
								per.nombre AS usuario_nombre,
								p.estado 
							FROM tbl_marketing_pizarra p
							LEFT JOIN tbl_usuarios u ON (u.id = p.usuario_id)
							LEFT JOIN tbl_personal_apt per ON (per.id = u.personal_id)
							ORDER BY p.fecha DESC";
			$piz_query = $mysqli->query($piz_command);
			while($piz = $piz_query->fetch_assoc()){
				$piz_arr[]=$piz;
			}
			// echo $today;
		?>
	<!-- <div class="col-xs-12"> -->
	<div class="table-responsive">
		<table class="table table-condensed table-bordered">
			<thead>
				<tr>
					<th>ID</th>
					<th>Fecha Publicacion</th>
					<th>Linea 1</th>
					<th>Linea 2</th>
					<th>Fecha Ingreso</th>
					<th>Usuario Ingreso</th>
					<th>Activo</th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ($piz_arr as $key => $value) {
					?>
					<tr>
						<td><?php echo $value["id"];?></td>
						<td><?php echo $value["fecha"];?></td>
						<td><?php echo $value["linea_1"];?></td>
						<td><?php echo $value["linea_2"];?></td>
						<td><?php echo $value["fecha_ingreso"];?></td>
						<td><?php echo $value["usuario_nombre"];?></td>
						<td>
							<input 
								class="switch" 
								id="checkbox_2"
								type="checkbox" 
								<?php if($value["estado"]){ ?>checked="checked"<?php } ?>
								data-table="tbl_marketing_pizarra"
								data-id="<?php echo $value["id"];?>"
								data-col="estado"
								data-on-value="1"
								data-off-value="0"
								data-button="state">
						</td>
						<!-- <td></td> -->
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
	</div>
	<!-- </div> -->
</div>
<?php
}
?>