<?php

date_default_timezone_set("America/Lima");

$result = array();

include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);

if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_reporte_contabilidad_listar_liquidacion") {
	$mepa_reporte_contabilidad_param_liquidacion_fecha_desde = $_POST['mepa_reporte_contabilidad_param_liquidacion_fecha_desde'];
	$param_liquidacion_fecha_desde = date("Y-m-d", strtotime($mepa_reporte_contabilidad_param_liquidacion_fecha_desde));

	$mepa_reporte_contabilidad_param_liquidacion_fecha_hasta = $_POST['mepa_reporte_contabilidad_param_liquidacion_fecha_hasta'];
	$param_liquidacion_fecha_hasta = date("Y-m-d", strtotime($mepa_reporte_contabilidad_param_liquidacion_fecha_hasta));

	$param_liquidacion_situacion = $_POST['mepa_reporte_contabilidad_param_liquidacion_situacion'];

	$login_usuario_id = $login ? $login['id'] : null;

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
        WHERE ul.usuario_id = '" . $login_usuario_id . "'
        	AND ul.estado = 1 AND l.red_id IS NOT NULL
        GROUP BY l.red_id
    ";

	$data_select_red = $mysqli->query($select_red);

	$row_count_data_select_red = $data_select_red->num_rows;

	$ids_data_select_red = '';
	$contador_ids = 0;

	if ($row_count_data_select_red > 0) {
		while ($row = $data_select_red->fetch_assoc()) {
			if ($contador_ids > 0) {
				$ids_data_select_red .= ',';
			}

			$ids_data_select_red .= $row["red_id"];
			$contador_ids++;
		}

		$where_redes = " AND rse.red_id IN ($ids_data_select_red) ";
	}

	// FIN: VERIFICAR RED

	if ($param_liquidacion_situacion != 0) {
		$where_situacion_contabilidad = " AND l.situacion_etapa_id_contabilidad = '" . $param_liquidacion_situacion . "' ";
	} else {
		$where_situacion_contabilidad = " AND l.situacion_etapa_id_contabilidad IN (1, 6, 7) ";
	}

	$query = "
		SELECT
			l.id,
			l.num_correlativo,
			l.solicitante_usuario_id,
		    rz.nombre AS empresa,
			concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS usuario_solicitante,
			z.nombre AS zona,
			l.total_rendicion AS total_liquidacion,
			l.se_aplica_movilidad, l.id_movilidad, m.monto_cierre AS total_movilidad,
			l.created_at AS fecha_solicitud,
		    l.situacion_etapa_id_contabilidad, cec.situacion AS estado_solicitud,
		    IF(l.etapa_id_se_envio_a_tesoreria = 9, 'SI', 'NO') AS se_envio_a_tesoreria
		FROM mepa_caja_chica_liquidacion l
			INNER JOIN mepa_asignacion_caja_chica a
			ON l.asignacion_id = a.id
			INNER JOIN tbl_usuarios tu
			ON l.solicitante_usuario_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN tbl_razon_social rse
			ON tp.razon_social_id = rse.id
			LEFT JOIN mepa_caja_chica_movilidad m
			ON l.id_movilidad = m.id
		    INNER JOIN cont_etapa cec
			ON l.situacion_etapa_id_contabilidad = cec.etapa_id
			INNER JOIN tbl_razon_social rz
			ON a.empresa_id = rz.id
			INNER JOIN mepa_zona_asignacion z
			ON a.zona_asignacion_id = z.id
		WHERE l.status = 1 
			" . $where_redes . " 
			AND l.situacion_etapa_id_superior = 6
		 	AND DATE_FORMAT(l.created_at, '%Y-%m-%d') BETWEEN '" . $param_liquidacion_fecha_desde . "' AND '" . $param_liquidacion_fecha_hasta . "'
		 	" . $where_situacion_contabilidad . "
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while ($reg = $list_query->fetch_object()) {
		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->empresa,
			"2" => $reg->zona,
			"3" => $reg->usuario_solicitante,
			"4" => $reg->fecha_solicitud,
			"5" => $reg->num_correlativo,
			"6" => "S/ " . $reg->total_liquidacion,
			"7" => ($reg->se_aplica_movilidad == 1) ? "S/ " . $reg->total_movilidad : "S/ 0.00",
			"8" => $reg->estado_solicitud,
			"9" => $reg->se_envio_a_tesoreria
		);
	}

	$resultado = array(
		"sEcho" => 1,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);

	echo json_encode($resultado);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_reporte_contabilidad_caja_chica_export") {
	$mepa_reporte_contabilidad_param_liquidacion_fecha_desde = $_POST['mepa_reporte_contabilidad_param_liquidacion_fecha_desde'];
	$param_liquidacion_fecha_desde = date("Y-m-d", strtotime($mepa_reporte_contabilidad_param_liquidacion_fecha_desde));

	$mepa_reporte_contabilidad_param_liquidacion_fecha_hasta = $_POST['mepa_reporte_contabilidad_param_liquidacion_fecha_hasta'];
	$param_liquidacion_fecha_hasta = date("Y-m-d", strtotime($mepa_reporte_contabilidad_param_liquidacion_fecha_hasta));

	$param_liquidacion_situacion = $_POST['mepa_reporte_contabilidad_param_liquidacion_situacion'];

	$login_usuario_id = $login ? $login['id'] : null;

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
        WHERE ul.usuario_id = '" . $login_usuario_id . "'
        	AND ul.estado = 1 AND l.red_id IS NOT NULL
        GROUP BY l.red_id
    ";

	$data_select_red = $mysqli->query($select_red);

	$row_count_data_select_red = $data_select_red->num_rows;

	$ids_data_select_red = '';
	$contador_ids = 0;

	if ($row_count_data_select_red > 0) {
		while ($row = $data_select_red->fetch_assoc()) {
			if ($contador_ids > 0) {
				$ids_data_select_red .= ',';
			}

			$ids_data_select_red .= $row["red_id"];
			$contador_ids++;
		}

		$where_redes = " AND rse.red_id IN ($ids_data_select_red) ";
	}

	// FIN: VERIFICAR RED

	if ($param_liquidacion_situacion != 0) {
		$where_situacion_contabilidad = " AND l.situacion_etapa_id_contabilidad = '" . $param_liquidacion_situacion . "' ";
	} else {
		$where_situacion_contabilidad = " AND l.situacion_etapa_id_contabilidad IN (1, 6, 7) ";
	}

	$query = "
		SELECT
			l.id,
			l.num_correlativo,
			l.solicitante_usuario_id,
			rz.nombre AS empresa,
			concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS usuario_solicitante,
			z.nombre AS zona,
			l.total_rendicion AS total_liquidacion,
			l.se_aplica_movilidad, l.id_movilidad, m.monto_cierre AS total_movilidad,
			l.created_at AS fecha_solicitud,
		    l.situacion_etapa_id_contabilidad, cec.situacion AS estado_solicitud,
		    IF(l.etapa_id_se_envio_a_tesoreria = 9, 'SI', 'NO') AS se_envio_a_tesoreria
		FROM mepa_caja_chica_liquidacion l
			INNER JOIN mepa_asignacion_caja_chica a
			ON l.asignacion_id = a.id
			INNER JOIN tbl_usuarios tu
			ON l.solicitante_usuario_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN tbl_razon_social rse
			ON tp.razon_social_id = rse.id
			LEFT JOIN mepa_caja_chica_movilidad m
			ON l.id_movilidad = m.id
		    INNER JOIN cont_etapa cec
			ON l.situacion_etapa_id_contabilidad = cec.etapa_id
			INNER JOIN tbl_razon_social rz
			ON a.empresa_id = rz.id
			INNER JOIN mepa_zona_asignacion z
			ON a.zona_asignacion_id = z.id
		WHERE l.status = 1 
			" . $where_redes . " 
			AND l.situacion_etapa_id_superior = 6
		 	AND DATE_FORMAT(l.created_at, '%Y-%m-%d') BETWEEN '" . $param_liquidacion_fecha_desde . "' AND '" . $param_liquidacion_fecha_hasta . "'
		 	" . $where_situacion_contabilidad . "
	";


	$list_query = $mysqli->query($query);

	//PROCEDEMOS A CREAR LA CARPETASI EN CASO NO EXISTA
	$path = "/var/www/html/files_bucket/mepa/descargas/contabilidad/rpt_caja_chica_aprobadas_no_aprobadas/";

	if (!is_dir($path)) {
		mkdir($path, 0777, true);
	}

	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/mepa/descargas/contabilidad/rpt_caja_chica_aprobadas_no_aprobadas/*'); //obtenemos todos los nombres de los ficheros
	foreach ($files as $file) {
		if (is_file($file))
			unlink($file); //elimino el fichero
	}


	require_once '../phpexcel/classes/PHPExcel.php';
	$objPHPExcel = new PHPExcel();

	// Se asignan las propiedades del libro
	$objPHPExcel->getProperties()->setCreator("AT") // Nombre del autor
		->setDescription("Reporte"); //Descripción

	$tituloReporte = "Reporte Caja Chica - Liquidación";

	$titulosColumnas = array('Nº', 'EMPRESA', 'ZONA', 'USUARIO SOLICITANTE', 'Nº CORRELATIVO', 'TOTAL LIQUIDACIÓN', '¿APLICA MOVILIDAD?', 'TOTAL MOVILIDAD', 'SITUACION', 'FECHA SOLICITUD', 'ENVIADO A TESORERÍA');

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
		->setCellValue('K1', $titulosColumnas[10]);

	//Se agregan los datos a la lista del reporte
	$se_aplica_movilidad = '';
	$cont = 0;

	$i = 2; //Numero de fila donde se va a comenzar a rellenar los datos
	while ($fila = $list_query->fetch_array()) {
		$cont++;

		if ($fila['se_aplica_movilidad'] == 1) {
			$se_aplica_movilidad = 'Si';
		} else {
			$se_aplica_movilidad = 'No';
		}

		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A' . $i, $cont)
			->setCellValue('B' . $i, $fila['empresa'])
			->setCellValue('C' . $i, $fila['zona'])
			->setCellValue('D' . $i, $fila['usuario_solicitante'])
			->setCellValue('E' . $i, $fila['num_correlativo'])
			->setCellValue('F' . $i, $fila['total_liquidacion'])
			->setCellValue('G' . $i, $se_aplica_movilidad)
			->setCellValue('H' . $i, $fila['total_movilidad'])
			->setCellValue('I' . $i, $fila['estado_solicitud'])
			->setCellValue('J' . $i, $fila['fecha_solicitud'])
			->setCellValue('K' . $i, $fila['se_envio_a_tesoreria']);

		$i++;
	}

	$estiloNombresColumnas = array(
		'font' => array(
			'name'      => 'Calibri',
			'bold'      => true,
			'italic'    => false,
			'strike'    => false,
			'size' => 10,
			'color'     => array(
				'rgb' => 'ffffff'
			)
		),
		'fill' => array(
			'type'  => PHPExcel_Style_Fill::FILL_SOLID,
			'color' => array(
				'rgb' => '000000'
			)
		),
		'alignment' =>  array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			'wrap'      => false
		)
	);

	$estiloInformacion = new PHPExcel_Style();
	$estiloInformacion->applyFromArray(array(
		'font' => array(
			'name'  => 'Arial',
			'color' => array(
				'rgb' => '000000'
			)
		)
	));

	$estilo_centrar = array(
		'alignment' =>  array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			'wrap'      => false
		)
	);

	$objPHPExcel->getActiveSheet()->getStyle('A1:K1')->applyFromArray($estiloNombresColumnas);
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:K" . ($i - 1));
	$objPHPExcel->getActiveSheet()->getStyle('A1:A' . ($i - 1))->applyFromArray($estilo_centrar);

	// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
	for ($i = 'A'; $i <= 'Z'; $i++) {
		$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}

	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('Mesa de Partes - Liquidación');

	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);

	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Caja Chica Liquidación AT.xls');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$excel_path = '/var/www/html/files_bucket/mepa/descargas/contabilidad/rpt_caja_chica_aprobadas_no_aprobadas/Caja Chica Liquidación AT.xls';
	$excel_path_download = '/files_bucket/mepa/descargas//contabilidad/rpt_caja_chica_aprobadas_no_aprobadas/Caja Chica Liquidación AT.xls';

	try {
		$objWriter->save($excel_path);
	} catch (PHPExcel_Writer_Exception $e) {
		echo json_encode(["error" => $e]);
		exit;
	}

	echo json_encode(array(
		"ruta_archivo" => $excel_path_download
	));
	exit;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_reporte_contabilidad_listar_asignacion") {
	$mepa_reporte_contabilidad_param_fecha_desde = $_POST['mepa_reporte_contabilidad_param_fecha_desde'];
	$fecha_desde = date("Y-m-d", strtotime($mepa_reporte_contabilidad_param_fecha_desde));

	$mepa_reporte_contabilidad_param_fecha_hasta = $_POST['mepa_reporte_contabilidad_param_fecha_hasta'];
	$fecha_hasta = date("Y-m-d", strtotime($mepa_reporte_contabilidad_param_fecha_hasta));

	$login_usuario_id = $login ? $login['id'] : null;

	// INICIO: VERIFICAR RED

	$where_redes = "";

	$select_red =
		"
        SELECT
            l.red_id
        FROM tbl_usuarios_locales ul
            INNER JOIN tbl_locales l
            ON ul.local_id = l.id
        WHERE ul.usuario_id = '" . $login_usuario_id . "'
        	AND ul.estado = 1 AND l.red_id IS NOT NULL
        GROUP BY l.red_id
    ";

	$data_select_red = $mysqli->query($select_red);

	$row_count_data_select_red = $data_select_red->num_rows;

	$ids_data_select_red = '';
	$contador_ids = 0;

	if ($row_count_data_select_red > 0) {
		while ($row = $data_select_red->fetch_assoc()) {
			if ($contador_ids > 0) {
				$ids_data_select_red .= ',';
			}

			$ids_data_select_red .= $row["red_id"];
			$contador_ids++;
		}

		$where_redes = " AND rse.red_id IN ($ids_data_select_red) ";
	}

	// FIN: VERIFICAR RED

	$query = "
		SELECT
			a.id, a.usuario_asignado_id,
			concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado,
			tp.dni AS usuario_asignado_dni, a.fondo_asignado, a.created_at AS fecha_solicitud,
			z.nombre AS zona,
		    rz.nombre AS empresa,
		    e.situacion,
		    (
				SELECT
					SUM((IFNULL(l.total_rendicion, 0) + IFNULL(m.monto_cierre, 0))) AS total
				FROM mepa_caja_chica_liquidacion l
					LEFT JOIN mepa_caja_chica_movilidad m
					ON l.id_movilidad = m.id
				WHERE l.asignacion_id = a.id AND l.situacion_etapa_id_tesoreria = 10 AND l.status = 1

			) AS total_liquidacion_solicitado,
		    (
		    	SELECT
					SUM((IFNULL(l.total_rendicion, 0) + IFNULL(m.monto_cierre, 0))) AS total
				FROM mepa_caja_chica_liquidacion l
					LEFT JOIN mepa_caja_chica_movilidad m
					ON l.id_movilidad = m.id
				WHERE l.asignacion_id = a.id 
					AND l.situacion_etapa_id_contabilidad = 6 
					AND l.situacion_etapa_id_tesoreria = 10 AND l.status = 1

		    ) AS total_aprobado_contabilidad
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
			ON a.situacion_etapa_id = e.etapa_id
			INNER JOIN tbl_razon_social rz
			ON a.empresa_id = rz.id
		WHERE a.status = 1 
			" . $where_redes . " 
			AND DATE_FORMAT(a.created_at, '%Y-%m-%d') BETWEEN '" . $fecha_desde . "' AND '" . $fecha_hasta . "'
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while ($reg = $list_query->fetch_object()) {
		$total_rembolso = ($reg->total_aprobado_contabilidad + $reg->total_liquidacion_solicitado);
		$saldo_caja = $reg->fondo_asignado - $reg->total_liquidacion_solicitado;

		$porcentaje = ($saldo_caja * 100) / $reg->fondo_asignado;

		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->empresa,
			"2" => $reg->zona,
			"3" => $reg->usuario_asignado,
			"4" => $reg->fecha_solicitud,
			"5" => "S/ " . number_format($reg->fondo_asignado, 2, '.', ','),
			"6" => "S/ " . number_format($reg->total_liquidacion_solicitado, 2, '.', ','),
			"7" => "S/ " . number_format($reg->total_aprobado_contabilidad, 2, '.', ','),
			"8" => "S/ " . number_format($saldo_caja, 2, '.', ','),
			"9" => (number_format($porcentaje, 2, '.', ',') > 50)
				?
				"<span class='badge badge-success'>" . number_format($porcentaje, 2, '.', ',') . " %" . "</span>"
				:
				"<span class='badge badge-danger'>" . number_format($porcentaje, 2, '.', ',') . " %" . "</span>"
		);
	}

	$resultado = array(
		"sEcho" => 1,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);

	echo json_encode($resultado);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_reporte_contabilidad_tipo_rpt_cajas_chicas_export") {
	$param_fecha_inicio = $_POST['param_fecha_inicio'];
	$fecha_inicio = date("Y-m-d", strtotime($param_fecha_inicio));

	$param_fecha_fin = $_POST['param_fecha_fin'];
	$fecha_fin = date("Y-m-d", strtotime($param_fecha_fin));

	$login_usuario_id = $login ? $login['id'] : null;

	// INICIO: VERIFICAR RED

	$where_redes = "";

	$select_red =
		"
        SELECT
            l.red_id
        FROM tbl_usuarios_locales ul
            INNER JOIN tbl_locales l
            ON ul.local_id = l.id
        WHERE ul.usuario_id = '" . $login_usuario_id . "'
        	AND ul.estado = 1 AND l.red_id IS NOT NULL
        GROUP BY l.red_id
    ";

	$data_select_red = $mysqli->query($select_red);

	$row_count_data_select_red = $data_select_red->num_rows;

	$ids_data_select_red = '';
	$contador_ids = 0;

	if ($row_count_data_select_red > 0) {
		while ($row = $data_select_red->fetch_assoc()) {
			if ($contador_ids > 0) {
				$ids_data_select_red .= ',';
			}

			$ids_data_select_red .= $row["red_id"];
			$contador_ids++;
		}

		$where_redes = " AND rse.red_id IN ($ids_data_select_red) ";
	}

	// FIN: VERIFICAR RED

	$query =
		"
		SELECT
			a.id, a.usuario_asignado_id,
			concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado,
			tp.dni AS usuario_asignado_dni, a.fondo_asignado, a.created_at AS fecha_solicitud,
			z.nombre AS zona,
		    rz.nombre AS empresa,
		    e.situacion,
		    (
				SELECT
					SUM((IFNULL(l.total_rendicion, 0) + IFNULL(m.monto_cierre, 0))) AS total
				FROM mepa_caja_chica_liquidacion l
					LEFT JOIN mepa_caja_chica_movilidad m
					ON l.id_movilidad = m.id
				WHERE l.asignacion_id = a.id AND l.situacion_etapa_id_tesoreria = 10 AND l.status = 1

			) AS total_liquidacion_solicitado,
		    (
		    	SELECT
					SUM((IFNULL(l.total_rendicion, 0) + IFNULL(m.monto_cierre, 0))) AS total
				FROM mepa_caja_chica_liquidacion l
					LEFT JOIN mepa_caja_chica_movilidad m
					ON l.id_movilidad = m.id
				WHERE l.asignacion_id = a.id 
					AND l.situacion_etapa_id_contabilidad = 6 
					AND l.situacion_etapa_id_tesoreria = 10 AND l.status = 1

		    ) AS total_aprobado_contabilidad
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
			ON a.situacion_etapa_id = e.etapa_id
			INNER JOIN tbl_razon_social rz
			ON a.empresa_id = rz.id
		WHERE a.status = 1 
			" . $where_redes . " 
			AND DATE_FORMAT(a.created_at, '%Y-%m-%d') BETWEEN '" . $fecha_inicio . "' AND '" . $fecha_fin . "'
	";

	$list_query = $mysqli->query($query);

	//PROCEDEMOS A CREAR LA CARPETASI EN CASO NO EXISTA
	$path = "/var/www/html/files_bucket/mepa/descargas/contabilidad/rpt_asignacion/";

	if (!is_dir($path)) {
		mkdir($path, 0777, true);
	}

	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/mepa/descargas/contabilidad/rpt_asignacion/*'); //obtenemos todos los nombres de los ficheros
	foreach ($files as $file) {
		if (is_file($file))
			unlink($file); //elimino el fichero
	}

	require_once '../phpexcel/classes/PHPExcel.php';
	$objPHPExcel = new PHPExcel();

	// Se asignan las propiedades del libro
	$objPHPExcel->getProperties()->setCreator("AT") // Nombre del autor
		->setDescription("Reporte"); //Descripción

	$tituloReporte = "Reporte - Asignación";

	$titulosColumnas = array('Nº', 'EMPRESA', 'ZONA', 'SOLICITANTE', 'FECHA SOLICITUD', 'FONDO ASIGNADO', 'APROBADO + PENDIENTE', 'APROBADO CONTABILIDAD', 'SALDO', '% SALDO');

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
		->setCellValue('J1', $titulosColumnas[9]);


	//Se agregan los datos a la lista del reporte
	$sub_total = 0;
	$cont = 0;

	$i = 2; //Numero de fila donde se va a comenzar a rellenar los datos
	while ($fila = $list_query->fetch_array()) {
		$cont++;

		$saldo_caja = $fila['fondo_asignado'] - $fila['total_liquidacion_solicitado'];
		$porcentaje = ($saldo_caja * 100) / $fila['fondo_asignado'];

		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A' . $i, $cont)
			->setCellValue('B' . $i, $fila['empresa'])
			->setCellValue('C' . $i, $fila['zona'])
			->setCellValue('D' . $i, $fila['usuario_asignado'])
			->setCellValue('E' . $i, $fila['fecha_solicitud'])
			->setCellValue('F' . $i, "S/ " . number_format($fila['fondo_asignado'], 2, '.', ','))
			->setCellValue('G' . $i, "S/ " . number_format($fila['total_liquidacion_solicitado'], 2, '.', ','))
			->setCellValue('H' . $i, "S/ " . number_format($fila['total_aprobado_contabilidad'], 2, '.', ','))
			->setCellValue('I' . $i, "S/ " . number_format($saldo_caja, 2, '.', ','))
			->setCellValue('J' . $i, number_format($porcentaje, 2, '.', ',') . " %");

		$i++;
	}

	$estiloNombresColumnas = array(
		'font' => array(
			'name'      => 'Calibri',
			'bold'      => true,
			'italic'    => false,
			'strike'    => false,
			'size' => 10,
			'color'     => array(
				'rgb' => 'ffffff'
			)
		),
		'fill' => array(
			'type'  => PHPExcel_Style_Fill::FILL_SOLID,
			'color' => array(
				'rgb' => '000000'
			)
		),
		'alignment' =>  array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			'wrap'      => false
		)
	);

	$estiloInformacion = new PHPExcel_Style();
	$estiloInformacion->applyFromArray(array(
		'font' => array(
			'name'  => 'Arial',
			'color' => array(
				'rgb' => '000000'
			)
		)
	));

	$estilo_centrar = array(
		'alignment' =>  array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			'wrap'      => false
		)
	);

	$objPHPExcel->getActiveSheet()->getStyle('A1:J1')->applyFromArray($estiloNombresColumnas);
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:J" . ($i - 1));
	$objPHPExcel->getActiveSheet()->getStyle('A1:A' . ($i - 1))->applyFromArray($estilo_centrar);

	// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
	for ($i = 'A'; $i <= 'Z'; $i++) {
		$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}

	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('Mesa de Partes - Asignación');

	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);

	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Caja Chica Fisico Virtual AT.xls');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$excel_path = '/var/www/html/files_bucket/mepa/descargas/contabilidad/rpt_asignacion/Caja Chica Asignación AT.xls';
	$excel_path_download = '/files_bucket/mepa/descargas/contabilidad/rpt_asignacion/Caja Chica Asignación AT.xls';

	try {
		$objWriter->save($excel_path);
	} catch (PHPExcel_Writer_Exception $e) {
		echo json_encode(["error" => $e]);
		exit;
	}

	echo json_encode(array(
		"ruta_archivo" => $excel_path_download
	));
	exit;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_reporte_contabilidad_listar_caja_chica_fisico_virtual") {
	$mepa_reporte_contabilidad_param_liquidacion_fecha_desde = $_POST['mepa_reporte_contabilidad_param_liquidacion_fecha_desde'];
	$param_liquidacion_fecha_desde = date("Y-m-d", strtotime($mepa_reporte_contabilidad_param_liquidacion_fecha_desde));

	$mepa_reporte_contabilidad_param_liquidacion_fecha_hasta = $_POST['mepa_reporte_contabilidad_param_liquidacion_fecha_hasta'];
	$param_liquidacion_fecha_hasta = date("Y-m-d", strtotime($mepa_reporte_contabilidad_param_liquidacion_fecha_hasta));

	$param_liquidacion_flag_envio_documento = $_POST['mepa_reporte_contabilidad_param_flag_envio_documento_fisico'];

	$login_usuario_id = $login ? $login['id'] : null;

	$where_flag_envio_documento = "";

	// INICIO: VERIFICAR RED

	$where_redes = "";

	$select_red =
		"
        SELECT
            l.red_id
        FROM tbl_usuarios_locales ul
            INNER JOIN tbl_locales l
            ON ul.local_id = l.id
        WHERE ul.usuario_id = '" . $login_usuario_id . "'
        	AND ul.estado = 1 AND l.red_id IS NOT NULL
        GROUP BY l.red_id
    ";

	$data_select_red = $mysqli->query($select_red);

	$row_count_data_select_red = $data_select_red->num_rows;

	$ids_data_select_red = '';
	$contador_ids = 0;

	if ($row_count_data_select_red > 0) {
		while ($row = $data_select_red->fetch_assoc()) {
			if ($contador_ids > 0) {
				$ids_data_select_red .= ',';
			}

			$ids_data_select_red .= $row["red_id"];
			$contador_ids++;
		}

		$where_redes = " AND rse.red_id IN ($ids_data_select_red) ";
	}

	// FIN: VERIFICAR RED

	if ($param_liquidacion_flag_envio_documento == 1) {
		//PENDIENTE
		$where_flag_envio_documento = "AND l.flag_envio_documento_fisico = 0";
	} else if ($param_liquidacion_flag_envio_documento == 2) {
		//OK (SI ENVIO)
		$where_flag_envio_documento = "AND l.flag_envio_documento_fisico = 1";
	}

	$query = "
		SELECT
			l.id,
		    a.user_created_id AS jefe_inmediato_id,
		    concat(IFNULL(tpj.nombre, ''),' ', IFNULL(tpj.apellido_paterno, ''),' ', IFNULL(tpj.apellido_materno, '')) AS jefe_inmediato,
			l.num_correlativo,
			l.solicitante_usuario_id,
			concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS solicitante,
		    tp.dni AS solicitante_dni,
		    a.fondo_asignado, a.saldo_disponible,
		    z.nombre AS zona_asignacion,
			l.total_rendicion AS total_liquidacion,
			l.id_movilidad, 
			IFNULL(m.monto_cierre, 0) AS total_movilidad,
			l.created_at AS fecha_solicitud,
			l.flag_envio_documento_fisico,
			p.fecha_carga_comprobante
		FROM mepa_caja_chica_liquidacion l
			INNER JOIN mepa_asignacion_caja_chica a
		    ON l.asignacion_id = a.id
		    INNER JOIN mepa_zona_asignacion z
		    ON a.zona_asignacion_id = z.id
		    INNER JOIN tbl_usuarios tuj
			ON a.user_created_id = tuj.id
			INNER JOIN tbl_personal_apt tpj
			ON tuj.personal_id = tpj.id
		    INNER JOIN tbl_usuarios tu
			ON l.solicitante_usuario_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN tbl_razon_social rse
			ON tp.razon_social_id = rse.id
		    LEFT JOIN mepa_caja_chica_movilidad m
			ON l.id_movilidad = m.id
			LEFT JOIN mepa_caja_chica_programacion_detalle pd
		    ON l.id = pd.nombre_tabla_id AND pd.nombre_tabla = 'mepa_liquidacion_caja_chica'
		    LEFT JOIN mepa_caja_chica_programacion p
		    ON pd.mepa_caja_chica_programacion_id = p.id
		WHERE l.status = 1 AND l.situacion_etapa_id_contabilidad = 6 
			" . $where_redes . " 
			AND DATE_FORMAT(l.created_at, '%Y-%m-%d') BETWEEN '" . $param_liquidacion_fecha_desde . "' AND '" . $param_liquidacion_fecha_hasta . "'
		 		" . $where_flag_envio_documento . "
		ORDER BY a.user_created_id
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$sub_total = 0;
	$data =  array();

	while ($reg = $list_query->fetch_object()) {
		$sub_total = $reg->total_liquidacion + $reg->total_movilidad;

		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->jefe_inmediato,
			"2" => $reg->solicitante,
			"3" => $reg->solicitante_dni,
			"4" => $reg->zona_asignacion,
			"5" => "S/ " . $reg->fondo_asignado,
			"6" => $reg->num_correlativo,
			"7" => $reg->fecha_carga_comprobante,
			"8" => "S/ " . number_format($sub_total, 2, '.', ','),
			"9" => ($reg->flag_envio_documento_fisico == 1) ? "Ok" : "Pendiente"
		);
	}

	$resultado = array(
		"sEcho" => 1,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);

	echo json_encode($resultado);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_reporte_contabilidad_caja_chica_fisico_virtual_export") {
	$mepa_reporte_contabilidad_param_liquidacion_fecha_desde = $_POST['mepa_reporte_contabilidad_param_liquidacion_fecha_desde'];
	$param_liquidacion_fecha_desde = date("Y-m-d", strtotime($mepa_reporte_contabilidad_param_liquidacion_fecha_desde));

	$mepa_reporte_contabilidad_param_liquidacion_fecha_hasta = $_POST['mepa_reporte_contabilidad_param_liquidacion_fecha_hasta'];
	$param_liquidacion_fecha_hasta = date("Y-m-d", strtotime($mepa_reporte_contabilidad_param_liquidacion_fecha_hasta));

	$param_liquidacion_flag_envio_documento = $_POST['mepa_reporte_contabilidad_param_flag_envio_documento_fisico'];

	$login_usuario_id = $login ? $login['id'] : null;

	$where_flag_envio_documento = "";

	// INICIO: VERIFICAR RED

	$where_redes = "";

	$select_red =
		"
        SELECT
            l.red_id
        FROM tbl_usuarios_locales ul
            INNER JOIN tbl_locales l
            ON ul.local_id = l.id
        WHERE ul.usuario_id = '" . $login_usuario_id . "'
        	AND ul.estado = 1 AND l.red_id IS NOT NULL
        GROUP BY l.red_id
    ";

	$data_select_red = $mysqli->query($select_red);

	$row_count_data_select_red = $data_select_red->num_rows;

	$ids_data_select_red = '';
	$contador_ids = 0;

	if ($row_count_data_select_red > 0) {
		while ($row = $data_select_red->fetch_assoc()) {
			if ($contador_ids > 0) {
				$ids_data_select_red .= ',';
			}

			$ids_data_select_red .= $row["red_id"];
			$contador_ids++;
		}

		$where_redes = " AND rse.red_id IN ($ids_data_select_red) ";
	}

	// FIN: VERIFICAR RED

	if ($param_liquidacion_flag_envio_documento == 1) {
		//PENDIENTE
		$where_flag_envio_documento = "AND l.flag_envio_documento_fisico = 0";
	} else if ($param_liquidacion_flag_envio_documento == 2) {
		//OK (SI ENVIO)
		$where_flag_envio_documento = "AND l.flag_envio_documento_fisico = 1";
	}

	$query = "
		SELECT
			l.id,
		    a.user_created_id AS jefe_inmediato_id,
		    concat(IFNULL(tpj.nombre, ''),' ', IFNULL(tpj.apellido_paterno, ''),' ', IFNULL(tpj.apellido_materno, '')) AS jefe_inmediato,
			l.num_correlativo,
			l.solicitante_usuario_id,
			concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS solicitante,
		    tp.dni AS solicitante_dni,
		    a.fondo_asignado, a.saldo_disponible,
		    z.nombre AS zona_asignacion,
			l.total_rendicion AS total_liquidacion,
			l.id_movilidad, 
			IFNULL(m.monto_cierre, 0) AS total_movilidad,
			l.created_at AS fecha_solicitud,
			l.flag_envio_documento_fisico,
			p.fecha_carga_comprobante
		FROM mepa_caja_chica_liquidacion l
			INNER JOIN mepa_asignacion_caja_chica a
		    ON l.asignacion_id = a.id
		    INNER JOIN mepa_zona_asignacion z
		    ON a.zona_asignacion_id = z.id
		    INNER JOIN tbl_usuarios tuj
			ON a.user_created_id = tuj.id
			INNER JOIN tbl_personal_apt tpj
			ON tuj.personal_id = tpj.id
		    INNER JOIN tbl_usuarios tu
			ON l.solicitante_usuario_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN tbl_razon_social rse
			ON tp.razon_social_id = rse.id
		    LEFT JOIN mepa_caja_chica_movilidad m
			ON l.id_movilidad = m.id
			LEFT JOIN mepa_caja_chica_programacion_detalle pd
		    ON l.id = pd.nombre_tabla_id AND pd.nombre_tabla = 'mepa_liquidacion_caja_chica'
		    LEFT JOIN mepa_caja_chica_programacion p
		    ON pd.mepa_caja_chica_programacion_id = p.id
		WHERE l.status = 1 AND l.situacion_etapa_id_contabilidad = 6 
			" . $where_redes . " 
			AND DATE_FORMAT(l.created_at, '%Y-%m-%d') BETWEEN '" . $param_liquidacion_fecha_desde . "' AND '" . $param_liquidacion_fecha_hasta . "'
		 	" . $where_flag_envio_documento . "
		ORDER BY a.user_created_id
	";


	$list_query = $mysqli->query($query);

	//PROCEDEMOS A CREAR LA CARPETASI EN CASO NO EXISTA
	$path = "/var/www/html/files_bucket/mepa/descargas/contabilidad/rpt_caja_chica_fisico_virtual/";

	if (!is_dir($path)) {
		mkdir($path, 0777, true);
	}

	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/mepa/descargas/contabilidad/rpt_caja_chica_fisico_virtual/*'); //obtenemos todos los nombres de los ficheros
	foreach ($files as $file) {
		if (is_file($file))
			unlink($file); //elimino el fichero
	}


	require_once '../phpexcel/classes/PHPExcel.php';
	$objPHPExcel = new PHPExcel();

	// Se asignan las propiedades del libro
	$objPHPExcel->getProperties()->setCreator("AT") // Nombre del autor
		->setDescription("Reporte"); //Descripción

	$tituloReporte = "Reporte Caja Chica - Fisico Virtual";

	$titulosColumnas = array('Nº', 'JEFE INMEDIATO', 'SOLICITANTE', 'DNI', 'ZONA', 'ASIGNACIÓN', 'Nº CAJA', 'FECHA REEMBOLSO', 'LIQUIDACIÓN', 'ESTADO');

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
		->setCellValue('J1', $titulosColumnas[9]);

	//Se agregan los datos a la lista del reporte
	$documento_fisico = '';
	$sub_total = 0;
	$cont = 0;

	$i = 2; //Numero de fila donde se va a comenzar a rellenar los datos
	while ($fila = $list_query->fetch_array()) {
		$cont++;

		if ($fila['flag_envio_documento_fisico'] == 1) {
			$documento_fisico = 'Ok';
		} else {
			$documento_fisico = 'Pendiente';
		}

		$sub_total = $fila['total_liquidacion'] + $fila['total_movilidad'];

		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A' . $i, $cont)
			->setCellValue('B' . $i, $fila['jefe_inmediato'])
			->setCellValue('C' . $i, $fila['solicitante'])
			->setCellValue('D' . $i, $fila['solicitante_dni'])
			->setCellValue('E' . $i, $fila['zona_asignacion'])
			->setCellValue('F' . $i, "S/ " . number_format($fila['fondo_asignado'], 2, '.', ','))
			->setCellValue('G' . $i, $fila['num_correlativo'])
			->setCellValue('H' . $i, $fila['fecha_carga_comprobante'])
			->setCellValue('I' . $i, "S/ " . number_format($sub_total, 2, '.', ','))
			->setCellValue('J' . $i, $documento_fisico);

		$i++;
	}

	$estiloNombresColumnas = array(
		'font' => array(
			'name'      => 'Calibri',
			'bold'      => true,
			'italic'    => false,
			'strike'    => false,
			'size' => 10,
			'color'     => array(
				'rgb' => 'ffffff'
			)
		),
		'fill' => array(
			'type'  => PHPExcel_Style_Fill::FILL_SOLID,
			'color' => array(
				'rgb' => '000000'
			)
		),
		'alignment' =>  array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			'wrap'      => false
		)
	);

	$estiloInformacion = new PHPExcel_Style();
	$estiloInformacion->applyFromArray(array(
		'font' => array(
			'name'  => 'Arial',
			'color' => array(
				'rgb' => '000000'
			)
		)
	));

	$estilo_centrar = array(
		'alignment' =>  array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			'wrap'      => false
		)
	);

	$objPHPExcel->getActiveSheet()->getStyle('A1:J1')->applyFromArray($estiloNombresColumnas);
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:J" . ($i - 1));
	$objPHPExcel->getActiveSheet()->getStyle('A1:A' . ($i - 1))->applyFromArray($estilo_centrar);

	// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
	for ($i = 'A'; $i <= 'Z'; $i++) {
		$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}

	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('Mesa de Partes - Fisico Virtual');

	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);

	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Caja Chica Fisico Virtual AT.xls');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$excel_path = '/var/www/html/files_bucket/mepa/descargas/contabilidad/rpt_caja_chica_fisico_virtual/Caja Chica Fisico Virtual AT.xls';
	$excel_path_download = '/files_bucket/mepa/descargas/contabilidad/rpt_caja_chica_fisico_virtual/Caja Chica Fisico Virtual AT.xls';

	try {
		$objWriter->save($excel_path);
	} catch (PHPExcel_Writer_Exception $e) {
		echo json_encode(["error" => $e]);
		exit;
	}

	echo json_encode(array(
		"ruta_archivo" => $excel_path_download
	));
	exit;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_reporte_contabilidad_listar_sin_caja_chica_realizada") {
	$dias_habiles = $_POST['mepa_reporte_contabilidad_param_dias_habiles'];

	$login_usuario_id = $login ? $login['id'] : null;

	// INICIO: VERIFICAR RED

	$where_redes = "";

	$select_red =
		"
        SELECT
            l.red_id
        FROM tbl_usuarios_locales ul
            INNER JOIN tbl_locales l
            ON ul.local_id = l.id
        WHERE ul.usuario_id = '" . $login_usuario_id . "'
        	AND ul.estado = 1 AND l.red_id IS NOT NULL
        GROUP BY l.red_id
    ";

	$data_select_red = $mysqli->query($select_red);

	$row_count_data_select_red = $data_select_red->num_rows;

	$ids_data_select_red = '';
	$contador_ids = 0;

	if ($row_count_data_select_red > 0) {
		while ($row = $data_select_red->fetch_assoc()) {
			if ($contador_ids > 0) {
				$ids_data_select_red .= ',';
			}

			$ids_data_select_red .= $row["red_id"];
			$contador_ids++;
		}

		$where_redes = " AND rse.red_id IN ($ids_data_select_red) ";
	}

	// FIN: VERIFICAR RED

	$query = "
		SELECT
			a.id,
		    a.user_created_id AS jefe_inmediato_creacion,
		    concat(IFNULL(tpj.nombre, ''),' ', IFNULL(tpj.apellido_paterno, ''),' ', IFNULL(tpj.apellido_materno, '')) AS jefe_inmediato,
		    tr.nombre AS empresa, z.nombre AS zona,
		    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS solicitante,
		    tp.dni AS solicitante_dni,
		    a.fondo_asignado,
		    a.fecha_asignado,
		    p.fecha_carga_comprobante AS fecha_deposito,
		    count(l.asignacion_id) AS cant_liquidacion
		FROM mepa_asignacion_caja_chica a
			INNER JOIN tbl_razon_social tr
			ON a.empresa_id = tr.id
			INNER JOIN mepa_zona_asignacion z
			ON a.zona_asignacion_id = z.id
			INNER JOIN tbl_usuarios tuj
			ON a.user_created_id = tuj.id
			INNER JOIN tbl_personal_apt tpj
			ON tuj.personal_id = tpj.id
			INNER JOIN tbl_usuarios tu
			ON a.usuario_asignado_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN tbl_razon_social rse
			ON tp.razon_social_id = rse.id
			INNER JOIN mepa_caja_chica_programacion_detalle pd
			ON a.id = pd.nombre_tabla_id
			INNER JOIN mepa_caja_chica_programacion p
			ON pd.mepa_caja_chica_programacion_id = p.id
			LEFT JOIN mepa_caja_chica_liquidacion l
			ON a.id = l.asignacion_id
		WHERE pd.nombre_tabla = 'mepa_asignacion_caja_chica'  
			AND DATE(NOW()) > DATE(DATE_ADD(a.fecha_asignado, INTERVAL $dias_habiles DAY))
			" . $where_redes . " 
		GROUP BY a.id
		HAVING count(l.asignacion_id) = 0
		ORDER BY a.user_created_id, a.id DESC
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while ($reg = $list_query->fetch_object()) {
		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->jefe_inmediato,
			"2" => $reg->empresa,
			"3" => $reg->zona,
			"4" => $reg->solicitante,
			"5" => $reg->solicitante_dni,
			"6" => "S/ " . number_format($reg->fondo_asignado, 2, '.', ','),
			"7" => $reg->fecha_asignado,
			"8" => $reg->fecha_deposito,
			"9" => $reg->cant_liquidacion
		);
	}

	$resultado = array(
		"sEcho" => 1,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);

	echo json_encode($resultado);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_reporte_contabilidad_sin_caja_chica_realizada_export") {
	$param_dias_habiles = $_POST['mepa_reporte_contabilidad_param_dias_habiles'];

	$login_usuario_id = $login ? $login['id'] : null;

	// INICIO: VERIFICAR RED

	$where_redes = "";

	$select_red =
		"
        SELECT
            l.red_id
        FROM tbl_usuarios_locales ul
            INNER JOIN tbl_locales l
            ON ul.local_id = l.id
        WHERE ul.usuario_id = '" . $login_usuario_id . "'
        	AND ul.estado = 1 AND l.red_id IS NOT NULL
        GROUP BY l.red_id
    ";

	$data_select_red = $mysqli->query($select_red);

	$row_count_data_select_red = $data_select_red->num_rows;

	$ids_data_select_red = '';
	$contador_ids = 0;

	if ($row_count_data_select_red > 0) {
		while ($row = $data_select_red->fetch_assoc()) {
			if ($contador_ids > 0) {
				$ids_data_select_red .= ',';
			}

			$ids_data_select_red .= $row["red_id"];
			$contador_ids++;
		}

		$where_redes = " AND rse.red_id IN ($ids_data_select_red) ";
	}

	// FIN: VERIFICAR RED

	$query = "
		SELECT
			a.id,
		    a.user_created_id AS jefe_inmediato_creacion,
		    concat(IFNULL(tpj.nombre, ''),' ', IFNULL(tpj.apellido_paterno, ''),' ', IFNULL(tpj.apellido_materno, '')) AS jefe_inmediato,
		    tr.nombre AS empresa, z.nombre AS zona,
		    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS solicitante,
		    tp.dni AS solicitante_dni,
		    a.fondo_asignado,
		    a.fecha_asignado,
		    p.fecha_carga_comprobante AS fecha_deposito,
		    count(l.asignacion_id) AS cant_liquidacion
		FROM mepa_asignacion_caja_chica a
			INNER JOIN tbl_razon_social tr
			ON a.empresa_id = tr.id
			INNER JOIN mepa_zona_asignacion z
			ON a.zona_asignacion_id = z.id
			INNER JOIN tbl_usuarios tuj
			ON a.user_created_id = tuj.id
			INNER JOIN tbl_personal_apt tpj
			ON tuj.personal_id = tpj.id
			INNER JOIN tbl_usuarios tu
			ON a.usuario_asignado_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN tbl_razon_social rse
			ON tp.razon_social_id = rse.id
			INNER JOIN mepa_caja_chica_programacion_detalle pd
			ON a.id = pd.nombre_tabla_id
			INNER JOIN mepa_caja_chica_programacion p
			ON pd.mepa_caja_chica_programacion_id = p.id
			LEFT JOIN mepa_caja_chica_liquidacion l
			ON a.id = l.asignacion_id
		WHERE pd.nombre_tabla = 'mepa_asignacion_caja_chica' 
			AND DATE(NOW()) > DATE(DATE_ADD(a.fecha_asignado, INTERVAL $param_dias_habiles DAY))
			" . $where_redes . " 
		GROUP BY a.id
		HAVING count(l.asignacion_id) = 0
		ORDER BY a.user_created_id, a.id DESC
	";


	$list_query = $mysqli->query($query);

	//PROCEDEMOS A CREAR LA CARPETASI EN CASO NO EXISTA
	$path = "/var/www/html/files_bucket/mepa/descargas/contabilidad/rpt_sin_caja_chica_realizada/";

	if (!is_dir($path)) {
		mkdir($path, 0777, true);
	}

	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/mepa/descargas/contabilidad/rpt_sin_caja_chica_realizada/*'); //obtenemos todos los nombres de los ficheros
	foreach ($files as $file) {
		if (is_file($file))
			unlink($file); //elimino el fichero
	}


	require_once '../phpexcel/classes/PHPExcel.php';
	$objPHPExcel = new PHPExcel();

	// Se asignan las propiedades del libro
	$objPHPExcel->getProperties()->setCreator("AT") // Nombre del autor
		->setDescription("Reporte"); //Descripción

	$tituloReporte = "Reporte Caja Chica - Sin Caja Chica Realizada";

	$titulosColumnas = array('Nº', 'JEFE INMEDIATO', 'EMPRESA', 'ZONA', 'SOLICITANTE', 'DNI', 'ASIGNACIÓN', 'FECHA SOLICITUD ASIGNADO', 'FECHA DEPOSITO', 'CANTIDAD CAJA CHICA');

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
		->setCellValue('J1', $titulosColumnas[9]);

	//Se agregan los datos a la lista del reporte
	$cont = 0;

	$i = 2; //Numero de fila donde se va a comenzar a rellenar los datos
	while ($fila = $list_query->fetch_array()) {
		$cont++;

		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A' . $i, $cont)
			->setCellValue('B' . $i, $fila['jefe_inmediato'])
			->setCellValue('C' . $i, $fila['empresa'])
			->setCellValue('D' . $i, $fila['zona'])
			->setCellValue('E' . $i, $fila['solicitante'])
			->setCellValue('F' . $i, $fila['solicitante_dni'])
			->setCellValue('G' . $i, "S/ " . number_format($fila['fondo_asignado'], 2, '.', ','))
			->setCellValue('H' . $i, $fila['fecha_asignado'])
			->setCellValue('I' . $i, $fila['fecha_deposito'])
			->setCellValue('J' . $i, $fila['cant_liquidacion']);

		$i++;
	}

	$estiloNombresColumnas = array(
		'font' => array(
			'name'      => 'Calibri',
			'bold'      => true,
			'italic'    => false,
			'strike'    => false,
			'size' => 10,
			'color'     => array(
				'rgb' => 'ffffff'
			)
		),
		'fill' => array(
			'type'  => PHPExcel_Style_Fill::FILL_SOLID,
			'color' => array(
				'rgb' => '000000'
			)
		),
		'alignment' =>  array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			'wrap'      => false
		)
	);

	$estiloInformacion = new PHPExcel_Style();
	$estiloInformacion->applyFromArray(array(
		'font' => array(
			'name'  => 'Arial',
			'color' => array(
				'rgb' => '000000'
			)
		)
	));

	$estilo_centrar = array(
		'alignment' =>  array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			'wrap'      => false
		)
	);

	$objPHPExcel->getActiveSheet()->getStyle('A1:J1')->applyFromArray($estiloNombresColumnas);
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:J" . ($i - 1));
	$objPHPExcel->getActiveSheet()->getStyle('A1:A' . ($i - 1))->applyFromArray($estilo_centrar);

	// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
	for ($i = 'A'; $i <= 'Z'; $i++) {
		$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}

	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('Mesa de Partes - Sin Caja Chica Realizada');

	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);

	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Sin Caja Chica Realizada AT.xls');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$excel_path = '/var/www/html/files_bucket/mepa/descargas/contabilidad/rpt_sin_caja_chica_realizada/Sin Caja Chica Realizada AT.xls';
	$excel_path_download = '/files_bucket/mepa/descargas/contabilidad/rpt_sin_caja_chica_realizada/Sin Caja Chica Realizada AT.xls';

	try {
		$objWriter->save($excel_path);
	} catch (PHPExcel_Writer_Exception $e) {
		echo json_encode(["error" => $e]);
		exit;
	}

	echo json_encode(array(
		"ruta_archivo" => $excel_path_download
	));
	exit;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_reporte_contabilidad_listar_gastos_proveedores") {
	$param_liquidacion_fecha_desde = $_POST['mepa_reporte_contabilidad_param_liquidacion_fecha_desde'];
	$fecha_desde = date("Y-m-d", strtotime($param_liquidacion_fecha_desde));

	$param_liquidacion_fecha_hasta = $_POST['mepa_reporte_contabilidad_param_liquidacion_fecha_hasta'];
	$fecha_hasta = date("Y-m-d", strtotime($param_liquidacion_fecha_hasta));

	$param_liquidacion_situacion = $_POST['mepa_reporte_contabilidad_param_liquidacion_situacion_gastos_proveedor'];

	$login_usuario_id = $login ? $login['id'] : null;

	$where_situacion_contabilidad = "";
	$where_fecha_liquidacion = "";

	// INICIO: VERIFICAR RED

	$where_redes = "";

	$select_red =
		"
        SELECT
            l.red_id
        FROM tbl_usuarios_locales ul
            INNER JOIN tbl_locales l
            ON ul.local_id = l.id
        WHERE ul.usuario_id = '" . $login_usuario_id . "'
        	AND ul.estado = 1 AND l.red_id IS NOT NULL
        GROUP BY l.red_id
    ";

	$data_select_red = $mysqli->query($select_red);

	$row_count_data_select_red = $data_select_red->num_rows;

	$ids_data_select_red = '';
	$contador_ids = 0;

	if ($row_count_data_select_red > 0) {
		while ($row = $data_select_red->fetch_assoc()) {
			if ($contador_ids > 0) {
				$ids_data_select_red .= ',';
			}

			$ids_data_select_red .= $row["red_id"];
			$contador_ids++;
		}

		$where_redes = " AND rse.red_id IN ($ids_data_select_red) ";
	}

	// FIN: VERIFICAR RED

	if ($param_liquidacion_situacion != 0) {
		$where_situacion_contabilidad = " AND l.situacion_etapa_id_tesoreria = '" . $param_liquidacion_situacion . "' ";
	} else {
		$where_situacion_contabilidad = " AND l.situacion_etapa_id_tesoreria IN (10,11) ";
	}

	if (!empty($param_liquidacion_fecha_desde) && !empty($param_liquidacion_fecha_hasta)) {
		$where_fecha_liquidacion = " AND l.created_at BETWEEN '" . $fecha_desde . " 00:00:00' AND '" . $fecha_hasta . " 23:59:59' ";
	} elseif (!empty($param_liquidacion_fecha_desde) && empty($param_liquidacion_fecha_hasta)) {
		$where_fecha_liquidacion = " AND l.created_at >= '" . $fecha_desde . " 00:00:00' ";
	} elseif (!empty($param_liquidacion_fecha_hasta) && empty($param_liquidacion_fecha_desde)) {
		$where_fecha_liquidacion = " AND l.created_at <= '" . $fecha_hasta . " 23:59:59' ";
	}

	$query = "
		SELECT 
			dl.ruc,
			td.cuenta_contable, td.nombre AS motivo, 
			SUM(dl.importe) AS suma_importe,
			l.fecha_desde,
			l.fecha_hasta,
			l.situacion_etapa_id_tesoreria,
			cec.nombre as estado_tesoreria
		FROM mepa_detalle_caja_chica_liquidacion dl
			INNER JOIN mepa_caja_chica_liquidacion l
			ON dl.mepa_caja_chica_liquidacion_id = l.id
			INNER JOIN mepa_asignacion_caja_chica a
			ON l.asignacion_id = a.id
			INNER JOIN tbl_usuarios tu
			ON a.usuario_asignado_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN tbl_razon_social rse
			ON tp.razon_social_id = rse.id
			INNER JOIN mepa_tipo_documento td
			ON dl.codigo_provision_contable = td.id
			INNER JOIN cont_etapa cec
			ON l.situacion_etapa_id_tesoreria = cec.etapa_id
		WHERE 
			l.status = 1 
			AND l.situacion_etapa_id_contabilidad = 6 
			AND dl.status = 1 
			AND dl.ruc IS NOT NULL 
			$where_redes
			$where_fecha_liquidacion
			$where_situacion_contabilidad
		GROUP BY dl.ruc, td.cuenta_contable
	;";
	$list_query = $mysqli->query($query);

	$data =  array();
	$num = 1;

	while ($reg = $list_query->fetch_object()) {
		$data[] = array(
			"0" => $num,
			"1" => $reg->fecha_desde,
			"2" => $reg->fecha_hasta,
			"3" => $reg->ruc,
			"4" => $reg->estado_tesoreria,
			"5" => $reg->motivo,
			"6" => "S/ " . number_format($reg->suma_importe, 2, '.', ',')
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
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_reporte_contabilidad_listar_gastos_proveedores_export") {

	$param_liquidacion_fecha_desde = $_POST['mepa_reporte_contabilidad_param_liquidacion_fecha_desde'];
	$fecha_desde = date("Y-m-d", strtotime($param_liquidacion_fecha_desde));

	$param_liquidacion_fecha_hasta = $_POST['mepa_reporte_contabilidad_param_liquidacion_fecha_hasta'];
	$fecha_hasta = date("Y-m-d", strtotime($param_liquidacion_fecha_hasta));

	$param_liquidacion_situacion = $_POST['mepa_reporte_contabilidad_param_liquidacion_situacion_gastos_proveedor'];

	$login_usuario_id = $login ? $login['id'] : null;

	$where_situacion_contabilidad = "";
	$where_fecha_liquidacion = "";

	// INICIO: VERIFICAR RED

	$where_redes = "";

	$select_red =
		"
        SELECT
            l.red_id
        FROM tbl_usuarios_locales ul
            INNER JOIN tbl_locales l
            ON ul.local_id = l.id
        WHERE ul.usuario_id = '" . $login_usuario_id . "'
        	AND ul.estado = 1 AND l.red_id IS NOT NULL
        GROUP BY l.red_id
    ";

	$data_select_red = $mysqli->query($select_red);

	$row_count_data_select_red = $data_select_red->num_rows;

	$ids_data_select_red = '';
	$contador_ids = 0;

	if ($row_count_data_select_red > 0) {
		while ($row = $data_select_red->fetch_assoc()) {
			if ($contador_ids > 0) {
				$ids_data_select_red .= ',';
			}

			$ids_data_select_red .= $row["red_id"];
			$contador_ids++;
		}

		$where_redes = " AND rse.red_id IN ($ids_data_select_red) ";
	}

	// FIN: VERIFICAR RED

	if ($param_liquidacion_situacion != 0) {
		$where_situacion_contabilidad = " AND l.situacion_etapa_id_tesoreria = '" . $param_liquidacion_situacion . "' ";
	} else {
		$where_situacion_contabilidad = " AND l.situacion_etapa_id_tesoreria IN (10,11) ";
	}

	if (!empty($param_liquidacion_fecha_desde) && !empty($param_liquidacion_fecha_hasta)) {
		$where_fecha_liquidacion = " AND l.created_at BETWEEN '" . $fecha_desde . " 00:00:00' AND '" . $fecha_hasta . " 23:59:59' ";
	} elseif (!empty($param_liquidacion_fecha_desde) && empty($param_liquidacion_fecha_hasta)) {
		$where_fecha_liquidacion = " AND l.created_at >= '" . $fecha_desde . " 00:00:00' ";
	} elseif (!empty($param_liquidacion_fecha_hasta) && empty($param_liquidacion_fecha_desde)) {
		$where_fecha_liquidacion = " AND l.created_at <= '" . $fecha_hasta . " 23:59:59' ";
	}

	$query = "
		SELECT 
			dl.ruc,
			td.cuenta_contable, td.nombre AS motivo, 
			SUM(dl.importe) AS suma_importe,
			l.fecha_desde,
			l.fecha_hasta,
			l.situacion_etapa_id_tesoreria,
			cec.nombre as estado_tesoreria
		FROM mepa_detalle_caja_chica_liquidacion dl
			INNER JOIN mepa_caja_chica_liquidacion l
			ON dl.mepa_caja_chica_liquidacion_id = l.id
			INNER JOIN mepa_asignacion_caja_chica a
			ON l.asignacion_id = a.id
			INNER JOIN tbl_usuarios tu
			ON a.usuario_asignado_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN tbl_razon_social rse
			ON tp.razon_social_id = rse.id
			INNER JOIN mepa_tipo_documento td
			ON dl.codigo_provision_contable = td.id
			INNER JOIN cont_etapa cec
			ON l.situacion_etapa_id_tesoreria = cec.etapa_id
		WHERE 
			l.status = 1 
			AND l.situacion_etapa_id_contabilidad = 6 
			AND dl.status = 1 
			AND dl.ruc IS NOT NULL 
			$where_redes
			$where_fecha_liquidacion
			$where_situacion_contabilidad
		GROUP BY dl.ruc, td.cuenta_contable
	;";

	$list_query = $mysqli->query($query);

	//PROCEDEMOS A CREAR LA CARPETASI EN CASO NO EXISTA
	$path = "/var/www/html/files_bucket/mepa/descargas/contabilidad/gastos_por_proveedores/";

	if (!is_dir($path)) {
		mkdir($path, 0777, true);
	}

	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/mepa/descargas/contabilidad/gastos_por_proveedores/*'); //obtenemos todos los nombres de los ficheros
	foreach ($files as $file) {
		if (is_file($file))
			unlink($file); //elimino el fichero
	}


	require_once '../phpexcel/classes/PHPExcel.php';
	$objPHPExcel = new PHPExcel();

	// Se asignan las propiedades del libro
	$objPHPExcel->getProperties()->setCreator("AT") // Nombre del autor
		->setDescription("Reporte"); //Descripción

	$tituloReporte = "Reporte Caja Chica - Gastos por Proveedores Caja Chica Realizada";

	$titulosColumnas = array('Nº', 'FECHA DESDE', 'FECHA HASTA', 'PROVEEDOR', 'SITUACIÓN TESORERIA',  'MOTIVO', 'TOTAL');

	// Se agregan los titulos del reporte
	$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('A1', $titulosColumnas[0])  //Titulo de las columnas
		->setCellValue('B1', $titulosColumnas[1])
		->setCellValue('C1', $titulosColumnas[2])
		->setCellValue('D1', $titulosColumnas[3])
		->setCellValue('E1', $titulosColumnas[4])
		->setCellValue('F1', $titulosColumnas[5])
		->setCellValue('G1', $titulosColumnas[6]);;

	//Se agregan los datos a la lista del reporte
	$cont = 0;

	$i = 2; //Numero de fila donde se va a comenzar a rellenar los datos
	while ($fila = $list_query->fetch_array()) {
		$cont++;

		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A' . $i, $cont)
			->setCellValue('B' . $i, $fila['fecha_desde'])
			->setCellValue('C' . $i, $fila['fecha_hasta'])
			->setCellValue('D' . $i, $fila['ruc'])
			->setCellValue('E' . $i, $fila['estado_tesoreria'])
			->setCellValue('F' . $i, $fila['motivo'])
			->setCellValue('G' . $i, "S/ " . number_format($fila['suma_importe'], 2, '.', ','));

		$i++;
	}

	$estiloNombresColumnas = array(
		'font' => array(
			'name'      => 'Calibri',
			'bold'      => true,
			'italic'    => false,
			'strike'    => false,
			'size' => 10,
			'color'     => array(
				'rgb' => 'ffffff'
			)
		),
		'fill' => array(
			'type'  => PHPExcel_Style_Fill::FILL_SOLID,
			'color' => array(
				'rgb' => '000000'
			)
		),
		'alignment' =>  array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			'wrap'      => false
		)
	);

	$estiloInformacion = new PHPExcel_Style();
	$estiloInformacion->applyFromArray(array(
		'font' => array(
			'name'  => 'Arial',
			'color' => array(
				'rgb' => '000000'
			)
		)
	));

	$estilo_centrar = array(
		'alignment' =>  array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			'wrap'      => false
		)
	);

	$objPHPExcel->getActiveSheet()->getStyle('A1:G1')->applyFromArray($estiloNombresColumnas);
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:G" . ($i - 1));
	$objPHPExcel->getActiveSheet()->getStyle('A1:A' . ($i - 1))->applyFromArray($estilo_centrar);
	$objPHPExcel->getActiveSheet()->getStyle('B1:B' . ($i - 1))->applyFromArray($estilo_centrar);
	$objPHPExcel->getActiveSheet()->getStyle('C1:C' . ($i - 1))->applyFromArray($estilo_centrar);
	$objPHPExcel->getActiveSheet()->getStyle('D1:D' . ($i - 1))->applyFromArray($estilo_centrar);
	$objPHPExcel->getActiveSheet()->getStyle('E1:E' . ($i - 1))->applyFromArray($estilo_centrar);
	$objPHPExcel->getActiveSheet()->getStyle('G1:G' . ($i - 1))->applyFromArray($estilo_centrar);

	// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
	for ($i = 'A'; $i <= 'Z'; $i++) {
		$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}

	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('Mesa de Partes - Gastos por Proveedores Caja Chica Realizada');

	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);

	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Sin Caja Chica Realizada AT.xls');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$excel_path = '/var/www/html/files_bucket/mepa/descargas/contabilidad/gastos_por_proveedores/Gastos por Proveedores Caja Chica Realizada AT.xls';
	$excel_path_download = '/files_bucket/mepa/descargas/contabilidad/gastos_por_proveedores/Gastos por Proveedores Caja Chica Realizada AT.xls';

	try {
		$objWriter->save($excel_path);
	} catch (PHPExcel_Writer_Exception $e) {
		echo json_encode(["error" => $e]);
		exit;
	}

	echo json_encode(array(
		"ruta_archivo" => $excel_path_download
	));
	exit;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_reporte_contabilidad_listar_gastos_ultimas_semanas") {
	$login_usuario_id = $login ? $login['id'] : null;

	// INICIO: VERIFICAR RED

	$where_redes = "";

	$select_red =
		"
        SELECT
            l.red_id
        FROM tbl_usuarios_locales ul
            INNER JOIN tbl_locales l
            ON ul.local_id = l.id
        WHERE ul.usuario_id = '" . $login_usuario_id . "'
        	AND ul.estado = 1 AND l.red_id IS NOT NULL
        GROUP BY l.red_id
    ";

	$data_select_red = $mysqli->query($select_red);

	$row_count_data_select_red = $data_select_red->num_rows;

	$ids_data_select_red = '';
	$contador_ids = 0;

	if ($row_count_data_select_red > 0) {
		while ($row = $data_select_red->fetch_assoc()) {
			if ($contador_ids > 0) {
				$ids_data_select_red .= ',';
			}

			$ids_data_select_red .= $row["red_id"];
			$contador_ids++;
		}

		$where_redes = " rse.red_id IN ($ids_data_select_red) ";
	}

	// FIN: VERIFICAR RED

	$fecha_hoy = getdate();

	// OBTENER QUE DIA DE LA SEMANA ES
	$dia_semana_hoy = $fecha_hoy['wday'];

	$num_dia = 0;

	switch ($dia_semana_hoy) {
		case 1:
			// LUNES
			$num_dia = 1;
			break;
		case 2:
			// MARTES
			$num_dia = 2;
			break;
		case 3:
			// MIERCOLES
			$num_dia = 3;
			break;
		case 4:
			// JUEVES
			$num_dia = 4;
			break;
		case 5:
			// VIERNES
			$num_dia = 5;
			break;
		case 6:
			// SABADO
			$num_dia = 6;
			break;
		case 0:
			// DOMINGO
			$num_dia = 7;
			break;

		default:
			$num_dia = 0;
			break;
	}

	$semana_uno_fecha_fin = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $num_dia, date("Y")));
	$semana_uno_fecha_inicio = date("Y-m-d", mktime(0, 0, 0, date("m", strtotime($semana_uno_fecha_fin)), date("d", strtotime($semana_uno_fecha_fin)) - 6, date("Y", strtotime($semana_uno_fecha_fin))));

	$semana_dos_fecha_fin = date("Y-m-d", mktime(0, 0, 0, date("m", strtotime($semana_uno_fecha_inicio)), date("d", strtotime($semana_uno_fecha_inicio)) - 1, date("Y", strtotime($semana_uno_fecha_inicio))));

	$semana_dos_fecha_inicio = date("Y-m-d", mktime(0, 0, 0, date("m", strtotime($semana_dos_fecha_fin)), date("d", strtotime($semana_dos_fecha_fin)) - 6, date("Y", strtotime($semana_dos_fecha_fin))));

	$semana_tres_fecha_fin = date("Y-m-d", mktime(0, 0, 0, date("m", strtotime($semana_dos_fecha_inicio)), date("d", strtotime($semana_dos_fecha_inicio)) - 1, date("Y", strtotime($semana_dos_fecha_inicio))));

	$semana_tres_fecha_inicio = date("Y-m-d", mktime(0, 0, 0, date("m", strtotime($semana_tres_fecha_fin)), date("d", strtotime($semana_tres_fecha_fin)) - 6, date("Y", strtotime($semana_tres_fecha_fin))));

	$semana_cuatro_fecha_fin = date("Y-m-d", mktime(0, 0, 0, date("m", strtotime($semana_tres_fecha_inicio)), date("d", strtotime($semana_tres_fecha_inicio)) - 1, date("Y", strtotime($semana_tres_fecha_inicio))));

	$semana_cuatro_fecha_inicio = date("Y-m-d", mktime(0, 0, 0, date("m", strtotime($semana_cuatro_fecha_fin)), date("d", strtotime($semana_cuatro_fecha_fin)) - 6, date("Y", strtotime($semana_cuatro_fecha_fin))));

	$semana_cinco_fecha_fin = date("Y-m-d", mktime(0, 0, 0, date("m", strtotime($semana_cuatro_fecha_inicio)), date("d", strtotime($semana_cuatro_fecha_inicio)) - 1, date("Y", strtotime($semana_cuatro_fecha_inicio))));

	$semana_cinco_fecha_inicio = date("Y-m-d", mktime(0, 0, 0, date("m", strtotime($semana_cinco_fecha_fin)), date("d", strtotime($semana_cinco_fecha_fin)) - 6, date("Y", strtotime($semana_cinco_fecha_fin))));


	$query = "
		SELECT
			a.id,
		    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado,
		    a.zona_asignacion_id, z.nombre AS zona,
		    (
				SELECT
					SUM(dl.importe)
				FROM mepa_detalle_caja_chica_liquidacion dl
					INNER JOIN mepa_caja_chica_liquidacion l
					ON l.id = dl.mepa_caja_chica_liquidacion_id
					INNER JOIN mepa_asignacion_caja_chica ai
					ON l.asignacion_id = ai.id
		        WHERE ai.id = a.id AND dl.status = 1 
	        		AND dl.fecha_documento BETWEEN '" . $semana_uno_fecha_inicio . "' AND '" . $semana_uno_fecha_fin . "'
		    ) AS semana_uno_importe,
		    (
				SELECT
					SUM(dl.importe)
				FROM mepa_detalle_caja_chica_liquidacion dl
					INNER JOIN mepa_caja_chica_liquidacion l
					ON l.id = dl.mepa_caja_chica_liquidacion_id
					INNER JOIN mepa_asignacion_caja_chica ai
					ON l.asignacion_id = ai.id
		        WHERE ai.id = a.id AND dl.status = 1 
	        		AND dl.fecha_documento BETWEEN '" . $semana_dos_fecha_inicio . "' AND '" . $semana_dos_fecha_fin . "'
		    ) AS semana_dos_importe,
		    (
				SELECT
					SUM(dl.importe)
				FROM mepa_detalle_caja_chica_liquidacion dl
					INNER JOIN mepa_caja_chica_liquidacion l
					ON l.id = dl.mepa_caja_chica_liquidacion_id
					INNER JOIN mepa_asignacion_caja_chica ai
					ON l.asignacion_id = ai.id
		        WHERE ai.id = a.id AND dl.status = 1 
	        		AND dl.fecha_documento BETWEEN '" . $semana_tres_fecha_inicio . "' AND '" . $semana_tres_fecha_fin . "'
		    ) AS semana_tres_importe,
		    (
				SELECT
					SUM(dl.importe)
				FROM mepa_detalle_caja_chica_liquidacion dl
					INNER JOIN mepa_caja_chica_liquidacion l
					ON l.id = dl.mepa_caja_chica_liquidacion_id
					INNER JOIN mepa_asignacion_caja_chica ai
					ON l.asignacion_id = ai.id
		        WHERE ai.id = a.id AND dl.status = 1 
	        		AND dl.fecha_documento BETWEEN '" . $semana_cuatro_fecha_inicio . "' AND '" . $semana_cuatro_fecha_fin . "'
		    ) AS semana_cuatro_importe,
		    (
				SELECT
					SUM(dl.importe)
				FROM mepa_detalle_caja_chica_liquidacion dl
					INNER JOIN mepa_caja_chica_liquidacion l
					ON l.id = dl.mepa_caja_chica_liquidacion_id
					INNER JOIN mepa_asignacion_caja_chica ai
					ON l.asignacion_id = ai.id
		        WHERE ai.id = a.id AND dl.status = 1 
	        		AND dl.fecha_documento BETWEEN '" . $semana_cinco_fecha_inicio . "' AND '" . $semana_cinco_fecha_fin . "'
		    ) AS semana_cinco_importe,
		    (
				SELECT
					SUM(dl.importe)
				FROM mepa_detalle_caja_chica_liquidacion dl
					INNER JOIN mepa_caja_chica_liquidacion l
					ON l.id = dl.mepa_caja_chica_liquidacion_id
					INNER JOIN mepa_asignacion_caja_chica ai
					ON l.asignacion_id = ai.id
		        WHERE ai.id = a.id AND dl.status = 1
		    ) AS importe_general,
		    a.fondo_asignado,
		    a.saldo_disponible
		FROM mepa_asignacion_caja_chica a
			INNER JOIN tbl_usuarios tu
			ON a.usuario_asignado_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN tbl_razon_social rse
			ON tp.razon_social_id = rse.id
			INNER JOIN mepa_zona_asignacion z
			ON a.zona_asignacion_id = z.id
		WHERE " . $where_redes . "
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();
	$num = 1;

	while ($reg = $list_query->fetch_object()) {
		$porcentaje = ($reg->saldo_disponible * 100) / $reg->fondo_asignado;

		$data[] = array(
			"0" => $num,
			"1" => $reg->usuario_asignado,
			"2" => $reg->zona,
			"3" => "S/ " . number_format($reg->semana_uno_importe, 2, '.', ','),
			"4" => "S/ " . number_format($reg->semana_dos_importe, 2, '.', ','),
			"5" => "S/ " . number_format($reg->semana_tres_importe, 2, '.', ','),
			"6" => "S/ " . number_format($reg->semana_cuatro_importe, 2, '.', ','),
			"7" => "S/ " . number_format($reg->semana_cinco_importe, 2, '.', ','),
			"8" => "S/ " . number_format($reg->importe_general, 2, '.', ','),
			"9" => "S/ " . number_format($reg->fondo_asignado, 2, '.', ','),
			"10" => "S/ " . number_format($reg->saldo_disponible, 2, '.', ','),
			"11" => (number_format($porcentaje, 2, '.', ',') > 50)
				?
				"<span class='badge badge-success'>" . number_format($porcentaje, 2, '.', ',') . " %" . "</span>"
				:
				"<span class='badge badge-danger'>" . number_format($porcentaje, 2, '.', ',') . " %" . "</span>"
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
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_reporte_contabilidad_listar_gastos_ultimas_semanas_export") {
	$login_usuario_id = $login ? $login['id'] : null;

	// INICIO: VERIFICAR RED

	$where_redes = "";

	$select_red =
		"
        SELECT
            l.red_id
        FROM tbl_usuarios_locales ul
            INNER JOIN tbl_locales l
            ON ul.local_id = l.id
        WHERE ul.usuario_id = '" . $login_usuario_id . "'
        	AND ul.estado = 1 AND l.red_id IS NOT NULL
        GROUP BY l.red_id
    ";

	$data_select_red = $mysqli->query($select_red);

	$row_count_data_select_red = $data_select_red->num_rows;

	$ids_data_select_red = '';
	$contador_ids = 0;

	if ($row_count_data_select_red > 0) {
		while ($row = $data_select_red->fetch_assoc()) {
			if ($contador_ids > 0) {
				$ids_data_select_red .= ',';
			}

			$ids_data_select_red .= $row["red_id"];
			$contador_ids++;
		}

		$where_redes = " rse.red_id IN ($ids_data_select_red) ";
	}

	// FIN: VERIFICAR RED

	$fecha_hoy = getdate();

	// OBTENER QUE DIA DE LA SEMANA ES
	$dia_semana_hoy = $fecha_hoy['wday'];

	$num_dia = 0;

	switch ($dia_semana_hoy) {
		case 1:
			// LUNES
			$num_dia = 1;
			break;
		case 2:
			// MARTES
			$num_dia = 2;
			break;
		case 3:
			// MIERCOLES
			$num_dia = 3;
			break;
		case 4:
			// JUEVES
			$num_dia = 4;
			break;
		case 5:
			// VIERNES
			$num_dia = 5;
			break;
		case 6:
			// SABADO
			$num_dia = 6;
			break;
		case 0:
			// DOMINGO
			$num_dia = 7;
			break;

		default:
			$num_dia = 0;
			break;
	}

	$semana_uno_fecha_fin = date("Y-m-d", mktime(0, 0, 0, date("m"), date("d") - $num_dia, date("Y")));
	$semana_uno_fecha_inicio = date("Y-m-d", mktime(0, 0, 0, date("m", strtotime($semana_uno_fecha_fin)), date("d", strtotime($semana_uno_fecha_fin)) - 6, date("Y", strtotime($semana_uno_fecha_fin))));

	$semana_dos_fecha_fin = date("Y-m-d", mktime(0, 0, 0, date("m", strtotime($semana_uno_fecha_inicio)), date("d", strtotime($semana_uno_fecha_inicio)) - 1, date("Y", strtotime($semana_uno_fecha_inicio))));

	$semana_dos_fecha_inicio = date("Y-m-d", mktime(0, 0, 0, date("m", strtotime($semana_dos_fecha_fin)), date("d", strtotime($semana_dos_fecha_fin)) - 6, date("Y", strtotime($semana_dos_fecha_fin))));

	$semana_tres_fecha_fin = date("Y-m-d", mktime(0, 0, 0, date("m", strtotime($semana_dos_fecha_inicio)), date("d", strtotime($semana_dos_fecha_inicio)) - 1, date("Y", strtotime($semana_dos_fecha_inicio))));

	$semana_tres_fecha_inicio = date("Y-m-d", mktime(0, 0, 0, date("m", strtotime($semana_tres_fecha_fin)), date("d", strtotime($semana_tres_fecha_fin)) - 6, date("Y", strtotime($semana_tres_fecha_fin))));

	$semana_cuatro_fecha_fin = date("Y-m-d", mktime(0, 0, 0, date("m", strtotime($semana_tres_fecha_inicio)), date("d", strtotime($semana_tres_fecha_inicio)) - 1, date("Y", strtotime($semana_tres_fecha_inicio))));

	$semana_cuatro_fecha_inicio = date("Y-m-d", mktime(0, 0, 0, date("m", strtotime($semana_cuatro_fecha_fin)), date("d", strtotime($semana_cuatro_fecha_fin)) - 6, date("Y", strtotime($semana_cuatro_fecha_fin))));

	$semana_cinco_fecha_fin = date("Y-m-d", mktime(0, 0, 0, date("m", strtotime($semana_cuatro_fecha_inicio)), date("d", strtotime($semana_cuatro_fecha_inicio)) - 1, date("Y", strtotime($semana_cuatro_fecha_inicio))));

	$semana_cinco_fecha_inicio = date("Y-m-d", mktime(0, 0, 0, date("m", strtotime($semana_cinco_fecha_fin)), date("d", strtotime($semana_cinco_fecha_fin)) - 6, date("Y", strtotime($semana_cinco_fecha_fin))));


	$query = "
		SELECT
			a.id,
		    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado,
		    a.zona_asignacion_id, z.nombre AS zona,
		    (
				SELECT
					SUM(dl.importe)
				FROM mepa_detalle_caja_chica_liquidacion dl
					INNER JOIN mepa_caja_chica_liquidacion l
					ON l.id = dl.mepa_caja_chica_liquidacion_id
					INNER JOIN mepa_asignacion_caja_chica ai
					ON l.asignacion_id = ai.id
		        WHERE ai.id = a.id AND dl.status = 1 
	        		AND dl.fecha_documento BETWEEN '" . $semana_uno_fecha_inicio . "' AND '" . $semana_uno_fecha_fin . "'
		    ) AS semana_uno_importe,
		    (
				SELECT
					SUM(dl.importe)
				FROM mepa_detalle_caja_chica_liquidacion dl
					INNER JOIN mepa_caja_chica_liquidacion l
					ON l.id = dl.mepa_caja_chica_liquidacion_id
					INNER JOIN mepa_asignacion_caja_chica ai
					ON l.asignacion_id = ai.id
		        WHERE ai.id = a.id AND dl.status = 1 
	        		AND dl.fecha_documento BETWEEN '" . $semana_dos_fecha_inicio . "' AND '" . $semana_dos_fecha_fin . "'
		    ) AS semana_dos_importe,
		    (
				SELECT
					SUM(dl.importe)
				FROM mepa_detalle_caja_chica_liquidacion dl
					INNER JOIN mepa_caja_chica_liquidacion l
					ON l.id = dl.mepa_caja_chica_liquidacion_id
					INNER JOIN mepa_asignacion_caja_chica ai
					ON l.asignacion_id = ai.id
		        WHERE ai.id = a.id AND dl.status = 1 
	        		AND dl.fecha_documento BETWEEN '" . $semana_tres_fecha_inicio . "' AND '" . $semana_tres_fecha_fin . "'
		    ) AS semana_tres_importe,
		    (
				SELECT
					SUM(dl.importe)
				FROM mepa_detalle_caja_chica_liquidacion dl
					INNER JOIN mepa_caja_chica_liquidacion l
					ON l.id = dl.mepa_caja_chica_liquidacion_id
					INNER JOIN mepa_asignacion_caja_chica ai
					ON l.asignacion_id = ai.id
		        WHERE ai.id = a.id AND dl.status = 1 
	        		AND dl.fecha_documento BETWEEN '" . $semana_cuatro_fecha_inicio . "' AND '" . $semana_cuatro_fecha_fin . "'
		    ) AS semana_cuatro_importe,
		    (
				SELECT
					SUM(dl.importe)
				FROM mepa_detalle_caja_chica_liquidacion dl
					INNER JOIN mepa_caja_chica_liquidacion l
					ON l.id = dl.mepa_caja_chica_liquidacion_id
					INNER JOIN mepa_asignacion_caja_chica ai
					ON l.asignacion_id = ai.id
		        WHERE ai.id = a.id AND dl.status = 1 
	        		AND dl.fecha_documento BETWEEN '" . $semana_cinco_fecha_inicio . "' AND '" . $semana_cinco_fecha_fin . "'
		    ) AS semana_cinco_importe,
		    (
				SELECT
					SUM(dl.importe)
				FROM mepa_detalle_caja_chica_liquidacion dl
					INNER JOIN mepa_caja_chica_liquidacion l
					ON l.id = dl.mepa_caja_chica_liquidacion_id
					INNER JOIN mepa_asignacion_caja_chica ai
					ON l.asignacion_id = ai.id
		        WHERE ai.id = a.id AND dl.status = 1
		    ) AS importe_general,
		    a.fondo_asignado,
		    a.saldo_disponible
		FROM mepa_asignacion_caja_chica a
			INNER JOIN tbl_usuarios tu
			ON a.usuario_asignado_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN tbl_razon_social rse
			ON tp.razon_social_id = rse.id
			INNER JOIN mepa_zona_asignacion z
			ON a.zona_asignacion_id = z.id
		WHERE " . $where_redes . " 
	";


	$list_query = $mysqli->query($query);

	//PROCEDEMOS A CREAR LA CARPETASI EN CASO NO EXISTA
	$path = "/var/www/html/files_bucket/mepa/descargas/contabilidad/promedio_gastos_ultimas_semanas/";

	if (!is_dir($path)) {
		mkdir($path, 0777, true);
	}

	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/mepa/descargas/contabilidad/promedio_gastos_ultimas_semanas/*'); //obtenemos todos los nombres de los ficheros
	foreach ($files as $file) {
		if (is_file($file))
			unlink($file); //elimino el fichero
	}


	require_once '../phpexcel/classes/PHPExcel.php';
	$objPHPExcel = new PHPExcel();

	// Se asignan las propiedades del libro
	$objPHPExcel->getProperties()->setCreator("AT") // Nombre del autor
		->setDescription("Reporte"); //Descripción

	$tituloReporte = "Reporte Caja Chica - Gastos por Proveedores ultimas 5 semanas Caja Chica Realizada";

	$titulosColumnas = array('Nº', 'RESPONSABLE', 'ZONA', 'SEMANA 1', 'SEMANA 2', 'SEMANA 3', 'SEMANA 4', 'SEMANA 5', 'TOTAL GENERAL', 'FONDO', 'SALDO', '% SALDO');

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
		->setCellValue('L1', $titulosColumnas[11]);

	//Se agregan los datos a la lista del reporte
	$cont = 0;

	$i = 2; //Numero de fila donde se va a comenzar a rellenar los datos
	while ($fila = $list_query->fetch_array()) {
		$cont++;

		$porcentaje = ($fila['saldo_disponible'] * 100) / $fila['fondo_asignado'];

		$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A' . $i, $cont)
			->setCellValue('B' . $i, $fila['usuario_asignado'])
			->setCellValue('C' . $i, $fila['zona'])
			->setCellValue('D' . $i, "S/ " . number_format($fila['semana_uno_importe'], 2, '.', ','))
			->setCellValue('E' . $i, "S/ " . number_format($fila['semana_dos_importe'], 2, '.', ','))
			->setCellValue('F' . $i, "S/ " . number_format($fila['semana_tres_importe'], 2, '.', ','))
			->setCellValue('G' . $i, "S/ " . number_format($fila['semana_cuatro_importe'], 2, '.', ','))
			->setCellValue('H' . $i, "S/ " . number_format($fila['semana_cinco_importe'], 2, '.', ','))
			->setCellValue('I' . $i, "S/ " . number_format($fila['importe_general'], 2, '.', ','))
			->setCellValue('J' . $i, "S/ " . number_format($fila['fondo_asignado'], 2, '.', ','))
			->setCellValue('K' . $i, "S/ " . number_format($fila['saldo_disponible'], 2, '.', ','))
			->setCellValue('L' . $i, number_format($porcentaje, 2, '.', ','));

		$i++;
	}

	$estiloNombresColumnas = array(
		'font' => array(
			'name'      => 'Calibri',
			'bold'      => true,
			'italic'    => false,
			'strike'    => false,
			'size' => 10,
			'color'     => array(
				'rgb' => 'ffffff'
			)
		),
		'fill' => array(
			'type'  => PHPExcel_Style_Fill::FILL_SOLID,
			'color' => array(
				'rgb' => '000000'
			)
		),
		'alignment' =>  array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			'wrap'      => false
		)
	);

	$estiloInformacion = new PHPExcel_Style();
	$estiloInformacion->applyFromArray(array(
		'font' => array(
			'name'  => 'Arial',
			'color' => array(
				'rgb' => '000000'
			)
		)
	));

	$estilo_centrar = array(
		'alignment' =>  array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			'wrap'      => false
		)
	);

	$objPHPExcel->getActiveSheet()->getStyle('A1:L1')->applyFromArray($estiloNombresColumnas);
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:L" . ($i - 1));
	$objPHPExcel->getActiveSheet()->getStyle('A1:A' . ($i - 1))->applyFromArray($estilo_centrar);

	// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
	for ($i = 'A'; $i <= 'Z'; $i++) {
		$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}

	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('Mesa de Partes - Gastos por Proveedores ultimas 5 semanas Caja Chica Realizada');

	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);

	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Sin Caja Chica Realizada AT.xls');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$excel_path = '/var/www/html/files_bucket/mepa/descargas/contabilidad/promedio_gastos_ultimas_semanas/Gastos por Proveedores ultimas 5 semanas Caja Chica Realizada AT.xls';
	$excel_path_download = '/files_bucket/mepa/descargas/contabilidad/promedio_gastos_ultimas_semanas/Gastos por Proveedores ultimas 5 semanas Caja Chica Realizada AT.xls';

	try {
		$objWriter->save($excel_path);
	} catch (PHPExcel_Writer_Exception $e) {
		echo json_encode(["error" => $e]);
		exit;
	}

	echo json_encode(array(
		"ruta_archivo" => $excel_path_download
	));
	exit;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_reporte_contabilidad_listar_asignacion_saldos") {
	$param_fecha_desde = date("Y-m-d", strtotime($_POST['param_fecha_desde']));
	$param_fecha_hasta = date("Y-m-d", strtotime($_POST['param_fecha_hasta']));
	$param_tipo_red = $_POST['param_tipo_red'];

	$login_usuario_id = $login ? $login['id'] : null;

	$query_obtener_usuarios =
	"
		SELECT
			a.id, a.usuario_asignado_id AS usuario_asignado
		FROM mepa_asignacion_caja_chica a
		GROUP BY a.usuario_asignado_id
		ORDER BY a.usuario_asignado_id
	";

	$list_query_obtener_usuarios = $mysqli->query($query_obtener_usuarios);

	$data =  array();

	while ($li = $list_query_obtener_usuarios->fetch_object())
	{
		$list_query = "";

		$query =
		"
			SELECT
				a.id,
			    a.usuario_asignado_id, a.empresa_id, rz.nombre AS empresa, rz.red_id,
			    a.zona_asignacion_id, z.nombre AS zona, tp.dni AS usuario_asignado_dni,
			    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado_nombre,
			    a.fondo_asignado,
			    (
			    	SELECT
			            SUM((IFNULL(li.total_rendicion, 0) + IFNULL(mi.monto_cierre, 0)))
			        FROM mepa_caja_chica_liquidacion li
			            LEFT JOIN mepa_caja_chica_movilidad mi
			            ON li.id_movilidad = mi.id
			            LEFT JOIN mepa_caja_chica_programacion_detalle pd
			            ON pd.nombre_tabla = 'mepa_liquidacion_caja_chica' AND pd.nombre_tabla_id = li.id
			            LEFT JOIN mepa_caja_chica_programacion p
			            ON p.id = pd.mepa_caja_chica_programacion_id
			        WHERE li.asignacion_id = a.id AND li.status = 1
			        	AND (DATE_FORMAT(li.fecha_desde, '%Y-%m-%d') >= '" . $param_fecha_desde . "' AND DATE_FORMAT(li.fecha_hasta, '%Y-%m-%d') <= '" . $param_fecha_hasta . "')
			        	AND (DATE_FORMAT(p.fecha_comprobante, '%Y-%m-%d') >= '" . $param_fecha_hasta . "')
			    ) AS caja_por_reembolsar
			FROM mepa_asignacion_caja_chica a
				INNER JOIN tbl_usuarios tu
				ON a.usuario_asignado_id = tu.id
				INNER JOIN tbl_personal_apt tp
				ON tu.personal_id = tp.id
			    INNER JOIN tbl_razon_social rz
				ON a.empresa_id = rz.id
			    INNER JOIN mepa_zona_asignacion z
				ON a.zona_asignacion_id = z.id
			WHERE a.usuario_asignado_id = '" . $li->usuario_asignado . "' 
				AND rz.red_id = '" . $param_tipo_red . "' AND a.status = 1
				ORDER BY a.id DESC
				LIMIT 1
		";

		$list_query = $mysqli->query($query);

		while ($reg = $list_query->fetch_object())
		{
			$saldo_caja = $reg->fondo_asignado - $reg->caja_por_reembolsar;
			$porcentaje_saldo = ($saldo_caja * 100) / $reg->fondo_asignado;

			$data[] = array(
				"0" => $reg->id,
				"1" => $reg->empresa,
				"2" => $reg->zona,
				"3" => $reg->usuario_asignado_dni,
				"4" => $reg->usuario_asignado_nombre,
				"5" => "S/ " . number_format($reg->fondo_asignado, 2, '.', ','),
				"6" => "S/ " . number_format($reg->caja_por_reembolsar, 2, '.', ','),
				"7" => "S/ " . number_format($saldo_caja, 2, '.', ','),
				"8" => (number_format($porcentaje_saldo, 2, '.', ',') > 50)
					?
					"<span class='badge badge-success'>" . number_format($porcentaje_saldo, 2, '.', ',') . " %" . "</span>"
					:
					"<span class='badge badge-danger'>" . number_format($porcentaje_saldo, 2, '.', ',') . " %" . "</span>"
			);
		}
	}

	$resultado = array(
		"sEcho" => 1,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);

	echo json_encode($resultado);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_reporte_contabilidad_asignacion_saldos_export") {
	$param_fecha_desde = date("Y-m-d", strtotime($_POST['param_fecha_desde']));
	$param_fecha_hasta = date("Y-m-d", strtotime($_POST['param_fecha_hasta']));
	$param_tipo_red = $_POST['param_tipo_red'];

	$login_usuario_id = $login ? $login['id'] : null;

	$query_obtener_usuarios =
	"
		SELECT
			a.id, a.usuario_asignado_id AS usuario_asignado
		FROM mepa_asignacion_caja_chica a
		GROUP BY a.usuario_asignado_id
		ORDER BY a.usuario_asignado_id
	";

	$list_query_obtener_usuarios = $mysqli->query($query_obtener_usuarios);

	//PROCEDEMOS A CREAR LA CARPETASI EN CASO NO EXISTA
	$path = "/var/www/html/files_bucket/mepa/descargas/contabilidad/rpt_saldo_caja_chica/";

	if (!is_dir($path)) {
		mkdir($path, 0777, true);
	}

	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/mepa/descargas/contabilidad/rpt_saldo_caja_chica/*'); //obtenemos todos los nombres de los ficheros
	foreach ($files as $file) {
		if (is_file($file))
			unlink($file); //elimino el fichero
	}

	require_once '../phpexcel/classes/PHPExcel.php';
	$objPHPExcel = new PHPExcel();

	// Se asignan las propiedades del libro
	$objPHPExcel->getProperties()->setCreator("AT") // Nombre del autor
		->setDescription("Reporte"); //Descripción

	$tituloReporte = "Reporte Caja Chica - Saldos";

	$titulosColumnas = array('Nº', 'EMPRESA', 'ZONA', 'DNI', 'SOLICITANTE', 'FONDO ASIGNADO', 'CAJA POR REEMBOLSAR', 'SALDO', '% SALDO');

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

	//Numero de fila donde se va a comenzar a rellenar los datos
	$i = 2;

	//Se agregan los datos a la lista del reporte
	$cont = 0;

	while ($li = $list_query_obtener_usuarios->fetch_object())
	{
		$list_query = "";

		$query =
		"
			SELECT
				a.id,
			    a.usuario_asignado_id, a.empresa_id, rz.nombre AS empresa, rz.red_id,
			    a.zona_asignacion_id, z.nombre AS zona, tp.dni AS usuario_asignado_dni,
			    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado_nombre,
			    a.fondo_asignado,
			    (
			    	SELECT
			            SUM((IFNULL(li.total_rendicion, 0) + IFNULL(mi.monto_cierre, 0)))
			        FROM mepa_caja_chica_liquidacion li
			            LEFT JOIN mepa_caja_chica_movilidad mi
			            ON li.id_movilidad = mi.id
			            LEFT JOIN mepa_caja_chica_programacion_detalle pd
			            ON pd.nombre_tabla = 'mepa_liquidacion_caja_chica' AND pd.nombre_tabla_id = li.id
			            LEFT JOIN mepa_caja_chica_programacion p
			            ON p.id = pd.mepa_caja_chica_programacion_id
			        WHERE li.asignacion_id = a.id AND li.status = 1
			        	AND (DATE_FORMAT(li.fecha_desde, '%Y-%m-%d') >= '" . $param_fecha_desde . "' AND DATE_FORMAT(li.fecha_hasta, '%Y-%m-%d') <= '" . $param_fecha_hasta . "')
			        	AND (DATE_FORMAT(p.fecha_comprobante, '%Y-%m-%d') >= '" . $param_fecha_hasta . "')
			    ) AS caja_por_reembolsar
			FROM mepa_asignacion_caja_chica a
				INNER JOIN tbl_usuarios tu
				ON a.usuario_asignado_id = tu.id
				INNER JOIN tbl_personal_apt tp
				ON tu.personal_id = tp.id
			    INNER JOIN tbl_razon_social rz
				ON a.empresa_id = rz.id
			    INNER JOIN mepa_zona_asignacion z
				ON a.zona_asignacion_id = z.id
			WHERE a.usuario_asignado_id = '" . $li->usuario_asignado . "' 
				AND rz.red_id = '" . $param_tipo_red . "' AND a.status = 1
				ORDER BY a.id DESC
				LIMIT 1
		";

		$list_query = $mysqli->query($query);

		while ($fila = $list_query->fetch_array())
		{
			$cont++;

			$saldo_caja = $fila['fondo_asignado'] - $fila['caja_por_reembolsar'];
			$porcentaje_saldo = ($saldo_caja * 100) / $fila['fondo_asignado'];

			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A' . $i, $cont)
				->setCellValue('B' . $i, $fila['empresa'])
				->setCellValue('C' . $i, $fila['zona'])
				->setCellValue('D' . $i, $fila['usuario_asignado_dni'])
				->setCellValue('E' . $i, $fila['usuario_asignado_nombre'])
				->setCellValue('F' . $i, "S/ " . number_format($fila['fondo_asignado'], 2, '.', ','))
				->setCellValue('G' . $i, "S/ " . number_format($fila['caja_por_reembolsar'], 2, '.', ','))
				->setCellValue('H' . $i, "S/ " . number_format($saldo_caja, 2, '.', ','))
				->setCellValue('I' . $i, number_format($porcentaje_saldo, 2, '.', ',') . " %");

			$i++;
		}
		
	}

	$estiloNombresColumnas = array(
		'font' => array(
			'name'      => 'Calibri',
			'bold'      => true,
			'italic'    => false,
			'strike'    => false,
			'size' => 10,
			'color'     => array(
				'rgb' => 'ffffff'
			)
		),
		'fill' => array(
			'type'  => PHPExcel_Style_Fill::FILL_SOLID,
			'color' => array(
				'rgb' => '000000'
			)
		),
		'alignment' =>  array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			'wrap'      => false
		)
	);

	$estiloInformacion = new PHPExcel_Style();
	$estiloInformacion->applyFromArray(array(
		'font' => array(
			'name'  => 'Arial',
			'color' => array(
				'rgb' => '000000'
			)
		)
	));

	$estilo_centrar = array(
		'alignment' =>  array(
			'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			'wrap'      => false
		)
	);

	$objPHPExcel->getActiveSheet()->getStyle('A1:I1')->applyFromArray($estiloNombresColumnas);
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:I" . ($i - 1));
	$objPHPExcel->getActiveSheet()->getStyle('A1:A' . ($i - 1))->applyFromArray($estilo_centrar);

	// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
	for ($i = 'A'; $i <= 'Z'; $i++) {
		$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}

	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('Mesa de Partes - Saldos');

	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);

	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Saldo Caja Chica AT.xls');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$excel_path = '/var/www/html/files_bucket/mepa/descargas/contabilidad/rpt_saldo_caja_chica/Saldo Caja Chica AT.xls';
	$excel_path_download = '/files_bucket/mepa/descargas//contabilidad/rpt_saldo_caja_chica/Saldo Caja Chica AT.xls';

	try {
		$objWriter->save($excel_path);
	} catch (PHPExcel_Writer_Exception $e) {
		echo json_encode(["error" => $e]);
		exit;
	}

	echo json_encode(array(
		"ruta_archivo" => $excel_path_download
	));
	exit;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_reporte_contabilidad_dashboard_caja_chica") {

	$mes = $_POST["mes"];
	$year = $_POST["year"];
	$razon_social = $_POST["razon_social"];
	$usuarios = $_POST["usuarios"];
	$descripcion = $_POST["descripcion"];
	$concepto = $_POST["concepto"];

	$query = "	SELECT 
					mes, mes_text, fondo_asignado, SUM(importe_mes) as importe_mes
				from (
						SELECT
							DATE_FORMAT(dli.fecha_documento, '%Y-%m') as mes, DATE_FORMAT(dli.fecha_documento, '%M') as mes_text, ai.fondo_asignado as fondo_asignado, SUM(dli.importe) AS importe_mes
						FROM
							mepa_detalle_caja_chica_liquidacion dli
							INNER JOIN mepa_caja_chica_liquidacion li ON li.id = dli.mepa_caja_chica_liquidacion_id
							INNER JOIN mepa_asignacion_caja_chica ai ON li.asignacion_id = ai.id
							INNER JOIN tbl_razon_social rsi ON ai.empresa_id = rsi.id
						WHERE
							li.situacion_etapa_id_superior = 6
							AND li.situacion_etapa_id_contabilidad = 6
							AND dli.status = 1
							AND DATE_FORMAT(dli.fecha_documento, '%Y') = '$year'
							";
	if (!empty($mes)) {
		$query .= "AND DATE_FORMAT(dli.fecha_documento, '%m') in (" . implode(",", $mes) . ")";
	}
	if (!empty($concepto)) {
		$query .= "AND dli.codigo_provision_contable in (" . implode(",", $concepto) . ")";
	}
	if (!empty($razon_social)) {
		$query .= "AND ai.empresa_id in (" . implode(",", $razon_social) . ")";
	}
	if (!empty($usuarios)) {
		$query .= "AND ai.usuario_asignado_id in (" . implode(",", $usuarios) . ")";
	}
	if (!empty($descripcion)) {
		$query .= "AND ai.zona_asignacion_id in (" . implode(",", $descripcion) . ")";
	}
	$query .= "	group by DATE_FORMAT(dli.fecha_documento, '%Y-%m')";

	if (empty($concepto) || in_array("'movilidad'", $concepto)) {
		$query .= " UNION ALL ";
		$query .= "			SELECT
							DATE_FORMAT(md.fecha, '%Y-%m') as mes, 
							DATE_FORMAT(md.fecha, '%M') as mes_text, 
							ai.fondo_asignado as fondo_asignado, 
							sum(md.monto) AS importe_mes
						FROM
							mepa_caja_chica_movilidad_detalle md
							INNER JOIN mepa_caja_chica_movilidad m ON m.id = md.id_mepa_caja_chica_movilidad
							INNER JOIN mepa_caja_chica_liquidacion li ON li.id_movilidad = m.id
							INNER JOIN mepa_asignacion_caja_chica ai ON li.asignacion_id = ai.id
						WHERE
							li.situacion_etapa_id_superior = 6
							AND li.situacion_etapa_id_contabilidad = 6
							AND m.estado = 1
							AND md.estado = 1
							AND DATE_FORMAT(md.fecha, '%Y') = '$year' ";
		if (!empty($mes)) {
			$query .= " AND DATE_FORMAT(md.fecha, '%m') in (" . implode(",", $mes) . ")";
		}
		if (!empty($razon_social)) {
			$query .= " AND ai.empresa_id in (" . implode(",", $razon_social) . ")";
		}
		if (!empty($usuarios)) {
			$query .= " AND ai.usuario_asignado_id in (" . implode(",", $usuarios) . ")";
		}
		if (!empty($descripcion)) {
			$query .= " AND ai.zona_asignacion_id in (" . implode(",", $descripcion) . ")";
		}
		$query .= " GROUP BY DATE_FORMAT(md.fecha, '%Y-%m')";
	}

	$query .= ") liquidaciones
				GROUP BY
					mes;";

	$result = $mysqli->query($query);
	if ($mysqli->error) {
		echo json_encode([
			'status' => '500',
			'msg' => 'ERROR MYSQL: ' . $mysqli->error,
			'query' => $query
		]);
		exit();
	}
	$response = [];
	$meses_espanol = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];

	while ($r = $result->fetch_assoc()) {
		$r['mes_text'] = $meses_espanol[intval(date('m', strtotime($r['mes'])) - 1)];
		$response[] = $r;
	}

	echo json_encode([
		'status' => '200',
		'result' => $response,
		'query' => $query
	]);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_reporte_contabilidad_dashboard_get_usuarios") {
	$razon_social = $_POST["razon_social"];
	$descripcion = $_POST["descripcion"];

	if (empty($razon_social) && empty($descripcion)) {
		echo json_encode([
			'status' => '500',
			'msg' => 'Seleccione una razon social para cargar las zonas.'
		]);
		exit();
	}

	$response = [];
	$response['usuarios'] = [];
	$response['zonas'] = [];

	if (!empty($descripcion)) {
		$query = "  SELECT 
						u.id, 
						ifnull(
							concat(
								ifnull(p.nombre,''), ' ',  ifnull(p.apellido_paterno,''), ' ',  ifnull(p.apellido_materno,'')
							), u.usuario
						) as nombre_completo
					FROM
						tbl_usuarios u
						inner join tbl_personal_apt p on u.personal_id = p.id
					where
						u.id in (
								select distinct(usuario_asignado_id)
								from mepa_asignacion_caja_chica
								where situacion_etapa_id = 6
							";
		if (!empty($razon_social)) {
			$query .= " AND empresa_id in (" . implode(",", $razon_social) . ")";
		}
		if (!empty($descripcion)) {
			$query .= " AND zona_asignacion_id in (" . implode(",", $descripcion) . ")";
		}
		$query .= " 		)
					ORDER  BY nombre_completo;";

		$result = $mysqli->query($query);

		while ($r = $result->fetch_assoc()) {
			$response['usuarios'][] = $r;
		}
	}

	if (!empty($razon_social)) {
		$query = "  select id, nombre from mepa_zona_asignacion
					where id in (
						select distinct (zona_asignacion_id)
						from mepa_asignacion_caja_chica
						where
							situacion_etapa_id = 6";
		if (!empty($razon_social)) {
			$query .= " 	AND empresa_id in (" . implode(",", $razon_social) . ")";
		}
		$query .= ");";

		$result = $mysqli->query($query);
		// echo $query;
		while ($r = $result->fetch_assoc()) {
			$response['zonas'][] = $r;
		}
	}

	echo json_encode([
		'status' => '200',
		'result' => $response
	]);
	exit();
}
