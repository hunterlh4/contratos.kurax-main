<?php 

$menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'mantenimiento' AND sub_sec_id = 'num_cuenta' LIMIT 1")->fetch_assoc();

$menu_permiso = $menu_id_consultar["id"];


?>

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

<div class="content container-fluid">
	<div class="page-header wide" style="margin-bottom: 10px;">
		<div class="row">
			<div class="col-xs-12 text-center">
				<h1 class="page-title titulosec_contrato">
					<i class="icon icon-inline glyphicon glyphicon-briefcase"></i> 
					Cuentas Bancarias y Contables
				</h1>
			</div>
		</div>
	</div>

    <?php if(array_key_exists($menu_permiso, $usuario_permisos)): ?>

    <div class="row" style="margin-bottom: 10px;">
        
        <?php if(in_array("btn_global_crear", $usuario_permisos[$menu_permiso])): ?>
            <button type="button" 
                    class="btn btn-success btn-sm" 
                    id="sec_mantenimiento_num_cuenta_btn_nuevo">
            <span class="fa fa-plus"></span>
                Nuevo
            </button>
        <?php endif; ?>

        <?php if(in_array("btn_descripcion_subdiario", $usuario_permisos[$menu_permiso])): ?>
            <button type="button" 
                    class="btn btn-warning btn-sm" 
                    onclick="sec_mantenimiento_num_cuenta_subdiarios()">
            <span class="icon icon-inline fa fa-tasks"></span>
                Sub Diarios
            </button>
        <?php endif; ?>

        <?php if(in_array("btn_num_procesos", $usuario_permisos[$menu_permiso])): ?>
            <button type="button" 
                    class="btn btn-info btn-sm" 
                    onclick="sec_mantenimiento_num_cuenta_procesos()">
            <span class="icon icon-inline fa fa-tasks"></span>
                Procesos
            </button>
        <?php endif; ?>

        <?php if(in_array("btn_num_cuenta_tipo_pago", $usuario_permisos[$menu_permiso])): ?>
            <button type="button" 
                    class="btn btn-primary btn-sm" 
                    onclick="sec_mantenimiento_num_cuenta_tipo_pagos()">
            <span class="icon icon-inline fa fa-tasks"></span>
                Tipos de pago
            </button>
        <?php endif; ?>

    </div>
    <?php endif; ?>

	<?php
	if(array_key_exists($menu_permiso,$usuario_permisos) && in_array("view", $usuario_permisos[$menu_permiso]))
	{
		?>
            <div class="col-md-12">

                <div class="row mt-3" id="sec_mantenimiento_num_cuenta_div_listar_cuenta_bancaria">
                    <table id="sec_mantenimiento_num_cuenta_div_listar_cuenta_bancaria_datatable" class="table table-bordered table-hover dt-responsive" cellspacing="0" width="100%" style="width:100%">
                        <thead>
                            <tr>
                                <th class="text-center">ID</th>
                                <th class="text-center">Canal</th>
                                <th class="text-center">Empresa</th>
                                <th class="text-center">Banco</th>
                                <th class="text-center">Nº Cuenta Bancaria</th>
                                <th class="text-center">Sub Diario</th>
                                <th class="text-center">Moneda</th>
                                <th class="text-center">Nº Cuenta Contable</th>
                                <th class="text-center">Código Anexo</th>
                                <th class="text-center">Tipo Pago</th>
                                <th class="text-center">Editar</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
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

</div>
<style>
    .campo-obligatorio {
    color: red;
}
</style>

<!--INICIO MODAL - CREAR CUENTA BANCARIA-->
<div class="modal" id="sec_mantenimiento_num_cuenta_modal_nuevo_cuenta_bancaria" data-backdrop="static" tabindex="false" style="">
    <div class="modal-dialog modal-md" style="width: 800px;" >
        <div class="modal-content modal-rounded">
            <div class="modal-header">
                <button type="button" class="close pull-right" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="sec_mantenimiento_num_cuenta_modal_nuevo_cuenta_bancaria_titulo">
                </h4>
                <span class="campo-obligatorio">
                    (*) Campos obligatorios
                </span>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <form id="form_modal_sec_mantenimiento_num_cuenta_cuenta_bancaria" autocomplete="off">
                            
                            <input type="hidden" 
                                id="form_modal_sec_mantenimiento_num_cuenta_param_id" 
                                name="form_modal_sec_mantenimiento_num_cuenta_param_id" 
                                value="0"
                                readonly>

                            <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                <label>
                                    Canal:
                                    <span class="campo-obligatorio">(*)</span>
                                </label>
                                <div class="form-group">
                                    <select style="width:220px;display:inline;font-size: 14px;"
                                        class="form-control sec_mantenimiento_num_cuenta_select_filtro"
                                        name="form_modal_sec_mantenimiento_num_cuenta_param_canal_id"
                                        id="form_modal_sec_mantenimiento_num_cuenta_param_canal_id"
                                        title="Seleccione">
                                    </select>
                                </div>
                            </div>

                            <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                <label>
                                    Empresa:
                                    <span class="campo-obligatorio">(*)</span>
                                </label>
                                <div class="form-group">
                                    <select style="width:220px;display:inline;font-size: 14px;"
                                        class="form-control sec_mantenimiento_num_cuenta_select_filtro"
                                        name="form_modal_sec_mantenimiento_num_cuenta_param_razon_social_id"
                                        id="form_modal_sec_mantenimiento_num_cuenta_param_razon_social_id"
                                        title="Seleccione">
                                    </select>
                                </div>
                            </div>

                            <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                <label>
                                    Banco:
                                </label>
                                <div class="form-group">
                                    <select style="width:220px;display:inline;font-size: 14px;"
                                        class="form-control sec_mantenimiento_num_cuenta_select_filtro"
                                        name="form_modal_sec_mantenimiento_num_cuenta_param_banco_id"
                                        id="form_modal_sec_mantenimiento_num_cuenta_param_banco_id"
                                        title="Seleccione">
                                    </select>
                                </div>
                            </div>
                            <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                <label>
                                    Moneda:
                                    <span class="sec_mantenimiento_form_num_cuenta_campo_obligatorio"></span>
                                </label>
                                <div class="form-group">
                                    <select style="width:220px;display:inline;font-size: 14px;"
                                        class="form-control sec_mantenimiento_num_cuenta_select_filtro"
                                        name="form_modal_sec_mantenimiento_num_cuenta_param_moneda_id"
                                        id="form_modal_sec_mantenimiento_num_cuenta_param_moneda_id"
                                        title="Seleccione">
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                <label>
                                    Número Cuenta Bancaria:
                                    <span class="sec_mantenimiento_form_num_cuenta_campo_obligatorio"></span>
                                </label>
								<div class="form-group">
									<input type="text" 
								            class="form-control" 
								            name="form_modal_sec_mantenimiento_num_cuenta_param_num_cuenta_corriente"
								            id="form_modal_sec_mantenimiento_num_cuenta_param_num_cuenta_corriente"
								            placeholder="XXXX-XXXX-XX-XXXXXXXX"
								            maxlength="45"
                                            oninput="this.value = this.value.replace(/[^0-9-]/g, '');">
								</div>
                            </div>

                            <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                <label>
                                    Código Anexo:
                                    <span class="sec_mantenimiento_form_num_cuenta_campo_obligatorio"></span>
                                </label>
                                <div class="form-group">
									<input type="text" 
											class="form-control" 
											name="form_modal_sec_mantenimiento_num_cuenta_param_cod_anexo"
											id="form_modal_sec_mantenimiento_num_cuenta_param_cod_anexo"
											placeholder="Ingrese el Código Anexo"
											maxlength="30"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '');">
								</div>
                            </div>


                            <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                <label>
                                    Número Cuenta Contable Debe:
                                    <span class="campo-obligatorio">(*)</span>
                                </label>
                                <div class="form-group">
									<input type="text"
											class="form-control" 
											name="form_modal_sec_mantenimiento_num_cuenta_param_num_cuenta_contable"
											id="form_modal_sec_mantenimiento_num_cuenta_param_num_cuenta_contable"
											placeholder="Ingrese la Número Cuenta Contable"
											maxlength="30"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '');">
								</div>
                            </div>

                            <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                <label>
                                    Número Cuenta Contable Haber:
                                </label>
                                <div class="form-group">
									<input type="text"
											class="form-control" 
											name="form_modal_sec_mantenimiento_num_cuenta_param_num_cuenta_contable_haber"
											id="form_modal_sec_mantenimiento_num_cuenta_param_num_cuenta_contable_haber"
											placeholder="Ingrese la Número Cuenta Contable del haber"
											maxlength="30"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '');">
								</div>
                            </div>

                            <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                <label>
                                    Sub Diario:
                                    <span class="sec_mantenimiento_form_num_cuenta_campo_obligatorio"></span>
                                </label>
                                <div class="form-group">
									<input type="text"
								            class="form-control" 
								            name="form_modal_sec_mantenimiento_num_cuenta_param_subdiario"
                                            id="form_modal_sec_mantenimiento_num_cuenta_param_subdiario"
								            placeholder="Ingrese el Sub Diario"
                                            maxlength="4"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                            onchange="sec_mantenimiento_num_cuenta_obtener_subdiario_descripcion();">
								</div>
                            </div>
                            <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                <label>
                                    Naturaleza de la operación:
                                </label>
                                <div class="form-group">
                                <input type="text"
                                        class="form-control" 
                                        class="form-control sec_mantenimiento_num_cuenta_select_filtro"
                                        name="form_modal_sec_mantenimiento_num_cuenta_param_subdiario_descripcion"
                                        id="form_modal_sec_mantenimiento_num_cuenta_param_subdiario_descripcion"
                                        readonly>                          
                                </div>
                            </div>

                
                            <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                <label>
                                    Tipo Pago:
                                    <span class="sec_mantenimiento_form_num_cuenta_campo_obligatorio"></span>
                                </label>
                                <div class="form-group">
                                    <select style="width:220px;display:inline;font-size: 14px;"
                                        class="form-control sec_mantenimiento_num_cuenta_select_filtro"
                                        name="form_modal_sec_mantenimiento_num_cuenta_param_tipo_pago_id"
                                        id="form_modal_sec_mantenimiento_num_cuenta_param_tipo_pago_id"
                                        title="Seleccione">                 
                                    </select>
                                </div>
                            </div>
                            <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                <label>
                                    Proceso:
                                </label>
                                <div class="form-group">
                                    <select style="width:220px;display:inline;font-size: 14px;"
                                        class="form-control sec_mantenimiento_num_cuenta_select_filtro"
                                        name="form_modal_sec_mantenimiento_num_cuenta_param_cont_num_cuenta_proceso_id"
                                        id="form_modal_sec_mantenimiento_num_cuenta_param_cont_num_cuenta_proceso_id"
                                        title="Seleccione">                               
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success btn_guardar">Guardar</button>
                <button class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!--FIN MODAL - CREAR CUENTA BANCARIA-->

<!-- INICIO MODAL SUBDIARIO -->
<div id="modalMantenimientoSubdiario" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_title_mantenimiento_subdiario">Subdiarios</h4>
			</div>
			<div class="modal-body">
                <fieldset class="dhhBorder">
                    <legend class="dhhBorder">Registro</legend>
                    <form method="POST" id="Frm_RegistroSubdiario" enctype="multipart/form-data" autocomplete="off">
                        <div class="row">
							<input id="modal_mant_subdiario_id" type="hidden" class="form-control">
                            <div class="col-md-2">
                            <div class="form-group">
                                    <label>Codigo de operación:</label>
                                    <input id="modal_mant_subdiario_cod_operacion" 
                                            class="form-control"
                                            maxlength="2"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                                </div>
                            </div>

                            <div class="col-md-6">
                            <div class="form-group">
                                    <label>Descripción:</label>
                                    <input id="modal_mant_subdiario_descripcion" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label></label>
                                    <button type="submit" class="btn form-control btn-info btn-guardar-subdiario"  id="btn-form-registro-subdiario"></button>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label></label>
                                    <button type="button" onclick="sec_mantenimiento_num_cuenta_reset_form_subdiario()" class="btn form-control btn-danger">Cancelar</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </fieldset>
                <hr>
                <div class="col-md-12">
                    <div class="table-responsive" id="mantenimiento_num_cuenta_subdiario_div_tabla">
						<table class="table display responsive" style="width:100%" id="mmantenimiento_num_cuenta_subdiario_datatable">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Cod Operación</th>
                                    <th class="text-center">Descripción</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Fecha creación</th>
                                    <th class="text-center">Usuario Creador</th>
									<th class="text-center">Fecha modificación</th>
                                    <th class="text-center">Usuario Modificador</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
							<tfoot>
								<tr>
									<th scope="col"></th>
									<th scope="col"></th>
									<th scope="col"></th>
									<th scope="col"></th>
									<th scope="col"></th>
									<th scope="col"></th>
                                    <th scope="col"></th>
									<th scope="col"></th>
									<th scope="col"></th>
								</tr>
							</tfoot>
                        </table>
                    </div>
                </div>
			</div>
				<div class="modal-footer">
				
				</div>
			</div>
		</div>
</div>
<!-- FIN MODAL SUBDIARIO -->

<!-- INICIO MODAL PROCESOS -->
<div id="modalMantenimientoProceso" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_title_mantenimiento_proceso">Subdiarios</h4>
			</div>
			<div class="modal-body">
                <fieldset class="dhhBorder">
                    <legend class="dhhBorder">Registro</legend>
                    <form method="POST" id="Frm_RegistroProceso" enctype="multipart/form-data" autocomplete="off">
                        <div class="row">
							<input id="modal_mant_proceso_id" type="hidden" class="form-control">
                            <div class="col-md-3">
                            <div class="form-group">
                                    <label>Nombre:</label>
                                    <input id="modal_mant_proceso_nombre" 
                                            class="form-control"
                                            maxlength="30">
                                </div>
                            </div>

                            <div class="col-md-5">
                            <div class="form-group">
                                    <label>Descripción:</label>
                                    <input id="modal_mant_proceso_descripcion" class="form-control">
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label></label>
                                    <button type="submit" class="btn form-control btn-info btn-guardar-proceso"  id="btn-form-registro-proceso"></button>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label></label>
                                    <button type="button" onclick="sec_mantenimiento_num_cuenta_reset_form_proceso()" class="btn form-control btn-danger">Cancelar</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </fieldset>
                <hr>
                <div class="col-md-12">
                    <div class="table-responsive" id="mantenimiento_num_cuenta_proceso_div_tabla">
						<table class="table display responsive" style="width:100%" id="mantenimiento_num_cuenta_proceso_datatable">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Nombre</th>
                                    <th class="text-center">Descripción</th>
                                    <th class="text-center">Estado</th>
									<th class="text-center">Fecha creación</th>
                                    <th class="text-center">Usuario Creador</th>
									<th class="text-center">Fecha modificación</th>
                                    <th class="text-center">Usuario Modificador</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
							<tfoot>
								<tr>
									<th scope="col"></th>
									<th scope="col"></th>
									<th scope="col"></th>
									<th scope="col"></th>
									<th scope="col"></th>
                                    <th scope="col"></th>
                                    <th scope="col"></th>
									<th scope="col"></th>
									<th scope="col"></th>
								</tr>
							</tfoot>
                        </table>
                    </div>
                </div>
			</div>
				<div class="modal-footer">
				
				</div>
			</div>
		</div>
</div>
<!-- FIN MODAL PROCESOS -->

<!-- INICIO MODAL TIPO DE PAGO -->
<div id="modalMantenimientoTipoPago" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="modal_title_mantenimiento_tipo_pago">Subdiarios</h4>
			</div>
			<div class="modal-body">
                <fieldset class="dhhBorder">
                    <legend class="dhhBorder">Registro</legend>
                    <form method="POST" id="Frm_RegistroTipoPago" enctype="multipart/form-data" autocomplete="off">
                        <div class="row">
							<input id="modal_mant_tipo_pago_id" type="hidden" class="form-control">
                            <div class="col-md-6">
                            <div class="form-group">
                                    <label>Nombre:</label>
                                    <input id="modal_mant_tipo_pago_nombre" 
                                            class="form-control"
                                            maxlength="30">
                                </div>
                            </div>
                            
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label></label>
                                    <button type="submit" class="btn form-control btn-info btn-guardar-tipo_pago"  id="btn-form-registro-tipo_pago"></button>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label></label>
                                    <button type="button" onclick="sec_mantenimiento_num_cuenta_reset_form_tipo_pago()" class="btn form-control btn-danger">Cancelar</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </fieldset>
                <hr>
                <div class="col-md-12">
                    <div class="table-responsive" id="mantenimiento_num_cuenta_tipo_pago_div_tabla">
						<table class="table display responsive" style="width:100%" id="mantenimiento_num_cuenta_tipo_pago_datatable">
                            <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Nombre</th>
                                    <th class="text-center">Estado</th>
									<th class="text-center">Fecha creación</th>
                                    <th class="text-center">Usuario Creador</th>
									<th class="text-center">Fecha modificación</th>
                                    <th class="text-center">Usuario Modificador</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
							<tfoot>
								<tr>
									<th scope="col"></th>
									<th scope="col"></th>
									<th scope="col"></th>
									<th scope="col"></th>
									<th scope="col"></th>
									<th scope="col"></th>
									<th scope="col"></th>
									<th scope="col"></th>
								</tr>
							</tfoot>
                        </table>
                    </div>
                </div>
			</div>
				<div class="modal-footer">
				
				</div>
			</div>
		</div>
</div>
<!-- FIN MODAL TIPO DE PAGO -->


<!-- INICIO MODAL HISTORICO USUARIOS POR GRUPO -->
<div id="modalCuentasContablesHistoricoCambios" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
	<div class="modal-dialog modal-xl" role="document">
		<div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                 <h4 class="modal-title" id="modal_title_cuenta_contable_historico"></h4>
            </div>

            <div class="modal-body">
                <div class="panel-body">
                    <div class="col-md-12">
                        <fieldset class="dhhBorder">
                            <legend class="dhhBorder">Busqueda</legend>
                                <form autocomplete="off">
                                <div class="form-group col-md-10" style="height: 55px;">
                                    <input id="num_cuenta_id" type="hidden" class="form-control">

                                    <label>Campos:</label>
                                    <div class="form-group">
                                        <div class="input-group col-xs-6">
                                            <select class="form-control input_text select2"
                                                data-live-search="true"
                                                id="search_id_mantenimiento_num_cuenta_campo_id"
                                                name="search_id_mantenimiento_num_cuenta_campo_id"
                                                style="width: 100%">
                                            </select>
                                        </div>
                                    </div>
                                </div>						
                    
                                <div class="form-group col-md-10 text-right" style="margin-top: 5px; margin-bottom: 10px; float: right;">
                                    <button type="button" class="btn btn-warning float-left" id="btn_mantenimiento_historico_limpiar_filtros_de_busqueda">
                                        <i class="fa fa-eraser"></i>
                                        Limpiar filtros
                                    </button>
                                    <button type="button" class="btn btn-info float-left" onclick="sec_mantenimiento_num_cuenta_historico_listar_Datatable();">
                                        <i class="glyphicon glyphicon-search"></i>
                                        Buscar
                                    </button>
                                </div>

                                <div class="col-md-12" id="cont_locales_alerta_filtrar_por">
                                                
                                </div>
                                </form>
                        </fieldset>

                        <div class="table-responsive" id="cuenta_contable_historico_cambios_div_tabla">
                            <table class="table display responsive" style="width:100%" id="cuenta_contable_historico_cambios_datatable">
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
	</div>
</div>
<!-- FIN MODAL HISTORICO USUARIOS POR GRUPO -->
