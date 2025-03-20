<?php
include("db_connect.php");
include("sys_login.php");

if (isset($_GET["action"])) {
	//Se activa o desactiva un grupo
	if ($_GET["action"] == "toggleActive") {
		$mysqli->query("START TRANSACTION");
		$query = "UPDATE tbl_usuarios_grupos 
			SET estado = IF(estado=1, 0, 1) 
			WHERE id = " . $_POST["groupId"];
		$mysqli->query($query);

		$query = "UPDATE tbl_usuarios 
			SET estado = IF(estado=1, 0, 1)
			WHERE grupo_id = " . $_POST["groupId"] . " AND
			manual = 0";
		$mysqli->query($query);
		$mysqli->query("COMMIT");
	}
	//Se elimina un grupo definitivamente
	else if ($_GET["action"] == "eliminar_grupo") {
		$mysqli->query("START TRANSACTION");
		$query = "DELETE FROM tbl_usuarios_grupos WHERE id = " . $_POST["groupId"];
		$mysqli->query($query);

		$query = "DELETE FROM tbl_permisos WHERE usuario_id = 0 AND grupo_id=" . $_POST["groupId"];
		$mysqli->query($query);

		$query = "UPDATE tbl_usuarios SET grupo_id=NULL WHERE grupo_id =" . $_POST["groupId"];
		$mysqli->query($query);
		$mysqli->query("COMMIT");
	}
	//Se crea grupo
	else if ($_GET["action"] == "guardar_grupo") {
		$mysqli->query("START TRANSACTION");
		$query = "INSERT INTO tbl_usuarios_grupos (nombre, descripcion,estado)
				VALUES ('" . $_POST["name"] . "', '" . $_POST["desc"] . "', 1)";
		$group_id = $mysqli->query($query) === TRUE ? $mysqli->insert_id : FALSE;
		$query = "INSERT INTO tbl_permisos (
			grupo_id,
			menu_id,
			boton_id,
			boton_nombre,
			estado,
			usuario_id)
			VALUES ";
		foreach ($_POST["perms"] as $perm) {
			$query .= "(
					" . $group_id . ",
					" . addslashes($perm["menuId"]) . ",
					" . addslashes($perm["btnId"]) . ",
					'" . addslashes($perm["nombre_permiso"]) . "',
					" . $perm["active"] . ",
					0),";
		}
		unset($_POST["perms"]);
		$query = substr($query, 0, -1);
		$mysqli->query($query);
		$mysqli->query("COMMIT");

		// Se guarda la información del nuevo grupo
		$query = "SELECT 
			id,
			nombre,
			descripcion,
			estado 
			FROM tbl_usuarios_grupos 
			WHERE nombre = '" . $_POST["name"] . "' LIMIT 1";
		$result = $mysqli->query($query);
		unset($perm);
		foreach ($result as $res_gq) {
			error_reporting(0);
			$nuevo_td = '
			<tr id="tr_grupoid_' . $res_gq["id"] . '">
				<td id="grupoId">' . $res_gq["id"] . '</td>
				<td>
					<span id="txtGrupoNombreHidden_' . $res_gq["id"] . '">' . $res_gq["nombre"] . '</span>
					<br>
					<span id="txtGrupoDescHidden_' . $res_gq["id"] . '"><small><i>' . $res_gq["descripcion"] . '</i></small></span>
				</td>
				<td class="text-center">
					<input type="hidden" id="txtEstadoGrupo_' . $res_gq["id"] . '" name="txtEstadoGrupo" value="1">
					<button id="btnToogleGrupoEstado_' . $res_gq["id"] . '" class="btn btn-xs btn-success" onclick="sec_grupo_cambiar_estado_grupo(' . $res_gq["id"] . ')">
						<i id="icoToogleGrupeEstado_' . $res_gq["id"] . '" class="glyphicon glyphicon-ok" title="Desactivar Rol"></i>
					</button>
				</td>
				<td class="text-center">
					<button name="btnEditarGrupo" class="btn btn-xs" onclick="sec_grupo_modal_check_grupo(' . $res_gq["id"] . ')">
						<i class="glyphicon glyphicon-edit" title="Editar Rol"></i>
					</button>
					<button name="btnRemoverGrupo" class="btn btn-xs" onclick="sec_grupo_eliminar_grupo(' . $res_gq["id"] . ')">
						<i class="glyphicon glyphicon-remove" title="Remover Rol"></i>
					</button>
				</td>
			</tr>';
		}
		echo $nuevo_td;
	}
	//Se editan los permisos de un grupo
	else if ($_GET["action"] == "actualizar_grupo") {
		$_POST['perms'] = $_POST['perms'][0];

		$grupo_usuarios = [];
		$result = $mysqli->query("SELECT id FROM tbl_usuarios WHERE grupo_id = " . $_POST["id"] . "");
		while ($r = $result->fetch_assoc()) $grupo_usuarios[] = $r["id"];
		$query = "
			UPDATE tbl_usuarios_grupos 
			SET 
				nombre = '" . $_POST["name"] . "',
				descripcion = '" . $_POST["desc"] . "'
			WHERE 
				id = " . $_POST["id"];
		$mysqli->query($query);
		foreach ($_POST['perms'] as $perm) {
			try {
				$mysqli->query("START TRANSACTION");

				// Comprobamos si el grupo tiene algún usuario asociado
				if (!empty($grupo_usuarios)) {
					$permiso_usuarios = [];
					$query = "
						SELECT 
						usuario_id 
						FROM tbl_permisos 
						WHERE 
							usuario_id IN (" . implode(',', $grupo_usuarios) . ") AND
							grupo_id=" . $_POST["id"] . " AND
							menu_id=" . $perm["menuId"] . " AND
							boton_id=" . $perm["btnId"] . "
						";
					$result = $mysqli->query($query);
					while ($r = $result->fetch_assoc()) $permiso_usuarios[] = $r["usuario_id"];
					$usuarios_faltantes = array_diff($grupo_usuarios, $permiso_usuarios);
					if (!empty($usuarios_faltantes)) {
						$query = "
						INSERT INTO tbl_permisos (
							grupo_id,
							menu_id,
							boton_id,
							boton_nombre,
							estado,
							usuario_id
						)
						VALUES ";
						foreach ($usuarios_faltantes as $usuario_id) {
							$query .= "(
								" . $_POST["id"] . ",
								" . addslashes($perm["menuId"]) . ",
								" . addslashes($perm["btnId"]) . ",
								(SELECT nombre FROM tbl_menu_sistemas_botones WHERE boton=" . addslashes($perm["btnId"]) . " AND menu_id=" . addslashes($perm["menuId"]) . " LIMIT 1),
								" . $perm["active"] . ",
								" . $usuario_id . "
							),";
						}
						$query = substr($query, 0, -1);
						$mysqli->query($query);
					}
				}

				// Se comprueba que exista el permiso en el grupo
				$query_permiso = "
					SELECT id
					FROM
						tbl_permisos
					WHERE
						usuario_id = 0
						AND grupo_id = " . $_POST["id"] . "
						AND menu_id = " . $perm["menuId"] . "
						AND boton_id = " . $perm["btnId"];
				$result_qp = $mysqli->query($query_permiso);
				$r_qp = $result_qp->num_rows;
				if ($r_qp > 0) {
					$query = "
						UPDATE tbl_permisos 
						SET 
							estado=" . $perm['active'] . "
						WHERE 
							manual=0 AND
							grupo_id=" . $_POST["id"] . " AND
							menu_id=" . $perm["menuId"] . " AND
							boton_id=" . $perm["btnId"];
				} else {
					$query = "
						INSERT INTO tbl_permisos (
							grupo_id,
							menu_id,
							boton_id,
							boton_nombre,
							estado,
							usuario_id
						)
						VALUES (
							" . $_POST["id"] . ",
							" . $perm["menuId"] . ",
							" . $perm["btnId"] . ",
							'" . $perm["nombre_permiso"] . "',
							" . $perm["active"] . ",
							'0'
						)";
				}
				$mysqli->query($query);
				$mysqli->query("COMMIT");
			} catch (\Throwable $th) {
				//$mysqli->query("ROLLBACK");
			}
		}
	}
}
//Cargamos el modal
else {
	$query = "SELECT 
		id,
		nombre,
		descripcion,
		estado 
		FROM tbl_usuarios_grupos 
		WHERE id != 7";
	$result = $mysqli->query($query);

	//Se obtiene los permisos de cada grupo
	if (isset($_POST["id_grupo"])) {
		$id_grupo = $_POST["id_grupo"];
		$result_final_menu_botones = array();
		$query = "SELECT menu_id,boton_id FROM tbl_permisos WHERE grupo_id='" . $id_grupo . "' AND usuario_id=0 AND estado = '1'";
		$result = $mysqli->query($query);
		while ($row_menu_botones = $result->fetch_assoc()) {
			$result_final_menu_botones[] = $row_menu_botones;
		}
		echo json_encode($result_final_menu_botones, JSON_FORCE_OBJECT);
	} else {
?>

		<!-- <div class="modal fade" id="modal_grupo_user" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">  -->
		<div class="modal-dialog modal-xl" role="document" id="modal_interno">
			<div class="modal-content" style="height: 700px;overflow-y: scroll;">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
					<h5 class="modal-title" id="mdCrearGrupoTitle">Roles</h5>
				</div>
				<div class="modal-body">
					<div class="col-xs-12">
						<div id="boxMessage" class="alert alert-success text-center" style="display: none"></div>
					</div>
					<div class="row">
						<div class="col-sm-6">
							<table id="listado_grupo" class="table table-condensed table-bordered">
								<thead>
									<tr class="bg-secondary">
										<th>ID</th>
										<th>Nombre / Descripción</th>
										<th class="text-center">Estado</th>
										<th class="text-center">Opciones</th>
									</tr>
								</thead>
								<tbody>
									<?php
									foreach ($result as $row) {
									?>
										<tr id="tr_grupoid_<?php echo $row['id']; ?>">
											<td id="grupoId"><?php echo $row["id"]; ?></td>
											<td>
												<span id="txtGrupoNombreHidden_<?php echo $row['id']; ?>"><?php echo $row["nombre"]; ?></span>
												<br>
												<span id="txtGrupoDescHidden_<?php echo $row['id']; ?>"><small><i><?php echo $row['descripcion']; ?></i></small></span>
											</td>
											<td class="text-center">
												<input type="hidden" id="txtEstadoGrupo_<?php echo $row['id']; ?>" name="txtEstadoGrupo" value="<?php echo $row['estado']; ?>">
												<?php
												if ($row["estado"]) {
												?>
													<button id="btnToogleGrupoEstado_<?php echo $row['id']; ?>" class="btn btn-xs btn-success" onclick="sec_grupo_cambiar_estado_grupo(<?php echo $row['id']; ?>)">
														<i id="icoToogleGrupeEstado_<?php echo $row['id']; ?>" class="glyphicon glyphicon-ok" title="Desactivar Rol"></i>
													</button>
												<?php
												} else {
												?>
													<button id="btnToogleGrupoEstado_<?php echo $row['id']; ?>" class="btn btn-xs btn-danger" onclick="sec_grupo_cambiar_estado_grupo(<?php echo $row['id']; ?>)">
														<i id="icoToogleGrupeEstado_<?php echo $row['id']; ?>" class="glyphicon glyphicon-remove" title="Activar Rol"></i>
													</button>
												<?php
												}
												?>
											</td>
											<td class="text-center">
												<?php
												$row_id = $row['id'];
												$row_nombre = $row['nombre'];
												$row_descripcion = $row['descripcion'];
												?>
												<button name="btnEditarGrupo" class="btn btn-xs" onclick="sec_grupo_modal_check_grupo(<?php echo $row_id; ?>)"><i class="glyphicon glyphicon-edit" title="Editar Rol"></i></button>
												<button name="btnRemoverGrupo" class="btn btn-xs" onclick="sec_grupo_eliminar_grupo(<?php echo $row_id; ?>)"><i class="glyphicon glyphicon-remove" title="Remover Rol"></i></button>
											</td>
										</tr>
									<?php
									}
									?>
								</tbody>
							</table>
						</div>
						<div class="col-sm-6" style="border-left: 1px solid lightgrey;">
							<h5 id="txtGrupoTitle">Crear nuevo Rol</h5>
							<form id="formGuardarGrupo" method="POST" name="formGuardarGrupo">
								<div class="form-group">
									<label for="txtGroupName">Nombre del Rol *</label>
									<input type="text" id="txtGroupName" name="txtGroupName" class="form-control">
								</div>
								<div class="form-group">
									<label for="txtGroupDesc">Descripción <small>(no requerido)</small></label>
									<input type="text" id="txtGroupDesc" name="txtGroupDesc" class="form-control">
								</div>
								<br>
								<div class="form-group row">
									<div class="col-sm-4">
										<button type="button" class="btn btn-success btn-block" id="btnCrearGrupo">Crear rol</button>
									</div>

									<div class="col-sm-4">
										<button type="button" class="btn btn-warning btn-block" id="btnActualizarGrupo">Actualizar Cambios</button>
									</div>

									<div class="col-sm-4">
										<button type="button" class="btn btn-secondary btn-block" id="btnCancelarGrupo">Cancelar</button>
									</div>
								</div>
								<br>
								<hr>
								<input type="hidden" id="txtGroupId" name="txtGroupId">
								<button class="btn btn-xs pull-right" id="btnCheckAll">
									Marcar Todos: <i id="iconCheck" class="glyphicon glyphicon-unchecked"></i>
								</button>
								<table class="table tbl_menu_sub_menu_botones_crear_grupo" width="100%">
									<?php $tipo_menu = 'grupo';
									include '../sys/get_usuarios_menu.php'; ?>
								</table>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- </div> -->

<?php
	}
}
?>