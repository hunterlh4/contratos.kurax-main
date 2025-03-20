<?php
global $mysqli;
$menu_id = "";
$area_id = $login ? $login['area_id'] : 0;
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'contrato' AND sub_sec_id = 'solicitud' LIMIT 1");
while ($r = $result->fetch_assoc())
	$menu_id = $r["id"];
$usuario_id = $login ? $login['id'] : null;

$permiso_ver = in_array("nuevo_contrato_arrendamiento", $usuario_permisos[$menu_id]);


$adenda_id = $_GET['id'];

$query = "SELECT a.contrato_id, a.abogado_id FROM cont_adendas a WHERE a.id = " . $adenda_id;
$list_query = $mysqli->query($query);
if ($list_query->num_rows > 0) {
	$row = $list_query->fetch_assoc();
	$contrato_id = $row['contrato_id'];
	$abogado_id = $row['abogado_id'];
}

$id_contrato_arrendamiento = $contrato_id;

$sel_query = $mysqli->query("
	SELECT 
		c.contrato_id,
		c.tipo_contrato_id,
		c.empresa_suscribe_id,
		r.nombre AS empresa_suscribe,
		c.persona_responsable_id,
		c.verificar_giro,
		c.fecha_verificacion_giro,
		c.usuario_verificacion_giro,
		CONCAT(IFNULL(pgiro.nombre, ''),' ',IFNULL(pgiro.apellido_paterno, ''),' ',IFNULL(pgiro.apellido_materno, '')) AS persona_verificaciongiro,
		CONCAT(IFNULL(p2.nombre, ''),' ',IFNULL(p2.apellido_paterno, ''),' ',IFNULL(p2.apellido_materno, '')) AS persona_responsable,
		CONCAT(IFNULL(pjc.nombre, ''),' ',IFNULL(pjc.apellido_paterno, ''),' ',IFNULL(pjc.apellido_materno, '')) AS jefe_comercial,
		c.jefe_comercial_id,
		c.nombre_tienda,
		c.observaciones,
		c.user_created_id,
		CONCAT(IFNULL(p.nombre, ''),' ',IFNULL(p.apellido_paterno, ''),' ',IFNULL(p.apellido_materno, '')) AS user_created,
		CONCAT(IFNULL(pa.nombre, ''),' ',IFNULL(pa.apellido_paterno, ''),' ',IFNULL(pa.apellido_materno, '')) AS abogado,
		c.created_at,
		c.cc_id
	FROM
		cont_contrato c
		INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id
		INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
		INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		LEFT JOIN tbl_usuarios u2 ON c.persona_responsable_id = u2.id
		LEFT JOIN tbl_personal_apt p2 ON u2.personal_id = p2.id
		LEFT JOIN tbl_usuarios ugiro ON c.usuario_verificacion_giro = ugiro.id
		LEFT JOIN tbl_personal_apt pgiro ON ugiro.personal_id = pgiro.id
		LEFT JOIN tbl_usuarios ujc ON c.jefe_comercial_id = ujc.id
		LEFT JOIN tbl_personal_apt pjc ON ujc.personal_id = pjc.id

		LEFT JOIN tbl_usuarios ua ON c.abogado_id = ua.id
		LEFT JOIN tbl_personal_apt pa ON ua.personal_id = pa.id
	WHERE 
		c.contrato_id IN (" . $id_contrato_arrendamiento . ")");
while ($sel = $sel_query->fetch_assoc()) {
	$contrato_id  = $sel["contrato_id"];
	$tipo_contrato_id  = $sel["tipo_contrato_id"];
	$empresa_suscribe = $sel["empresa_suscribe"];
	$nombre_tienda = $sel["nombre_tienda"];
	$observaciones = $sel["observaciones"];
	$supervisor = trim($sel["persona_responsable"]);
	$jefe_comercial = trim($sel["jefe_comercial"]);
	$abogado = trim($sel["abogado"]);
	$usuario_created_id = $sel["user_created_id"];
	$verificar_giro = $sel["verificar_giro"];
	$fecha_verificacion_giro = $sel["fecha_verificacion_giro"];
	$usuario_verificacion_giro = $sel["persona_verificaciongiro"];
	$centro_de_costos = $sel["cc_id"];

	if (empty($nombre_tienda)) {
		$nombre_tienda = 'Sin asignar';
	}

	if (empty($centro_de_costos)) {
		$centro_de_costos = 'Sin asignar';
	}

	if (empty($supervisor)) {
		$supervisor = 'Sin asignar';
	}

	if (empty($jefe_comercial)) {
		$jefe_comercial = 'Sin asignar';
	}
}


$tipo_persona = "";

include '/var/www/html/sys/function_replace_invalid_caracters_contratos.php';

?>
<style>
	.campo_obligatorio {
		font-size: 15px;
		color: red;
	}

	.campo_obligatorio_v2 {
		font-size: 13px;
		color: red;
	}

	.sec_contrato_nuevo_adenda_datepicker {
		min-height: 28px !important;
	}

	.ui-state-disabled {
		background-image: none;
		opacity: 1;
	}
</style>


<div id="div_sec_contrato_nuevo">

	<div id="loader_"></div>

	<div class="row">
		<div class="col-xs-12 text-center">
			<h1 class="page-title titulosec_contrato"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Editar Solicitud - Adenda de Contrato de Arrendamiento</h1>
		</div>
	</div>


	<div class="row">
		<div class="col-xs-12 col-md-12 col-lg-12">
			<!-- PANEL: Tipo contrato -->
			<div class="panel">
				<div class="panel-heading">
					<div class="panel-title">Nueva Solicitud</div>
				</div>
				<div class="panel-body">
					<form id="form_contrato_interno" name="form_contrato_interno" method="POST" enctype="multipart/form-data" autocomplete="off">
						<input type="hidden" name="usuario_id_temporal" id="usuario_id_temporal" value="<?php echo $usuario_id; ?>">
						<input type="hidden" name="tipo_contrato_id" id="tipo_contrato_id" value="1">
						<input type="hidden" name="sec_con_nuevo_adenda_id" id="sec_con_nuevo_adenda_id" value="<?= $adenda_id ?>">



						<div class="row">
							<div class="col-md-12">
								<br>
							</div>

							<div class="col-xs-12 col-md-12 col-lg-7">
								<div class="col-xs-12 col-md-12 col-lg-7" style="padding-right: 0px; margin-bottom: 20px;">

									<?php
									if ((array_key_exists($menu_id, $usuario_permisos) && in_array("reenviar_adenda_arrendamiento", $usuario_permisos[$menu_id]))) {
									?>
										<a class="btn btn-info btn-xs" onclick="reenviar_solicitud_adenda_arrendamiento(<?php echo $adenda_id; ?>);">
											<span class="fa fa-envelope-o"></span> Reenviar por email la solicitud
										</a>
									<?php
									}
									?>
								</div>
							</div>

							<div class="col-xs-12 col-md-12 col-lg-7">


								<div class="panel">
									<div class="panel-heading">
										<div class="panel-title">DETALLE DE LA ADENDA DE CONTRATO DE ARRENDAMIENTO</div>
										<input type="hidden" value="<?= $contrato_id ?>" id="id_registro_contrato_id">
										<input type="hidden" value="<?= $tipo_contrato_id ?>" id="id_tipo_contrato">
									</div>
									<div class="panel-body p-0">

										<div class="col-xs-12 col-md-12 col-lg-12">
											<div class="h4">
												<b>DATOS GENERALES</b>
											</div>
										</div>

										<div class="col-xs-12 col-md-12 col-lg-12">
											<div class="form-group">
												<table class="table table-bordered table-hover">

													<tr>
														<td><b>Empresa Arrendataria</b></td>
														<td><?= $empresa_suscribe ?></td>
														<td style="width: 75px;">
															<a class="btn btn-success btn-xs"
																onclick="sec_con_detalle_aden_arrend_solicitud_editar_campo_adenda('Datos Generales','cont_contrato','empresa_suscribe_id','Empresa Arrendaria','select_option','<?= $empresa_suscribe ?>','obtener_empresa_at','');">
																<span class="fa fa-edit"></span> Editar
															</a>
														</td>
													</tr>

													<!-- <tr>
														<td><b>Supervisor</b></td>
														<td><?= $supervisor ?></td>
														<td style="width: 75px;">
															<a class="btn btn-success btn-xs"
																onclick="sec_con_detalle_aden_arrend_solicitud_editar_campo_adenda('Datos Generales','cont_contrato','persona_responsable_id','Supervisor','select_option','<?= $supervisor ?>','obtener_personal_responsable','');">
																<span class="fa fa-edit"></span> Editar
															</a>
														</td>
													</tr> -->

													<!-- <tr>
							<td><b>Abogado</b></td>
							<td><?= $abogado ?></td>
							<td style="width: 75px;">
							<a class="btn btn-success btn-xs" 
							onclick="sec_con_detalle_aden_arrend_solicitud_editar_campo_adenda('Datos Generales','cont_contrato','abogado_id','Jefe Comercial','select_option','<?= $abogado ?>','obtener_abogados','');">
							<span class="fa fa-edit"></span> Editar
							</a>
							</td>
							</tr> -->

												</table>
											</div>
										</div>

										<div class="col-xs-12 col-md-12 col-lg-12">
											<br>
										</div>







										<?php
										$query = "SELECT pr.propietario_id,
								tp.nombre AS tipo_persona,
								td.nombre AS tipo_docu_identidad,
								p.id AS persona_id,
								p.num_docu,
								p.num_ruc,
								p.nombre,
								p.direccion,
								p.representante_legal,
								p.num_partida_registral,
								p.contacto_nombre,
								p.contacto_telefono,
								p.contacto_email
							FROM cont_propietario pr
							INNER JOIN cont_persona p ON pr.persona_id = p.id
							INNER JOIN cont_tipo_persona tp ON p.tipo_persona_id = tp.id
							INNER JOIN cont_tipo_docu_identidad td ON p.tipo_docu_identidad_id = td.id
							WHERE pr.status = 1 AND pr.contrato_id = " . $id_contrato_arrendamiento . "";
										$propiertario_query = $mysqli->query($query);

										?>



										<input type="hidden" id="contrato_id" value="<?= $id_contrato_arrendamiento ?>" />
										<div class="col-xs-12 col-md-12 col-lg-12">
											<div class="h4">
												<b>DATOS DEL ARRENDADOR</b> <br>

											</div>
										</div>

										<?php

										$nro = 1;
										while ($row = $propiertario_query->fetch_assoc()) {
											$propietario_id = $row["propietario_id"];
											$persona_id = $row["persona_id"];
											$tipo_persona = $row["tipo_persona"];
											$tipo_docu_identidad = $row["tipo_docu_identidad"];
											$num_docu = $row["num_docu"];
											$num_ruc = $row["num_ruc"];
											$nombre = $row["nombre"];
											$direccion = $row["direccion"];
											$representante_legal = $row["representante_legal"];
											$num_partida_registral_per = $row["num_partida_registral"];
											$contacto_nombre = $row["contacto_nombre"];
											$contacto_telefono = $row["contacto_telefono"];
											$contacto_email = $row["contacto_email"];
										?>

											<div class="col-xs-12 col-md-12 col-lg-12">
												<div class="">
													<b>PROPIETARIO # <?= $nro ?></b>
													<a class="btn btn-warning btn-xs"
														onclick="sec_con_detalle_aden_arrend_buscar_propietario_modal('CambiarPropietario','<?= $propietario_id ?>','<?= $persona_id ?>')">
														<span class="fa fa-pencil"></span> Reemplazar Propietario
													</a>
												</div>
											</div>

											<div class="col-xs-12 col-md-12 col-lg-12">
												<div class="form-group">
													<table class="table table-bordered table-hover">

														<tr>
															<td><b>Tipo de Persona</b></td>
															<td><?= $tipo_persona ?></td>
															<td style="width: 75px;">
																<a class="btn btn-success btn-xs"
																	onclick="sec_con_detalle_aden_arrend_solicitud_editar_campo_adenda('Propietario','cont_persona','tipo_persona_id','Tipo de Persona','select_option','<?= $tipo_persona ?>','obtener_tipo_persona','');">
																	<span class="fa fa-edit"></span> Editar
																</a>
															</td>
														</tr>

														<tr>
															<td><b>Nombre</b></td>
															<td><?= $nombre ?></td>
															<td style="width: 75px;">
																<a class="btn btn-success btn-xs"
																	onclick="sec_con_detalle_aden_arrend_solicitud_editar_campo_adenda('Propietario','cont_persona','nombre','Nombre','varchar','<?= $nombre ?>','','');">
																	<span class="fa fa-edit"></span> Editar
																</a>
															</td>
														</tr>

														<tr>
															<td><b>Tipo de Documento de Identidad</b></td>
															<td><?= $tipo_docu_identidad ?></td>
															<td style="width: 75px;">
																<a class="btn btn-success btn-xs"
																	onclick="sec_con_detalle_aden_arrend_solicitud_editar_campo_adenda('Propietario','cont_persona','tipo_docu_identidad_id','Tipo de Documento de Identidad','select_option','<?= $tipo_docu_identidad ?>','obtener_tipo_docu_identidad','');">
																	<span class="fa fa-edit"></span> Editar
																</a>
															</td>
														</tr>

														<tr>
															<td><b>Número de <?= $tipo_docu_identidad ?></b></td>
															<td><?= $num_docu ?></td>
															<td style="width: 75px;">
																<a class="btn btn-success btn-xs"
																	onclick="sec_con_detalle_aden_arrend_solicitud_editar_campo_adenda('Propietario','cont_persona','num_docu','Número de Documento de Identidad','varchar','<?= $num_docu ?>','','');">
																	<span class="fa fa-edit"></span> Editar
																</a>
															</td>
														</tr>

														<tr>
															<td><b>Número de RUC</b></td>
															<td><?= $num_ruc ?></td>
															<td style="width: 75px;">
																<a class="btn btn-success btn-xs"
																	onclick="sec_con_detalle_aden_arrend_solicitud_editar_campo_adenda('Propietario','cont_persona','num_ruc','RUC','varchar','<?= $num_ruc ?>','','');">
																	<span class="fa fa-edit"></span> Editar
																</a>
															</td>
														</tr>

														<tr>
															<td><b>Domicilio del propietario</b></td>
															<td><?= $direccion ?></td>
															<td style="width: 75px;">
																<a class="btn btn-success btn-xs"
																	onclick="sec_con_detalle_aden_arrend_solicitud_editar_campo_adenda('Propietario','cont_persona','direccion','Domicilio del propietario','varchar','<?= $direccion ?>','','');">
																	<span class="fa fa-edit"></span> Editar
																</a>
															</td>
														</tr>

														<tr>
															<td><b>Representante Legal</b></td>
															<td><?= $representante_legal ?></td>
															<td style="width: 75px;">
																<a class="btn btn-success btn-xs"
																	onclick="sec_con_detalle_aden_arrend_solicitud_editar_campo_adenda('Propietario','cont_persona','representante_legal','Representante Legal','varchar','<?= $representante_legal ?>','','');">
																	<span class="fa fa-edit"></span> Editar
																</a>
															</td>
														</tr>

														<tr>
															<td><b>N° de Partida Registral de la empresa</b></td>
															<td><?= $num_partida_registral_per ?></td>
															<td style="width: 75px;">
																<a class="btn btn-success btn-xs"
																	onclick="sec_con_detalle_aden_arrend_solicitud_editar_campo_adenda('Propietario','cont_persona','num_partida_registral','N° de Partida Registral de la empresa','varchar','<?= $num_partida_registral_per ?>','','');">
																	<span class="fa fa-edit"></span> Editar
																</a>
															</td>
														</tr>

													</table>
												</div>
											</div>
										<?php
											$nro++;
										}
										?>





										<div class="col-xs-12 col-md-12 col-lg-12">
											<br>
										</div>


										<?php



										$query_detalle = "SELECT d.id, d.codigo, d.observaciones FROM cont_contrato_detalle d WHERE d.status = 1 AND d.contrato_id = " . $id_contrato_arrendamiento . " ORDER BY d.id ASC";
										$result_detalle = $mysqli->query($query_detalle);

										while ($detalle_con = $result_detalle->fetch_assoc()) {
											$sel_query = $mysqli->query("SELECT
								i.id,
								MAX(udep.nombre) AS department,
								MAX(up.nombre) AS province,
								MAX(ud.nombre) AS district,
								i.ubicacion,
								i.latitud,
								i.longitud,
								i.id_empresa_servicio_agua,
								i.id_empresa_servicio_luz,
								MAX(lspe1.razon_social) AS empresa_servicio_agua,
								MAX(lspe2.razon_social) AS empresa_servicio_luz,
								i.area_arrendada,
								i.ubigeo_id,
								i.num_partida_registral,
								i.oficina_registral,
								i.num_suministro_agua,
								i.tipo_compromiso_pago_agua,
								MAX(t1.nombre) AS tipo_pago_agua,
								i.monto_o_porcentaje_agua,
								i.num_suministro_luz,
								i.tipo_compromiso_pago_luz,
								MAX(t2.nombre) AS tipo_pago_luz,
								i.monto_o_porcentaje_luz,
								i.tipo_compromiso_pago_arbitrios,
								MAX(ta.nombre) AS tipo_pago_arbitrios,
								i.porcentaje_pago_arbitrios
							FROM cont_inmueble i
							LEFT JOIN cont_tipo_pago_servicio t1 ON i.tipo_compromiso_pago_agua = t1.id
							LEFT JOIN cont_tipo_pago_servicio t2 ON i.tipo_compromiso_pago_luz = t2.id
							INNER JOIN cont_tipo_pago_arbitrios ta ON i.tipo_compromiso_pago_arbitrios = ta.id
							LEFT JOIN cont_local_servicio_publico_empresas lspe1 ON i.id_empresa_servicio_agua = lspe1.id
							LEFT JOIN cont_local_servicio_publico_empresas lspe2 ON i.id_empresa_servicio_luz = lspe2.id
							LEFT JOIN tbl_ubigeo ud ON (
								ud.cod_depa = SUBSTRING(i.ubigeo_id, 1, 2) 
								AND ud.cod_prov = SUBSTRING(i.ubigeo_id, 3, 2) 
								AND ud.cod_dist = SUBSTRING(i.ubigeo_id, 5, 2)
							)
							LEFT JOIN tbl_ubigeo up ON (
								up.cod_depa = SUBSTRING(i.ubigeo_id, 1, 2) 
								AND up.cod_prov = SUBSTRING(i.ubigeo_id, 3, 2) 
								AND up.cod_dist = '00'
							)
							LEFT JOIN tbl_ubigeo udep ON (
								udep.cod_depa = SUBSTRING(i.ubigeo_id, 1, 2) 
								AND udep.cod_prov = '00' 
								AND udep.cod_dist = '00'
							)
							WHERE i.contrato_detalle_id = " . $detalle_con['id'] . " 
							AND i.contrato_id = " . $id_contrato_arrendamiento . "
							GROUP BY 
								i.id, 
								i.ubicacion, 
								i.latitud, 
								i.longitud, 
								i.id_empresa_servicio_agua, 
								i.id_empresa_servicio_luz, 
								i.area_arrendada, 
								i.ubigeo_id, 
								i.num_partida_registral, 
								i.oficina_registral, 
								i.num_suministro_agua, 
								i.tipo_compromiso_pago_agua, 
								i.monto_o_porcentaje_agua, 
								i.num_suministro_luz, 
								i.tipo_compromiso_pago_luz, 
								i.monto_o_porcentaje_luz, 
								i.tipo_compromiso_pago_arbitrios, 
								i.porcentaje_pago_arbitrios");
											$row = $sel_query->fetch_assoc();
											$inmueble_id = $row["id"];
											$ubigeo_id = $row["ubigeo_id"];
											$departamento = $row["department"];
											$provincia = $row["province"];
											$distrito = $row["district"];
											$ubicacion = $row["ubicacion"];
											$latitud = $row["latitud"];
											$longitud = $row["longitud"];

											$id_empresa_servicio_agua = $row["id_empresa_servicio_agua"];
											$id_empresa_servicio_luz = $row["id_empresa_servicio_luz"];
											$empresa_servicio_agua = $row["empresa_servicio_agua"];
											$empresa_servicio_luz = $row["empresa_servicio_luz"];

											$area_arrendada = $row["area_arrendada"] . ' m2';
											$num_partida_registral = $row["num_partida_registral"];
											$oficina_registral = $row["oficina_registral"];


											$tipo_compromiso_pago_arbitrios = $row["tipo_compromiso_pago_arbitrios"];
											$tipo_pago_arbitrios = $row["tipo_pago_arbitrios"];
											$porcentaje_pago_arbitrios = $row["porcentaje_pago_arbitrios"] . '%';








											$query = "SELECT 
								c.condicion_economica_id,
								c.contrato_id,
								c.monto_renta,
								c.tipo_moneda_id,
								m.nombre AS moneda_contrato,
								concat(m.nombre,' (', m.simbolo,')') AS moneda_contrato_con_simbolo,
								m.simbolo AS simbolo_moneda,
								c.pago_renta_id,
								c.cuota_variable,
								tpr.nombre AS pago_renta,
								tv.nombre AS tipo_venta,
								tai.nombre AS igv_en_la_renta,
								c.impuesto_a_la_renta_id,
								i.nombre AS impuesto_a_la_renta,
								c.carta_de_instruccion_id,
								ci.nombre AS carta_de_instruccion,
								c.numero_cuenta_detraccion,
								c.garantia_monto,
								c.tipo_adelanto_id,
								a.nombre AS tipo_adelanto,
								c.plazo_id,
								tp.nombre as nombre_plazo,
								c.cant_meses_contrato,
								c.fecha_inicio,
								c.fecha_fin,
								c.periodo_gracia_id,
								p.nombre AS periodo_gracia,
								c.periodo_gracia_numero,
								c.periodo_gracia_inicio,
								c.periodo_gracia_fin,
								dp.nombre AS dia_de_pago,
								c.num_alerta_vencimiento,
								c.cargo_mantenimiento,
								c.fecha_suscripcion,
								c.status,
								c.user_created_id,
								c.created_at,
								c.user_updated_id,
								c.updated_at
								FROM cont_condicion_economica c
								INNER JOIN tbl_moneda m ON c.tipo_moneda_id = m.id 
								LEFT JOIN cont_tipo_impuesto_a_la_renta i ON c.impuesto_a_la_renta_id = i.id
								LEFT JOIN cont_tipo_adelanto a ON c.tipo_adelanto_id = a.id
								LEFT JOIN cont_tipo_periodo_de_gracia p ON c.periodo_gracia_id = p.id
								LEFT JOIN cont_tipo_carta_de_instruccion ci ON c.carta_de_instruccion_id = ci.id
								LEFT JOIN cont_tipo_dia_de_pago dp ON c.dia_de_pago_id = dp.id
								LEFT JOIN cont_tipo_plazo tp ON c.plazo_id = tp.id
								LEFT JOIN cont_tipo_pago_renta tpr ON c.pago_renta_id = tpr.id
								LEFT JOIN cont_tipo_venta tv ON c.tipo_venta_id = tv.id
								LEFT JOIN cont_tipo_afectacion_igv tai ON c.afectacion_igv_id = tai.id
								WHERE c.status = 1 AND c.contrato_detalle_id = " . $detalle_con['id'] . " AND c.contrato_id = " . $id_contrato_arrendamiento;

											$sel_query = $mysqli->query($query);
											$row = $sel_query->fetch_assoc();

											$condicion_economica_id = $row["condicion_economica_id"];
											$simbolo_moneda = $row["simbolo_moneda"];
											$moneda_contrato = $row["moneda_contrato"];
											$monto_renta = $simbolo_moneda . ' ' . number_format($row["monto_renta"], 2, '.', ',');
											$garantia_monto = $simbolo_moneda . ' ' . number_format($row["garantia_monto"], 2, '.', ',');

											$monto_renta = $simbolo_moneda . ' ' . number_format($row["monto_renta"], 2, '.', ',');
											$monto_renta_sin_formato = $row["monto_renta"];
											$pago_renta_id = $row["pago_renta_id"];
											$pago_renta = $row["pago_renta"];
											$cuota_variable = ((float) $row["cuota_variable"]) . '%';
											$tipo_venta = $row["tipo_venta"];
											$igv_en_la_renta = $row["igv_en_la_renta"];

											$impuesto_a_la_renta_id = $row["impuesto_a_la_renta_id"];
											$impuesto_a_la_renta_texto = $row["impuesto_a_la_renta"];
											$carta_de_instruccion_id = $row["carta_de_instruccion_id"];
											$carta_de_instruccion = $row["carta_de_instruccion"];
											$numero_cuenta_detraccion = $row["numero_cuenta_detraccion"];


											$plazo_id = $row["plazo_id"];
											$nombre_plazo = $row["nombre_plazo"];
											$cant_meses_contrato = $row["cant_meses_contrato"];
											$fecha_inicio = $row["fecha_inicio"];
											$contrato_inicio_fecha = date("d-m-Y", strtotime($fecha_inicio));
											$fecha_fin = $row["fecha_fin"];
											$contrato_fin_fecha = date("d-m-Y", strtotime($fecha_fin));
											$fecha_suscripcion = $row["fecha_suscripcion"];
											$contrato_fecha_suscripcion = date("d-m-Y", strtotime($fecha_suscripcion));
											$tipo_adelanto = $row["tipo_adelanto"];

											$dia_de_pago = $row["dia_de_pago"];
											$periodo_gracia = $row["periodo_gracia"];
											$periodo_gracia_numero = $row["periodo_gracia_numero"];
											$periodo_gracia_inicio = $row["periodo_gracia_inicio"];
											$periodo_gracia_fin = $row["periodo_gracia_fin"];

											if (empty($cant_meses_contrato)) {
												$cant_meses_contrato = 'Sin asignar';
											} else {
												$cant_meses_contrato = sec_contrato_detalle_solicitud_de_meses_a_anios_y_meses($cant_meses_contrato) . ' (' . $cant_meses_contrato . ' meses)';
											}

											// INICIO IMPUESTO A LA RENTA DETALLADO
											$factor = 1.05265;
											$renta_bruta = 0;
											$renta_neta = 0;
											$impuesto_a_la_renta = 0;
											$detalle = '';

											if ($impuesto_a_la_renta_id == 1) {
												$impuesto_a_la_renta = round($monto_renta_sin_formato * 0.05);
												$renta_bruta = $monto_renta_sin_formato;

												if ($carta_de_instruccion_id == 1) {
													$renta_neta = $monto_renta_sin_formato - $impuesto_a_la_renta;
													$quien_paga = 'AT';
												} elseif ($carta_de_instruccion_id == 2) {
													$renta_neta = $monto_renta_sin_formato;
													$quien_paga = 'Arrendador';
												}

												$detalle = 'AT deposita la renta (' . $simbolo_moneda . ' ' . number_format($renta_neta, 2, '.', ',') . ' ' . $moneda_contrato . ') al Arrendador. El ' . $quien_paga . ' realiza el pago del impuesto a la renta (' . $simbolo_moneda . ' ' . number_format($impuesto_a_la_renta, 0, '.', ',') . $moneda_contrato . ') a SUNAT.';
											} elseif ($impuesto_a_la_renta_id == 2) {
												$impuesto_a_la_renta = round(($monto_renta_sin_formato * $factor) - $monto_renta_sin_formato);
												$renta_bruta = $monto_renta_sin_formato + round($impuesto_a_la_renta);
												$renta_neta = $monto_renta_sin_formato;

												if ($carta_de_instruccion_id == 1) {
													$renta_neta = $monto_renta_sin_formato;
													$quien_paga = 'AT';
													$detalle = 'AT deposita renta (' . $simbolo_moneda . ' ' . number_format($monto_renta_sin_formato, 2, '.', ',') . ' ' . $moneda_contrato . ') al Arrendador. AT realiza el pago del impuesto a la renta (' . $simbolo_moneda . ' ' . number_format($impuesto_a_la_renta, 0, '.', ',') . $moneda_contrato . ') a SUNAT.';
												} elseif ($carta_de_instruccion_id == 2) {
													$renta_neta = $monto_renta_sin_formato + $impuesto_a_la_renta;
													$quien_paga = 'Arrendador';
													$detalle = 'AT deposita ' . $simbolo_moneda . ' ' . number_format($renta_neta, 2, '.', ',') . ' ' . $moneda_contrato . ' al Arrendador. El Arrendador realiza el pago del impuesto a la renta (' . $simbolo_moneda . ' ' . number_format($impuesto_a_la_renta, 0, '.', ',') . ' ' . $moneda_contrato . ') a SUNAT.';
												}
											}

											$impuesto_a_la_renta = $simbolo_moneda . ' ' . number_format($impuesto_a_la_renta, 0, '.', ',') . ' ' . $moneda_contrato;
											$renta_bruta = $simbolo_moneda . ' ' . number_format($renta_bruta, 2, '.', ',') . ' ' . $moneda_contrato;
											$renta_neta = $simbolo_moneda . ' ' . number_format($renta_neta, 2, '.', ',') . ' ' . $moneda_contrato;
											// FIN IMPUESTO A LA RENTA DETALLADO





											// $list_query = $mysqli->query($query);
											// $list = array();
											// while ($li = $list_query->fetch_assoc()) {
											// 	$list[] = $li;
											// }
										?>


											<div class="col-md-12">
												<div class="panel">
													<div class="panel-heading">
														<div class="panel-title">CONTRATO</div>
													</div>
													<div class="panel-body m-0 p-0">


														<div class="col-xs-12 col-md-12 col-lg-12">
															<div class="h4"><b>DATOS DEL INMUEBLE</b></div>
														</div>

														<div class="col-xs-12 col-md-12 col-lg-12">
															<div class="form-group">
																<table class="table table-bordered table-hover">

																	<tr>
																		<td><b>Ubigeo</b></td>
																		<td><?= $ubigeo_id ?></td>
																	</tr>


																	<tr>
																		<td><b>Departamento</b></td>
																		<td><?= $departamento ?></td>
																		<td rowspan="3">
																			<a class="btn btn-success btn-xs"
																				onclick="sec_con_detalle_aden_arrend_solicitud_editar_campo_adenda('Inmueble','cont_inmueble','ubigeo_id','Departamento/Provincia/Distrito','select_option','<?= $departamento . '/' . $provincia . '/' . $distrito ?>','obtener_departamentos','<?= $inmueble_id ?>');">
																				<span class="fa fa-edit"></span> Editar
																			</a>
																		</td>
																	</tr>

																	<tr>
																		<td><b>Provincia</b></td>
																		<td><?= $provincia ?></td>
																	</tr>

																	<tr>
																		<td><b>Distrito</b></td>
																		<td><?= $distrito ?></td>
																	</tr>

																	<tr>
																		<td><b>Ubicación</b></td>
																		<td><?= $ubicacion ?></td>
																		<td style="width: 75px;">
																			<a class="btn btn-success btn-xs"
																				onclick="sec_con_detalle_aden_arrend_solicitud_editar_campo_adenda('Inmueble','cont_inmueble','ubicacion','Ubicación','varchar','<?= $ubicacion ?>','','<?= $inmueble_id ?>');">
																				<span class="fa fa-edit"></span> Editar
																			</a>
																		</td>
																	</tr>

																	<tr>
																		<td><b>N° Partida Registral</b></td>
																		<td><?= $num_partida_registral ?></td>
																		<td style="width: 75px;">
																			<a class="btn btn-success btn-xs"
																				onclick="sec_con_detalle_aden_arrend_solicitud_editar_campo_adenda('Inmueble','cont_inmueble','num_partida_registral','N° Partida Registral','varchar','<?= $num_partida_registral ?>','','<?= $inmueble_id ?>');">
																				<span class="fa fa-edit"></span> Editar
																			</a>
																		</td>
																	</tr>

																	<tr>
																		<td><b>Oficina Registral (Sede)</b></td>
																		<td><?= $oficina_registral ?></td>
																		<td style="width: 75px;">
																			<a class="btn btn-success btn-xs"
																				onclick="sec_con_detalle_aden_arrend_solicitud_editar_campo_adenda('Inmueble','cont_inmueble','oficina_registral','Oficina Registral (Sede)','varchar','<?= $oficina_registral ?>','','<?= $inmueble_id ?>');">
																				<span class="fa fa-edit"></span> Editar
																			</a>
																		</td>
																	</tr>
																</table>




																<div class="col-xs-12 col-md-12 col-lg-12">
																	<br>
																</div>


																<?php

																$query_ser_pub = "SELECT s.id, s.tipo_servicio_id, sp.nombre AS tipo_servicio, s.nro_suministro, s.tipo_compromiso_pago_id, ps.nombre AS tipo_compromiso, s.monto_o_porcentaje, ps.nombre AS tipo_compromiso, s.monto_o_porcentaje, s.nro_documento_beneficiario, s.nombre_beneficiario, s.nro_cuenta_soles, td.nombre as tipo_documento_beneficiario
								FROM cont_inmueble_suministros AS s
								LEFT JOIN cont_tipo_pago_servicio AS ps ON ps.id = s.tipo_compromiso_pago_id
								LEFT JOIN cont_tipo_servicio_publico AS sp ON sp.id = s.tipo_servicio_id
								INNER JOIN cont_inmueble AS i ON i.id = s.inmueble_id
								INNER JOIN cont_contrato_detalle AS cd ON cd.id = i.contrato_detalle_id
								LEFT JOIN tbl_tipo_documento AS td ON td.id = s.tipo_documento_beneficiario
								WHERE i.status = 1 AND s.status = 1 AND s.tipo_servicio_id = 2 AND i.contrato_detalle_id = " . $detalle_con['id'] . " AND i.contrato_id = " . $contrato_id . "
								ORDER BY s.tipo_servicio_id, s.id ASC";
																$result_ser_pub = $mysqli->query($query_ser_pub);

																$index_agua = 1;
																while ($row_sp =  $result_ser_pub->fetch_assoc()) {

																	$suministro_id = $row_sp["id"];
																	$num_suministro_agua = $row_sp["nro_suministro"];
																	$tipo_compromiso_pago_agua = $row_sp["tipo_compromiso_pago_id"];
																	$tipo_pago_agua = $row_sp["tipo_compromiso"];
																	$monto_o_porcentaje_agua = $row_sp["monto_o_porcentaje"];

																	$tipo_documento_beneficiario = $row_sp["tipo_documento_beneficiario"];
																	$nro_documento_beneficiario = $row_sp["nro_documento_beneficiario"];
																	$nombre_beneficiario = $row_sp["nombre_beneficiario"];
																	$nro_cuenta_soles = $row_sp["nro_cuenta_soles"];

																	if ($tipo_compromiso_pago_agua == 1) {
																		$monto_o_porcentaje_agua = $monto_o_porcentaje_agua . '%';
																	} elseif ($tipo_compromiso_pago_agua == 2) {
																		$monto_o_porcentaje_agua = $simbolo_moneda . ' ' . $monto_o_porcentaje_agua;
																	}
																?>

																<?php
																}
																?>

																<div class="col-xs-12 col-md-12 col-lg-12">
																	<br>
																</div>

																<?php
																$query_ser_pub = "SELECT s.id, s.tipo_servicio_id, sp.nombre AS tipo_servicio, s.nro_suministro, s.tipo_compromiso_pago_id, ps.nombre AS tipo_compromiso, s.monto_o_porcentaje, s.monto_o_porcentaje, s.nro_documento_beneficiario, s.nombre_beneficiario, s.nro_cuenta_soles, td.nombre as tipo_documento_beneficiario
								FROM cont_inmueble_suministros AS s
								LEFT JOIN cont_tipo_pago_servicio AS ps ON ps.id = s.tipo_compromiso_pago_id
								LEFT JOIN cont_tipo_servicio_publico AS sp ON sp.id = s.tipo_servicio_id
								INNER JOIN cont_inmueble AS i ON i.id = s.inmueble_id
								INNER JOIN cont_contrato_detalle AS cd ON cd.id = i.contrato_detalle_id
								LEFT JOIN tbl_tipo_documento AS td ON td.id = s.tipo_documento_beneficiario
								WHERE i.status = 1 AND s.status = 1 AND s.tipo_servicio_id = 1 AND i.contrato_detalle_id = " . $detalle_con['id'] . " AND i.contrato_id = " . $contrato_id . "
								ORDER BY s.tipo_servicio_id, s.id ASC";
																$result_ser_pub = $mysqli->query($query_ser_pub);

																$index_luz = 1;
																while ($row_sp =  $result_ser_pub->fetch_assoc()) {

																	$suministro_id = $row_sp["id"];
																	$num_suministro_luz = $row_sp["nro_suministro"];
																	$tipo_compromiso_pago_luz = $row_sp["tipo_compromiso_pago_id"];
																	$tipo_pago_luz = $row_sp["tipo_compromiso"];
																	$monto_o_porcentaje_luz = $row_sp["monto_o_porcentaje"];

																	if ($tipo_compromiso_pago_luz == 1) {
																		$monto_o_porcentaje_luz = $monto_o_porcentaje_luz . '%';
																	} elseif ($tipo_compromiso_pago_luz == 2) {
																		$monto_o_porcentaje_luz = $simbolo_moneda . ' ' . $monto_o_porcentaje_luz;
																	}
																?>

																<?php
																}
																?>

															</div>
														</div>

														<div class="col-xs-12 col-md-12 col-lg-12">
															<br>
														</div>




														<div class="col-xs-12 col-md-12 col-lg-12">
															<div class="h4"><b>CONDICIONES ECONÓMICAS Y COMERCIALES</b></div>
														</div>

														<div class="col-xs-12 col-md-12 col-lg-12">
															<div class="form-group">
																<table class="table table-bordered table-hover">

																	<tr>
																		<td><b>Moneda del contrato</b></td>
																		<td><?= $moneda_contrato ?></td>
																		<td style="width: 75px;">
																			<a class="btn btn-success btn-xs"
																				onclick="sec_con_detalle_aden_arrend_solicitud_editar_campo_adenda('Condiciones económicas y comerciales','cont_condicion_economica','tipo_moneda_id','Moneda del contrato','select_option','<?= $moneda_contrato ?>','obtener_tipo_moneda','<?= $condicion_economica_id ?>');">
																				<span class="fa fa-edit"></span> Editar
																			</a>
																		</td>
																	</tr>

																	<tr>
																		<td><b>Cuota Fija - Monto de Renta Pactada</b></td>
																		<td><?= $monto_renta ?></td>
																		<td>
																			<a class="btn btn-success btn-xs" onclick="sec_con_detalle_aden_arrend_solicitud_editar_campo_adenda('Condiciones económicas y comerciales','cont_condicion_economica','monto_renta','Cuota Fija - Monto de Renta Pactada','decimal','<?= $monto_renta ?>','','<?= $condicion_economica_id ?>');">
																				<span class="fa fa-edit"></span> Editar
																			</a>
																		</td>
																	</tr>
																	<?php
																	if ($pago_renta_id == 2) {
																	?>
																		<tr>
																			<td><b>Cuota Variable - Porcentaje de venta</b></td>
																			<td><?php echo $cuota_variable; ?></td>
																			<td>
																				<a class="btn btn-success btn-xs" onclick="sec_con_detalle_aden_arrend_solicitud_editar_campo_adenda('Condiciones económicas y comerciales','cont_condicion_economica','cuota_variable','Porcentaje de venta','decimal','<?= $cuota_variable ?>','','<?= $condicion_economica_id ?>');">
																					<span class="fa fa-edit"></span> Editar
																				</a>
																			</td>
																		</tr>

																		<tr>
																			<td style="width: 250px;"><b>Cuota Variable - Tipo de venta</b></td>
																			<td><?= $tipo_venta ?></td>
																			<td style="width: 75px;">
																				<a class="btn btn-success btn-xs" onclick="sec_con_detalle_aden_arrend_solicitud_editar_campo_adenda('Condiciones económicas y comerciales','cont_condicion_economica','tipo_venta_id','Tipo de Venta','select_option','<?= $tipo_venta ?>','obtener_tipo_venta','<?= $condicion_economica_id ?>');">
																					<span class="fa fa-edit"></span> Editar
																				</a>
																			</td>
																		</tr>
																	<?php
																	}
																	?>
																</table>
															</div>
														</div>

														<div class="col-xs-12 col-md-12 col-lg-12">
															<br>
														</div>

														<?php

														$query_infl = "SELECT i.id, DATE_FORMAT(i.fecha, '%d-%m-%Y') as fecha, tp.nombre  AS tipo_periodicidad, i.numero, p.nombre as tipo_anio_mes, m.sigla AS moneda, i.porcentaje_anadido, i.tope_inflacion, i.minimo_inflacion
									FROM cont_inflaciones AS i
									LEFT JOIN cont_tipo_periodicidad AS tp ON tp.id = i.tipo_periodicidad_id
									LEFT JOIN tbl_moneda AS m ON m.id = i.moneda_id
									LEFT JOIN cont_periodo as p ON p.id = i.tipo_anio_mes
									WHERE 
										i.contrato_id = $id_contrato_arrendamiento
										AND i.contrato_detalle_id = " . $detalle_con['id'] . "
										AND i.status = 1
									ORDER BY i.id ASC";
														$sel_query = $mysqli->query($query_infl);

														?>
														<div class="col-xs-12 col-md-12 col-lg-12">
															<br>
														</div>
														<?php
														$query = "SELECT c.id, m.nombre as mes, c.multiplicador, c.fecha
									FROM cont_cuotas_extraordinarias AS c
									INNER JOIN tbl_meses AS m ON m.id = c.mes
										WHERE 
											c.contrato_id = $id_contrato_arrendamiento
											AND c.contrato_detalle_id = " . $detalle_con['id'] . "
											AND c.status = 1
										ORDER BY c.id ASC";
														$sel_query = $mysqli->query($query);
														?>

														<?php
														$num_cuota_extraordinaria = 0;
														$row_count_inflaciones = $sel_query->num_rows;
														if ($row_count_inflaciones > 0) {
															while ($sel = $sel_query->fetch_assoc()) {
																$num_cuota_extraordinaria++;

																$mes = $sel['mes'];
																$multiplicador = $sel['multiplicador'];
																$fecha = $sel['fecha'];
														?>

															<?php
															}
														} else {
															?>

														<?php
														}

														$query = "SELECT b.id,
									tp.nombre AS tipo_persona,
									td.nombre AS tipo_docu_identidad,
									b.num_docu,
									b.nombre,
									f.nombre AS forma_pago,
									ba.nombre AS banco,
									b.num_cuenta_bancaria,
									b.num_cuenta_cci,
									b.tipo_monto_id,
									b.monto
								FROM cont_beneficiarios b
								LEFT JOIN cont_tipo_persona tp ON b.tipo_persona_id = tp.id
								LEFT JOIN cont_tipo_docu_identidad td ON b.tipo_docu_identidad_id = td.id
								INNER JOIN cont_forma_pago f ON b.forma_pago_id = f.id
								LEFT JOIN tbl_bancos ba ON b.banco_id = ba.id
								WHERE b.status = 1 AND b.contrato_detalle_id = " . $detalle_con['id'] . " AND b.contrato_id = " . $id_contrato_arrendamiento . "";
														$sel_query = $mysqli->query($query);

														?>


														<div class="col-xs-12 col-md-12 col-lg-12">
															<div class="h4">
																<b>INFORMACIÓN DE PAGO</b>

															</div>
														</div>

														<?php
														$nro = 1;
														while ($row = $sel_query->fetch_assoc()) {
															$beneficiario_id = $row["id"];
															$ben_tipo_persona = $row["tipo_persona"];
															$ben_tipo_docu_identidad = $row["tipo_docu_identidad"];
															$ben_num_docu = $row["num_docu"];
															$ben_nombre = $row["nombre"];
															$ben_direccion = '';
															$ben_forma_pago = $row["forma_pago"];
															$ben_banco = $row["banco"];
															$ben_num_cuenta_bancaria = $row["num_cuenta_bancaria"];
															$ben_num_cuenta_cci = $row["num_cuenta_cci"];
															$ben_tipo_monto_id = $row["tipo_monto_id"];

															if ($ben_tipo_monto_id == 3) {
																$ben_monto_beneficiario = $monto_renta;
															} else {
																$ben_monto_beneficiario = $simbolo_moneda . ' ' . number_format($row["monto"], 2, '.', ',');
															}
														?>

															<div class="col-xs-12 col-md-12 col-lg-12">
																<div class="form-group">
																	<table class="table table-bordered table-hover">


																		<tr>
																			<td><b>Nombre del Banco</b></td>
																			<td><?= $ben_banco ?></td>
																			<td style="width: 75px;">
																				<a class="btn btn-success btn-xs"
																					onclick="sec_con_detalle_aden_arrend_solicitud_editar_campo_adenda('Beneficiario','cont_beneficiarios','banco_id','Nombre del Banco','select_option','<?= $ben_banco ?>','obtener_banco','<?= $beneficiario_id ?>');">
																					<span class="fa fa-edit"></span> Editar
																				</a>
																			</td>
																		</tr>

																		<tr>
																			<td><b>N° de la cuenta bancaria</b></td>
																			<td><?= $ben_num_cuenta_bancaria ?></td>
																			<td style="width: 75px;">
																				<a class="btn btn-success btn-xs"
																					onclick="sec_con_detalle_aden_arrend_solicitud_editar_campo_adenda('Beneficiario','cont_beneficiarios','num_cuenta_bancaria','N° de la cuenta bancaria','varchar','<?= $ben_num_cuenta_bancaria ?>','','<?= $beneficiario_id ?>');">
																					<span class="fa fa-edit"></span> Editar
																				</a>
																			</td>
																		</tr>
																	</table>
																</div>
															</div>
														<?php
															$nro++;
														}
														?>


														<div class="col-xs-12 col-md-12 col-lg-12">
															<br>
														</div>



														<?php
														$nro = 1;

														?>
														<?php

														?>
														<div class="col-xs-12 col-md-12 col-lg-12">
															<br>
														</div>

														<div class="col-xs-12 col-md-12 col-lg-12">
															<div class="h4"><b>FECHA DE SUSCRIPCIÓN DEL CONTRATO</b></div>
														</div>

														<div class="col-xs-12 col-md-12 col-lg-12">
															<div class="form-group">
																<table class="table table-bordered table-hover">

																	<tr>
																		<td><b>Fecha de suscripción del contrato</b></td>
																		<td><?= $contrato_fecha_suscripcion ?></td>
																		<td style="width: 75px;">
																			<a class="btn btn-success btn-xs"
																				onclick="sec_con_detalle_aden_arrend_solicitud_editar_campo_adenda('Fecha de suscripción','cont_condicion_economica','fecha_suscripcion','Fecha de suscripción del contrato','date','<?= $contrato_fecha_suscripcion ?>','','<?= $condicion_economica_id ?>');">
																				<span class="fa fa-edit"></span> Editar
																			</a>
																		</td>
																	</tr>

																</table>
															</div>
														</div>

														<div class="col-xs-12 col-md-12 col-lg-12">
															<br>
														</div>


													</div>
												</div>
											</div>
									</div>
								</div>
							</div>


						<?php
										}
						?>





						<div class="col-xs-12 col-md-12 col-lg-5" id="div_detalle_solicitud_derecha">
							<div class="panel" id="divDetalleSolicitud">
								<div class="panel-body" style="padding: 5px 10px 5px 10px;">
									<div class="panel-group no-mb" id="accordion" role="tablist" aria-multiselectable="true">
										<div class="panel">
											<div class="panel-heading" role="tab" id="browsers-this-week-heading">
												<div class="panel-title">
													<a href="#browsers-this-week" role="button" data-toggle="collapse"
														data-parent="#accordion" aria-expanded="true" aria-controls="browsers-this-week">
														Adenda - Cambios solicitados
													</a>
												</div>
											</div>

											<div id="browsers-this-week" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="browsers-this-week-heading">
												<div class="panel-body">
													<input type="hidden" id="contrato_id" value="">

													<div id="divTablaAdendas" tabindex="0">
													</div>

													<div id="divTablaAdendasArchivosAnexos">
													</div>

													<br>
													<button type="button" class="btn btn-success btn-xs btn-block" id="btnRegistrarAdenda" onclick="sec_con_detalle_aden_arrend_guardar_adenda();">
														<i class="icon fa fa-save"></i>
														<span id="demo-button-text">Guardar Solicitud de Adenda</span>
													</button>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>





						</div>



						<!-- <div class="col-md-5">
								<div class="panel">
									<div class="panel-heading">
										<div class="panel-title">DETALLE DE LA SOLICITUD DE CONTRATO INTERNO</div>
										<input type="hidden" value="'.$contrato_id.'" id="id_registro_contrato_id">
									</div>
									<div class="panel-body">
										<div id="divTablaAdendas"></div>
									</div>
								</div>
								
							</div> -->


				</div>

				</form>
			</div>
		</div>
		<!-- /PANEL: Tipo contrato -->
	</div>
</div>
</div>

<!-- INICIO MODAL NUEVO PROPIETARIO AP -->
<div id="modalNuevoProveedor" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_nuevo_proveedor_titulo_ap">Adenda - Nueva Representante Legal</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form autocomplete="off" id="frm_adenda_nuevo_proveedor">

						<div class="col-xs-12 col-md-6 col-lg-6">
							<div class="form-group">
								<div class="control-label">DNI del Representante Legal :</div>
								<input type="text" name="modal_prov_ade_int_dni_representante" id="modal_prov_ade_int_dni_representante"
									maxlength=8
									class="filtro"
									style="width: 100%; height: 30px;"
									oninput="this.value=this.value.replace(/[^0-9]/g,'');">
							</div>
						</div>

						<!--NOMBRE COMPLETO REPRESENTANTE LEGAL-->
						<div class="col-xs-12 col-md-6 col-lg-6">
							<div class="form-group">
								<div class="control-label">Nombre Completo del Representante Legal :</div>
								<input type="text" name="modal_prov_ade_int_nombre_representante" id="modal_prov_ade_int_nombre_representante" maxlength=50 class="filtro"
									style="width: 100%; height: 30px;">
							</div>
						</div>
				</div>
				<div class="row">
					<!--BANCO-->
					<div class="col-xs-12 col-md-4 col-lg-4">
						<div class="form-group">
							<div class="control-label">Banco : </div>
							<select class="form-control input_text select2" data-live-search="true"
								name="modal_prov_ade_int_prov_banco" id="modal_prov_ade_int_prov_banco" title="Seleccione el banco">
								<?php
								$banco_query = $mysqli->query("SELECT id, ifnull(nombre, '') nombre_banco
																FROM tbl_bancos
																WHERE estado = 1");
								while ($row = $banco_query->fetch_assoc()) {
								?>
									<option value="<?php echo $row["id"] ?>"><?php echo $row["nombre_banco"] ?></option>
								<?php
								}
								?>
							</select>
						</div>
					</div>

					<!--NRO CUENTA-->
					<div class="col-xs-12 col-md-4 col-lg-4">
						<div class="form-group">
							<div class="control-label">Nro Cuenta : </div>
							<input type="text" id="modal_prov_ade_int_nro_cuenta" name="modal_prov_ade_int_nro_cuenta" maxlength="50"
								style="width: 100%; height: 30px;" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
						</div>
					</div>

					<!--NRO CCI-->
					<div class="col-xs-12 col-md-4 col-lg-4">
						<div class="form-group">
							<div class="control-label">Nro CCI : </div>
							<input type="text" id="modal_prov_ade_int_nro_cci" name="modal_prov_ade_int_nro_cci" maxlength="50"
								style="width: 100%; height: 30px;" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
						</div>
					</div>
					<div class="col-xs-12 col-md-6 col-lg-6" style="display:none">
						<div class="form-group">
							<div class="control-label">
								Vigencia
							</div>
							<input type="file" name="modal_prov_ade_int_file_vigencia_nuevo_rl" accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip">
						</div>
					</div>
					<div class="col-xs-12 col-md-6 col-lg-6" style="display:none">
						<div class="form-group">
							<div class="control-label">
								DNI
							</div>
							<input type="file" name="modal_prov_ade_int_file_dni_nuevo_rl" accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip">
						</div>
					</div>

					<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_propietario_mensaje_ap" style="display: none">
						<div class="form-group">
							<div class="alert alert-danger" role="alert">
								<strong id="modal_propietario_mensaje"></strong>
							</div>
						</div>
					</div>
					</form>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
				<button type="button" onclick="sec_con_detalle_aden_prov_guardar_nuevo_representante_legal()" class="btn btn-success">
					<i class="icon fa fa-plus"></i>
					Agregar Representante Legal
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL NUEVO PROPIETARIO AP -->

<!-- INICIO MODAL SOLICITUD DE ADENDA -->
<div id="modal_adenda" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Adenda - Solicitud de edición</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form id="form_adenda" autocomplete="off">
						<input type="hidden" id="adenda_nombre_tabla">
						<input type="hidden" id="adenda_nombre_campo">
						<input type="hidden" id="adenda_tipo_valor">
						<input type="hidden" id="adenda_id_del_registro">
						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="form-group">
								<table class="table table-bordered">
									<tr>
										<td><b>Nombre del Menú:</b></td>
										<td id="adenda_nombre_menu_usuario"></td>
									</tr>
									<tr>
										<td><b>Nombre del Campo:</b></td>
										<td id="adenda_nombre_campo_usuario"></td>
									</tr>
									<tr>
										<td><b>Valor Actual:</b></td>
										<td id="adenda_valor_actual"></td>
									</tr>
									<tr>
										<td><b>Nuevo Valor:</b></td>
										<td>
											<div id="div_adenda_valor_varchar">
												<input type="text" id="adenda_valor_varchar" class="filtro txt_filter_style" style="width: 100%;">
											</div>
											<div id="div_adenda_valor_textarea">
												<textarea id="adenda_valor_textarea" class="form-control" rows="5"></textarea>
											</div>
											<div id="div_adenda_valor_int">
												<input type="text" id="adenda_valor_int" class="filtro txt_filter_style" style="width: 100%;">
											</div>
											<div id="div_adenda_valor_date">
												<input
													type="text"
													class="form-control sec_contrato_nuevo_datepicker"
													id="adenda_valor_date"
													value="<?php echo date("d-m-Y", strtotime("+1 days")); ?>"
													readonly="readonly"
													style="height: 34px;">
											</div>
											<div id="div_adenda_valor_decimal">
												<input type="text" id="adenda_valor_decimal" class="filtro txt_filter_style money" style="width: 100%;">
											</div>
											<div id="div_adenda_valor_select_option">
												<select class="form-control" id="adenda_valor_select_option" name="adenda_valor_select_option">
												</select>
											</div>

											<div id="div_adenda_solicitud_departamento" class="col-xs-12 col-md-12 col-lg-12">
												<div class="form-group">
													<div class="control-label">Departamento:</div>
													<select class="form-control select2" name="adenda_inmueble_id_departamento"
														id="adenda_inmueble_id_departamento">
													</select>
												</div>
											</div>
											<div id="div_adenda_solicitud_provincias" class="col-xs-12 col-md-12 col-lg-12">
												<div class="form-group">
													<div class="control-label">Provincia:</div>
													<select class="form-control input_text select2" name="adenda_inmueble_id_provincia"
														id="adenda_inmueble_id_provincia">
													</select>
												</div>
											</div>
											<div id="div_adenda_solicitud_distrito" class="col-xs-12 col-md-12 col-lg-12">
												<div class="form-group">
													<div class="control-label">Distrito:</div>
													<select class="form-control select2" name="adenda_inmueble_id_distrito"
														id="adenda_inmueble_id_distrito">
													</select>
												</div>
											</div>
											<input type="hidden" id="ubigeo_id_nuevo">
											<input type="hidden" id="ubigeo_text_nuevo">
										</td>
									</tr>
								</table>
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_adenda_mensaje" style="display: none">
							<div class="form-group">
								<div class="alert alert-danger" role="alert">
									<strong id="modal_adenda_mensaje"></strong>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
				<button type="button" class="btn btn-success" id="btnModalRegistrarAdenda" onclick="sec_con_detalle_aden_arrend_guardar_detalle_adenda('modalAgregar');">
					<i class="icon fa fa-plus"></i>
					<span id="demo-button-text">Agregar solicitud de edición a la Adenda</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL SOLICITUD DE ADENDA -->

<!-- INICIO MODAL NUEVO PROPIETARIO AP -->
<div id="modalNuevoContraprestacion" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_nuevo_contraprestacion_titulo_ap">Adenda - Nueva Contraprestacion</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form autocomplete="off" id="frm_nuevo_contraprestacion">
						<div class="col-xs-12 col-md-4 col-lg-6">
							<div class="form-group">
								<div class="control-label">Tipo de Moneda <span class="campo_obligatorio_v2">(*)</span>:</div>
								<select name="modal_contr_ade_int_moneda_id" id="modal_contr_ade_int_moneda_id" class="form-control select2" style="width: 100%;">
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-6">
							<div class="form-group">
								<div class="control-label">Monto Bruto <span class="campo_obligatorio_v2">(*)</span>:</div>
								<input type="text" name="modal_contr_ade_int_monto" id="modal_contr_ade_int_monto" class="filtro" style="width: 100%; height: 28px;">
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-6">
							<div class="form-group">
								<div class="control-label">IGV (18%):</div>
								<select
									name="modal_contr_ade_int_tipo_igv_id"
									id="modal_contr_ade_int_tipo_igv_id"
									class="form-control select2"
									style="width: 100%;">
									<option value="0">Seleccione</option>
									<option value="1">SI</option>
									<option value="2">No</option>
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-6">
							<div class="form-group">
								<div class="control-label" id="label_subtotal">Subtotal <span class="campo_obligatorio_v2">(*)</span>:</div>
								<input type="text" name="modal_contr_ade_int_subtotal" id="modal_contr_ade_int_subtotal" class="filtro" style="width: 100%; height: 28px;">
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-6">
							<div class="form-group">
								<div class="control-label" id="label_igv">Monto del IGV :</div>
								<input type="text" name="modal_contr_ade_int_igv" id="modal_contr_ade_int_igv" class="filtro" style="width: 100%; height: 28px;">
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-6" style="display: none;">
							<div class="form-group">
								<div class="control-label">Forma de Pago <span class="campo_obligatorio_v2">(*)</span>:</div>
								<select class="form-control select2" name="modal_contr_ade_int_forma_pago" id="modal_contr_ade_int_forma_pago" style="width: 100%;">
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-6">
							<div class="form-group">
								<div class="control-label">Tipo de Comprobante a Emitir <span class="campo_obligatorio_v2">(*)</span>:</div>
								<select
									name="modal_contr_ade_int_tipo_comprobante"
									id="modal_contr_ade_int_tipo_comprobante"
									class="form-control select2"
									style="width: 100%; height: 28px;">
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-6">
							<div class="form-group">
								<div class="control-label">Plazo de Pago <span class="campo_obligatorio_v2">(*)</span>:</div>
								<input type="text" name="modal_contr_ade_int_plazo_pago" id="modal_contr_ade_int_plazo_pago" class="filtro"
									style="width: 100%; height: 28px;">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="form-group">
								<div class="control-label">Forma de Pago<span class="campo_obligatorio_v2">(*)</span>:</div>
								<textarea name="modal_contr_ade_int_forma_pago_detallado" id="modal_contr_ade_int_forma_pago_detallado" rows="3" style="width: 100%;" placeholder="Ingrese  el detalle de la forma de pago"></textarea>
							</div>
						</div>
					</form>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
				<button type="button" class="btn btn-success" onclick="sec_con_detalle_aden_arrend_nuevo_contraprestacion()" id="btn_agregar_contraprestacion_ap">
					<i class="icon fa fa-plus"></i>
					Agregar Contraprestación
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL NUEVO PROPIETARIO AP -->

<!-- INICIO MODAL BUSCAR PROPIETARIO CA -->
<div id="modalBuscarPropietario_ca" class="modal" data-keyboard="false">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_buscar_propietario_titulo">Buscar Propietario del Inmueble</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form id="frmBuscarRemitente" autocomplete="off">

						<input type="hidden" id="modal_id_propietario_old">
						<input type="hidden" id="modal_id_persona_old">
						<input type="hidden" id="modal_buscar_propietario_tipo_solicitud">

						<div class="col-md-12 col-sm-12 col-xs-12" style="padding: 10px 15px 10px 15px;">
							<div class="form-group">
								<div class="col-md-4 col-sm-4 col-xs-12" style="padding: 0px;">
									<select name="modal_propietario_tipo_busqueda_ca" id="modal_propietario_tipo_busqueda_ca" class="form-control">
										<option value="1">Buscar por Nombre de Propietario</option>
										<option value="2">Buscar por Numero de Documento (DNI o RUC)</option>
									</select>
								</div>
								<div class="col-md-5 col-sm-5 col-xs-12">
									<input type="text" name="modal_propietario_nombre_o_numdocu_ca" id="modal_propietario_nombre_o_numdocu_ca" class="form-control" placeholder="Ingrese el nombre, despues los apellidos">
								</div>
								<div class="col-md-3 col-sm-3 col-xs-12" style="padding: 0px;">
									<button type="button" class="btn btn-success btn-sm btn-block" id="btnBuscarPropietario_ca" onclick="sec_con_detalle_aden_arrend_buscar_propietario()">
										<i class="icon fa fa-search"></i>
										<span id="demo-button-text">Buscar Propietario</span>
									</button>
								</div>
							</div>
						</div>

						<div class="col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 10px">
							<div class="form-group" id="tlbPropietariosxBusqueda_ca">
							</div>
						</div>

						<div id="divNoSeEncontroPropietario_ca" class="col-md-12 col-sm-12 col-xs-12" style="display: none; margin-bottom: 10px">
							<div class="form-group">
								<div class="alert alert-warning" role="alert">
									<div class="h4 strong">Resultados de la busqueda:</div>
									<p>
										No existe en la base de datos el propietario con <a href="#" class="alert-link" id="valoresDeBusqueda_ca"></a>. Clic en el boton Registrar nuevo propietario para registrarlo en nuestra base de datos.
									</p>
									<p>

									</p>
								</div>
							</div>
						</div>

						<div id="divRegistrarNuevoPropietario_ca" class="col-md-12 col-sm-12 col-xs-12" style="display: none;">
							<div class="form-group">
								<button type="button" onclick="sec_con_detalle_aden_arrend_propietario_modal()" class="btn btn-success btn-sm btn-block">
									<i class="icon fa fa-plus"></i>
									<span id="demo-button-text">Registrar Nuevo Propietario</span>
								</button>
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12" id="div_modal_buscar_propietario_mensaje" style="display: none">
							<div class="form-group">
								<div class="alert alert-danger" role="alert">
									<strong id="modal_buscar_propietario_mensaje"></strong>
								</div>
							</div>
						</div>

					</form>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL BUSCAR PROPIETARIO CA -->

<!-- INICIO MODAL NUEVO PROPIETARIO CA -->
<div id="modalNuevoPropietario_aa" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_nuevo_propietario_titulo_aa">Registrar Nuevo Propietario</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form autocomplete="off" id="frm_nuevo_propietario_aa">
						<input type="hidden" id="modal_nuevo_propietario_tipo_solicitud_aa">
						<input type="hidden" id="modal_propietaria_id_para_cambios_aa">
						<input type="hidden" id="modal_propietaria_id_persona_para_cambios_aa">
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Tipo de Persona:</div>
								<select
									class="form-control select2"
									name="modal_propietario_tipo_persona_aa"
									id="modal_propietario_tipo_persona_aa">
									<option value="0">- Seleccione -</option>
									<?php
									$sel_query = $mysqli->query("SELECT id, nombre
									FROM cont_tipo_persona
									WHERE estado = 1
									ORDER BY id ASC;");
									while ($sel = $sel_query->fetch_assoc()) { ?>
										<option value="<?php echo $sel["id"]; ?>"><?php echo $sel["nombre"]; ?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Nombre / Razón Social del propietario:</div>
								<input type="text" id="modal_propietario_nombre_aa" name="modal_propietario_nombre" class="filtro txt_filter_style" maxlength="50" style="width: 100%; height: 30px;">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Tipo de documento de identidad:</div>
								<select class="form-control" id="modal_propietario_tipo_docu_aa">
									<option value="0">- Seleccione -</option>
									<?php
									$sel_query = $mysqli->query("
									SELECT 
										id, 
										nombre
									FROM 
										cont_tipo_docu_identidad
									WHERE 
										estado = 1
									ORDER BY id ASC
									");
									while ($sel = $sel_query->fetch_assoc()) { ?>
										<option value="<?php echo $sel["id"]; ?>"><?php echo $sel["nombre"]; ?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_num_docu_propietario_aa" style="display: none;">
							<div class="form-group">
								<div class="control-label" id="label_num_docu_propietario_aa">Número de documento de identidad del propietario:</div>
								<input type="number" id="modal_propietario_num_docu_aa" name="modal_propietario_num_docu" class="filtro mask_dni_agente txt_filter_style" maxlength="11" style="width: 100%; height: 30px;">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_num_ruc_propietario_aa">
							<div class="form-group">
								<div class="control-label">Número de RUC del propietario:</div>
								<input type="number" id="modal_propietario_num_ruc_aa" name="modal_propietario_num_ruc" class="filtro mask_ruc_agente txt_filter_style" maxlength="11" style="width: 100%; height: 30px;">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Domicilio del propietario:</div>
								<input type="text" id="modal_propietario_direccion_aa" name="modal_propietario_direccion" class="filtro txt_filter_style" maxlength="100" style="width: 100%; height: 30px;" autocomplete="off">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_propietario_representante_legal_aa" style="display: none;">
							<div class="form-group">
								<div class="control-label">Representante Legal:</div>
								<input type="text" id="modal_propietario_representante_legal_aa" class="filtro txt_filter_style" maxlength="50" style="width: 100%; height: 30px;">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_propietario_num_partida_registral_aa" style="display: none;">
							<div class="form-group">
								<div class="control-label">N° Partida Registral de la empresa:</div>
								<input type="text" id="modal_propietario_num_partida_registral_aa" class="filtro txt_filter_style" maxlength="50" style="width: 100%; height: 30px;">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_propietario_persona_contacto_aa">
							<div class="form-group">
								<div class="control-label">Persona de contacto</div>
								<select class="form-control" id="modal_propietario_tipo_persona_contacto_aa">
									<option value="0">Seleccione la persona contacto</option>
									<option value="1">El propietario es la persona de contacto</option>
									<option value="2">El propietario no es la persona de contacto</option>
								</select>
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_propietario_contacto_nombre_aa" style="display: none;">
							<div class="form-group">
								<div class="control-label">Nombre de la persona de contacto:</div>
								<input type="text" id="modal_propietario_contacto_nombre_aa" name="modal_propietario_contacto_nombre" class="filtro txt_filter_style" maxlength="50" style="width: 100%; height: 30px;">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Teléfono de la persona de contacto:</div>
								<input type="number" id="modal_propietario_contacto_telefono_aa" name="modal_propietario_contacto_telefono" class="filtro mask_telefono_agente txt_filter_style" maxlength="9" style="width: 100%; height: 30px;" autocomplete="off">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Mail de la persona de contacto:</div>
								<input type="text" id="modal_propietario_contacto_email_aa" name="modal_propietario_contacto_email" class="filtro txt_filter_style" maxlength="50" style="width: 100%; height: 30px;">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_propietario_mensaje_aa" style="display: none">
							<div class="form-group">
								<div class="alert alert-danger" role="alert">
									<strong id="modal_propietario_mensaje"></strong>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
				<button type="button" class="btn btn-success" onclick="sec_con_detalle_aden_arrend_nuevo_propietario()">
					<i class="icon fa fa-plus"></i>
					Agregar propietario
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL NUEVO PROPIETARIO CA -->

<!-- INICIO MODAL NUEVO BENEFICIARIO -->
<div id="modalNuevoBeneficiario" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel" style="overflow: inherit;">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_beneficiario_titulo">Registrar Beneficiario</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<input type="hidden" id="modal_beneficiario_id_beneficiario_para_cambios">
					<input type="hidden" id="modal_beneficiario_tipo_solicitud">

					<form id="frmNuevoBeneficiario" autocomplete="off">
						<input type="hidden" id="modal_beneficiario_contrato_detalle_id">
						<input type="hidden" id="beneficiario_id_actual_adenda">
						<input type="hidden" id="beneficiario_id_adenda">

						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Tipo de Persona:</div>
								<select
									class="form-control select2"
									id="modal_beneficiario_tipo_persona">
									<option value="0">- Seleccione -</option>
									<?php
									$sel_query = $mysqli->query("SELECT id, nombre
									FROM cont_tipo_persona
									WHERE estado = 1
									ORDER BY id ASC;");
									while ($sel = $sel_query->fetch_assoc()) { ?>
										<option value="<?php echo $sel["id"]; ?>"><?php echo $sel["nombre"]; ?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Nombre / Razón Social del beneficiario:</div>
								<input type="text" id="modal_beneficiario_nombre" name="modal_beneficiario_nombre" class="filtro txt_filter_style" style="width: 100%; height: 30px;">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Tipo de documento de identidad:</div>
								<select
									class="form-control select2"
									id="modal_beneficiario_tipo_docu">
									<option value="0">- Seleccione -</option>
									<?php
									$sel_query = $mysqli->query("
									SELECT 
										id, nombre
									FROM
										cont_tipo_docu_identidad
									WHERE
										id IN (1 , 2) AND estado = 1
									ORDER BY id ASC
									");
									while ($sel = $sel_query->fetch_assoc()) { ?>
										<option value="<?php echo $sel["id"]; ?>"><?php echo $sel["nombre"]; ?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Número de Documento de Identidad:</div>
								<input type="text" id="modal_beneficiario_num_docu" name="modal_propietario_num_docu" class="filtro txt_filter_style" style="width: 100%; height: 30px;">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Tipo de forma de pago:</div>
								<select
									class="form-control select2"
									id="modal_beneficiario_id_forma_pago">
									<option value="0">- Seleccione -</option>
									<?php
									$sel_query = $mysqli->query("SELECT id, nombre
									FROM cont_forma_pago
									WHERE estado = 1
									ORDER BY id ASC;");
									while ($sel = $sel_query->fetch_assoc()) { ?>
										<option value="<?php echo $sel["id"]; ?>"><?php echo $sel["nombre"]; ?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_beneficiario_nombre_banco">
							<div class="form-group">
								<div class="control-label">Nombre del Banco:</div>
								<select
									class="form-control select2"
									id="modal_beneficiario_id_banco"
									title="Seleccione el banco">
									<option value="0">- Seleccione -</option>
									<?php
									$sel_query = $mysqli->query("SELECT id, nombre
									FROM tbl_bancos
									WHERE estado = 1
									ORDER BY id ASC;");
									while ($sel = $sel_query->fetch_assoc()) { ?>
										<option value="<?php echo $sel["id"]; ?>"><?php echo $sel["nombre"]; ?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_beneficiario_numero_cuenta_bancaria">
							<div class="form-group">
								<div class="control-label">N° de cuenta bancaria:</div>
								<input type="text" id="modal_beneficiario_num_cuenta_bancaria" class="filtro txt_filter_style" style="width: 100%; height: 30px;">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_beneficiario_numero_CCI">
							<div class="form-group">
								<div class="control-label">N° de CCI bancario:</div>
								<input type="text" id="modal_beneficiario_num_cuenta_cci" class="filtro txt_filter_style" style="width: 100%; height: 30px;">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Monto a depositar:</div>
								<select
									class="form-control select2"
									id="modal_beneficiario_tipo_monto">
									<option value="0">Seleccione el tipo de monto</option>
									<?php
									$sel_query = $mysqli->query("SELECT id, nombre
									FROM cont_tipo_monto_a_depositar
									WHERE status = 1
									ORDER BY id ASC;");
									while ($sel = $sel_query->fetch_assoc()) { ?>
										<option value="<?php echo $sel["id"]; ?>"><?php echo $sel["nombre"]; ?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_beneficiario_monto">
							<div class="form-group">
								<div class="control-label" id="label_beneficiario_tipo_pago">Monto:</div>
								<input type="text" id="modal_beneficiario_monto" class="filtro txt_filter_style" style="width: 100%; height: 30px;">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_beneficiario_mensaje">
							<div class="form-group">
								<div class="alert alert-danger" role="alert">
									<strong id="modal_beneficiario_mensaje"></strong>
								</div>
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_beneficiario_mensaje" style="display: none">
							<div class="form-group">
								<div class="alert alert-danger" role="alert">
									<strong id="modal_beneficiario_mensaje"></strong>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
				<button type="button" class="btn btn-success" onclick="sec_con_detalle_aden_arrend_guardar_beneficiario()">Agregar beneficiario</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL NUEVO BENEFICIARIO -->


<!-- INICIO MODAL AGREGAR INCREMENTOS -->
<div id="modal_adenda_agregar_incrementos" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_adenda_incremento_titulo">Agregar un Nuevo Incremento</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form autocomplete="off" id="frm_adenda_incremento">
						<input type="hidden" id="modal_adenda_contrato_detalle_id">
						<div class="col-xs-12 col-md-12 col-lg-12">
							<table class="table table-bordered table-striped no-mb" style="font-size:12px">
								<thead>
									<th class="text-center">Valor</th>
									<th class="text-center">Tipo Valor</th>
									<th class="text-center">Continuidad</th>
									<th class="text-center" id="titulo_adenda_incremento_a_partir">A partir del</th>
								</thead>
								<tbody>
									<tr>
										<td>
											<input
												type="hidden"
												id="contrato_adenda_incrementos_id_incremento_para_cambios"
												name="contrato_adenda_incrementos_id_incremento_para_cambios">
											<input
												type="text"
												id="contrato_adenda_incrementos_monto_o_porcentaje"
												class="filtro"
												style="width: 60px; height: 30px; text-align: right;">
										</td>
										<td>
											<select class="form-control select2" id="contrato_adenda_incrementos_en" style="width: 100%; height: 30px;">
												<option value="0">- Seleccione -</option>
												<?php
												$sel_query = $mysqli->query("
												SELECT 
													id, 
													nombre
												FROM 
													cont_tipo_pago_incrementos
												WHERE 
													estado = 1
												ORDER BY nombre ASC
												");
												while ($sel = $sel_query->fetch_assoc()) { ?>
													<option value="<?php echo $sel["id"]; ?>"><?php echo $sel["nombre"]; ?></option>
												<?php
												}
												?>
											</select>
										</td>
										<td>
											<select
												class="form-control select2"
												id="contrato_adenda_incrementos_continuidad"
												style="width: 100%; height: 30px;">
												<option value="0">- Seleccione -</option>
												<?php
												$sel_query = $mysqli->query("
												SELECT 
													id, 
													nombre
												FROM 
													cont_tipo_continuidad_pago
												WHERE 
													estado = 1 AND  id <> 3
												ORDER BY nombre ASC
												");
												while ($sel = $sel_query->fetch_assoc()) { ?>
													<option value="<?php echo $sel["id"]; ?>"><?php echo $sel["nombre"]; ?></option>
												<?php
												}
												?>
											</select>
										</td>
										<td id="td_contrato_adenda_incrementos_a_partir_de_año">
											<select
												class="form-control select2"
												id="contrato_adenda_incrementos_a_partir_de_año"
												style="width: 100%; height: 30px;">
												<option value="0">- Seleccione el año -</option>
												<option value="1">Primer año</option>
												<option value="2">Segundo año</option>
												<option value="3">Tercer año</option>
												<option value="4">Cuarto año</option>
												<option value="5">Quinto año</option>
												<option value="6">Sexto año</option>
												<option value="7">Septimo año</option>
												<option value="8">Octavo año</option>
												<option value="9">Noveno año</option>
												<option value="10">Decimo año</option>
												<option value="11">Decimoprimer año</option>
												<option value="12">Decimosegundo año</option>
												<option value="13">Decimotercero año</option>
												<option value="14">Decimocuarto año</option>
												<option value="15">Decimoquinto año</option>
												<option value="16">Decimosexto año</option>
												<option value="17">Decimoséptipo año</option>
												<option value="18">Decimoctavo año</option>
												<option value="19">Decimonoveno año</option>
												<option value="20">Vigésimo año</option>
											</select>
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</form>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
				<button
					type="button"
					class="btn btn-success"
					id="btn_adenda_agregar_incremento"
					onclick="sec_con_detalle_aden_arrend_solicitud_guardar_incremento()">
					<i class="icon fa fa-plus"></i>
					<span>Agregar el incremento</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL AGREGAR INCREMENTOS -->


<!-- INICIO MODAL AGREGAR ENMIENDA -->
<div id="modal_adenda_agregar_enmienda" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_adenda_incremento_titulo">Agregar Enmienda</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form autocomplete="off" id="frm_adenda_incremento">

						<div class="col-xs-12 col-md-12 col-lg-12">
							<table class="table table-bordered table-striped no-mb" style="font-size:12px">
								<thead>
									<th>Mes/Año</th>
									<th>Nueva renta</th>
								</thead>
								<tbody>
									<tr>
										<td>
											<input
												type="text"
												id="contrato_adenda_incrementos_monto_o_porcentaje"
												class="datepicker_enmienda"
												style="width: 100%; height: 30px; text-align: right;">
										</td>
										<td>
											<input
												type="text"
												id="contrato_adenda_incrementos_monto_o_porcentaje"
												class="filtro moneda"
												style="width: 100%; height: 30px; text-align: right;">
										</td>
									</tr>
									<tr>
										<td>
											<input
												type="text"
												id="contrato_adenda_incrementos_monto_o_porcentaje"
												class="datepicker_enmienda"
												style="width: 100%; height: 30px; text-align: right;">
										</td>
										<td>
											<input
												type="text"
												id="contrato_adenda_incrementos_monto_o_porcentaje"
												class="filtro moneda"
												style="width: 100%; height: 30px; text-align: right;">
										</td>
									</tr>
									<tr>
										<td>
											<input
												type="text"
												id="contrato_adenda_incrementos_monto_o_porcentaje"
												class="datepicker_enmienda"
												style="width: 100%; height: 30px; text-align: right;">
										</td>
										<td>
											<input
												type="text"
												id="contrato_adenda_incrementos_monto_o_porcentaje"
												class="filtro moneda"
												style="width: 100%; height: 30px; text-align: right;">
										</td>
									</tr>
									<tr>
										<td>
											<input
												type="text"
												id="contrato_adenda_incrementos_monto_o_porcentaje"
												class="datepicker_enmienda"
												style="width: 100%; height: 30px; text-align: right;">
										</td>
										<td>
											<input
												type="text"
												id="contrato_adenda_incrementos_monto_o_porcentaje"
												class="filtro moneda"
												style="width: 100%; height: 30px; text-align: right;">
										</td>
									</tr>
								</tbody>
							</table>
						</div>
					</form>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
				<button
					type="button"
					class="btn btn-success"
					id="btn_adenda_agregar_incremento"
					onclick="sec_con_detalle_aden_arrend_solicitud_guardar_incremento()">
					<i class="icon fa fa-plus"></i>
					<span>Agregar enmienda</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL AGREGAR ENMIENDA -->


<!-- INICIO MODAL AGREGAR INFLACION -->
<div id="modalAgregarInflacion" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_inflacion_titulo">Registrar Inflación</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form autocomplete="off" id="frm_inflacion">
						<input type="hidden" name="modal_if_inflacion_id" id="modal_if_inflacion_id" class="form-control text-center">
						<input type="hidden" name="modal_if_contrato_detalle_id" id="modal_if_contrato_detalle_id" class="form-control text-center">



						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Tipo Valor:</div>
								<select name="modal_if_tipo_periodicidad_id" id="modal_if_tipo_periodicidad_id" class="form-control select2" style="width: 100%;">
									<option value=""></option>
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-6 col-lg-6 block-periosidad">
							<div class="form-group">
								<div class="control-label">Periodicidad del ajuste (Ejemplo: 1 año, 6 meses.): <span class="campo_obligatorio_v2">(*)</span>:</div>
								<div class="">
									<div class="col-md-6" style="padding:0px;">
										<input type="number" id="modal_if_numero" name="modal_if_numero" class="form-control">
									</div>
									<div class="col-md-6" style="padding:0px;">
										<select name="modal_if_tipo_anio_mes" id="modal_if_tipo_anio_mes" class="form-control select2" style="width: 100%;">
											<option value=""></option>
										</select>
									</div>
								</div>
							</div>
						</div>


						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Porcentaje Añadido:</div>
								<input type="number" step="any" name="modal_if_porcentaje_anadido" id="modal_if_porcentaje_anadido" class="form-control text-right">
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Tope de Inflación:</div>
								<input type="number" name="modal_if_tope_inflacion" id="modal_if_tope_inflacion" class="form-control">
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Minimo de Inflación:</div>
								<input type="number" step="any" name="modal_if_minimo_inflacion" id="modal_if_minimo_inflacion" class="form-control text-right">
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Fecha de Aplicación:</div>
								<div class="input-group">
									<input name="modal_if_fecha" readonly id="modal_if_fecha" class="form-control sec_contrato_nuevo_adenda_datepicker text-center">
									<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="modal_if_fecha"></label>
								</div>
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Aplicación:</div>
								<select name="modal_if_aplicacion_id" id="modal_if_aplicacion_id" class="form-control select2" style="width: 100%;">
									<option value="0">- Seleccione -</option>
									<?php
									$query_ta =  "SELECT id, nombre  FROM cont_tipo_aplicacion ORDER BY id ASC";
									$sel_query_ta = $mysqli->query($query_ta);
									while ($sel = $sel_query_ta->fetch_assoc()) { ?>
										<option value="<?= $sel['id'] ?>"><?= $sel['nombre'] ?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>
					</form>
				</div>
			</div>
			<div class="modal-footer">
				<button id="btn_modal_if_agregar_agregar" type="button" class="btn btn-success" onclick="sec_con_detalle_aden_arrend_agregar_inflacion()">
					<i class="icon fa fa-plus"></i>
					<span id="demo-button-text">Agregar Inflación</span>
				</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL AGREGAR INFLACION -->

<!-- INICIO MODAL AGREGAR CUOTA EXTRAORDINARIA -->
<div id="modalAgregarCuotaExtraordinaria" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-md" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_cuota_extraordinaria_titulo">Registrar Cuota Extraordinaria</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form autocomplete="off" id="frm_inflacion">
						<input type="hidden" name="modal_ce_cuota_extraordinaria_id" id="modal_ce_cuota_extraordinaria_id" class="form-control">
						<input type="hidden" name="modal_ce_contrato_detalle_id" id="modal_ce_contrato_detalle_id" class="form-control">

						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Fecha de Aplicación:</div>
								<div class="input-group">
									<input name="modal_ce_fecha" readonly id="modal_ce_fecha" class="form-control sec_contrato_nuevo_adenda_datepicker text-center">
									<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="modal_ce_fecha"></label>
								</div>
							</div>
						</div>

						<div class="col-xs-12 col-md-5 col-lg-5">
							<div class="form-group">
								<div class="control-label">Mes:</div>
								<select name="modal_ce_mes" id="modal_ce_mes" class="form-control select2" style="width: 100%;">
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Multiplicador:</div>
								<input type="number" step="any" name="modal_ce_multiplicador" id="modal_ce_multiplicador" class="form-control text-right">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>
					</form>
				</div>
			</div>
			<div class="modal-footer">
				<button id="btn_modal_ce_agregar_agregar" type="button" class="btn btn-success" onclick="sec_con_detalle_aden_arrend_agregar_cuota_extraordinaria()">
					<i class="icon fa fa-plus"></i>
					<span id="demo-button-text">Agregar Cuota Extraordinaria</span>
				</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL AGREGAR CUOTA EXTRAORDINARIA -->

<!-- INICIO MODAL NUEVO RESPONSABLE IR -->
<div id="modalNuevoResponsableIR" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel" style="overflow: inherit;">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_responsable_ir_titulo">Registrar Responsable IR</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<input type="hidden" id="modal_responsable_ir_id_responsable_ir_para_cambios">
					<input type="hidden" id="modal_responsable_ir_tipo_solicitud">

					<form id="frmNuevoResponsableIR" autocomplete="off">
						<input type="hidden" id="modal_responsable_ir_contrato_detalle_id">
						<input type="hidden" id="responsable_id_actual_adenda">
						<input type="hidden" id="responsable_id_adenda">

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="form-group">
								<div class="control-label">Tipo de documento de identidad:</div>
								<select
									class="form-control select2"
									id="modal_responsable_ir_tipo_docu">
									<option value="0">- Seleccione -</option>
									<?php
									$sel_query = $mysqli->query("
									SELECT 
										id, nombre
									FROM
										cont_tipo_docu_identidad
									WHERE
										id IN (2) AND estado = 1
									ORDER BY id ASC
									");
									while ($sel = $sel_query->fetch_assoc()) { ?>
										<option value="<?php echo $sel["id"]; ?>"><?php echo $sel["nombre"]; ?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="form-group">
								<div class="control-label">Número de Documento:</div>
								<input type="text" id="modal_responsable_ir_nro_documento" name="modal_responsable_ir_nro_documento" class="form-control">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="form-group">
								<div class="control-label">Nombre / Razón Social:</div>
								<input type="text" id="modal_responsable_ir_nombre" name="modal_responsable_ir_nombre" class="form-control">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="form-group">
								<div class="control-label">Porcentaje:</div>
								<input type="number" step="any" id="modal_responsable_ir_num_porcentaje" class="form-control">
							</div>
						</div>


					</form>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
				<button type="button" class="btn btn-success" onclick="sec_con_detalle_aden_arrend_guardar_responsable_ir()">Agregar Responsable IR</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL NUEVO RESPONSABLE IR -->


<!-- INICIO MODAL NUEVO RESPONSABLE IR -->
<div id="modalNuevoSuministro" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel" style="overflow: inherit;">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_suministro_titulo">Registrar Responsable IR</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form id="frmNuevoSuministro" autocomplete="off">
						<input type="hidden" id="modal_suministro_tipo_servicio">
						<input type="hidden" id="modal_suministro_inmueble_id">
						<input type="hidden" id="modal_suministro_contrato_detalle_id">

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="form-group">
								<div class="control-label">N° de Suministro:</div>
								<input type="text" id="modal_suministro_nro_suministro" name="modal_suministro_nro_suministro" class="form-control">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="form-group">
								<div class="control-label">Compromiso de Pago:</div>
								<select
									class="form-control select2"
									id="modal_suministro_compromiso_pago" onchange="sec_con_detalle_aden_arrend_modal_change_tipo_compromiso()">
									<option value="0">- Seleccione -</option>
									<?php
									$sel_query = $mysqli->query("
									SELECT 
										id, nombre
									FROM
										cont_tipo_pago_servicio
									WHERE
										 estado = 1
									ORDER BY id ASC
									");
									while ($sel = $sel_query->fetch_assoc()) { ?>
										<option value="<?php echo $sel["id"]; ?>"><?php echo $sel["nombre"]; ?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12" style="display: none;" id="div_modal_suministro_monto_porcentaje">
							<div class="form-group">
								<div class="control-label">Monto/Porcentaje:</div>
								<input type="text" id="modal_suministro_monto_porcentaje" class="form-control">
							</div>
						</div>


					</form>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
				<button type="button" class="btn btn-success" onclick="sec_con_detalle_aden_arrend_agregar_suministro()">Agregar Suministro</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL NUEVO RESPONSABLE IR -->

<!-- INICIO MODAL NUEVO PROPIETARIO AP -->
<div id="modalNuevoArchivoAnexo" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-md" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_nuevo_contraprestacion_titulo_ap">Adenda - Nueva Archivo Anexo</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form autocomplete="off" id="sec_nuevo_nuevos_anexos_listado">
						<input type="hidden" id="modal_nuevo_adenda_detalle_contrato_id">
						<div class="col-md-12">
							<div class="form-group">
								<div class="control-label">Tipo de Anexo:</div>
								<select name="modal_nuevo_select_archivo_anexo" id="modal_nuevo_select_archivo_anexo" class="form-control select2" style="width: 100%;">

								</select>
							</div>
						</div>
					</form>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
				<button type="button" class="btn btn-success" onclick="sec_con_detalle_aden_anadir_archivo_arrendamiento()">
					<i class="icon fa fa-plus"></i>
					Agregar Archivo Anexo
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL NUEVO PROPIETARIO AP -->

<?php
function sec_contrato_detalle_solicitud_de_meses_a_anios_y_meses($meses)
{
	if ($meses < 12) {
		$anio_y_meses = $meses . ' meses';
	} else {
		$anio = intval($meses / 12);
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