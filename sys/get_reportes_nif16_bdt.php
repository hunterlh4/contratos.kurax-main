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

if (isset($_POST["accion"]) && $_POST["accion"] === "reporte_nif16_bdt") {

	$empresa      = $_POST["empresa"];
	$centro_costo = $_POST["centro_costo"];
	$nomb_tienda  = $_POST["nomb_tienda"];
	$departamento = $_POST["departamento"];
	$provincia    = $_POST["provincia"];
	$distrito     = $_POST["distrito"];
	$direccion    = $_POST["direccion"];
	$fec_suscrip  = $_POST["fec_suscrip"];
	$fec_inicio   = $_POST["fec_inicio"];
	$fec_fin      = $_POST["fec_fin"];

	$where_empresa       = "";
	$where_locales       = "";
	$where_centro_costo  = "";
	$where_nombre_tienda = "";
	$where_ubigeo        = "";
	$where_direccion     = "";

	$where_fech_suscripcion = "";
	$where_fecha_inicio     = "";
	$where_fecha_fin        = "";

	if (!empty($empresa) && $empresa != "0") {
		$where_empresa = " AND c.empresa_suscribe_id IN (" . $empresa . ")";
	}
	if($login["usuario_locales"]){
		$where_locales = " AND l.id IN (".implode(",", $login["usuario_locales"]).")";
	}
	if (!empty($centro_costo) && $centro_costo != "0") {
		$where_centro_costo = "  AND c.cc_id LIKE '%" . $centro_costo . "%'";
	}
	if (!empty($nomb_tienda) && $nomb_tienda != "0") {
		$where_nombre_tienda = "  AND c.nombre_tienda LIKE '%" . $nomb_tienda . "%'";
	}
	if (!empty($direccion) && $direccion != "0") {
		$where_direccion = "  AND i.ubicacion LIKE '%" . $direccion . "%'";
	}
	if (!empty($departamento) && $departamento != "0") {
		$where_ubigeo .= "  AND dpo.cod_depa = '" . $departamento . "'";
	}
	if (!empty($provincia) && $provincia != "0") {
		$where_ubigeo .= "  AND prv.cod_prov = '" . $provincia . "'";
	}
	if (!empty($distrito) && $distrito != "0") {
		$where_ubigeo .= "  AND dto.cod_dist = '" . $distrito . "'";
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

	$query_adendas = "
	SELECT
	c.contrato_id,
	c.nombre_tienda,
	GROUP_CONCAT(DISTINCT per.nombre SEPARATOR ' y ') AS arrendador,
	rz.nombre AS arrendatario,
	CONCAT('Terceros') AS tipo_relacion,
	CONCAT('Inmuebles: Agencias y oficinas') AS tipo_activo,
	CONCAT('No') AS bajo_valor,
	IF(c.cc_id IS NULL,'',c.cc_id) AS centro_logistico,
	m.sigla AS moneda,
	ce.fecha_inicio AS fecha_inicio,
	ce.fecha_fin AS fecha_final,
	CONCAT('Inicio de periodo') AS tipo_pago,
	CONCAT('Mensual') AS frecuencia_pago,
	tpr.nombre AS fijo_variable,
	ce.monto_renta AS importe_renta,
	IF(ce.pago_renta_id = 2, CONCAT(ce.cuota_variable,'% del ',tv.nombre),'') AS cuota_variable,
	tai.nombre AS afecto_igv,
	tir.nombre AS incluye_ir,
	CONCAT('Arrendamiento') AS califica_nif16,

	IF ( (SELECT COUNT(ad.id) AS total FROM cont_adelantos AS ad WHERE ad.contrato_id =  c.contrato_id) > 0, 'SI','NO') AS existe_pagos_anticipados,
	(SELECT COUNT(ad.id) AS total FROM cont_adelantos AS ad WHERE ad.contrato_id =  c.contrato_id)  AS total_pagos_anticipados,
	IF ( (SELECT COUNT(ad.id) AS total FROM cont_adelantos AS ad WHERE ad.contrato_id =  c.contrato_id) > 0, ((SELECT COUNT(ad.id) AS total FROM cont_adelantos AS ad WHERE ad.contrato_id =  c.contrato_id) * ce.monto_renta),'') AS pago_inicial_anticipado,
	IF ( ce.periodo_gracia_id = 1 AND TRUNCATE(( IF(ce.periodo_gracia_numero > 0, ce.periodo_gracia_numero ,0 ) / 30  ) ,0) >= 1,  'SI','NO') AS existe_periodo_gracia,
	TRUNCATE(( IF(ce.periodo_gracia_numero > 0, ce.periodo_gracia_numero ,0 ) / 30  ) ,0) AS periodo_gracia_numero,
	IF ( c.tipo_inflacion_id = 1,  'SI','NO') AS existe_inflacion,
	inf.fecha AS inf_fecha_ajuste,
	CONCAT(tp.nombre,' ',inf.numero,' ',pr.nombre) AS periocidad_ajuste,
	mi.sigla AS curva_inflacion,
	inf.porcentaje_anadido AS porcentaje_anadido,
	inf.tope_inflacion AS tope_inflacion,
	inf.minimo_inflacion AS minimo_inflacion,

	IF(ce.tipo_incremento_id = 1,IF(inc.tipo_continuidad_id = 3, 'SI','NO'),'NO') AS tipo_incremento,
	(CASE
		WHEN inc.tipo_continuidad_id = 3 THEN CONCAT(tci.nombre)
		ELSE ''
	END) AS continuidad,
	DATE_ADD(ce.fecha_inicio, INTERVAL 1 YEAR) AS fecha_inicio_incremento,
	IF(inc.tipo_continuidad_id = 3, IF(inc.tipo_valor_id = 2, CONCAT(inc.valor, ' %'), CONCAT(m.simbolo, ' ', inc.valor) ) ,'') AS incremento,

	IF(c.tipo_cuota_extraordinaria_id = 1, 'SI','NO') AS cuota_extraordinaria,
	mes.nombre AS mes_extraordinario,
	cex.multiplicador AS multiplicador,
	IF(c.tipo_cuota_extraordinaria_id = 1, '12','') AS cuantos_meses_prox_pago

	FROM cont_contrato AS c
	INNER JOIN cont_inmueble i ON i.contrato_id = c.contrato_id AND i.status = 1
	INNER JOIN tbl_ubigeo AS dpo ON dpo.cod_depa = SUBSTRING(i.ubigeo_id, 1, 2) AND dpo.cod_prov = '00' AND dpo.cod_dist = '00'
	INNER JOIN tbl_ubigeo AS prv ON prv.cod_depa = SUBSTRING(i.ubigeo_id, 1, 2) AND SUBSTRING(i.ubigeo_id, 3, 2) = prv.cod_prov AND prv.cod_dist = '00'
	INNER JOIN tbl_ubigeo AS dto ON dto.cod_depa = SUBSTRING(i.ubigeo_id, 1, 2) AND SUBSTRING(i.ubigeo_id, 3, 2) = dto.cod_prov AND SUBSTRING(i.ubigeo_id, 5, 2) = dto.cod_dist

	INNER JOIN tbl_razon_social AS rz ON rz.id = c.empresa_suscribe_id
	INNER JOIN cont_propietario AS p ON p.contrato_id = c.contrato_id
	INNER JOIN cont_persona AS per ON per.id = p.persona_id

	INNER JOIN tbl_locales AS l ON l.contrato_id = c.contrato_id

	INNER JOIN cont_condicion_economica AS ce ON ce.contrato_id = c.contrato_id
	INNER JOIN tbl_moneda AS m ON m.id = ce.tipo_moneda_id
	INNER JOIN cont_tipo_pago_renta tpr ON tpr.id = ce.pago_renta_id
	LEFT JOIN cont_tipo_afectacion_igv tai ON tai.id = ce.afectacion_igv_id
	LEFT JOIN cont_tipo_venta tv ON tv.id = ce.tipo_venta_id
	LEFT JOIN cont_tipo_impuesto_a_la_renta tir ON tir.id = ce.impuesto_a_la_renta_id

	LEFT JOIN cont_inflaciones AS inf ON inf.contrato_id = c.contrato_id
	LEFT JOIN cont_tipo_periodicidad AS tp ON tp.id = inf.tipo_periodicidad_id
	LEFT JOIN tbl_moneda AS mi ON mi.id = inf.moneda_id
	LEFT JOIN cont_periodo as pr ON pr.id = inf.tipo_anio_mes

	LEFT JOIN cont_incrementos AS inc ON inc.contrato_id = c.contrato_id AND inc.estado = 1
	LEFT JOIN cont_tipo_pago_incrementos AS tpi ON inc.tipo_valor_id = tpi.id
	LEFT JOIN cont_tipo_continuidad_pago AS tci ON inc.tipo_continuidad_id = tci.id

	LEFT JOIN cont_cuotas_extraordinarias AS cex ON cex.contrato_id = c.contrato_id
	LEFT JOIN tbl_meses AS mes ON mes.id = cex.mes

	WHERE c.tipo_contrato_id = 1
	AND c.etapa_id = 5
	AND c.status = 1
	AND p.status = 1
	AND ce.status = 1
	AND TIMESTAMPDIFF(MONTH, ce.fecha_inicio, ce.fecha_fin) >= 12

	AND (inf.status = 1 OR inf.status IS NULL)
	AND (cex.status = 1 OR cex.status IS NULL)

	$where_empresa
	$where_centro_costo
	$where_nombre_tienda
	$where_direccion
	$where_ubigeo
	$where_fech_suscripcion
	$where_fecha_inicio
	$where_fecha_fin
	$where_locales

	GROUP BY contrato_id
	";

	$list_query_adendas = $mysqli->query($query_adendas);
	$array_pre_data     = array();
	while ($li = $list_query_adendas->fetch_assoc()) {
		array_push($array_pre_data, array(
			'contrato_id'              => $li['contrato_id'],
			'nombre_tienda'            => $li['nombre_tienda'],
			'arrendador'               => $li['arrendador'],
			'arrendatario'             => $li['arrendatario'],
			'tipo_relacion'            => $li['tipo_relacion'],
			'tipo_activo'              => $li['tipo_activo'],
			'bajo_valor'               => $li['bajo_valor'],
			'centro_logistico'         => $li['centro_logistico'],
			'moneda'                   => $li['moneda'],
			'fecha_inicio'             => $li['fecha_inicio'],
			'fecha_final'              => $li['fecha_final'],
			'tipo_pago'                => $li['tipo_pago'],
			'frecuencia_pago'          => $li['frecuencia_pago'],
			'fijo_variable'            => $li['fijo_variable'],
			'importe_renta'            => $li['importe_renta'],
			'cuota_variable'           => $li['cuota_variable'],
			'afecto_igv'               => $li['afecto_igv'],
			'incluye_ir'               => $li['incluye_ir'],
			'califica_nif16'           => $li['califica_nif16'],
			'existe_pagos_anticipados' => $li['existe_pagos_anticipados'],
			'total_pagos_anticipados'  => $li['total_pagos_anticipados'],
			'pago_inicial_anticipado'  => $li['pago_inicial_anticipado'],
			'existe_periodo_gracia'    => $li['existe_periodo_gracia'],
			'periodo_gracia_numero'    => $li['periodo_gracia_numero'],
			'existe_inflacion'         => $li['existe_inflacion'],
			'inf_fecha_ajuste'         => $li['inf_fecha_ajuste'],
			'periocidad_ajuste'        => $li['periocidad_ajuste'],
			'curva_inflacion'          => $li['curva_inflacion'],
			'porcentaje_anadido'       => $li['porcentaje_anadido'],
			'tope_inflacion'           => $li['tope_inflacion'],
			'minimo_inflacion'         => $li['minimo_inflacion'],
			'tipo_incremento'          => $li['tipo_incremento'],
			'fecha_inicio_incremento'  => $li['fecha_inicio_incremento'],
			'continuidad'              => $li['continuidad'],
			'incremento'               => $li['incremento'],
			'cuota_extraordinaria'     => $li['cuota_extraordinaria'],
			'mes_extraordinario'       => $li['mes_extraordinario'],
			'multiplicador'            => $li['multiplicador'],
			'cuantos_meses_prox_pago'  => $li['cuantos_meses_prox_pago'],
		));
	}
	$html = '
	<table id="sec_rep_vigencia" class="table table-striped table-hover table-condensed table-bordered" cellspacing="0" width="100%">
		<thead>
			<tr>
				<th class="text-center">ID del Contrato</th>
				<th class="text-center">Arrendador</th>
				<th class="text-center">Arrendatario</th>
				<th class="text-center">Tipo de relación <br> comercial con el <br> arrendador</th>
				<th class="text-center">Tipo de <br> activo <br> arrendado</th>
				<th class="text-center">¿Es un arrendamiento <br> de bajo valor? <br> (Low value leasing)</th>
				<th class="text-center">Centro <br> Logístico</th>
				<th class="text-center">Moneda <br> del <br> contrato</th>
				<th class="text-center">Fecha de <br> inicio del <br> contrato</th>
				<th class="text-center">Fecha <br> Final del <br> Contrato</th>
				<th class="text-center">Tipo de pago</th>
				<th class="text-center">Frecuencia <br> de pago</th>
				<th class="text-center">¿El pago de la renta es <br> totalmente fijo, totalmente <br> variable o combinado?</th>
				<th class="text-center">Importe  de <br> renta/cuota fijo</th>
				<th class="text-center">Importe  de <br> renta/cuota variable <br> (solo informativo)</th>
				<th class="text-center">¿La renta incluye IGV, <br> no incluye IGV o está <br> inafecta al IGV?</th>
				<th class="text-center">La renta incluye el <br> impuesto a la renta y/o es <br> asumida por el arrendatario</th>
				<th class="text-center">¿Califica como un <br> contrato de arrendamiento <br> bajo NIIF 16?</th>
				<th class="text-center">¿Existen pagos <br> anticipados <br> al inicio?</th>
				<th class="text-center">¿Cuántos periodos <br> son pagados <br> anticipadamente?</th>
				<th class="text-center">¿A cuánto asciende <br> el pago inicial <br> anticipado?</th>
				<th class="text-center">¿Existen periodos <br> de gracia?</th>
				<th class="text-center">¿Cuántos <br> periodos  de <br> gracia son?</th>
				<th class="text-center">¿Se ajusta la <br> cuota de acuerdo <br> a la inflación?</th>
				<th class="text-center">Fecha en que <br> se realiza el <br> primer ajuste</th>
				<th class="text-center">Periodicidad <br> del Ajuste</th>
				<th class="text-center">Curva de <br> inflacion </th>
				<th class="text-center">Porcentaje <br> añadido a la <br> inflación</th>
				<th class="text-center">Tope de <br> Inflación</th>
				<th class="text-center">Minimo de <br> Inflación</th>
				<th class="text-center">¿La renta/cuota <br> pagada se incrementa <br> periódicamente?</th>
				<th class="text-center">¿A partir de <br> qué fecha  ocurre <br> el incremento?</th>
				<th class="text-center">¿Cada cuánto <br> se incrementa <br> la renta?</th>
				<th class="text-center">¿Cuál es el <br> porcentaje de incremento <br> de la renta/ cuota?</th>
				<th class="text-center">¿Existe pago <br> extraordinario?</th>
				<th class="text-center">Mes en el <br> que se paga <br> extraordinariamente</th>
				<th class="text-center">Multiplicador <br> extraordinario</th>
				<th class="text-center">¿Cuántos meses <br> después existe <br> otro pago?</th>
				<th class="text-center">Importe de <br> costos de <br> transacción </th>
				<th class="text-center">¿Se cambio el <br> plazo del <br> contrato?</th>
				<th class="text-center">¿Cambió en la <br> condiciones del <br> activo subyacente?</th>
				<th class="text-center">¿Hubo un cambio <br> de moneda en la renta <br> del contrato?</th>
				<th class="text-center">¿Se cambió la renta fija <br> por una renta variable por el <br> resto del plazo del contrato?</th>
				<th class="text-center">Importe de <br> costos de <br> Desmantelamiento</th>
				<th class="text-center">Fecha <br> Terminación de <br> Inflación</th>
				<th class="text-center">Fecha Terminación <br> de cuota <br> extraordinaria</th>
				<th class="text-center">Contrato <br> relacionado</th>
				<th class="text-center">Contrato <br> Diferido?</th>

			</tr>
		</thead>
		<tbody>';
	for ($i = 0; $i < count($array_pre_data); $i++) {
		$fecha_incremento = "";
		if ($array_pre_data[$i]['tipo_incremento'] == "SI") {
			$fecha_incremento = $array_pre_data[$i]['fecha_inicio_incremento'];
		}
		$html .= '
			<tr>
				<td class="text-left">' . $array_pre_data[$i]['nombre_tienda'] . '</td>
				<td class="text-center">' . $array_pre_data[$i]['arrendador'] . '</td>
				<td class="text-center">' . $array_pre_data[$i]['arrendatario'] . '</td>
				<td class="text-center">' . $array_pre_data[$i]['tipo_relacion'] . '</td>
				<td class="text-center">' . $array_pre_data[$i]['tipo_activo'] . '</td>
				<td class="text-center">' . $array_pre_data[$i]['bajo_valor'] . '</td>
				<td class="text-center">' . $array_pre_data[$i]['centro_logistico'] . '</td>
				<td class="text-center">' . $array_pre_data[$i]['moneda'] . '</td>
				<td class="text-center">' . $array_pre_data[$i]['fecha_inicio'] . '</td>
				<td class="text-center">' . $array_pre_data[$i]['fecha_final'] . '</td>
				<td class="text-center">' . $array_pre_data[$i]['tipo_pago'] . '</td>
				<td class="text-center">' . $array_pre_data[$i]['frecuencia_pago'] . '</td>
				<td class="text-center">' . $array_pre_data[$i]['fijo_variable'] . '</td>
				<td class="text-right">' . $array_pre_data[$i]['importe_renta'] . '</td>
				<td class="text-center">' . $array_pre_data[$i]['cuota_variable'] . '</td>
				<td class="text-center">' . $array_pre_data[$i]['afecto_igv'] . '</td>
				<td class="text-center">' . $array_pre_data[$i]['incluye_ir'] . '</td>
				<td class="text-center">' . $array_pre_data[$i]['califica_nif16'] . '</td>
				<td class="text-center">' . $array_pre_data[$i]['existe_pagos_anticipados'] . '</td>
				<td class="text-center">' . $array_pre_data[$i]['total_pagos_anticipados'] . '</td>
				<td class="text-center">' . $array_pre_data[$i]['pago_inicial_anticipado'] . '</td>
				<td class="text-center">' . $array_pre_data[$i]['existe_periodo_gracia'] . '</td>
				<td class="text-center">' . $array_pre_data[$i]['periodo_gracia_numero'] . '</td>
				<td class="text-center">' . $array_pre_data[$i]['existe_inflacion'] . '</td>
				<td class="text-center">' . $array_pre_data[$i]['inf_fecha_ajuste'] . '</td>
				<td class="text-center">' . $array_pre_data[$i]['periocidad_ajuste'] . '</td>
				<td class="text-center">' . $array_pre_data[$i]['curva_inflacion'] . '</td>
				<td class="text-center">' . $array_pre_data[$i]['porcentaje_anadido'] . '</td>
				<td class="text-right">' . $array_pre_data[$i]['tope_inflacion'] . '</td>
				<td class="text-right">' . $array_pre_data[$i]['minimo_inflacion'] . '</td>
				<td class="text-right">' . $array_pre_data[$i]['tipo_incremento'] . '</td>
				<td class="text-right">' . $fecha_incremento . '</td>
				<td class="text-center">' . $array_pre_data[$i]['continuidad'] . '</td>
				<td class="text-right">' . $array_pre_data[$i]['incremento'] . '</td>
				<td class="text-right">' . $array_pre_data[$i]['cuota_extraordinaria'] . '</td>
				<td class="text-center">' . $array_pre_data[$i]['mes_extraordinario'] . '</td>
				<td class="text-center">' . $array_pre_data[$i]['multiplicador'] . '</td>
				<td class="text-right">' . $array_pre_data[$i]['cuantos_meses_prox_pago'] . '</td>
				<td class="text-center"> </td>
				<td class="text-center"> </td>
				<td class="text-center"> </td>
				<td class="text-center"> </td>
				<td class="text-center"> </td>
				<td class="text-center"> </td>
				<td class="text-center"> </td>
				<td class="text-center"> </td>
				<td class="text-center"> </td>
				<td class="text-center"> </td>
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