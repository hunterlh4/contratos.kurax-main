<?php
global $mysqli;
$menu_id = "";
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'marketing' AND sub_sec_id = 'nuevo' LIMIT 1");
while ($r = $result->fetch_assoc())
	$menu_id = $r["id"];
	$usuario_id = $login?$login['id']:null;
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
		<div class="col-xs-12 col-sm-12 col-md-12">
			<div class="alert alert-success alert-dismissible fade in" role="alert" style="margin: 0px;">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close">
					<span aria-hidden="true">×</span>
				</button>
				<p class="text-center">
					<?php 
					$ultima_modificacion = "";
					$query_updated = $mysqli->query("SELECT updated_at FROM tbl_modificaciones WHERE status = 1 AND modulo = 'Marketing' ORDER BY updated_at DESC LIMIT 1");
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
			<h1 class="page-title titulosec_contrato"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Marketing - Nueva Solicitud</h1>
		</div>
	</div>

	<div class="row">
		<div class="col-xs-12 col-md-12 col-lg-12">
			<!-- PANEL: Tipo contrato -->
			<div class="panel">
				<div class="panel-heading">
					<div class="panel-title">Nuevo Solicitud <span class="campo_obligatorio_v2">(*) <small>Campos Obligatorios</small></span></div>
				</div>
				<div class="panel-body no-pad">
					<form id="form_marketing_requerimiento_nuevo" name="form_marketing_requerimiento_nuevo" method="POST" enctype="multipart/form-data" autocomplete="off">
						<input type="hidden" name="usuario_id_temporal" id="usuario_id_temporal" value="<?php echo $usuario_id;?>">
					
						<div class="row">
							<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4">
								<div class="form-group">
									<label for="sec_mkt_nuevo_area_id">Área <span class="campo_obligatorio_v2">(*)</span>:</label>
									<select class="form-control select2" name="sec_mkt_nuevo_area_id" id="sec_mkt_nuevo_area_id" 
										title="Seleccione la empresa grupo AT 1">
									</select>
								</div>
							</div>

							<div class="col-xs-12 col-sm-6 col-md-4 col-lg-4 mt-2">
								<div class="form-group">
									<label for="sec_mkt_nuevo_producto_id">Producto <span class="campo_obligatorio_v2">(*)</span>:</label>
									<select class="form-control select2" name="sec_mkt_nuevo_producto_id" id="sec_mkt_nuevo_producto_id" 
										title="Seleccione la empresa grupo AT 1">
									</select>
								</div>
							</div>

							<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4 mt-2">
								<div class="form-group">
									<label for="sec_mkt_nuevo_tipo_solicitud_id">Solicitud <span class="campo_obligatorio_v2">(*)</span>:</label>
									<select class="form-control select2" name="sec_mkt_nuevo_tipo_solicitud_id" id="sec_mkt_nuevo_tipo_solicitud_id" 
										title="Seleccione la empresa grupo AT 1">
									</select>
								</div>
							</div>


							<div class="col-xs-12 col-md-12 col-lg-12 mt-2">
								<div class="form-group">
									<label for="sec_mkt_nuevo_objetivo">Objetivo: <span class="campo_obligatorio_v2">(*)</span>:</label><br>
									<small>*Definir claramente el objetivo principal del requerimiento. (Ej. Incrementar el Tk promedio, Incrementar las ventas en un +%) </small>
									<textarea name="sec_mkt_nuevo_objetivo" id="sec_mkt_nuevo_objetivo"  rows="5" cols="50" class="form-control form-control-rounded"></textarea>
								</div>
							</div>

							<div class="col-xs-12 col-md-12 col-lg-12 mt-2">
								<div class="form-group">
									<label for="">Bullet: <span class="campo_obligatorio_v2">(minimo 3 * - maximo 5)</span>:</label><br>
									<small>*Son frases cortas con información relevante a colocarse en la publicidad y que impactarán positivamente en la decisión de compra del cosumidor.</small>
									<input name="sec_mkt_nuevo_bullet_1" id="sec_mkt_nuevo_bullet_1" class="form-control form-control-rounded mt-2" placeholder="bullet 1 Obligatorio" type="text">
									<input name="sec_mkt_nuevo_bullet_2" id="sec_mkt_nuevo_bullet_2" class="form-control form-control-rounded mt-2" placeholder="bullet 2 Obligatorio" type="text">
									<input name="sec_mkt_nuevo_bullet_3" id="sec_mkt_nuevo_bullet_3" class="form-control form-control-rounded mt-2" placeholder="bullet 3 Obligatorio" type="text">
									<input name="sec_mkt_nuevo_bullet_4" id="sec_mkt_nuevo_bullet_4" class="form-control form-control-rounded mt-2" placeholder="bullet 4 Opcional" type="text">
									<input name="sec_mkt_nuevo_bullet_5" id="sec_mkt_nuevo_bullet_5" class="form-control form-control-rounded mt-2" placeholder="bullet 5 Opcional" type="text">
								</div>
							</div>
		
							<div class="col-xs-12 col-md-4 col-lg-4 mt-2">
								<div class="form-group">
									<label >Requerimiento Estratégico: <span class="campo_obligatorio_v2">(*)</span>:</label><br>
									<small>*Se podrá seleccionar hasta máximo 8 requerimientos.</small>
									<p><select name="sec_mkt_nuevo_req_estrategico_1" id="sec_mkt_nuevo_req_estrategico_1" class="form-control select2"></select></p>
									<p><select name="sec_mkt_nuevo_req_estrategico_2" id="sec_mkt_nuevo_req_estrategico_2" class="form-control select2"></select></p>
									<p><select name="sec_mkt_nuevo_req_estrategico_3" id="sec_mkt_nuevo_req_estrategico_3" class="form-control select2"></select></p>
									<p><select name="sec_mkt_nuevo_req_estrategico_4" id="sec_mkt_nuevo_req_estrategico_4" class="form-control select2"></select></p>
									<p><select name="sec_mkt_nuevo_req_estrategico_5" id="sec_mkt_nuevo_req_estrategico_5" class="form-control select2"></select></p>
									<p><select name="sec_mkt_nuevo_req_estrategico_6" id="sec_mkt_nuevo_req_estrategico_6" class="form-control select2"></select></p>
									<p><select name="sec_mkt_nuevo_req_estrategico_7" id="sec_mkt_nuevo_req_estrategico_7" class="form-control select2"></select></p>
									<p><select name="sec_mkt_nuevo_req_estrategico_8" id="sec_mkt_nuevo_req_estrategico_8" class="form-control select2"></select></p>
								</div>
							</div>

							<div class="col-xs-12 col-md-8 col-lg-8 mt-2">
								<div class="form-group">
									<label for="sec_mkt_nuevo_sustento_req_estrategico">Sustento Requerimiento Estratégico: <span class="campo_obligatorio_v2">(*)</span>:</label><br>
									<small>*Se deberá sustentar claramente el porqué de la selección de cada requerimiento estratégico. </small>
									<textarea class="form-control form-control-rounded" name="sec_mkt_nuevo_sustento_req_estrategico" id="sec_mkt_nuevo_sustento_req_estrategico" cols="30" rows="12"></textarea>
								</div>
							</div>

							<div class="col-xs-12 col-md-12 col-lg-12 text-center">
								<br>
							 <strong class="campo_obligatorio_v2">Para el caso de promociones, el requerimiento deberá de ingresar con un plazo mínimo de 25 días útiles antes de su lanzamiento</strong>
							 	<br><br>
							</div>
							
						</div>

						<div class="row">
							<div class="col-xs-12 col-md-12 col-lg-12">
								<button type="submit" class="btn btn-block btn-rounded btn-success mt-1" id="guardar_contrato_proveedor">
									<i class="icon fa fa-save" id="demo-button-icon-left" style="display: inline-block;"></i>
									<span id="demo-button-text">Registrar Solicitud</span>
								</button>
							</div>
						</div>
					</form>
				</div>
			</div>
			<!-- /PANEL: Tipo contrato -->
		</div>
	</div>
</div>

