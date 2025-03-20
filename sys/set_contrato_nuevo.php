<?php
date_default_timezone_set("America/Lima");

$result = array();
include("db_connect.php");
include("sys_login.php");

include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';

require_once '/var/www/html/env.php';
include '/var/www/html/sys/envio_correos.php';
include '/var/www/html/sys/set_contrato_seguimiento_proceso.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_personal_segun_area") {

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

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list) == 0) {
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

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_areas") {

	$query = "SELECT  ta.id, ta.nombre from tbl_areas ta where ta.estado = 1";
	$list_query = $mysqli->query($query);

	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}


if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_abogados") {

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
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}


if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_departamentos") {
	$query = "SELECT nombre, cod_depa AS id";
	$query .= " FROM tbl_ubigeo";
	$query .= " WHERE cod_prov = '00' AND cod_dist = '00' AND estado = '1'";
	$query .= " ORDER BY nombre ASC";

	$list_query = $mysqli->query($query);
	$list = array();
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list) == 0) {
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

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_empresas_luz") {
	$query = "SELECT nombre_comercial as nombre, id";
	$query .= " FROM cont_local_servicio_publico_empresas";
	$query .= " WHERE status = 1";
	$query .= " ORDER BY nombre ASC";

	$list_query = $mysqli->query($query);
	$list = array();
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list) == 0) {
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

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_empresas_agua") {
	$query = "SELECT nombre_comercial as nombre, id";
	$query .= " FROM cont_local_servicio_publico_empresas";
	$query .= " WHERE status = 1";
	$query .= " ORDER BY nombre ASC";

	$list_query = $mysqli->query($query);
	$list = array();
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list) == 0) {
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

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_vigencia") {

	$query = "SELECT id, nombre";
	$query .= " FROM cont_tipo_plazo";
	$query .= " WHERE status = 1";

	$list_query = $mysqli->query($query);
	$list = array();
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list) == 0) {
		$result["http_code"] = 400;
		$result["result"] = "No hay tipo de plazos.";
	} elseif (count($list) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $list;
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_provincias_segun_departamento") {

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

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list) == 0) {
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

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_distritos_segun_provincia") {

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

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list) == 0) {
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


if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_contratos_proveedor") {

	$num_ruc_proveedor = $_POST["num_ruc_proveedor"];

	$query = "SELECT c.contrato_id, c.fecha_inicio, c.detalle_servicio
	FROM cont_contrato c
	WHERE c.ruc = " . $num_ruc_proveedor . "";

	$list_query = $mysqli->query($query);
	$list = array();
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list) == 0) {
		$result["http_code"] = 400;
		$result["result"] = "El contrato no existe.";
	} elseif (count($list) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $list;
	} else {
		$result["http_code"] = 400;
		$result["result"] = "El contrato no existe.";
	}
}


if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_contrato_proveedor_por_id") {

	$id_contrato_proveedor = $_POST["id_contrato_proveedor"];

	$list_emp_cont = $mysqli->query("SELECT tpg.valor FROM tbl_parametros_generales tpg WHERE tpg.codigo = 'empresa_contacto_de_contratos' AND tpg.estado = 1 LIMIT 1");
	$row_emp_cont = $list_emp_cont->fetch_assoc();
	$valor_empresa_contacto = isset($row_emp_cont['valor']) && $row_emp_cont['valor'] == 'Kurax' ? '(Kurax)' : '(AT)';

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
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Datos generales\',\'cont_contrato\',\'empresa_suscribe_id\',\'Empresa Contratante\',\'select_option\',\'' . $empresa_suscribe . '\',\'obtener_empresa_at\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Persona Contacto ' . $valor_empresa_contacto . '</b></td>';
	$html .= '<td>' . $persona_contacto_proveedor . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_contrato\',\'persona_contacto_proveedor\',\'Persona Contacto ' . $valor_empresa_contacto . '\',\'varchar\',\'' . $persona_contacto_proveedor . '\',\'\',\'\');">';
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
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_contrato\',\'ruc\',\'Número de RUC\',\'varchar\',\'' . $ruc . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Razón Social</b></td>';
	$html .= '<td>' . $razon_social . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_contrato\',\'razon_social\',\'Razón Social\',\'varchar\',\'' . $razon_social . '\',\'\',\'\');">';
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
	$html .= 'onclick="sec_contrato_nuevo_proveedor_modal_ap(' . $id_contrato_proveedor . ',\'Nuevo\')">';
	$html .= '<span class="fa fa-edit"></span> Agregar Representante Legal';
	$html .= '</a>';

	$html .= '</div>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="form-group">';
	$html .= '<table class="table table-bordered table-hover">';

	$sel_query = $mysqli->query(
		"
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
		$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_contrato\',\'dni_representante\',\'DNI del representante legal\',\'varchar\',\'' . $dni_representante . '\',\'\',\'\');">';
		$html .= '<span class="fa fa-edit"></span> Editar';
		$html .= '</a>';
		$html .= '</td>';
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= '<td><b>Nombre completo del representante legal</b></td>';
		$html .= '<td>' . $nombre_representante . '</td>';
		$html .= '<td>';
		$html .= '<a class="btn btn-success btn-xs" ';
		$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_contrato\',\'nombre_representante\',\'Nombre completo del representante legal\',\'varchar\',\'' . $nombre_representante . '\',\'\',\'\');">';
		$html .= '<span class="fa fa-edit"></span> Editar';
		$html .= '</a>';
		$html .= '</td>';
		$html .= '</tr>';
	} elseif ($row_count > 0) {
		while ($sel = $sel_query->fetch_assoc()) {
			$c = $c + 1;
			$id_representante_legal = $sel["id"];

			$html .= '<tr>';
			$html .= '<td style="width: 50%;"><b>DNI del representante legal</b></td>';
			$html .= '<td>' . $sel["dni_representante"] . '</td>';
			$html .= '<td style="width: 75px;">';
			$html .= '<a class="btn btn-success btn-xs" ';
			$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_representantes_legales\',\'dni_representante\',\'DNI del representante legal\',\'varchar\',\'' . $sel["dni_representante"] . '\',\'\',\'' . $sel["id"] . '\');">';
			$html .= '<span class="fa fa-edit"></span> Editar';
			$html .= '</a>';
			$html .= '</td>';
			$html .= '</tr>';

			$html .= '<tr>';
			$html .= '<td><b>Nombre completo del representante legal</b></td>';
			$html .= '<td>' . $sel["nombre_representante"] . '</td>';
			$html .= '<td>';
			$html .= '<a class="btn btn-success btn-xs" ';
			$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_representantes_legales\',\'nombre_representante\',\'Nombre completo del representante legal\',\'varchar\',\'' . $sel["nombre_representante"] . '\',\'\',\'' . $sel["id"] . '\');">';
			$html .= '<span class="fa fa-edit"></span> Editar';
			$html .= '</a>';
			$html .= '</td>';
			$html .= '</tr>';

			$html .= '<tr>';
			$html .= '<td><b>Número de cuenta de detracción (Banco de la Nación)</b></td>';
			$html .= '<td>' . $sel["nro_cuenta_detraccion"] . '</td>';
			$html .= '<td>';
			$html .= '<a class="btn btn-success btn-xs" ';
			$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_representantes_legales\',\'nro_cuenta_detraccion\',\'Número de cuenta de detracción (Banco de la Nación)\',\'varchar\',\'' . $sel["nro_cuenta_detraccion"] . '\',\'\',\'' . $sel["id"] . '\');">';
			$html .= '<span class="fa fa-edit"></span> Editar';
			$html .= '</a>';
			$html .= '</td>';
			$html .= '</tr>';

			$html .= '<tr>';
			$html .= '<td><b>Banco</b></td>';
			$html .= '<td>' . $sel["banco_representante"] . '</td>';
			$html .= '<td>';
			$html .= '<a class="btn btn-success btn-xs" ';
			$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_representantes_legales\',\'id_banco\',\'Banco\',\'select_option\',\'' . $sel["banco_representante"] . '\',\'obtener_banco\',\'' . $sel["id"] . '\');">';
			$html .= '<span class="fa fa-edit"></span> Editar';
			$html .= '</a>';
			$html .= '</td>';
			$html .= '</tr>';

			$html .= '<tr>';
			$html .= '<td><b>Número de cuenta</b></td>';
			$html .= '<td>' . $sel["nro_cuenta"] . '</td>';
			$html .= '<td>';
			$html .= '<a class="btn btn-success btn-xs" ';
			$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_representantes_legales\',\'nro_cuenta\',\'Número de cuenta\',\'varchar\',\'' . $sel["nro_cuenta"] . '\',\'\',\'' . $sel["id"] . '\');">';
			$html .= '<span class="fa fa-edit"></span> Editar';
			$html .= '</a>';
			$html .= '</td>';
			$html .= '</tr>';

			$html .= '<tr>';
			$html .= '<td><b>Número CCI</b></td>';
			$html .= '<td>' . $sel["nro_cci"] . '</td>';
			$html .= '<td>';
			$html .= '<a class="btn btn-success btn-xs" ';
			$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Información del proveedor\',\'cont_representantes_legales\',\'nro_cci\',\'Número CCI\',\'varchar\',\'' . $sel["nro_cci"] . '\',\'\',\'' . $sel["id"] . '\');">';
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
	$html .= '<div class="h4"><b>CONDICIONES COMERCIALES</b></div>';
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
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Objeto del Contrato\',\'cont_contrato\',\'detalle_servicio\',\'Detalle de servicio a contratar\',\'varchar\',\'' . $detalle_servicio . '\',\'\',\'\');">';
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
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Plazo del Contrato\',\'cont_contrato\',\'periodo_numero\',\'Periodo (Número)\',\'int\',\'' . $periodo_numero . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td style="width: 50%;"><b>Periodo - Año o Mes</b></td>';
	$html .= '<td>' . $periodo_anio_mes . '</td>';
	$html .= '<td style="width: 75px;">';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Plazo del Contrato\',\'cont_contrato\',\'periodo\',\'Periodo (Año o Mes)\',\'select_option\',\'' . $periodo_anio_mes . '\',\'obtener_periodo\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Fecha de inicio</b></td>';
	$html .= '<td>' . $fecha_inicio . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Plazo del Contrato\',\'cont_contrato\',\'fecha_inicio\',\'Fecha de inicio\',\'date\',\'' . $fecha_inicio . '\',\'\',\'\');">';
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
	$html .= 'onclick="sec_contrato_nuevo_contraprestacion_modal_ap(' . $id_contrato_proveedor . ',\'Nuevo\')">';
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
		while ($sel = $query->fetch_assoc()) {
			$contraprestacion_id = $sel["id"];
			$tipo_moneda = $sel["tipo_moneda"];
			$tipo_moneda_simbolo = $sel["tipo_moneda_simbolo"];
			$subtotal = $tipo_moneda_simbolo . ' ' . number_format($sel["subtotal"], 2, '.', ',');
			$igv = $tipo_moneda_simbolo . ' ' . number_format($sel["igv"], 2, '.', ',');
			$monto = $tipo_moneda_simbolo . ' ' . number_format($sel["monto"], 2, '.', ',');
			$forma_pago_detallado = $sel["forma_pago_detallado"];
			$tipo_comprobante = $sel["tipo_comprobante"];
			$plazo_pago = $sel["plazo_pago"];

			$html .= '<tr>';
			$html .= '<td style="width: 50%;"><b>Tipo de moneda</b></td>';
			$html .= '<td>' . $tipo_moneda . '</td>';
			$html .= '<td style="width: 75px;">';
			$html .= '<a class="btn btn-success btn-xs" ';
			$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contraprestacion\',\'moneda_id\',\'Tipo de moneda\',\'select_option\',\'' . $tipo_moneda . '\',\'obtener_monedas\',\'' . $contraprestacion_id . '\');">';
			$html .= '<span class="fa fa-edit"></span> Editar';
			$html .= '</a>';
			$html .= '</td>';
			$html .= '</tr>';

			$html .= '<tr>';
			$html .= '<td style="width: 50%;"><b>Subtotal</b></td>';
			$html .= '<td>' . $subtotal . '</td>';
			$html .= '<td style="width: 75px;">';
			$html .= '<a class="btn btn-success btn-xs" ';
			$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contraprestacion\',\'subtotal\',\'Subtotal\',\'decimal\',\'' . $subtotal . '\',\'\',\'' . $contraprestacion_id . '\');">';
			$html .= '<span class="fa fa-edit"></span> Editar';
			$html .= '</a>';
			$html .= '</td>';
			$html .= '</tr>';

			$html .= '<tr>';
			$html .= '<td style="width: 50%;"><b>IGV</b></td>';
			$html .= '<td>' . $igv . '</td>';
			$html .= '<td style="width: 75px;">';
			$html .= '<a class="btn btn-success btn-xs" ';
			$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contraprestacion\',\'igv\',\'IGV\',\'decimal\',\'' . $igv . '\',\'\',\'' . $contraprestacion_id . '\');">';
			$html .= '<span class="fa fa-edit"></span> Editar';
			$html .= '</a>';
			$html .= '</td>';
			$html .= '</tr>';

			$html .= '<tr>';
			$html .= '<td style="width: 50%;"><b>Monto Bruto</b></td>';
			$html .= '<td>' . $monto . '</td>';
			$html .= '<td style="width: 75px;">';
			$html .= '<a class="btn btn-success btn-xs" ';
			$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contraprestacion\',\'monto\',\'Monto Bruto\',\'decimal\',\'' . $monto . '\',\'\',\'' . $contraprestacion_id . '\');">';
			$html .= '<span class="fa fa-edit"></span> Editar';
			$html .= '</a>';
			$html .= '</td>';
			$html .= '</tr>';

			$html .= '<tr>';
			$html .= '<td><b>Tipo de comprobante a emitir</b></td>';
			$html .= '<td>' . $tipo_comprobante . '</td>';
			$html .= '<td>';
			$html .= '<a class="btn btn-success btn-xs" ';
			$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contraprestacion\',\'tipo_comprobante_id\',\'Tipo de comprobante a emitir\',\'select_option\',\'' . $tipo_comprobante . '\',\'obtener_tipo_comprobante\',\'' . $contraprestacion_id . '\');">';
			$html .= '<span class="fa fa-edit"></span> Editar';
			$html .= '</a>';
			$html .= '</td>';
			$html .= '</tr>';

			$html .= '<tr>';
			$html .= '<td><b>Plazo de Pago</b></td>';
			$html .= '<td>' . $plazo_pago . '</td>';
			$html .= '<td>';
			$html .= '<a class="btn btn-success btn-xs" ';
			$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contraprestacion\',\'plazo_pago\',\'Plazo de Pago\',\'varchar\',\'' . $plazo_pago . '\',\'\',\'' . $contraprestacion_id . '\');">';
			$html .= '<span class="fa fa-edit"></span> Editar';
			$html .= '</a>';
			$html .= '</td>';
			$html .= '</tr>';

			$html .= '<tr>';
			$html .= '<td><b>Forma de pago</b></td>';
			$html .= '<td>' . $forma_pago_detallado . '</td>';
			$html .= '<td>';
			$html .= '<a class="btn btn-success btn-xs" ';
			$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contraprestacion\',\'forma_pago_detallado\',\'Forma de pago\',\'varchar\',\'' . $forma_pago_detallado . '\',\'\',\'' . $contraprestacion_id . '\');">';
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
		$monto = $tipo_moneda_simbolo . ' ' . number_format($sel["monto"], 2, '.', ',');
		$forma_pago = $sel["forma_pago"];
		$tipo_comprobante = $sel["tipo_comprobante"];
		$plazo_pago = $sel["plazo_pago"];

		$html .= '<tr>';
		$html .= '<td style="width: 50%;"><b>Monto</b></td>';
		$html .= '<td>' . $monto . '</td>';
		$html .= '<td style="width: 75px;">';
		$html .= '<a class="btn btn-success btn-xs" ';
		$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contrato\',\'monto\',\'Monto\',\'decimal\',\'' . $monto . '\',\'\',\'\');">';
		$html .= '<span class="fa fa-edit"></span> Editar';
		$html .= '</a>';
		$html .= '</td>';
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= '<td><b>Forma de pago</b></td>';
		$html .= '<td>' . $forma_pago . '</td>';
		$html .= '<td>';
		$html .= '<a class="btn btn-success btn-xs" ';
		$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contrato\',\'forma_pago\',\'Forma de pago\',\'select_option\',\'' . $forma_pago . '\',\'obtener_forma_pago\',\'\');">';
		$html .= '<span class="fa fa-edit"></span> Editar';
		$html .= '</a>';
		$html .= '</td>';
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= '<td><b>Tipo de comprobante a emitir</b></td>';
		$html .= '<td>' . $tipo_comprobante . '</td>';
		$html .= '<td>';
		$html .= '<a class="btn btn-success btn-xs" ';
		$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contrato\',\'tipo_comprobante\',\'Tipo de comprobante a emitir\',\'select_option\',\'' . $tipo_comprobante . '\',\'obtener_tipo_comprobante\',\'\');">';
		$html .= '<span class="fa fa-edit"></span> Editar';
		$html .= '</a>';
		$html .= '</td>';
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= '<td><b>Plazo de Pago</b></td>';
		$html .= '<td>' . $plazo_pago . '</td>';
		$html .= '<td>';
		$html .= '<a class="btn btn-success btn-xs" ';
		$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Contraprestación\',\'cont_contrato\',\'plazo_pago\',\'Plazo de Pago\',\'varchar\',\'' . $plazo_pago . '\',\'\',\'\');">';
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
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Alcance del servicio\',\'cont_contrato\',\'alcance_servicio\',\'Alcance del servicio\',\'varchar\',\'' . $alcance_servicio . '\',\'\',\'\');">';
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
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Terminación Anticipada\',\'cont_contrato\',\'tipo_terminacion_anticipada_id\',\'Tipo de Terminación Anticipada\',\'select_option\',\'' . $tipo_terminacion_anticipada . '\',\'obtener_tipo_terminacion_anticipada\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	if ($tipo_terminacion_anticipada_id == "1") {
		$html .= '<tr>';
		$html .= '<td>' . $terminacion_anticipada . '</td>';
		$html .= '<td style="width: 75px;">';
		$html .= '<a class="btn btn-success btn-xs"';
		$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Terminación Anticipada\',\'cont_contrato\',\'terminacion_anticipada\',\'Terminación Anticipada - Detalle\',\'varchar\',\'' . $terminacion_anticipada . '\',\'\',\'\');">';
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
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Observaciones\',\'cont_contrato\',\'observaciones\',\'Observaciones\',\'varchar\',\'' . $observaciones . '\',\'\',\'\');">';
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


if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_contrato_arrendamiento_por_id") {

	$id_contrato_arrendamiento = $_POST["id_contrato_arrendamiento"];


	$sel_query = $mysqli->query("
					SELECT 
						c.empresa_suscribe_id,
						r.nombre AS empresa_suscribe,
						c.persona_responsable_id,
						c.verificar_giro,
						c.fecha_verificacion_giro,
						c.usuario_verificacion_giro,
						CONCAT(IFNULL(pgiro.nombre, ''),' ',IFNULL(pgiro.apellido_paterno, ''),' ',IFNULL(pgiro.apellido_materno, '')) AS persona_verificaciongiro,
						CONCAT(IFNULL(p2.nombre, ''),' ',IFNULL(p2.apellido_paterno, ''),' ',IFNULL(p2.apellido_materno, '')) AS persona_responsable,
						CONCAT(IFNULL(pjc.nombre, ''),' ',IFNULL(pjc.apellido_paterno, ''),' ',IFNULL(pjc.apellido_materno, '')) AS jefe_comercial,
						c.jefe_comercial_id,
						c.nombre_tienda,
						c.observaciones,
						c.user_created_id,
						CONCAT(IFNULL(p.nombre, ''),' ',IFNULL(p.apellido_paterno, ''),' ',IFNULL(p.apellido_materno, '')) AS user_created,
						c.created_at,
						c.cc_id
					FROM
						cont_contrato c
						INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id
						INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
						INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
						LEFT JOIN tbl_usuarios u2 ON c.persona_responsable_id = u2.id
						LEFT JOIN tbl_personal_apt p2 ON u2.personal_id = p2.id
						LEFT JOIN tbl_usuarios ugiro ON c.usuario_verificacion_giro = ugiro.id
						LEFT JOIN tbl_personal_apt pgiro ON ugiro.personal_id = pgiro.id
						LEFT JOIN tbl_usuarios ujc ON c.jefe_comercial_id = ujc.id
						LEFT JOIN tbl_personal_apt pjc ON ujc.personal_id = pjc.id
					WHERE 
						c.contrato_id IN (" . $id_contrato_arrendamiento . ")");
	while ($sel = $sel_query->fetch_assoc()) {
		$empresa_suscribe = $sel["empresa_suscribe"];
		$nombre_tienda = $sel["nombre_tienda"];
		$observaciones = $sel["observaciones"];
		$supervisor = trim($sel["persona_responsable"]);
		$jefe_comercial = trim($sel["jefe_comercial"]);
		$usuario_created_id = $sel["user_created_id"];
		$verificar_giro = $sel["verificar_giro"];
		$fecha_verificacion_giro = $sel["fecha_verificacion_giro"];
		$usuario_verificacion_giro = $sel["persona_verificaciongiro"];
		$centro_de_costos = $sel["cc_id"];

		if (empty($nombre_tienda)) {
			$nombre_tienda = 'Sin asignar';
		}

		if (empty($centro_de_costos)) {
			$centro_de_costos = 'Sin asignar';
		}

		if (empty($supervisor)) {
			$supervisor = 'Sin asignar';
		}

		if (empty($jefe_comercial)) {
			$jefe_comercial = 'Sin asignar';
		}
	}

	$tipo_persona = "";
	$html = '';
	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="h4">';
	$html .= '<b>DATOS GENERALES</b>&nbsp;';
	$html .= '</div>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="form-group">';
	$html .= '<table class="table table-bordered table-hover">';

	$html .= '<tr>';
	$html .= '<td><b>Nombre de la Tienda</b></td>';
	$html .= '<td>' . $nombre_tienda . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Datos Generales\',\'tbl_razon_social\',\'nombre\',\'Nombre\',\'varchar\',\'' . $nombre_tienda . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Centro de Costos</b></td>';
	$html .= '<td>' . $centro_de_costos . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Datos Generales\',\'cont_contrato\',\'cc_id\',\'Centro de Costos\',\'varchar\',\'' . $centro_de_costos . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Empresa Arrendataria</b></td>';
	$html .= '<td>' . $empresa_suscribe . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Datos Generales\',\'cont_contrato\',\'empresa_suscribe_id\',\'Empresa Arrendaria\',\'select_option\',\'' . $empresa_suscribe . '\',\'obtener_empresa_at\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Supervisor</b></td>';
	$html .= '<td>' . $supervisor . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Datos Generales\',\'cont_contrato\',\'persona_responsable_id\',\'Supervisor\',\'select_option\',\'' . $supervisor . '\',\'obtener_personal_responsable\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Jefe Comercial</b></td>';
	$html .= '<td>' . $jefe_comercial . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Datos Generales\',\'cont_contrato\',\'jefe_comercial_id\',\'Jefe Comercial\',\'select_option\',\'' . $jefe_comercial . '\',\'obtener_personal_responsable\',\'\');">';
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








	$query = "SELECT pr.propietario_id,
		tp.nombre AS tipo_persona,
		td.nombre AS tipo_docu_identidad,
		p.id AS persona_id,
		p.num_docu,
		p.num_ruc,
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
	WHERE pr.contrato_id = " . $id_contrato_arrendamiento . "";

	$sel_query = $mysqli->query($query);
	$row = $sel_query->fetch_assoc();

	$propietario_id = $row["propietario_id"];
	$persona_id = $row["persona_id"];
	$tipo_persona = $row["tipo_persona"];
	$tipo_docu_identidad = $row["tipo_docu_identidad"];
	$num_docu = $row["num_docu"];
	$num_ruc = $row["num_ruc"];
	$nombre = $row["nombre"];
	$direccion = $row["direccion"];
	$representante_legal = $row["representante_legal"];
	$num_partida_registral_per = $row["num_partida_registral"];
	$contacto_nombre = $row["contacto_nombre"];
	$contacto_telefono = $row["contacto_telefono"];
	$contacto_email = $row["contacto_email"];



	$query = "SELECT
		i.id,
		max(udep.nombre) as department,
		max(up.nombre) as province,
		max(ud.nombre) as district,
		i.ubicacion,
		i.latitud,
		i.longitud,

		i.id_empresa_servicio_agua,
		i.id_empresa_servicio_luz,
		lspe1.razon_social as empresa_servicio_agua,
		lspe2.razon_social as empresa_servicio_luz,

		i.area_arrendada,
		i.ubigeo_id,
		i.num_partida_registral,
		i.oficina_registral,
		i.num_suministro_agua,
		i.tipo_compromiso_pago_agua,
		t1.nombre AS tipo_pago_agua,
		i.monto_o_porcentaje_agua,
		i.num_suministro_luz,
		i.tipo_compromiso_pago_luz,
		t2.nombre AS tipo_pago_luz,
		i.monto_o_porcentaje_luz,
		i.tipo_compromiso_pago_arbitrios,
		ta.nombre AS tipo_pago_arbitrios,
		i.porcentaje_pago_arbitrios
	FROM cont_inmueble i
	INNER JOIN cont_tipo_pago_servicio t1 ON i.tipo_compromiso_pago_agua = t1.id
	INNER JOIN cont_tipo_pago_servicio t2 ON i.tipo_compromiso_pago_luz = t2.id
	INNER JOIN cont_tipo_pago_arbitrios ta ON i.tipo_compromiso_pago_arbitrios = ta.id

	LEFT JOIN cont_local_servicio_publico_empresas lspe1 ON i.id_empresa_servicio_agua = lspe1.id
	LEFT JOIN cont_local_servicio_publico_empresas lspe2 ON i.id_empresa_servicio_luz = lspe2.id

	LEFT JOIN tbl_ubigeo ud ON (
		ud.cod_depa = SUBSTRING(i.ubigeo_id, 1, 2) AND
		ud.cod_prov = SUBSTRING(i.ubigeo_id, 3, 2) AND
		ud.cod_dist = SUBSTRING(i.ubigeo_id, 5, 2)
	)
	LEFT JOIN tbl_ubigeo up ON (
		up.cod_depa = SUBSTRING(i.ubigeo_id, 1, 2) AND
		up.cod_prov = SUBSTRING(i.ubigeo_id, 3, 2) AND
		up.cod_dist = '00'
	)
	LEFT JOIN tbl_ubigeo udep ON (
		udep.cod_depa = SUBSTRING(i.ubigeo_id, 1, 2) AND
		udep.cod_prov = '00' AND
		udep.cod_dist = '00'
	)

	WHERE i.contrato_id = " . $id_contrato_arrendamiento . "";

	$sel_query = $mysqli->query($query);
	$row = $sel_query->fetch_assoc();
	$id = $row["id"];
	$ubigeo_id = $row["ubigeo_id"];
	$departamento = $row["department"];
	$provincia = $row["province"];
	$distrito = $row["district"];
	$ubicacion = $row["ubicacion"];
	$latitud = $row["latitud"];
	$longitud = $row["longitud"];

	$id_empresa_servicio_agua = $row["id_empresa_servicio_agua"];
	$id_empresa_servicio_luz = $row["id_empresa_servicio_luz"];
	$empresa_servicio_agua = $row["empresa_servicio_agua"];
	$empresa_servicio_luz = $row["empresa_servicio_luz"];

	$area_arrendada = $row["area_arrendada"] . ' m2';
	$num_partida_registral = $row["num_partida_registral"];
	$oficina_registral = $row["oficina_registral"];
	$num_suministro_agua = $row["num_suministro_agua"];
	$tipo_compromiso_pago_agua = $row["tipo_compromiso_pago_agua"];
	$tipo_pago_agua = $row["tipo_pago_agua"];
	$monto_o_porcentaje_agua = $row["monto_o_porcentaje_agua"];
	$num_suministro_luz = $row["num_suministro_luz"];
	$tipo_compromiso_pago_luz = $row["tipo_compromiso_pago_luz"];
	$tipo_pago_luz = $row["tipo_pago_luz"];
	$monto_o_porcentaje_luz = $row["monto_o_porcentaje_luz"];
	$tipo_compromiso_pago_arbitrios = $row["tipo_compromiso_pago_arbitrios"];
	$tipo_pago_arbitrios = $row["tipo_pago_arbitrios"];
	$porcentaje_pago_arbitrios = $row["porcentaje_pago_arbitrios"] . '%';





	$query = "SELECT 
		c.condicion_economica_id,
		c.contrato_id,
		c.monto_renta,
		c.tipo_moneda_id,
		m.nombre AS moneda_contrato,
		concat(m.nombre,' (', m.simbolo,')') AS moneda_contrato_con_simbolo,
		m.simbolo AS simbolo_moneda,
		c.impuesto_a_la_renta_id,
		i.nombre AS impuesto_a_la_renta,
		c.carta_de_instruccion_id,
		ci.nombre AS carta_de_instruccion,
		c.numero_cuenta_detraccion,
		c.garantia_monto,
		c.tipo_adelanto_id,
		a.nombre AS tipo_adelanto,
		c.cant_meses_contrato,
		c.fecha_inicio,
		c.fecha_fin,
		c.periodo_gracia_id,
		p.nombre AS periodo_gracia,
		c.periodo_gracia_numero,
		c.periodo_gracia_inicio,
		c.periodo_gracia_fin,
		dp.nombre AS dia_de_pago,
		c.num_alerta_vencimiento,
		c.cargo_mantenimiento,
		c.fecha_suscripcion,
		c.status,
		c.user_created_id,
		c.created_at,
		c.user_updated_id,
		c.updated_at
		FROM cont_condicion_economica c
		INNER JOIN tbl_moneda m ON c.tipo_moneda_id = m.id 
		INNER JOIN cont_tipo_impuesto_a_la_renta i ON c.impuesto_a_la_renta_id = i.id
		INNER JOIN cont_tipo_adelanto a ON c.tipo_adelanto_id = a.id
		LEFT JOIN cont_tipo_periodo_de_gracia p ON c.periodo_gracia_id = p.id
		INNER JOIN cont_tipo_carta_de_instruccion ci ON c.carta_de_instruccion_id = ci.id
		LEFT JOIN cont_tipo_dia_de_pago dp ON c.dia_de_pago_id = dp.id
		WHERE c.contrato_id = " . $id_contrato_arrendamiento;

	$sel_query = $mysqli->query($query);
	$row = $sel_query->fetch_assoc();

	$simbolo_moneda = $row["simbolo_moneda"];
	$moneda_contrato = $row["moneda_contrato"];
	$monto_renta = $simbolo_moneda . ' ' . number_format($row["monto_renta"], 2, '.', ',');
	$garantia_monto = $simbolo_moneda . ' ' . number_format($row["garantia_monto"], 2, '.', ',');

	$monto_renta = $simbolo_moneda . ' ' . number_format($row["monto_renta"], 2, '.', ',');
	$monto_renta_sin_formato = $row["monto_renta"];
	$impuesto_a_la_renta_id = $row["impuesto_a_la_renta_id"];
	$impuesto_a_la_renta_texto = $row["impuesto_a_la_renta"];
	$carta_de_instruccion_id = $row["carta_de_instruccion_id"];
	$carta_de_instruccion = $row["carta_de_instruccion"];
	$numero_cuenta_detraccion = $row["numero_cuenta_detraccion"];

	$cant_meses_contrato = $row["cant_meses_contrato"];
	$fecha_inicio = $row["fecha_inicio"];
	$contrato_inicio_fecha = date("d-m-Y", strtotime($fecha_inicio));
	$fecha_fin = $row["fecha_fin"];
	$contrato_fin_fecha = date("d-m-Y", strtotime($fecha_fin));
	$fecha_suscripcion = $row["fecha_suscripcion"];
	$contrato_fecha_suscripcion = date("d-m-Y", strtotime($fecha_suscripcion));
	$tipo_adelanto = $row["tipo_adelanto"];

	$dia_de_pago = $row["dia_de_pago"];
	$periodo_gracia = $row["periodo_gracia"];
	$periodo_gracia_numero = $row["periodo_gracia_numero"];
	$periodo_gracia_inicio = $row["periodo_gracia_inicio"];
	$periodo_gracia_fin = $row["periodo_gracia_fin"];

	if (empty($cant_meses_contrato)) {
		$cant_meses_contrato = 'Sin asignar';
	} else {
		$cant_meses_contrato = sec_contrato_detalle_solicitud_de_meses_a_anios_y_meses($cant_meses_contrato) . ' (' . $cant_meses_contrato . ' meses)';
	}

	if ($tipo_compromiso_pago_agua == 1) {
		$monto_o_porcentaje_agua = $monto_o_porcentaje_agua . '%';
	} elseif ($tipo_compromiso_pago_agua == 2) {
		$monto_o_porcentaje_agua = $simbolo_moneda . ' ' . $monto_o_porcentaje_agua;
	}

	if ($tipo_compromiso_pago_luz == 1) {
		$monto_o_porcentaje_luz = $monto_o_porcentaje_luz . '%';
	} elseif ($tipo_compromiso_pago_luz == 2) {
		$monto_o_porcentaje_luz = $simbolo_moneda . ' ' . $monto_o_porcentaje_luz;
	}

	// INICIO IMPUESTO A LA RENTA DETALLADO
	$factor = 1.05265;
	$renta_bruta = 0;
	$renta_neta = 0;
	$impuesto_a_la_renta = 0;

	if ($impuesto_a_la_renta_id == 1) {
		$impuesto_a_la_renta = round($monto_renta_sin_formato * 0.05);
		$renta_bruta = $monto_renta_sin_formato;

		if ($carta_de_instruccion_id == 1) {
			$renta_neta = $monto_renta_sin_formato - $impuesto_a_la_renta;
			$quien_paga = 'AT';
		} elseif ($carta_de_instruccion_id == 2) {
			$renta_neta = $monto_renta_sin_formato;
			$quien_paga = 'Arrendador';
		}

		$detalle = 'AT deposita la renta (' . $simbolo_moneda . ' ' . number_format($renta_neta, 2, '.', ',') . ' ' . $moneda_contrato . ') al Arrendador. El ' . $quien_paga . ' realiza el pago del impuesto a la renta (' . $simbolo_moneda . ' ' . number_format($impuesto_a_la_renta, 0, '.', ',') . ' ' . $moneda_contrato . ') a SUNAT.';
	} elseif ($impuesto_a_la_renta_id == 2) {
		$impuesto_a_la_renta = round(($monto_renta_sin_formato * $factor) - $monto_renta_sin_formato);
		$renta_bruta = $monto_renta_sin_formato + round($impuesto_a_la_renta);
		$renta_neta = $monto_renta_sin_formato;

		if ($carta_de_instruccion_id == 1) {
			$renta_neta = $monto_renta_sin_formato;
			$quien_paga = 'AT';
			$detalle = 'AT deposita renta (' . $simbolo_moneda . ' ' . number_format($monto_renta_sin_formato, 2, '.', ',') . ' ' . $moneda_contrato . ') al Arrendador. AT realiza el pago del impuesto a la renta (' . $simbolo_moneda . ' ' . number_format($impuesto_a_la_renta, 0, '.', ',') . ' ' . $moneda_contrato . ') a SUNAT.';
		} elseif ($carta_de_instruccion_id == 2) {
			$renta_neta = $monto_renta_sin_formato + $impuesto_a_la_renta;
			$quien_paga = 'Arrendador';
			$detalle = 'AT deposita ' . $simbolo_moneda . ' ' . number_format($renta_neta, 2, '.', ',') . ' ' . $moneda_contrato . ' al Arrendador. El Arrendador realiza el pago del impuesto a la renta (' . $simbolo_moneda . ' ' . number_format($impuesto_a_la_renta, 0, '.', ',') . ' ' . $moneda_contrato . ') a SUNAT.';
		}
	}

	$impuesto_a_la_renta = $simbolo_moneda . ' ' . number_format($impuesto_a_la_renta, 0, '.', ',') . ' ' . $moneda_contrato;
	$renta_bruta = $simbolo_moneda . ' ' . number_format($renta_bruta, 2, '.', ',') . ' ' . $moneda_contrato;
	$renta_neta = $simbolo_moneda . ' ' . number_format($renta_neta, 2, '.', ',') . ' ' . $moneda_contrato;
	// FIN IMPUESTO A LA RENTA DETALLADO

	$query = "SELECT b.id,
		tp.nombre AS tipo_persona,
		td.nombre AS tipo_docu_identidad,
		b.num_docu,
		b.nombre,
		f.nombre AS forma_pago,
		ba.nombre AS banco,
		b.num_cuenta_bancaria,
		b.num_cuenta_cci,
		b.tipo_monto_id,
		b.monto
	FROM cont_beneficiarios b
	LEFT JOIN cont_tipo_persona tp ON b.tipo_persona_id = tp.id
	LEFT JOIN cont_tipo_docu_identidad td ON b.tipo_docu_identidad_id = td.id
	INNER JOIN cont_forma_pago f ON b.forma_pago_id = f.id
	LEFT JOIN tbl_bancos ba ON b.banco_id = ba.id
	WHERE contrato_id = " . $id_contrato_arrendamiento . "";

	$sel_query = $mysqli->query($query);
	$row = $sel_query->fetch_assoc();

	$beneficiario_id = $row["id"];
	$ben_tipo_persona = $row["tipo_persona"];
	$ben_tipo_docu_identidad = $row["tipo_docu_identidad"];
	$ben_num_docu = $row["num_docu"];
	$ben_nombre = $row["nombre"];
	$ben_direccion = '';
	$ben_forma_pago = $row["forma_pago"];
	$ben_banco = $row["banco"];
	$ben_num_cuenta_bancaria = $row["num_cuenta_bancaria"];
	$ben_num_cuenta_cci = $row["num_cuenta_cci"];
	$ben_tipo_monto_id = $row["tipo_monto_id"];

	if ($ben_tipo_monto_id == 3) {
		$ben_monto_beneficiario = $monto_renta;
	} else {
		$ben_monto_beneficiario = $simbolo_moneda . ' ' . number_format($row["monto"], 2, '.', ',');
	}



	// $list_query = $mysqli->query($query);
	// $list = array();
	// while ($li = $list_query->fetch_assoc()) {
	// 	$list[] = $li;
	// }


	$html .= '<input type="hidden" id="contrato_id" value ="' . $id_contrato_arrendamiento . '"/>';
	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="h4">';
	$html .= '<b>DATOS DEL PROPIETARIO</b>&nbsp;';
	$html .= '<a class="btn btn-success btn-xs" onclick="sec_contrato_nuevo_buscar_propietario_adenda(\'' . $propietario_id . '\' , \'' . $persona_id . '\');"><span class="fa fa-edit"></span> Cambiar de Propietario</a>';
	$html .= '</div>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="form-group">';
	$html .= '<table class="table table-bordered table-hover">';

	$html .= '<tr>';
	$html .= '<td><b>Tipo de Persona</b></td>';
	$html .= '<td>' . $tipo_persona . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'tipo_persona_id\',\'Tipo de Persona\',\'select_option\',\'' . $tipo_persona . '\',\'obtener_tipo_persona\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Nombre</b></td>';
	$html .= '<td>' . $nombre . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'nombre\',\'Nombre\',\'varchar\',\'' . $nombre . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Tipo de Documento de Identidad</b></td>';
	$html .= '<td>' . $tipo_docu_identidad . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'tipo_docu_identidad_id\',\'Tipo de Documento de Identidad\',\'select_option\',\'' . $tipo_docu_identidad . '\',\'obtener_tipo_docu_identidad\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Número de ' . $tipo_docu_identidad . '</b></td>';
	$html .= '<td>' . $num_docu . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'num_docu\',\'Número de Documento de Identidad\',\'varchar\',\'' . $num_docu . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Número de RUC</b></td>';
	$html .= '<td>' . $num_ruc . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'num_ruc\',\'RUC\',\'varchar\',\'' . $num_ruc . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Domicilio del propietario</b></td>';
	$html .= '<td>' . $direccion . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'direccion\',\'Domicilio del propietario\',\'varchar\',\'' . $direccion . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Representante Legal</b></td>';
	$html .= '<td>' . $representante_legal . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'representante_legal\',\'Representante Legal\',\'varchar\',\'' . $representante_legal . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>N° de Partida Registral de la empresa</b></td>';
	$html .= '<td>' . $num_partida_registral_per . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'num_partida_registral\',\'N° de Partida Registral de la empresa\',\'varchar\',\'' . $num_partida_registral_per . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Persona de contacto</b></td>';
	$html .= '<td>' . $contacto_nombre . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'contacto_nombre\',\'Domicilio del propietario\',\'varchar\',\'' . $contacto_nombre . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Teléfono de la persona de contacto</b></td>';
	$html .= '<td>' . $contacto_telefono . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'contacto_telefono\',\'Teléfono de la persona de contacto\',\'varchar\',\'' . $contacto_telefono . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>E-mail de la persona de contacto</b></td>';
	$html .= '<td>' . $contacto_email . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'contacto_email\',\'E-mail de la persona de contacto\',\'varchar\',\'' . $contacto_email . '\',\'\',\'\');">';
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
	$html .= '<div class="h4"><b>DATOS DEL INMUEBLE</b></div>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="form-group">';
	$html .= '<table class="table table-bordered table-hover">';

	$html .= '<tr>';
	$html .= '<td><b>Ubigeo</b></td>';
	$html .= '<td>' . $ubigeo_id . '</td>';
	$html .= '</tr>';


	$html .= '<tr>';
	$html .= '<td><b>Departamento</b></td>';
	$html .= '<td>' . $departamento . '</td>';
	$html .= '<td rowspan="3">';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Inmueble\',\'cont_inmueble\',\'ubigeo_id\',\'Departamento/Provincia/Distrito\',\'select_option\',\'' . $departamento . "/" . $provincia . "/" . $distrito . '\',\'obtener_departamentos\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Provincia</b></td>';
	$html .= '<td>' . $provincia . '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Distrito</b></td>';
	$html .= '<td>' . $distrito . '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Ubicación</b></td>';
	$html .= '<td>' . $ubicacion . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Inmueble\',\'cont_inmueble\',\'ubicacion\',\'Ubicación\',\'varchar\',\'' . $ubicacion . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Latitud</b></td>';
	$html .= '<td>' . $latitud . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Inmueble\',\'cont_inmueble\',\'latitud\',\'Latitud\',\'varchar\',\'' . $latitud . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Longitud</b></td>';
	$html .= '<td>' . $longitud . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Inmueble\',\'cont_inmueble\',\'longitud\',\'Longitud\',\'varchar\',\'' . $longitud . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';


	$html .= '<tr>';
	$html .= '<td><b>Área arrendada(m2)</b></td>';
	$html .= '<td>' . $area_arrendada . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Inmueble\',\'cont_inmueble\',\'area_arrendada\',\'Área arrendada(m2)\',\'int\',\'' . $area_arrendada . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>N° Partida Registral</b></td>';
	$html .= '<td>' . $num_partida_registral . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Inmueble\',\'cont_inmueble\',\'num_partida_registral\',\'N° Partida Registral\',\'varchar\',\'' . $num_partida_registral . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Oficina Registral (Sede)</b></td>';
	$html .= '<td>' . $oficina_registral . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Inmueble\',\'cont_inmueble\',\'oficina_registral\',\'Oficina Registral (Sede)\',\'varchar\',\'' . $oficina_registral . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Servicio de Agua - N° de Suministro</b></td>';
	$html .= '<td>' . $num_suministro_agua . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Inmueble\',\'cont_inmueble\',\'num_suministro_agua\',\'Servicio de Agua - N° de Suministro\',\'varchar\',\'' . $num_suministro_agua . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Servicio de Agua - Compromiso de pago</b></td>';
	$html .= '<td>' . $tipo_pago_agua . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Inmueble\',\'cont_inmueble\',\'tipo_compromiso_pago_agua\',\'Servicio de Agua - Compromiso de pago\',\'select_option\',\'' . $tipo_pago_agua . '\',\'obtener_tipo_pago_servicio\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	if ($tipo_compromiso_pago_agua != 3) {

		$html .= '<tr>';
		$html .= '<td><b>Servicio de Agua - Monto Fijo</b></td>';
		$html .= '<td>' . $monto_o_porcentaje_agua . '</td>';
		$html .= '<td>';
		$html .= '<a class="btn btn-success btn-xs" ';
		$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Inmueble\',\'cont_inmueble\',\'monto_o_porcentaje_agua\',\'Servicio de Agua - Monto Fijo\',\'varchar\',\'' . $monto_o_porcentaje_agua . '\',\'\',\'\');">';
		$html .= '<span class="fa fa-edit"></span> Editar';
		$html .= '</a>';
		$html .= '</td>';
		$html .= '</tr>';
	}

	$html .= '<tr>';
	$html .= '<td><b>Empresa de Servicio - AGUA</b></td>';
	$html .= '<td>' . $empresa_servicio_agua . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Inmueble\',\'cont_inmueble\',\'id_empresa_servicio_agua\',\'Empresa de Servicio - AGUA\',\'select_option\',\'' . $empresa_servicio_agua . '\',\'obtener_empresas_agua\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Servicio de Luz - N° de Suministro</b></td>';
	$html .= '<td>' . $num_suministro_luz . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Inmueble\',\'cont_inmueble\',\'num_suministro_luz\',\'Servicio de Luz - N° de Suministro\',\'varchar\',\'' . $num_suministro_luz . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Servicio de Luz - Compromiso de pago</b></td>';
	$html .= '<td>' . $tipo_pago_luz . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Inmueble\',\'cont_inmueble\',\'tipo_compromiso_pago_luz\',\'Servicio de Luz - Compromiso de pago\',\'select_option\',\'' . $tipo_pago_luz . '\',\'obtener_tipo_pago_servicio\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	if ($tipo_compromiso_pago_luz != 3) {

		$html .= '<tr>';
		$html .= '<td><b>Servicio de Luz - Monto Fijo</b></td>';
		$html .= '<td>' . $monto_o_porcentaje_luz . '</td>';
		$html .= '<td>';
		$html .= '<a class="btn btn-success btn-xs" ';
		$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Inmueble\',\'cont_inmueble\',\'monto_o_porcentaje_luz\',\'Servicio de Luz - Monto Fijo\',\'varchar\',\'' . $monto_o_porcentaje_luz . '\',\'\',\'\');">';
		$html .= '<span class="fa fa-edit"></span> Editar';
		$html .= '</a>';
		$html .= '</td>';
		$html .= '</tr>';
	}

	$html .= '<tr>';
	$html .= '<td><b>Empresa de Servicio - LUZ</b></td>';
	$html .= '<td>' . $empresa_servicio_luz . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Inmueble\',\'cont_inmueble\',\'id_empresa_servicio_luz\',\'Empresa de Servicio - LUZ\',\'select_option\',\'' . $empresa_servicio_luz . '\',\'obtener_empresas_luz\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Arbitrios - Compromiso de pago</b></td>';
	$html .= '<td>' . $tipo_pago_arbitrios . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Inmueble\',\'cont_inmueble\',\'tipo_compromiso_pago_arbitrios\',\'Arbitrios - Compromiso de pago\',\'select_option\',\'' . $tipo_pago_arbitrios . '\',\'obtener_tipo_pago_arbitrios\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	if ($tipo_compromiso_pago_arbitrios != 2) {

		$html .= '<tr>';
		$html .= '<td><b>Porcentaje del Pago de Arbitrios (%)</b></td>';
		$html .= '<td>' . $porcentaje_pago_arbitrios . '</td>';
		$html .= '<td>';
		$html .= '<a class="btn btn-success btn-xs" ';
		$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Inmueble\',\'cont_inmueble\',\'porcentaje_pago_arbitrios\',\'Porcentaje del Pago de Arbitrios (%)\',\'varchar\',\'' . $porcentaje_pago_arbitrios . '\',\'\',\'\');">';
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
	$html .= '<div class="h4"><b>CONDICIONES ECONÓMICAS Y COMERCIALES</b></div>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="form-group">';
	$html .= '<table class="table table-bordered table-hover">';

	$html .= '<tr>';
	$html .= '<td><b>Moneda del contrato</b></td>';
	$html .= '<td>' . $moneda_contrato . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Condiciones económicas y comerciales\',\'cont_condicion_economica\',\'tipo_moneda_id\',\'Moneda del contrato\',\'select_option\',\'' . $moneda_contrato . '\',\'obtener_tipo_moneda\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Monto de Renta Pactada</b></td>';
	$html .= '<td>' . $monto_renta . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Condiciones económicas y comerciales\',\'cont_condicion_economica\',\'monto_renta\',\'Monto de Renta Pactada\',\'decimal\',\'' . $monto_renta . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';


	$html .= '<tr>';
	$html .= '<td><b>Monto de la garantías</b></td>';
	$html .= '<td>' . $garantia_monto . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Condiciones económicas y comerciales\',\'cont_condicion_economica\',\'garantia_monto\',\'Monto de la garantías\',\'decimal\',\'' . $garantia_monto . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';


	$html .= '<tr>';
	$html .= '<td><b>Adelantos</b></td>';
	$html .= '<td>' . $tipo_adelanto . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Condiciones económicas y comerciales\',\'cont_condicion_economica\',\'tipo_adelanto_id\',\'Tipo de Adelanto\',\'select_option\',\'' . $tipo_adelanto . '\',\'obtener_tipo_adelantos\',\'\');">';
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
	$html .= '<div class="h4"><b>IMPUESTO A LA RENTA</b></div>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="form-group">';
	$html .= '<table class="table table-bordered table-hover">';

	$html .= '<tr>';
	$html .= '<td><b>Tipo de Impuesto a la renta</b></td>';
	$html .= '<td>' . $impuesto_a_la_renta_texto . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Condiciones económicas y comerciales\',\'cont_condicion_economica\',\'impuesto_a_la_renta_id\',\'Tipo de Impuesto a la renta\',\'select_option\',\'' . $impuesto_a_la_renta_texto . '\',\'obtener_tipo_impuesto_a_la_renta\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>¿AT deposita impuesto a la renta a SUNAT? Carta de Instrucción</b></td>';
	$html .= '<td>' . $carta_de_instruccion . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Condiciones económicas y comerciales\',\'cont_condicion_economica\',\'carta_de_instruccion_id\',\'¿AT deposita impuesto a la renta a SUNAT? Carta de Instrucción\',\'select_option\',\'' . $carta_de_instruccion . '\',\'obtener_tipo_carta_de_instruccion\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Impuesto a la Renta</b></td>';
	$html .= '<td>' . $impuesto_a_la_renta . '</td>';
	$html .= '<td>';

	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Renta Bruta</b></td>';
	$html .= '<td>' . $renta_bruta . '</td>';
	$html .= '<td>';

	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Renta a pagar</b></td>';
	$html .= '<td>' . $renta_neta . '</td>';
	$html .= '<td>';

	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Detalle</b></td>';
	$html .= '<td>' . $detalle . '</td>';
	$html .= '<td>';

	$html .= '</td>';
	$html .= '</tr>';


	$html .= '</table>';
	$html .= '</div>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<br>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="h4"><b>VIGENCIA</b></div>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="form-group">';
	$html .= '<table class="table table-bordered table-hover">';

	$html .= '<tr>';
	$html .= '<td><b>Vigencia del Contrato</b></td>';
	$html .= '<td>' . $cant_meses_contrato . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Vigencia\',\'cont_condicion_economica\',\'cant_meses_contrato\',\'Vigencia del Contrato (En meses)\',\'int\',\'' . $cant_meses_contrato . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Contrato - Fecha de Inicio</b></td>';
	$html .= '<td>' . $contrato_inicio_fecha . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Vigencia\',\'cont_condicion_economica\',\'fecha_inicio\',\'Contrato - Fecha de Inicio\',\'date\',\'' . $contrato_inicio_fecha . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Contrato - Fecha de Fin</b></td>';
	$html .= '<td>' . $contrato_fin_fecha . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Vigencia\',\'cont_condicion_economica\',\'fecha_fin\',\'Contrato - Fecha de Fin\',\'date\',\'' . $contrato_fin_fecha . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Periodo de gracia</b></td>';
	$html .= '<td>' . $periodo_gracia . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Vigencia\',\'cont_condicion_economica\',\'periodo_gracia_id\',\'Periodo de gracia\',\'select_option\',\'' . $periodo_gracia . '\',\'obtener_tipo_periodo_de_gracia\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Día de Pago</b></td>';
	$html .= '<td>' . $dia_de_pago . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Vigencia\',\'cont_condicion_economica\',\'dia_de_pago_id\',\'Día de Pago\',\'select_option\',\'' . $dia_de_pago . '\',\'obtener_tipo_dia_de_pago\',\'\');">';
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
	$html .= '<div class="h4">';
	$html .= '<b>INCREMENTOS</b>&nbsp;';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_arrendamiento_modal_agregar_incrementos(' . $id_contrato_arrendamiento . ')">';
	$html .= '<span class="fa fa-plus"></span> Agregar Incrementos';
	$html .= '</a>';
	$html .= '</div>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="form-group">';
	$html .= '<table class="table table-bordered table-hover">';
	$num_incremento_contador = 0;
	$sel_query = $mysqli->query("
	SELECT 
		i.id, 
		i.valor, 
		i.tipo_valor_id,
		tp.nombre AS tipo_valor, 
		i.tipo_continuidad_id, 
		tc.nombre AS tipo_continuidad, 
		i.a_partir_del_año
	FROM 
		cont_incrementos i
		INNER JOIN cont_tipo_pago_incrementos tp ON i.tipo_valor_id = tp.id
		INNER JOIN cont_tipo_continuidad_pago tc ON i.tipo_continuidad_id = tc.id
	WHERE 
		i.contrato_id = $id_contrato_arrendamiento
		AND i.estado = 1
	ORDER BY i.id
	");
	$row_count_incrementos = $sel_query->num_rows;

	if ($row_count_incrementos > 0) {
		while ($sel = $sel_query->fetch_assoc()) {
			$num_incremento_contador++;

			$a_partir_del_año = $sel["a_partir_del_año"] . ' año';
			$valor = trim($sel["valor"]);
			$tipo_valor = '';

			if ($sel["tipo_valor_id"] == 1) {
				$tipo_valor = ' ' . $moneda_contrato;
				$valor = $simbolo_moneda . ' ' . $valor;
			} else if ($sel["tipo_valor_id"] == 2) {
				$tipo_valor = '%';
				if (substr($valor, -3, 3) == ".00") {
					$valor = substr($valor, 0, -3);
				}
			}

			if ($sel["tipo_continuidad_id"] == 3) {
				$a_partir_del_año = '';
			}

			$html .= '<tr>';
			$html .= '<td style="width: 250px;"><b>Incremento N.° 0' . $num_incremento_contador . '</b></td>';
			$html .= '<td>' . $valor . $tipo_valor . ' ' . $sel["tipo_continuidad"] . ' ' . $a_partir_del_año . '</td>';
			$html .= '<td>';
			// $html .= '<a class="btn btn-success btn-xs" ';
			// $html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Beneficiario\',\'cont_beneficiarios\',\'tipo_persona_id\',\'Tipo de Persona\',\'select_option\',\'' . $ben_tipo_persona . '\',\'obtener_tipo_persona\',\'\');">';
			// $html .= '<span class="fa fa-edit"></span> Editar';
			// $html .= '</a>';
			$html .= '</td>';
			$html .= '</tr>';
		}
	} else {
		$html .= '<tr>';
		$html .= '<td>El presente contrato no posee incrementos</td>';
		$html .= '<td>';
	}
	$html .= '</table>';
	$html .= '</div>';
	$html .= '</div>';



	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<br>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="h4">';
	$html .= '<b>BENEFICIARIOS</b>&nbsp;';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_adenda_cambiar_beneficiario(' . $beneficiario_id . ', ' . $id_contrato_arrendamiento . ')">';
	$html .= '<span class="fa fa-edit"></span> Cambiar de Beneficiario';
	$html .= '</a>';
	$html .= '</div>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="form-group">';
	$html .= '<table class="table table-bordered table-hover">';

	$html .= '<tr>';
	$html .= '<td><b>Tipo de Persona</b></td>';
	$html .= '<td>' . $ben_tipo_persona . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Beneficiario\',\'cont_beneficiarios\',\'tipo_persona_id\',\'Tipo de Persona\',\'select_option\',\'' . $ben_tipo_persona . '\',\'obtener_tipo_persona\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Nombre</b></td>';
	$html .= '<td>' . $ben_nombre . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Beneficiario\',\'cont_beneficiarios\',\'nombre\',\'Nombre\',\'varchar\',\'' . $ben_nombre . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Tipo de Documento de Identidad</b></td>';
	$html .= '<td>' . $ben_tipo_docu_identidad . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Beneficiario\',\'cont_beneficiarios\',\'tipo_docu_identidad_id\',\'Tipo de Documento de Identidad\',\'select_option\',\'' . $ben_tipo_docu_identidad . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Número de Documento de Identidad</b></td>';
	$html .= '<td>' . $ben_num_docu . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Beneficiario\',\'cont_beneficiarios\',\'num_docu\',\'Número de Documento de Identidad\',\'varchar\',\'' . $ben_num_docu . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Tipo de forma de pago</b></td>';
	$html .= '<td>' . $ben_forma_pago . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Beneficiario\',\'cont_beneficiarios\',\'forma_pago_id\',\'Tipo de forma de pago\',\'select_option\',\'' . $ben_forma_pago . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Nombre del Banco</b></td>';
	$html .= '<td>' . $ben_banco . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Beneficiario\',\'cont_beneficiarios\',\'banco_id\',\'Nombre del Banco\',\'select_option\',\'' . $ben_banco . '\',\'obtener_banco\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>N° de la cuenta bancaria</b></td>';
	$html .= '<td>' . $ben_num_cuenta_bancaria . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Beneficiario\',\'cont_beneficiarios\',\'num_cuenta_bancaria\',\'N° de la cuenta bancaria\',\'varchar\',\'' . $ben_num_cuenta_bancaria . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>N° de CCI bancario</b></td>';
	$html .= '<td>' . $ben_num_cuenta_cci . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Beneficiario\',\'cont_beneficiarios\',\'num_cuenta_cci\',\'N° de CCI bancario\',\'varchar\',\'' . $ben_num_cuenta_cci . '\',\'\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Monto</b></td>';
	$html .= '<td>' . $ben_monto_beneficiario . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Beneficiario\',\'cont_beneficiarios\',\'monto\',\'Monto\',\'select_option\',\'' . $ben_monto_beneficiario . '\',\'\',\'\');">';
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
	$html .= '<div class="h4"><b>FECHA DE SUSCRIPCIÓN DEL CONTRATO</b></div>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="form-group">';
	$html .= '<table class="table table-bordered table-hover">';

	$html .= '<tr>';
	$html .= '<td><b>Fecha de suscripción del contrato</b></td>';
	$html .= '<td>' . $contrato_fecha_suscripcion . '</td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Fecha de suscripción\',\'cont_condicion_economica\',\'fecha_suscripcion\',\'Fecha de suscripción del contrato\',\'date\',\'' . $contrato_fecha_suscripcion . '\',\'\',\'\');">';
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
	$html .= '<div class="h4"><b>OBSERVACIONES</b></div>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="form-group">';
	$html .= '<table class="table table-bordered table-hover">';

	$html .= '<tr>';
	$html .= '<td><b>Observaciones</b></td>';
	$html .= '<td><?php echo "..."; ?></td>';
	$html .= '<td>';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_contrato_nuevo_solicitud_editar_campo_adenda(\'Observaciones\',\'cont_contrato\',\'observaciones\',\'Observaciones\',\'varchar\',\'' . '' . '\',\'\',\'\');">';
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


if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_adelantos") {

	$user_id = $login ? $login['id'] : null;
	$created_at = date('Y-m-d H:i:s');
	$array_adelantos_id = [];

	$mes_adelanto = $_POST["mes_adelanto"];
	$data_adelantos = json_decode($mes_adelanto);
	foreach ($data_adelantos as $value_num_mes) {
		$query_insert = "INSERT INTO cont_adelantos
		(num_periodo,
		status,
		user_created_id,
		created_at)
		VALUES(
		'" . $value_num_mes . "',
		1,
		" . $user_id . ",
		'" . $created_at . "'
		)";
		$mysqli->query($query_insert);
		$array_adelantos_id[] = mysqli_insert_id($mysqli);
		$error = '';
		if ($mysqli->error) {
			$error = $mysqli->error;
		}
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $array_adelantos_id;
	$result["error"] = $error;
}


if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_adelantos") {

	$id_adelantos = $_POST["id_adelantos"];
	$contador_array_ids = 0;

	$data = json_decode($id_adelantos);
	$ids = '';

	foreach ($data as $value) {
		if ($contador_array_ids > 0) {
			$ids .= ',';
		}
		$ids .= $value;
		$contador_array_ids++;
	}

	if ($contador_array_ids == 0) {
		$ids = 0;
	}

	$html = '<table class="table table-bordered table-hover no-mb" style="font-size:11px; margin-top: 10px;">';
	$html .= '<thead>';

	$html .= '<tr>';

	$html .= '<th>#</th>';
	$html .= '<th>Adelantos</th>';
	$html .= '<th>Opciones</th>';

	$html .= '</tr>';

	$html .= '</thead>';
	$html .= '<tbody>';

	$sql = "
	SELECT 
		a.id, 
		t.nombre AS mes_adelanto
	FROM 
		cont_adelantos a
		INNER JOIN cont_tipo_mes_adelanto t ON a.num_periodo = t.id
	WHERE 
		a.id IN($ids)
	ORDER BY a.id ASC";

	$query = $mysqli->query($sql);

	$row_count = $query->num_rows;

	if ($row_count > 0) {
		$contador = 1;

		while ($row = $query->fetch_assoc()) {
			$html .= '<tr>';
			$html .= '<td>' . $contador . '</td>';
			$html .= '<td>Del ' . $row["mes_adelanto"] . '</td>';

			$html .= '<td>';
			$html .= '<a class="btn btn-success btn-xs" data-toggle="tooltip" data-placement="top" title="Editar" style="width: 24px;" onclick="sec_contrato_nuevo_editar_adelantos()">';
			$html .= '<i class="fa fa-edit"></i>';
			$html .= '</a>';
			$html .= '</td>';

			$html .= '</tr>';

			$contador++;
		}
	}


	$html .= '</tbody>';
	$html .= '</table>';

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $html;
}


if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_num_mes_de_adelantos") {

	$id_adelantos = $_POST["id_adelantos"];
	$contador_array_ids = 0;

	$array_adelantos_num_periodo = [];

	$data = json_decode($id_adelantos);
	$ids = '';

	foreach ($data as $value) {
		if ($contador_array_ids > 0) {
			$ids .= ',';
		}
		$ids .= $value;
		$contador_array_ids++;
	}

	if ($contador_array_ids == 0) {
		$ids = 0;
	}

	$sql = "SELECT 
	id, 
	num_periodo
	FROM cont_adelantos ";
	$sql .= " WHERE id IN(" . $ids . ") ";
	$sql .= " ORDER BY id ASC";

	$query = $mysqli->query($sql);

	$row_count = $query->num_rows;

	if ($row_count > 0) {
		$contador = 1;

		while ($row = $query->fetch_assoc()) {
			$array_adelantos_num_periodo[] = $row["num_periodo"];
		}
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $array_adelantos_num_periodo;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_propietario_por_id") {

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
	WHERE c.status = 1 AND p.id = " . $id_persona);

	if ($query_val->num_rows > 0) {
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

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_propietario") {

	$nombre_o_numdocu = $_POST["nombre_o_numdocu"];
	$tipo_busqueda = $_POST["tipo_busqueda"];
	$tipo_solicitud = $_POST["tipo_solicitud"];
	$contador_array_ids = 0;

	if ($tipo_busqueda == '3' || $tipo_busqueda == '5') {
		$data = json_decode($nombre_o_numdocu);
		$ids = '';

		foreach ($data as $value) {
			if ($contador_array_ids > 0) {
				$ids .= ',';
			}
			$ids .= $value;
			$contador_array_ids++;
		}

		if ($contador_array_ids == 0) {
			$ids = 0;
		}
	}

	$html = '<table class="table table-bordered table-hover no-mb" style="font-size:11px; margin-top: 10px;">';
	$html .= '<thead>';

	$html .= '<tr>';

	if ($tipo_busqueda == '1' || $tipo_busqueda == '2') {
		$html .= '<th>#</th>';
		$html .= '<th>Nombre / Razón Social</th>';
		$html .= '<th>DNI, Pasaporte, Carnet Ext.</th>';
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
		$html .= '<th>N.° de DNI o Pasaporte</th>';
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
	if ($tipo_busqueda == '3' || $tipo_busqueda == '5') {
		$sql .= " WHERE id IN(" . $ids . ") ";
	} else if ($tipo_busqueda == '1') {
		$sql .= " WHERE nombre like '" . $nombre_o_numdocu . "%' ";
	} else if ($tipo_busqueda == '4') {
		$sql .= " WHERE id = '" . $nombre_o_numdocu . "'";
	} else if ($tipo_busqueda == '2') {
		$sql .= " WHERE num_ruc = '" . $nombre_o_numdocu . "'";
	} else {
		$sql .= " WHERE num_docu like '" . $nombre_o_numdocu . "%'";
	}

	$query = $mysqli->query($sql);

	if ($tipo_busqueda == '4') {
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
					$html .= '<a class="btn btn-success btn-xs" onclick="sec_contrato_nuevo_asignar_otros_detalles_a_la_adenda(\'propietario\',' . $row["id"] . ', \'modalBuscarPropietario\')">';
					$html .= '<i class="fa fa-plus"></i> Agregar a la adenda como propietario';
					$html .= '</a>';
					$html .= '</td>';
				} else {
					$html .= '<td>';
					$html .= '<a class="btn btn-success btn-xs" onclick="sec_contrato_nuevo_asignar_propietario_al_contrato(' . $row["id"] . ', \'modalBuscar\')">';
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
				$html .= '<a class="btn btn-success btn-xs" data-toggle="tooltip" data-placement="top" title="Editar" style="width: 24px;" onclick="sec_contrato_nuevo_editar_propietario(' . $row["id"] . ')">';
				$html .= '<i class="fa fa-edit"></i>';
				$html .= '</a>';
				$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar del contrato" style="width: 24px;" onclick="sec_contrato_nuevo_eliminar_propietario(' . $row["id"] . ')">';
				$html .= '<i class="fa fa-trash"></i>';
				$html .= '</a>';
				$html .= '</td>';
			} else if ($tipo_busqueda == '5') {
				$html .= '<td>';
				$html .= '<a class="btn btn-success btn-xs" onclick="sec_contrato_nuevo_asignar_pre_beneficiario_al_contrato(' . $row["id"] . ', \'modalBuscar\')">';
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
		$html .= '<button type="button" class="btn btn-success" onclick="sec_contrato_nuevo_buscar_propietario_modal(\'arrendamiento\')" style="width: 180px;">';
		$html .= '<i class="icon fa fa-plus"></i>';
		$html .= '<span id="demo-button-text"> Agregar propietario</span>';
		$html .= '</button>';
		$html .= '</td>';
		$html .= '</tr>';
	}

	$html .= '</tbody>';
	$html .= '</table>';

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	if ($tipo_busqueda == '4') {
		$result["result"] = $list;
	} else {
		$result["result"] = $html;
	}
}


if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_propietario_ca") {

	$nombre_o_numdocu = $_POST["nombre_o_numdocu"];
	$tipo_busqueda = $_POST["tipo_busqueda"];
	$tipo_solicitud = $_POST["tipo_solicitud"];
	$contador_array_ids = 0;

	if ($tipo_busqueda == '3' || $tipo_busqueda == '5') {
		$data = json_decode($nombre_o_numdocu);
		$ids = '';

		foreach ($data as $value) {
			if ($contador_array_ids > 0) {
				$ids .= ',';
			}
			$ids .= $value;
			$contador_array_ids++;
		}

		if ($contador_array_ids == 0) {
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
		$html .= '<th>N.° de DNI o Pasaporte</th>';
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
	if ($tipo_busqueda == '3' || $tipo_busqueda == '5') {
		$sql .= " WHERE id IN(" . $ids . ") ";
	} else if ($tipo_busqueda == '1') {
		$sql .= " WHERE nombre like '" . $nombre_o_numdocu . "%' ";
	} else if ($tipo_busqueda == '4') {
		$sql .= " WHERE id = '" . $nombre_o_numdocu . "'";
	} else {
		$sql .= " WHERE num_docu like '" . $nombre_o_numdocu . "%'";
	}

	$query = $mysqli->query($sql);

	if ($tipo_busqueda == '4') {
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
					$html .= '<a class="btn btn-success btn-xs" onclick="sec_contrato_nuevo_asignar_otros_detalles_a_la_adenda_ca(\'propietario\',' . $row["id"] . ', \'modalBuscarPropietario\')">';
					$html .= '<i class="fa fa-plus"></i> Agregar a la adenda como propietario';
					$html .= '</a>';
					$html .= '</td>';
				} else {
					$html .= '<td>';
					$html .= '<a class="btn btn-success btn-xs" onclick="sec_contrato_nuevo_asignar_propietario_al_contrato_ca(' . $row["id"] . ', \'modalBuscar\')">';
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
				$html .= '<a class="btn btn-success btn-xs" data-toggle="tooltip" data-placement="top" title="Editar" style="width: 24px;" onclick="sec_contrato_nuevo_editar_propietario_ca(' . $row["id"] . ')">';
				$html .= '<i class="fa fa-edit"></i>';
				$html .= '</a>';
				$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar del contrato" style="width: 24px;" onclick="sec_contrato_nuevo_eliminar_propietario_ca(' . $row["id"] . ')">';
				$html .= '<i class="fa fa-trash"></i>';
				$html .= '</a>';
				$html .= '</td>';
			} else if ($tipo_busqueda == '5') {
				$html .= '<td>';
				$html .= '<a class="btn btn-success btn-xs" onclick="sec_contrato_nuevo_asignar_pre_beneficiario_al_contrato(' . $row["id"] . ', \'modalBuscar\')">';
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
		$html .= '<button type="button" class="btn btn-success" onclick="sec_contrato_nuevo_buscar_propietario_modal_ca(\'agente\')" style="width: 180px;">';
		$html .= '<i class="icon fa fa-plus"></i>';
		$html .= '<span id="demo-button-text"> Agregar propietario</span>';
		$html .= '</button>';
		$html .= '</td>';
		$html .= '</tr>';
	}

	$html .= '</tbody>';
	$html .= '</table>';

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	if ($tipo_busqueda == '4') {
		$result["result"] = $list;
	} else {
		$result["result"] = $html;
	}
}


if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_propietario") {
	$usuario_id = $login ? $login['id'] : null;
	$created_at = date("Y-m-d H:i:s");

	if ($_POST["tipo_persona_contacto"] == 1) {
		$contacto_nombre = $_POST["nombre"];
	} else {
		$contacto_nombre = $_POST["contacto_nombre"];
	}

	if ($_POST["tipo_docu"] != 2) {
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
		if (($row_existe) > 0) {
			$result["http_code"] = 400;
			$result["status"] = "El Nro de Documento ya esta registrado.";
			$result["result"] = $row_existe;
			echo json_encode($result);
			exit();
		}
	} else if ($_POST["tipo_docu"] == 2) {
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
		if (($row_existe) > 0) {
			$result["http_code"] = 400;
			$result["status"] = "El RUC ya esta registrado.";
			$result["result"] = $row_existe;
			echo json_encode($result);
			exit();
		}
	}


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
	'" . str_replace("'", "", trim($_POST["nombre"])) . "',
	'" . str_replace("'", "", trim($_POST["direccion"])) . "',
	'" . str_replace("'", "", trim($_POST["representante_legal"])) . "',
	'" . $_POST["num_partida_registral"] . "',
	'" . str_replace("'", "", trim($contacto_nombre)) . "',
	'" . $_POST["contacto_telefono"] . "',
	'" . $_POST["contacto_email"] . "',
	" . $usuario_id . ",
	'" . $created_at . "')";
	$mysqli->query($query_insert);
	$insert_id = mysqli_insert_id($mysqli);
	$error = '';
	if ($mysqli->error) {
		$error = $mysqli->error;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $insert_id;
	$result["error"] = $error;
}


if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_cambios_propietario") {
	$nombre = str_replace("'", "", trim($_POST["nombre"]));
	$usuario_id = $login ? $login['id'] : null;
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
	WHERE id = " . $_POST["id_propietario_para_cambios"] . "
	";
	$mysqli->query($query_update);

	$error = '';
	if ($mysqli->error) {
		$error = $mysqli->error;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = 'ok';
	$result["error"] = $error;
}

// INMUEBLES

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_inmuebles") {

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

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list) == 0) {
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

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_inmueble") {

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
	'" . str_replace("'", "", trim($_POST["ubicacion"])) . "',
	" . $_POST["area_arrendada"] . ",
	" . $_POST["num_partida_registral"] . ",
	'" . str_replace("'", "", trim($_POST["oficina_registral"])) . "',
	" . $_POST["num_suministro_agua"] . ",
	" . $_POST["tipo_compromiso_pago_agua"] . ",
	" . $monto_o_porcentaje_agua . ",
	" . $_POST["num_suministro_luz"] . ",
	" . $_POST["tipo_compromiso_pago_luz"] . ",
	" . $monto_o_porcentaje_luz . ",
	" . $_POST["tipo_compromiso_pago_arbitrios"] . ",
	" . $porcentaje_pago_arbitrios . ")";


	$mysqli->query($query_insert);
	$insert_id = mysqli_insert_id($mysqli);
	$error = '';
	if ($mysqli->error) {
		$error = $mysqli->error;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $insert_id;
	$result["error"] = $error;
}


// INCREMENTOS

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_incremento") {
	$usuario_id = $login ? $login['id'] : null;
	$created_at = date("Y-m-d H:i:s");
	$incremento_monto_o_porcentaje = str_replace(",", "", $_POST["incremento_monto_o_porcentaje"]);

	if (empty($_POST["incrementos_a_partir_de_año"])) {
		$incrementos_a_partir_de_año = "2";
	} else {
		$incrementos_a_partir_de_año = $_POST["incrementos_a_partir_de_año"];
	}

	$query_insert = " INSERT INTO cont_incrementos
	(
	valor,
	tipo_valor_id,
	tipo_continuidad_id,
	a_partir_del_año,
	user_created_id,
	created_at)
	VALUES
	(
	" . $incremento_monto_o_porcentaje . ",
	" . $_POST["incrementos_en"] . ",
	" . $_POST["incrementos_continuidad"] . ",
	" . $incrementos_a_partir_de_año . ",
	" . $usuario_id . ",
	'" . $created_at . "')";

	$mysqli->query($query_insert);
	$insert_id = mysqli_insert_id($mysqli);
	$error = '';
	if ($mysqli->error) {
		$error = $mysqli->error;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $insert_id;
	$result["error"] = $error;
}


if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_incrementos") {

	$sql = "SELECT i.id, i.valor, i.tipo_valor_id, tp.nombre AS tipo_valor, i.tipo_continuidad_id,  tc.nombre AS tipo_continuidad, i.a_partir_del_año
	FROM cont_incrementos i
	INNER JOIN cont_tipo_pago_incrementos tp ON i.tipo_valor_id = tp.id
	INNER JOIN cont_tipo_continuidad_pago tc ON i.tipo_continuidad_id = tc.id";
	if (isset($_POST["id_incrementos"])) {
		$id_incrementos = $_POST["id_incrementos"];
		$data = json_decode($id_incrementos);
		$contador = 0;
		$ids = '';
		foreach ($data as $value) {
			if ($contador > 0) {
				$ids .= ',';
			}
			$ids .= $value;
			$contador++;
		}
		$sql .= " WHERE i.id IN(" . $ids . ") AND i.estado = 1";

		$html = '<table class="table table-bordered" style="font-size:10px; margin-top: 12px;">';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th>#</th>';
		$html .= '<th>Incremento</th>';
		$html .= '<th>Opciones</th>';
		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody>';

		$query = $mysqli->query($sql);
		$row_count = $query->num_rows;

		if ($row_count > 0) {
			$contador = 1;

			while ($row = $query->fetch_assoc()) {
				$html .= '<tr>';

				$a_partir_del = $row["a_partir_del_año"] . ' año';

				if ($row["tipo_continuidad_id"] == 1) {
					$continuidad = 'el ';
				} else if ($row["tipo_continuidad_id"] == 2) {
					$continuidad = $row["tipo_continuidad"] . ' a partir del ';
				} else if ($row["tipo_continuidad_id"] == 3) {
					$continuidad = $row["tipo_continuidad"];
					$a_partir_del = '';
				}

				$incremento_texto = number_format($row["valor"], 2, '.', ',') . ' ' . $row["tipo_valor"] . ' ' . $row["tipo_continuidad"] . ' ' . $a_partir_del;

				$html .= '<td>' . $contador . '</td>';
				$html .= '<td>' . $incremento_texto . '</td>';
				$html .= '<td>';
				$html .= '<a class="btn btn-success btn-xs" data-toggle="tooltip" data-placement="top" title="Editar" onclick="sec_contrato_nuevo_editar_incremento(' . $row["id"] . ')">';
				$html .= '<i class="fa fa-edit"></i>';
				$html .= '</a>';
				$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_contrato_nuevo_eliminar_incremento(' . $row["id"] . ')">';
				$html .= '<i class="fa fa-trash"></i>';
				$html .= '</a>';
				$html .= '</td>';

				$html .= '</tr>';

				$contador++;
			}
		}

		$html .= '<tr>';
		$html .= '<td colspan=3 style="text-align:center;">';
		$html .= '<button type="button" class="btn btn-success" onclick="sec_contrato_nuevo_modal_agregar_incremento()" style="width: 180px;">';
		$html .= '<i class="icon fa fa-plus"></i>';
		$html .= '<span id="demo-button-text"> Agregar incremento</span>';
		$html .= '</button>';
		$html .= '</td>';
		$html .= '</tr>';

		$html .= '</tbody>';
		$html .= '</table>';

		if ($mysqli->error) {
			$result["consulta_error"] = $mysqli->error;
		}

		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $html;
	} else if (isset($_POST["id_incremento"])) {
		$id_incremento = $_POST["id_incremento"];
		$sql .= " WHERE i.id = " . $id_incremento . " AND i.estado = 1";
		$list_query = $mysqli->query($sql);
		$list = array();
		while ($li = $list_query->fetch_assoc()) {
			$list[] = $li;
		}

		if ($mysqli->error) {
			$result["consulta_error"] = $mysqli->error;
		}

		if (count($list) == 0) {
			$result["http_code"] = 400;
			$result["result"] = "El incremento no existe.";
		} elseif (count($list) > 0) {
			$result["http_code"] = 200;
			$result["status"] = "Datos obtenidos de gestion.";
			$result["result"] = $list;
		} else {
			$result["http_code"] = 400;
			$result["result"] = "El incremento no existe.";
		}
	}
}


if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_cambios_incremento") {
	$incremento_monto_o_porcentaje = str_replace(",", "", $_POST["incremento_monto_o_porcentaje"]);
	$usuario_id = $login ? $login['id'] : null;
	$created_at = date("Y-m-d H:i:s");

	$query_update = "
	UPDATE cont_incrementos
	SET 
		valor = " . $incremento_monto_o_porcentaje . ",
		tipo_valor_id = " . $_POST["incrementos_en"] . ",
		tipo_continuidad_id = " . $_POST["incrementos_continuidad"] . ",
		a_partir_del_año = " . $_POST["incrementos_a_partir_de_año"] . ",
		user_updated_id = " . $usuario_id . ",
		updated_at = '" . $created_at . "'
	WHERE id = " . $_POST["id_incremento_para_cambios"] . "
	";
	$mysqli->query($query_update);

	$error = '';
	if ($mysqli->error) {
		$error = $mysqli->error;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = 'ok';
	$result["error"] = $error;
}

// BENEFICIARIOS

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_beneficiario") {
	$usuario_id = $login ? $login['id'] : null;
	$created_at = date("Y-m-d H:i:s");

	//$nombre = mb_strtoupper(str_replace("'", "",trim($_POST["nombre"])), 'UTF-8');

	$nombre = str_replace("'", "", trim($_POST["nombre"]));

	$id_forma_pago = $_POST["id_forma_pago"];

	if ($id_forma_pago == '3') {
		$id_banco = "NULL";
		$num_cuenta_bancaria = "NULL";
		$num_cuenta_cci = "NULL";
	} else {
		$id_banco = $_POST["id_banco"];
		$num_cuenta_bancaria = "'" . $_POST["num_cuenta_bancaria"] . "'";
		$num_cuenta_cci = "'" . $_POST["num_cuenta_cci"] . "'";
	}

	if (empty($_POST["monto"])) {
		$monto = "NULL";
	} else {
		$monto = str_replace(",", "", $_POST["monto"]);
	}

	$query_insert = "INSERT INTO cont_beneficiarios
	(
	tipo_persona_id,
	tipo_docu_identidad_id,
	num_docu,
	nombre,
	forma_pago_id,
	banco_id,
	num_cuenta_bancaria,
	num_cuenta_cci,
	tipo_monto_id,
	monto,
	user_created_id,
	created_at)
	VALUES
	(
	" . $_POST["tipo_persona"] . ",
	" . $_POST["tipo_docu"] . ",
	'" . $_POST["num_docu"] . "',
	'" . $nombre . "',
	" . $id_forma_pago . ",
	" . $id_banco . ",
	" . $num_cuenta_bancaria . ",
	" . $num_cuenta_cci . ",
	" . $_POST["tipo_monto"] . ",
	" . $monto . ",
	" . $usuario_id . ",
	'" . $created_at . "')";

	$mysqli->query($query_insert);
	$insert_id = mysqli_insert_id($mysqli);
	$error = '';
	if ($mysqli->error) {
		$error = $mysqli->error;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $insert_id;
	$result["error"] = $error;
}


if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_cambios_beneficiario") {
	$nombre = str_replace("'", "", trim($_POST["nombre"]));
	$id_forma_pago = $_POST["id_forma_pago"];

	$usuario_id = $login ? $login['id'] : null;
	$created_at = date("Y-m-d H:i:s");

	if ($id_forma_pago == '3') {
		$id_banco = "NULL";
		$num_cuenta_bancaria = "NULL";
		$num_cuenta_cci = "NULL";
	} else {
		$id_banco = $_POST["id_banco"];
		$num_cuenta_bancaria = "'" . $_POST["num_cuenta_bancaria"] . "'";
		$num_cuenta_cci = "'" . $_POST["num_cuenta_cci"] . "'";
	}

	if (empty($_POST["monto"])) {
		$monto = "NULL";
	} else {
		$monto = str_replace(",", "", $_POST["monto"]);
	}

	$query_update = "
	UPDATE cont_beneficiarios
	SET 
		tipo_persona_id = " . $_POST["tipo_persona"] . ",
		tipo_docu_identidad_id = " . $_POST["tipo_docu"] . ",
		num_docu = '" . $_POST["num_docu"] . "',
		nombre = '" . $nombre . "',
		forma_pago_id = " . $id_forma_pago . ",
		banco_id = " . $id_banco . ",
		num_cuenta_bancaria = " . $num_cuenta_bancaria . ",
		num_cuenta_cci = " . $num_cuenta_cci . ",
		tipo_monto_id = " . $_POST["tipo_monto"] . ",
		monto = " . $monto . ",
		user_updated_id = " . $usuario_id . ",
		updated_at = '" . $created_at . "'
	WHERE id = " . $_POST["id_beneficiario_para_cambios"] . "
	";
	$mysqli->query($query_update);

	$error = '';
	if ($mysqli->error) {
		$error = $mysqli->error;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = 'ok';
	$result["error"] = $error;
}


if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_beneficiario") {

	$id_beneficiario = $_POST["id_beneficiario"];

	$query = "SELECT
	id,
	tipo_persona_id,
	tipo_docu_identidad_id,
	num_docu,
	nombre,
	forma_pago_id,
	banco_id,
	num_cuenta_bancaria,
	num_cuenta_cci,
	tipo_monto_id,
	monto
	FROM cont_beneficiarios
	WHERE id = " . $id_beneficiario . "";
	$list_query = $mysqli->query($query);
	$list = array();

	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list) == 0) {
		$result["http_code"] = 400;
		$result["result"] = "El beneficiario no existe.";
	} elseif (count($list) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $list;
	} else {
		$result["http_code"] = 400;
		$result["result"] = "El beneficiario no existe.";
	}
}


if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_beneficiarios") {

	$id_beneficiarios = $_POST["id_beneficiarios"];
	$contador_array_ids = 0;

	$data = json_decode($id_beneficiarios);
	$ids = '';

	foreach ($data as $value) {
		if ($contador_array_ids > 0) {
			$ids .= ',';
		}
		$ids .= $value;
		$contador_array_ids++;
	}

	if ($contador_array_ids == 0) {
		$ids = 0;
	}

	$html = '<table class="table table-bordered table-hover no-mb" style="font-size:11px; margin-top: 10px;">';
	$html .= '<thead>';

	$html .= '<tr>';

	$html .= '<th>#</th>';
	$html .= '<th>Número de RUC</th>';
	$html .= '<th>Nombre del Beneficiario</th>';
	$html .= '<th>Forma de Pago</th>';
	$html .= '<th>Banco</th>';
	$html .= '<th>Número de cuenta bancaria</th>';
	$html .= '<th>Número de CCI</th>';
	$html .= '<th>Monto a depositar</th>';
	$html .= '<th>Opciones</th>';

	$html .= '</tr>';

	$html .= '</thead>';
	$html .= '<tbody>';

	$sql = "SELECT 
	b.id, 
	b.nombre, 
	b.num_docu, 
	f.nombre AS forma_pago, 
	ba.nombre AS banco, 
	b.num_cuenta_bancaria, 
	b.num_cuenta_cci,
	b.tipo_monto_id,
	b.monto
	FROM cont_beneficiarios b
	INNER JOIN cont_forma_pago f ON b.forma_pago_id = f.id
	LEFT JOIN tbl_bancos ba ON b.banco_id = ba.id
	WHERE b.id IN(" . $ids . ")
	ORDER BY b.id ASC";

	$query = $mysqli->query($sql);
	$row_count = $query->num_rows;

	if ($row_count > 0) {
		$contador = 1;

		while ($row = $query->fetch_assoc()) {
			$html .= '<tr>';

			$html .= '<td>' . $contador . '</td>';
			$html .= '<td>' . $row["num_docu"] . '</td>';
			$html .= '<td>' . $row["nombre"] . '</td>';
			$html .= '<td>' . $row["forma_pago"] . '</td>';
			$html .= '<td>' . $row["banco"] . '</td>';
			$html .= '<td>' . $row["num_cuenta_bancaria"] . '</td>';
			$html .= '<td>' . $row["num_cuenta_cci"] . '</td>';

			if ($row["tipo_monto_id"] == '1') {
				$monto_a_depositar = number_format($row["monto"], 2, '.', ',');
			} else if ($row["tipo_monto_id"] == '2') {
				$monto_a_depositar = $row["monto"] . '%';
			} else if ($row["tipo_monto_id"] == '3') {
				$monto_a_depositar = 'Totalidad de la renta';
			}

			$html .= '<td>' . $monto_a_depositar . '</td>';

			$html .= '<td>';
			$html .= '<a class="btn btn-success btn-xs" data-toggle="tooltip" data-placement="top" title="Editar" onclick="sec_contrato_nuevo_editar_beneficiario(' . $row["id"] . ')">';
			$html .= '<i class="fa fa-edit"></i>';
			$html .= '</a>';
			$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_contrato_nuevo_eliminar_beneficiario(' . $row["id"] . ')">';
			$html .= '<i class="fa fa-trash"></i>';
			$html .= '</a>';
			$html .= '</td>';

			$html .= '</tr>';

			$contador++;
		}
	}

	$html .= '<tr>';
	$html .= '<td colspan=9 style="text-align:center;">';
	$html .= '<button type="button" class="btn btn-success" onclick="sec_contrato_nuevo_buscar_beneficiario_modal()" style="width: 180px;">';
	$html .= '<i class="icon fa fa-plus"></i>';
	$html .= '<span id="demo-button-text"> Agregar beneficiario</span>';
	$html .= '</button>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '</tbody>';
	$html .= '</table>';

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $html;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_beneficiarios_ca") {

	$id_beneficiarios = $_POST["id_beneficiarios"];
	$contador_array_ids = 0;

	$data = json_decode($id_beneficiarios);
	$ids = '';

	foreach ($data as $value) {
		if ($contador_array_ids > 0) {
			$ids .= ',';
		}
		$ids .= $value;
		$contador_array_ids++;
	}

	if ($contador_array_ids == 0) {
		$ids = 0;
	}

	$html = '<table class="table table-bordered table-hover no-mb" style="font-size:11px; margin-top: 10px;">';
	$html .= '<thead>';

	$html .= '<tr>';

	$html .= '<th>#</th>';
	$html .= '<th>Número de RUC</th>';
	$html .= '<th>Nombre del Beneficiario</th>';
	$html .= '<th>Forma de Pago</th>';
	$html .= '<th>Banco</th>';
	$html .= '<th>Número de cuenta bancaria</th>';
	$html .= '<th>Número de CCI</th>';
	$html .= '<th>Monto a depositar</th>';
	$html .= '<th>Opciones</th>';

	$html .= '</tr>';

	$html .= '</thead>';
	$html .= '<tbody>';

	$sql = "SELECT 
	b.id, 
	b.nombre, 
	b.num_docu, 
	f.nombre AS forma_pago, 
	ba.nombre AS banco, 
	b.num_cuenta_bancaria, 
	b.num_cuenta_cci,
	b.tipo_monto_id,
	b.monto
	FROM cont_beneficiarios b
	INNER JOIN cont_forma_pago f ON b.forma_pago_id = f.id
	LEFT JOIN tbl_bancos ba ON b.banco_id = ba.id
	WHERE b.id IN(" . $ids . ")
	ORDER BY b.id ASC";

	$query = $mysqli->query($sql);
	$row_count = $query->num_rows;

	if ($row_count > 0) {
		$contador = 1;

		while ($row = $query->fetch_assoc()) {
			$html .= '<tr>';

			$html .= '<td>' . $contador . '</td>';
			$html .= '<td>' . $row["num_docu"] . '</td>';
			$html .= '<td>' . $row["nombre"] . '</td>';
			$html .= '<td>' . $row["forma_pago"] . '</td>';
			$html .= '<td>' . $row["banco"] . '</td>';
			$html .= '<td>' . $row["num_cuenta_bancaria"] . '</td>';
			$html .= '<td>' . $row["num_cuenta_cci"] . '</td>';

			if ($row["tipo_monto_id"] == '1') {
				$monto_a_depositar = number_format($row["monto"], 2, '.', ',');
			} else if ($row["tipo_monto_id"] == '2') {
				$monto_a_depositar = $row["monto"] . '%';
			} else if ($row["tipo_monto_id"] == '3') {
				$monto_a_depositar = 'Totalidad de la renta';
			}

			$html .= '<td>' . $monto_a_depositar . '</td>';

			$html .= '<td>';
			$html .= '<a class="btn btn-success btn-xs" data-toggle="tooltip" data-placement="top" title="Editar" onclick="sec_contrato_nuevo_editar_beneficiario(' . $row["id"] . ')">';
			$html .= '<i class="fa fa-edit"></i>';
			$html .= '</a>';
			$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_contrato_nuevo_eliminar_beneficiario(' . $row["id"] . ')">';
			$html .= '<i class="fa fa-trash"></i>';
			$html .= '</a>';
			$html .= '</td>';

			$html .= '</tr>';

			$contador++;
		}
	}

	$html .= '<tr>';
	$html .= '<td colspan=9 style="text-align:center;">';
	$html .= '<button type="button" class="btn btn-success" onclick="sec_contrato_nuevo_buscar_beneficiario_modal()" style="width: 180px;">';
	$html .= '<i class="icon fa fa-plus"></i>';
	$html .= '<span id="demo-button-text"> Agregar beneficiario</span>';
	$html .= '</button>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '</tbody>';
	$html .= '</table>';

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $html;
}


if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_contrato") {
	$usuario_id = $login ? $login['id'] : null;

	if ((int) $usuario_id > 0) {
		$created_at = date("Y-m-d H:i:s");
		$error = '';

		$query_update = "UPDATE cont_correlativo SET numero = numero + 1 WHERE tipo_contrato = 1 AND status = 1 ";

		$mysqli->query($query_update);

		if ($mysqli->error) {
			$error .= $mysqli->error . $query_update;
		} else {
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
				WHERE tipo_contrato = 1 AND status = 1 LIMIT 1
			";

			$list_query = $mysqli->query($select_correlativo);

			while ($sel = $list_query->fetch_assoc()) {
				$sigla = $sel["sigla"];
				$numero_correlativo = $sel["numero"];
			}

			// INICIO INSERTAR EN CONTRATO
			$query_insert = " INSERT INTO cont_contrato(
			tipo_contrato_id,
			codigo_correlativo,
			empresa_suscribe_id,
			area_responsable_id,
			persona_responsable_id,
			observaciones,
			tipo_inflacion_id,
			tipo_cuota_extraordinaria_id,
			status,
			etapa_id,
			user_created_id,
			created_at)
			VALUES(
			" . $_POST["tipo_contrato_id"] . ",
			" . $numero_correlativo . ",
			" . $_POST["empresa_suscribe_id"] . ",
			" . $_POST["area_responsable_id"] . ",
			" . $_POST["personal_responsable_id"] . ",
			'" . $_POST["observaciones"] . "',
			'" . $_POST["tipo_inflacion_id"] . "',
			'" . $_POST["tipo_cuota_extraordinaria_id"] . "',
			1,
			1,
			" . $usuario_id . ",
			'" . $created_at . "')";

			$mysqli->query($query_insert);
			$contrato_id = mysqli_insert_id($mysqli);

			if ($mysqli->error) {
				$error .= $mysqli->error . $query_insert;
			}
			// FIN INSERTAR EN CONTRATO



			// INICIO GUARDAR INMUEBLE
			$id_departamento = str_pad(trim($_POST["id_departamento"]), 2, "0", STR_PAD_LEFT);
			$id_provincia = str_pad(trim($_POST["id_provincia"]), 2, "0", STR_PAD_LEFT);
			$id_distrito = str_pad(trim($_POST["id_distrito"]), 2, "0", STR_PAD_LEFT);

			$ubigeo_id = $id_departamento . $id_provincia . $id_distrito;

			if (empty($_POST["monto_o_porcentaje_agua"])) {
				$monto_o_porcentaje_agua = "NULL";
			} else {
				$monto_o_porcentaje_agua = str_replace(",", "", $_POST["monto_o_porcentaje_agua"]);
			}

			if (empty($_POST["monto_o_porcentaje_luz"])) {
				$monto_o_porcentaje_luz = "NULL";
			} else {
				$monto_o_porcentaje_luz = str_replace(",", "", $_POST["monto_o_porcentaje_luz"]);
			}

			if (empty($_POST["porcentaje_pago_arbitrios"])) {
				$porcentaje_pago_arbitrios = "NULL";
			} else {
				$porcentaje_pago_arbitrios = $_POST["porcentaje_pago_arbitrios"];
			}

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
			'" . str_replace("'", "", trim($_POST["ubicacion"])) . "',
			" . str_replace(",", "", trim($_POST["area_arrendada"])) . ",
			'" . $_POST["num_partida_registral"] . "',
			'" . str_replace("'", "", trim($_POST["oficina_registral"])) . "',
			'" . $_POST["num_suministro_agua"] . "',
			" . $_POST["tipo_compromiso_pago_agua"] . ",
			" . $monto_o_porcentaje_agua . ",
			'" . $_POST["num_suministro_luz"] . "',
			" . $_POST["tipo_compromiso_pago_luz"] . ",
			" . $monto_o_porcentaje_luz . ",
			" . $_POST["tipo_compromiso_pago_arbitrios"] . ",
			" . $porcentaje_pago_arbitrios . ",
			'" . $_POST["latitud"] . "',
			'" . $_POST["longitud"] . "',
			" . $usuario_id . ",
			'" . $created_at . "'
			)";


			$mysqli->query($query_insert);
			$insert_id = mysqli_insert_id($mysqli);
			if ($mysqli->error) {
				$error .= $mysqli->error . $query_insert;
			}
			// FIN GUARDAR INMUEBLE


			// INICIO INSERTAR EN CONDICIONES DE CONTRATO
			$monto_renta = str_replace(",", "", $_POST["monto_renta"]);
			$tipo_moneda_id = $_POST["tipo_moneda_renta_pactada"];
			$pago_renta_id = $_POST["tipo_pago_de_renta_id"];
			$afectacion_igv_id = $_POST["tipo_igv_renta_id"];
			$impuesto_a_la_renta_id = $_POST["impuesto_a_la_renta_id"];
			$carta_de_instruccion_id = $_POST["impuesto_a_la_renta_carta_de_instruccion_id"];
			$monto_garantia = str_replace(",", "", $_POST["monto_garantia"]);
			$tipo_adelanto_id = $_POST["tipo_adelanto_id"];
			$plazo_id = $_POST["plazo_id"];
			$cant_meses_contrato = trim($_POST["vigencia_del_contrato_en_meses"]);
			$contrato_inicio_fecha = $_POST["contrato_inicio_fecha"];
			$contrato_fin_fecha = $_POST["contrato_fin_fecha"];
			$contrato_fecha_suscripcion = trim($_POST["contrato_fecha_suscripcion"]);
			$periodo_gracia_id = $_POST["periodo_gracia_id"];
			$periodo_gracia_numero = trim($_POST["periodo_gracia_numero"]);

			if ((int) $pago_renta_id == 2) {
				$cuota_variable = $_POST["porcentaje_venta"];
				$tipo_venta_id = $_POST["tipo_venta_id"];
			} else {
				$cuota_variable = "NULL";
				$tipo_venta_id = "NULL";
			}

			if ((int) $impuesto_a_la_renta_id == 4) {
				$numero_cuenta_detraccion = "'" . trim($_POST["numero_cuenta_detraccion"]) . "'";
			} else {
				$numero_cuenta_detraccion = "NULL";
			}

			if ($plazo_id == 1) {
				if (empty($cant_meses_contrato)) {
					$cant_meses_contrato = "NULL";
				}

				if (empty($contrato_inicio_fecha)) {
					$inicio_fecha = "NULL";
				} else {
					$inicio_fecha = "'" . date("Y-m-d", strtotime($contrato_inicio_fecha)) . "'";
				}

				if (empty($contrato_fin_fecha)) {
					$fin_fecha = "NULL";
				} else {
					$fin_fecha = "'" . date("Y-m-d", strtotime($contrato_fin_fecha)) . "'";
				}

				if (empty($contrato_fecha_suscripcion)) {
					$fecha_suscripcion = "NULL";
				} else {
					$fecha_suscripcion = "'" . date("Y-m-d", strtotime($contrato_fecha_suscripcion)) . "'";
				}
			} else {

				$cant_meses_contrato = "NULL";

				if (empty($contrato_inicio_fecha)) {
					$inicio_fecha = "NULL";
				} else {
					$inicio_fecha = "'" . date("Y-m-d", strtotime($contrato_inicio_fecha)) . "'";
				}

				$fin_fecha = "NULL";

				if (empty($contrato_fecha_suscripcion)) {
					$fecha_suscripcion = "NULL";
				} else {
					$fecha_suscripcion = "'" . date("Y-m-d", strtotime($contrato_fecha_suscripcion)) . "'";
				}
			}

			if ($periodo_gracia_id == "0") {
				$periodo_gracia_id = "NULL";
				$periodo_gracia_numero = "NULL";
			} else if ($periodo_gracia_id == "1") {
				if (empty($periodo_gracia_numero)) {
					$periodo_gracia_numero = "NULL";
				}
			} else if ($periodo_gracia_id == "2") {
				$periodo_gracia_numero = "NULL";
			}

			$tipo_incremento_id = $_POST["tipo_incremento_id"];

			$query_insert = "INSERT INTO cont_condicion_economica(
			contrato_id,
			monto_renta,
			tipo_moneda_id,
			pago_renta_id,
			cuota_variable,
			tipo_venta_id,
			afectacion_igv_id,
			impuesto_a_la_renta_id,
			carta_de_instruccion_id,
			numero_cuenta_detraccion,
			periodo_gracia_id,
			periodo_gracia_numero,
			garantia_monto,
			tipo_adelanto_id,
			plazo_id,
			cant_meses_contrato,
			fecha_inicio,
			fecha_fin,
			tipo_incremento_id,
			fecha_suscripcion,
			status,
			user_created_id,
			created_at
			)
			VALUES
			(
			" . $contrato_id . ",
			" . $monto_renta . ",
			" . $tipo_moneda_id . ",
			" . $pago_renta_id . ",
			" . $cuota_variable . ",
			" . $tipo_venta_id . ",
			" . $afectacion_igv_id . ",
			" . $impuesto_a_la_renta_id . ",
			" . $carta_de_instruccion_id . ",
			" . $numero_cuenta_detraccion . ",

			" . $periodo_gracia_id . ",
			" . $periodo_gracia_numero . ",

			" . $monto_garantia . ",
			" . $tipo_adelanto_id . ",
			" . $plazo_id . ",
			" . $cant_meses_contrato . ",
			" . $inicio_fecha . ",
			" . $fin_fecha . ",
			" . $tipo_incremento_id . ",
			" . $fecha_suscripcion . ",
			1,
			" . $usuario_id . ",
			'" . $created_at . "')";

			$mysqli->query($query_insert);

			if ($mysqli->error) {
				$error .= $mysqli->error . $query_insert;
			}
			// FIN INSERTAR EN CONDICIONES DE CONTRATO


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

				if ($mysqli->error) {
					$error .= $mysqli->error . $query_insert;
				}
			}
			// FIN INSERTAR PROPIETARIOS


			// INICIO INCREMENTOS
			if ($tipo_incremento_id == '1') {
				$id_incrementos = $_POST["id_incrementos"];
				$data_incrementos = json_decode($id_incrementos);
				foreach ($data_incrementos as $value_id_incrementos) {
					$query_update = "
					UPDATE cont_incrementos
					SET 
						contrato_id = " . $contrato_id . ",
						user_updated_id = " . $usuario_id . ",
						updated_at = '" . $created_at . "'
					WHERE id = " . $value_id_incrementos . "
					";
					$mysqli->query($query_update);

					if ($mysqli->error) {
						$error .= $mysqli->error . $query_update;
					}
				}
			}
			// FIN INCREMENTOS

			// INICIO INFLACION
			if ($_POST["tipo_inflacion_id"] = 1) {
				$sql = "SELECT c.tipo_moneda_id, c.fecha_inicio, c.contrato_id
				FROM  cont_condicion_economica AS c 
				WHERE c.contrato_id = " . $contrato_id;
				$query = $mysqli->query($sql);
				$row_ce = $query->fetch_assoc();

				$array_inflacion_contrato = isset($_POST["id_inflaciones"]) ? $_POST["id_inflaciones"] : [];
				$data_inflacion = json_decode($array_inflacion_contrato);
				foreach ($data_inflacion as $value_id_inflacion) {
					$query_update = "
					UPDATE cont_inflaciones SET 
						moneda_id = '" . $row_ce['tipo_moneda_id'] . "',
						contrato_id = " . $contrato_id . "
					WHERE id = " . $value_id_inflacion . "
					";
					$mysqli->query($query_update);
					if ($mysqli->error) {
						$error .= $mysqli->error . $query_update;
					}
				}
			}
			// FIN INFLACION

			// INICIO CUOTA EXTRAORDINARIA
			if ($_POST['tipo_cuota_extraordinaria_id'] = 1) {
				$array_cuota_extraordinaria_contrato = $_POST["id_cuenta_extraordinaria"];
				$data_cuota_extraordinaria = json_decode($array_cuota_extraordinaria_contrato);
				foreach ($data_cuota_extraordinaria as $value_id_cuota_extraordinaria) {
					$query_update = "
					UPDATE cont_cuotas_extraordinarias 
					SET 
						contrato_id = " . $contrato_id . "
					WHERE id = " . $value_id_cuota_extraordinaria . "
					";
					$mysqli->query($query_update);

					if ($mysqli->error) {
						$error .= $mysqli->error . $query_update;
					}
				}
			}
			// FIN CUOTA EXTRAORDINARIA

			// INICIO BENEFICIARIOS
			$id_beneficiarios = $_POST["id_beneficiarios"];
			$data_beneficiarios = json_decode($id_beneficiarios);
			foreach ($data_beneficiarios as $value_id_beneficiario) {
				$query_update = "
				UPDATE cont_beneficiarios 
				SET 
					contrato_id = " . $contrato_id . ",
					user_updated_id = " . $usuario_id . ",
					updated_at = '" . $created_at . "'
				WHERE id = " . $value_id_beneficiario . "
				";
				$mysqli->query($query_update);

				if ($mysqli->error) {
					$error .= $mysqli->error . $query_update;
				}
			}
			// FIN BENEFICIARIOS


			// INICIO ADELANTOS
			if ($tipo_adelanto_id == '1') {
				$id_adelantos = $_POST["id_adelantos"];
				$data_adelantos = json_decode($id_adelantos);
				foreach ($data_adelantos as $value_id_adelanto) {
					$query_update = "
					UPDATE cont_adelantos 
					SET 
						contrato_id = " . $contrato_id . ",
						user_updated_id = " . $usuario_id . ",
						updated_at = '" . $created_at . "'
					WHERE id = " . $value_id_adelanto . "
					";
					$mysqli->query($query_update);

					if ($mysqli->error) {
						$error .= $mysqli->error . $query_update;
					}
				}
			}
			// FIN ADELANTOS


			// INICIO CARGAR PDF
			$path = "/var/www/html/files_bucket/contratos/solicitudes/locales/";
			$usuario_id = $login ? $login['id'] : null;
			$created_at = date("Y-m-d H:i:s");

			if (isset($_FILES['archivo_partida_registral']) && $_FILES['archivo_partida_registral']['error'] === UPLOAD_ERR_OK) {
				if (!is_dir($path)) mkdir($path, 0777, true);

				$filename = $_FILES['archivo_partida_registral']['name'];
				$filenametem = $_FILES['archivo_partida_registral']['tmp_name'];
				$filesize = $_FILES['archivo_partida_registral']['size'];
				$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
				if ($filename != "") {
					$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
					$nombre_archivo = $contrato_id . "_PARTIDA_REGISTRAL_" . date('YmdHis') . "." . $fileExt;
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

					if ($mysqli->error) {
						$error .= 'Error al guardar la partida registral: ' . $mysqli->error . $comando;
					}
				}
			}

			if (isset($_FILES['archivo_recibo_agua']) && $_FILES['archivo_recibo_agua']['error'] === UPLOAD_ERR_OK) {
				if (!is_dir($path)) mkdir($path, 0777, true);

				$filename = $_FILES['archivo_recibo_agua']['name'];
				$filenametem = $_FILES['archivo_recibo_agua']['tmp_name'];
				$filesize = $_FILES['archivo_recibo_agua']['size'];
				$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
				if ($filename != "") {
					$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
					$nombre_archivo = $contrato_id . "_RECIBO_AGUA_" . date('YmdHis') . "." . $fileExt;
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
									9,
									'" . $nombre_archivo . "',
									'" . $fileExt . "',
									'" . $filesize . "',
									'" . $path . "',
									" . $usuario_id . ",
									'" . $created_at . "'
									)";
					$mysqli->query($comando);

					if ($mysqli->error) {
						$error .= 'Error al guardar el recibo de agua: ' . $mysqli->error . $comando;
					}
				}
			}

			if (isset($_FILES['archivo_recibo_luz']) && $_FILES['archivo_recibo_luz']['error'] === UPLOAD_ERR_OK) {
				if (!is_dir($path)) mkdir($path, 0777, true);

				$filename = $_FILES['archivo_recibo_luz']['name'];
				$filenametem = $_FILES['archivo_recibo_luz']['tmp_name'];
				$filesize = $_FILES['archivo_recibo_luz']['size'];
				$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
				if ($filename != "") {
					$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
					$nombre_archivo = $contrato_id . "_RECIBO_LUZ_" . date('YmdHis') . "." . $fileExt;
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
									10,
									'" . $nombre_archivo . "',
									'" . $fileExt . "',
									'" . $filesize . "',
									'" . $path . "',
									" . $usuario_id . ",
									'" . $created_at . "'
									)";
					$mysqli->query($comando);

					if ($mysqli->error) {
						$error .= 'Error al guardar el recibo de luz: ' . $mysqli->error . $comando;
					}
				}
			}

			if (isset($_FILES['archivo_dni_propietario']) && $_FILES['archivo_dni_propietario']['error'] === UPLOAD_ERR_OK) {
				if (!is_dir($path)) mkdir($path, 0777, true);

				$filename = $_FILES['archivo_dni_propietario']['name'];
				$filenametem = $_FILES['archivo_dni_propietario']['tmp_name'];
				$filesize = $_FILES['archivo_dni_propietario']['size'];
				$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
				if ($filename != "") {
					$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
					$nombre_archivo = $contrato_id . "_DNI_PROPIETARIO_" . date('YmdHis') . "." . $fileExt;
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

					if ($mysqli->error) {
						$error .= 'Error al guardar el DNI del propietario: ' . $mysqli->error . $comando;
					}
				}
			}

			if (isset($_FILES['archivo_vigencia_poder']) && $_FILES['archivo_vigencia_poder']['error'] === UPLOAD_ERR_OK) {
				if (!is_dir($path)) mkdir($path, 0777, true);

				$filename = $_FILES['archivo_vigencia_poder']['name'];
				$filenametem = $_FILES['archivo_vigencia_poder']['tmp_name'];
				$filesize = $_FILES['archivo_vigencia_poder']['size'];
				$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
				if ($filename != "") {
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

					if ($mysqli->error) {
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
				if ($filename != "") {
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

					if ($mysqli->error) {
						$error .= 'Error al guardar el DNI del representante legal: ' . $mysqli->error . $comando;
					}
				}
			}

			if (isset($_FILES['archivo_hr_inmueble']) && $_FILES['archivo_hr_inmueble']['error'] === UPLOAD_ERR_OK) {
				if (!is_dir($path)) mkdir($path, 0777, true);

				$filename = $_FILES['archivo_hr_inmueble']['name'];
				$filenametem = $_FILES['archivo_hr_inmueble']['tmp_name'];
				$filesize = $_FILES['archivo_hr_inmueble']['size'];
				$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
				if ($filename != "") {
					$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
					$nombre_archivo = $contrato_id . "_HR_INMUEBLE_" . date('YmdHis') . "." . $fileExt;
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
									14,
									'" . $nombre_archivo . "',
									'" . $fileExt . "',
									'" . $filesize . "',
									'" . $path . "',
									" . $usuario_id . ",
									'" . $created_at . "'
									)";
					$mysqli->query($comando);

					if ($mysqli->error) {
						$error .= 'Error al guardar el HR del inmueble: ' . $mysqli->error . $comando;
					}
				}
			}

			if (isset($_FILES['archivo_pu_inmueble']) && $_FILES['archivo_pu_inmueble']['error'] === UPLOAD_ERR_OK) {
				if (!is_dir($path)) mkdir($path, 0777, true);

				$filename = $_FILES['archivo_pu_inmueble']['name'];
				$filenametem = $_FILES['archivo_pu_inmueble']['tmp_name'];
				$filesize = $_FILES['archivo_pu_inmueble']['size'];
				$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
				if ($filename != "") {
					$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
					$nombre_archivo = $contrato_id . "_PU_INMUEBLE_" . date('YmdHis') . "." . $fileExt;
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
									15,
									'" . $nombre_archivo . "',
									'" . $fileExt . "',
									'" . $filesize . "',
									'" . $path . "',
									" . $usuario_id . ",
									'" . $created_at . "'
									)";
					$mysqli->query($comando);

					if ($mysqli->error) {
						$error .= 'Error al guardar el PU del inmueble: ' . $mysqli->error . $comando;
					}
				}
			}

			if (isset($_FILES['archivo_pago_recibo_agua']) && $_FILES['archivo_pago_recibo_agua']['error'] === UPLOAD_ERR_OK) {
				if (!is_dir($path)) mkdir($path, 0777, true);

				$filename = $_FILES['archivo_pago_recibo_agua']['name'];
				$filenametem = $_FILES['archivo_pago_recibo_agua']['tmp_name'];
				$filesize = $_FILES['archivo_pago_recibo_agua']['size'];
				$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
				if ($filename != "") {
					$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
					$nombre_archivo = $contrato_id . "_RECIBO_PAGO_AGUA_" . date('YmdHis') . "." . $fileExt;
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
									20,
									'" . $nombre_archivo . "',
									'" . $fileExt . "',
									'" . $filesize . "',
									'" . $path . "',
									" . $usuario_id . ",
									'" . $created_at . "'
									)";
					$mysqli->query($comando);

					if ($mysqli->error) {
						$error .= 'Error al guardar el recibo de pago de agua del inmueble: ' . $mysqli->error . $comando;
					}
				}
			}


			if (isset($_FILES['archivo_pago_recibo_luz']) && $_FILES['archivo_pago_recibo_luz']['error'] === UPLOAD_ERR_OK) {
				if (!is_dir($path)) mkdir($path, 0777, true);

				$filename = $_FILES['archivo_pago_recibo_luz']['name'];
				$filenametem = $_FILES['archivo_pago_recibo_luz']['tmp_name'];
				$filesize = $_FILES['archivo_pago_recibo_luz']['size'];
				$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
				if ($filename != "") {
					$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
					$nombre_archivo = $contrato_id . "_RECIBO_PAGO_LUZ_" . date('YmdHis') . "." . $fileExt;
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
									21,
									'" . $nombre_archivo . "',
									'" . $fileExt . "',
									'" . $filesize . "',
									'" . $path . "',
									" . $usuario_id . ",
									'" . $created_at . "'
									)";
					$mysqli->query($comando);

					if ($mysqli->error) {
						$error .= 'Error al guardar el recibo de pago de luz del inmueble: ' . $mysqli->error . $comando;
					}
				}
			}

			if (isset($_FILES['archivo_pago_impuesto_predial']) && $_FILES['archivo_pago_impuesto_predial']['error'] === UPLOAD_ERR_OK) {
				if (!is_dir($path)) mkdir($path, 0777, true);

				$filename = $_FILES['archivo_pago_impuesto_predial']['name'];
				$filenametem = $_FILES['archivo_pago_impuesto_predial']['tmp_name'];
				$filesize = $_FILES['archivo_pago_impuesto_predial']['size'];
				$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
				if ($filename != "") {
					$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
					$nombre_archivo = $contrato_id . "_RECIBO_PAGO_IMPUESTO_PREDIAL_" . date('YmdHis') . "." . $fileExt;
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
									22,
									'" . $nombre_archivo . "',
									'" . $fileExt . "',
									'" . $filesize . "',
									'" . $path . "',
									" . $usuario_id . ",
									'" . $created_at . "'
									)";
					$mysqli->query($comando);

					if ($mysqli->error) {
						$error .= 'Error al guardar el recibo de pago de impuesto predial del inmueble: ' . $mysqli->error . $comando;
					}
				}
			}

			if (isset($_FILES['archivo_pago_arbitrios']) && $_FILES['archivo_pago_arbitrios']['error'] === UPLOAD_ERR_OK) {
				if (!is_dir($path)) mkdir($path, 0777, true);

				$filename = $_FILES['archivo_pago_arbitrios']['name'];
				$filenametem = $_FILES['archivo_pago_arbitrios']['tmp_name'];
				$filesize = $_FILES['archivo_pago_arbitrios']['size'];
				$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
				if ($filename != "") {
					$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
					$nombre_archivo = $contrato_id . "_RECIBO_PAGO_ARBITRIOS_" . date('YmdHis') . "." . $fileExt;
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
									23,
									'" . $nombre_archivo . "',
									'" . $fileExt . "',
									'" . $filesize . "',
									'" . $path . "',
									" . $usuario_id . ",
									'" . $created_at . "'
									)";
					$mysqli->query($comando);

					if ($mysqli->error) {
						$error .= 'Error al guardar el recibo de pago de arbitrios del inmueble: ' . $mysqli->error . $comando;
					}
				}
			}
			// FIN CARGAR PDF


			// INICIO DE CARGAR NUEVOS ANEXOS
			if (isset($_FILES["miarchivo"])) {
				if ($_FILES["miarchivo"]) {
					//Recorre el array de los archivos a subir
					$h = '';
					foreach ($_FILES["miarchivo"]['tmp_name'] as $key => $tmp_name) {
						//Si el archivo existe
						if ($_FILES["miarchivo"]["name"][$key]) {
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
								if ($value->nombre_archivo == $file_name && $value->tamano_archivo == $filesize && $value->extension == $fileExt) {
									$nombre_tipo_archivo = str_replace(' ', '_', $value->tip_doc_nombre);
									$nombre_tipo_archivo = strtoupper($nombre_tipo_archivo);
									$tipo_archivo = $value->id_tip_documento;
								}
							}

							$nombre_archivo = $contrato_id . "_" . $nombre_tipo_archivo . "_" . date('YmdHis') . "." . $fileExt;

							if (!file_exists($path)) {
								mkdir($path, 0777); //or die("Hubo un error al crear la carpeta");	
							}
							$dir = opendir($path);
							if (move_uploaded_file($fuente, $path . '/' . $nombre_archivo)) {
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
								if ($mysqli->error) {
									$error .= 'Error al guardar el nuevo anexo' . $mysqli->error . $comando;
								}
							} else {
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
			send_email_solicitud_contrato_arrendamiento($contrato_id);
			send_email_solicitud_contrato_arrendamiento_detallado($contrato_id, false, false);
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
		$result["error"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y volver a hacer clic en el botón: Enviar Solicitud de Arrendamiento.";
	}
}


//= MODULO DE ADENDAS =//


if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_adenda_detalle") {

	$usuario_id = $login ? $login['id'] : null;
	$created_at = date("Y-m-d H:i:s");
	$valor_original = $_POST["valor_original"];
	$tipo_valor = $_POST["tipo_valor"];

	if (isset($_POST["id_del_registro"])) {
		$id_del_registro = empty(trim($_POST["id_del_registro"])) ? 0 : $_POST["id_del_registro"];
	} else {
		$id_del_registro = 0;
	}

	$ubigeo_id_nuevo = isset($_POST["ubigeo_id_nuevo"]) ? $_POST["ubigeo_id_nuevo"] : '';
	$ubigeo_text_nuevo = isset($_POST["ubigeo_text_nuevo"]) ? $_POST["ubigeo_text_nuevo"] : '';

	if ($tipo_valor == 'varchar') {
		$valor_varchar = $_POST["valor_varchar"];
		$valor_int = "NULL";
		$valor_date = "NULL";
		$valor_decimal = "NULL";
		$valor_select_option = "NULL";
		$valor_id_tabla = "NULL";
	} else if ($tipo_valor == 'int') {
		$valor_varchar = "NULL";
		$valor_int = $_POST["valor_int"];
		$valor_date = "NULL";
		$valor_decimal = "NULL";
		$valor_select_option = "NULL";
		$valor_id_tabla = "NULL";
	} else if ($tipo_valor == 'date') {
		$valor_varchar = "NULL";
		$valor_int = "NULL";
		$valor_original = date("Y-m-d", strtotime($valor_original));
		$valor_date = "'" . date("Y-m-d", strtotime($_POST["valor_date"])) . "'";
		$valor_decimal = "NULL";
		$valor_select_option = "NULL";
		$valor_id_tabla = "NULL";
	} else if ($tipo_valor == 'decimal') {
		$valor_varchar = "NULL";
		$valor_int = "NULL";
		$valor_date = "NULL";
		$valor_decimal = str_replace(",", "", $_POST["valor_decimal"]);
		$valor_select_option = "NULL";
		$valor_id_tabla = "NULL";
	} else if ($tipo_valor == 'select_option') {

		if ($_POST["nombre_campo"] == "ubigeo_id") {
			$valor_varchar = $ubigeo_id_nuevo;
			$valor_int = "NULL";
			$valor_date = "NULL";
			$valor_decimal = "NULL";
			$valor_select_option = $ubigeo_text_nuevo;
			$valor_id_tabla = "NULL";
		} else {
			$valor_varchar = "NULL";
			$valor_int = $_POST["valor_int"];
			$valor_date = "NULL";
			$valor_decimal = "NULL";
			$valor_select_option = $_POST["valor_select_option"];
			$valor_id_tabla = "NULL";
		}
	} else if ($tipo_valor == 'id_tabla') {
		$valor_varchar = "NULL";
		$valor_int = $_POST["valor_int"];
		$valor_date = "NULL";
		$valor_decimal = "NULL";
		$valor_select_option = "NULL";
		$valor_id_tabla = $_POST["valor_id_tabla"];
	}


	$query_insert = " INSERT INTO cont_adendas_detalle
	(
	nombre_tabla,
	valor_original,
	nombre_campo,
	nombre_menu_usuario,
	nombre_campo_usuario,
	tipo_valor,
	valor_varchar,
	valor_int,
	valor_date,
	valor_decimal,
	valor_select_option,
	valor_id_tabla,
	id_del_registro_a_modificar,
	user_created_id,
	created_at)
	VALUES
	(
	'" . $_POST["nombre_tabla"] . "',
	'" . $valor_original . "',
	'" . $_POST["nombre_campo"] . "',
	'" . $_POST["nombre_menu_usuario"] . "',
	'" . $_POST["nombre_campo_usuario"] . "',
	'" . $tipo_valor . "',
	'" . $valor_varchar . "',
	" . $valor_int . ",
	" . $valor_date . ",
	" . $valor_decimal . ",
	'" . $valor_select_option . "',
	" . $valor_id_tabla . ",
	" . $id_del_registro . ",
	" . $usuario_id . ",
	'" . $created_at . "')";

	$mysqli->query($query_insert);
	$insert_id = mysqli_insert_id($mysqli);
	$error = '';
	if ($mysqli->error) {
		$error = $mysqli->error . " | " . $query_insert;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $insert_id;
	$result["error"] = $error;
}




if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_adendas_detalle") {

	$id_adendas = $_POST["id_adendas"];
	$html = '';

	$query = "SELECT id, nombre_menu_usuario, nombre_campo_usuario, valor_original, tipo_valor, valor_varchar, valor_int, valor_date, valor_decimal, valor_select_option
	FROM cont_adendas_detalle 
	WHERE tipo_valor != 'id_tabla' AND tipo_valor != 'registro' ";
	$data = json_decode($id_adendas);
	$contador = 0;
	$ids = '';
	foreach ($data as $value) {
		if ($contador > 0) {
			$ids .= ',';
		}
		$ids .= $value;
		$contador++;
	}
	$query .= " AND id IN(" . $ids . ")";

	$list_query = $mysqli->query($query);
	$row_count = $list_query->num_rows;

	if ($row_count > 0) {
		$html .= '<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th>#</th>';
		$html .= '<th>Menú</th>';
		$html .= '<th>Campo</th>';
		$html .= '<th>Valor Actual</th>';
		$html .= '<th>Nuevo Valor</th>';
		$html .= '<th></th>';
		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody>';

		$num = 1;

		while ($row = $list_query->fetch_assoc()) {
			$tipo_valor = $row["tipo_valor"];

			if ($tipo_valor == 'varchar') {
				$nuevo_valor = $row["valor_varchar"];
			} else if ($tipo_valor == 'int') {
				$nuevo_valor = $row["valor_int"];
			} else if ($tipo_valor == 'date') {
				$nuevo_valor = $row["valor_date"];
			} else if ($tipo_valor == 'decimal') {
				$nuevo_valor = $row["valor_decimal"];
			} else if ($tipo_valor == 'select_option') {
				$nuevo_valor = $row["valor_select_option"];
			}

			$html .= '<tr>';
			$html .= '<td>' . $num . '</td>';
			$html .= '<td>' . $row["nombre_menu_usuario"] . '</td>';
			$html .= '<td>' . $row["nombre_campo_usuario"] . '</td>';
			$html .= '<td style="white-space: pre-line;">' . $row["valor_original"] . '</td>';
			$html .= '<td style="white-space: pre-line;">' . $nuevo_valor . '</td>';
			$html .= '<td>';
			$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_contrato_nuevo_eliminar_detalle_adenda(' . $row["id"] . ')"><i class="fa fa-trash"></i></a>';
			$html .= '</td>';
			$html .= '</tr>';

			$num += 1;
		}

		$html .= '</tbody>';
		$html .= '</table>';
	}

	// CAMBIO DE PROPIETARIOS Y BENEFICIARIOS Y INCREMENTOS
	$query_otros = "SELECT id, nombre_menu_usuario, valor_original, valor_int, valor_id_tabla
	FROM cont_adendas_detalle 
	WHERE tipo_valor = 'id_tabla' ";
	$data = json_decode($id_adendas);
	$contador = 0;
	$ids = '';
	foreach ($data as $value) {
		if ($contador > 0) {
			$ids .= ',';
		}
		$ids .= $value;
		$contador++;
	}
	$query_otros .= " AND id IN(" . $ids . ")";

	$list_query_otros = $mysqli->query($query_otros);
	$row_count_otros = $list_query_otros->num_rows;

	if ($row_count_otros > 0) {
		while ($row = $list_query_otros->fetch_assoc()) {
			if ($row["nombre_menu_usuario"] == 'Propietario') {
				$query = "
				SELECT 
					p.id,
					tp.nombre AS tipo_persona,
					td.nombre AS tipo_docu_identidad,
					p.tipo_persona_id,
					p.tipo_docu_identidad_id,
					p.num_docu,
					p.nombre,
					p.direccion,
					p.representante_legal,
					p.num_partida_registral,
					p.contacto_nombre,
					p.contacto_telefono,
					p.contacto_email
				FROM
					cont_persona p
					INNER JOIN cont_tipo_persona tp ON p.tipo_persona_id = tp.id
					INNER JOIN cont_tipo_docu_identidad td ON p.tipo_docu_identidad_id = td.id
				WHERE
					p.id IN ('" . $row["valor_original"] . "' , '" . $row["valor_int"] . "')
				";

				$valores_originales = [];
				$valores_nuevos = [];
				$list_query = $mysqli->query($query);
				while ($li = $list_query->fetch_assoc()) {
					if ($li["id"] == $row["valor_original"]) {
						$valores_originales[] = $li;
					} else if ($li["id"] == $row["valor_int"]) {
						$valores_nuevos[] = $li;
					}
				}

				$html .= '<br>';
				$html .= '<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">';
				$html .= '<thead>';

				$html .= '<tr>';
				$html .= '<th colspan="4" style="text-align: center; vertical-align: middle;">Propietario</th>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<th>Campo</th>';
				$html .= '<th>Valor Actual</th>';
				$html .= '<th>Nuevo Valor</th>';
				$html .= '<th></th>';
				$html .= '</tr>';

				$html .= '</thead>';
				$html .= '<tbody>';

				$html .= '<tr>';
				$html .= '<td>Tipo de documento de persona</td>';
				$html .= '<td>' . $valores_originales[0]["tipo_persona"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["tipo_persona"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Nombre</td>';
				$html .= '<td>' . $valores_originales[0]["nombre"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["nombre"] . '</td>';
				$html .= '<td rowspan="9">';
				$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_contrato_nuevo_eliminar_detalle_adenda(' . $row["id"] . ')"><i class="fa fa-trash"></i></a></td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Tipo de documento de identidad</td>';
				$html .= '<td>' . $valores_originales[0]["tipo_docu_identidad"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["tipo_docu_identidad"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Número de documento de identidad</td>';
				$html .= '<td>' . $valores_originales[0]["num_docu"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["num_docu"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Dirección</td>';
				$html .= '<td>' . $valores_originales[0]["direccion"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["direccion"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Representante legal</td>';
				$html .= '<td>' . $valores_originales[0]["representante_legal"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["representante_legal"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>N° de Partida Registral de la empresa</td>';
				$html .= '<td>' . $valores_originales[0]["num_partida_registral"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["num_partida_registral"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Contacto - Nombre</td>';
				$html .= '<td>' . $valores_originales[0]["contacto_nombre"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["contacto_nombre"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Contacto - Teléfono</td>';
				$html .= '<td>' . $valores_originales[0]["contacto_telefono"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["contacto_telefono"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Contacto - Email</td>';
				$html .= '<td>' . $valores_originales[0]["contacto_email"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["contacto_email"] . '</td>';
				$html .= '</tr>';

				$html .= '</tbody>';
				$html .= '</table>';
			}

			if ($row["nombre_menu_usuario"] == 'Beneficiario') {
				$query = "
				SELECT 
					b.id,
					tp.nombre AS tipo_persona,
					td.nombre AS tipo_docu_identidad,
					b.num_docu,
					b.nombre,
					f.nombre AS forma_pago,
					ba.nombre AS banco,
					b.num_cuenta_bancaria,
					b.num_cuenta_cci,
					b.tipo_monto_id,
					tm.nombre AS tipo_monto,
					b.monto
				FROM
					cont_beneficiarios b
					LEFT JOIN cont_tipo_persona tp ON b.tipo_persona_id = tp.id
					LEFT JOIN cont_tipo_docu_identidad td ON b.tipo_docu_identidad_id = td.id
					INNER JOIN cont_forma_pago f ON b.forma_pago_id = f.id
					LEFT JOIN tbl_bancos ba ON b.banco_id = ba.id
					INNER JOIN cont_tipo_monto_a_depositar tm ON b.tipo_monto_id = tm.id
				WHERE
					b.id IN ('" . $row["valor_original"] . "' , '" . $row["valor_int"] . "')
				";

				$valores_originales = [];
				$valores_nuevos = [];
				$list_query = $mysqli->query($query);
				while ($li = $list_query->fetch_assoc()) {
					if ($li["id"] == $row["valor_original"]) {
						$valores_originales[] = $li;
					} else if ($li["id"] == $row["valor_int"]) {
						$valores_nuevos[] = $li;
					}
				}

				$html .= '<br>';
				$html .= '<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">';
				$html .= '<thead>';

				$html .= '<tr>';
				$html .= '<th colspan="4" style="text-align: center; vertical-align: middle;">Beneficiario</th>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<th>Campo</th>';
				$html .= '<th>Valor Actual</th>';
				$html .= '<th>Nuevo Valor</th>';
				$html .= '<th></th>';
				$html .= '</tr>';

				$html .= '</thead>';
				$html .= '<tbody>';

				$html .= '<tr>';
				$html .= '<td>Tipo de documento de persona</td>';
				$html .= '<td>' . $valores_originales[0]["tipo_persona"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["tipo_persona"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Nombre</td>';
				$html .= '<td>' . $valores_originales[0]["nombre"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["nombre"] . '</td>';
				$html .= '<td rowspan="9">';
				$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_contrato_nuevo_eliminar_detalle_adenda(' . $row["id"] . ')"><i class="fa fa-trash"></i></a></td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Tipo de documento de identidad</td>';
				$html .= '<td>' . $valores_originales[0]["tipo_docu_identidad"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["tipo_docu_identidad"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Número de documento de identidad</td>';
				$html .= '<td>' . $valores_originales[0]["num_docu"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["num_docu"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Tipo de forma de pago</td>';
				$html .= '<td>' . $valores_originales[0]["forma_pago"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["forma_pago"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Nombre del Banco</td>';
				$html .= '<td>' . $valores_originales[0]["banco"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["banco"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>N° de la cuenta bancaria</td>';
				$html .= '<td>' . $valores_originales[0]["num_cuenta_bancaria"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["num_cuenta_bancaria"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>N° de CCI bancario</td>';
				$html .= '<td>' . $valores_originales[0]["num_cuenta_cci"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["num_cuenta_cci"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Tipo de monto a depositar</td>';
				$html .= '<td>' . $valores_originales[0]["tipo_monto"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["tipo_monto"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Monto</td>';
				$html .= '<td>' . $valores_originales[0]["monto"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["monto"] . '</td>';
				$html .= '</tr>';

				$html .= '</tbody>';
				$html .= '</table>';
			}

			if ($row["nombre_menu_usuario"] == 'Nuevo Incremento') {
				$query = "
				SELECT 
					i.id, 
					i.valor, 
					i.tipo_valor_id,
					tp.nombre AS tipo_valor, 
					i.tipo_continuidad_id, 
					tc.nombre AS tipo_continuidad, 
					i.a_partir_del_año
				FROM 
					cont_incrementos i
					INNER JOIN cont_tipo_pago_incrementos tp ON i.tipo_valor_id = tp.id
					INNER JOIN cont_tipo_continuidad_pago tc ON i.tipo_continuidad_id = tc.id
				WHERE 
					i.id = " . $row["valor_int"];


				$list_query = $mysqli->query($query);
				while ($li = $list_query->fetch_assoc()) {
					$valor_nuevo = $li;
				}

				$html .= '<br>';
				$html .= '<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">';
				$html .= '<thead>';

				$html .= '<tr>';
				$html .= '<th colspan="4" style="text-align: center; vertical-align: middle;">Nuevo Incremento</th>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<th>Campo</th>';
				$html .= '<th>Valor Actual</th>';
				$html .= '<th>Nuevo Valor</th>';
				$html .= '<th></th>';
				$html .= '</tr>';

				$html .= '</thead>';
				$html .= '<tbody>';

				$html .= '<tr>';
				$html .= '<td>Valor</td>';
				$html .= '<td></td>';
				$html .= '<td>' . $valor_nuevo["valor"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Tipo Valor</td>';
				$html .= '<td></td>';
				$html .= '<td>' . $valor_nuevo["tipo_valor"] . '</td>';
				$html .= '<td rowspan="9">';
				$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_contrato_nuevo_eliminar_detalle_adenda(' . $row["id"] . ')"><i class="fa fa-trash"></i></a></td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Continuidad</td>';
				$html .= '<td></td>';
				$html .= '<td>' . $valor_nuevo["tipo_continuidad"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Apartir del</td>';
				$html .= '<td></td>';
				$html .= '<td>' . $valor_nuevo["a_partir_del_año"] . '</td>';
				$html .= '</tr>';

				$html .= '</tbody>';
				$html .= '</table>';
			}
		}
	}



	// CAMBIO DE PROPIETARIOS Y BENEFICIARIOS Y INCREMENTOS
	$query_otros = "SELECT id, nombre_menu_usuario, valor_original, valor_int, valor_id_tabla
	FROM cont_adendas_detalle 
	WHERE tipo_valor = 'registro' ";
	$data = json_decode($id_adendas);
	$contador = 0;
	$ids = '';
	foreach ($data as $value) {
		if ($contador > 0) {
			$ids .= ',';
		}
		$ids .= $value;
		$contador++;
	}
	$query_otros .= " AND id IN(" . $ids . ")";

	$list_query_otros = $mysqli->query($query_otros);
	$row_count_otros = $list_query_otros->num_rows;

	if ($row_count_otros > 0) {
		while ($row = $list_query_otros->fetch_assoc()) {
			if ($row["nombre_menu_usuario"] == 'Representante Legal') {
				$query = "
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
					rl.id IN ('" . $row["valor_int"] . "')
				";

				$valores_originales = [];
				$valores_nuevos = [];
				$list_query = $mysqli->query($query);
				while ($li = $list_query->fetch_assoc()) {
					if ($li["id"] == $row["valor_int"]) {
						$valores_nuevos[] = $li;
					}
				}

				$html .= '<br>';
				$html .= '<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">';
				$html .= '<thead>';

				$html .= '<tr>';
				$html .= '<th colspan="4" style="text-align: center; vertical-align: middle;">Nuevo Representante Legal</th>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<th>Campo</th>';
				$html .= '<th>Nuevo Valor</th>';
				$html .= '<th></th>';
				$html .= '</tr>';

				$html .= '</thead>';
				$html .= '<tbody>';

				$html .= '<tr>';
				$html .= '<td>DNI del representante legal</td>';
				$html .= '<td>' . $valores_nuevos[0]["dni_representante"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Nombre completo del representante legal</td>';
				$html .= '<td>' . $valores_nuevos[0]["nombre_representante"] . '</td>';
				$html .= '<td rowspan="9">';
				$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_contrato_nuevo_eliminar_detalle_adenda(' . $row["id"] . ')"><i class="fa fa-trash"></i></a></td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Nro cuenta de detraccion (Banco de la nación)</td>';
				$html .= '<td>' . $valores_nuevos[0]["nro_cuenta_detraccion"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Banco</td>';
				$html .= '<td>' . $valores_nuevos[0]["banco_representante"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Nro Cuenta</td>';
				$html .= '<td>' . $valores_nuevos[0]["nro_cuenta"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Nro CCI</td>';
				$html .= '<td>' . $valores_nuevos[0]["nro_cci"] . '</td>';
				$html .= '</tr>';


				$html .= '</tbody>';
				$html .= '</table>';
			}

			if ($row["nombre_menu_usuario"] == 'Contraprestación') {
				$query = "
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
					c.id IN ('" . $row["valor_int"] . "')
				";

				$valores_originales = [];
				$valores_nuevos = [];
				$list_query = $mysqli->query($query);
				while ($li = $list_query->fetch_assoc()) {
					if ($li["id"] == $row["valor_int"]) {
						$li["subtotal"] = $li["tipo_moneda_simbolo"] . ' ' . number_format($li["subtotal"], 2, '.', ',');
						$li["igv"] = $li["tipo_moneda_simbolo"] . ' ' . number_format($li["igv"], 2, '.', ',');
						$li["monto"] = $li["tipo_moneda_simbolo"] . ' ' . number_format($li["monto"], 2, '.', ',');
						$valores_nuevos[] = $li;
					}
				}

				$html .= '<br>';
				$html .= '<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">';
				$html .= '<thead>';

				$html .= '<tr>';
				$html .= '<th colspan="4" style="text-align: center; vertical-align: middle;">Nueva Contraprestación</th>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<th>Campo</th>';
				$html .= '<th>Nuevo Valor</th>';
				$html .= '<th></th>';
				$html .= '</tr>';

				$html .= '</thead>';
				$html .= '<tbody>';

				$html .= '<tr>';
				$html .= '<td>Tipo de moneda</td>';
				$html .= '<td>' . $valores_nuevos[0]["tipo_moneda"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Subtotal</td>';
				$html .= '<td>' . $valores_nuevos[0]["subtotal"] . '</td>';
				$html .= '<td rowspan="9">';
				$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_contrato_nuevo_eliminar_detalle_adenda(' . $row["id"] . ')"><i class="fa fa-trash"></i></a></td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>IGV</td>';
				$html .= '<td>' . $valores_nuevos[0]["igv"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Monto Bruto</td>';
				$html .= '<td>' . $valores_nuevos[0]["monto"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Tipo de comprobante a emitir</td>';
				$html .= '<td>' . $valores_nuevos[0]["tipo_comprobante"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Plazo de Pago</td>';
				$html .= '<td>' . $valores_nuevos[0]["plazo_pago"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Forma de pago</td>';
				$html .= '<td>' . $valores_nuevos[0]["forma_pago_detallado"] . '</td>';
				$html .= '</tr>';


				$html .= '</tbody>';
				$html .= '</table>';
			}
		}
	}

	$html .= '<br>';

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $html;
}


if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_adenda") {
	$usuario_id = $login ? $login['id'] : null;
	$created_at = date("Y-m-d H:i:s");
	$tipo_contrato_id = $_POST["tipo_contrato_id"];
	$error = '';

	// INICIO INSERTAR EN ADENDA
	$query_insert = "	INSERT INTO cont_adendas
						(
							contrato_id,
							user_created_id,
							created_at
						)
						VALUES
						(
							" . $_POST["contrato_id"] . ",
							" . $usuario_id . ",
							'" . $created_at . "'
						)
						";

	$mysqli->query($query_insert);
	$adenda_id = mysqli_insert_id($mysqli);

	if ($mysqli->error) {
		$error .= $mysqli->error;
		//echo $query_insert;
	}
	// FIN INSERTAR EN ADENDA


	// INICIO ADENDA DETALLE
	$id_adendas = $_POST["id_adendas"];
	$data_adendas = json_decode($id_adendas);
	foreach ($data_adendas as $value_id_adenda_detalle) {
		$query_update = "
		UPDATE cont_adendas_detalle 
		SET 
			adenda_id = " . $adenda_id . ",
			user_updated_id = " . $usuario_id . ",
			updated_at = '" . $created_at . "'
		WHERE id = " . $value_id_adenda_detalle . "
		";
		$mysqli->query($query_update);
	}

	if ($mysqli->error) {
		$error .= $mysqli->error;
		//echo $query_update;
	}
	// FIN ADENDA DETALLE

	if ($tipo_contrato_id == "4") {
		send_email_solicitud_adenda_contrato_proveedor($adenda_id);
	} else {
		send_email_solicitud_adenda_contrato_arrendamiento($adenda_id);
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = "ok";
	$result["error"] = $error;
}


if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_persona") {
	$query = "SELECT * FROM cont_tipo_persona WHERE estado = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}


if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_docu_identidad") {
	$query = "SELECT * FROM cont_tipo_docu_identidad WHERE estado = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_moneda") {
	$query = "SELECT * FROM tbl_moneda WHERE estado = 1 AND id IN (1,2)";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_pago_renta") {
	$query = "SELECT * FROM cont_tipo_pago_renta WHERE status = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_venta") {
	$query = "SELECT * FROM cont_tipo_venta WHERE status = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_igv_en_la_renta") {
	$query = "SELECT * FROM cont_tipo_afectacion_igv WHERE status = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_banco") {
	$query = "SELECT * FROM tbl_bancos WHERE estado = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_periodo_de_gracia") {
	$query = "SELECT * FROM cont_tipo_periodo_de_gracia WHERE status = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}

//= MODULO DE CONTRATO DE PROVEEDORES =//


if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_contrato_proveedor") {
	include("function_replace_invalid_caracters_contratos.php");

	$usuario_id = $login ? $login['id'] : null;

	if ((int) $usuario_id > 0) {

		$created_at = date("Y-m-d H:i:s");
		$monto = str_replace(",", "", $_POST["monto"]);
		$array_representantes = $_POST["rr_representantes"];
		$data_representantes = json_decode($array_representantes);

		$status = "success";

		$inputs_file = ["archivo_ficha_ruc", "archivo_formato_contrato", "archivo_know_your_client"];
		$extensiones = ["pdf", "jpg", "jpeg", "png", "doc", "docx", "odt", "ppt", "pptx", "xls", "xlsx", "txt", "7z", "rar", "zip"];
		$maxsize = 52428800; // 50 MB
		$error_msg = [];

		foreach ($inputs_file as $value) {
			if (isset($_FILES[$value]) && $_FILES[$value]['error'] === UPLOAD_ERR_OK) {
				$nombre_file = ucwords(str_replace("_", " ", $value));
				if ($_FILES[$value]['size'] > $maxsize) {
					$error_msg[] = 'Archivo debe ser menor a 50MB';
					$status = "error";
				} else if (!in_array(pathinfo(strtolower($_FILES[$value]['name']), PATHINFO_EXTENSION), $extensiones)) {
					$error_msg[] = $nombre_file . ' debe tener extensión .png, .jpg, .pdf.';
					$status = "error";
				}
			}
		}

		if (count($error_msg) > 0) {
			$result["http_code"] = 500;
			$result["status"] = $status;
			$result["mensaje"] = implode('<br> ', $error_msg);
			echo json_encode($result);
			die;
		}

		if ($_POST['correos_adjuntos'] != "") {
			$validate = true;
			$emails = preg_split('[,|;]', $_POST['correos_adjuntos']);
			foreach ($emails as $e) {
				if (preg_match('/^[_ña-z0-9-]+(\.[_ña-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/', trim($e)) == 0) {
					$error_msg = "'" . $e . "'" . " no es un Correo Adjunto válido";
					if ($e == "") {
						$error_msg = "Formato de Correo Incorrecto";
					}
					$result["http_code"] = 500;
					$result["status"] = "error";
					$result["mensaje"] = $error_msg;
					echo json_encode($result);
					die;
				}
			}
		}

		$query_update = "UPDATE cont_correlativo SET numero = numero + 1 WHERE tipo_contrato = 2 AND status = 1";

		$mysqli->query($query_update);

		if ($mysqli->error) {
			$mensaje = $mysqli->error . '. En la siguiente query: ' . $query_update;
			$http_code = 500;
		} else {
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
				WHERE tipo_contrato = 2 AND status = 1 LIMIT 1
			";

			$list_query = $mysqli->query($select_correlativo);

			while ($sel = $list_query->fetch_assoc()) {
				$sigla = $sel["sigla"];
				$numero_correlativo = $sel["numero"];
			}

			$gerente_area_id = trim($_POST["gerente_area_id"]);
			$gerente_area_nombre = '';
			$gerente_area_email = '';

			if ($gerente_area_id == 'A') {
				$gerente_area_id = "NULL";
				$gerente_area_nombre = trim($_POST["nombre_del_gerente_del_area"]);
				$gerente_area_email = trim($_POST["email_del_gerente_del_area"]);
			}

			$aprobacion_obligatoria_id = trim($_POST["aprobacion_obligatoria_id"]);
			$director_aprobacion_id = trim($_POST["director_aprobacion_id"]);
			$plazo_id = trim($_POST["plazo_id"]);
			$periodo_numero = trim($_POST["periodo_numero"]);
			$periodo = trim($_POST["periodo"]);
			$fecha_inicio = trim($_POST["fecha_inicio"]);
			$num_dias_para_alertar_vencimiento = trim($_POST["num_dias_para_alertar_vencimiento"]);
			$alerta_vencimiento_por_fecha_id = trim($_POST["alerta_vencimiento_por_fecha_id"]);
			$fecha_de_la_alerta = "'" . trim($_POST["fecha_de_la_alerta"]) . "'";
			$area_id = trim($_POST["area_id"]);
			if (!empty($director_aprobacion_id)) {
				$aprobacion_obligatoria_id = 1;
			}

			if ($plazo_id == 1) {
				$alerta_vencimiento_por_fecha_id = "NULL";
				$fecha_de_la_alerta = "NULL";
			} elseif ($plazo_id == 2) {
				$periodo_numero = "NULL";
				$periodo = "NULL";
				$num_dias_para_alertar_vencimiento = "NULL";
				if ($alerta_vencimiento_por_fecha_id == 0) {
					$alerta_vencimiento_por_fecha_id = "NULL";
					$fecha_de_la_alerta = "NULL";
				}
			}

			$cargo_id_persona_contacto = !empty($_POST['cargo_id_persona_contacto']) ? $_POST['cargo_id_persona_contacto'] : '0';
			$cargo_id_responsable = !empty($_POST['cargo_id_responsable']) ? $_POST['cargo_id_responsable'] : '0';
			$cargo_id_aprobante = !empty($_POST['cargo_id_aprobante']) ? $_POST['cargo_id_aprobante'] : '0';

			$query_insert = "INSERT INTO cont_contrato
			(
				  tipo_contrato_id
				, codigo_correlativo 
				, empresa_suscribe_id
				, area_responsable_id
				, persona_responsable_id
				, cargo_id_persona_contacto
				, cargo_id_responsable
				, cargo_id_aprobante
				, etapa_id
				, ruc
				, razon_social
				, nombre_comercial
				, check_gerencia_proveedor
				, director_aprobacion_id
				, persona_contacto_proveedor
				, detalle_servicio
				, plazo_id
				, periodo_numero
				, periodo
				, fecha_inicio
				, num_dias_para_alertar_vencimiento
				, alerta_vencimiento_por_fecha_id
				, fecha_de_la_alerta
				, alcance_servicio
				, tipo_terminacion_anticipada_id
				, terminacion_anticipada
				, observaciones
				, status
				, user_created_id
				, created_at
				, estado_solicitud
				, gerente_area_id
				, gerente_area_nombre
				, gerente_area_email
			)
			VALUES
			(
				" . $_POST["tipo_contrato_id"] . ",
				" . $numero_correlativo . ",
				" . $_POST["empresa_id"] . ",
				" . $area_id . ",
				0,
				" . $cargo_id_persona_contacto . ",
				" . $cargo_id_responsable . ",
				" . $cargo_id_aprobante . ",
				1,
				" . $_POST["ruc"] . ",
				'" . replace_invalid_caracters($_POST["razon_social"]) . "',
				'" . replace_invalid_caracters($_POST["nombre_comercial"]) . "',
				$aprobacion_obligatoria_id,
				$director_aprobacion_id,
				'" . replace_invalid_caracters($_POST["persona_contacto_proveedor"]) . "',
				'" . replace_invalid_caracters($_POST["detalle_servicio"]) . "',
				$plazo_id,
				$periodo_numero,
				$periodo,
				'" . $_POST["fecha_inicio"] . "',
				$num_dias_para_alertar_vencimiento,
				$alerta_vencimiento_por_fecha_id,
				$fecha_de_la_alerta,
				'" . replace_invalid_caracters($_POST["alcance_servicio"]) . "',
				" . $_POST["tipo_terminacion_anticipada_id"] . ",
				'" . replace_invalid_caracters($_POST["terminacion_anticipada"]) . "',
				'" . replace_invalid_caracters($_POST["observaciones_legal"]) . "',
				1,
				" . $usuario_id . ",
				'" . $created_at . "',
				1,
				" . $gerente_area_id . ",
				'" . $gerente_area_nombre . "',
				'" . $gerente_area_email . "'
			)";
			//echo "<pre>";print_r($query_insert);echo "</pre>";die();

			$mysqli->query($query_insert);
			$contrato_id = mysqli_insert_id($mysqli);

			if ($mysqli->error) {
				$mensaje = $mysqli->error . '. En la siguiente query: ' . $query_insert;
				$http_code = 500;
			} else {
				$http_code = 200;
				$mensaje = "Registro Insertado.";

				// files
				$path = "/var/www/html/files_bucket/contratos/solicitudes/proveedores/";
				if (!is_dir($path)) mkdir($path, 0777, true);

				if (isset($_FILES['archivo_ficha_ruc']) && $_FILES['archivo_ficha_ruc']['error'] === UPLOAD_ERR_OK) {
					$filename =    $_FILES['archivo_ficha_ruc']['name'];
					$filenametem = $_FILES['archivo_ficha_ruc']['tmp_name'];
					$filesize = $_FILES['archivo_ficha_ruc']['size'];
					$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
					if ($filename != "") {
						$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
						$nombre_archivo = $contrato_id . "_RUC_" . date('YmdHis') . "." . $fileExt;
						move_uploaded_file($filenametem, $path . $nombre_archivo);
						$comando = " INSERT INTO cont_archivos (
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
										1,
										'" . $nombre_archivo . "',
										'" . $fileExt . "',
										'" . $filesize . "',
										'" . $path . "',
										" . $usuario_id . ",
										'" . $created_at . "'
										)";
						$mysqli->query($comando);
						$imgruc_id = mysqli_insert_id($mysqli);
					}
				}

				if (isset($_FILES['archivo_formato_contrato']) && $_FILES['archivo_formato_contrato']['error'] === UPLOAD_ERR_OK) {
					$filename = $_FILES['archivo_formato_contrato']['name'];
					$filenametem = $_FILES['archivo_formato_contrato']['tmp_name'];
					$filesize = $_FILES['archivo_formato_contrato']['size'];
					$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
					if ($filename != "") {
						$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
						$nombre_archivo = $contrato_id . "_FORMATO_CONTRATO_" . date('YmdHis') . "." . $fileExt;
						move_uploaded_file($filenametem, $path . $nombre_archivo);
						$comando = " INSERT INTO cont_archivos (
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
										30,
										'" . $nombre_archivo . "',
										'" . $fileExt . "',
										'" . $filesize . "',
										'" . $path . "',
										" . $usuario_id . ",
										'" . $created_at . "'
										)";
						$mysqli->query($comando);
						$imgrvig_id = mysqli_insert_id($mysqli);
					}
				}

				if (isset($_FILES['archivo_know_your_client']) && $_FILES['archivo_know_your_client']['error'] === UPLOAD_ERR_OK) {


					$filename = $_FILES['archivo_know_your_client']['name'];
					$filenametem = $_FILES['archivo_know_your_client']['tmp_name'];
					$filesize = $_FILES['archivo_know_your_client']['size'];
					$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
					if ($filename != "") {
						$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
						$nombre_archivo = $contrato_id . "_FORMATO_CONTRATO_" . date('YmdHis') . "." . $fileExt;
						move_uploaded_file($filenametem, $path . $nombre_archivo);
						$comando = " INSERT INTO cont_archivos (
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
										118,
										'" . $nombre_archivo . "',
										'" . $fileExt . "',
										'" . $filesize . "',
										'" . $path . "',
										" . $usuario_id . ",
										'" . $created_at . "'
										)";
						$mysqli->query($comando);
						$imgrvig_id = mysqli_insert_id($mysqli);
					}
				}

				// INICIO DE CARGAR NUEVOS ANEXOS
				if (isset($_FILES["miarchivo"])) {
					if ($_FILES["miarchivo"]) {
						//Recorre el array de los archivos a subir
						$h = '';
						foreach ($_FILES["miarchivo"]['tmp_name'] as $key => $tmp_name) {
							//Si el archivo existe
							if ($_FILES["miarchivo"]["name"][$key]) {
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
									if ($value->nombre_archivo == $file_name && $value->tamano_archivo == $filesize && $value->extension == $fileExt) {
										$nombre_tipo_archivo = str_replace(' ', '_', $value->tip_doc_nombre);
										$nombre_tipo_archivo = strtoupper($nombre_tipo_archivo);
										$tipo_archivo = $value->id_tip_documento;
									}
								}

								$nombre_archivo = $contrato_id . "_" . $nombre_tipo_archivo . "_" . date('YmdHis') . "." . $fileExt;

								if (!file_exists($path)) {
									mkdir($path, 0777); //or die("Hubo un error al crear la carpeta");	
								}
								$dir = opendir($path);
								if (move_uploaded_file($fuente, $path . '/' . $nombre_archivo)) {
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
									if ($mysqli->error) {
										$mensaje .= 'Error al guardar el nuevo anexo' . $mysqli->error . $comando;
									}
								} else {
								}
								closedir($dir);
							}
						}
					}
				}
				// FIN DE CARGAR NUEVOS ANEXOS

				//REPRESENTANTES
				foreach ($data_representantes as $item) {
					// INICIO DE CARGAR ARCHIVOS DNI NUEVO REPRESENTANTE LEGAL
					$name_file_dni_like_repr = "dni_nuevo_representante_" . $item->id_registro;

					$filename = $_FILES[$name_file_dni_like_repr]['name'];
					$filenametem = $_FILES[$name_file_dni_like_repr]['tmp_name'];
					$filesize = $_FILES[$name_file_dni_like_repr]['size'];
					$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
					$img_id_insert_dni = 0;
					if ($filename != "") {
						$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
						$nombre_archivo = $contrato_id . "_DNI" . date('YmdHis') . "." . $fileExt;
						move_uploaded_file($filenametem, $path . $nombre_archivo);
						$comando = " INSERT INTO cont_archivos (
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
										3,
										'" . $nombre_archivo . "',
										'" . $fileExt . "',
										'" . $filesize . "',
										'" . $path . "',
										" . $usuario_id . ",
										'" . $created_at . "'
										)";
						$mysqli->query($comando);
						$img_id_insert_dni = mysqli_insert_id($mysqli);
					}
					// FIN DE CARGAR ARCHIVOS DNI NUEVO REPRESENTANTE LEGAL

					// INICIO DE CARGAR ARCHIVOS VIGENCIA NUEVO REPRESENTANTE LEGAL
					$name_file_vigencia_like_repr = "vigencia_nuevo_representante_" . $item->id_registro;

					$filename = $_FILES[$name_file_vigencia_like_repr]['name'];
					$filenametem = $_FILES[$name_file_vigencia_like_repr]['tmp_name'];
					$filesize = $_FILES[$name_file_vigencia_like_repr]['size'];
					$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
					$img_id_insert_vigencia = 0;
					if ($filename != "") {
						$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
						$nombre_archivo = $contrato_id . "_VIG" . date('YmdHis') . "." . $fileExt;
						move_uploaded_file($filenametem, $path . $nombre_archivo);
						$comando = " INSERT INTO cont_archivos (
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
										2,
										'" . $nombre_archivo . "',
										'" . $fileExt . "',
										'" . $filesize . "',
										'" . $path . "',
										" . $usuario_id . ",
										'" . $created_at . "'
										)";
						$mysqli->query($comando);
						$img_id_insert_vigencia = mysqli_insert_id($mysqli);
					}
					// FIN DE CARGAR ARCHIVOS VIGENCIA NUEVO REPRESENTANTE LEGAL

					$query_insert_repr =
						"INSERT INTO cont_representantes_legales (
						contrato_id, 
						tipo_documento_id,
						dni_representante, 
						nombre_representante, 
						nro_cuenta_detraccion, 
						id_banco, 
						nro_cuenta, 
						nro_cci, 
						vigencia_archivo_id, 
						dni_archivo_id, 
						id_user_created, 
						created_at
					) VALUES ("
						. $contrato_id . ", '"
						. $item->tipo_documento_id . "', '"
						. $item->dniRepresentante . "', '"
						. replace_invalid_caracters($item->nombreRepresentante) . "', '"
						. $item->nro_cuenta_detraccion . "', "
						. $item->banco . ", '"
						. $item->nro_cuenta . "', '"
						. $item->nro_cci . "', "
						. $img_id_insert_vigencia . ", "
						. $img_id_insert_dni . ", "
						. $usuario_id . ", now())";
					$mysqli->query($query_insert_repr);

					if ($mysqli->error) {
						$mensaje = $mysqli->error . '. En la siguiente query: ' . $query_insert_repr;
						$http_code = 500;
					} else {
						$http_code = 200;
						$mensaje = "Solicitud de Proveedor Enviada.";
					}
				}


				// INICIO CONTRAPRESTACIÓN
				$contraprestacion_ids = $_POST["contraprestacion_ids"];
				$data_contraprestaciones = json_decode($contraprestacion_ids);
				foreach ($data_contraprestaciones as $value_id_contraprestacion) {
					$query_update = "
					UPDATE cont_contraprestacion 
					SET 
						contrato_id = " . $contrato_id . ",
						user_updated_id = " . $usuario_id . ",
						updated_at = '" . $created_at . "'
					WHERE id = " . $value_id_contraprestacion . "
					";
					$mysqli->query($query_update);

					if ($mysqli->error) {
						$mensaje .= $mysqli->error . $query_update;
					}
				}
				// FIN CONTRAPRESTACIÓN



				$AREA_LEGAL_ID = 33;
				if ($aprobacion_obligatoria_id == 1) {
					$APROBACION_DEL_DOCUMENTO = 1;
					$CONTRATO = 1;
					$seg_proceso = new SeguimientoProceso();
					$data_proceso['tipo_documento_id'] = $CONTRATO; //Contratos
					$data_proceso['proceso_id'] = $contrato_id;
					$data_proceso['proceso_detalle_id'] = 0;
					$data_proceso['area_id'] = $area_id;
					$data_proceso['etapa_id'] = $APROBACION_DEL_DOCUMENTO; // Aprobación del Documento
					$data_proceso['status'] = 1;
					$data_proceso['created_at'] = date('Y-m-d H:i:s');
					$data_proceso['user_created_id'] = $usuario_id;
					$seg_proceso->registrar_proceso($data_proceso);
					send_email_confirmacion_solicitud_contrato_proveedor($contrato_id, false);
				} else {
					$INICIO_DE_PROCESO_LEGAL = 2;
					$AREA_LEGAL_ID = 33;
					$CONTRATO = 1;
					$seg_proceso = new SeguimientoProceso();
					$data_proceso['tipo_documento_id'] = $CONTRATO; //Contratos
					$data_proceso['proceso_id'] = $contrato_id;
					$data_proceso['proceso_detalle_id'] = 0;
					$data_proceso['area_id'] = $AREA_LEGAL_ID;
					$data_proceso['etapa_id'] = $INICIO_DE_PROCESO_LEGAL; // Inicio de Proceso Legal
					$data_proceso['status'] = 1;
					$data_proceso['created_at'] = date('Y-m-d H:i:s');
					$data_proceso['user_created_id'] = $usuario_id;
					$seg_proceso->registrar_proceso($data_proceso);
					send_email_solicitud_contrato_proveedor($contrato_id);
				}
			}
		}

		$result["http_code"] = $http_code;
		$result["status"] = $status;
		$result["mensaje"] = $mensaje;
		$result["result"] = $contrato_id;
	} else {
		$result["http_code"] = 400;
		$result["mensaje"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y volver a hacer clic en el botón verde: Solicitar Contrato con Proveedor.";
	}
}


if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_acuerdo_confidencialidad") {
	//echo "<pre>";print_r($_POST);echo "</pre>";die();
	$usuario_id = $login ? $login['id'] : null;
	$created_at = date("Y-m-d H:i:s");

	// $monto = str_replace(",","",$_POST["monto"]);
	$array_representantes = $_POST["rr_representantes"];
	$data_representantes = json_decode($array_representantes);

	$status = "success";

	$inputs_file = ["archivo_ficha_ruc", "archivo_formato_contrato"];
	$extensiones = ["pdf", "jpg", "jpeg", "png", "doc", "docx", "odt", "ppt", "pptx", "xls", "xlsx", "txt", "7z", "rar", "zip"];
	$maxsize = 52428800; // 50MB
	$error_msg = [];

	foreach ($inputs_file as $value) {
		if (isset($_FILES[$value]) && $_FILES[$value]['error'] === UPLOAD_ERR_OK) {
			$nombre_file = ucwords(str_replace("_", " ", $value));
			if ($_FILES[$value]['size'] > $maxsize) {
				$error_msg[] = 'Archivo debe ser menor a 50MB';
				$status = "error";
			} else if (!in_array(pathinfo(strtolower($_FILES[$value]['name']), PATHINFO_EXTENSION), $extensiones)) {
				$error_msg[] = $nombre_file . ' debe tener extensión .png, .jpg, .pdf.';
				$status = "error";
			}
		}
	}

	if (count($error_msg) > 0) {
		$result["http_code"] = 500;
		$result["status"] = $status;
		$result["mensaje"] = implode('<br> ', $error_msg);
		echo json_encode($result);
		die;
	}

	$query_update = "UPDATE cont_correlativo SET numero = numero + 1 WHERE tipo_contrato = 5 AND status = 1";

	$mysqli->query($query_update);

	if ($mysqli->error) {
		$error = $mysqli->error;
		$mensaje = $query_update;
		$http_code = 500;
	} else {
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
			WHERE tipo_contrato = 5 AND status = 1 LIMIT 1
		";

		$list_query = $mysqli->query($select_correlativo);

		while ($sel = $list_query->fetch_assoc()) {
			$sigla = $sel["sigla"];
			$numero_correlativo = $sel["numero"];
		}

		$query_insert = "INSERT INTO cont_contrato
		(
			  tipo_contrato_id
			, codigo_correlativo 
			, empresa_suscribe_id
			, area_responsable_id
			, persona_responsable_id
			, etapa_id
			, ruc
			, razon_social
			, check_gerencia_proveedor
			, persona_contacto_proveedor
			, detalle_servicio
			, periodo_numero
			, periodo
			, fecha_inicio 
			, observaciones
			, status
			, user_created_id
			, created_at
			, estado_solicitud
		)
		VALUES
		(
			" . $_POST["tipo_contrato_id"] . ",
			" . $numero_correlativo . ",
			" . $_POST["empresa_id"] . ",
			0,
			0,
			1,
			" . $_POST["ruc_ac"] . ",
			'" . $_POST["razon_social_ac"] . "',
			'" . $_POST["check_gerencia_proveedor_ac"] . "',
			'" . $_POST["persona_contacto_proveedor_ac"] . "',
			'" . $_POST["detalle_servicio_ac"] . "',
			1,
			1,
			'" . $_POST["fecha_inicio_ac"] . "',
			'" . $_POST["observaciones_legal_ac"] . "',
			1,
			" . $usuario_id . ",
			'" . $created_at . "'
			,1
		)";
		//echo "<pre>";print_r($query_insert);echo "</pre>";die();

		$mysqli->query($query_insert);
		$contrato_id = mysqli_insert_id($mysqli);
		$error = '';
		if ($mysqli->error) {
			$error = $mysqli->error;
			$mensaje = $query_insert;
			$http_code = 500;
		} else {
			$http_code = 200;
			$mensaje = "Registro Insertado.";

			// files
			$path = "/var/www/html/files_bucket/contratos/solicitudes/acuerdos/";
			if (!is_dir($path)) mkdir($path, 0777, true);

			if (isset($_FILES['archivo_ficha_ruc']) && $_FILES['archivo_ficha_ruc']['error'] === UPLOAD_ERR_OK) {
				$filename =    $_FILES['archivo_ficha_ruc']['name'];
				$filenametem = $_FILES['archivo_ficha_ruc']['tmp_name'];
				$filesize = $_FILES['archivo_ficha_ruc']['size'];
				$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
				if ($filename != "") {
					$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
					$nombre_archivo = $contrato_id . "_RUC_" . date('YmdHis') . "." . $fileExt;
					move_uploaded_file($filenametem, $path . $nombre_archivo);
					$comando = " INSERT INTO cont_archivos (
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
									1,
									'" . $nombre_archivo . "',
									'" . $fileExt . "',
									'" . $filesize . "',
									'" . $path . "',
									" . $usuario_id . ",
									'" . $created_at . "'
									)";
					$mysqli->query($comando);
					$imgruc_id = mysqli_insert_id($mysqli);
				}
			}

			if (isset($_FILES['archivo_formato_contrato']) && $_FILES['archivo_formato_contrato']['error'] === UPLOAD_ERR_OK) {
				$filename = $_FILES['archivo_formato_contrato']['name'];
				$filenametem = $_FILES['archivo_formato_contrato']['tmp_name'];
				$filesize = $_FILES['archivo_formato_contrato']['size'];
				$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
				if ($filename != "") {
					$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
					$nombre_archivo = $contrato_id . "_FORMATO_CONTRATO_" . date('YmdHis') . "." . $fileExt;
					move_uploaded_file($filenametem, $path . $nombre_archivo);
					$comando = " INSERT INTO cont_archivos (
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
									30,
									'" . $nombre_archivo . "',
									'" . $fileExt . "',
									'" . $filesize . "',
									'" . $path . "',
									" . $usuario_id . ",
									'" . $created_at . "'
									)";
					$mysqli->query($comando);
					$imgrvig_id = mysqli_insert_id($mysqli);
				}
			}

			// INICIO DE CARGAR NUEVOS ANEXOS
			if (isset($_FILES["miarchivo"])) {
				if ($_FILES["miarchivo"]) {
					//Recorre el array de los archivos a subir
					$h = '';
					foreach ($_FILES["miarchivo"]['tmp_name'] as $key => $tmp_name) {
						//Si el archivo existe
						if ($_FILES["miarchivo"]["name"][$key]) {
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
								if ($value->nombre_archivo == $file_name && $value->tamano_archivo == $filesize && $value->extension == $fileExt) {
									$nombre_tipo_archivo = str_replace(' ', '_', $value->tip_doc_nombre);
									$nombre_tipo_archivo = strtoupper($nombre_tipo_archivo);
									$tipo_archivo = $value->id_tip_documento;
								}
							}

							$nombre_archivo = $contrato_id . "_" . $nombre_tipo_archivo . "_" . date('YmdHis') . "." . $fileExt;

							if (!file_exists($path)) {
								mkdir($path, 0777); //or die("Hubo un error al crear la carpeta");	
							}
							$dir = opendir($path);
							if (move_uploaded_file($fuente, $path . '/' . $nombre_archivo)) {
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
								if ($mysqli->error) {
									$error .= 'Error al guardar el nuevo anexo' . $mysqli->error . $comando;
								}
							} else {
							}
							closedir($dir);
						}
					}
				}
			}
			// FIN DE CARGAR NUEVOS ANEXOS

			//REPRESENTANTES
			foreach ($data_representantes as $item) {
				// INICIO DE CARGAR ARCHIVOS DNI NUEVO REPRESENTANTE LEGAL
				$name_file_dni_like_repr = "dni_nuevo_representante_ac_" . $item->id_registro;

				$filename = $_FILES[$name_file_dni_like_repr]['name'];
				$filenametem = $_FILES[$name_file_dni_like_repr]['tmp_name'];
				$filesize = $_FILES[$name_file_dni_like_repr]['size'];
				$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
				$img_id_insert_dni = 0;
				if ($filename != "") {
					$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
					$nombre_archivo = $contrato_id . "_DNI" . date('YmdHis') . "." . $fileExt;
					move_uploaded_file($filenametem, $path . $nombre_archivo);
					$comando = " INSERT INTO cont_archivos (
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
									3,
									'" . $nombre_archivo . "',
									'" . $fileExt . "',
									'" . $filesize . "',
									'" . $path . "',
									" . $usuario_id . ",
									'" . $created_at . "'
									)";
					$mysqli->query($comando);
					$img_id_insert_dni = mysqli_insert_id($mysqli);
				}
				// FIN DE CARGAR ARCHIVOS DNI NUEVO REPRESENTANTE LEGAL

				// INICIO DE CARGAR ARCHIVOS VIGENCIA NUEVO REPRESENTANTE LEGAL
				$name_file_vigencia_like_repr = "vigencia_nuevo_representante_ac_" . $item->id_registro;

				$filename = $_FILES[$name_file_vigencia_like_repr]['name'];
				$filenametem = $_FILES[$name_file_vigencia_like_repr]['tmp_name'];
				$filesize = $_FILES[$name_file_vigencia_like_repr]['size'];
				$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
				$img_id_insert_vigencia = 0;
				if ($filename != "") {
					$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
					$nombre_archivo = $contrato_id . "_VIG" . date('YmdHis') . "." . $fileExt;
					move_uploaded_file($filenametem, $path . $nombre_archivo);
					$comando = " INSERT INTO cont_archivos (
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
									2,
									'" . $nombre_archivo . "',
									'" . $fileExt . "',
									'" . $filesize . "',
									'" . $path . "',
									" . $usuario_id . ",
									'" . $created_at . "'
									)";
					$mysqli->query($comando);
					$img_id_insert_vigencia = mysqli_insert_id($mysqli);
				}
				// FIN DE CARGAR ARCHIVOS VIGENCIA NUEVO REPRESENTANTE LEGAL

				$query_insert_repr =
					"INSERT INTO cont_representantes_legales (
					contrato_id, 
					dni_representante, 
					nombre_representante, 
					nro_cuenta_detraccion, 
					id_banco, 
					nro_cuenta, 
					nro_cci, 
					vigencia_archivo_id, 
					dni_archivo_id, 
					id_user_created, 
					created_at
				) VALUES ("
					. $contrato_id . ", '"
					. $item->dniRepresentante . "', '"
					. $item->nombreRepresentante . "', '"
					. $item->nro_cuenta_detraccion . "', "
					. $item->banco . ", '"
					. $item->nro_cuenta . "', '"
					. $item->nro_cci . "', "
					. $img_id_insert_vigencia . ", "
					. $img_id_insert_dni . ", "
					. $usuario_id . ", now())";


				$mysqli->query($query_insert_repr);
				$error = '';
				if ($mysqli->error) {
					$error = $mysqli->error;
					$mensaje = $query_insert_repr;
					$http_code = 500;
				} else {
					$http_code = 200;
					$mensaje = "Solicitud de Acuerdo de Confidencialidad Enviada.";
				}
			}


			// INICIO CONTRAPRESTACIÓN
			$contraprestacion_ids = $_POST["contraprestacion_ids"];
			$data_contraprestaciones = json_decode($contraprestacion_ids);
			foreach ($data_contraprestaciones as $value_id_contraprestacion) {
				$query_update = "
				UPDATE cont_contraprestacion 
				SET 
					contrato_id = " . $contrato_id . ",
					user_updated_id = " . $usuario_id . ",
					updated_at = '" . $created_at . "'
				WHERE id = " . $value_id_contraprestacion . "
				";
				$mysqli->query($query_update);

				if ($mysqli->error) {
					$error .= $mysqli->error . $query_update;
				}
			}
			// FIN CONTRAPRESTACIÓN


			if ($_POST["check_gerencia_proveedor_ac"] == 1) {
				send_email_confirmacion_acuerdo_confidencialidad($contrato_id, false);
			} else {
				send_email_solicitud_acuerdo_confidencialidad($contrato_id);
			}
		}
	}

	$result["http_code"] = $http_code;
	$result["status"] = $status;
	$result["mensaje"] = $mensaje;
	$result["result"] = $contrato_id;
	$result["error"] = $error;
}


if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_monedas") {
	$query = "SELECT * FROM tbl_moneda WHERE id IN (1,2) AND estado = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_forma_pago") {
	$query = "SELECT * FROM cont_forma_pago WHERE estado = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_comprobante") {
	$query = "SELECT * FROM cont_tipo_comprobante WHERE estado = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_terminacion_anticipada") {
	$query = "SELECT * FROM cont_tipo_terminacion_anticipada WHERE status = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_adelantos") {
	$query = "SELECT * FROM cont_tipo_adelanto WHERE status = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_gerentes") {
	$query = "
	SELECT 
		u.id, 
		CONCAT(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, ''),' ', IFNULL(p.apellido_materno, '')) AS nombre 
	FROM 
		tbl_usuarios u
		INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		INNER JOIN cont_usuarios_gerentes g ON u.id = g.user_id
	WHERE 
		u.estado = 1
		AND g.status = 1
	ORDER BY 
		p.nombre ASC,
		p.apellido_paterno ASC
	";
	$list_query = $mysqli->query($query);
	$list = [];

	$list[] = array(
		'id' => 'A',
		'nombre' => '- Otro -'
	);

	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_directores") {
	$query = "
	SELECT 
		u.id, 
		CONCAT(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, ''),' ', IFNULL(p.apellido_materno, '')) AS nombre 
	FROM 
		tbl_usuarios u
		INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		INNER JOIN cont_usuarios_directores ud ON u.id = ud.user_id
	WHERE 
		u.estado = 1
		AND ud.status = 1
	ORDER BY 
		p.nombre ASC,
		p.apellido_paterno ASC
	";
	$list_query = $mysqli->query($query);
	$list = [];

	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_plazo") {
	$query = "SELECT * FROM cont_tipo_plazo WHERE status = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_periodo") {
	$query = "SELECT * FROM cont_periodo WHERE estado = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_contrato_v2") {
	$area_id = $login ? $login['area_id'] : 0;
	$user_id = $login ? $login['id'] : null;

	$menu_id = "";
	$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'contrato' AND sub_sec_id = 'solicitud' LIMIT 1")->fetch_assoc();
	$menu_id = $result["id"];


	// menu contratos
	$query = "SELECT * FROM cont_tipo_contrato WHERE status = 1";
	$query .= " AND id IN('2','5'";
	if ((array_key_exists($menu_id, $usuario_permisos) && in_array("nuevo_contrato_arrendamiento", $usuario_permisos[$menu_id]))) { // Operaciones, Legal. Sistemas
		$query .= ",'1','12'";
	}
	if ((array_key_exists($menu_id, $usuario_permisos) && in_array("nuevo_contrato_locacion", $usuario_permisos[$menu_id]))) { // Contratos Internos
		$query .= ",'13'";
	}
	if ((array_key_exists($menu_id, $usuario_permisos) && in_array("nuevo_contrato_mandato", $usuario_permisos[$menu_id]))) { // Contratos Internos
		$query .= ",'14'";
	}
	if ((array_key_exists($menu_id, $usuario_permisos) && in_array("nuevo_contrato_mutuodinero", $usuario_permisos[$menu_id]))) { // Agentes
		$query .= ",'15'";
	}
	// 	nuevo_contrato_locacion
	// nuevo_contrato_mandato
	// nuevo_contrato_mutuodinero
	$query .= " )";
	$list_query = $mysqli->query($query);
	$menu = '<li class="dropdown-header solicitud">Contratos</li>';
	while ($li = $list_query->fetch_assoc()) {
		$sub_sec = "";
		switch ($li['id']) {
			case '1':
				$sub_sec = './?sec_id=contrato&amp;sub_sec_id=nuevo_arrendamiento';
				break;
			case '2':
				$sub_sec = './?sec_id=contrato&amp;sub_sec_id=nuevo&option=' . $li["id"];
				break;
			case '5':
				$sub_sec = './?sec_id=contrato&amp;sub_sec_id=nuevo_acuerdo_confidencialidad';
				break;
			case '6':
				$sub_sec = './?sec_id=contrato&amp;sub_sec_id=nuevo_agente';
				break;
			case '7':
				$sub_sec = './?sec_id=contrato&amp;sub_sec_id=nuevo_interno';
				break;
			case '12':
				$sub_sec = './?sec_id=contrato&amp;sub_sec_id=nuevo_arrendamiento_v2';
				break;
			case '13':
				$sub_sec = './?sec_id=contrato&amp;sub_sec_id=nuevo_locacion_servicio';
				break;
			case '14':
				$sub_sec = './?sec_id=contrato&amp;sub_sec_id=nuevo_mandato';
				break;
			case '15':
				$sub_sec = './?sec_id=contrato&amp;sub_sec_id=nuevo_mutuo_dinero';
				break;
		}
		$menu .= '<li><a href="' . $sub_sec . '">' . $li['nombre'] . '</a></li>';
	}

	// menu adendas
	$query = "SELECT * FROM cont_tipo_contrato WHERE status = 1";
	$query .= " AND id IN('4','9'";
	if ((array_key_exists($menu_id, $usuario_permisos) && in_array("nuevo_contrato_arrendamiento", $usuario_permisos[$menu_id]))) { // Operaciones, Legal. Sistemas
		$query .= ",'3'";
	}
	if ((array_key_exists($menu_id, $usuario_permisos) && in_array("nuevo_contrato_locacion", $usuario_permisos[$menu_id]))) { // Operaciones, Legal. Sistemas
		$query .= ",'16'";
	}
	if ((array_key_exists($menu_id, $usuario_permisos) && in_array("nuevo_contrato_mandato", $usuario_permisos[$menu_id]))) {
		$query .= ",'17'";
	}
	if ((array_key_exists($menu_id, $usuario_permisos) && in_array("nuevo_contrato_mutuodinero", $usuario_permisos[$menu_id]))) {
		$query .= ",'18'";
	}

	$query .= " )";
	$list_query = $mysqli->query($query);
	$menu .= '<li class="dropdown-header solicitud">Adendas</li>';
	while ($li = $list_query->fetch_assoc()) {
		$sub_sec = "";

		switch ($li['id']) {
			case '3':
				$sub_sec = './?sec_id=contrato&amp;sub_sec_id=nuevo_adenda_arrendamiento';
				break;
			case '4':
				$sub_sec = './?sec_id=contrato&amp;sub_sec_id=nuevo_adenda_proveedor';
				break;
			case '8':
				$sub_sec = './?sec_id=contrato&amp;sub_sec_id=nuevo_adenda_interno';
				break;
			case '9':
				$sub_sec = './?sec_id=contrato&amp;sub_sec_id=nuevo_adenda_acuerdo_confidencialidad';
				break;
			case '10':
				$sub_sec = './?sec_id=contrato&amp;sub_sec_id=nuevo_adenda_agente';
				break;
			case '16':
				$sub_sec = './?sec_id=contrato&amp;sub_sec_id=nuevo_adenda_locacion_servicio';
				break;
			case '17':
				$sub_sec = './?sec_id=contrato&amp;sub_sec_id=nuevo_adenda_mandato';
				break;
			case '18':
				$sub_sec = './?sec_id=contrato&amp;sub_sec_id=nuevo_adenda_mutuodinero';
				break;
		}
		$menu .= '<li><a href="' . $sub_sec . '">' . $li['nombre'] . '</a></li>';
	}

	// $menu .= '<li class="dropdown-header">Resolución de Contrato</li>';
	// menu contratos
	$query = "SELECT * FROM cont_tipo_contrato WHERE status = 1";
	$query .= " AND id IN('11')";
	$list_query = $mysqli->query($query);
	while ($li = $list_query->fetch_assoc()) {
		$sub_sec = "";
		switch ($li['id']) {
			case '11':
				$sub_sec = './?sec_id=contrato&amp;sub_sec_id=nuevo_resolucion_contrato';
				break;
		}
		$menu .= '<li><a href="' . $sub_sec . '">' . $li['nombre'] . '</a></li>';
	}
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $menu;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_contrato") {
	$area_id = $login ? $login['area_id'] : 0;

	$query = "SELECT * FROM cont_tipo_contrato WHERE status = 1";
	// if ($area_id != 6) { // Desarrollo
	if ($area_id != 21) { // Producción
		$query .= " AND id IN('2','4')";
	} else {
		$query .= " AND id NOT IN('5','6','7','8','9','10','11')";
	}

	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_empresa_at") {
	$query = "SELECT id, nombre
	FROM tbl_razon_social
	WHERE status = 1
	ORDER BY nombre ASC";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_personal_responsable") {
	$query = "SELECT u.id, concat( IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, ''),' ', IFNULL(p.apellido_materno, '')) AS nombre
	FROM tbl_personal_apt p
	INNER JOIN tbl_usuarios u ON p.id = u.personal_id
	WHERE p.area_id = 21 AND p.cargo_id = 4 AND p.estado = 1 AND u.estado = 1
	ORDER BY nombre ASC";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_jefe_comercial") {
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
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_impuesto_a_la_renta") {
	$query = "
	SELECT 
		id, nombre
	FROM
		cont_tipo_impuesto_a_la_renta
	WHERE
		status = 1
	ORDER BY id ASC
	";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_carta_de_instruccion") {
	$query = "
	SELECT 
		id, nombre
	FROM
		cont_tipo_carta_de_instruccion
	WHERE
		status = 1
	ORDER BY id ASC
	";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_pago_servicio") {
	$query = "SELECT id, nombre
	FROM cont_tipo_pago_servicio
	ORDER BY id ASC";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_pago_arbitrios") {
	$query = "SELECT id, nombre
	FROM cont_tipo_pago_arbitrios
	ORDER BY id ASC";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_monto_a_depositar") {
	$query = "SELECT id, nombre
	FROM cont_tipo_monto_a_depositar
	WHERE status = 1
	ORDER BY id ASC";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_adelanto") {
	$query = "SELECT id, nombre
	FROM cont_tipo_adelanto
	WHERE status = 1
	ORDER BY id ASC";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_mes_adelanto") {
	$query = "SELECT id, nombre
	FROM cont_tipo_mes_adelanto
	WHERE status = 1
	ORDER BY orden ASC";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_dia_de_pago") {
	$query = "
	SELECT 
		id, 
		nombre
	FROM 
		cont_tipo_dia_de_pago
	WHERE 
		status = 1
	ORDER BY id ASC
	";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
}

// INICIO MODULO DE DETALLE CONTRATO

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_contrato_firmado") {
	$usuario_id = $login ? $login['id'] : null;
	$created_at = date("Y-m-d H:i:s");

	$query_update = "
	UPDATE cont_contrato 
	SET 
		nombre_tienda = '" . str_replace("'", "", trim($_POST["nombre_tienda"])) . "'
		, etapa_id = '5',
		user_updated_id = " . $usuario_id . ",
		updated_at = '" . $created_at . "'
	WHERE contrato_id = '" . $_POST["contrato_id"] . "'
	";
	$mysqli->query($query_update);

	if ($mysqli->error) {
		$result["update_error"] = $mysqli->error;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
}

function send_email_solicitud_contrato_arrendamiento($contrato_id)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$sel_query = $mysqli->query("SELECT c.nombre_tienda, 
									 ce.fecha_suscripcion,
									 ce.created_at,
									 i.ubicacion, 
									 ce.fecha_inicio, 
									 ce.fecha_fin,
									 c.user_created_id,
									 concat( IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS usuario_solicitante,
									 tp.correo,
									 co.sigla AS sigla_correlativo,
									c.codigo_correlativo
								FROM cont_contrato c
									INNER JOIN cont_inmueble i
									ON c.contrato_id = i.contrato_id
									INNER JOIN cont_condicion_economica ce
									ON c.contrato_id = ce.contrato_id AND ce.status = 1
									INNER JOIN tbl_usuarios tu
									ON tu.id = c.user_created_id
									INNER JOIN tbl_personal_apt tp
									ON tp.id = tu.personal_id
									LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
								WHERE c.contrato_id = '" . $contrato_id . "' LIMIT 1
				");

	$body = "";
	$body .= '<html>';

	$email_user_created = '';

	while ($sel = $sel_query->fetch_assoc()) {
		$sigla_correlativo = $sel['sigla_correlativo'];
		$codigo_correlativo = $sel['codigo_correlativo'];

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
		$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Ubicación:</b></td>';
		$body .= '<td>' . $sel["ubicacion"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Fecha solicitud:</b></td>';
		$body .= '<td>' . $sel["created_at"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Usuario solicitante:</b></td>';
		$body .= '<td>' . $sel["usuario_solicitante"] . '</td>';
		$body .= '</tr>';

		$body .= '</table>';
		$body .= '</div>';

		$email_user_created = $sel["correo"];
	}

	$body .= '<div>';
	$body .= '<br>';
	$body .= '</div>';

	$body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
	$body .= '<a href="' . $host . '/?sec_id=contrato&sub_sec_id=detalle_solicitud&id=' . $contrato_id . '" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
	$body .= '<b>Ver Solicitud</b>';
	$body .= '</a>';
	$body .= '</div>';

	$body .= '</html>';
	$body .= "";

	$correos = new Correos(env('SEND_EMAIL'), env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_solicitud_contrato_arrendamiento([$email_user_created]);

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];

	$request = [
		"subject" => "Gestion - Sistema Contratos - Nueva Solicitud de Arrendamiento: Código - " . $sigla_correlativo . $codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try {
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if (isset($request["cc"])) {
			foreach ($request["cc"] as $cc) {
				$mail->addAddress($cc);
			}
		}

		if (isset($request["bcc"])) {
			foreach ($request["bcc"] as $bcc) {
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();
	} catch (Exception $e) {
		$result["http_code"] = 400;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["error_title"] = "La solicitud se registro correctamente, pero no se pudo enviar el email.";
		$result["error"] = $mail->ErrorInfo;
		echo json_encode($result);
		exit();
	}
}



if (isset($_POST["accion"]) && $_POST["accion"] === "send_email_solicitud_contrato_proveedores") {
	$contrato_id = $_POST["contrato_id"];

	send_email_solicitud_contrato_proveedor($contrato_id);
}

if (isset($_POST["accion"]) && $_POST["accion"] === "send_email_solicitud_contrato_aprobador") {
	$contrato_id = $_POST["contrato_id"];
	$tipo_contrato_id = $_POST["tipo_contrato_id"];

	send_email_solicitud_contrato_aprobador($contrato_id, $tipo_contrato_id);
}

if (isset($_POST["accion"]) && $_POST["accion"] === "send_email_solicitud_contrato_masivo") {
	$contrato_id = $_POST["contrato_id"];
	$tipo_contrato_id = $_POST["tipo_contrato_id"];
	send_email_solicitud_contrato_aprobador($contrato_id, $tipo_contrato_id);
	if ($tipo_contrato_id == 1) {
		send_email_solicitud_contrato_arrendamiento($contrato_id);
		send_email_solicitud_contrato_arrendamiento_detallado($contrato_id, true, false);
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "send_email_solicitud_contrato_acuerdo_confidencialidad") {
	$contrato_id = $_POST["contrato_id"];

	send_email_solicitud_acuerdo_confidencialidad($contrato_id);
}


function send_email_solicitud_contrato_proveedor($contrato_id)
{

	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$nombre_tienda = "";
	$empresa_suscribe_id = 0;
	$sel_query = $mysqli->query("
	SELECT
		c.empresa_suscribe_id,
		rs.nombre AS empresa_suscribe,
		c.ruc AS proveedor_ruc,
		c.razon_social AS proveedor_razon_social,
		c.nombre_comercial AS proveedor_nombre_comercial,
		c.detalle_servicio,
		c.plazo_id,
		tp.nombre AS plazo,
		CONCAT(c.periodo_numero, ' ', p.nombre) AS periodo,
		concat(IFNULL(per.nombre, ''),' ', IFNULL(per.apellido_paterno, '')) AS usuario_creacion,
		per.correo AS usuario_creacion_correo,
		c.fecha_inicio,
		ar.nombre AS area_creacion,
		c.check_gerencia_proveedor,
		c.fecha_atencion_gerencia_proveedor,
		c.aprobacion_gerencia_proveedor,
		co.sigla AS sigla_correlativo,
		c.codigo_correlativo,
		c.gerente_area_id,
		c.gerente_area_nombre,
		c.gerente_area_email,
		CONCAT(IFNULL(peg.nombre, ''),' ',IFNULL(peg.apellido_paterno, ''),' ',IFNULL(peg.apellido_materno, '')) AS nombre_del_gerente_area,
		peg.correo AS email_del_gerente_area,
		CONCAT(IFNULL(puap.nombre, ''),' ',IFNULL(puap.apellido_paterno, ''),' ',IFNULL(puap.apellido_materno, '')) AS aprobado_por,
		puap.correo AS email_del_aprobante,
		CONCAT(IFNULL(pab.nombre, ''),' ',IFNULL(pab.apellido_paterno, ''),' ',IFNULL(pab.apellido_materno, '')) AS abogado,
		pab.correo AS correo_abogado


		
	FROM 
		cont_contrato c
		LEFT JOIN cont_tipo_plazo tp ON c.plazo_id = tp.id
		LEFT JOIN cont_periodo p ON c.periodo = p.id
		INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
		INNER JOIN tbl_personal_apt per ON u.personal_id = per.id
		INNER JOIN tbl_areas ar ON per.area_id = ar.id
		INNER JOIN tbl_razon_social rs ON c.empresa_suscribe_id = rs.id
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato

		LEFT JOIN tbl_usuarios ug ON c.gerente_area_id = ug.id
		LEFT JOIN tbl_personal_apt peg ON ug.personal_id = peg.id
		
		LEFT JOIN tbl_usuarios uap ON c.aprobado_por = uap.id
		LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id

		LEFT JOIN tbl_usuarios uab ON c.abogado_id = uab.id
		LEFT JOIN tbl_personal_apt pab ON uab.personal_id = pab.id
	WHERE 
		c.contrato_id = $contrato_id
	");

	$body = "";
	$body .= '<html>';

	$usuario_creacion_correo = '';
	$correos_adicionales = [];

	while ($sel = $sel_query->fetch_assoc()) {
		$empresa_suscribe_id = $sel['empresa_suscribe_id'];
		$empresa_suscribe = $sel['empresa_suscribe'];

		$sigla_correlativo = $sel['sigla_correlativo'];
		$codigo_correlativo = $sel['codigo_correlativo'];

		$plazo_id = $sel["plazo_id"];
		$plazo = $sel["plazo"];

		$fecha_atencion_gerencia_proveedor = $sel['fecha_atencion_gerencia_proveedor'];
		$aprobacion_gerencia_proveedor = $sel['aprobacion_gerencia_proveedor'];
		$aprobado_por = $sel['aprobado_por'];
		$abogado = $sel['abogado'];
		if (!empty($sel['correo_abogado'])) {
			array_push($correos_adicionales, $sel['correo_abogado']); //Correo abogado
		}

		$proveedor_ruc = $sel['proveedor_ruc'];
		$proveedor_razon_social_titulo = $sel['proveedor_razon_social'];
		$proveedor_nombre_comercial = $sel['proveedor_nombre_comercial'];
		$usuario_creacion_correo = $sel['usuario_creacion_correo'];

		$date = date_create($sel["fecha_inicio"]);
		$fecha_inicio_contrato = date_format($date, "Y/m/d");

		$gerente_area_id = trim($sel["gerente_area_id"]);

		if (empty($gerente_area_id)) {
			$gerente_area_nombre = trim($sel["gerente_area_nombre"]);
			$gerente_area_email = trim($sel["gerente_area_email"]);
			array_push($correos_adicionales, $gerente_area_email); //Correo Responsable de Area
		} else {
			$gerente_area_nombre = trim($sel["nombre_del_gerente_area"]);
			$gerente_area_email = trim($sel["email_del_gerente_area"]);
			array_push($correos_adicionales, $gerente_area_email); //Correo Responsable de Area
		}

		array_push($correos_adicionales, trim($sel['usuario_creacion_correo'])); //Correo Persona de Contacto

		if (!empty($sel['email_del_aprobante'])) {
			array_push($correos_adicionales, trim($sel['email_del_aprobante'])); //Correo del Aprobante
		}

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

		$body .= '<thead>';

		if (!is_null($fecha_atencion_gerencia_proveedor) && $aprobacion_gerencia_proveedor == 0) {
			$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
			$body .= '<b>Solicitud Rechazada</b>';
			$body .= '</th>';
			$body .= '</tr>';
		} else {
			$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
			$body .= '<b>Nueva solicitud</b>';
			$body .= '</th>';
			$body .= '</tr>';
		}

		$body .= '</thead>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Empresa Contratante:</b></td>';
		$body .= '<td>' . $sel["empresa_suscribe"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>RUC Proveedor:</b></td>';
		$body .= '<td>' . $sel["proveedor_ruc"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Razon social Proveedor:</b></td>';
		$body .= '<td>' . $sel["proveedor_razon_social"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Nombre Comercial Proveedor:</b></td>';
		$body .= '<td>' . $sel["proveedor_nombre_comercial"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Plazo</b></td>';
		$body .= '<td>' . $plazo . '</td>';
		$body .= '</tr>';

		if ($plazo_id == 1) {

			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Periodo:</b></td>';
			$body .= '<td>' . $sel["periodo"] . '</td>';
			$body .= '</tr>';
		}

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Usuario solicitante:</b></td>';
		$body .= '<td>' . $sel["usuario_creacion"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Responsable de Área:</b></td>';
		$body .= '<td>' . $gerente_area_nombre . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Fecha inicio:</b></td>';
		$body .= '<td>' . $fecha_inicio_contrato . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Detalle servicio:</b></td>';
		$body .= '<td>' . $sel["detalle_servicio"] . '</td>';
		$body .= '</tr>';

		if (!is_null($fecha_atencion_gerencia_proveedor) && $aprobacion_gerencia_proveedor == 0) {
			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Rechazado por:</b></td>';
			$body .= '<td>' . $sel["aprobado_por"] . '</td>';
			$body .= '</tr>';
		} else if (!is_null($fecha_atencion_gerencia_proveedor) && $aprobacion_gerencia_proveedor == 1) {
			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Aprobado por:</b></td>';
			$body .= '<td>' . $sel["aprobado_por"] . '</td>';
			$body .= '</tr>';
		}

		$body .= '</table>';
		$body .= '</div>';
	}
	$body .= '<div>';
	$body .= '<br>';
	$body .= '</div>';

	$body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
	$body .= '<a href="' . $host . '/?sec_id=contrato&sub_sec_id=detalleSolicitudProveedor&id=' . $contrato_id . '" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
	$body .= '<b>Ver Solicitud</b>';
	$body .= '</a>';
	$body .= '</div>';

	$body .= '</html>';
	$body .= "";

	$titulo_email = "";

	if (!is_null($fecha_atencion_gerencia_proveedor) && $aprobacion_gerencia_proveedor == 0) {
		$correos_adicionales = [
			"$usuario_creacion_correo"
		];

		$titulo_email = "Gestion - Sistema Contratos - Aprobación del Documento - Solicitud de Proveedor Rechazada: CÓD - ";

		$correos = new Correos(env('SEND_EMAIL'), env('EMAIL_TEST'));
		$lista_correos = $correos->send_email_solicitud_rechazada($correos_adicionales);
		$cc = $lista_correos['cc'];
		$bcc = $lista_correos['bcc'];
	} else {

		if ($empresa_suscribe_id != 0) {
			$sql_contador_tesorero = "
			SELECT 
				p.correo
			FROM
				cont_usuarios_razones_sociales urs
				INNER JOIN tbl_usuarios u ON urs.user_id = u.id
				INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
			WHERE
				urs.razon_social_id = " . $empresa_suscribe_id . "
				AND p.estado = 1  AND u.estado = 1 
			";
			$sel_query = $mysqli->query($sql_contador_tesorero);

			$row_count = $sel_query->num_rows;

			if ($row_count > 0) {
				while ($sel = $sel_query->fetch_assoc()) {
					array_push($correos_adicionales, $sel['correo']);
				}
			}
		}

		if (env('SEND_EMAIL') == 'produccion') {
			if (!(empty($gerente_area_email))) {
				if (sec_contrato_nuevo_is_valid_email($gerente_area_email)) {
					array_push($correos_adicionales, $gerente_area_email);
				}
			}
		}

		if (isset($_POST['correos_adjuntos']) && $_POST['correos_adjuntos'] != "") {
			$validate = true;
			$emails = preg_split('[,|;]', $_POST['correos_adjuntos']);
			foreach ($emails as $e) {
				if (preg_match('/^[_ña-z0-9-]+(\.[_ña-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/', trim($e)) == 0) {
					$error_msg = "'" . $e . "'" . " no es un Correo Adjunto válido";
					if ($e == "") {
						$error_msg = "Formato de Correo Incorrecto";
					}
					$result["http_code"] = 500;
					$result["status"] = "error";
					$result["mensaje"] = $error_msg;
					echo json_encode($result);
					die;
				} else {
					$correos_adicionales[] = $e;
				}
			}
		}

		$correos = new Correos(env('SEND_EMAIL'), env('EMAIL_TEST'));
		$lista_correos = $correos->send_email_solicitud_contrato_proveedor($correos_adicionales);
		$cc = $lista_correos['cc'];
		$bcc = $lista_correos['bcc'];

		$titulo_email = "Gestion - Sistema Contratos - Inicio de Proceso Legal - Nueva Solicitud de Proveedor: Código - ";
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
		$body .= '<td>' . $correos_produccion . '</td>';
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
		"subject" => $titulo_email . $sigla_correlativo . $codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try {
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if (isset($request["cc"])) {
			foreach ($request["cc"] as $cc) {
				$mail->addAddress($cc);
			}
		}

		if (isset($request["bcc"])) {
			foreach ($request["bcc"] as $bcc) {
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

	} catch (Exception $e) {
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}
}

function send_email_solicitud_contrato_aprobador($contrato_id, $tipo_contrato_id)
{

	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	if ($tipo_contrato_id == 1) {
		$nombre_tipo_contrato = "Solicitud de Contrato de Arrendamiento";
	} elseif ($tipo_contrato_id == 6) {
		$nombre_tipo_contrato = "Solicitud de Contrato de Agente";
	}

	$nombre_tienda = "";
	$empresa_suscribe_id = 0;
	$sel_query = $mysqli->query("
	SELECT
		c.empresa_suscribe_id,
		rs.nombre AS empresa_suscribe,
		c.ruc AS proveedor_ruc,
		c.razon_social AS proveedor_razon_social,
		c.nombre_comercial AS proveedor_nombre_comercial,
		c.detalle_servicio,
		c.plazo_id,
		tp.nombre AS plazo,
		CONCAT(c.periodo_numero, ' ', p.nombre) AS periodo,
		concat(IFNULL(per.nombre, ''),' ', IFNULL(per.apellido_paterno, '')) AS usuario_creacion,
		per.correo AS usuario_creacion_correo,
		c.fecha_inicio,
		ar.nombre AS area_creacion,
		c.fecha_aprobacion,
		c.estado_aprobacion,
		co.sigla AS sigla_correlativo,
		c.codigo_correlativo,
		c.gerente_area_id,
		c.gerente_area_nombre,
		c.gerente_area_email,
		CONCAT(IFNULL(peg.nombre, ''),' ',IFNULL(peg.apellido_paterno, ''),' ',IFNULL(peg.apellido_materno, '')) AS nombre_del_gerente_area,
		peg.correo AS email_del_gerente_area,
		CONCAT(IFNULL(puap.nombre, ''),' ',IFNULL(puap.apellido_paterno, ''),' ',IFNULL(puap.apellido_materno, '')) AS aprobado_por,
		puap.correo AS email_del_aprobante,
		CONCAT(IFNULL(pab.nombre, ''),' ',IFNULL(pab.apellido_paterno, ''),' ',IFNULL(pab.apellido_materno, '')) AS abogado,
		pab.correo AS correo_abogado
	FROM 
		cont_contrato c
		LEFT JOIN cont_tipo_plazo tp ON c.plazo_id = tp.id
		LEFT JOIN cont_periodo p ON c.periodo = p.id
		INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
		INNER JOIN tbl_personal_apt per ON u.personal_id = per.id
		INNER JOIN tbl_areas ar ON per.area_id = ar.id
		INNER JOIN tbl_razon_social rs ON c.empresa_suscribe_id = rs.id
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato

		LEFT JOIN tbl_usuarios ug ON c.gerente_area_id = ug.id
		LEFT JOIN tbl_personal_apt peg ON ug.personal_id = peg.id
		
		LEFT JOIN tbl_usuarios uap ON c.aprobado_por = uap.id
		LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id

		LEFT JOIN tbl_usuarios uab ON c.abogado_id = uab.id
		LEFT JOIN tbl_personal_apt pab ON uab.personal_id = pab.id
	WHERE 
		c.contrato_id = $contrato_id
	");

	$body = "";
	$body .= '<html>';

	$usuario_creacion_correo = '';
	$correos_adicionales = [];

	while ($sel = $sel_query->fetch_assoc()) {
		$empresa_suscribe_id = $sel['empresa_suscribe_id'];
		$empresa_suscribe = $sel['empresa_suscribe'];

		$sigla_correlativo = $sel['sigla_correlativo'];
		$codigo_correlativo = $sel['codigo_correlativo'];

		$plazo_id = $sel["plazo_id"];
		$plazo = $sel["plazo"];

		$fecha_aprobacion = $sel['fecha_aprobacion'];
		$estado_aprobacion = $sel['estado_aprobacion'];
		$aprobado_por = $sel['aprobado_por'];
		$abogado = $sel['abogado'];
		if (!empty($sel['correo_abogado'])) {
			array_push($correos_adicionales, $sel['correo_abogado']); //Correo abogado
		}

		$proveedor_ruc = $sel['proveedor_ruc'];
		$proveedor_razon_social_titulo = $sel['proveedor_razon_social'];
		$proveedor_nombre_comercial = $sel['proveedor_nombre_comercial'];
		$usuario_creacion_correo = $sel['usuario_creacion_correo'];

		$date = date_create($sel["fecha_inicio"]);
		$fecha_inicio_contrato = date_format($date, "Y/m/d");

		$gerente_area_id = trim($sel["gerente_area_id"]);

		if (empty($gerente_area_id)) {
			$gerente_area_nombre = trim($sel["gerente_area_nombre"]);
			$gerente_area_email = trim($sel["gerente_area_email"]);
			array_push($correos_adicionales, $gerente_area_email); //Correo Responsable de Area
		} else {
			$gerente_area_nombre = trim($sel["nombre_del_gerente_area"]);
			$gerente_area_email = trim($sel["email_del_gerente_area"]);
			array_push($correos_adicionales, $gerente_area_email); //Correo Responsable de Area
		}

		array_push($correos_adicionales, trim($sel['usuario_creacion_correo'])); //Correo Persona de Contacto

		if (!empty($sel['email_del_aprobante'])) {
			array_push($correos_adicionales, trim($sel['email_del_aprobante'])); //Correo del Aprobante
		}

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

		$body .= '<thead>';

		if (!is_null($fecha_aprobacion) && $estado_aprobacion == 0) {
			$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
			$body .= '<b>Solicitud Rechazada</b>';
			$body .= '</th>';
			$body .= '</tr>';
		} else {
			$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
			$body .= '<b>Nueva solicitud</b>';
			$body .= '</th>';
			$body .= '</tr>';
		}

		$body .= '</thead>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Empresa Contratante:</b></td>';
		$body .= '<td>' . $sel["empresa_suscribe"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>RUC Proveedor:</b></td>';
		$body .= '<td>' . $sel["proveedor_ruc"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Razon social Proveedor:</b></td>';
		$body .= '<td>' . $sel["proveedor_razon_social"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Nombre Comercial Proveedor:</b></td>';
		$body .= '<td>' . $sel["proveedor_nombre_comercial"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Plazo</b></td>';
		$body .= '<td>' . $plazo . '</td>';
		$body .= '</tr>';

		if ($plazo_id == 1) {

			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Periodo:</b></td>';
			$body .= '<td>' . $sel["periodo"] . '</td>';
			$body .= '</tr>';
		}

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Usuario solicitante:</b></td>';
		$body .= '<td>' . $sel["usuario_creacion"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Responsable de Área:</b></td>';
		$body .= '<td>' . $gerente_area_nombre . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Fecha inicio:</b></td>';
		$body .= '<td>' . $fecha_inicio_contrato . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Detalle servicio:</b></td>';
		$body .= '<td>' . $sel["detalle_servicio"] . '</td>';
		$body .= '</tr>';

		if (!is_null($fecha_aprobacion) && $estado_aprobacion == 0) {
			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Rechazado por:</b></td>';
			$body .= '<td>' . $sel["aprobado_por"] . '</td>';
			$body .= '</tr>';
		} else if (!is_null($fecha_aprobacion) && $estado_aprobacion == 1) {
			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Aprobado por:</b></td>';
			$body .= '<td>' . $sel["aprobado_por"] . '</td>';
			$body .= '</tr>';
		}

		$body .= '</table>';
		$body .= '</div>';
	}
	$body .= '<div>';
	$body .= '<br>';
	$body .= '</div>';

	if ($tipo_contrato_id == 1) {

		$body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
		$body .= '<a href="' . $host . '/?sec_id=contrato&sub_sec_id=detalle_solicitud&id=' . $contrato_id . '" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
		$body .= '<b>Ver Solicitud</b>';
		$body .= '</a>';
		$body .= '</div>';
	} else if ($tipo_contrato_id == 6) {

		$body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
		$body .= '<a href="' . $host . '/?sec_id=contrato&sub_sec_id=detalle_agente&id=' . $contrato_id . '" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
		$body .= '<b>Ver Solicitud</b>';
		$body .= '</a>';
		$body .= '</div>';
	}



	$body .= '</html>';
	$body .= "";

	$titulo_email = "";

	if (!is_null($fecha_aprobacion) && $estado_aprobacion == 0) {
		$cc = [
			"$usuario_creacion_correo"
		];

		$titulo_email = "Gestion - Sistema Contratos - $nombre_tipo_contrato Rechazada: CÓD - ";
	} else {

		$correos = new Correos(env('SEND_EMAIL'), env('EMAIL_TEST'));
		$lista_correos = $correos->send_email_solicitud_contrato_proveedor($correos_adicionales);

		$cc = $lista_correos['cc'];

		if ($empresa_suscribe_id != 0) {
			$sql_contador_tesorero = "
			SELECT 
				p.correo
			FROM
				cont_usuarios_razones_sociales urs
				INNER JOIN tbl_usuarios u ON urs.user_id = u.id
				INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
			WHERE
				urs.razon_social_id = " . $empresa_suscribe_id . "
				AND p.estado = 1  AND u.estado = 1 
			";
			$sel_query = $mysqli->query($sql_contador_tesorero);

			$row_count = $sel_query->num_rows;

			if ($row_count > 0) {
				while ($sel = $sel_query->fetch_assoc()) {
					array_push($cc, $sel['correo']);
				}
			}
		}

		if (env('SEND_EMAIL') == 'produccion') {
			if (!(empty($gerente_area_email))) {
				if (sec_contrato_nuevo_is_valid_email($gerente_area_email)) {
					array_push($cc, $gerente_area_email);
				}
			}
		}

		$titulo_email = "Gestion - Sistema Contratos - Nueva $nombre_tipo_contrato: Código - ";
	}

	if (isset($_POST['correos_adjuntos']) && $_POST['correos_adjuntos'] != "") {
		$validate = true;
		$emails = preg_split('[,|;]', $_POST['correos_adjuntos']);
		foreach ($emails as $e) {
			if (preg_match('/^[_ña-z0-9-]+(\.[_ña-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/', trim($e)) == 0) {
				$error_msg = "'" . $e . "'" . " no es un Correo Adjunto válido";
				if ($e == "") {
					$error_msg = "Formato de Correo Incorrecto";
				}
				$result["http_code"] = 500;
				$result["status"] = "error";
				$result["mensaje"] = $error_msg;
				echo json_encode($result);
				die;
			} else {
				$cc[] = $e;
			}
		}
	}

	$bcc = $lista_correos['bcc'];

	$request = [
		"subject" => $titulo_email . $sigla_correlativo . $codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try {
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if (isset($request["cc"])) {
			foreach ($request["cc"] as $cc) {
				$mail->addAddress($cc);
			}
		}

		if (isset($request["bcc"])) {
			foreach ($request["bcc"] as $bcc) {
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

	} catch (Exception $e) {
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}
}

function send_email_solicitud_acuerdo_confidencialidad($contrato_id)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$nombre_tienda = "";
	$empresa_suscribe_id = 0;
	$sel_query = $mysqli->query("
	SELECT
		c.empresa_suscribe_id,
		rs.nombre AS empresa_suscribe,
		c.ruc AS proveedor_ruc,
		c.razon_social AS proveedor_razon_social,
		c.detalle_servicio,
		CONCAT(c.periodo_numero, ' ', p.nombre) AS periodo,
		concat(IFNULL(per.nombre, ''),' ', IFNULL(per.apellido_paterno, '')) AS usuario_creacion,
		per.correo AS usuario_creacion_correo,
		c.fecha_inicio,
		ar.nombre AS area_creacion,
		c.check_gerencia_proveedor,
		c.fecha_atencion_gerencia_proveedor,
		c.aprobacion_gerencia_proveedor,
		co.sigla AS sigla_correlativo,
		c.codigo_correlativo,
		CONCAT(IFNULL(puap.nombre, ''),' ',IFNULL(puap.apellido_paterno, ''),' ',IFNULL(puap.apellido_materno, '')) AS aprobado_por,
		CONCAT(IFNULL(pab.nombre, ''),' ',IFNULL(pab.apellido_paterno, ''),' ',IFNULL(pab.apellido_materno, '')) AS abogado
	FROM 
		cont_contrato c
		INNER JOIN cont_periodo p ON c.periodo = p.id
		INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
		INNER JOIN tbl_personal_apt per ON u.personal_id = per.id
		INNER JOIN tbl_areas ar ON per.area_id = ar.id
		INNER JOIN tbl_razon_social rs ON c.empresa_suscribe_id = rs.id
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
		LEFT JOIN tbl_usuarios uap ON c.aprobado_por = uap.id
		LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id

		LEFT JOIN tbl_usuarios uab ON c.abogado_id = uab.id
		LEFT JOIN tbl_personal_apt pab ON uab.personal_id = pab.id
	WHERE 
		c.contrato_id = $contrato_id
	");

	$body = "";
	$body .= '<html>';

	$usuario_creacion_correo = '';

	while ($sel = $sel_query->fetch_assoc()) {
		$empresa_suscribe_id = $sel['empresa_suscribe_id'];
		$empresa_suscribe = $sel['empresa_suscribe'];

		$sigla_correlativo = $sel['sigla_correlativo'];
		$codigo_correlativo = $sel['codigo_correlativo'];

		$fecha_atencion_gerencia_proveedor = $sel['fecha_atencion_gerencia_proveedor'];
		$aprobacion_gerencia_proveedor = $sel['aprobacion_gerencia_proveedor'];

		$abogado = $sel['abogado'];

		$proveedor_ruc = $sel['proveedor_ruc'];
		$proveedor_razon_social_titulo = $sel['proveedor_razon_social'];
		$usuario_creacion_correo = $sel['usuario_creacion_correo'];

		$date = date_create($sel["fecha_inicio"]);
		$fecha_inicio_contrato = date_format($date, "Y/m/d");

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

		$body .= '<thead>';

		if (!is_null($fecha_atencion_gerencia_proveedor) && $aprobacion_gerencia_proveedor == 0) {
			$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
			$body .= '<b>Solicitud Rechazada</b>';
			$body .= '</th>';
			$body .= '</tr>';
		} else {
			$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
			$body .= '<b>Nueva solicitud</b>';
			$body .= '</th>';
			$body .= '</tr>';
		}

		$body .= '</thead>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Empresa Contratante:</b></td>';
		$body .= '<td>' . $sel["empresa_suscribe"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>RUC Proveedor:</b></td>';
		$body .= '<td>' . $sel["proveedor_ruc"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Razon social Proveedor:</b></td>';
		$body .= '<td>' . $sel["proveedor_razon_social"] . '</td>';
		$body .= '</tr>';



		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Usuario solicitante:</b></td>';
		$body .= '<td>' . $sel["usuario_creacion"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Fecha inicio:</b></td>';
		$body .= '<td>' . $fecha_inicio_contrato . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Detalle servicio:</b></td>';
		$body .= '<td>' . $sel["detalle_servicio"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Abogado:</b></td>';
		$body .= '<td>' . $sel["abogado"] . '</td>';
		$body .= '</tr>';

		if (!is_null($fecha_atencion_gerencia_proveedor) && $aprobacion_gerencia_proveedor == 0) {
			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Rechazado por:</b></td>';
			$body .= '<td>' . $sel["aprobado_por"] . '</td>';
			$body .= '</tr>';
		} else if (!is_null($fecha_atencion_gerencia_proveedor) && $aprobacion_gerencia_proveedor == 1) {
			$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Aprobado por:</b></td>';
			$body .= '<td>' . $sel["aprobado_por"] . '</td>';
			$body .= '</tr>';
		}

		$body .= '</table>';
		$body .= '</div>';
	}
	$body .= '<div>';
	$body .= '<br>';
	$body .= '</div>';

	$body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
	$body .= '<a href="' . $host . '/?sec_id=contrato&sub_sec_id=detalle_solicitud_acuerdo_confidencialidad&id=' . $contrato_id . '" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
	$body .= '<b>Ver Solicitud</b>';
	$body .= '</a>';
	$body .= '</div>';

	$body .= '</html>';
	$body .= "";

	$titulo_email = "";

	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'), env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_solicitud_acuerdo_confidencialidad([$usuario_creacion_correo]);

	if (!is_null($fecha_atencion_gerencia_proveedor) && $aprobacion_gerencia_proveedor == 0) {
		$cc = [
			"$usuario_creacion_correo"
		];

		$titulo_email = "Gestion - Sistema Contratos - Solicitud de Acuerdo de Confidencialidad Rechazada: CÓD - ";
	} else {
		$cc = $lista_correos['cc'];

		$titulo_email = "Gestion - Sistema Contratos - Nueva Solicitud de Acuerdo de Confidencialidad: Código - ";
	}

	$bcc = $lista_correos['bcc'];

	$request = [
		"subject" => $titulo_email . $sigla_correlativo . $codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try {
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if (isset($request["cc"])) {
			foreach ($request["cc"] as $cc) {
				$mail->addAddress($cc);
			}
		}

		if (isset($request["bcc"])) {
			foreach ($request["bcc"] as $bcc) {
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

	} catch (Exception $e) {
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}
}


function send_email_solicitud_adenda_contrato_arrendamiento($adenda_id)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$sel_query = $mysqli->query("
	SELECT a.id, a.created_at, co.sigla, c.codigo_correlativo, c.contrato_id, tc.nombre,
	concat( IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS usuario_solicitante,
	r.nombre AS empresa_suscribe, ar.nombre AS nombre_area, tp.correo
	FROM cont_adendas AS a 
	INNER JOIN cont_contrato AS c ON c.contrato_id = a.contrato_id
	INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id
	INNER JOIN cont_tipo_contrato AS tc ON tc.id = c.tipo_contrato_id
	INNER JOIN tbl_usuarios AS tu	ON tu.id = a.user_created_id
	INNER JOIN tbl_personal_apt AS tp ON tp.id = tu.personal_id
	INNER JOIN tbl_areas AS ar ON tp.area_id = ar.id
	LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
	WHERE a.id = $adenda_id
	");


	$body = "";
	$body .= '<html>';

	$usuario_creacion_correo = '';

	while ($sel = $sel_query->fetch_assoc()) {
		$contrato_id = $sel['sigla'];
		$sigla_correlativo = $sel['sigla'];
		$codigo_correlativo = $sel['codigo_correlativo'];
		$empresa_suscribe = $sel['empresa_suscribe'];
		$fecha_solicitud = $sel['created_at'];
		$usuario_solicitante = $sel['usuario_solicitante'];
		if (!empty($sel['correo'])) {
			$usuario_creacion_correo = $sel['correo'];
		}


		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

		$body .= '<thead>';

		$body .= '<tr>';
		$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
		$body .= '<b>Nueva solicitud</b>';
		$body .= '</th>';
		$body .= '</tr>';


		$body .= '</thead>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Area Solicitante:</b></td>';
		$body .= '<td>' . $sel["nombre_area"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Usuario solicitante:</b></td>';
		$body .= '<td>' . $sel["usuario_solicitante"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Fecha Solicitud:</b></td>';
		$body .= '<td>' . $sel["created_at"] . '</td>';
		$body .= '</tr>';

		$body .= '</table>';
		$body .= '</div>';


		$query = $mysqli->query("
		SELECT id,
			adenda_id,
			nombre_tabla,
			valor_original,
			nombre_menu_usuario,
			nombre_campo_usuario,
			nombre_campo,
			tipo_valor,
			valor_varchar,
			valor_int,
			valor_date,
			valor_decimal,
			valor_select_option,
			status
		FROM cont_adendas_detalle
		WHERE adenda_id = " . $adenda_id . "
		AND tipo_valor != 'id_tabla' AND tipo_valor != 'registro'
		AND status = 1");
		$row_count = $query->num_rows;
		$numero_adenda_detalle = 0;
		if ($row_count > 0) {
			$body .= '<div>';
			$body .= '<br>';
			$body .= '</div>';

			$body .= '<div>';
			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

			$body .= '<thead>';

			$body .= '<tr>';
			$body .= '<th colspan="5" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
			$body .= '<b>Detalle</b>';
			$body .= '</th>';
			$body .= '</tr>';


			$body .= '</thead>';

			$body .= '<tr>';
			$body .= '<td align="center" style="background-color: #ffffdd; width: 20px;"><b>#</b></td>';
			$body .= '<td align="center" style="background-color: #ffffdd;"><b>Menu:</b></td>';
			$body .= '<td align="center" style="background-color: #ffffdd;"><b>Campo:</b></td>';
			$body .= '<td align="center" style="background-color: #ffffdd;"><b>Valor Original:</b></td>';
			$body .= '<td align="center" style="background-color: #ffffdd;"><b>Nuevo Valor:</b></td>';
			$body .= '</tr>';

			while ($row = $query->fetch_assoc()) {
				$nombre_menu_usuario = $row["nombre_menu_usuario"];
				$nombre_campo_usuario = $row["nombre_campo_usuario"];
				$valor_original = $row["valor_original"];
				$tipo_valor = $row["tipo_valor"];
				if ($tipo_valor == 'varchar') {
					$nuevo_valor = $row['valor_varchar'];
				} else if ($tipo_valor == 'int') {
					$nuevo_valor = $row['valor_int'];
				} else if ($tipo_valor == 'date') {
					$nuevo_valor = $row['valor_date'];
				} else if ($tipo_valor == 'decimal') {
					$nuevo_valor = $row['valor_decimal'];
				} else if ($tipo_valor == 'select_option') {
					$nuevo_valor = $row['valor_select_option'];
				}
				$numero_adenda_detalle++;

				$body .= '<tr>';
				$body .= '<td>' . $numero_adenda_detalle . '</td>';
				$body .= '<td>' . $nombre_menu_usuario . '</td>';
				$body .= '<td>' . $nombre_campo_usuario . '</td>';
				$body .= '<td>' . $valor_original . '</td>';
				$body .= '<td>' . $nuevo_valor . '</td>';
				$body .= '</tr>';
			}
			$body .= '</table>';
			$body .= '</div>';
		}

		$query_otros = "SELECT id, nombre_menu_usuario, valor_original, valor_int, valor_id_tabla
		FROM cont_adendas_detalle 
		WHERE tipo_valor = 'id_tabla'
			AND adenda_id = " . $adenda_id . "
			AND status = 1";

		$list_query_otros = $mysqli->query($query_otros);
		$row_count_otros = $list_query_otros->num_rows;

		if ($row_count_otros > 0) {
			$body .= '<div>';
			$body .= '<br>';
			$body .= '</div>';
			while ($row = $list_query_otros->fetch_assoc()) {
				if ($row["nombre_menu_usuario"] == 'Propietario') {
					$query = "
					SELECT 
						p.id,
						tp.nombre AS tipo_persona,
						td.nombre AS tipo_docu_identidad,
						p.tipo_persona_id,
						p.tipo_docu_identidad_id,
						p.num_docu,
						p.nombre,
						p.direccion,
						p.representante_legal,
						p.num_partida_registral,
						p.contacto_nombre,
						p.contacto_telefono,
						p.contacto_email
					FROM
						cont_persona p
						INNER JOIN cont_tipo_persona tp ON p.tipo_persona_id = tp.id
						INNER JOIN cont_tipo_docu_identidad td ON p.tipo_docu_identidad_id = td.id
					WHERE
						p.id IN ('" . $row["valor_original"] . "' , '" . $row["valor_int"] . "')
					";

					$valores_originales = [];
					$valores_nuevos = [];
					$list_query = $mysqli->query($query);
					while ($li = $list_query->fetch_assoc()) {
						if ($li["id"] == $row["valor_original"]) {
							$valores_originales[] = $li;
						} else if ($li["id"] == $row["valor_int"]) {
							$valores_nuevos[] = $li;
						}
					}


					$body .= '<div>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

					$body .= '<thead>';

					$body .= '<tr>';
					$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
					$body .= '<b>Propietario</b>';
					$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td align="center" style="background-color: #ffffdd;"><b>Campo:</b></td>';
					$body .= '<td align="center" style="background-color: #ffffdd;"><b>Valor Original:</b></td>';
					$body .= '<td align="center" style="background-color: #ffffdd;"><b>Nuevo Valor:</b></td>';
					$body .= '</tr>';

					$body .= '</thead>';

					$body .= '<tr>';
					$body .= '<td >Tipo de documento de persona</td>';
					$body .= '<td >' . $valores_originales[0]["tipo_persona"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["tipo_persona"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Nombre</td>';
					$body .= '<td >' . $valores_originales[0]["nombre"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["nombre"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Tipo de documento de identidad</td>';
					$body .= '<td >' . $valores_originales[0]["num_docu"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["num_docu"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Direccion</td>';
					$body .= '<td >' . $valores_originales[0]["direccion"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["direccion"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Representante legal</td>';
					$body .= '<td >' . $valores_originales[0]["representante_legal"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["representante_legal"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >N° de Partida Registral de la empresa</td>';
					$body .= '<td >' . $valores_originales[0]["num_partida_registral"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["num_partida_registral"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Contacto - Nombre</td>';
					$body .= '<td >' . $valores_originales[0]["contacto_nombre"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["contacto_nombre"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Contacto - Teléfono</td>';
					$body .= '<td >' . $valores_originales[0]["contacto_telefono"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["contacto_telefono"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Contacto - Email</td>';
					$body .= '<td >' . $valores_originales[0]["contacto_email"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["contacto_email"] . '</td>';
					$body .= '</tr>';

					$body .= '</table>';
					$body .= '</div>';
				}

				if ($row["nombre_menu_usuario"] == 'Beneficiario') {
					$query = "
						SELECT 
							b.id,
							tp.nombre AS tipo_persona,
							td.nombre AS tipo_docu_identidad,
							b.num_docu,
							b.nombre,
							f.nombre AS forma_pago,
							ba.nombre AS banco,
							b.num_cuenta_bancaria,
							b.num_cuenta_cci,
							b.tipo_monto_id,
							tm.nombre AS tipo_monto,
							b.monto
						FROM
							cont_beneficiarios b
							LEFT JOIN cont_tipo_persona tp ON b.tipo_persona_id = tp.id
							LEFT JOIN cont_tipo_docu_identidad td ON b.tipo_docu_identidad_id = td.id
							INNER JOIN cont_forma_pago f ON b.forma_pago_id = f.id
							LEFT JOIN tbl_bancos ba ON b.banco_id = ba.id
							INNER JOIN cont_tipo_monto_a_depositar tm ON b.tipo_monto_id = tm.id
						WHERE
							b.id IN ('" . $row["valor_original"] . "' , '" . $row["valor_int"] . "')
						";

					$valores_originales = [];
					$valores_nuevos = [];
					$list_query = $mysqli->query($query);
					while ($li = $list_query->fetch_assoc()) {
						if ($li["id"] == $row["valor_original"]) {
							$valores_originales[] = $li;
						} else if ($li["id"] == $row["valor_int"]) {
							$valores_nuevos[] = $li;
						}
					}

					$body .= '<div>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

					$body .= '<thead>';

					$body .= '<tr>';
					$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
					$body .= '<b>Beneficiario</b>';
					$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td align="center" style="background-color: #ffffdd;><b>Campo:</b></td>';
					$body .= '<td align="center" style="background-color: #ffffdd;><b>Valor Original:</b></td>';
					$body .= '<td align="center" style="background-color: #ffffdd;><b>Nuevo Valor:</b></td>';
					$body .= '</tr>';

					$body .= '</thead>';

					$body .= '<tr>';
					$body .= '<td >Tipo de documento de persona</td>';
					$body .= '<td >' . $valores_originales[0]["tipo_persona"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["tipo_persona"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Nombre</td>';
					$body .= '<td >' . $valores_originales[0]["nombre"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["nombre"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Tipo de documento de identidad</td>';
					$body .= '<td >' . $valores_originales[0]["tipo_docu_identidad"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["tipo_docu_identidad"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Número de documento de identidad</td>';
					$body .= '<td >' . $valores_originales[0]["num_docu"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["num_docu"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Tipo de forma de pago</td>';
					$body .= '<td >' . $valores_originales[0]["forma_pago"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["forma_pago"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Nombre del Banco</td>';
					$body .= '<td >' . $valores_originales[0]["banco"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["banco"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >N° de la cuenta bancaria</td>';
					$body .= '<td >' . $valores_originales[0]["num_cuenta_bancaria"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["num_cuenta_bancaria"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >N° de CCI bancario</td>';
					$body .= '<td >' . $valores_originales[0]["num_cuenta_cci"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["num_cuenta_cci"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Tipo de monto a depositar</td>';
					$body .= '<td >' . $valores_originales[0]["tipo_monto"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["tipo_monto"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Monto</td>';
					$body .= '<td >' . $valores_originales[0]["monto"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["monto"] . '</td>';
					$body .= '</tr>';

					$body .= '</table>';
					$body .= '</div>';
				}


				if ($row["nombre_menu_usuario"] == 'Nuevo Incremento') {
					$query = "
						SELECT 
							i.id, 
							i.valor, 
							i.tipo_valor_id,
							tp.nombre AS tipo_valor, 
							i.tipo_continuidad_id, 
							tc.nombre AS tipo_continuidad, 
							i.a_partir_del_año
						FROM 
							cont_incrementos i
							INNER JOIN cont_tipo_pago_incrementos tp ON i.tipo_valor_id = tp.id
							INNER JOIN cont_tipo_continuidad_pago tc ON i.tipo_continuidad_id = tc.id
						WHERE 
							i.id = " . $row["valor_int"];

					$list_query = $mysqli->query($query);
					while ($dataquery = $list_query->fetch_assoc()) {
						$valor_nuevo = $dataquery;
					}

					$body .= '<div>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

					$body .= '<thead>';

					$body .= '<tr>';
					$body .= '<th colspan="3" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
					$body .= '<b>Nuevo Incremento</b>';
					$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td align="center" style="background-color: #ffffdd;"><b>Campo:</b></td>';
					$body .= '<td align="center" style="background-color: #ffffdd;"><b>Valor Original:</b></td>';
					$body .= '<td align="center" style="background-color: #ffffdd;"><b>Nuevo Valor:</b></td>';
					$body .= '</tr>';

					$body .= '</thead>';

					$body .= '<tr>';
					$body .= '<td >Valor</td>';
					$body .= '<td ></td>';
					$body .= '<td >' . $valor_nuevo["valor"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Tipo Valor</td>';
					$body .= '<td ></td>';
					$body .= '<td >' . $valor_nuevo["tipo_valor"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Continuidad</td>';
					$body .= '<td ></td>';
					$body .= '<td >' . $valor_nuevo["tipo_continuidad"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Apartir del</td>';
					$body .= '<td ></td>';
					$body .= '<td >' . $valor_nuevo["a_partir_del_año"] . '</td>';
					$body .= '</tr>';

					$body .= '</table>';
					$body .= '</div>';
				}
			}
		}
	}
	$body .= '<div>';
	$body .= '<br>';
	$body .= '</div>';

	$body .= '<div style="width: 700px; text-align: center; font-family: arial;">';
	$body .= '<a href="' . $host . '/?sec_id=contrato&sub_sec_id=detalle_solicitud&id=' . $contrato_id . '" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
	$body .= '<b>Ver Solicitud</b>';
	$body .= '</a>';
	$body .= '</div>';

	$body .= '</html>';
	$body .= "";

	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'), env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_solicitud_adenda_contrato_arrendamiento([$usuario_creacion_correo]);

	$cc = $lista_correos['cc'];

	$titulo_email = "Gestion - Sistema Contratos - Nueva Solicitud de Adenda de Arrendamiento: Código - ";

	$bcc = $lista_correos['bcc'];

	$request = [
		"subject" => $titulo_email . $sigla_correlativo . $codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try {
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if (isset($request["cc"])) {
			foreach ($request["cc"] as $cc) {
				$mail->addAddress($cc);
			}
		}

		if (isset($request["bcc"])) {
			foreach ($request["bcc"] as $bcc) {
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

	} catch (Exception $e) {
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}
}

function send_email_solicitud_adenda_contrato_proveedor($adenda_id)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$sel_query = $mysqli->query("
	SELECT 
		a.id, 
		a.created_at, 
		co.sigla, 
		c.codigo_correlativo, 
		c.contrato_id, 
		tc.nombre,
		concat( IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS usuario_solicitante,
		r.nombre AS empresa_suscribe, ar.nombre AS nombre_area, tp.correo
	FROM 
		cont_adendas AS a 
		INNER JOIN cont_contrato AS c ON c.contrato_id = a.contrato_id
		INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id
		INNER JOIN cont_tipo_contrato AS tc ON tc.id = c.tipo_contrato_id
		INNER JOIN tbl_usuarios AS tu	ON tu.id = a.user_created_id
		INNER JOIN tbl_personal_apt AS tp ON tp.id = tu.personal_id
		INNER JOIN tbl_areas AS ar ON tp.area_id = ar.id
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
	WHERE a.id = $adenda_id
	");


	$body = "";
	$body .= '<html>';

	$usuario_creacion_correo = '';
	$contrato_id = 0;

	while ($sel = $sel_query->fetch_assoc()) {
		$contrato_id = $sel['contrato_id'];
		$sigla_correlativo = $sel['sigla'];
		$codigo_correlativo = $sel['codigo_correlativo'];
		$empresa_suscribe = $sel['empresa_suscribe'];
		$fecha_solicitud = $sel['created_at'];
		$usuario_solicitante = $sel['usuario_solicitante'];
		if (!empty($sel['correo'])) {
			$usuario_creacion_correo = $sel['correo'];
		}


		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

		$body .= '<thead>';

		$body .= '<tr>';
		$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
		$body .= '<b>Nueva solicitud</b>';
		$body .= '</th>';
		$body .= '</tr>';


		$body .= '</thead>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Area Solicitante:</b></td>';
		$body .= '<td>' . $sel["nombre_area"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Usuario solicitante:</b></td>';
		$body .= '<td>' . $sel["usuario_solicitante"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Fecha Solicitud:</b></td>';
		$body .= '<td>' . $sel["created_at"] . '</td>';
		$body .= '</tr>';

		$body .= '</table>';
		$body .= '</div>';


		$query = $mysqli->query("
		SELECT id,
			adenda_id,
			nombre_tabla,
			valor_original,
			nombre_menu_usuario,
			nombre_campo_usuario,
			nombre_campo,
			tipo_valor,
			valor_varchar,
			valor_int,
			valor_date,
			valor_decimal,
			valor_select_option,
			status
		FROM cont_adendas_detalle
		WHERE adenda_id = " . $adenda_id . "
		AND tipo_valor != 'id_tabla' AND tipo_valor != 'registro'
		AND status = 1");
		$row_count = $query->num_rows;
		$numero_adenda_detalle = 0;
		if ($row_count > 0) {
			$body .= '<div>';
			$body .= '<br>';
			$body .= '</div>';

			$body .= '<div>';
			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

			$body .= '<thead>';

			$body .= '<tr>';
			$body .= '<th colspan="5" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
			$body .= '<b>Detalle</b>';
			$body .= '</th>';
			$body .= '</tr>';


			$body .= '</thead>';

			$body .= '<tr>';
			$body .= '<td align="center" style="background-color: #ffffdd; width: 20px;"><b>#</b></td>';
			$body .= '<td align="center" style="background-color: #ffffdd;"><b>Menu:</b></td>';
			$body .= '<td align="center" style="background-color: #ffffdd;"><b>Campo:</b></td>';
			$body .= '<td align="center" style="background-color: #ffffdd;"><b>Valor Original:</b></td>';
			$body .= '<td align="center" style="background-color: #ffffdd;"><b>Nuevo Valor:</b></td>';
			$body .= '</tr>';

			while ($row = $query->fetch_assoc()) {
				$nombre_menu_usuario = $row["nombre_menu_usuario"];
				$nombre_campo_usuario = $row["nombre_campo_usuario"];
				$valor_original = $row["valor_original"];
				$tipo_valor = $row["tipo_valor"];
				if ($tipo_valor == 'varchar') {
					$nuevo_valor = $row['valor_varchar'];
				} else if ($tipo_valor == 'int') {
					$nuevo_valor = $row['valor_int'];
				} else if ($tipo_valor == 'date') {
					$nuevo_valor = $row['valor_date'];
				} else if ($tipo_valor == 'decimal') {
					$nuevo_valor = $row['valor_decimal'];
				} else if ($tipo_valor == 'select_option') {
					$nuevo_valor = $row['valor_select_option'];
				}
				$numero_adenda_detalle++;

				$body .= '<tr>';
				$body .= '<td>' . $numero_adenda_detalle . '</td>';
				$body .= '<td>' . $nombre_menu_usuario . '</td>';
				$body .= '<td>' . $nombre_campo_usuario . '</td>';
				$body .= '<td>' . $valor_original . '</td>';
				$body .= '<td>' . $nuevo_valor . '</td>';
				$body .= '</tr>';
			}
			$body .= '</table>';
			$body .= '</div>';
		}

		$query = $mysqli->query("
		SELECT id,
			adenda_id,
			nombre_tabla,
			valor_original,
			nombre_menu_usuario,
			nombre_campo_usuario,
			nombre_campo,
			tipo_valor,
			valor_varchar,
			valor_int,
			valor_date,
			valor_decimal,
			valor_select_option,
			status
		FROM cont_adendas_detalle
		WHERE adenda_id = " . $adenda_id . "
		AND tipo_valor = 'registro'
		AND status = 1");
		$row_count = $query->num_rows;
		$numero_adenda_detalle = 0;
		if ($row_count > 0) {
			while ($row = $query->fetch_assoc()) {
				if ($row["nombre_menu_usuario"] == 'Representante Legal') {
					$query_pro = "
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
						rl.id IN ('" . $row["valor_int"] . "')
					";

					$valores_originales = [];
					$valores_nuevos = [];
					$list_query = $mysqli->query($query_pro);
					while ($li = $list_query->fetch_assoc()) {
						if ($li["id"] == $row["valor_int"]) {
							$valores_nuevos[] = $li;
						}
					}
					$body .= '<div>';
					$body .= '<br>';
					$body .= '</div>';

					$body .= '<div>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

					$body .= '<thead>';

					$body .= '<tr>';
					$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
					$body .= '<b>Nuevo Representante Legal</b>';
					$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td align="center" style="background-color: #ffffdd;"><b>Campo:</b></td>';
					$body .= '<td align="center" style="background-color: #ffffdd;"><b>Nuevo Valor:</b></td>';
					$body .= '</tr>';

					$body .= '</thead>';

					$body .= '<tr>';
					$body .= '<td >DNI del representante legal</td>';
					$body .= '<td >' . $valores_nuevos[0]["dni_representante"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Nombre completo del representante legal</td>';
					$body .= '<td >' . $valores_nuevos[0]["nombre_representante"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Nro cuenta de detraccion (Banco de la nación)</td>';
					$body .= '<td >' . $valores_nuevos[0]["nro_cuenta_detraccion"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Banco</td>';
					$body .= '<td >' . $valores_nuevos[0]["banco_representante"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Nro Cuenta</td>';
					$body .= '<td >' . $valores_nuevos[0]["nro_cuenta"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Nro CCI</td>';
					$body .= '<td >' . $valores_nuevos[0]["nro_cci"] . '</td>';
					$body .= '</tr>';



					$body .= '</table>';
					$body .= '</div>';
				}

				if ($row["nombre_menu_usuario"] == 'Contraprestación') {
					$query_cont = "
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
					c.id IN ('" . $row["valor_int"] . "')
				";

					$valores_originales = [];
					$valores_nuevos = [];
					$list_query = $mysqli->query($query_cont);
					while ($li = $list_query->fetch_assoc()) {
						if ($li["id"] == $row["valor_int"]) {
							$valores_nuevos[] = $li;
						}
					}
					$body .= '<div>';
					$body .= '<br>';
					$body .= '</div>';

					$body .= '<div>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

					$body .= '<thead>';

					$body .= '<tr>';
					$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
					$body .= '<b>Nueva Contraprestación</b>';
					$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td align="center" style="background-color: #ffffdd;"><b>Campo:</b></td>';
					$body .= '<td align="center" style="background-color: #ffffdd;"><b>Nuevo Valor:</b></td>';
					$body .= '</tr>';

					$body .= '</thead>';

					$body .= '<tr>';
					$body .= '<td>Tipo de moneda</td>';
					$body .= '<td>' . $valores_nuevos[0]["tipo_moneda"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Subtotal</td>';
					$body .= '<td>' . $valores_nuevos[0]["subtotal"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>IGV</td>';
					$body .= '<td>' . $valores_nuevos[0]["igv"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Monto Bruto</td>';
					$body .= '<td>' . $valores_nuevos[0]["monto"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Tipo de comprobante a emitir</td>';
					$body .= '<td>' . $valores_nuevos[0]["tipo_comprobante"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Plazo de Pago</td>';
					$body .= '<td>' . $valores_nuevos[0]["plazo_pago"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Forma de pago</td>';
					$body .= '<td>' . $valores_nuevos[0]["forma_pago_detallado"] . '</td>';
					$body .= '</tr>';


					$body .= '</table>';
					$body .= '</div>';
				}
			}
		}
	}
	$body .= '<div>';
	$body .= '<br>';
	$body .= '</div>';

	$body .= '<div style="width: 700px; text-align: center; font-family: arial;">';
	$body .= '<a href="' . $host . '/?sec_id=contrato&sub_sec_id=detalleSolicitudProveedor&id=' . $contrato_id . '" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
	$body .= '<b>Ver Solicitud</b>';
	$body .= '</a>';
	$body .= '</div>';

	$body .= '</html>';
	$body .= "";

	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'), env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_solicitud_adenda_contrato_arrendamiento([$usuario_creacion_correo]);

	$cc = $lista_correos['cc'];

	$titulo_email = "Gestion - Sistema Contratos - Nueva Solicitud de Adenda de Proveedor: Código - ";

	$bcc = $lista_correos['bcc'];

	$request = [
		"subject" => $titulo_email . $sigla_correlativo . $codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try {
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if (isset($request["cc"])) {
			foreach ($request["cc"] as $cc) {
				$mail->addAddress($cc);
			}
		}

		if (isset($request["bcc"])) {
			foreach ($request["bcc"] as $bcc) {
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

	} catch (Exception $e) {
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "llamar_funcion_send_email_solicitud_proveedor_detalle_lourdes_britto") {
	$contrato_id = $_POST["contrato_id"];
	send_email_confirmacion_solicitud_contrato_proveedor($contrato_id, true);
}

if (isset($_POST["accion"]) && $_POST["accion"] === "llamar_funcion_send_email_solicitud_proveedor_gerencia_detalle_lourdes_britto") {
	$contrato_id = $_POST["contrato_id"];
	send_email_solicitud_contrato_proveedores_confirmacion_gerencia($contrato_id, true);
}

if (isset($_POST["accion"]) && $_POST["accion"] === "llamar_funcion_send_email_solicitud_proveedor") {
	$contrato_id = $_POST["contrato_id"];
	send_email_solicitud_contrato_proveedor($contrato_id);

	$result['http_code'] = 200;
	echo json_encode($result);
	exit();
}

function send_email_confirmacion_solicitud_contrato_proveedor($contrato_id, $reenvio)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];
	$correos_add = [];

	$list_emp_cont = $mysqli->query("SELECT tpg.valor FROM tbl_parametros_generales tpg WHERE tpg.codigo = 'empresa_contacto_de_contratos' AND tpg.estado = 1 LIMIT 1");
	$row_emp_cont = $list_emp_cont->fetch_assoc();
	$valor_empresa_contacto = isset($row_emp_cont['valor']) && $row_emp_cont['valor'] == 'Kurax' ? '(Kurax)' : '(AT)';

	$sel_query = $mysqli->query(
		"
		SELECT c.ruc,
			c.razon_social,
			c.nombre_comercial,
			c.dni_representante,
			c.nombre_representante,
			c.persona_contacto_proveedor,
			c.detalle_servicio,
			c.alcance_servicio,
			c.tipo_terminacion_anticipada_id,
			ta.nombre AS tipo_terminacion_anticipada,
			c.terminacion_anticipada,
			c.observaciones,
			c.plazo_id,
			tp.nombre AS plazo,
			c.periodo_numero,
			p.id AS periodo_nombre_id,
			p.nombre AS periodo_nombre,
			c.fecha_inicio,
	        m.nombre AS tipo_moneda,
			m.simbolo AS tipo_moneda_simbolo,
			c.monto,
			c.forma_pago_id,
			f.nombre AS forma_pago,
			c.tipo_comprobante_id,
			t.nombre AS tipo_comprobante,
			c.plazo_pago,
			c.empresa_suscribe_id,
			r.nombre AS empresa_suscribe,
			c.user_created_id,
			CONCAT(IFNULL(pe.nombre, ''),' ',IFNULL(pe.apellido_paterno, ''),' ',IFNULL(pe.apellido_materno, '')) AS user_created,
			pe.correo AS correo_solicitante,
			c.created_at,
			co.sigla AS sigla_correlativo,
			c.codigo_correlativo,
			c.gerente_area_id,
			c.gerente_area_nombre,
			c.gerente_area_email,
			CONCAT(IFNULL(peg.nombre, ''),' ',IFNULL(peg.apellido_paterno, ''),' ',IFNULL(peg.apellido_materno, '')) AS nombre_del_gerente_area,
			peg.correo AS email_del_gerente_area,
			pud.correo AS email_del_director,
			pab.correo AS correo_abogado,
			CONCAT(IFNULL(pab.nombre, ''),' ',IFNULL(pab.apellido_paterno, ''),' ',IFNULL(pab.apellido_materno, '')) AS abogado
		FROM cont_contrato c
			LEFT JOIN cont_tipo_plazo tp ON c.plazo_id = tp.id
			LEFT JOIN cont_periodo p ON c.periodo = p.id
	        LEFT JOIN tbl_moneda m ON c.moneda_id = m.id
			LEFT JOIN cont_forma_pago f ON c.forma_pago_id = f.id
			LEFT JOIN cont_tipo_comprobante t ON c.tipo_comprobante_id = t.id
			INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id
			INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
			INNER JOIN tbl_personal_apt pe ON u.personal_id = pe.id
			LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			LEFT JOIN cont_tipo_terminacion_anticipada ta ON c.tipo_terminacion_anticipada_id = ta.id
			LEFT JOIN tbl_usuarios ug ON c.gerente_area_id = ug.id
			LEFT JOIN tbl_personal_apt peg ON ug.personal_id = peg.id

			LEFT JOIN tbl_usuarios ud ON c.director_aprobacion_id = ud.id
			LEFT JOIN tbl_personal_apt pud ON ud.personal_id = pud.id

			LEFT JOIN tbl_usuarios uab ON c.abogado_id = uab.id
			LEFT JOIN tbl_personal_apt pab ON uab.personal_id = pab.id

		WHERE c.tipo_contrato_id = 2 AND c.contrato_id = '" . $contrato_id . "'
		"
	);

	$body = "";
	$body .= '<html>';

	while ($sel = $sel_query->fetch_assoc()) {
		$dni_representante = $sel['dni_representante'];
		$nombre_representante = $sel['nombre_representante'];

		$sigla_correlativo = $sel['sigla_correlativo'];
		$codigo_correlativo = $sel['codigo_correlativo'];

		$proveedor_ruc = $sel['ruc'];
		$proveedor_razon_social_titulo = $sel['razon_social'];
		$proveedor_nombre_comercial = $sel['nombre_comercial'];

		$abogado = $sel['abogado'];


		$date = date_create($sel["fecha_inicio"]);
		$fecha_inicio = date_format($date, "Y-m-d");

		$tipo_moneda_simbolo = $sel["tipo_moneda_simbolo"];

		$monto = $tipo_moneda_simbolo . ' ' . number_format($sel["monto"], 2, '.', ',');

		$periodo_nombre_id = $sel["periodo_nombre_id"];

		$plazo_id = $sel["plazo_id"];
		$plazo = $sel["plazo"];
		$periodo_anio_mes = $sel["periodo_nombre"];
		$periodo_numero = $sel["periodo_numero"];
		$periodo = $periodo_numero . ' ' . $periodo_anio_mes;

		$email_del_director = $sel["email_del_director"];
		array_push($correos_add, $email_del_director);
		array_push($correos_add, $sel["correo_solicitante"]);
		$gerente_area_id = trim($sel["gerente_area_id"]);

		if (empty($gerente_area_id)) {
			$gerente_area_nombre = trim($sel["gerente_area_nombre"]);
			$gerente_area_email = trim($sel["gerente_area_email"]);
		} else {
			$gerente_area_nombre = trim($sel["nombre_del_gerente_area"]);
			$gerente_area_email = trim($sel["email_del_gerente_area"]);
		}

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
		$body .= '<tbody>';
		$body .= '<tr>';
		$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
		$body .= 'DATOS GENERALES';
		$body .= '</th>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>Empresa Arrendataria</b></td>';
		$body .= '<td>' . $sel["empresa_suscribe"] . '</td>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>Persona Contacto ' . $valor_empresa_contacto . '</b></td>';
		$body .= '<td>' . $sel["persona_contacto_proveedor"] . '</td>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>Responsable de Área</b></td>';
		$body .= '<td>' . $gerente_area_nombre . '</td>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>Registrado por</b></td>';
		$body .= '<td>' . $sel["user_created"] . '</td>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>Fecha de Registro</b></td>';
		$body .= '<td>' . $sel["created_at"] . '</td>';
		$body .= '</tr>';
		$body .= '</tbody>';
		$body .= '</table>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<br>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
		$body .= '<tbody>';
		$body .= '<tr>';
		$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
		$body .= 'DATOS DEL PROVEEDOR';
		$body .= '</th>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>RUC</b></td>';
		$body .= '<td>' . $sel["ruc"] . '</td>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>Raz&oacute;n Social</b></td>';
		$body .= '<td>' . $sel["razon_social"] . '</td>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>Nombre Comercial</b></td>';
		$body .= '<td>' . $sel["nombre_comercial"] . '</td>';
		$body .= '</tr>';
		$body .= '</tbody>';
		$body .= '</table>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<br>';
		$body .= '</div>';

		$sel_query_rl = $mysqli->query(
			"
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
			rl.dni_archivo_id,
			td.nombre as nombre_tipo_doc
		FROM 
			cont_representantes_legales rl
			LEFT JOIN cont_tipo_docu_identidad as td ON td.id = rl.tipo_documento_id
			LEFT JOIN tbl_bancos b on b.id = rl.id_banco
		WHERE 
			rl.contrato_id = $contrato_id"
		);
		$c = 0;

		$row_count_rl = $sel_query_rl->num_rows;

		if ($row_count_rl == 0) {

			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
			$body .= '<tr>';
			$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
			$body .= 'DATOS DEL PROVEEDOR - REPRESENTANTE LEGAL';
			$body .= '</th>';
			$body .= '</tr>';
			$body .= '<tr>';
			$body .= '<td style="background-color:#ffffdd;width:300px;"><b>DNI del representante legal</b></td>';
			$body .= '<td>' . $dni_representante . '</td>';
			$body .= '</tr>';
			$body .= '<tr>';
			$body .= '<td style="background-color:#ffffdd;"><b>Nombre completo del representante legal</b></td>';
			$body .= '<td>' . $nombre_representante . '</td>';
			$body .= '</tr>';
			$body .= '</table>';
		} elseif ($row_count_rl > 0) {
			while ($row_rl = $sel_query_rl->fetch_assoc()) {
				$c = $c + 1;

				$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
				$body .= '<tr>';
				$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
				$body .= 'DATOS DEL PROVEEDOR - REPRESENTANTE LEGAL #' . $c;
				$body .= '</th>';
				$body .= '</tr>';
				$body .= '<tr>';
				$body .= '<td style="background-color:#ffffdd;width:300px;"><b>' . $row_rl['nombre_tipo_doc'] . ' del representante</b></td>';
				$body .= '<td>' . $row_rl["dni_representante"] . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
				$body .= '<td style="background-color:#ffffdd;"><b>Nombre completo del representante</b></td>';
				$body .= '<td>' . $row_rl["nombre_representante"] . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
				$body .= '<td style="background-color:#ffffdd;"><b>Número de Cuenta de Detracción</b></td>';
				$body .= '<td>' . $row_rl["nro_cuenta_detraccion"] . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
				$body .= '<td style="background-color:#ffffdd;"><b>Banco</b></td>';
				$body .= '<td>' . $row_rl["banco_representante"] . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
				$body .= '<td style="background-color:#ffffdd;"><b>Número de cuenta bancaria</b></td>';
				$body .= '<td>' . $row_rl["nro_cuenta"] . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
				$body .= '<td style="background-color:#ffffdd;"><b>Número de CCI</b></td>';
				$body .= '<td>' . $row_rl["nro_cci"] . '</td>';
				$body .= '</tr>';
				$body .= '</table>';
			}
		}

		$body .= '<div>';
		$body .= '<br>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<div class="form-group">';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
		$body .= '<tbody>';

		$body .= '<tr>';
		$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
		$body .= 'OBJETO DEL CONTRATO';
		$body .= '</th>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>Objeto</b></td>';
		$body .= '<td>' . $sel["detalle_servicio"] . '</td>';
		$body .= '</tr>';

		$body .= '</tbody>';
		$body .= '</table>';
		$body .= '</div>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<br>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<div class="form-group">';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
		$body .= '<tbody>';

		$body .= '<tr>';
		$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
		$body .= 'PLAZO DEL CONTRATO';
		$body .= '</th>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>Plazo</b></td>';
		$body .= '<td>' . $plazo . '</td>';
		$body .= '</tr>';

		if ($plazo_id == 1) {
			$body .= '<tr>';
			$body .= '<td style="background-color:#ffffdd;width:300px"><b>Periodo</b></td>';
			$body .= '<td>' . $periodo . '</td>';
			$body .= '</tr>';
		}

		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>Fecha de inicio</b></td>';
		$body .= '<td>' . $fecha_inicio . '</td>';
		$body .= '</tr>';

		$body .= '</tbody>';
		$body .= '</table>';
		$body .= '</div>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<br>';
		$body .= '</div>';

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
			c.contrato_id = $contrato_id
		";

		$query = $mysqli->query($sql_contraprestacion);
		$row_count = $query->num_rows;

		if ($row_count > 0) {
			$contador_contraprestacion = 1;
			while ($row = $query->fetch_assoc()) {
				$contraprestacion_id = $row["id"];
				$tipo_moneda = $row["tipo_moneda"];
				$tipo_moneda_simbolo = $row["tipo_moneda_simbolo"];
				$subtotal = $tipo_moneda_simbolo . ' ' . number_format($row["subtotal"], 2, '.', ',');
				$igv = $tipo_moneda_simbolo . ' ' . number_format($row["igv"], 2, '.', ',');
				$monto = $tipo_moneda_simbolo . ' ' . number_format($row["monto"], 2, '.', ',');
				$forma_pago_detallado = $row["forma_pago_detallado"];
				$tipo_comprobante = $row["tipo_comprobante"];
				$plazo_pago = $row["plazo_pago"];

				$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
				$body .= '<tr>';
				$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
				$body .= 'CONTRAPRESTACIÓN # ' . $contador_contraprestacion;
				$body .= '</th>';
				$body .= '</tr>';
				$body .= '<tr>';
				$body .= '<td style="background-color:#ffffdd;width:300px;"><b>Tipo de moneda</b></td>';
				$body .= '<td>' . $tipo_moneda  . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
				$body .= '<td style="background-color:#ffffdd;"><b>Subtotal</b></td>';
				$body .= '<td>' . $subtotal . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
				$body .= '<td style="background-color:#ffffdd;"><b>IGV</b></td>';
				$body .= '<td>' . $igv . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
				$body .= '<td style="background-color:#ffffdd;"><b>Monto Bruto</b></td>';
				$body .= '<td>' . $monto . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
				$body .= '<td style="background-color:#ffffdd;"><b>Forma de pago</b></td>';
				$body .= '<td>' . $forma_pago_detallado . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
				$body .= '<td style="background-color:#ffffdd;"><b>Tipo de comprobante a emitir</b></td>';
				$body .= '<td>' . $tipo_comprobante . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
				$body .= '<td style="background-color:#ffffdd;"><b>Plazo de Pago</b></td>';
				$body .= '<td>' . $plazo_pago . '</td>';
				$body .= '</tr>';
				$body .= '</table>';
				$body .= '<br>';

				$contador_contraprestacion++;
			}
		} elseif ($row_count == 0) {

			$body .= '<div>';
			$body .= '<div class="form-group">';
			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
			$body .= '<tbody>';

			$body .= '<tr>';
			$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
			$body .= 'CONTRAPRESTACIÓN';
			$body .= '</th>';
			$body .= '</tr>';
			$body .= '<tr>';
			$body .= '<td style="background-color:#ffffdd;width:300px"><b>Tipo de moneda</b></td>';
			$body .= '<td>' . $sel["tipo_moneda"] . '</td>';
			$body .= '</tr>';

			$body .= '<tr>';
			$body .= '<td style="background-color:#ffffdd;width:300px"><b>Monto</b></td>';
			$body .= '<td>' . $monto . '</td>';
			$body .= '</tr>';

			$body .= '<tr>';
			$body .= '<td style="background-color:#ffffdd;width:300px"><b>Forma de pago</b></td>';
			$body .= '<td>' . $sel["forma_pago"] . '</td>';
			$body .= '</tr>';

			$body .= '<tr>';
			$body .= '<td style="background-color:#ffffdd;width:300px"><b>Tipo de comprobante a emitir</b></td>';
			$body .= '<td>' . $sel["tipo_comprobante"] . '</td>';
			$body .= '</tr>';
			$body .= '<tr>';
			$body .= '<td style="background-color:#ffffdd;width:300px"><b>Plazo de Pago</b></td>';
			$body .= '<td>' . $sel["plazo_pago"] . '</td>';
			$body .= '</tr>';

			$body .= '</tbody>';
			$body .= '</table>';
			$body .= '</div>';
			$body .= '</div>';
		}

		$body .= '<div>';
		$body .= '<div class="form-group">';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
		$body .= '<tbody>';

		$body .= '<tr>';
		$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
		$body .= 'ALCANCE DEL SERVICIO';
		$body .= '</th>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>Alcance</b></td>';
		$body .= '<td>' . $sel["alcance_servicio"] . '</td>';
		$body .= '</tr>';

		$body .= '</tbody>';
		$body .= '</table>';
		$body .= '</div>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<br>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<div class="form-group">';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
		$body .= '<tbody>';

		$body .= '<tr>';
		$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
		$body .= 'TERMINACIÓN ANTICIPADA';
		$body .= '</th>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>Terminación Anticipada</b></td>';
		$body .= '<td>' . $sel["tipo_terminacion_anticipada"] . '</td>';
		$body .= '</tr>';
		if ($sel["tipo_terminacion_anticipada_id"] == "1") {
			$body .= '<tr>';
			$body .= '<td style="background-color:#ffffdd;width:300px"><b>Terminación Anticipada - Detalle</b></td>';
			$body .= '<td>' . $sel["terminacion_anticipada"] . '</td>';
			$body .= '</tr>';
		}

		$body .= '</tbody>';
		$body .= '</table>';
		$body .= '</div>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<br>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<div class="form-group">';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
		$body .= '<tbody>';

		$body .= '<tr>';
		$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
		$body .= 'OBSERVACIONES';
		$body .= '</th>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>Observaciones</b></td>';
		$body .= '<td>' . $sel["observaciones"] . '</td>';
		$body .= '</tr>';

		$body .= '</tbody>';
		$body .= '</table>';
		$body .= '</div>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<br>';
		$body .= '</div>';

		$body .= '<div style="width: 600px; text-align: center; font-family: arial;">';
		$body .= '<a href="' . $host . '/?sec_id=contrato&sub_sec_id=detalleSolicitudProveedor&id=' . $contrato_id . '" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
		$body .= '<b>Ver Solicitud</b>';
		$body .= '</a>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<br>';
		$body .= '</div>';
	}
	$body .= '</html>';
	$body .= "";

	$renvio_titulo = "";

	if ($reenvio) {
		// $renvio_titulo = "Reenvio - ";
		$renvio_titulo = "";
	}

	if (isset($_POST['correos_adjuntos']) && $_POST['correos_adjuntos'] != "") {
		$validate = true;
		$emails = preg_split('[,|;]', $_POST['correos_adjuntos']);
		foreach ($emails as $e) {
			if (preg_match('/^[_ña-z0-9-]+(\.[_ña-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/', trim($e)) == 0) {
				$error_msg = "'" . $e . "'" . " no es un Correo Adjunto válido";
				if ($e == "") {
					$error_msg = "Formato de Correo Incorrecto";
				}
				$result["http_code"] = 500;
				$result["status"] = "error";
				$result["mensaje"] = $error_msg;
				echo json_encode($result);
				die;
			} else {
				$correos_add[] = $e;
			}
		}
	}

	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'), env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_confirmacion_solicitud_contrato_proveedor($correos_add);

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];

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
		$body .= '<td>' . $correos_produccion . '</td>';
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
		//"subject" => "CONFIRMACIÓN DE SOLICITUD PROVEEDOR: ".$proveedor_razon_social_titulo. " - RUC " .$proveedor_ruc,
		"subject" => $renvio_titulo . "Gestion - Sistema Contratos - Aprobación del Documento - Confirmación de Solicitud de Proveedor: CÓD - " . $sigla_correlativo . $codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];


	$mail = new PHPMailer(true);

	try {
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if (isset($request["cc"])) {
			foreach ($request["cc"] as $cc) {
				$mail->addAddress($cc);
			}
		}

		if (isset($request["bcc"])) {
			foreach ($request["bcc"] as $bcc) {
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->send();
		//return true;

		if ($reenvio) {
			$result["http_code"] = 200;
			$result["status"] = "Datos obtenidos de gestion.";
			echo json_encode($result);
			exit();
		}
	} catch (Exception $e) {
		if ($reenvio) {
			$result["http_code"] = 400;
			$result["status"] = "Datos obtenidos de gestion.";
			$result["error"] = $mail->ErrorInfo;
			echo json_encode($result);
			exit();
		} else {
			$resultado = $mail->ErrorInfo;
			//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
			//return false;
			echo json_encode($resultado);
		}
	}
}

function send_email_solicitud_contrato_proveedores_confirmacion_gerencia($contrato_id)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];
	$correos_add = [];

	$sel_query = $mysqli->query(
		"
		SELECT c.ruc,
			c.razon_social,
			c.dni_representante,
			c.nombre_representante,
			c.persona_contacto_proveedor,
			c.detalle_servicio,
			c.alcance_servicio,
			c.tipo_terminacion_anticipada_id,
			ta.nombre AS tipo_terminacion_anticipada,
			c.terminacion_anticipada,
			c.observaciones,
			c.plazo_id,
			tp.nombre AS plazo,
			c.periodo_numero,
			p.id AS periodo_nombre_id,
			p.nombre AS periodo_nombre,
			c.fecha_inicio,
	        m.nombre AS tipo_moneda,
			m.simbolo AS tipo_moneda_simbolo,
			c.monto,
			c.forma_pago_id,
			f.nombre AS forma_pago,
			c.tipo_comprobante_id,
			t.nombre AS tipo_comprobante,
			c.plazo_pago,
			c.empresa_suscribe_id,
			r.nombre AS empresa_suscribe,
			c.user_created_id,
			CONCAT(IFNULL(pe.nombre, ''),' ',IFNULL(pe.apellido_paterno, ''),' ',IFNULL(pe.apellido_materno, '')) AS user_created,
			c.created_at,
			co.sigla AS sigla_correlativo,
			c.codigo_correlativo,
			c.gerente_area_id,
			c.gerente_area_nombre,
			c.gerente_area_email,
			CONCAT(IFNULL(peg.nombre, ''),' ',IFNULL(peg.apellido_paterno, ''),' ',IFNULL(peg.apellido_materno, '')) AS nombre_del_gerente_area,
			peg.correo AS email_del_gerente_area,
			pud.correo AS email_del_director
		FROM cont_contrato c
			LEFT JOIN cont_tipo_plazo tp ON c.plazo_id = tp.id
			LEFT JOIN cont_periodo p ON c.periodo = p.id
	        LEFT JOIN tbl_moneda m ON c.moneda_id = m.id
			LEFT JOIN cont_forma_pago f ON c.forma_pago_id = f.id
			LEFT JOIN cont_tipo_comprobante t ON c.tipo_comprobante_id = t.id
			INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id
			INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
			INNER JOIN tbl_personal_apt pe ON u.personal_id = pe.id
			LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			LEFT JOIN cont_tipo_terminacion_anticipada ta ON c.tipo_terminacion_anticipada_id = ta.id
			LEFT JOIN tbl_usuarios ug ON c.gerente_area_id = ug.id
			LEFT JOIN tbl_personal_apt peg ON ug.personal_id = peg.id
			LEFT JOIN tbl_usuarios ud ON c.director_aprobacion_id = ud.id
			LEFT JOIN tbl_personal_apt pud ON ud.personal_id = pud.id
		WHERE c.tipo_contrato_id = 2 AND c.contrato_id = '" . $contrato_id . "'
		"
	);

	$body = "";
	$body .= '<html>';

	$list_emp_cont = $mysqli->query("SELECT tpg.valor FROM tbl_parametros_generales tpg WHERE tpg.codigo = 'empresa_contacto_de_contratos' AND tpg.estado = 1 LIMIT 1");
	$row_emp_cont = $list_emp_cont->fetch_assoc();
	$valor_empresa_contacto = isset($row_emp_cont['valor']) && $row_emp_cont['valor'] == 'Kurax' ? '(Kurax)' : '(AT)';

	while ($sel = $sel_query->fetch_assoc()) {
		$dni_representante = $sel['dni_representante'];
		$nombre_representante = $sel['nombre_representante'];

		$sigla_correlativo = $sel['sigla_correlativo'];
		$codigo_correlativo = $sel['codigo_correlativo'];

		$proveedor_ruc = $sel['ruc'];
		$proveedor_razon_social_titulo = $sel['razon_social'];

		$date = date_create($sel["fecha_inicio"]);
		$fecha_inicio = date_format($date, "Y-m-d");

		$tipo_moneda_simbolo = $sel["tipo_moneda_simbolo"];

		$monto = $tipo_moneda_simbolo . ' ' . number_format($sel["monto"], 2, '.', ',');

		$periodo_nombre_id = $sel["periodo_nombre_id"];

		$plazo_id = $sel["plazo_id"];
		$plazo = $sel["plazo"];
		$periodo_anio_mes = $sel["periodo_nombre"];
		$periodo_numero = $sel["periodo_numero"];
		$periodo = $periodo_numero . ' ' . $periodo_anio_mes;

		$email_del_director = $sel["email_del_director"];
		array_push($correos_add, $email_del_director);

		$gerente_area_id = trim($sel["gerente_area_id"]);

		if (empty($gerente_area_id)) {
			$gerente_area_nombre = trim($sel["gerente_area_nombre"]);
			$gerente_area_email = trim($sel["gerente_area_email"]);
		} else {
			$gerente_area_nombre = trim($sel["nombre_del_gerente_area"]);
			$gerente_area_email = trim($sel["email_del_gerente_area"]);
		}

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
		$body .= '<tbody>';
		$body .= '<tr>';
		$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
		$body .= 'DATOS GENERALES';
		$body .= '</th>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>Empresa Arrendataria</b></td>';
		$body .= '<td>' . $sel["empresa_suscribe"] . '</td>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>Persona Contacto ' . $valor_empresa_contacto . '</b></td>';
		$body .= '<td>' . $sel["persona_contacto_proveedor"] . '</td>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>Responsable de Área</b></td>';
		$body .= '<td>' . $gerente_area_nombre . '</td>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>Registrado por</b></td>';
		$body .= '<td>' . $sel["user_created"] . '</td>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>Fecha de Registro</b></td>';
		$body .= '<td>' . $sel["created_at"] . '</td>';
		$body .= '</tr>';
		$body .= '</tbody>';
		$body .= '</table>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<br>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
		$body .= '<tbody>';
		$body .= '<tr>';
		$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
		$body .= 'DATOS DEL PROVEEDOR';
		$body .= '</th>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>RUC</b></td>';
		$body .= '<td>' . $sel["ruc"] . '</td>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>Raz&oacute;n Social</b></td>';
		$body .= '<td>' . $sel["razon_social"] . '</td>';
		$body .= '</tr>';
		$body .= '</tbody>';
		$body .= '</table>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<br>';
		$body .= '</div>';

		$sel_query_rl = $mysqli->query(
			"
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
			rl.contrato_id = $contrato_id"
		);
		$c = 0;

		$row_count_rl = $sel_query_rl->num_rows;

		if ($row_count_rl == 0) {

			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
			$body .= '<tr>';
			$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
			$body .= 'DATOS DEL PROVEEDOR - REPRESENTANTE LEGAL';
			$body .= '</th>';
			$body .= '</tr>';
			$body .= '<tr>';
			$body .= '<td style="background-color:#ffffdd;width:300px;"><b>DNI del representante legal</b></td>';
			$body .= '<td>' . $dni_representante . '</td>';
			$body .= '</tr>';
			$body .= '<tr>';
			$body .= '<td style="background-color:#ffffdd;"><b>Nombre completo del representante legal</b></td>';
			$body .= '<td>' . $nombre_representante . '</td>';
			$body .= '</tr>';
			$body .= '</table>';
		} elseif ($row_count_rl > 0) {
			while ($row_rl = $sel_query_rl->fetch_assoc()) {
				$c = $c + 1;

				$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
				$body .= '<tr>';
				$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
				$body .= 'DATOS DEL PROVEEDOR - REPRESENTANTE LEGAL #' . $c;
				$body .= '</th>';
				$body .= '</tr>';
				$body .= '<tr>';
				$body .= '<td style="background-color:#ffffdd;width:300px;"><b>DNI del representante</b></td>';
				$body .= '<td>' . $row_rl["dni_representante"] . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
				$body .= '<td style="background-color:#ffffdd;"><b>Nombre completo del representante</b></td>';
				$body .= '<td>' . $row_rl["nombre_representante"] . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
				$body .= '<td style="background-color:#ffffdd;"><b>Número de Cuenta de Detracción</b></td>';
				$body .= '<td>' . $row_rl["nro_cuenta_detraccion"] . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
				$body .= '<td style="background-color:#ffffdd;"><b>Banco</b></td>';
				$body .= '<td>' . $row_rl["banco_representante"] . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
				$body .= '<td style="background-color:#ffffdd;"><b>Número de cuenta bancaria</b></td>';
				$body .= '<td>' . $row_rl["nro_cuenta"] . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
				$body .= '<td style="background-color:#ffffdd;"><b>Número de CCI</b></td>';
				$body .= '<td>' . $row_rl["nro_cci"] . '</td>';
				$body .= '</tr>';
				$body .= '</table>';
			}
		}

		$body .= '<div>';
		$body .= '<br>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<div class="form-group">';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
		$body .= '<tbody>';

		$body .= '<tr>';
		$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
		$body .= 'OBJETO DEL CONTRATO';
		$body .= '</th>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>Objeto</b></td>';
		$body .= '<td>' . $sel["detalle_servicio"] . '</td>';
		$body .= '</tr>';

		$body .= '</tbody>';
		$body .= '</table>';
		$body .= '</div>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<br>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<div class="form-group">';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
		$body .= '<tbody>';

		$body .= '<tr>';
		$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
		$body .= 'PLAZO DEL CONTRATO';
		$body .= '</th>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>Plazo</b></td>';
		$body .= '<td>' . $plazo . '</td>';
		$body .= '</tr>';

		if ($plazo_id == 1) {
			$body .= '<tr>';
			$body .= '<td style="background-color:#ffffdd;width:300px"><b>Periodo</b></td>';
			$body .= '<td>' . $periodo . '</td>';
			$body .= '</tr>';
		}

		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>Fecha de inicio</b></td>';
		$body .= '<td>' . $fecha_inicio . '</td>';
		$body .= '</tr>';

		$body .= '</tbody>';
		$body .= '</table>';
		$body .= '</div>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<br>';
		$body .= '</div>';

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
			c.contrato_id = $contrato_id
		";

		$query = $mysqli->query($sql_contraprestacion);
		$row_count = $query->num_rows;

		if ($row_count > 0) {
			$contador_contraprestacion = 1;
			while ($row = $query->fetch_assoc()) {
				$contraprestacion_id = $row["id"];
				$tipo_moneda = $row["tipo_moneda"];
				$tipo_moneda_simbolo = $row["tipo_moneda_simbolo"];
				$subtotal = $tipo_moneda_simbolo . ' ' . number_format($row["subtotal"], 2, '.', ',');
				$igv = $tipo_moneda_simbolo . ' ' . number_format($row["igv"], 2, '.', ',');
				$monto = $tipo_moneda_simbolo . ' ' . number_format($row["monto"], 2, '.', ',');
				$forma_pago_detallado = $row["forma_pago_detallado"];
				$tipo_comprobante = $row["tipo_comprobante"];
				$plazo_pago = $row["plazo_pago"];

				$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
				$body .= '<tr>';
				$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
				$body .= 'CONTRAPRESTACIÓN # ' . $contador_contraprestacion;
				$body .= '</th>';
				$body .= '</tr>';
				$body .= '<tr>';
				$body .= '<td style="background-color:#ffffdd;width:300px;"><b>Tipo de moneda</b></td>';
				$body .= '<td>' . $tipo_moneda  . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
				$body .= '<td style="background-color:#ffffdd;"><b>Subtotal</b></td>';
				$body .= '<td>' . $subtotal . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
				$body .= '<td style="background-color:#ffffdd;"><b>IGV</b></td>';
				$body .= '<td>' . $igv . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
				$body .= '<td style="background-color:#ffffdd;"><b>Monto Bruto</b></td>';
				$body .= '<td>' . $monto . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
				$body .= '<td style="background-color:#ffffdd;"><b>Forma de pago</b></td>';
				$body .= '<td>' . $forma_pago_detallado . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
				$body .= '<td style="background-color:#ffffdd;"><b>Tipo de comprobante a emitir</b></td>';
				$body .= '<td>' . $tipo_comprobante . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
				$body .= '<td style="background-color:#ffffdd;"><b>Plazo de Pago</b></td>';
				$body .= '<td>' . $plazo_pago . '</td>';
				$body .= '</tr>';
				$body .= '</table>';
				$body .= '<br>';

				$contador_contraprestacion++;
			}
		} elseif ($row_count == 0) {

			$body .= '<div>';
			$body .= '<div class="form-group">';
			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
			$body .= '<tbody>';

			$body .= '<tr>';
			$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
			$body .= 'CONTRAPRESTACIÓN';
			$body .= '</th>';
			$body .= '</tr>';
			$body .= '<tr>';
			$body .= '<td style="background-color:#ffffdd;width:300px"><b>Tipo de moneda</b></td>';
			$body .= '<td>' . $sel["tipo_moneda"] . '</td>';
			$body .= '</tr>';

			$body .= '<tr>';
			$body .= '<td style="background-color:#ffffdd;width:300px"><b>Monto</b></td>';
			$body .= '<td>' . $monto . '</td>';
			$body .= '</tr>';

			$body .= '<tr>';
			$body .= '<td style="background-color:#ffffdd;width:300px"><b>Forma de pago</b></td>';
			$body .= '<td>' . $sel["forma_pago"] . '</td>';
			$body .= '</tr>';

			$body .= '<tr>';
			$body .= '<td style="background-color:#ffffdd;width:300px"><b>Tipo de comprobante a emitir</b></td>';
			$body .= '<td>' . $sel["tipo_comprobante"] . '</td>';
			$body .= '</tr>';
			$body .= '<tr>';
			$body .= '<td style="background-color:#ffffdd;width:300px"><b>Plazo de Pago</b></td>';
			$body .= '<td>' . $sel["plazo_pago"] . '</td>';
			$body .= '</tr>';

			$body .= '</tbody>';
			$body .= '</table>';
			$body .= '</div>';
			$body .= '</div>';
		}

		$body .= '<div>';
		$body .= '<div class="form-group">';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
		$body .= '<tbody>';

		$body .= '<tr>';
		$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
		$body .= 'ALCANCE DEL SERVICIO';
		$body .= '</th>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>Alcance</b></td>';
		$body .= '<td>' . $sel["alcance_servicio"] . '</td>';
		$body .= '</tr>';

		$body .= '</tbody>';
		$body .= '</table>';
		$body .= '</div>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<br>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<div class="form-group">';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
		$body .= '<tbody>';

		$body .= '<tr>';
		$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
		$body .= 'TERMINACIÓN ANTICIPADA';
		$body .= '</th>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>Terminación Anticipada</b></td>';
		$body .= '<td>' . $sel["tipo_terminacion_anticipada"] . '</td>';
		$body .= '</tr>';
		if ($sel["tipo_terminacion_anticipada_id"] == "1") {
			$body .= '<tr>';
			$body .= '<td style="background-color:#ffffdd;width:300px"><b>Terminación Anticipada - Detalle</b></td>';
			$body .= '<td>' . $sel["terminacion_anticipada"] . '</td>';
			$body .= '</tr>';
		}

		$body .= '</tbody>';
		$body .= '</table>';
		$body .= '</div>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<br>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<div class="form-group">';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
		$body .= '<tbody>';

		$body .= '<tr>';
		$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
		$body .= 'OBSERVACIONES';
		$body .= '</th>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>Observaciones</b></td>';
		$body .= '<td>' . $sel["observaciones"] . '</td>';
		$body .= '</tr>';

		$body .= '</tbody>';
		$body .= '</table>';
		$body .= '</div>';
		$body .= '</div>';



		$sql_obs = "SELECT 
		o.observaciones,
		o.created_at
		FROM cont_observaciones o
		WHERE o.contrato_id = " . $contrato_id . "
		AND o.status = 1
		AND o.user_created_id = 3218
		ORDER BY o.created_at DESC";

		$query_obs = $mysqli->query($sql_obs);
		$row_count = $query_obs->num_rows;
		if ($row_count > 0) {
			$body .= '<div>';
			$body .= '<br>';
			$body .= '</div>';

			$body .= '<div>';
			$body .= '<div class="form-group">';
			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
			$body .= '<tbody>';

			$body .= '<tr>';
			$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
			$body .= 'OBSERVACION DE GERENCIA';
			$body .= '</th>';
			$body .= '</tr>';
			$i = 1;
			while ($row = $query_obs->fetch_assoc()) {
				$body .= '<tr>';
				$body .= '<td style="background-color:#ffffdd;width:300px"><b>Observación #' . $i . '</b></td>';
				$body .= '<td>' . $row["observaciones"] . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
				$body .= '<td style="background-color:#ffffdd;width:300px"><b>Fecha </b></td>';
				$body .= '<td>' . $row["created_at"] . '</td>';
				$body .= '</tr>';
				$i++;
			}



			$body .= '</tbody>';
			$body .= '</table>';
			$body .= '</div>';
			$body .= '</div>';
		}

		$body .= '<div>';
		$body .= '<br>';
		$body .= '</div>';

		$body .= '<div style="width: 600px; text-align: center; font-family: arial;">';
		$body .= '<a href="' . $host . '/?sec_id=contrato&sub_sec_id=detalleSolicitudProveedor&id=' . $contrato_id . '" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
		$body .= '<b>Ver Solicitud</b>';
		$body .= '</a>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<br>';
		$body .= '</div>';
	}
	$body .= '</html>';
	$body .= "";

	$renvio_titulo = "";

	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'), env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_confirmacion_solicitud_contrato_proveedor($correos_add);

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];


	$request = [
		//"subject" => "CONFIRMACIÓN DE SOLICITUD PROVEEDOR: ".$proveedor_razon_social_titulo. " - RUC " .$proveedor_ruc,
		"subject" => $renvio_titulo . "Gestion - Sistema Contratos - Confirmación de Solicitud de Proveedor (Observación Corregida): CÓD - " . $sigla_correlativo . $codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];


	$mail = new PHPMailer(true);

	try {
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if (isset($request["cc"])) {
			foreach ($request["cc"] as $cc) {
				$mail->addAddress($cc);
			}
		}

		if (isset($request["bcc"])) {
			foreach ($request["bcc"] as $bcc) {
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->send();
		//return true;


		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		echo json_encode($result);
		exit();
	} catch (Exception $e) {

		$result["http_code"] = 400;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["error"] = $mail->ErrorInfo;
		echo json_encode($result);
		exit();
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "llamar_funcion_send_email_solicitud_acuerdo_confidencialidad_detalle_lourdes_britto") {
	$contrato_id = $_POST["contrato_id"];
	send_email_confirmacion_acuerdo_confidencialidad($contrato_id, true);
}

function send_email_confirmacion_acuerdo_confidencialidad($contrato_id, $reenvio)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$sel_query = $mysqli->query(
		"
		SELECT c.ruc,
			c.razon_social,
			c.dni_representante,
			c.nombre_representante,
			c.persona_contacto_proveedor,
			c.detalle_servicio,
			c.alcance_servicio,
			c.tipo_terminacion_anticipada_id,
			ta.nombre AS tipo_terminacion_anticipada,
			c.terminacion_anticipada,
			c.observaciones,
			CONCAT(c.periodo_numero, ' ', p.nombre) AS periodo,
			c.periodo_numero,
			p.id AS periodo_nombre_id,
			p.nombre AS periodo_nombre,
			c.fecha_inicio,
	        m.nombre AS tipo_moneda,
			m.simbolo AS tipo_moneda_simbolo,
			c.monto,
			c.forma_pago_id,
			f.nombre AS forma_pago,
			c.tipo_comprobante_id,
			t.nombre AS tipo_comprobante,
			c.plazo_pago,
			c.empresa_suscribe_id,
			r.nombre AS empresa_suscribe,
			c.user_created_id,
			CONCAT(IFNULL(pe.nombre, ''),' ',IFNULL(pe.apellido_paterno, ''),' ',IFNULL(pe.apellido_materno, '')) AS user_created,
			c.created_at,
			co.sigla AS sigla_correlativo,
			c.codigo_correlativo
		FROM cont_contrato c
			LEFT JOIN cont_periodo p ON c.periodo = p.id
	        LEFT JOIN tbl_moneda m ON c.moneda_id = m.id
			LEFT JOIN cont_forma_pago f ON c.forma_pago_id = f.id
			LEFT JOIN cont_tipo_comprobante t ON c.tipo_comprobante_id = t.id
			INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id
			INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
			INNER JOIN tbl_personal_apt pe ON u.personal_id = pe.id
			LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			LEFT JOIN cont_tipo_terminacion_anticipada ta ON c.tipo_terminacion_anticipada_id = ta.id
		WHERE c.tipo_contrato_id = 5 AND c.contrato_id = '" . $contrato_id . "'
		"
	);

	$body = "";
	$body .= '<html>';

	$list_emp_cont = $mysqli->query("SELECT tpg.valor FROM tbl_parametros_generales tpg WHERE tpg.codigo = 'empresa_contacto_de_contratos' AND tpg.estado = 1 LIMIT 1");
	$row_emp_cont = $list_emp_cont->fetch_assoc();
	$valor_empresa_contacto = isset($row_emp_cont['valor']) && $row_emp_cont['valor'] == 'Kurax' ? '(Kurax)' : '(AT)';

	while ($sel = $sel_query->fetch_assoc()) {
		$dni_representante = $sel['dni_representante'];
		$nombre_representante = $sel['nombre_representante'];

		$sigla_correlativo = $sel['sigla_correlativo'];
		$codigo_correlativo = $sel['codigo_correlativo'];

		$proveedor_ruc = $sel['ruc'];
		$proveedor_razon_social_titulo = $sel['razon_social'];

		$date = date_create($sel["fecha_inicio"]);
		$fecha_inicio = date_format($date, "Y-m-d");

		$tipo_moneda_simbolo = $sel["tipo_moneda_simbolo"];

		$monto = $tipo_moneda_simbolo . ' ' . number_format($sel["monto"], 2, '.', ',');

		$periodo_nombre_id = $sel["periodo_nombre_id"];

		$periodo_numero = $sel["periodo_numero"];


		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
		$body .= '<tbody>';
		$body .= '<tr>';
		$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
		$body .= 'DATOS GENERALES';
		$body .= '</th>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>Empresa Arrendataria</b></td>';
		$body .= '<td>' . $sel["empresa_suscribe"] . '</td>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>Persona Contacto ' . $valor_empresa_contacto . '</b></td>';
		$body .= '<td>' . $sel["persona_contacto_proveedor"] . '</td>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>Registrado por</b></td>';
		$body .= '<td>' . $sel["user_created"] . '</td>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>Fecha de Registro</b></td>';
		$body .= '<td>' . $sel["created_at"] . '</td>';
		$body .= '</tr>';
		$body .= '</tbody>';
		$body .= '</table>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<br>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
		$body .= '<tbody>';
		$body .= '<tr>';
		$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
		$body .= 'DATOS DEL PROVEEDOR';
		$body .= '</th>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>RUC</b></td>';
		$body .= '<td>' . $sel["ruc"] . '</td>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>Raz&oacute;n Social</b></td>';
		$body .= '<td>' . $sel["razon_social"] . '</td>';
		$body .= '</tr>';
		$body .= '</tbody>';
		$body .= '</table>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<br>';
		$body .= '</div>';

		$sel_query_rl = $mysqli->query(
			"
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
			rl.contrato_id = $contrato_id"
		);
		$c = 0;

		$row_count_rl = $sel_query_rl->num_rows;

		if ($row_count_rl == 0) {

			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
			$body .= '<tr>';
			$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
			$body .= 'DATOS DEL PROVEEDOR - REPRESENTANTE LEGAL';
			$body .= '</th>';
			$body .= '</tr>';
			$body .= '<tr>';
			$body .= '<td style="background-color:#ffffdd;width:300px;"><b>DNI del representante legal</b></td>';
			$body .= '<td>' . $dni_representante . '</td>';
			$body .= '</tr>';
			$body .= '<tr>';
			$body .= '<td style="background-color:#ffffdd;"><b>Nombre completo del representante legal</b></td>';
			$body .= '<td>' . $nombre_representante . '</td>';
			$body .= '</tr>';
			$body .= '</table>';
		} elseif ($row_count_rl > 0) {
			while ($row_rl = $sel_query_rl->fetch_assoc()) {
				$c = $c + 1;

				$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
				$body .= '<tr>';
				$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
				$body .= 'DATOS DEL PROVEEDOR - REPRESENTANTE LEGAL #' . $c;
				$body .= '</th>';
				$body .= '</tr>';
				$body .= '<tr>';
				$body .= '<td style="background-color:#ffffdd;width:300px;"><b>DNI del representante</b></td>';
				$body .= '<td>' . $row_rl["dni_representante"] . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
				$body .= '<td style="background-color:#ffffdd;"><b>Nombre completo del representante</b></td>';
				$body .= '<td>' . $row_rl["nombre_representante"] . '</td>';
				$body .= '</tr>';
				$body .= '</table>';
			}
		}

		$body .= '<div>';
		$body .= '<br>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<div class="form-group">';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
		$body .= '<tbody>';

		$body .= '<tr>';
		$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
		$body .= 'OBJETO DEL CONTRATO';
		$body .= '</th>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>Objeto</b></td>';
		$body .= '<td>' . $sel["detalle_servicio"] . '</td>';
		$body .= '</tr>';

		$body .= '</tbody>';
		$body .= '</table>';
		$body .= '</div>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<br>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<div class="form-group">';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
		$body .= '<tbody>';

		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>Fecha de inicio</b></td>';
		$body .= '<td>' . $fecha_inicio . '</td>';
		$body .= '</tr>';

		$body .= '</tbody>';
		$body .= '</table>';
		$body .= '</div>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<br>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<div class="form-group">';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
		$body .= '<tbody>';

		$body .= '<tr>';
		$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
		$body .= 'OBSERVACIONES';
		$body .= '</th>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;width:300px"><b>Observaciones</b></td>';
		$body .= '<td>' . $sel["observaciones"] . '</td>';
		$body .= '</tr>';

		$body .= '</tbody>';
		$body .= '</table>';
		$body .= '</div>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<br>';
		$body .= '</div>';

		$body .= '<div style="width: 600px; text-align: center; font-family: arial;">';
		$body .= '<a href="' . $host . '/?sec_id=contrato&sub_sec_id=detalle_solicitud_acuerdo_confidencialidad&id=' . $contrato_id . '" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
		$body .= '<b>Ver Solicitud</b>';
		$body .= '</a>';
		$body .= '</div>';

		$body .= '<div>';
		$body .= '<br>';
		$body .= '</div>';
	}
	$body .= '</html>';
	$body .= "";

	$renvio_titulo = "";

	if ($reenvio) {
		// $renvio_titulo = "Reenvio - ";
		$renvio_titulo = "";
	}

	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'), env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_confirmacion_acuerdo_confidencialidad([]);

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];

	$request = [
		//"subject" => "CONFIRMACIÓN DE SOLICITUD PROVEEDOR: ".$proveedor_razon_social_titulo. " - RUC " .$proveedor_ruc,
		"subject" => $renvio_titulo . "Gestion - Sistema Contratos - Confirmación de Solicitud de Acuerdo de Confidencialidad: CÓD - " . $sigla_correlativo . $codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try {
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if (isset($request["cc"])) {
			foreach ($request["cc"] as $cc) {
				$mail->addAddress($cc);
			}
		}

		if (isset($request["bcc"])) {
			foreach ($request["bcc"] as $bcc) {
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->send();
		//return true;

		if ($reenvio) {
			$result["http_code"] = 200;
			$result["status"] = "Datos obtenidos de gestion.";
			echo json_encode($result);
			exit();
		}
	} catch (Exception $e) {
		if ($reenvio) {
			$result["http_code"] = 400;
			$result["status"] = "Datos obtenidos de gestion.";
			$result["error"] = $mail->ErrorInfo;
			echo json_encode($result);
			exit();
		} else {
			$resultado = $mail->ErrorInfo;
			//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
			//return false;
			echo json_encode($resultado);
		}
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "verificar_beneficiarios_monto") {
	/*

	$id_beneficiarios = $_POST["id_beneficiarios"];
	$tipo_monto = $_POST["tipo_monto"];
	$monto = $_POST["monto"];

	$contador_array_ids = 0;

	$data = json_decode($id_beneficiarios);
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

	$sql = "SELECT 
	b.id, 
	b.nombre, 
	b.num_docu, 
	f.nombre AS forma_pago, 
	ba.nombre AS banco, 
	b.num_cuenta_bancaria, 
	b.num_cuenta_cci,
	b.tipo_monto_id,
	b.monto
	FROM cont_beneficiarios b
	INNER JOIN cont_forma_pago f ON b.forma_pago_id = f.id
	LEFT JOIN tbl_bancos ba ON b.banco_id = ba.id
	WHERE b.id IN(" . $ids . ")
	ORDER BY b.id ASC";
	
	$query = $mysqli->query($sql);
	$row_count = $query->num_rows;

	if ($row_count > 0) {
		$contador = 1;

		while ($row = $query->fetch_assoc()) {
			if ($row["tipo_monto_id"] == '1') {
				$monto_a_depositar = number_format($row["monto"], 2, '.', ',');
			} else if ($row["tipo_monto_id"] == '2') {
				$monto_a_depositar = $row["monto"] . '%';
			} else if ($row["tipo_monto_id"] == '3') {
				$monto_a_depositar = 'Totalidad de la renta';
			}

			$html .= '<td>' . $monto_a_depositar . '</td>';

			$contador++;
		}
	}

	if (condition) {
		$result["mensaje"] = 'El monto ingresado es mayor al monto de la renta ingresada';
	} else if() {
		$result["mensaje"] = 'No se puede registrar este nuevo beneficiario ya que existe un beneficiario que recibira la totalidad de la renta';
	} else if() {
		$result["mensaje"] = 'El beneficiario .. porcentaje';
	} else if() {
		$result["mensaje"] = 'Ya se regis beneficiario registrado, este tambien monto fijo';
	} else if() {
		$result["mensaje"] = 'La suma de los porcentajes es superior al 100%';
	} else if() {
		$result["mensaje"] = 'La suma de los montos fijos es superior al monto de la renta ingresada';
	} else if() {
		$result["mensaje"] = '';
	}

	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $html;
	*/
}

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_contrato_nuevo_guardar_nuevo_anexo") {
	$path = "/var/www/html/files_bucket/contratos/comprobantes_de_pago/";
	$usuario_id = $login ? $login['id'] : null;
	$created_at = date("Y-m-d H:i:s");
	//$programacion_id = $_POST["programacion_id"];
	$error = '';

	if (isset($_FILES['sec_nuevo_file_nuevo_anexo']) && $_FILES['sec_nuevo_file_nuevo_anexo']['error'] === UPLOAD_ERR_OK) {
		if (!is_dir($path)) mkdir($path, 0777, true);

		$filename = $_FILES['sec_nuevo_file_nuevo_anexo']['name'];
		$filenametem = $_FILES['sec_nuevo_file_nuevo_anexo']['tmp_name'];
		$filesize = $_FILES['sec_nuevo_file_nuevo_anexo']['size'];
		$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
		if ($filename != "") {
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
			if ($mysqli->error) {
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


if (isset($_POST["accion"]) && $_POST["accion"] === "sec_con_nuevo_guardar_nuevo_tipo_anexo_nuevo") {
	$anexo = $_POST["anexo"];
	$tipo_contrato_id = $_POST["tipo_contrato_id"];
	$query_insert = "INSERT INTO cont_tipo_archivos(nombre_tipo_archivo, tipo_contrato_id, status) VALUES ('" . $anexo . "',$tipo_contrato_id,1)";
	$mysqli->query($query_insert);
	$insert_id = mysqli_insert_id($mysqli);
	$error = '';
	if ($mysqli->error) {
		$error = $mysqli->error;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $insert_id;
	$result["error"] = $error;
}


if (isset($_POST["accion"]) && $_POST["accion"] === "llamar_funcion_send_email_solicitud_contrato_locales_detallado") {
	$contrato_id = $_POST["contrato_id"];
	send_email_solicitud_contrato_arrendamiento_detallado($contrato_id, true, true);
}


if (isset($_POST["accion"]) && $_POST["accion"] === "llamar_funcion_send_email_solicitud_de_arrendamiento") {
	$contrato_id = $_POST["contrato_id"];
	send_email_solicitud_contrato_arrendamiento($contrato_id);
	send_email_solicitud_contrato_arrendamiento_detallado($contrato_id, true, false);
}


function send_email_solicitud_contrato_arrendamiento_detallado($contrato_id, $enviar_respuesta, $reenvio)
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
	while ($sel = $sel_query->fetch_assoc()) {
		$observaciones = $sel["observaciones"];

		$sigla_correlativo = $sel['sigla_correlativo'];
		$codigo_correlativo = $sel['codigo_correlativo'];

		$body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
		$body .= '<tr>';
		$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Datos Generales</th>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Empresa Arrendataria</b></td>';
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
	while ($sel = $sel_query->fetch_assoc()) {

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
		t1.nombre AS tipo_pago_agua,
		i.monto_o_porcentaje_agua,
		i.num_suministro_luz,
		i.tipo_compromiso_pago_luz,
		t2.nombre AS tipo_pago_luz,
		i.monto_o_porcentaje_luz,
		i.tipo_compromiso_pago_arbitrios,
		ta.nombre AS tipo_pago_arbitrios,
		i.porcentaje_pago_arbitrios
	FROM cont_inmueble i
	INNER JOIN tbl_ubigeo ude ON SUBSTRING(i.ubigeo_id, 1, 2) = ude.cod_depa AND ude.cod_prov = '00' AND ude.cod_dist = '00'
	INNER JOIN tbl_ubigeo upr ON SUBSTRING(i.ubigeo_id, 1, 2) = upr.cod_depa AND SUBSTRING(i.ubigeo_id, 3, 2) = upr.cod_prov AND upr.cod_dist = '00'
	INNER JOIN tbl_ubigeo udi ON SUBSTRING(i.ubigeo_id, 1, 2) = udi.cod_depa AND SUBSTRING(i.ubigeo_id, 3, 2) = udi.cod_prov AND SUBSTRING(i.ubigeo_id, 5, 2) = udi.cod_dist
	INNER JOIN cont_tipo_pago_servicio t1 ON i.tipo_compromiso_pago_agua = t1.id
	INNER JOIN cont_tipo_pago_servicio t2 ON i.tipo_compromiso_pago_luz = t2.id
	INNER JOIN cont_tipo_pago_arbitrios ta ON i.tipo_compromiso_pago_arbitrios = ta.id
	WHERE i.contrato_id = " . $contrato_id . ";");
	while ($sel = $sel_query->fetch_assoc()) {

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
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;"><b>Área arrendada(m2)</b></td>';
		$body .= '<td>' . $sel["area_arrendada"] . ' m2' . '</td>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;"><b>N° Partida Registral</b></td>';
		$body .= '<td>' . $sel["num_partida_registral"] . '</td>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;"><b>Oficina Registral (Sede)</b></td>';
		$body .= '<td>' . $sel["oficina_registral"] . '</td>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;"><b>Servicio de Agua - N° de Suministro</b></td>';
		$body .= '<td>' . $sel["num_suministro_agua"] . '</td>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;"><b>Servicio de Agua - Compromiso de pago</b></td>';
		$body .= '<td>' . $sel["tipo_pago_agua"] . '</td>';
		$body .= '</tr>';

		if ($sel["tipo_compromiso_pago_agua"] == 1 || $sel["tipo_compromiso_pago_agua"] == 2 || $sel["tipo_compromiso_pago_agua"] == 6 || $sel["tipo_compromiso_pago_agua"] == 7) {
			$monto_o_porcentaje_agua = $sel["monto_o_porcentaje_agua"];

			if ($sel["tipo_compromiso_pago_agua"] == 1) {
				$compromiso_pago_agua = 'Porcentaje del recibo';
				$valor_compromiso_pago_agua = $monto_o_porcentaje_agua . '%';
			} else {
				$valor_compromiso_pago_agua = 'S/. ' . $monto_o_porcentaje_agua;

				if ($sel["tipo_compromiso_pago_agua"] == 2) {
					$compromiso_pago_agua = 'Monto Fijo';
				} else if ($sel["tipo_compromiso_pago_agua"] == 6) {
					$compromiso_pago_agua = 'Monto Base';
				} else if ($sel["tipo_compromiso_pago_agua"] == 7) {
					$compromiso_pago_agua = 'Monto a facturar';
				}
			}

			$compromiso_pago_agua = 'Servicio de Agua - ' . $compromiso_pago_agua;

			if (empty($monto_o_porcentaje_agua)) {
				$valor_compromiso_pago_agua = 'Sin asignar';
			}

			$body .= '<tr>';
			$body .= '<td style="background-color:#ffffdd;"><b>' . $compromiso_pago_agua . '</b></td>';
			$body .= '<td>' . $valor_compromiso_pago_agua . '</td>';
			$body .= '</tr>';
		}

		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;"><b>Servicio de Luz - N° de Suministro</b></td>';
		$body .= '<td>' . $sel["num_suministro_luz"] . '</td>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;"><b>Servicio de Luz - Compromiso de pago</b></td>';
		$body .= '<td>' . $sel["tipo_pago_luz"] . '</td>';
		$body .= '</tr>';

		if ($sel["tipo_compromiso_pago_luz"] == 1 || $sel["tipo_compromiso_pago_luz"] == 2 || $sel["tipo_compromiso_pago_luz"] == 6 || $sel["tipo_compromiso_pago_luz"] == 7) {
			$monto_o_porcentaje_luz = $sel["monto_o_porcentaje_luz"];

			if ($sel["tipo_compromiso_pago_luz"] == 1) {
				$compromiso_pago_luz = 'Porcentaje del recibo';
				$valor_compromiso_pago_luz = $monto_o_porcentaje_luz . '%';
			} else {
				$valor_compromiso_pago_luz = 'S/. ' . $monto_o_porcentaje_luz;

				if ($sel["tipo_compromiso_pago_luz"] == 2) {
					$compromiso_pago_luz = 'Monto Fijo';
				} else if ($sel["tipo_compromiso_pago_luz"] == 6) {
					$compromiso_pago_luz = 'Monto Base';
				} else if ($sel["tipo_compromiso_pago_luz"] == 7) {
					$compromiso_pago_luz = 'Monto a facturar';
				}
			}
			$compromiso_pago_luz = 'Servicio de Luz - ' . $compromiso_pago_luz;

			if (empty($monto_o_porcentaje_luz)) {
				$valor_compromiso_pago_luz = 'Sin asignar';
			}

			$body .= '<tr>';
			$body .= '<td style="background-color:#ffffdd;"><b>' . $compromiso_pago_luz . '</b></td>';
			$body .= '<td>' . $valor_compromiso_pago_luz . '</td>';
			$body .= '</tr>';
		}

		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;"><b>Arbitrios - Compromiso de pago</b></td>';
		$body .= '<td>' . $sel["tipo_pago_arbitrios"] . '</td>';
		$body .= '</tr>';

		if ($sel["tipo_compromiso_pago_arbitrios"] != 2) {
			$porcentaje_pago_arbitrios = $sel["porcentaje_pago_arbitrios"] . '%';

			$body .= '<tr>';
			$body .= '<td style="background-color:#ffffdd;"><b>Porcentaje del Pago de Arbitrios (%)</b></td>';
			$body .= '<td>' . $porcentaje_pago_arbitrios . '</td>';
			$body .= '</tr>';
		}

		$body .= '</table>';
	}

	$body .= '<br>';

	$sel_query = $mysqli->query("SELECT 
		c.condicion_economica_id,
		c.contrato_id,
		c.monto_renta,
		c.tipo_moneda_id,
		concat(m.nombre,' (', m.simbolo,')') AS moneda_contrato,
		m.simbolo AS simbolo_moneda,
		c.impuesto_a_la_renta_id,
		i.nombre AS impuesto_a_la_renta,
		c.numero_cuenta_detraccion,
		c.garantia_monto,
		c.tipo_adelanto_id,
		a.nombre AS tipo_adelanto,
		c.plazo_id,
		tp.nombre AS nombre_plazo,
		c.cant_meses_contrato,
		c.fecha_inicio,
		c.fecha_fin,
		c.periodo_gracia_id,
		p.nombre AS periodo_gracia,
		c.periodo_gracia_numero,
		c.periodo_gracia_inicio,
		c.periodo_gracia_fin,
		c.num_alerta_vencimiento,
		c.cargo_mantenimiento,
		c.fecha_suscripcion,
		c.status,
		c.user_created_id,
		c.created_at,
		c.user_updated_id,
		c.updated_at
	FROM cont_condicion_economica c
	INNER JOIN tbl_moneda m ON c.tipo_moneda_id = m.id 
	INNER JOIN cont_tipo_impuesto_a_la_renta i ON c.impuesto_a_la_renta_id = i.id
	INNER JOIN cont_tipo_adelanto a ON c.tipo_adelanto_id = a.id
	LEFT JOIN cont_tipo_periodo_de_gracia p ON c.periodo_gracia_id = p.id
	LEFT JOIN cont_tipo_plazo tp ON c.plazo_id = tp.id
	WHERE c.contrato_id = " . $contrato_id);
	while ($row = $sel_query->fetch_assoc()) {
		$simbolo_moneda = $row["simbolo_moneda"];
		$moneda_contrato = $row["moneda_contrato"];
		$monto_renta = $simbolo_moneda . ' ' . number_format($row["monto_renta"], 2, '.', ',');
		$impuesto_a_la_renta_id = $row["impuesto_a_la_renta_id"];
		$impuesto_a_la_renta = $row["impuesto_a_la_renta"];
		$numero_cuenta_detraccion = $row["numero_cuenta_detraccion"];
		$garantia_monto = $simbolo_moneda . ' ' . number_format($row["garantia_monto"], 2, '.', ',');
		$tipo_adelanto_id = $row["tipo_adelanto_id"];
		$tipo_adelanto = $row["tipo_adelanto"];

		$plazo_id = $row["plazo_id"];
		$nombre_plazo = $row["nombre_plazo"];
		$cant_meses_contrato = $row["cant_meses_contrato"];
		$fecha_inicio = $row["fecha_inicio"];
		$fecha_fin = $row["fecha_fin"];
		$fecha_suscripcion = $row["fecha_suscripcion"];


		$periodo_gracia_id = $row["periodo_gracia_id"];
		$periodo_gracia = trim($row["periodo_gracia"]);
		$periodo_gracia_numero = trim($row["periodo_gracia_numero"]);


		if ($plazo_id == 1) {
			if (empty($cant_meses_contrato)) {
				$cant_meses_contrato = 'Sin asignar';
			} else {
				$cant_meses_contrato = sec_contrato_nuevo_de_meses_a_anios_y_meses($cant_meses_contrato) . ' (' . $cant_meses_contrato . ' meses)';
			}

			if (empty($fecha_inicio)) {
				$contrato_inicio_fecha = 'Sin asignar';
			} else {
				$contrato_inicio_fecha = date("d/m/Y", strtotime($fecha_inicio));
			}

			if (empty($fecha_fin)) {
				$contrato_fin_fecha = 'Sin asignar';
			} else {
				$contrato_fin_fecha = date("d/m/Y", strtotime($fecha_fin));
			}

			if (empty($fecha_suscripcion)) {
				$contrato_fecha_suscripcion = 'Sin asignar';
			} else {
				$contrato_fecha_suscripcion = date("d/m/Y", strtotime($fecha_suscripcion));
			}
		} else {

			$cant_meses_contrato = 'Sin asignar';

			if (empty($fecha_inicio)) {
				$contrato_inicio_fecha = 'Sin asignar';
			} else {
				$contrato_inicio_fecha = date("d/m/Y", strtotime($fecha_inicio));
			}

			$contrato_fin_fecha = 'Sin asignar';

			if (empty($fecha_suscripcion)) {
				$contrato_fecha_suscripcion = 'Sin asignar';
			} else {
				$contrato_fecha_suscripcion = date("d/m/Y", strtotime($fecha_suscripcion));
			}
		}


		if ($periodo_gracia_id == '1') {
			$periodo_gracia_inicio = $row["periodo_gracia_inicio"];

			if (empty($periodo_gracia_inicio)) {
				$periodo_gracia_inicio_fecha = 'Sin asignar';
			} else {
				$periodo_gracia_inicio_fecha = date("d/m/Y", strtotime($periodo_gracia_inicio));
			}

			$periodo_gracia_fin = $row["periodo_gracia_fin"];

			if (empty($periodo_gracia_fin)) {
				$periodo_gracia_fin_fecha = 'Sin asignar';
			} else {
				$periodo_gracia_fin_fecha = date("d/m/Y", strtotime($periodo_gracia_fin));
			}
		}

		if (empty($periodo_gracia)) {
			$periodo_gracia = 'Sin asignar';
		}

		if (empty($periodo_gracia_numero)) {
			$periodo_gracia_numero = 'Sin asignar';
		} else {
			if ($periodo_gracia_numero == 1) {
				$periodo_gracia_numero .= ' día';
			} elseif ($periodo_gracia_numero > 1) {
				$periodo_gracia_numero .= ' días';
			}
		}
	}

	$body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
	$body .= '<tr>';
	$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Condiciones Económicas y Comerciales</th>';
	$body .= '</tr>';
	$body .= '<tr>';
	$body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Moneda del contrato</b></td>';
	$body .= '<td>' . $moneda_contrato . '</td>';
	$body .= '</tr>';
	$body .= '<tr>';
	$body .= '<td style="background-color:#ffffdd;"><b>Monto de Renta Pactada</b></td>';
	$body .= '<td>' . $monto_renta . '</td>';
	$body .= '</tr>';
	$body .= '<tr>';
	$body .= '<td style="background-color:#ffffdd;"><b>Impuesto a la renta</b></td>';
	$body .= '<td>' . $impuesto_a_la_renta . '</td>';
	$body .= '</tr>';

	if ((int) $impuesto_a_la_renta_id == 4) {

		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;"><b>N°. de cuenta de detracción (Banco de la Nación)</b></td>';
		$body .= '<td>' . $numero_cuenta_detraccion . '</td>';
		$body .= '</tr>';
	}

	$body .= '<tr>';
	$body .= '<td style="background-color:#ffffdd;"><b>Monto de la garantías</b></td>';
	$body .= '<td>' . $garantia_monto . '</td>';
	$body .= '</tr>';
	$body .= '<tr>';
	$body .= '<td style="background-color:#ffffdd;"><b>Adelantos</b></td>';
	$body .= '<td>' . $tipo_adelanto . '</td>';
	$body .= '</tr>';

	if ($tipo_adelanto_id == '1') {
		$sql = "SELECT 
		id, 
		num_periodo
		FROM cont_adelantos ";
		$sql .= " WHERE contrato_id =" . $contrato_id;
		$sql .= " ORDER BY id ASC";

		$query = $mysqli->query($sql);

		$row_count = $query->num_rows;

		if ($row_count > 0) {
			$contador = 1;
			while ($row = $query->fetch_assoc()) {
				$num_periodo = $row["num_periodo"];

				if ($num_periodo == 'x') {
					$mes_adelanto = 'Antepenúltimo';
				} else if ($num_periodo == 'y') {
					$mes_adelanto = 'Penúltimo';
				} else if ($num_periodo == 'z') {
					$mes_adelanto = 'Último';
				} else {
					$mes_adelanto = $num_periodo;
				}

				$mes_adelanto = $mes_adelanto . ' mes';

				if ($contador == 1) {
					$meses_de_adelanto = $mes_adelanto;
				} else {
					$meses_de_adelanto .= ', ' . $mes_adelanto;
				}

				$contador++;
			}
		}

		if ($row_count == 1) {
			$cant_meses_adelando = 'mes';
		} else {
			$cant_meses_adelando = 'meses';
		}

		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;"><b>Meses de adelanto</b></td>';
		$body .= '<td>' . $row_count . ' ' . $cant_meses_adelando . '(' . $meses_de_adelanto . ')' . '</td>';
		$body .= '</tr>';
	}

	$body .= '</table>';

	$body .= '<br>';

	$body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
	$body .= '<tr>';
	$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Vigencia</th>';
	$body .= '</tr>';
	$body .= '<tr>';
	$body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Vigencia</b></td>';
	$body .= '<td>' . $nombre_plazo . '</td>';
	$body .= '</tr>';

	if ($plazo_id == 1) {
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Vigencia del Contrato</b></td>';
		$body .= '<td>' . $cant_meses_contrato . '</td>';
		$body .= '</tr>';
	}
	$body .= '<tr>';
	$body .= '<td style="background-color:#ffffdd;"><b>Contrato - Fecha de Inicio</b></td>';
	$body .= '<td>' . $contrato_inicio_fecha . '</td>';
	$body .= '</tr>';

	if ($plazo_id == 1) {
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;"><b>Contrato - Fecha de Fin</b></td>';
		$body .= '<td>' . $contrato_fin_fecha . '</td>';
		$body .= '</tr>';
	}

	$body .= '<tr>';
	$body .= '<td style="background-color:#ffffdd;"><b>Periodo de gracia</b></td>';
	$body .= '<td>' . $periodo_gracia . '</td>';
	$body .= '</tr>';

	if ($periodo_gracia_id == '1') {

		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;"><b>Periodo de gracia - Número de días</b></td>';
		$body .= '<td>' . $periodo_gracia_numero . '</td>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;"><b>Periodo de gracia - Fecha de Inicio</b></td>';
		$body .= '<td>' . $periodo_gracia_inicio_fecha . '</td>';
		$body .= '</tr>';
		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;"><b>Periodo de gracia - Fecha de Fin</b></td>';
		$body .= '<td>' . $periodo_gracia_fin_fecha . '</td>';
		$body .= '</tr>';
	}

	$body .= '</table>';

	$body .= '<br>';

	$body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
	$body .= '<tr>';
	$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Incrementos</th>';
	$body .= '</tr>';

	$num_incremento_contador = 0;
	$sel_query = $mysqli->query("SELECT i.id, i.valor, tp.nombre AS tipo_valor, i.tipo_continuidad_id, tc.nombre AS tipo_continuidad, i.a_partir_del_año
	FROM cont_incrementos i
	INNER JOIN cont_tipo_pago_incrementos tp ON i.tipo_valor_id = tp.id
	INNER JOIN cont_tipo_continuidad_pago tc ON i.tipo_continuidad_id = tc.id
	WHERE i.contrato_id IN (" . $contrato_id . ")");
	$row_count_incrementos = $sel_query->num_rows;

	if ($row_count_incrementos > 0) {
		while ($sel = $sel_query->fetch_assoc()) {
			$num_incremento_contador++;

			$a_partir_del_año = $sel["a_partir_del_año"] . ' año';
			if ($sel["tipo_continuidad_id"] == 3) {
				$a_partir_del_año = '';
			}

			$body .= '<tr>';
			$body .= '<td style="background-color:#ffffdd; width: 300px;"><b>N° 0' . $num_incremento_contador . '</b></td>';
			$body .= '<td>' . $sel["valor"] . ' ' . $sel["tipo_valor"] . ' ' . $sel["tipo_continuidad"] . ' ' . $a_partir_del_año . '</td>';
			$body .= '</tr>';
		}
	} else {

		$body .= '<tr>';
		$body .= '<td colspan="2">El presente contrato no posee incrementos</td>';
		$body .= '</tr>';
	}

	$body .= '</table>';

	$body .= '<br>';








	$body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
	$body .= '<tr>';
	$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Inflaciones</th>';
	$body .= '</tr>';


	$num_inflacion = 0;
	$sel_query = $mysqli->query("SELECT i.id, DATE_FORMAT(i.fecha, '%d-%m-%Y') as fecha, tp.nombre  AS tipo_periodicidad, i.numero, p.nombre as tipo_anio_mes, m.sigla AS moneda, i.porcentaje_anadido, i.tope_inflacion, i.minimo_inflacion
	FROM cont_inflaciones AS i
	INNER JOIN cont_tipo_periodicidad AS tp ON tp.id = i.tipo_periodicidad_id
	LEFT JOIN tbl_moneda AS m ON m.id = i.moneda_id
	LEFT JOIN cont_periodo as p ON p.id = i.tipo_anio_mes
	WHERE 
		i.contrato_id = $contrato_id
		AND i.status = 1
	ORDER BY i.id ASC");
	$row_count_inflacion = $sel_query->num_rows;

	if ($row_count_inflacion > 0) {
		while ($sel = $sel_query->fetch_assoc()) {
			$num_inflacion++;

			$fecha = $sel['fecha'];
			$periocidad = $sel['tipo_periodicidad'] . ' ' . $sel['numero'] . ' ' . $sel['tipo_anio_mes'];
			$moneda = $sel['moneda'];
			$porcentaje_anadido = $sel['porcentaje_anadido'];
			$tope_inflacion = $sel['tope_inflacion'];
			$minimo_inflacion = $sel['minimo_inflacion'];

			$body .= '<tr>';
			$body .= '<td colspan="2" style="background-color:#ffffdd;"><b>N° ' . $num_inflacion . '</b></td>';
			$body .= '</tr>';

			if (!empty($fecha)) {
				$body .= '<tr>';
				$body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Fecha de Ajuste</b></td>';
				$body .= '<td>' . $fecha . '</td>';
				$body .= '</tr>';
			}
			$body .= '<tr>';
			$body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Periodicidad</b></td>';
			$body .= '<td>' . $periocidad . '</td>';
			$body .= '</tr>';

			$body .= '<tr>';
			$body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Porcentaje Añadido</b></td>';
			$body .= '<td>' . $porcentaje_anadido . '</td>';
			$body .= '</tr>';

			$body .= '<tr>';
			$body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Tope de Inflación</b></td>';
			$body .= '<td>' . $tope_inflacion . '</td>';
			$body .= '</tr>';

			$body .= '<tr>';
			$body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Minimo de Inflación</b></td>';
			$body .= '<td>' . $minimo_inflacion . '</td>';
			$body .= '</tr>';
		}
	} else {

		$body .= '<tr>';
		$body .= '<td colspan="2">El presente contrato no posee inflaciones</td>';
		$body .= '</tr>';
	}

	$body .= '</table>';

	$body .= '<br>';



	$body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
	$body .= '<tr>';
	$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Cuota Extraordinaria</th>';
	$body .= '</tr>';


	$num_cuota_extraordinaria = 0;
	$sel_query = $mysqli->query("SELECT c.id, m.nombre as mes, c.multiplicador, c.fecha
	FROM cont_cuotas_extraordinarias AS c
	INNER JOIN tbl_meses AS m ON m.id = c.mes
		WHERE 
			c.contrato_id = $contrato_id
			AND c.status = 1
		ORDER BY c.id ASC");
	$row_count_cuota = $sel_query->num_rows;

	if ($row_count_cuota > 0) {
		while ($sel = $sel_query->fetch_assoc()) {
			$num_cuota_extraordinaria++;

			$mes = $sel['mes'];
			$multiplicador = $sel['multiplicador'];
			$fecha = $sel['fecha'];

			$body .= '<tr>';
			$body .= '<td colspan="2" style="background-color:#ffffdd;"><b>N° ' . $num_cuota_extraordinaria . '</b></td>';
			$body .= '</tr>';

			if (!empty($fecha)) {
				$body .= '<tr>';
				$body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Fecha de Ajuste</b></td>';
				$body .= '<td>' . $fecha . '</td>';
				$body .= '</tr>';
			}
			$body .= '<tr>';
			$body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Mes</b></td>';
			$body .= '<td>' . $mes . '</td>';
			$body .= '</tr>';

			$body .= '<tr>';
			$body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Multiplicador</b></td>';
			$body .= '<td>' . $multiplicador . '</td>';
			$body .= '</tr>';
		}
	} else {

		$body .= '<tr>';
		$body .= '<td colspan="2">El presente contrato no posee cuotas extraordinarias</td>';
		$body .= '</tr>';
	}

	$body .= '</table>';

	$body .= '<br>';





	$sel_query = $mysqli->query("
	SELECT 
		b.id,
		tp.nombre AS tipo_persona,
		td.nombre AS tipo_docu_identidad,
		b.num_docu,
		b.nombre,
		b.forma_pago_id,
		f.nombre AS forma_pago,
		ba.nombre AS banco,
		b.num_cuenta_bancaria,
		b.num_cuenta_cci,
		b.tipo_monto_id,
		tm.nombre AS tipo_monto_a_depositar,
		b.monto
	FROM
		cont_beneficiarios b
			LEFT JOIN
		cont_tipo_persona tp ON b.tipo_persona_id = tp.id
			LEFT JOIN
		cont_tipo_docu_identidad td ON b.tipo_docu_identidad_id = td.id
			INNER JOIN
		cont_forma_pago f ON b.forma_pago_id = f.id
			LEFT JOIN
		tbl_bancos ba ON b.banco_id = ba.id
			INNER JOIN
		cont_tipo_monto_a_depositar tm ON b.tipo_monto_id = tm.id
	WHERE
		b.status = 1 AND contrato_id IN (" . $contrato_id . ")
	");
	while ($sel = $sel_query->fetch_assoc()) {
		if ($sel["tipo_monto_id"] == 3) {
			$monto_beneficiario = $monto_renta;
		} else {
			if ($sel["tipo_monto_id"] == 2) {
				$monto_beneficiario = $sel["monto"] . '%';
			} else {
				$monto_beneficiario = $simbolo_moneda . ' ' . number_format($sel["monto"], 2, '.', ',');
			}
		}

		$body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
		$body .= '<tr>';
		$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Datos del Beneficiario</th>';
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
		$body .= '<td style="background-color:#ffffdd;"><b>Tipo de forma de pago</b></td>';
		$body .= '<td>' . $sel["forma_pago"] . '</td>';
		$body .= '</tr>';

		if ($sel["forma_pago_id"] != '3') {

			$body .= '<tr>';
			$body .= '<td style="background-color:#ffffdd;"><b>Nombre del Banco</b></td>';
			$body .= '<td>' . $sel["banco"] . '</td>';
			$body .= '</tr>';
			$body .= '<tr>';
			$body .= '<td style="background-color:#ffffdd;"><b>N° de la cuenta bancaria</b></td>';
			$body .= '<td>' . $sel["num_cuenta_bancaria"] . '</td>';
			$body .= '</tr>';
			$body .= '<tr>';
			$body .= '<td style="background-color:#ffffdd;"><b>N° de CCI bancario</b></td>';
			$body .= '<td>' . $sel["num_cuenta_cci"] . '</td>';
			$body .= '</tr>';
		}

		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;"><b>Monto a depositar</b></td>';
		$body .= '<td>' . $sel["tipo_monto_a_depositar"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color:#ffffdd;"><b>Monto</b></td>';
		$body .= '<td>' . $monto_beneficiario . '</td>';
		$body .= '</tr>';
		$body .= '</table>';
	}

	$body .= '<br>';

	$body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
	$body .= '<tr>';
	$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Fecha de suscripción del contrato</th>';
	$body .= '</tr>';
	$body .= '<tr>';
	$body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Fecha de suscripción del contrato</b></td>';
	$body .= '<td>' . $contrato_fecha_suscripcion . '</td>';
	$body .= '</tr>';
	$body .= '</table>';

	$body .= '<br>';

	$body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
	$body .= '<tr>';
	$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Observaciones</th>';
	$body .= '</tr>';
	$body .= '<tr>';
	$body .= '<tr>';
	$body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Observaciones</b></td>';
	$body .= '<td>' . $observaciones . '</td>';
	$body .= '</tr>';
	$body .= '</table>';

	$pre_asunto = '';

	if ($reenvio) {
		$pre_asunto = 'Reenvío - ';

		$usuario_id = $login ? $login['id'] : null;
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
	$correos = new Correos(env('SEND_EMAIL'), env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_solicitud_contrato_arrendamiento_detallado([]);

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];

	$request = [
		"subject" => $pre_asunto . "Gestion - Sistema Contratos - Nueva Solicitud de Arrendamiento: Código - " . $sigla_correlativo . $codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try {
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if (isset($request["cc"])) {
			foreach ($request["cc"] as $cc) {
				$mail->addAddress($cc);
			}
		}

		if (isset($request["bcc"])) {
			foreach ($request["bcc"] as $bcc) {
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();

		if ($enviar_respuesta) {
			$result["http_code"] = 200;
			$result["status"] = "Datos obtenidos de gestion.";
			echo json_encode($result);
			exit();
		}
	} catch (Exception $e) {
		if ($enviar_respuesta) {
			$result["http_code"] = 400;
			$result["status"] = "Datos obtenidos de gestion.";
			$result["error"] = "Error al enviar el email. " . $mail->ErrorInfo;
			echo json_encode($result);
			exit();
		}
	}
}




function sec_contrato_nuevo_de_meses_a_anios_y_meses($meses)
{
	if ($meses < 12) {
		$anio_y_meses = $meses . ' meses';
	} else {
		$anio = intval($meses / 12);
		$meses_restantes = $meses % 12;

		if ($anio == 0) {
			$anio = '';
		} else if ($anio == 1) {
			$anio = $anio . ' año';
		} else if ($anio > 1) {
			$anio = $anio . ' años';
		}

		if ($meses_restantes == 0) {
			$meses_restantes = '';
		} else if ($meses_restantes == 1) {
			$meses_restantes = ' y ' . $meses_restantes . ' mes';
		} else if ($meses_restantes > 1) {
			$meses_restantes = ' y ' . $meses_restantes . ' meses';
		}

		return $anio . $meses_restantes;
	}
}


// INICIO CONTRAPRESTACIÓN
if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_contraprestacion") {
	include("function_replace_invalid_caracters_contratos.php");

	$usuario_id = $login ? $login['id'] : null;
	$created_at = date("Y-m-d H:i:s");

	$moneda_id = $_POST["moneda_id"];
	$forma_pago = 0;
	$forma_pago_detallado = "'" . replace_invalid_caracters(trim($_POST["forma_pago_detallado"])) . "'";
	$tipo_comprobante = $_POST["tipo_comprobante"];
	$plazo_pago = "'" . replace_invalid_caracters(trim($_POST["plazo_pago"])) . "'";

	if (empty($_POST["subtotal"])) {
		$subtotal = 0;
	} else {
		$subtotal = str_replace(",", "", $_POST["subtotal"]);
	}

	if (empty($_POST["igv"])) {
		$igv = 0;
	} else {
		$igv = str_replace(",", "", $_POST["igv"]);
	}

	$monto = $subtotal + $igv;

	$query_insert = "INSERT INTO cont_contraprestacion
	(
	moneda_id,
	forma_pago_id,
	forma_pago_detallado,
	tipo_comprobante_id,
	plazo_pago,
	subtotal,
	igv,
	monto,
	user_created_id,
	created_at)
	VALUES
	(
	" . $moneda_id . ",
	" . $forma_pago . ",
	" . $forma_pago_detallado . ",
	" . $tipo_comprobante . ",
	" . $plazo_pago . ",
	" . $subtotal . ",
	" . $igv . ",
	" . $monto . ",
	" . $usuario_id . ",
	'" . $created_at . "')";

	$mysqli->query($query_insert);
	$insert_id = mysqli_insert_id($mysqli);
	$error = '';
	if ($mysqli->error) {
		$error = $mysqli->error;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $insert_id;
	$result["error"] = $error;
}


if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_cambios_contraprestacion") {
	include("function_replace_invalid_caracters_contratos.php");

	$usuario_id = $login ? $login['id'] : null;
	$created_at = date("Y-m-d H:i:s");

	$moneda_id = $_POST["moneda_id"];
	$forma_pago_detallado = "'" . replace_invalid_caracters(trim($_POST["forma_pago_detallado"])) . "'";
	$tipo_comprobante = $_POST["tipo_comprobante"];
	$plazo_pago = "'" . replace_invalid_caracters(trim($_POST["plazo_pago"])) . "'";

	if (empty($_POST["subtotal"])) {
		$subtotal = "NULL";
	} else {
		$subtotal = str_replace(",", "", $_POST["subtotal"]);
	}

	if (empty($_POST["igv"])) {
		$igv = "NULL";
	} else {
		$igv = str_replace(",", "", $_POST["igv"]);
	}

	if (empty($_POST["monto"])) {
		$monto = "NULL";
	} else {
		$monto = str_replace(",", "", $_POST["monto"]);
	}

	$query_update = "
	UPDATE cont_contraprestacion
	SET 
		moneda_id = " . $moneda_id . ",
		forma_pago_detallado = " . $forma_pago_detallado . ",
		tipo_comprobante_id = " . $tipo_comprobante . ",
		plazo_pago = " . $plazo_pago . ",
		subtotal = " . $subtotal . ",
		igv = " . $igv . ",
		monto = " . $monto . ",
		user_updated_id = " . $usuario_id . ",
		updated_at = '" . $created_at . "'
	WHERE id = " . $_POST["contraprestacion_id_para_cambios"] . "
	";
	$mysqli->query($query_update);

	$error = '';
	if ($mysqli->error) {
		$error = $mysqli->error;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = 'ok';
	$result["error"] = $error;
}


if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_contraprestacion") {

	$contraprestacion_id = $_POST["contraprestacion_id"];

	$query = "
	SELECT
		id,
		moneda_id,
		forma_pago_detallado,
		tipo_comprobante_id,
		plazo_pago,
		subtotal,
		igv,
		monto
	FROM 
		cont_contraprestacion
	WHERE 
		id = $contraprestacion_id";
	$list_query = $mysqli->query($query);
	$list = array();

	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list) == 0) {
		$result["http_code"] = 400;
		$result["result"] = "La contraprestacion no existe.";
	} elseif (count($list) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $list;
	} else {
		$result["http_code"] = 400;
		$result["result"] = "La contraprestacion no existe.";
	}
}


if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_contraprestaciones") {

	$contraprestacion_ids = $_POST["contraprestacion_id"];
	$contador_array_ids = 0;

	$data = json_decode($contraprestacion_ids);
	$ids = '';

	foreach ($data as $value) {
		if ($contador_array_ids > 0) {
			$ids .= ',';
		}
		$ids .= $value;
		$contador_array_ids++;
	}

	if ($contador_array_ids == 0) {
		$ids = 0;
	}

	$html = '<table class="table table-bordered table-hover no-mb" style="font-size:11px; margin-top: 10px;">';
	$html .= '<thead>';

	$html .= '<tr>';

	$html .= '<th>#</th>';
	$html .= '<th>Tipo de Moneda</th>';
	$html .= '<th>Subtotal</th>';
	$html .= '<th>IGV</th>';
	$html .= '<th>Monto Bruto</th>';
	$html .= '<th>Forma de Pago</th>';
	$html .= '<th>Tipo de Comprobante a Emitir</th>';
	$html .= '<th>Plazo de Pago</th>';
	$html .= '<th>Opciones</th>';

	$html .= '</tr>';

	$html .= '</thead>';
	$html .= '<tbody>';

	$sql = "
	SELECT 
		c.id,
		m.nombre AS moneda,
		c.forma_pago_detallado,
		t.nombre AS tipo_de_comprobante,
		c.plazo_pago,
		c.subtotal,
		c.igv,
		c.monto
	FROM 
		cont_contraprestacion c
	    INNER JOIN tbl_moneda m ON c.moneda_id = m.id
	    INNER JOIN cont_tipo_comprobante t ON c.tipo_comprobante_id = t.id
	WHERE 
		c.id IN(" . $ids . ")
	ORDER BY c.id ASC";

	$query = $mysqli->query($sql);
	$row_count = $query->num_rows;

	if ($row_count == 0) {
		$html .= '<tr>';
		$html .= '<td colspan="9" align="center">Agrege la contraprestación del contrato</td>';
		$html .= '</tr>';
	} else if ($row_count > 0) {
		$contador = 1;

		while ($row = $query->fetch_assoc()) {
			$html .= '<tr>';

			$html .= '<td>' . $contador . '</td>';
			$html .= '<td>' . $row["moneda"] . '</td>';
			$html .= '<td>' . number_format($row["subtotal"], 2, '.', ',') . '</td>';
			$html .= '<td>' . number_format($row["igv"], 2, '.', ',') . '</td>';
			$html .= '<td>' . number_format($row["monto"], 2, '.', ',') . '</td>';
			$html .= '<td>' . $row["forma_pago_detallado"] . '</td>';
			$html .= '<td>' . $row["tipo_de_comprobante"] . '</td>';
			$html .= '<td>' . $row["plazo_pago"] . '</td>';


			$html .= '<td>';
			$html .= '<a class="btn btn-success btn-xs" data-toggle="tooltip" data-placement="top" title="Editar" onclick="sec_contrato_nuevo_editar_contraprestacion(' . $row["id"] . ')">';
			$html .= '<i class="fa fa-edit"></i>';
			$html .= '</a>';
			$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_contrato_nuevo_eliminar_contraprestacion(' . $row["id"] . ')">';
			$html .= '<i class="fa fa-trash"></i>';
			$html .= '</a>';
			$html .= '</td>';

			$html .= '</tr>';

			$contador++;
		}
	}

	$html .= '</tbody>';
	$html .= '</table>';

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $html;
}
// FIN CONTRAPRESTACIÓN






if (isset($_POST["accion"]) && $_POST["accion"] === "calcular_monto_a_pagar_segun_impuesto_a_la_renta") {

	$tipo_moneda_id = $_POST["tipo_moneda_renta_pactada"];
	$monto_renta_sin_formato = str_replace(",", "", $_POST["monto_renta"]);
	$impuesto_a_la_renta_id = $_POST["impuesto_a_la_renta_id"];
	$carta_de_instruccion_id = $_POST["impuesto_a_la_renta_carta_de_instruccion_id"];

	$sql_obtener_moneda = "
	SELECT 
		nombre,
		simbolo
	FROM 
		tbl_moneda
	WHERE 
		id = $tipo_moneda_id
	";

	$query = $mysqli->query($sql_obtener_moneda);
	$row_count = $query->num_rows;

	if ($mysqli->error) {
		$result["error"] = $mysqli->error;
	}

	if ($row_count == 0) {
		$result["error"] = 'Error al consultar la moneda';
	} else if ($row_count > 0) {
		$row = $query->fetch_assoc();
		$moneda_contrato = $row["nombre"];
		$simbolo_moneda = $row["simbolo"];
	}

	$factor = 1.05265;
	$renta_bruta = 0;
	$renta_neta = 0;
	$impuesto_a_la_renta = 0;
	$ir_detalle = array();

	if ($impuesto_a_la_renta_id == 1) {
		$impuesto_a_la_renta = round($monto_renta_sin_formato * 0.05);
		$renta_bruta = $monto_renta_sin_formato;

		if ($carta_de_instruccion_id == 1) {
			$renta_neta = $monto_renta_sin_formato - $impuesto_a_la_renta;
			$quien_paga = 'AT';
		} elseif ($carta_de_instruccion_id == 2) {
			$renta_neta = $monto_renta_sin_formato;
			$quien_paga = 'Arrendador';
		}

		$detalle = 'AT deposita la renta (' . $simbolo_moneda . ' ' . number_format($renta_neta, 2, '.', ',') . ' ' . $moneda_contrato . ') al Arrendador. El ' . $quien_paga . ' realiza el pago del impuesto a la renta (' . $simbolo_moneda . ' ' . number_format($impuesto_a_la_renta, 0, '.', ',') . ' ' . $moneda_contrato . ') a SUNAT.';
	} elseif ($impuesto_a_la_renta_id == 2) {
		$impuesto_a_la_renta = round(($monto_renta_sin_formato * $factor) - $monto_renta_sin_formato);
		$renta_bruta = $monto_renta_sin_formato + round($impuesto_a_la_renta);
		$renta_neta = $monto_renta_sin_formato;

		if ($carta_de_instruccion_id == 1) {
			$renta_neta = $monto_renta_sin_formato;
			$quien_paga = 'AT';
			$detalle = 'AT deposita renta (' . $simbolo_moneda . ' ' . number_format($monto_renta_sin_formato, 2, '.', ',') . ' ' . $moneda_contrato . ') al Arrendador. AT realiza el pago del impuesto a la renta (' . $simbolo_moneda . ' ' . number_format($impuesto_a_la_renta, 0, '.', ',') . ' ' . $moneda_contrato . ') a SUNAT.';
		} elseif ($carta_de_instruccion_id == 2) {
			$renta_neta = $monto_renta_sin_formato + $impuesto_a_la_renta;
			$quien_paga = 'Arrendador';
			$detalle = 'AT deposita ' . $simbolo_moneda . ' ' . number_format($renta_neta, 2, '.', ',') . ' ' . $moneda_contrato . ' al Arrendador. El Arrendador realiza el pago del impuesto a la renta (' . $simbolo_moneda . ' ' . number_format($impuesto_a_la_renta, 0, '.', ',') . ' ' . $moneda_contrato . ') a SUNAT.';
		}
	}

	$ir_detalle["impuesto_a_la_renta"] = $simbolo_moneda . ' ' . number_format($impuesto_a_la_renta, 0, '.', ',') . ' ' . $moneda_contrato;
	$ir_detalle["renta_bruta"] = $simbolo_moneda . ' ' . number_format($renta_bruta, 2, '.', ',') . ' ' . $moneda_contrato;
	$ir_detalle["renta_neta"] = $simbolo_moneda . ' ' . number_format($renta_neta, 2, '.', ',') . ' ' . $moneda_contrato;
	$ir_detalle["detalle"] = $detalle;

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = json_encode($ir_detalle);
}


if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_estado_contrato") {
	$usuario_id = $login ? $login['id'] : null;
	$created_at = date("Y-m-d H:i:s");

	$estado = $_POST["estado"];
	$id_contrato = $_POST["id_contrato"];


	$query_update = "
	UPDATE tbl_contratos
	SET 
		estado = " . $estado . ",
	WHERE id = " . $id_contrato . "
	";
	$mysqli->query($query_update);

	$error = '';
	if ($mysqli->error) {
		$error = $mysqli->error;
	}

	$result["http_code"] = 200;
	$result["status"] = "Se han guardado correctamente el estado.";
	$result["result"] = 'ok';
	$result["error"] = $error;
}

function sec_contrato_detalle_solicitud_de_meses_a_anios_y_meses($meses)
{
	if ($meses < 12) {
		$anio_y_meses = $meses . ' meses';
	} else {
		$anio = intval($meses / 12);
		$meses_restantes = $meses % 12;

		if ($anio == 0) {
			$anio = '';
		} else if ($anio == 1) {
			$anio = $anio . ' año';
		} else if ($anio > 1) {
			$anio = $anio . ' años';
		}

		if ($meses_restantes == 0) {
			$meses_restantes = '';
		} else if ($meses_restantes == 1) {
			$meses_restantes = ' y ' . $meses_restantes . ' mes';
		} else if ($meses_restantes > 1) {
			$meses_restantes = ' y ' . $meses_restantes . ' meses';
		}

		return $anio . $meses_restantes;
	}
}





if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_adenda_detalle_nuevos_registros") {

	$usuario_id = $login ? $login['id'] : null;
	$created_at = date("Y-m-d H:i:s");
	$insert_id = '';
	$result = array();

	if ($_POST['tabla'] == "representante_legal") {
		$contrato_id = $_POST['contrato_id'];
		$dniRepresentante = $_POST['dniRepresentante'];
		$nombreRepresentante = $_POST['nombreRepresentante'];
		$banco = $_POST['banco'];
		$nro_cuenta = $_POST['nro_cuenta'];
		$nro_cci = $_POST['nro_cci'];
		$nro_cuenta_detraccion = '';

		$path = "/var/www/html/files_bucket/contratos/solicitudes/proveedores/";
		// INICIO DE CARGAR ARCHIVOS DNI NUEVO REPRESENTANTE LEGAL
		$filename = $_FILES['sec_con_ap_prov_file_dni_nuevo_rl']['name'];
		$filenametem = $_FILES['sec_con_ap_prov_file_dni_nuevo_rl']['tmp_name'];
		$filesize = $_FILES['sec_con_ap_prov_file_dni_nuevo_rl']['size'];
		$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
		$img_id_insert_dni = 0;
		if ($filename != "") {
			$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
			$nombre_archivo = $contrato_id . "_DNI" . date('YmdHis') . "." . $fileExt;
			move_uploaded_file($filenametem, $path . $nombre_archivo);
			$comando = " INSERT INTO cont_archivos (
							contrato_id,
							tipo_archivo_id,
							nombre,
							extension,
							size,
							ruta,
							user_created_id,
							created_at)
						VALUES(
							'0',
							3,
							'" . $nombre_archivo . "',
							'" . $fileExt . "',
							'" . $filesize . "',
							'" . $path . "',
							" . $usuario_id . ",
							'" . $created_at . "'
							)";
			$mysqli->query($comando);
			$img_id_insert_dni = mysqli_insert_id($mysqli);
		}
		// FIN DE CARGAR ARCHIVOS DNI NUEVO REPRESENTANTE LEGAL

		// INICIO DE CARGAR ARCHIVOS VIGENCIA NUEVO REPRESENTANTE LEGAL
		$filename = $_FILES['sec_con_ap_prov_file_vigencia_nuevo_rl']['name'];
		$filenametem = $_FILES['sec_con_ap_prov_file_vigencia_nuevo_rl']['tmp_name'];
		$filesize = $_FILES['sec_con_ap_prov_file_vigencia_nuevo_rl']['size'];
		$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
		$img_id_insert_vigencia = 0;
		if ($filename != "") {
			$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
			$nombre_archivo = $contrato_id . "_VIG" . date('YmdHis') . "." . $fileExt;
			move_uploaded_file($filenametem, $path . $nombre_archivo);
			$comando = " INSERT INTO cont_archivos (
							contrato_id,
							tipo_archivo_id,
							nombre,
							extension,
							size,
							ruta,
							user_created_id,
							created_at)
						VALUES(
							'0',
							2,
							'" . $nombre_archivo . "',
							'" . $fileExt . "',
							'" . $filesize . "',
							'" . $path . "',
							" . $usuario_id . ",
							'" . $created_at . "'
							)";
			$mysqli->query($comando);
			$img_id_insert_vigencia = mysqli_insert_id($mysqli);
		}
		// FIN DE CARGAR ARCHIVOS VIGENCIA NUEVO REPRESENTANTE LEGAL

		$query_insert_repr =
			"INSERT INTO cont_representantes_legales (
			contrato_id,
			dni_representante, 
			nombre_representante, 
			nro_cuenta_detraccion, 
			id_banco, 
			nro_cuenta, 
			nro_cci, 
			vigencia_archivo_id, 
			dni_archivo_id, 
			id_user_created, 
			created_at
		) VALUES (
			0,
			'" . $dniRepresentante . "', '"
			. $nombreRepresentante . "', '"
			. $nro_cuenta_detraccion . "', "
			. $banco . ", '"
			. $nro_cuenta . "', '"
			. $nro_cci . "', "
			. $img_id_insert_vigencia . ", "
			. $img_id_insert_dni . ", "
			. $usuario_id . ", now())";

		$mysqli->query($query_insert_repr);
		$insert_id = mysqli_insert_id($mysqli);
		$error = '';
		if ($mysqli->error) {
			$error .= $error = $mysqli->error;
			$result["http_code"] = 404;
			$result["status"] = "A ocurrido un error al registrar el propietario.";
			$result["result"] = 0;
			$result["error"] = $error;
			echo json_encode($result);
			exit();
		}


		$usuario_id = $login ? $login['id'] : null;
		$created_at = date("Y-m-d H:i:s");
		$valor_original = "";
		$tipo_valor = 'registro';

		$id_del_registro = 0;

		$valor_varchar = "NULL";
		$valor_int = $insert_id;
		$valor_date = "NULL";
		$valor_decimal = "NULL";
		$valor_select_option = "NULL";
		$valor_id_tabla = "NULL";

		$query_insert = " INSERT INTO cont_adendas_detalle
		(
		nombre_tabla,
		valor_original,
		nombre_campo,
		nombre_menu_usuario,
		nombre_campo_usuario,
		tipo_valor,
		valor_varchar,
		valor_int,
		valor_date,
		valor_decimal,
		valor_select_option,
		valor_id_tabla,
		id_del_registro_a_modificar,
		user_created_id,
		created_at)
		VALUES
		(
		'cont_representantes_legales',
		'" . $valor_original . "',
		'contrato_id',
		'Representante Legal',
		'Nuevo Representante Legal',
		'" . $tipo_valor . "',
		'" . $valor_varchar . "',
		" . $valor_int . ",
		" . $valor_date . ",
		" . $valor_decimal . ",
		'" . $valor_select_option . "',
		" . $valor_id_tabla . ",
		" . $id_del_registro . ",
		" . $usuario_id . ",
		'" . $created_at . "')";

		$mysqli->query($query_insert);
		$insert_id = mysqli_insert_id($mysqli);
		$error = '';
		if ($mysqli->error) {
			$error = $mysqli->error . " | " . $query_insert;
		}

		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestión.";
		$result["result"] = $insert_id;
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}

	if ($_POST['tabla'] == "contraprestacion") {


		$usuario_id = $login ? $login['id'] : null;
		$created_at = date("Y-m-d H:i:s");

		$moneda_id = $_POST["moneda_id"];
		$forma_pago = 0;
		$forma_pago_detallado = "'" . $_POST["forma_pago_detallado"] . "'";
		$tipo_comprobante = $_POST["tipo_comprobante"];
		$plazo_pago = "'" . $_POST["plazo_pago"] . "'";

		if (empty($_POST["subtotal"])) {
			$subtotal = 0;
		} else {
			$subtotal = str_replace(",", "", $_POST["subtotal"]);
		}

		if (empty($_POST["igv"])) {
			$igv = 0;
		} else {
			$igv = str_replace(",", "", $_POST["igv"]);
		}

		$monto = $subtotal + $igv;

		$query_insert = "INSERT INTO cont_contraprestacion
		(
		moneda_id,
		forma_pago_id,
		forma_pago_detallado,
		tipo_comprobante_id,
		plazo_pago,
		subtotal,
		igv,
		monto,
		user_created_id,
		created_at)
		VALUES
		(
		" . $moneda_id . ",
		" . $forma_pago . ",
		" . $forma_pago_detallado . ",
		" . $tipo_comprobante . ",
		" . $plazo_pago . ",
		" . $subtotal . ",
		" . $igv . ",
		" . $monto . ",
		" . $usuario_id . ",
		'" . $created_at . "')";

		$mysqli->query($query_insert);
		$insert_id = mysqli_insert_id($mysqli);
		$error = '';
		if ($mysqli->error) {
			$error .= $error = $mysqli->error;
			$result["http_code"] = 404;
			$result["status"] = "A ocurrido un error al registrar la contraprestación.";
			$result["result"] = 0;
			$result["error"] = $error;
			echo json_encode($result);
			exit();
		}


		$usuario_id = $login ? $login['id'] : null;
		$created_at = date("Y-m-d H:i:s");
		$valor_original = "";
		$tipo_valor = 'registro';

		$id_del_registro = 0;

		$valor_varchar = "NULL";
		$valor_int = $insert_id;
		$valor_date = "NULL";
		$valor_decimal = "NULL";
		$valor_select_option = "NULL";
		$valor_id_tabla = "NULL";

		$query_insert = " INSERT INTO cont_adendas_detalle
		(
		nombre_tabla,
		valor_original,
		nombre_campo,
		nombre_menu_usuario,
		nombre_campo_usuario,
		tipo_valor,
		valor_varchar,
		valor_int,
		valor_date,
		valor_decimal,
		valor_select_option,
		valor_id_tabla,
		id_del_registro_a_modificar,
		user_created_id,
		created_at)
		VALUES
		(
		'cont_contraprestacion',
		'" . $valor_original . "',
		'contrato_id',
		'Contraprestación',
		'Nueva Contraprestación',
		'" . $tipo_valor . "',
		'" . $valor_varchar . "',
		" . $valor_int . ",
		" . $valor_date . ",
		" . $valor_decimal . ",
		'" . $valor_select_option . "',
		" . $valor_id_tabla . ",
		" . $id_del_registro . ",
		" . $usuario_id . ",
		'" . $created_at . "')";

		$mysqli->query($query_insert);
		$insert_id = mysqli_insert_id($mysqli);
		$error = '';
		if ($mysqli->error) {
			$error = $mysqli->error . " | " . $query_insert;
		}

		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestión.";
		$result["result"] = $insert_id;
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_categoria_contrato") {

	$query = "SELECT 
		id, nombre
	FROM cont_categoria_servicio
	WHERE status = 1
	ORDER BY nombre ASC;";

	$list_query = $mysqli->query($query);
	$list = array();
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list) == 0) {
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

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_categoria_contrato") {

	$query = "SELECT 
		id, nombre
	FROM cont_tipo_categoria_servicio
	WHERE categoria_servicio_id = " . $_POST['valor_select'] . " AND  status = 1
	ORDER BY nombre ASC;";

	$list_query = $mysqli->query($query);
	$list = array();
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list) == 0) {
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

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_firma") {

	$query = "SELECT id, nombre
	FROM cont_tipo_firma
	WHERE status = 1
	ORDER BY nombre ASC";

	$list_query = $mysqli->query($query);
	$list = array();
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list) == 0) {
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

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_fecha_vencimiento") {


	$query = "SELECT id, nombre";
	$query .= " FROM cont_tipo_fecha_vencimiento";
	$query .= " WHERE status = 1";

	$list_query = $mysqli->query($query);
	$list = array();
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list) == 0) {
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

function sec_contrato_nuevo_is_valid_email($str)
{
	return (false !== filter_var($str, FILTER_VALIDATE_EMAIL));
}






// INICIO OBTENER FUNCION NIF16
if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_periodicidad") {

	$query = "SELECT id, nombre 
				FROM cont_tipo_periodicidad
				WHERE status = 1";
	$list_query = $mysqli->query($query);
	$list_con_registros = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_con_registros[] = $li;
	}
	$result = array();
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["error"] = $mysqli->error;
		echo json_encode($result);
		exit();
	}
	$result["http_code"] = 200;
	$result["result"] = $list_con_registros;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_anio_mes") {

	$query = "SELECT id, nombre 
				FROM cont_periodo";
	$list_query = $mysqli->query($query);
	$list_con_registros = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_con_registros[] = $li;
	}
	$result = array();
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["error"] = $mysqli->error;
		echo json_encode($result);
		exit();
	}
	$result["http_code"] = 200;
	$result["result"] = $list_con_registros;
	echo json_encode($result);
	exit();
}


if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_aplicacion") {

	$query = "SELECT id, nombre 
				FROM cont_tipo_aplicacion
				ORDER BY id ASC";
	$list_query = $mysqli->query($query);
	$list_con_registros = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_con_registros[] = $li;
	}
	$result = array();
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["error"] = $mysqli->error;
		echo json_encode($result);
		exit();
	}
	$result["http_code"] = 200;
	$result["result"] = $list_con_registros;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_meses") {

	$query = "SELECT id, nombre 
				FROM tbl_meses
				ORDER BY id ASC";
	$list_query = $mysqli->query($query);
	$list_con_registros = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_con_registros[] = $li;
	}
	$result = array();
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["error"] = $mysqli->error;
		echo json_encode($result);
		exit();
	}
	$result["http_code"] = 200;
	$result["result"] = $list_con_registros;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_si_no") {

	$query = "SELECT id, nombre 
				FROM tbl_tipo_respuesta
				ORDER BY id ASC";
	$list_query = $mysqli->query($query);
	$list_con_registros = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_con_registros[] = $li;
	}
	$result = array();
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["error"] = $mysqli->error;
		echo json_encode($result);
		exit();
	}
	$result["http_code"] = 200;
	$result["result"] = $list_con_registros;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_terminacion_renovacion") {

	$list_con_registros = array(
		array('id' => '1', 'nombre' => 'Terminación'),
		array('id' => '2', 'nombre' => 'Renovación'),
	);

	$result = array();
	$result["http_code"] = 200;
	$result["result"] = $list_con_registros;
	echo json_encode($result);
	exit();
}





if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_inflacion") {

	$usuario_id = $login ? $login['id'] : null;
	$created_at = date('Y-m-d H:i:s');

	$numero = isset($_POST['numero']) && !empty($_POST['numero']) ? $_POST['numero'] : '0';
	$tipo_anio_mes = isset($_POST['tipo_anio_mes']) && !empty($_POST['tipo_anio_mes']) ? $_POST['tipo_anio_mes'] : '0';
	$porcentaje_anadido = !empty($_POST['porcentaje_anadido']) ? $_POST['porcentaje_anadido'] : '0';

	$tope_inflacion = !empty($_POST['tope_inflacion']) ? $_POST['tope_inflacion'] : '0';
	$minimo_inflacion = !empty($_POST['minimo_inflacion']) ? $_POST['minimo_inflacion'] : '0';


	$query_insert = " INSERT INTO cont_inflaciones
		(
			fecha,
			tipo_periodicidad_id,
			numero,
			tipo_anio_mes,
			moneda_id,
			porcentaje_anadido,
			tope_inflacion,
			minimo_inflacion,
			status,
			created_at,
			user_created_id)
		VALUES(
			NULL,
			'" . $_POST['tipo_periodicidad_id'] . "',
			'" . $numero . "',
			'" . $tipo_anio_mes . "',
			NULL,
			" . $porcentaje_anadido . ",
			'" . $tope_inflacion . "',
			'" . $minimo_inflacion . "',
			1,
			'" . $created_at . "',
			'" . $usuario_id . "')";

	$mysqli->query($query_insert);
	$insert_id = mysqli_insert_id($mysqli);
	$error = '';
	if ($mysqli->error) {
		$error = $mysqli->error . " | " . $query_insert;
		$result["http_code"] = 404;
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $insert_id;
	$result["error"] = $error;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "editar_inflacion") {

	$usuario_id = $login ? $login['id'] : null;
	$created_at = date('Y-m-d H:i:s');


	$fecha = "NULL";
	$numero = isset($_POST['numero']) && !empty($_POST['numero']) ? $_POST['numero'] : '0';
	$tipo_anio_mes = isset($_POST['tipo_anio_mes']) && !empty($_POST['tipo_anio_mes']) ? $_POST['tipo_anio_mes'] : '0';

	$porcentaje_anadido = !empty($_POST['porcentaje_anadido']) ? $_POST['porcentaje_anadido'] : 'NULL';
	$tope_inflacion = !empty($_POST['tope_inflacion']) ? $_POST['tope_inflacion'] : 'NULL';
	$minimo_inflacion = !empty($_POST['minimo_inflacion']) ? $_POST['minimo_inflacion'] : 'NULL';

	$query_update = " UPDATE cont_inflaciones SET 
			tipo_periodicidad_id = '" . $_POST['tipo_periodicidad_id'] . "',
			numero = '" . $numero . "',
			tipo_anio_mes = '" . $tipo_anio_mes . "',
			porcentaje_anadido = " . $porcentaje_anadido . ",
			tope_inflacion = " . $tope_inflacion . ",
			minimo_inflacion = " . $minimo_inflacion . "
		WHERE id = " . $_POST['inflacion_id'];

	$mysqli->query($query_update);
	$error = '';
	if ($mysqli->error) {
		$error = $mysqli->error . " | " . $query_update;
		$result["http_code"] = 404;
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["error"] = $error;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_nuevo_lista_inflacion") {

	$inflacion = isset($_POST['inflaciones']) ? $_POST['inflaciones'] : [];
	$ids = '';
	$contador_array_ids = 0;
	foreach ($inflacion as $value) {
		if ($contador_array_ids > 0) {
			$ids .= ',';
		}
		$ids .= $value;
		$contador_array_ids++;
	}
	if ($contador_array_ids == 0) {
		$ids = 0;
	}

	$sql = "SELECT i.id, i.tipo_periodicidad_id, DATE_FORMAT(i.fecha, '%d-%m-%Y') as fecha, tp.nombre  AS tipo_periodicidad, i.numero, p.nombre as tipo_anio_mes, m.sigla AS moneda, i.porcentaje_anadido, i.tope_inflacion, i.minimo_inflacion
		FROM cont_inflaciones AS i
		LEFT JOIN cont_tipo_periodicidad AS tp ON tp.id = i.tipo_periodicidad_id
		LEFT JOIN tbl_moneda AS m ON m.id = i.moneda_id
		LEFT JOIN cont_periodo as p ON p.id = i.tipo_anio_mes
		WHERE i.id IN (" . $ids . ")
		ORDER BY i.id ASC";

	$html = '
	<table class="table table-bordered">
		<thead>
			<tr>
				<th class="text-center">Periodicidad</th>
				<th class="text-center">Porcentaje Añadido</th>
				<th class="text-center">Tope de Inflación</th>
				<th class="text-center">Minimo de Inflación</th>
				<th class="text-center">Acc.</th>
			</tr>
		</thead>
		<tbody>
		
		';
	$query = $mysqli->query($sql);
	while ($row = $query->fetch_assoc()) {
		if ($row['tipo_periodicidad_id'] == 2) {
			$periosidad = $row['tipo_periodicidad'];
		} else {
			$periosidad = $row['tipo_periodicidad'] . ' ' . $row['numero'] . ' ' . $row['tipo_anio_mes'];
		}

		$html .= '
			<tr>
				<td class="text-center">' . $periosidad . '</td>
				<td class="text-center">' . $row['porcentaje_anadido'] . '</td>
				<td class="text-center">' . $row['tope_inflacion'] . '</td>
				<td class="text-center">' . $row['minimo_inflacion'] . '</td>
				<td class="text-center">
					<a class="btn btn-success btn-xs" data-toggle="tooltip" data-placement="top" title="Editar" onclick="sec_contrato_nuevo_modal_editar_inflacion(' . $row['id'] . ')"><i class="fa fa-edit"></i></a>
					<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_contrato_nuevo_eliminar_inflacion(' . $row['id'] . ')"><i class="fa fa-trash"></i></a>
				</td>
			</tr>';
	}
	$html .= '
		</tbody>
	</table>
	<div class="col-xs-12 col-md-12 col-lg-12">
		<br>
	</div>
	<div class="col-xs-12 col-md-4 col-lg-4">
	</div>
	<div class="col-xs-12 col-md-4 col-lg-4 block-new-inflacion">
		<button type="button" class="btn btn-success form-control" onclick="sec_contrato_nuevo_modal_agregar_inflacion(\'new\')" >
				<i class="icon fa fa-plus"></i>
				<span id="demo-button-text">Agregar Inflación</span>
		</button>
	</div>
	';

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $html;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_inflacion_por_id") {

	$sql = "SELECT id, contrato_id, DATE_FORMAT(fecha, '%d-%m-%Y') as fecha, tipo_periodicidad_id,
	numero, tipo_anio_mes, moneda_id, porcentaje_anadido, tope_inflacion, minimo_inflacion
	FROM cont_inflaciones
	WHERE id = " . $_POST['inflacion_id'];
	$query = $mysqli->query($sql);
	$data = array();
	while ($row = $query->fetch_assoc()) {
		$data = $row;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $data;
	echo json_encode($result);
	exit();
}


if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_cuota_extraordinaria") {

	$usuario_id = $login ? $login['id'] : null;
	$created_at = date('Y-m-d H:i:s');

	$query_insert = " INSERT INTO cont_cuotas_extraordinarias
		(
			mes,
			multiplicador,
			status,
			created_at,
			user_created_id)
		VALUES(
			'" . $_POST['mes'] . "',
			'" . $_POST['multiplicador'] . "',
			1,
			'" . $created_at . "',
			'" . $usuario_id . "')";

	$mysqli->query($query_insert);
	$insert_id = mysqli_insert_id($mysqli);
	$error = '';
	if ($mysqli->error) {
		$error = $mysqli->error . " | " . $query_insert;
		$result["http_code"] = 404;
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $insert_id;
	$result["error"] = $error;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "editar_cuota_extraordinaria") {

	$usuario_id = $login ? $login['id'] : null;
	$created_at = date('Y-m-d H:i:s');


	$query_update = " UPDATE cont_cuotas_extraordinarias SET 
			mes = '" . $_POST['mes'] . "',
			multiplicador = '" . $_POST['multiplicador'] . "'	
		WHERE id = " . $_POST['cuota_extraordinaria_id'];

	$mysqli->query($query_update);
	$error = '';
	if ($mysqli->error) {
		$error = $mysqli->error . " | " . $query_update;
		$result["http_code"] = 404;
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["error"] = $error;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_nuevo_lista_cuota_extraordinaria") {

	$cuota_extraordinaria = isset($_POST['cuota_extraordinaria']) ? $_POST['cuota_extraordinaria'] : [];
	$ids = '';
	$contador_array_ids = 0;
	foreach ($cuota_extraordinaria as $value) {
		if ($contador_array_ids > 0) {
			$ids .= ',';
		}
		$ids .= $value;
		$contador_array_ids++;
	}
	if ($contador_array_ids == 0) {
		$ids = 0;
	}

	$sql = "SELECT c.id, m.nombre as mes, c.multiplicador, c.meses_despues
		FROM cont_cuotas_extraordinarias AS c
		INNER JOIN tbl_meses AS m ON m.id = c.mes
		WHERE c.id IN (" . $ids . ")
		ORDER BY c.id ASC";

	$html = '
	<table class="table table-bordered">
		<thead>
			<tr>
				<th class="text-center">Mes en el que se Paga</th>
				<th class="text-center">Multiplicador</th>
				<th class="text-center">Acc.</th>
			</tr>
		</thead>
		<tbody>
		
		';
	$query = $mysqli->query($sql);
	while ($row = $query->fetch_assoc()) {

		$html .= '
			<tr>
				<td class="text-left">' . $row['mes'] . '</td>
				<td class="text-center">' . $row['multiplicador'] . '</td>
				<td class="text-center">
					<a class="btn btn-success btn-xs" data-toggle="tooltip" data-placement="top" title="Editar" onclick="sec_contrato_nuevo_modal_editar_cuota_extraordinaria(' . $row['id'] . ')"><i class="fa fa-edit"></i></a>
					<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_contrato_nuevo_eliminar_cuota_extraordinaria(' . $row['id'] . ')"><i class="fa fa-trash"></i></a>
				</td>
			</tr>';
	}
	$html .= '
		</tbody>
	</table>
	<div class="col-xs-12 col-md-12 col-lg-12">
		<br>
	</div>
	<div class="col-xs-12 col-md-4 col-lg-4">
	</div>
	<div class="col-xs-12 col-md-4 col-lg-4">
		<button type="button" class="btn btn-success form-control" onclick="sec_contrato_nuevo_modal_agregar_cuota_extraordinaria(\'new\')" >
				<i class="icon fa fa-plus"></i>
				<span id="demo-button-text">Agregar Cuota Extraordinaria</span>
		</button>
	</div>
	';

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $html;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_cuota_extraordinaria_por_id") {

	$sql = "SELECT * FROM cont_cuotas_extraordinarias
		WHERE id = " . $_POST['cuota_extraordinaria_id'];
	$query = $mysqli->query($sql);
	$data = array();
	while ($row = $query->fetch_assoc()) {
		$data = $row;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $data;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_cargos") {


	$query = "SELECT c.id, c.nombre FROM tbl_cargos AS c WHERE c.estado = 1 ORDER BY c.nombre ASC";

	$list_query = $mysqli->query($query);
	$list = array();
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list) == 0) {
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

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_cargos") {


	$query = "SELECT c.id, c.nombre FROM tbl_cargos AS c WHERE c.estado = 1 ORDER BY c.nombre ASC";

	$list_query = $mysqli->query($query);
	$list = array();
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	if (count($list) == 0) {
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

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_cargo_usuario") {

	if ($_POST['type'] == 'persona_contacto') {
		$usuario_id = $login ? $login['id'] : null;
		$query = "SELECT pe.id, pe.cargo_id FROM tbl_usuarios AS u 
		INNER JOIN tbl_personal_apt AS pe ON pe.id = u.personal_id AND pe.estado = 1
		WHERE u.id = " . $usuario_id;
		$list_query = $mysqli->query($query);
		$data = $list_query->fetch_assoc();

		if (isset($data['cargo_id'])) {
			$result["status"] = 200;
			$result["message"] = "Datos obtenidos de gestion.";
			$result["result"] = $data['cargo_id'];
			echo json_encode($result);
			exit();
		}

		$result["status"] = 400;
		$result["message"] = "Datos obtenidos de gestion.";
		$result["result"] = 0;
		echo json_encode($result);
		exit();
	} else {
		$usuario_id = $_POST['usuario_id'];
		if (isset($_POST['usuario_id']) && !($usuario_id == "0" || $usuario_id == "A")) {
			$query = "SELECT pe.id, pe.cargo_id FROM tbl_usuarios AS u 
			INNER JOIN tbl_personal_apt AS pe ON pe.id = u.personal_id AND pe.estado = 1
			WHERE u.id = " . $usuario_id;
			$list_query = $mysqli->query($query);
			$data = $list_query->fetch_assoc();

			if (isset($data['cargo_id'])) {
				$result["status"] = 200;
				$result["message"] = "Datos obtenidos de gestion.";
				$result["result"] = $data['cargo_id'];
				echo json_encode($result);
				exit();
			}

			$result["status"] = 400;
			$result["message"] = "Datos obtenidos de gestion.";
			$result["result"] = 0;
			echo json_encode($result);
			exit();
		} else {
			$result["status"] = 400;
			$result["message"] = "Datos obtenidos de gestion.";
			$result["result"] = 0;
			echo json_encode($result);
			exit();
		}
	}
}
// FIN OBTENER FUNCION NIF16

echo json_encode($result);
