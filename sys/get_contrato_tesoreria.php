<?php
date_default_timezone_set("America/Lima");

include "db_connect.php";
include "sys_login.php";

if (isset($_POST["accion"]) && $_POST["accion"] === "listar_detalle_de_pagos") {
	$condicion_economica_id = $_POST['condicion_economica_id'];
	$provision_id           = $_POST['provision_id'];
	$tipo_id                = $_POST['tipo_id'];
	$anio                   = (int) $_POST['anio'];
	$row_count              = 0;
	$continuar              = false;

	$query = "
	SELECT
		c.contrato_id,
		rs.nombre AS arrendatario,
		c.nombre_tienda,
		c.cc_id AS centro_de_costo,
		m.simbolo AS simbolo_moneda,
		m.nombre AS moneda_contrato,
		ce.monto_renta,
		ce.impuesto_a_la_renta_id,
		i.nombre AS impuesto_a_la_renta,
		ce.carta_de_instruccion_id,
		ci.nombre AS carta_de_instruccion,
		dp.nombre AS dia_de_pago
	FROM
		cont_contrato c
		INNER JOIN tbl_razon_social rs ON c.empresa_suscribe_id = rs.id
		INNER JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id
		INNER JOIN tbl_moneda m ON ce.tipo_moneda_id = m.id
		INNER JOIN cont_tipo_impuesto_a_la_renta i ON ce.impuesto_a_la_renta_id = i.id
		LEFT JOIN cont_tipo_carta_de_instruccion ci ON ce.carta_de_instruccion_id = ci.id
		LEFT JOIN cont_tipo_dia_de_pago dp ON ce.dia_de_pago_id = dp.id
	WHERE
		ce.condicion_economica_id = $condicion_economica_id
	";

	$list_query = $mysqli->query($query);

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	} else {
		$row_count = $list_query->num_rows;

		if ($row_count > 0) {

			$row                       = $list_query->fetch_assoc();
			$arrendatario              = $row['arrendatario'];
			$nombre_tienda             = $row['nombre_tienda'];
			$centro_de_costo           = $row['centro_de_costo'];
			$simbolo_moneda            = $row["simbolo_moneda"];
			$moneda_contrato           = $row["moneda_contrato"];
			$monto_renta_sin_formato   = $row["monto_renta"];
			$impuesto_a_la_renta_id    = $row["impuesto_a_la_renta_id"];
			$impuesto_a_la_renta_texto = $row["impuesto_a_la_renta"];
			$carta_de_instruccion_id   = $row["carta_de_instruccion_id"];
			$carta_de_instruccion      = $row["carta_de_instruccion"];
			$dia_de_pago               = trim($row["dia_de_pago"]);

			// INICIO IMPUESTO A LA RENTA DETALLADO
			$factor              = 1.05265;
			$renta_bruta         = 0;
			$renta_neta          = 0;
			$impuesto_a_la_renta = 0;

			if ($impuesto_a_la_renta_id == 1) {
				$impuesto_a_la_renta = round($monto_renta_sin_formato * 0.05);
				$renta_bruta         = $monto_renta_sin_formato;

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
				$renta_bruta         = $monto_renta_sin_formato + round($impuesto_a_la_renta);
				$renta_neta          = $monto_renta_sin_formato;

				if ($carta_de_instruccion_id == 1) {
					$renta_neta = $monto_renta_sin_formato;
					$quien_paga = 'AT';
					$detalle    = 'AT deposita renta (' . $simbolo_moneda . ' ' . number_format($monto_renta_sin_formato, 2, '.', ',') . ' ' . $moneda_contrato . ') al Arrendador. AT realiza el pago del impuesto a la renta (' . $simbolo_moneda . ' ' . number_format($impuesto_a_la_renta, 0, '.', ',') . ' ' . $moneda_contrato . ') a SUNAT.';
				} elseif ($carta_de_instruccion_id == 2) {
					$renta_neta = $monto_renta_sin_formato + $impuesto_a_la_renta;
					$quien_paga = 'Arrendador';
					$detalle    = 'AT deposita ' . $simbolo_moneda . ' ' . number_format($renta_neta, 2, '.', ',') . ' ' . $moneda_contrato . ' al Arrendador. El Arrendador realiza el pago del impuesto a la renta (' . $simbolo_moneda . ' ' . number_format($impuesto_a_la_renta, 0, '.', ',') . ' ' . $moneda_contrato . ') a SUNAT.';
				}
			}

			$impuesto_a_la_renta = $simbolo_moneda . ' ' . number_format($impuesto_a_la_renta, 0, '.', ',') . ' ' . $moneda_contrato;
			$renta_bruta         = $simbolo_moneda . ' ' . number_format($renta_bruta, 2, '.', ',') . ' ' . $moneda_contrato;
			$renta_neta          = $simbolo_moneda . ' ' . number_format($renta_neta, 2, '.', ',') . ' ' . $moneda_contrato;
			// FIN IMPUESTO A LA RENTA DETALLADO

			$html = '';
			$html .= '<input type="hidden" id="condicion_economica_id" name="condicion_economica_id" value="' . $condicion_economica_id . '">';
			$html .= '<input type="hidden" id="provision_id" name="provision_id" value="' . $provision_id . '">';

			$html .= '<table class="table table-condensed table-hover">';

			$html .= '<tr>';
			$html .= '<td style="font-weight: bold;">Tienda</td>';
			$html .= '<td>' . $centro_de_costo . ' - ' . $nombre_tienda . '</td>';
			$html .= '</tr>';

			$html .= '<tr>';
			$html .= '<td style="font-weight: bold;">Arrendatario</td>';
			$html .= '<td>' . $arrendatario . '</td>';
			$html .= '</tr>';

			$html .= '<tr>';
			$html .= '<td style="font-weight: bold;">Día de Pago</td>';
			$html .= '<td>' . $dia_de_pago . '</td>';
			$html .= '</tr>';

			$html .= '<tr>';
			$html .= '<td style="font-weight: bold;">Impuesto a la Renta</td>';
			$html .= '<td>' . $impuesto_a_la_renta . '</td>';
			$html .= '</tr>';

			$html .= '<tr>';
			$html .= '<td style="font-weight: bold;">Renta Bruta</td>';
			$html .= '<td>' . $renta_bruta . '</td>';
			$html .= '</tr>';

			$html .= '<tr>';
			$html .= '<td style="font-weight: bold;">Renta a pagar</td>';
			$html .= '<td>' . $renta_neta . '</td>';
			$html .= '</tr>';

			$html .= '<tr>';
			$html .= '<td style="font-weight: bold;">Detalle</td>';
			$html .= '<td>' . $detalle . '</td>';
			$html .= '</tr>';

			$html .= '</table>';

			$continuar = true;
		}
	}

	if ($continuar) {
		$num_cuota_inicio = 0;
		$num_cuota_fin    = 12;

		$query_cuotas = "
		SELECT
			MAX(p.num_cuota) AS fin,
			MIN(p.num_cuota) AS inicio
		FROM
			cont_provision p
			INNER JOIN cont_condicion_economica ce ON p.condicion_economica_id = ce.condicion_economica_id
		WHERE
			ce.condicion_economica_id = $condicion_economica_id
			AND p.status = 1
			AND ce.status = 1
		";

		$list_query_cuotas = $mysqli->query($query_cuotas);

		if ($mysqli->error) {
			$result["consulta_error"] = $mysqli->error;
		} else {
			$row_count = $list_query_cuotas->num_rows;

			if ($row_count > 0) {
				$row = $list_query_cuotas->fetch_assoc();

				$num_cuota_inicio = $row['inicio'];
				$num_cuota_fin    = $row['fin'];
			}
		}

		$anio_del_num_cuota_inicio = floor(($num_cuota_inicio - 0.1) / 12) + 1;
		$anio_del_num_cuota_fin    = floor(($num_cuota_fin - 0.1) / 12);
		// $anio_del_num_cuota_provision = floor(($num_cuota_provision - 0.1) / 12);

		if ($anio_del_num_cuota_inicio >= -1 && $anio_del_num_cuota_fin >= 0) {
			$html .= '<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6" style="padding-left: 0px;">';
			$html .= '<select
				class="form-control select2"
				name="anio_del_contrato_id"
				id="anio_del_contrato_id"
				title="Seleccione el tipo"
				onchange="sec_contrato_tesoreria_consultar_detalle_de_pagos();">';

			for ($i = $anio_del_num_cuota_inicio; $i <= $anio_del_num_cuota_fin; $i++) {

				$selected = '';

				// if ($i == $anio_del_num_cuota_provision) {
				//    $selected = ' selected';
				// }

				$html .= '<option value="' . ($anio_del_num_cuota_inicio + 1) . '"' . $selected . '>' . ($anio_del_num_cuota_inicio + 1) . '.º año</option>';
				$anio_del_num_cuota_inicio++;
			}

			$html .= '</select>';
			$html .= '</div>';

		}

		$html .= '<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6" style="padding-right: 0px;">';
		$html .= '<select
				class="form-control select2"
				name="tipo_id"
				id="tipo_id"
				title="Seleccione el tipo"
				onchange="sec_contrato_tesoreria_consultar_detalle_de_pagos();">';
		$html .= '<option value="0">Todos</option>';
		$html .= '<option value="2">Renta</option>';
		$html .= '<option value="3">Impuesto a la renta</option>';
		$html .= '<option value="1">Adelantos</option>';
		$html .= '</select>';
		$html .= '</div>';

		$html .= '<br>';
		$html .= '<br>';

		$html .= sec_contrato_tesoreria_consultar_detalle_de_pagos_x_anio($condicion_economica_id, $tipo_id, $anio, $provision_id);

		if ($row_count == 0) {
			$result["http_code"] = 400;
			$result["result"]    = "No existen registros.";
		} elseif ($row_count > 0) {
			$result["http_code"] = 200;
			$result["status"]    = "Datos obtenidos de gestion.";
			$result["result"]    = $html;
		} else {
			$result["http_code"] = 400;
			$result["result"]    = "No se pudo encontrar registros.";
		}
	} else {
		$result["http_code"] = 400;
		$result["result"]    = "No existe el contrato.";
	}

}

if (isset($_POST["accion"]) && $_POST["accion"] === "listar_detalle_de_pagos_x_anio") {
	$condicion_economica_id = $_POST['condicion_economica_id'];
	$provision_id           = $_POST['provision_id'];
	$tipo_id                = $_POST['tipo_id'];
	$anio                   = (int) $_POST['anio'];
	$html                   = sec_contrato_tesoreria_consultar_detalle_de_pagos_x_anio($condicion_economica_id, $tipo_id, $anio, $provision_id);

	$result["http_code"] = 200;
	$result["status"]    = "Datos obtenidos de gestion.";
	$result["result"]    = $html;
}

function sec_contrato_tesoreria_consultar_detalle_de_pagos_x_anio($condicion_economica_id, $tipo_id, $anio, $provision_id)
{
	global $mysqli;

	$where_tipo = '';
	$where_anio = '';
	$html       = '';

	if ((int) $tipo_id > 0) {
		$where_tipo = " AND p.tipo_id = $tipo_id";
	}

	$num_cuota_desde = 0;
	$num_cuota_hasta = 12;

	if ($anio > 0) {
		$num_cuota_desde = (($anio - 1) * 12) + 1;
		$num_cuota_hasta = $anio * 12;

		if ($num_cuota_desde == 1) {
			$num_cuota_desde = 0;
		}
	}

	$where_anio = " AND p.num_cuota >= $num_cuota_desde AND p.num_cuota <= $num_cuota_hasta";

	$query = "
	SELECT
		ce.contrato_id,
		p.id AS provision_id,
		p.tipo_id,
		tp.nombre AS concepto,
		p.tipo_anticipo_id,
		ta.nombre AS tipo_anticipo,
		p.empresa_id,
		rs.nombre AS razon_social,
		p.moneda_id,
		m.nombre AS moneda,
		p.renta_bruta,
		p.importe,
		p.num_cuota,
		p.periodo_inicio,
		p.periodo_fin,
		p.anio,
		p.mes,
		p.dia_de_pago,
		p.forma_de_pago_id,
		p.num_adelanto_id,
		p.programado_id,
		p.num_ruc
	FROM
		cont_provision p
		INNER JOIN cont_condicion_economica ce ON p.condicion_economica_id = ce.condicion_economica_id
		INNER JOIN cont_tipo_programacion tp ON p.tipo_id = tp.id
		LEFT JOIN cont_tipo_anticipo ta ON p.tipo_anticipo_id = ta.id
		INNER JOIN tbl_razon_social rs ON p.empresa_id = rs.id
		INNER JOIN tbl_moneda m ON p.moneda_id = m.id
	WHERE
		ce.condicion_economica_id = $condicion_economica_id
		AND p.status = 1
		AND ce.status = 1
		$where_tipo
		$where_anio
	ORDER BY p.num_cuota ASC
	";

	$list_query = $mysqli->query($query);

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	} else {
		$row_count = $list_query->num_rows;

		if ($row_count > 0) {

			$array_incrementos = [];

			$html .= '<div id="div_detalle_de_pagos_x_anio">';
			$html .= '<table class="table table-condensed table-hover">';
			$html .= '<thead>';
			$html .= '<tr>';
			$html .= '<th>Concepto</th>';
			$html .= '<th>Cuota</th>';
			$html .= '<th>Vencimiento</th>';
			$html .= '<th>Importe</th>';
			$html .= '<th>Pago</th>';
			$html .= '<th>Observación</th>';
			$html .= '</tr>';
			$html .= '</thead>';
			$html .= '<tbody>';

			while ($li = $list_query->fetch_assoc()) {

				$color          = '';
				$simbolo_moneda = '';
				$moneda_contrato = '';

				if ($li['provision_id'] == $provision_id) {
					$color = ' style = "background-color: #05F3E1;" ';
				}

				if ($li['concepto'] == "1") {
					$simbolo_moneda = 'S/.';
					$moneda_contrato = 'Soles';
				} elseif ($li['concepto'] == "2") {
					$simbolo_moneda = 'US$';
					$moneda_contrato = 'Dolares';
				}

				$contrato_id = $li['contrato_id'];
				$concepto = $li['concepto'];
				$num_cuota = $li['num_cuota'];

				if ($li['tipo_id'] == "1") {
					$concepto .= ' (' . $li['tipo_anticipo'] . ')';
				}

				$importe = $simbolo_moneda . ' ' . number_format($li['importe'], 2, ".", ",");

				$fecha_vencimiento             = date_create($li['periodo_fin']);
				$fecha_vencimiento_con_formato = date_format($fecha_vencimiento, "d/m/Y");

				// INICIO INCREMENTO

				$query = $mysqli->query("
				SELECT
				i.valor,
				i.tipo_valor_id,
				tp.nombre AS tipo_valor, 
				i.tipo_continuidad_id,
				tc.nombre AS tipo_continuidad, 
				i.a_partir_del_año
				FROM cont_incrementos i
				INNER JOIN cont_tipo_pago_incrementos tp ON i.tipo_valor_id = tp.id
				INNER JOIN cont_tipo_continuidad_pago tc ON i.tipo_continuidad_id = tc.id
				WHERE i.contrato_id = $contrato_id
				AND i.estado = 1");
				$row_count = $query->num_rows;

				if ($row_count > 0) {
					$cont_incremento = 0;
					while ($row = $query->fetch_assoc()) {
						$array_incrementos[$cont_incremento][0] = $row["valor"];
						$array_incrementos[$cont_incremento][1] = $row["tipo_valor_id"];
						$array_incrementos[$cont_incremento][2] = $row["tipo_continuidad_id"];
						$array_incrementos[$cont_incremento][3] = $row["a_partir_del_año"];
						$array_incrementos[$cont_incremento][4] = $row["tipo_valor"];
						$array_incrementos[$cont_incremento][5] = $row["tipo_continuidad"];
						$cont_incremento++;
					}
				}

				$num_incrementos = count($array_incrementos);

				if ($mysqli->error) {
					$error .= $mysqli->error;
				}

				$contador_incremento_a_la_renta = 0;
				$incrementos = '';

				for ($i = 0; $i < $num_incrementos; $i++) {
					$valor = $array_incrementos[$i][0];
					$tipo_valor = $array_incrementos[$i][1];
					$tipo_continuidad =  $array_incrementos[$i][2];
					$a_partir_del_año =  $array_incrementos[$i][3] . ' año';
					$a_partir_del_anio_en_meses = (($array_incrementos[$i][3] - 1) * 12) + 1;
					$tipo_valor_texto =  $array_incrementos[$i][2];
					$tipo_continuidad =  $array_incrementos[$i][2];

					$tipo_valor_texto = '';

					if ($tipo_valor == 1) {
						$tipo_valor_texto = ' ' . $moneda_contrato;
						$valor = $simbolo_moneda . ' ' . $valor;
					} else if ($tipo_valor == 2) {
						$tipo_valor_texto = '%';
						if (substr($valor,-3,3) == ".00") {
							$valor = substr($valor,0,-3);
						}
					}

					if ($tipo_continuidad == 3) {
						$a_partir_del_año = '';
					}

					if ($tipo_continuidad == 1) { // EL
						if ($num_cuota == $a_partir_del_anio_en_meses) {
							$incrementos .= $valor . $tipo_valor_texto . ' ' . $tipo_continuidad . ' ' . $a_partir_del_año;
						}
					} elseif ($tipo_continuidad == 2 || $tipo_continuidad == 3) { // ANUAL A PARTIR DEL
						for ($j = $a_partir_del_anio_en_meses; $j <= $num_cuota; $j+=12) {
							if ($num_cuota == $j) {
								$incrementos .= $valor . $tipo_valor_texto . ' ' . $tipo_continuidad . ' ' . $a_partir_del_año;
							}
						}
					}
				}
				// FIN INCREMENTO

				$html .= '<tr' . $color . '>';
				$html .= '<td>' . $concepto . '</td>';
				$html .= '<td>' . $num_cuota . '</td>';
				$html .= '<td>' . $fecha_vencimiento_con_formato . '</td>';
				$html .= '<td align="right">' . $importe . '</td>';
				$html .= '<td>Pendiente</td>';
				$html .= '<td>' . $incrementos . '</td>';
				$html .= '</tr>';

			}

			$html .= '</tbody>';
			$html .= '</table>';
			$html .= '</div>';
		}
	}
	return $html;
}

echo json_encode($result);
