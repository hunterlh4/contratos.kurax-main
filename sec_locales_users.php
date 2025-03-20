<?php
	$local_users = [];
	$lu_command = "SELECT u.id, u.usuario, u.estado, u.password_md5,
	p.nombre, p.apellido_paterno,
	(SELECT a.nombre FROM tbl_areas a WHERE a.id = p.area_id) AS area,
	(SELECT c.nombre FROM tbl_cargos c WHERE c.id = p.cargo_id) AS cargo
	FROM tbl_usuarios_locales ul
	LEFT JOIN tbl_usuarios u ON (u.id = ul.usuario_id)
	LEFT JOIN tbl_personal_apt p ON (p.id = u.personal_id)
	WHERE ul.local_id = '".$item_id."' AND ul.estado = '1'
	AND (p.area_id = ('21') AND p.cargo_id IN (4,5) OR (p.area_id = ('22') AND p.cargo_id != 3)
 	   OR (p.area_id = '31' AND p.cargo_id IN (4,5,17))
	   OR (p.area_id = '28' AND p.cargo_id = 5)
    )";
	$lu_query = $mysqli->query($lu_command);
	while($lu=$lu_query->fetch_assoc()){
		$local_users[$lu["id"]]=$lu;
	}
	$fechaInicio 	= date("Y-m-d", strtotime("-2 months"));
	$fechaFin 		= date("Y-m-d", strtotime("+1 day"));
	?>
	<div class="tab-pane" id="tab_users">
		<div class="col-xs-8 col-xs-offset-2">
			<div class="panel">
				<div class="panel-heading">
					<div class="panel-title"><i class="icon fa fa-file-text-o muted"></i> Operaciones - Usuarios</div>
					<div class="panel-controls">
						<button class="btn btn-secondary btn-sm btn-block locales_add_usuario_modal_btn"><span class="glyphicon glyphicon-plus"></span></button>
					</div>
				</div>
				<div class="panel-body">
					<table class="table table-bordered table-condensed">
						<thead>
							<tr>
								<th>ID</th>
								<th>Usuario</th>
								<th>Nombre</th>
								<th>Apellidos</th>
								<th>Area</th>
								<th>Cargo</th>
								<th>Estado</th>
								<th>Opt</th>
							</tr>
						</thead>
						<tbody>
							<?php
							foreach ($local_users as $u_id => $u_data) {

								?>
								<tr class="lu_item" data-id="<?php echo $u_id;?>">
									<td><?php echo $u_id;?></td>
									<td><?php echo $u_data["usuario"];?></td>
									<td><?php echo $u_data["nombre"];?></td>
									<td><?php echo $u_data["apellido_paterno"];?></td>
									<td><?php echo $u_data["area"];?></td>
									<td><?php echo $u_data["cargo"];?></td>
									<td>
										<?php

										 $swich = "";

											if(($u_data["area"] === "Operaciones") && (($u_data["cargo"] === "Cajero") || ($u_data["cargo"] === "Supervisor"))

												|| (($u_data["area"] === "Agentes") && $u_data["cargo"] === "Cajero" )
												|| (($u_data["area"] === "Televentas") && $u_data["cargo"] === "Cajero" )
											 ){
												$swich.="<input class='switch switch-table' data-width='55px' id='checkbox_".$u_id."'";
												$swich .= "type='checkbox'";
												$swich .= "data-table='tbl_usuarios'";
												$swich .= "data-id='".$u_id."'";
												$swich .= "data-col='estado'";
												$swich .= "data-on-value='1'";
												$swich .= "data-off-value='0'";
												if($u_data["estado"]){
													$swich.="checked='checked'";
												}
												$swich .= "data-ignore='true'>";
										 }else{
											 $swich .= $u_data["estado"]==1?"Activo":"Inactivo";
										 }

										 echo $swich;
										 ?>
									</td>
									<td>
										<div class="btn-group">
											<button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
												<span class="caret"></span>
											</button>
											<ul class="dropdown-menu">
												<li>
													<?php
													if($u_data["password_md5"]){
														?><button class="btn btn-warning btn-sm lu_restaurar_pass_btn" data-id="<?php echo $u_id;?>">Restaurar contraseña</button><?php
													}else{
														?><button class="btn btn-danger btn-sm lu_restaurar_pass_btn" data-id="<?php echo $u_id;?>">Crear contraseña</button><?php
													}
													?>
												</li>
												<li>
													<button class="btn btn-danger btn-sm lu_remove_btn" data-id="<?php echo $u_id;?>" >Eliminar de este local</button>
												</li>
											</ul>
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
		<div class="col-xs-8 col-xs-offset-2">
			<div class="panel">
				<div class="panel-heading">
					<div class="panel-title"><i class="icon fa fa-file-text-o muted"></i> Operaciones - Usuarios - Logs</div>	
						<div class="panel-controls">
							<input type="date" id="idFromUserOperatinsLog" value="<?php echo($fechaInicio) ?>"/>
							<input type="date" id="idToUserOperatinsLog" value="<?php echo($fechaFin) ?>"/>
							<button id="idSearchUserOperatinsLog">Buscar</button>
						</div>			
				</div>
				<div class="panel-body">					
				<table id="idTableLocaleUserOperations" class="dataTables_wrapper compact" style="width:100%">
					<thead>
						<tr>
							<th>ID</th>
							<th>DE</th>
							<th>PARA</th>
							<th>ACCION</th>
							<th>FECHA</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
				</div>
			</div>
		</div>
	</div>
