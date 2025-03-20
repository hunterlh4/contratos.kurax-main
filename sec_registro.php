<?php
$this_menu = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' AND sub_sec_id ='".$sub_sec_id."' LIMIT 1")->fetch_assoc();
$menu_id = $this_menu["id"];

if(array_key_exists($menu_id,$usuario_permisos) && in_array("view", $usuario_permisos[$menu_id])){
	include("sec_registro_".$sub_sec_id.".php");
}
else echo "No tienes permisos para ver este recurso";
