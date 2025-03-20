<div class="content container-fluid contratos_form_contrato">
	<div class="page-header wide">
		<div class="row">
			<div class="col-xs-12 text-center">
				<h1 class="page-title titulosec_contrato"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Contratos</h1>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12 ">
				<?php
				$this_menu = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '".$sec_id."' LIMIT 1")->fetch_assoc();
				if($item_id){
					?>
					<a class="btn btn-default" href="./?sec_id=<?php echo $sec_id;?>">
        				<i class="glyphicon glyphicon-arrow-left"></i>
        				Regresar
        			</a>
        			<?php if(array_key_exists($this_menu["id"], $usuario_permisos) && in_array("save", $usuario_permisos[$this_menu["id"]])){ ?>
		                <button type="button" data-form="contrato_form" data-then="exit" class="contrato_save_btn btn btn-success">
		                	<i class="glyphicon glyphicon-floppy-save"></i>
		                	Guardar y Salir
		                </button>
		                <button type="button" data-form="contrato_form" data-then="reload" class="contrato_save_btn btn btn-success">
		                	<i class="glyphicon glyphicon-floppy-save"></i>
		                	Guardar
		                </button>
	                <?php }else{ ?>
	               		<button type="button" data-then="exit" class="btn btn-success disabled no_permiso">
		                	<i class="glyphicon glyphicon-floppy-save"></i>
		                	Guardar y Salir
		                </button>
		                <button type="button" data-then="reload" class="btn btn-success disabled no_permiso">
		                	<i class="glyphicon glyphicon-floppy-save"></i>
		                	Guardar
		                </button>
	                <?php } ?>
	                <?php if(array_key_exists($this_menu["id"], $usuario_permisos) && in_array("delete", $usuario_permisos[$this_menu["id"]])){ ?>
		                <button type="button" 
		                	data-then="exit" 
		                	data-table="tbl_contratos" 
		                	data-id="<?php echo $item_id;?>" 
		                	class="del_btn btn btn-danger pull-right">
			                	<i class="glyphicon glyphicon-remove"></i>
			                	Eliminar
		                	</button>
	                <?php }else{ ?>
	                	<button class="no_permiso btn btn-danger pull-right disabled"><i class="glyphicon glyphicon-remove"></i> Eliminar</button>
	                <?php } ?>
					<?php
				}else{
					?>

					<?php if(array_key_exists($this_menu["id"], $usuario_permisos) && in_array("new", $usuario_permisos[$this_menu["id"]])){ ?>
					
					<a 	href="./?sec_id=<?php echo $sec_id;?>&amp;item_id=new"
						id="" 
						data-sec="<?php echo $sec_id;?>" 
						data-sub-sec="<?php echo $sub_sec_id;?>" 
						data-table="tbl_contratos"
						class="btn btn-rounded btn-min-width btn-success btn-add contrato_add_btn hvr-float">
						<i class="glyphicon glyphicon-plus"></i>
						Agregar
					</a>


					<?php }else{ ?><button class="btn btn-rounded btn-min-width btn-success no_permiso disabled">Agregar</button><?php } ?>
					<?php if(array_key_exists($this_menu["id"], $usuario_permisos) && in_array("export", $usuario_permisos[$this_menu["id"]])){ ?>
					<div class="btn-group btn-group-separators hvr-float">
						<a 	href="export.php?export=tbl_contratos&amp;type=lista" 
							type="button" 
							class="btn btn-success export_list_btn "  
							data-table="tbl_contratos"
							data-type="lista">
							<span class="glyphicon glyphicon-export"></span> Exportar Lista</a>
							
						<button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<span class="caret"></span>
						</button>
						<ul class="dropdown-menu">
							<li><a
							href="export.php?export=tbl_contratos" 
							class="export_list_btn " 
							data-table="tbl_contratos"
							data-type="lista"><span class="glyphicon glyphicon-export"></span> Exportar Todo</a></li>
						</ul>
					</div>
					<?php }else{ ?><button class="btn btn-rounded btn-min-width btn-success no_permiso disabled"><span class="glyphicon glyphicon-export"></span>  Exportar</button> <?php } ?>
					<button type="button" class="btn btn-success modal_open_btn hvr-float btnelegircolumnassec_contratos" data-target="#filter_holder">
					<i class="glyphicon glyphicon-search"></i>
						Elegir Columnas
					</button>
					<?php
				}
				?>
			</div>
		</div>
	</div>

	<?php
	if($item_id){
		//con.id, con.fecha_registro, con.canal_de_venta_id, con.tipo_contrato_id, con.fecha_inicio_contrato, con.fecha_fin_contrato
		$item = $mysqli->query("SELECT con.*
								, cli.ruc, cli.dni, cli.tipo_cliente_id
								FROM tbl_contratos con
								LEFT JOIN tbl_clientes cli ON (cli.id = con.cliente_id)
								WHERE con.id = '".$item_id."'")->fetch_assoc();

		if($item_id=="new"){			
			$sql_cols = "SHOW COLUMNS FROM tbl_contratos";
			$result = $mysqli->query($sql_cols);
			while($row=$result->fetch_array()){
				$item[$row['Field']]="";
			}
			$item["fecha_registro"]=$item["fecha_inicio_contrato"]=$item["fecha_fin_contrato"]=date("Y-m-d");
		}
		//print_r($item);
		?>
		
		<div class="modal fade" id="add_file_modal" tabindex="-1" role="dialog" aria-labelledby="add_file_modal">
			<div class="modal-dialog " role="document">
				<div class="modal-content modal-rounded">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="myModalLabel">Subir Archivo</h4>
					</div>
					<div class="modal-body">
						<form method="post" action="sys/uploadHandler.php" enctype="multipart/form-data" class="" id="add_file_form">

							<input type="hidden" name="new_file_tabla" value="tbl_contratos">
							<input type="hidden" name="new_file_item_id" value="<?php echo $item_id;?>">

							<div class="row">	
								<div class="col-xs-8 col-xs-offset-2">
									<div class="form-group form-group-nombre">
										<label class="col-xs-5 control-label" for="input_text-nombre">Nombre</label>
										<div class="input-group col-xs-7">
											<input 
												type="text" 
												class="form-control" 
												id="input_text-nombre"  
												name="new_file_nombre" 
												>
											<label class="input-group-addon glyphicon glyphicon-info-sign" ></label>
										</div>
									</div>
									<div class="form-group form-group-descripcion">
										<label class="col-xs-5 control-label" for="input_text-descripcion">Descripcion</label>
										<div class="input-group col-xs-7">
											<input 
												type="text" 
												class="form-control" 
												id="input_text-descripcion"  
												name="new_file_descripcion" 
												>
											<label class="input-group-addon glyphicon glyphicon-info-sign" ></label>
										</div>
									</div>
									<div class="form-group form-group-descripcion">
										<label class="col-xs-5 control-label" for="file">Archivo</label>
										<div class="input-group col-xs-7">
											<div class="btn btn-warning upload-btn" data-form="add_file_form">Seleccione</div>
											<label class="uploader_file_name" for="file"></label>
										</div>
									</div>
									<!--<input type="file" name="file" id="file" class="hidden">-->
									
								</div>						
							</div>
						</form>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
						<button type="submit" class="btn btn-success" name="" form="add_file_form"><span class="glyphicon glyphicon-upload"></span> Subir</button>
					</div>
				</div>
			</div>
		</div>
		<div class="modal fade" id="edit_file_modal" tabindex="-1" role="dialog" aria-labelledby="edit_file_modal">
			<div class="modal-dialog " role="document">
				<div class="modal-content modal-rounded">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title" id="myModalLabel">Editar Archivo</h4>
					</div>
					<div class="modal-body">
						<form enctype="multipart/form-data" class="" id="edit_file_form">

							<input type="hidden" name="edit_file_tabla" value="tbl_archivos">
							<input type="hidden" name="edit_file_item_id" value="">

							<div class="row">	
								<div class="col-xs-8 col-xs-offset-2">
									<div class="form-group form-group-nombre">
										<label class="col-xs-5 control-label" for="input_text-nombre">Nombre</label>
										<div class="input-group col-xs-7">
											<input 
												type="text" 
												class="form-control" 
												id="input_text-nombre"  
												name="edit_file_nombre" 
												>
											<label class="input-group-addon glyphicon glyphicon-info-sign" ></label>
										</div>
									</div>
									<div class="form-group form-group-descripcion">
										<label class="col-xs-5 control-label" for="input_text-descripcion">Descripcion</label>
										<div class="input-group col-xs-7">
											<input 
												type="text" 
												class="form-control" 
												id="input_text-descripcion"  
												name="edit_file_descripcion" 
												>
											<label class="input-group-addon glyphicon glyphicon-info-sign" ></label>
										</div>
									</div>									
								</div>						
							</div>
						</form>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
						<button type="submit" class="btn btn-success" name="" form="edit_file_form"><span class="glyphicon glyphicon-floppy-save"></span> Guardar</button>
					</div>
				</div>
			</div>
		</div>

		<div class="col-md-2 hidden-md hidden-lg">
			<ul class="nav nav-tabs contrato_tabs">
				<li class="active"><a class="tab_btn" href="#tab_contrato" data-tab="tab_contrato">Contrato</a></li>
				<li class=""><a class="tab_btn" href="#tab_archivos" data-tab="tab_archivos">Archivos</a></li>
				<li class=""><a class="tab_btn" href="#tab_comercial" data-tab="tab_comercial">Comercial</a></li>
			</ul>
			<br>
		</div>
		<div class="col-md-10 ">
			<div class="tab-content">
				<div class="tab-pane active" id="tab_contrato">
					<form class="form-horizontal" id="contrato_form">
						<input type="hidden" class="save_data" data-col="table" value="tbl_contratos">
						<input type="hidden" class="save_data" data-col="id" value="<?php echo $item_id;?>">
						<div class="row">
							<div class="col-xs-12 col-sm-6">
				                <div class="panel" id="datos_de_contrato">
				                    <div class="panel-heading">
				                        <div class="panel-title"><i class="icon fa fa-file-text-o muted"></i> Datos de Contrato</div>
				                    </div>
				                    <div id="panel-datos_de_contrato" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="panel-collapse-1-heading">
				                        <div class="panel-body">
				                            <div class="form-group form-group-id">
												<label class="col-xs-5 control-label" for="input_text-id">Contrato ID</label>
												<div class="input-group col-xs-7">
													<input 
														type="text" 
														class="form-control" 
														id="input_text-id" 
														value="<?php echo $item_id;?>" 
														readonly="readonly" 
														>
													<label class="input-group-addon glyphicon glyphicon-info-sign" ></label>
												</div>
											</div>
											<div class="form-group form-group-fecha_registro">
												<label class="col-xs-5 control-label" for="input_text-fecha_registro">Fecha de registro</label>
												<div class="input-group col-xs-7">
													<input 
										    			type="hidden" 
										    			class="input_text"
										    			data-col="fecha_registro"
														name="fecha_registro" 
										    			value="<?php echo $item["fecha_registro"]; ?>" 
										    			data-real-date="input_text-fecha_registro">
													<input 
														type="text" 
														class="form-control datepicker" 
														id="input_text-fecha_registro" 
														value="<?php echo date("d-m-Y", strtotime($item["fecha_registro"]));?>" 
														readonly="readonly" 
														>
													<label class="input-group-addon glyphicon glyphicon-calendar" for="input_text-fecha_registro"></label>
												</div>
											</div>
											<div class="form-group form-group-tipo_contrato_id">	
												<label class="col-xs-5 control-label" for="select-tipo_contrato_id">Tipo de Contrato</label>
												<div class="input-group col-xs-7">
													<select 										 
														class="form-control input_text" 
														data-col="tipo_contrato_id" 
														data-table="tbl_contratos"
														name="tipo_contrato_id" 
														id="select-tipo_contrato_id" 
														title="Seleccione el tipo de Contrato">
														<option value="">- Seleccione -</option>
														<?php
														$sel_query = $mysqli->query("SELECT id,nombre FROM tbl_contrato_tipos WHERE estado = '1' ORDER BY nombre ASC");
														while($sel=$sel_query->fetch_assoc()){
															?><option value="<?php echo $sel["id"];?>" <?php if($item["tipo_contrato_id"]==$sel["id"]){?> selected="selected" <?php } ?>><?php echo $sel["nombre"]; ?></option><?php
														}
														?>
													</select>													
													<label 
														class="input-group-addon glyphicon glyphicon-new-window select_add_dialog_btn"
														data-name="Tipo de Contrato"
														data-table="tbl_contrato_tipos"
														data-cols="nombre"
														data-select="select-tipo_contrato_id"
														data-cliente_id=""
														></label>
												</div>
											</div>
											<div class="form-group form-group-canal_de_venta_id">	
												<label class="col-xs-5 control-label" for="select-canal_de_venta_id">Canal de venta</label>
												<div class="input-group col-xs-7">
													<select 										 
														class="form-control input_text " 
														data-col="canal_de_venta_id" 
														data-table="tbl_contratos"
														name="canal_de_venta_id" 
														id="select-canal_de_venta_id" >
														<option value="">- Seleccione -</option>
														<?php
														$sel_query = $mysqli->query("SELECT id,nombre FROM tbl_canales_venta WHERE estado = '1' ORDER BY nombre ASC");
														while($sel=$sel_query->fetch_assoc()){
															?><option value="<?php echo $sel["id"];?>" <?php if($item["canal_de_venta_id"]==$sel["id"]){?> selected="selected" <?php } ?>><?php echo $sel["nombre"]; ?></option><?php
														}
														?>
													</select>																					
													<label 
														class="input-group-addon glyphicon glyphicon-new-window select_add_dialog_btn"
														data-name="Canal de Venta"
														data-table="tbl_canales_venta"
														data-cols="nombre"
														data-select="select-canal_de_venta_id"
														data-cliente_id=""
														></label>
												</div>
											</div>
				                        </div>
				                    </div>
				                </div>
				                <div class="panel" id="tiempo_de_contrato">
				                    <div class="panel-heading">
				                        <div class="panel-title"><i class="icon fa  fa-calendar-plus-o muted"></i> Tiempo de Contrato</div>
				                    </div>
				                    <div id="panel-tiempo_de_contrato" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="panel-collapse-1-heading">
				                        <div class="panel-body">
				                            <div class="form-group form-group-fecha_inicio_contrato">
												<label class="col-xs-5 control-label" for="input_text-fecha_inicio_contrato">Fecha de inicio</label>
												<div class="input-group col-xs-7">
													<input 
										    			type="hidden" 
										    			class="input_text"
										    			data-col="fecha_inicio_contrato"
														name="fecha_inicio_contrato" 
										    			value="<?php echo $item["fecha_inicio_contrato"]; ?>" 
										    			data-real-date="input_text-fecha_inicio_contrato">
													<input 
														type="text" 
														class="form-control datepicker" 
														id="input_text-fecha_inicio_contrato" 
														value="<?php echo date("d-m-Y", strtotime($item["fecha_inicio_contrato"]));?>" 
														readonly="readonly" 										 
														>
													<label class="input-group-addon glyphicon glyphicon-calendar" for="input_text-fecha_inicio_contrato"></label>
												</div>
											</div>
											<div class="form-group form-group-fecha_fin_contrato">
												<label class="col-xs-5 control-label" for="input_text-fecha_fin_contrato">Fecha de Fin</label>
												<div class="input-group col-xs-7">
													<input 
										    			type="hidden" 
										    			class="input_text"
										    			data-col="fecha_fin_contrato"
														name="fecha_fin_contrato" 
										    			value="<?php echo $item["fecha_fin_contrato"]; ?>" 
										    			data-real-date="input_text-fecha_fin_contrato">
													<input 
														type="text" 
														class="form-control datepicker" 
														id="input_text-fecha_fin_contrato" 
														value="<?php echo date("d-m-Y", strtotime($item["fecha_fin_contrato"]));?>" 
														readonly="readonly" 										 
														>
													<label class="input-group-addon glyphicon glyphicon-calendar" for="input_text-fecha_fin_contrato"></label>
												</div>
											</div>
											<div class="form-group form-group-tiempo_de_contrato">
												<label class="col-xs-5 control-label" for="input_text-tiempo_de_contrato">Tiempo de Contrato</label>
												<div class="input-group col-xs-7">
													<input 
														type="text" 
														class="form-control input_text " 
														data-col="tiempo_de_contrato" 
														name="tiempo_de_contrato" 
														data-table="tbl_contratos"
														id="input_text-tiempo_de_contrato" 
														placeholder="Tiempo de Contrato" 
														value="<?php echo $item["tiempo_de_contrato"];?>" 
														>
													<label class="input-group-addon glyphicon glyphicon-pencil" for="input_text-tiempo_de_contrato" ></label>
												</div>
											</div>
				                        </div>
				                    </div>
				                </div>
							</div>
							<div class="col-xs-12 col-sm-6">
				                <div class="panel" id="datos_del_cliente">
				                    <div class="panel-heading">
				                        <div class="panel-title"><i class="icon fa fa-user muted"></i> Datos del Cliente</div>
				                    </div>
				                    <div id="panel-datos_del_cliente" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="panel-collapse-1-heading">
				                        <div class="panel-body">
				                            <div class="form-group form-cliente_id">
												<label class="col-xs-5 control-label" for="select-cliente_id">Cliente</label>
												<div class="input-group col-xs-7">
													<select 
														 
														class="form-control input_text" 
														data-col="cliente_id" 
														data-table="tbl_contratos"
														name="cliente_id" 
														id="select-cliente_id" 
														title="Seleccione el tipo de Contrato">
														<option value="">- Seleccione -</option>
														<?php
														$sel_query = $mysqli->query("SELECT id,nombre,razon_social,tipo_cliente_id FROM tbl_clientes WHERE estado = '1' ORDER BY nombre ASC");
														while($sel=$sel_query->fetch_assoc()){
															?><option value="<?php echo $sel["id"];?>" <?php if($item["cliente_id"]==$sel["id"]){?> selected="selected" <?php } ?> ><?php 
																if($sel["tipo_cliente_id"]==2){
																	echo $sel["razon_social"];
																}elseif($sel["tipo_cliente_id"]==1){
																	echo $sel["nombre"];
																}else{
																	echo $sel["nombre"];
																}
																?></option><?php
														}
														?>
													</select>
													<label 
														class="input-group-addon glyphicon glyphicon-new-window select_add_dialog_btn"
														data-name="Cliente"
														data-table="tbl_clientes"
														data-cols="nombre"
														data-select="select-cliente_id"
														></label>
												</div>
											</div>
											<?php
											if($item_id=="new"){
												?>	                            
					                            <div class="form-group hidden form-group-dni_o_ruc">
													<label class="col-xs-5 control-label" for="input_text-id"></label>
													<div class="input-group col-xs-7">
														<input 
															type="text" 
															class="form-control" 
															id="input_text-dni_o_ruc" 
															value="???" 
															readonly="readonly" 
															>
														<label class="input-group-addon glyphicon glyphicon-info-sign" ></label>
													</div>
												</div>
												<?php
											}else{
												if($item["tipo_cliente_id"]==2){
													?>
						                            <div class="form-group form-group-dni_o_ruc">
														<label class="col-xs-5 control-label" for="input_text-id">RUC</label>
														<div class="input-group col-xs-7">
															<input 
																type="text" 
																class="form-control" 
																id="input_text-dni_o_ruc" 
																value="<?php echo $item["ruc"];?>" 
																readonly="readonly" 
																>
															<label class="input-group-addon glyphicon glyphicon-info-sign" ></label>
														</div>
													</div>
													<?php
												}elseif($item["tipo_cliente_id"]==1){
													?>
						                            <div class="form-group form-group-dni_o_ruc">
														<label class="col-xs-5 control-label" for="input_text-id">DNI</label>
														<div class="input-group col-xs-7">
															<input 
																type="text" 
																class="form-control" 
																id="input_text-dni_o_ruc" 
																value="<?php echo $item["dni"];?>" 
																readonly="readonly" 
																>
															<label class="input-group-addon glyphicon glyphicon-info-sign" ></label>
														</div>
													</div>
													<?php									
												}
											}
											?>
											<div class="form-group form-local_id">	
												<label class="col-xs-5 control-label" for="select-local_id">Local</label>
												<div class="input-group col-xs-7">
													
														<?php
														if($item_id=="new"){
															?>
															<select 
																disabled="disabled" 
																class="form-control input_text" 
																data-col="local_id" 
																name="local_id" 
																id="select-local_id" ">
																
															</select>										
															<label 
																class="input-group-addon glyphicon glyphicon-new-window select_add_dialog_btn hidden select_add_dialog_btn_local"
																data-name="Local"
																data-table="tbl_locales"
																data-cols="nombre"
																data-select="select-local_id"
																data-cliente_id=""
																></label>
															<?php
														}else{
															?>
															<select 
															class="form-control input_text" 
															data-col="local_id" 
															name="local_id" 
															id="select-local_id" ">
															<?php
																?>
															<option value="">- Seleccione un Cliente -</option>
															<?php
																	$sel_query = $mysqli->query("SELECT id,nombre FROM tbl_locales WHERE cliente_id = '".$item["cliente_id"]."' AND estado = '1' ORDER BY nombre ASC");
																	while($sel=$sel_query->fetch_assoc()){
																		?><option value="<?php echo $sel["id"];?>" <?php if($item["local_id"]==$sel["id"]){?> selected="selected" <?php } ?> ><?php 
																			echo $sel["nombre"];
																			?></option><?php
																	}
																?>
															</select>							
															<label 
																class="input-group-addon glyphicon glyphicon-new-window select_add_dialog_btn select_add_dialog_btn_local"
																data-name="Local"
																data-table="tbl_locales"
																data-cols="nombre"
																data-select="select-local_id"
																data-cliente_id=""
																></label>
															<?php
														}
														?>
													</select>
												</div>
											</div>
				                        </div>
				                    </div>
				                </div>		
							</div>
						</div>

						<div class="row">
							<div class="col-xs-12 col-md-6">
								<div class="panel" id="facturacion">
				                    <div class="panel-heading">
				                        <div class="panel-title"><i class="icon fa fa-money muted"></i> Facturacion</div>
				                    </div>
				                    <div id="panel-facturacion" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="panel-collapse-1-heading">
				                        <div class="panel-body">
				                            
				                        	<div class="form-group form-group-amortizacion_semanal">
												<label class="col-xs-7 control-label" for="input_text-amortizacion_semanal">Amortización Semanal</label>
												<div class="input-group col-xs-5">
													<div class="radio-inline">
							                            <label>
															<input type="radio" name="amortizacion_semanal" value="1" <?php if($item["amortizacion_semanal"]=="1"){?> checked="checked" <?php } ?> > Si
							                            </label>
							                        </div>
													<div class="radio-inline">
							                            <label>
															<input type="radio" name="amortizacion_semanal" value="0" <?php if($item["amortizacion_semanal"]==NULL){?> checked="checked" <?php } ?> > No
							                            </label>
													</div>
												</div>
											</div>
											<div class="form-group form-group-incluye_igv">
												<label class="col-xs-7 control-label" for="input_text-incluye_igv">Pago Incluye IGV</label>
												<div class="input-group col-xs-5">
													<div class="radio-inline">
							                            <label>
															<input type="radio" name="incluye_igv" value="1" <?php if($item["incluye_igv"]=="1"){?> checked="checked" <?php } ?> > Si
							                            </label>
							                        </div>
													<div class="radio-inline">
							                            <label>
															<input type="radio" name="incluye_igv" value="0" <?php if($item["incluye_igv"]==NULL){?> checked="checked" <?php } ?> > No
							                            </label>
													</div>
												</div>
											</div>
											<div class="form-group form-group-pagador">	
												<label class="col-xs-5 control-label" for="select-pagador">Pagador</label>
												<div class="input-group col-xs-7">
													<select 
														 
														class="form-control input_text" 
														data-col="pagador" 
														data-table="tbl_contratos"
														name="pagador" 
														id="select-pagador" >
														<option value="">- Seleccione -</option>
														<option value="1" <?php if($item["pagador"]==1){?> selected="selected" <?php } ?>>Operador Principal</option>
														<option value="0" <?php if($item["pagador"]==0){?> selected="selected" <?php } ?>>Agente</option>
													</select>
												</div>
											</div>
											<div class="form-group form-group-periodo_liquidacion_id">	
												<label class="col-xs-5 control-label" for="select-periodo_liquidacion_id">Periodo Liquidacion</label>
												<div class="input-group col-xs-7">
													<select 
														 
														class="form-control input_text" 
														data-col="periodo_liquidacion_id" 
														data-table="tbl_contratos"
														name="periodo_liquidacion_id" 
														id="select-periodo_liquidacion_id" >
														<option value="">- Seleccione -</option>
														<?php
														$sel_query = $mysqli->query("SELECT id,nombre FROM tbl_liquidacion_modalidad WHERE estado = '1' ORDER BY dias_proceso ASC");
														while($sel=$sel_query->fetch_assoc()){
															?><option value="<?php echo $sel["id"];?>" <?php if($item["periodo_liquidacion_id"]==$sel["id"]){?> selected="selected" <?php } ?>><?php echo $sel["nombre"]; ?></option><?php
														}
														?>
													</select>															
													<label 
														class="input-group-addon glyphicon glyphicon-new-window select_add_dialog_btn"
														data-name="Periodo Liquidacion"
														data-table="tbl_liquidacion_modalidad"
														data-cols="nombre"
														data-select="select-periodo_liquidacion_id"
														data-cliente_id=""
														></label>
												</div>
											</div>
											<div class="form-group form-group-red_tipo_id">	
												<label class="col-xs-5 control-label" for="select-red_tipo_id">Tipo Red</label>
												<div class="input-group col-xs-7">
													<select 
														 
														class="form-control input_text" 
														data-col="red_tipo_id" 
														data-table="tbl_contratos"
														name="red_tipo_id" 
														id="select-red_tipo_id" >
														<option value="">- Seleccione -</option>
														<?php
														$sel_query = $mysqli->query("SELECT id,nombre FROM tbl_contrato_tipos_de_red WHERE estado = '1' ORDER BY nombre ASC");
														while($sel=$sel_query->fetch_assoc()){
															?><option value="<?php echo $sel["id"];?>" <?php if($item["red_tipo_id"]==$sel["id"]){?> selected="selected" <?php } ?>><?php echo $sel["nombre"]; ?></option><?php
														}
														?>
													</select>																				
													<label 
														class="input-group-addon glyphicon glyphicon-new-window select_add_dialog_btn"
														data-name="Tipo Red"
														data-table="tbl_contrato_tipos_de_red"
														data-cols="nombre"
														data-select="select-red_tipo_id"
														data-cliente_id=""
														></label>
												</div>
											</div>
											<div class="form-group form-group-documento_tipo_id">	
												<label class="col-xs-5 control-label" for="select-documento_tipo_id">Tipo de Documento</label>
												<div class="input-group col-xs-7">
													<select 
														 
														class="form-control input_text" 
														data-col="documento_tipo_id" 
														data-table="tbl_contratos"
														name="documento_tipo_id" 
														id="select-documento_tipo_id" >
														<option value="">- Seleccione -</option>
														<?php
														$sel_query = $mysqli->query("SELECT id,nombre FROM tbl_facturacion_documento_tipos WHERE estado = '1' ORDER BY nombre ASC");
														while($sel=$sel_query->fetch_assoc()){
															?><option value="<?php echo $sel["id"];?>" <?php if($item["documento_tipo_id"]==$sel["id"]){?> selected="selected" <?php } ?>><?php echo $sel["nombre"]; ?></option><?php
														}
														?>
													</select>																			
													<label 
														class="input-group-addon glyphicon glyphicon-new-window select_add_dialog_btn"
														data-name="Tipo de Documento"
														data-table="tbl_facturacion_documento_tipos"
														data-cols="nombre"
														data-select="select-documento_tipo_id"
														data-cliente_id=""
														></label>
												</div>
											</div>
											<div class="form-group form-group-moneda_id">	
												<label class="col-xs-5 control-label" for="select-moneda_id">Moneda</label>
												<div class="input-group col-xs-7">
													<select 
														 
														class="form-control input_text" 
														data-col="moneda_id" 
														data-table="tbl_contratos"
														name="moneda_id" 
														id="select-moneda_id" >
														<option value="">- Seleccione -</option>
														<?php
														$sel_query = $mysqli->query("SELECT id,nombre,simbolo,sigla FROM tbl_moneda WHERE estado = '1' ORDER BY nombre ASC");
														while($sel=$sel_query->fetch_assoc()){
															?><option value="<?php echo $sel["id"];?>" <?php if($item["moneda_id"]==$sel["id"]){?> selected="selected" <?php } ?>><?php echo $sel["sigla"]; ?> - <?php echo $sel["nombre"]; ?> (<?php echo $sel["simbolo"]; ?>)</option><?php
														}
														?>
													</select>																	
													<label 
														class="input-group-addon glyphicon glyphicon-new-window select_add_dialog_btn"
														data-name="Moneda"
														data-table="tbl_moneda"
														data-cols="nombre"
														data-select="select-moneda_id"
														data-cliente_id=""
														></label>
												</div>
											</div>


				                        </div>
				                    </div>
				                </div>	
							</div>
							<div class="col-xs-12 col-md-6">
								<div class="panel" id="observaciones">
				                    <div class="panel-heading">
				                        <div class="panel-title"><i class="icon fa fa-commenting muted"></i> Observaciones / Consulta</div>
				                    </div>
				                    <div id="panel-observaciones" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="panel-collapse-1-heading">
				                        <div class="panel-body">
											<div class="form-group form-group_direccion">
												<label class="col-xs-4 control-label">¿Cómo se enteró de nosotros?</label>
												<div class="input-group col-xs-8">
													<div class="radio-inline">
							                            <label>
															<input type="radio" name="como_se_entero" value="radio" <?php if($item["como_se_entero"]=="radio"){?> checked="checked" <?php } ?> > Radio
							                            </label>
													</div>
													<div class="radio-inline">
							                            <label>
															<input type="radio" name="como_se_entero" value="redessociales" <?php if($item["como_se_entero"]=="redessociales"){?> checked="checked" <?php } ?> > Redes Sociales
							                            </label>
							                        </div>
													<div class="radio-inline">
							                            <label>
															<input type="radio" name="como_se_entero" value="web" <?php if($item["como_se_entero"]=="web"){?> checked="checked" <?php } ?> > Web
							                            </label>
							                        </div>
													<div class="radio-inline">
							                            <label>
															<input type="radio" name="como_se_entero" value="amigos" <?php if($item["como_se_entero"]=="amigos"){?> checked="checked" <?php } ?> > Amigos
							                            </label>
							                        </div>
													<div class="radio-inline">
							                            <label>
															<input type="radio" name="como_se_entero" value="otros" <?php if($item["como_se_entero"]=="otros"){?> checked="checked" <?php } ?> > Otros
							                            </label>
							                        </div>
													<div class="<?php if($item["como_se_entero"]!="otros"){?>hidden_form<?php } ?> hide_form_como_se_entero_des">
														<textarea 
															placeholder="Detallar aquí" 
															id="form-como_se_entero_des" 
															class="form-control input_text" 
															name="como_se_entero_des" 
															data-col="como_se_entero_des"
															rows="2"><?php echo $item["como_se_entero_des"];?></textarea>
													</div>
												</div>
											</div>
				                            <div class="form-group form-group-observa_vendedor">
												<label class="col-xs-12 " for="input-text-observa_vendedor ">Vendedor</label>
												<div class="input-group col-xs-12">
													<textarea
														class="form-control input_text " 
														data-col="observa_vendedor" 
														name="observa_vendedor" 
														id="input-text-observa_vendedor" 
														placeholder="Observaciones" 
														title="" 
														rows="7"
														><?php echo $item["observa_vendedor"];?></textarea>
												</div>
											</div>

				                        </div>
				                    </div>
				                </div>
							</div>
						</div>
						<div class="col-xs-10 col-xs-offset-1">
							
								
						</div>
					</form>
				</div>
				<div class="tab-pane" id="tab_archivos">
					<div class="row">
						<div class="col-xs-12">
							<button class="btn btn-success add_file_modal_btn" data-target="#add_file_modal">Agregar Archivo</button>
						</div>
					</div>
					<div class="row">
						<div class="col-xs-12">
							<?php
							$files = array();
							$get_files_sql = "SELECT id, tipo, ext, size, nombre, descripcion, archivo, fecha, orden, estado
											FROM tbl_archivos
											WHERE tabla = 'tbl_contratos'
											AND item_id = '".$item_id."'
											AND estado = '1'
											ORDER BY orden ASC";
							$get_files_query = $mysqli->query($get_files_sql);
							while($get_file=$get_files_query->fetch_assoc()){
								$files[]=$get_file;
							}
							$images_exts = array("jpg","png","gif");

							?>
							<div class="files-holder sort_list" data-sort-tabla="tbl_archivos">
								<?php
								foreach ($files as $f_k => $f_v) {
									?>
									<div class="file-item sort_item" 
										id="file-item-<?php echo $f_v["id"];?>" 
										data-id="<?php echo $f_v["id"];?>"
										data-sort-id="<?php echo $f_v["id"];?>">
										<div class="file-thumb">
											<?php
											if(in_array($f_v["ext"], $images_exts)){
												?>
												<img class="img-thumbnail" src="files/<?php echo $f_v["archivo"];?>">
												<?php
											}elseif($f_v["ext"]=="pdf"){
												?><span class="icon fa fa-fw fa-file-pdf-o"></span><?php
											}elseif($f_v["ext"]=="xls" || $f_v["ext"]=="xlsx"){
												?><span class="icon fa fa-fw fa-file-excel-o"></span><?php
											}
											?>
										</div>
										<div class="file-name"><?php echo $f_v["nombre"];?></div>
										<div class="file-opciones">
											<button 
												tabindex="0" 
												class="btn btn-default btn-xs" 
												role="button" 
												data-toggle="popover" 
												data-trigger="focus" 
												title="" 
												data-placement="top" 
												data-html="true"
												data-content="
															Nombre: <?php echo $f_v["nombre"];?> <br>
															Tamaño: <?php echo number_format(($f_v["size"])/1024);?>Kb <br>
															Fecha: <?php echo $f_v["fecha"];?> <br>
															Descripcion: <?php echo $f_v["descripcion"];?>
												" >
												<span class="glyphicon glyphicon-info-sign"></span>
											</button>
											<button 
												class="btn btn-default btn-xs edit_file_btn" 
												data-id="<?php echo $f_v["id"];?>"
												data-nombre="<?php echo $f_v["nombre"];?>"
												data-descripcion="<?php echo $f_v["descripcion"];?>">
												<span class="glyphicon glyphicon-edit"></span>
											</button>
											<button class="btn btn-default btn-xs del_file_btn" data-id="<?php echo $f_v["id"];?>">
												<span class="glyphicon glyphicon-remove"></span>
											</button>
											<a class="btn btn-default btn-xs" href="files/<?php echo $f_v["archivo"];?>" target="_blank">
												<span class="glyphicon glyphicon-new-window"></span>
											</a>
										</div>
									</div>
									<?php
								}
								?>
							</div>
						</div>
					</div>
				</div>
				<div class="tab-pane" id="tab_comercial">
					<div class="row">
						<div class="col-xs-12 col-sm-5">
							<div class="panel" id="comercial">
			                    <div class="panel-heading">
			                        <div class="panel-title"><i class="icon fa fa-handshake-o muted"></i> Comercial</div>                        
			                    </div>
			                    <div id="panel-comercial" class="panel-collapse collapse in" role="tabpanel"
			                         aria-labelledby="panel-collapse-1-heading">
			                        <div class="panel-body">
			                            
			                        	<div class="form-group form-group-limite_pago_id">	
											<label class="col-xs-4 control-label" for="select-limite_pago_id">Limite Pagos</label>
											<div class="input-group col-xs-8">
												<select 
													 
													class="form-control input_text" 
													data-col="limite_pago_id" 
													data-table="tbl_contratos"
													name="limite_pago_id" 
													id="select-limite_pago_id" >
													<option value="">- Seleccione -</option>
													<?php
													$sel_query = $mysqli->query("SELECT id,nombre FROM tbl_contrato_pago_premios_limite WHERE estado = '1' ORDER BY nombre ASC");
													while($sel=$sel_query->fetch_assoc()){
														?><option value="<?php echo $sel["id"];?>" <?php if($item["limite_pago_id"]==$sel["id"]){?> selected="selected" <?php } ?>><?php echo $sel["nombre"]; ?></option><?php
													}
													?>
												</select>
											</div>
										</div>
										<div class="form-group form-group-limite_monto">
											<label class="col-xs-4 control-label" for="input_text-limite_monto">Monto Limite</label>
											<div class="input-group col-xs-8">
												<input 
													type="text" 
													class="form-control input_text " 
													data-col="limite_monto" 
													data-table="tbl_contratos"
													name="limite_monto" 
													id="input_text-limite_monto" 
													value="<?php echo $item["limite_monto"];?>" 
													placeholder="Ingrese el monto" 
													title="" 
													>
												<label class="input-group-addon glyphicon glyphicon-pencil" for="input_text-limite_monto" ></label>
											</div>
										</div>
										<div class="form-group form-group-negativo_devolucion_tipo_id">	
											<label class="col-xs-4 control-label" for="select-negativo_devolucion_tipo_id">Devolucion Negativa</label>
											<div class="input-group col-xs-8">
												<select 
													 
													class="form-control input_text" 
													data-col="negativo_devolucion_tipo_id" 
													data-table="tbl_contratos"
													name="negativo_devolucion_tipo_id" 
													id="select-negativo_devolucion_tipo_id" >
													<option value="">- Seleccione -</option>
													<?php
													$sel_query = $mysqli->query("SELECT id,nombre FROM tbl_contrato_negativo_devolucion_tipos WHERE estado = '1' ORDER BY nombre ASC");
													while($sel=$sel_query->fetch_assoc()){
														?><option value="<?php echo $sel["id"];?>" <?php if($item["negativo_devolucion_tipo_id"]==$sel["id"]){?> selected="selected" <?php } ?>><?php echo $sel["nombre"]; ?></option><?php
													}
													?>
												</select>
											</div>
										</div>

										<div class="form-group form-group-corte_no_pago">	
											<label class="col-xs-4 control-label" for="select-corte_no_pago">Corte No Pago</label>
											<div class="input-group col-xs-8">
												<select 
													class="form-control input_text" 
													data-col="corte_no_pago" 
													data-table="tbl_contratos"
													name="corte_no_pago" 
													id="select-corte_no_pago" >
													<option value="">- Seleccione -</option>
													<option value="1" <?php if($item["corte_no_pago"]==1){?> selected="selected" <?php } ?>>Si</option>
													<option value="0" <?php if($item["corte_no_pago"]==0){?> selected="selected" <?php } ?>>No</option>
													<option value="2" <?php if($item["corte_no_pago"]==2){?> selected="selected" <?php } ?>>Consultar</option>
												</select>
											</div>
										</div>


										<div class="form-group form-group-observa_comercial">
											<label class="col-xs-12 " for="input-text-observa_comercial">Comercial</label>
											<div class="input-group col-xs-12">
												<textarea
													class="form-control input_text " 
													data-col="observa_comercial" 
													name="observa_comercial" 
													id="input-text-observa_comercial" 
													placeholder="Observaciones" 
													title="" 
													rows="8"
													><?php echo $item["observa_comercial"];?></textarea>
											</div>
										</div>

			                        </div>
			                    </div>
			                </div>
                		</div>
						<div class="col-xs-12 col-sm-7">
							<div class="panel" id="productos">
			                    <div class="panel-heading">
			                        <div class="panel-title"><i class="icon fa fa-handshake-o muted"></i> Productos</div>									
			                    </div>
			                    <div id="panel-productos" class="panel-collapse collapse in" role="tabpanel"
			                         aria-labelledby="panel-collapse-1-heading">
			                        <div class="panel-body">
			                        	<?php if(array_key_exists($this_menu["id"], $usuario_permisos) && in_array("save", $usuario_permisos[$this_menu["id"]])){ ?>
			                        		<button class="btn btn-sm btn-primary contrato_add_producto_dialog_btn" data-opt="show">Agregar Producto</button>
			                        	<?php }else{ ?>
			                        		<button class="btn btn-sm btn-primary no_permiso disabled">Agregar Producto</button><br><br>
			                        	<?php } ?>
			                        	<div class="panel form-add_producto hidden">
			                        		<div class="panel-body">
												<label class="col-xs-4 col-md-2 control-label" for="select-add_producto">Producto</label>
												<div class="col-xs-8 col-md-5">
													<div class="input-group ">
														<select 														 
															class="form-control" 
															data-col="add_producto" 
															data-table="tbl_productos"
															name="add_producto" 
															id="select-add_producto" 
															title="Seleccione el tipo de Contrato">
															<option value="">- Seleccione -</option>
															<?php
															$sel_query = $mysqli->query("SELECT id,nombre FROM tbl_productos WHERE estado = '1' ORDER BY nombre ASC");
															while($sel=$sel_query->fetch_assoc()){
																?><option value="<?php echo $sel["id"];?>"><?php 
																		echo $sel["nombre"];
																	?></option><?php
															}
															?>
														</select>
														<label 
															class="input-group-addon glyphicon glyphicon-new-window select_add_dialog_btn"
															data-name="Producto"
															data-table="tbl_productos"
															data-cols="nombre"
															data-select="select-add_producto"
															></label>
													</div>
												</div>
												<div class="col-xs-12 col-md-5">
													<button class="btn btn-default btn-sm contrato_add_producto_btn">Agregar</button>
													<button class="btn btn-default btn-sm contrato_cancel_producto_dialog_btn" data-opt="hide">Cancelar</button>
												</div>
											</div>
										</div>
										<?php
										$pro_query = $mysqli->query("SELECT cp.id, p.nombre AS pro_nombre
																	FROM tbl_contrato_productos cp
																	LEFT JOIN tbl_productos p ON(p.id = cp.producto_id)
																	WHERE cp.estado = '1' 
																	AND cp.contrato_id = '".$item_id."'
																	ORDER BY cp.id DESC");
										while($pro=$pro_query->fetch_assoc()){
											?>
											<div class="panel">
												<div class="panel-heading">
													<div class="panel-title"><?php echo $pro["pro_nombre"]; ?></div>
													<div class="panel-controls">
														<ul class="panel-buttons">
															<li>
																<a href="#panel-collapse-<?php echo $pro["id"];?>" class="btn-panel-control icon icon-panel-collapse"
															role="button" data-toggle="collapse" aria-expanded="true"></a>
															</li>
														</ul>
													</div>
												</div>
												<div id="panel-collapse-<?php echo $pro["id"];?>" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="panel-collapse-1-heading">
													<div class="panel-body">
														<?php
							                        	$contrato_formula = array();
							                        	$con_for_det_que_command = "SELECT id,formula_id,desde,hasta,monto 
							                        												FROM tbl_contrato_formulas 
							                        												WHERE contrato_id = '".$item["id"]."'
							                        												AND producto_id = '".$pro["id"]."'
							                        												AND estado = '1'
							                        												ORDER BY id ASC";
							                        	$con_for_det_que = $mysqli->query($con_for_det_que_command);
							                        	while($det=$con_for_det_que->fetch_assoc()){
							                        		$contrato_formula[]=$det;
							                        	}
							                        	//print_r($contrato_formula);
							                        	$formula=false;
							                        	if(count($contrato_formula)){
							                        		$formula = $mysqli->query("SELECT ff.id, ff.nombre, ff.aplica, ff.tipo_id
																								,participante.nombre AS participante_nombre
																								,servicio.nombre AS servicio_nombre
																								,tipo.nombre AS tipo_nombre
																								,operador.operador AS operador_nombre
																								,sobre.nombre AS sobre_nombre
																								,sobre.descripcion AS sobre_descripcion
																								,moneda.simbolo AS moneda_simbolo
																						FROM tbl_facturacion_formulas ff
																						LEFT JOIN tbl_facturacion_participantes participante ON (participante.id = ff.participante_id)
																						LEFT JOIN tbl_facturacion_servicios servicio ON (servicio.id = ff.servicio_id)
																						LEFT JOIN tbl_facturacion_tipos tipo ON (tipo.id = ff.tipo_id)
																						LEFT JOIN tbl_facturacion_operadores operador ON (operador.id = ff.operador_id)
																						LEFT JOIN tbl_facturacion_sobre sobre ON (sobre.id = ff.sobre_id)
																						LEFT JOIN tbl_moneda moneda ON (moneda.id = ff.moneda_id)
																						WHERE ff.id = '".$contrato_formula[0]["formula_id"]."'")->fetch_assoc();
															?>
															<?php if(array_key_exists($this_menu["id"], $usuario_permisos) && in_array("save", $usuario_permisos[$this_menu["id"]])){ ?>
																<button class="btn btn-primary btn-block contrato_change_formula_btn" data-pro-id="<?php echo $pro["id"];?>">Cambiar Formula</button>
															<?php }else{ ?>
																<button class="btn btn-primary btn-block no_permiso disabled">Cambiar Formula</button>
															<?php } ?>
															<br>
															<?php
							                        	}

							                        	?>
							                        	<?php
														if($formula){
															?>
								                        	<div class="col-xs-12 panel formula_holder" data-pro-id="<?php echo $pro["id"];?>">
																<table class="table table-striped table-user-information hidden-xs hidden-sm hidden-md">
																	<tr>
																		<td class="td-title">Nombre</td>
																		<td><?php echo $formula["nombre"];?></td>
																		<td class="td-title">Tipo</td>
																		<td><?php echo $formula["tipo_nombre"];?></td>
																	</tr>
																	<tr>
																		<td class="td-title">Participante</td>
																		<td><?php echo $formula["participante_nombre"];?></td>
																		<td class="td-title">Operador</td>
																		<td><?php echo $formula["operador_nombre"];?></td>
																	</tr>
																	<tr>
																		<td class="td-title">Sobre</td>
																		<td title="<?php echo $formula["sobre_descripcion"];?>"><?php echo $formula["sobre_nombre"];?></td>
																		<td class="td-title">Moneda</td>
																		<td><?php echo $formula["moneda_simbolo"];?></td>
																	</tr>
																</table>
																<table class="table table-striped table-user-information hidden-lg">
																	<tr>
																		<td class="td-title">Nombre</td>
																		<td><?php echo $formula["nombre"];?></td>
																	</tr>
																	<tr>
																		<td class="td-title">Tipo</td>
																		<td><?php echo $formula["tipo_nombre"];?></td>
																	</tr>
																	<tr>
																		<td class="td-title">Participante</td>
																		<td><?php echo $formula["participante_nombre"];?></td>
																	</tr>
																	<tr>
																		<td class="td-title">Operador</td>
																		<td><?php echo $formula["operador_nombre"];?></td>
																	</tr>
																	<tr>
																		<td class="td-title">Sobre</td>
																		<td><?php echo $formula["sobre_nombre"];?></td>
																	</tr>
																	<tr>
																		<td class="td-title">Moneda</td>
																		<td><?php echo $formula["moneda_simbolo"];?></td>
																	</tr>
																</table>
															</div>
								                        	<div class="col-xs-12 panel formula_holder" data-pro-id="<?php echo $pro["id"];?>">
																<table class="table table-striped table-user-information">
																	<?php
										                        	if(count($contrato_formula)==1){					                        	
										                        		?>
																		<tr>
																			<td class="td-title"></td>
																			<td></td>
																			<td class="td-title">Monto</td>
																			<td><?php echo $contrato_formula[0]["monto"];?></td>
																		</tr>
										                        		<?php
										                        	}if(count($contrato_formula)>1){
										                        		?>
																		<tr>
																			<td class="td-title">Desde</td>
																			<td class="td-title">Hasta</td>
																			<td class="td-title">Monto</td>
																		</tr>
										                        		<?php
										                        		foreach ($contrato_formula as $key => $value) {
										                        			?>
										                        			<tr>
																				<td><?php echo $value["desde"];?></td>
																				<td><?php echo $value["hasta"];?></td>
																				<td><?php echo $value["monto"];?></td>
																			</tr>
										                        			<?php
										                        		}
										                        	}else{
										                        	}
										                        	?>
																</table>
															</div>
															<?php
														}
														?>
							                            <div class="col-xs-12 panel contrato_change_formula_holder <?php if(count($contrato_formula)){ ?> hidden <?php } ?>" data-pro-id="<?php echo $pro["id"];?>">
							                            <br>

								                            <div class="form-group form-formula_id-<?php echo $pro["id"];?>">	
																<label class="col-xs-5 control-label" for="select-formula_id-<?php echo $pro["id"];?>">Formula</label>
																<div class="input-group col-xs-7">
																	<select 
																		class="form-control select-formula_id save_extra" 
																		name="formula_id" 
																		id="select-formula_id-<?php echo $pro["id"];?>" 
																		data-pro-id="<?php echo $pro["id"];?>">
																		<option value="">- Seleccione -</option>
																		<?php
																		$sel_query = $mysqli->query("SELECT id,nombre FROM tbl_facturacion_formulas WHERE estado = '1' ORDER BY nombre ASC");
																		while($sel=$sel_query->fetch_assoc()){
																			?><option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"]; ?></option><?php
																		}
																		?>
																	</select>
																	<label 
																		class="input-group-addon glyphicon glyphicon-new-window select_add_dialog_btn"
																		data-name="Formula"
																		data-table="tbl_facturacion_formulas"
																		data-cols="nombre"
																		data-select="select-formula_id-<?php echo $pro["id"];?>"
																		></label>
																</div>
															</div>
															<div class="formula_data_holder" id="formula_data_holder-<?php echo $pro["id"];?>">
															</div>
															<br>
														</div>
													</div>
												</div>
											</div>												
											<?php
										}
										?>
			                        </div>
			                    </div>
			                </div>
						</div>
                	</div>
				</div>
			</div>
		</div>

		<div class="col-md-2 hidden-xs hidden-sm">
			<ul class="nav nav-tabs tabs-right contrato_tabs">
				<li class="active"><a class="tab_btn" href="#tab_contrato" data-tab="tab_contrato">Contrato</a></li>
				<li class=""><a class="tab_btn" href="#tab_archivos" data-tab="tab_archivos">Archivos</a></li>
				<li class=""><a class="tab_btn" href="#tab_comercial" data-tab="tab_comercial">Comercial</a></li>
			</ul>
		</div>
		<?php
	}else{

		$list_query=$mysqli->query("SELECT 
									con.id, con.estado, con.descripcion, con.fecha_registro, con.fecha_inicio_contrato, con.fecha_fin_contrato, con.tiempo_de_contrato, con.juegos_virtuales, con.apuestas_deportivas, con.terminales, con.recargas_web, con.amortizacion_semanal, con.incluye_igv, con.pagador, con.limite_monto, con.corte_no_pago
									,con_tipo.nombre AS contrato_tipo
									,cli.nombre, cli.razon_social, cli.tipo_cliente_id, cli.dni, cli.ruc
									,cli_tipo.nombre AS cliente_tipo
									,loc.tipo_id, loc.nombre AS nombre_de_local
									,loctipo.nombre AS tipo_de_local
									,canal.nombre AS canal_de_venta
									,liq_mod.nombre AS periodo_liquidacion
									,con_tipo_red.nombre AS tipo_red
									,fac_doc_tipo.nombre AS documento_tipo
									,mon.sigla AS moneda
									,lim_pagos.nombre AS limite_pagos
									,neg_tip.nombre AS devo_negativa
									,pro.nombre AS producto
									,ser.nombre AS servicio
										FROM tbl_contratos con
										LEFT JOIN tbl_contrato_tipos con_tipo ON (con_tipo.id = con.tipo_contrato_id)
										LEFT JOIN tbl_clientes cli ON (cli.id = con.cliente_id)
										LEFT JOIN tbl_cliente_tipos cli_tipo ON (cli_tipo.id = cli.tipo_cliente_id)
										LEFT JOIN tbl_locales loc ON (loc.id = con.local_id)
										LEFT JOIN tbl_local_tipo loctipo ON (loctipo.id = loc.tipo_id)
										LEFT JOIN tbl_canales_venta canal ON (canal.id = con.canal_de_venta_id)
										LEFT JOIN tbl_liquidacion_modalidad liq_mod ON (liq_mod.id = con.periodo_liquidacion_id)
										LEFT JOIN tbl_contrato_tipos_de_red con_tipo_red ON (con_tipo_red.id = con.red_tipo_id)
										LEFT JOIN tbl_facturacion_documento_tipos fac_doc_tipo ON (fac_doc_tipo.id = con.documento_tipo_id)
										LEFT JOIN tbl_moneda mon ON (mon.id = con.moneda_id)
										LEFT JOIN tbl_contrato_pago_premios_limite lim_pagos ON (lim_pagos.id = con.limite_pago_id)
										LEFT JOIN tbl_contrato_negativo_devolucion_tipos neg_tip ON (neg_tip.id = con.negativo_devolucion_tipo_id)
										LEFT JOIN tbl_productos pro ON (pro.id = con.producto_id)
										LEFT JOIN tbl_servicios ser ON (ser.id = con.servicio_id)
										");
		$table_cols = array();
		/*$sql_cols = "SHOW COLUMNS FROM tbl_contratos";
		$result = $mysqli->query($sql_cols);
		while($row=$result->fetch_array()){
			$table_cols[$row['Field']]="";
		}*/
		$list=array();
		while ($li=$list_query->fetch_assoc()) {
			//$table_cols[$li[]]
			$li["cliente"]=$li["nombre"];


			/*if($li["tipo_cliente_id"]==2){
				$li["cliente"]=$li["razon_social"];
				$li["dni_o_ruc"]=$li["ruc"];
			}elseif($li["tipo_cliente_id"]==1){
				$li["cliente"]=$li["nombre"];
				$li["dni_o_ruc"]=$li["dni"];
			}else{
				$li["cliente"]=$li["nombre"];
				$li["dni_o_ruc"]=$li["dni"];
			}*/
			

			($li["juegos_virtuales"] ? $li["juegos_virtuales"]="Si" : $li["juegos_virtuales"]="No");
			($li["apuestas_deportivas"] ? $li["apuestas_deportivas"]="Si" : $li["apuestas_deportivas"]="No");
			($li["terminales"] ? $li["terminales"]="Si" : $li["terminales"]="No");
			($li["recargas_web"] ? $li["recargas_web"]="Si" : $li["recargas_web"]="No");

			($li["amortizacion_semanal"] ? $li["amortizacion_semanal"]="Si" : $li["amortizacion_semanal"]="No");
			($li["incluye_igv"] ? $li["incluye_igv"]="Si" : $li["incluye_igv"]="No");	

			($li["pagador"] ? $li["pagador"]="Operador Principal" : $li["pagador"]="Agente");

			($li["corte_no_pago"] ? $li["corte_no_pago"]="Si" : $li["corte_no_pago"]="No");	

			$li["fecha_registro"] = date("d-m-Y", strtotime($li["fecha_registro"]));
			$li["fecha_inicio_contrato"] = date("d-m-Y", strtotime($li["fecha_inicio_contrato"]));
			$li["fecha_fin_contrato"] = date("d-m-Y", strtotime($li["fecha_fin_contrato"]));
			//($li["periodo_liquidacion_id"] ? $li["periodo_liquidacion_id"]="Operador Principal" : $li["pagador"]="Agente");		

			$list[]=$li;
		}
		$list_cols=array();
			$list_cols["id"]="ID";
			$list_cols["fecha_registro"]="Fecha de Registro";
			$list_cols["canal_de_venta"]="Canal de venta";
			$list_cols["contrato_tipo"]="Tipo de Contrato";
			$list_cols["fecha_inicio_contrato"]="Fecha de Inicio";
			$list_cols["fecha_fin_contrato"]="Fecha de Fin";
			$list_cols["tiempo_de_contrato"]="Tiempo de contrato";
			$list_cols["cliente_tipo"]="Tipo de Cliente";

			$list_cols["cliente"]="Cliente";
			$list_cols["dni"]="DNI";
			$list_cols["razon_social"]="Razón Social";
			$list_cols["ruc"]="RUC";

			//$list_cols["dni_o_ruc"]="DNI/RUC";
			$list_cols["tipo_de_local"]="Tipo de Local";
			$list_cols["nombre_de_local"]="Nombre de Local";

			//$list_cols["juegos_virtuales"]="Juegos Virtuales";
			//$list_cols["apuestas_deportivas"]="Apuestas Deportivas";
			//$list_cols["terminales"]="Terminales";
			//$list_cols["recargas_web"]="Recargas Web";
			$list_cols["producto"]="Producto";
			$list_cols["servicio"]="Servicio";

			$list_cols["amortizacion_semanal"]="Amortización Semanal";
			$list_cols["incluye_igv"]="Pago Incluye IGV";
			$list_cols["pagador"]="Pagador";
			$list_cols["periodo_liquidacion"]="Periodo Liquidacion";
			$list_cols["tipo_red"]="Tipo Red";
			$list_cols["documento_tipo"]="Tipo de Documento";
			$list_cols["moneda"]="Moneda";

			$list_cols["limite_pagos"]="Limite Pagos";
			$list_cols["limite_monto"]="Limite Monto";
			$list_cols["devo_negativa"]="Devolucion Negativa";
			$list_cols["corte_no_pago"]="Corte No Pago";

			$list_cols["estado"]="Estado";
			$list_cols["opciones"]="Opciones";

			
		?>
		<?php
		$view_cols = array("id","fecha_registro","canal_de_venta","contrato_tipo","cliente");
		if(isset($_POST["contratos_list_cols_submit"])){
			if(isset($_POST["contratos_list_cols"])){
				$view_cols=$_POST["contratos_list_cols"];
			}else{
				$view_cols=array();
			}
		}elseif(isset($_COOKIE["contratos_list_cols"])){
			$view_cols = json_decode($_COOKIE['contratos_list_cols'], true);
		}else{
			//$list_cols_show = $view_cols;
		}
		$view_cols[]="id";
		$view_cols[]="estado";
		$view_cols[]="opciones";
		foreach ($list_cols as $key => $value) {
			if(in_array($key, $view_cols)){
				$list_cols_show[$key]=$value;
			}
		}
		//print_r($view_cols);
		?>
		<!-- Modal -->
		<div class="modal fade" id="filter_holder" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
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
										<li class="checkbox">
											<label>
												<input 
													type="checkbox" 
													value="<?php echo $key;?>" 
													name="contratos_list_cols[]"
													<?php if(in_array($key, array("id","estado","opciones"))){ ?>disabled="disabled"<?php } ?>
													<?php if(in_array($key, $view_cols)){ ?>checked="checked"<?php } ?>>
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

		<table 
			id="contratos_list"
			class="table table-striped table-hover table-condensed table-bordered display nowrap" cellspacing="0" width="100%">
            <thead>
                <tr>
                	<?php
                	foreach ($list_cols_show as $key => $value) {
                		if($key=="id"){
                			?><th id="th_<?php echo $key;?>" class="w-25px">ID</th><?php
                		}elseif($key=="opciones"){
                			?><th id="th_<?php echo $key;?>" class="w-85px">Opciones</th><?php
                		}elseif($key=="estado"){
                			?><th id="th_<?php echo $key;?>" class="w-85px">Estado</th><?php
                		}else{
	                		?>
	                		<th id="th_<?php echo $key;?>"><?php echo $value;?></th>
	                		<?php
	                	}
                	}
                	?>		                    
                </tr>
            </thead>
			<tfoot>
                <tr>
                	<?php
                	foreach ($list_cols_show as $key => $value) {
                		if($key=="id"){
                			?><th id="th_<?php echo $key;?>" class="w-25px">ID</th><?php
                		}elseif($key=="opciones"){
                			?><th id="th_<?php echo $key;?>" class="w-85px">Opciones</th><?php
                		}elseif($key=="estado"){
                			?><th id="th_<?php echo $key;?>" class="w-85px">Estado</th><?php
                		}else{
	                		?>
	                		<th id="th_<?php echo $key;?>"><?php echo $value;?></th>
	                		<?php
	                	}
                	}
                	?>		                    
                </tr>
			</tfoot>             
            <tbody>
            	<?php 
            	foreach ($list as $l_k => $l_v) {
                	?>	
	                <tr>			                	
	                	<?php
	                	foreach ($list_cols_show as $key => $value) {
	                		if($key=="opciones"){
	                			?>
								<td class="text-center">
									<?php if(array_key_exists($this_menu["id"], $usuario_permisos) && in_array("edit", $usuario_permisos[$this_menu["id"]])){ ?>
									<a 
										class="btn btn-rounded btn-default btn-sm btn-edit" 
										title="Editar"
										href="./?sec_id=<?php echo $sec_id;?>&amp;item_id=<?php echo $l_v["id"];?>">
										<i class="glyphicon glyphicon-edit"></i>												
									</a>
									<?php }else{ ?><button class="btn btn-rounded btn-default btn-sm no_permiso disabled" title="Editar" ><i class="glyphicon glyphicon-edit"></i></button><?php } ?>
									<?php if(array_key_exists($this_menu["id"], $usuario_permisos) && in_array("print", $usuario_permisos[$this_menu["id"]])){ ?>
									<a 
										class="hidden btn btn-rounded btn-default btn-sm btn-edit btn-print" 
										title="Imprimir"
										href="./print.php?sec_id=<?php echo $sec_id;?>&amp;item_id=<?php echo $l_v["id"];?>"
										>
										<i class="glyphicon glyphicon-print"></i>												
									</a>
									<?php }else{ ?><button class="hidden btn btn-rounded btn-default btn-sm no_permiso disabled" title="Imprimir" ><i class="glyphicon glyphicon-print"></i></button><?php } ?>
								</td>
	                			<?php
	                		}elseif($key=="estado"){
	                			?><td class="text-center"><?php
	                			if($l_v["estado"]){ 
									?><div class="btn btn-sm btn-default text-success btn-estado"><span class="glyphicon glyphicon-ok-circle"></span></div><?php 
								}else{ 
									?><div class="btn btn-sm btn-default text-warning btn-estado" title="Pendiente"><span class="glyphicon  glyphicon-info-sign"></span></div><?php
								}
								?></td><?php
							}elseif($key=="apellidos"){
		                		?>
		                		<td><?php echo $l_v["apellido_paterno"];?> <?php echo $l_v["apellido_materno"];?></td>
		                		<?php
	                		}elseif($key=="id"){
		                		?>
		                		<td class="text-right "><?php echo $l_v[$key];?></td>
		                		<?php
	                		}else{
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
		<?php
	}
	?>


</div>
