<?php
$return = array();
$return["memory_init"]=memory_get_usage();
$return["time_init"] = microtime(true);
include("global_config.php");
include("db_connect.php");
include("sys_login.php");

if(isset($_POST["sec_adm_login_ip_whitelist_save"])){
	$data = $_POST["sec_adm_login_ip_whitelist_save"];
	$ip = $data["ip"];
	$id = $data["id"];
	$descripcion = $data["descripcion"];
	$estado = $data["estado"];
	$grupo_id = $data["grupo_id"];
	if($ip == "" || $descripcion ==""){
		$return["error"] = true;
		$return["error_msg"] = "Ingrese ip y descripción";		
		print_r(json_encode($return));
		return;
	}
	if(!filter_var($ip, FILTER_VALIDATE_IP))
	{
		$return["error"] = true;
		$return["error_msg"] = "IP no válida , '" .$ip."'";
		$return["error_focus"] = "ip";
		print_r(json_encode($return));
		return;
	}
	$where_id = $id != "new" ? "AND id != '$id' " : "";
	//$where_grupo = $grupo_id == 0 ? "" : " AND grupo_id = ".$grupo_id ;
	$where_grupo = " AND grupo_id = ".$grupo_id ;
	$query_exist = "SELECT ip FROM tbl_login_ip_whitelist WHERE ip = '$ip' ". $where_id . $where_grupo;
	$exists = $mysqli->query($query_exist)->fetch_assoc();
	if($exists){
		$return["error"] = true;
		$return["sql"] = "SELECT ip FROM tbl_login_ip_whitelist WHERE ip = '$ip' ". $where_id . $where_grupo;
		$return["error_msg"] = "IP " . $ip . " ya registrada " .($grupo_id ? "para ese grupo" : "");
		$return["error_focus"] = "ip";
	}
	else{
		if($data["id"] == "new"){
			$insert_command = "INSERT INTO tbl_login_ip_whitelist (ip,descripcion,estado,grupo_id , created_at)";
			$insert_command.= "VALUES ('".$ip."','".$descripcion."','" .$estado. "','".$grupo_id."',now())";
			$mysqli->query($insert_command);
			if($mysqli->error){
				print_r($mysqli->error);
				echo "\n";
				echo $insert_command;
				exit();
			}
			$return["id"] = $mysqli->insert_id;

			if($grupo_id)
			{
				$cmd = "UPDATE tbl_usuarios 
					SET ip_restrict = ".$estado."
					WHERE grupo_id = ".$grupo_id;
				$mysqli->query($cmd);
				if($mysqli->error){
					print_r($mysqli->error);
					echo "\n";
					echo $cmd;
					exit();
				}
			}
			$return["mensaje"] = "Registro Insertado.";

		}else{
			/*$comando_select = "SELECT
				id,
				grupo_id
				FROM tbl_login_ip_whitelist
				WHERE id = ".$data["id"];
			$registro = $mysqli->query($comando_select)->fetch_assoc();
			$current_grupo = $registro["grupo_id"];
			if( $current_grupo != $grupo_id)
			{
				$cmd = "UPDATE tbl_usuarios 
					SET ip_restrict = 0
					WHERE grupo_id = ".$current_grupo;
				$mysqli->query($cmd);
			}*/
			$udpate_command = "UPDATE tbl_login_ip_whitelist 
				SET ip = '".$ip."'
				, descripcion = '".$descripcion."'
				, estado = '".$estado."'
				, grupo_id = '".$grupo_id."'
				,updated_at = now() 
				WHERE id = '".$data["id"]."'";
			$mysqli->query($udpate_command);

			/*if($grupo_id)
			{
				$cmd = "UPDATE tbl_usuarios 
					SET ip_restrict = ".$estado."
					WHERE grupo_id = ".$grupo_id;
				$mysqli->query($cmd);
				if($mysqli->error){
					print_r($mysqli->error);
					echo "\n";
					echo $cmd;
					exit();
				}
			}*/
			$return["mensaje"]= "Registro Actualizado.";
		}
	}
}

if(isset($_POST["desactivar_listablanca_grupo"])){
	$data = $_POST["desactivar_listablanca_grupo"];
	//$grupo_id = $data["grupo_id"];
	$grupo_id = $data["grupo_id"] == 0 ? " 0 OR grupo_id IS NULL" : $data["grupo_id"];	
	$grupo_nombre = $data["grupo_nombre"];
	$udpate_command = "UPDATE tbl_usuarios
		SET ip_restrict = 0
		WHERE grupo_id = ".$grupo_id;
	$mysqli->query($udpate_command);
	$return["mensaje"] = "Grupo : ".$grupo_nombre . ".";
}

if(isset($_POST["sec_adm_login_ip_whitelist_estado"])){
	$data=$_POST["sec_adm_login_ip_whitelist_estado"];
	$id = $data["id"];
	$estado = $data["estado"];

	$comando_select = "SELECT
						id,
						grupo_id
						FROM tbl_login_ip_whitelist
						WHERE id = ".$id;
	$registro = $mysqli->query($comando_select)->fetch_assoc();
	$grupo_id = $registro["grupo_id"];

	$udpate_command = "
			UPDATE tbl_login_ip_whitelist
			SET updated_at = now(),
			    estado = '" .$estado. "'
			WHERE id = '" . $id. "'";
	$mysqli->query($udpate_command);
	/*if($grupo_id)
	{
		$cmd = "UPDATE tbl_usuarios 
			SET ip_restrict = ".$estado."
			WHERE grupo_id = ".$grupo_id;
		$mysqli->query($cmd);
		if($mysqli->error){
			print_r($mysqli->error);
			echo "\n";
			echo $cmd;
			exit();
		}
	}*/
	$return["mensaje"] = "Estado Actualizado.";
}

if(isset($_POST["sec_adm_login_ip_whitelist_list"])){
	$data = $_POST["sec_adm_login_ip_whitelist_list"];
	
	$comando_select = "SELECT 
						 liw.id
						,liw.ip
						,liw.descripcion
						,liw.estado
						,liw.grupo_id
						,ug.nombre AS grupo_nombre
						FROM tbl_login_ip_whitelist liw
						LEFT JOIN tbl_usuarios_grupos ug on ug.id = liw.grupo_id
						ORDER BY id DESC";
	$query = $mysqli->query($comando_select);
	$lista = [];
	while($d=$query->fetch_assoc()){
		$lista[] = $d;
	}
	$return["lista"]=$lista;
	$return["mensaje"]="Lista realizada correctamente.";
}

$return["memory_end"]=memory_get_usage();
$return["time_end"] = microtime(true);
$return["memory_total"]=($return["memory_end"]-$return["memory_init"]);
$return["time_total"]=($return["time_end"]-$return["time_init"]);
print_r(json_encode($return));
?>