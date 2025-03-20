<?php
//Busca el menu padre
$query_menu_sistemas = "SELECT
    a.id, a.titulo, b.id as id_relacionado, b.titulo as titulo_relacionado, b.relacion_id

    FROM tbl_menu_sistemas AS  a
    LEFT JOIN tbl_menu_sistemas AS b
    ON (b.relacion_id = a.id and b.estado = 1)

    WHERE a.estado = 1 AND a.relacion_id IS NULL OR a.relacion_id=0

    ORDER BY a.id";
$result_menu_sistemas = $mysqli->query($query_menu_sistemas);
?>

<tbody>
    <?php
    if ($tipo_menu == 'permisos'){
        $id_tipo_menu = 'p_';
    }
    else {
        $id_tipo_menu = '';
    }
    $b_row='a';
    foreach ($result_menu_sistemas as $row_menu) {
        if ($row_menu['id'] != $b_row) {
    ?>
    <tr>
        <td class="tbl_menu_sub_menu_botones_primer_td" colspan="2">
            <button type="button" class="parent_tbl_sub_menu_botones_padres" data-id="<?php echo $row_menu['id'] ?>" >
                <span class="icon expand-icon glyphicon tbl_menu_sub_menu_botones_icon_expand_collapse_abuelo glyphicon-plus"></span>
            </button>
            <span class="icon node-icon glyphicon glyphicon-list tbl_menu_sub_menu_botones_icon_lista_abuelo"></span>
            <span class="tbl_menu_sub_menu_botones_texto">
                [<?php echo $row_menu['id'] ?>] <?php echo $row_menu['titulo'] ?>
            </span>
        </td>
        <td class="tbl_menu_sub_menu_botones_ultimo_td" style="width:10px !important;">
            <div class="checkbox">
                <input type="checkbox"
                class="checkbox-warning tbl_menu_first_checkbox"
                data-menu="<?php echo $row_menu['id'] ?>"
                >
                <label></label>
            </div>
        </td>

    </tr>
    <tr class="tbl_menu_sub_menu_botones_padres_detalles_<?php echo $row_menu['id']; ?> rows_hidden_usuarios_permisos" >
        <td  class="tbl_menu_sub_menu_botones_primer_td"></td>
        <td class="tbl_menu_sub_menu_botones_ultimo_td" colspan="2">
            <table width="100%" class="tabla_menu_sub_menu_botones_checkbox_botones_padre">
                <tbody>
                    <?php
                    $query_botones ="SELECT boton,nombre FROM tbl_menu_sistemas_botones WHERE menu_id  ='".$row_menu['id']."'";
                    $result_btns = $mysqli->query($query_botones);
                    while($row_btns = $result_btns->fetch_assoc()) {
                    ?>
                    <tr>
                        <td class="tbl_menu_sub_menu_botones_botones_padre_td">
                            <span class="glyphicon glyphicon-th-large tbl_menu_sub_menu_botones_iconos"></span>
                            <span class="tbl_menu_sub_menu_botones_texto_botones_padre">
                                [<?php echo $row_btns['boton']; ?>] <?php echo $row_btns['nombre']; ?>
                            </span>
                        </td>
                        <td class="tbl_menu_sub_menu_botones_checbox" style="width:120px !important;">
                            <div class="checkbox tbl_menu_sub_menu_botones_checkbox_botones_padre">
                                <input type="checkbox"
                                class="select_permisos_checkbox checkbox-warning"
                                value="<?php echo $row_btns["boton"]; ?>"
                                name="data_menu_<?php echo $id_tipo_menu; echo $row_menu['id']; ?>_<?php echo $row_btns['boton']; ?>"/
                                data-menu-id="<?php echo $row_menu['id']; ?>"
                                data-menu-f="<?php echo $row_menu['id']; ?>"
                                data-menu-nombre="<?php echo $row_btns['nombre']; ?>"
                                >
                                <label></label>
                            </div>
                        </td>
                    </tr>
                    <?php
                    }
                    mysqli_free_result($result_btns);
                    ?>
                </tbody>
            </table>
        </td>
    </tr>
    <?php
    }
    if($row_menu['id'] == $row_menu['relacion_id']) {
    ?>
    <tr class="tbl_menu_sub_menu_botones_padres_detalles tbl_menu_sub_menu_botones_padres_detalles_<?php echo $row_menu['id']; ?> rows_hidden_usuarios_permisos">
        <td class="tbl_menu_sub_menu_botones_primer_td"></td>
        <td>
            <button type='button' class='parent_tbl_sub_menu_botones' data-id='<?php echo $row_menu["id_relacionado"]; ?>'>
                <span class="icon expand-icon glyphicon glyphicon-plus tbl_menu_sub_menu_botones_icon_expand_collapse_hijos"></span>
            </button>
            <span class="icon node-icon glyphicon glyphicon-list tbl_menu_sub_menu_botones_icon_lista_hijos"></span>
            <span class="tbl_menu_sub_menu_botones_texto">
                <?php echo "[".$row_menu['id_relacionado']."] ".$row_menu['titulo_relacionado']; ?>
            </span>
        </td>
        <td class="tbl_menu_sub_menu_botones_ultimo_td" style="width:70px !important;">
            <div class="checkbox">
                <input type="checkbox"
                class="checkbox-warning tbl_menu_second_checkbox"
                data-menu-first-id="<?php echo $row_menu['id'] ?>"
                data-menu-sub-id="<?php echo $row_menu['id_relacionado'] ?>"
                >
                <label></label>
            </div>
        </td>
    </tr>
    <tr class="tbl_menu_sub_menu_botones_detalles tbl_menu_sub_menu_botones_detalles_<?php echo $row_menu["id_relacionado"]; ?> rows_hidden_usuarios_permisos">
        <td  class="tbl_menu_sub_menu_botones_primer_td"></td>
        <td class="tbl_menu_sub_menu_botones_ultimo_td " colspan="2">
            <table class="tbl_sub_menu_botones" width="95%">
                <tbody>
                    <?php
                    $query_botones ="SELECT boton,nombre FROM tbl_menu_sistemas_botones WHERE menu_id  ='".$row_menu['id_relacionado']."'";
                    $result_btns = $mysqli->query($query_botones);
                    while($row_btns = $result_btns->fetch_assoc()) {
                    ?>
                    <tr class="tr_tbl_sub_menu_botones">
                        <td class="tbl_menu_sub_menu_botones_botones_hijos_td">
                            <span class="glyphicon glyphicon-th-large tbl_menu_sub_menu_botones_iconos_hijo"></span>
                            <span class="tbl_menu_sub_menu_botones_texto_botones_hijo">
                                [<?php echo $row_btns['boton']; ?>] <?php echo $row_btns['nombre']; ?>
                            </span>
                        </td>
                        <td class="td_menu_sub_menu_botones_checkbox_botones_hijo" style="width:100px !important;">
                        <div class="checkbox tbl_menu_sub_menu_botones_checkbox_botones_hijo">
                            <input type="checkbox"
                            class="select_permisos_checkbox checkbox-warning"
                            value="<?php echo $row_btns["boton"]; ?>"
                            name="data_menu_<?php echo $id_tipo_menu; echo $row_menu['id_relacionado']; ?>_<?php echo $row_btns['boton']; ?>"/
                            data-menu-id="<?php echo $row_menu['id_relacionado']; ?>"
                            data-menu-f="<?php echo $row_menu['id']; ?>"
                            data-menu-nombre="<?php echo $row_btns['nombre']; ?>">
                            <label></label>
                        </div>
                    </td>
                </tr>
                <?php
                }
                mysqli_free_result($result_btns);
                ?>
                </tbody>
            </table>
        </td>
    </tr>
    <?php
    }
    $b_row = $row_menu['id'];
}
?>
</tbody>