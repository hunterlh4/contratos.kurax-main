
<?php
$this_menu = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' LIMIT 1")->fetch_assoc();
$menu_id = $this_menu["id"];

$menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' AND sub_sec_id = '$sub_sec_id' LIMIT 1")->fetch_assoc();
$menu_consultar = isset($menu_id_consultar["id"]) ? $menu_id_consultar["id"]:0;

if(!array_key_exists($menu_id,$usuario_permisos)){
	echo "No tienes permisos para este recurso.";
	die();
}

$contrato_id = $_GET["id"];
$adenta_id_temporal = '';
$resolucion_id_temporal = '';

if ( isset($_GET["adenda_id"]) ) {
	$adenta_id_temporal = $_GET["adenda_id"];
}

if ( isset($_GET["resolucion_id"]) ) {
	$resolucion_id_temporal = $_GET["resolucion_id"];
}

$permiso_editar_contrato_firmado = false;
if ((array_key_exists($menu_consultar,$usuario_permisos) && in_array("editar_contrato_firmado", $usuario_permisos[$menu_consultar]))) { 
	$permiso_editar_contrato_firmado = true;
}

$list_emp_cont = $mysqli->query("SELECT tpg.valor FROM tbl_parametros_generales tpg WHERE tpg.codigo = 'empresa_contacto_de_contratos' AND tpg.estado = 1 LIMIT 1");
$row_emp_cont = $list_emp_cont->fetch_assoc();
$valor_empresa_contacto = isset($row_emp_cont['valor']) && $row_emp_cont['valor'] == 'Kurax' ? '(Kurax)':'(AT)';
$pref_empresa_contacto = isset($row_emp_cont['valor']) && $row_emp_cont['valor'] == 'Kurax' ? 'Kurax':'AT';

$usuario_id = $login?$login['id']:null;
$area_id = $login ? $login['area_id'] : 0;
$cargo_id = $login ? $login['cargo_id'] : 0;
$query_sql = "
SELECT 
	c.user_created_id,
	c.etapa_id,
	p.area_id,
	co.sigla AS sigla_correlativo,
	c.codigo_correlativo,
	c.check_gerencia_interno,
	c.fecha_atencion_gerencia_interno,
	c.cancelado_id
FROM
	cont_contrato c
	LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
	LEFT JOIN tbl_usuarios u ON c.user_created_id = u.id
	LEFT JOIN tbl_personal_apt p ON u.personal_id = p.id
WHERE
	c.contrato_id = " . $contrato_id . "
";
$query = $mysqli->query($query_sql);
$row = $query->fetch_assoc();
$user_created_id = $row["user_created_id"];
$area_created_id = $row["area_id"];
$etapa_id = $row["etapa_id"];
$sigla_correlativo = $row["sigla_correlativo"];
$codigo_correlativo = $row["codigo_correlativo"];
$check_gerencia_interno = $row["check_gerencia_interno"];
$fecha_atencion_gerencia_interno = trim($row["fecha_atencion_gerencia_interno"]);
$cancelado_id = $row["cancelado_id"];

if ( ($usuario_id == $user_created_id && $etapa_id == 1) || ( $area_id == 33 && $cargo_id != 25 && $etapa_id == 5 ) || $permiso_editar_contrato_firmado) {
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
	.alert-default{
		background-color: #f1f1f1;
		border-color: #898989;
		padding: 0px !important;
	}
	.div_tab{
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

.bg-red, .bg-red>a {
	color: #fff!important;
}

.bg-red {
	background-color: #dc3545!important;
}

.bg-blue, .bg-blue>a {
	color: #fff!important;
}

.bg-blue {
	background-color: #007bff!important;
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
	border-bottom: 1px solid rgba(0,0,0,.125);
	color: #495057;
	font-size: 14px;
	line-height: 1.1;
	margin: 0;
	padding: 10px;
}

.timeline>div>.timeline-item>.timeline-body, .timeline>div>.timeline-item>.timeline-footer {
	padding: 10px;
}

.timeline>div>.timeline-item>.timeline-body, .timeline>div>.timeline-item>.timeline-footer {
	padding: 10px;
}

.timeline>div>.fa, .timeline>div>.fab, .timeline>div>.fad, .timeline>div>.fal, .timeline>div>.far, .timeline>div>.fas, .timeline>div>.ion, .timeline>div>.svg-inline--fa {
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

.bg-green, .bg-green>a {
	color: #fff!important;
}

.bg-green {
	background-color: #28a745!important;
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

.select2-selection--single{
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
						<i class="icon icon-inline glyphicon glyphicon-briefcase"></i> <?php echo $etapa_id == 1 ? 'Solicitud de ' : '';?> Contrato Interno - Código: <?php echo $sigla_correlativo; echo $codigo_correlativo; ?>
					</h1>
				</div>
			</div>

			<div class="col-xs-12 col-md-12 col-lg-7">
				<div class="panel" id="divDetalleSolicitud">

					<div class="panel-heading">
						<div class="panel-title">DETALLE DE LA SOLICITUD</div>
					</div>

					<div class="panel-body" style="padding: 0px; padding-top: 10px; font-size: 12px;">
						<form id="frmContratoDeArrendatario">

						<div class="w-100" style="padding-right: 5px;">
							<div class="col-xs-12 col-md-12 col-lg-12" style="padding-right: 0px; padding-left: 10px; margin-bottom: 20px;">
								<?php 
								$sel_query = $mysqli->query(
								"
								SELECT
									c.contrato_id,
									c.check_gerencia_interno,
									c.fecha_atencion_gerencia_interno,
									c.aprobacion_gerencia_interno
								FROM cont_contrato c
								WHERE c.contrato_id = '". $contrato_id ."'
								");
								while($sel=$sel_query->fetch_assoc())
								{
									$check_gerencia_interno = $sel["check_gerencia_interno"];
									$fecha_atencion_gerencia_interno = $sel["fecha_atencion_gerencia_interno"];
									$aprobacion_gerencia_interno = $sel["aprobacion_gerencia_interno"];
								}

								if($check_gerencia_interno == 1 && $etapa_id == 1)
								{
									if(is_null($fecha_atencion_gerencia_interno))
									{
								?>
								<a class="btn btn-warning btn-xs" onclick="sec_con_detalle_int_reenviar_correo_gerencia(<?php echo $contrato_id; ?>);">
									<span class="fa fa-envelope-o"></span> Notificar a Director(a)
								</a>
								<?php
									}
								}
								?>
								<?php 
								if($usuario_id == 3315 || $usuario_id == 3562 || $usuario_id == 3028 || ($check_gerencia_interno == "1" && $fecha_atencion_gerencia_interno == ""))
								{
									?>
									<a class="btn btn-info btn-xs" onclick="sec_con_detalle_int_reenviar_correo(<?php echo $contrato_id; ?>);">
										<span class="fa fa-envelope-o"></span> Reenviar por email a Lourdes Britto
									</a>
									<?php 
								}

								if($etapa_id == 1 && $area_id == $area_created_id && $cancelado_id != 1) { ?>
									<a class="btn btn-danger btn-xs" onclick="sec_contrato_detalle_solicitud_cancelar_solicitud_modal(<?php echo $contrato_id; ?>);">
										<span class="fa fa-close"></span> Cancelar Solicitud
									</a>
								<?php } ?>
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
												rs2.nombre AS empresa_at2,
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
												INNER JOIN tbl_razon_social rs2 ON c.empresa_grupo_at_2 = rs2.id
												LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
												LEFT JOIN tbl_usuarios ug ON c.gerente_area_id = ug.id
												LEFT JOIN tbl_personal_apt peg ON ug.personal_id = peg.id

												LEFT JOIN tbl_usuarios uab ON c.abogado_id = uab.id
												LEFT JOIN tbl_personal_apt pab ON uab.personal_id = pab.id

												LEFT JOIN tbl_cargos AS cpc ON cpc.id = c.cargo_id_persona_contacto
												LEFT JOIN tbl_cargos AS cr ON cr.id = c.cargo_id_responsable
												LEFT JOIN tbl_cargos AS ca ON ca.id = c.cargo_id_aprobante
											WHERE c.tipo_contrato_id = 7 AND	c.contrato_id IN (" . $contrato_id . ")");
												while($sel = $sel_query->fetch_assoc()){
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
														<td style="width: 50%;"><b>Empresa Grupo <?=$pref_empresa_contacto?> 1</b></td>
														<td><?php echo $sel["empresa_at1"];?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td style="width: 75px;">
															<a class="btn btn-success btn-xs" 
															onclick="sec_con_detalle_int_editar_solicitud('Datos Generales','cont_contrato','empresa_suscribe_id','Empresa Grupo <?=$pref_empresa_contacto?> 1','select_option','<?php echo $sel["empresa_at1"]; ?>','obtener_empresa_at');">
																<span class="fa fa-edit"></span> Editar
															</a>
														</td>
														<?php } ?>
													</tr>
													<tr>
														<td style="width: 50%;"><b>Empresa Grupo <?=$pref_empresa_contacto?> 2</b></td>
														<td><?php echo $sel["empresa_at2"];?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td style="width: 75px;">
															<a class="btn btn-success btn-xs" 
															onclick="sec_con_detalle_int_editar_solicitud('Datos Generales','cont_contrato','empresa_grupo_at_2','Empresa Grupo <?=$pref_empresa_contacto?> 2','select_option','<?php echo $sel["empresa_at2"]; ?>','obtener_empresa_at');">
																<span class="fa fa-edit"></span> Editar
															</a>
														</td>
														<?php } ?>
													</tr>

													<?php if (empty($gerente_area_id)) { ?>

													<tr>
														<td><b>Responsable de Área</b></td>
														<td><?php echo $gerente_area_nombre;?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td>
															<a class="btn btn-success btn-xs" 
															onclick="sec_contrato_detalle_solicitud_editar_solicitud('Datos Generales','cont_contrato','gerente_area_nombre','Responsable de Área','varchar','<?php echo $gerente_area_nombre; ?>','');">
																<span class="fa fa-edit"></span> Editar
															</a>
														</td>
														<?php } ?>
													</tr>

													<tr>
														<td><b>Responsable de Área (Email)</b></td>
														<td><?php echo $gerente_area_email;?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td>
															<a class="btn btn-success btn-xs" 
															onclick="sec_contrato_detalle_solicitud_editar_solicitud('Datos Generales','cont_contrato','gerente_area_email','Responsable de Área (Email)','varchar','<?php echo $gerente_area_email; ?>','');">
																<span class="fa fa-edit"></span> Editar
															</a>
														</td>
														<?php } ?>
													</tr>

													<?php } else { ?>

													<tr>
														<td><b>Responsable de Área</b></td>
														<td><?php echo $gerente_area_nombre;?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td>
															<a class="btn btn-success btn-xs" 
															onclick="sec_contrato_detalle_solicitud_editar_solicitud('Datos Generales','cont_contrato','gerente_area_id','Responsable de Área','select_option','<?php echo $gerente_area_nombre; ?>','obtener_gerentes');">
																<span class="fa fa-edit"></span> Editar
															</a>
														</td>
														<?php } ?>
													</tr>

													<?php }?>

													<tr>
														<td><b>Cargo Responsable de Área</b></td>
														<td><?php echo $sel["cargo_responsable"];?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td style="width: 75px;">
															<a class="btn btn-success btn-xs" 
															onclick="sec_contrato_detalle_solicitud_editar_solicitud('Datos Generales','cont_contrato','cargo_id_responsable','Cargo Responsable de Área','select_option','<?php echo $sel["cargo_responsable"]; ?>','obtener_cargos');">
																<span class="fa fa-edit"></span> Editar
															</a>
														</td>
														<?php } ?>
													</tr>

													<tr>
														<td><b>Registrado por</b></td>
														<td><?php echo $sel["usuario_creacion"];?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td></td>
														<?php } ?>
													</tr>
													<tr>
														<td><b>Cargo Persona Contacto <?=$valor_empresa_contacto?></b></td>
														<td><?php echo $sel["cargo_persona_contacto"];?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td style="width: 75px;">
															<a class="btn btn-success btn-xs" 
															onclick="sec_contrato_detalle_solicitud_editar_solicitud('Datos Generales','cont_contrato','cargo_id_persona_contacto','Cargo Persona Contacto <?=$valor_empresa_contacto?>','select_option','<?php echo $sel["cargo_persona_contacto"]; ?>','obtener_cargos');">
																<span class="fa fa-edit"></span> Editar
															</a>
														</td>
														<?php } ?>
													</tr>
													<tr>
														<td><b>Fecha de Registro</b></td>
														<td><?php echo $sel["created_at"];?></td>
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

													if($mysqli->error){
														echo $mysqli->error . $query_solicitud_cancelada;
													}

													while($sel = $sel_query->fetch_assoc())
													{
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
									c.check_gerencia_interno,
									c.fecha_atencion_gerencia_interno,
									c.aprobacion_gerencia_interno,
									CONCAT(IFNULL(pud.nombre, ''),' ',IFNULL(pud.apellido_paterno, ''),' ',IFNULL(pud.apellido_materno, '')) AS nombre_del_director_a_aprobar,
									CONCAT(IFNULL(puap.nombre, ''),' ',IFNULL(puap.apellido_paterno, ''),' ',IFNULL(puap.apellido_materno, '')) AS aprobado_por
								FROM 
									cont_contrato c
									LEFT JOIN tbl_usuarios ud ON c.director_aprobacion_id = ud.id
									LEFT JOIN tbl_personal_apt pud ON ud.personal_id = pud.id
									LEFT JOIN tbl_usuarios uap ON c.aprobado_por = uap.id
									LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id
								WHERE c.contrato_id = '". $contrato_id ."'
								");

								$cantReg = mysqli_num_rows($sel_query);

								while($sel=$sel_query->fetch_assoc())
								{
									$check_gerencia_interno = $sel["check_gerencia_interno"];
									$fecha_atencion_gerencia_interno = $sel["fecha_atencion_gerencia_interno"];
									$aprobacion_gerencia_interno = $sel["aprobacion_gerencia_interno"];
									$aprobado_por = $sel["aprobado_por"];
								}
				
							if($check_gerencia_interno == 1)
							{

							?>

							<div class="panel panel-default">
								<div class="panel-heading" role="tab" id="headingAprobacionGenrencia">
								<h4 class="panel-title">
									<a role="button" data-toggle="collapse" data-parent="#accordionContrato" href="#collapseAprobacionGerencia" aria-expanded="true" aria-controls="collapseAprobacionGerencia">
									APROBACIÓN DE GERENCIA
									</a>
								</h4>
								</div>
								<div id="collapseAprobacionGerencia" class="panel-collapse-all panel-collapse collapse in" role="tabpanel" aria-labelledby="headingAprobacionGenrencia">
									<div class="panel-body">
										<div class="form-group">
											<table class="table table-bordered">
												
												<?php  
													if(!is_null($fecha_atencion_gerencia_interno))
													{
														if($aprobacion_gerencia_interno == 1)
														{
															?>
																<tr>
																	<td style="width: 50%;"><b>Aprobado por</b></td>
																	<td><?php echo $aprobado_por; ?></td>
																</tr>
																<tr>
																	<td><b>Fecha Aprobación</b></td>
																	<td><?php echo $fecha_atencion_gerencia_interno; ?></td>
																</tr>
															<?php
														}
														else
														{
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
													}
													else
													{
														?>
															<tr>
																<td style="width: 50%;"><b>Aprobado por</b></td>
																<td></td>
															</tr>
															<tr>
																<td><b>Situación</b></td>
																<td>Pendiente</td>
															</tr>
														<?php
													}
												?>

													<tr>
														<td><b>Cargo Aprobante</b></td>
														<td><?php echo $cargo_aprobante;?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td style="width: 75px;">
															<a class="btn btn-success btn-xs" 
															onclick="sec_contrato_detalle_solicitud_editar_solicitud('Datos Generales','cont_contrato','cargo_id_aprobante','Cargo Aprobante','select_option','<?php echo $cargo_aprobante; ?>','obtener_cargos');">
																<span class="fa fa-edit"></span> Editar
															</a>
														</td>
														<?php } ?>
													</tr>
											</table>
										</div>
									</div>
								</div>
							</div>
							<?php 
							}
							?>

							<div class="panel panel-default">
								<div class="panel-heading" role="tab" id="headingCuentasBancarias">
								<h4 class="panel-title">
									<a role="button" data-toggle="collapse" data-parent="#accordionContrato" href="#collapseCuentasBancarias" aria-expanded="true" aria-controls="collapseCuentasBancarias">
									CUENTAS BANCARIAS
									</a>
								</h4>
								</div>
								<div id="collapseCuentasBancarias" class="panel-collapse-all panel-collapse collapse in" role="tabpanel" aria-labelledby="headingCuentasBancarias">
									<div class="panel-body">
										<div class="w-100 text-right">
											<?php if ($btn_editar_solicitud) { ?>
											<button type="button" class="btn btn-xs btn-primary" onclick="sec_con_detalle_int_agregar_representante()">
												<i class="fa fa-plus"></i>
												Agregar Cuenta Bancaria
											</button>
											<?php } ?>
										</div>

										<div class="w-100 mt-1">
											<div id="divTablaRepresentantesLegales" class="form-group">
												<?php
												$sel_query = $mysqli->query("
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
													rl.contrato_id = $contrato_id"
												);
												$c = 0;
												$id_representante_legal = 0;

												$row_count = $sel_query->num_rows;

												if ($row_count > 0) {
													while($sel=$sel_query->fetch_assoc()){
														$c = $c + 1; 
														$id_representante_legal = $sel["id"];
													?>
													<b># <?php echo $c ?></b>
													<table class="table table-bordered">
														
														<tr>
															<td style="width: 50%;"><b>BANCO</b></td>
															<td><?php echo $sel["banco_representante"]; ?></td>
															<?php if ($btn_editar_solicitud) { ?>
															<td style="width: 75px;">
																<a class="btn btn-success btn-xs" 
																onclick="sec_con_detalle_int_editar_solicitud('Datos del Proveedor - Representante Legal','cont_representantes_legales','id_banco','Banco','select_option','<?php echo $sel["banco_representante"]; ?>','obtener_banco','<?php echo $sel["id"]; ?>');">
																	<span class="fa fa-edit"></span> Editar
																</a>
															</td>
															<?php } ?>
														</tr>
														<tr>
															<td><b>NRO CUENTA</b></td>
															<td><?php echo $sel["nro_cuenta"]; ?></td>
															<?php if ($btn_editar_solicitud) { ?>
															<td>
																<a class="btn btn-success btn-xs" 
																onclick="sec_con_detalle_int_editar_solicitud('Datos del Proveedor - Representante Legal','cont_representantes_legales','nro_cuenta','Nro de Cuenta','varchar','<?php echo $sel["nro_cuenta"]; ?>','','<?php echo $sel["id"]; ?>');">
																	<span class="fa fa-edit"></span> Editar
																</a>
															</td>
															<?php } ?>
														</tr>
														<tr>
															<td><b>NRO CCI</b></td>
															<td><?php echo $sel["nro_cci"]; ?></td>
															<?php if ($btn_editar_solicitud) { ?>
															<td>
																<a class="btn btn-success btn-xs" 
																onclick="sec_con_detalle_int_editar_solicitud('Datos del Proveedor - Representante Legal','cont_representantes_legales','nro_cci','Nro de CCI','varchar','<?php echo $sel["nro_cci"]; ?>','','<?php echo $sel["id"]; ?>');">
																	<span class="fa fa-edit"></span> Editar
																</a>
															</td>
															<?php } ?>
														</tr>
														<!--VIGENCIA-->
														<?php 
															$html = '';
															$sql = "SELECT rl.id, a.archivo_id, a.contrato_id, t.tipo_archivo_id, t.nombre_tipo_archivo,
																	a.nombre, a.extension, a.ruta
																	FROM cont_archivos a
																	INNER JOIN cont_tipo_archivos t ON t.tipo_archivo_id = a.tipo_archivo_id 
																	INNER JOIN cont_representantes_legales rl ON rl.vigencia_archivo_id = a.archivo_id
																	WHERE a.status = 1 AND rl.id = " . $id_representante_legal;

															$query = $mysqli->query($sql);
															$row_count = $query->num_rows;
															$tipo_archivo_id = '';
															$archivo_estado = '';
															if($row_count > 0){
																while ($row = $query->fetch_assoc()) 
																{
																	$tipo_archivo_id = $row["tipo_archivo_id"];
																	if (strlen(trim($row["nombre"])) > 1) 
																	{
																		$ruta = str_replace("/var/www/html","",$row["ruta"]);

																		$archivo = '<a style="width: 150px;" ';
																		$archivo .= ' onclick="sec_con_detalle_int_ver_documento_en_visor(\''. trim($ruta) . '\', \''. trim($row["nombre"]) . '\', \'' . trim($row["extension"]) . '\', \'' . $row["nombre_tipo_archivo"] . '\');"';
																		$archivo .= ' class="btn btn-success btn-xs btn-block"';
																		$archivo .= ' data-toggle="tooltip"';
																		$archivo .= ' data-placement="top">';
																		$archivo .= ' <span class="fa fa-eye"></span> Ver Documento';
																		$archivo .= ' </a>';

																		$archivo_estado = '<a class="class_SubirFile_req_solicitud_arrendamiento btn btn-success btn-xs btn-block" id="btnSubirFile_req_solicitud_arrendamiento" data-toggle="tooltip" data-placement="top" onclick="sec_con_detalle_int_archivo_representante_legal(\''.$row["nombre_tipo_archivo"].'\', \''.$row["archivo_id"].'\', \''.$row["tipo_archivo_id"].'\',' . $id_representante_legal . ');"><i class="fa fa-upload"></i> Reemplazar</a>';
																	}
																	else 
																	{
																		$archivo = '<a class="btn btn-warning btn-xs btn-block" data-toggle="tooltip" data-placement="top"><span class="fa fa-eye-slash"></span> Incompleto</a>';

																		$archivo_estado = '<a class="class_SubirFile_req_solicitud_arrendamiento btn btn-warning btn-xs btn-block" id="btnSubirFile_req_solicitud_arrendamiento" data-toggle="tooltip" data-placement="top" onclick="sec_con_detalle_int_archivo_representante_legal(\''.$row["nombre_tipo_archivo"].'\', \''.$row["archivo_id"].'\', \''.$row["tipo_archivo_id"].'\',' . $id_representante_legal . ');"><i class="fa fa-upload"></i> Subir</a>';
																	}

																	
																}
															}else{
																$archivo = '<a style="width: 150px;" class="btn btn-warning btn-xs btn-block" data-toggle="tooltip" data-placement="top"><span class="fa fa-eye-slash"></span> Incompleto</a>';

																$archivo_estado = '<a class="class_SubirFile_req_solicitud_arrendamiento btn btn-warning btn-xs btn-block" id="btnSubirFile_req_solicitud_arrendamiento" data-toggle="tooltip" data-placement="top" onclick="sec_con_detalle_int_archivo_representante_legal(\'VIGENCIA DE PODER DEL REPRESENTANTE LEGAL DE LA EMPRESA PROVEEDORA\', \'\', \'2\',' . $id_representante_legal . ');"><i class="fa fa-upload"></i> Subir</a>';
															}
															
															
															$html .= '<tr style="text-transform: none;">';
															$html .= '<td style="text-transform: uppercase;"><b>VIGENCIA DE PODER DEL REPRESENTANTE LEGAL DE LA EMPRESA PROVEEDORA</b></td>';
															$html .= '<td>'.$archivo.'</td>';
															if(($area_id == 33 AND $cargo_id != 25) || $user_created_id == $login['id']){
																$html .= '<td>'.$archivo_estado.'</td>';
															}
															

															$html .= '</tr>';
															// echo $html;
														?>
														<!--DNI-->
														<?php 
															$html = '';
															$sql = "SELECT rl.id, a.archivo_id, a.contrato_id, t.tipo_archivo_id, t.nombre_tipo_archivo,
																	a.nombre, a.extension, a.ruta
																	FROM cont_archivos a
																	INNER JOIN cont_tipo_archivos t ON t.tipo_archivo_id = a.tipo_archivo_id 
																	INNER JOIN cont_representantes_legales rl ON rl.dni_archivo_id = a.archivo_id
																	WHERE a.status = 1 AND rl.id = " . $id_representante_legal;

															$query = $mysqli->query($sql);
															$row_count = $query->num_rows;
															$tipo_archivo_id = '';
															$archivo_estado = '';
															if($row_count > 0){
																while ($row = $query->fetch_assoc()) 
																{
																	$tipo_archivo_id = $row["tipo_archivo_id"];
																	if (strlen(trim($row["nombre"])) > 1) 
																	{
																		$ruta = str_replace("/var/www/html","",$row["ruta"]);

																		$archivo = '<a style="width: 150px;"';
																		$archivo .= ' onclick="sec_con_detalle_int_ver_documento_en_visor(\''. trim($ruta) . '\', \''. trim($row["nombre"]) . '\', \'' . trim($row["extension"]) . '\', \'' . $row["nombre_tipo_archivo"] . '\');"';
																		$archivo .= ' class="btn btn-success btn-xs btn-block"';
																		$archivo .= ' data-toggle="tooltip"';
																		$archivo .= ' data-placement="top">';
																		$archivo .= ' <span class="fa fa-eye"></span> Ver Documento';
																		$archivo .= ' </a>';

																		$archivo_estado = '<a class="class_SubirFile_req_solicitud_arrendamiento btn btn-success btn-xs btn-block" id="btnSubirFile_req_solicitud_arrendamiento" data-toggle="tooltip" data-placement="top" onclick="sec_con_detalle_int_archivo_representante_legal(\''.$row["nombre_tipo_archivo"].'\', \''.$row["archivo_id"].'\', \''.$row["tipo_archivo_id"].'\',' . $id_representante_legal . ');"><i class="fa fa-upload"></i> Reemplazar</a>';
																	}
																	else 
																	{
																		$archivo = '<a class="btn btn-warning btn-xs btn-block" data-toggle="tooltip" data-placement="top"><span class="fa fa-eye-slash"></span> Incompleto</a>';

																		$archivo_estado = '<a class="class_SubirFile_req_solicitud_arrendamiento btn btn-warning btn-xs btn-block" id="btnSubirFile_req_solicitud_arrendamiento" data-toggle="tooltip" data-placement="top" onclick="sec_con_detalle_int_archivo_representante_legal(\''.$row["nombre_tipo_archivo"].'\', \''.$row["archivo_id"].'\', \''.$row["tipo_archivo_id"].'\',' . $id_representante_legal . ');"><i class="fa fa-upload"></i> Subir</a>';
																	}

																	
																}
															}else{
																$archivo = '<a style="width: 150px;" class="btn btn-warning btn-xs btn-block" data-toggle="tooltip" data-placement="top"><span class="fa fa-eye-slash"></span> Incompleto</a>';

																$archivo_estado = '<a class="class_SubirFile_req_solicitud_arrendamiento btn btn-warning btn-xs btn-block" id="btnSubirFile_req_solicitud_arrendamiento" data-toggle="tooltip" data-placement="top" onclick="sec_con_detalle_int_archivo_representante_legal(\'DNI del representante legal de la empresa proveedora\', \'\', \'3\',' . $id_representante_legal . ');"><i class="fa fa-upload"></i> Subir</a>';
															}
															
															
															$html .= '<tr style="text-transform: none;">';
															$html .= '<td style="text-transform: uppercase;"><b>DNI del representante legal de la empresa proveedora</b></td>';

															$html .= '<td>'.$archivo.'</td>';
															if(($area_id == 33 AND $cargo_id != 25) || $user_created_id == $login['id']){
																$html .= '<td>'.$archivo_estado.'</td>';
															}

															$html .= '</tr>';
															// echo $html;


														?>
													</table>
													<hr>
													<?php
													}
												}
												?>
											</div>
										</div>
									</div>
								</div>
							</div>


							<div class="panel panel-default">
								<div class="panel-heading" role="tab" id="headingCCObjetoDelContrato">
								<h4 class="panel-title">
									<a role="button" data-toggle="collapse" data-parent="#accordionContrato" href="#collapseCCObjetoDekContrato" aria-expanded="true" aria-controls="collapseCCObjetoDekContrato">
									CONDICIONES COMERCIALES - 1) OBJETO DEL CONTRATO
									</a>
								</h4>
								</div>
								<div id="collapseCCObjetoDekContrato" class="panel-collapse-all panel-collapse collapse in" role="tabpanel" aria-labelledby="headingCCObjetoDelContrato">
									<div class="panel-body">
										<div class="W-100">
											<div class="form-group">
												<table class="table table-bordered table-hover">
													<tr>
														<td style="white-space: pre-line;"><?php echo $detalle_servicio;?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td style="width: 75px;">
															<a class="btn btn-success btn-xs" 
															onclick="sec_con_detalle_int_editar_solicitud('Condiciones Comerciales','cont_contrato','detalle_servicio','Objeto del Contrato','textarea','<?php echo replace_invalid_caracters_vista($detalle_servicio); ?>','','');">
																<span class="fa fa-edit"></span> Editar
															</a>
														</td>
														<?php } ?>
													</tr>
												</table>
											</div>
										</div>
									</div>
								</div>
							</div>


							<div class="panel panel-default">
								<div class="panel-heading" role="tab" id="headingCCPlazoDelContrato">
								<h4 class="panel-title">
									<a role="button" data-toggle="collapse" data-parent="#accordionContrato" href="#collapseCCPlazoDelContrato" aria-expanded="true" aria-controls="collapseCCPlazoDelContrato">
									CONDICIONES COMERCIALES - 2) PLAZO DEL CONTRATO
									</a>
								</h4>
								</div>
								<div id="collapseCCPlazoDelContrato" class="panel-collapse-all panel-collapse collapse in" role="tabpanel" aria-labelledby="headingCCPlazoDelContrato">
									<div class="panel-body">
										<div class="w-100">
											<div id="divTablaPropietarios" class="form-group">
												<table class="table table-bordered">
													<tr>
														<td style="width: 50%;"><b>Plazo</b></td>
														<td><?php echo $plazo;?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td>
															<a class="btn btn-success btn-xs" 
															onclick="sec_contrato_detalle_solicitud_editar_solicitud('Plazo del Contrato','cont_contrato','plazo_id','Plazo','select_option','<?php echo $periodo_numero; ?>','obtener_tipo_plazo');">
																<span class="fa fa-edit"></span> Editar
															</a>
														</td>
														<?php } ?>
													</tr>

													<?php if($plazo_id == 1) { ?>

													<tr>
														<td style="width: 50%;"><b>Periodo (Número)</b></td>
														<td><?php echo $periodo_numero; ?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td>
															<a class="btn btn-success btn-xs" 
															onclick="sec_contrato_detalle_solicitud_editar_solicitud('Plazo del Contrato','cont_contrato','periodo_numero','Periodo (Número)','int','<?php echo $periodo_numero; ?>','');">
																<span class="fa fa-edit"></span> Editar
															</a>
														</td>
														<?php } ?>
													</tr>

													<tr>
														<td style="width: 50%;"><b>Periodo (Año o Mes)</b></td>
														<td><?php echo $periodo_anio_mes; ?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td>
															<a class="btn btn-success btn-xs" 
															onclick="sec_contrato_detalle_solicitud_editar_solicitud('Plazo del Contrato','cont_contrato','periodo','Periodo (Año o Mes)','select_option','<?=$periodo_anio_mes ?>','obtener_periodo','');">
																<span class="fa fa-edit"></span> Editar
															</a>
														</td>
														<?php } ?>
													</tr>

													<?php } ?>

													<tr>
														<td><b>Fecha de inicio</b></td>
														<td><?php echo $fecha_inicio;?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td style="width: 75px;">
															<a class="btn btn-success btn-xs" 
															onclick="sec_con_detalle_int_editar_solicitud('Plazo del Contrato','cont_contrato','fecha_inicio','Fecha de Inicio','date','<?php echo $fecha_inicio; ?>','','');">
																<span class="fa fa-edit"></span> Editar
															</a>
														</td>
														<?php } ?>
													</tr>
												</table>
											</div>
										</div>
									</div>
								</div>
							</div>


							<div class="panel panel-default">
								<div class="panel-heading" role="tab" id="headingContraprestacion">
								<h4 class="panel-title">
									<a role="button" data-toggle="collapse" data-parent="#accordionContrato" href="#collapseContraprestacion" aria-expanded="true" aria-controls="collapseContraprestacion">
									CONDICIONES COMERCIALES - 3) CONTRAPRESTACIÓN
									</a>
								</h4>
								</div>
								<div id="collapseContraprestacion" class="panel-collapse-all panel-collapse collapse in" role="tabpanel" aria-labelledby="headingContraprestacion">
									<div class="panel-body">
										<div class="w-100 text-right">
											<?php if ($btn_editar_solicitud) { ?>
											<button 
												type="button" 
												class="btn btn-xs btn-primary" 
												style="display: none;" 
												onclick="sec_contrato_detalle_solicitud_agregar_contraprestacion()">
												<i class="fa fa-plus"></i>
												Agregar Contraprestación
											</button>
											<?php } ?>
										</div>

										<div class="w-100 mt-1">
											<div id="divTablaPropietarios" class="form-group">
												<?php
												$sql_contraprestacion = "
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
													c.contrato_id = $contrato_id
													AND c.status = 1
												";

												$query = $mysqli->query($sql_contraprestacion);
												$row_count = $query->num_rows;

												
													$contador_contraprestacion = 1;
													while($sel = $query->fetch_assoc()){
														$contraprestacion_id = $sel["id"];
														$tipo_moneda = $sel["tipo_moneda"];
														$tipo_moneda_simbolo = $sel["tipo_moneda_simbolo"];
														$subtotal = $tipo_moneda_simbolo.' '.number_format($sel["subtotal"], 2, '.', ',');
														$igv = $tipo_moneda_simbolo.' '.number_format($sel["igv"], 2, '.', ',');
														$monto = $tipo_moneda_simbolo.' '.number_format($sel["monto"], 2, '.', ',');
														$forma_pago_detallado = $sel["forma_pago_detallado"];
														$tipo_comprobante = $sel["tipo_comprobante"];
														$plazo_pago = $sel["plazo_pago"];
														?>
														<b>Contraprestación # <?php echo $contador_contraprestacion; ?></b>
														<table class="table table-bordered">
															<tr>
																<td style="width: 50%;"><b>Tipo de moneda</b></td>
																<td><?php echo $tipo_moneda ;?></td>
																<?php if ($btn_editar_solicitud) { ?>
																<td style="width: 75px;">
																	<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_int_editar_solicitud('Contraprestación','cont_contraprestacion','moneda_id','Tipo de moneda','select_option','<?php echo $tipo_moneda; ?>','obtener_tipo_moneda','<?php echo $contraprestacion_id; ?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
																<?php } ?>
															</tr>
															<tr>
																<td><b>Subtotal</b></td>
																<td><?php echo $subtotal;?></td>
																<?php if ($btn_editar_solicitud) { ?>
																<td>
																	<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_int_editar_solicitud('Contraprestación','cont_contraprestacion','subtotal','Subtotal','decimal','<?php echo $subtotal; ?>','','<?php echo $contraprestacion_id; ?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
																<?php } ?>
															</tr>
															<tr>
																<td><b>IGV</b></td>
																<td><?php echo $igv;?></td>
																<?php if ($btn_editar_solicitud) { ?>
																<td>
																	<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_int_editar_solicitud('Contraprestación','cont_contraprestacion','igv','IGV','decimal','<?php echo $igv; ?>','','<?php echo $contraprestacion_id; ?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
																<?php } ?>
															</tr>
															<tr>
																<td><b>Monto Bruto</b></td>
																<td><?php echo $monto;?></td>
																<?php if ($btn_editar_solicitud) { ?>
																<td>
																	<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_int_editar_solicitud('Contraprestación','cont_contraprestacion','monto','Monto','decimal','<?php echo $monto; ?>','','<?php echo $contraprestacion_id; ?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
																<?php } ?>
															</tr>
												
															<tr>
																<td><b>Tipo de comprobante a emitir</b></td>
																<td><?php echo $tipo_comprobante;?></td>
																<?php if ($btn_editar_solicitud) { ?>
																<td>
																	<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_int_editar_solicitud('Contraprestación','cont_contraprestacion','tipo_comprobante_id','Tipo de comprobante a emitir','select_option','<?php echo $tipo_comprobante; ?>','obtener_tipo_comprobante','<?php echo $contraprestacion_id; ?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
																<?php } ?>
															</tr>
															<tr>
																<td><b>Plazo de Pago</b></td>
																<td><?php echo $sel["plazo_pago"];?></td>
																<?php if ($btn_editar_solicitud) { ?>
																<td>
																	<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_int_editar_solicitud('Contraprestación','cont_contraprestacion','plazo_pago','Plazo de Pago','varchar','<?php echo $sel["plazo_pago"]; ?>','','<?php echo $contraprestacion_id; ?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
																<?php } ?>
															</tr>
															<tr>
																<td><b>Forma de pago</b></td>
																<td><?php echo $forma_pago_detallado;?></td>
																<?php if ($btn_editar_solicitud) { ?>
																<td>
																	<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_int_editar_solicitud('Contraprestación','cont_contraprestacion','forma_pago_detallado','Forma de pago - Detallado','varchar','<?php echo $forma_pago_detallado; ?>','','<?php echo $contraprestacion_id; ?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
																<?php } ?>
															</tr>
														</table>
														<br>
														<?php
														$contador_contraprestacion++;
													}
												?>
											</div>
										</div>
									</div>
								</div>
							</div>


							<div class="panel panel-default">
								<div class="panel-heading" role="tab" id="headingObservaciones">
								<h4 class="panel-title">
									<a role="button" data-toggle="collapse" data-parent="#accordionContrato" href="#collapseObservaciones" aria-expanded="true" aria-controls="collapseObservaciones">
									CONDICIONES COMERCIALES - 4) OBSERVACIONES
									</a>
								</h4>
								</div>
								<div id="collapseObservaciones" class="panel-collapse-all panel-collapse collapse in" role="tabpanel" aria-labelledby="headingObservaciones">
									<div class="panel-body">
										<div class="w-100">
											<div class="form-group">
												<table class="table table-bordered table-hover">
													<tr>
														<td style="white-space: pre-line;"><?php echo $observaciones;?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td style="width: 75px;">
															<a class="btn btn-success btn-xs" 
															onclick="sec_con_detalle_int_editar_solicitud('Observaciones','cont_contrato','observaciones','Observaciones','textarea','<?php echo replace_invalid_caracters_vista($observaciones); ?>','','');">
																<span class="fa fa-edit"></span> Editar
															</a>
														</td>
														<?php } ?>
													</tr>
												</table>
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
								<i class="fa fa-arrows-alt"></i>  Ver documento en toda la Pantalla
							</button>        
						</div>

						<div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center;margin-bottom: 5px; display:none;" id="divVisorPdfPrincipal">
						</div>

						<div class="col-xs-9 col-md-9 col-sm-9" style="text-align: center;margin-bottom: 5px; display:none;" id="divVerImagenFullPantalla">
							<button type="button" class="btn btn-block btn-block btn-primary" id="sec_contrato_detalle_solicitud_ver_imagen_full_pantalla" style="background-color:#7dc623;">
								<i class="fa fa-arrows-alt"></i>  Ver imagen en toda la Pantalla
							</button>
						</div>

						<div class="col-xs-3 col-md-3 col-sm-3" style="text-align: center;margin-bottom: 5px; display:none;" id="divDescargarImagen">
							<a class="btn btn-block btn-info" id="sec_contrato_detalle_solicitud_descargar_imagen">
								<i class="fa fa-cloud-download"></i>  Descargar
							</a>
						</div>

						<div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center;margin-bottom: 5px; display:none;" id="divVisorImagen">
							<img src="" class="img-responsive" style="border: 1px solid;">
						</div>
					</div>

				</div>

			</div>


			<div class="col-xs-12 col-md-12 col-lg-5">
				<div class="panel" id="divDetalleSolicitud">
					<div class="panel-body" style="padding: 5px 10px 5px 10px;">
						<div class="panel-group no-mb" id="accordion" role="tablist" aria-multiselectable="true">

							
							

							<!-- PANEL: DOCUMENTOS INICIO -->
							<div class="panel">
								<div class="panel-heading" role="tab" id="browsers-this-week-heading">
									<div class="panel-title">
										<a href="#browsers-this-week" role="button" data-toggle="collapse"
										   data-parent="#accordion" aria-expanded="true" aria-controls="browsers-this-week">
											Documentos
										</a>
									</div>
								</div>

								<div id="browsers-this-week" class="panel-collapse collapse in" role="tabpanel"
									 aria-labelledby="browsers-this-week-heading">
									<div class="panel-body">
										<button type="button" class="btn btn-sm btn-info" onclick="sec_con_detalle_int_agregarNuevoAnexoConProv();"><i class="fa fa-plus"></i> Agregar Nuevo Anexo</button>
										<table class="table table-responsive no-mb" style="font-size: 10px;">
											<thead style="background: none;">
												<tr style="text-transform: none;">
													<th align="center">Nombre del Documento</th>
													
													<?php  

													if(($area_id == 33 AND $cargo_id != 25) || $user_created_id == $login['id'])
													{
														?>
															<th align="center">Operación</th>
														<?php
													}
													?>
													
													<th align="center">Visualizar</th>
												</tr>
											</thead>
											<tbody>
												<tr style="text-transform: none;">
													<td>Solicitud de Contrato Interno</td>
													<td colspan="2">
														<a onclick="sec_con_detalle_int_ver_documento_en_visor('','', 'html', '');" class="btn btn-primary btn-xs btn-block" data-toggle="tooltip" data-placement="top"><span class="fa fa-eye"></span> Ver Detalle de la Solicitud</a>
													</td>
												</tr>

												<?php
												// INICIO CONTRATO FIRMADO
												if ((array_key_exists($menu_consultar,$usuario_permisos) && in_array("ver_contrato_firmado", $usuario_permisos[$menu_consultar]))) { 
											
													$sel_contrato_firmado = $mysqli->query("
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
														tipo_archivo_id = 19
														AND status = 1
														AND contrato_id = " . $contrato_id
													);
													$num_rows = mysqli_num_rows($sel_contrato_firmado);
													?>
	
													<tr style="text-transform: none;">
														<td>Contrato Firmado</td>
														
	
													<?php
													if ($num_rows == 0) {
													?>
														<td colspan="2">
															<a class="btn btn-warning btn-xs btn-block" data-toggle="tooltip" data-placement="top"><span class="fa fa-eye-slash"></span> Incompleto</a>
														</td>
													<?php 
													} else if($num_rows > 0) {
														$row = $sel_contrato_firmado->fetch_assoc();
														$ruta = str_replace("/var/www/html","",$row["ruta"]);
													?>
														<td>
															<a
																onclick="sec_con_detalle_int_moda_reemplazar_archivo('<?=$row['nombre'];?>','<?=$row['archivo_id'];?>','<?=$row['tipo_archivo_id'];?>');"
																class="btn btn-success btn-xs btn-block"
																data-toggle="tooltip"
																data-placement="top">
																<span class="fa fa-upload"></span> Reemplazar
															</a>
														</td>
														<td>
															<a
																onclick="sec_contrato_detalle_solicitud_ver_documento_en_visor('<?php echo $ruta;?>','<?php echo trim($row['nombre']); ?>','<?php echo trim($row['extension']); ?>','CONTRATO FIRMADO');"
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

												// INICIO ADENDA FIRMADA
												if ((array_key_exists($menu_consultar,$usuario_permisos) && in_array("ver_adenda_firmado", $usuario_permisos[$menu_consultar]))) { 
												
													$sel_contrato_firmado = $mysqli->query("
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
														ar.tipo_archivo_id = 98
														AND ar.status = 1
														AND ar.contrato_id = " . $contrato_id.
														" ORDER BY ad.codigo ASC"
													);
													$num_rows = mysqli_num_rows($sel_contrato_firmado);
													if($num_rows > 0) {
														while ($row = $sel_contrato_firmado->fetch_assoc()){
															$ruta = str_replace("/var/www/html","",$row["ruta"]);
															?>
															<tr style="text-transform: none;">
																<td>Adenda Firmada N° <?=$row['codigo']?></td>
																<td>
																	<a
																		onclick="sec_con_detalle_int_moda_reemplazar_archivo('<?=$row['nombre'];?>','<?=$row['archivo_id'];?>','<?=$row['tipo_archivo_id'];?>');"
																		class="btn btn-success btn-xs btn-block"
																		data-toggle="tooltip"
																		data-placement="top">
																		<span class="fa fa-upload"></span> Reemplazar
																	</a>
																</td>
																<td>
																	<a
																		onclick="sec_contrato_detalle_solicitud_ver_documento_en_visor('<?php echo $ruta;?>','<?php echo trim($row["nombre"]); ?>','<?php echo trim($row["extension"]); ?>','ADENDA FIRMADA');"
																		class="btn btn-success btn-xs btn-block"
																		data-toggle="tooltip"
																		data-placement="top">
																		<span class="fa fa-eye"></span> Ver Adenda Firmada
																	</a>
																</td>
															</tr>
																
															<?php
														}
													}
										
												}
												// FIN ADENDA FIRMADA


												$html = '';

												$sql = "
												SELECT 
													*
												FROM
													(
													

													SELECT 
														a.archivo_id,
														a.contrato_id,
														t.tipo_archivo_id,
														t.nombre_tipo_archivo,
														a.nombre,
														a.extension,
														a.ruta,
														IFNULL(rl.id, 0) AS id_representante_legal,
														IFNULL(rl.nombre_representante, '') AS nombre_representante
													FROM
														cont_tipo_archivos t
														INNER JOIN cont_archivos a ON t.tipo_archivo_id = a.tipo_archivo_id AND a.contrato_id = $contrato_id AND a.status = 1
														LEFT JOIN cont_representantes_legales rl ON rl.vigencia_archivo_id = a.archivo_id OR rl.dni_archivo_id = a.archivo_id
													WHERE
														t.tipo_archivo_id NOT IN (1,19,152)
													) z
												";

												$query = $mysqli->query($sql);
												$row_count = $query->num_rows;

												if ($row_count > 0) 
												{
													while ($row = $query->fetch_assoc()) 
													{
														if (strlen(trim($row["nombre"])) > 1) 
														{
															$ruta = str_replace("/var/www/html","",$row["ruta"]);

															$archivo = '<a';
															$archivo .= ' onclick="sec_con_detalle_int_ver_documento_en_visor(\''. trim($ruta) . '\', \''. trim($row["nombre"]) . '\', \'' . trim($row["extension"]) . '\', \'' . $row["nombre_tipo_archivo"] . '\');"';
															$archivo .= ' class="btn btn-success btn-xs btn-block"';
															$archivo .= ' data-toggle="tooltip"';
															$archivo .= ' data-placement="top">';
															$archivo .= ' <span class="fa fa-eye"></span> Ver Documento';
															$archivo .= ' </a>';

															if($row["id_representante_legal"] > 0){
																$archivo_estado = '<a class="class_SubirFile_req_solicitud_arrendamiento btn btn-success btn-xs btn-block" id="btnSubirFile_req_solicitud_arrendamiento" data-toggle="tooltip" data-placement="top" onclick="sec_con_detalle_int_archivo_representante_legal(\''.$row["nombre_tipo_archivo"].'\', \''.$row["archivo_id"].'\', \''.$row["tipo_archivo_id"].'\',\''. $row["id_representante_legal"] .'\');"><i class="fa fa-upload"></i> Reemplazar</a>';
															}else{
																$archivo_estado = '<a class="class_SubirFile_req_solicitud_arrendamiento btn btn-success btn-xs btn-block" id="btnSubirFile_req_solicitud_arrendamiento" data-toggle="tooltip" data-placement="top" onclick="sec_con_detalle_int_moda_subir_archivo(\''.$row["nombre_tipo_archivo"].'\', \''.$row["archivo_id"].'\', \''.$row["tipo_archivo_id"].'\');"><i class="fa fa-upload"></i> Reemplazar</a>';
															}
														}
														else 
														{
															$archivo = '<a class="btn btn-warning btn-xs btn-block" data-toggle="tooltip" data-placement="top"><span class="fa fa-eye-slash"></span> Incompleto</a>';

															$archivo_estado = '<a class="class_SubirFile_req_solicitud_arrendamiento btn btn-warning btn-xs btn-block" id="btnSubirFile_req_solicitud_arrendamiento" data-toggle="tooltip" data-placement="top" onclick="sec_con_detalle_int_moda_subir_archivo(\''.$row["nombre_tipo_archivo"].'\', \''.$row["archivo_id"].'\', \''.$row["tipo_archivo_id"].'\');"><i class="fa fa-upload"></i> Subir</a>';
														}

														$html .= '<tr style="text-transform: none;">';
														if($row["id_representante_legal"] > 0){
															$html .= '<td>Anexo - ' . $row["nombre_tipo_archivo"] . ' (' . $row["nombre_representante"] . ')</td>';
														}else{
															$html .= '<td>Anexo - ' . $row["nombre_tipo_archivo"] . '</td>';
														}

														if(($area_id == 33 AND $cargo_id != 25) || $user_created_id == $login['id'])
														{
															$html .= '<td>'.$archivo_estado.'</td>';
														}

														$html .= '<td>'.$archivo.'</td>';
														$html .= '</tr>';
													}
												}

												echo $html;

												?>
										<?php
										$sel_contrato_firmado = $mysqli->query("
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
											AND ar.contrato_id = " . $contrato_id.
											" ORDER BY ar.archivo_id ASC"
										);
										$num_rows = mysqli_num_rows($sel_contrato_firmado);
										if($num_rows > 0) {
											$cont_adendas_archivos = 0;
											while ($row = $sel_contrato_firmado->fetch_assoc()){
												$cont_adendas_archivos++;
												$ruta = str_replace("/var/www/html","",$row["ruta"]);
												?>
												<tr style="text-transform: none;">
												<td><?= $row['archivo_id']?> Archivos adendas  <?=!Empty($row['codigo']) ? 'N° '.$row['codigo'] : ''?></td>
													<!-- <td>
														<a
															onclick="moda_reemplazar_archivo_req_solicitud_arrendamiento('<?=$row['nombre'];?>','<?=$row['archivo_id'];?>','<?=$row['tipo_archivo_id'];?>');"
															class="btn btn-success btn-xs btn-block"
															data-toggle="tooltip"
															data-placement="top">
															<span class="fa fa-upload"></span> Reemplazar
														</a>
													</td> -->
													<td>
														<a
															onclick="sec_contrato_detalle_solicitud_ver_documento_en_visor('<?php echo $ruta;?>','<?php echo trim($row["nombre"]); ?>','<?php echo trim($row["extension"]); ?>','ARCHIVO ADENDA');"
															class="btn btn-success btn-xs btn-block"
															data-toggle="tooltip"
															data-placement="top">
															<span class="fa fa-eye"></span> Ver archivo Adenda
														</a>
													</td>
												</tr>
													
												<?php
											}
										}
										?>
											</tbody>
										</table>
									</div>
								</div>
							</div>
							<!-- PANEL: DOCUMENTOS FIN -->


							<!-- PANEL: ADENDAS INICIO -->
							<?php
							$numero_adenda = 0;
							
							$sel_query = $mysqli->query("
							SELECT 
								a.id,
								a.codigo,
								a.procesado,
								a.created_at AS fecha_solicitud,
								concat(IFNULL(p.nombre, ''), ' ',  IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS solicitante,
								concat(IFNULL(pab.nombre, ''), ' ',  IFNULL(pab.apellido_paterno, ''), ' ', IFNULL(pab.apellido_materno, '')) AS abogado,
								ar.nombre AS area,
								a.cancelado_id,
								CONCAT(IFNULL(tpa3.nombre, ''),' ',IFNULL(tpa3.apellido_paterno, ''),	' ',	IFNULL(tpa3.apellido_materno, '')) AS cancelado_por,
								a.cancelado_el,
								a.cancelado_motivo,
								a.estado_solicitud_id
							FROM 
								cont_adendas a
								INNER JOIN tbl_usuarios u ON a.user_created_id = u.id
								INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
								
								LEFT JOIN tbl_usuarios uab ON a.abogado_id = uab.id
								LEFT JOIN tbl_personal_apt pab ON uab.personal_id = pab.id

								INNER JOIN tbl_areas ar ON p.area_id = ar.id
								LEFT JOIN tbl_usuarios tu3 ON a.cancelado_por_id = tu3.id
								LEFT JOIN tbl_personal_apt tpa3 ON tu3.personal_id = tpa3.id
							WHERE a.cancelado_id IS NULL AND a.contrato_id = " . $contrato_id . "
							AND a.status = 1;");
							$row_cnt = $sel_query->num_rows;
							if ($row_cnt > 0) {
							?>

							<div class="panel">
								<div class="panel-heading" role="tab" id="browsers-adendas-heading">
									<div class="panel-title">
										<a href="#browsers-adendas" class="collapsed" role="button" data-toggle="collapse"
										   data-parent="#accordion" aria-expanded="true" aria-controls="browsers-adendas">
											Adendas
										</a>
									</div>
								</div>

								<div id="browsers-adendas" class="panel-collapse collapse" role="tabpanel"
									 aria-labelledby="browsers-adendas-heading">
									<div class="panel-body">

										<?php 
									   
										while($sel=$sel_query->fetch_assoc()){
											$adenda_id = $sel["id"];
											$codigo = $sel["codigo"];
											$procesado = $sel["procesado"];
											$cancelado_id = $sel["cancelado_id"];
											$cancelado_por = $sel['cancelado_por'];
											$abogado = $sel['abogado'];
											$cancelado_el = $sel['cancelado_el'];
											$cancelado_motivo = $sel['cancelado_motivo'];
											$area = $sel["area"];
											$solicitante = $sel["solicitante"];
											$fecha_solicitud = $sel["fecha_solicitud"];
											$estado_solicitud_id = $sel["estado_solicitud_id"];
											$numero_adenda++;

											if ($cancelado_id == 1) {
												$adenda_estado = '<span class="badge bg-danger text-white">Cancelada</span>';
											} elseif ($procesado == 0) {
												$adenda_estado = '<span class="badge bg-warning text-white">Pendiente</span>';
											} elseif ($procesado == 1) {
												$adenda_estado = '<span class="badge bg-info text-white">Procesado</span>';
											}
										?>

										<p>
											<b>Adenda N° <?php echo $codigo; ?>:</b> 
											<?php if ((array_key_exists($menu_consultar,$usuario_permisos) && in_array("reenviar_adenda_firmada", $usuario_permisos[$menu_consultar])) && ($procesado == 1)) { ?>
											<button type="button" onclick="sec_contrato_detalle_reenviar_adenda(<?=$sel['id']?>,'7')" style="float: right" class="btn btn-xs btn-info"><span class="fa fa-envelope-o"></span> Reenviar por email</button>
											<?php } ?>
										</p> 

										<table class="table table-responsive table-hover no-mb" style="font-size: 10px;">
											<tbody>
												<tr style="text-transform: none;">
													<td><b>Área solicitante</b></td>
													<td>
														<?php echo $area; ?>
													</td>
												</tr>
												<tr style="text-transform: none;">
													<td><b>Abogado</b></td>
													<td>
														<?php echo $abogado; ?>
													</td>
													<?php if ($btn_editar_solicitud || ($procesado == 0 && $area_id == 33 && $cargo_id != 25)) { ?>
													<td>
														<a 	class="btn btn-success btn-xs" 
															id="btn_editar_adenda_abogado_<?=$adenda_id?>"
															onclick="sec_contrato_detalle_solicitud_editar_solicitud('Adenda','cont_adendas','abogado_id','Abogado','select_option','<?php echo $abogado; ?>','obtener_abogados','<?php echo $adenda_id; ?>');">
															<span class="fa fa-edit"></span> Editar
														</a>
													</td>
													<?php } ?>
												</tr>
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

												<?php if ($cancelado_id == 1) { ?>

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
													$select_archivo = $mysqli->query("
													SELECT 
														a.ruta,
														a.nombre,
														a.extension,
														a.created_at,
														concat( IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS subido_por
													FROM 
														cont_archivos a
														INNER JOIN tbl_usuarios AS tu	ON tu.id = a.user_created_id
														INNER JOIN tbl_personal_apt AS tp ON tp.id = tu.personal_id
													WHERE 
														a.contrato_id = $contrato_id
														AND a.adenda_id = $adenda_id
													");
													$row_cnt = $select_archivo->num_rows;
												?>
												<tr style="text-transform: none;">
													<td><b>Adenda firmada</b></td>
													<td>
												<?php
													if ($row_cnt > 0) {
														$row = $select_archivo->fetch_assoc();
														echo '
														<a 
														class="btn btn-success btn-xs" 
														data-toggle="tooltip" 
														data-placement="top" 
														data-original-title="" 
														title=""
														onclick="sec_con_detalle_int_ver_documento_en_visor(\'' . str_replace("/var/www/html","",$row["ruta"]) . '\',\'' . trim($row["nombre"]) . '\',\'' . trim($row["extension"]) . '\',\'ADENDA FIRMADA\');"
														><span class="fa fa-eye"></span> Ver documento</a> (Subido por ' . $row['subido_por'] . ' el ' . $row['created_at'] . ')';
													} else {
														echo '<a class="btn btn-success btn-xs" data-toggle="tooltip" data-placement="top" data-original-title="" title=""><span class="fa fa-eye"></span> Ver documento</a> (Subido por Alguien)';
													}
													?>
													</td>
												</tr>
												<?php
												}
												?>
											</tbody>
										</table>

										<br>

										<p><b>Adenda N° <?php echo $codigo; ?> - Detalle</b></p>
										<?php
											$numero_adenda_detalle = 0;

											$query = $mysqli->query("
											SELECT id,
												adenda_id,
												nombre_tabla,
												valor_original,
												nombre_campo_usuario,
												nombre_campo,
												tipo_valor,
												valor_varchar,
												valor_int,
												valor_date,
												valor_decimal,
												valor_select_option,
												status
											FROM cont_adendas_detalle
											WHERE adenda_id = " . $adenda_id . "
											AND tipo_valor != 'id_tabla' AND tipo_valor != 'registro'
											AND status = 1");
											$row_count = $query->num_rows;
											if ($row_count > 0) {
										?>
										<table class="table table-responsive table-hover no-mb" style="font-size: 10px;">
											<thead style="background: none;">
												<tr style="text-transform: none;">
													<th align="center">#</th>
													<th align="center">Campo</th>
													<th align="center">Valor Original</th>
													<th align="center">Nuevo Valor</th>
												</tr>
											</thead>
											<tbody>

											<?php
												while($row = $query->fetch_assoc()){
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

													$numero_adenda_detalle++;
											?>
												<tr style="text-transform: none;">
													<td>
														<?php echo $numero_adenda_detalle; ?>
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
										$query = $mysqli->query("
										SELECT id,
											adenda_id,
											nombre_tabla,
											valor_original,
											nombre_menu_usuario,
											nombre_campo_usuario,
											nombre_campo,
											tipo_valor,
											valor_varchar,
											valor_int,
											valor_date,
											valor_decimal,
											valor_select_option,
											status
										FROM cont_adendas_detalle
										WHERE adenda_id = " . $adenda_id . "
										AND tipo_valor = 'registro'
										AND status = 1");
										$row_count = $query->num_rows;
										$numero_adenda_detalle = 0;
										if ($row_count > 0) {
											while($row = $query->fetch_assoc()){
												if ($row["nombre_menu_usuario"] == 'Cuenta Bancaria') {
													$query_pro = "SELECT 
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
													$list_query = $mysqli->query($query_pro);
													while ($li = $list_query->fetch_assoc()) {
														if ($li["id"] == $row["valor_int"]) {
															$valores_nuevos[] = $li;
														}
													}
													?>
													<div>
													<br>
													</div>
			
													<div>
													<table class="table table-responsive table-hover no-mb" style="font-size: 10px;">
								
													<thead style="background: none;">
													<tr style="text-transform: none;">
														<th colspan="4" >
															<b>Nueva Cuenta Bancaria</b>
														</th>
													</tr>
								
													<tr>
														<th align="center">Campo:</th>
														<th align="center">Valor:</th>
													</tr>
													
													</thead>

								
													<tr>
														<td >Banco</td>
														<td ><?=$valores_nuevos[0]["banco_representante"]?></td>
													</tr>
								
													<tr>
														<td >Nro Cuenta</td>
														<td ><?=$valores_nuevos[0]["nro_cuenta"]?></td>
													</tr>
								
													<tr>
														<td >Nro CCI</td>
														<td ><?=$valores_nuevos[0]["nro_cci"]?></td>
													</tr>
								
													</table>
													</div>

												<?php
												}
								
												if ($row["nombre_menu_usuario"] == 'Contraprestación') {
													$query_cont = "SELECT 
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
													$list_query = $mysqli->query($query_cont);
													while ($li = $list_query->fetch_assoc()) {
														if ($li["id"] == $row["valor_int"]) {
															$valores_nuevos[] = $li;
														}
													}
													?>
													<div>
													<br>
													</div>
								
													<div>
													<table class="table table-responsive table-hover no-mb" style="font-size: 10px;">
								
													<thead style="background: none;">
													<tr style="text-transform: none;">
														<th colspan="4">
															<b>Nueva Contraprestación</b>
														</th>
													</tr>
								
													<tr>
														<td align="center" class="text-dark">Campo:</td>
														<td align="center" class="text-dark">Valor:</td>
													</tr>
													
													</thead>
								
													<tr>
													<td>Tipo de moneda</td>
													<td><?=$valores_nuevos[0]["tipo_moneda"]?></td>
													</tr>
								
													<tr>
													<td>Subtotal</td>
													<td><?=$valores_nuevos[0]["subtotal"]?></td>
													</tr>
								
													<tr>
													<td>IGV</td>
													<td><?=$valores_nuevos[0]["igv"]?></td>
													</tr>
								
													<tr>
													<td>Monto Bruto</td>
													<td><?=$valores_nuevos[0]["monto"]?></td>
													</tr>
								
													<tr>
													<td>Tipo de comprobante a emitir</td>
													<td><?=$valores_nuevos[0]["tipo_comprobante"]?></td>
													</tr>
								
													<tr>
													<td>Plazo de Pago</td>
													<td><?=$valores_nuevos[0]["plazo_pago"]?></td>
													</tr>
								
													<tr>
													<td>Forma de pago</td>
													<td><?=$valores_nuevos[0]["forma_pago_detallado"]?></td>
													</tr>
								
								
													</table>
													</div>
											<?php
												}
											}
										}
										?>

										<br><br>

										
										<?php
										if ($procesado == 0 && $area_id == '33' && $cancelado_id != 1) {
										?>

										<br>
										<p><b>Adenda N° <?php echo $codigo; ?> - Estado Legal</b></p>

										<table class="table table-bordered table-hover">
											<tr>
												<td>
												<select id="adenda_estado_solicitud_<?php echo $adenda_id;?>" class="form-control">
													<?php
													$query = "SELECT * FROM cont_estado_solicitud WHERE status = 1";
													$list_query = $mysqli->query($query);
													$list = [];

													while ($li = $list_query->fetch_assoc()) 
													{
															$nombre_estado_solicitud = $li["id"] == 1 ? '- Seleccione -': $li["nombre"];
													?>
														<option <?=$estado_solicitud_id == $li["id"] ? 'selected':''; ?>  value="<?=$li["id"] == 1 ? '':$li["id"]; ?>"><?=$nombre_estado_solicitud; ?></option>
													<?php
													}
													?>
												</select>
												</td>
													<td>
													<button onclick="sec_contrato_detalle_solicitud_guardar_estado_solicitud_adenda(<?php echo $adenda_id?>)" class="btn btn-success form-control" type="button"><span class="glyphicon glyphicon-floppy-disk"></span> Guardar Estado</button>
													</td>
												</tr>
										</table>

										<form id="form_adenda_firmada_<?=$adenda_id?>" name="form_contrato_firmado_<?=$adenda_id?>" method="POST" enctype="multipart/form-data" autocomplete="off">

											<div style="margin-right: 10px; margin-left: 5px;">
												<div class="form-group">
													<input type="hidden" name="adenda_id_<?=$adenda_id?>" id="adenda_id_<?=$adenda_id?>" value="<?php echo $adenda_id;?>">
													<div class="control-label">Seleccione la adenda firmada:</div>
													<input type="file" name="adenda_firmada_<?=$adenda_id?>" id="adenda_firmada_<?=$adenda_id?>" required accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip">
												</div>
											</div>

											<div style="margin-right: 10px; margin-left: 5px;">
												<button type="button" class="btn btn-success btn-xs btn-block" id="btn_guardar_adenda_firmada" onclick="sec_con_detalle_int_guardar_adenda_firmada(<?=$adenda_id?>)">
													<i class="icon fa fa-plus"></i>
													<span id="demo-button-text">Agregar adenda firmada</span>
												</button>
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

										<br>
										<hr>
										<?php
											}					
										}
										?>

									</div>
								</div>
							</div>
							<?php
							}
							?>
							<!-- PANEL: ADENDAS FIN -->


							<!-- PANEL: CAMBIOS EN LA SOLICITUD INICIO -->
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
								ar.nombre AS area
							FROM
								cont_auditoria a
									INNER JOIN
								tbl_usuarios u ON a.user_created_id = u.id
									INNER JOIN
								tbl_personal_apt p ON u.personal_id = p.id
									INNER JOIN
								tbl_areas ar ON p.area_id = ar.id
							WHERE
								a.status = 1 AND contrato_id = " . $contrato_id . "
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
										while($sel=$sel_query->fetch_assoc()){
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
							<!-- PANEL: CAMBIOS EN LA SOLICITUD FIN -->


							<!-- PANEL: OBSERVACIONES INICIO -->
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
										<div style="margin-right: 10px; margin-left: 5px; margin-top: -30px;">
											<div id="div_observaciones" class="timeline" style="font-size: 11px;">
											</div>
											<?php 
												$query_emails = "SELECT email FROM cont_contrato_correos WHERE status = 1 AND contrato_id = ".$contrato_id;
												$list_query = $mysqli->query($query_emails);
												$row_count = $list_query->num_rows;
												$email_observaciones = "";
												$i = 1;
												while ($li = $list_query->fetch_assoc()) {
													$email_observaciones .= $li['email'];
													if ($i < $row_count) {
														$email_observaciones .= ", ";
													}
													$i++;
												}
											?>

											<textarea rows="3" id="contrato_observaciones_proveedor" placeholder="Ingrese sus observaciones" style="width: 100%"></textarea>
											<b>Correos Adjuntos: (Opcional)</b>
											<textarea rows="3" id="correos_adjuntos" placeholder="Ingrese Correos Adjuntos" style="width: 100%"><?=$email_observaciones?></textarea>
											<b>Nota: Para más de un correo se debe separar por comas (,)</b>
											<p></p>
											<button class="btn btn-success btn-xs btn-block" type="submit" onclick="sec_con_detalle_int_guardar_observaciones();">
												<i class="fa fa-plus"></i> Agregar y Notificar Observación
											</button>
										</div>
									</div>
								</div>
							</div>
							<!-- PANEL: OBSERVACIONES FIN -->


							<!-- PANEL: CONTRATO FIRMADO -->
							<div class="panel">
								<div class="panel-heading" role="tab" id="browsers-this-day-heading">
									<div class="panel-title">
										<a href="#browsers-this-day" class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" aria-expanded="true" aria-controls="browsers-this-day">
											Contrato final
										</a>
									</div>
								</div>
								<div id="browsers-this-day" class="panel-collapse collapse" role="tabpanel" aria-labelledby="browsers-this-day-heading">
									<div class="panel-body">
										
										<?php 

											$sel_query_info_contrato_firmado_proveedor = $mysqli->query("
												SELECT
													c.contrato_id, c.tipo_contrato_id, tc.nombre AS tipo_contrato,
													c.empresa_suscribe_id, trs.nombre AS razon_social,
													tf.nombre AS tipo_firma, c.fecha_suscripcion_proveedor,
													c.fecha_vencimiento_indefinida_id,c.categoria_id, c.tipo_contrato_proveedor_id,
													c.fecha_inicio, c.fecha_vencimiento_proveedor,
													concat( IFNULL(tpa.nombre, ''),' ', IFNULL(tpa.apellido_paterno, ''),' ', IFNULL(tpa.apellido_materno, '')) AS usuario_aprobado,
													concat( IFNULL(tpc.nombre, ''),' ', IFNULL(tpc.apellido_paterno, ''),' ', IFNULL(tpc.apellido_materno, '')) AS usuario_creado,
													
													cs.nombre AS categoria_servicio, cts.nombre AS tipo_categoria_servicio, c.renovacion_automatica
													
													FROM cont_contrato c
													INNER JOIN cont_tipo_contrato tc
													ON c.tipo_contrato_id = tc.id
													INNER JOIN tbl_razon_social trs
													ON c.empresa_suscribe_id = trs.id
													INNER JOIN tbl_usuarios tua
													ON c.usuario_contrato_proveedor_aprobado_id = tua.id
													INNER JOIN tbl_personal_apt tpa
													ON tua.personal_id = tpa.id
													INNER JOIN tbl_usuarios tu
													ON c.user_created_id = tu.id
													INNER JOIN tbl_personal_apt tpc
													ON tu.personal_id = tpc.id
													INNER JOIN cont_tipo_firma tf
													ON c.tipo_firma_id = tf.id

													LEFT JOIN cont_categoria_servicio AS cs 
													ON cs.id = c.categoria_id
													
													LEFT JOIN cont_tipo_categoria_servicio AS cts 
													ON cts.id = c.tipo_contrato_proveedor_id
	
											WHERE c.contrato_id = ".$contrato_id." AND c.etapa_id = 5 AND c.status = 1
											");

											$cantReg = mysqli_num_rows($sel_query_info_contrato_firmado_proveedor);

											if($cantReg > 0)
											{
												while($sel=$sel_query_info_contrato_firmado_proveedor->fetch_assoc())
												{
													$tipo_contrato = $sel["tipo_contrato"];
													$razon_social = $sel["razon_social"];
													$usuario_aprobado = $sel["usuario_aprobado"];
													$usuario_creado = $sel["usuario_creado"];
													$tipo_firma = $sel["tipo_firma"];
													$fecha_suscripcion_proveedor = date("d-m-Y", strtotime($sel["fecha_suscripcion_proveedor"]));
													$fecha_inicio = date("d-m-Y", strtotime($sel["fecha_inicio"]));
													$fecha_vencimiento_indefinida_id = $sel["fecha_vencimiento_indefinida_id"];
													$categoria_servicio = $sel["categoria_servicio"];
													$tipo_categoria_servicio = $sel["tipo_categoria_servicio"];
													$categoria_id = $sel["categoria_id"];
													$renovacion_automatica = $sel["renovacion_automatica"];
													$renovacion_automatica_value = $renovacion_automatica == 1 ? 'SI':'NO';

													if ($fecha_vencimiento_indefinida_id == 1) {
														$fecha_vencimiento_proveedor = 'Indefinida';
													} else {
														$fecha_vencimiento_proveedor = date("d-m-Y", strtotime($sel["fecha_vencimiento_proveedor"]));
													}

													if ($fecha_vencimiento_indefinida_id == 1) {
														$fecha_vencimiento_indefinida = 'Indefinida';
													}else{
														$fecha_vencimiento_indefinida = 'Definida';
													}
													
										?>

												<table class="table table-responsive table-hover no-mb" style="font-size: 10px;">
													<tbody>
														<tr style="text-transform: none;">
															<td><b>Tipo de contrato:</b></td>
															<td colspan="2">
																<?php echo $tipo_contrato; ?>
															</td>
														</tr>
														<tr style="text-transform: none;">
															<td><b>Contrato aprobado por:</b></td>
															<td colspan="2">
																<?php echo $usuario_aprobado; ?>
															</td>
														</tr>
														<tr style="text-transform: none;">
															<td><b>Contrato cargado por:</b></td>
															<td colspan="2">
																<?php echo $usuario_creado; ?>
															</td>
														</tr>
														<tr style="text-transform: none;">
															<td><b>Categoria:</b></td>
															<td >
																<?php echo $categoria_servicio; ?>
															</td>
															<td>
																<a class="btn btn-success btn-xs" 
																onclick="sec_con_detalle_int_editar_solicitud('Contrato Firmado','cont_contrato','categoria_id','Categoria de Contrato','select_option','<?php echo $categoria_servicio; ?>','obtener_categoria_contrato','');">
																	<span class="fa fa-edit"></span> Editar
																</a>
															</td>
														</tr>
														<tr style="text-transform: none;">
															<td><b>Tipo Categoria:</b></td>
															<td>
																<?php echo $tipo_categoria_servicio; ?>
															</td>
															<td>
																<a class="btn btn-success btn-xs" 
																onclick="sec_contrato_detalle_solicitud_editar_solicitud_param('Contrato Firmado','cont_contrato','tipo_contrato_proveedor_id','Tipo Categoria','select_option','<?php echo $tipo_categoria_servicio; ?>','obtener_tipo_categoria_contrato','','<?=$categoria_id;?>');">
																	<span class="fa fa-edit"></span> Editar
																</a>
															</td>
														</tr>
														<tr style="text-transform: none;">
															<td><b>Tipo Firma:</b></td>
															<td>
																<?php echo $tipo_firma; ?>
															</td>
															<td>
																<a class="btn btn-success btn-xs" 
																onclick="sec_con_detalle_int_editar_solicitud('Contrato Firmado','cont_contrato','tipo_firma_id','Tipo de Firma','select_option','<?php echo $tipo_firma; ?>','obtener_tipo_firma','');">
																	<span class="fa fa-edit"></span> Editar
																</a>
															</td>
														</tr>
														<tr style="text-transform: none;">
															<td><b>Fecha Inicio:</b></td>
															<td>
																<?php echo $fecha_inicio; ?>
															</td>
															<td>
																<a class="btn btn-success btn-xs" 
																onclick="sec_con_detalle_int_editar_solicitud('Contrato Firmado','cont_contrato','fecha_inicio','Fecha Inicio','date','<?php echo $fecha_inicio; ?>','','');">
																	<span class="fa fa-edit"></span> Editar
																</a>
															</td>
														</tr>
														<tr style="text-transform: none;">
															<td><b>Fecha Suscripción:</b></td>
															<td>
																<?php echo $fecha_suscripcion_proveedor; ?>
															</td>
															<td>
																<a class="btn btn-success btn-xs" 
																onclick="sec_con_detalle_int_editar_solicitud('Contrato Firmado','cont_contrato','fecha_suscripcion_proveedor','Fecha Suscripción','date','<?php echo $fecha_suscripcion_proveedor; ?>','','');">
																	<span class="fa fa-edit"></span> Editar
																</a>
															</td>
														</tr>
														<tr style="text-transform: none;">
															<td><b>Periodo:</b></td>
															<td>
																<?php echo $fecha_vencimiento_indefinida ?>
															</td>
															<td>
																<a class="btn btn-success btn-xs" 
																onclick="sec_con_detalle_int_editar_solicitud('Contrato Firmado','cont_contrato','fecha_vencimiento_indefinida_id','Tipo Fecha Vencimiento','select_option','<?php echo $fecha_vencimiento_indefinida; ?>','obtener_tipo_fecha_vencimiento','');">
																	<span class="fa fa-edit"></span> Editar
																</a>
															</td>
														</tr>
														<?php if ($fecha_vencimiento_indefinida_id != 1) { ?>
														<tr style="text-transform: none;">
															<td><b>Fecha Vencimiento:</b></td>
															<td>
																<?php echo $fecha_vencimiento_proveedor; ?>
															</td>
															<td>
																<a class="btn btn-success btn-xs" 
																onclick="sec_contrato_detalle_solicitud_editar_solicitud('Contrato Firmado','cont_contrato','fecha_vencimiento_proveedor','Fecha Vencimiento','date','<?php echo $fecha_vencimiento_proveedor; ?>','','');">
																	<span class="fa fa-edit"></span> Editar
																</a>
															</td>
														</tr>
														<?php } ?>

														<tr style="text-transform: none;">
															<td><b>Renovación Automática:</b></td>
															<td>
																<?php echo $renovacion_automatica_value; ?>
															</td>
															<td>
																<a class="btn btn-success btn-xs" 
																onclick="sec_contrato_detalle_solicitud_editar_solicitud('Contrato Firmado','cont_contrato','renovacion_automatica','Renovación Automática','select_option','<?php echo $renovacion_automatica_value; ?>','obtener_si_no','');">
																	<span class="fa fa-edit"></span> Editar
																</a>
															</td>
														</tr>

														<?php
														$sel_contrato_firmado = $mysqli->query("
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
															tipo_archivo_id = 19
															AND status = 1
															AND contrato_id = " . $contrato_id
														);
														$num_rows = mysqli_num_rows($sel_contrato_firmado);
														if($num_rows > 0)
														{
															$row = $sel_contrato_firmado->fetch_assoc();
															$ruta = str_replace("/var/www/html","",$row["ruta"]);
														?>
														<tr style="text-transform: none;">
															<td><b>Visualizar el contrato firmado:</b></td>
															<td>
																<a
																onclick="sec_con_detalle_int_ver_documento_en_visor('<?php echo $ruta;?>','<?php echo trim($row["nombre"]); ?>','<?php echo trim($row["extension"]); ?>','CONTRATO FIRMADO');"
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
												<?php
												}
											}
											
											else
											{
												if ($area_id == '33' && $cancelado_id != 1) // LEGAL PRODUCCION = 33, LEGAL DESARROLLO = 33  
												{
												?>

												<label>
													Categoría:
												</label>
												<select
													class="form-control input_text select2"
													data-live-search="true" 
													name="cont_detalle_proveedor_contrato_firmado_categoria_param" 
													id="cont_detalle_proveedor_contrato_firmado_categoria_param">
													<option value="0">-- Seleccione --</option>
													<?php
														$sel_query = $mysqli->query(
														"
															SELECT 
																id, nombre
															FROM cont_categoria_servicio
															WHERE status = 1
															ORDER BY nombre ASC;
														");

														while($sel=$sel_query->fetch_assoc())
														{
															?>
															<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
															<?php
														}
													?>
												</select>

												<label>
													Tipo contrato:
												</label>
												<select
													class="form-control input_text select2"
													data-live-search="true" 
													data-col="area_id" 
													data-table="tbl_areas"
													name="cont_detalle_proveedor_contrato_firmado_tipo_contrato_param" 
													id="cont_detalle_proveedor_contrato_firmado_tipo_contrato_param" 
													title="Seleccione el departamento">
												</select>
												
												<button type="button" class="btn btn-info btn-xs btn-block" onclick="sec_contrato_detalle_solicitud_modal_categoria_contrato()">
													<i class="icon fa fa-plus"></i>
													<span id="demo-button-text">Agregar nueva categoria y/o tipo contrato</span>
												</button>

												<form id="form_contrato_proveedor_firmado" name="form_contrato_proveedor_firmado" method="POST" enctype="multipart/form-data" autocomplete="off">

													<div class="col-xs-12 col-md-6 col-lg-6 item_filter">
														<div class="form-group">
															<label for="exampleInputEmail1">Tipo Firma:</label>
															<select
																class="form-control input_text select2"
																data-live-search="true"
																name="cont_detalle_proveedor_contrato_firmado_tipo_firma_param" 
																id="cont_detalle_proveedor_contrato_firmado_tipo_firma_param" 
																>
																<option value="0">-- Seleccione --</option>
																<?php
																	$sel_query = $mysqli->query(
																		"
																			SELECT id, nombre
																			FROM cont_tipo_firma
																			WHERE status = 1
																			ORDER BY nombre ASC;
																		");
																		
																		while($sel=$sel_query->fetch_assoc())
																		{
																			?>
																			<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
																			<?php
																		}
																?>
															</select>
														</div>
													</div>

													<div class="col-xs-12 col-md-6 col-lg-6 item_filter">
														<div class="form-group">
															<label>
																Fecha Inicio
															</label>
															<div class="input-group col-xs-12">
																<input
																	type="text"
																	class="form-control sec_contrato_detalle_solicitud_datepicker"
																	id="cont_detalle_proveedor_contrato_firmado_fecha_incio_param"
																	value="<?php echo date("d-m-Y", strtotime($fecha_inicio)); ?>"
																	readonly="readonly"
																	style="height: 34px;"
																	>
																<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="cont_detalle_proveedor_contrato_firmado_fecha_incio_param"></label>
															</div>
														</div>
													</div>

													<div class="col-xs-12 col-md-6 col-lg-6 item_filter">
														<div class="form-group">
															<label>
																Fecha Suscripción:
															</label>
															<div class="input-group col-xs-12">
																<input
																	type="text"
																	class="form-control sec_contrato_detalle_solicitud_datepicker"
																	id="cont_detalle_proveedor_contrato_firmado_fecha_suscripcion_param"
																	value="<?php echo date("d-m-Y");?>"
																	readonly="readonly"
																	style="height: 34px;"
																	>
																<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="cont_detalle_proveedor_contrato_firmado_fecha_suscripcion_param"></label>
															</div>
														</div>
													</div>

													<div class="col-xs-12 col-md-6 col-lg-6 item_filter">
														<div class="form-group">
															<label>
																Periodo:
															</label>
															<div class="input-group col-xs-12">
																<select 
																	class="form-control select2" 
																	id="fecha_vencimiento_indefinida_id" 
																	name="fecha_vencimiento_indefinida_id" 
																	style="height: 34px;">
																	<?php $query = "SELECT id, nombre";
																		$query .= " FROM cont_tipo_plazo";
																		$query .= " WHERE status = 1";
																		
																		$list_query = $mysqli->query($query);
																		$list = array();
																		while ($li = $list_query->fetch_assoc()) { ?>
																			
																			<option <?php echo ($li['id']==2 )?'selected':''; ?> value="<?php echo $li['id']==1?'2':'1'; ?>"><?php echo $li['nombre']?></option>
																	
																	<?php }?>
																</select>
															</div>
														</div>
													</div>

													<div class="col-xs-12 col-md-6 col-lg-6 item_filter" id="div_fecha_de_vencimiento" style="display: none;">
														<div class="form-group">
															<label>
																Fecha Vencimiento Definida:
															</label>
															<div class="input-group col-xs-12">
																<input
																	type="text"
																	class="form-control sec_contrato_detalle_solicitud_datepicker"
																	id="cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param"
																	value="<?php echo date("d-m-Y");?>"
																	readonly="readonly"
																	style="height: 34px;"
																	>
																<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param"></label>
															</div>
														</div>
													</div>

													<div class="col-xs-12 col-md-6 col-lg-6 item_filter">
														<div class="form-group">
															<div class="control-label">Seleccione el contrato firmado:</div>
															<input type="file" class="form-control" id="archivo_contrato_proveedor" name="archivo_contrato_proveedor" required accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip">
														</div>
													</div>
													<div class="col-xs-12 col-md-6 col-lg-6 item_filter">
														<div class="form-group">
															<label>Renovación Automática:</label>
															<select class="form-control select2" id="cont_detalle_proveedor_renovacion_automatica" name="cont_detalle_proveedor_renovacion_automatica">
																<option value="0">- Seleccione -</option>
																<option value="1">SI</option>
																<option value="2">NO</option>
															</select>
														</div>
													</div>

													<div class="col-xs-12 col-md-12 col-lg-12 item_filter" style="margin-bottom: 30px">
														<div style="margin-right: 10px; margin-left: 5px">
															<div class="form-group">
																<div class="control-label"><b>Correos Adjuntos: (Opcional)</b></div>
																<textarea name="con_firmado_correos_adjuntos" id="con_firmado_correos_adjuntos" rows="2" style="width:100%"></textarea>
																<b>Nota: Para más de un correo se debe separar por comas (,)</b>
															</div>
														</div>
													</div>														
													<br>
													<br>
													<div style="margin-right: 10px; margin-left: 5px;">
														<button type="button" class="btn btn-success btn-xs btn-block" id="btn_guardar_contrato_firmado" onclick="sec_con_detalle_int_guardar_contrato_firmado()">
															<i class="icon fa fa-plus"></i>
															<span id="demo-button-text">Agregar contrato firmado</span>
														</button>
													</div>

													<div style="margin-right: 10px; margin-left: 5px; display: none;" id="div_documentos_mensaje">
														<br>
														<div class="form-group">
															<div class="alert alert-danger" role="alert">
																<strong id="documentos_mensaje"></strong>
															</div>
														</div>
													</div>
												</form>
												<?php
												} else {
													?>
													<table>
														<thead>Aún no se carga el contrato firmado al sistema.</thead>
													</table>
													<?php
												}
											}
										?>
										
									</div>
								</div>
							</div>
							<!-- PANEL: CONTRATO FIRMADO -->
						
						<!-- INICIO PANEL RESOLUCION CONTRATO -->
						
						<div class="panel">
							<div class="panel-heading" role="tab" id="browsers-this-resolution-heading">
								<div class="panel-title">
									<a href="#browsers-this-resolution" class="collapsed" role="button" data-toggle="collapse"
									   data-parent="#accordion" aria-expanded="true" aria-controls="browsers-this-resolution">
										Resolución de Contrato
									</a>
								</div>
							</div>
							<div id="browsers-this-resolution" class="panel-collapse collapse" role="tabpanel" aria-labelledby="browsers-this-resolution-heading">
								<div class="panel-body">
									<?php

									$edicion_btn_contrato_firmado = false;

									$sel_query_info_resolucion_contrato = $mysqli->query("
									SELECT
										r.id,
										c.tipo_contrato_id,
										r.motivo,
										r.fecha_solicitud,
										DATE_FORMAT(r.fecha_resolucion,'%d-%m-%Y') AS fecha_resolucion,
										CONCAT(IFNULL(tpa.nombre, ''),' ',IFNULL(tpa.apellido_paterno, ''),	' ',	IFNULL(tpa.apellido_materno, '')) AS usuario_solicitud,
										r.anexo_archivo_id,
										r.archivo_id,
										CONCAT(IFNULL(tpa2.nombre, ''),' ',IFNULL(tpa2.apellido_paterno, ''),	' ',	IFNULL(tpa2.apellido_materno, '')) AS usuario_aprobado,
										DATE_FORMAT(r.fecha_resolucion_contrato_aprobado,'%d-%m-%Y %H:%i:%s') AS fecha_resolucion_contrato_aprobado,
										r.status,
										DATE_FORMAT(r.created_at,'%d-%m-%Y %H:%i:%s') AS created_at,
										r.cancelado_id,
										CONCAT(IFNULL(tpa3.nombre, ''),' ',IFNULL(tpa3.apellido_paterno, ''),	' ',	IFNULL(tpa3.apellido_materno, '')) AS cancelado_por,
										CONCAT(IFNULL(pab.nombre, ''),' ',IFNULL(pab.apellido_paterno, ''),	' ',	IFNULL(pab.apellido_materno, '')) AS abogado,
										DATE_FORMAT(r.cancelado_el,'%d-%m-%Y %H:%i:%s') AS cancelado_el,
										r.estado_solicitud_id,
										r.cancelado_motivo,
										r.estado_solicitud_legal,
										(CASE
											WHEN r.estado_solicitud_id = 1 THEN 'En Proceso'
											WHEN r.estado_solicitud_id = 2 THEN 'Procesado'
											ELSE ''
										END) as estado_solicitud,
										r.estado_aprobacion_gerencia,
										(CASE
											WHEN r.estado_aprobacion_gerencia = 0 THEN 'Pendiente'
											WHEN r.estado_aprobacion_gerencia = 1 THEN 'Aprobado'
											WHEN r.estado_aprobacion_gerencia = 2 THEN 'Rechazado'
											ELSE ''
										END) as estado_aprobacion, 
										CONCAT(IFNULL(tpa4.nombre, ''), ' ', IFNULL(tpa4.apellido_paterno, ''), ' ', IFNULL(tpa4.apellido_materno, '')) AS aprobado_por,
										DATE_FORMAT(r.fecha_aprobacion_gerencia,'%d-%m-%Y %H:%i:%s') AS fecha_aprobacion_gerencia
									FROM 
										cont_resolucion_contrato AS r
										INNER JOIN cont_contrato c ON c.contrato_id = r.contrato_id
										INNER JOIN tbl_usuarios tu ON r.user_created_id = tu.id
										INNER JOIN tbl_personal_apt tpa ON tu.personal_id = tpa.id
										LEFT JOIN tbl_usuarios tu2 ON r.usuario_resolucion_contrato_aprobado_id = tu2.id
										LEFT JOIN tbl_personal_apt tpa2 ON tu2.personal_id = tpa2.id
										LEFT JOIN tbl_usuarios tu3 ON r.cancelado_por_id = tu3.id
										LEFT JOIN tbl_personal_apt tpa3 ON tu3.personal_id = tpa3.id

										LEFT JOIN tbl_usuarios tu4 ON r.aprobado_por = tu4.id
										LEFT JOIN tbl_personal_apt tpa4 ON tu4.personal_id = tpa4.id

										LEFT JOIN tbl_usuarios uab ON r.abogado_id = uab.id
										LEFT JOIN tbl_personal_apt pab ON uab.personal_id = pab.id
									WHERE r.contrato_id = ".$contrato_id);
									$cantReg = mysqli_num_rows($sel_query_info_resolucion_contrato);
									if($cantReg > 0)
									{
										
										
										while($sel=$sel_query_info_resolucion_contrato->fetch_assoc())
										{
											$resolucion_id = $sel["id"];
											$motivo = $sel["motivo"];
											$fecha_solicitud = $sel["fecha_solicitud"];
											$fecha_resolucion = $sel["fecha_resolucion"];
											$usuario_solicitud = $sel["usuario_solicitud"];
											$anexo_archivo_id = $sel["anexo_archivo_id"];
											$usuario_aprobado = $sel["usuario_aprobado"];
											$estado_solicitud_id = $sel["estado_solicitud_id"];
											$estado_solicitud = $sel["estado_solicitud"];
											$archivo_id = $sel["archivo_id"];
											$fecha_resolucion_contrato_aprobado = $sel["fecha_resolucion_contrato_aprobado"];
											$status = $sel["status"];
											$created_at = $sel["created_at"];
											$cancelado_id = $sel["cancelado_id"];
											$cancelado_por = $sel['cancelado_por'];
											$cancelado_el = $sel['cancelado_el'];
											$cancelado_motivo = $sel['cancelado_motivo'];
											$estado_solicitud_legal = $sel['estado_solicitud_legal'];

											$fecha_resolucion = $fecha_resolucion != "" ? date("d-m-Y",strtotime($fecha_resolucion)):"";

											$estado_aprobacion_gerencia = $sel['estado_aprobacion_gerencia'];
											$estado_aprobacion = $sel['estado_aprobacion'];
											$aprobado_por = $sel['aprobado_por'];
											$fecha_aprobacion_gerencia = $sel['fecha_aprobacion_gerencia'];
											$abogado = $sel['abogado'];

										?>
										<?php if ((array_key_exists($menu_consultar,$usuario_permisos) && in_array("reenviar_resolucion_firmada", $usuario_permisos[$menu_consultar]) && ($estado_solicitud_id == 2 )) ) { ?>
										<button type="button" onclick="sec_contrato_detalle_reenviar_resolucion(<?=$sel['id']?>,'7')" style="float: right" class="btn btn-xs btn-info"><span class="fa fa-envelope-o"></span> Reenviar por email</button>
										<br>
										<?php } ?>
										<br>
										<table class="table table-responsive table-hover no-mb" style="font-size: 10px;">
											<tbody>
											<tr style="text-transform: none;">
													<td><b>Usuario quien registro la solicitud:</b></td>
													<td>
														<?php echo $usuario_solicitud; ?>
													</td>
												</tr>
												<tr style="text-transform: none;">
													<td><b>Motivo:</b></td>
													<td>
														<?php echo $motivo; ?>
													</td>
												</tr>
												<tr style="text-transform: none;">
													<td><b>Fecha de solicitud:</b></td>
													<td>
														<?php echo $created_at; ?>
													</td>
												</tr>
												<tr style="text-transform: none;">
													<td><b>Abogado:</b></td>
													<td>
														<?php echo $abogado; ?>
													</td>
													<?php if ($btn_editar_solicitud || ($archivo_id == 0  && $cancelado_id != 1 && $estado_aprobacion_gerencia == 1 &&  $area_id == 33 && $cargo_id != 25) ) { ?>
													<td>
														<a 	class="btn btn-success btn-xs" 
															id="btn_editar_resolucion_abogado_<?=$resolucion_id?>"
															onclick="sec_contrato_detalle_solicitud_editar_solicitud('Resolución de Contrato','cont_resolucion_contrato','abogado_id','Abogado','select_option','<?php echo $abogado; ?>','obtener_abogados','<?php echo $resolucion_id; ?>');">
															<span class="fa fa-edit"></span> Editar
														</a>
													</td>
													<?php } ?>
												</tr>
												<?php 
												if((int) $estado_aprobacion_gerencia == 1 || (int) $estado_aprobacion_gerencia == 2){ /// aprobado o rechazado
												?>
													<tr style="text-transform: none;">
														<td><b> Estado Aprobación:</b></td>
														<td>
															<?=$estado_aprobacion_gerencia == 1 ? '<span class="badge bg-info text-white">'.$estado_aprobacion.'</span>':'<span class="badge bg-danger text-white">'.$estado_aprobacion.'</span>'; ?>
														</td>
													</tr>
													<tr style="text-transform: none;">
														<td><b> <?=$estado_aprobacion_gerencia == 1 ? 'Aprobado por':'Rechazado por' ?>:</b></td>
														<td>
															<?php echo $aprobado_por; ?>
														</td>
													</tr>
													<tr style="text-transform: none;">
														<td><b> Fecha de <?=$estado_aprobacion_gerencia == 1 ? 'aprobación':'rechazo' ?>:</b></td>
														<td>
															<?php echo $fecha_aprobacion_gerencia; ?>
														</td>
													</tr>
												<?php
												}
												?>
												
												<?php
												if ((int) $estado_aprobacion_gerencia == 1){
												?>
												
												<tr style="text-transform: none;">
													<td><b>Estado:</b></td>
													<td>
													<?php
													if ($cancelado_id == 1) {
														echo '<span class="badge bg-danger text-white">Cancelada</span>';
													} else {
														echo '<span class="badge bg-info text-white">'.$estado_solicitud.'</span>';
													}
													?>
													</td>
												</tr>
												<?php
												}
												?>

												<?php if ($cancelado_id == 1) { ?>

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

												$sel_resolucion_archivos = $mysqli->query("
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
													DATE_FORMAT(created_at,'%d-%m-%Y %H:%i:%s') AS created_at
												FROM
													cont_archivos
												WHERE status = 1
													AND archivo_id = ".$anexo_archivo_id
												);
												$num_rows = mysqli_num_rows($sel_resolucion_archivos);
												if($num_rows > 0)
												{
													$row = $sel_resolucion_archivos->fetch_assoc();
													$ruta = str_replace("/var/www/html","",$row["ruta"]);

												?>
												<tr style="text-transform: none;">
												<td><b>Visualizar el anexo :</b></td>
												<td>
													<a
													onclick="sec_contrato_detalle_solicitud_ver_documento_en_visor('<?php echo $ruta;?>','<?php echo trim($row["nombre"]); ?>','<?php echo trim($row["extension"]); ?>','ANEXO DE RESOLUCION CONTRATO');"
													class="btn btn-success btn-xs"
													data-toggle="tooltip"
													data-placement="top">
													<span class="fa fa-eye"></span> Ver Anexo
													</a>
												</td>
												</tr>
												<?php
												}
												?>

												<?php 
												if ($archivo_id > 0) {
													?>
													<tr style="text-transform: none;">
														<td><b>Usuario que cargo la resolución de contrato firmado:</b></td>
														<td>
															<?php echo $usuario_aprobado; ?>
														</td>
													</tr>

													<tr style="text-transform: none;">
														<td><b>Fecha de resolución de contrato:</b></td>
														<td>
															<?php echo $fecha_resolucion; ?>
														</td>
													</tr>

													<?php
													$sel_resolucion_archivos = $mysqli->query("
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
														DATE_FORMAT(created_at,'%d-%m-%Y %H:%i:%s') AS created_at
													FROM
														cont_archivos
													WHERE status = 1
														AND archivo_id = ".$archivo_id
													);
													$num_rows = mysqli_num_rows($sel_resolucion_archivos);
													if($num_rows > 0)
													{
														$row = $sel_resolucion_archivos->fetch_assoc();
														$ruta = str_replace("/var/www/html","",$row["ruta"]);

													?>
													<tr style="text-transform: none;">
													<td><b>Visualizar la Resolución de Contrato Firmado:</b></td>
													<td>
														<a
														onclick="sec_contrato_detalle_solicitud_ver_documento_en_visor('<?php echo $ruta;?>','<?php echo trim($row["nombre"]); ?>','<?php echo trim($row["extension"]); ?>','RESOLUCION CONTRATO');"
														class="btn btn-success btn-xs"
														data-toggle="tooltip"
														data-placement="top">
														<span class="fa fa-eye"></span> Ver Anexo
														</a>
													</td>
													</tr>
													<tr style="text-transform: none;">
														<td><b>Fecha de carga de la resolución de contrato firmado:</b></td>
														<td>
															<?php echo $row["created_at"]; ?>
														</td>
													</tr>
													<?php
													}
												}
												?>
												
											</tbody>
										</table>

											<?php
												if( (int) $estado_aprobacion_gerencia == 0)
												{
													$boton_aprobacion_gerencia = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' AND sub_sec_id = '$sub_sec_id' LIMIT 1")->fetch_assoc();
													$id_boton_aprobacion_gerencia = $boton_aprobacion_gerencia["id"];

													if(array_key_exists($id_boton_aprobacion_gerencia,$usuario_permisos) && in_array("validaBotonSolicitud", $usuario_permisos[$id_boton_aprobacion_gerencia]))
													{
														?>

														<hr>

												
														<p><b> Aprobación de Gerencia:</b></p>

														<div>
															<div style="margin-right: 0px; margin-left: 0px;">

																<form id="form_aprobar_adenda_por_gerencia" name="form_aprobar_adenda_por_gerencia" method="POST" enctype="multipart/form-data" autocomplete="off">
																	
																	<div class="col-xs-12 col-md-6 col-lg-6" style="padding-left: 0px;">
																		<button type="button" class="btn btn-success btn-xs btn-block col-md-6" style="height: 30px;" onclick="sec_contrato_detalle_solicitud_aprobar_resolucion(1, <?=$sel['id']?>);">
																			<span id="demo-button-text">
																				<i class="glyphicon glyphicon-saved"></i>
																				Aceptar solicitud
																			</span>
																		</button>
																	</div>

																	<div class="col-xs-12 col-md-6 col-lg-6" style="padding-right: 0px;">
																		<button type="button" class="btn btn-danger btn-xs btn-block" style="height: 30px;" onclick="sec_contrato_detalle_solicitud_aprobar_resolucion(2, <?=$sel['id']?>)">
																			<span id="demo-button-text">
																				<i class="glyphicon glyphicon-remove-sign"></i>
																				Rechazar solicitud
																			</span>
																		</button>
																	</div>

																</form>
																<br/>
															</div>

															
														</div>

														
																				
														<?php
													}
												}
											?>


											<?php 
											if ($area_id == '33' AND $cargo_id != 25) {
												if ($archivo_id == 0 && $cancelado_id != 1 && $estado_aprobacion_gerencia == 1) {
												?>
													<hr>
													<p><b>Resolución de Contrato - Estado Legal</b></p>
													<table class="table table-bordered table-hover">
														<tr>
															<td>
																<select id="resolucion_estado_solicitud_legal_<?=$sel['id']?>" class="form-control">
																	<?php
																	$query = "SELECT * FROM cont_estado_solicitud WHERE status = 1";
																	$list_query = $mysqli->query($query);
																	$list = [];

																	while ($li = $list_query->fetch_assoc()) 
																	{
																		$nombre_estado_solicitud = $li["id"] == 1 ? '- Seleccione -': $li["nombre"];
																	?>
																		<option <?=$estado_solicitud_legal == $li["id"] ? 'selected':''; ?>  value="<?=$li["id"] == 1 ? '':$li["id"]; ?>"><?=$nombre_estado_solicitud; ?></option>
																	<?php
																	}
																	?>
																</select>
															</td>
															<td>
																<button onclick="sec_contrato_detalle_resolucion_cambiar_estado_legal(<?=$sel['id']?>,7)" class="btn btn-success form-control" type="button"><span class="glyphicon glyphicon-floppy-disk"></span> Guardar Estado</button>
															</td>
														</tr>
													</table>							
													<br>
													<form id="form_resolucion_contrato_firmado" name="form_resolucion_contrato_firmado" method="POST" enctype="multipart/form-data" autocomplete="off">
														<input type="hidden" value="<?=$sel['id']?>" id="resolucion_contrato_id" name="resolucion_contrato_id">
														<input type="hidden" value="<?=$sel['tipo_contrato_id']?>" id="resolucion_tipo_contrato_id" name="resolucion_tipo_contrato_id">
														<table class="table table-responsive table-hover no-mb" style="font-size: 10px;">
															<tbody>
																<tr style="text-transform: none;">
																	<td><label>Fecha Resolución de Contrato</label></td>
																	<td>
																	<div class="input-group col-xs-12">
																	<input
																		type="text"
																		class="form-control text-center fecha_detalle_arrendemiento_datepicker"
																		id="cont_detalle_resolucion_contrato_fecha"
																		value="<?php echo $fecha_resolucion; ?>"
																		readonly="readonly"
																		style="height: 34px;">
																	<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="cont_detalle_proveedor_contrato_firmado_fecha_incio_param"></label>
																</div>

																	</td>
																</tr>
															</tbody>
														</table>

												
														<div style="margin-right: 10px; margin-left: 5px;">
															<div class="form-group">
																<div class="control-label">Seleccione la resolución de contrato firmada (en formato pdf):</div>
																<input type="file" id="archivo_resolucion_contrato" name="archivo_resolucion_contrato" required accept=".pdf">
															</div>
														</div>

														<div style="margin-right: 10px; margin-left: 5px;">
															<button type="button" class="btn btn-success btn-xs btn-block" id="btn_guardar_contrato_firmado" onclick="sec_contrato_detalle_guardar_resolucion_contrato()">
																<i class="icon fa fa-plus"></i>
																<span id="demo-button-text">Agregar Resolución de Contrato Firmada</span>
															</button>
														</div>
													</form>
												<?php
												}
											}
										}
									}
									else
									{
									?>
									No hay resolución de contrato
									<?php
									}
									?>
								</div>
							</div>
						</div>
					
						<!-- FIN PANEL RESOLUCION CONTRATO -->


							<!-- INICIO PANEL ESTADO DE SOLICITUD -->
						<?php if ( (($area_id == '33') && ($etapa_id == 1)) || ($area_id == 6 && $cargo_id == 9) ) { ?>
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
									while($sel=$sel_query->fetch_assoc()){
										$estado_solicitud = $sel["estado_solicitud"];
										$motivo_estado_na = $sel["motivo_estado_na"];

										if(empty($estado_solicitud)) {
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
													while ($li = $list_query->fetch_assoc()) 
													{
														$nombre_estado_solicitud = $li["id"] == 1 ? 'Seleccione': $li["nombre"];
													?>
														<option <?=$estado_solicitud == $li["id"] ? 'selected':''; ?>  value="<?=$li["id"] == 1 ? '':$li["id"]; ?>"><?=$nombre_estado_solicitud; ?></option>
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
												<button onclick="sec_con_detalle_int_guardar_estado_solicitud()" class="btn btn-success form-control" type="button"><span class="glyphicon glyphicon-floppy-disk"></span> Guardar</button>
											</td>
											<?php if ($area_id == 6 && $cargo_id == 9) { ?>
											<td>
												<button onclick="sec_contrato_detalle_solicitud_corregir_dias_habiles()" class="btn btn-primary form-control" type="button"><span class="glyphicon glyphicon-floppy-disk"></span> Corregir Días Hábiles</button>											
											</td>
											<?php } ?>
										</tr>
										<?php
												if ($motivo_estado_na == '' or  $estado_solicitud <> '4'){

												}else{
													echo '		
													<tr>
														<td><b>Motivo</b></td> 
														<td colspan=2>
															<div class="form-group"> '.$motivo_estado_na.'</div>
														</td>
													</tr>' ;
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

						<!-- INICIO PANEL EDITAR ABOGADO -->
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
								<?php
									$sel_query = $mysqli->query("
									SELECT 
										CONCAT(IFNULL(pab.nombre, ''),' ',IFNULL(pab.apellido_paterno, ''),' ',IFNULL(pab.apellido_materno, '')) AS abogado
									FROM
										cont_contrato c
										INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id
										INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
										INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
										LEFT JOIN tbl_usuarios uab ON c.abogado_id = uab.id
										LEFT JOIN tbl_personal_apt pab ON uab.personal_id = pab.id
									WHERE 
										c.contrato_id IN (" . $contrato_id . ")");
									while($sel = $sel_query->fetch_assoc()){
										$abogado = $sel["abogado"];
									?>
									<table class="table table-bordered table-hover">
										<tr>
											<td><b>Abogado</b></td>
											<td><?php echo $sel["abogado"];?></td>
											<?php if ($btn_editar_solicitud || ( $area_id == 33 && $cargo_id != 25 && $etapa_id == 1 )) { ?>
											<td style="width: 75px;">
												<a class="btn btn-success btn-xs" 
												id="btn_editar_abogado"
												onclick="sec_contrato_detalle_solicitud_editar_solicitud('Datos Generales','cont_contrato','abogado_id','Abogado','select_option','<?php echo $sel["abogado"]; ?>','obtener_abogados');">
													<span class="fa fa-edit"></span> Editar
												</a>
											</td>
											<?php } ?>
										</tr>
									</table>
									<?php } ?>
								</div>
							</div>
						</div>
						<!-- FIN PANEL EDITAR ABOGADO -->

						</div>
						<!-- /PANEL-GROUP: Browsers -->

					</div>
					<!-- /Panel Body -->

				</div>
				<!-- /PANEL: Horizontal Form -->

			</div>

			<?php 
				if($check_gerencia_interno == 1 && $etapa_id == 1 && $cancelado_id != 1)
				{
					if(is_null($fecha_atencion_gerencia_interno))
					{
						$boton_aprobacion_gerencia = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' AND sub_sec_id = '$sub_sec_id' LIMIT 1")->fetch_assoc();
						$id_boton_aprobacion_gerencia = $boton_aprobacion_gerencia["id"];

						if(array_key_exists($id_boton_aprobacion_gerencia,$usuario_permisos) && in_array("validaBotonSolicitud", $usuario_permisos[$id_boton_aprobacion_gerencia]))
						{
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

																<div id="div_observaciones_gerencia" class="timeline" style="font-size: 11px;"></div>
																<b>Observación:</b>
																<textarea rows="3" id="contrato_observaciones_int_gerencia" placeholder="Ingrese sus observaciones" style="width: 100%"></textarea>																
																<button class="btn btn-warning btn-xs btn-block" type="submit" onclick="sec_con_detalle_int_guardar_observaciones_gerencia();">
																	<i class="fa fa-plus"></i> Enviar Observación
																</button>
															</div>

															<br>	


															<form id="form_contrato_proveedor_aprobar_gerencia" name="form_contrato_proveedor_firmado" method="POST" enctype="multipart/form-data" autocomplete="off">
																
																<div class="col-xs-12 col-md-6 col-lg-6 item_filter">
																	<button type="button" class="btn btn-success btn-xs btn-block col-md-6 sec_con_detalle_int_btn_guardar_aprobar_gerencia" value="1" style="height: 30px;">
																		<span id="demo-button-text">
																			<i class="glyphicon glyphicon-saved"></i>
																			Aceptar solicitud
																		</span>
																	</button>
																</div>

																<div class="col-xs-12 col-md-6 col-lg-6 item_filter">
																	<button type="button" class="btn btn-danger btn-xs btn-block sec_con_detalle_int_btn_guardar_aprobar_gerencia" value="0" style="height: 30px;">
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
				<h4 class="modal-title" id="myModalLabel">Editar Solicitud de Contrato Interno</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form id="form_editar_solicitud" autocomplete="off" >
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
												type="text"
												class="form-control sec_contrato_detalle_solicitud_datepicker"
												id="editar_solicitud_valor_date"
												value="<?php echo date("d-m-Y", strtotime("+1 days"));?>"
												readonly="readonly"
												style="height: 34px;"
												>
											</div>
											<div id="div_editar_solicitud_valor_decimal">
												<input type="text" id="editar_solicitud_valor_decimal" class="filtro txt_filter_style" style="width: 100%;" placeholder="0.00">
											</div>
											<div id="div_editar_solicitud_valor_select_option">
												<select class="form-control select2" id="editar_solicitud_valor_select_option" name="editar_solicitud_valor_select_option">
												</select>
											</div>
											<div id="div_editar_solicitud_departamento" class="col-xs-12 col-md-12 col-lg-12" >
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

										<input type="file" id="fileArchivo_requisitos_arrendamiento" name ="fileArchivo_requisitos_arrendamiento" multiple="multiple" accept='.jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip'>

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
								oninput="this.value=this.value.replace(/[^0-9]/g,'');"
								>
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
				<button type="button" class="btn btn-danger" 	class="btn btn-success" data-dismiss="modal"><i class="icon fa fa-close"></i>Cancelar</button>
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

										<input type="file" id="fileArchivo_requisitos_arrendamiento" name ="fileArchivo_requisitos_arrendamiento" multiple="multiple" accept='.jpeg, .jpg, .png, .pdf'>

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
									while($sel=$sel_query->fetch_assoc()){?>
									<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
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
									while($sel=$sel_query->fetch_assoc()){?>
									<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
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
												while($sel = $query->fetch_assoc()){
												?>
												<option value="<?=$sel['id']?>"><?=$sel['nombre']?></option>
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
				<h4 class="modal-title">Cancelar Solicitud de Contrato Interno</h4>
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
					<span>Cancelar Solicitud de Contrato Interno</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL CANCELAR SOLICITUD -->
