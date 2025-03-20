<?php 
include("db_connect.php");
include("sys_login.php");
if(isset($_POST["sec_reportes_solicitudes_get_solicitudes"])): 
?>	
	<?php
	$get_data = $_POST["sec_reportes_solicitudes_get_solicitudes"];

	$whereId = ($get_data['local_id'] != 'all') ? "AND sol.local =".$get_data['local_id'] : "";
	$whereEstados = ($get_data['estados'] != 'all') ? " AND sol.estado in (".$get_data['estados'].")" : "";
	$query = "
	SELECT
	sol.id,sol.motivo,sol.estado,sol.tipo_solicitud,sol.subtipo_solicitud,sol.local,sol.bet_id,sol.fecha_creacion,
	p.nombre, p.apellido_paterno,
	lc.nombre AS nombre_local,
	u.usuario,
	(SELECT a.nombre FROM tbl_areas a WHERE a.id = p.area_id) AS area,
	(SELECT c.nombre FROM tbl_cargos c WHERE c.id = p.cargo_id) AS cargo,
	(SELECT ts.descripcion FROM tbl_tipo_solicitud ts WHERE ts.id = sol.tipo_solicitud) AS tipo_solicitud_desc,
	(SELECT ss.descripcion FROM tbl_subtipo_solicitud ss WHERE ss.id = sol.subtipo_solicitud) AS subtipo_solicitud_desc
	FROM tbl_solicitud_prestamo  sol
	LEFT JOIN tbl_usuarios u ON (u.id = sol.usuario)
	LEFT JOIN tbl_personal_apt p ON (p.id = u.personal_id) 
	LEFT JOIN tbl_locales lc ON (lc.id = sol.local) 
	WHERE 
	sol.fecha_creacion >= '".$get_data['fecha_inicio']." 00:00:00' AND 
	sol.fecha_creacion <= '".$get_data['fecha_fin']." 23:59:59'
	AND sol.id IS NOT NULL ".$whereId.$whereEstados." ORDER BY sol.fecha_creacion DESC";

	$result = $mysqli->query($query);
	$this_menu = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sub_sec_id = '".$get_data['sub_sec_id']."' LIMIT 1")->fetch_assoc();
	$menu_id = $this_menu["id"];	
	?>
	<?php if($result->num_rows): ?>		
		<div class="row">
			<div class="col-xs-12">
				<div class="row table-responsive ">
					<table class="table table-bordered table-condensed">
						<thead>
							<tr>						
								<th>Fecha Creación</th>
								<th>Local Solicitante</th>
								<th>Tipo</th>
								<th>SubTipo</th>
								<th>Usuario</th>							
								<th>Area</th>
								<th>Cargo</th>								
								<th>Estado</th>
								<th>Motivo</th>							
								<th>Opt</th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ($result as $sol_id => $u_data) {
								?>
								<tr class="lu_item" data-id="<?php echo $u_id;?>">								
									<td><?php echo $u_data["fecha_creacion"];?></td>
									<td><?php echo $u_data["nombre_local"];?></td>
									<td><?php echo $u_data["tipo_solicitud_desc"];?></td>
									<td><?php echo $u_data["subtipo_solicitud_desc"];?></td>
									<td><?php echo $u_data["usuario"];?></td>								
									<td><?php echo $u_data["area"];?></td>
									<td><?php echo $u_data["cargo"];?></td>
									<td>												
										<?php if($u_data["estado"]=='0'){ ?>Pendiente<?php } ?>
										<?php if($u_data["estado"]=='1'){ ?>Aprobado<?php } ?>
										<?php if($u_data["estado"]=='2'){ ?>Abonado<?php } ?>
										<?php if($u_data["estado"]=='3'){ ?>Cancelado<?php } ?>													
										<?php if($u_data["estado"]=='4'){ ?>Expirado<?php } ?>													
										<?php if($u_data["estado"]=='5'){ ?>Recibido<?php } ?>													
										<?php if($u_data["estado"]=='6'){ ?>Abonado-Eliminacion-Turno<?php } ?>													
									</td>									
									<td><?php echo $u_data["motivo"];?></td>																																				
									<td>
										<div class="btn-group">
											<?php											
											if(array_key_exists($menu_id,$usuario_permisos) && in_array("ver_solicitud", $usuario_permisos[$menu_id])){
											?>
											<button type="button" data-id="<?php echo $u_data["id"]; ?>" data-bet_id="<?php echo $u_data["bet_id"]; ?>" data-estado="<?php echo $u_data["estado"]; ?>" class="btn btn-primary btn-xs reportes_ver_solicitud_modal_btn">Ver</button>
											<?php
											}
											?>														
										</div>
									</td>
								</tr>
								<?php
							}
							?>
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