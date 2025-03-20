<?php

$usuario_id = $login?$login['id']:null;

$menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '$sec_id' AND sub_sec_id = 'mesa_partes' LIMIT 1")->fetch_assoc();
$menu_caja_chica = $menu_id_consultar["id"];

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

<div class="content container-fluid vista_anuncios_anuncios">
    <div class="page-header wide" style="margin-bottom: 10px;">
        <div class="row">
            <div class="col-xs-12 text-center">
                <h1 class="page-title titulosec_contrato"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Solicitudes de Liquidación</h1>
            </div>
        </div>
    </div>

    <div class="col-md-12" style="margin-bottom: 10px;">
        <a class="btn btn-primary btn-sm" id="btnRegresar" href="./?sec_id=<?php echo $sec_id;?>&amp;sub_sec_id=mesa_partes">
            <i class="glyphicon glyphicon-arrow-left"></i>
            Regresar
        </a>
    </div>

    <?php
    if(array_key_exists($menu_caja_chica,$usuario_permisos) && in_array("AtenderLiquidacionJF", $usuario_permisos[$menu_caja_chica]))
    {
        ?>
        <div class="col-xs-12 col-md-12 col-lg-12 mt-3" id="">

            <div class="page-header wide">
                <fieldset class="dhhBorder">
                    <legend class="dhhBorder">Búsqueda</legend>
                    <form>
                        <div class="col-xs-12 col-sm-6 col-md-2 col-lg-3">
                            <label>Situación:</label>
                            <select
                                class="form-control input_text sec_mepa_atencion_liquidacion_select_filtro"
                                data-live-search="true"
                                name="sec_mepa_atencion_liquidacion_param_situacion" 
                                id="sec_mepa_atencion_liquidacion_param_situacion" 
                                title="Seleccione">
                                <option value="0">-- Seleccione --</option>
                                <?php
                                $sel_query = $mysqli->query(
                                    "
                                        SELECT
                                            etapa_id, situacion
                                        FROM cont_etapa cpb
                                        WHERE etapa_id IN (1, 6, 7, 12, 13)
                                    ");
                                while($sel=$sel_query->fetch_assoc()){
                                ?>
                                    <option value="<?php echo $sel["etapa_id"];?>">
                                        <?php echo $sel["situacion"]; ?>
                                    </option>
                                <?php
                                }
                                ?>
                                <option value="_00">Atender solicitud Dar de Baja</option>
                            </select>
                        </div>

                        <div class="col-xs-12 col-sm-6 col-md-2 col-lg-3">
                            <button 
                                type="button" 
                                name="" 
                                value="1" 
                                class="btn btn-success btn-block btn-sm" 
                                data-button="request" 
                                data-toggle="tooltip" 
                                data-placement="top" 
                                title="Buscar" 
                                onclick="sec_mepa_atencion_liquidacion_listar_liquidacion();"
                                style="position: relative; bottom: -19px; margin-bottom: 30px;">
                                <i class="glyphicon glyphicon-search"></i>
                                Buscar
                            </button>
                        </div>
                    </form>
                </fieldset>
            </div>

            <div class="row mt-3" id="sec_mepa_atencion_liquidacion_div_pendiente" style="display: none;">

                 <form id="mepa_solicitudes_atencion_liquidacion_jefe_form" method="POST" enctype="multipart/form-data">
                    
                    <div class="col-md-12" style="margin-bottom: 10px; text-align: right;">
                        <button type="button" class="btn btn-success btn-xs sec_mepa_atencion_liquidacion_check_guardar_aprobar_pendiente" id=""
                            onclick="sec_mepa_solicitud_atencion_liquidacion_jefe_check_aprobar_todos();"
                            >
                            <i class="fa fa-plus"></i>
                            Seleccionar Todos
                        </button>
                    </div>
                    <div class="col-md-12 table-responsive">
                    <table id="sec_mepa_atencion_liquidacion_div_pendiente_datatable" class="table table-bordered table-responsive table-hover dt-responsive" cellspacing="0" width="100%" style="">
                        <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-center">ID</th>
                                <th class="text-center">EMPRESA</th>
                                <th class="text-center">ZONA</th>
                                <th class="text-center">Usuario solicitante</th>
                                <th class="text-center">Nº correlativo</th>
                                <th class="text-center">Estado solicitud</th>
                                <th class="text-center">F. Solicitud</th>
                                <th class="text-center">Ver</th>
                                <th class="text-center">Aprobar</th>
                            </tr>
                        </thead>
                        <tbody 
                            id="sec_mepa_atencion_liquidacion_div_pendiente_datatable_body">
                        </tbody>
                    </table>
                    </div>
                    <div class="row mt-2 mb-2" style="text-align: right;">
                        <button type="button" class="btn btn-success sec_mepa_atencion_liquidacion_check_guardar_aprobar_pendiente" title="Aprobar Solicitudes" 
                            onclick="sec_mepa_solicitud_atencion_liquidacion_jefe_check_guardar_solo_check();">
                            <i class="fa fa-save"></i>
                            Aprobar Solicitudes
                        </button>
                    </div>

                </form>
            </div>

            <div class="row mt-3" id="sec_mepa_atencion_liquidacion_div_diferente_pendiente" style="display: none;">

                <table id="sec_mepa_atencion_liquidacion_div_diferente_pendiente_datatable" class="table table-bordered table-hover dt-responsive" cellspacing="0" width="100%" style="">
                    <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-center">ID</th>
                            <th class="text-center">EMPRESA</th>
                            <th class="text-center">ZONA</th>
                            <th class="text-center">Usuario solicitante</th>
                            <th class="text-center">Nº correlativo</th>
                            <th class="text-center">Estado solicitud</th>
                            <th class="text-center">F. Solicitud</th>
                            <th class="text-center">Ver</th>
                        </tr>
                    </thead>
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

