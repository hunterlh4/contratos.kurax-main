<div class="content container-fluid cuadro_de_ventas_form_contrato contenedor_general_sec_recaudacion_filtros"> <div class="page-header wide">
		<div class="row">
			<div class="col-xs-12 text-center">
				<h1 class="page-title"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Consolidado Agentes</h1>
			</div>
		</div>
	</div>
</div>
<div class="row mt-4">
	<div class="col-xs-12 container_filtros_recaudacion">
		<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
			<p class="text-center">Local</p>
			<select id="sec_consolidado_agentes_local" class="sec_consolidado_agentes_select2" name="sec_consolidado_agentes_local" multiple="true" style="width:100%;">
			    <option value="all">Todos</option>
			</select>
		</div>
		<div class="col-xs-12 col-sm-6 col-md-4 col-lg-3">
			<p class="text-center">Agente</p>
			<select id="sec_consolidado_agentes_agente" class="select" multiple="true" style="width:100%;">
			    <option value="all">Todos</option>
			</select>
		</div>
		<div class="col-xs-12 col-sm-6 col-md-4 col-lg-2">
			<p class="text-center">Canal de venta</p>
			<select id="sec_consolidado_agentes_cdv" class="sec_consolidado_agentes_select2" multiple="true" style="width:100%;">
			    <option value="all">Todos</option>
			    <option value="16">PBET</option>
			    <option value="17">SBT-Negocios</option>
			    <option value="21">JV Global Bet</option>
			    <option value="30">Bingo</option>
			    <option value="34">Hipica</option>
			    <option value="42">Kurax MVR</option>
			    <option value="43">Kurax SBT</option>
			</select>
		</div>
		<div class="col-xs-12 col-sm-6 col-md-6 col-lg-2">
			<p class="text-center">Concepto</p>
			<select id="sec_consolidado_agentes_concepto" class="sec_consolidado_agentes_concepto" style="width:100%;">
			    <option value="APOSTADO">APOSTADO</option>
			    <option value="PAGADO">PAGADO</option>
			    <option value="GANADO">GANADO</option>
			    <option value="PRODUCCIÓN">PRODUCCIÓN</option>
			    <option value="PARTICIPACIÓN OAT">PARTICIPACIÓN OAT</option>
			    <option value="PARTICIPACIÓN AGENTE">PARTICIPACIÓN AGENTE</option>
			</select>
		</div>
		<div class="col-lg-2 col-md-6 col-md-6 col-xs-12 text-right" style="margin-bottom: 10px">
			<button type="button" value="1" class="filtarconsolidado btnfiltarconsolidado btn btn-primary col-xs-12" data-button="request" data-toggle="tooltip" data-placement="top" title="Filtrar Consolidado"><span class="glyphicon glyphicon-search"></span> Consultar</button>
		</div>
		<div class="col-lg-12 col-xs-12 text-right">
			<button id="sec_consolidado_agentes_estado_locales" class="btn btn-danger ">Mostrar Inactivos</button>
		</div>
	</div>
</div>
<div class="row mt-4">
	<div class="col-xs-12">
		<div class="table-responsive">
			<table id="tabla_sec_consolidado_agente"
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