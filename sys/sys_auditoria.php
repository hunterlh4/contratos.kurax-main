<?php
//print_r($_COOKIE); exit();
include("global_config.php");
include("sys_cookies.php");
include("db_connect.php");
include("sys_login.php");
$return = array();
if(isset($_POST["opt"])){
	//print_r($login); exit();
	//exit();
	extract($_POST);
	if($opt=="auditoria_send"){
		auditoria_send($data);
	}
}

function auditoria_send($data){
	global $mysqli;
	global $return;
	global $date;
	global $login;
	global $sistema_id;
	//print_r($login); exit();

	$values = array();
	//array_merge($values,$data);
	$values = $data;
	$values["fecha_registro"]=$date;
	$values["login"]=json_encode($login);
	$values["usuario_id"]=(is_array($login)?(array_key_exists("id", $login)?$login["id"]:""):"");
	$values["ip"]=$_SERVER["REMOTE_ADDR"];
	// $values["geolocation"]="ninja";
	$values["estado"]=1;
	$values["sistema"]=$sistema_id;
	if(array_key_exists("data", $data)){
		$values["data"]=$mysqli->real_escape_string(json_encode($data["data"]));
	}

	$sql_insert = "INSERT INTO tbl_auditoria (".implode(",", array_keys($values)).") ";
	$sql_insert.= "VALUES";	
	$sql_insert.= "('";
	$sql_insert.= implode("','", $values);	
	$sql_insert.= "')";
	$mysqli->query($sql_insert);
	// echo $sql_insert;
	if($mysqli->error){
		// $return["ERROR_MYSQL"]=$mysqli->error;
		print_r($mysqli->error);
		// echo "\n";
		// echo $command;
		exit();
	}
	// $return["auditoria_send"]=$sql_insert;
	// $return["auditoria_send_data"]=$data;
	// print_r($values);

}

print_r(json_encode($return));
?>