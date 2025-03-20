<?php
ob_start();
include("db_connect.php");
include("sys_login.php");

if (isset($_POST["sec_usuarios_change_pass_modal"])) {
?>
	<div class="modal" id="sec_usuarios_change_pass_modal">
		<div class="modal-dialog modal-sm">
			<div class="modal-content">
				<form class="form" id="sec_usuarios_change_pass_form">
					<div class="modal-header">
						<?php if ($login["password_changed"]) { ?><button type="button" class="close close_btn"><span aria-hidden="true">&times;</span></button><?php } ?>
						<h4 class="modal-title">Cambiar Contraseña</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label>Contraseña actual:</label>
							<input class="form-control save_data" type="password" name="current_password" required="required">
						</div>
						<div class="form-group">
							<label>Nueva contraseña:</label>
							<input class="form-control save_data" type="password" name="new_password" required="required">
						</div>
						<div class="form-group">
							<label>Confirmar Nueva contraseña:</label>
							<input class="form-control save_data" type="password" name="new_repassword" required="required">
						</div>
						<div class="form-group">
							<label>Seguridad:</label>
							<div class="progress m-0">
								<div class="progress-bar" id="progress-bar-security" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">
								</div>
								<div style="position: absolute; left: 0px; width: 100%; text-align: center; z-index: 1; padding: 0px; margin: 0px">
									<span id="progress-bar-text">Muy debil (0/100)</span>
								</div>
							</div>
						</div>
						<div class="w-100" id="container-alert-password"></div>
					</div>
					<div class="modal-footer">
						<div class="form-group ">
							<button type="submit" class="btn btn-success save_btn" title="Abrir"><span class='glyphicon glyphicon-floppy-save'></span> Guardar</button>
							<?php if ($login["password_changed"]) { ?><button class="btn btn-default close_btn">Cancelar</button><?php } ?>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
<?php
}



if (isset($_POST["sec_usuarios_get_tabla_usuarios"])) {
	$data = $_POST["sec_usuarios_get_tabla_usuarios"];
	$data['offset'] = $data['limit'] * $data['page'];

	$list_where = " WHERE u.estado = '1'";
	if (isset($data['inactivo'])) {
		$list_where = " WHERE u.estado != '1'";
	}

	if (isset($data['p_inactivo'])) {
		$list_where .= " AND p.estado != '1'";
	}

	if ($data['filter'] != "") {
		$list_where .= " AND (
			u.id LIKE '%{$data['filter']}%' OR
			u.usuario LIKE '%{$data['filter']}%' OR 
			p.nombre LIKE '%{$data['filter']}%' OR
			p.apellido_paterno LIKE '%{$data['filter']}%' OR
			p.dni LIKE '%{$data['filter']}%' OR
			s.nombre LIKE '%{$data['filter']}%' OR
			a.nombre LIKE '%{$data['filter']}%' OR
			c.nombre LIKE '%{$data['filter']}%' OR
			g.nombre LIKE '%{$data['filter']}%'
		) ";
	}

	$mysqli->query("START TRANSACTION");
	$list_query = $mysqli->query("SELECT 		
		u.id,
		u.usuario,		
		p.nombre AS personal_nombre,
		p.apellido_paterno AS apellidos,
		p.dni AS dni,
		s.nombre AS sistema,
		a.nombre AS area,
		c.nombre AS cargo,
		g.nombre AS grupo,
		u.estado,
		u.validacion_2fa
		FROM tbl_usuarios  u
		LEFT JOIN tbl_personal_apt p ON (p.id = u.personal_id)
		LEFT JOIN tbl_sistemas s ON (s.id = u.sistema_id)
		LEFT JOIN tbl_areas a ON (a.id = p.area_id)
		LEFT JOIN tbl_cargos c ON (c.id = p.cargo_id)
		LEFT JOIN tbl_usuarios_grupos g ON (g.id = u.grupo_id)
		$list_where
		ORDER BY u.id ASC
		LIMIT {$data['limit']} OFFSET {$data['offset']}");

	$num_rows = $mysqli->query("SELECT u.id FROM tbl_usuarios  u
		LEFT JOIN tbl_personal_apt p ON (p.id = u.personal_id)
		LEFT JOIN tbl_sistemas s ON (s.id = u.sistema_id)
		LEFT JOIN tbl_areas a ON (a.id = p.area_id)
		LEFT JOIN tbl_cargos c ON (c.id = p.cargo_id)
		LEFT JOIN tbl_usuarios_grupos g ON (g.id = u.grupo_id)
		$list_where")->num_rows;
	$mysqli->query("COMMIT");
	$list = array();
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
		// print_r($li);
	}
	mysqli_free_result($list_query);
	if ($mysqli->error) {
		print_r($mysqli->error);
		die;
	}
	// DATOS DE LA TABLA
	$list_cols = array();
	$list_cols["id"] = "ID";
	$list_cols["usuario"] = "USUARIO";
	$list_cols["personal_nombre"] = "NOMBRE";
	$list_cols["apellidos"] = "APELLIDOS";
	$list_cols["dni"] = "DNI";
	$list_cols["sistema"] = "SISTEMA";
	$list_cols["area"] = "AREA";
	$list_cols["cargo"] = "CARGO";
	$list_cols["grupo"] = "ROL";
	//  editado por alex (usuarios tabla)
	// $list_cols["grupo"] = "GRUPO";
	$list_cols["estado"] = "ESTADO";
	// $list_cols["validacion_2fa"] = "VALIDACION 2FA";
	$list_cols["opciones"] = "OPCIONES";

	$body = "";

	$body .= '<thead>';
	$body .= '<tr>';
	foreach ($list_cols as $key => $value) {

		// if ($key == "id") {
		// 	$body .= '<th class="w-30px">ID</th>';
		// } elseif ($key == "estado") {
		// 	$body .= '<th class="w-85px text-center">ESTADO</th>';
		// } elseif ($key == "validacion_2fa") {
		// 	$body .= '<th class="w-85px text-center">VALIDACION 2FA</th>';
		// } elseif ($key == "opciones") {
		// 	$body .= '<th class="w-130px text-center">OPCIONES</th>';
		// } else {
		// 	$body .= '<th>' . $value . '</th>';
		// }

		//  editado por alex (usuarios tabla campos)
		$th_content = '<th>' . $value . '</th>';
		if ($key == "id") {
			$th_content = '<th class="w-30px">ID</th>';
		}
		if ($key == "dni") {
			$th_content = '<th class="w-80px text-center">DNI</th>';
		}

		if ($key == "sistema") {
			$th_content = '<th class="w-130px text-center">SISTEMA</th>';
		}

		if ($key == "estado") {
			$th_content = '<th class="w-85px text-center">ESTADO</th>';
		}

		if ($key == "validacion_2fa") {
			$th_content = '<th class="w-0px text-center">VALIDACION 2FA</th>';
		}

		if ($key == "opciones") {
			$th_content = '<th class="w-160px text-center">OPCIONES</th>';
		}

		// Agregar el resultado final al cuerpo
		$body .= $th_content;
	}
	$body .= '</tr>';
	$body .= '</thead>';
	$body .= '<tbody>';

	// usuarios - asignar permiso tabla modal
	foreach ($list as $l_k => $l_v) {
		$body .= '<tr>';
		foreach ($list_cols as $key => $value) {
			if ($key == "opciones") {
				$body .= '<td class="text-center">';
				if (array_key_exists(26, $usuario_permisos) && in_array("permissions", $usuario_permisos[26])) {
					$body .= '<button type="button" data-button="permissions"
					title="Asignar Permisos 2"
					data-id="' . $l_v['id'] . '"
					data-user="' . $l_v['usuario'] . '"
					data-nombre="' . $l_v['personal_nombre'] . '"
					data-apellido="' . $l_v['apellidos'] . '"
					data-sistema="' . $l_v['sistema'] . '"
					class="btn btn-sm btn-default btn_permisos_ususarios_usuario_selecionado_table" >';
					$body .= '<span class="glyphicon glyphicon-equalizer"></span>';
					$body .= '</button>';
				}
				if (array_key_exists(26, $usuario_permisos) && in_array("edit", $usuario_permisos[26])) {
					$body .= '<button type="button" data-button="edit"
					class="btn btn-rounded btn-default btn-sm btn-edit "
					title="Editar"
					onclick="sec_usuarios_tbl_editar_usuario(' . $l_v['id'] . ');">';
					$body .= '<span class="glyphicon glyphicon-edit"></span>';
					$body .= '</button>';
				}
				if (array_key_exists(26, $usuario_permisos) && in_array("ver_historial_contrasena", $usuario_permisos[26])) {
					$body .= '<button type="button" data-button="edit"
					class="btn btn-rounded btn-default btn-sm btn-edit "
					title="Historial de Cambios de Contraseñas"
					onclick="sec_usuarios_modal_historial_claves(' . $l_v['id'] . ');">';
					$body .= '<span class="icon fa fa-fw fa-file-text"></span>';
					$body .= '</button>';
				}
				if (array_key_exists(26, $usuario_permisos) && in_array("log_permisos_usuario", $usuario_permisos[26])) {
					$body .= '<button type="button" data-button="log-permisos"
					class="btn btn-rounded btn-sm btn-log-permisos btn-degradado btn-default"
					title="Historial de cambios de permisos"
					onclick="sec_log_permisos_usuario(' . $l_v['id'] . ');">';
					$body .= '<span class="glyphicon glyphicon-time"></span>';
					$body .= '</button>';
				}
				$body .= '</td>';
			} elseif ($key == "estado") {
				$body .= '<td class="text-center">';
				if (array_key_exists(26, $usuario_permisos) && in_array("state", $usuario_permisos[26])) {
					$body .= '<input class="switch switch-table" id="checkbox_' . $l_k . '" type="checkbox" ' . ($l_v["estado"] ? 'checked="checked"' : "") . 'data-table="tbl_usuarios" data-id="' . $l_v["id"] . '" data-view="listar" data-col="estado" data-on-value="1" data-off-value="0">';
				} else {
					if ($l_v["estado"]) {
						$body .= '<div class="btn btn-sm btn-default text-success btn-estado"><span class="glyphicon glyphicon-ok-circle"></span></div>';
					} else {
						$body .= '<div class="btn btn-sm btn-default text-danger btn-estado"><span class="glyphicon glyphicon-remove-circle"></span></div>';
					}
				}
				$body .= '</td>';
			} elseif ($key == "validacion_2fa") {
				$body .= '<td class="text-center">';
				if (array_key_exists(26, $usuario_permisos) && in_array("state", $usuario_permisos[26])) {
					$body .= '<input class="switch switch-2fa" id="checkbox_' . $l_k . '" type="checkbox" ' . ($l_v["validacion_2fa"] ? 'checked="checked"' : "") . 'data-table="tbl_usuarios" data-id="' . $l_v["id"] . '" data-view="listar" data-col="validacion_2fa" data-on-value="1" data-off-value="0">';
				} else {
					if ($l_v["validacion_2fa"]) {
						$body .= '<div class="btn btn-sm btn-default text-success btn-validacion_2fa"><span class="glyphicon glyphicon-ok-circle"></span></div>';
					} else {
						$body .= '<div class="btn btn-sm btn-default text-danger btn-validacion_2fa"><span class="glyphicon glyphicon-remove-circle"></span></div>';
					}
				}
				$body .= '</td>';
			} elseif ($key == "id") {
				$id = $l_v[$key];
				$vusuario = "hid_usuario_" . $id;
				$vusuario_nombre = $l_v['usuario'];
				// $l_v['usuario'];
				$hid_usuario  = "<input type='hidden' id=$vusuario name=$vusuario value=$vusuario_nombre />";
				$body .= '<td class="text-right">' . $l_v[$key] . $hid_usuario . '</td>';
			} else {
				$body .= '<td>' . $l_v[$key] . '</td>';
			}
		}
		$body .= '</tr>';
		$body .= '</tbody>';
	}
	echo json_encode(['body' => $body, 'num_rows' => $num_rows]);
}

if (isset($_POST["sec_usuarios_get_historial_claves"])) {
	try {
		$usuario_id = $_POST['usuario_id'];

		$select_query = "SELECT u.id, CONCAT(IFNULL(p.nombre,''),' ',IFNULL(p.apellido_paterno,''),' ',IFNULL(p.apellido_materno,''),' [',u.usuario,']') AS nombre  
		FROM tbl_usuarios AS u
		INNER JOIN tbl_personal_apt AS p ON p.id = u.personal_id AND p.estado = 1
		WHERE u.id = " . $usuario_id . " LIMIT 1";
		$personal_list = $mysqli->query($select_query);
		if ($mysqli->error) {
			$result = [];
			$result['status'] = 404;
			$result['result'] = [];
			$result['message'] = $select_query . ' - ' . $mysqli->error;
			echo json_encode($result);
			exit();
		}
		$personal =  $personal_list->fetch_assoc();


		$select_query = "SELECT p.id, p.ip, p.created_at
							FROM tbl_password_reset AS p 
							WHERE p.usuario_id = " . $usuario_id . "
							ORDER BY p.created_at DESC";
		$claves_list = $mysqli->query($select_query);
		if ($mysqli->error) {
			$result = [];
			$result['status'] = 404;
			$result['result'] = [];
			$result['message'] = $select_query . ' - ' . $mysqli->error;
			echo json_encode($result);
			exit();
		}
		$password_changes = [];
		while ($l = $claves_list->fetch_assoc()) {
			$password_changes[] = $l;
		}


		$select_query = "SELECT au.id, au.created_at, au.ip, 
		CONCAT(IFNULL(p.nombre,''),' ',IFNULL(p.apellido_paterno,''),' ',IFNULL(p.apellido_materno,''),' [',u.usuario,']') AS personal  
		FROM tbl_asignacion_usuario_log AS au 
		INNER JOIN tbl_usuarios AS u ON u.id = au.user_id
		INNER JOIN tbl_personal_apt AS p ON p.id = u.personal_id AND p.estado = 1
		WHERE au.select_user_id = " . $usuario_id . "
		AND au.action = 'Cambio Password'
		ORDER BY au.id DESC";
		$claves_list = $mysqli->query($select_query);
		if ($mysqli->error) {
			$result = [];
			$result['status'] = 404;
			$result['result'] = [];
			$result['message'] = $select_query . ' - ' . $mysqli->error;
			echo json_encode($result);
			exit();
		}
		$password_reset = [];
		while ($l = $claves_list->fetch_assoc()) {
			$password_reset[] = $l;
		}




		$result = [];
		$result['status'] = 200;
		$result['result'] = [
			'personal' => $personal,
			'password_changes' => $password_changes,
			'password_reset' => $password_reset,
		];
		$result['message'] = 'Datos obtenidos de gestión';
		echo json_encode($result);
		exit();
	} catch (\Exception $e) {
		$result = [];
		$result['status'] = 404;
		$result['result'] = [];
		$result['message'] = $e->getMessage();
		echo json_encode($result);
		exit();
	}
}



$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : "0";
switch ($action) {
	case "0":
		break;
	case "get_all_personal":
		$personal_command = "SELECT id, nombre, apellido_paterno, apellido_materno FROM tbl_personal_apt WHERE estado = 1";
		$result = $mysqli->query($personal_command);
		$personal_arr = array();
		while ($personal = $result->fetch_assoc()) {
			$personal_arr[] = array('id' => $personal["id"], 'nombre' => $personal["nombre"] . " " . $personal["apellido_paterno"] . " " . $personal["apellido_materno"]);
			//$personal_arr[$personal["id"]]=$personal["nombre"]." ".$personal["apellido_paterno"]." ".$personal["apellido_materno"];
		}
		echo json_encode($personal_arr);
		break;
	default:
		break;
}

?>