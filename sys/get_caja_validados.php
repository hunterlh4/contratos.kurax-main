<?php
if(isset($_POST["sec_caja_get_validados"])){
	$get_data = $_POST["sec_caja_get_validados"];

	$fecha_fin = date("Y-m-d",strtotime($get_data["fecha_fin"]." +1 day"));

	$where_id = $get_data["local_id"] == "all" ? "WHERE l.id != 1": "WHERE l.id = '".$get_data["local_id"]."'";

	if($login["usuario_locales"]){
		$where_id .= " AND l.id IN (".implode(",", $login["usuario_locales"]).")";
	}

	$query = "SELECT
		l.id,
		l.nombre,
		GROUP_CONCAT(DISTINCT CONCAT(p.nombre, ' ', p.apellido_paterno)) as analistas
		FROM tbl_locales l
		LEFT JOIN tbl_usuarios_locales ul ON(ul.local_id = l.id AND ul.estado = 1)
		LEFT JOIN tbl_usuarios u ON(u.id = ul.usuario_id AND u.estado = 1)
		LEFT JOIN tbl_personal_apt p ON(u.personal_id = p.id AND p.cargo_id=17 AND p.area_id = 22)
		".$where_id."
		GROUP BY ul.local_id
		ORDER BY l.nombre ASC";

	$local_query = $mysqli->query($query);
	$locals = array();
	while($loc = $local_query->fetch_assoc()){
		$locals[] = $loc;

	}
	$table = array();
	$cajas = array();

	$where_caja = $get_data["caja_validados"] == "all" ? "" : "AND COALESCE(c.validar, 0) = ".$get_data["caja_validados"];
	foreach($locals as $local){
		$caja_command = "SELECT
		c.id AS caja_id,
		c.fecha_operacion,
		c.turno_id,
		c.estado,
		c.validar
		FROM tbl_caja c
		LEFT JOIN tbl_local_cajas lc ON(lc.id = c.local_caja_id)
		LEFT JOIN tbl_locales l ON (l.id = lc.local_id)
		WHERE c.id != 1
		AND l.id = '".$local["id"]."'
		AND c.fecha_operacion >= '".$get_data["fecha_inicio"]."'
		AND c.fecha_operacion < '".$fecha_fin."' ".$where_caja."
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
				// $tr_in
				$tr = array();
				$tr["caja_id"]=$data["caja_id"];
				$tr["local_nombre"] = $local["nombre"];
				$tr["ano"] = substr($data["fecha_operacion"], 0,4);
				$tr["mes"] = substr($data["fecha_operacion"], 5,2);
				$tr["dia"] = substr($data["fecha_operacion"], 8,2);
				$tr["turno_id"] = $data["turno_id"];
				$tr["analistas"] = explode(",", $local["analistas"]);
				$tr["estado"]=($data["estado"]==1 ? "Cerrado" : "Abierto");

				$tr["validar"]=$data["validar"];

				$table[]=$tr;
			}
		}
	}


	?>
	<?php if(count($table)){ ?>
		<?php
		$tabla_no_validados = false;
		$permisos_boton = [];
		$query_boton = "SELECT p.usuario_id, p.menu_id, b.boton
		FROM tbl_permisos p
		LEFT JOIN tbl_botones b ON (p.boton_id = b.id)
		WHERE p.usuario_id = '" . $login["id"] . "' 
			AND p.estado = '1' 
			AND p.boton_nombre='Cajas no Validadas'";
		$usuario_permisos_query = $mysqli->query($query_boton);
		while ($usu_per = $usuario_permisos_query->fetch_assoc()) {
			$permisos_boton[] = $usu_per["boton"];
		}
		if(in_array('Cajas_no_Validadas',$permisos_boton)) 
		{ $tabla_no_validados = true;}
		if( $tabla_no_validados )
		{
			$cuadro_resumen = [];
			foreach($table as $nombre => $tr_i)
			{
				if(empty($tr_i["validar"]))
				{
					foreach($tr_i["analistas"] as $i => $nom)
					{
						$cuadro_resumen[$nom] = isset ( $cuadro_resumen[$nom] ) ? $cuadro_resumen[$nom] + 1 : 1;
					}
				}
			}
			$cuadro_resumen["Total General"] = array_sum(array_map(function($item) {
																		return $item;
																	}, $cuadro_resumen));
			?>
			<?php if (count($cuadro_resumen) > 0 )
			{
			?>
				<div class="col-lg-offset-4 col-lg-4 col-mg-12 col-xs-12">
					<div class="panel panel-success">
						<div class="panel-heading">
							<h3 class="panel-title">CAJAS NO VALIDADAS</h3>
						</div>
						<div class="panel-body rd-pad">
							<table class="table table-condensed table-bordered table-striped">
								<thead>
									<tr>
										<td><b>Analista</b></td>
										<td class="text-right"><b>Total</b></td>
									</tr>
								</thead>
								<tbody>
									<?php
										foreach($cuadro_resumen as $nombre => $total)
										{
										?>
										<tr>
											<th><?php echo $nombre;?></th>
											<td class="text-right"><?php echo $total;?></td>
										</tr>
										<?php
										}
									?>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			<?php }?>
		<?php }?>
		<div class="row">
			<button type="submit" id="btnExportValidados" name="btnExportValidados" class="btn btn-warning pull-right">
				<i class="glyphicon glyphicon-list-alt"></i> Exportar Excel
			</button>
		</div>

		<div class="row table-responsive valitable">
			<table id="tbl_reporte_resumen_turno" class="table table-condensed table-bordered table-striped" style="table-layout: fixed">
				<thead>
					<tr>
						<th style="width:20%">Local</th>
						<th>Año</th>
						<th>Mes</th>
						<th>Dia</th>
						<th>Turno</th>
						<th style="width:15%">Analista</th>
						<th>Estado</th>
						<th>Opt</th>
						<th>Validar</th>
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
							<td>
								<?php if(count($tr["analistas"]) > 1): ?>
									<span style="display: none" id="txtAnalistas">
										<div class="panel-group">
											<div class="panel panel-warning">
												<div class="panel-heading">
													<h4 class="modal-title">Analistas Tienda <b>#<?php echo $tr["local_nombre"]; ?></span></b></h4>
												</div>
												<div class="panel-body">
													<?php foreach($tr["analistas"] as $analista): ?>
														<?php echo $analista; ?><hr>
													<?php endforeach; ?>
												</div>
											</div>
										</div>
									</span>
									<button type="submit" id="btnShowAnalistas" name="btnShowAnalistas" class="btn btn-primary btn-block btn-xs">Analistas</button>
									<?php else: ?>
										<?php echo $tr["analistas"][0]; ?>
									<?php endif; ?>
								</td>
								<td class="text-center <?php echo $tr["estado"]=="Abierto" ?  "bg-danger" : "bg-success" ?>"><b><?php echo $tr["estado"]; ?></b></td>
								<td><a target="_blank" href="./?sec_id=caja&item_id=<?php echo $tr["caja_id"];?>"><i class="glyphicon glyphicon-new-window"></i> Ver Detalles</a></td>
								<td class="text-center <?php echo !$tr["validar"] ?  "bg-danger" : "bg-success" ?>"><b><?php echo $tr["validar"] ? "Validado" : "No Validado"; ?></b></td>
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
