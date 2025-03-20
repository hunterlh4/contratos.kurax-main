<?php
include("db_connect.php");
if ($_POST['where']=="mantenimiento_registrar_botones") {
	if ($_POST['array_botones']!=null) {

		$data = $_POST['array_botones'];
		$sql_delete = "DELETE FROM tbl_menu_sistemas_botones WHERE menu_id='".$_POST['id_padre']."'";
		$mysqli->query($sql_delete);
		foreach ($data as $key => $value) {
			$sql_nombre_botones = "SELECT nombre FROM tbl_botones WHERE id = ".$value;
			$result_nombre_botones = $mysqli->query($sql_nombre_botones);
				while($row_nombre_botones = $result_nombre_botones->fetch_assoc()) {
					$sql_delete=mysqli_query($mysqli,"INSERT INTO tbl_menu_sistemas_botones (menu_id,boton,nombre)
					SELECT * FROM (SELECT '".$_POST['id_padre']."','".$value."','".$row_nombre_botones['nombre']."' ) AS tmp
					WHERE NOT EXISTS (
					    SELECT menu_id,boton,nombre FROM tbl_menu_sistemas_botones WHERE boton = '".$value."' AND menu_id='".$_POST['id_padre']."'
					)LIMIT 1");
					$mysqli->query($sql_delete);
				}	
		}

	}else{
		if (mysqli_connect_errno())
		{
		  echo "Failed to connect to MySQL: " . mysqli_connect_error();
		}
		$sql_delete = "DELETE FROM tbl_menu_sistemas_botones WHERE menu_id='".$_POST['id_padre']."'";
		$mysqli->query($sql_delete);
	}
}
if ($_POST['where']=="mantenimiento_cargar_botones") {
	$array_select = array();
	$result_final_selected=array();	
	$sql_selected = "SELECT boton,nombre FROM tbl_menu_sistemas_botones WHERE menu_id = ".$_POST['id_padre'];
	$result_selected = $mysqli->query($sql_selected);
	if ($result_selected->num_rows > 0) {
		while($row_selected = $result_selected->fetch_assoc()) {
			$result_final_selected[$row_selected['boton']] = $row_selected;
		} 
	}
	$sql_available = "SELECT id,nombre FROM tbl_botones";
	$result_available = $mysqli->query($sql_available);
	    while($row_available = $result_available->fetch_assoc()) {
			if(array_key_exists($row_available["id"], $result_final_selected)){
				$array_select["selected"][$row_available["id"]]=$row_available["nombre"];
			}else{
				$array_select["available"][$row_available["id"]]=$row_available["nombre"];
			}
		}
	echo json_encode($array_select);
}
if ($_POST['where']=="mantenimiento_eliminar_opciones") {
	$sql_delete = "DELETE FROM tbl_menu_sistemas_botones WHERE boton='".$_POST['id_option'][0]."' AND menu_id='".$_POST['id_padre']."'";
	$mysqli->query($sql_delete);
	$sql_selected = "SELECT boton,nombre FROM tbl_menu_sistemas_botones WHERE menu_id = ".$_POST['id_padre'];
	$result_selected = $mysqli->query($sql_selected);
		
	$result_final_selected = array();
	if ($result_selected->num_rows > 0) {

		while($row_selected = $result_selected->fetch_assoc()) {
			$result_final_selected[$row_selected['boton']] = $row_selected;
		} 
	}
	$array_select = array();
	$sql_available = "SELECT id,nombre FROM tbl_botones";
	$result_available = $mysqli->query($sql_available);
		    while($row_available = $result_available->fetch_assoc()) {
    			if(array_key_exists($row_available["id"], $result_final_selected)){
    				$array_select["selected"][$row_available["id"]]=$row_available["nombre"];
    			}else{
    				$array_select["available"][$row_available["id"]]=$row_available["nombre"];
    			}
			}
	echo json_encode($array_select);
}
if ($_POST['where']=="mantenimiento_agregar_opciones") {
	$id_option = $_POST['id_option'][0];
	$sql_nombre_botones = "SELECT nombre FROM tbl_botones WHERE id = ".$id_option;
	$result_nombre_botones = $mysqli->query($sql_nombre_botones);
	while($row_nombre_botones = $result_nombre_botones->fetch_assoc()) {
		$insert_if_not_exist = mysqli_query($mysqli,"INSERT INTO tbl_menu_sistemas_botones (menu_id,boton,nombre)
		SELECT * FROM (SELECT '".$_POST['id_padre']."','".$id_option."','".$row_nombre_botones['nombre']."' ) AS tmp
		WHERE NOT EXISTS (
		    SELECT menu_id,boton,nombre FROM tbl_menu_sistemas_botones WHERE boton = '".$id_option."' AND menu_id='".$_POST['id_padre']."'
		)LIMIT 1");
	}
	$sql_selected = "SELECT boton,nombre FROM tbl_menu_sistemas_botones WHERE menu_id = ".$_POST['id_padre'];
	$result_selected = $mysqli->query($sql_selected);
	if ($result_selected->num_rows > 0) {
		$result_final_selected = array();
		while($row_selected = $result_selected->fetch_assoc()) {
			$result_final_selected[$row_selected['boton']] = $row_selected;
		} 
	}
	$array_select = array();
	$sql_available = "SELECT id,nombre FROM tbl_botones";
	$result_available = $mysqli->query($sql_available);
    while($row_available = $result_available->fetch_assoc()) {
		if(array_key_exists($row_available["id"], $result_final_selected)){
			$array_select["selected"][$row_available["id"]]=$row_available["nombre"];
		}else{
			$array_select["available"][$row_available["id"]]=$row_available["nombre"];
		}
	}
	echo json_encode($array_select);
}
?>
