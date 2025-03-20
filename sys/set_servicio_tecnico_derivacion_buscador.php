<?php
// $return = array();
// $return["memory_init"]=memory_get_usage();
// $return["time_init"] = microtime(true);
// include("global_config.php");
// include("db_connect.php");
// include("sys_login.php");

$tecnico_select = isset($_POST["tecnico_select"]) ? urlencode(serialize($_POST["tecnico_select"])) : "0";

// $return["buscador"] = $tecnico_select;
header("Location: /?sec_id=servicio_tecnico_derivacion&sub_sec_id=&buscador={$tecnico_select}");
exit();
// $return["memory_end"]=memory_get_usage();
// $return["time_end"] = microtime(true);
// $return["memory_total"]=($return["memory_end"]-$return["memory_init"]);
// $return["time_total"]=($return["time_end"]-$return["time_init"]);
// print_r(json_encode($return));
?>