<?php
include("sys/db_connect.php");
include("sys/sys_login.php");
require_once 'phpexcel/classes/PHPExcel.php';
//sec_contrato_tesoreria_export_excel
date_default_timezone_set("America/Lima");

$objPHPExcel = new PHPExcel();

// Se asignan las propiedades del libro
$objPHPExcel->getProperties()->setCreator("AT") // Nombre del autor
->setDescription("Reporte"); //Descripción

//$tituloReporte = "Relación de locales";

//Consulta detalles de la programacion
if($_GET["id"] == ""){
	echo "Error: No se recibió [id] de la programación";
	die;
}
$programacion_id = 0;
$programacion_id = $_GET["id"];

$query = "
SELECT 
	p.id AS programacion_id,
	p.numero programacion_numero,
	p.fecha_programacion,
	p.tipo_concepto_id,
	tc.nombre concepto,
	p.valor_cambio,
	p.tipo_pago_id,
	tp.nombre tipo_pago,
	p.num_cuenta_id,
	nc.num_cuenta_corriente AS banco,
	p.moneda_id,
	(CASE WHEN p.moneda_id = 1 THEN 'MN' WHEN p.moneda_id = 2 THEN 'ME' END) AS moneda,
	p.importe AS importe,
	p.etapa_id,
	ep.nombre etapa,
	#rs.nombre as razon_social,
	#auditoria
	if(isnull(concat(pp.nombre, ' ', pp.apellido_paterno)),'-',concat(pp.nombre, ' ', pp.apellido_paterno)) as elaborado_por,
	if(isnull(p.created_at),'-',p.created_at) as fecha_elaboracion,
	if(isnull(concat(pp_2.nombre, ' ', pp_2.apellido_paterno)),'-',concat(pp_2.nombre, ' ', pp_2.apellido_paterno))  as editado_por,
	if(isnull(p.edit_at),'-',p.edit_at) as fecha_edicion,
	if(isnull(concat(pp_3.nombre, ' ', pp_3.apellido_paterno)),'-',concat(pp_3.nombre, ' ', pp_3.apellido_paterno)) as procesado_por,
	if(isnull(p.process_at),'-',p.process_at) as fecha_proceso,
	if(isnull(concat(pp_4.nombre, ' ', pp_4.apellido_paterno)),'-',concat(pp_4.nombre, ' ', pp_4.apellido_paterno))as eliminado_por,
	if(isnull(p.delete_at),'-',p.delete_at) as fecha_eliminacion
FROM 
	cont_programacion p
	INNER JOIN cont_tipo_concepto tc on p.tipo_concepto_id = tc.id
	INNER JOIN cont_tipo_pago_programacion tp on p.tipo_pago_id = tp.id
	INNER JOIN cont_num_cuenta nc on p.num_cuenta_id = nc.id
	INNER JOIN tbl_moneda m on p.moneda_id = m.id
	INNER JOIN cont_etapa_programacion ep on p.etapa_id = ep.id
	-- INNER JOIN cont_subdiario cs on p.subdiario_id = cs.id
	-- INNER JOIN tbl_razon_social rs on cs.razon_social_id = rs.id
	LEFT JOIN cont_comprobantes_pago cp on p.comprobante_pago_id = cp.id
	#elaboracion
	INNER JOIN tbl_usuarios u on p.user_created_id = u.id
	INNER JOIN tbl_personal_apt pp on u.personal_id = pp.id
	#edicion
	LEFT JOIN tbl_usuarios u_2 on p.user_edit_id = u_2.id
	LEFT JOIN tbl_personal_apt pp_2 on u_2.personal_id = pp_2.id
	#proceso
	LEFT JOIN tbl_usuarios u_3 on p.user_process_id = u_3.id
	LEFT JOIN tbl_personal_apt pp_3 on u_3.personal_id = pp_3.id
	#eliminacion
	LEFT JOIN tbl_usuarios u_4 on p.user_delete_id = u_4.id
	LEFT JOIN tbl_personal_apt pp_4 on u_4.personal_id = pp_4.id
WHERE 
	p.id = $programacion_id";

$list_query = $mysqli->query($query);
$list_detalle_programacion = array();
while ($li = $list_query->fetch_assoc()) {
	$list_detalle_programacion[] = $li;
}
$result=array();
if ($mysqli->error) {
	$result["error"] = $mysqli->error;
}

$razon_social = "";
$numero_programacion = "";
$fecha_programacion = "";
$concepto = "";
$tipo_pago = "";
$banco = "";
$importe = "";
$estado = "";
$moneda = "";
$tipo_cambio = "";
$departamento = "";
$elaborado_por = "";
$modificado_por = "";
$aprobado_por = "";
$fecha_creacion = "";
$fecha_modificacion = "";
$fecha_aprobacion = "";
$vence_en = "";

foreach ($list_detalle_programacion as $item){
	// $razon_social = $item["razon_social"];
	$numero_programacion = $item["programacion_numero"];
	$fecha_programacion = $item["fecha_programacion"];
	$concepto = $item["concepto"];
	$tipo_pago = $item["tipo_pago"];
	$banco = $item["banco"];
	$importe = $item["importe"];
	$estado = $item["etapa"];
	$moneda = $item["moneda"];
	$tipo_cambio = $item["valor_cambio"];
	$departamento = "TESORERIA";
	$elaborado_por = $item["elaborado_por"];
	$modificado_por = $item["editado_por"];
	$aprobado_por = $item["procesado_por"];
	$fecha_creacion = $item["fecha_elaboracion"];
	$fecha_modificacion = $item["fecha_edicion"];
	$fecha_aprobacion = $item["fecha_proceso"];
	$vence_en = "0 Dias";
}

$query_2 = "
SELECT 
	pd.programacion_id, 
	concat('P-', b.num_docu) codigo,
	b.nombre AS acreedor, 
	CONCAT('1683-', p.mes, p.anio) AS num_doc, 
	'' fecha_emision, 
	p.periodo_fin AS fecha_vencimiento,
	(
		CASE 
			WHEN ce.tipo_moneda_id = 1 THEN 'MN' 
			WHEN ce.tipo_moneda_id = 2 THEN 'ME' 
		END
	) AS moneda,
	p.importe AS programado
FROM 
	cont_programacion_detalle pd
	INNER JOIN cont_provision p on pd.provision_id = p.id
	INNER JOIN cont_condicion_economica ce ON p.condicion_economica_id = ce.condicion_economica_id
	INNER JOIN cont_beneficiarios b ON ce.contrato_id = b.contrato_id
	INNER JOIN cont_contrato c ON ce.contrato_id = c.contrato_id
WHERE 
	pd.programacion_id = $programacion_id 
	AND pd.status = 1 
	AND p.status = 1 
	AND ce.status = 1 
	AND b.status = 1 
	AND c.status = 1
"; 

$list_query2 = $mysqli->query($query_2);
$list_acreedores_programacion = array();

while ($li = $list_query2->fetch_assoc()) {
	$list_acreedores_programacion[] = $li;
}
$result2=array();
if ($mysqli->error) {
	$result2["error_acreedores"] = $mysqli->error;
}

$c = 0;

$data = array(
	0  => array('A'=> $razon_social, 'B'=>'', 'C'=>'', 'D'=>'', 'E'=>'', 'F'=>'', 'G'=>'', 'H'=> $fecha_creacion, 'I'=>''),
	1  => array('A'=>'Programación de Pagos Nro ' . $numero_programacion, 'B'=>'', 'C'=>'', 'D'=>'', 'E'=>'', 'F'=>'', 'G'=>'', 'H'=>'', 'I'=>''),
	2  => array('A'=>'Agencia 0001-AGENCIA PRINCIPAL', 'B'=>'', 'C'=>'', 'D'=>'', 'E'=>'', 'F'=>'', 'G'=>'', 'H'=>'', 'I'=>''),
	3  => array('A'=>'Fecha Programación', 'B'=> $fecha_programacion, 'C'=>'', 'D'=>'', 'E'=>'', 'F'=>'Departamento', 'G'=>'', 'H'=> $departamento, 'I'=>''),
	4  => array('A'=>'Concepto', 'B'=> $concepto, 'C'=>'', 'D'=>'', 'E'=>'', 'F'=>'Elaborado por', 'G'=>'', 'H'=> $elaborado_por, 'I'=>''),
	5  => array('A'=>'Tipo Pago', 'B'=> $tipo_pago, 'C'=>'', 'D'=>'', 'E'=>'', 'F'=>'Modificado por', 'G'=>'', 'H'=> $modificado_por ,'I'=>''),
	6  => array('A'=>'Banco', 'B'=> $banco, 'C'=>'', 'D'=>'', 'E'=>'', 'F'=>'Aprobado por', 'G'=>'', 'H'=> $aprobado_por, 'I'=>''),
	7  => array('A'=>'Importe', 'B'=> $importe, 'C'=>'', 'D'=>'', 'E'=>'', 'F'=>'Fecha Creación', 'G'=>'', 'H'=> $fecha_creacion, 'I'=>''),
	8  => array('A'=>'Estado', 'B'=> $estado, 'C'=>'', 'D'=>'', 'E'=>'', 'F'=>'Fecha Modificación', 'G'=>'', 'H'=> $fecha_modificacion, 'I'=>''),
	9  => array('A'=>'Moneda', 'B'=> $moneda, 'C'=>'', 'D'=>'', 'E'=>'', 'F'=>'Fecha Aprobación', 'G'=>'', 'H'=> $fecha_creacion, 'I'=>''),
	10 => array('A'=>'Tipo de Cambio', 'B'=> $tipo_cambio, 'C'=>'', 'D'=>'', 'E'=>'', 'F'=>'Vence a', 'G'=>'', 'H'=> $vence_en, 'I'=>''),
	11 => array('A'=>'', 'B'=>'', 'C'=>'', 'D'=>'', 'E'=>'', 'F'=>'', 'G'=>'', 'H'=>'', 'I'=>''),
	13 => array('A'=>'', 'B'=>'', 'C'=>'', 'D'=>'', 'E'=>'', 'F'=>'', 'G'=>'', 'H'=>'', 'I'=>''),
	14 => array('A'=>'', 'B'=>'', 'C'=>'', 'D'=>'', 'E'=>'', 'F'=>'', 'G'=>'', 'H'=>'', 'I'=>''),
);
$num_fila = 1;

// Se agregan los titulos del reporte
foreach ($data as $key => $row) {
	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A' . $num_fila, $row['A'])
	->setCellValue('B' . $num_fila, $row['B'])
	->setCellValue('C' . $num_fila, $row['C'])
	->setCellValue('D' . $num_fila, $row['D'])
	->setCellValue('E' . $num_fila, $row['E'])
	->setCellValue('F' . $num_fila, $row['F'])
	->setCellValue('G' . $num_fila, $row['G'])
	->setCellValue('H' . $num_fila, $row['H'])
	->setCellValue('I' . $num_fila, $row['I']);
	$num_fila++;
}

//Cabecera Tabla Acreedores
$objPHPExcel->setActiveSheetIndex(0)
->setCellValue('A' . $num_fila, "Proveedor")
->setCellValue('B' . $num_fila, "Nombre")
->setCellValue('C' . $num_fila, "Td")
->setCellValue('D' . $num_fila, "Número")
->setCellValue('E' . $num_fila, "Emisión")
->setCellValue('F' . $num_fila, "Vmto")
->setCellValue('G' . $num_fila, "Mon");

if($moneda == "MN"){
	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('H' . $num_fila, "Programado MN");
}else{
	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('H' . $num_fila, "Programado US");
}
$num_fila++;
$TotalProgramado = 0;
//Acreedores
foreach($list_acreedores_programacion as $item){
	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A' . $num_fila, $item["codigo"])
	->setCellValue('B' . $num_fila, $item["acreedor"])
	->setCellValue('C' . $num_fila, "FT")
	->setCellValue('D' . $num_fila, $item["num_doc"])
	->setCellValue('E' . $num_fila, $item["fecha_emision"])
	->setCellValue('F' . $num_fila, $item["fecha_vencimiento"])
	->setCellValue('G' . $num_fila, $item["moneda"])
	->setCellValue('H' . $num_fila, $item["programado"]);
	$TotalProgramado = $TotalProgramado + $item["programado"];
	$num_fila++;
}
//Totales
$objPHPExcel->setActiveSheetIndex(0)
->setCellValue('A' . $num_fila, "Total General:")
->setCellValue('B' . $num_fila, "")
->setCellValue('C' . $num_fila, "")
->setCellValue('D' . $num_fila, "")
->setCellValue('E' . $num_fila, "")
->setCellValue('F' . $num_fila, "")
->setCellValue('G' . $num_fila, "")
->setCellValue('H' . $num_fila, $TotalProgramado);

// Combinar celdas
$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $num_fila . ':G' . $num_fila);
$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A2:H2');
$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A3:H3');

$estilo_general = array(
	'font' => array(
		'name'  => 'Arial',
		'bold'  => false,
		'size' => 10,
		'color' => array(
			'rgb' => '000000'
		)
	)
);

$estilo_negrita = array(
	'font' => array(
		'bold'  => true
	)
);

$estilo_alinear_al_centro = array(
	'alignment' =>  array(
		'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
		'wrap'      => false
	)
);

$estilo_alinear_a_la_izquierda = array(
	'alignment' =>  array(
		'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
		'wrap'      => false
	)
);

$estilo_alinear_a_la_derecha = array(
	'alignment' =>  array(
		'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
		'wrap'      => false
	)
);

$estilo_bordes = array(
	'borders' => array(
		'allborders' => array(
		  'style' => PHPExcel_Style_Border::BORDER_THIN
		)
	)
);

$objPHPExcel->getActiveSheet()->getStyle('A1:I20')->applyFromArray($estilo_general);
$objPHPExcel->getActiveSheet()->getStyle('A1:H3')->applyFromArray($estilo_negrita);
$objPHPExcel->getActiveSheet()->getStyle('A4:A11')->applyFromArray($estilo_negrita);
$objPHPExcel->getActiveSheet()->getStyle('F4:F11')->applyFromArray($estilo_negrita);
$objPHPExcel->getActiveSheet()->getStyle('A15:I15')->applyFromArray($estilo_negrita);
$objPHPExcel->getActiveSheet()->getStyle('A' . $num_fila . ':I' . $num_fila)->applyFromArray($estilo_negrita);
$objPHPExcel->getActiveSheet()->getStyle('B4:B11')->applyFromArray($estilo_alinear_a_la_izquierda);
$objPHPExcel->getActiveSheet()->getStyle('E16:E500')->applyFromArray($estilo_alinear_a_la_derecha);
$objPHPExcel->getActiveSheet()->getStyle('F16:F500')->applyFromArray($estilo_alinear_a_la_derecha);
$objPHPExcel->getActiveSheet()->getStyle('H16:H500')->applyFromArray($estilo_alinear_a_la_derecha);
$objPHPExcel->getActiveSheet()->getStyle('I16:I500')->applyFromArray($estilo_alinear_a_la_derecha);
$objPHPExcel->getActiveSheet()->getStyle('A15:H15')->applyFromArray($estilo_bordes);
$objPHPExcel->getActiveSheet()->getStyle('A' . $num_fila . ':H' . $num_fila)->applyFromArray($estilo_bordes);
$objPHPExcel->getActiveSheet()->getStyle('A2:H2')->applyFromArray($estilo_alinear_al_centro);
$objPHPExcel->getActiveSheet()->getStyle('A3:H3')->applyFromArray($estilo_alinear_al_centro);

// Se establece la anchura de las columnas
$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(14);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(3);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(12);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(10);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(4);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(13);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(13);


// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
// for($i = 'A'; $i <= 'Z'; $i++)
// {
// $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
// }

// Se asigna el nombre a la hoja
$objPHPExcel->getActiveSheet()->setTitle('Hoja1');

// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
$objPHPExcel->setActiveSheetIndex(0);

// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="XLSTARTW1516343.xls"');
header('Cache-Control: max-age=0');

//$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('php://output');
exit;