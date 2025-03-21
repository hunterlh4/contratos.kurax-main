<?php
global $mysqli;
$menu_id = "";
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'vale' AND sub_sec_id = 'fraccionamiento_manual' LIMIT 1");
while ($r = $result->fetch_assoc())
    $menu_id = $r["id"];

if (!array_key_exists($menu_id, $usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])) {
    echo "No tienes permisos para acceder a este recurso";
} else {

    $usuario_id = $login ? $login['id'] : null;

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
            <h1 class="page-title titulosec_contrato"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Vales de Descuento - Fraccionamiento Manual</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12">

            <div class="row">

                <div class="col-md-12">
                    <div class="panel">
                        <div class="panel-heading">
                            <div class="panel-title">Busqueda de Vales de Descuento</div>
                        </div>
                        <div class="panel-body no-pad">
                            <form id="frm_fracc_manual_vale_descuento" method="post">
                                <div class="row">
                                    <div class="col-md-9 no-pad">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="" class="control-label">Empresa: <strong class="text-danger">*</strong> </label>
                                                    <select class="form-control select2" multiple="multiple" name="sec_vale_fracc_manual_empresa[]" id="sec_vale_fracc_manual_empresa"></select>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="" class="control-label">Zona: <strong class="text-danger">*</strong> </label>
                                                    <select class="form-control select2" multiple="multiple" name="sec_vale_fracc_manual_zona[]" id="sec_vale_fracc_manual_zona">
                                                        <option value="0">- Seleccione -</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-7">
                                                <div class="form-group">
                                                    <label for="" class="control-label">Empleado: </label>
                                                    <input type="text" class="form-control" name="sec_vale_fracc_manual_empleado" id="sec_vale_fracc_manual_empleado">
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <label for="" class="control-label">DNI: </label>
                                                    <input type="text" class="form-control" name="sec_vale_fracc_manual_dni" id="sec_vale_fracc_manual_dni">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="" class="control-label">Desde: <strong class="text-danger">*</strong></label>
                                            <input type="text" readonly value="<?= date('d-m-Y') ?>" class="form-control text-center sec_vale_fracc_manual_datepicker" name="sec_vale_fracc_manual_fecha_desde_vale" id="sec_vale_fracc_manual_fecha_desde_vale" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label for="" class="control-label">Hasta: <strong class="text-danger">*</strong></label>
                                            <input type="text" readonly value="<?= date('d-m-Y') ?>" class="form-control text-center sec_vale_fracc_manual_datepicker" name="sec_vale_fracc_manual_fecha_hasta_vale" id="sec_vale_fracc_manual_fecha_hasta_vale" class="form-control">
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
                        <div class="panel-body">


                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table display responsive" style="width:100%"" id="tbl_vale_fraccionamiento_manual">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Nro Vale</th>
                                                <th class="text-center">Empresa</th>
                                                <th class="text-center">Zona</th>
                                                <th class="text-center">Local</th>
                                                <th class="text-center">Empleado</th>
                                                <th class="text-center">DNI</th>
                                                <th class="text-center">Motivo</th>
                                                <th class="text-center">Fecha <br>Vale</th>
                                                <th class="text-center">Monto</th>
                                                <th class="text-center">Cuotas</th>
                                                <th class="text-center hidden">Observación</th>
                                                <th class="text-center">Estado</th>
                                                <th class="text-center">Acciones</th>
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





    </div>
    </div>



    <!-- INICIO MODAL SELECCIONAR ARCHIVO ADJUNTO -->
    <div id="sec_vale_fracc_manual_modal_fraccionamiento" class="modal fade" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="modal_cambio_cuota_moneda_titulo">Fraccionamiento Manual</h4>
            </div>
            <div class="modal-body mb-1">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-nowrap">
                        <tr>
                            <th class="text-left">Deuda Total:</th>
                            <th class="text-right">
                                <span id="lbl_deuda_total">0.00</span>
                                <input type="hidden" id="modal_fracc_manual_vale_id">
                                <input type="hidden" id="modal_fracc_manual_deuda_total">
                                <input type="hidden" id="modal_fracc_manual_cuotas">
                                
                            </th>
                            <th class="text-center">
                                <button type="button" onclick="sec_vale_fracc_manual_agregar_cuota()" class="btn btn-info btn-sm">
                                    <i class="icon fa fa-fw fa-plus-circle"></i>
                                </button>
                                <button type="button" onclick="sec_vale_fracc_manual_eliminar_cuota()" class="btn btn-danger btn-sm">
                                    <i class="icon fa fa-fw fa-minus-circle"></i>
                                </button>
                            </th>
                        </tr>
                    </table>
               </div>

               <div class="table-responsive">
                    <table class="table table-bordered table-striped table-nowrap">
                        <thead>
                            <tr>
                                <th class="text-center">Cuota</th>
                                <th class="text-center">Monto</th>
                            </tr>
                        </thead>
                        <tbody id="block_table_body_coutas">
                        </tbody>
                    </table>
               </div>

               
            </div>
            <div class="modal-footer mt-1">
                <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar Ventana</button>
              
                <button type="button" class="btn btn-primary" onclick="sec_vale_fracc_manual_validar_fraccionamiento()">Guardar</button>
            </div>
        </div>
    </div>
    </div>
    <!-- FIN MODAL SELECCIONAR ARCHIVO ADJUNTO -->

<?php } ?>