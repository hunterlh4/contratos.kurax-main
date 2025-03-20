<?php
date_default_timezone_set("America/Lima");

include "db_connect.php";
include "sys_login.php";

if (isset($_POST["accion"]) && $_POST["accion"] === "cont_listar_locales_contabilidad_reporte") {
	$cont_contabilidad_razon_social_id    = $_POST['cont_contabilidad_razon_social_id'];
	$cont_contabilidad_numero_comprobante = $_POST['cont_contabilidad_numero_comprobante'];
	$cont_contabilidad_fecha_comprobante  = $_POST['cont_contabilidad_fecha_comprobante'];
	$cont_tipo_moneda                     = $_POST['cont_tipo_moneda'];
	$cont_contabilidad_anio               = $_POST['cont_contabilidad_anio'];
	$cont_contabilidad_mes                = $_POST['cont_contabilidad_mes'];

	$periodo_fecha_inicio = $cont_contabilidad_anio . '-' . $cont_contabilidad_mes . '-01';
	$periodo_fecha_inicio_datetime = new DateTime( $periodo_fecha_inicio ); 
	$periodo_fecha_fin = $periodo_fecha_inicio_datetime->format( 'Y-m-t' );

	$query = "
	SELECT
		p.id,
		p.tipo_id,
		p.tipo_anticipo_id,
		p.num_ruc AS num_ruc,
		b.num_docu,
		b.nombre AS acreedor,
		p.mes,
		p.anio,
		p.periodo_fin AS fecha_vencimiento,
		ce.tipo_moneda_id AS moneda_id,
		(
			CASE
				WHEN b.banco_id = 12 THEN 'P'
				ELSE 'I'
			END
		) AS tipo_cuenta_bancaria,
		b.num_cuenta_bancaria,
		b.num_cuenta_cci,
		p.importe AS importe_original,
		p.mes AS periodo,
		p.anio AS anio,
		p.periodo_inicio AS registro_mes,
		c.cc_id AS centro_de_costos,
		c.nombre_tienda AS nombre_tienda,
		rs.nombre AS razon_social,
		p.renta_bruta,
		c.cc_id
	FROM
		cont_provision p
		INNER JOIN cont_condicion_economica ce ON p.condicion_economica_id = ce.condicion_economica_id
		INNER JOIN cont_beneficiarios b ON ce.contrato_id = b.contrato_id
		INNER JOIN cont_contrato c ON ce.contrato_id = c.contrato_id
		INNER JOIN tbl_razon_social rs ON p.empresa_id = rs.id
	WHERE
		p.status = 1
		AND p.empresa_id = $cont_contabilidad_razon_social_id
		AND p.periodo_inicio BETWEEN '$periodo_fecha_inicio' AND '$periodo_fecha_fin'
		AND p.tipo_id IN (2,3)
		AND ce.tipo_moneda_id = $cont_tipo_moneda
		AND ce.status = 1
		AND b.status = 1
		AND c.status = 1
	";

	$list_query = $mysqli->query($query);

	$data = array();

	$cont        = 0;
	$id_actual   = "";
	$id_anterior = "";

	while ($reg = $list_query->fetch_object()) {

		$cont++;

		$num_ruc = trim($reg->num_ruc);

		if ((int) $num_ruc == 0 || $num_ruc == '') {
			$num_docu = $reg->num_docu;
		} else {
			$num_docu = $num_ruc;
		}

		$tipo = '';

		if ($reg->tipo_id == 2) {
			$tipo = 'Renta';
		} elseif ($reg->tipo_id == 3) {
			$tipo = 'Impuesto a la Renta';
		}

		$data[] = array(
			"0" => $reg->num_docu,
			"1" => $reg->acreedor,
			"2" => $reg->nombre_tienda,
			"3" => $reg->razon_social,
			"4" => $reg->cc_id,
			"5" => $tipo,
			"6" => $reg->importe_original,
		);

	}

	$resultado = array(
		"sEcho"                => 1,
		"iTotalREcords"        => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData"               => $data,
	);

	echo json_encode($resultado);

}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_razones_sociales") {

	$query = "
	SELECT id, CONCAT(subdiario_contabilidad, ' - ', nombre) AS nombre
	FROM  tbl_razon_social
	WHERE status = 1 AND subdiario_contabilidad IS NOT NULL
	ORDER BY nombre ASC";
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
