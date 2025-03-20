<?php
//uploadHandler
require('db_connect.php');
require('Uploader.php');

$upload_dir = "../files_bucket/";

/*print_r($_POST);
print_r($_FILES);

exit();/**/

$sql_insert = "INSERT INTO tbl_archivos (tabla,item_id) VALUES ('".$_POST["tabla"]."','".$_POST["item_id"]."')";
$mysqli->query($sql_insert);
$file_id = $mysqli->insert_id;
$new_file_name = ($file_id."_".$_FILES["uploadfile"]["name"]);
$target_file = $upload_dir.$new_file_name;
$ext = pathinfo($target_file, PATHINFO_EXTENSION);
$uploaded = move_uploaded_file($_FILES["uploadfile"]["tmp_name"], $target_file);

if($uploaded){
	$sql_update = "UPDATE tbl_archivos SET 
										ext = '".$ext."', 
										archivo = '".$new_file_name."', 
										size = '".$_FILES["uploadfile"]["size"]."', 
										nombre = '".$_POST["nombre"]."', 
										descripcion = '".$_POST["descripcion"]."',
										fecha = '".date("Y-m-d H:i:s")."',
										estado = '1' WHERE id = '".$file_id."'";
	$mysqli->query($sql_update);
	echo json_encode(array(
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


exit();/**/

$sql_insert = "INSERT INTO tbl_archivos (tabla,item_id) VALUES ('".$_POST["tabla"]."','".$_POST["item_id"]."')";
$mysqli->query($sql_insert);
$file_id = $mysqli->insert_id;

//$new_file_name = $file_id

$uploader = new FileUpload('uploadFile');

$uploader->newFileName = $file_id."_".$uploader->getFileName(); 
$result = $uploader->handleUpload($upload_dir);

if($result){
	$sql_update = "UPDATE tbl_archivos SET 
										ext = '".$uploader->getExtension()."', 
										archivo = '".$uploader->getFileName()."', 
										size = '".$uploader->getFileSize()."', 
										nombre = '".$_POST["nombre"]."', 
										descripcion = '".$_POST["descripcion"]."',
										fecha = '".date("Y-m-d H:i:s")."',
										estado = '1' WHERE id = '".$file_id."'";
	$mysqli->query($sql_update);
	echo json_encode(array(
		'success' => true,
		'file' => $uploader->getFileName(),
		'files' => $_FILES
	));

}
else {
	$sql_delete = "DELETE FROM tbl_archivos WHERE id = '".$file_id."'";
	$mysqli->query($sql_delete);
	echo json_encode(array(
		'success' => false,
		'msg' => $uploader->getErrorMsg()
	));    
}
?>