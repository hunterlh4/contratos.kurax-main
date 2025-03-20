<?php
global $mysqli;
$menu_id = "";
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'contrato' AND sub_sec_id = 'solicitud' LIMIT 1");
while ($r = $result->fetch_assoc())
	$menu_id = $r["id"];

if (!array_key_exists($menu_id, $usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])) {
	echo "No tienes permisos para acceder a este recurso";
} else {

$usuario_id = $login?$login['id']:null;

$list_emp_cont = $mysqli->query("SELECT tpg.valor FROM tbl_parametros_generales tpg WHERE tpg.codigo = 'empresa_contacto_de_contratos' AND tpg.estado = 1 LIMIT 1");
$row_emp_cont = $list_emp_cont->fetch_assoc();
$valor_empresa_contacto = isset($row_emp_cont['valor']) && $row_emp_cont['valor'] == 'Kurax' ? '(Kurax)':'(AT)';
?>

<style>
	.select-editable {position:relative; background-color:white; border:solid grey 1px;  width:120px; height:18px;}
.select-editable select {position:absolute; top:0px; left:0px; font-size:14px; border:none; width:120px; margin:0;}
.select-editable input {position:absolute; top:0px; left:0px; width:100px; padding:1px; font-size:12px; border:none;}
.select-editable select:focus, .select-editable input:focus {outline:none;}
	.campo_obligatorio{
		font-size: 15px;
		color: red;
	}

	.campo_obligatorio_v2{
		font-size: 13px;
		color: red;
	}

	.sec_contrato_nuevo_datepicker {
    	min-height: 28px !important;
	}
</style>


<div id="div_sec_contrato_nuevo">

	<div id="loader_"></div>

	<div class="row">
		<div class="col-xs-12 col-sm-12 col-md-12">
			<div class="alert alert-success alert-dismissible fade in" role="alert" style="margin: 0px;">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
				<p class="text-center">
					<?php 
					$ultima_modificacion = "";
					$query_updated = $mysqli->query("SELECT updated_at FROM tbl_modificaciones WHERE status = 1 AND modulo = 'Contratos' ORDER BY updated_at DESC LIMIT 1");
					while($sel2 = $query_updated->fetch_assoc())
					{
						$ultima_modificacion = $sel2['updated_at'];
					}
					if (!Empty($ultima_modificacion)) {
						$ultima_modificacion = !Empty($ultima_modificacion) ? date("d/m/Y H:i A", strtotime($ultima_modificacion)):'';
					}
	
					?>
					El sistema ha sido actualizado el <b> <?=$ultima_modificacion ?> </b> En caso de identificar un mal funcionamiento presionar:
					<b>Ctrl+F5</b> (Si estás en PC) o <b>Ctrl+Tecla Función +F5</b> (Si estás en laptop) o contactar con el área de sistemas.
				</p>
			</div>
		</div>
		<div class="col-xs-12 col-sm-12 col-md-12 text-center">
			<h1 class="page-title titulosec_contrato"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Contratos - Nueva Solicitud</h1>
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
					<form>
						<input type="hidden" id="usuario_id_temporal" value="<?php echo $usuario_id;?>">

						<div class="col-xs-12 col-md-4 col-lg-4 item_filter">
							<div class="form-group">
								<label for="exampleInputEmail1">Tipo de solicitud</label>
								<select
									class="form-control input_text select2"
									data-live-search="true" 
									data-col="area_id" 
									data-table="tbl_areas"
									name="tipo_contrato_id" 
									id="tipo_contrato_id" 
									title="Seleccione el tipo de contrato">
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4 item_filter" id="div_empresa_suscribe" style="display: none;">
							<div class="form-group">
								<label for="exampleInputEmail1">Empresa que suscribe el contrato</label>
								<select
									class="form-control input_text select2"
									data-live-search="true" 
									data-col="area_id" 
									data-table="tbl_areas"
									name="empresa_suscribe_id" 
									id="empresa_suscribe_id" 
									title="Seleccione la empresa que suscribe el contrato">
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4 item_filter" style="display: none;">
							<div class="form-group">
								<label for="exampleInputEmail1">Área responsable</label>
								<select
									class="form-control input_text select2"
									data-live-search="true" 
									data-col="area_id" 
									data-table="tbl_areas"
									name="area_responsable_id" 
									id="area_responsable_id" 
									title="Seleccione el tipo de Contrato">
									<option value="0">- Seleccione -</option>
									<?php
									$sel_query = $mysqli->query("SELECT id, nombre
									FROM tbl_areas
									WHERE estado = 1
									ORDER BY nombre ASC;");
									while($sel=$sel_query->fetch_assoc()){
										if ($sel["id"] == 21) {
											$selected_area = 'selected';
										} else {
											$selected_area = '';
										}?>
									<option value="<?php echo $sel["id"];?>" <?php echo $selected_area; ?>><?php echo $sel["nombre"];?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>
						<div class="col-xs-12 col-md-4 col-lg-4 item_filter" id="div_area_id" style="display: none;">
							<div class="form-group">
								<label for="exampleInputEmail1">Área</label>
								<select
									class="form-control input_text select2"
									data-live-search="true" 
									data-col="area_id" 
									data-table="tbl_areas"
									name="area_id" 
									id="area_id" 
									title="Seleccione el tipo de Contrato">
									<option value="0">- Seleccione -</option>
									<?php
									$sel_query = $mysqli->query(" SELECT  *from tbl_areas ta where ta.estado = 1
									");
									while($sel=$sel_query->fetch_assoc()){
										?>
									<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>
						<div class="col-xs-12 col-md-4 col-lg-4 item_filter" id="div_personal_responsable" style="display: none;">
							<div class="form-group">
								<label for="exampleInputEmail1"> Supervisor <span class="campo_obligatorio_v2">(*)</span></label>
								<select
									class="form-control input_text select2"
									data-live-search="true" 
									data-col="personal_id" 
									data-table="tbl_personal"
									name="personal_responsable_id" 
									id="personal_responsable_id" 
									title="Seleccione el tipo de Contrato">
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4 item_filter" id="div_correos_adicionales" style="display: none;">
							<div class="form-group">
									<div class="control-label">Correos Adjuntos: (Opcional)</div>
									<textarea cols="50" rows="2"					 
									name="correos_adjuntos_ad"
									id="correos_adjuntos_ad"></textarea>
									<p><b>Nota: Para más de un correo se debe separar por comas (,)</b></p>
								</div>
								 
						</div>


						<div class="col-xs-12 col-md-4 col-lg-4 item_filter" id="div_nombre_tienda" style="display: none;">
							<div class="form-group">
								<label for="exampleInputEmail1">Nombre de la tienda</label>
								<select
									class="form-control input_text select2"
									data-live-search="true" 
									data-col="personal_id" 
									data-table="tbl_personal"
									name="contrato_id_segun_nombre_tienda" 
									id="contrato_id_segun_nombre_tienda" 
									title="Seleccione la nombre de la tienda">
									<option value="0">- Seleccione -</option>
									<?php
									$sel_query = $mysqli->query("SELECT contrato_id, nombre_tienda
									FROM cont_contrato
									WHERE tipo_contrato_id = 1
									AND status = 1
									AND etapa_id = 5
									ORDER BY nombre_tienda ASC");
									while($sel=$sel_query->fetch_assoc()){?>
									<option value="<?php echo $sel["contrato_id"];?>"><?php echo $sel["nombre_tienda"];?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4 item_filter" id="div_nombre_proveedor" style="display: none;">
							<div class="form-group">
								<label for="exampleInputEmail1">Nombre del proveedor</label>
								<select
									class="form-control input_text select2"
									data-live-search="true" 
									data-col="personal_id" 
									data-table="tbl_personal"
									name="contrato_num_ruc_proveedor" 
									id="contrato_num_ruc_proveedor" 
									title="Seleccione el nombre del proveedor">
									<option value="0">- Seleccione -</option>
									<?php
									$sel_query = $mysqli->query("SELECT 
										c.ruc, c.razon_social
									FROM
										cont_contrato c
									WHERE
										tipo_contrato_id = 2
										AND c.user_created_id = $usuario_id
										AND c.etapa_id = 5
									GROUP BY c.ruc , c.razon_social
									ORDER BY c.razon_social
									");
									while($sel=$sel_query->fetch_assoc()){?>
									<option value="<?php echo $sel["ruc"];?>"><?php echo $sel["razon_social"];?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_fecha_contrato_proveedor" style="display: none;">
							<div class="form-group">
								<label for="exampleInputEmail1">Seleccione el contrato</label>
								<select
									class="form-control input_text select2"
									data-live-search="true" 
									data-col="personal_id" 
									data-table="tbl_personal"
									name="contrato_id_contrato_proveedor" 
									id="contrato_id_contrato_proveedor" 
									title="Seleccione la fecha del contrato">
								</select>
							</div>
						</div>
					</form>
				</div>
				<br>
			 
			</div>
			<!-- /PANEL: Tipo contrato -->


			<!-- PANEL: Contrato Arrendamiento -->
			<div class="panel" id="divContratoArrendamiento" style="display: none;">
				<div class="panel-heading">
					<div class="panel-title">FICHA DE CONDICIONES DE CONTRATO DE ARRENDATARIO</div>
				</div>
				<div class="panel-body">
					<form id="form_contrato_arrendatario" name="form_contrato_arrendatario" method="POST" enctype="multipart/form-data" autocomplete="off">
						<div class="col-xs-12 col-md-6 col-lg-6">
							<div class="h4"><b>DATOS DEL PROPIETARIO</b></div>
						</div>

						<!--
						<div class="col-xs-12 col-md-6 col-lg-6">
							<div class="form-group pull-right">
								<button type="button" class="btn btn-success" id="btnModalBuscarPropietario" style="width: 180px;">
									<i class="icon fa fa-plus"></i>
									<span id="demo-button-text">Agregar propietario</span>
								</button>
							</div>
						</div> 
						-->
						
						<div class="col-xs-12 col-md-12 col-lg-12">
							<div id="divTablaPropietarios" class="form-group">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>

						<div class="col-xs-12 col-md-6 col-lg-12">
							<div class="h4"><b>DATOS DEL INMUEBLE</b></div>
						</div>

						<!--
						<div class="col-xs-12 col-md-6 col-lg-6">
							<div class="form-group pull-right">
								<button type="button" class="btn btn-success" id="btnModalAgregarInmueble" style="width: 180px;">
									<i class="icon fa fa-plus"></i>
									<span id="demo-button-text">Agregar inmueble</span>
								</button>
							</div>
						</div>
						

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div id="divTablaInmuebles" class="form-group">
							</div>
						</div>
						-->

						<div class="col-xs-12 col-md-12 col-lg-3">
							<div class="form-group">
								<div class="control-label">Departamento:</div>
								<select
									class="form-control input_text select2"
									data-live-search="true" 
									data-col="area_id" 
									data-table="tbl_areas"
									name="modal_inmueble_id_departamento" 
									id="modal_inmueble_id_departamento" 
									title="Seleccione el departamento">
								</select>
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-3">
							<div class="form-group">
								<div class="control-label">Provincia:</div>
								<select class="form-control input_text select2"
									data-live-search="true" 
									data-col="personal_id" 
									data-table="tbl_personal"
									name="modal_inmueble_id_provincia" 
									id="modal_inmueble_id_provincia" 
									title="Seleccione el tipo de Contrato">
								</select>
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-3">
							<div class="form-group">
								<div class="control-label">Distrito:</div>
								<select class="form-control input_text select2"
									data-live-search="true" 
									data-col="personal_id" 
									data-table="tbl_personal"
									name="modal_inmueble_id_distrito" 
									id="modal_inmueble_id_distrito" 
									title="Seleccione el tipo de Contrato">
								</select>
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-9">
							<div class="form-group">
								<div class="control-label">Ubicación del Inmueble:</div>
								<input type="text" id="modal_inmueble_ubicacion" name="modal_inmueble_ubicacion" class="form-control" maxlength="100" style="height: 30px;">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-3">
							<div class="form-group">
								<div class="control-label">Área arrendada (m2):</div>
								<input type="text" id="modal_inmueble_area_arrendada" name="modal_inmueble_area_arrendada" class="filtro txt_filter_style" style="width: 100%; height: 30px;">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-3">
							<div class="form-group">
								<div class="control-label">N°. Partida Registral:</div>
								<input type="text" id="modal_inmueble_num_partida_registral" name="modal_inmueble_num_partida_registral" class="filtro txt_filter_style" maxlength="11" style="width: 100%; height: 30px;">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-3">
							<div class="form-group">
								<div class="control-label">Oficina Registral (Sede):</div>
								<input type="text" id="modal_inmueble_oficina_registral" name="modal_inmueble_oficina_registral" class="filtro txt_filter_style" maxlength="50" style="width: 100%; height: 30px;">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-3">
							<div class="form-group">
								<div class="control-label">Latitud:</div>
								<input type="text" id="modal_inmueble_latitud" name="modal_inmueble_latitud" class="txt_filter_style" maxlength="250" style="width: 100%; height: 30px;">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-3">
							<div class="form-group">
								<div class="control-label">Longitud:</div>
								<input type="text" id="modal_inmueble_longitud" name="modal_inmueble_longitud" class="txt_filter_style" maxlength="250" style="width: 100%; height: 30px;">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="h4"><b>DATOS DEL INMUEBLE - SERVICIO DE AGUA</b></div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-3">
							<div class="form-group">
								<div class="control-label">N° de Suministro:</div>
								<input type="text" id="modal_inmueble_num_suministro_agua" class="filtro txt_filter_style num_suministro" style="width: 100%; height: 30px;">
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-3">
							<div class="form-group">
								<div class="control-label">Compromiso de pago:</div>
								<select class="form-control input_text select2"
									data-live-search="true" 
									data-col="personal_id" 
									data-table="tbl_personal"
									name="modal_inmueble_tipo_compromiso_pago_agua" 
									id="modal_inmueble_tipo_compromiso_pago_agua" 
									title="Seleccione el tipo de Contrato">
									<option value="0">- Seleccione -</option>
									<?php
									$sel_query = $mysqli->query("SELECT id, nombre
									FROM cont_tipo_pago_servicio
									WHERE estado = 1
									ORDER BY nombre ASC;");
									while($sel=$sel_query->fetch_assoc()){?>
									<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-3" id="div_modal_inmueble_monto_o_porcentaje_recibo_agua" style="display: none">
							<div class="form-group">
								<div class="control-label" id="div_inmueble_label_monto_o_porcentaje_agua">Monto o porcentaje del recibo:</div>
								<input type="text" id="modal_inmueble_monto_o_porcentaje_agua" class="filtro txt_filter_style" style="width: 100%; height: 30px;">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="h4"><b>DATOS DEL INMUEBLE - SERVICIO DE LUZ</b></div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-3">
							<div class="form-group">
								<div class="control-label">N° de Suministro:</div>
								<input type="text" id="modal_inmueble_num_suministro_luz" class="filtro txt_filter_style num_suministro" style="width: 100%; height: 30px;">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-3">
							<div class="form-group">
								<div class="control-label">Compromiso de pago:</div>
								<select class="form-control input_text select2"
									data-live-search="true" 
									data-col="personal_id" 
									data-table="tbl_personal"
									name="modal_inmueble_tipo_compromiso_pago_luz" 
									id="modal_inmueble_tipo_compromiso_pago_luz" 
									title="Seleccione el tipo de Contrato">
									<option value="0">- Seleccione -</option>
									<?php
									$sel_query = $mysqli->query("SELECT id, nombre
									FROM cont_tipo_pago_servicio
									WHERE estado = 1
									ORDER BY nombre ASC;");
									while($sel=$sel_query->fetch_assoc()){?>
									<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-3" id="div_modal_inmueble_monto_o_porcentaje_recibo_luz" style="display: none">
							<div class="form-group">
								<div class="control-label" id="div_inmueble_label_monto_o_porcentaje_luz">Monto o porcentaje del recibo:</div>
								<input type="text" id="modal_inmueble_monto_o_porcentaje_luz" class="filtro txt_filter_style" style="width: 100%; height: 30px;">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="h4"><b>DATOS DEL INMUEBLE - ARBITRIOS MUNICIPALES</b></div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-3">
							<div class="form-group">
								<div class="control-label">Compromiso de Pago:</div>
								<select class="form-control input_text select2"
									data-live-search="true" 
									data-col="personal_id" 
									data-table="tbl_personal"
									name="modal_inmueble_tipo_compromiso_pago_arbitrios" 
									id="modal_inmueble_tipo_compromiso_pago_arbitrios" 
									title="Seleccione el tipo de Contrato">
									<option value="0">- Seleccione -</option>
									<?php
									$sel_query = $mysqli->query("SELECT id, nombre
									FROM cont_tipo_pago_arbitrios
									WHERE estado = 1
									ORDER BY nombre ASC;");
									while($sel=$sel_query->fetch_assoc()){?>
									<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-3" id="div_modal_inmueble_porcentaje_pago_arbitrios" style="display: none">
							<div class="form-group">
								<div class="control-label">Porcentaje del Pago (%):</div>
								<input type="text" id="modal_inmueble_porcentaje_pago_arbitrios" class="filtro txt_filter_style formato_porcentaje" style="width: 100%; height: 30px;">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="h4"><b>CONDICIONES ECONÓMICAS Y COMERCIALES</b></div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Moneda del contrato:</div>
								<select
									class="form-control input_text select2"
									data-live-search="true" 
									data-col="personal_id" 
									data-table="tbl_personal"
									name="contrato_tipo_moneda_renta_pactada" 
									id="contrato_tipo_moneda_renta_pactada" 
									title="Seleccione el tipo de Contrato">
									<option value="0">- Seleccione -</option>
									<?php
									$sel_query = $mysqli->query("SELECT id, CONCAT(nombre,' (',simbolo,')') AS nombre
									FROM tbl_moneda
									WHERE estado = 1 AND id IN(1,2)
									ORDER BY id ASC;");
									while($sel=$sel_query->fetch_assoc()){?>
									<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Pago de renta:</div>
								<select
									class="form-control input_text select2"
									name="tipo_pago_de_renta_id" 
									id="tipo_pago_de_renta_id" 
									title="Seleccione el pago de renta">
									<?php
									$sel_query = $mysqli->query("SELECT id, nombre
									FROM cont_tipo_pago_renta
									WHERE status = 1
									ORDER BY id ASC");
									while($sel=$sel_query->fetch_assoc()){?>
									<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Monto de Renta Pactada (cuota fija):</div>
								<input type="text" id="contrato_monto_renta" class="filtro" style="width: 100%; height: 30px; float: left; text-align: right;">
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3" id="div_porcentaje_venta" style="display: none;">
							<div class="form-group">
								<div class="control-label">Porcentaje de venta (cuota variable):</div>
								<input type="text" id="porcentaje_venta" class="filtro formato_moneda_clase" style="width: 100%; height: 30px; float: left; text-align: right;">
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3" id="div_tipo_venta" style="display: none;">
							<div class="form-group">
								<div class="control-label">Tipo de venta:</div>
								<select
									class="form-control input_text select2"
									name="tipo_venta_id" 
									id="tipo_venta_id" 
									title="Seleccione el tipo de renta">
									<option value="0">- Seleccione -</option>
									<?php
									$sel_query = $mysqli->query("SELECT id, nombre
									FROM cont_tipo_venta
									WHERE status = 1
									ORDER BY nombre ASC");
									while($sel=$sel_query->fetch_assoc()){?>
									<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">IGV en la renta:</div>
								<select
									class="form-control input_text select2"
									name="tipo_igv_renta_id" 
									id="tipo_igv_renta_id" 
									title="Seleccione el IGV en la renta">
									<option value="0">- Seleccione -</option>
									<?php
									$sel_query = $mysqli->query("SELECT id, nombre
									FROM cont_tipo_afectacion_igv
									WHERE status = 1
									ORDER BY nombre ASC");
									while($sel=$sel_query->fetch_assoc()){?>
									<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>
						
						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Monto de la garantía:</div>
								<input type="text" id="contrato_monto_garantia" class="filtro" style="width: 100%; height: 30px; float: left; text-align: right;">
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Adelantos:</div>
								<select
									class="form-control input_text select2"
									data-live-search="true" 
									data-col="personal_id_primeros" 
									data-table="tbl_personal_primeros"
									name="contrato_adelanto" 
									id="contrato_adelanto" 
									title="Seleccione los adelantos">
									<option value="0">- Seleccione -</option>
									<?php
									$sel_query = $mysqli->query("SELECT id, nombre
									FROM cont_tipo_adelanto
									WHERE status = 1
									ORDER BY id ASC;");
									while($sel=$sel_query->fetch_assoc()){?>
									<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="col-xs-12 col-md-6 col-lg-4" style="padding-left: 0px;">
								<div id="div_tabla_adelantos" class="form-group">
								</div>
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="h4"><b>IMPUESTO A LA RENTA</b></div>
						</div>

						<div class="col-xs-12 col-md-6 col-lg-6">
							<div class="form-group">
								<div class="control-label">Impuesto a la renta / Detracción:</div>
								<select
									class="form-control input_text select2"
									data-live-search="true" 
									data-col="personal_id" 
									data-table="tbl_personal"
									name="contrato_impuesto_a_la_renta" 
									id="contrato_impuesto_a_la_renta" 
									title="Seleccione el tipo de impuesto a la renta">
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-6 col-lg-6" id="div_carta_de_instruccion_por_descuento">
							<div class="form-group">
								<div class="control-label">¿AT deposita impuesto a la renta a SUNAT? Carta de Instrucción.</div>
								<select
									class="form-control input_text select2"
									name="contrato_impuesto_a_la_renta_carta_de_instruccion_id" 
									id="contrato_impuesto_a_la_renta_carta_de_instruccion_id" 
									title="Seleccione Si o No">
									<option value="0">- Seleccione -</option>
									<option value="1">Si</option>
									<option value="2">No</option>
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3" id="div_numero_cuenta_detraccion" style="display: none;">
							<div class="form-group">
								<div class="control-label">N°. de cuenta de detracción (10%):</div>
								<input type="text" id="contrato_numero_cuenta_detraccion" class="filtro" style="width: 100%; height: 30px; float: left;" placeholder="N°. de cuenta (Banco de la Nación)">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12" id="div_detalle_del_pago">
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="h4"><b>PERÍODO DE GRACIA</b></div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Período de Gracia:</div>
								<select class="form-control select2" id="contrato_periodo_gracia_id" style="width: 100%; height: 30px; float: left;">
									<option value="0">- Seleccione -</option>
									<?php
									$sel_query = $mysqli->query("SELECT id, nombre
									FROM cont_tipo_periodo_de_gracia
									WHERE status = 1
									ORDER BY id ASC;");
									while($sel=$sel_query->fetch_assoc()){?>
									<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-3" id="div_periodo_gracia_numero" style="display: none;">
							<div class="form-group">
								<div class="control-label">Número de días:</div>
								<input type="text" id="contrato_periodo_gracia_numero" name="contrato_periodo_gracia_numero" class="filtro txt_filter_style num_periodo_gracia" style="width: 100%; height: 30px;">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="h4"><b>VIGENCIA</b></div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Periodo<span class="campo_obligatorio_v2">(*)</span>:</div>
								<div class="col-xs-12 col-md-12 col-lg-12" style="padding: 0px;">
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
												
												<option value="<?php echo $li['id']?>"><?php echo $li['nombre']?></option>
										
										<?php }?>
									</select>
								</div>
							</div>
						</div>

						<div class="col-xs-12 col-md-2 col-lg-2 div_vig_def">
							<div class="form-group">
								<div class="control-label">Vigencia del Contrato en meses:</div>
								<input type="text" id="contrato_vigencia_del_contrato_en_meses" class="filtro vigencia_meses" style="width: 100%; height: 30px;">
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3 div_vig_def">
							<div class="form-group">
								<div class="control-label">Vigencia del Contrato (Solo lectura):</div>
								<input type="text" id="contrato_vigencia_en_anios" class="filtro" style="width: 100%; height: 30px;background-color: #f7f7f7; text-align: center;" readonly>
							</div>
						</div>

						<div class="col-xs-12 col-md-2 col-lg-2">
							<div class="form-group">
								<div class="control-label">Fecha de Inicio:</div>
								<div class="input-group">
									<input
										type="text"
										class="form-control sec_contrato_nuevo_datepicker"
										id="input_text_contrato_inicio_fecha"
										value=""
										readonly="readonly"
										>
									<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="input_text_contrato_inicio_fecha"></label>
								</div>
							</div>
						</div>

						<div class="col-xs-12 col-md-2 col-lg-2 div_vig_def">
							<div class="form-group">
								<div class="control-label">Fecha de Fin:</div>
								<div class="input-group">
									<input
										type="text"
										class="form-control sec_contrato_nuevo_datepicker"
										id="input_text_contrato_fin_fecha"
										value=""
										readonly="readonly"
										>
									<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="input_text_contrato_fin_fecha"></label>
								</div>
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="h4"><b>INCREMENTOS</b></div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Incrementos:</div>
								<select class="form-control select2" id="contrato_tipo_incremento_id" style="width: 100%; height: 30px; float: left;">*
									<option value="0">- Seleccione -</option>
									<?php
									$sel_query = $mysqli->query("SELECT id, nombre
									FROM cont_tipo_incremento
									WHERE status = 1
									ORDER BY id ASC;");
									while($sel=$sel_query->fetch_assoc()){?>
									<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="col-xs-12 col-md-6 col-lg-6" style="padding-left: 0px;">
								<div id="divTablaIncrementos" class="form-group">
								</div>
							</div>
						</div>


						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="h4"><b>INFLACIÓN</b></div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Inflación:</div>
								<select class="form-control select2" id="contrato_tipo_inflacion_id" style="width: 100%; height: 30px; float: left;">*
									<option value="0">- Seleccione -</option>
									<?php
									$sel_query = $mysqli->query("SELECT id, nombre
									FROM cont_tipo_inflacion
									WHERE status = 1
									ORDER BY id ASC;");
									while($sel=$sel_query->fetch_assoc()){?>
									<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="col-xs-12 col-md-12 col-lg-12 table-responsive" style="padding-left: 0px;">
								<div id="divTablaInflacion" class="form-group">
								</div>
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="h4"><b>CUOTAS EXTRAORDINARIAS</b></div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Cuota Extraordinaria:</div>
								<select class="form-control select2" id="contrato_tipo_cuota_extraordinaria_id" style="width: 100%; height: 30px; float: left;">*
									<option value="0">- Seleccione -</option>
									<?php
									$sel_query = $mysqli->query("SELECT id, nombre
									FROM cont_tipo_cuota_extraordinaria
									WHERE status = 1
									ORDER BY id ASC;");
									while($sel=$sel_query->fetch_assoc()){?>
									<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
									<?php
									}
									?>
								</select>
							</div>
						</div>


						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="col-xs-12 col-md-12 col-lg-12 table-responsive" style="padding-left: 0px;">
								<div id="divTablaCuotaExtraordinaria" class="form-group">
								</div>
							</div>
						</div>
						
						
						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>

						<div class="col-xs-12 col-md-6 col-lg-6">
							<div class="h4"><b>BENEFICIARIO</b></div>
						</div>

						<!--

						<div class="col-xs-12 col-md-6 col-lg-6">
							<div class="form-group pull-right">
								<button type="button" class="btn btn-success" id="btnModalAgregarBeneficiario" style="width: 180px;">
									<i class="icon fa fa-plus"></i>
									<span id="demo-button-text">Agregar beneficiario</span>
								</button>
							</div>
						</div>

						-->

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div id="divTablaBeneficiarios" class="form-group">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="h4"><b>FECHA DE SUSCRIPCIÓN DEL CONTRATO</b></div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="input-group">
									<input
										type="text"
										class="form-control sec_contrato_nuevo_datepicker"
										id="input_text_contrato_fecha_suscripcion"
										value=""
										readonly="readonly"
										style="height: 30px;"
										>
									<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="input_text_contrato_fecha_suscripcion"></label>
								</div>
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="h4"><b>OBSERVACIONES</b></div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="form-group">
								<textarea id="contrato_observaciones" name="contrato_observaciones"  rows="3" cols="50" style="width: 100%;"></textarea>
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="h4"><b>ANEXOS</b></div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Partida Registral del inmueble actualizada:</div>
								<input type="file" name="archivo_partida_registral" required accept=".jpg, .jpeg, .png, .pdf">
							</div>
						</div>
						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Recibo de agua:</div>
								<input type="file" name="archivo_recibo_agua" required accept=".jpg, .jpeg, .png, .pdf">
							</div>
						</div>
						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Recibo de luz:</div>
								<input type="file" name="archivo_recibo_luz" required accept=".jpg, .jpeg, .png, .pdf">
							</div>
						</div>
						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">DNI del propietario:</div>
								<input type="file" name="archivo_dni_propietario" required accept=".jpg, .jpeg, .png, .pdf">
							</div>
						</div>
						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Pago de Recibo de agua:</div>
								<input type="file" name="archivo_pago_recibo_agua" required accept=".jpg, .jpeg, .png, .pdf">
							</div>
						</div>
						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Pago de Recibo de Luz:</div>
								<input type="file" name="archivo_pago_recibo_luz" required accept=".jpg, .jpeg, .png, .pdf">
							</div>
						</div>
						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Vigencia de poder (Si es empresa):</div>
								<input type="file" name="archivo_vigencia_poder" required accept=".jpg, .jpeg, .png, .pdf">
							</div>
						</div>
						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">DNI de representante legal (Si es empresa):</div>
								<input type="file" name="archivo_dni_representante_legal" required accept=".jpg, .jpeg, .png, .pdf">
							</div>
						</div>
						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">HR del inmueble:</div>
								<input type="file" name="archivo_hr_inmueble" required accept=".jpg, .jpeg, .png, .pdf">
							</div>
						</div>
						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">PU del inmueble:</div>
								<input type="file" name="archivo_pu_inmueble" required accept=".jpg, .jpeg, .png, .pdf">
							</div>
						</div>                 

						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Pago de Impuesto Predial:</div>
								<input type="file" name="archivo_pago_impuesto_predial" required accept=".jpg, .jpeg, .png, .pdf">
							</div>
						</div>    
						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Pago de Arbitrios:</div>
								<input type="file" name="archivo_pago_arbitrios" required accept=".jpg, .jpeg, .png, .pdf">
							</div>
						</div>    
						<div class="col-xs-12 col-md-12 col-lg-12">

							<br>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="h4"><b>OTROS ANEXOS</b></div>
						</div>
						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<!--sec_nuevo_abrir_modal_nuevos_anexos-->
								<button type="button" class="btn btn-info btn-sm" id="sec_nuevo_btn_agregar_anexos" onclick="sec_con_nuevo_abrir_modal_tipos_anexos()">
									<i class="icon fa fa-plus"></i>
									Agregar otros anexos
								</button>
								<!--prueba
								<div hidden class="col-md-9 col-sm-8 col-xs-12">
									<input id="fileToUploadAnexo[]" name="fileToUploadAnexo[]" accept="application/pdf, image/png, image/jpeg" type="file" multiple="" style="display:none">
									<button type="button" class="btn btn-outline-secondary" name="btnAgregarAnexo" id="btnAgregarAnexo"><i class="fas fa-plus-square"></i> Agregar más archivos</button>
								</div>
								<div hidden class="form-group row" id="divListaAnexos" style="margin: 0px 15px;">      
      								<label class="control-label col-md-3 col-sm-4 col-xs-12" style="line-height:40px; font-size:12px;"><b>ANEXOS CARGADOS:</b></label>
									<div id="divListaAnexosCargados" class="col-md-9 col-sm-8 col-xs-12"></div>
    							</div>
								prueba-->
								
							</div>
						</div> 
						<!--PRUEBA 2-->
						<!--<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="h4"><b>Cargar Múltiple Archivos</b></div>
						</div>-->
						<div class="col-xs-12 col-md-4 col-lg-4">
				            <div id="archivos" style="width:300px">
				                
				            </div>
						</div>	
						<div class="col-md-12" id="sec_nuevos_files_prueba">
							<!--<input id="fileToUploadAnexo" accept="application/pdf, image/png, image/jpeg" type="file" multiple="" style="display:none">-->
						</div> 
						<!--<div class="col-md-12">
							<input type="button" value="+ Subir más ficheros" onclick="sec_con_nuevo_abrir_modal_tipos_anexos()">
						</div>--> 
						 
						<!-- FIN PRUEBA 2-->	
						
						<div class="col-md-12" id="sec_nuevo_nuevos_anexos_listado">
							<input id="fileToUploadAnexo" accept="application/pdf, image/png, image/jpeg" type="file" multiple="" style="display:none">
						</div> 
						<br/>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<button type="button" class="btn btn-success btn-block" id="boton_guardar_contratos">
								<i class="icon fa fa-save" id="demo-button-icon-left" style="display: inline-block;"></i>
								<span id="demo-button-text">Enviar Solicitud de Arrendamiento</span>
							</button>
						</div>
					</form>
				</div>
			</div>
			<!-- /PANEL: Contrato Arrendamiento -->


			<!-- PANEL: Contrato Proveedor -->
			<div class="panel" id="divContratoProveedor" style="display: none;">
				<div class="panel-heading">
					<div class="panel-title" id="div_panel_title">FICHA DE CONDICIONES DE CONTRATO CON PROVEEDORES  <span class="campo_obligatorio_v2" style="text-transform: none;">(*) Campos obligatorios</span></div>
				</div>
				<div class="panel-body">
					<form id="form_contrato_proveedor" name="form_contrato_proveedor" method="POST" enctype="multipart/form-data" autocomplete="off">
						<div class="row">
							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="h4"><b>DATOS DE CONTACTO</b></div>
							</div>

							<div class="col-xs-12 col-md-3 col-lg-3">
								<div class="form-group">
									<div class="control-label">Persona contacto <?=$valor_empresa_contacto?> <span class="campo_obligatorio_v2">(*)</span>:</div>
									<input type="text" name="persona_contacto_proveedor" id="persona_contacto_proveedor" maxlength=250 class="filtro formato_texto"
									 style="width: 100%; height: 28px;">
								</div>
							</div>

							<div class="col-xs-12 col-md-3 col-lg-3">
								<div class="form-group">
									<div class="control-label">Cargo Persona contacto <?=$valor_empresa_contacto?> <span class="campo_obligatorio_v2">(*)</span>:</div>
									<select 
											class="form-control input_text select2"
											name="cargo_id_persona_contacto" 
											id="cargo_id_persona_contacto" 
											title="Seleccione a el director">
									</select>
								</div>
							</div>

						

							<div class="col-xs-12 col-md-3 col-lg-3">
								<div class="form-group">

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
											onchange="sec_contrato_nuevo_select_cargo('aprobador')"
											name="director_aprobacion_id" 
											id="director_aprobacion_id" 
											title="Seleccione a el director">
										</select>
									</div>

								</div>
							</div>

							<div class="col-xs-12 col-md-3 col-lg-3">
								<div class="form-group">
									<div class="control-label">Cargo del Aprobador:</div>
									<select 
											class="form-control input_text select2"
											name="cargo_id_aprobante" 
											id="cargo_id_aprobante" 
											title="Seleccione a el director">
									</select>
								</div>
							</div>

						

							<div class="col-xs-12 col-md-3 col-lg-3">
								<div class="form-group">

									<div class="control-label">
										Responsable de Área 
										<span class="campo_obligatorio_v2">(*)</span>:
									</div>

									<select 
										class="form-control input_text select2"
										name="gerente_area_id" 
										id="gerente_area_id" 
										onchange="sec_contrato_nuevo_select_cargo('responsable')"
										title="Seleccione el gerente">
									</select>

									<span id="helpBlock2" class="help-block">
										<i class="fa fa-arrow-up"></i> 
										<b> Nota: Si no encuentra al Gerente, seleccione la opción "Otro".</b>
									</span>

								</div>
							</div>

							<div class="col-xs-12 col-md-3 col-lg-3" id="div_gerencia_area_nombre_gerente" style="display: none;">
								<div class="form-group">
									<div class="control-label">Nombre del Responsable de Área <span class="campo_obligatorio_v2">(*)</span>:</div>
									<input type="text" name="nombre_del_gerente_del_area" id="nombre_del_gerente_del_area" maxlength=250 class="filtro form-control" >
								</div>
							</div>

							<div class="col-xs-12 col-md-3 col-lg-3" id="div_gerencia_area_email_gerente" style="display: none;">
								<div class="form-group">
									<div class="control-label">Email del Responsable de Área <span class="campo_obligatorio_v2">(*)</span>:</div>
									<input type="text" name="email_del_gerente_del_area" id="email_del_gerente_del_area" maxlength=250 class="filtro form-control" >
								</div>
							</div>

							<div class="col-xs-12 col-md-3 col-lg-3">
								<div class="form-group">
									<div class="control-label">Cargo del Responsable <span class="campo_obligatorio_v2">(*)</span>:</div>
									<select 
											class="form-control input_text select2"
											name="cargo_id_responsable" 
											id="cargo_id_responsable" 
											title="Seleccione a el director">
									</select>
								</div>
							</div>

							<div class="col-xs-12 col-md-4 col-lg-3">
								<div class="form-group">
									<div class="control-label">Correos Adjuntos: (Opcional)</div>
									<textarea style="width: 100%;" rows="2" name="correos_adjuntos"></textarea>
									<span id="helpBlock2" class="help-block">
										<i class="fa fa-arrow-up"></i> 
										<b>Nota: Para más de un correo se debe separar por comas (,)</b>
									</span>
								</div>
							</div>

							<div class="col-xs-12 col-md-12 col-lg-12">
								<br>
							</div>

							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="h4"><b>INFORMACIÓN DEL PROVEEDOR</b></div>
							</div>
						
							<!--RUC PROVEEDOR-->
							<div class="col-xs-12 col-md-4 col-lg-4">
								<div class="form-group">
									<div class="control-label">N° de RUC del Proveedor <span class="campo_obligatorio_v2">(*)</span>:</div>
									<input type="text"
									id="ruc"
									name="ruc" 
									oninput="this.value=this.value.replace(/[^0-9]/g,'');"
									maxlength=11 
									class="filtro" 
									style="width: 100%; height: 30px;">
								</div>
							</div>
							<!--RAZON SOCIAL PROVEEDOR-->
							<div class="col-xs-12 col-md-4 col-lg-4">
								<div class="form-group">
									<div class="control-label">Razón Social del Proveedor <span class="campo_obligatorio_v2">(*)</span>:</div>
									<input type="text" name="razon_social" id="razon_social" maxlength=500 class="filtro" 
									style="width: 100%; height: 30px;">
								</div>
							</div>

							<div class="col-xs-12 col-md-4 col-lg-4">
								<div class="form-group">
									<div class="control-label">Nombre Comercial del Proveedor <span class="campo_obligatorio_v2">(*)</span>:</div>
									<input type="text" name="nombre_comercial" id="nombre_comercial" maxlength=500 class="filtro" 
									style="width: 100%; height: 30px;">
								</div>
							</div>
							
						</div>

						<div class="row">
							<div class="col-xs-12 col-md-12 col-lg-12" style="margin-top: 10px;">
								<div class="h4">
									<b>REPRESENTANTES LEGALES DEL PROVEEDOR</b>
									<input type="hidden" name="sec_con_nuevo_prov_id_prov_hidden" id="sec_con_nuevo_prov_id_prov_hidden" />
								</div>
							</div>
						</div>
						<div class="row">
							<!--DNI REPRESENTANTE LEGAL-->
							<div class="col-xs-12 col-md-2 col-lg-2">
								<div class="form-group">
									<div class="control-label">Tipo de Documento <span class="campo_obligatorio_v2">(*)</span>:</div>
									<select name="repr_tipo_documento_id" id="repr_tipo_documento_id" class="form-control select2">

									</select>
								</div>
							</div>

							<div class="col-xs-12 col-md-3 col-lg-3">
								<div class="form-group">
									<div class="control-label">Nro de Documento <span class="campo_obligatorio_v2">(*)</span>:</div>
									<input type="text" name="dni_representante" id="dni_representante" 
									class="filtro" 
									style="width: 100%; height: 30px;"
									>
								</div>
							</div>

							<!--NOMBRE COMPLETO REPRESENTANTE LEGAL-->
							<div class="col-xs-12 col-md-4 col-lg-4">
								<div class="form-group">
									<div class="control-label">Nombre Completo del Representante Legal <span class="campo_obligatorio_v2">(*)</span>:</div>
									<input type="text" name="nombre_representante" id="nombre_representante" maxlength=250 class="filtro"
									 style="width: 100%; height: 30px;">
								</div>
							</div>

							<!--NRO CUENTA DE DETRACCIÓN-->
							<div class="col-xs-12 col-md-3 col-lg-3">
								<div class="form-group">
									<div class="control-label">Nro. Cuenta de Detracción (Banco de la Nación): </div>
									<input type="text" id="sec_con_nuev_prov_nro_cuenta_detraccion" name="sec_con_nuev_prov_nro_cuenta_detraccion" maxlength="50"
									 style="width: 100%; height: 30px;" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
								</div>
							</div>
						</div>
						<div class="row">
							<!--BANCO-->
							<div class="col-xs-12 col-md-4 col-lg-4">
								<div class="form-group">
									<div class="control-label">Representante Legal posee cuenta bancaria : </div>
									<select class="form-control input_text select2" data-live-search="true" 
										name="sec_con_nuevo_repr_legal_posee_banco" id="sec_con_nuevo_repr_legal_posee_banco" title="Seleccione el banco">
										<option value="0">- Seleccione -</option>
										<option value="1">SI</option>
										<option value="2">NO</option>
									</select>
								</div>
							</div>

							<!--BANCO-->
							<div class="col-xs-12 col-md-4 col-lg-4" style="display: none;" id="div_repr_legal_banco">
								<div class="form-group">
									<div class="control-label">Banco <span class="campo_obligatorio_v2">(*)</span>: </div>
									<select class="form-control input_text select2" data-live-search="true" 
										name="sec_con_nuevo_prov_banco" id="sec_con_nuevo_prov_banco" title="Seleccione el banco">
									</select>
								</div>
							</div>

							<!--NRO CUENTA-->
							<div class="col-xs-12 col-md-4 col-lg-4" style="display: none;" id="div_repr_legal_num_cuenta">
								<div class="form-group">
									<div class="control-label">Nro. de Cuenta : </div>
									<input type="text" id="sec_con_nuev_prov_nro_cuenta" name="sec_con_nuev_prov_nro_cuenta" maxlength="50"
									 style="width: 100%; height: 30px;" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
								</div>
							</div>

							<!--NRO CCI-->
							<div class="col-xs-12 col-md-4 col-lg-4" style="display: none;" id="div_repr_legal_num_cci">
								<div class="form-group">
									<div class="control-label">Nro. de CCI : </div>
									<input type="text" id="sec_con_nuev_prov_nro_cci" name="sec_con_nuev_prov_nro_cci" maxlength="50"
									 style="width: 100%; height: 30px;" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
								</div>
							</div>

							<div class="col-xs-12 col-md-8 col-lg-8" style="display: none;" id="div_repr_legal_nota">
								<div class="form-group">
									<p style="margin-top: 25px;"><b>Nota: Como mínimo debe de ingresar el Número de Cuenta o el Número de CCI.</b></p>
								</div>
							</div>

						</div>
						<div class="col-xs-12 col-md-12 col-lg-12" style="margin-top: 10px;">
							<button 
								type="button" 
								id="sec_con_nuevo_btn_nuevo_proveedor" 
								class="btn btn-sm btn-info" 
								onclick="sec_con_nuevo_prov_agregar_prov();">
								<i class="fa fa-plus" style="margin-right: 10px;"></i> Agregar Representante
							</button>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12" id="div_sec_con_nuevo_prov_editar_proveedor" style="text-align: center; display: none;">
							<div class="form-group">
								<button type="button" class="btn btn-sm btn-success" onclick="guardarActualizacionRepresentante();"><i class="fa fa-save"></i> Guardar</button>
								<button type="button" class="btn btn-sm btn-danger" onclick="cancelarEdicionProveedor();"><i class="fa fa-close"></i> Cancelar</button>
							</div>
						</div>
						<!--TABLA-->
						<div class="row">
							<div class="col-xs-12 col-md-12 col-lg-12" style="margin-top: 10px;">
								<table id="sec_con_nuevo_prov_tabla_proveedores_sin_representantes" class="table table-bordered table-hover no-mb" style="font-size:11px; margin-top: 10px;">
									<thead>
										<th>Tipo Doc.</th>
										<th>Nro Documento R.L</th>
										<th>Nombre Completo R.L</th>
										<th>N° Cuenta Detracción</th>
										<th>Banco</th>
										<th>N° Cuenta</th>
										<th>N° CCI</th>
										<th>Vigencia de Poder</th>
										<th>DNI</th>
									</thead>
									<tbody>
										<tr>
											<td colspan="8" style="text-align: center;">Contrato sin Representantes Legales.</td>
										</tr>
									</tbody>
								</table>
								<table id="sec_con_nuevo_prov_tabla_proveedores" class="table table-bordered table-hover no-mb" style="font-size:11px; margin-top: 10px; display: none;">
									<thead>
										<th>Tipo Doc.</th>
										<th>Nro Documento R.L</th>
										<th>Nombre Completo R.L</th>
										<th>N° Cuenta Detracción</th>
										<th>Banco</th>
										<th>N° Cuenta</th>
										<th>N° CCI</th>
										<th>Vigencia de Poder</th>
										<th>DNI</th>
										<th colspan="2">Opciones</th>
									</thead>
									<tbody>
									</tbody>
								</table>
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12" style="margin-top: 10px;">
							<div class="h4"><b>CONDICIONES COMERCIALES:</b></div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="h4">1) Objeto del Contrato:</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="form-group">
								<div class="control-label">Detalle de servicio a contratar. Detallar si existirán entregables, forma y plazo  <span class="campo_obligatorio_v2">(*)</span>:</div>
								<textarea name="detalle_servicio" id="detalle_servicio" rows="5" cols="50" style="width: 100%;"></textarea>
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="h4">2) Plazo del Contrato:</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Periodo<span class="campo_obligatorio_v2">(*)</span>:</div>
								<div class="col-xs-12 col-md-12 col-lg-12" style="padding: 0px;">
									<select 
										class="form-control select2" 
										name="plazo_id" 
										id="plazo_id">
										<?php $query = "SELECT id, nombre";
											$query .= " FROM cont_tipo_plazo";
											$query .= " WHERE status = 1";
											
											$list_query = $mysqli->query($query);
											$list = array();
											while ($li = $list_query->fetch_assoc()) { ?>
												
												<option value="<?php echo $li['id']?>"><?php echo $li['nombre']?></option>
										
									<?php }?>
									</select>
								</div>
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4" id="div_periodo">
							<div class="form-group" style="margin: 0px;">
								<div class="control-label">Cant. tiempo (Ejemplo: 1 año, 6 meses.)  <span class="campo_obligatorio_v2">(*)</span>:</div>
								<div class="col-xs-12 col-md-6 col-lg-6" style="padding: 0px;">
									<input type="number" id="periodo_numero" name="periodo_numero" class="filtro" style="min-height: 28px !important; width: 100%;">
								</div>
								<div class="col-xs-12 col-md-6 col-lg-6" style="padding: 0px;">
									<select 
										class="form-control select2" 
										name="periodo" 
										id="periodo">
									</select>
								</div>
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group" style="margin: 0px;">
								<div class="control-label">Fecha de Inicio <span class="campo_obligatorio_v2">(*)</span>:</div>
								<div class="input-group" style="padding: 0px;">
									<input
											type="text"
											name="fecha_inicio"
											id="fecha_inicio"
											class="filtro fecha_datepicker"
											value="<?php echo date("Y-m-d", strtotime("-1 days"));?>"
											readonly="readonly"
											style="min-height: 28px !important; width: 100%;"
											>
									<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="fecha_inicio" style="min-height: 28px !important;"></label>
								</div>
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4" id="div_num_dias_para_alertar_vencimiento">
							<div class="form-group">
								<div class="control-label">Días de anticipación para alertar vencimiento <span class="campo_obligatorio_v2">(*)</span>:</div>
								<div class="col-xs-12 col-md-12 col-lg-12" style="padding: 0px;">
									<input type="number" id="num_dias_para_alertar_vencimiento" name="num_dias_para_alertar_vencimiento" class="filtro" style="min-height: 28px !important; width: 100%;">
								</div>
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4" id="div_alerta" style="display: none;">
							<div class="form-group">
								<div class="control-label">Alerta de vencimiento<span class="campo_obligatorio_v2">(*)</span>:</div>
								<div class="col-xs-12 col-md-12 col-lg-12" style="padding: 0px;">
									<select 
										class="form-control select2" 
										name="alerta_vencimiento_por_fecha_id" 
										id="alerta_vencimiento_por_fecha_id">
										<option value="0">No aplica</option>
										<option value="1">Seleccionar fecha en la que desea recibir una alerta de vencimiento</option>
									</select>
								</div>
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4" id="div_fecha_de_la_alerta" style="display: none;">
							<div class="form-group" style="margin: 0px;">
								<div class="control-label">Fecha de Alerta <span class="campo_obligatorio_v2">(*)</span>:</div>
								<div class="input-group">
									<input
										type="text"
										name="fecha_de_la_alerta"
										id="fecha_de_la_alerta"
										class="filtro fecha_datepicker"
										value="<?php echo date("Y-m-d", strtotime("-1 days"));?>"
										readonly="readonly"
										style="min-height: 28px !important; width: 100%;"
										>
									<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="fecha_de_la_alerta" style="min-height: 28px !important;"></label>
								</div>
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="h4">3) Contraprestación:</div>
						</div>

						<input type="hidden" name="contraprestacion_id_para_cambios" id="contraprestacion_id_para_cambios">

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

						<div class="col-xs-12 col-md-12 col-lg-12" style="margin-top: 10px;">
							<button 
								type="button" 
								id="btn_agregar_contraprestacion" 
								class="btn btn-sm btn-info" 
								onclick="sec_contrato_nuevo_proveedor_guardar_contraprestacion();">
								<i class="fa fa-plus" style="margin-right: 10px;"></i> Agregar Contraprestación
							</button>

							<button 
								type="button" 
								id="btn_guardar_cambios_contraprestacion" 
								class="btn btn-sm btn-success" 
								onclick="sec_contrato_nuevo_proveedor_guardar_cambios_contraprestacion();"
								style="display: none;">
								<i class="fa fa-save"></i> Guardar
							</button>

							<button 
								type="button" 
								id="btn_cancelar_cambios_contraprestacion" 
								class="btn btn-sm btn-danger" 
								onclick="sec_contrato_nuevo_proveedor_cancelar_guardar_cambios_contraprestacion();"
								style="display: none;">
								<i class="fa fa-close"></i> Cancelar
							</button>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12" style="margin-top: 10px;" id="div_tabla_contraprestaciones">
							<table class="table table-bordered table-hover no-mb" style="font-size:11px; margin-top: 10px;">
								<thead>
									<tr>
										<th>#</th>
										<th>Tipo de Moneda</th>
										<th>Subtotal</th>
										<th>IGV</th>
										<th>Monto Bruto</th>
										<th>Forma de Pago</th>
										<th>Tipo de Comprobante a Emitir</th>
										<th>Plazo de Pago</th>
										<th>Opciones</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td colspan="9" style="text-align: center;">Contrato sin contraprestaciónes.</td>
									</tr>
								</tbody>
							</table>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="h4">4) Alcance del servicio <span class="campo_obligatorio_v2">(*)</span>:</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="form-group">
								<textarea name="alcance_servicio" id="alcance_servicio" rows="5" cols="50" 
								style="width: 100%;"></textarea>
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="h4">5) Terminación Anticipada <span class="campo_obligatorio_v2">(*)</span>:</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="form-group">
								<select 
									class="form-control select2" 
									name="tipo_terminacion_anticipada_id" 
									id="tipo_terminacion_anticipada_id" 
									style="width: 100%;"
									required>
								</select>
							</div>

							<div class="form-group" id="div_terminacion_anticipada" style="margin-top: 10px; display: none;">
								<textarea 
									name="terminacion_anticipada" 
									id="terminacion_anticipada" 
									placeholder="Ingrese el detalle de la Teminación Anticipada" 
									rows="3" 
									cols="50" 
									style="width: 100%;"
								></textarea>
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="h4">6) Observaciones:</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="form-group">
								<textarea name="observaciones_legal" id="observaciones_legal" rows="5" cols="50" 
								style="width: 100%;"></textarea>
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="h4"><b>ANEXOS</b></div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Ficha RUC de la empresa proveedora:</div>
								<input type="file"  name="archivo_ficha_ruc" accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip">
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Formato de contrato:</div>
								<input type="file" name="archivo_formato_contrato" accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip">
							</div>
						</div>
						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Know your client (KYC):</div>
								<input type="file" name="archivo_know_your_client" accept=".jpg, .jpeg, .png, .pdf, .doc, .docx">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="h4"><b>OTROS ANEXOS</b></div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<!--sec_nuevo_abrir_modal_nuevos_anexos-->
								<button type="button" class="btn btn-info btn-sm" id="sec_nuevo_btn_agregar_anexos_proveedores" onclick="sec_contrato_nuevo_abrir_modal_tipos_anexos_proveedor()">
									<i class="icon fa fa-plus"></i>
									Agregar otros anexos
								</button>
							</div>
						</div> 

						<div class="col-xs-12 col-md-4 col-lg-4">
				            <div id="archivos_proveedores" style="width:300px">
				            </div>
						</div>

						<div class="col-md-12" id="sec_nuevos_files_prueba_proveedores">
						</div>

						<div class="col-md-12" id="sec_nuevo_nuevos_anexos_listado_proveedor">
							<input id="fileToUploadAnexo" accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip" type="file" multiple="" style="display:none">
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<button type="submit" class="btn btn-success btn-block" id="guardar_contrato_proveedor">
								<i class="icon fa fa-save" id="demo-button-icon-left" style="display: inline-block;"></i>
								<span id="demo-button-text">Solicitar Contrato con Proveedor</span>
							</button>
						</div>
					</form>
				</div>
			</div>
			<!-- /PANEL: Contrato Proveedor -->


			<!-- PANEL: Acuerdo de Confidencialidad -->
			<div class="panel" id="divAcuerdoDeConfidencialidad" style="display: none;">
				<div class="panel-heading">
					<div class="panel-title">FICHA DE CONDICIONES DEL ACUERDO DE CONFIDENCIALIDAD  <span class="campo_obligatorio_v2" style="text-transform: none;">(*) Campos obligatorios</span></div>
				</div>
				<div class="panel-body">
					<form id="form_acuerdo_confidencialidad" name="form_acuerdo_confidencialidad" method="POST" enctype="multipart/form-data" autocomplete="off">
						<div class="row">
							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="h4"><b>DATOS DE CONTACTO</b></div>
							</div>

							<div class="col-xs-12 col-md-4 col-lg-4">
								<div class="form-group">
									<div class="control-label">Persona contacto <?=$valor_empresa_contacto?> <span class="campo_obligatorio_v2">(*)</span>:</div>
									<input type="text" name="persona_contacto_proveedor_ac" id="persona_contacto_proveedor_ac" maxlength=100 required class="filtro"
									 style="width: 100%; height: 30px;">
								</div>
							</div>

							<div class="col-xs-12 col-md-4 col-lg-4">
								<div class="form-group" style="margin-bottom: 0px; margin-top: 10px; padding-bottom: 0px;">
									<input type="checkbox" name="check_gerencia_proveedor_ac" id="check_gerencia_proveedor_ac" style="width: 10%; height: 30px; padding-bottom: 0px; margin-bottom: 0px; vertical-align: middle;">
									<label class="control-label" style="vertical-align: text-bottom;">Aprobación de Lourdes Britto</label>
								</div>
							</div>

							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="h4"><b>INFORMACIÓN DEL PROVEEDOR</b></div>
							</div>
						
							<!--RUC PROVEEDOR-->
							<div class="col-xs-12 col-md-4 col-lg-4">
								<div class="form-group">
									<div class="control-label">N° de RUC del Proveedor <span class="campo_obligatorio_v2">(*)</span>:</div>
									<input type="text"
									id="ruc_ac"
									name="ruc_ac" required 
									oninput="this.value=this.value.replace(/[^0-9]/g,'');"
									maxlength=11 
									class="filtro" 
									style="width: 100%; height: 30px;">
								</div>
							</div>
							<!--RAZON SOCIAL PROVEEDOR-->
							<div class="col-xs-12 col-md-4 col-lg-4">
								<div class="form-group">
									<div class="control-label">Razón Social del Proveedor <span class="campo_obligatorio_v2">(*)</span>:</div>
									<input type="text" name="razon_social_ac" required maxlength=50 class="filtro" 
									style="width: 100%; height: 30px;">
								</div>
							</div>
							
						</div>

						<div class="row">
							<div class="col-xs-12 col-md-12 col-lg-12">
								<div class="h4">
									<b>INFORMACIÓN DEL PROVEEDOR - REPRESENTANTES LEGALES</b>
									<input type="hidden" name="sec_con_nuevo_prov_id_prov_hidden_ac" id="sec_con_nuevo_prov_id_prov_hidden_ac" />
								</div>
							</div>
						</div>
						<div class="row">
							<!--DNI REPRESENTANTE LEGAL-->
							<div class="col-xs-12 col-md- col-lg-6">
								<div class="form-group">
									<div class="control-label">DNI del Representante Legal <span class="campo_obligatorio_v2">(*)</span>:</div>
									<input type="text" name="dni_representante_ac" id="dni_representante_ac" 
									maxlength=8
									class="filtro" 
									style="width: 100%; height: 30px;"
									oninput="this.value=this.value.replace(/[^0-9]/g,'');"
									>
								</div>
							</div>

							<!--NOMBRE COMPLETO REPRESENTANTE LEGAL-->
							<div class="col-xs-12 col-md-6 col-lg-6">
								<div class="form-group">
									<div class="control-label">Nombre Completo del Representante Legal <span class="campo_obligatorio_v2">(*)</span>:</div>
									<input type="text" name="nombre_representante_ac" id="nombre_representante_ac" maxlength=50 class="filtro"
									 style="width: 100%; height: 30px;">
								</div>
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12" style="margin-top: 10px;">
							<button 
								type="button" 
								id="sec_con_nuevo_btn_nuevo_proveedor_ac" 
								class="btn btn-sm btn-info" 
								onclick="sec_con_nuevo_prov_agregar_prov_ac();">
								<i class="fa fa-plus" style="margin-right: 10px;"></i> Agregar Representante
							</button>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12" id="div_sec_con_nuevo_prov_editar_proveedor_ac" style="text-align: center; display: none;">
							<div class="form-group">
								<button type="button" class="btn btn-sm btn-success" onclick="guardarActualizacionRepresentante_ac();"><i class="fa fa-save"></i> Guardar</button>
								<button type="button" class="btn btn-sm btn-danger" onclick="cancelarEdicionProveedor_ac();"><i class="fa fa-close"></i> Cancelar</button>
							</div>
						</div>
						<!--TABLA-->
						<div class="row">
							<div class="col-xs-12 col-md-12 col-lg-12">
								<table id="sec_con_nuevo_prov_tabla_proveedores_ac" class="table">
									<thead>
										<th>DNI R.L</th>
										<th>Nombre Completo R.L</th>
										<th>Vigencia de Poder</th>
										<th>DNI</th>
									</thead>
									<tbody>

									</tbody>
								</table>
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="h4"><b>CONDICIONES COMERCIALES:</b></div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="h4">1) Objeto del Contrato:</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="form-group">
								<div class="control-label">Detalle de servicio a contratar. Detallar si existirán entregables, forma y plazo  <span class="campo_obligatorio_v2">(*)</span>:</div>
								<textarea name="detalle_servicio_ac" required rows="5" cols="50" style="width: 100%;"></textarea>
							</div>
						</div>

					

						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="h4">2) Fecha de Inicio <span class="campo_obligatorio_v2">(*)</span>:</div>
								<div class="input-group">
									<input
											type="text"
											name="fecha_inicio_ac"
											id="fecha_inicio_ac"
											class="form-control fecha_datepicker"
											value="<?php echo date("Y-m-d", strtotime("-1 days"));?>"
											readonly="readonly"
											style="height: 30px;"
											>
									<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="fecha_inicio"></label>
								</div>

							</div>
						</div>

						
						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="h4">3) Observaciones:</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="form-group">
								<textarea name="observaciones_legal_ac" id="observaciones_legal_ac" rows="5" cols="50" 
								style="width: 100%;"></textarea>
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="h4"><b>ANEXOS</b></div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Ficha RUC de la empresa proveedora:</div>
								<input type="file"  name="archivo_ficha_ruc" accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip">
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Formato de contrato:</div>
								<input type="file" name="archivo_formato_contrato" accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip">
							</div>
						</div>
						
						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="h4"><b>OTROS ANEXOS</b></div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<!--sec_nuevo_abrir_modal_nuevos_anexos-->
								<button type="button" class="btn btn-info btn-sm" id="sec_nuevo_btn_agregar_anexos_proveedores_ac" onclick="sec_contrato_nuevo_abrir_modal_tipos_anexos_acuerdo_confidencialidad()">
									<i class="icon fa fa-plus"></i>
									Agregar otros anexos
								</button>
							</div>
						</div> 

						<div class="col-xs-12 col-md-4 col-lg-4">
				            <div id="archivos_proveedores_ac" style="width:300px">
				            </div>
						</div>

						<div class="col-md-12" id="sec_nuevos_files_prueba_proveedores_ac">
						</div>

						<div class="col-md-12" id="sec_nuevo_nuevos_anexos_listado_proveedor_ac">
							<input id="fileToUploadAnexo" accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip" type="file" multiple="" style="display:none">
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>
						
						<div class="col-xs-12 col-md-12 col-lg-12">
							<button type="submit" class="btn btn-success btn-block" id="guardar_acuerdo_confidencialidad">
								<i class="icon fa fa-save" id="demo-button-icon-left" style="display: inline-block;"></i>
								<span id="demo-button-text">Solicitar Acuerdo de Confidencialidad</span>
							</button>
						</div>
					</form>
					
				</div>
				
			</div>
			<!-- /PANEL: CAcuerdo de Confidencialidad -->


			 
		</div>


		<div class="col-xs-12 col-md-12 col-lg-7" id="div_detalle_solicitud_izquierda" style="display: none;">
			<div class="panel" id="divDetalleSolicitud">
				<div class="panel-heading">
					<div class="panel-title">DETALLE DE LA SOLICITUD DE CONTRATO DE ARRENDATARIO</div>
				</div>

				<div class="panel-body">
					<form id="form_adenda_arrendamiento">
					</form>
				</div>
			</div>

			<div class="panel" id="divAnexos" style="display: none;">
				<div class="panel-heading">
					<div class="panel-title" id="divAnexoHeadingValue">TEMPORAL</div>
				</div>

				<div class="panel-body" style="padding: 10px 0px 10px 0px;">
					<div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center; margin-bottom: 5px; display:none;" id="divVerPdfFullPantalla">
						<button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#exampleModalPreview" style="background-color:#7dc623;border-color: #aaf152;">
							<i class="fa fa-arrows-alt"></i>  Ver documento en toda la Pantalla
						</button>        
					</div>

					<div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center;margin-bottom: 5px; display:none;" id="divVisorPdfPrincipal">
					</div>

					<div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center;margin-bottom: 5px; display:none;" id="divVerImagenFullPantalla">
						<button type="button" class="btn btn-block btn-block btn-primary" id="VerImagenFullPantalla" style="background-color:#7dc623;">
							<i class="fa fa-arrows-alt"></i>  Ver imagen en toda la Pantalla
						</button>
					</div>

					<div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center;margin-bottom: 5px; display:none;" id="divVisorImagen">
					</div>
				</div>
			</div>
		</div>


		<div class="col-xs-12 col-md-12 col-lg-7" id="div_detalle_solicitud_proveedor_izquierda" style="display: none;">
			<div class="panel" id="divDetalleSolicitud">
				<div class="panel-heading">
					<div class="panel-title">DETALLE DE LA SOLICITUD DE CONTRATO DE PROVEEDOR</div>
				</div>

				<div class="panel-body">
					<form id="form_adenda_proveedor">
					</form>
				</div>
			</div>

			<div class="panel" id="divAnexos" style="display: none;">
				<div class="panel-heading">
					<div class="panel-title" id="divAnexoHeadingValue">TEMPORAL</div>
				</div>

				<div class="panel-body" style="padding: 10px 0px 10px 0px;">
					<div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center; margin-bottom: 5px; display:none;" id="divVerPdfFullPantalla">
						<button type="button" class="btn btn-block btn-primary" data-toggle="modal" data-target="#exampleModalPreview" style="background-color:#7dc623;border-color: #aaf152;">
							<i class="fa fa-arrows-alt"></i>  Ver documento en toda la Pantalla
						</button>        
					</div>

					<div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center;margin-bottom: 5px; display:none;" id="divVisorPdfPrincipal">
					</div>

					<div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center;margin-bottom: 5px; display:none;" id="divVerImagenFullPantalla">
						<button type="button" class="btn btn-block btn-block btn-primary" id="VerImagenFullPantalla" style="background-color:#7dc623;">
							<i class="fa fa-arrows-alt"></i>  Ver imagen en toda la Pantalla
						</button>
					</div>

					<div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center;margin-bottom: 5px; display:none;" id="divVisorImagen">
					</div>
				</div>

			</div>
		</div>


		<div class="col-xs-12 col-md-12 col-lg-5" id="div_detalle_solicitud_derecha" style="display: none;">
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

									<div id="divTablaAdendas">
									</div>

									<button type="button" class="btn btn-success btn-xs btn-block" id="btnRegistrarAdenda" onclick="sec_contrato_nuevo_guardar_adenda();">
										<i class="icon fa fa-save"></i>
										<span id="demo-button-text">Registrar Solicitud de Adenda</span>
									</button>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>




<!-- INICIO MODAL BUSCAR PROPIETARIO -->
<div id="modalBuscarPropietario" class="modal" data-keyboard="false">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_buscar_propietario_titulo">Buscar Propietario del Inmueble</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form id="frmBuscarRemitente" autocomplete="off">

						<input type="hidden" id="modal_buscar_propietario_tipo_solicitud">
						<input type="hidden" id="persona_id_actual_adenda">
						<input type="hidden" id="propietario_id_adenda">

						<div class="col-md-12 col-sm-12 col-xs-12" style="padding: 10px 15px 10px 15px;">
							<div class="form-group">
								<div class="col-md-4 col-sm-4 col-xs-12" style="padding: 0px;">
									<select name="modal_propietario_tipo_busqueda" id="modal_propietario_tipo_busqueda" class="form-control">
										<option value="1">Buscar por Nombre de Propietario</option>
										<option value="2">Buscar por Numero de Documento (DNI o RUC)</option>
									</select>
								</div>
								<div class="col-md-5 col-sm-5 col-xs-12">
									<input type="text" name="modal_propietario_nombre_o_numdocu" id="modal_propietario_nombre_o_numdocu" class="form-control" placeholder="Ingrese el nombre, despues los apellidos">
								</div>
								<div class="col-md-3 col-sm-3 col-xs-12" style="padding: 0px;">
									<button type="button" class="btn btn-success btn-sm btn-block" id="btnBuscarPropietario" onclick="sec_contrato_nuevo_buscar_propietario()">
										<i class="icon fa fa-search"></i>
										<span id="demo-button-text">Buscar Propietario</span>
									</button>
								</div>
							</div>
						</div>

						<div class="col-md-12 col-sm-12 col-xs-12" style="margin-bottom: 10px">
							<div class="form-group" id="tlbPropietariosxBusqueda">
							</div>
						</div>

						<div id="divNoSeEncontroPropietario" class="col-md-12 col-sm-12 col-xs-12" style="display: none; margin-bottom: 10px">
							<div class="form-group">
								<div class="alert alert-warning" role="alert">
									<div class="h4 strong">Resultados de la busqueda:</div>
									<p>
										No existe en la base de datos el propietario con <a href="#" class="alert-link" id="valoresDeBusqueda"></a>. Clic en el boton Registrar nuevo propietario para registrarlo en nuestra base de datos.
									</p>
									<p>
										
									</p>
								</div>
							</div>
						</div>

						<div id="divRegistrarNuevoPropietario" class="col-md-12 col-sm-12 col-xs-12" style="display: none;">
							<div class="form-group">
								<button type="button" class="btn btn-success btn-sm btn-block" id="btnModalNuevoPropietario">
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
<!-- FIN MODAL BUSCAR PROPIETARIO -->


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

						<input type="hidden" id="modal_buscar_propietario_tipo_solicitud_ca">
						<input type="hidden" id="persona_id_actual_adenda_ca">
						<input type="hidden" id="propietario_id_adenda_ca">

						<div class="col-md-12 col-sm-12 col-xs-12" style="padding: 10px 15px 10px 15px;">
							<div class="form-group">
								<div class="col-md-4 col-sm-4 col-xs-12" style="padding: 0px;">
									<select name="modal_propietario_tipo_busqueda_ca" id="modal_propietario_tipo_busqueda_ca" class="form-control">
										<option value="1">Buscar por Nombre de Propietario</option>
										<option value="2">Buscar por Numero de Documento (DNI o RUCx)</option>
									</select>
								</div>
								<div class="col-md-5 col-sm-5 col-xs-12">
									<input type="text" name="modal_propietario_nombre_o_numdocu_ca" id="modal_propietario_nombre_o_numdocu_ca" class="form-control" placeholder="Ingrese el nombre, despues los apellidos">
								</div>
								<div class="col-md-3 col-sm-3 col-xs-12" style="padding: 0px;">
									<button type="button" class="btn btn-success btn-sm btn-block" id="btnBuscarPropietario_ca" onclick="sec_contrato_nuevo_buscar_propietario_ca()">
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
								<button type="button" class="btn btn-success btn-sm btn-block" id="btnModalNuevoPropietario_ca">
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


<!-- INICIO MODAL NUEVO PROPIETARIO -->
<div id="modalNuevoPropietario" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_nuevo_propietario_titulo">Registrar Nuevo Propietario</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form autocomplete="off" id="frm_nuevo_propietario">
						<input type="hidden" id="modal_nuevo_propietario_tipo_solicitud">
						<input type="hidden" id="modal_propietaria_id_persona_para_cambios">
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
								<input type="text" id="modal_propietario_num_docu" name="modal_propietario_num_docu" class="filtro txt_filter_style mask_dni_agente" maxlength="12" style="width: 100%; height: 30px;">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_num_ruc_propietario">
							<div class="form-group">
								<div class="control-label">Número de RUC del propietario:</div>
								<input type="text" id="modal_propietario_num_ruc" name="modal_propietario_num_ruc" class="filtro txt_filter_style" maxlength="11" style="width: 100%; height: 30px;">
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
								<input type="number" id="modal_propietario_contacto_telefono" name="modal_propietario_contacto_telefono" class="filtro txt_filter_style" maxlength="9" style="width: 100%; height: 30px;">
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
				<button type="button" class="btn btn-success" id="btn_guardar_cambios_propietario">
					<i class="icon fa fa-plus"></i>
					Guardar cambios
				</button>
				<button type="button" class="btn btn-success" id="btn_agregar_propietario_a_la_adenda">
					<i class="icon fa fa-plus"></i>
					Agregar propietario a la adenda
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL NUEVO PROPIETARIO -->

<!-- INICIO MODAL NUEVO PROPIETARIO CA -->
<div id="modalNuevoPropietario_ca" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_nuevo_propietario_titulo_ca">Registrar Nuevo Propietario</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form autocomplete="off" id="frm_nuevo_propietario_ca">
						<input type="hidden" id="modal_nuevo_propietario_tipo_solicitud_ca">
						<input type="hidden" id="modal_propietaria_id_persona_para_cambios_ca">
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Tipo de Persona:</div>
								<select
									class="form-control select2"
									name="modal_propietario_tipo_persona_ca" 
									id="modal_propietario_tipo_persona_ca">
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
								<input type="text" id="modal_propietario_nombre_ca" name="modal_propietario_nombre" class="filtro txt_filter_style" maxlength="50" style="width: 100%; height: 30px;">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Tipo de documento de identidad:</div>
								<select class="form-control" id="modal_propietario_tipo_docu_ca">
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
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_num_docu_propietario_ca" style="display: none;">
							<div class="form-group">
								<div class="control-label" id="label_num_docu_propietario_ca">Número de documento de identidad del propietario:</div>
								<input type="text" id="modal_propietario_num_docu_ca" name="modal_propietario_num_docu" class="filtro txt_filter_style" maxlength="11" style="width: 100%; height: 30px;">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_num_ruc_propietario_ca">
							<div class="form-group">
								<div class="control-label">Número de RUC del propietario:</div>
								<input type="text" id="modal_propietario_num_ruc_ca" name="modal_propietario_num_ruc" class="filtro txt_filter_style" maxlength="11" style="width: 100%; height: 30px;">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Domicilio del propietario:</div>
								<input type="text" id="modal_propietario_direccion_ca" name="modal_propietario_direccion" class="filtro txt_filter_style" maxlength="100" style="width: 100%; height: 30px;" autocomplete="off">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_propietario_representante_legal_ca" style="display: none;">
							<div class="form-group">
								<div class="control-label">Representante Legal:</div>
								<input type="text" id="modal_propietario_representante_legal_ca" class="filtro txt_filter_style" maxlength="50" style="width: 100%; height: 30px;">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_propietario_num_partida_registral_ca" style="display: none;">
							<div class="form-group">
								<div class="control-label">N° Partida Registral de la empresa:</div>
								<input type="text" id="modal_propietario_num_partida_registral_ca" class="filtro txt_filter_style" maxlength="50" style="width: 100%; height: 30px;">
							</div>
						</div>
						
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_propietario_persona_contacto_ca">
							<div class="form-group">
								<div class="control-label">Persona de contacto</div>
								<select class="form-control" id="modal_propietario_tipo_persona_contacto_ca">
									<option value="0">Seleccione la persona contacto</option>
									<option value="1">El propietario es la persona de contacto</option>
									<option value="2">El propietario no es la persona de contacto</option>
								</select>
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_propietario_contacto_nombre_ca" style="display: none;">
							<div class="form-group">
								<div class="control-label">Nombre de la persona de contacto:</div>
								<input type="text" id="modal_propietario_contacto_nombre_ca" name="modal_propietario_contacto_nombre" class="filtro txt_filter_style" maxlength="50" style="width: 100%; height: 30px;">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Teléfono de la persona de contacto:</div>
								<input type="number" id="modal_propietario_contacto_telefono_ca" name="modal_propietario_contacto_telefono" class="filtro txt_filter_style" maxlength="9" style="width: 100%; height: 30px;" autocomplete="off">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Mail de la persona de contacto:</div>
								<input type="text" id="modal_propietario_contacto_email_ca" name="modal_propietario_contacto_email" class="filtro txt_filter_style" maxlength="50" style="width: 100%; height: 30px;">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_propietario_mensaje_ca" style="display: none">
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
				<button type="button" class="btn btn-success" id="btn_agregar_propietario_ca">
					<i class="icon fa fa-plus"></i>
					Agregar propietario
				</button>
				<button type="button" class="btn btn-success" id="btn_guardar_cambios_propietario_ca">
					<i class="icon fa fa-plus"></i>
					Guardar cambios
				</button>
				<button type="button" class="btn btn-success" id="btn_agregar_propietario_a_la_adenda_ca">
					<i class="icon fa fa-plus"></i>
					Agregar propietario al agente
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL NUEVO PROPIETARIO CA -->


<!-- INICIO MODAL AGREGAR INMUEBLE -->
<div id="modalAgregarInmueble" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Agregar Inmueble</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form autocomplete="off">
						

						<div class="col-xs-12 col-md-12 col-lg-12" id="div_modal_inmueble_mensaje" style="display: none">
							<div class="form-group">
								<div class="alert alert-danger" role="alert">
									<strong id="modal_inmueble_mensaje"></strong>
								</div>
							</div>
						</div>
					</form>
				</div> 
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
				<button type="button" class="btn btn-success" id="btnAgregarInmueble">Agregar inmueble</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL AGREGAR INMUEBLE -->


<!-- INICIO MODAL AGREGAR INCREMENTOS -->
<div id="modalAgregarIncrementos" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_incremento_titulo">Registrar Incremento</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form autocomplete="off" id="frm_incremento">

						<div class="col-xs-12 col-md-12 col-lg-12">
							<table class="table table-bordered table-striped no-mb" style="font-size:12px">
								<thead>
									<th>Valor</th>
									<th>Tipo Valor</th>
									<th>Continuidad</th>
									<th id="titulo_incremento_a_partir">A partir del</th>
								</thead>
								<tbody>
									<tr>
										<td>
											<input type="hidden" id="contrato_incrementos_id_incremento_para_cambios" name="contrato_incrementos_id_incremento_para_cambios">
											<input type="text" id="contrato_incrementos_monto_o_porcentaje" class="filtro" style="width: 60px; height: 30px; text-align: right;">
										</td>
										<td>
											<select class="form-control select2" id="contrato_incrementos_en" style="width: 100%; height: 30px;">
												<option value="0">- Seleccione -</option>
												<?php
												$sel_query = $mysqli->query("SELECT id, nombre
												FROM cont_tipo_pago_incrementos
												WHERE estado = 1
												ORDER BY nombre ASC;");
												while($sel=$sel_query->fetch_assoc()){?>
												<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
												<?php
												}
												?>
											</select>
										</td>
										<td>
											<select class="form-control select2" id="contrato_incrementos_continuidad" style="width: 100%; height: 30px;">
												<option value="0">- Seleccione -</option>
												<?php
												$sel_query = $mysqli->query("SELECT id, nombre
												FROM cont_tipo_continuidad_pago
												WHERE estado = 1
												ORDER BY nombre ASC;");
												while($sel=$sel_query->fetch_assoc()){?>
												<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
												<?php
												}
												?>
											</select>
										</td>
										<td id="td_contrato_incrementos_a_partir_de_año">
											<select class="form-control select2" id="contrato_incrementos_a_partir_de_año" style="width: 100%; height: 30px;">

												<?php $sel_query = $mysqli->query("SELECT id, nombre
													FROM cont_tipo_anios_incrementos
													WHERE status = 1
													ORDER BY id ASC;");
													while($sel=$sel_query->fetch_assoc()){?>
													<option value="<?php echo  $sel["id"];?>"><?php echo  $sel["nombre"];?></option>

												<?php }?>
											</select>
										</td>
									</tr>
								</tbody>
							</table> 
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="form-group">
									<button type="button" class="btn btn-success btn-xs btn-block" id="btnAgregarSoloIncremento">
										<i class="icon fa fa-plus"></i>
										<span id="demo-button-text">Agregar el incremento</span>
									</button>
								</div>
							</div>
						</div>
						<div class="col-xs-12 col-md-8 col-lg-8">
							<div class="form-group">
								<div class="form-group">
									<button type="button" class="btn btn-success btn-xs btn-block" id="btnAgregarIncrementoPlus">
										<i class="icon fa fa-plus"></i>
										<span id="demo-button-text">Agregar el incremento y seguir agregando otro incremento</span>
									</button>
								</div>
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12" id="div_btn_guardar_cambios_incremento" style="display: none;">
							<div class="form-group">
								<div class="form-group">
									<button type="button" class="btn btn-success btn-xs btn-block" id="btn_guardar_cambios_incremento">
										<i class="icon fa fa-plus"></i>
										<span id="demo-button-text">Guardar cambios</span>
									</button>
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
<!-- FIN MODAL AGREGAR INCREMENTOS -->


<!-- INICIO MODAL ESCOGER BENEFICIARIO -->
<div id="modalCandidatosBeneficiario" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Seleccionar Beneficiario</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form autocomplete="off" >
						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="form-group">
								<div id="divTablaCandidatosBeneficiarios"></div>
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12">
							<br><br>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="form-group">
								<div class="alert alert-warning" role="alert">
									<strong><i class="glyphicon glyphicon-info-sign"></i> Si el beneficiario no es uno de los propietarios, dar clic en </strong>
									<button type="button" class="btn btn-success btn-xs" id="btnModalRegistrarBeneficiario">
										<i class="icon fa fa-plus"></i>
										<span id="demo-button-text">Registrar Beneficiario</span>
									</button>
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
<!-- FIN MODAL ESCOGER BENEFICIARIO -->


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
					<form id="frmNuevoBeneficiario" autocomplete="off" >
						<input type="hidden" id="modal_beneficiario_id_beneficiario_para_cambios">
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
									while($sel=$sel_query->fetch_assoc()){?>
									<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
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
									while($sel=$sel_query->fetch_assoc()){?>
									<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
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
									while($sel=$sel_query->fetch_assoc()){?>
									<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
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
				<button type="button" class="btn btn-success" id="btn_agregar_beneficiario">Agregar beneficiario</button>
				<button type="button" class="btn btn-success" id="btn_guardar_cambios_beneficiario">Guardar cambios</button>
				<button type="button" class="btn btn-success" id="btn_guardar_beneficiario_adenda">
					<i class="icon fa fa-save"></i> Guardar como nuevo beneficiario
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL NUEVO BENEFICIARIO -->


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
													<select
														class="form-control input_text select2"
														data-live-search="true" 
														data-col="area_id" 
														data-table="tbl_areas"
														name="adenda_inmueble_id_departamento" 
														id="adenda_inmueble_id_departamento" 
														title="Seleccione el departamento">
													</select>
												</div>
											</div>
											<div id="div_adenda_solicitud_provincias" class="col-xs-12 col-md-12 col-lg-12">
												<div class="form-group">
													<div class="control-label">Provincia:</div>
													<select class="form-control input_text select2"
														data-live-search="true" 
														data-col="personal_id" 
														data-table="tbl_personal"
														name="adenda_inmueble_id_provincia" 
														id="adenda_inmueble_id_provincia" 
														title="Seleccione el tipo de Contrato">
													</select>
												</div>
											</div>
											<div id="div_adenda_solicitud_distrito" class="col-xs-12 col-md-12 col-lg-12">
												<div class="form-group">
													<div class="control-label">Distrito:</div>
													<select class="form-control input_text select2"
														data-live-search="true" 
														data-col="personal_id" 
														data-table="tbl_personal"
														name="adenda_inmueble_id_distrito" 
														id="adenda_inmueble_id_distrito" 
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
				<button type="button" class="btn btn-success" id="btnModalRegistrarAdenda" onclick="sec_contrato_nuevo_guardar_detalle_adenda('modalAgregar');">
					<i class="icon fa fa-plus"></i>
					<span id="demo-button-text">Agregar solicitud de edición a la Adenda</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL SOLICITUD DE ADENDA -->


<!-- INICIO MODAL ADELANTOS -->
<div id="modalAdelantos" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="myModalLabel">Seleccione los meses de adelanto</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form id="form_adelantos" autocomplete="off" >
						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="form-group">
								<div>
									<label class="checkbox-inline">
										<input type="checkbox" class="contrato_adelanto" value="1">
										Del Primer mes
									</label>
								</div>
								<div>
									<label class="checkbox-inline">
										<input type="checkbox" class="contrato_adelanto" value="2">
										Del Segundo mes
									</label>
								</div>
								<div>
									<label class="checkbox-inline">
										<input type="checkbox" class="contrato_adelanto" value="3">
										Del Tercer mes
									</label>
								</div>
								<div>
									<label class="checkbox-inline">
										<input type="checkbox" class="contrato_adelanto" value="4">
										Del Cuarto mes
									</label>
								</div>
								<div>
									<label class="checkbox-inline">
										<input type="checkbox" class="contrato_adelanto" value="5">
										Del Quinto mes
									</label>
								</div>
								<div>
									<label class="checkbox-inline">
										<input type="checkbox" class="contrato_adelanto" value="6">
										Del Sexto mes
									</label>
								</div>
								<div>
									<label class="checkbox-inline">
										<input type="checkbox" class="contrato_adelanto" value="x">
										Del Antepenúltimo mes
									</label>
								</div>
								<div>
									<label class="checkbox-inline">
										<input type="checkbox" class="contrato_adelanto" value="y">
										Del Penúltimo mes
									</label>
								</div>
								<div>
									<label class="checkbox-inline">
										<input type="checkbox" class="contrato_adelanto" value="z">
										Del Último mes
									</label>
								</div>
							</div>
						</div>
					</form>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-success" id="btn_guardar_adelantos" onclick="sec_contrato_nuevo_guardar_adelantos('modalAgregar');">
					<i class="icon fa fa-save"></i>
					<span id="demo-button-text">Guardar meses de adelanto</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL ADELANTOS -->


<!-- INICIO MODAL NUEVOS ANEXOS -->
<div id="modalNuevosAnexos" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-sm" role="document" style="width: 50%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Agregar otro anexo</h4>
			</div>
			<div class="modal-body">
				<form id="sec_nuevo_form_modal_nuevo_anexo" name="sec_nuevo_form_modal_nuevo_anexo" method="POST" enctype="multipart/form-data" autocomplete="off">
					<div class="row">
					    <div class="col-md-3" style="text-align: right;">
					        <label>Tipo de anexo: </label>
					    </div>
					    <div class="col-md-5">
					        <select class="form-control input_text select2 col-5" name="modal_nuevo_anexo_select_tipos" id="modal_nuevo_anexo_select_tipos" title="Seleccione el tipo de anexo">
					            <!--<option value="0"> - Seleccione - </option>
					            <option value="1">Partida de Nacimiento 2</option>
					            <option value="2">Partida de Nacimiento 3</option>-->
					        </select>
					    </div>
					    <div class="col-md-4">
					        <button type="button" class=" col-5 btn btn-sm btn-info">
					            <i class="icon fa fa-plus"></i>
					            <span>Agregar Tipo</span>
					        </button>
					    </div>
					</div>
					<br><br>
					<div class="row">
						<div class="col-md-3"></div>
						<div id="sec_contrato_nuevo_div_input_file_nuevo_anexo" class="col-md-5">
							<!--<input type="file" id="sec_nuevo_file_nuevo_anexo" name="sec_nuevo_file_nuevo_anexo" required accept=".jpg, .jpeg, .png, .pdf">-->
						</div>
						<div class="col-md-3"></div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" id="sec_nuevo_modal_nuevos_close" onclick="sec_contrato_nuevo_close_modal_nuevos_anexos()">
					<i class="icon fa fa-close"></i>
					<span>Cancelar</span>
				</button>
				<button type="button" class="btn btn-success" id="sec_nuevo_modal_guardar_archivo" onclick="sec_nuevo_modal_guardar_nuevo_anexo()">
					<i class="icon fa fa-save"></i>
					<span>Agregar</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL NUEVOS ANEXOS -->


<!-- INICIO MODAL TIPOS ANEXOS -->
<div id="modaltiposanexos" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-sm" role="document" style="width: 30%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Selecciona tipo de Anexo</h4>
			</div>
			<div class="modal-body">
				<form id="sec_nuevo_form_modal_nuevo_anexo" name="sec_nuevo_form_modal_nuevo_anexo" method="POST" enctype="multipart/form-data" autocomplete="off">
					<div class="row">
					    <div class="col-md-8">
					    	<input type="hidden" name="modal_nuevo_anexo_tipo_contrato_id" id="modal_nuevo_anexo_tipo_contrato_id">
					        <select class="form-control input_text select2 col-5" name="modal_nuevo_anexo_select_tipos2"  id="modal_nuevo_anexo_select_tipos2" title="Seleccione el tipo de anexo">
					        </select>
					    </div>
					    <div class="col-md-4">
					        <button type="button" class=" col-5 btn btn-sm btn-info" id="sec_con_nuevo_agregar_tipo_anexo" onclick="sec_nuevo_con_agregar_nuevo_tipo_archivo()">
					            <i class="icon fa fa-plus"></i>
					            <span>Agregar Tipo</span>
					        </button>
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
				<button type="button" class="btn btn-success" id="sec_nuevo_modal_guardar_archivo" onclick="anadirArchivo()">
					<i class="icon fa fa-save"></i>
					<span>Elegir Tipo</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL TIPOS ANEXOS -->


<!-- INICIO MODAL TIPOS ANEXOS ACUERDOS DE CONFIDENCIALIDAD-->
<div id="modaltiposanexos_ac" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-sm" role="document" style="width: 30%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Selecciona tipo de Anexo</h4>
			</div>
			<div class="modal-body">
				<form id="sec_nuevo_form_modal_nuevo_anexo_ac" name="sec_nuevo_form_modal_nuevo_anexo_ac" method="POST" enctype="multipart/form-data" autocomplete="off">
					<div class="row">
					    <div class="col-md-8">
					    	<input type="hidden" name="modal_nuevo_anexo_tipo_contrato_id_ac" id="modal_nuevo_anexo_tipo_contrato_id_ac">
					        <select class="form-control input_text select2 col-5" name="modal_nuevo_anexo_select_tipos2_ac"  id="modal_nuevo_anexo_select_tipos2_ac" title="Seleccione el tipo de anexo">
					        </select>
					    </div>
					    <div class="col-md-4">
					        <button type="button" class=" col-5 btn btn-sm btn-info" id="sec_con_nuevo_agregar_tipo_anexo" onclick="sec_nuevo_con_agregar_nuevo_tipo_archivo_ac()">
					            <i class="icon fa fa-plus"></i>
					            <span>Agregar Tipo</span>
					        </button>
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
				<button type="button" class="btn btn-success" id="sec_nuevo_modal_guardar_archivo" onclick="anadirArchivo_ac()">
					<i class="icon fa fa-save"></i>
					<span>Elegir Tipo</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL TIPOS ANEXOS ACUERDOS DE CONFIDENCIALIDAD-->

<!-- INICIO MODAL TIPOS ANEXOS CONTRATO DE AGENTES-->
<div id="modaltiposanexos_ca" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-sm" role="document" style="width: 30%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Selecciona tipo de Anexo</h4>
			</div>
			<div class="modal-body">
				<form id="sec_nuevo_form_modal_nuevo_anexo_ca" name="sec_nuevo_form_modal_nuevo_anexo_ca" method="POST" enctype="multipart/form-data" autocomplete="off">
					<div class="row">
					    <div class="col-md-8">
					    	<input type="hidden" name="modal_nuevo_anexo_tipo_contrato_id_ca" id="modal_nuevo_anexo_tipo_contrato_id_ca">
					        <select class="form-control input_text select2 col-5" name="modal_nuevo_anexo_select_tipos2_ca"  id="modal_nuevo_anexo_select_tipos2_ca" title="Seleccione el tipo de anexo">
					        </select>
					    </div>
					    <div class="col-md-4">
					        <button type="button" class=" col-5 btn btn-sm btn-info" id="sec_con_nuevo_agregar_tipo_anexo_ca" onclick="sec_nuevo_con_agregar_nuevo_tipo_archivo_ca()">
					            <i class="icon fa fa-plus"></i>
					            <span>Agregar Tipo</span>
					        </button>
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
				<button type="button" class="btn btn-success" id="sec_nuevo_modal_guardar_archivo" onclick="anadirArchivo_ca()">
					<i class="icon fa fa-save"></i>
					<span>Elegir Tipo</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL TIPOS ANEXOS CONTRATO DE AGENTES-->

<!-- INICIO MODAL AGREGAR TIPO ANEXO -->
<div id="sec_nuevo_con_agregar_nuevo_tipo_archivo" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-sm" role="document" style="width: 30%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Agregar nuevo tipo de Anexo</h4>
			</div>
			<div class="modal-body">
				<form id="sec_con_nuevo_agregar_tipo_anexo_form" name="sec_con_nuevo_agregar_tipo_anexo_form" method="POST" enctype="multipart/form-data" autocomplete="off">
					<div class="row">
					    <div class="col-md-10">
					        <input type="text" name="sec_nuevo_tipo_anexo_nombre" id="sec_nuevo_tipo_anexo_nombre" class="form-control" placeholder="Nombre del tipo de anexo" />
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
				<button type="button" class="btn btn-success" id="sec_nuevo_modal_guardar_archivo" onclick="guardarNuevoTipoAnexo()">
					<i class="icon fa fa-save"></i>
					<span>Guardar nuevo tipo de anexo</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL AGREGAR TIPO ANEXO -->

<!-- INICIO MODAL AGREGAR TIPO ANEXO AC -->
<div id="sec_nuevo_con_agregar_nuevo_tipo_archivo_ac" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-sm" role="document" style="width: 30%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Agregar nuevo tipo de Anexo</h4>
			</div>
			<div class="modal-body">
				<form id="sec_con_nuevo_agregar_tipo_anexo_form" name="sec_con_nuevo_agregar_tipo_anexo_form" method="POST" enctype="multipart/form-data" autocomplete="off">
					<div class="row">
					    <div class="col-md-10">
					        <input type="text" name="sec_nuevo_tipo_anexo_nombre_ac" id="sec_nuevo_tipo_anexo_nombre_ac" class="form-control" placeholder="Nombre del tipo de anexo" />
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
				<button type="button" class="btn btn-success" id="sec_nuevo_modal_guardar_archivo" onclick="guardarNuevoTipoAnexo_ac()">
					<i class="icon fa fa-save"></i>
					<span>Guardar nuevo tipo de anexo</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL AGREGAR TIPO ANEXO AC-->

<!-- INICIO MODAL AGREGAR TIPO ANEXO AC -->
<div id="sec_nuevo_con_agregar_nuevo_tipo_archivo_ca" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-sm" role="document" style="width: 30%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Agregar nuevo tipo de Anexo</h4>
			</div>
			<div class="modal-body">
				<form id="sec_con_nuevo_agregar_tipo_anexo_form" name="sec_con_nuevo_agregar_tipo_anexo_form" method="POST" enctype="multipart/form-data" autocomplete="off">
					<div class="row">
					    <div class="col-md-10">
					        <input type="text" name="sec_nuevo_tipo_anexo_nombre_ca" id="sec_nuevo_tipo_anexo_nombre_ca" class="form-control" placeholder="Nombre del tipo de anexo" />
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
				<button type="button" class="btn btn-success" id="sec_nuevo_modal_guardar_archivo" onclick="guardarNuevoTipoAnexo_ca()">
					<i class="icon fa fa-save"></i>
					<span>Guardar nuevo tipo de anexo</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL AGREGAR TIPO ANEXO AC-->

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

						<div class="col-xs-12 col-md-12 col-lg-12">
							<table class="table table-bordered table-striped no-mb" style="font-size:12px">
								<thead>
									<th>Valor</th>
									<th>Tipo Valor</th>
									<th>Continuidad</th>
									<th id="titulo_adenda_incremento_a_partir">A partir del</th>
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
												while($sel=$sel_query->fetch_assoc()){?>
												<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
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
													estado = 1
												ORDER BY nombre ASC
												");
												while($sel=$sel_query->fetch_assoc()){?>
												<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
												<?php
												}
												?>
											</select>
										</td>
										<td id="td_contrato_adenda_incrementos_a_partir_de_año">
											<!-- <select 
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
											</select> -->
											<select class="form-control select2" 
													id="contrato_adenda_incrementos_a_partir_de_año" 
													style="width: 100%; height: 30px;">

												<?php $sel_query = $mysqli->query("SELECT id, nombre
													FROM cont_tipo_anios_incrementos
													WHERE status = 1
													ORDER BY id ASC;");
													while($sel=$sel_query->fetch_assoc()){?>
													<option value="<?php echo  $sel["id"];?>"><?php echo  $sel["nombre"];?></option>

												<?php }?>
												
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
					onclick="sec_contrato_nuevo_arrendamiento_solicitud_guardar_incremento()">
					<i class="icon fa fa-plus"></i>
					<span>Agregar el incremento</span>
				</button>
				<button 
					type="button" 
					class="btn btn-success" 
					id="btn_adenda_guardar_cambios_incremento" 
					onclick="sec_contrato_detalle_solicitud_guardar_cambios_incremento()">
					<i class="icon fa fa-plus"></i>
					<span id="demo-button-text">Guardar cambios</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL AGREGAR INCREMENTOS -->



<!-- INICIO MODAL NUEVO PROPIETARIO AP -->
<div id="modalNuevoProveedor_ap" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_nuevo_proveedor_titulo_ap">Adenda - Nuevo Proveedor</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form autocomplete="off" id="frm_nuevo_proveedor_ap">
						<input type="hidden" id="modal_nuevo_proveedor_ap_contrato_id">
						<input type="hidden" id="modal_nuevo_proveedor_ap_accion">
						<input type="hidden" id="modal_nuevo_propietario_ap_id_anterior">
						
						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">DNI del Representante Legal :</div>
								<input type="text" name="sec_con_ap_dni_representante" id="sec_con_ap_dni_representante" 
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
								<input type="text" name="sec_con_ap_nombre_representante" id="sec_con_ap_nombre_representante" maxlength=50 class="filtro"
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
									name="sec_con_ap_sec_con_nuevo_prov_banco" id="sec_con_ap_sec_con_nuevo_prov_banco" title="Seleccione el banco">
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
								<input type="text" id="sec_con_ap_sec_con_nuev_prov_nro_cuenta" name="sec_con_ap_sec_con_nuev_prov_nro_cuenta" maxlength="50"
								 style="width: 100%; height: 30px;" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
							</div>
						</div>

						<!--NRO CCI-->
						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Nro CCI : </div>
								<input type="text" id="sec_con_ap_sec_con_nuev_prov_nro_cci" name="sec_con_ap_sec_con_nuev_prov_nro_cci" maxlength="50"
								 style="width: 100%; height: 30px;" oninput="this.value=this.value.replace(/[^0-9]/g,'');">
							</div>
						</div>
						<div class="col-xs-12 col-md-6 col-lg-6">
							<div class="form-group">
								<div class="control-label">
									Vigencia
								</div>
								<input type="file" name="sec_con_ap_prov_file_vigencia_nuevo_rl" accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip">
							</div>
						</div>
						<div class="col-xs-12 col-md-6 col-lg-6">
							<div class="form-group">
								<div class="control-label">
									DNI
								</div>
								<input type="file" name="sec_con_ap_prov_file_dni_nuevo_rl" accept=".jpg, .jpeg, .png, .pdf, .doc, .docx, .odt, .ppt, .pptx, .xls, .xlsx, .txt, .7z, .rar, .zip">
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
				<button type="button" onclick="sec_contrato_nuevo_guardar_nuevo_representante_legal_ap()" class="btn btn-success" id="btn_agregar_propietario_ap">
					<i class="icon fa fa-plus"></i>
					Agregar Representante Legal
				</button>
				<button type="button" class="btn btn-success" id="btn_guardar_cambios_propietario_ap">
					<i class="icon fa fa-plus"></i>
					Guardar cambios
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL NUEVO PROPIETARIO AP -->



<!-- INICIO MODAL NUEVO PROPIETARIO AP -->
<div id="modalNuevoContraprestacion_ap" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_nuevo_contraprestacion_titulo_ap">Adenda - Nueva Contraprestacion</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form autocomplete="off" id="frm_nuevo_contraprestacion_ap">
						<input type="hidden" id="modal_nuevo_contraprestacion_ap_contrato_id">
						<input type="hidden" id="modal_nuevo_contraprestacion_ap_accion">
						<input type="hidden" id="modal_nuevo_contraprestacion_ap_id_anterior">
						<div class="col-xs-12 col-md-4 col-lg-6">
							<div class="form-group">
								<div class="control-label">Tipo de Moneda <span class="campo_obligatorio_v2">(*)</span>:</div>
								<select name="modal_cp_moneda_id" id="modal_cp_moneda_id" class="form-control select2" style="width: 100%;">
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-6">
							<div class="form-group">
								<div class="control-label">Monto Bruto <span class="campo_obligatorio_v2">(*)</span>:</div>
								<input type="text" name="modal_cp_monto" id="modal_cp_monto" class="filtro" style="width: 100%; height: 28px;">
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-6">
							<div class="form-group">
								<div class="control-label">IGV (18%):</div>
								<select 
									name="modal_cp_tipo_igv_id" 
									id="modal_cp_tipo_igv_id" 
									class="form-control select2" 
									id="select-default" 
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
								<input type="text" name="modal_cp_subtotal" id="modal_cp_subtotal" class="filtro" style="width: 100%; height: 28px;">
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-6">
							<div class="form-group">
								<div class="control-label" id="label_igv">Monto del IGV :</div>
								<input type="text" name="modal_cp_igv" id="modal_cp_igv" class="filtro" style="width: 100%; height: 28px;">
							</div>
						</div>						

						<div class="col-xs-12 col-md-4 col-lg-6" style="display: none;">
							<div class="form-group">
								<div class="control-label">Forma de Pago <span class="campo_obligatorio_v2">(*)</span>:</div>
								<select class="form-control select2" name="modal_cp_forma_pago" id="modal_cp_forma_pago" style="width: 100%;">
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-6">
							<div class="form-group">
								<div class="control-label">Tipo de Comprobante a Emitir <span class="campo_obligatorio_v2">(*)</span>:</div>
								<select 
									name="modal_cp_tipo_comprobante" 
									id="modal_cp_tipo_comprobante" 
									class="form-control select2" 
									style="width: 100%; height: 28px;">
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-6">
							<div class="form-group">
								<div class="control-label">Plazo de Pago <span class="campo_obligatorio_v2">(*)</span>:</div>
								<input type="text" name="modal_cp_plazo_pago" id="modal_cp_plazo_pago" class="filtro" 
								style="width: 100%; height: 28px;">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="form-group">
								<div class="control-label">Forma de Pago<span class="campo_obligatorio_v2">(*)</span>:</div>
								<textarea name="modal_cp_forma_pago_detallado" id="modal_cp_forma_pago_detallado" rows="3" style="width: 100%;" placeholder="Ingrese  el detalle de la forma de pago"></textarea>
							</div>
						</div>
					</form>
				</div> 
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
				<button type="button" class="btn btn-success" onclick="sec_contrato_nuevo_contraprestacion_ap()" id="btn_agregar_contraprestacion_ap">
					<i class="icon fa fa-plus"></i>
					Agregar Contraprestación
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL NUEVO PROPIETARIO AP -->






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
								<div class="control-label">Periodicidad del ajuste (Ejemplo: 1 año, 6 meses.):  <span class="campo_obligatorio_v2">(*)</span>:</div>
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
								<input type="number"  name="modal_if_tope_inflacion" id="modal_if_tope_inflacion" class="form-control">
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Minimo de Inflación:</div>
								<input type="number" step="any" name="modal_if_minimo_inflacion" id="modal_if_minimo_inflacion" class="form-control text-right">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>
					</form>
				</div> 
			</div>
			<div class="modal-footer">
				<button id="btn_modal_if_agregar_editar" type="button" class="btn btn-success" onclick="sec_contrato_nuevo_editar_inflacion()" >
						<i class="icon fa fa-plus"></i>
						<span id="demo-button-text">Editar Inflación</span>
				</button>
				<button id="btn_modal_if_agregar_agregar" type="button" class="btn btn-success" onclick="sec_contrato_nuevo_agregar_inflacion()" >
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

						<div class="col-xs-12 col-md-6 col-lg-6">
							<div class="form-group">
								<div class="control-label">Mes:</div>
								<select name="modal_ce_mes" id="modal_ce_mes" class="form-control select2" style="width: 100%;">
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-6 col-lg-6">
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
				<button id="btn_modal_ce_agregar_editar" type="button" class="btn btn-success" onclick="sec_contrato_nuevo_editar_cuota_extraordinaria()" >
						<i class="icon fa fa-plus"></i>
						<span id="demo-button-text">Editar Cuota Extraordinaria</span>
				</button>
				<button id="btn_modal_ce_agregar_agregar" type="button" class="btn btn-success" onclick="sec_contrato_nuevo_agregar_cuota_extraordinaria()" >
						<i class="icon fa fa-plus"></i>
						<span id="demo-button-text">Agregar Cuota Extraordinaria</span>
				</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL AGREGAR CUOTA EXTRAORDINARIA -->



<!-- INICIO MODAL AGREGAR CAMBIO DE CUOTA O MONEDA -->
<div id="modalAgregarCambioCuotaMoneda" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_cambio_cuota_moneda_titulo">Registrar Cambio de Cuota o Moneda</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form autocomplete="off" id="frm_inflacion">
						<input type="hidden" name="modal_ccm_cambio_cuota_moneda_id" id="modal_ccm_cambio_cuota_moneda_id" class="form-control">

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Moneda:</div>
								<select name="modal_ccm_moneda_id" id="modal_ccm_moneda_id" class="form-control select2" style="width: 100%;">
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Fecha de decisión:</div>
								<div class="input-group">
									<input name="modal_ccm_fecha_decision" id="modal_ccm_fecha_decision" class="form-control sec_contrato_nuevo_datepicker text-center">
									<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="modal_ccm_fecha_decision"></label>
								</div>
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Fecha de cambio:</div>
								<div class="input-group">
									<input name="modal_ccm_fecha_cambio" id="modal_ccm_fecha_cambio" class="form-control sec_contrato_nuevo_datepicker text-center">
									<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="modal_ccm_fecha_cambio"></label>
								</div>
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Importe:</div>
								<input type="number" step="any" name="modal_ccm_importe" id="modal_ccm_importe" class="form-control">
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Enmienda:</div>
								<select name="modal_ccm_enmienda" id="modal_ccm_enmienda" class="form-control select2" style="width: 100%;">
									<option value="0">- Seleccione -</option>
									<option value="Si">Si</option>
									<option value="No">No</option>
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">¿Inflación?:</div>
								<select name="modal_ccm_inflacion" id="modal_ccm_inflacion" class="form-control select2" style="width: 100%;">
									<option value="0">- Seleccione -</option>
									<option value="Si">Si</option>
									<option value="No">No</option>
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">¿Incrementos de renta?:</div>
								<select name="modal_ccm_incremento_renta" id="modal_ccm_incremento_renta" class="form-control select2" style="width: 100%;">
									<option value="0">- Seleccione -</option>
									<option value="Si">Si</option>
									<option value="No">No</option>
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">¿Cuotas Extraordinaria?:</div>
								<select name="modal_ccm_cuota_extraordinaria" id="modal_ccm_cuota_extraordinaria" class="form-control select2" style="width: 100%;">
									<option value="0">- Seleccione -</option>
									<option value="Si">Si</option>
									<option value="No">No</option>
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
				<button id="btn_modal_ccm_agregar_editar" type="button" class="btn btn-success" onclick="sec_contrato_nuevo_editar_cambio_cuota_moneda()" >
						<i class="icon fa fa-plus"></i>
						<span id="demo-button-text">Editar Cambio de Cuota o Moneda</span>
				</button>
				<button id="btn_modal_ccm_agregar_agregar" type="button" class="btn btn-success" onclick="sec_contrato_nuevo_agregar_cambio_cuota_moneda()" >
						<i class="icon fa fa-plus"></i>
						<span id="demo-button-text">Agregar Cambio de Cuota o Moneda</span>
				</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL AGREGAR CAMBIO DE CUOTA O MONEDA -->


<!-- INICIO MODAL AGREGAR CAMBIO DE CUOTA O MONEDA -->
<div id="modalAgregarTerminacionRenovacion" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_terminacion_renovacion_titulo">Registrar Terminación y Renovación</h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<form autocomplete="off" id="frm_inflacion">
						<input type="hidden" name="modal_tr_terminacion_renovacion_id" id="modal_tr_terminacion_renovacion_id" class="form-control">

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Tipo:</div>
								<select name="modal_tr_tipo" id="modal_tr_tipo" class="form-control select2" style="width: 100%;">
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Esta incluido en el contrato?:</div>
								<select name="modal_tr_incluido" id="modal_tr_incluido" class="form-control select2" style="width: 100%;">
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">La Ejecución depende unicamente del arrendatario?:</div>
								<select name="modal_tr_ejecucion_arrendatario" id="modal_tr_ejecucion_arrendatario" class="form-control select2" style="width: 100%;">
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">La Gerencia ejecutará la opción?:</div>
								<select name="modal_tr_gerencia_ejecutara" id="modal_tr_gerencia_ejecutara" class="form-control select2" style="width: 100%;">
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Fecha en la que gerencia decide ejecutar la opción:</div>
								<div class="input-group">
									<input readonly name="modal_tr_fecha_ejecucion_gerencia" id="modal_tr_fecha_ejecucion_gerencia" class="form-control sec_contrato_nuevo_datepicker text-center">
									<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="modal_tr_fecha_ejecucion_gerencia"></label>
								</div>
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Nueva fecha de vencimiento del contrato:</div>
								<div class="input-group">
									<input readonly name="modal_tr_nueva_fecha_vencimiento_contrato" id="modal_tr_nueva_fecha_vencimiento_contrato" class="form-control sec_contrato_nuevo_datepicker text-center">
									<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="modal_tr_nueva_fecha_vencimiento_contrato"></label>
								</div>
							</div>
						</div>



						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Importe de la penalización por ejecutar la opción:</div>
								<input type="number" step="any" name="modal_tr_importe_penalizacion" id="modal_tr_importe_penalizacion" class="form-control">
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Periodo de pago de la penalización:</div>
								<input type="text" name="modal_tr_periodo_penalizacion" id="modal_tr_periodo_penalizacion" class="form-control">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>
					</form>
				</div> 
			</div>
			<div class="modal-footer">
				<button id="btn_modal_tr_agregar_editar" type="button" class="btn btn-success" onclick="sec_contrato_nuevo_editar_terminacion_renovacion()" >
						<i class="icon fa fa-plus"></i>
						<span id="demo-button-text">Editar Terminación y Renovación</span>
				</button>
				<button id="btn_modal_tr_agregar_agregar" type="button" class="btn btn-success" onclick="sec_contrato_nuevo_agregar_terminacion_renovacion()" >
						<i class="icon fa fa-plus"></i>
						<span id="demo-button-text">Agregar Terminación y Renovación</span>
				</button>
				<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL AGREGAR CAMBIO DE CUOTA O MONEDA -->


<?php } ?>