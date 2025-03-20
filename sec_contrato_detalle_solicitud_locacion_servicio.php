<?php
$this_menu = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' LIMIT 1")->fetch_assoc();
$menu_id = $this_menu["id"];

$menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' AND sub_sec_id = '$sub_sec_id' LIMIT 1")->fetch_assoc();
$menu_consultar = isset($menu_id_consultar["id"]) ? $menu_id_consultar["id"] : 0;

if (!array_key_exists($menu_id, $usuario_permisos)) {
	echo "No tienes permisos para este recurso.";
	die();
}

$contrato_id = $_GET["id"];
$adenta_id_temporal = '';
$resolucion_id_temporal = '';

if (isset($_GET["adenda_id"])) {
	$adenta_id_temporal = $_GET["adenda_id"];
}

if (isset($_GET["resolucion_id"])) {
	$resolucion_id_temporal = $_GET["resolucion_id"];
}



$permiso_editar_contrato_firmado = false;
if ((array_key_exists($menu_consultar, $usuario_permisos) && in_array("editar_contrato_solicitud", $usuario_permisos[$menu_consultar]))) {
	$permiso_editar_contrato_firmado = true;
}
// $permiso_editar_contrato_firmado = false;
// if ((array_key_exists($menu_consultar, $usuario_permisos) && in_array("editar_contrato_firmado", $usuario_permisos[$menu_consultar]))) {
// 	$permiso_editar_contrato_firmado = true;
// }

$permiso_cambiar_estado_contrato = false;
if ((array_key_exists($menu_consultar, $usuario_permisos) && in_array("cambiar_estado_contrato", $usuario_permisos[$menu_consultar]))) {
	$permiso_cambiar_estado_contrato = true;
}

$list_emp_cont = $mysqli->query("SELECT tpg.valor FROM tbl_parametros_generales tpg WHERE tpg.codigo = 'empresa_contacto_de_contratos' AND tpg.estado = 1 LIMIT 1");
$row_emp_cont = $list_emp_cont->fetch_assoc();
$valor_empresa_contacto = isset($row_emp_cont['valor']) && $row_emp_cont['valor'] == 'Kurax' ? '(Kurax)' : '(AT)';
$pref_empresa_contacto = isset($row_emp_cont['valor']) && $row_emp_cont['valor'] == 'Kurax' ? 'Kurax' : 'AT';

$usuario_id = $login ? $login['id'] : null;
$area_id = $login ? $login['area_id'] : 0;
$cargo_id = $login ? $login['cargo_id'] : 0;
$query_sql = "
SELECT 
	c.tipo_contrato_id,
	c.etapa_id,
	p.area_id,
	co.sigla AS sigla_correlativo,
	c.codigo_correlativo,
	c.cancelado_id,
	c.fecha_aprobacion,
	c.user_created_id,
	c.abogado_id,
	CONCAT(IFNULL(pa.nombre, ''),' ',IFNULL(pa.apellido_paterno, ''),' ',IFNULL(pa.apellido_materno, '')) AS abogado,
	c.estado_aprobacion,
	c.aprobador_id,
	c.status
FROM
	cont_contrato c
	LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
	LEFT JOIN tbl_usuarios u ON c.user_created_id = u.id
	LEFT JOIN tbl_personal_apt p ON u.personal_id = p.id

	LEFT JOIN tbl_usuarios ua ON c.abogado_id = ua.id
	LEFT JOIN tbl_personal_apt pa ON ua.personal_id = pa.id
WHERE
	c.contrato_id = $contrato_id
";
$query = $mysqli->query($query_sql);
$row = $query->fetch_assoc();

if ($row["tipo_contrato_id"] == 13) {
	$continuar = true;
	$area_created_id = $row["area_id"];
	$etapa_id = $row["etapa_id"];
	$sigla_correlativo = $row["sigla_correlativo"];
	$codigo_correlativo = $row["codigo_correlativo"];
	$cancelado_id = $row["cancelado_id"];
	$fecha_aprobacion = $row["fecha_aprobacion"];
	$usuario_created_id = $row["user_created_id"];
	$abogado = $row["abogado"];
	$estado_aprobacion = $row["estado_aprobacion"];
	$aprobador_id = $row["aprobador_id"];
	$estado_contrato = $row["status"];
} else {
	echo 'En la presente página no se puede visualizar Contratos Locación';
}
// if (($area_id == 21 && $etapa_id == 1) || ($area_id == 33 && $cargo_id != 25 && $etapa_id == 5) || $permiso_editar_contrato_firmado) { // Producción
if (($area_id == 21 && $etapa_id == 1) || ($area_id == 33 && $cargo_id != 25 && $etapa_id == 5) || $permiso_editar_contrato_firmado) { // Producción
	$btn_editar_solicitud = true;
} else {
	$btn_editar_solicitud = false;
}

include '/var/www/html/sys/function_replace_invalid_caracters_contratos.php';
?>

<style type="text/css">
	.btn-default.active {
		color: #fff !important;
		background-color: #263238 !important;
		border-color: #263238 !important;
	}

	/* DIV - TAB */
	.alert-default {
		background-color: #f1f1f1;
		border-color: #898989;
		padding: 0px !important;
	}

	.div_tab {
		margin: 0px !important;
		padding: 0px !important;
	}

	.div_tab_cliente {
		/*fondo azul oscuro*/
		background-color: #395168;
		min-height: 40px;
		color: white;
		margin: 0px !important;
		padding: 0px !important;
		margin: 8px !important;
	}

	.div_tab_cliente.active {
		background-color: #659ce0;
	}

	.div_tab_cliente.naranja {
		background-color: #f0ad4e;
	}

	.div_tab_cliente_texto {
		font-weight: bold;
		font-size: 15px;
		width: 90%;
		margin: 0px !important;
		margin-top: 10px !important;
	}

	.div_tab_cliente_close {
		width: 10%;
		margin: 0px !important;
		padding: 0px !important;
	}


	.timeline {
		margin: 0 0 45px;
		padding: 0;
		position: relative;
	}

	.timeline>div {
		margin-bottom: 15px;
		margin-right: 10px;
		position: relative;
	}

	.timeline>.time-label>span {
		border-radius: 4px;
		background-color: #fff;
		display: inline-block;
		font-weight: 600;
		padding: 5px;
	}

	.bg-red,
	.bg-red>a {
		color: #fff !important;
	}

	.bg-red {
		background-color: #dc3545 !important;
	}

	.bg-blue,
	.bg-blue>a {
		color: #fff !important;
	}

	.bg-blue {
		background-color: #007bff !important;
	}

	.timeline>div>.timeline-item {
		box-shadow: 0 0 1px rgb(0 0 0 / 13%), 0 1px 3px rgb(0 0 0 / 20%);
		border-radius: .25rem;
		background-color: #fff;
		color: #495057;
		margin-left: 60px;
		/*margin-right: 15px;*/
		margin-top: 0;
		padding: 0;
		position: relative;
	}

	.timeline>div>.timeline-item>.timeline-header {
		border-bottom: 1px solid rgba(0, 0, 0, .125);
		color: #495057;
		font-size: 14px;
		line-height: 1.1;
		margin: 0;
		padding: 10px;
	}

	.timeline>div>.timeline-item>.timeline-body,
	.timeline>div>.timeline-item>.timeline-footer {
		padding: 10px;
	}

	.timeline>div>.timeline-item>.timeline-body,
	.timeline>div>.timeline-item>.timeline-footer {
		padding: 10px;
	}

	.timeline>div>.fa,
	.timeline>div>.fab,
	.timeline>div>.fad,
	.timeline>div>.fal,
	.timeline>div>.far,
	.timeline>div>.fas,
	.timeline>div>.ion,
	.timeline>div>.svg-inline--fa {
		background-color: #adb5bd;
		border-radius: 50%;
		font-size: 16px;
		height: 30px;
		left: 18px;
		line-height: 30px;
		position: absolute;
		text-align: center;
		top: 0;
		width: 30px;
	}

	.bg-green,
	.bg-green>a {
		color: #fff !important;
	}

	.bg-green {
		background-color: #28a745 !important;
	}

	.timeline>div>.timeline-item>.time {
		color: #999;
		float: right;
		font-size: 12px;
		padding: 10px;
	}

	.timeline::before {
		border-radius: .25rem;
		background-color: #dee2e6;
		bottom: 0;
		content: "";
		left: 31px;
		margin: 0;
		position: absolute;
		top: 0;
		width: 4px;
	}

	.timeline .timeline-item {
		border-left: none;
	}

	.timeline .timeline-item::before {
		content: none;
	}

	.select2-selection--single {
		height: 30px !important;
	}
</style>

<input type="hidden" id="contrato_id_temporal" value="<?php echo $contrato_id; ?>">
<input type="hidden" id="tipo_contrato_id_temporal" value="7">
<input type="hidden" id="adenta_id_temporal" value="<?php echo $adenta_id_temporal; ?>">
<input type="hidden" id="resolucion_id_temporal" value="<?php echo $resolucion_id_temporal; ?>">

<div class="tbl_goldenRace_retail_jackpots" id="div_sec_contrato_nuevo">

	<div id="loader_"></div>

	<div class="row">

		<div class="col-xs-12">
			<div class="col-md-4" style="margin-bottom: 10px;">
				<button class="btn btn-primary" onclick="sec_contrato_detalle_solicitud_btn_regresar();">
					<i class="glyphicon glyphicon-arrow-left"></i>
					Regresar
				</button>
			</div>
			<div class="col-md-8" style="margin-bottom: 10px; text-align: left;">
				<h1 class="page-title" style="margin-top: 10px;">
					<i class="icon icon-inline glyphicon glyphicon-briefcase"></i> <?php echo $etapa_id == 1 && $estado_aprobacion == 0 ? 'Solicitud de ' : ''; ?> Contrato Locación de Servicio - Código: <?php echo $sigla_correlativo;
																																																			echo $codigo_correlativo; ?>
				</h1>
			</div>
		</div>

		<div class="col-xs-12 col-md-12 col-lg-7">
			<div class="panel" id="divDetalleSolicitud">

				<div class="panel-body" style="padding: 0px; padding-top: 10px; font-size: 12px;">
					<form id="frmContratoDeArrendatario">


						<div class="w-100" style="padding-right: 5px;">
							<div id="app" class="col-xs-12 col-md-12 col-lg-12" style="padding-right: 0px; padding-left: 10px; margin-bottom: 20px;">

								<?php
								if ($etapa_id == 1 && $area_id == $area_created_id && $cancelado_id != 1 && $estado_aprobacion == 0) { ?>
									<a class="btn btn-danger btn-xs" onclick="sec_contrato_detalle_solicitudv2_cancelar_solicitud_modal(<?php echo $contrato_id; ?>);">
										<span class="fa fa-close"></span> Cancelar Solicitud
									</a>
								<?php } ?>


								<component-modal-contrato :contrato_id="<?= $contrato_id ?>"></component-modal-contrato>
								<loader :loader="loader" ref="loader"></loader>
							</div>
							<div class="text-right">
								<label>
									<input type="checkbox" name="check_collapse" id="check_collapse">
									<span id="label_check_collapse" for="check_collapse">Agrupar Secciones</span>
								</label>
							</div>
						</div>



						<div class="panel-group" id="accordionContrato" role="tablist" aria-multiselectable="true">
							<div class="panel panel-default">
								<div class="panel-heading" role="tab" id="headingDatosGenerales">
									<h4 class="panel-title">
										<a role="button" data-toggle="collapse" data-parent="#accordionContrato" href="#collapseDatosGeneral" aria-expanded="true" aria-controls="collapseDatosGeneral">
											DATOS GENERALES
										</a>
									</h4>
								</div>
								<div id="collapseDatosGeneral" class="panel-collapse-all panel-collapse collapse in" role="tabpanel" aria-labelledby="headingDatosGenerales">
									<div class="panel-body">
										<div class="w-100">
											<div id="divTablaGenerales" class="form-group">
												<?php
												$sel_query = $mysqli->query("
												SELECT
												c.empresa_suscribe_id,
												rs1.nombre AS empresa_at1, 
												c.detalle_servicio,
												c.plazo_id,
												tp.nombre AS plazo,
												p.nombre AS periodo,
												c.periodo_numero,
												concat(IFNULL(per.nombre, ''),' ', IFNULL(per.apellido_paterno, '')) AS usuario_creacion,
												per.correo AS usuario_creacion_correo,
												c.fecha_inicio,
												ar.nombre AS area_creacion,
												c.check_gerencia_interno,
												c.fecha_atencion_gerencia_interno,
												c.aprobacion_gerencia_interno,
												co.sigla AS sigla_correlativo,
												c.codigo_correlativo,
												c.observaciones,
												c.created_at,
												c.gerente_area_id,
												c.gerente_area_nombre,
												c.gerente_area_email,
												CONCAT(IFNULL(peg.nombre, ''),' ',IFNULL(peg.apellido_paterno, ''),' ',IFNULL(peg.apellido_materno, '')) AS nombre_del_gerente_area,
												CONCAT(IFNULL(pab.nombre, ''),' ',IFNULL(pab.apellido_paterno, ''),' ',IFNULL(pab.apellido_materno, '')) AS abogado,
												peg.correo AS email_del_gerente_area,

												cpc.nombre AS cargo_persona_contacto,
												cr.nombre AS cargo_responsable,
												ca.nombre AS cargo_aprobante
											FROM 
												cont_contrato c
												LEFT JOIN cont_tipo_plazo tp ON c.plazo_id = tp.id
												LEFT JOIN cont_periodo p ON c.periodo = p.id
												INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
												INNER JOIN tbl_personal_apt per ON u.personal_id = per.id
												INNER JOIN tbl_areas ar ON per.area_id = ar.id
												INNER JOIN tbl_razon_social rs1 ON c.empresa_suscribe_id = rs1.id 
												LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
												LEFT JOIN tbl_usuarios ug ON c.gerente_area_id = ug.id
												LEFT JOIN tbl_personal_apt peg ON ug.personal_id = peg.id

												LEFT JOIN tbl_usuarios uab ON c.abogado_id = uab.id
												LEFT JOIN tbl_personal_apt pab ON uab.personal_id = pab.id

												LEFT JOIN tbl_cargos AS cpc ON cpc.id = c.cargo_id_persona_contacto
												LEFT JOIN tbl_cargos AS cr ON cr.id = c.cargo_id_responsable
												LEFT JOIN tbl_cargos AS ca ON ca.id = c.cargo_id_aprobante
											WHERE c.tipo_contrato_id = 13 AND	c.contrato_id IN (" . $contrato_id . ")");
												while ($sel = $sel_query->fetch_assoc()) {
													$observaciones = $sel["observaciones"];
													$fecha_inicio = $sel["fecha_inicio"];
													$periodo = $sel["periodo"];
													$detalle_servicio = $sel["detalle_servicio"];
													$gerente_area_id = trim($sel["gerente_area_id"]);
													$cargo_aprobante = trim($sel["cargo_aprobante"]);

													$plazo_id = $sel["plazo_id"];
													$plazo = $sel["plazo"];
													$periodo_numero = $sel["periodo_numero"];
													$periodo_anio_mes = $sel["periodo"];
													$fecha_inicio_contrato = $sel["fecha_inicio"];

													if (empty($gerente_area_id)) {
														$gerente_area_nombre = trim($sel["gerente_area_nombre"]);
														$gerente_area_email = trim($sel["gerente_area_email"]);
													} else {
														$gerente_area_nombre = trim($sel["nombre_del_gerente_area"]);
														$gerente_area_email = trim($sel["email_del_gerente_area"]);
													}

												?>
													<table class="table table-bordered table-hover">
														<tr>
															<td style="width: 50%;"><b>Empresa (Locador)</b></td>
															<td><?php echo $sel["empresa_at1"]; ?></td>
															<?php if ($btn_editar_solicitud) { ?>
																<td style="width: 75px;">
																	<a class="btn btn-success btn-xs"
																		onclick="sec_con_detalle_int_editar_solicitud('Datos Generales','cont_contrato','empresa_suscribe_id','Empresa Grupo <?= $pref_empresa_contacto ?> 1','select_option','<?php echo $sel["empresa_at1"]; ?>','obtener_empresa_at');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															<?php } ?>
														</tr>
														<tr>
															<td><b>Registrado por</b></td>
															<td><?php echo $sel["usuario_creacion"]; ?></td>
															<?php if ($btn_editar_solicitud) { ?>
																<td></td>
															<?php } ?>
														</tr>

														<tr>
															<td><b>Fecha de Registro</b></td>
															<td><?php echo $sel["created_at"]; ?></td>
															<?php if ($btn_editar_solicitud) { ?>
																<td></td>
															<?php } ?>
														</tr>
													</table>
												<?php
												}

												if ($cancelado_id == 1) {
													$query_solicitud_cancelada = "
													SELECT 
														CONCAT(IFNULL(tpa.nombre, ''),' ',IFNULL(tpa.apellido_paterno, ''),	' ',	IFNULL(tpa.apellido_materno, '')) AS cancelado_por,
														c.cancelado_el,
														c.cancelado_motivo
													FROM 
														cont_contrato AS c
														LEFT JOIN tbl_usuarios tu ON c.cancelado_por_id = tu.id
														LEFT JOIN tbl_personal_apt tpa ON tu.personal_id = tpa.id
													WHERE 
														c.contrato_id = $contrato_id
													";

													$sel_query = $mysqli->query($query_solicitud_cancelada);

													if ($mysqli->error) {
														echo $mysqli->error . $query_solicitud_cancelada;
													}

													while ($sel = $sel_query->fetch_assoc()) {
														$cancelado_por = $sel['cancelado_por'];
														$cancelado_el = $sel['cancelado_el'];
														$cancelado_motivo = $sel['cancelado_motivo'];
													}
												?>

													<br>

													<table class="table table-bordered table-hover">

														<tr>
															<td style="width: 50%;"><b>Estado</b></td>
															<td style="color: red;"><b>Solicitud Cancelada</b></td>
															<?php if ($btn_editar_solicitud) { ?>
																<td style="width: 87px;"></td>
															<?php } ?>
														</tr>

														<tr>
															<td style="width: 50%;"><b>Cancelado por</b></td>
															<td><?php echo $cancelado_por; ?></td>
															<?php if ($btn_editar_solicitud) { ?>
																<td></td>
															<?php } ?>
														</tr>

														<tr>
															<td style="width: 50%;"><b>Cancelado el</b></td>
															<td><?php echo $cancelado_el; ?></td>
															<?php if ($btn_editar_solicitud) { ?>
																<td></td>
															<?php } ?>
														</tr>

														<tr>
															<td style="width: 50%;"><b>Motivo de la cancelación:</b></td>
															<td><?php echo $cancelado_motivo; ?></td>
															<?php if ($btn_editar_solicitud) { ?>
																<td></td>
															<?php } ?>
														</tr>

													</table>
												<?php } ?>
											</div>
										</div>
									</div>
								</div>
							</div>
							<?php
							$sel_query = $mysqli->query(
								"
								SELECT
									c.contrato_id,
									c.fecha_aprobacion,
									c.estado_aprobacion,
									CONCAT(IFNULL(pud.nombre, ''),' ',IFNULL(pud.apellido_paterno, ''),' ',IFNULL(pud.apellido_materno, '')) AS nombre_del_director_a_aprobar,
									CONCAT(IFNULL(puap.nombre, ''),' ',IFNULL(puap.apellido_paterno, ''),' ',IFNULL(puap.apellido_materno, '')) AS aprobado_por,
									tc.nombre cargo_aprobante
								FROM cont_contrato c
								LEFT JOIN tbl_usuarios ud ON c.aprobador_id = ud.id
								LEFT JOIN tbl_personal_apt pud ON ud.personal_id = pud.id
								LEFT JOIN tbl_usuarios uap ON c.aprobado_por = uap.id
								LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id
								LEFT JOIN tbl_cargos tc ON tc.id = c.cargo_aprobador_id
								WHERE c.contrato_id = '" . $contrato_id . "'
								"
							);

							$cantReg = mysqli_num_rows($sel_query);

							while ($sel = $sel_query->fetch_assoc()) {
								$fecha_aprobacion = $sel["fecha_aprobacion"];
								$estado_aprobacion = $sel["estado_aprobacion"];
								$nombre_del_director_a_aprobar = $sel["nombre_del_director_a_aprobar"];
								$aprobado_por = $sel["aprobado_por"];
								$cargo_aprobante = $sel["cargo_aprobante"];
							}

							if ($cancelado_id == 0) {



							?>

								<div class="panel panel-default">
									<div class="panel-heading" role="tab" id="headingAprobacionGerencia">
										<h4 class="panel-title">
											<a role="button" data-toggle="collapse" data-parent="#accordionContrato" href="#collapseAprovacionGerencia" aria-expanded="true" aria-controls="collapseAprovacionGerencia">
												APROBACIÓN DE GERENCIA
											</a>
										</h4>
									</div>
									<div id="collapseAprovacionGerencia" class="panel-collapse-all panel-collapse collapse in" role="tabpanel" aria-labelledby="headingAprobacionGerencia">
										<div class="panel-body">
											<div class="w-100">
												<div class="form-group">
													<table class="table table-bordered">

														<?php
														if (!is_null($fecha_aprobacion)) {
															if ($estado_aprobacion == 1) {
														?>
																<tr>
																	<td style="width: 50%;"><b>Aprobado por</b></td>
																	<td><?php echo $aprobado_por; ?></td>
																</tr>
																<tr>
																	<td><b>Fecha Aprobación</b></td>
																	<td><?php echo $fecha_aprobacion; ?></td>
																</tr>
															<?php
															} else {
															?>
																<tr>
																	<td style="width: 50%;"><b>Rechazado por</b></td>
																	<td><?php echo $aprobado_por; ?></td>
																</tr>
																<tr>
																	<td><b>Situación</b></td>
																	<td>Rechazado</td>
																</tr>
															<?php
															}
														} else {

															if ($cancelado_id == 1) {

															?>

																<tr>
																	<td><b>Situación</b></td>
																	<td>El contrato fue cancelado</td>
																</tr>
															<?php
															} else {
															?>

																<tr>
																	<td><b>Situación</b></td>
																	<td>Pendiente de Aprobación</td>
																</tr>
														<?php
															}
														}
														?>
													</table>
												</div>
											</div>
										</div>
									</div>
								</div>
							<?php 	}	?>

							<div class="panel panel-default">
								<div class="panel-heading" role="tab" id="headingDatosPropietarios">
									<h4 class="panel-title">
										<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordionContrato" href="#collapseDatosPropietarios" aria-expanded="true" aria-controls="collapseDatosPropietarios">
											DATOS DEL LOCATARIO
										</a>
									</h4>
								</div>
								<div id="collapseDatosPropietarios" class="panel-collapse-all panel-collapse collapse" role="tabpanel" aria-labelledby="headingDatosPropietarios">
									<div class="panel-body">

										<div class="w-100 mt-1">
											<div id="divTablaPropietarios" class="form-group">
												<?php
												$sel_query = $mysqli->query("SELECT p.id AS persona_id,
													pr.propietario_id,
													tp.nombre AS tipo_persona,
													p.tipo_docu_identidad_id,
													td.nombre AS tipo_docu_identidad,
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
												WHERE pr.status = 1 AND pr.contrato_id IN (" . $contrato_id . ")
												LIMIT 1;");
												$sel = $sel_query->fetch_assoc();
												if ($sel) {
													$tipo_docu_identidad_id = $sel["tipo_docu_identidad_id"];
													$tipo_docu_identidad = $sel["tipo_docu_identidad"];
													$num_docu_propietario = $sel["num_docu"];
													$num_ruc_propietario = $sel["num_ruc"];

													if (empty($num_ruc_propietario)) {
														$num_ruc_propietario = 'Sin asignar';
													}
												?>
													<b>PROPIETARIO</b>
													<table class="table table-bordered table-hover">
														<tr>
															<td style="width: 250px;"><b>Tipo de Persona</b></td>
															<td><?php echo $sel["tipo_persona"]; ?></td>
															<?php if ($btn_editar_solicitud) { ?>
																<td style="width: 75px;">
																	<a class="btn btn-success btn-xs"
																		onclick="sec_contrato_detalle_solicitudv2_editar_solicitud('Propietario','cont_persona','tipo_persona_id','Tipo de Persona','select_option','<?php echo $sel["tipo_persona"]; ?>','obtener_tipo_persona','<?php echo $sel["persona_id"]; ?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															<?php } ?>
														</tr>
														<tr>
															<td><b>Nombre</b></td>
															<td><?php echo $sel["nombre"]; ?></td>
															<?php if ($btn_editar_solicitud) { ?>
																<td>
																	<a class="btn btn-success btn-xs"
																		onclick="sec_contrato_detalle_solicitudv2_editar_solicitud('Propietario','cont_persona','nombre','Tipo Nombre Persona','varchar','<?php echo $sel["nombre"]; ?>','','<?php echo $sel["persona_id"]; ?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															<?php } ?>
														</tr>
														<tr>
															<td><b>Tipo de Documento de Identidad</b></td>
															<td><?php echo $tipo_docu_identidad; ?></td>
															<?php if ($btn_editar_solicitud) { ?>
																<td>
																	<a class="btn btn-success btn-xs"
																		onclick="sec_contrato_detalle_solicitudv2_editar_solicitud('Propietario','cont_persona','tipo_docu_identidad_id','Tipo de Documento de Identidad','select_option','<?php echo $tipo_docu_identidad; ?>','obtener_tipo_docu_identidad','<?php echo $sel["persona_id"]; ?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															<?php } ?>
														</tr>

														<?php if ($tipo_docu_identidad_id != "2") { ?>

															<tr>
																<td><b>Número de <?php echo $tipo_docu_identidad; ?></b></td>
																<td><?php echo $num_docu_propietario; ?></td>
																<?php if ($btn_editar_solicitud) { ?>
																	<td>
																		<a class="btn btn-success btn-xs"
																			onclick="sec_contrato_detalle_solicitudv2_editar_solicitud('Propietario','cont_persona','num_docu','Número de Documento de Identidad','varchar','<?php echo $num_docu_propietario; ?>','','<?php echo $sel["persona_id"]; ?>');">
																			<span class="fa fa-edit"></span> Editar
																		</a>
																	</td>
																<?php } ?>
															</tr>

														<?php } ?>

														<tr>
															<td><b>Número de RUC</b></td>
															<td><?php echo $num_ruc_propietario; ?></td>
															<?php if ($btn_editar_solicitud) { ?>
																<td>
																	<a class="btn btn-success btn-xs"
																		onclick="sec_contrato_detalle_solicitudv2_editar_solicitud('Propietario','cont_persona','num_ruc','Número de RUC','varchar','<?php echo $num_ruc_propietario; ?>','','<?php echo $sel["persona_id"]; ?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															<?php } ?>
														</tr>
														<tr>
															<td><b>Domicilio del propietario</b></td>
															<td><?php echo $sel["direccion"]; ?></td>
															<?php if ($btn_editar_solicitud) { ?>
																<td>
																	<a class="btn btn-success btn-xs"
																		onclick="sec_contrato_detalle_solicitudv2_editar_solicitud('Propietario','cont_persona','direccion','Domicilio del propietario','varchar','<?php echo $sel["direccion"]; ?>','','<?php echo $sel["persona_id"]; ?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															<?php } ?>
														</tr>
														<tr>
															<td><b>Representante Legal</b></td>
															<td><?php echo $sel["representante_legal"]; ?></td>
															<?php if ($btn_editar_solicitud) { ?>
																<td>
																	<a class="btn btn-success btn-xs"
																		onclick="sec_contrato_detalle_solicitudv2_editar_solicitud('Propietario','cont_persona','representante_legal','Representante Legal','varchar','<?php echo $sel["representante_legal"]; ?>','','<?php echo $sel["persona_id"]; ?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															<?php } ?>
														</tr>
														<tr>
															<td><b>N° de Partida Registral de la empresa</b></td>
															<td><?php echo $sel["num_partida_registral"]; ?></td>
															<?php if ($btn_editar_solicitud) { ?>
																<td>
																	<a class="btn btn-success btn-xs"
																		onclick="sec_contrato_detalle_solicitudv2_editar_solicitud('Propietario','cont_persona','num_partida_registral','N° de Partida Registral de la empresa','varchar','<?php echo $sel["num_partida_registral"]; ?>','','<?php echo $sel["persona_id"]; ?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															<?php } ?>
														</tr>
														<tr>
															<td><b>Persona de contacto</b></td>
															<td><?php echo $sel["contacto_nombre"]; ?></td>
															<?php if ($btn_editar_solicitud) { ?>
																<td>
																	<a class="btn btn-success btn-xs"
																		onclick="sec_contrato_detalle_solicitudv2_editar_solicitud('Propietario','cont_persona','contacto_nombre','Persona de contacto','varchar','<?php echo $sel["contacto_nombre"]; ?>','','<?php echo $sel["persona_id"]; ?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															<?php } ?>
														</tr>
														<tr>
															<td><b>Teléfono de la persona de contacto</b></td>
															<td><?php echo $sel["contacto_telefono"]; ?></td>
															<?php if ($btn_editar_solicitud) { ?>
																<td>
																	<a class="btn btn-success btn-xs"
																		onclick="sec_contrato_detalle_solicitudv2_editar_solicitud('Propietario','cont_persona','contacto_telefono','Teléfono de la persona de contacto','varchar','<?php echo $sel["contacto_telefono"]; ?>','','<?php echo $sel["persona_id"]; ?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															<?php } ?>
														</tr>
														<tr>
															<td><b>E-mail de la persona de contacto</b></td>
															<td><?php echo $sel["contacto_email"]; ?></td>
															<?php if ($btn_editar_solicitud) { ?>
																<td>
																	<a class="btn btn-success btn-xs"
																		onclick="sec_contrato_detalle_solicitudv2_editar_solicitud('Propietario','cont_persona','contacto_email','E-mail de la persona de contacto','varchar','<?php echo $sel["contacto_email"]; ?>','','<?php echo $sel["persona_id"]; ?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															<?php } ?>
														</tr>
													</table>
												<?php

												}
												?>
											</div>
										</div>



									</div>
								</div>
							</div>
							<div class="panel panel-default">
								<div class="panel-heading" role="tab" id="headingDatosAntecedentes">
									<h4 class="panel-title">
										<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordionContrato" href="#collapseDatosAntecedentes" aria-expanded="true" aria-controls="collapseDatosAntecedentes">
											CONTRATO
										</a>
									</h4>
								</div>

								<div id="collapseDatosAntecedentes" class="panel-collapse-all panel-collapse collapse" role="tabpanel" aria-labelledby="headingDatosPropietarios">
									<div class="panel-body">
										<b>ANTECEDENTES</b>

										<div class="w-100 mt-1">
											<div id="divTablaPropietarios" class="form-group">
												<?php

												$mysqli->query("CREATE TEMPORARY TABLE temp_locacion AS 
												SELECT * FROM cont_locacion WHERE idcontrato = $contrato_id");

												$query_adendas = $mysqli->query("
													SELECT id, contrato_id, fecha_de_ejecucion_del_cambio
													FROM cont_adendas
													WHERE contrato_id = " . $contrato_id . "
													AND procesado = 1
													AND anulado_id <> 1 
													ORDER BY fecha_de_ejecucion_del_cambio ASC
													");

												while ($adenda = $query_adendas->fetch_assoc()) {

													$adenda_id = $adenda["id"];

													// Obtener los detalles de la adenda
													$query_detalles = $mysqli->query("
														SELECT id, nombre_campo, tipo_valor, valor_varchar, valor_int, valor_date, valor_decimal,
															id_del_registro_a_modificar
														FROM cont_adendas_detalle
														WHERE adenda_id = " . $adenda_id . " AND status = 1 AND nombre_tabla = 'cont_locacion'
													");


													// Aplicar los cambios a la tabla temporal 
													while ($detalle = $query_detalles->fetch_assoc()) {
														// var_dump($detalle, "detalle"); 
														$nombre_campo = $detalle["nombre_campo"];
														$id_modificar = $detalle["id_del_registro_a_modificar"];

														// Determinar el tipo de valor y asignarlo correctamente   

														switch ($detalle["tipo_valor"]) {
															case 'varchar':
																$nuevo_valor = "'" . $mysqli->real_escape_string($detalle['valor_varchar']) . "'";
																break;
															case 'int':
																$nuevo_valor = (int)$detalle['valor_int'];
																break;
															case 'date':
																$nuevo_valor = "'" . $detalle['valor_date'] . "'";
																break;
															case 'decimal':
																$nuevo_valor = (float)$detalle['valor_decimal'];
																break;
															case 'select_option':
																$nuevo_valor = (int)$detalle['valor_int'];
																break;
															default:
																continue 2;
														}
														if ($nombre_campo == "ubigeo_id") {
															$nuevo_valor = "'" . $mysqli->real_escape_string($detalle['valor_varchar']) . "'";
														}
														// Ejecutar la actualización en la tabla temporal

														$updateQuery = "
										UPDATE temp_locacion 
										SET $nombre_campo = $nuevo_valor
										WHERE idlocacion = '$id_modificar'";
														$mysqli->query($updateQuery);
													}
												}



												$sel_query = $mysqli->query("SELECT 
													cl.idlocacion,
													cl.locador_descripcion,
													cl.locatario_descripcion,
													cl.nombreservicio,
													cl.locador_funciones
													FROM temp_locacion cl
													INNER JOIN cont_contrato cc on cc.contrato_id = cl.idcontrato
													WHERE cc.contrato_id IN (" . $contrato_id . ") AND cc.status = 1
												LIMIT 1;");
												$sel = $sel_query->fetch_assoc();
												if ($sel) {
													$idlocacion = $sel["idlocacion"];
													$locador_descripcion = $sel["locador_descripcion"];
													$locatario_descripcion = $sel["locatario_descripcion"];
													$nombreservicio = $sel["nombreservicio"];
													$locador_funciones = $sel["locador_funciones"];

												?>

													<table class="table table-bordered table-hover">
														<tr>
															<td style="width: 250px;"><b>Locatario (Descripción)</b></td>
															<td><?php echo $sel["locatario_descripcion"]; ?></td>
															<?php if ($btn_editar_solicitud) {
															?>
																<td style="width: 75px;">
																	<a class="btn btn-success btn-xs"
																		onclick="sec_contrato_detalle_solicitudv2_editar_solicitud('Antecedentes','cont_locacion','locatario_descripcion','Locatario','textarea','<?php echo $sel["locatario_descripcion"]; ?>','','<?php echo $idlocacion; ?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															<?php } ?>
														</tr>
														<tr>
															<td style="width: 250px;"><b>Locador (Descripción)</b></td>
															<td><?php echo $sel["locador_descripcion"];; ?></td>
															<?php if ($btn_editar_solicitud) { ?>
																<td style="width: 75px;">
																	<a class="btn btn-success btn-xs"
																		onclick="sec_contrato_detalle_solicitudv2_editar_solicitud('Antecedentes','cont_locacion','locador_descripcion','Locador','textarea','<?php echo $sel["locador_descripcion"]; ?>','','<?php echo $idlocacion; ?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															<?php } ?>
														</tr>
														<tr>
															<td style="width: 250px;"><b>Nombre del Servicio</b></td>
															<td><?php echo $sel["nombreservicio"]; ?></td>
															<?php if ($btn_editar_solicitud) { ?>
																<td style="width: 75px;">
																	<a class="btn btn-success btn-xs"
																		onclick="sec_contrato_detalle_solicitudv2_editar_solicitud('Antecedentes','cont_locacion','nombreservicio','Servicio','varchar','<?php echo $sel["nombreservicio"]; ?>','','<?php echo $sel["idlocacion"]; ?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															<?php } ?>
														</tr>

													</table>
													<br>
													<b>FUNCIONES DEL LOCADOR</b>
													<table class="table table-bordered table-hover">
														<tr>
															<td style="width: 250px;"><b>Funciones</b></td>
															<td><?php echo $sel["locador_funciones"]; ?></td>
															<?php if ($btn_editar_solicitud) { ?>
																<td style="width: 75px;">
																	<a class="btn btn-success btn-xs"
																		onclick="sec_contrato_detalle_solicitudv2_editar_solicitud('Propietario','cont_locacion','locador_funciones','Funciones','textarea','<?php echo $sel["locador_funciones"]; ?>','','<?php echo $sel["idlocacion"]; ?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															<?php } ?>
														</tr>
													</table>
												<?php

												}
												?>
											</div>
										</div>


										<br>
										<b> CONTRAPRESTACION</b>
										<div class="w-100 mt-1">
											<div id="divTablaPropietarios" class="form-group">
												<?php


												$mysqli->query("CREATE TEMPORARY TABLE temp_contraprestacion AS 
												SELECT * FROM cont_contraprestacion WHERE contrato_id = $contrato_id");

												$query_adendas = $mysqli->query("
													SELECT id, contrato_id, fecha_de_ejecucion_del_cambio
													FROM cont_adendas
													WHERE contrato_id = " . $contrato_id . "
													AND procesado = 1
													AND anulado_id <> 1 
													ORDER BY fecha_de_ejecucion_del_cambio ASC
													");

												while ($adenda = $query_adendas->fetch_assoc()) {

													$adenda_id = $adenda["id"];

													// Obtener los detalles de la adenda
													$query_detalles = $mysqli->query("
														SELECT id, nombre_campo, tipo_valor, valor_varchar, valor_int, valor_date, valor_decimal,
															id_del_registro_a_modificar
														FROM cont_adendas_detalle
														WHERE adenda_id = " . $adenda_id . " AND status = 1 AND nombre_tabla = 'cont_contraprestacion'
													");


													// Aplicar los cambios a la tabla temporal 
													while ($detalle = $query_detalles->fetch_assoc()) {
														// var_dump($detalle, "detalle"); 
														$nombre_campo = $detalle["nombre_campo"];
														$id_modificar = $detalle["id_del_registro_a_modificar"];

														// Determinar el tipo de valor y asignarlo correctamente   

														switch ($detalle["tipo_valor"]) {
															case 'varchar':
																$nuevo_valor = "'" . $mysqli->real_escape_string($detalle['valor_varchar']) . "'";
																break;
															case 'int':
																$nuevo_valor = (int)$detalle['valor_int'];
																break;
															case 'date':
																$nuevo_valor = "'" . $detalle['valor_date'] . "'";
																break;
															case 'decimal':
																$nuevo_valor = (float)$detalle['valor_decimal'];
																break;
															case 'select_option':
																$nuevo_valor = (int)$detalle['valor_int'];
																break;
															default:
																continue 2;
														}
														if ($nombre_campo == "ubigeo_id") {
															$nuevo_valor = "'" . $mysqli->real_escape_string($detalle['valor_varchar']) . "'";
														}
														// Ejecutar la actualización en la tabla temporal

														$updateQuery = "
															UPDATE temp_contraprestacion 
															SET $nombre_campo = $nuevo_valor
															WHERE id = '$id_modificar'";
														$mysqli->query($updateQuery);
													}
												}


												$sel_query = $mysqli->query("SELECT 
													    con.id as idcontraprestacion,
													 con.monto,
														cc.contrato_id
													FROM cont_locacion cl
													INNER JOIN cont_contrato cc on cc.contrato_id = cl.idcontrato
													INNER JOIN temp_contraprestacion con ON con.contrato_id = cc.contrato_id 
													WHERE cc.contrato_id IN (" . $contrato_id . ") AND cc.status = 1
												LIMIT 1;");
												$sel = $sel_query->fetch_assoc();
												if ($sel) {
													$idcontraprestacion = $sel["idcontraprestacion"];
													$monto = $sel["monto"];

												?>
													<table class="table table-bordered table-hover">
														<tr>
															<td style="width: 250px;"><b>Monto (S/.)</b></td>
															<td><?php echo $sel["monto"]; ?></td>
															<?php if ($btn_editar_solicitud) { ?>
																<td style="width: 75px;">
																	<a class="btn btn-success btn-xs"
																		onclick="sec_contrato_detalle_solicitudv2_editar_solicitud('Contraprestación','cont_contraprestacion','monto','Monto','varchar','<?php echo $sel["monto"]; ?>','obtener_tipo_persona','<?php echo $sel["idcontraprestacion"]; ?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															<?php } ?>
														</tr>

													</table>


												<?php

												}
												?>
											</div>
										</div>
										<br>
										<b> PLAZOS
										</b>
										<div class="w-100 mt-1">
											<div id="divTablaPropietarios" class="form-group">
												<?php




												$mysqli->query("CREATE TEMPORARY TABLE temp_contrato AS 
												SELECT * FROM cont_contrato WHERE contrato_id = $contrato_id");

												$query_adendas = $mysqli->query("
										SELECT id, contrato_id, fecha_de_ejecucion_del_cambio
										FROM cont_adendas
										WHERE contrato_id = " . $contrato_id . "
										AND procesado = 1
										AND anulado_id <> 1 
										ORDER BY fecha_de_ejecucion_del_cambio ASC
										");

												while ($adenda = $query_adendas->fetch_assoc()) {

													$adenda_id = $adenda["id"];

													// Obtener los detalles de la adenda
													$query_detalles = $mysqli->query("
										SELECT id, nombre_campo, tipo_valor, valor_varchar, valor_int, valor_date, valor_decimal,
											id_del_registro_a_modificar
										FROM cont_adendas_detalle
										WHERE adenda_id = " . $adenda_id . " AND status = 1 AND nombre_tabla = 'cont_contrato'
									");


													// Aplicar los cambios a la tabla temporal 
													while ($detalle = $query_detalles->fetch_assoc()) {
														// var_dump($detalle, "detalle"); 
														$nombre_campo = $detalle["nombre_campo"];
														$id_modificar = $detalle["id_del_registro_a_modificar"];

														// Determinar el tipo de valor y asignarlo correctamente   

														switch ($detalle["tipo_valor"]) {
															case 'varchar':
																$nuevo_valor = "'" . $mysqli->real_escape_string($detalle['valor_varchar']) . "'";
																break;
															case 'int':
																$nuevo_valor = (int)$detalle['valor_int'];
																break;
															case 'date':
																$nuevo_valor = "'" . $detalle['valor_date'] . "'";
																break;
															case 'decimal':
																$nuevo_valor = (float)$detalle['valor_decimal'];
																break;
															case 'select_option':
																$nuevo_valor = (int)$detalle['valor_int'];
																break;
															default:
																continue 2;
														}
														if ($nombre_campo == "ubigeo_id") {
															$nuevo_valor = "'" . $mysqli->real_escape_string($detalle['valor_varchar']) . "'";
														}
														// Ejecutar la actualización en la tabla temporal

														$updateQuery = "
										UPDATE temp_contrato 
										SET $nombre_campo = $nuevo_valor
										WHERE contrato_id = '$id_modificar'";

														$mysqli->query($updateQuery);
													}
												}

												$sel_query = $mysqli->query("SELECT 
													    CAST(cl.fechainicio AS DATE) AS fechainicio, 
    													CAST(cl.fechafin AS DATE) AS fechafin,
														cc.fecha_suscripcion_contrato,
														cc.contrato_id
													FROM cont_locacion cl
													INNER JOIN temp_contrato cc on cc.contrato_id = cl.idcontrato
													WHERE cc.contrato_id IN (" . $contrato_id . ") AND cc.status = 1
												LIMIT 1;");
												$sel = $sel_query->fetch_assoc();
												if ($sel) {
													$fechainicio = $sel["fechainicio"];
													$fechafin = $sel["fechafin"];
													$contrato_fecha_suscripcion = $sel["fecha_suscripcion_contrato"];
													$contrato_id = $sel["contrato_id"];

												?>
													<table class="table table-bordered table-hover">
														<tr>
															<td style="width: 250px;"><b>Fecha de Inicio</b></td>
															<td><?php echo $sel["fechainicio"]; ?></td>
															<?php if ($btn_editar_solicitud) { ?>
																<td style="width: 75px;">
																	<a class="btn btn-success btn-xs"
																		onclick="sec_contrato_detalle_solicitudv2_editar_solicitud('Propietario','cont_persona','tipo_persona_id','Tipo de Persona','select_option','<?php echo $sel["fechainicio"]; ?>','obtener_tipo_persona','<?php echo $sel["persona_id"]; ?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															<?php } ?>
														</tr>
														<tr>
															<td style="width: 250px;"><b>Fecha de Finalización</b></td>
															<td><?php echo $sel["fechafin"]; ?></td>
															<?php if ($btn_editar_solicitud) { ?>
																<td style="width: 75px;">
																	<a class="btn btn-success btn-xs"
																		onclick="sec_contrato_detalle_solicitudv2_editar_solicitud('Propietario','cont_persona','tipo_persona_id','Tipo de Persona','select_option','<?php echo $sel["fechafin"]; ?>','obtener_tipo_persona','<?php echo $sel["persona_id"]; ?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															<?php } ?>
														</tr>
													</table>
													<br>
													<b>FECHA DE SUSCRIPCIÓN DEL CONTRATO</b>
													<div class="w-100">
														<div class="form-group">
															<table class="table table-bordered table-hover">
																<tr>
																	<td style="width: 250px;"><b>Fecha de suscripción</b></td>
																	<td><?php echo $contrato_fecha_suscripcion; ?></td>
																	<?php if ($btn_editar_solicitud) { ?>
																		<td style="width: 75px;">
																			<a class="btn btn-success btn-xs"
																				onclick="sec_contrato_detalle_solicitudv2_editar_solicitud('Fecha de suscripción','cont_contrato','fecha_suscripcion_contrato','Fecha de suscripción del contrato','date','<?php echo $contrato_fecha_suscripcion; ?>','','<?= $contrato_id ?>');">
																				<span class="fa fa-edit"></span> Editar
																			</a>
																		</td>
																	<?php } ?>
																</tr>
															</table>
														</div>
													</div>

												<?php

												}
												?>
											</div>
										</div>
									</div>


								</div>
							</div>

						</div>



					</form>

				</div>
				<!-- /Panel Body -->

			</div>
			<!-- /PANEL: Horizontal Form -->

			<div class="panel" id="divAnexos" style="display: none;">

				<!-- Panel Heading -->
				<div class="panel-heading">

					<!-- Panel Title -->
					<div class="panel-title" id="divAnexoHeadingValue">TEMPORAL</div>
					<!-- /Panel Title -->

				</div>
				<!-- /Panel Heading -->

				<div class="panel-body" style="padding: 10px 0px 10px 0px;">
					<div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center; margin-bottom: 5px; display:none;" id="divVerPdfFullPantalla">
						<button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#exampleModalPreview" style="background-color:#7dc623;border-color: #aaf152;">
							<i class="fa fa-arrows-alt"></i> Ver documento en toda la Pantalla
						</button>
					</div>

					<div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center;margin-bottom: 5px; display:none;" id="divVisorPdfPrincipal">
					</div>

					<div class="col-xs-9 col-md-9 col-sm-9" style="text-align: center;margin-bottom: 5px; display:none;" id="divVerImagenFullPantalla">
						<button type="button" class="btn btn-block btn-block btn-primary" id="sec_contrato_detalle_solicitud_ver_imagen_full_pantalla" style="background-color:#7dc623;">
							<i class="fa fa-arrows-alt"></i> Ver imagen en toda la Pantalla
						</button>
					</div>

					<div class="col-xs-3 col-md-3 col-sm-3" style="text-align: center;margin-bottom: 5px; display:none;" id="divDescargarImagen">
						<a class="btn btn-block btn-info" id="sec_contrato_detalle_solicitud_descargar_imagen">
							<i class="fa fa-cloud-download"></i> Descargar
						</a>
					</div>

					<div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center;margin-bottom: 5px; display:none;" id="divVisorImagen">
						<img src="" class="img-responsive" style="border: 1px solid;">
					</div>
				</div>

			</div>

		</div>


		<?php

		$query_detalle_contrato = "SELECT d.id, d.codigo, ce.condicion_economica_id, ce.contrato_id, ce.plazo_id, CAST(loc.fechainicio AS DATE) as fecha_inicio, CAST(loc.fechafin AS DATE) as
		fecha_fin,
		c.fecha_suscripcion_contrato as fecha_suscripcion, 
		ce.renovacion_automatica 
				FROM cont_contrato_detalle AS d
				LEFT JOIN cont_condicion_economica AS ce ON ce.contrato_detalle_id = d.id
				RIGHT JOIN cont_contrato AS c ON c.contrato_id = d.contrato_id
				LEFT JOIN cont_locacion as loc ON loc.idcontrato = c.contrato_id
				WHERE c.status = 1   
			AND d.contrato_id = " . $contrato_id . " ORDER BY d.id ASC LIMIT 1";

		?>

		<div class="col-xs-12 col-md-12 col-lg-5">
			<div class="panel" id="divDetalleSolicitud">
				<div class="panel-body" style="padding: 5px 10px 5px 10px;">
					<div class="panel-group no-mb" id="accordion" role="tablist" aria-multiselectable="true">
						<!-- INICIO PANEL DOCUMENTOS -->
						<?php

						if (!($etapa_id == 1 && $estado_aprobacion != 1)) { ?>

							<div class="panel">
								<div class="panel-heading" role="tab" id="browsers-this-week-heading">
									<div class="panel-title">
										<a href="#browsers-this-week" role="button" data-toggle="collapse"
											data-parent="#accordion" aria-expanded="true" aria-controls="browsers-this-week">
											Documentos
										</a>
									</div>
								</div>

								<div id="browsers-this-week" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="browsers-this-week-heading">
									<div class="panel-body">
										<?php
										$list_detalle_contrato = $mysqli->query($query_detalle_contrato);
										$sel_cont_det = $list_detalle_contrato->fetch_assoc();
										if ($sel_cont_det) {
											$contrato_detalle_id = $sel_cont_det['id'];
											$contrato_detalle_codigo = $sel_cont_det['codigo'];

										?>
											<table class="table table-responsive table-hover" style="font-size: 10px;">
												<thead style="background: none;">

													<tr style="text-transform: none;">
														<th class="text-center">Nombre del Documento</th>

														<?php

														if (!(array_key_exists($menu_consultar, $usuario_permisos) && in_array("request", $usuario_permisos[$menu_consultar]))) {
														?>
															<th class="text-center">Operación</th>
															<?php
														} else {
															if (($area_id == 33 and $cargo_id != 25) || $usuario_created_id == $login['id'] || $permiso_editar_contrato_firmado) {
															?>
																<th class="text-center">Operación</th>
															<?php
															}
														}

														if ($area_id == 33) {
															?>
															<th class="text-center">Visualizar</th>
														<?php
														} else if ((array_key_exists($menu_consultar, $usuario_permisos) && in_array("request", $usuario_permisos[$menu_consultar]))) {
														?>
															<th class="text-center">Visualizar</th>
															<?php
														} else {
															if ($area_id != 33) {
															?>
																<th class="text-center">Visualizar</th>
														<?php
															}
														}

														?>

														<?php
														if ((array_key_exists($menu_consultar, $usuario_permisos) && in_array("eliminar_anexo", $usuario_permisos[$menu_consultar]))) {
														?>
															<th align="center"></th>
														<?php
														}
														?>

													</tr>
												</thead>
												<tbody>
													<tr style="text-transform: none;">
														<td>Solicitud de Contratos de Locacion</td>
														<td colspan="2">
															<a onclick="sec_contrato_detalle_solicitudv2_ver_documento_en_visor('','', 'html', '');" class="btn btn-primary btn-xs btn-block" data-toggle="tooltip" data-placement="top"><span class="fa fa-eye"></span> Ir sección general del contrato</a>
														</td>
													</tr>

													<?php
													// INICIO CONTRATO FIRMADO
													if ((array_key_exists($menu_consultar, $usuario_permisos) && in_array("ver_contrato_firmado", $usuario_permisos[$menu_consultar]))) {


														$sel_contrato_firmado = $mysqli->query(
															"
												SELECT 
													archivo_id,
													contrato_id,
													tipo_archivo_id,
													nombre,
													extension,
													ruta,
													size,
													user_created_id,
													status,
													created_at
												FROM
													cont_archivos
												WHERE
													tipo_archivo_id = 16
													AND status = 1
													AND contrato_detalle_id = " . $contrato_detalle_id . "
													AND contrato_id = " . $contrato_id
														);
														$num_rows = mysqli_num_rows($sel_contrato_firmado);
													?>

														<tr style="text-transform: none;">
															<td>Contrato Firmado</td>


															<?php
															if ($num_rows == 0) {
															?>

																<?php
																if ((array_key_exists($menu_consultar, $usuario_permisos) && in_array("subir_contrato_firmado", $usuario_permisos[$menu_consultar])) && $etapa_id == 5) {
																?>
																	<td colspan="1">
																		<a
																			onclick="moda_subir_archivo_req_solicitud_arrendamiento('Contrato Firmado','','16','<?= $contrato_detalle_id ?>');"
																			class="btn btn-success btn-xs btn-block"
																			data-toggle="tooltip"
																			data-placement="top">
																			<span class="fa fa-upload"></span> Subir
																		</a>
																	</td>
																	<td colspan="1">
																		<a class="btn btn-warning btn-xs btn-block" data-toggle="tooltip" data-placement="top"><span class="fa fa-eye-slash"></span> Incompleto</a>
																	</td>
																<?php
																} else {
																?>
																	<td colspan="2">
																		<a class="btn btn-warning btn-xs btn-block" data-toggle="tooltip" data-placement="top"><span class="fa fa-eye-slash"></span> Incompleto</a>
																	</td>
																<?php
																}
																?>



															<?php
															} else if ($num_rows > 0) {
																$row = $sel_contrato_firmado->fetch_assoc();
																$ruta = str_replace("/var/www/html", "", $row["ruta"]);
															?>
																<td>
																	<a
																		onclick="moda_reemplazar_archivo_req_solicitud_arrendamiento('<?= $row['nombre']; ?>','<?= $row['archivo_id']; ?>','<?= $row['tipo_archivo_id']; ?>','<?= $contrato_detalle_id ?>');"
																		class="btn btn-success btn-xs btn-block"
																		data-toggle="tooltip"
																		data-placement="top">
																		<span class="fa fa-upload"></span> Reemplazar
																	</a>
																</td>
																<td>
																	<a
																		onclick="sec_contrato_detalle_solicitudv2_ver_documento_en_visor('<?php echo $ruta; ?>','<?php echo trim($row["nombre"]); ?>','<?php echo trim($row["extension"]); ?>','CONTRATO FIRMADO');"
																		class="btn btn-success btn-xs btn-block"
																		data-toggle="tooltip"
																		data-placement="top">
																		<span class="fa fa-eye"></span> Ver Contrato Firmado
																	</a>
																</td>

															<?php
															}
															?>
															</td>
														</tr>
													<?php
													}
													// FIN CONTRATO FIRMADO
													?>

												</tbody>
											</table>
										<?php
										}
										?>

										<?php
										// INICIO ADENDA FIRMADA
										if ((array_key_exists($menu_consultar, $usuario_permisos) && in_array("ver_adenda_firmado", $usuario_permisos[$menu_consultar]) && (($etapa_id == 1 && $estado_aprobacion == 1) || $etapa_id == 5)) || true) {
										?>
											<table class="table table-responsive table-hover" style="font-size: 10px;">
												<thead style="background: none;">
													<tr>
														<th colspan="3" class="text-center">ADENDAS

															<!-- <button onclick="fnc_modal_nuevo_documento_adenda();" style="float: right" class="btn btn-xs btn-primary" type="button">Nueva Adenda</button> -->
														</th>
													</tr>
													<tr style="text-transform: none;">
														<th class="text-center">Nombre del Documento</th>

														<?php

														if (!(array_key_exists($menu_consultar, $usuario_permisos) && in_array("request", $usuario_permisos[$menu_consultar]))) {
														?>
															<th class="text-center">Operación</th>
															<?php
														} else {
															if (($area_id == 33 and $cargo_id != 25) || $usuario_created_id == $login['id']) {
															?>
																<th class="text-center">Operación</th>
															<?php
															}
														}

														if ($area_id == 33) {
															?>
															<th class="text-center">Visualizar</th>
														<?php
														} else if ((array_key_exists($menu_consultar, $usuario_permisos) && in_array("request", $usuario_permisos[$menu_consultar]))) {
														?>
															<th class="text-center">Visualizar</th>
															<?php
														} else {
															if ($area_id != 33) {
															?>
																<th class="text-center">Visualizar</th>
														<?php
															}
														}

														?>

														<?php
														if ((array_key_exists($menu_consultar, $usuario_permisos) && in_array("eliminar_anexo", $usuario_permisos[$menu_consultar])) || (array_key_exists($menu_consultar, $usuario_permisos) && in_array("eliminar_adenda_escision", $usuario_permisos[$menu_consultar]))) {
														?>
															<th align="center"></th>
														<?php
														}
														?>

													</tr>
												</thead>
												<tbody>
													<?php
													$sel_contrato_firmado = $mysqli->query(
														"
										SELECT 
											ar.archivo_id,
											ar.contrato_id,
											ar.tipo_archivo_id,
											ar.nombre,
											ar.extension,
											ar.ruta,
											ar.size,
											ar.user_created_id,
											ar.status,
											ar.created_at,
											ad.codigo,
											ca.id as adenda_escision_id,
											CASE
											    WHEN ar.tipo_archivo_id = 17 THEN 'Adenda Firmada'
											    WHEN ar.tipo_archivo_id = 154 THEN 'Adenda de Escisión Firmada'
											    ELSE ''
											END AS nombre_adenda
										FROM
											cont_archivos AS ar
											LEFT JOIN cont_adendas AS ad ON ad.archivo_id = ar.archivo_id
											LEFT JOIN cont_contrato_escision AS ca ON ca.archivo_id = ar.archivo_id
										WHERE
											ar.tipo_archivo_id IN (17,154)
											AND ar.status = 1
											AND ar.contrato_id = " . $contrato_id .
															" ORDER BY ar.tipo_archivo_id, ad.codigo ASC"
													);
													$num_rows = mysqli_num_rows($sel_contrato_firmado);
													if ($num_rows > 0) {
														while ($row = $sel_contrato_firmado->fetch_assoc()) {
															$ruta = str_replace("/var/www/html", "", $row["ruta"]);
													?>
															<tr style="text-transform: none;">
																<td><?= $row['nombre_adenda'] ?> <?= !empty($row['codigo']) ? 'N° ' . $row['codigo'] : '' ?></td>
																<td>
																	<a
																		onclick="moda_reemplazar_archivo_req_solicitud_arrendamiento('<?= $row['nombre']; ?>','<?= $row['archivo_id']; ?>','<?= $row['tipo_archivo_id']; ?>','<?= $contrato_detalle_id ?>');"
																		class="btn btn-success btn-xs btn-block"
																		data-toggle="tooltip"
																		data-placement="top">
																		<span class="fa fa-upload"></span> Reemplazaraaa
																	</a>
																</td>
																<td>
																	<a
																		onclick="sec_contrato_detalle_solicitudv2_ver_documento_en_visor('<?php echo $ruta; ?>','<?php echo trim($row["nombre"]); ?>','<?php echo trim($row["extension"]); ?>','ADENDA FIRMADA');"
																		class="btn btn-success btn-xs btn-block"
																		data-toggle="tooltip"
																		data-placement="top">
																		<span class="fa fa-eye"></span> Ver <?= $row['nombre_adenda'] ?>
																	</a>
																</td>
																<?php
																if ((array_key_exists($menu_consultar, $usuario_permisos) && in_array("eliminar_anexo", $usuario_permisos[$menu_consultar])) && empty($row['codigo']) && $row['tipo_archivo_id'] != '154') { // 154 => Adendas de Escisión
																?>
																	<td>
																		<a
																			onclick="sec_contrato_detalle_solicitudv2_ver_eliminar_anexo('<?= $row['archivo_id']; ?>')"
																			class="btn btn-danger btn-xs btn-block"
																			data-toggle="tooltip"
																			title="Eliminar Adenda"
																			data-placement="top">
																			<span class="fa fa-trash"></span>
																		</a>
																	</td>
																<?php
																} else if ((array_key_exists($menu_consultar, $usuario_permisos) && in_array("eliminar_adenda_escision", $usuario_permisos[$menu_consultar])) && $row['tipo_archivo_id'] == '154') { // 154 => Adendas de Escisión
																?>
																	<td>
																		<a
																			onclick="sec_contrato_detalle_solicitudv2_eliminar_adenda_escision(<?= $row['adenda_escision_id'] ?>);"
																			class="btn btn-danger btn-xs btn-block"
																			data-toggle="tooltip"
																			title="Eliminar Adenda de Escisión"
																			data-placement="top">
																			<span class="fa fa-trash"></span>
																		</a>
																	</td>
																<?php
																}
																?>
															</tr>

														<?php
														}
													} else {
														?>
														<td colspan="3" class="text-center">
															No existen registros de adendas
														</td>

														<?php


														?>
													<?php
													}
													?>
													<?php
													$sel_contrato_firmado = $mysqli->query(
														"
										SELECT 
											ar.archivo_id,
											ar.contrato_id,
											ar.tipo_archivo_id,
											ar.nombre,
											ar.extension,
											ar.ruta,
											ar.size,
											ar.user_created_id,
											ar.status,
											ar.created_at,
											ad.codigo
										FROM
											cont_archivos AS ar
											LEFT JOIN cont_adendas AS ad ON ad.archivo_id = ar.archivo_id
										WHERE
											ar.tipo_archivo_id = 152
											AND ar.status = 1
											AND ar.contrato_id = " . $contrato_id .
															" ORDER BY ar.archivo_id ASC"
													);
													$num_rows = mysqli_num_rows($sel_contrato_firmado);
													if ($num_rows > 0) {
														$cont_adendas_archivos = 0;
														while ($row = $sel_contrato_firmado->fetch_assoc()) {
															$cont_adendas_archivos++;
															$ruta = str_replace("/var/www/html", "", $row["ruta"]);
													?>
															<tr style="text-transform: none;">
																<td>Archivos adendas <?= !empty($row['codigo']) ? 'N° ' . $row['codigo'] : '' ?></td>
																<td>
																	<a
																		onclick="moda_reemplazar_archivo_req_solicitud_arrendamiento('<?= $row['nombre']; ?>','<?= $row['archivo_id']; ?>','<?= $row['tipo_archivo_id']; ?>','<?= $contrato_detalle_id ?>');"
																		class="btn btn-success btn-xs btn-block"
																		data-toggle="tooltip"
																		data-placement="top">
																		<span class="fa fa-upload"></span> Reemplazar
																	</a>
																</td>
																<td>
																	<a
																		onclick="sec_contrato_detalle_solicitudv2_ver_documento_en_visor('<?php echo $ruta; ?>','<?php echo trim($row["nombre"]); ?>','<?php echo trim($row["extension"]); ?>','ADENDA FIRMADA');"
																		class="btn btn-success btn-xs btn-block"
																		data-toggle="tooltip"
																		data-placement="top">
																		<span class="fa fa-eye"></span> Ver Adenda Firmada
																	</a>
																</td>
																<?php
																if ((array_key_exists($menu_consultar, $usuario_permisos) && in_array("eliminar_anexo", $usuario_permisos[$menu_consultar])) && empty($row['codigo']) && $row['tipo_archivo_id'] != '154') { // 154 => 
																?>
																	<td>
																		<a
																			onclick="sec_contrato_detalle_solicitudv2_ver_eliminar_anexo('<?= $row['archivo_id']; ?>')"
																			class="btn btn-danger btn-xs btn-block"
																			data-toggle="tooltip"
																			title="Eliminar Adenda"
																			data-placement="top">
																			<span class="fa fa-trash"></span>
																		</a>
																	</td>
																<?php
																}
																?>
															</tr>

													<?php
														}
													}
													?>
												</tbody>
											</table>

										<?php
										}
										// FIN ADENDA FIRMADA
										?>

									</div>
								</div>
							</div>
						<?php } ?>

						<!-- FIN PANEL DOCUMENTOS -->

						<!-- INICIO CAMBIOS EN LA SOLICITUD -->
						<?php
						$sel_query = $mysqli->query("
						SELECT 
							a.nombre_tabla,
							a.valor_original,
							a.nombre_campo,
							a.nombre_menu_usuario,
							a.nombre_campo_usuario,
							a.tipo_valor,
							a.valor_varchar,
							a.valor_int,
							a.valor_date,
							a.valor_decimal,
							a.valor_select_option,
							a.valor_id_tabla,
							a.created_at AS fecha_del_cambio,
							CONCAT(IFNULL(p.nombre, ''), ' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS usuario_que_realizo_cambio,
							ar.nombre AS area,
							CONCAT('#',d.codigo) AS codigo
						FROM
							cont_auditoria a
								INNER JOIN
							tbl_usuarios u ON a.user_created_id = u.id
								INNER JOIN
							tbl_personal_apt p ON u.personal_id = p.id
								INNER JOIN
							tbl_areas ar ON p.area_id = ar.id
								LEFT JOIN 
							cont_contrato_detalle d ON d.id = a.contrato_detalle_id 
						WHERE
							a.status = 1 AND a.contrato_id = " . $contrato_id . "
						ORDER BY a.id DESC");
						$row_count = $sel_query->num_rows;
						if ($row_count > 0) {
						?>

							<div class="panel">
								<div class="panel-heading" role="tab" id="browsers-cambios-heading">
									<div class="panel-title">
										<a href="#browsers-cambios" class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" aria-expanded="true" aria-controls="browsers-cambios">
											Cambios (Auditoría)
										</a>
									</div>
								</div>

								<div id="browsers-cambios" class="panel-collapse collapse" role="tabpanel" aria-labelledby="browsers-cambios-heading">
									<div class="panel-body" style="width: 100%; height: 350px; overflow: scroll;">
										<?php
										while ($sel = $sel_query->fetch_assoc()) {
											$tipo_valor = $sel["tipo_valor"];
											if ($tipo_valor == 'varchar') {
												$nuevo_valor = $sel['valor_varchar'];
											} else if ($tipo_valor == 'int') {
												$nuevo_valor = $sel['valor_int'];
											} else if ($tipo_valor == 'date') {
												$nuevo_valor = $sel['valor_date'];
											} else if ($tipo_valor == 'decimal') {
												$nuevo_valor = $sel['valor_decimal'];
											} else if ($tipo_valor == 'select_option') {
												$nuevo_valor = $sel['valor_select_option'];
											}
										?>

											<p><b>Cambio N° <?php echo $row_count; ?></b></p>

											<table class="table table-responsive table-hover no-mb" style="font-size: 12px;">
												<tbody>


													<tr style="text-transform: none;">
														<td><b>Nombre del menú</b></td>
														<td>
															<?php echo $sel["nombre_menu_usuario"]; ?>
														</td>
													</tr>
													<tr style="text-transform: none;">
														<td><b>Nombre del campo</b></td>
														<td>
															<?php echo $sel["nombre_campo_usuario"]; ?>
														</td>
													</tr>
													<tr style="text-transform: none;">
														<td><b>Valor anterior</b></td>
														<td style="white-space: pre-line;">
															<?php echo $sel["valor_original"]; ?>
														</td>
													</tr>
													<tr style="text-transform: none;">
														<td><b>Nuevo valor</b></td>
														<td style="white-space: pre-line;">
															<?php echo $nuevo_valor; ?>
														</td>
													</tr>
													<tr style="text-transform: none;">
														<td><b>Usuario que realizó el cambio</b></td>
														<td>
															<?php echo $sel["usuario_que_realizo_cambio"]; ?>
														</td>
													</tr>
													<tr style="text-transform: none;">
														<td><b>Fecha del cambio</b></td>
														<td>
															<?php echo $sel["fecha_del_cambio"]; ?>
														</td>
													</tr>
												</tbody>
											</table>
											<br>
										<?php
											$row_count--;
										} ?>
									</div>
								</div>
							</div>
						<?php
						}
						?>
						<!-- FIN PANEL CAMBIOS EN LA SOLICITUD -->


						<!-- INICIO PANEL ADENDAS -->
						<?php
						$numero_adenda = 0;

						$sel_query = $mysqli->query("
						SELECT 
							a.id,
							a.codigo,
							a.procesado,
							a.anulado_id,
							a.created_at AS fecha_solicitud,
							DATE_FORMAT(a.fecha_de_ejecucion_del_cambio,'%d-%m-%Y') as fecha_aplicacion,
							a.estado_solicitud_id,
							concat(IFNULL(p.nombre, ''), ' ',  IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS solicitante,
							ar.nombre AS area,
							a.cancelado_id,
							CONCAT(IFNULL(tpa3.nombre, ''),' ',IFNULL(tpa3.apellido_paterno, ''),	' ',	IFNULL(tpa3.apellido_materno, '')) AS cancelado_por,
							CONCAT(IFNULL(pab.nombre, ''),' ',IFNULL(pab.apellido_paterno, ''),	' ',	IFNULL(pab.apellido_materno, '')) AS abogado,
							a.cancelado_el,
							a.cancelado_motivo,
							a.requiere_aprobacion_id,
							a.aprobado_estado_id,
							a.aprobado_el,
							CONCAT(IFNULL(puap.nombre, ''),' ',IFNULL(puap.apellido_paterno, ''),' ',IFNULL(puap.apellido_materno, '')) AS adenda_aprobada_por
						FROM 
							cont_adendas a
							INNER JOIN tbl_usuarios u ON a.user_created_id = u.id
							INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
							LEFT JOIN tbl_usuarios uab ON a.abogado_id = uab.id
							LEFT JOIN tbl_personal_apt pab ON uab.personal_id = pab.id
							INNER JOIN tbl_areas ar ON p.area_id = ar.id
							LEFT JOIN tbl_usuarios tu3 ON a.cancelado_por_id = tu3.id
							LEFT JOIN tbl_personal_apt tpa3 ON tu3.personal_id = tpa3.id
							LEFT JOIN tbl_usuarios uap ON a.aprobado_por_id = uap.id
							LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id
						WHERE a.cancelado_el IS NULL  AND a.contrato_id = " . $contrato_id . "
						AND a.status = 1;");
						$row_cnt = $sel_query->num_rows;
						if ($row_cnt > 0) {
						?>

							<div class="panel">
								<div class="panel-heading" role="tab" id="browsers-adendas-heading">
									<div class="panel-title">
										<a href="#browsers-adendas" class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" aria-expanded="true" aria-controls="browsers-adendas">
											Adendas
										</a>
									</div>
								</div>

								<div id="browsers-adendas" class="panel-collapse collapse" role="tabpanel" aria-labelledby="browsers-adendas-heading">
									<div class="panel-body">
										<?php
										while ($sel = $sel_query->fetch_assoc()) {
											$adenda_id = $sel["id"];
											$codigo = $sel["codigo"];
											$procesado = $sel["procesado"];
											$cancelado_id = $sel["cancelado_id"];
											$anulado = $sel["anulado_id"];
											$cancelado_por = $sel['cancelado_por'];
											$cancelado_el = $sel['cancelado_el'];
											$cancelado_motivo = $sel['cancelado_motivo'];
											$estado_solicitud_id = $sel["estado_solicitud_id"];
											$area = $sel["area"];
											$solicitante = $sel["solicitante"];
											$abogado = $sel["abogado"];
											$fecha_solicitud = $sel["fecha_solicitud"];
											$fecha_aplicacion = $sel["fecha_aplicacion"];
											$requiere_aprobacion_id = trim($sel["requiere_aprobacion_id"]);
											$aprobado_el = $sel["aprobado_el"];
											$adenda_aprobada_por = $sel["adenda_aprobada_por"];
											$aprobado_estado_id = trim($sel["aprobado_estado_id"]);
											$numero_adenda++;

											if ($requiere_aprobacion_id != "") {
												$requiere_aprobacion_id = (int) $requiere_aprobacion_id;
											}

											if ($aprobado_estado_id != "") {
												$aprobado_estado_id = (int) $aprobado_estado_id;
											}
											$adenda_estado = '<span class="badge bg-info text-white">Sin definir</span>';
											if ($cancelado_id == 1) {
												$adenda_estado = '<span class="badge bg-danger text-white">Cancelada</span>';
											} elseif ($procesado == 0) {
												if ($requiere_aprobacion_id === 1) {

													if ($aprobado_estado_id === 1) {
														$adenda_estado = '<span class="badge bg-success text-white">Aprobado</span>';
													} elseif ($aprobado_estado_id === 0) {
														$adenda_estado = '<span class="badge bg-danger text-white">Rechazada</span>';
													}
												}
											} elseif ($procesado == 1) {
												$adenda_estado = '<span class="badge bg-info text-white">Procesado</span>';
												if ($anulado == 1) {
													$adenda_estado = '<span class="badge bg-dark text-white">Anulado</span>';
												}
											}
										?>

											<p>
												<b>Adenda N° <?php echo $codigo; ?>:</b>
												<?php if ($procesado == 1 and $anulado != 1) { ?>
													<button type="button" onclick="anularAdenda(<?= $sel['id'] ?>)" style="float: right" class="btn btn-xs btn-danger" style="margin-left: 10px;">
														<span class="fa fa-times"></span> Anular
													</button>
												<?php } ?>
												<!-- <?php if ((array_key_exists($menu_consultar, $usuario_permisos) && in_array("reenviar_adenda_firmada", $usuario_permisos[$menu_consultar])) && ($procesado == 1 || $aprobado_estado_id == 1)) { ?>
													<button type="button" onclick="sec_contrato_detalle_reenviar_adenda(<?= $sel['id'] ?>,'1')" style="float: right" class="btn btn-xs btn-info"><span class="fa fa-envelope-o"></span> Reenviar por email</button>
												<?php } ?> -->
											</p>
											<table class="table table-responsive table-hover no-mb" style="font-size: 10px;">
												<tbody>
													<tr style="text-transform: none;">
														<td><b>Área solicitante</b></td>
														<td>
															<?php echo $area; ?>
														</td>
													</tr>
													<!-- <tr style="text-transform: none;">
														<td><b>Abogado</b></td>
														<td>
															<?php echo $abogado; ?>
														</td>
														<?php if ($btn_editar_solicitud || ($procesado == 0 && $area_id == 33 && $cargo_id != 25)) { ?>
															<td>
																<a class="btn btn-success btn-xs"
																	id="btn_editar_adenda_abogado_<?= $adenda_id ?>"
																	onclick="sec_contrato_detalle_solicitudv2_editar_solicitud('Adenda','cont_adendas','abogado_id','Abogado','select_option','<?php echo $abogado; ?>','obtener_abogados','<?php echo $adenda_id; ?>');">
																	<span class="fa fa-edit"></span> Editar
																</a>
															</td>
														<?php } ?>
													</tr> -->
													<tr style="text-transform: none;">
														<td><b>Solicitante</b></td>
														<td>
															<?php echo $solicitante; ?>
														</td>
													</tr>
													<tr style="text-transform: none;">
														<td><b>Fecha de la solicitud</b></td>
														<td>
															<?php echo $fecha_solicitud; ?>
														</td>
													</tr>
													<tr style="text-transform: none;">
														<td><b>Estado</b></td>
														<td>
															<?php echo $adenda_estado; ?>
														</td>
													</tr>
													<tr style="text-transform: none;">
														<td><b>Fecha de Aplicación</b></td>
														<td>
															<?php echo $fecha_aplicacion; ?>
														</td>
													</tr>

													<?php
													if ($requiere_aprobacion_id == 1) {
														if ($aprobado_estado_id == 1) {
													?>

															<tr style="text-transform: none;">
																<td><b>Aprobado por</b></td>
																<td><?php echo $adenda_aprobada_por; ?></td>
															</tr>

															<tr style="text-transform: none;">
																<td><b>Aprobado el</b></td>
																<td><?php echo $aprobado_el; ?></td>
															</tr>

														<?php
														} elseif ($aprobado_estado_id == 0) {
														?>

															<tr style="text-transform: none;">
																<td><b>Rechazado por</b></td>
																<td><?php echo $adenda_aprobada_por; ?></td>
															</tr>

															<tr style="text-transform: none;">
																<td><b>Rechazado el</b></td>
																<td><?php echo $aprobado_el; ?></td>
															</tr>

														<?php
														} else {
														?>

															<tr style="text-transform: none;">
																<td><b>Aprobación pendiente de:</b></td>
																<td><?php echo $director_que_aprueba; ?></td>
															</tr>

														<?php
														}
													}
													if ($cancelado_id == 1) { ?>

														<tr style="text-transform: none;">
															<td><b>Cancelado por</b></td>
															<td><?php echo $cancelado_por; ?></td>
														</tr>

														<tr style="text-transform: none;">
															<td><b>Cancelado el</b></td>
															<td><?php echo $cancelado_el; ?></td>
														</tr>

														<tr style="text-transform: none;">
															<td><b>Motivo de la cancelación:</b></td>
															<td><?php echo $cancelado_motivo; ?></td>
														</tr>

														<?php
													}

													if ($procesado == 1) {
														$sql_adenda_firmada = "
												SELECT 
													a.archivo_id,
													a.contrato_id,
													a.tipo_archivo_id,
													a.nombre,
													a.extension,
													a.ruta,
													a.size,
													a.user_created_id,
													a.status,
													a.created_at,
													CONCAT(IFNULL(p.nombre, ''),' ',IFNULL(p.apellido_paterno, ''),' ',IFNULL(p.apellido_materno, '')) AS user_created
												FROM
													cont_archivos a
													INNER JOIN tbl_usuarios u ON a.user_created_id = u.id
													INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
												WHERE
													a.tipo_archivo_id = 17 
													AND a.status = 1     AND a.adenda_id= " . $adenda_id . "
													AND a.contrato_id = " . $contrato_id . "
													
													";

														$query = $mysqli->query($sql_adenda_firmada);
														$row_count = $query->num_rows;

														if ($row_count > 0) {
															$row = $query->fetch_assoc();
															$ruta = str_replace("/var/www/html", "", $row["ruta"]);
														?>
															<tr style="text-transform: none;">
																<td><b>Visualizar la adenda firmada:</b></td>
																<td>
																	<a
																		type="button"
																		onclick="sec_contrato_detalle_solicitudv2_ver_documento_en_visor('<?php echo $ruta; ?>','<?php echo trim($row["nombre"]); ?>','<?php echo trim($row["extension"]); ?>','ADENDA FIRMADA');"
																		class="btn btn-success btn-xs"
																		data-toggle="tooltip"
																		data-placement="top">
																		<span class="fa fa-eye"></span> Ver adenda firmada
																	</a>
																	<br>
																	(Subido por <?php echo $row["user_created"]; ?> el <?php echo $row["created_at"]; ?>)
																</td>
															</tr>
													<?php
														}
													}
													?>
												</tbody>
											</table>

											<br>

											<p><b>Adenda N° <?php echo $codigo; ?> - Detalle</b></p>

											<?php

											$numero_adenda_detalle = 0;

											$query_ade = "SELECT a.id, a.nombre_menu_usuario, a.nombre_campo_usuario, a.valor_original, a.tipo_valor, a.valor_varchar, a.valor_int, a.valor_date, 
										a.valor_decimal, a.valor_select_option, cd.codigo
										FROM cont_adendas_detalle AS a
										LEFT JOIN cont_contrato_detalle AS cd ON cd.id = a.contrato_detalle_id
										WHERE a.tipo_valor != 'id_tabla' AND a.tipo_valor != 'registro' AND a.tipo_valor != 'eliminar' 
										AND a.adenda_id = " . $adenda_id . "
										AND a.status = 1";
											$query = $mysqli->query($query_ade);
											$row_count = $query->num_rows;
											if ($row_count > 0) {
											?>
												<table class="table table-responsive table-hover no-mb" style="font-size: 10px;">
													<thead style="background: none;">
														<tr style="text-transform: none;">
															<th align="center">#</th>
															<th align="center">Menu</th>
															<th align="center">Campo</th>
															<th align="center">Valor Original</th>
															<th align="center">Nuevo Valor</th>
														</tr>
													</thead>
													<tbody>
														<?php
														while ($row = $query->fetch_assoc()) {
															$nombre_menu_usuario = $row["nombre_menu_usuario"];
															$nombre_campo_usuario = $row["nombre_campo_usuario"];
															$valor_original = $row["valor_original"];
															$tipo_valor = $row["tipo_valor"];
															if ($tipo_valor == 'varchar') {
																$nuevo_valor = $row['valor_varchar'];
															} else if ($tipo_valor == 'int') {
																$nuevo_valor = $row['valor_int'];
															} else if ($tipo_valor == 'date') {
																$nuevo_valor = $row['valor_date'];
															} else if ($tipo_valor == 'decimal') {
																$nuevo_valor = $row['valor_decimal'];
															} else if ($tipo_valor == 'select_option') {
																$nuevo_valor = $row['valor_select_option'];
															}
															$codigo = !empty($row["codigo"]) ? '(#' . $row["codigo"] . ')' : '';

															$numero_adenda_detalle++;
														?>
															<tr style="text-transform: none;">
																<td>
																	<?php echo $numero_adenda_detalle; ?>
																</td>
																<td>
																	<?php echo $nombre_menu_usuario . " " . $codigo; ?>
																</td>
																<td>
																	<?php echo $nombre_campo_usuario; ?>
																</td>
																<td style="white-space: pre-line;">
																	<?php echo $valor_original; ?>
																</td>
																<td style="white-space: pre-line;">
																	<?php echo $nuevo_valor; ?>
																</td>
															</tr>
														<?php
														}
														?>
													</tbody>
												</table>
											<?php
											}
											?>


											<?php
											// CAMBIO DE PROPIETARIOS Y BENEFICIARIOS
											$query_otros = "SELECT a.id, a.nombre_menu_usuario, a.valor_original,  a.valor_int, a.valor_id_tabla, cd.codigo
										FROM cont_adendas_detalle AS a
										LEFT JOIN cont_contrato_detalle AS cd ON cd.id = a.contrato_detalle_id
										WHERE a.tipo_valor = 'id_tabla'
											AND a.adenda_id = " . $adenda_id . "
											AND a.status = 1";

											$list_query_otros = $mysqli->query($query_otros);
											$row_count_otros = $list_query_otros->num_rows;

											if ($row_count_otros > 0) {
												while ($row = $list_query_otros->fetch_assoc()) {
													$codigo_contrato = !empty($row['codigo']) ? '- Contrato #' . $row['codigo'] : '';
													$valores_originales = [];
													$valores_nuevos = [];

													if ($row["nombre_menu_usuario"] == 'Propietario') {
														$query = "
													SELECT 
														p.id,
														tp.nombre AS tipo_persona,
														td.nombre AS tipo_docu_identidad,
														p.tipo_persona_id,
														p.tipo_docu_identidad_id,
														p.num_docu,
														p.nombre,
														p.direccion,
														p.representante_legal,
														p.num_partida_registral,
														p.contacto_nombre,
														p.contacto_telefono,
														p.contacto_email
													FROM
														cont_persona p
														INNER JOIN cont_tipo_persona tp ON p.tipo_persona_id = tp.id
														INNER JOIN cont_tipo_docu_identidad td ON p.tipo_docu_identidad_id = td.id
													WHERE
														p.id IN ('" . $row["valor_original"] . "' , '" . $row["valor_int"] . "')
													";

														$valores_originales = [];
														$valores_nuevos = [];
														$list_query = $mysqli->query($query);
														while ($li = $list_query->fetch_assoc()) {
															if ($li["id"] == $row["valor_original"]) {
																$valores_originales[] = $li;
															} else if ($li["id"] == $row["valor_int"]) {
																$valores_nuevos[] = $li;
															}
														}
											?>
														<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">
															<thead>

																<tr>
																	<th colspan="4" style="text-align: center; vertical-align: middle;">Cambio de Propietario <?= $codigo_contrato ?></th>
																</tr>

																<tr>
																	<th>Campo</th>
																	<th>Valor Actual</th>
																	<th>Nuevo Valor</th>
																</tr>

															</thead>
															<tbody>

																<tr>
																	<td>Tipo de documento de persona</td>
																	<td><?php echo $valores_originales[0]["tipo_persona"]; ?></td>
																	<td><?php echo $valores_nuevos[0]["tipo_persona"]; ?></td>
																</tr>

																<tr>
																	<td>Nombre</td>
																	<td><?php echo $valores_originales[0]["nombre"]; ?></td>
																	<td><?php echo $valores_nuevos[0]["nombre"]; ?></td>
																</tr>

																<tr>
																	<td>Tipo de documento de identidad</td>
																	<td><?php echo $valores_originales[0]["tipo_docu_identidad"]; ?></td>
																	<td><?php echo $valores_nuevos[0]["tipo_docu_identidad"]; ?></td>
																</tr>

																<tr>
																	<td>Número de documento de identidad</td>
																	<td><?php echo $valores_originales[0]["num_docu"]; ?></td>
																	<td><?php echo $valores_nuevos[0]["num_docu"]; ?></td>
																</tr>

																<tr>
																	<td>Dirección</td>
																	<td><?php echo $valores_originales[0]["direccion"]; ?></td>
																	<td><?php echo $valores_nuevos[0]["direccion"]; ?></td>
																</tr>

																<tr>
																	<td>Representante legal</td>
																	<td><?php echo $valores_originales[0]["representante_legal"]; ?></td>
																	<td><?php echo $valores_nuevos[0]["representante_legal"]; ?></td>
																</tr>

																<tr>
																	<td>N° de Partida Registral de la empresa</td>
																	<td><?php echo $valores_originales[0]["num_partida_registral"]; ?></td>
																	<td><?php echo $valores_nuevos[0]["num_partida_registral"]; ?></td>
																</tr>

																<tr>
																	<td>Contacto - Nombre</td>
																	<td><?php echo $valores_originales[0]["contacto_nombre"]; ?></td>
																	<td><?php echo $valores_nuevos[0]["contacto_nombre"]; ?></td>
																</tr>

																<tr>
																	<td>Contacto - Teléfono</td>
																	<td><?php echo $valores_originales[0]["contacto_telefono"]; ?></td>
																	<td><?php echo $valores_nuevos[0]["contacto_telefono"]; ?></td>
																</tr>

																<tr>
																	<td>Contacto - Email</td>
																	<td><?php echo $valores_originales[0]["contacto_email"]; ?></td>
																	<td><?php echo $valores_nuevos[0]["contacto_email"]; ?></td>
																</tr>

															</tbody>
														</table>
													<?php
													}

													if ($row["nombre_menu_usuario"] == 'Beneficiario') {
														$query = "
													SELECT 
														b.id,
														tp.nombre AS tipo_persona,
														td.nombre AS tipo_docu_identidad,
														b.num_docu,
														b.nombre,
														f.nombre AS forma_pago,
														ba.nombre AS banco,
														b.num_cuenta_bancaria,
														b.num_cuenta_cci,
														b.tipo_monto_id,
														tm.nombre AS tipo_monto,
														b.monto
													FROM
														cont_beneficiarios b
														LEFT JOIN cont_tipo_persona tp ON b.tipo_persona_id = tp.id
														LEFT JOIN cont_tipo_docu_identidad td ON b.tipo_docu_identidad_id = td.id
														INNER JOIN cont_forma_pago f ON b.forma_pago_id = f.id
														LEFT JOIN tbl_bancos ba ON b.banco_id = ba.id
														INNER JOIN cont_tipo_monto_a_depositar tm ON b.tipo_monto_id = tm.id
													WHERE
														b.id IN ('" . $row["valor_original"] . "' , '" . $row["valor_int"] . "')
													";

														$valores_originales = [];
														$valores_nuevos = [];
														$list_query = $mysqli->query($query);
														while ($li = $list_query->fetch_assoc()) {
															if ($li["id"] == $row["valor_original"]) {
																$valores_originales[] = $li;
															} else if ($li["id"] == $row["valor_int"]) {
																$valores_nuevos[] = $li;
															}
														}
													?>

														<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">
															<thead>

																<tr>
																	<th colspan="4" style="text-align: center; vertical-align: middle;">Cambio de Beneficiario <?= $codigo_contrato ?></th>
																</tr>

																<tr>
																	<th>Campo</th>
																	<th>Valor Actual</th>
																	<th>Nuevo Valor</th>
																</tr>

															</thead>
															<tbody>

																<tr>
																	<td>Tipo de documento de persona</td>
																	<td><?php echo $valores_originales[0]["tipo_persona"]; ?></td>
																	<td><?php echo $valores_nuevos[0]["tipo_persona"]; ?></td>
																</tr>

																<tr>
																	<td>Nombre</td>
																	<td><?php echo $valores_originales[0]["nombre"]; ?></td>
																	<td><?php echo $valores_nuevos[0]["nombre"]; ?></td>
																</tr>

																<tr>
																	<td>Tipo de documento de identidad</td>
																	<td><?php echo $valores_originales[0]["tipo_docu_identidad"]; ?></td>
																	<td><?php echo $valores_nuevos[0]["tipo_docu_identidad"]; ?></td>
																</tr>

																<tr>
																	<td>Número de documento de identidad</td>
																	<td><?php echo $valores_originales[0]["num_docu"]; ?></td>
																	<td><?php echo $valores_nuevos[0]["num_docu"]; ?></td>
																</tr>

																<tr>
																	<td>Tipo de forma de pago</td>
																	<td><?php echo $valores_originales[0]["forma_pago"]; ?></td>
																	<td><?php echo $valores_nuevos[0]["forma_pago"]; ?></td>
																</tr>

																<tr>
																	<td>Nombre del Banco</td>
																	<td><?php echo $valores_originales[0]["banco"]; ?></td>
																	<td><?php echo $valores_nuevos[0]["banco"]; ?></td>
																</tr>

																<tr>
																	<td>N° de la cuenta bancaria</td>
																	<td><?php echo $valores_originales[0]["num_cuenta_bancaria"]; ?></td>
																	<td><?php echo $valores_nuevos[0]["num_cuenta_bancaria"]; ?></td>
																</tr>

																<tr>
																	<td>N° de CCI bancario</td>
																	<td><?php echo $valores_originales[0]["num_cuenta_cci"]; ?></td>
																	<td><?php echo $valores_nuevos[0]["num_cuenta_cci"]; ?></td>
																</tr>

																<tr>
																	<td>Tipo de monto a depositar</td>
																	<td><?php echo $valores_originales[0]["tipo_monto"]; ?></td>
																	<td><?php echo $valores_nuevos[0]["tipo_monto"]; ?></td>
																</tr>

																<tr>
																	<td>Monto</td>
																	<td><?php echo $valores_originales[0]["monto"]; ?></td>
																	<td><?php echo $valores_nuevos[0]["monto"]; ?></td>
																</tr>

															</tbody>
														</table>
													<?php
													}

													if ($row["nombre_menu_usuario"] == 'Incremento') {
														$query = "
													SELECT 
														i.id, 
														i.valor, 
														i.tipo_valor_id,
														tp.nombre AS tipo_valor, 
														i.tipo_continuidad_id, 
														tc.nombre AS tipo_continuidad, 
														i.a_partir_del_año
													FROM 
														cont_incrementos i
														INNER JOIN cont_tipo_pago_incrementos tp ON i.tipo_valor_id = tp.id
														INNER JOIN cont_tipo_continuidad_pago tc ON i.tipo_continuidad_id = tc.id
													WHERE 
														i.id IN ('" . $row["valor_original"] . "' , '" . $row["valor_int"] . "')";

														$valores_originales = [];
														$valores_nuevos = [];
														$list_query = $mysqli->query($query);
														while ($li = $list_query->fetch_assoc()) {
															if ($li["id"] == $row["valor_original"]) {
																$valores_originales[] = $li;
															} else if ($li["id"] == $row["valor_int"]) {
																$valores_nuevos[] = $li;
															}
														}
													?>

														<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">
															<thead>

																<tr>
																	<th colspan="4" style="text-align: center; vertical-align: middle;">Cambio de Incremento <?= $codigo_contrato ?></th>
																</tr>

																<tr>
																	<th>Campo</th>
																	<th>Valor Actual</th>
																	<th>Nuevo Valor</th>
																</tr>

															</thead>
															<tbody>

																<tr>
																	<td>Valor</td>
																	<td><?php echo $valores_originales[0]["valor"]; ?></td>
																	<td><?php echo $valores_nuevos[0]["valor"]; ?></td>
																</tr>

																<tr>
																	<td>Tipo Valor</td>
																	<td><?php echo $valores_originales[0]["valor"]; ?></td>
																	<td><?php echo $valores_nuevos[0]["tipo_valor"]; ?></td>
																</tr>

																<tr>
																	<td>Continuidad</td>
																	<td><?php echo $valores_originales[0]["valor"]; ?></td>
																	<td><?php echo $valores_nuevos[0]["tipo_continuidad"]; ?></td>
																</tr>

																<tr>
																	<td>Apartir del</td>
																	<td><?php echo $valores_originales[0]["valor"]; ?></td>
																	<td><?php echo $valores_nuevos[0]["a_partir_del_año"]; ?></td>
																</tr>

															</tbody>
														</table>
													<?php
													}

													if ($row["nombre_menu_usuario"] == 'Responsable IR') {
														$query = "
														SELECT 
															r.id,
															r.contrato_id,
															r.tipo_documento_id,
															r.num_documento,
															r.nombres,
															r.estado_emisor,
															r.porcentaje,
															r.status,
															r.user_created_id,
															r.created_at,
															td.nombre AS tipo_documento
														FROM cont_responsable_ir as r
														LEFT JOIN cont_tipo_docu_identidad AS td ON td.id = r.tipo_documento_id
														WHERE r.id IN ('" . $row["valor_original"] . "' , '" . $row["valor_int"] . "')
													";

														$valores_originales = [];
														$valores_nuevos = [];
														$list_query = $mysqli->query($query);
														while ($li = $list_query->fetch_assoc()) {
															if ($li["id"] == $row["valor_original"]) {
																$valores_originales[] = $li;
															} else if ($li["id"] == $row["valor_int"]) {
																$valores_nuevos[] = $li;
															}
														}
													?>

														<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">
															<thead>

																<tr>
																	<th colspan="4" style="text-align: center; vertical-align: middle;">Cambio de Responsable IR <?= $codigo_contrato ?></th>
																</tr>

																<tr>
																	<th>Campo</th>
																	<th>Valor Actual</th>
																	<th>Nuevo Valor</th>
																</tr>

															</thead>
															<tbody>

																<tr>
																	<td>Tipo de Documento de Identidad</td>
																	<td><?php echo $valores_originales[0]["tipo_documento"]; ?></td>
																	<td><?php echo $valores_nuevos[0]["tipo_documento"]; ?></td>
																</tr>

																<tr>
																	<td>Nro Documento</td>
																	<td><?php echo $valores_originales[0]["num_documento"]; ?></td>
																	<td><?php echo $valores_nuevos[0]["num_documento"]; ?></td>
																</tr>

																<tr>
																	<td>Nombres</td>
																	<td><?php echo $valores_originales[0]["nombres"]; ?></td>
																	<td><?php echo $valores_nuevos[0]["nombres"]; ?></td>
																</tr>

																<tr>
																	<td>Porcentaje</td>
																	<td><?php echo $valores_originales[0]["porcentaje"]; ?></td>
																	<td><?php echo $valores_nuevos[0]["porcentaje"]; ?></td>
																</tr>



															</tbody>
														</table>
													<?php
													}
												}
											}

											// NUEVOS RESGISTROS DE PROPIETARIOS Y BENEFICIARIOS
											$query_otros = "SELECT a.id, a.nombre_menu_usuario, a.valor_original,  a.valor_int, a.valor_id_tabla, cd.codigo
										FROM cont_adendas_detalle AS a
										LEFT JOIN cont_contrato_detalle AS cd ON cd.id = a.contrato_detalle_id
										WHERE a.tipo_valor = 'registro'
											AND a.adenda_id = " . $adenda_id . "
											AND a.status = 1";

											$list_query_otros = $mysqli->query($query_otros);
											$row_count_otros = $list_query_otros->num_rows;

											if ($row_count_otros > 0) {
												while ($row = $list_query_otros->fetch_assoc()) {

													$codigo_contrato = !empty($row['codigo']) ? '- Contrato #' . $row['codigo'] : '';
													$valores_originales = [];
													$valores_nuevos = [];

													if ($row["nombre_menu_usuario"] == 'Inflación') {
														$query = "SELECT i.id, DATE_FORMAT(i.fecha, '%d-%m-%Y') as fecha, tp.nombre  AS tipo_periodicidad, i.numero, p.nombre as tipo_anio_mes, m.sigla AS moneda, i.porcentaje_anadido, i.tope_inflacion, i.minimo_inflacion, i.tipo_aplicacion_id, ta.nombre as tipo_aplicacion
													FROM cont_inflaciones AS i
													INNER JOIN cont_tipo_periodicidad AS tp ON tp.id = i.tipo_periodicidad_id
													LEFT JOIN tbl_moneda AS m ON m.id = i.moneda_id
													LEFT JOIN cont_periodo as p ON p.id = i.tipo_anio_mes
													LEFT JOIN cont_tipo_aplicacion as ta ON ta.id = i.tipo_aplicacion_id
													WHERE i.id IN ('" . $row["valor_int"] . "')
													";

														$valores_originales = [];
														$valores_nuevos = [];
														$list_query = $mysqli->query($query);
														while ($li = $list_query->fetch_assoc()) {
															if ($li["id"] == $row["valor_int"]) {
																$valores_nuevos[] = $li;
															}
														}
													?>
														<br>
														<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">
															<thead>

																<tr>
																	<th colspan="2" style="text-align: center; vertical-align: middle;">Nueva Inflación <?= $codigo_contrato ?> </th>
																</tr>

																<tr>
																	<th>Campo</th>
																	<th>Nuevo Valor</th>

																</tr>

															</thead>
															<tbody>

																<tr>
																	<td>Fecha de Ajuste</td>
																	<td><?= $valores_nuevos[0]["fecha"] ?></td>
																</tr>

																<tr>
																	<td>Periodicidad</td>
																	<td><?= $valores_nuevos[0]['tipo_periodicidad'] . ' ' . $valores_nuevos[0]['numero'] . ' ' . $valores_nuevos[0]['tipo_anio_mes'] ?></td>
																</tr>

																<tr>
																	<td>Curva</td>
																	<td><?= $valores_nuevos[0]["moneda"] ?></td>
																</tr>

																<tr>
																	<td>Porcentaje Añadido</td>
																	<td><?= $valores_nuevos[0]["porcentaje_anadido"] ?></td>
																</tr>

																<tr>
																	<td>Tope de Inflación</td>
																	<td><?= $valores_nuevos[0]["tope_inflacion"] ?></td>
																</tr>

																<tr>
																	<td>Minimo de Inflación</td>
																	<td><?= $valores_nuevos[0]["minimo_inflacion"] ?></td>
																</tr>

																<tr>
																	<td>Tipo Aplicación</td>
																	<td><?= $valores_nuevos[0]["tipo_aplicacion"] ?></td>
																</tr>


															</tbody>
														</table>
													<?php
													}

													if ($row["nombre_menu_usuario"] == 'Cuota Extraordinaria') {
														$query = "SELECT c.id, m.nombre as mes, c.multiplicador, c.fecha
													FROM cont_cuotas_extraordinarias AS c
													INNER JOIN tbl_meses AS m ON m.id = c.mes
													WHERE c.id IN ('" . $row["valor_int"] . "')
													";

														$valores_originales = [];
														$valores_nuevos = [];
														$list_query = $mysqli->query($query);
														while ($li = $list_query->fetch_assoc()) {
															if ($li["id"] == $row["valor_int"]) {
																$valores_nuevos[] = $li;
															}
														}
													?>
														<br>
														<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">
															<thead>

																<tr>
																	<th colspan="2" style="text-align: center; vertical-align: middle;">Nueva Cuota Extraordinaria <?= $codigo_contrato ?> </th>
																</tr>

																<tr>
																	<th>Campo</th>
																	<th>Nuevo Valor</th>

																</tr>

															</thead>
															<tbody>

																<tr>
																	<td>Fecha de Ajuste</td>
																	<td><?= $valores_nuevos[0]["fecha"] ?></td>
																</tr>

																<tr>
																	<td>Mes</td>
																	<td><?= $valores_nuevos[0]["mes"] ?></td>
																</tr>

																<tr>
																	<td>Multiplicador</td>
																	<td><?= $valores_nuevos[0]["multiplicador"] ?></td>
																</tr>

															</tbody>
														</table>
													<?php
													}


													if ($row["nombre_menu_usuario"] == 'Representante Legal') {
														$query = "
													SELECT 
														rl.id, 
														rl.dni_representante, 
														rl.nombre_representante, 
														rl.nro_cuenta_detraccion,
														rl.id_banco, 
														b.nombre as banco_representante, 
														rl.nro_cuenta, 
														rl.nro_cci, 
														rl.vigencia_archivo_id,
														rl.dni_archivo_id
													FROM 
														cont_representantes_legales rl
														LEFT JOIN tbl_bancos b on b.id = rl.id_banco
													WHERE 
														rl.id IN ('" . $row["valor_int"] . "')
													";

														$valores_originales = [];
														$valores_nuevos = [];
														$list_query = $mysqli->query($query);
														while ($li = $list_query->fetch_assoc()) {
															if ($li["id"] == $row["valor_int"]) {
																$valores_nuevos[] = $li;
															}
														}
													?>
														<br>
														<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">
															<thead>

																<tr>
																	<th colspan="2" style="text-align: center; vertical-align: middle;">Nuevo Representante Legal <?= $codigo_contrato ?> </th>
																</tr>

																<tr>
																	<th>Campo</th>
																	<th>Nuevo Valor</th>

																</tr>

															</thead>
															<tbody>

																<tr>
																	<td>DNI del representante legal</td>
																	<td><?= $valores_nuevos[0]["dni_representante"] ?></td>
																</tr>

																<tr>
																	<td>Nombre completo del representante legal</td>
																	<td><?= $valores_nuevos[0]["nombre_representante"] ?></td>
																</tr>

																<tr>
																	<td>Número de cuenta de detracción (Banco de la Nación)</td>
																	<td><?= $valores_nuevos[0]["nro_cuenta_detraccion"] ?></td>
																</tr>

																<tr>
																	<td>Banco</td>
																	<td><?= $valores_nuevos[0]["banco_representante"] ?></td>
																</tr>

																<tr>
																	<td>Nro Cuenta</td>
																	<td><?= $valores_nuevos[0]["nro_cuenta"] ?></td>
																</tr>

																<tr>
																	<td>Nro CCI</td>
																	<td><?= $valores_nuevos[0]["nro_cci"] ?></td>
																</tr>


															</tbody>
														</table>
													<?php
													}

													if ($row["nombre_menu_usuario"] == 'Propietario') {
														$query = "
														SELECT p.id AS persona_id,
															pr.propietario_id,
															tp.nombre AS tipo_persona,
															p.tipo_docu_identidad_id,
															td.nombre AS tipo_docu_identidad,
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
														WHERE pr.propietario_id IN ('" . $row["valor_int"] . "')
													";

														$valores_originales = [];
														$valores_nuevos = [];
														$list_query = $mysqli->query($query);
														while ($li = $list_query->fetch_assoc()) {
															if ($li["propietario_id"] == $row["valor_int"]) {
																$valores_nuevos[] = $li;
															}
														}
													?>
														<br>
														<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">
															<thead>

																<tr>
																	<th colspan="2" style="text-align: center; vertical-align: middle;">Nuevo Propietario <?= $codigo_contrato ?> </th>
																</tr>

																<tr>
																	<th>Campo</th>
																	<th>Nuevo Valor</th>

																</tr>

															</thead>
															<tbody>

																<tr>
																	<td>Tipo Persona</td>
																	<td><?= $valores_nuevos[0]["tipo_persona"] ?></td>
																</tr>

																<tr>
																	<td>Nombre</td>
																	<td><?= $valores_nuevos[0]["nombre"] ?></td>
																</tr>

																<tr>
																	<td>Tipo de Documento de Identidad</td>
																	<td><?= $valores_nuevos[0]["tipo_docu_identidad"] ?></td>
																</tr>

																<?php

																if ($valores_nuevos[0]["tipo_docu_identidad_id"] != "2") {
																?>
																	<tr>
																		<td><?= $valores_nuevos[0]["tipo_docu_identidad"] ?></td>
																		<td><?= $valores_nuevos[0]["num_docu"] ?></td>
																	</tr>
																<?php
																}
																?>
																<tr>
																	<td>Número de RUC</td>
																	<td><?= $valores_nuevos[0]["num_ruc"] ?></td>
																</tr>

																<tr>
																	<td>Domicilio del propietario</td>
																	<td><?= $valores_nuevos[0]["direccion"] ?></td>
																</tr>

																<tr>
																	<td>Representante Legal</td>
																	<td><?= $valores_nuevos[0]["representante_legal"] ?></td>
																</tr>

																<tr>
																	<td>N° de Partida Registral de la empresa</td>
																	<td><?= $valores_nuevos[0]["num_partida_registral"] ?></td>
																</tr>

																<tr>
																	<td>Persona de contacto</td>
																	<td><?= $valores_nuevos[0]["contacto_nombre"] ?></td>
																</tr>

																<tr>
																	<td>Teléfono de la persona de contacto</td>
																	<td><?= $valores_nuevos[0]["contacto_telefono"] ?></td>
																</tr>

																<tr>
																	<td>E-mail de la persona de contacto</td>
																	<td><?= $valores_nuevos[0]["contacto_email"] ?></td>
																</tr>

															</tbody>
														</table>
													<?php
													}

													if ($row["nombre_menu_usuario"] == 'Contraprestación') {
														$query = "
													SELECT 
														c.id,
														c.moneda_id,
														m.nombre AS tipo_moneda,
														m.simbolo AS tipo_moneda_simbolo,
														c.subtotal,
														c.igv,
														c.monto,
														c.forma_pago_detallado,
														c.tipo_comprobante_id,
														t.nombre AS tipo_comprobante,
														c.plazo_pago
													FROM 
														cont_contraprestacion c
														INNER JOIN tbl_moneda m ON c.moneda_id = m.id
														INNER JOIN cont_tipo_comprobante t ON c.tipo_comprobante_id = t.id
													WHERE 
														c.id IN ('" . $row["valor_int"] . "')
													";

														$valores_originales = [];
														$valores_nuevos = [];
														$list_query = $mysqli->query($query);
														while ($li = $list_query->fetch_assoc()) {
															if ($li["id"] == $row["valor_int"]) {
																$li["subtotal"] = $li["tipo_moneda_simbolo"] . ' ' . number_format($li["subtotal"], 2, '.', ',');
																$li["igv"] = $li["tipo_moneda_simbolo"] . ' ' . number_format($li["igv"], 2, '.', ',');
																$li["monto"] = $li["tipo_moneda_simbolo"] . ' ' . number_format($li["monto"], 2, '.', ',');
																$valores_nuevos[] = $li;
															}
														}
													?>
														<br>
														<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">
															<thead>

																<tr>
																	<th colspan="2" style="text-align: center; vertical-align: middle;">Nueva Contraprestación <?= $codigo_contrato ?> </th>
																</tr>

																<tr>
																	<th>Campo</th>
																	<th>Nuevo Valor</th>

																</tr>

															</thead>
															<tbody>

																<tr>
																	<td>Tipo de moneda</td>
																	<td><?= $valores_nuevos[0]["tipo_moneda"] ?></td>
																</tr>

																<tr>
																	<td>Subtotal</td>
																	<td><?= $valores_nuevos[0]["subtotal"] ?></td>
																</tr>

																<tr>
																	<td>IGV</td>
																	<td><?= $valores_nuevos[0]["igv"] ?></td>
																</tr>

																<tr>
																	<td>Monto Bruto</td>
																	<td><?= $valores_nuevos[0]["monto"] ?></td>
																</tr>

																<tr>
																	<td>Tipo de comprobante a emitir</td>
																	<td><?= $valores_nuevos[0]["tipo_comprobante"] ?></td>
																</tr>

																<tr>
																	<td>Plazo de Pago</td>
																	<td><?= $valores_nuevos[0]["plazo_pago"] ?></td>
																</tr>

																<tr>
																	<td>Forma de pago</td>
																	<td><?= $valores_nuevos[0]["forma_pago_detallado"] ?></td>
																</tr>


															</tbody>
														</table>
													<?php
													}

													if ($row["nombre_menu_usuario"] == 'Beneficiario') {
														$query = "
															SELECT b.id,
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
														WHERE b.id = " . $row["valor_int"];

														$valores_originales = [];
														$valores_nuevos = [];
														$list_query = $mysqli->query($query);
														while ($li = $list_query->fetch_assoc()) {
															if ($li["id"] == $row["valor_int"]) {
																$valores_nuevos[] = $li;
															}
														}

														$beneficiario_id = $valores_nuevos[0]["id"];
														$ben_tipo_persona = $valores_nuevos[0]["tipo_persona"];
														$ben_tipo_docu_identidad = $valores_nuevos[0]["tipo_docu_identidad"];
														$ben_num_docu = $valores_nuevos[0]["num_docu"];
														$ben_nombre = $valores_nuevos[0]["nombre"];
														$ben_direccion = '';
														$ben_forma_pago = $valores_nuevos[0]["forma_pago"];
														$ben_banco = $valores_nuevos[0]["banco"];
														$ben_num_cuenta_bancaria = $valores_nuevos[0]["num_cuenta_bancaria"];
														$ben_num_cuenta_cci = $valores_nuevos[0]["num_cuenta_cci"];
														$ben_tipo_monto_id = $valores_nuevos[0]["tipo_monto_id"];


														$ben_monto_beneficiario = number_format($valores_nuevos[0]["monto"], 2, '.', ',');

													?>
														<br>
														<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">
															<thead>

																<tr>
																	<th colspan="2" style="text-align: center; vertical-align: middle;">Nuevo Beneficiario <?= $codigo_contrato ?> </th>
																</tr>

																<tr>
																	<th>Campo</th>
																	<th>Nuevo Valor</th>

																</tr>

															</thead>
															<tbody>

																<tr>
																	<td>Tipo de Persona</td>
																	<td><?= $ben_tipo_persona ?></td>
																</tr>

																<tr>
																	<td>Nombre</td>
																	<td><?= $ben_nombre ?></td>
																</tr>

																<tr>
																	<td>Tipo de Documento de Identidad</td>
																	<td><?= $ben_tipo_docu_identidad ?></td>
																</tr>


																<tr>
																	<td>Número de Documento de Identidad</td>
																	<td><?= $ben_num_docu ?></td>
																</tr>

																<tr>
																	<td>Tipo de forma de pago</td>
																	<td><?= $ben_forma_pago ?></td>
																</tr>

																<tr>
																	<td>Nombre del Banco</td>
																	<td><?= $ben_banco ?></td>
																</tr>

																<tr>
																	<td>N° de la cuenta bancaria</td>
																	<td><?= $ben_num_cuenta_bancaria ?></td>
																</tr>

																<tr>
																	<td>N° de CCI bancario</td>
																	<td><?= $ben_num_cuenta_cci ?></td>
																</tr>

																<tr>
																	<td>Monto</td>
																	<td><?= $ben_monto_beneficiario ?></td>
																</tr>



															</tbody>
														</table>
													<?php
													}

													if ($row["nombre_menu_usuario"] == 'Incremento') {
														$query = "
													SELECT 
														i.id, 
														i.valor, 
														i.tipo_valor_id,
														tp.nombre AS tipo_valor, 
														i.tipo_continuidad_id, 
														tc.nombre AS tipo_continuidad, 
														i.a_partir_del_año
													FROM 
														cont_incrementos i
														INNER JOIN cont_tipo_pago_incrementos tp ON i.tipo_valor_id = tp.id
														INNER JOIN cont_tipo_continuidad_pago tc ON i.tipo_continuidad_id = tc.id
													WHERE 
														i.id = " . $row["valor_int"];


														$list_query = $mysqli->query($query);
														while ($li = $list_query->fetch_assoc()) {
															$valor_nuevo = $li;
														}
													?>
														<br>
														<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">
															<thead>

																<tr>
																	<th colspan="2" style="text-align: center; vertical-align: middle;">Nuevo Incremento <?= $codigo_contrato ?> </th>
																</tr>

																<tr>
																	<th>Campo</th>
																	<th>Nuevo Valor</th>
																</tr>

															</thead>
															<tbody>

																<tr>
																	<td>Valor</td>
																	<td><?= $valor_nuevo["valor"] ?></td>
																</tr>

																<tr>
																	<td>Tipo Valor</td>
																	<td><?= $valor_nuevo["tipo_valor"] ?></td>
																</tr>

																<tr>
																	<td>Continuidad</td>
																	<td><?= $valor_nuevo["tipo_continuidad"] ?></td>
																</tr>

																<tr>
																	<td>Apartir del</td>
																	<td><?= $valor_nuevo["a_partir_del_año"] ?></td>
																</tr>

															</tbody>
														</table>
													<?php
													}

													if ($row["nombre_menu_usuario"] == 'Responsable IR') {
														$query = "SELECT 
														r.id,
														r.contrato_id,
														r.tipo_documento_id,
														r.num_documento,
														r.nombres,
														r.estado_emisor,
														r.porcentaje,
														r.status,
														r.user_created_id,
														r.created_at,
														td.nombre AS tipo_documento
													FROM cont_responsable_ir as r
													LEFT JOIN cont_tipo_docu_identidad AS td ON td.id = r.tipo_documento_id
													WHERE r.id = " . $row["valor_int"];


														$list_query = $mysqli->query($query);
														while ($li = $list_query->fetch_assoc()) {
															$valor_nuevo = $li;
														}
													?>
														<br>
														<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">
															<thead>

																<tr>
																	<th colspan="2" style="text-align: center; vertical-align: middle;">Nuevo Responsable IR <?= $codigo_contrato ?> </th>
																</tr>

																<tr>
																	<th>Campo</th>
																	<th>Nuevo Valor</th>
																</tr>

															</thead>
															<tbody>

																<tr>
																	<td>Tipo de Documento de Identidad<< /td>
																	<td><?= $valor_nuevo["tipo_documento"] ?></td>
																</tr>

																<tr>
																	<td>Nro Documento</td>
																	<td><?= $valor_nuevo["num_documento"] ?></td>
																</tr>

																<tr>
																	<td>Nombres</td>
																	<td><?= $valor_nuevo["nombres"] ?></td>
																</tr>

																<tr>
																	<td>Porcentaje</td>
																	<td><?= $valor_nuevo["porcentaje"] ?></td>
																</tr>

															</tbody>
														</table>
													<?php
													}

													if ($row["nombre_menu_usuario"] == 'Suministro') {
														$query = "SELECT s.id, s.tipo_servicio_id, sp.nombre AS tipo_servicio, s.nro_suministro, s.tipo_compromiso_pago_id, ps.nombre AS tipo_compromiso, s.monto_o_porcentaje
													FROM cont_inmueble_suministros AS s
													LEFT JOIN cont_tipo_pago_servicio AS ps ON ps.id = s.tipo_compromiso_pago_id
													LEFT JOIN cont_tipo_servicio_publico AS sp ON sp.id = s.tipo_servicio_id
													INNER JOIN cont_inmueble AS i ON i.id = s.inmueble_id
													INNER JOIN cont_contrato_detalle AS cd ON cd.id = i.contrato_detalle_id
													WHERE s.id = " . $row["valor_int"];


														$list_query = $mysqli->query($query);
														while ($li = $list_query->fetch_assoc()) {
															$valor_nuevo = $li;
														}
													?>
														<br>
														<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">
															<thead>

																<tr>
																	<th colspan="2" style="text-align: center; vertical-align: middle;">Nuevo Suministro <?= $codigo_contrato ?> </th>
																</tr>

																<tr>
																	<th>Campo</th>
																	<th>Nuevo Valor</th>
																</tr>

															</thead>
															<tbody>

																<tr>
																	<td>Tipo de Servicio</td>
																	<td><?= $valor_nuevo["tipo_servicio"] ?></td>
																</tr>

																<tr>
																	<td>N° de Suministro</td>
																	<td><?= $valor_nuevo["nro_suministro"] ?></td>
																</tr>

																<tr>
																	<td>Compromiso de pago</td>
																	<td><?= $valor_nuevo["tipo_compromiso"] ?></td>
																</tr>

																<tr>
																	<td>Monto/Porcentaje</td>
																	<td><?= $valor_nuevo["monto_o_porcentaje"] ?></td>
																</tr>

															</tbody>
														</table>
													<?php
													}
												}
											}


											// NUEVOS RESGISTROS DE PROPIETARIOS Y BENEFICIARIOS
											$query_otros = "SELECT a.id, a.nombre_menu_usuario, a.valor_original,  a.valor_int, a.valor_id_tabla, cd.codigo
										FROM cont_adendas_detalle AS a
										LEFT JOIN cont_contrato_detalle AS cd ON cd.id = a.contrato_detalle_id
										WHERE a.tipo_valor = 'eliminar'
											AND a.adenda_id = " . $adenda_id . "
											AND a.status = 1";

											$list_query_otros = $mysqli->query($query_otros);
											$row_count_otros = $list_query_otros->num_rows;

											if ($row_count_otros > 0) {
												while ($row = $list_query_otros->fetch_assoc()) {

													$codigo_contrato = !empty($row['codigo']) ? '- Contrato #' . $row['codigo'] : '';
													$valores_originales = [];
													$valores_nuevos = [];

													if ($row["nombre_menu_usuario"] == 'Inflación') {
														$query = "SELECT i.id, DATE_FORMAT(i.fecha, '%d-%m-%Y') as fecha, tp.nombre  AS tipo_periodicidad, i.numero, p.nombre as tipo_anio_mes, m.sigla AS moneda, i.porcentaje_anadido, i.tope_inflacion, i.minimo_inflacion
													FROM cont_inflaciones AS i
													INNER JOIN cont_tipo_periodicidad AS tp ON tp.id = i.tipo_periodicidad_id
													LEFT JOIN tbl_moneda AS m ON m.id = i.moneda_id
													LEFT JOIN cont_periodo as p ON p.id = i.tipo_anio_mes
													WHERE i.id IN ('" . $row["valor_int"] . "')
													";

														$valores_originales = [];
														$valores_nuevos = [];
														$list_query = $mysqli->query($query);
														while ($li = $list_query->fetch_assoc()) {
															if ($li["id"] == $row["valor_int"]) {
																$valores_nuevos[] = $li;
															}
														}
													?>
														<br>
														<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">
															<thead>

																<tr>
																	<th colspan="2" style="text-align: center; vertical-align: middle;">Eliminar Inflación <?= $codigo_contrato ?></th>
																</tr>

																<tr>
																	<th>Campo</th>
																	<th>Nuevo Valor</th>

																</tr>

															</thead>
															<tbody>

																<tr>
																	<td>Fecha de Ajuste</td>
																	<td><?= $valores_nuevos[0]["fecha"] ?></td>
																</tr>

																<tr>
																	<td>Periodicidad</td>
																	<td><?= $valores_nuevos[0]['tipo_periodicidad'] . ' ' . $valores_nuevos[0]['numero'] . ' ' . $valores_nuevos[0]['tipo_anio_mes'] ?></td>
																</tr>

																<tr>
																	<td>Curva</td>
																	<td><?= $valores_nuevos[0]["moneda"] ?></td>
																</tr>

																<tr>
																	<td>Porcentaje Añadido</td>
																	<td><?= $valores_nuevos[0]["porcentaje_anadido"] ?></td>
																</tr>

																<tr>
																	<td>Tope de Inflación</td>
																	<td><?= $valores_nuevos[0]["tope_inflacion"] ?></td>
																</tr>

																<tr>
																	<td>Minimo de Inflación</td>
																	<td><?= $valores_nuevos[0]["minimo_inflacion"] ?></td>
																</tr>


															</tbody>
														</table>
													<?php
													}

													if ($row["nombre_menu_usuario"] == 'Cuota Extraordinaria') {
														$query = "SELECT c.id, m.nombre as mes, c.multiplicador, c.fecha
													FROM cont_cuotas_extraordinarias AS c
													INNER JOIN tbl_meses AS m ON m.id = c.mes
													WHERE c.id IN ('" . $row["valor_int"] . "')
													";

														$valores_originales = [];
														$valores_nuevos = [];
														$list_query = $mysqli->query($query);
														while ($li = $list_query->fetch_assoc()) {
															if ($li["id"] == $row["valor_int"]) {
																$valores_nuevos[] = $li;
															}
														}
													?>
														<br>
														<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">
															<thead>

																<tr>
																	<th colspan="2" style="text-align: center; vertical-align: middle;">Eliminar Cuota Extraordinaria <?= $codigo_contrato ?></th>
																</tr>

																<tr>
																	<th>Campo</th>
																	<th>Nuevo Valor</th>

																</tr>

															</thead>
															<tbody>

																<tr>
																	<td>Fecha de Ajuste</td>
																	<td><?= $valores_nuevos[0]["fecha"] ?></td>
																</tr>

																<tr>
																	<td>Mes</td>
																	<td><?= $valores_nuevos[0]["mes"] ?></td>
																</tr>

																<tr>
																	<td>Multiplicador</td>
																	<td><?= $valores_nuevos[0]["multiplicador"] ?></td>
																</tr>

															</tbody>
														</table>
													<?php
													}

													if ($row["nombre_menu_usuario"] == 'Propietario') {
														$query = "
														SELECT p.id AS persona_id,
															pr.propietario_id,
															tp.nombre AS tipo_persona,
															p.tipo_docu_identidad_id,
															td.nombre AS tipo_docu_identidad,
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
														WHERE pr.propietario_id IN ('" . $row["valor_int"] . "')
													";

														$valores_originales = [];
														$valores_nuevos = [];
														$list_query = $mysqli->query($query);
														while ($li = $list_query->fetch_assoc()) {
															if ($li["propietario_id"] == $row["valor_int"]) {
																$valores_nuevos[] = $li;
															}
														}
													?>
														<br>
														<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">
															<thead>

																<tr>
																	<th colspan="2" style="text-align: center; vertical-align: middle;">Eliminar Propietario <?= $codigo_contrato ?></th>
																</tr>

																<tr>
																	<th>Campo</th>
																	<th>Nuevo Valor</th>

																</tr>

															</thead>
															<tbody>

																<tr>
																	<td>Tipo Persona</td>
																	<td><?= $valores_nuevos[0]["tipo_persona"] ?></td>
																</tr>

																<tr>
																	<td>Nombre</td>
																	<td><?= $valores_nuevos[0]["nombre"] ?></td>
																</tr>

																<tr>
																	<td>Tipo de Documento de Identidad</td>
																	<td><?= $valores_nuevos[0]["tipo_docu_identidad"] ?></td>
																</tr>

																<?php

																if ($valores_nuevos[0]["tipo_docu_identidad_id"] != "2") {
																?>
																	<tr>
																		<td><?= $valores_nuevos[0]["tipo_docu_identidad"] ?></td>
																		<td><?= $valores_nuevos[0]["num_docu"] ?></td>
																	</tr>
																<?php
																}
																?>
																<tr>
																	<td>Número de RUC</td>
																	<td><?= $valores_nuevos[0]["num_ruc"] ?></td>
																</tr>

																<tr>
																	<td>Domicilio del propietario</td>
																	<td><?= $valores_nuevos[0]["direccion"] ?></td>
																</tr>

																<tr>
																	<td>Representante Legal</td>
																	<td><?= $valores_nuevos[0]["representante_legal"] ?></td>
																</tr>

																<tr>
																	<td>N° de Partida Registral de la empresa</td>
																	<td><?= $valores_nuevos[0]["num_partida_registral"] ?></td>
																</tr>

																<tr>
																	<td>Persona de contacto</td>
																	<td><?= $valores_nuevos[0]["contacto_nombre"] ?></td>
																</tr>

																<tr>
																	<td>Teléfono de la persona de contacto</td>
																	<td><?= $valores_nuevos[0]["contacto_telefono"] ?></td>
																</tr>

																<tr>
																	<td>E-mail de la persona de contacto</td>
																	<td><?= $valores_nuevos[0]["contacto_email"] ?></td>
																</tr>

															</tbody>
														</table>
													<?php
													}

													if ($row["nombre_menu_usuario"] == 'Beneficiario') {
														$query = "
															SELECT b.id,
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
														WHERE b.id = " . $row["valor_int"];

														$valores_originales = [];
														$valores_nuevos = [];
														$list_query = $mysqli->query($query);
														while ($li = $list_query->fetch_assoc()) {
															if ($li["id"] == $row["valor_int"]) {
																$valores_nuevos[] = $li;
															}
														}

														$beneficiario_id = $valores_nuevos[0]["id"];
														$ben_tipo_persona = $valores_nuevos[0]["tipo_persona"];
														$ben_tipo_docu_identidad = $valores_nuevos[0]["tipo_docu_identidad"];
														$ben_num_docu = $valores_nuevos[0]["num_docu"];
														$ben_nombre = $valores_nuevos[0]["nombre"];
														$ben_direccion = '';
														$ben_forma_pago = $valores_nuevos[0]["forma_pago"];
														$ben_banco = $valores_nuevos[0]["banco"];
														$ben_num_cuenta_bancaria = $valores_nuevos[0]["num_cuenta_bancaria"];
														$ben_num_cuenta_cci = $valores_nuevos[0]["num_cuenta_cci"];
														$ben_tipo_monto_id = $valores_nuevos[0]["tipo_monto_id"];


														$ben_monto_beneficiario = number_format($valores_nuevos[0]["monto"], 2, '.', ',');

													?>
														<br>
														<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">
															<thead>

																<tr>
																	<th colspan="2" style="text-align: center; vertical-align: middle;">Eliminar Beneficiario <?= $codigo_contrato ?></th>
																</tr>

																<tr>
																	<th>Campo</th>
																	<th>Nuevo Valor</th>

																</tr>

															</thead>
															<tbody>

																<tr>
																	<td>Tipo de Persona</td>
																	<td><?= $ben_tipo_persona ?></td>
																</tr>

																<tr>
																	<td>Nombre</td>
																	<td><?= $ben_nombre ?></td>
																</tr>

																<tr>
																	<td>Tipo de Documento de Identidad</td>
																	<td><?= $ben_tipo_docu_identidad ?></td>
																</tr>


																<tr>
																	<td>Número de Documento de Identidad</td>
																	<td><?= $ben_num_docu ?></td>
																</tr>

																<tr>
																	<td>Tipo de forma de pago</td>
																	<td><?= $ben_forma_pago ?></td>
																</tr>

																<tr>
																	<td>Nombre del Banco</td>
																	<td><?= $ben_banco ?></td>
																</tr>

																<tr>
																	<td>N° de la cuenta bancaria</td>
																	<td><?= $ben_num_cuenta_bancaria ?></td>
																</tr>

																<tr>
																	<td>N° de CCI bancario</td>
																	<td><?= $ben_num_cuenta_cci ?></td>
																</tr>

																<tr>
																	<td>Monto</td>
																	<td><?= $ben_monto_beneficiario ?></td>
																</tr>



															</tbody>
														</table>
													<?php
													}

													if ($row["nombre_menu_usuario"] == 'Responsable IR') {
														$query = "SELECT 
														r.id,
														r.contrato_id,
														r.tipo_documento_id,
														r.num_documento,
														r.nombres,
														r.estado_emisor,
														r.porcentaje,
														r.status,
														r.user_created_id,
														r.created_at,
														td.nombre AS tipo_documento
													FROM cont_responsable_ir as r
													LEFT JOIN cont_tipo_docu_identidad AS td ON td.id = r.tipo_documento_id
													WHERE r.id = " . $row["valor_int"];


														$list_query = $mysqli->query($query);
														while ($li = $list_query->fetch_assoc()) {
															$valor_nuevo = $li;
														}
													?>
														<br>
														<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">
															<thead>

																<tr>
																	<th colspan="2" style="text-align: center; vertical-align: middle;">Eliminar Responsable IR <?= $codigo_contrato ?></th>
																</tr>

																<tr>
																	<th>Campo</th>
																	<th>Nuevo Valor</th>
																</tr>

															</thead>
															<tbody>

																<tr>
																	<td>Tipo de Documento de Identidad</td>
																	<td><?= $valor_nuevo["tipo_documento"] ?></td>
																</tr>

																<tr>
																	<td>Nro Documento</td>
																	<td><?= $valor_nuevo["num_documento"] ?></td>
																</tr>

																<tr>
																	<td>Nombres</td>
																	<td><?= $valor_nuevo["nombres"] ?></td>
																</tr>

																<tr>
																	<td>Porcentaje</td>
																	<td><?= $valor_nuevo["porcentaje"] ?></td>
																</tr>

															</tbody>
														</table>
													<?php
													}

													if ($row["nombre_menu_usuario"] == 'Suministro') {
														$query = "SELECT s.id, s.tipo_servicio_id, sp.nombre AS tipo_servicio, s.nro_suministro, s.tipo_compromiso_pago_id, ps.nombre AS tipo_compromiso, s.monto_o_porcentaje
													FROM cont_inmueble_suministros AS s
													LEFT JOIN cont_tipo_pago_servicio AS ps ON ps.id = s.tipo_compromiso_pago_id
													LEFT JOIN cont_tipo_servicio_publico AS sp ON sp.id = s.tipo_servicio_id
													INNER JOIN cont_inmueble AS i ON i.id = s.inmueble_id
													INNER JOIN cont_contrato_detalle AS cd ON cd.id = i.contrato_detalle_id
													WHERE s.id = " . $row["valor_int"];


														$list_query = $mysqli->query($query);
														while ($li = $list_query->fetch_assoc()) {
															$valor_nuevo = $li;
														}
													?>
														<br>
														<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">
															<thead>

																<tr>
																	<th colspan="2" style="text-align: center; vertical-align: middle;">Eliminar Suministro <?= $codigo_contrato ?></th>
																</tr>

																<tr>
																	<th>Campo</th>
																	<th>Nuevo Valor</th>
																</tr>

															</thead>
															<tbody>

																<tr>
																	<td>Tipo de Servicio</td>
																	<td><?= $valor_nuevo["tipo_servicio"] ?></td>
																</tr>

																<tr>
																	<td>N° de Suministro</td>
																	<td><?= $valor_nuevo["nro_suministro"] ?></td>
																</tr>

																<tr>
																	<td>Compromiso de pago</td>
																	<td><?= $valor_nuevo["tipo_compromiso"] ?></td>
																</tr>

																<tr>
																	<td>Monto/Porcentaje</td>
																	<td><?= $valor_nuevo["monto_o_porcentaje"] ?></td>
																</tr>

															</tbody>
														</table>
											<?php
													}
												}
											}

											?>

											<br>

											<?php
											if ($procesado == 0 && $area_id == '33' && $cancelado_id != 1) {
											?>

												<p><b>Adenda N° <?php echo $codigo; ?> - Estado Legal</b></p>

												<table class="table table-bordered table-hover">
													<tr>
														<td>
															<select id="adenda_estado_solicitud_<?php echo $adenda_id; ?>" class="form-control">
																<?php
																$query = "SELECT * FROM cont_estado_solicitud WHERE status = 1";
																$list_query = $mysqli->query($query);
																$list = [];

																while ($li = $list_query->fetch_assoc()) {
																	$nombre_estado_solicitud = $li["id"] == 1 ? '- Seleccione -' : $li["nombre"];
																?>
																	<option <?= $estado_solicitud_id == $li["id"] ? 'selected' : ''; ?> value="<?= $li["id"] == 1 ? '' : $li["id"]; ?>"><?= $nombre_estado_solicitud; ?></option>
																<?php
																}
																?>
															</select>
														</td>
														<td>
															<button onclick="sec_contrato_detalle_solicitudv2_guardar_estado_solicitud_adenda(<?php echo $adenda_id ?>)" class="btn btn-success form-control" type="button"><span class="glyphicon glyphicon-floppy-disk"></span> Guardar Estado</button>
														</td>
													</tr>
												</table>

												<p><b>Adenda N° <?php echo $codigo; ?> - Agregar Adenda Firmada</b></p>

												<form id="form_adenda_firmada_<?= $adenda_id ?>" name="form_contrato_firmado_<?= $adenda_id ?>" method="POST" enctype="multipart/form-data" autocomplete="off">
													<div class="row">
														<div class="col-md-4">
															<div class="form-group">
																<div class="control-label">Fecha de Aplicación:</div>
																<input type="text" readonly class="form-control text-center fecha_detalle_arrendemiento_datepicker" id="adenda_fecha_aplicacion_<?= $adenda_id ?>" name="adenda_fecha_aplicacion_<?= $adenda_id ?>" required>
															</div>
														</div>
														<div class="col-md-8">
															<div class="form-group">
																<input type="hidden" name="adenda_id_<?= $adenda_id ?>" id="adenda_id_<?= $adenda_id ?>" value="<?php echo $adenda_id; ?>">
																<div class="control-label">Seleccione la adenda firmada:</div>
																<input class="form-control" type="file" id="adenda_firmada_<?= $adenda_id ?>" name="adenda_firmada_<?= $adenda_id ?>" required accept=".jpg, .jpeg, .png, .pdf">
															</div>
														</div>
														<div class="col-md-12">
															<div class="form-group">
																<div class="control-label"><br></div>
																<button type="button" class="btn btn-success btn-xs btn-block" id="btn_guardar_adenda_firmada" onclick="sec_contrato_detalle_solicitud_locacion_servicio_guardar_adenda_firmada(<?= $adenda_id ?>)">
																	<i class="icon fa fa-plus"></i>
																	<span id="demo-button-text">Agregar adenda firmada</span>
																</button>
															</div>
														</div>
													</div>


													<div style="margin-right: 10px; margin-left: 5px; display: none;" id="div_adenda_mensaje">
														<br>
														<div class="form-group">
															<div class="alert alert-danger" role="alert">
																<strong id="adendas_mensaje"></strong>
															</div>
														</div>
													</div>
												</form>

												<?php
											}

											if ($requiere_aprobacion_id == 1 && $aprobado_estado_id === "" && $procesado == 0 && $cancelado_id != 1) {
												$boton_aprobacion_gerencia = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' AND sub_sec_id = '$sub_sec_id' LIMIT 1")->fetch_assoc();
												$id_boton_aprobacion_gerencia = $boton_aprobacion_gerencia["id"];

												if (array_key_exists($id_boton_aprobacion_gerencia, $usuario_permisos) && in_array("validaBotonSolicitud", $usuario_permisos[$id_boton_aprobacion_gerencia])) {
												?>

													<p><b>Adenda N° <?php echo $codigo; ?> - Aprobación de Gerencia:</b></p>

													<div>
														<div style="margin-right: 0px; margin-left: 0px; min-height: 170px;">

															<form id="form_aprobar_adenda_por_gerencia" name="form_aprobar_adenda_por_gerencia" method="POST" enctype="multipart/form-data" autocomplete="off">

																<div class="col-xs-12 col-md-6 col-lg-6" style="padding-left: 0px;">
																	<button type="button" class="btn btn-success btn-xs btn-block col-md-6" style="height: 30px;" onclick="sec_contrato_detalle_solicitudv2_aprobar_adenda(1, <?= $adenda_id ?>);">
																		<span id="demo-button-text">
																			<i class="glyphicon glyphicon-saved"></i>
																			Aceptar solicitud
																		</span>
																	</button>
																</div>

																<div class="col-xs-12 col-md-6 col-lg-6" style="padding-right: 0px;">
																	<button type="button" class="btn btn-danger btn-xs btn-block" style="height: 30px;" onclick="sec_contrato_detalle_solicitudv2_aprobar_adenda(0, <?= $adenda_id ?>)">
																		<span id="demo-button-text">
																			<i class="glyphicon glyphicon-remove-sign"></i>
																			Rechazar solicitud
																		</span>
																	</button>
																</div>

															</form>

															<br /><br />

															<div>
																<b>Adenda N° <?php echo $codigo; ?> - Observación:</b>
																<div id="div_observaciones_adenda_gerencia_<?= $adenda_id ?>" class="timeline" style="font-size: 11px;">
																	<?php
																	$html = '';

																	$sql = "SELECT 
																o.observaciones,
																concat(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, '')) AS usuario,
																ar.nombre AS area,
																o.user_created_id,
																o.created_at
																FROM cont_observaciones o
																INNER JOIN tbl_usuarios u ON o.user_created_id = u.id
																INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
																INNER JOIN tbl_areas ar ON p.area_id = ar.id
																WHERE o.contrato_id = " . $contrato_id . "
																AND o.adenda_id = " . $adenda_id . "
																AND o.status = 1
																AND o.user_created_id IN (SELECT ud.user_id FROM cont_usuarios_directores ud WHERE ud.status = 1)
																ORDER BY o.created_at ASC";

																	$query = $mysqli->query($sql);
																	$row_count = $query->num_rows;

																	if ($row_count > 0) {
																		while ($row = $query->fetch_assoc()) {
																			$date = date_create($row["created_at"]);
																			$created_at = date_format($date, "d/m/Y h:i a");

																			if ($row["user_created_id"] == $login['id']) {
																				// ESTE DIV ES PARA EL USUARIO LOGUEADO
																				$html .= '<div class="col-sm-offset-1 col-sm-11 caja_usuario_aprobacion alert alert-success" style="padding-bottom:10px;color:#333;box-shadow: 4px 4px 4px rgba(0,0,0,0.15);">';

																				$html .= '<div class="col-sm-8" style="margin-bottom:5px;">';
																				$html .= '<strong>' . $row["usuario"] . '(' . $row["area"] . ')</strong>';
																				$html .= '</div>';

																				$html .= '<div class="col-sm-4 text-right" style="color:rgba(0,0,0,0.5); font-size: 11px;">';
																				$html .= '<span class="time"><i class="fa fa-clock-o"></i> ' . $created_at . '</span>';
																				$html .= '</div>';

																				$html .= '<div class="col-xs-12" style="padding-top: 5px; background:rgba(255,255,255,0.7); margin-bottom:2px;">';
																				$html .= $row["observaciones"];
																				$html .= '</div>';

																				$html .= '</div>';
																			} else {
																				// ESTE DIV ES PARA OTROS USUARIOS
																				$html .= '<div class="col-sm-11 caja_usuario_creador alert alert-info" style="padding-bottom:10px;color:#333;box-shadow: 4px 4px 4px rgba(0,0,0,0.15);">';

																				$html .= '<div class="col-sm-8" style="margin-bottom:5px;">';
																				$html .= '<strong>' . $row["usuario"] . '(' . $row["area"] . ')</strong>';
																				$html .= '</div>';

																				$html .= '<div class="col-sm-4 text-right" style="color:rgba(0,0,0,0.5); font-size: 11px;">';
																				$html .= '<span class="time"><i class="fa fa-clock-o"></i> <strong>' . $created_at . '</strong></span>';
																				$html .= '</div>';

																				$html .= '<div class="col-xs-12" style="padding-top: 5px; background:rgba(255,255,255,0.7); margin-bottom:2px;">';
																				$html .= $row["observaciones"];
																				$html .= '</div>';

																				$html .= '</div>';
																			}
																		}
																		echo $html;
																	}
																	?>
																</div>
																<textarea rows="3" id="observaciones_adenda_gerencia" placeholder="Ingrese sus observaciones" style="width: 100%"></textarea>
																<button class="btn btn-warning btn-xs btn-block" type="submit" onclick="sec_contrato_detalle_solicitudv2_guardar_observaciones_proveedores_adenda_gerencia(<?= $adenda_id ?>);">
																	<i class="fa fa-plus"></i> Enviar Observación
																</button>
															</div>



														</div>


													</div>



												<?php
												}
											}
											if ($cancelado_id == 1) {

												?>
												<p style='color: red; font-weight: bold;'>No se puede aceptar la solicitud de la adenda debido a que está cancelada.</p>";
											<?php
											}
											?>
											<br>
											<hr>
										<?php
										}

										?>

									</div>
								</div>
							</div>
						<?php
						}
						?>
						<!-- FIN PANEL ADENDAS -->


						<!-- INICIO PANEL OBSERVACIONES -->
						<?php
						if ((array_key_exists($menu_consultar, $usuario_permisos) && in_array("request", $usuario_permisos[$menu_consultar]))) {
						?>
							<div class="panel">
								<div class="panel-heading" role="tab" id="browsers-this-month-heading">
									<div class="panel-title">
										<a href="#browsers-this-month" class="collapsed" role="button" data-toggle="collapse"
											data-parent="#accordion" aria-expanded="true" aria-controls="browsers-this-month">
											Observaciones
										</a>
									</div>
								</div>

								<div id="browsers-this-month" class="panel-collapse collapse" role="tabpanel" aria-labelledby="browsers-this-month-heading">
									<div class="panel-body">
										<div style="margin-right: 10px; margin-left: 5px;">
											<div id="div_observaciones" class="timeline" style="font-size: 11px;">
											</div>

											<textarea rows="3" id="contrato_observaciones" placeholder="Ingrese sus observaciones" style="width: 100%"></textarea>

											<textarea rows="3" id="correos_adjuntos" placeholder="Ingrese correos adjuntos" style="width: 100%"></textarea>

											<button class="btn btn-success btn-xs btn-block" type="submit" onclick="sec_contrato_detalle_solicitudv2_guardar_observaciones();">
												<i class="fa fa-plus"></i> Agregar y Notificar la Observación
											</button>
										</div>
									</div>
								</div>
							</div>
							<?php
						} else {
							if (($area_id == 33 and $cargo_id != 25) || $usuario_created_id == $login['id']) {
							?>
								<div class="panel">
									<div class="panel-heading" role="tab" id="browsers-this-month-heading">
										<div class="panel-title">
											<a href="#browsers-this-month" class="collapsed" role="button" data-toggle="collapse"
												data-parent="#accordion" aria-expanded="true" aria-controls="browsers-this-month">
												Observaciones
											</a>
										</div>
									</div>

									<div id="browsers-this-month" class="panel-collapse collapse" role="tabpanel" aria-labelledby="browsers-this-month-heading">
										<div class="panel-body">
											<div style="margin-right: 10px; margin-left: 5px;">
												<div id="div_observaciones" class="timeline" style="font-size: 11px;">
												</div>

												<textarea rows="3" id="contrato_observaciones" placeholder="Ingrese sus observaciones" style="width: 100%"></textarea>

												<textarea rows="3" id="correos_adjuntos" placeholder="Ingrese correos adjuntos" style="width: 100%"></textarea>

												<button class="btn btn-success btn-xs btn-block" type="submit" onclick="sec_contrato_detalle_solicitudv2_guardar_observaciones();">
													<i class="fa fa-plus"></i> Agregar y Notificar la Observación
												</button>
											</div>
										</div>
									</div>
								</div>
						<?php
							}
						}
						?>
						<!-- FIN PANEL OBSERVACIONES -->

						<!-- INICIO PANEL CONTRATO FIRMADO -->
						<!-- if (($area_id == '33' and $cargo_id != 25) or true) { -->
						<?php if (($etapa_id == 1 and $estado_aprobacion) or ($etapa_id == 5)) { ?>
							<div class="panel">
								<div class="panel-heading" role="tab" id="browsers-this-day-heading">
									<div class="panel-title">
										<a href="#browsers-this-day" class="collapsed" role="button" data-toggle="collapse"
											data-parent="#accordion" aria-expanded="true" aria-controls="browsers-this-day">
											Contrato firmado
										</a>
									</div>
								</div>
								<div id="browsers-this-day" class="panel-collapse collapse" role="tabpanel" aria-labelledby="browsers-this-day-heading">
									<div class="panel-body">
										<?php
										$list_detalle_contrato = $mysqli->query($query_detalle_contrato);
										while ($sel_cont_det_cf = $list_detalle_contrato->fetch_assoc()) {
											$contrato_detalle_id = $sel_cont_det_cf['id'];
											$cod_contrato = $sel_cont_det_cf['codigo'];
											$num_contrato = (int) $sel_cont_det_cf['codigo'];

											$det_condicion_economica_id = $sel_cont_det_cf['condicion_economica_id'];
											$det_plazo_id = $sel_cont_det_cf['plazo_id'];
											$det_fecha_inicio = $sel_cont_det_cf['fecha_inicio'];
											$det_fecha_fin = $sel_cont_det_cf['fecha_fin'];
											$det_fecha_suscripcion = $sel_cont_det_cf['fecha_suscripcion'];
											$det_renovacion_automatica_id = $sel_cont_det_cf['renovacion_automatica'];
											$det_renovacion_automatica = $det_renovacion_automatica_id == 1 ? 'SI' : 'NO';


											$det_nombre_plazo = '';
											if ($det_plazo_id == 1) {
												$det_nombre_plazo = 'Periodo Definido';
											} else if ($det_plazo_id == 2) {
												$det_nombre_plazo = 'Periodo Indefinido';
											}

											$edicion_btn_contrato_firmado = true;
											$sel_query_info_contrato_firmado = $mysqli->query("
											SELECT 
												c.contrato_id,
												c.nombre_tienda,
												ce.condicion_economica_id,
												ce.fecha_inicio,
												ce.fecha_fin,
												CONCAT(IFNULL(tpa.nombre, ''),
														' ',
														IFNULL(tpa.apellido_paterno, ''),
														' ',
														IFNULL(tpa.apellido_materno, '')) AS usuario_aprobado
											FROM
												cont_contrato c
													LEFT JOIN
												cont_condicion_economica ce ON c.contrato_id = ce.contrato_id
												LEFT JOIN
												tbl_usuarios tu ON ce.usuario_contrato_aprobado_id = tu.id
												LEFT JOIN
												tbl_personal_apt tpa ON tu.personal_id = tpa.id
												LEFT JOIN
												cont_locacion loc ON loc.idcontrato = c.contrato_id
											WHERE  c.contrato_id = " . $contrato_id . " 
											");
											$cantReg = mysqli_num_rows($sel_query_info_contrato_firmado);
											if ($cantReg > 0 && $etapa_id == 5) {
												while ($sel = $sel_query_info_contrato_firmado->fetch_assoc()) {
													$nombre_tienda = $sel["nombre_tienda"];
													$usuario_aprobado = $sel["usuario_aprobado"];
													$condicion_economica_id = $sel['condicion_economica_id'];
													$contrato_inicio_fecha = $sel['fecha_inicio'];
													$contrato_fin_fecha = $sel['fecha_fin'];
										?>
													<table class="table table-responsive table-hover no-mb" style="font-size: 10px;">
														<tbody>
															<tr>
																<th class="text-center" colspan=2><b>Contrato</b></th>

															</tr>

															<tr style="text-transform: none;">
																<td><b>Usuario que cargo el contrato firmado:</b></td>
																<td>
																	<?php echo $usuario_aprobado; ?>
																</td>
															</tr>
															<?php
															$sel_contrato_firmado = $mysqli->query(
																"
													SELECT 
														archivo_id,
														contrato_id,
														tipo_archivo_id,
														nombre,
														extension,
														ruta,
														size,
														user_created_id,
														status,
														created_at
													FROM
														cont_archivos
													WHERE
														tipo_archivo_id = 16
														AND status = 1
														AND contrato_detalle_id = " . $contrato_detalle_id . "
														AND contrato_id = " . $contrato_id
															);
															$num_rows = mysqli_num_rows($sel_contrato_firmado);
															if ($num_rows > 0) {
																$row = $sel_contrato_firmado->fetch_assoc();
																$ruta = str_replace("/var/www/html", "", $row["ruta"]);

																$fecha_actual = date('Y-m-d H:i:s');
																$fecha_actual = date("Y-m-d H:i:s", strtotime($fecha_actual . "- 1 days"));
																$fecha_contrato = date("Y-m-d H:i:s", strtotime($row["created_at"]));
																if (($area_id == 33 &&  $cargo_id != 25 && $etapa_id == 5) || $permiso_editar_contrato_firmado) {
																	$edicion_btn_contrato_firmado = true;
																}
															?>
																<tr style="text-transform: none;">
																	<td><b>Fecha de carga del contrato firmado:</b></td>
																	<td>
																		<?php echo $row["created_at"]; ?>
																	</td>
																</tr>

																<tr>
																	<td><b>Contrato - Fecha de Inicio</b></td>
																	<td><?php echo $det_fecha_inicio; ?></td>
																	<?php if ($edicion_btn_contrato_firmado) { ?>
																		<td>


																		</td>
																	<?php } ?>
																</tr>
																<?php

																?>
																<tr>
																	<td><b>Contrato - Fecha de Fin</b></td>
																	<td><?php echo $det_fecha_fin; ?></td>
																	<?php if ($edicion_btn_contrato_firmado) { ?>
																		<td>

																		</td>
																	<?php } ?>
																</tr>
																<?php

																?>
																<tr>
																	<td style="width: 250px;"><b>Fecha de suscripción del contrato</b></td>
																	<td><?php echo $det_fecha_suscripcion; ?></td>
																	<?php if ($edicion_btn_contrato_firmado) { ?>
																		<td style="width: 75px;">

																		</td>
																	<?php } ?>
																</tr>


																<tr style="text-transform: none;">
																	<td><b>Visualizar el contrato firmado:</b></td>
																	<td>
																		<a
																			onclick="sec_contrato_detalle_solicitudv2_ver_documento_en_visor('<?php echo $ruta; ?>','<?php echo trim($row["nombre"]); ?>','<?php echo trim($row["extension"]); ?>','CONTRATO FIRMADO');"
																			class="btn btn-success btn-xs"
																			data-toggle="tooltip"
																			data-placement="top">
																			<span class="fa fa-eye"></span> Ver contrato firmado
																		</a>
																	</td>
																</tr>
															<?php
															}
															?>

														</tbody>
													</table>
													<hr>
												<?php
												}
											} elseif ($cancelado_id != 1) {
												?>
												<table class="table table-responsive table-hover no-mb" style="font-size: 10px;">
													<tbody>
														<tr>
															<th class="text-center" colspan=2><b>Contrato </b></th>

														</tr>
													</tbody>
												</table>
												<form id="form_contrato_firmado_<?= $contrato_detalle_id ?>" name="form_contrato_firmado_<?= $contrato_detalle_id ?>" method="POST" enctype="multipart/form-data" autocomplete="off">

													<div class="col-xs-12 col-md-6 col-lg-6" style="padding: 5px">
														<div class="form-group">
															<label>
																Fecha Inicio:
															</label>

															<div class="input-group col-xs-12">
																<input
																	type="text"
																	class="form-control fecha_detalle_arrendemiento_datepicker"
																	id="cont_detalle_contrato_firmado_fecha_incio_param_<?= $contrato_detalle_id ?>"
																	value="<?php echo $det_fecha_inicio; ?>"
																	readonly="readonly">
																<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="cont_detalle_proveedor_contrato_firmado_fecha_incio_param"></label>
															</div>
														</div>
													</div>

													<div class="col-xs-12 col-md-6 col-lg-6 div_vig_def_<?= $contrato_detalle_id ?>" style="padding: 5px; <?= $det_plazo_id == 2 ? 'display:none;' : '' ?>">
														<div class="form-group">
															<label>
																Fecha Fin:
															</label>
															<div class="input-group col-xs-12">
																<input
																	type="text"
																	class="form-control fecha_detalle_arrendemiento_datepicker"
																	id="cont_detalle_contrato_firmado_fecha_vencimiento_param_<?= $contrato_detalle_id ?>"
																	value="<?php echo $det_fecha_fin; ?>"
																	readonly="readonly">
																<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param"></label>
															</div>
														</div>
													</div>

													<div class="col-xs-12 col-md-6 col-lg-6" style="padding: 5px">
														<div class="form-group">
															<label>
																Fecha Suscripción:
															</label>
															<div class="input-group col-xs-12">

																<input
																	type="text"
																	class="form-control fecha_detalle_arrendemiento_datepicker"
																	id="cont_detalle_contrato_firmado_fecha_suscripcion_param_<?= $contrato_detalle_id ?>"
																	value="<?php echo $det_fecha_suscripcion; ?>"
																	readonly="readonly">
																<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="cont_detalle_proveedor_contrato_firmado_fecha_suscripcion_param"></label>
															</div>
														</div>
													</div>

													<div class="col-xs-12 col-md-6 col-lg-6" style="padding: 5px">
														<div class="form-group">
															<label>Seleccione el contrato firmado (en formato pdf):</label>
															<input type="file" id="archivo_contrato_<?= $contrato_detalle_id ?>" name="archivo_contrato_<?= $contrato_detalle_id ?>" required accept=".pdf">
														</div>
													</div>
													<div style="margin-right: 10px; margin-left: 5px;">
														<button type="button" class="btn btn-success btn-xs btn-block" id="btn_guardar_contrato_firmado_<?= $contrato_detalle_id ?>" onclick="sec_contrato_detalle_solicitud_verificar_documentos_locacion('<?= $contrato_detalle_id ?>')">
															<i class="icon fa fa-plus"></i>
															<span id="demo-button-text">Agregar contrato firmado</span>
														</button>
													</div>
												</form>
												<hr>
										<?php
											}
										}
										?>
									</div>
								</div>
							</div>
						<?php } ?>
						<!-- FIN PANEL CONTRATO FIRMADO -->

						<!-- INICIO PANEL RESOLUCION CONTRATO -->
						<!-- if (($area_id == '33' and $cargo_id != 25) || $area_id == 6 || $permiso_cambiar_estado_contrato) -->
						<?php if (($etapa_id == 1 && $estado_aprobacion == 1) || $etapa_id == 5) { ?>
							<div class="panel">
								<div class="panel-heading" role="tab" id="browsers-this-estado-contrato-heading">
									<div class="panel-title">
										<a href="#browsers-this-estado-contrato" class="collapsed" role="button" data-toggle="collapse"
											data-parent="#accordion" aria-expanded="true" aria-controls="browsers-this-estado-contrato">
											Estado de Contrato
										</a>
									</div>
								</div>
								<div id="browsers-this-estado-contrato" class="panel-collapse collapse" role="tabpanel" aria-labelledby="browsers-this-estado-contrato-heading">
									<div class="panel-body">

										<?php
										$sel_query_estado_contrato = $mysqli->query("
									SELECT ce.id,
									CASE
										WHEN ce.estado = 1 THEN 'Activo'
										WHEN ce.estado = 2 THEN 'Inactivo'
										ELSE ''
									END AS estado,
									ce.created_at AS fecha,
									ce.motivo,
									CONCAT(IFNULL(tpa.nombre, ''),' ',IFNULL(tpa.apellido_paterno, ''),	' ',	IFNULL(tpa.apellido_materno, '')) AS usuario

									FROM cont_contrato_estado AS ce
									INNER JOIN tbl_usuarios tu ON ce.user_created_id = tu.id
									INNER JOIN tbl_personal_apt tpa ON tu.personal_id = tpa.id
									WHERE ce.contrato_id = " . $contrato_id . "
									ORDER BY ce.id ASC");
										$cantRegEstado = mysqli_num_rows($sel_query_estado_contrato);
										?>
										<?php
										if ($cantRegEstado > 0) {
											$index_estado = 1;
											while ($sel = $sel_query_estado_contrato->fetch_assoc()) {
										?>
												<table class="table table-responsive table-hover no-mb mt-1" style="font-size: 10px;">
													<tbody>
														<tr style="text-transform: none;">
															<td colspan="2" class="text-center"><b>Cambio de Estado #<?= $index_estado ?></b></td>
														</tr>
														<tr style="text-transform: none;">
															<td style="width:30%"><b>Estado:</b></td>
															<td style="width:70%"><?= $sel['estado'] ?></td>
														</tr>
														<tr style="text-transform: none;">
															<td style="width:30%"><b>Motivo:</b></td>
															<td style="width:70%; white-space: pre-line;"><?= $sel['motivo'] ?></td>
														</tr>
														<tr style="text-transform: none;">
															<td style="width:30%"><b>Fecha:</b></td>
															<td style="width:70%"><?= $sel['fecha'] ?></td>
														</tr>
														<tr style="text-transform: none;">
															<td style="width:30%"><b>Usuario:</b></td>
															<td style="width:70%"><?= $sel['usuario'] ?></td>
														</tr>
													</tbody>
												</table>
												<br>
										<?php
												$index_estado++;
											}
										}
										?>
										<hr>
										<table class="table table-responsive table-hover no-mb" style="font-size: 10px;">
											<tbody>
												<tr style="text-transform: none;">
													<td style="width:30%"><b>Estado del Contrato:</b></td>
													<td style="width:70%">
														<select id="contrato_estado" class="form-control text-center text-white <?= $estado_contrato == 1 ? 'bg-success' : 'bg-danger' ?>">
															<option class="text-white" <?= $estado_contrato == 1 ? 'selected' : '' ?> value="1">Activo</option>
															<option class="text-white" <?= $estado_contrato == 2 ? 'selected' : '' ?> value="2">Inactivo</option>
														</select>
													</td>
												</tr>
												<tr style="text-transform: none;">
													<td style="width:30%"><b>Motivo:</b></td>
													<td style="width:70%">
														<textarea id="contrato_motivo" rows="5" class="form-control"></textarea>
													</td>
												</tr>
												<tr style="text-transform: none;">
													<td></td>
													<td class="text-center">
														<button onclick="sec_contrato_detalle_cambiar_estado()" type="button" class="btn btn-info form-control">Guardar</button>
													</td>
												</tr>
											</tbody>
										</table>
									</div>
								</div>
							</div>
						<?php } ?>
						<!-- FIN PANEL RESOLUCION CONTRATO -->

						<!-- INICIO PANEL ESTADO DE SOLICITUD -->

						<?php if (($etapa_id == 1 && $estado_aprobacion == 0)) { ?>
							<!-- if ((($area_id == '33') && ($etapa_id == 1) && ($estado_aprobacion == 0))) -->
							<!-- AREA LEGAL Y (GERENTE, JEFE, ASISTENTE) -->
							<div class="panel">
								<div class="panel-heading" role="tab" id="browsers-this-day-heading">
									<div class="panel-title">
										<a href="#browsers-estado-solicitud" class="collapsed" role="button" data-toggle="collapse"
											data-parent="#accordion" aria-expanded="true" aria-controls="browsers-estado-solicitud">
											Estado de Solicitud
										</a>
									</div>
								</div>
								<div id="browsers-estado-solicitud" class="panel-collapse collapse" role="tabpanel" aria-labelledby="browsers-this-day-heading">
									<div class="panel-body">
										<?php
										$sel_query = $mysqli->query("SELECT c.estado_solicitud, c.motivo_estado_na FROM cont_contrato c WHERE c.contrato_id = $contrato_id");
										while ($sel = $sel_query->fetch_assoc()) {
											$estado_solicitud = $sel["estado_solicitud"];
											$motivo_estado_na = $sel["motivo_estado_na"];

											if (empty($estado_solicitud)) {
												$estado_solicitud = 1;
											}
										}
										$query = "SELECT * FROM cont_estado_solicitud WHERE status = 1";
										?>
										<table class="table table-bordered table-hover">
											<tr>
												<td><b>Estado</b></td>
												<td>
													<select id="estado_solicitud" class="form-control">
														<?php
														$list_query = $mysqli->query($query);
														$list = [];
														while ($li = $list_query->fetch_assoc()) {
															$nombre_estado_solicitud = $li["id"] == 1 ? 'Seleccione' : $li["nombre"];
														?>
															<option <?= $estado_solicitud == $li["id"] ? 'selected' : ''; ?> value="<?= $li["id"] == 1 ? '' : $li["id"]; ?>"><?= $nombre_estado_solicitud; ?></option>
														<?php
														}
														?>
													</select>
													<div id="divNoAplica" style="display: none;">
														<div class="form-group">
															<div class="control-label">Motivo</div>
															<input type="text"
																name="motivo_estado_na" maxlength="100"
																id="motivo_estado_na" class="filtro" style="width: 100%; height: 30px; float: left; text-align: left;">
														</div>
													</div>
												</td>

												<td>
													<button onclick="sec_contrato_detalle_solicitudv2_guardar_estado_solicitud()" class="btn btn-success form-control" type="button"><span class="glyphicon glyphicon-floppy-disk"></span> Guardar</button>
												</td>

											</tr>
											<?php
											if ($motivo_estado_na == '' or  $estado_solicitud <> '4') {
											} else {
												echo '		
													<tr>
														<td><b>Motivo</b></td> 
														<td colspan=2>
															<div class="form-group"> ' . $motivo_estado_na . '</div>
														</td>
													</tr>';
											}

											?>
										</table>
									</div>
								</div>
							</div>
							<!-- FIN PANEL ESTADO DE SOLICITUD -->
						<?php
						}
						?>

						<!-- INICIO PANEL ESCISIONES -->
						<?php if (($area_id == '33' || (array_key_exists($menu_consultar, $usuario_permisos) && in_array("ver_adendas_escision", $usuario_permisos[$menu_consultar]))) && ($etapa_id == 5)) { ?>
							<?php
							$query_escision = "SELECT ces.id, ces.contrato_id,
								CONCAT(IFNULL(p.nombre, ''), ' ',  IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS usuario,
								rz1.nombre AS empresa_anterior,
								rz2.nombre AS empresa_nueva,
								ces.fecha AS fecha_escision,
								a.nombre as nombre_archivo,
								a.extension as extension_archivo,
								a.ruta,
								ces.created_at
								
								FROM cont_contrato_escision AS ces
								INNER JOIN tbl_razon_social AS rz1 ON rz1.id = ces.empresa_anterior_id
								INNER JOIN tbl_razon_social AS rz2 ON rz2.id = ces.empresa_nueva_id
								INNER JOIN tbl_usuarios u ON ces.user_created_id = u.id
								INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
								LEFT JOIN cont_archivos AS a ON a.archivo_id = ces.archivo_id
								WHERE ces.status = 1 AND ces.contrato_id = " . $contrato_id . " ORDER BY ces.created_at DESC";
							$sel_query = $mysqli->query($query_escision);
							$row_cnt_esc = $sel_query->num_rows;

							if ($row_cnt_esc > 0) {
							?>
								<div class="panel">
									<div class="panel-heading" role="tab" id="browsers-this-day-heading">
										<div class="panel-title">
											<a href="#browsers-escision" class="collapsed" role="button" data-toggle="collapse"
												data-parent="#accordion" aria-expanded="true" aria-controls="browsers-escision">
												Adenda de Escisión
											</a>
										</div>
									</div>
									<div id="browsers-escision" class="panel-collapse collapse" role="tabpanel" aria-labelledby="browsers-this-day-heading">
										<div class="panel-body">
											<?php
											while ($sel = $sel_query->fetch_assoc()) {
											?>

												<table class="table table-responsive table-hover no-mb" style="font-size: 10px;">
													<tbody>
														<tr style="text-transform: none;">
															<td><b>Solicitante</b></td>
															<td>
																<?php echo $sel['usuario']; ?>
															</td>
														</tr>
														<tr style="text-transform: none;">
															<td><b>Fecha de la solicitud</b></td>
															<td>
																<?= $sel['created_at']; ?>
															</td>
														</tr>
														<tr style="text-transform: none;">
															<td><b>Empresa Anterior</b></td>
															<td>
																<?= $sel['empresa_anterior']; ?>
															</td>
														</tr>
														<tr style="text-transform: none;">
															<td><b>Empresa Nueva</b></td>
															<td>
																<?= $sel['empresa_nueva']; ?>
															</td>
														</tr>
														<tr style="text-transform: none;">
															<td><b>Fecha de Escisión</b></td>
															<td>
																<?= $sel['fecha_escision']; ?>
															</td>
														</tr>
														<?php
														$ruta = str_replace("/var/www/html", "", $sel["ruta"]);
														?>
														<tr style="text-transform: none;">
															<td><b>Visualizar la adenda firmada:</b></td>
															<td>
																<a
																	type="button"
																	onclick="sec_contrato_detalle_solicitudv2_ver_documento_en_visor('<?php echo $ruta; ?>','<?php echo trim($sel["nombre_archivo"]); ?>','<?php echo trim($sel["extension_archivo"]); ?>','ADENDA FIRMADA');"
																	class="btn btn-success btn-xs"
																	data-toggle="tooltip"
																	data-placement="top">
																	<span class="fa fa-eye"></span> Ver adenda de Escisión firmada
																</a>
															</td>
														</tr>

													</tbody>
												</table>
												<hr>
											<?php
											}
											?>
										</div>
									</div>
								</div>

							<?php
							}
							?>
							<!-- FIN PANEL ESCISIONES -->
						<?php
						}
						?>

						<!-- INICIO PANEL EDITAR ABOGADO -->
						<?php if (($area_id == '33' and $cargo_id != 25) || $area_id == 6 || $permiso_cambiar_estado_contrato || true) { ?>
							<div class="panel">
								<div class="panel-heading" role="tab" id="browsers-this-cambiar-abogado-heading">
									<div class="panel-title">
										<a href="#browsers-this-cambiar-abogado" class="collapsed" role="button" data-toggle="collapse"
											data-parent="#accordion" aria-expanded="true" aria-controls="browsers-this-cambiar-abogado">
											Abogado
										</a>
									</div>
								</div>
								<div id="browsers-this-cambiar-abogado" class="panel-collapse collapse" role="tabpanel" aria-labelledby="browsers-this-cambiar-abogado-heading">
									<div class="panel-body">
										<table class="table table-bordered table-hover">
											<tr>
												<td><b>Abogado</b></td>
												<td><?php echo $abogado; ?></td>
												<?php if ($btn_editar_solicitud || ($etapa_id == 1 && $area_id == 33 && $cargo_id != 25)) { ?>
													<td>
														<a class="btn btn-success btn-xs"
															id="btn_editar_abogado"
															onclick="sec_contrato_detalle_solicitudv2_editar_solicitud('Generales','cont_contrato','abogado_id','Abogado','select_option','<?php echo $abogado; ?>','obtener_abogados');">
															<span class="fa fa-edit"></span> Editar
														</a>
													</td>
												<?php } ?>
											</tr>
										</table>
									</div>
								</div>
							</div>
						<?php } ?>
						<!-- FIN PANEL EDITAR ABOGADO -->
					</div>
				</div>
			</div>
		</div>

		<?php

		// if ($etapa_id == 1 && $cancelado_id != 1 && $aprobador_id == $login["id"]) {	
		if (true) {
			// if (is_null($fecha_aprobacion)) {
			if ($etapa_id == 1 && $cancelado_id != 1 && $estado_aprobacion == 0  && $fecha_aprobacion == null) {
				$boton_aprobacion_gerencia = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' AND sub_sec_id = '$sub_sec_id' LIMIT 1")->fetch_assoc();
				$id_boton_aprobacion_gerencia = $boton_aprobacion_gerencia["id"];

				if (array_key_exists($id_boton_aprobacion_gerencia, $usuario_permisos) && in_array("validaBotonSolicitud", $usuario_permisos[$id_boton_aprobacion_gerencia]) || true) {
		?>
					<div class="col-xs-12 col-md-12 col-lg-5">

						<!-- PANEL: Horizontal Form -->
						<div class="panel" id="divDetalleSolicitud_gerencia">

							<!-- Panel Body -->
							<div class="panel-body" style="padding: 5px 10px 5px 10px;">

								<!-- PANEL-GROUP: Browsers -->
								<div class="panel-group no-mb" id="accordion" role="tablist" aria-multiselectable="true">

									<!-- PANEL: CONTRATO FIRMADO -->
									<div class="panel">

										<!-- Panel Heading -->
										<div class="panel-heading" role="tab" id="browsers-aprobacion_gerencia-heading">

											<!-- Panel Title -->
											<div class="panel-title">
												<a href="#browsers-aprobacion_gerencia" class="collapsed" role="button" data-toggle="collapse"
													data-parent="#accordion" aria-expanded="true" aria-controls="browsers-aprobacion_gerencia">

													Aprobación de Gerencia
												</a>
											</div>
											<!-- /Panel Title -->

										</div>
										<!-- /Panel Heading -->

										<!-- COLLAPSE: This Week -->
										<div id="browsers-aprobacion_gerencia" class="panel-collapse collapse in" role="tabpanel"
											aria-labelledby="browsers-aprobacion_gerencia-heading">

											<!-- Panel Body -->
											<div class="panel-body">
												<div style="margin-right: 10px; margin-left: 5px;">

													<!-- <div id="div_observaciones_gerencia" class="timeline" style="font-size: 11px;"></div> -->
													<b>Observación:</b>
													<textarea rows="3" id="contrato_observaciones_proveedor_gerencia" placeholder="Ingrese sus observaciones" style="width: 100%"></textarea>
													<button class="btn btn-warning btn-xs btn-block" type="submit" onclick="sec_contrato_detalle_solicitudv2_guardar_observaciones_proveedores_gerencia();">
														<i class="fa fa-plus"></i> Enviar Observación
													</button>
												</div>

												<br>

												<form id="form_contrato_proveedor_aprobar_gerencia" name="form_contrato_proveedor_firmado" method="POST" enctype="multipart/form-data" autocomplete="off">

													<div class="col-xs-12 col-md-6 col-lg-6 item_filter">
														<button type="button" class="btn btn-success btn-xs btn-block col-md-6 cont_btn_guardar_aprobar_locacion" value="1" style="height: 30px;">
															<span id="demo-button-text">
																<i class="glyphicon glyphicon-saved"></i>
																Aceptar solicitud
															</span>
														</button>
													</div>

													<div class="col-xs-12 col-md-6 col-lg-6 item_filter">
														<button type="button" class="btn btn-danger btn-xs btn-block cont_btn_guardar_aprobar_locacion" value="0" style="height: 30px;">
															<span id="demo-button-text">
																<i class="glyphicon glyphicon-remove-sign"></i>
																Rechazar solicitud
															</span>
														</button>
													</div>
												</form>

											</div>
											<!-- /Panel Body -->

										</div>
										<!-- /COLLAPSE: This Week -->

									</div>
									<!-- PANEL: CONTRATO FIRMADO -->
								</div>
								<!-- /PANEL-GROUP: Browsers -->

							</div>
							<!-- /Panel Body -->

						</div>
						<!-- /PANEL: Horizontal Form -->

					</div>
				<?php
				}
			}
			if ($cancelado_id == 1) {
				?>

				<div class="col-xs-12 col-md-12 col-lg-5">

					<!-- PANEL: Horizontal Form -->
					<div class="panel" id="divDetalleSolicitud_gerencia">

						<!-- Panel Body -->
						<div class="panel-body" style="padding: 5px 10px 5px 10px;">

							<!-- PANEL-GROUP: Browsers -->
							<div class="panel-group no-mb" id="accordion" role="tablist" aria-multiselectable="true">

								<!-- PANEL: CONTRATO FIRMADO -->
								<div class="panel">

									<!-- Panel Heading -->
									<div class="panel-heading" role="tab" id="browsers-aprobacion_gerencia-heading">

										<!-- Panel Title -->
										<div class="panel-title">

											<p style='color: red; font-weight: bold; margin-top:10px;'>No se puede aceptar la solicitud debido a que está cancelada.</p>


										</div>
										<!-- /Panel Title -->

									</div>

								</div>
								<!-- PANEL: CONTRATO FIRMADO -->
							</div>
							<!-- /PANEL-GROUP: Browsers -->

						</div>
						<!-- /Panel Body -->

					</div>
					<!-- /PANEL: Horizontal Form -->

				</div>


		<?php
			}
		}

		?>

	</div>

</div>




<!-- INICIO MODAL EDITAR SOLICITUD -->
<div id="modal_editar_solicitud" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Editar Solicitud de Contrato Locación de Servicio</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form id="form_editar_solicitud" autocomplete="off">
						<input type="hidden" id="editar_solicitud_nombre_tabla">
						<input type="hidden" id="editar_solicitud_nombre_campo">
						<input type="hidden" id="editar_solicitud_tipo_valor">
						<input type="hidden" id="editar_solicitud_id_tabla">
						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="form-group">
								<table class="table table-bordered">
									<tr>
										<td><b>Nombre del Menú:</b></td>
										<td id="editar_solicitud_nombre_menu_usuario"></td>
									</tr>
									<tr>
										<td><b>Nombre del Campo:</b></td>
										<td id="editar_solicitud_nombre_campo_usuario"></td>
									</tr>
									<tr>
										<td><b>Valor Actual:</b></td>
										<td id="editar_solicitud_valor_actual"></td>
									</tr>
									<tr>
										<td><b>Nuevo Valor:</b></td>
										<td>
											<div id="div_editar_solicitud_valor_varchar">
												<input type="text" id="editar_solicitud_valor_varchar" class="filtro txt_filter_style" style="width: 100%;">
											</div>
											<div id="div_editar_solicitud_valor_textarea">
												<textarea id="editar_solicitud_valor_textarea" class="form-control" rows="5"></textarea>
											</div>
											<div id="div_editar_solicitud_valor_int">
												<input type="text" id="editar_solicitud_valor_int" class="filtro txt_filter_style" style="width: 100%;">
											</div>
											<div id="div_editar_solicitud_valor_date">
												<input
													type="date"
													class="form-control sec_contrato_detalle_solicitud_datepicker"
													id="editar_solicitud_valor_date"
													value="<?php echo date("d-m-Y", strtotime("+1 days")); ?>"
													style="height: 34px;">
											</div>
											<!-- readonly="readonly" -->
											<div id="div_editar_solicitud_valor_decimal">
												<input type="text" id="editar_solicitud_valor_decimal" class="filtro txt_filter_style" style="width: 100%;" placeholder="0.00">
											</div>
											<div id="div_editar_solicitud_valor_select_option">
												<select class="form-control select2" id="editar_solicitud_valor_select_option" name="editar_solicitud_valor_select_option">
												</select>
											</div>
											<div id="div_editar_solicitud_departamento" class="col-xs-12 col-md-12 col-lg-12">
												<div class="form-group">
													<div class="control-label">Departamento:</div>
													<select
														class="form-control input_text select2"
														data-live-search="true"
														data-col="area_id"
														data-table="tbl_areas"
														name="inmueble_id_departamento"
														id="inmueble_id_departamento"
														title="Seleccione el departamento">
													</select>
												</div>
											</div>
											<div id="div_editar_solicitud_provincias" class="col-xs-12 col-md-12 col-lg-12">
												<div class="form-group">
													<div class="control-label">Provincia:</div>
													<select class="form-control input_text select2"
														data-live-search="true"
														data-col="personal_id"
														data-table="tbl_personal"
														name="inmueble_id_provincia"
														id="inmueble_id_provincia"
														title="Seleccione el tipo de Contrato">
													</select>
												</div>
											</div>
											<div id="div_editar_solicitud_distrito" class="col-xs-12 col-md-12 col-lg-12">
												<div class="form-group">
													<div class="control-label">Distrito:</div>
													<select class="form-control input_text select2"
														data-live-search="true"
														data-col="personal_id"
														data-table="tbl_personal"
														name="inmueble_id_distrito"
														id="inmueble_id_distrito"
														title="Seleccione el tipo de Contrato">
													</select>
												</div>
											</div>
										</td>
									</tr>
								</table>
							</div>
						</div>
					</form>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
				<button type="button" class="btn btn-success" onclick="sec_con_detalle_int_editar_campo_solicitud('modal_editar_solicitud');">
					<i class="icon fa fa-edit"></i>
					<span id="demo-button-text">Editar</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL EDITAR SOLICITUD -->






















<!-- INICIO LECTOR PANTALLA COMPLETA -->
<div class="modal fade right" id="exampleModalPreview" tabindex="-1" role="dialog" aria-labelledby="exampleModalPreviewLabel" aria-hidden="true" style="width: 100%;padding-left: 0px;">
	<div class="modal-dialog-full-width modal-dialog momodel modal-fluid" role="document" style="width: 100%; margin: 10px auto;">
		<div class="modal-content-full-width modal-content " style="background-color: rgb(0 0 0 / 0%) !important;">
			<div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center;margin-top: 0px; margin-bottom:10px;">
				<button type="button" class="btn btn-block btn-danger" data-dismiss="modal"><i class="fa fa-close"></i> Cerrar Visor</button>
			</div>
			<div class="modal-body" style="padding: 0px;" id="divVisorPdfModal">
			</div>
			<div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center;margin-top: 10px; margin-bottom:10px;">
				<button type="button" class="btn btn-block btn-danger" data-dismiss="modal"><i class="fa fa-close"></i> Cerrar Visor</button>
			</div>
			<div class="col-xs-12 col-md-12 col-sm-12"></div>
		</div>
	</div>
</div>
<!-- FIN LECTOR PANTALLA COMPLETA -->





<!-- INICIO MODAL SUBIR ARCHIVOS - REQUISITOS DE LA SOLICITUD DE PROVEEDOR -->
<div id="moda_subir_archivo_req_solicitud" class="modal fade" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">

				<h4 class="modal-title"></h4>
			</div>
			<form id="formArchivosModal_req_solicitud_interno" method="POST" enctype="multipart/form-data">
				<input type="hidden" name="id_archivo" id="id_archivo">
				<input type="hidden" name="id_contrato_req_file_arrendamiento" id="id_contrato_req_file_arrendamiento" value="<?php echo $contrato_id; ?>">
				<input type="hidden" name="id_tipo_archivo" id="id_tipo_archivo">
				<input type="hidden" name="id_representante_legal" id="id_representante_legal">

				<div class="modal-body">
					<div class="row">
						<div class="col-lg-12">

							<div class="form-group col-md-12">
								<label for="fileArchivo_requisitos_arrendamiento">Nombre file:</label>
								<div class="input-container">

									<input type="file" id="fileArchivo_requisitos_arrendamiento" name="fileArchivo_requisitos_arrendamiento" multiple="multiple" accept='.jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip'>

									<button class="browse-btn" id="btnBuscarFile_req_solicitud_arrendamiento">
										Seleccionar
									</button>

									<span class="file-info" id="txtFile_req_solicitud_arrendamiento"></span>
								</div>
							</div>

							<div class="form-group col-md-12" id="divMensajeAlertaLicFuncionamiento">
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-success"><i class="fa fa-upload"></i> Enviar</button>
					<button type="button" class="btn btn-danger" onclick="sec_con_detalle_int_cerrar_moda_subir_archivo();">Cerrar</button>
				</div>
			</form>
		</div>
	</div>
</div>
<!-- FIN MODAL SUBIR ARCHIVOS - REQUISITOS DE LA SOLICITUD DE PROVEEDOR -->

<!-- INICIO MODAL NUEVOS ANEXOS -->
<div id="modalNuevosAnexosConProv" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-xs" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<input type="hidden" name="sec_det_con_prov_id_contrato" id="sec_det_con_prov_id_contrato" value="<?php echo $contrato_id; ?>">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Agregar otro anexo</h4>
			</div>
			<div class="modal-body">
				<form id="sec_nuevo_form_modal_nuevo_anexo" name="sec_nuevo_form_modal_nuevo_anexo" method="POST" enctype="multipart/form-data" autocomplete="off">
					<div class="row">
						<div class="col-md-3">
							<label>Tipo de anexo: </label>
						</div>
						<div class="col-md-5">
							<select class="form-control input_text select2 col-5" name="modal_nuevo_anexo_select_tipos_anexos_con_prov" id="modal_nuevo_anexo_select_tipos_anexos_con_prov" title="Seleccione el tipo de anexo">
							</select>
						</div>
						<div class="col-md-4">
							<button type="button" class=" col-5 btn btn-sm btn-info" onclick="agregarNuevoAnexoDetalleProveedor();">
								<i class="icon fa fa-plus"></i>
								<span> Agregar Nuevo Tipo</span>
							</button>
						</div>
					</div>
					<br><br>
					<div class="row">
						<div class="col-md-3"></div>
						<div id="sec_contrato_nuevo_div_input_file_nuevo_anexo_con_prov" class="col-md-5">
							<input type="file" name="fileArchivo_requisitos_arrendamiento" required accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip">
						</div>
						<div class="col-md-3"></div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button"
					class="btn btn-danger"
					class="btn btn-success"
					data-dismiss="modal">
					<i class="icon fa fa-close"></i>
					Cancelar</button>
				<button type="button" class="btn btn-success" id="sec_nuevo_modal_guardar_archivo" onclick="sec_con_detalle_int_modal_guardar_nuevo_anexo()">
					<i class="icon fa fa-save"></i>
					<span>Agregar</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL NUEVOS ANEXOS -->

<!-- INICIO MODAL AGREGAR TIPO ANEXO -->
<div id="sec_nuevo_con_agregar_nuevo_tipo_archivo_contrato_proveedor" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-sm" role="document" style="width: 30%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Agregar nuevo tipo de Anexo</h4>
			</div>
			<div class="modal-body">
				<form id="sec_con_nuevo_agregar_tipo_anexo_form_con_prov" name="sec_con_nuevo_agregar_tipo_anexo_form_con_prov" method="POST" enctype="multipart/form-data" autocomplete="off">
					<div class="row">
						<div class="col-md-10">
							<input type="text" name="sec_nuevo_tipo_anexo_nombre_con_prov" id="sec_nuevo_tipo_anexo_nombre_con_prov" class="form-control" placeholder="Nombre del tipo de anexo" />
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button"
					class="btn btn-danger"
					class="btn btn-success"
					data-dismiss="modal">
					<i class="icon fa fa-close"></i>
					Cancelar</button>
				<button type="button" class="btn btn-success" id="sec_nuevo_modal_guardar_archivo" onclick="guardarNuevoTipoAnexoConAcuerdoConfidencialidad(5)">
					<i class="icon fa fa-save"></i>
					<span>Guardar nuevo tipo de anexo</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL AGREGAR TIPO ANEXO -->

<!-- INICIO MODAL AGREGAR REPRESENTANTE -->
<div id="modalSecConDetProvAgregarRepresentante" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<input type="hidden" name="sec_con_det_prov_id_contrato_modal_nuevo_representante" id="sec_con_det_prov_id_contrato_modal_nuevo_representante" value="<?php echo $contrato_id; ?>">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Agregar Cuenta Bancaria</h4>
			</div>
			<div class="modal-body">
				<form id="sec_con_nuevo_agregar_nuevo_representante_legal_form" name="sec_con_nuevo_agregar_nuevo_representante_legal_form" enctype="multipart/form-data" autocomplete="off">
					<div class="row">
						<!--DNI REPRESENTANTE LEGAL-->
						<div class="col-xs-12 col-md-6 col-lg-6" style="display:none">
							<div class="form-group">
								<div class="control-label">DNI del Representante Legal :</div>
								<input type="text" name="sec_con_det_dni_representante" id="sec_con_det_dni_representante"
									maxlength=8
									class="filtro"
									style="width: 100%; height: 30px;"
									oninput="this.value=this.value.replace(/[^0-9]/g,'');">
							</div>
						</div>

						<!--NOMBRE COMPLETO REPRESENTANTE LEGAL-->
						<div class="col-xs-12 col-md-6 col-lg-6" style="display:none">
							<div class="form-group">
								<div class="control-label">Nombre Completo del Representante Legal :</div>
								<input type="text" name="sec_con_det_nombre_representante" id="sec_con_det_nombre_representante" maxlength=50 class="filtro"
									style="width: 100%; height: 30px;">
							</div>
						</div>

						<div class="col-xs-12 col-md-6 col-lg-6" style="display:none">
							<div class="form-group">
								<div class="control-label">Nro. Cuenta de Detracción (Banco de la Nación): </div>
								<input type="text" id="sec_con_det_nro_cuenta_detraccion" name="sec_con_det_nro_cuenta_detraccion" maxlength="50"
									style="width: 100%; height: 30px;" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
							</div>
						</div>

						<!--BANCO-->
						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Banco : </div>
								<select class="form-control input_text select2" data-live-search="true"
									name="sec_con_det_banco" id="sec_con_det_banco" title="Seleccione el banco">
								</select>
							</div>
						</div>

						<!--NRO CUENTA-->
						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Nro. de Cuenta : </div>
								<input type="text" id="sec_con_det_nro_cuenta" name="sec_con_det_nro_cuenta" maxlength="50"
									style="width: 100%; height: 30px;" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
							</div>
						</div>

						<!--NRO CCI-->
						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Nro. de CCI : </div>
								<input type="text" id="sec_con_det_nro_cci" name="sec_con_det_nro_cci" maxlength="50"
									style="width: 100%; height: 30px;" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
							</div>
						</div>
					</div>
					<div class="row">

						<div class="col-xs-12 col-md-6 col-lg-6" style="display:none">
							<div class="form-group">
								<div class="control-label">
									Vigencia
								</div>
								<input type="file" name="sec_con_det_prov_file_vigencia_nuevo_rl" accept=".jpg, .jpeg, .png, .pdf">
							</div>
						</div>
						<div class="col-xs-12 col-md-6 col-lg-6" style="display:none">
							<div class="form-group">
								<div class="control-label">
									DNI
								</div>
								<input type="file" name="sec_con_det_prov_file_dni_nuevo_rl" accept=".jpg, .jpeg, .png, .pdf">
							</div>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" class="btn btn-success" data-dismiss="modal"><i class="icon fa fa-close"></i>Cancelar</button>
				<button type="button" class="btn btn-success" onclick="sec_con_detalle_int_guardar_nuevo_representante_legal()">
					<i class="icon fa fa-save"></i>
					<span>Guardar Nuevo Cuenta Bancaria</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL AGREGAR REPRESENTANTE -->

<!-- INICIO MODAL AGREGAR CONTRAPRESTACION -->
<div id="modal_agregar_contraprestacion" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Agregar Nueva Contraprestación</h4>
			</div>
			<div class="modal-body">
				<form id="form_agregar_nueva_contraprestacion" name="form_agregar_nueva_contraprestacion" enctype="multipart/form-data" autocomplete="off">
					<div class="row">
						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Tipo de Moneda <span class="campo_obligatorio_v2">(*)</span>:</div>
								<select
									name="moneda_id"
									id="moneda_id"
									class="form-control select2"
									id="select-default"
									style="width: 100%;">
									<option value="">Seleccione el tipo de moneda</option>
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label" id="label_subtotal">Subtotal <span class="campo_obligatorio_v2">(*)</span>:</div>
								<input type="text" name="subtotal" id="subtotal" class="filtro" style="width: 100%; height: 28px;">
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label" id="label_igv">IGV :</div>
								<input type="text" name="igv" id="igv" class="filtro" style="width: 100%; height: 28px;">
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Monto Bruto <span class="campo_obligatorio_v2">(*)</span>:</div>
								<input type="text" name="monto" id="monto" class="filtro" style="width: 100%; height: 28px;">
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Forma de Pago <span class="campo_obligatorio_v2">(*)</span>:</div>
								<select class="form-control select2" name="forma_pago" id="forma_pago" style="width: 100%;">
									<option value="">Seleccione el tipo de forma de pago</option>
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Tipo de Comprobante a Emitir <span class="campo_obligatorio_v2">(*)</span>:</div>
								<select
									name="tipo_comprobante"
									id="tipo_comprobante"
									class="form-control select2"
									style="width: 100%; height: 28px;">
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Plazo de Pago <span class="campo_obligatorio_v2">(*)</span>:</div>
								<input type="text" name="plazo_pago" id="plazo_pago" class="filtro"
									style="width: 100%; height: 28px;">
							</div>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button
					type="button"
					class="btn btn-danger"
					class="btn btn-success"
					data-dismiss="modal">
					<i class="icon fa fa-close"></i>
					Cancelar
				</button>
				<button
					type="button"
					class="btn btn-success"
					onclick="sec_con_det_prov_guardar_nuevo_representante_legal()">
					<i class="icon fa fa-save"></i>
					<span>Guardar Nueva Contraprestación</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL AGREGAR CONTRAPRESTACION -->


<!-- INICIO MODAL SUBIR ARCHIVOS - REQUISITOS DE LA SOLICITUD DE ARRENDAMIENTO -->
<div id="moda_subir_archivo_req_solicitud_arrendamiento" class="modal fade" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">

				<h4 class="modal-title"></h4>
			</div>
			<form id="formArchivosModal_req_solicitud_arrendamiento" method="POST" enctype="multipart/form-data">
				<input type="hidden" name="id_archivo" id="id_archivo">
				<input type="hidden" name="id_contrato_req_file_arrendamiento" id="id_contrato_req_file_arrendamiento" value="<?php echo $contrato_id; ?>">
				<input type="hidden" name="id_tipo_archivo" id="id_tipo_archivo">

				<div class="modal-body">
					<div class="row">
						<div class="col-lg-12">

							<div class="form-group col-md-12">
								<label for="fileArchivo_requisitos_arrendamiento">Nombre file:</label>
								<div class="input-container">

									<input type="file" id="fileArchivo_requisitos_arrendamiento" name="fileArchivo_requisitos_arrendamiento" multiple="multiple" accept='.jpeg, .jpg, .png, .pdf'>

									<button class="browse-btn" id="btnBuscarFile_req_solicitud_arrendamiento">
										Seleccionar
									</button>

									<span class="file-info" id="txtFile_req_solicitud_arrendamiento"></span>
								</div>
							</div>

							<div class="form-group col-md-12" id="divMensajeAlertaLicFuncionamiento">
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-success"><i class="fa fa-upload"></i> Enviar</button>
					<button type="button" class="btn btn-danger" onclick="cerrar_moda_subir_archivo_req_solicitud_arrendamiento();">Cerrar</button>
				</div>
			</form>
		</div>
	</div>
</div>
<!-- FIN MODAL SUBIR ARCHIVOS - REQUISITOS DE LA SOLICITUD DE ARRENDAMIENTO -->


<!-- INICIO MODAL NUEVO PROPIETARIO -->
<div id="modal_propietario" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_nuevo_propietario_titulo">Registrar Nuevo Propietario</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form autocomplete="off" id="frm_nuevo_propietario">
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Tipo de Persona:</div>
								<select
									class="form-control select2"
									name="modal_propietario_tipo_persona"
									id="modal_propietario_tipo_persona">
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
								<input type="text" id="modal_propietario_nombre" name="modal_propietario_nombre" class="filtro txt_filter_style" maxlength="50" style="width: 100%; height: 30px;">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Tipo de documento de identidad:</div>
								<select class="form-control" id="modal_propietario_tipo_docu">
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
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_num_docu_propietario" style="display: none;">
							<div class="form-group">
								<div class="control-label" id="label_num_docu_propietario">Número de documento de identidad del propietario:</div>
								<input type="text" id="modal_propietario_num_docu" name="modal_propietario_num_docu" class="filtro txt_filter_style" maxlength="11" style="width: 100%; height: 30px;">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_num_ruc_propietario">
							<div class="form-group">
								<div class="control-label">Número de RUC del propietario:</div>
								<input type="text" id="modal_propietario_num_ruc" name="modal_propietario_num_ruc" class="filtro txt_filter_style num_ruc" maxlength="11" style="width: 100%; height: 30px;">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Domicilio del propietario:</div>
								<input type="text" id="modal_propietario_direccion" name="modal_propietario_direccion" class="filtro txt_filter_style" maxlength="100" style="width: 100%; height: 30px;" autocomplete="off">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_propietario_representante_legal" style="display: none;">
							<div class="form-group">
								<div class="control-label">Representante Legal:</div>
								<input type="text" id="modal_propietario_representante_legal" class="filtro txt_filter_style" maxlength="50" style="width: 100%; height: 30px;">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_propietario_num_partida_registral" style="display: none;">
							<div class="form-group">
								<div class="control-label">N° Partida Registral de la empresa:</div>
								<input type="text" id="modal_propietario_num_partida_registral" class="filtro txt_filter_style" maxlength="50" style="width: 100%; height: 30px;">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_propietario_persona_contacto">
							<div class="form-group">
								<div class="control-label">Persona de contacto</div>
								<select class="form-control" id="modal_propietario_tipo_persona_contacto">
									<option value="0">Seleccione la persona contacto</option>
									<option value="1">El propietario es la persona de contacto</option>
									<option value="2">El propietario no es la persona de contacto</option>
								</select>
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_propietario_contacto_nombre" style="display: none;">
							<div class="form-group">
								<div class="control-label">Nombre de la persona de contacto:</div>
								<input type="text" id="modal_propietario_contacto_nombre" name="modal_propietario_contacto_nombre" class="filtro txt_filter_style" maxlength="50" style="width: 100%; height: 30px;">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Teléfono de la persona de contacto:</div>
								<input type="text" id="modal_propietario_contacto_telefono" name="modal_propietario_contacto_telefono" class="filtro txt_filter_style" maxlength="9" style="width: 100%; height: 30px;">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Mail de la persona de contacto:</div>
								<input type="text" id="modal_propietario_contacto_email" name="modal_propietario_contacto_email" class="filtro txt_filter_style" maxlength="50" style="width: 100%; height: 30px;">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_propietario_mensaje" style="display: none">
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
				<button type="button" class="btn btn-success" id="btn_agregar_propietario">
					<i class="icon fa fa-plus"></i>
					Agregar propietario
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL NUEVO PROPIETARIO -->

<!-- INICIO MODAL AGREGAR CATEGORIA CONTRATO -->
<div id="modal_categoria_contrato" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Categoria / Tipos de Contrato</h4>
			</div>
			<div class="modal-body">
				<div>
					<!-- Nav tabs -->
					<ul class="nav nav-tabs" role="tablist">
						<li role="presentation" class="active"><a href="#Categoria" aria-controls="Categoria" role="tab" data-toggle="tab">Categoria</a></li>
						<li role="presentation"><a href="#TiposDeCategoria" aria-controls="TiposDeCategoria" role="tab" data-toggle="tab">Tipos de Categoria</a></li>
					</ul>

					<!-- Tab panes -->
					<div class="tab-content">
						<div role="tabpanel" class="tab-pane active" id="Categoria">
							<form id="form_modal_categoria" name="form_modal_categoria" enctype="multipart/form-data" autocomplete="off">
								<div class="row">
									<div class="col-xs-12 col-md-10 col-lg-10">
										<div class="form-group">
											<div class="control-label">Nombre <span class="campo_obligatorio_v2">(*)</span>:</div>
											<input type="hidden" id="md_id_categoria_servicio" class="form-control">
											<input type="text" id="md_categoria_servicio_nombre" class="form-control">
										</div>
									</div>
									<div class="col-xs-12 col-md-2 col-lg-2">
										<div class="form-group">
											<div class="control-label">.</div>
											<button
												type="button"
												class="btn btn-success form-control"
												onclick="sec_contrato_detalle_solicitud_guardar_modal_categoria_servicio()">
												<i class="icon fa fa-save"></i>
												<span>Guardar</span>
											</button>
										</div>
									</div>


								</div>
							</form>
							<br>
							<hr>
							<br>
							<div clas="table-responsive" id="div_listar_servicio_categoria"></div>

						</div>
						<div role="tabpanel" class="tab-pane" id="TiposDeCategoria">
							<form id="form_modal_tipo_categoria" name="form_modal_tipo_categoria" enctype="multipart/form-data" autocomplete="off">
								<div class="row">
									<div class="col-xs-12 col-md-5 col-lg-5">
										<div class="form-group">
											<div class="control-label">Categoria <span class="campo_obligatorio_v2">(*)</span>:</div>
											<input type="hidden" id="md_id_tipo_categoria_servicio" class="form-control">
											<select class="form-control select2" id="md_categoria_servicio_id">
												<option value="0">- Seleccione -</option>
												<?php
												$sql_query = "SELECT* FROM cont_categoria_servicio WHERE status = 1 ORDER BY id DESC";
												$query = $mysqli->query($sql_query);
												while ($sel = $query->fetch_assoc()) {
												?>
													<option value="<?= $sel['id'] ?>"><?= $sel['nombre'] ?></option>
												<?php
												}
												?>
											</select>
										</div>
									</div>
									<div class="col-xs-12 col-md-5 col-lg-5">
										<div class="form-group">
											<div class="control-label">Nombre <span class="campo_obligatorio_v2">(*)</span>:</div>
											<input type="text" id="md_tipo_categoria_servicio_nombre" class="form-control">
										</div>
									</div>
									<div class="col-xs-12 col-md-2 col-lg-2">
										<div class="form-group">
											<div class="control-label">.</div>
											<button
												type="button"
												class="btn btn-success form-control"
												onclick="sec_contrato_detalle_solicitud_guardar_modal_tipo_categoria_servicio()">
												<i class="icon fa fa-save"></i>
												<span>Guardar</span>
											</button>
										</div>
									</div>


								</div>
							</form>
							<br>
							<hr>
							<br>
							<div clas="table-responsive" id="div_listar_tipo_servicio_categoria"></div>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button
					type="button"
					class="btn btn-danger"
					class="btn btn-success"
					data-dismiss="modal">
					<i class="icon fa fa-close"></i>
					Cancelar
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL AGREGAR CATEGORIA CONTRATO -->

<!-- INICIO MODAL CANCELAR SOLICITUD -->
<div id="modal_cancelar_solicitud" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Cancelar Solicitud de Contrato Locación de Servicio</h4>
			</div>
			<div class="modal-body">
				<form id="form_cancelar_solicitud" name="form_cancelar_solicitud" enctype="multipart/form-data" autocomplete="off">
					<div class="row">

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="form-group">
								<div class="control-label" id="label_subtotal">Motivo <span class="campo_obligatorio_v2">(*)</span>:</div>
								<textarea name="cancelado_motivo" id="cancelado_motivo" class="filtro" style="width: 100%;"></textarea>
							</div>
						</div>

					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button
					type="button"
					class="btn btn-default"
					data-dismiss="modal">
					Desistir
				</button>
				<button
					type="button"
					class="btn btn-danger"
					onclick="sec_contrato_detalle_solicitud_cancelar_solicitud()">
					<i class="icon fa fa-close"></i>
					<span>Cancelar Solicitud de Contrato Locación de Servicio</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL CANCELAR SOLICITUD -->