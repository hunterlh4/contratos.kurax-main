<?php 
$this_menu = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sub_sec_id = '$sub_sec_id' LIMIT 1")->fetch_assoc();
$menu_id = $this_menu["id"];

if(!array_key_exists($menu_id,$usuario_permisos)){
	echo "&nbsp;&nbsp;&nbsp;No tienes permisos para este recurso.";
	//die();
	$continuar=false;
}

?>


<style>
	.hasDatepicker {
    	min-height: 28px !important;
	}
</style>
<div class="tab-pane">
	<div class="row">
		<div class="col-xs-12 text-center">
			<h1 class="page-title">
				Servicios Públicos
			</h1>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-xs-12">
		<!--PARAMETROS DE BUSQUEDA-->
		<div class="row mt-4 mb-2" id="cont_contabilidad_div_parametros_reporte">
			<div class="page-header wide">
				<fieldset class="dhhBorder">
					<legend class="dhhBorder">Parámetros</legend>
					<div class="panel-body">
						<!--FILTROS-->
						<div class="row">

							<div class="form-group col-md-2">
								<label>Buscar Por: </label>
								<div class="input-group" style="width: 100%;">
									<select class="form-control select2 col-5" name="sec_con_asi_cont_ser_pub_buscar_por"  id="sec_con_asi_cont_ser_pub_buscar_por" title="Seleccione el Mes">
										<option selected value="1">Periodo</option>
										<option value="2">Fecha Vencimiento</option>
										<option value="3">Fecha Pago</option>
									</select>
								</div>
							</div>
							<!--PERIODO-->
							<div class="form-group block-periodo col-md-2">
								<label>Periodo: </label>
								<div class="input-group" style="width: 100%;">
									<select class="form-control select2 col-5" name="sec_con_asi_cont_ser_pub_select_mes"  id="sec_con_asi_cont_ser_pub_select_mes" title="Seleccione el Mes">
									</select>
								</div>
							</div>
							<!--FECHA VENCIMIENTO-->
							<div class="form-group block-fecha col-md-2" style="display:none">
								<label>Fecha Vcto. Desde:</label>
								<div class="input-group">
	                                <input type="text" class="form-control servicio_publico_fecha_emision_datepicker"
	                                id="sec_con_asi_cont_ser_pub_txt_fec_vcto_desde" value="" readonly="readonly" style="height: 20px;">
	                                <label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="sec_con_asi_cont_ser_pub_txt_fec_vcto_desde"></label>
								</div>
							</div>
							<div class="form-group block-fecha col-md-2" style="display:none">
								<label>Fecha Vcto. Hasta:</label>
								<div class="input-group">
	                                <input type="text" class="form-control servicio_publico_fecha_emision_datepicker" 
	                                id="sec_con_asi_cont_ser_pub_txt_fec_vcto_hasta" value="" readonly="readonly" style="height: 20px;">
	                                <label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="sec_con_asi_cont_ser_pub_txt_fec_vcto_hasta"></label>
								</div>
							</div>


							<!--LOCALES-->
							<div class="form-group col-md-2">
								<label>Tipo de Servicio: </label>
								<div class="input-group" style="width: 100%;">
									<select class="form-control select2 col-5" name="sec_con_asi_cont_ser_pub_select_tipo_servicio"  id="sec_con_asi_cont_ser_pub_select_tipo_servicio" title="Seleccione el Local">
							            <option value="0">TODOS</option>
										<?php
										$locales_command = "SELECT id, nombre FROM cont_tipo_servicio_publico where status = 1";
										$locales_query = $mysqli->query($locales_command);
										while($ct=$locales_query->fetch_assoc()){
											?>
											<option value="<?php echo $ct["id"];?>"><?php echo $ct["nombre"];?></option>
											<?php
										}
										?>
							        </select>
								</div>
							</div>

							<div class="form-group col-md-2">
								<label>Empresa Arrendataria: </label>
								<div class="input-group" style="width: 100%;">
									<select class="form-control select2 col-5" name="sec_con_asi_cont_ser_pub_select_empresa"  id="sec_con_asi_cont_ser_pub_select_empresa" title="Seleccione una empresa">
							            <option value="0">TODOS</option>
										<?php
										$query = "SELECT id, nombre
										FROM tbl_razon_social
										WHERE subdiario <> '' AND status = 1
										ORDER BY nombre ASC";
										$list_query = $mysqli->query($query);
										$list = [];
										while ($li = $list_query->fetch_assoc()) {
											?>
											<option value="<?php echo $li["id"];?>"><?php echo $li["nombre"];?></option>
											<?php
										}
										?>
							        </select>
								</div>
							</div>

							<div class="form-group col-md-2">
								<label>Fecha Comprobante:</label>
								<div class="input-group">
									<input type="text" class="form-control servicio_publico_fecha_emision_datepicker" 
									id="sec_con_asi_cont_ser_pub_fecha_comprobante" value="" readonly="readonly" style="height: 20px;">
									<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="sec_con_asi_cont_ser_pub_fecha_comprobante"></label>
								</div>
							</div>

							<div class="form-group col-md-2">
								<label>Numero comprobante:</label>
								<div class="input-group">
									<input type="text" class="form-control" id="sec_con_asi_cont_ser_pub_numero_comprobante">
								</div>
							</div>

						
						
						
						</div>
						<!--BOTONES-->
						<div class="row">
							<div class="col-xs-10"></div>
							<!-- <div class="col-xs-2" style="margin-top: 15px; text-align: right;">
								<button type="button" class="btn btn-block btn-success" id="sec_ser_pub_btn_buscar" onclick="sec_asi_cont_serv_pub_buscar_registros();" data-toggle="tooltip" data-placement="bottom"  data-original-title="Buscar">
									<i class="glyphicon glyphicon-search" ></i>
									Buscar
								</button>	
							</div> -->
							<div class="col-xs-2" id="div_contrato_export_servicio_publico_concar" style="margin-top: 15px; text-align: right;">
								<a onclick="sec_asi_cont_serv_pub_mostrarReporteExcel();" class="btn btn-success  btn-block export_list_btn">
									<span class="glyphicon glyphicon-export"></span> Exportar excel
								</a>
							</div>
						</div>
					</div>
				</fieldset>
			</div>
		</div>
		<!--TABLA-->
		<div class="panel">
			<div class="panel-body">
				<div class="row">
					<div id="container_table_servicio_publico" class="form-group col-md-12">
						<table id="sec_con_tabla_registros" class="table table-striped table-hover table-condensed table-bordered dt-responsive display" cellspacing="0" width="100%">
							
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

