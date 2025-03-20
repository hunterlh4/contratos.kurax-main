<?php
include("db_connect.php");
if ($_POST['where']=="mantenimiento_registrar_botones") {
	$array_nombre_botones = array();
	$sql_nombre_botones = "SELECT id,nombre FROM tbl_botones";
	$result_nombre_botones = $mysqli->query($sql_nombre_botones);
	while($row_nombre_botones = $result_nombre_botones->fetch_assoc()) {
		$array_nombre_botones[$row_nombre_botones["id"]] = $row_nombre_botones["nombre"];
	}

	$group_ids = [];
	$result = $mysqli->query("SELECT DISTINCT id FROM tbl_usuarios_grupos");
	while($r = $result->fetch_assoc()) $group_ids[] = $r["id"];

	if (isset($_POST['array_botones_ins'])) {
		$data_botones_ins = $_POST['array_botones_ins'];
		if(count($data_botones_ins) > 0){
			foreach ($data_botones_ins as $key => $value){
				foreach ($group_ids as $group_id) {
					$mysqli->query("INSERT INTO tbl_permisos (grupo_id, usuario_id, menu_id, boton_id, boton_nombre, estado, manual)
									VALUES (" . $group_id . ", 0, " . $_POST['menu_id'] . ", " . $value . ", '" . $array_nombre_botones[$value] . "', 0, 0)");
				}
				$sql_insert = "INSERT INTO tbl_menu_sistemas_botones (menu_id,boton,nombre)
								VALUES ('".$_POST['menu_id']."','".$value."','".$array_nombre_botones[$value]."')";
				$mysqli->query($sql_insert);
			}
		}
	}

	if (isset($_POST['array_botones_elm'])){
		$data_botones_elm = $_POST['array_botones_elm'];
		if(count($data_botones_elm) > 0){
			foreach ($data_botones_elm as $key => $value){
				$sql_delete = "DELETE FROM tbl_menu_sistemas_botones WHERE menu_id = '" . $_POST['menu_id'] . "' AND boton = '" . $value."'";
				$mysqli->query("DELETE FROM tbl_permisos WHERE menu_id = '" . $_POST['menu_id'] . "' AND usuario_id = 0 AND boton_id = " . $value );
				$mysqli->query($sql_delete);
			}
		}
	}
	/*$sql_delete = "DELETE FROM tbl_menu_sistemas_botones WHERE menu_id='".$_POST['menu_id']."'";
	$mysqli->query("DELETE FROM tbl_permisos WHERE menu_id='".$_POST['menu_id']."' AND usuario_id = 0");
	$mysqli->query($sql_delete);
	if (isset($_POST['array_botones'])) {
		$data_botones = $_POST['array_botones'];
		if ($data_botones){
			$array_nombre_botones=array();
			$sql_nombre_botones = "SELECT id,nombre FROM tbl_botones";
			$result_nombre_botones = $mysqli->query($sql_nombre_botones);
			while($row_nombre_botones = $result_nombre_botones->fetch_assoc()) {
				$array_nombre_botones[$row_nombre_botones["id"]] = $row_nombre_botones["nombre"];
			}

			$group_ids = [];
			$result = $mysqli->query("SELECT DISTINCT id FROM tbl_usuarios_grupos");
			while($r = $result->fetch_assoc()) $group_ids[] = $r["id"];

			foreach ($data_botones as $key => $value) {
				foreach ($group_ids as $group_id) {
					$mysqli->query("INSERT INTO tbl_permisos (grupo_id, usuario_id, menu_id, boton_id, boton_nombre, estado, manual)
					VALUES (".$group_id.",0,".$_POST['menu_id'].",".$value.",'".$array_nombre_botones[$value]."',0,0)");
				}
				
				$sql_insert="INSERT INTO tbl_menu_sistemas_botones (menu_id,boton,nombre)
				VALUES ('".$_POST['menu_id']."','".$value."','".$array_nombre_botones[$value]."')";
				$mysqli->query($sql_insert);
			}
		}
	}*/
}
if ($_POST['where']=="mantenimiento_cargar_botones") {
	$array_select = array();
	$result_final_selected=array();	
	$sql_selected = "SELECT boton,nombre FROM tbl_menu_sistemas_botones WHERE menu_id = '".$_POST['menu_id']."'";
	$result_selected = $mysqli->query($sql_selected);
	while($row_selected = $result_selected->fetch_assoc()) {
		$result_final_selected[$row_selected['boton']] = $row_selected;
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
/*
if ($_POST['where']=="mantenimiento_eliminar_botones") {
	$sql_delete = "DELETE FROM tbl_menu_sistemas_botones WHERE boton='".$_POST['id_option'][0]."' AND menu_id='".$_POST['menu_id']."'";
	$mysqli->query($sql_delete);
	$sql_selected = "SELECT boton,nombre FROM tbl_menu_sistemas_botones WHERE menu_id = ".$_POST['menu_id'];
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
if ($_POST['where']=="mantenimiento_agregar_botones") {
	$id_option = $_POST['id_option'][0];
	$sql_nombre_botones = "SELECT nombre FROM tbl_botones WHERE id = ".$id_option;
	$result_nombre_botones = $mysqli->query($sql_nombre_botones);
	while($row_nombre_botones = $result_nombre_botones->fetch_assoc()) {
		$insert_if_not_exist = "INSERT INTO tbl_menu_sistemas_botones (menu_id,boton,nombre)
		SELECT * FROM (SELECT '".$_POST['menu_id']."','".$id_option."','".$row_nombre_botones['nombre']."' ) AS tmp
		WHERE NOT EXISTS (
			SELECT menu_id,boton,nombre FROM tbl_menu_sistemas_botones WHERE boton = '".$id_option."' AND menu_id='".$_POST['menu_id']."'
		)LIMIT 1";
		$mysqli->query($insert_if_not_exist);
	}
	$sql_selected = "SELECT boton,nombre FROM tbl_menu_sistemas_botones WHERE menu_id = ".$_POST['menu_id'];
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
*/

if ($_POST['where']=="mantenimiento_crear_nuevo_boton") {
		$result_final_selected = array();
		$insert_new_btn = "INSERT INTO tbl_botones (boton, nombre, class) SELECT * FROM (SELECT '".$_POST["filtro"]["primer_nombre_boton"]."', '".$_POST["filtro"]["nombre_boton"]."', '".$_POST["filtro"]["clase_boton"]."') AS tmp WHERE NOT EXISTS (SELECT nombre FROM tbl_botones WHERE nombre = '".$_POST["filtro"]["nombre_boton"]."' ) LIMIT 1";
		$mysqli->query($insert_new_btn);
		$result_final_selected["query"] = $insert_new_btn;
		$result_final_selected["inserted"] = mysqli_affected_rows($mysqli);	
		echo json_encode($result_final_selected);
}
?>
