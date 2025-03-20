<?php
date_default_timezone_set("America/Lima");

$result=array();
include("db_connect.php");
include("sys_login.php");

include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';

require_once '/var/www/html/env.php';
include '/var/www/html/sys/envio_correos.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_personal_segun_area") {

	$area_id = $_POST["area_id"];

	$query = "SELECT u.id, concat( IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, ''),' ', IFNULL(p.apellido_materno, '')) AS nombre_completo
	FROM tbl_personal_apt p
	INNER JOIN tbl_usuarios u ON p.id = u.personal_id";
	$query .= " WHERE p.area_id = " . $area_id . " AND p.estado = 1 AND u.estado = 1";
	$query .= " ORDER BY nombre_completo ASC";
	
	$list_query = $mysqli->query($query);
	$list = array();
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	
	if(count($list) == 0){
		$result["http_code"] = 400;
		$result["result"] = "El área no existe.";
	} elseif (count($list) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $list;
	} else {
		$result["http_code"] = 400;
		$result["result"] = "El área no existe.";
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_abogados") {

	$query = "SELECT  u.id ,
	CONCAT(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, ''),' ', IFNULL(p.apellido_materno, '')) AS nombre 
	FROM 
		tbl_usuarios u
		INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		INNER JOIN tbl_cargos AS c ON c.id = p.cargo_id
	WHERE 
		u.estado = 1
		AND p.estado = 1
		AND p.area_id IN (33)
	ORDER BY 
		p.nombre ASC,
		p.apellido_paterno ASC";
	$list_query = $mysqli->query($query);
	
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_departamentos") {
	$query = "SELECT nombre, cod_depa AS id";
	$query .= " FROM tbl_ubigeo";
	$query .= " WHERE cod_prov = '00' AND cod_dist = '00' AND estado = '1'";
	$query .= " ORDER BY nombre ASC";
	
	$list_query = $mysqli->query($query);
	$list = array();
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	
	if(count($list) == 0){
		$result["http_code"] = 400;
		$result["result"] = "El departamento no existe.";
	} elseif (count($list) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $list;
	} else {
		$result["http_code"] = 400;
		$result["result"] = "El departamento no existe.";
	}
}


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_provincias_segun_departamento") {

	$departamento_id = $_POST["departamento_id"];

	$query = "SELECT nombre,cod_prov AS id";
	$query .= " FROM tbl_ubigeo";
	$query .= " WHERE cod_depa = " . $departamento_id . " AND cod_dist = '00' AND cod_prov != '00' AND estado = '1'";
	$query .= " ORDER BY nombre ASC";
	
	$list_query = $mysqli->query($query);
	$list = array();
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	
	if(count($list) == 0){
		$result["http_code"] = 400;
		$result["result"] = "El departamento no existe.";
	} elseif (count($list) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $list;
	} else {
		$result["http_code"] = 400;
		$result["result"] = "El departamento no existe.";
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_distritos_segun_provincia") {

	$departamento_id = $_POST["departamento_id"];
	$provincia_id = $_POST["provincia_id"];

	$query = "SELECT nombre,cod_dist AS id";
	$query .= " FROM tbl_ubigeo";
	$query .= " WHERE cod_prov = '" . $provincia_id . "' ";
	$query .= " AND cod_depa = '" . $departamento_id . "' ";
	$query .= " AND cod_dist != '00' ";
	$query .= " AND estado = '1'";
	$query .= " ORDER BY nombre ASC";
	
	$list_query = $mysqli->query($query);
	$list = array();
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	
	if(count($list) == 0){
		$result["http_code"] = 400;
		$result["result"] = "La provincia no existe.";
	} elseif (count($list) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $list;
	} else {
		$result["http_code"] = 400;
		$result["result"] = "La provincia no existe.";
	}
}
 


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_contrato_proveedor_por_id") {

	$id_contrato_proveedor = $_POST["id_contrato_proveedor"];

	$list_emp_cont = $mysqli->query("SELECT tpg.valor FROM tbl_parametros_generales tpg WHERE tpg.codigo = 'empresa_contacto_de_contratos' AND tpg.estado = 1 LIMIT 1");
	$row_emp_cont = $list_emp_cont->fetch_assoc();
	$valor_empresa_contacto = isset($row_emp_cont['valor']) && $row_emp_cont['valor'] == 'Kurax' ? '(Kurax)':'(AT)';

	$query_datos_generales = $mysqli->query("
	SELECT 
		c.empresa_suscribe_id,
		r.nombre AS empresa_suscribe,
		c.observaciones,
		c.persona_contacto_proveedor,
		c.user_created_id,
		CONCAT(IFNULL(p.nombre, ''),' ',IFNULL(p.apellido_paterno, ''),' ',IFNULL(p.apellido_materno, '')) AS user_created,
		c.created_at
	FROM
		cont_contrato c
		INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id
		INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
		INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
	WHERE 
		c.contrato_id = $id_contrato_proveedor
	");

	$row = $query_datos_generales->fetch_assoc();
	$empresa_suscribe = $row["empresa_suscribe"];
	$persona_contacto_proveedor = $row["persona_contacto_proveedor"];

	$html = '';
	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="h4"><b>DATOS GENERALES</b></div>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="form-group">';
	$html .= '<table class="table table-bordered table-hover">';

	$html .= '<tr>';
	$html .= '<td style="width: 50%;"><b>Empresa Contratante</b></td>';
	$html .= '<td>' . $empresa_suscribe . '</td>';
	$html .= '<td style="width: 75px;">';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_agente_solicitud_editar_campo_adenda(\'Datos generales\',\'cont_contrato\',\'empresa_suscribe_id\',\'Empresa Contratante\',\'select_option\',\'' . $empresa_suscribe . '\',\'obtener_empresa_at\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Persona Contacto '.$valor_empresa_contacto.'</b></td>';
	$html .= '<td>' . $persona_contacto_proveedor . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_agente_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_contrato\',\'persona_contacto_proveedor\',\'Persona Contacto '.$valor_empresa_contacto.'\',\'varchar\',\'' . $persona_contacto_proveedor . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '</table>';
	$html .= '</div>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<br>';
	$html .= '</div>';

	$query = "
	SELECT 
		c.ruc,
		c.razon_social,
		c.vigencia,
		c.dni_representante,
		c.nombre_representante,
		c.persona_contacto_proveedor,
		c.detalle_servicio,
		c.alcance_servicio,
		c.tipo_terminacion_anticipada_id,
		t.nombre AS tipo_terminacion_anticipada,
		c.terminacion_anticipada,
		c.observaciones,
		c.periodo_numero,
		p.nombre AS periodo_anio_mes,
		c.periodo_numero,
		c.fecha_inicio
	FROM 
		cont_contrato c
		INNER JOIN cont_periodo p ON c.periodo = p.id
		LEFT JOIN cont_tipo_terminacion_anticipada t ON c.tipo_terminacion_anticipada_id = t.id
	WHERE 
		c.tipo_contrato_id = 2 
		AND c.contrato_id = $id_contrato_proveedor
	";

	$sel_query = $mysqli->query($query);
	$row = $sel_query->fetch_assoc();

	$ruc = $row["ruc"];
	$razon_social = $row["razon_social"];
	$dni_representante = $row["dni_representante"];
	$nombre_representante = $row["nombre_representante"];

	$detalle_servicio = $row["detalle_servicio"];

	$periodo_numero = $row["periodo_numero"];
	$periodo_anio_mes = $row["periodo_anio_mes"];
	
	//$fecha_inicio = $row["fecha_inicio"];

	$date = date_create($row["fecha_inicio"]);
	$fecha_inicio = date_format($date, "Y-m-d");

	$alcance_servicio = $row["alcance_servicio"];
	$tipo_terminacion_anticipada_id = $row["tipo_terminacion_anticipada_id"];
	$tipo_terminacion_anticipada = $row["tipo_terminacion_anticipada"];
	$terminacion_anticipada = $row["terminacion_anticipada"];
	$observaciones = $row["observaciones"];

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="h4"><b>DATOS DEL PROVEEDOR</b></div>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="form-group">';
	$html .= '<table class="table table-bordered table-hover">';

	$html .= '<tr>';
	$html .= '<td style="width: 50%;"><b>Número de RUC</b></td>';
	$html .= '<td>' . $ruc . '</td>';
	$html .= '<td style="width: 75px;">';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_agente_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_contrato\',\'ruc\',\'Número de RUC\',\'varchar\',\'' . $ruc . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Razón Social</b></td>';
	$html .= '<td>' . $razon_social . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_agente_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_contrato\',\'razon_social\',\'Razón Social\',\'varchar\',\'' . $razon_social . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '</table>';
	$html .= '</div>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<br>';
	$html .= '</div>';
	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="h4"><b>DATOS DEL PROVEEDOR - REPRESENTANTE LEGAL</b> ';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_agente_proveedor_modal_ap('.$id_contrato_proveedor.',\'Nuevo\')">';
	$html .= '<span class="fa fa-edit"></span> Agregar Representante Legal';
	$html .= '</a>';
	
	$html .= '</div>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="form-group">';
	$html .= '<table class="table table-bordered table-hover">';

	$sel_query = $mysqli->query("
	SELECT 
		rl.id, 
		rl.dni_representante, 
		rl.nombre_representante, 
		rl.nro_cuenta_detraccion,
		rl.id_banco, 
		b.nombre as banco_representante, 
		rl.nro_cuenta, 
		rl.nro_cci, 
		rl.vigencia_archivo_id,
		rl.dni_archivo_id
	FROM 
		cont_representantes_legales rl
		LEFT JOIN tbl_bancos b on b.id = rl.id_banco
	WHERE 
		rl.contrato_id = $id_contrato_proveedor"
	);

	$c = 0;
	$id_representante_legal = 0;

	$row_count = $sel_query->num_rows;

	if ($row_count == 0) {

	$html .= '<tr>';
	$html .= '<td style="width: 50%;"><b>DNI del representante legal</b></td>';
	$html .= '<td>' . $dni_representante . '</td>';
	$html .= '<td style="width: 75px;">';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_agente_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_contrato\',\'dni_representante\',\'DNI del representante legal\',\'varchar\',\'' . $dni_representante . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Nombre completo del representante legal</b></td>';
	$html .= '<td>' . $nombre_representante . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_agente_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_contrato\',\'nombre_representante\',\'Nombre completo del representante legal\',\'varchar\',\'' . $nombre_representante . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	} elseif ($row_count > 0) {
		while($sel=$sel_query->fetch_assoc()){
			$c = $c + 1; 
			$id_representante_legal = $sel["id"];

	$html .= '<tr>';
	$html .= '<td style="width: 50%;"><b>DNI del representante legal</b></td>';
	$html .= '<td>' . $sel["dni_representante"] . '</td>';
	$html .= '<td style="width: 75px;">';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_agente_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_representantes_legales\',\'dni_representante\',\'DNI del representante legal\',\'varchar\',\'' . $sel["dni_representante"] . '\',\'\',\'' . $sel["id"] . '\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Nombre completo del representante legal</b></td>';
	$html .= '<td>' . $sel["nombre_representante"] . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_agente_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_representantes_legales\',\'nombre_representante\',\'Nombre completo del representante legal\',\'varchar\',\'' . $sel["nombre_representante"] . '\',\'\',\'' . $sel["id"] . '\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Número de cuenta de detracción (Banco de la Nación)</b></td>';
	$html .= '<td>' . $sel["nro_cuenta_detraccion"] . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_agente_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_representantes_legales\',\'nro_cuenta_detraccion\',\'Número de cuenta de detracción (Banco de la Nación)\',\'varchar\',\'' . $sel["nro_cuenta_detraccion"] . '\',\'\',\'' . $sel["id"] . '\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Banco</b></td>';
	$html .= '<td>' . $sel["banco_representante"] . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_agente_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_representantes_legales\',\'id_banco\',\'Banco\',\'select_option\',\'' . $sel["banco_representante"] . '\',\'obtener_banco\',\'' . $sel["id"] . '\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Número de cuenta</b></td>';
	$html .= '<td>' . $sel["nro_cuenta"] . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_agente_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_representantes_legales\',\'nro_cuenta\',\'Número de cuenta\',\'varchar\',\'' . $sel["nro_cuenta"] . '\',\'\',\'' . $sel["id"] . '\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Número CCI</b></td>';
	$html .= '<td>' . $sel["nro_cci"] . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_agente_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_representantes_legales\',\'nro_cci\',\'Número CCI\',\'varchar\',\'' . $sel["nro_cci"] . '\',\'\',\'' . $sel["id"] . '\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

		}
	}

	$html .= '</table>';
	$html .= '</div>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<br>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="h4"><b>CONDICIONES COMERCIALES x</b></div>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="h4">1) Objeto del Contrato:</div>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="form-group">';
	$html .= '<table class="table table-bordered table-hover">';

	$html .= '<tr>';
	$html .= '<td>' . $detalle_servicio . '</td>';
	$html .= '<td style="width: 75px;">';
	$html .= '<a class="btn btn-success btn-xs"';
	$html .= 'onclick="sec_contrato_nuevo_agente_solicitud_editar_campo_adenda(\'Objeto del Contrato\',\'cont_contrato\',\'detalle_servicio\',\'Detalle de servicio a contratar\',\'varchar\',\'' . $detalle_servicio . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '</table>';
	$html .= '</div>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<br>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="h4">2) Plazo del Contrato:</div>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="form-group">';
	$html .= '<table class="table table-bordered table-hover">';

	$html .= '<tr>';
	$html .= '<td style="width: 50%;"><b>Periodo - Número</b></td>';
	$html .= '<td>' . $periodo_numero . '</td>';
	$html .= '<td style="width: 75px;">';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_agente_solicitud_editar_campo_adenda(\'Plazo del Contrato\',\'cont_contrato\',\'periodo_numero\',\'Periodo (Número)\',\'int\',\'' . $periodo_numero . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td style="width: 50%;"><b>Periodo - Año o Mes</b></td>';
	$html .= '<td>' . $periodo_anio_mes . '</td>';
	$html .= '<td style="width: 75px;">';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_agente_solicitud_editar_campo_adenda(\'Plazo del Contrato\',\'cont_contrato\',\'periodo\',\'Periodo (Año o Mes)\',\'select_option\',\'' . $periodo_anio_mes . '\',\'obtener_periodo\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Fecha de inicio</b></td>';
	$html .= '<td>' . $fecha_inicio . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_agente_solicitud_editar_campo_adenda(\'Plazo del Contrato\',\'cont_contrato\',\'fecha_inicio\',\'Fecha de inicio\',\'date\',\'' . $fecha_inicio . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '</table>';
	$html .= '</div>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<br>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="h4">3) Contraprestación: ';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_agente_contraprestacion_modal_ap('.$id_contrato_proveedor.',\'Nuevo\')">';
	$html .= '<span class="fa fa-edit"></span> Agregar Contraprestación';
	$html .= '</a>';
	$html .= '</div>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="form-group">';
	$html .= '<table class="table table-bordered table-hover">';

	$sql_contraprestacion = "
	SELECT 
		c.id,
		c.moneda_id,
		m.nombre AS tipo_moneda,
		m.simbolo AS tipo_moneda_simbolo,
		c.subtotal,
		c.igv,
		c.monto,
		c.forma_pago_detallado,
		c.tipo_comprobante_id,
		t.nombre AS tipo_comprobante,
		c.plazo_pago
	FROM 
		cont_contraprestacion c
		INNER JOIN tbl_moneda m ON c.moneda_id = m.id
		INNER JOIN cont_tipo_comprobante t ON c.tipo_comprobante_id = t.id
	WHERE 
		c.contrato_id = $id_contrato_proveedor
		AND c.status = 1
	";

	$query = $mysqli->query($sql_contraprestacion);
	$row_count = $query->num_rows;

	if ($row_count > 0) {
		$contador_contraprestacion = 1;
		while($sel = $query->fetch_assoc()){
			$contraprestacion_id = $sel["id"];
			$tipo_moneda = $sel["tipo_moneda"];
			$tipo_moneda_simbolo = $sel["tipo_moneda_simbolo"];
			$subtotal = $tipo_moneda_simbolo.' '.number_format($sel["subtotal"], 2, '.', ',');
			$igv = $tipo_moneda_simbolo.' '.number_format($sel["igv"], 2, '.', ',');
			$monto = $tipo_moneda_simbolo.' '.number_format($sel["monto"], 2, '.', ',');
			$forma_pago_detallado = $sel["forma_pago_detallado"];
			$tipo_comprobante = $sel["tipo_comprobante"];
			$plazo_pago = $sel["plazo_pago"];

	$html .= '<tr>';
	$html .= '<td style="width: 50%;"><b>Tipo de moneda</b></td>';
	$html .= '<td>' . $tipo_moneda . '</td>';
	$html .= '<td style="width: 75px;">';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_agente_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contraprestacion\',\'moneda_id\',\'Tipo de moneda\',\'select_option\',\'' . $tipo_moneda . '\',\'obtener_monedas\',\'' . $contraprestacion_id . '\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td style="width: 50%;"><b>Subtotal</b></td>';
	$html .= '<td>' . $subtotal . '</td>';
	$html .= '<td style="width: 75px;">';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_agente_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contraprestacion\',\'subtotal\',\'Subtotal\',\'decimal\',\'' . $subtotal . '\',\'\',\'' . $contraprestacion_id . '\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td style="width: 50%;"><b>IGV</b></td>';
	$html .= '<td>' . $igv . '</td>';
	$html .= '<td style="width: 75px;">';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_agente_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contraprestacion\',\'igv\',\'IGV\',\'decimal\',\'' . $igv . '\',\'\',\'' . $contraprestacion_id . '\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td style="width: 50%;"><b>Monto Bruto</b></td>';
	$html .= '<td>' . $monto . '</td>';
	$html .= '<td style="width: 75px;">';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_agente_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contraprestacion\',\'monto\',\'Monto Bruto\',\'decimal\',\'' . $monto . '\',\'\',\'' . $contraprestacion_id . '\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Tipo de comprobante a emitir</b></td>';
	$html .= '<td>' . $tipo_comprobante . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_agente_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contraprestacion\',\'tipo_comprobante_id\',\'Tipo de comprobante a emitir\',\'select_option\',\'' . $tipo_comprobante . '\',\'obtener_tipo_comprobante\',\'' . $contraprestacion_id . '\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Plazo de Pago</b></td>';
	$html .= '<td>' . $plazo_pago . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_agente_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contraprestacion\',\'plazo_pago\',\'Plazo de Pago\',\'varchar\',\'' . $plazo_pago . '\',\'\',\'' . $contraprestacion_id . '\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Forma de pago</b></td>';
	$html .= '<td>' . $forma_pago_detallado . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_agente_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contraprestacion\',\'forma_pago_detallado\',\'Forma de pago\',\'varchar\',\'' . $forma_pago_detallado . '\',\'\',\'' . $contraprestacion_id . '\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

		}
	} elseif ($row_count == 0) {
		$sel_query = $mysqli->query("
		SELECT 
			c.moneda_id,
			m.nombre AS tipo_moneda,
			m.simbolo AS tipo_moneda_simbolo,
			c.monto,
			c.forma_pago_id,
			f.nombre AS forma_pago,
			c.tipo_comprobante_id,
			t.nombre AS tipo_comprobante,
			c.plazo_pago
		FROM 
			cont_contrato c
			INNER JOIN tbl_moneda m ON c.moneda_id = m.id
			INNER JOIN cont_forma_pago f ON c.forma_pago_id = f.id
			INNER JOIN cont_tipo_comprobante t ON c.tipo_comprobante_id = t.id
		WHERE 
			c.tipo_contrato_id = 2 AND c.contrato_id = $id_contrato_proveedor
		");

		$sel = $sel_query->fetch_assoc();
		$tipo_moneda = $sel["tipo_moneda"];
		$tipo_moneda_simbolo = $sel["tipo_moneda_simbolo"];
		$monto = $tipo_moneda_simbolo.' '.number_format($sel["monto"], 2, '.', ',');
		$forma_pago = $sel["forma_pago"];
		$tipo_comprobante = $sel["tipo_comprobante"];
		$plazo_pago = $sel["plazo_pago"];

	$html .= '<tr>';
	$html .= '<td style="width: 50%;"><b>Monto</b></td>';
	$html .= '<td>' . $monto . '</td>';
	$html .= '<td style="width: 75px;">';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_agente_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contrato\',\'monto\',\'Monto\',\'decimal\',\'' . $monto . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Forma de pago</b></td>';
	$html .= '<td>' . $forma_pago . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_agente_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contrato\',\'forma_pago\',\'Forma de pago\',\'select_option\',\'' . $forma_pago . '\',\'obtener_forma_pago\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Tipo de comprobante a emitir</b></td>';
	$html .= '<td>' . $tipo_comprobante . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_agente_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contrato\',\'tipo_comprobante\',\'Tipo de comprobante a emitir\',\'select_option\',\'' . $tipo_comprobante . '\',\'obtener_tipo_comprobante\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Plazo de Pago</b></td>';
	$html .= '<td>' . $plazo_pago . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_agente_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contrato\',\'plazo_pago\',\'Plazo de Pago\',\'varchar\',\'' . $plazo_pago . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	}

	$html .= '</table>';
	$html .= '</div>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<br>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="h4">4) Alcance del servicio:</div>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="form-group">';
	$html .= '<table class="table table-bordered table-hover">';

	$html .= '<tr>';
	$html .= '<td>' . $alcance_servicio . '</td>';
	$html .= '<td style="width: 75px;">';
	$html .= '<a class="btn btn-success btn-xs"';
	$html .= 'onclick="sec_contrato_nuevo_agente_solicitud_editar_campo_adenda(\'Alcance del servicio\',\'cont_contrato\',\'alcance_servicio\',\'Alcance del servicio\',\'varchar\',\'' . $alcance_servicio . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '</table>';
	$html .= '</div>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<br>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="h4">5) Terminación Anticipada:</div>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="form-group">';
	$html .= '<table class="table table-bordered table-hover">';

	$html .= '<tr>';
	$html .= '<td>' . $tipo_terminacion_anticipada . '</td>';
	$html .= '<td style="width: 75px;">';
	$html .= '<a class="btn btn-success btn-xs"';
	$html .= 'onclick="sec_contrato_nuevo_agente_solicitud_editar_campo_adenda(\'Terminación Anticipada\',\'cont_contrato\',\'tipo_terminacion_anticipada_id\',\'Tipo de Terminación Anticipada\',\'select_option\',\'' . $tipo_terminacion_anticipada . '\',\'obtener_tipo_terminacion_anticipada\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	if ($tipo_terminacion_anticipada_id == "1") {
		$html .= '<tr>';
		$html .= '<td>' . $terminacion_anticipada . '</td>';
		$html .= '<td style="width: 75px;">';
		$html .= '<a class="btn btn-success btn-xs"';
		$html .= 'onclick="sec_contrato_nuevo_agente_solicitud_editar_campo_adenda(\'Terminación Anticipada\',\'cont_contrato\',\'terminacion_anticipada\',\'Terminación Anticipada - Detalle\',\'varchar\',\'' . $terminacion_anticipada . '\',\'\',\'\');">';
		$html .= '<span class="fa fa-edit"></span> Editar';
		$html .= '</a>';
		$html .= '</td>';
		$html .= '</tr>';
	}

	$html .= '</table>';
	$html .= '</div>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<br>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="h4">6) Observaciones:</div>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="form-group">';
	$html .= '<table class="table table-bordered table-hover">';

	$html .= '<tr>';
	$html .= '<td>' . $observaciones . '</td>';
	$html .= '<td style="width: 75px;">';
	$html .= '<a class="btn btn-success btn-xs"';
	$html .= 'onclick="sec_contrato_nuevo_agente_solicitud_editar_campo_adenda(\'Observaciones\',\'cont_contrato\',\'observaciones\',\'Observaciones\',\'varchar\',\'' . $observaciones . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '</table>';
	$html .= '</div>';
	$html .= '</div>';

	// if($mysqli->error){
	// 	$result["consulta_error"] = $mysqli->error;
	// }
	
	// if(count($list) == 0){
	// 	$result["http_code"] = 400;
	// 	$result["result"] = "El contrato no existe.";
	// } elseif (count($list) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $html;
	// } else {
	// 	$result["http_code"] = 400;
	// 	$result["result"] = "El contrato no existe.";
	// }
}


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_propietario_por_id") {

	$id_persona = $_POST['nombre_o_numdocu'];
	$sql = "SELECT 
	id, 
	tipo_persona_id, 
	nombre, 
	tipo_docu_identidad_id, 
	num_docu, 
	num_ruc,
	direccion, 
	representante_legal, 
	num_partida_registral, 
	contacto_nombre, 
	contacto_telefono, 
	contacto_email 
	FROM cont_persona 
	WHERE id = '" . $id_persona . "'";

	$query = $mysqli->query($sql);
	$list = array();
	while ($li = $query->fetch_assoc()) {
		$list[] = $li;
	}
	

	$query_val = $mysqli->query("SELECT c.contrato_id, p.id AS persona_id, pr.propietario_id
	FROM cont_contrato AS c
	INNER JOIN cont_propietario pr ON pr.contrato_id = c.contrato_id
	INNER JOIN cont_persona p ON pr.persona_id = p.id
	WHERE c.status = 1 AND p.id = ".$id_persona);

	if($query_val->num_rows > 0){
		$result["http_code"] = 400;
		$result["status"] = "No se puede editar este propietario, porque ya tiene asignado un contrato.";	
		echo json_encode($result);
		exit();
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_propietario") {

	$nombre_o_numdocu = $_POST["nombre_o_numdocu"];
	$tipo_busqueda = $_POST["tipo_busqueda"];
	$tipo_solicitud = $_POST["tipo_solicitud"];
	$contador_array_ids = 0;

	if($tipo_busqueda == '3' || $tipo_busqueda == '5'){
		$data = json_decode($nombre_o_numdocu);
		$ids = '';

		foreach ($data as $value) {
			if ($contador_array_ids > 0) {
				$ids .= ',';
			}
			$ids .= $value;			
			$contador_array_ids++;
		}

		if($contador_array_ids == 0){
			$ids = 0;
		}
	}

	$html = '<table class="table table-bordered table-hover no-mb" style="font-size:11px; margin-top: 10px;">';
	$html .= '<thead>';

	$html .= '<tr>';

	if ($tipo_busqueda == '1' || $tipo_busqueda == '2') {
		$html .= '<th>#</th>';
		$html .= '<th>Nombre / Razón Social</th>';
		$html .= '<th>DNI o Pasaporte</th>';
		$html .= '<th>RUC</th>';
		$html .= '<th>Domicilio</th>';
		$html .= '<th>Opciones</th>';
	} else if ($tipo_busqueda == '3') {
		$html .= '<th colspan=5 style="text-align:center;">DATOS DEL PROPIETARIO</th>';
		$html .= '<th colspan=2 style="text-align:center;">DATOS EN EL CASO DE SER EMPRESA</th>';
		$html .= '<th colspan=3 style="text-align:center;">DATOS DEL CONTACTO</th>';
		$html .= '<th style="text-align:center;"></th>';
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= '<th>#</th>';
		$html .= '<th>Nombre</th>';
		$html .= '<th>N.° de DNI/Pasaporte/Carnet Ext.</th>';
		$html .= '<th>N.° de RUC</th>';
		$html .= '<th>Domicilio</th>';
		$html .= '<th>Representante Legal</th>';
		$html .= '<th>N° Partida Registral</th>';
		$html .= '<th>Nombre</th>';
		$html .= '<th>Teléfono</th>';
		$html .= '<th>Email</th>';
		$html .= '<th>Opciones</th>';
	} else if ($tipo_busqueda == '5') {
		$html .= '<th>#</th>';
		$html .= '<th>Nombre / Razón Social</th>';
		$html .= '<th>DNI o Pasaporte</th>';
		$html .= '<th>RUC</th>';
		$html .= '<th>Opciones</th>';
	}

	$html .= '</tr>';

	$html .= '</thead>';
	$html .= '<tbody>';

	$sql = "SELECT 
	id, 
	tipo_persona_id, 
	nombre, 
	tipo_docu_identidad_id, 
	num_docu, 
	num_ruc,
	direccion, 
	representante_legal, 
	num_partida_registral, 
	contacto_nombre, 
	contacto_telefono, 
	contacto_email 
	FROM cont_persona ";
	if($tipo_busqueda == '3' || $tipo_busqueda == '5'){
		$sql .= " WHERE id IN(" . $ids . ") ";
	} else if ($tipo_busqueda == '1') {
		$sql .= " WHERE nombre like '" . $nombre_o_numdocu . "%' ";
	} else if ($tipo_busqueda == '4'){
		$sql .= " WHERE id = '" . $nombre_o_numdocu . "'";
	} else {
		$sql .= " WHERE num_docu like '" . $nombre_o_numdocu . "%'";
	}

	$query = $mysqli->query($sql);

	if ($tipo_busqueda == '4'){
		$list = array();
		while ($li = $query->fetch_assoc()) {
			$list[] = $li;
		}
	}

	$row_count = $query->num_rows;

	if ($row_count > 0) {
		$contador = 1;

		while ($row = $query->fetch_assoc()) {
			$html .= '<tr>';
			$html .= '<td>' . $contador . '</td>';
			$html .= '<td>' . $row["nombre"] . '</td>';
			$html .= '<td>' . $row["num_docu"] . '</td>';
			$html .= '<td>' . $row["num_ruc"] . '</td>';

			if ($tipo_busqueda != '5') {
				$html .= '<td>' . $row["direccion"] . '</td>';
			}

			if ($tipo_busqueda == '1' || $tipo_busqueda == '2') {
				if ($tipo_solicitud == 'adenda') {
					$html .= '<td>';
					$html .= '<a class="btn btn-success btn-xs" onclick="sec_contrato_nuevo_agente_asignar_otros_detalles_a_la_adenda(\'propietario\',' . $row["id"] . ', \'modalBuscarPropietario\')">';
					$html .= '<i class="fa fa-plus"></i> Agregar a la adenda como propietario';
					$html .= '</a>';
					$html .= '</td>';
				} else {
					$html .= '<td>';
					$html .= '<a class="btn btn-success btn-xs" onclick="sec_contrato_nuevo_agente_asignar_propietario_al_contrato_agente(' . $row["id"] . ', \'modalBuscar\')">';
					$html .= '<i class="fa fa-plus"></i> Agregar como propietario';
					$html .= '</a>';
					$html .= '</td>';
				}
			} else if ($tipo_busqueda == '3') {
				$html .= '<td>' . $row["representante_legal"] . '</td>';
				$html .= '<td>' . $row["num_partida_registral"] . '</td>';
				$html .= '<td>' . $row["contacto_nombre"] . '</td>';
				$html .= '<td>' . $row["contacto_telefono"] . '</td>';
				$html .= '<td>' . $row["contacto_email"] . '</td>';
				$html .= '<td>';
				$html .= '<a class="btn btn-success btn-xs" data-toggle="tooltip" data-placement="top" title="Editar" style="width: 24px;" onclick="sec_contrato_nuevo_agente_editar_propietario(' . $row["id"] . ')">';
				$html .= '<i class="fa fa-edit"></i>';
				$html .= '</a>';
				$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar del contrato" style="width: 24px;" onclick="sec_contrato_nuevo_agente_eliminar_propietario(' . $row["id"] . ')">';
				$html .= '<i class="fa fa-trash"></i>';
				$html .= '</a>';
				$html .= '</td>';
			} else if ($tipo_busqueda == '5') {
				$html .= '<td>';
				$html .= '<a class="btn btn-success btn-xs" onclick="sec_contrato_nuevo_agente_asignar_pre_beneficiario_al_contrato(' . $row["id"] . ', \'modalBuscar\')">';
				$html .= '<i class="fa fa-plus"></i> Agregar al propietario como beneficiario';
				$html .= '</a>';
				$html .= '</td>';
			}

			$html .= '</tr>';

			$contador++;
		}
	}

	if ($row_count == 0 && $tipo_busqueda == '1') {
		$html .= '<tr>';
		$html .= '<td colspan=5 style="text-align:center;">';
		$html .= 'No existe un propietario con el nombre ' . $nombre_o_numdocu . ' en nuestra base de datos';
		$html .= '</td>';
		$html .= '</tr>';
	}

	if ($row_count == 0 && $tipo_busqueda == '2') {
		$html .= '<tr>';
		$html .= '<td colspan=5 style="text-align:center;">';
		$html .= 'No existe un propietario con el número de documento ' . $nombre_o_numdocu . ' en nuestra base de datos';
		$html .= '</td>';
		$html .= '</tr>';
	}

	if ($tipo_busqueda == '3') {
		$html .= '<tr>';
		$html .= '<td colspan=11 style="text-align:center;">';
		$html .= '<button type="button" class="btn btn-success" onclick="sec_contrato_nuevo_agente_buscar_propietario_modal(\'arrendamiento\')" style="width: 180px;">';
		$html .= '<i class="icon fa fa-plus"></i>';
		$html .= '<span id="demo-button-text"> Agregar propietario</span>';
		$html .= '</button>';
		$html .= '</td>';
		$html .= '</tr>';
	}

	$html .= '</tbody>';
	$html .= '</table>';

	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	if ($tipo_busqueda == '4'){
		$result["result"] = $list;
	} else {
		$result["result"] = $html;
	}
}


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_propietario_ca") {

	$nombre_o_numdocu = $_POST["nombre_o_numdocu"];
	$tipo_busqueda = $_POST["tipo_busqueda"];
	$tipo_solicitud = $_POST["tipo_solicitud"];
	$contador_array_ids = 0;

	if($tipo_busqueda == '3' || $tipo_busqueda == '5'){
		$data = json_decode($nombre_o_numdocu);
		$ids = '';

		foreach ($data as $value) {
			if ($contador_array_ids > 0) {
				$ids .= ',';
			}
			$ids .= $value;			
			$contador_array_ids++;
		}

		if($contador_array_ids == 0){
			$ids = 0;
		}
	}

	$html = '<table class="table table-bordered table-hover no-mb" style="font-size:11px; margin-top: 10px;">';
	$html .= '<thead>';

	$html .= '<tr>';

	if ($tipo_busqueda == '1' || $tipo_busqueda == '2') {
		$html .= '<th>#</th>';
		$html .= '<th>Nombre / Razón Social</th>';
		$html .= '<th>DNI o Pasaporte</th>';
		$html .= '<th>RUC</th>';
		$html .= '<th>Domicilio</th>';
		$html .= '<th>Opciones</th>';
	} else if ($tipo_busqueda == '3') {
		$html .= '<th colspan=5 style="text-align:center;">DATOS DEL PROPIETARIO</th>';
		$html .= '<th colspan=2 style="text-align:center;">DATOS EN EL CASO DE SER EMPRESA</th>';
		$html .= '<th colspan=3 style="text-align:center;">DATOS DEL CONTACTO</th>';
		$html .= '<th style="text-align:center;"></th>';
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= '<th>#</th>';
		$html .= '<th>Nombre</th>';
		$html .= '<th>N.° de DNI/Pasaporte/Carnet Ext.</th>';
		$html .= '<th>N.° de RUC</th>';
		$html .= '<th>Domicilio</th>';
		$html .= '<th>Representante Legal</th>';
		$html .= '<th>N° Partida Registral</th>';
		$html .= '<th>Nombre</th>';
		$html .= '<th>Teléfono</th>';
		$html .= '<th>Email</th>';
		$html .= '<th>Opciones</th>';
	} else if ($tipo_busqueda == '5') {
		$html .= '<th>#</th>';
		$html .= '<th>Nombre / Razón Social</th>';
		$html .= '<th>DNI o Pasaporte</th>';
		$html .= '<th>RUC</th>';
		$html .= '<th>Opciones</th>';
	}

	$html .= '</tr>';

	$html .= '</thead>';
	$html .= '<tbody>';

	$sql = "SELECT 
	id, 
	tipo_persona_id, 
	nombre, 
	tipo_docu_identidad_id, 
	num_docu, 
	num_ruc,
	direccion, 
	representante_legal, 
	num_partida_registral, 
	contacto_nombre, 
	contacto_telefono, 
	contacto_email 
	FROM cont_persona ";
	if($tipo_busqueda == '3' || $tipo_busqueda == '5'){
		$sql .= " WHERE id IN(" . $ids . ") ";
	} else if ($tipo_busqueda == '1') {
		$sql .= " WHERE nombre like '" . $nombre_o_numdocu . "%' ";
	} else if ($tipo_busqueda == '4'){
		$sql .= " WHERE id = '" . $nombre_o_numdocu . "'";
	} else if ($tipo_busqueda == '2'){
		$sql .= " WHERE num_ruc = '" . $nombre_o_numdocu . "'";
	} else {
		$sql .= " WHERE num_docu like '" . $nombre_o_numdocu . "%'";
	}

	$query = $mysqli->query($sql);

	if ($tipo_busqueda == '4'){
		$list = array();
		while ($li = $query->fetch_assoc()) {
			$list[] = $li;
		}
	}

	$row_count = $query->num_rows;

	if ($row_count > 0) {
		$contador = 1;

		while ($row = $query->fetch_assoc()) {
			$html .= '<tr>';
			$html .= '<td>' . $contador . '</td>';
			$html .= '<td>' . $row["nombre"] . '</td>';
			$html .= '<td>' . $row["num_docu"] . '</td>';
			$html .= '<td>' . $row["num_ruc"] . '</td>';

			if ($tipo_busqueda != '5') {
				$html .= '<td>' . $row["direccion"] . '</td>';
			}

			if ($tipo_busqueda == '1' || $tipo_busqueda == '2') {
				if ($tipo_solicitud == 'adenda') {
					$html .= '<td>';
					$html .= '<a class="btn btn-success btn-xs" onclick="sec_contrato_nuevo_agente_asignar_otros_detalles_a_la_adenda_ca(\'propietario\',' . $row["id"] . ', \'modalBuscarPropietario\')">';
					$html .= '<i class="fa fa-plus"></i> Agregar a la adenda como propietario';
					$html .= '</a>';
					$html .= '</td>';
				} else {
					$html .= '<td>';
					$html .= '<a class="btn btn-success btn-xs" onclick="sec_contrato_nuevo_agente_asignar_propietario_al_contrato(' . $row["id"] . ', \'modalBuscar\')">';
					$html .= '<i class="fa fa-plus"></i> Agregar como propietario';
					$html .= '</a>';
					$html .= '</td>';
				}
			} else if ($tipo_busqueda == '3') {
				$html .= '<td>' . $row["representante_legal"] . '</td>';
				$html .= '<td>' . $row["num_partida_registral"] . '</td>';
				$html .= '<td>' . $row["contacto_nombre"] . '</td>';
				$html .= '<td>' . $row["contacto_telefono"] . '</td>';
				$html .= '<td>' . $row["contacto_email"] . '</td>';
				$html .= '<td>';
				$html .= '<a class="btn btn-success btn-xs" data-toggle="tooltip" data-placement="top" title="Editar" style="width: 24px;" onclick="sec_contrato_nuevo_agente_editar_propietario_ca(' . $row["id"] . ')">';
				$html .= '<i class="fa fa-edit"></i>';
				$html .= '</a>';
				$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar del contrato" style="width: 24px;" onclick="sec_contrato_nuevo_agente_eliminar_propietario_agente(' . $row["id"] . ')">';
				$html .= '<i class="fa fa-trash"></i>';
				$html .= '</a>';
				$html .= '</td>';
			} else if ($tipo_busqueda == '5') {
				$html .= '<td>';
				$html .= '<a class="btn btn-success btn-xs" onclick="sec_contrato_nuevo_agente_asignar_pre_beneficiario_al_contrato(' . $row["id"] . ', \'modalBuscar\')">';
				$html .= '<i class="fa fa-plus"></i> Agregar al propietario como beneficiario';
				$html .= '</a>';
				$html .= '</td>';
			}

			$html .= '</tr>';

			$contador++;
		}
	}

	if ($row_count == 0 && $tipo_busqueda == '1') {
		$html .= '<tr>';
		$html .= '<td colspan=5 style="text-align:center;">';
		$html .= 'No existe un propietario con el nombre ' . $nombre_o_numdocu . ' en nuestra base de datos';
		$html .= '</td>';
		$html .= '</tr>';
	}

	if ($row_count == 0 && $tipo_busqueda == '2') {
		$html .= '<tr>';
		$html .= '<td colspan=5 style="text-align:center;">';
		$html .= 'No existe un propietario con el número de documento ' . $nombre_o_numdocu . ' en nuestra base de datos';
		$html .= '</td>';
		$html .= '</tr>';
	}

	if ($tipo_busqueda == '3') {
		$html .= '<tr>';
		$html .= '<td colspan=11 style="text-align:center;">';
		$html .= '<button type="button" class="btn btn-success" onclick="sec_contrato_nuevo_agente_buscar_propietario_modal_ca(\'agente\')" style="width: 180px;">';
		$html .= '<i class="icon fa fa-plus"></i>';
		$html .= '<span id="demo-button-text"> Agregar propietario</span>';
		$html .= '</button>';
		$html .= '</td>';
		$html .= '</tr>';
	}

	$html .= '</tbody>';
	$html .= '</table>';

	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	if ($tipo_busqueda == '4'){
		$result["result"] = $list;
	} else {
		$result["result"] = $html;
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_jefe_comercial") {
	$query = "SELECT u.id, concat( IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, ''),' ', IFNULL(p.apellido_materno, '')) AS nombre
	FROM tbl_personal_apt p
	INNER JOIN tbl_usuarios u ON p.id = u.personal_id
	WHERE p.area_id = 21 AND p.cargo_id = 16 AND p.estado = 1 AND u.estado = 1
	ORDER BY nombre ASC";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}




if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_propietario_existe") {

	$nombre_o_numdocu = $_POST["nombre_o_numdocu"];
 
 
	$sql = "SELECT 
	id, 
	tipo_persona_id, 
	nombre, 
	tipo_docu_identidad_id, 
	num_docu, 
	num_ruc,
	direccion, 
	representante_legal, 
	num_partida_registral, 
	contacto_nombre, 
	contacto_telefono, 
	contacto_email 
	FROM cont_persona WHERE num_docu like '" . $nombre_o_numdocu . "'";
	

	$query = $mysqli->query($sql);

	$row_count = $query->num_rows;

	if(($row_count) == 0){
		$result["http_code"] = 400;
		$result["result"] = "El DNI no existe.";
	} elseif (($row_count) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $row_count;
	} else {
		$result["http_code"] = 400;
		$result["result"] = "El DNI no existe.";
	}

 
}


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_propietario_ruc_existe") {

	$nombre_o_numdocu = $_POST["nombre_o_numdocu"];
 
 
	$sql = "SELECT 
	id, 
	tipo_persona_id, 
	nombre, 
	tipo_docu_identidad_id, 
	num_docu, 
	num_ruc,
	direccion, 
	representante_legal, 
	num_partida_registral, 
	contacto_nombre, 
	contacto_telefono, 
	contacto_email 
	FROM cont_persona WHERE num_ruc like '" . $nombre_o_numdocu . "'";
	

	$query = $mysqli->query($sql);

	$row_count = $query->num_rows;

	if(($row_count) == 0){
		$result["http_code"] = 400;
		$result["result"] = "El RUC no existe.";
	} elseif (($row_count) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $row_count;
	} else {
		$result["http_code"] = 400;
		$result["result"] = "El RUC no existe.";
	}

 
}


if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_propietario") {
	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	$contacto_nombre = $_POST["nombre"]; 
	
	if ($_POST["tipo_docu"] != 2){
		$sql_existe = "SELECT 
		id, 
		tipo_persona_id, 
		nombre, 
		tipo_docu_identidad_id, 
		num_docu, 
		num_ruc,
		direccion, 
		representante_legal, 
		num_partida_registral, 
		contacto_nombre, 
		contacto_telefono, 
		contacto_email 
		FROM cont_persona WHERE num_docu like '" . $_POST["num_docu"] . "' AND status = 1";
		
		$query_existe = $mysqli->query($sql_existe);
		$row_existe = $query_existe->num_rows;
		if(($row_existe) > 0) {
			$result["http_code"] = 400;
			$result["status"] = "El Nro de Documento ya esta registrado.";
			$result["result"] = $row_existe;
			echo json_encode($result);
			exit();
		} 
		
	} else if($_POST["tipo_docu"] == 2){
		$sql_existe = "SELECT 
		id, 
		tipo_persona_id, 
		nombre, 
		tipo_docu_identidad_id, 
		num_docu, 
		num_ruc,
		direccion, 
		representante_legal, 
		num_partida_registral, 
		contacto_nombre, 
		contacto_telefono, 
		contacto_email 
		FROM cont_persona WHERE num_ruc like '" . $_POST["num_ruc"] . "' AND status = 1";
		
		$query_existe = $mysqli->query($sql_existe);
		$row_existe = $query_existe->num_rows;
		if(($row_existe) > 0) {
			$result["http_code"] = 400;
			$result["status"] = "El RUC ya esta registrado.";
			$result["result"] = $row_existe;
			echo json_encode($result);
			exit();
		} 
	}
	


	
	$result["http_code"] = 400;
	$query_insert = " INSERT INTO cont_persona
					(
					tipo_persona_id,
					tipo_docu_identidad_id,
					num_docu,
					num_ruc,
					nombre,
					direccion,
					representante_legal,
					num_partida_registral,
					contacto_nombre,
					contacto_telefono,
					contacto_email,
					user_created_id,
					created_at)
					VALUES
					(
					" . $_POST["tipo_persona"] . ",
					" . $_POST["tipo_docu"] . ",
					'" . $_POST["num_docu"] . "',
					'" . $_POST["num_ruc"] . "',
					'" . str_replace("'", "",trim($_POST["nombre"])). "',
					'" . str_replace("'", "",trim($_POST["direccion"])). "',
					'" . str_replace("'", "",trim($_POST["representante_legal"])). "',
					'" . $_POST["num_partida_registral"] . "',
					'" . str_replace("'", "",trim($contacto_nombre)). "',
					'" . $_POST["contacto_telefono"] . "',
					'" . $_POST["contacto_email"] . "',
					" . $usuario_id . ",
					'" . $created_at . "')";
	$mysqli->query($query_insert);
	$insert_id = mysqli_insert_id($mysqli);
	$error = '';
	if($mysqli->error){
		$error=$mysqli->error;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $insert_id;
	$result["error"] = $error;


	
}


if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_cambios_propietario") {
	$nombre = str_replace("'", "",trim($_POST["nombre"]));
	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");

	$query_update = "
	UPDATE cont_persona
	SET 
		tipo_persona_id = " . $_POST["tipo_persona"] . ",
		tipo_docu_identidad_id = " . $_POST["tipo_docu"] . ",
		num_docu = '" . $_POST["num_docu"] . "',
		num_ruc = '" . $_POST["num_ruc"] . "',
		nombre = '" . $nombre . "',
		direccion = '" . $_POST["direccion"] . "',
		representante_legal = '" . $_POST["representante_legal"] . "',
		num_partida_registral = '" . $_POST["num_partida_registral"] . "',
		contacto_nombre = '" . $_POST["contacto_nombre"] . "',
		contacto_telefono = '" . $_POST["contacto_telefono"] . "',
		contacto_email = '" . $_POST["contacto_email"] . "',
		user_updated_id = " . $usuario_id . ",
		updated_at = '" . $created_at . "'
	WHERE id = ". $_POST["id_propietario_para_cambios"] ."
	";
	$mysqli->query($query_update);

	$error = '';
	if($mysqli->error){
		$error=$mysqli->error;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = 'ok';
	$result["error"] = $error;
}

// INMUEBLES

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_inmuebles") {

	$id_inmuebles = $_POST["id_inmuebles"];


	$query = "SELECT
	id,
	departamento_id,
	provincia_id,
	distrito_id,
	ubicacion,
	area_arrendada,
	num_partida_registral,
	oficina_registral,
	num_suministro_agua,
	tipo_compromiso_pago_agua,
	monto_o_porcentaje_agua,
	num_suministro_luz,
	tipo_compromiso_pago_luz,
	monto_o_porcentaje_luz,
	tipo_compromiso_pago_arbitrios,
	porcentaje_pago_arbitrios
	FROM cont_inmueble ";
	$data = json_decode($id_inmuebles);
	$contador = 0;
	$ids = '';
	foreach ($data as $value) {
		if ($contador > 0) {
			$ids .= ',';
		}
		$ids .= $value;			
		$contador++;
	}
	$query .= " WHERE id IN(" . $ids . ")";

	
	$list_query = $mysqli->query($query);
	$list = array();
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	
	if(count($list) == 0){
		$result["http_code"] = 400;
		$result["result"] = "El inmueble no existe.";
	} elseif (count($list) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $list;
	} else {
		$result["http_code"] = 400;
		$result["result"] = "El inmueble no existe.";
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_inmueble") {

	if (empty($_POST["monto_o_porcentaje_agua"])) {
		$monto_o_porcentaje_agua = "NULL";
	} else {
		$monto_o_porcentaje_agua = $_POST["monto_o_porcentaje_agua"];
	}

	if (empty($_POST["monto_o_porcentaje_luz"])) {
		$monto_o_porcentaje_luz = "NULL";
	} else {
		$monto_o_porcentaje_luz = $_POST["monto_o_porcentaje_luz"];
	}

	if (empty($_POST["porcentaje_pago_arbitrios"])) {
		$porcentaje_pago_arbitrios = "NULL";
	} else {
		$porcentaje_pago_arbitrios = $_POST["porcentaje_pago_arbitrios"];
	}

	$query_insert = " INSERT INTO cont_inmueble
	(
	departamento_id,
	provincia_id,
	distrito_id,
	ubicacion,
	area_arrendada,
	num_partida_registral,
	oficina_registral,
	num_suministro_agua,
	tipo_compromiso_pago_agua,
	monto_o_porcentaje_agua,
	num_suministro_luz,
	tipo_compromiso_pago_luz,
	monto_o_porcentaje_luz,
	tipo_compromiso_pago_arbitrios,
	porcentaje_pago_arbitrios)
	VALUES
	(
	" . $_POST["id_departamento"] . ",
	" . $_POST["id_provincia"] . ",
	" . $_POST["id_distrito"] . ",
	'" . str_replace("'", "",trim($_POST["ubicacion"])) . "',
	" . $_POST["area_arrendada"] . ",
	" . $_POST["num_partida_registral"] . ",
	'" . str_replace("'", "",trim($_POST["oficina_registral"])) . "',
	" . $_POST["num_suministro_agua"] . ",
	" . $_POST["tipo_compromiso_pago_agua"] . ",
	" . $monto_o_porcentaje_agua. ",
	" . $_POST["num_suministro_luz"] . ",
	" . $_POST["tipo_compromiso_pago_luz"] . ",
	" . $monto_o_porcentaje_luz . ",
	" . $_POST["tipo_compromiso_pago_arbitrios"] . ",
	" . $porcentaje_pago_arbitrios . ")";


	$mysqli->query($query_insert);
	$insert_id = mysqli_insert_id($mysqli);
	$error = '';
	if($mysqli->error){
		$error=$mysqli->error;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $insert_id;
	$result["error"] = $error;
}

 
 
 

if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_contrato_agente") {
	$usuario_id = $login?$login['id']:null;
	$correos_adjuntos_ad = isset($_POST["correos_adjuntos_ad"])? json_decode($_POST["correos_adjuntos_ad"],true) : [];


	$observ_agente = str_replace( "'", " ", $_POST["contrato_ag_observaciones"]);
	
	if ((int) $usuario_id > 0) {
		$created_at = date("Y-m-d H:i:s");
		$error = '';

		$query_update = "UPDATE cont_correlativo SET numero = numero + 1 WHERE tipo_contrato = 6 AND status = 1 ";
		
		$mysqli->query($query_update);

		if($mysqli->error)
		{
			$error .= $mysqli->error . $query_update;
		}
		else
		{
			$numero_correlativo = "";

			$select_correlativo = 
			"
				SELECT
					tipo_contrato,
					sigla,
					numero,
					status
				FROM
				cont_correlativo
				WHERE tipo_contrato = 6 AND status = 1 LIMIT 1
			";

			$list_query = $mysqli->query($select_correlativo);

			while($sel = $list_query->fetch_assoc())
			{
				$sigla = $sel["sigla"];
				$numero_correlativo = $sel["numero"];
			}

			$query_ag_cc = "SELECT id,  nombre, cc_id 
			FROM tbl_locales 
			WHERE id = '" . $_POST["nombre_ag_lc"] . "'";

			$query_cc = $mysqli->query($query_ag_cc);

			while($rest_cc = $query_cc->fetch_assoc())
			{
				$nombre_agente_lc = $rest_cc["nombre"];
				$centro_costos_ag_lc = $rest_cc["cc_id"];
			}
			 

			// INICIO INSERTAR EN CONTRATO
			$query_insert = " INSERT INTO cont_contrato(
			tipo_contrato_id,
			codigo_correlativo,
			empresa_suscribe_id,
			area_responsable_id,
			aprobador_id,
			cargo_aprobador_id,
			persona_responsable_id,
			periodo_numero,
			periodo,
			observaciones,
			status,
			etapa_id,
			plazo_id_agente, 	 
			estado_solicitud,
			usuario_responsable_estado_solicitud,
			user_created_id,
			created_at,
			fecha_suscripcion_contrato,
			nombre_agente,
			c_costos)
			VALUES(
			6,
			" . $numero_correlativo . ",
			" . $_POST["empresa_suscribe_id"] . ",
			" . $_POST["area_responsable_id"] . ",
			" . $_POST["aprobador_id"] . ",
			" . $_POST["cargo_aprobador_id"] . ",
			" . $_POST["personal_responsable_id"] . ",
			" . $_POST["periodo_numero"] . ",
			" . $_POST["periodo"] . ",
			'" . $observ_agente . "',
			1,
			1,	
			1,
			1,
			0,
			" . $usuario_id . ",
			'" . $created_at . "',
			'" . $_POST["fecha_contrato_ag"] . "',
			'" . $nombre_agente_lc . "',
			'" . $centro_costos_ag_lc . "' )";

			$mysqli->query($query_insert);
			$contrato_id = mysqli_insert_id($mysqli);

			if($mysqli->error){
				$error .= $mysqli->error . $query_insert;
			}
			// FIN INSERTAR EN CONTRATO

			$query_update_lc = "UPDATE tbl_locales SET status_cont_ag = 0 WHERE id = '" . $_POST["nombre_ag_lc"] . "'";
		
			$mysqli->query($query_update_lc);

			if($mysqli->error)
			{
				$error .= $mysqli->error . $query_update_lc;
			}
		 


			// INICIO INSERTAR EN CONDICIONES COMERCIALES


			$query_insert_cc = " INSERT INTO cont_cc_agente(
				contrato_id,			 
				participacion_id,
				porcentaje_participacion,
				condicion_comercial_id)
				VALUES(
				" . $contrato_id . ",		 
				1,
				" . $_POST["porcentaje_participacion_bet"] . ",
				" . $_POST["condicion_comercial_id_bet"] . " )";
	
				$mysqli->query($query_insert_cc); 
	
				if($mysqli->error){
					$error .= $mysqli->error . $query_insert_cc;
				}
			
				$query_insert_cc = " INSERT INTO cont_cc_agente(
					contrato_id,			 
					participacion_id,
					porcentaje_participacion,
					condicion_comercial_id)
					VALUES(
					" . $contrato_id . ",		 
					2,
					" . $_POST["porcentaje_participacion_j"] . ",
					" . $_POST["condicion_comercial_id_jv"] . " )";
		
					$mysqli->query($query_insert_cc); 
		
					if($mysqli->error){
						$error .= $mysqli->error . $query_insert_cc;
					}

					$query_insert_cc = " INSERT INTO cont_cc_agente(
						contrato_id,			 
						participacion_id,
						porcentaje_participacion,
						condicion_comercial_id)
						VALUES(
						" . $contrato_id . ",		 
						3,
						" . $_POST["porcentaje_participacion_ter"] . ",
						" . $_POST["condicion_comercial_id_t"] . " )";
			
						$mysqli->query($query_insert_cc); 
			
						if($mysqli->error){
							$error .= $mysqli->error . $query_insert_cc;
						}

						$query_insert_cc = " INSERT INTO cont_cc_agente(
							contrato_id,			 
							participacion_id,
							porcentaje_participacion,
							condicion_comercial_id)
							VALUES(
							" . $contrato_id . ",		 
							4,
							" . $_POST["porcentaje_participacion_bin"] . ",
							" . $_POST["condicion_comercial_id_b"] . " )";
				
							$mysqli->query($query_insert_cc); 
				
							if($mysqli->error){
								$error .= $mysqli->error . $query_insert_cc;
							}

							$query_insert_cc = " INSERT INTO cont_cc_agente(
								contrato_id,			 
								participacion_id,
								porcentaje_participacion,
								condicion_comercial_id)
								VALUES(
								" . $contrato_id . ",		 
								5,
								" . $_POST["porcentaje_participacion_dep"] . ",
								" . $_POST["condicion_comercial_id_dw"] . " )";
					
								$mysqli->query($query_insert_cc); 
					
								if($mysqli->error){
									$error .= $mysqli->error . $query_insert_cc;
								}
						$query_insert_cc = " INSERT INTO cont_cc_agente(
							contrato_id,			 
							participacion_id,
							porcentaje_participacion,
							condicion_comercial_id)
							VALUES(
							" . $contrato_id . ",		 
							6,
							" . $_POST["porcentaje_participacion_ccv"] . ",
							" . $_POST["condicion_comercial_id_ccv"] . " )";
				
							$mysqli->query($query_insert_cc); 
				
							if($mysqli->error){
								$error .= $mysqli->error . $query_insert_cc;
							}
				

			// INICIO INSERTAR EN CONDICIONES COMERCIALES



			// INICIO GUARDAR INMUEBLE
			$id_departamento = str_pad(trim($_POST["id_departamento"]), 2,"0", STR_PAD_LEFT);
			$id_provincia = str_pad(trim($_POST["id_provincia"]), 2,"0", STR_PAD_LEFT);
			$id_distrito = str_pad(trim($_POST["id_distrito"]), 2,"0", STR_PAD_LEFT);

			$ubigeo_id = $id_departamento . $id_provincia . $id_distrito;

			$query_insert = " INSERT INTO cont_inmueble
			(
			contrato_id,
			ubigeo_id,
			ubicacion,
			area_arrendada,
			num_partida_registral,
			oficina_registral,
			num_suministro_agua,
			tipo_compromiso_pago_agua,
			monto_o_porcentaje_agua,
			num_suministro_luz,
			tipo_compromiso_pago_luz,
			monto_o_porcentaje_luz,
			tipo_compromiso_pago_arbitrios,
			porcentaje_pago_arbitrios,
			latitud,
			longitud,
			user_created_id,
			created_at)
			VALUES
			(
			" . $contrato_id . ",
			'" . $ubigeo_id . "',
			'" . str_replace("'", "",trim($_POST["ubicacion"])) . "',
			0,
			'',
			'',
			'',
			0,
			0,
			'',
			0,
			0,
			0,
			0,
			'',
			'',
			" . $usuario_id . ",
			'" . $created_at . "'
			)";


			$mysqli->query($query_insert);
			$insert_id = mysqli_insert_id($mysqli);
			if($mysqli->error){
				$error .= $mysqli->error . $query_insert;
			}
			// FIN GUARDAR INMUEBLE


			// INICIO INSERTAR EN CONDICIONES DE CONTRATO
			$monto_renta = str_replace(",","",$_POST["monto_renta"]);
			$tipo_moneda_id = $_POST["tipo_moneda_renta_pactada"];
			$impuesto_a_la_renta_id = $_POST["impuesto_a_la_renta_id"];
			$carta_de_instruccion_id = $_POST["impuesto_a_la_renta_carta_de_instruccion_id"];
			$monto_garantia = str_replace(",","",$_POST["monto_garantia"]);
			$tipo_adelanto_id = $_POST["tipo_adelanto_id"];
			$cant_meses_contrato = trim($_POST["vigencia_del_contrato_en_meses"]);
			$contrato_inicio_fecha = $_POST["contrato_inicio_fecha"];
			$contrato_fin_fecha = $_POST["contrato_fin_fecha"];
			$contrato_fecha_suscripcion = trim($_POST["contrato_fecha_suscripcion"]);
			$periodo_gracia_id = $_POST["periodo_gracia_id"];
			$periodo_gracia_numero = trim($_POST["periodo_gracia_numero"]);


			// INICIO INSERTAR PROPIETARIOS
			$id_propietarios = $_POST["id_propietarios"];
			$data_propietarios = json_decode($id_propietarios);
			foreach ($data_propietarios as $value_id_propietario) {
				$query_insert = " INSERT INTO cont_propietario(
				contrato_id,
				persona_id,
				user_created_id)
				VALUES(
				" . $contrato_id . ",
				" . $value_id_propietario . ",
				" . $usuario_id . ")";

				$mysqli->query($query_insert);

				if($mysqli->error){
					$error .= $mysqli->error . $query_insert;
				}
			}
			// FIN INSERTAR PROPIETARIOS

			// INICIO BENEFICIARIOS
			$id_beneficiarios = $_POST["id_beneficiarios"];
			$data_beneficiarios = json_decode($id_beneficiarios);
			foreach ($data_beneficiarios as $value_id_beneficiario) {
				$query_update = "
				UPDATE cont_beneficiarios 
				SET 
					contrato_id = ". $contrato_id .",
					user_updated_id = " . $usuario_id . ",
					updated_at = '" . $created_at . "'
				WHERE id = ". $value_id_beneficiario ."
				";
				$mysqli->query($query_update);

				if($mysqli->error){
					$error .= $mysqli->error . $query_update;
				}
			}
			// FIN BENEFICIARIOS

			

			// INICIO CARGAR PDF
			$path = "/var/www/html/files_bucket/contratos/solicitudes/agentes/";
			$usuario_id = $login?$login['id']:null;
			$created_at = date("Y-m-d H:i:s");



			if (isset($_FILES['archivo_partida_inmueble']) && $_FILES['archivo_partida_inmueble']['error'] === UPLOAD_ERR_OK) {
				if (!is_dir($path)) mkdir($path, 0777, true);

				$filename = $_FILES['archivo_partida_inmueble']['name'];
				$filenametem = $_FILES['archivo_partida_inmueble']['tmp_name'];
				$filesize = $_FILES['archivo_partida_inmueble']['size'];
				$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
				if($filename != ""){
					$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
					$nombre_archivo = $contrato_id . "_PARTIDA_INMUEBLE_" . date('YmdHis') . "." . $fileExt;
					move_uploaded_file($filenametem, $path . $nombre_archivo);
					$comando = "INSERT INTO cont_archivos (
									contrato_id,
									tipo_archivo_id,
									nombre,
									extension,
									size,
									ruta,
									user_created_id,
									created_at)
								VALUES(
									'" . $contrato_id . "',
									8,
									'" . $nombre_archivo . "',
									'" . $fileExt . "',
									'" . $filesize . "',
									'" . $path . "',
									" . $usuario_id . ",
									'" . $created_at . "'
									)";
					$mysqli->query($comando);

					if($mysqli->error){
						$error .= 'Error al guardar la partida registral del inmueble: ' . $mysqli->error . $comando;
					}
				}
			}



			if (isset($_FILES['archivo_licencia_funcionamiento']) && $_FILES['archivo_licencia_funcionamiento']['error'] === UPLOAD_ERR_OK) {
				if (!is_dir($path)) mkdir($path, 0777, true);

				$filename = $_FILES['archivo_licencia_funcionamiento']['name'];
				$filenametem = $_FILES['archivo_licencia_funcionamiento']['tmp_name'];
				$filesize = $_FILES['archivo_licencia_funcionamiento']['size'];
				$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
				if($filename != ""){
					$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
					$nombre_archivo = $contrato_id . "_LICENCIA_FUNCIONAMIENTO_" . date('YmdHis') . "." . $fileExt;
					move_uploaded_file($filenametem, $path . $nombre_archivo);
					$comando = "INSERT INTO cont_archivos (
									contrato_id,
									tipo_archivo_id,
									nombre,
									extension,
									size,
									ruta,
									user_created_id,
									created_at)
								VALUES(
									'" . $contrato_id . "',
									4,
									'" . $nombre_archivo . "',
									'" . $fileExt . "',
									'" . $filesize . "',
									'" . $path . "',
									" . $usuario_id . ",
									'" . $created_at . "'
									)";
					$mysqli->query($comando);

					if($mysqli->error){
						$error .= 'Error al guardar la licencia del funcionamiento: ' . $mysqli->error . $comando;
					}
				}
			}



			if (isset($_FILES['archivo_ruc_agente']) && $_FILES['archivo_ruc_agente']['error'] === UPLOAD_ERR_OK) {
				if (!is_dir($path)) mkdir($path, 0777, true);

				$filename = $_FILES['archivo_ruc_agente']['name'];
				$filenametem = $_FILES['archivo_ruc_agente']['tmp_name'];
				$filesize = $_FILES['archivo_ruc_agente']['size'];
				$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
				if($filename != ""){
					$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
					$nombre_archivo = $contrato_id . "_RUC_AGENTE_" . date('YmdHis') . "." . $fileExt;
					move_uploaded_file($filenametem, $path . $nombre_archivo);
					$comando = "INSERT INTO cont_archivos (
									contrato_id,
									tipo_archivo_id,
									nombre,
									extension,
									size,
									ruta,
									user_created_id,
									created_at)
								VALUES(
									'" . $contrato_id . "',
									159,
									'" . $nombre_archivo . "',
									'" . $fileExt . "',
									'" . $filesize . "',
									'" . $path . "',
									" . $usuario_id . ",
									'" . $created_at . "'
									)";
					$mysqli->query($comando);

					if($mysqli->error){
						$error .= 'Error al guardar el ruc del agente: ' . $mysqli->error . $comando;
					}
				}
			}



			if (isset($_FILES['archivo_dni_agente']) && $_FILES['archivo_dni_agente']['error'] === UPLOAD_ERR_OK) {
				if (!is_dir($path)) mkdir($path, 0777, true);

				$filename = $_FILES['archivo_dni_agente']['name'];
				$filenametem = $_FILES['archivo_dni_agente']['tmp_name'];
				$filesize = $_FILES['archivo_dni_agente']['size'];
				$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
				if($filename != ""){
					$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
					$nombre_archivo = $contrato_id . "_DNI_AGENTE_" . date('YmdHis') . "." . $fileExt;
					move_uploaded_file($filenametem, $path . $nombre_archivo);
					$comando = "INSERT INTO cont_archivos (
									contrato_id,
									tipo_archivo_id,
									nombre,
									extension,
									size,
									ruta,
									user_created_id,
									created_at)
								VALUES(
									'" . $contrato_id . "',
									11,
									'" . $nombre_archivo . "',
									'" . $fileExt . "',
									'" . $filesize . "',
									'" . $path . "',
									" . $usuario_id . ",
									'" . $created_at . "'
									)";
					$mysqli->query($comando);

					if($mysqli->error){
						$error .= 'Error al guardar el dni del agente: ' . $mysqli->error . $comando;
					}
				}
			}



			if (isset($_FILES['archivo_vigencia_poder']) && $_FILES['archivo_vigencia_poder']['error'] === UPLOAD_ERR_OK) {
				if (!is_dir($path)) mkdir($path, 0777, true);

				$filename = $_FILES['archivo_vigencia_poder']['name'];
				$filenametem = $_FILES['archivo_vigencia_poder']['tmp_name'];
				$filesize = $_FILES['archivo_vigencia_poder']['size'];
				$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
				if($filename != ""){
					$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
					$nombre_archivo = $contrato_id . "_VIGENCIA_PODER_" . date('YmdHis') . "." . $fileExt;
					move_uploaded_file($filenametem, $path . $nombre_archivo);
					$comando = "INSERT INTO cont_archivos (
									contrato_id,
									tipo_archivo_id,
									nombre,
									extension,
									size,
									ruta,
									user_created_id,
									created_at)
								VALUES(
									'" . $contrato_id . "',
									12,
									'" . $nombre_archivo . "',
									'" . $fileExt . "',
									'" . $filesize . "',
									'" . $path . "',
									" . $usuario_id . ",
									'" . $created_at . "'
									)";
					$mysqli->query($comando);

					if($mysqli->error){
						$error .= 'Error al guardar la vigencia poder: ' . $mysqli->error . $comando;
					}
				}
			}

			if (isset($_FILES['archivo_dni_representante_legal']) && $_FILES['archivo_dni_representante_legal']['error'] === UPLOAD_ERR_OK) {
				if (!is_dir($path)) mkdir($path, 0777, true);

				$filename = $_FILES['archivo_dni_representante_legal']['name'];
				$filenametem = $_FILES['archivo_dni_representante_legal']['tmp_name'];
				$filesize = $_FILES['archivo_dni_representante_legal']['size'];
				$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
				if($filename != ""){
					$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
					$nombre_archivo = $contrato_id . "_DNI_REPRESENTANTE_LEGAL_" . date('YmdHis') . "." . $fileExt;
					move_uploaded_file($filenametem, $path . $nombre_archivo);
					$comando = "INSERT INTO cont_archivos (
									contrato_id,
									tipo_archivo_id,
									nombre,
									extension,
									size,
									ruta,
									user_created_id,
									created_at)
								VALUES(
									'" . $contrato_id . "',
									13,
									'" . $nombre_archivo . "',
									'" . $fileExt . "',
									'" . $filesize . "',
									'" . $path . "',
									" . $usuario_id . ",
									'" . $created_at . "'
									)";
					$mysqli->query($comando);

					if($mysqli->error){
						$error .= 'Error al guardar el DNI del representante legal: ' . $mysqli->error . $comando;
					}
				}
			}
			// FIN CARGAR PDF


			// INICIO DE CARGAR NUEVOS ANEXOS
			if (isset($_FILES["miarchivo"])) {
			    if($_FILES["miarchivo"]){
			        //Recorre el array de los archivos a subir
			        $h = '';
			        foreach($_FILES["miarchivo"]['tmp_name'] as $key => $tmp_name){
			            //Si el archivo existe
			            if($_FILES["miarchivo"]["name"][$key]){
			                $file_name = $_FILES["miarchivo"]["name"][$key]; 
			                $fuente = $_FILES["miarchivo"]["tmp_name"][$key]; 
							$filesize = $_FILES['miarchivo']['size'][$key];
							//$file_id = $_FILES['miarchivo']['id'][$key];
			                $fileExt = pathinfo($file_name, PATHINFO_EXTENSION);
			                $tipo_archivo = 0;
			                $nombre_tipo_archivo = "";

			                $array_nuevos_files_anexos = $_POST["array_nuevos_files_anexos"];
							$cont = 0;

							$data = json_decode($array_nuevos_files_anexos);
							$ids = '';

							foreach ($data as $value) {
								$result["file_name"] = $file_name . " - data filename: " . $value->nombre_archivo;
								$result["fileSize"] = $filesize . " - data fileSize: " . $value->tamano_archivo;
								$result["fileExt"] = $fileExt . " - data fileExt: " . $value->extension;
								if($value->nombre_archivo == $file_name && $value->tamano_archivo == $filesize && $value->extension == $fileExt){
									$nombre_tipo_archivo = str_replace(' ', '_', $value->tip_doc_nombre);
									$nombre_tipo_archivo = strtoupper($nombre_tipo_archivo);
									$tipo_archivo = $value->id_tip_documento;
								}
							}

			                $nombre_archivo = $contrato_id . "_" . $nombre_tipo_archivo . "_" . date('YmdHis') . "." . $fileExt;
			               	
			                if(!file_exists($path)){
			                    mkdir($path, 0777); //or die("Hubo un error al crear la carpeta");	
			                }
			                $dir=opendir($path);
			                if(move_uploaded_file($fuente, $path.'/'.$nombre_archivo)){	
			                	$comando = "INSERT INTO cont_archivos (
									contrato_id,
									tipo_archivo_id,
									nombre,
									extension,
									size,
									ruta,
									user_created_id,
									created_at)
									VALUES(
									'" . $contrato_id . "',
									'" . $tipo_archivo . "',
									'" . $nombre_archivo . "',
									'" . $fileExt . "',
									'" . $filesize . "',
									'" . $path . "',
									" . $usuario_id . ",
									'" . $created_at . "'
									)";

									$mysqli->query($comando);
									if($mysqli->error){
										$error .= 'Error al guardar el nuevo anexo' . $mysqli->error . $comando;
									}
			                }else{	
			                }
			                closedir($dir); 
			            }
			        }
			    }
		    }
			// FIN DE CARGAR NUEVOS ANEXOS
		}

	

		//$result["filenametem_na"] = $filename_tmp_na;
		if ($error == '') {
			//$correos_adjuntos_ad = ['yonathan.mamani@testtest.kuraxdev.net', 'jacqueline.santiago@testtest.apuestatotal.com']; 
			send_email_confirmacion_solicitud_contrato_agente($contrato_id, $correos_adjuntos_ad, false);
			// send_email_solicitud_contrato_agente_detallado($contrato_id, false, false);
			$result["http_code"] = 200;
			$result["status"] = "Datos obtenidos de gestión.";
			$result["result"] = $contrato_id;
			$result["error"] = $error;
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Datos obtenidos de gestión.";
			$result["result"] = '';
			$result["error"] = $error;
		}
	} else {
		$result["http_code"] = 400;
		$result["error"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y volver a hacer clic en el botón de color verde: Enviar Solicitud Contrato de Agente.";
	}
}

 

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_tipo_persona") {
	$query = "SELECT * FROM cont_tipo_persona WHERE estado = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_tipo_docu_identidad") {
	$query = "SELECT * FROM cont_tipo_docu_identidad WHERE estado = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}
  
 

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_forma_pago") {
	$query = "SELECT * FROM cont_forma_pago WHERE estado = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_tipo_contrato_agente") {
	$area_id = $login ? $login['area_id'] : 0;

	$query = "SELECT * FROM cont_tipo_contrato WHERE status = 1";
	// if ($area_id != 6) { // Desarrollo
	if ($area_id != 21) { // Producción
		$query .= " AND id IN('6')";
	}

	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_empresa_at") {
	$query = "SELECT id, nombre
	FROM tbl_razon_social
	WHERE status = 1
	ORDER BY nombre ASC";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_empresa_agente") {
	$query = "SELECT id, nombre
	FROM tbl_razon_social
	WHERE status = 1 AND id IN (1,5,30)
	ORDER BY nombre ASC";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_personal_responsable_agente") {
	$query = "SELECT u.id, concat( IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, ''),' ', IFNULL(p.apellido_materno, '')) AS nombre
	FROM tbl_personal_apt p
	INNER JOIN tbl_usuarios u ON p.id = u.personal_id
	WHERE p.cargo_id IN (4,19) AND p.estado = 1 AND u.estado = 1 AND p.zona_id = 12
	ORDER BY nombre ASC";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}
 

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_agente_cc_lc") {
	$query = "SELECT id, concat( IFNULL(nombre, ''),' - ', IFNULL(cc_id, '')) AS nombre
	FROM tbl_locales 
	WHERE red_id in (5) AND estado = 1
	ORDER BY id ASC";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}


function send_email_confirmacion_solicitud_contrato_agente($contrato_id, $correos_adjuntos_ad, $reenvio = false)
{
	
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$sel_query = $mysqli->query("SELECT c.nombre_tienda, 
									 i.ubicacion, 
									 c.user_created_id,
									 concat( IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS usuario_solicitante,
									 tp.correo,
									 co.sigla AS sigla_correlativo,
									c.codigo_correlativo,
									c.created_at,
									c.nombre_agente,
									c.c_costos,
									tpa.correo correo_aprobador,
									p2.correo correo_supervisor, 
									p.correo correo_responsable
								FROM cont_contrato c
									INNER JOIN cont_inmueble i
									ON c.contrato_id = i.contrato_id
									INNER JOIN tbl_usuarios tu
									ON tu.id = c.user_created_id
									INNER JOIN tbl_personal_apt tp
									ON tp.id = tu.personal_id
									LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
									LEFT JOIN tbl_usuarios tu2 ON tu2.id = c.aprobador_id
									LEFT JOIN tbl_personal_apt tpa ON tpa.id = tu2.personal_id
									LEFT JOIN tbl_usuarios u2 ON c.persona_responsable_id = u2.id
									LEFT JOIN tbl_personal_apt p2 ON u2.personal_id = p2.id
									LEFT JOIN tbl_usuarios u ON c.user_created_id = u.id
									LEFT JOIN tbl_personal_apt p ON u.personal_id = p.id
								WHERE c.contrato_id = '".$contrato_id."' LIMIT 1
				");

	$body = "";
	$body .= '<html>';

	$correos_ad = [];

	while($sel = $sel_query->fetch_assoc())
	{
		$sigla_correlativo = $sel['sigla_correlativo'];
		$codigo_correlativo = $sel['codigo_correlativo'];
		$nombre_agente = $sel['nombre_agente'];
		$correo_aprobador = $sel['correo_aprobador'];
		$correo_supervisor = $sel['correo_supervisor'];
		$correo_responsable = $sel['correo_responsable'];
		
		if (!empty($correo_aprobador)) {
			array_push($correos_ad, $correo_aprobador);
		}
		if (!empty($correo_responsable)) {
			array_push($correos_ad, $correo_responsable);
		}

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

		$body .= '<thead>';
		$body .= '<tr>';
		$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
		$body .= '<b>Nueva solicitud</b>';
		$body .= '</th>';
		$body .= '</tr>';
		$body .= '</thead>';


		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Nombre de Agente:</b></td>';
			$body .= '<td>'.$sel["nombre_agente"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Dirección:</b></td>';
			$body .= '<td>'.$sel["ubicacion"].'</td>';
		$body .= '</tr>';
		
		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Centro de Costos:</b></td>';
			$body .= '<td>'.$sel["c_costos"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha solicitud:</b></td>';
			$body .= '<td>'.$sel["created_at"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Usuario solicitante:</b></td>';
			$body .= '<td>'.$sel["usuario_solicitante"].'</td>';
		$body .= '</tr>';

		$body .= '</table>';
		$body .= '</div>';

	}

	$body .= '<div>';
		$body .= '<br>';
	$body .= '</div>';

	$body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
	    $body .= '<a href="'.$host.'/?sec_id=contrato&sub_sec_id=detalle_agente&id='.$contrato_id.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
			$body .= '<b>Ver Solicitud</b>';
	    $body .= '</a>';
	$body .= '</div>';

	$body .= '</html>';
	$body .= "";


	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_confirmacion_solicitud_contrato_agente($correos_ad);
	
	
	$titulo = "Gestion - Sistema Contratos - Confirmación Solicitud de Contrato de Agente: ".$nombre_agente." - Código: " .$sigla_correlativo.$codigo_correlativo;
	if($reenvio){
		$titulo = "Gestion - Sistema Contratos - Reenvio - Confirmación Solicitud de Contrato de Agente: ".$nombre_agente." - Código: " .$sigla_correlativo.$codigo_correlativo;
	}

	if (env('SEND_EMAIL') == 'TEST') { // Imprimir lista de correos que se enviarian en producción pero solo se visualizara en Desarrollo 
		$correos_produccion = implode(", ", $lista_correos['cc_dev']);

		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
		$body .= '<thead>';
		$body .= '<tr>';
			$body .= '<th style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>Lista de Correos</b>';
			$body .= '</th>';
		$body .= '</tr>';
		$body .= '</thead>';
		$body .= '<tbody>';
		$body .= '<tr>';
			$body .= '<td>'.$correos_produccion.'</td>';
		$body .= '</tr>';
		$body .= '</tfoot>';
		$body .= '<tr>';
			$body .= '<td style="text-align: center;"><small>*** Esta sección solo se envia en desarroollo ***</small></td>';
		$body .= '</tr>';
		$body .= '</tfoot>';
		$body .= '</table>';
		$body .= '</div>';
	}

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];

	$request = [
		"subject" => $titulo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try 
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;        
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc) 
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc) 
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();
		//return true;

	} 
	catch (Exception $e) 
	{
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}
}

function send_email_solicitud_contrato_agente($contrato_id, $correos_adjuntos_ad, $reenvio = false)
{
	
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$sel_query = $mysqli->query("SELECT c.nombre_tienda, 
									 i.ubicacion, 
									 c.user_created_id,
									 concat( IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS usuario_solicitante,
									 tp.correo,
									 co.sigla AS sigla_correlativo,
									c.codigo_correlativo,
									c.created_at,
									c.nombre_agente,
									c.c_costos,
									tpa.correo correo_aprobador,
									p2.correo correo_supervisor, 
									p.correo correo_responsable,
									c.fecha_aprobacion,
									c.estado_aprobacion
								FROM cont_contrato c
									INNER JOIN cont_inmueble i
									ON c.contrato_id = i.contrato_id
									INNER JOIN tbl_usuarios tu
									ON tu.id = c.user_created_id
									INNER JOIN tbl_personal_apt tp
									ON tp.id = tu.personal_id
									LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
									LEFT JOIN tbl_usuarios tu2 ON tu2.id = c.aprobado_por
									LEFT JOIN tbl_personal_apt tpa ON tpa.id = tu2.personal_id
									LEFT JOIN tbl_usuarios u2 ON c.persona_responsable_id = u2.id
									LEFT JOIN tbl_personal_apt p2 ON u2.personal_id = p2.id
									LEFT JOIN tbl_usuarios u ON c.user_created_id = u.id
									LEFT JOIN tbl_personal_apt p ON u.personal_id = p.id
								WHERE c.contrato_id = '".$contrato_id."' LIMIT 1
				");

	$body = "";
	$body .= '<html>';

	$correos_ad = [];
	$correo_responsable = "";
	$estado_aprobacion = true;
	while($sel = $sel_query->fetch_assoc())
	{
		$sigla_correlativo = $sel['sigla_correlativo'];
		$codigo_correlativo = $sel['codigo_correlativo'];
		$nombre_agente = $sel['nombre_agente'];
		$correo_aprobador = $sel['correo_aprobador'];
		$correo_supervisor = $sel['correo_supervisor'];
		$correo_responsable = $sel['correo_responsable'];
		
		if (!empty($correo_aprobador)) {
			array_push($correos_ad, $correo_aprobador);
		}
		if (!empty($correo_supervisor)) {
			array_push($correos_ad, $correo_supervisor);
		}
		if (!empty($correo_responsable)) {
			array_push($correos_ad, $correo_responsable);
		}

		if (!is_null($sel['fecha_aprobacion']) && $sel['estado_aprobacion'] == 0) {
			$estado_aprobacion = false; // en caso el contrato este rechazado
		}

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

		$body .= '<thead>';
		$body .= '<tr>';
		$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
		$body .= '<b>Nueva solicitud</b>';
		$body .= '</th>';
		$body .= '</tr>';
		$body .= '</thead>';


		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Nombre de Agente:</b></td>';
			$body .= '<td>'.$sel["nombre_agente"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Dirección:</b></td>';
			$body .= '<td>'.$sel["ubicacion"].'</td>';
		$body .= '</tr>';
		
		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Centro de Costos:</b></td>';
			$body .= '<td>'.$sel["c_costos"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha solicitud:</b></td>';
			$body .= '<td>'.$sel["created_at"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Usuario solicitante:</b></td>';
			$body .= '<td>'.$sel["usuario_solicitante"].'</td>';
		$body .= '</tr>';

		$body .= '</table>';
		$body .= '</div>';

	}

	$body .= '<div>';
		$body .= '<br>';
	$body .= '</div>';

	$body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
	    $body .= '<a href="'.$host.'/?sec_id=contrato&sub_sec_id=detalle_agente&id='.$contrato_id.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
			$body .= '<b>Ver Solicitud</b>';
	    $body .= '</a>';
	$body .= '</div>';

	$body .= '</html>';
	$body .= "";



	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	if ($estado_aprobacion) { // aprobado
		$lista_correos = $correos->send_email_solicitud_contrato_agente($correos_ad);
		$cc = $lista_correos['cc'];
		$bcc = $lista_correos['bcc'];
		$titulo = "Gestion - Sistema Contratos - Nueva Solicitud de Contrato de Agente: ".$nombre_agente." - Código: " .$sigla_correlativo.$codigo_correlativo;
		if($reenvio){
			$titulo = "Gestion - Sistema Contratos - Reenvio - Nueva Solicitud de Contrato de Agente: ".$nombre_agente." - Código: " .$sigla_correlativo.$codigo_correlativo;
		}
	}else{  // rechazado
		$lista_correos = $correos->send_email_solicitud_rechazada([$correo_responsable]);
		$cc = $lista_correos['cc'];
		$bcc = $lista_correos['bcc'];
		$titulo = "Gestion - Sistema Contratos - Solicitud de Contrato de Agente Rechazada: ".$nombre_agente." - Código: " .$sigla_correlativo.$codigo_correlativo;
		if($reenvio){
			$titulo = "Gestion - Sistema Contratos - Reenvio - Solicitud de Contrato de Agente Rechazada: ".$nombre_agente." - Código: " .$sigla_correlativo.$codigo_correlativo;
		}
	}
	
	if (env('SEND_EMAIL') == 'TEST') { // Imprimir lista de correos que se enviarian en producción pero solo se visualizara en Desarrollo 
		$correos_produccion = implode(", ", $lista_correos['cc_dev']);

		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
		$body .= '<thead>';
		$body .= '<tr>';
			$body .= '<th style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>Lista de Correos</b>';
			$body .= '</th>';
		$body .= '</tr>';
		$body .= '</thead>';
		$body .= '<tbody>';
		$body .= '<tr>';
			$body .= '<td>'.$correos_produccion.'</td>';
		$body .= '</tr>';
		$body .= '</tfoot>';
		$body .= '<tr>';
			$body .= '<td style="text-align: center;"><small>*** Esta sección solo se envia en desarroollo ***</small></td>';
		$body .= '</tr>';
		$body .= '</tfoot>';
		$body .= '</table>';
		$body .= '</div>';
	}
	
	$request = [
		"subject" => $titulo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try 
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;        
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc) 
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc) 
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();
		//return true;

	} 
	catch (Exception $e) 
	{
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}
}


 

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_contrato_nuevo_agente_guardar_nuevo_anexo") {
	$path = "/var/www/html/files_bucket/contratos/comprobantes_de_pago/";
	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	//$programacion_id = $_POST["programacion_id"];
	$error = '';

	if (isset($_FILES['sec_nuevo_file_nuevo_anexo']) && $_FILES['sec_nuevo_file_nuevo_anexo']['error'] === UPLOAD_ERR_OK) {
		if (!is_dir($path)) mkdir($path, 0777, true);

		$filename = $_FILES['sec_nuevo_file_nuevo_anexo']['name'];
		$filenametem = $_FILES['sec_nuevo_file_nuevo_anexo']['tmp_name'];
		$filesize = $_FILES['sec_nuevo_file_nuevo_anexo']['size'];
		$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
		if($filename != ""){
			$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
			$nombre_archivo = "COMPROBANTE_PAGO_" . date('YmdHis') . "." . $fileExt;
			move_uploaded_file($filenametem, $path . $nombre_archivo);
			$comando = "INSERT INTO cont_comprobantes_pago ( nombre, extension, size,
							ruta, user_created_id, created_at)
						VALUES('" . $nombre_archivo . "','" . $fileExt . "','" . $filesize . "',
							'" . $path . "'," . $usuario_id . ",'" . $created_at . "')";
			$mysqli->query($comando);
			$insert_id = mysqli_insert_id($mysqli);

			$comando2 = "UPDATE cont_programacion 
						SET fecha_pago = '" . $_POST["fecha_pago"] . "',
						comprobante_pago_id = " . $insert_id . "
						WHERE id = " . $programacion_id;

			$mysqli->query($comando2);
			if($mysqli->error){
				$error .= 'Error al actualizar la programacion de pago: ' . $mysqli->error . $comando2;
			}

		}
	}
	if ($error == '') {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["error"] = $error;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["error"] = $error;
	}
	$result["created_at"] = $created_at;
	echo json_encode($result);
}


if (isset($_POST["accion"]) && $_POST["accion"]==="sec_con_nuevo_guardar_nuevo_tipo_anexo_nuevo") {
	$anexo = $_POST["anexo"];
	$tipo_contrato_id = $_POST["tipo_contrato_id"];
	$query_insert = "INSERT INTO cont_tipo_archivos(nombre_tipo_archivo, tipo_contrato_id, status) VALUES ('" . $anexo . "',$tipo_contrato_id,1)";
	$mysqli->query($query_insert);
	$insert_id = mysqli_insert_id($mysqli);
	$error = '';
	if($mysqli->error){
		$error=$mysqli->error;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $insert_id;
	$result["error"] = $error;
}

 

function send_email_solicitud_contrato_agente_detallado($contrato_id, $enviar_respuesta, $reenvio)
{
	include("db_connect.php");
	include("sys_login.php");

	$body = '';

	$sel_query = $mysqli->query("
	SELECT 
		c.empresa_suscribe_id,
		r.nombre AS empresa_suscribe,
		c.persona_responsable_id,
		CONCAT(IFNULL(p2.nombre, ''),' ',IFNULL(p2.apellido_paterno, ''),' ',IFNULL(p2.apellido_materno, '')) AS persona_responsable,
		c.observaciones,
		c.user_created_id,
		CONCAT(IFNULL(p.nombre, ''),' ',IFNULL(p.apellido_paterno, ''),' ',IFNULL(p.apellido_materno, '')) AS user_created,
		c.created_at,
		co.sigla AS sigla_correlativo,
		c.codigo_correlativo
	FROM
		cont_contrato c
		INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id
		INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
		INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		LEFT JOIN tbl_usuarios u2 ON c.persona_responsable_id = u2.id
		LEFT JOIN tbl_personal_apt p2 ON u2.personal_id = p2.id
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
	WHERE 
		c.contrato_id IN (" . $contrato_id . ")");
	while($sel = $sel_query->fetch_assoc()){
		$observaciones = $sel["observaciones"];

		$sigla_correlativo = $sel['sigla_correlativo'];
		$codigo_correlativo = $sel['codigo_correlativo'];

	$body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
	$body .= '<tr>';
	$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Datos Generales</th>';
	$body .= '</tr>';
	$body .= '<tr>';
	$body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Empresa</b></td>';
	$body .= '<td>' . $sel["empresa_suscribe"] . '</td>';
	$body .= '</tr>';
	$body .= '<tr>';
	$body .= '<td style="background-color:#ffffdd;"><b>Supervisor</b></td>';
	$body .= '<td>' . $sel["persona_responsable"] . '</td>';
	$body .= '</tr>';
	$body .= '<tr>';
	$body .= '<td style="background-color:#ffffdd;"><b>Registrado por</b></td>';
	$body .= '<td>' . $sel["user_created"] . '</td>';
	$body .= '</tr>';
	$body .= '<tr>';
	$body .= '<td style="background-color:#ffffdd;"><b>Fecha de Registro</b></td>';
	$body .= '<td>' . $sel["created_at"] . '</td>';
	$body .= '</tr>';
	$body .= '</table>';

	}

	$body .= '<br>';

	$sel_query = $mysqli->query("SELECT p.id AS persona_id,
		pr.propietario_id,
		tp.nombre AS tipo_persona,
		td.nombre AS tipo_docu_identidad,
		p.num_docu,
		p.nombre,
		p.direccion,
		p.representante_legal,
		p.num_partida_registral,
		p.contacto_nombre,
		p.contacto_telefono,
		p.contacto_email
	FROM cont_propietario pr
	INNER JOIN cont_persona p ON pr.persona_id = p.id
	INNER JOIN cont_tipo_persona tp ON p.tipo_persona_id = tp.id
	INNER JOIN cont_tipo_docu_identidad td ON p.tipo_docu_identidad_id = td.id
	WHERE pr.contrato_id IN (" . $contrato_id . ");");
	while($sel = $sel_query->fetch_assoc()){

	$body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
	$body .= '<tr>';
	$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Datos del Propietario</th>';
	$body .= '</tr>';
	$body .= '<tr>';
	$body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Tipo de Persona</b></td>';
	$body .= '<td>' . $sel["tipo_persona"] . '</td>';
	$body .= '</tr>';
	$body .= '<tr>';
	$body .= '<td style="background-color:#ffffdd;"><b>Nombre</b></td>';
	$body .= '<td>' . $sel["nombre"] . '</td>';
	$body .= '</tr>';
	$body .= '<tr>';
	$body .= '<td style="background-color:#ffffdd;"><b>Tipo de Documento de Identidad</b></td>';
	$body .= '<td>' . $sel["tipo_docu_identidad"] . '</td>';
	$body .= '</tr>';
	$body .= '<tr>';
	$body .= '<td style="background-color:#ffffdd;"><b>Número de Documento de Identidad</b></td>';
	$body .= '<td>' . $sel["num_docu"] . '</td>';
	$body .= '</tr>';
	$body .= '<tr>';
	$body .= '<td style="background-color:#ffffdd;"><b>Domicilio del propietario</b></td>';
	$body .= '<td>' . $sel["direccion"] . '</td>';
	$body .= '</tr>';
	$body .= '<tr>';
	$body .= '<td style="background-color:#ffffdd;"><b>Representante Legal</b></td>';
	$body .= '<td>' . $sel["representante_legal"] . '</td>';
	$body .= '</tr>';
	$body .= '<tr>';
	$body .= '<td style="background-color:#ffffdd;"><b>N° de Partida Registral de la empresa</b></td>';
	$body .= '<td>' . $sel["num_partida_registral"] . '</td>';
	$body .= '</tr>';
	$body .= '<tr>';
	$body .= '<td style="background-color:#ffffdd;"><b>Persona de contacto</b></td>';
	$body .= '<td>' . $sel["contacto_nombre"] . '</td>';
	$body .= '</tr>';
	$body .= '<tr>';
	$body .= '<td style="background-color:#ffffdd;"><b>Teléfono de la persona de contacto</b></td>';
	$body .= '<td>' . $sel["contacto_telefono"] . '</td>';
	$body .= '</tr>';
	$body .= '<tr>';
	$body .= '<td style="background-color:#ffffdd;"><b>E-mail de la persona de contacto</b></td>';
	$body .= '<td>' . $sel["contacto_email"] . '</td>';
	$body .= '</tr>';
	$body .= '</table>';

	}

	$body .= '<br>';

	$sel_query = $mysqli->query("SELECT
		i.id,
		ude.nombre AS departamento, 
		upr.nombre AS provincia,
		udi.nombre AS distrito,
		i.ubicacion,
		i.area_arrendada,
		i.num_partida_registral,
		i.oficina_registral,
		i.num_suministro_agua,
		i.tipo_compromiso_pago_agua,
		i.monto_o_porcentaje_agua,
		i.num_suministro_luz,
		i.tipo_compromiso_pago_luz,
		i.monto_o_porcentaje_luz,
		i.tipo_compromiso_pago_arbitrios,
		i.porcentaje_pago_arbitrios
	FROM cont_inmueble i
	INNER JOIN tbl_ubigeo ude ON SUBSTRING(i.ubigeo_id, 1, 2) = ude.cod_depa AND ude.cod_prov = '00' AND ude.cod_dist = '00'
	INNER JOIN tbl_ubigeo upr ON SUBSTRING(i.ubigeo_id, 1, 2) = upr.cod_depa AND SUBSTRING(i.ubigeo_id, 3, 2) = upr.cod_prov AND upr.cod_dist = '00'
	INNER JOIN tbl_ubigeo udi ON SUBSTRING(i.ubigeo_id, 1, 2) = udi.cod_depa AND SUBSTRING(i.ubigeo_id, 3, 2) = udi.cod_prov AND SUBSTRING(i.ubigeo_id, 5, 2) = udi.cod_dist
	WHERE i.contrato_id = " . $contrato_id . ";");
	while($sel=$sel_query->fetch_assoc()){

	$body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
	$body .= '<tr>';
	$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Datos del Inmueble</th>';
	$body .= '</tr>';
	$body .= '<tr>';
	$body .= '<tr>';
	$body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Departamento</b></td>';
	$body .= '<td>' . $sel["departamento"] . '</td>';
	$body .= '</tr>';
	$body .= '<tr>';
	$body .= '<td style="background-color:#ffffdd;"><b>Provincia</b></td>';
	$body .= '<td>' . $sel["provincia"] . '</td>';
	$body .= '</tr>';
	$body .= '<tr>';
	$body .= '<td style="background-color:#ffffdd;"><b>Distrito</b></td>';
	$body .= '<td>' . $sel["distrito"] . '</td>';
	$body .= '</tr>';
	$body .= '<tr>';
	$body .= '<td style="background-color:#ffffdd;"><b>Ubicación</b></td>';
	$body .= '<td>' . $sel["ubicacion"] . '</td>';
	$body .= '</tr>';

	$body .= '</table>';

	}

	$body .= '<br>';
	/*
	$sel_query = $mysqli->query("SELECT 
		c.participacion_id,
		c.porcentaje_participacion,
		c.condicion_comercial_id, 
		p.nombre as nombre_participacion,
		i.nombre as nombre_condicion,
		c.periodo_numero,
		c.periodo,
		r.nombre as nombre_periodo
	FROM cont_contrato c
	INNER JOIN cont_participaciones p ON c.participacion_id = p.id 
	INNER JOIN cont_condiciones_comerciales i ON c.condicion_comercial_id = i.id
	INNER JOIN cont_periodo r ON c.periodo = r.id
	WHERE c.contrato_id = " . $contrato_id);
	while($row=$sel_query->fetch_assoc()){                                
		$nombre_participacion = $row["nombre_participacion"];
		$porcentaje_participacion = $row["porcentaje_participacion"];
		$nombre_condicion = $row["nombre_condicion"];
		  
		$periodo_numero = $row["periodo_numero"];
		$nombre_periodo = $row["nombre_periodo"];
			
			
	}

	$body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
	$body .= '<tr>';
	$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Condiciones Comerciales</th>';
	$body .= '</tr>';
	$body .= '<tr>';
	$body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Tipo</b></td>';
	$body .= '<td>' . $nombre_participacion . '</td>';
	$body .= '</tr>';
	$body .= '<tr>';
	$body .= '<td style="background-color:#ffffdd;"><b>Porcentaje de Participación</b></td>';
	$body .= '<td>' . $porcentaje_participacion . '</td>';
	$body .= '</tr>';
	$body .= '<tr>';
	$body .= '<td style="background-color:#ffffdd;"><b>Condición</b></td>';
	$body .= '<td>' . $nombre_condicion . '</td>';
	$body .= '</tr>';
	$body .= '</table>';
	*/

	$body .= '<br>';
/*
	if ((int) $bien_entregado == "SI") {
		$body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
		$body .= '<tr>';
		$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Bienes Entregados</th>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;"><b>Detalle del Bien Entregado</b></td>';
		$body .= '<td>' . $detalle_bien_entradado . '</td>';
		$body .= '</tr>';
		$body .= '</table>';
		$body .= '<br>';
	}
*/
	$body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
	$body .= '<tr>';
	$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Plazo</th>';
	$body .= '</tr>';
	$body .= '<tr>';
	$body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Periodo</b></td>';
	$body .= '<td>' .$periodo_numero.' '. $nombre_periodo . '</td>';
	$body .= '</tr>';
	$body .= '</table>';

	$body .= '<br>';

	$pre_asunto = '';
	if ($reenvio) {
		$pre_asunto = 'Reenvío - ';
		$usuario_id = $login?$login['id']:null;
		$created_at = date('Y-m-d H:i:s');

		$query_insert = "INSERT INTO cont_emails_enviados(
		contrato_id,
		tipo_email_enviado_id,
		status,
		user_created_id,
		created_at)
		VALUES(
		" . $contrato_id . ",
		1,
		1,
		" . $usuario_id . ",
		'" . $created_at . "' )";

		$mysqli->query($query_insert);
	}

	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_solicitud_contrato_agente_detallado([]);

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];

	if($_POST['correos_adjuntos'] != ""){
		$validate = true;
		$emails = preg_split('[,|;]',$_POST['correos_adjuntos']);
		foreach($emails as $e){
		    if(preg_match('/^[_ña-z0-9-]+(\.[_ña-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/',trim($e)) == 0){
				$error_msg = "'" . $e . "'" ." no es un Correo Adjunto válido";
				if($e == "")
				{
					$error_msg = "Formato de Correo Incorrecto";
				}
		        $result["http_code"] = 500;
				$result["status"] = "error";
				$result["mensaje"] = $error_msg;
				echo json_encode($result);
				die;
		    }
		    else
		    {
				$cc[] = $e;
		    }
		}
	}

	$bcc = $lista_correos['bcc'];
	

	$request = [
		"subject" => $pre_asunto . "Gestion - Sistema Contratos - Nueva Solicitud de Agente : Código - " .$sigla_correlativo.$codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try 
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;        
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc) 
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc) 
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();
		//return true;

		if ($enviar_respuesta) {
			$result["http_code"] = 200;
			$result["status"] = "Datos obtenidos de gestion.";
			echo json_encode($result);
			exit();
		}		
	} 
	catch (Exception $e) 
	{
		if ($enviar_respuesta) {
			$result["http_code"] = 400;
			$result["status"] = "Datos obtenidos de gestion.";
			$result["error"] = $mail->ErrorInfo;
			echo json_encode($result);
			exit();
		}
	}

}

 


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_condiciones_comerciales_de_agentes") {

	$query = "
	SELECT
		id,
		nombre
	FROM 
		cont_condiciones_comerciales
	WHERE 
		status = 1";
	$list_query = $mysqli->query($query);
	$list = array();

	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	
	if(count($list) == 0){
		$result["http_code"] = 400;
		$result["result"] = "Las condiciones comerciales no existen.";
	} elseif (count($list) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $list;
	} else {
		$result["http_code"] = 400;
		$result["result"] = "La condición economica no existe.";
	}
}


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_plazo_agente") {

	$query = "
	SELECT
		id,
		nombre
	FROM 
		cont_plazo
	WHERE 
		status = 1";
	$list_query = $mysqli->query($query);
	$list = array();

	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	
	if(count($list) == 0){
		$result["http_code"] = 400;
		$result["result"] = "Los plazos no existen.";
	} elseif (count($list) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $list;
	} else {
		$result["http_code"] = 400;
		$result["result"] = "El plazo no existe.";
	}
}


 

if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_estado_contrato") 
{
	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");

	$estado = $_POST["estado"];
	$id_contrato = $_POST["id_contrato"];
	

	$query_update = "
	UPDATE tbl_contratos
	SET 
		estado = " . $estado . ",
	WHERE id = ". $id_contrato ."
	";
	$mysqli->query($query_update);

	$error = '';
	if($mysqli->error){
		$error=$mysqli->error;
	}

	$result["http_code"] = 200;
	$result["status"] = "Se han guardado correctamente el estado.";
	$result["result"] = 'ok';
	$result["error"] = $error;
}


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_tipo_fecha_vencimiento") {


	$query = "SELECT id, nombre";
	$query .= " FROM cont_tipo_fecha_vencimiento";
	$query .= " WHERE status = 1";
	
	$list_query = $mysqli->query($query);
	$list = array();
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	
	if(count($list) == 0){
		$result["http_code"] = 400;
		$result["result"] = "El área no existe.";
	} elseif (count($list) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $list;
	} else {
		$result["http_code"] = 400;
		$result["result"] = "El área no existe.";
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="send_email_solicitud_contrato_agente") {
	try {
		send_email_solicitud_contrato_agente($_POST['contrato_id'], [], false);
		$result["status"] = 200;
		$result["message"] = "Se ha enviado correctamente el correo de la solicitud de contrato de agente.";
		$result["result"] = '';
		echo json_encode($result);
		exit();
	}catch (Exception $e){
		$result["status"] = 400;
		$result["message"] = "A ocurrido un error";
		$result["result"] = '';
		echo json_encode($result);
		exit();
	}
}


if (isset($_POST["accion"]) && $_POST["accion"]==="reenviar_email_solicitud_agente") {
	try {
		send_email_solicitud_contrato_agente($_POST['contrato_id'], [], true);
		$result["status"] = 200;
		$result["message"] = "Se ha enviado correctamente el correo de la solicitud de contrato de agente.";
		$result["result"] = '';
		echo json_encode($result);
		exit();
	}catch (Exception $e){
		$result["status"] = 400;
		$result["message"] = "A ocurrido un error";
		$result["result"] = '';
		echo json_encode($result);
		exit();
	}
}



echo json_encode($result);
?>
