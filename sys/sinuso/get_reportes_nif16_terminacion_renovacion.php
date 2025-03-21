<?php
include "db_connect.php";
include "sys_login.php";

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_arrendatario_v") {

	if ($login["usuario_locales"]) {
		$query_empresa = "SELECT l.razon_social_id as id, r.nombre FROM tbl_locales AS l 
		INNER JOIN tbl_razon_social AS r ON r.id = l.razon_social_id
		WHERE l.estado = 1 AND r.status = 1 
		AND l.id IN (".implode(",", $login["usuario_locales"]).")
		GROUP BY l.razon_social_id 
		ORDER BY l.nombre ASC;";
	}else{
		$query_empresa = "SELECT id, nombre
		FROM tbl_razon_social
		WHERE status = 1
		ORDER BY nombre ASC";
	}
	$list_query = $mysqli->query($query_empresa);
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

if (isset($_POST["accion"]) && $_POST["accion"] === "reporte_nif16_terminacion_renovacion") {

	$tipo         = $_POST["tipo"];
	$empresa      = $_POST["empresa"];
	$centro_costo = $_POST["centro_costo"];
	$nomb_tienda  = $_POST["nomb_tienda"];

	$where_tipo          = "";
	$where_empresa       = "";
	$where_centro_costo  = "";
	$where_nombre_tienda = "";
	$where_locales       = "";

	if (!empty($tipo) && $tipo != "0") {
		$where_tipo = " AND tipo = '" . $tipo . "'";
	}
	if($login["usuario_locales"]){
		$where_locales = " AND local_id IN (".implode(",", $login["usuario_locales"]).")";
	}
	if (!empty($empresa) && $empresa != "0") {
		$where_empresa = " AND empresa_suscribe_id IN (" . $empresa . ")";
	}
	if (!empty($centro_costo) && $centro_costo != "0") {
		$where_centro_costo = "  AND cc_id LIKE '%" . $centro_costo . "%'";
	}
	if (!empty($nomb_tienda) && $nomb_tienda != "0") {
		$where_nombre_tienda = "  AND nombre_tienda LIKE '%" . $nomb_tienda . "%'";
	}

	$query_adendas = "
	SELECT  contrato_id, nombre_tienda, tipo, fecha_gerencia, fecha_vencimiento, empresa_suscribe_id, cc_id, local_id, fecha_orden
	FROM (

		SELECT c.contrato_id, c.nombre_tienda, CONCAT('Terminación') AS tipo, DATE_FORMAT(rc.fecha_resolucion, '%d-%m-%Y') AS fecha_gerencia,
		DATE_FORMAT(rc.fecha_resolucion, '%d-%m-%Y') AS fecha_vencimiento,c.empresa_suscribe_id ,c.cc_id, l.id as local_id,
		rc.fecha_resolucion AS fecha_orden
		FROM cont_resolucion_contrato AS rc
		INNER JOIN cont_contrato AS c ON c.contrato_id = rc.contrato_id
		INNER JOIN tbl_locales as l ON l.contrato_id = c.contrato_id
		WHERE rc.tipo_contrato_id = 1
		AND rc.estado_solicitud_id = 2
		GROUP BY rc.id

		UNION ALL

		SELECT c.contrato_id, c.nombre_tienda,
		CONCAT('Renovación') AS tipo,
		DATE_FORMAT(DATE_ADD(CONVERT(ad.valor_original,DATE), INTERVAL 1 DAY),'%d-%m-%Y') AS fecha_gerencia,
		DATE_FORMAT(ad.valor_date,'%d-%m-%Y') AS fecha_vencimiento,c.empresa_suscribe_id ,c.cc_id, l.id as local_id,
		DATE_ADD(CONVERT(ad.valor_original,DATE), INTERVAL 1 DAY) AS fecha_orden
		FROM cont_adendas_detalle ad
		INNER JOIN cont_adendas AS a ON a.id = ad.adenda_id
		INNER JOIN cont_contrato AS c ON c.contrato_id = a.contrato_id
		INNER JOIN tbl_locales as l ON l.contrato_id = c.contrato_id
		WHERE  a.status = 1 AND  a.procesado = 1
		AND c.tipo_contrato_id = 1
		AND ad.nombre_tabla = 'cont_condicion_economica' AND ad.nombre_campo = 'fecha_fin'
		GROUP BY ad.id

	) AS reporte
	WHERE 1 = 1
	" . $where_tipo . "
	" . $where_empresa . "
	" . $where_locales . "
	" . $where_centro_costo . "
	" . $where_nombre_tienda . "
	ORDER BY nombre_tienda ASC, fecha_orden ASC
	";
	$list_query_adendas = $mysqli->query($query_adendas);
	$array_pre_data     = array();

	while ($li = $list_query_adendas->fetch_assoc()) {
		array_push($array_pre_data, array(
			'contrato_id'            => $li['nombre_tienda'],
			'tipo'                   => $li['tipo'],
			'incluido_contrato'      => 'SI',
			'ejecucion_arrendatario' => 'SI',
			'gerencia_ejecutara'     => 'SI',
			'fecha_gerencia'         => $li['fecha_gerencia'],
			'nueva_fecha'            => $li['fecha_vencimiento'],
			'importe_penalizacion'   => '',
			'periodo_pago'           => '',
		));
	}

	$html = '
	<table id="sec_rep_vigencia" class="table table-striped table-hover table-condensed table-bordered" cellspacing="0" width="100%">
		<thead>
			<tr>
				<th class="text-center">ID Contrato</th>
				<th class="text-center">¿Terminación <br> o <br> Renovación?</th>
				<th class="text-center">¿Se ha incluido <br> esta opción en <br> el contrato?</th>
				<th class="text-center">¿La ejecución <br> de la opción depende <br> únicamente del arrendatario?</th>
				<th class="text-center">¿La gerencia <br> ejecutará <br> la opción?</th>
				<th class="text-center">Fecha en que <br> la Gerencia decide <br> ejecutar la opción</th>
				<th class="text-center">Nueva fecha <br> de vencimiento <br> del contrato</th>
				<th class="text-center">Importe de la <br> penalización por <br> ejecutar la opción</th>
				<th class="text-center">Periodo <br> de pago de <br> penalización</th>
			</tr>
		</thead>
		<tbody>';
	for ($i = 0; $i < count($array_pre_data); $i++) {
		$html .= '
			<tr>
				<td class="text-left">' . $array_pre_data[$i]['contrato_id'] . '</td>
				<td class="text-center">' . $array_pre_data[$i]['tipo'] . '</td>
				<td class="text-center">' . $array_pre_data[$i]['incluido_contrato'] . '</td>
				<td class="text-center">' . $array_pre_data[$i]['ejecucion_arrendatario'] . '</td>
				<td class="text-center">' . $array_pre_data[$i]['gerencia_ejecutara'] . '</td>
				<td class="text-center">' . $array_pre_data[$i]['fecha_gerencia'] . '</td>
				<td class="text-center">' . $array_pre_data[$i]['nueva_fecha'] . '</td>
				<td class="text-right">' . $array_pre_data[$i]['importe_penalizacion'] . '</td>
				<td class="text-right">' . $array_pre_data[$i]['periodo_pago'] . '</td>
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
?>