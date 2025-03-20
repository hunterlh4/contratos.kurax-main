<?php
include("db_connect.php");
include("sys_login.php");

if (isset($_POST['accion'])) {
	$inputs = json_decode(json_encode($_POST));
	switch ($_POST['accion']) {
		case 'listar_saldo_disashop':
			$data_result 				= fnc_list_balance_disashop($inputs);
			$data_result_total_record 	= fnc_total_record_balance_disashop($inputs);
			$data_result_sum_record		= fnc_total_record_sum_disashop($inputs);

			$result["draw"] = (isset($_POST["draw"])) ? intval($_POST["draw"]) : 0;
			$result["recordsTotal"] = (int)$data_result_total_record;
			$result["recordsFiltered"] = (int)$data_result_total_record;
			$result["http_code"] = 200;
			$result["status"] = "ok";
			$result["sum_records"] = $data_result_sum_record;
			$result["data"] = $data_result;
			echo json_encode($result);
			break;
		case 'listar_zonas_tipos':
			$data_result_types 				= fnc_list_types();
			$data_result_zones 				= fnc_list_zones();
			$result["http_code"] = 200;
			$result["status"] = "ok";
			$result["data"] = [
				'types' => $data_result_types,
				'zones' => $data_result_zones
			];
			echo json_encode($result);
			break;
		case 'listar_saldo_disashop_history':
			$data_result 				= fnc_list_balance_disashop_history($inputs);
			$data_result_total_record 	= fnc_total_record_balance_disashop_history($inputs);

			$result["draw"] = (isset($_POST["draw"])) ? intval($_POST["draw"]) : 0;
			$result["recordsTotal"] = (int)$data_result_total_record;
			$result["recordsFiltered"] = (int)$data_result_total_record;
			$result["http_code"] = 200;
			$result["status"] = "ok";
			$result["data"] = $data_result;
			echo json_encode($result);
			break;
		case 'saveFileDisashop':
			$data_result 				= fnc_save_file_disashop_history($inputs, $_FILES);
			if ($data_result) {
				$result["error"] = false;
				$result["http_code"] = 200;
				$result["status"] = "ok";
				$result["message"] = "Archivo subido correctamente";
				$result["data"] = $data_result;
				echo json_encode($result, JSON_UNESCAPED_UNICODE);
			} else {
				$result["true"] = false;
				$result["http_code"] = 500;
			}
			break;
		case 'increment_disashop':
			$data_result 			= fnc_increment_balance($inputs, $_FILES);
			if ($data_result) {
				$result["error"] = false;
				$result["http_code"] = 200;
				$result["status"] = "ok";
				$result["message"] = "Archivo subido correctamente";
				$result["data"] = $data_result;
				echo json_encode($result, JSON_UNESCAPED_UNICODE);
			} else {
				$result["true"] = false;
				$result["http_code"] = 500;
			}
			break;
		case 'export_reporte_disashop':
			$data_result = disashop_report_exxport($inputs);
			echo json_encode($data_result);
			break;
		case 'export_reporte_disashop_history':
			$data_result = disashop_report_history_exxport($inputs);
			echo json_encode($data_result);
			break;
		case 'get_data_recarga_by_file':
			$data_result = fnc_recarga_masiva_read_file($inputs, $_FILES);
			echo json_encode($data_result);
			break;
		case 'save_data_recarga_by_file_disashop':
			$data_result = fnc_save_data_carga_masiva($inputs, $_FILES);
			echo json_encode($data_result);
			break;
		case 'get_data_historica_recarga_masiva':
			$data_result = get_data_historica_recarga_masiva();
			echo json_encode($data_result);
			break;
		default:
			# code...
			break;
	}
}
function fnc_save_data_carga_masiva($inputs, $files = false)
{
	global $mysqli;
	global $login;
	$save_data = $inputs->data_save;
	$date = $inputs->date;
	$time = $inputs->time;
	$arrayData = json_decode($save_data);
	if ($date == '' || $time == '' || $save_data == '' || count($arrayData) == 0) {
		$dataReturn['error'] = true;
		$dataReturn['message'] = 'Todos los datos son requeridos';
		echo json_encode($dataReturn);
		exit();
	}
	$arrayData = json_decode($save_data);
	$dateRecarga = $date . ' ' . $time;


	$path = '/var/www/html/files_bucket/disashop/recargas-masivas/';
	$recarga_masiva_id = 0;
	if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
		if (!is_dir($path)) mkdir($path, 0777, true);
		$filename = $_FILES['file']['name'];
		$filenametem = $_FILES['file']['tmp_name'];
		$filesize = $_FILES['file']['size'];
		$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
		if($filename != ""){
			$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
			$nombre_archivo =  pathinfo($filename, PATHINFO_FILENAME);
			$nombre_archivo = $nombre_archivo.' '. date('YmdHis') . "." . $fileExt;
			$ruta = $path . $nombre_archivo;
			move_uploaded_file($filenametem, $ruta);
			$comando = "INSERT INTO tbl_saldo_disashop_historico_recarga_masiva (
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
			if($mysqli->error){
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

		$data["txtDisashopRecarga"] = $value->monto;
		$data["txtDisashopLocalId"] = $value->local_id;
		$data["dateRecarga"] = $dateRecarga;
		$subtipo = ($data["txtDisashopRecarga"] > 0) ? 5 : 6;

		$saldo_anterior = 0;
		$result = $mysqli->query("
		SELECT
			saldo_final
		FROM tbl_saldo_disashop
		WHERE
			estado = 1
			AND local_id = " . $data["txtDisashopLocalId"] . "
			AND created_at <= '" . $data["dateRecarga"] . "'
		ORDER BY created_at DESC
		LIMIT 1
		");
		while ($r = $result->fetch_assoc()) $saldo_anterior = $r["saldo_final"];

		$mysqli->query("
		INSERT INTO tbl_saldo_disashop(
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
			" . $data["txtDisashopLocalId"] . ",
			" . $saldo_anterior . ",
			" . $data["txtDisashopRecarga"] . ",
			" . (float)($saldo_anterior + $data["txtDisashopRecarga"]) . ",
			'" . $login["sesion_cookie"] . "',
			2,
			'" . $subtipo . "',
			" . ($login["area_id"] == 6 ? 1 : 0) . ",
			".$recarga_masiva_id.",
			'" . $data["dateRecarga"] . "',
			'" . $data["dateRecarga"] . "'
		)
		");

		$ultimo = [
			'id' => $mysqli->insert_id,
			'local_id' => $data["txtDisashopLocalId"],
			'incremento' => $data["txtDisashopRecarga"],
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
		FROM tbl_saldo_disashop
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
			FROM tbl_saldo_disashop
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

				$update_disashop = "
				UPDATE tbl_saldo_disashop
				SET
					saldo_anterior=" . $saldo_antPost['saldo_final'] . ",
					saldo_final=" . $new_saldo_fin . "
				WHERE id=" . $post["id"] . "
				";


				$fin = $mysqli->query($update_disashop);

				if ($post["tipo_id"] == 1) {
					$update_datos_fisicos = "
					UPDATE tbl_caja_datos_fisicos
					SET
						valor=" . $new_saldo_fin . "
					WHERE
						caja_id=" . $post["caja_id"] . "
						AND tipo_id=25
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
	return($dataReturn);
}
function fnc_recarga_masiva_read_file($inputs, $file)
{
	global $mysqli;
	$dataReturn = [];

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
	WHERE  tlpi.`servicio_id` = 14
		AND tlpi.`canal_de_venta_id` = 35
		AND tlpi.`estado` = 1
	";
	$list_query_result = $mysqli->query($query);
	$localesProveddor = [];
	while ($li = $list_query_result->fetch_assoc()) {
		$locales[$li['cc_id']][] = $li;
		$localesProveddor[$li['cc_id']][] =$li["proveedor_id"] ;
	}
	$dataReturn = [];
	$dataTmp = [];
	foreach ($extract as $key => $value) {
		$error = true;
		$arrayTmp = [];
		if (isset($locales[$value['cc_id']])) {
			$arrayTmp['cc'] = $value['cc_id'];
			$arrayTmp['error_cc'] = false;
			if (in_array($value['terminal'], $localesProveddor[$value['cc_id']])) {
				$clave = array_search($value['terminal'],$localesProveddor[$value['cc_id']]);
				$arrayTmp['proveedor_id'] = $value['terminal'];
				$arrayTmp['error_proveedor_id'] = false;
				$arrayTmp['nombre'] = $locales[$value['cc_id']][$clave]['nombre'];
				$arrayTmp['monto'] = $value['monto'];
				$arrayTmp['local_id'] = $locales[$value['cc_id']][$clave]['local_id'];
			} else {
				$arrayTmp['proveedor_id'] = $value['terminal'];
				$arrayTmp['error_proveedor_id'] = true;
				$arrayTmp['nombre'] = $value['terminal'] . ' no pertenece o no esta configurado en ' . $locales[$value['cc_id']][$clave]['nombre'];
				$arrayTmp['monto'] = $value['monto'];
				$arrayTmp['local_id'] = $locales[$value['cc_id']][$clave]['local_id'];
			}
		} else {
			$arrayTmp['cc'] = $value['cc_id'];
			$arrayTmp['error_cc'] = true;
			$arrayTmp['proveedor_id'] = $value['terminal'];
			$arrayTmp['error_proveedor_id'] = true;
			$arrayTmp['nombre'] = 'CC_ID de Local no identificaco Para locales con proveedor Disadhop';
			$arrayTmp['monto'] = $value['monto'];
			$arrayTmp['local_id'] = '';
		}
		$dataTmp[] = $arrayTmp;
	}

	$dataReturn['data'] = $dataTmp;
	$dataReturn['msj'] = 'Archivo analizado correctamente';
	return ($dataReturn);
}


function fnc_increment_balance($inputs, $files)
{
	global $mysqli;
	global $login;
	$subtype = ($inputs->monto > 0) ? 5 : 6;
	if ($inputs->fecha == '') {
		$inputs->fecha = '00:00:00';
	}
	$previous_balance	= 0;
	$inputs->fecha = $inputs->fecha . ' ' . $inputs->hora;
	$query_previous_balance = "
	SELECT
			saldo_final
		FROM tbl_saldo_disashop
		WHERE
			estado = 1
			AND local_id = {$inputs->idLocal}
			AND created_at <= '{$inputs->fecha}'
		ORDER BY created_at DESC
		LIMIT 1
	";
	$result = $mysqli->query($query_previous_balance);
	while ($r = $result->fetch_assoc()) $previous_balance = $r["saldo_final"];

	$query_insert_balance = "
	INSERT INTO tbl_saldo_disashop (
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
		" . $inputs->idLocal . ",
		" . $previous_balance . ",
		" . $inputs->monto . ",
		" . (float)($previous_balance + $inputs->monto) . ",
		'" . $login["sesion_cookie"] . "',
		2,
		'" . $subtype . "',
		" . ($login["area_id"] == 6 ? 1 : 0) . ",
		'" . $inputs->fecha . "',
		'" . $inputs->fecha . "'
	)
	";
	$state = $mysqli->query($query_insert_balance);
	$ultimo = [
		'id' => $mysqli->insert_id,
		'local_id' => $inputs->idLocal,
		'incremento' => $inputs->monto,
		'created' => $inputs->fecha
	];

	//consulta si hay resgistros posteriores a la fecha
	$registros_posteriores = "
		SELECT
			id,
			saldo_incremento,
			tipo_id,
			caja_id,
			created_at
		FROM tbl_saldo_disashop
		WHERE
			local_id =" . $ultimo["local_id"] . "
			AND created_at > '" . $ultimo["created"] . "'
		ORDER BY created_at ASC
	";
	$result_post = $mysqli->query($registros_posteriores);
	fnc_save_file_disashop_history_per_local($ultimo['id'], $files);
	while ($r = $result_post->fetch_assoc()) {
		$post = $r;

		//consulto el registro anterior a la fecha de este registro
		$registro_anterior = "
			SELECT
				saldo_final
			FROM tbl_saldo_disashop
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

			$update_disashop = "
				UPDATE tbl_saldo_disashop
				SET
					saldo_anterior=" . $saldo_antPost['saldo_final'] . ",
					saldo_final=" . $new_saldo_fin . "
				WHERE id=" . $post["id"] . "
			";


			$fin = $mysqli->query($update_disashop);

			if ($post["tipo_id"] == 1) {
				$update_datos_fisicos = "
					UPDATE tbl_caja_datos_fisicos
					SET
						valor=" . $new_saldo_fin . "
					WHERE
						caja_id=" . $post["caja_id"] . "
						AND tipo_id=25
					";

				$fin2 = $mysqli->query($update_datos_fisicos);
			}

			if ($fin) {
				$saldo_antPost['saldo_final'] = "";
			}
			//var_dump($fin);
		}
	}
	return true;
}
function fnc_save_file_disashop_history_per_local($id, $files)
{
	global $mysqli;
	$return_data = array();
	if (isset($files['filesDisashopRecarga']["name"])) {
		for ($i = 0; $i < count($files['filesDisashopRecarga']["name"]); $i++) {
			$file = $files['filesDisashopRecarga']['name'][$i];
			$tmp = $files['filesDisashopRecarga']['tmp_name'][$i];
			$size = $files['filesDisashopRecarga']['size'][$i];
			$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

			$valid_extensions = array('jpeg', 'jpg', 'png', 'pdf', 'doc', 'docx');
			$final_file = strtolower($id . "_" . date('YmdHis') . "." . $ext);
			if ($size <= 10485760) {
				if (in_array($ext, $valid_extensions)) {
					$path = '/var/www/html/files_bucket/disashop/saldo/' . $final_file;
					$path_exist = "/var/www/html/files_bucket/disashop/saldo/";
					if (!is_dir($path_exist)) {
						mkdir($path_exist, 0777, true);
					}
					move_uploaded_file($tmp, $path);
					$queryInsertArchivos = "
					INSERT INTO tbl_archivos(tabla, item_id, ext, size, archivo, fecha, estado)
					VALUES(
						'tbl_saldo_disashop',
						{$id},
						'{$ext}',
						{$size},
						'disashop/saldo/{$final_file}',
						'" . date('Y-m-d H:i:s') . "',
						1
						)
					";
					$mysqli->query($queryInsertArchivos);
					if ($mysqli->connect_errno) {
						return false;
					}
					return true;
				}
			} else {
				return false;
			}
		}
	}


	return $return_data;
}
function fnc_save_file_disashop_history($inputs, $files)
{
	global $mysqli;
	$return_data = array();
	for ($i = 0; $i < count($files['filesDisashop']["name"]); $i++) {
		$file = $files['filesDisashop']['name'][$i];
		$tmp = $files['filesDisashop']['tmp_name'][$i];
		$size = $files['filesDisashop']['size'][$i];
		$ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

		$valid_extensions = array('jpeg', 'jpg', 'png', 'pdf', 'doc', 'docx');
		$final_file = strtolower($inputs->id . "_" . date('YmdHis') . "." . $ext);
		if ($size <= 10485760) {
			if (in_array($ext, $valid_extensions)) {
				$path = '/var/www/html/files_bucket/disashop/saldo/' . $final_file;
				$path_exist = "/var/www/html/files_bucket/disashop/saldo/";
				if (!is_dir($path_exist)) {
					mkdir($path_exist, 0777, true);
				}
				move_uploaded_file($tmp, $path);
				$queryInsertArchivos = "
				INSERT INTO tbl_archivos(tabla, item_id, ext, size, archivo, fecha, estado)
				VALUES(
					'tbl_saldo_disashop',
					{$inputs->id},
					'{$ext}',
					{$size},
					'disashop/saldo/{$final_file}',
					'" . date('Y-m-d H:i:s') . "',
					1
					)
				";
				$mysqli->query($queryInsertArchivos);
				if ($mysqli->connect_errno) {
					return false;
				}
				$inputs->path = "disashop/saldo/{$final_file}";
				array_push($return_data, $inputs);
			}
		} else {
			return false;
		}
	}

	return $return_data;
}
function fnc_list_balance_disashop($inputs)
{
	global $mysqli;
	$limit = '';
	if (isset($inputs->length)) {
		if ($inputs->length != -1) {
			$limit = 'LIMIT ' . $inputs->start . ', ' . $inputs->length;
		}
	}
	$where_zona = "";
	//------------------------------------------------------------------------------------
	$where_tipo = "";
	$like_local = "";
	if (isset($inputs->search)) {
		$search = $inputs->search;
		if ($search->value != '') {
			$tmp_search_filter = $search->value;
			$search_filter = json_decode($tmp_search_filter);

			if (isset($search_filter->zona) && (int) $search_filter->zona > 0) {
				$where_zona = " AND tz.id = " . $search_filter->zona;
			}
			if (isset($search_filter->tipo) && (int) $search_filter->tipo > 0) {
				$where_tipo = " AND u1.tipo_id = " . $search_filter->tipo;
			}
			if (isset($search_filter->local) && $search_filter->local != '') {
				$like_local = "AND (
					tl.nombre LIKE '%{$search_filter->local}%'
					OR tl.cc_id  LIKE '%{$search_filter->local}%'
					 )";
			}
		}
	}
	//--------------------------------------
	$query = "		
	SELECT
		u1.id,
		u1.caja_id,
		u1.local_id,
		tl.cc_id,
		tl.nombre AS nombre_local,
		tl.zona_id,
		tz.nombre AS nombre_zona,
		tlp.proveedor_id AS terminal,
		tsdt.nombre AS saldo_tipo,
		u1.saldo_anterior,
		u1.saldo_incremento,
		u1.saldo_final,
		u1.tipo_id,
		u1.sub_tipo_id,
		u1.sistema,
		u1.created_at,
		u1.updated_at,
		COALESCE(lcc.valor, 0)               AS monto_disashop,
		u1.estado
		FROM
		(SELECT MAX(created_at) AS max_created_at, local_id FROM tbl_saldo_disashop WHERE estado=1 GROUP BY local_id) AS max_u1
		JOIN tbl_saldo_disashop AS u1 ON u1.local_id = max_u1.local_id AND u1.created_at = max_u1.max_created_at
		LEFT JOIN tbl_locales AS tl ON tl.id = u1.local_id
		LEFT JOIN tbl_zonas AS tz ON tz.id = tl.zona_id
		LEFT JOIN tbl_local_proveedor_id AS tlp ON tlp.local_id = u1.local_id AND tlp.servicio_id = 14 AND tlp.estado = 1 
		LEFT JOIN tbl_saldo_disashop_tipo AS tsdt ON tsdt.id = u1.tipo_id
		LEFT JOIN tbl_local_caja_config AS lcc ON lcc.local_id = u1.local_id AND lcc.campo = 'saldo_disashop' AND lcc.estado = 1
		WHERE u1.estado=1 AND tlp.habilitado = 1 AND
		EXISTS (SELECT 1 FROM tbl_local_proveedor_id WHERE local_id = u1.local_id AND servicio_id = 14 AND estado = 1)
		AND EXISTS (SELECT 1 FROM tbl_local_caja_config WHERE local_id = u1.local_id AND campo = 'saldo_disashop' AND estado = 1)
		{$where_zona}
		{$where_tipo}
		{$like_local}
		{$limit}
	   ";
	   
	//---------------------------------
	/*
	$query = "		
	SELECT u1.id,
		u1.caja_id,
		u1.local_id,
		tl.cc_id ,
		tl.nombre                          AS nombre_local,
		tl.zona_id,
		tz.nombre                          AS nombre_zona,
		tlp.proveedor_id                   AS terminal,
		tsdt.nombre                        AS saldo_tipo,
		u1.saldo_anterior,
		u1.saldo_incremento,
		u1.saldo_final,
		u1.tipo_id,
		u1.sub_tipo_id,
		u1.sistema,
		u1.created_at,
		u1.updated_at,
		IFNULL(lcc.valor,0) as monto_disashop,
		u1.estado
	FROM   tbl_saldo_disashop                 AS u1
		LEFT JOIN tbl_saldo_disashop       AS u2
				ON  u1.local_id = u2.local_id 
				AND u1.created_at < u2.created_at
		LEFT JOIN tbl_locales              AS tl
				ON  tl.id = u1.local_id
		LEFT JOIN tbl_local_proveedor_id   AS tlp
				ON ( tlp.local_id = u1.local_id AND tlp.servicio_id = 14)
		LEFT JOIN tbl_zonas                AS tz
				ON  tz.id = tl.zona_id
		LEFT JOIN tbl_saldo_disashop_tipo  AS tsdt
				ON  tsdt.id = u1.tipo_id
		LEFT JOIN tbl_local_caja_config lcc ON (lcc.local_id = u1.local_id AND campo = 'saldo_disashop' and lcc.estado = 1)
	WHERE  u2.created_at IS NULL
		-- AND lcc.estado = 1
		AND u1.created_at IS NOT NULL
		AND tlp.estado = 1
		AND u1.estado = 1
		{$where_zona}
		{$where_tipo}
		{$like_local}
		{$limit}
	   ";
	   */
	   //----------------------------------
	$list_query = $mysqli->query($query);
	$list_transaccion = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_transaccion[] = $li;
	}

	return $list_transaccion;
}
function fnc_total_record_sum_disashop($inputs)
{
	global $mysqli;
	$where_zona = "";
	$where_tipo = "";
	$like_local = "";
	if (isset($inputs->search)) {
		$search = $inputs->search;
		if ($search->value != '') {
			$tmp_search_filter = $search->value;
			$search_filter = json_decode($tmp_search_filter);

			if (isset($search_filter->zona) && (int) $search_filter->zona > 0) {
				$where_zona = " AND tz.id = " . $search_filter->zona;
			}
			if (isset($search_filter->tipo) && (int) $search_filter->tipo > 0) {
				$where_tipo = " AND u1.tipo_id = " . $search_filter->tipo;
			}
			if (isset($search_filter->local) && $search_filter->local != '') {
				$like_local = "AND (
					tl.nombre LIKE '%{$search_filter->local}%'
					OR tl.cc_id  LIKE '%{$search_filter->local}%'
					 )";
			}
		}
	}
	//-----------------------------
	$query = "		
	SELECT sum(u1.saldo_final) as sum_record
		FROM tbl_saldo_disashop u1
		JOIN (SELECT MAX(created_at) AS max_created_at, local_id FROM tbl_saldo_disashop WHERE estado = 1 GROUP BY local_id) AS max_u1 ON u1.local_id = max_u1.local_id AND u1.created_at = max_u1.max_created_at
		LEFT JOIN tbl_locales AS tl ON tl.id = u1.local_id
		LEFT JOIN tbl_zonas AS tz ON tz.id = tl.zona_id
		LEFT JOIN tbl_local_proveedor_id AS tlp ON tlp.local_id = u1.local_id AND tlp.servicio_id = 14 AND tlp.estado = 1
		LEFT JOIN tbl_saldo_disashop_tipo AS tsdt ON tsdt.id = u1.tipo_id
		LEFT JOIN tbl_local_caja_config AS lcc ON lcc.local_id = u1.local_id AND lcc.campo = 'saldo_disashop' AND lcc.estado = 1
		WHERE u1.estado = 1
			{$where_zona}
			{$where_tipo}
			{$like_local}
		";
	//----------------------------------------
	/*
	$query = "		
	SELECT 
		sum(u1.saldo_final) as sum_record
	FROM   tbl_saldo_disashop                 AS u1
		LEFT JOIN tbl_saldo_disashop       AS u2
				ON  u1.local_id = u2.local_id
				AND u1.created_at < u2.created_at
		LEFT JOIN tbl_locales              AS tl
				ON  tl.id = u1.local_id
		LEFT JOIN tbl_local_proveedor_id   AS tlp
				ON  ( tlp.local_id = u1.local_id AND tlp.servicio_id = 14)
		LEFT JOIN tbl_zonas                AS tz
				ON  tz.id = tl.zona_id
		LEFT JOIN tbl_saldo_disashop_tipo  AS tsdt
				ON  tsdt.id = u1.tipo_id
		LEFT JOIN tbl_local_caja_config lcc ON (lcc.local_id = u1.local_id AND campo = 'saldo_disashop' and lcc.estado = 1)
	WHERE  u2.created_at IS NULL
		-- AND lcc.estado = 1
		AND u1.created_at IS NOT NULL
		AND tlp.estado = 1
		AND u1.estado = 1
		{$where_zona}
		{$where_tipo}
		{$like_local}
	   ";
	*/
	//----------------------------------------------
	$list_query = $mysqli->query($query);
	$sum_record = 0;
	while ($li = $list_query->fetch_assoc()) {
		$sum_record = number_format($li['sum_record'], 2);
	}

	return $sum_record;
}
function fnc_total_record_balance_disashop($inputs)
{
	global $mysqli;
	$where_zona = "";
	$where_tipo = "";
	$like_local = "";
	if (isset($inputs->search)) {
		$search = $inputs->search;
		if ($search->value != '') {
			$tmp_search_filter = $search->value;
			$search_filter = json_decode($tmp_search_filter);

			if (isset($search_filter->zona) && (int) $search_filter->zona > 0) {
				$where_zona = " AND tz.id = " . $search_filter->zona;
			}
			if (isset($search_filter->tipo) && (int) $search_filter->tipo > 0) {
				$where_tipo = " AND u1.tipo_id = " . $search_filter->tipo;
			}
			if (isset($search_filter->local) && $search_filter->local != '') {
				$like_local = "AND (
					tl.nombre LIKE '%{$search_filter->local}%'
					OR tl.cc_id  LIKE '%{$search_filter->local}%'
					 )";
			}
		}
	}
	//---
	$query = "		
	SELECT COUNT(*) as total_record
	FROM tbl_saldo_disashop u1
	INNER JOIN (
	  SELECT MAX(created_at) AS max_created_at, local_id FROM tbl_saldo_disashop WHERE estado = 1 GROUP BY local_id) AS max_u1 ON u1.local_id = max_u1.local_id AND u1.created_at = max_u1.max_created_at
	LEFT JOIN tbl_locales tl ON tl.id = u1.local_id
	LEFT JOIN tbl_zonas tz ON tz.id = tl.zona_id
	LEFT JOIN tbl_local_proveedor_id tlp ON tlp.local_id = u1.local_id AND tlp.servicio_id = 14 AND tlp.estado = 1
	LEFT JOIN tbl_saldo_disashop_tipo tsdt ON tsdt.id = u1.tipo_id
	LEFT JOIN tbl_local_caja_config lcc ON lcc.local_id = u1.local_id AND lcc.campo = 'saldo_disashop' AND lcc.estado = 1
	WHERE u1.estado = 1 
	  AND tlp.local_id IS NOT NULL 
	  AND lcc.local_id IS NOT NULL
		{$where_zona}
		{$where_tipo}
		{$like_local}
	   ";
	/*
	$query = "		
	SELECT 
		count(*) as total_record
	FROM   tbl_saldo_disashop                 AS u1
		LEFT JOIN tbl_saldo_disashop       AS u2
				ON  u1.local_id = u2.local_id
				AND u1.created_at < u2.created_at
		LEFT JOIN tbl_locales              AS tl
				ON  tl.id = u1.local_id
		LEFT JOIN tbl_local_proveedor_id   AS tlp
				ON  ( tlp.local_id = u1.local_id AND tlp.servicio_id = 14)
		LEFT JOIN tbl_zonas                AS tz
				ON  tz.id = tl.zona_id
		LEFT JOIN tbl_saldo_disashop_tipo  AS tsdt
				ON  tsdt.id = u1.tipo_id
		LEFT JOIN tbl_local_caja_config lcc ON (lcc.local_id = u1.local_id AND campo = 'saldo_disashop' and lcc.estado = 1 )
	WHERE  u2.created_at IS NULL
		-- AND lcc.estado = 1
		AND u1.created_at IS NOT NULL
		AND tlp.estado = 1
		AND u1.estado = 1
		{$where_zona}
		{$where_tipo}
		{$like_local}
	   ";
	   */
	$list_query = $mysqli->query($query);
	$total_record = 0;
	while ($li = $list_query->fetch_assoc()) {
		$total_record = $li['total_record'];
	}

	return $total_record;
}
function fnc_list_zones()
{
	global $mysqli;
	$query = "
	SELECT id, nombre FROM tbl_zonas
	";
	$result_query = $mysqli->query($query);
	while ($li = $result_query->fetch_assoc()) {
		$data_return[] = $li;
	}
	return $data_return;
}
function fnc_list_types()
{
	global $mysqli;
	$query = "
	SELECT id, nombre FROM tbl_saldo_disashop_tipo
	";
	$result_query = $mysqli->query($query);
	while ($li = $result_query->fetch_assoc()) {
		$data_return[] = $li;
	}
	return $data_return;
}

function fnc_list_balance_disashop_history($inputs)
{
	global $mysqli;
	$limit = '';
	if (isset($inputs->length)) {
		if ($inputs->length != -1) {
			$limit = 'LIMIT ' . $inputs->start . ', ' . $inputs->length;
		}
	}

	$where_tipo = "";
	$where_local = "";
	$filter_date = '';
	$to_date = '';
	$from_date = '';
	if (isset($inputs->search)) {
		$search = $inputs->search;
		if ($search->value != '') {
			$tmp_search_filter = $search->value;
			$search_filter = json_decode($tmp_search_filter);
			if (isset($search_filter->tipo) && (int) $search_filter->tipo > 0) {
				$where_tipo = " AND (u1.tipo_id={$search_filter->tipo} OR u1.sub_tipo_id={$search_filter->tipo})";
			}
			if (isset($search_filter->to_date) && (int) $search_filter->to_date != "") {
				$to_date = " AND u1.created_at>= '{$search_filter->to_date}'";
			}
			if (isset($search_filter->from_date) && (int) $search_filter->from_date != "") {
				$from_date = " AND u1.created_at<'" . date('Y-m-d', strtotime('+1 Day', strtotime($search_filter->from_date))) . "'";
			}
		}
	}
	if (isset($inputs->local_id) && ((int) $inputs->local_id) > 0) {
		$where_local = " AND u1.local_id = " . (int)$inputs->local_id;
	}
	if (isset($inputs->to_date) && (int) $inputs->to_date != "") {
		$to_date = " AND u1.created_at>= '{$search_filter->to_date}'";
	}
	if (isset($inputs->from_date) && (int) $inputs->from_date != "") {
		$from_date = " AND u1.created_at<'" . date('Y-m-d', strtotime('+1 Day', strtotime($inputs->from_date))) . "'";
	}
	if ($to_date != '' && $from_date != '') {
		$filter_date = $to_date . '' . $from_date;
	}
	$query = "		
	SELECT u1.id,
		u1.caja_id,
		u1.local_id,
		tl.cc_id,
		tl.nombre                     AS nombre_local,       
		u1.created_at,
		CONCAT('Turno ', c.turno_id)  AS turno_id,      
		(IF (u1.tipo_id = 1, tsdt.nombre, tsdt2.nombre)) AS disashop_tipo,
			u1.saldo_anterior,
		u1.saldo_incremento,
		u1.saldo_final,
		CONCAT(
			IFNULL(p.nombre, ''),
			' ',
			IFNULL(p.apellido_paterno, ''),
			' ',
			IFNULL(p.apellido_materno, '')
		)                             AS personal_nombre,
		
		u1.tipo_id,
		IFNULL(lcc.valor, 0)          AS monto_disashop,
		(
			(
				SELECT count(*) AS filepath
				FROM   tbl_archivos
				WHERE  
					   (
						   tabla = 'tbl_caja'
						   AND estado      = 1
						   AND item_id     = u1.caja_id
						   AND LOWER(archivo) LIKE '%disashop%'
					   )
			) +(
				SELECT count(*) AS filepath
				FROM   tbl_archivos
				WHERE  (tabla = 'tbl_saldo_disashop' AND item_id = u1.id)
					   
			)
		)                                  AS archivo
	FROM   tbl_saldo_disashop            AS u1
		INNER JOIN tbl_saldo_disashop_tipo AS tsdt ON  tsdt.id = u1.tipo_id
		LEFT JOIN tbl_saldo_disashop_tipo AS tsdt2 ON  tsdt2.id = u1.sub_tipo_id
		INNER JOIN tbl_locales        AS tl
				ON  tl.id = u1.local_id
		LEFT JOIN tbl_local_caja_config lcc
				ON  (lcc.local_id = u1.local_id AND campo = 'saldo_disashop')
		LEFT JOIN tbl_caja c
				ON  c.id = u1.caja_id
		LEFT JOIN tbl_login lo
				ON  lo.sesion_cookie = u1.session_cookie
		LEFT JOIN tbl_usuarios u
				ON  u.id = lo.usuario_id
		LEFT JOIN tbl_personal_apt p
				ON  p.id = u.personal_id
		WHERE		 u1.estado = 1
		 AND lcc.estado = 1
		{$where_local}
		{$where_tipo}
		{$filter_date}
		ORDER BY u1.created_at DESC
		{$limit}
	   ";
	$list_query = $mysqli->query($query);
	$list_transaccion = array();
	while ($li = $list_query->fetch_assoc()) {
		$files_exist = (int)($li['archivo']);
		$li['files'] = array();
		if ($files_exist) {
			$file_query = "
			SELECT id, tabla, archivo as filepath FROM tbl_archivos
					WHERE
					(tabla = 'tbl_saldo_disashop' AND item_id =  {$li['id']}) ||
					(LOWER(archivo) LIKE '%disashop%' AND item_id = " . ($li["caja_id"] ?: 0) . ")
			";
			$list_file_query = $mysqli->query($file_query);
			$list_file = array();
			while ($fl = $list_file_query->fetch_assoc()) {
				$list_file[] = $fl;
			}
			$li['files'] = $list_file;
			$list_transaccion[] = $li;
		} else {
			$list_transaccion[] = $li;
		}
	}

	return $list_transaccion;
}

function fnc_total_record_balance_disashop_history($inputs)
{
	global $mysqli;
	$where_tipo = "";
	$where_local = "AND u1.local_id = 0";
	$to_date = '';
	$from_date = '';
	$filter_date = '';
	$to_date = '';
	if (isset($inputs->search)) {
		$search = $inputs->search;
		if ($search->value != '') {
			$tmp_search_filter = $search->value;
			$search_filter = json_decode($tmp_search_filter);

			if (isset($search_filter->tipo) && (int) $search_filter->tipo > 0) {
				$where_tipo = " AND (u1.tipo_id={$search_filter->tipo} OR u1.sub_tipo_id={$search_filter->tipo})";
			}
			if (isset($search_filter->to_date) && (int) $search_filter->to_date != "") {
				$to_date = " AND u1.created_at>= '{$search_filter->to_date}'";
			}
			if (isset($search_filter->from_date) && (int) $search_filter->from_date != "") {
				$from_date = " AND u1.created_at<'" . date('Y-m-d', strtotime('+1 Day', strtotime($search_filter->from_date))) . "'";
			}
		}
	}
	if (isset($inputs->local_id) && (int) $inputs->local_id > 0) {
		$where_local = " AND u1.local_id = " . (int)$inputs->local_id;
	}
	if (isset($inputs->to_date) && (int) $inputs->to_date != "") {
		$to_date = " AND u1.created_at>= '{$search_filter->to_date}'";
	}
	if (isset($inputs->from_date) && (int) $inputs->from_date != "") {
		$from_date = " AND u1.created_at<'" . date('Y-m-d', strtotime('+1 Day', strtotime($inputs->from_date))) . "'";
	}
	if ($to_date != '' && $from_date != '') {
		$filter_date = $to_date . '' . $from_date;
	}

	$query = "		
	SELECT 
		count(*) as total_record
	FROM  tbl_saldo_disashop            AS u1
		INNER JOIN tbl_saldo_disashop_tipo AS tsdt ON  tsdt.id = u1.tipo_id
		LEFT JOIN tbl_saldo_disashop_tipo AS tsdt2 ON  tsdt2.id = u1.sub_tipo_id
		INNER JOIN tbl_locales        AS tl
				ON  tl.id = u1.local_id
		LEFT JOIN tbl_local_caja_config lcc
				ON  (lcc.local_id = u1.local_id AND campo = 'saldo_disashop')
		LEFT JOIN tbl_caja c
				ON  c.id = u1.caja_id
		LEFT JOIN tbl_login lo
				ON  lo.sesion_cookie = u1.session_cookie
		LEFT JOIN tbl_usuarios u
				ON  u.id = lo.usuario_id
		LEFT JOIN tbl_personal_apt p
				ON  p.id = u.personal_id
		WHERE		 u1.estado = 1
		 AND lcc.estado = 1	
		{$where_local}
		{$where_tipo}
		{$filter_date}
	   ";
	$list_query = $mysqli->query($query);
	$total_record = 0;
	while ($li = $list_query->fetch_assoc()) {
		$total_record = $li['total_record'];
	}

	return $total_record;
}
function disashop_report_exxport($inputs)
{
	global $mysqli, $login;

	// $data = $_POST["export_reporte_kasnet"];
	//	 $list_where = "WHERE k.id IN(SELECT MAX(id) from tbl_saldo_disashop WHERE estado = 1 GROUP BY local_id)";
	$list_where = '';
	if ($inputs->tipo != 0) $list_where .= " AND (u1.tipo_id=" . $inputs->tipo . " OR k.sub_tipo_id=" . $inputs->tipo . " )";
	if ($inputs->zona != 0) $list_where .= " AND tl.zona_id " . (($inputs->zona != -1) ? ("=" . $inputs->zona) : "IS NULL");
	if ($login["usuario_locales"]) $list_where .= " AND tl.id IN (" . implode(",", $login["usuario_locales"]) . ")";
	if ($inputs->filter != "") {
		$list_where .= " AND (
				u1.caja_id 		LIKE '%{($inputs->filter}%' OR
				tl.cc_id 		LIKE '%{($inputs->filter}%' OR
				tl.id 			LIKE '%{($inputs->filter}%' OR
				tl.nombre 		LIKE '%{($inputs->filter}%' OR
				u1.saldo_final 	LIKE '%{($inputs->filter}%' OR
				u1.created_at 	LIKE '%{($inputs->filter}%'
			)
		";
	}
	$mysqli->query("START TRANSACTION");

	$query_report = "
		SELECT
		tl.cc_id,
		tl.nombre AS local,
		tlp.proveedor_id AS terminal,
		tz.nombre AS zona_nombre,
		u1.saldo_final,
		tsdt.nombre AS disashop_tipo,
		u1.created_at
		FROM
		(SELECT MAX(created_at) AS max_created_at, local_id FROM tbl_saldo_disashop WHERE estado=1 GROUP BY local_id) AS max_u1
		JOIN tbl_saldo_disashop AS u1 ON u1.local_id = max_u1.local_id AND u1.created_at = max_u1.max_created_at
		LEFT JOIN tbl_locales AS tl ON tl.id = u1.local_id
		LEFT JOIN tbl_zonas AS tz ON tz.id = tl.zona_id
		LEFT JOIN tbl_local_proveedor_id AS tlp ON tlp.local_id = u1.local_id AND tlp.servicio_id = 14 AND tlp.estado = 1 AND tlp.habilitado = 1
		LEFT JOIN tbl_saldo_disashop_tipo AS tsdt ON tsdt.id = u1.tipo_id
		LEFT JOIN tbl_local_caja_config AS lcc ON lcc.local_id = u1.local_id AND lcc.campo = 'saldo_disashop' AND lcc.estado = 1
		WHERE u1.estado=1 AND
		EXISTS (SELECT 1 FROM tbl_local_proveedor_id WHERE local_id = u1.local_id AND servicio_id = 14 AND estado = 1)
		AND EXISTS (SELECT 1 FROM tbl_local_caja_config WHERE local_id = u1.local_id AND campo = 'saldo_disashop' AND estado = 1)
		{$list_where}
	";
	$list_query = $mysqli->query($query_report);
	$mysqli->query("COMMIT");

	$table = [
		[
			"cc_id" 		=> "CC ID",
			"local" 		=> "LOCAL",
			"terminal" 		=> "TERMINAL",
			"zona_nombre" 	=> "ZONA",
			"saldo_final" 	=> "SALDO ACTUAL",
			"disashop_tipot_tipo" 	=> "TIPO",
			"created_at" 	=> "FECHA"
		]
	];
	while ($row = $list_query->fetch_assoc()) {
		$table[] = [
			"cc_id" 		=> $row["cc_id"],
			"local" 		=> $row["local"],
			"terminal" 		=> $row["terminal"],
			"zona_nombre" 	=> $row["zona_nombre"],
			"saldo_final" 	=> $row["saldo_final"],
			"disashop_tipo_tipo" 	=> $row["disashop_tipo"],
			"created_at" 	=> $row["created_at"]
		];
	}
	require_once('../phpexcel/classes/PHPExcel.php');
	$doc = new PHPExcel();
	$doc->setActiveSheetIndex(0);
	$doc->getActiveSheet()->fromArray($table);

	$filename = "reporte_saldo_disashop" . date("Ymdhis") . ".xls";
	$excel_path = '/var/www/html/export/files_export/reporte_disashop/' . $filename;
	$path = "/var/www/html/export/files_export/reporte_disashop/";
	if (!is_dir($path)) {
		mkdir($path, 0777, true);
	}
	$objWriter = PHPExcel_IOFactory::createWriter($doc, 'Excel5');
	$objWriter->save($excel_path);

	$data_return = array(
		"path" => '/export/files_export/reporte_disashop/' . $filename,
		"tipo" => "excel",
		"ext" => "xls",
		"size" => filesize($excel_path),
		"fecha_registro" => date("Y-m-d h:i:s"),
	);
	return $data_return;
}


function disashop_report_history_exxport($inputs)
{
	global $mysqli, $login;
	$list_where = '';
	//if($login["usuario_locales"]) 	$list_where.=" AND tl.id IN (".implode(",", $login["usuario_locales"]).")";
	if ($inputs->tipo != 0) $list_where .= " AND u1.tipo_id=" . $inputs->tipo;

    if (isset($inputs->localId) && ((int) $inputs->localId) > 0) {
		$list_where = " AND tl.id = " . $inputs->localId;
	}

    if (isset($inputs->desde) && $inputs->desde !== "") {
		$list_where .= " AND u1.created_at>= '{$inputs->desde}'";
	}
	if (isset($inputs->hasta) && $inputs->hasta !== "") {
		$list_where .= " AND u1.created_at<'" . date('Y-m-d', strtotime('+1 Day', strtotime($inputs->hasta))) . "'";
	}
	$mysqli->query("START TRANSACTION");
	$query_report = "
	SELECT
		tl.cc_id,
		tl.nombre                     AS local_nombre,  
		u1.created_at,
		CONCAT('Turno ', c.turno_id)  AS turno_id, 
		tsdt.nombre as disashop_tipo,
		u1.saldo_anterior,
		u1.saldo_incremento,
		u1.saldo_final,
		CONCAT(
			IFNULL(p.nombre, ''),
			' ',
			IFNULL(p.apellido_paterno, ''),
			' ',
			IFNULL(p.apellido_materno, '')
		)                             AS personal_nombre
		
	FROM   tbl_saldo_disashop            AS u1
		INNER JOIN tbl_saldo_disashop_tipo AS tsdt ON  tsdt.id = u1.tipo_id
		LEFT JOIN tbl_saldo_disashop_tipo AS tsdt2 ON  tsdt2.id = u1.sub_tipo_id
		INNER JOIN tbl_locales        AS tl
				ON  tl.id = u1.local_id
		LEFT JOIN tbl_local_caja_config lcc
				ON  (lcc.local_id = u1.local_id AND campo = 'saldo_disashop')
		LEFT JOIN tbl_caja c
				ON  c.id = u1.caja_id
		LEFT JOIN tbl_login lo
				ON  lo.sesion_cookie = u1.session_cookie
		LEFT JOIN tbl_usuarios u
				ON  u.id = lo.usuario_id
		LEFT JOIN tbl_personal_apt p
				ON  p.id = u.personal_id
		WHERE		 u1.estado = 1
		 AND lcc.estado = 1

		 {$list_where}
		 ORDER BY u1.created_at DESC
	";
	$list_query = $mysqli->query($query_report);
	$mysqli->query("COMMIT");

	$table = [
		[
			"cc_id" 			=> "CC ID",
			"local_nombre" 		=> "LOCAL",
			"created_at" 		=> "FECHA",
			"turno_id" 			=> "TURNO",
			"disashop_tipo" 		=> "TIPO",
			"saldo_anterior" 	=> "ANTERIOR",
			"saldo_incremento" 	=> "INCREMENTO",
			"saldo_final" 		=> "FINAL",
			"personal_nombre" 	=> "USUARIO"
		]
	];
	while ($row = $list_query->fetch_assoc()) {
		$table[] = [
			"cc_id" 			=> $row["cc_id"],
			"local_nombre" 		=> $row["local_nombre"],
			"created_at" 		=> $row["created_at"],
			"turno_id" 			=> $row["turno_id"],
			"kasnet_tipo" 		=> $row["disashop_tipo"],
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

	$filename = "reporte_saldo_historico_disashop_" . date("Ymdhis") . ".xls";
	$excel_path = '/var/www/html/export/files_export/reporte_disashop/' . $filename;

	$path = "/var/www/html/export/files_export/reporte_disashop/";
	if (!is_dir($path)) {
		mkdir($path, 0777, true);
	}
	$objWriter = PHPExcel_IOFactory::createWriter($doc, 'Excel5');
	$objWriter->save($excel_path);

	$data_return = array(
		"path" => '/export/files_export/reporte_disashop/' . $filename,
		"tipo" => "excel",
		"ext" => "xls",
		"size" => filesize($excel_path),
		"fecha_registro" => date("Y-m-d h:i:s"),
	);
	return $data_return;
}


function get_data_historica_recarga_masiva(){
	global $mysqli, $login;
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

				FROM tbl_saldo_disashop_historico_recarga_masiva AS h
				INNER JOIN tbl_usuarios u ON h.user_created_id = u.id
				INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
				ORDER BY h.created_at DESC";

	$list_query = $mysqli->query($query);
	$list = array();
	while ($li = $list_query->fetch_assoc()) {

		$li['ruta'] = str_replace("/var/www/html","",$li["ruta"]);
		$li['archivo'] = '<a title="Descargar Archivo" target="_blank" href="./'.$li['ruta'].'" class="btn btn-xs btn-primary"><i class="fa fa-file"></i></a>';
		$list[] = $li;
	}

	$result['status'] = 200;
	$result['message'] = "Datos obtenidos de gestión";
	$result['result'] = $list;
	return $result;
		
}