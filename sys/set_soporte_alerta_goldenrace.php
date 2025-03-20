<?php
include("db_connect.php");
include("sys_login.php");

$this_menu = $mysqli->query("
	SELECT id 
	FROM tbl_menu_sistemas 
	WHERE sec_id = 'soporte' 
	AND sub_sec_id = 'alerta_goldenrace' 
	LIMIT 1
")->fetch_assoc();

$menu_id = $this_menu["id"];

if(isset($_POST["set_switch"])){
    if(!array_key_exists($menu_id,$usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])){
        die(json_encode(['code' => 403, 'message' => 'No Autorizado. No tienes permisos suficientes']));
    }

    $data = $_POST["set_switch"];
    $id = $data["id"];
    $config_param = $data["config_param"];
    $update_command = "UPDATE tbl_local_config 
						SET config_param = '".($config_param ? 0 : 1)."',
						updated_at = NOW() 
						WHERE local_id = '$id' 
						AND config_id = 'alerta_gr_turnover'";
    $mysqli->query($update_command);
    die(json_encode(['code' => 200, 'message' => "Alerta Apagada"]));
}