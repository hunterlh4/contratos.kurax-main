<?php
	include("db_connect.php");	

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
	if ($_POST["where"]=="sec_permisos_get_menu_sistemas") {
		$sql_what = "id,grupo_id,sec_id,sub_sec_id,codigo_id,descripcion,relacion_id,relacion_grupo_id,sistema_id,estado,ord";
		if(array_key_exists("what", $_POST)){
			$sql_what = implode(",", $_POST["what"]);
		}
		$sql_command = "SELECT $sql_what FROM tbl_menu_sistemas";
		$sql_query = $mysqli->query($sql_command);
		while($itm=$sql_query->fetch_assoc()){
			$return["data"][]=$itm;
		}
		echo json_encode($return);		
	}
	if ($_POST["where"]=="sec_permisos_set_permisos") {
		if ($_POST["usuario"]!=-1) {
			$array_botones_id_nombres=array();	
			$menu_id=$_POST["menu_sistemas"];
			foreach($_POST["botones"] as $key => $btn_id){
				$sql_botones = "SELECT nombre FROM tbl_botones WHERE id= '".$btn_id."'";
				$result_botones = $mysqli->query($sql_botones);
				while($row_selected = $result_botones->fetch_assoc()) {
					$array_botones_id_nombres[$btn_id]=$row_selected["nombre"];
				}
			}
			$array_menu_sistemas = array();
			$array_menu_sistemas = $_POST["menu_sistemas"];
			$delete_permisos = "DELETE FROM tbl_permisos WHERE usuario_id='".$_POST["usuario"]."'";
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
	}
	if ($_POST["where"]=="sec_permisos_get_listbuttons_asigned") {

		//print $_POST['menu_id'];

		$array_selec = array();
		$result_final_selected = array();	
		$sql_selected = "SELECT boton_id,boton_nombre FROM tbl_permisos WHERE menu_id = '".$_POST['menu_id']."'";
		//print $sql_selected."\n";
		$result_selected = $mysqli->query($sql_selected);
		while($row_selected = $result_selected->fetch_assoc()) {
			$result_final_selected["selected"][$row_selected['boton_id']] = $row_selected;
		} 
		//print_r($result_final_selected);

		$sql_available = "SELECT boton,nombre FROM tbl_menu_sistemas_botones WHERE menu_id = '".$_POST['menu_id']."'";
		$result_available = $mysqli->query($sql_available);
		while($row_available = $result_available->fetch_assoc()) {
			//print_r($row_available["boton"]);
			if(array_key_exists($row_available["boton"], $result_final_selected)){
				print "selected";
				$array_select["selected"][$row_available["boton"]]=$row_available["nombre"];
				//print_r($array_select);
				$array_select["selected"][$row_available["boton"]]=$row_available["nombre"];
				print_r($array_select);						
			}else{
				$array_select["available"][$row_available["boton"]]=$row_available["nombre"];
				//print_r($array_select);
			}
		}

		//echo json_encode($array_select);
	}







	if ($_POST["where"]=="sec_permisos_get_user") {
		$result_user_final=array();
		$sql_user = "SELECT nombre,apellido_paterno,apellido_materno,usuario FROM tbl_usuarios WHERE id = '".$_POST['user_id']."'";	
		$result_user = $mysqli->query($sql_user);
		while($row_user = $result_user->fetch_assoc()) {
			$result_user_final["user"]=$row_user["nombre"]." ".$row_user["apellido_paterno"]." ".$row_user["apellido_materno"]." - ".$row_user["usuario"];
		} 
		echo json_encode($result_user_final);
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