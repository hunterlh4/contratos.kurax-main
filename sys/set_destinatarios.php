<?php
$return = array();
$return["memory_init"]=memory_get_usage();
$return["time_init"] = microtime(true);
include("global_config.php");
include("db_connect.php");
include("sys_login.php");


if(isset($_POST["sec_destinatarios_save"])){
	$data=$_POST["sec_destinatarios_save"];
	$exists = $mysqli->query("SELECT id FROM tbl_destinatario WHERE correo = '".$data["correo"]."'")->fetch_assoc();
	// echo $data["estado"];
	// exit;
	// if($data["estado"]=="on"){
	// 	$data["estado"]=0;
	// }
	// else{
	// 	$data["estado"]=1;
	// }

	if($data["id"]=="new"){
		if($exists){
			$return["error"]="exists";
			$return["error_msg"]="El Correo ya existe!";
			$return["error_focus"]="correo";	
		}else{
			$insert_command = "INSERT INTO tbl_destinatario (nombre,correo,estado)";
			$insert_command.= "VALUES ('".$data["nombre"]."','".$data["correo"]."','".$data["estado"]."')";
			$mysqli->query($insert_command);
			if($mysqli->error){
				print_r($mysqli->error);
				echo "\n";
				echo $insert_command;
				exit();
			}
			$return["id"] = $mysqli->insert_id;
			// $return["usuario_id"] = $mysqli->insert_id;
		}
	}else{
		$save  = true;
		if($exists){
			if($exists["id"]!=$data["id"]){
				$save=false;
				$return["error"]="exists";
				$return["error_msg"]="El Correo ya existe!";
				$return["error_focus"]="correo";	
			}
		}else{
		}
		if($save){
			$udpate_command = "UPDATE tbl_destinatario SET nombre = '".$data["nombre"]."', correo = '".$data["correo"]."', estado = '".$data["estado"]."' WHERE id = '".$data["id"]."'";
			$mysqli->query($udpate_command);
		}
	}
}


$return["memory_end"]=memory_get_usage();
$return["time_end"] = microtime(true);
$return["memory_total"]=($return["memory_end"]-$return["memory_init"]);
$return["time_total"]=($return["time_end"]-$return["time_init"]);
print_r(json_encode($return));
?>