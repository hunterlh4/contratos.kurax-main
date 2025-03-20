<?php 

$result=array();

include("db_connect.php");
include("sys_login.php");


if (isset($_POST["accion"]) && $_POST["accion"]==="cont_listar_servicios_categoria")
{
	$cont_categoria_select_situacion = $_POST['cont_categoria_select_situacion'];

	$query = "SELECT id, nombre, status as estado FROM cont_categoria_servicio";
	
	if($cont_categoria_select_situacion == "0")
	{
		$query .= " WHERE status = 0";
	}
	elseif($cont_categoria_select_situacion == "1")
	{
		$query .= " WHERE status = 1";
	}
	

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->nombre,
			"2" => ($reg->estado) ? '<input class="switch" id="checkbox_'.$reg->id.'" type="checkbox" data-table="cont_categoria_servicio" data-id="'.$reg->id.'" data-col="status" data-on-value="1" data-off-value="0" checked="checked" data-ignore="true">' : '<input class="switch" id="checkbox_'.$reg->id.'" type="checkbox" data-table="cont_categoria_servicio" data-id="'.$reg->id.'" data-col="status" data-on-value="1" data-off-value="0" data-ignore="true">',
			"3" => '<button class="btn btn-warning btn-sm" title="Editar" 
			onclick="obtener_categoria_servicio('.$reg->id.')"><i class="fa fa-pencil"></i></button>'
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


if (isset($_POST["accion"]) && $_POST["accion"]==="guardaryeditar")
{
	$message = "";
	
	$txtidserviciocategoria = $_POST['txtidserviciocategoria'];
	$txtnombre = $_POST['txtnombre'];
	

	if(empty($txtidserviciocategoria))
	{	
		$querySelect = "SELECT nombre FROM cont_categoria_servicio WHERE nombre = '".$txtnombre."' LIMIT 1";

		//$querySelect = "SELECT nombre FROM cont_categoria_servicio WHERE nombre = '".."' LIMIT 1";

		$list_query = $mysqli->query($querySelect);

		$li = $list_query->fetch_assoc();

		if($li == "")
		{
			$query = "INSERT INTO cont_categoria_servicio (nombre, status, created_id, created_at, updated_id, update_at) VALUES ('".$txtnombre."',  1, '".$login["id"]."', '".date('Y-m-d')."', '".$login["id"]."', '".date('Y-m-d')."')";

			$mysqli->query($query);

			if($mysqli->error)
			{
				$status = false;
				$message = $mysqli->error;
			}
			else
			{
				$message = "Datos guardados correctamente";
				$status = true;
			}
			$valor = 1;
		}
		else
		{
			$message = "La categoría que intenta registrar ya existe, porfavor, ingresa uno que no exista.";
			$status = false;
			$valor = 2;
		}
	}
	else
	{
		$querySelect = "SELECT nombre FROM cont_categoria_servicio WHERE nombre = '".$txtnombre."' LIMIT 1";

		$list_query = $mysqli->query($querySelect);

		$li = $list_query->fetch_assoc();

		if($li == "")
		{
			$query = "UPDATE cont_categoria_servicio SET nombre = '".$txtnombre."', updated_id = '".$login["id"]."', update_at = '".date('Y-m-d')."' WHERE id = '".$txtidserviciocategoria."'";

			$mysqli->query($query);

			if($mysqli->error)
			{
				$status = false;
				$message = $mysqli->error;
			}
			else
			{
				$message = "Datos actualizados correctamente";
				$status = true;
			}

			$valor = 3;
		}
		else
		{
			$message = "La categoría que intenta actualizar ya existe, porfavor, ingresa uno que no exista.";
			$status = false;
			$valor = 4;
		}
		

	}

	echo json_encode([
		'status' => $status,
		'message' => $message,
		'valor' => $valor
	]);

}

if (isset($_POST["accion"]) && $_POST["accion"]==="desactivar_categoria")
{
	$message = "";
	
	$txtidserviciocategoria = $_POST['txtidserviciocategoria'];
	
	$query = "UPDATE cont_categoria_servicio SET status = 0 WHERE id = '".$txtidserviciocategoria."'";

	$mysqli->query($query);

	if($mysqli->error)
	{
		$status = false;
		$message = $mysqli->error;
	}
	else
	{
		$message = "Categoría desactivada";
		$status = true;
	}

	echo json_encode([
		'status' => $status,
		'message' => $message
	]);

}

if (isset($_POST["accion"]) && $_POST["accion"]==="activar_categoria")
{
	$message = "";
	
	$txtidserviciocategoria = $_POST['txtidserviciocategoria'];
	
	$query = "UPDATE cont_categoria_servicio SET status = 1 WHERE id = '".$txtidserviciocategoria."'";

	$mysqli->query($query);

	if($mysqli->error)
	{
		$status = false;
		$message = $mysqli->error;
	}
	else
	{
		$message = "Categoría activada";
		$status = true;
	}

	echo json_encode([
		'status' => $status,
		'message' => $message
	]);

}


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_dato_categoria_servicio")
{
	$message = "";
	
	$txtidserviciocategoria = $_POST['txtidserviciocategoria'];
	
	$query = "SELECT id, nombre FROM cont_categoria_servicio WHERE id = '".$txtidserviciocategoria."'";

	$list_query = $mysqli->query($query);

	$li = $list_query->fetch_assoc();

	if($mysqli->error)
	{
		$status = false;
		$message = $mysqli->error;
	}
	else
	{
		$message = "Se obtuvo el registro correctamente";
		$status = true;
	}

	echo json_encode([
		'status' => $status,
		'message' => $message,
		'dato' => $li
	]);

}


if (isset($_POST["accion"]) && $_POST["accion"]==="cont_listar_servicios_tipo_categoria")
{
	$cont_tipo_categoria_select_situacion = $_POST['cont_tipo_categoria_select_situacion'];

	$query = "SELECT tc.id as id_tipo_categoria, c.id as id_categoria, c.nombre as categoria, tc.nombre as tipo_categoria, tc.status as estado 
		FROM cont_tipo_categoria_servicio tc 
		INNER JOIN cont_categoria_servicio c 
		ON tc.categoria_servicio_id = c.id AND c.status = 1";

	if($cont_tipo_categoria_select_situacion == "0")
	{
		$query .= " WHERE tc.status = 0";
	}
	elseif($cont_tipo_categoria_select_situacion == "1")
	{
		$query .= " WHERE tc.status = 1";
	}
	

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
		$data[] = array(
			"0" => $reg->id_tipo_categoria,
			"1" => $reg->categoria,
			"2" => $reg->tipo_categoria,
			"3" => ($reg->estado) ? '<input class="switch" id="checkbox_'.$reg->id_tipo_categoria.'" type="checkbox" data-table="cont_tipo_categoria_servicio" data-id="'.$reg->id_tipo_categoria.'" data-col="status" data-on-value="1" data-off-value="0" checked="checked" data-ignore="true">' : '<input class="switch" id="checkbox_'.$reg->id_tipo_categoria.'" type="checkbox" data-table="cont_tipo_categoria_servicio" data-id="'.$reg->id_tipo_categoria.'" data-col="status" data-on-value="1" data-off-value="0" data-ignore="true">',
			"4" => '<button class="btn btn-warning btn-sm" title="Editar" 
			onclick="obtener_tipo_categoria_servicio('.$reg->id_tipo_categoria.')"><i class="fa fa-pencil"></i></button>'
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


if (isset($_POST["accion"]) && $_POST["accion"]==="obtenerSelectCategorias")
{
	$message = "";
	
	$txtidserviciocategoria = $_POST['txtidserviciocategoria'];
	
	$query = "SELECT id, nombre FROM cont_categoria_servicio WHERE status = 1";

	$list_query = $mysqli->query($query);


	while ($li = $list_query->fetch_object()) 
	{
		echo '<option value='.$li->id.'>'.$li->nombre.'</option>';
	}
}


if (isset($_POST["accion"]) && $_POST["accion"]==="guardaryeditarTipoCategoria")
{
	$message = "";
	
	$txtidserviciotipocategoria = $_POST['txtidserviciotipocategoria'];
	$txtidserviciocategoriaselect = $_POST['txtidserviciocategoriaselect'];
	$txtnombretipocategoria = $_POST['txtnombretipocategoria'];
	

	if(empty($txtidserviciotipocategoria))
	{	
		$querySelect = "SELECT nombre FROM cont_tipo_categoria_servicio WHERE categoria_servicio_id = '".$txtidserviciocategoriaselect."' AND nombre = '".$txtnombretipocategoria."' LIMIT 1";


		$list_query = $mysqli->query($querySelect);

		$li = $list_query->fetch_assoc();

		if($li == "")
		{
			$query = "INSERT INTO cont_tipo_categoria_servicio (categoria_servicio_id, nombre, status, created_id, created_at, updated_id, updated_at) VALUES ('".$txtidserviciocategoriaselect."' ,'".$txtnombretipocategoria."',  1, '".$login["id"]."', '".date('Y-m-d')."', '".$login["id"]."', '".date('Y-m-d')."')";

			$mysqli->query($query);

			if($mysqli->error)
			{
				$status = false;
				$message = $mysqli->error;
			}
			else
			{
				$message = "Datos guardados correctamente";
				$status = true;
			}
			$valor = 1;
		}
		else
		{
			$message = "El tipo de categoría que intenta registrar ya existe en una determinada categoría, porfavor, ingresa uno que no exista.";
			$status = false;
			$valor = 2;
		}
	}
	else
	{
		$querySelect = "SELECT nombre FROM cont_tipo_categoria_servicio WHERE categoria_servicio_id = '".$txtidserviciocategoriaselect."' AND nombre = '".$txtnombretipocategoria."' LIMIT 1";

		$list_query = $mysqli->query($querySelect);

		$li = $list_query->fetch_assoc();

		if($li == "")
		{
			$query = "UPDATE cont_tipo_categoria_servicio SET nombre = '".$txtnombretipocategoria."', updated_id = '".$login["id"]."', updated_at = '".date('Y-m-d')."' WHERE id = '".$txtidserviciotipocategoria."'";

			$mysqli->query($query);

			if($mysqli->error)
			{
				$status = false;
				$message = $mysqli->error;
			}
			else
			{
				$message = "Datos actualizados correctamente";
				$status = true;
			}

			$valor = 3;
		}
		else
		{
			$message = "El tipo de categoría que intenta actualizar ya existe, porfavor, ingresa uno que no exista.";
			$status = false;
			$valor = 4;
		}
		

	}

	echo json_encode([
		'status' => $status,
		'message' => $message,
		'valor' => $valor
	]);

}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_dato_tipo_categoria_servicio")
{
	$message = "";
	
	$txtidserviciotipocategoria = $_POST['txtidserviciotipocategoria'];
	
	$query = "SELECT tc.id, tc.nombre as tipo_categoria, c.id as id_categoria, c.nombre as categoria
			FROM cont_tipo_categoria_servicio tc
			INNER JOIN cont_categoria_servicio c
			ON tc.categoria_servicio_id = c.id
			WHERE tc.id = '".$txtidserviciotipocategoria."'";

	$list_query = $mysqli->query($query);

	$li = $list_query->fetch_assoc();

	if($mysqli->error)
	{
		$status = false;
		$message = $mysqli->error;
	}
	else
	{
		$message = "Se obtuvo el registro correctamente";
		$status = true;
	}

	echo json_encode([
		'status' => $status,
		'message' => $message,
		'dato' => $li
	]);

}

if (isset($_POST["accion"]) && $_POST["accion"]==="desactivar_tipo_categoria")
{
	$message = "";
	
	$txtidserviciotipocategoria = $_POST['txtidserviciotipocategoria'];
	
	$query = "UPDATE cont_tipo_categoria_servicio SET status = 0 WHERE id = '".$txtidserviciotipocategoria."'";

	$mysqli->query($query);

	if($mysqli->error)
	{
		$status = false;
		$message = $mysqli->error;
	}
	else
	{
		$message = "Tipo de categoría desactivada";
		$status = true;
	}

	echo json_encode([
		'status' => $status,
		'message' => $message
	]);

}

if (isset($_POST["accion"]) && $_POST["accion"]==="activar_tipo_categoria")
{
	$message = "";
	
	$txtidserviciotipocategoria = $_POST['txtidserviciotipocategoria'];
	
	$query = "UPDATE cont_tipo_categoria_servicio SET status = 1 WHERE id = '".$txtidserviciotipocategoria."'";

	$mysqli->query($query);

	if($mysqli->error)
	{
		$status = false;
		$message = $mysqli->error;
	}
	else
	{
		$message = "Tipo de categoría activada";
		$status = true;
	}

	echo json_encode([
		'status' => $status,
		'message' => $message
	]);

}


?>
