<?php 
global $mysqli;
$menu_id = "";
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'contrato' AND sub_sec_id = 'agente' LIMIT 1");
while ($r = $result->fetch_assoc())
	$menu_id = $r["id"];

if (!array_key_exists($menu_id, $usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])) 
{
	echo "No tienes permisos para acceder a este recurso";
	//die;
}
else
{

	$date_now = date('d-m-Y');
	$fecha_inicio = strtotime('-30 day', strtotime($date_now));
	$fecha_inicio = date('d-m-Y', $fecha_inicio);
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
				<h1 class="page-title titulosec_contrato"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> 	Contratos Agentes firmados</h1>
			</div>
		</div>
	</div>

	<div class="page-header wide">
		<div class="row mt-4 mb-2">
			<fieldset class="dhhBorder">
				<legend class="dhhBorder">Búsqueda</legend>
				<form autocomplete="off">

					<div class="form-group col-lg-3 col-md-4 col-sm-6 col-xs-12" style="height: 55px;">
						<label>Empresa:</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<select class="form-control input_text select2 change-contrato-agentes-firmado"
									data-live-search="true"
									id="empresa_id"
									style="width: 100%">
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
								<a class="btn btn-xs btn-default limpiar_select2" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="empresa_id" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>
					
					<div class="form-group col-lg-3 col-md-4 col-sm-6 col-xs-12" style="height: 55px;">
						<label>Nombre del agente:</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<input type="text" class="form-control" id="nombre_agente" placeholder="Nombre del agente" style="border: 1px solid #aaa; border-radius: 1px;">
								<a class="btn btn-xs btn-default limpiar_input" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="nombre_agente" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>

					<div class="form-group col-lg-3 col-md-4 col-sm-3 col-xs-12" style="height: 55px;">
						<label>Centro de costos:</label>
						<div class="form-group">
							<div class="input-group col-xs-12">					 
								<input type="text" class="form-control" id="search_centro_costos_agente" placeholder="Centro de costos" style="border: 1px solid #aaa; border-radius: 1px;">
								<a class="btn btn-xs btn-default limpiar_input" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="search_centro_costos_agente" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>

					<div class="form-group col-lg-3 col-md-4 col-sm-3 col-xs-12" style="height: 55px;">
						<label>Departamento:</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<select
									class="form-control input_text select2 change-contrato-agentes-firmado"
									data-live-search="true" 
									onchange="sec_contrato_agente_obtener_provincias()"
									name="search_id_departamento" 
									id="search_id_departamento">
									<option value="">-- TODOS --</option>
								</select>
								<a class="btn btn-xs btn-default limpiar_select2" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="search_id_departamento" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>
					
					<div class="form-group col-lg-3 col-md-4 col-sm-3 col-xs-12" style="height: 55px;">
						<label>Provincia:</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<select class="form-control input_text select2 change-contrato-agentes-firmado"
									data-live-search="true" 
									onchange="sec_contrato_agente_obtener_distritos()"
									name="search_id_provincia" 
									id="search_id_provincia">
									<option value="">-- TODOS --</option>
								</select>
								<a class="btn btn-xs btn-default limpiar_select2" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="search_id_provincia" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>
					
					<div class="form-group col-lg-3 col-md-4 col-sm-3 col-xs-12" style="height: 55px;">
						<label>Distrito:</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<select class="form-control input_text select2 change-contrato-agentes-firmado"
									data-live-search="true" 
									name="search_id_distrito" 
									id="search_id_distrito">
									<option value="">-- TODOS --</option>
								</select>
								<a class="btn btn-xs btn-default limpiar_select2" style="display: table-cell; border-color: #aaa; color: #777; width: 32px;" limpiar="search_id_distrito" title="Restablecer filtro"><i class="icon fa fa-close"></i></a>
							</div>
						</div>
					</div>

					<div class="form-group col-lg-3 col-md-3 col-sm-6 col-xs-12" style="height: 55px;">
						<label>
							F. Solicitud desde:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<input
									type="text"
									class="form-control cont_agente_datepicker"
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

					<div class="form-group col-lg-3 col-md-3 col-sm-6 col-xs-12" style="height: 55px;">
						<label>
							F. Solicitud hasta:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<input
									type="text"
									class="form-control cont_agente_datepicker"
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

					<div class="form-group col-lg-3 col-md-3 col-sm-6 col-xs-12" style="height: 55px;">
						<label>
							F. Inicio desde:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<input
									type="text"
									class="form-control cont_agente_datepicker"
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

					<div class="form-group col-lg-3 col-md-3 col-sm-6 col-xs-12" style="height: 55px;">
						<label>
							F. Inicio hasta:
						</label>
						<div class="form-group">
							<div class="input-group col-xs-12">
								<input
									type="text"
									class="form-control cont_agente_datepicker"
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

					<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 text-right" style="margin-top: 15px; margin-bottom: 15px; float: right;">
						<button type="button" class="btn btn-warning float-left" id="btn_limpiar_filtros_de_busqueda">
							<i class="fa fa-eraser"></i>
							Limpiar filtros
						</button>
						<span id="cont_interno_excel" class="float-left" style="padding: inherit;"></span>
						<button type="button" class="btn btn-success" id="cont_agente_btn_export_agente">
							<span class="fa fa-file-excel-o"></span>
							Exportar excel
						</button>
						<button type="button" class="btn btn-success float-left" onclick="cont_agente_buscar_por_parametros();">
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
					
				</div>
			</div>
		</div>
	</div>

	<div class="row mt-3" id="cont_contrato_agente_div_tabla" style="display: none;">
		<table id="cont_agente_datatable" class="table table-bordered table-hover dt-responsive" cellspacing="0" width="100%">
		<thead>
			<tr>
				<th scope="col">Cód</th>
				<th scope="col">CC</th>
				<th scope="col">Agente</th>
				<th scope="col">Solicitante</th>
				<th scope="col">Suscribe</th>
				<th scope="col">RUC</th> 
				<th scope="col">F. Solicitud</th> 
				<th scope="col">F. Suscripción</th> 
				<th scope="col">F. Inicio</th> 
				<th scope="col">Etapa</th>
				<th scope="col">Detalle</th>
				<th scope="col">Alerta</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
		<tfoot>
			<tr>
				<th scope="col">Cód</th>
				<th scope="col">CC</th>
				<th scope="col">Agente</th>
				<th scope="col">Solicitante</th>
				<th scope="col">Suscribe</th>
				<th scope="col">RUC</th> 
				<th scope="col">F. Solicitud</th> 
				<th scope="col">F. Suscripción</th> 
				<th scope="col">F. Inicio</th> 
				<th scope="col">Etapa</th>
				<th scope="col">Detalle</th>
				<th scope="col">Alerta</th>
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
							<th class="text-center">Nombre del agente</th>
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
						<input type="text" class="form-control input-sm" name="num_dias" id="num_dias" maxlength="3" min="0" max="999" onkeypress="sec_contrato_agente_validar_solo_numeros(event)">
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
				<button type="button" class="btn btn-success" onclick="sec_contrato_agente_registrar_alerta();">
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