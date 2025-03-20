<?php
require('db_connect.php');
require('Uploader.php');
require("sys_login.php");

//sleep(2);
$return=array();
$upload_dir = "../files_bucket/";

$sql_insert = "INSERT INTO tbl_archivos (tabla,fecha) VALUES ('".$_POST["tabla"]."','".date("Y-m-d H:i:s")."')";
$mysqli->query($sql_insert);
$file_id = $mysqli->insert_id;

$uploader = new FileUpload('file');  
$uploader->newFileName = $file_id."_".$uploader->getFileName(); 
$result = $uploader->handleUpload($upload_dir);

if($result){
	$sql_update = "UPDATE tbl_archivos SET 
						tipo = 'repo',
						ext = '".$uploader->getExtension()."', 
						archivo = '".$uploader->getFileName()."', 
						size = '".$uploader->getFileSize()."', 
						fecha = '".date("Y-m-d H:i:s")."',
						estado = '1' 
						WHERE id = '".$file_id."'";
	$mysqli->query($sql_update);

	if($_POST["tabla"]=="tbl_transacciones_repositorio"){
		include("set_data.php");
		include("sys_transacciones.php");
		$repo_data=array();
		$repo_data=$_POST;
		$repo_data["file_id"]=$file_id;
		$repo_data["file_dir"]=$upload_dir;

		$return["import_csv_to_db"]=import_csv_to_db($repo_data);
	}
	$output = array("success" => true,
					"message" => "Success!",
					'return' => $return,
					'post' => $_POST,
					'files' => $_FILES);
}else{
	$output = array("success" => false,
					"error" => "Failure!",
					'return' => $return,
					'response' => $uploader->getErrorMsg(),
					'post' => $_POST,
					'files' => $_FILES);
}
print_r(json_encode($output));
?>