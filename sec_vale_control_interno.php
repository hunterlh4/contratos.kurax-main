<?php
global $mysqli;
$menu_id = "";
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'vale' AND sub_sec_id = 'control_interno' LIMIT 1");
while ($r = $result->fetch_assoc())
    $menu_id = $r["id"];

if (!array_key_exists($menu_id, $usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])) {
    echo "No tienes permisos para acceder a este recurso";
} else {

    $usuario_id = $login ? $login['id'] : null;
    $permiso_recharzar_vale = false;
    if (array_key_exists($menu_id, $usuario_permisos) && in_array("rechazar_vale", $usuario_permisos[$menu_id])) {
        $permiso_recharzar_vale = true;
    }

?>

    <style>
        .campo_obligatorio {
            font-size: 15px;
            color: red;
        }

        .form-group {
            margin-bottom: 10px !important;
        }

        .select2-selection--multiple {
            max-height: 10rem !important;
        }
    </style>

    <div id="div_sec_vale_nuevo"></div>

    <div id="loader_"></div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 text-center">
            <h1 class="page-title titulosec_contrato"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Vales de Descuento - Control Interno</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-md-12 col-lg-12">

            <div class="row">

                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">Busqueda de Vales de Control Interno</div>
                        </div>
                        <div class="panel-body no-pad">
                            <form id="frm_control_interno_vale_descuento" method="post">
                                <div class="row">
                                    <div class="col-md-9 no-pad">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="" class="control-label">Empresa: <strong class="text-danger">*</strong> </label>
                                                    <select class="form-control select2" multiple="multiple" name="sec_vale_control_int_empresa[]" id="sec_vale_control_int_empresa"></select>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="" class="control-label">Zona: <strong class="text-danger">*</strong> </label>
                                                    <select class="form-control select2" multiple="multiple" name="sec_vale_control_int_zona[]" id="sec_vale_control_int_zona">
                                                        <option value="0">- Seleccione -</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-7">
                                                <div class="form-group">
                                                    <label for="" class="control-label">Empleado: </label>
                                                    <input type="text" class="form-control" name="sec_vale_control_int_empleado" id="sec_vale_control_int_empleado">
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <label for="" class="control-label">DNI: </label>
                                                    <input type="text" class="form-control" name="sec_vale_control_int_dni" id="sec_vale_control_int_dni">
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="" class="control-label">Estado: <strong class="text-danger">*</strong></label>
                                                    <select name="sec_vale_control_int_estado[]" multiple="multiple" id="sec_vale_control_int_estado" class="form-control select2">
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="" class="control-label">Desde: <strong class="text-danger">*</strong></label>
                                            <input type="text" readonly value="<?= date('d-m-Y') ?>" class="form-control text-center sec_vale_control_int_datepicker" name="sec_vale_control_int_fecha_desde_vale" id="sec_vale_control_int_fecha_desde_vale" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label for="" class="control-label">Hasta: <strong class="text-danger">*</strong></label>
                                            <input type="text" readonly value="<?= date('d-m-Y') ?>" class="form-control text-center sec_vale_control_int_datepicker" name="sec_vale_control_int_fecha_hasta_vale" id="sec_vale_control_int_fecha_hasta_vale" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label for="control-label"></label>
                                            <button type="submit" class="btn btn-info form-control">Buscar</button>
                                        </div>
                                    </div>

                                </div>
                            </form>
                        
                        </div>
                    </div>

                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">RESULTADOS</div>
                        </div>
                        <div class="panel-body no-pad">


                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table display responsive" style="width:100%"" id="tbl_vale_control_interno">
                                        <thead>
                                            <tr>
                                                <th class="text-center hidden">Id</th>
                                                <th class="text-center">Nro Vale</th>
                                                <th class="text-center">Empresa</th>
                                                <th class="text-center">Zona</th>
                                                <th class="text-center">Local</th>
                                                <th class="text-center">Empleado</th>
                                                <th class="text-center">DNI</th>
                                                <th class="text-center">Motivo</th>
                                                <th class="text-center">Fecha <br> Vale</th>
                                                <th class="text-center">Monto</th>
                                                <th class="text-center">ID Totalizado</th>
                                                <th class="text-center hidden">Observación</th>
                                                <th class="text-center">Doc. Adj.</th>
                                                <th class="text-center">Estado</th>
                                                <th class="text-center">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-md-5"></div>
                                <div class="col-md-2">
                                    <button id="button-aprobar-selected" style="display:none" type="button"  class="btn-vale-control-aprobar btn btn-sm btn-o btn-rounded btn-success form-control">Aprobar Vales</button>
                                </div>
                            </div>

                        </div>
                    </div>



                </div>
            </div>


        </div>





    </div>
    </div>


      <!-- INICIO MODAL VIZUALIDOR DE DOCUMENTO -->
      <div id="sec_vale_control_int_modal_vizualizacion_archivo" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="panel panel-tasks">
                        <div class="panel-body">
                            <div class="col-md-12 text-center" id="div-container-pdf">
                            
                            </div>
                            <div class="col-md-12 text-center" id="div-container-img">

                            </div>
                            <div class="col-md-12 text-center" id="div-container-img-full-pantalla">

                            </div>
                            

                            
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
                </div>
            </div>
        </div>
    </div>
    <!-- FIN MODAL SELECCIONAR VIZUALIDOR DE DOCUMENTO -->

<?php } ?>