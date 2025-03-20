<?php
$this_menu = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' LIMIT 1")->fetch_assoc();
$menu_id = $this_menu["id"];

$menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' AND sub_sec_id = '$sub_sec_id' LIMIT 1")->fetch_assoc();
$menu_consultar = $menu_id_consultar["id"];

if(!array_key_exists($menu_id,$usuario_permisos)){
	echo "No tienes permisos para este recurso.";
	die();
}

$solicitud_id = $_GET["id"];
$continuar = false;

$usuario_id = $login?$login['id']:null;
$area_id = $login ? $login['area_id'] : 0;
$cargo_id = $login ? $login['cargo_id'] : 0;
$btn_editar_solicitud = false;

include '/var/www/html/sys/function_replace_invalid_caracters_contratos.php';

//QUERY DE CONSULTA
$query_solicitud_detalle = "SELECT r.solicitud_id,r.area_id,r.producto_id,r.tipo_solicitud_id,r.numero,r.objetivo,r.bullet_1,r.bullet_2,r.bullet_3,r.bullet_4,r.bullet_5,r.req_estrategico_1,r.req_estrategico_2,r.req_estrategico_3,r.req_estrategico_4,r.req_estrategico_5,r.req_estrategico_6,r.req_estrategico_7,r.req_estrategico_8,r.sustento_req_estrategico,r.etapa_id,r.status,r.user_created_id,r.created_at, r.respuesta,
a.nombre AS nombre_area,
p.nombre AS nombre_producto,
ts.nombre AS nombre_solicitud,
es.nombre as nombre_estado,
DATE_FORMAT(r.fecha_entrega,'%d-%m-%Y') as fecha_entrega,

CONCAT(IFNULL(peg.nombre, ''),' ',IFNULL(peg.apellido_paterno, ''),' ',IFNULL(peg.apellido_materno, '')) AS nombre_usuario,
peg.correo AS email_usuario,
re1.nombre as nombre_re1,
re2.nombre as nombre_re2,
re3.nombre as nombre_re3,
re4.nombre as nombre_re4,
re5.nombre as nombre_re5,
re6.nombre as nombre_re6,
re7.nombre as nombre_re7,
re8.nombre as nombre_re8
	
FROM mkt_solicitud as r
INNER JOIN mkt_areas AS a ON a.id = r.area_id
INNER JOIN mkt_productos AS p ON p.id = r.producto_id
INNER JOIN mkt_tipo_solicitud AS ts ON ts.id = r.tipo_solicitud_id
INNER JOIN mkt_estado_solicitud AS es ON es.id = r.etapa_id

LEFT JOIN mkt_tipo_requerimiento_estrategico AS re1 ON re1.id = r.req_estrategico_1
LEFT JOIN mkt_tipo_requerimiento_estrategico AS re2 ON re2.id = r.req_estrategico_2
LEFT JOIN mkt_tipo_requerimiento_estrategico AS re3 ON re3.id = r.req_estrategico_3
LEFT JOIN mkt_tipo_requerimiento_estrategico AS re4 ON re4.id = r.req_estrategico_4
LEFT JOIN mkt_tipo_requerimiento_estrategico AS re5 ON re5.id = r.req_estrategico_5
LEFT JOIN mkt_tipo_requerimiento_estrategico AS re6 ON re6.id = r.req_estrategico_6
LEFT JOIN mkt_tipo_requerimiento_estrategico AS re7 ON re7.id = r.req_estrategico_7
LEFT JOIN mkt_tipo_requerimiento_estrategico AS re8 ON re8.id = r.req_estrategico_8


LEFT JOIN tbl_usuarios us ON us.id = r.user_created_id
LEFT JOIN tbl_personal_apt peg ON  peg.id = us.personal_id
WHERE r.solicitud_id = ".$solicitud_id;
$query_sol_det = $mysqli->query($query_solicitud_detalle);
$detalle_solitud = $query_sol_det->fetch_assoc();

// BOTON EDITAR
if ($detalle_solitud['user_created_id'] == $usuario_id && $detalle_solitud['etapa_id'] == 1) {
	$btn_editar_solicitud = true;
}else{
	$query_validar_estado = "SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' AND sub_sec_id = '$sub_sec_id' LIMIT 1";
	$boton_cambiar_estado = $mysqli->query($query_validar_estado)->fetch_assoc();
	$id_boton_cambiar_estado = $boton_cambiar_estado["id"];
	if(array_key_exists($id_boton_cambiar_estado,$usuario_permisos) && in_array("edit", $usuario_permisos[$id_boton_cambiar_estado]))
	{
		$btn_editar_solicitud = true;
	}
}



?>

<style type="text/css">
	.sec_contrato_detalle_solicitud_datepicker {
    	min-height: 28px !important;
	}
</style>

<input type="hidden" id="solicitud_id_temporal" value="<?php echo $solicitud_id; ?>">

<div id="div_sec_contrato_nuevo">
	<div id="loader_"></div>
	<div class="row">
		
		<div class="col-xs-12">
			<div class="col-md-4" style="margin-bottom: 10px;">
				<a class="btn btn-primary btn-sm" id="btnRegresar" href="javascript:history.back();">
					<i class="glyphicon glyphicon-arrow-left"></i>
					Regresar
				</a>
			</div>
			<div class="col-md-8" style="margin-bottom: 10px; text-align: left;">
				<h1 class="page-title" style="margin-top: 10px;">
					<i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Solicitud de Requerimiento Marketing Retail - Código: <?php echo $detalle_solitud['numero']; ?>
				</h1>
			</div>
		</div>

		<div class="col-xs-12 col-md-12 col-lg-7">
			<div class="panel" id="divDetalleSolicitud">

				<div class="panel-heading">
					<div class="panel-title" style="width: 300px; display: inline-block;">DETALLE DE LA SOLICITUD</div>
				</div>

				<div class="panel-body" style="padding: 0px; padding-top: 10px; font-size: 12px;">
					<form id="frmMarketingDetalle">
					
						<div class="w-100 text-right" style="padding-right: 5px;">
							<label class="">
								<input type="checkbox" name="check_collapse" id="check_collapse">
								<span id="label_check_collapse" for="check_collapse">Agrupar Secciones</span>
							</label>
						</div>
						<!-- INICIO PANEL COLLAPSE -->
						<div class="panel-group" id="accordionContrato" role="tablist" aria-multiselectable="true">

							<!-- INICIO DATOS GENERALES -->
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
												<table class="table table-bordered table-hover">
													<tr>
														<td style="width: 250px;"><b>Registrado por</b></td>
														<td><?php echo $detalle_solitud['nombre_usuario'];?></td>
													</tr>
													<tr>
														<td style="width: 250px;"><b>Fecha de Registro</b></td>
														<td><?php echo $detalle_solitud['created_at'];?></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
								</div>
							</div>
							<!-- FIN DATOS GENERALES -->

							<!-- INICIO DE DATOS DE SOLICITUD -->
							<div class="panel panel-default">
								<div class="panel-heading" role="tab" id="headingDatosSolicitud">
								<h4 class="panel-title">
									<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordionContrato" href="#collapseDatosSolicitud" aria-expanded="false" aria-controls="collapseDatosSolicitud">
										DATOS DE LA SOLICITUD
									</a>
								</h4>
								</div>
								<div id="collapseDatosSolicitud" class="panel-collapse-all panel-collapse collapse" role="tabpanel" aria-labelledby="headingDatosSolicitud">
									<div class="panel-body">
										<table class="table table-bordered table-hover">
											<tr>
												<td style="width: 250px;"><b>Área</b></td>
												<td><?php echo $detalle_solitud['nombre_area'];?></td>
												<?php if ($btn_editar_solicitud) { ?>
												<td style="width: 75px;">
													<a class="btn btn-success btn-xs" 
														onclick="sec_mkt_detalle_solicitud_editar_solicitud('Datos de la Solicitud','mkt_solicitud','area_id','Área','select_option','<?php echo $detalle_solitud['nombre_area']; ?>','obtener_areas','');">
														<span class="fa fa-edit"></span> Editar
													</a>
												</td>
												<?php } ?>
											</tr>
											<tr>
												<td style="width: 250px;"><b>Producto</b></td>
												<td><?php echo $detalle_solitud['nombre_producto'];?></td>
												<?php if ($btn_editar_solicitud) { ?>
												<td style="width: 75px;">
													<a class="btn btn-success btn-xs" 
														onclick="sec_mkt_detalle_solicitud_editar_solicitud('Datos de la Solicitud','mkt_solicitud','producto_id','Producto','select_option','<?php echo $detalle_solitud['nombre_producto']; ?>','obtener_productos','');">
														<span class="fa fa-edit"></span> Editar
													</a>
												</td>
												<?php } ?>
											</tr>
											<tr>
												<td style="width: 250px;"><b>Solicitud</b></td>
												<td><?php echo $detalle_solitud['nombre_solicitud'];?></td>
												<?php if ($btn_editar_solicitud) { ?>
												<td style="width: 75px;">
													<a class="btn btn-success btn-xs" 
														onclick="sec_mkt_detalle_solicitud_editar_solicitud('Datos de la Solicitud','mkt_solicitud','tipo_solicitud_id','Solicitud','select_option','<?php echo $detalle_solitud['nombre_solicitud']; ?>','obtener_tipo_solicitud','');">
														<span class="fa fa-edit"></span> Editar
													</a>
												</td>
												<?php } ?>
											</tr>
										</table>
									</div>
								</div>
							</div>
							<!-- FIN DE DATOS DE SOLICITUD -->


							<!-- INICIO DE DATOS DE OBJETIVOS -->
							<div class="panel panel-default">
								<div class="panel-heading" role="tab" id="headingDatosObjetivo">
								<h4 class="panel-title">
									<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordionContrato" href="#collapseDatosObjetivo" aria-expanded="false" aria-controls="collapseDatosObjetivo">
										OBJETIVO
									</a>
								</h4>
								</div>
								<div id="collapseDatosObjetivo" class="panel-collapse-all panel-collapse collapse" role="tabpanel" aria-labelledby="headingDatosObjetivo">
									<div class="panel-body">
										<table class="table table-bordered table-hover">
											<tr>
												<td style="width: 250px;"><b>Objetivo</b></td>
												<td style="white-space: pre-line;"><?php echo $detalle_solitud['objetivo'];?></td>
												<?php if ($btn_editar_solicitud) { ?>
												<td style="width: 75px;">
													<a class="btn btn-success btn-xs" 
														onclick="sec_mkt_detalle_solicitud_editar_solicitud('Datos de la Solicitud','mkt_solicitud','objetivo','Objetivo','textarea','<?php echo $detalle_solitud['objetivo']; ?>','','');">
														<span class="fa fa-edit"></span> Editar
													</a>
												</td>
												<?php } ?>
											</tr>
										</table>
									</div>
								</div>
							</div>
							<!-- FIN DE DATOS DE OBJETIVOS -->


							<!-- INICIO DE DATOS DE BULLETS -->
							<div class="panel panel-default">
								<div class="panel-heading" role="tab" id="headingDatosBullet">
								<h4 class="panel-title">
									<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordionContrato" href="#collapseDatosBullet" aria-expanded="false" aria-controls="collapseDatosBullet">
										BULLETS
									</a>
								</h4>
								</div>
								<div id="collapseDatosBullet" class="panel-collapse-all panel-collapse collapse" role="tabpanel" aria-labelledby="headingDatosBullet">
									<div class="panel-body">
										<table class="table table-bordered table-hover">
											<tr>
												<td style="width: 250px;"><b>Bullet 1</b></td>
												<td><?php echo $detalle_solitud['bullet_1'];?></td>
												<?php if ($btn_editar_solicitud) { ?>
												<td style="width: 75px;">
													<a class="btn btn-success btn-xs" 
														onclick="sec_mkt_detalle_solicitud_editar_solicitud('Bullets','mkt_solicitud','bullet_1','Bullet 1','varchar','<?php echo $detalle_solitud['bullet_1']; ?>','','');">
														<span class="fa fa-edit"></span> Editar
													</a>
												</td>
												<?php } ?>
											</tr>
											<tr>
												<td style="width: 250px;"><b>Bullet 2</b></td>
												<td><?php echo $detalle_solitud['bullet_2'];?></td>
												<?php if ($btn_editar_solicitud) { ?>
												<td style="width: 75px;">
													<a class="btn btn-success btn-xs" 
													onclick="sec_mkt_detalle_solicitud_editar_solicitud('Bullets','mkt_solicitud','bullet_2','Bullet 2','varchar','<?php echo $detalle_solitud['bullet_2']; ?>','','');">
														<span class="fa fa-edit"></span> Editar
													</a>
												</td>
												<?php } ?>
											</tr>
											<tr>
												<td style="width: 250px;"><b>Bullet 3</b></td>
												<td><?php echo $detalle_solitud['bullet_3'];?></td>
												<?php if ($btn_editar_solicitud) { ?>
												<td style="width: 75px;">
													<a class="btn btn-success btn-xs" 
													onclick="sec_mkt_detalle_solicitud_editar_solicitud('Bullets','mkt_solicitud','bullet_3','Bullet 3','varchar','<?php echo $detalle_solitud['bullet_3']; ?>','','');">
														<span class="fa fa-edit"></span> Editar
													</a>
												</td>
												<?php } ?>
											</tr>
											<tr>
												<td style="width: 250px;"><b>Bullet 4</b></td>
												<td><?php echo $detalle_solitud['bullet_4'];?></td>
												<?php if ($btn_editar_solicitud) { ?>
												<td style="width: 75px;">
													<a class="btn btn-success btn-xs" 
													onclick="sec_mkt_detalle_solicitud_editar_solicitud('Bullets','mkt_solicitud','bullet_4','Bullet 4','varchar','<?php echo $detalle_solitud['bullet_4']; ?>','','');">
														<span class="fa fa-edit"></span> Editar
													</a>
												</td>
												<?php } ?>
											</tr>
											<tr>
												<td style="width: 250px;"><b>Bullet 5</b></td>
												<td><?php echo $detalle_solitud['bullet_5'];?></td>
												<?php if ($btn_editar_solicitud) { ?>
												<td style="width: 75px;">
													<a class="btn btn-success btn-xs" 
													onclick="sec_mkt_detalle_solicitud_editar_solicitud('Bullets','mkt_solicitud','bullet_5','Bullet 5','varchar','<?php echo $detalle_solitud['bullet_5']; ?>','','');">
														<span class="fa fa-edit"></span> Editar
													</a>
												</td>
												<?php } ?>
											</tr>
										</table>
									</div>
								</div>
							</div>
							<!-- FIN DE DATOS DE BULLETS -->


							<!-- INICIO DE DATOS DE REQUERIMIENTO ESTRATEGICO -->
							<div class="panel panel-default">
								<div class="panel-heading" role="tab" id="headingDatosRequerimientoEstrategico">
								<h4 class="panel-title">
									<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordionContrato" href="#collapseDatosRequerimientoEstrategico" aria-expanded="false" aria-controls="collapseDatosRequerimientoEstrategico">
										REQUERIMIENTOS ESTRATEGICOS
									</a>
								</h4>
								</div>
								<div id="collapseDatosRequerimientoEstrategico" class="panel-collapse-all panel-collapse collapse" role="tabpanel" aria-labelledby="headingDatosRequerimientoEstrategico">
									<div class="panel-body">
										<table class="table table-bordered table-hover">
											<tr>
												<td style="width: 250px;"><b>Req. Estrategico 1</b></td>
												<td><?php echo $detalle_solitud['nombre_re1'];?></td>
												<?php if ($btn_editar_solicitud) { ?>
												<td style="width: 75px;">
													<a class="btn btn-success btn-xs" 
														onclick="sec_mkt_detalle_solicitud_editar_solicitud('Requerimiento Estrategico','mkt_solicitud','req_estrategico_1','Requerimiento Estrategico 1','select_option','<?php echo $detalle_solitud['nombre_re1']; ?>','obtener_tipo_requerimiento_estrategico','');">
														<span class="fa fa-edit"></span> Editar
													</a>
												</td>
												<?php } ?>
											</tr>
											<tr>
												<td style="width: 250px;"><b>Req. Estrategico 2</b></td>
												<td><?php echo $detalle_solitud['nombre_re2'];?></td>
												<?php if ($btn_editar_solicitud) { ?>
												<td style="width: 75px;">
													<a class="btn btn-success btn-xs" 
													onclick="sec_mkt_detalle_solicitud_editar_solicitud('Requerimiento Estrategico','mkt_solicitud','req_estrategico_2','Requerimiento Estrategico 2','select_option','<?php echo $detalle_solitud['nombre_re2']; ?>','obtener_tipo_requerimiento_estrategico','');">
														<span class="fa fa-edit"></span> Editar
													</a>
												</td>
												<?php } ?>
											</tr>
											<tr>
												<td style="width: 250px;"><b>Req. Estrategico 3</b></td>
												<td><?php echo $detalle_solitud['nombre_re3'];?></td>
												<?php if ($btn_editar_solicitud) { ?>
												<td style="width: 75px;">
													<a class="btn btn-success btn-xs" 
														onclick="sec_mkt_detalle_solicitud_editar_solicitud('Requerimiento Estrategico','mkt_solicitud','req_estrategico_3','Requerimiento Estrategico 3','select_option','<?php echo $detalle_solitud['nombre_re3']; ?>','obtener_tipo_requerimiento_estrategico','');">
														<span class="fa fa-edit"></span> Editar
													</a>
												</td>
												<?php } ?>
											</tr>
											<tr>
												<td style="width: 250px;"><b>Req. Estrategico 4</b></td>
												<td><?php echo $detalle_solitud['nombre_re4'];?></td>
												<?php if ($btn_editar_solicitud) { ?>
												<td style="width: 75px;">
													<a class="btn btn-success btn-xs" 
														onclick="sec_mkt_detalle_solicitud_editar_solicitud('Requerimiento Estrategico','mkt_solicitud','req_estrategico_4','Requerimiento Estrategico 4','select_option','<?php echo $detalle_solitud['nombre_re4']; ?>','obtener_tipo_requerimiento_estrategico','');">
														<span class="fa fa-edit"></span> Editar
													</a>
												</td>
												<?php } ?>
											</tr>
											<tr>
												<td style="width: 250px;"><b>Req. Estrategico 5</b></td>
												<td><?php echo $detalle_solitud['nombre_re5'];?></td>
												<?php if ($btn_editar_solicitud) { ?>
												<td style="width: 75px;">
													<a class="btn btn-success btn-xs" 
														onclick="sec_mkt_detalle_solicitud_editar_solicitud('Requerimiento Estrategico','mkt_solicitud','req_estrategico_5','Requerimiento Estrategico 5','select_option','<?php echo $detalle_solitud['nombre_re5']; ?>','obtener_tipo_requerimiento_estrategico','');">
														<span class="fa fa-edit"></span> Editar
													</a>
												</td>
												<?php } ?>
											</tr>
											<tr>
												<td style="width: 250px;"><b>Req. Estrategico 6</b></td>
												<td><?php echo $detalle_solitud['nombre_re6'];?></td>
												<?php if ($btn_editar_solicitud) { ?>
												<td style="width: 75px;">
													<a class="btn btn-success btn-xs" 
														onclick="sec_mkt_detalle_solicitud_editar_solicitud('Requerimiento Estrategico','mkt_solicitud','req_estrategico_6','Requerimiento Estrategico 6','select_option','<?php echo $detalle_solitud['nombre_re6']; ?>','obtener_tipo_requerimiento_estrategico','');">
														<span class="fa fa-edit"></span> Editar
													</a>
												</td>
												<?php } ?>
											</tr>
											<tr>
												<td style="width: 250px;"><b>Req. Estrategico 7</b></td>
												<td><?php echo $detalle_solitud['nombre_re7'];?></td>
												<?php if ($btn_editar_solicitud) { ?>
												<td style="width: 75px;">
													<a class="btn btn-success btn-xs" 
														onclick="sec_mkt_detalle_solicitud_editar_solicitud('Requerimiento Estrategico','mkt_solicitud','req_estrategico_7','Requerimiento Estrategico 7','select_option','<?php echo $detalle_solitud['nombre_re7']; ?>','obtener_tipo_requerimiento_estrategico','');">
														<span class="fa fa-edit"></span> Editar
													</a>
												</td>
												<?php } ?>
											</tr>
											<tr>
												<td style="width: 250px;"><b>Req. Estrategico 8</b></td>
												<td><?php echo $detalle_solitud['nombre_re8'];?></td>
												<?php if ($btn_editar_solicitud) { ?>
												<td style="width: 75px;">
													<a class="btn btn-success btn-xs" 
														onclick="sec_mkt_detalle_solicitud_editar_solicitud('Requerimiento Estrategico','mkt_solicitud','req_estrategico_8','Requerimiento Estrategico 8','select_option','<?php echo $detalle_solitud['nombre_re8']; ?>','obtener_tipo_requerimiento_estrategico','');">
														<span class="fa fa-edit"></span> Editar
													</a>
												</td>
												<?php } ?>
											</tr>
										</table>
									</div>
								</div>
							</div>
							<!-- FIN DE DATOS DE REQUERIMIENTO ESTRATEGICO -->


							<!-- INICIO DE SUSTENTO ESTRATEGICO -->
							<div class="panel panel-default">
								<div class="panel-heading" role="tab" id="headingDatosSustentoEstrategico">
								<h4 class="panel-title">
									<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordionContrato" href="#collapseDatosSustentoEstrategico" aria-expanded="false" aria-controls="collapseDatosSustentoEstrategico">
										SUSTENTO REQUERIMIENTO ESTRATÉGICO
									</a>
								</h4>
								</div>
								<div id="collapseDatosSustentoEstrategico" class="panel-collapse-all panel-collapse collapse" role="tabpanel" aria-labelledby="headingDatosSustentoEstrategico">
									<div class="panel-body">
										<table class="table table-bordered table-hover">
											<tr>
												<td style="width: 250px;"><b>Sustento Req. Estratégico</b></td>
												<td style="white-space: pre-line;"><?php echo $detalle_solitud['sustento_req_estrategico'];?></td>
												<?php if ($btn_editar_solicitud) { ?>
												<td style="width: 75px;">
													<a class="btn btn-success btn-xs" 
														onclick="sec_mkt_detalle_solicitud_editar_solicitud('Sustento Estratégico','mkt_solicitud','sustento_req_estrategico','Sustento Requerimiento Estrategico','textarea','<?php echo $detalle_solitud['sustento_req_estrategico']; ?>','','');">
														<span class="fa fa-edit"></span> Editar
													</a>
												</td>
												<?php } ?>
											</tr>
										</table>
									</div>
								</div>
							</div>
							<!-- FIN DE SUSTENTO ESTRATEGICO -->


							<!-- INICIO DE SUSTENTO ESTRATEGICO -->
							<div class="panel panel-default">
								<div class="panel-heading" role="tab" id="headingDatosEstadoSolicitud">
								<h4 class="panel-title">
									<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordionContrato" href="#collapseDatosEstadoSolicitud" aria-expanded="false" aria-controls="collapseDatosEstadoSolicitud">
										ESTADO DE SOLICITUD
									</a>
								</h4>
								</div>
								<div id="collapseDatosEstadoSolicitud" class="panel-collapse-all panel-collapse collapse" role="tabpanel" aria-labelledby="headingDatosEstadoSolicitud">
									<div class="panel-body">
										<table class="table table-bordered table-hover">
											<tr>
												<td style="width: 250px;"><b>Estado</b></td>
												<td style="white-space: pre-line;"><?php echo $detalle_solitud['nombre_estado'];?></td>
											</tr>
											<tr>
												<td style="width: 250px;"><b>Fecha de Entrega</b></td>
												<td style="white-space: pre-line;"><?php echo $detalle_solitud['fecha_entrega'];?></td>
											</tr>
											<tr>
												<td style="width: 250px;"><b>Respuesta</b></td>
												<td style="white-space: pre-line;"><?php echo $detalle_solitud['respuesta'];?></td>
											</tr>
										</table>
									</div>
								</div>
							</div>
							<!-- FIN DE SUSTENTO ESTRATEGICO -->

						</div>
						<!-- FINAL PANEL COLLAPSE -->
						                    
					</form>
				</div>
			</div>


		</div>


		<div class="col-xs-12 col-md-12 col-lg-5">
			<div class="panel" id="divDetalleSolicitud">
				<div class="panel-body" style="padding: 5px 10px 5px 10px;">
					<div class="panel-group no-mb" id="accordion" role="tablist" aria-multiselectable="true">
						<?php
						$query_validar_estado = "SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' AND sub_sec_id = '$sub_sec_id' LIMIT 1";
						$boton_cambiar_estado = $mysqli->query($query_validar_estado)->fetch_assoc();
						$id_boton_cambiar_estado = $boton_cambiar_estado["id"];

						if(array_key_exists($id_boton_cambiar_estado,$usuario_permisos) && in_array("validaBotonSolicitud", $usuario_permisos[$id_boton_cambiar_estado]))
						{
						?>
						<!-- INICIO CAMBIO DE ESTADO -->
						<div class="panel">
							<div class="panel-heading" role="tab" id="browsers-this-week-heading">
								<div class="panel-title">
									<a href="#browsers-this-week" role="button" data-toggle="collapse"
									   data-parent="#accordion" aria-expanded="true" aria-controls="browsers-this-week">
										Estado de Solicitud
									</a>
								</div>
							</div>

							<div id="browsers-this-week" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="browsers-this-week-heading">
								<div class="panel-body">
									<form id="frm_mkt_cambio_estado" name="frm_mkt_cambio_estado" method="post">
										<div class="row">
											<div class="col-sm-6 col-md-6">
												<div class="form-group">
													<label for="">Estado:</label>
													<select class="form-control" name="mkt_detalle_solicitud_estado" id="mkt_detalle_solicitud_estado">
														<?php 
														$query = "SELECT id, nombre FROM mkt_estado_solicitud WHERE status = 1 ORDER BY id ASC";
														$list_query = $mysqli->query($query);
														$datos = array();
														while ($li = $list_query->fetch_assoc()) {
														?>
															<option <?=$detalle_solitud['etapa_id'] == $li['id'] ? 'selected':''?> value="<?=$li['id']?>"><?=$li['nombre']?></option>
														<?php
														}
														?>
														
													</select>
												</div>
											</div>
											<div class="col-sm-6 col-md-6">
												<label>Fecha Entrega:</label>
												<div class="form-group">
													<div class="input-group col-xs-12">
														<input type="text" class="form-control mkt_req_datepicker" id="mkt_detalle_solicitud_fecha_entrega" value="<?=$detalle_solitud['fecha_entrega']?>" readonly="readonly">
														<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="mkt_detalle_solicitud_fecha_entrega"></label>
													</div>
												</div>
											</div>
											<div class="col-md-12">
												<div class="form-group">
													<label for="">Respuesta:</label>
													<textarea class="form-control" name="mkt_detalle_solicitud_respuesta" id="mkt_detalle_solicitud_respuesta" cols="30" rows="4"><?=$detalle_solitud['respuesta']?></textarea>
												</div>
											</div>
											<hr>
											<div class="col-md-12 mt-1">
												<button type="submit" class="btn btn-success btn-block" id="guardar_contrato_proveedor">
													<i class="icon fa fa-save" id="demo-button-icon-left" style="display: inline-block;"></i>
													<span id="demo-button-text">Cambiar Estado</span>
												</button>
											</div>
										</div>
									</form>
								</div>
							</div>
						</div>
						<!-- FIN CAMBIO DE ESTADO -->
						<?php
						} 
						?>


						<!-- INICIO CAMBIO DE ESTADO -->
						<div class="panel">
							<div class="panel-heading" role="tab" id="browsers-this-changes-heading">
								<div class="panel-title">
									<a href="#browsers-this-changes" role="button" data-toggle="collapse"
									   data-parent="#accordion" aria-expanded="false" aria-controls="browsers-this-changes">
										Cambios (Auditoria)
									</a>
								</div>
							</div>

							<div id="browsers-this-changes" class="panel-collapse collapse" role="tabpanel" aria-labelledby="browsers-this-changes-heading">
								<div class="panel-body" style="width: 100%; height: 350px; overflow: scroll;">
									<?php
									$sel_query = $mysqli->query("SELECT 
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
										mkt_auditoria a
											INNER JOIN
										tbl_usuarios u ON a.user_created_id = u.id
											INNER JOIN
										tbl_personal_apt p ON u.personal_id = p.id
											INNER JOIN
										tbl_areas ar ON p.area_id = ar.id
									WHERE
										a.status = 1 AND a.solicitud_id = " . $solicitud_id . "
									ORDER BY a.id DESC");
									$row_count = $sel_query->num_rows;
									if ($row_count > 0) {
									?>

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

									<?php
									}
									?>
								</div>
							</div>
						</div>
						<!-- FIN CAMBIO DE ESTADO -->

					</div>
				</div>
			</div>
		</div>
	</div>
</div>


<!-- INICIO MODAL EDITAR SOLICITUD -->
<div id="modal_editar_solicitud" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Editar Solicitud</h4>
			</div>
			<div class="modal-body no-pad">
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
												<input type="number" id="editar_solicitud_valor_int" class="filtro txt_filter_style" style="width: 100%;">
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
												<input type="number" step="any" id="editar_solicitud_valor_decimal" class="filtro txt_filter_style" style="width: 100%;" placeholder="0.00">
											</div>
											<div id="div_editar_solicitud_valor_select_option">
												<select class="form-control select2" id="editar_solicitud_valor_select_option" name="editar_solicitud_valor_select_option">
												</select>
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
				<button type="button" class="btn btn-success" onclick="sec_mkt_detalle_solicitud_editar_campo_solicitud('modal_editar_solicitud');">
					<i class="icon fa fa-edit"></i>
					<span id="demo-button-text">Editar</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL EDITAR SOLICITUD -->

