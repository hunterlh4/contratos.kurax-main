<?php 
include("db_connect.php");
include("sys_login.php");ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_contratos_internos") {
	$empresa_1 = $_POST['empresa_1'];
	$empresa_2 = $_POST['empresa_2'];
	$area = $_POST['area'];
	$fecha_inicio_solicitud = $_POST['fecha_inicio_solicitud'];
	$fecha_fin_solicitud = $_POST['fecha_fin_solicitud'];
	$fecha_inicio_inicio = $_POST['fecha_inicio_inicio'];
	$fecha_fin_inicio = $_POST['fecha_fin_inicio'];
	
	$fecha_inicio_aprobacion = $_POST['search_fecha_inicio_aprobacion_firmado'];
	$fecha_fin_aprobacion = $_POST['search_fecha_fin_aprobacion_firmado'];

	$director_aprobacion_id = trim($_POST['aprobante']);

	$where_empresa_1 = "";
	$where_empresa_2 = "";
	$where_area = "";
	$where_fecha_solicitud = "";
	$where_fecha_inicio = "";

	$where_director_aprobacion	=	"";
	$where_estado_aprobacion	=	"";
	if (!Empty($empresa_1)) {
		$where_empresa_1 = " AND c.empresa_suscribe_id = ".$empresa_1;
	}
	if (!Empty($empresa_2)) {
		$where_empresa_2 = " AND c.empresa_grupo_at_2 = ".$empresa_2;
	}
	if (!Empty($area)) {
		$where_area = " AND ar.id = ".$area;
	}

	if (!Empty($fecha_inicio_solicitud) && !Empty($fecha_fin_solicitud)) {
		$where_fecha_solicitud = " AND c.created_at BETWEEN '$fecha_inicio_solicitud 00:00:00' AND '$fecha_fin_solicitud 23:59:59'";
	} elseif (!Empty($fecha_inicio_solicitud)) {
		$where_fecha_solicitud = " AND c.created_at >= '$fecha_inicio_solicitud 00:00:00'";
	} elseif (!Empty($fecha_fin_solicitud)) {
		$where_fecha_solicitud = " AND c.created_at <= '$fecha_fin_solicitud 23:59:59'";
	}

	if (!Empty($fecha_inicio_inicio) && !Empty($fecha_fin_inicio)) {
		$where_fecha_inicio = " AND c.fecha_inicio BETWEEN '$fecha_inicio_inicio 00:00:00' AND '$fecha_fin_inicio 23:59:59'";
	} elseif (!Empty($fecha_inicio_inicio)) {
		$where_fecha_inicio = " AND c.fecha_inicio >= '$fecha_inicio_inicio 00:00:00'";
	} elseif (!Empty($fecha_fin_inicio)) {
		$where_fecha_inicio = " AND c.fecha_inicio <= '$fecha_fin_inicio 23:59:59'";
	}

	$where_fecha_aprobacion = '';
	if (!Empty($fecha_inicio_aprobacion) && !Empty($fecha_fin_aprobacion)) {
		$where_fecha_aprobacion = " AND c.fecha_atencion_gerencia_interno between '$fecha_inicio_aprobacion 00:00:00' AND '$fecha_fin_aprobacion 23:59:59'";
	} elseif (!Empty($fecha_inicio_aprobacion)) {
		$where_fecha_aprobacion = " AND c.fecha_atencion_gerencia_interno >= '$fecha_inicio_aprobacion 00:00:00'";
	} elseif (!Empty($fecha_fin_aprobacion)) {
		$where_fecha_aprobacion = " AND c.fecha_atencion_gerencia_interno <= '$fecha_fin_aprobacion 23:59:59'";
	}

	if (!Empty($director_aprobacion_id))
	{
		$where_director_aprobacion = " AND ( ( c.director_aprobacion_id = ".$director_aprobacion_id." AND (c.check_gerencia_interno =1 and c.fecha_atencion_gerencia_interno IS NULL AND c.aprobacion_gerencia_interno=0) ) OR c.aprobado_por = ".$director_aprobacion_id." ) ";
	}

	 
	$query = "
	SELECT
		c.contrato_id, 
		c.empresa_suscribe_id,
		rs1.nombre AS empresa_at1,
		rs2.nombre AS empresa_at2,
		c.detalle_servicio,
		CONCAT(c.periodo_numero, ' ', p.nombre) AS periodo,
		concat(IFNULL(per.nombre, ''),' ', IFNULL(per.apellido_paterno, '')) AS usuario_creacion,
		per.correo AS usuario_creacion_correo,

		(CASE WHEN (c.check_gerencia_interno =1 and c.fecha_atencion_gerencia_interno IS NOT NULL AND c.aprobacion_gerencia_interno=1) THEN 'Aprobado' ELSE '' END) as aprobS, 

		CONCAT(IFNULL(pud.nombre, ''),' ',IFNULL(pud.apellido_paterno, ''),' ',IFNULL(pud.apellido_materno, '')) AS nombre_del_director_a_aprobar,

		c.fecha_inicio,
		ar.nombre AS area_creacion,
		c.check_gerencia_interno,
		c.fecha_atencion_gerencia_interno,
		c.aprobacion_gerencia_interno,
		co.sigla AS sigla_correlativo,
		c.codigo_correlativo,
		c.observaciones,
		c.created_at,
		c.estado_resolucion,
		c.fecha_vencimiento_indefinida_id,
		c.fecha_vencimiento_proveedor
	FROM 
		cont_contrato c
		INNER JOIN cont_periodo p ON c.periodo = p.id
		INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
		INNER JOIN tbl_personal_apt per ON u.personal_id = per.id
		INNER JOIN tbl_areas ar ON per.area_id = ar.id
		INNER JOIN tbl_razon_social rs1 ON c.empresa_suscribe_id = rs1.id 
		INNER JOIN tbl_razon_social rs2 ON c.empresa_grupo_at_2 = rs2.id 
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato

		LEFT JOIN tbl_usuarios ud ON c.aprobado_por = ud.id
		LEFT JOIN tbl_personal_apt pud ON ud.personal_id = pud.id
	WHERE 
		c.status = 1 
		AND c.etapa_id = 5 
		AND c.tipo_contrato_id = 7 
		$where_empresa_1
		$where_empresa_2
		$where_area
		$where_fecha_solicitud
		$where_fecha_inicio
		$where_fecha_aprobacion
		$where_director_aprobacion
	ORDER BY c.contrato_id DESC
	";

	$table = '
	<table id="cont_interno_datatable" class="table table-bordered table-hover table-condensed" cellspacing="0" width="100%">
		<thead>
			<tr>
				<th class="text-center">Cod</th>
				<th class="text-center">Area Solicitante</th>
				<th class="text-center">Solicitante</th>
				<th class="text-center">Empresa Grupo AT 1</th>
				<th class="text-center">Empresa Grupo AT 2</th>
				<th class="text-center">Fecha Solicitud</th>
				<th class="text-center">Fecha Inicio</th>
				<th class="text-center">Fecha Fin</th>
				<th class="text-center">Estado <br> Contractual</th>
				<th class="text-center">Aprobante</th>
				<th class="text-center">F. Aprobación</th>
				<th class="text-center"></th>
			</tr>
		</thead>
		<tbody>';
	$list_query = $mysqli->query($query);
	while ($li = $list_query->fetch_assoc()) {

		$fecha_vencimiento_proveedor = '';
		$estado_contractual = '';

		if ((int) $li['fecha_vencimiento_indefinida_id'] == 1) {
			$fecha_vencimiento_proveedor = 'Indefinida';
			$estado_contractual = 'Vigente';
		} else {

			$fechaObj1 = new DateTime($li['fecha_vencimiento_proveedor']);
			$fechaObj2 = new DateTime(date('Y-m-d'));

			if($li['estado_resolucion'] == 2){
				$estado_contractual = 'Resuelto';
			}else{
				if ($fechaObj1 > $fechaObj2) {
					$estado_contractual = 'Vigente';
				}else{
					$estado_contractual = 'Vencido';
				}
			}
			$fecha_vencimiento_proveedor = $li['fecha_vencimiento_proveedor'];
		}

		$fecha = new DateTime($li['fecha_inicio']);
		$fecha_convertido = $fecha->format('Y-m-d');

		$fecha_aprob = new DateTime($li['fecha_atencion_gerencia_interno']);
		$fecha_convertido_aprob = $fecha_aprob->format('Y-m-d');
		$table .= '<tr>
					<td class="text-center">'.$li['sigla_correlativo'].$li['codigo_correlativo'].'</td>
					<td class="text-left">'.$li['area_creacion'].'</td>
					<td class="text-left">'.$li['usuario_creacion'].'</td>
					<td class="text-left">'.$li['empresa_at1'].'</td>
					<td class="text-left">'.$li['empresa_at2'].'</td>
					<td class="text-left">'.$li['created_at'].'</td>
					<td class="text-center">'.$fecha_convertido.'</td>
					<td class="text-center">'.$fecha_vencimiento_proveedor.'</td>
					<td class="text-center">'.$estado_contractual.'</td>
					<td class="text-center">'.$li['nombre_del_director_a_aprobar'].'</td>
					<td class="text-center">'.$fecha_convertido_aprob.'</td>
					<td class="text-center">
						<a class="btn btn-rounded btn-primary btn-sm" href="./?sec_id=contrato&amp;sub_sec_id=detalle_solicitud_interno&amp;id='.$li['contrato_id'].'" title="Ver detalle">
							<i class="fa fa-eye"></i>												
						</a>
					</td>
				</tr>';
	}
	$table .= '
		</tbody>
		<tfoot>
			<tr>
				<th class="text-center">Cod</th>
				<th class="text-center">Area Solicitante</th>
				<th class="text-center">Solicitante</th>
				<th class="text-center">Empresa Grupo AT 1</th>
				<th class="text-center">Empresa Grupo AT 2</th>
				<th class="text-center">Fecha Solicitud</th>
				<th class="text-center">Fecha Inicio</th>
				<th class="text-center">Fecha Fin</th>
				<th class="text-center">Estado <br> Contractual</th>
				<th class="text-center">Aprobante</th>
				<th class="text-center">F. Aprobación</th>
				<th class="text-center"></th>
			</tr>
		</tfoot>
	</table>';

	
	$result["status"] = 200;
	$result["message"] = "";
	$result["result"] = $table;
	echo json_encode($result);
}


?>