<?php
date_default_timezone_set("America/Lima");

$result = array();

include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$error = '';

if (isset($_POST["accion"]) && $_POST["accion"] === "cont_listar_locaciones") {

	$user_id = $login ? $login['id'] : null;
	$area_id = $login ? $login['area_id'] : 0;

	$id_empresa = $_POST['id_empresa'];
	$nombre_tienda = $_POST['nombre_tienda'];
	$centro_costos = $_POST['centro_costos'];
	$moneda = $_POST['moneda'];
	$id_departamento = $_POST['id_departamento'];
	$id_provincia = $_POST['id_provincia'];
	$id_distrito = $_POST['id_distrito'];

	$fecha_inicio_solicitud = $_POST['fecha_inicio_solicitud'];
	$fecha_fin_solicitud = $_POST['fecha_fin_solicitud'];
	$fecha_inicio_inicio = $_POST['fecha_inicio_inicio'];
	$fecha_fin_inicio = $_POST['fecha_fin_inicio'];
	$fecha_inicio_suscripcion = $_POST['fecha_inicio_suscripcion'];
	$fecha_fin_suscripcion = $_POST['fecha_fin_suscripcion'];
	$etapa = $_POST['etapa'];

	$where_empresa = '';
	$where_nombre_tienda = '';
	$where_centro_costos = '';
	$where_moneda = '';
	$where_ubigeo = '';
	$where_fecha_solicitud = "";
	$where_fecha_inicio = "";
	$where_fecha_suscripcion = "";
	$where_etapa = "";

	$where_locales = "";

	if ($login["usuario_locales"]) {
		$where_locales = " AND l.id IN (" . implode(",", $login["usuario_locales"]) . ")";
	}

	if (!empty($id_empresa)) {
		$where_empresa = " AND c.empresa_suscribe_id = '" . $id_empresa . "' ";
	}

	if (!empty($nombre_tienda)) {
		$where_nombre_tienda = " AND c.nombre_tienda LIKE '%" . $nombre_tienda . "%' ";
	}

	if (!empty($centro_costos)) {
		$where_centro_costos = " AND c.cc_id LIKE '%" . $centro_costos . "%' ";
	}

	if (!empty($moneda)) {
		$where_moneda = " AND m.id = '" . $moneda . "' ";
	}

	if (!empty($id_departamento)) {
		$where_ubigeo = " AND i.ubigeo_id LIKE '%" . $id_departamento . $id_provincia . $id_distrito . "%'";
	}

	if (!empty($fecha_inicio_solicitud) && !empty($fecha_fin_solicitud)) {
		$where_fecha_solicitud = " AND c.created_at BETWEEN '$fecha_inicio_solicitud 00:00:00' AND '$fecha_fin_solicitud 23:59:59'";
	} elseif (!empty($fecha_inicio_solicitud)) {
		$where_fecha_solicitud = " AND c.created_at >= '$fecha_inicio_solicitud 00:00:00'";
	} elseif (!empty($fecha_fin_solicitud)) {
		$where_fecha_solicitud = " AND c.created_at <= '$fecha_fin_solicitud 23:59:59'";
	}

	if (!empty($fecha_inicio_inicio) && !empty($fecha_fin_inicio)) {
		$where_fecha_inicio = " AND ce.fecha_inicio BETWEEN '$fecha_inicio_inicio 00:00:00' AND '$fecha_fin_inicio 23:59:59'";
	} elseif (!empty($fecha_inicio_inicio)) {
		$where_fecha_inicio = " AND ce.fecha_inicio >= '$fecha_inicio_inicio 00:00:00'";
	} elseif (!empty($fecha_fin_inicio)) {
		$where_fecha_inicio = " AND ce.fecha_inicio <= '$fecha_fin_inicio 23:59:59'";
	}

	if (!empty($fecha_inicio_suscripcion) && !empty($fecha_fin_suscripcion)) {
		$where_fecha_suscripcion = " AND ce.fecha_suscripcion BETWEEN '$fecha_inicio_suscripcion 00:00:00' AND '$fecha_fin_suscripcion 23:59:59'";
	} elseif (!empty($fecha_inicio_suscripcion)) {
		$where_fecha_suscripcion = " AND ce.fecha_suscripcion >= '$fecha_inicio_suscripcion 00:00:00'";
	} elseif (!empty($fecha_fin_suscripcion)) {
		$where_fecha_suscripcion = " AND ce.fecha_suscripcion <= '$fecha_fin_suscripcion 23:59:59'";
	}

	if ($etapa == "1") { //Firmado
		$where_etapa = " AND cd.estado_resolucion <> 2";
	} else if ($etapa == "2") { // Resuelto
		$where_etapa = " AND cd.estado_resolucion = 2";
	}

	$query = "
	SELECT 
		c.contrato_id,
		ce.condicion_economica_id,
		c.nombre_tienda,
	 contra.monto,
		c.fecha_solicitud,
  loca.idlocacion,
		e.etapa_id,
		e.etapa_id as idetapa,
		e.nombre AS etapa,
		e.descripcion AS etapa_descripcion,
		e.situacion AS etapa_situacion,
		ce.monto_renta,
		m.nombre AS tipo_moneda,
		m.simbolo,
		concat(m.simbolo, ' ',contra.monto) as montostr,
		loca.fechainicio as fecha_inicio,
		loca.fechafin as fecha_fin, 
		loca.num_alerta_vencimiento,
		loca.se_enviara_alerta,
		co.sigla AS sigla_correlativo,
		c.codigo_correlativo,
		c.cc_id AS centro_costos,
		c.created_at,
		per.nombre as locatario,
		raz.nombre as locador,
		cd.id as contrato_detalle_id,
		c.fecha_suscripcion_contrato as fecha_suscripcion,
		c.status,
		CASE
			WHEN c.etapa_id = 1 AND c.etapa_conta_id IS NULL THEN 'Pendiente'
			WHEN c.etapa_id = 5 AND c.etapa_conta_id IS NULL THEN 'Firmado'
			WHEN c.etapa_id = 5 AND c.etapa_conta_id = 1 THEN 'Firmado y legalizado'
		END AS estado_v,
		cd.estado_resolucion
	FROM
		cont_contrato c
		INNER JOIN cont_contrato_detalle as cd ON cd.contrato_id = c.contrato_id 
		LEFT JOIN cont_condicion_economica ce ON cd.id = ce.contrato_detalle_id AND ce.status = 1 
		
		LEFT JOIN cont_etapa e ON c.etapa_id = e.etapa_id
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
		LEFT JOIN tbl_locales as l ON l.contrato_id = c.contrato_id
		INNER JOIN cont_propietario as pro on pro.contrato_id = c.contrato_id
		INNER JOIN cont_persona as per on per.id = pro.persona_id
		INNER JOIN tbl_razon_social as raz on c.empresa_suscribe_id = raz.id
		LEFT JOIN cont_locacion as loca on loca.idcontrato = c.contrato_id
		LEFT JOIN cont_contraprestacion contra on contra.contrato_id = c.contrato_id
		LEFT JOIN tbl_moneda m ON contra.moneda_id = m.id
	WHERE c.status IN (1,2) AND (c.etapa_id = 5 OR (c.etapa_id = 1 && c.estado_aprobacion = 1)) AND  c.tipo_contrato_id = 13
	";

	$query .= $where_empresa;
	$query .= $where_nombre_tienda;
	$query .= $where_centro_costos;
	$query .= $where_moneda;
	$query .= $where_ubigeo;
	$query .= $where_fecha_solicitud;
	$query .= $where_fecha_inicio;
	$query .= $where_fecha_suscripcion;
	$query .= $where_locales;
	$query .= $where_etapa;
	$query .= " ORDER BY c.contrato_id desc";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while ($reg = $list_query->fetch_object()) {
		$area_id = $login ? $login['area_id'] : 0;
		$provision = '';


		$estado = '';


		if ($reg->estado_resolucion == 2) {
			$estado = '
				<select class="form-control" style="width: 100%">		 
					<option value="0"  >Resuelto</option>
				</select>';
		} else {
			$estado = ($reg->estado_v == 'Firmado') ? '<select class="form-control"  onchange="sec_rep_vig_cambioestado(this.value ,' . $reg->contrato_id . ')" >			 
				<option value="5" selected >Firmado</option>
				<option value="2" >Firmado y Legalizado</option>
				</select>' : '<select class="form-control" onchange="sec_rep_vig_cambioestado(this.value ,' . $reg->contrato_id . ')"  >		 
				<option value="5"  >Firmado</option>
				<option value="2" selected >Firmado y Legalizado</option>
				</select>';
		}

		$data[] = array(
			"0" => $reg->sigla_correlativo . $reg->codigo_correlativo,

			"1" => $reg->locatario,
			"2" => $reg->locador,
			"3" => $reg->montostr,
			"4" => $reg->created_at,
			"5" => $reg->fecha_suscripcion,
			"6" => $reg->fecha_inicio,
			"7" => $reg->fecha_fin,
			"8" => ($reg->idetapa == 5) ? ' <span class="badge bg-success text-white"> Firmado</span>' : ' <span class="badge bg-warning text-white">No firmado</span>',

			"9" => '<a class="btn btn-rounded btn-primary btn-sm" 
							href="./?sec_id=contrato&amp;sub_sec_id=detalle_solicitud_locacion_servicio&id=' . $reg->contrato_id . '"
							title="Ver detalle">
							<i class="fa fa-eye"></i> Ver
						</a>' . $provision,
			"10" => ($reg->num_alerta_vencimiento == 0) ? '<button type="button" class="btn btn-primary btn-sm" title="Alerta por configurar" onclick="contrato_alerta_locacionservicio(' . $reg->contrato_id . ')">
					  <i class="glyphicon glyphicon-bell"></i>
					</button>' : '<button type="button" class="btn btn-success btn-sm" title="Alerta configurada" 
						onclick="contrato_alerta_locacionservicio(' . $reg->contrato_id . ')">
					  <i class="glyphicon glyphicon-bell"></i>
					</button>',
			"11" => ($reg->num_alerta_vencimiento == 0)
				? 'No existe'
				: '<input class="switch" id="checkbox_' . $reg->idlocacion . '" type="checkbox" 
						data-table="cont_locacion" 
						data-id="' . $reg->idlocacion . '" 
						data-col="se_enviara_alerta" 
						data-on-value="1" 
						data-off-value="0" 
						data-ignore="true" ' . ($reg->se_enviara_alerta != 0 ? 'checked' : '') . '>',


		);
	}
	$resultado = array(
		"sEcho" => 1,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);

	// var_dump($resultado);
	// die();
	echo json_encode($resultado);
}



if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_dato_contrato") {

	$contrato_id = $_POST["parametro"];

	$query = "SELECT
				c.contrato_id,
				ce.idlocacion,
				ce.fechainicio as fecha_inicio,
				ce.fechafin as fecha_fin,
				ce.num_alerta_vencimiento,
				ce.se_enviara_alerta 
			FROM
				cont_contrato c
				INNER JOIN cont_locacion ce ON c.contrato_id = ce.idcontrato 
			
			WHERE
				c.STATUS = 1  
				AND ce.idcontrato = '" . $contrato_id . "'  ";

	$list_query = $mysqli->query($query);

	$li = $list_query->fetch_assoc();

	echo json_encode($li);
}

if (isset($_POST["accion"]) && $_POST["accion"] === "actualizar_alerta_contrato") {

	$condicion_economica_id = $_POST["condicion_economica_id"];
	$numAlerta = $_POST["numAlerta"];

	$query_update = "UPDATE cont_locacion 
						SET num_alerta_vencimiento = '" . $numAlerta . "', 
							se_enviara_alerta = '1'  
						WHERE idlocacion = '" . $condicion_economica_id . "' 
					";

	$mysqli->query($query_update);

	$resultado = "";

	if ($mysqli->error) {
		$resultado = $mysqli->error;
	} else {
		$resultado = "ok";
	}

	echo json_encode($resultado);
}


if (isset($_POST["accion"]) && $_POST["accion"] === "generar_ordenes_de_pago") {
	// INICIO GET VARIABLES
	$contrato_id = $_POST["contrato_id"];

	$query = $mysqli->query("
	SELECT
	ce.monto_renta,
	ce.tipo_moneda_id,
	ce.fecha_inicio,
	ce.fecha_fin,
	ce.garantia_monto,
	ce.impuesto_a_la_renta_id,
	ce.periodo_gracia_id,
	ce.periodo_gracia_inicio,
	ce.periodo_gracia_fin,
	b.forma_pago_id
	FROM cont_contrato c
	INNER JOIN cont_condicion_economica ce
	ON c.contrato_id = ce.contrato_id AND ce.status = 1
	INNER JOIN cont_beneficiarios b
	ON c.contrato_id = b.contrato_id
	WHERE c.contrato_id = " . $contrato_id . "
	AND c.status = 1");
	$row_count = $query->num_rows;
	if ($row_count > 0) {
		while ($row = $query->fetch_assoc()) {
			$renta = $row["monto_renta"];
			$tipo_moneda_id = $row["tipo_moneda_id"];
			$fecha_inicio = $row["fecha_inicio"];
			$fecha_fin = $row["fecha_fin"];
			$garantia_monto = $row["garantia_monto"];
			$impuesto_a_la_renta_id = $row["impuesto_a_la_renta_id"];
			$periodo_gracia_id = $row["periodo_gracia_id"];
			$periodo_gracia_inicio = $row["periodo_gracia_inicio"];
			$periodo_gracia_fin = $row["periodo_gracia_fin"];
			$forma_pago_id = $row["forma_pago_id"];
		}
	}
	// FIN GET VARIABLES


	// INICIO OBTENER ADELANTOS
	$array_adelantos = [];
	$query = $mysqli->query("
	SELECT
	num_periodo
	FROM cont_adelantos
	WHERE contrato_id = " . $contrato_id . "
	AND status = 1");
	$row_count = $query->num_rows;
	if ($row_count > 0) {
		$cont_incremento = 0;
		while ($row = $query->fetch_assoc()) {
			$array_adelantos[] = $row["num_meses"];
		}
	}
	$num_adelantos = count($array_adelantos);
	// FIN OBTENER ADELANTOS


	// INICIO OBTENER INCREMENTOS
	$query = $mysqli->query("
	SELECT
	valor,
	tipo_valor_id,
	tipo_continuidad_id,
	a_partir_del_año
	FROM cont_incrementos
	WHERE contrato_id = " . $contrato_id . "
	AND estado = 1");
	$row_count = $query->num_rows;
	if ($row_count > 0) {
		$cont_incremento = 0;
		while ($row = $query->fetch_assoc()) {
			$array_incrementos[$cont_incremento][0] = $row["valor"];
			$array_incrementos[$cont_incremento][1] = $row["tipo_valor_id"];
			$array_incrementos[$cont_incremento][2] = $row["tipo_continuidad_id"];
			$array_incrementos[$cont_incremento][3] = $row["a_partir_del_año"];
			$cont_incremento++;
		}
	}
	$num_incrementos = count($array_incrementos);
	// FIN OBTENER INCREMENTOS


	// INICIO INICIALIZACION DE VARIABLES
	$user_id = $login ? $login['id'] : null;
	$created_at = date('Y-m-d H:i:s');

	$num_dias_excedentes = 0;
	$incrementos = 0;
	$descuento = 0;
	$tipo_orden_id = 1;
	// FIN INICIALIZACION DE VARIABLES


	// INICIO GENERAR ORDEN
	$query = "INSERT INTO cont_orden
	(contrato_id,
	status,
	user_created_id,
	created_at)
	VALUES (
	" . $contrato_id . ",
	1,
	" . $user_id . ",
	'" . $created_at . "')";

	$mysqli->query($query);
	$orden_id = mysqli_insert_id($mysqli);
	// FIN GENERAR ORDEN


	// INICIO GENERAR ORDEN PERIODO DE GRACIA
	if ($periodo_gracia_id == '1') {
		$query = "INSERT INTO cont_orden_detalle(
		orden_id,
		tipo_orden_id,
		num_cuota,
		periodo_inicio,
		periodo_fin,
		forma_pago_id,
		moneda_id,
		renta,
		incrementos,
		descuento,
		total,
		status,
		user_created_id,
		created_at)
		VALUES (
		" . $orden_id . ",
		5,
		1,
		'" . $periodo_gracia_inicio . "',
		'" . $periodo_gracia_fin . "',
		" . $forma_pago_id . ",
		" . $tipo_moneda_id . ",
		" . round(0, 2) . ",
		" . round(0, 2) . ",
		" . round(0, 2) . ",
		" . round(0, 2) . ",
		1,
		" . $user_id . ",
		'" . $created_at . "')";
		// echo $sql;
		$mysqli->query($query);
	}
	// FIN GENERAR ORDEN PERIODO DE GRACIA


	// INICIO INTERVALO DE FECHA INICIO Y FIN
	if ($periodo_gracia_id == 1) {
		$fecha_inicio = $periodo_gracia_fin;
		$datetime_inicio = new DateTime($fecha_inicio);
		$datetime_inicio->modify('+1 day');
	} else {
		$datetime_inicio = new DateTime($fecha_inicio);
	}

	$datetime_fin = new DateTime($fecha_fin);

	$intervalo = $datetime_fin->diff($datetime_inicio);

	$intervalo_dias = $intervalo->format("%d");
	$intervalo_meses = $intervalo->format("%m");
	$intervalo_anios = $intervalo->format("%y") * 12;

	$intervalo_meses_final = $intervalo_meses + $intervalo_anios;
	// FIN INTERVALO DE FECHA INICIO Y FIN


	// INICIO GENERAR ORDEN DE TIPO GARANTIA
	$query = "INSERT INTO cont_orden_detalle(
	orden_id,
	tipo_orden_id,
	num_cuota,
	periodo_inicio,
	periodo_fin,
	forma_pago_id,
	moneda_id,
	renta,
	incrementos,
	descuento,
	total,
	status,
	user_created_id,
	created_at)
	VALUES (
	" . $orden_id . ",
	3,
	1,
	'" . $datetime_inicio->format('Y-m-d') . "',
	'" . $datetime_inicio->format('Y-m-d') . "',
	" . $forma_pago_id . ",
	" . $tipo_moneda_id . ",
	" . round($garantia_monto, 2) . ",
	" . round(0, 2) . ",
	" . round(0, 2) . ",
	" . round($garantia_monto, 2) . ",
	1,
	" . $user_id . ",
	'" . $created_at . "')";
	// echo $sql;
	$mysqli->query($query);
	// FIN GENERAR ORDEN DE TIPO GARANTIA


	// INICIO GENERAR ORDEN DE TIPO ADELANTO
	$query = "INSERT INTO cont_orden_detalle(
	orden_id,
	tipo_orden_id,
	num_cuota,
	periodo_inicio,
	periodo_fin,
	forma_pago_id,
	moneda_id,
	renta,
	incrementos,
	descuento,
	total,
	status,
	user_created_id,
	created_at)
	VALUES (
	" . $orden_id . ",
	2,
	1,
	'" . $datetime_inicio->format('Y-m-d') . "',
	'" . $datetime_inicio->format('Y-m-d') . "',
	" . $forma_pago_id . ",
	" . $tipo_moneda_id . ",
	" . round(($renta * $num_adelantos), 2) . ",
	" . round(0, 2) . ",
	" . round(0, 2) . ",
	" . round(($renta * $num_adelantos), 2) . ",
	1,
	" . $user_id . ",
	'" . $created_at . "')";
	// echo $sql;
	$mysqli->query($query);
	// FIN GENERAR ORDEN DE TIPO ADELANTO


	for ($num_cuota = 1; $num_cuota <= $intervalo_meses_final; $num_cuota++) {

		// INICIO RESET VARIALES LOCALES
		$descuento = 0;
		// FIN RESET VARIALES LOCALES


		// INICIO PERIODO INICIO Y FIN
		$periodo_inicio = $datetime_inicio->format('Y-m-d');
		$datetime_inicio->modify('+1 month');

		$intervalo_dias_excedente = $datetime_fin->diff($datetime_inicio);
		$num_dias_excedentes = $intervalo_dias_excedente->format('%R%a');
		if ($num_dias_excedentes > 0) {
			$periodo_fin = $datetime_fin->format('Y-m-d');
			$descuento = $renta - (($renta * $num_dias_excedentes) / 30);
			break;
		} else {
			$periodo_fin = $datetime_inicio->format('Y-m-d');
		}
		// FIN PERIODO INICIO Y FIN


		// INICIO INCREMENTOS
		$contador_incremento_a_la_renta = 0;
		for ($i = 0; $i < $num_incrementos; $i++) {
			$valor = $array_incrementos[$i][0];
			$tipo_valor = $array_incrementos[$i][1];
			$tipo_continuidad =  $array_incrementos[$i][2];
			$a_partir_del_anio_en_meses = ($array_incrementos[$i][3] * 12) + 1;

			if ($tipo_continuidad == 1) { // SOLO UNA VEZ
				if ($num_cuota == $a_partir_del_anio_en_meses) {
					if ($contador_incremento_a_la_renta == 0) {
						$renta = $renta + $incrementos;
						$incrementos = 0;
						$contador_incremento_a_la_renta++;
					}

					if ($tipo_valor == 1) {
						$incrementos += $valor;
					} else if ($tipo_valor == 2) {
						$incrementos += ($renta * $valor) / 100;
					}
				}
			} elseif ($tipo_continuidad == 2) { // INCREMENTO ANUAL
				for ($j = $a_partir_del_anio_en_meses; $j <= $num_cuota; $j += 12) {
					if ($num_cuota == $j) {
						if ($contador_incremento_a_la_renta == 0) {
							$renta = $renta + $incrementos;
							$incrementos = 0;
							$contador_incremento_a_la_renta++;
						}

						if ($tipo_valor == 1) {
							$incrementos += $valor;
						} else if ($tipo_valor == 2) {
							$incrementos += ($renta * $valor) / 100;
						}
					}
				}
			}
		}
		// FIN INCREMENTOS


		// INICIO DESCUENTO (ADELANTO)
		foreach ($array_adelantos as $value) {
			if ($num_cuota == $value) {
				$descuento = $renta;
			}
		}
		// FIN DESCUENTO (ADELANTO)

		$total = ($renta + $incrementos) - $descuento;

		$query = "INSERT INTO cont_orden_detalle(
		orden_id,
		tipo_orden_id,
		num_cuota,
		periodo_inicio,
		periodo_fin,
		forma_pago_id,
		moneda_id,
		renta,
		incrementos,
		descuento,
		total,
		status,
		user_created_id,
		created_at)
		VALUES (
		" . $orden_id . ",
		" . $tipo_orden_id . ",
		" . $num_cuota . ",
		'" . $periodo_inicio . "',
		'" . $periodo_fin . "',
		" . $forma_pago_id . ",
		" . $tipo_moneda_id . ",
		" . round($renta, 2) . ",
		" . round($incrementos, 2) . ",
		" . round($descuento, 2) . ",
		" . round($total, 2) . ",
		1,
		" . $user_id . ",
		'" . $created_at . "')";
		// echo $sql;
		$mysqli->query($query);

		if ($impuesto_a_la_renta_id == '2' || $impuesto_a_la_renta_id == '3') {
			$query = "INSERT INTO cont_orden_detalle(
			orden_id,
			tipo_orden_id,
			num_cuota,
			periodo_inicio,
			periodo_fin,
			forma_pago_id,
			moneda_id,
			renta,
			incrementos,
			descuento,
			total,
			status,
			user_created_id,
			created_at)
			VALUES (
			" . $orden_id . ",
			4,
			" . $num_cuota . ",
			'" . $periodo_inicio . "',
			'" . $periodo_fin . "',
			" . $forma_pago_id . ",
			" . $tipo_moneda_id . ",
			" . round(($total * 0.05), 2) . ",
			" . round(0, 2) . ",
			" . round(0, 2) . ",
			" . round(($total * 0.05), 2) . ",
			1,
			" . $user_id . ",
			'" . $created_at . "')";
			// echo $sql;
			$mysqli->query($query);
		}
	}
	// FIN DETALLE ORDEN


	// INICIO ULTIMO MES
	if ($intervalo_dias > 0 && $num_dias_excedentes < 0) {
		$periodo_inicio = $datetime_inicio->format('Y-m-d');
		$periodo_fin = $datetime_fin->format('Y-m-d');

		$descuento = ($renta + $incrementos) - ((($renta + $incrementos) * $intervalo_dias) / 30);
		$total = ($renta + $incrementos) - $descuento;

		// guardar_detalle_orden($orden_id, $tipo_orden_id, $num_cuota, $periodo_inicio, $periodo_fin, $forma_pago_id, $tipo_moneda_id, $renta, $incrementos, $descuento);

		$query = "INSERT INTO cont_orden_detalle(
		orden_id,
		tipo_orden_id,
		num_cuota,
		periodo_inicio,
		periodo_fin,
		forma_pago_id,
		moneda_id,
		renta,
		incrementos,
		descuento,
		total,
		status,
		user_created_id,
		created_at)
		VALUES (
		" . $orden_id . ",
		" . $tipo_orden_id . ",
		" . $num_cuota . ",
		'" . $periodo_inicio . "',
		'" . $periodo_fin . "',
		" . $forma_pago_id . ",
		" . $tipo_moneda_id . ",
		" . round($renta, 2) . ",
		" . round($incrementos, 2) . ",
		" . round($descuento, 2) . ",
		" . round($total, 2) . ",
		1,
		" . $user_id . ",
		'" . $created_at . "')";
		// echo $sql;
		$mysqli->query($query);

		if ($impuesto_a_la_renta_id == '2' || $impuesto_a_la_renta_id == '3') {
			$query = "INSERT INTO cont_orden_detalle(
			orden_id,
			tipo_orden_id,
			num_cuota,
			periodo_inicio,
			periodo_fin,
			forma_pago_id,
			moneda_id,
			renta,
			incrementos,
			descuento,
			total,
			status,
			user_created_id,
			created_at)
			VALUES (
			" . $orden_id . ",
			4,
			" . $num_cuota . ",
			'" . $periodo_inicio . "',
			'" . $periodo_fin . "',
			" . $forma_pago_id . ",
			" . $tipo_moneda_id . ",
			" . round(($total * 0.05), 2) . ",
			" . round(0, 2) . ",
			" . round(0, 2) . ",
			" . round(($total * 0.05), 2) . ",
			1,
			" . $user_id . ",
			'" . $created_at . "')";
			// echo $sql;
			$mysqli->query($query);
		}
	}
	// FIN ULTIMO MES

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = 'ok';
	$result["error"] = $error;

	echo json_encode($result);
}



if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_ordenes_de_pago") {
	// INICIO GET VARIABLES
	$contrato_id = $_POST["contrato_id"];
	$html = '<table class="table table-bordered table-hover">
	<thead>
		<tr>
			<th>Tipo</th>
			<th>Cuota</th>
			<th>Periodo Inicio</th>
			<th>Periodo Fin</th>
			<th>Forma de Pago</th>
			<th>Moneda</th>
			<th>Subtotal</th>
			<th>Incrementos</th>
			<th>Descuento</th>
			<th>Total</th>
			<th>Fecha de pago</th>
			<th>Comprobante</th>
		</tr>
	</thead>
	<tbody>';

	$query = $mysqli->query("
	SELECT
	d.id,
	t.nombre AS tipo_orden,
	d.num_cuota,
	d.periodo_inicio,
	d.periodo_fin,
	f.nombre AS forma_de_pago,
	m.nombre AS moneda,
	d.renta,
	d.incrementos,
	d.descuento,
	d.total,
	d.fecha_pago,
	d.comprobante_id,
	a.nombre,
	a.extension,
	a.ruta
	FROM cont_orden o
	INNER JOIN cont_orden_detalle d ON d.orden_id = o.id
	INNER JOIN tbl_moneda m ON d.moneda_id = m.id
	INNER JOIN cont_forma_pago f ON d.forma_pago_id = f.id
	INNER JOIN cont_tipo_orden t ON d.tipo_orden_id = t.id
	LEFT JOIN cont_archivos a ON d.comprobante_id = a.archivo_id
	WHERE o.contrato_id = " . $contrato_id . "
	AND o.status = 1 AND d.status = 1
	ORDER BY d.id");
	$row_count = $query->num_rows;
	if ($row_count > 0) {
		while ($row = $query->fetch_assoc()) {
			$orden_detalle_id = $row["id"];
			$tipo_orden = $row["tipo_orden"];
			$num_cuota = $row["num_cuota"];
			$periodo_inicio = $row["periodo_inicio"];
			$periodo_fin = $row["periodo_fin"];
			$forma_de_pago = $row["forma_de_pago"];
			$moneda = $row["moneda"];
			$renta = $row["renta"];
			$incrementos = $row["incrementos"];
			$descuento = $row["descuento"];
			$total = $row["total"];
			$fecha_pago = $row["fecha_pago"];
			$comprobante_id = $row["comprobante_id"];
			$archivo_nombre = $row["nombre"];
			$archivo_extension = $row["extension"];
			$archivo_ruta = $row["ruta"];

			$html .= '<tr>';

			$html .= '<td>' . $tipo_orden . '</td>';
			$html .= '<td>' . $num_cuota . '</td>';
			$html .= '<td>' . $periodo_inicio . '</td>';
			$html .= '<td>' . $periodo_fin . '</td>';
			$html .= '<td>' . $forma_de_pago . '</td>';
			$html .= '<td>' . $moneda . '</td>';
			$html .= '<td style="text-align:right">' . number_format($renta, 2, '.', ',') . '</td>';
			$html .= '<td style="text-align:right">' . number_format($incrementos, 2, '.', ',') . '</td>';
			$html .= '<td style="text-align:right">' . number_format($descuento, 2, '.', ',') . '</td>';
			$html .= '<td style="text-align:right">' . number_format($total, 2, '.', ',') . '</td>';

			if ($comprobante_id > 0) {
				if ($archivo_extension == 'pdf') {
					$class_button = 'danger';
					$class_span = 'file-pdf-o';
				} else {
					$class_button = 'info';
					$class_span = 'image';
				}

				$archivo_ruta = str_replace('/var/www/html/', '', $archivo_ruta);

				$html .= '<td>' . $fecha_pago . '</td>';
				$html .= '<td>';
				$html .= '<button type="button" class="btn btn-' . $class_button . ' btn-sm" onclick="ver_comprobante(\'' . $archivo_nombre . '\', \'' . $archivo_extension . '\', \'' . $archivo_ruta . '\')">';
				$html .= '<span class="fa fa-' . $class_span . '"></span> Ver';
				$html .= '</button>';
			} else {
				$html .= '<td colspan="2">';
				$html .= '<button type="button" onclick="modal_agregar_pago(' . $orden_detalle_id . ');" class="btn btn-success btn-sm">';
				$html .= '<span class="fa fa-plus"></span> Agregar fecha y comprobante de pago';
				$html .= '</button></td>';
			}

			$html .= '</tr>';
		}
	}
	// FIN GET VARIABLES


	$html .= '</tbody></table>';


	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $html;
	$result["error"] = $error;

	echo json_encode($result);
}



if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_locales_por_ids") {
	// INICIO GET VARIABLES
	$orden_detalle_ids = $_POST["orden_detalle_ids"];

	$html = '<table class="table table-bordered table-hover" style="font-size: 11px;">
	<thead>
		<tr>
			<th>Local</th>
			<th>Tipo</th>
			<th>Fecha Inicio</th>
			<th>Fecha Fin</th>
			<th>Forma Pago</th>
			<th>Moneda</th>
			<th>Total</th>
			<th></th>
		</tr>
	</thead>
	<tbody>';

	$sql = "SELECT
	d.id,
	c.nombre_tienda,
	t.nombre AS tipo_orden,
	d.periodo_inicio,
	d.periodo_fin,
	f.nombre AS forma_de_pago,
	m.nombre AS moneda,
	d.total,
	d.fecha_pago,
	d.comprobante_id,
	a.nombre,
	a.extension,
	a.ruta
	FROM cont_orden o
	INNER JOIN cont_contrato c ON o.contrato_id = c.contrato_id
	INNER JOIN cont_orden_detalle d ON d.orden_id = o.id
	INNER JOIN tbl_moneda m ON d.moneda_id = m.id
	INNER JOIN cont_forma_pago f ON d.forma_pago_id = f.id
	INNER JOIN cont_tipo_orden t ON d.tipo_orden_id = t.id
	LEFT JOIN cont_archivos a ON d.comprobante_id = a.archivo_id
	WHERE o.status = 1 AND d.status = 1 AND c.status = 1";
	$data = json_decode($orden_detalle_ids);
	$contador = 0;
	$ids = '';
	foreach ($data as $value) {
		if ($contador > 0) {
			$ids .= ',';
		}
		$ids .= $value;
		$contador++;
	}
	$sql .= " AND d.id IN(" . $ids . ") ";
	$sql .= " ORDER BY c.nombre_tienda ASC";
	$query = $mysqli->query($sql);
	$row_count = $query->num_rows;
	if ($row_count > 0) {
		while ($row = $query->fetch_assoc()) {
			$orden_detalle_id = $row["id"];
			$nombre_tienda = $row["nombre_tienda"];
			$tipo_orden = $row["tipo_orden"];
			$periodo_inicio = $row["periodo_inicio"];
			$periodo_fin = $row["periodo_fin"];
			$forma_de_pago = $row["forma_de_pago"];
			$moneda = $row["moneda"];
			$total = $row["total"];
			$fecha_pago = $row["fecha_pago"];
			$comprobante_id = $row["comprobante_id"];
			$archivo_nombre = $row["nombre"];
			$archivo_extension = $row["extension"];
			$archivo_ruta = $row["ruta"];

			$html .= '<tr>';

			$html .= '<td>' . $nombre_tienda . '</td>';
			$html .= '<td>' . $tipo_orden . '</td>';
			$html .= '<td>' . $periodo_inicio . '</td>';
			$html .= '<td>' . $periodo_fin . '</td>';
			$html .= '<td>' . $forma_de_pago . '</td>';
			$html .= '<td>' . $moneda . '</td>';
			$html .= '<td style="text-align:right">' . number_format($total, 2, '.', ',') . '</td>';

			$html .= '<td>';
			$html .= '<button type="button" onclick="remover_local_de_la_lista(' . $orden_detalle_id . ');" class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Remover de la lista">';
			$html .= '<span class="fa fa-remove"></span>';
			$html .= '</button>';
			$html .= '</td>';

			$html .= '</tr>';
		}
	}

	$html .= '</tbody></table>';

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $html;
	$result["error"] = $error;

	echo json_encode($result);
}


if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_nombre_del_local") {
	// INICIO GET VARIABLES
	$contrato_id = $_POST["contrato_id"];

	$query = $mysqli->query("
	SELECT
	nombre_tienda
	FROM cont_contrato
	WHERE contrato_id = " . $contrato_id . "
	AND status = 1");
	$row_count = $query->num_rows;
	if ($row_count > 0) {
		while ($row = $query->fetch_assoc()) {
			$nombre_tienda = $row["nombre_tienda"];
		}
	}
	// FIN GET VARIABLES


	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $nombre_tienda;
	$result["error"] = $error;

	echo json_encode($result);
}


if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_ordenes_de_pago_varios_locales") {
	$nombre_local = trim($_POST["nombre_local"]);
	$periodo = $_POST["periodo"];
	$tipo_renta = $_POST["tipo_renta"];
	$numero_registros_a_mostrar = $_POST["numero_registros_a_mostrar"];

	$periodo_inicio =  substr($periodo, 4, 4) . '-' . substr($periodo, 2, 2) . '-' . substr($periodo, 0, 2);
	$periodo_fin = substr($periodo, 12, 4) . '-' . substr($periodo, 10, 2) . '-' . substr($periodo, 8, 2);

	$num_digitos_nombre_local = strlen($nombre_local);

	$html = '<table class="table table-bordered table-hover" style="font-size: 11px;">
	<thead>
		<tr>
			<th>Local</th>
			<th>Tipo</th>
			<th>Fecha Inicio</th>
			<th>Fecha Fin</th>
			<th>Forma Pago</th>
			<th>Moneda</th>
			<th>Total</th>
			<th>F. pago</th>
			<th>Comprobante</th>
		</tr>
	</thead>
	<tbody>';

	$sql = "SELECT
	d.id,
	c.nombre_tienda,
	t.nombre AS tipo_orden,
	d.periodo_inicio,
	d.periodo_fin,
	f.nombre AS forma_de_pago,
	m.nombre AS moneda,
	d.total,
	d.fecha_pago,
	d.comprobante_id,
	a.nombre,
	a.extension,
	a.ruta
	FROM cont_orden o
	INNER JOIN cont_contrato c ON o.contrato_id = c.contrato_id
	INNER JOIN cont_orden_detalle d ON d.orden_id = o.id
	INNER JOIN tbl_moneda m ON d.moneda_id = m.id
	INNER JOIN cont_forma_pago f ON d.forma_pago_id = f.id
	INNER JOIN cont_tipo_orden t ON d.tipo_orden_id = t.id
	LEFT JOIN cont_archivos a ON d.comprobante_id = a.archivo_id
	WHERE o.status = 1 AND d.status = 1 AND c.status = 1
	AND d.periodo_inicio BETWEEN '" . $periodo_inicio . "' AND '" . $periodo_fin . "'
	AND d.tipo_orden_id = '" . $tipo_renta . "'";
	if ($num_digitos_nombre_local > 3) {
		$sql .= " AND c.nombre_tienda LIKE '%" . $nombre_local . "%'";
	}
	$sql .= " ORDER BY c.nombre_tienda ASC";
	$sql .= " LIMIT " . $numero_registros_a_mostrar;
	$query = $mysqli->query($sql);
	$row_count = $query->num_rows;
	if ($row_count > 0) {
		while ($row = $query->fetch_assoc()) {
			$orden_detalle_id = $row["id"];
			$nombre_tienda = $row["nombre_tienda"];
			$tipo_orden = $row["tipo_orden"];
			$periodo_inicio = $row["periodo_inicio"];
			$periodo_fin = $row["periodo_fin"];
			$forma_de_pago = $row["forma_de_pago"];
			$moneda = $row["moneda"];
			$total = $row["total"];
			$fecha_pago = $row["fecha_pago"];
			$comprobante_id = $row["comprobante_id"];
			$archivo_nombre = $row["nombre"];
			$archivo_extension = $row["extension"];
			$archivo_ruta = $row["ruta"];

			$html .= '<tr>';

			$html .= '<td>' . $nombre_tienda . '</td>';
			$html .= '<td>' . $tipo_orden . '</td>';
			$html .= '<td>' . $periodo_inicio . '</td>';
			$html .= '<td>' . $periodo_fin . '</td>';
			$html .= '<td>' . $forma_de_pago . '</td>';
			$html .= '<td>' . $moneda . '</td>';
			$html .= '<td style="text-align:right">' . number_format($total, 2, '.', ',') . '</td>';

			if ($comprobante_id > 0) {
				if ($archivo_extension == 'pdf') {
					$class_button = 'danger';
					$class_span = 'file-pdf-o';
				} else {
					$class_button = 'info';
					$class_span = 'image';
				}

				$archivo_ruta = str_replace('/var/www/html/', '', $archivo_ruta);

				$html .= '<td>' . $fecha_pago . '</td>';
				$html .= '<td>';
				$html .= '<button type="button" class="btn btn-' . $class_button . ' btn-sm" onclick="ver_comprobante(\'' . $archivo_nombre . '\', \'' . $archivo_extension . '\', \'' . $archivo_ruta . '\')">';
				$html .= '<span class="fa fa-' . $class_span . '"></span> Ver';
				$html .= '</button>';
				$html .= '</td>';
			} else {
				$html .= '<td colspan="2">';
				$html .= '<button type="button" onclick="agregar_local_a_lista(' . $orden_detalle_id . ');" class="btn btn-success btn-sm">';
				$html .= '<span class="fa fa-plus"></span> Agregar local';
				$html .= '</button>';
				$html .= '</td>';
			}

			$html .= '</tr>';
		}
	}

	$html .= '</tbody></table>';

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $html;
	$result["error"] = $error;

	echo json_encode($result);
}

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_comprobante_pago") {
	$usuario_id = $login ? $login['id'] : null;
	$created_at = date("Y-m-d H:i:s");
	$contrato_id = $_POST["contrato_id"];

	// INICIO CARGAR COMPROBANTE
	if (isset($_FILES['archivo_comprobante_de_pago']) && $_FILES['archivo_comprobante_de_pago']['error'] === UPLOAD_ERR_OK) {
		$path = "/var/www/html/files_bucket/contratos/comprobantes/";
		if (!is_dir($path)) mkdir($path, 0777, true);

		$filename = $_FILES['archivo_comprobante_de_pago']['name'];
		$filenametem = $_FILES['archivo_comprobante_de_pago']['tmp_name'];
		$filesize = $_FILES['archivo_comprobante_de_pago']['size'];
		$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
		if ($filename != "") {
			$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
			$nombre_archivo = $contrato_id . "_COMPROBANTE_DE_PAGO_" . date('YmdHis') . "." . $fileExt;
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
							" . $contrato_id . ",
							18,
							'" . $nombre_archivo . "',
							'" . $fileExt . "',
							'" . $filesize . "',
							'" . $path . "',
							" . $usuario_id . ",
							'" . $created_at . "'
							)";
			$mysqli->query($comando);
			$comprobante_id = mysqli_insert_id($mysqli);
		}
	}
	// FIN CARGAR COMPROBANTE

	$fecha_pago_sin_formato = $_POST["fecha_pago"];
	$fecha_pago = date("Y-m-d", strtotime($fecha_pago_sin_formato));
	$orden_detalle_id = $_POST["orden_detalle_id"];

	$query_update = "
	UPDATE cont_orden_detalle 
	SET 
		fecha_pago = '" . $fecha_pago . "',
		comprobante_id = " . $comprobante_id . ",
		user_updated_id = " . $usuario_id . ",
		updated_at = '" . $created_at . "'
	WHERE id = " . $orden_detalle_id . "
	";
	$mysqli->query($query_update);

	if ($mysqli->error) {
		$result["update_error"] = $mysqli->error;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $query_update;
	$result["error"] = $error;

	echo json_encode($result);
}


if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_comprobante_pago_varios_locales") {
	$usuario_id = $login ? $login['id'] : null;
	$created_at = date("Y-m-d H:i:s");

	// INICIO CARGAR COMPROBANTE
	if (isset($_FILES['archivo_comprobante_de_pago_varios_locales']) && $_FILES['archivo_comprobante_de_pago_varios_locales']['error'] === UPLOAD_ERR_OK) {
		$path = "/var/www/html/files_bucket/contratos/comprobantes/";
		if (!is_dir($path)) mkdir($path, 0777, true);

		$filename = $_FILES['archivo_comprobante_de_pago_varios_locales']['name'];
		$filenametem = $_FILES['archivo_comprobante_de_pago_varios_locales']['tmp_name'];
		$filesize = $_FILES['archivo_comprobante_de_pago_varios_locales']['size'];
		$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
		if ($filename != "") {
			$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
			$nombre_archivo = "_COMPROBANTE_DE_PAGO_" . date('YmdHis') . "." . $fileExt;
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
							0,
							18,
							'" . $nombre_archivo . "',
							'" . $fileExt . "',
							'" . $filesize . "',
							'" . $path . "',
							" . $usuario_id . ",
							'" . $created_at . "'
							)";
			$mysqli->query($comando);
			$comprobante_id = mysqli_insert_id($mysqli);
		}
	}
	// FIN CARGAR COMPROBANTE

	$fecha_pago_sin_formato = $_POST["fecha_pago"];
	$fecha_pago = date("Y-m-d", strtotime($fecha_pago_sin_formato));

	$orden_detalle_ids = $_POST["locales_orden_detalle_id"];
	$data_orden_detalle = json_decode($orden_detalle_ids);
	foreach ($data_orden_detalle as $orden_detalle_id) {
		$query_update = "
		UPDATE cont_orden_detalle 
		SET 
		fecha_pago = '" . $fecha_pago . "',
		comprobante_id = " . $comprobante_id . ",
		user_updated_id = " . $usuario_id . ",
		updated_at = '" . $created_at . "'
		WHERE id = " . $orden_detalle_id . "
		";
		$mysqli->query($query_update);
	}

	if ($mysqli->error) {
		$result["update_error"] = $mysqli->error;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $query_update;
	$result["error"] = $error;

	echo json_encode($result);
}


if (isset($_POST["accion"]) && $_POST["accion"] === "cambio_estado_vigencia") {

	$estado = $_POST["valor_id"];
	$contrato_id = $_POST["contrato_id"];

	if ($estado == 1) {
		$query_update = "
		UPDATE cont_contrato
		SET 
		etapa_id = '1' ,
		etapa_conta_id = null
		WHERE contrato_id = " . $contrato_id . "
		";
	} elseif ($estado == 5) {
		$query_update = "
		UPDATE cont_contrato
		SET 
		etapa_id = '5',
		etapa_conta_id = null
		WHERE contrato_id = " . $contrato_id . "
		";
	} elseif ($estado == 2) {
		$query_update = "
		UPDATE cont_contrato
		SET 
		etapa_id = '5',
		etapa_conta_id = '1'
		WHERE contrato_id = " . $contrato_id . "
		";
	}


	$mysqli->query($query_update);

	$result["error"] = "";
	if ($mysqli->error) {
		$result["error"] = $mysqli->error;
	}

	if ($result["error"] == "") {
		$result["http_code"] = 200;
		$result["status"] = "Se han guardado correctamente el estado.";
		$result["result"] = 'ok';
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los registros.";
	}
	echo json_encode($result);
	exit();
}


if (isset($_POST["accion"]) && $_POST["accion"] === "generar_provision_contable_manual") {
	// INICIO GET VARIABLES
	$contrato_id = $_POST["contrato_id"];

	$select_datos_generales = "
	SELECT
		p.num_ruc,
		c.empresa_suscribe_id,
		ce.condicion_economica_id,
		ce.monto_renta,
		ce.tipo_moneda_id,
		ce.fecha_inicio,
		ce.fecha_fin,
		ce.garantia_monto,
		ce.impuesto_a_la_renta_id,
		ce.carta_de_instruccion_id,
		b.forma_pago_id
	FROM 
		cont_contrato c
		INNER JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id AND ce.status = 1
		INNER JOIN cont_beneficiarios b ON c.contrato_id = b.contrato_id
		INNER JOIN cont_propietario pr ON pr.contrato_id = c.contrato_id
		INNER JOIN cont_persona p ON pr.persona_id = p.id
	WHERE 
		c.contrato_id = $contrato_id
		AND c.status = 1
	";

	$query = $mysqli->query($select_datos_generales);
	$row_count = $query->num_rows;

	if ($row_count > 0) {
		while ($row = $query->fetch_assoc()) {
			$num_ruc = $row["num_ruc"];
			$empresa_suscribe_id = $row["empresa_suscribe_id"];
			$condicion_economica_id = $row["condicion_economica_id"];
			$renta = $row["monto_renta"];
			$tipo_moneda_id = $row["tipo_moneda_id"];
			$fecha_inicio = $row["fecha_inicio"];
			$fecha_fin = $row["fecha_fin"];
			$garantia_monto = $row["garantia_monto"];
			$impuesto_a_la_renta_id = $row["impuesto_a_la_renta_id"];
			$carta_de_instruccion_id = $row["carta_de_instruccion_id"];
			$forma_pago_id = $row["forma_pago_id"];
		}
	}
	// FIN GET VARIABLES


	// INICIO VALIDAR DATOS
	if (empty($fecha_inicio) || empty($fecha_fin)) {
		$result["consulta_error"] = 'No se pudo generar la Provisión Contable porque faltan las fechas.';
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestión.";
		$result["result"] = 'ok';
		$result["error"] = $error;

		exit(json_encode($result));
	}
	// FIN VALIDAR DATOS


	// INICIO OBTENER ADELANTOS
	$array_adelantos = [];

	$select_adelantos = "
	SELECT
		num_periodo
	FROM 
		cont_adelantos
	WHERE 
		contrato_id = $contrato_id 
		AND status = 1
	";

	$query = $mysqli->query($select_adelantos);
	$row_count = $query->num_rows;

	if ($row_count > 0) {
		$cont_incremento = 0;
		while ($row = $query->fetch_assoc()) {
			$array_adelantos[] = $row["num_periodo"];
		}
	}

	$num_adelantos = count($array_adelantos);
	// FIN OBTENER ADELANTOS


	// INICIO OBTENER INCREMENTOS
	$array_incrementos = [];

	$select_incrementos = "
	SELECT
		valor,
		tipo_valor_id,
		tipo_continuidad_id,
		a_partir_del_año
	FROM 
		cont_incrementos
	WHERE 
		contrato_id = $contrato_id
		AND estado = 1
	";

	$query = $mysqli->query($select_incrementos);
	$row_count = $query->num_rows;

	if ($row_count > 0) {
		$cont_incremento = 0;
		while ($row = $query->fetch_assoc()) {
			$array_incrementos[$cont_incremento][0] = $row["valor"];
			$array_incrementos[$cont_incremento][1] = $row["tipo_valor_id"];
			$array_incrementos[$cont_incremento][2] = $row["tipo_continuidad_id"];
			$array_incrementos[$cont_incremento][3] = $row["a_partir_del_año"];
			$cont_incremento++;
		}
	}

	$num_incrementos = count($array_incrementos);
	// FIN OBTENER INCREMENTOS


	// INICIO INICIALIZACION DE VARIABLES
	$user_id = $login ? $login['id'] : null;
	$created_at = date('Y-m-d H:i:s');

	$num_dias_excedentes = 0;
	$incrementos = 0;
	$descuento = 0;
	$tipo_orden_id = 1;
	// FIN INICIALIZACION DE VARIABLES


	// INICIO INTERVALO DE FECHA INICIO Y FIN
	$datetime_inicio = new DateTime($fecha_inicio);
	$datetime_fin = new DateTime($fecha_fin);

	$intervalo = $datetime_fin->diff($datetime_inicio);

	$intervalo_dias = $intervalo->format("%d");
	$intervalo_meses = $intervalo->format("%m");
	$intervalo_anios = $intervalo->format("%y") * 12;

	$intervalo_meses_final = $intervalo_meses + $intervalo_anios;
	// FIN INTERVALO DE FECHA INICIO Y FIN


	// INICIO GARANTIA
	$query = "
	INSERT INTO cont_provision (
		condicion_economica_id,
		num_ruc,
		tipo_id,
		tipo_anticipo_id,
		empresa_id,
		moneda_id,
		importe,
		num_cuota,
		periodo_inicio,
		periodo_fin,
		anio,
		mes,
		dia_de_pago,
		forma_de_pago_id,
		status,
		user_created_id,
		created_at
	) VALUES (
		$condicion_economica_id,
		'$num_ruc',
		1,
		1,
		$empresa_suscribe_id,
		$tipo_moneda_id,
		" . round($garantia_monto, 2) . ",
		0,
		'" . $datetime_inicio->format('Y-m-d') . "',
		'" . $datetime_inicio->format('Y-m-d') . "',
		" . $datetime_inicio->format('Y') . ",
		" . $datetime_inicio->format('m') . ",
		" . $datetime_inicio->format('d') . ",
		$forma_pago_id,
		1,
		$user_id,
		'$created_at'
	)";
	$mysqli->query($query);
	// FIN GARANTIA


	// INICIO ADELANTO
	foreach ($array_adelantos as $key => $value) {
		$query = "
		INSERT INTO cont_provision (
			condicion_economica_id,
			num_ruc,
			tipo_id,
			tipo_anticipo_id,
			empresa_id,
			moneda_id,
			importe,
			num_cuota,
			periodo_inicio,
			periodo_fin,
			anio,
			mes,
			dia_de_pago,
			forma_de_pago_id,
			num_adelanto_id,
			status,
			user_created_id,
			created_at
		) VALUES (
			$condicion_economica_id,
			'$num_ruc',
			1,
			2,
			$empresa_suscribe_id,
			$tipo_moneda_id,
			" . round($renta, 2) . ",
			0,
			'" . $datetime_inicio->format('Y-m-d') . "',
			'" . $datetime_inicio->format('Y-m-d') . "',
			" . $datetime_inicio->format('Y') . ",
			" . $datetime_inicio->format('m') . ",
			" . $datetime_inicio->format('d') . ",
			$forma_pago_id,
			'$value',
			1,
			$user_id,
			'$created_at'
		)";

		$mysqli->query($query);
	}
	// FIN ADELANTO


	// INICIO PROVISIÓN
	for ($num_cuota = 1; $num_cuota <= $intervalo_meses_final; $num_cuota++) {

		// INICIO RESET VARIALES LOCALES
		$descuento = 0;
		// FIN RESET VARIALES LOCALES


		// INICIO PERIODO INICIO Y FIN
		$periodo_inicio = $datetime_inicio->format('Y-m-d');
		$datetime_inicio->modify('+1 month');

		$intervalo_dias_excedente = $datetime_fin->diff($datetime_inicio);
		$num_dias_excedentes = $intervalo_dias_excedente->format('%R%a');
		if ($num_dias_excedentes > 0) {
			$periodo_fin = $datetime_fin->format('Y-m-d');
			$descuento = $renta - (($renta * $num_dias_excedentes) / 30);
			break;
		} else {
			$datetime_inicio->modify('-1 day');
			$periodo_fin_tmp = $datetime_inicio->format('Y-m-d');
		}
		// FIN PERIODO INICIO Y FIN


		// INICIO INCREMENTOS
		$contador_incremento_a_la_renta = 0;
		for ($i = 0; $i < $num_incrementos; $i++) {
			$valor = $array_incrementos[$i][0];
			$tipo_valor = $array_incrementos[$i][1];
			$tipo_continuidad =  $array_incrementos[$i][2];
			$a_partir_del_anio_en_meses = (($array_incrementos[$i][3] - 1) * 12) + 1;

			if ($tipo_continuidad == 1) { // EL
				if ($num_cuota == $a_partir_del_anio_en_meses) {
					if ($contador_incremento_a_la_renta == 0) {
						$renta = $renta + $incrementos;
						$incrementos = 0;
						$contador_incremento_a_la_renta++;
					}

					if ($tipo_valor == 1) {
						$incrementos += $valor;
					} else if ($tipo_valor == 2) {
						$incrementos += ($renta * $valor) / 100;
					}
				}
				if ($num_cuota == ($a_partir_del_anio_en_meses + 12)) {
					$renta = $renta + $incrementos;
					$incrementos = 0;
				}
			} elseif ($tipo_continuidad == 2 || $tipo_continuidad == 3) { // ANUAL A PARTIR DEL
				for ($j = $a_partir_del_anio_en_meses; $j <= $num_cuota; $j += 12) {
					if ($num_cuota == $j) {
						if ($contador_incremento_a_la_renta == 0) {
							$renta = $renta + $incrementos;
							$incrementos = 0;
							$contador_incremento_a_la_renta++;
						}

						if ($tipo_valor == 1) {
							$incrementos += $valor;
						} else if ($tipo_valor == 2) {
							$incrementos += ($renta * $valor) / 100;
						}
					}
				}
			}
		}
		// FIN INCREMENTOS

		$total = ($renta + $incrementos);

		// INICIO IMPUESTO A LA RENTA
		if ($impuesto_a_la_renta_id == '1' || $impuesto_a_la_renta_id == '2') {
			if ($impuesto_a_la_renta_id == '1') {
				$importe_impuesto_a_la_renta = $total * 0.05;
				$renta_a_pagar = $total - $importe_impuesto_a_la_renta;
			} elseif ($impuesto_a_la_renta_id == '2') {
				$importe_impuesto_a_la_renta = ($total * 1.05265) - $total;
				$renta_a_pagar = $total;
			}

			// if($carta_de_instruccion_id == '1') {
			$query = "
				INSERT INTO cont_provision (
					condicion_economica_id,
					num_ruc,
					tipo_id,
					empresa_id,
					moneda_id,
					renta_bruta,
					importe,
					num_cuota,
					periodo_inicio,
					periodo_fin,
					anio,
					mes,
					dia_de_pago,
					forma_de_pago_id,
					status,
					user_created_id,
					created_at
				) VALUES (
					$condicion_economica_id,
					'$num_ruc',
					3,
					$empresa_suscribe_id,
					$tipo_moneda_id,
					" . round($renta_a_pagar + $importe_impuesto_a_la_renta, 2) . ",
					" . round($importe_impuesto_a_la_renta, 2) . ",
					$num_cuota,
					'$periodo_inicio',
					'$periodo_fin_tmp',
					" . $datetime_inicio->format('Y') . ",
					" . $datetime_inicio->format('m') . ",
					" . rand(1, 28) . ",
					$forma_pago_id,
					1,
					$user_id,
					'$created_at'
				)";

			$mysqli->query($query);
			// } 
			/*
			elseif ($carta_de_instruccion_id == '2') {
				if ($impuesto_a_la_renta_id == '1') {
					$renta_a_pagar = $total;
				} elseif ($impuesto_a_la_renta_id == '2') {
					$renta_a_pagar = $total + $importe_impuesto_a_la_renta;
				}
			}
			*/
		} else {
			$renta_a_pagar = $total;
		}
		// FIN IMPUESTO A LA RENTA


		// INICIO DESCUENTO (ADELANTO)
		foreach ($array_adelantos as $value) {
			if ($num_cuota == $value) {
				$renta_a_pagar = 0;
			}
		}
		// FIN DESCUENTO (ADELANTO)


		// INICIO RENTA
		if ($renta_a_pagar != 0) {
			$query = "
			INSERT INTO cont_provision (
				condicion_economica_id,
				num_ruc,
				tipo_id,
				empresa_id,
				moneda_id,
				importe,
				num_cuota,
				periodo_inicio,
				periodo_fin,
				anio,
				mes,
				dia_de_pago,
				forma_de_pago_id,
				status,
				user_created_id,
				created_at
			) VALUES (
				$condicion_economica_id,
				'$num_ruc',
				2,
				$empresa_suscribe_id,
				$tipo_moneda_id,
				" . round($renta_a_pagar, 2) . ",
				$num_cuota,
				'$periodo_inicio',
				'$periodo_fin_tmp',
				" . $datetime_inicio->format('Y') . ",
				" . $datetime_inicio->format('m') . ",
				" . rand(1, 28) . ",
				$forma_pago_id,
				1,
				$user_id,
				'$created_at'
			)";

			$mysqli->query($query);
		}
		// FIN RENTA

		$datetime_inicio->modify('+1 day');

		if ($num_cuota > 100) {
			break;
		}
	}
	// FIN PROVISIÓN

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = 'ok';
	$result["error"] = $error;

	echo json_encode($result);
}
