<?php if(isset($_POST["sec_caja_get_faltantes"])): ?>

	<?php
	$get_data = $_POST["sec_caja_get_faltantes"];
	$locales_command = "";
	if($login["usuario_locales"]){
		$locales_command = " AND l.id IN (".implode(",", $login["usuario_locales"]).")";
	}
	//$whereId = ($get_data['local_id'] != 'all') ? "AND l.id =".$get_data['local_id'] : "";
	$whereId = ($get_data['local_id'] != 'all') ? "AND l.id =".$get_data['local_id'] : $locales_command;
	if ($get_data['caja_observacion_id'] == 'empty') {
		$whereObs = "AND CONCAT(oci.titulo,'@limit@',oci.descripcion) is NULL ";
	} else {
		$whereObs = ($get_data['caja_observacion_id'] != 'all') ? "AND oci.id =".$get_data['caja_observacion_id'] : $locales_command;
	}

	$query = "SELECT
	IFNULL(max(l.cc_id),l.id) as cc_id,
	MAX(l.nombre) as nombre,
	DATE_FORMAT(c.fecha_operacion, '%Y') AS year,
	DATE_FORMAT(c.fecha_operacion, '%m') AS month,
	DATE_FORMAT(c.fecha_operacion, '%d') AS day,
	c.turno_id,
	IFNULL(MAX(cdf11.valor),0) - IFNULL(MAX(cdf10.valor),0) AS faltantes,
	GROUP_CONCAT(DISTINCT COALESCE(c.observaciones)) as observaciones,
	CONCAT(oci.titulo,'@limit@',oci.descripcion) as oci_titulo,
	CONCAT_WS(' ', MAX(pa.nombre), MAX(pa.apellido_paterno), MAX(pa.apellido_materno)) as personal_name,
	MAX(pa.dni) as dni,
	c.validar
	FROM tbl_locales l
	INNER JOIN tbl_local_cajas lc ON lc.local_id = l.id
	INNER JOIN tbl_caja c ON c.local_caja_id = lc.id
	LEFT JOIN tbl_caja_observaciones_lista oci ON oci.id = c.id_oci
	INNER JOIN tbl_caja_detalle cd ON cd.caja_id = c.id
	INNER JOIN tbl_caja_datos_fisicos cdf11 ON (cdf11.caja_id = c.id AND cdf11.tipo_id = 11)
	INNER JOIN tbl_caja_datos_fisicos cdf10 ON (cdf10.caja_id = c.id AND cdf10.tipo_id = 10)
	LEFT JOIN tbl_usuarios u ON u.id = c.usuario_id
	LEFT JOIN tbl_personal_apt pa ON pa.id = u.personal_id
	WHERE
	c.fecha_operacion >= '".$get_data['fecha_inicio']." 00:00:00' AND
	c.fecha_operacion <= '".$get_data['fecha_fin']." 23:59:59'
	".$whereId."
	".$whereObs."
	GROUP BY l.id, c.turno_id, c.id
	HAVING faltantes < 0
	ORDER BY c.fecha_operacion, faltantes";
	$result = $mysqli->query($query);
	?>
	<?php if($result->num_rows): ?>
		<div class="row">
			<div class="col-xs-12">
				<button type="submit" class="btn btn-warning btn-xs btn_export_caja_faltantes pull-right">
					<span class="glyphicon glyphicon-download-alt"></span>
					Exportar XLS
				</button>
			</div>
		</div>

		<div class="row">
			<div class="col-xs-12 no-pad">
				<div class="row table-responsive ">
					<table id="table_faltante" class="table table-condensed table-bordered table-striped prel">
						<thead>
							<tr>
								<th rowspan="2" class="text-center bg-primary" style="width: 12.5%">Centro de Costo</th>
								<th rowspan="2" class="text-center bg-primary" style="width: 12.5%">Local</th>
								<th colspan="3" class="text-center bg-primary" style="width: 18.75%">Fecha</th>
								<th rowspan="2" class="text-center bg-primary" style="width: 6.25%">Turno</th>
								<th class="text-center bg-success" style="width: 6.25%">Efectivo</th>
								<th rowspan="2" class="text-center bg-secondary" style="width: 18.75%">Observaciones</th>
								<th rowspan="2" class="text-center bg-secondary" style="width: 18.75%">Observaciones control interno</th>
								<th colspan="2" class="text-center bg-primary" style="width: 25%">Usuario</th>
								<th rowspan="2" class="text-center bg-primary" style="width: 12.5%">Validado</th>
							</tr>
							<tr>
								<th class="text-center bg-secondary">Año</th>
								<th class="text-center bg-secondary">Mes</th>
								<th class="text-center bg-secondary">Dia</th>
								<th class="text-center bg-secondary">Faltante</th>
								<th class="text-center bg-secondary">Nombre del Cajero</th>
								<th class="text-center bg-secondary">Documento de Identidad</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($result as $row): ?>
								<tr>
									<?php foreach($row as $key => $field): ?>
										<?php if($key == "validar"): ?>
											<?php if((int)$field == 1): ?>
												<td class="text-center" style="background-color: #42b787;color: white;">Validado</td>
											<?php else: ?>
												<td class="text-center" style="background-color: #e52736;color: white;">No Validado</td>
											<?php endif; ?>
										<?php else: ?>
											<?php if($key != "observaciones"): ?>
												<?php
													if($key == "oci_titulo"){
														$contenido   = explode("@limit@", $field);
														$titulo      = array_key_exists(0, $contenido) ? $contenido[0] : '';
														$descripcion = array_key_exists(1, $contenido) ? $contenido[1] : '';
														echo '<td class="text-center"><i class="view_more_btn" title="'.$descripcion.'">'.$titulo.'</i></td>';
													}else{
														echo '<td class="text-center">'.$field.'</td>';
													}
												?>
											<?php else: ?>
											<?php
												echo '<td><i title="'.$field.'">'.substr($field, 0,50).'</i></td>';
											?>
											<?php endif; ?>
										<?php endif; ?>
									<?php endforeach; ?>
								</tr>
								<?php endforeach; ?>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<?php else: ?>
				<div class="alert alert-danger alert-dismissible fade in" role="alert">
					<strong>No hay información para esta busqueda.</strong>
				</div>
			<?php endif; ?>
			<?php endif; ?>
