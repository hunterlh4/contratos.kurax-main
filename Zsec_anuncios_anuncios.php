<?php  
global $mysqli;

$this_menu = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' AND sub_sec_id = '$sub_sec_id' LIMIT 1")->fetch_assoc();
$menu_id = $this_menu["id"];


if(!array_key_exists($menu_id,$usuario_permisos))
{
	?>
	<h5>No tienes permisos para este recurso.</h5>
	<?php  
}
else
{
?>

<style>
	.nota_importante{
		font-size: 16px;
		color: red;
	}	
</style>

<div class="content container-fluid vista_anuncios_anuncios">
	<div class="page-header wide">
		<div class="row">
			<div class="col-xs-12 text-center">
				<h1 class="page-title titulosec_contrato"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Gestión de Anuncios</h1>
			</div>
		</div>
	</div>
	
	<div class="row">
		<div class="row">
			<div class="col-xs-12 col-md-8 col-lg-8 mt-3" id="anuncios_form_anuncio">
				<div class="panel">
					<div class="panel-heading">
						<div class="panel-title">Nuevo Anuncio</div>
					</div>
					<div class="panel-body no-pad">
						<form id="anuncios_formulario_anuncios" method="POST" enctype="multipart/form-data">

							<div class="form-group col-xs-12 col-md-12 col-lg-12 mt-3" id="">
								<label for="anuncios_texto">Nombre de anuncio:</label><small style="color: #2000ff">(max. caracteres 100)</small>
								<input type="text" id="anuncios_texto" maxlength="100" class="form-control" autocomplete="off" style="width: 100%; height: 30px;" value="" placeholder="Ingrese el nombre del anuncio">
							</div>

							<div class="form-group col-xs-12 col-sm-6 col-md-6 col-lg-3 mt-2" id="">
								<label for="anuncios_fecha_desde">Fecha Inicio:</label>
								<div class="input-group">
									<input
										type="hidden"
										value="<?php echo date("Y-m-d"); ?>"
										data-real-date="anuncios_fecha_desde">
									<input
										type="text"
										class="form-control anuncio_fecha_datepicker"
										id="anuncios_fecha_desde"
										value="<?php echo date("d-m-Y");?>"
										readonly="readonly"
										style="height: 34px;"
										>
									<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="anuncios_fecha_desde"></label>
								</div>
							</div>

							<div class="form-group col-xs-12 col-sm-6 col-md-6 col-lg-3 mt-2" id="">
								<label>Fecha Fin:</label>
								<div class="input-group">
									<input
										type="hidden"
										value="<?php echo date("Y-m-d"); ?>"
										data-real-date="anuncios_fecha_hasta">
									<input
										type="text"
										class="form-control anuncio_fecha_datepicker"
										id="anuncios_fecha_hasta"
										value="<?php echo date("d-m-Y");?>"
										readonly="readonly"
										style="height: 34px;"
										>
									<label class="input-group-addon glyphicon glyphicon-calendar label_icono_calendar_datepicker" for="anuncios_fecha_hasta"></label>
								</div>
							</div>

                            <!-- <div class="row"> -->
                                <!-- <div class="form_group col-sx-12 col-md-6 col-lg-6"></div> -->
                                <div class="form-group col-xs-12 col-sm-6 col-md-7 col-lg-4 mt-2">
                                    <input type="text" id="anuncios_input_dias_semana" hidden value="">
                                    <label>Días de la semana:</label> <br>
                                    <div class="btn-group btn-group-sm" role="group" aria-label="..." id="anuncios_dias_semana">
                                        <button type="button" class="btn btn-default mr-2" onclick="seleccionarDiasDeSemana(2)" id="anuncios_dia_2">L</button>
                                        <button type="button" class="btn btn-default mr-2" onclick="seleccionarDiasDeSemana(3)" id="anuncios_dia_3">M</button>
                                        <button type="button" class="btn btn-default mr-2" onclick="seleccionarDiasDeSemana(4)" id="anuncios_dia_4">M</button>
                                        <button type="button" class="btn btn-default mr-2" onclick="seleccionarDiasDeSemana(5)" id="anuncios_dia_5">J</button>
                                        <button type="button" class="btn btn-default mr-2" onclick="seleccionarDiasDeSemana(6)" id="anuncios_dia_6">V</button>
                                        <button type="button" class="btn btn-default mr-2" onclick="seleccionarDiasDeSemana(7)" id="anuncios_dia_7">S</button>
                                        <button type="button" class="btn btn-default mr-2" onclick="seleccionarDiasDeSemana(1)" id="anuncios_dia_1">D</button>
                                        <button type="button" class="btn btn-default mr-2" onclick="seleccionarDiasDeSemana(0)">TODOS</button>
                                    </div>
                                </div>
                            <!-- </div> -->

							<div class="form-group col-xs-12 col-sm-6 col-md-5 col-lg-2 mt-2" id="">
								<label for="anuncios_tipo_archivo">Tipo anuncio</label>
								<select
									class="form-control input_text anuncios_select_filtro m-2"
									data-live-search="true" 
									id="anuncios_tipo_archivo" 
									title="Seleccione el nombre del proveedor">
									<!-- <option value="0">-- Seleccione --</option>-->
									<?php
									$sel_query = $mysqli->query("
										SELECT 
											id, name, active 
										FROM tbl_archivos_categoria
										WHERE id in(6, 7)") AND active != 0;
									
									while($sel=$sel_query->fetch_assoc())
									{
										if($sel["id"] == 6)
										{
											?>
												<option value="<?php echo $sel["id"];?>" selected><?php echo $sel["name"];?></option>
											<?php
										}
										else
										{
											?>
												<option value="<?php echo $sel["id"];?>"><?php echo $sel["name"];?></option>
											<?php
										}
									}
									?>
								</select>
							</div>

							<div class="form-group col-xs-12 col-sm-12 col-md-12 col-lg-12 mt-2" id="anuncios_div_imagen" style="margin-bottom: -30px;">
								<label for="anuncios_imagen">
                                    Imagen:
                                    <small class="label label_default">
                                        <label for="anuncios_check_image_multiple">múltiple</label>
                                        <input type="checkbox" id="anuncios_check_image_multiple" />
                                    </small>
                                </label>
								<div class="input-container">
									<input type="file" id="anuncios_imagen" name ="anuncios_imagen" multiple="multiple" accept='.jpeg, .jpg, .png'>

									<button class="browse-btn" id="btn_buscar_anuncio_imagen">
										Seleccionar
									</button>
									<span class="file-info" id="txt_anuncio_imagen"></span>
								</div>
							</div>

							<div class="form-group col-xs-12 col-sm-12 col-md-12 col-lg-12 mt-2" id="anuncios_div_audio" style="margin-bottom: -30px; display: none;">
								<label for="anuncios_audio">Audio:</label>
								<div class="input-container">
									<input type="file" id="anuncios_audio" name ="anuncios_audio" multiple="multiple" accept='.mp3'>

									<button class="browse-btn" id="btn_buscar_anuncio_audio">
										Seleccionar
									</button>
									<span class="file-info" id="txt_anuncio_audio"></span>
								</div>
							</div>

							<div class="form-group col-xs-12 col-md-6 col-lg-6" id="anuncios_div_video" style="display: none;">
								<label for="anuncios_video">Video:</label>
								<div class="input-container">
									<input type="file" id="anuncios_video" name ="anuncios_video" multiple="multiple" accept='.avi, .flv, .wmv, .mov, .mp4'>

									<button class="browse-btn" id="btn_buscar_anuncio_video">
										Seleccionar
									</button>
									<span class="file-info" id="txt_anuncio_video"></span>
								</div>
							</div>

							<div class="form-group col-xs-12 col-sm-6 col-md-6 col-lg-2 mt-2" id="">
								<label for="anuncios_tipo_reproduccion">Tipo reproducción</label>
								<select
									class="form-control input_text anuncios_select_filtro"
									data-live-search="true" 
									data-col="personal_id" 
									data-table="tbl_personal"
									name="contrato_num_ruc_proveedor" 
									id="anuncios_tipo_reproduccion" 
									title="Seleccione el nombre del proveedor">
									<!-- <option value="0">-- Seleccione --</option>-->
									<?php
									$sel_query = $mysqli->query("
										SELECT
											id, nombre, status 
										FROM tbl_gestion_tipo_reproduccion
										WHERE id in(2)
										ORDER BY id DESC ");
									
									while($sel=$sel_query->fetch_assoc())
									{
										if ($sel["id"] == 2) 
										{
											?>
												<option value="<?php echo $sel["id"];?>" selected><?php echo $sel["nombre"];?></option>
											<?php
										}
										else
										{
											?>
											<option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"];?></option>
											<?php
										}
									}
									?>
								</select>
							</div>
							<!-- style="margin-top: 20px; width: 200px;" -->
							<div class="form-group col-xs-12 col-sm-6 col-md-6 col-lg-3 mt-2" id="anuncios_div_tiempo_anuncio">
								<label for="anuncios_tiempo_anuncio">Reproducir cada (minutos):</label>
								<input type="number" id="anuncios_tiempo_anuncio" name="anuncios_tiempo_anuncio" class="form-control anuncios_tiempo_anuncio" autocomplete="off">
							</div>

							<div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-2 mt-2" id="anuncios_div_horario_desde">
								<label for="anuncios_horario_desde">Horario Inicio:</label>
								<input type="time" id="anuncios_horario_desde" name="anuncios_horario_desde" class="filtro anuncios_horario_desde w-100">
							</div>

							<div class="form-group col-xs-6 col-sm-6 col-md-4 col-lg-2 mt-2" id="anuncios_div_horario_hasta">
								<label for="anuncios_horario_hasta">Horario Fin:</label>
								<input type="time" name="anuncios_horario_hasta" id="anuncios_horario_hasta" class="filtro anuncios_horario_hasta w-100">
							</div>

							<div class="form-group col-xs-12 col-sm-6 col-md-4 col-lg-3 mt-2" id="anuncios_comprobar_hora">
								<label for="anuncios_comprobar_hora_btn">Validar horario:</label>
								<input type="button" name="anuncios_comprobar_hora_btn" id="anuncios_comprobar_hora_btn" class="btn btn-sm btn-rounded btn-success" value="Comprobar Disponibilidad">
							</div>

							<div class="form-group col-xs-12 col-sm-12 col-md-12 col-lg-12 mt-3" style="margin-top: 10px;">
								<span class="nota_importante">(*)</span> <b> Los minutos se suman a la hora de inicio (Horario inicio)</b>
							</div>

							<div class="col-xs-12 col-md-12 col-lg-12" id="anuncios_div_btn_agg_detalle" style="margin-top: 20px; display: none;">
								<button type="button" class="btn btn-warning btn-xs" id="btn_agg_detalle_horario" onclick="anuncios_agregar_detalle_anuncio();">
									<span class="glyphicon glyphicon-plus"></span>
									<span id="demo-button-text">Agregar detalle</span>
								</button>
							</div>

							<div class="col-xs-12 col-md-12 col-lg-12" id="anuncios_div_table_detalle" style="display: none; margin-top: 12px; width: 30%;">
								<table id="anuncio_detalle_table" class="anuncio_detalle_table table table-striped table-hover table-condensed table-bordered dt-responsive display" cellspacing="0" width="70%">
									<thead style="background-color: #A9D0F5;">
										<tr>
											<th class="text-center" colspan="2">Detalle de Horarios a Reproducir</th>
										</tr>
									</thead>            
									<tbody>
										
									</tbody>
								</table>
							</div>

							<div class="form-group col-xs-12 col-sm-6 col-md-6 col-lg-6" id="" style="margin-top: 10px;">
								<label for="">Filtrar objetivo por Área:</label>
								<select
									class="form-control input_text anuncios_select_filtro"
									data-live-search="true" 
									id="anuncios_area_select_filtro" >
									<option value="0" selected>-- Todos --</option>
									<?php
									$sel_query = $mysqli->query("SELECT id, nombre FROM tbl_areas WHERE estado = 1");
									
									while($sel=$sel_query->fetch_assoc()) {
										?>
											<option value="<?php echo $sel["id"];?>" ><?php echo $sel["nombre"];?></option>
										<?php
									}
									?>
								</select>
							</div>

							<div class="form-group col-xs-12 col-sm-6 col-md-6 col-lg-6" id="" style="margin-top: 10px;">
								<label for="">Filtrar objetivo por Grupo:</label>
								<select
									class="form-control input_text anuncios_select_filtro"
									data-live-search="true" 
									id="anuncios_grupo_select_filtro"
									multiple>
									<option value="0">-- Todos --</option>
									<?php
									$sel_query = $mysqli->query("SELECT id, nombre FROM tbl_usuarios_grupos WHERE estado = 1");
									
									while($sel=$sel_query->fetch_assoc()) {
										?>
											<option value="<?php echo $sel["id"];?>" ><?php echo $sel["nombre"];?></option>
										<?php
									}
									?>
								</select>
							</div>
							<!-- style="margin-top: 20px;" -->
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-6 col-lg-offset-3 mt-2">
								<button type="submit" class="btn btn-success btn-block" id="">
									<i class="icon fa Sfa-save" id="demo-button-icon-left" style="display: inline-block;"></i>
									<span id="demo-button-text">Guardar</span>
								</button>
							</div>

						</form>
					</div>
				</div>

				<!-- <div class="col-xs-12 col-md-8 col-lg-8 mt-3" id="anuncios_div_lista_anuncio">-->

					<?php
						$sel_query = $mysqli->query(
							"
							SELECT
							tga.id, tga.text, per.nombre, per.apellido_paterno, per.apellido_materno,
							tga.id_tipo_archivo, tac.name AS nombre_tipo_archivo, 
								tgtr.nombre AS nombre_tipo_reproduccion, tga.imagen, tga.download, tga.extension, 
								tga.fecha_desde, tga.fecha_hasta, tga.horario_desde, tga.horario_hasta, tga.tiempo_anuncio,
								tga.created_at,
								IF (ar.nombre is not null, ar.nombre, 'Todos') AS area_nombre,
								tga.id_grupos
							FROM tbl_gestion_anuncios tga
								LEFT JOIN tbl_archivos_categoria tac ON tga.id_tipo_archivo = tac.id
								LEFT JOIN tbl_gestion_tipo_reproduccion tgtr ON tga.id_tipo_reproduccion = tgtr.id
								LEFT JOIN tbl_usuarios us ON tga.user_created_id = us.id
                                LEFT JOIN tbl_personal_apt per ON us.personal_id = per.id
								LEFT JOIN tbl_areas ar ON tga.id_area = ar.id
							WHERE tga.estado > 0
							ORDER BY tga.created_at DESC
							");
					?>

					<table id="anuncios_div_table_anuncios" class="table table-striped table-hover table-condensed table-bordered dt-responsive display" cellspacing="0" width="100%">
						<thead>
							<tr>
								<th class="text-center">ID</th>
								<th class="text-center">Contenido</th>
								<th class="text-center">Nombre Usuario</th>
								<th class="text-center">Tipo archivo</th>
								<th class="text-center">Tipo de reprodución</th>
								<th class="text-center">F. Creación</th>
								<th class="text-center">Anuncio</th>
								<th class="text-center">Eliminar</th>
							</tr>
						</thead>
						<tfoot>
							<tr>
								<th class="text-center">ID</th>
								<th class="text-center">Contenido</th>
								<th class="text-center">Nombre Usuario</th>
								<th class="text-center">Tipo archivo</th>
								<th class="text-center">Tipo de reprodución</th>
								<th class="text-center">F. Creación</th>
								<th class="text-center">Anuncio</th>
								<th class="text-center">Eliminar</th>
							</tr>
						</tfoot>             
						<tbody>
							<?php
							while($sel=$sel_query->fetch_assoc())
							{

								$hora_desde = date_create($sel["horario_desde"]);
								$horario_desde = date_format($hora_desde,"h:i a");

								$hora_hasta = date_create($sel["horario_hasta"]);
								$horario_hasta = date_format($hora_hasta,"h:i a");
								
								if ($sel['area_nombre'] == '' ) {
									$sel['area_nombre'] = 'Todos';
								}
								$muestra_nombre_grupo = "";
								$sel['id_grupos'] = trim($sel['id_grupos'], ',');
								$cadena_id_grupos = explode(',',$sel['id_grupos']);
								if($cadena_id_grupos[0] != '' ){
									for ($i=0; $i < count($cadena_id_grupos); $i++) {
										if ($cadena_id_grupos[$i] == 0) {
											$muestra_nombre_grupo = "Todos   ";
										} else {
											$query_nombre_grupo = "SELECT nombre FROM tbl_usuarios_grupos WHERE id = ".$cadena_id_grupos[$i];
											$mq_nombre_grupo = $mysqli->query($query_nombre_grupo);
											while($sel_nombre_grupo=$mq_nombre_grupo->fetch_assoc()) {
												$muestra_nombre_grupo .= $sel_nombre_grupo['nombre']." - ";
											}
										}
									}
									$muestra_nombre_grupo = substr($muestra_nombre_grupo, '0', '-3');
								} else {
									$muestra_nombre_grupo = "Todos";
								}
								?>
								<tr>
									<td class="col-md-1"><?php echo $sel["id"];?></td>
									<td class="col-md-2"><?php echo $sel["text"];?></td>
									<td class="col-md-2"><?php echo $sel["nombre"];?> <?php echo $sel["apellido_paterno"];?> <?php echo $sel["apellido_materno"];?></td>
									<td class="col-md-2"><?php echo $sel["nombre_tipo_archivo"];?></td>
									<td class="col-md-2"><?php echo $sel["nombre_tipo_reproduccion"];?></td>
									<td class="col-md-3 text-center"><?php echo $sel["created_at"];?></td>
									<td class="text-center">
										<a onclick="sec_anuncios_ver_archivo('<?php echo $sel['id']?>', '<?php echo $sel['download']?>', '<?php echo $sel['extension']?>', '<?php echo $sel['fecha_desde']?>', '<?php echo $sel['fecha_hasta']?>', '<?php echo $horario_desde ?>', '<?php echo $horario_hasta ?>', '<?php echo $sel['tiempo_anuncio']?>', '<?php echo $sel['area_nombre'] ?>', '<?php echo $muestra_nombre_grupo ?>')";
											class="btn btn-success btn-xs btn-block" data-toggle="tooltip" data-placement="top">
											<span class="fa fa-eye"></span>
											Ver Anuncio
										</a>
									</td>
									<td class="text-center">
										<a onclick="sec_anuncio_eliminar_anuncio('<?php echo $sel['id']?>')"; 
											class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top">
											<span class="fa fa-trash"></span> 
										</a>
									</td>
								</tr>
								<?php
							}
							?>
						</tbody>
					</table>
				<!-- </div>-->
			</div>

			<div class="col-xs-12 col-md-4 col-lg-4 mt-3" id="anuncios_div_lista_opciones">
				<div class="panel" id="sec_anuncios_div_panel_ver_anuncio" style="display: none;">
					<!-- Panel Heading -->
					<div class="panel-heading">

						<!-- Panel Title -->
						<div class="panel-title" id="sec_anuncios_div_nombre_panel_visor_titulo_ver_archivo">Detalle Programación</div>
						<!-- /Panel Title -->

					</div>
					<!-- /Panel Heading -->

					<div class="panel-body" style="padding: 10px 0px 10px 0px;">
						<div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center;margin-bottom: 5px;" id="sec_anuncios_div_detalle_programacion_anuncio">
						</div>

						<div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center;margin-bottom: 5px; display:none;" id="sec_anuncios_div_visor_ver_archivo_full_pantalla">
							<button type="button" class="btn btn-block btn-block btn-primary" id="sec_anuncios_ver_imagen_full_pantalla" style="background-color:#7dc623;">
								<i class="fa fa-arrows-alt"></i>  Ver imagen en toda la Pantalla
							</button>
						</div>

						<div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center;margin-bottom: 5px; display:none;" id="sec_anuncios_div_visor_ver_archivo">
						</div>
					</div>

				</div>
			</div>

		</div>

		<div class="col-xs-12 col-md-12 col-lg-12">
			
			<div class="col-xs-12 col-md-4 col-lg-4 mt-3" id="anuncios_div_lista_opciones">
				<div class="panel" id="sec_anuncios_div_panel_ver_anuncio" style="display: none;">
					<!-- Panel Heading -->
					<div class="panel-heading">

						<!-- Panel Title -->
						<div class="panel-title" id="sec_anuncios_div_nombre_panel_visor_titulo_ver_archivo">Detalle Programación</div>
						<!-- /Panel Title -->

					</div>
					<!-- /Panel Heading -->

					<div class="panel-body" style="padding: 10px 0px 10px 0px;">
						<div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center;margin-bottom: 5px;" id="sec_anuncios_div_detalle_programacion_anuncio">
						</div>

						<div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center;margin-bottom: 5px; display:none;" id="sec_anuncios_div_visor_ver_archivo_full_pantalla">
							<button type="button" class="btn btn-block btn-block btn-primary" id="sec_anuncios_ver_imagen_full_pantalla" style="background-color:#7dc623;">
								<i class="fa fa-arrows-alt"></i>  Ver imagen en toda la Pantalla
							</button>
						</div>

						<div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center;margin-bottom: 5px; display:none;" id="sec_anuncios_div_visor_ver_archivo">
						</div>

						<div class="col-xs-12 col-md-12 col-sm-12" style="text-align: center;margin-bottom: 5px; display:none;" id="sec_anuncios_div_visor_ver_archivo">
						</div>
					</div>

				</div>
			</div>
		</div>
	</div>
		
</div>

<?php  
}
?>

