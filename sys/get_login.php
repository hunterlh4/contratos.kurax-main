<?php
include("db_connect.php");
include("sys_login.php");
if(isset($_GET["check_login"])){
	if($login){
		echo $login["sesion_cookie"];
	}else{
		echo "no_login";
	}
}
?>