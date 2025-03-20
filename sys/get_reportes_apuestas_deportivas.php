<?php
date_default_timezone_set("America/Lima");

$result = array();
include("db_connect.php");
include("sys_login.php");
include '/var/www/html/sys/Pagination.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_apuesta") {

	$query = "SELECT id, nombre FROM tbl_televentas_clientes_tipo_transaccion WHERE id IN(4,5) ORDER BY nombre ASC";

	$list_query = $mysqli->query($query);
	$list = array();

	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list) > 0) {
		$result["http_code"] = 200;
		$result["result"] = $list;
	} else {
		$result["http_code"] = 400;
		$result["result"] = "No existen tipos de apuestas.";
	}

	echo json_encode($result);
}

if (isset($_POST["accion"]) && $_POST["accion"] === "listar_apuestas_deportivas") {

	$apuesta_generada_fecha_inicio = trim($_POST['apuesta_generada_fecha_inicio']);
	$apuesta_generada_fecha_fin = trim($_POST['apuesta_generada_fecha_fin']);
	$apuesta_calculada_fecha_inicio = trim($_POST['apuesta_calculada_fecha_inicio']);
	$apuesta_calculada_fecha_fin = trim($_POST['apuesta_calculada_fecha_fin']);
	$tipo_estado_id = trim($_POST['tipo_estado_id']);
	$tipo_apuesta_id = trim($_POST['tipo_apuesta_id']);

	if ($apuesta_generada_fecha_inicio == '') {
		$result['http_code'] = 500;
		$result['consulta_error'] = 'Ingrese la fecha inicio de la apuesta generada.';
		echo json_encode($result);
		exit;
	}

	if ($apuesta_generada_fecha_fin == '') {
		$result['http_code'] = 500;
		$result['consulta_error'] = 'Ingrese la fecha fin de la apuesta generada.';
		echo json_encode($result);
		exit;
	}

	if ($apuesta_generada_fecha_inicio != '' && $apuesta_generada_fecha_fin != '') {
		$fechaInicio = new DateTime($apuesta_generada_fecha_inicio);
		$fechaFin = new DateTime($apuesta_generada_fecha_fin);
		$diferencia = $fechaInicio->diff($fechaFin);
		$diasDiferencia = $diferencia->days;

		if ($diasDiferencia > 10) {
			$result['http_code'] = 500;
			$result['consulta_error'] = 'La diferencia entre la fecha de inicio y la fecha de fin de apuesta generada no debe ser mayor a 10 días.';
			echo json_encode($result);
			exit;
		}
	}

	if ($apuesta_calculada_fecha_inicio != '' && $apuesta_calculada_fecha_fin != '') {
		$fechaInicio = new DateTime($apuesta_calculada_fecha_inicio);
		$fechaFin = new DateTime($apuesta_calculada_fecha_fin);
		$diferencia = $fechaInicio->diff($fechaFin);
		$diasDiferencia = $diferencia->days;

		if ($diasDiferencia > 10) {
			$result['http_code'] = 500;
			$result['consulta_error'] = 'La diferencia entre la fecha de inicio y la fecha de fin de apuesta calculada no debe ser mayor a 10 días.';
			echo json_encode($result);
			exit;
		}
	}

	$where_fecha_apuesta_generada = '';
	$where_fecha_apuesta_calculada = '';
	$where_tipo_estado_id = '';
	$where_tipo_apuesta_id = ' AND ct.tipo_id IN(4,5)';

	if (!empty($apuesta_generada_fecha_inicio) && !empty($apuesta_generada_fecha_fin)) {
		$where_fecha_apuesta_generada = ' AND tt.created BETWEEN "' . $apuesta_generada_fecha_inicio . ' 00:00:00" AND "' . $apuesta_generada_fecha_fin . ' 23:59:59"';
	} elseif (!empty($apuesta_generada_fecha_inicio)) {
		$where_fecha_apuesta_generada = ' AND tt.created >= "' . $apuesta_generada_fecha_inicio . ' 00:00:00"';
	} elseif (!empty($apuesta_generada_fecha_fin)) {
		$where_fecha_apuesta_generada = ' AND tt.created <= "' . $apuesta_generada_fecha_fin . ' 23:59:59"';
	}

	if (!empty($apuesta_calculada_fecha_inicio) && !empty($apuesta_calculada_fecha_fin)) {
		$where_fecha_apuesta_calculada = ' AND tt.calc_date between "' . $apuesta_calculada_fecha_inicio . ' 00:00:00" AND "' . $apuesta_calculada_fecha_fin . ' 23:59:59"';
	} elseif (!empty($apuesta_calculada_fecha_inicio)) {
		$where_fecha_apuesta_calculada = ' AND tt.calc_date >= "' . $apuesta_calculada_fecha_inicio . ' 00:00:00"';
	} elseif (!empty($apuesta_calculada_fecha_fin)) {
		$where_fecha_apuesta_calculada = ' AND tt.calc_date <= "' . $apuesta_calculada_fecha_fin . ' 23:59:59"';
	}

	if (!empty($tipo_estado_id)) {
		$where_tipo_estado_id = ' AND tt.status_text = "' . $tipo_estado_id . '"';
	}

	if (!empty($tipo_apuesta_id)) {
		$where_tipo_apuesta_id = ' AND ct.tipo_id = ' . $tipo_apuesta_id;
	}

	$query = '
	SELECT COUNT(tt.ticket_id) AS num_rows 
	FROM tbl_televentas_tickets tt
	INNER JOIN tbl_televentas_clientes_transaccion ct ON tt.ticket_id = ct.txn_id
	WHERE tt.proveedor_id IN(1,2,5)
	' . $where_fecha_apuesta_generada . '
	' . $where_fecha_apuesta_calculada . '
	' . $where_tipo_estado_id . '
	' . $where_tipo_apuesta_id;

	try {
		$total_query = $mysqli->query($query);
		$resultNum = $total_query->fetch_assoc();
		$num_rows = $resultNum['num_rows'];
		$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page']))?$_REQUEST['page']:1;
		$per_page = 10;
		$numLinks = 10;
		$total_paginate = ceil($num_rows/$per_page);
		$total_paginate = $total_paginate == 0 ? 1:$total_paginate;
		$page = $page > $total_paginate ? $total_paginate:$page;
		$offset = ($page - 1) * $per_page;
		$paginate = new Pagination('sec_reportes_apuestas_deportivas_cambiar_de_pagina',$num_rows,$per_page,$numLinks,$page);
	} catch (Exception $e) {
		$result['http_code'] = 500;
		$result['consulta_error'] = $e->getMessage();
		echo json_encode($result);
		exit;
	}

	$query = '
	SELECT 
		ct.cliente_id,
		tt.ticket_id,
		tt.created,
		tt.calc_date,
		tt.game,
		tt.num_selections,
		ct.tipo_id,
		tt.price,
		tt.stake_amount,
		tt.winning_amount,
		tt.status_text
	FROM tbl_televentas_tickets tt
	INNER JOIN tbl_televentas_clientes_transaccion ct ON tt.ticket_id = ct.txn_id
	WHERE tt.proveedor_id IN(1,2,5)
	' . $where_fecha_apuesta_generada . '
	' . $where_fecha_apuesta_calculada . '
	' . $where_tipo_estado_id . '
	' . $where_tipo_apuesta_id . '
	ORDER BY tt.created DESC
	LIMIT ' . $offset . ',' . $per_page;

	try {
		$sel_query = $mysqli->query($query);
	} catch (Exception $e) {
		$result['http_code'] = 500;
		$result['consulta_error'] = $e->getMessage();
		echo json_encode($result);
		exit;
	}

	$row_count = $sel_query->num_rows;

	$html = '';
	$html_footer = '';

	if ($row_count > 0) {

		$results = [];
		$list_clientes_id = array();

		while($row = $sel_query->fetch_assoc()) {
			$results[] = $row;
			$list_clientes_id[] = $row['cliente_id'];
		}

		$query_clientes = '
		SELECT 
			id,
			nombre,
			apellido_paterno,
			apellido_materno,
			num_doc,
			player_id
		FROM tbl_televentas_clientes
		WHERE id IN(' . implode(',',$list_clientes_id) . ')';

		$sel_query_clientes = $mysqli->query($query_clientes);
		$list_clientes = array();

		while ($sel_clientes = $sel_query_clientes->fetch_assoc()) {
			$list_clientes[] = $sel_clientes;
		}

		foreach ($results as $sel) {
			$cliente_idBuscado = $sel['cliente_id'];
			$cliente_num_doc = '';
			$cliente_player_id = '';
			$nombre_completo = '';

			foreach ($list_clientes as $cliente) {
				if ($cliente['id'] == $cliente_idBuscado) {
					$cliente_encontrado = $cliente;
					break;
				}
			}

			if (isset($cliente_encontrado)) {
				$cliente_num_doc = $cliente_encontrado['num_doc'] ?? '';
				$cliente_player_id = $cliente_encontrado['player_id'] ?? '';

				$tc = [
					'nombre' => $cliente_encontrado['nombre'] ?? '',
					'apellido_paterno' => $cliente_encontrado['apellido_paterno'] ?? '',
					'apellido_materno' => $cliente_encontrado['apellido_materno'] ?? '',
				];

				$nombre_completo = implode(' ', array_filter($tc));
			}

			$tipo_nombre = '';

			if($sel['tipo_id'] == '4') {
				$tipo_nombre = 'Apuesta Generada';
			} else if($sel['tipo_id'] == '5') {
				$tipo_nombre = 'Apuesta Pagada';
			}

			$monto_ganado = '';

			$estados_validos = ['WON', 'VOIDED', 'VOIDED_PAID', 'REJECTED', 'REJECTED_PAID'];

			if (in_array($sel['status_text'], $estados_validos)) {
				$monto_ganado = number_format(($sel['winning_amount']), 2, '.', ',');
			}

			$html .= '<tr>';
			$html .= '<td>' . $cliente_num_doc . '</td>';
			$html .= '<td>' . $nombre_completo . '</td>';
			$html .= '<td>' . $cliente_player_id . '</td>';
			$html .= '<td>' . $sel['ticket_id'] . '</td>';
			$html .= '<td>' . $sel['status_text'] . '</td>';
			$html .= '<td>' . $sel['created'] . '</td>';
			$html .= '<td>' . $sel['calc_date'] . '</td>';
			$html .= '<td class="text-right">' . $sel['num_selections'] . '</td>';
			$html .= '<td>' . $sel['game'] . '</td>';
			$html .= '<td></td>';
			$html .= '<td>' . $tipo_nombre . '</td>';
			$html .= '<td class="text-right">' . number_format(($sel['price']), 4, '.', ',') . '</td>';
			$html .= '<td class="text-right">' . number_format(($sel['stake_amount']), 2, '.', ',') . '</td>';
			$html .= '<td class="text-right">' . $monto_ganado . '</td>';
			$html .= '</tr>';

			$result['http_code'] = 200;
		}

		$html_footer .= '<div class="row">';
		$html_footer .= '<div class="col-md-4">';
		$html_footer .= '<br>';

		$from = $sel_query->num_rows > 0 && $offset == 0 ? 1: $offset;
		$del = $sel_query->num_rows > 0  ? $offset + 1: $offset;
		$al = $offset + $sel_query->num_rows;

		$html_footer .= '<small>Mostrando del  ' . $del . ' al ' . $al . ' de ' . $num_rows . ' registros.</small>';
		$html_footer .= '</div>';
		$html_footer .= '<div class="col-md-4 text-center">' . $paginate->createLinks() . '</div>';
		$html_footer .= '<div class="col-md-4"></div>';
		$html_footer .= '</div>';
	} else {
		$html = '<tr><td colspan="14" class="text-center">No existen apuestas deportivas</td></tr>';
		$result['http_code'] = 400;
	}

	$result['result'] = $html;
	$result['result_footer'] = $html_footer;

	echo json_encode($result, JSON_UNESCAPED_SLASHES);
}

if (isset($_POST["accion"]) && $_POST["accion"]==="SecRptApuestasDeportivas_exportar_xls") {
	$apuesta_generada_fecha_inicio = trim($_POST['apuesta_generada_fecha_inicio']);
	$apuesta_generada_fecha_fin = trim($_POST['apuesta_generada_fecha_fin']);
	$apuesta_calculada_fecha_inicio = trim($_POST['apuesta_calculada_fecha_inicio']);
	$apuesta_calculada_fecha_fin = trim($_POST['apuesta_calculada_fecha_fin']);
	$tipo_estado_id = trim($_POST['tipo_estado_id']);
	$tipo_apuesta_id = trim($_POST['tipo_apuesta_id']);

	$where_fecha_apuesta_generada = '';
	$where_fecha_apuesta_calculada = '';
	$where_tipo_estado_id = '';
	$where_tipo_apuesta_id = ' AND ct.tipo_id IN(4,5)';

	if (!empty($apuesta_generada_fecha_inicio) && !empty($apuesta_generada_fecha_fin)) {
		$where_fecha_apuesta_generada = ' AND tt.created BETWEEN "' . $apuesta_generada_fecha_inicio . ' 00:00:00" AND "' . $apuesta_generada_fecha_fin . ' 23:59:59"';
	} elseif (!empty($apuesta_generada_fecha_inicio)) {
		$where_fecha_apuesta_generada = ' AND tt.created >= "' . $apuesta_generada_fecha_inicio . ' 00:00:00"';
	} elseif (!empty($apuesta_generada_fecha_fin)) {
		$where_fecha_apuesta_generada = ' AND tt.created <= "' . $apuesta_generada_fecha_fin . ' 23:59:59"';
	}

	if (!empty($apuesta_calculada_fecha_inicio) && !empty($apuesta_calculada_fecha_fin)) {
		$where_fecha_apuesta_calculada = ' AND tt.calc_date between "' . $apuesta_calculada_fecha_inicio . ' 00:00:00" AND "' . $apuesta_calculada_fecha_fin . ' 23:59:59"';
	} elseif (!empty($apuesta_calculada_fecha_inicio)) {
		$where_fecha_apuesta_calculada = ' AND tt.calc_date >= "' . $apuesta_calculada_fecha_inicio . ' 00:00:00"';
	} elseif (!empty($apuesta_calculada_fecha_fin)) {
		$where_fecha_apuesta_calculada = ' AND tt.calc_date <= "' . $apuesta_calculada_fecha_fin . ' 23:59:59"';
	}

	if (!empty($tipo_estado_id)) {
		$where_tipo_estado_id = ' AND tt.status_text = "' . $tipo_estado_id . '"';
	}

	if (!empty($tipo_apuesta_id)) {
		$where_tipo_apuesta_id = ' AND ct.tipo_id = ' . $tipo_apuesta_id;
	}

	$query = '
	SELECT 
		tc.nombre,
		tc.apellido_paterno,
		tc.apellido_materno,
		tc.num_doc,
		tc.player_id,
		tt.ticket_id,
		tt.created,
		tt.calc_date,
		tt.game,
		tt.num_selections,
		ct.tipo_id,
		tt.price,
		tt.stake_amount,
		tt.winning_amount,
		tt.status_text
	FROM tbl_televentas_tickets tt
	INNER JOIN tbl_televentas_clientes_transaccion ct ON tt.ticket_id = ct.txn_id
	INNER JOIN tbl_televentas_clientes tc ON ct.cliente_id = tc.id
	WHERE tt.proveedor_id IN(1,2,5)
	' . $where_fecha_apuesta_generada . '
	' . $where_fecha_apuesta_calculada . '
	' . $where_tipo_estado_id . '
	' . $where_tipo_apuesta_id . '
	ORDER BY tt.created DESC';

	try {
		$sel_query = $mysqli->query($query);
	} catch (Exception $e) {
		$result['http_code'] = 500;
		$result['consulta_error'] = $e->getMessage();
		echo json_encode($result);
		exit;
	}

	$row_count = $sel_query->num_rows;

	ini_set('display_errors', 0);
	ini_set('display_startup_errors', 0);
	error_reporting(0);

	try {
		$filePath = '../phpexcel/classes/PHPExcel.php';
		if (file_exists($filePath)) {
			require_once $filePath;
		} else {
			echo json_encode(['error' => 'No se pudo encontrar el archivo PHPExcell.php']);
			exit;
		}
	} catch (Exception $e) {
		echo json_encode(['error' => 'Error al cargar PHPExcel.php: ' . $e->getMessage()]);
		exit;
	}

	if (!class_exists('PHPExcel')) {
		echo json_encode(['error' => 'No se pudo cargar PHPExcel.']);
	}
	
	try {
		$objPHPExcel = new PHPExcel();
	} catch (Exception $e) {
		echo json_encode(['error' => 'Error al crear la instancia de PHPExcel: ' . $e->getMessage()]);
		exit;
	}

	$objPHPExcel->setActiveSheetIndex(0);

	try {
		$objPHPExcel->setActiveSheetIndex(0);
	} catch (Exception $e) {
		echo json_encode(['error' => 'Error al establecer el índice de la hoja activa: ' . $e->getMessage()]);
		exit;
	}

	$sheet = $objPHPExcel->getActiveSheet();

	try {
		$sheet = $objPHPExcel->getActiveSheet();
	} catch (Exception $e) {
		echo json_encode(['error' => 'Error al trabajar con la hoja activa: ' . $e->getMessage()]);
		exit;
	}

	$lastRowIndex = 1;

	if ($row_count > 0) {

		$estilo = array(
			'font' => array(
				'name'  => 'Arial',
				'bold'  => true,
				'size' => 10,
				'color' => array(
					'rgb' => '000000'
				)
			),
			'alignment' =>  array(
				'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
				'wrap'      => false
			)
		);

		$sheet->setCellValue('A' . $lastRowIndex, 'REPORTE DE APUESTAS DEPORTIVAS');

		$objPHPExcel->getActiveSheet()->getStyle('A'.$lastRowIndex)->applyFromArray($estilo);

		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $lastRowIndex.':N'. $lastRowIndex);

		$lastRowIndex = $lastRowIndex + 1;

		$sheet->setCellValue('A' . $lastRowIndex, 'NUM DOC.');
		$sheet->setCellValue('B' . $lastRowIndex, 'CLIENTE');
		$sheet->setCellValue('C' . $lastRowIndex, 'ID JUGADOR');
		$sheet->setCellValue('D' . $lastRowIndex, 'TICKET');
		$sheet->setCellValue('E' . $lastRowIndex, 'ESTADO');
		$sheet->setCellValue('F' . $lastRowIndex, 'FECHA DE JUEGO');
		$sheet->setCellValue('G' . $lastRowIndex, 'FECHA DE CALCULO');
		$sheet->setCellValue('H' . $lastRowIndex, 'N° DE EVENTOS');
		$sheet->setCellValue('I' . $lastRowIndex, 'TIPO DE JUEGO');
		$sheet->setCellValue('J' . $lastRowIndex, 'TIPO DE PRODUCTO');
		$sheet->setCellValue('K' . $lastRowIndex, 'TIPO');
		$sheet->setCellValue('L' . $lastRowIndex, 'CUOTA NETA');
		$sheet->setCellValue('M' . $lastRowIndex, 'MONTO APOSTADO');
		$sheet->setCellValue('N' . $lastRowIndex, 'MONTO GANADO');

		$lastRowIndex = $lastRowIndex + 1;

		$rowIndex2 = $lastRowIndex;

		while ($sel = $sel_query->fetch_assoc()) {
			try {
				$tc = [
					'nombre' => $sel['nombre'] ?? '',
					'apellido_paterno' => $sel['apellido_paterno'] ?? '',
					'apellido_materno' => $sel['apellido_materno'] ?? '',
				];

				$tc_filtrado = array_filter($tc, function ($value) {
					return !is_null($value);
				});

				$nombre_completo = implode(' ', $tc_filtrado);

				$tipo_nombre = '';

				if($sel['tipo_id'] == '4') {
					$tipo_nombre = 'Apuesta Generada';
				} else if($sel['tipo_id'] == '5') {
					$tipo_nombre = 'Apuesta Pagada';
				}

				$monto_ganado = '';

				$estados_validos = ['WON', 'VOIDED', 'VOIDED_PAID', 'REJECTED', 'REJECTED_PAID'];

				if (in_array($sel['status_text'], $estados_validos)) {
					$monto_ganado = $sel['winning_amount'];
				}

				$sheet->setCellValue('A' . $rowIndex2, $sel['num_doc']);
				$sheet->setCellValue('B' . $rowIndex2, $nombre_completo);
				$sheet->setCellValue('C' . $rowIndex2, $sel['player_id']);
				$sheet->setCellValue('D' . $rowIndex2, $sel['ticket_id']);
				$sheet->setCellValue('E' . $rowIndex2, $sel['status_text']);
				$sheet->setCellValue('F' . $rowIndex2, $sel['created']);
				$sheet->setCellValue('G' . $rowIndex2, $sel['calc_date']);
				$sheet->setCellValue('H' . $rowIndex2, $sel['num_selections']);
				$sheet->setCellValue('I' . $rowIndex2, $sel['game']);
				$sheet->setCellValue('J' . $rowIndex2, '');
				$sheet->setCellValue('K' . $rowIndex2, $tipo_nombre);
				$sheet->setCellValue('L' . $rowIndex2, $sel['price']);
				$sheet->setCellValue('M' . $rowIndex2, $sel['stake_amount']);
				$sheet->setCellValue('N' . $rowIndex2, $monto_ganado);
				
				$rowIndex2++;
			} catch (Exception $e) {
				echo json_encode(['error' => 'Error al armar contenido del reporte: ' . $e->getMessage()]);
				exit;
			}
		}

	}

	try {
		$date = new DateTime();
		$file_title = "rpt_apuestas_deportivas_" . $date->getTimestamp();
	} catch (Exception $e) {
		echo json_encode(['error' => 'Fecha inválida: ' . $e->getMessage()]);
		exit;
	}

	try {
		if (!file_exists('/var/www/html/export/files_exported/rpt_apuestas_deportivas/')) {
			mkdir('/var/www/html/export/files_exported/rpt_apuestas_deportivas/', 0777, true);
		}
	} catch (Exception $e) {
		echo json_encode(['error' => 'Error al crear el directorio: ' . $e->getMessage()]);
		exit;
	}

	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="' . $file_title . '.xls"');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$excel_path = '/var/www/html/export/files_exported/rpt_apuestas_deportivas/' . $file_title . '.xls';
	$excel_path_download = '/export/files_exported/rpt_apuestas_deportivas/' . $file_title . '.xls';
	$url = $file_title . '.xls';

	try {
		$objWriter->save($excel_path);
	} catch (PHPExcel_Writer_Exception $e) {
		echo json_encode(["error" => $e]);
		exit;
	}

	$insert_cmd = "INSERT INTO tbl_exported_files (url,tipo,ext,size,fecha_registro,usuario_id)";
	$insert_cmd .= " VALUES ('" . $url . "','excel','xls','" . filesize($excel_path) . "','" . date("Y-m-d h:i:s") . "','" . $login["id"] . "')";

	try {
		$mysqli->query($insert_cmd);
	} catch (mysqli_sql_exception $e) {
		echo json_encode(['error' => 'Error al ejecutar la consulta SQL: ' . $e->getMessage()]);
		exit;
	}

	try {
		$size = filesize($excel_path);
	} catch (Exception $e) {
		echo json_encode(['error' => 'Error al obtener el tamaño del archivo: ' . $e->getMessage()]);
		exit;
	}

	echo json_encode(array(
		"path" => $excel_path_download,
		"url" => $file_title . '.xls',
		"tipo" => "excel",
		"ext" => "xls",
		"size" => $size,
		"fecha_registro" => date("d-m-Y h:i:s"),
		"sql" => $insert_cmd
	));

	exit;
}