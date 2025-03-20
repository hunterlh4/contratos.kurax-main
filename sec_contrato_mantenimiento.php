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
                    Mantenimiento </div>

            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-10 tab-contrato">
            <div class="mant_tipos" style="margin-top: 1%;">
                <?php include('sec_contrato_mantenimiento_tipo.php')?>
            </div>
            <div class="mant_responsable_area">
                <?php include('sec_contrato_responsables_de_area.php')?>
            </div>
            <div class="mant_directores_area">
                <?php include('sec_contrato_directores_de_area.php')?>
            </div>
            <div class="mant_correo_metodo">
                <?php include('sec_contrato_mantenimiento_correo_metodo.php')?>
            </div>
            <div class="mant_notificacion_contrato">
                <?php include('sec_contrato_mantenimiento_notificacion_contrato.php')?>
            </div>
            <div class="mant_correo_cargo">
                <?php include('sec_contrato_mantenimiento_correo_cargo.php')?>
            </div>
            <div class="mant_correlativo">
                <?php include('sec_contrato_mantenimiento_correlativo.php')?>
            </div>
            <div class="mant_cambio_tipo_contrato">
                <?php include('sec_contrato_mantenimiento_cambio_tipo_contrato.php')?>
            </div>
            <div class="mant_servicio_publico">
                <?php include('sec_contrato_mantenimiento_servicio_publico.php')?>
            </div>
         
        </div>
        <div class="col-md-2">
            <ul id="tab-contrato" class="nav nav-tabs tabs-right local_tabs">
                <li class="activo"><a class="tab_btn" href="#mant_tipos" data-tab="mant_tipos">Tipos</a></li>
                <li><a class="tab_btn" href="#mant_responsable_area" data-tab="mant_responsable_area">Responsables de Área</a></li>
                <li><a class="tab_btn" href="#mant_directores_area" data-tab="mant_directores_area">Directores de Área</a></li>
                <li><a class="tab_btn" href="#mant_correo_metodo" data-tab="mant_correo_metodo">Correos</a></li>
                <li><a class="tab_btn" href="#mant_notificacion_contrato" data-tab="mant_notificacion_contrato">Notificaciones de Contratos</a></li>
                <li><a class="tab_btn" href="#mant_correo_cargo" data-tab="mant_correo_cargo">Alerta de Correos por Cargo</a></li>
                <?php
                if (array_key_exists($menu_id, $usuario_permisos) && in_array("correlativo_contrato", $usuario_permisos[$menu_id])) {
                ?>
                <li><a class="tab_btn" href="#mant_correlativo" data-tab="mant_correlativo">Correlativo</a></li>
                <?php 
                }
                if (array_key_exists($menu_id, $usuario_permisos) && in_array("cambiar_tipo_contrato", $usuario_permisos[$menu_id])) {
                ?>
                <li><a class="tab_btn" href="#mant_cambio_tipo_contrato" data-tab="mant_cambio_tipo_contrato">Cambiar Tipo de Contrato</a></li>
                <?php 
                }
                if (array_key_exists($menu_id, $usuario_permisos) && in_array("servicio_publico", $usuario_permisos[$menu_id])) {
                ?>
                <li><a class="tab_btn" href="#mant_servicio_publico" data-tab="mant_servicio_publico">Servicio Público</a></li>
                <?php 
                }
                ?>
            </ul>
        </div>
    </div>



</div>


<?php include('sec_contrato_mantenimiento_correo.php')?>
<?php include('sec_contrato_mantenimiento_cargo.php')?>
<?php 
}
?>