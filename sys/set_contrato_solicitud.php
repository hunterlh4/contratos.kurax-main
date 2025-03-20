<?php
date_default_timezone_set("America/Lima");

$result = array();
include("db_connect.php");
include("sys_login.php");

include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';
require_once '/var/www/html/sys/helpers.php';

require_once '/var/www/html/env.php';
include '/var/www/html/sys/envio_correos.php';
include '/var/www/html/sys/function_replace_invalid_caracters_contratos.php';
include '/var/www/html/sys/set_contrato_seguimiento_proceso.php';

include '/var/www/html/sys/Pagination.php';

$area_id_login = $login["area_id"];
// var_dump( $area_id_login );
// var_dump( $login );
$usuario_id_login = $login ? $login['id'] : null;

if ($_POST['action'] == "listar_contrato_arredamiento") {

	// datos de sesion
	$menu_consultar = $_POST['menu_consultar'];
	$area_id = $_POST['area_id'];
	$cargo_id = $_POST['cargo_id'];
	$menu_id = $_POST['menu_id'];
	$sec_id = $_POST['sec_id'];

	$empresa = $_POST['empresa'];
	$area_solicitante = $_POST['area'];
	$ruc = $_POST['ruc'];
	$razon_social = $_POST['razon_social'];
	$moneda = $_POST['moneda'];
	$fecha_inicio = trim($_POST['fecha_inicio']);
	$fecha_fin = trim($_POST['fecha_fin']);
	$nombre_tienda = trim($_POST['nombre_tienda']);
	$estado_sol_v2 = trim($_POST['estado_sol_v2']);

	$estado_sol = '';
	if (isset($_POST["estado_sol"])) {
		$estado_sol = $_POST["estado_sol"];
	}

	$id_departamento = $_POST['id_departamento'];
	$id_provincia = $_POST['id_provincia'];
	$id_distrito = $_POST['id_distrito'];


	$where_empresa = "";
	$where_area_solicitante = "";
	$where_ruc = "";
	$where_razon_social = "";
	$where_moneda = "";
	$where_fecha = '';
	$where_ubigeo = '';
	$where_nombre_tienda = '';
	$where_estado_sol = '';
	$where_estado_sol_v2 = '';
	$where_estado_cancelado = "";

	if (!empty($empresa)) {
		$where_empresa = " AND c.empresa_suscribe_id = '" . $empresa . "' ";
	}

	if (!empty($area_solicitante)) {
		$where_area_solicitante = " AND a.id = '" . $area_solicitante . "' ";
	}

	if (!empty($ruc)) {
		$where_ruc = " AND c.ruc LIKE '%" . $ruc . "%' ";
	}

	if (!empty($razon_social)) {
		$where_razon_social = " AND c.razon_social  LIKE '%" . $razon_social . "%' ";
	}

	if (!empty($moneda)) {
		$where_moneda = " AND ce.tipo_moneda_id = '" . $moneda . "'";
	}

	if (!empty($fecha_inicio) && !empty($fecha_fin)) {
		$where_fecha = " AND c.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
	} elseif (!empty($fecha_inicio)) {
		$where_fecha = " AND c.created_at >= '$fecha_inicio 00:00:00'";
	} elseif (!empty($fecha_fin)) {
		$where_fecha = " AND c.created_at <= '$fecha_fin 23:59:59'";
	}

	if (!empty($id_departamento)) {
		$where_ubigeo = " AND i.ubigeo_id LIKE '%" . $id_departamento . $id_provincia . $id_distrito . "%'";
	}

	if (!empty($nombre_tienda)) {
		$where_nombre_tienda = " AND c.nombre_tienda LIKE '%" . $nombre_tienda . "%' ";
	}

	if (!empty($estado_sol)) {
		if ((int) $estado_sol === 99) {
			$where_estado_sol_v2 = " AND c.cancelado_id = 1 ";
		} elseif ((int) $estado_sol === 1) {
			$where_estado_sol = " AND ( c.estado_solicitud = 1 OR c.estado_solicitud IS NULL ) ";
		} else {
			$where_estado_sol = " AND c.estado_solicitud = '" . $estado_sol . "' ";
		}
	}

	if ($login["area_id"] == 33) {
		$where_estado_cancelado = " AND ( c.cancelado_id != 1 OR c.cancelado_id IS NULL )";
	}

	if (!empty($estado_sol_v2)) {
		if ((int) $estado_sol_v2 === 1) {
			$where_estado_sol_v2 = " AND ( c.cancelado_id != 1 || c.cancelado_id IS NULL )";
		} elseif ((int) $estado_sol_v2 === 2) {
			$where_estado_sol_v2 = " AND c.cancelado_id = 1 ";
		}
	}


	$total_query = $mysqli->query("
		SELECT COUNT(c.contrato_id) as num_rows 
        FROM 
			cont_contrato c
			INNER JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id AND ce.status = 1 
			INNER JOIN cont_inmueble i ON c.contrato_id = i.contrato_id AND ce.contrato_detalle_id = i.contrato_detalle_id
			INNER JOIN cont_etapa e ON c.etapa_id = e.etapa_id
			INNER JOIN tbl_areas a ON e.area_id = a.id
			LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			LEFT JOIN cont_estado_solicitud es ON es.id = c.estado_solicitud
		WHERE 
			c.tipo_contrato_id = 1 
			AND c.status = 1 
			AND e.etapa_id <> 5
           " . $where_empresa . "
			" . $where_area_solicitante . "
			" . $where_ruc . "
			" . $where_razon_social . "
			" . $where_moneda . "
			" . $where_fecha . "
			" . $where_ubigeo . "
			" . $where_nombre_tienda . "
			" . $where_estado_sol . "
			" . $where_estado_sol_v2 . "
			" . $where_estado_cancelado . "
		");

	$resultNum = $total_query->fetch_assoc();
	$num_rows = $resultNum['num_rows'];
	$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
	$per_page = 10;
	$numLinks = 10;
	$total_paginate = ceil($num_rows / $per_page);
	$total_paginate = $total_paginate == 0 ? 1 : $total_paginate;
	$page = $page > $total_paginate ? $total_paginate : $page;
	$offset = ($page - 1) * $per_page;
	$paginate = new Pagination('sec_contratos_solicitud_cambiar_de_pagina', $num_rows, $per_page, $numLinks, $page);

?>

	<?php
	$query = "
		SELECT 
			c.contrato_id, 
			c.nombre_tienda, 
			c.cc_id,
			i.ubicacion, 
			i.num_partida_registral, 
			c.created_at AS fecha_solicitud, 
			ce.fecha_suscripcion,
			e.etapa_id, 
			e.nombre AS etapa, 
			e.descripcion AS etapa_descripcion, 
			e.situacion AS etapa_situacion, 
			a.nombre AS area_encargada, 
			c.verificar_giro, 
			co.sigla AS sigla_correlativo, 
			c.codigo_correlativo,
			c.estado_solicitud,
			es.nombre AS nombre_estado_solicitud,
			c.cancelado_id,
			c.fecha_cambio_estado_solicitud,
			c.dias_habiles,
			(CASE WHEN (c.fecha_aprobacion IS NULL AND c.estado_aprobacion=0) THEN 'Pendiente' ELSE '' END) as aprob, 	
			(CASE WHEN (c.fecha_aprobacion IS NOT NULL AND c.estado_aprobacion=1) THEN 'Aprobado' ELSE '' END) as aprobS, 
			(CASE WHEN (c.fecha_aprobacion IS NOT NULL AND c.estado_aprobacion=0) THEN 'Denegado' ELSE '' END) as aprobN, 
			CONCAT(IFNULL(pud.nombre, ''),' ',IFNULL(pud.apellido_paterno, ''),' ',IFNULL(pud.apellido_materno, '')) AS nombre_del_director_a_aprobar,
			CONCAT(IFNULL(puap.nombre, ''),' ',IFNULL(puap.apellido_paterno, ''),' ',IFNULL(puap.apellido_materno, '')) AS nombre_del_aprobador,
			c.aprobador_id,
			c.aprobado_por,
			c.fecha_aprobacion,
			CONCAT(IFNULL(pa.nombre, ''),' ',IFNULL(pa.apellido_paterno, ''),' ',IFNULL(pa.apellido_materno, '')) AS abogado,
			p.area_id as usuario_s
		FROM 
			cont_contrato c
			INNER JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id AND ce.status = 1
			INNER JOIN cont_inmueble i ON c.contrato_id = i.contrato_id AND ce.contrato_detalle_id = i.contrato_detalle_id
			INNER JOIN cont_etapa e ON c.etapa_id = e.etapa_id
			INNER JOIN tbl_areas a ON e.area_id = a.id
			LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			LEFT JOIN cont_estado_solicitud es ON es.id = c.estado_solicitud
			LEFT JOIN tbl_usuarios ud ON c.aprobador_id = ud.id
			LEFT JOIN tbl_personal_apt pud ON ud.personal_id = pud.id
			LEFT JOIN tbl_usuarios uap ON c.aprobado_por = uap.id
			LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id
			LEFT JOIN tbl_usuarios ua ON c.abogado_id = ua.id
			LEFT JOIN tbl_personal_apt pa ON ua.personal_id = pa.id
			INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
			INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		WHERE 
			c.tipo_contrato_id = 1 
			AND c.status = 1 
			AND e.etapa_id <> 5
			" . $where_empresa . "
			" . $where_area_solicitante . "
			" . $where_ruc . "
			" . $where_razon_social . "
			" . $where_moneda . "
			" . $where_fecha . "
			" . $where_ubigeo . "
			" . $where_nombre_tienda . "
			" . $where_estado_sol . "
			" . $where_estado_sol_v2 . "
			" . $where_estado_cancelado . "
			ORDER BY c.created_at DESC

		";
	$sel_query = $mysqli->query($query);
	?>

	<div class="w-100 text-center">
		<h5 style="font-weight: bold; margin: 0px;">SOLICITUDES DE CONTRATO DE ARRENDAMIENTOS</h5>
	</div>
	<br>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed table-bordered tabla_contratos_para_filtro">
			<thead>
				<tr>
					<th class="text-center">Código</th>
					<th class="text-center">Tienda</th>
					<th class="text-center">Centro de Costo</th>
					<th class="text-center">Ubicación del Inmueble</th>
					<th class="text-center">F. Solicitud</th>
					<th class="text-center">F. Suscripción</th>
					<th class="text-center">Giro</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Abogado</th>
					<?php } ?>
					<th class="text-center">Estado</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Fecha de atención</th>
						<th class="text-center">Días de atención</th>
					<?php } ?>
					<th class="text-center">Estado Aprobación</th>
					<th class="text-center">Aprobante</th>
					<th class="text-center">F. Aprobación</th>
					<th class="text-center">Ver Detalle</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th class="text-center">Código</th>
					<th class="text-center">Tienda</th>
					<th class="text-center">Centro de Costo</th>
					<th class="text-center">Ubicación del Inmueble</th>
					<th class="text-center">F. Solicitud</th>
					<th class="text-center">F. Suscripción</th>
					<th class="text-center">Giro</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Abogado</th>
					<?php } ?>
					<th class="text-center">Estado</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Fecha de atención</th>
						<th class="text-center">Días de atención</th>
					<?php } ?>
					<th class="text-center">Estado Aprobación</th>
					<th class="text-center">Aprobante</th>
					<th class="text-center">F. Aprobación</th>
					<th class="text-center">Ver Detalle</th>
				</tr>
			</tfoot>
			<tbody>
				<?php
				while ($sel = $sel_query->fetch_assoc()) {
					$giro = "";
					$verificar_giro = trim($sel["verificar_giro"]);
					if ($verificar_giro == "0") {
						$giro = "";
					} else if ($verificar_giro == "1") {
						$giro = "Si";
					} else {
						$giro = "No";
					}
				?>
					<tr>
						<td class="text-center"><?php echo $sel["sigla_correlativo"];
												echo $sel["codigo_correlativo"]; ?></td>
						<td><?php echo $sel["nombre_tienda"]; ?></td>
						<td><?php echo $sel["cc_id"]; ?></td>
						<td><?php echo $sel["ubicacion"]; ?></td>
						<td><?php echo $sel["fecha_solicitud"]; ?></td>
						<td><?php echo $sel["fecha_suscripcion"]; ?></td>
						<td><?php echo $giro; ?></td>
						<?php if ($area_id == '33') { ?>
							<td><?php echo $sel["abogado"]; ?></td>
						<?php } ?>
						<td class="text-center">
							<?php
							if ($sel["cancelado_id"] == 1) {
								echo '<span class="badge bg-danger text-white">Cancelado</span>';
							} elseif ($area_id == '33') {
								$bg_estado_solicitud = '';
								switch ($sel["estado_solicitud"]) {
									case 1:
										$bg_estado_solicitud = 'bg-default';
										break;
									case 2:
										$bg_estado_solicitud = 'bg-info';
										break;
									case 3:
										$bg_estado_solicitud = 'bg-warning';
										break;
								}
							?>
								<span class="badge <?= $bg_estado_solicitud ?> text-white"><?php echo $sel["nombre_estado_solicitud"]; ?></span>
							<?php } else {
								echo '<span class="badge bg-info text-white">En proceso</span>';
							} ?>
						</td>
						<?php if ($area_id == '33') { ?>
							<td class="text-center">
								<?php echo (!is_null($sel['fecha_cambio_estado_solicitud'])) ? $sel['fecha_cambio_estado_solicitud'] : ''; ?>
							</td>
							<td class="text-center">
								<?php echo (!is_null($sel['dias_habiles'])) ? sec_contrato_solicitud_num_dias_habiles_formato($sel['dias_habiles']) : ''; ?>
							</td>
						<?php } ?>
						<td class="text-center">
							<?php
							if (($sel["usuario_s"] ==  $login["area_id"]) || $usuario_id_login == $sel["aprobador_id"] || $usuario_id_login == $sel["aprobado_por"] || $area_id == '6' || ($area_id == '33')) {
								echo $sel["aprob"];
								echo $sel["aprobS"];
								echo $sel["aprobN"];
							}
							?>
						</td>
						<td>
							<?php
							if (($sel["usuario_s"] ==  $login["area_id"]) || $usuario_id_login == $sel["aprobador_id"] || $usuario_id_login == $sel["aprobado_por"] || $area_id == '6' || ($area_id == '33')) {

								if (trim($sel["aprob"]) == "Pendiente") {
									echo $sel["nombre_del_director_a_aprobar"];
								} else {
									echo $sel["nombre_del_aprobador"];
								}
							}
							?>
						</td>
						<td>
							<?php if (($sel["usuario_s"] ==  $login["area_id"]) || $usuario_id_login == $sel["director_aprobacion_id"] || $usuario_id_login == $sel["aprobado_por"] || $area_id == '6' || ($area_id == '33')) {
								echo $sel["fecha_aprobacion"];
							}
							?>
						</td>
						<td class="text-center">
							<a class="btn btn-rounded btn-primary btn-sm"
								href="./?sec_id=<?php echo $sec_id; ?>&amp;sub_sec_id=detalle_solicitudV2&id=<?php echo $sel["contrato_id"]; ?>"
								title="Ver detalle">
								<i class="fa fa-eye"></i>
							</a>
						</td>
					</tr>
				<?php
				}
				?>
			</tbody>
		</table>
	</div>


<?php
}
if ($_POST['action'] == "listar_contrato_arredamiento_v2") {

	// datos de sesion
	$menu_consultar = $_POST['menu_consultar'];
	$area_id = $_POST['area_id'];
	$cargo_id = $_POST['cargo_id'];
	$menu_id = $_POST['menu_id'];
	$sec_id = $_POST['sec_id'];

	$empresa = $_POST['empresa'];
	$area_solicitante = $_POST['area'];
	$ruc = $_POST['ruc'];
	$razon_social = $_POST['razon_social'];
	$moneda = $_POST['moneda'];
	$fecha_inicio = trim($_POST['fecha_inicio']);
	$fecha_fin = trim($_POST['fecha_fin']);
	$nombre_tienda = trim($_POST['nombre_tienda']);
	$estado_sol_v2 = trim($_POST['estado_sol_v2']);

	$estado_sol = '';
	if (isset($_POST["estado_sol"])) {
		$estado_sol = $_POST["estado_sol"];
	}

	$id_departamento = $_POST['id_departamento'];
	$id_provincia = $_POST['id_provincia'];
	$id_distrito = $_POST['id_distrito'];


	$where_empresa = "";
	$where_area_solicitante = "";
	$where_ruc = "";
	$where_razon_social = "";
	$where_moneda = "";
	$where_fecha = '';
	$where_ubigeo = '';
	$where_nombre_tienda = '';
	$where_estado_sol = '';
	$where_estado_sol_v2 = '';
	$where_estado_cancelado = "";

	if (!empty($empresa)) {
		$where_empresa = " AND c.empresa_suscribe_id = '" . $empresa . "' ";
	}

	if (!empty($area_solicitante)) {
		$where_area_solicitante = " AND a.id = '" . $area_solicitante . "' ";
	}

	if (!empty($ruc)) {
		$where_ruc = " AND c.ruc LIKE '%" . $ruc . "%' ";
	}

	if (!empty($razon_social)) {
		$where_razon_social = " AND c.razon_social  LIKE '%" . $razon_social . "%' ";
	}

	if (!empty($moneda)) {
		$where_moneda = " AND ce.tipo_moneda_id = '" . $moneda . "'";
	}

	if (!empty($fecha_inicio) && !empty($fecha_fin)) {
		$where_fecha = " AND c.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
	} elseif (!empty($fecha_inicio)) {
		$where_fecha = " AND c.created_at >= '$fecha_inicio 00:00:00'";
	} elseif (!empty($fecha_fin)) {
		$where_fecha = " AND c.created_at <= '$fecha_fin 23:59:59'";
	}

	if (!empty($id_departamento)) {
		$where_ubigeo = " AND i.ubigeo_id LIKE '%" . $id_departamento . $id_provincia . $id_distrito . "%'";
	}

	if (!empty($nombre_tienda)) {
		$where_nombre_tienda = " AND c.nombre_tienda LIKE '%" . $nombre_tienda . "%' ";
	}

	if (!empty($estado_sol)) {
		if ((int) $estado_sol === 99) {
			$where_estado_sol_v2 = " AND c.cancelado_id = 1 ";
		} elseif ((int) $estado_sol === 1) {
			$where_estado_sol = " AND ( c.estado_solicitud = 1 OR c.estado_solicitud IS NULL ) ";
		} else {
			$where_estado_sol = " AND c.estado_solicitud = '" . $estado_sol . "' ";
		}
	}

	if ($login["area_id"] == 33) {
		$where_estado_cancelado = " AND ( c.cancelado_id != 1 OR c.cancelado_id IS NULL )";
	}

	if (!empty($estado_sol_v2)) {
		if ((int) $estado_sol_v2 === 1) {
			$where_estado_sol_v2 = " AND ( c.cancelado_id != 1 || c.cancelado_id IS NULL )";
		} elseif ((int) $estado_sol_v2 === 2) {
			$where_estado_sol_v2 = " AND c.cancelado_id = 1 ";
		}
	}


	$total_query = $mysqli->query("
		SELECT COUNT(c.contrato_id) as num_rows 
        FROM 
			cont_contrato c
			INNER JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id AND ce.status = 1 
			INNER JOIN cont_inmueble i ON c.contrato_id = i.contrato_id AND ce.contrato_detalle_id = i.contrato_detalle_id
			INNER JOIN cont_etapa e ON c.etapa_id = e.etapa_id
			INNER JOIN tbl_areas a ON e.area_id = a.id
			LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			LEFT JOIN cont_estado_solicitud es ON es.id = c.estado_solicitud
		WHERE 
			c.tipo_contrato_id = 1 
			AND c.status = 1 
			AND e.etapa_id <> 5
           " . $where_empresa . "
			" . $where_area_solicitante . "
			" . $where_ruc . "
			" . $where_razon_social . "
			" . $where_moneda . "
			" . $where_fecha . "
			" . $where_ubigeo . "
			" . $where_nombre_tienda . "
			" . $where_estado_sol . "
			" . $where_estado_sol_v2 . "
			" . $where_estado_cancelado . "
		");

	$resultNum = $total_query->fetch_assoc();
	$num_rows = $resultNum['num_rows'];
	$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
	$per_page = 10;
	$numLinks = 10;
	$total_paginate = ceil($num_rows / $per_page);
	$total_paginate = $total_paginate == 0 ? 1 : $total_paginate;
	$page = $page > $total_paginate ? $total_paginate : $page;
	$offset = ($page - 1) * $per_page;
	$paginate = new Pagination('sec_contratos_solicitud_cambiar_de_pagina', $num_rows, $per_page, $numLinks, $page);

?>

	<?php
	$query = "
		SELECT 
			c.contrato_id, 
			c.nombre_tienda, 
			c.cc_id,
			i.ubicacion, 
			i.num_partida_registral, 
			c.created_at AS fecha_solicitud, 
			ce.fecha_suscripcion,
			e.etapa_id, 
			e.nombre AS etapa, 
			e.descripcion AS etapa_descripcion, 
			e.situacion AS etapa_situacion, 
			a.nombre AS area_encargada, 
			c.verificar_giro, 
			co.sigla AS sigla_correlativo, 
			c.codigo_correlativo,
			c.estado_solicitud,
			es.nombre AS nombre_estado_solicitud,
			c.cancelado_id,
			c.fecha_cambio_estado_solicitud,
			c.dias_habiles,
			(CASE WHEN (c.fecha_aprobacion IS NULL AND c.estado_aprobacion=0) THEN 'Pendiente' ELSE '' END) as aprob, 	
			(CASE WHEN (c.fecha_aprobacion IS NOT NULL AND c.estado_aprobacion=1) THEN 'Aprobado' ELSE '' END) as aprobS, 
			(CASE WHEN (c.fecha_aprobacion IS NOT NULL AND c.estado_aprobacion=0) THEN 'Denegado' ELSE '' END) as aprobN, 
			CONCAT(IFNULL(pud.nombre, ''),' ',IFNULL(pud.apellido_paterno, ''),' ',IFNULL(pud.apellido_materno, '')) AS nombre_del_director_a_aprobar,
			CONCAT(IFNULL(puap.nombre, ''),' ',IFNULL(puap.apellido_paterno, ''),' ',IFNULL(puap.apellido_materno, '')) AS nombre_del_aprobador,
			c.aprobador_id,
			c.aprobado_por,
			c.fecha_aprobacion,
			CONCAT(IFNULL(pa.nombre, ''),' ',IFNULL(pa.apellido_paterno, ''),' ',IFNULL(pa.apellido_materno, '')) AS abogado,
			p.area_id as usuario_s,
			raz.nombre as arrendadatario,
			pers.nombre arrendador
		FROM 
			cont_contrato c
			INNER JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id AND ce.status = 1
			INNER JOIN cont_inmueble i ON c.contrato_id = i.contrato_id AND ce.contrato_detalle_id = i.contrato_detalle_id
			INNER JOIN cont_etapa e ON c.etapa_id = e.etapa_id
			INNER JOIN tbl_areas a ON e.area_id = a.id
			LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			LEFT JOIN cont_estado_solicitud es ON es.id = c.estado_solicitud
			LEFT JOIN tbl_usuarios ud ON c.aprobador_id = ud.id
			LEFT JOIN tbl_personal_apt pud ON ud.personal_id = pud.id
			LEFT JOIN tbl_usuarios uap ON c.aprobado_por = uap.id
			LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id
			LEFT JOIN tbl_usuarios ua ON c.abogado_id = ua.id
			LEFT JOIN tbl_personal_apt pa ON ua.personal_id = pa.id
			INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
			INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
			INNER JOIN tbl_razon_social raz ON raz.id = c.empresa_suscribe_id
			INNER JOIN cont_propietario prop ON prop.contrato_id = c.contrato_id
			INNER JOIN cont_persona pers ON pers.id = prop.persona_id
		WHERE 
			c.tipo_contrato_id = 1 
			AND c.status = 1 AND c.estado_aprobacion = 0
			AND e.etapa_id <> 5
			" . $where_empresa . "
			" . $where_area_solicitante . "
			" . $where_ruc . "
			" . $where_razon_social . "
			" . $where_moneda . "
			" . $where_fecha . "
			" . $where_ubigeo . "
			" . $where_nombre_tienda . "
			" . $where_estado_sol . "
			" . $where_estado_sol_v2 . "
			" . $where_estado_cancelado . "
			ORDER BY c.created_at DESC

		";
	$sel_query = $mysqli->query($query);
	?>

	<div class="w-100 text-center">
		<h5 style="font-weight: bold; margin: 0px;">SOLICITUDES DE CONTRATO DE ARRENDAMIENTO</h5>
	</div>
	<br>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed table-bordered tabla_contratos_para_filtro">
			<thead>
				<tr>
					<th class="text-center">Código</th>

					<th class="text-center">Ubicación del Inmueble</th>
					<th class="text-center">F. Solicitud</th>
					<th class="text-center">F. Suscripción</th>


					<th class="text-center">Estado</th>

					<th class="text-center">Estado Aprobación</th>
					<th class="text-center">Arrendador</th>

					<th class="text-center">Arrendatario</th>
					<th class="text-center">Ver Detalle</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th class="text-center">Código</th>

					<th class="text-center">Ubicación del Inmueble</th>
					<th class="text-center">F. Solicitud</th>
					<th class="text-center">F. Suscripción</th>


					<th class="text-center">Estado</th>

					<th class="text-center">Estado Aprobación</th>

					<th class="text-center">Arrendador</th>
					<th class="text-center">Arrendatario</th>

					<th class="text-center">Ver Detalle</th>
				</tr>
			</tfoot>
			<tbody>
				<?php
				while ($sel = $sel_query->fetch_assoc()) {
					$giro = "";
					$verificar_giro = trim($sel["verificar_giro"]);
					if ($verificar_giro == "0") {
						$giro = "";
					} else if ($verificar_giro == "1") {
						$giro = "Si";
					} else {
						$giro = "No";
					}
				?>
					<tr>
						<td class="text-center"><?php echo $sel["sigla_correlativo"];
												echo $sel["codigo_correlativo"]; ?></td>

						<td><?php echo $sel["ubicacion"]; ?></td>
						<td><?php echo $sel["fecha_solicitud"]; ?></td>
						<td><?php echo $sel["fecha_suscripcion"]; ?></td>


						<td class="text-center">
							<?php
							if ($sel["cancelado_id"] == 1) {
								echo '<span class="badge bg-danger text-white">Cancelado</span>';
								// } elseif ($area_id == '33') {
							} else {
								$bg_estado_solicitud = '';
								switch ($sel["estado_solicitud"]) {
									case 1:
										$bg_estado_solicitud = 'bg-default';
										break;
									case 2:
										$bg_estado_solicitud = 'bg-info';
										break;
									case 3:
										$bg_estado_solicitud = 'bg-warning';
										break;
								}
							?>
								<span class="badge <?= $bg_estado_solicitud ?> text-white"><?php echo $sel["nombre_estado_solicitud"]; ?></span>
							<?php
							} ?>
						</td>

						<td class="text-center">
							<?php
							if (($sel["usuario_s"] ==  $login["area_id"]) || $usuario_id_login == $sel["aprobador_id"] || $usuario_id_login == $sel["aprobado_por"] || $area_id == '6' || ($area_id == '33')) {
								echo $sel["aprob"];
								echo $sel["aprobS"];
								echo $sel["aprobN"];
							}
							?>
						</td>

						<td><?php echo $sel["arrendador"]; ?></td>
						<td><?php echo $sel["arrendadatario"]; ?></td>

						<td class="text-center">
							<a class="btn btn-rounded btn-primary btn-sm"
								href="./?sec_id=<?php echo $sec_id; ?>&amp;sub_sec_id=detalle_solicitudV2&id=<?php echo $sel["contrato_id"]; ?>"
								title="Ver detalle">
								<i class="fa fa-eye"></i>
							</a>
						</td>
					</tr>
				<?php
				}
				?>
			</tbody>
		</table>
	</div>


<?php
}

if ($_POST['action'] == "listar_contrato_proveedor") {
	// datos de sesion
	$menu_consultar = $_POST['menu_consultar'];
	$area_id = $_POST['area_id'];
	$cargo_id = $_POST['cargo_id'];
	$menu_id = $_POST['menu_id'];
	$usuario_permisos = json_decode($_POST['usuario_permisos'], true);
	$sec_id = $_POST['sec_id'];

	$empresa = $_POST['empresa'];
	$area_solicitante = $_POST['area'];
	$ruc = $_POST['ruc'];
	$razon_social = $_POST['razon_social'];
	$moneda = $_POST['moneda'];
	$fecha_inicio = trim($_POST['fecha_inicio']);
	$fecha_fin = trim($_POST['fecha_fin']);
	$estado_sol_v2 = trim($_POST['estado_sol_v2']);
	$estado_aprobacion = trim($_POST['estado_aprobacion']);
	$fecha_inicio_aprobacion = trim($_POST['fecha_inicio_aprobacion']);
	$fecha_fin_aprobacion = trim($_POST['fecha_fin_aprobacion']);
	$director_aprobacion_id = trim($_POST['director_aprobacion_id']);

	$estado_sol = '';
	if (isset($_POST["estado_sol"])) {
		$estado_sol = $_POST["estado_sol"];
	}

	$where_empresa = "";
	$where_area_solicitante = "";
	$where_ruc = "";
	$where_razon_social = "";
	$where_moneda = "";

	$where_estado_sol = '';
	$where_estado_sol_v2 = '';
	$where_estado_aprobacion = '';
	$where_director_aprobacion = '';

	$where_area = "";
	$where_estado_cancelado = "";
	if (array_key_exists($menu_consultar, $usuario_permisos) && (in_array("ver_todo_solicitud", $usuario_permisos[$menu_consultar]) || in_array("see_all", $usuario_permisos[$menu_consultar]))) {
		$where_area = "";
	} else {
		if ($area_id == 33 || $area_id == 6) {
			$where_area = " AND ((c.check_gerencia_proveedor = 0 ) OR (c.aprobacion_gerencia_proveedor = 1) OR (c.check_gerencia_proveedor = 1 and p.area_id = 33 ) )";
		} else {
			$where_area = " AND (c.user_created_id = " . $usuario_id_login . " OR a.id =" . $area_id_login . ")";
		}
	}

	if ($login["area_id"] == 33) {
		$where_estado_cancelado = " AND ( c.cancelado_id != 1 OR c.cancelado_id IS NULL )";
	}

	if (!empty($empresa)) {
		$where_empresa = " AND c.empresa_suscribe_id = '" . $empresa . "' ";
	}

	if (!empty($area_solicitante)) {
		$where_area_solicitante = " AND a.id = '" . $area_solicitante . "' ";
	}

	if (!empty($ruc)) {
		$where_ruc = " AND c.ruc LIKE '%" . $ruc . "%' ";
	}

	if (!empty($razon_social)) {
		$where_razon_social = " AND c.razon_social  LIKE '%" . $razon_social . "%' ";
	}

	if (!empty($moneda)) {
		$where_moneda = " AND (c.moneda_id = $moneda OR (SELECT ct.moneda_id FROM cont_contraprestacion ct WHERE ct.contrato_id = c.contrato_id LIMIT 1) = $moneda) ";
	}

	$where_fecha = '';
	if (!empty($fecha_inicio) && !empty($fecha_fin)) {
		$where_fecha = " AND c.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
	} elseif (!empty($fecha_inicio)) {
		$where_fecha = " AND c.created_at >= '$fecha_inicio 00:00:00'";
	} elseif (!empty($fecha_fin)) {
		$where_fecha = " AND c.created_at <= '$fecha_fin 23:59:59'";
	}

	$where_fecha_aprobacion = '';
	if (!empty($fecha_inicio_aprobacion) && !empty($fecha_fin_aprobacion)) {
		$where_fecha_aprobacion = " AND c.fecha_atencion_gerencia_proveedor between '$fecha_inicio_aprobacion 00:00:00' AND '$fecha_fin_aprobacion 23:59:59'";
	} elseif (!empty($fecha_inicio_aprobacion)) {
		$where_fecha_aprobacion = " AND c.fecha_atencion_gerencia_proveedor >= '$fecha_inicio_aprobacion 00:00:00'";
	} elseif (!empty($fecha_fin_aprobacion)) {
		$where_fecha_aprobacion = " AND c.fecha_atencion_gerencia_proveedor <= '$fecha_fin_aprobacion 23:59:59'";
	}

	if (!empty($estado_sol)) {
		if ((int) $estado_sol === 99) {
			$where_estado_sol_v2 = " AND c.cancelado_id = 1 ";
		} elseif ((int) $estado_sol === 1) {
			$where_estado_sol = " AND ( c.estado_solicitud = 1 OR c.estado_solicitud IS NULL ) ";
		} else {
			$where_estado_sol = " AND c.estado_solicitud = '" . $estado_sol . "' ";
		}
	}

	if (!empty($estado_sol_v2)) {
		if ((int) $estado_sol_v2 === 1) {
			$where_estado_sol_v2 = " AND ( c.cancelado_id != 1 || c.cancelado_id IS NULL )";
		} elseif ((int) $estado_sol_v2 === 2) {
			$where_estado_sol_v2 = " AND c.cancelado_id = 1 ";
		}
	}

	if (!empty($director_aprobacion_id)) {
		$where_director_aprobacion = " AND ( ( c.director_aprobacion_id = " . $director_aprobacion_id . " AND (c.check_gerencia_proveedor =1 and c.fecha_atencion_gerencia_proveedor IS NULL AND c.aprobacion_gerencia_proveedor=0) ) OR c.aprobado_por = " . $director_aprobacion_id . " ) ";
	}

	if ($estado_aprobacion == 1) {
		$where_estado_aprobacion = " AND (c.check_gerencia_proveedor =1 and c.fecha_atencion_gerencia_proveedor IS NOT NULL AND c.aprobacion_gerencia_proveedor=1)";
	} elseif ($estado_aprobacion == 2) {
		$where_estado_aprobacion = " AND (c.check_gerencia_proveedor =1 and c.fecha_atencion_gerencia_proveedor IS NOT NULL AND c.aprobacion_gerencia_proveedor=0)";
	} elseif ($estado_aprobacion == 3) {
		$where_estado_aprobacion = " AND (c.check_gerencia_proveedor =1 and c.fecha_atencion_gerencia_proveedor IS NULL AND c.aprobacion_gerencia_proveedor=0)";
	}

	$total_query = $mysqli->query("
		SELECT COUNT(c.contrato_id) as num_rows 
			FROM cont_contrato c
				INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
				INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
				INNER JOIN tbl_areas a ON c.area_responsable_id = a.id
				INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id AND r.status = 1
				LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
				LEFT JOIN cont_estado_solicitud es ON es.id = c.estado_solicitud
			WHERE c.status = 1 AND c.tipo_contrato_id = 2 AND c.etapa_id != 5 
			" . $where_area . "
			" . $where_estado_cancelado . "
			" . $where_empresa . "
			" . $where_area_solicitante . "
			" . $where_ruc . "
			" . $where_razon_social . "
			" . $where_moneda . "
			" . $where_fecha . "
			" . $where_estado_sol . "
			" . $where_estado_sol_v2 . "
			" . $where_director_aprobacion . "
			" . $where_estado_aprobacion . "
			" . $where_fecha_aprobacion . "
		");

	$resultNum = $total_query->fetch_assoc();
	$num_rows = $resultNum['num_rows'];
	$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
	$per_page = 10;
	$numLinks = 10;
	$total_paginate = ceil($num_rows / $per_page);
	$total_paginate = $total_paginate == 0 ? 1 : $total_paginate;
	$page = $page > $total_paginate ? $total_paginate : $page;
	$offset = ($page - 1) * $per_page;
	$paginate = new Pagination('sec_contratos_solicitud_cambiar_de_pagina', $num_rows, $per_page, $numLinks, $page);

	$sel_query = $mysqli->query(
		"
			SELECT 
				c.contrato_id, 
				concat(IFNULL(p.nombre, ''), ' ',  IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS solicitante,
				a.nombre AS area, c.razon_social AS parte, r.nombre AS empresa_suscribe, c.status, c.detalle_servicio,
				c.created_at,
				co.sigla AS sigla_correlativo,
				c.codigo_correlativo,
				c.estado_solicitud,
				es.nombre AS nombre_estado_solicitud, 
				(CASE WHEN (c.check_gerencia_proveedor =1 and c.fecha_atencion_gerencia_proveedor IS NULL AND c.aprobacion_gerencia_proveedor=0) THEN 'Pendiente' ELSE '' END) as aprob, 	
				(CASE WHEN (c.check_gerencia_proveedor =1 and c.fecha_atencion_gerencia_proveedor IS NOT NULL AND c.aprobacion_gerencia_proveedor=1) THEN 'Aprobado' ELSE '' END) as aprobS, 
				(CASE WHEN (c.check_gerencia_proveedor =1 and c.fecha_atencion_gerencia_proveedor IS NOT NULL AND c.aprobacion_gerencia_proveedor=0) THEN 'Denegado' ELSE '' END) as aprobN, 
				p.area_id as usuario_s,
				c.fecha_atencion_gerencia_proveedor,
				c.cancelado_id,
				CONCAT(IFNULL(pud.nombre, ''),' ',IFNULL(pud.apellido_paterno, ''),' ',IFNULL(pud.apellido_materno, '')) AS nombre_del_director_a_aprobar,
				CONCAT(IFNULL(puap.nombre, ''),' ',IFNULL(puap.apellido_paterno, ''),' ',IFNULL(puap.apellido_materno, '')) AS nombre_del_aprobador,
				c.director_aprobacion_id,
				c.aprobado_por,
				c.fecha_cambio_estado_solicitud,
				c.area_responsable_id,
				c.dias_habiles,
				CONCAT(IFNULL(pa.nombre, ''),' ',IFNULL(pa.apellido_paterno, ''),' ',IFNULL(pa.apellido_materno, '')) AS abogado
			FROM cont_contrato c
				INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
				INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
				INNER JOIN tbl_areas a ON c.area_responsable_id = a.id
				INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id AND r.status = 1
				LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
				LEFT JOIN cont_estado_solicitud es ON es.id = c.estado_solicitud
				LEFT JOIN tbl_usuarios ud ON c.director_aprobacion_id = ud.id
				LEFT JOIN tbl_personal_apt pud ON ud.personal_id = pud.id
				LEFT JOIN tbl_usuarios uap ON c.aprobado_por = uap.id
				LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id
				LEFT JOIN tbl_usuarios ua ON c.abogado_id = ua.id
				LEFT JOIN tbl_personal_apt pa ON ua.personal_id = pa.id
			WHERE c.status = 1 AND c.tipo_contrato_id = 2 AND c.etapa_id != 5 
			" . $where_area . "
			" . $where_estado_cancelado . "
			" . $where_empresa . "
			" . $where_area_solicitante . "
			" . $where_ruc . "
			" . $where_razon_social . "
			" . $where_moneda . "
			" . $where_fecha . "
			" . $where_estado_sol . "
			" . $where_estado_sol_v2 . "
			" . $where_director_aprobacion . "
			" . $where_estado_aprobacion . "
			" . $where_fecha_aprobacion . "
			ORDER BY c.created_at DESC
            

			"
	);

?>
	<div class="w-100 text-center">
		<h5 style="font-weight: bold; margin: 0px;">SOLICITUDES DE CONTRATO DE PROVEEDOR</h5>
	</div>
	<br>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed table-bordered tabla_contratos_para_filtro">
			<thead>
				<tr>
					<th class="text-center">Código</th>
					<th class="text-center">Área solicitante</th>
					<th class="text-center">Solicitante</th>
					<th class="text-center">F. Solicitud</th>
					<th class="text-center">Detalle servicio</th>
					<th class="text-center">Empresa que suscribe</th>
					<th class="text-center">Proveedor</th>
					<th class="text-center">Estado Solicitud</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Fecha de atención</th>
						<th class="text-center">Días de atención</th>
					<?php } ?>
					<th class="text-center">Estado Aprobación</th>
					<th class="text-center">Aprobante</th>
					<th class="text-center">F. Aprobación</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Abogado</th>
						<th class="text-center">Seguimiento</th>
					<?php } ?>
					<th class="text-center">Ver Detalle</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th class="text-center">Código</th>
					<th class="text-center">Área solicitante</th>
					<th class="text-center">Solicitante</th>
					<th class="text-center">F. Solicitud</th>
					<th class="text-center">Detalle servicio</th>
					<th class="text-center">Empresa que suscribe</th>
					<th class="text-center">Proveedor</th>
					<th class="text-center">Estado Solicitud</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Fecha de atención</th>
						<th class="text-center">Días de atención</th>
					<?php } ?>
					<th class="text-center">Estado Aprobación</th>
					<th class="text-center">Aprobante</th>
					<th class="text-center">F. Aprobación</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Abogado</th>
						<th class="text-center">Seguimiento</th>
					<?php } ?>
					<th class="text-center">Ver Detalle</th>
				</tr>
			</tfoot>
			<tbody>
				<?php
				while ($sel = $sel_query->fetch_assoc()) {
					$detalle_servicio = trim($sel["detalle_servicio"]);
					$parte = trim($sel["parte"]);

					if (strlen($detalle_servicio) > 50) {
						$detalle_servicio = substr($detalle_servicio, 0, 50) . ' ...';
					}

					if (strlen($parte) > 50) {
						$parte = substr($parte, 0, 50) . ' ...';
					}
				?>
					<tr>
						<td class="text-center"><?php echo $sel["sigla_correlativo"];
												echo $sel["codigo_correlativo"]; ?></td>
						<td><?php echo $sel["area"]; ?></td>
						<td><?php echo $sel["solicitante"]; ?></td>
						<td><?php echo $sel["created_at"]; ?></td>
						<td><?php echo $detalle_servicio; ?></td>
						<td><?php echo $sel["empresa_suscribe"]; ?></td>
						<td><?php echo $parte; ?></td>
						<td class="text-center">
							<?php
							if ($sel["cancelado_id"] == 1) {
								echo '<span class="badge bg-danger text-white">Cancelado</span>';
							} elseif ($area_id == '33') {
								$bg_estado_solicitud = '';
								switch ($sel["estado_solicitud"]) {
									case 1:
										$bg_estado_solicitud = 'bg-default';
										break;
									case 2:
										$bg_estado_solicitud = 'bg-info';
										break;
									case 3:
										$bg_estado_solicitud = 'bg-warning';
										break;
								}
							?>
								<span class="badge <?= $bg_estado_solicitud ?> text-white"><?php echo $sel["nombre_estado_solicitud"]; ?></span>

							<?php } else {
								echo '<span class="badge bg-info text-white">En proceso</span>';
							} ?>
						</td>
						<?php if ($area_id == '33') { ?>
							<td class="text-center">
								<?php echo (!is_null($sel['fecha_cambio_estado_solicitud'])) ? $sel['fecha_cambio_estado_solicitud'] : ''; ?>
							</td>
							<td class="text-center">
								<?php echo (!is_null($sel['dias_habiles'])) ? sec_contrato_solicitud_num_dias_habiles_formato($sel['dias_habiles']) : ''; ?>
							</td>
						<?php } ?>
						<td class="text-center">
							<?php
							if (($sel["usuario_s"] ==  $login["area_id"]) || $usuario_id_login == $sel["director_aprobacion_id"] || $usuario_id_login == $sel["aprobado_por"] || $area_id == '6' || ($area_id == '33')) {
								echo $sel["aprob"];
								echo $sel["aprobS"];
								echo $sel["aprobN"];
							}
							?>
						</td>
						<td>
							<?php
							if (($sel["usuario_s"] ==  $login["area_id"]) || $usuario_id_login == $sel["director_aprobacion_id"] || $usuario_id_login == $sel["aprobado_por"] || $area_id == '6' || ($area_id == '33')) {

								if (trim($sel["aprob"]) == "Pendiente") {
									echo $sel["nombre_del_director_a_aprobar"];
								} else {
									echo $sel["nombre_del_aprobador"];
								}
							}
							?>
						</td>
						<td>
							<?php if (($sel["usuario_s"] ==  $login["area_id"]) || $usuario_id_login == $sel["director_aprobacion_id"] || $usuario_id_login == $sel["aprobado_por"] || $area_id == '6' || ($area_id == '33')) {
								echo $sel["fecha_atencion_gerencia_proveedor"];
							}
							?>
						</td>
						<?php if ($area_id == '33') {
							$seg_proc = new SeguimientoProceso();
							$data_sp['tipo_documento_id'] = 1;
							$data_sp['proceso_id'] = $sel['contrato_id'];
							$data_sp['proceso_detalle_id'] = 0;
							$etapa_seguimiento = $seg_proc->obtener_ultimo_seguimiento($data_sp);
						?>
							<td class="text-center"><?php echo $sel["abogado"]; ?></td>
							<td class="text-center"><?php echo $etapa_seguimiento; ?></td>
						<?php } ?>
						<td class="text-center">
							<a class="btn btn-rounded btn-primary btn-sm"
								href="./?sec_id=<?php echo $sec_id; ?>&amp;sub_sec_id=detalleSolicitudProveedor&id=<?php echo $sel["contrato_id"]; ?>"
								title="Ver detalle">
								<i class="fa fa-eye"></i>
							</a>
						</td>
					</tr>
				<?php
				}
				?>
			</tbody>
		</table>
	</div>



<?php
}

if ($_POST['action'] == "listar_adenda_de_arrendamiento") {
	$menu_consultar = $_POST['menu_consultar'];
	$area_id = $_POST['area_id'];
	$cargo_id = $_POST['cargo_id'];
	$menu_id = $_POST['menu_id'];
	$empresa = $_POST['empresa'];
	$area_solicitante = $_POST['area'];
	$ruc = $_POST['ruc'];
	$razon_social = $_POST['razon_social'];
	$moneda = $_POST['moneda'];
	$nombre_tienda = trim($_POST['nombre_tienda']);
	$id_departamento = $_POST['id_departamento'];
	$id_provincia = $_POST['id_provincia'];
	$id_distrito = $_POST['id_distrito'];
	$fecha_inicio = trim($_POST['fecha_inicio']);
	$fecha_fin = trim($_POST['fecha_fin']);
	$usuario_permisos = json_decode($_POST['usuario_permisos'], true);
	$sec_id = $_POST['sec_id'];
	$estado_sol_v2 = trim($_POST['estado_sol_v2']);

	$estado_sol = '';
	if (isset($_POST["estado_sol"])) {
		$estado_sol = $_POST["estado_sol"];
	}

	$where_empresa = "";
	$where_area_solicitante = "";
	$where_ruc = "";
	$where_razon_social = "";
	$where_moneda = "";
	$where_ubigeo = '';
	$where_nombre_tienda = '';
	$query_fecha = '';
	$where_estado_sol = '';
	$where_estado_sol_v2 = '';
	$where_estado_cancelado = "";

	if (!empty($empresa)) {
		$where_empresa = " AND c.empresa_suscribe_id = '" . $empresa . "' ";
	}

	if (!empty($ruc)) {
		$where_ruc = " AND c.ruc LIKE '%" . $ruc . "%' ";
	}

	if (!empty($razon_social)) {
		$where_razon_social = " AND c.razon_social  LIKE '%" . $razon_social . "%' ";
	}

	if (!empty($moneda)) {
		$where_moneda = " AND ce.tipo_moneda_id = '" . $moneda . "'";
	}

	if (!empty($id_departamento)) {
		$where_ubigeo = " AND i.ubigeo_id LIKE '%" . $id_departamento . $id_provincia . $id_distrito . "%'";
	}

	if (!empty($nombre_tienda)) {
		$where_nombre_tienda = " AND c.nombre_tienda LIKE '%" . $nombre_tienda . "%' ";
	}

	if (!empty($fecha_inicio) && !empty($fecha_fin)) {
		$query_fecha = " AND a.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
	} elseif (!empty($fecha_inicio)) {
		$query_fecha = " AND a.created_at >= '$fecha_inicio 00:00:00'";
	} elseif (!empty($fecha_fin)) {
		$query_fecha = " AND a.created_at <= '$fecha_fin 23:59:59'";
	}

	if (!empty($estado_sol)) {
		if ((int) $estado_sol === 99) {
			$where_estado_sol_v2 = " AND a.cancelado_id = 1 ";
		} elseif ((int) $estado_sol === 1) {
			$where_estado_sol = " AND ( a.estado_solicitud_id = 1 OR a.estado_solicitud_id IS NULL ) ";
		} else {
			$where_estado_sol = " AND a.estado_solicitud_id = '" . $estado_sol . "' ";
		}
	}

	if (!empty($estado_sol_v2)) {
		if ((int) $estado_sol_v2 === 1) {
			$where_estado_sol_v2 = " AND ( a.cancelado_id != 1 || a.cancelado_id IS NULL )";
		} elseif ((int) $estado_sol_v2 === 2) {
			$where_estado_sol_v2 = " AND a.cancelado_id = 1 ";
		}
	}

	if ($login["area_id"] == 33) {
		$where_estado_cancelado = " AND ( a.cancelado_id != 1 OR a.cancelado_id IS NULL )";
	}

	$total_query = $mysqli->query("
		SELECT COUNT(c.contrato_id) as num_rows 
		FROM cont_adendas a
		INNER JOIN cont_contrato c ON a.contrato_id = c.contrato_id
		INNER JOIN tbl_usuarios u ON a.user_created_id = u.id
		INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		INNER JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id AND ce.status = 1 
		INNER JOIN cont_inmueble i ON c.contrato_id = i.contrato_id AND i.contrato_detalle_id = ce.contrato_detalle_id
		INNER JOIN cont_etapa e ON c.etapa_id = e.etapa_id
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
		WHERE c.tipo_contrato_id = 1
		AND a.procesado = 0
		AND a.status = 1
		" . $where_empresa . "
		" . $where_ruc . "
		" . $where_razon_social . "
		" . $where_moneda . "
		" . $where_ubigeo . "
		" . $where_nombre_tienda . "
		" . $query_fecha . "
		" . $where_estado_sol . "
		" . $where_estado_sol_v2 . "
		" . $where_estado_cancelado . "
		 
		");

	$resultNum = $total_query->fetch_assoc();
	$num_rows = $resultNum['num_rows'];
	$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
	$per_page = 10;
	$numLinks = 10;
	$total_paginate = ceil($num_rows / $per_page);
	$total_paginate = $total_paginate == 0 ? 1 : $total_paginate;
	$page = $page > $total_paginate ? $total_paginate : $page;
	$offset = ($page - 1) * $per_page;
	$paginate = new Pagination('sec_contratos_solicitud_cambiar_de_pagina', $num_rows, $per_page, $numLinks, $page);

	$sel_query = $mysqli->query("
		SELECT
			a.id,
			a.codigo,
			c.contrato_id,
			c.nombre_tienda,
			CONCAT(p.nombre, ' ', IFNULL(p.apellido_paterno,''), ' ', IFNULL(p.apellido_materno,'')) AS solicitante,
			p.area_id,
			a.created_at,
			co.sigla AS sigla_correlativo,
			c.codigo_correlativo,
			a.estado_solicitud_id,
			es.nombre AS nombre_estado_solicitud,
			a.procesado,
			a.cancelado_id,
			(CASE WHEN (c.check_gerencia_proveedor =0 and c.fecha_aprobacion IS NULL AND c.aprobador_id=0) THEN 'Pendiente' ELSE '' END) as aprob, 	
			(CASE WHEN (c.check_gerencia_proveedor =0 and c.fecha_aprobacion IS NOT NULL AND c.aprobador_id=1) THEN 'Aprobado' ELSE '' END) as aprobS, 
			(CASE WHEN (c.check_gerencia_proveedor =0 and c.fecha_aprobacion IS NOT NULL AND c.aprobador_id=0) THEN 'Denegado' ELSE '' END) as aprobN,
			CONCAT(IFNULL(pud.nombre, ''),' ',IFNULL(pud.apellido_paterno, ''),' ',IFNULL(pud.apellido_materno, '')) AS nombre_del_director_a_aprobar,
			CONCAT(IFNULL(puap.nombre, ''),' ',IFNULL(puap.apellido_paterno, ''),' ',IFNULL(puap.apellido_materno, '')) AS nombre_del_aprobador,
			a.director_aprobacion_id,
			a.aprobado_por_id,
			a.aprobado_el,
			a.fecha_cambio_estado_solicitud,
			a.dias_habiles,
			CONCAT(IFNULL(pa.nombre, ''),' ',IFNULL(pa.apellido_paterno, ''),' ',IFNULL(pa.apellido_materno, '')) AS abogado
		FROM 
			cont_adendas a
			INNER JOIN cont_contrato c ON a.contrato_id = c.contrato_id
			INNER JOIN tbl_usuarios u ON a.user_created_id = u.id
			INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
			INNER JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id AND ce.status = 1
			INNER JOIN cont_inmueble i ON c.contrato_id = i.contrato_id AND i.contrato_detalle_id = ce.contrato_detalle_id
			INNER JOIN cont_etapa e ON c.etapa_id = e.etapa_id
			LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			LEFT JOIN cont_estado_solicitud es ON es.id = a.estado_solicitud_id
			LEFT JOIN tbl_usuarios ua ON a.abogado_id = ua.id
			LEFT JOIN tbl_personal_apt pa ON ua.personal_id = pa.id
			LEFT JOIN tbl_usuarios ud ON a.director_aprobacion_id = ud.id
			LEFT JOIN tbl_personal_apt pud ON ud.personal_id = pud.id
			LEFT JOIN tbl_usuarios uap ON a.director_aprobacion_id = uap.id
			LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id
		WHERE 
			c.tipo_contrato_id = 1
			AND a.procesado = 0
			AND a.status = 1
			" . $where_empresa . "
			" . $where_ruc . "
			" . $where_razon_social . "
			" . $where_moneda . "
			" . $where_ubigeo . "
			" . $where_nombre_tienda . "
			" . $query_fecha . "
			" . $where_estado_sol . "
			" . $where_estado_sol_v2 . "
			" . $where_estado_cancelado . "
			GROUP BY 
			a.id, a.codigo, c.contrato_id, c.nombre_tienda, 
			p.nombre, p.apellido_paterno, p.apellido_materno, p.area_id, 
			a.created_at, co.sigla, c.codigo_correlativo, a.estado_solicitud_id, 
			es.nombre, a.procesado, a.cancelado_id, c.check_gerencia_proveedor, 
			c.fecha_aprobacion, c.aprobador_id, pud.nombre, pud.apellido_paterno, 
			pud.apellido_materno, puap.nombre, puap.apellido_paterno, puap.apellido_materno, 
			a.director_aprobacion_id, a.aprobado_por_id, a.aprobado_el, 
			a.fecha_cambio_estado_solicitud, a.dias_habiles, pa.nombre, 
			pa.apellido_paterno, pa.apellido_materno
		ORDER BY a.created_at DESC
	 
		");

?>
	<div class="w-100 text-center">
		<h5 style="font-weight: bold; margin: 0px;">SOLICITUDES DE ADENDA DE CONTRATO DE ARRENDAMIENTO</h5>
	</div>
	<br>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed table-bordered tabla_contratos_para_filtro" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th class="text-center">Código</th>

					<th class="text-center">Solicitante</th>
					<th class="text-center">F. Solicitud</th>
					<th class="text-center">Estado</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Fecha de atención</th>
						<th class="text-center">Días de atención</th>
						<th class="text-center">Abogado</th>
					<?php } ?>
					<th class="text-center">Estado Aprobación</th>
					<th class="text-center">Aprobante</th>
					<th class="text-center">Ver Detalle</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th class="text-center">Código</th>

					<th class="text-center">Solicitante</th>
					<th class="text-center">F. Solicitud</th>
					<th class="text-center">Estado</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Fecha de atención</th>
						<th class="text-center">Días de atención</th>
						<th class="text-center">Abogado</th>
					<?php } ?>
					<th class="text-center">Estado Aprobación</th>
					<th class="text-center">Aprobante</th>
					<th class="text-center">Ver Detalle</th>
				</tr>
			</tfoot>
			<tbody>
				<?php
				while ($sel = $sel_query->fetch_assoc()) {
					$nombre_adenda = 'Adenda N° ' . $sel['codigo'] . ' - ' . $sel["sigla_correlativo"] . $sel["codigo_correlativo"];
					$nombre_tienda = $sel["nombre_tienda"];
				?>
					<tr>
						<td class="text-center"><?php echo $nombre_adenda; ?></td>

						<td><?php echo $sel["solicitante"]; ?></td>
						<td><?php echo $sel["created_at"]; ?></td>
						<td class="text-center">
							<?php
							if ($sel["cancelado_id"] == 1) {
								echo '<span class="badge bg-danger text-white">Cancelado</span>';
							} elseif ($area_id == '33') {
								$bg_estado_solicitud = '';
								switch ($sel["estado_solicitud_id"]) {
									case 1:
										$bg_estado_solicitud = 'bg-default';
										break;
									case 2:
										$bg_estado_solicitud = 'bg-info';
										break;
									case 3:
										$bg_estado_solicitud = 'bg-warning';
										break;
								}
							?>
								<span class="badge <?= $bg_estado_solicitud ?> text-white"><?php echo $sel["nombre_estado_solicitud"]; ?></span>
							<?php } else {
								echo '<span class="badge bg-info text-white">En proceso</span>';
							} ?>
						</td>
						<?php if ($area_id == '33') { ?>
							<td class="text-center">
								<?php echo (!is_null($sel['fecha_cambio_estado_solicitud'])) ? $sel['fecha_cambio_estado_solicitud'] : ''; ?>
							</td>
							<td class="text-center">
								<?php echo (!is_null($sel['dias_habiles'])) ? sec_contrato_solicitud_num_dias_habiles_formato($sel['dias_habiles']) : ''; ?>
							</td>
							<td class="text-center"><?php echo $sel["abogado"]; ?></td>
						<?php } ?>
						<td class="text-center">
							<?php
							if (($sel["area_id"] ==  $login["area_id"]) || $usuario_id_login == $sel["director_aprobacion_id"] || $usuario_id_login == $sel["aprobado_por_id"] || $area_id == '6' || ($area_id == '33')) {
								echo $sel["aprob"];
								echo $sel["aprobS"];
								echo $sel["aprobN"];
							}
							?>
						</td>
						<td>
							<?php
							if (($sel["area_id"] ==  $login["area_id"]) || $usuario_id_login == $sel["director_aprobacion_id"] || $usuario_id_login == $sel["aprobado_por_id"] || $area_id == '6' || ($area_id == '33')) {

								if (trim($sel["aprob"]) == "Pendiente") {
									echo $sel["nombre_del_director_a_aprobar"];
								} else {
									echo $sel["nombre_del_aprobador"];
								}
							}
							?>
						</td>

						<td class="text-center">
							<a class="btn btn-rounded btn-warning btn-sm"
								href="./?sec_id=<?php echo $sec_id; ?>&amp;sub_sec_id=detalle_adenda_arrendamiento&id=<?php echo $sel["id"]; ?>"
								title="Editar solicitud">
								<i class="fa fa-pencil"></i>
							</a>
							<a class="btn btn-rounded btn-primary btn-sm"
								href="./?sec_id=<?php echo $sec_id; ?>&amp;sub_sec_id=detalle_solicitudV2&id=<?php echo $sel["contrato_id"]; ?>&adenda_id=<?php echo $sel["id"]; ?>"
								title="Ver detalle">
								<i class="fa fa-eye"></i>
							</a>
							<?php if ($sel["procesado"] != 1 && $sel["cancelado_id"] != 1 && $sel["area_id"] == $area_id_login) { ?>
								<a class="btn btn-rounded btn-danger btn-sm"
									onclick="sec_contrato_solicitud_cancelar_solicitud_modal(1, <?php echo $sel["id"]; ?>, '<?php echo $nombre_adenda . ' ' . $nombre_tienda; ?>')"
									title="Eliminar Solicitud">
									<i class="fa fa-close"></i>
								</a>
							<?php } ?>
						</td>
					</tr>
				<?php
				}
				?>
			</tbody>
		</table>
	</div>



<?php
}

if ($_POST['action'] == "listar_adenda_de_mandato") {
	$menu_consultar = $_POST['menu_consultar'];
	$area_id = $_POST['area_id'];
	$cargo_id = $_POST['cargo_id'];
	$menu_id = $_POST['menu_id'];
	$empresa = $_POST['empresa'];
	$area_solicitante = $_POST['area'];
	$ruc = $_POST['ruc'];
	$razon_social = $_POST['razon_social'];
	$moneda = $_POST['moneda'];
	$nombre_tienda = trim($_POST['nombre_tienda']);
	$id_departamento = $_POST['id_departamento'];
	$id_provincia = $_POST['id_provincia'];
	$id_distrito = $_POST['id_distrito'];
	$fecha_inicio = trim($_POST['fecha_inicio']);
	$fecha_fin = trim($_POST['fecha_fin']);
	$usuario_permisos = json_decode($_POST['usuario_permisos'], true);
	$sec_id = $_POST['sec_id'];
	$estado_sol_v2 = trim($_POST['estado_sol_v2']);

	$estado_sol = '';
	if (isset($_POST["estado_sol"])) {
		$estado_sol = $_POST["estado_sol"];
	}

	$where_empresa = "";
	$where_area_solicitante = "";
	$where_ruc = "";
	$where_razon_social = "";
	$where_moneda = "";
	$where_ubigeo = '';
	$where_nombre_tienda = '';
	$query_fecha = '';
	$where_estado_sol = '';
	$where_estado_sol_v2 = '';
	$where_estado_cancelado = "";

	if (!empty($empresa)) {
		$where_empresa = " AND c.empresa_suscribe_id = '" . $empresa . "' ";
	}

	if (!empty($ruc)) {
		$where_ruc = " AND c.ruc LIKE '%" . $ruc . "%' ";
	}

	if (!empty($razon_social)) {
		$where_razon_social = " AND c.razon_social  LIKE '%" . $razon_social . "%' ";
	}

	if (!empty($moneda)) {
		$where_moneda = " AND ce.tipo_moneda_id = '" . $moneda . "'";
	}

	if (!empty($id_departamento)) {
		$where_ubigeo = " AND i.ubigeo_id LIKE '%" . $id_departamento . $id_provincia . $id_distrito . "%'";
	}

	if (!empty($nombre_tienda)) {
		$where_nombre_tienda = " AND c.nombre_tienda LIKE '%" . $nombre_tienda . "%' ";
	}

	if (!empty($fecha_inicio) && !empty($fecha_fin)) {
		$query_fecha = " AND a.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
	} elseif (!empty($fecha_inicio)) {
		$query_fecha = " AND a.created_at >= '$fecha_inicio 00:00:00'";
	} elseif (!empty($fecha_fin)) {
		$query_fecha = " AND a.created_at <= '$fecha_fin 23:59:59'";
	}

	if (!empty($estado_sol)) {
		if ((int) $estado_sol === 99) {
			$where_estado_sol_v2 = " AND a.cancelado_id = 1 ";
		} elseif ((int) $estado_sol === 1) {
			$where_estado_sol = " AND ( a.estado_solicitud_id = 1 OR a.estado_solicitud_id IS NULL ) ";
		} else {
			$where_estado_sol = " AND a.estado_solicitud_id = '" . $estado_sol . "' ";
		}
	}

	if (!empty($estado_sol_v2)) {
		if ((int) $estado_sol_v2 === 1) {
			$where_estado_sol_v2 = " AND ( a.cancelado_id != 1 || a.cancelado_id IS NULL )";
		} elseif ((int) $estado_sol_v2 === 2) {
			$where_estado_sol_v2 = " AND a.cancelado_id = 1 ";
		}
	}

	if ($login["area_id"] == 33) {
		$where_estado_cancelado = " AND ( a.cancelado_id != 1 OR a.cancelado_id IS NULL )";
	}

	$total_query = $mysqli->query("
		SELECT COUNT(c.contrato_id) as num_rows 
		FROM cont_adendas a
		INNER JOIN cont_contrato c ON a.contrato_id = c.contrato_id
		INNER JOIN tbl_usuarios u ON a.user_created_id = u.id
		INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		LEFT JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id AND ce.status = 1 
		LEFT JOIN cont_inmueble i ON c.contrato_id = i.contrato_id AND i.contrato_detalle_id = ce.contrato_detalle_id
		INNER JOIN cont_etapa e ON c.etapa_id = e.etapa_id
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
		WHERE c.tipo_contrato_id = 14
		AND a.procesado = 0
		AND a.status = 1
		" . $where_empresa . "
		" . $where_ruc . "
		" . $where_razon_social . "
		" . $where_moneda . "
		" . $where_ubigeo . "
		" . $where_nombre_tienda . "
		" . $query_fecha . "
		" . $where_estado_sol . "
		" . $where_estado_sol_v2 . "
		" . $where_estado_cancelado . "
		 
		");

	$resultNum = $total_query->fetch_assoc();
	$num_rows = $resultNum['num_rows'];
	$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
	$per_page = 10;
	$numLinks = 10;
	$total_paginate = ceil($num_rows / $per_page);
	$total_paginate = $total_paginate == 0 ? 1 : $total_paginate;
	$page = $page > $total_paginate ? $total_paginate : $page;
	$offset = ($page - 1) * $per_page;
	$paginate = new Pagination('sec_contratos_solicitud_cambiar_de_pagina', $num_rows, $per_page, $numLinks, $page);

	$sel_query = $mysqli->query("
		SELECT
			a.id,
			a.codigo,
			c.contrato_id,
			c.nombre_tienda,
			CONCAT(p.nombre, ' ', IFNULL(p.apellido_paterno,''), ' ', IFNULL(p.apellido_materno,'')) AS solicitante,
			p.area_id,
			a.created_at,
			co.sigla AS sigla_correlativo,
			c.codigo_correlativo,
			a.estado_solicitud_id,
			es.nombre AS nombre_estado_solicitud,
			a.procesado,
			a.cancelado_id,
			(CASE WHEN (c.check_gerencia_proveedor =0 and c.fecha_aprobacion IS NULL AND c.aprobador_id=0) THEN 'Pendiente' ELSE '' END) as aprob, 	
			(CASE WHEN (c.check_gerencia_proveedor =0 and c.fecha_aprobacion IS NOT NULL AND c.aprobador_id=1) THEN 'Aprobado' ELSE '' END) as aprobS, 
			(CASE WHEN (c.check_gerencia_proveedor =0 and c.fecha_aprobacion IS NOT NULL AND c.aprobador_id=0) THEN 'Denegado' ELSE '' END) as aprobN,
			CONCAT(IFNULL(pud.nombre, ''),' ',IFNULL(pud.apellido_paterno, ''),' ',IFNULL(pud.apellido_materno, '')) AS nombre_del_director_a_aprobar,
			CONCAT(IFNULL(puap.nombre, ''),' ',IFNULL(puap.apellido_paterno, ''),' ',IFNULL(puap.apellido_materno, '')) AS nombre_del_aprobador,
			a.director_aprobacion_id,
			a.aprobado_por_id,
			a.aprobado_el,
			a.fecha_cambio_estado_solicitud,
			a.dias_habiles,
			CONCAT(IFNULL(pa.nombre, ''),' ',IFNULL(pa.apellido_paterno, ''),' ',IFNULL(pa.apellido_materno, '')) AS abogado
		FROM 
			cont_adendas a
			INNER JOIN cont_contrato c ON a.contrato_id = c.contrato_id
			INNER JOIN tbl_usuarios u ON a.user_created_id = u.id
			INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
			LEFT JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id AND ce.status = 1
			LEFT JOIN cont_inmueble i ON c.contrato_id = i.contrato_id AND i.contrato_detalle_id = ce.contrato_detalle_id
			INNER JOIN cont_etapa e ON c.etapa_id = e.etapa_id
			LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			LEFT JOIN cont_estado_solicitud es ON es.id = a.estado_solicitud_id
			LEFT JOIN tbl_usuarios ua ON a.abogado_id = ua.id
			LEFT JOIN tbl_personal_apt pa ON ua.personal_id = pa.id
			LEFT JOIN tbl_usuarios ud ON a.director_aprobacion_id = ud.id
			LEFT JOIN tbl_personal_apt pud ON ud.personal_id = pud.id
			LEFT JOIN tbl_usuarios uap ON a.director_aprobacion_id = uap.id
			LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id
		WHERE 
			c.tipo_contrato_id = 14
			AND a.procesado = 0
			AND a.status = 1
			" . $where_empresa . "
			" . $where_ruc . "
			" . $where_razon_social . "
			" . $where_moneda . "
			" . $where_ubigeo . "
			" . $where_nombre_tienda . "
			" . $query_fecha . "
			" . $where_estado_sol . "
			" . $where_estado_sol_v2 . "
			" . $where_estado_cancelado . "
			GROUP BY 
			a.id, a.codigo, c.contrato_id, c.nombre_tienda, 
			p.nombre, p.apellido_paterno, p.apellido_materno, p.area_id, 
			a.created_at, co.sigla, c.codigo_correlativo, a.estado_solicitud_id, 
			es.nombre, a.procesado, a.cancelado_id, c.check_gerencia_proveedor, 
			c.fecha_aprobacion, c.aprobador_id, pud.nombre, pud.apellido_paterno, 
			pud.apellido_materno, puap.nombre, puap.apellido_paterno, puap.apellido_materno, 
			a.director_aprobacion_id, a.aprobado_por_id, a.aprobado_el, 
			a.fecha_cambio_estado_solicitud, a.dias_habiles, pa.nombre, 
			pa.apellido_paterno, pa.apellido_materno
		ORDER BY a.created_at DESC
	 
		");

?>
	<div class="w-100 text-center">
		<h5 style="font-weight: bold; margin: 0px;">SOLICITUDES DE ADENDA DE CONTRATO DE MANDATO</h5>
	</div>
	<br>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed table-bordered tabla_contratos_para_filtro" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th class="text-center">Código</th>

					<th class="text-center">Solicitante</th>
					<th class="text-center">F. Solicitud</th>
					<th class="text-center">Estado</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Fecha de atención</th>
						<th class="text-center">Días de atención</th>
						<th class="text-center">Abogado</th>
					<?php } ?>
					<th class="text-center">Estado Aprobación</th>
					<th class="text-center">Aprobante</th>
					<th class="text-center">Ver Detalle</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th class="text-center">Código</th>

					<th class="text-center">Solicitante</th>
					<th class="text-center">F. Solicitud</th>
					<th class="text-center">Estado</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Fecha de atención</th>
						<th class="text-center">Días de atención</th>
						<th class="text-center">Abogado</th>
					<?php } ?>
					<th class="text-center">Estado Aprobación</th>
					<th class="text-center">Aprobante</th>
					<th class="text-center">Ver Detalle</th>
				</tr>
			</tfoot>
			<tbody>
				<?php
				while ($sel = $sel_query->fetch_assoc()) {
					$nombre_adenda = 'Adenda N° ' . $sel['codigo'] . ' - ' . $sel["sigla_correlativo"] . $sel["codigo_correlativo"];
					$nombre_tienda = $sel["nombre_tienda"];
				?>
					<tr>
						<td class="text-center"><?php echo $nombre_adenda; ?></td>

						<td><?php echo $sel["solicitante"]; ?></td>
						<td><?php echo $sel["created_at"]; ?></td>
						<td class="text-center">
							<?php
							if ($sel["cancelado_id"] == 1) {
								echo '<span class="badge bg-danger text-white">Cancelado</span>';
							} elseif ($area_id == '33') {
								$bg_estado_solicitud = '';
								switch ($sel["estado_solicitud_id"]) {
									case 1:
										$bg_estado_solicitud = 'bg-default';
										break;
									case 2:
										$bg_estado_solicitud = 'bg-info';
										break;
									case 3:
										$bg_estado_solicitud = 'bg-warning';
										break;
								}
							?>
								<span class="badge <?= $bg_estado_solicitud ?> text-white"><?php echo $sel["nombre_estado_solicitud"]; ?></span>
							<?php } else {
								echo '<span class="badge bg-info text-white">En proceso</span>';
							} ?>
						</td>
						<?php if ($area_id == '33') { ?>
							<td class="text-center">
								<?php echo (!is_null($sel['fecha_cambio_estado_solicitud'])) ? $sel['fecha_cambio_estado_solicitud'] : ''; ?>
							</td>
							<td class="text-center">
								<?php echo (!is_null($sel['dias_habiles'])) ? sec_contrato_solicitud_num_dias_habiles_formato($sel['dias_habiles']) : ''; ?>
							</td>
							<td class="text-center"><?php echo $sel["abogado"]; ?></td>
						<?php } ?>
						<td class="text-center">
							<?php
							if (($sel["area_id"] ==  $login["area_id"]) || $usuario_id_login == $sel["director_aprobacion_id"] || $usuario_id_login == $sel["aprobado_por_id"] || $area_id == '6' || ($area_id == '33')) {
								echo $sel["aprob"];
								echo $sel["aprobS"];
								echo $sel["aprobN"];
							}
							?>
						</td>
						<td>
							<?php
							if (($sel["area_id"] ==  $login["area_id"]) || $usuario_id_login == $sel["director_aprobacion_id"] || $usuario_id_login == $sel["aprobado_por_id"] || $area_id == '6' || ($area_id == '33')) {

								if (trim($sel["aprob"]) == "Pendiente") {
									echo $sel["nombre_del_director_a_aprobar"];
								} else {
									echo $sel["nombre_del_aprobador"];
								}
							}
							?>
						</td>

						<td class="text-center">
							<a class="btn btn-rounded btn-warning btn-sm"
								href="./?sec_id=<?php echo $sec_id; ?>&amp;sub_sec_id=detalle_adenda_mandato&id=<?php echo $sel["id"]; ?>"
								title="Editar solicitud">
								<i class="fa fa-pencil"></i>
							</a>
							<a class="btn btn-rounded btn-primary btn-sm"
								href="./?sec_id=<?php echo $sec_id; ?>&amp;sub_sec_id=detalle_solicitud_mandato&id=<?php echo $sel["contrato_id"]; ?>&adenda_id=<?php echo $sel["id"]; ?>"
								title="Ver detalle">
								<i class="fa fa-eye"></i>
							</a>
							<?php if ($sel["procesado"] != 1 && $sel["cancelado_id"] != 1 && $sel["area_id"] == $area_id_login) { ?>
								<a class="btn btn-rounded btn-danger btn-sm"
									onclick="sec_contrato_solicitud_cancelar_solicitud_modal(1, <?php echo $sel["id"]; ?>, '<?php echo $nombre_adenda . ' ' . $nombre_tienda; ?>')"
									title="Eliminar Solicitud">
									<i class="fa fa-close"></i>
								</a>
							<?php } ?>
						</td>
					</tr>
				<?php
				}
				?>
			</tbody>
		</table>
	</div>



<?php
}

if ($_POST['action'] == "listar_adenda_de_mutuo_dinero") {
	$menu_consultar = $_POST['menu_consultar'];
	$area_id = $_POST['area_id'];
	$cargo_id = $_POST['cargo_id'];
	$menu_id = $_POST['menu_id'];
	$empresa = $_POST['empresa'];
	$area_solicitante = $_POST['area'];
	$ruc = $_POST['ruc'];
	$razon_social = $_POST['razon_social'];
	$moneda = $_POST['moneda'];
	$nombre_tienda = trim($_POST['nombre_tienda']);
	$id_departamento = $_POST['id_departamento'];
	$id_provincia = $_POST['id_provincia'];
	$id_distrito = $_POST['id_distrito'];
	$fecha_inicio = trim($_POST['fecha_inicio']);
	$fecha_fin = trim($_POST['fecha_fin']);
	$usuario_permisos = json_decode($_POST['usuario_permisos'], true);
	$sec_id = $_POST['sec_id'];
	$estado_sol_v2 = trim($_POST['estado_sol_v2']);

	$estado_sol = '';
	if (isset($_POST["estado_sol"])) {
		$estado_sol = $_POST["estado_sol"];
	}

	$where_empresa = "";
	$where_area_solicitante = "";
	$where_ruc = "";
	$where_razon_social = "";
	$where_moneda = "";
	$where_ubigeo = '';
	$where_nombre_tienda = '';
	$query_fecha = '';
	$where_estado_sol = '';
	$where_estado_sol_v2 = '';
	$where_estado_cancelado = "";

	if (!empty($empresa)) {
		$where_empresa = " AND c.empresa_suscribe_id = '" . $empresa . "' ";
	}

	if (!empty($ruc)) {
		$where_ruc = " AND c.ruc LIKE '%" . $ruc . "%' ";
	}

	if (!empty($razon_social)) {
		$where_razon_social = " AND c.razon_social  LIKE '%" . $razon_social . "%' ";
	}

	if (!empty($moneda)) {
		$where_moneda = " AND ce.tipo_moneda_id = '" . $moneda . "'";
	}

	if (!empty($id_departamento)) {
		$where_ubigeo = " AND i.ubigeo_id LIKE '%" . $id_departamento . $id_provincia . $id_distrito . "%'";
	}

	if (!empty($nombre_tienda)) {
		$where_nombre_tienda = " AND c.nombre_tienda LIKE '%" . $nombre_tienda . "%' ";
	}

	if (!empty($fecha_inicio) && !empty($fecha_fin)) {
		$query_fecha = " AND a.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
	} elseif (!empty($fecha_inicio)) {
		$query_fecha = " AND a.created_at >= '$fecha_inicio 00:00:00'";
	} elseif (!empty($fecha_fin)) {
		$query_fecha = " AND a.created_at <= '$fecha_fin 23:59:59'";
	}

	if (!empty($estado_sol)) {
		if ((int) $estado_sol === 99) {
			$where_estado_sol_v2 = " AND a.cancelado_id = 1 ";
		} elseif ((int) $estado_sol === 1) {
			$where_estado_sol = " AND ( a.estado_solicitud_id = 1 OR a.estado_solicitud_id IS NULL ) ";
		} else {
			$where_estado_sol = " AND a.estado_solicitud_id = '" . $estado_sol . "' ";
		}
	}

	if (!empty($estado_sol_v2)) {
		if ((int) $estado_sol_v2 === 1) {
			$where_estado_sol_v2 = " AND ( a.cancelado_id != 1 || a.cancelado_id IS NULL )";
		} elseif ((int) $estado_sol_v2 === 2) {
			$where_estado_sol_v2 = " AND a.cancelado_id = 1 ";
		}
	}

	if ($login["area_id"] == 33) {
		$where_estado_cancelado = " AND ( a.cancelado_id != 1 OR a.cancelado_id IS NULL )";
	}

	$total_query = $mysqli->query("
		SELECT COUNT(c.contrato_id) as num_rows 
		FROM cont_adendas a
		INNER JOIN cont_contrato c ON a.contrato_id = c.contrato_id
		INNER JOIN tbl_usuarios u ON a.user_created_id = u.id
		INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		LEFT JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id AND ce.status = 1 
		LEFT JOIN cont_inmueble i ON c.contrato_id = i.contrato_id AND i.contrato_detalle_id = ce.contrato_detalle_id
		INNER JOIN cont_etapa e ON c.etapa_id = e.etapa_id
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
		WHERE c.tipo_contrato_id = 15
		AND a.procesado = 0
		AND a.status = 1
		" . $where_empresa . "
		" . $where_ruc . "
		" . $where_razon_social . "
		" . $where_moneda . "
		" . $where_ubigeo . "
		" . $where_nombre_tienda . "
		" . $query_fecha . "
		" . $where_estado_sol . "
		" . $where_estado_sol_v2 . "
		" . $where_estado_cancelado . "
		 
		");

	$resultNum = $total_query->fetch_assoc();
	$num_rows = $resultNum['num_rows'];
	$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
	$per_page = 10;
	$numLinks = 10;
	$total_paginate = ceil($num_rows / $per_page);
	$total_paginate = $total_paginate == 0 ? 1 : $total_paginate;
	$page = $page > $total_paginate ? $total_paginate : $page;
	$offset = ($page - 1) * $per_page;
	$paginate = new Pagination('sec_contratos_solicitud_cambiar_de_pagina', $num_rows, $per_page, $numLinks, $page);

	$sel_query = $mysqli->query("
		SELECT
			a.id,
			a.codigo,
			c.contrato_id,
			c.nombre_tienda,
			CONCAT(p.nombre, ' ', IFNULL(p.apellido_paterno,''), ' ', IFNULL(p.apellido_materno,'')) AS solicitante,
			p.area_id,
			a.created_at,
			co.sigla AS sigla_correlativo,
			c.codigo_correlativo,
			a.estado_solicitud_id,
			es.nombre AS nombre_estado_solicitud,
			a.procesado,
			a.cancelado_id,
			(CASE WHEN (c.check_gerencia_proveedor =0 and c.fecha_aprobacion IS NULL AND c.aprobador_id=0) THEN 'Pendiente' ELSE '' END) as aprob, 	
			(CASE WHEN (c.check_gerencia_proveedor =0 and c.fecha_aprobacion IS NOT NULL AND c.aprobador_id=1) THEN 'Aprobado' ELSE '' END) as aprobS, 
			(CASE WHEN (c.check_gerencia_proveedor =0 and c.fecha_aprobacion IS NOT NULL AND c.aprobador_id=0) THEN 'Denegado' ELSE '' END) as aprobN,
			CONCAT(IFNULL(pud.nombre, ''),' ',IFNULL(pud.apellido_paterno, ''),' ',IFNULL(pud.apellido_materno, '')) AS nombre_del_director_a_aprobar,
			CONCAT(IFNULL(puap.nombre, ''),' ',IFNULL(puap.apellido_paterno, ''),' ',IFNULL(puap.apellido_materno, '')) AS nombre_del_aprobador,
			a.director_aprobacion_id,
			a.aprobado_por_id,
			a.aprobado_el,
			a.fecha_cambio_estado_solicitud,
			a.dias_habiles,
			CONCAT(IFNULL(pa.nombre, ''),' ',IFNULL(pa.apellido_paterno, ''),' ',IFNULL(pa.apellido_materno, '')) AS abogado
		FROM 
			cont_adendas a
			INNER JOIN cont_contrato c ON a.contrato_id = c.contrato_id
			INNER JOIN tbl_usuarios u ON a.user_created_id = u.id
			INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
			LEFT JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id AND ce.status = 1
			LEFT JOIN cont_inmueble i ON c.contrato_id = i.contrato_id AND i.contrato_detalle_id = ce.contrato_detalle_id
			INNER JOIN cont_etapa e ON c.etapa_id = e.etapa_id
			LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			LEFT JOIN cont_estado_solicitud es ON es.id = a.estado_solicitud_id
			LEFT JOIN tbl_usuarios ua ON a.abogado_id = ua.id
			LEFT JOIN tbl_personal_apt pa ON ua.personal_id = pa.id
			LEFT JOIN tbl_usuarios ud ON a.director_aprobacion_id = ud.id
			LEFT JOIN tbl_personal_apt pud ON ud.personal_id = pud.id
			LEFT JOIN tbl_usuarios uap ON a.director_aprobacion_id = uap.id
			LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id
		WHERE 
			c.tipo_contrato_id = 15
			AND a.procesado = 0
			AND a.status = 1
			" . $where_empresa . "
			" . $where_ruc . "
			" . $where_razon_social . "
			" . $where_moneda . "
			" . $where_ubigeo . "
			" . $where_nombre_tienda . "
			" . $query_fecha . "
			" . $where_estado_sol . "
			" . $where_estado_sol_v2 . "
			" . $where_estado_cancelado . "
			GROUP BY 
			a.id, a.codigo, c.contrato_id, c.nombre_tienda, 
			p.nombre, p.apellido_paterno, p.apellido_materno, p.area_id, 
			a.created_at, co.sigla, c.codigo_correlativo, a.estado_solicitud_id, 
			es.nombre, a.procesado, a.cancelado_id, c.check_gerencia_proveedor, 
			c.fecha_aprobacion, c.aprobador_id, pud.nombre, pud.apellido_paterno, 
			pud.apellido_materno, puap.nombre, puap.apellido_paterno, puap.apellido_materno, 
			a.director_aprobacion_id, a.aprobado_por_id, a.aprobado_el, 
			a.fecha_cambio_estado_solicitud, a.dias_habiles, pa.nombre, 
			pa.apellido_paterno, pa.apellido_materno
		ORDER BY a.created_at DESC
	 
		");

?>
	<div class="w-100 text-center">
		<h5 style="font-weight: bold; margin: 0px;">SOLICITUDES DE ADENDA DE CONTRATO DE MUTUO DE DINERO</h5>
	</div>
	<br>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed table-bordered tabla_contratos_para_filtro" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th class="text-center">Código</th>

					<th class="text-center">Solicitante</th>
					<th class="text-center">F. Solicitud</th>
					<th class="text-center">Estado</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Fecha de atención</th>
						<th class="text-center">Días de atención</th>
						<th class="text-center">Abogado</th>
					<?php } ?>
					<th class="text-center">Estado Aprobación</th>
					<th class="text-center">Aprobante</th>
					<th class="text-center">Ver Detalle</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th class="text-center">Código</th>

					<th class="text-center">Solicitante</th>
					<th class="text-center">F. Solicitud</th>
					<th class="text-center">Estado</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Fecha de atención</th>
						<th class="text-center">Días de atención</th>
						<th class="text-center">Abogado</th>
					<?php } ?>
					<th class="text-center">Estado Aprobación</th>
					<th class="text-center">Aprobante</th>
					<th class="text-center">Ver Detalle</th>
				</tr>
			</tfoot>
			<tbody>
				<?php
				while ($sel = $sel_query->fetch_assoc()) {
					$nombre_adenda = 'Adenda N° ' . $sel['codigo'] . ' - ' . $sel["sigla_correlativo"] . $sel["codigo_correlativo"];
					$nombre_tienda = $sel["nombre_tienda"];
				?>
					<tr>
						<td class="text-center"><?php echo $nombre_adenda; ?></td>

						<td><?php echo $sel["solicitante"]; ?></td>
						<td><?php echo $sel["created_at"]; ?></td>
						<td class="text-center">
							<?php
							if ($sel["cancelado_id"] == 1) {
								echo '<span class="badge bg-danger text-white">Cancelado</span>';
							} elseif ($area_id == '33') {
								$bg_estado_solicitud = '';
								switch ($sel["estado_solicitud_id"]) {
									case 1:
										$bg_estado_solicitud = 'bg-default';
										break;
									case 2:
										$bg_estado_solicitud = 'bg-info';
										break;
									case 3:
										$bg_estado_solicitud = 'bg-warning';
										break;
								}
							?>
								<span class="badge <?= $bg_estado_solicitud ?> text-white"><?php echo $sel["nombre_estado_solicitud"]; ?></span>
							<?php } else {
								echo '<span class="badge bg-info text-white">En proceso</span>';
							} ?>
						</td>
						<?php if ($area_id == '33') { ?>
							<td class="text-center">
								<?php echo (!is_null($sel['fecha_cambio_estado_solicitud'])) ? $sel['fecha_cambio_estado_solicitud'] : ''; ?>
							</td>
							<td class="text-center">
								<?php echo (!is_null($sel['dias_habiles'])) ? sec_contrato_solicitud_num_dias_habiles_formato($sel['dias_habiles']) : ''; ?>
							</td>
							<td class="text-center"><?php echo $sel["abogado"]; ?></td>
						<?php } ?>
						<td class="text-center">
							<?php
							if (($sel["area_id"] ==  $login["area_id"]) || $usuario_id_login == $sel["director_aprobacion_id"] || $usuario_id_login == $sel["aprobado_por_id"] || $area_id == '6' || ($area_id == '33')) {
								echo $sel["aprob"];
								echo $sel["aprobS"];
								echo $sel["aprobN"];
							}
							?>
						</td>
						<td>
							<?php
							if (($sel["area_id"] ==  $login["area_id"]) || $usuario_id_login == $sel["director_aprobacion_id"] || $usuario_id_login == $sel["aprobado_por_id"] || $area_id == '6' || ($area_id == '33')) {

								if (trim($sel["aprob"]) == "Pendiente") {
									echo $sel["nombre_del_director_a_aprobar"];
								} else {
									echo $sel["nombre_del_aprobador"];
								}
							}
							?>
						</td>

						<td class="text-center">
							<a class="btn btn-rounded btn-warning btn-sm"
								href="./?sec_id=<?php echo $sec_id; ?>&amp;sub_sec_id=detalle_adenda_mutuo_dinero&id=<?php echo $sel["id"]; ?>"
								title="Editar solicitud">
								<i class="fa fa-pencil"></i>
							</a>
							<a class="btn btn-rounded btn-primary btn-sm"
								href="./?sec_id=<?php echo $sec_id; ?>&amp;sub_sec_id=detalle_solicitud_mutuo_dinero&id=<?php echo $sel["contrato_id"]; ?>&adenda_id=<?php echo $sel["id"]; ?>"
								title="Ver detalle">
								<i class="fa fa-eye"></i>
							</a>
							<?php if ($sel["procesado"] != 1 && $sel["cancelado_id"] != 1 && $sel["area_id"] == $area_id_login) { ?>
								<a class="btn btn-rounded btn-danger btn-sm"
									onclick="sec_contrato_solicitud_cancelar_solicitud_modal(1, <?php echo $sel["id"]; ?>, '<?php echo $nombre_adenda . ' ' . $nombre_tienda; ?>')"
									title="Eliminar Solicitud">
									<i class="fa fa-close"></i>
								</a>
							<?php } ?>
						</td>
					</tr>
				<?php
				}
				?>
			</tbody>
		</table>
	</div>



<?php
}


if ($_POST['action'] == "listar_adenda_de_proveedor") {
	$menu_consultar = $_POST['menu_consultar'];
	$area_id = $_POST['area_id'];
	$cargo_id = $_POST['cargo_id'];
	$menu_id = $_POST['menu_id'];
	$empresa = $_POST['empresa'];
	$area_solicitante = $_POST['area'];
	$ruc = $_POST['ruc'];
	$razon_social = $_POST['razon_social'];
	$fecha_inicio = trim($_POST['fecha_inicio']);
	$fecha_fin = trim($_POST['fecha_fin']);
	$usuario_permisos = json_decode($_POST['usuario_permisos'], true);
	$sec_id = $_POST['sec_id'];
	$estado_sol_v2 = trim($_POST['estado_sol_v2']);
	$estado_aprobacion = trim($_POST['estado_aprobacion']);
	$fecha_inicio_aprobacion = trim($_POST['fecha_inicio_aprobacion']);
	$fecha_fin_aprobacion = trim($_POST['fecha_fin_aprobacion']);
	$director_aprobacion_id = trim($_POST['director_aprobacion_id']);

	$estado_sol = '';
	if (isset($_POST["estado_sol"])) {
		$estado_sol = $_POST["estado_sol"];
	}

	$where_area = "";
	$where_empresa = "";
	$where_area_solicitante = "";
	$where_ruc = "";
	$where_razon_social = "";
	$where_estado_sol = '';
	$where_estado_sol_v2 = '';
	$where_estado_cancelado = "";
	$where_estado_aprobacion = '';
	$where_director_aprobacion = '';

	$permiso_cancelar_adenda = false;
	if ((array_key_exists($menu_consultar, $usuario_permisos) && in_array("cancelar_solicitud_adenda", $usuario_permisos[$menu_consultar]))) {
		$permiso_cancelar_adenda = true;
	}

	if (!(array_key_exists($menu_consultar, $usuario_permisos) && (in_array("ver_todo_solicitud", $usuario_permisos[$menu_consultar]) || in_array("see_all", $usuario_permisos[$menu_consultar])))) {
		if ($area_id == 33) {
			$where_area = " AND ((a.requiere_aprobacion_id = 1 AND a.aprobado_estado_id = 1 ) OR (a.requiere_aprobacion_id = 0) OR (a.requiere_aprobacion_id IS NULL) OR (a.requiere_aprobacion_id = 1 AND p.area_id = 33 ) )";
		} else {
			$where_area = " AND p.area_id = " . $area_id;
		}
	}

	if (!empty($empresa)) {
		$where_empresa = " AND c.empresa_suscribe_id = '" . $empresa . "' ";
	}

	if (!empty($area_solicitante)) {
		$where_area_solicitante = " AND p.area_id = '" . $area_solicitante . "' ";
	}

	if (!empty($ruc)) {
		$where_ruc = " AND c.ruc LIKE '%" . $ruc . "%' ";
	}

	if (!empty($razon_social)) {
		$where_razon_social = " AND c.razon_social  LIKE '%" . $razon_social . "%' ";
	}

	$query_fecha = '';
	if (!empty($fecha_inicio) && !empty($fecha_fin)) {
		$query_fecha = " AND a.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
	} elseif (!empty($fecha_inicio)) {
		$query_fecha = " AND a.created_at >= '$fecha_inicio 00:00:00'";
	} elseif (!empty($fecha_fin)) {
		$query_fecha = " AND a.created_at <= '$fecha_fin 23:59:59'";
	}

	if (!empty($estado_sol)) {
		if ((int) $estado_sol === 99) {
			$where_estado_sol_v2 = " AND a.cancelado_id = 1 ";
		} elseif ((int) $estado_sol === 1) {
			$where_estado_sol = " AND ( a.estado_solicitud_id = 1 OR a.estado_solicitud_id IS NULL ) ";
		} else {
			$where_estado_sol = " AND a.estado_solicitud_id = '" . $estado_sol . "' ";
		}
	}

	if (!empty($estado_sol_v2)) {
		if ((int) $estado_sol_v2 === 1) {
			$where_estado_sol_v2 = " AND ( a.cancelado_id != 1 || a.cancelado_id IS NULL )";
		} elseif ((int) $estado_sol_v2 === 2) {
			$where_estado_sol_v2 = " AND a.cancelado_id = 1 ";
		}
	}

	if ($login["area_id"] == 33) {
		$where_estado_cancelado = " AND ( a.cancelado_id != 1 OR a.cancelado_id IS NULL )";
	}

	$where_fecha_aprobacion = '';
	if (!empty($fecha_inicio_aprobacion) && !empty($fecha_fin_aprobacion)) {
		$where_fecha_aprobacion = " AND a.aprobado_el between '$fecha_inicio_aprobacion 00:00:00' AND '$fecha_fin_aprobacion 23:59:59'";
	} elseif (!empty($fecha_inicio_aprobacion)) {
		$where_fecha_aprobacion = " AND a.aprobado_el >= '$fecha_inicio_aprobacion 00:00:00'";
	} elseif (!empty($fecha_fin_aprobacion)) {
		$where_fecha_aprobacion = " AND a.aprobado_el <= '$fecha_fin_aprobacion 23:59:59'";
	}

	if (!empty($director_aprobacion_id)) {
		$where_director_aprobacion = " AND ( ( a.director_aprobacion_id = $director_aprobacion_id AND (a.requiere_aprobacion_id = 1 AND a.aprobado_estado_id IS NULL) ) OR a.aprobado_por_id = $director_aprobacion_id ) ";
	}

	if ($estado_aprobacion == 1) {
		$where_estado_aprobacion = " AND (a.aprobado_estado_id = 1)";
	} elseif ($estado_aprobacion == 2) {
		$where_estado_aprobacion = " AND (a.aprobado_estado_id = 0)";
	} elseif ($estado_aprobacion == 3) {
		$where_estado_aprobacion = " AND (a.requiere_aprobacion_id = 1)";
	}

	$total_query = $mysqli->query("
		SELECT COUNT(c.contrato_id) as num_rows 
		FROM cont_adendas a
		INNER JOIN cont_contrato c ON a.contrato_id = c.contrato_id
		INNER JOIN tbl_usuarios u ON a.user_created_id = u.id
		INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		INNER JOIN tbl_areas ar ON p.area_id = ar.id
		INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id AND r.status = 1
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
		WHERE c.tipo_contrato_id = 2
		AND a.procesado = 0
		AND a.status = 1
		" . $where_empresa . "
		" . $where_area_solicitante . "
		" . $where_ruc . "
		" . $where_razon_social . "
		" . $where_area . "
		" . $query_fecha . "
		" . $where_estado_sol . "
		" . $where_estado_sol_v2 . "
		" . $where_estado_cancelado . "
		" . $where_director_aprobacion . "
		" . $where_estado_aprobacion . "
		" . $where_fecha_aprobacion . "
		ORDER BY c.created_at DESC
		");

	$resultNum = $total_query->fetch_assoc();
	$num_rows = $resultNum['num_rows'];
	$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
	$per_page = 10;
	$numLinks = 10;
	$total_paginate = ceil($num_rows / $per_page);
	$total_paginate = $total_paginate == 0 ? 1 : $total_paginate;
	$page = $page > $total_paginate ? $total_paginate : $page;
	$offset = ($page - 1) * $per_page;
	$paginate = new Pagination('sec_contratos_solicitud_cambiar_de_pagina', $num_rows, $per_page, $numLinks, $page);

	$sel_query = $mysqli->query("
		SELECT 
			a.id,
			a.codigo,
			c.contrato_id,
			c.nombre_tienda,
			CONCAT(IFNULL(p.nombre, ''), ' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS solicitante,
			p.area_id,
			a.created_at,
			ar.nombre AS area,
			c.razon_social AS parte,
			r.nombre AS empresa_suscribe,
			c.status,
			c.detalle_servicio,
			co.sigla AS sigla_correlativo,
			c.codigo_correlativo,
			a.estado_solicitud_id,
			es.nombre AS nombre_estado_solicitud,
			(CASE WHEN (a.requiere_aprobacion_id = 1 AND a.aprobado_estado_id IS NULL) THEN 'Pendiente' ELSE '' END) as aprob, 	
			(CASE WHEN (a.aprobado_estado_id = 1) THEN 'Aprobado' ELSE '' END) as aprobS, 
			(CASE WHEN (a.aprobado_estado_id = 0) THEN 'Denegado' ELSE '' END) as aprobN, 
			a.procesado,
			a.cancelado_id,
			CONCAT(IFNULL(pud.nombre, ''),' ',IFNULL(pud.apellido_paterno, ''),' ',IFNULL(pud.apellido_materno, '')) AS nombre_del_director_a_aprobar,
			CONCAT(IFNULL(puap.nombre, ''),' ',IFNULL(puap.apellido_paterno, ''),' ',IFNULL(puap.apellido_materno, '')) AS nombre_del_aprobador,
			a.director_aprobacion_id,
			a.aprobado_por_id,
			a.aprobado_el,
			a.fecha_cambio_estado_solicitud,
			a.dias_habiles,
			CONCAT(IFNULL(pa.nombre, ''),' ',IFNULL(pa.apellido_paterno, ''),' ',IFNULL(pa.apellido_materno, '')) AS abogado
		FROM 
			cont_adendas a
			INNER JOIN cont_contrato c ON a.contrato_id = c.contrato_id
			INNER JOIN tbl_usuarios u ON a.user_created_id = u.id
			INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
			INNER JOIN tbl_areas ar ON p.area_id = ar.id
			INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id AND r.status = 1
			LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			LEFT JOIN cont_estado_solicitud es ON es.id = a.estado_solicitud_id
			LEFT JOIN tbl_usuarios ud ON a.director_aprobacion_id = ud.id
			LEFT JOIN tbl_personal_apt pud ON ud.personal_id = pud.id
			LEFT JOIN tbl_usuarios uap ON a.aprobado_por_id = uap.id
			LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id
			LEFT JOIN tbl_usuarios ua ON c.abogado_id = ua.id
			LEFT JOIN tbl_personal_apt pa ON ua.personal_id = pa.id
		WHERE 
			c.tipo_contrato_id = 2
			AND a.procesado = 0
			AND a.status = 1
			" . $where_empresa . "
			" . $where_area_solicitante . "
			" . $where_ruc . "
			" . $where_razon_social . "
			" . $where_area . "
			" . $query_fecha . "
			" . $where_estado_sol . "
			" . $where_estado_sol_v2 . "
			" . $where_estado_cancelado . "
			" . $where_director_aprobacion . "
			" . $where_estado_aprobacion . "
			" . $where_fecha_aprobacion . "
		ORDER BY a.created_at DESC
		 
		");

?>
	<div class="w-100 text-center">
		<h5 style="font-weight: bold; margin: 0px;">SOLICITUDES DE ADENDA DE CONTRATO DE PROVEEDOR</h5>
	</div>
	<br>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed table-bordered tabla_contratos_para_filtro" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th class="text-center">Código</th>
					<th class="text-center">Área solicitante</th>
					<th class="text-center">Solicitante</th>
					<th class="text-center">Detalle servicio</th>
					<th class="text-center">Empresa que suscribe el contrato</th>
					<th class="text-center">Proveedor</th>
					<th class="text-center">F. Solicitud</th>
					<th class="text-center">Estado Solicitud</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Fecha de atención</th>
						<th class="text-center">Días de atención</th>
					<?php } ?>
					<th class="text-center">Estado Aprobación</th>
					<th class="text-center">Aprobante</th>
					<th class="text-center">F. Aprobación</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Abogado</th>
						<th class="text-center">Seguimiento</th>
					<?php } ?>
					<th class="text-center">Ver Detalle</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th class="text-center">Código</th>
					<th class="text-center">Área solicitante</th>
					<th class="text-center">Solicitante</th>
					<th class="text-center">Detalle servicio</th>
					<th class="text-center">Empresa que suscribe el contrato</th>
					<th class="text-center">Proveedor</th>
					<th class="text-center">F. Solicitud</th>
					<th class="text-center">Estado Solicitud</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Fecha de atención</th>
						<th class="text-center">Días de atención</th>
					<?php } ?>
					<th class="text-center">Estado Aprobación</th>
					<th class="text-center">Aprobante</th>
					<th class="text-center">F. Aprobación</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Abogado</th>
						<th class="text-center">Seguimiento</th>
					<?php } ?>
					<th class="text-center">Ver Detalle</th>
				</tr>
			</tfoot>
			<tbody>
				<?php
				while ($sel = $sel_query->fetch_assoc()) {
					$nombre_adenda = 'Adenda N° ' . $sel['codigo'] . ' - ' . $sel["sigla_correlativo"] . $sel["codigo_correlativo"];
					$parte = $sel["parte"];

					$detalle_servicio = trim($sel["detalle_servicio"]);

					if (strlen($detalle_servicio) > 50) {
						$detalle_servicio = substr($detalle_servicio, 0, 50) . ' ...';
					}
				?>
					<tr>
						<td class="text-center"><?php echo $nombre_adenda; ?></td>
						<td><?php echo $sel["area"]; ?></td>
						<td><?php echo $sel["solicitante"]; ?></td>
						<td><?php echo $detalle_servicio; ?></td>
						<td><?php echo $sel["empresa_suscribe"]; ?></td>
						<td><?php echo $parte; ?></td>
						<td><?php echo $sel["created_at"]; ?></td>
						<td class="text-center">
							<?php
							if ($sel["cancelado_id"] == 1) {
								echo '<span class="badge bg-danger text-white">Cancelado</span>';
							} elseif ($area_id == '33') {

								$bg_estado_solicitud = '';
								switch ($sel["estado_solicitud_id"]) {
									case 1:
										$bg_estado_solicitud = 'bg-default';
										break;
									case 2:
										$bg_estado_solicitud = 'bg-info';
										break;
									case 3:
										$bg_estado_solicitud = 'bg-warning';
										break;
								}
							?>
								<span class="badge <?= $bg_estado_solicitud ?> text-white"><?php echo $sel["nombre_estado_solicitud"]; ?></span>
							<?php } else {
								echo '<span class="badge bg-info text-white">En proceso</span>';
							} ?>
						</td>
						<?php if ($area_id == '33') { ?>
							<td class="text-center">
								<?php echo (!is_null($sel['fecha_cambio_estado_solicitud'])) ? $sel['fecha_cambio_estado_solicitud'] : ''; ?>
							</td>
							<td class="text-center">
								<?php echo (!is_null($sel['dias_habiles'])) ? sec_contrato_solicitud_num_dias_habiles_formato($sel['dias_habiles']) : ''; ?>
							</td>
						<?php } ?>
						<td class="text-center">
							<?php
							if (($sel["area_id"] ==  $login["area_id"]) || $usuario_id_login == $sel["director_aprobacion_id"] || $usuario_id_login == $sel["aprobado_por_id"] || $area_id == '6' || ($area_id == '33') || true) {
								echo $sel["aprob"];
								echo $sel["aprobS"];
								echo $sel["aprobN"];
							}
							?>
						</td>
						<td>
							<?php
							if (($sel["area_id"] ==  $login["area_id"]) || $usuario_id_login == $sel["director_aprobacion_id"] || $usuario_id_login == $sel["aprobado_por_id"] || $area_id == '6' || ($area_id == '33')) {

								if (trim($sel["aprob"]) == "Pendiente") {
									echo $sel["nombre_del_director_a_aprobar"];
								} else {
									echo $sel["nombre_del_aprobador"];
								}
							}
							?>
						</td>
						<td>
							<?php if (($sel["area_id"] ==  $login["area_id"]) || $usuario_id_login == $sel["director_aprobacion_id"] || $usuario_id_login == $sel["aprobado_por_id"] || $area_id == '6' || ($area_id == '33')) {
								echo $sel["aprobado_el"];
							}
							?>
						</td>
						<?php if ($area_id == '33') { ?>
							<td><?php echo $sel["abogado"]; ?></td>
							<?php
							$seg_proc = new SeguimientoProceso();
							$data_sp['tipo_documento_id'] = 2;
							$data_sp['proceso_id'] = $sel['id'];
							$data_sp['proceso_detalle_id'] = 0;
							$etapa_seguimiento = $seg_proc->obtener_ultimo_seguimiento($data_sp);
							?>
							<td><?php echo $etapa_seguimiento; ?></td>
						<?php } ?>
						<td class="text-center">
							<a class="btn btn-rounded btn-warning btn-sm"
								href="./?sec_id=<?php echo $sec_id; ?>&amp;sub_sec_id=detalle_adenda_proveedor&id=<?php echo $sel["id"]; ?>"
								title="Editar solicitud">
								<i class="fa fa-pencil"></i>
							</a>
							<a class="btn btn-rounded btn-primary btn-sm"
								href="./?sec_id=<?php echo $sec_id; ?>&amp;sub_sec_id=detalleSolicitudProveedor&id=<?php echo $sel["contrato_id"]; ?>&adenda_id=<?php echo $sel["id"]; ?>"
								title="Ver detalle">
								<i class="fa fa-eye"></i>
							</a>
							<?php if ($sel["procesado"] != 1 && $sel["cancelado_id"] != 1 && ($sel["area_id"] == $area_id_login || $permiso_cancelar_adenda)) { ?>
								<a class="btn btn-rounded btn-danger btn-sm"
									onclick="sec_contrato_solicitud_cancelar_solicitud_modal(1, <?php echo $sel["id"]; ?>, '<?php echo $nombre_adenda . ' ' . $parte; ?>' )"
									title="Eliminar Solicitud">
									<i class="fa fa-close"></i>
								</a>
							<?php } ?>
						</td>
					</tr>
				<?php
				}
				?>
			</tbody>
		</table>
	</div>

<?php

}

if ($_POST['action'] == "listar_acuerdo_de_confidencialidad") {
	$menu_consultar = $_POST['menu_consultar'];
	$area_id = $_POST['area_id'];
	$cargo_id = $_POST['cargo_id'];
	$menu_id = $_POST['menu_id'];
	$usuario_permisos = json_decode($_POST['usuario_permisos'], true);
	$sec_id = $_POST['sec_id'];

	$estado_sol = '';
	if (isset($_POST["estado_sol"])) {
		$estado_sol = $_POST["estado_sol"];
	}

	$empresa = $_POST['empresa'];
	$area_solicitante = $_POST['area'];
	$ruc = $_POST['ruc'];
	$razon_social = $_POST['razon_social'];
	$moneda = $_POST['moneda'];
	$fecha_inicio = trim($_POST['fecha_inicio']);
	$fecha_fin = trim($_POST['fecha_fin']);
	$estado_sol_v2 = trim($_POST['estado_sol_v2']);
	$estado_aprobacion = trim($_POST['estado_aprobacion']);
	$fecha_inicio_aprobacion = trim($_POST['fecha_inicio_aprobacion']);
	$fecha_fin_aprobacion = trim($_POST['fecha_fin_aprobacion']);
	$director_aprobacion_id = trim($_POST['director_aprobacion_id']);

	$where_empresa = "";
	$where_area_solicitante = "";
	$where_ruc = "";
	$where_razon_social = "";
	$where_moneda = "";
	$query_fecha = '';
	$where_estado_sol = '';
	$where_estado_cancelado = "";
	$where_estado_sol_v2 = '';
	$where_estado_aprobacion = '';
	$where_director_aprobacion = '';

	if (!empty($fecha_inicio) && !empty($fecha_fin)) {
		$query_fecha = " AND c.created_at between '" . $fecha_inicio . "' AND '" . $fecha_fin . "'";
	}

	if (!empty($estado_sol)) {
		if ((int) $estado_sol === 99) {
			$where_estado_sol_v2 = " AND c.cancelado_id = 1 ";
		} elseif ((int) $estado_sol === 1) {
			$where_estado_sol = " AND ( c.estado_solicitud = 1 OR c.estado_solicitud IS NULL ) ";
		} else {
			$where_estado_sol = " AND c.estado_solicitud = '" . $estado_sol . "' ";
		}
	}

	if (!empty($estado_sol_v2)) {
		if ((int) $estado_sol_v2 === 1) {
			$where_estado_sol_v2 = " AND ( c.cancelado_id != 1 || c.cancelado_id IS NULL )";
		} elseif ((int) $estado_sol_v2 === 2) {
			$where_estado_sol_v2 = " AND c.cancelado_id = 1 ";
		}
	}

	$where_area = "";

	if (array_key_exists($menu_consultar, $usuario_permisos) && (in_array("ver_todo_solicitud", $usuario_permisos[$menu_consultar]) || in_array("see_all", $usuario_permisos[$menu_consultar]))) {
		$where_area = "";
	} else {
		if ($area_id == 33) {
			$where_area = " AND ( c.check_gerencia_proveedor = 0 OR c.aprobacion_gerencia_proveedor = 1 OR p.area_id = 33 ) ";
		} else {
			$where_area = " AND a.id =" . $area_id;
		}
	}

	if ($login["area_id"] == 33) {
		$where_estado_cancelado = " AND ( c.cancelado_id != 1 OR c.cancelado_id IS NULL )";
	}

	if (!empty($empresa)) {
		$where_empresa = " AND c.empresa_suscribe_id = '" . $empresa . "' ";
	}

	if (!empty($area_solicitante)) {
		$where_area_solicitante = " AND a.id = '" . $area_solicitante . "' ";
	}

	if (!empty($ruc)) {
		$where_ruc = " AND c.ruc LIKE '%" . $ruc . "%' ";
	}

	if (!empty($razon_social)) {
		$where_razon_social = " AND c.razon_social  LIKE '%" . $razon_social . "%' ";
	}

	if (!empty($moneda)) {
		$where_moneda = " AND (c.moneda_id = $moneda OR (SELECT ct.moneda_id FROM cont_contraprestacion ct WHERE ct.contrato_id = c.contrato_id LIMIT 1) = $moneda) ";
	}
	$where_fecha = '';
	if (!empty($fecha_inicio) && !empty($fecha_fin)) {
		$where_fecha = " AND c.created_at between '" . $fecha_inicio . "' AND '" . $fecha_fin . "'";
	}

	$where_fecha_aprobacion = '';
	if (!empty($fecha_inicio_aprobacion) && !empty($fecha_fin_aprobacion)) {
		$where_fecha = " AND c.fecha_atencion_gerencia_proveedor between '$fecha_inicio_aprobacion 00:00:00' AND '$fecha_fin_aprobacion 23:59:59'";
	} elseif (!empty($fecha_inicio_aprobacion)) {
		$where_fecha = " AND c.fecha_atencion_gerencia_proveedor >= '$fecha_inicio_aprobacion 00:00:00'";
	} elseif (!empty($fecha_fin_aprobacion)) {
		$where_fecha = " AND c.fecha_atencion_gerencia_proveedor <= '$fecha_fin_aprobacion 23:59:59'";
	}

	if (!empty($director_aprobacion_id)) {
		$where_director_aprobacion = " AND ( ( c.director_aprobacion_id = " . $director_aprobacion_id . " AND (c.check_gerencia_proveedor =1 and c.fecha_atencion_gerencia_proveedor IS NULL AND c.aprobacion_gerencia_proveedor=0) ) OR c.aprobado_por = " . $director_aprobacion_id . " ) ";
	}

	$total_query = $mysqli->query("
		SELECT COUNT(c.contrato_id) as num_rows 
		FROM cont_contrato c
			INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
			INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
			INNER JOIN tbl_areas a ON p.area_id = a.id
			INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id AND r.status = 1
			LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			LEFT JOIN cont_estado_solicitud es ON es.id = c.estado_solicitud
		WHERE c.status = 1 AND c.tipo_contrato_id = 5 AND c.etapa_id != 5
		" . $where_area . "
		" . $where_estado_cancelado . "
		" . $where_empresa . "
		" . $where_area_solicitante . "
		" . $where_ruc . "
		" . $where_razon_social . "
		" . $where_moneda . "
		" . $where_fecha . "
		" . $where_estado_sol . "
		" . $where_estado_sol_v2 . "
		" . $where_director_aprobacion . "
		" . $where_estado_aprobacion . "
		" . $where_fecha_aprobacion . "
		ORDER BY c.created_at DESC
		");

	$resultNum = $total_query->fetch_assoc();
	$num_rows = $resultNum['num_rows'];
	$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
	$per_page = 10;
	$numLinks = 10;
	$total_paginate = ceil($num_rows / $per_page);
	$total_paginate = $total_paginate == 0 ? 1 : $total_paginate;
	$page = $page > $total_paginate ? $total_paginate : $page;
	$offset = ($page - 1) * $per_page;
	$paginate = new Pagination('sec_contratos_solicitud_cambiar_de_pagina', $num_rows, $per_page, $numLinks, $page);

	$query_acuerdo_de_confidencialidad = "
		SELECT 
			c.contrato_id, 
			concat(IFNULL(p.nombre, ''), ' ',  IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS solicitante,
			a.nombre AS area, c.razon_social AS parte, r.nombre AS empresa_suscribe, c.status, c.detalle_servicio,
			c.created_at,
			co.sigla AS sigla_correlativo,
			c.codigo_correlativo,
			c.estado_solicitud,
			es.nombre AS nombre_estado_solicitud,
			(CASE WHEN (c.check_gerencia_proveedor =1 and c.fecha_atencion_gerencia_proveedor IS NULL AND c.aprobacion_gerencia_proveedor=0) THEN 'Pendiente' ELSE '' END) as aprob, 	
			(CASE WHEN (c.check_gerencia_proveedor =1 and c.fecha_atencion_gerencia_proveedor IS NOT NULL AND c.aprobacion_gerencia_proveedor=1) THEN 'Aprobado' ELSE '' END) as aprobS, 
			(CASE WHEN (c.check_gerencia_proveedor =1 and c.fecha_atencion_gerencia_proveedor IS NOT NULL AND c.aprobacion_gerencia_proveedor=0) THEN 'Denegado' ELSE '' END) as aprobN,
			p.area_id as usuario_s,
			c.fecha_atencion_gerencia_proveedor,
			c.cancelado_id,
			CONCAT(IFNULL(pud.nombre, ''),' ',IFNULL(pud.apellido_paterno, ''),' ',IFNULL(pud.apellido_materno, '')) AS nombre_del_director_a_aprobar,
			CONCAT(IFNULL(puap.nombre, ''),' ',IFNULL(puap.apellido_paterno, ''),' ',IFNULL(puap.apellido_materno, '')) AS nombre_del_aprobador,
			c.director_aprobacion_id,
			c.aprobado_por,
			c.fecha_cambio_estado_solicitud,
			c.dias_habiles,
			CONCAT(IFNULL(pa.nombre, ''),' ',IFNULL(pa.apellido_paterno, ''),' ',IFNULL(pa.apellido_materno, '')) AS abogado
		FROM cont_contrato c
			INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
			INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
			INNER JOIN tbl_areas a ON p.area_id = a.id
			INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id AND r.status = 1
			LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			LEFT JOIN cont_estado_solicitud es ON es.id = c.estado_solicitud
			LEFT JOIN tbl_usuarios ud ON c.director_aprobacion_id = ud.id
			LEFT JOIN tbl_personal_apt pud ON ud.personal_id = pud.id
			LEFT JOIN tbl_usuarios uap ON c.aprobado_por = uap.id
			LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id
			LEFT JOIN tbl_usuarios ua ON c.abogado_id = ua.id
			LEFT JOIN tbl_personal_apt pa ON ua.personal_id = pa.id
		WHERE c.status = 1 AND c.tipo_contrato_id = 5 AND c.etapa_id != 5 
			" . $where_area . "
			" . $where_estado_cancelado . "
			" . $where_empresa . "
			" . $where_area_solicitante . "
			" . $where_ruc . "
			" . $where_razon_social . "
			" . $where_moneda . "
			" . $where_fecha . "
			" . $where_estado_sol . "
			" . $where_estado_sol_v2 . "
			" . $where_director_aprobacion . "
			" . $where_estado_aprobacion . "
			" . $where_fecha_aprobacion . "
		ORDER BY c.created_at DESC
		 
		";

	$sel_query = $mysqli->query($query_acuerdo_de_confidencialidad);
?>
	<div class="w-100 text-center">
		<h5 style="font-weight: bold; margin: 0px;">SOLICITUDES DE ACUERDO DE CONFIDENCIALIDAD</h5>
	</div>
	<br>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed table-bordered tabla_contratos_para_filtro" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th class="text-center">Código</th>
					<th class="text-center">Área solicitante</th>
					<th class="text-center">Solicitante</th>
					<th class="text-center">F. Solicitud</th>
					<th class="text-center">Detalle servicio</th>
					<th class="text-center">Empresa que suscribe el contrato</th>
					<th class="text-center">Proveedor</th>
					<th class="text-center">Estado Solicitud</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Fecha de atención</th>
						<th class="text-center">Días de atención</th>
					<?php } ?>
					<th class="text-center">Estado Aprobación</th>
					<th class="text-center">Aprobante</th>
					<th class="text-center">F. Aprobación</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Abogado</th>
					<?php } ?>
					<th class="text-center">Ver Detalle</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th class="text-center">Código</th>
					<th class="text-center">Área solicitante</th>
					<th class="text-center">Solicitante</th>
					<th class="text-center">F. Solicitud</th>
					<th class="text-center">Detalle servicio</th>
					<th class="text-center">Empresa que suscribe el contrato</th>
					<th class="text-center">Proveedor</th>
					<th class="text-center">Estado Solicitud</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Fecha de atención</th>
						<th class="text-center">Días de atención</th>
					<?php } ?>
					<th class="text-center">Estado Aprobación</th>
					<th class="text-center">Aprobante</th>
					<th class="text-center">F. Aprobación</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Abogado</th>
					<?php } ?>
					<th class="text-center">Ver Detalle</th>
				</tr>
			</tfoot>
			<tbody>
				<?php
				while ($sel = $sel_query->fetch_assoc()) {
					$detalle_servicio = trim($sel["detalle_servicio"]);

					if (strlen($detalle_servicio) > 50) {
						$detalle_servicio = substr($detalle_servicio, 0, 50) . ' ...';
					}
				?>
					<tr>
						<td class="text-center"><?php echo $sel["sigla_correlativo"];
												echo $sel["codigo_correlativo"]; ?></td>
						<td><?php echo $sel["area"]; ?></td>
						<td><?php echo $sel["solicitante"]; ?></td>
						<td><?php echo $sel["created_at"]; ?></td>
						<td><?php echo $detalle_servicio; ?></td>
						<td><?php echo $sel["empresa_suscribe"]; ?></td>
						<td><?php echo $sel["parte"]; ?></td>
						<td class="text-center">
							<?php
							if ($sel["cancelado_id"] == 1) {
								echo '<span class="badge bg-danger text-white">Cancelado</span>';
							} elseif ($area_id == '33') {
								$bg_estado_solicitud = '';
								switch ($sel["estado_solicitud"]) {
									case 1:
										$bg_estado_solicitud = 'bg-default';
										break;
									case 2:
										$bg_estado_solicitud = 'bg-info';
										break;
									case 3:
										$bg_estado_solicitud = 'bg-warning';
										break;
								}
							?>

								<span class="badge <?= $bg_estado_solicitud ?> text-white"><?php echo $sel["nombre_estado_solicitud"]; ?></span>
							<?php } else {
								echo '<span class="badge bg-info text-white">En proceso</span>';
							} ?>
						</td>
						<?php if ($area_id == '33') { ?>
							<td class="text-center">
								<?php echo (!is_null($sel['fecha_cambio_estado_solicitud'])) ? $sel['fecha_cambio_estado_solicitud'] : ''; ?>
							</td>
							<td class="text-center">
								<?php echo (!is_null($sel['dias_habiles'])) ? sec_contrato_solicitud_num_dias_habiles_formato($sel['dias_habiles']) : ''; ?>
							</td>
						<?php } ?>
						<td class="text-center">
							<?php
							if (($sel["usuario_s"] ==  $login["area_id"]) || $usuario_id_login == $sel["director_aprobacion_id"] || $usuario_id_login == $sel["aprobado_por"] || $area_id == '6' || ($area_id == '33')) {
								echo $sel["aprob"];
								echo $sel["aprobS"];
								echo $sel["aprobN"];
							}
							?>
						</td>
						<td>
							<?php
							if (($sel["usuario_s"] ==  $login["area_id"]) || $usuario_id_login == $sel["director_aprobacion_id"] || $usuario_id_login == $sel["aprobado_por"] || $area_id == '6' || ($area_id == '33')) {

								if (trim($sel["aprob"]) == "Pendiente") {
									echo $sel["nombre_del_director_a_aprobar"];
								} else {
									echo $sel["nombre_del_aprobador"];
								}
							}
							?>
						</td>
						<td>
							<?php if (($sel["usuario_s"] ==  $login["area_id"]) || $usuario_id_login == $sel["director_aprobacion_id"] || $usuario_id_login == $sel["aprobado_por"] || $area_id == '6' || ($area_id == '33')) {
								echo $sel["fecha_atencion_gerencia_proveedor"];
							}
							?>
						</td>
						<?php if ($area_id == '33') { ?>
							<td><?php echo $sel["abogado"]; ?></td>
						<?php } ?>

						<td class="text-center">
							<a class="btn btn-rounded btn-primary btn-sm"
								href="./?sec_id=<?php echo $sec_id; ?>&amp;sub_sec_id=detalle_solicitud_acuerdo_confidencialidad&id=<?php echo $sel["contrato_id"]; ?>"
								title="Ver detalle">
								<i class="fa fa-eye"></i>
							</a>
						</td>
					</tr>
				<?php
				}
				?>
			</tbody>
		</table>
	</div>


<?php

}

if ($_POST['action'] == "listar_contrato_de_agente") {
	// datos de sesion
	$menu_consultar = $_POST['menu_consultar'];
	$area_id = $_POST['area_id'];
	$cargo_id = $_POST['cargo_id'];
	$menu_id = $_POST['menu_id'];
	$empresa = $_POST['empresa'];
	$fecha_inicio = trim($_POST['fecha_inicio']);
	$fecha_fin = trim($_POST['fecha_fin']);
	$nombre_agente = trim($_POST['nombre_agente']);
	$usuario_permisos = json_decode($_POST['usuario_permisos'], true);
	$sec_id = $_POST['sec_id'];
	$estado_sol_v2 = trim($_POST['estado_sol_v2']);

	$estado_sol = '';
	if (isset($_POST["estado_sol"])) {
		$estado_sol = $_POST["estado_sol"];
	}

	$where_empresa = "";
	$where_estado_sol = '';
	$where_estado_sol_v2 = '';
	$where_area = "";
	$where_estado_cancelado = "";
	$where_nombre_agente = "";

	if (array_key_exists($menu_consultar, $usuario_permisos) && (in_array("ver_todo_solicitud", $usuario_permisos[$menu_consultar]) || in_array("see_all", $usuario_permisos[$menu_consultar]))) {
		$where_area = "";
	} else {
		if ($area_id == 33) {
			$where_area = " AND (c.check_gerencia_proveedor = 0 OR (c.aprobacion_gerencia_proveedor = 1)) ";
		} else {
			$where_area = " AND p.area_id =" . $area_id;
		}
	}

	if (!empty($empresa)) {
		$where_empresa = " AND c.empresa_suscribe_id = '" . $empresa . "' ";
	}

	if ($login["area_id"] == 33) {
		$where_estado_cancelado = " AND ( c.cancelado_id != 1 OR c.cancelado_id IS NULL )";
	}

	$query_fecha = '';
	if (!empty($fecha_inicio) && !empty($fecha_fin)) {
		$query_fecha = " AND c.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
	} elseif (!empty($fecha_inicio)) {
		$query_fecha = " AND c.created_at >= '$fecha_inicio 00:00:00'";
	} elseif (!empty($fecha_fin)) {
		$query_fecha = " AND c.created_at <= '$fecha_fin 23:59:59'";
	}

	if (!empty($nombre_agente)) {
		$where_nombre_agente = " AND c.nombre_agente LIKE '%" . $nombre_agente . "%' ";
	}

	if (!empty($estado_sol)) {
		if ((int) $estado_sol === 99) {
			$where_estado_sol_v2 = " AND c.cancelado_id = 1 ";
		} elseif ((int) $estado_sol === 1) {
			$where_estado_sol = " AND ( c.estado_solicitud = 1 OR c.estado_solicitud IS NULL ) ";
		} else {
			$where_estado_sol = " AND c.estado_solicitud = '" . $estado_sol . "' ";
		}
	}

	if (!empty($estado_sol_v2)) {
		if ((int) $estado_sol_v2 === 1) {
			$where_estado_sol_v2 = " AND ( c.cancelado_id != 1 || c.cancelado_id IS NULL )";
		} elseif ((int) $estado_sol_v2 === 2) {
			$where_estado_sol_v2 = " AND c.cancelado_id = 1 ";
		}
	}

	$total_query = $mysqli->query("
		SELECT COUNT(c.contrato_id) as num_rows 
	FROM cont_contrato c
		INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
		INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		INNER JOIN tbl_areas a ON p.area_id = a.id
		INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id AND r.status = 1
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
		LEFT JOIN cont_estado_solicitud es ON es.id = c.estado_solicitud
	WHERE c.status = 1 AND c.tipo_contrato_id = 6 AND c.etapa_id != 5 
			" . $where_empresa . "
			" . $where_estado_cancelado . "
			" . $query_fecha . "
			" . $where_nombre_agente . "
			" . $where_estado_sol . "
			" . $where_estado_sol_v2 . "
		");

	$resultNum = $total_query->fetch_assoc();
	$num_rows = $resultNum['num_rows'];
	$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
	$per_page = 10;
	$numLinks = 10;
	$total_paginate = ceil($num_rows / $per_page);
	$total_paginate = $total_paginate == 0 ? 1 : $total_paginate;
	$page = $page > $total_paginate ? $total_paginate : $page;
	$offset = ($page - 1) * $per_page;
	$paginate = new Pagination('sec_contratos_solicitud_cambiar_de_pagina', $num_rows, $per_page, $numLinks, $page);


	$sel_query = $mysqli->query(
		"
	SELECT 
		c.contrato_id, 
		concat(IFNULL(p.nombre, ''), ' ',  IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS solicitante,
		a.nombre AS area, c.razon_social AS parte, r.nombre AS empresa_suscribe, c.status, c.detalle_servicio,
		c.created_at,
		co.sigla AS sigla_correlativo,
		c.codigo_correlativo,
		c.estado_solicitud,
		es.nombre AS nombre_estado_solicitud,
		c.nombre_agente,
		c.cancelado_id,
		c.fecha_cambio_estado_solicitud,
		c.dias_habiles,
		(CASE WHEN (c.fecha_aprobacion IS NULL AND c.estado_aprobacion=0) THEN 'Pendiente' ELSE '' END) as aprob, 	
		(CASE WHEN (c.fecha_aprobacion IS NOT NULL AND c.estado_aprobacion=1) THEN 'Aprobado' ELSE '' END) as aprobS, 
		(CASE WHEN (c.fecha_aprobacion IS NOT NULL AND c.estado_aprobacion=0) THEN 'Denegado' ELSE '' END) as aprobN, 
		CONCAT(IFNULL(pud.nombre, ''),' ',IFNULL(pud.apellido_paterno, ''),' ',IFNULL(pud.apellido_materno, '')) AS nombre_del_director_a_aprobar,
		CONCAT(IFNULL(puap.nombre, ''),' ',IFNULL(puap.apellido_paterno, ''),' ',IFNULL(puap.apellido_materno, '')) AS nombre_del_aprobador,
		c.aprobador_id,
		c.aprobado_por,
		c.fecha_aprobacion,
		CONCAT(IFNULL(pa.nombre, ''),' ',IFNULL(pa.apellido_paterno, ''),' ',IFNULL(pa.apellido_materno, '')) AS abogado
	FROM cont_contrato c
		INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
		INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		INNER JOIN tbl_areas a ON p.area_id = a.id
		INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id AND r.status = 1
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
		LEFT JOIN cont_estado_solicitud es ON es.id = c.estado_solicitud
		LEFT JOIN tbl_usuarios ua ON c.abogado_id = ua.id
		LEFT JOIN tbl_personal_apt pa ON ua.personal_id = pa.id
		LEFT JOIN tbl_usuarios ud ON c.aprobador_id = ud.id
		LEFT JOIN tbl_personal_apt pud ON ud.personal_id = pud.id
		LEFT JOIN tbl_usuarios uap ON c.aprobado_por = uap.id
		LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id
	WHERE c.status = 1 AND c.tipo_contrato_id = 6 AND c.etapa_id != 5 
	" . $where_empresa . "
	" . $query_fecha . "
	" . $where_nombre_agente . "
	" . $where_estado_cancelado . "
	" . $where_estado_sol . "
	" . $where_estado_sol_v2 . "
	ORDER BY c.created_at DESC
	 
	"
	);
?>

	<div class="w-100 text-center">
		<h5 style="font-weight: bold; margin: 0px;">SOLICITUDES DE CONTRATO DE AGENTE</h5>
	</div>
	<br>

	<div class="table-responsive">


		<table class="table table-striped table-hover table-condensed table-bordered tabla_contratos_para_filtro" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th class="text-center">Código</th>
					<th class="text-center">Agente</th>
					<th class="text-center">Solicitante</th>
					<th class="text-center">F. Solicitud</th>
					<th class="text-center">Empresa que suscribe el contrato</th>
					<th class="text-center">Estado</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Fecha de atención</th>
						<th class="text-center">Días de atención</th>
						<th class="text-center">Abogado</th>
					<?php } ?>
					<th class="text-center">Estado Aprobación</th>
					<th class="text-center">Aprobante</th>
					<th class="text-center">F. Aprobación</th>
					<th class="text-center">Ver Detalle</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th class="text-center">Código</th>
					<th class="text-center">Agente</th>
					<th class="text-center">Solicitante</th>
					<th class="text-center">F. Solicitud</th>
					<th class="text-center">Empresa que suscribe el contrato</th>
					<th class="text-center">Estado</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Fecha de atención</th>
						<th class="text-center">Días de atención</th>
						<th class="text-center">Abogado</th>
					<?php } ?>
					<th class="text-center">Estado Aprobación</th>
					<th class="text-center">Aprobante</th>
					<th class="text-center">F. Aprobación</th>
					<th class="text-center">Ver Detalle</th>
				</tr>
			</tfoot>
			<tbody>
				<?php
				while ($sel = $sel_query->fetch_assoc()) {
					$detalle_servicio = trim($sel["detalle_servicio"]);

					if (strlen($detalle_servicio) > 50) {
						$detalle_servicio = substr($detalle_servicio, 0, 50) . ' ...';
					}
				?>
					<tr>
						<td class="text-center"><?php echo $sel["sigla_correlativo"];
												echo $sel["codigo_correlativo"]; ?></td>
						<td><?php echo $sel["nombre_agente"]; ?></td>
						<td><?php echo $sel["solicitante"]; ?></td>
						<td><?php echo $sel["created_at"]; ?></td>
						<td><?php echo $sel["empresa_suscribe"]; ?></td>
						<td class="text-center">
							<?php
							if ($sel["cancelado_id"] == 1) {
								echo '<span class="badge bg-danger text-white">Cancelado</span>';
							} elseif ($area_id == '33') {
								$bg_estado_solicitud = '';
								switch ($sel["estado_solicitud"]) {
									case 1:
										$bg_estado_solicitud = 'bg-default';
										break;
									case 2:
										$bg_estado_solicitud = 'bg-info';
										break;
									case 3:
										$bg_estado_solicitud = 'bg-warning';
										break;
								}
							?>

								<span class="badge <?= $bg_estado_solicitud ?> text-white"><?php echo $sel["nombre_estado_solicitud"]; ?></span>
							<?php } else {
								echo '<span class="badge bg-info text-white">En proceso</span>';
							} ?>
						</td>
						<?php if ($area_id == '33') { ?>
							<td class="text-center">
								<?php echo (!is_null($sel['fecha_cambio_estado_solicitud'])) ? $sel['fecha_cambio_estado_solicitud'] : ''; ?>
							</td>
							<td class="text-center">
								<?php echo (!is_null($sel['dias_habiles'])) ? sec_contrato_solicitud_num_dias_habiles_formato($sel['dias_habiles']) : ''; ?>
							</td>
							<td><?php echo $sel["abogado"]; ?></td>
						<?php } ?>
						<td class="text-center">
							<?php
							if (($sel["usuario_s"] ==  $login["area_id"]) || $usuario_id_login == $sel["aprobador_id"] || $usuario_id_login == $sel["aprobado_por"] || $area_id == '6' || ($area_id == '33')) {
								echo $sel["aprob"];
								echo $sel["aprobS"];
								echo $sel["aprobN"];
							}
							?>
						</td>
						<td>
							<?php
							if (($sel["usuario_s"] ==  $login["area_id"]) || $usuario_id_login == $sel["aprobador_id"] || $usuario_id_login == $sel["aprobado_por"] || $area_id == '6' || ($area_id == '33')) {

								if (trim($sel["aprob"]) == "Pendiente") {
									echo $sel["nombre_del_director_a_aprobar"];
								} else {
									echo $sel["nombre_del_aprobador"];
								}
							}
							?>
						</td>
						<td>
							<?php if (($sel["usuario_s"] ==  $login["area_id"]) || $usuario_id_login == $sel["director_aprobacion_id"] || $usuario_id_login == $sel["aprobado_por"] || $area_id == '6' || ($area_id == '33')) {
								echo $sel["fecha_aprobacion"];
							}
							?>
						</td>
						<td class="text-center">
							<a class="btn btn-rounded btn-primary btn-sm"
								href="./?sec_id=<?php echo $sec_id; ?>&amp;sub_sec_id=detalle_agente&id=<?php echo $sel["contrato_id"]; ?>"
								title="Ver detalle">
								<i class="fa fa-eye"></i>
							</a>
						</td>
					</tr>
				<?php
				}
				?>
			</tbody>
		</table>
	</div>


<?php


}

if ($_POST['action'] == "listar_contrato_interno") {

	$list_emp_cont = $mysqli->query("SELECT tpg.valor FROM tbl_parametros_generales tpg WHERE tpg.codigo = 'empresa_contacto_de_contratos' AND tpg.estado = 1 LIMIT 1");
	$row_emp_cont = $list_emp_cont->fetch_assoc();
	$pref_empresa_contacto = isset($row_emp_cont['valor']) && $row_emp_cont['valor'] == 'Kurax' ? 'Kurax' : 'AT';

	// datos de sesion
	$menu_consultar = $_POST['menu_consultar'];
	$area_id = $_POST['area_id'];
	$cargo_id = $_POST['cargo_id'];
	$menu_id = $_POST['menu_id'];
	$empresa = $_POST['empresa'];
	$area_solicitante = $_POST['area'];
	$fecha_inicio = trim($_POST['fecha_inicio']);
	$fecha_fin = trim($_POST['fecha_fin']);
	$usuario_permisos = json_decode($_POST['usuario_permisos'], true);
	$sec_id = $_POST['sec_id'];
	$estado_sol_v2 = trim($_POST['estado_sol_v2']);

	$estado_sol = '';
	if (isset($_POST["estado_sol"])) {
		$estado_sol = $_POST["estado_sol"];
	}

	$where_empresa = "";
	$where_area_solicitante = "";
	$where_area = "";
	$where_estado_cancelado = "";
	$where_estado_sol = '';
	$where_estado_sol_v2 = '';

	if (array_key_exists($menu_consultar, $usuario_permisos) && (in_array("ver_todo_solicitud", $usuario_permisos[$menu_consultar]) || in_array("see_all", $usuario_permisos[$menu_consultar]))) {
		$where_area = "";
	} else {
		if ($area_id == 33) {
			$where_area = " AND (c.check_gerencia_interno = 0 OR (c.aprobacion_gerencia_interno = 1)) ";
		} else {
			$where_area = " AND (c.user_created_id = " . $usuario_id_login . " OR ar.id =" . $area_id_login . ")";
		}
	}

	if (!empty($empresa)) {
		$where_empresa = " AND ( c.empresa_suscribe_id = '" . $empresa . "'  || c.empresa_grupo_at_2 = '" . $empresa . "' ) ";
	}

	if (!empty($area_solicitante)) {
		$where_area_solicitante = " AND ar.id = '" . $area_solicitante . "' ";
	}

	if ($login["area_id"] == 33) {
		$where_estado_cancelado = " AND ( c.cancelado_id != 1 OR c.cancelado_id IS NULL )";
	}

	if (!empty($estado_sol)) {
		if ((int) $estado_sol === 99) {
			$where_estado_sol_v2 = " AND c.cancelado_id = 1 ";
		} elseif ((int) $estado_sol === 1) {
			$where_estado_sol = " AND ( c.estado_solicitud = 1 OR c.estado_solicitud IS NULL ) ";
		} else {
			$where_estado_sol = " AND c.estado_solicitud = '" . $estado_sol . "' ";
		}
	}

	if (!empty($estado_sol_v2)) {
		if ((int) $estado_sol_v2 === 1) {
			$where_estado_sol_v2 = " AND ( c.cancelado_id != 1 || c.cancelado_id IS NULL )";
		} elseif ((int) $estado_sol_v2 === 2) {
			$where_estado_sol_v2 = " AND c.cancelado_id = 1 ";
		}
	}

	$query_fecha = '';
	if (!empty($fecha_inicio) && !empty($fecha_fin)) {
		$query_fecha = " AND c.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
	} elseif (!empty($fecha_inicio)) {
		$query_fecha = " AND c.created_at >= '$fecha_inicio 00:00:00'";
	} elseif (!empty($fecha_fin)) {
		$query_fecha = " AND c.created_at <= '$fecha_fin 23:59:59'";
	}

	$total_query = "
		SELECT COUNT(c.contrato_id) as num_rows 
		FROM cont_contrato c
		LEFT JOIN cont_periodo p ON c.periodo = p.id
		INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
		INNER JOIN tbl_personal_apt per ON u.personal_id = per.id
		INNER JOIN tbl_areas ar ON per.area_id = ar.id
		INNER JOIN tbl_razon_social rs1 ON c.empresa_suscribe_id = rs1.id AND rs1.status = 1
		INNER JOIN tbl_razon_social rs2 ON c.empresa_grupo_at_2 = rs2.id AND rs2.status = 1
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
		WHERE c.status = 1 AND c.tipo_contrato_id = 7  AND c.etapa_id != 5 
			" . $where_empresa . "
			" . $where_area_solicitante . "
			" . $where_area . "
			" . $where_estado_cancelado . "
			" . $query_fecha . "
			" . $where_estado_sol . "
			" . $where_estado_sol_v2 . "
		";

	$total_query = $mysqli->query($total_query);
	$resultNum = $total_query->fetch_assoc();
	$num_rows = $resultNum['num_rows'];
	$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
	$per_page = 10;
	$numLinks = 10;
	$total_paginate = ceil($num_rows / $per_page);
	$total_paginate = $total_paginate == 0 ? 1 : $total_paginate;
	$page = $page > $total_paginate ? $total_paginate : $page;
	$offset = ($page - 1) * $per_page;
	$paginate = new Pagination('sec_contratos_solicitud_cambiar_de_pagina', $num_rows, $per_page, $numLinks, $page);


	$sel_query = $mysqli->query(
		"
	SELECT
		c.contrato_id,
		c.empresa_suscribe_id,
		rs1.nombre AS empresa_at1,
		rs2.nombre AS empresa_at2,
		c.detalle_servicio,
		CONCAT(c.periodo_numero, ' ', p.nombre) AS periodo,
		concat(IFNULL(per.nombre, ''),' ', IFNULL(per.apellido_paterno, '')) AS solicitante,
		c.fecha_inicio,
		ar.nombre AS area,
		c.check_gerencia_interno,
		c.fecha_atencion_gerencia_interno,
		c.aprobacion_gerencia_interno,
		co.sigla AS sigla_correlativo,
		c.codigo_correlativo,
		c.observaciones,
		c.created_at,
		c.estado_solicitud,
		es.nombre AS nombre_estado_solicitud,
		(CASE WHEN (c.check_gerencia_interno =1 and c.fecha_atencion_gerencia_interno IS NULL AND (c.aprobacion_gerencia_interno=0 OR c.aprobacion_gerencia_interno IS NULL) ) THEN 'Pendiente' ELSE '' END) as aprob, 	
		(CASE WHEN (c.check_gerencia_interno =1 and c.fecha_atencion_gerencia_interno IS NOT NULL AND c.aprobacion_gerencia_interno=1) THEN 'Aprobado' ELSE '' END) as aprobS, 
		(CASE WHEN (c.check_gerencia_interno =1 and c.fecha_atencion_gerencia_interno IS NOT NULL AND (c.aprobacion_gerencia_interno=0 OR c.aprobacion_gerencia_interno IS NULL)) THEN 'Denegado' ELSE '' END) as aprobN, 
		per.area_id as usuario_s,
		c.fecha_atencion_gerencia_interno,
		c.cancelado_id,
		c.fecha_cambio_estado_solicitud,
		c.dias_habiles,
		CONCAT(IFNULL(pa.nombre, ''),' ',IFNULL(pa.apellido_paterno, ''),' ',IFNULL(pa.apellido_materno, '')) AS abogado
	FROM 
		cont_contrato c
		LEFT JOIN cont_periodo p ON c.periodo = p.id
		INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
		INNER JOIN tbl_personal_apt per ON u.personal_id = per.id
		INNER JOIN tbl_areas ar ON per.area_id = ar.id
		INNER JOIN tbl_razon_social rs1 ON c.empresa_suscribe_id = rs1.id AND rs1.status = 1
		INNER JOIN tbl_razon_social rs2 ON c.empresa_grupo_at_2 = rs2.id AND rs2.status = 1
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
		LEFT JOIN cont_estado_solicitud es ON es.id = c.estado_solicitud
		LEFT JOIN tbl_usuarios ua ON c.abogado_id = ua.id
		LEFT JOIN tbl_personal_apt pa ON ua.personal_id = pa.id
	WHERE c.status = 1 AND c.tipo_contrato_id = 7  AND c.etapa_id != 5 
	" . $where_empresa . "
	" . $where_area_solicitante . "
	" . $where_area . "
	" . $where_estado_cancelado . "
	" . $query_fecha . "
	" . $where_estado_sol . "
	" . $where_estado_sol_v2 . "
	ORDER BY c.created_at DESC
	"
	);
?>

	<div class="w-100 text-center">
		<h5 style="font-weight: bold; margin: 0px;">SOLICITUDES DE CONTRATO INTERNO</h5>
	</div>
	<br>

	<div class="table-responsive">


		<table class="table table-striped table-hover table-condensed table-bordered tabla_contratos_para_filtro" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th class="text-center">Código</th>
					<th class="text-center">Área solicitante</th>
					<th class="text-center">Solicitante</th>
					<th class="text-center">F. Solicitud</th>
					<th class="text-center">Detalle servicio</th>
					<th class="text-center">Empresa Grupo <?= $pref_empresa_contacto ?> 1</th>
					<th class="text-center">Empresa Grupo <?= $pref_empresa_contacto ?> 2</th>
					<th class="text-center">Estado</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Fecha de atención</th>
						<th class="text-center">Días de atención</th>
					<?php } ?>
					<th class="text-center">Aprobación</th>
					<th class="text-center">F. Aprobación</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Abogado</th>
					<?php } ?>
					<th class="text-center">Ver Detalle</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th class="text-center">Código</th>
					<th class="text-center">Área solicitante</th>
					<th class="text-center">Solicitante</th>
					<th class="text-center">F. Solicitud</th>
					<th class="text-center">Detalle servicio</th>
					<th class="text-center">Empresa Grupo <?= $pref_empresa_contacto ?> 1</th>
					<th class="text-center">Empresa Grupo <?= $pref_empresa_contacto ?> 2</th>
					<th class="text-center">Estado</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Fecha de atención</th>
						<th class="text-center">Días de atención</th>
					<?php } ?>
					<th class="text-center">Aprobación</th>
					<th class="text-center">F. Aprobación</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Abogado</th>
					<?php } ?>
					<th class="text-center">Ver Detalle</th>
				</tr>
			</tfoot>
			<tbody>
				<?php
				while ($sel = $sel_query->fetch_assoc()) {
					$detalle_servicio = trim($sel["detalle_servicio"]);
					if (strlen($detalle_servicio) > 50) {
						$detalle_servicio = substr($detalle_servicio, 0, 50) . ' ...';
					}
				?>
					<tr>
						<td class="text-center"><?php echo $sel["sigla_correlativo"];
												echo $sel["codigo_correlativo"]; ?></td>
						<td><?php echo $sel["area"]; ?></td>
						<td><?php echo $sel["solicitante"]; ?></td>
						<td><?php echo $sel["created_at"]; ?></td>
						<td><?php echo $detalle_servicio; ?></td>
						<td><?php echo $sel["empresa_at1"]; ?></td>
						<td><?php echo $sel["empresa_at2"]; ?></td>
						<td class="text-center">
							<?php
							if ($sel["cancelado_id"] == 1) {
								echo '<span class="badge bg-danger text-white">Cancelado</span>';
							} elseif ($area_id == '33') {
								$bg_estado_solicitud = '';
								switch ($sel["estado_solicitud"]) {
									case 1:
										$bg_estado_solicitud = 'bg-default';
										break;
									case 2:
										$bg_estado_solicitud = 'bg-info';
										break;
									case 3:
										$bg_estado_solicitud = 'bg-warning';
										break;
								}
							?>

								<span class="badge <?= $bg_estado_solicitud ?> text-white"><?php echo $sel["nombre_estado_solicitud"]; ?></span>

							<?php } else {
								echo '<span class="badge bg-info text-white">En proceso</span>';
							} ?>
						</td>
						<?php if ($area_id == '33') { ?>
							<td class="text-center">
								<?php echo (!is_null($sel['fecha_cambio_estado_solicitud'])) ? $sel['fecha_cambio_estado_solicitud'] : ''; ?>
							</td>
							<td class="text-center">
								<?php echo (!is_null($sel['dias_habiles'])) ? sec_contrato_solicitud_num_dias_habiles_formato($sel['dias_habiles']) : ''; ?>
							</td>
						<?php } ?>
						<td class="text-center">
							<?php if (($sel["usuario_s"] ==  $login["area_id"]) || ($area_id == '33')) {
								echo $sel["aprob"];
								echo $sel["aprobS"];
								echo $sel["aprobN"];
							}
							?>
						</td>
						<td>
							<?php if (($sel["usuario_s"] ==  $login["area_id"]) || ($area_id == '33')) {
								echo $sel["fecha_atencion_gerencia_interno"];
							}
							?>
						</td>
						<?php if ($area_id == '33') { ?>
							<td><?php echo $sel["abogado"]; ?></td>
						<?php } ?>

						<td class="text-center">
							<a class="btn btn-rounded btn-primary btn-sm"
								href="./?sec_id=<?php echo $sec_id; ?>&amp;sub_sec_id=detalle_solicitud_interno&id=<?php echo $sel["contrato_id"]; ?>"
								title="Ver detalle">
								<i class="fa fa-eye"></i>
							</a>
						</td>
					</tr>
				<?php
				}
				?>
			</tbody>
		</table>
	</div>


<?php


}


if ($_POST['action'] == "listar_contrato_adenda_interno") {

	$list_emp_cont = $mysqli->query("SELECT tpg.valor FROM tbl_parametros_generales tpg WHERE tpg.codigo = 'empresa_contacto_de_contratos' AND tpg.estado = 1 LIMIT 1");
	$row_emp_cont = $list_emp_cont->fetch_assoc();
	$pref_empresa_contacto = isset($row_emp_cont['valor']) && $row_emp_cont['valor'] == 'Kurax' ? 'Kurax' : 'AT';

	$menu_consultar = $_POST['menu_consultar'];
	$area_id = $_POST['area_id'];
	$cargo_id = $_POST['cargo_id'];
	$menu_id = $_POST['menu_id'];
	$empresa = $_POST['empresa'];
	$area_solicitante = $_POST['area'];
	$fecha_inicio = trim($_POST['fecha_inicio']);
	$fecha_fin = trim($_POST['fecha_fin']);
	$usuario_permisos = json_decode($_POST['usuario_permisos'], true);
	$sec_id = $_POST['sec_id'];
	$estado_sol_v2 = trim($_POST['estado_sol_v2']);

	$estado_sol = '';
	if (isset($_POST["estado_sol"])) {
		$estado_sol = $_POST["estado_sol"];
	}

	$where_empresa = "";
	$where_area_solicitante = "";
	$where_estado_sol = '';
	$where_estado_sol_v2 = '';
	$where_estado_cancelado = "";
	$query_fecha = '';

	$query_fecha = '';
	if (!empty($fecha_inicio) && !empty($fecha_fin)) {
		$query_fecha = " AND a.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
	} elseif (!empty($fecha_inicio)) {
		$query_fecha = " AND a.created_at >= '$fecha_inicio 00:00:00'";
	} elseif (!empty($fecha_fin)) {
		$query_fecha = " AND a.created_at <= '$fecha_fin 23:59:59'";
	}

	if (!empty($empresa)) {
		$where_empresa = " AND ( c.empresa_suscribe_id = '" . $empresa . "'  || c.empresa_grupo_at_2 = '" . $empresa . "' ) ";
	}

	if (!empty($area_solicitante)) {
		$where_area_solicitante = " AND p.area_id = '" . $area_solicitante . "' ";
	}

	if (!empty($estado_sol)) {
		if ((int) $estado_sol === 99) {
			$where_estado_sol_v2 = " AND a.cancelado_id = 1 ";
		} elseif ((int) $estado_sol === 1) {
			$where_estado_sol = " AND ( a.estado_solicitud_id = 1 OR a.estado_solicitud_id IS NULL ) ";
		} else {
			$where_estado_sol = " AND a.estado_solicitud_id = '" . $estado_sol . "' ";
		}
	}

	if (!empty($estado_sol_v2)) {
		if ((int) $estado_sol_v2 === 1) {
			$where_estado_sol_v2 = " AND ( a.cancelado_id != 1 || a.cancelado_id IS NULL )";
		} elseif ((int) $estado_sol_v2 === 2) {
			$where_estado_sol_v2 = " AND a.cancelado_id = 1 ";
		}
	}

	if ($login["area_id"] == 33) {
		$where_estado_cancelado = " AND ( a.cancelado_id != 1 OR a.cancelado_id IS NULL )";
	}

	$total_query = $mysqli->query("
		SELECT COUNT(c.contrato_id) as num_rows 
		FROM cont_adendas a
		INNER JOIN cont_contrato c ON a.contrato_id = c.contrato_id
		INNER JOIN tbl_usuarios u ON a.user_created_id = u.id
		INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		INNER JOIN tbl_areas ar ON p.area_id = ar.id
		INNER JOIN tbl_razon_social rs1 ON c.empresa_suscribe_id = rs1.id AND rs1.status = 1
		INNER JOIN tbl_razon_social rs2 ON c.empresa_grupo_at_2 = rs2.id AND rs2.status = 1
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
		WHERE c.tipo_contrato_id = 7
		AND a.procesado = 0
		AND a.status = 1
		" . $where_empresa . "
		" . $where_area_solicitante . "
		" . $query_fecha . "
		" . $where_estado_sol . "
		" . $where_estado_sol_v2 . "
		" . $where_estado_cancelado . "
		ORDER BY c.created_at DESC
		");

	$resultNum = $total_query->fetch_assoc();
	$num_rows = $resultNum['num_rows'];
	$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
	$per_page = 10;
	$numLinks = 10;
	$total_paginate = ceil($num_rows / $per_page);
	$total_paginate = $total_paginate == 0 ? 1 : $total_paginate;
	$page = $page > $total_paginate ? $total_paginate : $page;
	$offset = ($page - 1) * $per_page;
	$paginate = new Pagination('sec_contratos_solicitud_cambiar_de_pagina', $num_rows, $per_page, $numLinks, $page);

	$sel_query = $mysqli->query("
		SELECT
			a.id,
			a.codigo,
			c.contrato_id,
			c.nombre_tienda,
			CONCAT(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, ''),' ', IFNULL(p.apellido_materno, '')) AS solicitante,
			p.area_id,
			a.created_at,
			ar.nombre AS area,
			c.razon_social AS parte,
			rs1.nombre AS empresa_at_1,
			rs2.nombre AS empresa_at_2,
			c.status,
			c.detalle_servicio,
			co.sigla AS sigla_correlativo,
			c.codigo_correlativo,
			a.estado_solicitud_id,
			es.nombre AS nombre_estado_solicitud,
			a.procesado,
			a.cancelado_id,
			a.fecha_cambio_estado_solicitud,
			a.dias_habiles,
			CONCAT(IFNULL(pa.nombre, ''),' ',IFNULL(pa.apellido_paterno, ''),' ',IFNULL(pa.apellido_materno, '')) AS abogado
		FROM cont_adendas a
		INNER JOIN cont_contrato c ON a.contrato_id = c.contrato_id
		INNER JOIN tbl_usuarios u ON a.user_created_id = u.id
		INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		INNER JOIN tbl_areas ar ON p.area_id = ar.id
		INNER JOIN tbl_razon_social rs1 ON c.empresa_suscribe_id = rs1.id AND rs1.status = 1
		INNER JOIN tbl_razon_social rs2 ON c.empresa_grupo_at_2 = rs2.id AND rs2.status = 1
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
		LEFT JOIN cont_estado_solicitud es ON es.id = a.estado_solicitud_id
		LEFT JOIN tbl_usuarios ua ON c.abogado_id = ua.id
		LEFT JOIN tbl_personal_apt pa ON ua.personal_id = pa.id
		WHERE c.tipo_contrato_id = 7
		AND a.procesado = 0
		AND a.status = 1
		" . $where_empresa . "
		" . $where_area_solicitante . "
		" . $query_fecha . "
		" . $where_estado_sol . "
		" . $where_estado_sol_v2 . "
		" . $where_estado_cancelado . "
		ORDER BY c.created_at DESC
		 
		");

?>
	<div class="w-100 text-center">
		<h5 style="font-weight: bold; margin: 0px;">SOLICITUDES DE ADENDA DE CONTRATO INTERNO</h5>
	</div>
	<br>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed table-bordered tabla_contratos_para_filtro tabla_contratos_para_filtro" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th class="text-center">Código</th>
					<th class="text-center">Área solicitante</th>
					<th class="text-center">Solicitante</th>
					<th class="text-center">Detalle servicio</th>
					<th class="text-center">Empresa Grupo <?= $pref_empresa_contacto ?> 1</th>
					<th class="text-center">Empresa Grupo <?= $pref_empresa_contacto ?> 2</th>
					<th class="text-center">F. Solicitud</th>
					<th class="text-center">Estado</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Fecha de atención</th>
						<th class="text-center">Días de atención</th>
						<th class="text-center">Abogado</th>
					<?php } ?>
					<th class="text-center">Ver Detalle</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th class="text-center">Código</th>
					<th class="text-center">Área solicitante</th>
					<th class="text-center">Solicitante</th>
					<th class="text-center">Detalle servicio</th>
					<th class="text-center">Empresa Grupo <?= $pref_empresa_contacto ?> 1</th>
					<th class="text-center">Empresa Grupo <?= $pref_empresa_contacto ?> 2</th>
					<th class="text-center">F. Solicitud</th>
					<th class="text-center">Estado</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Fecha de atención</th>
						<th class="text-center">Días de atención</th>
						<th class="text-center">Abogado</th>
					<?php } ?>
					<th class="text-center">Ver Detalle</th>
				</tr>
			</tfoot>
			<tbody>
				<?php
				while ($sel = $sel_query->fetch_assoc()) {
					$nombre_adenda = 'Adenda N° ' . $sel['codigo'] . ' - ' . $sel["sigla_correlativo"] . $sel["codigo_correlativo"];
				?>
					<tr>
						<td class="text-center"><?php echo $nombre_adenda; ?></td>
						<td><?php echo $sel["area"]; ?></td>
						<td><?php echo $sel["solicitante"]; ?></td>
						<td><?php echo $sel["detalle_servicio"]; ?></td>
						<td><?php echo $sel["empresa_at_1"]; ?></td>
						<td><?php echo $sel["empresa_at_2"]; ?></td>
						<td><?php echo $sel["created_at"]; ?></td>
						<td class="text-center">
							<?php
							if ($sel["cancelado_id"] == 1) {
								echo '<span class="badge bg-danger text-white">Cancelado</span>';
							} elseif ($area_id == '33') {
								$bg_estado_solicitud = '';
								switch ($sel["estado_solicitud_id"]) {
									case 1:
										$bg_estado_solicitud = 'bg-default';
										break;
									case 2:
										$bg_estado_solicitud = 'bg-info';
										break;
									case 3:
										$bg_estado_solicitud = 'bg-warning';
										break;
								}
							?>
								<span class="badge <?= $bg_estado_solicitud ?> text-white"><?php echo $sel["nombre_estado_solicitud"]; ?></span>
							<?php } else {
								echo '<span class="badge bg-info text-white">En proceso</span>';
							} ?>
						</td>
						<?php if ($area_id == '33') { ?>
							<td class="text-center">
								<?php echo (!is_null($sel['fecha_cambio_estado_solicitud'])) ? $sel['fecha_cambio_estado_solicitud'] : ''; ?>
							</td>
							<td class="text-center">
								<?php echo (!is_null($sel['dias_habiles'])) ? sec_contrato_solicitud_num_dias_habiles_formato($sel['dias_habiles']) : ''; ?>
							</td>
							<td class="text-center"><?php echo $sel["abogado"]; ?></td>
						<?php } ?>
						<td class="text-center">
							<a class="btn btn-rounded btn-primary btn-sm"
								href="./?sec_id=<?php echo $sec_id; ?>&amp;sub_sec_id=detalle_solicitud_interno&id=<?php echo $sel["contrato_id"]; ?>&adenda_id=<?php echo $sel["id"]; ?>"
								title="Ver detalle">
								<i class="fa fa-eye"></i>
							</a>
							<?php if ($sel["procesado"] != 1 && $sel["cancelado_id"] != 1 && $sel["area_id"] == $area_id_login) { ?>
								<a class="btn btn-rounded btn-danger btn-sm"
									onclick="sec_contrato_solicitud_cancelar_solicitud_modal(1, <?php echo $sel["id"]; ?>, '<?php echo $nombre_adenda; ?>')"
									title="Eliminar Solicitud">
									<i class="fa fa-close"></i>
								</a>
							<?php } ?>
						</td>
					</tr>
				<?php
				}
				?>
			</tbody>
		</table>
	</div>

<?php

}


if ($_POST['action'] == "listar_contrato_adenda_acuerdo_confidencialidad") {
	$menu_consultar = $_POST['menu_consultar'];
	$area_id = $_POST['area_id'];
	$cargo_id = $_POST['cargo_id'];
	$menu_id = $_POST['menu_id'];
	$empresa = $_POST['empresa'];
	$area_solicitante = $_POST['area'];
	$ruc = $_POST['ruc'];
	$razon_social = $_POST['razon_social'];
	$fecha_inicio = trim($_POST['fecha_inicio']);
	$fecha_fin = trim($_POST['fecha_fin']);
	$usuario_permisos = json_decode($_POST['usuario_permisos'], true);
	$sec_id = $_POST['sec_id'];
	$estado_sol_v2 = trim($_POST['estado_sol_v2']);
	$estado_aprobacion = trim($_POST['estado_aprobacion']);
	$fecha_inicio_aprobacion = trim($_POST['fecha_inicio_aprobacion']);
	$fecha_fin_aprobacion = trim($_POST['fecha_fin_aprobacion']);
	$director_aprobacion_id = trim($_POST['director_aprobacion_id']);

	$estado_sol = '';
	if (isset($_POST["estado_sol"])) {
		$estado_sol = $_POST["estado_sol"];
	}

	$where_area = "";
	$where_empresa = "";
	$where_area_solicitante = "";
	$where_ruc = "";
	$where_razon_social = "";
	$where_estado_sol = '';
	$where_estado_sol_v2 = '';
	$where_estado_cancelado = "";
	$where_estado_aprobacion = '';
	$where_director_aprobacion = '';

	if (!(array_key_exists($menu_consultar, $usuario_permisos) && (in_array("ver_todo_solicitud", $usuario_permisos[$menu_consultar]) || in_array("see_all", $usuario_permisos[$menu_consultar])))) {
		if ($area_id == 33) {
			$where_area = " AND ((a.requiere_aprobacion_id = 1 AND a.aprobado_estado_id = 1 ) OR (a.requiere_aprobacion_id = 0) OR (a.requiere_aprobacion_id IS NULL) OR (a.requiere_aprobacion_id = 1 AND p.area_id = 33 ) )";
		} else {
			$where_area = " AND p.area_id = " . $area_id;
		}
	}

	if (!empty($empresa)) {
		$where_empresa = " AND c.empresa_suscribe_id = '" . $empresa . "' ";
	}

	if (!empty($area_solicitante)) {
		$where_area_solicitante = " AND p.area_id = '" . $area_solicitante . "' ";
	}

	if (!empty($ruc)) {
		$where_ruc = " AND c.ruc LIKE '%" . $ruc . "%' ";
	}

	if (!empty($razon_social)) {
		$where_razon_social = " AND c.razon_social  LIKE '%" . $razon_social . "%' ";
	}

	$query_fecha = '';
	if (!empty($fecha_inicio) && !empty($fecha_fin)) {
		$query_fecha = " AND a.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
	} elseif (!empty($fecha_inicio)) {
		$query_fecha = " AND a.created_at >= '$fecha_inicio 00:00:00'";
	} elseif (!empty($fecha_fin)) {
		$query_fecha = " AND a.created_at <= '$fecha_fin 23:59:59'";
	}

	if (!empty($estado_sol)) {
		if ((int) $estado_sol === 99) {
			$where_estado_sol_v2 = " AND a.cancelado_id = 1 ";
		} elseif ((int) $estado_sol === 1) {
			$where_estado_sol = " AND ( a.estado_solicitud_id = 1 OR a.estado_solicitud_id IS NULL ) ";
		} else {
			$where_estado_sol = " AND a.estado_solicitud_id = '" . $estado_sol . "' ";
		}
	}

	if (!empty($estado_sol_v2)) {
		if ((int) $estado_sol_v2 === 1) {
			$where_estado_sol_v2 = " AND ( a.cancelado_id != 1 || a.cancelado_id IS NULL )";
		} elseif ((int) $estado_sol_v2 === 2) {
			$where_estado_sol_v2 = " AND a.cancelado_id = 1 ";
		}
	}

	if ($login["area_id"] == 33) {
		$where_estado_cancelado = " AND ( a.cancelado_id != 1 OR a.cancelado_id IS NULL )";
	}

	$where_fecha_aprobacion = '';
	if (!empty($fecha_inicio_aprobacion) && !empty($fecha_fin_aprobacion)) {
		$where_fecha_aprobacion = " AND a.aprobado_el between '$fecha_inicio_aprobacion 00:00:00' AND '$fecha_fin_aprobacion 23:59:59'";
	} elseif (!empty($fecha_inicio_aprobacion)) {
		$where_fecha_aprobacion = " AND a.aprobado_el >= '$fecha_inicio_aprobacion 00:00:00'";
	} elseif (!empty($fecha_fin_aprobacion)) {
		$where_fecha_aprobacion = " AND a.aprobado_el <= '$fecha_fin_aprobacion 23:59:59'";
	}

	if (!empty($director_aprobacion_id)) {
		$where_director_aprobacion = " AND ( ( a.director_aprobacion_id = $director_aprobacion_id AND (a.requiere_aprobacion_id = 1 AND a.aprobado_estado_id IS NULL) ) OR a.aprobado_por_id = $director_aprobacion_id ) ";
	}

	if ($estado_aprobacion == 1) {
		$where_estado_aprobacion = " AND (a.aprobado_estado_id = 1)";
	} elseif ($estado_aprobacion == 2) {
		$where_estado_aprobacion = " AND (a.aprobado_estado_id = 0)";
	} elseif ($estado_aprobacion == 3) {
		$where_estado_aprobacion = " AND (a.requiere_aprobacion_id = 1)";
	}

	$total_query = $mysqli->query("
		SELECT COUNT(c.contrato_id) as num_rows 
		FROM cont_adendas a
		INNER JOIN cont_contrato c ON a.contrato_id = c.contrato_id
		INNER JOIN tbl_usuarios u ON a.user_created_id = u.id
		INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		INNER JOIN tbl_areas ar ON p.area_id = ar.id
		INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id AND r.status = 1
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
		WHERE c.tipo_contrato_id = 5
		AND a.procesado = 0
		AND a.status = 1
		" . $where_area . "
		" . $where_empresa . "
		" . $where_area_solicitante . "
		" . $where_ruc . "
		" . $where_razon_social . "
		" . $query_fecha . "
		" . $where_estado_sol . "
		" . $where_estado_sol_v2 . "
		" . $where_estado_cancelado . "
		" . $where_director_aprobacion . "
		" . $where_estado_aprobacion . "
		" . $where_fecha_aprobacion . "
		ORDER BY c.created_at DESC
		");

	$resultNum = $total_query->fetch_assoc();
	$num_rows = $resultNum['num_rows'];
	$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
	$per_page = 10;
	$numLinks = 10;
	$total_paginate = ceil($num_rows / $per_page);
	$total_paginate = $total_paginate == 0 ? 1 : $total_paginate;
	$page = $page > $total_paginate ? $total_paginate : $page;
	$offset = ($page - 1) * $per_page;
	$paginate = new Pagination('sec_contratos_solicitud_cambiar_de_pagina', $num_rows, $per_page, $numLinks, $page);

	$sel_query = $mysqli->query("
		SELECT 
			a.id,
			a.codigo,
			c.contrato_id,
			c.nombre_tienda,
			CONCAT(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, ''),' ', IFNULL(p.apellido_materno, '')) AS solicitante,
			p.area_id,
			a.created_at,
			ar.nombre AS area,
			c.razon_social AS parte,
			r.nombre AS empresa_suscribe,
			c.status,
			c.detalle_servicio,
			co.sigla AS sigla_correlativo,
			c.codigo_correlativo,
			a.estado_solicitud_id,
			es.nombre AS nombre_estado_solicitud,
			(CASE WHEN (a.requiere_aprobacion_id = 1 AND a.aprobado_estado_id IS NULL) THEN 'Pendiente' ELSE '' END) as aprob, 	
			(CASE WHEN (a.aprobado_estado_id = 1) THEN 'Aprobado' ELSE '' END) as aprobS, 
			(CASE WHEN (a.aprobado_estado_id = 0) THEN 'Denegado' ELSE '' END) as aprobN, 
			a.procesado,
			a.cancelado_id,
			CONCAT(IFNULL(pud.nombre, ''),' ',IFNULL(pud.apellido_paterno, ''),' ',IFNULL(pud.apellido_materno, '')) AS nombre_del_director_a_aprobar,
			CONCAT(IFNULL(puap.nombre, ''),' ',IFNULL(puap.apellido_paterno, ''),' ',IFNULL(puap.apellido_materno, '')) AS nombre_del_aprobador,
			a.director_aprobacion_id,
			a.aprobado_por_id,
			a.aprobado_el,
			a.fecha_cambio_estado_solicitud,
			a.dias_habiles,
			CONCAT(IFNULL(pa.nombre, ''),' ',IFNULL(pa.apellido_paterno, ''),' ',IFNULL(pa.apellido_materno, '')) AS abogado
		FROM 
			cont_adendas a
			INNER JOIN cont_contrato c ON a.contrato_id = c.contrato_id
			INNER JOIN tbl_usuarios u ON a.user_created_id = u.id
			INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
			INNER JOIN tbl_areas ar ON p.area_id = ar.id
			INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id AND r.status = 1
			LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			LEFT JOIN cont_estado_solicitud es ON es.id = a.estado_solicitud_id
			LEFT JOIN tbl_usuarios ud ON a.director_aprobacion_id = ud.id
			LEFT JOIN tbl_personal_apt pud ON ud.personal_id = pud.id
			LEFT JOIN tbl_usuarios uap ON a.aprobado_por_id = uap.id
			LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id
			LEFT JOIN tbl_usuarios ua ON c.abogado_id = ua.id
			LEFT JOIN tbl_personal_apt pa ON ua.personal_id = pa.id
		WHERE c.tipo_contrato_id = 5
		AND a.procesado = 0
		AND a.status = 1
		" . $where_area . "
		" . $where_empresa . "
		" . $where_area_solicitante . "
		" . $where_ruc . "
		" . $where_razon_social . "
		" . $query_fecha . "
		" . $where_estado_sol . "
		" . $where_estado_sol_v2 . "
		" . $where_estado_cancelado . "
		" . $where_director_aprobacion . "
		" . $where_estado_aprobacion . "
		" . $where_fecha_aprobacion . "
		ORDER BY a.created_at DESC
		");

?>
	<div class="w-100 text-center">
		<h5 style="font-weight: bold; margin: 0px;">SOLICITUDES DE ADENDA DE ACUERDO DE CONFIDENCIALIDAD</h5>
	</div>
	<br>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed table-bordered tabla_contratos_para_filtro" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th class="text-center">Código</th>
					<th class="text-center">Área solicitante</th>
					<th class="text-center">Solicitante</th>
					<th class="text-center">Detalle servicio</th>
					<th class="text-center">Empresa que suscribe el contrato</th>
					<th class="text-center">Proveedor</th>
					<th class="text-center">F. Solicitud</th>
					<th class="text-center">Estado Solicitud</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Fecha de atención</th>
						<th class="text-center">Días de atención</th>
					<?php } ?>
					<th class="text-center">Estado Aprobación</th>
					<th class="text-center">Aprobante</th>
					<th class="text-center">F. Aprobación</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Abogado</th>
					<?php } ?>
					<th class="text-center">Ver Detalle</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th class="text-center">Código</th>
					<th class="text-center">Área solicitante</th>
					<th class="text-center">Solicitante</th>
					<th class="text-center">Detalle servicio</th>
					<th class="text-center">Empresa que suscribe el contrato</th>
					<th class="text-center">Proveedor</th>
					<th class="text-center">F. Solicitud</th>
					<th class="text-center">Estado Solicitud</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Fecha de atención</th>
						<th class="text-center">Días de atención</th>
					<?php } ?>
					<th class="text-center">Estado Aprobación</th>
					<th class="text-center">Aprobante</th>
					<th class="text-center">F. Aprobación</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Abogado</th>
					<?php } ?>
					<th class="text-center">Ver Detalle</th>
				</tr>
			</tfoot>
			<tbody>
				<?php
				while ($sel = $sel_query->fetch_assoc()) {
					$nombre_adenda = 'Adenda N° ' . $sel['codigo'] . ' - ' . $sel["sigla_correlativo"] . $sel["codigo_correlativo"];
					$parte = $sel["parte"];
					$detalle_servicio = trim($sel["detalle_servicio"]);

					if (strlen($detalle_servicio) > 50) {
						$detalle_servicio = substr($detalle_servicio, 0, 50) . ' ...';
					}
				?>
					<tr>
						<td class="text-center"><?php echo $nombre_adenda; ?></td>
						<td><?php echo $sel["area"]; ?></td>
						<td><?php echo $sel["solicitante"]; ?></td>
						<td><?php echo $detalle_servicio; ?></td>
						<td><?php echo $sel["empresa_suscribe"]; ?></td>
						<td><?php echo $parte; ?></td>
						<td><?php echo $sel["created_at"]; ?></td>
						<td class="text-center">
							<?php
							if ($sel["cancelado_id"] == 1) {
								echo '<span class="badge bg-danger text-white">Cancelado</span>';
							} elseif ($area_id == '33') {
								$bg_estado_solicitud = '';
								switch ($sel["estado_solicitud_id"]) {
									case 1:
										$bg_estado_solicitud = 'bg-default';
										break;
									case 2:
										$bg_estado_solicitud = 'bg-info';
										break;
									case 3:
										$bg_estado_solicitud = 'bg-warning';
										break;
								}
							?>
								<span class="badge <?= $bg_estado_solicitud ?> text-white"><?php echo $sel["nombre_estado_solicitud"]; ?></span>
							<?php } else {
								echo '<span class="badge bg-info text-white">En proceso</span>';
							} ?>
						</td>
						<?php if ($area_id == '33') { ?>
							<td class="text-center">
								<?php echo (!is_null($sel['fecha_cambio_estado_solicitud'])) ? $sel['fecha_cambio_estado_solicitud'] : ''; ?>
							</td>
							<td class="text-center">
								<?php echo (!is_null($sel['dias_habiles'])) ? sec_contrato_solicitud_num_dias_habiles_formato($sel['dias_habiles']) : ''; ?>
							</td>
						<?php } ?>
						<td class="text-center">
							<?php
							if (($sel["area_id"] ==  $login["area_id"]) || $usuario_id_login == $sel["director_aprobacion_id"] || $usuario_id_login == $sel["aprobado_por_id"] || $area_id == '6' || ($area_id == '33')) {
								echo $sel["aprob"];
								echo $sel["aprobS"];
								echo $sel["aprobN"];
							}
							?>
						</td>
						<td>
							<?php
							if (($sel["area_id"] ==  $login["area_id"]) || $usuario_id_login == $sel["director_aprobacion_id"] || $usuario_id_login == $sel["aprobado_por_id"] || $area_id == '6' || ($area_id == '33')) {

								if (trim($sel["aprob"]) == "Pendiente") {
									echo $sel["nombre_del_director_a_aprobar"];
								} else {
									echo $sel["nombre_del_aprobador"];
								}
							}
							?>
						</td>
						<td>
							<?php if (($sel["area_id"] ==  $login["area_id"]) || $usuario_id_login == $sel["director_aprobacion_id"] || $usuario_id_login == $sel["aprobado_por_id"] || $area_id == '6' || ($area_id == '33')) {
								echo $sel["aprobado_el"];
							}
							?>
						</td>
						<?php if ($area_id == '33') { ?>
							<td><?php echo $sel["abogado"]; ?></td>
						<?php } ?>
						<td class="text-center">
							<a class="btn btn-rounded btn-primary btn-sm"
								href="./?sec_id=<?php echo $sec_id; ?>&amp;sub_sec_id=detalle_solicitud_acuerdo_confidencialidad&id=<?php echo $sel["contrato_id"]; ?>&adenda_id=<?php echo $sel["id"]; ?>"
								title="Ver detalle">
								<i class="fa fa-eye"></i>
							</a>
							<?php if ($sel["procesado"] != 1 && $sel["cancelado_id"] != 1 && $sel["area_id"] == $area_id_login) { ?>
								<a class="btn btn-rounded btn-danger btn-sm"
									onclick="sec_contrato_solicitud_cancelar_solicitud_modal(1, <?php echo $sel["id"]; ?>, '<?php echo $nombre_adenda . ' ' . $parte; ?>')"
									title="Eliminar Solicitud">
									<i class="fa fa-close"></i>
								</a>
							<?php } ?>
						</td>
					</tr>
				<?php
				}
				?>
			</tbody>
		</table>
	</div>

<?php

}


if ($_POST['action'] == "listar_contrato_adenda_agente") {
	$menu_consultar = $_POST['menu_consultar'];
	$area_id = $_POST['area_id'];
	$cargo_id = $_POST['cargo_id'];
	$menu_id = $_POST['menu_id'];
	$empresa = $_POST['empresa'];
	$fecha_inicio = trim($_POST['fecha_inicio']);
	$fecha_fin = trim($_POST['fecha_fin']);
	$usuario_permisos = json_decode($_POST['usuario_permisos'], true);
	$sec_id = $_POST['sec_id'];
	$estado_sol_v2 = trim($_POST['estado_sol_v2']);

	$where_empresa = "";
	$where_estado_sol_v2 = '';
	$where_estado_cancelado = "";

	if (!empty($empresa)) {
		$where_empresa = " AND c.empresa_suscribe_id = '" . $empresa . "' ";
	}

	$query_fecha = '';
	if (!empty($fecha_inicio) && !empty($fecha_fin)) {
		$query_fecha = " AND a.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
	} elseif (!empty($fecha_inicio)) {
		$query_fecha = " AND a.created_at >= '$fecha_inicio 00:00:00'";
	} elseif (!empty($fecha_fin)) {
		$query_fecha = " AND a.created_at <= '$fecha_fin 23:59:59'";
	}

	if (!empty($estado_sol_v2)) {
		if ((int) $estado_sol_v2 === 1) {
			$where_estado_sol_v2 = " AND ( a.cancelado_id != 1 || a.cancelado_id IS NULL )";
		} elseif ((int) $estado_sol_v2 === 2) {
			$where_estado_sol_v2 = " AND a.cancelado_id = 1 ";
		}
	}

	if ($login["area_id"] == 33) {
		$where_estado_cancelado = " AND ( a.cancelado_id != 1 OR a.cancelado_id IS NULL )";
	}

	$total_query = $mysqli->query("
		SELECT COUNT(c.contrato_id) as num_rows 
		FROM cont_adendas a
		INNER JOIN cont_contrato c ON a.contrato_id = c.contrato_id
		INNER JOIN tbl_usuarios u ON a.user_created_id = u.id
		INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		INNER JOIN tbl_areas ar ON p.area_id = ar.id
		INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id AND r.status = 1
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
		WHERE c.tipo_contrato_id = 6
		AND a.procesado = 0
		AND a.status = 1
		" . $where_empresa . "
		" . $query_fecha . "
		" . $where_estado_sol_v2 . "
		ORDER BY c.created_at DESC
		");

	$resultNum = $total_query->fetch_assoc();
	$num_rows = $resultNum['num_rows'];
	$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
	$per_page = 10;
	$numLinks = 10;
	$total_paginate = ceil($num_rows / $per_page);
	$total_paginate = $total_paginate == 0 ? 1 : $total_paginate;
	$page = $page > $total_paginate ? $total_paginate : $page;
	$offset = ($page - 1) * $per_page;
	$paginate = new Pagination('sec_contratos_solicitud_cambiar_de_pagina', $num_rows, $per_page, $numLinks, $page);

	$sel_query = $mysqli->query("
		SELECT 
			a.id,
			a.codigo,
			c.contrato_id,
			c.nombre_tienda,
			CONCAT(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, ''),' ', IFNULL(p.apellido_materno, '')) AS solicitante, 
			p.area_id,
			a.created_at,
			ar.nombre AS area,
			c.razon_social AS parte,
			r.nombre AS empresa_suscribe,
			c.status,
			c.detalle_servicio,
			co.sigla AS sigla_correlativo,
			c.codigo_correlativo,
			a.estado_solicitud_id,
			es.nombre AS nombre_estado_solicitud,
			a.procesado,
			a.cancelado_id,
			CONCAT(IFNULL(pud.nombre, ''),' ',IFNULL(pud.apellido_paterno, ''),' ',IFNULL(pud.apellido_materno, '')) AS nombre_del_director_a_aprobar,
			CONCAT(IFNULL(puap.nombre, ''),' ',IFNULL(puap.apellido_paterno, ''),' ',IFNULL(puap.apellido_materno, '')) AS nombre_del_aprobador,
			a.director_aprobacion_id,
			a.aprobado_por_id,
			a.aprobado_el,
			a.fecha_cambio_estado_solicitud,
			a.dias_habiles,
			CONCAT(IFNULL(pa.nombre, ''),' ',IFNULL(pa.apellido_paterno, ''),' ',IFNULL(pa.apellido_materno, '')) AS abogado,
			c.nombre_agente
		FROM cont_adendas a
		INNER JOIN cont_contrato c ON a.contrato_id = c.contrato_id
		INNER JOIN tbl_usuarios u ON a.user_created_id = u.id
		INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		INNER JOIN tbl_areas ar ON p.area_id = ar.id
		LEFT JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id AND r.status = 1
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
		LEFT JOIN cont_estado_solicitud es ON es.id = a.estado_solicitud_id
		LEFT JOIN tbl_usuarios ud ON a.director_aprobacion_id = ud.id
		LEFT JOIN tbl_personal_apt pud ON ud.personal_id = pud.id
		LEFT JOIN tbl_usuarios uap ON a.aprobado_por_id = uap.id
		LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id
		LEFT JOIN tbl_usuarios ua ON c.abogado_id = ua.id
		LEFT JOIN tbl_personal_apt pa ON ua.personal_id = pa.id
		WHERE c.tipo_contrato_id = 6
		AND a.procesado = 0
		AND a.status = 1
		" . $where_empresa . "
		" . $query_fecha . "
		" . $where_estado_sol_v2 . "
		" . $where_estado_cancelado . "
		ORDER BY a.created_at DESC
		");

?>
	<div class="w-100 text-center">
		<h5 style="font-weight: bold; margin: 0px;">SOLICITUDES DE ADENDA DE CONTRATO DE AGENTES</h5>
	</div>
	<br>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed table-bordered tabla_contratos_para_filtro" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th class="text-center">Código</th>
					<th class="text-center">Agente</th>
					<th class="text-center">Área solicitante</th>
					<th class="text-center">Solicitante</th>
					<th class="text-center">Detalle servicio</th>
					<th class="text-center">Empresa que suscribe el contrato</th>
					<th class="text-center">Proveedor</th>
					<th class="text-center">F. Solicitud</th>
					<th class="text-center">Estado</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Fecha de atención</th>
						<th class="text-center">Días de atención</th>
						<th class="text-center">Abogado</th>
					<?php } ?>
					<th class="text-center">Estado Aprobación</th>
					<th class="text-center">Aprobante</th>
					<th class="text-center">F. Aprobación</th>
					<th class="text-center">Ver Detalle</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th class="text-center">Código</th>
					<th class="text-center">Agente</th>
					<th class="text-center">Área solicitante</th>
					<th class="text-center">Solicitante</th>
					<th class="text-center">Detalle servicio</th>
					<th class="text-center">Empresa que suscribe el contrato</th>
					<th class="text-center">Proveedor</th>
					<th class="text-center">F. Solicitud</th>
					<th class="text-center">Estado</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Fecha de atención</th>
						<th class="text-center">Días de atención</th>
						<th class="text-center">Abogado</th>
					<?php } ?>
					<th class="text-center">Estado Aprobación</th>
					<th class="text-center">Aprobante</th>
					<th class="text-center">F. Aprobación</th>
					<th class="text-center">Ver Detalle</th>
				</tr>
			</tfoot>
			<tbody>
				<?php
				while ($sel = $sel_query->fetch_assoc()) {
					$nombre_adenda = 'Adenda N° ' . $sel['codigo'] . ' - ' . $sel["sigla_correlativo"] . $sel["codigo_correlativo"];
					$parte = $sel["parte"];
				?>
					<tr>
						<td class="text-center"><?php echo $nombre_adenda; ?></td>
						<td><?php echo $sel["nombre_agente"]; ?></td>
						<td><?php echo $sel["area"]; ?></td>
						<td><?php echo $sel["solicitante"]; ?></td>
						<td><?php echo $sel["detalle_servicio"]; ?></td>
						<td><?php echo $sel["empresa_suscribe"]; ?></td>
						<td><?php echo $parte; ?></td>
						<td><?php echo $sel["created_at"]; ?></td>
						<td class="text-center">
							<?php
							if ($sel["cancelado_id"] == 1) {
								echo '<span class="badge bg-danger text-white">Cancelado</span>';
							} elseif ($area_id == '33') {
								$bg_estado_solicitud = '';
								switch ($sel["estado_solicitud_id"]) {
									case 1:
										$bg_estado_solicitud = 'bg-default';
										break;
									case 2:
										$bg_estado_solicitud = 'bg-info';
										break;
									case 3:
										$bg_estado_solicitud = 'bg-warning';
										break;
								}
							?>
								<span class="badge <?= $bg_estado_solicitud ?> text-white"><?php echo $sel["nombre_estado_solicitud"]; ?></span>
						</td>
						<?php if ($area_id == '33') { ?>
							<td class="text-center">
								<?php echo (!is_null($sel['fecha_cambio_estado_solicitud'])) ? $sel['fecha_cambio_estado_solicitud'] : ''; ?>
							</td>
							<td class="text-center">
								<?php echo (!is_null($sel['dias_habiles'])) ? sec_contrato_solicitud_num_dias_habiles_formato($sel['dias_habiles']) : ''; ?>
							</td>
							<td><?php echo $sel["abogado"]; ?></td>
						<?php } ?>
					<?php } else {
								echo '<span class="badge bg-info text-white">En proceso</span>';
							} ?>
					<td class="text-center">
						<?php
						if (($sel["area_id"] ==  $login["area_id"]) || $usuario_id_login == $sel["director_aprobacion_id"] || $usuario_id_login == $sel["aprobado_por_id"] || $area_id == '6' || ($area_id == '33')) {
							echo $sel["aprob"];
							echo $sel["aprobS"];
							echo $sel["aprobN"];
						}
						?>
					</td>
					<td>
						<?php
						if (($sel["area_id"] ==  $login["area_id"]) || $usuario_id_login == $sel["director_aprobacion_id"] || $usuario_id_login == $sel["aprobado_por_id"] || $area_id == '6' || ($area_id == '33')) {

							if (trim($sel["aprob"]) == "Pendiente") {
								echo $sel["nombre_del_director_a_aprobar"];
							} else {
								echo $sel["nombre_del_aprobador"];
							}
						}
						?>
					</td>
					<td>
						<?php if (($sel["area_id"] ==  $login["area_id"]) || $usuario_id_login == $sel["director_aprobacion_id"] || $usuario_id_login == $sel["aprobado_por_id"] || $area_id == '6' || ($area_id == '33')) {
							echo $sel["aprobado_el"];
						}
						?>
					</td>
					<td class="text-center">
						<a class="btn btn-rounded btn-primary btn-sm"
							href="./?sec_id=<?php echo $sec_id; ?>&amp;sub_sec_id=detalle_agente&id=<?php echo $sel["contrato_id"]; ?>&adenda_id=<?php echo $sel["id"]; ?>"
							title="Ver detalle">
							<i class="fa fa-eye"></i>
						</a>
						<?php if ($sel["procesado"] != 1 && $sel["cancelado_id"] != 1 && $sel["area_id"] == $area_id_login) { ?>
							<a class="btn btn-rounded btn-danger btn-sm"
								onclick="sec_contrato_solicitud_cancelar_solicitud_modal(1, <?php echo $sel["id"]; ?>, '<?php echo $nombre_adenda . ' ' . $parte; ?>')"
								title="Eliminar Solicitud">
								<i class="fa fa-close"></i>
							</a>
						<?php } ?>
					</td>
					</tr>
				<?php
				}
				?>
			</tbody>
		</table>
	</div>

<?php

}

if ($_POST['action'] == "listar_resolucion_contrato") {
	$menu_consultar = $_POST['menu_consultar'];
	$area_id = $_POST['area_id'];
	$cargo_id = $_POST['cargo_id'];
	$menu_id = $_POST['menu_id'];
	$fecha_inicio = trim($_POST['fecha_inicio']);
	$fecha_fin = trim($_POST['fecha_fin']);
	$usuario_permisos = json_decode($_POST['usuario_permisos'], true);
	$sec_id = $_POST['sec_id'];

	$estado_sol = isset($_POST['estado_sol']) ? $_POST['estado_sol'] : '';
	$estado_sol_v2 = trim($_POST['estado_sol_v2']);
	$rc_tipo_contrato_id = isset($_POST['rc_tipo_contrato_id']) ? $_POST['rc_tipo_contrato_id'] : '';

	$query_fecha = '';
	$query_estado = '';
	$query_tipo_contrato = '';
	$query_area = '';
	$where_estado_sol_v2 = '';
	$where_estado_cancelado = "";

	// if ( !($area_id == '33' || $area_id == 6) ) {
	// 	$query_area = ' AND tpa.area_id ='.$area_id;
	// } 
	if (!empty($fecha_inicio) && !empty($fecha_fin)) {
		$query_fecha = " AND r.created_at between '" . $fecha_inicio . "' AND '" . $fecha_fin . "'";
	}
	if (!empty($rc_tipo_contrato_id)) {
		$query_tipo_contrato = " AND r.tipo_contrato_id = '" . $rc_tipo_contrato_id . "'";
	}
	if (!empty($estado_sol)) {
		if ((int) $estado_sol === 99) {
			$where_estado_sol_v2 = " AND r.cancelado_id = 1 ";
		} elseif ((int) $estado_sol === 1) {
			$query_estado = " AND ( r.estado_solicitud_legal = 1 OR r.estado_solicitud_legal IS NULL )";
		} else {
			$query_estado = " AND r.estado_solicitud_legal = '" . $estado_sol . "' ";
		}
	}

	if (!empty($estado_sol_v2)) {
		if ((int) $estado_sol_v2 === 1) {
			$where_estado_sol_v2 = " AND ( r.cancelado_id != 1 || r.cancelado_id IS NULL )";
		} elseif ((int) $estado_sol_v2 === 2) {
			$where_estado_sol_v2 = " AND r.cancelado_id = 1 ";
		}
	}

	if ($login["area_id"] == 33) {
		$where_estado_cancelado = " AND ( r.cancelado_id != 1 OR r.cancelado_id IS NULL )";
	}

	$total_query = $mysqli->query("
		SELECT COUNT(r.id) as num_rows 
		FROM cont_resolucion_contrato AS r
		INNER JOIN cont_contrato c ON c.contrato_id = r.contrato_id
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
		INNER JOIN cont_tipo_contrato AS tc ON tc.id = c.tipo_contrato_id
		
		INNER JOIN tbl_usuarios tu ON r.user_created_id = tu.id
		INNER JOIN tbl_personal_apt tpa ON tu.personal_id = tpa.id

		WHERE r.status = 1 AND r.estado_solicitud_id = 1
		" . $query_fecha . "
		" . $query_tipo_contrato . "
		" . $query_estado . "
		" . $where_estado_sol_v2 . "
		" . $where_estado_cancelado . "
		" . $query_area . "
		ORDER BY r.created_at DESC
		");

	$resultNum = $total_query->fetch_assoc();
	$num_rows = $resultNum['num_rows'];
	$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
	$per_page = 10;
	$numLinks = 10;
	$total_paginate = ceil($num_rows / $per_page);
	$total_paginate = $total_paginate == 0 ? 1 : $total_paginate;
	$page = $page > $total_paginate ? $total_paginate : $page;
	$offset = ($page - 1) * $per_page;
	$paginate = new Pagination('sec_contratos_solicitud_cambiar_de_pagina', $num_rows, $per_page, $numLinks, $page);



	$consult_query = "
	SELECT 
		r.id,
		c.tipo_contrato_id,
		r.contrato_id,
		r.motivo,
		r.fecha_solicitud,
		DATE_FORMAT(r.fecha_resolucion,'%d-%m-%Y') as fecha_resolucion,
		CONCAT(IFNULL(tpa.nombre, ''),' ',IFNULL(tpa.apellido_paterno, ''),	' ',	IFNULL(tpa.apellido_materno, '')) AS usuario_solicitud,
		tpa.area_id,
		r.status,
		DATE_FORMAT(r.created_at,'%d-%m-%Y %H:%i:%s') as created_at,
		co.sigla,
		c.codigo_correlativo,
		tc.nombre AS nombre_tipo_contrato,
		r.estado_solicitud_id,
		r.estado_solicitud_legal,
		es.nombre AS nombre_estado_solicitud_legal,
		r.fecha_cambio_estado_solicitud,
		r.dias_habiles,
		(CASE
		    WHEN r.estado_solicitud_id = 1 THEN 'En Proceso'
		    WHEN r.estado_solicitud_id = 2 THEN 'Procesado'
		    ELSE ''
		END) as estado_solicitud, 
		c.nombre_tienda,
		c.razon_social AS parte,
		c.nombre_agente,
		r.cancelado_id,

		CONCAT(IFNULL(tpag.nombre, ''),' ',IFNULL(tpag.apellido_paterno, ''),	' ',	IFNULL(tpag.apellido_materno, '')) AS aprobante,
		(CASE
		    WHEN r.estado_aprobacion_gerencia = 0 THEN 'Pendiente'
			WHEN r.estado_aprobacion_gerencia = 1 THEN 'Aprobado'
		    WHEN r.estado_aprobacion_gerencia = 2 THEN 'Rechazado'
		    ELSE ''
		END) as estado_aprobacion, 
		r.fecha_aprobacion_gerencia,
		CONCAT(IFNULL(pa.nombre, ''),' ',IFNULL(pa.apellido_paterno, ''),' ',IFNULL(pa.apellido_materno, '')) AS abogado

	FROM cont_resolucion_contrato AS r
	INNER JOIN cont_contrato c ON c.contrato_id = r.contrato_id
	LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
	INNER JOIN cont_tipo_contrato AS tc ON tc.id = c.tipo_contrato_id
	LEFT JOIN cont_estado_solicitud es ON es.id = r.estado_solicitud_legal
	INNER JOIN tbl_usuarios tu ON r.user_created_id = tu.id
	INNER JOIN tbl_personal_apt tpa ON tu.personal_id = tpa.id

	LEFT JOIN tbl_usuarios tua ON r.aprobacion_gerencia_id = tua.id
	LEFT JOIN tbl_personal_apt tpag ON tua.personal_id = tpag.id

	LEFT JOIN tbl_usuarios ua ON c.abogado_id = ua.id
	LEFT JOIN tbl_personal_apt pa ON ua.personal_id = pa.id

	WHERE r.status = 1 AND r.estado_solicitud_id = 1
	" . $query_fecha . "
	" . $query_tipo_contrato . "
	" . $query_estado . "
	" . $where_estado_sol_v2 . "
	" . $where_estado_cancelado . "
	" . $query_area . "
	ORDER BY r.created_at DESC
	";
	$sel_query = $mysqli->query($consult_query);
?>
	<div class="w-100 text-center">
		<h5 style="font-weight: bold; margin: 0px;">SOLICITUDES DE RESOLUCIÓN DE CONTRATO</h5>
	</div>
	<br>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed table-bordered tabla_contratos_para_filtro" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th class="text-center">Tipo Contrato</th>
					<th class="text-center">Código</th>
					<th class="text-center">Nombre</th>
					<th class="text-center">Solicitante</th>
					<th class="text-center">Motivo</th>
					<th class="text-center">Fecha <br> Resolución</th>
					<th class="text-center">F. Solicitud</th>
					<th class="text-center">Estado <br> Aprobante</th>
					<th class="text-center">Aprobante</th>
					<th class="text-center">F. Aprobación</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Abogado</th>
						<th class="text-center">Seguimiento</th>
					<?php } ?>
					<th class="text-center">Estado</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Fecha de atención</th>
						<th class="text-center">Días de atención</th>
					<?php } ?>

					<th class="text-center">Ver Detalle</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th class="text-center">Tipo Contrato</th>
					<th class="text-center">Código</th>
					<th class="text-center">Nombre</th>
					<th class="text-center">Solicitante</th>
					<th class="text-center">Motivo</th>
					<th class="text-center">Fecha <br> Resolución</th>
					<th class="text-center">F. Solicitud</th>
					<th class="text-center">Estado <br> Aprobante</th>
					<th class="text-center">Aprobante</th>
					<th class="text-center">F. Aprobación</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Abogado</th>
						<th class="text-center">Seguimiento</th>
					<?php } ?>
					<th class="text-center">Estado</th>
					<?php if ($area_id == '33') { ?>
						<th class="text-center">Fecha de atención</th>
						<th class="text-center">Días de atención</th>
					<?php } ?>
					<th class="text-center">Ver Detalle</th>
				</tr>
			</tfoot>
			<tbody>
				<?php
				while ($sel = $sel_query->fetch_assoc()) {
					$nombre_tipo_contrato = $sel["nombre_tipo_contrato"];
					$correlativo = $sel["sigla"] . $sel["codigo_correlativo"];
					$nombre = '';
					switch ($sel["tipo_contrato_id"]) {
						case '1':
							$nombre = $sel['nombre_tienda'];
							break;
						case '2':
							$nombre = $sel['parte'];
							break;
						case '5':
							$nombre = $sel['parte'];
							break;
						case '6':
							$nombre = $sel['nombre_agente'];
							break;
						default:
							$nombre = '';
							break;
					}
				?>
					<tr>
						<td><?php echo $nombre_tipo_contrato; ?></td>
						<td class="text-center"><?php echo $correlativo; ?></td>
						<td class="text-left"><?php echo $nombre; ?></td>
						<td><?php echo $sel["usuario_solicitud"]; ?></td>
						<td><?php echo $sel["motivo"]; ?></td>
						<td class="text-center"><?php echo $sel["fecha_resolucion"]; ?></td>
						<td class="text-center"><?php echo $sel["created_at"]; ?></td>
						<td class="text-center"><?php echo $sel["estado_aprobacion"]; ?></td>
						<td class="text-center"><?php echo $sel["aprobante"]; ?></td>
						<td class="text-center"><?php echo $sel["fecha_aprobacion_gerencia"]; ?></td>
						<?php if ($area_id == '33') { ?>
							<td class="text-center"><?php echo $sel["abogado"]; ?></td>
							<?php
							$seg_proc = new SeguimientoProceso();
							$data_sp['tipo_documento_id'] = 3;
							$data_sp['proceso_id'] = $sel['id'];
							$data_sp['proceso_detalle_id'] = 0;
							$etapa_seguimiento = $seg_proc->obtener_ultimo_seguimiento($data_sp);
							?>
							<td><?php echo $etapa_seguimiento; ?></td>
						<?php } ?>
						<td class="text-center">
							<?php
							if ($sel["cancelado_id"] == 1) {
								echo '<span class="badge bg-danger text-white">Cancelado</span>';
							} else {
								$bg_state = '';
								switch ($sel['estado_solicitud_legal']) {
									case '2':
										$bg_state = 'bg-success';
										break;
									case '3':
										$bg_state = 'bg-warning';
										break;
									case '4':
										$bg_state = 'bg-danger';
										break;
								}
							?>
								<span class="badge <?= $bg_state ?>"><?php echo $sel['nombre_estado_solicitud_legal']; ?></span>
							<?php } ?>
						</td>
						<?php
						$url_solicitud = '';
						switch ($sel['tipo_contrato_id']) {
							case '1':
								$url_solicitud = "detalle_solicitud";
								break;
							case '2':
								$url_solicitud = "detalleSolicitudProveedor";
								break;
							case '5':
								$url_solicitud = "detalle_solicitud_acuerdo_confidencialidad";
								break;
							case '6':
								$url_solicitud = "detalle_agente";
								break;
							case '7':
								$url_solicitud = "detalle_solicitud_interno";
								break;
						}
						?>
						<?php if ($area_id == '33') { ?>
							<td class="text-center">
								<?php echo (!is_null($sel['fecha_cambio_estado_solicitud'])) ? $sel['fecha_cambio_estado_solicitud'] : ''; ?>
							</td>
							<td class="text-center">
								<?php echo (!is_null($sel['dias_habiles'])) ? sec_contrato_solicitud_num_dias_habiles_formato($sel['dias_habiles']) : ''; ?>
							</td>
						<?php } ?>
						<td class="text-center">
							<a class="btn btn-rounded btn-primary btn-sm"
								href="./?sec_id=<?php echo $sec_id; ?>&amp;sub_sec_id=<?= $url_solicitud ?>&id=<?php echo $sel["contrato_id"]; ?>&resolucion_id=<?php echo $sel["id"]; ?>"
								title="Ver detalle">
								<i class="fa fa-eye"></i>
							</a>
							<?php if ($sel["cancelado_id"] != 1 && $sel["estado_solicitud_id"] != 2 && $sel["area_id"] == $area_id_login) { ?>
								<a class="btn btn-rounded btn-danger btn-sm"
									onclick="sec_contrato_solicitud_cancelar_solicitud_modal(2, <?php echo $sel["id"]; ?>, '<?php echo ' Resolución de ' . $nombre_tipo_contrato . ' ' . $correlativo; ?>')"
									title="Eliminar Solicitud">
									<i class="fa fa-close"></i>
								</a>
							<?php } ?>
						</td>
					</tr>
				<?php
				}
				?>
			</tbody>
		</table>
	</div>

<?php

}



if (isset($_POST["action"]) && $_POST["action"] === "cancelar_solicitud") {
	$usuario_id = $login ? $login['id'] : null;

	if (!((int) $usuario_id > 0)) {
		$error = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y hacer click e el boton: Cancelar Solicitud.";
		sec_contrato_solicitud_enviar_error($error);
	}

	$tipo_de_solicitud = trim($_POST["tipo_de_solicitud"]);
	$solicitud_id_temporal = trim($_POST["solicitud_id_temporal"]);

	if (empty($tipo_de_solicitud)) {
		$error = "tipo solicitud id invalido";
		sec_contrato_solicitud_enviar_error($error);
	}

	if (empty($solicitud_id_temporal)) {
		$error = "id de la solicitud invalido";
		sec_contrato_solicitud_enviar_error($error);
	}

	$created_at = date('Y-m-d H:i:s');

	$cancelado_motivo = replace_invalid_caracters(trim($_POST["cancelado_motivo"]));

	if ($tipo_de_solicitud == 1) { // adenda
		$tabla = "cont_adendas";
		$query_select = "SELECT a.contrato_id, a.codigo FROM cont_adendas AS a WHERE a.id = " . $solicitud_id_temporal;
		$result_adenda = $mysqli->query($query_select);
		$row = $result_adenda->fetch_assoc();

		$query_select = "SELECT a.codigo FROM cont_adendas AS a 
		WHERE a.status = 1 
		AND a.cancelado_el IS NULL 
		AND a.contrato_id = " . $row['contrato_id'] . "
		ORDER BY a.id DESC
		LIMIT 1";
		$result_adenda = $mysqli->query($query_select);
		$row_adenda = $result_adenda->fetch_assoc();

		if ($row_adenda['codigo'] != $row['codigo']) {
			sec_contrato_solicitud_enviar_error('Solo se puede cancelar la última adenda (#' . $row_adenda['codigo'] . ')');
			exit();
		}
	} elseif ($tipo_de_solicitud == 2) {
		$tabla = "cont_resolucion_contrato";

		$query_select = "SELECT r.id, r.tipo_contrato_id, r.contrato_id, r.contrato_detalle_id
		FROM cont_resolucion_contrato AS r WHERE r.id = " . $solicitud_id_temporal;
		$result_resol = $mysqli->query($query_select);
		$row = $result_resol->fetch_assoc();

		//estado 4 es No aplica
		if ($row['tipo_contrato_id'] == 1) {
			$query_update_cont = "UPDATE cont_contrato_detalle SET estado_resolucion = '4' WHERE id = " . $row['contrato_detalle_id'];
			$mysqli->query($query_update_cont);
		} else {
			$query_update_cont = "UPDATE cont_contrato SET estado_resolucion = '4' WHERE contrato_id = " . $row['contrato_id'];
			$mysqli->query($query_update_cont);
		}
	} else {
		$error = "Error al actualizar";
		sec_contrato_solicitud_enviar_error($error);
	}


	$query_update = "
	UPDATE $tabla
	SET 
		cancelado_id = 1,
		cancelado_por_id = $usuario_id,
		cancelado_el = '$created_at',
		cancelado_motivo = '$cancelado_motivo'
	WHERE id = $solicitud_id_temporal";

	$mysqli->query($query_update);

	if ($tipo_de_solicitud == 1) { // Anular archivos anexos a la adenda
		$query_update_archivo = "UPDATE cont_archivos SET status ='0' , user_updated_id = " . $usuario_id . ", updated_at = '" . $created_at . "' WHERE  adenda_id = " . $solicitud_id_temporal;
		$mysqli->query($query_update_archivo);
	}


	if ($mysqli->error) {
		sec_contrato_solicitud_enviar_error($mysqli->error . $query_update);
	}

	send_email_cancelar_solicitud_varias($tipo_de_solicitud, $solicitud_id_temporal);
}



function send_email_cancelar_solicitud_varias($tipo_de_solicitud, $solicitud_id_temporal)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	if ($tipo_de_solicitud == 1) {
		$query_solicitud_cancelada = "
		SELECT 
			c.contrato_id,
			c.tipo_contrato_id,
			c.nombre_tienda,
			CONCAT(IFNULL(tpa.nombre, ''),' ',IFNULL(tpa.apellido_paterno, ''),	' ',	IFNULL(tpa.apellido_materno, '')) AS cancelado_por,
			a.cancelado_el,
			a.cancelado_motivo,
			a.codigo,
			tpa.correo AS correo_cancelador,
			co.sigla AS sigla_correlativo,
			c.codigo_correlativo
		FROM
			cont_adendas a
			INNER JOIN cont_contrato c ON a.contrato_id = c.contrato_id
			LEFT JOIN tbl_usuarios tu ON a.cancelado_por_id = tu.id
			LEFT JOIN tbl_personal_apt tpa ON tu.personal_id = tpa.id
			LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
		WHERE
			a.id = $solicitud_id_temporal
		";
	} elseif ($tipo_de_solicitud == 2) {
		$query_solicitud_cancelada = "
		SELECT 
			r.contrato_id,
			c.tipo_contrato_id,
			CONCAT(IFNULL(tpa.nombre, ''),' ',IFNULL(tpa.apellido_paterno, ''),	' ',	IFNULL(tpa.apellido_materno, '')) AS cancelado_por,
			r.cancelado_el,
			r.cancelado_motivo,
			tpa.correo AS correo_cancelador,
			co.sigla AS sigla_correlativo,
			c.codigo_correlativo
		FROM
			cont_resolucion_contrato AS r
			INNER JOIN cont_contrato c ON c.contrato_id = r.contrato_id
			LEFT JOIN tbl_usuarios tu ON r.cancelado_por_id = tu.id
			LEFT JOIN tbl_personal_apt tpa ON tu.personal_id = tpa.id
			LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
		WHERE
			r.id = $solicitud_id_temporal
		";
	}

	$sel_query = $mysqli->query($query_solicitud_cancelada);

	if ($mysqli->error) {
		sec_contrato_solicitud_enviar_error($mysqli->error . $query_solicitud_cancelada);
	}

	if (!($sel_query->num_rows > 0)) {
		$error = "Al enviar email de confirmacion el num_rows is 0.";
		sec_contrato_solicitud_enviar_error($error);
	}

	$body = "";
	$body .= '<html>';

	$tipo_contrato_id = 0;
	$url_solicitud = "";
	$fecha_atencion_gerencia_proveedor = "";
	$aprobacion_gerencia_proveedor = "";
	$codigo = "";
	$nombre_tienda = "";
	$correos_add = [];

	while ($sel = $sel_query->fetch_assoc()) {
		$contrato_id = $sel['contrato_id'];
		$tipo_contrato_id = $sel['tipo_contrato_id'];
		$cancelado_por = $sel['cancelado_por'];
		$cancelado_el = $sel['cancelado_el'];
		$cancelado_motivo = $sel['cancelado_motivo'];
		$sigla_correlativo = $sel['sigla_correlativo'];
		$nombre_tienda = $sel['nombre_tienda'];
		$codigo_correlativo = $sel['codigo_correlativo'];
		$correo_cancelador = trim($sel['correo_cancelador']);

		if ($tipo_de_solicitud == 1) {
			$codigo = $sel['codigo'];
		}

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

		$body .= '<thead>';

		$body .= '<tr>';
		$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
		$body .= '<b>Solicitud Cancelada</b>';
		$body .= '</th>';
		$body .= '</tr>';

		$body .= '</thead>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Cancelado por:</b></td>';
		$body .= '<td>' . $cancelado_por . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Cancelado el:</b></td>';
		$body .= '<td>' . $cancelado_el . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Motivo:</b></td>';
		$body .= '<td>' . $cancelado_motivo . '</td>';
		$body .= '</tr>';

		$body .= '</table>';
		$body .= '</div>';

		if (sec_contrato_solicitud_is_valid_email($correo_cancelador)) {
			array_push($correos_add, $correo_cancelador);
		}

		// Usuarios del área Legal
		// array_push($correos_add, "mayra.duffoo@testtest.apuestatotal.com" );
		// array_push($correos_add, "sandra.murrugarra@testtest.apuestatotal.com" );
		// array_push($correos_add, "carolina.cano@testtest.apuestatotal.com" );
		// array_push($correos_add, "ingrid.escobar@testtest.apuestatotal.com" );

	}
	$body .= '<div>';
	$body .= '<br>';
	$body .= '</div>';

	switch ($tipo_contrato_id) {
		case '1':
			$url_solicitud = "detalle_solicitud";
			break;
		case '2':
			$url_solicitud = "detalleSolicitudProveedor";
			break;
		case '5':
			$url_solicitud = "detalle_solicitud_acuerdo_confidencialidad";
			break;
		case '6':
			$url_solicitud = "detalle_agente";
			break;
		case '7':
			$url_solicitud = "detalle_solicitud_interno";
			break;
	}

	$body .= '<div style="width: 700px; text-align: center; font-family: arial;">';
	$body .= '<a href="' . $host . '/?sec_id=contrato&sub_sec_id=' . $url_solicitud . '&id=' . $contrato_id . '" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
	$body .= '<b>Ver Solicitud</b>';
	$body .= '</a>';
	$body .= '</div>';

	$body .= '<div>';
	$body .= '<br>';
	$body .= '</div>';

	$body .= '</html>';
	$body .= "";

	$correos = new Correos(env('SEND_EMAIL'), env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_cancelar_solicitud_adenda($correos_add);

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];
	$nombre_tipo_contrato = "";

	if ($tipo_de_solicitud == 1) {
		$titulo_cancelacion = (!empty($nombre_tienda) ? $nombre_tienda . ' - ' : '') . 'Adenda N° ' . $codigo;
	} elseif ($tipo_de_solicitud == 2) {
		$titulo_cancelacion = 'Resolución de Contrato';
	}

	switch ($tipo_contrato_id) {
		case '1':
			$nombre_tipo_contrato = "Contrato de Arrendamiento";
			break;
		case '2':
			$nombre_tipo_contrato = "Contrato de Proveedor";
			break;
		case '5':
			$nombre_tipo_contrato = "Contrato de Acuerdo de Confidencialidad";
			break;
		case '6':
			$nombre_tipo_contrato = "Contrato de Agente";
			break;
		case '7':
			$nombre_tipo_contrato = "Contrato Interno";
			break;
	}

	if (env('SEND_EMAIL') == 'test') {
		$sub_titulo_email = "TEST SISTEMAS: ";
	}

	$titulo_email = $sub_titulo_email . "Gestion - Sistema Contratos - Solicitud Cancelada: " . $titulo_cancelacion . " del " . $nombre_tipo_contrato . " Código - ";

	$request = [
		"subject" => $titulo_email . $sigla_correlativo . $codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try {
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;
		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if (isset($request["cc"])) {
			foreach ($request["cc"] as $cc) {
				$mail->addAddress($cc);
			}
		}

		if (isset($request["bcc"])) {
			foreach ($request["bcc"] as $bcc) {
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->send();

		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		echo json_encode($result);
		exit();
	} catch (Exception $e) {
		$result["http_code"] = 400;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["error"] = $mail->ErrorInfo;
		echo json_encode($result);
		exit();
	}
}

function sec_contrato_solicitud_enviar_error($error)
{
	// $result = array();
	$result["http_code"] = 500;
	$result["status"] = "error";
	$result["mensaje"] = $error;
	echo json_encode($result);
	die;
}

function sec_contrato_solicitud_is_valid_email($str)
{
	return (false !== filter_var($str, FILTER_VALIDATE_EMAIL));
}

function sec_contrato_solicitud_num_dias_habiles($fecha_cambio_estado_solicitud)
{
	if (!sec_contrato_solicitud_validar_formato_de_fecha($fecha_cambio_estado_solicitud)) {
		return 'Formato de fecha no es válido';
	}

	$fecha_del_cambio    = DateTime::createFromFormat('Y-m-d', date('Y-m-d', strtotime($fecha_cambio_estado_solicitud)));
	$fecha_actual_string = date("Y-m-d");
	$fecha_actual        = DateTime::createFromFormat('Y-m-d', $fecha_actual_string);
	$intervalo           = $fecha_actual->diff($fecha_del_cambio);
	$intervalo_en_dias   = $intervalo->days;

	if (!($intervalo_en_dias >= 0)) {
		return 'La fecha de cambio no es válido';
	}

	if (!($intervalo_en_dias < 90)) {
		return 'Más de 60';
	}

	$dias_habiles = 0;
	$fecha_del_cambio->modify('+1 day');

	while ($fecha_del_cambio <= $fecha_actual) {

		$num_dia_semana = $fecha_del_cambio->format('N');

		if ($num_dia_semana >= 1 && $num_dia_semana <= 5) {
			$dias_habiles++;
		}

		$fecha_del_cambio->modify('+1 day');
	}

	return $dias_habiles;
}

function sec_contrato_solicitud_validar_formato_de_fecha($date, $format = 'Y-m-d')
{
	$d = DateTime::createFromFormat($format, $date);
	return $d && $d->format($format) == $date;
}

function sec_contrato_solicitud_num_dias_habiles_formato($num_dias_habiles)
{
	$texto = ((int) $num_dias_habiles === 1) ? 'día hábil' : 'días hábiles';
	return $num_dias_habiles . ' ' . $texto;
}
if ($_POST['action'] == "listar_contrato_locacionservicio") {

	// datos de sesion
	$menu_consultar = $_POST['menu_consultar'];
	$area_id = $_POST['area_id'];
	$cargo_id = $_POST['cargo_id'];
	$menu_id = $_POST['menu_id'];
	$sec_id = $_POST['sec_id'];

	$empresa = $_POST['empresa'];
	$area_solicitante = $_POST['area'];
	$ruc = $_POST['ruc'];
	$razon_social = $_POST['razon_social'];
	$moneda = $_POST['moneda'];
	$fecha_inicio = trim($_POST['fecha_inicio']);
	$fecha_fin = trim($_POST['fecha_fin']);
	$nombre_tienda = trim($_POST['nombre_tienda']);
	$estado_sol_v2 = trim($_POST['estado_sol_v2']);

	$estado_sol = '';
	if (isset($_POST["estado_sol"])) {
		$estado_sol = $_POST["estado_sol"];
	}

	$id_departamento = $_POST['id_departamento'];
	$id_provincia = $_POST['id_provincia'];
	$id_distrito = $_POST['id_distrito'];


	$where_empresa = "";
	$where_area_solicitante = "";
	$where_ruc = "";
	$where_razon_social = "";
	$where_moneda = "";
	$where_fecha = '';
	$where_ubigeo = '';
	$where_nombre_tienda = '';
	$where_estado_sol = '';
	$where_estado_sol_v2 = '';
	$where_estado_cancelado = "";

	if (!empty($empresa)) {
		$where_empresa = " AND c.empresa_suscribe_id = '" . $empresa . "' ";
	}

	if (!empty($area_solicitante)) {
		$where_area_solicitante = " AND a.id = '" . $area_solicitante . "' ";
	}

	if (!empty($ruc)) {
		$where_ruc = " AND c.ruc LIKE '%" . $ruc . "%' ";
	}

	if (!empty($razon_social)) {
		$where_razon_social = " AND c.razon_social  LIKE '%" . $razon_social . "%' ";
	}

	if (!empty($moneda)) {
		$where_moneda = " AND ce.tipo_moneda_id = '" . $moneda . "'";
	}

	if (!empty($fecha_inicio) && !empty($fecha_fin)) {
		$where_fecha = " AND c.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
	} elseif (!empty($fecha_inicio)) {
		$where_fecha = " AND c.created_at >= '$fecha_inicio 00:00:00'";
	} elseif (!empty($fecha_fin)) {
		$where_fecha = " AND c.created_at <= '$fecha_fin 23:59:59'";
	}

	if (!empty($id_departamento)) {
		$where_ubigeo = " AND i.ubigeo_id LIKE '%" . $id_departamento . $id_provincia . $id_distrito . "%'";
	}

	if (!empty($nombre_tienda)) {
		$where_nombre_tienda = " AND c.nombre_tienda LIKE '%" . $nombre_tienda . "%' ";
	}

	if (!empty($estado_sol)) {
		if ((int) $estado_sol === 99) {
			$where_estado_sol_v2 = " AND c.cancelado_id = 1 ";
		} elseif ((int) $estado_sol === 1) {
			$where_estado_sol = " AND ( c.estado_solicitud = 1 OR c.estado_solicitud IS NULL ) ";
		} else {
			$where_estado_sol = " AND c.estado_solicitud = '" . $estado_sol . "' ";
		}
	}

	if ($login["area_id"] == 33) {
		$where_estado_cancelado = " AND ( c.cancelado_id != 1 OR c.cancelado_id IS NULL )";
	}

	if (!empty($estado_sol_v2)) {
		if ((int) $estado_sol_v2 === 1) {
			$where_estado_sol_v2 = " AND ( c.cancelado_id != 1 || c.cancelado_id IS NULL )";
		} elseif ((int) $estado_sol_v2 === 2) {
			$where_estado_sol_v2 = " AND c.cancelado_id = 1 ";
		}
	}


	$total_query = $mysqli->query("
		SELECT COUNT(c.contrato_id) as num_rows 
        FROM 
			cont_contrato c
			LEFT JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id AND ce.status = 1 
			LEFT JOIN cont_inmueble i ON c.contrato_id = i.contrato_id AND ce.contrato_detalle_id = i.contrato_detalle_id
			INNER JOIN cont_etapa e ON c.etapa_id = e.etapa_id
			LEFT JOIN tbl_areas a ON e.area_id = a.id
			LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			LEFT JOIN cont_estado_solicitud es ON es.id = c.estado_solicitud
		WHERE 
			c.tipo_contrato_id = 13
			AND c.status = 1 
			AND e.etapa_id <> 5
           " . $where_empresa . "
			" . $where_area_solicitante . "
			" . $where_ruc . "
			" . $where_razon_social . "
			" . $where_moneda . "
			" . $where_fecha . "
			" . $where_ubigeo . "
			" . $where_nombre_tienda . "
			" . $where_estado_sol . "
			" . $where_estado_sol_v2 . "
			" . $where_estado_cancelado . "
		");

	$resultNum = $total_query->fetch_assoc();
	$num_rows = $resultNum['num_rows'];
	$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
	$per_page = 10;
	$numLinks = 10;
	$total_paginate = ceil($num_rows / $per_page);
	$total_paginate = $total_paginate == 0 ? 1 : $total_paginate;
	$page = $page > $total_paginate ? $total_paginate : $page;
	$offset = ($page - 1) * $per_page;
	$paginate = new Pagination('sec_contratos_solicitud_cambiar_de_pagina', $num_rows, $per_page, $numLinks, $page);

?>

	<?php
	$query = "
		SELECT 
			c.contrato_id, 
			c.nombre_tienda, 
			c.cc_id,
			i.ubicacion, 
			i.num_partida_registral, 
			c.created_at AS fecha_solicitud, 
			c.fecha_suscripcion_contrato as fecha_suscripcion,
			e.etapa_id, 
			e.nombre AS etapa, 
			e.descripcion AS etapa_descripcion, 
			e.situacion AS etapa_situacion, 
			a.nombre AS area_encargada, 
			c.verificar_giro, 
			co.sigla AS sigla_correlativo, 
			c.codigo_correlativo,
			c.estado_solicitud,
			es.nombre AS nombre_estado_solicitud,
			c.cancelado_id,
			c.fecha_cambio_estado_solicitud,
			c.dias_habiles,
			raz.nombre as locador,
			perso.nombre as locatario,
			(CASE WHEN (c.fecha_aprobacion IS NULL AND c.estado_aprobacion=0) THEN 'Pendiente' ELSE '' END) as aprob, 	
			(CASE WHEN (c.fecha_aprobacion IS NOT NULL AND c.estado_aprobacion=1) THEN 'Aprobado' ELSE '' END) as aprobS, 
			(CASE WHEN (c.fecha_aprobacion IS NOT NULL AND c.estado_aprobacion=0) THEN 'Denegado' ELSE '' END) as aprobN, 
			CONCAT(IFNULL(pud.nombre, ''),' ',IFNULL(pud.apellido_paterno, ''),' ',IFNULL(pud.apellido_materno, '')) AS nombre_del_director_a_aprobar,
			CONCAT(IFNULL(puap.nombre, ''),' ',IFNULL(puap.apellido_paterno, ''),' ',IFNULL(puap.apellido_materno, '')) AS nombre_del_aprobador,
			c.aprobador_id,
			c.aprobado_por,
			c.fecha_aprobacion,
			CONCAT(IFNULL(pa.nombre, ''),' ',IFNULL(pa.apellido_paterno, ''),' ',IFNULL(pa.apellido_materno, '')) AS abogado,
			p.area_id as usuario_s
		FROM 
			cont_contrato c
			LEFT JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id AND ce.status = 1
			LEFT JOIN cont_inmueble i ON c.contrato_id = i.contrato_id AND ce.contrato_detalle_id = i.contrato_detalle_id
			INNER JOIN cont_etapa e ON c.etapa_id = e.etapa_id
			LEFT JOIN tbl_areas a ON e.area_id = a.id
			LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			LEFT JOIN cont_estado_solicitud es ON es.id = c.estado_solicitud
			LEFT JOIN tbl_usuarios ud ON c.aprobador_id = ud.id
			LEFT JOIN tbl_personal_apt pud ON ud.personal_id = pud.id
			LEFT JOIN tbl_usuarios uap ON c.aprobado_por = uap.id
			LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id
			LEFT JOIN tbl_usuarios ua ON c.abogado_id = ua.id
			LEFT JOIN tbl_personal_apt pa ON ua.personal_id = pa.id
			INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
			INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
			LEFT JOIN tbl_razon_social raz on raz.id = c.empresa_suscribe_id
			LEFT JOIN cont_propietario prop on prop.contrato_id = c.contrato_id
			JOIN cont_persona perso on perso.id = prop.persona_id
		WHERE 
			c.tipo_contrato_id = 13
			AND c.status = 1 
			AND e.etapa_id <> 5 
			AND (c.etapa_id = 1 AND c.estado_aprobacion = 0)
			" . $where_empresa . "
			" . $where_area_solicitante . "
			" . $where_ruc . "
			" . $where_razon_social . "
			" . $where_moneda . "
			" . $where_fecha . "
			" . $where_ubigeo . "
			" . $where_nombre_tienda . "
			" . $where_estado_sol . "
			" . $where_estado_sol_v2 . "
			" . $where_estado_cancelado . "
			ORDER BY c.created_at DESC

		";
	$sel_query = $mysqli->query($query);
	?>

	<div class="w-100 text-center">
		<h5 style="font-weight: bold; margin: 0px;">SOLICITUDES DE CONTRATO DE LOCACIÓN DE SERVICIO</h5>
	</div>
	<br>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed table-bordered tabla_contratos_para_filtro">
			<thead>
				<tr>
					<th class="text-center">Código</th>

					<th class="text-center">F. Solicitud</th>
					<th class="text-center">F. Suscripción</th>


					<th class="text-center">Estado</th>

					<th class="text-center">Estado Aprobación</th>
					<th class="text-center">Locatario</th>
					<th class="text-center">Locador</th>
					<th class="text-center">Ver Detalle</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th class="text-center">Código</th>

					<th class="text-center">F. Solicitud</th>
					<th class="text-center">F. Suscripción</th>


					<th class="text-center">Estado</th>

					<th class="text-center">Estado Aprobación</th>
					<th class="text-center">Locatario</th>
					<th class="text-center">Locador</th>
					<th class="text-center">Ver Detalle</th>
				</tr>
			</tfoot>
			<tbody>
				<?php
				while ($sel = $sel_query->fetch_assoc()) {
					$giro = "";
					$verificar_giro = trim($sel["verificar_giro"]);
					if ($verificar_giro == "0") {
						$giro = "";
					} else if ($verificar_giro == "1") {
						$giro = "Si";
					} else {
						$giro = "No";
					}
				?>
					<tr>
						<td class="text-center"><?php echo $sel["sigla_correlativo"];
												echo $sel["codigo_correlativo"]; ?></td>

						<td><?php echo $sel["fecha_solicitud"]; ?></td>
						<td><?php echo $sel["fecha_suscripcion"]; ?></td>


						<td class="text-center">
							<?php
							if ($sel["cancelado_id"] == 1) {
								echo '<span class="badge bg-danger text-white">Cancelado</span>';
								// } elseif ($area_id == '33') {
							} else {
								$bg_estado_solicitud = 'bg-info';
								$name = "En proceso";
								switch ($sel["estado_solicitud"]) {
									case 1:
										$bg_estado_solicitud = 'bg-default';
										$name = $sel["nombre_estado_solicitud"];

										break;
									case 2:
										$name = $sel["nombre_estado_solicitud"];
										$bg_estado_solicitud = 'bg-info';
										break;
									case 3:
										$name = $sel["nombre_estado_solicitud"];
										$bg_estado_solicitud = 'bg-warning';
										break;
									case 4:
										$name = $sel["nombre_estado_solicitud"];
										$bg_estado_solicitud = 'bg-secondary';
										break;
								}
							?>
								<span class="badge <?= $bg_estado_solicitud ?> text-white"><?php echo $name; ?></span>
							<?php
							} ?>
						</td>

						<td class="text-center">
							<?php
							if (($sel["usuario_s"] ==  $login["area_id"]) || $usuario_id_login == $sel["aprobador_id"] || $usuario_id_login == $sel["aprobado_por"] || $area_id == '6' || ($area_id == '33')) {
								echo $sel["aprob"];
								echo $sel["aprobS"];
								echo $sel["aprobN"];
							}
							?>
						</td>

						<td><?php echo $sel["locador"]; ?></td>
						<td><?php echo $sel["locatario"]; ?></td>

						<td class="text-center">
							<a class="btn btn-rounded btn-primary btn-sm"
								href="./?sec_id=<?php echo $sec_id; ?>&amp;sub_sec_id=detalle_solicitud_locacion_servicio&id=<?php echo $sel["contrato_id"]; ?>"
								title="Ver detalle">
								<i class="fa fa-eye"></i>
							</a>
						</td>
					</tr>
				<?php
				}
				?>
			</tbody>
		</table>
	</div>


<?php
}
if ($_POST['action'] == "listar_contrato_mutuodinero") {

	// datos de sesion
	$menu_consultar = $_POST['menu_consultar'];
	$area_id = $_POST['area_id'];
	$cargo_id = $_POST['cargo_id'];
	$menu_id = $_POST['menu_id'];
	$sec_id = $_POST['sec_id'];

	$empresa = $_POST['empresa'];
	$area_solicitante = $_POST['area'];
	$ruc = $_POST['ruc'];
	$razon_social = $_POST['razon_social'];
	$moneda = $_POST['moneda'];
	$fecha_inicio = trim($_POST['fecha_inicio']);
	$fecha_fin = trim($_POST['fecha_fin']);
	$nombre_tienda = trim($_POST['nombre_tienda']);
	$estado_sol_v2 = trim($_POST['estado_sol_v2']);

	$estado_sol = '';
	if (isset($_POST["estado_sol"])) {
		$estado_sol = $_POST["estado_sol"];
	}

	$id_departamento = $_POST['id_departamento'];
	$id_provincia = $_POST['id_provincia'];
	$id_distrito = $_POST['id_distrito'];


	$where_empresa = "";
	$where_area_solicitante = "";
	$where_ruc = "";
	$where_razon_social = "";
	$where_moneda = "";
	$where_fecha = '';
	$where_ubigeo = '';
	$where_nombre_tienda = '';
	$where_estado_sol = '';
	$where_estado_sol_v2 = '';
	$where_estado_cancelado = "";

	if (!empty($empresa)) {
		$where_empresa = " AND c.empresa_suscribe_id = '" . $empresa . "' ";
	}

	if (!empty($area_solicitante)) {
		$where_area_solicitante = " AND a.id = '" . $area_solicitante . "' ";
	}

	if (!empty($ruc)) {
		$where_ruc = " AND c.ruc LIKE '%" . $ruc . "%' ";
	}

	if (!empty($razon_social)) {
		$where_razon_social = " AND c.razon_social  LIKE '%" . $razon_social . "%' ";
	}

	if (!empty($moneda)) {
		$where_moneda = " AND ce.tipo_moneda_id = '" . $moneda . "'";
	}

	if (!empty($fecha_inicio) && !empty($fecha_fin)) {
		$where_fecha = " AND c.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
	} elseif (!empty($fecha_inicio)) {
		$where_fecha = " AND c.created_at >= '$fecha_inicio 00:00:00'";
	} elseif (!empty($fecha_fin)) {
		$where_fecha = " AND c.created_at <= '$fecha_fin 23:59:59'";
	}

	if (!empty($id_departamento)) {
		$where_ubigeo = " AND i.ubigeo_id LIKE '%" . $id_departamento . $id_provincia . $id_distrito . "%'";
	}

	if (!empty($nombre_tienda)) {
		$where_nombre_tienda = " AND c.nombre_tienda LIKE '%" . $nombre_tienda . "%' ";
	}

	if (!empty($estado_sol)) {
		if ((int) $estado_sol === 99) {
			$where_estado_sol_v2 = " AND c.cancelado_id = 1 ";
		} elseif ((int) $estado_sol === 1) {
			$where_estado_sol = " AND ( c.estado_solicitud = 1 OR c.estado_solicitud IS NULL ) ";
		} else {
			$where_estado_sol = " AND c.estado_solicitud = '" . $estado_sol . "' ";
		}
	}

	if ($login["area_id"] == 33) {
		$where_estado_cancelado = " AND ( c.cancelado_id != 1 OR c.cancelado_id IS NULL )";
	}

	if (!empty($estado_sol_v2)) {
		if ((int) $estado_sol_v2 === 1) {
			$where_estado_sol_v2 = " AND ( c.cancelado_id != 1 || c.cancelado_id IS NULL )";
		} elseif ((int) $estado_sol_v2 === 2) {
			$where_estado_sol_v2 = " AND c.cancelado_id = 1 ";
		}
	}


	$total_query = $mysqli->query("
		SELECT COUNT(c.contrato_id) as num_rows 
        FROM 
			cont_contrato c
			LEFT JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id AND ce.status = 1 
			LEFT JOIN cont_inmueble i ON c.contrato_id = i.contrato_id AND ce.contrato_detalle_id = i.contrato_detalle_id
			INNER JOIN cont_etapa e ON c.etapa_id = e.etapa_id
			LEFT JOIN tbl_areas a ON e.area_id = a.id
			LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			LEFT JOIN cont_estado_solicitud es ON es.id = c.estado_solicitud
		WHERE 
			c.tipo_contrato_id = 13
			AND c.status = 1 
			AND e.etapa_id <> 5
           " . $where_empresa . "
			" . $where_area_solicitante . "
			" . $where_ruc . "
			" . $where_razon_social . "
			" . $where_moneda . "
			" . $where_fecha . "
			" . $where_ubigeo . "
			" . $where_nombre_tienda . "
			" . $where_estado_sol . "
			" . $where_estado_sol_v2 . "
			" . $where_estado_cancelado . "
		");

	$resultNum = $total_query->fetch_assoc();
	$num_rows = $resultNum['num_rows'];
	$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
	$per_page = 10;
	$numLinks = 10;
	$total_paginate = ceil($num_rows / $per_page);
	$total_paginate = $total_paginate == 0 ? 1 : $total_paginate;
	$page = $page > $total_paginate ? $total_paginate : $page;
	$offset = ($page - 1) * $per_page;
	$paginate = new Pagination('sec_contratos_solicitud_cambiar_de_pagina', $num_rows, $per_page, $numLinks, $page);

?>

	<?php
	$query = "
		SELECT 
			c.contrato_id, 
			c.nombre_tienda, 
			c.cc_id,
			i.ubicacion, 
			i.num_partida_registral, 
			c.created_at AS fecha_solicitud, 
			c.fecha_suscripcion_contrato as fecha_suscripcion,
			e.etapa_id, 
			e.nombre AS etapa, 
			e.descripcion AS etapa_descripcion, 
			e.situacion AS etapa_situacion, 
			a.nombre AS area_encargada, 
			c.verificar_giro, 
			co.sigla AS sigla_correlativo, 
			c.codigo_correlativo,
			c.estado_solicitud,
			es.nombre AS nombre_estado_solicitud,
			c.cancelado_id,
			c.fecha_cambio_estado_solicitud,
			c.dias_habiles,
			(CASE WHEN (c.fecha_aprobacion IS NULL AND c.estado_aprobacion=0) THEN 'Pendiente' ELSE '' END) as aprob, 	
			(CASE WHEN (c.fecha_aprobacion IS NOT NULL AND c.estado_aprobacion=1) THEN 'Aprobado' ELSE '' END) as aprobS, 
			(CASE WHEN (c.fecha_aprobacion IS NOT NULL AND c.estado_aprobacion=0) THEN 'Denegado' ELSE '' END) as aprobN, 
			CONCAT(IFNULL(pud.nombre, ''),' ',IFNULL(pud.apellido_paterno, ''),' ',IFNULL(pud.apellido_materno, '')) AS nombre_del_director_a_aprobar,
			CONCAT(IFNULL(puap.nombre, ''),' ',IFNULL(puap.apellido_paterno, ''),' ',IFNULL(puap.apellido_materno, '')) AS nombre_del_aprobador,
			c.aprobador_id,
			c.aprobado_por,
			c.fecha_aprobacion,
			CONCAT(IFNULL(pa.nombre, ''),' ',IFNULL(pa.apellido_paterno, ''),' ',IFNULL(pa.apellido_materno, '')) AS abogado,
			p.area_id as usuario_s,
			raz.nombre as mutuante,
			pers.nombre as mutuatario
		FROM 
			cont_contrato c
			LEFT JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id AND ce.status = 1
			LEFT JOIN cont_inmueble i ON c.contrato_id = i.contrato_id AND ce.contrato_detalle_id = i.contrato_detalle_id
			INNER JOIN cont_etapa e ON c.etapa_id = e.etapa_id
			LEFT JOIN tbl_areas a ON e.area_id = a.id
			LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			LEFT JOIN cont_estado_solicitud es ON es.id = c.estado_solicitud
			LEFT JOIN tbl_usuarios ud ON c.aprobador_id = ud.id
			LEFT JOIN tbl_personal_apt pud ON ud.personal_id = pud.id
			LEFT JOIN tbl_usuarios uap ON c.aprobado_por = uap.id
			LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id
			LEFT JOIN tbl_usuarios ua ON c.abogado_id = ua.id
			LEFT JOIN tbl_personal_apt pa ON ua.personal_id = pa.id
			INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
			INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
			LEFT JOIN tbl_razon_social raz ON raz.id = c.empresa_suscribe_id
			LEFT JOIN cont_propietario prop ON prop.contrato_id = c.contrato_id
			LEFT JOIN cont_persona pers ON pers.id = prop.persona_id
		WHERE 
			c.tipo_contrato_id = 15
			AND c.status = 1 
			AND (e.etapa_id <> 5 AND (c.etapa_id = 1 AND c.estado_aprobacion = 0) ) 
			" . $where_empresa . "
			" . $where_area_solicitante . "
			" . $where_ruc . "
			" . $where_razon_social . "
			" . $where_moneda . "
			" . $where_fecha . "
			" . $where_ubigeo . "
			" . $where_nombre_tienda . "
			" . $where_estado_sol . "
			" . $where_estado_sol_v2 . "
			" . $where_estado_cancelado . "
			ORDER BY c.created_at DESC

		";
	$sel_query = $mysqli->query($query);
	?>

	<div class="w-100 text-center">
		<h5 style="font-weight: bold; margin: 0px;">SOLICITUDES DE CONTRATO DE MUTUO DE DINERO</h5>
	</div>
	<br>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed table-bordered tabla_contratos_para_filtro">
			<thead>
				<tr>
					<th class="text-center">Código</th>

					<th class="text-center">F. Solicitud</th>
					<th class="text-center">F. Suscripción</th>


					<th class="text-center">Estado</th>

					<th class="text-center">Estado Aprobación</th>
					<th class="text-center">Mutuatario</th>
					<th class="text-center">Mutuante</th>
					<th class="text-center">Ver Detalle</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th class="text-center">Código</th>

					<th class="text-center">F. Solicitud</th>
					<th class="text-center">F. Suscripción</th>


					<th class="text-center">Estado</th>

					<th class="text-center">Estado Aprobación</th>
					<th class="text-center">Mutuatario</th>
					<th class="text-center">Mutuante</th>
					<th class="text-center">Ver Detalle</th>
				</tr>
			</tfoot>
			<tbody>
				<?php
				while ($sel = $sel_query->fetch_assoc()) {
					$giro = "";
					$verificar_giro = trim($sel["verificar_giro"]);
					if ($verificar_giro == "0") {
						$giro = "";
					} else if ($verificar_giro == "1") {
						$giro = "Si";
					} else {
						$giro = "No";
					}
				?>
					<tr>
						<td class="text-center"><?php echo $sel["sigla_correlativo"];
												echo $sel["codigo_correlativo"]; ?></td>

						<td><?php echo $sel["fecha_solicitud"]; ?></td>
						<td><?php echo $sel["fecha_suscripcion"]; ?></td>


						<td class="text-center">
							<?php
							if ($sel["cancelado_id"] == 1) {
								echo '<span class="badge bg-danger text-white">Cancelado</span>';
								// } elseif ($area_id == '33') {
							} else {
								$bg_estado_solicitud = 'bg-info';
								$name = "En proceso";
								switch ($sel["estado_solicitud"]) {
									case 1:
										$bg_estado_solicitud = 'bg-default';
										$name = $sel["nombre_estado_solicitud"];

										break;
									case 2:
										$name = $sel["nombre_estado_solicitud"];
										$bg_estado_solicitud = 'bg-info';
										break;
									case 3:
										$name = $sel["nombre_estado_solicitud"];
										$bg_estado_solicitud = 'bg-warning';
										break;
									case 4:
										$name = $sel["nombre_estado_solicitud"];
										$bg_estado_solicitud = 'bg-secondary';
										break;
								}
							?>
								<span class="badge <?= $bg_estado_solicitud ?> text-white"><?php echo $name; ?></span>
							<?php
							} ?>
						</td>

						<td class="text-center">
							<?php
							if (($sel["usuario_s"] ==  $login["area_id"]) || $usuario_id_login == $sel["aprobador_id"] || $usuario_id_login == $sel["aprobado_por"] || $area_id == '6' || ($area_id == '33')) {
								echo $sel["aprob"];
								echo $sel["aprobS"];
								echo $sel["aprobN"];
							}
							?>
						</td>
						<td><?php echo $sel["mutuatario"]; ?></td>
						<td><?php echo $sel["mutuante"]; ?></td>

						<td class="text-center">
							<a class="btn btn-rounded btn-primary btn-sm"
								href="./?sec_id=<?php echo $sec_id; ?>&amp;sub_sec_id=detalle_solicitud_mutuo_dinero&id=<?php echo $sel["contrato_id"]; ?>"
								title="Ver detalle">
								<i class="fa fa-eye"></i>
							</a>
						</td>
					</tr>
				<?php
				}
				?>
			</tbody>
		</table>
	</div>


<?php
}
if ($_POST['action'] == "listar_contrato_mandato") {

	// datos de sesion
	$menu_consultar = $_POST['menu_consultar'];
	$area_id = $_POST['area_id'];
	$cargo_id = $_POST['cargo_id'];
	$menu_id = $_POST['menu_id'];
	$sec_id = $_POST['sec_id'];

	$empresa = $_POST['empresa'];
	$area_solicitante = $_POST['area'];
	$ruc = $_POST['ruc'];
	$razon_social = $_POST['razon_social'];
	$moneda = $_POST['moneda'];
	$fecha_inicio = trim($_POST['fecha_inicio']);
	$fecha_fin = trim($_POST['fecha_fin']);
	$nombre_tienda = trim($_POST['nombre_tienda']);
	$estado_sol_v2 = trim($_POST['estado_sol_v2']);

	$estado_sol = '';
	if (isset($_POST["estado_sol"])) {
		$estado_sol = $_POST["estado_sol"];
	}

	$id_departamento = $_POST['id_departamento'];
	$id_provincia = $_POST['id_provincia'];
	$id_distrito = $_POST['id_distrito'];


	$where_empresa = "";
	$where_area_solicitante = "";
	$where_ruc = "";
	$where_razon_social = "";
	$where_moneda = "";
	$where_fecha = '';
	$where_ubigeo = '';
	$where_nombre_tienda = '';
	$where_estado_sol = '';
	$where_estado_sol_v2 = '';
	$where_estado_cancelado = "";

	if (!empty($empresa)) {
		$where_empresa = " AND c.empresa_suscribe_id = '" . $empresa . "' ";
	}

	if (!empty($area_solicitante)) {
		$where_area_solicitante = " AND a.id = '" . $area_solicitante . "' ";
	}

	if (!empty($ruc)) {
		$where_ruc = " AND c.ruc LIKE '%" . $ruc . "%' ";
	}

	if (!empty($razon_social)) {
		$where_razon_social = " AND c.razon_social  LIKE '%" . $razon_social . "%' ";
	}

	if (!empty($moneda)) {
		$where_moneda = " AND ce.tipo_moneda_id = '" . $moneda . "'";
	}

	if (!empty($fecha_inicio) && !empty($fecha_fin)) {
		$where_fecha = " AND c.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
	} elseif (!empty($fecha_inicio)) {
		$where_fecha = " AND c.created_at >= '$fecha_inicio 00:00:00'";
	} elseif (!empty($fecha_fin)) {
		$where_fecha = " AND c.created_at <= '$fecha_fin 23:59:59'";
	}

	if (!empty($id_departamento)) {
		$where_ubigeo = " AND i.ubigeo_id LIKE '%" . $id_departamento . $id_provincia . $id_distrito . "%'";
	}

	if (!empty($nombre_tienda)) {
		$where_nombre_tienda = " AND c.nombre_tienda LIKE '%" . $nombre_tienda . "%' ";
	}

	if (!empty($estado_sol)) {
		if ((int) $estado_sol === 99) {
			$where_estado_sol_v2 = " AND c.cancelado_id = 1 ";
		} elseif ((int) $estado_sol === 1) {
			$where_estado_sol = " AND ( c.estado_solicitud = 1 OR c.estado_solicitud IS NULL ) ";
		} else {
			$where_estado_sol = " AND c.estado_solicitud = '" . $estado_sol . "' ";
		}
	}

	if ($login["area_id"] == 33) {
		$where_estado_cancelado = " AND ( c.cancelado_id != 1 OR c.cancelado_id IS NULL )";
	}

	if (!empty($estado_sol_v2)) {
		if ((int) $estado_sol_v2 === 1) {
			$where_estado_sol_v2 = " AND ( c.cancelado_id != 1 || c.cancelado_id IS NULL )";
		} elseif ((int) $estado_sol_v2 === 2) {
			$where_estado_sol_v2 = " AND c.cancelado_id = 1 ";
		}
	}


	$total_query = $mysqli->query("
		SELECT COUNT(c.contrato_id) as num_rows 
        FROM 
			cont_contrato c
			LEFT JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id AND ce.status = 1 
			LEFT JOIN cont_inmueble i ON c.contrato_id = i.contrato_id AND ce.contrato_detalle_id = i.contrato_detalle_id
			INNER JOIN cont_etapa e ON c.etapa_id = e.etapa_id
			LEFT JOIN tbl_areas a ON e.area_id = a.id
			LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			LEFT JOIN cont_estado_solicitud es ON es.id = c.estado_solicitud
		WHERE 
			c.tipo_contrato_id = 13
			AND c.status = 1 
			AND (e.etapa_id <> 5 AND (c.etapa_id = 1 AND c.estado_aprobacion = 0) ) 
           " . $where_empresa . "
			" . $where_area_solicitante . "
			" . $where_ruc . "
			" . $where_razon_social . "
			" . $where_moneda . "
			" . $where_fecha . "
			" . $where_ubigeo . "
			" . $where_nombre_tienda . "
			" . $where_estado_sol . "
			" . $where_estado_sol_v2 . "
			" . $where_estado_cancelado . "
		");

	$resultNum = $total_query->fetch_assoc();
	$num_rows = $resultNum['num_rows'];
	$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
	$per_page = 10;
	$numLinks = 10;
	$total_paginate = ceil($num_rows / $per_page);
	$total_paginate = $total_paginate == 0 ? 1 : $total_paginate;
	$page = $page > $total_paginate ? $total_paginate : $page;
	$offset = ($page - 1) * $per_page;
	$paginate = new Pagination('sec_contratos_solicitud_cambiar_de_pagina', $num_rows, $per_page, $numLinks, $page);

?>

	<?php
	$query = "
		SELECT 
			c.contrato_id,
			co.sigla AS sigla_correlativo, 
			c.codigo_correlativo,
			c.nombre_tienda, 
			c.cc_id,
			i.ubicacion, 
			i.num_partida_registral, 
			c.created_at AS fecha_solicitud, 
			c.fecha_suscripcion_contrato as fecha_suscripcion,
			e.etapa_id, 
			e.nombre AS etapa, 
			e.descripcion AS etapa_descripcion, 
			e.situacion AS etapa_situacion, 
			a.nombre AS area_encargada, 
			c.verificar_giro, 
			
			c.estado_solicitud,
			es.nombre AS nombre_estado_solicitud,
			c.cancelado_id,
			c.fecha_cambio_estado_solicitud,
			c.dias_habiles,
			(CASE WHEN (c.fecha_aprobacion IS NULL AND c.estado_aprobacion=0) THEN 'Pendiente' ELSE '' END) as aprob, 	
			(CASE WHEN (c.fecha_aprobacion IS NOT NULL AND c.estado_aprobacion=1) THEN 'Aprobado' ELSE '' END) as aprobS, 
			(CASE WHEN (c.fecha_aprobacion IS NOT NULL AND c.estado_aprobacion=0) THEN 'Denegado' ELSE '' END) as aprobN, 
			CONCAT(IFNULL(pud.nombre, ''),' ',IFNULL(pud.apellido_paterno, ''),' ',IFNULL(pud.apellido_materno, '')) AS nombre_del_director_a_aprobar,
			CONCAT(IFNULL(puap.nombre, ''),' ',IFNULL(puap.apellido_paterno, ''),' ',IFNULL(puap.apellido_materno, '')) AS nombre_del_aprobador,
			c.aprobador_id,
			c.aprobado_por,
			c.fecha_aprobacion,
			CONCAT(IFNULL(pa.nombre, ''),' ',IFNULL(pa.apellido_paterno, ''),' ',IFNULL(pa.apellido_materno, '')) AS abogado,
			p.area_id as usuario_s,
			pers.nombre as mandante,
			raz.nombre as mandatario
		FROM 
			cont_contrato c
			LEFT JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id AND ce.status = 1
			LEFT JOIN cont_inmueble i ON c.contrato_id = i.contrato_id AND ce.contrato_detalle_id = i.contrato_detalle_id
			INNER JOIN cont_etapa e ON c.etapa_id = e.etapa_id
			LEFT JOIN tbl_areas a ON e.area_id = a.id
			LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			LEFT JOIN cont_estado_solicitud es ON es.id = c.estado_solicitud
			LEFT JOIN tbl_usuarios ud ON c.aprobador_id = ud.id
			LEFT JOIN tbl_personal_apt pud ON ud.personal_id = pud.id
			LEFT JOIN tbl_usuarios uap ON c.aprobado_por = uap.id
			LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id
			LEFT JOIN tbl_usuarios ua ON c.abogado_id = ua.id
			LEFT JOIN tbl_personal_apt pa ON ua.personal_id = pa.id
			INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
			INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
			LEFT JOIN tbl_razon_social raz ON raz.id = c.empresa_suscribe_id
			LEFT JOIN cont_propietario prop ON prop.contrato_id = c.contrato_id
			LEFT JOIN cont_persona pers ON pers.id = prop.persona_id
		WHERE 
			c.tipo_contrato_id = 14
			AND c.status = 1 
			AND (e.etapa_id <> 5 AND (c.etapa_id = 1 AND c.estado_aprobacion = 0) )  
			" . $where_empresa . "
			" . $where_area_solicitante . "
			" . $where_ruc . "
			" . $where_razon_social . "
			" . $where_moneda . "
			" . $where_fecha . "
			" . $where_ubigeo . "
			" . $where_nombre_tienda . "
			" . $where_estado_sol . "
			" . $where_estado_sol_v2 . "
			" . $where_estado_cancelado . "
			ORDER BY c.created_at DESC

		";
	$sel_query = $mysqli->query($query);
	?>

	<div class="w-100 text-center">
		<h5 style="font-weight: bold; margin: 0px;">SOLICITUDES DE CONTRATO DE MANDATO</h5>
	</div>
	<br>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed table-bordered tabla_contratos_para_filtro">
			<thead>
				<tr>
					<th class="text-center">Código</th>

					<th class="text-center">F. Solicitud</th>
					<th class="text-center">F. Suscripción</th>


					<th class="text-center">Estado</th>

					<th class="text-center">Estado Aprobación</th>
					<th class="text-center">Mandante</th>
					<th class="text-center">Mandatario</th>
					<th class="text-center">Ver Detalle</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th class="text-center">Código</th>

					<th class="text-center">F. Solicitud</th>
					<th class="text-center">F. Suscripción</th>


					<th class="text-center">Estado</th>

					<th class="text-center">Estado Aprobación</th>
					<th class="text-center">Mandante</th>
					<th class="text-center">Mandatario</th>
					<th class="text-center">Ver Detalle</th>
				</tr>
			</tfoot>
			<tbody>
				<?php
				while ($sel = $sel_query->fetch_assoc()) {
					$giro = "";
					$verificar_giro = trim($sel["verificar_giro"]);
					if ($verificar_giro == "0") {
						$giro = "";
					} else if ($verificar_giro == "1") {
						$giro = "Si";
					} else {
						$giro = "No";
					}
				?>
					<tr>
						<td class="text-center"><?php echo $sel["sigla_correlativo"];
												echo $sel["codigo_correlativo"]; ?></td>

						<td><?php echo $sel["fecha_solicitud"]; ?></td>
						<td><?php echo $sel["fecha_suscripcion"]; ?></td>


						<td class="text-center">
							<?php
							if ($sel["cancelado_id"] == 1) {
								echo '<span class="badge bg-danger text-white">Cancelado</span>';
								// } elseif ($area_id == '33') {
							} else {
								$bg_estado_solicitud = 'bg-info';
								$name = "En proceso";
								switch ($sel["estado_solicitud"]) {
									case 1:
										$bg_estado_solicitud = 'bg-default';
										$name = $sel["nombre_estado_solicitud"];

										break;
									case 2:
										$name = $sel["nombre_estado_solicitud"];
										$bg_estado_solicitud = 'bg-info';
										break;
									case 3:
										$name = $sel["nombre_estado_solicitud"];
										$bg_estado_solicitud = 'bg-warning';
										break;
									case 4:
										$name = $sel["nombre_estado_solicitud"];
										$bg_estado_solicitud = 'bg-secondary';
										break;
								}
							?>
								<span class="badge <?= $bg_estado_solicitud ?> text-white"><?php echo $name; ?></span>
							<?php
							} ?>
						</td>

						<td class="text-center">
							<?php
							if (($sel["usuario_s"] ==  $login["area_id"]) || $usuario_id_login == $sel["aprobador_id"] || $usuario_id_login == $sel["aprobado_por"] || $area_id == '6' || ($area_id == '33')) {
								echo $sel["aprob"];
								echo $sel["aprobS"];
								echo $sel["aprobN"];
							}
							?>
						</td>
						<td><?php echo $sel["mandante"]; ?></td>
						<td><?php echo $sel["mandatario"]; ?></td>

						<td class="text-center">
							<a class="btn btn-rounded btn-primary btn-sm"
								href="./?sec_id=<?php echo $sec_id; ?>&amp;sub_sec_id=detalle_solicitud_mandato&id=<?php echo $sel["contrato_id"]; ?>"
								title="Ver detalle">
								<i class="fa fa-eye"></i>
							</a>
						</td>
					</tr>
				<?php
				}
				?>
			</tbody>
		</table>
	</div>


<?php
}
?>
<?php
if ($_POST['action'] == "listar_adenda_de_locacion_de_servicio") {
	$menu_consultar = $_POST['menu_consultar'];
	$area_id = $_POST['area_id'];
	$cargo_id = $_POST['cargo_id'];
	$menu_id = $_POST['menu_id'];
	$empresa = $_POST['empresa'];
	$area_solicitante = $_POST['area'];
	$ruc = $_POST['ruc'];
	$razon_social = $_POST['razon_social'];
	$moneda = $_POST['moneda'];
	$nombre_tienda = trim($_POST['nombre_tienda']);
	$id_departamento = $_POST['id_departamento'];
	$id_provincia = $_POST['id_provincia'];
	$id_distrito = $_POST['id_distrito'];
	$fecha_inicio = trim($_POST['fecha_inicio']);
	$fecha_fin = trim($_POST['fecha_fin']);
	$usuario_permisos = json_decode($_POST['usuario_permisos'], true);
	$sec_id = $_POST['sec_id'];
	$estado_sol_v2 = trim($_POST['estado_sol_v2']);

	$estado_sol = '';
	if (isset($_POST["estado_sol"])) {
		$estado_sol = $_POST["estado_sol"];
	}

	$where_empresa = "";
	$where_area_solicitante = "";
	$where_ruc = "";
	$where_razon_social = "";
	$where_moneda = "";
	$where_ubigeo = '';
	$where_nombre_tienda = '';
	$query_fecha = '';
	$where_estado_sol = '';
	$where_estado_sol_v2 = '';
	$where_estado_cancelado = "";

	if (!empty($empresa)) {
		$where_empresa = " AND c.empresa_suscribe_id = '" . $empresa . "' ";
	}

	if (!empty($ruc)) {
		$where_ruc = " AND c.ruc LIKE '%" . $ruc . "%' ";
	}

	if (!empty($razon_social)) {
		$where_razon_social = " AND c.razon_social  LIKE '%" . $razon_social . "%' ";
	}

	if (!empty($moneda)) {
		$where_moneda = " AND ce.tipo_moneda_id = '" . $moneda . "'";
	}

	if (!empty($id_departamento)) {
		$where_ubigeo = " AND i.ubigeo_id LIKE '%" . $id_departamento . $id_provincia . $id_distrito . "%'";
	}

	if (!empty($nombre_tienda)) {
		$where_nombre_tienda = " AND c.nombre_tienda LIKE '%" . $nombre_tienda . "%' ";
	}

	if (!empty($fecha_inicio) && !empty($fecha_fin)) {
		$query_fecha = " AND a.created_at BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59'";
	} elseif (!empty($fecha_inicio)) {
		$query_fecha = " AND a.created_at >= '$fecha_inicio 00:00:00'";
	} elseif (!empty($fecha_fin)) {
		$query_fecha = " AND a.created_at <= '$fecha_fin 23:59:59'";
	}

	if (!empty($estado_sol)) {
		if ((int) $estado_sol === 99) {
			$where_estado_sol_v2 = " AND a.cancelado_id = 1 ";
		} elseif ((int) $estado_sol === 1) {
			$where_estado_sol = " AND ( a.estado_solicitud_id = 1 OR a.estado_solicitud_id IS NULL ) ";
		} else {
			$where_estado_sol = " AND a.estado_solicitud_id = '" . $estado_sol . "' ";
		}
	}

	if (!empty($estado_sol_v2)) {
		if ((int) $estado_sol_v2 === 1) {
			$where_estado_sol_v2 = " AND ( a.cancelado_id != 1 || a.cancelado_id IS NULL )";
		} elseif ((int) $estado_sol_v2 === 2) {
			$where_estado_sol_v2 = " AND a.cancelado_id = 1 ";
		}
	}

	if ($login["area_id"] == 33) {
		$where_estado_cancelado = " AND ( a.cancelado_id != 1 OR a.cancelado_id IS NULL )";
	}

	$total_query = $mysqli->query("
		SELECT COUNT(c.contrato_id) as num_rows 
		FROM 
			cont_adendas a
			LEFT JOIN cont_contrato c ON a.contrato_id = c.contrato_id
			LEFT JOIN tbl_usuarios u ON a.user_created_id = u.id
			LEFT JOIN tbl_personal_apt p ON u.personal_id = p.id
			LEFT JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id AND ce.status = 1
			LEFT JOIN cont_inmueble i ON c.contrato_id = i.contrato_id AND i.contrato_detalle_id = ce.contrato_detalle_id
			LEFT JOIN cont_etapa e ON c.etapa_id = e.etapa_id
			LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			LEFT JOIN cont_estado_solicitud es ON es.id = a.estado_solicitud_id
			LEFT JOIN tbl_usuarios ua ON a.abogado_id = ua.id
			LEFT JOIN tbl_personal_apt pa ON ua.personal_id = pa.id
			LEFT JOIN tbl_usuarios ud ON a.director_aprobacion_id = ud.id
			LEFT JOIN tbl_personal_apt pud ON ud.personal_id = pud.id
			LEFT JOIN tbl_usuarios uap ON a.director_aprobacion_id = uap.id
			LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id
		WHERE 
			c.tipo_contrato_id = 13
			AND a.procesado = 0
			AND a.status = 1
		" . $where_empresa . "
		" . $where_ruc . "
		" . $where_razon_social . "
		" . $where_moneda . "
		" . $where_ubigeo . "
		" . $where_nombre_tienda . "
		" . $query_fecha . "
		" . $where_estado_sol . "
		" . $where_estado_sol_v2 . "
		" . $where_estado_cancelado . "
		 
		");

	$resultNum = $total_query->fetch_assoc();
	$num_rows = $resultNum['num_rows'];
	$page = (isset($_REQUEST['page']) && !empty($_REQUEST['page'])) ? $_REQUEST['page'] : 1;
	$per_page = 10;
	$numLinks = 10;
	$total_paginate = ceil($num_rows / $per_page);
	$total_paginate = $total_paginate == 0 ? 1 : $total_paginate;
	$page = $page > $total_paginate ? $total_paginate : $page;
	$offset = ($page - 1) * $per_page;
	$paginate = new Pagination('sec_contratos_solicitud_cambiar_de_pagina', $num_rows, $per_page, $numLinks, $page);

	$sel_query = $mysqli->query("
		SELECT
			a.id,
			a.codigo,
			c.contrato_id,
			c.nombre_tienda,
			CONCAT(p.nombre, ' ', IFNULL(p.apellido_paterno,''), ' ', IFNULL(p.apellido_materno,'')) AS solicitante,
			p.area_id,
			a.created_at,
			co.sigla AS sigla_correlativo,
			c.codigo_correlativo,
			a.estado_solicitud_id,
			es.nombre AS nombre_estado_solicitud,
			a.procesado,
			a.cancelado_id,
			(CASE WHEN (c.check_gerencia_proveedor =0 and c.fecha_aprobacion IS NULL AND c.aprobador_id=0) THEN 'Pendiente' ELSE '' END) as aprob, 	
			(CASE WHEN (c.check_gerencia_proveedor =0 and c.fecha_aprobacion IS NOT NULL AND c.aprobador_id=1) THEN 'Aprobado' ELSE '' END) as aprobS, 
			(CASE WHEN (c.check_gerencia_proveedor =0 and c.fecha_aprobacion IS NOT NULL AND c.aprobador_id=0) THEN 'Denegado' ELSE '' END) as aprobN,
			CONCAT(IFNULL(pud.nombre, ''),' ',IFNULL(pud.apellido_paterno, ''),' ',IFNULL(pud.apellido_materno, '')) AS nombre_del_director_a_aprobar,
			CONCAT(IFNULL(puap.nombre, ''),' ',IFNULL(puap.apellido_paterno, ''),' ',IFNULL(puap.apellido_materno, '')) AS nombre_del_aprobador,
			a.director_aprobacion_id,
			a.aprobado_por_id,
			a.aprobado_el,
			a.fecha_cambio_estado_solicitud,
			a.dias_habiles,
			CONCAT(IFNULL(pa.nombre, ''),' ',IFNULL(pa.apellido_paterno, ''),' ',IFNULL(pa.apellido_materno, '')) AS abogado
		FROM 
			cont_adendas a
			LEFT JOIN cont_contrato c ON a.contrato_id = c.contrato_id
			LEFT JOIN tbl_usuarios u ON a.user_created_id = u.id
			LEFT JOIN tbl_personal_apt p ON u.personal_id = p.id
			LEFT JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id AND ce.status = 1
			LEFT JOIN cont_inmueble i ON c.contrato_id = i.contrato_id AND i.contrato_detalle_id = ce.contrato_detalle_id
			LEFT JOIN cont_etapa e ON c.etapa_id = e.etapa_id
			LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			LEFT JOIN cont_estado_solicitud es ON es.id = a.estado_solicitud_id
			LEFT JOIN tbl_usuarios ua ON a.abogado_id = ua.id
			LEFT JOIN tbl_personal_apt pa ON ua.personal_id = pa.id
			LEFT JOIN tbl_usuarios ud ON a.director_aprobacion_id = ud.id
			LEFT JOIN tbl_personal_apt pud ON ud.personal_id = pud.id
			LEFT JOIN tbl_usuarios uap ON a.director_aprobacion_id = uap.id
			LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id
		WHERE 
			c.tipo_contrato_id = 13
			AND a.procesado = 0
			AND a.status = 1
			" . $where_empresa . "
			" . $where_ruc . "
			" . $where_razon_social . "
			" . $where_moneda . "
			" . $where_ubigeo . "
			" . $where_nombre_tienda . "
			" . $query_fecha . "
			" . $where_estado_sol . "
			" . $where_estado_sol_v2 . "
			" . $where_estado_cancelado . "
			GROUP BY 
			a.id, a.codigo, c.contrato_id, c.nombre_tienda, 
			p.nombre, p.apellido_paterno, p.apellido_materno, p.area_id, 
			a.created_at, co.sigla, c.codigo_correlativo, a.estado_solicitud_id, 
			es.nombre, a.procesado, a.cancelado_id, c.check_gerencia_proveedor, 
			c.fecha_aprobacion, c.aprobador_id, pud.nombre, pud.apellido_paterno, 
			pud.apellido_materno, puap.nombre, puap.apellido_paterno, puap.apellido_materno, 
			a.director_aprobacion_id, a.aprobado_por_id, a.aprobado_el, 
			a.fecha_cambio_estado_solicitud, a.dias_habiles, pa.nombre, 
			pa.apellido_paterno, pa.apellido_materno
		ORDER BY a.created_at DESC
	 
		");

?>
	<div class="w-100 text-center">
		<h5 style="font-weight: bold; margin: 0px;">SOLICITUDES DE ADENDA DE CONTRATO DE LOCACIÓN DE SERVICIO</h5>
	</div>
	<br>
	<div class="table-responsive">
		<table class="table table-striped table-hover table-condensed table-bordered tabla_contratos_para_filtro" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th class="text-center">Código</th>

					<th class="text-center">Solicitante</th>
					<th class="text-center">F. Solicitud</th>
					<th class="text-center">Estado</th>

					<th class="text-center">Estado Aprobación</th>
					<th class="text-center">Aprobante</th>
					<th class="text-center">Ver Detalle</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th class="text-center">Código</th>

					<th class="text-center">Solicitante</th>
					<th class="text-center">F. Solicitud</th>
					<th class="text-center">Estado</th>

					<th class="text-center">Estado Aprobación</th>
					<th class="text-center">Aprobante</th>
					<th class="text-center">Ver Detalle</th>
				</tr>
			</tfoot>
			<tbody>
				<?php
				while ($sel = $sel_query->fetch_assoc()) {
					$nombre_adenda = 'Adenda N° ' . $sel['codigo'] . ' - ' . $sel["sigla_correlativo"] . $sel["codigo_correlativo"];
					$nombre_tienda = $sel["nombre_tienda"];
				?>
					<tr>
						<td class="text-center"><?php echo $nombre_adenda; ?></td>

						<td><?php echo $sel["solicitante"]; ?></td>
						<td><?php echo $sel["created_at"]; ?></td>
						<td class="text-center">
							<?php
							if ($sel["cancelado_id"] == 1) {
								echo '<span class="badge bg-danger text-white">Cancelado</span>';
							} elseif ($area_id == '33') {
								$bg_estado_solicitud = '';
								switch ($sel["estado_solicitud_id"]) {
									case 1:
										$bg_estado_solicitud = 'bg-default';
										break;
									case 2:
										$bg_estado_solicitud = 'bg-info';
										break;
									case 3:
										$bg_estado_solicitud = 'bg-warning';
										break;
								}
							?>
								<span class="badge <?= $bg_estado_solicitud ?> text-white"><?php echo $sel["nombre_estado_solicitud"]; ?></span>
							<?php } else {
								echo '<span class="badge bg-info text-white">En proceso</span>';
							} ?>
						</td>

						<td class="text-center">
							<?php
							if (($sel["area_id"] ==  $login["area_id"]) || $usuario_id_login == $sel["director_aprobacion_id"] || $usuario_id_login == $sel["aprobado_por_id"] || $area_id == '6' || ($area_id == '33')) {
								echo $sel["aprob"];
								echo $sel["aprobS"];
								echo $sel["aprobN"];
							}
							?>
						</td>
						<td>
							<?php
							if (($sel["area_id"] ==  $login["area_id"]) || $usuario_id_login == $sel["director_aprobacion_id"] || $usuario_id_login == $sel["aprobado_por_id"] || $area_id == '6' || ($area_id == '33')) {

								if (trim($sel["aprob"]) == "Pendiente") {
									echo $sel["nombre_del_director_a_aprobar"];
								} else {
									echo $sel["nombre_del_aprobador"];
								}
							}
							?>
						</td>

						<td class="text-center">
							<a class="btn btn-rounded btn-warning btn-sm"
								href="./?sec_id=<?php echo $sec_id; ?>&amp;sub_sec_id=detalle_adenda_locacion&id=<?php echo $sel["id"]; ?>"
								title="Editar solicitud">
								<i class="fa fa-pencil"></i>
							</a>
							<a class="btn btn-rounded btn-primary btn-sm"
								href="./?sec_id=<?php echo $sec_id; ?>&amp;sub_sec_id=detalle_solicitud_locacion_servicio&id=<?php echo $sel["contrato_id"]; ?>&adenda_id=<?php echo $sel["id"]; ?>"
								title="Ver detalle">
								<i class="fa fa-eye"></i>
							</a>
							<?php if ($sel["procesado"] != 1 && $sel["cancelado_id"] != 1 && $sel["area_id"] == $area_id_login) { ?>
								<a class="btn btn-rounded btn-danger btn-sm"
									onclick="sec_contrato_solicitud_cancelar_solicitud_modal(1, <?php echo $sel["id"]; ?>, '<?php echo $nombre_adenda . ' ' . $nombre_tienda; ?>')"
									title="Eliminar Solicitud">
									<i class="fa fa-close"></i>
								</a>
							<?php } ?>
						</td>
					</tr>
				<?php
				}
				?>
			</tbody>
		</table>
	</div>



<?php
}
