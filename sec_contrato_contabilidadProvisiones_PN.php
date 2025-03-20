<?php
global $mysqli;
$menu_id = "";
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'contrato' AND sub_sec_id = 'nuevo' LIMIT 1");
while ($r = $result->fetch_assoc())
    $menu_id = $r["id"];
$usuario_id = $login ? $login['id'] : null;
?>
<style>
/* .panel {
        margin-bottom: 5px !important;
    } */
fieldset.dhhBorder {
    border: 1px solid #ddd;
    padding: 0 15px 5px 15px;
    margin: 0 0 10px 0;
    box-shadow: 0px 0px 0px 0px #000;
    border-radius: 5px;
}

legend.dhhBorder {
    font-size: 14px;
    text-align: left;
    width: auto;
    padding: 0 10px;
    border-bottom: none;
    margin-bottom: 10px;
    text-transform: capitalize;
}

.table-comprimido>tbody>tr>th,
.table-comprimido>tfoot>tr>th,
.table-comprimido>thead>tr>th {
    padding: 4px;
    padding-left: 8px;
}

.table-comprimido>tbody>tr>td,
.table-comprimido>tfoot>tr>td,
.table-comprimido>thead>tr>td {
    padding: 0px;
    padding-left: 8px;
}

.btn {
    margin-right: 10px;
}
</style>
<!--  Inicio librerias -->
<script src="https://unpkg.com/vue@3.2.6/dist/vue.global.prod.js"></script>
<script src="https://unpkg.com/vuex@4.0.2/dist/vuex.global.prod.js"></script>
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>


<!--  Fin librerias -->

<div class="content container-fluid">
    <div class="page-header wide">
        <div class="row">
            <div class="col-xs-12 text-center">
                <h1 class="page-title titulosec_contrato"><i class="icon icon-inline fa fa-fw fa-money"></i>
                    Persona Natural
                </h1>
            </div>
        </div>
    </div>


    <div class="row mt-2 mb-2">
        <div class="page-header wide">
            <fieldset class="dhhBorder">
                <legend class="dhhBorder">Provisiones Persona Natural</legend>
                <form>
                    <input type="hidden" id="programacion_id_edit" name="programacion_id_edit" value="">
                    <div class="col-xs-12 col-sm-6 col-md-2 col-lg-2">
                        <label>Situación</label>
                        <select class="select2" name="" id="tipo_provisiones" onchange="obtener_provisiones_actuales()">
                            <option value="0">Provisiones Sin IPC</option>
                            <option value="1">Provisiones Con IPC</option>
                        </select>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-2 col-lg-2">
                        <label for="fecha_inicio_a_provisionar">Seleccione periodo:</label>
                        <select class="select2" name="" id="fecha_inicio_a_provisionar">
                            <option value="2023-03">2023-03</option>
                            <option value="2023-04">2023-04</option>
                            <option value="2023-05">2023-05</option>
                            <option value="2023-06">2023-06</option>
                            <option value="2023-07">2023-07</option>
                            <option value="2023-08">2023-08</option>
                            <option value="2023-09">2023-09</option>
                            <option value="2023-10">2023-10</option>
                            <option value="2023-11">2023-11</option>
                            <option value="2023-12" selected>2023-12</option>
                        </select>
                    </div>
                    
                    <div class="col-xs-12 col-sm-6 col-md-2 col-lg-2">
                        <button class="btn btn-success btn-block btn-sm" type="button" name="" id=""
                            onclick="obtener_provisiones_actuales()" value="1" data-button="request"
                            style="position: relative; bottom: -19px; margin-bottom: 30px;"
                            data-toggle="tooltip" data-placement="top" title="Obtener provisiones">
                            <i class="glyphicon glyphicon-search"></i>
                            Obtener provisiones
                        </button>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-2 col-lg-2" style="text-align: right;">
                        <button id="enviar_tesoreria_button" class="btn btn-info btn-block btn-sm" type="button" 
                        style="position: relative; bottom: -19px; margin-bottom: 30px;"
                            onclick="enviar_tesoreria_provisiones()">
                            <span class="glyphicon glyphicon-send"></span> Enviar a tesoreria
                        </button>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-2 col-lg-2">
                        <button class="btn btn-info btn-block btn-sm" type="button" 
                        style="position: relative; bottom: -19px; margin-bottom: 30px;"
                            onclick="exportar_excel_calculo_provisiones()">
                            <span class="fa fa-file-excel-o"></span> Exportar a excel
                        </button>
                    </div>
                    <div class="col-xs-12 col-sm-6 col-md-2 col-lg-2" >
                        <button class="btn btn-info btn-block btn-sm" type="button" 
                        style="position: relative; bottom: -19px; margin-bottom: 30px;"
                            onclick="exportar_plantilla_contable()">
                            <span class="fa fa-file-excel-o"></span> Exportar plantilla
                        </button>
                    </div>
                   

                    

                </form>
            </fieldset>
        </div>
    </div>
    <div class="row mt-3 mb-2">
        <div class="table-responsive" id="div_acreedores_pendiente_pago">
            <table id="tbl_datos_provisiones"
                class="table table-striped table-hover table-condensed table-bordered dt-responsive" cellspacing="0"
                width="100%">
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


<!-- Vuex -->


<!-- App -->
<?php
?>
<!-- componets -->


<script>
// cargar_tabla_provisiones_contables();
</script>