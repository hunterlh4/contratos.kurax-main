<?php 

$menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'mantenimiento' AND sub_sec_id = 'canal_venta' LIMIT 1")->fetch_assoc();

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

    .campo-obligatorio {
    color: red;
    
</style>

<div class="content container-fluid">
	<div class="page-header wide" style="margin-bottom: 10px;">
		<div class="row">
			<div class="col-xs-12 text-center">
				<h1 class="page-title titulosec_contrato">
					<i class="icon icon-inline glyphicon glyphicon-briefcase"></i> 
					Canales de Ventas
				</h1>
			</div>
		</div>
	</div>
	
    <?php if(array_key_exists($menu_permiso, $usuario_permisos)): ?>
		<div class="row" style="margin-bottom: 10px;">
			
			<?php
        		if(in_array("save", $usuario_permisos[$menu_permiso]))
        		{
        			?>
	        			<button type="button" 
		                    class="btn btn-success btn-sm" 
		                    onclick="sec_mantenimiento_canal_venta_btn_nuevo();">
			            	<span class="fa fa-plus"></span>
			                Nuevo
			            </button>
					<?php
        		}
        	?>

		</div>	
	<?php endif; ?>

	<?php
	if(array_key_exists($menu_permiso,$usuario_permisos) && in_array("view", $usuario_permisos[$menu_permiso]))
	{
		?>
            <div class="col-md-12">

                <div class="row mt-3" id="sec_mantenimiento_canal_venta_div_listar_canal_venta">
                    <table id="sec_mantenimiento_canal_venta_div_listar_canal_venta_datatable" class="table table-bordered table-hover dt-responsive" cellspacing="0" width="100%" style="width:100%">
                        <thead>
                            <tr>
                                <th class="text-center">ID</th>
                                <th class="text-center">Servicio</th>
                                <th class="text-center">Liquidación</th>
                                <th class="text-center">Nombre</th>
                                <th class="text-center">Código</th>
                                <th class="text-center">Descripción</th>
                                <th class="text-center">Color Hexadecimal</th>
                                <th class="text-center">Pago Manual</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
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


<!--INICIO MODAL - CREAR CANAL DE VENTA-->
<div class="modal" id="sec_mantenimiento_canal_venta_modal_nuevo_canal_venta" data-backdrop="static" tabindex="false" style="">
    <div class="modal-dialog modal-md" style="width: 800px;" >
        <div class="modal-content modal-rounded">
            <div class="modal-header">
                <button type="button" class="close pull-right" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="sec_mantenimiento_canal_venta_modal_nuevo_canal_venta_titulo">
                </h4>
                <span class="campo-obligatorio">
                    (*) Campos obligatorios
                </span>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <form id="form_modal_sec_mantenimiento_canal_venta" autocomplete="off">
                            
                            <input type="hidden" 
                                id="form_modal_sec_mantenimiento_canal_venta_param_id" 
                                name="form_modal_sec_mantenimiento_canal_venta_param_id" 
                                value="0"
                                readonly>

                            <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                <label>
                                    Servicio:
                                    <span class="campo-obligatorio">(*)</span>
                                </label>
                                <div class="form-group">
                                    <select style="width:220px;display:inline;font-size: 14px;"
                                        class="form-control sec_mantenimiento_canal_venta_select_filtro"
                                        name="form_modal_sec_mantenimiento_canal_venta_param_servicio"
                                        id="form_modal_sec_mantenimiento_canal_venta_param_servicio"
                                        title="Seleccione una opción">
                                    </select>
                                </div>
                            </div>

                            <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                <label>
                                    Aplica Liquidación:
                                    <span class="campo-obligatorio">(*)</span>
                                </label>
                                <div class="form-group">
                                	<select style="width:220px;display:inline;font-size: 14px;"
                                        class="form-control sec_mantenimiento_canal_venta_select_filtro"
                                        name="form_modal_sec_mantenimiento_canal_venta_param_aplica_liquidacion"
                                        id="form_modal_sec_mantenimiento_canal_venta_param_aplica_liquidacion"
                                        title="Seleccione">
                                        <option value="">-- Seleccione una opción --</option>
                                        <option value="1">Si</option>
                                        <option value="0">No</option>
                                    </select>
                                </div>
                            </div>

                            <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                <label>
                                    Nombre:
                                    <span class="campo-obligatorio">(*)</span>
                                </label>
                                <div class="form-group">
                                    <input 
                                        type="text" 
								        class="form-control" 
								        name="form_modal_sec_mantenimiento_canal_venta_param_nombre"
								        id="form_modal_sec_mantenimiento_canal_venta_param_nombre"
								        maxlength="100">
                                </div>
                            </div>
                            <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                <label>
                                    Código:
                                    <span class="campo-obligatorio">(*)</span>
                                </label>
                                <div class="form-group">
                                    <input 
                                        type="text" 
								        class="form-control" 
								        name="form_modal_sec_mantenimiento_canal_venta_param_codigo"
								        id="form_modal_sec_mantenimiento_canal_venta_param_codigo"
								        maxlength="100">
                                </div>
                            </div>
                            <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                <label>
                                    Descripción:
                                    <span class="campo-obligatorio">(*)</span>
                                </label>
                                <div class="form-group">
                                    <input 
                                        type="text" 
								        class="form-control" 
								        name="form_modal_sec_mantenimiento_canal_venta_param_descripcion"
								        id="form_modal_sec_mantenimiento_canal_venta_param_descripcion"
								        maxlength="100">
                                </div>
                            </div>
                            
                            <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                <label>
                                    Color Hexadecimal:
                                </label>
								<div class="form-group">
									<input 
                                        type="text" 
								        class="form-control" 
								        name="form_modal_sec_mantenimiento_canal_venta_param_color_hexadecimal"
								        id="form_modal_sec_mantenimiento_canal_venta_param_color_hexadecimal"
								        maxlength="6">
								</div>
                            </div>


                            <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                <label>
                                    Aplica Pago Manual:
                                    <span class="campo-obligatorio">(*)</span>
                                </label>
								<div class="form-group">
                                    <select
                                        class="form-control"
                                        name="form_modal_sec_mantenimiento_canal_venta_param_aplica_pago_manual"
                                        id="form_modal_sec_mantenimiento_canal_venta_param_aplica_pago_manual"
                                        title="Seleccione">
                                        <option selected value="1">Si</option>
                                        <option value="0">No</option>
                                    </select>
								</div>
                            </div>

                            <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                <label>
                                    Estado:
                                    <span class="campo-obligatorio">(*)</span>
                                </label>
                                <div class="form-group">
                                	<select 
                                        class="form-control"
                                        name="form_modal_sec_mantenimiento_canal_venta_param_estado"
                                        id="form_modal_sec_mantenimiento_canal_venta_param_estado"
                                        title="Seleccione">
                                        <option selected value="1">Activo</option>
                                        <option value="0">Inactivo</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success btn_guardar"
                	onclick="form_modal_sec_mantenimiento_canal_venta_btn_guardar();">
                	Guardar
                </button>
                <button class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!--FIN MODAL - CREAR CANAL DE VENTA-->