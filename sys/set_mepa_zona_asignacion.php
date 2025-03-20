<?php

date_default_timezone_set("America/Lima");

$result=array();

include("db_connect.php");
include("sys_login.php");


if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_zona_asignacion_listar_usuario_zona_asignacion")
{
	$sec_mepa_zona_asignacion_param_usuario = $_POST['sec_mepa_zona_asignacion_param_usuario'];
	$sec_mepa_zona_asignacion_param_zona = $_POST['sec_mepa_zona_asignacion_param_zona'];

	$login_usuario_id = $login?$login['id']:null;

	// INICIO: OBTENER RED

	$where_redes = "";
	$where_zonas = "";

	$select_red =
    "
        SELECT
            l.red_id
        FROM tbl_usuarios_locales ul
            INNER JOIN tbl_locales l
            ON ul.local_id = l.id
        WHERE ul.usuario_id = '".$login_usuario_id."'
        	AND ul.estado = 1 AND l.red_id IS NOT NULL
        GROUP BY l.red_id
    ";

    $data_select_red = $mysqli->query($select_red);
    
    $row_count_data_select_red = $data_select_red->num_rows;

    $ids_data_select_red = '';
    $contador_ids = 0;
    
    if ($row_count_data_select_red > 0) 
    {
        while ($row = $data_select_red->fetch_assoc()) 
        {
        	if ($contador_ids > 0) 
            {
                $ids_data_select_red .= ',';
            }

            $ids_data_select_red .= $row["red_id"];           
            $contador_ids++;
        }

        $where_redes = " AND rz.red_id IN ($ids_data_select_red) ";
    }

	// FIN: OBTENER RED

	$where_todos = "";
	$where_id_usuario = "";
	$where_id_zona = "";


	if($sec_mepa_zona_asignacion_param_usuario != 0 AND $sec_mepa_zona_asignacion_param_zona != 0)
	{
		$where_todos = " AND (z.id_usuario = '".$sec_mepa_zona_asignacion_param_usuario."' AND z.id_zona = '".$sec_mepa_zona_asignacion_param_zona."') ";
	}
	else
	{
		if($sec_mepa_zona_asignacion_param_usuario != 0)
		{
			$where_id_usuario = " AND z.id_usuario = '".$sec_mepa_zona_asignacion_param_usuario."' ";
		}

		if($sec_mepa_zona_asignacion_param_zona != 0)
		{
			$where_id_zona = " AND z.id_zona = '".$sec_mepa_zona_asignacion_param_zona."' ";
		}
	}

	$query = "
		SELECT
			z.id,
			z.id_usuario,
		    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS usuario,
		    z.id_zona,
		    za.nombre AS zona,
		    z.status,
		    z.created_at AS fecha_registro
		FROM mepa_atencion_solicitud_zona z
			INNER JOIN tbl_usuarios tu
			ON z.id_usuario = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN mepa_zona_asignacion za
			ON z.id_zona = za.id
			INNER JOIN tbl_razon_social rz
			ON tp.razon_social_id = rz.id
		WHERE z.status = 1 
			".$where_redes."
			".$where_todos."
			".$where_id_usuario."
			".$where_id_zona."
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
		$data[] = array(
			"0" => $reg->usuario.'-'.$reg->id_usuario,
			"1" => $reg->zona,
			"2" => $reg->fecha_registro,
			"3" => $reg->id_usuario
		);
	}

	$resultado = array(
		"sEcho" => 1,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);

	echo json_encode($resultado);
}

if(isset($_POST["accion"]) && $_POST["accion"]==="sec_mepa_zona_asignacion_listar_zonas")
{
	$param_usuario_id = $_POST['param_usuario_id'];
	$login_usuario_id = $login?$login['id']:null;

	// INICIO: OBTENER RED
	
	$where_zonas = "";

	$select_red =
    "
        SELECT
            l.red_id
        FROM tbl_usuarios_locales ul
            INNER JOIN tbl_locales l
            ON ul.local_id = l.id
        WHERE ul.usuario_id = '".$login_usuario_id."'
        	AND ul.estado = 1 AND l.red_id IS NOT NULL
        GROUP BY l.red_id
    ";

    $data_select_red = $mysqli->query($select_red);
    
    $row_count_data_select_red = $data_select_red->num_rows;

    $ids_data_select_red = '';
    $contador_ids = 0;
    
    if ($row_count_data_select_red > 0) 
    {
        while ($row = $data_select_red->fetch_assoc()) 
        {
        	if ($contador_ids > 0) 
            {
                $ids_data_select_red .= ',';
            }

            $ids_data_select_red .= $row["red_id"];           
            $contador_ids++;
        }

        $where_zonas = " AND za.tbl_locales_redes_id IN ($ids_data_select_red) ";
    }

	// FIN: OBTENER RED

	$zonas_asignadas = array();
	$lista_todas_zona = "";
	$usuario = "";

	if($param_usuario_id != 0)
	{
		$sel_query_zonas_usuario = $mysqli->query("
			SELECT
				z.id_usuario, z.id_zona,
			    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS usuario
			FROM mepa_atencion_solicitud_zona z
				INNER JOIN tbl_usuarios tu
				ON z.id_usuario = tu.id
				INNER JOIN tbl_personal_apt tp
				ON tu.personal_id = tp.id
			WHERE z.id_usuario = '".$param_usuario_id."'
		");

		while($zonas = $sel_query_zonas_usuario->fetch_object())
		{
			$usuario = $zonas->usuario;
			array_push($zonas_asignadas, $zonas->id_zona);
		}
	}
	
	$sel_query = $mysqli->query("
		SELECT
			za.id, za.nombre, lr.nombre AS red_nombre
		FROM mepa_zona_asignacion za
			INNER JOIN tbl_locales_redes lr
			ON za.tbl_locales_redes_id = lr.id
		WHERE za.status = 1
			".$where_zonas."
	");

	while($sel = $sel_query->fetch_object())
	{
		$c_m = in_array($sel->id, $zonas_asignadas) ? 'checked':'';

		$lista_todas_zona .= '<li><input type="checkbox" '.$c_m.' name="permiso_usuario_zona[]" value="'.$sel->id.'"> '.$sel->nombre. ' - '.$sel->red_nombre.'</li>';
	}

	$result["lista_zonas"] = $lista_todas_zona;
	$result["usuario"] = $usuario;

	echo json_encode($result);

	exit;

}

if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_zona_asignacion_nuevo_usuario_zona")
{
	$select_zonas_nuevas = $_POST['permiso_usuario_zona'];
	$param_usuario = $_POST['sec_mepa_form_param_usuario'];

	$error = '';
	$num_elementos = 0;

	if($select_zonas_nuevas != null)
	{
		while($num_elementos < count($select_zonas_nuevas))
		{
			//INSERTAMOS EN LA TABLA mepa_atencion_solicitud_zona

			$query_insert = "INSERT INTO mepa_atencion_solicitud_zona
							(
								id_usuario,
								id_zona,
								status,
								user_created_id,
								created_at,
								user_updated_id,
								updated_at
							)
							VALUES
							(
								'".$param_usuario."',
								'".$select_zonas_nuevas[$num_elementos]."',
								1,
								'".$login["id"]."', 
								'".date('Y-m-d H:i:s')."',
								'".$login["id"]."', 
								'".date('Y-m-d H:i:s')."'
							)
							";
			
			$num_elementos ++;

			$mysqli->query($query_insert);

			if($mysqli->error)
			{
				$error = $mysqli->error;

				$result["http_code"] = 400;
				$result["status"] = "Error al registrar.";
				$result["error"] = $error;

				echo json_encode($result);
				exit;
			}
		}
	}
	else
	{
		$result["http_code"] = 400;
		$result["status"] = "Error al registrar";
		$result["error"] = "Seleccione la zona.";

		echo json_encode($result);
		exit;
	}

	if($mysqli->error)
	{
		$error = $mysqli->error;

		$result["http_code"] = 400;
		$result["status"] = "Error al registrar.";
		$result["error"] = $error;
	}
	else
	{
		$result["http_code"] = 200;
		$result["status"] = "";
		$result["error"] = "";
	}

	echo json_encode($result);
}

if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_zona_asignacion_editar_usuario_zona")
{
	$select_zonas_nuevas = $_POST['permiso_usuario_zona'];
	$param_usuario_id = $_POST['zona_asignacion_usuario_id'];

	$error = '';
	$num_elementos = 0;

	$query_delete = "DELETE FROM mepa_atencion_solicitud_zona WHERE id_usuario = '".$param_usuario_id."' ";

	$mysqli->query($query_delete);

	if($select_zonas_nuevas != null)
	{
		while($num_elementos < count($select_zonas_nuevas))
		{
			//INSERTAMOS EN LA TABLA mepa_atencion_solicitud_zona

			$query_insert = "INSERT INTO mepa_atencion_solicitud_zona
							(
								id_usuario,
								id_zona,
								status,
								user_created_id,
								created_at,
								user_updated_id,
								updated_at
							)
							VALUES
							(
								'".$param_usuario_id."',
								'".$select_zonas_nuevas[$num_elementos]."',
								1,
								'".$login["id"]."', 
								'".date('Y-m-d H:i:s')."',
								'".$login["id"]."', 
								'".date('Y-m-d H:i:s')."'
							)
							";
			
			$num_elementos ++;

			$mysqli->query($query_insert);

			if($mysqli->error)
			{
				$error = $mysqli->error;

				$result["http_code"] = 400;
				$result["status"] = "Error al registrar.";
				$result["error"] = $error;

				echo json_encode($result);

				exit;

			}
		}
	}

	if($mysqli->error)
	{
		$error = $mysqli->error;

		$result["http_code"] = 400;
		$result["status"] = "Error al registrar.";
		$result["error"] = $error;
	}
	else
	{
		$result["http_code"] = 200;
		$result["status"] = "";
		$result["error"] = "";
	}

	echo json_encode($result);
}

?>