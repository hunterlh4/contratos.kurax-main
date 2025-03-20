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
					$html .= '<a class="btn btn-success btn-xs" onclick="sec_con_nuevo_aden_mandat_reemplazar_propietario(' . $row["id"] . ')">';
					$html .= '<i class="fa fa-plus"></i> Agregar a la adenda como propietario';
					$html .= '</a>';
					$html .= '</td>';
				}
				if ($tipo_solicitud == 'NuevoPropietario') {
					$html .= '<td>';
					$html .= '<a class="btn btn-success btn-xs" onclick="sec_con_nuevo_aden_mandat_agignar_propietario(' . $row["id"] . ')">';
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
c.nombre MANDANTE,
d.nombre MANDATARIA,
concat('CODIGO: A', a.codigo_correlativo,' - MANDANTE: ',c.nombre, ' - MANDATARIA: ',d.nombre) as nombre
from cont_contrato a
LEFT JOIN cont_propietario b on b.contrato_id = a.contrato_id
LEFT JOIN cont_persona c on c.id = b.persona_id
LEFT JOIN tbl_razon_social d on d.id = a.empresa_suscribe_id
WHERE a.tipo_contrato_id = 14
AND a.status = 1
AND a.etapa_id = 5
ORDER BY a.contrato_id DESC
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

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_contrato_mandato_por_id") {

	$id_contrato_mandato = $_POST["contrato_id"];
    
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
						c.contrato_id IN (" . $id_contrato_mandato . ")");
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
		$created_at = $sel["created_at"];
		$user_created = $sel["user_created"];
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
			<div class="panel-title">DETALLE DE LA SOLICITUD DE CONTRATO DE mandato</div>
			<input type="hidden" value="' . $contrato_id . '" id="id_registro_contrato_id">
			<input type="hidden" value="' . $tipo_contrato_id . '" id="id_tipo_contrato">
		</div>
		<div class="panel-body p-0">';


    $html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
    $html .= '<div class="form-group">';
    $html .= '<table class="table table-bordered table-hover">';
    // $html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
    // $html .= '<div class="h4">';
    // $html .= '<b>DATOS GENERALES</b>&nbsp;';
    // $html .= '</div>';
    // $html .= '</div>';

    
	// $html .= '<tr>';
	// $html .= '<td style="width: 250px;"><b>Empresa Suscribe</b></td>';
	// $html .= '<td>' . $empresa_suscribe . '</td>';
	

	// $html .= '<tr>';
	// $html .= '<td style="width: 250px;"><b>Registrado por</b></td>';
	// $html .= '<td>' . $user_created . '</td>';
	// $html .= '</tr>';
	// $html .= '</div>';
	// $html .= '</div>';

	// $html .= '<tr>';
	// $html .= '<td style="width: 250px;"><b>Fecha de Registro</b></td>';
	// $html .= '<td>' . $created_at . '</td>';
	// $html .= '</tr>';
	// $html .= '</div>';
	// $html .= '</div>';


    //MANDANTE------------------------------------------------------------------------
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
	WHERE pr.status = 1 AND pr.contrato_id = " . $id_contrato_mandato . "";
	$propiertario_query = $mysqli->query($query);

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

		$html .= '</div>';
		$html .= '</div>';
		$html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
		$html .= '<div class="form-group">';
		$html .= '<table class="table table-bordered table-hover">';
        $html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
        $html .= '<div class="h4">';
        $html .= '<b>MANDANTE</b>&nbsp;';
        $html .= '</div>';
        $html .= '</div>';

		// $html .= '<tr>';
		// $html .= '<td style="width: 250px;"><b>Tipo de Persona</b></td>';
		// $html .= '<td>' . $tipo_persona . '</td>';
		// $html .= '<td style="width: 75px;">';
		// $html .= '<a class="btn btn-success btn-xs" ';
		// $html .= 'onclick="sec_con_nuevo_aden_mandat_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'tipo_persona_id\',\'Tipo de Persona\',\'select_option\',\'' . $tipo_persona . '\',\'obtener_tipo_persona\',\'\');">';
		// $html .= '<span class="fa fa-edit"></span> Editar';
		// $html .= '</a>';
		// $html .= '</td>';
		// $html .= '</tr>';

		$html .= '<tr>';
		$html .= '<td style="width: 250px;"><b>Nombre</b></td>';
		$html .= '<td>' . $nombre . '</td>';
		// $html .= '<td style="width: 75px;">';
		// $html .= '<a class="btn btn-success btn-xs" ';
		// $html .= 'onclick="sec_con_nuevo_aden_mandat_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'nombre\',\'Nombre\',\'varchar\',\'' . $nombre . '\',\'\',\'\');">';
		// $html .= '<span class="fa fa-edit"></span> Editar';
		// $html .= '</a>';
		// $html .= '</td>';
		$html .= '</tr>';

		// $html .= '<tr>';
		// $html .= '<td style="width: 250px;"><b>Tipo de Documento de Identidad</b></td>';
		// $html .= '<td>' . $tipo_docu_identidad . '</td>';
		// $html .= '<td style="width: 75px;">';
		// $html .= '<a class="btn btn-success btn-xs" ';
		// $html .= 'onclick="sec_con_nuevo_aden_mandat_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'tipo_docu_identidad_id\',\'Tipo de Documento de Identidad\',\'select_option\',\'' . $tipo_docu_identidad . '\',\'obtener_tipo_docu_identidad\',\'\');">';
		// $html .= '<span class="fa fa-edit"></span> Editar';
		// $html .= '</a>';
		// $html .= '</td>';
		// $html .= '</tr>';

		$html .= '<tr>';
		$html .= '<td style="width: 250px;"><b>Número de RUC</b></td>';
		$html .= '<td>' . $num_ruc . '</td>';
		// $html .= '<td style="width: 75px;">';
		// $html .= '<a class="btn btn-success btn-xs" ';
		// $html .= 'onclick="sec_con_nuevo_aden_mandat_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'num_ruc\',\'RUC\',\'varchar\',\'' . $num_ruc . '\',\'\',\'\');">';
		// $html .= '<span class="fa fa-edit"></span> Editar';
		// $html .= '</a>';
		// $html .= '</td>';
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= '<td style="width: 250px;"><b>Domicilio del propietario</b></td>';
		$html .= '<td>' . $direccion . '</td>';
		// $html .= '<td style="width: 75px;">';
		// $html .= '<a class="btn btn-success btn-xs" ';
		// $html .= 'onclick="sec_con_nuevo_aden_mandat_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'direccion\',\'Domicilio del propietario\',\'varchar\',\'' . $direccion . '\',\'\',\'\');">';
		// $html .= '<span class="fa fa-edit"></span> Editar';
		// $html .= '</a>';
		// $html .= '</td>';
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= '<td style="width: 250px;"><b>Representante Legal</b></td>';
		$html .= '<td>' . $representante_legal . '</td>';
		// $html .= '<td style="width: 75px;">';
		// $html .= '<a class="btn btn-success btn-xs" ';
		// $html .= 'onclick="sec_con_nuevo_aden_mandat_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'representante_legal\',\'Representante Legal\',\'varchar\',\'' . $representante_legal . '\',\'\',\'\');">';
		// $html .= '<span class="fa fa-edit"></span> Editar';
		// $html .= '</a>';
		// $html .= '</td>';
		$html .= '</tr>';

		// $html .= '<tr>';
		// $html .= '<td style="width: 250px;"><b>N° de Partida Registral de la empresa</b></td>';
		// $html .= '<td>' . $num_partida_registral_per . '</td>';
		// $html .= '<td style="width: 75px;">';
		// $html .= '<a class="btn btn-success btn-xs" ';
		// $html .= 'onclick="sec_con_nuevo_aden_mandat_solicitud_editar_campo_adenda(\'Propietario\',\'cont_persona\',\'num_partida_registral\',\'N° de Partida Registral de la empresa\',\'varchar\',\'' . $num_partida_registral_per . '\',\'\',\'\');">';
		// $html .= '<span class="fa fa-edit"></span> Editar';
		// $html .= '</a>';
		// $html .= '</td>';
		// $html .= '</tr>';

		$html .= '</table>';
		$html .= '</div>';
		$html .= '</div>';

		$nro++;
	}

            // MANDATARIA ------------------------------------
            $query = "
            SELECT 
                p.id, 
                p.tipo_docu_identidad_id, 
                td.nombre AS tipo_docu_identidad, 
                p.num_docu, 
                p.num_ruc, 
                p.nombre, 
                p.direccion, 
                p.representante_legal, 
                p.num_partida_registral 
            FROM 
                cont_contrato pr 
            INNER JOIN 
                tbl_razon_social p ON pr.empresa_suscribe_id = p.id 
            INNER JOIN 
                cont_tipo_docu_identidad td ON p.tipo_docu_identidad_id = td.id 
            WHERE 
                pr.contrato_id IN (" . $contrato_id . ") 
            LIMIT 1;
        ";

        $sel_query = $mysqli->query($query);
        $sel = $sel_query->fetch_assoc();
        $btn_editar_solicitud = true;
        
        if ($sel) {
            $tipo_docu_identidad_id = $sel["tipo_docu_identidad_id"];
            $tipo_docu_identidad = $sel["tipo_docu_identidad"];
            $num_docu = $sel["num_docu"];
            $num_ruc = $sel["num_ruc"];
            $nombre = $sel["nombre"];
            $direccion = $sel["direccion"];
            $representante_legal = $sel["representante_legal"];
            $num_partida_registral = $sel["num_partida_registral"];

            $html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
            $html .= '<div class="form-group">';
            $html .= '<table class="table table-bordered table-hover">';
            $html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
            $html .= '<div class="h4">';
            $html .= '<b>MANDATARIA</b>&nbsp;';
            $html .= '</div>';
            $html .= '</div>';

            // Nombre
            $html .= '<tr>';
            $html .= '<td style="width: 250px;"><b>Nombre</b></td>';
            $html .= '<td>' . $nombre . '</td>';
            // if ($btn_editar_solicitud) {
            //     $html .= '<td style="width: 75px;">';
            //     $html .= '<a class="btn btn-success btn-xs" onclick="sec_con_nuevo_aden_mandat_solicitud_editar_campo_adenda(\'Mandataria\',\'tbl_razon_social\',\'nombre\',\'Nombre\',\'varchar\',\'' . $nombre . '\',\'\',\'' . $sel["id"] . '\');">';
            //     $html .= '<span class="fa fa-edit"></span> Editar';
            //     $html .= '</a>';
            //     $html .= '</td>';
            // }
            $html .= '</tr>';

            // $html .= '<tr>';
            // $html .= '<td><b>Tipo de Documento de Identidad</b></td>';
            // $html .= '<td>' . $tipo_docu_identidad . '</td>';
            // if ($btn_editar_solicitud) {
            //     $html .= '<td>';
            //     $html .= '<a class="btn btn-success btn-xs" onclick="sec_con_nuevo_aden_mandat_solicitud_editar_campo_adenda(\'Mandataria\',\'tbl_razon_social\',\'tipo_docu_identidad_id\',\'Tipo de Documento de Identidad\',\'select_option\',\'' . $tipo_docu_identidad . '\',\'obtener_tipo_docu_identidad\',\'' . $sel["id"] . '\');">';
            //     $html .= '<span class="fa fa-edit"></span> Editar';
            //     $html .= '</a>';
            //     $html .= '</td>';
            // }
            // $html .= '</tr>';

            // Número de Documento de Identidad (si no es RUC)
            if ($tipo_docu_identidad_id != "2") {
                $html .= '<tr>';
                $html .= '<td><b>Número de ' . $tipo_docu_identidad . '</b></td>';
                $html .= '<td>' . $num_docu . '</td>';
                // if ($btn_editar_solicitud) {
                //     $html .= '<td>';
                //     $html .= '<a class="btn btn-success btn-xs" onclick="sec_con_nuevo_aden_mandat_solicitud_editar_campo_adenda(\'Mandataria\',\'tbl_razon_social\',\'num_docu\',\'Número de Documento de Identidad\',\'varchar\',\'' . $num_docu . '\',\'\',\'' . $sel["id"] . '\');">';
                //     $html .= '<span class="fa fa-edit"></span> Editar';
                //     $html .= '</a>';
                //     $html .= '</td>';
                // }
                $html .= '</tr>';
            }

            // Número de RUC
            $html .= '<tr>';
            $html .= '<td><b>Número de RUC</b></td>';
            $html .= '<td>' . $num_ruc . '</td>';
            // if ($btn_editar_solicitud) {
            //     $html .= '<td>';
            //     $html .= '<a class="btn btn-success btn-xs" onclick="sec_con_nuevo_aden_mandat_solicitud_editar_campo_adenda(\'Mandataria\',\'tbl_razon_social\',\'num_ruc\',\'Número de RUC\',\'varchar\',\'' . $num_ruc . '\',\'\',\'' . $sel["id"] . '\');">';
            //     $html .= '<span class="fa fa-edit"></span> Editar';
            //     $html .= '</a>';
            //     $html .= '</td>';
            // }
            $html .= '</tr>';

            // Domicilio
            $html .= '<tr>';
            $html .= '<td><b>Domicilio</b></td>';
            $html .= '<td>' . $direccion . '</td>';
            // if ($btn_editar_solicitud) {
            //     $html .= '<td>';
            //     $html .= '<a class="btn btn-success btn-xs" onclick="sec_con_nuevo_aden_mandat_solicitud_editar_campo_adenda(\'Mandataria\',\'tbl_razon_social\',\'direccion\',\'Domicilio\',\'varchar\',\'' . $direccion . '\',\'\',\'' . $sel["id"] . '\');">';
            //     $html .= '<span class="fa fa-edit"></span> Editar';
            //     $html .= '</a>';
            //     $html .= '</td>';
            // }
            $html .= '</tr>';



            $html .= '<tr>';
            $html .= '<td><b>Representante Legal</b></td>';
            $html .= '<td>' . $representante_legal . '</td>';
            // if ($btn_editar_solicitud) {
            //     $html .= '<td>';
            //     $html .= '<a class="btn btn-success btn-xs" onclick="sec_con_nuevo_aden_mandat_solicitud_editar_campo_adenda(\'Mandataria\',\'tbl_razon_social\',\'representante_legal\',\'Representante Legal\',\'varchar\',\'' . $representante_legal . '\',\'\',\'' . $sel["id"] . '\');">';
            //     $html .= '<span class="fa fa-edit"></span> Editar';
            //     $html .= '</a>';
            //     $html .= '</td>';
            // }
            $html .= '</tr>';

            // $html .= '<tr>';
            // $html .= '<td><b>N° de Partida Registral</b></td>';
            // $html .= '<td>' . $num_partida_registral . '</td>';
            // if ($btn_editar_solicitud) {
            //     $html .= '<td>';
            //     $html .= '<a class="btn btn-success btn-xs" onclick="sec_con_nuevo_aden_mandat_solicitud_editar_campo_adenda(\'Mandataria\',\'tbl_razon_social\',\'num_partida_registral\',\'N° de Partida Registral\',\'varchar\',\'' . $num_partida_registral . '\',\'\',\'' . $sel["id"] . '\');">';
            //     $html .= '<span class="fa fa-edit"></span> Editar';
            //     $html .= '</a>';
            //     $html .= '</td>';
            // }
            // $html .= '</tr>';
        }

        //CONTRATO----------------------------------------------------------------------------------

        $sel_query_mandato = $mysqli->query("
            SELECT
                man.idmandato as id,
                man.mandante_antecedente, 
                man.mandataria_objetivo, 
                man.mandataria_retribucion, 
                man.fecha_inicio, 
                man.fecha_fin, 
                man.plazo_duracion, 
                cc.fecha_suscripcion_contrato as contrato_fecha_suscripcion 
            FROM cont_mandato man 
            INNER JOIN cont_contrato cc ON cc.contrato_id = man.idcontrato 
            WHERE man.idcontrato IN (" . $contrato_id . ") 
            LIMIT 1
        ");

        if ($row_mandato = $sel_query_mandato->fetch_assoc()) {
            $mandato_id = $row_mandato["id"];
            $mandante_antecedente = $row_mandato["mandante_antecedente"];
            $mandataria_objetivo = $row_mandato["mandataria_objetivo"];
            $mandataria_retribucion = $row_mandato["mandataria_retribucion"];
            $fecha_inicio = $row_mandato["fecha_inicio"];
            $fecha_fin = $row_mandato["fecha_fin"];
            $plazo_duracion = $row_mandato["plazo_duracion"];
            $contrato_fecha_suscripcion = $row_mandato["contrato_fecha_suscripcion"];

            $html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
            $html .= '<div class="form-group">';
            $html .= '<table class="table table-bordered table-hover">';
            $html .= '<div class="col-xs-12 col-md-12 col-lg-12">';
            $html .= '<div class="h4">';
            $html .= '<b>CONTRATO</b>&nbsp;';
            $html .= '</div>';
            $html .= '</div>';

            $html .= '<tr>';
            $html .= '<td style="width: 250px;"><b>La mandante es:</b></td>';
            $html .= '<td>' . $mandante_antecedente . '</td>';
            $html .= '<td style="width: 75px;">';
            $html .= '<a class="btn btn-success btn-xs" onclick="sec_con_nuevo_aden_mandat_solicitud_editar_campo_adenda(\'Antecedentes\',\'cont_mandato\',\'mandante_antecedente\',\'Nombre\',\'varchar\',\'' . $mandante_antecedente . '\',\'\',\'' . $mandato_id . '\');">';
            $html .= '<span class="fa fa-edit"></span> Editar';
            $html .= '</a>';
            $html .= '</td>';
            $html .= '</tr>';

            $html .= '<tr>';
            $html .= '<td style="width: 250px;"><b>La mandataria se obliga a:</b></td>';
            $html .= '<td>' . $mandataria_objetivo . '</td>';
            $html .= '<td style="width: 75px;">';
            $html .= '<a class="btn btn-success btn-xs" onclick="sec_con_nuevo_aden_mandat_solicitud_editar_campo_adenda(\'Objeto del Contrato\',\'cont_mandato\',\'mandataria_objetivo\',\'Nombre\',\'varchar\',\'' . $mandataria_objetivo . '\',\'\',\'' . $mandato_id . '\');">';
            $html .= '<span class="fa fa-edit"></span> Editar';
            $html .= '</a>';
            $html .= '</td>';
            $html .= '</tr>';

            $html .= '<tr>';
            $html .= '<td style="width: 250px;"><b>De contraprestación por los servicios por la mandataria será el:</b></td>';
            $html .= '<td>' . $mandataria_retribucion . '</td>';
            $html .= '<td style="width: 75px;">';
            $html .= '<a class="btn btn-success btn-xs" onclick="sec_con_nuevo_aden_mandat_solicitud_editar_campo_adenda(\'Retribución\',\'cont_mandato\',\'mandataria_retribucion\',\'Contraprestación\',\'varchar\',\'' . $mandataria_retribucion . '\',\'\',\'' . $mandato_id . '\');">';
            $html .= '<span class="fa fa-edit"></span> Editar';
            $html .= '</a>';
            $html .= '</td>';
            $html .= '</tr>';

            $html .= '<tr>';
            $html .= '<td style="width: 250px;"><b>Plazo de duración:</b></td>';
            $html .= '<td>' . $plazo_duracion . '</td>';
            $html .= '<td style="width: 75px;">';
            $html .= '<a class="btn btn-success btn-xs" onclick="sec_con_nuevo_aden_mandat_solicitud_editar_campo_adenda(\'Plazo del Contrato\',\'cont_mandato\',\'plazo_duracion\',\'Duración\',\'int\',\'' . $plazo_duracion . '\',\'\',\'' . $mandato_id . '\');">';
            $html .= '<span class="fa fa-edit"></span> Editar';
            $html .= '</a>';
            $html .= '</td>';
            $html .= '</tr>';

            $html .= '<tr>';
            $html .= '<td style="width: 250px;"><b>Fecha de Inicio:</b></td>';
            $html .= '<td>' . $fecha_inicio . '</td>';
            $html .= '<td style="width: 75px;">';
            $html .= '<a class="btn btn-success btn-xs" onclick="sec_con_nuevo_aden_mandat_solicitud_editar_campo_adenda(\'Plazo del Contrato\',\'cont_mandato\',\'fecha_inicio\',\'Fecha de Inicio\',\'date\',\'' . $fecha_inicio . '\',\'\',\'' . $mandato_id . '\');">';
            $html .= '<span class="fa fa-edit"></span> Editar';
            $html .= '</a>';
            $html .= '</td>';
            $html .= '</tr>';

            $html .= '<tr>';
            $html .= '<td style="width: 250px;"><b>Fecha de Fin:</b></td>';
            $html .= '<td>' . $fecha_fin . '</td>';
            $html .= '<td style="width: 75px;">';
            $html .= '<a class="btn btn-success btn-xs" onclick="sec_con_nuevo_aden_mandat_solicitud_editar_campo_adenda(\'Plazo del Contrato\',\'cont_mandato\',\'fecha_fin\',\'Fecha de Fin\',\'date\',\'' . $fecha_fin . '\',\'\',\'' . $mandato_id . '\');">';
            $html .= '<span class="fa fa-edit"></span> Editar';
            $html .= '</a>';
            $html .= '</td>';
            $html .= '</tr>';

            $html .= '<tr>';
            $html .= '<td style="width: 250px;"><b>Fecha de suscripción:</b></td>';
            $html .= '<td>' . $contrato_fecha_suscripcion . '</td>';
            $html .= '<td style="width: 75px;">';
            $html .= '<a class="btn btn-success btn-xs" onclick="sec_con_nuevo_aden_mandat_solicitud_editar_campo_adenda(\'Fecha de suscripción\',\'cont_contrato\',\'fecha_suscripcion_contrato\',\'Fecha de suscripción del contrato\',\'date\',\'' . $contrato_fecha_suscripcion . '\',\'\',\'\');">';
            $html .= '<span class="fa fa-edit"></span> Editar';
            $html .= '</a>';
            $html .= '</td>';
            $html .= '</tr>';
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
