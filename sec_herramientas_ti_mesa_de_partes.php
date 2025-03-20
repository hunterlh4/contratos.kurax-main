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



$select_entidad = "SELECT * FROM tbl_herramientas_ti_proceso AS hp
WHERE hp.modulo_id = 3 AND hp.status = 1  ORDER BY hp.id DESC";
$sel_query = $mysqli->query($select_entidad);


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

    .select2-container .select2-selection--multiple {
 
      min-height: 100px;
    }



</style>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/buttons/2.2.1/css/buttons.dataTables.min.css">
<div class="content container-fluid content_locales">
    <div class="page-header wide">
        <div class="row">
            <div class="col-xs-12 text-center">
                <div class="page-title"><i class="icon icon-inline fa fa-fw fa-building"></i>
                    Reporte TI - Mesa de Partes </div>

            </div>
        </div>
    </div>

 
    <div class="row">
        <div class="col-md-12">
            <fieldset class="dhhBorder">
                <legend class="dhhBorder">Reporte</legend>
                <form action="" id="frm_herramientas_ti_proceso">
                    <div class="row">

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="">Entidad:</label>
                                <select name="sec_herr_ti_proc_proceso_id" id="sec_herr_ti_proc_proceso_id" onchange="sec_herramientas_ti_proc_obtener_proceso_detalle()" class="form-control select2" >
                                <option value="0">- Seleccione -</option>
                                    <?php 
                                    while($sel = $sel_query->fetch_assoc()){
                                    ?>
                                    <option value="<?=$sel['id']?>"><?=$sel['tabla']?></option>
                                    <?php 
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="">Consulta de datos:</label>
                                <select name="sec_herr_ti_proc_proceso_detalle_id" id="sec_herr_ti_proc_proceso_detalle_id" multiple="multiple" class="form-control select2">
                                    
                                    
                                </select>
                            </div>
                        </div>

                         <div class="col-md-2">
                            <div class="form-group">
                                <label for="">Fecha Inicio:</label>
                                <input type="text" value="<?=date('Y-m-d').' 00:00:00'?>" id="sec_herr_ti_proc_fecha_inicio" name="sec_herr_ti_proc_fecha_inicio" class="form-control fecha_datepicker_desde text-center">
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="">Fecha Fin:</label>
                                <input type="text" value="<?=date('Y-m-d').' 00:00:00'?>" id="sec_herr_ti_proc_fecha_fin" name="sec_herr_ti_proc_fecha_fin" class="form-control fecha_datepicker_hasta text-center">
                            </div>
                        </div>

                        <div class="col-md-1">
                            <div class="form-group">
                                <br>
                                <button type="submit" class="btn btn-primary form-control"><i class="icon fa fa-fw fa-search"></i></button>
                            </div>
                        </div>


                    </div>
                </form>
            </fieldset>
        </div>
    </div>
    <hr>
   



    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive mt-3">
                <table id="tbl_herramienta_ti_reporte" class="table table-striped table-hover table-condensed table-bordered display" cellspacing="0" width="100%">
                    <thead>
                        <tr>

                        </tr>
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
                <h4 class="modal-title" id="modal-title-parametros-generales">Definir Columnas </h4>
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
                            <label>Nombre</label>
                            <input type="text" id="modal_herramienta_ti_proc_nombre" class="form-control">
                            <input type="hidden" id="modal_herramienta_ti_proc_proceso_id" class="form-control">
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Descripción</label>
                            <input type="text" class="form-control" id="modal_herramienta_ti_proc_descripcion">
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Entidad</label>
                            <input type="text" readonly class="form-control" id="modal_herramienta_ti_proc_tabla">
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