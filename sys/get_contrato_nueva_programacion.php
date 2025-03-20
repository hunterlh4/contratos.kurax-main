<?php
$result=array();
include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
date_default_timezone_set('America/Lima');
error_reporting(E_ALL);

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_acreedores_pendientes_de_pago") {

	$tipo_consulta = $_POST["tipo_consulta"];
	$provision_ids = $_POST["provision_ids"];
	$contador_array_ids = 0;
	$data = json_decode($provision_ids);
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
	
	$tipo_programacion_id = $_POST["tipo_programacion_id"];

	if ($tipo_consulta == '1') {
		$tipo_anticipo_id = $_POST["tipo_anticipo_id"];
		$tipo_concepto_id = $_POST["tipo_concepto_id"];
		$empresa_id = $_POST["empresa_id"];
		$empresa_nombre = $_POST["empresa_nombre"];
		$busqueda_por = $_POST["busqueda_por"];
		$mes = substr($_POST["mes_id"], 4, 2);
		$anio = substr($_POST["mes_id"], 0, 4);
		$dia_de_pago = $_POST["dia_de_pago"];
		$fecha_vencimiento_inicio = $_POST["fecha_vencimiento_inicio"];
		$fecha_vencimiento_fin =' '; //$_POST["fecha_vencimiento_fin"];
		$banco_de_acreedores = (int) $_POST["banco_de_acreedores"];
		$parametros_de_busqueda = '';
		$tipo_boton = $_POST["tipo_boton"];
		$moneda_id = 0;
		$moneda_nombre = '';
		if ($tipo_concepto_id == 7) {
			$moneda_id = 2;
			$moneda_nombre = 'Dolar';
		} elseif ($tipo_concepto_id == 8) {
			$moneda_id = 1;
			$moneda_nombre = 'Sol';
		}
	}

	$html = '';
	$tbody = '';
	$html2 = '';

	$ids_de_acreedores = '';
	$total_programado = 0;
	$usuario_id = $login?$login['id']:null;
	// verificar si el usuario tiene todos los permisos locales 
	$query = "SELECT  tul.usuario_id  FROM tbl_usuarios_locales tul WHERE tul.usuario_id=".$usuario_id;

	$list_query_permisos = $mysqli->query($query);

	if($tipo_programacion_id == 3) {

	$query = "
				SELECT p.contrato_id ,p.tipo_id   ,
				ce.condicion_economica_id,
				p.id AS provision_id,
				
				b.num_docu AS beneficiario_num_docu,
				b.nombre AS beneficiario_nombre,
				p.mes,
				p.anio,
				CONCAT('1683-', p.mes, p.anio) AS num_doc,
				p.periodo_fin AS fec_venc,
				(
					CASE
						WHEN ce.tipo_moneda_id = 1 THEN 'MN'
						WHEN ce.tipo_moneda_id = 2 THEN 'ME'
					END
				) AS moneda,
				p.importe AS programado,
				c.cc_id AS centro_de_costos,
				c.nombre_tienda AS nombre_tienda,
				rs.nombre AS razon_social,
				p.dia_de_pago AS dia_de_pago_id,
				p.tipo_id,
				tp.nombre AS tipo_programacion,
				
				p.tipo_anticipo_id,
				ma.nombre AS mes_adelanto,
				ba.nombre AS banco,
				ce.renta_adelantada_id
			FROM
				cont_provision p
				LEFT JOIN cont_provision_detalle pd ON p.id =pd.provision_id
				LEFT JOIN cont_contrato c ON c.contrato_id =p.contrato_id 
				LEFT JOIN tbl_razon_social rs ON rs.id =c.empresa_suscribe_id
				LEFT JOIN cont_condicion_economica ce ON p.condicion_economica_id = ce.condicion_economica_id
				

				LEFT JOIN cont_tipo_programacion tp ON p.tipo_id = tp.id
				
				 
				LEFT JOIN cont_tipo_mes_adelanto ma ON p.num_adelanto_id = ma.id 
				LEFT JOIN cont_beneficiarios b ON ce.contrato_id = b.contrato_id
				LEFT JOIN tbl_bancos ba ON b.banco_id = ba.id 
				LEFT JOIN tbl_usuarios_locales tul ON tul.local_id = tl.id
				
			WHERE
				p.status = 1
				AND ce.status = 1 
			
				AND c.status = 1
				 
				AND rs.estado_tesoreria=1
				 
			AND p.tipo_id = '3'

	";
	} else {

	$query = "
		SELECT 
			ce.condicion_economica_id,
			p.id AS provision_id,
			b.num_docu AS beneficiario_num_docu,
			b.nombre AS beneficiario_nombre,
			p.mes,
			p.anio,
			CONCAT('1683-', p.mes, p.anio) AS num_doc,
			p.fecha_actual AS fec_venc,
			(
				CASE
					WHEN ce.tipo_moneda_id = 1 THEN 'MN'
					WHEN ce.tipo_moneda_id = 2 THEN 'ME'
				END
			) AS moneda,
			p.importe AS programado,
			c.cc_id AS centro_de_costos,
			c.nombre_tienda AS nombre_tienda,
			rs.nombre AS razon_social,
			p.dia_de_pago AS dia_de_pago_id,
			p.tipo_id,
			tp.nombre AS tipo_programacion,
			ta.nombre AS tipo_anticipo,
			p.tipo_anticipo_id,
			ma.nombre AS mes_adelanto,
			ba.nombre AS banco,
			ce.renta_adelantada_id
		FROM
			cont_provision p
			LEFT JOIN cont_provision_detalle pd ON p.id =pd.provision_id
			LEFT JOIN cont_contrato c ON c.contrato_id =p.contrato_id 
			LEFT JOIN tbl_razon_social rs ON rs.id =c.empresa_suscribe_id
			LEFT JOIN cont_condicion_economica ce ON p.condicion_economica_id = ce.condicion_economica_id
			
			LEFT JOIN tbl_locales tl ON tl.contrato_id = c.contrato_id
			LEFT JOIN cont_tipo_programacion tp ON p.tipo_id = tp.id
			
			LEFT JOIN cont_tipo_anticipo ta ON p.tipo_anticipo_id = ta.id
			LEFT JOIN cont_tipo_mes_adelanto ma ON p.num_adelanto_id = ma.id 
			LEFT JOIN cont_beneficiarios b ON ce.contrato_id = b.contrato_id
			LEFT JOIN tbl_bancos ba ON b.banco_id = ba.id 
			LEFT JOIN tbl_usuarios_locales tul ON tul.local_id = tl.id

		WHERE
			p.status = 1
			AND ce.status = 1 
			 
			AND c.status = 1
			AND rs.estado_tesoreria=1
	";

	}
	 
	if ($tipo_consulta == '1') {
		// $query .=' AND p.etapa_id =1 ';
		if($tipo_boton==1){  
			if($_POST['programacion_id_edit']!=0){
				$query .= " AND (p.etapa_id = 1 OR p.etapa_id = 2 OR p.etapa_id = 3) ";
			}else{
				$query .= " AND (p.etapa_id = 1 OR p.etapa_id = 2) ";
				
			}
			 
			$roand = " AND (p.etapa_id = 1 OR p.etapa_id = 2) " ;
		}else{
			$roand = " AND p.etapa_id <> 5 " ;

			if($_POST['programacion_id_edit']!=0){
				$query .= " AND p.etapa_id <> 5" ;

			}else{
				$query .= " AND p.etapa_id <> 5 AND p.etapa_id <> 3" ;

				
			}
		}
	
		$etapa_id	=	1; // etapa de la provision 1: en pendiente 2: en propgramado
		if ($tipo_programacion_id != '0') {
			// $query .= " AND p.tipo_id = '" . $tipo_programacion_id . "'";

			$tipo_programacion_nombre = 'Tipo: ';
		   
			if ($tipo_programacion_id == 1) {
			$tipo_programacion_nombre .= 'Anticipo';
			// $query .= " AND p.tipo_id = '" . $tipo_programacion_id . "'";

			$query.= ' AND p.num_adelanto_id is NOT null '; // esto ya que la renta es renta normal , es decir no es anticipo 

			} elseif ($tipo_programacion_id == 2) {
			$tipo_programacion_nombre .= 'Renta';
			$query .= " AND p.tipo_id = '" . $tipo_programacion_id . "'";

			$query.= ' AND p.num_adelanto_id is null '; // esto ya que la renta es renta normal , es decir no es anticipo 
			
			} elseif ($tipo_programacion_id == 3) {
			$query .= " AND p.tipo_id = '" . $tipo_programacion_id . "'";

			$tipo_programacion_nombre .= 'Imp. Renta';
			}
		   
			$parametros_de_busqueda .= ' ' . $tipo_programacion_nombre . ' | ';
			
		}
		   

		  if ($empresa_id != '0') {
		  	$parametros_de_busqueda .= ' Empresa: ' . $empresa_nombre;
		 	$query .= " AND c.empresa_suscribe_id = '" . $empresa_id . "'";
		  }

		 if ($tipo_programacion_id !=3 && $moneda_id != '0') {
		  	$parametros_de_busqueda .= ' | Moneda: ' . $moneda_nombre;
		 	$query .= " AND ce.tipo_moneda_id = '" . $moneda_id . "'";
		}

		if ($busqueda_por == 1) {
			if ($dia_de_pago_desde != '' && $dia_de_pago_hasta != '') {

				if ($mes == 1) {
					$anio_renta_adelantada = $anio - 1;
					$mes_renta_adelantada = 12;
				} else {
					$anio_renta_adelantada = $anio;
					$mes_renta_adelantada = $mes - 1;
				}

				$periodo_fecha_inicio_renta_adelantada = $anio_renta_adelantada . '-' . $mes_renta_adelantada . '-18';
				$periodo_fecha_inicio_renta_adelantada_datetime = new DateTime( $periodo_fecha_inicio_renta_adelantada ); 
				$periodo_fecha_fin_renta_adelantada = $periodo_fecha_inicio_renta_adelantada_datetime->format( 'Y-m-t' );

				$periodo_fecha_inicio = $anio . '-' . $mes . '-01';
				$periodo_fecha_inicio_datetime = new DateTime( $periodo_fecha_inicio ); 
				$periodo_fecha_fin = $periodo_fecha_inicio_datetime->format( 'Y-m-t' );

				$periodo_fecha_inicio_datetime->modify('last day of this month');
				$periodo_fecha_inicio_renta_adelantada_datetime_caso_2 = $periodo_fecha_inicio_datetime->modify('+1 day');
				$periodo_fecha_inicio_renta_adelantada_caso_2 = $periodo_fecha_inicio_renta_adelantada_datetime_caso_2->format( 'Y-m-d' );
				$periodo_fecha_fin_renta_adelantada_caso_2 = $periodo_fecha_inicio_renta_adelantada_datetime_caso_2->format( 'Y-m-t' );

				$parametros_de_busqueda .= '  Día de pago del ' . $dia_de_pago_desde . ' al ' . $dia_de_pago_hasta;
				$query .= " AND (";
				$query .= " 	( p.periodo_inicio BETWEEN '$periodo_fecha_inicio_renta_adelantada' AND '$periodo_fecha_fin_renta_adelantada' ) ";
				$query .= " 	OR ";
				$query .= " 	(";
				$query .= "			( p.periodo_fin BETWEEN '$periodo_fecha_inicio' AND '$periodo_fecha_fin' )";
				$query .= "			AND";
				$query .= "			( ce.dia_de_pago_id >= $dia_de_pago_desde AND ce.dia_de_pago_id <= $dia_de_pago_hasta )";
				$query .= " 	) ";
				$query .= " 	OR ";
				$query .= " 	(";
				$query .= "			( ce.renta_adelantada_id = 1 )";
				$query .= "			AND";
				$query .= "			( p.periodo_fin BETWEEN '$periodo_fecha_inicio_renta_adelantada_caso_2' AND '$periodo_fecha_fin_renta_adelantada_caso_2' )";
				$query .= "			AND";
				$query .= "			( ce.dia_de_pago_id >= $dia_de_pago_desde AND ce.dia_de_pago_id <= $dia_de_pago_hasta )";
				$query .= " 	) ";
				$query .= " )";
			}
		} elseif ($busqueda_por == 2) {
			if ($fecha_vencimiento_inicio != '' && $fecha_vencimiento_fin != '') {
				$fecha = DateTime::createFromFormat("Y-m-d", $fecha_vencimiento_inicio);
				if (!$fecha) {
					// Si la fecha no pudo ser creada a partir del formato "Y-m-d", intentar con "d/m/Y"
					$fecha = DateTime::createFromFormat("d/m/Y", $fecha_vencimiento_inicio);
				}
				
				if ($fecha) {
					// La fecha es válida, formatearla a "Y-m-d"
					$fecha_limite = $fecha->format("Y-m");
				} 
				// $fecha_limite = $fecha->format("Y-m");
				// var_dump($fecha_limite);exit();

				$parametros_de_busqueda .= '  Fecha vcto. desde ' . $fecha_vencimiento_inicio . ' hasta ' . $fecha_vencimiento_fin;
				$fecha_vencimiento_inicio = date('Y-m-d', strtotime(str_replace('/', '-', $fecha_vencimiento_inicio)));
				$fecha_vencimiento_fin = date('Y-m-d', strtotime(str_replace('/', '-', $fecha_vencimiento_fin)));
			   
				$periodo_fecha_inicio_datetime = new DateTime( $fecha_vencimiento_fin ); 
				$periodo_fecha_inicio_datetime->modify('last day of this month');
				$periodo_fecha_inicio_renta_adelantada_datetime_caso_2 = $periodo_fecha_inicio_datetime->modify('+1 day');
				$periodo_fecha_inicio_renta_adelantada_caso_2 = $periodo_fecha_inicio_renta_adelantada_datetime_caso_2->format( 'Y-m-d' );
				$periodo_fecha_fin_renta_adelantada_caso_2 = $periodo_fecha_inicio_renta_adelantada_datetime_caso_2->format( 'Y-m-t' );
			   
				$query .= " AND p.fecha_actual = '".$fecha_limite."'";
				// $query .= " ( p.periodo_inicio BETWEEN '" . $fecha_vencimiento_inicio . "' AND '" . $fecha_vencimiento_fin . "' )";
				// $query .= " OR ";
				// $query .= " (";
				// $query .= " ( ce.renta_adelantada_id = 1 )";
				// $query .= " AND";
				// $query .= " ( p.periodo_inicio BETWEEN '$periodo_fecha_inicio_renta_adelantada_caso_2' AND '$periodo_fecha_fin_renta_adelantada_caso_2' )";
				// $query .= " ) ";
				// $query .= " )";
				}
		}

		if ($ids != '') {
			$query .= " AND p.id NOT IN(" . $ids . ")";
		}

		if ($tipo_programacion_id == 1 && $tipo_anticipo_id != 'T') {
			$query .= " AND p.tipo_anticipo_id = $tipo_anticipo_id";
		}

		if ($banco_de_acreedores === 1) {
			$query .= " AND b.banco_id = 12";
		} elseif ($banco_de_acreedores === 2) {
			$query .= " AND b.banco_id != 12";
		}
		if($dia_de_pago > 0) { // SI HAY UN FILTRO DE DIAS , EN CASO CONTRARIO SELECCIOAN TODOS
			$query.= "AND p.dia_de_pago = ".$dia_de_pago;

		}
	} else {
		// $query .=' AND p.etapa_id =2 ';
		$roand = "TIPO CONSULTA 2 ".$tipo_consulta;
		if($_POST["programacion_id_edit"]!=0){
			$etapa_id	=	3;// etapa de la provision 1: en pendiente 2: en propgramado

		}else{
			$etapa_id	=	2;// etapa de la provision 1: en pendiente 2: en propgramado

		}

		$query .= " AND p.id IN(" . $ids . ")";
	}

	// if(isset($_POST["programacion_id_edit"]) && $_POST["programacion_id_edit"]!=0){
	// 	$query .=' AND p.etapa_id =2 ';

	// }
	
    if ($list_query_permisos->num_rows > 0) {
		$query.= ' AND  tul.usuario_id ='.$usuario_id.' GROUP BY p.id';
	}else{
		$query.= ' GROUP BY p.id';

	} 
	$query .= " ORDER BY p.periodo_inicio";
	$list_query = $mysqli->query($query);
	// var_dump($query);exit();
	if($mysqli->error){
		enviar_error($mysqli->error . $query);
		 
	}
	$row_count = $list_query->num_rows;

	if ($row_count > 0) {
		$num = 1;

		while ($row = $list_query->fetch_assoc()) {
			$condicion_economica_id = $row["condicion_economica_id"];
			$provision_id = $row["provision_id"];
			$tipo_id = $row["tipo_id"];
			$tipo_programacion = $row["tipo_programacion"];
			$num_ruc = $row["beneficiario_num_docu"];
			$acreedor = $row["beneficiario_nombre"];

			if ($tipo_id == 1) {
				$tipo_programacion = $row["tipo_anticipo"];

				if ($row["tipo_anticipo_id"] == 2) {
					$tipo_programacion .= ' del ' . $row["mes_adelanto"];
				}
			} elseif ($tipo_id == 3) {
				// $num_ruc = '';
				// $acreedor = 'ARRENDADOR PRUEBA';

				// $num_ruc = '';

				// $acreedor = '';
			}

			$num_doc = '1683-' . str_pad($row["mes"], 2, "0", STR_PAD_LEFT) . $row["anio"];

			$renta_adelantada = $row["renta_adelantada_id"] == 1 ? 'Si' : 'No'; 

			$tbody .= '<tr>';
			$tbody .= '<td><a onclick="sec_contrato_tesoreria_ver_detalle_de_pagos('. $condicion_economica_id .', ' . $provision_id . ', 0, 0)">' . $num . '</a></td>';
			$tbody .= '<td>' . $tipo_programacion . '</td>';
			$tbody .= '<td>' . $num_ruc . '</td>';
			$tbody .= '<td>' . $acreedor . '</td>';
			$tbody .= '<td>' . $row["dia_de_pago_id"] . '</td>';
			$tbody .= '<td>' . $row["fec_venc"] . '</td>';
			$tbody .= '<td>' . $row["moneda"] . '</td>';
			$tbody .= '<td>' . number_format($row["programado"], 2, '.', ',') . '</td>';
			$tbody .= '<td>' . $row["centro_de_costos"] . ' - ' . $row["nombre_tienda"] . '</td>';
			$tbody .= '<td>' . $row["banco"] . '</td>';
			$tbody .= '<td>' . $renta_adelantada . '</td>';
			$tbody .= '<td>';

			if ($tipo_consulta == '1') {
				$tbody .= '<a class="btn btn-success btn-xs" data-toggle="tooltip" data-placement="top" title="Agregar a la programación de pagos" onclick="sec_contrato_nueva_programacion_agregar_a_la_programacion(' . $row["provision_id"] . ')">';
				$tbody .= '<i class="fa fa-plus"></i>';
				$tbody .= '</a>';
			} else {
				$tbody .= '<a class="btn btn-warning btn-xs" data-toggle="tooltip" data-placement="top" title="Quitar de la programación de pagos" onclick="sec_contrato_nueva_programacion_quitar_de_la_programacion(' . $row["provision_id"] . ')">';
				$tbody .= '<i class="fa fa-minus"></i>';
				$tbody .= '</a>';
			}

			$tbody .= '</td>';
			$tbody .= '</tr>';

			if ($num == 1) {
				$ids_de_acreedores .= $row["provision_id"];
			} else {
				$ids_de_acreedores .= "," . $row["provision_id"];
			}

			$total_programado += $row["programado"];
			$num += 1;
		}

		$colspan = 13;

		$html .= '<table class="table table-bordered table-striped table-hover no-mb table-comprimido" style="font-size:10px; margin-top: 10px;">';
		$html .= '<thead>';

		$html .= '<tr>';
		$html .= '<th colspan="' . $colspan . '" style="background-color: #E5E5E5;">';
		$html .= '<div class="col-xs-10 col-sm-10 col-md-10 col-lg-10" style="font-size: 13px; text-align: left; margin-top: 4px;">';

		if ($tipo_consulta == '1') {
			$html .= 'Pendiente de pago ( ' . trim($parametros_de_busqueda) . ' ):';
			$html .= '</div>';
			$html .= '<div class="col-xs-2 col-sm-2 col-md-2 col-lg-2" style="text-align: right;">';
			$html .= '<button type="button" class="btn btn-success btn-xs" title="Agregar todos los Acreedores" style="width: 110px;" onclick="sec_contrato_nueva_programacion_agregar_varios_a_la_programacion(' . $ids_de_acreedores . ')">';
			$html .= '<i class="fa fa-plus"></i>';
			$html .= ' Agregar todos';
		} else {
			$html .= 'Programación de pago:';
			$html .= '</div>';
			$html .= '<div class="col-xs-2 col-sm-2 col-md-2 col-lg-2" style="text-align: right;">';
			$html .= '<button type="button" class="btn btn-warning btn-xs" title="Quitar todos los Acreedores" style="width: 110px;" onclick="sec_contrato_nueva_programacion_quitar_varios_a_la_programacion(' . $ids_de_acreedores . ')">';
			$html .= '<i class="fa fa-minus"></i>';
			$html .= ' Quitar todos';
		}

		$html .= '</button>';
		$html .= '</div>';
		$html .= '</th>';
		$html .= '</tr>';

		$html .= '<tr>';
		$html .= '<th>#</th>';
		$html .= '<th>Tipo</th>';
		$html .= '<th>N. Doc</th>';
		$html .= '<th>Acreedor</th>';
		$html .= '<th>Día de pago</th>';
		$html .= '<th>Periodo</th>';
		$html .= '<th>Moneda</th>';
		$html .= '<th>Total a pagar</th>';
		$html .= '<th>Centro de costos</th>';
		$html .= '<th>Banco</th>';
		$html .= '<th>Renta Adelantada</th>';

		if ($tipo_consulta == '1') {
			$html .= '<th>Agregar</th>';
		} else {
			$html .= '<th>Quitar</th>';
		}

		$html .= '</tr>';

		$html .= '</thead>';
		$html .= '<tbody>';
		$html .= $tbody;
		if ($tipo_consulta == '2') {
			$html2 .= '<table class="table table-bordered" style="font-size: 12px">';
			$html2 .= '<thead>';

			$html2 .= '<tr>';
			$html2 .= '<th colspan="' . $colspan . '" style="text-align: right; background-color: #E5E5E5; padding: 4px;">';
			$html2 .= '</th>';
			$html2 .= '</tr>';
			$html2 .= '<tr style="font-size: 13px;">';
			$html2 .= '<th colspan="' . ( $colspan - 1 ) . '" style="text-align: right;">';
			$html2 .= 'Total acreedores:';
			$html2 .= '</th>';
			$html2 .= '<th style="text-align: right;">';
			$html2 .= $row_count;
			$html2 .= '</th>';
			$html2 .= '</tr>';
			$html2 .= '<tr style="font-size: 13px;">';
			$html2 .= '<th colspan="' . ( $colspan - 1 ) . '" style="text-align: right;">';
			$html2 .= 'Total monto:';
			$html2 .= '</th>';
			$html2 .= '<th style="text-align: right;">';
			$html2 .= number_format($total_programado, 2, '.', ',');
			$html2 .= '</th>';
			$html2 .= '</tr>';
			$html2 .= '</thead>';
			$html2 .= '</table>';
		}

		$html .= '</tbody>';
		$html .= '</table>';
	} else if ($row_count == 0){
		$html .= '<table class="table table-bordered table-striped no-mb table-comprimido" style="font-size:12px; margin-top: 10px;">';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th style="background-color: #E5E5E5;">';

		if ($tipo_consulta == '1') {
			$html .= '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="font-size: 14px; text-align: left;">';
			$html .= 'Pendiente de pago (' . trim($parametros_de_busqueda) . '):';
			$html .= '</div>';
		} else {
			$html .= '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12" style="font-size: 14px" style="text-align: left;">';
			$html .= 'Programación de pago:';
			$html .= '</div>';
		}

		$html .= '</th>';
		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody>';
		$html .= '<tr>';

		if ($tipo_consulta == '1') {
			$html .= '<td style="text-align: center;">No existen acreedores pendiente de pagos con los parámetros de búsqueda ingresados.</td>';
		} else {
			$html .= '<td style="text-align: center;">No se han agregado acreedores pendientes de pago a la programación de pago.</td>';
		}

		$html .= '</tr>';
		$html .= '</tbody>';
		$html .= '</table>';
	}
	//actualizar etapa
	$update_query = "
	 UPDATE
	 cont_provision
	SET etapa_id ='" . $etapa_id . "'
	WHERE id in (" . $ids_de_acreedores . ")";
	$mysqli->query($update_query);

	if ($mysqli->error) {
		$error = 'Error al actualizar la programacion de pago: ' . $mysqli->error . $update_query;
		$result["error"] = $error;
	}
	if ($row_count >= 0) {
		$result['RONALDO'] = $roand;

		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $html;
		$result["result_footer_totales"] = $html2;
	} else {
		$result["http_code"] = 400;
		$result["error"] = $error;
		$result["result"] = "Los acreedores no existe.";
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_concepto") {

	$tipo_programacion_id = $_POST["tipo_programacion_id"];

	if ($tipo_programacion_id == 3) {
		$concepto_id = '8';
	} else {
		$concepto_id = '7,8';
	}

	$query = "
	SELECT id, nombre
	FROM cont_tipo_concepto
	WHERE status = 1
	AND id IN($concepto_id)
	ORDER BY nombre ASC
	";
	
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
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_empresas") {

	$tipo_programacion_id = $_POST["tipo_programacion_id"];
	$tipo_concepto_id = $_POST["tipo_concepto_id"];

	if ($tipo_programacion_id == 3) {
		$tipo_pago_id = '3';
	} else {
		$tipo_pago_id = '1,2';
	}

	if ($tipo_concepto_id == 7) {
		$moneda_id = 2;
	} elseif ($tipo_concepto_id == 8) {
		$moneda_id = 1;
	}

	$query = "
	SELECT DISTINCT
		r.id,
		r.nombre
	FROM 
		cont_num_cuenta c
		INNER JOIN tbl_razon_social r ON c.razon_social_id = r.id
	WHERE 
		c.moneda_id = $moneda_id
		AND c.tipo_pago_id IN ($tipo_pago_id)
		AND c.status = 1
		AND r.estado_tesoreria=1
	ORDER BY nombre ASC
	";
	
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
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_numeros_de_cuentas") {

	$concepto_id = $_POST["concepto_id"];
	$tipo_programacion_id = $_POST["tipo_programacion_id"];
	$empresa_id = $_POST["empresa_id"];

	if ($tipo_programacion_id == 3) {
		$tipo_pago_id = '3';
	} else {
		$tipo_pago_id = '1,2';
	}

	if ($concepto_id == 7) {
		$moneda_id = 2;
	} elseif ($concepto_id == 8) {
		$moneda_id = 1;
	}

	$query = "
	SELECT 
		c.id,
		CONCAT(b.nombre, ' ', c.num_cuenta_corriente, ' ', m.nombre) AS nombre
	FROM 
		cont_num_cuenta c
		INNER JOIN tbl_moneda m ON c.moneda_id = m.id
		INNER JOIN tbl_bancos b ON c.banco_id = b.id
	WHERE 
		c.razon_social_id = $empresa_id 
		AND c.moneda_id = $moneda_id
		AND c.tipo_pago_id IN ($tipo_pago_id)
		AND c.status = 1
	ORDER BY nombre ASC
	";
	
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
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_tipo_de_cambio") {

	$user_id = $login?$login['id']:null;

	if((int) $user_id > 0){

		$fecha_actual = date("Y-m-d");
		$row_count = 0;

		$query = "
		SELECT 
			monto_venta
		FROM 
			tbl_tipo_cambio
		WHERE
			fecha = '$fecha_actual'
			AND moneda_id = 2
		";
		
		$sel_query = $mysqli->query($query);

		if($mysqli->error){
			$result["consulta_error"] = $mysqli->error;
			$result["result_error"] = 0;

		} else {
			$row_count = $sel_query->num_rows;

			if ($row_count > 0) {
				$row = $sel_query->fetch_assoc();
				$valor_cambio = $row['monto_venta'];
			} else {
				$result["result_error"] = 0;

				$result["consulta_error"] = 'No existe tipo de cambio del dia de hoy ' . date("d/m/Y");
			}
		}	
		
		if ($row_count > 0) {
			$result["http_code"] = 200;
			$result["status"] = "Datos obtenidos de gestion.";
			$result["result"] = $valor_cambio;
		} else {
			$result["http_code"] = 400;
			$result["fecha"] = $fecha_actual;

		}
	} else {
        $result["http_code"] = 400;
        $result["consulta_error"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
    }

}

function enviar_error($error){
	$result["http_code"] = 400;
	$result["result"] = 'Ocurrio un error al consultar: ' . $error;
	echo json_encode($result);
	exit();
}
if(isset($_POST["accion"]) && $_POST["accion"]==="validar_provision"){
	$user_id = $login?$login['id']:null;
	$ids = '';
	$contador_array_ids	=	0;
	if((int) $user_id > 0){
		$provision_ids = $_POST["provision_ids"];

		$data = json_decode($provision_ids);
		// if (is_numeric($data)) {
		// 	$query = "
		// 			SELECT
		// 				id
		// 			FROM
		// 				cont_provision
		// 			WHERE
		// 				id = '$data'
		// 				AND  (etapa_id = 2 OR etapa_id = 3)
		// 		";
		// 	$list_query = $mysqli->query($query);
		// 	if($list_query->num_rows > 0){
		// 		$ids	=	$data;
		// 		$contador_array_ids++;

		// 	}


		// } elseif (is_array($data)) {
		// 	$contador_array_ids	=0;
		// 	foreach ($data as $value) {
		// 		$query = "
		// 			SELECT
		// 				id
		// 			FROM
		// 				cont_provision
		// 			WHERE
		// 				id = '$value'
		// 				AND  (etapa_id = 2 OR etapa_id = 3)
		// 		";
		// 		$list_query = $mysqli->query($query);

		// 		if ($list_query->num_rows > 0) {
		// 			// $row = $list_query->fetch_assoc();
		// 			// $value = $row['id'];
		// 			$contador_array_ids++;

		// 			$ids .= $value ;
		// 			$ids .= ',';

		// 		}

		// 		// Ejecutar la consulta y procesar los resultados aquí
		// 	}
		// } 
		// if ($mysqli->error) {
		// 	$result["consulta_error"] = $mysqli->error;
		// 	$result["http_code"] = 400;

		// }else{


		// 	$message = "Estas provisiónes ya están seleccionada como programada o aprobada";
		// 	$result["http_code"] = 200;
		// 	$result["status"] = "Datos obtenidos de gestion.";
		// 	$result["message"] = $message;
		// 	$result['list_no_cumplen']	=	$contador_array_ids;

		// }
			$message = "Estas provisiónes ya están seleccionada como programada o aprobada";
			$result["http_code"] = 200;
			$result["status"] = "Datos obtenidos de gestion.";
			$result["message"] = $message;
			$result['list_no_cumplen']	=	$contador_array_ids;

	} else {
        $result["http_code"] = 400;
        $result["consulta_error"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
    }
}

if(isset($_POST["accion"]) && $_POST["accion"]==="obtener_provisiones_ids"){
	$user_id = $login?$login['id']:null;
	$ids = '';
	$contador_array_ids	=	0;
	if((int) $user_id > 0){
		$programacion_id	=	$_POST['programacion_id'];
		$query_detalle_programacion = "
		SELECT 
			cd.provision_id ,cd.programacion_id ,cp2.id ,cp2.importe as programado ,cp2.periodo_fin 
		FROM 
			cont_programacion_detalle cd
			left join cont_programacion cp 
			on cp.id =cd.programacion_id 
			left join cont_provision cp2 
			on cp2 .id = cd.provision_id 
		WHERE 
			cd.status = 1
			AND cd.programacion_id =" . $programacion_id . "
		";

		$list_query = $mysqli->query($query_detalle_programacion);

		if($mysqli->error){
			enviar_error($mysqli->error . $query_detalle_programacion);
		}

		$row_count = $list_query->num_rows;

		$provision_ids = '';
		$contador_ids = 0;
		if ($row_count > 0) {
			while ($row = $list_query->fetch_assoc()) {
				if ($contador_ids > 0) {
					$provision_ids .= ',';
				}
				$provision_ids .= $row["provision_id"];			
				$contador_ids++;
			}
		}

		if ($row_count > 0) {
			$result["http_code"] = 200;
			$result["status"] = "Datos obtenidos de gestion.";
			$result["result"] = $provision_ids;
		} else {
			$result["http_code"] = 400;
		}
	}else{
		$result["http_code"] = 400;
        $result["consulta_error"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
	}


}
echo json_encode($result);