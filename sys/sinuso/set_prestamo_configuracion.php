<?php 

date_default_timezone_set("America/Lima");

$result=array();

include("db_connect.php");
include("sys_login.php");


if (isset($_POST["accion"]) && $_POST["accion"]==="prestamo_configuracion_permisos")
{
	$param_tipo_prestamo = $_POST['param_tipo_prestamo'];
	$param_usuario = $_POST['param_usuario'];
	
	$where_usuario = "";

	if($param_usuario != 0)
	{
		$where_usuario = "AND u.id = '".$param_usuario."' ";
	}

	$query = 
	"
		SELECT
			u.id,
		    concat(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, ''),' ', IFNULL(p.apellido_materno, '')) AS usuario,
		    a.nombre AS area,
		    c.nombre AS cargo,
		    u.estado
		FROM tbl_usuarios u
			INNER JOIN tbl_personal_apt p
			ON u.personal_id = p.id
		    LEFT JOIN tbl_areas a
		    ON p.area_id = a.id
		    LEFT JOIN tbl_cargos c
		    ON p.cargo_id = c.id
		WHERE u.estado = 1 
			".$where_usuario."
		ORDER BY concat(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, ''),' ', IFNULL(p.apellido_materno, ''))
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->usuario,
			"2" => $reg->area,
			"3" => $reg->cargo,
			"4" => ($reg->estado == 1) ? '<span class="badge badge-primary">Activo</span>' : '<span class="badge badge-danger">Inactivo</span>',
			"5" => '<a class="btn btn-info btn-sm" 
						onclick="prestamo_configuracion_permisos_ver_permiso_prestamo(\''.$reg->id.'\', \''.$param_tipo_prestamo.'\');"
                        data-toggle="tooltip" data-placement="top" title="Ver Permisos">
                        <span class="fa fa-eye"></span>
                    </a>'
		);
	}

	$result = array(
		"sEcho" => 1,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="prestamo_configuracion_permisos_ver_permiso_boton")
{
	$param_tipo_prestamo = $_POST['param_tipo_prestamo'];
	$usuario_id = $_POST['usuario_id'];
	
	$where_usuario = "";
	$tipo_menu_id = 0;

	if($usuario_id != 0)
	{
		$where_usuario = "AND u.id = '".$usuario_id."' ";
	}

	// LOS TIPOS MENU EN PRODUCCION Y DEV ES EL MISMO ID
	if($param_tipo_prestamo == 1)
	{
		// PRESTAMO ENTRE TIENDA
		$tipo_menu_id = 291;
	}
	else
	{
		// PRESTAMO BOVEDA
		$tipo_menu_id = 298;
	}

	$query = 
	"
		SELECT
			id, boton_nombre
		FROM tbl_permisos 
		WHERE usuario_id = '".$usuario_id."' AND menu_id = '".$tipo_menu_id."'
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();
	$num = 1;

	while($reg = $list_query->fetch_object()) 
	{
		$data[] = array(
			"0" => $num,
			"1" => $reg->id,
			"2" => $reg->boton_nombre
		);

		$num++;
	}

	$result = array(
		"sEcho" => 1,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="prestamo_configuracion_permisos_ver_permiso_local")
{
	$usuario_id = $_POST['usuario_id'];
	
	$query = 
	"
		SELECT
            ul.id, ul.usuario_id, ul.local_id,
            l.cc_id, l.nombre, z.nombre AS zona
        FROM tbl_usuarios_locales ul
            INNER JOIN tbl_locales l
            ON ul.local_id = l.id
            LEFT JOIN tbl_zonas z
            ON l.zona_id = z.id
        WHERE ul.usuario_id = ".$usuario_id." AND ul.estado = 1 AND l.nombre IS NOT NULL
        ORDER BY l.nombre ASC
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();
	$num = 1;

	while($reg = $list_query->fetch_object()) 
	{
		$data[] = array(
			"0" => $num,
			"1" => $reg->local_id,
			"2" => $reg->nombre,
			"3" => $reg->cc_id,
			"4" => $reg->zona
		);

		$num++;
	}

	$result = array(
		"sEcho" => 1,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="prestamo_configuracion_correo_tipo_correo_listar_tipo_proceso") 
{
	
	$usuario_id = $login?$login['id']:null;
	
	if((int)$usuario_id > 0)
	{
		$query = "";

		$query = 
	    "
	        SELECT
				id, nombre
			FROM tbl_prestamo_mantenimiento_correo_tipo
			ORDER BY id
	    ";

	    $list_query = $mysqli->query($query);
	    
	    if($mysqli->error)
		{
			$result["http_code"] = 400;
	        $result["titulo"] ="Error.";
	        $result["texto"] = $mysqli->error;
	        $result["codigo"] = 2;
			exit();
		}

		$list = array();
		
		while ($li = $list_query->fetch_assoc()) 
		{
			$list[] = $li;
		}

		if(count($list) == 0)
		{
			$result["http_code"] = 400;
			$result["titulo"] ="";
			$result["codigo"] = 1;
			$result["texto"] = "No se encontro resultados";
		} 
		elseif (count($list) > 0) 
		{
			$result["http_code"] = 200;
			$result["titulo"] = "Datos obtenidos de gestion.";
			$result["texto"] = $list;
		} 
		else 
		{
			$result["http_code"] = 400;
			$result["titulo"] ="";
			$result["codigo"] = 1;
			$result["texto"] = "No existen registros";
		}

		echo json_encode($result);
		exit();
	}
	else
	{
		$result["http_code"] = 400;
        $result["titulo"] ="Sesión perdida.";
        $result["texto"] ="Por favor vuelva a iniciar sesión.";
        $result["codigo"] = 2;
	}

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="prestamo_configuracion_correo_modal_guardar_o_editar_tipo_correo") 
{
	$login_usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	
	$error = '';
	$query = "";
	$respuesta_status = "";

	if((int) $login_usuario_id > 0)
	{
		$param_id = $_POST["param_id"];
		$param_nombre = $_POST["param_nombre"];
		$param_descripcion = $_POST["param_descripcion"];
		$tipo_accion = $_POST["tipo_accion"];

		if($tipo_accion == 1)
		{
			// INSERT
			//INICIO: VALIDAR SI EL REGISTRO A INSERTAR YA EXISTE
			$query_validar = 
			"
				SELECT
					id, nombre
				FROM tbl_prestamo_mantenimiento_correo_tipo
				WHERE nombre = '".$param_nombre."'
				LIMIT 1
			";

			$query = $mysqli->query($query_validar);

		    $cant_movilidad = $query->num_rows;

		    if($cant_movilidad > 0)
		    {
		        $reg = $query->fetch_assoc();
		        $select_id = $reg["id"];
		        $select_nombre = $reg["nombre"];

		        $result["http_code"] = 400;
				$result["status"] = "Error de duplicidad";
				$result["error"] = "El registro ".$select_nombre." ya existe.";

		        echo json_encode($result);
				exit();
		    }
			//FIN: VALIDAR SI EL REGISTRO A INSERTAR YA EXISTE



			$query = 
			"
				INSERT INTO tbl_prestamo_mantenimiento_correo_tipo
				(
					nombre,
					descripcion,
					status,
					user_created_id,
					created_at,
					user_updated_id,
					updated_at
				)
				VALUES
				(
					'".$param_nombre."',
					'".$param_descripcion."',
					1,
					'".$login_usuario_id."',
					'".$created_at."',
					'".$login_usuario_id."',
					'".$created_at."'
				)
			";

			$respuesta_status = "Registro exitoso";
		}
		else
		{
			// UPDATE
			
			$query = 
			"
				UPDATE tbl_prestamo_mantenimiento_correo_tipo 
					SET nombre = '".$param_nombre."',
						descripcion = '".$param_descripcion."',
						user_updated_id = '".$login_usuario_id."',
						updated_at = '".$created_at."'
				WHERE id = '".$param_id."'
			";

			$respuesta_status = "Edición exitoso";
		}

		$mysqli->query($query);

		if($mysqli->error)
		{
			$error = $mysqli->error;
		}

		if ($error == '') 
		{
			$result["http_code"] = 200;
			$result["status"] = $respuesta_status;
			$result["error"] = "";
		}
		else 
		{
			$result["http_code"] = 400;
			$result["status"] = "Error.";
			$result["error"] = $error;
		}
	}
	else
	{
		$result["http_code"] = 400;
        $result["error"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
	}

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="prestamo_configuracion_correo_modal_buscar_tipo_correo")
{
	$param_tipo_correo = $_POST['param_tipo_correo'];
	
	$where_tipo_correo = "";

	if($param_tipo_correo != 0)
	{
		$where_tipo_correo = "WHERE ct.id = '".$param_tipo_correo."' ";
	}

	$query = 
	"
		SELECT 
			ct.id, ct.nombre, ct.descripcion, ct.status AS estado,
			ct.user_created_id,
			concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS usuario_creador,
			ct.created_at AS fecha_creacion
		FROM tbl_prestamo_mantenimiento_correo_tipo ct
			INNER JOIN tbl_usuarios tu
			ON ct.user_created_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
		".$where_tipo_correo."
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->nombre,
			"2" => $reg->descripcion,
			"3" => ($reg->estado == 1) ? '<span class="badge badge-primary">Activo</span>' : '<span class="badge badge-danger">Desactivado</span>',
			"4" => $reg->usuario_creador,
			"5" => $reg->fecha_creacion,
			"6" => ($reg->estado == 1) ? 
					'<a class="btn btn-warning btn-sm" style="margin-right: 5px;" 
						onclick="prestamo_configuracion_correo_modal_tipo_correo_cargar_datos_a_editar(\''.$reg->id.'\', \''.$reg->nombre.'\', \''.$reg->descripcion.'\');"
                        data-toggle="tooltip" data-placement="top" title="Editar">
                        <span class="fa fa-pencil"></span>
                    </a>
                    <a class="btn btn-danger btn-sm" style="margin-right: 5px;" 
						onclick="prestamo_configuracion_correo_modal_tipo_correo_activar_desactivar(\''.$reg->id.'\', 0);"
                        data-toggle="tooltip" data-placement="top" title="Desactivar">
                        <span class="fa fa-close"></span>
                    </a>' 
                    :
                    '<a class="btn btn-warning btn-sm" style="margin-right: 5px;"
						onclick="prestamo_configuracion_correo_modal_tipo_correo_cargar_datos_a_editar(\''.$reg->id.'\', \''.$reg->nombre.'\', \''.$reg->descripcion.'\');"
                        data-toggle="tooltip" data-placement="top" title="Editar">
                        <span class="fa fa-pencil"></span>
                    </a>
                    <a class="btn btn-primary btn-sm" style="margin-right: 5px;"
						onclick="prestamo_configuracion_correo_modal_tipo_correo_activar_desactivar(\''.$reg->id.'\', 1);"
                        data-toggle="tooltip" data-placement="top" title="Activar">
                        <span class="fa fa-check"></span>
                    </a>' 

		);
	}

	$result = array(
		"sEcho" => 1,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="prestamo_configuracion_correo_modal_tipo_correo_activar_desactivar") 
{
	$login_usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	
	$error = '';
	$query = "";

	if((int) $login_usuario_id > 0)
	{
		$param_id = $_POST["param_id"];
		$param_tipo = $_POST["param_tipo"];

		$query = 
		"
			UPDATE tbl_prestamo_mantenimiento_correo_tipo 
				SET status = '".$param_tipo."',
					user_updated_id = '".$login_usuario_id."',
					updated_at = '".$created_at."'
			WHERE id = '".$param_id."'
		";

		$mysqli->query($query);

		if($mysqli->error)
		{
			$error = $mysqli->error;
		}

		if ($error == '') 
		{
			$result["http_code"] = 200;
			$result["status"] = "Edición exitoso";
			$result["error"] = "";
		}
		else 
		{
			$result["http_code"] = 400;
			$result["status"] = "Error.";
			$result["error"] = $error;
		}
	}
	else
	{
		$result["http_code"] = 400;
        $result["error"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
	}

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="prestamo_configuracion_correo_buscar_correo_grupo")
{
	$param_tipo_correo = $_POST['param_tipo_correo'];
	
	$where_tipo_correo = "";

	if($param_tipo_correo != 0)
	{
		$where_tipo_correo = "WHERE cg.tbl_prestamo_mantenimiento_correo_tipo_id = '".$param_tipo_correo."' ";
	}
	
	$query = 
	"
		SELECT 
			cg.id, 
		    ct.nombre AS tipo_grupo,
		    cg.nombre, cg.metodo, cg.status AS estado,
		    cg.user_created_id,
		    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS usuario_creador,
		    cg.created_at AS fecha_creacion
		FROM tbl_prestamo_mantenimiento_correo_grupo cg
			INNER JOIN tbl_prestamo_mantenimiento_correo_tipo ct
		    ON cg.tbl_prestamo_mantenimiento_correo_tipo_id = ct.id
			INNER JOIN tbl_usuarios tu
			ON cg.user_created_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
		".$where_tipo_correo."
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->tipo_grupo,
			"2" => $reg->nombre,
			"3" => $reg->metodo,
			"4" => ($reg->estado == 1) ? '<span class="badge badge-primary">Activo</span>' : '<span class="badge badge-danger">Desactivado</span>',
			"5" => $reg->usuario_creador,
			"6" => $reg->fecha_creacion,
			"7" => '<a class="btn btn-info btn-sm" style="margin-right: 5px;" 
						onclick="prestamo_configuracion_correo_grupo_correo_detalle(\''.$reg->id.'\', \''.$reg->metodo.'\');"
                        data-toggle="tooltip" data-placement="top" title="Ver Detalle">
                        <span class="fa fa-eye"></span>
                    </a>
                    <a class="btn btn-warning btn-sm" style="margin-right: 5px;" 
						onclick="prestamo_configuracion_correo_grupo_correo_cargar_datos_a_editar(\''.$reg->id.'\');"
                        data-toggle="tooltip" data-placement="top" title="Editar">
                        <span class="fa fa-pencil"></span>
                    </a>'
		);
	}

	$result = array(
		"sEcho" => 1,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="prestamo_configuracion_correo_grupo_correo_cargar_datos_a_editar") 
{
	$login_usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	
	$error = '';
	$query = "";

	if((int) $login_usuario_id > 0)
	{
		$param_grupo_id = $_POST["param_grupo_id"];

		$query = 
		"
			SELECT 
				id AS prestamo_configuracion_correo_modal_grupo_correo_param_id,
				tbl_prestamo_mantenimiento_correo_tipo_id AS prestamo_configuracion_correo_modal_grupo_correo_param_tipo,
				nombre AS prestamo_configuracion_correo_modal_grupo_correo_param_nombre, 
				metodo AS prestamo_configuracion_correo_modal_grupo_correo_param_metodo
			FROM tbl_prestamo_mantenimiento_correo_grupo 						
			WHERE id = '".$param_grupo_id."'
		";

		$row =  $mysqli->query($query)->fetch_assoc();

		$result["data_correo_grupo"] = $row;
	}
	else
	{
		$result["http_code"] = 400;
        $result["error"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
	}

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="prestamo_configuracion_correo_modal_guardar_o_editar_grupo_correo") 
{
	$login_usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	
	$error = '';
	$query = "";
	$respuesta_status = "";

	if((int) $login_usuario_id > 0)
	{
		$param_id = $_POST["param_id"];
		$param_tipo = $_POST["param_tipo"];
		$param_nombre = $_POST["param_nombre"];
		$param_metodo = $_POST["param_metodo"];
		$tipo_accion = $_POST["tipo_accion"];

		if($tipo_accion == 1)
		{
			// INSERT

			$query = 
			"
				INSERT INTO tbl_prestamo_mantenimiento_correo_grupo
				(
					tbl_prestamo_mantenimiento_correo_tipo_id,
					nombre,
					metodo,
					status,
					user_created_id,
					created_at,
					user_updated_id,
					updated_at
				)
				VALUES
				(
					'".$param_tipo."',
					'".$param_nombre."',
					'".$param_metodo."',
					1,
					'".$login_usuario_id."',
					'".$created_at."',
					'".$login_usuario_id."',
					'".$created_at."'
				)
			";

			$respuesta_status = "Registro exitoso";
		}
		else
		{
			// UPDATE
			
			$query = 
			"
				UPDATE tbl_prestamo_mantenimiento_correo_grupo 
					SET tbl_prestamo_mantenimiento_correo_tipo_id = '".$param_tipo."',
						nombre = '".$param_nombre."',
						metodo = '".$param_metodo."',
						user_updated_id = '".$login_usuario_id."',
						updated_at = '".$created_at."'
				WHERE id = '".$param_id."'
			";

			$respuesta_status = "Edición exitoso";
		}

		$mysqli->query($query);

		if($mysqli->error)
		{
			$error = $mysqli->error;
		}

		if ($error == '') 
		{
			$result["http_code"] = 200;
			$result["status"] = $respuesta_status;
			$result["error"] = "";
		}
		else 
		{
			$result["http_code"] = 400;
			$result["status"] = "Error.";
			$result["error"] = $error;
		}
	}
	else
	{
		$result["http_code"] = 400;
        $result["error"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
	}

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="prestamo_configuracion_correo_modal_grupo_detalle_listar_usuario")
{
	$param_grupo_id = $_POST['param_grupo_id'];
	
	$query = 
	"
		SELECT
			cu.id, 
			cu.tbl_prestamo_mantenimiento_correo_grupo_id,
			cu.usuario_id,
			concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS usuario,
			tp.correo AS usuario_correo,
		    cu.user_created_id,
		    concat(IFNULL(tpc.nombre, ''),' ', IFNULL(tpc.apellido_paterno, ''),' ', IFNULL(tpc.apellido_materno, '')) AS usuario_creador,
		    tpc.correo, cu.created_at AS fecha_creacion
		FROM tbl_prestamo_mantenimiento_correo_usuario cu
			INNER JOIN tbl_usuarios tu
			ON cu.usuario_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
		    INNER JOIN tbl_usuarios tuc
			ON cu.user_created_id = tuc.id
			INNER JOIN tbl_personal_apt tpc
			ON tuc.personal_id = tpc.id
		WHERE cu.tbl_prestamo_mantenimiento_correo_grupo_id = '".$param_grupo_id."'
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->usuario,
			"2" => $reg->usuario_correo,
			"3" => $reg->usuario_creador,
			"4" => $reg->fecha_creacion,
			"5" => '<a class="btn btn-danger btn-sm" 
						onclick="prestamo_configuracion_correo_modal_grupo_detalle_anular_usuario(\''.$reg->id.'\', \''.$reg->tbl_prestamo_mantenimiento_correo_grupo_id.'\');"
                        data-toggle="tooltip" data-placement="top" title="Anular">
                        <span class="fa fa-trash"></span>
                    </a>' 

		);
	}

	$result = array(
		"sEcho" => 1,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="prestamo_configuracion_correo_modal_grupo_detalle_agregar_usuario") 
{
	$login_usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	
	$error = '';
	$query = "";
	$respuesta_status = "";

	if((int) $login_usuario_id > 0)
	{
		$param_grupo_id = $_POST["param_grupo_id"];
		$param_usuario = $_POST["param_usuario"];

		// INICIO: VERIFICAR SI YA SE ENCUENTRA REGISTRADO EN EL GRUPO

		$query_verificar = 
		"
			SELECT
				id, tbl_prestamo_mantenimiento_correo_grupo_id, usuario_id
			FROM tbl_prestamo_mantenimiento_correo_usuario
			WHERE tbl_prestamo_mantenimiento_correo_grupo_id = '".$param_grupo_id."' 
				AND usuario_id = '".$param_usuario."' AND status = 1
		";

		$query_verificar_usuario = $mysqli->query($query_verificar);

		$row_count = mysqli_num_rows($query_verificar_usuario);

		if($row_count > 0)
		{
		    $result["http_code"] = 201;
			$result["status"] = "Ya se encuentra registrado en este grupo.";
			$result["error"] = "";

			echo json_encode($result);
			exit();
		}

		// FIN: VERIFICAR SI YA SE ENCUENTRA REGISTRADO EN EL GRUPO

		// INSERT
		$query = 
		"
			INSERT INTO tbl_prestamo_mantenimiento_correo_usuario
			(
				tbl_prestamo_mantenimiento_correo_grupo_id,
				usuario_id,
				status,
				user_created_id,
				created_at,
				user_updated_id,
				updated_at
			)
			VALUES
			(
				'".$param_grupo_id."',
				'".$param_usuario."',
				1,
				'".$login_usuario_id."',
				'".$created_at."',
				'".$login_usuario_id."',
				'".$created_at."'
			)
		";

		$mysqli->query($query);

		if($mysqli->error)
		{
			$error = $mysqli->error;
		}

		if ($error == '') 
		{
			$result["http_code"] = 200;
			$result["status"] = "Se agregó correctamente";
			$result["error"] = "";
		}
		else 
		{
			$result["http_code"] = 400;
			$result["status"] = "Error.";
			$result["error"] = $error;
		}
	}
	else
	{
		$result["http_code"] = 400;
        $result["error"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
	}

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="prestamo_configuracion_correo_modal_grupo_detalle_anular_usuario") 
{
	$login_usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	
	$error = '';
	$query = "";

	if((int) $login_usuario_id > 0)
	{
		$param_grupo_detalle_id = $_POST["param_grupo_detalle_id"];

		$query = 
		"
			DELETE FROM tbl_prestamo_mantenimiento_correo_usuario
			WHERE id = '".$param_grupo_detalle_id."'
		";

		$mysqli->query($query);

		if($mysqli->error)
		{
			$error = $mysqli->error;
		}

		if ($error == '') 
		{
			$result["http_code"] = 200;
			$result["status"] = "Anulación exitoso";
			$result["error"] = "";
		}
		else 
		{
			$result["http_code"] = 400;
			$result["status"] = "Error.";
			$result["error"] = $error;
		}
	}
	else
	{
		$result["http_code"] = 400;
        $result["error"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
	}

	echo json_encode($result);
	exit();
}
?>