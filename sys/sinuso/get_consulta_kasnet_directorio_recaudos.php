<?php
include("db_connect.php");
include("sys_login.php");
require_once('/var/www/html/phpexcel/classes/PHPExcel.php');

if(isset($_FILES["fileKdrUpload"])){
	$file = $_FILES["fileKdrUpload"];
	
	$valid_extensions = array('xls', 'xlsx');
	
	$file = $_FILES['fileKdrUpload']['name'];
	$tmp = $_FILES['fileKdrUpload']['tmp_name'];
	$size = $_FILES['fileKdrUpload']['size'];
	$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
	
	$filename = strtolower("kdrUpload_".date("YmdHis").".".$ext);
	if(in_array($ext, $valid_extensions)) {
		$filepath = '/var/www/html/files_bucket/kasnet'.$filename;
		move_uploaded_file($tmp,$filepath);
	}
    $query = "TRUNCATE TABLE tbl_consultas_kasnet_recaudos";
    $mysqli->query($query);

	$query = "
		INSERT INTO tbl_consultas_kasnet_recaudos(
			codigo,
			nombre_instituicion,
		    categoria,
		    dato_ingreso,
		    comision_usuario,
            canal,
		    departamento,
		    provincia,
		    distrito
		) VALUES 
	";

	libxml_use_internal_errors(TRUE);
	$excelReader = PHPExcel_IOFactory::createReaderForFile($filepath);
	$excelObj = $excelReader->load($filepath);
	$worksheet = $excelObj->getSheet(0);
	$lastRow = $worksheet->getHighestRow();
	
	for($row = 2; $row <= $lastRow; $row++){

		/*$comision_usuario = is_numeric($mysqli->real_escape_string($worksheet->getCell('F'.$row)->getValue())) ? $mysqli->real_escape_string($worksheet->getCell('F'.$row)->getValue()) : 0;
		$query .= '(
			 '.$mysqli->real_escape_string($worksheet->getCell('A'.$row)->getValue()).',
			"'.$mysqli->real_escape_string($worksheet->getCell('B'.$row)->getValue()).'",
			"'.$mysqli->real_escape_string($worksheet->getCell('C'.$row)->getValue()).'",
			"'.$mysqli->real_escape_string($worksheet->getCell('D'.$row)->getValue()).'",
			"'.$mysqli->real_escape_string($worksheet->getCell('E'.$row)->getValue()).'",
			 '.$comision_usuario.',
			"'.$mysqli->real_escape_string($worksheet->getCell('G'.$row)->getValue()).'",
			"'.$mysqli->real_escape_string($worksheet->getCell('H'.$row)->getValue()).'",
			"'.$mysqli->real_escape_string($worksheet->getCell('I'.$row)->getValue()).'"
		),';*/

        $comision_usuario = is_numeric($mysqli->real_escape_string($worksheet->getCell('E'.$row)->getValue())) ? $mysqli->real_escape_string($worksheet->getCell('E'.$row)->getValue()) : 0;
        $query .= '(
			 '.$mysqli->real_escape_string($worksheet->getCell('A'.$row)->getValue()).',
			"'.$mysqli->real_escape_string($worksheet->getCell('B'.$row)->getValue()).'",
			"'.$mysqli->real_escape_string($worksheet->getCell('C'.$row)->getValue()).'",
			"'.$mysqli->real_escape_string($worksheet->getCell('D'.$row)->getValue()).'",
			 '.$comision_usuario.',
			"'.$mysqli->real_escape_string($worksheet->getCell('F'.$row)->getValue()).'",
			"'.$mysqli->real_escape_string($worksheet->getCell('G'.$row)->getValue()).'",
			"'.$mysqli->real_escape_string($worksheet->getCell('H'.$row)->getValue()).'",
			"'.$mysqli->real_escape_string($worksheet->getCell('I'.$row)->getValue()).'"
		),';
	}
	$query = substr($query, 0, -1);
	$mysqli->query($query);

	if($mysqli->error) echo $mysqli->error;
	unlink($filepath);
}