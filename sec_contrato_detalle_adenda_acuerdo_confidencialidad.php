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

$list_emp_cont = $mysqli->query("SELECT tpg.valor FROM tbl_parametros_generales tpg WHERE tpg.codigo = 'empresa_contacto_de_contratos' AND tpg.estado = 1 LIMIT 1");
$row_emp_cont = $list_emp_cont->fetch_assoc();
$valor_empresa_contacto = isset($row_emp_cont['valor']) && $row_emp_cont['valor'] == 'Kurax' ? '(Kurax)':'(AT)';

$sel_query = $mysqli->query("SELECT
    c.empresa_suscribe_id,
    rs1.nombre AS empresa_at1,
    rs2.nombre AS empresa_at2,
    c.plazo_id,
    tp.nombre AS plazo,
    p.nombre AS periodo,
    c.periodo_numero,
    concat(IFNULL(per.nombre, ''),' ', IFNULL(per.apellido_paterno, '')) AS usuario_creacion,
    per.correo AS usuario_creacion_correo,
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
    peg.correo AS email_del_gerente_area,
	CONCAT(IFNULL(pab.nombre, ''),' ',IFNULL(pab.apellido_paterno, ''),' ',IFNULL(pab.apellido_materno, '')) AS abogado
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
WHERE c.tipo_contrato_id = 7 AND	c.contrato_id IN (" . $contrato_id . ")");

while($sel = $sel_query->fetch_assoc()){
    $empresa_at1 = $sel["empresa_at1"];
    $empresa_at2 = $sel["empresa_at2"];
    $usuario_creacion = $sel["usuario_creacion"];
    $created_at = $sel["created_at"];
    $observaciones = $sel["observaciones"];
	$abogado = $sel["abogado"];
    $periodo = $sel["periodo"];
    $gerente_area_id = trim($sel["gerente_area_id"]);
    
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
}

$query = $mysqli->query("
    SELECT 
		c.ruc,
		c.razon_social,
		c.nombre_comercial,
        c.empresa_suscribe_id,
        r.nombre AS empresa_suscribe,
        c.observaciones,
        c.persona_contacto_proveedor,
		c.detalle_servicio,
        c.user_created_id,
        CONCAT(IFNULL(p.nombre, ''),' ',IFNULL(p.apellido_paterno, ''),' ',IFNULL(p.apellido_materno, '')) AS user_created,
        c.created_at,
        CONCAT(IFNULL(peg.nombre, ''),' ',IFNULL(peg.apellido_paterno, ''),' ',IFNULL(peg.apellido_materno, '')) AS nombre_del_gerente_area,
        peg.correo AS email_del_gerente_area,
        c.cancelado_id,
		c.fecha_inicio
    FROM
        cont_contrato c
        INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id
        INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
        INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
        LEFT JOIN tbl_usuarios ug ON c.gerente_area_id = ug.id
        LEFT JOIN tbl_personal_apt peg ON ug.personal_id = peg.id
    WHERE 
        c.contrato_id IN (" . $contrato_id . ")
");

$row = $query->fetch_assoc();
$ruc = $row["ruc"];
$razon_social = $row["razon_social"];
$nombre_comercial = $row["nombre_comercial"];
$empresa_suscribe = $row["empresa_suscribe"];
$persona_contacto_proveedor = $row["persona_contacto_proveedor"];
$detalle_servicio = $row["detalle_servicio"];
$user_created = $row["user_created"];
$created_at = $row["created_at"];
$cancelado_id = $row["cancelado_id"];
$date_fecha_inicio = date_create($row["fecha_inicio"]);
$fecha_inicio = date_format($date_fecha_inicio, "Y-m-d");

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

$cbancarias_query = $mysqli->query("SELECT 
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
			<h1 class="page-title titulosec_contrato"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Editar Solicitud - Adenda de Acuerdo de Confidencialidad</h1>
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
					<form id="form_acuerdo_confidencialidad" name="form_acuerdo_confidencialidad" method="POST" enctype="multipart/form-data" autocomplete="off">
						<input type="hidden" name="usuario_id_temporal" id="usuario_id_temporal" value="<?php echo $usuario_id;?>">
						<input type="hidden" name="tipo_contrato_id"  id="tipo_contrato_id" value="2">

						
						<div class="row">
							<div class="col-md-12">
								<br>
							</div>
							<div class="col-xs-12 col-md-12 col-lg-7">
								<div id="div_contrato_interno">
									<div class="panel">
										<div class="panel-heading">
											<div class="panel-title">DETALLE DE LA SOLICITUD DEL ACUERDO DE CONFIDENCIALIDAD</div>
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
																<td style="width: 50%;"><b>Empresa Contratante</b></td>
																<td><?=$empresa_suscribe ?></td>
																<td style="width: 75px;">
																<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_acu_confidencialidad_solicitud_editar_campo_adenda('Datos Generales','cont_contrato','empresa_suscribe_id','Empresa Contratante','select_option','<?= $empresa_suscribe; ?>','obtener_empresa_at', '');">
																	<span class="fa fa-edit"></span> Editar</a>
																</td>
															</tr>
                                                            <tr>
																<td style="width: 50%;"><b>Persona Contacto <?=$valor_empresa_contacto?></b></td>
																<td><?=$persona_contacto_proveedor ?></td>
																<td style="width: 75px;">
																<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_acu_confidencialidad_solicitud_editar_campo_adenda('Datos Generales','cont_contrato','persona_contacto_proveedor','Persona Contacto <?=$valor_empresa_contacto?>','varchar','<?= $persona_contacto_proveedor; ?>','','');">
																	<span class="fa fa-edit"></span> Editar</a>
																</td>
															</tr>

                                                            <?php if (empty($gerente_area_id)) { ?>
                                                            <tr>
																<td style="width: 50%;"><b>Responsable de Área</b></td>
																<td><?=$gerente_area_nombre ?></td>
																<td style="width: 75px;">
																<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_acu_confidencialidad_solicitud_editar_campo_adenda('Datos Generales','cont_contrato','gerente_area_nombre','Responsable de Área','varchar','<?= $gerente_area_nombre; ?>','','');">
																	<span class="fa fa-edit"></span> Editar</a>
																</td>
															</tr>
                                                            <tr>
																<td style="width: 50%;"><b>Responsable de Área (Email)</b></td>
																<td><?=$gerente_area_nombre ?></td>
																<td style="width: 75px;">
																<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_acu_confidencialidad_solicitud_editar_campo_adenda('Datos Generales','cont_contrato','gerente_area_email','Responsable de Área (Email)','varchar','<?= $gerente_area_email; ?>','','');">
																	<span class="fa fa-edit"></span> Editar</a>
																</td>
															</tr>
                                                            <?php } else { ?>
                                                            <tr>
																<td style="width: 50%;"><b>Responsable de Área</b></td>
																<td><?=$gerente_area_nombre ?></td>
																<td style="width: 75px;">
																<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_acu_confidencialidad_solicitud_editar_campo_adenda('Datos Generales','cont_contrato','gerente_area_id','Responsable de Área','select_option','<?= $gerente_area_nombre; ?>','obtener_gerentes','');">
																	<span class="fa fa-edit"></span> Editar</a>
																</td>
															</tr>
                                                            <?php }?>
															<tr>
																<td style="width: 50%;"><b>Abogado</b></td>
																<td><?=$abogado ?></td>
																<td style="width: 75px;">
																<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_acu_confidencialidad_solicitud_editar_campo_adenda('Datos Generales','cont_contrato','abogado_id','Abogado','select_option','<?= $abogado; ?>','obtener_abogados','');">
																	<span class="fa fa-edit"></span> Editar</a>
																</td>
															</tr>
                                                            <tr>
																<td style="width: 50%;"><b>Registrado por</b></td>
																<td><?=$user_created ?></td>
																<td style="width: 75px;"></td>
															</tr>
                                                            <tr>
																<td style="width: 50%;"><b>Fecha del Registro</b></td>
																<td><?=$created_at ?></td>
																<td style="width: 75px;"></td>
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

                                                <?php if ($cancelado_id == 1) {
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

													while($sel = $sel_query->fetch_assoc()) {
														$cancelado_por = $sel['cancelado_por'];
														$cancelado_el = $sel['cancelado_el'];
														$cancelado_motivo = $sel['cancelado_motivo'];
													}
													?>
                                                    
                                                    <div class="col-xs-12 col-md-12 col-lg-12">
													<div class="h4"><b>DATOS GENERALES</b></div>
												</div>

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

                                                <div class="col-xs-12 col-md-12 col-lg-12">
													<div class="h4"><b>DATOS DEL PROVEEDOR</b></div>
												</div>
												<div class="col-xs-12 col-md-12 col-lg-12">
													<div class="form-group">
														<table class="table table-bordered table-hover">
															<tr>
																<td style="width: 50%;"><b>RUC</b></td>
																<td><?=$ruc ?></td>
																<td style="width: 75px;">
																<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_acu_confidencialidad_solicitud_editar_campo_adenda('Datos del Proveedor','cont_contrato','ruc','RUC del Proveedor','varchar','<?= $ruc; ?>','', '');">
																	<span class="fa fa-edit"></span> Editar</a>
																</td>
															</tr>
															<tr>
																<td style="width: 50%;"><b>Razón Social</b></td>
																<td><?=$razon_social ?></td>
																<td style="width: 75px;">
																<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_acu_confidencialidad_solicitud_editar_campo_adenda('Datos del Proveedor','cont_contrato','razon_social','Razón Social','varchar','<?= $razon_social; ?>','', '');">
																	<span class="fa fa-edit"></span> Editar</a>
																</td>
															</tr>
															<tr>
																<td style="width: 50%;"><b>Nombre Comercial</b></td>
																<td><?=$nombre_comercial ?></td>
																<td style="width: 75px;">
																<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_acu_confidencialidad_solicitud_editar_campo_adenda('Datos del Proveedor','cont_contrato','nombre_comercial','Nombre Comercial','varchar','<?= $nombre_comercial; ?>','', '');">
																	<span class="fa fa-edit"></span> Editar</a>
																</td>
															</tr>
														</table>
													</div>
												</div>

												<div class="col-xs-12 col-md-12 col-lg-12">
													<div class="h4"><b>DATOS DEL PROVEEDOR - REPRESENTANTE LEGAL</b>
														<button type="button" class="btn btn-sm btn-info" onclick="sec_con_detalle_aden_acu_confidencialidad_agregar_representante()">
															<i class="fa fa-plus"></i> Agregar Representante Legal
														</button>
													</div>
												</div>
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
													rl.contrato_id = ".$contrato_id
												);
												$c = 0;
												$id_representante_legal = 0;
												$row_count = mysqli_num_rows($sel_query);
												$index = 1;
												if ($row_count > 0) {
													while($sel=$sel_query->fetch_assoc()){
												?>
												<div class="col-xs-12 col-md-12 col-lg-12">
													<div class="w-100">
														<b>Representante Legal # <?=$index?></b>
													</div>
													<div class="form-group">
														<table class="table table-bordered table-hover">
															<tr>
																<td style="width: 50%;"><b>DNI del Representante Legal</b></td>
																<td><?=$sel['dni_representante'] ?></td>
																<td style="width: 75px;">
																<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_acu_confidencialidad_solicitud_editar_campo_adenda('Datos del Proveedor','cont_representantes_legales','dni_representante','DNI del Representante Legal','varchar','<?= $sel['dni_representante']; ?>','', '');">
																	<span class="fa fa-edit"></span> Editar</a>
																</td>
															</tr>
															<tr>
																<td style="width: 50%;"><b>Nombre Completo del Representante Legal</b></td>
																<td><?=$sel['nombre_representante'] ?></td>
																<td style="width: 75px;">
																<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_acu_confidencialidad_solicitud_editar_campo_adenda('Datos del Proveedor','cont_representantes_legales','nombre_representante','Nombre Completo del Representante Legal','varchar','<?= $sel['nombre_representante']; ?>','', '');">
																	<span class="fa fa-edit"></span> Editar</a>
																</td>
															</tr>
															<tr>
																<td style="width: 50%;"><b>Banco</b></td>
																<td><?=$sel['banco_representante'] ?></td>
																<td style="width: 75px;">
																<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_acu_confidencialidad_solicitud_editar_campo_adenda('Datos del Proveedor','cont_representantes_legales','id_banco','Banco','select_option','<?= $sel['banco_representante']; ?>','obtener_bancos', '');">
																	<span class="fa fa-edit"></span> Editar</a>
																</td>
															</tr>
															<tr>
																<td style="width: 50%;"><b>N° de Cuenta:</b></td>
																<td><?=$sel['nro_cuenta'] ?></td>
																<td style="width: 75px;">
																<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_acu_confidencialidad_solicitud_editar_campo_adenda('Datos del Proveedor','cont_representantes_legales','nro_cuenta','N° de Cuenta','varchar','<?= $sel['nro_cuenta']; ?>','', '');">
																	<span class="fa fa-edit"></span> Editar</a>
																</td>
															</tr>
															<tr>
																<td style="width: 50%;"><b>N° de CCI:</b></td>
																<td><?=$sel['nro_cci'] ?></td>
																<td style="width: 75px;">
																<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_acu_confidencialidad_solicitud_editar_campo_adenda('Datos del Proveedor','cont_representantes_legales','nro_cci','N° de CCI','varchar','<?= $sel['nro_cci']; ?>','', '');">
																	<span class="fa fa-edit"></span> Editar</a>
																</td>
															</tr>
														</table>
													</div>
												</div>
												<?php
													$index++;
													}
												}
												?>
												<div class="col-xs-12 col-md-12 col-lg-12">
													<div class="h4"><b>CONDICIONES COMERCIALES</b></div>
												</div>
												<div class="col-xs-12 col-md-12 col-lg-12">
													<div class="form-group">
														<table class="table table-bordered table-hover">
															<tr>
																<td style="width: 50%;"><b>1) Objeto del Contrato</b></td>
																<td><?=$detalle_servicio ?></td>
																<td style="width: 75px;">
																<a class="btn btn-success btn-xs" 
																	onclick="sec_con_detalle_aden_acu_confidencialidad_solicitud_editar_campo_adenda('Condiciones Comerciales','cont_contrato','detalle_servicio','Objeto del Contrato','textarea','<?=$detalle_servicio ?>','<?php echo replace_invalid_caracters_vista($detalle_servicio); ?>','');">
																	<span class="fa fa-edit"></span> Editar</a>
																</td>
															</tr>
															<tr>
																<td style="width: 50%;"><b>2) Fecha de Inicio</b></td>
																<td><?=$fecha_inicio ?></td>
																<td style="width: 75px;">
																<a class="btn btn-success btn-xs" 
																onclick="sec_con_detalle_aden_acu_confidencialidad_solicitud_editar_campo_adenda('Condiciones Comerciales','cont_contrato','fecha_inicio','Fecha de Inicio','date','<?php echo $fecha_inicio; ?>','','');">
																	<span class="fa fa-edit"></span> Editar
																</a>
																</td>
															</tr>
															<tr>
																<td style="width: 50%;"><b>3) Observaciones</b></td>
																<td><?=$observaciones ?></td>
																<td style="width: 75px;">
																<a class="btn btn-success btn-xs" 
																onclick="sec_con_detalle_aden_acu_confidencialidad_solicitud_editar_campo_adenda('Observaciones','cont_contrato','observaciones','Observaciones','textarea','<?php echo $observaciones; ?>','','');">
																	<span class="fa fa-edit"></span> Editar
																</a>
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
														<input type="hidden" id="adenda_id" value="<?=$adenda_id?>">
														<input type="hidden" id="contrato_id" value="<?=$contrato_id?>">

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

														<button type="button" class="btn btn-success btn-xs btn-block" id="btnRegistrarAdenda" onclick="sec_con_detalle_aden_acu_confidencialidad_guardar_adenda();">
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
				<button type="button" class="btn btn-success" id="btnModalRegistrarAdenda" onclick="sec_con_detalle_aden_acu_confidencialidad_guardar_detalle_adenda('modalAgregar');">
					<i class="icon fa fa-plus"></i>
					<span id="demo-button-text">Agregar solicitud de edición a la Adenda</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL SOLICITUD DE ADENDA -->

<!-- INICIO MODAL NUEVO REPRESENTANTE LEGAL -->
<div id="modalNuevaCuentaBancaria" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_nuevo_representante_legal_titulo_ap">Adenda - Nuevo Representante Legal</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form autocomplete="off" id="frm_adenda_nuevo_representante_legal">
						<!-- DNI -->
						<div class="col-xs-12 col-md-6 col-lg-6">
							<div class="form-group">
								<div class="control-label">Número de DNI: </div>
								<input type="text" name="modal_ade_acu_confidencialidad_numero_dni" id="modal_ade_acu_confidencialidad_numero_dni"
								maxlength=8
								class="filtro" 
								style="width: 100%; height: 30px;"
								oninput="this.value=this.value.replace(/[^0-9]/g,'');"
								>
							</div>
						</div>
						<!-- NOMBRE REPRESENTANTE LEGAL -->
						<div class="col-xs-12 col-md-6 col-lg-6">
							<div class="form-group">
								<div class="control-label">Nombre Representante Legal:</div>
								<input type="text" name="modal_ade_acu_confidencialidad_representante_legal" id="modal_ade_acu_confidencialidad_representante_legal" maxlength=50 class="filtro"
								 style="width: 100%; height: 30px;">
							</div>
						</div>
						<!-- NOMBRE DEL BANCO -->
						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Banco: </div>
								<select class="form-control input_text select2" data-live-search="true" 
									name="modal_ade_acu_confidencialidad_banco" id="modal_ade_acu_confidencialidad_banco" title="Seleccione el Tipo de Banco">
									<?php 
									$banco_query = $mysqli->query("SELECT id, nombre FROM tbl_bancos WHERE estado = 1");
									while($row=$banco_query->fetch_assoc()){
										?>
										<option value="<?php echo $row["id"] ?>"><?php echo $row["nombre"] ?></option>
										<?php
									}
									?>
								</select>
							</div>
						</div>
						<!-- N° DE CUENTA -->
						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">N° de Cuenta:</div>
								<input type="text" name="modal_ade_acu_confidencialidad_n_cuenta" id="modal_ade_acu_confidencialidad_n_cuenta" maxlength=50 class="filtro" style="width: 100%; height: 30px;">
							</div>
						</div>
						<!-- N° DE CCI -->
						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">N° de CCI: </div>
								<input type="text" name="modal_ade_acu_confidencialidad_n_cci" id="modal_ade_acu_confidencialidad_n_cci" maxlength=50 class="filtro" style="width: 100%; height: 30px;">
							</div>
						</div>
					</form>
				</div> 
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
				<button type="button" onclick="sec_con_detalle_aden_acu_confidencialidad_guardar_nuevo_representante_legal()" class="btn btn-success" >
					<i class="icon fa fa-plus"></i>
					Agregar nuevo Representante Legal
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL NUEVO REPRESENTANTE LEGAL -->


<?php 
}else{
	echo "No existe ningun registro.";
}
?>
