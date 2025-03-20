<?php
		include("db_connect.php");
		$array_menu_id=array();
		$query_menu_usuarios_botones="SELECT usuario_id,menu_id,boton_id,boton_nombre FROM tbl_permisos WHERE usuario_id='".$_POST["user_id"]."'";
		$result_menu_usuarios_botones = mysqli_query($mysqli,$query_menu_usuarios_botones);
		while ($row_menu_usuario_botones= mysqli_fetch_array($result_menu_usuarios_botones)) {
			$array_menu_id[$row_menu_usuario_botones["menu_id"]]=$row_menu_usuario_botones["menu_id"];
		}
		$query = "SELECT id,titulo,relacion_id FROM tbl_menu_sistemas ORDER BY COALESCE(relacion_id, '0')";
		$state=[];
		$result = mysqli_query($mysqli, $query);
		while($row = mysqli_fetch_array($result))
		{
			 $sub_data["id"] = $row["id"];
			 $sub_data["name"] = $row["titulo"];
			 $sub_data["text"] = $row["titulo"];
			 $sub_data["selectable"]=true;
			 $sub_data["selectedIcon"]="glyphicon glyphicon-ok";
			 if (array_key_exists($row["id"], $array_menu_id)) {
			 			$state["checked"]=true;

			 }else{
			 			$state["checked"]=false;	
			 }
			 $state["expanded"]=false;
			 $sub_data["state"] = $state;
			 $sub_data["parent_id"] = $row["relacion_id"];
			 $data[] = $sub_data;
		}
		foreach($data as $key => &$value)
		{
		 	$output[$value["id"]] = &$value;
		}
		foreach($data as $key => &$value)
		{
			if($value["parent_id"] && isset($output[$value["parent_id"]]))
			{
				$output[$value["parent_id"]]["nodes"][] = &$value;
			}
		}
		foreach($data as $key => &$value)
		{
			if($value["parent_id"] && isset($output[$value["parent_id"]]))
			{
				unset($data[$key]);
			}
		}
		echo json_encode($data);
?>