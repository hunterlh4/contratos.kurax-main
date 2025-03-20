<?php
include("db_connect.php");
include("sys_login.php");
if (isset($_POST["sec_caja_get_reporte"])) {
	$get_data = $_POST["sec_caja_get_reporte"];
	// print_r($get_data);
	// exit();
	$local_id = $get_data["local_id"];
	$fecha_inicio = $get_data["fecha_apertura"];
	$fecha_inicio_pretty = date("d-m-Y", strtotime($get_data["fecha_apertura"]));
	$fecha_fin = $get_data["fecha_aperturafin"];
	$fecha_fin_pretty = date("d-m-Y", strtotime($get_data["fecha_aperturafin"]));
	// $fecha_fin = $get_data["fecha_fin"];

	$rowlocal = "";

	if ($local_id == "_all_") {
		$rowlocal = "Todos";
	} else {
		$local = $mysqli->query("SELECT l.id, l.nombre FROM tbl_locales l WHERE l.id = '" . $local_id . "'")->fetch_assoc();
		$rowlocal = $local['nombre'];
	}
	//echo $rowlocal;exit;
	//$cajas_sql_command = "SELECT id, fecha_registro,data,login from tbl_auditoria where proceso='sec_caja_eliminar' and date(fecha_registro) >='".date("Y-m-d",strtotime($get_data["fecha_apertura"]))."' and date(fecha_registro) <='".date("Y-m-d",strtotime($get_data["fecha_aperturafin"]))."' ";
	$cajas_sql_command = "	 
	   SELECT id, fecha_registro, data, login
		FROM tbl_auditoria
		WHERE  proceso='sec_caja_eliminar' and fecha_registro >='" . date("Y-m-d", strtotime($get_data["fecha_apertura"])) . "' AND fecha_registro <= '" . date("Y-m-d", strtotime($get_data["fecha_aperturafin"]."+ 1 days")) . "'
		-- UNION ALL
		-- SELECT id, fecha_registro, data, login
		-- FROM tbl_auditoria_bu_20230522_184252
		-- WHERE  proceso='sec_caja_eliminar' and fecha_registro >='" . date("Y-m-d", strtotime($get_data["fecha_apertura"])) . "' AND fecha_registro <= '" . date("Y-m-d", strtotime($get_data["fecha_aperturafin"]."+ 1 days")) . "'	   
	   ";
	//$cajas_sql_command.= " and lc.local_id = '".$local_id."'";
	//echo $cajas_sql_command;
	$cajas_sql_command .= " order by fecha_registro desc";
	$cajas_sql_query = $mysqli->query($cajas_sql_command);
	if ($mysqli->error) {
		echo "ERROR: ";
		print_r($mysqli->error);
		exit();
	}
	$cdv = array();
	$i = 0;
	if ($local_id == "_all_") {
		while ($row_selected = $cajas_sql_query->fetch_assoc()) {			
			$data2 = json_decode($row_selected['data'], true);
			$mensaje = 'Sin Mensaje';
			if (array_key_exists('mensaje', $data2)) {
				$mensaje = $data2['mensaje'];
			};
			$caja = json_decode($data2['response'], true);
			$fechaapertura = 'Sin Fecha';
			$localID = "Sin Centro de Costo";
			if(!isset($caja['caja']['fecha_apertura']))continue;
			if (array_key_exists('caja', $caja)) {
				$fechaapertura = strtotime(date("d-m-Y", strtotime($caja['caja']['fecha_apertura'])));
				$localID = $caja['caja']['local_id'];
			}
			$usuarioElimina = json_decode($row_selected['login'], true);
			$cdv[$i]['id'] = $row_selected["id"];
			$cdv[$i]['fecha_registro'] = $row_selected["fecha_registro"];
			$cdv[$i]['usuario_elimina'] = $usuarioElimina['nombre'] . " " . $usuarioElimina['apellido_paterno'];
			$cdv[$i]['mensaje'] = $mensaje;
			$cdv[$i]['data'] = json_decode($row_selected["data"], true);
			$i++;
		}
	} else {
		while ($row_selected = $cajas_sql_query->fetch_assoc()) {
			$data2 = json_decode($row_selected['data'], true);
			$mensaje = 'Sin Mensaje';
			if (is_array($data2)) {
				if (array_key_exists('mensaje', $data2)) {
					$mensaje = $data2['mensaje'];
				};
				$caja = json_decode($data2['response'], true);
				if (is_array($caja)) {
					$fechaapertura = 'Sin Fecha';
					$localID = "Sin Centro de Costo";
					if (array_key_exists('caja', $caja)) {
						$fechaapertura = (is_array($caja["caja"]) ? (array_key_exists("fecha_apertura", $caja["caja"]) ? strtotime(date("d-m-Y", strtotime($caja['caja']['fecha_apertura']))) : "") : "");
						$localID = (is_array($caja["caja"]) ? (array_key_exists("local_id", $caja["caja"]) ? $caja['caja']['local_id'] : "") : "");
					}

					$usuarioElimina = json_decode($row_selected['login'], true);
					if ($localID == $local_id) {
						$cdv[$i]['id'] = $row_selected["id"];
						$cdv[$i]['fecha_registro'] = $row_selected["fecha_registro"];
						$cdv[$i]['mensaje'] = $mensaje;
						$cdv[$i]['usuario_elimina'] = $usuarioElimina['nombre'] . " " . $usuarioElimina['apellido_paterno'];
						$cdv[$i]['data'] = json_decode($row_selected["data"], true);
						$i++;
					}
				}
			}
		}
	}

	if (count($cdv)) {

		// print_r($table_total);

?>
		<?php if (array_key_exists(84, $usuario_permisos) && in_array("export", $usuario_permisos[84])) {
		?>
			<a href="export.php?export=tbl_caja_eliminada&amp;type=lista&amp;ini=<?php echo date("Y-m-d", strtotime($get_data["fecha_apertura"])); ?>&amp;fin=<?php echo date("Y-m-d", strtotime($get_data["fecha_aperturafin"])); ?>&amp;local=<?php echo $local_id; ?>" class="btn btn-success btn-sm export_list_btn pull-right" style="margin-bottom: 10px" download="cajasEliminadas_<?php echo $rowlocal; ?>_<?php echo date("d-m-Y", strtotime($get_data["fecha_apertura"])); ?>_al_<?php echo date("d-m-Y", strtotime($get_data["fecha_aperturafin"])); ?>.xls"><span class="glyphicon glyphicon-export"></span> Exportar Lista</a>

		<?php
		} ?>
		<table class="tbl_apertura_caja table table-bordered table-condensed" id="tbl_apertura_caja">
			<thead>
				<tr>
					<th>
						<div class="form-group">
							<div class="control-label">FECHA ELIMINACION</div>
						</div>
					</th>
					<th>
						<div class="form-group">
							<div class="control-label">USUARIO ELIMINACION</div>
						</div>
					</th>
					<th>
						<div class="form-group">
							<div class="control-label">LOCAL</div>
							<div class="input-group hidden">
								<input type="text" class="form-control single_searcher sec_caja_turno_searcher_td_local" data-item_class="tr_turno" data-holder_id="tbl_apertura_caja" data-where="td_local" autofocus="autofocus">
								<div class="input-group-addon cursor-pointer search_clear_btn">X</div>
							</div>
						</div>
					</th>
					<th>
						<div class="form-group">
							<div class="control-label">CENTRO DE COSTO</div>
						</div>
					</th>
					<th>
						<div class="form-group">
							<div class="control-label">USUARIO</div>
						</div>
					</th>
					<th>
						<div class="form-group">
							<div class="control-label">CAJA</div>
						</div>
					</th>
					<th>
						<div class="form-group">
							<div class="control-label">TURNO</div>
						</div>
					</th>
					<th>
						<div class="form-group">
							<div class="control-label">FECHA OPERACION</div>
						</div>
					</th>
					<th>
						<div class="form-group">
							<div class="control-label">FECHA APERTURA</div>
						</div>
					</th>
					<th>
						<div class="form-group">
							<div class="control-label">FECHA CIERRE</div>
						</div>
					</th>
					<th>
						<div class="form-group">
							<div class="control-label">MENSAJE</div>
						</div>
					</th>
					<th>
						<div class="form-group">
							<div class="control-label">ESTADO</div>
						</div>
					</th>
					<th>OPCION</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$locales_query = "SELECT id, cc_id FROM tbl_locales where cc_id IS NOT NULL and red_id = 1";
				$result = $mysqli->query($locales_query);
				while ($row = $result->fetch_assoc()) $locales[$row["id"]] = $row["cc_id"];
				foreach ($cdv as $key => $value) {
					$data2 = json_decode($cdv[$key]['data']['response'], true);
					$caja = $data2['caja'];
				?>
					<tr class="tr_turno">
						<td><?php echo $value['fecha_registro']; ?></td>
						<td><?php echo $value['usuario_elimina']; ?></td>
						<td><?php echo substr($caja['local_nombre'], strpos($caja['local_nombre'], " ")); ?></td>
						<td><?php echo isset($locales[$caja["local_id"]]) ? $locales[$caja["local_id"]] : ""; ?></td>
						<td><?php
							$sin_corchete = explode("]", $caja['usuario_nombre']);
							if (count($sin_corchete) == 2) {
								echo $sin_corchete[1];
							} else {
								echo $caja['usuario_nombre'];
							}
							?>
						</td>
						<td><?php echo $caja['caja_nombre']; ?></td>
						<td><?php echo $caja['turno']; ?></td>
						<td><?php echo $caja['fecha_operacion']; ?></td>
						<td><?php echo $caja['fecha_apertura']; ?></td>
						<td><?php echo $caja['fecha_cierre']; ?></td>
						<td style="cursor: pointer;" class="td_mensaje" data-mensaje="<?php echo $value['mensaje']; ?>">
							<?php
							$string = $value['mensaje'];
							if (strlen($string) > 5) {
								$string = substr($string, 0, 5) . "...";
							};
							echo $string; ?>

						</td>
						<td class="<?php echo ($caja["estado"] == 1 ? "bg-success" : ($caja["estado"] == 2 ? "bg-danger" : "bg-warning")) ?>"><?php echo $caja['estado_nombre']; ?></td>
						<td class="apertura_opcion_caja">
							<div class="container_btn_editar_apertura">
								<a class="btn btn-primary btn_id_editar_caja" target="_blank" title="Ver Detalle" href="./?sec_id=reportes&sub_sec_id=cajas_eliminadas&item_id=<?php echo $value["id"]; ?>">
									<i class="glyphicon glyphicon glyphicon-eye-open"></i>
								</a>
							</div>
						</td>
					</tr>
				<?php
				}
				?>
			</tbody>
			<tfoot>
			</tfoot>
		</table>

	<?php

	} else {
	?>
		<div class="alert alert-danger alert-dismissible fade in" role="alert">
			<strong>No hay información para esta busqueda.</strong>
		</div>
<?php
	}
}
?>