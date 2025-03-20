<?php 
global $mysqli;
$menu_id = "";
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'contrato' AND sub_sec_id = 'interno' LIMIT 1");
while ($r = $result->fetch_assoc())
	$menu_id = $r["id"];

if (!array_key_exists($menu_id, $usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])) 
{
	echo "No tienes permisos para acceder a este recurso";
	//die;
}
else
{
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
				<h1 class="page-title titulosec_contrato"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> 	Contratos Internos firmados</h1>
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
							Empresa AT 1:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<select
									class="form-control input_text select2 change-contrato-interno-firmado"
									data-live-search="true" 
									name="sec_con_int_empresa_1" 
									id="sec_con_int_empresa_1">
									<option value="">-- TODOS --</option>
									<?php
										$sel_query = $mysqli->query(
										"
										SELECT 
										    id, nombre
										FROM
										    tbl_razon_social
										WHERE status = 1
										ORDER BY nombre ASC
										");

										while($sel=$sel_query->fetch_assoc())
										{
											?>
											<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
											<?php
										}
									?>
								</select>
								<a class="btn btn-xs btn-default limpiar_select2" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="sec_con_int_empresa_1" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>

					<div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
						<label>
							Empresa AT 2:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<select
									class="form-control input_text select2 change-contrato-interno-firmado"
									data-live-search="true" 
									name="sec_con_int_empresa_2" 
									id="sec_con_int_empresa_2">
									<option value="">-- TODOS --</option>
									<?php
										$sel_query = $mysqli->query(
										"
										SELECT 
										    id, nombre
										FROM
										    tbl_razon_social
										WHERE status = 1
										ORDER BY nombre ASC
										");

										while($sel=$sel_query->fetch_assoc())
										{
											?>
											<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
											<?php
										}
									?>
								</select>
								<a class="btn btn-xs btn-default limpiar_select2" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="sec_con_int_empresa_2" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
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
									class="form-control input_text select2 change-contrato-interno-firmado"
									data-live-search="true" 
									name="sec_con_int_area" 
									id="sec_con_int_area">
									
									<?php

										$area_id = $login['area_id'];

									?>
										<option value="">-- TODOS --</option>
									<?php

										$sel_query = $mysqli->query(
										"
											SELECT 
												id,
												nombre
											FROM 
												tbl_areas
											WHERE 
												estado = 1 
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
								<a class="btn btn-xs btn-default limpiar_select2" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="sec_con_int_area" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
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
									class="form-control cont_interno_datepicker"
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
									class="form-control cont_interno_datepicker"
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
									class="form-control cont_interno_datepicker"
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
									class="form-control cont_interno_datepicker"
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
								<select class="form-control select2 change-contrato-interno-firmado" name="director_aprobacion_id" id="director_aprobacion_id" title="Seleccione a el director">
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
								<input type="text" class="form-control sec_contrato_solicitud_datepicker" id="search_fecha_inicio_aprobacion" value="" readonly="readonly" style="border: 1px solid #aaa; border-radius: 1px; min-height: 28px !important;">
								<a class="btn btn-xs btn-default" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" onclick="document.getElementById('search_fecha_inicio_aprobacion').click();"><i class="icon fa fa-calendar"></i></a>
								<a class="btn btn-xs btn-default limpiar_input" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="search_fecha_inicio_aprobacion" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>

					<div class="form-search-fecha_aprobacion_hasta col-lg-3 col-md-6 col-sm-12 col-xs-12" style="height: 55px;">
						<label>
							F. Aprobación hasta:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<input type="text" class="form-control sec_contrato_solicitud_datepicker" id="search_fecha_fin_aprobacion" value="" readonly="readonly" style="border: 1px solid #aaa; border-radius: 1px; min-height: 28px !important;">
								<a class="btn btn-xs btn-default" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" onclick="document.getElementById('search_fecha_fin_aprobacion').click();"><i class="icon fa fa-calendar"></i></a>
								<a class="btn btn-xs btn-default limpiar_input" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="search_fecha_fin_aprobacion" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>

					<div class="col-md-6 col-md-offset-6 text-right" style="padding: 10px 0px 10px 0px;">
						<button type="button" class="btn btn-warning float-left" id="btn_limpiar_filtros_de_busqueda">
							<i class="fa fa-eraser"></i>
							Limpiar filtros
						</button>
						<span id="cont_interno_excel" class="float-left" style="padding: inherit;"></span>
						<button type="button" class="btn btn-success float-left" onclick="sec_con_int_buscar();">
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

	<div class="page-header wide" id="div_proveedor_boton_export" style="display: none;">
		<div class="row mt-3 mb-2">
			<div class="row form-horizontal">
				<div class="col-md-12" id="cont_proveedor_exportar_boton_excel_proveedor">					
					<button class="btn btn-success btn-sm" id="cont_proveedor_btn_export_proveedor">
						<span class="glyphicon glyphicon-export"></span>
						Exportar excel
					</button>
				</div>
			</div>
		</div>
	</div>

	<div class="row mt-3 table-responsive" id="cont_contrato_interno_div_tabla">
		<table id="cont_interno_datatable" class="table table-bordered table-hover" cellspacing="0" width="100%">
		<thead>
			<tr>
				<th class="text-center">Cod</th>
				<th class="text-center">Area Solicitante</th>
				<th class="text-center">Solicitante</th>
				<th class="text-center">Empresa Grupo AT 1</th>
				<th class="text-center">Empresa Grupo AT 2</th>
				<th class="text-center">Fecha Inicio</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
		<tfoot>
			<tr>
				<th class="text-center">Cod</th>
				<th class="text-center">Area Solicitante</th>
				<th class="text-center">Solicitante</th>
				<th class="text-center">Empresa Grupo AT 1</th>
				<th class="text-center">Empresa Grupo AT 2</th>
				<th class="text-center">Fecha Inicio</th>
			</tr>
		</tfoot>
	   </table>
	</div>
</div>
<?php 
}
?>