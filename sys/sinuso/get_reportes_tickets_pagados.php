<?php
$result=array();
include("db_connect.php");
include("sys_login.php");
include '/var/www/html/sys/Pagination.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER REGISTROS
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"]==="SecRptTkP_listar_registros") {
	
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$tipo_busqueda             = $_POST["tipo_busqueda"];
	$fecha_inicio              = $_POST["fecha_inicio"];
	$fecha_fin                 = $_POST["fecha_fin"];
	$cliente_tipo              = $_POST["cliente_tipo"];
	$cliente_texto             = $_POST["cliente_texto"];
	$proveedor                 = $_POST["proveedor"];
	$zona                      = $_POST["zona"];
	$num_transaccion           = $_POST["num_transaccion"];

	$where_fecha = "";
	if($tipo_busqueda == 1){
		$where_fecha = " AND DATE(tra.created_at) >= '" . $fecha_inicio . "'  AND DATE(tra.created_at) <= '" . $fecha_fin . "'";
	}else if($tipo_busqueda == 2){
		$where_fecha = " AND DATE(tra_v.created_at) >= '" . $fecha_inicio . "'  AND DATE(tra_v.created_at) <= '" . $fecha_fin . "'";
	}else if($tipo_busqueda == 3){
		$where_fecha = " AND DATE(tra.created_at) >= '" . $fecha_inicio . "'  AND DATE(tra.created_at) <= '" . $fecha_fin . "'";
	}



	$where_cliente = "";
	if(strlen($cliente_texto)>1){
		if((int)$cliente_tipo === 1){ // Por nombres
			$where_cliente = " AND CONCAT( IFNULL(cli.nombre, ''), ' ', IFNULL(cli.apellido_paterno, ''), ' ', IFNULL(cli.apellido_materno, '') ) LIKE '%". $cliente_texto ."%' ";
		}
		if((int)$cliente_tipo === 2){ // Por num doc
			$where_cliente = " AND IFNULL(cli.num_doc, '') = '". $cliente_texto ."' ";
		}
		if((int)$cliente_tipo === 3){ // Por web-id
			$where_cliente = " AND IFNULL(cli.web_id, '') = '". $cliente_texto ."' ";
		}
		if((int)$cliente_tipo === 4){ // Por celular
			$where_cliente = " AND IFNULL(cli.telefono, '') = '". $cliente_texto ."' ";
		}
	}

	$nombre_busqueda = "";
	if(isset($_POST["search"]["value"])) {
		$busqueda_nombre = $_POST["search"]["value"];
		if ($busqueda_nombre !="") {
			$nombre_busqueda = '
			 AND (
			tra.txn_id LIKE "%'.$_POST["search"]["value"].'%"
			cli.num_doc LIKE "%'.$_POST["search"]["value"].'%"
			cli.web_id LIKE "%'.$_POST["search"]["value"].'%"
			cli.nombre LIKE "%'.$_POST["search"]["value"].'%"
			cli.apellido_paterno LIKE "%'.$_POST["search"]["value"].'%"
			cli.apellido_materno LIKE "%'.$_POST["search"]["value"].'%"';
			$nombre_busqueda .= ')';
		}
	}

	$where_proveedor = "";
	if($proveedor > 0){
		$where_proveedor = " AND tra.api_id = " . $proveedor;
	}

	$where_zona="";
	if((int)$zona>0){
		$where_zona=" AND (loc_v.zona_id='".$zona."' OR ce_ssql_v.zona_id='".$zona."') ";
	}

	$where_num_transaccion = "";
	if(strlen($num_transaccion)>0){
		$where_num_transaccion = " AND tra.txn_id = '" . $num_transaccion."'";
	}

	$order = "";
	$column = array(
		1=>"tra_v.created_at",
		2=>"tra.id",
		3=>"tra.txn_id",
		4=>"tra.monto",
		5=>"tra.created_at",
		6=>"cli.nombre",
		7=>"p.name"
		);
	if(isset($_POST["order"])) {
		if (array_key_exists($_POST['order']['0']['column'],$column)) {
			$order = ' ORDER BY '.$column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' ';
		} else {
			$order = ' ORDER BY tra.id ASC ';
		}
	} else {
		$order = ' ORDER BY tra.id ASC ';
	}

	// Cantidades
	// QUERY
	$query_COUNT = "
		SELECT
			COUNT(*) cant
		FROM tbl_televentas_clientes_transaccion tra
			LEFT JOIN tbl_televentas_clientes cli ON tra.cliente_id = cli.id
			LEFT JOIN tbl_televentas_proveedor p ON tra.api_id = p.id
			LEFT JOIN tbl_televentas_clientes_transaccion tra_v ON tra.txn_id = tra_v.txn_id AND tra_v.tipo_id = 4 AND tra_v.estado = 1 AND tra_v.api_id = tra.api_id

			LEFT JOIN tbl_caja caj_v ON caj_v.id = tra_v.turno_id
			LEFT JOIN tbl_local_cajas loc_caj_v ON loc_caj_v.id = caj_v.local_caja_id
			LEFT JOIN tbl_locales loc_v ON loc_v.id = loc_caj_v.local_id
			LEFT JOIN tbl_caja_eliminados ce_v on ce_v.id = tra_v.turno_id
			LEFT JOIN tbl_local_cajas ce_sqlc_v ON ce_sqlc_v.id = ce_v.local_caja_id
			LEFT JOIN tbl_locales ce_ssql_v ON ce_ssql_v.id = ce_sqlc_v.local_id

			LEFT JOIN tbl_caja caj ON caj.id = tra.turno_id
			LEFT JOIN tbl_local_cajas loc_caj ON loc_caj.id = caj.local_caja_id
			LEFT JOIN tbl_locales loc ON loc.id = loc_caj.local_id
			LEFT JOIN tbl_caja_eliminados ce on ce.id = tra.turno_id
			LEFT JOIN tbl_local_cajas ce_sqlc ON ce_sqlc.id = ce.local_caja_id
			LEFT JOIN tbl_locales ce_ssql ON ce_ssql.id = ce_sqlc.local_id
			LEFT JOIN tbl_televentas_tickets tt ON tra.txn_id = tt.ticket_id AND tra.api_id = tt.proveedor_id

			LEFT JOIN tbl_transacciones_repositorio tr ON tra.txn_id = tr.bet_id AND tra.api_id = 1
			LEFT JOIN tbl_locales loc_r ON loc_r.id = tr.local_id
			LEFT JOIN tbl_zonas z_r ON z_r.id = loc_r.zona_id
		WHERE tra.tipo_id = 5 AND tra.estado = 1 "
		. $where_fecha
		. $where_cliente
		. $where_num_transaccion
		. $nombre_busqueda;

	try {
		$total_query = $mysqli->query($query_COUNT);
		$resultNum = $total_query->fetch_assoc();
		$num_rows = $resultNum['cant'];

		if ($num_rows == 0) {
			$result['result'] = '<tr><td colspan="14" class="text-center">No existen tickets pagados</td></tr>';
			$result['http_code'] = 400;
			echo json_encode($result);
			exit;
		} else if ($num_rows > 0) {
			$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page']))?$_REQUEST['page']:1;
			$per_page = 10;
			$numLinks = 10;
			$total_paginate = ceil($num_rows/$per_page);
			$total_paginate = $total_paginate == 0 ? 1:$total_paginate;
			$page = $page > $total_paginate ? $total_paginate:$page;
			$offset = ($page - 1) * $per_page;
			$paginate = new Pagination('sec_reportes_tickets_pagados_cambiar_de_pagina',$num_rows,$per_page,$numLinks,$page);
		}
	} catch (Exception $e) {
		$result['http_code'] = 500;
		$result['consulta_error'] = $e->getMessage();
		echo json_encode($result);
		exit;
	}

	// QUERY
	$query_1 = "
		SELECT
			tra.txn_id,
			IF(tra.api_id = 1, IFNULL(tr.created, ''),IFNULL(tra_v.created_at,'')) fecha_creacion,
			IF(tra.api_id = 1, UPPER(IFNULL(z_r.nombre,'')), UPPER(IFNULL(z.nombre, ''))) zona_creadora,
			IF(tra.api_id = 1, UPPER(IFNULL(loc_r.nombre, '')),UPPER(IFNULL(loc_v.nombre, IFNULL(ce_ssql_v.nombre,''))) ) caja_creadora,
			tra.user_id AS user_id_creador,
			tra_v.user_id AS user_id_pagador,
			IF(tra.api_id = 1, IFNULL(tr.calc_date,''), IFNULL(tt.calc_date,'')) fecha_calculo,
			IFNULL(tra.created_at,'') fecha_pago,
			UPPER(IFNULL(loc.nombre, ce_ssql.nombre)) caja_pagadora,
			IF(tra.api_id = 1, tr.apostado, IFNULL(tra_v.monto, 0)) monto_apostado,
			IFNULL(tra.monto,0) monto_ganado,
			IFNULL(cli.num_doc,'') num_doc,
			CONCAT( UPPER(IFNULL(cli.nombre, '')), ' ', UPPER(IFNULL(cli.apellido_paterno, '')), ' ', UPPER(IFNULL(cli.apellido_materno, '')) ) cliente,
			UPPER(IFNULL(p.name,'')) proveedor
		FROM tbl_televentas_clientes_transaccion tra
			LEFT JOIN tbl_televentas_clientes cli ON tra.cliente_id = cli.id
			LEFT JOIN tbl_televentas_proveedor p ON tra.api_id = p.id
			LEFT JOIN tbl_televentas_clientes_transaccion tra_v ON tra.txn_id = tra_v.txn_id AND tra_v.tipo_id = 4 AND tra_v.estado = 1 AND tra_v.api_id = tra.api_id

			LEFT JOIN tbl_caja caj_v ON caj_v.id = tra_v.turno_id
			LEFT JOIN tbl_local_cajas loc_caj_v ON loc_caj_v.id = caj_v.local_caja_id
			LEFT JOIN tbl_locales loc_v ON loc_v.id = loc_caj_v.local_id
			LEFT JOIN tbl_caja_eliminados ce_v on ce_v.id = tra_v.turno_id
			LEFT JOIN tbl_local_cajas ce_sqlc_v ON ce_sqlc_v.id = ce_v.local_caja_id
			LEFT JOIN tbl_locales ce_ssql_v ON ce_ssql_v.id = ce_sqlc_v.local_id
			LEFT JOIN tbl_zonas z ON z.id = loc_v.zona_id OR z.id = ce_ssql_v.zona_id

			LEFT JOIN tbl_caja caj ON caj.id = tra.turno_id
			LEFT JOIN tbl_local_cajas loc_caj ON loc_caj.id = caj.local_caja_id
			LEFT JOIN tbl_locales loc ON loc.id = loc_caj.local_id
			LEFT JOIN tbl_caja_eliminados ce on ce.id = tra.turno_id
			LEFT JOIN tbl_local_cajas ce_sqlc ON ce_sqlc.id = ce.local_caja_id
			LEFT JOIN tbl_locales ce_ssql ON ce_ssql.id = ce_sqlc.local_id
			LEFT JOIN tbl_televentas_tickets tt ON tra.txn_id = tt.ticket_id AND tra.api_id = tt.proveedor_id

			LEFT JOIN tbl_transacciones_repositorio tr ON tra.txn_id = tr.bet_id AND tra.api_id = 1
			LEFT JOIN tbl_locales loc_r ON loc_r.id = tr.local_id
			LEFT JOIN tbl_zonas z_r ON z_r.id = loc_r.zona_id

		WHERE tra.tipo_id = 5 AND tra.estado = 1 "
		. $where_fecha
		. $where_cliente
		. $nombre_busqueda
		. $where_proveedor
		. $where_zona
		. $where_num_transaccion
		. $order
		. 'LIMIT ' . $offset . ',' . $per_page;

	$list_query_tp = $mysqli->query($query_1);

	$from = $list_query_tp->num_rows > 0 && $offset == 0 ? 1: $offset;
	$del = $list_query_tp->num_rows > 0  ? $offset + 1: $offset;
	$al = $offset + $list_query_tp->num_rows;

	$list_transaccion = array();
	$list_usuarios = array();
	$html = '';
	$html_footer = '';

	if ($mysqli->error) {
		$result['http_code'] = 500;
		$result["consulta_error"] = $mysqli->error;
		echo json_encode($result);
		exit;
	} else {
		$list_usuarios_id = array();

		while ($li = $list_query_tp->fetch_assoc()) {
			$list_transaccion[] = $li;

			$user_id_creador = trim($li['user_id_creador']);
			if ($user_id_creador !== '') {
			$list_usuarios_id[] = $user_id_creador;
			}

			$user_id_pagador = trim($li['user_id_pagador']);
			if ($user_id_pagador !== '') {
			$list_usuarios_id[] = $user_id_pagador;
			}
		}

		$list_usuarios_id = array_keys(array_flip($list_usuarios_id));
		$result["list_usuarios_id"] = $list_usuarios_id;
		
		$list_usuarios = array();
		if(count($list_usuarios_id) > 0){
			$query = '
				SELECT id, usuario
				FROM tbl_usuarios
				WHERE id IN(' . implode(',', $list_usuarios_id) . ')';
			$list_query = $mysqli->query($query);

			if ($mysqli->error) {
				$result['http_code'] = 500;
				$result["consulta_error"] = $mysqli->error;
				echo json_encode($result);
				exit;
			} else {
				while ($li = $list_query->fetch_assoc()) {
					$list_usuarios[] = $li;
				}
			}
		}
		
	}

	$contador = $del;

	foreach ($list_transaccion as $sel) {
		$user_id_creador = $sel['user_id_creador'];
		$user_id_pagador = $sel['user_id_pagador'];
		$user_creador = '';
		$user_pagador = '';

		foreach ($list_usuarios as $usuario) {
			if ($usuario['id'] == $user_id_creador) {
				$user_creador = $usuario['usuario'];
				if ($user_id_creador == $user_id_pagador) {
					$user_pagador = $user_creador;
					break;
				}
			} elseif ($usuario['id'] == $user_id_pagador) {
				$user_pagador = $usuario['usuario'];
				if (!empty($user_creador)) break;
			}
		}

		$html .= '<tr>';
		$html .= '<td>' . $contador . '</td>';
		$html .= '<td>' . $sel['txn_id'] . '</td>';
		$html .= '<td>' . $sel['fecha_creacion'] . '</td>';
		$html .= '<td>' . $sel['zona_creadora'] . '</td>';
		$html .= '<td>' . $sel['caja_creadora'] . '</td>';
		$html .= '<td>' . $user_creador . '</td>';
		$html .= '<td>FREE GAMES</td>';
		$html .= '<td>' . $sel['fecha_calculo'] . '</td>';
		$html .= '<td>' . $sel['fecha_pago'] . '</td>';
		$html .= '<td>' . $sel['caja_pagadora'] . '</td>';
		$html .= '<td>' . $user_pagador . '</td>';
		$html .= '<td>FREE GAMES</td>';
		$html .= '<td class="text-right">' . number_format(($sel['monto_apostado']), 2, '.', ',') . '</td>';
		$html .= '<td class="text-right">' . number_format(($sel['monto_ganado']), 2, '.', ',') . '</td>';
		$html .= '<td>' . $sel['num_doc'] . '</td>';
		$html .= '<td>' . $sel['cliente'] . '</td>';
		$html .= '<td>' . $sel['proveedor'] . '</td>';
		$html .= '</tr>';

		$contador++;
	}

	$html_footer .= '<div class="row">';
	$html_footer .= '<div class="col-md-4">';
	$html_footer .= '<br>';
	$html_footer .= '<small>Mostrando del  ' . $del . ' al ' . $al . ' de ' . $num_rows . ' registros.</small>';
	$html_footer .= '</div>';
	$html_footer .= '<div class="col-md-4 text-center">' . $paginate->createLinks() . '</div>';
	$html_footer .= '<div class="col-md-4"></div>';
	$html_footer .= '</div>';

	$result["http_code"] = 200;
	$result["result"] = $html;
	$result["result_footer"] = $html_footer;

	echo json_encode($result, JSON_UNESCAPED_SLASHES);
	exit;
}

//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER TRANSACCIONES X CAJERO --> EXCEL
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"] === "SecRptTkP_exportar_xls") {
	global $mysqli;
	$usuario_id = $login ? $login['id'] : 0;

	$tipo_busqueda = $_POST["tipo_busqueda"];
	$fecha_inicio  = $_POST["fecha_inicio"];
	$fecha_fin     = $_POST["fecha_fin"];
	$cliente_tipo  = $_POST["cliente_tipo"];
	$cliente_texto = $_POST["cliente_texto"];
	$proveedor     = $_POST["proveedor"];
	$zona          = $_POST["zona"];
	$num_transaccion = $_POST["num_transaccion"];

	$where_fecha = "";
	if($tipo_busqueda == 1){
		$where_fecha = " AND DATE(tra.created_at) >= '" . $fecha_inicio . "'  AND DATE(tra.created_at) <= '" . $fecha_fin . "'";
	}else if($tipo_busqueda == 2){
		$where_fecha = " AND DATE(tra_v.created_at) >= '" . $fecha_inicio . "'  AND DATE(tra_v.created_at) <= '" . $fecha_fin . "'";
	}else if($tipo_busqueda == 3){
		$where_fecha = " AND DATE(tra.created_at) >= '" . $fecha_inicio . "'  AND DATE(tra.created_at) <= '" . $fecha_fin . "'";
	}

	$where_cliente = "";
	if(strlen($cliente_texto)>1){
		if((int)$cliente_tipo === 1){ // Por nombres
			$where_cliente = " AND CONCAT(IFNULL(cli.nombre,''), ' ', IFNULL(cli.apellido_paterno,''), ' ', IFNULL(cli.apellido_materno,'')) LIKE '%". $cliente_texto ."%' ";
		}
		if((int)$cliente_tipo === 2){ // Por num doc
			$where_cliente = " AND IFNULL(cli.num_doc, '') = '". $cliente_texto ."' ";
		}
		if((int)$cliente_tipo === 3){ // Por web-id
			$where_cliente = " AND IFNULL(cli.web_id, '') = '". $cliente_texto ."' ";
		}
		if((int)$cliente_tipo === 4){ // Por celular
			$where_cliente = " AND IFNULL(cli.telefono, '') = '". $cliente_texto ."' ";
		}
	}

	$nombre_busqueda = "";
	if(isset($_POST["search"]["value"])) {
		$busqueda_nombre = $_POST["search"]["value"];
		if ($busqueda_nombre !="") {
			$nombre_busqueda = '
			AND (
			cli.nombre LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.apellido_paterno LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.apellido_materno LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.num_doc LIKE "%'.$_POST["search"]["value"].'%" ';
			$nombre_busqueda .= ')';
		}
	}

	$where_proveedor = "";
	if($proveedor > 0){
		$where_proveedor = " AND tra.api_id = " . $proveedor;
	}

	$where_zona="";
	if((int)$zona>0){
		$where_zona=" AND (loc_v.zona_id='".$zona."' OR ce_ssql_v.zona_id='".$zona."') ";
	}

	$where_num_transaccion = "";
	if(strlen($num_transaccion)>0){
		$where_num_transaccion = " AND tra.txn_id = '" . $num_transaccion."'";
	}

	// QUERY
	$query_1 ="
		SELECT 
			tra.txn_id,
            IF(tra.api_id = 1, IFNULL(tr.created, ''),IFNULL(tra_v.created_at,'')) fecha_creacion,
            IF(tra.api_id = 1, UPPER(IFNULL(z_r.nombre,'')), UPPER(IFNULL(z.nombre, ''))) zona_creadora,
            IF(tra.api_id = 1, UPPER(IFNULL(loc_r.nombre, '')),UPPER(IFNULL(loc_v.nombre, IFNULL(ce_ssql_v.nombre,''))) ) caja_creadora,
            IFNULL(us.usuario,'') user_creador,
			IF(tra.api_id = 1, IFNULL(tr.calc_date,''), IFNULL(tt.calc_date,'')) fecha_calculo,
			IFNULL(tra.created_at,'') fecha_pago,
			UPPER(IFNULL(loc.nombre, ce_ssql.nombre)) caja_pagadora,
			IFNULL(us_v.usuario,'') user_pagador,
            IF(tra.api_id = 1, tr.apostado, IFNULL(tra_v.monto, 0)) monto_apostado,
			IFNULL(tra.monto,0) monto_ganado,
			IFNULL(cli.num_doc,'') num_doc,
			CONCAT( UPPER(IFNULL(cli.nombre, '')), ' ', UPPER(IFNULL(cli.apellido_paterno, '')), ' ', UPPER(IFNULL(cli.apellido_materno, '')) ) cliente,
			UPPER(IFNULL(p.name,'')) proveedor
		FROM tbl_televentas_clientes_transaccion tra
			LEFT JOIN tbl_usuarios us ON tra.user_id = us.id
			LEFT JOIN tbl_televentas_clientes cli ON tra.cliente_id = cli.id
			LEFT JOIN tbl_televentas_proveedor p ON tra.api_id = p.id
			LEFT JOIN tbl_televentas_clientes_transaccion tra_v ON tra.txn_id = tra_v.txn_id AND tra_v.tipo_id = 4 AND tra_v.estado = 1 AND tra_v.api_id = tra.api_id
			LEFT JOIN tbl_usuarios us_v ON tra_v.user_id = us_v.id

			LEFT JOIN tbl_caja caj_v ON caj_v.id = tra_v.turno_id
			LEFT JOIN tbl_local_cajas loc_caj_v ON loc_caj_v.id = caj_v.local_caja_id
			LEFT JOIN tbl_locales loc_v ON loc_v.id = loc_caj_v.local_id
			LEFT JOIN tbl_caja_eliminados ce_v on ce_v.id = tra_v.turno_id
			LEFT JOIN tbl_local_cajas ce_sqlc_v ON ce_sqlc_v.id = ce_v.local_caja_id 
			LEFT JOIN tbl_locales ce_ssql_v ON ce_ssql_v.id = ce_sqlc_v.local_id 
			LEFT JOIN tbl_zonas z ON z.id = loc_v.zona_id OR z.id = ce_ssql_v.zona_id 

			LEFT JOIN tbl_caja caj ON caj.id = tra.turno_id
			LEFT JOIN tbl_local_cajas loc_caj ON loc_caj.id = caj.local_caja_id
			LEFT JOIN tbl_locales loc ON loc.id = loc_caj.local_id
			LEFT JOIN tbl_caja_eliminados ce on ce.id = tra.turno_id
			LEFT JOIN tbl_local_cajas ce_sqlc ON ce_sqlc.id = ce.local_caja_id 
			LEFT JOIN tbl_locales ce_ssql ON ce_ssql.id = ce_sqlc.local_id 
            LEFT JOIN tbl_televentas_tickets tt ON tra.txn_id = tt.ticket_id AND tra.api_id = tt.proveedor_id
            
            LEFT JOIN tbl_transacciones_repositorio tr ON tra.txn_id = tr.bet_id AND tra.api_id = 1
			LEFT JOIN tbl_locales loc_r ON loc_r.id = tr.local_id
			LEFT JOIN tbl_zonas z_r ON z_r.id = loc_r.zona_id
            
		WHERE tra.tipo_id = 5 AND tra.estado = 1 "
		.$where_fecha
		.$where_cliente
		.$nombre_busqueda
		.$where_proveedor
		.$where_zona 
		.$where_num_transaccion
		."  ORDER BY tra.id ASC ";
	$list_query = $mysqli->query($query_1);
	$result_data = array();
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
		echo json_encode([
			"error" => "Export error"
		]);
		exit;
	} else {
		ini_set('display_errors', 0);
		ini_set('display_startup_errors', 0);
		error_reporting(0);

		require_once '../phpexcel/classes/PHPExcel.php';
		$objPHPExcel = new PHPExcel();
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

		$sheet->setCellValue('A' . $lastRowIndex, 'REPORTE DE TICKETS PAGADOS');

		$objPHPExcel->getActiveSheet()->getStyle('A'.$lastRowIndex)->applyFromArray($estilo);

		$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $lastRowIndex.':Q'. $lastRowIndex);

		$lastRowIndex = $lastRowIndex + 1;

		$sheet->setCellValue('A' . $lastRowIndex, 'ID TX');
		$sheet->setCellValue('B' . $lastRowIndex, 'FECHA CREACIÓN');
		$sheet->setCellValue('C' . $lastRowIndex, 'ZONA CREADORA');
		$sheet->setCellValue('D' . $lastRowIndex, 'CAJA CREADORA');
		$sheet->setCellValue('E' . $lastRowIndex, 'USUARIO CREADOR');
		$sheet->setCellValue('F' . $lastRowIndex, 'RAZON SOCIAL CREADOR');
		$sheet->setCellValue('G' . $lastRowIndex, 'FECHA CALCULO');
		$sheet->setCellValue('H' . $lastRowIndex, 'FECHA PAGO');
		$sheet->setCellValue('I' . $lastRowIndex, 'CAJA PAGADORA');
		$sheet->setCellValue('J' . $lastRowIndex, 'USUARIO PAGADOR');
		$sheet->setCellValue('K' . $lastRowIndex, 'RAZON SOCIAL PAGADOR');
		$sheet->setCellValue('L' . $lastRowIndex, 'MONTO APOSTADO');
		$sheet->setCellValue('M' . $lastRowIndex, 'MONTO PAGADO');
		$sheet->setCellValue('N' . $lastRowIndex, 'NUM DOC');
		$sheet->setCellValue('O' . $lastRowIndex, 'CLIENTE');
		$sheet->setCellValue('P' . $lastRowIndex, 'PROVEEDOR');

		$lastRowIndex = $lastRowIndex + 1;

		$rowIndex2 = $lastRowIndex;

		while ($sel = $list_query->fetch_assoc()) {
			try {
				$sheet->setCellValue('A' . $rowIndex2, $sel['txn_id']);
				$sheet->setCellValue('B' . $rowIndex2, $sel['fecha_creacion']);
				$sheet->setCellValue('C' . $rowIndex2, $sel['zona_creadora']);
				$sheet->setCellValue('D' . $rowIndex2, $sel['caja_creadora']);
				$sheet->setCellValue('E' . $rowIndex2, $sel['user_creador']);
				$sheet->setCellValue('F' . $rowIndex2, 'FREE GAMES');
				$sheet->setCellValue('G' . $rowIndex2, $sel['fecha_calculo']);
				$sheet->setCellValue('H' . $rowIndex2, $sel['fecha_pago']);
				$sheet->setCellValue('I' . $rowIndex2, $sel['caja_pagadora']);
				$sheet->setCellValue('J' . $rowIndex2, $sel['user_pagador']);
				$sheet->setCellValue('K' . $rowIndex2, 'FREE GAMES');
				$sheet->setCellValue('L' . $rowIndex2, $sel['monto_apostado']);
				$sheet->setCellValue('M' . $rowIndex2, $sel['monto_ganado']);
				$sheet->setCellValue('N' . $rowIndex2, $sel['num_doc']);
				$sheet->setCellValue('O' . $rowIndex2, $sel['cliente']);
				$sheet->setCellValue('P' . $rowIndex2, $sel['proveedor']);

				
				$rowIndex2++;
			} catch (Exception $e) {
				echo json_encode(['error' => 'Error al armar contenido del reporte: ' . $e->getMessage()]);
				exit;
			}
		}

		$date = new DateTime();
		$file_title = "reporte_tickets_pagados_" . $date->getTimestamp();

		if (!file_exists('/var/www/html/export/files_exported/reporte_premios/')) {
			mkdir('/var/www/html/export/files_exported/reporte_premios/', 0777, true);
		}

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . $file_title . '.xls"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$excel_path = '/var/www/html/export/files_exported/reporte_premios/' . $file_title . '.xls';
		$excel_path_download = '/export/files_exported/reporte_premios/' . $file_title . '.xls';
		$url = $file_title . '.xls';
		try {
			$objWriter->save($excel_path);
		} catch (PHPExcel_Writer_Exception $e) {
			echo json_encode(["error" => $e]);
			exit;
		}

		$insert_cmd = "INSERT INTO tbl_exported_files (url,tipo,ext,size,fecha_registro,usuario_id)";
		$insert_cmd .= " VALUES ('" . $url . "','excel','xls','" . filesize($excel_path) . "','" . date("Y-m-d h:i:s") . "','" . $login["id"] . "')";
		$mysqli->query($insert_cmd);

		echo json_encode(array(
			"path" => $excel_path_download,
			"url" => $file_title . '.xls',
			"tipo" => "excel",
			"ext" => "xls",
			"size" => filesize($excel_path),
			"fecha_registro" => date("d-m-Y h:i:s"),
			"sql" => $insert_cmd
		));
		exit;
	}
}

echo json_encode($result);
?>