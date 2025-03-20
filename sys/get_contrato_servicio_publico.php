<?php 
include("db_connect.php");
include("sys_login.php");ini_set('display_errors', 1);

include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';	

include '/var/www/html/sys/globalFunctions/generalInfo/parameterGeneral.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);

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

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_contrato_servicio_publico_obtener_supervisores") 
{

	$zona_id = $_POST["zona_id"];
	
	$where_zona = "";

	if($zona_id != 0)
	{
		$where_zona = "WHERE l.zona_id = '".$zona_id."' ";
	}

	$query = 
	"
		SELECT
			us.id,
			concat( IFNULL(ps.nombre, ''),' ', IFNULL(ps.apellido_paterno, ''),' ', IFNULL(ps.apellido_materno, '')) AS nombre
		FROM tbl_locales l
			INNER JOIN tbl_usuarios_locales ul
			ON l.id = ul.local_id
			INNER JOIN tbl_usuarios AS us
			ON ul.usuario_id = us.id AND ul.estado = 1
			INNER JOIN tbl_personal_apt AS ps
			ON us.personal_id = ps.id AND ps.area_id = 21 AND ps.cargo_id = 4
		".$where_zona."
		GROUP BY us.id
		ORDER BY concat( IFNULL(ps.nombre, ''),' ', IFNULL(ps.apellido_paterno, ''),' ', IFNULL(ps.apellido_materno, '')) ASC
	";
	
	$list_query = $mysqli->query($query);
	$list = array();
	
	while ($li = $list_query->fetch_assoc()) 
	{
		$list[] = $li;
	}

	if($mysqli->error)
	{
		$result["http_code"] = 400;
		$result["status"] = "Error.";
		$result["result"] = $mysqli->error;
	}
	else
	{
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $list;	
	}
	
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_contrato_servicio_publico_obtener_locales") 
{

	$param_zona = $_POST["param_zona"];
	$param_supervisor = $_POST["param_supervisor"];
	
	$where_supervisor = "";
	$where_zona = "";

	if($login && $login["usuario_locales"]){
		$permiso_locales=" AND l.id IN (".implode(",", $login["usuario_locales"]).") ";
	}

	if($param_supervisor != 0)
	{
		$where_supervisor = "WHERE ul.usuario_id = '".$param_supervisor."' ";
	}
	else
	{
		if($param_zona != 0)
		{
			$where_zona = "WHERE l.zona_id = '".$param_zona."' ";	
		}
	}

	$query = 
	"
		SELECT
			l.id,
		    l.nombre
		FROM tbl_locales l
			INNER JOIN tbl_usuarios_locales ul
			ON l.id = ul.local_id AND ul.estado = 1
			INNER JOIN tbl_usuarios AS us
			ON ul.usuario_id = us.id
			INNER JOIN tbl_personal_apt AS ps
			ON us.personal_id = ps.id AND ps.area_id = 21 AND ps.cargo_id = 4
		".$where_supervisor."
		".$where_zona."
		".$permiso_locales."
		GROUP BY l.id
		ORDER BY l.nombre ASC
	";
	
	$list_query = $mysqli->query($query);
	$list = array();
	
	while ($li = $list_query->fetch_assoc()) 
	{
		$list[] = $li;
	}

	if($mysqli->error)
	{
		$result["http_code"] = 400;
		$result["status"] = "Error.";
		$result["result"] = $mysqli->error;
	}
	else
	{
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $list;	
	}
	
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
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_tipos_de_servicio") {

	$locales_command = "SELECT id, nombre FROM cont_tipo_servicio_publico 
	where status = 1";
	$locales_query = $mysqli->query($locales_command);

	$option = '';
	$option .= '<option value="0">TODOS</option>';
	while($ct=$locales_query->fetch_assoc()){
		$option .= '<option value="'.$ct["id"].'">'.$ct["nombre"].'</option>';
	}
	
	echo $option;
	exit();
								
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_registros_locales_periodo") {
	$id_local = $_POST["local_id"];
	$id_empresa = $_POST["id_empresa"];
	$id_jefe_comercial = $_POST["id_jefe_comercial"];
	$id_supervisor = $_POST["id_supervisor"];
	$periodo = $_POST["periodo"];
	$fec_vcto_desde = date("Y-m-d", strtotime($_POST["fec_vcto_desde"]));
	$fec_vcto_hasta = date("Y-m-d", strtotime($_POST["fec_vcto_hasta"]));
	$pendientes = $_POST["btn_pendientes"];
	$tipo_servicio = $_POST["tipo_servicio"];
	$permiso_monto = $_POST['permiso_monto'];

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
		$where_local = " AND lc.id = " . $id_local;
	}
	if($id_empresa != 0){
		$where_empresa = " AND lc.razon_social_id = " . $id_empresa;
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
	if($periodo != 0){
		$anio = substr($periodo, 0 , 4);
		$mes = substr($periodo, 5, 2);	
		$where_periodo = " AND month(sp.periodo_consumo) = " . $mes . " and year(sp.periodo_consumo) = " . $anio;
	}
	if (!Empty($estado) && $estado != 0) {
		if ($estado != 9) {
			$where_estado = " AND sp.estado = ".$estado;
		}
	}

	///inciio consulta nueva
	$query = "
		SELECT 
			lc.id AS local_id,
			IFNULL(lc.cc_id,'0') AS centro_costo, 
			lc.nombre AS local_nombre,
			i.tipo_compromiso_pago_agua,
			i.tipo_compromiso_pago_luz,
			s.tipo_compromiso_pago_id,
			concat( IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS jefe_comercial,
			concat( IFNULL(tpp.nombre, ''),' ', IFNULL(tpp.apellido_paterno, ''),' ', IFNULL(tpp.apellido_materno, '')) AS supervisor,
			r.nombre AS empresa
		FROM tbl_locales AS lc
			INNER JOIN cont_contrato AS c ON c.contrato_id = lc.contrato_id
			INNER JOIN cont_contrato_detalle AS cd
	        ON cd.contrato_id = c.contrato_id
			INNER JOIN cont_inmueble AS  i ON i.contrato_id = c.contrato_id
			INNER JOIN cont_inmueble_suministros s
	        ON s.inmueble_id = i.id
			INNER JOIN tbl_razon_social AS  r ON r.id = lc.razon_social_id
			LEFT JOIN tbl_usuarios_locales tuls ON tuls.local_id = lc.id AND tuls.estado = 1
			LEFT JOIN tbl_usuarios tus ON tuls.usuario_id = tus.id AND tus.estado = 1
			INNER JOIN tbl_personal_apt tp 
			ON tp.id = tus.personal_id AND tp.area_id = 21 AND tp.cargo_id = 16 AND tp.estado = 1
			LEFT JOIN tbl_usuarios_locales tulss ON tulss.local_id = lc.id AND tulss.estado = 1
			LEFT JOIN tbl_usuarios tuss ON tulss.usuario_id = tuss.id AND tuss.estado = 1
			INNER JOIN tbl_personal_apt tpp 
			ON tpp.id = tuss.personal_id AND tpp.area_id = 21 AND tpp.cargo_id = 4 AND tpp.estado = 1
		WHERE lc.red_id = 1 AND lc.estado = 1
		-- AND ((i.tipo_compromiso_pago_agua <> 8 AND i.tipo_compromiso_pago_agua <> 0 ) OR 
		-- (i.tipo_compromiso_pago_luz <> 8 AND i.tipo_compromiso_pago_luz <> 0 ))
			AND (s.tipo_compromiso_pago_id <> 8 AND s.tipo_compromiso_pago_id <> 0)
			".$where_local."
			".$where_empresa."
			".$where_jefe_comercial."
			".$where_supervisor."
		GROUP BY lc.id ORDER BY local_nombre ASC
		";

	
	$result=array();

	$list_query = $mysqli->query($query);

	$list_proc_registros = array();
	while ($li = $list_query->fetch_assoc()) {

		$query_sp = 
		"
			SELECT 
				sp.id, sp.id_tipo_servicio_publico, sp.periodo_consumo, 
				DATE_FORMAT(sp.fecha_emision, '%d/%m/%Y') as fecha_emision, 
				DATE_FORMAT(sp.fecha_vencimiento, '%d/%m/%Y') as fecha_vencimiento, 
				sp.monto_total, sp.estado, esp.nombre as nombre_estado 
		FROM cont_local_servicio_publico AS sp
			LEFT JOIN cont_tipo_estado_servicio_publico AS esp 
			ON sp.estado = esp.id
		WHERE sp.status = 1 AND sp.id_local = ".$li['local_id']."
			".$where_tipo_servicio."
			".$where_periodo."
		ORDER BY sp.id DESC
		";

		//echo $query_sp;

		$list_query_sp = $mysqli->query($query_sp);
		$recibo = array('id_tipo_servicio_publico' => '' , 'periodo_consumo' => '', 'id_estado_luz' => '','id_estado_agua' => '', 'fec_vcto_recibo_agua' => '', 'fec_vcto_recibo_luz' => '', 'estado_recibo_agua' => '','estado_recibo_luz' => '','id_recibo_agua' => '','id_recibo_luz' => '','recibo_agua' => '','recibo_luz' => '');

		while ($sp = $list_query_sp->fetch_assoc())
		{
			if ($sp['id_tipo_servicio_publico'] == 2)
			{
				// SERVICIO DE AGUA
				$recibo['id_estado_agua'] =  $sp['estado'];
				$recibo['estado_recibo_agua'] =  $sp['nombre_estado'];
				$recibo['id_recibo_agua'] = $sp['id'];
				$recibo['recibo_agua'] = $sp['monto_total'];
				$recibo['fec_vcto_recibo_agua'] = $sp['fecha_vencimiento'];
			}
			
			if ($sp['id_tipo_servicio_publico'] == 1)
			{
				// SERVICIO DE LUZ
				$recibo['id_estado_luz'] =  $sp['estado'];
				$recibo['estado_recibo_luz'] =  $sp['nombre_estado'];
				$recibo['id_recibo_luz'] = $sp['id'];
				$recibo['recibo_luz'] = $sp['monto_total'];
				$recibo['fec_vcto_recibo_luz'] = $sp['fecha_vencimiento'];
			}
			$recibo['periodo_consumo'] = $sp['periodo_consumo'];
			$recibo['id_tipo_servicio_publico'] = $sp['id_tipo_servicio_publico']; 
		}

		$agregar = false;
		
		if($estado == 0)
		{ 
			//SE AGREGA TODOS
			$agregar = true;
		}
		else if($estado == 9)
		{ 
			// SIN RECIBO
			if($tipo_servicio == 0)
			{
				$agregar = $recibo['id_estado_agua'] == "" || $recibo['id_estado_luz'] == "" ? true :false;
			}else if($tipo_servicio == 1){
				$agregar = $recibo['id_estado_luz'] == "" ? true :false;
			}else if($tipo_servicio == 2){
				$agregar = $recibo['id_estado_agua'] == "" ? true :false;
			}								
		}
		else 
		{
			if($recibo['id_estado_agua'] == $estado || $recibo['id_estado_luz'] == $estado)
			{
				$agregar = true;
			}
		}

		if ($agregar) {
			array_push($list_proc_registros,array(
				'jefe_comercial' => $li['jefe_comercial'],
				'local_nombre' => $li['local_nombre'],
				'centro_costo' => $li['centro_costo'],
				'local_id' => $li['local_id'],
				'local_nombre' => $li['local_nombre'],
				'empresa' => $li['empresa'],
				'supervisor' => $li['supervisor'],
				'periodo_consumo' => NombreMes($recibo['periodo_consumo']),
				'fec_vcto_recibo_luz' => $recibo['fec_vcto_recibo_luz'],
				'fec_vcto_recibo_agua' => $recibo['fec_vcto_recibo_agua'],
				'tipo_compromiso_pago_agua' => $li['tipo_compromiso_pago_agua'],
				'tipo_compromiso_pago_luz' => $li['tipo_compromiso_pago_luz'],
				'estado_recibo_agua' => !Empty($recibo['estado_recibo_agua']) ? $recibo['estado_recibo_agua'] : 'SIN RECIBO',
				'estado_recibo_luz' => !Empty($recibo['estado_recibo_luz']) ? $recibo['estado_recibo_luz'] : 'SIN RECIBO',
				'id_recibo_agua' => $recibo['id_recibo_agua'],
				'id_recibo_luz' => $recibo['id_recibo_luz'],
				'recibo_agua' => $recibo['recibo_agua'],
				'recibo_luz' => $recibo['recibo_luz'],
				'id_estado_agua' => $recibo['id_estado_agua'],
				'id_estado_luz' => $recibo['id_estado_luz'],
			));
		}
	}

	$table = '<table id="sec_con_tabla_registros" class="table table-striped table-hover table-condensed table-bordered dt-responsiveS display" cellspacing="0" width="100%">
		<thead>
			<tr role="row">
				<th style="text-align: center;">C.C.</th>
				<th style="text-align: center;">Local</th>
				<th style="text-align: center;">Empresa Arrendaria</th>
				<th style="text-align: center;">Jefe Comercial</th>
				<th style="text-align: center;">Supervisor</th>
				<th style="text-align: center;">Periodo</th>';

	if ($tipo_servicio == 0 || $tipo_servicio == 1) 
	{
		$table .= '<th style="text-align: center;">Servicio de Luz</th>
				<th style="text-align: center;">Fec. Vcto. Luz</th>
				<th style="text-align: center;">Estado Luz</th>';
	}
	if ($tipo_servicio == 0 || $tipo_servicio == 2) 
	{
	$table .= '	<th style="text-align: center;">Servicio de Agua</th>
				<th style="text-align: center;">Fec. Vcto. Agua</th>
				<th style="text-align: center;">Estado Agua</th>';
	}
	$table .= '</tr>
		</thead>
		<tbody>';
		
		for ($i=0; $i < count($list_proc_registros) ; $i++)
		{ 
			$onclick_luz = "agregarMonto(1,".$list_proc_registros[$i]['local_id'].",'".$list_proc_registros[$i]['periodo_consumo'].
			"','".$list_proc_registros[$i]['centro_costo']." ".$list_proc_registros[$i]['local_nombre']."', ".$list_proc_registros[$i]['id_recibo_luz'].")";

			$button_agregar_monto_luz = '<button type="button" class="btn btn-sm btn-success" onclick="'.$onclick_luz.'")">Agregar Monto</button>';
            
			if($list_proc_registros[$i]['recibo_luz'] == 0 && $list_proc_registros[$i]['id_recibo_luz'] > 0)
			{
				if($permiso_monto == 'true')
				{
					$list_proc_registros[$i]['recibo_luz'] = $button_agregar_monto_luz;	
				}
				else
				{
					$list_proc_registros[$i]['recibo_luz'] = "0.00";
				}
			}
			else if ($list_proc_registros[$i]['id_recibo_luz'] == 0)
			{
				$list_proc_registros[$i]['recibo_luz'] = "SIN RECIBO";
			}
			else
			{
				$onClick_verDetalle = "verDetalle(".$list_proc_registros[$i]['id_recibo_luz'].")";
				
				$list_proc_registros[$i]['recibo_luz'] = '<span>'.$list_proc_registros[$i]['recibo_luz'].'</span>
				<button type="button" class="btn btn-sm btn-info" style="margin-left: 10px" onclick="'.$onclick_luz.'"><i class="fa fa-eye"></i></button>';
			}


			$onclick_agua = "agregarMonto(2,".$list_proc_registros[$i]['local_id'].",'".$list_proc_registros[$i]['periodo_consumo'] ."','" .$list_proc_registros[$i]['centro_costo']." ".$list_proc_registros[$i]['local_nombre']."', ".$list_proc_registros[$i]['id_recibo_agua'].")";
			$button_agregar_monto_agua = '<button type="button" class="btn btn-sm btn-success" onclick="'.$onclick_agua.'">Agregar Monto</button>';

			if($list_proc_registros[$i]['recibo_agua'] == 0 && $list_proc_registros[$i]['id_recibo_agua'] > 0)
			{
				if($permiso_monto == 'true')
				{
					$list_proc_registros[$i]['recibo_agua'] = $button_agregar_monto_agua;
				}
				else
				{
					$list_proc_registros[$i]['recibo_agua'] = "0.00";
				}
			}
			else if ($list_proc_registros[$i]['id_recibo_agua'] == 0)
			{
				$list_proc_registros[$i]['recibo_agua'] = "SIN RECIBO";
			}
			else
			{
				$onClick_verDetalle = "verDetalle(".$list_proc_registros[$i]['id_recibo_agua'].")";
				$list_proc_registros[$i]['recibo_agua'] = '<span>'.$list_proc_registros[$i]['recibo_agua'].
				'</span><button type="button" class="btn btn-sm btn-info" style="margin-left: 10px" onclick="'.$onclick_agua.'"><i class="fa fa-eye"></i></button>';
			}
			
			$table .= '
			<tr>
				<td>'.$list_proc_registros[$i]['centro_costo'].'</td>
				<td>'.$list_proc_registros[$i]['local_nombre'].'</td>
				<td>'.$list_proc_registros[$i]['empresa'].'</td>
				<td>'.$list_proc_registros[$i]['jefe_comercial'].'</td>
				<td>'.$list_proc_registros[$i]['supervisor'].'</td>
				<td>'.$list_proc_registros[$i]['periodo_consumo'].'</td>';
			if ($tipo_servicio == 0 || $tipo_servicio == 1) {
				$table .= '
				<td class="text-right">'.$list_proc_registros[$i]['recibo_luz'].'</td>
				<td class="text-center">'.$list_proc_registros[$i]['fec_vcto_recibo_luz'].'</td>
				<td class="text-center">'.$list_proc_registros[$i]['estado_recibo_luz'].'</td>';
			}
			if ($tipo_servicio == 0 || $tipo_servicio == 2) {
				$table .= '
				<td class="text-right">'.$list_proc_registros[$i]['recibo_agua'].'</td>
				<td class="text-center">'.$list_proc_registros[$i]['fec_vcto_recibo_agua'].'</td>
				<td class="text-center"> '.$list_proc_registros[$i]['estado_recibo_agua'].'</td>';
			}
			$table .= '
			</tr>';
		}
	
		
	$table .= '
		</tbody>
	</table>';

	
	if ($mysqli->error) {
		$result["error"] = $mysqli->error." | ".$query;
	}
	if (count($list_proc_registros) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
	} elseif (count($list_proc_registros) > 0) {
		$result["http_code"] = 200;
		$result["table"] = $table;
		
		$result["status"] = "OK";
		$result["result"] = $list_proc_registros;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los registros.";
	}
	echo json_encode($result);
	exit();

}

if (isset($_POST["accion"]) && $_POST["accion"]==="contrato_servicio_publico_buscar_servicio_publico")
{

	$param_buscar_por = $_POST['param_buscar_por'];
	$param_tipo_servicio = $_POST['param_tipo_servicio'];
	$param_empresa_arrendataria = $_POST['param_empresa_arrendataria'];
	$param_zona = $_POST['param_zona'];
	$param_supervisor = $_POST['param_supervisor'];
	$param_local = $_POST['param_local'];
	$param_periodo = $_POST['param_periodo'];
	$param_fecha_incio = date("Y-m-d", strtotime($_POST["param_fecha_incio"]));
	$param_fecha_fin = date("Y-m-d", strtotime($_POST["param_fecha_fin"]));
	$param_estado = $_POST['param_estado'];

	if($login && $login["usuario_locales"]){
		$permiso_locales=" AND l.id IN (".implode(",", $login["usuario_locales"]).") ";
	}
	$query = "";
	
	$where_tipo_servicio = "";
	$where_empresa_arrendataria = "";
	$where_zona = "";
	$where_supervisor = "";
	$where_local = "";
	$where_periodo = "";
	$where_fechas = "";
	$where_estado = "";

	if($param_tipo_servicio != 0)
	{
		$where_tipo_servicio = " AND sp.id_tipo_servicio_publico = '".$param_tipo_servicio."' ";
	}

	if($param_empresa_arrendataria != 0)
	{
		$where_empresa_arrendataria = " AND l.razon_social_id = '".$param_empresa_arrendataria."' ";
	}

	if($param_zona != 0)
	{
		$where_zona = " AND l.zona_id = '".$param_zona."' ";
	}

	if($param_supervisor != 0)
	{
		$where_supervisor = " AND uc.id = '".$param_supervisor."' ";
	}

	if($param_local != 0)
	{
		$where_local = " AND l.id = '".$param_local."' ";
	}else{
		$where_local = $permiso_locales;
	}

	if($param_buscar_por == 1)
	{
		// BUSCAR POR PERIODO
		if($param_periodo != 0)
		{
			$anio = substr($param_periodo, 0 , 4);
			$mes = substr($param_periodo, 5, 2);	
			$where_periodo = " AND month(sp.periodo_consumo) = '".$mes."' 
								AND year(sp.periodo_consumo) = '".$anio."' ";
		}
	}
	else if($param_buscar_por == 2)
	{
		// BUSCAR POR FECHAS
		if($param_fecha_incio != "" && $param_fecha_fin != "")
		{
			$where_fechas = " AND (date_format(sp.fecha_vencimiento, '%Y-%m-%d') BETWEEN '".$param_fecha_incio."' AND '".$param_fecha_fin ."') ";
		}
	}

	if($param_estado != 0)
	{
		$where_estado = " AND sp.estado = '".$param_estado."' ";
	}

	$query = "
		SELECT
			sp.id,
		    r.nombre AS empresa_arrendataria,
		    z.nombre AS zona_nombre,
		    concat( IFNULL(pj.nombre, ''),' ', IFNULL(pj.apellido_paterno, ''),' ', IFNULL(pj.apellido_materno, '')) AS jefe_comercial,
		    concat( IFNULL(ps.nombre, ''),' ', IFNULL(ps.apellido_paterno, ''),' ', IFNULL(ps.apellido_materno, '')) AS creador,
		    IFNULL(l.cc_id,'0') AS local_centro_costo,
		    l.id AS local_id,
			l.nombre AS local_nombre,
			CONCAT(l.nombre, ' [', IFNULL(l.cc_id,'No tiene') ,']') AS local_nombre_completo,
		    tsp.nombre AS tipo_servicio_nombre,
			DATE(sp.created_at) AS fecha_envio_supervisor,
			DATE(sp.fecha_validacion_contabilidad)  AS fecha_validacion_contabilidad,
			DATE(sp.fecha_cancelacion_tesoreria) AS fecha_cancelacion_tesoreria,
		    sp.id_tipo_servicio_publico,
		    sp.periodo_consumo,
		    cis.nro_suministro,
		    sp.monto_total AS monto,
		    sp.fecha_emision,
		    sp.fecha_vencimiento,
		    sp.estado,
		    esp.nombre as estado_nombre,
		    sp.total_pagar AS total_pagado
		FROM cont_local_servicio_publico sp
			INNER JOIN cont_inmueble_suministros cis
			ON sp.inmueble_suministros_id = cis.id
			INNER JOIN cont_inmueble i
			ON cis.inmueble_id = i.id
			INNER JOIN cont_contrato_detalle cd
			ON i.contrato_id = cd.contrato_id AND i.contrato_detalle_id = cd.id
			INNER JOIN cont_contrato c
			ON cd.contrato_id = c.contrato_id
			INNER JOIN tbl_locales AS l
			ON l.contrato_id = c.contrato_id
			INNER JOIN tbl_razon_social AS r 
			ON r.id = l.razon_social_id
			INNER JOIN tbl_zonas AS z
			ON l.zona_id = z.id
			LEFT JOIN tbl_personal_apt AS pj
			ON z.jop_id = pj.id 
			INNER JOIN tbl_usuarios uc
			ON sp.user_created_id = uc.id
			INNER JOIN tbl_personal_apt AS ps
			ON uc.personal_id = ps.id
			INNER JOIN cont_tipo_servicio_publico tsp
			ON sp.id_tipo_servicio_publico = tsp.id
			INNER JOIN cont_tipo_estado_servicio_publico AS esp 
			ON sp.estado = esp.id
		WHERE sp.status = 1
			".$where_tipo_servicio."
			".$where_empresa_arrendataria."
			".$where_zona."
			".$where_supervisor."
			".$where_local."
			".$where_periodo."
			".$where_fechas."
			".$where_estado."
	";

	$list_query = $mysqli->query($query);

	$data =  array();
	$num = 1;

	while($reg = $list_query->fetch_object()) 
	{

		$onclick_luz = "agregarMonto('".$reg->id_tipo_servicio_publico."', '".$reg->local_id."', '".$reg->periodo_consumo."', '".$reg->local_centro_costo." ".$reg->local_nombre."', '".$reg->id."')";

		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->jefe_comercial,
			"2" => $reg->creador,
			"3" => $reg->local_nombre_completo,
			"4" => $reg->tipo_servicio_nombre,
			"5" => $reg->fecha_envio_supervisor,
			"6" => $reg->fecha_validacion_contabilidad,
			"7" => $reg->fecha_cancelacion_tesoreria,
			"8" => NombreMes($reg->periodo_consumo),
			"9" => $reg->nro_suministro,
			"10" => $reg->monto,
			"11" => $reg->fecha_vencimiento,
			"12" => $reg->estado_nombre,
			"13" => $reg->total_pagado,
			"14" => '
					<button type="button" class="btn btn-sm btn-info" style="margin-left: 10px" onclick="'.$onclick_luz.'"><i class="fa fa-eye"></i></button>
					'
		);

		$num++;
	}

	$resultado = array(
		"sEcho" => 1,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);

	echo json_encode($resultado);

	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="contrato_servicio_publico_reporte_btn_listar_servicios_publicos")
{
	$param_buscar_por = $_POST['param_buscar_por'];
	$param_tipo_servicio = $_POST['param_tipo_servicio'];
	$param_empresa_arrendataria = $_POST['param_empresa_arrendataria'];
	$param_zona = $_POST['param_zona'];
	$param_supervisor = $_POST['param_supervisor'];
	$param_local = $_POST['param_local'];
	$param_periodo = $_POST['param_periodo'];
	$param_fecha_incio = date("Y-m-d", strtotime($_POST["param_fecha_incio"]));
	$param_fecha_fin = date("Y-m-d", strtotime($_POST["param_fecha_fin"]));
	$param_estado = $_POST['param_estado'];

	if($login && $login["usuario_locales"]){
		$permiso_locales=" AND l.id IN (".implode(",", $login["usuario_locales"]).") ";
	}

	$query = "";
	
	$where_tipo_servicio = "";
	$where_empresa_arrendataria = "";
	$where_zona = "";
	$where_supervisor = "";
	$where_local = "";
	$where_periodo = "";
	$where_fechas = "";
	$where_estado = "";

	if($param_tipo_servicio != 0)
	{
		$where_tipo_servicio = " AND sp.id_tipo_servicio_publico = '".$param_tipo_servicio."' ";
	}

	if($param_empresa_arrendataria != 0)
	{
		$where_empresa_arrendataria = " AND l.razon_social_id = '".$param_empresa_arrendataria."' ";
	}

	if($param_zona != 0)
	{
		$where_zona = " AND l.zona_id = '".$param_zona."' ";
	}

	if($param_supervisor != 0)
	{
		$where_supervisor = " AND us.id = '".$param_supervisor."' ";
	}

	if($param_local != 0)
	{
		$where_local = " AND l.id = '".$param_local."' ";
	}

	if($param_buscar_por == 1)
	{
		// BUSCAR POR PERIODO
		if($param_periodo != 0)
		{
			$anio = substr($param_periodo, 0 , 4);
			$mes = substr($param_periodo, 5, 2);	
			$where_periodo = " AND month(sp.periodo_consumo) = '".$mes."' 
								AND year(sp.periodo_consumo) = '".$anio."' ";
		}
	}
	else if($param_buscar_por == 2)
	{
		// BUSCAR POR FECHAS
		if($param_fecha_incio != "" && $param_fecha_fin != "")
		{
			$where_fechas = " AND (sp.fecha_vencimiento BETWEEN '".$param_fecha_incio."' 
									AND '".$param_fecha_fin ."') ";
		}
	}

	if($param_estado != 0)
	{
		$where_estado = " AND sp.estado = '".$param_estado."' ";
	}

	$query = "
		SELECT
			sp.id,
		    r.nombre AS empresa_arrendataria,
		    z.nombre AS zona_nombre,
		    concat( IFNULL(pj.nombre, ''),' ', IFNULL(pj.apellido_paterno, ''),' ', IFNULL(pj.apellido_materno, '')) AS jefe_comercial,
		    concat( IFNULL(ps.nombre, ''),' ', IFNULL(ps.apellido_paterno, ''),' ', IFNULL(ps.apellido_materno, '')) AS creador,
		    IFNULL(l.cc_id,'0') AS local_centro_costo,
		    l.id AS local_id,
			CONCAT(l.nombre, ' [', IFNULL(l.cc_id,'No tiene') ,']') AS local_nombre,
		    tsp.nombre AS tipo_servicio_nombre,
			DATE(sp.created_at) AS fecha_envio_supervisor,
			DATE(sp.fecha_validacion_contabilidad)  AS fecha_validacion_contabilidad,
			DATE(sp.fecha_cancelacion_tesoreria) AS fecha_cancelacion_tesoreria,
		    sp.periodo_consumo,
		    cis.nro_suministro,
		    sp.monto_total AS monto,
		    sp.fecha_emision,
		    sp.fecha_vencimiento,
		    sp.estado,
		    esp.nombre as estado_nombre,
		    sp.total_pagar AS total_pagado
		FROM cont_local_servicio_publico sp
			INNER JOIN cont_inmueble_suministros cis
			ON sp.inmueble_suministros_id = cis.id
			INNER JOIN cont_inmueble i
			ON cis.inmueble_id = i.id
			INNER JOIN cont_contrato_detalle cd
			ON i.contrato_id = cd.contrato_id AND i.contrato_detalle_id = cd.id
			INNER JOIN cont_contrato c
			ON cd.contrato_id = c.contrato_id
			INNER JOIN tbl_locales AS l
			ON l.contrato_id = c.contrato_id
			INNER JOIN tbl_razon_social AS r 
			ON r.id = l.razon_social_id
			INNER JOIN tbl_zonas AS z
			ON l.zona_id = z.id
			LEFT JOIN tbl_personal_apt AS pj
			ON z.jop_id = pj.id 
			INNER JOIN tbl_usuarios uc
			ON sp.user_created_id = uc.id
			INNER JOIN tbl_personal_apt AS ps
			ON uc.personal_id = ps.id
			INNER JOIN cont_tipo_servicio_publico tsp
			ON sp.id_tipo_servicio_publico = tsp.id
			INNER JOIN cont_tipo_estado_servicio_publico AS esp 
			ON sp.estado = esp.id
		WHERE sp.status = 1
			".$where_tipo_servicio."
			".$where_empresa_arrendataria."
			".$where_zona."
			".$where_supervisor."
			".$where_local."
			".$where_periodo."
			".$where_fechas."
			".$where_estado."
			".$permiso_locales."
	";

	$list_query = $mysqli->query($query);

	//PROCEDEMOS A CREAR LA CARPETA SI EN CASO NO EXISTA
	$path = "/var/www/html/files_bucket/contratos/servicios_publicos/reportes/contabilidad/";

	if (!is_dir($path)) 
	{
		mkdir($path, 0777, true);
	}

	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/contratos/servicios_publicos/reportes/contabilidad/*'); //obtenemos todos los nombres de los ficheros
	foreach($files as $file)
	{
	    if(is_file($file))
	    unlink($file); //elimino el fichero
	}


	require_once '../phpexcel/classes/PHPExcel.php';
	$objPHPExcel = new PHPExcel();

	// Se asignan las propiedades del libro
	$objPHPExcel->getProperties()->setCreator("AT") // Nombre del autor
    ->setDescription("Reporte"); //Descripción

    $tituloReporte = "Relación de Servicios Públicos - Contabilidad";

	$titulosColumnas = array('Nº', 'Razón Social', 'Zona', 'Jefe Comercial', 'Creado Por', 'Local', 'Servicio', 'F. de envío - Supervisor', 'F. de validación - Contabilidad', 'F. de cancelación - Tesorería', 'Periodo', 'Cod. Suministro', 'Monto', 'F. Vencimiento', 'Estado', 'Monto Total');

	// Se combinan las celdas A1 hasta M1, para colocar ahí el titulo del reporte
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:P1');
	
	$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A1', $tituloReporte);

	// Se agregan los titulos del reporte
	$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A2', $titulosColumnas[0])  //Titulo de las columnas
    ->setCellValue('B2', $titulosColumnas[1])
    ->setCellValue('C2', $titulosColumnas[2])
    ->setCellValue('D2', $titulosColumnas[3])
    ->setCellValue('E2', $titulosColumnas[4])
    ->setCellValue('F2', $titulosColumnas[5])
    ->setCellValue('G2', $titulosColumnas[6])
    ->setCellValue('H2', $titulosColumnas[7])
    ->setCellValue('I2', $titulosColumnas[8])
    ->setCellValue('J2', $titulosColumnas[9])
    ->setCellValue('K2', $titulosColumnas[10])
    ->setCellValue('L2', $titulosColumnas[11])
    ->setCellValue('M2', $titulosColumnas[12])
    ->setCellValue('N2', $titulosColumnas[13])
    ->setCellValue('O2', $titulosColumnas[14])
    ->setCellValue('P2', $titulosColumnas[15]);

    //Se agregan los datos a la lista del reporte
	$cont = 0;

	$i = 3; //Numero de fila donde se va a comenzar a rellenar los datos
	while ($fila = $list_query->fetch_array()) 
	{
		$cont ++;

		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('A'.$i, $cont)
		->setCellValue('B'.$i, $fila['empresa_arrendataria'])
		->setCellValue('C'.$i, $fila['zona_nombre'])
		->setCellValue('D'.$i, $fila['jefe_comercial'])
		->setCellValue('E'.$i, $fila['creador'])
		->setCellValue('F'.$i, $fila['local_nombre'])
		->setCellValue('G'.$i, $fila['tipo_servicio_nombre'])
		->setCellValue('H'.$i, $fila['fecha_envio_supervisor'])
		->setCellValue('I'.$i, $fila['fecha_validacion_contabilidad'])
		->setCellValue('J'.$i, $fila['fecha_cancelacion_tesoreria'])
		->setCellValue('K'.$i, $fila['periodo_consumo'])
		->setCellValue('L'.$i, $fila['nro_suministro'])
		->setCellValue('M'.$i, "S/ ".$fila['monto'])
		->setCellValue('N'.$i, $fila['fecha_vencimiento'])
		->setCellValue('O'.$i, $fila['estado_nombre'])
		->setCellValue('P'.$i, "S/ ".$fila['total_pagado']);
		
		$i++;
	}

	$estiloNombresColumnas = array(
		'font' => array(
	        'name'      => 'Calibri',
	        'bold'      => true,
	        'italic'    => false,
	        'strike'    => false,
	        'size' =>10,
	        'color'     => array(
	            'rgb' => '000000'
	        )
	    ),
	    'fill' => array(
			  'type'  => PHPExcel_Style_Fill::FILL_SOLID,
			  'color' => array(
			        'rgb' => 'FFFFFF')
			),
	    'alignment' =>  array(
	        'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        'wrap'      => false
	    ),
	    'borders' => array(
		        'allborders' => array(
		            'style' => PHPExcel_Style_Border::BORDER_THIN
		        )
		    )
	);

	$estiloInformacion = new PHPExcel_Style();
	$estiloInformacion->applyFromArray( array(
	    'font' => array(
	        'name'  => 'Arial',
	        'color' => array(
	            'rgb' => '000000'
	        )
	    )
	));

	$estilo_centrar = array(
	    'alignment' =>  array(
	        'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        'wrap'      => false
	    )
	);

	// SIRVE PARA PONER ALTURA A LA FILA, EN ESTE CASO A LA FILA 1.
	$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(63);

	$objPHPExcel->getActiveSheet()->getStyle('A1:P1')->applyFromArray($estiloNombresColumnas);
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:L".($i-1));
	$objPHPExcel->getActiveSheet()->getStyle('A1:P'.($i-1))->applyFromArray($estilo_centrar);

	$objPHPExcel->getActiveSheet()->getStyle('J3:J'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');

	$objPHPExcel->getActiveSheet()->getStyle('M3:M'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');

	// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
	for($i = 'A'; $i <= 'Z'; $i++)
	{
	    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}

	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('Servicios Públicos');
	  
	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);
	
	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Servicios Públicos Contabilidad.xls" ');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$excel_path = '/var/www/html/files_bucket/contratos/servicios_publicos/reportes/contabilidad/Servicios Públicos Contabilidad.xls';
	$excel_path_download = '/files_bucket/contratos/servicios_publicos/reportes/contabilidad/Servicios Públicos Contabilidad.xls';

	try 
	{
		$objWriter->save($excel_path);
	} 
	catch (Exception $e)
	{
		echo json_encode(["error" => $e]);
		exit;
	}

	echo json_encode(array(
		"ruta_archivo" => $excel_path_download
	));
	exit;
}

if (isset($_POST["accion"]) && $_POST["accion"]==="contrato_servicio_publico_buscar_servicio_publico_pre_concar")
{
	$param_tipo_empresa = $_POST['param_tipo_empresa'];
	$param_fecha_incio = date("Y-m-d", strtotime($_POST["param_fecha_incio"]));
	$param_fecha_fin = date("Y-m-d", strtotime($_POST["param_fecha_fin"]));
	
	if($login && $login["usuario_locales"]){
		$permiso_locales=" AND l.id IN (".implode(",", $login["usuario_locales"]).") ";
	}

	$query = "";
	
	$where_empresa_arrendataria = "";
	$where_fechas = "";
	
	if($param_tipo_empresa != 0)
	{
		$where_empresa_arrendataria = " AND l.razon_social_id = '".$param_tipo_empresa."' ";
	}

	if($param_fecha_incio != "" && $param_fecha_fin != "")
	{
		$where_fechas = " AND (date_format(sp.fecha_atencion_contabilidad, '%Y-%m-%d') BETWEEN '".$param_fecha_incio."' AND '".$param_fecha_fin ."') ";
	}

	$query = 
	"
		SELECT
			sp.id,
		    r.nombre AS empresa_arrendataria,
		    r.subdiario_contabilidad AS empresa_subdiario,
		    IFNULL(l.cc_id,'0') AS local_centro_costo,
		    l.id AS local_id,
			l.nombre AS local_nombre,
		    tsp.nombre AS tipo_servicio_nombre,
		    sp.periodo_consumo,
		    cis.nro_suministro,
		    cis.tipo_servicio_id AS tipo_servicio_publico,
			ea.id,
		    ea.ruc AS empresa_agua_ruc,
			el.ruc AS empresa_luz_ruc,
		    sp.monto_total AS monto,
		    sp.fecha_emision,
		    sp.fecha_vencimiento,
		    sp.created_at AS fecha_creacion,
		    sp.fecha_atencion_contabilidad,
		    sp.estado,
		    esp.nombre as estado_nombre,
		    sp.total_pagar AS total_pagado
		FROM cont_local_servicio_publico sp
			INNER JOIN cont_inmueble_suministros cis
			ON sp.inmueble_suministros_id = cis.id
			INNER JOIN cont_inmueble i
			ON cis.inmueble_id = i.id
			LEFT JOIN cont_local_servicio_publico_empresas ea
			ON i.id_empresa_servicio_agua = ea.id
			LEFT JOIN cont_local_servicio_publico_empresas el
			ON i.id_empresa_servicio_luz = el.id
			INNER JOIN cont_contrato_detalle cd
			ON i.contrato_id = cd.contrato_id AND i.contrato_detalle_id = cd.id
			INNER JOIN cont_contrato c
			ON cd.contrato_id = c.contrato_id
			INNER JOIN tbl_locales AS l
			ON l.contrato_id = c.contrato_id
			INNER JOIN tbl_razon_social AS r 
			ON r.id = l.razon_social_id
			INNER JOIN cont_tipo_servicio_publico tsp
			ON sp.id_tipo_servicio_publico = tsp.id
			INNER JOIN cont_tipo_estado_servicio_publico AS esp 
			ON sp.estado = esp.id
		WHERE sp.status = 1 AND sp.estado = 2
			".$where_fechas."
			".$where_empresa_arrendataria."
			".$permiso_locales."
	";

	$list_query = $mysqli->query($query);

	$data =  array();
	$num = 1;

	while($reg = $list_query->fetch_object()) 
	{
		$tipo_servicio_publico = $reg->tipo_servicio_publico;

		$empresa_servicio_publico_ruc = "El servicio publico no es Agua ni Luz.";

		if($tipo_servicio_publico == 1)
		{
			// LUZ
			$empresa_servicio_publico_ruc = $reg->empresa_luz_ruc;
		}
		else if($tipo_servicio_publico == 2)
		{
			// AGUA
			$empresa_servicio_publico_ruc = $reg->empresa_agua_ruc;
		}

		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->local_nombre,
			"2" => $reg->empresa_arrendataria,
			"3" => $reg->tipo_servicio_nombre,
			"4" => $empresa_servicio_publico_ruc,
			"5" => NombreMes($reg->periodo_consumo),
			"6" => $reg->nro_suministro,
			"7" => $reg->fecha_vencimiento,
			"8" => $reg->fecha_creacion,
			"9" => $reg->fecha_atencion_contabilidad,
			"10" => $reg->estado_nombre,
			"11" => $reg->total_pagado
		);

		$num++;
	}

	$resultado = array(
		"sEcho" => 1,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);

	echo json_encode($resultado);

	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="contrato_servicio_publico_modal_parametro_plantilla_concar")
{
	$param_tipo_empresa = $_POST['param_tipo_empresa'];
	$param_fecha_incio = date("Y-m-d", strtotime($_POST["param_fecha_incio"]));
	$param_fecha_fin = date("Y-m-d", strtotime($_POST["param_fecha_fin"]));
	$modal_param_num_correlativo = $_POST['modal_param_num_correlativo'];
	$modal_param_fecha_comprobante = date("Y-m-d", strtotime($_POST["modal_param_fecha_comprobante"]));
	
	if($login && $login["usuario_locales"]){
		$permiso_locales=" AND l.id IN (".implode(",", $login["usuario_locales"]).") ";
	}

	$where_empresa_arrendataria = "";
	$where_fechas = "";
	
	if($param_tipo_empresa != 0)
	{
		$where_empresa_arrendataria = " AND l.razon_social_id = '".$param_tipo_empresa."' ";
	}

	if($param_fecha_incio != "" && $param_fecha_fin != "")
	{
		$where_fechas = " AND (date_format(sp.fecha_atencion_contabilidad, '%Y-%m-%d') BETWEEN '".$param_fecha_incio."' AND '".$param_fecha_fin ."') ";
	}
	
	$importe_dolares = 0;
	$fecha_dia = "";
	$fecha_mes = "";
	$fecha_anio = "";

	$glosa = getParameterGeneral('servicio_publico_contabilidad_glosa');
	$cuenta_contable_debe_luz = getParameterGeneral('servicio_publico_contabilidad_cuenta_contable_debe_luz');
	$cuenta_contable_haber_luz = getParameterGeneral('servicio_publico_contabilidad_cuenta_contable_haber_luz');
	$cuenta_contable_debe_agua = getParameterGeneral('servicio_publico_contabilidad_cuenta_contable_debe_agua');
	$cuenta_contable_haber_agua = getParameterGeneral('servicio_publico_contabilidad_cuenta_contable_haber_agua');

	$query = 
	"
		SELECT
			sp.id,
		    r.subdiario_contabilidad AS sub_diario,
		    '' AS numero_comprobante,
		    '{$modal_param_fecha_comprobante}' AS fecha_comprobante,
		    'MN' AS codigo_moneda,
		    sp.periodo_consumo,
		    cis.nro_suministro,
		    l.nombre AS local_nombre,
		    '' AS glosa_principal,
		    tc.monto_venta AS tipo_cambio,
		    'V' AS tipo_conversion,
			'S' AS flag_conversion_moneda,
		    tc.fecha AS fecha_tipo_cambio,
		    '' AS cuenta_contable,
		    cis.tipo_servicio_id AS tipo_servicio_publico,
		    ea.ruc AS empresa_agua_ruc,
			el.ruc AS empresa_luz_ruc,
		    '' AS codigo_anexo,
		    IFNULL(l.cc_id,'0') AS codigo_centro_costo,
		    '' AS debe_haber,
		    sp.total_pagar AS importe_original,
		    sp.tipo_documento AS tipo_documento,
		    CONCAT(sp.serie, '-', sp.numero_recibo) AS num_documento,
			sp.fecha_emision AS fecha_documento,
		    sp.fecha_vencimiento AS fecha_vencimiento,
		    '' AS codigo_area,
		    '' AS glosa_detalle,
		    '' AS codigo_anexo_auxiliar,
			'' AS medio_pago,
			'' AS tipo_documento_referencia,
			'' AS num_documento_referencia,
			'' AS fecha_documento_referencia,
			'' AS maquina_registradora_tipo_documento,
			'' AS base_imponible_documento_referencia,
			'' AS igv_documento_provision,
			'' AS tipo_referencia_estado,
			'' AS num_serie_caja_registradora,
			'' AS fecha_operacion,
			'' AS tipo_tasa,
			'' AS tasa_detraccion_percepcion,
			'' AS importe_base_detraccion_percepcion_dolares,
			'' AS importe_base_detraccion_percepcion_soles,
			'' AS tipo_cambio_para_f,
			'' AS importe_igv_sin_derecho_credito_fiscal,
			'' AS tasa_igv
		FROM cont_local_servicio_publico sp
			INNER JOIN cont_inmueble_suministros cis
			ON sp.inmueble_suministros_id = cis.id
			INNER JOIN cont_inmueble i
			ON cis.inmueble_id = i.id
			LEFT JOIN cont_local_servicio_publico_empresas ea
			ON i.id_empresa_servicio_agua = ea.id
			LEFT JOIN cont_local_servicio_publico_empresas el
			ON i.id_empresa_servicio_luz = el.id
			INNER JOIN cont_contrato_detalle cd
			ON i.contrato_id = cd.contrato_id AND i.contrato_detalle_id = cd.id
			INNER JOIN cont_contrato c
			ON cd.contrato_id = c.contrato_id
			INNER JOIN tbl_locales AS l
			ON l.contrato_id = c.contrato_id
			INNER JOIN tbl_razon_social AS r 
			ON r.id = l.razon_social_id
			LEFT JOIN tbl_tipo_cambio tc
			ON '{$modal_param_fecha_comprobante}' = tc.fecha
		WHERE sp.status = 1 AND sp.estado = 2
			".$where_fechas."
			".$where_empresa_arrendataria."
			".$permiso_locales."
	";

	$list_query = $mysqli->query($query);

	//PROCEDEMOS A CREAR LA CARPETA SI EN CASO NO EXISTA
	$path = "/var/www/html/files_bucket/contratos/servicios_publicos/reportes/contabilidad/";

	if (!is_dir($path)) 
	{
		mkdir($path, 0777, true);
	}

	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/contratos/servicios_publicos/reportes/contabilidad/*'); //obtenemos todos los nombres de los ficheros
	foreach($files as $file)
	{
	    if(is_file($file))
	    unlink($file); //elimino el fichero
	}

	
	require_once '../phpexcel/classes/PHPExcel.php';
	$objPHPExcel = new PHPExcel();

	// Se asignan las propiedades del libro
	$objPHPExcel->getProperties()->setCreator("AT") // Nombre del autor
    ->setDescription("Reporte"); //Descripción

	$tituloReporte = "Reporte Concar";

	$titulosColumnas = array('Sub Diario', 'Número de Comprobante', 'Fecha de Comprobante', 'Código de Moneda', 'Glosa Principal', 'Tipo de Cambio', 'Tipo de Conversión', 'Flag de Conversión de Moneda', 'Fecha Tipo de Cambio', 'Cuenta Contable', 'Código de Anexo', 'Código de Centro de Costo', 'Debe / Haber', 'Importe Original', 'Importe en Dólares', 'Importe en Soles', 'Tipo de Documento', 'Número de Documento', 'Fecha de Documento', 'Fecha de Vencimiento', 'Código de Area', 'Glosa Detalle', 'Código de Anexo Auxiliar', 'Medio de Pago', 'Tipo de Documento de Referencia', 'Número de Documento Referencia', 'Fecha Documento Referencia', 'Nro Máq. Registradora Tipo Doc. Ref.', 'Base Imponible Documento Referencia', 'IGV Documento Provisión', 'Tipo Referencia en estado MQ', 'Número Serie Caja Registradora', 'Fecha de Operación', 'Tipo de Tasa', 'Tasa Detracción/Percepción', 'Importe Base Detracción/Percepción Dólares', 'Importe Base Detracción/Percepción Soles', 'Tipo Cambio para F', 'Importe de IGV sin derecho credito fiscal', 'Tasa IGV');

	// Se agregan los titulos del reporte
	$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('B1', $titulosColumnas[0])	//Titulo de las columnas
			    ->setCellValue('C1', $titulosColumnas[1])
			    ->setCellValue('D1', $titulosColumnas[2])
			    ->setCellValue('E1', $titulosColumnas[3])
			    ->setCellValue('F1', $titulosColumnas[4])
			    ->setCellValue('G1', $titulosColumnas[5])
			    ->setCellValue('H1', $titulosColumnas[6])
			    ->setCellValue('I1', $titulosColumnas[7])
			    ->setCellValue('J1', $titulosColumnas[8])
			    ->setCellValue('K1', $titulosColumnas[9])
			    ->setCellValue('L1', $titulosColumnas[10])
			    ->setCellValue('M1', $titulosColumnas[11])
			    ->setCellValue('N1', $titulosColumnas[12])
			    ->setCellValue('O1', $titulosColumnas[13])
			    ->setCellValue('P1', $titulosColumnas[14])
			    ->setCellValue('Q1', $titulosColumnas[15])
			    ->setCellValue('R1', $titulosColumnas[16])
			    ->setCellValue('S1', $titulosColumnas[17])
			    ->setCellValue('T1', $titulosColumnas[18])
			    ->setCellValue('U1', $titulosColumnas[19])
			    ->setCellValue('V1', $titulosColumnas[20])
			    ->setCellValue('W1', $titulosColumnas[21])
			    ->setCellValue('X1', $titulosColumnas[22])
			    ->setCellValue('Y1', $titulosColumnas[23])
			    ->setCellValue('Z1', $titulosColumnas[24])
			    ->setCellValue('AA1', $titulosColumnas[25])
			    ->setCellValue('AB1', $titulosColumnas[26])
			    ->setCellValue('AC1', $titulosColumnas[27])
			    ->setCellValue('AD1', $titulosColumnas[28])
			    ->setCellValue('AE1', $titulosColumnas[29])
			    ->setCellValue('AF1', $titulosColumnas[30])
			    ->setCellValue('AG1', $titulosColumnas[31])
			    ->setCellValue('AH1', $titulosColumnas[32])
			    ->setCellValue('AI1', $titulosColumnas[33])
			    ->setCellValue('AJ1', $titulosColumnas[34])
			    ->setCellValue('AK1', $titulosColumnas[35])
			    ->setCellValue('AL1', $titulosColumnas[36])
			    ->setCellValue('AM1', $titulosColumnas[37])
			    ->setCellValue('AN1', $titulosColumnas[38])
			    ->setCellValue('AO1', $titulosColumnas[39]);

	//Se agregan los datos a la lista del reporte
	
	$i = 4; //Numero de fila donde se va a comenzar a rellenar
	while ($fila = $list_query->fetch_array()) 
	{
		if(is_null($fila['tipo_cambio']) OR empty($fila['tipo_cambio']))
		{
			echo json_encode(array(
				"ruta_archivo" => "No existe el tipo de cambio de la fecha ".$fila['fecha_comprobante'],
				"estado_archivo" => 0
			));
			exit;
		}

		$fecha_anio = date('Y', strtotime($fila['fecha_comprobante']));
		$fecha_mes = date('m', strtotime($fila['fecha_comprobante']));

		

		$periodo_fecha_anio = date('Y', strtotime($fila['periodo_consumo']));
		$fecha_anio_actual = date('Y');

		if($periodo_fecha_anio == $fecha_anio_actual)
		{
			$codigo_anexo_auxiliar = "01";
		}
		else
		{
			$codigo_anexo_auxiliar = "02";
		}

		$importe_dolares = $fila['importe_original'] / $fila['tipo_cambio'];

		//INICIO: NOMBRE DE LA GLOSA

		$nombre_periodo = substr(NombreMes($fila['periodo_consumo']), 0, 3);
		$glosa_texto = $glosa.$fila['nro_suministro']." ".$nombre_periodo." ".$fila['local_nombre'];

		//FIN: NOMBRE DE LA GLOSA

		$tipo_servicio_publico = $fila['tipo_servicio_publico'];

		if($tipo_servicio_publico == 1)
		{
			// LUZ
			$empresa_servicio_publico_ruc = $fila['empresa_luz_ruc'];
			$cuenta_contable_debe = $cuenta_contable_debe_luz;
			$cuenta_contable_haber = $cuenta_contable_haber_luz;
		}
		else if($tipo_servicio_publico == 2)
		{
			// AGUA
			$empresa_servicio_publico_ruc = $fila['empresa_agua_ruc'];
			$cuenta_contable_debe = $cuenta_contable_debe_agua;
			$cuenta_contable_haber = $cuenta_contable_haber_agua;
		}
		else
		{
			echo json_encode(array(
				"ruta_archivo" => "El servicio publico no es Agua ni Luz",
				"estado_archivo" => 0
			));
			exit;
		}

		$glosa_principal = substr($glosa_texto, 0, 40);
		$glosa_detalle = substr($glosa_texto, 0, 30);

		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('B'.$i, substr($fila['sub_diario'], 0, 4))
		->setCellValue('C'.$i, substr($fecha_mes.str_pad($modal_param_num_correlativo, 4, '0', STR_PAD_LEFT), 0, 6))
		->setCellValue('D'.$i, date('d/m/Y', strtotime($fila['fecha_comprobante'])))
		->setCellValue('E'.$i, substr($fila['codigo_moneda'], 0, 2))
		->setCellValue('F'.$i, $glosa_principal)
		->setCellValue('G'.$i, $fila['tipo_cambio'])
		->setCellValue('H'.$i, $fila['tipo_conversion'])
		->setCellValue('I'.$i, substr($fila['flag_conversion_moneda'], 0, 1))
		->setCellValue('J'.$i, date('d/m/Y', strtotime($fila['fecha_tipo_cambio'])))
		->setCellValue('K'.$i, substr($cuenta_contable_debe, 0, 12))
		->setCellValue('L'.$i, substr($empresa_servicio_publico_ruc, 0, 18))
		->setCellValue('M'.$i, substr($fila['codigo_centro_costo'], 0, 6))
		->setCellValue('N'.$i, substr('D', 0, 1))
		->setCellValue('O'.$i, $fila['importe_original'])
		->setCellValue('P'.$i, $importe_dolares)
		->setCellValue('Q'.$i, $fila['importe_original'])
		->setCellValue('R'.$i, substr($fila['tipo_documento'], 0, 2))
		->setCellValue('S'.$i, substr($fila['num_documento'], 0, 20))
		->setCellValue('T'.$i, date('d/m/Y', strtotime($fila['fecha_documento'])))
		->setCellValue('U'.$i, date('d/m/Y', strtotime($fila['fecha_vencimiento'])))
		->setCellValue('V'.$i, substr('', 0, 3))
		->setCellValue('W'.$i, $glosa_detalle)
		->setCellValue('X'.$i, substr($codigo_anexo_auxiliar, 0, 18))
		->setCellValue('Y'.$i, substr($fila['medio_pago'], 0, 8))
		->setCellValue('Z'.$i, substr($fila['tipo_documento_referencia'], 0, 2))
		->setCellValue('AA'.$i, substr($fila['num_documento_referencia'], 0, 20))
		->setCellValue('AB'.$i, empty($fila['fecha_documento_referencia'])?'':date('d/m/Y', strtotime($fila['fecha_documento_referencia'])))
		->setCellValue('AC'.$i, substr($fila['maquina_registradora_tipo_documento'], 0, 20))
		->setCellValue('AD'.$i, $fila['base_imponible_documento_referencia'])
		->setCellValue('AE'.$i, $fila['igv_documento_provision'])
		->setCellValue('AF'.$i, $fila['tipo_referencia_estado'])
		->setCellValue('AG'.$i, substr($fila['num_serie_caja_registradora'], 0, 15))
		->setCellValue('AH'.$i, $fila['fecha_operacion'])
		->setCellValue('AI'.$i, substr($fila['tipo_tasa'], 0, 5))
		->setCellValue('AJ'.$i, $fila['tasa_detraccion_percepcion'])
		->setCellValue('AK'.$i, $fila['importe_base_detraccion_percepcion_dolares'])
		->setCellValue('AL'.$i, $fila['importe_base_detraccion_percepcion_soles'])
		->setCellValue('AM'.$i, substr($fila['tipo_cambio_para_f'], 0, 1))
		->setCellValue('AN'.$i, $fila['importe_igv_sin_derecho_credito_fiscal'])
		->setCellValue('AO'.$i, $fila['tasa_igv']);

		$i++;
		
		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('B'.$i, substr($fila['sub_diario'], 0, 4))
		->setCellValue('C'.$i, substr($fecha_mes.str_pad($modal_param_num_correlativo, 4, '0', STR_PAD_LEFT), 0, 6))
		->setCellValue('D'.$i, date('d/m/Y', strtotime($fila['fecha_comprobante'])))
		->setCellValue('E'.$i, substr($fila['codigo_moneda'], 0, 2))
		->setCellValue('F'.$i, $glosa_principal)
		->setCellValue('G'.$i, $fila['tipo_cambio'])
		->setCellValue('H'.$i, $fila['tipo_conversion'])
		->setCellValue('I'.$i, substr($fila['flag_conversion_moneda'], 0, 1))
		->setCellValue('J'.$i, date('d/m/Y', strtotime($fila['fecha_tipo_cambio'])))
		->setCellValue('K'.$i, substr($cuenta_contable_haber, 0, 12))
		->setCellValue('L'.$i, substr($empresa_servicio_publico_ruc, 0, 18))
		->setCellValue('M'.$i, substr($fila['codigo_centro_costo'], 0, 6))
		->setCellValue('N'.$i, substr('H', 0, 1))
		->setCellValue('O'.$i, $fila['importe_original'])
		->setCellValue('P'.$i, $importe_dolares)
		->setCellValue('Q'.$i, $fila['importe_original'])
		->setCellValue('R'.$i, substr($fila['tipo_documento'], 0, 2))
		->setCellValue('S'.$i, substr($fila['num_documento'], 0, 20))
		->setCellValue('T'.$i, date('d/m/Y', strtotime($fila['fecha_documento'])))
		->setCellValue('U'.$i, date('d/m/Y', strtotime($fila['fecha_vencimiento'])))
		->setCellValue('V'.$i, substr('', 0, 3))
		->setCellValue('W'.$i, $glosa_detalle)
		->setCellValue('X'.$i, substr($codigo_anexo_auxiliar, 0, 18))
		->setCellValue('Y'.$i, substr($fila['medio_pago'], 0, 8))
		->setCellValue('Z'.$i, substr($fila['tipo_documento_referencia'], 0, 2))
		->setCellValue('AA'.$i, substr($fila['num_documento_referencia'], 0, 20))
		->setCellValue('AB'.$i, empty($fila['fecha_documento_referencia'])?'':date('d/m/Y', strtotime($fila['fecha_documento_referencia'])))
		->setCellValue('AC'.$i, substr($fila['maquina_registradora_tipo_documento'], 0, 20))
		->setCellValue('AD'.$i, $fila['base_imponible_documento_referencia'])
		->setCellValue('AE'.$i, $fila['igv_documento_provision'])
		->setCellValue('AF'.$i, $fila['tipo_referencia_estado'])
		->setCellValue('AG'.$i, substr($fila['num_serie_caja_registradora'], 0, 15))
		->setCellValue('AH'.$i, $fila['fecha_operacion'])
		->setCellValue('AI'.$i, substr($fila['tipo_tasa'], 0, 5))
		->setCellValue('AJ'.$i, $fila['tasa_detraccion_percepcion'])
		->setCellValue('AK'.$i, $fila['importe_base_detraccion_percepcion_dolares'])
		->setCellValue('AL'.$i, $fila['importe_base_detraccion_percepcion_soles'])
		->setCellValue('AM'.$i, substr($fila['tipo_cambio_para_f'], 0, 1))
		->setCellValue('AN'.$i, $fila['importe_igv_sin_derecho_credito_fiscal'])
		->setCellValue('AO'.$i, $fila['tasa_igv']);

		$i++;
		$modal_param_num_correlativo++;
	}



	$estiloColoFondoAmarilloOscuro = array(
	    'fill' => array(
	      'type'  => PHPExcel_Style_Fill::FILL_SOLID,
	      'color' => array(
	            'rgb' => 'ffc000')
	  )
	);
	  
	$estiloTituloColumnas = array(
	    'font' => array(
	        'name'  => 'Arial',
	        'bold'  => false,
	        'size' => 10,
	        'color' => array(
	            'rgb' => '000000'
	        )
	    ),
	    'alignment' =>  array(
	        'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        'wrap'      => false
	    )
	);
	  
	$estiloInformacion = new PHPExcel_Style();
	$estiloInformacion->applyFromArray( array(
	    'font' => array(
	        'name'  => 'Arial',
	        'color' => array(
	            'rgb' => '000000'
	        )
	    )
	));


	$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(57);



	$objPHPExcel->getActiveSheet()->getStyle('B1:Z1')->applyFromArray($estiloTituloColumnas);
	$objPHPExcel->getActiveSheet()->getStyle('AA1:AO1')->applyFromArray($estiloTituloColumnas);

	$objPHPExcel->getActiveSheet()->getStyle('B1:Z1')->applyFromArray($estiloColoFondoAmarilloOscuro);
	$objPHPExcel->getActiveSheet()->getStyle('AA1:AO1')->applyFromArray($estiloColoFondoAmarilloOscuro);

	
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A4:AN".($i-1));

	$objPHPExcel->getActiveSheet()->getStyle('O4:O'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');
	$objPHPExcel->getActiveSheet()->getStyle('P4:P'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');
	$objPHPExcel->getActiveSheet()->getStyle('Q4:Q'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');

	// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
	for($i = 'B'; $i <= 'Z'; $i++)
	{
	    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}

	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('Mesa de Partes');
	  
	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);
	
	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(0);
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Plantilla Concar Servicio Publico.xls');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$excel_path = '/var/www/html/files_bucket/contratos/servicios_publicos/programacion/plantilla_concar/Plantilla Concar Servicio Publico.xls';
	$excel_path_download = '/files_bucket/contratos/servicios_publicos/programacion/plantilla_concar/Plantilla Concar Servicio Publico.xls';
	
	try 
	{
		$objWriter->save($excel_path);
	} 
	catch (Exception $e)
	{
		echo json_encode(["error" => $e]);
		exit;
	}

	echo json_encode(array(
		"ruta_archivo" => $excel_path_download,
		"estado_archivo" => 1
	));
	exit;
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_registros_locales_fechas") {
	$id_local = $_POST["local_id"];
	$id_empresa = $_POST["id_empresa"];
	$id_jefe_comercial = $_POST["id_jefe_comercial"];
	$id_supervisor = $_POST["id_supervisor"];
	$periodo = $_POST["periodo"];
	$fec_vcto_desde = date("Y-m-d", strtotime($_POST["fec_vcto_desde"]));
	$fec_vcto_hasta = date("Y-m-d", strtotime($_POST["fec_vcto_hasta"]));
	$pendientes = $_POST["btn_pendientes"];
	$tipo_servicio = $_POST["tipo_servicio"];
	$permiso_monto = $_POST["permiso_monto"];

	$estado = $_POST["estado"];

	$where_periodo = "";
	$where_empresa = "";
	$where_local = "";
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
	if($periodo != 0){
		$anio = substr($periodo, 0 , 4);
		$mes = substr($periodo, 5, 2);	
		$where_periodo = " AND month(sp.periodo_consumo) = " . $mes . " and year(sp.periodo_consumo) = " . $anio;
	}
	if (!Empty($estado) && $estado != 0) {
		if ($estado != 9) {
			$where_estado = " AND sp.estado = ".$estado;
		}
	}
	if($fec_vcto_desde != "" && $fec_vcto_hasta != ""){
		$having_fec_vcto = " AND (sp.fecha_vencimiento between '" . $fec_vcto_desde . "' and '" . $fec_vcto_hasta . "')";
	}


	if (!empty($having_fec_vcto)) {
		$query = "
			SELECT 
				sp.id, sp.id_tipo_servicio_publico, sp.periodo_consumo, 
				DATE_FORMAT(sp.fecha_emision, '%d/%m/%Y') as fecha_emision,
				DATE_FORMAT(sp.fecha_vencimiento, '%d/%m/%Y') as fecha_vencimiento, 
				sp.monto_total, sp.estado,
		 		esp.nombre as nombre_estado,
		 		tl.id AS local_id,ifnull(tl.cc_id,'0') AS centro_costo, tl.nombre AS local_nombre,
				i.tipo_compromiso_pago_agua,
				i.tipo_compromiso_pago_luz,
				
				concat( IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS jefe_comercial,
				concat( IFNULL(tpp.nombre, ''),' ', IFNULL(tpp.apellido_paterno, ''),' ', IFNULL(tpp.apellido_materno, '')) AS supervisor,
				tsp.nombre as tipo_servicio,
				r.nombre as empresa
				
				FROM cont_local_servicio_publico AS sp
					INNER JOIN cont_tipo_servicio_publico AS tsp 
					ON sp.id_tipo_servicio_publico = tsp.id
					INNER JOIN tbl_locales  AS tl  ON sp.id_local = tl.id
					INNER JOIN cont_contrato AS c ON c.contrato_id = tl.contrato_id
					INNER JOIN cont_inmueble AS  i ON i.contrato_id = c.contrato_id

					INNER JOIN tbl_razon_social AS r ON r.id = tl.razon_social_id
					
					LEFT JOIN tbl_usuarios_locales tuls 
					ON tuls.local_id = tl.id AND tuls.estado = 1
					LEFT JOIN tbl_usuarios tus ON tuls.usuario_id = tus.id AND tus.estado = 1
					INNER JOIN tbl_personal_apt tp 
					ON tp.id = tus.personal_id AND tp.area_id = 21 
					AND tp.cargo_id = 16 AND tp.estado = 1

					LEFT JOIN tbl_usuarios_locales tulss 
					ON tulss.local_id = tl.id AND tulss.estado = 1
					LEFT JOIN tbl_usuarios tuss ON tulss.usuario_id = tuss.id AND tuss.estado = 1
					INNER JOIN tbl_personal_apt tpp 
					ON tpp.id = tuss.personal_id AND tpp.area_id = 21 
					AND tpp.cargo_id = 4 AND tpp.estado = 1
					LEFT JOIN cont_tipo_estado_servicio_publico AS esp 
					ON sp.estado = esp.id
				-- WHERE sp.status = 1 and (esp.id <> 3 AND esp.id <> 5)
				WHERE sp.status = 1
					".$where_local."
					".$where_empresa."
					".$where_jefe_comercial."
					".$where_supervisor."
					".$where_tipo_servicio."
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
				'empresa' => $sp['empresa'],
				'local_id' => $sp['local_id'],
				'local_nombre' => $sp['local_nombre'],
				'supervisor' => $sp['supervisor'],
				'periodo_consumo' => $sp['periodo_consumo'],
				'fecha_vencimiento' => $sp['fecha_vencimiento'],
				'tipo_compromiso_pago_agua' => $sp['tipo_compromiso_pago_agua'],
				'tipo_compromiso_pago_luz' => $sp['tipo_compromiso_pago_luz'],
				'estado' => $sp['nombre_estado'],
				'id_recibo' => $sp['id'],
				'monto_total' => $sp['monto_total'],
			));

		}


		$table = '<table id="sec_con_tabla_registros" class="table table-striped table-hover table-condensed table-bordered dt-responsive display" cellspacing="0" width="100%">
		<thead>
			<tr role="row">
				<th style="text-align: center;">C.C.</th>
				<th style="text-align: center;">Local</th>
				<th style="text-align: center;">Razón Social</th>
				<th style="text-align: center;">Jefe Comercial</th>
				<th style="text-align: center;">Supervisor</th>
				<th style="text-align: center;">Periodo</th>
				<th style="text-align: center;">Tipo Servicio</th>
				<th style="text-align: center;">Monto</th>
				<th style="text-align: center;">Fec. Vcto.</th>
				<th style="text-align: center;">Estado</th>
				';
		$table .= '</tr>
			</thead>
			<tbody>';
			
			for ($i=0; $i < count($list_proc_registros) ; $i++) { 
				$onclick = "agregarMonto(".$list_proc_registros[$i]['id_tipo_servicio_publico'].",".$list_proc_registros[$i]['local_id'].",'".$list_proc_registros[$i]['periodo_consumo'].
				"','".$list_proc_registros[$i]['centro_costo']." ".$list_proc_registros[$i]['local_nombre']."', ".$list_proc_registros[$i]['id_recibo'].")";
				$button_agregar_monto_luz = '<button type="button" class="btn btn-sm btn-success" onclick="'.$onclick.'")">Agregar Monto</button>';
				
				if($list_proc_registros[$i]['monto_total'] == 0 && $list_proc_registros[$i]['id_recibo'] > 0){
					if($permiso_monto == 'true'){
						$list_proc_registros[$i]['monto_total'] = $button_agregar_monto_luz;	
					}else{
						$list_proc_registros[$i]['monto_total'] = "0.00";
					}
				}else if ($list_proc_registros[$i]['id_recibo'] == 0){
					$list_proc_registros[$i]['monto_total'] = "SIN RECIBO";
				}else{
					$onClick_verDetalle = "verDetalle(".$list_proc_registros[$i]['id_recibo'].")";
					$list_proc_registros[$i]['monto_total'] = '<span>'.$list_proc_registros[$i]['monto_total'].'</span>
					<button type="button" class="btn btn-sm btn-info" style="margin-left: 10px" onclick="'.$onclick.'"><i class="fa fa-eye"></i></button>';
				}

				$table .= '
				<tr>
					<td>'.$list_proc_registros[$i]['centro_costo'].'</td>
					<td>'.$list_proc_registros[$i]['local_nombre'].'</td>
					<td>'.$list_proc_registros[$i]['empresa'].'</td>
					<td>'.$list_proc_registros[$i]['jefe_comercial'].'</td>
					<td>'.$list_proc_registros[$i]['supervisor'].'</td>
					<td>'.NombreMes($list_proc_registros[$i]['periodo_consumo']).'</td>
					<td>'.$list_proc_registros[$i]['tipo_servicio'].'</td>
					<td class="text-right">'.$list_proc_registros[$i]['monto_total'].'</td>
					<td class="text-center">'.$list_proc_registros[$i]['fecha_vencimiento'].'</td>
					<td class="text-center">'.$list_proc_registros[$i]['estado'].'</td>
				</tr>';
			}
		
			
		$table .= '
			</tbody>
		</table>';


		if ($mysqli->error) {
			$result["error"] = $mysqli->error." | ".$query;
		}
		if (count($list_proc_registros) == 0) {
			$result["http_code"] = 400;
			$result["status"] = "No hay registros.";
		} elseif (count($list_proc_registros) > 0) {
			$result["http_code"] = 200;
			$result["table"] = $table;
			$result["status"] = "OK";
			$result["result"] = $list_proc_registros;
		} else {
			$result["http_code"] = 400;
			$result["status"] = "Ocurrió un error al consultar los registros.";
		}
		echo json_encode($result);
		exit();
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_registros_locales_tesoreria") {
	$buscar_por = $_POST["buscar_por"];
	$id_local = $_POST["local_id"];
	$id_empresa = $_POST["id_empresa"];
	$id_jefe_comercial = $_POST["id_jefe_comercial"];
	$id_supervisor = $_POST["id_supervisor"];
	$periodo = $_POST["periodo"];
	$fec_vcto_desde = date("Y-m-d", strtotime($_POST["fec_vcto_desde"]));
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
		$where_empresa = " AND l.razon_social_id = " . $id_empresa;
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

	if (!Empty($estado) && $estado != 0) 
	{
		$where_estado = " AND sp.estado = ".$estado;
	}
	else
	{
		$where_estado = " AND sp.estado IN (2, 3) ";	
	}

	$query = "
		SELECT sp.id, sp.id_tipo_servicio_publico, sp.periodo_consumo, 
		DATE_FORMAT(sp.fecha_emision, '%d/%m/%Y') as fecha_emision, 
		DATE_FORMAT(sp.fecha_vencimiento, '%d/%m/%Y') as fecha_vencimiento, 
		sp.fecha_carga_pago,
		sp.fecha_pago,
		sp.total_pagar, sp.monto_total, sp.estado,
		esp.nombre as nombre_estado,
		tl.id AS local_id,IFNULL(tl.cc_id,'0') AS centro_costo, tl.nombre AS local_nombre,
		-- i.tipo_compromiso_pago_agua,
		-- i.tipo_compromiso_pago_luz,
		s.tipo_compromiso_pago_id,
		
		concat( IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS jefe_comercial,
		concat( IFNULL(tpp.nombre, ''),' ', IFNULL(tpp.apellido_paterno, ''),' ', IFNULL(tpp.apellido_materno, '')) AS supervisor,
		tsp.nombre as tipo_servicio, r.nombre AS empresa,
		
		-- case when sp.id_tipo_servicio_publico = 1 
		-- then IFNULL(i.num_suministro_luz,'') else IFNULL(i.num_suministro_agua,'') end as numero_suministro

		s.nro_suministro AS numero_suministro
	
		FROM cont_local_servicio_publico AS sp
			INNER JOIN cont_tipo_servicio_publico AS tsp ON sp.id_tipo_servicio_publico = tsp.id
			INNER JOIN tbl_locales  AS tl  ON sp.id_local = tl.id
			INNER JOIN cont_contrato AS c ON c.contrato_id = tl.contrato_id

			INNER JOIN cont_contrato_detalle AS cd
			ON cd.contrato_id = c.contrato_id
			
			INNER JOIN cont_inmueble AS  i ON i.contrato_id = c.contrato_id

			INNER JOIN cont_inmueble_suministros AS s
			ON s.inmueble_id = i.id AND sp.inmueble_suministros_id = s.id

			INNER JOIN tbl_razon_social AS r ON r.id = l.razon_social_id

			LEFT JOIN tbl_usuarios_locales tuls ON tuls.local_id = tl.id  AND tuls.estado = 1
			LEFT JOIN tbl_usuarios tus ON tuls.usuario_id = tus.id AND tus.estado = 1
			INNER JOIN tbl_personal_apt tp ON tp.id = tus.personal_id AND tp.area_id = 21 
			AND tp.cargo_id = 16 AND tp.estado = 1

			LEFT JOIN tbl_usuarios_locales tulss ON tulss.local_id = tl.id  AND tulss.estado = 1
			LEFT JOIN tbl_usuarios tuss ON tulss.usuario_id = tuss.id AND tuss.estado = 1
			INNER JOIN tbl_personal_apt tpp ON tpp.id = tuss.personal_id AND tpp.area_id = 21 
			AND tpp.cargo_id = 4 AND tpp.estado = 1


			LEFT JOIN cont_tipo_estado_servicio_publico AS esp ON sp.estado = esp.id
		-- WHERE sp.status = 1 AND sp.estado = 2
		WHERE sp.status = 1
			".$where_local."
			".$where_empresa."
			".$where_jefe_comercial."
			".$where_supervisor."
			".$where_tipo_servicio."
			".$where_estado."
			".$where_periodo."
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
			
			'empresa' => $sp['empresa'],
			'periodo_consumo' => $sp['periodo_consumo'],
			'fecha_vencimiento' => $sp['fecha_vencimiento'],
			'fecha_pago' => $sp['fecha_pago'],
			'fecha_carga_pago' => $sp['fecha_carga_pago'],
			// 'tipo_compromiso_pago_agua' => $sp['tipo_compromiso_pago_agua'],
			// 'tipo_compromiso_pago_luz' => $sp['tipo_compromiso_pago_luz'],
			'estado_id' => $sp['estado'],
			'estado' => $sp['nombre_estado'],
			'id_recibo' => $sp['id'],
			'monto_total' => $sp['total_pagar'],
		));

	}


	$table = '<table id="sec_con_tabla_registros" class="table table-striped table-hover table-condensed table-bordered dt-responsive display" cellspacing="0" width="100%">
	<thead>
		<tr role="row">
			<th style="text-align: center;">C.C.</th>
			<th style="text-align: center;">Local</th>
			<th style="text-align: center;">Razón Social</th>
			<th style="text-align: center;">Jefe Comercial</th>
			<th style="text-align: center;">Supervisor</th>
			<th style="text-align: center;">Periodo</th>
			<th style="text-align: center;">Tipo Servicio</th>
			<th style="text-align: center;">N° suministro</th>
			<th style="text-align: center;">Monto</th>
			<th style="text-align: center;">Fec. Vcto.</th>
			<th style="text-align: center;">Estado</th>
			<th style="text-align: center;">F. Carga</th>
			<th style="text-align: center;">F. Pago</th>
			<th style="text-align: center;">Acciones</th>
			';
	$table .= '</tr>
		</thead>
		<tbody>';
		
		for ($i=0; $i < count($list_proc_registros) ; $i++)
		{ 
			$onclick = "ModalCancelar(".$list_proc_registros[$i]['id_tipo_servicio_publico'].",".$list_proc_registros[$i]['local_id'].",'".$list_proc_registros[$i]['periodo_consumo'].
			"','".$list_proc_registros[$i]['centro_costo'].' '.$list_proc_registros[$i]['local_nombre']."', ".$list_proc_registros[$i]['id_recibo'].")";

			$list_proc_registros[$i]['monto_total'] = '<span>'.$list_proc_registros[$i]['monto_total'].'</span>';

			$boton_pagar = "";

			if($list_proc_registros[$i]['estado_id'] == 3)
			{
				// ESTADO PAGADO
				$boton_pagar = "";
			}
			else
			{
				$boton_pagar = '<button onclick="'.$onclick.'" class="btn btn-info" type="button">Pagar</button>';
			}

			$table .= '
			<tr>
				<td>'.$list_proc_registros[$i]['centro_costo'].'</td>
				<td>'.$list_proc_registros[$i]['local_nombre'].'</td>
				<td>'.$list_proc_registros[$i]['empresa'].'</td>
				<td>'.$list_proc_registros[$i]['jefe_comercial'].'</td>
				<td>'.$list_proc_registros[$i]['supervisor'].'</td>
				<td>'.NombreMes($list_proc_registros[$i]['periodo_consumo']).'</td>
				<td>'.$list_proc_registros[$i]['tipo_servicio'].'</td>
				<td>'.$list_proc_registros[$i]['numero_suministro'].'</td>
				<td class="text-right">'.$list_proc_registros[$i]['monto_total'].'</td>
				<td class="text-center">'.$list_proc_registros[$i]['fecha_vencimiento'].'</td>
				<td class="text-center">'.$list_proc_registros[$i]['estado'].'</td>
				<td class="text-center">'.$list_proc_registros[$i]['fecha_pago'].'</td>
				<td class="text-center">'.$list_proc_registros[$i]['fecha_carga_pago'].'</td>
				<td class="text-center">'.$boton_pagar.'</td>
			</tr>';
		}
	
		
	$table .= '
		</tbody>
	</table>';


	if ($mysqli->error) {
		$result["error"] = $mysqli->error." | ".$query;
	}
	if (count($list_proc_registros) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
	} elseif (count($list_proc_registros) > 0) {
		$result["http_code"] = 200;
		$result["table"] = $table;
		$result["status"] = "OK";
		$result["result"] = $list_proc_registros;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los registros.";
	}
	echo json_encode($result);
	exit();

}

if (isset($_POST["accion"]) && $_POST["accion"]==="contrato_servicio_publico_buscar_servicio_publico_tesoreria")
{
	$param_buscar_por = $_POST['param_buscar_por'];
	$param_tipo_servicio = $_POST['param_tipo_servicio'];
	$param_empresa_arrendataria = $_POST['param_empresa_arrendataria'];
	$param_zona = $_POST['param_zona'];
	$param_supervisor = $_POST['param_supervisor'];
	$param_local = $_POST['param_local'];
	$param_fecha_incio = date("Y-m-d", strtotime($_POST["param_fecha_incio"]));
	$param_fecha_fin = date("Y-m-d", strtotime($_POST["param_fecha_fin"]));
	$param_periodo = $_POST['param_periodo'];
	$param_estado = $_POST['param_estado'];

	$query = "";
	
	$where_tipo_servicio = "";
	$where_empresa_arrendataria = "";
	$where_zona = "";
	$where_supervisor = "";
	$where_local = "";
	$where_periodo = "";
	$where_fechas = "";
	$where_estado = "";

	if($param_tipo_servicio != 0)
	{
		$where_tipo_servicio = " AND sp.id_tipo_servicio_publico = '".$param_tipo_servicio."' ";
	}

	if($param_empresa_arrendataria != 0)
	{
		$where_empresa_arrendataria = " AND c.empresa_suscribe_id = '".$param_empresa_arrendataria."' ";
	}

	if($param_zona != 0)
	{
		$where_zona = " AND l.zona_id = '".$param_zona."' ";
	}

	if($param_supervisor != 0)
	{
		$where_supervisor = " AND us.id = '".$param_supervisor."' ";
	}

	if($param_local != 0)
	{
		$where_local = " AND l.id = '".$param_local."' ";
	}

	if($param_buscar_por == 1)
	{
		// BUSCAR POR PERIODO
		if($param_periodo != 0)
		{
			$anio = substr($param_periodo, 0 , 4);
			$mes = substr($param_periodo, 5, 2);	
			$where_periodo = " AND month(sp.periodo_consumo) = '".$mes."' 
								AND year(sp.periodo_consumo) = '".$anio."' ";
		}
	}
	else if($param_buscar_por == 2)
	{
		// BUSCAR POR FECHAS
		if($param_fecha_incio != "" && $param_fecha_fin != "")
		{
			$where_fechas = " AND (sp.fecha_vencimiento BETWEEN '".$param_fecha_incio."' 
									AND '".$param_fecha_fin ."') ";
		}
	}

	if($param_estado != 0)
	{
		$where_estado = " AND sp.estado = '".$param_estado."' ";
	}
	else
	{
		$where_estado = " AND sp.estado IN (2, 3)";
	}

	$query = "
		SELECT
			sp.id,
			sp.id_tipo_servicio_publico,
		    r.nombre AS empresa_arrendataria,
		    z.nombre AS zona_nombre,
		    concat( IFNULL(pj.nombre, ''),' ', IFNULL(pj.apellido_paterno, ''),' ', IFNULL(pj.apellido_materno, '')) AS jefe_comercial,
		    concat( IFNULL(ps.nombre, ''),' ', IFNULL(ps.apellido_paterno, ''),' ', IFNULL(ps.apellido_materno, '')) AS supervisor,
		    IFNULL(l.cc_id,'0') AS local_centro_costo,
		    l.id AS local_id,
			l.nombre AS local_nombre,
		    tsp.nombre AS tipo_servicio_nombre,
		    sp.periodo_consumo,
		    cis.nro_suministro,
		    sp.monto_total AS monto,
		    sp.fecha_emision,
		    sp.fecha_vencimiento,
		    sp.estado,
		    esp.nombre as estado_nombre,
		    sp.total_pagar AS total_pagado,
		    sp.fecha_carga_pago,
		    sp.fecha_pago
		FROM cont_local_servicio_publico sp
			INNER JOIN cont_inmueble_suministros cis
			ON sp.inmueble_suministros_id = cis.id
			INNER JOIN cont_inmueble i
			ON cis.inmueble_id = i.id
			INNER JOIN cont_contrato_detalle cd
			ON i.contrato_id = cd.contrato_id AND i.contrato_detalle_id = cd.id
			INNER JOIN cont_contrato c
			ON cd.contrato_id = c.contrato_id
			INNER JOIN tbl_razon_social AS r 
			ON r.id = c.empresa_suscribe_id
			INNER JOIN tbl_locales AS l
			ON l.contrato_id = c.contrato_id
			INNER JOIN tbl_zonas AS z
			ON l.zona_id = z.id
			LEFT JOIN tbl_personal_apt AS pj
			ON z.jop_id = pj.id 
			INNER JOIN tbl_usuarios_locales AS uls
			ON l.id = uls.local_id AND uls.estado = 1
			INNER JOIN tbl_usuarios AS us
			ON uls.usuario_id = us.id
			INNER JOIN tbl_personal_apt AS ps
			ON us.personal_id = ps.id AND ps.area_id = 21 AND ps.cargo_id = 4
			INNER JOIN cont_tipo_servicio_publico tsp
			ON sp.id_tipo_servicio_publico = tsp.id
			INNER JOIN cont_tipo_estado_servicio_publico AS esp 
			ON sp.estado = esp.id
		WHERE sp.status = 1
			".$where_tipo_servicio."
			".$where_empresa_arrendataria."
			".$where_zona."
			".$where_supervisor."
			".$where_local."
			".$where_periodo."
			".$where_fechas."
			".$where_estado."
	";

	$list_query = $mysqli->query($query);

	$data =  array();
	$num = 1;

	while($reg = $list_query->fetch_object()) 
	{
		$onclick = "ModalCancelar('".$reg->id_tipo_servicio_publico."', '".$reg->local_id."', '".$reg->periodo_consumo."', '".$reg->local_centro_costo." ".$reg->local_nombre."', '".$reg->id."')";

		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->jefe_comercial,
			"2" => $reg->supervisor,
			"3" => $reg->local_nombre,
			"4" => $reg->tipo_servicio_nombre,
			"5" => NombreMes($reg->periodo_consumo),
			"6" => $reg->nro_suministro,
			"7" => $reg->monto,
			"8" => $reg->fecha_vencimiento,
			"9" => $reg->estado_nombre,
			"10" => $reg->total_pagado,
			"11" => $reg->fecha_carga_pago,
			"12" => $reg->fecha_pago,
			"13" => ($reg->estado != 3) ? 
					'<button type="button" 
						class="btn btn-sm btn-info" style="margin-left: 10px" 
						onclick="'.$onclick.'">
						Pagar
					</button>'
					:
					''
		);

		$num++;
	}

	$resultado = array(
		"sEcho" => 1,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);

	echo json_encode($resultado);

	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="contrato_servicio_publico_reporte_btn_listar_servicios_publicos_tesoreria")
{
	$param_buscar_por = $_POST['param_buscar_por'];
	$param_tipo_servicio = $_POST['param_tipo_servicio'];
	$param_empresa_arrendataria = $_POST['param_empresa_arrendataria'];
	$param_zona = $_POST['param_zona'];
	$param_supervisor = $_POST['param_supervisor'];
	$param_local = $_POST['param_local'];
	$param_fecha_incio = date("Y-m-d", strtotime($_POST["param_fecha_incio"]));
	$param_fecha_fin = date("Y-m-d", strtotime($_POST["param_fecha_fin"]));
	$param_periodo = $_POST['param_periodo'];
	$param_estado = $_POST['param_estado'];

	$query = "";
	
	$where_tipo_servicio = "";
	$where_empresa_arrendataria = "";
	$where_zona = "";
	$where_supervisor = "";
	$where_local = "";
	$where_periodo = "";
	$where_fechas = "";
	$where_estado = "";

	if($param_tipo_servicio != 0)
	{
		$where_tipo_servicio = " AND sp.id_tipo_servicio_publico = '".$param_tipo_servicio."' ";
	}

	if($param_empresa_arrendataria != 0)
	{
		$where_empresa_arrendataria = " AND l.razon_social_id = '".$param_empresa_arrendataria."' ";
	}

	if($param_zona != 0)
	{
		$where_zona = " AND l.zona_id = '".$param_zona."' ";
	}

	if($param_supervisor != 0)
	{
		$where_supervisor = " AND us.id = '".$param_supervisor."' ";
	}

	if($param_local != 0)
	{
		$where_local = " AND l.id = '".$param_local."' ";
	}

	if($param_buscar_por == 1)
	{
		// BUSCAR POR PERIODO
		if($param_periodo != 0)
		{
			$anio = substr($param_periodo, 0 , 4);
			$mes = substr($param_periodo, 5, 2);	
			$where_periodo = " AND month(sp.periodo_consumo) = '".$mes."' 
								AND year(sp.periodo_consumo) = '".$anio."' ";
		}
	}
	else if($param_buscar_por == 2)
	{
		// BUSCAR POR FECHAS
		if($param_fecha_incio != "" && $param_fecha_fin != "")
		{
			$where_fechas = " AND (sp.fecha_vencimiento BETWEEN '".$param_fecha_incio."' 
									AND '".$param_fecha_fin ."') ";
		}
	}

	if($param_estado != 0)
	{
		$where_estado = " AND sp.estado = '".$param_estado."' ";
	}
	else
	{
		$where_estado = " AND sp.estado IN (2, 3)";
	}

	$query = "
		SELECT
			sp.id,
			sp.id_tipo_servicio_publico,
		    r.nombre AS empresa_arrendataria,
		    z.nombre AS zona_nombre,
		    concat( IFNULL(pj.nombre, ''),' ', IFNULL(pj.apellido_paterno, ''),' ', IFNULL(pj.apellido_materno, '')) AS jefe_comercial,
		    concat( IFNULL(ps.nombre, ''),' ', IFNULL(ps.apellido_paterno, ''),' ', IFNULL(ps.apellido_materno, '')) AS supervisor,
		    IFNULL(l.cc_id,'0') AS local_centro_costo,
		    l.id AS local_id,
			l.nombre AS local_nombre,
		    tsp.nombre AS tipo_servicio_nombre,
		    sp.periodo_consumo,
		    cis.nro_suministro,
		    sp.monto_total AS monto,
		    sp.fecha_emision,
		    sp.fecha_vencimiento,
		    sp.estado,
		    esp.nombre as estado_nombre,
		    sp.total_pagar AS total_pagado,
		    sp.fecha_carga_pago,
		    sp.fecha_pago
		FROM cont_local_servicio_publico sp
			INNER JOIN cont_inmueble_suministros cis
			ON sp.inmueble_suministros_id = cis.id
			INNER JOIN cont_inmueble i
			ON cis.inmueble_id = i.id
			INNER JOIN cont_contrato_detalle cd
			ON i.contrato_id = cd.contrato_id AND i.contrato_detalle_id = cd.id
			INNER JOIN cont_contrato c
			ON cd.contrato_id = c.contrato_id
			INNER JOIN tbl_locales AS l
			ON l.contrato_id = c.contrato_id
			INNER JOIN tbl_razon_social AS r 
			ON r.id = l.razon_social_id
			INNER JOIN tbl_zonas AS z
			ON l.zona_id = z.id
			LEFT JOIN tbl_personal_apt AS pj
			ON z.jop_id = pj.id 
			INNER JOIN tbl_usuarios_locales AS uls
			ON l.id = uls.local_id AND uls.estado = 1
			INNER JOIN tbl_usuarios AS us
			ON uls.usuario_id = us.id
			INNER JOIN tbl_personal_apt AS ps
			ON us.personal_id = ps.id AND ps.area_id = 21 AND ps.cargo_id = 4
			INNER JOIN cont_tipo_servicio_publico tsp
			ON sp.id_tipo_servicio_publico = tsp.id
			INNER JOIN cont_tipo_estado_servicio_publico AS esp 
			ON sp.estado = esp.id
		WHERE sp.status = 1
			".$where_tipo_servicio."
			".$where_empresa_arrendataria."
			".$where_zona."
			".$where_supervisor."
			".$where_local."
			".$where_periodo."
			".$where_fechas."
			".$where_estado."
	";

	$list_query = $mysqli->query($query);

	//PROCEDEMOS A CREAR LA CARPETA SI EN CASO NO EXISTA
	$path = "/var/www/html/files_bucket/contratos/servicios_publicos/reportes/tesoreria/";

	if (!is_dir($path)) 
	{
		mkdir($path, 0777, true);
	}

	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/contratos/servicios_publicos/reportes/tesoreria/*'); //obtenemos todos los nombres de los ficheros
	foreach($files as $file)
	{
	    if(is_file($file))
	    unlink($file); //elimino el fichero
	}


	require_once '../phpexcel/classes/PHPExcel.php';
	$objPHPExcel = new PHPExcel();

	// Se asignan las propiedades del libro
	$objPHPExcel->getProperties()->setCreator("AT") // Nombre del autor
    ->setDescription("Reporte"); //Descripción

    $tituloReporte = "Relación de Servicios Públicos - Tesorería";

	$titulosColumnas = array('Nº', 'Razón Social', 'Zona', 'Jefe Comercial', 'Supervisor', 'Local', 'Servicio', 'Periodo', 'Cod. Suministro', 'Monto', 'F. Vencimiento', 'Estado', 'Monto Total', 'F. Carga', 'F. Pago');

	// Se combinan las celdas A1 hasta M1, para colocar ahí el titulo del reporte
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:O1');
	
	$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A1', $tituloReporte);

	// Se agregan los titulos del reporte
	$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A2', $titulosColumnas[0])  //Titulo de las columnas
    ->setCellValue('B2', $titulosColumnas[1])
    ->setCellValue('C2', $titulosColumnas[2])
    ->setCellValue('D2', $titulosColumnas[3])
    ->setCellValue('E2', $titulosColumnas[4])
    ->setCellValue('F2', $titulosColumnas[5])
    ->setCellValue('G2', $titulosColumnas[6])
    ->setCellValue('H2', $titulosColumnas[7])
    ->setCellValue('I2', $titulosColumnas[8])
    ->setCellValue('J2', $titulosColumnas[9])
    ->setCellValue('K2', $titulosColumnas[10])
    ->setCellValue('L2', $titulosColumnas[11])
    ->setCellValue('M2', $titulosColumnas[12])
    ->setCellValue('N2', $titulosColumnas[13])
    ->setCellValue('O2', $titulosColumnas[14]);

    //Se agregan los datos a la lista del reporte
	$cont = 0;

	$i = 3; //Numero de fila donde se va a comenzar a rellenar los datos
	while ($fila = $list_query->fetch_array()) 
	{
		$cont ++;

		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('A'.$i, $cont)
		->setCellValue('B'.$i, $fila['empresa_arrendataria'])
		->setCellValue('C'.$i, $fila['zona_nombre'])
		->setCellValue('D'.$i, $fila['jefe_comercial'])
		->setCellValue('E'.$i, $fila['supervisor'])
		->setCellValue('F'.$i, $fila['local_nombre'])
		->setCellValue('G'.$i, $fila['tipo_servicio_nombre'])
		->setCellValue('H'.$i, $fila['periodo_consumo'])
		->setCellValue('I'.$i, $fila['nro_suministro'])
		->setCellValue('J'.$i, "S/ ".$fila['monto'])
		->setCellValue('K'.$i, $fila['fecha_vencimiento'])
		->setCellValue('L'.$i, $fila['estado_nombre'])
		->setCellValue('M'.$i, "S/ ".$fila['total_pagado'])
		->setCellValue('N'.$i, $fila['fecha_carga_pago'])
		->setCellValue('O'.$i, $fila['fecha_pago']);
		
		$i++;
	}

	$estiloNombresColumnas = array(
		'font' => array(
	        'name'      => 'Calibri',
	        'bold'      => true,
	        'italic'    => false,
	        'strike'    => false,
	        'size' =>10,
	        'color'     => array(
	            'rgb' => '000000'
	        )
	    ),
	    'fill' => array(
			  'type'  => PHPExcel_Style_Fill::FILL_SOLID,
			  'color' => array(
			        'rgb' => 'FFFFFF')
			),
	    'alignment' =>  array(
	        'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        'wrap'      => false
	    ),
	    'borders' => array(
		        'allborders' => array(
		            'style' => PHPExcel_Style_Border::BORDER_THIN
		        )
		    )
	);

	$estiloInformacion = new PHPExcel_Style();
	$estiloInformacion->applyFromArray( array(
	    'font' => array(
	        'name'  => 'Arial',
	        'color' => array(
	            'rgb' => '000000'
	        )
	    )
	));

	$estilo_centrar = array(
	    'alignment' =>  array(
	        'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        'wrap'      => false
	    )
	);

	// SIRVE PARA PONER ALTURA A LA FILA, EN ESTE CASO A LA FILA 1.
	$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(63);

	$objPHPExcel->getActiveSheet()->getStyle('A1:O1')->applyFromArray($estiloNombresColumnas);
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:L".($i-1));
	$objPHPExcel->getActiveSheet()->getStyle('A1:O'.($i-1))->applyFromArray($estilo_centrar);

	$objPHPExcel->getActiveSheet()->getStyle('J3:J'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');

	$objPHPExcel->getActiveSheet()->getStyle('M3:M'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');

	// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
	for($i = 'A'; $i <= 'Z'; $i++)
	{
	    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}

	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('Servicios Públicos');
	  
	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);
	
	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Servicios Públicos Tesorería.xls" ');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$excel_path = '/var/www/html/files_bucket/contratos/servicios_publicos/reportes/tesoreria/Servicios Públicos Tesorería.xls';
	$excel_path_download = '/files_bucket/contratos/servicios_publicos/reportes/tesoreria/Servicios Públicos Tesorería.xls';

	try 
	{
		$objWriter->save($excel_path);
	} 
	catch (Exception $e)
	{
		echo json_encode(["error" => $e]);
		exit;
	}

	echo json_encode(array(
		"ruta_archivo" => $excel_path_download
	));
	exit;
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_listado_compromisos_de_pago") {
	$query = "SELECT id, nombre FROM cont_tipo_pago_servicio where estado = 1";
	$result=array();
	$list_query = $mysqli->query($query);
	$list_datos_compromisos = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_datos_compromisos[] = $li;
	}
	
	if ($mysqli->error) {
		$result["error"] = $mysqli->error;
	}
	if (count($list_datos_compromisos) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
	} elseif (count($list_datos_compromisos) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "OK";
		$result["result"] = $list_datos_compromisos;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los registros.";
	}
	echo json_encode($result);
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_datos_recibo") {
	$id_recibo = $_POST["id_recibo"];
	$id_local = $_POST["id_local"];
	$periodo = $_POST["periodo"];
	$id_tipo_recibo = $_POST["id_tipo_recibo"];

	$where_id_recibo = "";
	$where = "";

	if($id_recibo == 0){
		$anio = substr($periodo, 0 , 4);
		$mes = substr($periodo, 5, 2);	
		$where = " and month(sp.periodo_consumo) = " . $mes 
				. " and year(sp.periodo_consumo) = " . $anio 
				. " and sp.id_local = " . $id_local
				. " and sp.id_tipo_servicio_publico = " . $id_tipo_recibo;
	}else{
		$where_id_recibo = " and sp.id = " . $id_recibo;
	}
	$query = 
	"
		SELECT 
			sp.id, sp.id_local, l.nombre as local, l.nombre local_nombre, 
			IFNULL(sp.serie,'') AS serie,
			IFNULL(sp.fecha_emision, '') fecha_emision, 
			IFNULL(sp.fecha_vencimiento, '') fecha_vencimiento,
			sp.id_tipo_servicio_publico, 
			IFNULL(sp.monto_total, 0) monto_total,
			IFNULL(sp.total_pagar, 0) total_pagar,
			sp.periodo_consumo, 
			IFNULL(sf.download, '') ruta_download_file,
			IFNULL(sf.file, '') nombre_file,
			IFNULL(sf.extension, '') extension,
			IFNULL(sfc.download, '') ruta_download_file_contometro,
			IFNULL(sfc.file, '') nombre_file_contometro,
			IFNULL(sfc.extension, '') extension_contometro,
			s.nro_suministro AS numero_suministro,
			s.tipo_compromiso_pago_id AS tipo_compromiso,
			ctp.nombre AS tipo_compromiso_nombre,
			s.monto_o_porcentaje AS monto_pct,
			IFNULL(sp.estado, 1) estado_recibo,
			IFNULL(sp.numero_recibo, '') numero_recibo,
			IFNULL(sp.aplica_caja_chica, 0) aplica_caja_chica,
			IFNULL(sp.nombre_paga_caja_chica, '') nombre_paga_caja_chica,
			sp.comentario
		FROM cont_local_servicio_publico sp
			INNER JOIN tbl_locales l on sp.id_local = l.id
			LEFT JOIN cont_contrato c on l.contrato_id = c.contrato_id
			LEFT JOIN cont_inmueble i on i.contrato_id = c.contrato_id
			INNER JOIN cont_inmueble_suministros s
			ON s.id = sp.inmueble_suministros_id
			INNER JOIN cont_local_servicio_publico_files sf
            ON sf.cont_local_servicio_publico_id = sp.id AND sf.cont_tipo_servicio_publico_id = sp.id_tipo_servicio_publico
            LEFT JOIN cont_local_servicio_publico_files sfc
			ON sfc.cont_local_servicio_publico_id = sp.id AND sfc.cont_tipo_servicio_publico_id = 3
			INNER JOIN cont_tipo_pago_servicio ctp 
            ON s.tipo_compromiso_pago_id = ctp.id
		where sp.id > 0
			".$where_id_recibo."
			".$where."
		ORDER BY sp.id DESC 
		LIMIT 1
	";

	$result=array();
	$list_query = $mysqli->query($query);
	$list_datos_recibo = array();
	while ($li = $list_query->fetch_assoc())
	{
		if($li['fecha_emision']!='') $li['fecha_emision'] = date("d-m-Y", strtotime($li['fecha_emision']));  
		
		if($li['fecha_vencimiento']!='') $li['fecha_vencimiento'] = date("d-m-Y", strtotime($li['fecha_vencimiento']));  
		
		$li['periodo_consumo'] = date("Y-m", strtotime($li['periodo_consumo']));  
		$list_datos_recibo[] = $li;
	}
	
	if ($mysqli->error) {
		$result["error"] = $mysqli->error;
	}
	if (count($list_datos_recibo) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
	} elseif (count($list_datos_recibo) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "OK";
		$result["result"] = $list_datos_recibo;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los registros.";
	}
	echo json_encode($result);
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_montos_meses_anteriores") {
	$id_local = $_POST["local_id"];
	$id_tipo_recibo = $_POST["id_tipo_recibo"];
	$periodo_consumo_rr = $_POST["periodo_consumo_rr"];
	$montos_rr = array();
	
	foreach ($periodo_consumo_rr as $p)
	{
		$monto = 0;
		$anio = substr($p, 0 , 4);
		$mes = substr($p, 5, 2);

		$query = "SELECT distinct ls.periodo_consumo, 
				ifnull((select distinct monto_total from cont_local_servicio_publico 
				where id_tipo_servicio_publico = 2 and periodo_consumo = ls.periodo_consumo and id_local = ls.id_local limit 1),0) monto_total_agua,
				ifnull((select distinct monto_total from cont_local_servicio_publico 
				where id_tipo_servicio_publico = 1 and periodo_consumo = ls.periodo_consumo and id_local = ls.id_local limit 1),0) monto_total_luz
				from cont_local_servicio_publico  ls
				where month(ls.periodo_consumo) = " . $mes . " and year(ls.periodo_consumo) = " . $anio . " and ls.id_local = " . $id_local . " ORDER BY ls.id DESC LIMIT 1";

		$result=array();
		$list_query = $mysqli->query($query);

		while ($li = $list_query->fetch_assoc()) {
			$monto = $li;
		}
		if($monto == 0){
			$arr = array("periodo_consumo" => $anio . "-" . $mes . "-01", "monto_total_agua" => "0.00", "monto_total_luz" => "0.00");
			array_push($montos_rr, $arr);
		}else{
			array_push($montos_rr, $monto);
		}

		if ($mysqli->error) {
			$result["error"] = $mysqli->error;
		}
	}
	
	if (count($montos_rr) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
	} elseif (count($montos_rr) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "OK";
		$result["result"] = $montos_rr;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los registros.";
	}
	echo json_encode($result);
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_registros_locales_sin_recibo") {
	$id_local = $_POST["local_id"];
	$id_jefe_comercial = $_POST["id_jefe_comercial"];
	$id_supervisor = $_POST["id_supervisor"];
	$periodo = $_POST["periodo"];

	$where_periodo = "";
	$where_local = "";
	$where_jefe_comercial = "";
	$where_supervisor = "";

	
	if($periodo != 0){
		$anio = substr($periodo, 0 , 4);
		$mes = substr($periodo, 5, 2);	
		$where_periodo = " AND month(sp.periodo_consumo) = " . $mes . " and year(sp.periodo_consumo) = " . $anio;
	}

	if($id_local != 0){
		$where_local = " AND l.id = " . $id_local;
	}

	if($id_jefe_comercial != 0){
		$where_jefe_comercial = " AND tp.id = " . $id_jefe_comercial;
	}

	if($id_supervisor != 0){
		$where_supervisor = " AND tpp.id = " . $id_supervisor;
	}


	$query = "SELECT distinct
			l.id AS local_id,concat(ifnull(l.cc_id,'0'),' - ', l.nombre) AS local, l.nombre local_nombre,'' periodo_consumo, 
			0 as recibo_luz, 0 as id_recibo_luz, '' as fec_vcto_recibo_luz, '' as estado_recibo_luz,
			0 as recibo_agua, 0 as id_recibo_agua, '' as fec_vcto_recibo_agua, '' as estado_recibo_agua,
			IFNULL((SELECT
			concat( IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS supervisor
			FROM tbl_usuarios_locales tuls
			INNER JOIN tbl_usuarios tus ON tuls.usuario_id = tus.id AND tus.grupo_id = 10
			INNER JOIN tbl_personal_apt tp ON tp.id = tus.personal_id AND tp.area_id = 21 AND tp.cargo_id = 16
			where tuls.local_id = l.id AND tuls.estado = 1 LIMIT 1), '') AS jefe_comercial,
			IFNULL((SELECT
			concat( IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS supervisor
			FROM tbl_usuarios_locales tuls
			INNER JOIN tbl_usuarios tus ON tuls.usuario_id = tus.id -- AND tus.grupo_id = 12
			INNER JOIN tbl_personal_apt tp ON tp.id = tus.personal_id AND tp.area_id = 21 AND tp.cargo_id = 4 
			where tuls.local_id = l.id LIMIT 1),'') AS supervisor
			FROM tbl_locales l
			WHERE l.id not in (
					SELECT id_local 
					FROM cont_local_servicio_publico sp
					where status = 1 " . $where_periodo . ")
				AND l.estado = 1  and l.contrato_id > 0 " . $where_local . "
			GROUP BY l.id";
	$result=array();
	$list_query = $mysqli->query($query);

	$list_proc_registros = array();
	while ($li = $list_query->fetch_assoc()) {
		$list_proc_registros[] = $li;
	}
	
	if ($mysqli->error) {
		$result["error"] = $mysqli->error;
	}
	if (count($list_proc_registros) == 0) {
		$result["http_code"] = 400;
		$result["status"] = "No hay registros.";
	} elseif (count($list_proc_registros) > 0) {
		$result["http_code"] = 200;
		$result["status"] = "OK";
		$result["result"] = $list_proc_registros;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los registros.";
	}
	echo json_encode($result);
}


if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_observaciones_servicio_publico") {

	$id_recibo = $_POST["id_recibo"];

	$query = "SELECT o.id, o.id_recibo, o.observacion, o.created_at, IFNULL(p.nombre,'')  AS nombre, 
	IFNULL(p.apellido_paterno,'') AS apellido_paterno, IFNULL(p.apellido_materno,'') AS apellido_materno,	
	ar.nombre AS area
	FROM cont_local_servicio_publico_observaciones AS o
	INNER JOIN tbl_usuarios u ON o.user_created_id = u.id
	INNER JOIN tbl_personal_apt p ON p.id = u.personal_id
	INNER JOIN tbl_areas ar ON p.area_id = ar.id
	WHERE o.id_recibo = ".$id_recibo."
	AND o.status = 1
	ORDER BY o.created_at ASC";
	$result=array();
	$list_query = $mysqli->query($query);

	$html = "";
	while ($li = $list_query->fetch_assoc()) {
		$li['created_at'] = date("d-m-Y H:i", strtotime($li['created_at']));  
		
		$html .= '<div class="col-sm-offset-1 col-sm-11 caja_usuario_aprobacion alert alert-success" style="padding-bottom:10px;color:#333;box-shadow: 4px 4px 4px rgba(0,0,0,0.15);">';
		$html .= '<div class="col-sm-8" style="margin-bottom:5px;">';
		$html .= '<strong>'.$li['nombre'].' '.$li['apellido_paterno'].' '.$li['apellido_materno'].'('.$li['area'].')</strong>';
		$html .= '</div>';
		$html .= '<div class="col-sm-4 text-right" style="color:rgba(0,0,0,0.5); font-size: 11px;">';
		$html .= '<span class="time"><i class="fa fa-clock-o"></i> '.$li['created_at'].'</span>';
		$html .= '</div>';
		$html .= '<div class="col-xs-12" style="padding-top: 5px; background:rgba(255,255,255,0.7); margin-bottom:2px;">'.$li['observacion'].'</div>';
		$html .= '</div>';
	}
	$result["error"] = "";
	if ($mysqli->error) {
		$result["error"] = $mysqli->error;
	}

	if ($result["error"] == "") {
		$result["http_code"] = 200;
		$result["status"] = "OK";
		$result["result"] = $html;
	} else {
		$result["http_code"] = 400;
		$result["status"] = "Ocurrió un error al consultar los registros.";
	}
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_correos_observacion") {
    $result = [
        "http_code" => 200,
        "result" => "",
        "observacion" => "",
        "correos" => "", 
    ];

    $id_archivo = isset($_POST['id_archivo']) ? intval($_POST['id_archivo']) : 0;

    if ($id_archivo > 0) {
        $query_correos = "SELECT spc.correo
            FROM cont_local_servicio_publico AS sp
            INNER JOIN cont_local_servicio_publico_correos AS spc ON spc.id_recibo = sp.id
            WHERE sp.id = ? AND spc.status = 1";

        $stmt_correos = $mysqli->prepare($query_correos);
        $stmt_correos->bind_param("i", $id_archivo);
        $stmt_correos->execute();
        $stmt_correos->store_result();

        if ($stmt_correos->num_rows > 0) {
            $stmt_correos->bind_result($correo);
            while ($stmt_correos->fetch()) {
                $result["correos"] .= $correo . ",";
            }
            $result["correos"] = rtrim($result["correos"], ",");
        }
        $stmt_correos->close();

        $query_observacion = "SELECT spo.observacion
            FROM cont_local_servicio_publico AS sp
            INNER JOIN cont_local_servicio_publico_observaciones spo ON spo.id_recibo = sp.id
            WHERE sp.id = ? AND spo.status = 1";

        $stmt_observacion = $mysqli->prepare($query_observacion);
        $stmt_observacion->bind_param("i", $id_archivo);
        $stmt_observacion->execute();
        $stmt_observacion->store_result();

        if ($stmt_observacion->num_rows > 0) {
            $stmt_observacion->bind_result($observacion);
            $stmt_observacion->fetch();
            $result["observacion"] = $observacion;
        }
        $stmt_observacion->close();
    }

    echo json_encode($result);
}

if (isset($_POST["accion"]) && $_POST["accion"]==="verificar_num_recibo")
{
	$num_recibo = $_POST["num_recibo"];
	$id_archivo = $_POST["id_archivo"];

	// Consultar si el número de recibo ya fue registrado
	$selectQuery = "SELECT 
						IFNULL(sp.numero_recibo, '') numero_recibo,
						sp.periodo_consumo, 
						u.usuario
					FROM cont_local_servicio_publico sp
					INNER JOIN tbl_usuarios u ON sp.user_updated_id = u.id
					WHERE sp.id != ? AND sp.numero_recibo = ? AND sp.status = 1 ORDER BY sp.id DESC LIMIT 1";

	$selectStmt = $mysqli->prepare($selectQuery);
	$selectStmt->bind_param("is", $id_archivo, $num_recibo);
	$selectStmt->execute();
	$selectStmt->store_result();

	if ($selectStmt->num_rows > 0) {
		$selectStmt->bind_result($numero_recibo, $periodo_consumo, $usuario);
		$selectStmt->fetch();

		if ($num_recibo == '')
			{
				$result["http_code"] = 400;
				$result["titulo"] = "Alerta";
				$result["descripcion"] = "No se encontraron registros";
			}
		else
			{
				setlocale(LC_TIME, 'es_ES.utf8');
				$result["http_code"] = 200;
				$result["titulo"] = "Código de recibo nro: ". $numero_recibo ." ya fue registrado en el periodo: ". strftime('%B-%Y', strtotime($periodo_consumo))." por el usuario:". $usuario;
			}
	}else{
		$result["http_code"] = 400;
		$result["titulo"] = "Alerta";
		$result["descripcion"] = "No se encontraron registros";
	}
	
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_razones_sociales") {
	include("db_connect.php");
	include("sys_login.php");

    try {

		// Permiso de redes de locales
			$login_usuario_id = $login?$login['id']:null;
			$where_redes = "";

			$select_red =
			"
				SELECT
					l.red_id
				FROM tbl_usuarios_locales ul
					INNER JOIN tbl_locales l
					ON ul.local_id = l.id
				WHERE ul.usuario_id = '".$login_usuario_id."'
					AND ul.estado = 1 AND l.red_id IS NOT NULL
				GROUP BY l.red_id
			";
		
			$data_select_red = $mysqli->query($select_red);
		
			$row_count_data_select_red = $data_select_red->num_rows;
		
			$ids_data_select_red = '';
			$contador_ids = 0;
			
			if ($row_count_data_select_red > 0) 
			{
				while ($row = $data_select_red->fetch_assoc()) 
				{
					if ($contador_ids > 0) 
					{
						$ids_data_select_red .= ',';
					}
		
					$ids_data_select_red .= $row["red_id"];           
					$contador_ids++;
				}
		
				$where_redes = " AND red_id IN ($ids_data_select_red) ";
			}
		//

		$query = "SELECT 
                    id, 
                    nombre AS nombre
                FROM tbl_razon_social 
                WHERE status = '1' AND nombre IS NOT NULL AND permiso_servicios_publicos=1
                $where_redes
                ORDER BY nombre ASC";

        $stmt = $mysqli->prepare($query);

        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $mysqli->error);
        }

        $stmt->bind_param();
        $stmt->execute();

        $list_query = $stmt->get_result();
        $list = $list_query->fetch_all(MYSQLI_ASSOC);

        $stmt->close();

        if (count($list) === 0) {
            $result["http_code"] = 400;
            $result["result"] = "El concepto no existe.";
        } else {
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestion.";
            $result["result"] = $list;
        }

        echo json_encode($result);
    } catch (Exception $e) {
        // Registra el error en un archivo de registro o en otro lugar seguro
        $result["consulta_error"] = $e->getMessage();
        $result["http_code"] = 500;
        $result["result"] = "Error en la consulta. Comunicarse con Soporte.";

        echo json_encode($result);
    }
    exit();
}
?>