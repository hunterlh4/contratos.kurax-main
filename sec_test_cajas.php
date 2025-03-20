
<div class="content container-fluid">
	<div class="page-header wide">
		<div class="row">
			<div class="col-xs-12 text-center">
				<div class="page-title"><i class="glyphicon glyphicon-piggy-bank"></i> Administrador de Cajas</div>
			</div>
		</div>
		<div class="row">
			<div class="col-xs-12">
			</div>
		</div>
	</div>	

	<div class="row">
		<div class="col-lg-12 col-md-12">

			  <!-- Nav tabs -->
			  <ul class="nav nav-tabs" role="tablist">
			    <li role="presentation" class="active"><a href="#estructura" aria-controls="estructura" role="tab" data-toggle="tab">Estructura</a></li>
			    <li role="presentation"><a href="#plantilla" aria-controls="plantilla" role="tab" data-toggle="tab">Plantilla</a></li>
			  </ul>

			  <!-- Tab panes -->
				<div class="tab-content">
					<div role="tabpanel" class="tab-pane active" id="estructura">
						<!-- <div class="col-lg-3 col-md-3"></div> -->
						<div class="col-lg-6 col-md-6 col-lg-offset-3 col-md-offset-3">
							<div class="panel" id="datos_formulario_generico_admin_caja">
							    <div class="panel-heading">
							        <div class="panel-title"><i class="icon fa fa-file-text-o muted"></i> Formulario Generico</div>
							    </div>
							    <div id="panel-" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="panel-collapse-1-heading">
							        <div class="panel-body locales_form_local">
										<div class="form-group">	
											<label class="col-sm-4 control-label" for="select_tipo_admin_caja" id="label_select_tipo_admin_caja">Cuadro</label>
											<div class="col-sm-8">
												<select 												 
													class="form-control input_text" 
													data-col="tipo"
													name="tipo_admin_caja" 
													id="select_tipo_cuadro_admin_caja" >
												</select>
											</div>
										</div>
										<div class="form-group">	
											<label class="col-sm-4 control-label" for="select_tipo_fila_columna_admin_caja" id="label_select_tipo_fila_columna_admin_caja">Tipo</label>
											<div class=" col-sm-8">
												<select 												 
													class="form-control input_text" 
													data-col="tipo_fila_columna"
													name="tipo_fila_columna_admin_caja" 
													id="select_tipo_fila_columna_admin_caja" >
													<option value="-1">Seleccione</option>
													<option value="0">Columna</option>
													<option value="1">Fila</option>
												</select>
											</div>
										</div>	
										<div class="form-group ">
											<button type="button" class="btn_consultar_cuadro_tipo_caja_admin btn btn-info" data-button="request" data-toggle="tooltip" data-placement="top" title="Consultar Cuadro tipo"><span class="glyphicon glyphicon-send"></span> Consultar</button>								
										</div>																			
										<div class="form-group ">	
											<div class="input-group col-xs-12">
												<div class="tabla_contenedor_tabla_caja_admin">
												</div>
											</div>
										</div>
							        </div>
							    </div>
							</div>
						</div>
						<div class="col-lg-3 col-md-3"></div>
					</div>
					<div role="tabpanel" class="tab-pane" id="plantilla">
					</div>
				</div>

		</div>	
	</div>	
</div>	


<!-- MODAL EDITAR COLUMNA-->
<div class="modal fade bs-example-modal-sm" id="modal_editar_columna_caja" role="dialog" aria-labelledby="mySmallModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">EDITAR COLUMNA</h4>
      </div>
      <div class="modal-body">
			<input id="id_columna_admin_caja" class="form-control" type="hidden"></input>		

			<div class="form-group">	
				<label class="col-xs-4 control-label" for="posicion_columna_admin_caja" 
				id="label_posicion_columna_admin_caja">Posición</label>
				<div class="input-group col-xs-8">
					<input class="form-control input_text" id="posicion_columna_admin_caja" type="text">
					</input>
				</div>
			</div>	
			<div class="form-group">	
				<label class="col-xs-4 control-label" for="descripcion_columna_admin_caja" 
				id="label_descripcion_columna_admin_caja">Descripción</label>
				<div class="input-group col-xs-8">
					<input class="form-control input_text" id="descripcion_columna_admin_caja" type="text">
					</input>
				</div>
			</div>
			<div class="form-group">	
				<label class="col-xs-4 control-label" for="estado_columna_admin_caja" 
				id="label_estado_columna_admin_caja">Estado</label>
				<div class="input-group col-xs-8">
					<select class="form-control input_text" id="estado_columna_admin_caja">
						<option value="1">Activo</option>
						<option value="0">Inactivo</option>						
					</select>
				</div>
			</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="btn_table_td_caja_guardar_edit_columna_row"><span class="glyphicon glyphicon-floppy-saved"></span></button>
      </div>
    </div>
  </div>
</div>	


<!-- MODAL EDITAR FILA-->
<div class="modal fade bs-example-modal-sm" id="modal_editar_fila_caja" role="dialog" aria-labelledby="mySmallModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">EDITAR FILA</h4>
      </div>
      <div class="modal-body">
			<input id="id_fila_admin_caja" class="form-control" type="hidden"></input>

			<div class="form-group">	
				<label class="col-xs-4 control-label" for="posicion_fila_admin_caja" 
				id="label_posicion_fila_admin_caja">Posición</label>
				<div class="input-group col-xs-8">
					<input class="form-control input_text" id="posicion_fila_admin_caja" type="text">
					</input>
				</div>
			</div>	
			<div class="form-group">	
				<label class="col-xs-4 control-label" for="descripcion_fila_admin_caja" 
				id="label_descripcion_fila_admin_caja">Descripción</label>
				<div class="input-group col-xs-8">
					<input class="form-control input_text" id="descripcion_fila_admin_caja" type="text">
					</input>
				</div>
			</div>
			<div class="form-group">	
				<label class="col-xs-4 control-label" for="relacion_fila_admin_caja" 
				id="label_relacion_fila_admin_caja">Relación</label>
				<div class="input-group col-xs-8">
						<input class="form-control input_text" id="relacion_fila_admin_caja" type="text" readonly>
						</input> 
				</div>
			</div>
			<div class="form-group">	
				<label class="col-xs-4 control-label" for="id_relacion_fila_admin_caja" 
				id="label_id_relacion_fila_admin_caja">Id Relación</label>
				<div class="input-group col-xs-8">
					<input class="form-control input_text" id="id_relacion_fila_admin_caja" type="text" readonly>
					</input>
				</div>
			</div>
			<div class="form-group">	
				<label class="col-xs-4 control-label" for="estado_fila_admin_caja" 
				id="label_estado_fila_admin_caja">Estado</label>
				<div class="input-group col-xs-8">
					<select class="form-control input_text" id="estado_fila_admin_caja">
						<option value="1">Activo</option>
						<option value="0">Inactivo</option>						
					</select>					
				</div>
			</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="btn_table_td_caja_guardar_edit_fila_row" ><span class="glyphicon glyphicon-floppy-saved"></span></button>
      </div>
    </div>
  </div>
</div>	



