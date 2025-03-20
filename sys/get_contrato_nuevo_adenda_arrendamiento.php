<?php
$result = array();
include("db_connect.php");
include("sys_login.php");

include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';
include '/var/www/html/sys/function_replace_invalid_caracters_contratos.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_persona") {
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

	echo json_encode($result);
	exit();
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

	echo json_encode($result);
	exit();
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
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_docu_identidad") {
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

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_pago_servicio") {
	$query = "SELECT id, nombre
	FROM cont_tipo_pago_servicio
	ORDER BY id ASC";
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

	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
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

	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
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
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
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
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
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
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
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
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
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
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
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
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
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
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_periodo_de_gracia") {
	$query = "SELECT * FROM cont_tipo_periodo_de_gracia WHERE status = 1";
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
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
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
		$sql .= " WHERE nombre like '%" . $nombre_o_numdocu . "%' ";
	} else if ($tipo_busqueda == '4') {
		$sql .= " WHERE id = '" . $nombre_o_numdocu . "'";
	} else if ($tipo_busqueda == '2') {
		$sql .= " WHERE (num_ruc like '%" . $nombre_o_numdocu . "%' OR num_docu like '%" . $nombre_o_numdocu . "%')";
	} else {
		$sql .= " WHERE num_docu like '%" . $nombre_o_numdocu . "%'";
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
				if ($tipo_solicitud == 'CambiarPropietario') {
					$html .= '<td>';
					$html .= '<a class="btn btn-success btn-xs" onclick="sec_con_nuevo_aden_arrend_reemplazar_propietario(' . $row["id"] . ')">';
					$html .= '<i class="fa fa-plus"></i> Agregar a la adenda como propietario';
					$html .= '</a>';
					$html .= '</td>';
				}
				if ($tipo_solicitud == 'NuevoPropietario') {
					$html .= '<td>';
					$html .= '<a class="btn btn-success btn-xs" onclick="sec_con_nuevo_aden_arrend_agignar_propietario(' . $row["id"] . ')">';
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

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tiendas") {
	$usuario_id = $login ? $login['id'] : null;
	$query = "
	SELECT
a.contrato_id as id,
c.nombre ARRENDADOR,
d.nombre ARRENDATARIO,
concat('CODIGO: A', a.codigo_correlativo,' - ARRENDADOR: ',c.nombre, ' - ARRENDATARIO: ',d.nombre) as nombre
from cont_contrato a
LEFT JOIN cont_propietario b on b.contrato_id = a.contrato_id
LEFT JOIN cont_persona c on c.id = b.persona_id
LEFT JOIN tbl_razon_social d on d.id = a.empresa_suscribe_id
WHERE a.tipo_contrato_id = 1
AND a.status = 1
AND a.etapa_id = 5
ORDER BY a.contrato_id DESC
				";
	// 	$query = "SELECT 
	// 	contrato_id as id, 
	// 	nombre_tienda as nombre
	// FROM cont_contrato
	// WHERE tipo_contrato_id = 1
	// AND status = 1
	// AND etapa_id = 5
	// ORDER BY contrato_id DESC
	// 				";
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

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_contrato_arrendamiento_por_id") {

	$id_contrato_arrendamiento = $_POST["contrato_id"];

	$sel_query = $mysqli->query("
					SELECT 
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
						CONCAT(IFNULL(pa.nombre, ''),' ',IFNULL(pa.apellido_paterno, ''),' ',IFNULL(pa.apellido_materno, '')) AS abogado,
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

						LEFT JOIN tbl_usuarios ua ON c.abogado_id = ua.id
						LEFT JOIN tbl_personal_apt pa ON ua.personal_id = pa.id
					WHERE 
						c.contrato_id IN (" . $id_contrato_arrendamiento . ")");
	while ($sel = $sel_query->fetch_assoc()) {
		$contrato_id  = $sel["contrato_id"];
		$tipo_contrato_id  = $sel["tipo_contrato_id"];
		$empresa_suscribe = $sel["empresa_suscribe"];
		$nombre_tienda = $sel["nombre_tienda"];
		$observaciones = $sel["observaciones"];
		$supervisor = trim($sel["persona_responsable"]);
		$abogado = trim($sel["abogado"]);
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
	$html .= '
	<div class="panel">
		<div class="panel-heading">
			<div class="panel-title">DETALLE DE LA SOLICITUD DE CONTRATO DE ARRENDAMIENTO</div>
			<input type="hidden" value="' . $contrato_id . '" id="id_registro_contrato_id">
			<input type="hidden" value="' . $tipo_contrato_id . '" id="id_tipo_contrato">
		</div>
		<div class="panel-body p-0">';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="h4">';
	$html .= '<b>DATOS GENERALES</b>&nbsp;';
	$html .= '</div>';
	$html .= '</div>';

	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="form-group">';
	$html .= '<table class="table table-bordered table-hover">';

	$html .= '<tr>';
	$html .= '<td><b>Empresa Arrendataria</b></td>';
	$html .= '<td>' . $empresa_suscribe . '</td>';
	$html .= '<td style="width: 75px;">';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_con_nuevo_aden_arrend_solicitud_editar_campo_adenda(\'Datos Generales\',\'cont_contrato\',\'empresa_suscribe_id\',\'Empresa Arrendaria\',\'select_option\',\'' . $empresa_suscribe . '\',\'obtener_empresa_at\',\'\');">';
	$html .= '<span class="fa fa-edit"></span> Editar';
	$html .= '</a>';
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td><b>Supervisor</b></td>';
	$html .= '<td>' . $supervisor . '</td>';
	$html .= '<td style="width: 75px;">';
	$html .= '<a class="btn btn-success btn-xs" ';
	$html .= 'onclick="sec_con_nuevo_aden_arrend_solicitud_editar_campo_adenda(\'Datos Generales\',\'cont_contrato\',\'persona_responsable_id\',\'Supervisor\',\'select_option\',\'' . $supervisor . '\',\'obtener_personal_responsable\',\'\');">';
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
	WHERE pr.status = 1 AND pr.contrato_id = " . $id_contrato_arrendamiento . "";
	$propiertario_query = $mysqli->query($query);





	$html .= '<input type="hidden" id="contrato_id" value ="' . $id_contrato_arrendamiento . '"/>';
	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<div class="h4">';
	$html .= '<b>DATOS DEL PROPIETARIO</b>&nbsp;';

	$html .= '</div>';
	$html .= '</div>';

	$nro = 1;
	while ($row = $propiertario_query->fetch_assoc()) {
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


		$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
		$html .= '<div class="">';
		$html .= '<b>PROPIETARIO</b>&nbsp;';
		$html .= '<a class="btn btn-warning btn-xs" ';
		$html .= 'onclick="sec_con_nuevo_aden_arrend_buscar_propietario_modal(\'CambiarPropietario\',\'' . $propietario_id . '\',\'' . $persona_id . '\')">';
		$html .= '<span class="fa fa-pencil"></span> Reemplazar Propietario';
		$html .= '</a>  ';

		$html .= '</div>';
		$html .= '</div>';

		$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
		$html .= '<div class="form-group">';
		$html .= '<table class="table table-bordered table-hover">';

		$html .= '<tr>';
		$html .= '<td><b>Tipo de Persona</b></td>';
		$html .= '<td>' . $tipo_persona . '</td>';
		$html .= '<td style="width: 75px;">';
		$html .= '<a class="btn btn-success btn-xs" ';
		$html .= 'onclick="sec_con_nuevo_aden_arrend_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'tipo_persona_id\',\'Tipo de Persona\',\'select_option\',\'' . $tipo_persona . '\',\'obtener_tipo_persona\',\'\');">';
		$html .= '<span class="fa fa-edit"></span> Editar';
		$html .= '</a>';
		$html .= '</td>';
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= '<td><b>Nombre</b></td>';
		$html .= '<td>' . $nombre . '</td>';
		$html .= '<td style="width: 75px;">';
		$html .= '<a class="btn btn-success btn-xs" ';
		$html .= 'onclick="sec_con_nuevo_aden_arrend_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'nombre\',\'Nombre\',\'varchar\',\'' . $nombre . '\',\'\',\'\');">';
		$html .= '<span class="fa fa-edit"></span> Editar';
		$html .= '</a>';
		$html .= '</td>';
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= '<td><b>Tipo de Documento de Identidad</b></td>';
		$html .= '<td>' . $tipo_docu_identidad . '</td>';
		$html .= '<td style="width: 75px;">';
		$html .= '<a class="btn btn-success btn-xs" ';
		$html .= 'onclick="sec_con_nuevo_aden_arrend_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'tipo_docu_identidad_id\',\'Tipo de Documento de Identidad\',\'select_option\',\'' . $tipo_docu_identidad . '\',\'obtener_tipo_docu_identidad\',\'\');">';
		$html .= '<span class="fa fa-edit"></span> Editar';
		$html .= '</a>';
		$html .= '</td>';
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= '<td><b>Número de ' . $tipo_docu_identidad . '</b></td>';
		$html .= '<td>' . $num_docu . '</td>';
		$html .= '<td style="width: 75px;">';
		$html .= '<a class="btn btn-success btn-xs" ';
		$html .= 'onclick="sec_con_nuevo_aden_arrend_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'num_docu\',\'Número de Documento de Identidad\',\'varchar\',\'' . $num_docu . '\',\'\',\'\');">';
		$html .= '<span class="fa fa-edit"></span> Editar';
		$html .= '</a>';
		$html .= '</td>';
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= '<td><b>Número de RUC</b></td>';
		$html .= '<td>' . $num_ruc . '</td>';
		$html .= '<td style="width: 75px;">';
		$html .= '<a class="btn btn-success btn-xs" ';
		$html .= 'onclick="sec_con_nuevo_aden_arrend_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'num_ruc\',\'RUC\',\'varchar\',\'' . $num_ruc . '\',\'\',\'\');">';
		$html .= '<span class="fa fa-edit"></span> Editar';
		$html .= '</a>';
		$html .= '</td>';
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= '<td><b>Domicilio del propietario</b></td>';
		$html .= '<td>' . $direccion . '</td>';
		$html .= '<td style="width: 75px;">';
		$html .= '<a class="btn btn-success btn-xs" ';
		$html .= 'onclick="sec_con_nuevo_aden_arrend_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'direccion\',\'Domicilio del propietario\',\'varchar\',\'' . $direccion . '\',\'\',\'\');">';
		$html .= '<span class="fa fa-edit"></span> Editar';
		$html .= '</a>';
		$html .= '</td>';
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= '<td><b>Representante Legal</b></td>';
		$html .= '<td>' . $representante_legal . '</td>';
		$html .= '<td style="width: 75px;">';
		$html .= '<a class="btn btn-success btn-xs" ';
		$html .= 'onclick="sec_con_nuevo_aden_arrend_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'representante_legal\',\'Representante Legal\',\'varchar\',\'' . $representante_legal . '\',\'\',\'\');">';
		$html .= '<span class="fa fa-edit"></span> Editar';
		$html .= '</a>';
		$html .= '</td>';
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= '<td><b>N° de Partida Registral de la empresa</b></td>';
		$html .= '<td>' . $num_partida_registral_per . '</td>';
		$html .= '<td style="width: 75px;">';
		$html .= '<a class="btn btn-success btn-xs" ';
		$html .= 'onclick="sec_con_nuevo_aden_arrend_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'num_partida_registral\',\'N° de Partida Registral de la empresa\',\'varchar\',\'' . $num_partida_registral_per . '\',\'\',\'\');">';
		$html .= '<span class="fa fa-edit"></span> Editar';
		$html .= '</a>';
		$html .= '</td>';
		$html .= '</tr>';

		$html .= '</table>';
		$html .= '</div>';
		$html .= '</div>';

		$nro++;
	}


	$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
	$html .= '<br>';
	$html .= '</div>';













	$query_detalle = "SELECT d.id, d.codigo, d.observaciones FROM cont_contrato_detalle d WHERE d.status = 1 AND d.contrato_id = " . $id_contrato_arrendamiento . " ORDER BY d.id ASC";
	$result_detalle = $mysqli->query($query_detalle);

	while ($detalle_con = $result_detalle->fetch_assoc()) {


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
	LEFT JOIN cont_tipo_pago_servicio t1 ON i.tipo_compromiso_pago_agua = t1.id
	LEFT JOIN cont_tipo_pago_servicio t2 ON i.tipo_compromiso_pago_luz = t2.id
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
    udep.cod_depa = SUBSTRING(i.ubigeo_id, 1, 2) 
    AND udep.cod_prov = '00' 
    AND udep.cod_dist = '00'
)
WHERE i.contrato_detalle_id = " . $detalle_con['id'] . " 
AND i.contrato_id = " . $id_contrato_arrendamiento . " 
GROUP BY 
    i.id, 
    i.ubicacion, 
    i.latitud, 
    i.longitud, 
    i.id_empresa_servicio_agua, 
    i.id_empresa_servicio_luz, 
    lspe1.razon_social, 
    lspe2.razon_social, 
    i.area_arrendada, 
    i.ubigeo_id, 
    i.num_partida_registral, 
    i.oficina_registral, 
    i.num_suministro_agua, 
    i.tipo_compromiso_pago_agua, 
    t1.nombre, 
    i.monto_o_porcentaje_agua, 
    i.num_suministro_luz, 
    i.tipo_compromiso_pago_luz, 
    t2.nombre, 
    i.monto_o_porcentaje_luz, 
    i.tipo_compromiso_pago_arbitrios, 
    ta.nombre, 
    i.porcentaje_pago_arbitrios";

		$sel_query = $mysqli->query($query);
		$row = $sel_query->fetch_assoc();
		$inmueble_id = $row["id"];
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
	c.pago_renta_id,
	c.cuota_variable,
	tpr.nombre AS pago_renta,
	tv.nombre AS tipo_venta,
	tai.nombre AS igv_en_la_renta,
	c.impuesto_a_la_renta_id,
	i.nombre AS impuesto_a_la_renta,
	c.carta_de_instruccion_id,
	ci.nombre AS carta_de_instruccion,
	c.numero_cuenta_detraccion,
	c.garantia_monto,
	c.tipo_adelanto_id,
	a.nombre AS tipo_adelanto,
	c.plazo_id,
	tp.nombre as nombre_plazo,
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
	LEFT JOIN cont_tipo_impuesto_a_la_renta i ON c.impuesto_a_la_renta_id = i.id
	LEFT JOIN cont_tipo_adelanto a ON c.tipo_adelanto_id = a.id
	LEFT JOIN cont_tipo_periodo_de_gracia p ON c.periodo_gracia_id = p.id
	LEFT JOIN cont_tipo_carta_de_instruccion ci ON c.carta_de_instruccion_id = ci.id
	LEFT JOIN cont_tipo_dia_de_pago dp ON c.dia_de_pago_id = dp.id
	LEFT JOIN cont_tipo_plazo tp ON c.plazo_id = tp.id
	LEFT JOIN cont_tipo_pago_renta tpr ON c.pago_renta_id = tpr.id
	LEFT JOIN cont_tipo_venta tv ON c.tipo_venta_id = tv.id
	LEFT JOIN cont_tipo_afectacion_igv tai ON c.afectacion_igv_id = tai.id
	WHERE c.status = 1 AND c.contrato_detalle_id = " . $detalle_con['id'] . " AND c.contrato_id = " . $id_contrato_arrendamiento;

		$sel_query = $mysqli->query($query);
		$row = $sel_query->fetch_assoc();

		$condicion_economica_id = $row["condicion_economica_id"];
		$simbolo_moneda = $row["simbolo_moneda"];
		$moneda_contrato = $row["moneda_contrato"];
		$monto_renta = $simbolo_moneda . ' ' . number_format($row["monto_renta"], 2, '.', ',');
		$garantia_monto = $simbolo_moneda . ' ' . number_format($row["garantia_monto"], 2, '.', ',');

		$monto_renta = $simbolo_moneda . ' ' . number_format($row["monto_renta"], 2, '.', ',');
		$monto_renta_sin_formato = $row["monto_renta"];
		$pago_renta_id = $row["pago_renta_id"];
		$pago_renta = $row["pago_renta"];
		$cuota_variable = ((float) $row["cuota_variable"]) . '%';
		$tipo_venta = $row["tipo_venta"];
		$igv_en_la_renta = $row["igv_en_la_renta"];

		$impuesto_a_la_renta_id = $row["impuesto_a_la_renta_id"];
		$impuesto_a_la_renta_texto = $row["impuesto_a_la_renta"];
		$carta_de_instruccion_id = $row["carta_de_instruccion_id"];
		$carta_de_instruccion = $row["carta_de_instruccion"];
		$numero_cuenta_detraccion = $row["numero_cuenta_detraccion"];


		$plazo_id = $row["plazo_id"];
		$nombre_plazo = $row["nombre_plazo"];
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

		// INICIO IMPUESTO A LA RENTA DETALLADO
		$factor = 1.05265;
		$renta_bruta = 0;
		$renta_neta = 0;
		$impuesto_a_la_renta = 0;
		$detalle = '';

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





		// $list_query = $mysqli->query($query);
		// $list = array();
		// while ($li = $list_query->fetch_assoc()) {
		// 	$list[] = $li;
		// }


		$html .= '
<div class="col-md-12">
	<div class="panel">		
		<div class="panel-heading">
			<div class="panel-title">CONTRATO</div>
		</div>
		<div class="panel-body m-0 p-0">';


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
		$html .= 'onclick="sec_con_nuevo_aden_arrend_solicitud_editar_campo_adenda(\'Inmueble\',\'cont_inmueble\',\'ubigeo_id\',\'Departamento/Provincia/Distrito\',\'select_option\',\'' . $departamento . "/" . $provincia . "/" . $distrito . '\',\'obtener_departamentos\',\'' . $inmueble_id . '\');">';
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
		$html .= '<td style="width: 75px;">';
		$html .= '<a class="btn btn-success btn-xs" ';
		$html .= 'onclick="sec_con_nuevo_aden_arrend_solicitud_editar_campo_adenda(\'Inmueble\',\'cont_inmueble\',\'ubicacion\',\'Ubicación\',\'varchar\',\'' . $ubicacion . '\',\'\',\'' . $inmueble_id . '\');">';
		$html .= '<span class="fa fa-edit"></span> Editar';
		$html .= '</a>';
		$html .= '</td>';
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= '<td><b>N° Partida Registral</b></td>';
		$html .= '<td>' . $num_partida_registral . '</td>';
		$html .= '<td style="width: 75px;">';
		$html .= '<a class="btn btn-success btn-xs" ';
		$html .= 'onclick="sec_con_nuevo_aden_arrend_solicitud_editar_campo_adenda(\'Inmueble\',\'cont_inmueble\',\'num_partida_registral\',\'N° Partida Registral\',\'varchar\',\'' . $num_partida_registral . '\',\'\',\'' . $inmueble_id . '\');">';
		$html .= '<span class="fa fa-edit"></span> Editar';
		$html .= '</a>';
		$html .= '</td>';
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= '<td><b>Oficina Registral (Sede)</b></td>';
		$html .= '<td>' . $oficina_registral . '</td>';
		$html .= '<td style="width: 75px;">';
		$html .= '<a class="btn btn-success btn-xs" ';
		$html .= 'onclick="sec_con_nuevo_aden_arrend_solicitud_editar_campo_adenda(\'Inmueble\',\'cont_inmueble\',\'oficina_registral\',\'Oficina Registral (Sede)\',\'varchar\',\'' . $oficina_registral . '\',\'\',\'' . $inmueble_id . '\');">';
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
		$html .= '<div class="h4"><b>CONDICIONES ECONÓMICAS Y COMERCIALES</b></div>';
		$html .= '</div>';

		$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
		$html .= '<div class="form-group">';
		$html .= '<table class="table table-bordered table-hover">';

		$html .= '<tr>';
		$html .= '<td><b>Moneda del contrato</b></td>';
		$html .= '<td>' . $moneda_contrato . '</td>';
		$html .= '<td style="width: 75px;">';
		$html .= '<a class="btn btn-success btn-xs" ';
		$html .= 'onclick="sec_con_nuevo_aden_arrend_solicitud_editar_campo_adenda(\'Condiciones económicas y comerciales\',\'cont_condicion_economica\',\'tipo_moneda_id\',\'Moneda del contrato\',\'select_option\',\'' . $moneda_contrato . '\',\'obtener_tipo_moneda\',\'' . $condicion_economica_id . '\');">';
		$html .= '<span class="fa fa-edit"></span> Editar';
		$html .= '</a>';
		$html .= '</td>';
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= '<td style="width: 250px;"><b>Pago de Renta</b></td>';
		$html .= '<td>' . $pago_renta . '</td>';
		$html .= '<td style="width: 75px;">';
		$html .= '<a class="btn btn-success btn-xs" onclick="sec_con_nuevo_aden_arrend_solicitud_editar_campo_adenda(\'Condiciones económicas y comerciales\',\'cont_condicion_economica\',\'pago_renta_id\',\'Pago de Renta\',\'select_option\',\'' . $pago_renta . '\',\'obtener_tipo_pago_renta\',\'' . $condicion_economica_id . '\');">';
		$html .= '<span class="fa fa-edit"></span> Editar';
		$html .= '</a>';
		$html .= '</td>';
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= '<td><b>Cuota Fija - Monto de Renta Pactada</b></td>';
		$html .= '<td>' . $monto_renta . '</td>';
		$html .= '<td>';
		$html .= '<a class="btn btn-success btn-xs" onclick="sec_con_nuevo_aden_arrend_solicitud_editar_campo_adenda(\'Condiciones económicas y comerciales\',\'cont_condicion_economica\',\'monto_renta\',\'Cuota Fija - Monto de Renta Pactada\',\'decimal\',\'' . $monto_renta . '\',\'\',\'' . $condicion_economica_id . '\');">';
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
		$html .= '<br>';
		$html .= '</div>';

		$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
		$html .= '<div class="h4"><b>VIGENCIA</b></div>';
		$html .= '</div>';

		$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
		$html .= '<div class="form-group">';
		$html .= '<table class="table table-bordered table-hover">';

		if ($plazo_id == 1) {
			$html .= '<tr>';
			$html .= '<td><b>Vigencia del Contrato</b></td>';
			$html .= '<td>' . $cant_meses_contrato . '</td>';
			$html .= '<td style="width: 75px;">';
			$html .= '<a class="btn btn-success btn-xs" ';
			$html .= 'onclick="sec_con_nuevo_aden_arrend_solicitud_editar_campo_adenda(\'Vigencia\',\'cont_condicion_economica\',\'cant_meses_contrato\',\'Vigencia del Contrato (En meses)\',\'int\',\'' . $cant_meses_contrato . '\',\'\',\'' . $condicion_economica_id . '\');">';
			$html .= '<span class="fa fa-edit"></span> Editar';
			$html .= '</a>';
			$html .= '</td>';
			$html .= '</tr>';
		}


		$html .= '<tr>';
		$html .= '<td><b>Contrato - Fecha de Inicio</b></td>';
		$html .= '<td>' . $contrato_inicio_fecha . '</td>';
		$html .= '<td style="width: 75px;">';
		$html .= '<a class="btn btn-success btn-xs" ';
		$html .= 'onclick="sec_con_nuevo_aden_arrend_solicitud_editar_campo_adenda(\'Vigencia\',\'cont_condicion_economica\',\'fecha_inicio\',\'Contrato - Fecha de Inicio\',\'date\',\'' . $contrato_inicio_fecha . '\',\'\',\'' . $condicion_economica_id . '\');">';
		$html .= '<span class="fa fa-edit"></span> Editar';
		$html .= '</a>';
		$html .= '</td>';
		$html .= '</tr>';

		if ($plazo_id == 1) {
			$html .= '<tr>';
			$html .= '<td><b>Contrato - Fecha de Fin</b></td>';
			$html .= '<td>' . $contrato_fin_fecha . '</td>';
			$html .= '<td style="width: 75px;">';
			$html .= '<a class="btn btn-success btn-xs" ';
			$html .= 'onclick="sec_con_nuevo_aden_arrend_solicitud_editar_campo_adenda(\'Vigencia\',\'cont_condicion_economica\',\'fecha_fin\',\'Contrato - Fecha de Fin\',\'date\',\'' . $contrato_fin_fecha . '\',\'\',\'' . $condicion_economica_id . '\');">';
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
		WHERE b.status = 1 AND b.contrato_detalle_id = " . $detalle_con['id'] . " AND b.contrato_id = " . $id_contrato_arrendamiento . "";
		$sel_query = $mysqli->query($query);

		$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
		$html .= '<div class="h4">';
		$html .= '<b>INFORMACION DEL PAGO</b>&nbsp;';

		$html .= '</div>';
		$html .= '</div>';

		$nro = 1;
		while ($row = $sel_query->fetch_assoc()) {
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


			$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
			$html .= '<div class="form-group">';
			$html .= '<table class="table table-bordered table-hover">';

			$html .= '<tr>';
			$html .= '<td><b>Nombre del Banco</b></td>';
			$html .= '<td>' . $ben_banco . '</td>';
			$html .= '<td style="width: 75px;">';
			$html .= '<a class="btn btn-success btn-xs" ';
			$html .= 'onclick="sec_con_nuevo_aden_arrend_solicitud_editar_campo_adenda(\'Beneficiario\',\'cont_beneficiarios\',\'banco_id\',\'Nombre del Banco\',\'select_option\',\'' . $ben_banco . '\',\'obtener_banco\',\'' . $beneficiario_id . '\');">';
			$html .= '<span class="fa fa-edit"></span> Editar';
			$html .= '</a>';
			$html .= '</td>';
			$html .= '</tr>';

			$html .= '<tr>';
			$html .= '<td><b>N° de la cuenta bancaria</b></td>';
			$html .= '<td>' . $ben_num_cuenta_bancaria . '</td>';
			$html .= '<td style="width: 75px;">';
			$html .= '<a class="btn btn-success btn-xs" ';
			$html .= 'onclick="sec_con_nuevo_aden_arrend_solicitud_editar_campo_adenda(\'Beneficiario\',\'cont_beneficiarios\',\'num_cuenta_bancaria\',\'N° de la cuenta bancaria\',\'varchar\',\'' . $ben_num_cuenta_bancaria . '\',\'\',\'' . $beneficiario_id . '\');">';
			$html .= '<span class="fa fa-edit"></span> Editar';
			$html .= '</a>';
			$html .= '</td>';
			$html .= '</tr>';

			$html .= '</table>';
			$html .= '</div>';
			$html .= '</div>';

			$nro++;
		}



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
		$html .= '<td style="width: 75px;">';
		$html .= '<a class="btn btn-success btn-xs" ';
		$html .= 'onclick="sec_con_nuevo_aden_arrend_solicitud_editar_campo_adenda(\'Fecha de suscripción\',\'cont_condicion_economica\',\'fecha_suscripcion\',\'Fecha de suscripción del contrato\',\'date\',\'' . $contrato_fecha_suscripcion . '\',\'\',\'' . $condicion_economica_id . '\');">';
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

		$html .= '
		</div>
	</div>
</div>';
		$html .= '</div>';
		$html .= '</div>
		</div>';
	}


	$result["status"] = 200;
	$result["messages"] = "Datos obtenidos de gestion.";
	$result["result"] = $html;
	echo json_encode($result);
	exit();
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

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipos_de_archivos") {
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

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_terminacion_anticipada") {
	$query = "SELECT * FROM cont_tipo_terminacion_anticipada WHERE status = 1";

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

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_bancos") {
	$query = "SELECT id, ifnull(nombre, '') nombre 
				FROM tbl_bancos
				WHERE estado = 1";

	$list_query = $mysqli->query($query);
	$list = array();
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}

	$result = array();
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_periodo") {
	$query = "SELECT * FROM cont_periodo WHERE estado = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	$result = array();
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_tipo_comprobante") {
	$query = "SELECT * FROM cont_tipo_comprobante WHERE estado = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	$result = array();
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_forma_pago") {
	$query = "SELECT * FROM cont_forma_pago WHERE estado = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	$result = array();
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_monedas") {
	$query = "SELECT id, nombre FROM tbl_moneda WHERE id IN (1,2) AND estado = 1";
	$list_query = $mysqli->query($query);
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	$result = array();
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
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
	$result = array();
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
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
	$result = array();
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
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
	$result = array();
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "listar_proveedores") {
	$proveedores = isset($_POST['proveedores']) ? $_POST['proveedores'] : [];

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

	for ($i = 0; $i < count($proveedores); $i++) {
		$html .= '
			<tr>
				<td class="text-left">' . $proveedores[$i]['dni_representante'] . '</td>
				<td class="text-left">' . $proveedores[$i]['nombre_representante'] . '</td>
				<td class="text-left">' . $proveedores[$i]['nro_cuenta_detraccion'] . '</td>
				<td class="text-left">' . $proveedores[$i]['banco_nombre'] . '</td>
				<td class="text-left">' . $proveedores[$i]['nro_cuenta'] . '</td>
				<td class="text-left">' . $proveedores[$i]['nro_cci'] . '</td>
				<td><input type="file" name="vigencia_nuevo_representante_' . $i . '" accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip"></td>
				<td><input type="file" name="dni_nuevo_representante_' . $i . '" accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip"></td>
				<td><button type="button" class="btn btn-sm btn-success" onclick="sec_con_nuevo_int_editar_proveedor(' . $i . ')"><i class="fa fa-edit"></i></button></td>
				<td><button type="button" class="btn btn-sm btn-danger" onclick="sec_con_nuevo_int_eliminar_proveedor(' . $i . ')"><i class="fa fa-close"></i></button></td>
			</tr>
			';
	}

	$html .= '
		</tbody>
	</table>';



	$result = array();
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $html;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "listar_contraprestaciones") {
	$contraprestaciones = isset($_POST['contraprestaciones']) ? $_POST['contraprestaciones'] : [];

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

	for ($i = 0; $i < count($contraprestaciones); $i++) {
		$html .= '
			<tr>
				<td class="text-left">' . $contraprestaciones[$i]['moneda_nombre'] . '</td>
				<td class="text-left">' . $contraprestaciones[$i]['subtotal'] . '</td>
				<td class="text-left">' . $contraprestaciones[$i]['igv'] . '</td>
				<td class="text-left">' . $contraprestaciones[$i]['monto'] . '</td>
				<td class="text-left">' . $contraprestaciones[$i]['forma_pago_detallado'] . '</td>
				<td class="text-left">' . $contraprestaciones[$i]['tipo_comprobante_nombre'] . '</td>
				<td class="text-left">' . $contraprestaciones[$i]['plazo_pago'] . '</td>
				<td><button type="button" class="btn btn-sm btn-success" onclick="sec_con_nuevo_int_editar_contraprestacion(' . $i . ')"><i class="fa fa-edit"></i></button></td>
				<td><button type="button" class="btn btn-sm btn-danger" onclick="sec_con_nuevo_int_eliminar_contraprestacion(' . $i . ')"><i class="fa fa-close"></i></button></td>
			</tr>
			';
	}

	$html .= '
		</tbody>
	</table>';



	$result = array();
	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestion.";
	$result["result"] = $html;
	echo json_encode($result);
	exit();
}
