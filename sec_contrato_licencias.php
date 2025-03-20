<?php
	$this_menu = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' LIMIT 1")->fetch_assoc();
	$menu_id = $this_menu["id"];

	if(!array_key_exists($menu_id,$usuario_permisos))
	{
		echo "No tienes permisos para este recurso.";
		die();
	}


?>

<style>


	/* legend {
		background-color: #34495e;
		color: white;
		padding: 5px 10px;
	} */

	#table-files fieldset{
		background-color: white;
	}

	#table-files legend {
		background-color: white;
		color: black;
		padding: 0px 10px;
	}
	
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

	.cont_proveedor_datepicker {
    	min-height: 28px !important;
	}
	.dt-button {
        padding: 0;
        border: none;
    }
</style>
 

<?php 

$select_status = [
		"0" => "CONCLUIDO",
		"1" => "NO APLICA",
		"2" => "PENDIENTE",
		"3" => "TRAMITE"
	];

$select_condicion = [
		"0" => "INDEFINIDA",
		"1" => "TEMPORAL"
	];

?>

	<?php
	if($item_id)
	{
		$sql_query = $mysqli->query("
						SELECT c.contrato_id, c.nombre_tienda, tl.cc_id AS codigo_concar,
							  c.declaracion_jurada_id, dj.nombre as declaracion_jurada
					FROM cont_contrato c
						INNER JOIN tbl_locales tl 
						ON c.contrato_id = tl.contrato_id
						LEFT JOIN cont_declaracion_jurada dj
						ON c.declaracion_jurada_id = dj.id
					WHERE c.status = 1 and c.contrato_id = '".$item_id."'")->fetch_assoc();

		$squery_declaracion_jurada = $mysqli->query("SELECT id, nombre AS nombre_declaracion_jurada FROM cont_declaracion_jurada WHERE status = 1");

		?>
		
<div class="container-fluid">
	<div class="page-header wide">
		<div class="row">
			<div class="col-xs-12 text-center">
				<h1 class="page-title titulosec_contrato">Autorizaciones Municipales del Local - <?php echo $sql_query["nombre_tienda"]; ?> </h1>
			</div>
		</div>

		<div class="row container-fluid" style="padding: 0;">

			<div class="col-md-12">
				<div class="col-md-12" style="margin-bottom: 10px;">
					<a class="btn btn-primary" id="btnRegresar" href="./?sec_id=<?php echo $sec_id;?>&amp;sub_sec_id=<?php echo $sub_sec_id;?>">
						<i class="glyphicon glyphicon-arrow-left"></i>
						Regresar
					</a>
				</div>
				<br>

				<div class="col-xs-12 col-md-12 col-lg-12" style="margin: 0;">

					<!-- PANEL: Horizontal Form -->
					<div class="panel" id="divLicenciasMUnicipales">

						<!-- Panel Body -->
						<div class="panel-body" style="padding: 5px 5px 5px 5px;">

							<!-- PANEL-GROUP: Browsers -->
							<div class="panel-group no-mb" id="accordion" role="tablist" aria-multiselectable="true">

								<!-- PANEL 1 INICIO -->
								<div class="panel">

									<!-- Panel Heading -->
									<div class="panel-heading" role="tab" id="lic_funcionamiento_heading">

										<!-- Panel Title -->
										<div class="panel-title">
											<a href="#lic_funcionamiento_body" role="button" data-toggle="collapse"
											data-parent="#accordion" aria-expanded="true" aria-controls="lic_funcionamiento_body">
											Licencia de Funcionamiento:
											</a>
										</div>
										<!-- /Panel Title -->
									</div>
									<!-- /Panel Heading -->

									<!-- COLLAPSE: This Week -->
									<div id="lic_funcionamiento_body" class="panel-collapse collapse in" role="tabpanel"
										aria-labelledby="lic_funcionamiento_heading">

										<!-- Panel Body -->
										<div class="panel-body">
												<div class="row">
												
													<div class="col-md-8">
													<fieldset class="dhhBorder">
				
													<legend class="dhhBorder">Registro</legend>
														<form id="formularioFuncionamiento" method="POST" enctype="multipart/form-data">
															<input type="hidden" id="contrato_id" name="contrato_id" value="<?php echo $sql_query["contrato_id"]; ?>">
															<input type="hidden" id="contrato_nombre_local" name="contrato_nombre_local" value="<?php echo $sql_query["nombre_tienda"]; ?>">

														

																<div class="form-group col-md-3">
																	<label>Estado de licencia:</label>
																	<select name="txtLicFuncionamiento" id="txtLicFuncionamiento" class='form-control input-sm'>
																		<option value=''>Seleccione</option>
																		<?php foreach ($select_status as $key => $value) : ?>
																			<option value="<?= $value ?>"><?= $value ?></option>
																		<?php endforeach; ?>
																	</select>
																</div>

																<div class="form-group col-md-3" id="cont_licencias_div_condicion_funcionamiento" style="display: none;">
																	<label>Condicion de licencia:</label>
																	<select name="txtCondicionLicFuncionamiento" id="txtCondicionLicFuncionamiento" class='form-control input-sm'>
																		<option value=''>Seleccione</option>
																		<?php foreach ($select_condicion as $key => $value) : ?>
																			<option value="<?= $value ?>"><?= $value ?></option>
																		<?php endforeach; ?>
																	</select>
																</div>

																<div class="form-group col-md-3" id="cont_licencias_div_fecha_vencimiento_funcionamiento" style="display: none;">
																	<label>Fecha vencimiento:</label>
																	<div class="input-group">
																		<input type="hidden" id="contrato_vigencia_inicio_fecha" class="filtro" data-col="fecha_inicio" name="fecha_inicio" value="<?php echo date("Y-m-d"); ?>" data-real-date="txtFechaVencimientoLicFuncionamiento">
																		<input type="text" class="form-control licencia_funcionamiento_datepicker" id="txtFechaVencimientoLicFuncionamiento" value="<?php echo date("d-m-Y"); ?>" readonly="readonly" style="height: 34px;">
																		<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="txtFechaVencimientoLicFuncionamiento"></label>
																	</div>

																</div>

																<div class="form-group col-md-3" id="cont_licencias_div_fecha_renovacion_funcionamiento" style="display: none;">
																	<label>Fecha de renovación:</label>

																	<div class="input-group">
																		<input type="hidden" id="contrato_vigencia_inicio_fecha" class="filtro" data-col="fecha_inicio" name="fecha_inicio" value="<?php echo date("Y-m-d"); ?>" data-real-date="txtFechaRenovacionLicFuncionamiento">
																		<input type="text" class="form-control licencia_funcionamiento_datepicker" id="txtFechaRenovacionLicFuncionamiento" value="<?php echo date("d-m-Y"); ?>" readonly="readonly" style="height: 34px;">
																		<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="txtFechaRenovacionLicFuncionamiento"></label>
																	</div>

																</div>

																<div class="form-group col-md-4" id="cont_licencias_div_file_funcionamiento" style="display: none;">
																	<label for="fileArchivoLicFuncionamiento">Nombre file:</label>
																	<div class="input-container">

																		<input type="file" id="fileArchivoLicFuncionamiento" name="fileArchivoLicFuncionamiento"  accept='.jpeg, .jpg, .png, .pdf'>

																		<button class="browse-btn" id="btnBuscarFileFuncionamiento">
																			Seleccionar
																		</button>

																		<span class="file-info" id="txtFileLicFuncionamiento"></span>
																	</div>
																</div>

																<!-- <div class="form-group col-md-8" id="divMensajeAlertaLicFuncionamiento">
																</div> -->
																				 
																<div class="form-group col-md-12">
																	<button type="submit" class="btn btn-success loat-right" id="btnGuardarLicenciasFuncionamiento">
																		<i class="glyphicon glyphicon-floppy-disk"></i>
																		Guardar
																	</button>
																</div>
															
														</form>
													</fieldset>
														
													</div>
													<div class="col-md-4">
														<fieldset class="dhhBorder">
					
														<legend class="dhhBorder">Dirección Municipal:</legend>
															<div class="form-group" id="cont_licencias_div_file_funcionamiento">

																	<div class="row">
																		
																		<?php
																		$query_direccion_municipal = "SELECT 
																	i.id,i.contrato_id ,i.direccion_municipal  
																	FROM  cont_inmueble i
																	inner join cont_contrato cc 
																	on cc.contrato_id =i.contrato_id
																	where i.contrato_id =" . $item_id;
																		$sel_query_direccion_municipal =  $mysqli->query($query_direccion_municipal);

																		while ($sel = $sel_query_direccion_municipal->fetch_assoc()) {
																			$direccion_municipal = htmlspecialchars($sel["direccion_municipal"]);
																		}
																		if ($login['area_id'] == 33 && $login['cargo_id'] == 25) {
																		?>
																			<div class="col-md-12 mb-1">
																				<textarea class="form-control" id="direccion_municipal" name="direccion_municipal" rows="3"><?php echo $direccion_municipal ?></textarea>

																			</div>
																			

																			<div class="col-md-6">
																				<a onclick="sec_contrato_detalle_solicitud_guardar_direccion_municipal();" class="btn btn-rounded btn-success"><span class="glyphicon glyphicon-floppy-disk"></span>Guardar</a>
																			</div>
																		<?php } ?>

																	</div>

																</div>
														</fieldset>
														
													</div>
													
													<div class="col-md-12 table-responsive" id="table-files">
															<fieldset class="dhhBorder">
															<legend class="dhhBorder">Historial</legend>
															
															<table id="licencias_detalle_funcionamiento_datatable" class="table table-striped table-hover table-condensed table-bordered">
																<thead>
																	<tr>
																		<th ></th>
																		<th ></th>
																		<th ></th>
																		<th ></th>
																		<th ></th>
																		<th ></th>
																		<th ></th>
																	</tr>
																</thead>
																<tbody>
																
																</tbody>
															</table>

															</fieldset>

													</div>

												</div>
												
												
												
												
												
												

											
										</div>
										<!-- /Panel Body -->
									</div>
									<!-- /COLLAPSE: This Week -->
								</div>
								<!-- PANEL 1 FIN -->

								<!-- PANEL 2 INICIO -->
								<div class="panel">

									<!-- PANEL CABECERA INICIO -->
									<div class="panel-heading" role="tab" id="lic_indeci_heading">

										<!-- Panel inicio titulo -->
										<div class="panel-title">                                        
											<a href="#lic_indeci_body" class="collapsed" role="button" data-toggle="collapse"
											data-parent="#accordion" aria-expanded="true" aria-controls="lic_indeci_body">

											Certificado de Indeci:
											</a>
										</div>
										<!-- /Panel fin titulo -->

									</div>
									<!-- PANEL CABECERA FIN -->

									<!-- COLLAPSE: This Week -->
									<div id="lic_indeci_body" class="panel-collapse collapse" role="tabpanel"
									aria-labelledby="lic_indeci_heading">

										<!-- Panel Body -->
										<div class="panel-body">
										<fieldset class="dhhBorder">
				
										<legend class="dhhBorder">Registro</legend>
											<form id="formularioIndeci" method="POST" enctype="multipart/form-data">
												<input type="hidden" id="contrato_id" name="contrato_id" value="<?php echo $sql_query["contrato_id"]; ?>">
												<input type="hidden" id="contrato_nombre_local" name="contrato_nombre_local" value="<?php echo $sql_query["nombre_tienda"]; ?>">
												<fieldset>

													<div class="form-group col-md-2">
														<label>Estado de licencia:</label>
														<select name="txtLicIndeci" id="txtLicIndeci" class='form-control input-sm'>
															<option value=''>Seleccione</option>
															
															<?php foreach($select_status as $key => $value) :?>
															<option value="<?= $value ?>" ><?= $value ?></option>

															<?php endforeach; ?>
														</select>
													</div>

													<div class="form-group col-md-3" id="cont_licencias_div_fecha_vencimiento_indeci" style="display: none;">
														<label>Fecha vencimiento:</label>
														<div class="input-group">
															<input
																type="hidden"
																id="contrato_vigencia_inicio_fecha"
																class="filtro"
																data-col="fecha_inicio"
																name="fecha_inicio"
																value="<?php echo date("Y-m-d"); ?>"
																data-real-date="txtFechaVencimientoLicIndeci">
															<input
																type="text"
																class="form-control licencia_funcionamiento_datepicker"
																id="txtFechaVencimientoLicIndeci"
																value="<?php echo date("d-m-Y");?>"
																readonly="readonly"
																style="height: 34px;"
																>
															<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="txtFechaVencimientoLicIndeci"></label>
														</div>

													</div>

													<div class="form-group col-md-3" id="cont_licencias_div_fecha_renovacion_indeci" style="display: none;">
														<label>Fecha de renovación:</label>
														
														<div class="input-group">
															<input
																type="hidden"
																id="contrato_vigencia_inicio_fecha"
																class="filtro"
																data-col="fecha_inicio"
																name="fecha_inicio"
																value="<?php echo date("Y-m-d"); ?>"
																data-real-date="txtFechaRenovacionLicIndeci">
															<input
																type="text"
																class="form-control licencia_funcionamiento_datepicker"
																id="txtFechaRenovacionLicIndeci"
																value="<?php echo date("d-m-Y");?>"
																readonly="readonly"
																style="height: 34px;"
																>
															<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="txtFechaRenovacionLicIndeci"></label>
														</div>

													</div>

													<div class="form-group col-md-4" id="cont_licencias_div_file_indeci" style="display: none;">
														<label for="fileArchivoLicIndeci">Nombre file:</label>
														<div class="input-container">

															<input type="file" id="fileArchivoLicIndeci" name ="fileArchivoLicIndeci" multiple="multiple" accept='.jpeg, .jpg, .png, .pdf'>

															<button class="browse-btn" id="btnBuscarFileIndeci">
															Seleccionar
															</button>

															<span class="file-info" id="txtFileLicIndeci"></span>
														</div>
													</div>
													<br>

													<div class="form-group col-md-12" id="divMensajeAlertaLicIndeci">
													</div>

													<div class="col-md-12">
														<button type="submit" class="btn btn-success loat-right" id="btnGuardarLicenciasIndeci">
														<i class="glyphicon glyphicon-floppy-disk" ></i>
														Guardar
														</button>
													</div>
												</fieldset>
											</form>
										</fieldset>
											
										
											<div class="form-group col-md-12 table-responsive" id="table-files" style="margin-top: 20px; padding: 0;">
												<fieldset class="dhhBorder">
													<legend class="dhhBorder">Historial</legend>
											
													<table id="licencias_detalle_indeci_datatable" class="table table-hover table-bordered">
														<thead>
															<tr>
																<th ></th>
																<th ></th>
																<th ></th>
																<th ></th>
																<th ></th>
																<th ></th>
																<th ></th>
															</tr>
														</thead>
														<tbody>
														
														</tbody>
													</table>
												</fieldset>
											</div>
										</div>
										<!-- /Panel Body -->

									</div>
									<!-- /COLLAPSE: This Week -->
								</div>
								<!-- PANEL 2 FIN -->

								<!-- PANEL 3 INICIO -->
								<div class="panel">

									<!-- Panel Heading -->
									<div class="panel-heading" role="tab" id="lic_publicidad_heading">

										<!-- Panel Title -->
										<div class="panel-title">                                        
											<a href="#lic_publicidad_body" class="collapsed" role="button" data-toggle="collapse"
											data-parent="#accordion" aria-expanded="true" aria-controls="lic_publicidad_body">

											Autorización de Anuncio Publicitario:
											</a>
										</div>
										<!-- /Panel Title -->

									</div>
									<!-- /Panel Heading -->

									<!-- COLLAPSE: This Week -->
									<div id="lic_publicidad_body" class="panel-collapse collapse" role="tabpanel"
									aria-labelledby="lic_publicidad_heading">

										<!-- Panel Body -->
										<div class="panel-body">
										<fieldset class="dhhBorder">
											<legend class="dhhBorder">Registro</legend>
											<form id="formularioPublicidad" method="POST" enctype="multipart/form-data">
												<input type="hidden" id="contrato_id" name="contrato_id" value="<?php echo $sql_query["contrato_id"]; ?>">
												<input type="hidden" id="contrato_nombre_local" name="contrato_nombre_local" value="<?php echo $sql_query["nombre_tienda"]; ?>">
												<fieldset>
													<div class="form-group col-md-2">
														<label>Estado de licencia:</label>
														<select name="txtLicPublicidad" id="txtLicPublicidad" class='form-control input-sm'>
															<option value=''>Seleccione</option>
															 <?php foreach($select_status as $key => $value) :?>
																	<option value="<?= $value ?>" ><?= $value ?></option>
																<?php endforeach; ?>
														</select>
													</div>
										
													<div class="form-group col-md-2" id="cont_licencias_div_condicion_publicidad" style="display: none;">
														<label>Condicion de licencia:</label>
														<select name="txtCondicionLicPublicidad" id="txtCondicionLicPublicidad" class='form-control input-sm'>
															<option value=''>Seleccione</option>
															 <?php foreach($select_condicion as $key => $value) :?>
																<option value="<?= $value ?>" ><?= $value ?></option>
													<?php endforeach; ?>
														</select>

													</div>
													
													<div class="form-group col-md-2" id="cont_licencias_div_fecha_vencimiento_publicidad" style="display: none;">
														<label>Fecha vencimiento:</label>
														<div class="input-group">
															<input
																type="hidden"
																id="contrato_vigencia_inicio_fecha"
																class="filtro"
																data-col="fecha_inicio"
																name="fecha_inicio"
																value="<?php echo date("Y-m-d"); ?>"
																data-real-date="txtFechaVencimientoLicPublicidad">
															<input
																type="text"
																class="form-control licencia_funcionamiento_datepicker"
																id="txtFechaVencimientoLicPublicidad"
																value="<?php echo date("d-m-Y");?>"
																readonly="readonly"
																style="height: 34px;">
															<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="txtFechaVencimientoLicPublicidad">
															</label>
														</div>
													</div>

													<div class="form-group col-md-2" id="cont_licencias_div_fecha_renovacion_publicidad" style="display: none;">
														<label>Fecha de renovación:</label>
														  
														<div class="input-group">
															<input
																type="hidden"
																id="contrato_vigencia_inicio_fecha"
																class="filtro"
																data-col="fecha_inicio"
																name="fecha_inicio"
																value="<?php echo date("Y-m-d"); ?>"
																data-real-date="txtFechaRenovacionLiPublicidad">
															<input
																type="text"
																class="form-control licencia_funcionamiento_datepicker"
																id="txtFechaRenovacionLiPublicidad"
																value="<?php echo date("d-m-Y");?>"
																readonly="readonly"
																style="height: 34px;"
																>
															<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="txtFechaRenovacionLiPublicidad"></label>
														</div>
													</div>
													  
													<div class="form-group col-md-4" id="cont_licencias_div_file_publicidad" style="display: none;">
														<label for="fileArchivoLicPublicidad">Nombre file:</label>
														<div class="input-container">
															
														<input type="file" id="fileArchivoLicPublicidad" name ="fileArchivoLicPublicidad" multiple="multiple" accept='.jpeg, .jpg, .png, .pdf'>
															
															<button class="browse-btn" id="btnBuscarFilePublicidad">
																Seleccionar
															</button>
															
															<span class="file-info" id="txtFileLicPublicidad"></span>
														</div>
													</div>
													<br>

													<div class="form-group col-md-12" id="divMensajeAlertaLicPublicidad">
													</div>

													<div class="col-md-12">
														<button type="submit" class="btn btn-success loat-right" id="btnGuardarLicenciasPublicidad">
															<i class="glyphicon glyphicon-floppy-disk" ></i>
															Guardar
														</button>
												   </div>

												</fieldset>
											</form>
										</fieldset>
											
											
											<div class="form-group col-md-12 table-responsive" id="table-files" style="margin-top: 20px; padding: 0;">
											
												<fieldset class="dhhBorder">
													<legend class="dhhBorder">Historial:</legend>
												
													<table id="licencias_detalle_publicidad_datatable" class="table table-hover table-bordered">
														<thead>
															<tr>
																<th ></th>
																<th ></th>
																<th ></th>
																<th ></th>
																<th ></th>
																<th ></th>
																<th ></th>
															</tr>
														</thead>
														<tbody>
														
													  </tbody>
													</table>
												</fieldset>
											</div>
											<!-- /Panel Body -->
										</div>
										<!-- /COLLAPSE: This Week -->
									</div>
								</div>
								<!-- PANEL 3 FIN -->

								<!-- PANEL 4 INICIO -->
								<div class="panel">

									<!-- Panel Heading -->
									<div class="panel-heading" role="tab" id="lic_declaracion_jurada_heading">

										<!-- Panel Title -->
										<div class="panel-title">                                        
											<a href="#lic_declaracion_jurada_body" class="collapsed" role="button" data-toggle="collapse"
											data-parent="#accordion" aria-expanded="true" aria-controls="lic_declaracion_jurada_body">

											Declaracion jurada de actividades simultaneas:
											</a>
										</div>
										<!-- /Panel Title -->

									</div>
									<!-- /Panel Heading -->

									<!-- COLLAPSE: This Week -->
									<div id="lic_declaracion_jurada_body" class="panel-collapse collapse" role="tabpanel"
									aria-labelledby="lic_declaracion_jurada_heading">

										<!-- Panel Body -->
										<div class="panel-body">

											<form id="formularioDeclaracionJurada" method="POST" enctype="multipart/form-data">
												
												<input type="hidden" id="contrato_id" name="contrato_id" value="<?php echo $sql_query["contrato_id"]; ?>">
												<input type="hidden" id="contrato_nombre_local" name="contrato_nombre_local" value="<?php echo $sql_query["nombre_tienda"]; ?>">
												
													
													<div class="col-md-12" id="divRegistrarDeclaracionJurada">
														<fieldset class="dhhBorder">
				
														<legend class="dhhBorder">Registro</legend>
															<div class="form-group col-md-6" >
																<label class="control-label">Seleccionar giros:</label>
																<div class="input-group">
																		<input type="hidden" id="declaracion_jurada_id" value="<?php echo $sql_query["declaracion_jurada_id"]?>">
																		<select name="txtDeclaracionJurada" id="txtDeclaracionJurada" class='form-control select2'>
																			<option value="">Seleccione Giro</option>
																			
																		</select>
																		<label class="input-group-addon glyphicon glyphicon-new-window" id="select_add_giro_btn" data-cols="nombre" title="Nuevo Giro"></label>
																
																</div>
																			
															</div>
															 					
															<div class="form-group col-md-4">
																<label class="control-label">Nombre file:</label>
																<div class="input-container ">

																	<input type="file" id="fileArchivoLicDeclaracionJurada" name ="fileArchivoLicDeclaracionJurada" multiple="multiple" accept='.jpeg, .jpg, .png, .pdf'>

																	<button class="browse-btn" id="btnBuscarFileDeclaracionJurada">
																		Seleccionar
																	</button>

																	<span class="file-info" id="txtFileLicDeclaracionJurada"></span>
																</div>
															</div>
															
															<div class="form-group col-md-12">
																
																<button type="submit" class="btn btn-success loat-left" id="btnGuardarLicenciasDeclaracionJurada">
																	<i class="glyphicon glyphicon-floppy-disk" ></i>
																	Guardar
																</button>
															</div>
															<div class="form-group col-md-12" id="divMensajeAlertaDeclaracionJurada">
															</div>
														</fieldset>
													
													</div>

													<div class="col-md-12" id="divRegistrarDeclaracionJurada">
														<fieldset class="dhhBorder">
																
																<legend class="dhhBorder">Historial</legend>
																<div class="form-group col-md-12 table-responsive" id="table-files" style="margin-top: 20px; padding: 0;">
																
																<table id="licencias_detalle_declaracion_jurada_datatable" class="table table-hover table-bordered">
																	<thead>
																		<tr>
																			<th></th>
																			<th></th>
																			<th></th>
																		</tr>
																	</thead>
																	<tbody>
																	
																	</tbody>
																</table>
															</div>
														</fieldset>
													</div>
													
													

												
												<!-- /Panel Body -->
											</form>
										</div>
										<!-- /COLLAPSE: This Week -->
									</div>
								</div>
								<!-- PANEL 4 FIN -->
							</div>
						<!-- /Panel Body -->

						</div>
						<!-- PANEL: Horizontal Form -->

					</div>
				</div>
			</div>

	</div>

</div>

	
</div>

	</div>
</div>	
		<?php
	}
	else
	{

		$list = $mysqli->query("
		SELECT 
			c.contrato_id, 
			c.nombre_tienda, 
			tl.cc_id AS codigo_concar, 
			ce.fecha_inicio,
			lmf.status_licencia AS licencia_funcionamiento, 
			lmf.condicion AS licencia_funcionamiento_condicion, 
			lmf.fecha_vencimiento AS licencia_funcionamiento_vencimiento, 
			lmf.fecha_renovacion AS licencia_funcionamiento_renovacion,
			lmi.status_licencia AS indeci, 
			lmi.fecha_vencimiento AS indeci_vencimiento, 
			lmi.fecha_renovacion AS indeci_renovacion,
			lmp.status_licencia AS anuncio_publicitario, 
			lmp.condicion AS anuncio_publicitario_condicion, 
			lmp.fecha_vencimiento AS anuncio_publicitario_vencimiento, 
			lmp.fecha_renovacion AS anuncio_publicitario_renovacion,
			dj.nombre AS declaracion_jurada,
			inm.direccion_municipal
		FROM 
			cont_contrato c
			LEFT JOIN cont_inmueble inm ON inm.contrato_id=c.contrato_id
			INNER JOIN cont_condicion_economica ce ON c.contrato_id = ce.contrato_id
			INNER JOIN tbl_locales tl ON c.contrato_id = tl.contrato_id
			LEFT JOIN cont_declaracion_jurada dj ON c.declaracion_jurada_id = dj.id
			LEFT JOIN cont_licencia_municipales lmf ON (lmf.contrato_id = c.contrato_id AND lmf.estado = 1 AND lmf.tipo_archivo_id = 4)
			LEFT JOIN cont_licencia_municipales lmi ON (lmi.contrato_id = c.contrato_id AND lmi.estado = 1 AND lmi.tipo_archivo_id = 5)
			LEFT JOIN cont_licencia_municipales lmp ON (lmp.contrato_id = c.contrato_id AND lmp.estado = 1 AND lmp.tipo_archivo_id = 6)
		WHERE 
			c.status = 1 
			AND c.etapa_id = 5
		");


		$list_cols=array();

		// $list_cols["contrato_id"] = "ID";
		$list_cols["codigo_concar"] = "C.C. concar";

		$list_cols["nombre_tienda"] = "Nombre tienda";
		$list_cols["fecha_inicio"] = "F. Inicio Contrato";
		$list_cols["licencia_funcionamiento"] = "Lic. Funcionamiento";
		$list_cols["licencia_funcionamiento_condicion"] = "condicion lic. funcionamiento";
		$list_cols["licencia_funcionamiento_vencimiento"] = "F. vencimiento lic. funcionamiento";
		$list_cols["licencia_funcionamiento_renovacion"] = "F.renovacion lic. funcionamiento";
		$list_cols["indeci"] = "Lic. Indeci";
		$list_cols["indeci_vencimiento"] = "F. vencimiento indeci";
		$list_cols["indeci_renovacion"] = "F. renovacion indeci";
		$list_cols["anuncio_publicitario"] = "Lic. Publicidad";
		$list_cols["anuncio_publicitario_condicion"] = "condicion anun. publicitario";
		$list_cols["anuncio_publicitario_vencimiento"] = "F. vencimiento anun. publicitario";
		$list_cols["anuncio_publicitario_renovacion"] = "F. renovacion anun. publicitario";
		$list_cols["declaracion_jurada"] = "Declaracion jurada";
		$list_cols["direccion_municipal"] = "Dirección municipal";

		$list_cols["licencias"]="Licencias";
		
		?>
		<?php
		/*$view_cols = array("id","fecha_registro","canal_de_venta","contrato_tipo","cliente");*/

		$view_cols = array("contrato_id", "nombre_tienda","direccion_municipal" ,"codigo_concar", "fecha_inicio", "licencia_funcionamiento", "indeci", "anuncio_publicitario", "declaracion_jurada");
		
		if(isset($_POST["contratos_list_cols_submit"]))
		{
			if(isset($_POST["contratos_list_cols"]))
			{
				$view_cols=$_POST["contratos_list_cols"];
			}
			else
			{
				$view_cols=array();
			}
		}
		elseif(isset($_COOKIE["contratos_list_cols"]))
		{
			$view_cols = json_decode($_COOKIE['contratos_list_cols'], true);
		}
		else
		{
			//$list_cols_show = $view_cols;
		}

		$view_cols[]="contrato_id";
		$view_cols[]="licencias";
		
		foreach ($list_cols as $key => $value) 
		{
			if(in_array($key, $view_cols))
			{
				$list_cols_show[$key]=$value;
			}
		}

		?>
<div class="container-fluid contratos_form_licencias">
		<div class="page-header wide">
				<div class="row">
					<div class="col-xs-12 text-center">
						<h1 class="page-title titulosec_contrato"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Licencias municipales de locales</h1>
					</div>
				</div>
				<div class="row">
					<div class="col-xs-12 ">
						<button type="button" class="btn btn-success" data-toggle="modal" data-target="#filter_holder">
						<i class="glyphicon glyphicon-search"></i>
							Elegir Columnas
						</button>
						<?php 
							if($item_id)
							{
								?>
								<h1>fdrffdfds</h1>
								<?php
							}
						?>
					</div>
				</div>
				<div class="row">
					<div class="col-12">

						<table id="cont_locales_licencias_datatable" class="table table-striped table-hover table-condensed table-bordered dt-responsive display" cellspacing="0" width="100%">
							<thead>
								
								<tr>
									<?php
									foreach ($list_cols_show as $key => $value) 
									{
										if($key=="contrato_id")
										{
											?><th class="text-center" id="th_<?php echo $key;?>" class="w-25px">ID</th><?php
										}
										elseif($key=="licencias")
										{
										?>
											<th id="th_<?php echo $key;?>" class="text-center">
													<?php echo $value;?>
											</th>
										<?php
										}
										
										elseif($key=="licencia_funcionamiento" || $key=="indeci" || $key=="anuncio_publicitario")
										{
											?>
												<th id="th_<?php echo $key;?>" class="text-center">
														<?php if($key=='licencia_funcionamiento'){
															$key='Estado Licencia </br> de funcionamiento';
														}else if($key=='indeci'){
															$key='Estado Indeci';
														}else{
															$key='Estado Anuncio publicitario';
															
														}
														;?>
														 <?php echo $key;?>
												</th>
											<?php
										}

										elseif($key=="licencia_funcionamiento_condicion" || $key=="anuncio_publicitario_condicion")
										{
											?>
												<th id="th_<?php echo $key;?>" class="text-center">
												<?php if($key=='licencia_funcionamiento_condicion'){
															$key='Condición de Licencia </br> de funcionamiento';
														}else if($key=="anuncio_publicitario_condicion"){
															$key='Condición de Anuncio publicitario';
															
														}
														;?>
														 <?php echo $key;?>
												</th>
											<?php
										}

										elseif($key=="licencia_funcionamiento_vencimiento" || $key=="indeci_vencimiento" || $key=="anuncio_publicitario_vencimiento")
										{
											?>
												<th id="th_<?php echo $key;?>" class="text-center">
												<?php if($key=='licencia_funcionamiento_vencimiento'){
															$key='Vencimiento Licencia </br> de funcionamiento';
														}else if($key=='indeci_vencimiento'){
															$key='Vencimiento Indeci';
														}else if($key=='anuncio_publicitario_vencimiento'){
															$key='Vencimiento Anuncio publicitario';
															
														}
														;?>
														 <?php echo $key;?>
												</th>
											<?php
										}

										elseif($key=="licencia_funcionamiento_renovacion" || $key=="indeci_renovacion" || $key=="anuncio_publicitario_renovacion")
										{
											?>
												<th id="th_<?php echo $key;?>" class="text-center">
												<?php if($key=='licencia_funcionamiento_renovacion'){
															$key='Fecha de renovación </br> Licencia de funcionamiento';
														}else if($key=='indeci_renovacion'){
															$key='Fecha de renovación  Indeci';
														}else if($key=="anuncio_publicitario_renovacion"){
															$key='Fecha de renovación Anuncio publicitario';
															
														}
														;?>
													   <?php echo $key;?>
												</th>
											<?php
										}



										else
										{

											?>
											<th class="text-center" id="th_<?php echo $key;?>"><?php echo $value;?></th>
											<?php
										}
									}
									?>		                    
								</tr>
							</thead>
							           
							<tbody>
								<?php 
								foreach ($list as $l_k => $l_v) 
								{
									?>	
									<tr>			                	
										<?php
										foreach ($list_cols_show as $key => $value) 
										{
											if($key=="licencias")
											{
												?>
												<td class="text-center">
													
													<a 
														class="btn btn-rounded btn-primary btn-sm btn-edit btn_editar_contratossss" 
														title="Editar"
														data-button="edit"
														data-href="./?sec_id=<?php echo $sec_id;?>&amp;item_id=<?php echo $l_v["contrato_id"];?>"
														href="./?sec_id=<?php echo $sec_id;?>&amp;sub_sec_id=<?php echo $sub_sec_id;?>&amp;item_id=<?php echo $l_v["contrato_id"];?>">
														<i class="glyphicon glyphicon-edit"></i>												
													</a>

												</td>
												<?php
											}
											elseif($key=="contrato_id")
											{
												?>
												<td class="text-center"><?php echo $l_v[$key];?></td>
												<?php
											}
											else
											{
												?>
												<td><?php echo $l_v[$key];?></td>
												<?php
											}
										}
										?>
									</tr>



									<?php
									
								}
								?>


							</tbody>

						</table>
					</div>
				</div>
		</div>

		<!-- Modal -->
		<div class="modal fade dataTables_wrapper" id="filter_holder" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
			<div class="modal-dialog modal-sm" role="document">
				<div class="modal-content modal-rounded">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="myModalLabel">Elegir Columnas</h4>
					</div>
					<div class="modal-body pre-scrollable">
						<div class="row">
							<form class="form" method="post" id="contratos_list_cols">
								<div class="col-xs-12">
									 <div class="form-group">
										<div class="input-group">
											<input type="text" class="form-control list-filter-input" data-list="col_select_list" id="filtro" placeholder="Busqueda" autofocus autocomplete="off">
											<div class="input-group-addon"><span class="glyphicon glyphicon-search"></span></div>
										</div>
									</div>
								</div>
								<ul class="col-xs-12" id="col_select_list">
									<?php
									foreach ($list_cols as $key => $value) {
										?>
										<li class="checkbox visible_input">
											<label>
												<input type="checkbox" value="<?php echo $key;?>" name="contratos_list_cols[]"
													<?php 
													if(in_array($key, array("contrato_id", "licencias")))
													{ 
														?>
															disabled="disabled"
														<?php 
													} 
													?>
													<?php 
													if(in_array($key, $view_cols))
													{ 
														?>
															checked="checked"
														<?php 
													} 
													?>>
													<?php echo $value;?>
											</label>
										</li>
										<?php
									}
									?>
								</ul>
							</form>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
						<button type="submit" class="btn btn-success" name="contratos_list_cols_submit" form="contratos_list_cols"><span class="glyphicon glyphicon-filter"></span> Filtrar</button>
					</div>
				</div>
			</div>
		</div>
</div>

		<?php
	}
	?>

<!-- INICIO LECTOR PANTALLA COMPLETA -->
<div class="modal fade right" id="exampleModalPreviewServicio" tabindex="-1" role="dialog" aria-labelledby="exampleModalPreviewLabel" aria-hidden="true" style="width: 100%; padding-left: 0px;">
    <div class="modal-dialog-full-width modal-dialog momodel modal-fluid" role="document" style="width: 100%; margin: 10px auto;">
        <div class="modal-content-full-width modal-content " style="background-color: rgb(0 0 0 / 0%) !important;">
            <div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center;margin-top: 5px; margin-bottom:5px;">
              <button type="button" class="btn btn-block btn-danger" data-dismiss="modal"><i class="fa fa-close"></i> Cerrar Visor</button>
            </div>
            <div class="modal-body" style="padding: 0px;" id="cont_licienciasDivVisorPdfModal">
            </div>
            <div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center;margin-top: 5px; margin-bottom: 5px;">
              <button type="button" class="btn btn-block btn-danger" data-dismiss="modal"><i class="fa fa-close"></i> Cerrar Visor</button>   
            </div>
            <div class="col-xs-12 col-md-12 col-sm-12"></div>
        </div>
    </div>
</div>
<!-- FIN LECTOR PANTALLA COMPLETA -->
<!-- MODAL PARA EDITAR REGISTROS  -->
<div class="modal fade" id="modal_editar_licencia" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h5 class="modal-title" id="mdCrearGrupoTitle">Editar licencia</h5>
			</div>
			<div class="modal-body">
				<form id="formularioFuncionamiento_edit" method="POST" enctype="multipart/form-data">
					<input type="hidden" id="contrato_id_edit" name="contrato_id_edit" value="<?php echo $sql_query["contrato_id"]; ?>">
					<input type="hidden" id="contrato_nombre_local_edit" name="contrato_nombre_local_edit" value="<?php echo $sql_query["nombre_tienda"]; ?>">
					<input type="hidden" id="contrato_licencia_id" name="contrato_licencia_id" value="">

					<fieldset>

						<div class="form-group col-md-3">
							<label>Estado de licencia:</label>
							<select name="txtLicFuncionamiento" id="txtLicFuncionamiento_edit" class='form-control input-sm'>
								<option value=''>Seleccione</option>
								<?php foreach ($select_status as $key => $value) : ?>
									<option value="<?= $value ?>"><?= $value ?></option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="form-group col-md-3" id="cont_licencias_div_condicion_funcionamiento_edit" style="display: none;">
							<label>Condicion de licencia:</label>
							<select name="txtCondicionLicFuncionamiento" id="txtCondicionLicFuncionamiento_edit" class='form-control input-sm'>
								<option value=''>Seleccione</option>
								<?php foreach ($select_condicion as $key => $value) : ?>
									<option value="<?= $value ?>"><?= $value ?></option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="form-group col-md-3" id="cont_licencias_div_fecha_vencimiento_funcionamiento_edit" style="display: none;">
							<label>Fecha vencimiento:</label>
							<div class="input-group">
								<input type="hidden" id="contrato_vigencia_inicio_fecha" class="filtro" data-col="fecha_inicio" name="fecha_inicio" value="<?php echo date("Y-m-d"); ?>" data-real-date="txtFechaVencimientoLicFuncionamiento">
								<input type="text" class="form-control licencia_funcionamiento_datepicker" id="txtFechaVencimientoLicFuncionamiento_edit" value="<?php echo date("d-m-Y"); ?>" readonly="readonly" style="height: 34px;">
								<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="txtFechaVencimientoLicFuncionamiento"></label>
							</div>

						</div>

						<div class="form-group col-md-3" id="cont_licencias_div_fecha_renovacion_funcionamiento_edit" style="display: none;">
							<label>Fecha de renovación:</label>

							<div class="input-group">
								<input type="hidden" id="contrato_vigencia_inicio_fecha" class="filtro" data-col="fecha_inicio" name="fecha_inicio" value="<?php echo date("Y-m-d"); ?>" data-real-date="txtFechaRenovacionLicFuncionamiento">
								<input type="text" class="form-control licencia_funcionamiento_datepicker" id="txtFechaRenovacionLicFuncionamiento_edit" value="<?php echo date("d-m-Y"); ?>" readonly="readonly" style="height: 34px;">
								<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="txtFechaRenovacionLicFuncionamiento"></label>
							</div>

						</div>

						<div class="form-group col-md-6" id="cont_licencias_div_file_funcionamiento_edit" style="display: none;">
							<label for="fileArchivoLicFuncionamiento_edit">Nombre file:</label>
							<div class="input-container">

								<input type="file" id="fileArchivoLicFuncionamiento_edit" name="fileArchivoLicFuncionamiento_edit" multiple="multiple" accept='.jpeg, .jpg, .png, .pdf'>

								<button class="browse-btn" id="btnBuscarFileFuncionamiento_edit">
									Seleccionar
								</button>

								<span class="file-info" id="txtFileLicFuncionamiento_edit"></span>
							</div>
						</div>

						<div class="form-group col-md-12" id="divMensajeAlertaLicFuncionamiento">
						</div>

						<div class="form-group col-md-12">
							<button type="submit" class="btn btn-success loat-right" id="btnGuardarLicenciasFuncionamiento">
								<i class="glyphicon glyphicon-floppy-disk"></i>
								Guardar
							</button>
						</div>
					</fieldset>
				</form>
			</div>
			<div class="modal-footer">
				<div class="form-group ">
					<button class="btn btn-default" data-dismiss="modal">Cancelar</button>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal_editar_cert_indeci" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h5 class="modal-title" id="mdCrearGrupoTitle">Editar Certificado</h5>
			</div>
			<div class="modal-body">
				<form id="formularioIndeci_edit" method="POST" enctype="multipart/form-data">
					<input type="hidden" id="contrato_cert_indeci_id" name="contrato_cert_indeci_id" value="<?php echo $sql_query["contrato_id"]; ?>">
					<input type="hidden" id="contrato_nombre_local_indeci_dit" name="contrato_nombre_local_indeci_dit" value="<?php echo $sql_query["nombre_tienda"]; ?>">
					<input type="hidden" id="cert_indeci_id" name="cert_indeci_id" value="">

					<fieldset>

						<div class="form-group col-md-3">
							<label>Estado de licencia:</label>
							<select name="txtLicIndeci_edit" id="txtLicIndeci_edit" class='form-control input-sm'>
								<option value=''>Seleccione</option>

								<?php foreach ($select_status as $key => $value) : ?>
									<option value="<?= $value ?>"><?= $value ?></option>

								<?php endforeach; ?>
							</select>
						</div>

						<div class="form-group col-md-3" id="cont_licencias_div_fecha_vencimiento_indeci_edit" style="display: none;">
							<label>Fecha vencimiento:</label>
							<div class="input-group">
								<input type="hidden" id="contrato_vigencia_inicio_fecha_edit" class="filtro" data-col="fecha_inicio" name="fecha_inicio" value="<?php echo date("Y-m-d"); ?>" data-real-date="txtFechaVencimientoLicIndeci_edit">
								<input type="text" class="form-control licencia_funcionamiento_datepicker" id="txtFechaVencimientoLicIndeci_edit" value="<?php echo date("d-m-Y"); ?>" readonly="readonly" style="height: 34px;">
								<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker_edit" for="txtFechaVencimientoLicIndeci_edit"></label>
							</div>

						</div>

						<div class="form-group col-md-3" id="cont_licencias_div_fecha_renovacion_indeci_edit" style="display: none;">
							<label>Fecha de renovación:</label>

							<div class="input-group">
								<input type="hidden" id="contrato_vigencia_inicio_fecha_edit" class="filtro" data-col="fecha_inicio" name="fecha_inicio" value="<?php echo date("Y-m-d"); ?>" data-real-date="txtFechaRenovacionLicIndeci_edit">
								<input type="text" class="form-control licencia_funcionamiento_datepicker" id="txtFechaRenovacionLicIndeci_edit" value="<?php echo date("d-m-Y"); ?>" readonly="readonly" style="height: 34px;">
								<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="txtFechaRenovacionLicIndeci_edit"></label>
							</div>

						</div>

						<div class="form-group col-md-6" id="cont_licencias_div_file_indeci_edit" style="display: none;">
							<label for="fileArchivoLicIndeci">Nombre file:</label>
							<div class="input-container">

								<input type="file" id="fileArchivoLicIndeci_edit" name="fileArchivoLicIndeci_edit" multiple="multiple" accept='.jpeg, .jpg, .png, .pdf'>

								<button class="browse-btn" id="btnBuscarFileIndeci_edit">
									Seleccionar
								</button>

								<span class="file-info" id="txtFileLicIndeci_edit"></span>
							</div>
						</div>
						<br>

						<div class="form-group col-md-12" id="divMensajeAlertaLicIndeci_edit">
						</div>

						<div class="col-md-12">
							<button type="submit" class="btn btn-success loat-right" id="btnGuardarLicenciasIndeci_edit">
								<i class="glyphicon glyphicon-floppy-disk"></i>
								Guardar
							</button>
						</div>
					</fieldset>
				</form>
			</div>
			<div class="modal-footer">
				<div class="form-group ">
					<button class="btn btn-default" data-dismiss="modal">Cancelar</button>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="modal_editar_autorizacion" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h5 class="modal-title" id="mdCrearGrupoTitle">Editar Autorización</h5>
			</div>
			<div class="modal-body">
				<form id="formularioPublicidad_edit" method="POST" enctype="multipart/form-data">
					<input type="hidden" id="contrato_id_autorizacion" name="contrato_id_autorizacion" value="<?php echo $sql_query["contrato_id"]; ?>">
					<input type="hidden" id="contrato_nombre_local_autorizacion" name="contrato_nombre_local_autorizacion" value="<?php echo $sql_query["nombre_tienda"]; ?>">
					<input type="hidden" id="autorizacion_id" name="autorizacion_id" value="">
					<fieldset>
						<div class="form-group col-md-3">
							<label>Estado de licencia:</label>
							<select name="txtLicPublicidad_edit" id="txtLicPublicidad_edit" class='form-control input-sm'>
								<option value=''>Seleccione</option>
								<?php foreach ($select_status as $key => $value) : ?>
									<option value="<?= $value ?>"><?= $value ?></option>
								<?php endforeach; ?>
							</select>
						</div>

						<div class="form-group col-md-3" id="cont_licencias_div_condicion_publicidad_edit" style="display: none;">
							<label>Condicion de licencia:</label>
							<select name="txtCondicionLicPublicidad_edit" id="txtCondicionLicPublicidad_edit" class='form-control input-sm'>
								<option value=''>Seleccione</option>
								<?php foreach ($select_condicion as $key => $value) : ?>
									<option value="<?= $value ?>"><?= $value ?></option>
								<?php endforeach; ?>
							</select>

						</div>

						<div class="form-group col-md-3" id="cont_licencias_div_fecha_vencimiento_publicidad_edit" style="display: none;">
							<label>Fecha vencimiento:</label>
							<div class="input-group">
								<input type="hidden" id="contrato_vigencia_inicio_fecha" class="filtro" data-col="fecha_inicio" name="fecha_inicio" value="<?php echo date("Y-m-d"); ?>" data-real-date="txtFechaVencimientoLicPublicidad_edit">
								<input type="text" class="form-control licencia_funcionamiento_datepicker" id="txtFechaVencimientoLicPublicidad_edit" value="<?php echo date("d-m-Y"); ?>" readonly="readonly" style="height: 34px;">
								<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="txtFechaVencimientoLicPublicidad_edit">
								</label>
							</div>
						</div>

						<div class="form-group col-md-3" id="cont_licencias_div_fecha_renovacion_publicidad_edit" style="display: none;">
							<label>Fecha de renovación:</label>

							<div class="input-group">
								<input type="hidden" id="contrato_vigencia_inicio_fecha" class="filtro" data-col="fecha_inicio" name="fecha_inicio" value="<?php echo date("Y-m-d"); ?>" data-real-date="txtFechaRenovacionLiPublicidad_edit">
								<input type="text" class="form-control licencia_funcionamiento_datepicker" id="txtFechaRenovacionLiPublicidad_edit" value="<?php echo date("d-m-Y"); ?>" readonly="readonly" style="height: 34px;">
								<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="txtFechaRenovacionLiPublicidad_edit"></label>
							</div>
						</div>

						<div class="form-group col-md-6" id="cont_licencias_div_file_publicidad_edit" style="display: none;">
							<label for="fileArchivoLicPublicidad_edit">Nombre file:</label>
							<div class="input-container">

								<input type="file" id="fileArchivoLicPublicidad_edit" name="fileArchivoLicPublicidad_edit" multiple="multiple" accept='.jpeg, .jpg, .png, .pdf'>

								<button class="browse-btn" id="btnBuscarFilePublicidad_edit">
									Seleccionar
								</button>

								<span class="file-info" id="txtFileLicPublicidad_edit"></span>
							</div>
						</div>
						<br>

						<div class="form-group col-md-12" id="divMensajeAlertaLicPublicidad">
						</div>

						<div class="col-md-12">
							<button type="submit" class="btn btn-success loat-right" id="btnGuardarLicenciasPublicidad">
								<i class="glyphicon glyphicon-floppy-disk"></i>
								Guardar
							</button>
						</div>

					</fieldset>
				</form>
			</div>
			<div class="modal-footer">
				<div class="form-group ">
					<button class="btn btn-default" data-dismiss="modal">Cancelar</button>
				</div>
			</div>
		</div>
	</div>
</div>


<div class="modal fade" id="modal_editar_declaracion" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h5 class="modal-title" id="mdCrearGrupoTitle">Editar Declaración jurada</h5>
			</div>
			<div class="modal-body">
			<form id="formularioDeclaracionJurada_edit" method="POST" enctype="multipart/form-data">
			<input type="hidden" id="contrato_id_declaracion" name="contrato_id_declaracion" value="<?php echo $sql_query["contrato_id"]; ?>">
			<input type="hidden" id="contrato_nombre_local_declaracion" name="contrato_nombre_local_declaracion" value="<?php echo $sql_query["nombre_tienda"]; ?>">
			<input type="hidden" id="declaracion_id" name="declaracion_id" value="">
				<fieldset>
													
														
					 

						<div class="form-group col-md-12">
							<label for="fileArchivoLicDeclaracionJurada_edit">Nombre file:</label>
							<div class="input-container">

								<input type="file" id="fileArchivoLicDeclaracionJurada_edit" name ="fileArchivoLicDeclaracionJurada_edit" accept='.jpeg, .jpg, .png, .pdf'>

								<button class="browse-btn" id="btnBuscarFileDeclaracionJurada_edit">
									Seleccionar
								</button>

								<span class="file-info" id="txtFileLicDeclaracionJurada_edit"></span>
							</div>
						</div>
						
						<div class="form-group col-md-12">
															 
							<button type="submit" class="btn btn-success loat-right" id="btnGuardarLicenciasDeclaracionJurada">
								<i class="glyphicon glyphicon-floppy-disk" ></i>
								Guardar
							</button>
						</div>
						<div class="form-group col-md-12" id="divMensajeAlertaDeclaracionJurada">
						</div>
					 

													 
	
													 

				</fieldset>
												<!-- /Panel Body -->
											</form>										
											
			</div>
			<div class="modal-footer">
				<div class="form-group ">
					<button class="btn btn-default" data-dismiss="modal">Cancelar</button>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="modal fade" id="modal_registrar_giro" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h5 class="modal-title" id="mdCrearGrupoTitle">Agregar Giro</h5>
			</div>
			<div class="modal-body">
				<div class="form-group col-md-12">
					<label for="txtNuevoDeclaracionJurada">Nombre Giro:</label>
					<div class="input-container">

						<input class="form-control" type="text" id="txtNuevoDeclaracionJurada" name ="txtNuevoDeclaracionJurada">

						 
					</div>
				</div>
					
					<div class="form-group col-md-12">
																	
						<button type="submit" class="btn btn-success loat-right" onclick="btnGrabarDeclaracionJuradaNuevo()">
							<i class="glyphicon glyphicon-floppy-disk" ></i>
							Guardar
						</button>
					</div>
								
							

															
			
															

												<!-- /Panel Body -->
											
			</div>
			<div class="modal-footer">
				<div class="form-group ">
					<button class="btn btn-default" data-dismiss="modal">Cancelar</button>
				</div>
			</div>
		</div>
	</div>
</div>
<!--  -->