<?php 
$this_menu = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sub_sec_id = '$sub_sec_id' LIMIT 1")->fetch_assoc();
$menu_id = $this_menu["id"];
$id_user = $login?$login['id']:null;
// if(!array_key_exists($menu_id,$usuario_permisos)){
// 	echo "&nbsp;&nbsp;&nbsp;No tienes permisos para este recurso.";
// 	$continuar=false;
// }
$permiso_agregar_monto='false';
if(in_array("agregar_monto", $usuario_permisos[$menu_id])){
	$permiso_agregar_monto='true';
}
$permiso_agregar_recibo='false';
if(in_array("agregar_recibo", $usuario_permisos[$menu_id])){
	$permiso_agregar_recibo='true';
}

$id_recibo = $_GET["id"];


$query = "SELECT sp.*,  t.nombre AS  nombre_tipo_servicio, lc.nombre AS  nombre_local, lc.cc_id AS centro_costo,
case when sp.id_tipo_servicio_publico = 1 
then IFNULL(i.num_suministro_luz,'') else IFNULL(i.num_suministro_agua,'') end as numero_suministro,
case when sp.id_tipo_servicio_publico = 1 
then IFNULL(i.tipo_compromiso_pago_agua,'') else IFNULL(i.tipo_compromiso_pago_luz,'') end as tipo_compromiso,
case when sp.id_tipo_servicio_publico = 1
then IFNULL(ctp.nombre,'') else IFNULL(ctp_2.nombre,'') end as tipo_compromiso_nombre,
				
case when sp.id_tipo_servicio_publico = 1 
then IFNULL(i.monto_o_porcentaje_agua,'') else IFNULL(i.monto_o_porcentaje_luz,'') end as monto_pct,
est.nombre as nombre_estado


FROM cont_local_servicio_publico AS sp
INNER JOIN cont_tipo_servicio_publico AS t ON t.id = sp.id_tipo_servicio_publico
INNER JOIN tbl_locales AS lc ON lc.id = sp.id_local
INNER JOIN cont_contrato c on lc.contrato_id = c.contrato_id
INNER JOIN cont_inmueble i on i.contrato_id = c.contrato_id
INNER JOIN cont_tipo_pago_servicio ctp on i.tipo_compromiso_pago_agua = ctp.id
INNER JOIN cont_tipo_pago_servicio ctp_2 on i.tipo_compromiso_pago_luz = ctp_2.id
INNER JOIN cont_tipo_estado_servicio_publico est on sp.estado = est.id
WHERE sp.id = ".$id_recibo;
$query = $mysqli->query($query);
$recibo = $query->fetch_assoc();
$recibo['periodo_consumo'] = date("Y-m", strtotime($recibo['periodo_consumo']));
$recibo['fecha_emision'] = date("d-m-Y", strtotime($recibo['fecha_emision']));
$recibo['fecha_vencimiento'] = date("d-m-Y", strtotime($recibo['fecha_vencimiento']));

$path_img = "files_bucket/contratos/servicios_publicos/";
$path_voucher = "files_bucket/contratos/servicios_publicos/";
if($recibo['id_tipo_servicio_publico'] == 1){ //Luz
	$path_img .= "luz/";
	$path_voucher .= "luz/";
}else{
	$path_img .= "agua/";
	$path_voucher .= "agua/";
}
$path_img .= $recibo['nombre_file'];
$path_voucher .= $recibo['nombre_file_voucher'];

$nuevo_id = "sec_serv_pub_img_servicio_publico".$recibo['id']."_".$recibo['numero_suministro'];
$nuevo_id_voucher = "sec_serv_pub_img_servicio_publico".$recibo['id']."_".$recibo['numero_suministro'];

$area = '';

$disabled = '';
if ($recibo['estado'] == 2 || $recibo['estado'] == 3 || $recibo['estado'] == 4 || $recibo['estado'] == 5) {
	$disabled = 'disabled';
}
$query = "SELECT ar.nombre
	FROM tbl_usuarios u 
	INNER JOIN tbl_personal_apt p ON p.id = u.personal_id
	INNER JOIN tbl_areas ar ON p.area_id = ar.id
	WHERE u.id = ".$id_user;
$sel_query = $mysqli->query($query);
while($sel = $sel_query->fetch_assoc()){
	$area = !Empty($sel["nombre"]) ? $sel["nombre"]:"";
}

?>

<style>
	fieldset.dhhBorder{
		border: 1px solid #ddd;
		padding: 0 15px 5px 15px;
		margin: 0 0 15px 0;
		box-shadow: 0px 0px 0px 0px #000;
		border-radius: 5px;
	}

	legend.dhhBorder{
		font-size: 14px;
		text-align: left;
		width: auto;
		padding: 0 10px;
		border-bottom: none;
		margin-bottom: 10px;
		text-transform: capitalize;
	}
	.diabled_all_content {
	    pointer-events: none;
	    opacity: 0.4;
	}

	.ocultar_div{
		visibility: collapse;
	}

	.hasDatepicker {
    	min-height: 28px !important;
	}
</style>
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
				<i class="icon icon-inline glyphicon glyphicon-briefcase"></i>  Servicios Públicos
			</h1>
		</div>

		<div class="col-xs-12 col-md-12 col-lg-6">
			<div class="panel" id="divDetalleSolicitud">
				<div class="panel-heading">
					<div class="panel-title" style="width: 300px; display: inline-block;">DETALLE DEL SERVICIO PÚBLICO</div>
				</div>
			</div>
			<div class="panel-body" style="padding: 0px; padding-top: 10px; font-size: 12px;">
					<form id="">
						<table class="table table-bordered table-hover">
							<tr>
								<th width="70%">Número de suministro:</th>
								<td width="30%">
									<input type="hidden" value="<?=$recibo['id']?>" name="sec_con_serv_pub_id_recibo" id="sec_con_serv_pub_id_recibo">
									<div class="input-group" style="width: 100%;">
										<input <?=$disabled?> type="text" value="<?=$recibo['numero_suministro']?>" class="form-control" disabled name="sec_con_serv_pub_num_suministro" id="sec_con_serv_pub_num_suministro" placeholder="123456789" style="text-align: right;" />
									</div>
								</td>
							</tr>
							<tr>
								<th>Número de recibo:</th>
								<td>
									<div class="input-group" style="width: 100%;">
										<input <?=$disabled?> type="text" value="<?=$recibo['numero_recibo']?>" class="form-control" name="sec_con_serv_pub_num_recibo" id="sec_con_serv_pub_num_recibo" placeholder="000000010" style="text-align: right; font-weight: bold;" />
									</div>
								</td>
							</tr>
							<tr>
								<th>Periodo de consumo:</th>
								<td>
									<div class="input-group" style="width: 100%;">
										<input <?=$disabled?> type="month" value="<?=$recibo['periodo_consumo']?>" class="form-control" name="sec_con_serv_pub_periodo_consumo" id="sec_con_serv_pub_periodo_consumo" placeholder="123456789" style="text-align: right;" />
									</div>
								</td>
							</tr>
							<tr>
								<th>Fecha de emisión:</th>
								<td>
									<div class="input-group" style="width: 100%;">
										<input <?=$disabled?> type="text" value="<?=$recibo['fecha_emision']?>" class="form-control servicio_publico_datepicker" name="sec_con_serv_pub_fecha_emision" id="sec_con_serv_pub_fecha_emision" placeholder="123456789" style="text-align: right;" />
									</div>
								</td>
							</tr>
							<tr>
								<th>Fecha de vencimiento:</th>
								<td>
									<div class="input-group" style="width: 100%;">
										<input <?=$disabled?> type="text" value="<?=$recibo['fecha_vencimiento']?>"  class="form-control servicio_publico_datepicker" name="sec_con_serv_pub_fecha_vencimiento" id="sec_con_serv_pub_fecha_vencimiento" placeholder="123456789" style="text-align: right;" />
									</div>
								</td>
							</tr>
							<tr>
								<th>Compromiso de pago:</th>
								<td>
									<div class="input-group" style="width: 100%;">
										<select <?=$disabled?> style="display:none;" class="form-control input_text col-5" name="sec_con_ser_pub_select_compromiso_pago"  id="sec_con_ser_pub_select_compromiso_pago" title="Seleccione el Jefe Comercial">
										</select>
										<input <?=$disabled?> type="text" value="<?=$recibo['tipo_compromiso_nombre']?>" disabled class="form-control" name="sec_con_serv_pub_id_tipo_compromiso_nombre" id="sec_con_serv_pub_id_tipo_compromiso_nombre" placeholder="" style="text-align: right;" />
									</div>
								</td>
							</tr>
							<tr>
								<th>Total mes actual:</th>
								<td>
									<div class="input-group" style="width: 100%;">
										<input <?=$disabled?> type="number" step="any" value="<?=$recibo['monto_total']?>" class="form-control" name="sec_con_serv_pub_monto_mes_actual" id="sec_con_serv_pub_monto_mes_actual" placeholder="Ejm: 125.60" style="text-align: right; font-weight: bold;" />
									</div>
								</td>
							</tr>
							<tr>
								<th>Total a pagar:</th>
								<td>
									<div class="input-group" style="width: 100%;">
										<input <?=$disabled?> type="text"  step="any" value="<?=$recibo['total_pagar']?>"class="form-control"  name="sec_con_serv_pub_total_pagar" id="sec_con_serv_pub_total_pagar" placeholder="0.00" style="text-align: right;" />
									</div>
								</td>
							</tr>
							<tr>
								<th>Caja Chica ?:</th>
								<td>
									<div class="input-group text-center" style="width: 100%;">
										<input <?=$disabled?> type="checkbox" <?=$recibo['aplica_caja_chica'] == 1 ? 'checked':''?> value="" id="sec_con_serv_pub_check_cajachica_id_detalle" />
									</div>
								</td>
							</tr>

							<tr id="block-caja-chica" class="<?=$recibo['aplica_caja_chica'] == 0 ? 'ocultar_div':''?> ">
								<th><label style="text-align: left;">Se le paga a: <span style="color: red;">(*)</span></label>:</th>
								<td>
									<div class="input-group" style="width: 100%;">
										<input <?=$disabled?> type="text" value="<?=$recibo['nombre_paga_caja_chica']?>" class="form-control" name="sec_con_serv_pub_nombre_pagar" id="sec_con_serv_pub_nombre_pagar" placeholder="Ejm: María Perez" style="text-align: right; font-weight: bold;" maxlength="100" />
									</div>
								</td>
							</tr>

							<tr>
								<th><label style="text-align: left;">Estado:</th>
								<td class="text-right">
									<strong><?=$recibo['nombre_estado']?></strong> 
								</td>
							</tr>
							
										
						</table>

					</form>
			</div>
		</div>
		<div class="col-xs-12 col-md-12 col-lg-6">
			<div class="panel" id="divDetalleSolicitud">
				<div class="panel-body" style="padding: 5px 10px 5px 10px;">
					<div class="panel-group no-mb" id="accordion" role="tablist" aria-multiselectable="true">
						<div class="panel">

							<div class="panel-heading" role="tab" id="browsers-observaciones-heading">
								<div class="panel-title">
									<a href="#browsers-observaciones" role="button" data-toggle="collapse"
									   data-parent="#accordion" aria-expanded="true" aria-controls="browsers-observaciones">
										OBSERVACIONES
									</a>
								</div>
							</div>
							
							<div id="browsers-observaciones" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="browsers-observaciones-heading">
								<div class="panel-body">

									<div id="div_observaciones" class="timeline" style="font-size: 11px;">
									</div>
									<hr>
									<?php 
									if ($disabled  == '') {
									?>
									<textarea rows="3" id="servicio_publico_observaciones" placeholder="Ingrese sus observaciones" style="width: 100%"></textarea>
									<button class="btn btn-success btn-xs btn-block" type="submit" onclick="sec_contrato_detalle_servicio_publico_guardar_observaciones();">
										<i class="fa fa-plus"></i> Agregar Observaciones
									</button>
									<?php 
									}
									?>
								</div>
							</div>


							<!-- INICIO RECIBO -->
							<div class="panel-heading" role="tab" id="browsers-recibo-heading">
								<div class="panel-title">
									<a href="#browsers-recibo" role="button" data-toggle="collapse"
									   data-parent="#accordion" aria-expanded="false" aria-controls="browsers-recibo">
										RECIBO
									</a>
								</div>
							</div>

							<div id="browsers-recibo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="browsers-recibo-heading">
								<div class="panel-body">
									<h4 id="sec_con_serv_pub_mensaje_imagen" style="text-align: center;"></h4>
									<div class="row" style="margin-bottom: 20px;" id="sec_con_serv_pub_div_imagen_recibo">
									<?php 
									if ($recibo['extension'] == "pdf") {
									?>
										<iframe src="<?=$path_img?>" class="col-xs-12 col-md-12 col-sm-12" width="100%" height="400"></iframe>
									<?php
									}else if ($recibo['extension'] == 'jpg' || $recibo['extension'] == 'png' || $recibo['extension'] == 'jpeg') {
									?>
										<div class="col-md-12">
											<div align="center" style="height: 100%; width: 100%;">
										  		<img  id="<?=$nuevo_id?>" src="<?=$path_img?>" width="300px" height="350px" />
										   </div>
										</div>
									<?php
									$ruta = $recibo['ruta_download_file'];
									}
									?>
									</div>
									<?php 
									if ($recibo['extension'] == 'jpg' || $recibo['extension'] == 'png' || $recibo['extension'] == 'jpeg') {
									?>
									<div class="col-xs-6 col-md-6 col-sm-6" style="text-align: center; margin-bottom: 5px;" id="sec_con_serv_pub_div_VerImagenFullPantalla">
										<button type="button" onclick="sec_contrato_detalle_solicitud_ver_imagen_full_pantalla('<?=$path_img?>')" class="btn btn-block btn-block btn-info" id="sec_con_serv_pub_ver_full_pantalla">
											<i class="fa fa-arrows-alt"></i>  Pantalla Completa
										</button>
									</div>
									<div class="col-xs-6 col-md-6 col-sm-6" style="text-align: center; margin-bottom: 5px;" id="sec_con_serv_pub_btn_descargar_imagen_recibo">											
										<a 
											onclick="sec_contrato_detalle_servicio_publico_btn_descargar('<?php $ruta ?>');"
											id="sec_con_serv_pub_descargar_imagen_a" class="btn btn-block btn-block btn-success"><i class="fa fa-arrow-circle-down"></i> Descargar</a>
									</div>
									<?php } ?>
								</div>
							</div>
							<!-- FIN RECIBO -->

							<!-- INICIO VOUCHER -->
							<div class="panel-heading" role="tab" id="browsers-voucher-heading">
								<div class="panel-title">
									<a href="#browsers-voucher" role="button" data-toggle="collapse"
									   data-parent="#accordion" aria-expanded="false" aria-controls="browsers-voucher">
										VOUCHER
									</a>
								</div>
							</div>
							<div id="browsers-voucher" class="panel-collapse collapse" role="tabpanel" aria-labelledby="browsers-voucher-heading">
								<div class="panel-body">
									<h4 id="sec_con_serv_pub_mensaje_imagen" style="text-align: center;"></h4>
									<div class="row" style="margin-bottom: 20px;" id="sec_con_serv_pub_div_imagen_recibo">
									<?php 
									if ($recibo['extension_voucher'] == "pdf") {
									?>
										<iframe src="<?=$path_voucher?>" class="col-xs-12 col-md-12 col-sm-12" width="100%" height="400"></iframe>
									<?php
									}else if ($recibo['extension_voucher'] == 'jpg' || $recibo['extension_voucher'] == 'png' || $recibo['extension_voucher'] == 'jpeg') {
									?>
										<div class="col-md-12">
											<div align="center" style="height: 100%; width: 100%;">
										  		<img  id="<?=$nuevo_id_voucher?>" src="<?=$path_voucher?>" width="300px" height="350px" />
										   </div>
										</div>
									<?php
									$ruta = $recibo['ruta_download_file_voucher'];
									}
									?>
									</div>
									<?php 
									if ($recibo['extension_voucher'] == 'jpg' || $recibo['extension_voucher'] == 'png' || $recibo['extension_voucher'] == 'jpeg') {
									?>
									<div class="col-xs-6 col-md-6 col-sm-6" style="text-align: center; margin-bottom: 5px;" id="sec_con_serv_pub_div_VerImagenFullPantalla">
										<button type="button" onclick="sec_contrato_detalle_solicitud_ver_imagen_full_pantalla('<?=$path_voucher?>')" class="btn btn-block btn-block btn-info" id="sec_con_serv_pub_ver_full_pantalla">
											<i class="fa fa-arrows-alt"></i>  Pantalla Completa
										</button>
									</div>
									<div class="col-xs-6 col-md-6 col-sm-6" style="text-align: center; margin-bottom: 5px;" id="sec_con_serv_pub_btn_descargar_imagen_recibo">											
										<a 
											onclick="sec_contrato_detalle_servicio_publico_btn_descargar('<?php $ruta ?>');"
											id="sec_con_serv_pub_descargar_imagen_a" class="btn btn-block btn-block btn-success"><i class="fa fa-arrow-circle-down"></i> Descargar</a>
									</div>
									<?php } ?>
								</div>
							</div>
							<!-- FIN VOUCHER -->

							<br>
							
							<?php
							if (($recibo['estado'] == 1 || $recibo['estado'] == 6) && ($area == 'Contabilidad' || $area == 'Sistemas' ) ) {
							?>
							<div class="w-100 text-right">
								<button type="button" class="btn btn-success" onclick="sec_contrato_detalle_guardar_recibo()">
									<i class="icon fa fa-save"></i>
									<span id="btn_nombre_guardar_modal" >Validar</span>
								</button>
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
</div>




<!-- INICIO MODAL AGREGAR MONTO -->
<div id="sec_con_serv_pub_modal_agregar_monto" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-sm" role="document" style="width: 90%;">
		<div class="modal-content">
			<div class="modal-header">
				
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h3 class="modal-title" id="sec_con_serv_pub_title_modal_agregar_monto" style="text-align: center;"></h3>
				<h5 class="modal-title" id="sec_con_serv_pub_title_modal_agregar_monto_local" style="text-align: center; font-weight: bold;"></h5>
			</div>
			<div class="modal-body">
				
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" class="btn btn-success" data-dismiss="modal">
					<i class="icon fa fa-close"></i>
					Cancelar
				</button>
				<button type="button" class="btn btn-primary" id="sec_nuevo_modal_observar_archivo" onclick="sec_con_serv_pub_abrir_modal_observacion()">
					<i class="icon fa fa-eye"></i>
					<span>Observar</span>
				</button>
				<button type="button" class="btn btn-success" id="sec_nuevo_modal_guardar_archivo" onclick="guardarNuevoMontoRecibo()">
					<i class="icon fa fa-save"></i>
					<span id="btn_nombre_guardar_modal" >Validar</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL AGREGAR MONTO -->

<!-- INICIO MODAL OBSERVAR SERVICIO -->
<div id="sec_con_serv_pub_modal_observar_servicio" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h3 class="modal-title" style="text-align: center;">Agregar Observación</h3>
			</div>
			<div class="modal-body">
				<div class="form-group" style="width: 100%;">
					<label style="text-align: left;">Observación: </label>
					<div class="input-group" style="width: 100%;">
						 <textarea class="form-control" id="sec_con_serv_pub_observacion" rows="6"></textarea>
					</div>
				</div>

				<div class="form-group" style="width: 100%;">
					<label style="text-align: left;">Correos: </label>
					<div class="input-group" style="width: 100%;">
						 <textarea placeholder="Ingrese los correos separados con ','" class="form-control" id="sec_con_serv_pub_observacion_correos" rows="4"></textarea>
					</div>
				</div>
				
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" class="btn btn-success" data-dismiss="modal">
					<i class="icon fa fa-close"></i>
					Cancelar
				</button>
				<button type="button" class="btn btn-success" id="sec_nuevo_modal_guardar_observar_archivo" onclick="observarServicioPublico()">
					<i class="icon fa fa-save"></i>
					<span>Guardar</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL OBSERVAR SERVICIO -->

<!-- INICIO MODAL AGREGAR RECIBO -->
<div id="sec_con_serv_pub_modal_agregar_recibo" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-sm" role="document" style="width: 55%">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h3 class="modal-title" style="text-align: center;" id="sec_serv_pub_modal_title_agregar_recibo">Agregar Recibo</h3>
			</div>
			<div class="modal-body">
				<form id="sec_con_serv_pub_form_modal_agregar_recibo" method="POST" enctype="multipart/form-data">
					<div class="form-group col-md-4">
						<label>Fecha emision:</label>
						<div class="input-group">
							<input type="hidden" id="sec_con_serv_pub_fecha_emision_recibo" class="filtro"
                                data-col="sec_con_serv_pub_fecha_emision_recibo" name="sec_con_serv_pub_fecha_emision_recibo" value="<?php echo date("Y-m-d"); ?>"
                                data-real-date="sec_con_serv_pub_txt_fecha_emision">
                            <input type="text" class="form-control servicio_publico_fecha_emision_datepicker"
                                id="sec_con_serv_pub_txt_fecha_emision" value="<?php echo date("d-m-Y");?>"
                                readonly="readonly" style="height: 34px;" >
                            <label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="sec_con_serv_pub_txt_fecha_emision"></label>

						</div>
					</div>

					<div class="form-group col-md-4">
						<label>Fecha vencimiento:</label>
						<div class="input-group">
						  <input type="hidden" id="sec_con_serv_pub_fecha_vencimiento_recibo" class="filtro" 
						  		data-col="sec_con_serv_pub_fecha_vencimiento_recibo" 
						  		name="sec_con_serv_pub_fecha_vencimiento_recibo" value="<?php echo date("Y-m-d"); ?>"
                                data-real-date="sec_con_serv_pub_txt_fecha_vencimiento">
                            <input type="text" class="form-control servicio_publico_fecha_emision_datepicker"
                                id="sec_con_serv_pub_txt_fecha_vencimiento" value="<?php echo date("d-m-Y");?>"
                                readonly="readonly" style="height: 34px;">
                            <label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="sec_con_serv_pub_txt_fecha_vencimiento"></label>
						</div>
					</div>

					<div class="form-group col-md-4">
						<label>Monto total:</label>
						<input type="text" class="form-control monto sec_con_serv_pub_monto_total_recibo" 
						id="sec_con_serv_pub_monto_total_recibo" style="height: 34px;" placeholder="0.00">
					</div>

					<div class="form-group col-md-6">
						<label>Número de Recibo:</label>
						<input type="text" class="form-control" 
						id="sec_con_serv_pub_num_recibo_nuevo" style="height: 34px;" placeholder="0123456" maxlength="10">

						<label for="fileLocalServicioPublico">Nombre file:</label>
						<div class="input-container">
							<input type="file" id="sec_serv_pub_file_archivo_recibo" name="sec_serv_pub_file_archivo_recibo" required multiple="multiple" accept=".jpeg, .jpg, .png, .pdf">

							<button class="browse-btn btn btn-info" id="sec_serv_pub_btn_buscar_archivo">
								Seleccionar
							</button>
							<span class="file-info" id="sec_serv_pub_file_info"></span>
						</div>
					</div>

					<!--<div class="form-group col-md-3">
						<label>Periodo consumo:</label>
						<div class="input-group" style="width: 100%">
						  <input type="month" class="form-control txt_locales_servicio_publico_periodo_consumo" id="txt_locales_servicio_publico_periodo_consumo" style="height: 34px;">
						</div>
					</div>-->

					<div class="form-group col-md-6">
						<label>¿Desea ingresar algun comentario?</label>
						<textarea class="form-control" name="sec_con_serv_pub_comentario_recibo" id="sec_con_serv_pub_comentario_recibo" maxlength="255" style = "text-transform: uppercase;" rows="4"></textarea>
						<small>Quedan <span id="sec_con_serv_pub_caracteres_comentario">255</span> caracteres</small>
					</div>

					
					<!--<div class="col-xs-6" style="margin-top: 20px;">
						<button type="submit" class="btn btn-block btn-success" id="btnGuardarLocalesServicioPublico">
							<i class="glyphicon glyphicon-saved" ></i>
							Agregar recibo
						</button>	
					</div>-->
				</form>
			</div>
			<br>
			<br>
			<br>
			<br>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" class="btn btn-success" data-dismiss="modal">
					<i class="icon fa fa-close"></i>
					Cancelar
				</button>
				<button type="button" class="btn btn-success" id="sec_nuevo_modal_guardar_recibo">
					<i class="icon fa fa-save"></i>
					<span>Guardar</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL AGREGAR RECIBO -->
