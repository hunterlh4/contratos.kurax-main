
<?php  

	$select_estado = array(
		array("id" => "1", "Nombre" => "Activo"),
		array("id" => "0", "Nombre" => "Inactivo")
	);

	$select_local_tienda = $mysqli->query("SELECT contrato_id, nombre_tienda
							FROM cont_contrato
							WHERE tipo_contrato_id = 1
							AND status = 1
							AND etapa_id = 5
							ORDER BY nombre_tienda ASC");
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
				<h1 class="page-title titulosec_contrato"><i class="icon icon-inline fa fa-fw fa-file"></i> 
					Archivos Licencias Municipales
				</h1>
			</div>
		</div>
	</div>

	<div class="row">
		<div class="row mt-4 mb-2">
			<fieldset class="dhhBorder">
				<legend class="dhhBorder">Búsqueda</legend>
				
				<div class="row">
					<div class="form-group col-lg-4 col-md-4 col-sm-6 col-xs-12">
						<label>
							Local:
						</label>
						<select id="cont_licencia_municipal_select_tienda" name="cont_licencia_municipal_select_tienda" class="form-control select2">
							<option value="0">Todos</option>
							<?php foreach ($select_local_tienda as $item): ?>
								<option value="<?php echo $item["contrato_id"] ?>"><?php echo $item["nombre_tienda"]; ?></option>
							<?php endforeach ?>
						</select>
					</div>

					<div class="col-lg-2 col-xs-12">
						<button type="button" name="contrato_licenciasFile_filtro_btn_buscar" value="1" class="btn_filtrar_reporte_venta_general_tienda btn btn-primary" data-button="request" data-toggle="tooltip" data-placement="top" 
						title="Consultar" onclick="buscarArchivosLicenciasMunicipalesLocales();">
							<i class="glyphicon glyphicon-search"></i>
							Consultar
						</button>
					</div>

				</div>

			</fieldset>
		</div>
	</div>

	<div class="row mt-3" id="cont_contrato_div_tabla" style="display: none;">
		<table id="cont_locales_licenciafile_datatable" class="table-responsive table table-striped table-hover table-condensed table-bordered dt-responsive display" cellspacing="0" width="100%">
		<thead>			
			<tr>
				<th scope="col">Tienda</th>
				<th scope="col">Funcionamiento</th>
				<th scope="col">Indeci</th>
				<th scope="col">Anuncio</th>
				<th scope="col">Declaracion Jurada</th>
			</tr>
			
		</thead>
		<tbody>
			
		</tbody>
		<tfoot>
			<th scope="col">Tienda</th>
				<th scope="col">Funcionamiento</th>
				<th scope="col">Indeci</th>
				<th scope="col">Anuncio</th>
				<th scope="col">Declaracion Jurada</th>
		</tfoot>
	</table>
	</div>
	
</div>


<!-- INICIO LECTOR PANTALLA COMPLETA -->
<div class="modal fade right" id="exampleModalPreviewServicio" tabindex="-1" role="dialog" aria-labelledby="exampleModalPreviewLabel" aria-hidden="true" style="width: 100%; padding-left: 0px;">
    <div class="modal-dialog-full-width modal-dialog momodel modal-fluid" role="document" style="width: 100%; margin: 10px auto;">
        <div class="modal-content-full-width modal-content " style="background-color: rgb(0 0 0 / 0%) !important;">
            <div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center;margin-top: 5px; margin-bottom:5px;">
              <button type="button" class="btn btn-block btn-danger" data-dismiss="modal"><i class="fa fa-close"></i> Cerrar Visor</button>
            </div>
            <div class="modal-body" style="padding: 0px;" id="cont_licienciafileDivVisorPdfModal">
            </div>
            <div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center;margin-top: 5px; margin-bottom: 5px;">
              <button type="button" class="btn btn-block btn-danger" data-dismiss="modal"><i class="fa fa-close"></i> Cerrar Visor</button>   
            </div>
            <div class="col-xs-12 col-md-12 col-sm-12"></div>
        </div>
    </div>
</div>
<!-- FIN LECTOR PANTALLA COMPLETA -->
