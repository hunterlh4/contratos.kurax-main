<?php
$return = array();
$return["memory_init"]=memory_get_usage();
$return["time_init"] = microtime(true);
include("global_config.php");
include("db_connect.php");
include("sys_login.php");
$data = json_decode(file_get_contents('php://input'), true);

if(isset($data["opt"])){
    $opt=$data["opt"];
    if ($opt=="set_locales_usuarios") {
        $filtro =$data["filtro"];
        $update_user_to_local = "UPDATE tbl_usuarios_locales SET estado = '0' 
		WHERE usuario_id = '".$filtro["usuario_id"]."' ";
        $return["data"]["query_update_tbl_usuarios_locales"] = $update_user_to_local;
        $result_usert_to_local =$mysqli->query($update_user_to_local);

        if (!empty($filtro["locales"])) {
            for ($i=0; $i < count($filtro["locales"]); $i++) {
                $return["registros_no_existentes"][] = [$filtro["locales"][$i][0],$filtro["locales"][$i][1]];
                $insert_local_to_user = "INSERT INTO tbl_usuarios_locales(usuario_id,local_id,estado) 
				VALUES('".$filtro["usuario_id"]."','".$filtro["locales"][$i][0]."','1')";
                $return["data"]["query_insert_tbl_usuarios_locales"] = $insert_local_to_user;
                $mysqli->query($insert_local_to_user);
            }
        }
    }
}

$return["memory_end"]=memory_get_usage();
$return["time_end"] = microtime(true);
$return["memory_total"]=($return["memory_end"]-$return["memory_init"]);
$return["time_total"]=($return["time_end"]-$return["time_init"]);
print_r(json_encode($return));
?>