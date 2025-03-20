<?php
?>
<div class="content container-fluid">
    <form class="form-horizontal" method="post" id="add_cliente_form">
		<div class="page-header wide">
			<div class="row">
				<div class="col-xs-4">
					<h1 class="page-title"><i class="icon icon-inline glyphicon glyphicon-user"></i> Registro de nuevo Cliente</h1>
				</div>
				<div class="col-xs-8 text-right">
					<button type="submit" class="add_cliente_btn btn btn-primary">Guardar</button>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-4 ">
				<div class="h4">1. DATOS PERSONALES</div>
	            	<div class="form-group form-group_tipo_cliente">	
						<label class="col-xs-5 control-label" for="select-tipo_cliente">Tipo de Cliente</label>
						<div class="input-group col-xs-7">
							<select 
								required="required" 
								class="form-control input_text" 
								data-col="tipo_cliente" 
								name="tipo_cliente" 
								id="select-tipo_cliente" 
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
						</div>
					</div>

	            	<div class="hidden_form hidden_form_1">
						<div class="form-group form-group_dni">
							<label class="col-xs-5 control-label" for="varchar_dni">DNI</label>
							<div class="input-group col-xs-7">
								<input 
									type="text" 
									class="form-control input_text " 
									data-col="dni" 
									name="dni" 
									id="varchar_dni" 
									value="45540366" 
									placeholder="Ingrese el DNI" 
									title="" 
									data-toggle="tooltip" 
									data-placement="bottom" 
									data-original-title="Ingrese el DNI"
									required="required" 
									>
							</div>
						</div>	
						<div class="form-group form-group_nombre">
							<label class="col-xs-5 control-label" for="varchar_nombre">Nombre</label>
							<div class="input-group col-xs-7">
								<input 
									type="text" 
									class="form-control input_text " 
									data-col="nombre" 
									name="nombre" 
									id="varchar_nombre" 
									value="Manuel Llaguno" 
									placeholder="Ingrese el Nombre" 
									title="" 
									data-toggle="tooltip" 
									data-placement="bottom" 
									data-original-title="Ingrese el Nombre"
									required="required" 
									>
							</div>
						</div>	
	            	</div>
	            	<div class="hidden_form hidden_form_2">
						<div class="form-group form-group_ruc">
							<label class="col-xs-5 control-label" for="varchar_ruc">RUC</label>
							<div class="input-group col-xs-7">
								<input 
									type="text" 
									class="form-control input_text " 
									data-col="ruc" 
									name="ruc" 
									id="varchar_ruc" 
									value="1548798465132" 
									placeholder="Ingrese el RUC" 
									title="" 
									data-toggle="tooltip" 
									data-placement="bottom" 
									data-original-title="Ingrese el RUC"
									required="required" 
									>
							</div>
						</div>
						<div class="form-group form-group_razon_social">
							<label class="col-xs-5 control-label" for="varchar_razon_social">Razón Social</label>
							<div class="input-group col-xs-7">
								<input 
									type="text" 
									class="form-control input_text " 
									data-col="razon_social"
									name="razon_social" 
									id="varchar_razon_social" 
									value="USS Enterprise" 
									placeholder="Ingrese la Razón Social" 
									title="" 
									data-toggle="tooltip" 
									data-placement="bottom" 
									data-original-title="Ingrese la Razón Social"
									required="required"
									>
							</div>
						</div>	
	            	</div>
	            	<div class="hidden_form hidden_form_all">
						<div class="form-group form-group_email">
							<label class="col-xs-5 control-label" for="varchar_email">Correo</label>
							<div class="input-group col-xs-7">
								<input 
									type="text" 
									class="form-control input_text " 
									data-col="email" 
									name="email" 
									id="varchar_email" 
									value="name@host.domain" 
									placeholder="Ingrese el Correo Electronico" 
									title="" 
									data-toggle="tooltip" 
									data-placement="bottom" 
									data-original-title="Ingrese el Correo Electronico"
									required="required"
									>
							</div>
						</div>	
						<div class="form-group form-group_telefono">
							<label class="col-xs-5 control-label" for="varchar_telefono">Telefono</label>
							<div class="input-group col-xs-7">
								<input 
									type="text" 
									class="form-control input_text " 
									data-col="telefono" 
									name="telefono" 
									id="varchar_telefono" 
									value="7654321" 
									placeholder="Ingrese el Telefono" 
									title="" 
									data-toggle="tooltip" 
									data-placement="bottom" 
									data-original-title="Ingrese el Telefono"
									required="required"
									>
							</div>
						</div>	
						<div class="form-group form-group_celular">
							<label class="col-xs-5 control-label" for="varchar_celular">Celular</label>
							<div class="input-group col-xs-7">
								<input 
									type="text" 
									class="form-control input_text " 
									data-col="celular" 
									name="celular" 
									id="varchar_celular" 
									value="987654321" 
									placeholder="Ingrese el Celular" 
									title="" 
									data-toggle="tooltip" 
									data-placement="bottom" 
									data-original-title="Ingrese el Celular"
									required="required"
									>
							</div>
						</div>	
						<div class="form-group form-group_direccion">
							<label class="col-xs-5 control-label" for="varchar_direccion">¿está en INFOCORP?</label>
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
			</div>

			<div class="col-xs-4 hidden_form hidden_form_all">
				<div class="h4">2. ACERCA DEL LOCAL</div>
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
						<input 
							type="text" 
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
							>
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

			<div class="col-xs-4 hidden_form hidden_form_all">
				<div class="h4">3. ACERCA DE APUESTA TOTAL</div>

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
    </form>
</div>