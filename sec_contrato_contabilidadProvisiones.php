<?php
global $mysqli;
$menu_id = "";
$result = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'contrato' AND sub_sec_id = 'mantenimiento' LIMIT 1");
while ($r = $result->fetch_assoc())
	$menu_id = $r["id"];

if (!array_key_exists($menu_id, $usuario_permisos) || !in_array("view", $usuario_permisos[$menu_id])) {
	echo "No tienes permisos para acceder a este recurso";
} else {

$usuario_id = $login?$login['id']:null;

?>

<div class="content container-fluid content_locales">
    <div class="page-header wide">
        <div class="row">
            <div class="col-xs-12 text-center">
                <div class="page-title"><i class="icon icon-inline fa fa-fw fa-building"></i>
                    Cálculo de provisiones </div>

            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12 tab-provisiones">
            <div class="persona_natural" style="margin-top: 1%;">
                <?php include('sec_contrato_contabilidadProvisiones_PN.php')?>
            </div>
            <div class="persona_juridica">
                <?php include('sec_contrato_contabilidadProvisiones_PJ.php')?>
            </div>
         

        </div>
        <!-- <div class="col-md-2">
            <ul id="tab-provisiones" class="nav nav-tabs tabs-right local_tabs">
                <li class="activo"><a class="tab_btn" href="#persona_natural" data-tab="persona_natural">Persona Natural</a></li>
                <li><a class="tab_btn" href="#persona_juridica" data-tab="persona_juridica">Persona Jurídica</a></li>

            </ul>
        </div> -->
    </div>



</div>

<?php 

}

?>