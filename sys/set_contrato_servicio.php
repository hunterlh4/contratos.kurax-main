<?php 
date_default_timezone_set("America/Lima");

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
			"0" => $reg->nombre,
			"1" => ($reg->estado) ? '<input class="switch" id="checkbox_'.$reg->id.'" type="checkbox" data-table="cont_categoria_servicio" data-id="'.$reg->id.'" data-col="status" data-on-value="1" data-off-value="0" checked="checked" data-ignore="true">' : '<input class="switch" id="checkbox_'.$reg->id.'" type="checkbox" data-table="cont_categoria_servicio" data-id="'.$reg->id.'" data-col="status" data-on-value="1" data-off-value="0" data-ignore="true">',
			"2" => '<button class="btn btn-warning btn-sm" title="Editar" 
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
			"0" => $reg->categoria,
			"1" => $reg->tipo_categoria,
			"2" => ($reg->estado) ? '<input class="switch" id="checkbox_'.$reg->id_tipo_categoria.'" type="checkbox" data-table="cont_tipo_categoria_servicio" data-id="'.$reg->id_tipo_categoria.'" data-col="status" data-on-value="1" data-off-value="0" checked="checked" data-ignore="true">' : '<input class="switch" id="checkbox_'.$reg->id_tipo_categoria.'" type="checkbox" data-table="cont_tipo_categoria_servicio" data-id="'.$reg->id_tipo_categoria.'" data-col="status" data-on-value="1" data-off-value="0" data-ignore="true">',
			"3" => '<button class="btn btn-warning btn-sm" title="Editar" 
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
			$message = "El tipo de contrato que intenta registrar ya existe en una determinada tipo de contarto, porfavor, ingresa uno que no exista.";
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

if (isset($_POST["accion"]) && $_POST["accion"] == "guardar_categoria_servicio")
{
	$message = "";
	
	$id_categoria_servicio = $_POST['id_categoria_servicio'];
	$nombre = $_POST['nombre'];
	
	if (Empty($id_categoria_servicio)) {
		$query = "INSERT INTO cont_categoria_servicio (nombre, status, created_id, created_at, updated_id, update_at) 
		VALUES (
			'".$nombre."', 
			1, 
			'".$login["id"]."', 
			'".date('Y-m-d')."', 
			'".$login["id"]."', 
			'".date('Y-m-d')."')";
		$mysqli->query($query);

		if($mysqli->error)
		{
			$status = 404;
			$message = $query." | ".$mysqli->error;
		}
		else
		{
			$message = "Datos guardados correctamente";
			$status = 200;
		}
	}else{
		$query = "UPDATE cont_categoria_servicio SET 
		nombre = '".$nombre."',
		updated_id = '".$login["id"]."', 
		update_at = '".date('Y-m-d')."'
		WHERE id = ".$id_categoria_servicio;
		$mysqli->query($query);

		if($mysqli->error)
		{
			$status = 404;
			$message = $query." | ".$mysqli->error;
		}
		else
		{
			$message = "Datos guardados correctamente";
			$status = 200;
		}
	}
	

	echo json_encode([
		'http_code' => $status,
		'message' => $message,
	]);
	exit();

}

if (isset($_POST["accion"]) && $_POST["accion"] == "modificar_estado_categoria_servicio")
{
	$message = "";
	
	$id_categoria_servicio = $_POST['id_categoria_servicio'];
	$estado = $_POST['estado'];
	
	$query = "UPDATE cont_categoria_servicio SET 
	status = '".$estado."',
	updated_id = '".$login["id"]."', 
	update_at = '".date('Y-m-d')."'
	WHERE id = ".$id_categoria_servicio;
	$mysqli->query($query);

	if($mysqli->error)
	{
		$status = 404;
		$message = $query." | ".$mysqli->error;
	}
	else
	{
		$message = "Datos guardados correctamente";
		$status = 200;
	}
	
	

	echo json_encode([
		'http_code' => $status,
		'message' => $message,
	]);
	exit();

}

if (isset($_POST["accion"]) && $_POST["accion"] == "listar_categoria_servicio")
{
	$sql_query = "SELECT* FROM cont_categoria_servicio ORDER BY id DESC"; 
	$query = $mysqli->query($sql_query);
	
	$html = '
	<table class="table table-bordered data-table" width="100%">
		<thead>
			<tr>
				<td class="text-center">Nombre Categoria</td>
				<td class="text-center">Estado</td>
				<td class="text-center">Acciones</td>
			</tr>
		</thead>
		<tbody>';
		while($sel = $query->fetch_assoc()){
			if ($sel['status'] == 1) {
				$btn_estado = '<button onclick="sec_contrato_detalle_solicitud_modal_cambiar_estado_categoria_servicio('.$sel['id'].',0)" type="button" class="btn-success btn btn-xs">Activo</button>';
			}else{
				$btn_estado = '<button onclick="sec_contrato_detalle_solicitud_modal_cambiar_estado_categoria_servicio('.$sel['id'].',1)" type="button" class="btn-danger btn btn-xs">Inactivo</button>';
			}
			$html .= '
			<tr>
				<td class="text-left">'.$sel['nombre'].'</td>
				<td class="text-center">'.$btn_estado.'</td>
				<td class="text-center">
					<button class="btn btn-warning btn-xs" title="Editar" onclick="sec_contrato_detalle_solicitud_modal_obtener_datos_categoria_servicio('.$sel['id'].')"><i class="fa fa-pencil"></i></button>
				</td>
			</tr>';
		}
	
	$html .= '
		</tbody>
	</table>
	';
	
	echo json_encode([
		'http_code' => 200,
		'result' => $html,
	]);
	exit();

}

if (isset($_POST["accion"]) && $_POST["accion"] == "obtener_categoria_servicio")
{
	
	$id_categoria_servicio = $_POST['id_categoria_servicio'];
	$query_sql = "SELECT * FROM cont_categoria_servicio WHERE id = ".$id_categoria_servicio;
	$query = $mysqli->query($query_sql);
	$result = array();
	while($sel = $query->fetch_assoc()){
		$result = $sel;
	}

	if($mysqli->error)
	{
		$status = 404;
		$message = $query." | ".$mysqli->error;
	}
	else
	{
		$message = "Datos guardados correctamente";
		$status = 200;
	}
	
	

	echo json_encode([
		'http_code' => $status,
		'message' => $message,
		'result' => $result,
	]);
	exit();

}





if (isset($_POST["accion"]) && $_POST["accion"] == "guardar_tipo_categoria_servicio")
{
	$message = "";
	
	$id_tipo_categoria_servicio = $_POST['id_tipo_categoria_servicio'];
	$categoria_servicio_id = $_POST['categoria_servicio_id'];
	$nombre = $_POST['nombre'];
	
	if (Empty($id_tipo_categoria_servicio)) {
		$query = "INSERT INTO cont_tipo_categoria_servicio (categoria_servicio_id, nombre, status, created_id, created_at, updated_id, updated_at) 
		VALUES (
			'".$categoria_servicio_id."', 
			'".$nombre."', 
			1, 
			'".$login["id"]."', 
			'".date('Y-m-d')."', 
			'".$login["id"]."', 
			'".date('Y-m-d')."')";
		$mysqli->query($query);

		if($mysqli->error)
		{
			$status = 404;
			$message = $query." | ".$mysqli->error;
		}
		else
		{
			$message = "Datos guardados correctamente";
			$status = 200;
		}
	}else{
		$query = "UPDATE cont_tipo_categoria_servicio SET 
		categoria_servicio_id = '".$categoria_servicio_id."',
		nombre = '".$nombre."',
		updated_id = '".$login["id"]."', 
		updated_at = '".date('Y-m-d')."'
		WHERE id = ".$id_tipo_categoria_servicio;
		$mysqli->query($query);

		if($mysqli->error)
		{
			$status = 404;
			$message = $query." | ".$mysqli->error;
		}
		else
		{
			$message = "Datos guardados correctamente";
			$status = 200;
		}
	}
	

	echo json_encode([
		'http_code' => $status,
		'message' => $message,
	]);
	exit();

}

if (isset($_POST["accion"]) && $_POST["accion"] == "modificar_estado_tipo_categoria_servicio")
{
	$message = "";
	
	$id_tipo_categoria_servicio = $_POST['id_tipo_categoria_servicio'];
	$estado = $_POST['estado'];
	
	$query = "UPDATE cont_tipo_categoria_servicio SET 
	status = '".$estado."'
	WHERE id = ".$id_tipo_categoria_servicio;
	$mysqli->query($query);

	if($mysqli->error)
	{
		$status = 404;
		$message = $query." | ".$mysqli->error;
	}
	else
	{
		$message = "Datos guardados correctamente";
		$status = 200;
	}
	
	

	echo json_encode([
		'http_code' => $status,
		'message' => $message,
	]);
	exit();

}

if (isset($_POST["accion"]) && $_POST["accion"] == "listar_tipo_categoria_servicio")
{
	$sql_query = "SELECT t.id, t.categoria_servicio_id, t.nombre, t.status, c.nombre AS nombre_categoria  
	FROM cont_tipo_categoria_servicio AS t
	INNER JOIN cont_categoria_servicio AS c ON c.id = t.categoria_servicio_id
	ORDER BY t.id DESC"; 
	$query = $mysqli->query($sql_query);
	
	$html = '
	<table class="table table-bordered data-table" width="100%">
		<thead>
			<tr>
				<td class="text-center">Categoria</td>
				<td class="text-center">Tipo Categoria</td>
				<td class="text-center">Estado</td>
				<td class="text-center">Acciones</td>
			</tr>
		</thead>
		<tbody>';
		while($sel = $query->fetch_assoc()){
			if ($sel['status'] == 1) {
				$btn_estado = '<button onclick="sec_contrato_detalle_solicitud_modal_cambiar_estado_tipo_categoria_servicio('.$sel['id'].',0)" type="button" class="btn-success btn btn-xs">Activo</button>';
			}else{
				$btn_estado = '<button onclick="sec_contrato_detalle_solicitud_modal_cambiar_estado_tipo_categoria_servicio('.$sel['id'].',1)" type="button" class="btn-danger btn btn-xs">Inactivo</button>';
			}
			$html .= '
			<tr>
				<td class="text-left">'.$sel['nombre_categoria'].'</td>
				<td class="text-left">'.$sel['nombre'].'</td>
				<td class="text-center">'.$btn_estado.'</td>
				<td class="text-center">
					<button class="btn btn-warning btn-xs" title="Editar" onclick="sec_contrato_detalle_solicitud_modal_obtener_datos_tipo_categoria_servicio('.$sel['id'].')"><i class="fa fa-pencil"></i></button>
				</td>
			</tr>';
		}
	
	$html .= '
		</tbody>
	</table>
	';
	
	echo json_encode([
		'http_code' => 200,
		'result' => $html,
	]);
	exit();

}

if (isset($_POST["accion"]) && $_POST["accion"] == "obtener_tipo_categoria_servicio")
{
	
	$id_tipo_categoria_servicio = $_POST['id_tipo_categoria_servicio'];
	$query_sql = "SELECT t.id, t.categoria_servicio_id, t.nombre, t.status, c.nombre AS nombre_categoria  
	FROM cont_tipo_categoria_servicio AS t
	INNER JOIN cont_categoria_servicio AS c ON c.id = t.categoria_servicio_id
	WHERE t.id = ".$id_tipo_categoria_servicio;
	$query = $mysqli->query($query_sql);
	$result = array();
	while($sel = $query->fetch_assoc()){
		$result = $sel;
	}

	if($mysqli->error)
	{
		$status = 404;
		$message = $query." | ".$mysqli->error;
	}
	else
	{
		$message = "Datos guardados correctamente";
		$status = 200;
	}
	
	

	echo json_encode([
		'http_code' => $status,
		'message' => $message,
		'result' => $result,
	]);
	exit();

}



if (isset($_POST["accion"]) && $_POST["accion"] == "obtener_listar_categoria_servicio")
{
	$sql_query = "SELECT 
		id, nombre
	FROM cont_categoria_servicio
	WHERE status = 1
	ORDER BY nombre ASC"; 
	$query = $mysqli->query($sql_query);
	$html = '<option value="0">- Seleccione -</option>';
	while($sel = $query->fetch_assoc()){
		$html .= '<option value="'.$sel['id'].'">'.$sel['nombre'].'</option>';
	}
	echo json_encode([
		'http_code' => 200,
		'result' => $html,
	]);
	exit();

}

?>
