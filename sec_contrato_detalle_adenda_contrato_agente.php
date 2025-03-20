<?php
global $mysqli;

$menu_id = "";
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'contrato' AND sub_sec_id = 'nuevo' LIMIT 1");
while ($r = $result->fetch_assoc())
	$menu_id = $r["id"];
	$usuario_id = $login?$login['id']:null;

$adenda_id = $_GET['id'];

$query = "SELECT a.contrato_id FROM cont_adendas a WHERE a.id = ".$adenda_id;
$list_query = $mysqli->query($query);
if ($list_query->num_rows > 0) {
$row = $list_query->fetch_assoc();
$contrato_id = $row['contrato_id'];

$query = "SELECT c.ruc AS id, c.razon_social as nombre_proveedor FROM cont_contrato c WHERE contrato_id = ".$contrato_id;
$list_query = $mysqli->query($query);
$row = $list_query->fetch_assoc();
$nombre_proveedor = $row["nombre_proveedor"];

$query = $mysqli->query("
	SELECT 
		c.tipo_contrato_id,
		c.empresa_suscribe_id,
		r.nombre AS empresa_suscribe,
		c.ruc,
		c.razon_social,
		c.vigencia,
		c.dni_representante,
		CONCAT(IFNULL(p2.nombre, ''),' ',IFNULL(p2.apellido_paterno, ''),' ',IFNULL(p2.apellido_materno, '')) AS nombre_supervisor,
		c.observaciones,
		c.user_created_id,
		c.detalle_servicio,
		c.periodo_numero,
		c.periodo,
		pd.nombre AS periodo_anio_mes,
		c.fecha_inicio,
		CONCAT(IFNULL(p.nombre, ''),' ',IFNULL(p.apellido_paterno, ''),' ',IFNULL(p.apellido_materno, '')) AS user_created,
		c.created_at,
		c.tipo_terminacion_anticipada_id,
		t.nombre AS tipo_terminacion_anticipada,
		c.terminacion_anticipada,
		c.alcance_servicio,
		c.nombre_agente,
		c.c_costos,
		c.fecha_inicio_agente,
		c.fecha_fin_agente,
		c.plazo_id_agente,
		c.fecha_suscripcion_contrato,
		ctp.nombre  as vigencia
	FROM
		cont_contrato c
		INNER JOIN cont_periodo pd ON c.periodo = pd.id
		INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id
		INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
		INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		LEFT JOIN tbl_usuarios u2 ON c.persona_responsable_id = u2.id
		LEFT JOIN tbl_personal_apt p2 ON u2.personal_id = p2.id
		LEFT JOIN cont_tipo_plazo ctp ON ctp .id =c.plazo_id_agente 

		LEFT JOIN cont_tipo_terminacion_anticipada t ON c.tipo_terminacion_anticipada_id = t.id
	WHERE 
		c.contrato_id = $contrato_id
");
$row = $query->fetch_assoc();
$empresa_suscribe = $row["empresa_suscribe"];
$tipo_contrato_id = $row["tipo_contrato_id"]; 

$ruc = $row["ruc"];
$razon_social = $row["razon_social"];
$nombre_supervisor = $row["nombre_supervisor"];

$detalle_servicio = $row["detalle_servicio"];
$periodo_numero = $row["periodo_numero"];
$periodo_anio_mes = $row["periodo_anio_mes"];
$observaciones = $row["observaciones"];
if($row["periodo"]=='1'){													
	$periodo = 'Año(s)';
}else{
	$periodo = 'Mes(es)';
}

$fecha_suscripcion_contrato = $row["fecha_suscripcion_contrato"]!=null? date("d-m-Y", strtotime($row["fecha_suscripcion_contrato"])):null;
$nombre_agente = $row["nombre_agente"];
$c_costos = $row["c_costos"];
$fecha_inicio_agente = ($row["fecha_inicio_agente"]!=null)?date("d-m-Y", strtotime($row["fecha_inicio_agente"])):null;
$fecha_fin_agente= $row["fecha_fin_agente"]!=null? date("d-m-Y", strtotime($row["fecha_fin_agente"])):null;
$plazo_id_agente = $row["plazo_id_agente"];
$vigencia = $row["vigencia"];

$date = date_create($row["fecha_inicio"]);
$fecha_inicio = date_format($date, "d-m-Y");

$alcance_servicio = $row["alcance_servicio"];
$tipo_terminacion_anticipada_id = $row["tipo_terminacion_anticipada_id"];
$tipo_terminacion_anticipada = $row["tipo_terminacion_anticipada"];
$terminacion_anticipada = $row["terminacion_anticipada"];

$repre_query = $mysqli->query("
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
	WHERE pr.contrato_id IN ($contrato_id)"
);

include("sys/function_replace_invalid_caracters_contratos.php");
?>
<style>
	.campo_obligatorio{
		font-size: 15px;
		color: red;
	}

	.campo_obligatorio_v2{
		font-size: 13px;
		color: red;
	}
</style>


<div id="div_sec_contrato_nuevo">

	<div id="loader_"></div>

	<div class="row">
		<div class="col-xs-12 text-center">
			<h1 class="page-title titulosec_contrato"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Editar Solicitud - Adenda de Contrato de Agentes</h1>
		</div>
	</div>


	<div class="row">
		<div class="col-xs-12 col-md-12 col-lg-12">
			<!-- PANEL: Tipo contrato -->
			<div class="panel">
				<div class="panel-heading">
					<div class="panel-title">Editar Solicitud</div>
				</div>
				<div class="panel-body">
					<form id="form_contrato_interno" name="form_contrato_interno" method="POST" enctype="multipart/form-data" autocomplete="off">
						<input type="hidden" name="usuario_id_temporal" id="usuario_id_temporal" value="<?php echo $usuario_id;?>">
						<input type="hidden" name="tipo_contrato_id"  id="tipo_contrato_id" value="2">

						<div class="row">
							<div class="col-xs-12 col-md-5 col-lg-5">
								<div class="form-group">
									<label for="sec_con_nuevo_proveedor">Nombre del Proveedor:</label>
									<input disabled class="form-control" value="<?=$nombre_proveedor?>">
									
								</div>
							</div>
						
							<div class="col-xs-12 col-md-7 col-lg-7">
								<div class="form-group">
									<label for="exampleInputEmail1">Contrato</label>
									<input disabled class="form-control" value="<?=$fecha_inicio.' - '.$detalle_servicio?>">
									<input type="hidden" id="sec_con_nuevo_contrato_id" value="<?=$contrato_id?>" >
									<input type="hidden" id="sec_con_nuevo_adenda_id" value="<?=$adenda_id?>" >
									
								</div>
							</div>
						</div>

						
						<div class="row">
							<div class="col-md-12">
								<br>
							</div>
							<div class="col-xs-12 col-md-12 col-lg-7">
								<div id="div_contrato_interno">
									<div class="panel">
										<div class="panel-heading">
											<div class="panel-title">DETALLE DE LA SOLICITUD DE CONTRATO DE AGENTE</div>
											<input type="hidden" value="<?=$contrato_id?>" id="id_registro_contrato_id">
											<input type="hidden" value="<?=$tipo_contrato_id?>" id="id_tipo_contrato">
										</div>
										<div class="panel-body">
											<form>
												<div class="col-xs-12 col-md-12 col-lg-12">
													<div class="h4"><b>DATOS GENERALES</b></div>
												</div>
												<div class="col-xs-12 col-md-12 col-lg-12">
													<div class="form-group">
														<table class="table table-bordered table-hover">
															<tr>
																<td style="width: 50%;"><b>Empresa Arrendataria</b></td>
																<td><?=$empresa_suscribe ?></td>
																<td style="width: 75px;">
																<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_cont_agente_solicitud_editar_campo_adenda('Datos generales','cont_contrato','empresa_suscribe_id','Empresa Arrendataria','select_option','<?=$empresa_suscribe ?>','obtener_empresa_at','');">
																	<span class="fa fa-edit"></span> Editar</a>
																</td>
															</tr>
															<tr>
																<td style="width: 50%;"><b>Supervisor</b></td>
																<td><?=$nombre_supervisor ?></td>
																<td style="width: 75px;">
																<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_cont_agente_solicitud_editar_campo_adenda('Datos Generales','tbl_personal_apt','nombre_supervisor','Supervisor','select_option','<?=$nombre_supervisor ?>','obtener_personal_responsable_agente','');">
																	<span class="fa fa-edit"></span> Editar</a>
																</td>
															</tr>
														</table>
													</div>
												</div>

												<div class="col-xs-12 col-md-12 col-lg-12">
													<br>
												</div>

												<div class="col-xs-12 col-md-12 col-lg-12">
													<br>
												</div>

												<div class="col-xs-12 col-md-12 col-lg-12">
													<div class="h4"><b>DATOS DEL PROPIETARIO</b>
														<button type="button" class="btn btn-sm btn-info" onclick="sec_con_detalle_aden_cont_agente_agregar_representante()">
															<i class="fa fa-plus"></i> Agregar Propietario
														</button>
													</div>
												</div>
												<?php 
												$row_count = $repre_query->num_rows;
												$index = 1;
												if ($row_count > 0) {
													while($sel=$repre_query->fetch_assoc()){
												?>
												<div class="col-xs-12 col-md-12 col-lg-12">
													<div class="w-100">
														<b>Propietario # <?=$index?></b>
													</div>
													<div class="form-group">
														<table class="table table-bordered table-hover">

															<tr>
																<td><b>Tipo de Persona</b></td>
																<td><?=$sel["tipo_persona"] ?></td>
																<td style="width: 75px;">
																	<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_cont_agente_solicitud_editar_campo_adenda('Datos del Propietario','cont_tipo_persona','tipo_persona','Tipo de Persona','select_option','<?=$sel['tipo_persona']?>','obtener_tipo_persona','');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															</tr>

															<tr>
																<td><b>Nombre del Propietario</b></td>
																<td><?=$sel["nombre"] ?></td>
																<td style="width: 75px;">
																	<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_cont_agente_solicitud_editar_campo_adenda('Datos del Propietario','cont_persona','nombre','Nombre del Propietario','varchar','<?=$sel['nombre']?>','','');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															</tr>

															<tr>
																<td><b>Tipo de Documento de Identidad</b></td>
																<td><?=$sel["tipo_docu_identidad"] ?></td>
																<td style="width: 75px;">
																	<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_cont_agente_solicitud_editar_campo_adenda('Datos del Propietario','cont_tipo_docu_identidad','nombre','Tipo de Documento de Identidad','select_option','<?=$sel['tipo_docu_identidad']?>','obtener_tipo_documento_identidad','');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															</tr>

															<tr>
																<td><b>Número de DNI</b></td>
																<td><?=$sel["num_docu"] ?></td>
																<td style="width: 75px;">
																	<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_cont_agente_solicitud_editar_campo_adenda('Datos del Propietario','cont_persona','num_docu','Número de DNI','varchar','<?=$sel['num_docu']?>','','<?=$sel['id']?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															</tr>

															<tr>
																<td><b>Número de RUC</b></td>
																<td><?=$sel["num_ruc"] ?></td>
																<td style="width: 75px;">
																	<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_cont_agente_solicitud_editar_campo_adenda('Datos del Propietario','cont_persona','num_ruc','Número de RUC','varchar','<?=$sel['num_ruc']?>','','<?=$sel['id']?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															</tr>

															<tr>
																<td><b>Domicilio del propietario</b></td>
																<td><?=$sel["direccion"] ?></td>
																<td style="width: 75px;">
																	<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_cont_agente_solicitud_editar_campo_adenda('Datos del Propietario','cont_persona','direccion','Domicilio','varchar','<?=$sel['direccion']?>','','<?=$sel['id']?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															</tr>

															<tr>
																<td><b>Representante Legal</b></td>
																<td><?=$sel["representante_legal"] ?></td>
																<td style="width: 75px;">
																	<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_cont_agente_solicitud_editar_campo_adenda('Datos del Propietario','cont_persona','representante_legal','Representante Legal','varchar','<?=$sel['representante_legal']?>','','<?=$sel['id']?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															</tr>

															<tr>
																<td><b>N° de Partida Registral de la empresa</b></td>
																<td><?=$sel["num_partida_registral"] ?></td>
																<td style="width: 75px;">
																	<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_cont_agente_solicitud_editar_campo_adenda('Datos del Propietario','cont_persona','dni_representante','N° de Partida Registral de la Empresa','varchar','<?=$sel['num_partida_registral']?>','','<?=$sel['id']?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															</tr>

															<tr>
																<td><b>Persona de contacto</b></td>
																<td><?=$sel["contacto_nombre"] ?></td>
																<td style="width: 75px;">
																	<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_cont_agente_solicitud_editar_campo_adenda('Datos del Propietario','cont_persona','contacto_nombre','Persona de contacto','varchar','<?=$sel['contacto_nombre']?>','','<?=$sel['id']?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															</tr>

															<tr>
																<td><b>Teléfono de la persona de contacto</b></td>
																<td><?=$sel["contacto_telefono"] ?></td>
																<td style="width: 75px;">
																	<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_cont_agente_solicitud_editar_campo_adenda('Datos del Propietario','cont_persona','contacto_telefono','Teléfono de la persona de contacto','varchar','<?=$sel['contacto_telefono']?>','','<?=$sel['id']?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															</tr>

															<tr>
																<td><b>E-mail de la persona de contacto</b></td>
																<td><?=$sel["contacto_email"] ?></td>
																<td style="width: 75px;">
																	<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_cont_agente_solicitud_editar_campo_adenda('Datos del Propietario','cont_persona','contacto_email','E-mail de la persona de contacto','varchar','<?=$sel['contacto_email']?>','','<?=$sel['id']?>');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															</tr>
													
														</table>
													</div>
												</div>
												<?php
												$index++;
													}
												}else{
												?>

												<div class="col-xs-12 col-md-12 col-lg-12">
													<div class="h4"><b>DATOS DEL AGENTE</b></div>
												</div>
												<div class="col-xs-12 col-md-12 col-lg-12">
													<div class="form-group">
														<table class="table table-bordered table-hover">
															<tr>
																<td style="width: 50%;"><b>Empresa Arrendataria</b></td>
																<td><?=$empresa_suscribe ?></td>
																<td style="width: 75px;">
																<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_cont_agente_solicitud_editar_campo_adenda('Datos del Agente','cont_contrato','empresa_suscribe_id','Empresa Arrendataria','select_option','<?=$empresa_suscribe ?>','obtener_empresa_at','');">
																	<span class="fa fa-edit"></span> Editar</a>
																</td>
															</tr>
															<tr>
																<td style="width: 50%;"><b>Supervisor</b></td>
																<td><?=$nombre_supervisor ?></td>
																<td style="width: 75px;">
																<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_cont_agente_solicitud_editar_campo_adenda('Datos del Agente','cont_contrato','nombre_supervisor','Supervisor','varchar','<?=$nombre_supervisor ?>','','');">
																	<span class="fa fa-edit"></span> Editar</a>
																</td>
															</tr>
														</table>
													</div>
												</div>
												<!--
												<div class="col-xs-12 col-md-12 col-lg-12">
													<div class="w-100">
														<b>Representante Legal</b>
													</div>
													<div class="form-group">
														<table class="table table-bordered table-hover">';
														
														<tr>
																<td><b>DNI del representante legal</b></td>
																<td><?=$dni_representante ?></td>
																<td style="width: 75px;">
																	<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_cont_agente_solicitud_editar_campo_adenda('Información del proveedor','cont_contrato','dni_representante','DNI del representante legal','varchar','<?=$dni_representante ?>','','');">
																	<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															</tr>

															<tr>
																<td><b>Nombre completo del representante legal</b></td>
																<td><?=$nombre_representante ?></td>
																<td style="width: 75px;">
																	<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_cont_agente_solicitud_editar_campo_adenda('Información del proveedor','cont_contrato','nombre_representante','Nombre completo del representante legal','varchar','<?=$nombre_representante ?>','','');">
																	<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															</tr>
														</table>
													</div>
												</div>
												-->
												<?php
												}
												?>
												
												<div class="col-xs-12 col-md-12 col-lg-12">
												<br>
												</div>

												<div class="col-xs-12 col-md-12 col-lg-12">
													<div class="h4"><b>PLAZOS</b></div>
												</div>

												<div class="col-xs-12 col-md-12 col-lg-12">
													<div class="form-group">
														<table class="table table-bordered table-hover">
															<tr>
																<td style="width: 50%;"><b>Vigencia</b></td>
																<td>
																	<?php echo ($plazo_id_agente==1 || $plazo_id_agente==2)?$vigencia:'Definida' ?>
																</td>
																<td style="width: 75px;">
																	<a class="btn btn-success btn-xs" 
																		onclick="sec_con_detalle_aden_cont_agente_solicitud_editar_campo_adenda('Plazos','cont_tipo_plazo','nombre','Vigencia','select_option','<?=$vigencia ?>','obtener_tipo_plazo_vigencia','');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															</tr>
															<tr>
																<td style="width: 50%;"><b>Cantidad</b></td>
																<td><?=$periodo_numero ?></td>
																<td style="width: 75px;">
																	<a class="btn btn-success btn-xs" 
																		onclick="sec_con_detalle_aden_cont_agente_solicitud_editar_campo_adenda('Plazos','cont_contrato','periodo_numero','Cantidad de Plazos','int','<?=$periodo_numero ?>','','');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															</tr>
															<tr>
																<td style="width: 50%;"><b>Años o Meses</b></td>
																<td><?php echo $periodo;?></td>
																<td style="width: 75px;">
																	<a class="btn btn-success btn-xs" 
																		onclick="sec_con_detalle_aden_cont_agente_solicitud_editar_campo_adenda('Plazos','cont_periodo','nombre','Años o Meses','select_option','<?=$periodo ?>','obtener_tipo_plazo','');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															</tr>
															<tr>
																<td style="width: 50%;"><b>Fecha Inicio:</b></td>
																<td><?=$fecha_inicio_agente ?></td>
																<td style="width: 75px;">
																	<a class="btn btn-success btn-xs" 
																		onclick="sec_con_detalle_aden_cont_agente_solicitud_editar_campo_adenda('Plazos','cont_contrato','fecha_inicio_agente','Fecha de Inicio','date','<?=$fecha_inicio_agente ?>','','');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															</tr>
															<tr>
																<td style="width: 50%;"><b>Fecha Fin:</b></td>
																<td><?=$fecha_fin_agente ?></td>
																<td style="width: 75px;">
																	<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_cont_agente_solicitud_editar_campo_adenda('Plazos','cont_contrato','fecha_fin_agente','Fecha de Fin','date','<?=$fecha_fin_agente ?>','','');">
																		<span class="fa fa-edit"></span> Editar
																	</a>
																</td>
															</tr>
														</table>
													</div>
												</div>

												<div class="col-xs-12 col-md-12 col-lg-12">
													<div class="h4"><b>NOMBRE DEL AGENTE</b></div>
												</div>

												<div class="col-xs-12 col-md-12 col-lg-12">
													<div class="form-group">
														<table class="table table-bordered table-hover">
															<tr>
																<td style="width: 50%;"><b>Agente AT</b></td>
																<td><?=$nombre_agente ?></td>
																<td style="width: 75px;">
																<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_cont_agente_solicitud_editar_campo_adenda('Nombre del Agente','cont_contrato','nombre_agente','Agente AT','varchar','<?=$nombre_agente ?>','','');">
																	<span class="fa fa-edit"></span> Editar</a>
																</td>
															</tr>
														</table>
													</div>
												</div>

												<div class="col-xs-12 col-md-12 col-lg-12">
													<div class="h4"><b>CENTRO DE COSTOS</b></div>
												</div>
												<div class="col-xs-12 col-md-12 col-lg-12">
													<div class="form-group">
														<table class="table table-bordered table-hover">
															<tr>
																<td style="width: 50%;"><b>Centro de Costos</b></td>
																<td><?=$c_costos ?></td>
																<td style="width: 75px;">
																<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_cont_agente_solicitud_editar_campo_adenda('Centro de Costos','cont_contrato','c_costos','Centro de Costos','int','<?=$c_costos ?>','','');">
																	<span class="fa fa-edit"></span> Editar</a>
																</td>
															</tr>
														</table>
													</div>
												</div>

												<div class="col-xs-12 col-md-12 col-lg-12">
													<div class="h4"><b>FECHA DE SUSCRIPCIÓN DEL CONTRATO</b></div>
												</div>
												<div class="col-xs-12 col-md-12 col-lg-12">
													<div class="form-group">
														<table class="table table-bordered table-hover">
															<tr>
																<td style="width: 50%;"><b>Fecha de suscripción del contrato</b></td>
																<td><?=$fecha_suscripcion_contrato ?></td>
																<td style="width: 75px;">
																<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_cont_agente_solicitud_editar_campo_adenda('Fecha de suscripción del contrato','cont_contrato','fecha_suscripcion_contrato','Fecha de suscripción del contrato','date','<?=$fecha_suscripcion_contrato ?>','','');">
																	<span class="fa fa-edit"></span> Editar</a>
																</td>
															</tr>
														</table>
													</div>
												</div>

												<div class="col-xs-12 col-md-12 col-lg-12">
													<div class="h4"><b>OBSERVACIONES</b></div>
												</div>
												<div class="col-xs-12 col-md-12 col-lg-12">
													<div class="form-group">
														<table class="table table-bordered table-hover">
															<tr>
																<td style="width: 50%;"><b>Observaciones</b></td>
																<td><?=$observaciones ?></td>
																<td style="width: 75px;">
																<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_cont_agente_solicitud_editar_campo_adenda('Observaciones','cont_contrato','observaciones','Observaciones','varchar','<?= replace_invalid_caracters_vista($observaciones) ?>','','');">
																	<span class="fa fa-edit"></span> Editar</a>
																</td>
															</tr>
														</table>
													</div>
												</div>
											</form>
										</div>
									</div>

									<div class="col-xs-12 col-md-12 col-lg-12">
									<br>
									</div>

								</div>
							</div>

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

														<div class="form-group" style="margin-bottom: 10px; margin-top: 10px;" >

															<?php
															$campo_aprobacion_tooltip = '';
															$aprobacion_obligatoria_id = 1;
															$campo_aprobacion_mensaje = '<span class="campo_obligatorio_v2">(*)</span>';
															$query_directores = "SELECT user_id FROM cont_usuarios_directores WHERE status = 1";
															$sel_query = $mysqli->query($query_directores);
															while($sel=$sel_query->fetch_assoc()){
																if ($sel["user_id"] == $usuario_id) {
																	$campo_aprobacion_tooltip = ' data-toggle="tooltip" data-placement="left" title="Opcional para directores" ';
																	$campo_aprobacion_mensaje = '(Opcional)';
																	$aprobacion_obligatoria_id = 0;
																}
															}
															?>

															<input type="hidden" id="aprobacion_obligatoria_id" name="aprobacion_obligatoria_id" value="<?php echo $aprobacion_obligatoria_id; ?>">

															<div class="control-label" <?php echo $campo_aprobacion_tooltip; ?>>
																Aprobación de: <?php echo $campo_aprobacion_mensaje; ?>:
															</div>

															<div <?php echo $campo_aprobacion_tooltip; ?>>
																<select 
																	class="form-control input_text select2"
																	name="director_aprobacion_id" 
																	id="director_aprobacion_id" 
																	title="Seleccione a el director">
																</select>
															</div>
														</div>

														<button type="button" class="btn btn-success btn-xs btn-block" id="btnRegistrarAdenda" onclick="sec_con_detalle_aden_cont_agente_guardar_adenda();">
															<i class="icon fa fa-save"></i>
															<span id="demo-button-text">Modificar Solicitud de Adenda</span>
														</button>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>

						</div>
						
					</form>
				</div>
			</div>
			<!-- /PANEL: Tipo contrato -->
		</div>
	</div>
</div>


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
					<form id="form_adenda" autocomplete="off" >
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
											<div id="div_adenda_valor_int">
												<input type="text" id="adenda_valor_int" class="filtro txt_filter_style" style="width: 100%;">
											</div>
											<div id="div_adenda_valor_date">
												<input
												type="text"
												class="form-control sec_contrato_nuevo_datepicker"
												id="adenda_valor_date"
												value="<?php echo date("d-m-Y", strtotime("+1 days"));?>"
												readonly="readonly"
												style="height: 34px;"
												>
											</div>
											<div id="div_adenda_valor_decimal">
												<input type="text" id="adenda_valor_decimal" class="filtro txt_filter_style money" style="width: 100%;">
											</div>
											<div id="div_adenda_valor_select_option">
												<select  class="form-control" id="adenda_valor_select_option" name="adenda_valor_select_option">
												</select>
											</div>

											<div id="div_adenda_solicitud_departamento" class="col-xs-12 col-md-12 col-lg-12" >
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
													<select class="form-control select2"	name="adenda_inmueble_id_distrito" 
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
				<button type="button" class="btn btn-success" id="btnModalRegistrarAdenda" onclick="sec_con_detalle_aden_cont_agente_guardar_detalle_adenda('modalAgregar');">
					<i class="icon fa fa-plus"></i>
					<span id="demo-button-text">Agregar solicitud de edición a la Adenda</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL SOLICITUD DE ADENDA -->

<!-- INICIO MODAL NUEVO PROPIETARIO AP -->
<div id="modalNuevoPropietario" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_nuevo_propietario_titulo_ap">Adenda - Nuevo Propietario</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form autocomplete="off" id="frm_adenda_nuevo_propietario">
						<!-- TIPO DE PERSONA -->
						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Tipo de Persona: </div>
								<select class="form-control input_text select2" data-live-search="true" 
									name="modal_ade_cont_agente_tipo_persona" id="modal_ade_cont_agente_tipo_persona" title="Seleccione el Tipo de Persona">
									<?php 
									$banco_query = $mysqli->query("SELECT id, nombre FROM cont_tipo_persona WHERE estado = 1");
									while($row=$banco_query->fetch_assoc()){
										?>
										<option value="<?php echo $row["id"] ?>"><?php echo $row["nombre"] ?></option>
										<?php
									}
									?>
								</select>
							</div>
						</div>
						<!-- NOMBRE COMPLETO DEL NUEVO PROPIETARIO -->
						<div class="col-xs-12 col-md-6 col-lg-6">
							<div class="form-group">
								<div class="control-label">Nombre Completo del Nuevo Propietario:</div>
								<input type="text" name="modal_ade_cont_agente_nombre_nuevo_propietario" id="modal_ade_cont_agente_nombre_nuevo_propietario" maxlength=50 class="filtro"
								 style="width: 100%; height: 30px;">
							</div>
						</div>
						<!-- TIPO DE DOCUMENTO DE IDENTIDAD -->
						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Tipo de Documento de Identidad: </div>
								<select class="form-control input_text select2" data-live-search="true" 
									name="modal_ade_cont_agente_tipo_documento_identidad" id="modal_ade_cont_agente_tipo_documento_identidad" title="Seleccione el Tipo de Documento de Identidad">
									<?php 
									$banco_query = $mysqli->query("SELECT id, nombre FROM cont_tipo_docu_identidad WHERE estado = 1");
									while($row=$banco_query->fetch_assoc()){
										?>
										<option value="<?php echo $row["id"] ?>"><?php echo $row["nombre"] ?></option>
										<?php
									}
									?>
								</select>
							</div>
						</div>
						<!-- DNI -->
						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Número de DNI:</div>
								<input type="text" name="modal_ade_cont_agente_numero_dni" id="modal_ade_cont_agente_numero_dni"
								maxlength=12
								class="filtro" 
								style="width: 100%; height: 30px;"
								oninput="this.value=this.value.replace(/[^0-9]/g,'');"
								>
							</div>
						</div>
						<!-- RUC -->
						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Número de RUC:</div>
								<input type="text" name="modal_ade_cont_agente_numero_ruc" id="modal_ade_cont_agente_numero_ruc" maxlength=11 class="filtro"
								 style="width: 100%; height: 30px;">
							</div>
						</div>
						<!-- DOMICILIO DEL PROPIETARIO -->
						<div class="col-xs-12 col-md-6 col-lg-6">
							<div class="form-group">
								<div class="control-label">Domicilio del Propietario:</div>
								<input type="text" name="modal_ade_cont_agente_domicilio_propietario" id="modal_ade_cont_agente_domicilio_propietario" maxlength=50 class="filtro"
								 style="width: 100%; height: 30px;">
							</div>
						</div>
						<!-- REPRESENTANTE LEGAL -->
						<div class="col-xs-12 col-md-6 col-lg-6">
							<div class="form-group">
								<div class="control-label">Representante Legal:</div>
								<input type="text" name="modal_ade_cont_agente_representante_legal" id="modal_ade_cont_agente_representante_legal" maxlength=50 class="filtro"
								 style="width: 100%; height: 30px;">
							</div>
						</div>
						<!-- N° DE PARTIDA REGISTRAL DE LA EMPRESA -->
						<div class="col-xs-12 col-md-6 col-lg-6">
							<div class="form-group">
								<div class="control-label">N° de Partida Registral de la Empresa:</div>
								<input type="text" name="modal_ade_cont_agente_partida_registral" id="modal_ade_cont_agente_partida_registral" maxlength=50 class="filtro"
								 style="width: 100%; height: 30px;">
							</div>
						</div>
						<!-- PERSONA DE CONTACTO -->
						<div class="col-xs-12 col-md-6 col-lg-6">
							<div class="form-group">
								<div class="control-label">Persona de Contacto:</div>
								<input type="text" name="modal_ade_cont_agente_persona_contacto" id="modal_ade_cont_agente_persona_contacto" maxlength=50 class="filtro"
								 style="width: 100%; height: 30px;">
							</div>
						</div>
						<!-- TELEFONO DE LA PERSONA DE CONTACTO -->
						<div class="col-xs-12 col-md-6 col-lg-6">
							<div class="form-group">
								<div class="control-label">Telefóno de la Persona de Contacto:</div>
								<input type="text" name="modal_ade_cont_agente_telefono_persona_contacto" id="modal_ade_cont_agente_telefono_persona_contacto" maxlength=9 class="filtro"
								 style="width: 100%; height: 30px;">
							</div>
						</div>
						<!-- EMAIL DE LA PERSONA DE CONTACTO -->
						<div class="col-xs-12 col-md-6 col-lg-6">
							<div class="form-group">
								<div class="control-label">Email de la Persona de Contacto:</div>
								<input type="text" name="modal_ade_cont_agente_email_persona_contacto" id="modal_ade_cont_agente_email_persona_contacto" maxlength=50 class="filtro"
								 style="width: 100%; height: 30px;">
							</div>
						</div>
					</form>
				</div> 
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
				<button type="button" onclick="sec_con_detalle_aden_cont_agente_guardar_nuevo_propietario()" class="btn btn-success" >
					<i class="icon fa fa-plus"></i>
					Agregar Representante Legal
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL NUEVO PROPIETARIO AP -->


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
				<button type="button" class="btn btn-success" onclick="sec_con_detalle_aden_prov_nuevo_contraprestacion()" id="btn_agregar_contraprestacion_ap">
					<i class="icon fa fa-plus"></i>
					Agregar Contraprestación
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL NUEVO PROPIETARIO AP -->


<?php 
}else{
	echo "No existe ningun registro.";
}
?>
