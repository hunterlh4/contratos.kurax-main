<?php

$menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' AND sub_sec_id = 'servicio_publico' LIMIT 1")->fetch_assoc();

$menu_id = $menu_id_consultar["id"];

$login_empresa_id = $login?$login['empresa_id']:null;

$param_red_id = 0;

if($login_empresa_id == null)
{
	$login_empresa_id = 5;
}

$select_red =
"
    SELECT
		red_id
	FROM tbl_razon_social
	WHERE id = '".$login_empresa_id."'
	LIMIT 1
";

$data_select_red = $mysqli->query($select_red);

$ids_empresa = 0;
$ids_locales_redes = ' id IN (0)';

while($row = $data_select_red->fetch_assoc())
{
	$param_red_id = $row["red_id"];
}

$tiene_permiso_vista = 0;

if(array_key_exists($menu_id,$usuario_permisos) && in_array("view", $usuario_permisos[$menu_id]))
{
    $tiene_permiso_vista = 1;
}
?>



<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>
<script src="https://code.highcharts.com/modules/accessibility.js"></script>
<style>
	.highcharts-figure,
	.highcharts-data-table table {
	    min-width: 310px;
	    max-width: 800px;
	    margin: 1em auto;
	}

	#container {
	    height: 400px;
	}

	.highcharts-data-table table {
	    font-family: Verdana, sans-serif;
	    border-collapse: collapse;
	    border: 1px solid #ebebeb;
	    margin: 10px auto;
	    text-align: center;
	    width: 100%;
	    max-width: 500px;
	}

	.highcharts-data-table caption {
	    padding: 1em 0;
	    font-size: 1.2em;
	    color: #555;
	}

	.highcharts-data-table th {
	    font-weight: 600;
	    padding: 0.5em;
	}

	.highcharts-data-table td,
	.highcharts-data-table th,
	.highcharts-data-table caption {
	    padding: 0.5em;
	}

	.highcharts-data-table thead tr,
	.highcharts-data-table tr:nth-child(even) {
	    background: #f8f8f8;
	}

	.highcharts-data-table tr:hover {
	    background: #f1f7ff;
	}
</style>
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
	.diabled_all_content {
	    pointer-events: none;
	    opacity: 0.4;
	}

	.ocultar_div{
		visibility: collapse;
	}

	.hasDatepicker {
    	min-height: 28px !important;
	}
</style>

<?php 
if($tiene_permiso_vista == 1)
{
	?>
	<div class="content container-fluid">
        <div class="page-header wide" style="margin-bottom: 10px;">
            <div class="row">
                <div class="col-xs-12 text-center">
                    <h1 class="page-title titulosec_contrato">
                        <i class="icon icon-inline glyphicon glyphicon-briefcase"></i>
                        Servicios Públicos - Contabilidad
                    </h1>
                </div>
            </div>
        </div>

        <div class="content col-md-12">
        	<div class="page-header wide">
                <div class="row mt-4 mb-2">
                    <fieldset class="dhhBorder">
                    	<legend class="dhhBorder">Búsqueda</legend>
                    	<form autocomplete="off">

                    		<div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12">
                                <label>
                                    Tipo:
                                </label>
                                <div class="form-group">
	                                <select 
	                                	class="form-control sec_contrato_servicio_publico_select_filtro" 
	                                	name="contrato_servicio_publico_param_tipo_solicitud"
	                                	id="contrato_servicio_publico_param_tipo_solicitud" 
	                                	title="Seleccione">
										<option value="0">Seleccione</option>
										<?php
											$consulta = 
											"
												SELECT 
													id, nombre 
												FROM cont_ser_pub_tipo_solicitud 
												where id IN (3, 4) AND status = 1
											";
										
										$query_select = $mysqli->query($consulta);

										while($ct=$query_select->fetch_assoc())
										{
											?>
												<option value="<?php echo $ct["id"];?>">
													<?php echo $ct["nombre"];?>
												</option>
											<?php
										}
										?>
									</select>
                                </div>
                            </div>

                            <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12 tipo_recibo" 
                            	style="display: none;">
                                <label>
                                    Buscar Por:
                                </label>
                                <div class="form-group">
	                                <select 
	                                	class="form-control sec_contrato_servicio_publico_select_filtro" 
	                                	name="contrato_servicio_publico_param_buscar_por"
	                                	id="contrato_servicio_publico_param_buscar_por" 
	                                	title="Seleccione">
										<option value="0">-- Seleccione --</option>
										<option value="1">Periodo</option>
										<option value="2">Fecha Vencimiento</option>
									</select>
                                </div>
                            </div>

                            <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12 tipo_recibo" 
                            	style="display: none;">
                                <label>
                                    Tipo de Servicio:
                                </label>
                                <div class="form-group">
                                	<select 
                                		class="form-control sec_contrato_servicio_publico_select_filtro" 
                                		name="contrato_servicio_publico_param_tipo_servicio"  
                                		id="contrato_servicio_publico_param_tipo_servicio" 
                                		title="Seleccione el Servicio">
									<option value="0">Todos</option>
									<?php
										$locales_command = 
										"
											SELECT 
												id, nombre 
											FROM cont_tipo_servicio_publico 
											where id IN (1, 2) AND status = 1";
									
									$locales_query = $mysqli->query($locales_command);

									while($ct=$locales_query->fetch_assoc())
									{
										?>
											<option value="<?php echo $ct["id"];?>">
												<?php echo $ct["nombre"];?>
											</option>
										<?php
									}
									?>
								</select>
                                </div>
                            </div>

                            <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12 tipo_recibo" 
                            	style="display: none;">
                                <label>
                                    Razón Social:
                                </label>
                                <div class="form-group">
                                    <select
                                        class="form-control sec_contrato_servicio_publico_select_filtro"
                                        name="contrato_servicio_publico_param_empresa_arrendataria"
                                        id="contrato_servicio_publico_param_empresa_arrendataria"
                                        title="Todos la Empresa Arrendataria">
                                    </select>
                                </div>
                            </div>

                            <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12 tipo_recibo"
                            	style="display: none;">
                                <label>
                                    Zona:
                                </label>
                                <div class="form-group">
                                    <select
                                        class="form-control sec_contrato_servicio_publico_select_filtro"
                                        name="contrato_servicio_publico_param_zona"
                                        id="contrato_servicio_publico_param_zona"
                                        title="Todos el estado">
                                        <option value="0">Todos</option>
                                        <?php

                                            $query = 
                                            "
                                                SELECT
                                                    z.id, z.nombre
                                                FROM tbl_zonas z
                                                WHERE razon_social_id = '".$login_empresa_id."'
                                            ";
                                            
                                            $list_query = $mysqli->query($query);
                                            
                                            while ($li = $list_query->fetch_assoc()) 
                                            {
                                                ?>
                                                    <option value="<?php echo $li["id"]; ?>">
                                                    	<?php echo $li["nombre"]; ?>
                                                    </option>
                                                <?php
                                            }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12 tipo_recibo" 
                            	style="display: none;">
                                <label>
                                    Supervisor:
                                </label>
                                <div class="form-group">
                                    <select
                                        class="form-control sec_contrato_servicio_publico_select_filtro"
                                        name="contrato_servicio_publico_param_supervisor"
                                        id="contrato_servicio_publico_param_supervisor"
                                        title="Todos los Supervisor">
                                        <option value="0">Todos</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12 tipo_recibo" 
                            	style="display: none;">
                                <label>
                                    Local:
                                </label>
                                <div class="form-group">
                                    <select
                                        class="form-control sec_contrato_servicio_publico_select_filtro"
                                        name="contrato_servicio_publico_param_local"
                                        id="contrato_servicio_publico_param_local"
                                        title="Todos">
                                        <option value="0">Todos</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12 tipo_recibo contrato_servicio_publico_div_param_fechas"
                            	style="display: none;">
                                <label>Razón Social:</label>
                                <div class="form-group">
                                    <select
                                        class="form-control sec_contrato_servicio_publico_select_filtro"
                                        name="contrato_servicio_publico_param_tipo_empresa"
                                        id="contrato_servicio_publico_param_tipo_empresa"
                                        title="Seleccione">
                                        <option value="0">Seleccione</option>
                                        <?php

                                            $query = 
                                            "
                                                SELECT
													id, nombre
												FROM tbl_razon_social
												WHERE permiso_servicios_publicos = 1 AND status = 1
                                            ";
                                            
                                            $list_query = $mysqli->query($query);
                                            
                                            while ($li = $list_query->fetch_assoc()) 
                                            {
                                                ?>
                                                    <option value="<?php echo $li["id"]; ?>">
                                                    	<?php echo $li["nombre"]; ?>
                                                    </option>
                                                <?php
                                            }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12 tipo_recibo contrato_servicio_publico_div_param_fechas" style="display: none;">
								<label id="contrato_servicio_publico_param_label_fecha_inicio">
									Fecha Inicio:
								</label>
	                            <div class="input-group">
	                                <input
	                                        type="text"
	                                        name="contrato_servicio_publico_param_fecha_inicio"
	                                        id="contrato_servicio_publico_param_fecha_inicio"
	                                        class="form-control servicio_publico_datepicker"
	                                        value=""
	                                        style="height: 30px;"
	                                        >
	                                <label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="contrato_servicio_publico_param_fecha_inicio"></label>
	                            </div>
                            </div>

                            <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12 tipo_recibo contrato_servicio_publico_div_param_fechas" style="display: none;">
								<label id="contrato_servicio_publico_param_label_fecha_fin">
									Fecha Fin:
								</label>
	                            <div class="input-group">
	                                <input
	                                        type="text"
	                                        name="contrato_servicio_publico_param_fecha_fin"
	                                        id="contrato_servicio_publico_param_fecha_fin"
	                                        class="form-control servicio_publico_datepicker"
	                                        value=""
	                                        style="height: 30px;"
	                                        >
	                                <label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="contrato_servicio_publico_param_fecha_fin"></label>
	                            </div>
                            </div>

                            <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12 tipo_recibo" 
                            	id="contrato_servicio_publico_div_param_periodo" 
                            	style="display: none;">
                                <label>
                                    Periodo:
                                </label>
                                <div class="form-group">
                                	<select 
                                		class="form-control sec_contrato_servicio_publico_select_filtro" 
                                		name="contrato_servicio_publico_param_periodo"  
                                		id="contrato_servicio_publico_param_periodo" 
                                		title="Seleccione el Periodo">
									</select>
                                </div>
                            </div>

                            <div class="form-group col-lg-3 col-md-3 col-sm-3 col-xs-12 tipo_recibo" 	style="display: none;">
                                <label>
                                    Estado:
                                </label>
                                <div class="form-group">
                                    <select
                                        class="form-control sec_contrato_servicio_publico_select_filtro"
                                        name="contrato_servicio_publico_param_estado"
                                        id="contrato_servicio_publico_param_estado"
                                        title="Todos los Estados">
                                        <option value="0">Todos</option>
										<?php 
										$query = 
										"
											SELECT
												id, nombre 
											FROM cont_tipo_estado_servicio_publico 
											WHERE status = 1 AND id IN(1, 2, 3, 4, 5, 6)
										";
										
										$list_query = $mysqli->query($query);
										while ($li = $list_query->fetch_assoc())
										{
											?>
												<option value="<?=$li['id']?>">
													<?=$li['nombre']?>
												</option>
											<?php
										}
										?>
                                    </select>
                                </div>
                            </div>

                            <div class="row form-horizontal">
                                <div class="col-md-4 col-md-offset-8 text-right">
                                    <button 
                                    	type="button" 
                                    	class="btn btn-success float-left"
                                    	onclick="contrato_servicio_publico_btn_buscar();">
                                        <i class="glyphicon glyphicon-search"></i>
                                        	Buscar
                                    </button>
                                </div>
                            </div>

                    	</form>
                	</fieldset>
            	</div>
        	</div>

        	<div class="page-header wide" id="">
                <div class="row mt-3 mb-2">
                    <div class="row form-horizontal">
                        <div class="col-md-12">                   
                            <button 
                                class="btn btn-success btn-sm" 
                                id="contrato_servicio_publico_reporte_btn_listar_servicios_publicos"
                                style="display: none;">
                                <span class="icon fa fa-file-excel-o" style="font-size: 14px;"></span>
                                Exportar xls
                            </button>

                            <button 
                                class="btn btn-success btn-sm" 
                                id="contrato_servicio_publico_exportar_servicios_publicos_plantilla_concar"
                                style="display: none;">
                                <span class="icon fa fa-file-excel-o" style="font-size: 14px;"></span>
                                Exportar Plantilla Concar xls
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3" id="contrato_servicio_publico_listar_servicios_publicos_tabla" 
            	style="display: none; width:100%;overflow: auto;">
                <table id="contrato_servicio_publico_listar_servicios_publicos_datatable" class="table table-striped table-bordered table-hover table-condensed dt-responsive display" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th class="text-center">ID</th>
                            <th class="text-center">Jefe Comercial</th>
                            <th class="text-center">Creado Por</th>
							<th class="text-center">Local</th>
                            <th class="text-center">Servicio</th>
							<th class="text-center">Fecha de envío - Supervisor</th>
                            <th class="text-center">F. de validación - Contabilidad</th>
                            <th class="text-center">F. de cancelación - Tesorería</th>
                            <th class="text-center">Periodo</th>
                            <th class="text-center">Suministro</th>
                            <th class="text-center">Monto</th>
                            <th class="text-center">F. Vencimiento</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Monto Total</th>
                            <th class="text-center">Validar</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>

            <div class="row mt-3" id="contrato_servicio_publico_listar_servicios_publicos_tabla_pre_concar" 
            	style="display: none; width:100%;overflow: auto;">
                <table id="contrato_servicio_publico_listar_servicios_publicos_pre_concar_datatable" class="table table-striped table-bordered table-hover table-condensed dt-responsive display" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th class="text-center">ID</th>
                            <th class="text-center">Local</th>
                            <th class="text-center">Razón Social</th>
                            <th class="text-center">Servicio</th>
                            <th class="text-center">RUC</th>
                            <th class="text-center">Periodo</th>
                            <th class="text-center">Suministro</th>
                            <th class="text-center">F. Vencimiento</th>
                            <th class="text-center">F. Creado</th>
                            <th class="text-center">F. Atención</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Monto Total</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>

		</div>        	
	</div>
	<?php
}
else
{
	include("403.php");
	return false;
}
?>

<!-- INICIO MODAL AGREGAR MONTO -->
<div id="sec_con_serv_pub_modal_agregar_monto" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-sm" role="document" style="width: 90%;">
		<div class="modal-content">
			<div class="modal-header">
				<input type="hidden" name="sec_con_serv_pub_id_archivo" id="sec_con_serv_pub_id_archivo">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h3 class="modal-title" id="sec_con_serv_pub_title_modal_agregar_monto" style="text-align: center;"></h3>
				<h5 class="modal-title" id="sec_con_serv_pub_title_modal_agregar_monto_local" style="text-align: center; font-weight: bold;"></h5>
			</div>
			<div class="modal-body">
				<form id="sec_con_ser_pub_agregar_monto_recibo" name="sec_con_ser_pub_agregar_monto_recibo" method="POST" enctype="multipart/form-data" autocomplete="off">
					<div class="row">
					    <div class="col-md-12">
					    	<!--RECIBO-->
					        <div class="col-md-4">
					        	<div class="panel">
									<div class="panel-body" style="padding: 0px !important; padding-top: 15px !important;">	
										<h4 id="sec_con_serv_pub_mensaje_imagen" style="text-align: center;"></h4>
										<div class="row" style="margin-bottom: 20px;" id="sec_con_serv_pub_div_imagen_recibo">
											
										</div>
										<div class="col-xs-6 col-md-6 col-sm-6" style="text-align: center; margin-bottom: 5px;" id="sec_con_serv_pub_div_VerImagenFullPantalla">
											<button type="button" class="btn btn-block btn-block btn-info" id="sec_con_serv_pub_ver_full_pantalla">
												<i class="fa fa-arrows-alt"></i>  Pantalla Completa
											</button>
										</div>
										<div class="col-xs-6 col-md-6 col-sm-6" style="text-align: center; margin-bottom: 5px;" id="sec_con_serv_pub_btn_descargar_imagen_recibo">											
											<a id="sec_con_serv_pub_descargar_imagen_a" class="btn btn-block btn-block btn-success"><i class="fa fa-arrow-circle-down"></i> Descargar</a>
										</div>
									</div>
								</div>
					        </div>

					        <!--FILE CONTOMETRO-->
					        <div class="col-md-4" 
					        	id="sec_contrato_servicio_publico_modal_agregar_monto_div_file_contometro"
					        	style="display: none;" 
					        	>
					        	<div class="panel">
									<div class="panel-body" style="padding: 0px !important; padding-top: 15px !important;">	
										<h4 id="sec_con_serv_pub_mensaje_imagen_contometro" style="text-align: center;"></h4>
										<div class="row" style="margin-bottom: 20px;" id="sec_con_serv_pub_div_imagen_recibo_contometro">
											
										</div>
										<div class="col-xs-6 col-md-6 col-sm-6" style="text-align: center; margin-bottom: 5px;" id="sec_con_serv_pub_div_VerImagenFullPantalla_contometro">
											<button type="button" class="btn btn-block btn-block btn-info" id="sec_con_serv_pub_ver_full_pantalla_contometro">
												<i class="fa fa-arrows-alt"></i>  Pantalla Completa
											</button>
										</div>
										<div class="col-xs-6 col-md-6 col-sm-6" style="text-align: center; margin-bottom: 5px;" id="sec_con_serv_pub_btn_descargar_imagen_recibo_contometro">											
											<a id="sec_con_serv_pub_descargar_imagen_a_contometro" class="btn btn-block btn-block btn-success"><i class="fa fa-arrow-circle-down"></i> Descargar</a>
										</div>
									</div>
								</div>
					        </div>

					        <!--DATOS-->
					        <div class="col-md-3">
					        	<div class="col-md-12" style="text-align: right;">
					        		<label id="sec_con_serv_pub_periodo_busqueda" style="text-align: center;"></label>
					        		<input type="hidden" id="sec_con_serv_pub_periodo_busqueda_data" style="text-align: center;"></label>
					        		<button type="button" class="btn btn-info btn-sm" id="sec_con_serv_pub_btn_navegar_periodo" onclick="sec_con_serv_pub_navegar_periodo(-1)"><i class="fa fa-chevron-left"></i></button>
					        		<button type="button" class="btn btn-info btn-sm" id="sec_con_serv_pub_btn_navegar_periodo" onclick="sec_con_serv_pub_navegar_periodo(1)"><i class="fa fa-chevron-right"></i></button>
					        	</div>
					        	<div class="col" id="sec_con_serv_pub_div_datos_recibo">
					        		<div class="form-group col-md-12" style="width:  100%;">
										<label style="text-align: left;">Número de suministro: </label>
										<div class="input-group" style="width: 100%;">
											<input type="text" class="form-control" disabled name="sec_con_serv_pub_num_suministro" id="sec_con_serv_pub_num_suministro" placeholder="123456789" style="text-align: right;" />
										</div>
									</div>
									<div class="form-group col-md-12" style="width:  100%;">
										<label style="text-align: left;">Serie: </label>
										<div class="input-group" style="width: 100%;">
											<input type="text" class="form-control" name="sec_con_serv_pub_serie" id="sec_con_serv_pub_serie" style="text-align: right; font-weight: bold;" />
										</div>
									</div>
									<div class="form-group col-md-12" style="width:  100%;">
										<label style="text-align: left;">Número de recibo: </label>
										<div class="input-group" style="width: 100%;">
											<input type="text" class="form-control" name="sec_con_serv_pub_num_recibo" id="sec_con_serv_pub_num_recibo" placeholder="000000010" style="text-align: right; font-weight: bold;" />
										</div>
									</div>
									<div class="form-group col-md-12" id="div_con_serv_pub_comentario" style="width:  100%; display:none">
										<label style="text-align: left;">Comentario: </label>
										<div class="input-group" style="width: 100%;">
											<textarea disabled class="form-control" name="sec_con_serv_pub_comentario" id="sec_con_serv_pub_comentario"  rows="3"></textarea>
										</div>
									</div>
					        		<div class="form-group col-md-12" style="width:  100%;">
										<label style="text-align: left;">Periodo de consumo: </label>
										<div class="input-group" style="width: 100%;">
											<input type="month" class="form-control" name="sec_con_serv_pub_periodo_consumo" id="sec_con_serv_pub_periodo_consumo" placeholder="123456789" style="text-align: right;" />
										</div>
										
										
									</div>
					        		<div class="form-group col-md-12" style="width:  100%;">
										<label style="text-align: left;">Fecha de emisión: </label>
										<div class="input-group" style="width: 100%;">
											<input type="text" class="form-control servicio_publico_datepicker" name="sec_con_serv_pub_fecha_emision" id="sec_con_serv_pub_fecha_emision" style="text-align: right;" />
										</div>
									</div>
					        		<div class="form-group col-md-12" style="width:  100%;">
										<label style="text-align: left;">Fecha de vencimiento: </label>
										<div class="input-group" style="width: 100%;">
											<input type="text" class="form-control servicio_publico_datepicker" name="sec_con_serv_pub_fecha_vencimiento" id="sec_con_serv_pub_fecha_vencimiento" style="text-align: right;" />
										</div>
									</div>
									<div class="form-group col-md-12" style="width:  100%;">
										<label style="text-align: left;">Compromiso de pago: </label>
										<input type="hidden" name="sec_con_serv_pub_id_tipo_compromiso" id="sec_con_serv_pub_id_tipo_compromiso">
										<input type="hidden" name="sec_con_serv_pub_id_tipo_recibo" id="sec_con_serv_pub_id_tipo_recibo">
										<input type="hidden" name="sec_con_serv_pub_id_local" id="sec_con_serv_pub_id_local">
										<input type="hidden" name="sec_con_serv_pub_id_recibo" id="sec_con_serv_pub_id_recibo">
										<input type="hidden" name="sec_con_serv_pub_id_monto_pct" id="sec_con_serv_pub_id_monto_pct">
										<div class="input-group" style="width: 100%;">
											<select style="display:none;" class="form-control input_text col-5" name="sec_con_ser_pub_select_compromiso_pago"  id="sec_con_ser_pub_select_compromiso_pago" title="Seleccione el Jefe Comercial">
											</select>
											<input type="text" disabled class="form-control" name="sec_con_serv_pub_id_tipo_compromiso_nombre" id="sec_con_serv_pub_id_tipo_compromiso_nombre" placeholder="" style="text-align: right;" />
										</div>
									</div>
					        		<div class="form-group col-md-12" style="width:  100%;">
										<label style="text-align: left;">Total mes actual: <span style="color: red;">(*)</span></label>
										<div class="input-group" style="width: 100%;">
											<input type="number" class="form-control" name="sec_con_serv_pub_monto_mes_actual" id="sec_con_serv_pub_monto_mes_actual" placeholder="Ejm: 125.60" style="text-align: right; font-weight: bold;" />
										</div>
									</div>
					        		<div class="form-group col-md-12" style="width:  100%;">
										<label style="text-align: left;">Total a pagar: </label>
										<div class="input-group" style="width: 100%;">
											<input type="text" class="form-control"  name="sec_con_serv_pub_total_pagar" id="sec_con_serv_pub_total_pagar" placeholder="0.00" style="text-align: right;" />
										</div>
									</div>
									<div class="form-group col-md-12" id="sec_con_serv_pub_check_caja_chica_div" style="width: 100%; margin-top: 10px;">
										<input type="checkbox" value="" id="sec_con_serv_pub_check_cajachica_id" />
										<label for="sec_con_serv_pub_check_cajachica_id">Caja Chica</label>
									</div>
									<div class="form-group col-md-12 ocultar_div" style="width: 100%;" id="sec_con_serv_pub_div_nombre_pagar_hide">
										<label style="text-align: left;">Ingrese el email de la persona que se notificara: <span style="color: red;">(*)</span></label>
										<div class="input-group" style="width: 100%;">
											<input type="text" class="form-control" name="sec_con_serv_pub_nombre_pagar" id="sec_con_serv_pub_nombre_pagar" placeholder="Ejm: maria.casas@testtest.apuestatotal.com" style="text-align: right; font-weight: bold;" maxlength="100" />
										</div>
									</div>
									<!--<div class="form-group col-md-12" style="width: 100%;">
										<label style="text-align: left;">Observación: </label>
										<div class="input-group" style="width: 100%;">
											 <textarea class="form-control" id="sec_con_serv_pub_observacion" rows="3"></textarea>
										</div>
									</div>-->
					        	</div>
					        	
					        </div>
					        <div class="col-md-5">
								<figure class="highcharts-figure">
								    <div id="container"></div>
								    <!--<p class="highcharts-description"></p>-->
								</figure>
							</div>
					    </div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-info" id="sec_nuevo_modal_editar_archivo" onclick="sec_contrato_servicio_publico_guardar_nuevo_monto_recibo('editar')">
					<i class="icon fa fa-save"></i>
					<span>Guardar</span>
				</button>
				<button type="button" class="btn btn-danger" class="btn btn-success" data-dismiss="modal">
					<i class="icon fa fa-close"></i>
					Cancelar
				</button>
				<button type="button" class="btn btn-primary" id="sec_nuevo_modal_observar_archivo" onclick="sec_contrato_servicio_publico_abrir_modal_observacion()">
					<i class="icon fa fa-eye"></i>
					<span>Observar</span>
				</button>
				<button type="button" class="btn btn-success" id="sec_nuevo_modal_guardar_archivo" onclick="sec_contrato_servicio_publico_guardar_nuevo_monto_recibo('validar')">
					<i class="icon fa fa-save"></i>
					<span id="btn_nombre_guardar_modal" >Validar</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL AGREGAR MONTO -->

<!-- INICIO MODAL OBSERVAR SERVICIO -->
<div id="sec_con_serv_pub_modal_observar_servicio" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h3 class="modal-title" style="text-align: center;">Agregar Observación</h3>
			</div>
			<div class="modal-body">
				<div class="form-group" style="width: 100%;">
					<label style="text-align: left;">Observación: </label>
					<div class="input-group" style="width: 100%;">
						 <textarea class="form-control" id="sec_con_serv_pub_observacion" rows="6" maxlength="255"></textarea>
					</div>
				</div>

				<div class="form-group" style="width: 100%;">
					<label style="text-align: left;">Correos: </label>
					<div class="input-group" style="width: 100%;">
						 <textarea placeholder="Ingrese los correos separados con ','" class="form-control" id="sec_con_serv_pub_observacion_correos" rows="4"></textarea>
						 <b>Nota: Para más de un correo se debe separar por comas (,)</b>
					</div>
				</div>
				
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" class="btn btn-success" data-dismiss="modal">
					<i class="icon fa fa-close"></i>
					Cancelar
				</button>
				<button type="button" class="btn btn-success" id="sec_nuevo_modal_guardar_observar_archivo" onclick="sec_contrato_servicio_publico_observar_servicio_publico()">
					<i class="icon fa fa-save"></i>
					<span>Guardar</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL OBSERVAR SERVICIO -->

<!-- INICIO MODAL AGREGAR RECIBO -->
<div id="sec_con_serv_pub_modal_agregar_recibo" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-sm" role="document" style="width: 55%">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h3 class="modal-title" style="text-align: center;" id="sec_serv_pub_modal_title_agregar_recibo">Agregar Recibo</h3>
			</div>
			<div class="modal-body">
				<form id="sec_con_serv_pub_form_modal_agregar_recibo" method="POST" enctype="multipart/form-data">
					<div class="form-group col-md-4">
						<label>Fecha emision:</label>
						<div class="input-group">
							<input type="hidden" id="sec_con_serv_pub_fecha_emision_recibo" class="filtro"
                                data-col="sec_con_serv_pub_fecha_emision_recibo" name="sec_con_serv_pub_fecha_emision_recibo" value="<?php echo date("Y-m-d"); ?>"
                                data-real-date="sec_con_serv_pub_txt_fecha_emision">
                            <input type="text" class="form-control servicio_publico_fecha_emision_datepicker"
                                id="sec_con_serv_pub_txt_fecha_emision" value="<?php echo date("d-m-Y");?>"
                                readonly="readonly" style="height: 34px;" >
                            <label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="sec_con_serv_pub_txt_fecha_emision"></label>

						</div>
					</div>

					<div class="form-group col-md-4">
						<label>Fecha vencimiento:</label>
						<div class="input-group">
						  <input type="hidden" id="sec_con_serv_pub_fecha_vencimiento_recibo" class="filtro" 
						  		data-col="sec_con_serv_pub_fecha_vencimiento_recibo" 
						  		name="sec_con_serv_pub_fecha_vencimiento_recibo" value="<?php echo date("Y-m-d"); ?>"
                                data-real-date="sec_con_serv_pub_txt_fecha_vencimiento">
                            <input type="text" class="form-control servicio_publico_fecha_emision_datepicker"
                                id="sec_con_serv_pub_txt_fecha_vencimiento" value="<?php echo date("d-m-Y");?>"
                                readonly="readonly" style="height: 34px;">
                            <label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="sec_con_serv_pub_txt_fecha_vencimiento"></label>
						</div>
					</div>

					<div class="form-group col-md-4">
						<label>Monto total:</label>
						<input type="text" class="form-control monto sec_con_serv_pub_monto_total_recibo" 
						id="sec_con_serv_pub_monto_total_recibo" style="height: 34px;" placeholder="0.00">
					</div>

					<div class="form-group col-md-6">
						<label>Número de Recibo:</label>
						<input type="text" class="form-control" 
						id="sec_con_serv_pub_num_recibo_nuevo" style="height: 34px;" placeholder="0123456" maxlength="10">

						<label for="fileLocalServicioPublico">Nombre file:</label>
						<div class="input-container">
							<input type="file" id="sec_serv_pub_file_archivo_recibo" name="sec_serv_pub_file_archivo_recibo" required multiple="multiple" accept=".jpeg, .jpg, .png, .pdf">

							<button class="browse-btn btn btn-info" id="sec_serv_pub_btn_buscar_archivo">
								Seleccionar
							</button>
							<span class="file-info" id="sec_serv_pub_file_info"></span>
						</div>
					</div>

					<!--<div class="form-group col-md-3">
						<label>Periodo consumo:</label>
						<div class="input-group" style="width: 100%">
						  <input type="month" class="form-control txt_locales_servicio_publico_periodo_consumo" id="txt_locales_servicio_publico_periodo_consumo" style="height: 34px;">
						</div>
					</div>-->

					<div class="form-group col-md-6">
						<label>¿Desea ingresar algun comentario?</label>
						<textarea class="form-control" name="sec_con_serv_pub_comentario_recibo" id="sec_con_serv_pub_comentario_recibo" maxlength="255" style = "text-transform: uppercase;" rows="4"></textarea>
						<small>Quedan <span id="sec_con_serv_pub_caracteres_comentario">255</span> caracteres</small>
					</div>

					
					<!--<div class="col-xs-6" style="margin-top: 20px;">
						<button type="submit" class="btn btn-block btn-success" id="btnGuardarLocalesServicioPublico">
							<i class="glyphicon glyphicon-saved" ></i>
							Agregar recibo
						</button>	
					</div>-->
				</form>
			</div>
			<br>
			<br>
			<br>
			<br>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger" class="btn btn-success" data-dismiss="modal">
					<i class="icon fa fa-close"></i>
					Cancelar
				</button>
				<button type="button" class="btn btn-success" id="sec_nuevo_modal_guardar_recibo">
					<i class="icon fa fa-save"></i>
					<span>Guardar</span>
				</button>
			</div>
		</div>
	</div>
</div>
<!-- FIN MODAL AGREGAR RECIBO -->

<!--MODAL INICIO: EXPORTAR PLANTILLA CONCAR-->
<div class="modal" id="contrato_servicio_publico_modal_parametro_plantilla_concar" data-backdrop="static" tabindex="false" style="">
    <div class="modal-dialog modal-md">
        <div class="modal-content modal-rounded">
            <div class="modal-header">
                <button type="button" class="close pull-right" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="">
                    Parametros
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <form id="contrato_servicio_publico_form_modal_parametro_plantilla_concar" autocomplete="off">
                            <input type="hidden" id="form_modal_sec_prestamo_boveda_param_tiene_num_cuenta" value="<?php echo $tiene_num_cuenta_input; ?>">
                            
                            <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12"
                                style="margin-bottom: 10px;">
                                <label>
                                    Número Correlativo (máximo 4 dígitos):
                                </label>
                                <div class="form-group">
                                    <input type="text" 
                                        class="form-control" 
                                        name="contrato_servicio_publico_modal_param_num_correlativo"
                                        id="contrato_servicio_publico_modal_param_num_correlativo"
                                        maxlength="4"
                                        autocomplete="off"
                                        placeholder="Ingrese el Número Correlativo">
                                </div>
                            </div>

                            <div class="form-group col-lg-12 col-md-12 col-sm-12 col-xs-12"
                                style="margin-bottom: 10px;">
                                <label>
                                    Fecha de Comprobante:
                                </label>
                                <div class="input-group">
	                                <input
                                        type="text"
                                        name="contrato_servicio_publico_modal_param_fecha_comprobante"
                                        id="contrato_servicio_publico_modal_param_fecha_comprobante"
                                        class="form-control servicio_publico_datepicker"
                                        value=""
                                        style="height: 30px;"
                                        >
	                                <label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="contrato_servicio_publico_modal_param_fecha_comprobante"></label>
	                            </div>

                            </div>

                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                
                <button class="btn btn-success btn_guardar">
					Exportar Plantilla
					<span class="icon fa fa-file-excel-o" style="font-size: 14px;"></span>
                </button>

                <button class="btn btn-default " data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!--MODAL FIN: EXPORTAR PLANTILLA CONCAR-->
