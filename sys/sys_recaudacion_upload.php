<?php
require('db_connect.php');
require('Uploader.php');

$upload_dir = "../files_bucket/";

$sql_insert = "INSERT INTO tbl_archivos (tabla) VALUES ('".$_POST["tabla"]."')";
$mysqli->query($sql_insert);
$file_id = $mysqli->insert_id;
$new_file_name = ($file_id."_".$_FILES["uploadfile"]["name"]);
$target_file = $upload_dir.$new_file_name;
$ext = pathinfo($target_file, PATHINFO_EXTENSION);
$uploaded = move_uploaded_file($_FILES["uploadfile"]["tmp_name"], $target_file);

if($uploaded){
	chmod($upload_dir.$new_file_name, 0777);
	$sql_update = "UPDATE tbl_archivos SET 
					ext = '".$ext."', 
					archivo = '".$new_file_name."', 
					size = '".$_FILES["uploadfile"]["size"]."', 
					fecha = '".date("Y-m-d H:i:s")."',
					estado = '1' WHERE id = '".$file_id."'";
	$mysqli->query($sql_update);
	echo json_encode(array(
		'file_id' => $file_id,
		'success' => true,
		'file' => $new_file_name,
		'files' => $_FILES
	));
}else{
	$sql_delete = "DELETE FROM tbl_archivos WHERE id = '".$file_id."'";
	$mysqli->query($sql_delete);
	echo json_encode(array(
		'success' => false,
		'msg' => "horror!"
	));

}
?>