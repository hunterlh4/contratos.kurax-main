<?php

include("../sys/db_connect.php");
include("../sys/sys_login.php");
date_default_timezone_set("America/Lima");

$post = array(
	"local_id" => $_POST["local_id"],
	"fecha_inicio" => $_POST["fecha_inicio"],
	"fecha_fin" => $_POST["fecha_fin"]
);
//$whereId = ($post['local_id'] != 'all') ? "AND l.id =".$post['local_id'] : "";
$locales_command = "";
if($login["usuario_locales"]){
	$locales_command = " AND l.id IN (".implode(",", $login["usuario_locales"]).")";
}
$whereId = ($post['local_id'] != 'all') ? "AND l.id =".$post['local_id'] : $locales_command;

$query = "	SELECT
				IFNULL(max(l.cc_id),l.id) as cc_id, 
				MAX(l.nombre) as nombre,
				z.nombre as nombre_zona,
				DATE_FORMAT(c.fecha_operacion, '%Y') AS year,
				DATE_FORMAT(c.fecha_operacion, '%m') AS month,
				DATE_FORMAT(c.fecha_operacion, '%d') AS day,
				c.turno_id,
				IFNULL(MAX(cdf11.valor),0) - IFNULL(MAX(cdf10.valor),0) AS faltantes,
				GROUP_CONCAT(DISTINCT COALESCE(c.observaciones)) as observaciones,
				MAX(oci.titulo) as oci_titulo,
				CONCAT_WS(' ', MAX(pa.nombre), MAX(pa.apellido_paterno), MAX(pa.apellido_materno)) as personal_name,
				MAX(pa.dni) as dni,
				c.validar
			FROM tbl_locales l
			INNER JOIN tbl_local_cajas lc ON lc.local_id = l.id
			INNER JOIN tbl_caja c ON c.local_caja_id = lc.id
			LEFT JOIN tbl_caja_observaciones_lista oci ON oci.id = c.id_oci
			INNER JOIN tbl_caja_detalle cd ON cd.caja_id = c.id
			INNER JOIN tbl_caja_datos_fisicos cdf11 ON (cdf11.caja_id = c.id AND cdf11.tipo_id = 11)
			INNER JOIN tbl_caja_datos_fisicos cdf10 ON (cdf10.caja_id = c.id AND cdf10.tipo_id = 10)
			LEFT JOIN tbl_usuarios u ON u.id = c.usuario_id
			LEFT JOIN tbl_personal_apt pa ON pa.id = u.personal_id
			INNER JOIN tbl_zonas z ON z.id = l.zona_id
			WHERE
				c.fecha_operacion >= '".$post['fecha_inicio']." 00:00:00' AND 
				c.fecha_operacion <= '".$post['fecha_fin']." 23:59:59'
				".$whereId."
			GROUP BY l.id, c.turno_id, c.id
			HAVING faltantes < 0
			ORDER BY c.fecha_operacion, faltantes";
$result = $mysqli->query($query);

if($result->num_rows){
	require_once '../phpexcel/classes/PHPExcel.php'; 
	
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getProperties()->setCreator("AtGestion")->setLastModifiedBy("AtGestion")->setTitle("Cajas Faltante")->setSubject("Cajas Faltante")->setDescription("Cajas Faltante")->setKeywords("Cajas Faltante")->setCategory("Cajas Faltante");
	$objPHPExcel->setActiveSheetIndex(0);

	$midCenterStyle = array(
		'alignment' => array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER
		)
	);
	$midLeftStyle = array(
		'alignment' => array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
		)
	);

	$objPHPExcel->getActiveSheet()->getDefaultStyle()->applyFromArray($midCenterStyle);
	$objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setName('Arial')->setSize(10);
	$objPHPExcel->getActiveSheet()->getStyle("I4:I9999")->applyFromArray($midLeftStyle);

	$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(30);
	$objPHPExcel->getActiveSheet()->getStyle("A1")->getFont()->setBold( true )->setSize(14)->setName('Arial');

	$objPHPExcel->getActiveSheet()->getStyle("A1:M1")->getFont()->setBold( true )->setSize(10)->setName('Arial');

	$objPHPExcel->getActiveSheet()
	->setCellValue('A1', 'REPORTE DE FALTANTES DE CAJA')
	->setCellValue('A2', 'Centro de Costo')
	->setCellValue('B2', 'Local')
	->setCellValue('C2', 'Zona')
	->setCellValue('D2', 'Fecha')
	->setCellValue('G2', 'Turno')
	->setCellValue('H2', 'Efectivo')
	->setCellValue('I2', 'Observaciones')
	->setCellValue('J2', 'Observaciones Control Interno')
	->setCellValue('K2', 'Usuario')
	->setCellValue('D3', 'Año')
	->setCellValue('E3', 'Mes')
	->setCellValue('F3', 'Día')
	->setCellValue('H3', 'Faltante')
	->setCellValue('K3', 'Nombre del Cajero')
	->setCellValue('L3', 'Documento de Identidad')
	->setCellValue('M2', 'Validado');

	$objPHPExcel->setActiveSheetIndex(0)->mergeCells("A1:M1");
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells("A2:A3");
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells("B2:B3");
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells("C2:C3");
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells("D2:F2");
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells("G2:G3");
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells("I2:I3");
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells("J2:J3");
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells("K2:L2");
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells("M2:M3");

	$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(18);
	$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(18);
	$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(18);
	$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(14);
	$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(14);
	$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);
	$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(28);
	$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(28);
	$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(28);
	$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(28);
	
	$i = 4; // INITIAL ROW
	foreach($result as $row){
		$objPHPExcel->getActiveSheet()->setCellValue('A'.$i, $row["cc_id"]);
		$objPHPExcel->getActiveSheet()->setCellValue('B'.$i, $row["nombre"]);
		$objPHPExcel->getActiveSheet()->setCellValue('C'.$i, $row["nombre_zona"]);
		$objPHPExcel->getActiveSheet()->setCellValue('D'.$i, $row["year"]);
		$objPHPExcel->getActiveSheet()->setCellValue('E'.$i, $row["month"]);
		$objPHPExcel->getActiveSheet()->setCellValue('F'.$i, $row["day"]);
		$objPHPExcel->getActiveSheet()->setCellValue('G'.$i, $row["turno_id"]);
		$objPHPExcel->getActiveSheet()->setCellValue('H'.$i, $row["faltantes"]);
		$objPHPExcel->getActiveSheet()->setCellValue('I'.$i, $row["observaciones"]);
		$objPHPExcel->getActiveSheet()->setCellValue('J'.$i, $row["oci_titulo"]);
		$objPHPExcel->getActiveSheet()->setCellValue('K'.$i, $row["personal_name"]);
		$objPHPExcel->getActiveSheet()->setCellValue('L'.$i, $row["dni"]);
		if((int)$row["validar"]==1){
			$objPHPExcel->getActiveSheet()->setCellValue('M'.$i, 'Validado');
		}else{
			$objPHPExcel->getActiveSheet()->setCellValue('M'.$i, 'No Validado');
		}

		$i++;
	}

	$xlsName = "faltantes_caja_".date("d-m-Y",strtotime($post["fecha_inicio"]))."_al_".date("d-m-Y",strtotime($post["fecha_fin"]))."_".date("Ymdhis").".xls";
	$objPHPExcel->getActiveSheet()->setTitle($xlsName);

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$excel_path = "/var/www/html/export/files_exported/caja_faltantes/".$xlsName;
	$excel_path_download = "/export/files_exported/caja_faltantes/".$xlsName;
	$objWriter->save($excel_path);

	$insert_cmd = "INSERT INTO tbl_exported_files (url,tipo,ext,size,fecha_registro,usuario_id)";
	$insert_cmd.= " VALUES ('".$xlsName."','excel','xls','".filesize($excel_path)."','".date("Y-m-d h:i:s")."','".$login["id"]."')";
	$mysqli->query($insert_cmd);

	echo json_encode(array(
		"path" => $excel_path_download,
		"url" => $xlsName,
		"tipo" => "excel",
		"ext" => "xls",
		"size" => filesize($excel_path),
		"fecha_registro" => date("d-m-Y h:i:s"),
		"sql" => $insert_cmd
	));
	
}
else print_r('No hay resultados para mostrar');
?>