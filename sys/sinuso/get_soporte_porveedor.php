<?php
include("db_connect.php");
include("sys_login.php");

if (isset($_POST['accion'])) {
	$inputs = json_decode(json_encode($_POST));
	switch ($_POST['accion']) {
		case 'list_provider_per_channel':
			$data_result 				= fnc_list_provider_per_chanel($inputs);
			$result["http_code"] = 200;
			$result["status"] = "ok";
			$result["data"] = $data_result;
			echo json_encode($result);
			break;
		case 'analyze_file':
			$data_result 				= fnc_analyze_file($inputs, $_FILES);
			$data_analyze_result		= fnc_compare_data($inputs, $data_result);
			$result["http_code"] = 200;
			$result["status"] = "ok";
			$result["data"] = $data_analyze_result;
			echo json_encode($result);
			break;
		case 'save_data_provider_per_service':
			$data_result 				= fnc_save_provider_to_db($inputs);
			if ($data_result) {
				$result["http_code"] = 200;
				$result["status"] = "ok";
				$result["message"] = 'Datos insertados';
				$result["error"] = false;
			}else {
				$result["http_code"] = 400;
				$result["status"] = "error";
				$result["error"] = true;
				$result["message"] = 'Error en la transacción';
			}
			echo json_encode($result);
			break;
			
		default:
			# code...
			break;
	}
}

function fnc_list_provider_per_chanel($inputs)
{
	global $mysqli;
	$where_kurax = '';
	if($inputs->channel == '17'){
		$where_kurax = ' and lpi.canal_de_venta_id = '.$inputs->canal_venta_id;
	}
	$query_list = "
	SELECT 
		lpi.id,
		l.id,
		l.cc_id,
		l.nombre,
		lpi.canal_de_venta_id,
		lpi.proveedor_id,
		s.nombre AS nombre_canal
	FROM   tbl_local_proveedor_id       AS lpi
		LEFT JOIN tbl_locales        AS l
				ON  l.id = lpi.local_id
		LEFT JOIN tbl_canales_venta  AS s
				ON  s.id = lpi.canal_de_venta_id
	WHERE  lpi.servicio_id = {$inputs->channel}
		AND lpi.estado = 1 
		{$where_kurax}
	";
	$list_query_result = $mysqli->query($query_list);
	$list_providers_id = array();
	while ($li = $list_query_result->fetch_assoc()) {
		$list_providers_id[] = $li;
	}

	return $list_providers_id;
}


function fnc_analyze_file($inputs, $files)
{
	$ext = pathinfo($files['file']['name'], PATHINFO_EXTENSION);
	$extract = array();
	if ($ext == "xls" || $ext == "xlsx") {
		$return = array();
		$return["memory_init"] = memory_get_usage();
		$return["time_init"] = microtime(true);
		include("global_config.php");
		include("db_connect.php");
		include("sys_login.php");
		include("/var/www/html/sys/helpers.php");
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
					"id" => str_replace("'", "", $worksheet->getCell('B' . $row)->getValue()),

				];
			}
		}
	}
	return ($extract);
}

function fnc_compare_data($inputs, $data_analyze)
{
	global $mysqli;
	$where_kurax = '';
	if($inputs->idChannel == '17'){
		$where_kurax = ' and lpi.canal_de_venta_id = '.$inputs->canal_venta_id;
	}
	$query = "
	SELECT
		l.cc_id,		
		lpi.proveedor_id
	FROM   tbl_local_proveedor_id       AS lpi
		LEFT JOIN tbl_locales        AS l
				ON  l.id = lpi.local_id
		LEFT JOIN tbl_canales_venta  AS s
				ON  s.id = lpi.canal_de_venta_id
	WHERE  lpi.servicio_id = {$inputs->idChannel}
		AND lpi.estado = 1
		{$where_kurax}	
	";

	$list_query_result = $mysqli->query($query);


	$list_providers_id = array();
	$all_providers_id = array();
	while ($li = $list_query_result->fetch_assoc()) {
		$list_providers_id[$li['cc_id']] = $li['proveedor_id'];
		$all_providers_id[$li['proveedor_id']] = $li['cc_id'];
	}

	$new_data_array = [];
	$types_data = [
		1 => ' ID del proveedor repetido',
		2 => ' Nuevo Registro',
		3 => ' CC_ID no reconocido',
	];
	$type_data = 0;

	foreach ($data_analyze as $key => $data) {
		$ceco_exist = '';
		$locales = fnc_get_local();
		if (in_array($data['cc_id'], $locales)) {
			if (array_key_exists($data['id'], $all_providers_id)) {
				$type_data = 1;
				$ceco_exist = ' en CECO_' . $all_providers_id[$data['id']];
			} else {
				$type_data = 2;
			}
		} else {
			$type_data = 3;
		}
		$data['channel_id'] = $inputs->idChannel;
		$data['type_data'] = $type_data;
		$data['type_data_msj'] = $types_data[$type_data] . $ceco_exist;
		$new_data_array[] = $data;
	}

	return $new_data_array;
}
function fnc_get_local()
{

	global $mysqli;
	$query = "
	SELECT l.id,
		l.canal_id,
		l.cc_id,
		l.red_id,
		l.tipo_id,
		l.propiedad_id,
		l.cliente_id,
		l.razon_social_id,
		l.nombre
	FROM   tbl_locales AS l
	WHERE  l.estado = 1
		AND l.red_id IN (1, 5, 9,16)
		AND l.cc_id IS NOT NULL
	";

	$list_query_result = $mysqli->query($query);
	$locales = array();
	while ($li = $list_query_result->fetch_assoc()) {
		$locales[] = $li['cc_id'];
	}
	return $locales;
}

function fnc_save_provider_to_db($inputs)
{
	global $mysqli;
	$where_kurax = '';
	if($inputs->id_channel == '17'){
		$where_kurax = ' and id = '.$inputs->canal_venta_id;
	}
	$providers = json_decode($inputs->data_save);
	if (count($providers)==0) {
		return false;
	}
	$query_values = '';
	$query = "
	SELECT l.id,
		l.canal_id,
		l.cc_id,
		l.red_id,
		l.tipo_id,
		l.propiedad_id,
		l.cliente_id,
		l.razon_social_id,
		l.nombre
	FROM   tbl_locales AS l
	WHERE  l.estado = 1
		AND l.red_id IN (1, 5, 9,16)
		AND l.cc_id IS NOT NULL
	";

	$list_query_result = $mysqli->query($query);
	$locales = array();
	while ($li = $list_query_result->fetch_assoc()) {
		$locales[$li['cc_id']] = $li;
	}
	$cnv = [];
	$canales_de_venta_query = $mysqli->query("SELECT id,servicio_id,nombre,codigo FROM tbl_canales_venta WHERE servicio_id = {$inputs->id_channel} {$where_kurax}");
	while ($cdv = $canales_de_venta_query->fetch_assoc()) {
		$cnv = $cdv['id'];
	}
	foreach ($providers as $provider) {


		$query_values .= "(
						'" . $locales[$provider->cc_id]['id'] . "',
						'" . $inputs->id_channel . "',
						" . $cnv . ",
						" . 'NULL' . ",
						" . $provider->id . "
		),";
	}

	$query_values = substr($query_values, 0, -1);

	$sql_insert_lp_id = "
	INSERT INTO tbl_local_proveedor_id 
	(local_id,servicio_id,canal_de_venta_id,nombre,proveedor_id) 
	VALUES 
	{$query_values}";
	if (!$mysqli->query($sql_insert_lp_id)) {
		//printf("Errormessage: %s\n", $mysqli->error);
		$return = false;
	}
	return true;
}
