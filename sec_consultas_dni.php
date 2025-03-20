<style>
	fieldset.dhhBorder{
		border: 1px solid #ddd;
		padding: 0 15px 5px 15px;
		margin: 0 0 10px 0;
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
	
</style>

<div class="content container-fluid content_consultas">
	<div class="page-header wide">
		<div class="row">
			<div class="col-xs-12 text-center">
				<div class="page-title"><i class="icon icon-inline fa fa-fw fa-id-card"></i> Consultas - <?php echo strtoupper(str_replace("_", " ", $sub_sec_id)); ?> 1.2</div>
			</div>
		</div>
	</div>

	<div class="row" class="mt-5">
		<div class="col-lg-3 col-xs-12" style="pointer-events: none;">
			<fieldset class="dhhBorder">
				<legend class="dhhBorder">Leyenda</legend>
				<button class="btn btn-primary btn-xs">
					Gestión
				</button>
				<button class="btn btn-success btn-xs">
					BetConstruct
				</button>
				<button class="btn btn-danger btn-xs">
					Calimaco
				</button>
			</fieldset>
		</div>
		<div class="col-lg-6 col-xs-12" style="margin-top: 20px;">
			<div class="input-group">
				<input type="text" id="txtDNINumber" name="txtDNINumber" class="form-control auto-focus" placeholder="Buscar Número DNI" style="height:34px !important" maxlength="8">
				<span class="input-group-btn">
                    <button type="button" id="btnDNISearch" class="btn btn-primary" style="margin-right: 10px; margin-left: 10px;"><i class="fa fa-search"></i> Buscar</button>
				</span>
				<span class="input-group-btn">
					<form id="formDNI" method="POST" enctype="multipart/form-data">
						<input type="file" id="fileDNIUpload" name="fileDNIUpload" oninput="this.value=this.value.replace(/\D/g,'')" style="display: none;">
						<button id="btnDNIUpload" class="btn btn-warning"><i class="fa fa-file-o"></i> Archivo</button>
					</form>
				</span>
			</div>
		</div>
	</div>

	<div class="row" class="" style="margin-top: 5px;">
		<!-- <div class="col-lg-offset-0 col-lg-12 col-xs-12"> -->
			<div id="dniContent"></div>
		<!-- </div> -->
	</div>
</div>


<!-- INICIO MODAL HISTORICO CAMBIOS-->
<div id="modalConsultaChangeLog" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                 <h4 class="modal-title" id="modal_title_consulta_historico"></h4>
            </div>

            <div class="modal-body">

			<div class="table-responsive" id="consulta_change_log_div_tabla">
                <table class="table display responsive" style="width:100%" id="consulta_change_log_datatable">
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-center">Valor anterior</th>
                                                        <th class="text-center">Valor nuevo</th>
                                                        <th class="text-center">Nombre campo</th>
                                                        <th class="text-center">Fecha registro</th>
                                                        <th class="text-center">Usuario</th>
                                                    </tr>
                                                 </thead>
                                            <tbody>
                                            </tbody>
                </table>
            </div>
            </div>             
        </div>
	</div>
</div>
<!-- FIN MODAL HISTORICO CAMBIOS -->
<style>
    .campo-obligatorio {
    color: red;
}
</style>
<!--INICIO MODAL - CREAR COMPROBANTE -->

<div class="modal" id="sec_consulta_modal_editar" data-backdrop="static" tabindex="false" style="">
    <div class="modal-dialog modal-md" style="width: 800px;" >
        <div class="modal-content modal-rounded">
            <div class="modal-header">
                <button type="button" class="close pull-right" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="sec_consulta_modal_guardar_titulo">
                </h4>
                <span class="campo-obligatorio">
                    (*) Campos obligatorios
                </span>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <form id="Frm_UsuarioDni" method="POST" enctype="multipart/form-data" autocomplete="off">
                            
                            <input type="hidden" 
                                id="form_modal_sec_consulta_dni_param_id" 
                                name="form_modal_sec_consulta_dni_param_id" 
                                value="0">

                            <input type="hidden" 
                                id="form_modal_sec_consulta_dni_param_dni" 
                                name="form_modal_sec_consulta_dni_param_dni">

							<div class="form-group row" style="margin-bottom: 10px;">
                                <label class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                    Nombre(s):
                                    <span class="campo-obligatorio">(*)</span>
                                </label>
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <input type="text" 
                                    class="form-control campo-editable" 
                                    name="form_modal_sec_consulta_dni_param_nombres"
                                    id="form_modal_sec_consulta_dni_param_nombres"
                                    placeholder="Nombres"
                                    oninput="this.value=this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g,'')"
                                    maxlength="45">
                            	</div>
                        	</div>     
							
							<div class="form-group row" style="margin-bottom: 10px;">
                                <label class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                    Apellido Paterno:
                                    <span class="campo-obligatorio">(*)</span>
                                </label>
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <input type="text" 
                                    class="form-control campo-editable" 
                                    name="form_modal_sec_consulta_dni_param_apellido_paterno"
                                    id="form_modal_sec_consulta_dni_param_apellido_paterno"
                                    placeholder="Apellido Paterno"
                                    oninput="this.value=this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g,'')"
                                    maxlength="45">
                            	</div>
                        	</div>    
							<div class="form-group row" style="margin-bottom: 10px;">
                                <label class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                    Apellido Materno:
                                    <span class="campo-obligatorio">(*)</span>
                                </label>
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <input type="text" 
                                    class="form-control campo-editable" 
                                    name="form_modal_sec_consulta_dni_param_apellido_materno"
                                    id="form_modal_sec_consulta_dni_param_apellido_materno"
                                    placeholder="Apellido Materno"
                                    oninput="this.value=this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g,'')"
                                    maxlength="45">
                            	</div>
                        	</div>   
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button  type="submit" class="btn btn-success btn_guardar btn-subir-da">Guardar</button>
                <button class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!--FIN MODAL - CREAR COMPROBANTE -->
