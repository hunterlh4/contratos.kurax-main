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

    // Consulta principal usando `cont_contrato` como tabla maestra
    $query = "
        SELECT 
            a.contrato_id as id, 
            c.nombre as mutuante_nombre, 
            d.nombre as mutuatario_nombre, 
            CONCAT('CODIGO: A', a.codigo_correlativo, ' - Mutuante: ', c.nombre, ' - Mutuatario: ', d.nombre) as nombre 
        FROM 
            cont_contrato a 
            LEFT JOIN cont_mutuodinero m ON m.idcontrato = a.contrato_id 
            LEFT JOIN cont_propietario b ON b.contrato_id = a.contrato_id 
            LEFT JOIN cont_persona c ON c.id = b.persona_id 
            LEFT JOIN tbl_razon_social d ON d.id = a.empresa_suscribe_id 
        WHERE 
            a.tipo_contrato_id = 15
            AND a.status = 1 
            AND a.etapa_id = 5 
        ORDER BY 
            a.contrato_id DESC
    ";

    $list_query = $mysqli->query($query);
    $list = [];

    while ($li = $list_query->fetch_assoc()) {
        $list[] = $li;
    }

    $result["status"] = 200;
    $result["message"] = "Datos obtenidos de gestión.";
    $result["result"] = $list;
    echo json_encode($result);
    exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_contrato_mutuodinero_por_id") {
    $id_contrato_mutuodinero = $_POST["contrato_id"];

    // Consulta principal para obtener los detalles del contrato de mutuo de dinero
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
            CONCAT(IFNULL(pgiro.nombre, ''), ' ', IFNULL(pgiro.apellido_paterno, ''), ' ', IFNULL(pgiro.apellido_materno, '')) AS persona_verificaciongiro, 
            CONCAT(IFNULL(p2.nombre, ''), ' ', IFNULL(p2.apellido_paterno, ''), ' ', IFNULL(p2.apellido_materno, '')) AS persona_responsable, 
            CONCAT(IFNULL(pjc.nombre, ''), ' ', IFNULL(pjc.apellido_paterno, ''), ' ', IFNULL(pjc.apellido_materno, '')) AS jefe_comercial, 
            c.jefe_comercial_id, 
            c.nombre_tienda, 
            c.observaciones, 
            c.user_created_id, 
            CONCAT(IFNULL(p.nombre, ''), ' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS user_created, 
            CONCAT(IFNULL(pa.nombre, ''), ' ', IFNULL(pa.apellido_paterno, ''), ' ', IFNULL(pa.apellido_materno, '')) AS abogado, 
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
            c.contrato_id IN (" . $id_contrato_mutuodinero . ")
    ");

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

        // Asignar valores por defecto si están vacíos
        if (empty($nombre_tienda)) { $nombre_tienda = 'Sin asignar'; }
        if (empty($centro_de_costos)) { $centro_de_costos = 'Sin asignar'; }
        if (empty($supervisor)) { $supervisor = 'Sin asignar'; }
        if (empty($jefe_comercial)) { $jefe_comercial = 'Sin asignar'; }
    }

    $tipo_persona = "";
    $html = '';
    $html .= '
    <div class="panel">
        <div class="panel-heading">
            <div class="panel-title">DETALLE DE LA SOLICITUD DE CONTRATO DE MUTUO DE DINERO</div>
            <input type="hidden" value="' . $contrato_id . '" id="id_registro_contrato_id">
            <input type="hidden" value="' . $tipo_contrato_id . '" id="id_tipo_contrato">
        </div>
        <div class="panel-body p-0">';

    // MANDANTE (Mutuante) ------------------------------------------------------------------------
    $query = "
        SELECT 
            pr.propietario_id, 
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
        FROM 
            cont_propietario pr 
            INNER JOIN cont_persona p ON pr.persona_id = p.id 
            INNER JOIN cont_tipo_persona tp ON p.tipo_persona_id = tp.id 
            INNER JOIN cont_tipo_docu_identidad td ON p.tipo_docu_identidad_id = td.id 
        WHERE 
            pr.status = 1 
            AND pr.contrato_id = " . $id_contrato_mutuodinero . "
    ";
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

        $html .= '
        <div class="col-xs-12 col-md-12 col-lg-12">
            <div class="form-group">
                <table class="table table-bordered table-hover">
                    <div class="col-xs-12 col-md-12 col-lg-12">
                        <div class="h4">
                            <b>MUTUANTE</b>&nbsp;
                        </div>
                    </div>
                    <tr>
                        <td style="width: 250px;"><b>Nombre</b></td>
                        <td>' . $nombre . '</td>
                    </tr>
                    <tr>
                        <td style="width: 250px;"><b>Número de RUC</b></td>
                        <td>' . $num_ruc . '</td>
                    </tr>
                    <tr>
                        <td style="width: 250px;"><b>Domicilio</b></td>
                        <td>' . $direccion . '</td>
                    </tr>
                    <tr>
                        <td style="width: 250px;"><b>Representante Legal</b></td>
                        <td>' . $representante_legal . '</td>
                    </tr>
                </table>
            </div>
        </div>';
        $nro++;
    }

    // MANDATARIA (Mutuatario) ------------------------------------
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
            INNER JOIN tbl_razon_social p ON pr.empresa_suscribe_id = p.id 
            INNER JOIN cont_tipo_docu_identidad td ON p.tipo_docu_identidad_id = td.id 
        WHERE 
            pr.contrato_id IN (" . $contrato_id . ") 
        LIMIT 1;
    ";
    $sel_query = $mysqli->query($query);
    $sel = $sel_query->fetch_assoc();
    $btn_editar_solicitud = true;
    // var_dump($contrato_id);
    // die("hasta aqui llego");
    if ($sel) {
        $tipo_docu_identidad_id = $sel["tipo_docu_identidad_id"];
        $tipo_docu_identidad = $sel["tipo_docu_identidad"];
        $num_docu = $sel["num_docu"];
        $num_ruc = $sel["num_ruc"];
        $nombre = $sel["nombre"];
        $direccion = $sel["direccion"];
        $representante_legal = $sel["representante_legal"];
        $num_partida_registral = $sel["num_partida_registral"];

        $html .= '
        <div class="col-xs-12 col-md-12 col-lg-12">
            <div class="form-group">
                <table class="table table-bordered table-hover">
                    <div class="col-xs-12 col-md-12 col-lg-12">
                        <div class="h4">
                            <b>MUTUATARIO</b>&nbsp;
                        </div>
                    </div>
                    <tr>
                        <td style="width: 250px;"><b>Nombre</b></td>
                        <td>' . $nombre . '</td>
                    </tr>';
        if ($tipo_docu_identidad_id != "2") {
            $html .= '
                    <tr>
                        <td><b>Número de ' . $tipo_docu_identidad . '</b></td>
                        <td>' . $num_docu . '</td>
                    </tr>';
        }
        $html .= '
                    <tr>
                        <td><b>Número de RUC</b></td>
                        <td>' . $num_ruc . '</td>
                    </tr>
                    <tr>
                        <td><b>Domicilio</b></td>
                        <td>' . $direccion . '</td>
                    </tr>
                    <tr>
                        <td><b>Representante Legal</b></td>
                        <td>' . $representante_legal . '</td>
                    </tr>
                </table>
            </div>
        </div>';
    }

    // CONTRATO DE MUTUO DE DINERO ---------------------------------------------------------------
    $sel_query_mutuodinero = $mysqli->query("
        SELECT 
            m.idmutuodinero as id, 
            m.mutuante_descripcion, 
            m.mutuatario_descripcion, 
            m.tasa_interes, 
            m.plazo_devolucion, 
            c.fecha_suscripcion_contrato as contrato_fecha_suscripcion 
        FROM 
            cont_mutuodinero m 
            INNER JOIN cont_contrato c ON c.contrato_id = m.idcontrato 
        WHERE 
            m.idcontrato IN (" . $id_contrato_mutuodinero . ") 
        LIMIT 1
    ");

    if ($row_mutuodinero = $sel_query_mutuodinero->fetch_assoc()) {
        $mutuodinero_id = $row_mutuodinero["id"];
        $mutuante_descripcion = $row_mutuodinero["mutuante_descripcion"];
        $mutuatario_descripcion = $row_mutuodinero["mutuatario_descripcion"];
        $tasa_interes = $row_mutuodinero["tasa_interes"];
        $plazo_devolucion = $row_mutuodinero["plazo_devolucion"];
        $contrato_fecha_suscripcion = $row_mutuodinero["contrato_fecha_suscripcion"];

        $html .= '
        <div class="col-xs-12 col-md-12 col-lg-12">
            <div class="form-group">
                <table class="table table-bordered table-hover">
                    <div class="col-xs-12 col-md-12 col-lg-12">
                        <div class="h4">
                            <b>CONTRATO DE MUTUO DE DINERO</b>&nbsp;
                        </div>
                    </div>
                    <tr>
                        <td style="width: 250px;"><b>Descripción del Mutuante:</b></td>
                        <td>' . $mutuante_descripcion . '</td>
                        <td style="width: 75px;">
                            <a class="btn btn-success btn-xs" onclick="sec_con_nuevo_aden_mutuodinero_solicitud_editar_campo_adenda(\'Descripción del Mutuante\', \'cont_mutuodinero\', \'mutuante_descripcion\', \'Descripción\', \'varchar\', \'' . $mutuante_descripcion . '\', \'\', \'' . $mutuodinero_id . '\');">
                                <span class="fa fa-edit"></span> Editar
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 250px;"><b>Descripción del Mutuatario:</b></td>
                        <td>' . $mutuatario_descripcion . '</td>
                        <td style="width: 75px;">
                            <a class="btn btn-success btn-xs" onclick="sec_con_nuevo_aden_mutuodinero_solicitud_editar_campo_adenda(\'Descripción del Mutuatario\', \'cont_mutuodinero\', \'mutuatario_descripcion\', \'Descripción\', \'varchar\', \'' . $mutuatario_descripcion . '\', \'\', \'' . $mutuodinero_id . '\');">
                                <span class="fa fa-edit"></span> Editar
                            </a>
                        </td>
                    </tr>

                    <tr>
                        <td style="width: 250px;"><b>Plazo de Devolución:</b></td>
                        <td>' . $plazo_devolucion . '</td>
                        <td style="width: 75px;">
                            <a class="btn btn-success btn-xs" onclick="sec_con_nuevo_aden_mutuodinero_solicitud_editar_campo_adenda(\'Plazo de Devolución\', \'cont_mutuodinero\', \'plazo_devolucion\', \'Plazo\', \'date\', \'' . $plazo_devolucion . '\', \'\', \'' . $mutuodinero_id . '\');">
                                <span class="fa fa-edit"></span> Editar
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 250px;"><b>Fecha de Suscripción:</b></td>
                        <td>' . $contrato_fecha_suscripcion . '</td>
                        <td style="width: 75px;">
                            <a class="btn btn-success btn-xs" onclick="sec_con_nuevo_aden_mutuodinero_solicitud_editar_campo_adenda(\'Fecha de Suscripción\', \'cont_contrato\', \'fecha_suscripcion_contrato\', \'Fecha\', \'date\', \'' . $contrato_fecha_suscripcion . '\', \'\', \'\');">
                                <span class="fa fa-edit"></span> Editar
                            </a>
                        </td>
                    </tr>
                </table>
            </div>
        </div>';
    }

            // CONTRATO DE MUTUO DE DINERO ---------------------------------------------------------------
        $sel_query_mutuodinero = $mysqli->query("
        SELECT 
            cp.moneda_id,
            cp.monto,
            CONCAT(tm.simbolo, ' ', cp.monto, ' (', tm.sigla, ')') as montostr,
            tm.nombre as moneda,
            cb.nombre as beneficiarionombre,
            cb.num_cuenta_bancaria,
            tb.nombre as banconombre
        FROM 
            cont_contrato cc
            INNER JOIN cont_contraprestacion cp ON cp.contrato_id = cc.contrato_id
            INNER JOIN tbl_moneda tm ON tm.id = cp.moneda_id
            INNER JOIN cont_beneficiarios cb ON cb.contrato_id = cc.contrato_id
            INNER JOIN tbl_bancos tb ON tb.id = cb.banco_id
        WHERE 
            cc.contrato_id = " . $id_contrato_mutuodinero . "
        ");
        
        if ($row_mutuodinero = $sel_query_mutuodinero->fetch_assoc()) {
            $moneda_id = $row_mutuodinero["moneda_id"];
            $montostr = $row_mutuodinero["montostr"];
            $moneda = $row_mutuodinero["moneda"];
            $num_cuenta_bancaria = $row_mutuodinero["num_cuenta_bancaria"];
            $banconombre = $row_mutuodinero["banconombre"];
            // var_dump($row_mutuodinero);
            // var_dump($id_contrato_mutuodinero);
    
            // die("murio aqui");
            
        $html .= '

            <div class="col-xs-12 col-md-12 col-lg-12">
                        <div class="form-group">
                            <table class="table table-bordered table-hover">
                                <div class="col-xs-12 col-md-12 col-lg-12">
                                    <div class="h4">
                                        <b>IMPORTE DINERARIO</b>&nbsp;
                                    </div>
                                </div>
                                <tr>
                                    <td style="width: 250px;"><b>Moneda</b></td>
                                    <td>' . $moneda . '</td>
                                    <td style="width: 75px;">
                                        <a class="btn btn-success btn-xs" onclick="sec_con_nuevo_aden_mutuodinero_solicitud_editar_campo_adenda(\'Importe Dinerario\', \'cont_contraprestacion\', \'moneda_id\', \'Moneda\', \'select_option\', \'' . $moneda . '\', \'obtener_tipo_moneda\', \'' . $moneda_id . '\');">
                                            <span class="fa fa-edit"></span> Editar
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 250px;"><b>Monto</b></td>
                                    <td>' . $montostr . '</td>
                                    <td style="width: 75px;">
                                        <a class="btn btn-success btn-xs" onclick="sec_con_nuevo_aden_mutuodinero_solicitud_editar_campo_adenda(\'Importe Dinerario\', \'cont_contraprestacion\', \'monto\', \'Monto\', \'decimal\', \'' . $montostr . '\', \'\', \'' . $moneda_id . '\');">
                                            <span class="fa fa-edit"></span> Editar
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 250px;"><b>Banco</b></td>
                                    <td>' . $banconombre . '</td>
                                    <td style="width: 75px;">
                                        <a class="btn btn-success btn-xs" onclick="sec_con_nuevo_aden_mutuodinero_solicitud_editar_campo_adenda(\'Importe Dinerario\', \'cont_beneficiarios\', \'banco_id\', \'Banco\', \'select_option\', \'' . $banconombre . '\', \'obtener_banco\', \'' . $contrato_id . '\');">
                                            <span class="fa fa-edit"></span> Editar
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="width: 250px;"><b>N° Cuenta Bancaria</b></td>
                                    <td>' . $num_cuenta_bancaria . '</td>
                                    <td style="width: 75px;">
                                        <a class="btn btn-success btn-xs" onclick="sec_con_nuevo_aden_mutuodinero_solicitud_editar_campo_adenda(\'Importe Dinerario\', \'cont_beneficiarios\', \'num_cuenta_bancaria\', \'N° Cuenta Bancaria\', \'int\', \'' . $num_cuenta_bancaria . '\', \'\', \'' . $contrato_id . '\');">
                                            <span class="fa fa-edit"></span> Editar
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>';
        }

                // LUGAR Y FECHA DE SUSCRIPCIÓN DEL CONTRATO ------------------------------------------------
        $sel_query_lugar_fecha = $mysqli->query("
        SELECT 
            c.ciudad,
            c.fecha_suscripcion_contrato as contrato_fecha_suscripcion
        FROM 
            cont_contrato c 
        WHERE 
            c.contrato_id = " . $contrato_id . "
        ");

        if ($row_lugar_fecha = $sel_query_lugar_fecha->fetch_assoc()) {
        $ciudad = $row_lugar_fecha["ciudad"];
        $contrato_fecha_suscripcion = $row_lugar_fecha["contrato_fecha_suscripcion"];

        $html .= '
        <div class="col-xs-12 col-md-12 col-lg-12">
            <div class="form-group">
                <table class="table table-bordered table-hover">
                    <div class="col-xs-12 col-md-12 col-lg-12">
                        <div class="h4">
                            <b>LUGAR Y FECHA DE SUSCRIPCIÓN DEL CONTRATO</b>&nbsp;
                        </div>
                    </div>
                    <tr>
                        <td style="width: 250px;"><b>Ciudad</b></td>
                        <td>' . $ciudad . '</td>
                        <td style="width: 75px;">
                            <a class="btn btn-success btn-xs" onclick="sec_con_nuevo_aden_mutuodinero_solicitud_editar_campo_adenda(\'Lugar y Fecha\', \'cont_contrato\', \'ciudad\', \'Ciudad\', \'varchar\', \'' . $ciudad . '\', \'\', \'' . $contrato_id . '\');">
                                <span class="fa fa-edit"></span> Editar
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 250px;"><b>Fecha de Suscripción</b></td>
                        <td>' . $contrato_fecha_suscripcion . '</td>
                        <td style="width: 75px;">
                            <a class="btn btn-success btn-xs" onclick="sec_con_nuevo_aden_mutuodinero_solicitud_editar_campo_adenda(\'Lugar y Fecha\', \'cont_contrato\', \'fecha_suscripcion_contrato\', \'Fecha\', \'date\', \'' . $contrato_fecha_suscripcion . '\', \'\', \'' . $contrato_id . '\');">
                                <span class="fa fa-edit"></span> Editar
                            </a>
                        </td>
                    </tr>
                </table>
            </div>
        </div>';
        }

    $result["status"] = 200;
    $result["messages"] = "Datos obtenidos de gestión.";
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
