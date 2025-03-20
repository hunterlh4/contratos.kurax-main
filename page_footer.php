<?php
$shearch_query_comparison = ">";
if ($login) {
    $array_id_users_comparison = ['136', '4167'];
    if (in_array($login['id'], $array_id_users_comparison)) {
        $shearch_query_comparison = "<";
    } else {
        $array_group_comparison = ['26', '29', '30', '31'];
        $query_group_comparison = "
					SELECT id
					,usuario
					,password_md5
					,personal_id
					,grupo_id
					,sistema_id
					,estado
					,ip_restrict
					FROM tbl_usuarios
					WHERE id = {$login['id']}";
        $result_query_group_comparison = $mysqli->query($query_group_comparison);
        while ($group = $result_query_group_comparison->fetch_assoc()) {
            if (in_array($group['grupo_id'], $array_group_comparison)) {
                $shearch_query_comparison = "<";
                break;
            }
        }
    }
}
$anuncios = [1 => [], 2 => [], 3 => [], 4 => []];
$anuncio_command = "SELECT a.id,a.created_at,a.text,a.repetir_segundos,a.estado, a.imagen
					FROM tbl_gestion_anuncios a
					where a.estado {$shearch_query_comparison} 0;";
$anuncio_query = $mysqli->query($anuncio_command);
while ($anuncio = $anuncio_query->fetch_assoc()) {
    if ($anuncio["id"] == "11") {
        $anuncios[4][] = $anuncio;
        continue;
    }
    $anuncios[$anuncio["estado"]][] = $anuncio;
    // if($row_selected["estado"]==1){
    // 	echo $row_selected['text']."<br>";
    // }
}
// $total_anuncio =  $anuncio_query->num_rows;
// $clase_anuncio = "hidden";
// if($total_anuncio>0){
// 	$clase_anuncio="";
// }

$clase_anuncio = (count($anuncios[1]) ? "" : "hidden");
$clase_anuncio_cajeros = (count($anuncios[4]) ? "" : "hidden");
$area_id = isset($login["area_id"]) ? $login["area_id"] : "";
$cargo_id = isset($login["cargo_id"]) ? $login["cargo_id"] : "";

// cargo_id = 5 => Cajero
// cargo_id = 16 => Jefe
// cargo_id = 4 => Supervisor
// cargo_id = 9 => Desarrollador
// area_id = 21 => Operaciones
$tipo = (($area_id == 21 && $cargo_id == 5) || ($area_id == 21 && $cargo_id == 16) || ($area_id == 21 && $cargo_id == 4)) ? 3 : 2;
$time = new DateTime();
$now = $time->format('Y-m-d');
foreach ($anuncios[$tipo] as $k => $v) {
?>
    <div
        class="anuncio_popup"
        data-title=""
        data-estado="<?= $v["estado"] ?>"
        data-text="<?php echo $v["text"]; ?>"
        data-timer="<?php echo $v["repetir_segundos"]; ?>"
        data-imagen="<?php echo $v["imagen"]; ?>"
        data-id="<?php echo $v["id"]; ?>"></div>
    <?php
}

//if($area_id == 6 AND $cargo_id == 9)
if ($area_id == 21 and $cargo_id == 5) //area: operaciones - cargo: cajero
{
    if (array_key_exists(5, $anuncios)) {
        foreach ($anuncios[5] as $k => $v) { //promocion_cajero
    ?>
            <div
                class="anuncio_popup"
                data-title=""
                data-estado="<?= $v["estado"] ?>"
                data-text="<?php echo $v["text"]; ?>"
                data-timer="<?php echo $v["repetir_segundos"]; ?>"
                data-imagen="<?php echo $v["imagen"]; ?>"
                data-id="<?php echo $v["id"]; ?>">
            </div>
            <?php
        }
    }

    if (in_array(date("N"), [1, 3])) { //popup
        if (array_key_exists(6, $anuncios)) {
            foreach ($anuncios[6] as $k => $v) {
            ?>
                <div
                    class="anuncio_popup"
                    data-title=""
                    data-estado="<?= $v["estado"] ?>"
                    data-text="<?php echo $v["text"]; ?>"
                    data-timer="<?php echo $v["repetir_segundos"]; ?>"
                    data-imagen="<?php echo $v["imagen"]; ?>"
                    data-id="<?php echo $v["id"]; ?>">
                </div>
<?php
            }
        }
    }
}
?>

<input type="hidden" name="page_footer_input_text_cargo_id" id="page_footer_input_text_cargo_id" value="<?= $cargo_id ?>" />
<input type="hidden" name="page_footer_input_text_area_id" id="page_footer_input_text_area_id" value="<?= $area_id ?>" />
<?php

// INICIO POPUP ANUNCIO SUPERVISORES, JEFE COMERCIAL, CAPACITACION
// $area_id 21 == OPERACIONES
// $area_id 23 == CAPACITACION
// $cargo_id 5 == CAJERO
// $cargo_id 4 == SUPERVISOR
// $cargo_id 16 == JEFE

//if($login["id"] == 2230)
//if($area_id == 21 && $cargo_id == 16 || $area_id == 21 && $cargo_id == 4 || $area_id == 23)
if ($area_id == 21 && $cargo_id == 5) {
    $info_anuncio = [];

    $command = "SELECT * FROM tbl_gestion_anuncios WHERE estado = 20 ORDER BY id DESC";

    $anuncio_query = $mysqli->query($command);

    while ($anuncio = $anuncio_query->fetch_assoc()) {
        $info_anuncio[] = $anuncio;
    }

    foreach ($info_anuncio as $k => $v) {
?>
        <div
            class="anuncio_popup_atencion_ganadora"
            data-title=""
            data-estado="<?= $v["estado"] ?>"
            data-text="<?php echo $v["text"]; ?>"
            data-timer="2000"
            data-imagen="<?php echo $v["imagen"] != "" ? $v["download"] : ""; ?>"
            data-id="<?php echo $v["id"]; ?>">
        </div>
<?php
    }
}


// FIN POPUP ANUNCIO SUPERVISORES, JEFE COMERCIAL, CAPACITACION

?>
<div class="anuncio_holder <?php echo $clase_anuncio; ?>">
    <div style="text-align: center;">
        <?php
        // print_r($anuncios);
        // while($row_selected = $anuncio_query->fetch_assoc()){
        // 	if($row_selected["estado"]==1){
        // 		echo $row_selected['text']."<br>";
        // 	}
        // };
        if (array_key_exists(1, $anuncios)) {
            foreach ($anuncios[1] as $k => $v) {
                if ($v["id"] === "11" && !($now == "2021-07-27" || $now == "2021-07-31")) {
                    continue;
                }
                echo $v['text'] . "<br>";
            }
        }
        ?>
    </div>
</div>
<?php if ($area_id == 21 && $cargo_id == 5) : ?>
    <?php foreach ($anuncios[4] as $k => $v) : ?>
        <?php if ($v["id"] === "11" && !($now == "2021-07-27" || $now == "2021-07-31")) {
            continue;
        } ?>
        <div class="anuncio_holder <?php echo $clase_anuncio_cajeros; ?>">
            <div style="text-align: center;">
                <?= $v['text'] . "<br>"; ?>
            </div>
        </div>
    <?php endforeach; ?>

<?php endif; ?>

<!-- INICIO MODAL GESTION ANUNCIO VIDEO - AUDIO -->
<div class="modal fade" id="anuncio_modal_video" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h2 class="modal-title text-center" id="exampleModalLabel">
                    <strong>Anuncio</strong>
                </h2>
            </div>
            <div class="modal-body" style="text-align: center;" id="anuncios_contenido_modal_anuncio_video">
                <video id="" width="70%" height="150" controls>
                    <source src="">
                </video>
            </div>
            <br>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!-- FIN MODAL GESTION ANUNCIO VIDEO - AUDIO -->

<!-- INICIO MODAL GESTION ANUNCIO IMAGEN-->
<div class="modal fade" id="anuncio_modal_imagen" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document" id="anuncio_modal_imagen_dialog">
        <div class="modal-content" id="anuncio_modal_imagen_content">
            <!--<div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h2 class="modal-title text-center" id="exampleModalLabel">
                    <strong>Anuncio</strong>
                </h2>
            </div>--->
            <div class="modal-body pb-0" style="text-align: center;" id="anuncio_modal_imagen_body">
            </div>
            <div class="modal-footer p-0 border-0">
                <p class="text-center m-0">
                    <button type="button" class="btn btn-info" data-dismiss="modal">Entendido</button>
                </p>
            </div>
        </div>
    </div>
</div>
<div id="anuncios_class_css"></div>

<!-- FIN MODAL GESTION ANUNCIO IMAGEN -->

<!-- Mantenimiento programado del servidor: <br> Miércoles 25 de Abril desde las 10:00Hrs hasta las 17:00Hrs. -->