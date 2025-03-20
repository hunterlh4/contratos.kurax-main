<?php

$menu_id = "";
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'registro' AND sub_sec_id = 'premios' LIMIT 1");
while ($r = $result->fetch_assoc()) $menu_id = $r["id"];

$locales_arr = array();
$locales_command = "SELECT id, nombre, cc_id FROM tbl_locales";
if ($login["usuario_locales"]) {
    $locales_command .= " WHERE id IN (" . implode(",", $login["usuario_locales"]) . ")";
}
$locales_command .= " ORDER BY nombre ASC";
$locales_query = $mysqli->query($locales_command);
if ($mysqli->error) {
    print_r($mysqli->error);
    exit();
}

while ($l = $locales_query->fetch_assoc()) {
    $locales_arr[] = $l;
}

if (!array_key_exists($menu_id, $usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])) {
    echo "No tienes permisos para acceder a este recurso";
    die;
}
?>

<script>

</script>

<div class="tbl_goldenRace_retail_jackpots">

    <div class="col-xs-12 col-md-8 col-md-offset-2">
        <h3 class="textCenter"><b>Registro de Premios</b></h3>

        <form id="formJackpots" name="formJackpots" class="formulario col-xs-12" method="POST"
              enctype="multipart/form-data">

            <div class="col-xs-12 col-lg-6 mb-3">
                <?php if($login["area_id"] == 21 && in_array($login["cargo_id"],[4,5])): //CAJERO ?>
                    <select name="paid_local" id="paidLocal" class="form-control" style="display: none" data-is-cashier="true">
                        <option value="<?= $login["local_id"] ?>"></option>
                    </select>
                <?php elseif (count($locales_arr) == 1): ?>
                    <select name="paid_local" id="paidLocal" class="form-control" style="display: none" data-is-cashier="false">
                        <option value="<?= $locales_arr[0]["id"]; ?>">[<?= $locales_arr[0]["cc_id"]; ?>
                            ] <?= $locales_arr[0]["nombre"]; ?></option>
                    </select>
                <?php else: ?>
                    <label for="paidLocal">Escoja el local:</label>
                    <select name="paid_local" id="paidLocal" class="form-control select2" data-is-cashier="false" style="width: 100%">
                        <?php foreach ($locales_arr as $l_key => $l): ?>
                            <option value="<?= $l["id"]; ?>">[<?= $l["cc_id"]; ?>] <?= $l["nombre"]; ?></option>
                        <?php endforeach;?>
                    </select>
                <?php endif; ?>
            </div>

            <div class="btn_tipo_premio col-xs-6 mb-3">
                <label for="selectTipo">Tipo de Ticket:</label>
                <br>
                <div class="chekes mt-3">
                    <label for="selectTipo0" class="clicked_t"><span>JACKPOT</span>
                        <input id="selectTipo0" class="selectTipo" type="radio" name="selectTipo" value="0"
                               checked="true">
                        <span class="checkmark"></span>
                    </label>
                </div>

                <div class="chekes mt-3">
                    <label for="selectTipo1"><span>BINGO</span>
                        <input id="selectTipo1" class="selectTipo" type="radio" name="selectTipo" value="1">
                        <span class="checkmark"></span>
                    </label>
                </div>

                <div class="chekes mt-3">
                    <label for="selectTipo2"><span>PREMIOS > A 36 MIL SOLES</span>
                        <input id="selectTipo2" class="selectTipo" type="radio" name="selectTipo" value="2">
                        <span class="checkmark"></span>
                    </label>
                </div>
                <div class="chekes mt-3">
                    <label for="selectTipo3"><span>SORTEO</span>
                        <input id="selectTipo3" class="selectTipo" type="radio" name="selectTipo" value="3">
                        <span class="checkmark"></span>
                    </label>
                </div>
                <div class="chekes mt-3">
                    <label for="selectTipo4"><span>MEGA JACKPOT</span>
                        <input id="selectTipo4" class="selectTipo" type="radio" name="selectTipo" value="4">
                        <span class="checkmark"></span>
                    </label>
                </div>
                <div class="chekes mt-3">
                    <label for="selectTipo6"><span>TORITO</span>
                        <input id="selectTipo6" class="selectTipo" type="radio" name="selectTipo" value="6">
                        <span class="checkmark"></span>
                    </label>
                </div>
                <div class="chekes mt-3">
                    <label for="selectTipo7"><span>FREEBET</span>
                        <input id="selectTipo7" class="selectTipo" type="radio" name="selectTipo" value="7">
                        <span class="checkmark"></span>
                    </label>
                </div>
            </div>

            <hr>

            <input type="hidden" name="user" id="user" value="<?php echo $login['id'] ?>">
            <input type="hidden" name="fecha" id="fecha" value="<?php echo date('Y-m-d H:i:s'); ?>">

            <div class="col-xs-12">
                <div class="section_type_doc">
                    <fieldset>
                        <span><strong>Tipo de Documento: </strong><br/>

                            <div class="btnRadio">
                                <label for="ckDni" class="clicked_o"><span>DNI</span>
                                    <input id="ckDni" type="radio" name="tipoDoc" value="DNI" checked="true"
                                           data-val="0">
                                    <span class="checkmark"></span>
                                </label>
                            </div>

                            <div class="btnRadio">
                                <label for="CE_PE"><span>CE / PTP</span>
                                    <input id="CE_PE" type="radio" name="tipoDoc" value="CE_PTP" data-val="1">
                                    <span class="checkmark"></span>
                                </label>
                            </div>

                            <div class="btnRadio">
                                <label for="PS"><span>PASAPORTE</span>
                                    <input id="PS" type="radio" name="tipoDoc" value="PS" data-val="2">
                                    <span class="checkmark"></span>
                                </label>
                            </div>
                    </fieldset>
                </div>
            </div>

            <!-- DNI -->
            <div class="search_dni consultaD col-xs-12">

                <div class="col-xs-12 col-sm-10 no-pad">
                    <div class="focoPadre">
                        <input id="txtDniCliente" class="auto-focus" type="text" tabindex="1"
                               placeholder="Ingrese el Nro de Documento" autocomplete="off">
                        <span class="triki"></span>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-2 no-pad">
                    <button class="btn btn-primary consultar" type="button" name="button" id="btnConsultaDni">
                        CONSULTAR
                    </button>
                </div>

                <div class="col-xs-12 no-pad">
                    <div class="table-responsive">
                        <table class="tablaRegJackpot table table-bordered">
                            <thead>
                                <tr>
                                    <td>DNI</td>
                                    <td>Nombres</td>
                                    <td>Apellido Paterno</td>
                                    <td>Apellido Materno</td>
                                </tr>
                            </thead>
                            <tbody id="bodyDni">
                                <tr>
                                    <td id="tdni" class="text-muted">DNI</td>
                                    <td id="tnombres" class="text-muted">Nombres</td>
                                    <td id="tapepat" class="text-muted">Apellido Paterno</td>
                                    <td id="tapemat" class="text-muted">Apellido Materno</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>


            </div>

            <!-- CARNET DE EXTRANJERIA -->
            <div class="search_Ce col-xs-12">
                <div class="row consultaDoc">
                    <div class="col-xs-10 txtForm">
                        <div class="focoPadre">
                            <input id="Nro_doc" type="text" name="Nro_doc" value=""
                                   placeholder="Ingrese el Nro de Documeto">
                            <span class="triki"></span>
                        </div>
                    </div>
                    <div class="col-xs-2 no-pad">
                        <button class="btn btn-primary consultar" type="button" name="button">Consultar</button>
                    </div>
                </div>
                <div class="row inputContCe">
                    <table class="tablaRegJackpot table table-bordered">
                        <thead>
                        <tr>
                            <td>CE / PTP</td>
                            <td>nombres</td>
                            <td>Apellido Paterno</td>
                            <td>Apellido Materno</td>
                        </tr>
                        </thead>
                        <tbody>
                        <td>
                            <div class="txtForm">
                                <input id="CE_Nro" type="text" name="" value="" placeholder="Nro de CE / PTP"
                                       disabled="true">
                            </div>
                        </td>

                        <td>
                            <div class="txtForm">
                                <div class="focoPadre">
                                    <input id="CE_Nombres" type="text" name="" value="" placeholder="Nombres"
                                           disabled="true">
                                    <span class="triki"></span>
                                </div>
                            </div>
                        </td>

                        <td>
                            <div class="txtForm">
                                <input id="CE_ApePat" type="text" name="" value="" placeholder="Apellido Paterno"
                                       disabled="true">
                            </div>
                        </td>

                        <td>
                            <div class="txtForm">
                                <input id="CE_ApeMat" type="text" name="" value="" placeholder="Apellido Materno"
                                       disabled="true">
                            </div>
                        </td>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- PASAPORTE -->
            <div class="search_Ps col-xs-12">
                <div class="row consultaDoc">
                    <div class="col-xs-10 txtForm">
                        <div class="focoPadre">
                            <input id="Nro_doc_Ps" type="text" name="Nro_doc" value=""
                                   placeholder="Ingrese el Nro de Documeto">
                            <span class="triki"></span>
                        </div>
                    </div>
                    <div class="col-xs-2 no-pad">
                        <button class="btn btn-primary consultar" type="button" name="button">CONSULTAR</button>
                    </div>
                </div>
                <div class="row inputsContPs">
                    <table class="tablaRegJackpot table table-bordered">
                        <thead>
                        <tr>
                            <td>Nro de Pasaporte</td>
                            <td>Nombres</td>
                            <td>Apellido Paterno</td>
                            <td>Apellido Materno</td>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>
                                <div class="txtForm">
                                    <input id="PS_Nro" type="text" name="" value="" placeholder="Nro de Pasaporte"
                                           disabled="true">
                                </div>
                            </td>

                            <td>
                                <div class="txtForm">
                                    <input class="mover" id="PS_Nombres" type="text" name="" value=""
                                           placeholder="Nombres" disabled="true">
                                </div>
                            </td>

                            <td>
                                <div class="txtForm">
                                    <input class="mover" id="PS_ApePat" type="text" name="" value=""
                                           placeholder="Apellido Paterno" disabled="true">
                                </div>
                            </td>
                            <td>
                                <div class="txtForm">
                                    <input class="mover" id="PS_ApeMat" type="text" name="" value=""
                                           placeholder="Apellido Materno" disabled="true">
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- DIV - SORTEO -->
            <div id="premioText" class="col-xs-12 content_data_sorteo" style="margin-bottom: 1em;margin-top: -0.5em; display: none">
                <input type="hidden" name="premio_tipo_codigo" id="premio_tipo_codigo">
                <h4>
                    <span class="label label-primary">PREMIO:</span>
                    <span id="prize_amount_show" class="ml-3" style="font-weight: bolder"> S/. 100.00</span><span id="prize_amount_text"></span>
                </h4>
                <table class="table table-bordered" id="premios_table">
                    <thead>
                        <tr>
                            <td></td>
                            <td>Nombre de Sorteo</td>
                            <td>Fecha de Sorteo</td>
                            <td>Monto de premio</td>
                            <td>Estado</td>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>


            <!-- AVISO DATOS DEL CLIENTE -->
            <div class="col-xs-12 content_data hidden_content" style="margin-bottom: 1em;margin-top: -1em;">
                <h4><span class="col-xs-12 label label-danger">RECUERDE PEDIR ESTOS DATOS AL CLIENTE</span></h4>
            </div>
            <div class="col-xs-12 content_data hidden_content" style="margin-top: -1em;margin-bottom: 1em;">
                <table class="table table-bordered table-client-data">
                    <thead>
                    <tr>
                        <td>Correo Electrónico</td>
                        <td>Teléfono</td>
                        <td>Profesión</td>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>
                            <input id="clientEmail" type="email" name="" placeholder="CORREO ELECTRÓNICO"
                                   autocomplete="off" class="reg-client-data" disabled>
                        </td>
                        <td>
                            <input id="clientPhone" type="tel" name="" placeholder="TELÉFONO"
                                   autocomplete="off" class="reg-client-data" disabled>
                        </td>
                        <td>
                            <input id="clientProfession" type="tel" name="" placeholder="PROFESIÓN"
                                   autocomplete="off" class="reg-client-data" disabled>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- DATOS DEL TICKET -->
            <div class="col-xs-12 content_data hidden_content">
                <p><strong>Datos del ticket</strong></p>
                <div class="jackpot_data">
                    <div class="tabla_jackpot">
                        <div class="table-responsive">
                            <table class="tablaRegJackpot table table-bordered">
                            <thead>
                            <tr>
                                <td>Nro de Ticket</td>
                                <td>Monto Apostado</td>
                                <td>Monto Ganado</td>
                                <td>Local</td>
                            </tr>
                            </thead>
                            <tbody>
                            <td>
                                <div class="row">
                                    <div class="col-xs-10 col-md-10 no-pad">
                                        <div class="focoPadre">
                                            <input id="txtNroTicket" class="txtTick move" type="text" name=""
                                                   placeholder="NRO DE TICKET" autocomplete="off">
                                            <span class="triki"></span>
                                        </div>
                                    </div>
                                    <div class="col-xs-2 col-md-2 no-pad">
                                        <button type="button" id="btnBuscarTicket" class="btn btn-primary"><i
                                                    class="fa fa-search" aria-hidden="true"></i></button>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <input id="txtMontoApostado" class="" type="text" name="" placeholder="Monto Apostado"
                                       autocomplete="off" disabled>
                            </td>
                            <td>
                                <input id="txtMonto" class="" type="text" name="" placeholder="Monto Ganado"
                                       autocomplete="off" disabled>
                            </td>
                            <td>
                                <input id="cbLocales" class="" type="text" name="" placeholder="Local"
                                       autocomplete="off" data-idLocal="" disabled>
                            </td>
                            </tbody>
                        </table>
                        </div>
                        
                    </div>

                    <hr>

                    <p><strong>Autoriza sus datos para marketing</strong></p>

                    <div class="col-xs-2 no-pad">
                        <div class="btnRadio radioCheck">
                            <label for="checkAut" tabindex="" class="radioCheck">
                                <span>SÍ</span>
                                <input id="checkAut" name="checkAut" type="radio">
                                <span class="checkmark"></span>
                            </label>
                        </div>
                    </div>

                    <div class="col-xs-2 no-pad">
                        <div class="btnRadio radioCheck">
                            <label for="checkAutNo" tabindex="" class="radioCheck">
                                <span>NO</span>
                                <input id="checkAutNo" name="checkAut" type="radio">
                                <span class="checkmark"></span>
                            </label>
                        </div>
                    </div>


                </div>
            </div>

            <!-- DATOS DEL TICKET -->
            <div class="col-xs-12 content_data_sorteo" style="display: none;">
                <p><strong>Datos del ticket</strong></p>
                <div class="jackpot_data">
                    <div class="tabla_jackpot">
                        <table class="tablaRegJackpot table table-bordered">
                            <thead>
                            <tr>
                                <td>Nro de Ticket</td>
                                <td>Monto Apostado</td>
                                <td>Monto Ganado</td>
                                <td>Local</td>
                            </tr>
                            </thead>
                            <tbody>
                            <td>
                                <div class="row">
                                    <div class="col-md-10 no-pad">
                                        <div class="focoPadre">
                                            <input id="txtNroTicketTeleservicios" class="txtTick move" type="text" name=""
                                                   placeholder="NRO DE TICKET" autocomplete="off">
                                            <span class="triki"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-2 no-pad">
                                        <button type="button" id="btnBuscarTicketTeleservicios" class="btn btn-primary"><i
                                                    class="fa fa-search" aria-hidden="true"></i></button>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <input id="txtMontoApostadoTeleservicios" class="" type="text" name="" placeholder="Monto Apostado"
                                       autocomplete="off" disabled>
                            </td>
                            <td>
                                <input id="txtMontoTeleservicios" class="" type="text" name="" placeholder="Monto Ganado"
                                       autocomplete="off" disabled>
                            </td>
                            <td>
                                <input id="cbLocalesTeleservicios" class="" type="text" name="" placeholder="Local"
                                       autocomplete="off" data-idLocal="" disabled>
                            </td>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-xs-12 bton mt-5">
                <input id="tokenSesion" type="hidden" value="<?php print_r($login['sesion_cookie']); ?>">
                <button id="btnSaveSig" style="display: none" class="btnSaveSig btn btn-success mgR" type="button"
                    name="button" data-token="<?php print_r($login['sesion_cookie']); ?>" disabled>
                    Guardar - Firmar
                    <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                </button>
                <?php 
                    $regprem_grupo_id = $login ? $login['grupo_id'] : 0;
                    $regprem_area_id = $login ? $login['area_id'] : 0;
                    $regprem_cargo_id = $login ? $login['cargo_id'] : 0;
                    if( (int)$regprem_grupo_id === 26 || // televentas-cajero
                        (int)$regprem_grupo_id === 31 || // televentas-supervisor
                        (int)$regprem_cargo_id === 16 || // jefe
                        (int)$regprem_area_id === 6 ) { // sistemas
                ?>
                    <button id="btnSavePrint_sorteotls" class="btnSavePrint_sorteotls btn btn-info mgR" type="button" name="button"
                        data-token="<?php print_r($login['sesion_cookie']); ?>" style="display:none;">
                        Enviar al Billetero
                        <i class="fa fa-paper-plane" aria-hidden="true"></i>
                    </button>
                <?php 
                    }
                    if( ((int)$regprem_grupo_id !== 26 && // televentas-cajero
                        (int)$regprem_grupo_id !== 31 ) || // televentas-supervisor
                        (int)$regprem_cargo_id === 16 || // jefe
                        (int)$regprem_area_id === 6 ) { // sistemas
                ?>
                <button id="btnSavePrint" class="btnSavePrint btn btn-info mgR" type="button" name="button"
                    data-token="<?php print_r($login['sesion_cookie']); ?>" disabled>
                    Guardar - Imprimir
                    <i class="fa fa-print" aria-hidden="true"></i>
                </button>
                <?php 
                    }
                ?>
                <button id="btnSaveTeleservicios" class="btnSaveTeleservicios btn btn-info mgR" type="button" name="button"
                    data-token="<?php print_r($login['sesion_cookie']); ?>">
                    Guardar
                    <i class="fa fa-save" aria-hidden="true"></i>
                </button>
                <button id="btnClean" class="btn btn-warning" type="button" name="button">Limpiar</button>
            </div>
            <input type="hidden" id="winner_id">
            <input type="hidden" id="local_id">
            <input type="hidden" id="prize_amount">
            <input type="hidden" id="regpre_num_doc">
            <input type="hidden" id="regpre_tipo_doc">

        </form>
    </div>



    <!-- PASAPORTE -->
    <img style="display: none; border: 1px solid black;" id="imgFirmaRecurso" src="" alt="">

    <div class="listado tbl_registro_premios mt-5">
        <table id="listadoTable" class="tablaRegJackpot table table-bordered" style="margin-top: 2em">
            <thead>
            <tr>
                <td style=" height: 16px;">Nro de Ticket</td>
                <td style=" height: 16px;">Tipo premio</td>
                <td style=" height: 16px;">Nombre</td>
                <td style=" height: 16px;">Fecha</td>
                <td style=" height: 16px;">Monto Apostado</td>
                <td style=" height: 16px;">Monto Entregado</td>
                <td style=" height: 16px;">Doc. de Identidad</td>
                <td style=" height: 16px;">usuario</td>
                <td style=" height: 16px;">Fotos de Marketing</td>
                <td style=" height: 16px;">Fotos de ID</td>
                <td style=" height: 16px;">Fotos de Comprobante</td>
                <td style=" height: 16px;font-size: 18px; text-align:center"><i class="fa fa-print"
                                                                                aria-hidden="true"></i></td>
                <td style=" height: 16px;font-size: 18px; text-align:center; display: none"><i
                            class="fa fa-pencil-square-o" aria-hidden="true"></i></td>
            </tr>
            </thead>
            <tbody class="actTableBody">

            </tbody>
        </table>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="ticketModal" role="dialog" aria-labelledby="exampleModalLongTitle" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLongTitle"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body fondoTicket">

                    <div id="ticketPrint" class="ticket">
                        <div class="cabecera">
                            <h4>GESTION <br/> APUESTA TOTAL</h4>
                            <h6>Registro de Premios</h6>
                            <p><strong>Nro de Ticket: <span id="mNroTicket"></span></strong></p>
                            <div class="date"><p><strong>Fecha: </strong> <span id="mfecha"></span></p></div>

                            <p class="pdate"><strong>Local:</strong> <span id="mLocal"></span></p>

                            <table class="datos">
                                <tr>
                                    <th><strong id="tipoDocHtml">DNI: </strong></th>
                                    <td><span id="mDni"></span></td>
                                </tr>
                                <tr>
                                    <th><strong>Nombres:</strong></th>
                                    <td id="mNombres"></td>
                                </tr>
                                <tr>
                                    <th><strong>Apellidos:</strong></th>
                                    <td id="mApellidos"></td>
                                </tr>
                                <tr class="monto">
                                    <th><strong>Monto:</strong></th>
                                    <td><span>s/</span> <span id="mMonto"></span></td>
                                </tr>

                            </table>
                        </div>
                        <div id="autorizacion">
                            <p style="margin-left:1rem;margin-top:1rem;"><strong>AUTORIZACIÓN</strong></p>
                            <p id="textoLegal"><?php echo trim($jackpot_pago_texto); ?></p>
                            <p id="textoLegalMarketing"><?php echo trim($jackpot_pago_cliente_marketing); ?></p>
                            <p id="textoLegalClienteDB"><?php echo trim($jackpot_pago_cliente_db); ?></p>
                            <p id="textoLegalSorteo"><?php echo trim($registro_premios_sorteo); ?></p>
                        </div>

                        <div class="firmaTicket">
                            Firma:
                            <hr/>
                        </div>
                        <div style="margin-left: 1rem; display: none" class="dniTicket">
                            DNI:
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary cerrrar" data-dismiss="modal">Cerrar</button>
                    <button id="imprimir" type="button" class="imprimirbtn btn btn-primary" autofocus>Imprimir</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="fotoModal" role="dialog" aria-labelledby="foto_Modal" aria-hidden="true"
         data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="foto_Modal"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body subirFoto">
                    <button class="btn btn-primary" type="button" name="showQR" id="showQR" data-id="" data-cantidad=""
                            data-token="<?php print_r($login['sesion_cookie']); ?>">Agregar desde el Movil
                    </button>
                    <div class="showQR">
                        <p>Escanea con tu movil el siguiente codigo QR, inicia sesion y adjunta las imagenes</p>
                        <div width="80px" id="qrcode"></div>
                    </div>

                    <form id="formUpload" class="formUpload" action="sys/set_registro_fotos_jackpot.php" method="post"
                          enctype="multipart/form-data" data-type="">
                        <input type="hidden" id="id-Jackpot" name="id-Jackpot" value="">
                        <div class="row">

                            <div class="imgFoto col-lg-12 no-pad">
                                <span id="maxi"></span>
                                <img id="previewImg" src="images/default_avatar.png" alt="">
                            </div>
                            <div id="miniatura" class="miniaturas col-lg-6 col-lg-offset-3 no-pad">
                                <span id="minions"></span>
                            </div>
                        </div>

                        <div class="uploads">
                            <input data-cant="{count} archivos seleccionados" class="inputfileDesk" id="imgInp"
                                   type="file" name="files[]" accept=".jpeg,.png,.jpg" multiple="multiple" value="">
                            <label class="labelbtn" for="imgInp"><i class="fa fa-picture-o" aria-hidden="true"></i>
                                <span id="leyenda">Elegir imagenes</span> </label>
                        </div>
                        <br>
                        <button id="resette" style="display:none;" type="reset" name="reset">reset</button>
                        <button style="display: none" class="uploadInput" type="submit" name="button" disabled="true"><i
                                    class="fa fa-cloud-upload" aria-hidden="true"></i> Subir Imagenes
                        </button>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close but" data-dismiss="modal">cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="firmaModal" role="dialog" aria-labelledby="firma_Modal" aria-hidden="true"
         data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body subirFirma">
                    <p>Escanea con tu movil el siguiente codigo QR</p>
                    <div class="showQRfirma">
                        <div width="80px" id="qrcodeFirma"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close but" data-dismiss="modal">cerrar</button>
                </div>
            </div>
        </div>
    </div>


</div>
<!-- tab para jackpot -->
