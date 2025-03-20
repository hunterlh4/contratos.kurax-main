<?php
include "db_connect.php";
include "sys_login.php";

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_locales") {
	$query = "SELECT lc.id, lc.nombre
	FROM tbl_locales lc
	where lc.estado = 1";
	$list_query = $mysqli->query($query);
	$list       = array();
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	$result["status"] = 200;
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_redes_v") {
	$list = array(
		array('id' => 1, 'nombre' => 'Tienda'),
		array('id' => 9, 'nombre' => 'Casino'),
		array('id' => 7, 'nombre' => 'Tambo'),
	);
	$result["status"] = 200;
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_arrendatario_v") {

	$query = "SELECT id, nombre
	FROM  tbl_razon_social
	WHERE status = 1
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

if (isset($_POST["accion"]) && $_POST["accion"] === "reporte_vigencia") {

	$empresa      = $_POST["empresa"];
	$centro_costo = $_POST["centro_costo"];
	$renta_m      = $_POST["renta_m"];
	$nomb_tienda  = $_POST["nomb_tienda"];
	$departamento = $_POST["departamento"];
	$provincia    = $_POST["provincia"];
	$distrito     = $_POST["distrito"];
	$direccion    = $_POST["direccion"];
	$fec_suscrip  = $_POST["fec_suscrip"];
	$fec_inicio   = $_POST["fec_inicio"];
	$fec_fin      = $_POST["fec_fin"];
	$n_adendas    = $_POST["n_adendas"];
	$estado       = $_POST["estado"];

	$where_empresa          = "";
	$where_centro_costo     = "";
	$where_renta_m          = "";
	$where_nombre_tienda    = "";
	$where_ubigeo           = "";
	$where_direccion        = "";
	$where_fech_suscripcion = "";
	$where_fecha_inicio     = "";
	$where_fecha_fin        = "";
	$where_n_adendas        = "";
	$where_estado           = "";

	if (!empty($empresa) && $empresa != "0") {
		$where_empresa = " AND c.empresa_suscribe_id IN (" . $empresa . ")";
	}
	if (!empty($centro_costo) && $centro_costo != "0") {
		$where_centro_costo = "  AND c.cc_id LIKE '%" . $centro_costo . "%'";
	}
	if (!empty($renta_m) && $renta_m != "0") {
		$where_renta_m = "  AND ce.monto_renta like '%" . $renta_m . "%'";
	}
	if (!empty($nomb_tienda) && $nomb_tienda != "0") {
		$where_nombre_tienda = "  AND c.nombre_tienda LIKE '%" . $nomb_tienda . "%'";
	}
	if (!empty($direccion) && $direccion != "0") {
		$where_direccion = "  AND i.ubicacion LIKE '%" . $direccion . "%'";
	}
	if (!empty($departamento) && $departamento != "0") {
		$where_ubigeo .= "  AND dp.cod_depa = '" . $departamento . "'";
	}
	if (!empty($provincia) && $provincia != "0") {
		$where_ubigeo .= "  AND pr.cod_prov = '" . $provincia . "'";
	}
	if (!empty($distrito) && $distrito != "0") {
		$where_ubigeo .= "  AND dt.cod_dist = '" . $distrito . "'";
	}
	if (!empty($fec_suscrip)) {
		$where_fech_suscripcion .= "  AND ce.fecha_suscripcion = '" . $fec_suscrip . "'";
	}
	if (!empty($fec_inicio)) {
		$where_fecha_inicio .= "  AND ce.fecha_inicio = '" . $fec_inicio . "'";
	}
	if (!empty($fec_fin)) {
		$where_fecha_fin .= "  AND ce.fecha_fin = '" . $fec_fin . "'";
	}
	if (!empty($n_adendas) && $n_adendas != "0") {
		$where_n_adendas .= "  AND ad.num_adendas = '" . $n_adendas . "'";
	}
	if (!empty($estado) && $estado != "0") {
		$where_estado .= "  AND c.etapa_id = '" . $estado . "'";
	}

	$query = "SELECT
	c.contrato_id AS id_item,
	rs.nombre AS empresa,
	c.nombre_tienda,
	c.cc_id,
	ce.monto_renta,
	dp.nombre AS departamento,
	pr.nombre AS provincia,
	dt.nombre AS distrito,
	i.ubicacion AS direccion,
	IFNULL(ad.num_adendas, 0) AS numero_adendas,
	ce.fecha_inicio,
	ce.fecha_fin,
	ce.fecha_suscripcion,
	c.etapa_id AS etapa_est,
	CASE
		WHEN
			c.etapa_id = 1
				AND c.etapa_conta_id IS NULL
		THEN
			'Pendiente'
		WHEN
			c.etapa_id = 5
				AND c.etapa_conta_id IS NULL
		THEN
			'Firmado'
		WHEN c.etapa_id = 5 AND c.etapa_conta_id = 1 THEN 'Firmado y legalizado'
	END AS estado_v,
	ir.nombre AS impuesto_a_la_renta,
	ci.nombre AS carta_de_instruccion
FROM
	cont_contrato AS c
	INNER JOIN cont_inmueble i ON i.contrato_id = c.contrato_id AND i.status = 1
	INNER JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id AND ce.status = 1
	INNER JOIN cont_tipo_impuesto_a_la_renta ir ON ce.impuesto_a_la_renta_id = ir.id
	LEFT JOIN cont_tipo_carta_de_instruccion ci ON ce.carta_de_instruccion_id = ci.id
	LEFT JOIN (
		SELECT
			contrato_id, COUNT(*) AS num_adendas
		FROM
			cont_adendas
		WHERE
			procesado = 1
			AND status = 1
		GROUP BY contrato_id
	) AS ad ON c.contrato_id = ad.contrato_id
	INNER JOIN tbl_razon_social AS rs ON rs.id = c.empresa_suscribe_id
	INNER JOIN tbl_ubigeo AS dp ON dp.cod_depa = SUBSTRING(i.ubigeo_id, 1, 2) AND dp.cod_prov = '00' AND dp.cod_dist = '00'
	INNER JOIN tbl_ubigeo AS pr ON pr.cod_depa = SUBSTRING(i.ubigeo_id, 1, 2) AND SUBSTRING(i.ubigeo_id, 3, 2) = pr.cod_prov AND pr.cod_dist = '00'
	INNER JOIN tbl_ubigeo AS dt ON dt.cod_depa = SUBSTRING(i.ubigeo_id, 1, 2) AND SUBSTRING(i.ubigeo_id, 3, 2) = dt.cod_prov AND SUBSTRING(i.ubigeo_id, 5, 2) = dt.cod_dist
WHERE
	c.status = 1
	$where_empresa
	$where_centro_costo
	$where_renta_m
	$where_nombre_tienda
	$where_direccion
	$where_ubigeo
	$where_n_adendas
	$where_estado
	$where_fech_suscripcion
	$where_fecha_inicio
	$where_fecha_fin
	";
	$list_query = $mysqli->query($query);
	$html       = '
	<table id="sec_rep_vigencia" class="table table-striped table-hover table-condensed table-bordered" cellspacing="0" width="100%">
		<thead>
			<tr>
				<th class="text-center">Empresa</th>
				<th class="text-center">C.C.</th>
				<th class="text-center">Renta Mensual</th>
				<th class="text-center">Nombre Tienda</th>
				<th class="text-center">Departamento</th>
				<th class="text-center">Provincia</th>
				<th class="text-center">Distrito</th>
				<th class="text-center">Dirección</th>
				<th class="text-center">Fecha suscripción</th>
				<th class="text-center">Vigencia</th>
				<th class="text-center">Fecha Inicio</th>
				<th class="text-center">Fecha Fin</th>
				<th class="text-center">Impuesto a la renta</th>
				<th class="text-center">Carta de Instrucción</th>
				<th class="text-center">Nº Adendas</th>
				<th class="text-center">Estado</th>
			</tr>
		</thead>
		<tbody>';
	while ($li = $list_query->fetch_assoc()) {
		$cant_meses_contrato = "";
		$fecha_inicio        = trim($li['fecha_inicio']);
		$fecha_fin           = trim($li['fecha_fin']);
		if (!(empty($fecha_inicio) || empty($fecha_fin))) {
			$inicio    = $fecha_inicio . " 00:00:00";
			$fin       = $fecha_fin . " 23:59:59";
			$datetime1 = new DateTime($inicio);
			$datetime2 = new DateTime($fin);
			$datetime2->modify('+1 day');
			$interval            = $datetime2->diff($datetime1);
			$intervalMeses       = $interval->format("%m");
			$intervalAnos        = $interval->format("%y") * 12;
			$cant_meses_contrato = $intervalMeses + $intervalAnos;
			$cant_meses_contrato = sec_contrato_detalle_solicitud_de_meses_a_anios_y_meses($cant_meses_contrato);
		}

		$html .= '
			<tr>
				<td class="text-center">' . $li['empresa'] . '</td>
				<td class="text-left">' . $li['cc_id'] . '</td>
				<td class="text-left"> ' . $li['monto_renta'] . '</td>
				<td class="text-left">' . $li['nombre_tienda'] . '</td>
				<td class="text-left">' . $li['departamento'] . '</td>
				<td class="text-left">' . $li['provincia'] . '</td>
				<td class="text-left">' . $li['distrito'] . '</td>
				<td class="text-left">' . $li['direccion'] . '</td>
				<td class="text-left">' . $li['fecha_suscripcion'] . '</td>
				<td class="text-left">' . $cant_meses_contrato . '</td>
				<td class="text-left">' . $li['fecha_inicio'] . '</td>
				<td class="text-left">' . $li['fecha_fin'] . '</td>
				<td class="text-left">' . $li['impuesto_a_la_renta'] . '</td>
				<td class="text-left">' . $li['carta_de_instruccion'] . '</td>
				<td class="text-left">' . $li['numero_adendas'] . '</td>
				<td class="text-left">' . $li['estado_v'] . '</td>
			</tr>
			';
	}
	$html .= '
		</tbody>
	</table>';

	$result["status"] = 200;
	$result["result"] = $html;
	echo json_encode($result);
	exit();
}

function sec_contrato_detalle_solicitud_de_meses_a_anios_y_meses($meses)
{
	if ($meses < 12) {
		$anio_y_meses = $meses . ' meses';
	} else {
		$anio            = intval($meses / 12);
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
?>