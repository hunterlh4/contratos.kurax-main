<?php
include("db_connect.php");
	if ($_POST["where"]=="sec_permisos_get_user") {
		$result_user_final=array();
		$sql_user = "SELECT nombre,apellido_paterno,apellido_materno,usuario FROM tbl_usuarios WHERE id = '".$_POST['user_id']."'";	
		$result_user = $mysqli->query($sql_user);
		while($row_user = $result_user->fetch_assoc()) {
			$result_user_final["user"]=$row_user["nombre"]." ".$row_user["apellido_paterno"]." ".$row_user["apellido_materno"]." - ".$row_user["usuario"];
		} 
		echo json_encode($result_user_final);
	}
	if($_POST["where"]=="sec_permisos_get_usuarios"){
		$sql_what = "id,nombre,apellido_paterno,apellido_materno,usuario";
		if(array_key_exists("what", $_POST)){
			$sql_what = implode(",", $_POST["what"]);
		}
		$sql_command = "SELECT $sql_what FROM tbl_usuarios";
		$sql_query = $mysqli->query($sql_command);
		while($itm=$sql_query->fetch_assoc()){
			$return["data"][]=$itm;
		}
		echo json_encode($return);
	}
	if ($_POST["where"]=="sec_permisos_get_lista_botones"){
		$array_select = array();
		$result_final_selected = array();	
		$sql_selected = "SELECT boton_id,boton_nombre FROM tbl_permisos WHERE menu_id = '".$_POST['menu_id']."' AND usuario_id='".$_POST['usuario_id']."'";
		$result_selected = $mysqli->query($sql_selected);
		if(mysqli_num_rows($result_selected) > 0){ 		
			while($row_selected = $result_selected->fetch_assoc()) {
				$result_final_selected[$row_selected['boton_id']] = $row_selected["boton_nombre"];
				$array_select["mensaje"]["botones"]= "Permisos asignados correctamente!!!";
			} 
		}else{
			$array_select["mensaje"]["botones"]= "No hay permisos asignados !!!";			
		}
		$array_available_buttons = array();
		$query_botones ="SELECT boton,nombre FROM tbl_menu_sistemas_botones 
		WHERE menu_id  ='".$_POST["menu_id"]."'";
		$result_btns = $mysqli->query($query_botones);
		while($row_btns = $result_btns->fetch_assoc()) {

			$array_available_buttons["available"][$row_btns["boton"]]=$row_btns["nombre"];
			if (array_key_exists($row_btns["boton"], $result_final_selected)) {
				$array_select["selected"][$row_btns['boton']]= $row_btns["nombre"];
			}else{
				$array_select["available"][$row_btns['boton']]= $row_btns["nombre"];	
			}	
		}

		$query_titulo_menu="SELECT titulo FROM tbl_menu_sistemas WHERE id='".$_POST['menu_id']."'";
		$result_titulo_menu = $mysqli->query($query_titulo_menu);
		$rows_titulo_menu=$result_titulo_menu->fetch_assoc();
		$array_select["titulo"]["menu"]= $rows_titulo_menu["titulo"];
		echo json_encode($array_select);
	}
	if($_POST["where"]=="sec_permisos_set_permisos"){
			$array_botones_id_nombres=array();	
			$menu_id=$_POST["menu_id"];
			foreach($_POST["botones"] as $key => $btn_id){
				$sql_botones = "SELECT nombre FROM tbl_botones WHERE id= '".$btn_id."'";
				$result_botones = $mysqli->query($sql_botones);
				while($row_selected = $result_botones->fetch_assoc()) {
					$array_botones_id_nombres[$btn_id]=$row_selected["nombre"];
				}
			}
			$delete_permisos = "DELETE FROM tbl_permisos WHERE menu_id = '".$_POST["menu_id"]."' AND  usuario_id = '".$_POST["usuario"]."'";
			$result_permisos=$mysqli->query($delete_permisos);
				$sql_menu_sistemas = "SELECT grupo_id,codigo_id,relacion_id,relacion_grupo_id,titulo,sistema_id 
				FROM tbl_menu_sistemas WHERE id= '".$menu_id."'";
				$result_menu_sistemas = $mysqli->query($sql_menu_sistemas);
				while($row_menu_sistemas= $result_menu_sistemas->fetch_assoc()) {	
					foreach ($array_botones_id_nombres as $id => $nombre) {
						$insert_permisos = "INSERT INTO tbl_permisos 
						(grupo_id,codigo_id,relacion_id,relacion_grupo_id,usuario_id,personal_id,menu_id,sistema_id,boton_id,boton_nombre,estado) 
						VALUES('".validar_null_fields($row_menu_sistemas["grupo_id"])."','".validar_null_fields($row_menu_sistemas["codigo_id"])."','".validar_null_fields($row_menu_sistemas["relacion_id"])."','".validar_null_fields($row_menu_sistemas["relacion_grupo_id"])."','".$_POST["usuario"]."',0,'".$menu_id."','".validar_null_fields($row_menu_sistemas["sistema_id"])."','".$id."','".$nombre."','1')";
						$mysqli->query($insert_permisos);
					}
				}
	}
	if ($_POST["where"]=="sec_permisos_copiar_permisos") {
		$array_select=array();
		$sql_user_to_copy = "SELECT count(usuario_id) as sum FROM tbl_permisos WHERE usuario_id='".$_POST["id_user_to_copy"]."'";
		$result_user_to_copy = $mysqli->query($sql_user_to_copy);
		$rows_user_to_copy=$result_user_to_copy->fetch_assoc();
		if ($rows_user_to_copy["sum"]>0){  
			$array_select["mensaje"]["usuario"]="El usuario ya tiene asignado permisos";	
		}else{
			$array_select["mensaje"]["usuario"]="Usuario Disponible!!!";			
		}
		echo json_encode($array_select);
	}
	/*
	if ($_POST["where"]=="sec_permisos_copiar_permisos_referencia") {
		$array_select=array();
		$sql_user_to_copy_reference = "SELECT count(usuario_id) as sum FROM tbl_permisos WHERE usuario_id='".$_POST["user_id_reference"]."'";
		$result_user_to_copy_reference = $mysqli->query($sql_user_to_copy_reference);
		$rows_user_to_copy_reference=$result_user_to_copy_reference->fetch_assoc();
		if ($rows_user_to_copy_reference["sum"]>0){  
			$array_select["mensaje"]["usuario"]="Usuario Disponible!!!";	
		}else{
			$array_select["mensaje"]["usuario"]="El usuario seleccionado no cuenta con permisos a copiar";			
		}
		echo json_encode($array_select);
	}
	*/
	if ($_POST["where"]=="sec_permisos_copiar_permisos_usuarios") {
		$delete_user_to = "DELETE FROM tbl_permisos WHERE usuario_id = '".$_POST["user_to"]."'";
		$result_usert_to=$mysqli->query($delete_user_to);
		$sql_user_since = "SELECT grupo_id,codigo_id,relacion_id,relacion_grupo_id,
		usuario_id,personal_id,menu_id,sistema_id,boton_id,boton_nombre,estado 
		FROM tbl_permisos WHERE usuario_id='".$_POST["user_since"]."'";
		$result_user_since = $mysqli->query($sql_user_since);
		while($row_user_since = $result_user_since->fetch_assoc()) {
			$insert_user_to = "INSERT INTO tbl_permisos 
			(grupo_id,codigo_id,relacion_id,relacion_grupo_id,usuario_id,personal_id,menu_id,sistema_id,boton_id,boton_nombre,estado) 
			VALUES('".$row_user_since["grupo_id"]."','".$row_user_since["codigo_id"]."','".$row_user_since["relacion_id"]."','".$row_user_since["relacion_grupo_id"]."','".$_POST["user_to"]."','".$row_user_since["personal_id"]."','".$row_user_since["menu_id"]."','".$row_user_since["sistema_id"]."','".$row_user_since["boton_id"]."','".$row_user_since["boton_nombre"]."','1')";
			$mysqli->query($insert_user_to);
		}
	}
	function validar_null_fields($field){
	  if(empty($field)){
	   $field = 0;
	  }else{
	   $field = $field;
	  }
	  return $field;
	}	

?>