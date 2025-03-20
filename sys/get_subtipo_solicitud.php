<?php
include("db_connect.php");
include("sys_login.php");

if(isset($_POST["tipo_solicitud"])){
	$data=$_POST["tipo_solicitud"];
	$subtipo_solicitud = $mysqli->query("SELECT * FROM tbl_subtipo_solicitud where tipo_solicitud=".$data);
	$ret = array();	
	while($sel=$subtipo_solicitud->fetch_assoc()){
		$ret[]=array("id"=>$sel["id"],"descripcion"=>$sel["descripcion"]);
	}
	print_r(json_encode($ret));		
}
?>	