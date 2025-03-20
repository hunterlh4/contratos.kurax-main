<?php
	function usuarios_permisos_get_data_menu_sistema($post){
		global $mysqli,$return;		
		$array_menu_id=array();
		$query_menu_usuarios_botones="SELECT usuario_id,menu_id,boton_id,boton_nombre FROM tbl_permisos 
		WHERE usuario_id='".$post["user_id"]."' AND estado = '1'";
		$result_menu_usuarios_botones = $mysqli->query($query_menu_usuarios_botones);
		while ($row_menu_usuario_botones= $result_menu_usuarios_botones->fetch_assoc()) {
			$array_menu_id[$row_menu_usuario_botones["menu_id"]]=$row_menu_usuario_botones["menu_id"];
		}
		$query_menu_sistemas = "SELECT id,titulo,relacion_id FROM tbl_menu_sistemas ORDER BY COALESCE(relacion_id, '0')";
		$state=[];
		$result = $mysqli->query($query_menu_sistemas);
		while($row_menu_sistemas = $result->fetch_assoc())
		{
			 $sub_data["id"] = $row_menu_sistemas["id"];
			 $sub_data["name"] = $row_menu_sistemas["titulo"];
			 $sub_data["text"] = $row_menu_sistemas["titulo"];
			 $sub_data["selectable"]=true;
			 $sub_data["selectedIcon"]="glyphicon glyphicon-ok";
			 if (array_key_exists($row_menu_sistemas["id"], $array_menu_id)) {
			 		$state["checked"]=true;
			 }else{
			 		$state["checked"]=false;	
			 }
			 $state["expanded"]=true;
			 $sub_data["state"] = $state;
			 $sub_data["parent_id"] = $row_menu_sistemas["relacion_id"];
			 $return[] = $sub_data;
		}
		foreach($return as $key => &$value)
		{
		 	$output[$value["id"]] = &$value;
		}
		foreach($return as $key => &$value)
		{
			if($value["parent_id"] && isset($output[$value["parent_id"]]))
			{
				$output[$value["parent_id"]]["nodes"][] = &$value;
			}
		}
		foreach($return as $key => &$value)
		{
			if($value["parent_id"] && isset($output[$value["parent_id"]]))
			{
				unset($return[$key]);
			}
		}
	}

	function usuarios_permisos_get_data_local($post){
		global $mysqli,$return;	

		$filtro = $post["filtro"];
		$sql_what = "id,nombre,IF(ISNULL(red_id),'4',red_id) as red_id ";
		if(array_key_exists("what", $post)){
			$sql_what = implode(",", $post["what"]);
		}
		$array_select = array();
		$array_locales_seleccionados = array();
		$array_locales_activos_x_red = array();
		$query_locales ="SELECT local_id,usuario_id FROM tbl_usuarios_locales 
		WHERE usuario_id  ='".$filtro["usuario"]."' AND estado = '1'";
		$result_locales = $mysqli->query($query_locales);
		while($row_locales_asignados = $result_locales->fetch_assoc()) {
			$array_locales_seleccionados[$row_locales_asignados["local_id"]]=$row_locales_asignados["local_id"];
		}
		$array_lista_locales_disponibles = array();
		// $array_lista_red_x_locales = array();
		$sql_command = "SELECT id,nombre,IF(ISNULL(red_id),'4',red_id) as red_id FROM tbl_locales ORDER BY nombre ";
		$sql_query = $mysqli->query($sql_command);
		$row_cnt = $sql_query->num_rows;
		$data = array();		
		while($itm=$sql_query->fetch_assoc()){
			$array_lista_locales_disponibles[$itm["id"]] =  $itm["id"];
			// $array_lista_red_x_locales[$itm["red_id"]][$itm["id"]] = $itm["nombre"];
			$data["text"]=$itm["nombre"];
			$data["id"] = $itm["id"];
			

			if (array_key_exists($itm["id"], $array_locales_seleccionados)) {
				$array_select[$itm["red_id"]][]= array("nombre"=>$itm["nombre"],"id"=>$itm["id"],"checked"=>"checked","estado"=>"1");
				$array_locales_activos_x_red = count($itm["id"]);
			}else{
				$array_select[$itm["red_id"]][]= array("nombre"=>$itm["nombre"],"id"=>$itm["id"],"checked"=>"unchecked","estado"=>"0");	
			}	
		}	

			$return["array_disponibles"][] = $array_lista_locales_disponibles;
			$return["array_seleccionados"][] = $array_locales_seleccionados;
			$return["id_usuario"][] = $filtro["usuario"];

			$return["count"] = count($array_select);
			$return["data"] = $array_select;
			$return["query_locales"] = $query_locales;

			$return["data"]= $array_select;
	}
	function usuarios_permisos_set_permisos($post){
		global $mysqli,$return;

		$return["data"]["user_id"]= $post["usuario"];
	}
	function usuarios_permisos_get_usuarios($post){
		global $mysqli,$return;
		$filtro = $post["filtro"];

		$array_consolidado = array();
		$usuarios_cn_personal = array();
		$sql_usuarios_personal_command = "SELECT u.id as id ,u.usuario as usuario,
		p.nombre as nombre,p.apellido_paterno as apellido_paterno,		 
		p.apellido_materno as apellido_materno,s.id as sistema_id,s.nombre as sistema 
		FROM tbl_usuarios u 
		INNER JOIN tbl_personal_apt p
		ON u.personal_id = p.id 
		INNER JOIN tbl_sistemas s
		ON u.sistema_id = s.id WHERE u.sistema_id ='".$filtro["id_sistema"]."' AND u.estado ='1'";
		$sql_query_usuarios_personal = $mysqli->query($sql_usuarios_personal_command);
		while($itm=$sql_query_usuarios_personal->fetch_assoc()){
			$usuarios_cn_personal[$itm["id"]] = "[".$itm["id"]."] - ".$itm["usuario"]." - ".$itm["nombre"]." ".$itm["apellido_paterno"]." ".$itm["apellido_materno"];

		}

		$usuarios_sn_personal = array();
		$sql_command = "SELECT u.id as id,u.usuario as usuario,
		s.id as sistema_id,s.nombre as sistema 
		FROM tbl_usuarios u
		INNER JOIN tbl_sistemas s 
		ON u.sistema_id = s.id WHERE u.sistema_id ='".$filtro["id_sistema"]."' AND u.estado ='1'";
		$sql_query = $mysqli->query($sql_command);
		while($itm=$sql_query->fetch_assoc()){
			if (array_key_exists($itm["id"],$usuarios_cn_personal)) {
				$return["data"][$itm["id"]] = $usuarios_cn_personal[$itm["id"]];
			}else{
				$return["data"][$itm["id"]] = "[".$itm["id"]."] - ".$itm["usuario"];
			}			
		}
	}
	function usuarios_permisos_get_sistemas(){
		global $mysqli,$return;
		$sql_what = "id,nombre";
		if(array_key_exists("what", $_POST)){
			$sql_what = implode(",", $_POST["what"]);
		}
		$sql_command = "SELECT $sql_what FROM tbl_sistemas";
		$sql_query = $mysqli->query($sql_command);
		while($itm=$sql_query->fetch_assoc()){
			$return["data"][]=$itm;
		}
	}	
	function usuarios_permisos_get_usuarios_copiar(){
		global $mysqli,$return;

		$array_consolidado = array();
		$usuarios_cn_personal = array();
		$sql_usuarios_personal_command = "SELECT u.id as id ,u.usuario as usuario,
		p.nombre as nombre,p.apellido_paterno as apellido_paterno,		 
		p.apellido_materno as apellido_materno,s.id as sistema_id,s.nombre as sistema 
		FROM tbl_usuarios u 
		INNER JOIN tbl_personal_apt p
		ON u.personal_id = p.id 
		INNER JOIN tbl_sistemas s
		ON u.sistema_id = s.id WHERE u.estado = '1'";
		$sql_query_usuarios_personal = $mysqli->query($sql_usuarios_personal_command);
		while($itm=$sql_query_usuarios_personal->fetch_assoc()){
			$usuarios_cn_personal[$itm["id"]] = "[".$itm["id"]."] - ".$itm["usuario"]." - ".$itm["nombre"]." ".$itm["apellido_paterno"]." ".$itm["apellido_materno"]." - [".$itm["sistema_id"]."] ".$itm["sistema"];

		}

		$usuarios_sn_personal = array();
		$sql_command = "SELECT u.id as id,u.usuario as usuario,
		s.id as sistema_id,s.nombre as sistema 
		FROM tbl_usuarios u
		INNER JOIN tbl_sistemas s 
		ON u.sistema_id = s.id WHERE u.estado = '1'";
		$sql_query = $mysqli->query($sql_command);
		while($itm=$sql_query->fetch_assoc()){
			if (array_key_exists($itm["id"],$usuarios_cn_personal)) {
				$return["data"][$itm["id"]] = $usuarios_cn_personal[$itm["id"]];
			}else{
				$return["data"][$itm["id"]] = "[".$itm["id"]."] - ".$itm["usuario"]." - [".$itm["sistema_id"]."] ".$itm["sistema"];
			}			
		}
	}	
	function usuarios_permisos_get_usuario($post){
		global $mysqli,$return;

		$array_consolidado = array();
		$usuarios_cn_personal = array();
		$sql_usuarios_personal_command = "SELECT u.id as id ,u.usuario as usuario,
		p.nombre as nombre,p.apellido_paterno as apellido_paterno,		 
		p.apellido_materno as apellido_materno,s.id as sistema_id,s.nombre as sistema 
		FROM tbl_usuarios u 
		INNER JOIN tbl_personal_apt p
		ON u.personal_id = p.id 
		INNER JOIN tbl_sistemas s
		ON u.sistema_id = s.id WHERE u.id ='".$post['user_id']."' AND u.estado ='1'";
		$return["sql_usuarios_personal_command"] = $sql_usuarios_personal_command;
		$sql_query_usuarios_personal = $mysqli->query($sql_usuarios_personal_command);
		while($itm=$sql_query_usuarios_personal->fetch_assoc()){
			$usuarios_cn_personal[$itm["id"]] = "[".$itm["id"]."] - ".$itm["usuario"]." - ".$itm["nombre"]." ".$itm["apellido_paterno"]." ".$itm["apellido_materno"]." - [".$itm["sistema_id"]."] ".$itm["sistema"];
		}

		$usuarios_sn_personal = array();
		$sql_command = "SELECT u.id as id,u.usuario as usuario,
		s.id as sistema_id,s.nombre as sistema 
		FROM tbl_usuarios u
		INNER JOIN tbl_sistemas s 
		ON u.sistema_id = s.id WHERE u.id ='".$post['user_id']."' AND u.estado ='1'";
		$return["sql_command"] = $sql_command;
		$sql_query = $mysqli->query($sql_command);
		while($itm=$sql_query->fetch_assoc()){
			if (array_key_exists($itm["id"],$usuarios_cn_personal)) {
				$return["data"][$itm["id"]] = $usuarios_cn_personal[$itm["id"]];
			}else{
				$return["data"][$itm["id"]] = "[".$itm["id"]."] - ".$itm["usuario"]." - [".$itm["sistema_id"]."] ".$itm["sistema"];
			}			
		}
	}
	function usuarios_permisos_copiar_usuario_referencia($post){
		global $mysqli,$return;
		$array_select=array();
		$sql_user_to_copy_reference = "SELECT count(usuario_id) as sum FROM tbl_permisos WHERE usuario_id='".$post["user_id_reference"]."'";
		$result_user_to_copy_reference = $mysqli->query($sql_user_to_copy_reference);
		$rows_user_to_copy_reference=$result_user_to_copy_reference->fetch_assoc();
		if ($rows_user_to_copy_reference["sum"]>0){  
			$return["mensaje"]["usuario"]="Usuario Disponible!!!";	
		}else{
			$return["mensaje"]["usuario"]="El usuario seleccionado no cuenta con permisos a copiar";			
		}
	}
	function usuarios_permisos_copiar_usuario_objetivo($post){
		global $mysqli,$return;
		$array_select=array();
		$sql_user_to_copy = "SELECT count(usuario_id) as sum FROM tbl_permisos WHERE usuario_id='".$post["id_user_to_copy"]."'";
		$result_user_to_copy = $mysqli->query($sql_user_to_copy);
		$rows_user_to_copy=$result_user_to_copy->fetch_assoc();
		if ($rows_user_to_copy["sum"]>0){  
			$return["mensaje"]["usuario"]="El usuario ya tiene asignado permisos";	
		}else{
			$return["mensaje"]["usuario"]="Usuario Disponible!!!";			
		}
	}

	function usuarios_permisos_copiar_permisos_usuarios_local_x_red($post){
		global $mysqli,$return;
		$return["post"][] = $post;

		//locales x red 
		$sql_delete_user_to_l_x_r = "DELETE FROM tbl_usuarios_locales WHERE usuario_id = '".$post["user_to"]."'";
		$sql_result_usert_to_l_x_r = $mysqli->query($sql_delete_user_to_l_x_r);
		$return["delete_user_to_locales_x_red"][] = $sql_delete_user_to_l_x_r;	

		$sql_user_since_l_x_r = "SELECT local_id,estado FROM tbl_usuarios_locales WHERE usuario_id='".$post["user_from"]."' and estado = '1'";
		$sql_result_user_since_l_x_r = $mysqli->query($sql_user_since_l_x_r);
		$return["sql_user_since_l_x_r"][] = $sql_user_since_l_x_r;

		while($row_user_since_l_x_r = $sql_result_user_since_l_x_r->fetch_assoc()) {
			$sql_insert_user_to_l_x_r = "INSERT INTO tbl_usuarios_locales 
			(usuario_id,local_id,estado) VALUES('".$post["user_to"] ."','".$row_user_since_l_x_r["local_id"]."','1')";
			$mysqli->query($sql_insert_user_to_l_x_r);
			$return["insert_user_to_l_x_r"][] = $sql_insert_user_to_l_x_r;			
		}	
	}
	function usuarios_permisos_copiar_permisos_usuarios_menus_sub_menu($post){
		global $mysqli,$return;
		$return["post"][] = $post;				

		//menu sub menus 
		$sql_delete_user_to = "DELETE FROM tbl_permisos WHERE usuario_id = '".$post["user_to"]."'";
		$sql_result_usert_to=$mysqli->query($sql_delete_user_to);
		$return["delete_user_to_menu_sub_menus"][] = $sql_delete_user_to;

		$sql_user_since = "SELECT grupo_id,codigo_id,relacion_id,relacion_grupo_id,
		personal_id,menu_id,sistema_id,boton_id,boton_nombre
		FROM tbl_permisos WHERE usuario_id='".$post["user_from"]."' and estado = '1'";
		$result_user_since = $mysqli->query($sql_user_since);
			if($mysqli->error){
				print_r($mysqli->error);
				echo "\n";
				echo $sql_user_since;
				exit();
			}		

		$return["sql_user_since_menu_sub_menus"][] = $sql_user_since;		

		while($row_user_since = $result_user_since->fetch_assoc()) {
			$insert_user_to = "INSERT INTO tbl_permisos 
			(grupo_id,codigo_id,relacion_id,relacion_grupo_id,usuario_id,personal_id,menu_id,sistema_id,boton_id,boton_nombre,estado) 
			VALUES('".validar_null_fields($row_user_since["grupo_id"])."','".validar_null_fields($row_user_since["codigo_id"])."','".validar_null_fields($row_user_since["relacion_id"])."','".validar_null_fields($row_user_since["relacion_grupo_id"])."','".validar_null_fields($post["user_to"])."','".validar_null_fields($row_user_since["personal_id"])."','".validar_null_fields($row_user_since["menu_id"])."','".validar_null_fields($row_user_since["sistema_id"])."','".validar_null_fields($row_user_since["boton_id"])."','".$row_user_since["boton_nombre"]."','1')";
			$mysqli->query($insert_user_to);
			if($mysqli->error){
				print_r($mysqli->error);
				echo "\n";
				echo $insert_user_to;
				exit();
			}			
			$return["insert_user_to_menu_sub_menus"][] = $insert_user_to;			
		}		
	}

	function validar_null_fields($field){
		if(empty($field)){$field = 0;
		}else{$field = $field;}
		return $field;
	}	
?>