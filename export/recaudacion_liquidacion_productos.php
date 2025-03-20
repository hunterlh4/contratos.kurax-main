<?php

include("../sys/db_connect.php");
include("../sys/sys_login.php");
include '/var/www/html/phpexcel/classes/PHPExcel.php';

if(isset($_POST["export_liquidacion_productos"])){
	$data = $_POST["export_liquidacion_productos"];
	$data["fecha_fin"] = date('Y-m-d', strtotime("+1 days", strtotime($data["fecha_fin"])));

	$locales_with_products = [];
	$result = $mysqli->query("SELECT local_id FROM tbl_local_caja_detalle_tipos WHERE detalle_tipos_id IN(13,17)");
	while($r = $result->fetch_assoc()) $locales_with_products[] = $r["local_id"];

	$list_where = " WHERE l.id IN (".implode(",", $locales_with_products).")";
	if($login["usuario_locales"])		$list_where .= " AND l.id IN (".implode(",", $login["usuario_locales"]).")";
	if(isset($data["locales"]))			$list_where .= " AND l.id IN (".implode(",", $data["locales"]).")";
	if(isset($data["zonas"]))			$list_where .= " AND l.zona_id IN (".implode(",", $data["zonas"]).")";

	$table = [];

	$foot = [
		'total_in' 	=> 0,
		'total_out' => 0,
		'total' 	=> 0
	];

	$mysqli->query("START TRANSACTION");
	if(!isset($data["productos"]) || in_array(32, $data["productos"])){
		$result = $mysqli->query("
			SELECT
				IFNULL(SUM(ar.total), 0) AS total_in,
				IFNULL(SUM(ar.note_total), 0) AS total_out,
				IFNULL((SUM(ar.total) - SUM(ar.note_total)), 0) AS total
			FROM tbl_locales l 
			LEFT JOIN tbl_repositorio_atsnacks_resumen ar ON (ar.local_id = l.id AND created_at >= '{$data['fecha_inicio']}' AND created_at < '{$data['fecha_fin']}')
			{$list_where}
		");
		while($r = $result->fetch_assoc()){
			$foot["total_in"]	+= $r["total_in"];
			$foot["total_out"]	+= $r["total_out"];
			$foot["total"]		+= $r["total"];
		}

		$result = $mysqli->query("
			SELECT
			    l.id as local_id,
			    l.cc_id,
			    l.nombre,
				datediff('{$data['fecha_fin']}', '{$data['fecha_inicio']}') AS days,
				IFNULL(SUM(ar.total), 0) AS total_in,
				IFNULL(SUM(ar.note_total), 0) AS total_out,
				IFNULL((SUM(ar.total) - SUM(ar.note_total)), 0) AS total
			FROM tbl_locales l 
			LEFT JOIN tbl_repositorio_atsnacks_resumen ar ON (ar.local_id = l.id AND created_at >= '{$data['fecha_inicio']}' AND created_at < '{$data['fecha_fin']}')
			{$list_where}
			GROUP BY l.id
		");
		while($r = $result->fetch_assoc()){
			$table[$r["local_id"]]["local_id"] 	= $r["local_id"];
			$table[$r["local_id"]]["cc_id"] 	= $r["cc_id"];
			$table[$r["local_id"]]["nombre"] 	= $r["nombre"];
			$table[$r["local_id"]]["days"] 		= $r["days"];

			$table[$r["local_id"]]['productos']['ATSnacks'] = [
				'total_in' 	=> $r["total_in"],
				'total_out' => $r["total_out"],
				'total' 	=> $r["total"]
			];
		}
	}

	if(!isset($data["productos"]) || in_array(28, $data["productos"])){
		$result = $mysqli->query("
			SELECT
				IFNULL(SUM(kr.total_in), 0) AS total_in,
				IFNULL(SUM(kr.total_out), 0) AS total_out,
				IFNULL((SUM(kr.total)), 0) AS total
			FROM tbl_locales l 
			LEFT JOIN tbl_repositorio_kasnet_resumen kr ON (kr.local_id = l.id AND created_at >= '{$data['fecha_inicio']}' AND created_at < '{$data['fecha_fin']}')
			{$list_where}
		");
		while($r = $result->fetch_assoc()){
			$foot["total_in"]	+= $r["total_in"];
			$foot["total_out"]	+= $r["total_out"];
			$foot["total"]		+= $r["total"];
		}

		$result = $mysqli->query("
			SELECT
			    l.id as local_id,
			    l.cc_id,
			    l.nombre,
				datediff('{$data['fecha_fin']}', '{$data['fecha_inicio']}') AS days,
				IFNULL(SUM(kr.total_in), 0) AS total_in,
				IFNULL(SUM(kr.total_out), 0) AS total_out,
				IFNULL((SUM(kr.total)), 0) AS total
			FROM tbl_locales l 
			LEFT JOIN tbl_repositorio_kasnet_resumen kr ON (kr.local_id = l.id AND created_at >= '{$data['fecha_inicio']}' AND created_at < '{$data['fecha_fin']}')
			{$list_where}
			GROUP BY l.id
		");
		while($r = $result->fetch_assoc()){
			$table[$r["local_id"]]["local_id"] 	= $r["local_id"];
			$table[$r["local_id"]]["cc_id"] 	= $r["cc_id"];
			$table[$r["local_id"]]["nombre"] 	= $r["nombre"];
			$table[$r["local_id"]]["days"] 		= $r["days"];

			$table[$r["local_id"]]['productos']['Kasnet'] = [
				'total_in' 	=> $r["total_in"],
				'total_out' => $r["total_out"],
				'total' 	=> $r["total"]
			];
		}
	}

	$mysqli->query("COMMIT");

	foreach ($table as $local_id => $local) {
			$table[$local_id]['productos']["Total"] = [
				'total_in' 	=> 0,
				'total_out' => 0,
				'total' 	=> 0
			];
		foreach ($local["productos"] as $producto) {
			$table[$local_id]['productos']["Total"]["total_in"] 	+= $producto["total_in"];
			$table[$local_id]['productos']["Total"]["total_out"] 	+= $producto["total_out"];
			$table[$local_id]['productos']["Total"]["total"] 		+= $producto["total"];
		}
	}

	$body = "";
	if(!empty($table)){
		$rowspan = 1;

		$body .= '<table>';
		$body .= '<thead>';
		$body .= '<tr>';
		$body .= '<th>CC ID</th>';
		$body .= '<th>NOMBRE LOCAL</th>';
		$body .= '<th>DIAS</th>';
		$body .= '<th>PRODUCTO</th>';
		$body .= '<th>ENTRADA</th>';
		$body .= '<th>SALIDA</th>';
		$body .= '<th>RESULTADO</th>';
		$body .= '</tr>';
		$body .= '</thead>';
		$body .= '<tbody>';

		foreach ($table as $local_id => $local) {
			foreach ($local['productos'] as $prod_nombre => $producto) {
				$body .= '<tr>';
				$body .= '<td>'.$local["cc_id"].'</td>';
				$body .= '<td>'.$local["nombre"].'</td>';
				$body .= '<td>'.$local["days"].'</td>';
				$body .= '<td>'.$prod_nombre.'</td>';
				$body .= '<td>'.number_format($producto["total_in"], 2, ',', '.').'</td>';
				$body .= '<td>'.number_format($producto["total_out"], 2, ',', '.').'</td>';
				$body .= '<td>'.number_format($producto["total"], 2, ',', '.').'</td>';
				$body .= '</tr>';
			}
		}
		$body .= '</tbody>';
		$body .= '<tfoot>';
		$body .= '<tr>';
		$body .= '<td></td>';
		$body .= '<td></td>';
		$body .= '<td></td>';
		$body .= '<td></td>';
		$body .= '<td></td>';
		$body .= '<td>'.number_format($foot["total_in"], 2, ',', '.').'</td>';
		$body .= '<td>'.number_format($foot["total_out"], 2, ',', '.').'</td>';
		$body .= '<td>'.number_format($foot["total"], 2, ',', '.').'</td>';
		$body .= '</tr>';
		$body .= '</tfoot>';
		$body .= '</table>';
	}

	$filename = "recaudacion_liquidacion_productos_".date('Ymd_His').".xls";
	$filepath = "/var/www/html/export/files_exported/".$filename;
	$path = "/export/files_exported/".$filename;

	$file = fopen($filepath, "w+");
	fputs($file, $body);
	fclose($file);

	$insert_cmd = "INSERT INTO tbl_exported_files (url,tipo,ext,size,fecha_registro,usuario_id) VALUES (
		'".$filename."',
		'excel',
		'xls',
		'".filesize($filepath)."',
		'".date("Y-m-d h:i:s")."',
		'".$login["id"]."')";

	$mysqli->query($insert_cmd);

	echo json_encode(["path" => $path, "sql" => $insert_cmd ]);

}

?>