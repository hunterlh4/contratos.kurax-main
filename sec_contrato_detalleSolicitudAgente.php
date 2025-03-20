<?php
$this_menu = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' LIMIT 1")->fetch_assoc();
$menu_id = $this_menu["id"];

if(!array_key_exists($menu_id,$usuario_permisos)){
	echo "No tienes permisos para este recurso.";
	die();
}

$contrato_id = $_GET["id"];

$usuario_id = $login?$login['id']:null;
$area_id = $login ? $login['area_id'] : 0;
$cargo_id = $login ? $login['cargo_id'] : 0;

$query_sql = "
SELECT 
	c.user_created_id,
	c.etapa_id,
	p.area_id,
	co.sigla AS sigla_correlativo,
	c.codigo_correlativo
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

if ($usuario_id == $user_created_id && $etapa_id == 1) {
	$btn_editar_solicitud = true;
} else {
	$btn_editar_solicitud = false;
}

$list_emp_cont = $mysqli->query("SELECT tpg.valor FROM tbl_parametros_generales tpg WHERE tpg.codigo = 'empresa_contacto_de_contratos' AND tpg.estado = 1 LIMIT 1");
$row_emp_cont = $list_emp_cont->fetch_assoc();
$valor_empresa_contacto = isset($row_emp_cont['valor']) && $row_emp_cont['valor'] == 'Kurax' ? '(Kurax)':'(AT)';

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
</style>

<input type="hidden" id="contrato_id_temporal" value="<?php echo $contrato_id; ?>">
<input type="hidden" id="tipo_contrato_id_temporal" value="2">

<div class="tbl_goldenRace_retail_jackpots" id="div_sec_contrato_nuevo">

	<div id="loader_"></div>

		<div class="row">
	
			<div class="col-xs-12">
				<div class="col-md-4" style="margin-bottom: 10px;">
					<a class="btn btn-primary" id="btnRegresar" href="javascript:history.back();">
						<i class="glyphicon glyphicon-arrow-left"></i>
						Regresar
					</a>
				</div>
				<div class="col-md-8" style="margin-bottom: 10px; text-align: left;">
					<h1 class="page-title" style="margin-top: 10px;">
						<i class="icon icon-inline glyphicon glyphicon-briefcase"></i> <?php echo $etapa_id == 1 ? 'Solicitud de ' : '';?> Contrato de Agente - Código: <?php echo $sigla_correlativo; echo $codigo_correlativo; ?>
					</h1>
				</div>
			</div>

			<div class="col-xs-12 col-md-12 col-lg-7">

				<!-- PANEL: Horizontal Form -->
				<div class="panel" id="divDetalleSolicitud">

					<!-- Panel Heading -->
					<div class="panel-heading">

						<!-- Panel Title -->
						<div class="panel-title">DETALLE DE LA SOLICITUD</div>
						<!-- /Panel Title -->
						<?php 
							if($usuario_id == 3315 || $usuario_id == 3562 || $usuario_id == 3028)
							{
								?>
								<a class="btn btn-info btn-xs" style="float: right;" onclick="enviar_por_email_solicitud_al_lourdes_britto(<?php echo $contrato_id; ?>);">
									<span class="fa fa-envelope-o"></span> Reenviar por email a Lourdes Britto
								</a>
								<?php 
							}
						?>
					</div>
					<!-- /Panel Heading -->

					<!-- Panel Body -->
					<div class="panel-body" style="padding: 0px;">

						<form id="frmContratoDeArrendatario">
							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="h5"><b>DATOS GENERALES</b></div>
							</div>

							<div class="col-xs-12 col-md-12 col-lg-12">
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
										c.created_at
									FROM
										cont_contrato c
										INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id
										INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
										INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
									WHERE 
										c.contrato_id IN (" . $contrato_id . ")");
									while($sel = $sel_query->fetch_assoc()){
										$observaciones = $sel["observaciones"];
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
											<td><b>Registrado por</b></td>
											<td><?php echo $sel["user_created"];?></td>
											<?php if ($btn_editar_solicitud) { ?>
											<td></td>
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
									?>
								</div>
							</div>
							
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

									$cantReg = mysqli_num_rows($sel_query);

									while($sel=$sel_query->fetch_assoc())
									{
										$check_gerencia_proveedor = $sel["check_gerencia_proveedor"];
										$fecha_atencion_gerencia_proveedor = $sel["fecha_atencion_gerencia_proveedor"];
										$aprobacion_gerencia_proveedor = $sel["aprobacion_gerencia_proveedor"];
									}
							?>
							<div class="col-xs-12 col-md-12 col-lg-12">
								<br>
							</div>

							<?php  
							if($check_gerencia_proveedor == 1)
							{
								
							?>
								<div class="col-xs-12 col-md-12 col-lg-12">
									<div class="h4"><b>APROBACIÓN DE GERENCIA</b></div>
								</div>

								<div class="col-xs-12 col-md-12 col-lg-12">
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
																<td>Lourdes Britto</td>
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
																<td>Lourdes Britto</td>
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

										</table>
									</div>
								</div>

								<div class="col-xs-12 col-md-12 col-lg-12">
									<br>
								</div>

							<?php  
							}
							?>

							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="h5"><b>DATOS DEL PROVEEDOR</b></div>
							</div>

							<div class="col-xs-12 col-md-12 col-lg-12">
								<div id="divTablaPropietarios" class="form-group">
									<?php
									$sel_query = $mysqli->query("
									SELECT 
										c.ruc,
										c.razon_social,
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
										CONCAT(c.periodo_numero, ' ', p.nombre) AS periodo,
										c.periodo_numero,
										c.fecha_inicio
									FROM 
										cont_contrato c
										INNER JOIN cont_periodo p ON c.periodo = p.id
										LEFT JOIN cont_tipo_terminacion_anticipada t ON c.tipo_terminacion_anticipada_id = t.id
									WHERE 
										c.contrato_id = $contrato_id
									");
									while($sel=$sel_query->fetch_assoc()){
										$dni_representante = $sel["dni_representante"];
										$nombre_representante = $sel["nombre_representante"];
										$detalle_servicio = $sel["detalle_servicio"];
										$periodo = $sel["periodo"];
										$fecha_inicio_contrato = $sel["fecha_inicio"];
										$periodo_numero = $sel["periodo_numero"];
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
									</table>
									<?php
									}
									?>
								</div>
							</div>

							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="h5">
									<b>DATOS DEL PROVEEDOR - REPRESENTANTE LEGAL</b>
								 
								</div>
							</div>

							<div class="col-xs-12 col-md-12 col-lg-12">
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
										<b># <?php echo $c ?></b>
										<table class="table table-bordered">
											<tr>
												<td width="50%"><b>DNI DEL REPRESENTANTE LEGAL</b></td>
												<td><?php echo $sel["dni_representante"]; ?></td>
												<?php if ($btn_editar_solicitud) { ?>
												<td style="width: 75px;">
													<a class="btn btn-success btn-xs" 
													onclick="sec_contrato_detalle_solicitud_editar_solicitud('Datos del Proveedor - Representante Legal','cont_representantes_legales','dni_representante','DNI del representante legal','varchar','<?php echo $sel["dni_representante"]; ?>','','<?php echo $sel["id"]; ?>');">
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

															$archivo_estado = '';
														}
														else 
														{
															$archivo = '<a class="btn btn-warning btn-xs btn-block" data-toggle="tooltip" data-placement="top"><span class="fa fa-eye-slash"></span> Incompleto</a>';

															$archivo_estado = '';
														}

														
													}
												}else{
													$archivo = '<a style="width: 150px;" class="btn btn-warning btn-xs btn-block" data-toggle="tooltip" data-placement="top"><span class="fa fa-eye-slash"></span> Incompleto</a>';

													$archivo_estado = '';
												}
												
												
												$html .= '<tr style="text-transform: none;">';
												$html .= '<td style="text-transform: uppercase;"><b>VIGENCIA DE PODER DEL REPRESENTANTE LEGAL DE LA EMPRESA PROVEEDORA</b></td>';
												$html .= '<td>'.$archivo.'</td>';
												if(($area_id == 33 AND $cargo_id != 25) || $user_created_id == $login['id']){
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

															$archivo_estado = '';
														}
														else 
														{
															$archivo = '<a class="btn btn-warning btn-xs btn-block" data-toggle="tooltip" data-placement="top"><span class="fa fa-eye-slash"></span> Incompleto</a>';

															$archivo_estado = '';
														}

														
													}
												}else{
													$archivo = '<a style="width: 150px;" class="btn btn-warning btn-xs btn-block" data-toggle="tooltip" data-placement="top"><span class="fa fa-eye-slash"></span> Incompleto</a>';

													$archivo_estado = '';
												}
												
												
												$html .= '<tr style="text-transform: none;">';
												$html .= '<td style="text-transform: uppercase;"><b>DNI del representante legal de la empresa proveedora</b></td>';

												$html .= '<td>'.$archivo.'</td>';
												if(($area_id == 33 AND $cargo_id != 25) || $user_created_id == $login['id']){
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

							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="h5"><b>CONDICIONES COMERCIALES</b></div>
							</div>

							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="h5" style="font-weight: bold;">1) Objeto del Contrato:</div>
							</div>

							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="form-group">
									<table class="table table-bordered table-hover">
										<tr>
											<td><?php echo $detalle_servicio;?></td>
											<?php if ($btn_editar_solicitud) { ?>
											<td style="width: 75px;">
												<a class="btn btn-success btn-xs" 
												onclick="sec_contrato_detalle_solicitud_editar_solicitud('Condiciones Comerciales','cont_contrato','detalle_servicio','Objeto del Contrato','varchar','<?php echo $detalle_servicio; ?>','','');">
													<span class="fa fa-edit"></span> Editar
												</a>
											</td>
											<?php } ?>
										</tr>
									</table>
								</div>
							</div>

							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="h5" style="font-weight: bold;">2) Plazo del Contrato:</div>
							</div>

							<div class="col-xs-12 col-md-12 col-lg-12">
								<div id="divTablaPropietarios" class="form-group">
									<table class="table table-bordered">
										<tr>
											<td style="width: 50%;"><b>Periodo</b></td>
											<td><?php echo $periodo;?></td>
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

							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="h5" style="font-weight: bold;">
									3) Contraprestación:
									<button 
										type="button" 
										class="btn btn-sm btn-info" 
										style="display: none;" 
										onclick="sec_contrato_detalle_solicitud_agregar_contraprestacion()">
										<i class="fa fa-plus"></i>
										 Agregar Contraprestación
									</button>
								</div>
							</div>

							<div class="col-xs-12 col-md-12 col-lg-12">
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
														onclick="sec_contrato_detalle_solicitud_editar_solicitud('Contraprestación','cont_contraprestacion','plazo_pago','Plazo de Pago','varchar','<?php echo $sel["plazo_pago"]; ?>','','<?php echo $contraprestacion_id; ?>');">
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
														onclick="sec_contrato_detalle_solicitud_editar_solicitud('Contraprestación','cont_contraprestacion','forma_pago_detallado','Forma de pago - Detallado','varchar','<?php echo $forma_pago_detallado; ?>','','<?php echo $contraprestacion_id; ?>');">
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
													onclick="sec_contrato_detalle_solicitud_editar_solicitud('Contraprestación','cont_contrato','plazo_pago','Plazo de Pago','varchar','<?php echo $sel["plazo_pago"]; ?>','','');">
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

							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="h5" style="font-weight: bold;">4) Alcance del servicio:</div>
							</div>
							
							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="form-group">
									<table class="table table-bordered table-hover">
										<tr>
											<td><?php echo $alcance_servicio;?></td>
											<?php if ($btn_editar_solicitud) { ?>
											<td style="width: 75px;">
												<a class="btn btn-success btn-xs" 
												onclick="sec_contrato_detalle_solicitud_editar_solicitud('Alcance del servicio','cont_contrato','alcance_servicio','Alcance del servicio','varchar','<?php echo $alcance_servicio; ?>','','');">
													<span class="fa fa-edit"></span> Editar
												</a>
											</td>
											<?php } ?>
										</tr>
									</table>
								</div>
							</div>

							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="h5" style="font-weight: bold;">5) Terminación Anticipada:</div>
							</div>
							
							<div class="col-xs-12 col-md-12 col-lg-12">
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
											<td><?php echo $terminacion_anticipada;?></td>
											<?php if ($btn_editar_solicitud) { ?>
											<td style="width: 75px;">
												<a class="btn btn-success btn-xs" 
												onclick="sec_contrato_detalle_solicitud_editar_solicitud('Terminación Anticipada','cont_contrato','terminacion_anticipada','Terminación Anticipada - Detalle','varchar','<?php echo $terminacion_anticipada; ?>','','');">
													<span class="fa fa-edit"></span> Editar
												</a>
											</td>
											<?php } ?>
										</tr>
										<?php } ?>
									</table>
								</div>
							</div>

							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="h5" style="font-weight: bold;">6) Observaciones:</div>
							</div>
							
							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="form-group">
									<table class="table table-bordered table-hover">
										<tr>
											<td><?php echo $observaciones_legal;?></td>
											<?php if ($btn_editar_solicitud) { ?>
											<td style="width: 75px;">
												<a class="btn btn-success btn-xs" 
												onclick="sec_contrato_detalle_solicitud_editar_solicitud('Observaciones','cont_contrato','observaciones','Observaciones','varchar','<?php echo $observaciones_legal; ?>','','');">
													<span class="fa fa-edit"></span> Editar
												</a>
											</td>
											<?php } ?>
										</tr>
									</table>
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
											<!--	<tr style="text-transform: none;">
													<td>Solicitud de Contratos de Proveedor</td>
													<td colspan="2">
														<a onclick="sec_contrato_detalle_solicitud_ver_documento_en_visor('','', 'html', '');" class="btn btn-primary btn-xs btn-block" data-toggle="tooltip" data-placement="top"><span class="fa fa-eye"></span> Ver Detalle de la Solicitud</a>
													</td>
												</tr> -->

												<?php
												// INICIO CONTRATO FIRMADO
												if ($area_id == '33' || $usuario_id == $user_created_id) { // 33: Legal 
												
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
														tipo_archivo_id = 16
														AND status = 1
														AND contrato_id = " . $contrato_id
													);
													$num_rows = mysqli_num_rows($sel_contrato_firmado);
													?>

													<tr style="text-transform: none;">
														<td>Contrato Firmado</td>
														<td colspan="2">

													<?php
													if ($num_rows == 0) {
													?>
														<a class="btn btn-warning btn-xs btn-block" data-toggle="tooltip" data-placement="top"><span class="fa fa-eye-slash"></span> Incompleto</a>
													<?php 
													} else if($num_rows > 0) {
														$row = $sel_contrato_firmado->fetch_assoc();
														$ruta = str_replace("/var/www/html","",$row["ruta"]);
													?>
														<a
															onclick="sec_contrato_detalle_solicitud_ver_documento_en_visor('<?php echo $ruta;?>','<?php echo trim($row["nombre"]); ?>','<?php echo trim($row["extension"]); ?>','CONTRATO FIRMADO');"
															class="btn btn-success btn-xs btn-block"
															data-toggle="tooltip"
															data-placement="top">
															<span class="fa fa-eye"></span> Ver Contrato Firmado
														</a>
													<?php
													}
													?>
														</td>
													</tr>
												<?php
												}
												// FIN CONTRATO FIRMADO


												$html = '';

												$sql = "
												SELECT * from (
													SELECT
													a.archivo_id,
													a.contrato_id,
													t.tipo_archivo_id,
													t.nombre_tipo_archivo,
													a.nombre,
													a.extension,
													a.ruta
													FROM cont_tipo_archivos t
													LEFT JOIN cont_archivos a 
													ON t.tipo_archivo_id = a.tipo_archivo_id AND a.contrato_id = $contrato_id  AND a.status = 1
													WHERE t.tipo_archivo_id IN(12, 13)
													UNION ALL
													SELECT
													a.archivo_id,
													a.contrato_id,
													t.tipo_archivo_id,
													t.nombre_tipo_archivo,
													a.nombre,
													a.extension,
													a.ruta
													FROM cont_tipo_archivos t
													INNER JOIN cont_archivos a 
													ON t.tipo_archivo_id = a.tipo_archivo_id AND a.contrato_id = $contrato_id AND a.status = 1
													WHERE t.tipo_archivo_id NOT IN (8,9,10,11,12,13,14,15,20,21,22,23)
													) z ORDER BY z.nombre_tipo_archivo asc
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
																$archivo_estado = '';
															}else{
																$archivo_estado = '';
															}
														}
														else 
														{
															$archivo = '<a class="btn btn-warning btn-xs btn-block" data-toggle="tooltip" data-placement="top"><span class="fa fa-eye-slash"></span> Incompleto</a>';

															$archivo_estado = '';
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
								a.procesado,
								a.created_at AS fecha_solicitud,
								concat(IFNULL(p.nombre, ''), ' ',  IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS solicitante,
								ar.nombre AS area
							FROM cont_adendas a
							INNER JOIN tbl_usuarios u ON a.user_created_id = u.id
							INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
							INNER JOIN tbl_areas ar ON p.area_id = ar.id
							WHERE a.contrato_id = " . $contrato_id . "
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
											$procesado = $sel["procesado"];
											$area = $sel["area"];
											$solicitante = $sel["solicitante"];
											$fecha_solicitud = $sel["fecha_solicitud"];
											$numero_adenda++;

											if ($procesado == 0) {
												$adenda_estado = 'Pendiente';
											} elseif ($procesado == 1) {
												$adenda_estado = 'Procesado';
											}
										?>

										<p><b>Adenda N° <?php echo $numero_adenda; ?></b></p>

										<table class="table table-responsive table-hover no-mb" style="font-size: 10px;">
											<tbody>
												<tr style="text-transform: none;">
													<td><b>Área solicitante</b></td>
													<td>
														<?php echo $area; ?>
													</td>
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

										<br>

										<p><b>Adenda N° <?php echo $numero_adenda; ?> - Detalle</b></p>
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
													<br>
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
										}

										if ($procesado == 0) {
										?>

										<br>

										<form id="form_adenda_firmada" name="form_contrato_firmado" method="POST" enctype="multipart/form-data" autocomplete="off">

											<div style="margin-right: 10px; margin-left: 5px;">
												<div class="form-group">
													<input type="hidden" name="adenda_id" id="adenda_id" value="<?php echo $adenda_id;?>">
													<div class="control-label">Seleccione la adenda firmada:</div>
													<input type="file" name="adenda_firmada" id="adenda_firmada" required accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip">
												</div>
											</div>

											<div style="margin-right: 10px; margin-left: 5px;">
												<button type="button" class="btn btn-success btn-xs btn-block" id="btn_guardar_adenda_firmada" onclick="sec_contrato_detalle_solicitud_guardar_adenda_proveedor_firmada()">
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
											<div id="div_observaciones" class="timeline" style="font-size: 11px;">
											</div>

											<textarea rows="3" id="contrato_observaciones_proveedor" placeholder="Ingrese sus observaciones" style="width: 100%"></textarea>
											<b>Correos Adjuntos: (Opcional)</b>
											<textarea rows="3" id="correos_adjuntos" placeholder="Ingrese Correos Adjuntos" style="width: 100%"></textarea>
											<b>Nota: Para más de un correo se debe separar por comas (,)</b>
											<p></p>
											<button class="btn btn-success btn-xs btn-block" type="submit" onclick="sec_contrato_detalle_solicitud_guardar_observaciones_proveedores();">
												<i class="fa fa-plus"></i> Agregar Observaciones
											</button>
										</div>
									</div>
								</div>
							</div>
							<!-- PANEL: OBSERVACIONES FIN -->


							<!-- PANEL: CONTRATO FIRMADO -->
							
							<?php 
								if ($area_id == '33') // LEGAL PRODUCCION = 33, LEGAL DESARROLLO = 33  
								{ 
							?>
							<div class="panel">

								<!-- Panel Heading -->
								<div class="panel-heading" role="tab" id="browsers-this-day-heading">

									<!-- Panel Title -->
									<div class="panel-title">
										<a href="#browsers-this-day" class="collapsed" role="button" data-toggle="collapse"
										   data-parent="#accordion" aria-expanded="true" aria-controls="browsers-this-day">

											Contrato final
										</a>
									</div>
									<!-- /Panel Title -->

								</div>
								<!-- /Panel Heading -->

								<!-- COLLAPSE: This Week -->
								<div id="browsers-this-day" class="panel-collapse collapse" role="tabpanel"
									 aria-labelledby="browsers-this-day-heading">

									<!-- Panel Body -->
									<div class="panel-body">
										
										<?php 

											$sel_query_info_contrato_firmado_proveedor = $mysqli->query("
											SELECT
												c.contrato_id, c.tipo_contrato_id, tc.nombre AS tipo_contrato,
												c.empresa_suscribe_id, trs.nombre AS razon_social,
												tf.nombre AS tipo_firma, c.fecha_suscripcion_proveedor,
    											c.fecha_inicio, c.fecha_vencimiento_proveedor,
												concat( IFNULL(tpa.nombre, ''),' ', IFNULL(tpa.apellido_paterno, ''),' ', IFNULL(tpa.apellido_materno, '')) AS usuario_aprobado,
												concat( IFNULL(tpc.nombre, ''),' ', IFNULL(tpc.apellido_paterno, ''),' ', IFNULL(tpc.apellido_materno, '')) AS usuario_creado
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

													$fecha_vencimiento_proveedor = date("d-m-Y", strtotime($sel["fecha_vencimiento_proveedor"]));												?>

												<table class="table table-responsive table-hover no-mb" style="font-size: 10px;">
													<tbody>
														<tr style="text-transform: none;">
															<td><b>Tipo de contrato:</b></td>
															<td>
																<?php echo $tipo_contrato; ?>
															</td>
														</tr>
														<tr style="text-transform: none;">
															<td><b>Razón social:</b></td>
															<td>
																<?php echo $razon_social; ?>
															</td>
														</tr>
														<tr style="text-transform: none;">
															<td><b>Contrato aprobado por:</b></td>
															<td>
																<?php echo $usuario_aprobado; ?>
															</td>
														</tr>
														<tr style="text-transform: none;">
															<td><b>Contrato creado por:</b></td>
															<td>
																<?php echo $usuario_creado; ?>
															</td>
														</tr>
														<tr style="text-transform: none;">
															<td><b>Tipo Firma:</b></td>
															<td>
																<?php echo $tipo_firma; ?>
															</td>
														</tr>
														<tr style="text-transform: none;">
															<td><b>Fecha Inicio:</b></td>
															<td>
																<?php echo $fecha_inicio; ?>
															</td>
														</tr>
														<tr style="text-transform: none;">
															<td><b>Fecha Suscripción:</b></td>
															<td>
																<?php echo $fecha_suscripcion_proveedor; ?>
															</td>
														</tr>
														<tr style="text-transform: none;">
															<td><b>Fecha Vencimiento:</b></td>
															<td>
																<?php echo $fecha_vencimiento_proveedor; ?>
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
												
												<button type="button" class="btn btn-info btn-xs btn-block" id="btn_guardar_contrato_firmado" onclick="$(location).attr('href','?sec_id=contrato&sub_sec_id=servicio');" style="margin-top: 10px; margin-bottom: 10px;">
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
																	style="height: 34px;"
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
																	style="height: 34px;"
																	>
																<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param"></label>
															</div>
														</div>
													</div>
													
													

													<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
														<div style="margin-right: 10px; margin-left: 5px;">
															<div class="form-group">
																<div class="control-label">Seleccione el contrato firmado:</div>
																<input type="file" id="archivo_contrato_proveedor" name="archivo_contrato_proveedor" required accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip">

															</div>
														</div>
													</div>

													<div class="col-xs-12 col-md-12 col-lg-12 item_filter" style="margin-bottom: 30px">
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
											}
										?>
										
									</div>
									<!-- /Panel Body -->

								</div>
								<!-- /COLLAPSE: This Week -->

							</div>
							<!-- PANEL: CONTRATO FIRMADO -->
							<?php 
								} 
							?>


							<?php 
								if ($area_id != '33') // LEGAL PRODUCCION = 33, LEGAL DESARROLLO = 26 O 33  
								{ 
							?>
							<div class="panel">

								<!-- Panel Heading -->
								<div class="panel-heading" role="tab" id="browsers-this-day-heading">

									<!-- Panel Title -->
									<div class="panel-title">
										<a href="#browsers-this-day" class="collapsed" role="button" data-toggle="collapse"
										   data-parent="#accordion" aria-expanded="true" aria-controls="browsers-this-day">

											Contrato final
										</a>
									</div>
									<!-- /Panel Title -->

								</div>
								<!-- /Panel Heading -->

								<!-- COLLAPSE: This Week -->
								<div id="browsers-this-day" class="panel-collapse collapse" role="tabpanel"
									 aria-labelledby="browsers-this-day-heading">

									<!-- Panel Body -->
									<div class="panel-body">
										
										<?php 

											$sel_query_info_contrato_firmado_proveedor = $mysqli->query("
											SELECT
												c.contrato_id, c.tipo_contrato_id, tc.nombre AS tipo_contrato,
												c.empresa_suscribe_id, trs.nombre AS razon_social,
												tf.nombre AS tipo_firma, c.fecha_suscripcion_proveedor,
    											c.fecha_inicio, c.fecha_vencimiento_proveedor,
												concat( IFNULL(tpa.nombre, ''),' ', IFNULL(tpa.apellido_paterno, ''),' ', IFNULL(tpa.apellido_materno, '')) AS usuario_aprobado,
												concat( IFNULL(tpc.nombre, ''),' ', IFNULL(tpc.apellido_paterno, ''),' ', IFNULL(tpc.apellido_materno, '')) AS usuario_creado
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

													$fecha_vencimiento_proveedor = date("d-m-Y", strtotime($sel["fecha_vencimiento_proveedor"]));
												?>

												<table class="table table-responsive table-hover no-mb" style="font-size: 10px;">
													<tbody>
														<tr style="text-transform: none;">
															<td><b>Tipo de contrato:</b></td>
															<td>
																<?php echo $tipo_contrato; ?>
															</td>
														</tr>
														<tr style="text-transform: none;">
															<td><b>Razon social:</b></td>
															<td>
																<?php echo $razon_social; ?>
															</td>
														</tr>
														<tr style="text-transform: none;">
															<td><b>Contrato aprobado por:</b></td>
															<td>
																<?php echo $usuario_aprobado; ?>
															</td>
														</tr>
														<tr style="text-transform: none;">
															<td><b>Contrato creado por:</b></td>
															<td>
																<?php echo $usuario_creado; ?>
															</td>
														</tr>
														<tr style="text-transform: none;">
															<td><b>Tipo Firma:</b></td>
															<td>
																<?php echo $tipo_firma; ?>
															</td>
														</tr>
														<tr style="text-transform: none;">
															<td><b>Fecha Inicio:</b></td>
															<td>
																<?php echo $fecha_inicio; ?>
															</td>
														</tr>
														<tr style="text-transform: none;">
															<td><b>Fecha Suscripción:</b></td>
															<td>
																<?php echo $fecha_suscripcion_proveedor; ?>
															</td>
														</tr>
														<tr style="text-transform: none;">
															<td><b>Fecha Vencimiento:</b></td>
															<td>
																<?php echo $fecha_vencimiento_proveedor; ?>
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
												?>
												<table>
													<thead>Aún no se carga el contrato firmado al sistema.</thead>
												</table>
												<?php
											}
											
											
										?>
										
									</div>
									<!-- /Panel Body -->

								</div>
								<!-- /COLLAPSE: This Week -->

							</div>
							<!-- PANEL: CONTRATO FIRMADO -->
							<?php 
								} 
							?>



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
									$sel_query = $mysqli->query("SELECT c.estado_solicitud FROM cont_contrato c WHERE c.contrato_id = $contrato_id");
									while($sel=$sel_query->fetch_assoc()){
										$estado_solicitud = $sel["estado_solicitud"];
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
													<option value="0">No aplica</option>
												</select>
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
									</table>
								</div>
							</div>
						</div>
						<!-- FIN PANEL ESTADO DE SOLICITUD -->
						<?php
						}		
						?>

						</div>
						<!-- /PANEL-GROUP: Browsers -->

					</div>
					<!-- /Panel Body -->

				</div>
				<!-- /PANEL: Horizontal Form -->

			</div>

			<?php 
				if($check_gerencia_proveedor == 1)
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