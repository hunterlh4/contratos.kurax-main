<?php

$menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'mantenimiento' AND sub_sec_id = 'producto' LIMIT 1")->fetch_assoc();

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
					Producto
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
		                    onclick="sec_mantenimiento_producto_btn_nuevo();">
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

                <div class="row mt-3" id="sec_mantenimiento_producto_div_listar_producto">
                    <table id="sec_mantenimiento_producto_div_listar_producto_datatable" class="table table-bordered table-hover dt-responsive" cellspacing="0" width="100%" style="width:100%">
                        <thead>
                            <tr>
                                <th class="text-center">ID</th>
                                <th class="text-center">Nombre</th>
                                <th class="text-center">Descripción</th>
                                <th class="text-center">Canal Venta</th>
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

<!--INICIO MODAL - CREAR PRODUCTO-->
<div class="modal" id="sec_mantenimiento_producto_modal_nuevo_producto" data-backdrop="static" tabindex="false" style="">
    <div class="modal-dialog modal-md" style="width: 800px;" >
        <div class="modal-content modal-rounded">
            <div class="modal-header">
                <button type="button" class="close pull-right" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="sec_mantenimiento_producto_modal_nuevo_producto_titulo">
                </h4>
                <span class="campo-obligatorio">
                    (*) Campos obligatorios
                </span>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <form id="form_modal_sec_mantenimiento_producto" autocomplete="off">
                            
                            <input type="hidden" 
                                id="form_modal_sec_mantenimiento_producto_param_id" 
                                name="form_modal_sec_mantenimiento_producto_param_id" 
                                value="0"
                                readonly>

                            <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                <label>
                                    Nombre:
                                    <span class="campo-obligatorio">(*)</span>
                                </label>
                                <div class="form-group">
                                    <input 
                                        type="text" 
								        class="form-control" 
								        name="form_modal_sec_mantenimiento_producto_param_nombre"
								        id="form_modal_sec_mantenimiento_producto_param_nombre"
								        maxlength="50">
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
								        name="form_modal_sec_mantenimiento_producto_param_descripcion"
								        id="form_modal_sec_mantenimiento_producto_param_descripcion"
								        maxlength="100">
                                </div>
                            </div>
                            <div class="form-group col-lg-6 col-md-6 col-sm-6 col-xs-12" style="margin-bottom: 10px;">
                                <label>
                                    Canal de Venta:
                                    <span class="campo-obligatorio">(*)</span>
                                </label>
                                <div class="form-group">
                                    <select style="width:220px;display:inline;font-size: 14px;"
                                        class="form-control sec_mantenimiento_producto_select_filtro"
                                        name="form_modal_sec_mantenimiento_producto_param_canal_venta"
                                        id="form_modal_sec_mantenimiento_producto_param_canal_venta"
                                        title="Seleccione una opción">
                                    </select>
                                </div>
                            </div>
                            
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-success btn_guardar"
                	onclick="form_modal_sec_mantenimiento_producto_btn_guardar();">
                	Guardar
                </button>
                <button class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!--FIN MODAL - CREAR PRODUCTO-->

<!-- INICIO MODAL - HISTORICO PRODUCTO -->
<div class="modal" id="sec_mantenimiento_producto_modal_historial_cambio_producto" data-backdrop="static" tabindex="false" style="">
    <div class="modal-dialog modal-xl">
        <div class="modal-content modal-rounded">
            <div class="modal-header">
                <button type="button" class="close pull-right" data-dismiss="modal">&times;</button>
                <h4 class="modal-title" id="">
                	Historial de cambios
                </h4>
            </div>
            <div class="modal-body">
                <div class="col-md-12">

	                <div class="row mt-3" id="sec_mantenimiento_producto_div_listar_historial_cambio_producto">
	                    <table id="sec_mantenimiento_producto_div_listar_historial_cambio_producto_datatable" class="table table-bordered table-hover dt-responsive" cellspacing="0" width="100%" style="width:100%">
	                        <thead>
	                            <tr>
	                                <th class="text-center">ID</th>
	                                <th class="text-center">Nombre</th>
	                                <th class="text-center">Descripción</th>
	                                <th class="text-center">Canal Venta</th>
	                                <th class="text-center">Estado</th>
	                                <th class="text-center">Usuario Actualización</th>
	                                <th class="text-center">Fecha Actualización</th>
	                                <th class="text-center">Situación</th>
	                            </tr>
	                        </thead>
	                        <tbody>
	                        </tbody>
	                    </table>
	                </div>

            	</div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-default" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!-- FIN MODAL - HISTORICO PRODUCTO -->