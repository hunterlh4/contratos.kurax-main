<?php
global $mysqli;
$menu_id = "";
$area_id = $login ? $login['area_id'] : 0;
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'contrato' AND sub_sec_id = 'solicitud' LIMIT 1");
while ($r = $result->fetch_assoc())
	$menu_id = $r["id"];


$usuario_id = $login?$login['id']:null;
$permiso_solicitud = in_array("nuevo_contrato_agente", $usuario_permisos[$menu_id]);

if (!($area_id == 2 || $area_id == 3  || $area_id == 6 || $area_id == 33 || $permiso_solicitud) ) {
	echo "No tienes permisos para acceder a este recurso";
}else {

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
</style>
<script type="text/javascript">
     function anular(e) {
          tecla = (document.all) ? e.keyCode : e.which;
          return (tecla != 13);
     }
	</script>

<div id="div_sec_contrato_nuevo_agente">

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
			<h1 class="page-title titulosec_contrato"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Nueva Solicitud - Contrato de Agente</h1>
		</div>
	</div>

	<div class="row">
		<div class="col-xs-12 col-md-12 col-lg-12">
			<!-- PANEL: Tipo contrato -->
			<div class="panel">
				<div class="panel-heading">
					<div class="panel-title">Nueva Solicitud <span class="campo_obligatorio_v2">(*) Campos obligatorios</span></div>
				</div>
				<div class="panel-body">
					<form>
						<input type="hidden" id="usuario_id_temporal" value="<?php echo $usuario_id;?>">
   
						<div class="col-xs-12 col-md-3" style="display: none;">
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

						<div class="col-xs-12 col-md-4">
							<div class="form-group">
								<div class="control-label">
								Empresa que suscribe <span class="campo_obligatorio_v2">(*)</span>:</div>	 

								<select
									class="form-control input_text select2"
									data-live-search="true" 
									data-col="empresa_suscribe_ag" 
									name="empresa_suscribe_ag" 
									id="empresa_suscribe_ag" 
									title="Seleccione una opción">
								</select>
								
							</div>
						</div>

						<div class="col-xs-12 col-md-4">
							<div class="form-group">
								<div class="control-label">
								Agente y Centro de Costos<span class="campo_obligatorio_v2">(*)</span>:</div>	 

								<select
									class="form-control input_text select2"
									data-live-search="true" 
									data-col="nombre_ag_lc" 
									data-table="tbl_locales"
									name="nombre_ag_lc" 
									id="nombre_ag_lc" 
									title="Seleccione una opción">
								</select>
								
							</div>
						</div>

						
						<div class="col-xs-12 col-md-4 col-lg-4" id="div_personal_responsable" style="display: none;">
							<div class="form-group">
								<label for="exampleInputEmail1"> Supervisor <span class="campo_obligatorio_v2">(*)</span></label>
								<select
									class="form-control input_text select2"
									name="personal_responsable_id" 
									id="personal_responsable_id" 
									title="Seleccione un supervisor">
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4 pt-2">
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
										name="aprobador_id" 
										id="aprobador_id" 
										title="Seleccione a el director">
									</select>
								</div>

							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4 pt-2">
							<div class="form-group">
								<div class="control-label">Cargo del Aprobador:</div>
								<select 
										class="form-control input_text select2"
										name="cargo_aprobador_id" 
										id="cargo_aprobador_id" 
										title="Seleccione a el director">
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4 pt-2" id="div_correos_adicionales" style="display: none;">
							<div class="form-group">
									<div class="control-label">Correos Adjuntos: (Opcional)</div>
									<textarea cols="50" rows="2"					 
									name="correos_adjuntos_ad"
									id="correos_adjuntos_ad"></textarea>
									<p><b>Nota: Para más de un correo se debe separar por comas (,)</b></p>
								</div>
								 
						</div>
						 
					</form>
				</div>
				<br>
			 
			</div>
			<!-- /PANEL: Tipo contrato -->


			<!-- PANEL: Contrato de Agente -->
			<div class="panel" id="divContratoAgente" style="display: none;">
				<div class="panel-heading">
					<div class="panel-title">FICHA DE CONDICIONES DE CONTRATO DE AGENTE</div>
				</div>
				<div class="panel-body">
					<form onkeypress="return anular(event)" id="form_contrato_agente" name="form_contrato_agente" method="POST" enctype="multipart/form-data" autocomplete="off">
						<div class="col-xs-12 col-md-6 col-lg-6">
							<div class="h4"><b>DATOS DEL PROPIETARIO<span class="campo_obligatorio_v2">(*)</span></b></div>
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
							<div id="divTablaPropietarios_ca" class="form-group">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>

						<div class="col-xs-12 col-md-6 col-lg-12">
							<div class="h4"><b>DATOS DEL AGENTE</b></div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-3">
							<div class="form-group">
								<div class="control-label">Departamento<span class="campo_obligatorio_v2">(*)</span>:</div>
								<select
									class="form-control input_text select2"
									data-live-search="true" 
									data-col="area_id" 
									data-table="tbl_areas"
									name="modal_inmueble_id_departamento_ca" 
									id="modal_inmueble_id_departamento_ca" 
									title="Seleccione el departamento">
								</select>
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-3">
							<div class="form-group">
								<div class="control-label">Provincia<span class="campo_obligatorio_v2">(*)</span>:</div>
								<select class="form-control input_text select2"
									data-live-search="true" 
									data-col="personal_id" 
									data-table="tbl_personal"
									name="modal_inmueble_id_provincia_ca" 
									id="modal_inmueble_id_provincia_ca" 
									title="Seleccione el tipo de Contrato">
								</select>
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-3">
							<div class="form-group">
								<div class="control-label">Distrito<span class="campo_obligatorio_v2">(*)</span>:</div>
								<select class="form-control input_text select2"
									data-live-search="true" 
									data-col="personal_id" 
									data-table="tbl_personal"
									name="modal_inmueble_id_distrito_ca" 
									id="modal_inmueble_id_distrito_ca" 
									title="Seleccione el tipo de Contrato">
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-9">
							<div class="form-group">
								<div class="control-label">Dirección:<span class="campo_obligatorio_v2">(*)</span>:</div>
								<input type="text" id="modal_inmueble_ubicacion_ca" name="modal_inmueble_ubicacion_ca" 
								placeholder ="Ejemplo: Av nombre con Calle nombre 0001"
								class="form-control" maxlength="100" style="height: 30px;">
							</div>
						</div>
						

						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>


						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="h4"><b>CONDICIONES COMERCIALES</b></div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Tipo:</div>
								<input type="text" id="participacion_id_bet" 
								value="BETSHOP"
								class="filtro" style="width: 100%; height: 30px; float: left; text-align: left;" readonly>
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Porcentaje de Participación<span class="campo_obligatorio_v2">(*)</span>:</div>
								<input type="number" step="any"		 
								name="porcentaje_participacion_bet"
								id="porcentaje_participacion_bet" class="filtro formato_porcentaje" style="width: 100%; height: 30px; float: left; text-align: left;">
							</div>
						</div>
						
				

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Condición de Participación<span class="campo_obligatorio_v2">(*)</span>:</div>
								<select
									class="form-control input_text select2"
									data-live-search="true" 
									data-col="personal_id" 
									data-table="tbl_personal"
									name="condicion_comercial_id_bet" 
									id="condicion_comercial_id_bet" 
									title="Seleccione la condición de participación">
									<option value="0">- Seleccione -</option>
									<?php
									$sel_query = $mysqli->query("SELECT *
									FROM cont_condiciones_comerciales
									WHERE status = 1 
									ORDER BY id ASC;");
									while($sel=$sel_query->fetch_assoc()){
										if ($sel["id"]=='1'){?>
											<option value="<?php echo $sel["id"];?>" 
											selected="selected"	> <?php echo $sel["nombre"];?></option>
											<?php
										}
										else{?>
											<option value="<?php echo $sel["id"];?>"> <?php echo $sel["nombre"];?></option>
											<?php 
										}
										 
									}
									?>
								</select>
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>

						
						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Tipo:</div>
								<input type="text" step="any" id="participacion_id_jv" 
								value="JUEGOS VIRTUALES"
								class="filtro" style="width: 100%; height: 30px; float: left; text-align: left;" readonly>
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Porcentaje de Participación<span class="campo_obligatorio_v2">(*)</span>:</div>
								<input type="number" step="any" 
								name="porcentaje_participacion_j"
								id="porcentaje_participacion_j" class="filtro formato_porcentaje" style="width: 100%; height: 30px; float: left; text-align: left;">
							</div>
						</div>
						
				

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Condición de Participación<span class="campo_obligatorio_v2">(*)</span>:</div>
								<select
									class="form-control input_text select2"
									data-live-search="true" 
									data-col="personal_id" 
									data-table="tbl_personal"
									name="condicion_comercial_id_jv" 
									id="condicion_comercial_id_jv" 
									title="Seleccione la condición de participación">
									<option value="0">- Seleccione -</option>
									<?php
									$sel_query = $mysqli->query("SELECT *
									FROM cont_condiciones_comerciales
									WHERE status = 1 
									ORDER BY id ASC;");
									while($sel=$sel_query->fetch_assoc()){
										if ($sel["id"]=='1'){?>
											<option value="<?php echo $sel["id"];?>" 
											selected="selected"	> <?php echo $sel["nombre"];?></option>
											<?php
										}
										else{?>
											<option value="<?php echo $sel["id"];?>"> <?php echo $sel["nombre"];?></option>
											<?php 
										}
										 
									}
									?>
								</select>
							</div>
						</div>


						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>


						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Tipo:</div>
								<input type="text" step="any" id="participacion_id_t" 
								value="TERMINALES"
								class="filtro" style="width: 100%; height: 30px; float: left; text-align: left;" readonly>
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Porcentaje de Participación<span class="campo_obligatorio_v2">(*)</span>:</div>
								<input type="number" step="any"					 
								name="porcentaje_participacion_ter"
								id="porcentaje_participacion_ter" class="filtro formato_porcentaje" style="width: 100%; height: 30px; float: left; text-align: left;">
							</div>
						</div>
						
				

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Condición de Participación<span class="campo_obligatorio_v2">(*)</span>:</div>
								<select
									class="form-control input_text select2"
									data-live-search="true" 
									data-col="personal_id" 
									data-table="tbl_personal"
									name="condicion_comercial_id_t" 
									id="condicion_comercial_id_t" 
									title="Seleccione la condición de participación">
									<option value="0">- Seleccione -</option>
									<?php
									$sel_query = $mysqli->query("SELECT *
									FROM cont_condiciones_comerciales
									WHERE status = 1 
									ORDER BY id ASC;");
									while($sel=$sel_query->fetch_assoc()){
										if ($sel["id"]=='1'){?>
											<option value="<?php echo $sel["id"];?>" 
											selected="selected"	> <?php echo $sel["nombre"];?></option>
											<?php
										}
										else{?>
											<option value="<?php echo $sel["id"];?>"> <?php echo $sel["nombre"];?></option>
											<?php 
										}
										 
									}
									?>
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>


						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Tipo:</div>
								<input type="text" step="any" id="participacion_id_b" 
								value="BINGO"
								class="filtro" style="width: 100%; height: 30px; float: left; text-align: left;" readonly>
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Porcentaje de Participación<span class="campo_obligatorio_v2">(*)</span>:</div>
								<input type="number" step="any"  
								name="porcentaje_participacion_bin" 
								id="porcentaje_participacion_bin" class="filtro formato_porcentaje" style="width: 100%; height: 30px; float: left; text-align: left;">
							</div>
						</div>
						
				

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Condición de Participación<span class="campo_obligatorio_v2">(*)</span>:</div>
								<select
									class="form-control input_text select2"
									data-live-search="true" 
									data-col="personal_id" 
									data-table="tbl_personal"
									name="condicion_comercial_id_b" 
									id="condicion_comercial_id_b" 
									title="Seleccione la condición de participación">
									<option value="0">- Seleccione -</option>
									<?php
									$sel_query = $mysqli->query("SELECT *
									FROM cont_condiciones_comerciales
									WHERE status = 1 
									ORDER BY id ASC;");
									while($sel=$sel_query->fetch_assoc()){
										if ($sel["id"]=='2'){?>
											<option value="<?php echo $sel["id"];?>" 
											selected="selected"	> <?php echo $sel["nombre"];?></option>
											<?php
										}
										else{?>
											<option value="<?php echo $sel["id"];?>"> <?php echo $sel["nombre"];?></option>
											<?php 
										}
										 
									}
									?>
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>


						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Tipo:</div>
								<input type="text" step="any" id="participacion_id_dw" 
								value="DEPOSITO WEB"
								class="filtro" style="width: 100%; height: 30px; float: left; text-align: left;" readonly>
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Porcentaje de Participación<span class="campo_obligatorio_v2">(*)</span>:</div>
								<input type="number" step="any" 
								name="porcentaje_participacion_dep"
								id="porcentaje_participacion_dep" class="filtro formato_porcentaje" style="width: 100%; height: 30px; float: left; text-align: left;">
							</div>
						</div>
						
				

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Condición de Participación<span class="campo_obligatorio_v2">(*)</span>:</div>
								<select
									class="form-control input_text select2"
									data-live-search="true" 
									data-col="personal_id" 
									data-table="tbl_personal"
									name="condicion_comercial_id_dw" 
									id="condicion_comercial_id_dw" 
									title="Seleccione la condición de participación">
									<option value="0">- Seleccione -</option>
									<?php
									$sel_query = $mysqli->query("SELECT *
									FROM cont_condiciones_comerciales
									WHERE status = 1 
									ORDER BY id ASC;");
									while($sel=$sel_query->fetch_assoc()){
										if ($sel["id"]=='3'){?>
											<option value="<?php echo $sel["id"];?>" 
											selected="selected"	> <?php echo $sel["nombre"];?></option>
											<?php
										}
										else{?>
											<option value="<?php echo $sel["id"];?>"> <?php echo $sel["nombre"];?></option>
											<?php 
										}
										 
									}
									?>
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Tipo:</div>
								<input type="text" step="any" id="participacion_id_ccv" 
								value="CARRERAS DE CABALLO EN VIVO"
								class="filtro" style="width: 100%; height: 30px; float: left; text-align: left;" readonly>
							</div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Porcentaje de Participación<span class="campo_obligatorio_v2">(*)</span>:</div>
								<input type="number" step="any" 
								name="porcentaje_participacion_ccv"
								id="porcentaje_participacion_ccv" class="filtro formato_porcentaje" style="width: 100%; height: 30px; float: left; text-align: left;">
							</div>
						</div>
						
				

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Condición de Participación<span class="campo_obligatorio_v2">(*)</span>:</div>
								<select
									class="form-control input_text select2"
									data-live-search="true" 
									data-col="personal_id" 
									data-table="tbl_personal"
									name="condicion_comercial_id_ccv" 
									id="condicion_comercial_id_ccv" 
									title="Seleccione la condición de participación">
									<option value="0">- Seleccione -</option>
									<?php
									$sel_query = $mysqli->query("SELECT *
									FROM cont_condiciones_comerciales
									WHERE status = 1 
									ORDER BY id ASC;");
									while($sel=$sel_query->fetch_assoc()){
										if ($sel["id"]=='1'){?>
											<option value="<?php echo $sel["id"];?>" 
											selected="selected"	> <?php echo $sel["nombre"];?></option>
											<?php
										}
										else{?>
											<option value="<?php echo $sel["id"];?>"> <?php echo $sel["nombre"];?></option>
											<?php 
										}
										 
									}
									?>
								</select>
							</div>
						</div>


						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>

						<!-- <div class="col-xs-12 col-md-12 col-lg-12">
							<div class="h4"><b>BIENES ENTREGADOS</b></div>
						</div>

						<div class="col-xs-12 col-md-3 col-lg-3">
							<div class="form-group">
								<div class="control-label">Bienes Entregados:</div>
								<select
									class="form-control input_text"
									data-live-search="true" 
									name="bien_entregado" 
									id="bien_entregado" 
									title="Seleccione el tipo de comercio">
									<option value="">- Seleccione -</option>
									<option value="SI">SI</option>
									<option value="NO">NO</option>
								</select>
							</div>
						</div> 

						<div class="col-xs-12 col-md-12 col-lg-12" id="div_detalle_bien" style="display:none">
							<div class="form-group">
								<div class="control-label">Detalle:</div>
								<textarea id="detalle_bien_entradado" class="form-control" rows="3" cols="50" style="width: 100%;"></textarea>
							</div>
						</div> -->

				

						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<div class="h4"><b>PLAZO</b></div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Periodo (Ejemplo: 1 año, 6 meses.)  <span class="campo_obligatorio_v2">(*)</span>:</div>
								<input type="number"  id="periodo_numero_ca" name="periodo_numero_ca" required  class="filtro" style="width: 40%; height: 30px; float: left;">

								<select 
									class="form-control" 
									name="periodo_ca" 
									id="periodo_ca" 
									required 
									style="width: 60%; height: 30px; float: left;">
									<option value="0">Seleccione el período</option>
									<option selected value="1">Año(s)</option>
									<option value="2">Mes(es)</option>
								</select>
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>
						
						<div id="div_nombre_local" style="display:none" >
							 
							

								
						

								<div class="col-xs-12 col-md-12 col-lg-12">
									<br>
								</div>

							</div>

 
						

								<div class="col-xs-12 col-md-12 col-lg-12">
									<br>
								</div>


								<div class="col-xs-12 col-md-12 col-lg-12">
									<div class="h4"><b>FECHA DE SUSCRIPCIÓN DEL CONTRATO</b></div>
								</div>


								<div class="col-xs-12 col-md-4 col-lg-4">
									<div class="form-group">
										<div class="control-label">Fecha de suscripción del contrato<span class="campo_obligatorio_v2">(*)</span>:</div>
										<div class="input-group">
											<input
													type="text"
													name="fecha_contrato_ag"
													id="fecha_contrato_ag"
													class="form-control fecha_datepicker_ag"
													value="<?php echo date("Y-m-d", strtotime("now"));?>"
													readonly="readonly"
													style="height: 30px;"
													>
											<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="fecha_contrato_ag"></label>
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
								<textarea id="contrato_ag_observaciones" name="contrato_ag_observaciones"  rows="3" cols="50" style="width: 100%;"  onkeypress="return event.charCode != 39"></textarea>
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
								<input type="file" name="archivo_partida_inmueble" accept=".jpg, .jpeg, .png, .pdf">
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Licencia de Funcionamiento:</div>
								<input type="file" name="archivo_licencia_funcionamiento" accept=".jpg, .jpeg, .png, .pdf">
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Ficha RUC del Agente:</div>
								<input type="file" name="archivo_ruc_agente" accept=".jpg, .jpeg, .png, .pdf">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">DNI del Agente:</div>
								<input type="file" name="archivo_dni_agente" accept=".jpg, .jpeg, .png, .pdf">
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">Vigencia de poder (Si es empresa):</div>
								<input type="file" name="archivo_vigencia_poder" accept=".jpg, .jpeg, .png, .pdf">
							</div>
						</div>

						<div class="col-xs-12 col-md-4 col-lg-4">
							<div class="form-group">
								<div class="control-label">DNI de representante legal (Si es empresa):</div>
								<input type="file" name="archivo_dni_representante_legal" accept=".jpg, .jpeg, .png, .pdf">
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
								<button type="button" class="btn btn-info btn-sm" id="sec_nuevo_btn_agregar_anexos" onclick="sec_con_nuevo_abrir_modal_tipos_anexos_ca()">
									<i class="icon fa fa-plus"></i>
									Agregar otros anexos
								</button>
					 
								
							</div>
						</div> 
					 
						<div class="col-xs-12 col-md-4 col-lg-4">
				            <div id="archivos" style="width:300px">
				                
				            </div>
						</div>	
						<div class="col-md-12" id="sec_nuevos_files_prueba">
							<!--<input id="fileToUploadAnexo" accept="application/pdf, image/png, image/jpeg" type="file" multiple="" style="display:none">-->
						</div> 
					 	
						<div class="col-md-12" id="sec_nuevo_nuevos_anexos_listado_ca">
							<input id="fileToUploadAnexo" accept="application/pdf, image/png, image/jpeg" type="file" multiple="" style="display:none">
						</div> 
						<br/>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12">
							<br>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12">
							<button type="button" class="btn btn-success btn-block" id="boton_guardar_contrato_agente">
								<i class="icon fa fa-save" id="demo-button-icon-left" style="display: inline-block;"></i>
								<span id="demo-button-text">Enviar Solicitud Contrato de Agente</span>
							</button>
						</div>
					</form>
				</div>
			</div>
			<!-- /PANEL: Contrato de Agente -->
		</div>
 

 
	</div>
</div> 


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

						<input type="hidden" id="modal_buscar_propietario_tipo_solicitud_ca">

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
									<button type="button" class="btn btn-success btn-sm btn-block" id="btnBuscarPropietario_ca" onclick="sec_contrato_nuevo_agente_buscar_propietario()">
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
					<form autocomplete="off" id="frm_nuevo_propietario_ca" onkeypress="return anular(event)">
						<input type="hidden" id="modal_nuevo_propietario_tipo_solicitud_ca">
						<input type="hidden" id="modal_propietaria_id_persona_para_cambios_ca">
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Tipo de Persona<span class="campo_obligatorio_v2">(*)</span>:</div>
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
								<div class="control-label">Nombre / Razón Social del propietario<span class="campo_obligatorio_v2">(*)</span>:</div>
								<input type="text" id="modal_propietario_nombre_ca" name="modal_propietario_nombre" class="filtro txt_filter_style" maxlength="50" style="width: 100%; height: 30px;">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Tipo de documento de identidad<span class="campo_obligatorio_v2">(*)</span>:</div>
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
								<input type="number" id="modal_propietario_num_docu_ca" name="modal_propietario_num_docu" class="filtro mask_dni_agente txt_filter_style" maxlength="11" style="width: 100%; height: 30px;">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_num_ruc_propietario_ca">
							<div class="form-group">
								<div class="control-label">Número de RUC del propietario<span class="campo_obligatorio_v2">(*)</span>:</div>
								<input type="number" id="modal_propietario_num_ruc_ca" name="modal_propietario_num_ruc" class="filtro mask_ruc_agente txt_filter_style" maxlength="11" style="width: 100%; height: 30px;">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter">
							<div class="form-group">
								<div class="control-label">Domicilio del propietario<span class="campo_obligatorio_v2">(*)</span>:</div>
								<input type="text" id="modal_propietario_direccion_ca" name="modal_propietario_direccion" class="filtro txt_filter_style" maxlength="100" style="width: 100%; height: 30px;" autocomplete="off">
							</div>
						</div>
						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_propietario_representante_legal_ca" style="display: none;">
							<div class="form-group">
								<div class="control-label">Representante Legal<span class="campo_obligatorio_v2">(*)</span>:</div>
								<input type="text" id="modal_propietario_representante_legal_ca" class="filtro txt_filter_style" maxlength="50" style="width: 100%; height: 30px;">
							</div>
						</div>

						<div class="col-xs-12 col-md-12 col-lg-12 item_filter" id="div_modal_propietario_num_partida_registral_ca" style="display: none;">
							<div class="form-group">
								<div class="control-label">N° Partida Registral de la empresa<span class="campo_obligatorio_v2">(*)</span>:</div>
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
								<div class="control-label">Teléfono de la persona de contacto<span class="campo_obligatorio_v2">(*)</span>:</div>
								<input type="number" id="modal_propietario_contacto_telefono_ca" name="modal_propietario_contacto_telefono" class="filtro mask_telefono_agente txt_filter_style" maxlength="9" style="width: 100%; height: 30px;" autocomplete="off">
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
 

<!-- INICIO MODAL TIPOS ANEXOS CONTRATO DE AGENTES-->
<div id="modaltiposanexos_ca" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-sm" role="document" style="width: 30%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Selecciona tipo de Anexo</h4>
			</div>
			<div class="modal-body">
				<form id="sec_nuevo_form_modal_nuevo_anexo_ca"  onkeypress="return anular(event)" name="sec_nuevo_form_modal_nuevo_anexo_ca" method="POST" enctype="multipart/form-data" autocomplete="off">
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
 


<!-- INICIO MODAL AGREGAR TIPO ANEXO AC -->
<div id="sec_nuevo_con_agregar_nuevo_tipo_archivo_ca" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-sm" role="document" style="width: 30%;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title">Agregar nuevo tipo de Anexo</h4>
			</div>
			<div class="modal-body">
				<form id="sec_con_nuevo_agregar_tipo_anexo_form"  onkeypress="return anular(event)" name="sec_con_nuevo_agregar_tipo_anexo_form" method="POST" enctype="multipart/form-data" autocomplete="off">
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

<?php 
}
?>