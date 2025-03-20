<?php 

$menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'mantenimiento' AND sub_sec_id = 'razon_social' LIMIT 1")->fetch_assoc();

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
					<i class="icon icon-inline glyphicon fa fa-building"></i> 
					Empresas AT
				</h1>
			</div>
		</div>
	</div>

    <?php if(array_key_exists($menu_permiso, $usuario_permisos)): ?>

    <div class="row" style="margin-bottom: 10px;">
        
        <?php if(in_array("btn_global_crear", $usuario_permisos[$menu_permiso])): ?>
            <button type="button" 
                    class="btn btn-success btn-sm" 
                    id="sec_mantenimiento_razon_social_btn_nuevo">
            <span class="fa fa-plus"></span>
                Nuevo
            </button>
        <?php endif; ?>

    </div>
    <?php endif; ?>

	<?php
	if(array_key_exists($menu_permiso,$usuario_permisos) && in_array("view", $usuario_permisos[$menu_permiso]))
	{
		?>
            <div class="col-md-12">

                <div class="row mt-3" id="sec_mantenimiento_razon_social_div_listar">
                    <table id="sec_mantenimiento_razon_social_div_listar_datatable" class="table table-bordered table-hover dt-responsive" cellspacing="0" width="100%" style="width:100%">
                        <thead>
                            <tr>
                                <th class="text-center">ID</th>
                                <th class="text-center">Empresa</th>
                                <th class="text-center">Canal</th>
                                <th class="text-center">Red</th>
                                <th class="text-center">RUC</th>
                                <th class="text-center">Subdiario</th>
                                <th class="text-center">Opciones</th>
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

<!--INICIO MODAL - CREAR RAZÓN SOCIAL -->

<div class="modal" id="sec_mantenimiento_razon_social_modal_nuevo" data-backdrop="static" tabindex="false" style="">
    <div class="modal-dialog modal-md" style="width: 800px;" >
        <div class="modal-content modal-rounded">
            <div class="modal-header">
                <button type="button" class="close pull-right" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="sec_mantenimiento_razon_social_modal_guardar_titulo">
                </h4>
                <span class="campo-obligatorio">
                    (*) Campos obligatorios
                </span>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <form id="Frm_RegistroRazonSocial" autocomplete="off">
                            
                            <input type="hidden" 
                                id="form_modal_sec_mantenimiento_razon_social_param_id" 
                                name="form_modal_sec_mantenimiento_razon_social_param_id" 
                                value="0"
                                readonly>

                            <div class="panel-group" id="accordionRazónSocialDatos" role="tablist" aria-multiselectable="true">
							    <div class="panel panel-default">
								<div class="panel-heading" role="tab" id="headingRazónSocialDatos">
								<h4 class="panel-title">
									<a role="button" data-toggle="collapse" data-parent="#accordionRazónSocialDatos" href="#collapseRazónSocialDatos" aria-expanded="true" aria-controls="collapseRazónSocialDatos">
									DATOS DE EMPRESA
									</a>
								</h4>
								</div>
								<div id="collapseRazónSocialDatos" class="panel-collapse-all panel-collapse collapse in" role="tabpanel" aria-labelledby="headingRazónSocialDatos">
									<div class="panel-body">
										<div class="col-md-12">
                            
                            
                                        <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                            <label>
                                                Nombre:
                                                <span class="campo-obligatorio">(*)</span>
                                            </label>
                                            <div class="form-group">
                                                <input type="text" 
                                                        class="form-control campo-editable" 
                                                        name="form_modal_sec_mantenimiento_razon_social_param_nombre_empresa"
                                                        id="form_modal_sec_mantenimiento_razon_social_param_nombre_empresa"
                                                        placeholder="Nombre de empresa"
                                                        maxlength="45">
                                            </div>
                                        </div>

                                        <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                            <label>
                                                Código Empresa:
                                                <span class="sec_mantenimiento_form_razon_social_campo_obligatorio"></span>
                                            </label>
                                            <div class="form-group">
                                                <input type="text"
                                                        class="form-control campo-editable" 
                                                        name="form_modal_sec_mantenimiento_razon_social_param_codigo_empresa"
                                                        id="form_modal_sec_mantenimiento_razon_social_param_codigo_empresa"
                                                        placeholder="Ingrese el código de empresa"
                                                        maxlength="4"
                                                        oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                                            </div>
                                        </div>

                                        <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                            <label>
                                                Canal:
                                            </label>
                                            <div class="form-group">
                                                <select style="width:220px;display:inline;font-size: 14px;"
                                                    class="form-control select-editable sec_mantenimiento_razon_social_select_filtro"
                                                    name="form_modal_sec_mantenimiento_razon_social_param_canal"
                                                    id="form_modal_sec_mantenimiento_razon_social_param_canal"
                                                    title="Seleccione">
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                            <label>
                                                Red:
                                            </label>
                                            <div class="form-group">
                                                <select style="width:220px;display:inline;font-size: 14px;"
                                                    class="form-control select-editable sec_mantenimiento_razon_social_select_filtro"
                                                    name="form_modal_sec_mantenimiento_razon_social_param_red"
                                                    id="form_modal_sec_mantenimiento_razon_social_param_red"
                                                    title="Seleccione">
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                            <label>
                                                RUC:
                                                <span class="sec_mantenimiento_form_razon_social_campo_obligatorio"></span>
                                            </label>
                                            <div class="form-group">
                                                <input type="text"
                                                        class="form-control campo-editable" 
                                                        name="form_modal_sec_mantenimiento_razon_social_param_ruc"
                                                        id="form_modal_sec_mantenimiento_razon_social_param_ruc"
                                                        placeholder="Ingrese el RUC"
                                                        maxlength="11"
                                                        oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                                        >
                                            </div>
                                        </div>

                                        <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                            <label>
                                                Código SAP:
                                                <span class="sec_mantenimiento_form_razon_social_campo_obligatorio"></span>
                                            </label>
                                            <div class="form-group">
                                                <input type="text"
                                                        class="form-control campo-editable" 
                                                        name="form_modal_sec_mantenimiento_razon_social_param_codigo_sap"
                                                        id="form_modal_sec_mantenimiento_razon_social_param_codigo_sap"
                                                        placeholder="Ingrese el código SAP"
                                                        maxlength="4"
                                                        >
                                            </div>
                                        </div>

                                        </div>
								    </div>
							    </div>
                                </div>
				            </div>

                            <div class="panel-group" id="accordionRazónSocialSubdiarios" role="tablist" aria-multiselectable="true">
							    <div class="panel panel-default">
                                    <div class="panel-heading" role="tab" id="headingRazónSocialSubdiarios">
                                        <h4 class="panel-title">
                                            <a role="button" data-toggle="collapse" data-parent="#accordionRazónSocialSubdiarios" href="#collapseRazónSocialSubdiarios" aria-expanded="true" aria-controls="collapseRazónSocialSubdiarios">
                                                SUBDIARIOS
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="collapseRazónSocialSubdiarios" class="panel-collapse-all panel-collapse collapse in" role="tabpanel" aria-labelledby="headingRazónSocialSubdiarios">
                                        <div class="panel-body">
                                            <div class="col-md-12">
                                
                                            <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                                <label>
                                                    Subdiario:
                                                    <span class="sec_mantenimiento_form_razon_social_campo_obligatorio"></span>
                                                </label>
                                                <div class="form-group">
                                                    <input type="text"
                                                            class="form-control campo-editable" 
                                                            name="form_modal_sec_mantenimiento_razon_social_param_subdiario"
                                                            id="form_modal_sec_mantenimiento_razon_social_param_subdiario"
                                                            placeholder="Ingrese el Subdiario"
                                                            maxlength="4"
                                                            oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                                            onchange="sec_mantenimiento_razon_social_obtener_subdiario_descripcion();">
                                                </div>
                                            </div>

                                            <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                                <label>
                                                    Naturaleza de la operación:
                                                </label>
                                                <div class="form-group">
                                                <input type="text"
                                                        class="form-control"
                                                        name="form_modal_sec_mantenimiento_razon_social_param_subdiario_descripcion"
                                                        id="form_modal_sec_mantenimiento_razon_social_param_subdiario_descripcion"
                                                        readonly>                          
                                                </div>
                                            </div>
                                                        
                                            <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                                <label>
                                                    Subdiario Contabilidad:
                                                    <span class="sec_mantenimiento_form_razon_social_campo_obligatorio"></span>
                                                </label>
                                                <div class="form-group">
                                                    <input type="text"
                                                            class="form-control campo-editable" 
                                                            name="form_modal_sec_mantenimiento_razon_social_param_subdiario_contabilidad"
                                                            id="form_modal_sec_mantenimiento_razon_social_param_subdiario_contabilidad"
                                                            placeholder="Ingrese el Subdiario de contabilidad"
                                                            maxlength="4"
                                                            oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                                            onchange="sec_mantenimiento_razon_social_obtener_subdiario_contabilidad_descripcion();">
                                                </div>
                                            </div>

                                            <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                                <label>
                                                    Naturaleza de la operación:
                                                </label>
                                                <div class="form-group">
                                                <input type="text"
                                                        class="form-control" 
                                                        name="form_modal_sec_mantenimiento_razon_social_param_subdiario_contabilidad_descripcion"
                                                        id="form_modal_sec_mantenimiento_razon_social_param_subdiario_contabilidad_descripcion"
                                                        readonly>                          
                                                </div>
                                            </div>

                                            <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                                <label>
                                                    Subdiario Compra con IGV:
                                                    <span class="sec_mantenimiento_form_razon_social_campo_obligatorio"></span>
                                                </label>
                                                <div class="form-group">
                                                    <input type="text"
                                                            class="form-control campo-editable" 
                                                            name="form_modal_sec_mantenimiento_razon_social_param_subdiario_compra_con_igv"
                                                            id="form_modal_sec_mantenimiento_razon_social_param_subdiario_compra_con_igv"
                                                            placeholder="Ingrese el Subdiario"
                                                            maxlength="4"
                                                            oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                                            onchange="sec_mantenimiento_razon_social_obtener_subdiario_compra_con_igv_descripcion();">
                                                </div>
                                            </div>

                                            <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                                <label>
                                                    Naturaleza de la operación:
                                                </label>
                                                <div class="form-group">
                                                <input type="text"
                                                        class="form-control" 
                                                        name="form_modal_sec_mantenimiento_razon_social_param_subdiario_compra_con_igv_descripcion"
                                                        id="form_modal_sec_mantenimiento_razon_social_param_subdiario_compra_con_igv_descripcion"
                                                        readonly>                          
                                                </div>
                                            </div>
                                            <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                                <label>
                                                    Subdiario Compra sin IGV:
                                                    <span class="sec_mantenimiento_form_razon_social_campo_obligatorio"></span>
                                                </label>
                                                <div class="form-group">
                                                    <input type="text"
                                                            class="form-control campo-editable" 
                                                            name="form_modal_sec_mantenimiento_razon_social_param_subdiario_compra_sin_igv"
                                                            id="form_modal_sec_mantenimiento_razon_social_param_subdiario_compra_sin_igv"
                                                            placeholder="Ingrese el Subdiario"
                                                            maxlength="4"
                                                            oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                                            onchange="sec_mantenimiento_razon_social_obtener_subdiario_compra_sin_igv_descripcion();">
                                                </div>
                                            </div>
                                            <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                                <label>
                                                    Naturaleza de la operación:
                                                </label>
                                                <div class="form-group">
                                                <input type="text"
                                                        class="form-control" 
                                                        name="form_modal_sec_mantenimiento_razon_social_param_subdiario_compra_sin_igv_descripcion"
                                                        id="form_modal_sec_mantenimiento_razon_social_param_subdiario_compra_sin_igv_descripcion"
                                                        readonly>                          
                                                </div>
                                            </div>

                                            <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                                <label>
                                                    Subdiario Cancelación Caja chica:
                                                    <span class="sec_mantenimiento_form_razon_social_campo_obligatorio"></span>
                                                </label>
                                                <div class="form-group">
                                                    <input type="text"
                                                            class="form-control campo-editable" 
                                                            name="form_modal_sec_mantenimiento_razon_social_param_subdiario_cancelacion_caja_chica"
                                                            id="form_modal_sec_mantenimiento_razon_social_param_subdiario_cancelacion_caja_chica"
                                                            placeholder="Ingrese el Subdiario"
                                                            maxlength="4"
                                                            oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                                            onchange="sec_mantenimiento_razon_social_obtener_subdiario_cancelacion_caja_chica_descripcion();">
                                                </div>
                                            </div>

                                            <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                                <label>
                                                    Naturaleza de la operación:
                                                </label>
                                                <div class="form-group">
                                                <input type="text"
                                                        class="form-control" 
                                                        class="form-control sec_mantenimiento_razon_social_select_filtro"
                                                        name="form_modal_sec_mantenimiento_razon_social_param_subdiario_cancelacion_caja_chica_descripcion"
                                                        id="form_modal_sec_mantenimiento_razon_social_param_subdiario_cancelacion_caja_chica_descripcion"
                                                        readonly>                          
                                                </div>
                                            </div>                                
                                            
                                            </div>
                                        </div>
                                    </div>
                                </div>
				            </div>

                            <div class="panel-group" id="accordionRazónSocialPermisos" role="tablist" aria-multiselectable="true">
							    <div class="panel panel-default">
								<div class="panel-heading" role="tab" id="headingRazónSocialPermisos">
								<h4 class="panel-title">
									<a role="button" data-toggle="collapse" data-parent="#accordionRazónSocialPermisos" href="#collapseRazónSocialPermisos" aria-expanded="true" aria-controls="collapseRazónSocialPermisos">
									    PERMISOS Y ESTADOS
									</a>
								</h4>
								</div>
								<div id="collapseRazónSocialPermisos" class="panel-collapse-all panel-collapse collapse in" role="tabpanel" aria-labelledby="headingRazónSocialPermisos">
									<div class="panel-body">
										<div class="col-md-12">
                            
                                        <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                            <label>
                                                Estado Tesoreria:
                                            </label>
                                            <div class="form-group">
                                                <select style="width:220px;display:inline;font-size: 14px;"
                                                    class="form-control select-editable sec_mantenimiento_razon_social_select_filtro"
                                                    name="form_modal_sec_mantenimiento_razon_social_param_estado_tesoreria"
                                                    id="form_modal_sec_mantenimiento_razon_social_param_estado_tesoreria"
                                                    title="Seleccione">     
                                                    <option value="1">Activo</option>
                                                    <option value="0">Inactivo</option>            
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                            <label>
                                                Estado Vales de descuentos:
                                            </label>
                                            <div class="form-group">
                                                <select style="width:220px;display:inline;font-size: 14px;"
                                                    class="form-control select-editable sec_mantenimiento_razon_social_select_filtro"
                                                    name="form_modal_sec_mantenimiento_razon_social_param_estado_vale"
                                                    id="form_modal_sec_mantenimiento_razon_social_param_estado_vale"
                                                    title="Seleccione">     
                                                    <option value="1">Activo</option>
                                                    <option value="0">Inactivo</option>            
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                            <label>
                                                Habilitado en servicios públicos:
                                            </label>
                                            <div class="form-group">
                                                <select style="width:220px;display:inline;font-size: 14px;"
                                                    class="form-control select-editable sec_mantenimiento_razon_social_select_filtro"
                                                    name="form_modal_sec_mantenimiento_razon_social_param_habilitado_servicios_publicos"
                                                    id="form_modal_sec_mantenimiento_razon_social_param_habilitado_servicios_publicos"
                                                    title="Seleccione">     
                                                    <option value="1">Activo</option>
                                                    <option value="0">Inactivo</option>            
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                            <label>
                                                Habilitado en préstamos bóveda:
                                            </label>
                                            <div class="form-group">
                                                <select style="width:220px;display:inline;font-size: 14px;"
                                                    class="form-control select-editable sec_mantenimiento_razon_social_select_filtro"
                                                    name="form_modal_sec_mantenimiento_razon_social_param_habilitado_prestamo_boveda"
                                                    id="form_modal_sec_mantenimiento_razon_social_param_habilitado_prestamo_boveda"
                                                    title="Seleccione">                 
                                                    <option value="1">Activo</option>
                                                    <option value="0">Inactivo</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                            <label>
                                                Habilitado en recargas kasnet:
                                            </label>
                                            <div class="form-group">
                                                <select style="width:220px;display:inline;font-size: 14px;"
                                                    class="form-control select-editable sec_mantenimiento_razon_social_select_filtro"
                                                    name="form_modal_sec_mantenimiento_razon_social_param_habilitado_recargas_kasnet"
                                                    id="form_modal_sec_mantenimiento_razon_social_param_habilitado_recargas_kasnet"
                                                    title="Seleccione">     
                                                    <option value="1">Activo</option>
                                                    <option value="0">Inactivo</option>            
                                                </select>
                                            </div>
                                        </div>

                                        </div>
								    </div>
							    </div>
                                </div>
				            </div>

                            <div class="panel-group" id="accordionRazónSocialCamposAuditoria" role="tablist" aria-multiselectable="true">
							    <div class="panel panel-default">
								<div class="panel-heading" role="tab" id="headingRazónSocialCamposAuditoria">
								<h4 class="panel-title">
									<a role="button" data-toggle="collapse" data-parent="#accordionRazónSocialCamposAuditoria" href="#collapseRazónSocialCamposAuditoria" aria-expanded="true" aria-controls="collapseRazónSocialCamposAuditoria">
									CAMPOS DE AUDITORÍA
									</a>
								</h4>
								</div>
								<div id="collapseRazónSocialCamposAuditoria" class="panel-collapse-all panel-collapse collapse in" role="tabpanel" aria-labelledby="headingRazónSocialCamposAuditoria">
									<div class="panel-body">
										<div class="col-md-12">
                            
                                        <div id="campoFechaCreacion">
                                        <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                            <label>
                                                Fecha creación:
                                            </label>
                                            <div class="form-group">
                                                <input type="text" 
                                                        class="form-control" 
                                                        name="form_modal_sec_mantenimiento_razon_social_param_fecha_create"
                                                        id="form_modal_sec_mantenimiento_razon_social_param_fecha_create"
                                                        readonly>
                                            </div>
                                        </div>

                                        <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                            <label>
                                                Usuario creador:
                                                <span class="sec_mantenimiento_form_razon_social_campo_obligatorio"></span>
                                            </label>
                                            <div class="form-group">
                                                <input type="text"
                                                        class="form-control" 
                                                        name="form_modal_sec_mantenimiento_razon_social_param_usuario_create"
                                                        id="form_modal_sec_mantenimiento_razon_social_param_usuario_create"
                                                        readonly>
                                            </div>
                                        </div>
                                        </div>
                                        <div id="campoFechaActualiacion">
                                        <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                            <label>
                                                Fecha actualización:
                                            </label>
                                            <div class="form-group">
                                                <input type="text"
                                                        class="form-control" 
                                                        name="form_modal_sec_mantenimiento_razon_social_param_fecha_update"
                                                        id="form_modal_sec_mantenimiento_razon_social_param_fecha_update"
                                                        placeholder="Sin cambios"
                                                        readonly>
                                            </div>
                                        </div>

                                        <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                            <label>
                                                Usuario modificador:
                                            </label>
                                            <div class="form-group">
                                                <input type="text"
                                                        class="form-control" 
                                                        name="form_modal_sec_mantenimiento_razon_social_param_usuario_update"
                                                        id="form_modal_sec_mantenimiento_razon_social_param_usuario_update"
                                                        placeholder="Sin cambios"
                                                    readonly>
                                            </div>
                                        </div>                                  
                                        </div>  
                                        </div>
								    </div>
							    </div>
                                </div>
				            </div>
                    
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button  type="submit" class="btn btn-success btn_guardar">Guardar</button>
                <button class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!--FIN MODAL - CREAR RAZÓN SOCIAL -->
