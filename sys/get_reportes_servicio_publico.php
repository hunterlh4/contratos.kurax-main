<?php 

include("db_connect.php");
include("sys_login.php");


function NombreMes($fecha){
	if (Empty($fecha)) {
		return '';
	}
	$anio = date("Y", strtotime($fecha));
	$mes = date("m", strtotime($fecha));
	$nombre_mes = "";
	switch ($mes) {
		case '01': $nombre_mes = "Enero"; break;
		case '02': $nombre_mes = "Febrero"; break;
		case '03': $nombre_mes = "Marzo"; break;
		case '04': $nombre_mes = "Abril"; break;
		case '05': $nombre_mes = "Mayo"; break;
		case '06': $nombre_mes = "Junio"; break;
		case '07': $nombre_mes = "Julio"; break;
		case '08': $nombre_mes = "Agosto"; break;
		case '09': $nombre_mes = "Septiembre"; break;
		case '10': $nombre_mes = "Octubre"; break;
		case '11': $nombre_mes = "Noviembre"; break;
		case '12': $nombre_mes = "Diciembre"; break;
	}
	return $nombre_mes." del ".$anio;
}


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_meses"){
	$fecha_actual = date('Y-m')."-01";
	$html = '';
	for ($i=0; $i < 24 ; $i++) { 
		$mes = date("Y-m-d",strtotime($fecha_actual."- ".$i." month"));
		$nombre_mes  = NombreMes($mes);
		$html .= '<option '.($i == 0 ? 'selected':'' ).' value="'.$mes.'">'.$nombre_mes.'</option>';

	}
	$result['status'] = 200;
	$result['result'] = $html;

	echo json_encode($result);
	exit();

}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_jefes_comerciales") {
	$id_local = $_POST["local_id"];
	$where_local = "";
	if($id_local != 0){
		$where_local = " tuls.local_id = " . $id_local . " AND ";
	}

	$query = "SELECT
				tus.id,
				concat( IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS jefe_comercial
				FROM tbl_usuarios_locales tuls
				INNER JOIN tbl_usuarios tus
				ON tuls.usuario_id = tus.id AND tus.grupo_id = 10
				INNER JOIN tbl_personal_apt tp
				ON tp.id = tus.personal_id AND 
				tp.area_id = 21 -- AREA 15 = COMERCIAL 
				AND tp.cargo_id = 16 -- CARGO 16 = JEFE
				where " . $where_local . " tuls.estado = 1 AND tus.estado = 1 AND tp.estado = 1 group by tus.id";

	$list_query = $mysqli->query($query);
	$list_proc_jefes_comerciales = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_proc_jefes_comerciales[] = $li;
	}
	$result=array();
	if ($mysqli->error) {
		$result["error"] = $mysqli->error;
	}

	if (count($list_proc_jefes_comerciales) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay jefes comerciales.";
	} elseif (count($list_proc_jefes_comerciales) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "OK";
		$result["result"] = $list_proc_jefes_comerciales;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los jefes comerciales.";
	}
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_supervisores") {
	$id_local = $_POST["local_id"];
	$where_local = "";
	if($id_local != 0){
		$where_local = " tuls.local_id = " . $id_local . " AND ";
	}
	$query = "SELECT tus.id,
				concat( IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS supervisor
				FROM tbl_usuarios_locales tuls
				INNER JOIN tbl_usuarios tus
				ON tuls.usuario_id = tus.id -- AND tus.grupo_id = 12
				INNER JOIN tbl_personal_apt tp
				ON tp.id = tus.personal_id AND
				tp.area_id = 21 -- AREA 21 = OPERACIONES 
				AND tp.cargo_id = 4 -- CARGO 4 = SUPERVISOR
				where " . $where_local . " tuls.estado = 1 group by tus.id";

	$list_query = $mysqli->query($query);
	$list_proc_supervisores = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_proc_supervisores[] = $li;
	}
	$result=array();
	if ($mysqli->error) {
		$result["error"] = $mysqli->error;
	}

	if (count($list_proc_supervisores) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay supervisores.";
	} elseif (count($list_proc_supervisores) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "OK";
		$result["result"] = $list_proc_supervisores;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los supervisores.";
	}
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_reporte_servicio_publico") {
	$buscar_por = $_POST["buscar_por"];
	$id_local = $_POST["local_id"];
	$id_empresa = $_POST["id_empresa"];
	$id_jefe_comercial = $_POST["id_jefe_comercial"];
	$id_supervisor = $_POST["id_supervisor"];
	$periodo = $_POST["periodo"];
	$fec_vcto_desde =  date("Y-m-d", strtotime($_POST["fec_vcto_desde"]));
	$fec_vcto_hasta = date("Y-m-d", strtotime($_POST["fec_vcto_hasta"]));
	$tipo_servicio = $_POST["tipo_servicio"];
	$estado = $_POST["estado"];

	$where_periodo = "";
	$where_local = "";
	$where_empresa = "";
	$where_jefe_comercial = "";
	$where_supervisor = "";
	$having_fec_vcto = "";
	$having_pendientes = "";
	$where_estado = "";
	$where_tipo_servicio = "";

	if($id_local != 0){
		$where_local = " AND tl.id = " . $id_local;
	}
	if($id_empresa != 0){
		$where_empresa = " AND tl.razon_social_id = " . $id_empresa;
	}
	if($id_jefe_comercial != 0){
		$where_jefe_comercial = " AND tus.id = " . $id_jefe_comercial;
	}
	if($id_supervisor != 0){
		$where_supervisor = " AND tuss.id = " . $id_supervisor;
	}
	if($tipo_servicio != 0){
		$where_tipo_servicio = " AND sp.id_tipo_servicio_publico = " . $tipo_servicio;
	}
	if($periodo != 0 && $buscar_por == 1){
		$anio = substr($periodo, 0 , 4);
		$mes = substr($periodo, 5, 2);	
		$where_periodo = " AND month(sp.periodo_consumo) = " . $mes . " and year(sp.periodo_consumo) = " . $anio;
	}
	
	if($fec_vcto_desde != "" && $fec_vcto_hasta != "" && $buscar_por == 2){
		$having_fec_vcto = " AND (sp.fecha_vencimiento between '" . $fec_vcto_desde . "' and '" . $fec_vcto_hasta . "')";
	}

	if($fec_vcto_desde != "" && $fec_vcto_hasta != "" && $buscar_por == 3){
		$having_fec_vcto = " AND (sp.fecha_pago between '" . $fec_vcto_desde . "' and '" . $fec_vcto_hasta . "')";
	}

	if($estado != 0){
		$where_estado = " AND sp.estado = " . $estado;
	}



	$query = "SELECT sp.id, c.contrato_id, sp.id_tipo_servicio_publico, sp.periodo_consumo, sp.fecha_emision, DATE_FORMAT(sp.fecha_vencimiento, '%d/%m/%Y') as fecha_vencimiento, DATE_FORMAT(sp.fecha_pago, '%d/%m/%Y') as fecha_pago, sp.total_pagar, sp.monto_total, sp.estado,
	esp.nombre as nombre_estado,
	tl.id AS local_id,IFNULL(tl.cc_id,'0') AS centro_costo, tl.nombre AS local_nombre,
	i.tipo_compromiso_pago_agua,
	i.tipo_compromiso_pago_luz,
	
	concat( IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS jefe_comercial,
	concat( IFNULL(tpp.nombre, ''),' ', IFNULL(tpp.apellido_paterno, ''),' ', IFNULL(tpp.apellido_materno, '')) AS supervisor,
	tsp.nombre as tipo_servicio, r.nombre AS empresa,
	case when sp.id_tipo_servicio_publico = 1
	then CONCAT(IFNULL(lspe2.razon_social,''),' - ',IFNULL(lspe2.ruc,'')) else CONCAT(IFNULL(lspe1.razon_social,''),' - ',IFNULL(lspe1.ruc,'')) end as empresa_servicio,
	case when sp.id_tipo_servicio_publico = 1 
	then IFNULL(i.num_suministro_luz,'') else IFNULL(i.num_suministro_agua,'') end as numero_suministro
	
	FROM cont_local_servicio_publico AS sp
	INNER JOIN cont_tipo_servicio_publico AS tsp ON sp.id_tipo_servicio_publico = tsp.id
	INNER JOIN tbl_locales  AS tl  ON sp.id_local = tl.id
	INNER JOIN cont_contrato AS c ON c.contrato_id = tl.contrato_id
	INNER JOIN cont_inmueble AS  i ON i.contrato_id = c.contrato_id
	LEFT JOIN tbl_razon_social AS r ON r.id = tl.razon_social_id 

	LEFT JOIN cont_local_servicio_publico_empresas lspe1 ON i.id_empresa_servicio_agua = lspe1.id
	LEFT JOIN cont_local_servicio_publico_empresas lspe2 ON i.id_empresa_servicio_luz = lspe2.id

	LEFT JOIN tbl_usuarios_locales tuls ON tuls.local_id = tl.id  AND tuls.estado = 1
	LEFT JOIN tbl_usuarios tus ON tuls.usuario_id = tus.id AND tus.estado = 1
	INNER JOIN tbl_personal_apt tp ON tp.id = tus.personal_id AND tp.area_id = 21 
	AND tp.cargo_id = 16 AND tp.estado = 1

	LEFT JOIN tbl_usuarios_locales tulss ON tulss.local_id = tl.id  AND tulss.estado = 1
	LEFT JOIN tbl_usuarios tuss ON tulss.usuario_id = tuss.id AND tuss.estado = 1
	INNER JOIN tbl_personal_apt tpp ON tpp.id = tuss.personal_id AND tpp.area_id = 21 
	AND tpp.cargo_id = 4 AND tpp.estado = 1
	LEFT JOIN cont_tipo_estado_servicio_publico AS esp ON sp.estado = esp.id
	WHERE sp.status = 1
	".$where_local."
	".$where_empresa."
	".$where_jefe_comercial."
	".$where_supervisor."
	".$where_tipo_servicio."
	".$where_periodo."
	".$where_estado."
	".$having_fec_vcto."
	GROUP BY sp.id
	";


	$list_query = $mysqli->query($query);
	$list_proc_registros = array();
	while ($sp = $list_query->fetch_assoc()) {
		array_push($list_proc_registros,array(
			'id_tipo_servicio_publico' => $sp['id_tipo_servicio_publico'],
			'tipo_servicio' => $sp['tipo_servicio'],
			'jefe_comercial' => $sp['jefe_comercial'],
			'centro_costo' => $sp['centro_costo'],
			'local_id' => $sp['local_id'],
			'local_nombre' => $sp['local_nombre'],
			'supervisor' => $sp['supervisor'],
			'numero_suministro' => $sp['numero_suministro'],
			'empresa_servicio' => $sp['empresa_servicio'],
			'empresa' => $sp['empresa'],
			'periodo_consumo' => $sp['periodo_consumo'],
			'fecha_vencimiento' => $sp['fecha_vencimiento'],
			'fecha_pago' => $sp['fecha_pago'],
			'tipo_compromiso_pago_agua' => $sp['tipo_compromiso_pago_agua'],
			'tipo_compromiso_pago_luz' => $sp['tipo_compromiso_pago_luz'],
			'estado' => $sp['estado'],
			'nombre_estado' => $sp['nombre_estado'],
			
			'id_recibo' => $sp['id'],
			'monto_total' => $sp['total_pagar'],
		));

	}


	$table = '<table id="sec_rep_servicio_publico" class="table table-striped table-hover table-condensed table-bordered dt-responsive display" cellspacing="0" width="100%">
	<thead>
		<tr role="row">
			<th style="text-align: center;">C.C.</th>
			<th style="text-align: center;">Local</th>
			<th style="text-align: center;">Razón Social</th>
			<th style="text-align: center;">Periodo</th>
			<th style="text-align: center;">Empresa Servicio</th>
			<th style="text-align: center;">Tipo Servicio</th>
			<th style="text-align: center;">N° suministro</th>
			<th style="text-align: center;">Fec. Vcto.</th>
			<th style="text-align: center;">Fec. Pago</th>
			<th style="text-align: center;">Monto</th>
			<th style="text-align: center;">Estado</th>
			<th style="text-align: center;">Recibo</th>
			<th style="text-align: center;">Pago</th>
			';
	$table .= '</tr>
		</thead>
		<tbody>';
		
		for ($i=0; $i < count($list_proc_registros) ; $i++) { 
			$modal_recibo = "ModalVerRecibo('Recibo', ".$list_proc_registros[$i]['id_recibo'].")";
			$modal_pago = "ModalVerRecibo('Pago', ".$list_proc_registros[$i]['id_recibo'].")";

			$list_proc_registros[$i]['monto_total'] = '<span>'.$list_proc_registros[$i]['monto_total'].'</span>';

			$table .= '
			<tr>
				<td>'.$list_proc_registros[$i]['centro_costo'].'</td>
				<td>'.$list_proc_registros[$i]['local_nombre'].'</td>
				<td>'.$list_proc_registros[$i]['empresa'].'</td>
				<td>'.NombreMes($list_proc_registros[$i]['periodo_consumo']).'</td>
				<td>'.$list_proc_registros[$i]['empresa_servicio'].'</td>
				<td>'.$list_proc_registros[$i]['tipo_servicio'].'</td>
				<td>'.$list_proc_registros[$i]['numero_suministro'].'</td>
				<td class="text-center">'.$list_proc_registros[$i]['fecha_vencimiento'].'</td>
				<td class="text-center">'.$list_proc_registros[$i]['fecha_pago'].'</td>
				<td class="text-right">'.$list_proc_registros[$i]['monto_total'].'</td>
				<td class="text-center">'.$list_proc_registros[$i]['nombre_estado'].'</td>
				<td class="text-center">
					<button title="Ver Recibo" onclick="'.$modal_recibo.'" class="btn btn-info" type="button">
					<span class="glyphicon glyphicon-eye-open"></span>
					</button>
				</td>
				<td class="text-center">
					<button title="Ver Constancia de Pago" '.($list_proc_registros[$i]['estado'] != 3 ? 'disabled':'').' onclick="'.$modal_pago.'" class="btn btn-primary" type="button">
					<span class="glyphicon glyphicon-eye-open"></span>
					</button>
				</td>
				
			</tr>';
		}
	
		
	$table .= '
		</tbody>
	</table>';


	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["error"] = $mysqli->error." | ".$query;
		echo json_encode($result);
		exit();
	}
	
	$result["http_code"] = 200;
	$result["table"] = $table;
	$result["status"] = "OK";


	echo json_encode($result);
	exit();

}


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_recibo_servicio_publico") {

	$id_recibo = $_POST['id_recibo'];
	$tipo = $_POST['tipo'];

	$query = "SELECT sp.id, sp.id_tipo_servicio_publico,
	sp.nombre_file, sp.ruta_download_file, sp.extension, 
	sp.nombre_file_voucher, sp.ruta_download_file_voucher, sp.extension_voucher, 
	tl.id AS local_id,IFNULL(tl.cc_id,'0') AS centro_costo, tl.nombre AS local_nombre,
	sp.estado,
	case when sp.id_tipo_servicio_publico = 1 
	then IFNULL(i.num_suministro_luz,'') else IFNULL(i.num_suministro_agua,'') end as numero_suministro
	
	FROM cont_local_servicio_publico AS sp
	INNER JOIN tbl_locales  AS tl  ON sp.id_local = tl.id
	INNER JOIN cont_contrato AS c ON c.contrato_id = tl.contrato_id
	INNER JOIN cont_inmueble AS  i ON i.contrato_id = c.contrato_id

	WHERE sp.id = ".$id_recibo;
	$list_query = $mysqli->query($query);
	$data = array();
	while ($sp = $list_query->fetch_assoc()) {
		if ($tipo == "Recibo") {
			$data['centro_costo'] = $sp['centro_costo'];
			$data['id_tipo_servicio_publico'] = $sp['id_tipo_servicio_publico'];
			$data['estado'] = $sp['estado'];
			$data['id'] = $sp['id'];
			$data['local_id'] = $sp['local_id'];
			$data['local_nombre'] = $sp['local_nombre'];
			$data['numero_suministro'] = $sp['numero_suministro'];
			

			$data['extension'] = $sp['extension'];
			$data['nombre_file'] = $sp['nombre_file'];
			$data['ruta_download_file'] = $sp['ruta_download_file'];
		}else if ($tipo == "Pago") {
			$data['centro_costo'] = $sp['centro_costo'];
			$data['id_tipo_servicio_publico'] = $sp['id_tipo_servicio_publico'];
			$data['estado'] = $sp['estado'];
			$data['id'] = $sp['id'];
			$data['local_id'] = $sp['local_id'];
			$data['local_nombre'] = $sp['local_nombre'];
			$data['numero_suministro'] = $sp['numero_suministro'];

			$data['extension'] = $sp['extension_voucher'];
			$data['nombre_file'] = $sp['nombre_file_voucher'];
			$data['ruta_download_file'] = $sp['ruta_download_file_voucher'];
		}
	}
	$result["http_code"] = 200;
	$result["result"] = $data;

	echo json_encode($result);
	exit();
}

























?>
