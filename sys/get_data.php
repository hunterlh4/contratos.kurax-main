<?php
$return = array();
$return["memory_init"]=memory_get_usage();
$return["time_init"] = microtime(true);
include("global_config.php");
include("db_connect.php");
include("set_clientes.php");
include("sys_login.php");
if(isset($_POST["opt"])){
	extract($_POST);
	if($opt=="usuarios_permisos_get_data_menu_sistema"){
		include("sys_usuarios.php");
		usuarios_permisos_get_data_menu_sistema($_POST);
	}
	if($opt=="usuarios_permisos_get_data_local"){
		include("sys_usuarios.php");
		usuarios_permisos_get_data_local($_POST);
	}
	if($opt=="usuarios_permisos_asignar_locales_para_usuarios"){
		include("sys_usuarios.php");
		usuarios_permisos_asignar_locales_para_usuarios($_POST);
	}
	if($opt=="usuarios_permisos_save_data_local_usuarios"){
		include("sys_usuarios.php");
		usuarios_permisos_save_locales_usuarios($_POST);
	}		
	if ($opt=="sec_permisos_get_usuarios") {
		include("sys_usuarios.php");	
		usuarios_permisos_get_usuarios($_POST);	
	}
	if ($opt=="sec_permisos_get_sistemas") {
		include("sys_usuarios.php");	
		usuarios_permisos_get_sistemas();	
	}	
	if ($opt=="sec_permisos_get_usuarios_copiar") {
		include("sys_usuarios.php");	
		usuarios_permisos_get_usuarios_copiar();	
	}	
	if ($opt=="sec_permisos_get_user") {
		include("sys_usuarios.php");
		usuarios_permisos_get_usuario($_POST);
	}
	if ($opt=="sec_permisos_copiar_permisos_referencia") {
		include("sys_usuarios.php");
		usuarios_permisos_copiar_usuario_referencia($_POST);
	}
	if ($opt=="sec_permisos_copiar_permisos") {
		include("sys_usuarios.php");
		usuarios_permisos_copiar_usuario_objetivo($_POST);
	}
	if ($opt=="get_nombre_sistema") {
		include("get_nombre_sistema.php");
		get_nombre_sistema($_POST);

	}	
}


$return["memory_end"]=memory_get_usage();
$return["time_end"] = microtime(true);
$return["memory_total"]=($return["memory_end"]-$return["memory_init"]);
$return["time_total"]=($return["time_end"]-$return["time_init"]);
print_r(json_encode($return));
?>