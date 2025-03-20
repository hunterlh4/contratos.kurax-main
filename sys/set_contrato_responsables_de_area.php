<?php  
date_default_timezone_set("America/Lima");

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_responsables_de_area")
{
	$result = ObtenerResponsablesDeArea($_POST["search"]);
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_responsables_de_area")
{
	$result = GuardarResponsablesDeArea($_POST);
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="listar_responsables_de_area")
{
	$result = ListarResponsablesDeArea('');
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="modificar_estado_responsables_de_area")
{
	$result = ModificarEstadoResponsablesDeArea($_POST["id_responsable"]);
	echo json_encode($result);
	exit();
}




function ObtenerResponsablesDeArea($search){
	include("db_connect.php");
	include("sys_login.php");

	$query = "SELECT u.id, CONCAT(p.nombre, ' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno,'')) AS text  FROM tbl_personal_apt AS p 
	INNER JOIN tbl_usuarios AS u ON u.personal_id = p.id
	WHERE p.estado = 1 AND u.estado = 1 
	AND CONCAT(p.nombre, ' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno,'')) LIKE '%".$search."%'
	ORDER BY CONCAT(p.nombre, IFNULL(p.apellido_paterno, ' '), IFNULL(p.apellido_materno,' ')) ASC LIMIT 10";
	$list_query = $mysqli->query($query);
	$result =  array();
	while($reg = $list_query->fetch_object()) 
	{
		$result[] = $reg;
	}

	$data['status'] = 200;
	$data['result'] = $result;
	$data['message'] = '';
	return $data;
}

function GuardarResponsablesDeArea($request){
	include("db_connect.php");
	include("sys_login.php");

	$query = "SELECT u.id, p.correo FROM tbl_personal_apt AS p 
	INNER JOIN tbl_usuarios AS u ON u.personal_id = p.id
	WHERE u.id = ".$request['responsable_area'];
	$list_query = $mysqli->query($query);
	$correo = "";
	while($reg = $list_query->fetch_object()) 
	{
		$correo = $reg->correo;
	}

	if ($correo == "") { //validar que el responsable tenga un correo registrado
		$data['status'] = 404;
		$data['result'] = '';
		$data['message'] = 'El responsable de área seleccionado no cuenta con un correo.';
		return $data;
	}else{ //validar que el correo sea valido
		if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
			$data['status'] = 404;
			$data['result'] = '';
			$data['message'] = 'El correo del responsable de área seleccionado no cuenta con un correo valido.';
			return $data;
		}
	}

	$query = "INSERT INTO cont_usuarios_gerentes (user_id,status) 
				VALUES (
					".$request['responsable_area'].",
					1
				)";
	$mysqli->query($query);
	if($mysqli->error){
		$data['status'] = 404;
		$data['result'] = $mysqli->error . $query_insert;
		$data['message'] = 'A ocurrido un error, intentalo mas tarde.';
		return $data;
	}

	$data['status'] = 200;
	$data['result'] = mysqli_insert_id($mysqli);
	$data['message'] = 'Se ha registrado el responsable de area exitosamente.';
	return $data;
}

function ListarResponsablesDeArea($search = ''){
	include("db_connect.php");
	include("sys_login.php");

	$query = "SELECT ug.id, ug.user_id, ug.status,
	CONCAT(p.nombre, ' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno,'')) AS personal
	FROM cont_usuarios_gerentes AS ug
	INNER JOIN tbl_usuarios AS u ON u.id = ug.user_id
	INNER JOIN tbl_personal_apt AS p ON p.id = u.personal_id";
	$list_query = $mysqli->query($query);
	$result =  array();
	$index = 1;
	while($reg = $list_query->fetch_object()) 
	{
		$estado = "<input class='switch-responsable-area' id='checkbox_".$reg->status."' type='checkbox' data-id='".$reg->id."' data-on-value='1' data-off-value='0' ".($reg->status == 1 ? "checked='checked'":"" )."' data-ignore='true'>";
		array_push($result, array(
			'index' => $index,
			'nombre' => $reg->personal,
			'estado' => $estado,
		));
		$index++;
	}

	$data['status'] = 200;
	$data['result'] = $result;
	$data['message'] = '';
	return $data;
}

function ModificarEstadoResponsablesDeArea($id_responsable){
	include("db_connect.php");
	include("sys_login.php");

	$query = "UPDATE cont_usuarios_gerentes SET status = (IF(status = 0,  1, 0)) WHERE id = ".$id_responsable;
	$mysqli->query($query);
	if($mysqli->error){
		$data['status'] = 404;
		$data['result'] = $mysqli->error . $query;
		$data['message'] = 'A ocurrido un error, intentalo mas tarde.';
		return $data;
	}

	$data['status'] = 200;
	$data['result'] = $id_responsable;
	$data['message'] = 'Se ha modificado el estado del usuario responsable.';
	return $data;
}

?>

