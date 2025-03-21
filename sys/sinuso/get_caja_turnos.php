<?php

if(isset($_POST["sec_caja_get_turnos"])){
	$count_limit = 10;
	$get_data = $_POST["sec_caja_get_turnos"];
	$local_id = $get_data["turnos_local_id"];
	if($login){
		?>
		<div class="table-responsive">
			<table class="tbl_apertura_caja table table-bordered table-condensed" id="tbl_apertura_caja">
				<thead>
					<tr>
						<th>
							<div class="form-group">
								<div class="control-label">ID</div>
							</div>
						</th>
						<th>
							<div class="form-group">
								<div class="control-label">LOCAL</div>
								<div class="input-group hidden">
									<input
										type="text"
										class="form-control single_searcher sec_caja_turno_searcher_td_local"
										data-item_class="tr_turno"
										data-holder_id="tbl_apertura_caja"
										data-where="td_local"
										autofocus="autofocus">
										<div class="input-group-addon cursor-pointer search_clear_btn">X</div>
								</div>
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
								<div class="control-label">ESTADO</div>
							</div>
						</th>
						<th>OPCION</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$cajas_sql_command = "SELECT
								c.id
								-- ,l.nombre AS local_nombre
								,CONCAT('[',l.cc_id,']',' ',l.nombre) AS local_nombre
								,CASE WHEN u.usuario IS NOT NULL
								THEN IF(u.personal_id,CONCAT('[',u.usuario,']',' ',IFNULL(p.nombre,''),' ',IFNULL(p.apellido_paterno,'')),u.usuario)
								ELSE '' END AS usuario_nombre
								,lc.nombre AS caja_nombre
								,ct.nombre AS caja_tipo
								,IFNULL(c.turno_id,'-') AS turno
								,c.fecha_operacion
								,c.fecha_apertura
								,c.fecha_cierre
								,c.estado
								,IF(c.estado=1,'Cerrado',IF(c.estado=2,'Re-Abierto','Abierto')) as estado_nombre
								,c.validar
							FROM tbl_caja c
							LEFT JOIN tbl_local_cajas lc ON (lc.id = c.local_caja_id)
							LEFT JOIN tbl_caja_tipos ct ON (ct.id = lc.caja_tipo_id)
							LEFT JOIN tbl_locales l ON (l.id = lc.local_id)
							LEFT JOIN tbl_usuarios u ON (u.id = c.usuario_id)
							LEFT JOIN tbl_personal_apt p ON (p.id = u.personal_id)
							WHERE lc.local_id = '".$local_id."'";


					// if($login["usuario_locales"]){
					// 	$cajas_sql_command.=" WHERE lc.local_id IN (".implode(",", $login["usuario_locales"]).")";
					// }
					$cajas_sql_command.="ORDER BY c.fecha_operacion DESC, l.id ASC, c.fecha_apertura DESC LIMIT ".$count_limit;
					//echo $cajas_sql_command;exit();
					$cajas_sql_query = $mysqli->query($cajas_sql_command);
					if($mysqli->error){
						echo "ERROR: ";
						print_r($mysqli->error);
						exit();
					}

					$aux = 0;
					while($caj=$cajas_sql_query->fetch_assoc()){
						?>
						<tr class="tr_turno">
							<td class=""><?php echo $caj["id"]; ?></td>
							<td class="td_local"><?php echo $caj["local_nombre"]; ?></td>
							<td class=""><?php echo $caj["usuario_nombre"]; ?></td>
							<td class=""><?php echo $caj["caja_nombre"]; ?></td>
							<td><?php echo $caj["turno"]; ?></td>
							<td><?php echo $caj["fecha_operacion"]; ?></td>
							<td><?php echo $caj["fecha_apertura"]; ?></td>
							<td><?php echo $caj["fecha_cierre"]; ?></td>
							<td class="<?php echo ($caj["estado"]==1 ? "bg-success" : ($caj["estado"]==2 ? "bg-danger" : "bg-warning"))?>"><?php echo $caj["estado_nombre"]; ?></td>
							<td class="apertura_opcion_caja">
								<?php
									if(
										($aux <= 1 
											&& (
												$login["area_id"] == 21
												|| $login["area_id"] == 9 
												|| $login["area_id"] == 16 
												|| $login["area_id"] == 6
											)
										)
										|| (
												(
													$login["area_id"] == 21
													&& !$caj["estado"] 
													&& !$caj["validar"] 
													&& (
														$login["cargo_id"] == 5 
														|| $login["cargo_id"] == 16
													)
												)
												|| (
													$login["area_id"] == 9 
													&& !$caj["validar"] 
													&& (
														$login["cargo_id"] == 18 
														|| $login["cargo_id"] == 16
													)
												)
												|| ($login["area_id"] == 6)
												|| ($login["area_id"] == 23)

												|| ($login["area_id"] == 31 && $login["cargo_id"] == 5)
												|| ($login["area_id"] == 31 && $login["cargo_id"] == 4)//televentas supervisor
												|| ($login["area_id"] == 31 && $login["cargo_id"] == 23)//televentas pagador
												|| ($login["area_id"] == 31 && $login["cargo_id"] == 24)//televentas digitador

										)
									): ?>
										<div class="container_btn_editar_apertura" style="display: inline-block;">
											<a class="btn btn-primary btn-sm btn_id_editar_caja"
												title="Editar Turno"
												href="./?sec_id=caja&item_id=<?php echo $caj["id"]; ?>">
												<i class="glyphicon glyphicon-pencil"></i>
											</a>
										</div>
								<?php endif;?>
								<?php
									if (in_array("delete", $usuario_permisos[75])) {
										$fechaApertura = strtotime($caj["fecha_apertura"]);
										$fechaActual = strtotime(date("Y-m-d H:i:s"));
										$fechaHace7Dias = strtotime("-7 days", $fechaActual);
										if ($login["cargo_id"] == obtener_cargo_analista_control_interno('caja_cargo_control_interno') && $login["area_id"] == obtener_area_analista_control_interno('caja_area_control_interno') ){
											if ($fechaApertura >= $fechaHace7Dias && $fechaApertura <= $fechaActual) {
												?>
												<button data-item_id="<?php echo $caj["id"]; ?>" data-local="<?php echo $local_id; ?>" data-fecha_operacion="<?php echo $caj["fecha_operacion"]; ?>" class="btn btn-danger btn-sm caja_eliminar_turno_btn" data-button="delete">
													<span class='glyphicon glyphicon-remove'></span>
												</button>
												<?php
											}
										}else{
											?>
											<button data-item_id="<?php echo $caj["id"]; ?>" data-local="<?php echo $local_id; ?>" data-fecha_operacion="<?php echo $caj["fecha_operacion"]; ?>" class="btn btn-danger btn-sm caja_eliminar_turno_btn" data-button="delete">
												<span class='glyphicon glyphicon-remove'></span>
											</button>
											<?php
										}
										
									}
								?>
							</td>
						</tr>
						<?php
						$aux++;
					}
					?>
				</tbody>
				<tfoot>
				</tfoot>
			</table>
		</div>
		<div class="alert alert-warning text-center">
			<b>Solo se muestran los últimos <?php echo $count_limit; ?> registros.</b>
		</div>
		<?php
	}
}
function obtener_cargo_analista_control_interno($codigo){
    global $mysqli;

    $sel_query = $mysqli->query("SELECT codigo,valor FROM tbl_parametros_generales WHERE codigo = '".$codigo."' LIMIT 1");
    while($sel = $sel_query->fetch_assoc())
    {
        $cargo=$sel['valor'];
    }

    return $cargo;
}

function obtener_area_analista_control_interno($codigo){
    global $mysqli;

    $sel_query = $mysqli->query("SELECT codigo,valor FROM tbl_parametros_generales WHERE codigo = '".$codigo."' LIMIT 1");
    while($sel = $sel_query->fetch_assoc())
    {
        $area=$sel['valor'];
    }

    return $area;
}
?>
