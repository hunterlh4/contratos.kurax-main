<?php
$result=array();
include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//error_reporting(0);


if (isset($_POST["accion"]) && $_POST["accion"]==="listar_log_apis_x_fec") {

	$fec_inicio = $_POST["fec_inicio"];
	$fec_fin = $_POST["fec_fin"];

	$nombre_busqueda = "";
	if(isset($_POST["search"]["value"])) {
		$busqueda_nombre = $_POST["search"]["value"];
		if ($busqueda_nombre !="") {
			$nombre_busqueda = '
			AND (
				created_at LIKE "%'.$_POST["search"]["value"].'%" ';
			$nombre_busqueda .= ')';
		}
	}

	$order = "";
	$column = array(
		1=>"created_at",
		2=>"total",
		3=>"total_error",
		4=>"total_sucess"	
		);
	if(isset($_POST["order"])) {
		if (array_key_exists($_POST['order']['0']['column'],$column)) {
			$order = ' ORDER BY '.$column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' ';
		} else {
			$order = ' ORDER BY id DESC';
		}
	} else {
		$order = ' ORDER BY id DESC ';
	}


	$limit = "";
	if(isset($_POST["length"])) {
		if($_POST["length"] != -1) {
			$limit = 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
		}	
	}

 	//query listado

	 $query = "SELECT
			id,
			created_at,
			COUNT(*) as total,
			SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) AS total_error,
			SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) AS total_sucess
		FROM (
			SELECT
			id as id,
				DATE(created_at) as created_at,
				status
			FROM tbl_televentas_api_calimaco_response
			WHERE 
				created_at >= '".$fec_inicio."'
				AND created_at < '".$fec_fin."'
				$nombre_busqueda
		) AS t
		
		GROUP BY created_at
		".$order
	.$limit;

	 	$result["consulta_query_log"] = $query;
		$list_query = $mysqli->query($query);
		$list_transaccion = array();
		if ($mysqli->error) {
			$result["consulta_error"] = $mysqli->error;
		} else {
			while ($li = $list_query->fetch_assoc()) {
				$list_transaccion[] = $li;
			}
		}


	//query cantidad

	$query_count = "SELECT 
		COUNT(*) cant 
		FROM (SELECT 
				COUNT(id) cant
				FROM wwwapuestatotal_gestion.tbl_televentas_api_calimaco_response 
				WHERE created_at between '".$fec_inicio."' and '".$fec_fin."'
				GROUP BY DATE(created_at)
				ORDER BY id DESC) 
		AS tbl_televentas_api_calimaco_response_count;
	";

	$result["consulta_query_count"] = $query_count;
	$list_query_count = $mysqli->query($query_count);
	$list_transaccion_count = array();
	if ($mysqli->error) {
		$result["consulta_error_count"] = $mysqli->error;
	} else {
		while ($li_count = $list_query_count->fetch_assoc()) {
			$list_transaccion_count[] = $li_count;
		}
	}


	if (count($list_transaccion) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
	} elseif (count($list_transaccion) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_transaccion;
		$result["draw"]            = isset($_POST["draw"]) == true ?intval($_POST["draw"]):'';
		$result["recordsTotal"]    = $list_transaccion_count[0]["cant"];
		$result["recordsFiltered"] = $list_transaccion_count[0]["cant"];
		$result["data"] = $list_transaccion;
	}
	
	
}

if (isset($_POST["accion"]) && $_POST["accion"]==="listar_log_apis_x_dia") {

	$fec_inicio = $_POST["fec_inicio"];
	$fec_fin = $_POST["fec_fin"];

	$where_fecha_inicio = " AND lg.created_at >= '" . $fec_inicio . "' ";
	$where_fecha_fin = " AND lg.created_at <= '" . $fec_fin . "' ";

	$nombre_busqueda = "";
	if(isset($_POST["search"]["value"])) {
		$busqueda_nombre = $_POST["search"]["value"];
		if ($busqueda_nombre !="") {
			$nombre_busqueda = '
			AND (
			lg.created_at LIKE "%'.$_POST["search"]["value"].'%"
			OR prov.name LIKE "%'.$_POST["search"]["value"].'%"
			OR lg.method LIKE "%'.$_POST["search"]["value"].'%"
			OR IFNULL(CONCAT( cli.nombre, " ", IFNULL( cli.apellido_paterno, "" ), " ", IFNULL( cli.apellido_materno, "" ) ), "") LIKE "%'.$_POST["search"]["value"].'%"
			OR lg.bet_id LIKE "%'.$_POST["search"]["value"].'%"';
			$nombre_busqueda .= ')';
		}
	}

	$order = "";
	$column = array(
		1=>"lg.created_at",
		2=>"prov.name",
		3=>"lg.method",
		4=>"lg.bet_id"	
		);
	if(isset($_POST["order"])) {
		if (array_key_exists($_POST['order']['0']['column'],$column)) {
			$order = ' ORDER BY '.$column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' ';
		} else {
			$order = ' ORDER BY lg.id DESC';
		}
	} else {
		$order = ' ORDER BY lg.id DESC ';
	}


	$limit = "";
	if(isset($_POST["length"])) {
		if($_POST["length"] != -1) {
			$limit = 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
		}	
	}

 	//query listado

	$query = "SELECT
	lg.id,
	IFNULL(prov.name, '') proveedor,
	IFNULL(lg.method, '') method,
	IFNULL(lg.bet_id, '') bet_id,
	UPPER(IFNULL(CONCAT( cli.nombre, ' ', IFNULL( cli.apellido_paterno, '' ), ' ', IFNULL( cli.apellido_materno, '' ) ), '')) AS cliente,
	IFNULL(SUBSTRING(lg.body, 1, 25), '') body,
    IFNULL(SUBSTRING(lg.response, 1, 25), '') response,
	IFNULL(lg.hash, '') hash,
	IFNULL(lg.turno_id, '') turno_id,
	IFNULL(lg.cc_id, '') cc_id,
    lg.status,
    UPPER(IFNULL(usu.usuario, '')) AS usuario,
    lg.created_at,
	lg.updated_at
	FROM wwwapuestatotal_gestion.tbl_televentas_api_calimaco_response lg
	LEFT JOIN wwwapuestatotal_gestion.tbl_televentas_proveedor prov ON prov.id = lg.proveedor_id
    LEFT JOIN wwwapuestatotal_gestion.tbl_televentas_clientes cli ON cli.id = lg.client_id
	LEFT JOIN wwwapuestatotal_gestion.tbl_usuarios usu ON usu.id = lg.user_id
	WHERE lg.id > 0
	$where_fecha_inicio 
	$where_fecha_fin
	 
	".$order
	.$limit;

	$result["consulta_query_log"] = $query;
	$list_query = $mysqli->query($query);
	$list_transaccion = array();
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$list_transaccion[] = $li;
		}
	}

	//query cantidad

	$query_count = "SELECT 
	COUNT(lg.id) cant
	FROM wwwapuestatotal_gestion.tbl_televentas_api_calimaco_response lg
	WHERE lg.id > 0
	$where_fecha_inicio 
	$where_fecha_fin
	ORDER BY lg.id DESC
	";

	$result["consulta_query_count"] = $query_count;
	$list_query_count = $mysqli->query($query_count);
	$list_transaccion_count = array();
	if ($mysqli->error) {
		$result["consulta_error_count"] = $mysqli->error;
	} else {
		while ($li_count = $list_query_count->fetch_assoc()) {
			$list_transaccion_count[] = $li_count;
		}
	}


	if (count($list_transaccion) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay transacciones.";
	} elseif (count($list_transaccion) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_transaccion;
		$result["draw"]            = isset($_POST["draw"]) == true ?intval($_POST["draw"]):'';
		$result["recordsTotal"]    = $list_transaccion_count[0]["cant"];
		$result["recordsFiltered"] = $list_transaccion_count[0]["cant"];
		$result["data"] = $list_transaccion;
	}
	
	
}


if (isset($_POST["accion"]) && $_POST["accion"]==="listar_log_apis_x_dia_filtros") {

	$fec_inicio = $_POST["fec_inicio"];
	$fec_fin = $_POST["fec_fin"];
	$proveedor = $_POST["proveedor"];
	$method = $_POST["method"];
	$cliente = $_POST["cliente"];
	$betid = $_POST["betid"];
	$usuario = $_POST["usuario"];

	$where_fecha_inicio = " AND lg.created_at >= '" . $fec_inicio . "' ";
	$where_fecha_fin = " AND lg.created_at <= '" . $fec_fin . "' ";

	$where_proveedor = '';
	if ((int) $proveedor > 0) {
		$where_proveedor = ' AND lg.proveedor_id=' . $proveedor . ' ';
	}

	$where_method = '';
	if ($method !== '') {
		$where_method = " AND lg.method='" . $method."' ";
	}

	$where_cliente = '';
	if ((int) $cliente > 0) {
		$where_cliente = ' AND lg.client_id=' . $cliente . ' ';
	}

	$where_betid = '';
	if ($betid !== '') {
		$where_betid = ' AND lg.bet_id=' . $betid . ' ';
	}

	$where_usuario = '';
	if ((int) $usuario > 0) {
		$where_usuario = ' AND lg.user_id=' . $usuario . ' ';
	}

	$nombre_busqueda = "";
	if(isset($_POST["search"]["value"])) {
		$busqueda_nombre = $_POST["search"]["value"];
		if ($busqueda_nombre !="") {
			$nombre_busqueda = '
			AND (
			lg.created_at LIKE "%'.$_POST["search"]["value"].'%"
			OR prov.name LIKE "%'.$_POST["search"]["value"].'%"
			OR lg.method LIKE "%'.$_POST["search"]["value"].'%"
			OR IFNULL(CONCAT( cli.nombre, " ", IFNULL( cli.apellido_paterno, "" ), " ", IFNULL( cli.apellido_materno, "" ) ), "") LIKE "%'.$_POST["search"]["value"].'%"
			OR lg.bet_id LIKE "%'.$_POST["search"]["value"].'%"';
			$nombre_busqueda .= ')';
		}
	}

	$order = "";
	$column = array(
		1=>"lg.created_at",
		2=>"prov.name",
		3=>"lg.method",
		4=>"lg.bet_id"	
		);
	if(isset($_POST["order"])) {
		if (array_key_exists($_POST['order']['0']['column'],$column)) {
			$order = ' ORDER BY '.$column[$_POST['order']['0']['column']].' '.$_POST['order']['0']['dir'].' ';
		} else {
			$order = ' ORDER BY lg.id DESC';
		}
	} else {
		$order = ' ORDER BY lg.id DESC ';
	}


	$limit = "";
	if(isset($_POST["length"])) {
		if($_POST["length"] != -1) {
			$limit = 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
		}	
	}

 	//query listado

	$query = "SELECT
	lg.id,
	IFNULL(prov.name, '') proveedor,
	IFNULL(lg.method, '') method,
	IFNULL(lg.bet_id, '') bet_id,
	UPPER(IFNULL(CONCAT( cli.nombre, ' ', IFNULL( cli.apellido_paterno, '' ), ' ', IFNULL( cli.apellido_materno, '' ) ), '')) AS cliente,
	IFNULL(SUBSTRING(lg.body, 1, 25), '') body,
    IFNULL(SUBSTRING(lg.response, 1, 25), '') response,
	IFNULL(lg.hash, '') hash,
	IFNULL(lg.turno_id, '') turno_id,
	IFNULL(lg.cc_id, '') cc_id,
    lg.status,
    UPPER(IFNULL(usu.usuario, '')) AS usuario,
    lg.created_at,
	lg.updated_at
	FROM wwwapuestatotal_gestion.tbl_televentas_api_calimaco_response lg
	LEFT JOIN wwwapuestatotal_gestion.tbl_televentas_proveedor prov ON prov.id = lg.proveedor_id
    LEFT JOIN wwwapuestatotal_gestion.tbl_televentas_clientes cli ON cli.id = lg.client_id
	LEFT JOIN wwwapuestatotal_gestion.tbl_usuarios usu ON usu.id = lg.user_id
	WHERE lg.id > 0
	$where_fecha_inicio 
	$where_fecha_fin
	$where_proveedor
	$where_method
	$where_cliente
	$where_betid
	$where_usuario
	".$order
	.$limit;

	$result["consulta_query_log"] = $query;
	$list_query = $mysqli->query($query);
	$list_transaccion = array();
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li = $list_query->fetch_assoc()) {
			$list_transaccion[] = $li;
		}
	}

	//query cantidad

	$query_count = "SELECT 
	COUNT(lg.id) cant
	FROM wwwapuestatotal_gestion.tbl_televentas_api_calimaco_response lg
	WHERE lg.id > 0
	$where_fecha_inicio 
	$where_fecha_fin
	$where_proveedor
	$where_method
	$where_cliente
	$where_betid
	$where_usuario
	ORDER BY lg.id DESC
	";

	$result["consulta_query_count"] = $query_count;
	$list_query_count = $mysqli->query($query_count);
	$list_transaccion_count = array();
	if ($mysqli->error) {
		$result["consulta_error_count"] = $mysqli->error;
	} else {
		while ($li_count = $list_query_count->fetch_assoc()) {
			$list_transaccion_count[] = $li_count;
		}
	}


	if (count($list_transaccion) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
	} elseif (count($list_transaccion) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_transaccion;
		$result["draw"]            = isset($_POST["draw"]) == true ?intval($_POST["draw"]):'';
		$result["recordsTotal"]    = $list_transaccion_count[0]["cant"];
		$result["recordsFiltered"] = $list_transaccion_count[0]["cant"];
		$result["data"] = $list_transaccion;
	}
	
	
}


if (isset($_POST["accion"]) && $_POST["accion"]==="SecDevView_listar_modal_detalle") {

	$id_transaccion = $_POST["id"];
 
	$query = "SELECT 
				IFNULL(body, '') body,
    			IFNULL(response, '') response
				FROM wwwapuestatotal_gestion.tbl_televentas_api_calimaco_response WHERE 
				id = '".$id_transaccion."' ";

	$list = $mysqli->query($query);
	$lista = array();
	while ($li = $list->fetch_assoc()) {
		$lista[] = $li; 
	}
	$lista_new = array();

	if(strpos($lista[0]["response"], 'Bearer')){
		$response_filt= explode("{", $lista[0]["response"]);
		$response = '{'.$response_filt[1];
	}else{
		$response = $lista[0]["response"];
	}

	if(strpos($lista[0]["body"], 'Bearer')){
		$body_filt= explode("{", $lista[0]["body"]);
		$body = '{'.$body_filt[1];
	}else{
		$body = $lista[0]["body"];
	}

	$lista_new = [
		"body" => $body ,
		"response" => $response
	];
	

	if (count($lista) <> 0) {
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $lista_new;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar historial fusion clientes.";
		$result["result"] = $lista_new;
	}
}


if (isset($_GET["accion"]) && $_GET["accion"]==="SecDevView_listar_proveedor_log") {

	$cargo_id = $login ? $login['cargo_id'] : 0;
	$query ="
		SELECT
			id as cod_prov, 		
			IFNULL(name, '') name
		FROM
			tbl_televentas_proveedor
		HAVING 
			name LIKE '%" . strtoupper(trim($_GET["term"])) . "%'
		LIMIT 10
		";
	//$result["consulta_query"] = $query;
	$list_query=$mysqli->query($query);
	$list_registros=array();
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li=$list_query->fetch_assoc()) {
			$list_registros[]=$li;
			$temp_array['codigo'] = $li['cod_prov'];
            $temp_array['value'] = strtoupper('' . $li['name']);
            $temp_array['label'] = $temp_array['value'];
            array_push($result, $temp_array);
		}
	}

	if(count($list_registros)===0){
		$result["http_code"] = 204;
		//$result["status"] = "No hay registros.";
		$result["result"] = $list_registros;
		$result['value'] = '';
        $result['label'] = 'No se encontraron coincidencias.';
	} elseif(count($list_registros)>0){
		$result["http_code"] = 200;
		$result["result"] = $list_registros;
	} else {
		$result["http_code"] = 400;
		//$result["status"] ="Ocurrió un error al consultar.";
		$result['value'] = '';
        $result['label'] = 'No se encontraron coincidencias.';
	}
}


if (isset($_GET["accion"]) && $_GET["accion"]==="SecDevView_listar_clientes_log") {

	$cargo_id = $login ? $login['cargo_id'] : 0;
	$query ="
		SELECT
			c.id as cod_cli, 		
			IFNULL(c.num_doc, '') num_doc,
			IFNULL(c.telefono, '') telefono,
			IFNULL(c.web_id, '') web_id,
			IFNULL(c.player_id, '') player_id,
			IFNULL(c.web_full_name, '') web_full_name,
			CONCAT(IFNULL(c.nombre, ''), ' ', IFNULL(c.apellido_paterno, ''), ' ', IFNULL(c.apellido_materno, '')) AS cliente
		FROM
			tbl_televentas_clientes c
		 
		HAVING 
			cliente LIKE '%" . strtoupper(trim($_GET["term"])) . "%'
			OR num_doc LIKE '%" . strtoupper(trim($_GET["term"])) . "%'
			OR web_id LIKE '%" . strtoupper(trim($_GET["term"])) . "%'
		LIMIT 10
		";
	//$result["consulta_query"] = $query;
	$list_query=$mysqli->query($query);
	$list_registros=array();
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li=$list_query->fetch_assoc()) {
			$list_registros[]=$li;
			$temp_array['codigo'] = $li['cod_cli'];
            $temp_array['value'] = strtoupper('' . $li['num_doc'] . ' - ' . $li['cliente']. ' - ' .$li['web_id']);
            $temp_array['label'] = $temp_array['value'];
            array_push($result, $temp_array);
		}
	}

	if(count($list_registros)===0){
		$result["http_code"] = 204;
		//$result["status"] = "No hay registros.";
		$result["result"] = $list_registros;
		$result['value'] = '';
        $result['label'] = 'No se encontraron coincidencias.';
	} elseif(count($list_registros)>0){
		$result["http_code"] = 200;
		$result["result"] = $list_registros;
	} else {
		$result["http_code"] = 400;
		//$result["status"] ="Ocurrió un error al consultar.";
		$result['value'] = '';
        $result['label'] = 'No se encontraron coincidencias.';
	}
}


if (isset($_GET["accion"]) && $_GET["accion"]==="SecDevView_listar_usuario_log") {

	$cargo_id = $login ? $login['cargo_id'] : 0;
	$query ="
		SELECT
			id as cod_usu, 		
			IFNULL(usuario, '') usuario
		FROM
			tbl_usuarios
		 
		HAVING 
			usuario LIKE '%" . strtoupper(trim($_GET["term"])) . "%'
		 
		LIMIT 10
		";
	//$result["consulta_query"] = $query;
	$list_query=$mysqli->query($query);
	$list_registros=array();
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li=$list_query->fetch_assoc()) {
			$list_registros[]=$li;
			$temp_array['codigo'] = $li['cod_usu'];
            $temp_array['value'] = strtoupper('' . $li['usuario']);
            $temp_array['label'] = $temp_array['value'];
            array_push($result, $temp_array);
		}
	}

	if(count($list_registros)===0){
		$result["http_code"] = 204;
		//$result["status"] = "No hay registros.";
		$result["result"] = $list_registros;
		$result['value'] = '';
        $result['label'] = 'No se encontraron coincidencias.';
	} elseif(count($list_registros)>0){
		$result["http_code"] = 200;
		$result["result"] = $list_registros;
	} else {
		$result["http_code"] = 400;
		//$result["status"] ="Ocurrió un error al consultar.";
		$result['value'] = '';
        $result['label'] = 'No se encontraron coincidencias.';
	}
}


if (isset($_POST["accion"]) && $_POST["accion"]==="SecDevView_excel_list_dia") {

	$fec_inicio = $_POST["fec_inicio"];
	$fec_fin = $_POST["fec_fin"];
	$dia = $_POST["dia"];

	$where_fecha_inicio = " AND lg.created_at >= '" . $fec_inicio . "' ";
	$where_fecha_fin = " AND lg.created_at <= '" . $fec_fin . "' "; 

	// QUERY
		$query_1 = "SELECT
		lg.created_at,
		IFNULL(prov.name, '') proveedor,
		IFNULL(lg.method, '') method,
		IFNULL(lg.bet_id, '') bet_id,
		UPPER(IFNULL(CONCAT( cli.nombre, ' ', IFNULL( cli.apellido_paterno, '' ), ' ', IFNULL( cli.apellido_materno, '' ) ), '')) AS cliente,
		IFNULL(lg.body, '') body,
		IFNULL(lg.response, '') response,
		IFNULL(lg.hash, '') hash,
		IFNULL(lg.turno_id, '') turno_id,
		IFNULL(lg.cc_id, '') cc_id,
		lg.status,
		UPPER(IFNULL(usu.usuario, '')) AS usuario,
		lg.updated_at
		FROM wwwapuestatotal_gestion.tbl_televentas_api_calimaco_response lg
		LEFT JOIN wwwapuestatotal_gestion.tbl_televentas_proveedor prov ON prov.id = lg.proveedor_id
		LEFT JOIN wwwapuestatotal_gestion.tbl_televentas_clientes cli ON cli.id = lg.client_id
		LEFT JOIN wwwapuestatotal_gestion.tbl_usuarios usu ON usu.id = lg.user_id
		WHERE lg.id > 0
		$where_fecha_inicio 
		$where_fecha_fin
		ORDER BY lg.id DESC
		";
	

	$list_query = $mysqli->query($query_1);
	$result_data = array();

	if ($mysqli->error) {
		//$result["consulta_error"] = $mysqli->error;
		echo json_encode([
			"error" => "Export error"
		]);
		exit;
	} else {
		
		while ($li = $list_query->fetch_assoc()) {
			$result_data[] = $li;
		}

		$headers = [
			"created_at" => "Created_at",
			"proveedor" => "Proveedor",
			"method" => "Method",
			"bet_id" => "Bet_id",
			"cliente" => "Cliente",
			"body" => "Body",
			"response" => "Response",
			"hash" => "Hash",
			"turno_id" => "Turno_id",
			"cc_id" => "CC_id",
			"status" => "Status",
			"usuario" => "Usuario",
			"updated_at" => "Updated_at" 
		];
		array_unshift($result_data, $headers);

		ini_set('display_errors', 0);
		ini_set('display_startup_errors', 0);
		error_reporting(0);

		require_once '../phpexcel/classes/PHPExcel.php';
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getActiveSheet()->fromArray($result_data, null, 'A1');
		$date = new DateTime();
		$file_title = "log_registro_dia_" . $dia;

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
			echo json_encode(["error" => $e, "query" => $query_1]);
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


if (isset($_POST["accion"]) && $_POST["accion"]==="SecDevView_excel_registro") {

	$id = $_POST["id"];
	$fecha = $_POST["fecha"];

	// QUERY
	$query_1 = "SELECT
		lg.created_at,
		IFNULL(prov.name, '') proveedor,
		IFNULL(lg.method, '') method,
		IFNULL(lg.bet_id, '') bet_id,
		UPPER(IFNULL(CONCAT( cli.nombre, ' ', IFNULL( cli.apellido_paterno, '' ), ' ', IFNULL( cli.apellido_materno, '' ) ), '')) AS cliente,
		IFNULL(lg.body, '') body,
		IFNULL(lg.response, '') response,
		IFNULL(lg.hash, '') hash,
		IFNULL(lg.turno_id, '') turno_id,
		IFNULL(lg.cc_id, '') cc_id,
		lg.status,
		UPPER(IFNULL(usu.usuario, '')) AS usuario,
		lg.updated_at
		FROM wwwapuestatotal_gestion.tbl_televentas_api_calimaco_response lg
		LEFT JOIN wwwapuestatotal_gestion.tbl_televentas_proveedor prov ON prov.id = lg.proveedor_id
		LEFT JOIN wwwapuestatotal_gestion.tbl_televentas_clientes cli ON cli.id = lg.client_id
		LEFT JOIN wwwapuestatotal_gestion.tbl_usuarios usu ON usu.id = lg.user_id
		WHERE lg.id = '".$id."'
		ORDER BY lg.id DESC
		";
	

	$list_query = $mysqli->query($query_1);
	$result_data = array();

	if ($mysqli->error) {
		//$result["consulta_error"] = $mysqli->error;
		echo json_encode([
			"error" => "Export error"
		]);
		exit;
	} else {
		
		while ($li = $list_query->fetch_assoc()) {
			$result_data[] = $li;
		}

		$headers = [
			"created_at" => "Created_at",
			"proveedor" => "Proveedor",
			"method" => "Method",
			"bet_id" => "Bet_id",
			"cliente" => "Cliente",
			"body" => "Body",
			"response" => "Response",
			"hash" => "Hash",
			"turno_id" => "Turno_id",
			"cc_id" => "CC_id",
			"status" => "Status",
			"usuario" => "Usuario",
			"updated_at" => "Updated_at" 
		];
		array_unshift($result_data, $headers);

		ini_set('display_errors', 0);
		ini_set('display_startup_errors', 0);
		error_reporting(0);

		require_once '../phpexcel/classes/PHPExcel.php';
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getActiveSheet()->fromArray($result_data, null, 'A1');
		$date = new DateTime();
		$file_title = "log_registro_" . $fecha;

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
			echo json_encode(["error" => $e, "query" => $query_1]);
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


if (isset($_POST["accion"]) && $_POST["accion"]==="SecDevView_exportar_excel_dl") {

	$fec_inicio = $_POST["fec_inicio"];
	$fec_fin = $_POST["fec_fin"];
	$proveedor = $_POST["proveedor"];
	$method = $_POST["method"];
	$cliente = $_POST["cliente"];
	$betid = $_POST["betid"];
	$usuario = $_POST["usuario"];

	$where_fecha_inicio = " AND lg.created_at >= '" . $fec_inicio . "' ";
	$where_fecha_fin = " AND lg.created_at <= '" . $fec_fin . "' ";

	$where_proveedor = '';
	if ((int) $proveedor > 0) {
		$where_proveedor = ' AND lg.proveedor_id=' . $proveedor . ' ';
	}

	$where_method = '';
	if ($method !== '') {
		$where_method = " AND lg.method='" . $method."' ";
	}

	$where_cliente = '';
	if ((int) $cliente > 0) {
		$where_cliente = ' AND lg.client_id=' . $cliente . ' ';
	}
	
	$where_betid = '';
	if ($betid !== '') {
		$where_betid = ' AND lg.bet_id=' . $betid . ' ';
	}
	
	$where_usuario = '';
	if ((int) $usuario > 0) {
		$where_usuario = ' AND lg.user_id=' . $usuario . ' ';
	}

	// QUERY
	$query_1 = "SELECT
	lg.created_at,
	IFNULL(prov.name, '') proveedor,
	IFNULL(lg.method, '') method,
	IFNULL(lg.bet_id, '') bet_id,
	UPPER(IFNULL(CONCAT( cli.nombre, ' ', IFNULL( cli.apellido_paterno, '' ), ' ', IFNULL( cli.apellido_materno, '' ) ), '')) AS cliente,
	IFNULL(lg.body, '') body,
	IFNULL(lg.response, '') response,
	IFNULL(lg.hash, '') hash,
	IFNULL(lg.turno_id, '') turno_id,
	IFNULL(lg.cc_id, '') cc_id,
	lg.status,
	UPPER(IFNULL(usu.usuario, '')) AS usuario,				
	lg.updated_at
	FROM wwwapuestatotal_gestion.tbl_televentas_api_calimaco_response lg
	LEFT JOIN wwwapuestatotal_gestion.tbl_televentas_proveedor prov ON prov.id = lg.proveedor_id
	LEFT JOIN wwwapuestatotal_gestion.tbl_televentas_clientes cli ON cli.id = lg.client_id
	LEFT JOIN wwwapuestatotal_gestion.tbl_usuarios usu ON usu.id = lg.user_id
	WHERE lg.id > 0
	$where_fecha_inicio 
	$where_fecha_fin
	$where_proveedor
	$where_method
	$where_cliente
	$where_betid
	$where_usuario
	ORDER BY lg.id DESC
		";
	

	$list_query = $mysqli->query($query_1);
	$result_data = array();

	if ($mysqli->error) {
		//$result["consulta_error"] = $mysqli->error;
		echo json_encode([
			"error" => "Export error"
		]);
		exit;
	} else {
		
		while ($li = $list_query->fetch_assoc()) {
			$result_data[] = $li;
		}

		$headers = [
			"created_at" => "Created_at",
			"proveedor" => "Proveedor",
			"method" => "Method",
			"bet_id" => "Bet_id",
			"cliente" => "Cliente",
			"body" => "Body",
			"response" => "Response",
			"hash" => "Hash",
			"turno_id" => "Turno_id",
			"cc_id" => "CC_id",
			"status" => "Status",
			"usuario" => "Usuario",
			"updated_at" => "Updated_at" 
		];
		array_unshift($result_data, $headers);

		ini_set('display_errors', 0);
		ini_set('display_startup_errors', 0);
		error_reporting(0);

		require_once '../phpexcel/classes/PHPExcel.php';
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getActiveSheet()->fromArray($result_data, null, 'A1');
		$date = new DateTime();
		$file_title = "dev_view_log_apis_" .  $date->getTimestamp();

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
			echo json_encode(["error" => $e, "query" => $query_1]);
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