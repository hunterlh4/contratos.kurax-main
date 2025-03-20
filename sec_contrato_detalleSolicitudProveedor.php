<?php
$this_menu = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' LIMIT 1")->fetch_assoc();
$menu_id = $this_menu["id"];

$menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' AND sub_sec_id = '$sub_sec_id' LIMIT 1")->fetch_assoc();
$menu_consultar = isset($menu_id_consultar["id"]) ? $menu_id_consultar["id"]:0;

if(!array_key_exists($menu_id,$usuario_permisos)){
	echo "No tienes permisos para este recurso.";
	die();
}

$continuar = true;

if(!array_key_exists($menu_id,$usuario_permisos)){
	$continuar = false;
} elseif(!in_array("view", $usuario_permisos[$menu_id])){
	$continuar = false;
}
$permiso_editar_solicitud_contrato = false;
if ((array_key_exists($menu_consultar,$usuario_permisos) && in_array("Edicion_De_Otro_Usuario", $usuario_permisos[$menu_consultar]))) { 

	$permiso_editar_solicitud_contrato = true;
}

$list_emp_cont = $mysqli->query("SELECT tpg.valor FROM tbl_parametros_generales tpg WHERE tpg.codigo = 'empresa_contacto_de_contratos' AND tpg.estado = 1 LIMIT 1");
$row_emp_cont = $list_emp_cont->fetch_assoc();
$valor_empresa_contacto = isset($row_emp_cont['valor']) && $row_emp_cont['valor'] == 'Kurax' ? '(Kurax)':'(AT)';

/*
echo "sec_id: ".$sec_id;
echo "login_id: ".$login["id"];
echo "menu_id: ".$menu_id;

if(array_key_exists($menu_id,$usuario_permisos)){
	echo "Si tienes permisos para ver este recurso 1";
}

if(in_array("view", $usuario_permisos[$menu_id])){
	echo "Si tienes permisos para ver este recurso 2";
}

if(in_array("request", $usuario_permisos[$menu_id])){
	echo "Si tienes permisos para ver este recurso 3";
}
*/

if ($continuar) {

$contrato_id = $_GET["id"];
$adenta_id_temporal = '';
$resolucion_id_temporal = '';

if ( isset($_GET["adenda_id"]) ) {
	$adenta_id_temporal = $_GET["adenda_id"];
}

if ( isset($_GET["resolucion_id"]) ) {
	$resolucion_id_temporal = $_GET["resolucion_id"];
}

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
$cancelado_id = $row["cancelado_id"];

if (($usuario_id == $user_created_id && $etapa_id == 1) || ( $area_id == 33 && $cargo_id != 25 && $etapa_id == 5 ) || $permiso_editar_solicitud_contrato) {
	$btn_editar_solicitud = true;
} else {
	$btn_editar_solicitud = false;
}

include '/var/www/html/sys/function_replace_invalid_caracters_contratos.php';
?>

<style type="text/css">


	fieldset.dhhBorder {
		border: 1px solid #ddd;
		padding: 0 15px 5px 15px;
		margin: 0 0 15px 0;
		box-shadow: 0px 0px 0px 0px #000;
		border-radius: 5px;
	}

	legend.dhhBorder {
		font-size: 14px;
		text-align: left;
		width: auto;
		padding: 0 10px;
		border-bottom: none;
		margin-bottom: 10px;
		text-transform: capitalize;
	}

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

/* INICIO RESPLANDOR */
.resplandor {
	box-shadow: 0px 0px 20px;
	animation: infinite resplandor_animacion 2s;
}

@keyframes resplandor_animacion {
	0%, 100% {
		box-shadow: 0px 0px 20px;
	}
	50% {
		box-shadow: 0px 0px 0px;
	}
}
/* FIN RESPLANDOR */

</style>

<input type="hidden" id="contrato_id_temporal" value="<?php echo $contrato_id; ?>">
<input type="hidden" id="tipo_contrato_id_temporal" value="2">
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
						<i class="icon icon-inline glyphicon glyphicon-briefcase"></i> <?php echo $etapa_id == 1 ? 'Solicitud de ' : '';?> Contrato de Proveedor - Código: <?php echo $sigla_correlativo; echo $codigo_correlativo; ?>
					</h1>
				</div>
			</div>

			<div class="col-xs-12 col-md-12 col-lg-7">
				<div class="panel" id="divDetalleSolicitud">
					<div class="panel-heading">
						<div class="panel-title" style="width: 300px; display: inline-block;">DETALLE DE LA SOLICITUD </div>
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
										c.check_gerencia_proveedor,
										c.fecha_atencion_gerencia_proveedor,
										c.aprobacion_gerencia_proveedor
									FROM cont_contrato c
									WHERE c.contrato_id = '". $contrato_id ."'
									");
									while($sel=$sel_query->fetch_assoc()) {
										$check_gerencia_proveedor = $sel["check_gerencia_proveedor"];
										$fecha_atencion_gerencia_proveedor = $sel["fecha_atencion_gerencia_proveedor"];
										$aprobacion_gerencia_proveedor = $sel["aprobacion_gerencia_proveedor"];
									}

									if($check_gerencia_proveedor == 1 && $etapa_id == 1) {
										if(is_null($fecha_atencion_gerencia_proveedor)) {
									?>
										<a class="btn btn-warning btn-xs" onclick="enviar_por_email_solicitud_al_lourdes_britto_gerencia(<?php echo $contrato_id; ?>);">
											<span class="fa fa-envelope-o"></span> Notificar a Director(a)
										</a>
									<?php
										}
									}
									
									if($usuario_id == 3315 || $usuario_id == 3562 || $usuario_id == 3028) { ?>
										<a class="btn btn-danger btn-xs" onclick="enviar_por_email_solicitud_al_lourdes_britto(<?php echo $contrato_id; ?>);">
											<span class="fa fa-envelope-o"></span> Reenviar por email a Lourdes Britto
										</a>
									<?php }

									if($area_id == 6) { ?>
										<a class="btn btn-info btn-xs" onclick="sec_contrato_detalle_solicitud_enviar_por_email_solicitud_de_proveedor(<?php echo $contrato_id; ?>);">
											<span class="fa fa-envelope-o"></span> Reenviar por email a Todos
										</a>
									<?php } 

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

							<!-- INICIO PANEL DETALLE CONTRATO -->
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
														r.nombre AS empresa_suscribe,
														c.observaciones,
														c.persona_contacto_proveedor,
														c.user_created_id,
														CONCAT(IFNULL(p.nombre, ''),' ',IFNULL(p.apellido_paterno, ''),' ',IFNULL(p.apellido_materno, '')) AS user_created,
														c.created_at,
														c.gerente_area_id,
														c.gerente_area_nombre,
														c.gerente_area_email,
														CONCAT(IFNULL(peg.nombre, ''),' ',IFNULL(peg.apellido_paterno, ''),' ',IFNULL(peg.apellido_materno, '')) AS nombre_del_gerente_area,
														peg.correo AS email_del_gerente_area,
														ta.nombre AS area_responsable,
														
														cpc.nombre AS cargo_persona_contacto,
														cr.nombre AS cargo_responsable,
														ca.nombre AS cargo_aprobante,

														CONCAT(IFNULL(pab.nombre, ''),' ',IFNULL(pab.apellido_paterno, ''),' ',IFNULL(pab.apellido_materno, '')) AS abogado
														
													FROM
														cont_contrato c
														INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id
														INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
														INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
														LEFT JOIN tbl_usuarios ug ON c.gerente_area_id = ug.id
														LEFT JOIN tbl_personal_apt peg ON ug.personal_id = peg.id
														LEFT JOIN tbl_areas ta ON ta.id = c.area_responsable_id

														LEFT JOIN tbl_usuarios uab ON c.abogado_id = uab.id
														LEFT JOIN tbl_personal_apt pab ON uab.personal_id = pab.id
														
														LEFT JOIN tbl_cargos AS cpc ON cpc.id = c.cargo_id_persona_contacto
														LEFT JOIN tbl_cargos AS cr ON cr.id = c.cargo_id_responsable
														LEFT JOIN tbl_cargos AS ca ON ca.id = c.cargo_id_aprobante
													WHERE 
														c.contrato_id IN (" . $contrato_id . ")");
													while($sel = $sel_query->fetch_assoc()){
														$observaciones = $sel["observaciones"];
														$gerente_area_id = trim($sel["gerente_area_id"]);
														$area_responsable = trim($sel["area_responsable"]);
														$cargo_aprobante = trim($sel["cargo_aprobante"]);
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
															<td style="width: 50%;"><b>Empresa Contratante</b></td>
															<td><?php echo $sel["empresa_suscribe"];?></td>
															<?php if ($btn_editar_solicitud) { ?>
															<td style="width: 75px;">
																<a class="btn btn-success btn-xs" 
																onclick="sec_contrato_detalle_solicitud_editar_solicitud('Datos Generales','cont_contrato','empresa_suscribe_id','Empresa Contratante','select_option','<?php echo $sel["empresa_suscribe"]; ?>','obtener_empresa_at');">
																	<span class="fa fa-edit"></span> Editar
																</a>
															</td>
															<?php } ?>
														</tr>
														<tr>
															<td><b>Persona Contacto <?=$valor_empresa_contacto?></b></td>
															<td><?php echo $sel["persona_contacto_proveedor"];?></td>
															<?php if ($btn_editar_solicitud) { ?>
															<td>
																<a class="btn btn-success btn-xs" 
																onclick="sec_contrato_detalle_solicitud_editar_solicitud('Datos Generales','cont_contrato','persona_contacto_proveedor','Persona Contacto <?=$valor_empresa_contacto?>','varchar','<?php echo $sel["persona_contacto_proveedor"]; ?>','');">
																	<span class="fa fa-edit"></span> Editar
																</a>
															</td>
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
																onclick="sec_contrato_detalle_solicitud_editar_solicitud('Datos Generales','cont_contrato','cargo_id_responsable','Cargo Responsable de Área<','select_option','<?php echo $sel["cargo_responsable"]; ?>','obtener_cargos');">
																	<span class="fa fa-edit"></span> Editar
																</a>
															</td>
															<?php } ?>
														</tr>



														

														<tr>
															<td><b>Registrado por</b></td>
															<td><?php echo $sel["user_created"];?></td>
															<?php if ($btn_editar_solicitud) { ?>
															<td></td>
															<?php } ?>
														</tr>
														<tr>
															<td><b>Área responsable</b></td>
															<td><?php echo $area_responsable;?></td>
															<?php if ($btn_editar_solicitud) { ?>
															<td>
																<a class="btn btn-success btn-xs" 
																onclick="sec_contrato_detalle_solicitud_editar_solicitud('Datos Generales','cont_contrato','area_responsable_id','Área responsable','select_option','<?php echo $area_responsable; ?>','obtener_areas');">
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
											c.check_gerencia_proveedor,
											c.fecha_atencion_gerencia_proveedor,
											c.aprobacion_gerencia_proveedor,
											CONCAT(IFNULL(pud.nombre, ''),' ',IFNULL(pud.apellido_paterno, ''),' ',IFNULL(pud.apellido_materno, '')) AS nombre_del_director_a_aprobar,
											CONCAT(IFNULL(puap.nombre, ''),' ',IFNULL(puap.apellido_paterno, ''),' ',IFNULL(puap.apellido_materno, '')) AS aprobado_por
										FROM cont_contrato c
										LEFT JOIN tbl_usuarios ud ON c.director_aprobacion_id = ud.id
										LEFT JOIN tbl_personal_apt pud ON ud.personal_id = pud.id
										LEFT JOIN tbl_usuarios uap ON c.aprobado_por = uap.id
										LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id
										WHERE c.contrato_id = '". $contrato_id ."'
										");

								$cantReg = mysqli_num_rows($sel_query);

								while($sel=$sel_query->fetch_assoc())
								{
									$check_gerencia_proveedor = $sel["check_gerencia_proveedor"];
									$fecha_atencion_gerencia_proveedor = $sel["fecha_atencion_gerencia_proveedor"];
									$aprobacion_gerencia_proveedor = $sel["aprobacion_gerencia_proveedor"];
									$nombre_del_director_a_aprobar = $sel["nombre_del_director_a_aprobar"];
									$aprobado_por = $sel["aprobado_por"];
								}
								if($check_gerencia_proveedor == 1)
								{
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
															if(!is_null($fecha_atencion_gerencia_proveedor))
															{
																if($aprobacion_gerencia_proveedor == 1)
																{
																	?>
																		<tr>
																			<td style="width: 50%;"><b>Aprobado por</b></td>
																			<td><?php echo $aprobado_por; ?></td>
																		</tr>
																		<tr>
																			<td><b>Fecha Aprobación</b></td>
																			<td><?php echo $fecha_atencion_gerencia_proveedor; ?></td>
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
																		<td style="width: 50%;"><b>Esperando la aprobación de:</b></td>
																		<td><?php echo $nombre_del_director_a_aprobar; ?></td>
																	</tr>
																	<tr>
																		<td><b>Situación</b></td>
																		<td>Pendiente de Aprobación</td>
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
								</div>
								<?php  
								}
								?>



								<div class="panel panel-default">
									<div class="panel-heading" role="tab" id="headindDatosProveedor">
									<h4 class="panel-title">
										<a role="button" data-toggle="collapse" data-parent="#accordionContrato" href="#collapeDatosProveedor" aria-expanded="true" aria-controls="collapeDatosProveedor">
										DATOS DEL PROVEEDOR
										</a>
									</h4>
									</div>
									<div id="collapeDatosProveedor" class="panel-collapse-all panel-collapse collapse in" role="tabpanel" aria-labelledby="headindDatosProveedor">
										<div class="panel-body">
											<div class="w-100">
												<div id="divTablaPropietarios" class="form-group">
													<?php
													$sel_query = $mysqli->query("
													SELECT 
														c.ruc,
														c.razon_social,
														c.nombre_comercial,
														c.vigencia,
														c.dni_representante,
														c.nombre_representante,
														c.persona_contacto_proveedor,
														c.detalle_servicio,
														c.alcance_servicio,
														c.tipo_terminacion_anticipada_id,
														t.nombre AS tipo_terminacion_anticipada,
														c.terminacion_anticipada,
														c.observaciones,
														c.plazo_id,
														tp.nombre AS plazo,
														p.nombre AS periodo,
														c.periodo_numero,
														c.fecha_inicio
													FROM 
														cont_contrato c
														LEFT JOIN cont_tipo_plazo tp ON c.plazo_id = tp.id
														LEFT JOIN cont_periodo p ON c.periodo = p.id
														LEFT JOIN cont_tipo_terminacion_anticipada t ON c.tipo_terminacion_anticipada_id = t.id
													WHERE 
														c.contrato_id = $contrato_id
													");
													while($sel=$sel_query->fetch_assoc()){
														$dni_representante = $sel["dni_representante"];
														$nombre_representante = $sel["nombre_representante"];
														$detalle_servicio = $sel["detalle_servicio"];
														$plazo_id = $sel["plazo_id"];
														$plazo = $sel["plazo"];
														$periodo_numero = $sel["periodo_numero"];
														$periodo_anio_mes = $sel["periodo"];
														$fecha_inicio_contrato = $sel["fecha_inicio"];
														$alcance_servicio = $sel["alcance_servicio"];
														$tipo_terminacion_anticipada_id = $sel["tipo_terminacion_anticipada_id"];
														$tipo_terminacion_anticipada = $sel["tipo_terminacion_anticipada"];
														$terminacion_anticipada = $sel["terminacion_anticipada"];
														$observaciones_legal = $sel["observaciones"];

														$date = date_create($sel["fecha_inicio"]);
														$fecha_inicio = date_format($date, "Y-m-d");

														?>
													<table class="table table-bordered">
													<tr>
														<td style="width: 50%;"><b>RUC</b></td>
														<td><?php echo $sel["ruc"];?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td style="width: 75px;">
															<a class="btn btn-success btn-xs" 
															onclick="sec_contrato_detalle_solicitud_editar_solicitud('Datos del Proveedor','cont_contrato','ruc','RUC del Proveedor','varchar','<?php echo $sel["ruc"]; ?>','');">
																<span class="fa fa-edit"></span> Editar
															</a>
														</td>
														<?php } ?>
													</tr>
													<tr>
														<td><b>Razón Social</b></td>
														<td><?php echo $sel["razon_social"];?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td>
															<a class="btn btn-success btn-xs" 
															onclick="sec_contrato_detalle_solicitud_editar_solicitud('Datos del Proveedor','cont_contrato','razon_social','Razón Social','varchar','<?php echo $sel["razon_social"]; ?>','');">
																<span class="fa fa-edit"></span> Editar
															</a>
														</td>
														<?php } ?>
													</tr>
													<tr>
														<td><b>Nombre Comercial</b></td>
														<td><?php echo $sel["nombre_comercial"];?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td>
															<a class="btn btn-success btn-xs" 
															onclick="sec_contrato_detalle_solicitud_editar_solicitud('Datos del Proveedor','cont_contrato','nombre_comercial','Nombre Comercial','varchar','<?php echo $sel["nombre_comercial"]; ?>','');">
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
									<div class="panel-heading" role="tab" id="headingRepresentanteLegal">
									<h4 class="panel-title">
										<a role="button" data-toggle="collapse" data-parent="#accordionContrato" href="#collapseRepresentanteLegal" aria-expanded="true" aria-controls="collapseRepresentanteLegal">
										DATOS DEL PROVEEDOR - REPRESENTANTE LEGAL
										</a>
									</h4>
									</div>
									<div id="collapseRepresentanteLegal" class="panel-collapse-all panel-collapse collapse in" role="tabpanel" aria-labelledby="headingRepresentanteLegal">
										<div class="panel-body">
											<div class="w-100 text-right">
												<?php if ($btn_editar_solicitud) { ?>
												<button type="button" class="btn btn-xs btn-primary" onclick="sec_con_det_prov_agregar_representante()">
													<i class="fa fa-plus"></i>
													Agregar Representante
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
														rl.dni_archivo_id,
														td.nombre as nombre_tipo_doc
													FROM 
														cont_representantes_legales rl
														LEFT JOIN cont_tipo_docu_identidad as td ON td.id = rl.tipo_documento_id
														LEFT JOIN tbl_bancos b on b.id = rl.id_banco
													WHERE 
														rl.contrato_id = $contrato_id"
													);
													$c = 0;
													$id_representante_legal = 0;

													$row_count = $sel_query->num_rows;

													if ($row_count == 0) {
													?>

													<table class="table table-bordered">
														<tr>
															<td style="width: 50%;"><b>DNI del representante legal</b></td>
															<td><?php echo $dni_representante;?></td>
															<?php if ($btn_editar_solicitud) { ?>
															<td>
																<a class="btn btn-success btn-xs" 
																onclick="sec_contrato_detalle_solicitud_editar_solicitud('Datos del Proveedor','cont_contrato','dni_representante','DNI del representante legal','varchar','<?php echo $dni_representante; ?>','');">
																	<span class="fa fa-edit"></span> Editar
																</a>
															</td>
															<?php } ?>
														</tr>
														<tr>
															<td><b>Nombre completo del representante legal</b></td>
															<td><?php echo $nombre_representante;?></td>
															<?php if ($btn_editar_solicitud) { ?>
															<td>
																<a class="btn btn-success btn-xs" 
																onclick="sec_contrato_detalle_solicitud_editar_solicitud('Datos del Proveedor','cont_contrato','nombre_representante','Nombre completo del representante legal','varchar','<?php echo $nombre_representante; ?>','');">
																	<span class="fa fa-edit"></span> Editar
																</a>
															</td>
															<?php } ?>
														</tr>
													</table>

													<?php
													} elseif ($row_count > 0) {
														while($sel=$sel_query->fetch_assoc()){
															$c = $c + 1; 
															$id_representante_legal = $sel["id"];
														?>
														<b>Representate Legal # <?php echo $c ?></b>
														<table class="table table-bordered">
															<tr>
																<td width="50%"><b>TIPO DOCUMENTO DEL REPRESENTANTE LEGAL</b></td>
																<td><?php echo $sel["nombre_tipo_doc"]; ?></td>
																<?php if ($btn_editar_solicitud) { ?>
																<td style="width: 75px;">
																	<a class="btn btn-success btn-xs" 
																	onclick="sec_contrato_detalle_solicitud_editar_solicitud('Datos del Proveedor - Representante Legal','cont_representantes_legales','tipo_documento_id','Tipo Documento del representante legal','select_option','<?php echo $sel["nombre_tipo_doc"]; ?>','obtener_tipo_docu_identidad','<?php echo $sel["id"]; ?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
																<?php } ?>
															</tr>
															<tr>
																<td width="50%"><b>NRO DOCUMENTO DEL REPRESENTANTE LEGAL</b></td>
																<td><?php echo $sel["dni_representante"]; ?></td>
																<?php if ($btn_editar_solicitud) { ?>
																<td style="width: 75px;">
																	<a class="btn btn-success btn-xs" 
																	onclick="sec_contrato_detalle_solicitud_editar_solicitud('Datos del Proveedor - Representante Legal','cont_representantes_legales','dni_representante','Nro Documento del representante legal','varchar','<?php echo $sel["dni_representante"]; ?>','','<?php echo $sel["id"]; ?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
																<?php } ?>
															</tr>
															<tr>
																<td><b>NOMBRE COMPLETO DEL REPRESENTANTE LEGAL</b></td>
																<td><?php echo $sel["nombre_representante"]; ?></td>
																<?php if ($btn_editar_solicitud) { ?>
																<td>
																	<a class="btn btn-success btn-xs" 
																	onclick="sec_contrato_detalle_solicitud_editar_solicitud('Datos del Proveedor - Representante Legal','cont_representantes_legales','nombre_representante','Nombre completo del representante legal','varchar','<?php echo $sel["nombre_representante"]; ?>','','<?php echo $sel["id"]; ?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
																<?php } ?>
															</tr>
															<tr>
																<td><b>NRO CUENTA DE DETRACCIÓN (BANCO DE LA NACIÓN)</b></td>
																<td><?php echo $sel["nro_cuenta_detraccion"]; ?></td>
																<?php if ($btn_editar_solicitud) { ?>
																<td>
																	<a class="btn btn-success btn-xs" 
																	onclick="sec_contrato_detalle_solicitud_editar_solicitud('Datos del Proveedor - Representante Legal','cont_representantes_legales','nro_cuenta_detraccion','Número de cuenta de detracción','varchar','<?php echo $sel["nro_cuenta_detraccion"]; ?>','','<?php echo $sel["id"]; ?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
																<?php } ?>
															</tr>
															<tr>
																<td><b>BANCO</b></td>
																<td><?php echo $sel["banco_representante"]; ?></td>
																<?php if ($btn_editar_solicitud) { ?>
																<td>
																	<a class="btn btn-success btn-xs" 
																	onclick="sec_contrato_detalle_solicitud_editar_solicitud('Datos del Proveedor - Representante Legal','cont_representantes_legales','id_banco','Banco','select_option','<?php echo $sel["banco_representante"]; ?>','obtener_banco','<?php echo $sel["id"]; ?>');">
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
																	onclick="sec_contrato_detalle_solicitud_editar_solicitud('Datos del Proveedor - Representante Legal','cont_representantes_legales','nro_cuenta','Nro de Cuenta','varchar','<?php echo $sel["nro_cuenta"]; ?>','','<?php echo $sel["id"]; ?>');">
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
																	onclick="sec_contrato_detalle_solicitud_editar_solicitud('Datos del Proveedor - Representante Legal','cont_representantes_legales','nro_cci','Nro de CCI','varchar','<?php echo $sel["nro_cci"]; ?>','','<?php echo $sel["id"]; ?>');">
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
																			$archivo .= ' onclick="sec_contrato_detalle_solicitud_ver_documento_en_visor(\''. trim($ruta) . '\', \''. trim($row["nombre"]) . '\', \'' . trim($row["extension"]) . '\', \'' . $row["nombre_tipo_archivo"] . '\');"';
																			$archivo .= ' class="btn btn-success btn-xs btn-block"';
																			$archivo .= ' data-toggle="tooltip"';
																			$archivo .= ' data-placement="top">';
																			$archivo .= ' <span class="fa fa-eye"></span> Ver Documento';
																			$archivo .= ' </a>';

																			$archivo_estado = '<a class="class_SubirFile_req_solicitud_arrendamiento btn btn-success btn-xs btn-block" id="btnSubirFile_req_solicitud_arrendamiento" data-toggle="tooltip" data-placement="top" onclick="moda_subir_archivo_req_solicitud_proveedor_representante_legal(\''.$row["nombre_tipo_archivo"].'\', \''.$row["archivo_id"].'\', \''.$row["tipo_archivo_id"].'\',' . $id_representante_legal . ');"><i class="fa fa-upload"></i> Reemplazar</a>';
																		}
																		else 
																		{
																			$archivo = '<a class="btn btn-warning btn-xs btn-block" data-toggle="tooltip" data-placement="top"><span class="fa fa-eye-slash"></span> Incompleto</a>';

																			$archivo_estado = '<a class="class_SubirFile_req_solicitud_arrendamiento btn btn-warning btn-xs btn-block" id="btnSubirFile_req_solicitud_arrendamiento" data-toggle="tooltip" data-placement="top" onclick="moda_subir_archivo_req_solicitud_proveedor_representante_legal(\''.$row["nombre_tipo_archivo"].'\', \''.$row["archivo_id"].'\', \''.$row["tipo_archivo_id"].'\',' . $id_representante_legal . ');"><i class="fa fa-upload"></i> Subir</a>';
																		}

																		
																	}
																}else{
																	$archivo = '<a style="width: 150px;" class="btn btn-warning btn-xs btn-block" data-toggle="tooltip" data-placement="top"><span class="fa fa-eye-slash"></span> Incompleto</a>';

																	$archivo_estado = '<a class="class_SubirFile_req_solicitud_arrendamiento btn btn-warning btn-xs btn-block" id="btnSubirFile_req_solicitud_arrendamiento" data-toggle="tooltip" data-placement="top" onclick="moda_subir_archivo_req_solicitud_proveedor_representante_legal(\'VIGENCIA DE PODER DEL REPRESENTANTE LEGAL DE LA EMPRESA PROVEEDORA\', \'\', \'2\',' . $id_representante_legal . ');"><i class="fa fa-upload"></i> Subir</a>';
																}
																
																
																$html .= '<tr style="text-transform: none;">';
																$html .= '<td style="text-transform: uppercase;"><b>VIGENCIA DE PODER DEL REPRESENTANTE LEGAL DE LA EMPRESA PROVEEDORA</b></td>';
																$html .= '<td>'.$archivo.'</td>';
																if(($area_id == 33 AND $cargo_id != 25) || $user_created_id == $login['id'] || $btn_editar_solicitud){
																	$html .= '<td>'.$archivo_estado.'</td>';
																}
																

																$html .= '</tr>';
																echo $html;
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
																			$archivo .= ' onclick="sec_contrato_detalle_solicitud_ver_documento_en_visor(\''. trim($ruta) . '\', \''. trim($row["nombre"]) . '\', \'' . trim($row["extension"]) . '\', \'' . $row["nombre_tipo_archivo"] . '\');"';
																			$archivo .= ' class="btn btn-success btn-xs btn-block"';
																			$archivo .= ' data-toggle="tooltip"';
																			$archivo .= ' data-placement="top">';
																			$archivo .= ' <span class="fa fa-eye"></span> Ver Documento';
																			$archivo .= ' </a>';

																			$archivo_estado = '<a class="class_SubirFile_req_solicitud_arrendamiento btn btn-success btn-xs btn-block" id="btnSubirFile_req_solicitud_arrendamiento" data-toggle="tooltip" data-placement="top" onclick="moda_subir_archivo_req_solicitud_proveedor_representante_legal(\''.$row["nombre_tipo_archivo"].'\', \''.$row["archivo_id"].'\', \''.$row["tipo_archivo_id"].'\',' . $id_representante_legal . ');"><i class="fa fa-upload"></i> Reemplazar</a>';
																		}
																		else 
																		{
																			$archivo = '<a class="btn btn-warning btn-xs btn-block" data-toggle="tooltip" data-placement="top"><span class="fa fa-eye-slash"></span> Incompleto</a>';

																			$archivo_estado = '<a class="class_SubirFile_req_solicitud_arrendamiento btn btn-warning btn-xs btn-block" id="btnSubirFile_req_solicitud_arrendamiento" data-toggle="tooltip" data-placement="top" onclick="moda_subir_archivo_req_solicitud_proveedor_representante_legal(\''.$row["nombre_tipo_archivo"].'\', \''.$row["archivo_id"].'\', \''.$row["tipo_archivo_id"].'\',' . $id_representante_legal . ');"><i class="fa fa-upload"></i> Subir</a>';
																		}

																		
																	}
																}else{
																	$archivo = '<a style="width: 150px;" class="btn btn-warning btn-xs btn-block" data-toggle="tooltip" data-placement="top"><span class="fa fa-eye-slash"></span> Incompleto</a>';

																	$archivo_estado = '<a class="class_SubirFile_req_solicitud_arrendamiento btn btn-warning btn-xs btn-block" id="btnSubirFile_req_solicitud_arrendamiento" data-toggle="tooltip" data-placement="top" onclick="moda_subir_archivo_req_solicitud_proveedor_representante_legal(\'DNI del representante legal de la empresa proveedora\', \'\', \'3\',' . $id_representante_legal . ');"><i class="fa fa-upload"></i> Subir</a>';
																}
																
																
																$html .= '<tr style="text-transform: none;">';
																$html .= '<td style="text-transform: uppercase;"><b>DNI del representante legal de la empresa proveedora</b></td>';

																$html .= '<td>'.$archivo.'</td>';
																if(($area_id == 33 AND $cargo_id != 25) || $user_created_id == $login['id'] || $btn_editar_solicitud){
																	$html .= '<td>'.$archivo_estado.'</td>';
																}

																$html .= '</tr>';
																echo $html;


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
										<a role="button" data-toggle="collapse" data-parent="#accordionContrato" href="#collapseCCObjetoDelContrato" aria-expanded="true" aria-controls="collapseCCObjetoDelContrato">
										CONDICIONES COMERCIALES - 1) OBJETO DEL CONTRATO
										</a>
									</h4>
									</div>
									<div id="collapseCCObjetoDelContrato" class="panel-collapse-all panel-collapse collapse in" role="tabpanel" aria-labelledby="headingCCObjetoDelContrato">
										<div class="panel-body">
											<div class="w-100">
												<div class="form-group">
													<table class="table table-bordered table-hover">
														<tr>
															<td style="white-space: pre-line;"><?php echo $detalle_servicio;?></td>
															<?php if ($btn_editar_solicitud) { ?>
															<td style="width: 75px;">
																<a class="btn btn-success btn-xs" 
																onclick="sec_contrato_detalle_solicitud_editar_solicitud('Condiciones Comerciales','cont_contrato','detalle_servicio','Objeto del Contrato','textarea','<?php echo replace_invalid_caracters_vista($detalle_servicio); ?>','','');">
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
									<div class="panel-heading" role="tab" id="headingCCPlazoContrato">
									<h4 class="panel-title">
										<a role="button" data-toggle="collapse" data-parent="#accordionContrato" href="#collapseCCPlazoContrato" aria-expanded="true" aria-controls="collapseCCPlazoContrato">
										CONDICIONES COMERCIALES - 2) PLAZO DEL CONTRATO
										</a>
									</h4>
									</div>
									<div id="collapseCCPlazoContrato" class="panel-collapse-all panel-collapse collapse in" role="tabpanel" aria-labelledby="headingCCPlazoContrato">
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
																onclick="sec_contrato_detalle_solicitud_editar_solicitud('Plazo del Contrato','cont_contrato','plazo_id','Plazo','select_option','<?php echo $plazo; ?>','obtener_tipo_plazo');">
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
																onclick="sec_contrato_detalle_solicitud_editar_solicitud('Plazo del Contrato','cont_contrato','fecha_inicio','Fecha de Inicio','date','<?php echo $fecha_inicio; ?>','','');">
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
									<div class="panel-heading" role="tab" id="headingCCContraprestacion">
									<h4 class="panel-title">
										<a role="button" data-toggle="collapse" data-parent="#accordionContrato" href="#collapseCCContraprestacion" aria-expanded="true" aria-controls="collapseCCContraprestacion">
										CONDICIONES COMERCIALES - 3) CONTRAPRESTACIÓN
										</a>
									</h4>
									</div>
									<div id="collapseCCContraprestacion" class="panel-collapse-all panel-collapse collapse in" role="tabpanel" aria-labelledby="headingCCContraprestacion">
										<div class="panel-body">
											<div class="w-100 text-right">
												<?php if ($btn_editar_solicitud) { ?>
												<button 
													type="button" 
													class="btn btn-sm btn-primary" 
													onclick="sec_contrato_detalle_solicitud_agregar_contraprestacion()">
													<i class="fa fa-plus"></i>
													Agregar Contraprestación
												</button>
												<?php } ?>
											</div>

											<div class="w-100">
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

													if ($row_count > 0) {
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
															<b># <?php echo $contador_contraprestacion; ?></b>
															<table class="table table-bordered">
																<tr>
																	<td style="width: 50%;"><b>Tipo de moneda</b></td>
																	<td><?php echo $tipo_moneda ;?></td>
																	<?php if ($btn_editar_solicitud) { ?>
																	<td style="width: 75px;">
																		<a class="btn btn-success btn-xs" 
																		onclick="sec_contrato_detalle_solicitud_editar_solicitud('Contraprestación','cont_contraprestacion','moneda_id','Tipo de moneda','select_option','<?php echo $tipo_moneda; ?>','obtener_tipo_moneda','<?php echo $contraprestacion_id; ?>');">
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
																		onclick="sec_contrato_detalle_solicitud_editar_solicitud('Contraprestación','cont_contraprestacion','subtotal','Subtotal','decimal','<?php echo $subtotal; ?>','','<?php echo $contraprestacion_id; ?>');">
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
																		onclick="sec_contrato_detalle_solicitud_editar_solicitud('Contraprestación','cont_contraprestacion','igv','IGV','decimal','<?php echo $igv; ?>','','<?php echo $contraprestacion_id; ?>');">
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
																		onclick="sec_contrato_detalle_solicitud_editar_solicitud('Contraprestación','cont_contraprestacion','monto','Monto','decimal','<?php echo $monto; ?>','','<?php echo $contraprestacion_id; ?>');">
																			<span class="fa fa-edit"></span> Editar
																		</a>
																	</td>
																	<?php } ?>
																</tr>
																<!--	
																<tr>
																	<td><b>Forma de pago</b></td>
																	<td><?php echo $forma_pago;?></td>
																	<?php if ($btn_editar_solicitud) { ?>
																	<td>
																		<a class="btn btn-success btn-xs" 
																		onclick="sec_contrato_detalle_solicitud_editar_solicitud('Contraprestación','cont_contraprestacion','forma_pago_id','Forma de pago','select_option','<?php echo $forma_pago; ?>','obtener_forma_pago','<?php echo $contraprestacion_id; ?>');">
																			<span class="fa fa-edit"></span> Editar
																		</a>
																	</td>
																	<?php } ?>
																</tr>
																-->
																<tr>
																	<td><b>Tipo de comprobante a emitir</b></td>
																	<td><?php echo $tipo_comprobante;?></td>
																	<?php if ($btn_editar_solicitud) { ?>
																	<td>
																		<a class="btn btn-success btn-xs" 
																		onclick="sec_contrato_detalle_solicitud_editar_solicitud('Contraprestación','cont_contraprestacion','tipo_comprobante_id','Tipo de comprobante a emitir','select_option','<?php echo $tipo_comprobante; ?>','obtener_tipo_comprobante','<?php echo $contraprestacion_id; ?>');">
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
																		onclick="sec_contrato_detalle_solicitud_editar_solicitud('Contraprestación','cont_contraprestacion','plazo_pago','Plazo de Pago','varchar','<?php echo preg_replace("/[\r\n|\n|\r]+/", " ", $sel["plazo_pago"]); ?>','','<?php echo $contraprestacion_id; ?>');">
																			<span class="fa fa-edit"></span> Editar
																		</a>
																	</td>
																	<?php } ?>
																</tr>
																<tr>
																	<td><b>Forma de pago</b></td>
																	<td style="white-space: pre-line;"><?php echo $forma_pago_detallado;?></td>
																	<?php if ($btn_editar_solicitud) { ?>
																	<td>
																		<a class="btn btn-success btn-xs" 
																		onclick="sec_contrato_detalle_solicitud_editar_solicitud('Contraprestación','cont_contraprestacion','forma_pago_detallado','Forma de pago - Detallado','textarea','<?php echo replace_invalid_caracters_vista($forma_pago_detallado); ?>','','<?php echo $contraprestacion_id; ?>');">
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
													} elseif ($row_count == 0) {
														$sel_query = $mysqli->query("
														SELECT 
															c.moneda_id,
															m.nombre AS tipo_moneda,
															m.simbolo AS tipo_moneda_simbolo,
															c.monto,
															c.forma_pago_id,
															f.nombre AS forma_pago,
															c.tipo_comprobante_id,
															t.nombre AS tipo_comprobante,
															c.plazo_pago
														FROM 
															cont_contrato c
															INNER JOIN tbl_moneda m ON c.moneda_id = m.id
															INNER JOIN cont_forma_pago f ON c.forma_pago_id = f.id
															INNER JOIN cont_tipo_comprobante t ON c.tipo_comprobante_id = t.id
														WHERE 
															c.tipo_contrato_id = 2 AND c.contrato_id = $contrato_id");
														while($sel=$sel_query->fetch_assoc()){
															$tipo_moneda = $sel["tipo_moneda"];
															$tipo_moneda_simbolo = $sel["tipo_moneda_simbolo"];
															$monto = $tipo_moneda_simbolo.' '.number_format($sel["monto"], 2, '.', ',');
															$forma_pago = $sel["forma_pago"];
															$tipo_comprobante = $sel["tipo_comprobante"];
															$plazo_pago = $sel["plazo_pago"];
														?>
														<table class="table table-bordered">
															<tr>
																<td style="width: 50%;"><b>Tipo de moneda</b></td>
																<td><?php echo $tipo_moneda ;?></td>
																<?php if ($btn_editar_solicitud) { ?>
																<td style="width: 75px;">
																	<a class="btn btn-success btn-xs" 
																	onclick="sec_contrato_detalle_solicitud_editar_solicitud('Contraprestación','cont_contrato','moneda_id','Tipo de moneda','select_option','<?php echo $tipo_moneda; ?>','obtener_tipo_moneda','');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
																<?php } ?>
															</tr>
															<tr>
																<td><b>Monto</b></td>
																<td><?php echo $monto;?></td>
																<?php if ($btn_editar_solicitud) { ?>
																<td>
																	<a class="btn btn-success btn-xs" 
																	onclick="sec_contrato_detalle_solicitud_editar_solicitud('Contraprestación','cont_contrato','monto','Monto','decimal','<?php echo $monto; ?>','','');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
																<?php } ?>
															</tr>
															<tr>
																<td><b>Forma de pago</b></td>
																<td><?php echo $forma_pago;?></td>
																<?php if ($btn_editar_solicitud) { ?>
																<td>
																	<a class="btn btn-success btn-xs" 
																	onclick="sec_contrato_detalle_solicitud_editar_solicitud('Contraprestación','cont_contrato','forma_pago_id','Forma de pago','select_option','<?php echo $forma_pago; ?>','obtener_forma_pago','');">
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
																	onclick="sec_contrato_detalle_solicitud_editar_solicitud('Contraprestación','cont_contrato','tipo_comprobante_id','Tipo de comprobante a emitir','select_option','<?php echo $tipo_comprobante; ?>','obtener_tipo_comprobante','');">
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
																	onclick="sec_contrato_detalle_solicitud_editar_solicitud('Contraprestación','cont_contrato','plazo_pago','Plazo de Pago','varchar','<?php echo preg_replace("/[\r\n|\n|\r]+/", " ", $sel["plazo_pago"]); ?>','','');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
																<?php } ?>
															</tr>
														</table>
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
									<div class="panel-heading" role="tab" id="headingCCAlcanceDelServicio">
									<h4 class="panel-title">
										<a role="button" data-toggle="collapse" data-parent="#accordionContrato" href="#collapseCCAlcanceDelServicio" aria-expanded="true" aria-controls="collapseCCAlcanceDelServicio">
										CONDICIONES COMERCIALES - 4) ALCANCE DEL SERVICIO
										</a>
									</h4>
									</div>
									<div id="collapseCCAlcanceDelServicio" class="panel-collapse-all panel-collapse collapse in" role="tabpanel" aria-labelledby="headingCCAlcanceDelServicio">
										<div class="panel-body">
											<div class="w-100">
												<div class="form-group">
													<table class="table table-bordered table-hover">
														<tr>
															<td style="white-space: pre-line;"><?php echo $alcance_servicio;?></td>
															<?php if ($btn_editar_solicitud) { ?>
															<td style="width: 75px;">
																<a class="btn btn-success btn-xs" 
																onclick="sec_contrato_detalle_solicitud_editar_solicitud('Alcance del servicio','cont_contrato','alcance_servicio','Alcance del servicio','textarea','<?php echo replace_invalid_caracters_vista($alcance_servicio); ?>','','');">
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
									<div class="panel-heading" role="tab" id="headingCCTerminacionAnticipada">
									<h4 class="panel-title">
										<a role="button" data-toggle="collapse" data-parent="#accordionContrato" href="#collapseCCTerminacionAnticipada" aria-expanded="true" aria-controls="collapseCCTerminacionAnticipada">
										CONDICIONES COMERCIALES - 5) TERMINACIÓN ANTICIPADA
										</a>
									</h4>
									</div>
									<div id="collapseCCTerminacionAnticipada" class="panel-collapse-all panel-collapse collapse in" role="tabpanel" aria-labelledby="headingCCTerminacionAnticipada">
										<div class="panel-body">
											<div class="w-100">
												<div class="form-group">
													<table class="table table-bordered table-hover">
														<tr>
															<td><?php echo $tipo_terminacion_anticipada;?></td>
															<?php if ($btn_editar_solicitud) { ?>
															<td style="width: 75px;">
																<a class="btn btn-success btn-xs" 
																onclick="sec_contrato_detalle_solicitud_editar_solicitud('Terminación Anticipada','cont_contrato','tipo_terminacion_anticipada_id','Terminación Anticipada','select_option','<?php echo $tipo_terminacion_anticipada; ?>','obtener_tipo_terminacion_anticipada','');">
																	<span class="fa fa-edit"></span> Editar
																</a>
															</td>
															<?php } ?>
														</tr>
														<?php if ($tipo_terminacion_anticipada_id == "1") { ?>
														<tr>
															<td style="white-space: pre-line;"><?php echo $terminacion_anticipada;?></td>
															<?php if ($btn_editar_solicitud) { ?>
															<td style="width: 75px;">
																<a class="btn btn-success btn-xs" 
																onclick="sec_contrato_detalle_solicitud_editar_solicitud('Terminación Anticipada','cont_contrato','terminacion_anticipada','Terminación Anticipada - Detalle','textarea','<?php echo preg_replace("/[\r\n|\n|\r]+/", " ", $terminacion_anticipada); ?>','','');">
																	<span class="fa fa-edit"></span> Editar
																</a>
															</td>
															<?php } ?>
														</tr>
														<?php } ?>
													</table>
												</div>
											</div>
										</div>
									</div>
								</div>


								<div class="panel panel-default">
									<div class="panel-heading" role="tab" id="headingCCObservaciones">
									<h4 class="panel-title">
										<a role="button" data-toggle="collapse" data-parent="#accordionContrato" href="#collapseCCObservaciones" aria-expanded="true" aria-controls="collapseCCObservaciones">
										CONDICIONES COMERCIALES - 6) OBSERVACIONES
										</a>
									</h4>
									</div>
									<div id="collapseCCObservaciones" class="panel-collapse-all panel-collapse collapse in" role="tabpanel" aria-labelledby="headingCCObservaciones">
										<div class="panel-body">
											<div class="w-100">
												<div class="form-group">
													<table class="table table-bordered table-hover">
														<tr>
															<td style="white-space: pre-line;"><?php echo $observaciones_legal;?></td>
															<?php if ($btn_editar_solicitud) { ?>
															<td style="width: 75px;">
																<a class="btn btn-success btn-xs" 
																onclick="sec_contrato_detalle_solicitud_editar_solicitud('Observaciones','cont_contrato','observaciones','Observaciones','textarea','<?php echo replace_invalid_caracters_vista($observaciones_legal); ?>','','');">
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

								

								<?php 
								if ($etapa_id == 5){
									?>
									<div class="panel panel-default">
										<div class="panel-heading" role="tab" id="headingCCObjetoDeADenda">
										<h4 class="panel-title">
											<a role="button" data-toggle="collapse" data-parent="#accordionContrato" href="#collapseCCObjetoDeADenda" aria-expanded="true" aria-controls="collapseCCObjetoDeADenda">
											CONDICIONES COMERCIALES - 7) OBJETOS DE ADENDAS
											</a>
										</h4>
										</div>
										<div id="collapseCCObjetoDeADenda" class="panel-collapse-all panel-collapse collapse in" role="tabpanel" aria-labelledby="headingCCObjetoDeADenda">

										<?php 
											$sql_objetos = "SELECT co.id, co.contrato_id, co.contrato_detalle_id, co.archivo_id, co.objeto, co.created_at ,a.codigo
											FROM cont_contrato_objetos AS co 
											LEFT JOIN cont_adendas AS a ON a.id = co.adenda_id
											WHERE co.status = 1 AND co.contrato_id = ".$contrato_id." ORDER BY co.id ASC";
											$query_objetos = $mysqli->query($sql_objetos);
											$row_count = $query_objetos->num_rows;
										?>
									
									
											<div class="panel-body">
												<div class="w-100">
													<div class="form-group">

														<?php 
														while($sel = $query_objetos->fetch_assoc())	{
														?>
														<table class="table table-bordered table-hover">
															<tr>
																<td>Objeto de Adenda <?=!Empty($sel['codigo']) ? '#'.$sel['codigo']:'' ?></td>
															</tr>
															<tr>
																<td style="white-space: pre-line;"><?php echo $sel['objeto'];?></td>
																<?php if ($btn_editar_solicitud) { ?>
																<td style="width: 75px;">
																	<a class="btn btn-success btn-xs" 
																	onclick="sec_contrato_detalle_solicitud_editar_solicitud('Objeto de Adenda','cont_contrato_objetos','objeto','Objeto de Adenda','textarea','<?php echo replace_invalid_caracters_vista($sel['objeto']); ?>','','<?=$sel['id']?>');">
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
									<?php
								}
								?>

								



							</div>
							<!-- FINAL PANEL DETALLE CONTRATO -->

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
										<button type="button" class="btn btn-sm btn-info" onclick="agregarNuevoAnexoConProv();"><i class="fa fa-plus"></i> Agregar Nuevo Anexo</button>
										<table class="table table-responsive no-mb" style="font-size: 10px;">
											<thead style="background: none;">
												<tr style="text-transform: none;">
													<th align="center">Nombre del Documento</th>
													
													<?php  

													if(($area_id == 33 AND $cargo_id != 25) || $user_created_id == $login['id'] || $permiso_editar_solicitud_contrato)
													{
														?>
															<th align="center">Operación</th>
														<?php
													}
													?>
													
													<th align="center">Visualizar</th>

													<?php
													if ((array_key_exists($menu_consultar,$usuario_permisos) && in_array("eliminar_anexo", $usuario_permisos[$menu_consultar]))) { 
													?>
														<th align="center"></th>
													<?php
													}
													?>
												</tr>
											</thead>
											<tbody>
												<tr style="text-transform: none;">
													<td>Solicitud de Contratos de Proveedor</td>
													<td colspan="2">
														<a onclick="sec_contrato_detalle_solicitud_ver_documento_en_visor('','', 'html', '');" class="btn btn-primary btn-xs btn-block" data-toggle="tooltip" data-placement="top"><span class="fa fa-eye"></span> Ver Detalle de la Solicitud</a>
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
														<?php 
														if ((array_key_exists($menu_consultar,$usuario_permisos) && in_array("subir_contrato_firmado", $usuario_permisos[$menu_consultar])) && $etapa_id == 5) { 
														?>
														<td colspan="1">
															<a
																onclick="moda_subir_archivo_req_solicitud_arrendamiento('Contrato Firmado','','19');"
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
														}else{
														?>
														<td colspan="2">
															<a class="btn btn-warning btn-xs btn-block" data-toggle="tooltip" data-placement="top"><span class="fa fa-eye-slash"></span> Incompleto</a>
														</td>
														<?php
														}
														?>
													<?php 
													} else if($num_rows > 0) {
														$row = $sel_contrato_firmado->fetch_assoc();
														$ruta = str_replace("/var/www/html","",$row["ruta"]);
													?>
														<td>
															<a
																onclick="moda_reemplazar_archivo_req_solicitud_arrendamiento('<?=$row['nombre'];?>','<?=$row['archivo_id'];?>','<?=$row['tipo_archivo_id'];?>');"
																class="btn btn-success btn-xs btn-block"
																data-toggle="tooltip"
																data-placement="top">
																<span class="fa fa-upload"></span> Reemplazar
															</a>
														</td>
														<td>
															<a
																onclick="sec_contrato_detalle_solicitud_ver_documento_en_visor('<?php echo $ruta;?>','<?php echo trim($row["nombre"]); ?>','<?php echo trim($row["extension"]); ?>','CONTRATO FIRMADO');"
																class="btn btn-success btn-xs btn-block"
																data-toggle="tooltip"
																data-placement="top">
																<span class="fa fa-eye"></span> Ver Contrato Firmado
															</a>
														</td>

													<?php
													}
													?>
														
													</tr>
												<?php
												}
												// FIN CONTRATO FIRMADO

							

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
														'' AS id_representante_legal,
														'' AS nombre_representante
													FROM
														cont_tipo_archivos t
														LEFT JOIN cont_archivos a ON t.tipo_archivo_id = a.tipo_archivo_id AND a.contrato_id = $contrato_id AND a.status = 1
													WHERE
														t.tipo_archivo_id IN (1,30,118)
													UNION ALL 

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
														t.tipo_archivo_id NOT IN (1,30,118,19,63,152)
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
															$archivo .= ' onclick="sec_contrato_detalle_solicitud_ver_documento_en_visor(\''. trim($ruta) . '\', \''. trim($row["nombre"]) . '\', \'' . trim($row["extension"]) . '\', \'' . $row["nombre_tipo_archivo"] . '\');"';
															$archivo .= ' class="btn btn-success btn-xs btn-block"';
															$archivo .= ' data-toggle="tooltip"';
															$archivo .= ' data-placement="top">';
															$archivo .= ' <span class="fa fa-eye"></span> Ver Documento';
															$archivo .= ' </a>';

															if($row["id_representante_legal"] > 0){
																$archivo_estado = '<a class="class_SubirFile_req_solicitud_arrendamiento btn btn-success btn-xs btn-block" id="btnSubirFile_req_solicitud_arrendamiento" data-toggle="tooltip" data-placement="top" onclick="moda_subir_archivo_req_solicitud_proveedor_representante_legal(\''.$row["nombre_tipo_archivo"].'\', \''.$row["archivo_id"].'\', \''.$row["tipo_archivo_id"].'\',\''. $row["id_representante_legal"] .'\');"><i class="fa fa-upload"></i> Reemplazar</a>';
															}else{
																$archivo_estado = '<a class="class_SubirFile_req_solicitud_arrendamiento btn btn-success btn-xs btn-block" id="btnSubirFile_req_solicitud_arrendamiento" data-toggle="tooltip" data-placement="top" onclick="moda_subir_archivo_req_solicitud_arrendamiento(\''.$row["nombre_tipo_archivo"].'\', \''.$row["archivo_id"].'\', \''.$row["tipo_archivo_id"].'\');"><i class="fa fa-upload"></i> Reemplazar</a>';
															}
														}
														else 
														{
															$archivo = '<a class="btn btn-warning btn-xs btn-block" data-toggle="tooltip" data-placement="top"><span class="fa fa-eye-slash"></span> Incompleto</a>';

															$archivo_estado = '<a class="class_SubirFile_req_solicitud_arrendamiento btn btn-warning btn-xs btn-block" id="btnSubirFile_req_solicitud_arrendamiento" data-toggle="tooltip" data-placement="top" onclick="moda_subir_archivo_req_solicitud_arrendamiento(\''.$row["nombre_tipo_archivo"].'\', \''.$row["archivo_id"].'\', \''.$row["tipo_archivo_id"].'\');"><i class="fa fa-upload"></i> Subir</a>';
														}

														$html .= '<tr style="text-transform: none;">';
														if($row["id_representante_legal"] > 0){
															$html .= '<td>Anexo - ' . $row["nombre_tipo_archivo"] . ' (' . $row["nombre_representante"] . ')</td>';
														}else{
															$html .= '<td>Anexo - ' . $row["nombre_tipo_archivo"] . '</td>';
														}

														if(($area_id == 33 AND $cargo_id != 25) || $user_created_id == $login['id'] || $permiso_editar_solicitud_contrato)
														{
															$html .= '<td>'.$archivo_estado.'</td>';
														}

														$html .= '<td>'.$archivo.'</td>';
														if ((array_key_exists($menu_consultar,$usuario_permisos) && in_array("eliminar_anexo", $usuario_permisos[$menu_consultar])) && strlen(trim($row["nombre"])) > 1) { 
															$html .= '<td>
																<a
																onclick="sec_contrato_detalle_solicitud_ver_eliminar_anexo('.$row['archivo_id'].')"
																	class="btn btn-danger btn-xs btn-block"
																	data-toggle="tooltip"
																	data-placement="top">
																	<span class="fa fa-trash"></span>
																</a>
															</td>';
														}
														$html .= '</tr>';
													}
												}

												echo $html;

												?>
											</tbody>
										</table>

										<?php
										// INICIO ADENDA FIRMADA
										if (( $area_id == 33 && $cargo_id != 25 && $etapa_id == 5 ) || (array_key_exists($menu_consultar,$usuario_permisos) && in_array("ver_adenda_firmado", $usuario_permisos[$menu_consultar]))) { 
										?>
											<table class="table table-responsive table-hover" style="font-size: 10px;">
												<thead style="background: none;">
													<tr>
														<th colspan="3" class="text-center">ADENDAS 
															<button onclick="fnc_modal_nuevo_documento_adenda();" style="float: right" class="btn btn-xs btn-primary" type="button">Nueva Adenda</button>	
														</th>
													</tr>
													<tr style="text-transform: none;">
														<th class="text-center">Nombre del Documento</th>

														<?php  

														if(!(array_key_exists($menu_consultar,$usuario_permisos) && in_array("request", $usuario_permisos[$menu_consultar])))
														{
															?>
																<th class="text-center">Operación</th>		
															<?php
														}
														else
														{
															if(($area_id == 33 AND $cargo_id != 25) || $usuario_created_id == $login['id'])
															{
																?>
																	<th class="text-center">Operación</th>
																<?php
															}
														}

														if($area_id == 33)
														{
															?>
																<th class="text-center">Visualizar</th>			
															<?php
														}
														else if((array_key_exists($menu_consultar,$usuario_permisos) && in_array("request", $usuario_permisos[$menu_consultar])))
														{
															?>
																<th class="text-center">Visualizar</th>		
															<?php
														}
														else
														{
															if($area_id != 33)
															{
																?>
																	<th class="text-center">Visualizar</th>			
																<?php
															}
														}

														?>

														<?php
														if ((array_key_exists($menu_consultar,$usuario_permisos) && in_array("eliminar_anexo", $usuario_permisos[$menu_consultar])) || (array_key_exists($menu_consultar,$usuario_permisos) && in_array("eliminar_adenda_escision", $usuario_permisos[$menu_consultar]))) { 
														?>
															<th align="center"></th>
														<?php
														}
														?>

													</tr>
												</thead>
												<tbody>
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
													ad.codigo,
													ca.id as adenda_escision_id,
													CASE
														WHEN ar.tipo_archivo_id = 63 THEN 'Adenda Firmada'
														ELSE ''
													END AS nombre_adenda
												FROM
													cont_archivos AS ar
													LEFT JOIN cont_adendas AS ad ON ad.archivo_id = ar.archivo_id
													LEFT JOIN cont_contrato_escision AS ca ON ca.archivo_id = ar.archivo_id
												WHERE
													ar.tipo_archivo_id IN (63)
													AND ar.status = 1
													AND ar.contrato_id = " . $contrato_id.
													" ORDER BY ad.codigo,ar.tipo_archivo_id ASC"
												);
												$num_rows = mysqli_num_rows($sel_contrato_firmado);
												if($num_rows > 0) {
													while ($row = $sel_contrato_firmado->fetch_assoc()){
														$ruta = str_replace("/var/www/html","",$row["ruta"]);
														?>
														<tr style="text-transform: none;">
														<td><?=$row['nombre_adenda']?> <?=!Empty($row['codigo']) ? 'N° '.$row['codigo'] : ''?></td>
															<td>
																<a
																	onclick="moda_reemplazar_archivo_req_solicitud_arrendamiento('<?=$row['nombre'];?>','<?=$row['archivo_id'];?>','<?=$row['tipo_archivo_id'];?>','<?=$contrato_detalle_id?>');"
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
																	<span class="fa fa-eye"></span> Ver <?=$row['nombre_adenda']?>
																</a>
															</td>
															<?php 
															if ((array_key_exists($menu_consultar,$usuario_permisos) && in_array("eliminar_anexo", $usuario_permisos[$menu_consultar])) && Empty($row['codigo']) && $row['tipo_archivo_id'] != '154') { // 154 => Adendas de Escisión
															?>
															<td>
																<a
																	onclick="sec_contrato_detalle_solicitud_ver_eliminar_anexo('<?=$row['archivo_id'];?>')"
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
														<td>Archivos adendas  <?=!Empty($row['codigo']) ? 'N° '.$row['codigo'] : ''?></td>
															<td>
																<a
																	onclick="moda_reemplazar_archivo_req_solicitud_arrendamiento('<?=$row['nombre'];?>','<?=$row['archivo_id'];?>','<?=$row['tipo_archivo_id'];?>','<?=$contrato_detalle_id?>');"
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
															<?php 
															if ((array_key_exists($menu_consultar,$usuario_permisos) && in_array("eliminar_anexo", $usuario_permisos[$menu_consultar])) && Empty($row['codigo']) && $row['tipo_archivo_id'] != '154') { // 154 => 
															?>
															<td>
																<a
																	onclick="sec_contrato_detalle_solicitud_ver_eliminar_anexo('<?=$row['archivo_id'];?>')"
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
								CONCAT(IFNULL(puap.nombre, ''),' ',IFNULL(puap.apellido_paterno, ''),' ',IFNULL(puap.apellido_materno, '')) AS adenda_aprobada_por,
								CONCAT(IFNULL(pud.nombre, ''),' ',IFNULL(pud.apellido_paterno, ''),' ',IFNULL(pud.apellido_materno, '')) AS director_que_aprueba
							FROM 
								cont_adendas a
								INNER JOIN tbl_usuarios u ON a.user_created_id = u.id
								INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
								INNER JOIN tbl_areas ar ON p.area_id = ar.id
								LEFT JOIN tbl_usuarios tu3 ON a.cancelado_por_id = tu3.id
								LEFT JOIN tbl_personal_apt tpa3 ON tu3.personal_id = tpa3.id

								LEFT JOIN tbl_usuarios uab ON a.abogado_id = uab.id
								LEFT JOIN tbl_personal_apt pab ON uab.personal_id = pab.id

								LEFT JOIN tbl_usuarios uap ON a.aprobado_por_id = uap.id
								LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id
								LEFT JOIN tbl_usuarios ud ON a.director_aprobacion_id = ud.id
								LEFT JOIN tbl_personal_apt pud ON ud.personal_id = pud.id
							WHERE a.cancelado_id IS NULL AND a.contrato_id = $contrato_id
							AND a.status = 1;");
							$row_cnt = $sel_query->num_rows;
							if ($row_cnt > 0) {
							?>

							<div class="panel">
								<div class="panel-heading" role="tab" id="browsers-adendas-heading">
									<div class="panel-title">
										<a href="#browsers-adendas" class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" aria-expanded="true" aria-controls="browsers-adendas" id="btn_adendas">
											Adendas
										</a>
									</div>
								</div>

								<div id="browsers-adendas" class="panel-collapse collapse" role="tabpanel" aria-labelledby="browsers-adendas-heading">
									<div class="panel-body">

										<?php 
									   
										while($sel = $sel_query->fetch_assoc()){
											$adenda_id = $sel["id"];
											$codigo = $sel["codigo"];
											$procesado = trim($sel["procesado"]);
											$cancelado_id = $sel["cancelado_id"];
											$cancelado_por = $sel['cancelado_por'];
											$cancelado_el = $sel['cancelado_el'];
											$cancelado_motivo = $sel['cancelado_motivo'];
											$estado_solicitud_id = $sel["estado_solicitud_id"];
											$area = $sel["area"];
											$solicitante = $sel["solicitante"];
											$abogado = $sel["abogado"];
											$fecha_solicitud = $sel["fecha_solicitud"];
											$requiere_aprobacion_id = trim($sel["requiere_aprobacion_id"]);
											$aprobado_estado_id = trim($sel["aprobado_estado_id"]);
											$aprobado_el = $sel["aprobado_el"];
											$adenda_aprobada_por = $sel["adenda_aprobada_por"];
											$director_que_aprueba = $sel["director_que_aprueba"];
											$numero_adenda++;

											if ($procesado != "") {
												$procesado = (int) $procesado;
											}

											if ($requiere_aprobacion_id != "") {
												$requiere_aprobacion_id = (int) $requiere_aprobacion_id;
											}

											if ($aprobado_estado_id != "") {
												$aprobado_estado_id = (int) $aprobado_estado_id;
											}

											$adenda_estado = '<span class="badge bg-warning text-white">Pendiente</span>';

											if ($cancelado_id == 1) {
												$adenda_estado = '<span class="badge bg-danger text-white">Cancelada</span>';
											} elseif ($procesado === 0) {
												if ($requiere_aprobacion_id === 1) { 
													if ($aprobado_estado_id === 1) {
														$adenda_estado = '<span class="badge bg-success text-white">Aprobado</span>';
													} elseif($aprobado_estado_id === 0) {
														$adenda_estado = '<span class="badge bg-danger text-white">Rechazada</span>';
													}
												}
											} elseif ($procesado === 1) {
												$adenda_estado = '<span class="badge bg-info text-white">Procesado</span>';
											}

											$resplandor_adenda = '';

											if ($adenda_id == $adenta_id_temporal) {
												$resplandor_adenda = 'class="resplandor" tabindex="1"';
											}

											?>

											<div <?php echo $resplandor_adenda; ?> style="border: 1px solid black; padding: 20px 20px; min-height: 100px;" >

												<p>
													<b>Adenda N° <?php echo $codigo; ?>:</b> 
													<?php if ((array_key_exists($menu_consultar,$usuario_permisos) && in_array("reenviar_adenda_firmada", $usuario_permisos[$menu_consultar])) && ($procesado == 1 || $aprobado_estado_id == 1)) { ?>
													<button type="button" onclick="sec_contrato_detalle_reenviar_adenda(<?=$sel['id']?>,'2')" style="float: right" class="btn btn-xs btn-info"><span class="fa fa-envelope-o"></span> Reenviar por email</button>
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

														<?php 
														if ($requiere_aprobacion_id == 1) { 
															if ($aprobado_estado_id === 1) {
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
															} elseif ($aprobado_estado_id === 0) {
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

														if ($procesado === 1) {
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
															WHERE a.tipo_archivo_id = 63
																AND a.contrato_id = $contrato_id
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
																onclick="sec_contrato_detalle_solicitud_ver_documento_en_visor(\'' . str_replace("/var/www/html","",$row["ruta"]) . '\',\'' . trim($row["nombre"]) . '\',\'' . trim($row["extension"]) . '\',\'ADENDA FIRMADA\');"
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

												<br/>

												<p><b>Adenda N° <?php echo $codigo; ?> - Detalle:</b></p>

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
													WHERE adenda_id = $adenda_id
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
															$nombre_campo_usuario = trim($row["nombre_campo_usuario"]);
															$valor_original = trim($row["valor_original"]);
															$tipo_valor = trim($row["tipo_valor"]);

															if ($tipo_valor == 'varchar') {
																$nuevo_valor = trim($row['valor_varchar']);
															} else if ($tipo_valor == 'int') {
																$nuevo_valor = trim($row['valor_int']);
															} else if ($tipo_valor == 'date') {
																$nuevo_valor = trim($row['valor_date']);
															} else if ($tipo_valor == 'decimal') {
																$nuevo_valor = trim($row['valor_decimal']);
															} else if ($tipo_valor == 'select_option') {
																$nuevo_valor = trim($row['valor_select_option']);
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
														if ($row["nombre_menu_usuario"] == 'Representante Legal') {
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
																<br/>
															</div>
					
															<div>
																<table class="table table-responsive table-hover no-mb" style="font-size: 10px;">
											
																	<thead style="background: none;">

																		<tr style="text-transform: none;">
																			<th colspan="4" >
																				<b>Nuevo Representante Legal</b>
																			</th>
																		</tr>
													
																		<tr>
																			<td align="center" class="test-dark">Campo:</td>
																			<td align="center" class="test-dark">Valor:</td>
																		</tr>
																	
																	</thead>
												
																	<tr>
																		<td >DNI del representante legal</td>
																		<td ><?=$valores_nuevos[0]["dni_representante"]?></td>
																	</tr>
												
																	<tr>
																		<td >Nombre completo del representante legal</td>
																		<td ><?=$valores_nuevos[0]["nombre_representante"]?></td>
																	</tr>
												
																	<tr>
																		<td >Nro cuenta de detraccion (Banco de la nación)</td>
																		<td ><?=$valores_nuevos[0]["nro_cuenta_detraccion"]?></td>
																	</tr>
												
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
															$query_cont = "
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
															$list_query = $mysqli->query($query_cont);
															while ($li = $list_query->fetch_assoc()) {
																if ($li["id"] == $row["valor_int"]) {
																	$valores_nuevos[] = $li;
																}
															}
														?>
															<div>
																<br/>
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

												<br/>

												<?php
												

												if ($procesado === 0 && $area_id == '33' && $cancelado_id != 1) {
												?>

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

												<p><b>Adenda N° <?php echo $codigo; ?> - Agregar Adenda Firmada</b></p>

												<form id="form_adenda_firmada_<?=$adenda_id?>" name="form_contrato_firmado" method="POST" enctype="multipart/form-data" autocomplete="off">

													<div style="margin-right: 10px; margin-left: 5px;">
														<div class="form-group">
															<input type="hidden" name="adenda_id_<?=$adenda_id?>" id="adenda_id_<?=$adenda_id?>" value="<?php echo $adenda_id;?>">
															<div class="control-label">Seleccione la adenda firmada:</div>
															<input type="file" name="adenda_firmada_<?=$adenda_id?>" id="adenda_firmada_<?=$adenda_id?>" required accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip">
														</div>
													</div>

													<div style="margin-right: 10px; margin-left: 5px;">
														<div class="form-group">

															<button type="button" class="btn btn-success btn-xs btn-block" id="btn_guardar_adenda_firmada" onclick="sec_contrato_detalle_solicitud_guardar_adenda_proveedor_firmada(<?=$adenda_id?>)">
																<i class="icon fa fa-plus"></i>
																<span id="demo-button-text">Agregar adenda firmada</span>
															</button>
														</div>
													</div>

													<div style="margin-right: 10px; margin-left: 5px; display: none;" id="div_adenda_mensaje">
														<br/>
														<div class="form-group">
															<div class="alert alert-danger" role="alert">
																<strong id="adendas_mensaje"></strong>
															</div>
														</div>
													</div>
												</form>
												<?php
												}

												if($requiere_aprobacion_id == 1 && $aprobado_estado_id === "" && $procesado === 0 && $cancelado_id != 1)
												{
													$boton_aprobacion_gerencia = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' AND sub_sec_id = '$sub_sec_id' LIMIT 1")->fetch_assoc();
													$id_boton_aprobacion_gerencia = $boton_aprobacion_gerencia["id"];

													if(array_key_exists($id_boton_aprobacion_gerencia,$usuario_permisos) && in_array("validaBotonSolicitud", $usuario_permisos[$id_boton_aprobacion_gerencia]))
													{
														?>
														<hr>
														<p><b>Adenda N° <?php echo $codigo; ?> - Aprobación de Gerencia:</b></p>
														<div>
															<div style="margin-right: 0px; margin-left: 0px; min-height: 170px;">

																<form id="form_aprobar_adenda_por_gerencia" name="form_aprobar_adenda_por_gerencia" method="POST" enctype="multipart/form-data" autocomplete="off">
																	
																	<div class="col-xs-12 col-md-6 col-lg-6" style="padding-left: 0px;">
																		<button type="button" class="btn btn-success btn-xs btn-block col-md-6" style="height: 30px;" onclick="sec_contrato_detalle_solicitud_aprobar_adenda(1, <?=$adenda_id?>);">
																			<span id="demo-button-text">
																				<i class="glyphicon glyphicon-saved"></i>
																				Aceptar solicitud
																			</span>
																		</button>
																	</div>

																	<div class="col-xs-12 col-md-6 col-lg-6" style="padding-right: 0px;">
																		<button type="button" class="btn btn-danger btn-xs btn-block" style="height: 30px;" onclick="sec_contrato_detalle_solicitud_aprobar_adenda(0, <?=$adenda_id?>)">
																			<span id="demo-button-text">
																				<i class="glyphicon glyphicon-remove-sign"></i>
																				Rechazar solicitud
																			</span>
																		</button>
																	</div>

																</form>

																<br/><br/>

																<div>
																	<b>Adenda N° <?php echo $codigo; ?> - Observación:</b>
																	<div id="div_observaciones_adenda_gerencia_<?=$adenda_id?>" class="timeline" style="font-size: 11px;">
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

																		if ($row_count > 0) 
																		{
																			while ($row = $query->fetch_assoc()) 
																			{
																				$date = date_create($row["created_at"]);
																				$created_at = date_format($date,"d/m/Y h:i a");

																				if($row["user_created_id"] == $login['id'])
																				{
																					// ESTE DIV ES PARA EL USUARIO LOGUEADO
																					$html .= '<div class="col-sm-offset-1 col-sm-11 caja_usuario_aprobacion alert alert-success" style="padding-bottom:10px;color:#333;box-shadow: 4px 4px 4px rgba(0,0,0,0.15);">';
																					
																					$html .= '<div class="col-sm-8" style="margin-bottom:5px;">';
																					$html .= '<strong>'. $row["usuario"] .'(' . $row["area"] .')</strong>';
																					$html .= '</div>';

																					$html .= '<div class="col-sm-4 text-right" style="color:rgba(0,0,0,0.5); font-size: 11px;">';
																					$html .= '<span class="time"><i class="fa fa-clock-o"></i> ' . $created_at . '</span>';
																					$html .= '</div>';

																					$html .= '<div class="col-xs-12" style="padding-top: 5px; background:rgba(255,255,255,0.7); margin-bottom:2px;">';
																					$html .= $row["observaciones"];
																					$html .= '</div>';

																					$html .= '</div>';
																				}
																				else
																				{
																					// ESTE DIV ES PARA OTROS USUARIOS
																					$html .= '<div class="col-sm-11 caja_usuario_creador alert alert-info" style="padding-bottom:10px;color:#333;box-shadow: 4px 4px 4px rgba(0,0,0,0.15);">';
																					
																					$html .= '<div class="col-sm-8" style="margin-bottom:5px;">';
																					$html .= '<strong>'. $row["usuario"] .'(' . $row["area"] .')</strong>';
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
																	<button class="btn btn-warning btn-xs btn-block" type="submit" onclick="sec_contrato_detalle_solicitud_guardar_observaciones_proveedores_adenda_gerencia(<?=$adenda_id?>);">
																		<i class="fa fa-plus"></i> Enviar Observación
																	</button>
																</div>


															</div>

															
														</div>

														
																				
														<?php
													}
												}
												?>



												<!-- Inicio Seguimiento de Proceso -->
												<hr>
												<p><b>Adenda N° <?php echo $codigo; ?> - Seguimiento de Proceso:</b></p>
												<?php
													$query_seg = "SELECT 
														spl.id ,
														spl.tipo_documento_id ,
														csp.nombre  as proceso,
														spl.proceso_id,
														spl.proceso_detalle_id ,
														spl.etapa_id,
														spl.area_id,
														spl.status, 
														DATE_FORMAT(spl.created_at, '%Y-%m-%d %H:%i') as created_at,
														cesl.nombre as etapa,
														ta.nombre as area
													FROM
														cont_seguimiento_proceso_legal as spl
													INNER JOIN cont_seguimiento_tipo_documento csp ON csp.id = spl.tipo_documento_id 
													INNER JOIN cont_etapa_seguimiento_legal cesl ON
														cesl.id = spl.etapa_id
													INNER JOIN tbl_areas ta ON
														ta.id = spl.area_id 
													WHERE
														spl.tipo_documento_id  = 2 AND spl.status = 1 AND spl.proceso_id = ". $adenda_id." ORDER BY spl.id ASC";
													$sel_spl_query = $mysqli->query($query_seg);
													$cant_seguimientos = $sel_spl_query->num_rows;
												?>
												<?php
												if ($cant_seguimientos > 0) {

													$INICIO_PROCESO_LEGAL = 2;
													$REVISION_AREA_USUARIA = 3;
													$REVISION_DEL_PROVEEDOR = 4;
													$REVISION_AREA_LEGAL = 5;
													$PASO_A_FIRMAS = 6;
													$CONFORMIDAD_AREA_USUARIA = 7;
													$NO_HAY_SEGUIMIENTO = 8;
													$OBSERVADO = 9;
													$AREA_LEGAL_ID = 33;

													if ($procesado === 0 && $cancelado_id != 1) {
														while($sel_spl=$sel_spl_query->fetch_assoc()){
														?>
														<div class="" style="padding-top: 5px; border: 1px solid #cfd0d3; border-radius: 5px; font-size:10px;">
															<div class="row">
																<div class="col-md-3 text-left">
																	<p><b><?=$sel_spl['area']?></b></p>
																</div>
																<div class="col-md-6 text-center">
																	<p class="text-warning"><b><?=$sel_spl['etapa']?></b></p>
																</div>
																<div class="col-md-3 text-right">
																	<p class="muted"><?=$sel_spl['created_at']?></p>
																</div>
															</div>
															<?php
															if ($sel_spl['etapa_id'] == $REVISION_AREA_USUARIA && ($sel_spl['area_id'] == $area_id)) {
																?>
																<div class="row" style="padding-bottom: 7px;">
																	<div class="col-md-4">
																		<button type="button" seguimiento-id="<?=$sel_spl['id']?>" nueva-etapa-id="<?=$REVISION_DEL_PROVEEDOR?>" class="w-100 btn-atender-seguimiento-proceso btn btn-xs btn-warning">Revisión <br> Proveedor</button>
																	</div>
																	<div class="col-md-4"></div>
																	<div class="col-md-4 text-center">
																		<button type="button" seguimiento-id="<?=$sel_spl['id']?>" nueva-etapa-id="<?=$REVISION_AREA_LEGAL?>" class="w-100 btn-atender-seguimiento-proceso btn btn-xs btn-primary">Revisión <br> Área Legal</button>
																	</div>
																</div>
																<?php 
															}else if ($sel_spl['etapa_id'] == $REVISION_DEL_PROVEEDOR && ($sel_spl['area_id'] == $area_id)){
																?>
																<div class="row" style="padding-bottom: 7px;">
																	<div class="col-md-4"></div>
																	<div class="col-md-4 text-center">
																		<button type="button" seguimiento-id="<?=$sel_spl['id']?>" nueva-etapa-id="<?=$REVISION_AREA_LEGAL?>" class="w-100 btn-atender-seguimiento-proceso btn btn-xs btn-primary">Revisión <br> Área Legal</button>
																	</div>
																	<div class="col-md-4"></div>
																</div>
																<?php
															}else if ($sel_spl['etapa_id'] == $REVISION_AREA_LEGAL && ($sel_spl['area_id'] == $area_id)){
																?>
																<div class="row" style="padding-bottom: 7px;">
																	<div class="col-md-4">
																		<button type="button" seguimiento-id="<?=$sel_spl['id']?>" nueva-etapa-id="<?=$REVISION_AREA_USUARIA?>" class="w-100 btn-atender-seguimiento-proceso btn btn-xs btn-warning">Revisión <br> Área Usuaria</button>
																	</div>
																	<div class="col-md-4"></div>
																	<div class="col-md-4 text-center">
																		<button type="button" seguimiento-id="<?=$sel_spl['id']?>" nueva-etapa-id="<?=$PASO_A_FIRMAS?>" class="w-100 btn-atender-seguimiento-proceso btn btn-xs btn-primary">Paso a <br> Firmas</button>
																	</div>
																</div>
																<?php
															}else if ($sel_spl['etapa_id'] == $CONFORMIDAD_AREA_USUARIA && ($sel_spl['area_id'] == $area_id)){
																?>
																<div class="row" style="padding-bottom: 7px;">
																	
																	<div class="col-md-4">
																		<button type="button" seguimiento-id="<?=$sel_spl['id']?>" nueva-etapa-id="<?=$NO_HAY_SEGUIMIENTO?>" class="w-100 btn-atender-seguimiento-proceso btn btn-xs btn-danger">No Hay <br> Seguimiento</button>
																	</div>
																	<div class="col-md-4">
																		<button type="button" seguimiento-id="<?=$sel_spl['id']?>" nueva-etapa-id="<?=$REVISION_AREA_LEGAL?>" class="w-100 btn-atender-seguimiento-proceso btn btn-xs btn-warning">Revisión <br> Área Legal</button>
																	</div>
																	<div class="col-md-4 text-center">
																		<button type="button" seguimiento-id="<?=$sel_spl['id']?>" nueva-etapa-id="<?=$CONFORMIDAD_AREA_USUARIA?>" class="w-100 btn-atender-seguimiento-proceso btn btn-xs btn-primary">Conformidad <br> Área Usuaria</button>
																	</div>
																</div>
																<?php
															}
															?>	

														</div>
														<?php 
														}
													}

													// En caso que su ultimo movimiento sea observado legal tiene la opcion de reunadar el proceso
													$query_seg_rev_usuario = "SELECT 
														spl.id ,
														spl.tipo_documento_id ,
														csp.nombre  as proceso,
														spl.proceso_id,
														spl.proceso_detalle_id ,
														spl.etapa_id,
														spl.area_id,
														spl.status, 
														DATE_FORMAT(spl.created_at, '%Y-%m-%d %H:%i') as created_at,
														cesl.nombre as etapa,
														ta.nombre as area
													FROM
														cont_seguimiento_proceso_legal as spl
													INNER JOIN cont_seguimiento_tipo_documento csp ON csp.id = spl.tipo_documento_id 
													INNER JOIN cont_etapa_seguimiento_legal cesl ON
														cesl.id = spl.etapa_id
													INNER JOIN tbl_areas ta ON
														ta.id = spl.area_id 
													WHERE
														spl.tipo_documento_id = 2 AND spl.status IN (1,2) AND spl.proceso_id = ". $adenda_id." ORDER BY spl.id DESC LIMIT 1";
													$sel_query_observador = $mysqli->query($query_seg_rev_usuario);
													$sel_observado = $sel_query_observador->fetch_assoc();

													$row_seg_obs = $sel_query_observador->num_rows;
													if ($row_seg_obs > 0) {
														if (isset($sel_observado['etapa_id']) && $sel_observado['etapa_id'] == $OBSERVADO && $area_id == $AREA_LEGAL_ID){
															?>
															<div class="row" style="padding-bottom: 7px;">
																<div class="col-md-4"></div>
																<div class="col-md-4 text-center">
																	<button type="button" tipo-documento-id="1" proceso-id="<?=$adenda_id?>" proceso-detalle-id="0" nueva-etapa-id="<?=$REVISION_AREA_USUARIA?>"  class="w-100 btn-reinicio-proceso-legal btn btn-xs btn-primary">Revisión <br> Área Usuaria</button>
																</div>
																<div class="col-md-4"></div>
															</div>
															<?php 
														}
													}
												}else{
													echo "<span> No hay ningun seguimiento pendiente. </span>";
												}
											
												$query_seg_historial = "SELECT
													spl.id ,
													spl.tipo_documento_id ,
													csp.nombre as proceso,
													spl.proceso_id,
													spl.proceso_detalle_id ,
													spl.etapa_id,
													spl.area_id,
													spl.status,
													DATE_FORMAT(spl.created_at, '%Y-%m-%d %H:%i') as created_at,
													DATE_FORMAT(spl.updated_at, '%Y-%m-%d %H:%i') as updated_at,
													CONCAT(IFNULL(tpa.nombre, ''), ' ', IFNULL(tpa.apellido_paterno, ''), ' ', IFNULL(tpa.apellido_materno, '')) AS responsable,
													cesl.nombre as etapa,
													ta.nombre as area
												FROM
													cont_seguimiento_proceso_legal as spl
												INNER JOIN cont_seguimiento_tipo_documento csp ON
													csp.id = spl.tipo_documento_id
												INNER JOIN cont_etapa_seguimiento_legal cesl ON
													cesl.id = spl.etapa_id
												LEFT JOIN tbl_usuarios tu ON
													tu.id = spl.usuario_id
												LEFT JOIN tbl_personal_apt tpa ON
													tpa.id = tu.personal_id
												INNER JOIN tbl_areas ta ON
													ta.id = spl.area_id
												WHERE
													spl.tipo_documento_id = 2
													AND spl.status IN (1,2) AND spl.proceso_id = ". $adenda_id." ORDER BY spl.id ASC";
												$sel_query_historial = $mysqli->query($query_seg_historial);

												$row_seg_aden = $sel_query_historial->num_rows;
												if ($row_seg_aden > 0) {
													?>
													<br>
													<div class="row">
														<div class="w-100 text-right">
															<button type="button" div-toggle="div_adenda_historial_seguimiento_<?=$adenda_id?>" class="btn btn-xs btn-primary toggleButtonVerHistorialSeguimiento">Ver Historial</button>	
														</div>
													</div>

													<div id="div_adenda_historial_seguimiento_<?=$adenda_id?>" style="display: none;" class="row">

														<fieldset class="dhhBorder">
															<legend class="dhhBorder">Historial de Seguimiento de Proceso</legend>

															<div class="table-responsive">
																<table class="table table-bordered table-condensed" style="font-size: 8px;" cellspacing="0" width="100%">
																	<thead>
																		<tr>
																			<th class="text-center">N°</th>
																			<th class="text-center">Etapa</th>
																			<th class="text-center">Fecha <br> Inicio</th>
																			<th class="text-center">Fecha <br> Termino</th>
																			<th class="text-center">Área</th>
																			<th class="text-center">Responsable</th>
																			<?php
																			if (( $area_id == 33 && $cargo_id != 25) || (array_key_exists($menu_consultar,$usuario_permisos) && in_array("reenviar_seguimiento_proceso", $usuario_permisos[$menu_consultar]))) { 
																			?>
																			<th class="text-center"></th>
																			<?php 
																			}
																			?>
																		</tr>
																	</thead>
																	<tbody>
																		<?php 
																		$index = 1;
																		while($sel=$sel_query_historial->fetch_assoc()){
																		?>
																		<tr>
																			<td class="text-center"><?=$index?></td>
																			<td class="text-left"><?=$sel['etapa']?></td>
																			<td class="text-center"><?=$sel['created_at']?></td>
																			<td class="text-center"><?=$sel['updated_at']?></td>
																			<td class="text-left"><?=$sel['area']?></td>
																			<td class="text-left"><?=$sel['responsable']?></td>
																			<?php
																			if (( $area_id == 33 && $cargo_id != 25) || (array_key_exists($menu_consultar,$usuario_permisos) && in_array("reenviar_seguimiento_proceso", $usuario_permisos[$menu_consultar]))) { 
																			?>
																			<td class="text-center">
																				<a title="Reenviar Seguimiento de Proceso" class="btn btn-info btn-xs" onclick="sec_contrato_detalle_reenviar_notificacion_seguimiento_proceso(<?=$sel['id']?>,2);">
																					<span class="fa fa-envelope-o"></span>
																				</a>
																			</td>
																			<?php 
																			}
																			?>
																		</tr>
																		<?php 
																			$index++;
																		}
																		?>
																	</tbody>
																</table>
															</div>
														</fieldset>

													</div>
												
													<?php
												}
												?>
												<!-- Fin Seguimiento de Proceso -->


											</div>

											<br/>

											<?php
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



							<!-- PANEL: CAMBIOS QUE REQUIEREN LA APROBACION DEL DIRECTOR EN LA SOLICITUD INICIO -->
							<?php						
							$sel_query = $mysqli->query("
							SELECT 
								a.id,
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
								CONCAT(IFNULL(pa.nombre, ''), ' ', IFNULL(pa.apellido_paterno, ''), ' ', IFNULL(pa.apellido_materno, '')) AS director,
								CONCAT(IFNULL(pap.nombre, ''), ' ', IFNULL(pap.apellido_paterno, ''), ' ', IFNULL(pap.apellido_materno, '')) AS aprobar_por,
								case a.estado_aprobacion  
									when 1 then 'Pendiente'  
									when 2 then 'Aprobado'  
									when 3 then 'Rechazado'  
								end as nombre_estado_aprobacion,
								a.estado_aprobacion,
								ar.nombre AS area
							FROM cont_contrato_cambios a
							INNER JOIN tbl_usuarios u ON a.user_created_id = u.id
							INNER JOIN tbl_personal_apt p ON u.personal_id = p.id

							LEFT JOIN tbl_usuarios ua ON a.director_id = ua.id
							LEFT JOIN tbl_personal_apt pa ON ua.personal_id = pa.id

							LEFT JOIN tbl_usuarios uap ON a.aprobado_por = uap.id
							LEFT JOIN tbl_personal_apt pap ON uap.personal_id = pap.id

							INNER JOIN tbl_areas ar ON p.area_id = ar.id

							WHERE a.status = 1 AND contrato_id = ".$contrato_id."
							ORDER BY a.id DESC");
							$row_count = $sel_query->num_rows;
							if ($row_count > 0) {
							?>
							
							<div class="panel">
								<div class="panel-heading" role="tab" id="browsers-cambios-aprobacion-heading">
									<div class="panel-title">
										<a href="#browsers-cambios-aprobacion" class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" aria-expanded="true" aria-controls="browsers-cambios-aprobacion">
											Cambios (Requiere Aprobación)
										</a>
									</div>
								</div>

								<div id="browsers-cambios-aprobacion" class="panel-collapse collapse" role="tabpanel" aria-labelledby="browsers-cambios-aprobacion-heading">
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

											
												<tr style="text-transform: none;">
													<td><b>Director</b></td>
													<td>
														<?php echo $sel["director"]; ?>
													</td>
												</tr>

												<?php 
												if (!Empty($sel["aprobado_por"])) {
												?>
												<tr style="text-transform: none;">
													<td><b>Aprobado Por</b></td>
													<td>
														<?php echo $sel["aprobado_por"]; ?>
													</td>
												</tr>
												<?php
												}
												?>

												<tr style="text-transform: none;">
													<td><b>Estado Aprobación</b></td>
													<td><?php echo $sel["nombre_estado_aprobacion"]; ?></td>
												</tr>
											</tbody>
										</table>
										<br>

											<?php 
											$boton_aprobacion_gerencia = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' AND sub_sec_id = '$sub_sec_id' LIMIT 1")->fetch_assoc();
											$id_boton_aprobacion_gerencia = $boton_aprobacion_gerencia["id"];

											if(array_key_exists($id_boton_aprobacion_gerencia,$usuario_permisos) && in_array("validaBotonSolicitud", $usuario_permisos[$id_boton_aprobacion_gerencia]))
											{
												if ($sel['estado_aprobacion'] == 1) {
												?>
												<div class="row">
													<div class="col-md-6">
														<button type="button" onclick="sec_contrato_detalle_aprobar_rechazar_cambio(<?=$sel['id']?>,1)" class="btn btn-success btn-sm w-100">Aprobar</button>
													</div>
													<div class="col-md-6">
														<button type="button" onclick="sec_contrato_detalle_aprobar_rechazar_cambio(<?=$sel['id']?>,0)" class="btn btn-danger btn-sm w-100">Rechazar</button>
													</div>
												</div>
												<br>
												<?php 
												}
											}
										
											$row_count--;
										} 
										?>
									</div>
								</div>
							</div>
							<?php
							}
							?>
							<!-- PANEL: CAMBIOS QUE REQUIEREN LA APROBACION DEL DIRECTOR EN LA SOLICITUD FIN -->


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
										<div style="margin-right: 10px; margin-left: 5px;">
											<div id="div_observaciones" class="timeline" style="font-size: 11px; margin-top:-30px;">
											</div>

											<textarea rows="3" id="contrato_observaciones_proveedor" placeholder="Ingrese sus observaciones" style="width: 100%"></textarea>
											<b>Correos Adjuntos: (Opcional)</b>
											<textarea rows="3" id="correos_adjuntos" placeholder="Ingrese Correos Adjuntos" style="width: 100%"></textarea>
											<b>Nota: Para más de un correo se debe separar por comas (,)</b>
											<p></p>
											<button class="btn btn-success btn-xs btn-block" type="submit" onclick="sec_contrato_detalle_solicitud_guardar_observaciones_proveedores();">
												<i class="fa fa-plus"></i> Agregar y Notificar Observación
											</button>
										</div>
									</div>
								</div>
							</div>
							<!-- PANEL: OBSERVACIONES FIN -->


							<!-- INICIO PANEL ETAPA SEGUIMIENTO LEGAL -->
							<div class="panel">
								<div class="panel-heading" role="tab" id="browsers-this-etapa-seguimiento-heading">
									<div class="panel-title">
										<a href="#browsers-this-etapa-seguimiento" class="collapsed" role="button" data-toggle="collapse"
										data-parent="#accordion" aria-expanded="true" aria-controls="browsers-this-etapa-seguimiento">
											Seguimiento de Proceso
										</a>
									</div>
								</div>
								<div id="browsers-this-etapa-seguimiento" class="panel-collapse collapse" role="tabpanel" aria-labelledby="browsers-this-etapa-seguimiento-heading">
									<div class="panel-body">
										<?php
											$query_seg = "SELECT 
												spl.id ,
												spl.tipo_documento_id ,
												csp.nombre  as proceso,
												spl.proceso_id,
												spl.proceso_detalle_id ,
												spl.etapa_id,
												spl.area_id,
												spl.status, 
												DATE_FORMAT(spl.created_at, '%Y-%m-%d %H:%i') as created_at,
												cesl.nombre as etapa,
												ta.nombre as area
											FROM
												cont_seguimiento_proceso_legal as spl
											INNER JOIN cont_seguimiento_tipo_documento csp ON csp.id = spl.tipo_documento_id 
											INNER JOIN cont_etapa_seguimiento_legal cesl ON
												cesl.id = spl.etapa_id
											INNER JOIN tbl_areas ta ON
												ta.id = spl.area_id 
											WHERE
												spl.tipo_documento_id  = 1 AND spl.status = 1 AND spl.proceso_id = ". $contrato_id." ORDER BY spl.id ASC";
											$sel_query = $mysqli->query($query_seg);

											$INICIO_PROCESO_LEGAL = 2;
											$REVISION_AREA_USUARIA = 3;
											$REVISION_DEL_PROVEEDOR = 4;
											$REVISION_AREA_LEGAL = 5;
											$PASO_A_FIRMAS = 6;
											$CONFORMIDAD_AREA_USUARIA = 7;
											$NO_HAY_SEGUIMIENTO = 8;
											$OBSERVADO = 9;
											$AREA_LEGAL_ID = 33;

											while($sel=$sel_query->fetch_assoc()){
											?>
											<div class="" style="padding-top: 5px; border: 1px solid #cfd0d3; border-radius: 5px;">
												<div class="row">
													<div class="col-md-3 text-left">
														<p><b><?=$sel['area']?></b></p>
													</div>
													<div class="col-md-6 text-center">
														<p class="text-warning"><b><?=$sel['etapa']?></b></p>
													</div>
													<div class="col-md-3 text-right">
														<p class="muted"><?=$sel['created_at']?></p>
													</div>
												</div>
												<?php
												if ($sel['etapa_id'] == $REVISION_AREA_USUARIA && ($sel['area_id'] == $area_id)) {
													?>
													<div class="row" style="padding-bottom: 7px;">
														<div class="col-md-4">
															<button type="button" seguimiento-id="<?=$sel['id']?>" nueva-etapa-id="<?=$REVISION_DEL_PROVEEDOR?>" class="w-100 btn-atender-seguimiento-proceso btn btn-xs btn-warning">Revisión <br> Proveedor</button>
														</div>
														<div class="col-md-4"></div>
														<div class="col-md-4 text-center">
															<button type="button" seguimiento-id="<?=$sel['id']?>" nueva-etapa-id="<?=$REVISION_AREA_LEGAL?>" class="w-100 btn-atender-seguimiento-proceso btn btn-xs btn-primary">Revisión <br> Área Legal</button>
														</div>
													</div>
													<?php 
												}else if ($sel['etapa_id'] == $REVISION_DEL_PROVEEDOR && ($sel['area_id'] == $area_id)){ // aprobado
													?>
													<div class="row" style="padding-bottom: 7px;">
														<div class="col-md-4"></div>
														<div class="col-md-4 text-center">
															<button type="button" seguimiento-id="<?=$sel['id']?>" nueva-etapa-id="<?=$REVISION_AREA_LEGAL?>" class="w-100 btn-atender-seguimiento-proceso btn btn-xs btn-primary">Revisión <br> Área Legal</button>
														</div>
														<div class="col-md-4"></div>
													</div>
													<?php
												}else if ($sel['etapa_id'] == $REVISION_AREA_LEGAL && ($sel['area_id'] == $area_id)){ // aprobado
													?>
													<div class="row" style="padding-bottom: 7px;">
														<div class="col-md-4">
															<button type="button" seguimiento-id="<?=$sel['id']?>" nueva-etapa-id="<?=$REVISION_AREA_USUARIA?>" class="w-100 btn-atender-seguimiento-proceso btn btn-xs btn-warning">Revisión <br> Área Usuaria</button>
														</div>
														<div class="col-md-4"></div>
														<div class="col-md-4 text-center">
															<button type="button" seguimiento-id="<?=$sel['id']?>" nueva-etapa-id="<?=$PASO_A_FIRMAS?>" class="w-100 btn-atender-seguimiento-proceso btn btn-xs btn-primary">Paso a <br> Firmas</button>
														</div>
													</div>
													<?php
												}else if ($sel['etapa_id'] == $CONFORMIDAD_AREA_USUARIA && ($sel['area_id'] == $area_id)){ // aprobado
													?>
													<div class="row" style="padding-bottom: 7px;">
														
														<div class="col-md-4">
															<button type="button" seguimiento-id="<?=$sel['id']?>" nueva-etapa-id="<?=$NO_HAY_SEGUIMIENTO?>" class="w-100 btn-atender-seguimiento-proceso btn btn-xs btn-danger">No Hay <br> Seguimiento</button>
														</div>
														<div class="col-md-4">
															<button type="button" seguimiento-id="<?=$sel['id']?>" nueva-etapa-id="<?=$REVISION_AREA_LEGAL?>" class="w-100 btn-atender-seguimiento-proceso btn btn-xs btn-warning">Revisión <br> Área Legal</button>
														</div>
														<div class="col-md-4 text-center">
															<button type="button" seguimiento-id="<?=$sel['id']?>" nueva-etapa-id="<?=$CONFORMIDAD_AREA_USUARIA?>" class="w-100 btn-atender-seguimiento-proceso btn btn-xs btn-primary">Conformidad <br> Área Usuaria</button>
														</div>
													</div>
													<?php
												}
												?>

												
										
											</div>
											<?php 
											}

											// En caso que su ultimo movimiento sea observado legal tiene la opcion de reunadar el proceso
											$query_seg = "SELECT 
												spl.id ,
												spl.tipo_documento_id ,
												csp.nombre  as proceso,
												spl.proceso_id,
												spl.proceso_detalle_id ,
												spl.etapa_id,
												spl.area_id,
												spl.status, 
												DATE_FORMAT(spl.created_at, '%Y-%m-%d %H:%i') as created_at,
												cesl.nombre as etapa,
												ta.nombre as area
											FROM
												cont_seguimiento_proceso_legal as spl
											INNER JOIN cont_seguimiento_tipo_documento csp ON csp.id = spl.tipo_documento_id 
											INNER JOIN cont_etapa_seguimiento_legal cesl ON
												cesl.id = spl.etapa_id
											INNER JOIN tbl_areas ta ON
												ta.id = spl.area_id 
											WHERE
												spl.tipo_documento_id  = 1 AND spl.status IN (1,2) AND spl.proceso_id = ". $contrato_id." ORDER BY spl.id DESC LIMIT 1";
											$sel_query = $mysqli->query($query_seg);
											$sel_observado = $sel_query->fetch_assoc();
											

											if (isset($sel_observado['etapa_id']) && $sel_observado['etapa_id'] == $OBSERVADO && $area_id == $AREA_LEGAL_ID){
												?>
												<div class="row" style="padding-bottom: 7px;">
													<div class="col-md-4"></div>
													<div class="col-md-4 text-center">
														<button type="button" tipo-documento-id="1" proceso-id="<?=$contrato_id?>" proceso-detalle-id="0" nueva-etapa-id="<?=$REVISION_AREA_USUARIA?>"  class="w-100 btn-reinicio-proceso-legal btn btn-xs btn-primary">Revisión <br> Área Usuaria</button>
													</div>
													<div class="col-md-4"></div>
												</div>
												<?php 
											}
											?>

											<br>
				
											<?php
											$query_seg = "SELECT
												spl.id ,
												spl.tipo_documento_id ,
												csp.nombre as proceso,
												spl.proceso_id,
												spl.proceso_detalle_id ,
												spl.etapa_id,
												spl.area_id,
												spl.status,
												DATE_FORMAT(spl.created_at, '%Y-%m-%d %H:%i') as created_at,
												DATE_FORMAT(spl.updated_at, '%Y-%m-%d %H:%i') as updated_at,
												CONCAT(IFNULL(tpa.nombre, ''), ' ', IFNULL(tpa.apellido_paterno, ''), ' ', IFNULL(tpa.apellido_materno, '')) AS responsable,
												cesl.nombre as etapa,
												ta.nombre as area
											FROM
												cont_seguimiento_proceso_legal as spl
											INNER JOIN cont_seguimiento_tipo_documento csp ON
												csp.id = spl.tipo_documento_id
											INNER JOIN cont_etapa_seguimiento_legal cesl ON
												cesl.id = spl.etapa_id
											LEFT JOIN tbl_usuarios tu ON
												tu.id = spl.usuario_id
											LEFT JOIN tbl_personal_apt tpa ON
												tpa.id = tu.personal_id
											INNER JOIN tbl_areas ta ON
												ta.id = spl.area_id
											WHERE
												spl.tipo_documento_id = 1
												AND spl.status IN (1,2) AND spl.proceso_id = ". $contrato_id." ORDER BY spl.id ASC";
											$sel_query = $mysqli->query($query_seg);
											
											?>
											
												<div id="div_historial_seguimiento" style="display: block;" class="row">

													<fieldset class="dhhBorder">
														<legend class="dhhBorder">Historial de Seguimiento de Proceso</legend>

														<div class="table-responsive">
															<table class="table table-bordered table-condensed" style="font-size: 9px;" cellspacing="0" width="100%">
																<thead>
																	<tr>
																		<th class="text-center">N°</th>
																		<th class="text-center">Etapa</th>
																		<th class="text-center">Fecha <br> Inicio</th>
																		<th class="text-center">Fecha <br> Termino</th>
																		<th class="text-center">Área</th>
																		<th class="text-center">Responsable</th>
																		<?php
																		if (( $area_id == 33 && $cargo_id != 25) || (array_key_exists($menu_consultar,$usuario_permisos) && in_array("reenviar_seguimiento_proceso", $usuario_permisos[$menu_consultar]))) { 
																		?>
																		<th class="text-center"></th>
																		<?php 
																		}
																		?>
																	</tr>
																</thead>
																<tbody>
																	<?php 
																	$index = 1;
																	while($sel=$sel_query->fetch_assoc()){
																	?>
																	<tr>
																		<td class="text-center"><?=$index?></td>
																		<td class="text-left"><?=$sel['etapa']?></td>
																		<td class="text-center"><?=$sel['created_at']?></td>
																		<td class="text-center"><?=$sel['updated_at']?></td>
																		<td class="text-left"><?=$sel['area']?></td>
																		<td class="text-left"><?=$sel['responsable']?></td>
																		<?php
																		if (( $area_id == 33 && $cargo_id != 25) || (array_key_exists($menu_consultar,$usuario_permisos) && in_array("reenviar_seguimiento_proceso", $usuario_permisos[$menu_consultar]))) { 
																		?>
																		<td class="text-center">
																			<a title="Reenviar Seguimiento de Proceso" class="btn btn-info btn-xs" onclick="sec_contrato_detalle_reenviar_notificacion_seguimiento_proceso(<?=$sel['id']?>,2);">
																				<span class="fa fa-envelope-o"></span>
																			</a>
																		</td>
																		<?php 
																		}
																		?>
																	</tr>
																	<?php 
																		$index++;
																	}
																	?>
																</tbody>
															</table>
														</div>
													</fieldset>

												</div>
										
									</div>
								</div>
							</div>
							<!-- FIN PANEL ETAPA SEGUIMIENTO LEGAL -->


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
											$edicion_btn_contrato_firmado = false;
											if (($area_id == 33 &&  $cargo_id != 25 && $etapa_id == 5) || $permiso_editar_solicitud_contrato ) {
												$edicion_btn_contrato_firmado = true;
											}
											$sel_query_info_contrato_firmado_proveedor = $mysqli->query("
												SELECT
													c.contrato_id, c.tipo_contrato_id, tc.nombre AS tipo_contrato,
													c.empresa_suscribe_id, trs.nombre AS razon_social,
													tf.nombre AS tipo_firma, c.fecha_suscripcion_proveedor,
													c.fecha_vencimiento_indefinida_id,c.categoria_id, c.tipo_contrato_proveedor_id,
													c.fecha_inicio, c.fecha_vencimiento_proveedor,
													concat( IFNULL(tpa.nombre, ''),' ', IFNULL(tpa.apellido_paterno, ''),' ', IFNULL(tpa.apellido_materno, '')) AS usuario_aprobado,
													concat( IFNULL(tpc.nombre, ''),' ', IFNULL(tpc.apellido_paterno, ''),' ', IFNULL(tpc.apellido_materno, '')) AS usuario_creado,
													
													cs.nombre AS categoria_servicio, cts.nombre AS tipo_categoria_servicio,c.renovacion_automatica
													
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
	
											WHERE c.contrato_id = $contrato_id AND c.etapa_id = 5 AND c.status = 1
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
													
													$fecha_vencimiento_indefinida = $fecha_vencimiento_indefinida_id == 1 ? 'Indefinida':'Definida';
													if ($fecha_vencimiento_indefinida_id == 1) {
														$fecha_vencimiento_proveedor = 'Indefinida';
													} else {
														$fecha_vencimiento_proveedor = date("d-m-Y", strtotime($sel["fecha_vencimiento_proveedor"]));
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
															<td><b>Razón social:</b></td>
															<td colspan="2">
																<?php echo $razon_social; ?>
															</td>
														</tr>
														<tr style="text-transform: none;">
															<td><b>Contrato aprobado por:</b></td>
															<td colspan="2">
																<?php echo $usuario_aprobado; ?>
															</td>
														</tr>
														<tr style="text-transform: none;">
															<td><b>Contrato creado por:</b></td>
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
																<?php if ($edicion_btn_contrato_firmado) { ?>
																<a class="btn btn-success btn-xs" 
																onclick="sec_contrato_detalle_solicitud_editar_solicitud('Contrato Firmado','cont_contrato','categoria_id','Categoria de Contrato','select_option','<?php echo $categoria_servicio; ?>','obtener_categoria_contrato','');">
																	<span class="fa fa-edit"></span> Editar
																</a>
																<?php } ?>
															</td>
														</tr>
														<tr style="text-transform: none;">
															<td><b>Tipo Categoria:</b></td>
															<td>
																<?php echo $tipo_categoria_servicio; ?>
															</td>
															<td>
																<?php if ($edicion_btn_contrato_firmado) { ?>
																<a class="btn btn-success btn-xs" 
																onclick="sec_contrato_detalle_solicitud_editar_solicitud_param('Contrato Firmado','cont_contrato','tipo_contrato_proveedor_id','Tipo Categoria','select_option','<?php echo $tipo_categoria_servicio; ?>','obtener_tipo_categoria_contrato','','<?=$categoria_id;?>');">
																	<span class="fa fa-edit"></span> Editar
																</a>
																<?php } ?>
															</td>
														</tr>
														<tr style="text-transform: none;">
															<td><b>Tipo Firma:</b></td>
															<td>
																<?php echo $tipo_firma; ?>
															</td>
															<td>
																<?php if ($edicion_btn_contrato_firmado) { ?>
																<a class="btn btn-success btn-xs" 
																onclick="sec_contrato_detalle_solicitud_editar_solicitud('Contrato Firmado','cont_contrato','tipo_firma_id','Tipo de Firma','select_option','<?php echo $tipo_firma; ?>','obtener_tipo_firma','');">
																	<span class="fa fa-edit"></span> Editar
																</a>
																<?php } ?>
															</td>
														</tr>
														<tr style="text-transform: none;">
															<td><b>Fecha Inicio:</b></td>
															<td>
																<?php echo $fecha_inicio; ?>
															</td>
															<td>
																<?php if ($edicion_btn_contrato_firmado) { ?>
																<a class="btn btn-success btn-xs" 
																onclick="sec_contrato_detalle_solicitud_editar_solicitud('Contrato Firmado','cont_contrato','fecha_inicio','Fecha Inicio','date','<?php echo $fecha_inicio; ?>','','');">
																	<span class="fa fa-edit"></span> Editar
																</a>
																<?php } ?>
															</td>
														</tr>
														<tr style="text-transform: none;">
															<td><b>Fecha Suscripción:</b></td>
															<td>
																<?php echo $fecha_suscripcion_proveedor; ?>
															</td>
															<td>
																<?php if ($edicion_btn_contrato_firmado) { ?>
																<a class="btn btn-success btn-xs" 
																onclick="sec_contrato_detalle_solicitud_editar_solicitud('Contrato Firmado','cont_contrato','fecha_suscripcion_proveedor','Fecha Suscripción','date','<?php echo $fecha_suscripcion_proveedor; ?>','','');">
																	<span class="fa fa-edit"></span> Editar
																</a>
																<?php } ?>
															</td>
														</tr>
														<tr style="text-transform: none;">
															<td><b>Tipo Fecha Vencimiento:</b></td>
															<td>
																<?php echo $fecha_vencimiento_indefinida ?>
															</td>
															<td>
																<?php if ($edicion_btn_contrato_firmado) { ?>
																<a class="btn btn-success btn-xs" 
																onclick="sec_contrato_detalle_solicitud_editar_solicitud('Contrato Firmado','cont_contrato','fecha_vencimiento_indefinida_id','Tipo Fecha Vencimiento','select_option','<?php echo $fecha_vencimiento_indefinida; ?>','obtener_tipo_fecha_vencimiento','');">
																	<span class="fa fa-edit"></span> Editar
																</a>
																<?php } ?>
															</td>
														</tr>

														<?php if ($fecha_vencimiento_indefinida_id != 1) { ?>
														<tr style="text-transform: none;">
															<td><b>Fecha Vencimiento:</b></td>
															<td>
																<?php echo $fecha_vencimiento_proveedor; ?>
															</td>
															<td>
																<?php if ($edicion_btn_contrato_firmado) { ?>
																<a class="btn btn-success btn-xs" 
																onclick="sec_contrato_detalle_solicitud_editar_solicitud('Contrato Firmado','cont_contrato','fecha_vencimiento_proveedor','Fecha Vencimiento','date','<?php echo $fecha_vencimiento_proveedor; ?>','','');">
																	<span class="fa fa-edit"></span> Editar
																</a>
																<?php } ?>
															</td>
														</tr>
														<?php } ?>

														<tr style="text-transform: none;">
															<td><b>Renovación Automática:</b></td>
															<td>
																<?php echo $renovacion_automatica_value; ?>
															</td>
															<td>
																<?php if ($edicion_btn_contrato_firmado) { ?>
																<a class="btn btn-success btn-xs" 
																onclick="sec_contrato_detalle_solicitud_editar_solicitud('Contrato Firmado','cont_contrato','renovacion_automatica','Renovación Automática','select_option','<?php echo $renovacion_automatica_value; ?>','obtener_si_no','');">
																	<span class="fa fa-edit"></span> Editar
																</a>
																<?php } ?>
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
															<td colspan="2">
																<a
																onclick="sec_contrato_detalle_solicitud_ver_documento_en_visor('<?php echo $ruta;?>','<?php echo trim($row["nombre"]); ?>','<?php echo trim($row["extension"]); ?>','CONTRATO FIRMADO');"
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
													Tipo Categoria:
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
																	type="hidden"
																	id="cont_detalle_proveedor_contrato_firmado_fecha_inicio_param_hidden"
																	class="input_text filtro"
																	data-col="fecha_inicio"
																	name="fecha_inicio"
																	value="<?php echo date("d-m-Y", strtotime("+1 days")); ?>"
																	data-real-date="cont_detalle_proveedor_contrato_firmado_fecha_incio_param">
																<input
																	type="text"
																	class="form-control fecha_detalle_proveedor_datepicker"
																	id="cont_detalle_proveedor_contrato_firmado_fecha_incio_param"
																	value="<?php echo date("d-m-Y", strtotime($fecha_inicio)); ?>"
																	readonly="readonly"
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
																	type="hidden"
																	id="cont_detalle_proveedor_contrato_firmado_fecha_suscripcion_param_hidden"
																	class="input_text filtro"
																	data-col="fecha_inicio"
																	name="fecha_inicio"
																	value="<?php echo date("Y-m-d", strtotime("+1 days")); ?>"
																	data-real-date="cont_detalle_proveedor_contrato_firmado_fecha_suscripcion_param">
																<input
																	type="text"
																	class="form-control fecha_detalle_proveedor_datepicker"
																	id="cont_detalle_proveedor_contrato_firmado_fecha_suscripcion_param"
																	value="<?php echo date("d-m-Y");?>"
																	readonly="readonly"
																	>
																<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="cont_detalle_proveedor_contrato_firmado_fecha_suscripcion_param"></label>
															</div>
														</div>
													</div>

													<div class="col-xs-12 col-md-6 col-lg-6 item_filter">
														<div class="form-group">
															<label>
																Fecha Vencimiento:
															</label>
															<div class="input-group col-xs-12">
																<select 
																	class="form-control select2" 
																	id="fecha_vencimiento_indefinida_id" 
																	name="fecha_vencimiento_indefinida_id" 
																	style="height: 34px;">
																	<option value="0"> - Seleccione - </option>
																	<option value="1">Indefinida</option>
																	<option value="2">Definida</option>
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
																	type="hidden"
																	id="cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param_hidden"
																	class="input_text filtro"
																	data-col="fecha_inicio"
																	name="fecha_inicio"
																	value="<?php echo date("Y-m-d", strtotime("+1 days")); ?>"
																	data-real-date="cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param">
																<input
																	type="text"
																	class="form-control fecha_detalle_proveedor_datepicker"
																	id="cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param"
																	value="<?php echo date("d-m-Y");?>"
																	readonly="readonly"
																	>
																<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param"></label>
															</div>
														</div>
													</div>

													<div class="col-xs-12 col-md-6 col-lg-6" style="padding: 5px">
															<div class="form-group">
																<div class="control-label">Seleccione el contrato firmado:</div>
																<input type="file" class="form-control" id="archivo_contrato_proveedor" name="archivo_contrato_proveedor" required accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip">
															</div>
													</div>

													<div class="col-xs-12 col-md-6 col-lg-6" style="padding: 5px">
														<div class="form-group">
															<label>Renovación Automática:</label>
															<select class="form-control select2" id="cont_detalle_proveedor_renovacion_automatica" name="cont_detalle_proveedor_renovacion_automatica">
																<option value="0">- Seleccione -</option>
																<option value="1">SI</option>
																<option value="2">NO</option>
															</select>
														</div>
													</div>

													<div class="col-xs-12 col-md-12 col-lg-12" style="margin-bottom: 30px">
														<div style="margin-right: 10px; margin-left: 5px">
															<div class="form-group">
																<div class="control-label"><b>Correos Adjuntos: (Opcional)</b></div>
																<textarea name="correos_adjuntos" rows="2" style="width:100%"></textarea>
																<b>Nota: Para más de un correo se debe separar por comas (,)</b>
															</div>
														</div>
													</div>														
													<br>
													<br>
													<div style="margin-right: 10px; margin-left: 5px;">
														<button type="button" class="btn btn-success btn-xs btn-block" id="btn_guardar_contrato_firmado" onclick="guardar_contrato_firmado_proveedor()">
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
										CONCAT(IFNULL(tpa.nombre, ''), ' ', IFNULL(tpa.apellido_paterno, ''), ' ', IFNULL(tpa.apellido_materno, '')) AS usuario_solicitud,
										r.anexo_archivo_id,
										r.archivo_id,
										CONCAT(IFNULL(tpa2.nombre, ''), ' ', IFNULL(tpa2.apellido_paterno, ''), ' ', IFNULL(tpa2.apellido_materno, '')) AS usuario_aprobado,
										r.fecha_resolucion_contrato_aprobado,
										r.status,
										DATE_FORMAT(r.created_at,'%d-%m-%Y %H:%i:%s') AS created_at,
										r.cancelado_id,
										CONCAT(IFNULL(tpa3.nombre, ''), ' ', IFNULL(tpa3.apellido_paterno, ''), ' ', IFNULL(tpa3.apellido_materno, '')) AS cancelado_por,
										CONCAT(IFNULL(pab.nombre, ''), ' ', IFNULL(pab.apellido_paterno, ''), ' ', IFNULL(pab.apellido_materno, '')) AS abogado,
										DATE_FORMAT(r.cancelado_el,'%d-%m-%Y %H:%i:%s') AS cancelado_el,
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

										LEFT JOIN tbl_usuarios uab ON r.abogado_id = uab.id
										LEFT JOIN tbl_personal_apt pab ON uab.personal_id = pab.id

										LEFT JOIN tbl_usuarios tu4 ON r.aprobado_por = tu4.id
										LEFT JOIN tbl_personal_apt tpa4 ON tu4.personal_id = tpa4.id
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
											$abogado = $sel['abogado'];

											$estado_aprobacion_gerencia = $sel['estado_aprobacion_gerencia'];
											$estado_aprobacion = $sel['estado_aprobacion'];
											$aprobado_por = $sel['aprobado_por'];
											$fecha_aprobacion_gerencia = $sel['fecha_aprobacion_gerencia'];
											

											?>
										
											<?php if ((array_key_exists($menu_consultar,$usuario_permisos) && in_array("reenviar_resolucion_firmada", $usuario_permisos[$menu_consultar]))  && ($estado_solicitud_id == 2 )) { ?>
											<button type="button" onclick="sec_contrato_detalle_reenviar_resolucion(<?=$sel['id']?>,'2')" style="float: right" class="btn btn-xs btn-info"><span class="fa fa-envelope-o"></span> Reenviar por email</button>
											<br>
											<?php } ?>
										 


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
														created_at
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
															created_at
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
																<button onclick="sec_contrato_detalle_resolucion_cambiar_estado_legal(<?=$sel['id']?>,2)" class="btn btn-success form-control" type="button"><span class="glyphicon glyphicon-floppy-disk"></span> Guardar Estado</button>
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
											?>
											
											<!-- Inicio Seguimiento de Proceso -->
											<hr>
											<p><b>Seguimiento de Proceso:</b></p>
											<?php
												$query_seg = "SELECT 
													spl.id ,
													spl.tipo_documento_id ,
													csp.nombre  as proceso,
													spl.proceso_id,
													spl.proceso_detalle_id ,
													spl.etapa_id,
													spl.area_id,
													spl.status, 
													DATE_FORMAT(spl.created_at, '%Y-%m-%d %H:%i') as created_at,
													cesl.nombre as etapa,
													ta.nombre as area
												FROM
													cont_seguimiento_proceso_legal as spl
												INNER JOIN cont_seguimiento_tipo_documento csp ON csp.id = spl.tipo_documento_id 
												INNER JOIN cont_etapa_seguimiento_legal cesl ON
													cesl.id = spl.etapa_id
												INNER JOIN tbl_areas ta ON
													ta.id = spl.area_id 
												WHERE
													spl.tipo_documento_id  = 3 AND spl.status = 1 AND spl.proceso_id = ". $resolucion_id." ORDER BY spl.id ASC";
												$sel_spl_query = $mysqli->query($query_seg);
												$cant_seguimientos_rc = $sel_spl_query->num_rows;
											?>
											<?php
											if ($cant_seguimientos_rc > 0) {
												
												$INICIO_PROCESO_LEGAL = 2;
												$REVISION_AREA_USUARIA = 3;
												$REVISION_DEL_PROVEEDOR = 4;
												$REVISION_AREA_LEGAL = 5;
												$PASO_A_FIRMAS = 6;
												$CONFORMIDAD_AREA_USUARIA = 7;
												$NO_HAY_SEGUIMIENTO = 8;
												$OBSERVADO = 9;
												$AREA_LEGAL_ID = 33;

												if ($archivo_id == 0 && $cancelado_id != 1 && $estado_aprobacion_gerencia == 1) {
													while($sel_spl=$sel_spl_query->fetch_assoc()){
													?>
													<div class="" style="padding-top: 5px; border: 1px solid #cfd0d3; border-radius: 5px; font-size:10px;">
														<div class="row">
															<div class="col-md-3 text-left">
																<p><b><?=$sel_spl['area']?></b></p>
															</div>
															<div class="col-md-6 text-center">
																<p class="text-warning"><b><?=$sel_spl['etapa']?></b></p>
															</div>
															<div class="col-md-3 text-right">
																<p class="muted"><?=$sel_spl['created_at']?></p>
															</div>
														</div>
														<?php
														if ($sel_spl['etapa_id'] == $REVISION_AREA_USUARIA && ($sel_spl['area_id'] == $area_id)) {
															?>
															<div class="row" style="padding-bottom: 7px;">
																<div class="col-md-4">
																	<button type="button" seguimiento-id="<?=$sel_spl['id']?>" nueva-etapa-id="<?=$REVISION_DEL_PROVEEDOR?>" class="w-100 btn-atender-seguimiento-proceso btn btn-xs btn-warning">Revisión <br> Proveedor</button>
																</div>
																<div class="col-md-4"></div>
																<div class="col-md-4 text-center">
																	<button type="button" seguimiento-id="<?=$sel_spl['id']?>" nueva-etapa-id="<?=$REVISION_AREA_LEGAL?>" class="w-100 btn-atender-seguimiento-proceso btn btn-xs btn-primary">Revisión <br> Área Legal</button>
																</div>
															</div>
															<?php 
														}else if ($sel_spl['etapa_id'] == $REVISION_DEL_PROVEEDOR && ($sel_spl['area_id'] == $area_id)){
															?>
															<div class="row" style="padding-bottom: 7px;">
																<div class="col-md-4"></div>
																<div class="col-md-4 text-center">
																	<button type="button" seguimiento-id="<?=$sel_spl['id']?>" nueva-etapa-id="<?=$REVISION_AREA_LEGAL?>" class="w-100 btn-atender-seguimiento-proceso btn btn-xs btn-primary">Revisión <br> Área Legal</button>
																</div>
																<div class="col-md-4"></div>
															</div>
															<?php
														}else if ($sel_spl['etapa_id'] == $REVISION_AREA_LEGAL && ($sel_spl['area_id'] == $area_id)){
															?>
															<div class="row" style="padding-bottom: 7px;">
																<div class="col-md-4">
																	<button type="button" seguimiento-id="<?=$sel_spl['id']?>" nueva-etapa-id="<?=$REVISION_AREA_USUARIA?>" class="w-100 btn-atender-seguimiento-proceso btn btn-xs btn-warning">Revisión <br> Área Usuaria</button>
																</div>
																<div class="col-md-4"></div>
																<div class="col-md-4 text-center">
																	<button type="button" seguimiento-id="<?=$sel_spl['id']?>" nueva-etapa-id="<?=$PASO_A_FIRMAS?>" class="w-100 btn-atender-seguimiento-proceso btn btn-xs btn-primary">Paso a <br> Firmas</button>
																</div>
															</div>
															<?php
														}else if ($sel_spl['etapa_id'] == $CONFORMIDAD_AREA_USUARIA && ($sel_spl['area_id'] == $area_id)){
															?>
															<div class="row" style="padding-bottom: 7px;">
																
																<div class="col-md-4">
																	<button type="button" seguimiento-id="<?=$sel_spl['id']?>" nueva-etapa-id="<?=$NO_HAY_SEGUIMIENTO?>" class="w-100 btn-atender-seguimiento-proceso btn btn-xs btn-danger">No Hay <br> Seguimiento</button>
																</div>
																<div class="col-md-4">
																	<button type="button" seguimiento-id="<?=$sel_spl['id']?>" nueva-etapa-id="<?=$REVISION_AREA_LEGAL?>" class="w-100 btn-atender-seguimiento-proceso btn btn-xs btn-warning">Revisión <br> Área Legal</button>
																</div>
																<div class="col-md-4 text-center">
																	<button type="button" seguimiento-id="<?=$sel_spl['id']?>" nueva-etapa-id="<?=$CONFORMIDAD_AREA_USUARIA?>" class="w-100 btn-atender-seguimiento-proceso btn btn-xs btn-primary">Conformidad <br> Área Usuaria</button>
																</div>
															</div>
															<?php
														}
														?>	

													</div>
													<?php 
													}
												}
											}else{
												echo "<span> No hay ningun seguimiento pendiente. </span>";
											}
											?>

											<?php
												$query_seg_historial = "SELECT
													spl.id ,
													spl.tipo_documento_id ,
													csp.nombre as proceso,
													spl.proceso_id,
													spl.proceso_detalle_id ,
													spl.etapa_id,
													spl.area_id,
													spl.status,
													DATE_FORMAT(spl.created_at, '%Y-%m-%d %H:%i') as created_at,
													DATE_FORMAT(spl.updated_at, '%Y-%m-%d %H:%i') as updated_at,
													CONCAT(IFNULL(tpa.nombre, ''), ' ', IFNULL(tpa.apellido_paterno, ''), ' ', IFNULL(tpa.apellido_materno, '')) AS responsable,
													cesl.nombre as etapa,
													ta.nombre as area
												FROM
													cont_seguimiento_proceso_legal as spl
												INNER JOIN cont_seguimiento_tipo_documento csp ON
													csp.id = spl.tipo_documento_id
												INNER JOIN cont_etapa_seguimiento_legal cesl ON
													cesl.id = spl.etapa_id
												LEFT JOIN tbl_usuarios tu ON
													tu.id = spl.usuario_id
												LEFT JOIN tbl_personal_apt tpa ON
													tpa.id = tu.personal_id
												INNER JOIN tbl_areas ta ON
													ta.id = spl.area_id
												WHERE
													spl.tipo_documento_id = 3
													AND spl.status IN (1,2) AND spl.proceso_id = ". $resolucion_id." ORDER BY spl.id ASC";
												$sel_query_historial = $mysqli->query($query_seg_historial);

												$row_seg_res = $sel_query_historial->num_rows;
												if ($row_seg_res > 0) {
													?>
													<br>
													<div class="row">
														<div class="w-100 text-right">
															<button type="button" div-toggle="div_resolucion_historial_seguimiento_<?=$resolucion_id?>" class="btn btn-xs btn-primary toggleButtonVerHistorialSeguimiento">Ver Historial</button>	
														</div>
													</div>

													<div id="div_resolucion_historial_seguimiento_<?=$resolucion_id?>" style="display: none;" class="row">

														<fieldset class="dhhBorder">
															<legend class="dhhBorder">Historial de Seguimiento de Proceso</legend>

															<div class="table-responsive">
																<table class="table table-bordered table-condensed" style="font-size: 8px;" cellspacing="0" width="100%">
																	<thead>
																		<tr>
																			<th class="text-center">N°</th>
																			<th class="text-center">Etapa</th>
																			<th class="text-center">Fecha <br> Inicio</th>
																			<th class="text-center">Fecha <br> Termino</th>
																			<th class="text-center">Área</th>
																			<th class="text-center">Responsable</th>
																			<?php
																			if (( $area_id == 33 && $cargo_id != 25) || (array_key_exists($menu_consultar,$usuario_permisos) && in_array("reenviar_seguimiento_proceso", $usuario_permisos[$menu_consultar]))) { 
																			?>
																			<th class="text-center"></th>
																			<?php 
																			}
																			?>
																		</tr>
																	</thead>
																	<tbody>
																		<?php 
																		$index = 1;
																		while($sel=$sel_query_historial->fetch_assoc()){
																		?>
																		<tr>
																			<td class="text-center"><?=$index?></td>
																			<td class="text-left"><?=$sel['etapa']?></td>
																			<td class="text-center"><?=$sel['created_at']?></td>
																			<td class="text-center"><?=$sel['updated_at']?></td>
																			<td class="text-left"><?=$sel['area']?></td>
																			<td class="text-left"><?=$sel['responsable']?></td>
																			<?php
																			if (( $area_id == 33 && $cargo_id != 25) || (array_key_exists($menu_consultar,$usuario_permisos) && in_array("reenviar_seguimiento_proceso", $usuario_permisos[$menu_consultar]))) { 
																			?>
																			<td class="text-center">
																				<a title="Reenviar Seguimiento de Proceso" class="btn btn-info btn-xs" onclick="sec_contrato_detalle_reenviar_notificacion_seguimiento_proceso(<?=$sel['id']?>,2);">
																					<span class="fa fa-envelope-o"></span>
																				</a>
																			</td>
																			<?php 
																			}
																			?>
																		</tr>
																		<?php 
																			$index++;
																		}
																		?>
																	</tbody>
																</table>
															</div>
														</fieldset>

													</div>
												
													<?php
												}
												?>
											<!-- Fin Seguimiento de Proceso -->
											<?php
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
												<button onclick="sec_contrato_detalle_solicitud_guardar_estado_solicitud()" class="btn btn-success form-control" type="button"><span class="glyphicon glyphicon-floppy-disk"></span> Guardar</button>
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
											<?php if ($btn_editar_solicitud || ($etapa_id == 1 && $area_id == 33 && $cargo_id != 25)) { ?>
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
				if($check_gerencia_proveedor == 1 && $etapa_id == 1 && $cancelado_id != 1)
				{
					if(is_null($fecha_atencion_gerencia_proveedor))
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
																<textarea rows="3" id="contrato_observaciones_proveedor_gerencia" placeholder="Ingrese sus observaciones" style="width: 100%"></textarea>																
																<button class="btn btn-warning btn-xs btn-block" type="submit" onclick="sec_contrato_detalle_solicitud_guardar_observaciones_proveedores_gerencia();">
																	<i class="fa fa-plus"></i> Enviar Observación
																</button>
															</div>

															<br>	

															<form id="form_contrato_proveedor_aprobar_gerencia" name="form_contrato_proveedor_firmado" method="POST" enctype="multipart/form-data" autocomplete="off">
																
																<div class="col-xs-12 col-md-6 col-lg-6 item_filter">
																	<button type="button" class="btn btn-success btn-xs btn-block col-md-6 cont_detalleSolicitudProveedor_btn_guardar_aprobar_gerencia" value="1" style="height: 30px;">
																		<span id="demo-button-text">
																			<i class="glyphicon glyphicon-saved"></i>
																			Aceptar solicitud
																		</span>
																	</button>
																</div>

																<div class="col-xs-12 col-md-6 col-lg-6 item_filter">
																	<button type="button" class="btn btn-danger btn-xs btn-block cont_detalleSolicitudProveedor_btn_guardar_aprobar_gerencia" value="0" style="height: 30px;">
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


<!-- INICIO MODAL EDITAR SOLICITUD -->
<div id="modal_editar_solicitud" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Editar Solicitud de Proveedor</h4>
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
												<textarea id="editar_solicitud_valor_textarea" class="form-control"  rows="5"></textarea>
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
				<button type="button" class="btn btn-success" onclick="sec_contrato_detalle_solicitud_editar_campo_solicitud('modal_editar_solicitud');">
					<i class="icon fa fa-edit"></i>
					<span id="demo-button-text">Editar</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL EDITAR SOLICITUD -->


<!-- INICIO MODAL SUBIR ARCHIVOS - REQUISITOS DE LA SOLICITUD DE PROVEEDOR -->
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
					<button type="button" class="btn btn-danger" onclick="cerrar_moda_subir_archivo_req_solicitud_arrendamiento();">Cerrar</button>
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
				<button type="button" class="btn btn-success" id="sec_nuevo_modal_guardar_archivo" onclick="sec_det_modal_guardar_nuevo_anexo_con_prov()">
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
				<button type="button" class="btn btn-success" id="sec_nuevo_modal_guardar_archivo" onclick="guardarNuevoTipoAnexoConProv(2)">
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
				<h4 class="modal-title">Agregar Nuevo Representante Legal</h4>
			</div>
			<div class="modal-body">
				<form id="sec_con_nuevo_agregar_nuevo_representante_legal_form" name="sec_con_nuevo_agregar_nuevo_representante_legal_form" enctype="multipart/form-data" autocomplete="off">
					<div class="row">
						<!--DNI REPRESENTANTE LEGAL-->
						<div class="col-xs-12 col-md-4 col-lg-4">
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
						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Nombre Completo del Representante Legal :</div>
								<input type="text" name="sec_con_det_nombre_representante" id="sec_con_det_nombre_representante" maxlength=50 class="filtro"
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
									name="sec_con_det_sec_con_nuevo_prov_banco" id="sec_con_det_sec_con_nuevo_prov_banco" title="Seleccione el banco">
									<?php 
									$banco_query = $mysqli->query("SELECT id, ifnull(nombre, '') nombre_banco
																FROM tbl_bancos
																WHERE estado = 1");
									while($row=$banco_query->fetch_assoc()){
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
								<input type="text" id="sec_con_det_sec_con_nuev_prov_nro_cuenta" name="sec_con_det_sec_con_nuev_prov_nro_cuenta" maxlength="50"
								 style="width: 100%; height: 30px;" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
							</div>
						</div>

						<!--NRO CCI-->
						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Nro CCI : </div>
								<input type="text" id="sec_con_det_sec_con_nuev_prov_nro_cci" name="sec_con_det_sec_con_nuev_prov_nro_cci" maxlength="50"
								 style="width: 100%; height: 30px;" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
							</div>
						</div>
						<div class="col-xs-12 col-md-6 col-lg-6">
							<div class="form-group">
								<div class="control-label">
									Vigencia
								</div>
								<input type="file" name="sec_con_det_prov_file_vigencia_nuevo_rl" accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip">
							</div>
						</div>
						<div class="col-xs-12 col-md-6 col-lg-6">
							<div class="form-group">
								<div class="control-label">
									DNI
								</div>
								<input type="file" name="sec_con_det_prov_file_dni_nuevo_rl" accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip">
							</div>
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
				<button type="button" class="btn btn-success" onclick="sec_con_det_prov_guardar_nuevo_representante_legal()">
					<i class="icon fa fa-save"></i>
					<span>Guardar Nuevo Representante Legal</span>
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
									</select>
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
									<div class="control-label">IGV (18%):</div>
									<select 
										name="tipo_igv_id" 
										id="tipo_igv_id" 
										class="form-control select2" 
										id="select-default" 
										style="width: 100%;">
										<option value="0">Seleccione</option>
										<option value="1">SI</option>
										<option value="2">No</option>
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
									<div class="control-label" id="label_igv">Monto del IGV :</div>
									<input type="text" name="igv" id="igv" class="filtro" style="width: 100%; height: 28px;">
								</div>
							</div>						

							<div class="col-xs-12 col-md-4 col-lg-4" style="display: none;">
								<div class="form-group">
									<div class="control-label">Forma de Pago <span class="campo_obligatorio_v2">(*)</span>:</div>
									<select class="form-control select2" name="forma_pago" id="forma_pago" style="width: 100%;">
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

							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="form-group">
									<div class="control-label">Forma de Pago<span class="campo_obligatorio_v2">(*)</span>:</div>
									<textarea name="forma_pago_detallado" id="forma_pago_detallado" rows="3" style="width: 100%;" placeholder="Ingrese  el detalle de la forma de pago"></textarea>
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
					onclick="sec_con_det_prov_guardar_contraprestacion()">
					<i class="icon fa fa-save"></i>
					<span>Guardar Nueva Contraprestación</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL AGREGAR CONTRAPRESTACION -->

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
				<h4 class="modal-title">Cancelar Solicitud de Proveedor</h4>
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
					<span>Cancelar Solicitud de Proveedor</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL CANCELAR SOLICITUD -->


<!-- INICIO MODAL AGREGAR OBJETO DE ADENDA -->
<div id="modal_objeto_adenda" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Objeto de Adenda de Proveedor</h4>
			</div>
			<div class="modal-body">
				<form id="form_modal_objeto_adenda" name="form_modal_objeto_adenda" enctype="multipart/form-data" autocomplete="off">
					<div class="row">

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="form-group">
								<div class="control-label">Objeto de la Adenda <span class="campo_obligatorio_v2">(*)</span>:</div>
								<textarea name="modal_objeto_de_adenda" id="modal_objeto_de_adenda" class="filtro" rows="10" style="width: 100%;"></textarea>
								<input type="hidden" class="form-control" id="modal_objeto_contrato_detalle_id">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="form-group">
								<div class="control-label">Archivo Adjunto:</div>
								<input class="form-control" id="modal_archivo_objeto_adenda" name="modal_archivo_objeto_adenda" type="file">
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
					Cancelar
				</button>
				<button 
					type="button" 
					class="btn btn-success" 
					onclick="sec_contrato_detalle_solicitud_modal_guardar_objeto_adenda()">
					<i class="icon fa fa-save"></i>
					<span>Guardar</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL AGREGAR OBJETO DE ADENDA -->


<!-- INICIO MODAL NUEVO DOCUMENTO ADENDA -->
<div id="modalAgregarDocumentoAdenda" class="modal fade" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Documento - Nuevo Adenda</h4>
			</div>
			<form id="formArchivosModal_documento_adenda" method="POST" enctype="multipart/form-data">
				<div class="modal-body">
						<div class="row">
							<div class="col-lg-12">

								<div class="form-group col-md-12">
									<label for="fileArchivo_adenda">Nombre file:</label>
									<div class="input-container">

										<input type="file" id="fileArchivo_adenda" name ="fileArchivo_adenda" multiple="multiple" accept='.jpeg, .jpg, .png, .pdf'>

										<button class="browse-btn" id="btnBuscarFile_req_solicitud_adenda">
											Seleccionar
										</button>

										<span class="file-info" id="txtFile_req_solicitud_adenda"></span>
									</div>
								</div>

								<div class="form-group col-md-12" id="divMensaje_modal_documento_adenda">
								</div>
							</div>
						</div>
				</div>
				<div class="modal-footer">
					<button type="button" onclick="modal_documento_subir_adenda();" class="btn btn-success"><i class="fa fa-upload"></i> Enviar</button>
					<button type="button" data-dismiss="modal" class="btn btn-danger">Cerrar</button>
				</div>
			</form>
		</div>
	</div>
</div>

<?php } else {
	echo "No tiene permisos para ver este recurso.";
} ?>