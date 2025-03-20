<?php
global $mysqli;
$menu_id = "";
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '".$sec_id."' AND sub_sec_id = '".$sub_sec_id."' LIMIT 1");
while ($r = $result->fetch_assoc())
	$menu_id = $r["id"];

if (!array_key_exists($menu_id, $usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])) {
	echo "No tienes permisos para acceder a este recurso";
} else {

$usuario_id = $login?$login['id']:null;

?>

<style>
	fieldset.dhhBorder{
		border: 1px solid #ddd;
		padding: 0 15px 5px 15px;
		margin: 0 0 15px 0;
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

    .mbt {
        margin-left: 1.5px !important;
        margin-right: 1.5px !important;
    }
</style>

<div class="content container-fluid content_locales">
    <div class="page-header wide">
        <div class="row">
            <div class="col-xs-12 text-center">
                <div class="page-title"><i class="icon icon-inline fa fa-fw fa-building"></i>
                    Mantenimiento - Procesos </div>

            </div>
        </div>
    </div>

    <?php 
    if (array_key_exists($menu_id, $usuario_permisos) && in_array("registrar", $usuario_permisos[$menu_id])) {
    ?>
    <div class="row">
        <div class="col-md-12">
            <fieldset class="dhhBorder">
                <legend class="dhhBorder">Registro</legend>
                <form action="" id="frm_herramientas_ti_mantenimiento">
                    <div class="row">

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="">Módulo:</label>
                                <select name="sec_herramientas_ti_new_modulo_id" id="sec_herramientas_ti_new_modulo_id" class="form-control select2">
                                    <option></option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="">Nombre:</label>
                                <input type="text" name="sec_herramientas_ti_new_nombre" id="sec_herramientas_ti_new_nombre" class="form-control" maxlength="50">
                            </div>
                        </div>
                      
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="">Entidad:</label>
                                <select name="sec_herramientas_ti_new_entidad" id="sec_herramientas_ti_new_entidad" class="form-control select2" onchange="sec_herramientas_ti_mantenimiento_obtener_columndas_de_tabla()">
                                    <option></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="">Filtro Fecha:</label>
                                <select name="sec_herramientas_ti_new_filtro_fecha" id="sec_herramientas_ti_new_filtro_fecha" class="form-control select2">
                                    <option value="0">- Seleccione -</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-1">
                            <div class="form-group">
                                <br>
                                <button type="submit" class="btn btn-info form-control"><i class="fa fa-save"></i></button>
                            </div>
                        </div>


                    </div>
                </form>
            </fieldset>
        </div>
    </div>
    <hr>
    <?php 
    }
    ?>




    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive mt-3">
                <table id="tbl_herramienta_ti_procesos" class="table table-striped table-hover table-condensed table-bordered" cellspacing="0" width="100%">
                    <thead>
                        <th class="text-center" width="4%">ID</th>
                        <th class="text-center" width="20%">Módulo</th>
                        <th class="text-center" width="33%">Nombre</th>
                        <th class="text-center" width="15%">Entidad</th>
                        <th class="text-center" width="10%">Filtro Fecha</th>
                        <th class="text-center" width="10%">Estado</th>
                        <th class="text-center" width="7%">Acc.</th>
                    </thead>
                </table>
            </div>
        </div>
    </div>

   




    <div class="modal fade" id="sec_herramientas_ti_modal_definir_columnas" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog modal-md" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modal-title-definir-columna">Definir Columnas </h4>
			</div>
			<div class="modal-body">
                <form action="">
                    <div class="row">
                        <div class="col-md-9 form-group">
                            <label>Columna</label>
                            <select class="form-control select2" id="modal_herramienta_ti_proc_det_columna" name="modal_herramienta_ti_proc_det_columna">
                            </select>
                            <input type="hidden" id="modal_herramienta_ti_proc_det_proceso_id" class="form-control">
                        </div>
                        <div class="col-md-3 form-group">
                            <br>
                            <button type="button" onclick="sec_herramientas_ti_mantenimiento_modal_registrar_proceso_detalle();" class="btn btn-info form-control">Agregar</button>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive mt-3">
                                <table id="tbl_herramienta_ti_proceso_detalle" class="table table-striped table-hover table-condensed table-bordered" cellspacing="0" width="100%">
                                    <thead>
                                        <th class="text-center" width="10%">ID</th>
                                        <th class="text-center" width="65%">Nombre</th>
                                        <th class="text-center" width="15%">Estado</th>
                                        <th class="text-center" width="10%">Acc.</th>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
				
			</div>
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" aria-label="Close" class="btn btn-default pull-left">
					<b><i class="fa fa-close"></i> CERRAR</b>
				</button>
			</div>
		</div>
	  </div>
	</div>

    <div class="modal fade" id="sec_herramientas_ti_modal_proceso" role="dialog" aria-labelledby="myModalLabel">
	  <div class="modal-dialog modal-md" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="modal-title-parametros-generales">Editar Proceso </h4>
			</div>
			<div class="modal-body">
                <form action="">
                    <div class="row">

                        <div class="col-md-12 form-group">
                            <label>Módulo</label>
                            <select class="form-control select2" id="modal_herramienta_ti_proc_modulo_id" name="modal_herramienta_ti_proc_modulo_id" ></select>
                        </div>

                        <div class="col-md-12 form-group">
                            <label>Nombre</label>
                            <input type="text" id="modal_herramienta_ti_proc_nombre" class="form-control">
                            <input type="hidden" id="modal_herramienta_ti_proc_proceso_id" class="form-control">
                        </div>

                        <div class="col-md-12 form-group">
                            <label>Entidad</label>
                            <input type="text" id="modal_herramienta_ti_proc_tabla" readonly class="form-control">
                        </div>
                     
                        <div class="col-md-12 form-group">
                            <label>Filtro Fecha</label>
                            <select class="form-control select2" id="modal_herramienta_ti_proc_filtro_fecha" name="modal_herramienta_ti_proc_filtro_fecha" >
                            </select>
                        </div>

                        <div class="col-md-12 form-group">
                            <label>Estado</label>
                            <select class="form-control" id="modal_herramienta_ti_proc_status">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                     
                    </div>

                </form>
				
			</div>
			<div class="modal-footer">
				<button type="button" data-dismiss="modal" aria-label="Close" class="btn btn-default pull-left">
					<b><i class="fa fa-close"></i> CERRAR</b>
				</button>
				<?php 
				if (array_key_exists($menu_id, $usuario_permisos) && in_array("edit", $usuario_permisos[$menu_id])) {
				?>
				<button id="btn-modal-parametros-generales-modificar" type="button" class="btn btn-success pull-right" onclick="sec_herramientas_ti_mantenimiento_modal_editar_proceso();">
					<b><i class="fa fa-save"></i> EDITAR</b>
				</button>
				<?php
				}
				?>
				
			</div>
		</div>
	  </div>
	</div>

</div>

<?php 
}
?>