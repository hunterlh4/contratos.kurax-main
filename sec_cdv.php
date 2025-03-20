<div class="content container-fluid cuadro_de_ventas_form_contrato">
	<div class="page-header wide">
		<div class="row">
			<div class="col-xs-12 text-center">
				<h1 class="page-title"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Cuadro de ventas</h1>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12 col-md-6">
				<?php
				if($item_id){
				}else{
					?>
					<button
						class="btn btn-primary cdv_import_btn">
							<span class="glyphicon glyphicon-import"></span> 
							Importar CSV
					</button>					
					<?php
				}
				?>
			</div>
		</div>
	</div>

	<?php
	if($item_id){
	}else{
		$productos=array();
		$productos_query=$mysqli->query("SELECT id, nombre FROM tbl_productos WHERE estado = '1' ORDER BY nombre ASC");
		while($pro=$productos_query->fetch_assoc()){
			
			$productos[]=$pro;
		}
		$servicios=array();
		$srv_query = $mysqli->query("SELECT id,nombre FROM tbl_servicios WHERE  estado = '1' ORDER BY nombre ASC");
		while($srv=$srv_query->fetch_assoc()){
			$servicios[]=$srv;
		}
		?>
		<div class="modal " id="cdv_import_modal">
			<div class="modal-dialog">
				<div class="modal-content modal-rounded">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title"><span class="glyphicon glyphicon-import"></span>  Importar CSV</h4>
					</div>
					<div class="modal-body">
						<form method="post" action="sys/SimpleUpload.php" enctype="multipart/form-data" class="" id="cdv_import_form">
							<?php if(1==2){ ?><label class="h4 strong block">Producto</label>
							<div class="btn-group nav-tabs" data-toggle="buttons">
								<?php
								foreach ($productos as $pro_k => $pro_v) {
									?>
									<label class="btn btn-primary">
										<input type="radio" name="canal_de_venta" value="<?php echo $pro_v["id"];?>"> <?php echo $pro_v["nombre"];?>
									</label>
									<?php
								}
								?>
							</div><?php } ?>
							<label class="h4 strong block">Servicio/Proveedor</label>
							<div class="btn-group" data-toggle="buttons">
								<?php
								foreach ($servicios as $srv_k => $srv_v) {
									?>
									<label class="btn btn-success">
										<input type="radio" class="import_data" name="servicio" value="<?php echo $srv_v["id"];?>" required> <?php echo $srv_v["nombre"];?>
									</label>
									<?php
								}
								?>
							</div>
							<label class="h4 strong block">Rango</label>
							<div class="panel-body">
								<div class="col-xs-6">
									<p class="text-center"><strong>Inicio</strong></p>
									<div class="form-group form-group-inicio_fecha">
										<label class="col-xs-4 control-label" for="input_text-inicio_fecha">Fecha</label>
										<div class="input-group col-xs-8">
											<input 
								    			type="hidden" 
								    			class="input_text import_data"
								    			data-col="inicio_fecha"
												name="inicio_fecha" 
												value="<?php echo date("Y-m-d", strtotime("yesterday"));?>" 
								    			data-real-date="input_text-inicio_fecha">
											<input 
												type="text" 
												class="form-control cdv_datepicker" 
												id="input_text-inicio_fecha" 
												value="<?php echo date("d-m-Y", strtotime("yesterday"));?>" 
												readonly="readonly" 										 
												>
											<label class="input-group-addon glyphicon glyphicon-calendar" for="input_text-inicio_fecha"></label>
										</div>
									</div>
									<div class="form-group form-group-inicio_hora">
										<label class="col-xs-4 control-label" for="input_text-inicio_hora">Hora</label>
										<div class="input-group col-xs-8">
											<input 
												type="text" 
												class="form-control timepicker import_data" 
												id="input_text-inicio_hora" 
												value="00:00" 
												readonly="readonly" 
												name="inicio_hora" 										 
												>
											<label class="input-group-addon glyphicon glyphicon-time" for="input_text-inicio_hora"></label>
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
								    			class="input_text import_data"
								    			data-col="fin_fecha"
												name="fin_fecha" 
												value="<?php echo date("Y-m-d");?>" 
								    			data-real-date="input_text-fin_fecha">
											<input 
												type="text" 
												class="form-control cdv_datepicker" 
												id="input_text-fin_fecha" 
												value="<?php echo date("d-m-Y");?>" 
												readonly="readonly" 										 
												>
											<label class="input-group-addon glyphicon glyphicon-calendar" for="input_text-fin_fecha"></label>
										</div>
									</div>
									<div class="form-group form-group-fin_hora">
										<label class="col-xs-4 control-label" for="input_text-fin_hora">Hora</label>
										<div class="input-group col-xs-8">
											<input 
												type="text" 
												class="form-control timepicker import_data" 
												id="input_text-fin_hora" 
												value="00:00" 
												readonly="readonly" 
												name="fin_hora" 										 
												>
											<label class="input-group-addon glyphicon glyphicon-time" for="input_text-fin_hora"></label>
										</div>
									</div>
								</div>
							</div>
							<label class="h4 strong block">Archivo</label>
							<div class="form-group form-group-upload-btn">
								<div class="btn btn-warning upload-btn" data-form="cdv_import_form">Seleccione</div>
								<label class="uploader_file_name" for="file"></label>
							</div>
						</form>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-success" form="cdv_import_form" ><span class="glyphicon glyphicon-upload"></span> Subir</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
	?>
</div>