
<?php
if($sub_sec_id=="cdv"){
	?>
	<div class="modal " id="recaudacion_import_modal">
		<div class="modal-dialog">
			<div class="modal-content modal-rounded">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><span class="glyphicon glyphicon-import"></span>  Importar CSV</h4>
				</div>
				<div class="modal-body">
					<form method="post" action="sys/SimpleUpload.php" enctype="multipart/form-data" class="" id="recaudacion_import_form">
						<label class="h4 strong block">Servicio/Proveedor</label>
						<div class="btn-group" data-toggle="buttons">
							<?php
							foreach ($servicios as $srv_k => $srv_v) {
								// if($srv_k!=1 && $srv_k!=4){
								if($srv_k==3 || $srv_k==5 || $srv_k==6 || $srv_k == 7){
									?>
									<label class="btn btn-success">
										<input type="radio" class="import_data btn_servicio" name="servicio_id" value="<?php echo $srv_v["id"];?>" required> <?php echo $srv_v["nombre"];?>
									</label>
									<?php
								}
							}
							?>
						</div>
						<label class="h4 strong block">Tipo de reporte</label>
						<div class="btn-group tipo_servicio" data-toggle="buttons">									
							<label class="btn btn-primary active">
								<input type="radio" class="import_data" name="tipo" value="tickets" checked="checked"> Reporte Diario
							</label>								
							<!-- <label class="btn btn-primary">
								<input type="radio" class="import_data" name="tipo" value="cashdesk"> Cajas
							</label>								
							<label class="btn btn-primary">
								<input type="radio" class="import_data" name="tipo" value="terminals"> Terminales
							</label> -->
						</div>
						<label class="h4 strong block">Archivos</label>
						<div class="files_list_holder col-xs-12">
							<div class="file col-xs-12 file_example hidden">
								<div class="progress_bg">
									<div class="progress-bar progress-bar-striped active progress-bar-success"></div>
								</div>
								<div class="filename col-xs-8">
									<span class="name"></span>
									<span class="size"></span>
								</div>
								<div class="por col-xs-2"><span class="num">0</span>%</div>
							</div>
						</div>
						<div class="form-group form-group-upload-btn">
							<input type="file" name="file" id="file" multiple accept=".csv">									
							<label class="uploader_file_name" for="file">
								<div class="btn btn-warning upload-btn" data-form="recaudacion_import_form">Seleccione</div>
							</label>
						</div>
					</form>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-success" form="recaudacion_import_form" ><span class="glyphicon glyphicon-upload"></span> Subir</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
				</div>
			</div>
		</div>
	</div>
	<div class="modal " id="recaudacion_import_from_bc_modal">
		<div class="modal-dialog">
			<div class="modal-content modal-rounded">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"><span class="glyphicon glyphicon-import"></span>  Importar desde Servidor</h4>
				</div>
				<div class="modal-body">
					<form method="post" action="sys/sys_transacciones.php" enctype="multipart/form-data" id="recaudacion_import_from_bc_form">
						<label class="h4 strong block">Servicio/Proveedor</label>
						<div class="btn-group" data-toggle="buttons">
							<?php
							foreach ($servicios as $srv_k => $srv_v) {
								if($srv_k==1){
									?>
									<label class="btn btn-success active">
										<input 
											type="radio" 
											class="import_bc_data btn_servicio " 
											name="servicio" 
											value="<?php echo $srv_v["id"];?>" 
											required 
											checked="checked"> <?php echo $srv_v["nombre"];?>
									</label>
									<?php
								}
							}
							?>
						</div>
						<label class="h4 strong block">Tipo de reporte</label>
						<div class="btn-group tipo_servicio" data-toggle="buttons">									
							<label class="btn btn-primary active">
								<input type="radio" class="import_bc_data" name="tipo" value="tickets" checked="checked"> Tickets
							</label>	
						</div>
						<label class="h4 strong block">Filtro</label>
						<div class="btn-group">
							<div class="checkbox">
								<label>
									<input type="checkbox" class="import_bc_data" name="filtro" value="All"> Todo
								</label>
							</div>
							<div class="checkbox">
								<label>
									<input type="checkbox" class="import_bc_data" name="filtro" value="Won" > Ganados
								</label>
							</div>
							<div class="checkbox">
								<label>
									<input type="checkbox" class="import_bc_data" name="filtro" value="Paid" > Pagados
								</label>
							</div>
							<div class="checkbox">
								<label>
									<input type="checkbox" class="import_bc_data" name="filtro" value="Pending"> Pendientes
								</label>
							</div>	
						</div>
						<div class="fecha_rango">
						<label class="h4 strong block">Rango de fecha</label>
							<div class="panel-body">
								<div class="col-xs-6">
									<p class="text-center"><strong>Inicio</strong></p>
									<div class="form-group form-group-inicio_fecha">
										<label class="col-xs-4 control-label" for="input_text-inicio_fecha">Fecha</label>
										<div class="input-group col-xs-8">
											<input 
								    			type="hidden" 
								    			class="import_bc_data generar_data"
								    			data-col="inicio_fecha"
												name="inicio_fecha" 
												value="2017-05-01<?php //echo date("Y-m-d", strtotime("-1 week"));?>" 
								    			data-real-date="input_text-inicio_fecha">
											<input 
												type="text" 
												class="form-control recaudacion_datepicker" 
												id="input_text-inicio_fecha" 
												value="01-05-2017<?php //echo date("d-m-Y", strtotime("-1 week"));?>" 
												readonly="readonly" 										 
												>
											<label class="input-group-addon glyphicon glyphicon-calendar" for="input_text-inicio_fecha"></label>
										</div>
									</div>
								</div>
								<div class="col-xs-6">
									<p class="text-center"><strong>Fin</strong></p>
									<div class="form-group form-group-fin_fecha">
										<label class="col-xs-4 control-label" for="input_text-fin_fecha">Fecha</label>
										<div class="input-group col-xs-8">
											<input 
								    			type="hidden" 
								    			class="import_bc_data generar_data"
								    			data-col="fin_fecha"
												name="fin_fecha" 
												value="2017-05-01<?php //echo date("Y-m-d",strtotime("today"));?>" 
								    			data-real-date="input_text-fin_fecha">
											<input 
												type="text" 
												class="form-control recaudacion_datepicker" 
												id="input_text-fin_fecha" 
												value="01-05-2017<?php //echo date("d-m-Y",strtotime("today"));?>" 
												readonly="readonly" 										 
												>
											<label class="input-group-addon glyphicon glyphicon-calendar" for="input_text-fin_fecha"></label>
										</div>
									</div>
								</div>
							</div>
						</div>

					</form>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-success recaudacion_import_from_bc_submit_btn" ><span class="glyphicon glyphicon-upload"></span> Importar</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
				</div>
			</div>
		</div>
	</div>
	<?php

	$procesos = array();
		$procesos_command = "SELECT tp.id
									,tp.time_init
									,tp.fecha_inicio
									,tp.fecha_fin
									,tp.file_registros
									,tp.repositorios_insertados
									,tp.repositorios_updateados
									,tp.repositorios_nothing
									,tp.cabeceras_insertadas
									,tp.cabeceras_updateadas
									,tp.cabeceras_nothing
									,tp.detalles_insertados
									,tp.detalles_updateados
									,tp.detalles_nothing
									,tp.ids_no_procesados
									,tp.tipo
									,s.nombre AS servicio
									,a.archivo
									,u.usuario
							FROM tbl_transacciones_procesos tp 
							LEFT JOIN tbl_servicios s ON (s.id = tp.servicio_id)
							LEFT JOIN tbl_archivos a ON (a.id = tp.archivo_id)
							LEFT JOIN tbl_usuarios u ON (u.id = tp.usuario_id)
							WHERE tp.estado = '1'
							AND tp.tipo = '4'
							ORDER BY tp.id DESC";
		$procesos_query = $mysqli->query($procesos_command);
		while($pro=$procesos_query->fetch_assoc()){
			$procesos[]=$pro;
		}
	?>
	<div class="col-xs-12">
		<table class="table table-bordered table-condensed table-liquidaciones table_recaudacion_importacion">
			<thead>
				<tr>
					<th>
						<label>
							<input type="checkbox" class="re_process_checkbox" data-id="all">
						</label>
					</th>
					<th>id</th>
					<th>Usuario</th>
					<th>Fecha Proceso</th>
					<th>Servicio</th>
					<th>Tipo</th>
					<th>Archivo</th>
					<th>File Registros</th>
					<th>Repositorio Insert</th>
					<th>Repositorio Update</th>
					<th>Repositorio Nothing</th>
					<th>Detalle Insert</th>
					<th>Detalle Update</th>
					<th>Detalle Nothing</th>
					<th>No procesados</th>
					<th>Opciones</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$totales=array();
				$totales["file_registros"]=0;
				$totales["repositorios_insertados"]=0;
				$totales["repositorios_updateados"]=0;
				$totales["repositorios_nothing"]=0;
				$totales["detalles_insertados"]=0;
				$totales["detalles_updateados"]=0;
				$totales["detalles_nothing"]=0;
				$totales["ids_no_procesados"]=array();
				foreach ($procesos as $pro_k => $pro_v) {
					$totales["file_registros"]+=$pro_v["file_registros"];
					$totales["repositorios_insertados"]+=$pro_v["repositorios_insertados"];
					$totales["repositorios_updateados"]+=$pro_v["repositorios_updateados"];
					$totales["repositorios_nothing"]+=$pro_v["repositorios_nothing"];
					$totales["detalles_insertados"]+=$pro_v["detalles_insertados"];
					$totales["detalles_updateados"]+=$pro_v["detalles_updateados"];
					$totales["detalles_nothing"]+=$pro_v["detalles_nothing"];
					?>
					<tr class="checkbox_me">
						<td>
							<label>
								<input type="checkbox" class="re_process_checkbox" data-id="<?php echo $pro_v["id"];?>">
							</label>
						</td>
						<td><?php echo $pro_v["id"];?></td>
						<td><?php echo $pro_v["usuario"];?></td>
						<td><?php echo $pro_v["time_init"];?></td>
						<td><?php echo $pro_v["servicio"];?></td>
						<td><?php 
							if($pro_v["tipo"]==4){
								echo "Reporte diario";
							}else{
								echo ucfirst($pro_v["tipo"]);
							}
							?></td>
						<td class="file_name"><?php echo $pro_v["archivo"];?> <a href="/files_bucket/<?php echo $pro_v["archivo"];?>" target="_blank"><span class="glyphicon glyphicon-download"></span></a></td>
						<td><?php echo $pro_v["file_registros"];?></td>
						<td><?php echo $pro_v["repositorios_insertados"];?></td>
						<td><?php echo $pro_v["repositorios_updateados"];?></td>
						<td><?php echo $pro_v["repositorios_nothing"];?></td>
						<td><?php echo $pro_v["detalles_insertados"];?></td>
						<td><?php echo $pro_v["detalles_updateados"];?></td>
						<td><?php echo $pro_v["detalles_nothing"];?></td>
						<td>
							<?php
							//echo $pro_v["ids_no_procesados"]; 
							$ids_no_procesados_arr = json_decode($pro_v["ids_no_procesados"]);
							if($ids_no_procesados_arr){
								foreach ($ids_no_procesados_arr as $key => $value) {
									if(!in_array($value, $totales["ids_no_procesados"])){
										$totales["ids_no_procesados"][]=$value;
									}
									echo $value; echo "<br>";
								}
							}else{
								?>0<?php
							}
							//print_r($ids_no_procesados_arr);
							//echo substr($pro_v["ids_no_procesados"],1,-1); 
							?>
							<span class="swal_alert" data-text="">								
							<?php //echo count(json_decode($pro_v["ids_no_procesados"]));?></span></td>
						<td>
							<?php
							if(array_key_exists(30,$usuario_permisos)){
								if(in_array("reprocess", $usuario_permisos[30])){
									?>
									<button class="btn btn-primary rec_reprocess_btn" data-id="<?php echo $pro_v["id"];?>">RE-Procesar</button>
									<?php
								}
							}
							?>
						</td>
					</tr>
					<?php
				}
				?>
			</tbody>
			<tfoot>
				<tr class="success">
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td></td>
					<td><?php echo $totales["file_registros"];?></td>
					<td><?php echo $totales["repositorios_insertados"];?></td>
					<td><?php echo $totales["repositorios_updateados"];?></td>
					<td><?php echo $totales["repositorios_nothing"];?></td>
					<td><?php echo $totales["detalles_insertados"];?></td>
					<td><?php echo $totales["detalles_updateados"];?></td>
					<td><?php echo $totales["detalles_nothing"];?></td>
					<td><?php foreach ($totales["ids_no_procesados"] as $key => $value) { 
						echo $value; echo "<br>";
						}?></td>
					<td></td>
				</tr>
			</tfoot>
		</table>
	</div>
	<?php
}
?>