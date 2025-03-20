<?php
global $mysqli;
$menu_id = "";
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'vale' AND sub_sec_id = 'mantenimiento' LIMIT 1");
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
    </style>

    <div id="div_sec_vale_mantenimiento"></div>

    <div id="loader_"></div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 text-center">
            <h1 class="page-title titulosec_contrato"><i class="icon icon-inline glyphicon glyphicon-briefcase"></i> Vales de Descuento - Mantenimiento</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-md-12 col-lg-12">


            <div class="bs-example bs-example-tabs" data-example-id="togglable-tabs">

                <div class="col-md-2">
                    <ul class="nav nav-tabs tabs-right " id="TabsMantenimientoVale" role="tablist">
                        <li role="presentation" class="active"><a href="#motivo" id="motivo-tab" role="tab" data-toggle="tab" aria-controls="motivo" aria-expanded="true">Motivos</a></li>
                        <li role="presentation" class=""><a href="#parametros-fraccionamiento" role="tab" id="parametros-fraccionamiento-tab" data-toggle="tab" aria-controls="parametros-fraccionamiento" aria-expanded="false">Parametros de Fraccionamiento</a></li>
                        <li role="presentation" class=""><a href="#parametro-general" role="tab" id="parametro-general-tab" data-toggle="tab" aria-controls="parametro-general" aria-expanded="false">Monto de Fraccionamiento</a></li>
                    
                    </ul>
                </div>

                <div class="col-md-10" class="panel">
                    <div class="tab-content" id="myTabContent">
                        <div class="tab-pane fade active in" role="tabpanel" id="motivo" aria-labelledby="motivo-tab">
                            <?php include("sec_vale_motivo.php") ?>
                        </div>
                        <div class="tab-pane fade" role="tabpanel" id="parametros-fraccionamiento" aria-labelledby="parametros-fraccionamiento-tab">
                            <?php include("sec_vale_parametros_fraccionamiento.php") ?>
                        </div>
                        <div class="tab-pane fade" role="tabpanel" id="parametro-general" aria-labelledby="parametro-general-tab">
                            <?php include("sec_vale_parametro_general.php") ?>
                        </div>
                    </div>
                </div>



            </div>

        </div>





    </div>
    </div>



<?php } ?>