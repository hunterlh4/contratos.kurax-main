<?php

include("../sys/db_connect.php");
include("../sys/sys_login.php");
date_default_timezone_set("America/Lima");

if(isset($_POST["export_reporte_kasnet"])){
	$data = $_POST["export_reporte_kasnet"];
	$list_where = "
		WHERE k.id IN(
			SELECT max(id) FROM tbl_saldo_kasnet WHERE created_at IN(
				SELECT max(created_at) FROM tbl_saldo_kasnet WHERE estado =1 GROUP BY local_id
			)
			GROUP BY local_id
		)
		AND lcc.estado = 1
	";
	if($data["tipo"] != "") $list_where.=" AND (k.tipo_id=".$data["tipo"]." OR k.sub_tipo_id=".$data["tipo"]." )";
	if($data["zona"] != "") $list_where.=" AND l.zona_id ".(($data["zona"] != -1)? ("=".$data["zona"]):"IS NULL");
	if($login["usuario_locales"]) $list_where.= " AND l.id IN (".implode(",", $login["usuario_locales"]).")";
	if($data['filter'] != ""){
		$list_where .= " AND (
				k.caja_id 		LIKE '%{$data['filter']}%' OR
				l.cc_id 		LIKE '%{$data['filter']}%' OR
				l.id 			LIKE '%{$data['filter']}%' OR
				l.nombre 		LIKE '%{$data['filter']}%' OR
				k.saldo_final 	LIKE '%{$data['filter']}%' OR
				k.created_at 	LIKE '%{$data['filter']}%'
			)
		";
	}

	$list_where .= " AND lp.estado = 1";
	$list_where .= " AND lp.habilitado = 1";

	$mysqli->query("START TRANSACTION");
	$list_query=$mysqli->query("SELECT
		l.cc_id AS cc_id,
		l.nombre AS local,
		lp.proveedor_id AS terminal,
		z.nombre AS zona_nombre,
		k.saldo_final,
		(if (k.tipo_id=1,kt.nombre,kt2.nombre)) as kasnet_tipo,
		k.created_at
	  	FROM tbl_saldo_kasnet k
		INNER JOIN tbl_saldo_kasnet_tipo kt ON kt.id = k.tipo_id
		LEFT JOIN tbl_saldo_kasnet_tipo kt2 ON kt2.id = k.sub_tipo_id
		INNER JOIN tbl_locales l ON l.id = k.local_id
		INNER JOIN tbl_local_proveedor_id lp ON (lp.local_id = k.local_id AND servicio_id = 7)
		LEFT JOIN tbl_local_caja_config lcc ON (lcc.local_id = k.local_id AND campo = 'saldo_kasnet')
		LEFT JOIN tbl_zonas z ON z.id = l.zona_id
		$list_where
		ORDER BY k.created_at DESC");
	$mysqli->query("COMMIT");

	$table=[
		[
			"cc_id" 		=> "CC ID",
			"local" 		=> "LOCAL",
			"terminal" 		=> "TERMINAL",
			"zona_nombre" 	=> "ZONA",
			"saldo_final" 	=> "SALDO ACTUAL",
			"kasnet_tipo" 	=> "TIPO",
			"created_at" 	=> "FECHA"
		]
	];
	while ($row=$list_query->fetch_assoc()){
		$table[] = [
			"cc_id" 		=> $row["cc_id"],
			"local" 		=> $row["local"],
			"terminal" 		=> $row["terminal"],
			"zona_nombre" 	=> $row["zona_nombre"],
			"saldo_final" 	=> $row["saldo_final"],
			"kasnet_tipo" 	=> $row["kasnet_tipo"],
			"created_at" 	=> $row["created_at"]
		];
	}
	require_once('../phpexcel/classes/PHPExcel.php');

	$doc = new PHPExcel();
	$doc->setActiveSheetIndex(0);
	$doc->getActiveSheet()->fromArray($table);

	$filename = "reporte_saldo_kasnet_".date("Ymdhis").".xls";
	$excel_path = '/var/www/html/export/files_exported/reporte_kasnet/'.$filename;

	$objWriter = PHPExcel_IOFactory::createWriter($doc, 'Excel5');
	$objWriter->save($excel_path);

	echo json_encode(array(
		"path" => '/export/files_exported/reporte_kasnet/'.$filename,
		"tipo" => "excel",
		"ext" => "xls",
		"size" => filesize($excel_path),
		"fecha_registro" => date("Y-m-d h:i:s"),
	));
	exit;
}

if(isset($_POST["export_reporte_history_kasnet"])){
	$data = $_POST["export_reporte_history_kasnet"];
	$list_where="WHERE k.estado = 1";
	$fecha_inicio = $data['start_date'];
    $fecha_fin = $data['end_date'];

	if($login["usuario_locales"]) 	$list_where.=" AND l.id IN (".implode(",", $login["usuario_locales"]).")";

	if($data["tipo"] != "") 		$list_where.=" AND k.tipo_id=".$data["tipo"];
	if($data["local_id"] != "all") 	$list_where.=" AND l.id =".$data["local_id"];

	$where_fecha = "";
    if (!empty($fecha_inicio) && !empty($fecha_fin)) {
        $list_where.= " AND k.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
    } elseif (!empty($fecha_inicio)) {
        $list_where.= " AND k.created_at >= '$fecha_inicio 00:00:00'";
    } elseif (!empty($fecha_fin)) {
        $list_where.= " AND k.created_at <= '$fecha_fin 23:59:59'";
    }

	$mysqli->query("START TRANSACTION");
	$list_query=$mysqli->query("SELECT
		l.cc_id,
		l.nombre as local_nombre,
		k.created_at,
		CONCAT('Turno ',c.turno_id) as turno_id,
		kt.nombre as kasnet_tipo,
		k.saldo_anterior,
		k.saldo_incremento,
		k.saldo_final,
		CONCAT(IFNULL(p.nombre,''), ' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) as personal_nombre
	  	FROM tbl_saldo_kasnet k
	  	INNER JOIN tbl_saldo_kasnet_tipo kt ON kt.id = k.tipo_id
		INNER JOIN tbl_locales l ON l.id = k.local_id
		LEFT JOIN tbl_login lo ON lo.sesion_cookie = k.session_cookie
		LEFT JOIN tbl_usuarios u ON u.id = lo.usuario_id
		LEFT JOIN tbl_personal_apt p ON p.id = u.personal_id
		LEFT JOIN tbl_caja c ON c.id = k.caja_id
		$list_where
		ORDER BY k.created_at DESC");
	$mysqli->query("COMMIT");

	$table=[
		[
			"cc_id" 			=> "CC ID",
			"local_nombre" 		=> "LOCAL",
			"created_at" 		=> "FECHA",
			"turno_id" 			=> "TURNO",
			"kasnet_tipo" 		=> "TIPO",
			"saldo_anterior" 	=> "ANTERIOR",
			"saldo_incremento" 	=> "INCREMENTO",
			"saldo_final" 		=> "FINAL",
			"personal_nombre" 	=> "USUARIO"
		]
	];
	while ($row=$list_query->fetch_assoc()){
		$table[] = [
			"cc_id" 			=> $row["cc_id"],
			"local_nombre" 		=> $row["local_nombre"],
			"created_at" 		=> $row["created_at"],
			"turno_id" 			=> $row["turno_id"],
			"kasnet_tipo" 		=> $row["kasnet_tipo"],
			"saldo_anterior" 	=> $row["saldo_anterior"],
			"saldo_incremento" 	=> $row["saldo_incremento"],
			"saldo_final" 		=> $row["saldo_final"],
			"personal_nombre" 	=> $row["personal_nombre"]
		];
	}
	require_once('../phpexcel/classes/PHPExcel.php');

	$doc = new PHPExcel();
	$doc->setActiveSheetIndex(0);
	$doc->getActiveSheet()->fromArray($table);

	$filename = "reporte_saldo_historico_kasnet_".date("Ymdhis").".xls";
	$excel_path = '/var/www/html/export/files_exported/'.$filename;

	$objWriter = PHPExcel_IOFactory::createWriter($doc, 'Excel5');
	$objWriter->save($excel_path);

	echo json_encode(array(
		"path" => '/export/files_exported/'.$filename,
		"tipo" => "excel",
		"ext" => "xls",
		"size" => filesize($excel_path),
		"fecha_registro" => date("Y-m-d h:i:s"),
	));
	exit;
}

?>
