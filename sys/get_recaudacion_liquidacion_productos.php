<?php
include("db_connect.php");
include("sys_login.php");

if(isset($_POST["get_tabla_liquidacion_productos"])){
	$data = $_POST["get_tabla_liquidacion_productos"];
	$data["fecha_fin"] = date('Y-m-d', strtotime("+1 days", strtotime($data["fecha_fin"])));
	$data['offset'] = $data['limit']*$data['page'];

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
			LIMIT {$data['limit']} OFFSET {$data['offset']}
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
			LIMIT {$data['limit']} OFFSET {$data['offset']}
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

	$num_rows = $mysqli->query("
		SELECT id
		FROM tbl_locales l 
		$list_where
		GROUP BY l.id
	")->num_rows;
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
		$rowspan = count($table[key($table)]['productos']);

		$body .= '<thead>';
		$body .= '<tr class="small">';
		$body .= '<th class="hidden">LOCAL ID</th>';
		$body .= '<th style="width: 100px position: fixed; top: 0;" class="bg-header text-bold" rowspan="'.$rowspan.'">CC ID</th>';
		$body .= '<th style="width: 400px position: fixed; top: 0;" class="bg-header text-bold text-left" rowspan="'.$rowspan.'">NOMBRE LOCAL</th>';
		$body .= '<th style="width: 100px position: fixed; top: 0;" class="bg-header text-bold text-right" rowspan="'.$rowspan.'">DIAS</th>';
		$body .= '<th style="width: 180px position: fixed; top: 0;" class="bg-header text-bold text-left">PRODUCTO</th>';
		$body .= '<th style="width: 180px position: fixed; top: 0;" class="bg-header text-bold text-right">ENTRADA</th>';
		$body .= '<th style="width: 180px position: fixed; top: 0;" class="bg-header text-bold text-right">SALIDA</th>';
		$body .= '<th style="width: 180px position: fixed; top: 0;" class="bg-header text-bold text-right">RESULTADO</th>';
		$body .= '</tr>';
		$body .= '</thead>';
		$body .= '<tbody>';

		foreach ($table as $local_id => $local) {
			$body .= '<tr>';
			$body .= '<td style="padding:1px !important;" rowspan="'.$rowspan.'" class="hidden">'.$local_id.'</td>';
			$body .= '<td style="padding:1px !important;" rowspan="'.$rowspan.'" class="bg-primary text-left">'.$local["cc_id"].'</td>';
			$body .= '<td style="padding:1px !important;" rowspan="'.$rowspan.'" class="text-left bg-primary">'.$local["nombre"].'</td>';
			$body .= '<td style="padding:1px !important;" rowspan="'.$rowspan.'" class="bg-primary">'.$local["days"].'</td>';
			$first = true;
			foreach ($local['productos'] as $prod_nombre => $producto) {
				if(!$first){
					$body .= '<tr>';
					$first = false;
				}
				$body .= '<td style="'.(($prod_nombre == "Total") ? "color:blue; background-color:#9BE0FD;" : "").' padding:1px !important;" class="text-left small">'.$prod_nombre.'</td>';
				$body .= '<td style="'.(($prod_nombre == "Total") ? "color:blue; background-color:#9BE0FD;" : "").' padding:1px !important;" class="text-right small">'.number_format($producto["total_in"], 2, '.', ',').'</td>';
				$body .= '<td style="'.(($prod_nombre == "Total") ? "color:blue; background-color:#9BE0FD;" : "").' padding:1px !important;" class="text-right small">'.number_format($producto["total_out"], 2, '.', ',').'</td>';
				$body .= '<td style="'.(($prod_nombre == "Total") ? "color:blue; background-color:#9BE0FD;" : "").' padding:1px !important;" class="text-right small">'.number_format($producto["total"], 2, '.', ',').'</td>';
				$body .= '</tr>';
			}
		}
		$body .= '</tbody>';
		$body .= '<tfoot>';
		$body .= '<tr>';
		$body .= '<td class="hidden"></td>';
		$body .= '<td class="bg-primary"></td>';
		$body .= '<td class="bg-primary"></td>';
		$body .= '<td class="bg-primary"></td>';
		$body .= '<td></td>';
		$body .= '<td class="text-right text-bold">Total:<br> <span class="text-success">'.number_format($foot["total_in"], 2, '.', ',').'</span></td>';
		$body .= '<td class="text-right text-bold">Total:<br> <span class="text-success">'.number_format($foot["total_out"], 2, '.', ',').'</span></td>';
		$body .= '<td class="text-right text-bold">Total:<br> <span class="text-success">'.number_format($foot["total"], 2, '.', ',').'</span></td>';
		$body .= '</tr>';
		$body .= '</tfoot>';
	}

	

	echo json_encode(['body' => $body, 'num_rows' => $num_rows]);
}

?>