<?php
$result=array();
include("db_connect.php");
include("sys_login.php");

include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';
include '/var/www/html/sys/function_replace_invalid_caracters_contratos.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_personal_responsable_agente") {
	$query = "SELECT u.id, concat( IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, ''),' ', IFNULL(p.apellido_materno, '')) AS nombre
	FROM tbl_personal_apt p
	INNER JOIN tbl_usuarios u ON p.id = u.personal_id
	WHERE p.cargo_id = 4 AND p.estado = 1 AND u.estado = 1 AND p.zona_id = 12
	ORDER BY nombre ASC";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
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
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_personal") {
	$query = "SELECT id,
    CONCAT(IFNULL(nombre, ''), IFNULL(CONCAT(' ',apellido_paterno), ''), IFNULL(CONCAT(' ',apellido_materno), '')) as nombre
	FROM tbl_personal_apt
	WHERE estado = 1
	ORDER BY id ASC";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_tipo_persona") {
	$query = "SELECT * FROM cont_tipo_persona WHERE estado = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_tipo_documento_identidad") {
	$query = "SELECT * FROM cont_tipo_docu_identidad WHERE estado = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_tipo_moneda") {
	$query = "SELECT id, nombre FROM tbl_moneda WHERE estado = 1 AND id IN (1,2)";
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

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_tipo_plazo_vigencia") {
	$query = "SELECT * FROM cont_tipo_plazo WHERE status = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_tipo_plazo") {
	$query = "SELECT * FROM cont_periodo WHERE estado = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_periodo") {
	$query = "SELECT * FROM cont_periodo WHERE estado = 1";
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

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_nombre_propietario") {
	$query = "SELECT DISTINCT * FROM cont_persona WHERE status = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
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
	if($tipo_busqueda == '3' || $tipo_busqueda == '5'){
		$sql .= " WHERE id IN(" . $ids . ") ";
	} else if ($tipo_busqueda == '1') {
		$sql .= " WHERE nombre like '%" . $nombre_o_numdocu . "%' ";
	} else if ($tipo_busqueda == '4'){
		$sql .= " WHERE id = '" . $nombre_o_numdocu . "'";
	} else if ($tipo_busqueda == '2'){
		$sql .= " WHERE (num_ruc like '%" . $nombre_o_numdocu . "%' OR num_docu like '%" . $nombre_o_numdocu . "%')";
	} else {
		$sql .= " WHERE num_docu like '%" . $nombre_o_numdocu . "%'";
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
					$html .= '<a class="btn btn-success btn-xs" onclick="sec_con_nuevo_aden_agente_agignar_propietario(' . $row["id"] . ')">';
					$html .= '<i class="fa fa-plus"></i> Agregar a la adenda como propietario';
					$html .= '</a>';
					$html .= '</td>';
				}
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

	echo json_encode($result);
	exit();

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

	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
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

	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_banco") {

	$query = "
	SELECT
		id,
		nombre
	FROM 
		tbl_bancos
	WHERE 
		estado = 1";
	$list_query = $mysqli->query($query);
	$list = array();

	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_tipo_docu_identidad") {
	$query = "SELECT * FROM cont_tipo_docu_identidad WHERE estado = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_tipo_vigencia") {

	$query = "SELECT id, nombre";
	$query .= " FROM cont_tipo_plazo";
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
		$result["result"] = "No hay tipo de plazos.";
	} elseif (count($list) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $list;
	}
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_contratos") {
	
	$supervisor = $_POST['supervisor'];

	$where_supervisor = " AND c.persona_responsable_id = ".$supervisor;

	$query = "SELECT c.contrato_id, c.fecha_inicio, c.nombre_agente, c.c_costos
	FROM cont_contrato c
	WHERE c.status = 1 AND c.tipo_contrato_id = 6 
	AND c.etapa_id = 5
	$where_supervisor
	";

	$list_query = $mysqli->query($query);
	$list = [];

	if (!Empty($supervisor) && $supervisor != "0") {
		while ($li = $list_query->fetch_assoc()) {
			$date = new DateTime($li['fecha_inicio']);
			$fecha_inicio = $date->format('Y-m-d');
			array_push($list,array(
				'id' => $li['contrato_id'],
				'nombre' => $fecha_inicio.' - '.$li['c_costos'].' | '.$li['nombre_agente'],
			));
		}
	}
	
	
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
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

	echo json_encode($result);
	exit();
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

	echo json_encode($result);
	exit();
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
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_contrato_por_id") {

	$contrato_id = $_POST["contrato_id"];
	$query = $mysqli->query("SELECT 
		c.contrato_id,
		c.tipo_contrato_id,
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
		c.fecha_suscripcion_contrato,
		c.periodo,
		c.periodo_numero,
		c.nombre_agente,
		c.c_costos,
		c.cc_id,
		c.fecha_inicio,

		c.plazo_id_agente,
		c.fecha_inicio_agente,
		c.fecha_fin_agente,

		ctp.nombre as plazo

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
		LEFT JOIN cont_tipo_plazo ctp ON ctp.id = c.plazo_id_agente
	WHERE 
		c.contrato_id IN (" . $contrato_id . ")");

	
	$row = $query->fetch_assoc();
	if($row["periodo"]=='1'){				
		$periodo = 'Año(s)';
	}else{
		$periodo = 'Mes(es)';
	}
	$contrato_id = $row["contrato_id"];
	$tipo_contrato_id = $row["tipo_contrato_id"];
	$empresa_suscribe = $row["empresa_suscribe"];
	$nombre_tienda = $row["nombre_tienda"];
	$observaciones = $row["observaciones"];
	$supervisor = trim($row["persona_responsable"]);
	$jefe_comercial = trim($row["jefe_comercial"]);
	$usuario_created_id = $row["user_created_id"];
	$verificar_giro = $row["verificar_giro"];
	$fecha_suscripcion_contrato = $row["fecha_suscripcion_contrato"];
	$periodo_numero = $row["periodo_numero"];

	$plazo_id_agente = $row["plazo_id_agente"];
	$plazo = $row["plazo"];
	$fecha_inicio_agente = $row["fecha_inicio_agente"];
	$fecha_fin_agente = $row["fecha_fin_agente"];

	// $periodo_anio_mes = $row["periodo_anio_mes"];
	$nombre_agente = $row["nombre_agente"];
	$c_costos = $row["c_costos"];
	$fecha_verificacion_giro = $row["fecha_verificacion_giro"];
	$usuario_verificacion_giro = $row["persona_verificaciongiro"];
	$centro_de_costos = $row["cc_id"];


	$html = '
	<div class="panel">
		<div class="panel-heading">
			<div class="panel-title">DETALLE DE LA SOLICITUD DE CONTRATO DE AGENTE</div>
			<input type="hidden" value="'.$contrato_id.'" id="id_registro_contrato_id">
			<input type="hidden" value="'.$tipo_contrato_id.'" id="id_tipo_contrato">
		</div>
		<div class="panel-body">
			<form>
				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="h4"><b>DATOS GENERALES</b></div>
				</div>
				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="form-group">
						<table class="table table-bordered table-hover">
							<tr>
								<td style="width: 50%;"><b>Empresa eque suscribe</b></td>
								<td>' . $empresa_suscribe . '</td>
								<td style="width: 75px;">
							
								</td>
							</tr>

							<tr>
								<td style="width: 50%;"><b>Supervisor</b></td>
								<td>' . $supervisor . '</td>
								<td style="width: 75px;">
								
								</td>
							</tr>
							
						</table>
					</div>
				</div>

				<div class="col-xs-12 col-md-12 col-lg-12">
					<br>
				</div>

				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="h4"><b>DATOS DEL PROPIETARIO</b>
						<button type="button" class="btn btn-sm btn-info" onclick=" sec_con_nuevo_aden_agente_buscar_propietario_modal(\'agente\')">
							<i class="fa fa-plus"></i> Agregar Propietario
						</button>
					</div>
				</div>';


				$sel_query = $mysqli->query("SELECT p.id AS persona_id,
									pr.propietario_id,
									tp.nombre AS tipo_persona,
									p.tipo_docu_identidad_id,
									td.nombre AS tipo_docu_identidad,
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
								WHERE pr.contrato_id IN (" . $contrato_id . ");");

				while($sel = $sel_query->fetch_assoc()){
					$tipo_docu_identidad_id = $sel["tipo_docu_identidad_id"];
					$tipo_docu_identidad = $sel["tipo_docu_identidad"];
					$num_docu_propietario = $sel["num_docu"];
					$num_ruc_propietario = $sel["num_ruc"];

					if(empty($num_ruc_propietario)) {
						$num_ruc_propietario = 'Sin asignar';
					}
				$html .= '
				<table class="table table-bordered table-hover">
					<tr>
						<td style="width: 250px;"><b>Tipo de Persona</b></td>
						<td>'.$sel["tipo_persona"].'</td>
						<td style="width: 75px;">
							<a class="btn btn-success btn-xs" 
							onclick=" sec_con_nuevo_aden_agente_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'tipo_persona_id\',\'Tipo de Persona\',\'select_option\',\'' . $sel["tipo_persona"] . '\',\'obtener_tipo_persona\',\'' . $sel["persona_id"] . '\');">
							<span class="fa fa-edit"></span> Editar
							</a>
						</td>
					</tr>
					<tr>
						<td><b>Nombre</b></td>
						<td>'.$sel["nombre"].'</td>
						<td>
							<a class="btn btn-success btn-xs" 
							onclick=" sec_con_nuevo_aden_agente_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'nombre\',\'Tipo Nombre Persona\',\'varchar\',\'' . $sel["nombre"] . '\',\'\',\'' . $sel["persona_id"] . '\');">
							<span class="fa fa-edit"></span> Editar
							</a>
						</td>
					</tr>
					<tr>
						<td><b>Tipo de Documento de Identidad</b></td>
						<td>'.$tipo_docu_identidad.'</td>
						<td>
							<a class="btn btn-success btn-xs" 
							onclick=" sec_con_nuevo_aden_agente_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'tipo_docu_identidad_id\',\'Tipo de Documento de Identidad\',\'select_option\',\'' . $tipo_docu_identidad . '\',\'obtener_tipo_docu_identidad\',\'' . $sel["persona_id"] . '\');">
							<span class="fa fa-edit"></span> Editar
							</a>
						</td>
					</tr>';

					if ($tipo_docu_identidad_id != "2") {
				$html .= '
					<tr>
						<td><b>Número de '.$tipo_docu_identidad.'</b></td>
						<td>'.$num_docu_propietario.'</td>
						<td>
							<a class="btn btn-success btn-xs" 
							onclick=" sec_con_nuevo_aden_agente_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'num_docu\',\'Número de Documento de Identidad\',\'varchar\',\'' . $num_docu_propietario . '\',\'\',\'' . $sel["persona_id"] . '\');">
							<span class="fa fa-edit"></span> Editar
							</a>
						</td>
					</tr>';
					} 

				$html .= '
					<tr>
						<td><b>Número de RUC</b></td>
						<td>'.$num_ruc_propietario.'</td>
						<td>
							<a class="btn btn-success btn-xs" 
							onclick=" sec_con_nuevo_aden_agente_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'num_ruc\',\'Número de RUC\',\'varchar\',\'' . $num_ruc_propietario . '\',\'\',\'' . $sel["persona_id"] . '\');">
							<span class="fa fa-edit"></span> Editar
							</a>
						</td>
					</tr>
					<tr>
						<td><b>Domicilio del propietario</b></td>
						<td>'.$sel["direccion"].'</td>
						<td>
							<a class="btn btn-success btn-xs" 
							onclick=" sec_con_nuevo_aden_agente_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'direccion\',\'Domicilio del propietario\',\'varchar\',\'' . $sel["direccion"] . '\',\'\',\'' . $sel["persona_id"] . '\');">
							<span class="fa fa-edit"></span> Editar
							</a>
						</td>
					</tr>
					<tr>
						<td><b>Representante Legal</b></td>
						<td>'.$sel["representante_legal"].'</td>
						<td>
							<a class="btn btn-success btn-xs" 
							onclick=" sec_con_nuevo_aden_agente_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'representante_legal\',\'Representante Legal\',\'varchar\',\'' . $sel["representante_legal"] . '\',\'\',\'' . $sel["persona_id"] . '\');">
							<span class="fa fa-edit"></span> Editar
							</a>
						</td>
					</tr>
					<tr>
						<td><b>N° de Partida Registral de la empresa</b></td>
						<td>'.$sel["num_partida_registral"].'</td>
						<td>
							<a class="btn btn-success btn-xs" 
							onclick=" sec_con_nuevo_aden_agente_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'num_partida_registral\',\'N° de Partida Registral de la empresa\',\'varchar\',\'' . $sel["num_partida_registral"] . '\',\'\',\'' . $sel["persona_id"] . '\');">
							<span class="fa fa-edit"></span> Editar
							</a>
						</td>
					</tr>
					<tr>
						<td><b>Persona de contacto</b></td>
						<td>'.$sel["contacto_nombre"].'</td>
						<td>
							<a class="btn btn-success btn-xs" 
							onclick=" sec_con_nuevo_aden_agente_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'contacto_nombre\',\'Persona de contacto\',\'varchar\',\'' . $sel["contacto_nombre"] . '\',\'\',\'' . $sel["persona_id"] . '\');">
							<span class="fa fa-edit"></span> Editar
							</a>
						</td>
					</tr>
					<tr>
						<td><b>Teléfono de la persona de contacto</b></td>
						<td>'.$sel["contacto_telefono"].'</td>
						<td>
							<a class="btn btn-success btn-xs" 
							onclick=" sec_con_nuevo_aden_agente_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'contacto_telefono\',\'Teléfono de la persona de contacto\',\'varchar\',\'' . $sel["contacto_telefono"] . '\',\'\',\'' . $sel["persona_id"] . '\');">
							<span class="fa fa-edit"></span> Editar
							</a>
						</td>
					</tr>
					<tr>
						<td><b>E-mail de la persona de contacto</b></td>
						<td>'.$sel["contacto_email"].'</td>
						<td>
							<a class="btn btn-success btn-xs" 
							onclick=" sec_con_nuevo_aden_agente_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'contacto_email\',\'E-mail de la persona de contacto\',\'varchar\',\'' . $sel["contacto_email"] . '\',\'\',\'' . $sel["persona_id"] . '\');">
							<span class="fa fa-edit"></span> Editar
							</a>
						</td>
					</tr>
				</table>';
			
				}

				$sel_query = $mysqli->query("SELECT
					i.id,
					i.ubigeo_id,
					ude.nombre AS departamento, 
					upr.nombre AS provincia,
					udi.nombre AS distrito,
					i.ubicacion
				FROM cont_inmueble i
				INNER JOIN tbl_ubigeo ude ON SUBSTRING(i.ubigeo_id, 1, 2) = ude.cod_depa AND ude.cod_prov = '00' AND ude.cod_dist = '00'
				INNER JOIN tbl_ubigeo upr ON SUBSTRING(i.ubigeo_id, 1, 2) = upr.cod_depa AND SUBSTRING(i.ubigeo_id, 3, 2) = upr.cod_prov AND upr.cod_dist = '00'
				INNER JOIN tbl_ubigeo udi ON SUBSTRING(i.ubigeo_id, 1, 2) = udi.cod_depa AND SUBSTRING(i.ubigeo_id, 3, 2) = udi.cod_prov AND SUBSTRING(i.ubigeo_id, 5, 2) = udi.cod_dist
				WHERE i.contrato_id = " . $contrato_id . ";");

				$html .= '
				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="h5"><b>DATOS DEL LOCAL</b></div>
				</div>

				<div class="col-xs-12 col-md-12 col-lg-12">
					<div id="divTablaInmuebles" class="form-group">';
						while($sel=$sel_query->fetch_assoc()){
							$ubigeo_id = $sel["ubigeo_id"];
				
				$html .= '
						<table class="table table-bordered table-hover">
							<tr>
								<td><b>Ubigeo</b></td>
								<td>'.$ubigeo_id.'</td>
								<td style="width: 75px;">
								</td>
							</tr>
							<tr>
								<td style="width: 250px;"><b>Departamento</b></td>
								<td>'.$sel["departamento"].'</td>
								<td rowspan="3" style="width: 75px;">
									<a class="btn btn-success btn-xs" 
									onclick=" sec_con_nuevo_aden_agente_solicitud_editar_campo_adenda(\'Inmueble\',\'cont_inmueble\',\'ubigeo_id\',\'Departamento/Provincia/Distrito\',\'select_option\',\'' . $sel["departamento"] . "/" . $sel["provincia"] . "/" . $sel["distrito"] . '\',\'obtener_departamentos\',\''.$sel["id"].'\');">
									<span class="fa fa-edit"></span> Editar
									</a>
								</td>
							</tr>
							<tr>
								<td><b>Provincia</b></td>
								<td>'.$sel["provincia"].'</td>
							</tr>
							<tr>
								<td><b>Distrito</b></td>
								<td>'.$sel["distrito"].'</td>
							</tr>
							<tr>
								<td><b>Ubicación</b></td>
								<td>'.$sel["ubicacion"].'</td>
								<td>
									<a class="btn btn-success btn-xs" 
									onclick=" sec_con_nuevo_aden_agente_solicitud_editar_campo_adenda(\'Inmueble\',\'cont_inmueble\',\'ubicacion\',\'Ubicación\',\'varchar\',\'' . $sel["ubicacion"]. '\',\'\',\''.$sel["id"].'\');">
									<span class="fa fa-edit"></span> Editar
									</a>
								</td>
							</tr>
						
						</table>';
						}
				$html .= '
					</div>
				</div>';

				$sel_query = $mysqli->query("SELECT 
								c.id,
								c.participacion_id,
								c.porcentaje_participacion,
								c.condicion_comercial_id,
								p.nombre as nombre_participacion,
								m.nombre as nombre_condicion
							FROM cont_cc_agente c
							INNER JOIN cont_participaciones p ON c.participacion_id = p.id 
							INNER JOIN cont_condiciones_comerciales m ON c.condicion_comercial_id = m.id
							WHERE c.contrato_id = " . $contrato_id);

				$html .= '
				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="h5"><b>CONDICIONES COMERCIALES</b></div>
				</div>

				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="form-group">
						<table class="table table-bordered table-hover">';

							while($row=$sel_query->fetch_assoc()){                                
								$participacion_id = $row["participacion_id"];
								$porcentaje_participacion = $row["porcentaje_participacion"];
								$condicion_comercial_id = $row["condicion_comercial_id"];
								$nombre_participacion = $row["nombre_participacion"];
								$nombre_condicion = $row["nombre_condicion"];
				$html .= '						
							<tr>
								<td style="width: 250px;"><b>Tipo</b></td>
								<td>'.$nombre_participacion.'</td>
								<td style="width: 75px;"></td>
							</tr>
							<tr>
								<td><b>Porcentaje de Participación</b></td>
								<td>'.$porcentaje_participacion.'</td>
								<td>
									<a class="btn btn-success btn-xs" 
									onclick=" sec_con_nuevo_aden_agente_solicitud_editar_campo_adenda(\'Condiciones Comerciales\',\'cont_cc_agente\',\'porcentaje_participacion\',\'Porcentaje de Participación\',\'decimal\',\'' . $porcentaje_participacion. '\',\'\',\''.$row["id"].'\');">
									<span class="fa fa-edit"></span> Editar
									</a>
								</td>
							</tr>
							<tr>
								<td><b>Condición de Participación</b></td>
								<td>'.$nombre_condicion.'</td>
								<td>
									<a class="btn btn-success btn-xs" 
									onclick=" sec_con_nuevo_aden_agente_solicitud_editar_campo_adenda(\'Condiciones Comerciales\',\'cont_cc_agente\',\'condicion_comercial_id\',\'Condición de Participación\',\'select_option\',\'' . $nombre_condicion. '\',\'obtener_condiciones_comerciales_de_agentes\',\''.$row["id"].'\');">
									<span class="fa fa-edit"></span> Editar
									</a>
								</td>
							</tr>';
							}
				$html .= '	
						</table>
					</div>
				</div>

				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="h5"><b>PLAZO</b></div>
				</div>
				
				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="form-group">
						<table class="table table-bordered table-hover">
							<tr>
								<td style="width: 250px;"><b>Vigencia</b></td>
								<td>'.$plazo.'</td>
								<td style="width: 75px;">
									<a class="btn btn-success btn-xs" 
									onclick=" sec_con_nuevo_aden_agente_solicitud_editar_campo_adenda(\'Periodo\',\'cont_contrato\',\'plazo_id_agente\',\'Vigencia\',\'select_option\',\'' . $plazo. '\',\'obtener_tipo_vigencia\',\'\');">
									<span class="fa fa-edit"></span> Editar
									</a>
								</td>
							</tr>';

							if ($plazo_id_agente == 1) {
							$html .= '	
								<tr>
									<td style="width: 250px;"><b>Cantidad</b></td>
									<td>'.$periodo_numero.'</td>
									<td style="width: 75px;">
										<a class="btn btn-success btn-xs" 
										onclick=" sec_con_nuevo_aden_agente_solicitud_editar_campo_adenda(\'Periodo\',\'cont_contrato\',\'periodo_numero\',\'Periodo\',\'varchar\',\'' . $periodo_numero. '\',\'\',\'\');">
										<span class="fa fa-edit"></span> Editar
										</a>
									</td>
								</tr>
								<tr>
									<td style="width: 250px;"><b>Años o Meses</b></td>
									<td>'.$periodo.'</td>
									<td style="width: 75px;">
										<a class="btn btn-success btn-xs" 
										onclick=" sec_con_nuevo_aden_agente_solicitud_editar_campo_adenda(\'Periodo\',\'cont_contrato\',\'periodo\',\'Periodo\',\'select_option\',\'' . $periodo. '\',\'obtener_plazo_agente\',\'\');">
										<span class="fa fa-edit"></span> Editar
										</a>
									</td>
								</tr>';
							}

							$html .= '	
							<tr>
								<td style="width: 250px;"><b>Fecha Inicio</b></td>
								<td>'.$fecha_inicio_agente.'</td>
								<td style="width: 75px;">
									<a class="btn btn-success btn-xs" 
									onclick=" sec_con_nuevo_aden_agente_solicitud_editar_campo_adenda(\'Periodo\',\'cont_contrato\',\'fecha_inicio_agente\',\'Fecha Inicio\',\'date\',\'' . $fecha_inicio_agente. '\',\'\',\'\');">
									<span class="fa fa-edit"></span> Editar
									</a>
								</td>
							</tr>';
							if ($plazo_id_agente == 1) {
							$html .= '
							<tr>
								<td style="width: 250px;"><b>Fecha Fin</b></td>
								<td>'.$fecha_fin_agente.'</td>
								<td style="width: 75px;">
									<a class="btn btn-success btn-xs" 
									onclick=" sec_con_nuevo_aden_agente_solicitud_editar_campo_adenda(\'Periodo\',\'cont_contrato\',\'fecha_fin_agente\',\'Fecha Fin\',\'date\',\'' . $fecha_fin_agente. '\',\'\',\'\');">
									<span class="fa fa-edit"></span> Editar
									</a>
								</td>
							</tr>';
							}
							$html .= '
						</table>

					</div>
				</div>

				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="h5"><b>NOMBRE DEL LOCAL</b></div>
				</div>

				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="form-group">
						<table class="table table-bordered table-hover">
							<tr>
								<td style="width: 250px;"><b>Agente AT</b></td>
								<td>'.$nombre_agente.'</td>
								<td style="width: 75px;">
									<a class="btn btn-success btn-xs" 
									onclick=" sec_con_nuevo_aden_agente_solicitud_editar_campo_adenda(\'Nombre del Agente\',\'cont_contrato\',\'nombre_agente\',\'Nombre del Agente\',\'varchar\',\'' . $nombre_agente. '\',\'\',\'\');">
									<span class="fa fa-edit"></span> Editar
									</a>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="h5"><b>CC</b></div>
				</div>

				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="form-group">
						<table class="table table-bordered table-hover">
							<tr>
								<td style="width: 250px;"><b>Centro de costos</b></td>
								<td>'.$c_costos.'</td>
								<td style="width: 75px;">
									<a class="btn btn-success btn-xs" 
									onclick=" sec_con_nuevo_aden_agente_solicitud_editar_campo_adenda(\'Centro de costos\',\'cont_contrato\',\'c_costos\',\'Centro de costos\',\'int\',\'' . $c_costos. '\',\'\',\'\');">
									<span class="fa fa-edit"></span> Editar
									</a>
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="h5"><b>FECHA DE SUSCRIPCIÓN DEL CONTRATO</b></div>
				</div>

				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="form-group">
						<table class="table table-bordered table-hover">
							<tr>
								<td style="width: 250px;"><b>Fecha de suscripción del contrato</b></td>
								<td>'.$fecha_suscripcion_contrato.'</td>
								<td style="width: 75px;">
								</td>
							</tr>
						</table>
					</div>
				</div>

				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="h5"><b>OBSERVACIONES</b></div>
				</div>

				<div class="col-xs-12 col-md-12 col-lg-12">
					<div class="form-group">
						<table class="table table-bordered table-hover">
							<tr>
								<td style="width: 250px;"><b>Observaciones</b></td>
								<td>'.$observaciones.'</td>
								<td style="width: 75px;">
									<a class="btn btn-success btn-xs" 
									onclick=" sec_con_nuevo_aden_agente_solicitud_editar_campo_adenda(\'Observaciones\',\'cont_contrato\',\'observaciones\',\'Observaciones\',\'textarea\',\'' . replace_invalid_caracters_vista($observaciones). '\',\'\',\'\');">
									<span class="fa fa-edit"></span> Editar
									</a>
								</td>
							</tr>							 
						</table>
					</div>
				</div>';
				$html .='
			</form>
		</div>
	</div>';
	



	

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<br>';
	$html .= '</div>';

	$result["status"] = 200;
	$result["messages"] = "Datos obtenidos de gestion.";
	$result["result"] = $html;
	echo json_encode($result);
	exit();
}































if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_tipos_de_archivos") {
	$tipo_contrato_id = $_POST["tipo_contrato_id"];

	$query = "
	SELECT 
		tipo_archivo_id, nombre_tipo_archivo
	FROM
		cont_tipo_archivos
	WHERE
		status = 1
		AND tipo_contrato_id = $tipo_contrato_id
		AND tipo_archivo_id NOT IN (16 , 17, 19)
	ORDER BY nombre_tipo_archivo ASC
	";

	$list_query = $mysqli->query($query);
	$list_proc_tipos_archivos = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_proc_tipos_archivos[] = $li;
	}

	$result["http_code"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list_proc_tipos_archivos;
	echo json_encode($result);
	exit();
}



if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_bancos") {
	$query = "SELECT id, ifnull(nombre, '') nombre 
				FROM tbl_bancos
				WHERE estado = 1";

	$list_query = $mysqli->query($query);
	$list = array();
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	$result=array();
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_periodo") {
	$query = "SELECT * FROM cont_periodo WHERE estado = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	$result=array();
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_tipo_comprobante") {
	$query = "SELECT * FROM cont_tipo_comprobante WHERE estado = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	$result=array();
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_forma_pago") {
	$query = "SELECT * FROM cont_forma_pago WHERE estado = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	$result=array();
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_monedas") {
	$query = "SELECT id, nombre FROM tbl_moneda WHERE id IN (1,2) AND estado = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	$result=array();
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="listar_proveedores") {
	$proveedores = isset($_POST['proveedores']) ? $_POST['proveedores']:[];

	$html = '';
	$html .= '<table class="table table-hover">
		<thead>
			<th class="text-center">DNI R.L</th>
			<th class="text-center">Nombre Completo R.L</th>
			<th class="text-center">N° Cuenta Detracción</th>
			<th class="text-center">Banco</th>
			<th class="text-center">N° Cuenta</th>
			<th class="text-center">N° CCI</th>
			<th class="text-center">Vigencia de Poder</th>
			<th class="text-center">DNI</th>
			<th class="text-center">Acc.</th>
		</thead>
		<tbody>';

		for ($i=0; $i < count($proveedores) ; $i++) { 
			$html .= '
			<tr>
				<td class="text-left">'.$proveedores[$i]['dni_representante'].'</td>
				<td class="text-left">'.$proveedores[$i]['nombre_representante'].'</td>
				<td class="text-left">'.$proveedores[$i]['nro_cuenta_detraccion'].'</td>
				<td class="text-left">'.$proveedores[$i]['banco_nombre'].'</td>
				<td class="text-left">'.$proveedores[$i]['nro_cuenta'].'</td>
				<td class="text-left">'.$proveedores[$i]['nro_cci'].'</td>
				<td><input type="file" name="vigencia_nuevo_representante_' .$i .'" accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip"></td>
				<td><input type="file" name="dni_nuevo_representante_' .$i .'" accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip"></td>
				<td><button type="button" class="btn btn-sm btn-success" onclick="sec_con_nuevo_int_editar_proveedor('.$i.')"><i class="fa fa-edit"></i></button></td>
				<td><button type="button" class="btn btn-sm btn-danger" onclick="sec_con_nuevo_int_eliminar_proveedor('.$i.')"><i class="fa fa-close"></i></button></td>
			</tr>
			';
		}
			
	$html .= '
		</tbody>
	</table>';
	


	$result=array();
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $html;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="listar_contraprestaciones") {
	$contraprestaciones = isset($_POST['contraprestaciones']) ? $_POST['contraprestaciones']:[];

	$html = '';
	$html .= '<table class="table table-hover">
		<thead>
			<th class="text-center">Tipo de Moneda</th>
			<th class="text-center">Subtotal</th>
			<th class="text-center">IGV</th>
			<th class="text-center">Monto Bruto</th>
			<th class="text-center">Forma de Pago</th>
			<th class="text-center">Tipo de Comprobante a Emitir</th>
			<th class="text-center">Plazo de pago</th>
			<th colspan="2" class="text-center">Opciones</th>
		</thead>
		<tbody>';

		for ($i=0; $i < count($contraprestaciones) ; $i++) { 
			$html .= '
			<tr>
				<td class="text-left">'.$contraprestaciones[$i]['moneda_nombre'].'</td>
				<td class="text-left">'.$contraprestaciones[$i]['subtotal'].'</td>
				<td class="text-left">'.$contraprestaciones[$i]['igv'].'</td>
				<td class="text-left">'.$contraprestaciones[$i]['monto'].'</td>
				<td class="text-left">'.$contraprestaciones[$i]['forma_pago_detallado'].'</td>
				<td class="text-left">'.$contraprestaciones[$i]['tipo_comprobante_nombre'].'</td>
				<td class="text-left">'.$contraprestaciones[$i]['plazo_pago'].'</td>
				<td><button type="button" class="btn btn-sm btn-success" onclick="sec_con_nuevo_int_editar_contraprestacion('.$i.')"><i class="fa fa-edit"></i></button></td>
				<td><button type="button" class="btn btn-sm btn-danger" onclick="sec_con_nuevo_int_eliminar_contraprestacion('.$i.')"><i class="fa fa-close"></i></button></td>
			</tr>
			';
		}
			
	$html .= '
		</tbody>
	</table>';
	


	$result=array();
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $html;
	echo json_encode($result);
	exit();
}

?>