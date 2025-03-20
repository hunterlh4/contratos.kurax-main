<?php 
global $mysqli;
$menu_id = "";
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'contrato' AND sub_sec_id = 'arrendamiento' LIMIT 1");
while ($r = $result->fetch_assoc())
	$menu_id = $r["id"];

if (!array_key_exists($menu_id, $usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])) 
{
	echo "No tienes permisos para acceder a este recurso";
	//die;
}
else
{

	$area_id = $login ? $login['area_id'] : 0;
	$user_id = $login?$login['id']:null;
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

</style>

<div class="content container-fluid contratos_form_etapas">
	<div class="page-header wide">
		<div class="row">
			<div class="col-xs-12 text-center">
				<h1 class="page-title titulosec_contrato"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> 	Contratos Arrendamientos Aprobados</h1>
			</div>
		</div>
	</div>

	<div class="page-header wide">
		<div class="row mt-4 mb-2">
			<fieldset class="dhhBorder">
				<legend class="dhhBorder">Búsqueda</legend>
				<form autocomplete="off">
					<div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
						<label>
							Empresa:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<select
									class="form-control input_text select2 change-contrato-arrendamiento-firmado"
									data-live-search="true" 
									name="cont_arrendamiento_param_empresa" 
									id="cont_arrendamiento_param_empresa">
									<option value="">-- TODOS --</option>
									<?php

										$query_empresa = "";
										if($login["usuario_locales"]){
											$query_empresa = "SELECT l.razon_social_id as id, r.nombre FROM tbl_locales AS l 
											INNER JOIN tbl_razon_social AS r ON r.id = l.razon_social_id
											WHERE l.estado = 1 AND r.status = 1 
											AND l.id IN (".implode(",", $login["usuario_locales"]).")
											GROUP BY l.razon_social_id 
											ORDER BY l.nombre ASC;";
											
										}else{
											$query_empresa = "SELECT id, nombre FROM tbl_razon_social WHERE status = 1 ORDER BY nombre ASC";
										}

										$sel_query = $mysqli->query($query_empresa);

										while($sel=$sel_query->fetch_assoc())
										{
											?>
											<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
											<?php
										}
									?>
								</select>
								<a class="btn btn-xs btn-default limpiar_select2" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="cont_arrendamiento_param_empresa" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>

					<div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
						<label>
							Área Solicitante:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<select
									class="form-control input_text select2 change-contrato-arrendamiento-firmado"
									data-live-search="true" 
									name="cont_arrendamiento_param_area_solicitante" 
									id="cont_arrendamiento_param_area_solicitante">
									<option value="">-- TODOS --</option>
									<?php

										$area_id = $login['area_id'];

										if($area_id == 33 || $area_id == 6 || ( array_key_exists($menu_id,$usuario_permisos) && in_array("see_all", $usuario_permisos[$menu_id]) ) ) // 33:legal 6:sistemas
										{
											$usuario_area_id = "" ;
											?>
												<!-- <option value="">-- TODOS --</option> -->
											<?php
										}
										else
										{
											$usuario_area_id = " AND id = $area_id";	
										}
											

										$sel_query = $mysqli->query(
										"
											SELECT 
												id,
												nombre
											FROM 
												tbl_areas
											WHERE 
												estado = 1 
												$usuario_area_id
											ORDER BY nombre ASC
										");

										while($sel=$sel_query->fetch_assoc())
										{
											$select_area = '';

											if ($area_id == 6 && $sel["id"] == 6) {
												$select_area = 'selected';
											}
											?>
											<option value="<?php echo $sel["id"];?>" <?php echo $select_area;?> ><?php echo $sel["nombre"];?></option>
											<?php
										}
									?>
								</select>
								<a class="btn btn-xs btn-default limpiar_select2" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="cont_arrendamiento_param_area_solicitante" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>

					<div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
						<label>
							RUC Proveedor:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<input type="text" name="cont_arrendamiento_param_ruc" class="form-control txt_filter_style cont_arrendamiento_param_ruc" id="cont_arrendamiento_param_ruc" placeholder="RUC" maxlength="11" style="border: 1px solid #aaa; border-radius: 1px;">
								<a class="btn btn-xs btn-default limpiar_input" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="cont_arrendamiento_param_ruc" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>

					<div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
						<label>
							Razón Social del Proveedor:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<input type="text" name="cont_arrendamiento_param_razon_social" class="form-control txt_filter_style cont_arrendamiento_param_razon_social" id="cont_arrendamiento_param_razon_social" placeholder="Razón Social" style="border: 1px solid #aaa; border-radius: 1px;">
								<a class="btn btn-xs btn-default limpiar_input" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="cont_arrendamiento_param_razon_social" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>

					<div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
						<label>
							Moneda:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<select
									class="form-control input_text select2 change-contrato-arrendamiento-firmado"
									data-live-search="true" 
									name="cont_arrendamiento_param_moneda" 
									id="cont_arrendamiento_param_moneda">
									<option value="">-- TODOS --</option>
									<?php
										$sel_query = $mysqli->query(
										"
											SELECT 
												id, CONCAT(nombre,' (',simbolo,')') AS nombre
											FROM tbl_moneda
											WHERE estado = 1 AND id IN(1,2)
											ORDER BY id ASC
										");

										while($sel=$sel_query->fetch_assoc())
										{
											?>
											<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
											<?php
										}
									?>
								</select>
								<a class="btn btn-xs btn-default limpiar_select2" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="cont_arrendamiento_param_moneda" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>

					<div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
						<label>
							F. Solicitud desde:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<input
									type="text"
									class="form-control cont_arrendamiento_datepicker"
									id="fecha_inicio_solicitud"
									value=""
									readonly="readonly"
									style="border: 1px solid #aaa; border-radius: 1px; min-height: 28px !important;"
									>
									<a class="btn btn-xs btn-default" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" onclick="document.getElementById('fecha_inicio_solicitud').click();"><i class="icon fa fa-calendar"></i></a>
									<a class="btn btn-xs btn-default limpiar_input" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="fecha_inicio_solicitud" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>

							</div>
						</div>
					</div>

					<div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
						<label>
							F. Solicitud hasta:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<input
									type="text"
									class="form-control cont_arrendamiento_datepicker"
									id="fecha_fin_solicitud"
									value=""
									readonly="readonly"
									style="border: 1px solid #aaa; border-radius: 1px; min-height: 28px !important;"
									>
									<a class="btn btn-xs btn-default" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" onclick="document.getElementById('fecha_fin_solicitud').click();"><i class="icon fa fa-calendar"></i></a>
									<a class="btn btn-xs btn-default limpiar_input" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="fecha_fin_solicitud" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>

					<div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
						<label>
							F. Inicio desde:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<input
									type="text"
									class="form-control cont_arrendamiento_datepicker"
									id="fecha_inicio_inicio"
									value=""
									readonly="readonly"
									style="border: 1px solid #aaa; border-radius: 1px; min-height: 28px !important;"
									>
								<a class="btn btn-xs btn-default" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" onclick="document.getElementById('fecha_inicio_inicio').click();"><i class="icon fa fa-calendar"></i></a>
								<a class="btn btn-xs btn-default limpiar_input" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="fecha_inicio_inicio" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>

					<div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
						<label>
							F. Inicio hasta:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<input
									type="text"
									class="form-control cont_arrendamiento_datepicker"
									id="fecha_fin_inicio"
									value=""
									readonly="readonly"
									style="border: 1px solid #aaa; border-radius: 1px; min-height: 28px !important;"
									>
								<a class="btn btn-xs btn-default" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" onclick="document.getElementById('fecha_fin_inicio').click();"><i class="icon fa fa-calendar"></i></a>
								<a class="btn btn-xs btn-default limpiar_input" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="fecha_fin_inicio" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>
					<div class="form-search-aprobante col-lg-3 col-md-3 col-sm-3 col-xs-12" style="height: 55px;">
						<label>
							Aprobante:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<select class="form-control select2 change-contrato-arrendamiento-firmado" name="director_aprobacion_id" id="director_aprobacion_id" title="Seleccione a el director">
								</select>
								<a class="btn btn-xs btn-default limpiar_select2" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="director_aprobacion_id" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>

				
					<div class="form-search-fecha_aprobacion_desde col-lg-3 col-md-6 col-sm-12 col-xs-12" style="height: 55px;">
						<label>
							F. Aprobación desde:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<input type="text" class="form-control sec_contrato_solicitud_datepicker" id="search_fecha_inicio_aprobacion_firmado" value="" readonly="readonly" style="border: 1px solid #aaa; border-radius: 1px; min-height: 28px !important;">
								<a class="btn btn-xs btn-default" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" onclick="document.getElementById('search_fecha_inicio_aprobacion_firmado').click();"><i class="icon fa fa-calendar"></i></a>
								<a class="btn btn-xs btn-default limpiar_input" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="search_fecha_inicio_aprobacion_firmado" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>

					<div class="form-search-fecha_aprobacion_hasta col-lg-3 col-md-6 col-sm-12 col-xs-12" style="height: 55px;">
						<label>
							F. Aprobación hasta:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<input type="text" class="form-control sec_contrato_solicitud_datepicker" id="search_fecha_fin_aprobacion_firmado" value="" readonly="readonly" style="border: 1px solid #aaa; border-radius: 1px; min-height: 28px !important;">
								<a class="btn btn-xs btn-default" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" onclick="document.getElementById('search_fecha_fin_aprobacion_firmado').click();"><i class="icon fa fa-calendar"></i></a>
								<a class="btn btn-xs btn-default limpiar_input" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="search_fecha_fin_aprobacion_firmado" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>

					<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 text-right" style="margin-top: 15px; margin-bottom: 15px; float: right;">
						<button type="button" class="btn btn-warning float-left" id="btn_limpiar_filtros_de_busqueda">
							<i class="fa fa-eraser"></i>
							Limpiar filtros
						</button>
						<button type="button" class="btn btn-success float-left" id="cont_arrendamiento_btn_export_arrendamiento">
							<i class="fa fa-file-excel-o"></i>
							Exportar excel
						</button>
						<button type="button" class="btn btn-primary float-left" onclick="cont_arrendamiento_buscar_arrendamiento_por_parametros();">
							<i class="glyphicon glyphicon-search"></i>
							Buscar
						</button>
					</div>

					<div class="col-md-12" id="cont_locales_alerta_filtrar_por">
									
					</div>
				</form>
			</fieldset>
		</div>
	</div>

	<div class="row mt-3 table-responsive" id="cont_contrato_arrendamiento_div_tabla" style="display: none; font-size: 11px;">
		<table id="cont_locales_arrendamiento_datatable" class="table table-bordered table-hover table-condensed" cellspacing="0" width="100%">
		<thead>
			<tr>
				<th scope="col" class="text-center">Cód</th>
				<th scope="col" class="text-center">Área solicitante</th>
				<th scope="col" class="text-center">Solicitante</th>
				<th scope="col" class="text-center">Suscribe</th>
				<th scope="col" class="text-center">RUC Proveedor</th>
				<th scope="col" class="text-center">Razón Social</th>
				<th scope="col" class="text-center">Moneda</th>
				<th scope="col" class="text-center">F. Solicitud</th>
				<th scope="col" class="text-center">F. Inicio</th>
				<th scope="col" class="text-center">F. Fin</th>
				<th scope="col" class="text-center">Estado <br>Contractual</th>
				<th scope="col" class="text-center">Categoría</th>
				<th scope="col" class="text-center">Tipo Contrato</th>
				<th scope="col" class="text-center">Aprobante</th>
				<th scope="col" class="text-center">F. Aprobación</th>
				<th scope="col" class="text-center">Detalle</th>
				<th scope="col" class="text-center">Alerta</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
		<tfoot>
			<tr>
				<th scope="col" class="text-center">Cód</th>
				<th scope="col" class="text-center">Área solicitante</th>
				<th scope="col" class="text-center">Solicitante</th>
				<th scope="col" class="text-center">Suscribe</th>
				<th scope="col" class="text-center">RUC Proveedor</th>
				<th scope="col" class="text-center">Razón Social</th>
				<th scope="col" class="text-center">Moneda</th>
				<th scope="col" class="text-center">F. Solicitud</th>
				<th scope="col" class="text-center">F. Inicio</th>
				<th scope="col" class="text-center">F. Fin</th>
				<th scope="col" class="text-center">Estado <br>Contractual</th>
				<th scope="col" class="text-center">Categoría</th>
				<th scope="col" class="text-center">Tipo Contrato</th>
				<th scope="col" class="text-center">Aprobante</th>
				<th scope="col" class="text-center">F. Aprobación</th>
				<th scope="col" class="text-center">Detalle</th>
				<th scope="col" class="text-center">Alerta</th>
			</tr>
		</tfoot>
	   </table>
	</div>
</div>

<!-- INICIO MODAL ALERTA CONTRATO -->
<div class="modal fade" id="modal_alertas" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h2 class="modal-title text-center">
					<strong>Registro de alerta</strong>
				</h2>
			</div>
			<div class="modal-body">

				<table id="tabla_datos_alerta" class="table table-striped table-bordered table-responsive">
					<input type="hidden" name="contrato_id_temp" id="contrato_id_temp">
					<thead>
						<tr>
							<th class="text-center">Proveedor</th>
							<th class="text-center">Fecha inicio</th>
							<th class="text-center">Fecha fin</th>
							<th class="text-center">Alerta</th>
						</tr>
					</thead>
					<tbody id="contenido_modal_alerta">
						
					</tbody>
				</table>
				<div>
					<div class="col-md-2" style="text-align: right; padding: 0;">
						<label>Notificar</label>	
					</div>

					<div class="col-md-2" style="padding-bottom: 5px;">
						<input type="text" class="form-control input-sm" name="num_dias" id="num_dias" maxlength="3" min="0" max="999" onkeypress="sec_contrato_arrendamiento_validar_solo_numeros(event)">
					</div>

					<div class="col-md-8" style="text-align: left; padding: 0;">
						<label>días antes de finalizar el contrato.</label>
					</div>
				</div>
				<div class="col-md-12" id="divMensajeAlerta">
					
				</div>

			</div>
			<br>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" data-dismiss="modal">
					<i class="glyphicon glyphicon-remove-sign"></i>
					Cancelar
				</button>
				<button type="button" class="btn btn-success" onclick="sec_contrato_arrendamiento_registrar_alerta();">
				<i class="glyphicon glyphicon-saved" ></i>
				Registrar alerta
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL ALERTA CONTRATO -->
<?php 
}
?>