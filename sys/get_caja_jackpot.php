<?php
if(isset($_POST["sec_caja_get_jackpot"])){
	$get_data = $_POST["sec_caja_get_jackpot"];

	$fecha_fin = date("Y-m-d",strtotime($get_data["fecha_fin"]." +1 day"));

	$where_id = $get_data["local_id"] == "all" ? "WHERE l.id != 1": "WHERE l.id = '".$get_data["local_id"]."'";
	$local_query = $mysqli->query("SELECT l.id, l.nombre FROM tbl_locales l ".$where_id." ORDER BY l.nombre ASC");
	$locals = array();
	while($loc = $local_query->fetch_assoc()){
		$locals[] = $loc;
	
}
	$table = array();
	$cajas = array();
	
	foreach($locals as $local){
		$caja_command = "SELECT 
		c.id AS caja_id,
		c.fecha_operacion,
		c.turno_id
		FROM tbl_caja c
		LEFT JOIN tbl_local_cajas lc ON(lc.id = c.local_caja_id)
		LEFT JOIN tbl_locales l ON (l.id = lc.local_id)
		WHERE c.id != 1
		AND l.id = '".$local["id"]."'
		AND c.fecha_operacion >= '".$get_data["fecha_inicio"]."'
		AND c.fecha_operacion < '".$fecha_fin."'
		ORDER BY c.fecha_operacion ASC, c.turno_id ASC";
		$caja_query = $mysqli->query($caja_command);
		if($mysqli->error){
			print_r($mysqli->error);
			exit();
		}
		$caja_data = array();
		while($c=$caja_query->fetch_assoc()){
			$caja_data[]=$c;
		}
		if(count($caja_data)){
			
			foreach ($caja_data as $data_id => $data) {
				$tr = array();
				$tr["caja_id"]=$data["caja_id"];
				$tr["local_nombre"] = $local["nombre"];
				$tr["ano"] = substr($data["fecha_operacion"], 0,4);
				$tr["mes"] = substr($data["fecha_operacion"], 5,2);
				$tr["dia"] = substr($data["fecha_operacion"], 8,2);
				$tr["turno_id"] = $data["turno_id"];

				$table[]=$tr;
			}
		}
	}

	
	?>
	<?php if(count($table)){ ?>
		<div class="row table-responsive">
			<table id="tbl_reporte_resumen_turno" class="table table-condensed table-bordered" style="table-layout: fixed">
				<thead>
					<tr>
						<th style="width:20%">Local</th>
						<th>Año</th>
						<th>Mes</th>
						<th>Dia</th>
						<th>Turno</th>
						<th class="bg-warning">Jackpot</th>
						<th>Opt</th>
					</tr>
				</thead>
				<tbody>	
					<?php foreach ($table as $tr): ?>						
						<tr>
							<td id="tblNombreLocal"><?php echo $tr["local_nombre"]; ?></td>
							<td><?php echo $tr["ano"]; ?></td>
							<td><?php echo $tr["mes"]; ?></td>
							<td><?php echo $tr["dia"]; ?></td>
							<td><?php echo $tr["turno_id"]; ?></td>
							<td class="bg-warning">999</td>
							<td><a target="_blank" href="./?sec_id=caja&item_id=<?php echo $tr["caja_id"];?>"><i class="glyphicon glyphicon-new-window"></i> Ver Detalles</a></td>
						</tr>
					<?php endforeach ?>
				</tbody>
			</table>
		</div>

		<div id="mdAnalistas" class="modal fade" >
			<div class="modal-dialog modal-md">
				<div class="modal-content">
					<div class="modal-body">
						<div class="row">
							<div class="col-md-12" id="divAnalistas"></div>
						</div>
					</div>

					<div class="modal-footer">
						<button type="button" class="btn btn-second" data-dismiss="modal">Cerrar</button>
					</div>
				</div>
			</div>
		</div>


	<?php }else{ ?>
		<div class="alert alert-danger alert-dismissible fade in" role="alert">
			<strong>No hay información para esta busqueda.</strong>
		</div>
	<?php }
}
?>