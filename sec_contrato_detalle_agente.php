<?php
$this_menu = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' LIMIT 1")->fetch_assoc();
$menu_id = $this_menu["id"];

$menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' AND sub_sec_id = '$sub_sec_id' LIMIT 1")->fetch_assoc();
$menu_consultar = isset($menu_id_consultar["id"]) ? $menu_id_consultar["id"]:0;

if(!array_key_exists($menu_id,$usuario_permisos)){
	echo "No tienes permisos para este recurso.";
	die();
}

$permiso_editar_contrato_firmado = false;
if ((array_key_exists($menu_consultar,$usuario_permisos) && in_array("editar_contrato_firmado", $usuario_permisos[$menu_consultar]))) { 
	$permiso_editar_contrato_firmado = true;
}

$permiso_cambiar_estado_contrato = false;
if ((array_key_exists($menu_consultar,$usuario_permisos) && in_array("cambiar_estado_contrato", $usuario_permisos[$menu_consultar]))) { 
	$permiso_cambiar_estado_contrato = true;
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

$continuar = false;

$query_sql = "
SELECT 
	c.tipo_contrato_id,
	c.etapa_id,
	p.area_id,
	c.user_created_id,
	co.sigla AS sigla_correlativo,
	c.codigo_correlativo,
	c.cancelado_id,
	c.fecha_aprobacion
FROM
	cont_contrato c
	LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
	LEFT JOIN tbl_usuarios u ON c.user_created_id = u.id
	LEFT JOIN tbl_personal_apt p ON u.personal_id = p.id
WHERE
	c.contrato_id = $contrato_id
";
$query = $mysqli->query($query_sql);
$row = $query->fetch_assoc();

if ($row["tipo_contrato_id"] == 6) {
	$continuar = true;
} else {
	echo 'En la presente página no se puede visualisar Contratos de Agentes';
}

if($continuar===true){

$area_created_id = $row["area_id"];
$etapa_id = $row["etapa_id"];
$sigla_correlativo = $row["sigla_correlativo"];
$codigo_correlativo = $row["codigo_correlativo"];
$user_created = $row["user_created_id"];
$cancelado_id = $row["cancelado_id"];
$fecha_aprobacion = $row["fecha_aprobacion"];

$area_id = $login ? $login['area_id'] : 0;
$cargo_id = $login ? $login['cargo_id'] : 0;
$usuario_id = $login ? $login['id']:null;
 

if ( ($etapa_id == 1 && $user_created == $usuario_id ) || ( $etapa_id == 1 && $area_id == 33) || ( $etapa_id == 1 && $area_id == 21) || $permiso_editar_contrato_firmado ) { // Producción $area_id == 21 &&
	$btn_editar_solicitud = true;
} else {
	$btn_editar_solicitud = false;
}

$permiso_reenviar_email_solicitud = false;
if ( (array_key_exists($menu_consultar,$usuario_permisos) && in_array("reenviar_email_solicitud", $usuario_permisos[$menu_consultar])) && !is_null($fecha_aprobacion)) { 
	$permiso_reenviar_email_solicitud = true;
}

include '/var/www/html/sys/function_replace_invalid_caracters_contratos.php';

$tipo_archivo_id_mincetur = 163;
?>

<style type="text/css">
	.timeline>div {
		margin-bottom: 15px;
		position: relative;
	}

	.timeline>.time-label>span {
		border-radius: 4px;
		background-color: #fff;
		display: inline-block;
		font-weight: 600;
		padding: 5px;
	}

	.timeline>div>.timeline-item {
		box-shadow: 0 0 1px rgb(0 0 0 / 13%), 0 1px 3px rgb(0 0 0 / 20%);
		border-radius: .25rem;
		background-color: #fff;
		color: #495057;
		margin-top: 0;
		padding: 0;
		position: relative;
	}

	.timeline>div>.timeline-item>.timeline-header {
		border-bottom: 1px solid rgba(0,0,0,.125);
		border-top: 1px solid rgba(0,0,0,.125);
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

	.timeline>div>.timeline-item>.time {
		color: #707070;
		float: right;
		font-size: 12px;
		padding: 10px;
	}

	.timeline .timeline-item::before {
		content: none;
	}

</style>

<script type="text/javascript">
     function anular(e) {
          tecla = (document.all) ? e.keyCode : e.which;
          return (tecla != 13);
     }
	</script>

<input type="hidden" id="contrato_id_temporal" value="<?php echo $contrato_id; ?>">
<input type="hidden" id="tipo_contrato_id_temporal" value="6">
<input type="hidden" id="adenta_id_temporal" value="<?php echo $adenta_id_temporal; ?>">
<input type="hidden" id="resolucion_id_temporal" value="<?php echo $resolucion_id_temporal; ?>">

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
					<form id="frmBuscarRemitente" autocomplete="off" onkeypress="return anular(event)">

						<input type="hidden" id="modal_buscar_propietario_tipo_solicitud_ca" value="<?php echo $contrato_id ?>">

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
									<button type="button" class="btn btn-success btn-sm btn-block" id="btnBuscarPropietario_ca" onclick="sec_contrato_detalle_agente_buscar_propietario()">
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
								<button type="button" class="btn btn-success btn-sm btn-block" onclick="sec_contrato_detalle_solicitud_llamar_modal_agregar_propietario();">
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
				<button type="button" class="btn btn-success" id="sec_nuevo_modal_guardar_archivo" onclick="guardarNuevoTipoAnexoConProv(6)">
					<i class="icon fa fa-save"></i>
					<span>Guardar nuevo tipo de anexo</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL AGREGAR TIPO ANEXO -->

<div id="div_sec_contrato_nuevo_agente">
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
					<i class="icon icon-inline glyphicon glyphicon-briefcase"></i> <?php echo $etapa_id == 1 ? 'Solicitud de ' : '';?> Contrato de Agente - Código: <?php echo $sigla_correlativo; echo $codigo_correlativo; ?>
				</h1>
			</div>
		</div>

		<div class="col-xs-12 col-md-12 col-lg-7">
			<div class="panel" id="divDetalleSolicitud">
				<div class="panel-heading">
					<div class="panel-title" style="width: 300px; display: inline-block;">DETALLE DE LA SOLICITUD</div>
					<?php 
					if(($area_id == 33 && $cargo_id != 25) || $area_id == 21){
					?>
				 
					<?php 
					}
					?>
				</div>

				<div class="panel-body" style="padding: 0px; padding-top: 10px; font-size: 12px;">
					<form id="frmContratoDeArrendatario">

						<div class="w-100" style="padding-right: 5px;">
							<div class="col-xs-12 col-md-12 col-lg-12" style="padding-right: 0px; padding-left: 10px; margin-bottom: 20px;">
								<?php 
								if($etapa_id == 1 && $area_id == $area_created_id && $cancelado_id != 1) { ?>
									<a class="btn btn-danger btn-xs" onclick="sec_contrato_detalle_solicitud_cancelar_solicitud_modal(<?php echo $contrato_id; ?>);">
										<span class="fa fa-close"></span> Cancelar Solicitud
									</a>
								<?php } ?>
								<?php
								if ($permiso_reenviar_email_solicitud) {
								?>
								<a class="btn btn-info btn-xs" onclick="reenviar_solicitud_contrato_agente(<?php echo $contrato_id; ?>);">
									<span class="fa fa-envelope-o"></span> Reenviar por email a Todos
								</a>
								<?php 
								}
								?>
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
													c.created_at,
													c.fecha_suscripcion_contrato,
													c.periodo,
													c.periodo_numero,
													c.nombre_agente,
													c.c_costos,
													c.cc_id,
													c.fecha_inicio,
													c.fecha_inicio_agente,
													c.fecha_fin_agente,
									 				c.plazo_id_agente ,
									 				ctp.nombre  as vigencia,
													c.status,
													CONCAT(IFNULL(pab.nombre, ''),' ',IFNULL(pab.apellido_paterno, ''),' ',IFNULL(pab.apellido_materno, '')) AS abogado
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

													LEFT JOIN tbl_usuarios uab ON c.abogado_id = uab.id
													LEFT JOIN tbl_personal_apt pab ON uab.personal_id = pab.id

													LEFT JOIN cont_tipo_plazo ctp ON ctp .id =c.plazo_id_agente 
												WHERE 
													c.contrato_id IN (" . $contrato_id . ")");
												while($sel = $sel_query->fetch_assoc()){
													$empresa_suscribe = $sel["empresa_suscribe"];
													$nombre_tienda = $sel["nombre_tienda"];
													$observaciones = $sel["observaciones"];
													$supervisor = trim($sel["persona_responsable"]);
													$jefe_comercial = trim($sel["jefe_comercial"]);
													$usuario_created_id = $sel["user_created_id"];
													$verificar_giro = $sel["verificar_giro"];
													$abogado = $sel["abogado"];
													$fecha_suscripcion_contrato = $sel["fecha_suscripcion_contrato"];
													$periodo_numero = $sel["periodo_numero"];
													$periodo_tipo = $sel["periodo"];
													$fecha_inicio_agente = ($sel["fecha_inicio_agente"]!=null)?date("d-m-Y", strtotime($sel["fecha_inicio_agente"])):null;
													
													$fecha_vencimiento_indefinida = $sel['vigencia'];
													$plazo_id_agente = $sel['plazo_id_agente'];
													 
													$fecha_fin_agente= $sel["fecha_fin_agente"]!=null? date("d-m-Y", strtotime($sel["fecha_fin_agente"])):null;
													
													if($sel["periodo"]=='1'){
														
														$periodo = 'Año(s)';
													}else{
														$periodo = 'Mes(es)';
													}
													$nombre_agente = $sel["nombre_agente"];
													$c_costos = $sel["c_costos"];
													$fecha_verificacion_giro = $sel["fecha_verificacion_giro"];
													$usuario_verificacion_giro = $sel["persona_verificaciongiro"];
													$centro_de_costos = $sel["cc_id"];
													$estado_contrato = $sel["status"];
													$con_final_fecha_inicio = $sel["fecha_inicio"];
													$con_final_fecha_fin = "";
													$con_final_fecha_suscripcion = $sel["fecha_suscripcion_contrato"];

													if (!Empty($con_final_fecha_inicio)) {
														$con_final_fecha_inicio = date("d/m/Y", strtotime($con_final_fecha_inicio));
													}
													if (!Empty($con_final_fecha_suscripcion)) {
														$con_final_fecha_suscripcion = date("d/m/Y", strtotime($con_final_fecha_suscripcion));
													}


													if(empty($nombre_tienda)) {
														$nombre_tienda = 'Sin asignar';
													}

													if(empty($centro_de_costos)) {
														$centro_de_costos = 'Sin asignar';
													}

													if(empty($supervisor)) {
														$supervisor = 'Sin asignar';
													}

													if(empty($jefe_comercial)) {
														$jefe_comercial = 'Sin asignar';
													}

													
												?>
												<table class="table table-bordered table-hover">
													<!-- <tr>
														<td style="width: 250px;"><b>Nombre de la Tienda</b></td>
														<td><?php echo $nombre_tienda;?></td>
														<?php if ($btn_editar_solicitud || ($etapa_id == 1 && $area_id == 33)) { ?>
														<td style="width: 75px;">
															<a class="btn btn-success btn-xs"
																id="btn_editar_nombre_de_la_tienda"
																onclick="sec_contrato_detalle_agente_solicitud_editar_solicitud('Generales','cont_contrato','nombre_tienda','Nombre de la Tienda','varchar','<?php echo $nombre_tienda; ?>','');">
																<span class="fa fa-edit"></span> Editar
															</a>
														</td>
														<?php } ?>
													</tr> -->
													<tr>
														<td style="width: 250px;"><b>Empresa Arrendataria</b></td>
														<td><?php echo $empresa_suscribe;?></td>
														<?php if ($btn_editar_solicitud || ($etapa_id == 1 && $area_id == 33)) { ?>
														<td style="width: 75px;">
															<!-- <a class="btn btn-success btn-xs" 
															onclick="sec_contrato_detalle_agente_solicitud_editar_solicitud('Generales','cont_contrato','empresa_suscribe_id','Empresa Arrendataria','select_option','<?php echo $empresa_suscribe; ?>','obtener_empresa_at');">
																<span class="fa fa-edit"></span> Editar
															</a> -->
														</td>
														<?php } ?>
													</tr>
													<tr>
														<td><b>Supervisor</b></td>
														<td><?php echo $supervisor;?></td>
														<?php if ($btn_editar_solicitud || ($etapa_id == 1 && $area_id == 33) || (array_key_exists($menu_consultar,$usuario_permisos) && in_array("edit_super_jefe_come", $usuario_permisos[$menu_consultar]))) { ?>
														<td>
															<a 	class="btn btn-success btn-xs" 
																id="btn_editar_supervisor"
																onclick="sec_contrato_detalle_agente_solicitud_editar_solicitud('Generales','cont_contrato','persona_responsable_id','Supervisor','select_option','<?php echo $supervisor; ?>','obtener_personal_responsable');">
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
														<td style="width: 250px;"><b>Estado</b></td>
														<td style="color: red;"><b>Solicitud Cancelada</b></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td style="width: 87px;"></td>
														<?php } ?>
													</tr>

													<tr>
														<td><b>Cancelado por</b></td>
														<td><?php echo $cancelado_por; ?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td></td>
														<?php } ?>
													</tr>

													<tr>
														<td><b>Cancelado el</b></td>
														<td><?php echo $cancelado_el; ?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td></td>
														<?php } ?>
													</tr>

													<tr>
														<td><b>Motivo de la cancelación:</b></td>
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
								WHERE c.contrato_id = '". $contrato_id ."'
								");

							$cantReg = mysqli_num_rows($sel_query);

							while($sel=$sel_query->fetch_assoc())
							{
								$fecha_aprobacion = $sel["fecha_aprobacion"];
								$estado_aprobacion = $sel["estado_aprobacion"];
								$nombre_del_director_a_aprobar = $sel["nombre_del_director_a_aprobar"];
								$aprobado_por = $sel["aprobado_por"];
								$cargo_aprobante = $sel["cargo_aprobante"];
							}
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
														if(!is_null($fecha_aprobacion))
														{
															if($estado_aprobacion == 1)
															{
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
															onclick="sec_contrato_detalle_solicitud_editar_solicitud('Datos Generales','cont_contrato','cargo_aprobador_id','Cargo Aprobante','select_option','<?php echo $cargo_aprobante; ?>','obtener_cargos');">
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
								<div class="panel-heading" role="tab" id="headingDatosDelPropietario">
								<h4 class="panel-title">
									<a role="button" data-toggle="collapse" data-parent="#accordionContrato" href="#collapseDatosDelPropietario" aria-expanded="true" aria-controls="collapseDatosDelPropietario">
									DATOS DEL PROPIETARIO
									</a>
								</h4>
								</div>
								<div id="collapseDatosDelPropietario" class="panel-collapse-all panel-collapse collapse in" role="tabpanel" aria-labelledby="headingDatosDelPropietario">
									<div class="panel-body">
										<div class="w-100 text-right">
										<?php if ($btn_editar_solicitud) { ?> 
										<button 
											class="btn btn-primary btn-xs" 
											type="button" 
											onclick="sec_contrato_detalle_solicitud_llamar_modal_buscar_propietario();">
											<i class="fa fa-plus"></i> Agregar Propietarios
										</button>
										<?php } ?> 
										</div>

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
												WHERE pr.contrato_id IN (" . $contrato_id . ");");
												while($sel = $sel_query->fetch_assoc()){
													$tipo_docu_identidad_id = $sel["tipo_docu_identidad_id"];
													$tipo_docu_identidad = $sel["tipo_docu_identidad"];
													$num_docu_propietario = $sel["num_docu"];
													$num_ruc_propietario = $sel["num_ruc"];

													if(empty($num_ruc_propietario)) {
														$num_ruc_propietario = 'Sin asignar';
													}
												?>
												<table class="table table-bordered table-hover">
													<tr>
														<td style="width: 250px;"><b>Tipo de Persona</b></td>
														<td><?php echo $sel["tipo_persona"];?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td style="width: 75px;">
															<a class="btn btn-success btn-xs" 
															onclick="sec_contrato_detalle_agente_solicitud_editar_solicitud('Propietario','cont_persona','tipo_persona_id','Tipo de Persona','select_option','<?php echo $sel["tipo_persona"]; ?>','obtener_tipo_persona','<?php echo $sel["persona_id"];?>');">
																<span class="fa fa-edit"></span> Editar
															</a>
														</td>
														<?php } ?>
													</tr>
													<tr>
														<td><b>Nombre</b></td>
														<td><?php echo $sel["nombre"];?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td>
															<a class="btn btn-success btn-xs" 
															onclick="sec_contrato_detalle_agente_solicitud_editar_solicitud('Propietario','cont_persona','nombre','Tipo Nombre Persona','varchar','<?php echo $sel["nombre"]; ?>','','<?php echo $sel["persona_id"];?>');">
																<span class="fa fa-edit"></span> Editar
															</a>
														</td>
														<?php } ?>
													</tr>
													<tr>
														<td><b>Tipo de Documento de Identidad</b></td>
														<td><?php echo $tipo_docu_identidad;?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td>
															<a class="btn btn-success btn-xs" 
															onclick="sec_contrato_detalle_agente_solicitud_editar_solicitud('Propietario','cont_persona','tipo_docu_identidad_id','Tipo de Documento de Identidad','select_option','<?php echo $tipo_docu_identidad; ?>','obtener_tipo_docu_identidad','<?php echo $sel["persona_id"];?>');">
																<span class="fa fa-edit"></span> Editar
															</a>
														</td>
														<?php } ?>
													</tr>

													<?php if ($tipo_docu_identidad_id != "2") { ?>

													<tr>
														<td><b>Número de <?php echo $tipo_docu_identidad;?></b></td>
														<td><?php echo $num_docu_propietario;?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td>
															<a class="btn btn-success btn-xs" 
															onclick="sec_contrato_detalle_agente_solicitud_editar_solicitud('Propietario','cont_persona','num_docu','Número de Documento de Identidad','varchar','<?php echo $num_docu_propietario; ?>','','<?php echo $sel["persona_id"];?>');">
																<span class="fa fa-edit"></span> Editar
															</a>
														</td>
														<?php } ?>
													</tr>

													<?php } ?>

													<tr>
														<td><b>Número de RUC</b></td>
														<td><?php echo $num_ruc_propietario;?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td>
															<a class="btn btn-success btn-xs" 
															onclick="sec_contrato_detalle_agente_solicitud_editar_solicitud('Propietario','cont_persona','num_ruc','Número de RUC','varchar','<?php echo $num_ruc_propietario; ?>','','<?php echo $sel["persona_id"];?>');">
																<span class="fa fa-edit"></span> Editar
															</a>
														</td>
														<?php } ?>
													</tr>
													<tr>
														<td><b>Domicilio del propietario</b></td>
														<td><?php echo $sel["direccion"];?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td>
															<a class="btn btn-success btn-xs" 
															onclick="sec_contrato_detalle_agente_solicitud_editar_solicitud('Propietario','cont_persona','direccion','Domicilio del propietario','varchar','<?php echo $sel["direccion"]; ?>','','<?php echo $sel["persona_id"];?>');">
																<span class="fa fa-edit"></span> Editar
															</a>
														</td>
														<?php } ?>
													</tr>
													<tr>
														<td><b>Representante Legal</b></td>
														<td><?php echo $sel["representante_legal"];?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td>
															<a class="btn btn-success btn-xs" 
															onclick="sec_contrato_detalle_agente_solicitud_editar_solicitud('Propietario','cont_persona','representante_legal','Representante Legal','varchar','<?php echo $sel["representante_legal"]; ?>','','<?php echo $sel["persona_id"];?>');">
																<span class="fa fa-edit"></span> Editar
															</a>
														</td>
														<?php } ?>
													</tr>
													<tr>
														<td><b>N° de Partida Registral de la empresa</b></td>
														<td><?php echo $sel["num_partida_registral"];?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td>
															<a class="btn btn-success btn-xs" 
															onclick="sec_contrato_detalle_agente_solicitud_editar_solicitud('Propietario','cont_persona','num_partida_registral','N° de Partida Registral de la empresa','varchar','<?php echo $sel["num_partida_registral"]; ?>','','<?php echo $sel["persona_id"];?>');">
																<span class="fa fa-edit"></span> Editar
															</a>
														</td>
														<?php } ?>
													</tr>
													<tr>
														<td><b>Persona de contacto</b></td>
														<td><?php echo $sel["contacto_nombre"];?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td>
															<a class="btn btn-success btn-xs" 
															onclick="sec_contrato_detalle_agente_solicitud_editar_solicitud('Propietario','cont_persona','contacto_nombre','Persona de contacto','varchar','<?php echo $sel["contacto_nombre"]; ?>','','<?php echo $sel["persona_id"];?>');">
																<span class="fa fa-edit"></span> Editar
															</a>
														</td>
														<?php } ?>
													</tr>
													<tr>
														<td><b>Teléfono de la persona de contacto</b></td>
														<td><?php echo $sel["contacto_telefono"];?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td>
															<a class="btn btn-success btn-xs" 
															onclick="sec_contrato_detalle_agente_solicitud_editar_solicitud('Propietario','cont_persona','contacto_telefono','Teléfono de la persona de contacto','varchar','<?php echo $sel["contacto_telefono"]; ?>','','<?php echo $sel["persona_id"];?>');">
																<span class="fa fa-edit"></span> Editar
															</a>
														</td>
														<?php } ?>
													</tr>
													<tr>
														<td><b>E-mail de la persona de contacto</b></td>
														<td><?php echo $sel["contacto_email"];?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td>
															<a class="btn btn-success btn-xs" 
															onclick="sec_contrato_detalle_agente_solicitud_editar_solicitud('Propietario','cont_persona','contacto_email','E-mail de la persona de contacto','varchar','<?php echo $sel["contacto_email"]; ?>','','<?php echo $sel["persona_id"];?>');">
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
								<div class="panel-heading" role="tab" id="headingDatosDelAgente">
								<h4 class="panel-title">
									<a role="button" data-toggle="collapse" data-parent="#accordionContrato" href="#collapseDatosDelAgente" aria-expanded="true" aria-controls="collapseDatosDelAgente">
									DATOS DEL AGENTE
									</a>
								</h4>
								</div>
								<div id="collapseDatosDelAgente" class="panel-collapse-all panel-collapse collapse in" role="tabpanel" aria-labelledby="headingDatosDelAgente">
									<div class="panel-body">
										<div class="w-100">
											<div id="divTablaInmuebles" class="form-group">
												<?php
												$sel_query = $mysqli->query("SELECT
													i.id,
													i.ubigeo_id,
													ude.nombre AS departamento, 
													upr.nombre AS provincia,
													udi.nombre AS distrito,
													i.ubicacion
												FROM cont_inmueble i
												INNER JOIN tbl_ubigeo ude ON SUBSTRING(i.ubigeo_id, 1, 2) = ude.cod_depa AND ude.cod_prov = '00' AND ude.cod_dist = '00'
												INNER JOIN tbl_ubigeo upr ON SUBSTRING(i.ubigeo_id, 1, 2) = upr.cod_depa AND SUBSTRING(i.ubigeo_id, 3, 2) = upr.cod_prov AND upr.cod_dist = '00'
												INNER JOIN tbl_ubigeo udi ON SUBSTRING(i.ubigeo_id, 1, 2) = udi.cod_depa AND SUBSTRING(i.ubigeo_id, 3, 2) = udi.cod_prov AND SUBSTRING(i.ubigeo_id, 5, 2) = udi.cod_dist
											
												WHERE i.contrato_id = " . $contrato_id . ";");
												while($sel=$sel_query->fetch_assoc()){
													$ubigeo_id = $sel["ubigeo_id"];
										
													
												?>
												<table class="table table-bordered table-hover">
													<tr>
														<td><b>Ubigeo</b></td>
														<td><?php echo $ubigeo_id;?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td style="width: 75px;">
														</td>
														<?php } ?>
													</tr>
													<tr>
														<td style="width: 250px;"><b>Departamento</b></td>
														<td><?php echo $sel["departamento"];?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td rowspan="3" style="width: 75px;">
															<a class="btn btn-success btn-xs" 
															onclick="sec_contrato_detalle_agente_solicitud_editar_solicitud('Inmueble','cont_inmueble','ubigeo_id','Departamento/Provincia/Distrito','select_option','<?php echo $sel["departamento"] . "/" . $sel["provincia"] . "/" . $sel["distrito"]; ?>','obtener_departamentos','');">
																<span class="fa fa-edit"></span> Editar
															</a>
														</td>
														<?php } ?>
													</tr>
													<tr>
														<td><b>Provincia</b></td>
														<td><?php echo $sel["provincia"];?></td>
													</tr>
													<tr>
														<td><b>Distrito</b></td>
														<td><?php echo $sel["distrito"];?></td>
													</tr>
													<tr>
														<td><b>Ubicación</b></td>
														<td><?php echo $sel["ubicacion"];?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td>
															<a class="btn btn-success btn-xs" 
															onclick="sec_contrato_detalle_agente_solicitud_editar_solicitud('Inmueble','cont_inmueble','ubicacion','Ubicación','varchar','<?php echo $sel["ubicacion"]; ?>','','');">
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
								<div class="panel-heading" role="tab" id="headingConcicionesComerciales">
								<h4 class="panel-title">
									<a role="button" data-toggle="collapse" data-parent="#accordionContrato" href="#collapseCondicionesComerciales" aria-expanded="true" aria-controls="collapseCondicionesComerciales">
									CONDICIONES COMERCIALES
									</a>
								</h4>
								</div>
								<div id="collapseCondicionesComerciales" class="panel-collapse-all panel-collapse collapse in" role="tabpanel" aria-labelledby="headingConcicionesComerciales">
									<div class="panel-body">
										<?php
										$sel_query = $mysqli->query("SELECT 						 
											bien_entregado,
											detalle_bien_entradado,
											status,
											user_created_id,
											created_at,
											user_updated_id,
											updated_at 							 
										FROM cont_contrato 						 
										WHERE contrato_id = " . $contrato_id);
										while($row=$sel_query->fetch_assoc()){                                							 
											$bien_entregado = $row["bien_entregado"];
											$detalle_bien_entradado = $row["detalle_bien_entradado"];
											
										}?>

										<div class="w-100">
											<div class="form-group">
												<table class="table table-bordered table-hover">

												<?php
												$sel_query = $mysqli->query("SELECT 
													c.participacion_id,
													c.porcentaje_participacion,
													c.condicion_comercial_id,
													p.nombre as nombre_participacion,
													m.nombre as nombre_condicion
												FROM cont_cc_agente c
												INNER JOIN cont_participaciones p ON c.participacion_id = p.id 
												INNER JOIN cont_condiciones_comerciales m ON c.condicion_comercial_id = m.id
												WHERE c.contrato_id = " . $contrato_id);
												while($row=$sel_query->fetch_assoc()){                                
													$participacion_id = $row["participacion_id"];
													$porcentaje_participacion = $row["porcentaje_participacion"];
													$condicion_comercial_id = $row["condicion_comercial_id"];
													$nombre_participacion = $row["nombre_participacion"];
													$nombre_condicion = $row["nombre_condicion"];
												?>							
														<tr>
															<td style="width: 250px;"><b>Tipo</b></td>
															<td><?php echo $nombre_participacion;?></td>
															<?php if ($btn_editar_solicitud) { ?>
															<td style="width: 75px;">
															
															</td>
															<?php } ?>
														</tr>
														<tr>
															<td><b>Porcentaje de Participación</b></td>
															<td><?php echo $porcentaje_participacion?></td>
															<?php if ($btn_editar_solicitud) { ?>
															<td>
																<a class="btn btn-success btn-xs" 
																onclick="sec_contrato_detalle_agente_solicitud_editar_solicitud('Condiciones Comerciales','cont_cc_agente','porcentaje_participacion','Porcentaje de Participación','decimal','<?php echo $porcentaje_participacion; ?>','','');">
																	<span class="fa fa-edit"></span> Editar
																</a>
															</td>
															<?php } ?>
														</tr>
														<tr>
															<td><b>Condición de Participación</b></td>
															<td><?php echo $nombre_condicion;?></td>
															<?php if ($btn_editar_solicitud) { ?>
															<td>
																<a class="btn btn-success btn-xs" 
																onclick="sec_contrato_detalle_agente_solicitud_editar_solicitud('Condiciones Comerciales','cont_cc_agente','condicion_comercial_id','Condición de Participación','select_option','<?php echo $nombre_condicion; ?>','obtener_condiciones_comerciales_de_agentes');">
																	<span class="fa fa-edit"></span> Editar
																</a>
															</td> 

															<?php } ?>
														</tr>
													
													<?php
													}?>
													</table>
												</div>
											</div>
									</div>
								</div>
							</div>

							<div class="panel panel-default">
								<div class="panel-heading" role="tab" id="headingPlazo">
								<h4 class="panel-title">
									<a role="button" data-toggle="collapse" data-parent="#accordionContrato" href="#collapsePlazo" aria-expanded="true" aria-controls="collapsePlazo">
									PLAZO
									</a>
								</h4>
								</div>
								<div id="collapsePlazo" class="panel-collapse-all panel-collapse collapse in" role="tabpanel" aria-labelledby="headingPlazo">
									<div class="panel-body">
										<div class="w-100">
											<div class="form-group">
												<table class="table table-bordered table-hover">
													<tr>
														<td style="width: 250px;"><b>Vigencia:</b></td>
														<td>
															<?php echo ($plazo_id_agente==1 || $plazo_id_agente==2)?$fecha_vencimiento_indefinida:'Definida' ?>
														</td>
														<?php if ($btn_editar_solicitud) { ?>
															<td>
																<a class="btn btn-success btn-xs" onclick="sec_contrato_detalle_agente_solicitud_editar_solicitud('Contrato Firmado','cont_contrato','plazo_id_agente','Vigencia','select_option','<?php echo $fecha_vencimiento_indefinida; ?>','obtener_tipo_fecha_vencimiento','');">
																	<span class="fa fa-edit"></span> Editar
																</a>
															</td>
														<?php } ?>

													</tr>
													<tr <?php echo ($plazo_id_agente==2)?"hidden":"" ?>>
														<td style="width: 250px;"><b>Cantidad</b></td>
														<td><?php echo $periodo_numero;?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td style="width: 75px;">
															<a class="btn btn-success btn-xs" 
															onclick="sec_contrato_detalle_agente_solicitud_editar_solicitud('Periodo','cont_contrato','periodo_numero','Periodo','varchar','<?php echo $periodo_numero; ?>','','');">
																<span class="fa fa-edit"></span> Editar
															</a>
														</td>
														<?php } ?>
													</tr>
													<tr <?php echo ($plazo_id_agente==2)?"hidden":"" ?>>
														<td style="width: 250px;"><b>Años o Meses</b></td>
														<td><?php echo $periodo;?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td style="width: 75px;">
															<a class="btn btn-success btn-xs" 
															onclick="sec_contrato_detalle_agente_solicitud_editar_solicitud('Periodo','cont_contrato','periodo','Periodo','select_option','<?php echo $periodo; ?>','obtener_plazo_agente')									
															;">
																<span class="fa fa-edit"></span> Editar
															</a>
														</td>
														<?php } ?>
													</tr>
													
													<tr style="text-transform: none;">
														<td><b>Fecha Inicio:</b></td>
														<td>
																<?php echo $fecha_inicio_agente; ?>
														</td>
														<?php if ($btn_editar_solicitud) { ?>

														<td>
																<a class="btn btn-success btn-xs" onclick="sec_contrato_detalle_agente_solicitud_editar_solicitud('Contrato Firmado','cont_contrato','fecha_inicio_agente','Fecha Inicio','date','<?php echo $fecha_inicio_agente; ?>','','');">
																	<span class="fa fa-edit"></span> Editar
																</a>
														</td> 
														<?php } ?>

													</tr>
													<tr style="text-transform: none;" <?php echo ($plazo_id_agente==2)?"hidden":"" ?>>
														<td><b>Fecha Fin:</b></td>
														<td>
																<?php echo $fecha_fin_agente ?>
														</td>
														<?php if ($btn_editar_solicitud) { ?>

														<td>
																<a class="btn btn-success btn-xs" onclick="sec_contrato_detalle_agente_solicitud_editar_solicitud('Contrato Firmado','cont_contrato','fecha_fin_agente','Fecha Fin','date','<?php echo $fecha_fin_agente; ?>','','');">
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
								<div class="panel-heading" role="tab" id="headingNombreAgente">
								<h4 class="panel-title">
									<a role="button" data-toggle="collapse" data-parent="#accordionContrato" href="#collapseNombreAgente" aria-expanded="true" aria-controls="collapseNombreAgente">
									NOMBRE DEL AGENTE
									</a>
								</h4>
								</div>
								<div id="collapseNombreAgente" class="panel-collapse-all panel-collapse collapse in" role="tabpanel" aria-labelledby="headingNombreAgente">
									<div class="panel-body">
										<div class="w-100">
											<div class="form-group">
												<table class="table table-bordered table-hover">
													<tr>
														<td style="width: 250px;"><b>Agente AT</b></td>
														<td><?php echo $nombre_agente;?></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
								</div>
							</div>


							<div class="panel panel-default">
								<div class="panel-heading" role="tab" id="headingCC">
								<h4 class="panel-title">
									<a role="button" data-toggle="collapse" data-parent="#accordionContrato" href="#collapseCC" aria-expanded="true" aria-controls="collapseCC">
									CC
									</a>
								</h4>
								</div>
								<div id="collapseCC" class="panel-collapse-all panel-collapse collapse in" role="tabpanel" aria-labelledby="headingCC">
									<div class="panel-body">
										<div class="w-100">
											<div class="form-group">
												<table class="table table-bordered table-hover">
													<tr>
														<td style="width: 250px;"><b>Centro de costos</b></td>
														<td><?php echo $c_costos;?></td>
													</tr>
												</table>
											</div>
										</div>
									</div>
								</div>
							</div>

							<div class="panel panel-default">
								<div class="panel-heading" role="tab" id="headingFechaSuscripcion">
								<h4 class="panel-title">
									<a role="button" data-toggle="collapse" data-parent="#accordionContrato" href="#collapseFechaSucripcion" aria-expanded="true" aria-controls="collapseFechaSucripcion">
									FECHA DE SUSCRIPCIÓN DEL CONTRATO
									</a>
								</h4>
								</div>
								<div id="collapseFechaSucripcion" class="panel-collapse-all panel-collapse collapse in" role="tabpanel" aria-labelledby="headingFechaSuscripcion">
									<div class="panel-body">
										<div class="w-100">
											<div class="form-group">
												<table class="table table-bordered table-hover">
													<tr>
														<td style="width: 250px;"><b>Fecha de suscripción del contrato</b></td>
														<td><?php echo $fecha_suscripcion_contrato;?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td style="width: 75px;">
															<a class="btn btn-success btn-xs" 
															onclick="sec_contrato_detalle_agente_solicitud_editar_solicitud('Fecha de suscripción','cont_contrato','fecha_suscripcion_contrato','Fecha de suscripción del contrato','date','<?php echo $fecha_suscripcion_contrato; ?>','','');">
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
								<div class="panel-heading" role="tab" id="headingObservaciones">
								<h4 class="panel-title">
									<a role="button" data-toggle="collapse" data-parent="#accordionContrato" href="#collapseObservaciones" aria-expanded="true" aria-controls="collapseObservaciones">
									OBSERVACIONES
									</a>
								</h4>
								</div>
								<div id="collapseObservaciones" class="panel-collapse-all panel-collapse collapse in" role="tabpanel" aria-labelledby="headingObservaciones">
									<div class="panel-body">
										<div class="w-100">
											<div class="form-group">
												<table class="table table-bordered table-hover">
													<tr>
														<td style="width: 250px;"><b>Observaciones</b></td>
														<td style="white-space: pre-line;"><?php echo $observaciones; ?></td>
														<?php if ($btn_editar_solicitud) { ?>
														<td style="width: 75px;">
															<a class="btn btn-success btn-xs" 
															onclick="sec_contrato_detalle_agente_solicitud_editar_solicitud('Observaciones','cont_contrato','observaciones','Observaciones','textarea','<?php echo replace_invalid_caracters_vista($observaciones); ?>','','');">
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
			</div>


			<div class="panel" id="divFormatoDePago" style="display: none;">


				<div class="panel-heading">
					<div class="panel-title">FORMATO DE PAGO DE NUEVAS TIENDAS</div>
				</div>

				<div class="panel-body" style="padding: 0px; padding-top: 10px; font-size: 12px;">
					<form id="frmFormatoDePago">
						<div class="col-xs-12 col-md-12 col-lg-12">
							<div id="divTablaGenerales" class="form-group">
								<table class="table table-bordered table-hover">
									<tr>
										<td style="width: 250px;"><b>Tipo de Persona</b></td>
										<td><?php echo $tipo_persona_beneficiario; ?></td>
									</tr>
									<tr>
										<td><b>Nombre de la tienda</b></td>
										<td><?php echo $nombre_tienda; ?></td>
									</tr>
									<tr>
										<td><b>Arrendataria</b></td>
										<td><?php echo $empresa_suscribe; ?></td>
									</tr>
									<tr>
										<td><b>Adelanto</b></td>
										<td><?php 
										
										if($tipo_adelanto_id == '1')
										{
											if ($num_de_meses_de_adelanto == 0) 
											{
												echo 'Cero meses de adelanto.';
											} 
											else 
											{
												echo $meses_de_adelanto;
											}
										} 
										else
										{
											echo $tipo_adelanto;
										}

										?></td>
									</tr>
									<tr>
										<td><b>Garantía</b></td>
										<td><?php echo $garantia_monto; ?></td>
									</tr>
									<tr>
										<td><b>Número de RUC del Arrendador</b></td>
										<td><?php echo $num_ruc_propietario; ?></td>
									</tr>
									<tr>
										<td><b>Número de DNI y/o RUC del Beneficiario</b></td>
										<td><?php echo $num_docu_beneficiario; ?></td>
									</tr>
									<tr>
										<td><b>Nombre y Apellidos del Beneficiario</b></td>
										<td><?php echo $nombre_beneficiario; ?></td>
									</tr>
									<tr>
										<td><b>Nombre del Banco</b></td>
										<td><?php echo $banco_beneficiario; ?></td>
									</tr>
									<tr>
										<td><b>Número de la cuenta bancaria</b></td>
										<td><?php echo $num_cuenta_bancaria_beneficiario; ?></td>
									</tr>
									<tr>
										<td><b>Número de CCI bancario</b></td>
										<td><?php echo $num_cuenta_cci_beneficiario; ?></td>
									</tr>
									<tr>
										<td><b>Moneda</b></td>
										<td><?php echo $moneda_contrato_con_simbolo; ?></td>
									</tr>
									<tr>
										<td><b>Importe a girar y/o depositar</b></td>
										<td>
										<?php 
										$importe_a_girar = $garantia_monto_sin_formato + ( $monto_renta_sin_formato * $num_de_meses_de_adelanto );
										echo $simbolo_moneda . ' ' . number_format($importe_a_girar, 2, '.', ',');
										?>
										</td>
									</tr>
								</table>

								<span>*PREVIA PROVISION CONTABLE</span>
							</div>
						</div>					
					</form>
				</div>
			</div>


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
						<!-- <img src="contratos/recibo_luz.jpg" class="img-responsive" style="border: 1px solid;"> -->
					</div>
				</div>

			</div>

		</div>


		<div class="col-xs-12 col-md-12 col-lg-5">
			<div class="panel" id="divDetalleSolicitud">
				<div class="panel-body" style="padding: 5px 10px 5px 10px;">
					<div class="panel-group no-mb" id="accordion" role="tablist" aria-multiselectable="true">
						<!-- INICIO PANEL DOCUMENTOS -->
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
									if ($etapa_id == 5 && (array_key_exists($menu_consultar,$usuario_permisos) && in_array("subir_contrato_firmado", $usuario_permisos[$menu_consultar]))) { 
									?>
									<button type="button" class="btn btn-xs btn-info" onclick="sec_cont_detalle_agregarNuevoContratoFirmado()"><i class="fa fa-plus"></i> Agregar Nuevo Contrato Firmado</button>
									<?php
									}
									?>

									<table class="table table-responsive table-hover" style="font-size: 10px;">
										<thead style="background: none;">
											<tr style="text-transform: none;">
												<th align="center">Nombre del Documento</th>

												<?php  

												if(!(array_key_exists($menu_consultar,$usuario_permisos) && in_array("request", $usuario_permisos[$menu_consultar])))
												{
													?>
														<th align="center">Operación</th>		
													<?php
												}
												else
												{
													if(($area_id == 33 AND $cargo_id != 25) || $usuario_created_id == $login['id'])
													{
														?>
															<th align="center">Operación</th>
														<?php
													}
												}

												if($area_id == 33)
												{
													?>
														<th align="center">Visualizar</th>			
													<?php
												}
												else if((array_key_exists($menu_consultar,$usuario_permisos) && in_array("request", $usuario_permisos[$menu_consultar])))
												{
													?>
														<th align="center">Visualizar</th>		
													<?php
												}
												else
												{
													if($area_id != 33)
													{
														?>
															<th align="center">Visualizar</th>			
														<?php
													}
												}

												?>

												<?php
												if ($etapa_id == 5 && (array_key_exists($menu_consultar,$usuario_permisos) && in_array("eliminar_contrato_firmado", $usuario_permisos[$menu_consultar]))) { 
												?>
													<th align="center"></th>
												<?php
												}
												?>
											</tr>
										</thead>
										<tbody>
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
													tipo_archivo_id = 16
													AND status = 1
													AND contrato_id = " . $contrato_id
												);
												$num_rows = mysqli_num_rows($sel_contrato_firmado);
												?>

												
													

												<?php
												if ($num_rows == 0) {
												?>
												<tr style="text-transform: none;">
													<td>Contrato Firmado</td>
													<td colspan="2">
														<a class="btn btn-warning btn-xs btn-block" data-toggle="tooltip" data-placement="top"><span class="fa fa-eye-slash"></span> Incompleto</a>
													</td>
												</tr>
												<?php 
												} else if($num_rows > 0) {
													while ($row = $sel_contrato_firmado->fetch_assoc()){
														$ruta = str_replace("/var/www/html","",$row["ruta"]);
													?>
												<tr style="text-transform: none;">
													<td>Contrato Firmado</td>
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
														if ($etapa_id == 5 && (array_key_exists($menu_consultar,$usuario_permisos) && in_array("eliminar_contrato_firmado", $usuario_permisos[$menu_consultar]))) { 
													?>
													<td>
														<a
															onclick="sec_contrato_detalle_solicitud_ver_eliminar_anexo(<?=$row['archivo_id']?>);"
															class="btn btn-danger btn-xs btn-block"
															data-toggle="tooltip"
															title="Eliminar Contrato Firmado"
															data-placement="top">
															<span class="fa fa-trash"></span>
														</a>
													</td>
													<?php
														}
													}
													?>
												</tr>
												<?php
												}
												?>
											<?php
											}
											// FIN CONTRATO FIRMADO
											?>

										</tbody>
									</table>

									

									



								<button type="button" class="btn btn-xs btn-info" onclick="agregarNuevoAnexoConAgente();"><i class="fa fa-plus"></i> Agregar Nuevo Anexo</button>
									<table class="table table-responsive table-hover" style="font-size: 10px;">
										<thead style="background: none;">
											<tr style="text-transform: none;">
												<th align="center">Nombre del Documento</th>

												<?php  

												if(!(array_key_exists($menu_consultar,$usuario_permisos) && in_array("request", $usuario_permisos[$menu_consultar])))
												{
													?>
														<th align="center">Operación</th>		
													<?php
												}
												else
												{
													if(($area_id == 33 AND $cargo_id != 25) || $usuario_created_id == $login['id'])
													{
														?>
															<th align="center">Operación</th>
														<?php
													}
												}

												if($area_id == 33)
												{
													?>
														<th align="center">Visualizar</th>			
													<?php
												}
												else if((array_key_exists($menu_consultar,$usuario_permisos) && in_array("request", $usuario_permisos[$menu_consultar])))
												{
													?>
														<th align="center">Visualizar</th>		
													<?php
												}
												else
												{
													if($area_id != 33)
													{
														?>
															<th align="center">Visualizar</th>			
														<?php
													}
												}

												?>

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
												<td>Solicitud de Contratos de Agente</td>
												<td colspan="2">
													<a onclick="sec_contrato_detalle_solicitud_ver_documento_en_visor('','', 'html', '');" class="btn btn-primary btn-xs btn-block" data-toggle="tooltip" data-placement="top"><span class="fa fa-eye"></span> Ver Detalle de la Solicitud</a>
												</td>
											</tr>

											


											<?php
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
													ar.tipo_archivo_id = 97
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
																	onclick="moda_reemplazar_archivo_req_solicitud_arrendamiento('<?=$row['nombre'];?>','<?=$row['archivo_id'];?>','<?=$row['tipo_archivo_id'];?>');"
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
															if ((array_key_exists($menu_consultar,$usuario_permisos) && in_array("eliminar_anexo", $usuario_permisos[$menu_consultar]))) { 
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
											}
											// FIN ADENDA FIRMADA
											?>

											<!-- INICIO FORMATO DE PAGO -->
										<!--	<tr style="text-transform: none;">
												<td>Formato de Pago</td>
												<td colspan="2">
													<a onclick="sec_contrato_detalle_solicitud_ver_documento_en_visor('','', 'html', 'formato_de_pago');" class="btn btn-primary btn-xs btn-block" data-toggle="tooltip" data-placement="top"><span class="fa fa-eye"></span> Ver Formato de Pago</a>
												</td>
											</tr> -->
											<!-- FIN FORMATO DE PAGO -->


											<?php
											$html = '';

											$sql = "SELECT * from (
													 
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
												WHERE t.tipo_archivo_id NOT IN (152)
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

														$archivo_estado = '<a class="class_SubirFile_req_solicitud_arrendamiento btn btn-success btn-xs btn-block" id="btnSubirFile_req_solicitud_arrendamiento" data-toggle="tooltip" data-placement="top" onclick="moda_subir_archivo_req_solicitud_arrendamiento(\''.$row["nombre_tipo_archivo"].'\', \''.$row["archivo_id"].'\', \''.$row["tipo_archivo_id"].'\');"><i class="fa fa-upload"></i> Reemplazar</a>';
													}
													else 
													{
														$archivo = '<a class="btn btn-warning btn-xs btn-block" data-toggle="tooltip" data-placement="top"><span class="fa fa-eye-slash"></span> Incompleto</a>';

														$archivo_estado = '<a class="class_SubirFile_req_solicitud_arrendamiento btn btn-warning btn-xs btn-block" id="btnSubirFile_req_solicitud_arrendamiento" data-toggle="tooltip" data-placement="top" onclick="moda_subir_archivo_req_solicitud_arrendamiento(\''.$row["nombre_tipo_archivo"].'\', \''.$row["archivo_id"].'\', \''.$row["tipo_archivo_id"].'\');"><i class="fa fa-upload"></i> Subir</a>';
													}

													$html .= '<tr style="text-transform: none;">';
													$html .= '<td>Anexo - ' . $row["nombre_tipo_archivo"] . '</td>';

													if(!(array_key_exists($menu_consultar,$usuario_permisos) && in_array("request", $usuario_permisos[$menu_consultar])))
													{
														$html .= '<td>'.$archivo_estado.'</td>';
													}
													else
													{
														if(($area_id == 33 AND $cargo_id != 25) || $usuario_created_id == $login['id'])
														{
															$html .= '<td>'.$archivo_estado.'</td>';
														}
													}

													if($area_id == 33)
													{
														$html .= '<td>'.$archivo.'</td>';
													}
													else if((array_key_exists($menu_consultar,$usuario_permisos) && in_array("request", $usuario_permisos[$menu_consultar])))
													{
														$html .= '<td>'.$archivo.'</td>';
													}
													else
													{
														if($area_id != 33)
														{
															$html .= '<td>'.$archivo.'</td>';
														}
													}


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


									<?php
									// INICIO RESOLUCIÓN DE CONTRATO 
									if (($area_id == 33 AND $cargo_id != 25) || (array_key_exists($menu_consultar,$usuario_permisos) && in_array("ver_resolucion_contrato", $usuario_permisos[$menu_consultar]))) { 
										?>
										<table class="table table-responsive table-hover" style="font-size: 10px;">
											<thead style="background: none;">
												<tr>
													<th colspan="3" class="text-center">RESOLUCIÓN DE CONTRATO 
														<button onclick="fnc_modal_nuevo_documento_resolucion_contrato();" style="float: right" class="btn btn-xs btn-primary" type="button">Nueva Resolución de Contrato</button>	
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
												rc.id as resolucion_contrato_id
											FROM
												cont_archivos AS ar
												LEFT JOIN cont_resolucion_contrato AS rc ON rc.archivo_id = ar.archivo_id
											WHERE
												ar.tipo_archivo_id = 105
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
													<td>Archivo Resolución de Contrato</td>
														<td>
															<a
																onclick="moda_subir_archivo_req_solicitud_arrendamiento('<?=$row['nombre'];?>','<?=$row['archivo_id'];?>','<?=$row['tipo_archivo_id'];?>');"
																class="btn btn-success btn-xs btn-block"
																data-toggle="tooltip"
																data-placement="top">
																<span class="fa fa-upload"></span> Reemplazar
															</a>
														</td>
														<td>
															<a
																onclick="sec_contrato_detalle_solicitud_ver_documento_en_visor('<?php echo $ruta;?>','<?php echo trim($row["nombre"]); ?>','<?php echo trim($row["extension"]); ?>','Resolucion');"
																class="btn btn-success btn-xs btn-block"
																data-toggle="tooltip"
																data-placement="top">
																<span class="fa fa-eye"></span> Ver Resolución <?=!Empty($row['resolucion_contrato_id']) ? ' Firmada' : ''?> 
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
									// FIN RESOLUCION CONTRATO
									?>

								</div>
							</div>
						</div>
						<!-- FIN PANEL DOCUMENTOS -->

						<!-- INICIO CONFIRMACION DE GIROS -->
						<?php 

						$boton_aprobacion_gerencia = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' AND sub_sec_id = '$sub_sec_id' LIMIT 1")->fetch_assoc();
						$id_boton_aprobacion_gerencia = $boton_aprobacion_gerencia["id"];

						if(array_key_exists($id_boton_aprobacion_gerencia,$usuario_permisos) && in_array("municipal", $usuario_permisos[$id_boton_aprobacion_gerencia]))
						{ 
						?>
						<div class="panel">
							<div class="panel-heading" role="tab" id="browsers-this-giro-heading">
								<div class="panel-title">                                        
									<a href="#browsers-verificar_giro" class="collapsed" role="button" data-toggle="collapse"
									   data-parent="#accordion" aria-expanded="true" aria-controls="browsers-verificar_giro">
										Confirmación de Giro
									</a>
								</div>
							</div>

							<div id="browsers-verificar_giro" class="panel-collapse collapse" role="tabpanel" aria-labelledby="browsers-this-giro-heading">
								<div class="panel-body">
									
								
							<?php
								if($verificar_giro == "0")
								{
									?>
										<form id="form_contrato_detalle_solicitud_giro" name="form_contrato_proveedor_firmado" method="POST" enctype="multipart/form-data" autocomplete="off">
											
											<div class="col-xs-12 col-md-12 col-lg-12" id="cont_detalle_solicitud_div_giro_botones_aprobaryrechazar">
												<div class="col-xs-12 col-md-6 col-lg-6 item_filter">
													<button type="button" class="btn btn-success btn-xs btn-block col-md-6 cont_detalle_solicitud_giro_boton" id="cont_detalle_solicitud_giro_aprobar" value="1" style="height: 30px;">
														<span id="demo-button-text">
															<i class="glyphicon glyphicon-saved"></i>
															SI
														</span>
													</button>
												</div>

												<div class="col-xs-12 col-md-6 col-lg-6 item_filter">
													<button type="button" class="btn btn-danger btn-xs btn-block cont_detalle_solicitud_giro_boton" id="cont_detalle_solicitud_giro_rechazar" value="2" style="height: 30px;">
														<span id="demo-button-text">
															<i class="glyphicon glyphicon-remove-sign"></i>
															NO
														</span>
													</button>
												</div>
											</div>
											
											<div class="col-md-12" id="cont_detalle_solicitud_div_giro_ingrese_motivo" style="border: 1px solid #000; margin: 0px; display: none;">
												<div class="form-group col-md-12" style="margin: 0px; padding: 5px;">
													<label>Ingresar motivo</label>
													<textarea class="form-control" name="cont_detalle_solicitud_param_texto_motivo_giro" id="cont_detalle_solicitud_param_texto_motivo_giro" rows="3" maxlength="200" autocomplete="off" style="text-transform:uppercase; resize: none;"></textarea>
													<small>Quedan <span id="cont_detalle_solicitud_param_text_giro_cantidad_caracteres">255</span> caracteres</small>
												</div>

												<div class="form-group col-md-12" style="text-align: right;">
													<button type="button" class="btn btn-warning cont_detalle_solicitud_giro_boton" style="margin-left: 5px;" value="4">
														<i class="glyphicon glyphicon-minus-sign" ></i>
														Omitir
													</button>
													<button type="button" class="btn btn-success cont_detalle_solicitud_giro_boton" value="3">
														<i class="glyphicon glyphicon-saved" ></i>
														Guardar
													</button>
											   </div>
											</div>
										</form>
											
									<?php
								}
								else
								{
									?>
										<table class="table table-responsive table-hover no-mb" style="font-size: 12px;">
											<tbody>
												<?php  
													if($verificar_giro != "1")
													{
														?>
															<tr style="text-transform: none;">
																<td><b>Giro:</b></td>
																<td>No</td>
															</tr>
															<tr style="text-transform: none;">
																<td><b>Motivo:</b></td>
																<td><?php echo $verificar_giro; ?></td>
															</tr>
															<tr style="text-transform: none;">
																<td><b>Confirmado por:</b></td>
																<td><?php echo $usuario_verificacion_giro; ?></td>
															</tr>
															<tr style="text-transform: none;">
																<td><b>Fecha confirmación:</b></td>
																<td><?php echo $fecha_verificacion_giro; ?></td>
															</tr>
														<?php
													}
													else
													{
														?>
															<tr style="text-transform: none;">
																<td><b>Giro:</b></td>
																<td>Si</td>
															</tr>
															<tr style="text-transform: none;">
																<td><b>Confirmado por:</b></td>
																<td><?php echo $usuario_verificacion_giro; ?></td>
															</tr>
															<tr style="text-transform: none;">
																<td><b>Fecha confirmación:</b></td>
																<td><?php echo $fecha_verificacion_giro; ?></td>
															</tr>
														<?php
													}
												?>
											</tbody>
										</table>
									<?php
								}
							?>
								</div>
							</div>
						</div>

						<?php 
						} 
						?>
						<!-- FIN CONFIRMACION DE GIROS -->

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
						<!-- FIN PANEL CAMBIOS EN LA SOLICITUD -->


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
								a.estado_solicitud_id,
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
											$cancelado_el = $sel['cancelado_el'];
											$cancelado_motivo = $sel['cancelado_motivo'];
											$area = $sel["area"];
											$solicitante = $sel["solicitante"];
											$abogado = $sel["abogado"];
											$fecha_solicitud = $sel["fecha_solicitud"];
											$estado_solicitud_id = $sel["estado_solicitud_id"];
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

											if ($cancelado_id == 1) {
												$adenda_estado = '<span class="badge bg-danger text-white">Cancelada</span>';
											} elseif ($procesado == 0) {
												if ($requiere_aprobacion_id === 1) { 
													if ($aprobado_estado_id === 1) {
														$adenda_estado = '<span class="badge bg-success text-white">Aprobado</span>';
													} elseif($aprobado_estado_id === 0) {
														$adenda_estado = '<span class="badge bg-danger text-white">Rechazada</span>';
													}
												}
											} elseif ($procesado == 1) {
												$adenda_estado = '<span class="badge bg-info text-white">Procesado</span>';
											}
										?>

										<p>
											<b>Adenda N° <?php echo $codigo; ?>:</b> 
											<?php if ((array_key_exists($menu_consultar,$usuario_permisos) && in_array("reenviar_adenda_firmada", $usuario_permisos[$menu_consultar])) && ($procesado == 1 || $aprobado_estado_id == 1)) { ?>
											<button type="button" onclick="sec_contrato_detalle_reenviar_adenda(<?=$sel['id']?>,'6')" style="float: right" class="btn btn-xs btn-info"><span class="fa fa-envelope-o"></span> Reenviar por email</button>
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
													<td><b>Solicitante</b></td>
													<td>
														<?php echo $solicitante; ?>
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

												if ($row["nombre_menu_usuario"] == 'Propietario') {
													$query_cont = "SELECT p.id AS persona_id,
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
													$list_query = $mysqli->query($query_cont);
													while ($li = $list_query->fetch_assoc()) {
														if ($li["propietario_id"] == $row["valor_int"]) {
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
														<th class="text-center" colspan="4">
															<b>Nuevo Propietario</b>
														</th>
													</tr>
								
													<tr>
														<td align="center" class="text-dark">Campo:</td>
														<td align="center" class="text-dark">Valor:</td>
													</tr>
													
													</thead>
								
														<tr>
														<td>Tipo Persona</td>
														<td> <?=$valores_nuevos[0]["tipo_persona"] ?></td>
														</tr>

														<tr>
														<td>Nombre</td>
														<td> <?=$valores_nuevos[0]["nombre"] ?></td>
														</tr>

														<tr>
														<td>Tipo de Documento de Identidad</td>
														<td> <?=$valores_nuevos[0]["tipo_docu_identidad"] ?></td>
														</tr>
														<?php
														if ($valores_nuevos[0]["tipo_docu_identidad_id"] != "2") {
														?>
																<tr>
																<td><?=$valores_nuevos[0]["tipo_docu_identidad"] ?></td>
																<td> <?=$valores_nuevos[0]["num_docu"] ?></td>
																</tr>
														<?php
														}
														?>
														<tr>
														<td>Número de RUC</td>
														<td> <?=$valores_nuevos[0]["num_ruc"] ?></td>
														</tr>

														<tr>
														<td>Domicilio del propietario</td>
														<td> <?=$valores_nuevos[0]["direccion"] ?></td>
														</tr>

														<tr>
														<td>Representante Legal</td>
														<td> <?=$valores_nuevos[0]["representante_legal"] ?></td>
														</tr>

														<tr>
														<td>N° de Partida Registral de la empresa</td>
														<td> <?=$valores_nuevos[0]["num_partida_registral"] ?></td>
														</tr>

														<tr>
														<td>Persona de contacto</td>
														<td> <?=$valores_nuevos[0]["contacto_nombre"] ?></td>
														</tr>

														<tr>
														<td>Teléfono de la persona de contacto</td>
														<td> <?=$valores_nuevos[0]["contacto_telefono"] ?></td>
														</tr>

														<tr>
														<td>E-mail de la persona de contacto</td>
														<td> <?=$valores_nuevos[0]["contacto_email"] ?></td>
														</tr>

														</tbody>
														</table>
								
								
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
												<button type="button" class="btn btn-success btn-xs btn-block" id="btn_guardar_adenda_firmada" onclick="sec_contrato_detalle_solicitud_agente_guardar_adenda_firmada(<?=$adenda_id?>)">
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
												if($requiere_aprobacion_id == 1 && $aprobado_estado_id === "" && $procesado == 0 && $cancelado_id != 1)
												{
													$boton_aprobacion_gerencia = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' AND sub_sec_id = '$sub_sec_id' LIMIT 1")->fetch_assoc();
													$id_boton_aprobacion_gerencia = $boton_aprobacion_gerencia["id"];

													if(array_key_exists($id_boton_aprobacion_gerencia,$usuario_permisos) && in_array("validaBotonSolicitud", $usuario_permisos[$id_boton_aprobacion_gerencia]))
													{
														?>

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
										}
										?>

									</div>
								</div>
							</div>
							<?php
							}
							?>
							<!-- PANEL: ADENDAS FIN -->


						<!-- INICIO PANEL OBSERVACIONES -->
						<?php  
							if(!(array_key_exists($menu_consultar,$usuario_permisos) && in_array("request", $usuario_permisos[$menu_consultar])))
							{
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

													<textarea rows="3" id="contrato_observaciones" placeholder="Ingrese sus observaciones" style="width: 100%" onkeypress="return event.charCode != 39"></textarea>


													<div id="div_observaciones" class="timeline" style="font-size: 11px;">
													
													</div>
													<label style="text-align: left;">Correos Adjuntos: (Opcional)</label>

													<textarea rows="3" id="correos_adjuntos" placeholder="Ingrese los correos separados con ','"  style="width: 100%"></textarea>

													

													<button class="btn btn-success btn-xs btn-block" type="submit" onclick="sec_contrato_detalle_solicitud_guardar_observaciones_agente();">
														<i class="fa fa-plus"></i> Agregar y Notificar Observación
													</button>
												</div>
											</div>
										</div>
									</div>		
								<?php
							}
							else
							{
								if(($area_id == 33 AND $cargo_id != 25) || $usuario_created_id == $login['id'])
								{
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

														<button class="btn btn-success btn-xs btn-block" type="submit" onclick="sec_contrato_detalle_solicitud_guardar_observaciones();">
															<i class="fa fa-plus"></i> Agregar Observaciones
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


						<!-- INICIO PANEL FORMATO DE PAGO -->
						<!--  <div class="panel">
							<div class="panel-heading" role="tab" id="browsers-this-month-heading">
								<div class="panel-title">                                        
									<a 
										href="#browsers-formato-de-pago" 
										class="collapsed" 
										role="button" 
										data-toggle="collapse" 
										data-parent="#accordion" 
										aria-expanded="true" 
										aria-controls="browsers-formato-de-pago">
										Formato de Pago 
									</a>
								</div>
							</div>

							<div id="browsers-formato-de-pago" class="panel-collapse collapse" role="tabpanel" aria-labelledby="browsers-formato-de-pago-heading">
								<div class="panel-body">
									<?php 
									if(($area_id == 33 && $cargo_id != 25)){ // 33: Legal
									?>
									<div style="margin-right: 10px; margin-left: 5px;">
										<button 
											class="btn btn-success btn-xs btn-block" 
											type="button"
											onclick="sec_contrato_detalle_solicitud_enviar_formato_de_pago(<?php echo $contrato_id; ?>);">
											<i class="fa fa-envelope-o"></i> Enviar Formato de Pago por email
										</button>
									</div>
									<?php 
									}
									?>
									<div style="margin-right: 10px; margin-left: 5px;" id="div_emails_enviados_formato_de_pago">
									</div>
								</div>
							</div>
						</div>	-->
						<!-- FIN PANEL FORMATO DE PAGO -->

						<!-- INICIO PANEL CONTRATO FIRMADO -->
						<?php if ($area_id == '33' AND $cargo_id != 25) { ?>
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
									$sel_query_info_contrato_firmado = $mysqli->query("
									SELECT 
									c.contrato_id,
									c.nombre_agente, 
									c.fecha_inicio_agente,
									c.fecha_fin_agente,
									c.fecha_suscripcion_contrato,
									 c.plazo_id_agente ,
									 c.renovacion_automatica,
									 ctp.nombre  as periodo,
									CONCAT(IFNULL(tpa.nombre, ''),
											' ',
											IFNULL(tpa.apellido_paterno, ''),
											' ',
											IFNULL(tpa.apellido_materno, '')) AS usuario_aprobado
								FROM
									cont_contrato c
										INNER JOIN
									tbl_usuarios tu ON c.usuario_id_contrato_aprobado_agente = tu.id
										INNER JOIN
									tbl_personal_apt tpa ON tu.personal_id = tpa.id
										LEFT JOIN 
									cont_tipo_plazo ctp ON ctp .id =c.plazo_id_agente 
								WHERE
									c.etapa_id = 5
									AND c.status = 1
									AND c.contrato_id =".$contrato_id." 
										");
									$cantReg = mysqli_num_rows($sel_query_info_contrato_firmado);
									if($cantReg > 0)
									{
										while($sel=$sel_query_info_contrato_firmado->fetch_assoc())
										{
											$nombre_tienda = $sel["nombre_agente"];
											$usuario_aprobado = $sel["usuario_aprobado"];
											$renovacion_automatica = $sel['renovacion_automatica'];
											$renovacion_automatica_value = $renovacion_automatica == 1 ? 'SI':'NO';
											
											$fecha_inicio_agente = date("d-m-Y", strtotime($sel["fecha_inicio_agente"]));

											$fecha_vencimiento_indefinida = $sel['periodo'];
											
											if ($plazo_id_agente ==2) {
												$fecha_fin_agente = 'Indefinida';
											} else {
												$fecha_fin_agente =($sel["fecha_fin_agente"]!=null)? date("d-m-Y", strtotime($sel["fecha_fin_agente"])):null;
												 
											 }

											$fecha_suscripcion_contrato = date("d-m-Y", strtotime($sel["fecha_suscripcion_contrato"]));
											
										?>
										<table class="table table-responsive table-hover no-mb" style="font-size: 10px;">
											<tbody>
												
												<tr style="text-transform: none;">
													<td><b>Usuario que cargo el contrato firmado:</b></td>
													<td>
														<?php echo $usuario_aprobado; ?>
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
													tipo_archivo_id = 16
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
													<td><b>Fecha de carga del contrato firmado:</b></td>
													<td>
														<?php echo $row["created_at"];; ?>
													</td>
												</tr>

												<tr style="text-transform: none;">
													<td><b>Nombre del Agente:</b></td>
													<td>
														<?php echo $nombre_tienda; ?>
													</td>
													<td>
														<a class="btn btn-success btn-xs" 
														onclick="sec_contrato_detalle_solicitud_editar_solicitud('Contrato Firmado','cont_contrato','nombre_agente','Nombre del Agente','varchar','<?php echo $nombre_tienda; ?>','','');">
															<span class="fa fa-edit"></span> Editar
														</a>
													</td>
												</tr>
												<tr style="text-transform: none;">
													<td><b>Vigencia:</b></td>
													<td>
														<?php echo $fecha_vencimiento_indefinida?>
													</td>
													<td>
														<a class="btn btn-success btn-xs" 
																onclick="sec_contrato_detalle_solicitud_editar_solicitud('Contrato Firmado','cont_contrato','plazo_id_agente','Vigencia','select_option','<?php echo $fecha_vencimiento_indefinida; ?>','obtener_tipo_fecha_vencimiento','');">
																	<span class="fa fa-edit"></span> Editar
														</a>
														 
													</td>
												</tr>
												<tr style="text-transform: none;">
													<td><b>Fecha Inicio:</b></td>
													<td>
														<?php echo $fecha_inicio_agente; ?>
													</td>
													<td>
														<a class="btn btn-success btn-xs" 
														onclick="sec_contrato_detalle_solicitud_editar_solicitud('Contrato Firmado','cont_contrato','fecha_inicio_agente','Fecha Inicio','date','<?php echo $fecha_inicio_agente; ?>','','');">
															<span class="fa fa-edit"></span> Editar
														</a>
													</td>
												</tr>
												
												<tr style="text-transform: none;" <?php echo $fecha_fin_agente == 'Indefinida' ? 'hidden' : '' ?>>
													<td><b>Fecha Fin:</b></td>
													<td>
														<?php echo $fecha_fin_agente; ?>
													</td>
													<td>
														<a class="btn btn-success btn-xs" 
														onclick="sec_contrato_detalle_solicitud_editar_solicitud('Contrato Firmado','cont_contrato','fecha_fin_agente','Fecha Fin','date','<?php echo $fecha_fin_agente; ?>','','');">
															<span class="fa fa-edit"></span> Editar
														</a>
													</td>
												</tr>
												<tr style="text-transform: none;">
													<td><b>Fecha Suscripción:</b></td>
													<td>
														<?php echo $fecha_suscripcion_contrato; ?>
													</td>
													<td>
														<a class="btn btn-success btn-xs" 
														onclick="sec_contrato_detalle_solicitud_editar_solicitud('Contrato Firmado','cont_contrato','fecha_suscripcion_contrato','Fecha Suscripción','date','<?php echo $fecha_suscripcion_contrato; ?>','','');">
															<span class="fa fa-edit"></span> Editar
														</a>
													</td>
												</tr>
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
									elseif ($cancelado_id != 1)
									{
									?>
									<form id="form_contrato_firmado" name="form_contrato_firmado" method="POST" enctype="multipart/form-data" autocomplete="off">

											<input type="hidden" id="cont_detalle_contrato_firmado_periodo" value="<?php echo $periodo_tipo; ?>">
											<input type="hidden" id="cont_detalle_contrato_firmado_periodo_numero" value="<?php echo $periodo_numero; ?>">
											<div class="col-xs-12 col-md-6 col-lg-6 item_filter">
												<div class="form-group">
													<label>Nombre del Agente:</label>
													<input 
														type="text" 
														id="contrato_nombre_tienda" 
														class="filtro" 
														style="width: 100%; height: 30px;" 
														value="<?php echo ($nombre_tienda == 'Sin asignar' ? '' : $nombre_tienda); ?>" maxlength="100">
												</div>
											</div>

											<div class="col-xs-12 col-md-6 col-lg-6 item_filter">
												<div class="form-group">
													<label>
														Periodo
													</label>
													<select 
														class="form-control select2" 
														name="plazo_id_arr" 
														id="plazo_id_arr">
														<?php $query = "SELECT id, nombre";
														$query .= " FROM cont_tipo_plazo";
														$query .= " WHERE status = 1";
									
														$list_query = $mysqli->query($query);
														$list = array();
														while ($li = $list_query->fetch_assoc()) { ?>
														<option  value="<?php echo   $li['id']; ?>"  <?php echo  $plazo_id_agente==$li['id']?"selected":"" ?>><?php echo  $li['nombre']; ?></option>
														 
														<?php }?>
														
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
															class="form-control fecha_detalle_arrendemiento_datepicker"
															id="cont_detalle_contrato_firmado_fecha_incio_param"
															value="<?php echo $fecha_inicio_agente; ?>"
															readonly="readonly"
															style="height: 34px;"
															>
														<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="cont_detalle_proveedor_contrato_firmado_fecha_incio_param"></label>
													</div>
												</div>
											</div>

											<div class="col-xs-12 col-md-6 col-lg-6 item_filter div_vig_def" >
												<div class="form-group">
													<label>
														Fecha Fin:
												</label>
													<div class="input-group col-xs-12">
														<input
															type="text"
															class="form-control fecha_detalle_arrendemiento_datepicker"
															id="cont_detalle_contrato_firmado_fecha_vencimiento_param"
															value="<?php echo $fecha_fin_agente;?>"
															readonly="readonly"
															style="height: 34px;"
															>
														<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="cont_detalle_proveedor_contrato_firmado_fecha_vencimiento_param"></label>
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
															class="form-control fecha_detalle_arrendemiento_datepicker"
															id="cont_detalle_contrato_firmado_fecha_suscripcion_param"
															value="<?php echo $con_final_fecha_suscripcion;?>"
															readonly="readonly"
															style="height: 34px;"
															>
														<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="cont_detalle_proveedor_contrato_firmado_fecha_suscripcion_param"></label>
													</div>
												</div>
											</div>

											

											<div class="col-xs-12 col-md-6 col-lg-6 item_filter">
												<div class="form-group">
													<div class="control-label">Seleccione el contrato firmado (en formato pdf):</div>
													<input type="file" id="archivo_contrato" name="archivo_contrato" required accept=".pdf">
												</div>
											</div>

											<div class="col-xs-12 col-md-6 col-lg-6 item_filter">
												<div class="form-group">
													<label>Renovación Automática:</label>
													<select class="form-control select2" id="cont_detalle_renovacion_automatica" name="cont_detalle_renovacion_automatica">
														<option value="0">- Seleccione -</option>
														<option value="1">SI</option>
														<option value="2">NO</option>
													</select>
												</div>
											</div>

											<div style="margin-right: 10px; margin-left: 5px;">
												<button type="button" class="btn btn-success btn-xs btn-block" id="btn_guardar_contrato_firmado" onclick="sec_contrato_detalle_solicitud_agente_verificar_documentos()">
													<i class="icon fa fa-plus"></i>
													<span id="demo-button-text">Agregar contrato firmado</span>
												</button>
											</div>
										</form>
									<?php
									}
									?>
								</div>
							</div>
						</div>
						<?php } ?>
						<!-- FIN PANEL CONTRATO FIRMADO -->

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
										DATE_FORMAT(r.fecha_carta,'%d-%m-%Y') AS fecha_carta,
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
											$fecha_carta = $sel["fecha_carta"];
											$usuario_solicitud = $sel["usuario_solicitud"];
											$anexo_archivo_id = $sel["anexo_archivo_id"];
											$estado_solicitud_id = $sel["estado_solicitud_id"];
											$usuario_aprobado = $sel["usuario_aprobado"];
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
											$fecha_carta = !Empty($fecha_carta) ? date("d-m-Y",strtotime($fecha_carta)):"";

											$estado_aprobacion_gerencia = $sel['estado_aprobacion_gerencia'];
											$estado_aprobacion = $sel['estado_aprobacion'];
											$aprobado_por = $sel['aprobado_por'];
											$fecha_aprobacion_gerencia = $sel['fecha_aprobacion_gerencia'];
											$abogado = $sel['abogado'];

										?>
										<br>
										<?php if ((array_key_exists($menu_consultar,$usuario_permisos) && in_array("reenviar_resolucion_firmada", $usuario_permisos[$menu_consultar])) && ($estado_solicitud_id == 2 ) ) { ?>
										<button type="button" onclick="sec_contrato_detalle_reenviar_resolucion(<?=$sel['id']?>,'6')" style="float: right" class="btn btn-xs btn-info"><span class="fa fa-envelope-o"></span> Reenviar por email</button>
										<br>
										<?php } ?>
										<table class="table table-responsive table-hover no-mb" style="font-size: 10px;">
											<tbody>
												
												<tr style="text-transform: none;">
													<td><b>Motivo:</b></td>
													<td>
														<?php echo $motivo; ?>
													</td>
												</tr>
												<tr style="text-transform: none;">
													<td><b>Fecha de Carta:</b></td>
													<td>
														<?php echo $fecha_carta; ?>
													</td>
												</tr>
												<tr style="text-transform: none;">
													<td><b>Usuario quien registro la solicitud:</b></td>
													<td>
														<?php echo $usuario_solicitud; ?>
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
																<button onclick="sec_contrato_detalle_resolucion_cambiar_estado_legal(<?=$sel['id']?>,6)" class="btn btn-success form-control" type="button"><span class="glyphicon glyphicon-floppy-disk"></span> Guardar Estado</button>
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

						<!-- INICIO PANEL RESOLUCION CONTRATO -->
						<?php if (($area_id == '33' AND $cargo_id != 25) || $area_id == 6 || $permiso_cambiar_estado_contrato) { ?>
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
									DATE_FORMAT(ce.created_at,'%d-%m-%Y %H:%i:%s') AS fecha,
									ce.motivo,
									CONCAT(IFNULL(tpa.nombre, ''),' ',IFNULL(tpa.apellido_paterno, ''),	' ',	IFNULL(tpa.apellido_materno, '')) AS usuario

									FROM cont_contrato_estado AS ce
									INNER JOIN tbl_usuarios tu ON ce.user_created_id = tu.id
									INNER JOIN tbl_personal_apt tpa ON tu.personal_id = tpa.id
									WHERE ce.contrato_id = ".$contrato_id."
									ORDER BY ce.id ASC");
									$cantRegEstado = mysqli_num_rows($sel_query_estado_contrato);
									?>							
									<?php 
									if ($cantRegEstado > 0){
										$index_estado = 1;
										while($sel=$sel_query_estado_contrato->fetch_assoc()){
									?>
											<table class="table table-responsive table-hover no-mb mt-1" style="font-size: 10px;">
												<tbody>
												<tr style="text-transform: none;">
														<td colspan="2" class="text-center"><b>Cambio de Estado #<?=$index_estado?></b></td>
													</tr>
													<tr style="text-transform: none;">
														<td style="width:30%"><b>Estado:</b></td>
														<td style="width:70%"><?=$sel['estado']?></td>
													</tr>
													<tr style="text-transform: none;">
														<td style="width:30%"><b>Motivo:</b></td>
														<td style="width:70%; white-space: pre-line;"><?=$sel['motivo']?></td>
													</tr>
													<tr style="text-transform: none;">
														<td style="width:30%"><b>Fecha:</b></td>
														<td style="width:70%"><?=$sel['fecha']?></td>
													</tr>
													<tr style="text-transform: none;">
														<td style="width:30%"><b>Usuario:</b></td>
														<td style="width:70%"><?=$sel['usuario']?></td>
													</tr>
												</tbody>
											</table>
											<br	>
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
													<select id="contrato_estado" style="font-weight: 700;" class="form-control text-center text-white <?=$estado_contrato == 1 ? 'bg-success':'bg-danger'?>" >
														<option class="text-white" <?=$estado_contrato == 1 ? 'selected':''?> value="1">Activo</option>
														<option class="text-white" <?=$estado_contrato == 2 ? 'selected':''?> value="2">Inactivo</option>
													</select>
												</td>
											</tr>
											<tr style="text-transform: none;">
												<td style="width:30%"><b>Motivo:</b></td>
												<td style="width:70%">
													<textarea id="contrato_motivo" rows="5" class="form-control" placeholder="Ingrese un motivo"></textarea>
												</td>
											</tr>
											<tr style="text-transform: none;">
												<td></td>
												<td class="text-center">
													<button onclick="sec_contrato_detalle_cambiar_estado()" type="button" class="btn btn-success form-control"><strong>Guardar</strong></button>
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
														<option <?=$estado_solicitud == $li["id"] ? '- selected -':''; ?>  value="<?=$li["id"] == 1 ? '':$li["id"]; ?>"><?=$nombre_estado_solicitud; ?></option>
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
						
						<!-- INICIO PANEL AUTORIZACIÓN MINCETUR -->
						<?php
							if(($area_id == 33) || $area_id == 6){
						?>
						<div class="panel">
							<div class="panel-heading" role="tab" id="browsers-this-autorizacion-mincetur">
								<div class="panel-title">
									<a href="#browsers-autorizacion-mincetur" class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" aria-expanded="true" aria-controls="browsers-autorizacion-mincetur">
										Autorización Mincetur
									</a>
								</div>
							</div>
							<div id="browsers-autorizacion-mincetur" class="panel-collapse collapse" role="tabpanel" aria-labelledby="browsers-this-autorizacion-mincetur">
								<div class="panel-body">
									<div class="col-xs-12 col-md-12 col-lg-12" style="padding-left: 0px;">
										<table class="table table-responsive table-hover" style="font-size: 10px;">
											<thead style="background: none;">
												<tr>
													<th colspan="33" class="text-center">AUTORIZACIÓN MINCETUR
														<a type="button" style="float: right; margin-left: 5px;" class="btn btn-primary btn-xs" data-toggle="modal" data-target="#moda_subir_archivo_req_autorizacion_mincetur"><i class="fa fa-upload"></i> Nueva Autorización Mincetur</a>
													</th>
												</tr>
											</thead>
											<tbody>
											<tr style="text-transform: none;">
												<th class="text-center">Nombre del Documento</th>
												<th class="text-center" colspan="3">Visualizar</th>
											</tr>
											<?php 
											$query = "SELECT 
												t1.archivo_id,
												t1.nombre,
												t1.extension,
												t1.ruta,
												t2.nombre_tipo_archivo
												FROM cont_archivos t1
												INNER JOIN cont_tipo_archivos t2 ON t2.tipo_archivo_id = t1.tipo_archivo_id
												WHERE t1.contrato_id = ? 
												AND t1.tipo_archivo_id = ? 
												AND t1.status = 1";
											$stmt = $mysqli->prepare($query);
											$stmt->bind_param('ii', $contrato_id, $tipo_archivo_id_mincetur);
											$stmt->execute();
											$result = $stmt->get_result();
											$count = 1;
											if ($result->num_rows > 0) {
												while ($row = $result->fetch_assoc()) {
													$ruta = str_replace("/var/www/html", "", $row["ruta"]);
											?>
												<tr style="text-transform: none;">
													<td>
														<?php echo $row['nombre_tipo_archivo'] ?> N°<?php echo $count ?>
													</td>
													<td>
														<a type="button" class="btn btn-success btn-xs btn-block btn_reemplazar_autorizacion_mincetur" data-toggle="modal" data-target="#moda_reemplazar_archivo_req_autorizacion_mincetur" data-archivo_id="<?php echo $row["archivo_id"] ?>"><i class="fa fa-upload"></i> Reemplazar</a>
													</td>
													<td>
														<a type="button" onclick="sec_contrato_detalle_solicitud_ver_documento_en_visor('<?php echo htmlspecialchars($ruta, ENT_QUOTES); ?>','<?php echo htmlspecialchars(trim($row["nombre"]), ENT_QUOTES); ?>','<?php echo htmlspecialchars(trim($row["extension"]), ENT_QUOTES); ?>','AUTORIZACIÓN MINCETUR');" class="btn btn-success btn-xs btn-block" data-toggle="tooltip" data-placement="top"><span class="fa fa-eye"></span> Ver Autorización Mincetur</a>
													</td>
													<td>
														<button type="button" class="btn btn-danger btn-xs btn-block btn_eliminar_autorizacion_mincetur" data-archivo_id="<?php echo $row["archivo_id"] ?>" data-toggle="tooltip" data-placement="top"><span class="fa fa-trash"></span></button>
													</td>
												</tr>
											<?php 
											$count++;
												} 
											} else { 
											?>
												<tr>
													<td colspan="3" class="text-center">No hay autorizaciones de Mincetur disponibles.</td>
												</tr>
											<?php 
											} 
											?>
											</tbody>
										</table>			
									</div>
								</div>
							</div>
						</div>
						<?php } ?>
						<!-- FIN PANEL AUTORIZACIÓN MINCETUR -->

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
											<td><?php echo $abogado;?></td>
											<?php if ($btn_editar_solicitud || ($etapa_id == 1 && $area_id == 33) || true) { ?>
											<td>
												<a 	class="btn btn-success btn-xs" 
													id="btn_editar_jefe_abogado"
													onclick="sec_contrato_detalle_agente_solicitud_editar_solicitud('Generales','cont_contrato','abogado_id','Abogado','select_option','<?php echo $abogado; ?>','obtener_abogados');">
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

						<?php 
						if($etapa_id == 1 && $cancelado_id != 1)
						{
							if(is_null($fecha_aprobacion))
							{
								$boton_aprobacion_gerencia = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' AND sub_sec_id = '$sub_sec_id' LIMIT 1")->fetch_assoc();
								$id_boton_aprobacion_gerencia = $boton_aprobacion_gerencia["id"];

								if(array_key_exists($id_boton_aprobacion_gerencia,$usuario_permisos) && in_array("validaBotonSolicitud", $usuario_permisos[$id_boton_aprobacion_gerencia]))
								{
									?>

									<!-- INICIO PANEL APROBADOR -->
									<div class="panel">
										<div class="panel-heading" role="tab" id="browsers-this-cambiar-aprobador-heading">
											<div class="panel-title">
												<a href="#browsers-this-cambiar-aprobador" class="collapsed" role="button" data-toggle="collapse"
												data-parent="#accordion" aria-expanded="true" aria-controls="browsers-this-cambiar-aprobador">
												Aprobación de Gerencia
												</a>
											</div>
										</div>
										<div id="browsers-this-cambiar-aprobador" class="panel-collapse collapse" role="tabpanel" aria-labelledby="browsers-this-cambiar-aprobador-heading">
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
														<button type="button" class="btn btn-success btn-xs btn-block col-md-6 cont_btn_guardar_aprobar_agente" value="1" style="height: 30px;">
															<span id="demo-button-text">
																<i class="glyphicon glyphicon-saved"></i>
																Aceptar solicitud
															</span>
														</button>
													</div>

													<div class="col-xs-12 col-md-6 col-lg-6 item_filter">
														<button type="button" class="btn btn-danger btn-xs btn-block cont_btn_guardar_aprobar_agente" value="0" style="height: 30px;">
															<span id="demo-button-text">
																<i class="glyphicon glyphicon-remove-sign"></i>
																Rechazar solicitud
															</span>
														</button>
													</div>
												</form>
												
											</div>
										</div>
									</div>
									<!-- FIN PANEL APROBADOR -->
									<?php
								}
							}
						}?>



					</div>
				</div>
			</div>
		</div>
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
				<h4 class="modal-title" id="myModalLabel">Editar Solicitud de Agente</h4>
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
												<input type="number" id="editar_solicitud_valor_decimal" class="filtro txt_filter_style" style="width: 100%;" placeholder="0.00">
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
											<input type="hidden" id="ubigeo_id_nuevo">
											<input type="hidden" id="ubigeo_text_nuevo">
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
				<button type="button" class="btn btn-success" onclick="sec_contrato_detalle_agente_solicitud_editar_campo_solicitud('modal_editar_solicitud');">
					<i class="icon fa fa-edit"></i>
					<span id="demo-button-text">Editar</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL EDITAR SOLICITUD -->


<!-- INICIO MODAL SUBIR ARCHIVOS - AUTORIZACIÓN MINCETUR -->
<div id="moda_subir_archivo_req_autorizacion_mincetur" class="modal fade" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">
				
				<h4 class="modal-title">Subir Archivo Nuevo</h4>
			</div>
			<form id="formArchivosModal_req_autorizacion_mincetur" method="POST" enctype="multipart/form-data">
				<input type="hidden" name="id_archivo" id="id_archivo">
				<input type="hidden" name="id_contrato_req_file_mincetur" id="id_contrato_req_file_mincetur" value="<?php echo $contrato_id; ?>">
				<input type="hidden" name="tipo_archivo_id_mincetur" id="tipo_archivo_id_mincetur" value="<?php echo $tipo_archivo_id_mincetur; ?>">
				<input type="hidden" name="modal_subir_arch_contrato_detalle_id" id="modal_subir_arch_contrato_detalle_id">
				
				<div class="modal-body">
						<div class="row">
							<div class="col-lg-12">

								<div class="form-group col-md-12">
									<label for="fileArchivo_requisitos_autorizacion_mincetur">Nombre file:</label>
									<div class="input-container">
										<input type="file" id="fileArchivo_requisitos_autorizacion_mincetur" name="fileArchivo_requisitos_autorizacion_mincetur" multiple="multiple" accept='.jpeg, .jpg, .png, .pdf' style="display: none;">
										<button type="button" class="browse-btn" id="btnBuscarFile_req_autorizacion_mincetur">
											Seleccionar
										</button>
										<span class="file-info" id="txtFile_req_autorizacion_mincetur"></span>
									</div>
								</div>

								<div class="form-group col-md-12" id="divMensajeAlertaLicFuncionamiento">
								</div>
							</div>
						</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-success"><i class="fa fa-upload"></i> Enviar</button>
					<button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
				</div>
			</form>
		</div>
	</div>
</div>
<!-- FIN MODAL SUBIR ARCHIVOS - AUTORIZACIÓN MINCETUR -->





<!-- INICIO MODAL REEMPLAZAR ARCHIVOS - AUTORIZACIÓN MINCETUR -->
<div id="moda_reemplazar_archivo_req_autorizacion_mincetur" class="modal fade" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">
				
				<h4 class="modal-title">Reemplazar archivos</h4>
			</div>
			<form id="formArchivosModal_reemplazar_autorizacion_mincetur" method="POST" enctype="multipart/form-data">
				<input type="hidden" name="id_archivo" id="id_archivo">
				<input type="hidden" name="id_contrato_req_file_mincetur" id="id_contrato_req_file_mincetur" value="<?php echo $contrato_id; ?>">
				<input type="hidden" name="tipo_archivo_id_mincetur" id="tipo_archivo_id_mincetur" value="<?php echo $tipo_archivo_id_mincetur; ?>">
				<input type="hidden" name="modal_subir_arch_contrato_detalle_id" id="modal_subir_arch_contrato_detalle_id">
				
				<div class="modal-body">
						<div class="row">
							<div class="col-lg-12">

								<div class="form-group col-md-12">
									<label for="fileArchivo_reemplazar_autorizacion_mincetur">Nombre file:</label>
									<div class="input-container">
										<input type="hidden" id="archivoIdField" value="">
										<input type="file" id="fileArchivo_reemplazar_autorizacion_mincetur" name="fileArchivo_reemplazar_autorizacion_mincetur" multiple="multiple" accept='.jpeg, .jpg, .png, .pdf' style="display: none;">
										<button type="button" class="browse-btn" id="btnBuscarFile_reemplazar_autorizacion_mincetur">
											Seleccionar
										</button>
										<span class="file-info" id="txtFile_reemplazar_autorizacion_mincetur"></span>
									</div>
								</div>

								<div class="form-group col-md-12" id="divMensajeAlertaLicFuncionamiento">
								</div>
							</div>
						</div>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-success"><i class="fa fa-upload"></i> Enviar</button>
					<button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
				</div>
			</form>
		</div>
	</div>
</div>
<!-- FIN MODAL REEMPLAZAR ARCHIVOS - AUTORIZACIÓN MINCETUR -->


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
									<label for="fileArchivo_requisitos_arrendamiento">Nombre Archivo:</label>
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


<!-- INICIO MODAL DOCUMENTOS PENDIENTES DE SUBIR -->
<div id="modal_documentos_pendientes_por_subir" class="modal fade" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Falta subir los siguientes documentos</h4>
			</div>
			<form>
				<div class="modal-body">
					<div id="div_documentos_pendientes_por_subir" class="row">
					</div>
				</div>
				<div class="modal-footer">					
					<button type="button" class="btn btn-danger" data-dismiss="modal">
						<i class="fa fa-close"></i> Cancelar operación
					</button>
					<button type="button" onclick="sec_contrato_detalle_solicitud_agente_guardar_contrato_firmado()" class="btn btn-success">
						<i class="fa fa-save"></i> Guardar de todos modos
					</button>
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
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_num_docu_propietario" >
							<div class="form-group">
								<div class="control-label" id="label_num_docu_propietario">Número de documento de identidad del propietario:</div>
								<input type="text" id="modal_propietario_num_docu" name="modal_propietario_num_docu" class="filtro txt_filter_style" maxlength="12" style="width: 100%; height: 30px;">
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
				<button type="button" class="btn btn-success" id="btn_agregar_propietario_agente">					
					<i class="icon fa fa-plus"></i>
					Agregar propietario
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL NUEVO PROPIETARIO -->

<!-- INICIO MODAL CANCELAR SOLICITUD -->
<div id="modal_cancelar_solicitud" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Cancelar Solicitud de Agente</h4>
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
					<span>Cancelar Solicitud de Agente</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL CANCELAR SOLICITUD -->


<!-- INICIO MODAL NUEVOS ANEXOS -->
<div id="modalNuevosContratoFirmado" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-xs" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<input type="hidden" name="sec_modal_cont_firmado_contrato_id" id="sec_modal_cont_firmado_contrato_id" value="<?php echo $contrato_id; ?>">
				<input type="hidden" name="sec_modal_cont_firmado_contrato_detalle_id" id="sec_modal_cont_firmado_contrato_detalle_id" value="0">
				<input type="hidden" name="sec_modal_cont_firmado_tipo_contrato_id" id="sec_modal_cont_firmado_tipo_contrato_id" value="6">
				<input type="hidden" name="sec_modal_cont_firmado_tipo_archivo_id" id="sec_modal_cont_firmado_tipo_archivo_id" value="16">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Agregar Contrato Firmado</h4>
			</div>
			<div class="modal-body">
				<form id="sec_nuevo_form_modal_nuevo_contrato_firmado" name="sec_nuevo_form_modal_nuevo_contrato_firmado" method="POST" enctype="multipart/form-data" autocomplete="off">
					<div class="row">
						<div class="col-md-12">
							<label>Contrato Firmado:</label>
							<input type="file" name="fileArchivo_contrato_firmado" required accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip">
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
				<button type="button" class="btn btn-success" id="sec_nuevo_modal_guardar_contrato_firmado" onclick="sec_cont_detalle_modal_guardar_nuevo_contrato_firmado()">
					<i class="icon fa fa-save"></i>
					<span>Agregar</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL NUEVOS ANEXOS -->

<div id="modalAgregarDocumentoResolucionContrato" class="modal fade" data-backdrop="static" data-keyboard="false">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Documento - Nuevo Resolución Contrato</h4>
			</div>
			<form id="formArchivosModal_documento_resolucion_contrato" method="POST" enctype="multipart/form-data">
				<div class="modal-body">
						<div class="row">
							<div class="col-lg-12">

								<div class="form-group col-md-12">
									<label for="fileArchivo_resolucion_contrato">Nombre Archivo:</label>
									<div class="input-container">

										<input type="file" id="fileArchivo_resolucion_contrato" name ="fileArchivo_resolucion_contrato" multiple="multiple" accept='.jpeg, .jpg, .png, .pdf'>

										<button class="browse-btn" id="btnBuscarFile_req_solicitud_resolucion_contrato">
											Seleccionar
										</button>

										<span class="file-info" id="txtFile_req_solicitud_resolucion_contrato"></span>
									</div>
								</div>

								<div class="form-group col-md-12" id="divMensaje_modal_documento_resolucion_contrato">
								</div>
							</div>
						</div>
				</div>
				<div class="modal-footer">
					<button type="button" onclick="modal_documento_subir_resolucion_contrato();" class="btn btn-success"><i class="fa fa-upload"></i> Enviar</button>
					<button type="button" data-dismiss="modal" class="btn btn-danger">Cerrar</button>
				</div>
			</form>
		</div>
	</div>
</div>
<?php
}


function sec_contrato_detalle_solicitud_de_meses_a_anios_y_meses($meses) {
	if ($meses < 12) {
		$anio_y_meses = $meses . ' meses';
	} else {
		$anio = intval($meses/12);
		$meses_restantes = $meses%12;

		if ($anio == 0) {
			$anio = '';
		} else if($anio == 1){
			$anio = $anio . ' año';
		} else if ($anio > 1) {
			$anio = $anio . ' años';
		}

		if ($meses_restantes == 0) {
			$meses_restantes = '';
		} else if($meses_restantes == 1){
			$meses_restantes = ' y ' . $meses_restantes . ' mes';
		} else if ($meses_restantes > 1) {
			$meses_restantes = ' y ' . $meses_restantes . ' meses';
		}

		return $anio . $meses_restantes;
	}
}

?>