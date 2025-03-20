<?php 

date_default_timezone_set("America/Lima");

$result=array();

include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);


if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_reporte_tesoreria_listar_asignacion")
{
	$tipo_solicitud = $_POST['param_tipo_solicitud'];

	$param_fecha_desde = $_POST['param_fecha_desde'];
	$param_fecha_desde = date("Y-m-d", strtotime($param_fecha_desde));

	$param_fecha_hasta = $_POST['param_fecha_hasta'];
	$param_fecha_hasta = date("Y-m-d", strtotime($param_fecha_hasta));

	$param_situacion_tesoreria = $_POST['param_situacion_tesoreria'];

	$login_usuario_id = $login?$login['id']:null;

	$query_pendiente_pago = "";
	$query_pagado = "";
	$query_todos = "";

	// INICIO: VERIFICAR RED
	
	$where_redes = "";

	$select_red =
    "
        SELECT
            l.red_id
        FROM tbl_usuarios_locales ul
            INNER JOIN tbl_locales l
            ON ul.local_id = l.id
        WHERE ul.usuario_id = '".$login_usuario_id."'
        	AND ul.estado = 1 AND l.red_id IS NOT NULL
        GROUP BY l.red_id
    ";

    $data_select_red = $mysqli->query($select_red);

    $row_count_data_select_red = $data_select_red->num_rows;

    $ids_data_select_red = '';
    $contador_ids = 0;
    
    if ($row_count_data_select_red > 0) 
    {
        while ($row = $data_select_red->fetch_assoc()) 
        {
            if ($contador_ids > 0) 
            {
                $ids_data_select_red .= ',';
            }

            $ids_data_select_red .= $row["red_id"];           
            $contador_ids++;
        }

        $where_redes = " AND rse.red_id IN ($ids_data_select_red) ";
    }

	// FIN: VERIFICAR RED

	if($param_situacion_tesoreria == 10 || $param_situacion_tesoreria == 0)
	{
		//PENDIENTE DE PAGO
		$query_pendiente_pago = "
			SELECT
				a.id, a.usuario_asignado_id,
			    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado,
			    tp.dni AS usuario_asignado_dni, a.fondo_asignado, a.created_at AS fecha_solicitud,
			    z.nombre AS zona,
			    e.situacion AS situacion_tesoreria,
			    '' AS fecha_comprobante
			FROM mepa_asignacion_caja_chica a
				INNER JOIN tbl_usuarios tu
				ON a.usuario_asignado_id = tu.id
				INNER JOIN tbl_personal_apt tp
				ON tu.personal_id = tp.id
				INNER JOIN tbl_razon_social rse
				ON tp.razon_social_id = rse.id
				INNER JOIN mepa_zona_asignacion z
				ON a.zona_asignacion_id = z.id
				INNER JOIN cont_etapa e
				ON a.situacion_etapa_id_tesoreria = e.etapa_id
			WHERE a.status = 1 AND a.situacion_etapa_id = 6 
				AND a.situacion_etapa_id_tesoreria = 10 
				".$where_redes." 
				AND DATE_FORMAT(a.created_at, '%Y-%m-%d') BETWEEN '".$param_fecha_desde."' AND '".$param_fecha_hasta."'
		";

		$list_query = $mysqli->query($query_pendiente_pago);
		
	}
	if($param_situacion_tesoreria == 11 || $param_situacion_tesoreria == 0)
	{
		//PAGADO
		$query_pagado = "
			SELECT
				a.id, a.usuario_asignado_id,
			    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado,
			    tp.dni AS usuario_asignado_dni, a.fondo_asignado, a.created_at AS fecha_solicitud,
			    z.nombre AS zona,
			    e.situacion AS situacion_tesoreria,
			    p.fecha_comprobante
			FROM mepa_asignacion_caja_chica a
				INNER JOIN tbl_usuarios tu
				ON a.usuario_asignado_id = tu.id
				INNER JOIN tbl_personal_apt tp
				ON tu.personal_id = tp.id
				INNER JOIN tbl_razon_social rse
				ON tp.razon_social_id = rse.id
				INNER JOIN mepa_zona_asignacion z
				ON a.zona_asignacion_id = z.id
				INNER JOIN cont_etapa e
				ON a.situacion_etapa_id_tesoreria = e.etapa_id
				INNER JOIN mepa_caja_chica_programacion_detalle pd
				ON pd.nombre_tabla_id = a.id
				INNER JOIN mepa_caja_chica_programacion p
				ON pd.mepa_caja_chica_programacion_id = p.id
			WHERE a.status = 1 AND a.situacion_etapa_id = 6 
				AND pd.status = 1 AND p.tipo_solicitud_id = 1 
				AND a.situacion_etapa_id_tesoreria = 11 
				".$where_redes." 
				AND DATE_FORMAT(a.created_at, '%Y-%m-%d') BETWEEN '".$param_fecha_desde."' AND '".$param_fecha_hasta."'
		";

		$list_query = $mysqli->query($query_pagado);	
	}
	if($param_situacion_tesoreria == 0)
	{
		$query_todos.= $query_pendiente_pago;
		$query_todos.= "UNION ALL";
		$query_todos.= $query_pagado;

		$list_query = $mysqli->query($query_todos);
	}

	$data =  array();
	$num = 1;

	while($reg = $list_query->fetch_object()) 
	{
		$data[] = array(
			"0" => $num,
			"1" => $reg->usuario_asignado,
			"2" => $reg->usuario_asignado_dni,
			"3" => $reg->zona,
			"4" => $reg->fecha_solicitud,
			"5" => $reg->fondo_asignado,
			"6" => $reg->situacion_tesoreria,
			"7" => $reg->fecha_comprobante
		);

		$num++;
	}

	$resultado = array(
		"sEcho" => 1,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);

	echo json_encode($resultado);
}

if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_reporte_tesoreria_asignacion_pagados_export")
{
	$tipo_solicitud = $_POST['param_tipo_solicitud'];

	$param_fecha_desde = $_POST['param_fecha_desde'];
	$param_fecha_desde = date("Y-m-d", strtotime($param_fecha_desde));

	$param_fecha_hasta = $_POST['param_fecha_hasta'];
	$param_fecha_hasta = date("Y-m-d", strtotime($param_fecha_hasta));

	$param_situacion_tesoreria = $_POST['param_situacion_tesoreria'];

	$login_usuario_id = $login?$login['id']:null;

	$query_pendiente_pago = "";
	$query_pagado = "";
	$query_todos = "";

	// INICIO: VERIFICAR RED
	
	$where_redes = "";

	$select_red =
    "
        SELECT
            l.red_id
        FROM tbl_usuarios_locales ul
            INNER JOIN tbl_locales l
            ON ul.local_id = l.id
        WHERE ul.usuario_id = '".$login_usuario_id."'
        	AND ul.estado = 1 AND l.red_id IS NOT NULL
        GROUP BY l.red_id
    ";

    $data_select_red = $mysqli->query($select_red);

    $row_count_data_select_red = $data_select_red->num_rows;

    $ids_data_select_red = '';
    $contador_ids = 0;
    
    if ($row_count_data_select_red > 0) 
    {
        while ($row = $data_select_red->fetch_assoc()) 
        {
            if ($contador_ids > 0) 
            {
                $ids_data_select_red .= ',';
            }

            $ids_data_select_red .= $row["red_id"];           
            $contador_ids++;
        }

        $where_redes = " AND rse.red_id IN ($ids_data_select_red) ";
    }

	// FIN: VERIFICAR RED

	if($param_situacion_tesoreria == 10 || $param_situacion_tesoreria == 0)
	{
		//PENDIENTE DE PAGO
		$query_pendiente_pago = "
			SELECT
				a.id, a.usuario_asignado_id,
			    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado,
			    tp.dni AS usuario_asignado_dni, a.fondo_asignado,
			    z.nombre AS zona,
			    e.situacion AS situacion_tesoreria,
			    '' AS fecha_comprobante
			FROM mepa_asignacion_caja_chica a
				INNER JOIN tbl_usuarios tu
				ON a.usuario_asignado_id = tu.id
				INNER JOIN tbl_personal_apt tp
				ON tu.personal_id = tp.id
				INNER JOIN tbl_razon_social rse
				ON tp.razon_social_id = rse.id
				INNER JOIN mepa_zona_asignacion z
				ON a.zona_asignacion_id = z.id
				INNER JOIN cont_etapa e
				ON a.situacion_etapa_id_tesoreria = e.etapa_id
			WHERE a.status = 1 AND a.situacion_etapa_id = 6 
				AND a.situacion_etapa_id_tesoreria = 10 
				".$where_redes." 
				AND DATE_FORMAT(a.created_at, '%Y-%m-%d') BETWEEN '".$param_fecha_desde."' AND '".$param_fecha_hasta."'
		";

		$list_query = $mysqli->query($query_pendiente_pago);
		
	}
	if($param_situacion_tesoreria == 11 || $param_situacion_tesoreria == 0)
	{
		//PAGADO
		$query_pagado = "
			SELECT
				a.id, a.usuario_asignado_id,
			    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado,
			    tp.dni AS usuario_asignado_dni, a.fondo_asignado,
			    z.nombre AS zona,
			    e.situacion AS situacion_tesoreria,
			    p.fecha_comprobante
			FROM mepa_asignacion_caja_chica a
				INNER JOIN tbl_usuarios tu
				ON a.usuario_asignado_id = tu.id
				INNER JOIN tbl_personal_apt tp
				ON tu.personal_id = tp.id
				INNER JOIN tbl_razon_social rse
				ON tp.razon_social_id = rse.id
				INNER JOIN tbl_usuarios tua
				ON a.usuario_atencion_id = tua.id
				INNER JOIN tbl_personal_apt tpa
				ON tua.personal_id = tpa.id
				INNER JOIN mepa_zona_asignacion z
				ON a.zona_asignacion_id = z.id
				INNER JOIN cont_etapa e
				ON a.situacion_etapa_id_tesoreria = e.etapa_id

				INNER JOIN mepa_caja_chica_programacion_detalle pd
				ON pd.nombre_tabla_id = a.id
				INNER JOIN mepa_caja_chica_programacion p
				ON pd.mepa_caja_chica_programacion_id = p.id
			WHERE a.status = 1 AND a.situacion_etapa_id = 6 
				AND pd.status = 1 AND p.tipo_solicitud_id = 1 
				".$where_redes." 
				AND a.situacion_etapa_id_tesoreria = 11 AND DATE_FORMAT(a.created_at, '%Y-%m-%d') BETWEEN '".$param_fecha_desde."' AND '".$param_fecha_hasta."'
		";

		$list_query = $mysqli->query($query_pagado);	
	}
	if($param_situacion_tesoreria == 0)
	{
		$query_todos.= $query_pendiente_pago;
		$query_todos.= "UNION ALL";
		$query_todos.= $query_pagado;

		$list_query = $mysqli->query($query_todos);
	}

	//PROCEDEMOS A CREAR LA CARPETASI EN CASO NO EXISTA
	$path = "/var/www/html/files_bucket/mepa/descargas/tesoreria/rpt_asignacion/";

	if (!is_dir($path)) 
	{
		mkdir($path, 0777, true);
	}

	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/mepa/descargas/tesoreria/rpt_asignacion/*'); //obtenemos todos los nombres de los ficheros
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

	$tituloReporte = "Reporte Asignación Caja Chica Pagados de Solicitudes '".$param_fecha_desde."' AL '".$param_fecha_hasta."' ";

	$titulosColumnas = array('Nº', 'USUARIO ASIGNADO', 'DNI', 'ZONA', 'FONDO ASIGNADO', 'SITUACION TESORERIA', 'FECHA COMPROBANTE DE PAGO');

	// Se agregan los titulos del reporte
	$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A1', $titulosColumnas[0])  //Titulo de las columnas
    ->setCellValue('B1', $titulosColumnas[1])
    ->setCellValue('C1', $titulosColumnas[2])
    ->setCellValue('D1', $titulosColumnas[3])
    ->setCellValue('E1', $titulosColumnas[4])
    ->setCellValue('F1', $titulosColumnas[5])
    ->setCellValue('G1', $titulosColumnas[6]);

    //Se agregan los datos a la lista del reporte
	$cont = 0;

	$i = 2; //Numero de fila donde se va a comenzar a rellenar los datos
	while ($fila = $list_query->fetch_array()) 
	{
		$cont ++;

		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('A'.$i, $cont)
		->setCellValue('B'.$i, $fila['usuario_asignado'])
		->setCellValue('C'.$i, $fila['usuario_asignado_dni'])
		->setCellValue('D'.$i, $fila['zona'])
		->setCellValue('E'.$i, $fila['fondo_asignado'])
		->setCellValue('F'.$i, $fila['situacion_tesoreria'])
		->setCellValue('G'.$i, $fila['fecha_comprobante']);
		
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

	$objPHPExcel->getActiveSheet()->getStyle('A1:G1')->applyFromArray($estiloNombresColumnas);
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:G".($i-1));
	$objPHPExcel->getActiveSheet()->getStyle('A1:A'.($i-1))->applyFromArray($estilo_centrar);

	// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
	for($i = 'A'; $i <= 'Z'; $i++)
	{
	    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}

	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('Mesa de Partes - Asignación');
	  
	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);
	
	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Reporte Asignación Caja Chica Pagados de Solicitudes '.$param_fecha_desde.' AL '.$param_fecha_hasta.'.xls" ');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$excel_path = '/var/www/html/files_bucket/mepa/descargas/tesoreria/rpt_asignacion/Reporte Asignación Caja Chica Pagados de Solicitudes '.$param_fecha_desde.' AL '.$param_fecha_hasta.'.xls';
	$excel_path_download = '/files_bucket/mepa/descargas/tesoreria/rpt_asignacion/Reporte Asignación Caja Chica Pagados de Solicitudes '.$param_fecha_desde.' AL '.$param_fecha_hasta.'.xls';

	try 
	{
		$objWriter->save($excel_path);
	} 
	catch (Exception $e)
	{
		echo json_encode(["error" => $e]);
		exit;
	}

	echo json_encode(array(
		"ruta_archivo" => $excel_path_download
	));
	exit;
}

if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_reporte_tesoreria_listar_liquidacion")
{
	$tipo_solicitud = $_POST['param_tipo_solicitud'];

	$param_fecha_desde = $_POST['param_fecha_desde'];
	$param_fecha_desde = date("Y-m-d", strtotime($param_fecha_desde));

	$param_fecha_hasta = $_POST['param_fecha_hasta'];
	$param_fecha_hasta = date("Y-m-d", strtotime($param_fecha_hasta));

	$param_situacion_contabilidad = $_POST['param_situacion_contabilidad'];
	$param_situacion_tesoreria = $_POST['param_situacion_tesoreria'];

	$login_usuario_id = $login?$login['id']:null;

	$query_pendiente_pago = "";
	$query_pagado = "";
	$query_todos = "";
	$suma_total = 0;

	$where_situacion_contabilidad = "";

	// INICIO: VERIFICAR RED
	
	$where_redes = "";

	$select_red =
    "
        SELECT
            l.red_id
        FROM tbl_usuarios_locales ul
            INNER JOIN tbl_locales l
            ON ul.local_id = l.id
        WHERE ul.usuario_id = '".$login_usuario_id."'
        	AND ul.estado = 1 AND l.red_id IS NOT NULL
        GROUP BY l.red_id
    ";

    $data_select_red = $mysqli->query($select_red);

    $row_count_data_select_red = $data_select_red->num_rows;

    $ids_data_select_red = '';
    $contador_ids = 0;
    
    if ($row_count_data_select_red > 0) 
    {
        while ($row = $data_select_red->fetch_assoc()) 
        {
            if ($contador_ids > 0) 
            {
                $ids_data_select_red .= ',';
            }

            $ids_data_select_red .= $row["red_id"];           
            $contador_ids++;
        }

        $where_redes = " AND rse.red_id IN ($ids_data_select_red) ";
    }

	// FIN: VERIFICAR RED

	if($param_situacion_contabilidad == 1)
	{
		// PENDIENTE
		$where_situacion_contabilidad = " AND l.situacion_etapa_id_contabilidad = 1 ";
	}
	else if($param_situacion_contabilidad == 6)
	{
		// APROBADO
		$where_situacion_contabilidad = " AND l.situacion_etapa_id_contabilidad = 6 ";
	}
	else if($param_situacion_contabilidad == 1)
	{
		// RECHAZADO
		$where_situacion_contabilidad = " AND l.situacion_etapa_id_contabilidad = 7 ";
	}

	if($param_situacion_tesoreria == 10 || $param_situacion_tesoreria == 0)
	{
		//PENDIENTE DE PAGO
		$query_pendiente_pago = "
			SELECT
				a.id, a.usuario_asignado_id, l.id,
				concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado,
				tp.dni AS usuario_asignado_dni,
				z.nombre AS zona,
			    l.num_correlativo, l.created_at AS fecha_solicitud,
			    l.total_rendicion AS total_liquidacion,
			    m.monto_cierre AS total_movilidad,
			    ec.situacion AS situacion_contabilidad,
				et.situacion AS situacion_tesoreria,
				'' AS fecha_comprobante
			FROM mepa_caja_chica_liquidacion l
				INNER JOIN mepa_asignacion_caja_chica a
				ON l.asignacion_id = a.id
				LEFT JOIN mepa_caja_chica_movilidad m
				ON l.id_movilidad = m.id
				INNER JOIN tbl_usuarios tu
				ON a.usuario_asignado_id = tu.id
				INNER JOIN tbl_personal_apt tp
				ON tu.personal_id = tp.id
				INNER JOIN tbl_razon_social rse
				ON tp.razon_social_id = rse.id
				INNER JOIN mepa_zona_asignacion z
				ON a.zona_asignacion_id = z.id
				INNER JOIN cont_etapa ec
				ON l.situacion_etapa_id_contabilidad = ec.etapa_id
				INNER JOIN cont_etapa et
				ON l.situacion_etapa_id_tesoreria = et.etapa_id
			WHERE l.status = 1 
				".$where_redes." 
				".$where_situacion_contabilidad."
				AND l.situacion_etapa_id_tesoreria = 10 
				AND DATE_FORMAT(a.created_at, '%Y-%m-%d') BETWEEN '".$param_fecha_desde."' AND '".$param_fecha_hasta."'
		";

		$list_query = $mysqli->query($query_pendiente_pago);
		
	}

	if($param_situacion_tesoreria == 11 || $param_situacion_tesoreria == 0)
	{
		//PAGADO
		$query_pagado = "
			SELECT
				a.id, a.usuario_asignado_id, l.id,
				concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado,
				tp.dni AS usuario_asignado_dni,
				z.nombre AS zona,
			    l.num_correlativo, l.created_at AS fecha_solicitud,
			    l.total_rendicion AS total_liquidacion,
			    m.monto_cierre AS total_movilidad,
			    ec.situacion AS situacion_contabilidad,
				et.situacion AS situacion_tesoreria,
				p.fecha_comprobante
			FROM mepa_caja_chica_liquidacion l
				INNER JOIN mepa_asignacion_caja_chica a
				ON l.asignacion_id = a.id
				LEFT JOIN mepa_caja_chica_movilidad m
				ON l.id_movilidad = m.id
				INNER JOIN tbl_usuarios tu
				ON a.usuario_asignado_id = tu.id
				INNER JOIN tbl_personal_apt tp
				ON tu.personal_id = tp.id
				INNER JOIN tbl_razon_social rse
				ON tp.razon_social_id = rse.id
				INNER JOIN mepa_zona_asignacion z
				ON a.zona_asignacion_id = z.id
				INNER JOIN cont_etapa ec
				ON l.situacion_etapa_id_contabilidad = ec.etapa_id
				INNER JOIN cont_etapa et
				ON l.situacion_etapa_id_tesoreria = et.etapa_id
				INNER JOIN mepa_caja_chica_programacion_detalle pd
				ON pd.nombre_tabla_id = l.id
				INNER JOIN mepa_caja_chica_programacion p
				ON pd.mepa_caja_chica_programacion_id = p.id
			WHERE pd.status = 1 AND p.tipo_solicitud_id = 2 
				AND l.status = 1 
				".$where_redes." 
				".$where_situacion_contabilidad."
				AND l.situacion_etapa_id_tesoreria = 11 
				AND DATE_FORMAT(l.created_at, '%Y-%m-%d') BETWEEN '".$param_fecha_desde."' AND '".$param_fecha_hasta."'
		";

		$list_query = $mysqli->query($query_pagado);	
	}
	if($param_situacion_tesoreria == 0)
	{
		$query_todos.= $query_pendiente_pago;
		$query_todos.= "UNION ALL";
		$query_todos.= $query_pagado;

		$list_query = $mysqli->query($query_todos);
	}

	$data =  array();
	$num = 1;

	while($reg = $list_query->fetch_object()) 
	{
		$suma_total = $reg->total_liquidacion + $reg->total_movilidad;

		$data[] = array(
			"0" => $num,
			"1" => $reg->usuario_asignado,
			"2" => $reg->usuario_asignado_dni,
			"3" => $reg->zona,
			"4" => $reg->num_correlativo,
			"5" => "S/ ".$suma_total,
			"6" => $reg->situacion_contabilidad,
			"7" => $reg->situacion_tesoreria,
			"8" => $reg->fecha_comprobante
		);

		$num++;
	}

	$resultado = array(
		"sEcho" => 1,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);

	echo json_encode($resultado);
}

if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_reporte_tesoreria_liquidacion_pagados_export")
{
	$tipo_solicitud = $_POST['param_tipo_solicitud'];

	$param_fecha_desde = $_POST['param_fecha_desde'];
	$param_fecha_desde = date("Y-m-d", strtotime($param_fecha_desde));

	$param_fecha_hasta = $_POST['param_fecha_hasta'];
	$param_fecha_hasta = date("Y-m-d", strtotime($param_fecha_hasta));

	$param_situacion_contabilidad = $_POST['param_situacion_contabilidad'];
	$param_situacion_tesoreria = $_POST['param_situacion_tesoreria'];

	$login_usuario_id = $login?$login['id']:null;

	$query_pendiente_pago = "";
	$query_pagado = "";
	$query_todos = "";
	$suma_total = 0;

	$where_situacion_contabilidad = "";

	// INICIO: VERIFICAR RED
	
	$where_redes = "";

	$select_red =
    "
        SELECT
            l.red_id
        FROM tbl_usuarios_locales ul
            INNER JOIN tbl_locales l
            ON ul.local_id = l.id
        WHERE ul.usuario_id = '".$login_usuario_id."'
        	AND ul.estado = 1 AND l.red_id IS NOT NULL
        GROUP BY l.red_id
    ";

    $data_select_red = $mysqli->query($select_red);

    $row_count_data_select_red = $data_select_red->num_rows;

    $ids_data_select_red = '';
    $contador_ids = 0;
    
    if ($row_count_data_select_red > 0) 
    {
        while ($row = $data_select_red->fetch_assoc()) 
        {
            if ($contador_ids > 0) 
            {
                $ids_data_select_red .= ',';
            }

            $ids_data_select_red .= $row["red_id"];           
            $contador_ids++;
        }

        $where_redes = " AND rse.red_id IN ($ids_data_select_red) ";
    }

	// FIN: VERIFICAR RED

	if($param_situacion_contabilidad == 1)
	{
		// PENDIENTE
		$where_situacion_contabilidad = " AND l.situacion_etapa_id_contabilidad = 1 ";
	}
	else if($param_situacion_contabilidad == 6)
	{
		// APROBADO
		$where_situacion_contabilidad = " AND l.situacion_etapa_id_contabilidad = 6 ";
	}
	else if($param_situacion_contabilidad == 1)
	{
		// RECHAZADO
		$where_situacion_contabilidad = " AND l.situacion_etapa_id_contabilidad = 7 ";
	}

	if($param_situacion_tesoreria == 10 || $param_situacion_tesoreria == 0)
	{
		//PENDIENTE DE PAGO
		$query_pendiente_pago = "
			SELECT
				a.id, a.usuario_asignado_id, l.id,
				concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado,
				tp.dni AS usuario_asignado_dni,
				z.nombre AS zona,
			    l.num_correlativo,
			    l.total_rendicion AS total_liquidacion,
			    m.monto_cierre AS total_movilidad,
			    ec.situacion AS situacion_contabilidad,
				et.situacion AS situacion_tesoreria,
				'' AS fecha_comprobante
			FROM mepa_caja_chica_liquidacion l
				INNER JOIN mepa_asignacion_caja_chica a
				ON l.asignacion_id = a.id
				LEFT JOIN mepa_caja_chica_movilidad m
				ON l.id_movilidad = m.id
				INNER JOIN tbl_usuarios tu
				ON a.usuario_asignado_id = tu.id
				INNER JOIN tbl_personal_apt tp
				ON tu.personal_id = tp.id
				INNER JOIN tbl_razon_social rse
				ON tp.razon_social_id = rse.id
				INNER JOIN mepa_zona_asignacion z
				ON a.zona_asignacion_id = z.id
				INNER JOIN cont_etapa ec
				ON l.situacion_etapa_id_contabilidad = ec.etapa_id
				INNER JOIN cont_etapa et
				ON l.situacion_etapa_id_tesoreria = et.etapa_id
			WHERE l.status = 1 
				".$where_redes." 
				".$where_situacion_contabilidad."
				AND l.situacion_etapa_id_tesoreria = 10 
				AND DATE_FORMAT(a.created_at, '%Y-%m-%d') BETWEEN '".$param_fecha_desde."' AND '".$param_fecha_hasta."'
		";

		$list_query = $mysqli->query($query_pendiente_pago);
		
	}
	if($param_situacion_tesoreria == 11 || $param_situacion_tesoreria == 0)
	{
		//PAGADO
		$query_pagado = "
			SELECT
				a.id, a.usuario_asignado_id, l.id,
				concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado,
				tp.dni AS usuario_asignado_dni,
				z.nombre AS zona,
			    l.num_correlativo,
			    l.total_rendicion AS total_liquidacion,
			    m.monto_cierre AS total_movilidad,
			    ec.situacion AS situacion_contabilidad,
				et.situacion AS situacion_tesoreria,
				p.fecha_comprobante
			FROM mepa_caja_chica_liquidacion l
				INNER JOIN mepa_asignacion_caja_chica a
				ON l.asignacion_id = a.id
				LEFT JOIN mepa_caja_chica_movilidad m
				ON l.id_movilidad = m.id
				INNER JOIN tbl_usuarios tu
				ON a.usuario_asignado_id = tu.id
				INNER JOIN tbl_personal_apt tp
				ON tu.personal_id = tp.id
				INNER JOIN tbl_razon_social rse
				ON tp.razon_social_id = rse.id
				INNER JOIN mepa_zona_asignacion z
				ON a.zona_asignacion_id = z.id
				INNER JOIN cont_etapa ec
				ON l.situacion_etapa_id_contabilidad = ec.etapa_id
				INNER JOIN cont_etapa et
				ON l.situacion_etapa_id_tesoreria = et.etapa_id
				INNER JOIN mepa_caja_chica_programacion_detalle pd
				ON pd.nombre_tabla_id = l.id
				INNER JOIN mepa_caja_chica_programacion p
				ON pd.mepa_caja_chica_programacion_id = p.id
			WHERE pd.status = 1 AND p.tipo_solicitud_id = 2 
				AND l.status = 1 
				".$where_redes." 
				".$where_situacion_contabilidad."
				AND l.situacion_etapa_id_tesoreria = 11 
				AND DATE_FORMAT(l.created_at, '%Y-%m-%d') BETWEEN '".$param_fecha_desde."' AND '".$param_fecha_hasta."'
		";

		$list_query = $mysqli->query($query_pagado);	
	}
	if($param_situacion_tesoreria == 0)
	{
		$query_todos.= $query_pendiente_pago;
		$query_todos.= "UNION ALL";
		$query_todos.= $query_pagado;

		$list_query = $mysqli->query($query_todos);
	}

	//PROCEDEMOS A CREAR LA CARPETASI EN CASO NO EXISTA
	$path = "/var/www/html/files_bucket/mepa/descargas/tesoreria/rpt_liquidacion/";

	if (!is_dir($path)) 
	{
		mkdir($path, 0777, true);
	}

	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/mepa/descargas/tesoreria/rpt_liquidacion/*'); //obtenemos todos los nombres de los ficheros
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

	$tituloReporte = "Reporte Liquidación Caja Chica Pagados de Solicitudes '".$param_fecha_desde."' AL '".$param_fecha_hasta."' ";

	$titulosColumnas = array('Nº', 'USUARIO ASIGNADO', 'DNI', 'ZONA', 'CORRELATIVO', 'SUB TOTAL', 'SITUACION CONTABILIDAD', 'SITUACION TESORERIA', 'FECHA COMPROBANTE DE PAGO');

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
    ->setCellValue('I1', $titulosColumnas[8]);

    //Se agregan los datos a la lista del reporte
	$cont = 0;

	$i = 2; //Numero de fila donde se va a comenzar a rellenar los datos
	while ($fila = $list_query->fetch_array()) 
	{
		$cont ++;

		$suma_total = $fila['total_liquidacion'] + $fila['total_movilidad'];

		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('A'.$i, $cont)
		->setCellValue('B'.$i, $fila['usuario_asignado'])
		->setCellValue('C'.$i, $fila['usuario_asignado_dni'])
		->setCellValue('D'.$i, $fila['zona'])
		->setCellValue('E'.$i, $fila['num_correlativo'])
		->setCellValue('F'.$i, $suma_total)
		->setCellValue('G'.$i, $fila['situacion_contabilidad'])
		->setCellValue('H'.$i, $fila['situacion_tesoreria'])
		->setCellValue('I'.$i, $fila['fecha_comprobante']);
		
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

	$objPHPExcel->getActiveSheet()->getStyle('A1:I1')->applyFromArray($estiloNombresColumnas);
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:I".($i-1));
	$objPHPExcel->getActiveSheet()->getStyle('A1:A'.($i-1))->applyFromArray($estilo_centrar);

	// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
	for($i = 'A'; $i <= 'Z'; $i++)
	{
	    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}

	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('Mesa de Partes - Liquidación');
	  
	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);
	
	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Reporte Liquidación Caja Chica Pagados de Solicitudes '.$param_fecha_desde.' AL '.$param_fecha_hasta.'.xls" ');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$excel_path = '/var/www/html/files_bucket/mepa/descargas/tesoreria/rpt_liquidacion/Reporte Liquidación Caja Chica Pagados de Solicitudes '.$param_fecha_desde.' AL '.$param_fecha_hasta.'.xls';
	$excel_path_download = '/files_bucket/mepa/descargas/tesoreria/rpt_liquidacion/Reporte Liquidación Caja Chica Pagados de Solicitudes '.$param_fecha_desde.' AL '.$param_fecha_hasta.'.xls';

	try 
	{
		$objWriter->save($excel_path);
	} 
	catch (Exception $e)
	{
		echo json_encode(["error" => $e]);
		exit;
	}

	echo json_encode(array(
		"ruta_archivo" => $excel_path_download
	));
	exit;
}

?>