<?php
global $mysqli;
$menu_id = "";
$continuar = false;
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'contrato' AND sub_sec_id = 'provision' LIMIT 1");

while ($r = $result->fetch_assoc())
	$menu_id = $r["id"];

if (!array_key_exists($menu_id, $usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])) {
	echo "No tienes permisos para acceder a este recurso";
} else {
	$continuar = true;
}

if($continuar === true){
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
				<h1 class="page-title titulosec_contrato">
					<i class="icon icon-inline fa fa-fw fa-money"></i>
					Contratos - Provisión
				</h1>
			</div>
		</div>
	</div>

	<div class="row mt-4 mb-2" id="cont_contabilidad_div_parametros_reporte">
		<div class="page-header wide">
			<fieldset class="dhhBorder">

				<legend class="dhhBorder">Búsqueda</legend>

				<div class="col-lg-4 col-sm-6 col-md-6 col-xs-12">
					<label>Empresa: </label>
					<div class="input-group" style="width: 100%;"> 
						<select
							class="form-control select2"
							name="razon_social_id" 
							id="razon_social_id" 
							title="- Seleccione -">
						</select>
					</div>
				</div>

				<div class="col-lg-4 col-sm-6 col-md-6 col-xs-12">
					<label for="start">Periodo:</label>
					<select
						class="form-control select2"
						name="cont_contabilidad_fecha_mes" 
						id="cont_contabilidad_fecha_mes" 
						title="- Seleccione -">
						<option value="0">- Seleccione -</option>
						<?php
						$fecha_actual = date("d-m-Y");
						$meses = array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');

						for ($i=0; $i < 12; $i++) { 
							$anio_actual = date("Y",strtotime($fecha_actual));
							$mes_actual = date("m",strtotime($fecha_actual));
							echo '<option value="' . $anio_actual . '-' . $mes_actual . '">' . $meses[($mes_actual)-1] . ' del ' . $anio_actual . '</option>';
							$fecha_actual = date("d-m-Y",strtotime($fecha_actual."- 1 month")); 
						}

						?>
					</select>
				</div>

				<div class="col-lg-4 col-sm-6 col-md-6 col-xs-12">
					<label>Tipo de Moneda</label>
					<select
						class="form-control select2"
						name="tipo_moneda_contable"
						id="tipo_moneda_contable"
						title="Seleccione el tipo de moneda">
						<option value="0">- Seleccione -</option>
						<option value="1">Sol</option>
						<option value="2">Dolar</option>
					</select>
				</div>

				<div class="col-lg-4 col-sm-6 col-md-6 col-xs-12">
					<label>Fecha comprobante</label>
					<div class="form-group">
						<div class="input-group col-xs-12">
							<input
								type="text"
								class="form-control sec_contrato_contabilidad_datepicker"
								id="cont_contabilidad_fecha_comprobante"
								value="<?php echo date("d-m-Y"); ?>"
								readonly="readonly"
								style="height: 34px;"
								>
							<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="cont_contabilidad_fecha_comprobante"></label>
						</div>
					</div>
				</div>

				<div class="col-lg-4 col-sm-6 col-md-6 col-xs-12">
					<label>Número comprobante</label>
					<div class="form-group">
						<div class="input-group col-xs-12">
							<input
								type="text"
								class="form-control"
								id="cont_contabilidad_numero_comprobante"
								autocomplete="off"
								style="height: 34px;"
								>
						</div>
					</div>
				</div>

				<div class="col-lg-4 col-sm-6 col-md-6 col-xs-12">
					<label>Tipo de cambio (Opcional)</label>
					<div class="form-group">
						<div class="input-group col-xs-12">
							<input
								type="text"
								class="form-control"
								id="cont_contabilidad_tipo_de_cambio"
								autocomplete="off"
								style="height: 34px;"
								>
						</div>
					</div>
				</div>

				<div class="col-lg-4 col-sm-6 col-md-6 col-xs-12">
					<button
						type="button"
						class="btn btn-primary btn-block"
						data-placement="top"
						title="Consultar"
						onclick="sec_contrato_provision_buscar();"
						style="margin-top: 18px;margin-bottom: 40px;">
						<i class="glyphicon glyphicon-search"></i>
						Consultar
					</button>
				</div>

			</fieldset>

		</div>
	</div>

	<div class="page-header wide" id="div_contabilidad_boton_export" style="display: none;">
		<div class="row mt-3 mb-2">
			<div class="row form-horizontal">
				<div class="col-md-2" id="cont_contabilidad_boton_excel_concar">

				</div>
			</div>
		</div>
	</div>

	<div class="row mt-3" id="cont_contrato_contabilidad_div_tabla">
		<table id="cont_locales_contabilidad_datatable" class="table table-bordered dt-responsive" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th scope="col">N° Documento</th>
					<th scope="col">Acreedor</th>
					<th scope="col">Tienda</th>
					<th scope="col">Empresa AT</th>
					<th scope="col">Centro costo</th>
					<th scope="col">Tipo</th>
					<th scope="col">Importe</th>
				</tr>
			</thead>
			<tbody>

			</tbody>
			<tfoot>
				<tr>
					<th scope="col">N° Documento</th>
					<th scope="col">Acreedor</th>
					<th scope="col">Tienda</th>
					<th scope="col">Empresa AT</th>
					<th scope="col">Centro de costo</th>
					<th scope="col">Tipo</th>
					<th scope="col">Importe</th>
				</tr>
			</tfoot>
		</table>
	</div>

</div>

<?php
}
?>