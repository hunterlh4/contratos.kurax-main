<div class="content container-fluid">
	<div class="page-header wide">
		<div class="row">
			<div class="col-xs-4">
				<?php
				if($item_id){
					?>
					<a class="btn btn-default" href="./?sec_id=<?php echo $sec_id;?>">
        				<i class="glyphicon glyphicon-arrow-left"></i>
        				Regresar
        			</a>
	                <button type="submit" form="contrato_add_form" class="contrato_save_btn btn btn-primary">
	                	<i class="glyphicon glyphicon-floppy-save"></i>
	                	Guardar
	                </button>
					<?php
				}else{
					?>
					<a 
						href="./?sec_id=<?php echo $sec_id;?>&amp;item_id=new"
						id="" 
						data-sec="<?php echo $sec_id;?>" 
						data-sub-sec="<?php echo $sub_sec_id;?>" 
						data-table="<?php echo $m["tabla"];?>"
						class="btn btn-rounded btn-min-width btn-primary btn-add contrato_add_btn">Agregar</a>

					<?php
				}
				?>
			</div>
			<div class="col-xs-4 text-center">
				<h1 class="page-title"><i class="icon icon-inline glyphicon glyphicon-user"></i> Contratos</h1>
			</div>
			<div class="col-xs-4 text-right">
				<?php if($item_id){ echo "ID:".$item_id; }?>
			</div>
		</div>
	</div>
	<?php
	if($item_id){
		?>
		<form class="form-horizontal" id="contrato_add_form">

			<div class="col-xs-12">

				<!-- Nav tabs -->
				<ul class="nav nav-tabs" role="tablist" id="contrato_tabs">
					<li role="presentation" class=""><a href="#tab_contrato" aria-controls="tab_contrato" role="tab" data-toggle="tab">Datos del Contrato</a></li>
					<li role="presentation" class=""><a href="#tab_cliente" aria-controls="tab_cliente" role="tab" data-toggle="tab">Datos del Cliente</a></li>
					<li role="presentation" class=""><a href="#tabl_local" aria-controls="tabl_local" role="tab" data-toggle="tab">Datos del Local</a></li>
					<li role="presentation" class=""><a href="#tabl_observacion" aria-controls="tabl_observacion" role="tab" data-toggle="tab">Observaciones</a></li>
				</ul>

				<!-- Tab panes -->
				<br>
				<div class="tab-content">
					<div role="tabpanel" class="tab-pane" id="tab_contrato">
						<div class="col-xs-4 ">
							<div class="h4">Datos de Contrato</div>
							<div class="form-group form-group_dni">
								<label class="col-xs-5 control-label" for="input_text_id">Contrato ID</label>
								<div class="input-group col-xs-7">
									<input 
										type="text" 
										class="form-control input_text " 
										data-col="id" 
										name="id" 
										data-table="tbl_contratos"
										id="input_text_id" 
										value="new" 
										placeholder="ID del contrato" 
										readonly="readonly" 
										>
									<label class="input-group-addon glyphicon glyphicon-info-sign" ></label>
								</div>
							</div>
							<div class="form-group form-group_dni">
								<label class="col-xs-5 control-label" for="input_text_fecha_registro">Fecha de registro</label>
								<div class="input-group col-xs-7">
									<input 
										type="text" 
										class="form-control input_text datepicker" 
										data-col="id" 
										name="id" 
										id="input_text_fecha_registro" 
										value="<?php echo date("Y-m-d");?>" 
										readonly="readonly" 
										>
									<label class="input-group-addon glyphicon glyphicon-calendar" for="input_text_fecha_registro"></label>
								</div>
							</div>
							<div class="form-group form-group_dni">
								<label class="col-xs-5 control-label" for="input_text-canal_de_venta">Canal de venta</label>
								<div class="input-group col-xs-7">
									<input 
										type="text" 
										class="form-control input_text " 
										data-col="canal_de_venta" 
										name="canal_de_venta" 
										data-table="tbl_contratos"
										id="input_text-canal_de_venta" 
										placeholder="Canal de venta" 
										>
									<label class="input-group-addon glyphicon glyphicon-pencil" for="input_text-canal_de_venta" ></label>
								</div>
							</div>
							<div class="form-group form-group_tipo_cliente">	
								<label class="col-xs-5 control-label" for="select-tipo_contrato_id">Tipo de Contrato</label>
								<div class="input-group col-xs-7">
									<select 
										required="required" 
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
											?><option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"]; ?></option><?php
										}
										?>
									</select>
								</div>
							</div>
							<div class="h4">Tiempo de Contrato</div>
							<div class="form-group form-group-fecha_inicio_contrato">
								<label class="col-xs-5 control-label" for="input_text-fecha_inicio_contrato">Fecha de inicio</label>
								<div class="input-group col-xs-7">
									<input 
										type="text" 
										class="form-control input_text datepicker" 
										data-col="fecha_inicio_contrato" 
										name="fecha_inicio_contrato" 
										id="input_text-fecha_inicio_contrato" 
										value="" 
										readonly="readonly" 
										required="required" 
										>
									<label class="input-group-addon glyphicon glyphicon-calendar" for="input_text-fecha_inicio_contrato"></label>
								</div>
							</div>
							<div class="form-group form-group-fecha_fin_contrato">
								<label class="col-xs-5 control-label" for="input_text-fecha_fin_contrato">Fecha de Fin</label>
								<div class="input-group col-xs-7">
									<input 
										type="text" 
										class="form-control input_text datepicker" 
										data-col="fecha_fin_contrato" 
										name="fecha_fin_contrato" 
										id="input_text-fecha_fin_contrato" 
										value="" 
										readonly="readonly" 
										required="required" 
										>
									<label class="input-group-addon glyphicon glyphicon-calendar" for="input_text-fecha_fin_contrato"></label>
								</div>
							</div>
							<div class="form-group form-group-dni">
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
										>
									<label class="input-group-addon glyphicon glyphicon-pencil" for="input_text-tiempo_de_contrato" ></label>
								</div>
							</div>
						</div>
						<div class="col-xs-4">
							<div class="h4">Productos/Servicios</div>
							<div class="form-group form-group-juegos_virtuales">
								<label class="col-xs-7 control-label" for="input_text-juegos_virtuales">Juegos Virtuales</label>
								<div class="input-group col-xs-5">
									<div class="radio-inline">
		                                <label>
											<input type="radio" name="juegos_virtuales" value="1" required="required" > Si
		                                </label>
		                            </div>
									<div class="radio-inline">
		                                <label>
											<input type="radio" name="juegos_virtuales" value="0" required="required" > No
		                                </label>
									</div>
								</div>
							</div>
							<div class="form-group form-group-apuestas_deportivas">
								<label class="col-xs-7 control-label" for="input_text-apuestas_deportivas">Apuestas Deportivas</label>
								<div class="input-group col-xs-5">
									<div class="radio-inline">
		                                <label>
											<input type="radio" name="apuestas_deportivas" value="1" required="required" > Si
		                                </label>
		                            </div>
									<div class="radio-inline">
		                                <label>
											<input type="radio" name="apuestas_deportivas" value="0" required="required" > No
		                                </label>
									</div>
								</div>
							</div>
							<div class="form-group form-group-terminales">
								<label class="col-xs-7 control-label" for="input_text-terminales">Terminales</label>
								<div class="input-group col-xs-5">
									<div class="radio-inline">
		                                <label>
											<input type="radio" name="terminales" value="1" required="required" > Si
		                                </label>
		                            </div>
									<div class="radio-inline">
		                                <label>
											<input type="radio" name="terminales" value="0" required="required" > No
		                                </label>
									</div>
								</div>
							</div>
							<div class="form-group form-group-recargas_web">
								<label class="col-xs-7 control-label" for="input_text-recargas_web">Recargas Web</label>
								<div class="input-group col-xs-5">
									<div class="radio-inline">
		                                <label>
											<input type="radio" name="recargas_web" value="1" required="required" > Si
		                                </label>
		                            </div>
									<div class="radio-inline">
		                                <label>
											<input type="radio" name="recargas_web" value="0" required="required" > No
		                                </label>
									</div>
								</div>
							</div>
						</div>
						<div class="col-xs-4">
							<div class="h4">Facturacion</div>
							<div class="form-group form-group-amortizacion_semanal">
								<label class="col-xs-7 control-label" for="input_text-amortizacion_semanal">Amortización Semanal</label>
								<div class="input-group col-xs-5">
									<div class="radio-inline">
		                                <label>
											<input type="radio" name="amortizacion_semanal" value="1" required="required" > Si
		                                </label>
		                            </div>
									<div class="radio-inline">
		                                <label>
											<input type="radio" name="amortizacion_semanal" value="0" required="required" > No
		                                </label>
									</div>
								</div>
							</div>
							<div class="form-group form-group-incluye_igv">
								<label class="col-xs-7 control-label" for="input_text-incluye_igv">Pago Incluye IGV</label>
								<div class="input-group col-xs-5">
									<div class="radio-inline">
		                                <label>
											<input type="radio" name="incluye_igv" value="1" required="required" > Si
		                                </label>
		                            </div>
									<div class="radio-inline">
		                                <label>
											<input type="radio" name="incluye_igv" value="0" required="required" > No
		                                </label>
									</div>
								</div>
							</div>
							<div class="form-group form-group-pagador">	
								<label class="col-xs-5 control-label" for="select-pagador">Pagador</label>
								<div class="input-group col-xs-7">
									<select 
										required="required" 
										class="form-control input_text" 
										data-col="pagador" 
										data-table="tbl_contratos"
										name="pagador" 
										id="select-pagador" 
										data-toggle="tooltip" 
										title="Seleccione el tipo de Contrato">
										<option value="">- Seleccione -</option>
										<option value="1">Operador Principal</option>
										<option value="0">Agente</option>
									</select>
								</div>
							</div>
							<div class="form-group form-group-periodo_liquidacion_id">	
								<label class="col-xs-5 control-label" for="select-periodo_liquidacion_id">Periodo Liquidacion</label>
								<div class="input-group col-xs-7">
									<select 
										required="required" 
										class="form-control input_text" 
										data-col="periodo_liquidacion_id" 
										data-table="tbl_contratos"
										name="periodo_liquidacion_id" 
										id="select-periodo_liquidacion_id" 
										data-toggle="tooltip" 
										title="Seleccione el tipo de Contrato">
										<option value="">- Seleccione -</option>
										<?php
										$sel_query = $mysqli->query("SELECT id,nombre FROM tbl_liquidacion_modalidad WHERE estado = '1' ORDER BY dias_proceso ASC");
										while($sel=$sel_query->fetch_assoc()){
											?><option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"]; ?></option><?php
										}
										?>
									</select>
								</div>
							</div>
							<div class="form-group form-group-red_tipo_id">	
								<label class="col-xs-5 control-label" for="select-red_tipo_id">Tipo Red</label>
								<div class="input-group col-xs-7">
									<select 
										required="required" 
										class="form-control input_text" 
										data-col="red_tipo_id" 
										data-table="tbl_contratos"
										name="red_tipo_id" 
										id="select-red_tipo_id" 
										data-toggle="tooltip" 
										title="Seleccione el tipo de Contrato">
										<option value="">- Seleccione -</option>
										<?php
										$sel_query = $mysqli->query("SELECT id,nombre FROM tbl_contrato_tipos_de_red WHERE estado = '1' ORDER BY nombre ASC");
										while($sel=$sel_query->fetch_assoc()){
											?><option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"]; ?></option><?php
										}
										?>
									</select>
								</div>
							</div>
							<div class="form-group form-group-documento_tipo_id">	
								<label class="col-xs-5 control-label" for="select-documento_tipo_id">Tipo de Documento</label>
								<div class="input-group col-xs-7">
									<select 
										required="required" 
										class="form-control input_text" 
										data-col="documento_tipo_id" 
										data-table="tbl_contratos"
										name="documento_tipo_id" 
										id="select-documento_tipo_id" 
										data-toggle="tooltip" 
										title="Seleccione el tipo de Contrato">
										<option value="">- Seleccione -</option>
										<?php
										$sel_query = $mysqli->query("SELECT id,nombre FROM tbl_facturacion_documento_tipos WHERE estado = '1' ORDER BY nombre ASC");
										while($sel=$sel_query->fetch_assoc()){
											?><option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"]; ?></option><?php
										}
										?>
									</select>
								</div>
							</div>
							<div class="form-group form-group-moneda_id">	
								<label class="col-xs-5 control-label" for="select-moneda_id">Moneda</label>
								<div class="input-group col-xs-7">
									<select 
										required="required" 
										class="form-control input_text" 
										data-col="moneda_id" 
										data-table="tbl_contratos"
										name="moneda_id" 
										id="select-moneda_id" 
										data-toggle="tooltip" 
										title="Seleccione el tipo de Contrato">
										<option value="">- Seleccione -</option>
										<?php
										$sel_query = $mysqli->query("SELECT id,nombre,simbolo,codigo FROM tbl_moneda WHERE estado = '1' ORDER BY nombre ASC");
										while($sel=$sel_query->fetch_assoc()){
											?><option value="<?php echo $sel["id"];?>"><?php echo $sel["codigo"]; ?> - <?php echo $sel["nombre"]; ?> (<?php echo $sel["simbolo"]; ?>)</option><?php
										}
										?>
									</select>
								</div>
							</div>

							<hr>


							<div class="form-group form-group-limite_pago_id">	
								<label class="col-xs-5 control-label" for="select-limite_pago_id">Limite Pagos</label>
								<div class="input-group col-xs-7">
									<select 
										required="required" 
										class="form-control input_text" 
										data-col="limite_pago_id" 
										data-table="tbl_contratos"
										name="limite_pago_id" 
										id="select-limite_pago_id" 
										data-toggle="tooltip" 
										title="Seleccione el tipo de Contrato">
										<option value="">- Seleccione -</option>
										<?php
										$sel_query = $mysqli->query("SELECT id,nombre FROM tbl_contrato_pago_premios_limite WHERE estado = '1' ORDER BY nombre ASC");
										while($sel=$sel_query->fetch_assoc()){
											?><option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"]; ?></option><?php
										}
										?>
									</select>
								</div>
							</div>
							<div class="form-group form-group-limite_monto">
								<label class="col-xs-5 control-label" for="input_text-limite_monto">Monto Limite</label>
								<div class="input-group col-xs-7">
									<input 
										type="text" 
										class="form-control input_text " 
										data-col="limite_monto" 
										data-table="tbl_clientes"
										name="limite_monto" 
										id="input_text-limite_monto" 
										value="" 
										placeholder="Ingrese el monto" 
										title="" 
										>
									<label class="input-group-addon glyphicon glyphicon-pencil" for="input_text-limite_monto" ></label>
								</div>
							</div>
							<div class="form-group form-group-negativo_devolucion_tipo_id">	
								<label class="col-xs-5 control-label" for="select-negativo_devolucion_tipo_id">Devolucion Negativa</label>
								<div class="input-group col-xs-7">
									<select 
										required="required" 
										class="form-control input_text" 
										data-col="negativo_devolucion_tipo_id" 
										data-table="tbl_contratos"
										name="negativo_devolucion_tipo_id" 
										id="select-negativo_devolucion_tipo_id" 
										title="Seleccione el tipo de Contrato">
										<option value="">- Seleccione -</option>
										<?php
										$sel_query = $mysqli->query("SELECT id,nombre FROM tbl_contrato_negativo_devolucion_tipos WHERE estado = '1' ORDER BY nombre ASC");
										while($sel=$sel_query->fetch_assoc()){
											?><option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"]; ?></option><?php
										}
										?>
									</select>
								</div>
							</div>

							<div class="form-group form-group-corte_no_pago">	
								<label class="col-xs-5 control-label" for="select-corte_no_pago">Corte No Pago</label>
								<div class="input-group col-xs-7">
									<select 
										class="form-control input_text" 
										data-col="corte_no_pago" 
										data-table="tbl_contratos"
										name="corte_no_pago" 
										id="select-corte_no_pago" 
										title="Seleccione el tipo de Corte No Pago">
										<option value="">- Seleccione -</option>
										<option value="1">Si</option>
										<option value="0">No</option>
										<option value="2">Consultar</option>
									</select>
								</div>
							</div>
						</div>
					</div>
					<div role="tabpanel" class="tab-pane" id="tab_cliente">
						<div class="row">
							<div class="form-group form-group_tipo_cliente">	
								<label class="col-xs-5 control-label" for="select-tipo_cliente_id">Tipo de Cliente :D</label>
								<div class="input-group col-xs-7">
									<select 
										required="required" 
										class="form-control input_text" 
										data-col="tipo_cliente_id" 
										data-table="tbl_clientes"
										name="tipo_cliente_id" 
										id="select-tipo_cliente_id" 
										data-toggle="tooltip" 
										title="Seleccione el tipo de cliente">
										<option value="">- Seleccione -</option>
										<?php
										$sel_query = $mysqli->query("SELECT id,nombre FROM tbl_cliente_tipos WHERE estado = '1' ORDER BY nombre ASC");
										while($sel=$sel_query->fetch_assoc()){
											?><option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"]; ?></option><?php
										}
										?>
									</select>
									<label 
										class="input-group-addon glyphicon glyphicon-new-window select_add_dialog_btn"
										data-name="Tipo de Cliente"
										data-table="tbl_cliente_tipos"
										data-cols="nombre"
										data-select="select-tipo_cliente_id"
										></label>
								</div>
							</div>

							<div class="hidden_form hidden_form_1">
								<div class="form-group form-group-dni">
									<label class="col-xs-5 control-label" for="input_text-dni">DNI</label>
									<div class="input-group col-xs-7">
										<input 
											type="text" 
											class="form-control input_text " 
											data-col="dni" 
											data-table="tbl_clientes"
											name="dni" 
											id="input_text-dni" 
											value="45540366" 
											placeholder="Ingrese el DNI" 
											title="" 
											data-toggle="tooltip" 
											data-placement="bottom" 
											data-original-title="Ingrese el DNI"
											required="required" 
											>
										<label class="input-group-addon glyphicon glyphicon-pencil" for="input_text-dni" ></label>
									</div>
								</div>	
								<div class="form-group form-group-nombre">
									<label class="col-xs-5 control-label" for="input_text-nombre">Nombre</label>
									<div class="input-group col-xs-7">
										<input 
											type="text" 
											class="form-control input_text " 
											data-col="nombre" 
											data-table="tbl_clientes"
											name="nombre" 
											id="input_text-nombre" 
											value="Manuel Llaguno" 
											placeholder="Ingrese el Nombre" 
											title="" 
											data-toggle="tooltip" 
											data-placement="bottom" 
											data-original-title="Ingrese el Nombre"
											required="required" 
											>
										<label class="input-group-addon glyphicon glyphicon-pencil" for="input_text-nombre" ></label>
									</div>
								</div>	
			            	</div>
			            	<div class="hidden_form hidden_form_2">
								<div class="form-group form-group-ruc">
									<label class="col-xs-5 control-label" for="input_text-ruc">RUC</label>
									<div class="input-group col-xs-7">
										<input 
											type="text" 
											class="form-control input_text " 
											data-col="ruc" 
											name="ruc" 
											id="input_text-ruc" 
											value="1548798465132" 
											placeholder="Ingrese el RUC" 
											title="" 
											data-toggle="tooltip" 
											data-placement="bottom" 
											data-original-title="Ingrese el RUC"
											required="required" 
											>
										<label class="input-group-addon glyphicon glyphicon-pencil" for="input_text-ruc" ></label>
									</div>
								</div>
								<div class="form-group form-group-razon_social">
									<label class="col-xs-5 control-label" for="input_text-razon_social">Razón Social</label>
									<div class="input-group col-xs-7">
										<input 
											type="text" 
											class="form-control input_text " 
											data-col="razon_social"
											name="razon_social" 
											id="input_text-razon_social" 
											value="USS Enterprise" 
											placeholder="Ingrese la Razón Social" 
											title="" 
											data-toggle="tooltip" 
											data-placement="bottom" 
											data-original-title="Ingrese la Razón Social"
											required="required"
											>
										<label class="input-group-addon glyphicon glyphicon-pencil" for="input_text-razon_social" ></label>
									</div>
								</div>	
			            	</div>

			            	<div class="form-group form-group-email">
								<label class="col-xs-5 control-label" for="input_text-email">Correo</label>
								<div class="input-group col-xs-7">
									<input 
										type="text" 
										class="form-control input_text " 
										data-col="email" 
										name="email" 
										id="input_text-email" 
										value="name@host.domain" 
										placeholder="Ingrese el Correo Electronico" 
										title="" 
										data-toggle="tooltip" 
										data-placement="bottom" 
										data-original-title="Ingrese el Correo Electronico"
										required="required"
										>
									<label class="input-group-addon glyphicon glyphicon-pencil" for="input_text-email" ></label>
								</div>
							</div>	
							<div class="form-group form-group-telefono">
								<label class="col-xs-5 control-label" for="input_text-telefono">Telefono</label>
								<div class="input-group col-xs-7">
									<input 
										type="text" 
										class="form-control input_text " 
										data-col="telefono" 
										name="telefono" 
										id="input_text-telefono" 
										value="7654321" 
										placeholder="Ingrese el Telefono" 
										title="" 
										data-toggle="tooltip" 
										data-placement="bottom" 
										data-original-title="Ingrese el Telefono"
										required="required"
										>
									<label class="input-group-addon glyphicon glyphicon-pencil" for="input_text-telefono" ></label>
								</div>
							</div>	
							<div class="form-group form-group-celular">
								<label class="col-xs-5 control-label" for="input_text-celular">Celular</label>
								<div class="input-group col-xs-7">
									<input 
										type="text" 
										class="form-control input_text " 
										data-col="celular" 
										name="celular" 
										id="input_text-celular" 
										value="987654321" 
										placeholder="Ingrese el Celular" 
										title="" 
										data-toggle="tooltip" 
										data-placement="bottom" 
										data-original-title="Ingrese el Celular"
										required="required"
										>
									<label class="input-group-addon glyphicon glyphicon-pencil" for="input_text-celular" ></label>
								</div>
							</div>
							<div class="form-group form-group_direccion">
								<label class="col-xs-5 control-label" for="input_text-infocorp">¿está en INFOCORP?</label>
								<div class="input-group col-xs-7">
									<div class="radio">
		                                <label>
											<input type="radio" name="infocorp" value="0" required="required" > No, nunca
		                                </label>
									</div>
									<div class="radio">
		                                <label>
											<input type="radio" name="infocorp" value="1" required="required" > Si, estoy en INFOCORP
		                                </label>
		                            </div>
									<div class="radio">
		                                <label>
											<input type="radio" name="infocorp" value="2" required="required" checked="checked" > No, pero SI estuve en el pasado
		                                </label>
		                            </div>
								</div>
							</div>	
						</div>
						<div class="row">
							<div class="form-group form-group-banco_id">	
								<label class="col-xs-5 control-label" for="select-banco_id">Banco</label>
								<div class="input-group col-xs-7">
									<select 
										required="required" 
										class="form-control input_text" 
										data-col="banco_id" 
										data-table="tbl_locales"
										name="banco_id" 
										id="select-banco_id" 
										title="Seleccione el tipo de cliente">
										<option value="">- Seleccione -</option>
										<?php
										$sel_query = $mysqli->query("SELECT id,nombre FROM tbl_bancos WHERE estado = '1' ORDER BY nombre ASC");
										while($sel=$sel_query->fetch_assoc()){
											?><option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"]; ?></option><?php
										}
										?>
									</select>
									<label 
										class="input-group-addon glyphicon glyphicon-new-window select_add_dialog_btn"
										data-name="Banco"
										data-table="tbl_bancos"
										data-cols="nombre"
										data-select="select-banco_id"
										></label>
								</div>
							</div>
							<div class="form-group form-group-numero_cuenta">
								<label class="col-xs-5 control-label" for="input_text-numero_cuenta">Cuenta</label>
								<div class="input-group col-xs-7">
									<input 
										type="text" 
										class="form-control input_text " 
										data-col="numero_cuenta" 
										data-table="tbl_cliente_bancos"
										name="numero_cuenta" 
										id="input_text-numero_cuenta" 
										value="" 
										placeholder="Ingrese el numero de cuenta" 
										title="" 
										required="required" 
										>
									<label class="input-group-addon glyphicon glyphicon-pencil" for="input_text-numero_cuenta" ></label>
								</div>
							</div>	
							<div class="form-group form-group-moneda_id">	
								<label class="col-xs-5 control-label" for="select-moneda_id">Moneda</label>
								<div class="input-group col-xs-7">
									<select 
										required="required" 
										class="form-control input_text" 
										data-col="moneda_id" 
										data-table="tbl_contratos"
										name="moneda_id" 
										id="select-moneda_id" 
										data-toggle="tooltip" 
										title="Seleccione el tipo de Contrato">
										<option value="">- Seleccione -</option>
										<?php
										$sel_query = $mysqli->query("SELECT id,nombre,simbolo,codigo FROM tbl_moneda WHERE estado = '1' ORDER BY nombre ASC");
										while($sel=$sel_query->fetch_assoc()){
											?><option value="<?php echo $sel["id"];?>"><?php echo $sel["codigo"]; ?> - <?php echo $sel["nombre"]; ?> (<?php echo $sel["simbolo"]; ?>)</option><?php
										}
										?>
									</select>
								</div>
							</div>
						</div>						
					</div>
					<div role="tabpanel" class="tab-pane" id="tabl_local">
						<div class="col-xs-4">
							<div class="form-group form-local_tipo_id">	
								<label class="col-xs-5 control-label" for="select-local_tipo_id">Tipo de Local</label>
								<div class="input-group col-xs-7">
									<select 
										class="form-control input_text" 
										data-col="local_tipo_id" 
										name="tipo_id" 
										id="select-local_tipo_id" 
										data-toggle="tooltip" 
										title="Seleccione el tipo de local"
										required="required">
										<option value="">- Seleccione -</option>
										<?php
										$sel_query = $mysqli->query("SELECT id,nombre FROM tbl_local_tipo WHERE estado = '1' ORDER BY nombre ASC");
										while($sel=$sel_query->fetch_assoc()){
											?><option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"]; ?></option><?php
										}
										?>
									</select>
								</div>
							</div>
							<div class="form-group form-estado_legal_id">	
								<label class="col-xs-5 control-label" for="select-estado_legal_id">Estado Legal</label>
								<div class="input-group col-xs-7">
									<select 
										class="form-control input_text" 
										data-col="estado_legal_id" 
										name="estado_legal_id" 
										id="select-estado_legal_id" 
										data-toggle="tooltip" 
										title="Seleccione el tipo de local"
										required="required"
										>
										<option value="">- Seleccione -</option>
										<?php
										$sel_query = $mysqli->query("SELECT id,nombre FROM tbl_local_estado_legal WHERE estado = '1' ORDER BY nombre ASC");
										while($sel=$sel_query->fetch_assoc()){
											?><option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"]; ?></option><?php
										}
										?>
									</select>
								</div>
							</div>
							<div class="form-group form-group-local_nombre">
								<label class="col-xs-5 control-label" for="input_text-local_nombre">Nombre del local</label>
								<div class="input-group col-xs-7">
									<input 
										type="text" 
										class="form-control input_text " 
										data-col="local_nombre" 
										name="local_nombre" 
										id="input_text-local_nombre" 
										value="" 
										placeholder="Ingrese el Nombre del local" 
										title="" 
										required="required"
										>
									<label class="input-group-addon glyphicon glyphicon-pencil" for="input_text-local_nombre" ></label>
								</div>
							</div>
							<div class="form-group form-group_local_area">
								<label class="col-xs-5 control-label" for="varchar_local_area">Area del local</label>
								<div class="input-group col-xs-7">
									<input 
										type="text" 
										class="form-control input_text " 
										data-col="local_area" 
										name="area"
										id="varchar_local_area" 
										value="100.5" 
										placeholder="Ingrese el Area del local" 
										title="Ingrese el Area del local" 
										data-toggle="tooltip" 
										data-placement="bottom" 
										required="required"
										>
									<div class="input-group-addon" title="Metros Cuadrados">m2</div>
								</div>
							</div>
							<div class="form-group form-group_direccion">
								<label class="col-xs-5 control-label" for="varchar_direccion">Dirección</label>
								<div class="input-group col-xs-7">
									<textarea
										class="form-control input_text " 
										data-col="direccion" 
										name="direccion" 
										id="varchar_direccion" 
										value="direccion on on on" 
										placeholder="Ingrese la Dirección" 
										title="" 
										data-toggle="tooltip" 
										data-placement="bottom" 
										data-original-title="Ingrese la Dirección"
										required="required"
										></textarea>
								</div>
							</div>
							<div class="form-group form-ubigeo_departamento">	
								<label class="col-xs-5 control-label" for="select-ubigeo_departamento">Departamento</label>
								<div class="input-group col-xs-7">
									<select 
										class="form-control input_text" 
										data-col="local_ubigeo_departament" 
										name="ubigeo_departamento" 
										id="select-ubigeo_departamento" 
										data-toggle="tooltip" 
										title="Seleccione el Departamento"
										required="required"
										>
										<option value="">- Seleccione un Departamento -</option>
										<?php
										$sel_query = $mysqli->query("SELECT id,nombre FROM tbl_ubigeo_departamentos WHERE estado = '1' ORDER BY nombre ASC");
										while($sel=$sel_query->fetch_assoc()){
											?><option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"]; ?></option><?php
										}
										?>
									</select>
								</div>
							</div>
							<div class="form-group form-ubigeo_provincia">	
								<label class="col-xs-5 control-label" for="select-ubigeo_provincia">Provincia</label>
								<div class="input-group col-xs-7">
									<select 
										disabled="disabled" 
										class="form-control input_text" 
										data-col="local_ubigeo_provincia" 
										name="ubigeo_provincia" 
										id="select-ubigeo_provincia" 
										data-toggle="tooltip" 
										title="Seleccione la Provincia"
										required="required">
										<option value="">- Seleccione un Departamento -</option>
									</select>
								</div>
							</div>
							<div class="form-group form-ubigeo_distrito">	
								<label class="col-xs-5 control-label" for="select-ubigeo_distrito">Distrito</label>
								<div class="input-group col-xs-7">
									<select 
										disabled="disabled" 
										class="form-control input_text" 
										data-col="local_ubigeo_distrito" 
										name="ubigeo_distrito" 
										id="select-ubigeo_distrito" 
										data-toggle="tooltip" 
										title="Seleccione el Distrito"
										required="required">
										<option value="">- Seleccione un Departamento -</option>
									</select>
								</div>
							</div>

							<div class="form-group form-otra_casa_apuestas">
								<label class="col-xs-5 control-label">¿El local cuenta con otra casa de apuestas?</label>
								<div class="input-group col-xs-7">
									<div class="radio-inline">
			                            <label>
											<input type="radio" name="otra_casa_apuestas" value="1" required="required"> Si
			                            </label>
			                        </div>
									<div class="radio-inline">
			                            <label>
											<input type="radio" name="otra_casa_apuestas" value="0" required="required"> No
			                            </label>
									</div>
									<div class="hidden_form hide_form_otra_casa_apuestas_des">
										<textarea 
											placeholder="Detallar aquí cual es" 
											id="form-otra_casa_apuestas_des" 
											class="form-control" 
											name="otra_casa_apuestas_des" 
											rows="2"></textarea>
									</div>
								</div>
							</div>	

							<div class="form-group form-experiencia_casa_apuestas">
								<label class="col-xs-5 control-label">¿Ha trabajado anteriormente con una casa de apuestas?</label>
								<div class="input-group col-xs-7">
									<div class="radio-inline">
			                            <label>
											<input type="radio" name="experiencia_casa_apuestas" value="1" required="required"> Si
			                            </label>
			                        </div>
									<div class="radio-inline">
			                            <label>
											<input type="radio" name="experiencia_casa_apuestas" value="0" required="required"> No
			                            </label>
									</div>
									<div class="hidden_form hidden_form_experiencia_casa_apuestas_des">
										<textarea 
											placeholder="Detallar cual" 
											id="form-experiencia_casa_apuestas_des" 
											class="form-control" 
											name="experiencia_casa_apuestas_des" 
											rows="2"></textarea>
									</div>
								</div>
							</div>
						</div>
						<div class="col-xs-4">
							<div class="form-group form-group_tipo_cliente">	
								<label class="col-xs-5 control-label" for="select-representante_id">Representante Legal</label>
								<div class="input-group col-xs-7">
									<select 
										required="required" 
										class="form-control input_text" 
										data-col="representante_id" 
										data-table="tbl_locales"
										name="representante_id" 
										id="select-representante_id" 
										data-toggle="tooltip" 
										title="Seleccione el tipo de cliente">
										<option value="">- Seleccione -</option>
										<?php
										$sel_query = $mysqli->query("SELECT id,nombre FROM tbl_representante_legal WHERE estado = '1' ORDER BY nombre ASC");
										while($sel=$sel_query->fetch_assoc()){
											?><option value="<?php echo $sel["id"];?>"><?php echo $sel["nombre"]; ?></option><?php
										}
										?>
									</select>
									<label 
										class="input-group-addon glyphicon glyphicon-new-window select_add_dialog_btn"
										data-name="Representante Legal"
										data-table="tbl_representante_legal"
										data-cols="nombre"
										data-select="select-representante_id"
										></label>
								</div>
							</div>
						</div>
					</div>
					<div role="tabpanel" class="tab-pane" id="tabl_observacion">
						<div class="col-xs-7">
							<div class="form-group form-group-observa_vendedor">
								<label class="col-xs-3 control-label" for="input-text-observa_vendedor">Observaciones Vendedor</label>
								<div class="input-group col-xs-7">
									<textarea
										class="form-control input_text " 
										data-col="observa_vendedor" 
										name="observa_vendedor" 
										id="input-text-observa_vendedor" 
										value="" 
										placeholder="Observaciones" 
										title="" 
										rows="5"
										></textarea>
								</div>
							</div>
							<div class="form-group form-group-observa_comercial">
								<label class="col-xs-3 control-label" for="input-text-observa_comercial">Observaciones Comercial</label>
								<div class="input-group col-xs-7">
									<textarea
										class="form-control input_text " 
										data-col="observa_comercial" 
										name="observa_comercial" 
										id="input-text-observa_comercial" 
										value="" 
										placeholder="Observaciones" 
										title="" 
										rows="5"
										></textarea>
								</div>
							</div>
						</div>
						<div class="col-xs-5">
							<div class="form-group form-group_direccion">
								<label class="col-xs-5 control-label">¿Cómo se enteró de nosotros?</label>
								<div class="input-group col-xs-7">
									<div class="radio">
			                            <label>
											<input type="radio" name="como_se_entero" value="radio" required="required"> Radio
			                            </label>
									</div>
									<div class="radio">
			                            <label>
											<input type="radio" name="como_se_entero" value="redessociales" required="required"> Redes Sociales
			                            </label>
			                        </div>
									<div class="radio">
			                            <label>
											<input type="radio" name="como_se_entero" value="web" required="required"> Web
			                            </label>
			                        </div>
									<div class="radio">
			                            <label>
											<input type="radio" name="como_se_entero" value="amigos" required="required"> Amigos
			                            </label>
			                        </div>
									<div class="radio">
			                            <label>
											<input type="radio" name="como_se_entero" value="otros" required="required"> Otros
			                            </label>
			                        </div>
									<div class="hidden_form hide_form_como_se_entero_des">
										<textarea 
											placeholder="Detallar aquí cual es" 
											id="form-como_se_entero_des" 
											class="form-control" 
											name="como_se_entero_des" 
											rows="2"></textarea>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
		<?php
	}else{

	}
	?>
</div>