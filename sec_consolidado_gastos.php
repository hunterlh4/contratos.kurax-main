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
<div class="content container-fluid"> 
	<div class="page-header wide">
		<div class="row">
			<div class="col-xs-12 text-center">
				<h1 class="page-title"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Consolidado Gastos - Free Games</h1>
			</div>
		</div>
	</div>

	<div class="page-header wide">
		<div class="row mt-4 mb-2">
			<fieldset class="dhhBorder">
				<legend class="dhhBorder">Búsqueda</legend>

					<div class="col-xs-12 container_filtros_recaudacion">
						<div class="form-group col-lg-3 col-md-4 col-sm-6 col-xs-12">
							<p class="text-center">Zona</p>
							<select 
									id="search_id_consolidado_zona" 
									class="sec_consolidado_gastos_select2" 
									name="search_id_consolidado_zona" 
									onchange="sec_consolidado_gastos_obtener_supervisor_por_zona()"
									multiple="true"
									style="width:100%;">
							</select>
						</div>
						<div class="form-group col-lg-3 col-md-4 col-sm-6 col-xs-12">
							<p class="text-center">Supervisor</p>
							<select 
									id="search_id_consolidado_supervisor" 
									name="search_id_consolidado_supervisor"
									onchange="sec_consolidado_gastos_obtener_locales_por_supervisor()" 
									class="sec_consolidado_gastos_select2" 
									multiple="true" 
									style="width:100%;">
							</select>
						</div>
						<div class="form-group col-lg-3 col-md-4 col-sm-6 col-xs-12">
							<p class="text-center">Local</p>
							<select 
									id="search_id_consolidado_locales" 
									class="sec_consolidado_gastos_select2" 
									name="search_id_consolidado_locales" 
									multiple="true" 
									style="width:100%;">

							</select>
						</div>
						<div class="form-group col-lg-3 col-md-4 col-sm-6 col-xs-12">
							<p class="text-center">Concepto</p>
							<select 
								id="sec_consolidado_gastos_cdv" 
								class="sec_consolidado_gastos_select2" 
								name="search_id_consolidado_conceptos"
								multiple="true" style="width:100%;">
							</select>
						</div>
					<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 text-right" style="margin-top: 15px; margin-bottom: 15px; float: right;">

						<div class="btn-group btn-group-separators" style="margin-top: 25px">
							<button type="button" value="1" class="filtarconsolidado btnfiltarconsolidadoGastos btn btn-primary col-sm-12" data-button="request" data-toggle="tooltip" data-placement="top" title="Filtrar Consolidado"><span class="glyphicon glyphicon-search"></span> Consultar</button>
						</div>
						
						<div class="btn-group btn-group-separators" style="margin-top: 25px">
							<button id="sec_consolidado_gastos_cargar_datos" class="btn btn-success col-sm-12"><span
								class="glyphicon glyphicon-import"></span>Importar</button>
						</div>
						<div class="btn-group btn-group-separators" style="margin-top: 25px">
							<a onclick="sec_consolidado_gastos_exportar_formato();" class="btn btn-warning col-sm-12" data-button="request" data-toggle="tooltip" data-placement="top" title="Exportar formato para importar"><span
								class="glyphicon glyphicon-export"></span>Exportar Formato</a>
						</div>
						</div>
					</div>
				</fieldset>
		</div>
</div>
</div>

<style>
	.DTFC_LeftBodyLiner{
		overflow-y: hidden!important;
	}	
</style>
<div class="row mt-4" style="overflow-x: auto;">
	<div class="col-xs-12" style="min-width: 1200px">
		<div class="table-responsive">
			<table id="tabla_sec_consolidado_gastos_provincia"
			cellspacing="0"
			width="100%"
			class="table table-hover table-condensed table-bordered">
			<thead style="background-color:#fff !important; color:#fff !important; border-bottom:1px solid #ddd !important;">
			</thead>
			<tbody>
				</tbody>
				<tfoot>
					</tfoot>
				</table>
		</div>
	</div>
</div>

<!-- INICIO MODAL SUBIR DATA -->
<div id="modalCargarDataConsolidadoGastos" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title"> IMPORTAR DATA DE CONSOLIDADO DE GASTOS </h4>
			</div>
			<div class="modal-body">
                
                <form method="POST" id="Frm_ImportarConsolidadoGastos" enctype="multipart/form-data" autocomplete="off">

					<div class="row">
						<div class="col-md-12 mt-3">
							<label for="fileInput" id="fileInputLabel">Seleccionar Archivo Excel</label>
							<input type="file" id="fileInput" name="excelFile" accept=".xls, .xlsx" />
						</div>
					</div>

						<label></label>
						<div class="modal-footer">
							<div class="col-md-2">
								<button type="submit" class="btn form-control btn-info">Cargar Data</button>
							</div>
						</div>
                    </form>
            
			</div>
		</div>
	</div>
</div>