<?php
include("db_connect.php");
include("sys_login.php");

if (isset($_POST["get_tabla_saldo_kasnet"])) {
	$data = $_POST["get_tabla_saldo_kasnet"];
	$data['offset'] = $data['limit'] * $data['page'];
	$list_where = "
		WHERE k.id IN(
			SELECT max(id) FROM tbl_saldo_kasnet WHERE created_at IN(
				SELECT max(created_at) FROM tbl_saldo_kasnet WHERE estado =1 GROUP BY local_id
			)
			GROUP BY local_id
		)
		AND lcc.estado = 1
	";

	if ($data["tipo"] != "") $list_where .= " AND (k.tipo_id=" . $data['tipo'] . " OR k.sub_tipo_id=" . $data['tipo'] . ")";
	if ($data["zona"] != "") $list_where .= " AND l.zona_id " . (($data["zona"] != -1) ? ("=" . $data["zona"]) : "IS NULL");
	if ($login["usuario_locales"]) $list_where .= " AND l.id IN (" . implode(",", $login["usuario_locales"]) . ")";
	if ($data['filter'] != "") {
		$list_where .= " AND (
			k.caja_id 			LIKE '%{$data['filter']}%' OR
			l.cc_id 			LIKE '%{$data['filter']}%' OR
			l.id 				LIKE '%{$data['filter']}%' OR
			lp.proveedor_id 	LIKE '%{$data['filter']}%' OR
			l.nombre 			LIKE '%{$data['filter']}%' OR
			k.saldo_final 		LIKE '%{$data['filter']}%' OR
			k.created_at 		LIKE '%{$data['filter']}%'
		)
		";
	}
	$list_where .= " AND lp.estado = 1";
	$list_where .= " AND lp.habilitado = 1";

	$mysqli->query("START TRANSACTION");

	$query = "
		SELECT
			k.caja_id,
			k.local_id,
			lp.proveedor_id as terminal,
			z.nombre as zona_nombre,
			l.cc_id as cc_id,
			l.nombre as local,
			k.saldo_final,
			(if (k.tipo_id=1,kt.nombre,kt2.nombre)) as kasnet_tipo,
			k.created_at,
			k.tipo_id,
			IFNULL(lcc.valor,0) as monto_kasnet
		FROM tbl_saldo_kasnet k
		INNER JOIN tbl_saldo_kasnet_tipo kt ON kt.id = k.tipo_id
		LEFT JOIN tbl_saldo_kasnet_tipo kt2 ON kt2.id = k.sub_tipo_id
		INNER JOIN tbl_locales l ON l.id = k.local_id
		INNER JOIN tbl_local_proveedor_id lp ON (lp.local_id = k.local_id AND servicio_id = 7)
		LEFT JOIN tbl_local_caja_config lcc ON (lcc.local_id = k.local_id AND campo = 'saldo_kasnet')
		LEFT JOIN tbl_zonas z ON z.id = l.zona_id
		$list_where
		ORDER BY k.created_at DESC
		LIMIT {$data['limit']} OFFSET {$data['offset']}
	";
	$list_query = $mysqli->query($query);

	$num_rows = $mysqli->query("
		SELECT
			k.id
		FROM tbl_saldo_kasnet k
		INNER JOIN tbl_saldo_kasnet_tipo kt ON kt.id = k.tipo_id
		LEFT JOIN tbl_saldo_kasnet_tipo kt2 ON kt2.id = k.sub_tipo_id
		INNER JOIN tbl_local_cajas lc ON lc.local_id = k.local_id
		INNER JOIN tbl_locales l ON l.id = lc.local_id
		INNER JOIN tbl_local_proveedor_id lp ON (lp.local_id = k.local_id AND servicio_id = 7)
		LEFT JOIN tbl_local_caja_config lcc ON (lcc.local_id = k.local_id AND campo = 'saldo_kasnet')
		LEFT JOIN tbl_zonas z ON z.id = l.zona_id
		$list_where
	")->num_rows;
	$mysqli->query("COMMIT");

	$list = array();
	while ($li = $list_query->fetch_assoc()) $list[] = $li;
	if ($mysqli->error) {
		print_r($mysqli->error);
		die;
	}

	$list_cols = array();
	$list_cols["caja_id"] = "CAJA ID";
	$list_cols["local_id"] = "LOCAL ID";
	$list_cols["cc_id"] = "CENTRO DE COSTO";
	$list_cols["local"] = "LOCAL";
	$list_cols["terminal"] = "TERMINAL";
	$list_cols["zona_nombre"] = "ZONA";
	$list_cols["saldo_final"] = "SALDO ACTUAL";
	$list_cols["kasnet_tipo"] = "TIPO";
	$list_cols["created_at"] = "FECHA";
	$list_cols["opciones"] = "OPCIONES";

	$body = "";

	$body .= '<thead>';
	$body .= '<tr>';
	foreach ($list_cols as $key => $value) {
		if ($key == "caja_id") 		$body .= '<th class="hidden">' . $value . '</th>';
		elseif ($key == "local_id") 	$body .= '<th class="hidden">' . $value . '</th>';
		elseif ($key == "opciones") 	$body .= '<th style="width:150px" class="text-center">OPCIONES</th>';
		elseif ($key == "terminal")	$body .= '<th width="150px">' . $value . '</th>';
		elseif ($key == "zona_nombre")	$body .= '<th width="150px">' . $value . '</th>';
		elseif ($key == "cc_id")		$body .= '<th width="150px">' . $value . '</th>';
		elseif ($key == "saldo_final")	$body .= '<th width="150px">' . $value . '</th>';
		elseif ($key == "kasnet_tipo")	$body .= '<th width="150px">' . $value . '</th>';
		elseif ($key == "created_at")	$body .= '<th width="150px">' . $value . '</th>';
		else 						$body .= '<th>' . $value . '</th>';
	}
	$body .= '</tr>';
	$body .= '</thead>';
	$body .= '<tbody>';

	$saldo_total = 0;
	foreach ($list as $l_k => $l_v) {
		$body .= '<tr>';
		foreach ($list_cols as $key => $value) {
			$max = $l_v["monto_kasnet"] * 1.5 ?: 1500;
			$min = $l_v["monto_kasnet"] * 0.2 ?: 200;
			$medium = $l_v["monto_kasnet"] ?: 1000;
			if ($key == "opciones") {
				// if(array_key_exists(26,$usuario_permisos) && in_array("permissions", $usuario_permisos[26])){
				// }
				$body .= '<td class="text-right">';
				if ($l_v["tipo_id"] == 1) {
					$body .= '<a title="Ver Caja" target="_blank" href="./?sec_id=caja&item_id=' . $l_v["caja_id"] . '" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i></a> ';
				}
				if (array_key_exists(95, $usuario_permisos) && in_array("edit", $usuario_permisos[95]))
					$body .= '<button title="Modificar Saldo" id="btnIncrementSaldo" class="btn btn-xs btn-success"><i class="fa fa-money"></i></button> ';
				$body .= '<button title="Ver Historial" id="btnShowKasnetHistory" class="btn btn-xs btn-warning"><i class="fa fa-list"></i></button>';
				$body .= '</td>';
			} elseif ($key == "caja_id") $body .= '<td class="caja_id hidden">' . $l_v[$key] . '</td>';
			elseif ($key == "local_id") $body .= '<td class="local_id hidden">' . $l_v[$key] . '</td>';
			elseif ($key == "saldo_final") {
				$saldo_total += $l_v[$key];
				$class = "";
				if ($l_v[$key] > $max) $class = "bg-warning text-bold";
				elseif ($l_v[$key] < $min) $class = "bg-danger text-white text-bold";
				else $class = "bg-success text-white text-bold";
				$body .= '<td class="text-right ' . $class . '">' . number_format($l_v[$key], 2, ".", ",") . '</td>';
			} else $body .= '<td class="txt_' . $key . '">' . $l_v[$key] . '</td>';
		}
		$body .= '</tr>';
	}
	$body .= '</tbody>';
	$body .= '<tfoot>';
	$body .= '<tr class="text-right text-bold" style="background-color:#e1e1e1">';
	$body .= '<td></td>';
	$body .= '<td></td>';
	$body .= '<td></td>';
	$body .= '<td>Total:</td>';
	$body .= '<td>' . number_format($saldo_total, 2, '.', ',') . '</td>';
	$body .= '<td></td>';
	$body .= '<td></td>';
	$body .= '<td></td>';
	$body .= '</tr>';
	$body .= '</tfoot>';

	echo json_encode(['body' => $body, 'num_rows' => $num_rows]);
} elseif (isset($_POST["get_tabla_saldo_kasnet_history"])) {
	$data = $_POST["get_tabla_saldo_kasnet_history"];
	$data['offset'] = $data['limit'] * $data['page'];
	$list_where = "WHERE k.estado = 1 AND lcc.estado = 1";
	if ($data["local_id"] != "all") $list_where .= " AND k.local_id = " . $data["local_id"];
	if ($data["tipo"] != "") $list_where .= " AND (k.tipo_id=" . $data['tipo'] . " OR k.sub_tipo_id=" . $data['tipo'] . ")";
	if ($data["start_date"] != "") $list_where .= " AND k.created_at >='" . $data["start_date"] . "'";
	if ($data["end_date"] != "") $list_where .= " AND k.created_at <'" . date('Y-m-d', strtotime('+1 Day', strtotime($data["end_date"]))) . "'";

	$mysqli->query("START TRANSACTION");
	$list_query = $mysqli->query("
		SELECT
			k.id,
			k.caja_id,
			k.local_id,
			l.cc_id,
			l.nombre as local_nombre,
			k.created_at,
			CONCAT('Turno ',c.turno_id) as turno_id,
			(if (k.tipo_id=1,kt.nombre,kt2.nombre)) as kasnet_tipo,
			k.saldo_anterior,
			k.saldo_incremento,
			k.saldo_final,
			CONCAT(IFNULL(p.nombre,''), ' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) as personal_nombre,
			k.tipo_id,
			IFNULL(lcc.valor,0) as monto_kasnet
		FROM tbl_saldo_kasnet k
		INNER JOIN tbl_saldo_kasnet_tipo kt ON kt.id = k.tipo_id
		LEFT JOIN tbl_saldo_kasnet_tipo kt2 ON kt2.id = k.sub_tipo_id
		INNER JOIN tbl_locales l ON l.id = k.local_id
		LEFT JOIN tbl_login lo ON lo.sesion_cookie = k.session_cookie
		LEFT JOIN tbl_usuarios u ON u.id = lo.usuario_id
		LEFT JOIN tbl_personal_apt p ON p.id = u.personal_id
		LEFT JOIN tbl_caja c ON c.id = k.caja_id
		LEFT JOIN tbl_local_caja_config lcc ON (lcc.local_id = k.local_id AND campo = 'saldo_kasnet')
		$list_where
		ORDER BY k.created_at DESC, k.id DESC
		LIMIT {$data['limit']} OFFSET {$data['offset']}");

	$num_rows = $mysqli->query("
			SELECT
				k.id
			FROM tbl_saldo_kasnet k
			INNER JOIN tbl_saldo_kasnet_tipo kt ON kt.id = k.tipo_id
			LEFT JOIN tbl_saldo_kasnet_tipo kt2 ON kt2.id = k.sub_tipo_id
			INNER JOIN tbl_locales l ON l.id = k.local_id
			LEFT JOIN tbl_login lo ON lo.sesion_cookie = k.session_cookie
			LEFT JOIN tbl_usuarios u ON u.id = lo.usuario_id
			LEFT JOIN tbl_personal_apt p ON p.id = u.personal_id
			LEFT JOIN tbl_caja c ON c.id = k.caja_id
			LEFT JOIN tbl_local_caja_config lcc ON (lcc.local_id = k.local_id AND campo = 'saldo_kasnet')
			$list_where")->num_rows;
	$mysqli->query("COMMIT");
	$list = array();
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	if ($mysqli->error) {
		print_r($mysqli->error);
		die;
	}
	$list_cols = array();
	$list_cols["id"] = "ID";
	$list_cols["caja_id"] = "CAJA ID";
	$list_cols["local_id"] = "LOCAL ID";
	$list_cols["cc_id"] = "CC_ID";
	$list_cols["local_nombre"] = "LOCAL";
	$list_cols["created_at"] = "FECHA";
	$list_cols["turno_id"] = "TURNO";
	$list_cols["kasnet_tipo"] = "TIPO";
	$list_cols["saldo_anterior"] = "ANTERIOR";
	$list_cols["saldo_incremento"] = "INCREMENTO";
	$list_cols["saldo_final"] = "FINAL";
	$list_cols["personal_nombre"] = "USUARIO";
	$list_cols["archivos"] = "ARCHIVOS";
	$list_cols["opciones"] = "OPCIONES";

	$body = "";
	$body .= '<div class="pre-scrollable">';
	$body .= '<table class="table table-striped table-hover table-condensed dt-responsive mb-0" cellspacing="0">';
	$body .= '<thead>';
	$body .= '<tr class="bg-secondary">';
	foreach ($list_cols as $key => $value) {

		if ($key == "id") $body .= '<th class="hidden">' . $value . '</th>';
		elseif ($key == "caja_id") $body .= '<th class="hidden">' . $value . '</th>';
		elseif ($key == "local_id") $body .= '<th class="hidden">' . $value . '</th>';
		elseif ($key == "cc_id") $body .= '<th style="width:60px">' . $value . '</th>';
		elseif ($key == "created_at") $body .= '<th style="width:120px">' . $value . '</th>';
		elseif ($key == "turno_id") $body .= '<th style="width:80px">' . $value . '</th>';
		elseif ($key == "kasnet_tipo") $body .= '<th style="width:120px">' . $value . '</th>';
		elseif ($key == "saldo_anterior") $body .= '<th style="width:100px">' . $value . '</th>';
		elseif ($key == "saldo_incremento") $body .= '<th style="width:100px">' . $value . '</th>';
		elseif ($key == "saldo_final") $body .= '<th style="width:100px">' . $value . '</th>';
		elseif ($key == "personal_nombre") $body .= '<th style="width:100px">' . $value . '</th>';
		elseif ($key == "archivos") $body .= '<th style="width:80px">' . $value . '</th>';
		elseif ($key == "opciones") $body .= '<th style="width:80px">' . $value . '</th>';
		elseif ($key == "tipo_id");
		else $body .= '<th>' . $value . '</th>';
	}
	$body .= '</tr>';
	$body .= '</thead>';
	$body .= '</table>';
	$body .= '</div>';
	$body .= '<div class="small pre-scrollable mb-2" style="min-height:350px">';
	$body .= '<table class="table table-striped table-hover-kasnet table-condensed dt-responsive" cellspacing="0">';
	$body .= '<tbody>';
	foreach ($list as $l_k => $l_v) {
		$max = $l_v["monto_kasnet"] * 1.5 ?: 1500;
		$min = $l_v["monto_kasnet"] * 0.2 ?: 200;
		$medium = $l_v["monto_kasnet"] ?: 1000;
		$body .= '<tr>';
		foreach ($list_cols as $key => $value) {
			if ($key == "id") 						$body .= '<td class="kasnet_id hidden">' . $l_v[$key] . '</td>';
			elseif ($key == "caja_id") 			$body .= '<td class="caja_id hidden">' . $l_v[$key] . '</td>';
			elseif ($key == "local_id") 			$body .= '<td class="local_id hidden">' . $l_v[$key] . '</td>';
			elseif ($key == "cc_id")				$body .= '<td width="60px" class="txt_' . $key . '">' . $l_v[$key] . '</td>';
			elseif ($key == "created_at")			$body .= '<td width="120px" class="txt_' . $key . '">' . $l_v[$key] . '</td>';
			elseif ($key == "turno_id")			$body .= '<td width="80px" class="txt_' . $key . '">' . $l_v[$key] . '</td>';
			elseif ($key == "kasnet_tipo")			$body .= '<td width="120px" class="txt_' . $key . '">' . $l_v[$key] . '</td>';
			elseif ($key == "saldo_anterior") 		$body .= '<td width="100px" class="text-right txt_' . $key . '">' . number_format($l_v[$key], 2, ".", ",") . '</td>';
			elseif ($key == "saldo_incremento") 	$body .= '<td width="100px" class="text-right txt_' . $key . '">' . number_format($l_v[$key], 2, ".", ",") . '</td>';
			elseif ($key == "saldo_final") {
				$class = "";
				if ($l_v[$key] > $max) $class = "bg-warning text-bold";
				elseif ($l_v[$key] < $min) $class = "bg-danger text-white text-bold";
				else $class = "bg-success text-white text-bold";
				$body .= '<td width="100px" class="text-right ' . $class . '">' . number_format($l_v[$key], 2, ".", ",") . '</td>';
			} elseif ($key == "personal_nombre")		$body .= '<td width="100px" class="txt_' . $key . '">' . $l_v[$key] . '</td>';
			elseif ($key == "archivos") {
				$body .= '<td width="80px" class="txt_' . $key . '">';

				$archivos = [];
				$result = $mysqli->query(
					"
					SELECT id, tabla, archivo as filepath FROM tbl_archivos
					WHERE
					(tabla = 'tbl_saldo_kasnet' AND item_id = " . $l_v["id"] . ") ||
					(LOWER(archivo) LIKE '%kasnet%' AND item_id = " . ($l_v["caja_id"] ?: 0) . ")"
				);
				while ($r = $result->fetch_assoc()) $archivos[] = $r;
				foreach ($archivos as $archivo) {
					$body .= '
					<a  title="' . $archivo["filepath"] . '"
					target="_blank"
					href="' . ($archivo["tabla"] == "tbl_saldo_kasnet" ? '/files_bucket' : '/files_bucket/cajas/') . $archivo["filepath"] . '">
					<i class="fa fa-file"></i>
					</a>';
					// $body .='<button id="#deleteHistoryFile" class="btn-danger btn-sm"><i class="fa fa-trash"></i></button><br/>';
				}

				$body .= '</td>';
			} elseif ($key == "opciones") {
				// if(array_key_exists(26,$usuario_permisos) && in_array("permissions", $usuario_permisos[26])){
				// }
				$body .= '<td width="80px" class="text-right">';
				$body .= '<form id="formKasnetArchivo" method="POST" enctype="multipart/form-data">';
				$body .= '	<div class="form-group hidden">';
				$body .= '		<input type="file" data-kasnet-id="' . $l_v["id"] . '" id="fileKasnetArchivo" class="fileKasnetArchivo" name ="fileKasnetArchivo">';
				$body .= '	</div>';
				$body .= '</form>';
				$body .= '<button title="Adjuntar Archivo" id="btnKasnetArchivo" class="btn btn-xs btn-secondary"><i class="fa fa-file"></i></button>';
				if ($l_v["tipo_id"] == 1)
					$body .= ' <a title="Ver Caja" target="_blank" href="./?sec_id=caja&item_id=' . $l_v["caja_id"] . '" class="btn btn-xs btn-primary float-right"><i class="fa fa-eye"></i></a>';

				$body .= '</td>';
			} else $body .= '<td class="txt_' . $key . '">' . $l_v[$key] . '</td>';
		}
		$body .= '</tr>';
	}
	$body .= '</tbody>';
	$body .= '</table>';
	$body .= '</div>';

	echo json_encode(['body' => $body, 'num_rows' => $num_rows]);
} elseif (isset($_POST["set_increment"])) {
	$data = $_POST;
	$subtipo = ($data["txtKasnetRecarga"] > 0) ? 5 : 6;

	$saldo_anterior = 0;
	$result = $mysqli->query("
		SELECT
			saldo_final
		FROM tbl_saldo_kasnet
		WHERE
			estado = 1
			AND local_id = " . $data["txtKasnetLocalId"] . "
			AND created_at <= '" . $data["dateRecarga"] . "'
		ORDER BY created_at DESC
		LIMIT 1
	");
	while ($r = $result->fetch_assoc()) $saldo_anterior = $r["saldo_final"];

	$mysqli->query("
		INSERT INTO tbl_saldo_kasnet (
			local_id,
			saldo_anterior,
			saldo_incremento,
			saldo_final,
			session_cookie,
			tipo_id,
			sub_tipo_id,
			sistema,
			created_at,
			updated_at
		)
		VALUES(
			" . $data["txtKasnetLocalId"] . ",
			" . $saldo_anterior . ",
			" . $data["txtKasnetRecarga"] . ",
			" . (float)($saldo_anterior + $data["txtKasnetRecarga"]) . ",
			'" . $login["sesion_cookie"] . "',
			2,
			'" . $subtipo . "',
			" . ($login["area_id"] == 6 ? 1 : 0) . ",
			'" . $data["dateRecarga"] . "',
			'" . $data["dateRecarga"] . "'
		)
	");

	$ultimo = [
		'id' => $mysqli->insert_id,
		'local_id' => $data["txtKasnetLocalId"],
		'incremento' => $data["txtKasnetRecarga"],
		'created' => $data["dateRecarga"]
	];

	//consulta si hay resgistros posteriores a la fecha
	$registros_posteriores = "
		SELECT
			id,
			saldo_incremento,
			tipo_id,
			caja_id,
			created_at
		FROM tbl_saldo_kasnet
		WHERE
			local_id =" . $ultimo["local_id"] . "
			AND created_at > '" . $ultimo["created"] . "'
		ORDER BY created_at ASC
	";
	$result_post = $mysqli->query($registros_posteriores);


	while ($r = $result_post->fetch_assoc()) {
		$post = $r;

		//consulto el registro anterior a la fecha de este registro
		$registro_anterior = "
			SELECT
				saldo_final
			FROM tbl_saldo_kasnet
			WHERE
				local_id=" . $ultimo["local_id"] . "
				AND  estado = 1
				AND created_at < '" . $post["created_at"] . "'
			ORDER BY created_at DESC
			LIMIT 1
		";


		$resul = $mysqli->query($registro_anterior);
		while ($saldo_antPost = $resul->fetch_assoc()) {
			$new_saldo_fin = (float)($saldo_antPost['saldo_final'] + $post["saldo_incremento"]);

			$update_kasnet = "
				UPDATE tbl_saldo_kasnet
				SET
					saldo_anterior=" . $saldo_antPost['saldo_final'] . ",
					saldo_final=" . $new_saldo_fin . "
				WHERE id=" . $post["id"] . "
			";


			$fin = $mysqli->query($update_kasnet);

			if ($post["tipo_id"] == 1) {
				$update_datos_fisicos = "
					UPDATE tbl_caja_datos_fisicos
					SET
						valor=" . $new_saldo_fin . "
					WHERE
						caja_id=" . $post["caja_id"] . "
						AND tipo_id=20
					";

				$fin2 = $mysqli->query($update_datos_fisicos);
			}

			if ($fin) {
				$saldo_antPost['saldo_final'] = "";
			}
			//var_dump($fin);
		}
	}

	if (isset($_FILES['fileKasnetRecarga'])) {
		$path = "";
		$valid_extensions = array('jpeg', 'jpg', 'png', 'gif', 'pdf', 'doc', 'docx', 'csv', 'xls', 'xlsx');

		$file = $_FILES['fileKasnetRecarga']['name'];
		$tmp = $_FILES['fileKasnetRecarga']['tmp_name'];
		$size = $_FILES['fileKasnetRecarga']['size'];
		$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

		$final_file = strtolower($data["txtKasnetLocalId"] . "_" . date('YmdHis') . "." . $ext);
		if (in_array($ext, $valid_extensions)) {
			$path = '/var/www/html/files_bucket/kasnet/saldo/' . $final_file;
			move_uploaded_file($tmp, $path);
			$mysqli->query("
				INSERT INTO tbl_archivos(
					tabla,
					item_id,
					ext,
					size,
					archivo,
					fecha,
					estado
				)
				VALUES(
					'tbl_saldo_kasnet',
					" . $insert_id . ",
					'" . $ext . "',
					" . $size . ",
					'/kasnet/saldo/" . $final_file . "',
					'" . date('Y-m-d H:i:s') . "',
					1
				)
			");
		}
	}
} elseif (isset($_POST["set_file_upload"])) {
	$data = $_POST;

	if (isset($_FILES['fileKasnetArchivo'])) {
		$path = "";
		$valid_extensions = array('jpeg', 'jpg', 'png', 'gif', 'pdf', 'doc', 'docx');

		$file = $_FILES['fileKasnetArchivo']['name'];
		$tmp = $_FILES['fileKasnetArchivo']['tmp_name'];
		$size = $_FILES['fileKasnetArchivo']['size'];
		$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

		$final_file = strtolower($data["local_id"] . "_" . date('YmdHis') . "." . $ext);
		if (in_array($ext, $valid_extensions)) {
			$path = '/var/www/html/files_bucket/kasnet/saldo/' . $final_file;
			move_uploaded_file($tmp, $path);
		}
		$mysqli->query("
		INSERT INTO tbl_archivos(tabla, item_id, ext, size, archivo, fecha, estado)
		VALUES(
			'tbl_saldo_kasnet',
			" . $data["id"] . ",
			'" . $ext . "',
			" . $size . ",
			'/kasnet/saldo/" . $final_file . "',
			'" . date('Y-m-d H:i:s') . "',
			1
			)
			");
	}
} elseif (isset($_POST["get_data_recarga_by_file"])) {
	$dataReturn = [];
	$file = $_FILES;
	$ext = pathinfo($file['file']['name'], PATHINFO_EXTENSION);
	$extract = array();
	if ($ext == "xls" || $ext == "xlsx") {
		$return = array();
		require_once '../phpexcel/classes/PHPExcel.php';
		$tmpfname = $_FILES['file']['tmp_name'];
		$excelReader = PHPExcel_IOFactory::createReaderForFile($tmpfname);
		libxml_use_internal_errors(TRUE);
		$excelObj = $excelReader->load($tmpfname);
		$worksheet = $excelObj->getSheet(0);
		$lastRow = $worksheet->getHighestRow();
		$firstRow = 1;



		for ($row = $firstRow + 1; $row <= $lastRow; $row++) {
			if ($worksheet->getCell('A' . $row)->getValue() != "") {

				$extract[] = [

					"cc_id" => str_replace("'", "", $worksheet->getCell('A' . $row)->getValue()),
					"monto" => str_replace("'", "", $worksheet->getCell('B' . $row)->getValue()),
					"terminal" => str_replace("'", "", $worksheet->getCell('C' . $row)->getValue()),

				];
			}
		}
	} else {

		$dataReturn['message'] = 'Formato no permitido';
		$dataReturn['error'] = true;
		echo json_encode($dataReturn);
		exit();
	}

	$query = "
	SELECT tlpi.`local_id`,
		tl.`cc_id`,
		tlpi.`proveedor_id`,
		tl.`nombre`
	FROM   `tbl_local_proveedor_id`  AS tlpi
		LEFT JOIN `tbl_locales`   AS tl
				ON  tl.`id` = tlpi.`local_id`
	WHERE  tlpi.`servicio_id` = 7
		AND tlpi.`canal_de_venta_id` = 28
		AND tlpi.`estado` = 1
	";
	$list_query_result = $mysqli->query($query);
	while ($li = $list_query_result->fetch_assoc()) {
		$locales[$li['cc_id']] = $li;
	}
	$dataReturn = [];
	$dataTmp = [];
	foreach ($extract as $key => $value) {
		$error = true;
		$arrayTmp = [];
		if (isset($locales[$value['cc_id']])) {

			$arrayTmp['cc'] = $value['cc_id'];
			$arrayTmp['error_cc'] = false;
			if ($locales[$value['cc_id']]['proveedor_id'] == $value['terminal']) {
				$arrayTmp['proveedor_id'] = $value['terminal'];
				$arrayTmp['error_proveedor_id'] = false;
				$arrayTmp['nombre'] = $locales[$value['cc_id']]['nombre'];
				$arrayTmp['monto'] = $value['monto'];
				$arrayTmp['local_id'] = $locales[$value['cc_id']]['local_id'];
			} else {
				$arrayTmp['proveedor_id'] = $value['terminal'];
				$arrayTmp['error_proveedor_id'] = true;
				$arrayTmp['nombre'] = $value['terminal'] . ' no pertenece o no esta configurado en ' . $locales[$value['cc_id']]['nombre'];
				$arrayTmp['monto'] = $value['monto'];
				$arrayTmp['local_id'] = $locales[$value['cc_id']]['local_id'];
			}
		} else {
			$arrayTmp['cc'] = $value['cc_id'];
			$arrayTmp['error_cc'] = true;
			$arrayTmp['proveedor_id'] = $value['terminal'];
			$arrayTmp['error_proveedor_id'] = true;
			$arrayTmp['nombre'] = 'CC_ID de Local no identificaco Para locales con proveedor kasnet';
			$arrayTmp['monto'] = $value['monto'];
			$arrayTmp['local_id'] = '';
		}
		$dataTmp[] = $arrayTmp;
	}

	$dataReturn['data'] = $dataTmp;
	$dataReturn['msj'] = 'Archivo analizado correctamente';
	echo json_encode($dataReturn);
} elseif (isset($_POST["save_data_recarga_by_file_kasnet"])) {

	$save_data = $_POST['data_save'];
	$date = $_POST['date'];
	$time = $_POST['time'];
	$arrayData = json_decode($save_data);
	if ($date == '' || $time == '' || $save_data == '' || count($arrayData) == 0) {
		$dataReturn['error'] = true;
		$dataReturn['message'] = 'Todos los datos son requeridos';
		echo json_encode($dataReturn);
		exit();
	}
	$arrayData = json_decode($save_data);
	$dateRecarga = $date . ' ' . $time;



	$path = '/var/www/html/files_bucket/kasnet/recargas-masivas/';
	$recarga_masiva_id = 0;
	if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
		if (!is_dir($path)) mkdir($path, 0777, true);
		$filename = $_FILES['file']['name'];
		$filenametem = $_FILES['file']['tmp_name'];
		$filesize = $_FILES['file']['size'];
		$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
		if ($filename != "") {
			$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
			$nombre_archivo =  pathinfo($filename, PATHINFO_FILENAME);
			$nombre_archivo = $nombre_archivo . ' ' . date('YmdHis') . "." . $fileExt;
			$ruta = $path . $nombre_archivo;
			move_uploaded_file($filenametem, $ruta);
			$comando = "INSERT INTO tbl_saldo_kasnet_historico_recarga_masiva (
							nombre,
							extension,
							size,
							ruta,
							state,
							session_cookie,
							user_created_id,
							created_at)
						VALUES(
	
							'" . $nombre_archivo . "',
							'" . $fileExt . "',
							'" . $filesize . "',
							'" . $ruta . "',
							1,
							'" . $login["sesion_cookie"] . "',
							" . $login['id'] . ",
							'" . date('Y-m-d H:i:s') . "'
							)";
			$mysqli->query($comando);
			if ($mysqli->error) {
				$dataReturn['error'] = true;
				$dataReturn['message'] = 'A ocurrido al subir el archivo de la carga histórica.';
				$result['result'] = $comando;
				echo json_encode($dataReturn);
				exit();
			}
			$recarga_masiva_id = mysqli_insert_id($mysqli);
		}
	}

	foreach ($arrayData as $key => $value) {

		$data["txtKasnetRecarga"] = $value->monto;
		$data["txtKasnetLocalId"] = $value->local_id;
		$data["dateRecarga"] = $dateRecarga;
		$subtipo = ($data["txtKasnetRecarga"] > 0) ? 5 : 6;

		$saldo_anterior = 0;
		$result = $mysqli->query("
		SELECT
			saldo_final
		FROM tbl_saldo_kasnet
		WHERE
			estado = 1
			AND local_id = " . $data["txtKasnetLocalId"] . "
			AND created_at <= '" . $data["dateRecarga"] . "'
		ORDER BY created_at DESC
		LIMIT 1
		");
		while ($r = $result->fetch_assoc()) $saldo_anterior = $r["saldo_final"];

		$mysqli->query("
		INSERT INTO tbl_saldo_kasnet (
			local_id,
			saldo_anterior,
			saldo_incremento,
			saldo_final,
			session_cookie,
			tipo_id,
			sub_tipo_id,
			sistema,
			recarga_masiva_id,
			created_at,
			updated_at
		)
		VALUES(
			" . $data["txtKasnetLocalId"] . ",
			" . $saldo_anterior . ",
			" . $data["txtKasnetRecarga"] . ",
			" . (float)($saldo_anterior + $data["txtKasnetRecarga"]) . ",
			'" . $login["sesion_cookie"] . "',
			2,
			'" . $subtipo . "',
			" . ($login["area_id"] == 6 ? 1 : 0) . ",
			" . $recarga_masiva_id . ",
			'" . $data["dateRecarga"] . "',
			'" . $data["dateRecarga"] . "'
		)
		");

		$ultimo = [
			'id' => $mysqli->insert_id,
			'local_id' => $data["txtKasnetLocalId"],
			'incremento' => $data["txtKasnetRecarga"],
			'created' => $data["dateRecarga"]
		];

		//consulta si hay resgistros posteriores a la fecha
		$registros_posteriores = "
		SELECT
			id,
			saldo_incremento,
			tipo_id,
			caja_id,
			created_at
		FROM tbl_saldo_kasnet
		WHERE
			local_id =" . $ultimo["local_id"] . "
			AND created_at > '" . $ultimo["created"] . "'
		ORDER BY created_at ASC
		";
		$result_post = $mysqli->query($registros_posteriores);


		while ($r = $result_post->fetch_assoc()) {
			$post = $r;

			//consulto el registro anterior a la fecha de este registro
			$registro_anterior = "
			SELECT
				saldo_final
			FROM tbl_saldo_kasnet
			WHERE
				local_id=" . $ultimo["local_id"] . "
				AND  estado = 1
				AND created_at < '" . $post["created_at"] . "'
			ORDER BY created_at DESC
			LIMIT 1
			";


			$resul = $mysqli->query($registro_anterior);
			while ($saldo_antPost = $resul->fetch_assoc()) {
				$new_saldo_fin = (float)($saldo_antPost['saldo_final'] + $post["saldo_incremento"]);

				$update_kasnet = "
				UPDATE tbl_saldo_kasnet
				SET
					saldo_anterior=" . $saldo_antPost['saldo_final'] . ",
					saldo_final=" . $new_saldo_fin . "
				WHERE id=" . $post["id"] . "
				";


				$fin = $mysqli->query($update_kasnet);

				if ($post["tipo_id"] == 1) {
					$update_datos_fisicos = "
					UPDATE tbl_caja_datos_fisicos
					SET
						valor=" . $new_saldo_fin . "
					WHERE
						caja_id=" . $post["caja_id"] . "
						AND tipo_id=20
					";

					$fin2 = $mysqli->query($update_datos_fisicos);
				}

				if ($fin) {
					$saldo_antPost['saldo_final'] = "";
				}
				//var_dump($fin);
			}
		}
	}
	$dataReturn['error'] = false;
	$dataReturn['message'] = 'Datos Insertado Correctamente';
	echo json_encode($dataReturn);
} elseif (isset($_POST["get_data_historica_recarga_masiva"])) {

	$query = "SELECT 
				h.id,
				h.nombre,
				h.extension,
				h.size,
				h.ruta,
				h.session_cookie,
				h.state,
				h.created_at,
				CONCAT(IFNULL(p.nombre, ''),' ',IFNULL(p.apellido_paterno, ''),' ',IFNULL(p.apellido_materno, '')) AS usuario
				FROM tbl_saldo_kasnet_historico_recarga_masiva AS h
				INNER JOIN tbl_usuarios u ON h.user_created_id = u.id
				INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
				ORDER BY h.created_at DESC";

	$list_query = $mysqli->query($query);
	$list = array();
	while ($li = $list_query->fetch_assoc()) {

		switch ($li['state']) {
			case '1':
				$classbtn = "success";
				$toggle = "fa-toggle-on";
				$title = "Inactivar";
				break;
			case '0':
				# code...
				$toggle = "fa-toggle-off";
				$title = "Activar";
				$classbtn = "danger";
				break;
			default:
				$classbtn = "info";
				break;
		}
		$li['ruta'] = str_replace("/var/www/html", "", $li["ruta"]);
		$li['archivo'] = '<a title="Descargar Archivo" target="_blank" href="./' . $li['ruta'] . '" class="btn btn-xs btn-primary"><i class="fa fa-download"></i></a>
								<button type="button" title="' . $title . ' recarga" class="btn btn-xs text-' . $classbtn .
			' btn_change_estado_recarga_masiva" data-id-recarga="' . $li['id'] .
			'"  data-state-recarga="' . $li['state'] . '" style="padding: 0; font-size: 23px; background-color: transparent;"><i class="fa ' . $toggle . '"></i></button>
						';
		$list[] = $li;
	}

	$result['status'] = 200;
	$result['message'] = "Datos obtenidos de gestión";
	$result['result'] = $list;
	echo json_encode($result);
	exit();
} elseif (isset($_POST["set_state_recarga_masiva"])) {

	$recarga_masiva_id = $_POST["recarga_masiva_id"];
	$new_state = $_POST["new_state"];

	$result = [];

	$query = "  UPDATE tbl_saldo_kasnet_historico_recarga_masiva h
				SET 
					h.state = $new_state,
					h.user_updated_id = " . $login['id'] . ",
					h.updated_at = now()
				where h.id = $recarga_masiva_id;
				";

	$update_historico = $mysqli->query($query);


	if ($update_historico) {

		$query = "  UPDATE tbl_saldo_kasnet sk
					SET
						sk.estado = $new_state
					where
						sk.recarga_masiva_id =  $recarga_masiva_id;
				";

		$update_saldos = $mysqli->query($query);
	}


	if ($update_saldos) {
		$query = "SELECT DISTINCT(local_id), created_at from tbl_saldo_kasnet where recarga_masiva_id = " . $recarga_masiva_id;
		$local_distinct = $mysqli->query($query);
		$locales = [];
		$fecha = '';
		while ($r = $local_distinct->fetch_assoc()) {
			$locales[] = $r['local_id'];
			$fecha = $r['created_at'];
		}
		$count_locales = count($locales);

		$locales = implode(',', $locales);
		$fecha_inicio = date('Y-m-d', strtotime($fecha . " -1 day"));


		$uri = "/cron/kasnet/fix_increment.php?locales=" . $locales . "&from_date=" . $fecha_inicio;

		// $url_fix_increment = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://192.168.18.3" . $uri; //local
		$url_fix_increment = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]" . $uri;
		//$url_fix_increment = "https://gestion.apuestatotal.com" . $uri;

		//$_SERVER[REQUEST_URI]";

		$ch = curl_init($url_fix_increment);

		// Execute
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response_fix_increment = curl_exec($ch);

		// Check if any error occurred
		if (curl_errno($ch)) {
			$error = 'Curl error: ' . curl_error($ch); 
			$result['status'] = 500;
			$result['message'] = $error;
		}

		// Close handle
		curl_close($ch);

		if($new_state){
			$result_state = 'activó';
		} else {
			$result_state = 'desactivó';
		}

		if ($response_fix_increment == "\n") {
			$result['status'] = 200;
			$result['message'] = "Se $result_state correctamente la recarga y el fix para $count_locales locales.";
		}
	} else {
		$result['status'] = 500;
		$result['message'] = "Ocurrió un error";
	}

	echo json_encode($result);
	exit();
} else {
	echo "<pre>";
	var_dump($_POST, $_FILES);
	echo "</pre>";
	die;
}
