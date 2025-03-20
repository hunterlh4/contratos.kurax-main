<?php  
date_default_timezone_set("America/Lima");

$result=array();

include("db_connect.php");
include("sys_login.php");


if (isset($_POST["accion"]) && $_POST["accion"]==="cont_listar_acuerdo_confidencialidad")
{
	$cont_proveedor_param_empresa = $_POST['cont_proveedor_param_empresa'];

	$cont_proveedor_param_area_solicitante = $_POST['cont_proveedor_param_area_solicitante'];

	$cont_proveedor_param_ruc = $_POST['cont_proveedor_param_ruc'];

	$cont_proveedor_param_razon_social = $_POST['cont_proveedor_param_razon_social'];

	$fecha_inicio_solicitud = $_POST['fecha_inicio_solicitud'];
	$fecha_fin_solicitud = $_POST['fecha_fin_solicitud'];
	$fecha_inicio_inicio = $_POST['cont_proveedor_param_fecha_inicio'];
	$fecha_fin_inicio = $_POST['cont_proveedor_param_fecha_fin'];

	$director_aprobacion_id = trim($_POST['aprobante']);
	$fecha_inicio_aprobacion = $_POST['search_fecha_inicio_aprobacion_firmado'];
	$fecha_fin_aprobacion = $_POST['search_fecha_fin_aprobacion_firmado'];


	$where_empresa = "";
	$where_area_solicitante = "";
	$where_ruc = "";
	$where_razon_social = "";
	$where_moneda = "";
	$where_fecha_solicitud = "";
	$where_fecha_inicio = "";
	$where_director_aprobacion = '';

	if ($cont_proveedor_param_empresa != "")
	{
		$where_empresa = " AND c.empresa_suscribe_id = '".$cont_proveedor_param_empresa."' ";
	}

	if ($cont_proveedor_param_area_solicitante != "")
	{
		$where_area_solicitante = " AND a.id = '".$cont_proveedor_param_area_solicitante."' ";
	}

	if ($cont_proveedor_param_ruc != "")
	{
		$where_ruc = " AND c.ruc = '".$cont_proveedor_param_ruc."' ";
	}

	if ($cont_proveedor_param_razon_social != "")
	{
		$where_razon_social = " AND c.razon_social = '".$cont_proveedor_param_razon_social."' ";
	}


	if (!Empty($fecha_inicio_solicitud) && !Empty($fecha_fin_solicitud)) {
		$where_fecha_solicitud = " AND c.created_at BETWEEN '$fecha_inicio_solicitud 00:00:00' AND '$fecha_fin_solicitud 23:59:59'";
	} elseif (!Empty($fecha_inicio_solicitud)) {
		$where_fecha_solicitud = " AND c.created_at >= '$fecha_inicio_solicitud 00:00:00'";
	} elseif (!Empty($fecha_fin_solicitud)) {
		$where_fecha_solicitud = " AND c.created_at <= '$fecha_fin_solicitud 23:59:59'";
	}

	if (!Empty($fecha_inicio_inicio) && !Empty($fecha_fin_inicio)) {
		$where_fecha_inicio = " AND c.fecha_inicio BETWEEN '$fecha_inicio_inicio 00:00:00' AND '$fecha_fin_inicio 23:59:59'";
	} elseif (!Empty($fecha_inicio_inicio)) {
		$where_fecha_inicio = " AND c.fecha_inicio >= '$fecha_inicio_inicio 00:00:00'";
	} elseif (!Empty($fecha_fin_inicio)) {
		$where_fecha_inicio = " AND c.fecha_inicio <= '$fecha_fin_inicio 23:59:59'";
	}

	$where_fecha_aprobacion = '';
	if (!Empty($fecha_inicio_aprobacion) && !Empty($fecha_fin_aprobacion)) {
		$where_fecha_aprobacion = " AND c.fecha_atencion_gerencia_proveedor between '$fecha_inicio_aprobacion 00:00:00' AND '$fecha_fin_aprobacion 23:59:59'";
	} elseif (!Empty($fecha_inicio_aprobacion)) {
		$where_fecha_aprobacion = " AND c.fecha_atencion_gerencia_proveedor >= '$fecha_inicio_aprobacion 00:00:00'";
	} elseif (!Empty($fecha_fin_aprobacion)) {
		$where_fecha_aprobacion = " AND c.fecha_atencion_gerencia_proveedor <= '$fecha_fin_aprobacion 23:59:59'";
	}
	
	if (!Empty($director_aprobacion_id))
	{
		$where_director_aprobacion = " AND ( ( c.director_aprobacion_id = ".$director_aprobacion_id." AND (c.check_gerencia_proveedor =1 and c.fecha_atencion_gerencia_proveedor IS NULL AND c.aprobacion_gerencia_proveedor=0) ) OR c.aprobado_por =".$director_aprobacion_id." ) ";
	}



	$query = "
	SELECT
		c.contrato_id, 
		a.nombre AS area, 
		r.nombre AS empresa_suscribe, 
		c.ruc, 
		c.razon_social, 
		c.fecha_atencion_gerencia_proveedor,
		IFNULL(m.nombre,(SELECT mc.nombre FROM cont_contraprestacion ct INNER JOIN tbl_moneda mc ON ct.moneda_id = mc.id WHERE ct.contrato_id = c.contrato_id LIMIT 1)) AS moneda,
		c.fecha_inicio, 
		concat(IFNULL(p.nombre, ''), ' ',  IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS solicitante, 
		
		CONCAT(IFNULL(pud.nombre, ''),' ',IFNULL(pud.apellido_paterno, ''),' ',IFNULL(pud.apellido_materno, '')) AS nombre_del_director_a_aprobar,
		cs.nombre AS proveedor_categoria, 
		tcs.nombre AS proveedor_tipo_categoria,
		arc.ruta, 
		arc.nombre,
		co.sigla AS sigla_correlativo,
		c.codigo_correlativo,
		c.num_dias_para_alertar_vencimiento,
		c.created_at
	FROM 
		cont_contrato c
		INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
		INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		INNER JOIN tbl_areas a ON p.area_id = a.id
		LEFT JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id AND r.status = 1
		LEFT JOIN tbl_moneda m ON c.moneda_id = m.id
		INNER JOIN cont_categoria_servicio cs ON c.categoria_id = cs.id
		INNER JOIN cont_tipo_categoria_servicio tcs ON c.tipo_contrato_proveedor_id = tcs.id
		LEFT JOIN cont_archivos arc ON c.contrato_id = arc.contrato_id AND arc.archivo_id IN(SELECT MAX(archivo_id) AS archivo_id FROM cont_archivos WHERE status = 1 AND tipo_archivo_id = 19 GROUP BY contrato_id)
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato

		LEFT JOIN tbl_usuarios ud ON c.director_aprobacion_id = ud.id
		LEFT JOIN tbl_personal_apt pud ON ud.personal_id = pud.id
	WHERE 
		c.status = 1 
		AND c.etapa_id = 5 
		AND c.tipo_contrato_id = 5 
		$where_empresa
		$where_area_solicitante
		$where_ruc
		$where_razon_social
		$where_moneda
		$where_fecha_solicitud
		$where_fecha_inicio
		$where_fecha_aprobacion
		$where_director_aprobacion
	ORDER BY c.contrato_id DESC
	";
	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
		$fecha = new DateTime($reg->fecha_inicio);
		$fecha_convertido = $fecha->format('Y-m-d');

		if ( empty(trim($reg->num_dias_para_alertar_vencimiento)) ) {
			$clase_boton_alertar = 'primary';
			$titulo_boton_alerta = 'Alerta por configurar';
		} else {
			$clase_boton_alertar = 'success';
			$titulo_boton_alerta = 'Alerta configurada';
		}

		$data[] = array(
			"0" => $reg->sigla_correlativo.$reg->codigo_correlativo,
			"1" => $reg->area,
			"2" => $reg->solicitante,
			"3" => $reg->empresa_suscribe,
			"4" => $reg->ruc,
			"5" => $reg->razon_social,
			"6" => $reg->created_at,
			"7" => $fecha_convertido,
			"8" => $reg->proveedor_categoria,
			"9" => $reg->proveedor_tipo_categoria,
			"10"=>	$reg->nombre_del_director_a_aprobar,
			"11"=>	$reg->fecha_atencion_gerencia_proveedor,
			"12" => '<a class="btn btn-rounded btn-primary btn-xs" 
						href="./?sec_id=contrato&amp;sub_sec_id=detalle_solicitud_acuerdo_confidencialidad&id=' . $reg->contrato_id . '"
						title="Ver detalle">
						<i class="fa fa-eye"></i> Ver
					</a>
					<a href="download_file.php?fileAcuerdoConfidencialidadFinal=' . str_replace("/var/www/html", "", $reg->ruta) . $reg->nombre . '" title="Descargar Acuerdo de Confidencialidad" class="btn btn-primary btn-xs"><i class="fa fa-download"></i> Descargar
					</a>',
			"13" => '<button type="button" class="btn btn-' . $clase_boton_alertar . ' btn-sm" title="' . $titulo_boton_alerta . '" onclick="sec_contrato_proveedor_alerta('.$reg->contrato_id.')">
					  <i class="glyphicon glyphicon-bell"></i>
					</button>'
		);
	}

	$resultado = array(
		"sEcho" => 1,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);

	echo json_encode($resultado);
}

if (isset($_POST["accion"]) && $_POST["accion"]==="cont_acuerdo_reporte_confidencialidad_excel")
{
	$cont_proveedor_param_empresa = $_POST['cont_proveedor_param_empresa'];

	$cont_proveedor_param_area_solicitante = $_POST['cont_proveedor_param_area_solicitante'];

	$cont_proveedor_param_ruc = $_POST['cont_proveedor_param_ruc'];

	$cont_proveedor_param_razon_social = $_POST['cont_proveedor_param_razon_social'];

	$fecha_inicio_solicitud = $_POST['fecha_inicio_solicitud'];
	$fecha_fin_solicitud = $_POST['fecha_fin_solicitud'];
	$fecha_inicio_inicio = $_POST['cont_proveedor_param_fecha_inicio'];
	$fecha_fin_inicio = $_POST['cont_proveedor_param_fecha_fin'];

	$director_aprobacion_id = trim($_POST['aprobante']);
	$fecha_inicio_aprobacion = $_POST['search_fecha_inicio_aprobacion_firmado'];
	$fecha_fin_aprobacion = $_POST['search_fecha_fin_aprobacion_firmado'];

	$where_empresa = "";
	$where_area_solicitante = "";
	$where_ruc = "";
	$where_razon_social = "";
	$where_fecha_solicitud = "";
	$where_fecha_inicio = "";
	$where_director_aprobacion = '';

	if ($cont_proveedor_param_empresa != "")
	{
		$where_empresa = " AND c.empresa_suscribe_id = '".$cont_proveedor_param_empresa."' ";
	}

	if ($cont_proveedor_param_area_solicitante != "")
	{
		$where_area_solicitante = " AND ta.id = '".$cont_proveedor_param_area_solicitante."' ";
	}

	if ($cont_proveedor_param_ruc != "")
	{
		$where_ruc = " AND c.ruc = '".$cont_proveedor_param_ruc."' ";
	}

	if ($cont_proveedor_param_razon_social != "")
	{
		$where_razon_social = " AND c.razon_social = '".$cont_proveedor_param_razon_social."' ";
	}

	if (!Empty($fecha_inicio_solicitud) && !Empty($fecha_fin_solicitud)) {
		$where_fecha_solicitud = " AND c.created_at BETWEEN '$fecha_inicio_solicitud 00:00:00' AND '$fecha_fin_solicitud 23:59:59'";
	} elseif (!Empty($fecha_inicio_solicitud)) {
		$where_fecha_solicitud = " AND c.created_at >= '$fecha_inicio_solicitud 00:00:00'";
	} elseif (!Empty($fecha_fin_solicitud)) {
		$where_fecha_solicitud = " AND c.created_at <= '$fecha_fin_solicitud 23:59:59'";
	}

	if (!Empty($fecha_inicio_inicio) && !Empty($fecha_fin_inicio)) {
		$where_fecha_inicio = " AND c.fecha_inicio BETWEEN '$fecha_inicio_inicio 00:00:00' AND '$fecha_fin_inicio 23:59:59'";
	} elseif (!Empty($fecha_inicio_inicio)) {
		$where_fecha_inicio = " AND c.fecha_inicio >= '$fecha_inicio_inicio 00:00:00'";
	} elseif (!Empty($fecha_fin_inicio)) {
		$where_fecha_inicio = " AND c.fecha_inicio <= '$fecha_fin_inicio 23:59:59'";
	}

	$where_fecha_aprobacion = '';
	if (!Empty($fecha_inicio_aprobacion) && !Empty($fecha_fin_aprobacion)) {
		$where_fecha_aprobacion = " AND c.fecha_atencion_gerencia_proveedor between '$fecha_inicio_aprobacion 00:00:00' AND '$fecha_fin_aprobacion 23:59:59'";
	} elseif (!Empty($fecha_inicio_aprobacion)) {
		$where_fecha_aprobacion = " AND c.fecha_atencion_gerencia_proveedor >= '$fecha_inicio_aprobacion 00:00:00'";
	} elseif (!Empty($fecha_fin_aprobacion)) {
		$where_fecha_aprobacion = " AND c.fecha_atencion_gerencia_proveedor <= '$fecha_fin_aprobacion 23:59:59'";
	}
	
	if (!Empty($director_aprobacion_id))
	{
		$where_director_aprobacion = " AND ( ( c.director_aprobacion_id = ".$director_aprobacion_id." AND (c.check_gerencia_proveedor =1 and c.fecha_atencion_gerencia_proveedor IS NULL AND c.aprobacion_gerencia_proveedor=0) ) OR c.aprobado_por =".$director_aprobacion_id." ) ";
	}

	$query = "
	SELECT
		c.contrato_id,
		co.sigla AS sigla_correlativo,
		c.codigo_correlativo,
		ta.nombre AS area_solicitante,
		c.persona_contacto_proveedor,
		tcs.nombre AS tipo_contrato,
		cs.nombre AS categoria,
		e.nombre AS razon_social,
		c.razon_social AS parte,
		c.fecha_suscripcion_proveedor,
		c.fecha_vencimiento_proveedor,
		et.situacion AS estado,
		f.nombre AS tipo_firma,
		c.observaciones,
		c.created_at,
		c.renovacion_automatica,
		CONCAT(IFNULL(pud.nombre, ''),' ',IFNULL(pud.apellido_paterno, ''),' ',IFNULL(pud.apellido_materno, '')) AS nombre_del_director_a_aprobar,
		c.fecha_atencion_gerencia_proveedor
	FROM cont_contrato c
		INNER JOIN tbl_usuarios tu ON c.user_created_id = tu.id
		INNER JOIN tbl_personal_apt tp ON tu.personal_id = tp.id
		INNER JOIN tbl_areas ta ON tp.area_id = ta.id
		LEFT JOIN cont_tipo_categoria_servicio tcs ON c.tipo_contrato_proveedor_id = tcs.id
		LEFT JOIN cont_categoria_servicio cs ON c.categoria_id = cs.id
		INNER JOIN tbl_razon_social e ON c.empresa_suscribe_id = e.id AND e.status = 1
		INNER JOIN cont_etapa et ON c.etapa_id = et.etapa_id
		LEFT JOIN cont_tipo_firma f ON c.tipo_firma_id = f.id
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato

		LEFT JOIN tbl_usuarios ud ON c.director_aprobacion_id = ud.id
		LEFT JOIN tbl_personal_apt pud ON ud.personal_id = pud.id
	WHERE 
		c.status = 1 
		AND c.etapa_id = 5 
		AND c.tipo_contrato_id = 5 
		$where_empresa
		$where_area_solicitante
		$where_ruc
		$where_razon_social
		$where_fecha_solicitud
		$where_fecha_inicio
		$where_fecha_aprobacion
		$where_director_aprobacion
	ORDER BY c.contrato_id DESC
	";

	$list_query = $mysqli->query($query);

	//PROCEDEMOS A CREAR LA CARPETASI EN CASO NO EXISTA
	$path = "/var/www/html/files_bucket/contratos/descargas/";

	if (!is_dir($path)) 
	{
		mkdir($path, 0777, true);
	}

	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/contratos/descargas/*'); //obtenemos todos los nombres de los ficheros
	foreach($files as $file)
	{
	    if(is_file($file))
	    unlink($file); //elimino el fichero
	}


	require_once '../phpexcel/classes/PHPExcel.php';
	$objPHPExcel = new PHPExcel();

	// Se asignan las propiedades del libro
	$objPHPExcel->getProperties()->setCreator("AT") // Nombre del autor
    ->setDescription("Reporte"); //Descripción

	$tituloReporte = "Relación de proveedores";

	$titulosColumnas = array('CÓDIGO', 'ÁREA SOLICITANTE', 'PERSONA DE CONTACTO', 'TIPO DE CONTRATO', 'CATEGORÍA', 'RAZÓN SOCIAL', 'PARTE', 'FECHA DE SOLICITUD', 'FECHA SUSCRIPCIÓN', 'VENCIMIENTO','RENOVACIÓN AUTOMÁTICA','VIGENCIA', 'ESTADO', 'TIPO DE FIRMA',
	'APROBANTE',
	'F. APROBACIÓN',
	'OBSERVACIONES');

	// Se agregan los titulos del reporte
	$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A1', $titulosColumnas[0])  //Titulo de las columnas
    ->setCellValue('B1', $titulosColumnas[1])
    ->setCellValue('C1', $titulosColumnas[2])
    ->setCellValue('D1', $titulosColumnas[3])
    ->setCellValue('E1', $titulosColumnas[4])
    ->setCellValue('F1', $titulosColumnas[5])
    ->setCellValue('G1', $titulosColumnas[6])
    ->setCellValue('H1', $titulosColumnas[7])
    ->setCellValue('I1', $titulosColumnas[8])
    ->setCellValue('J1', $titulosColumnas[9])
    ->setCellValue('K1', $titulosColumnas[10])
    ->setCellValue('L1', $titulosColumnas[11])
    ->setCellValue('M1', $titulosColumnas[12])
    ->setCellValue('N1', $titulosColumnas[13])
    ->setCellValue('O1', $titulosColumnas[14])
    ->setCellValue('P1', $titulosColumnas[15])
	->setCellValue('Q1', $titulosColumnas[15]);

    //Se agregan los datos a la lista del reporte
	$vigencia = '';
	$cont = 0;

	$i = 2; //Numero de fila donde se va a comenzar a rellenar los datos
	while ($fila = $list_query->fetch_array()) 
	{
		$cont ++;

		$vigencia = '';

		if (!empty(trim($fila['fecha_vencimiento_proveedor']))) {
			if($fila['fecha_vencimiento_proveedor'] < date("Y-m-d H:i:s"))
			{
				$vigencia = 'VIGENTE';
			}
			else
			{
				$vigencia = 'VENCIDA';
			}
		}
		$fila['renovacion_automatica'] = $fila['renovacion_automatica'] == 1 ? 'SI':'NO';

		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('A'.$i, $fila['sigla_correlativo'].$fila['codigo_correlativo'])
		->setCellValue('B'.$i, $fila['area_solicitante'])
		->setCellValue('C'.$i, $fila['persona_contacto_proveedor'])
		->setCellValue('D'.$i, $fila['tipo_contrato'])
		->setCellValue('E'.$i, $fila['categoria'])
		->setCellValue('F'.$i, $fila['razon_social'])
		->setCellValue('G'.$i, $fila['parte'])
		->setCellValue('H'.$i, $fila['created_at'])
		->setCellValue('I'.$i, $fila['fecha_suscripcion_proveedor'])
		->setCellValue('J'.$i, $fila['fecha_vencimiento_proveedor'])
		->setCellValue('K'.$i, $fila['renovacion_automatica'])
		->setCellValue('L'.$i, $vigencia)
		->setCellValue('M'.$i, $fila['estado'])
		->setCellValue('N'.$i, $fila['tipo_firma'])
		->setCellValue('O'.$i, $fila['nombre_del_director_a_aprobar'])
		->setCellValue('P'.$i, $fila['fecha_atencion_gerencia_proveedor'])
		->setCellValue('Q'.$i, $fila['observaciones']);
		$i++;
	}

	$estiloNombresColumnas = array(
		'font' => array(
	        'name'      => 'Calibri',
	        'bold'      => true,
	        'italic'    => false,
	        'strike'    => false,
	        'size' =>10,
	        'color'     => array(
	            'rgb' => 'ffffff'
	        )
	    ),
	    'fill' => array(
			  'type'  => PHPExcel_Style_Fill::FILL_SOLID,
			  'color' => array(
			        'rgb' => '000000')
			),
	    'alignment' =>  array(
	        'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        'wrap'      => false
	    )
	);

	$estiloInformacion = new PHPExcel_Style();
	$estiloInformacion->applyFromArray( array(
	    'font' => array(
	        'name'  => 'Arial',
	        'color' => array(
	            'rgb' => '000000'
	        )
	    )
	));

	$estilo_centrar = array(
	    'alignment' =>  array(
	        'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        'wrap'      => false
	    )
	);

	$objPHPExcel->getActiveSheet()->getStyle('A1:Q1')->applyFromArray($estiloNombresColumnas);
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:Q".($i-1));
	$objPHPExcel->getActiveSheet()->getStyle('A1:A'.($i-1))->applyFromArray($estilo_centrar);
	$objPHPExcel->getActiveSheet()->getStyle('H1:L'.($i-1))->applyFromArray($estilo_centrar);

	// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
	for($i = 'A'; $i <= 'Z'; $i++)
	{
	    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}

	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('Acuerdo de Confidencialidad');
	  
	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);
	
	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Acuerdo de Confidencialidad AT.xls');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$excel_path = '/var/www/html/files_bucket/contratos/descargas/Acuerdo de Confidencialidad AT.xls';
	$excel_path_download = '/files_bucket/contratos/descargas/Acuerdo de Confidencialidad AT.xls';

	try 
	{
		$objWriter->save($excel_path);
	} 
	catch (PHPExcel_Writer_Exception $e) 
	{
		echo json_encode(["error" => $e]);
		exit;
	}

	echo json_encode(array(
		"ruta_archivo" => $excel_path_download
	));
	exit;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_info_alerta") {

    $contrato_id = $_POST["contrato_id"];

    $query = "
	SELECT
	c.razon_social,
	c.fecha_inicio,
	c.fecha_vencimiento_proveedor,
	c.num_dias_para_alertar_vencimiento
	FROM cont_contrato c
	WHERE c.status = 1 AND c.contrato_id = '$contrato_id'
	";

    $list_query = $mysqli->query($query);
    $list = array();

	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

    $result["http_code"] = 200;
    $result["result"] = $list;
	$result["status"] = "Datos obtenidos de gestion.";

	echo json_encode($result);
	exit;
}

if (isset($_POST["accion"]) && $_POST["accion"]==="actualizar_alerta_contrato") {
	$contrato_id = $_POST["contrato_id"];
	$num_dias = $_POST["num_dias"];

	$query_update = "
	UPDATE cont_contrato
	SET num_dias_para_alertar_vencimiento = '$num_dias',
	alerta_enviada_id = NULL
	WHERE contrato_id = '$contrato_id' 
	";
	
	$mysqli->query($query_update);

	if($mysqli->error) {
		$result["http_code"] = 400;
		$result["error"] = $mysqli->error;
	} else {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
	}

	echo json_encode($result);
	exit();
}

?>

