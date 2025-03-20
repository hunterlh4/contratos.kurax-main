<div class="panel">
    <div class="panel-heading text-center">
				<div class="panel-title"> Tipos</div>
	</div><br>
    <div class="form-inline mb-2">
        <div class="row">
            <div class="col-lg-4 col-md-2 col-sm-4 col-xs-12 mb-2">
                <?php 
                if (array_key_exists($menu_id, $usuario_permisos) && in_array("nuevo_tipo", $usuario_permisos[$menu_id])){

                ?>
                <a style="" onclick="sec_mantenimientos_contrato_tipo_modal()" class="btn btn-rounded btn-min-width btn-success mr-4 mt-2 mt-md-0"><i class="glyphicon glyphicon-plus"></i> Agregar</a>
                <?php }?>
            </div>
            <div class="col-lg-4 col-md-5 col-sm-5 col-xs-12 mb-2">
                <label class="mr-2" for="tipo">Tipo:</label>
                <select class="form-control mr-4 contrato_tipo_select2" id="tipo_mantenimiento_contrato" name="tipo_mantenimiento_contrato">
                    <option data-nombre_tipo="0" value="0">Seleccione una opción</option>
                    <?php
                    $sql_mantenimiento = "
										select *from cont_mantenimiento_contrato where status=1";

                    $resultado = $mysqli->query($sql_mantenimiento);
                    foreach ($resultado as $lis) {
                    ?>
                        <option data-nombre_tipo="<?php echo $lis['nombre'] ?>" value="<?php echo $lis['nombre_tabla'] ?>"><?php echo $lis['nombre'] ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-lg-4 col-md-5 col-sm-3 col-xs-12">
                <label class="mr-2" for="select_opcion2">Estado:</label>
                <select class="form-control mr-4" id="estado_contrato_tipo" name="estado_contrato_tipo">
                    <option value="">-- Todos --</option>
                    <option value="Activo">Activo</option>
                    <option value="Inactivo">Inactivo</option>
                </select>
            </div>
        </div>
    </div>





    <br>
    <div class="row">
        <div class="col-md-12">
            <div class="tab-content">
                <div class="modo1 activo">
                    <div class="row">
                        <div class="table-responsive">

                            <table id="tbl_datos_mantenimientos_contrato_tipo" class="table table-striped table-hover table-condensed table-bordered" cellspacing="0" width="100%">
                                <thead>
                                    <tr>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>

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


<div class="modal fade" id="modal_nuevo_contrato_tipo" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title titulo_modal_contrato_tipo"> Nuevo registro en tipo : <label id="txtipo"></label></h4>
                <input type="hidden" id="tipo_accion_modal">
                <input type="hidden" id="id_contrato_tipo">
            </div>
            <div class="modal-body">
                <div class="col-xs-12">
                    <div id="boxMessage" class="alert alert-success text-center" style="display: none"></div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="">Nombre: </label>
                            <input type="text" id="nombre_contrato_tipo" name="nombre" class="form-control" placeholder="" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-6 d-flex align-items-center">
                        <label class="mr-2 flex-nowrap" for="select_opcion2">Seleccione el estado: </label><br>

                        <select class="form-control mr-4" id="estado_tipo" name="estado_tipo">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <i class="icon fa fa-close"></i>
                    Cancelar</button>
                <button type="button" class="btn btn-success" id="guardar_contrato_tipo">
                    <i class="icon fa fa-save"></i>
                    <span>Guardar</span>
                </button>
            </div>
        </div>
    </div>
</div>