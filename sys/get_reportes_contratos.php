<?php
include "db_connect.php";
include "sys_login.php";
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$request = $_POST;
$usuario_id = $login?$login['id']:null;
if ($usuario_id == null) {
    $result['status'] = 400;
    $result['result'] = '';
    $result['message'] = 'Por favor inicie sessión, e intentalo otra vez.';
    echo json_encode($result);
    exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipos_de_contrato") {
	$response = ObtenerTiposDeContratos();
	echo json_encode($response);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_areas") {
	$response = ObtenerAreas();
	echo json_encode($response);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_estados") {
	$response = ObtenerEstados();
	echo json_encode($response);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_estados_solicitud") {
	$response = ObtenerEstadosSolicitud();
	echo json_encode($response);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_estados_aprobacion") {
	$response = ObtenerEstadosAprobacion();
	echo json_encode($response);
	exit();
}


if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_reporte") {
	$data['tipo_contrato'] = $_POST['tipo_contrato'];
	$data['area'] = $_POST['area'];
	$data['desde'] = date("Y-m-d", strtotime($_POST['desde'])) ;
	$data['hasta'] = date("Y-m-d", strtotime($_POST['hasta'])) ;
	$data['estado'] = $_POST['estado'];
	$data['estado_solicitud'] = $_POST['estado_solicitud'];
	$data['estado_aprobacion'] = $_POST['estado_aprobacion'];
	$response = ObtenerReporte($data);
	echo json_encode($response);
	exit();
}



function ObtenerTiposDeContratos(){
	include "db_connect.php";
	include "sys_login.php";
	
	$query = "SELECT tc.id, tc.nombre FROM cont_tipo_contrato tc 
	WHERE tc.id IN (2,5,7) 
	ORDER BY tc.num_orden  ASC";
	$list_query = $mysqli->query($query);
	$list       = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	$result["status"]  = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"]  = $list;
	echo json_encode($result);
	exit();
}

function ObtenerAreas(){
	include "db_connect.php";
	include "sys_login.php";
	
	$query = "SELECT id,nombre FROM tbl_areas WHERE estado = 1 ORDER BY nombre ASC";
	$list_query = $mysqli->query($query);
	$list       = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	$result["status"]  = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"]  = $list;
	echo json_encode($result);
	exit();
}

function ObtenerEstados(){
	include "db_connect.php";
	include "sys_login.php";
	
	$query = "SELECT e.etapa_id  as  id, e.situacion as nombre FROM cont_etapa  as e 
	WHERE e.etapa_id IN (1,5)";
	$list_query = $mysqli->query($query);
	$list       = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	$result["status"]  = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"]  = $list;
	echo json_encode($result);
	exit();
}

function ObtenerEstadosSolicitud(){
	include "db_connect.php";
	include "sys_login.php";
	
	$query = "SELECT id,nombre FROM cont_estado_solicitud WHERE status = 1 ORDER BY id ASC";
	$list_query = $mysqli->query($query);
	$list       = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	$result["status"]  = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"]  = $list;
	echo json_encode($result);
	exit();
}

function ObtenerEstadosAprobacion(){
	include "db_connect.php";
	include "sys_login.php";
	
	$list = array();
	array_push($list, array(
		'id' => 1,
		'nombre' => 'Aprobado'
	));
	array_push($list, array(
		'id' => 2,
		'nombre' => 'Rechazado'
	));
	array_push($list, array(
		'id' => 3,
		'nombre' => 'Pendiente'
	));

	$result["status"]  = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"]  = $list;
	echo json_encode($result);
	exit();
}

function ObtenerReporte($data){
	try {

		include "db_connect.php";
		include "sys_login.php";
		include("function_replace_invalid_caracters_contratos.php");
		
		$where_cont_prov_acue_etapa = "";
		$where_cont_int_etapa = "";
		$where_aden_prov_acue_etapa = "";
		$where_aden_int_etapa = "";

		$where_cont_prov_acue_estado_sol = "";
		$where_cont_int_estado_sol = "";
		$where_aden_prov_acue_estado_sol = "";
		$where_aden_int_estado_sol = "";


		$where_cont_prov_acue_estado_aprob = "";
		$where_cont_int_estado_aprob = "";
		$where_aden_prov_acue_estado_aprob = "";
		$where_aden_int_estado_aprob = "";

		$where_cont_prov_acue_area = "";
		$where_cont_int_area = "";
		$where_aden_prov_acue_area = "";
		$where_aden_int_area = "";

		//contratos proveedores y acuerdo de confidencialidad
		$where_cont_prov_acue_tipo_contrato = " AND cc.tipo_contrato_id IN (2,5)";
		$where_cont_prov_acue_fecha = "";
		//contratos internos
		$where_cont_int_tipo_contrato = " AND cc.tipo_contrato_id IN (7)";
		$where_cont_int_fecha = "";
		//adenda de proveedores y acuerdo de confidencialidad
		$where_aden_prov_acue_tipo_contrato = " AND cc.tipo_contrato_id IN (2,5,7)";
		$where_aden_prov_acue_fecha = "";
		//adenda de contratos internos
		$where_aden_int_tipo_contrato = " AND cc.tipo_contrato_id IN (2,5,7)";
		$where_aden_int_fecha = "";

		if (!Empty($data['estado_solicitud']) && $data['estado_solicitud'] != "0") {

			if ( (int) $data['estado_solicitud'] == 1) {
				$where_cont_prov_acue_estado_sol = " AND ( cc.estado_solicitud = 1 OR cc.estado_solicitud IS NULL ) ";
				$where_cont_int_estado_sol = " AND ( cc.estado_solicitud = 1 OR cc.estado_solicitud IS NULL ) ";

				$where_aden_prov_acue_estado_sol = " AND ( ca.estado_solicitud_id = 1 OR ca.estado_solicitud_id IS NULL ) ";
				$where_aden_int_estado_sol = " AND ( ca.estado_solicitud_id = 1 OR ca.estado_solicitud_id IS NULL ) ";
			} else {
				$where_cont_prov_acue_estado_sol = " AND cc.estado_solicitud = '".$data['estado_solicitud']."' ";
				$where_cont_int_estado_sol = " AND cc.estado_solicitud = '".$data['estado_solicitud']."' ";

				$where_aden_prov_acue_estado_sol = " AND ca.estado_solicitud_id = '".$data['estado_solicitud']."' ";
				$where_aden_int_estado_sol = " AND ca.estado_solicitud_id = '".$data['estado_solicitud']."' ";
			}
			/*
			if ( (int) $data['estado_sol'] === 1) {
				$where_cont_prov_acue_estado_sol = " AND ( cc.cancelado_id != 1 || cc.cancelado_id IS NULL )";
				$where_cont_int_estado_sol = " AND ( cc.cancelado_id != 1 || cc.cancelado_id IS NULL )";

				$where_aden_prov_acue_estado_sol = " AND ( ca.cancelado_id = 1 OR ca.cancelado_id IS NULL ) ";
				$where_aden_int_estado_sol = " AND ( ca.cancelado_id = 1 OR ca.cancelado_id IS NULL ) ";
				//en proceso
			} else if ( (int) $data['estado_sol'] === 2) {
				$where_cont_prov_acue_estado_sol = " AND cc.cancelado_id = 1 ";
				$where_cont_int_estado_sol = " AND cc.cancelado_id = 1 ";

				$where_aden_prov_acue_estado_sol = " AND cc.cancelado_id = 1 ";
				$where_aden_int_estado_sol = " AND cc.cancelado_id = 1 ";
				//cancelado
			}
			*/
		}

		if (!Empty($data['tipo_contrato']) && $data['tipo_contrato'] != "0") {
			if ($data['tipo_contrato'] == 2 || $data['tipo_contrato'] == 5) {
				$where_cont_prov_acue_tipo_contrato = " AND cc.tipo_contrato_id = ".$data['tipo_contrato'];
				$where_cont_int_tipo_contrato = " AND cc.tipo_contrato_id = 0";

				$where_aden_prov_acue_tipo_contrato = " AND cc.tipo_contrato_id = ".$data['tipo_contrato'];
				$where_aden_int_tipo_contrato = " AND cc.tipo_contrato_id = 0";

			}else if($data['tipo_contrato'] == 7){
				$where_cont_prov_acue_tipo_contrato = " AND cc.tipo_contrato_id = 0";
				$where_cont_int_tipo_contrato = " AND cc.tipo_contrato_id = ".$data['tipo_contrato'];

				$where_aden_prov_acue_tipo_contrato = " AND cc.tipo_contrato_id = 0";
				$where_aden_int_tipo_contrato = " AND cc.tipo_contrato_id = ".$data['tipo_contrato'];
			}
			
		}

		if (!Empty($data['estado']) && $data['estado'] != "0") {
			$where_cont_prov_acue_etapa = " AND cc.etapa_id = ".$data['estado'];
			$where_cont_int_etapa = " AND cc.etapa_id = ".$data['estado'];
			$where_aden_prov_acue_etapa = " AND cc.etapa_id = ".$data['estado'];
			$where_aden_int_etapa = " AND cc.etapa_id = ".$data['estado'];
		}

		if (!Empty($data['estado_aprobacion']) && $data['estado_aprobacion'] != "0") {

			if ($data['estado_aprobacion'] == 1) {
				$where_cont_prov_acue_estado_aprob = " AND (cc.check_gerencia_proveedor =1 and cc.fecha_atencion_gerencia_proveedor IS NOT NULL AND cc.aprobacion_gerencia_proveedor=1)";
				$where_cont_int_estado_aprob = " AND (cc.check_gerencia_interno = 1 and cc.fecha_atencion_gerencia_interno IS NOT NULL AND cc.aprobacion_gerencia_interno = 1)";

				$where_aden_prov_acue_estado_aprob = " AND (ca.aprobado_estado_id = 1)";
				$where_aden_int_estado_aprob = " AND (ca.aprobado_estado_id = 1)";
			} elseif ($data['estado_aprobacion'] == 2) {
				$where_cont_prov_acue_estado_aprob = " AND (cc.check_gerencia_proveedor =1 and cc.fecha_atencion_gerencia_proveedor IS NOT NULL AND cc.aprobacion_gerencia_proveedor=0)";
				$where_cont_int_estado_aprob = " AND (cc.check_gerencia_interno =1 and cc.fecha_atencion_gerencia_interno IS NOT NULL AND cc.aprobacion_gerencia_interno=0)";

				$where_aden_prov_acue_estado_aprob = " AND (ca.aprobado_estado_id = 0)";
				$where_aden_int_estado_aprob = " AND (ca.aprobado_estado_id = 0)";
			} elseif ($data['estado_aprobacion'] == 3) {
				$where_cont_prov_acue_estado_aprob = " AND (cc.check_gerencia_proveedor =1 and cc.fecha_atencion_gerencia_proveedor IS NULL AND cc.aprobacion_gerencia_proveedor=0)";
				$where_cont_int_estado_aprob = " AND (cc.check_gerencia_interno = 1 and cc.fecha_atencion_gerencia_interno IS NULL AND cc.aprobacion_gerencia_interno = 0)";

				$where_aden_prov_acue_estado_aprob = " AND (ca.requiere_aprobacion_id = 1)";
				$where_aden_int_estado_aprob = " AND (ca.requiere_aprobacion_id = 1)";
			}
		}

	
		if (!Empty($data['area']) && $data['area'] != "0") {
			$where_cont_prov_acue_area = " AND p.area_id = ".$data['area'];
			$where_cont_int_area = " AND p.area_id = ".$data['area'];
			$where_aden_prov_acue_area = " AND p.area_id = ".$data['area'];
			$where_aden_int_area = " AND p.area_id = ".$data['area'];
		}
		
		if (!Empty($data['desde']) && !Empty($data['hasta'])) {

			$where_cont_prov_acue_fecha = " AND DATE_FORMAT(cc.fecha_atencion_gerencia_proveedor, '%Y-%m-%d') BETWEEN '".$data['desde']."' AND  '".$data['hasta']."'";
			$where_cont_int_fecha = " AND DATE_FORMAT(cc.fecha_atencion_gerencia_interno, '%Y-%m-%d') BETWEEN '".$data['desde']."' AND  '".$data['hasta']."'";			

			$where_aden_prov_acue_fecha = " AND (DATE_FORMAT(ca.aprobado_el, '%Y-%m-%d') BETWEEN '".$data['desde']."' AND  '".$data['hasta']."' OR DATE_FORMAT(ca.cancelado_el, '%Y-%m-%d') BETWEEN '".$data['desde']."' AND  '".$data['hasta']."')";
			$where_aden_int_fecha = " AND (DATE_FORMAT(ca.aprobado_el, '%Y-%m-%d') BETWEEN '".$data['desde']."' AND  '".$data['hasta']."' OR DATE_FORMAT(ca.cancelado_el, '%Y-%m-%d') BETWEEN '".$data['desde']."' AND  '".$data['hasta']."')";
			
		}
		
		$query = "SELECT DISTINCT * FROM (
			SELECT  
			CONCAT('Adenda de ',ctc.nombre)  as tipo_contrato, 
			CONCAT('N° ', ca.codigo,' - ',co.sigla, cc.codigo_correlativo) AS codigo,
			a.nombre AS area,
			CONCAT(IFNULL(p.nombre, ''), ' ',  IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS solicitante,
			r.nombre AS empresa_suscribe,
			cc.detalle_servicio,
			DATE_FORMAT(ca.created_at, '%d-%m-%Y') AS fecha_solicitud,
			ca.created_at,
			cc.razon_social AS proveedor,
			cc.fecha_atencion_gerencia_proveedor as fecha_aprobacion,
			ca.dias_habiles,
			ces.nombre AS estado_solicitud,
			IF(cc.etapa_id = 5, 'Firmado','Pendiente') as estado,
			(CASE 
				WHEN (ca.requiere_aprobacion_id = 1 AND ca.aprobado_estado_id IS NULL) THEN 'Pendiente' 
				WHEN (ca.aprobado_estado_id = 1) THEN 'Aprobado' 
				WHEN (ca.aprobado_estado_id = 0) THEN 'Rechazado' 
				ELSE ''
			END) as estado_aprobacion, 
			ca.cancelado_id,
			DATE_FORMAT(cc.fecha_inicio, '%d-%m-%Y') AS fecha_inicio,
			DATE_FORMAT(cc.fecha_vencimiento_proveedor, '%d-%m-%Y') AS fecha_final,
			DATE_FORMAT(cc.fecha_suscripcion_proveedor, '%d-%m-%Y') AS fecha_suscripcion,
			cc.fecha_vencimiento_indefinida_id
			FROM  cont_adendas ca 
			INNER JOIN cont_contrato cc  ON cc.contrato_id  = ca.contrato_id 
			INNER JOIN cont_tipo_contrato ctc ON ctc.id = cc.tipo_contrato_id  
			LEFT JOIN cont_estado_solicitud ces ON ces.id = ca.estado_solicitud_id  
			LEFT JOIN cont_correlativo co ON cc.tipo_contrato_id = co.tipo_contrato
			INNER JOIN tbl_usuarios u ON ca.user_created_id = u.id
			INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
			INNER JOIN tbl_areas a ON p.area_id = a.id
			INNER JOIN tbl_razon_social r ON cc.empresa_suscribe_id = r.id AND r.status = 1
			WHERE ca.status = 1
			".$where_aden_prov_acue_tipo_contrato."
			".$where_aden_prov_acue_etapa."
			".$where_aden_prov_acue_estado_sol."
			".$where_aden_prov_acue_estado_aprob."
			".$where_aden_prov_acue_area."
			".$where_aden_prov_acue_fecha."

			UNION ALL
			
			SELECT  
			CONCAT('Adenda de ',ctc.nombre)  as tipo_contrato, 
			CONCAT('N° ', ca.codigo,' - ',co.sigla, cc.codigo_correlativo) AS codigo,
			a.nombre AS area,
			CONCAT(IFNULL(p.nombre, ''), ' ',  IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS solicitante,
			r.nombre AS empresa_suscribe,
			cc.detalle_servicio,
			DATE_FORMAT(ca.created_at, '%d-%m-%Y') AS fecha_solicitud,
			ca.created_at,
			r2.nombre  AS proveedor,
			cc.fecha_atencion_gerencia_proveedor as fecha_aprobacion,
			ca.dias_habiles,
			ces.nombre AS estado_solicitud,
			IF(cc.etapa_id = 5, 'Firmado','Pendiente') as estado,
			(CASE 
				WHEN (ca.requiere_aprobacion_id = 1 AND ca.aprobado_estado_id IS NULL) THEN 'Pendiente' 
				WHEN (ca.aprobado_estado_id = 1) THEN 'Aprobado' 
				WHEN (ca.aprobado_estado_id = 0) THEN 'Rechazado' 
				ELSE ''
			END) as estado_aprobacion,
			ca.cancelado_id,
			DATE_FORMAT(cc.fecha_inicio, '%d-%m-%Y') AS fecha_inicio,
			DATE_FORMAT(cc.fecha_vencimiento_proveedor, '%d-%m-%Y') AS fecha_final,
			DATE_FORMAT(cc.fecha_suscripcion_proveedor, '%d-%m-%Y') AS fecha_suscripcion,
			cc.fecha_vencimiento_indefinida_id
			FROM  cont_adendas ca 
			INNER JOIN cont_contrato cc  ON cc.contrato_id  = ca.contrato_id 
			INNER JOIN cont_tipo_contrato ctc ON ctc.id = cc.tipo_contrato_id  
			LEFT JOIN cont_estado_solicitud ces ON ces.id = ca.estado_solicitud_id  
			LEFT JOIN cont_correlativo co ON cc.tipo_contrato_id = co.tipo_contrato
			INNER JOIN tbl_usuarios u ON ca.user_created_id = u.id
			INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
			INNER JOIN tbl_areas a ON p.area_id = a.id
			INNER JOIN tbl_razon_social r ON cc.empresa_suscribe_id = r.id AND r.status = 1
			INNER JOIN tbl_razon_social r2 ON cc.empresa_grupo_at_2 = r2.id AND r2.status = 1
			WHERE ca.status = 1
			".$where_aden_int_tipo_contrato."
			".$where_aden_int_etapa."
			".$where_aden_int_estado_sol."
			".$where_aden_int_estado_aprob."
			".$where_aden_int_area."
			".$where_aden_int_fecha."

			UNION ALL
			
			SELECT ctc.nombre  as tipo_contrato, 
			CONCAT(co.sigla, cc.codigo_correlativo) AS codigo,
			a.nombre AS area,
			CONCAT(IFNULL(p.nombre, ''), ' ',  IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS solicitante,
			r.nombre AS empresa_suscribe,
			cc.detalle_servicio,
			DATE_FORMAT(cc.created_at, '%d-%m-%Y') AS fecha_solicitud,
			cc.created_at,
			cc.razon_social AS proveedor,
			cc.fecha_atencion_gerencia_proveedor as fecha_aprobacion,
			cc.dias_habiles,
			ces.nombre AS estado_solicitud,
			IF(cc.etapa_id = 5, 'Firmado','Pendiente') as estado,
			(CASE 
				WHEN (cc.check_gerencia_proveedor = 1 and cc.fecha_atencion_gerencia_proveedor IS NULL AND cc.aprobacion_gerencia_proveedor = 0) THEN 'Pendiente'	
				WHEN (cc.check_gerencia_proveedor = 1 and cc.fecha_atencion_gerencia_proveedor IS NOT NULL AND cc.aprobacion_gerencia_proveedor = 1) THEN 'Aprobado'
				WHEN (cc.check_gerencia_proveedor = 1 and cc.fecha_atencion_gerencia_proveedor IS NOT NULL AND cc.aprobacion_gerencia_proveedor = 0) THEN 'Rechazado' 
				ELSE '' 
			END) as estado_aprobacion, 
			cc.cancelado_id,
			DATE_FORMAT(cc.fecha_inicio, '%d-%m-%Y') AS fecha_inicio,
			DATE_FORMAT(cc.fecha_vencimiento_proveedor, '%d-%m-%Y') AS fecha_final,
			DATE_FORMAT(cc.fecha_suscripcion_proveedor, '%d-%m-%Y') AS fecha_suscripcion,
			cc.fecha_vencimiento_indefinida_id
			FROM  cont_contrato cc 
			INNER JOIN cont_tipo_contrato ctc ON ctc.id = cc.tipo_contrato_id  
			LEFT JOIN cont_estado_solicitud ces ON ces.id = cc.estado_solicitud  
			LEFT JOIN cont_correlativo co ON cc.tipo_contrato_id = co.tipo_contrato
			INNER JOIN tbl_usuarios u ON cc.user_created_id = u.id
			INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
			INNER JOIN tbl_areas a ON p.area_id = a.id
			INNER JOIN tbl_razon_social r ON cc.empresa_suscribe_id = r.id AND r.status = 1
			INNER JOIN cont_etapa ce ON ce.etapa_id = cc.etapa_id
			WHERE cc.status = 1 
			".$where_cont_prov_acue_tipo_contrato."
			".$where_cont_prov_acue_etapa."
			".$where_cont_prov_acue_estado_sol."
			".$where_cont_prov_acue_estado_aprob."
			".$where_cont_prov_acue_area."
			".$where_cont_prov_acue_fecha."

			UNION ALL
			
			SELECT ctc.nombre  as tipo_contrato, 
			CONCAT(co.sigla, cc.codigo_correlativo) AS codigo,
			a.nombre AS area,
			CONCAT(IFNULL(p.nombre, ''), ' ',  IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS solicitante,
			r.nombre AS empresa_suscribe,
			cc.detalle_servicio,
			DATE_FORMAT(cc.created_at, '%d-%m-%Y') AS fecha_solicitud,
			cc.created_at,
			r2.nombre AS proveedor,
			cc.fecha_atencion_gerencia_proveedor as fecha_aprobacion,
			cc.dias_habiles,
			ces.nombre AS estado_solicitud,
			IF(cc.etapa_id = 5, 'Firmado','Pendiente') as estado,
			(CASE 
				WHEN (cc.check_gerencia_interno = 1 and cc.fecha_atencion_gerencia_interno IS NULL AND cc.aprobacion_gerencia_interno = 0) THEN 'Pendiente'	
				WHEN (cc.check_gerencia_interno = 1 and cc.fecha_atencion_gerencia_interno IS NOT NULL AND cc.aprobacion_gerencia_interno = 1) THEN 'Aprobado'
				WHEN (cc.check_gerencia_interno = 1 and cc.fecha_atencion_gerencia_interno IS NOT NULL AND cc.aprobacion_gerencia_interno = 0) THEN 'Rechazado' 
				ELSE '' 
			END) as estado_aprobacion, 
			cc.cancelado_id,
			DATE_FORMAT(cc.fecha_inicio, '%d-%m-%Y') AS fecha_inicio,
			DATE_FORMAT(cc.fecha_vencimiento_proveedor, '%d-%m-%Y') AS fecha_final,
			DATE_FORMAT(cc.fecha_suscripcion_proveedor, '%d-%m-%Y') AS fecha_suscripcion,
			cc.fecha_vencimiento_indefinida_id
			FROM  cont_contrato cc 
			INNER JOIN cont_tipo_contrato ctc ON ctc.id = cc.tipo_contrato_id  
			LEFT JOIN cont_estado_solicitud ces ON ces.id = cc.estado_solicitud
			LEFT JOIN cont_correlativo co ON cc.tipo_contrato_id = co.tipo_contrato
			INNER JOIN tbl_usuarios u ON cc.user_created_id = u.id
			INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
			INNER JOIN tbl_areas a ON p.area_id = a.id
			INNER JOIN tbl_razon_social r ON cc.empresa_suscribe_id = r.id AND r.status = 1
			INNER JOIN tbl_razon_social r2 ON cc.empresa_grupo_at_2 = r2.id AND r2.status = 1
			INNER JOIN cont_etapa ce ON ce.etapa_id = cc.etapa_id
			WHERE cc.status = 1 
			".$where_cont_int_tipo_contrato."
			".$where_cont_int_etapa."
			".$where_cont_int_estado_sol."
			".$where_cont_int_estado_aprob."
			".$where_cont_int_area."
			".$where_cont_int_fecha."
		) as x
		
		ORDER BY DATE_FORMAT(x.created_at, '%Y-%m-%d')  DESC";
	
		$list_query = $mysqli->query($query);
		$list = [];

		while ($li = $list_query->fetch_assoc()) {
			$estado_solicitud = $li['estado_solicitud'] == "" ? 'Pendiente':$li['estado_solicitud'];
			array_push($list,array(
				"tipo_contrato" => $li['tipo_contrato'],
				"codigo"=> $li['codigo'],
				"area"=> $li['area'],
				"solicitante"=> $li['solicitante'],
				"empresa_suscribe"=> $li['empresa_suscribe'],
				"detalle_servicio_resumen"=>replace_invalid_caracters($li['detalle_servicio']),
				"detalle_servicio"=> replace_invalid_caracters($li['detalle_servicio']),
				"fecha_solicitud"=> $li['fecha_solicitud'],
				"estado"=> $li['estado'],
				"estado_solicitud"=> $li["cancelado_id"] == 1 ? 'Cancelado' : $estado_solicitud,
				"estado_aprobacion"=> $li['estado_aprobacion'],
				"proveedor"=> $li['proveedor'],
				"dias_habiles"=> $li['dias_habiles'],
				"fecha_inicio"=> $li['fecha_inicio'],
				"fecha_final"=> $li['fecha_vencimiento_indefinida_id'] == 1 ? 'Indefinida':$li['fecha_final'],
				"fecha_suscripcion"=> $li['fecha_suscripcion'],
			));
		}

		$result["status"]  = 200;
		$result["message"] = "Datos obtenidos de gestion.";
		$result["result"]  = $list;
		echo json_encode($result);
		exit();
		
	} catch (\Throwable $e) {
		echo $e;
		exit();
	}


}
?>