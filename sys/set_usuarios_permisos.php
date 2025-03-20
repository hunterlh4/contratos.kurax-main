<?php
include("global_config.php");
include("db_connect.php");
include('sys_login.php');
require('/var/www/html/sys/globalFunctions/templates/crud.php');

//Se obtienen los permisos de locales
if (isset($_POST["id_usuario"])) {
    $local_botones = array();

    //Buscamos los locales del usuario
    $query = "SELECT UL.local_id, L.red_id
    FROM tbl_usuarios_locales UL
    JOIN tbl_locales L
    ON UL.local_id = L.id
    WHERE UL.usuario_id = '" . $_POST["id_usuario"] . "' AND UL.estado = '1'";
    $result = $mysqli->query($query);
    while ($row_locales = $result->fetch_assoc()) {
        $local_botones[] = $row_locales;
    }
    echo json_encode($local_botones, JSON_FORCE_OBJECT);
} else if (isset($_GET["action"])) {
    if ($_GET["action"] == "actualizar_locales") {
        $_POST['perms'] = json_decode($_POST['perms'][0], true);
        foreach ($_POST['perms'] as $perm) {
            try {
                $mysqli->query("START TRANSACTION");
                if ($perm["local_active"] == "1") {
                    $query = "INSERT INTO tbl_usuarios_locales (usuario_id, local_id, estado)
                    VALUES ('" . $_POST['id_user'] . "', '" . $perm['local_btnId'] . "', '1')";
                    $result = $mysqli->query($query);
                } else if ($perm["local_active"] == "0") {
                    $query = "DELETE FROM tbl_usuarios_locales WHERE local_id = '" . $perm['local_btnId'] . "' AND usuario_id = '" . $_POST['id_user'] . "'";
                    $result = $mysqli->query($query);
                }
                $mysqli->query("COMMIT");
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
    } else if ($_GET["action"] == "cargar_permisos") {
        $result_final_permisos = array();
        $query = "SELECT a.menu_id,a.boton_id FROM tbl_permisos a LEFT JOIN tbl_menu_sistemas b on b.id = a.menu_id WHERE a.usuario_id='" . $_POST['id_usuario_permisos'] . "' and a.estado = '1' and b.estado = 1";
        $result = $mysqli->query($query);
        while ($row_permisos = $result->fetch_assoc()) {
            $result_final_permisos[] = $row_permisos;
        }
        echo json_encode($result_final_permisos, JSON_FORCE_OBJECT);
    } else if ($_GET["action"] == "actualizar_permisos") {
        $_POST['perms'] = $_POST['perms'][0];
        foreach ($_POST['perms'] as $perm) {
            try {
                $mysqli->query("START TRANSACTION");
                if ($perm["active"] == "1") {
                    $sql = "select id from tbl_permisos 
                        where usuario_id=" . $_POST['id_user'] . " and menu_id=" . $perm['menuId'] . " and boton_id=" . $perm['btnId'] . " 
                        and boton_nombre='" . $perm['nombre_permiso'] . "' and estado=0
                        limit 1";
                    $result_existente = $mysqli->query($sql);
                    $fila = $result_existente->fetch_assoc();
                    $id_existente =  isset($fila['id']) ? $fila['id'] : null;
                    if ($id_existente) {
                        $sql_existente = "update tbl_permisos  set estado=1 where id= " . $id_existente;
                        $result = $mysqli->query($sql_existente);
                    } else {
                        //en caso de ser un registro nuevo : INSERT
                        $query = "INSERT INTO tbl_permisos (usuario_id, menu_id, boton_id, boton_nombre, estado)
                            VALUES ('" . $_POST['id_user'] . "', '" . $perm['menuId'] . "', '" . $perm['btnId'] . "', '" . $perm['nombre_permiso'] . "', '1')";
                        $result = $mysqli->query($query);
                    }
                } else if ($perm["active"] == "0") {
                    //$query = "DELETE FROM tbl_permisos WHERE usuario_id = '".$_POST['id_user']."' AND menu_id = '".$perm['menuId']."' AND boton_id = '".$perm['btnId']."'";
                    $query = "UPDATE tbl_permisos set estado=0 WHERE usuario_id = '" . $_POST['id_user'] . "' AND menu_id = '" . $perm['menuId'] . "' AND boton_id = '" . $perm['btnId'] . "'";
                    $result = $mysqli->query($query);
                }
                // echo   $query;//$auditoria_permiso['error'];
                // 		exit();

                //Auditoria
                if ($result) {

                    $menu_descripcion = '';
                    $menu_query = "SELECT * FROM tbl_menu_sistemas WHERE id = " . $perm['menuId'];
                    $menu_r = $mysqli->query($menu_query)->fetch_assoc();
                    $menu = $menu_r['titulo'];

                    $menu_padre = '';
                    if (!empty($menu_r['relacion_id'])) {
                        $menu_padre_query = "SELECT * FROM tbl_menu_sistemas WHERE id = " . $menu_r['relacion_id'];
                        $menu_padre = $mysqli->query($menu_padre_query)->fetch_assoc();

                        $menu_descripcion .= $menu_padre['titulo'] . ' > ';
                    }

                    $menu_descripcion .= $menu;
                    // $query = "INSERT INTO tbl_permisos_auditoria(usuario_id, boton_id, created_at, accion, menu_id, menu_descripcion, boton_nombre,ip, permiso_updated_by)
                    //         VALUES ('".$_POST['id_user']."', '".$perm['btnId']."', now(), ".$perm["active"].", '".$perm['menuId']."', '".$menu_descripcion."','".$perm['nombre_permiso']."', '".$login['login_ip']."',".$login['id'].")";
                    // $resultante = $mysqli->query($query);


                    $auditoria_permiso = insertTable('tbl_permisos_auditoria', [
                        'usuario_id' => $_POST['id_user'],
                        'boton_id' => $perm['btnId'],
                        'accion' => $perm["active"],
                        'menu_id' => $perm['menuId'],
                        'menu_descripcion' => $menu_descripcion,
                        'boton_nombre' => $perm['nombre_permiso'],
                        'permiso_updated_by' => $login['id'],
                        'ip' => $login['login_ip'],
                        'created_at' => date('Y-m-d H:i:s'),
                        'user_created_id' => $login['id']
                    ]);



                    if (!empty($auditoria_permiso['mysqli_error'])) {
                        echo json_encode($auditoria_permiso);
                        exit();
                    }
                }

                $mysqli->query("COMMIT");
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
    } else if ($_GET['action'] == "asignar_usuarios_redes") {
        $operacion = isset($_GET['operacion']) ? $_GET['operacion'] : "";
        $usuario_id = (int) (isset($_GET['usuario_id']) ? $_GET['usuario_id'] : 0);
        $red_id = (int)  (isset($_GET['red_id']) ? $_GET['red_id'] : 0);

        if ($operacion == "asignar") {
            if ($usuario_id != 0 && $red_id != 0) {
                $sql = "insert into tbl_usuarios_redes (usuario_id, red_id) values (" . $usuario_id . "," . $red_id . ")";
                $result = $mysqli->query($sql);
                $arr_response = array("msg" => "Se asigno usuario a red exitosamente");
            } else {
                $arr_response = array("msg" => "Red/usuario incorrecto.");
            }
        }
        if ($operacion == "quitar") {
            if ($usuario_id != 0 && $red_id != 0) {
                $sql = "update tbl_usuarios_redes set estado=0 where usuario_id=" . $usuario_id . " and red_id=" . $red_id;
                $result = $mysqli->query($sql);
                $arr_response = array("msg" => "Se quito usuario a red exitosamente");
            } else {
                $arr_response = array("msg" => "Red/usuario incorrecto.");
            }
        }
        echo json_encode($arr_response);
        exit();
    } else if ($_GET['action'] == "obtener_usuarios_redes") {
        $usuario_id = (int) (isset($_GET['usuario_id']) ? $_GET['usuario_id'] : 0);
        if ($usuario_id != 0) {
            $sql = "select * from tbl_usuarios_redes where estado=1 and usuario_id=" . $usuario_id;
            $result = $mysqli->query($sql);
            $resultado_usuarios_redes = array();
            while ($row_permisos = $result->fetch_assoc()) {
                $resultado_usuarios_redes[] = $row_permisos;
            }
            echo json_encode($resultado_usuarios_redes, JSON_FORCE_OBJECT);
        }
    }
} else {
    $query = "SELECT id,nombre FROM tbl_sistemas";
    $sql_query_sistemas = $mysqli->query($query);

    $query = "SELECT id, nombre FROM tbl_locales_redes";
    $query_locales_redes = $mysqli->query($query);

    // Para los modales de copiar
    $query = "SELECT
        u.id as id ,
        u.usuario as usuario,
        p.nombre as nombre,
        p.apellido_paterno as apellido_paterno,
        p.apellido_materno as apellido_materno,
        s.id as sistema_id,
        s.nombre as sistema 
        FROM tbl_usuarios u 
        INNER JOIN tbl_personal_apt p
        ON u.personal_id = p.id 
        INNER JOIN tbl_sistemas s
        ON u.sistema_id = s.id
        WHERE u.estado = '1'
        ORDER BY u.id ASC";
    $sql_query_copiar_user = $mysqli->query($query);
?>

    <!-- Modal asignar permisos multiples -->
    <div class="modal fade" id="modal_permisos_usuarios_multiples" role="dialog" aria-labelledby="myLargeModalLabel">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close cerrar_modal_asignar_permisos_multiples" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="gridSystemModalLabel">Asignar Permisos<span class="current_user_selected_table"></span></h4>
                </div>
                <div class="modal-body no-pad">
                    <div class="row">
                        <div class="col-xs-12 col-md-12 col-lg-12">
                            <h4 id="sec_usuarios_info"></h4>
                            <div class="col-xs-12 col-md-6 col-lg-6">
                                <div class="sec_usuarios_permisos_filtros">
                                    <select id="select_sistemas" class="select_sistemas " name="select_sistemas" style="width:33% !important;">
                                        <option selected disabled>Seleccione opción</option>
                                        <?php
                                        foreach ($sql_query_sistemas as $row_select_sistemas) {
                                        ?>
                                            <option value="<?php echo $row_select_sistemas['id']; ?>">
                                                [<?php echo $row_select_sistemas['id']; ?>] - <?php echo $row_select_sistemas['nombre']; ?>
                                            </option>
                                        <?php
                                        }
                                        ?>
                                    </select>
                                    <select id="select_permisos" class="select_permisos" name="select_permisos" style="width:66% !important;">
                                    </select>
                                    <input type="hidden" id="select_permisos_selected" value="">
                                </div>
                            </div>
                            <div class="col-xs-12 col-md-6 col-lg-6">
                                <div class="sec_usuarios_permisos_filtros">
                                    <button type="button" class="btn btn-info btn_copiar_user_settings hidden" data-button="copy">
                                        COPIAR
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row" id="container_locales_menus_sub_menus">
                        <div class="">
                            <!-- editado por alex asignar permisos usuarios (clase añadida porque era innecesaria) -->
                            <div class="col-xs-12 col-md-6 col-lg-6 asignar_permisos_locales">
                                <div class="contenedor_permisos_locales_copiar">
                                    <div class="">
                                        <div class="panel_heading_usuarios_locales">
                                            <h4 class="pull-left">
                                                <a id="titulo_locales_permisos_usuarios">
                                                    Locales
                                                </a>
                                            </h4>
                                            <button id="btn_copiar_locales" class="btn btn-info btn-xs btn_copiar_user_settings_locales pull-right" data-button="copy" data-tipo-permisos="locales">COPIAR</button>
                                            <button id="btnActualizarLocales" type="button" class="btn btn-warning btn-xs pull-right" style="margin-top: 7px; margin-right: 3px;">GUARDAR CAMBIOS</button>
                                        </div>
                                        <div id="collapseOne_locales">
                                            <div class="panel-body-1">
                                                <div class="container_table_locales_seleccionados" style="height: 700px !important">
                                                    <table id="tbl_locales_usuarios_seleccionados" class="table tbl_locales_usuarios_seleccionados" cellspacing="0" width="100%">
                                                        <thead id="tbl_locales_usuarios_thead">
                                                            <tr>
                                                                <th>
                                                                    <button type='button' class='all_parent_usuarios_permisos'>
                                                                        <span class='glyphicon glyphicon-plus'></span>
                                                                    </button>
                                                                    <span class='red_locales_usuarios_permisos'>Red</span>
                                                                </th>
                                                                <th></th>
                                                                <th>
                                                                    <input type="text" id="filter_tbl_locales_usuarios_seleccionados" class="form-control has-success" placeholder="Buscar..."></input>
                                                                </th>
                                                                <th>
                                                                    <div class="checkbox checkbox-success">
                                                                        <input class="checkAll_locales" type="checkbox"></input>
                                                                        <label></label>
                                                                    </div>
                                                                </th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            foreach ($query_locales_redes as $locales_data) {
                                                            ?>
                                                                <tr class="parent_redes_usuarios_permisos">
                                                                    <td>
                                                                        <button type="button" class="parent_usuarios_permisos" data-red="<?php echo $locales_data['id']; ?>">
                                                                            <span class="glyphicon glyphicon-plus"></span>
                                                                        </button>
                                                                        <span class="nombre_red_usuarios_permisos"><?php echo $locales_data['nombre']; ?></span>
                                                                    </td>
                                                                    <td></td>
                                                                    <td>
                                                                        FT
                                                                        <label class="switch_redes">
                                                                            <input type="checkbox" class="switch_redes" id="switch_<?php echo $locales_data['id']; ?>" onclick='fxAsignarRedUsuario(this, this.id);'>
                                                                            <span class="slider round"></span>
                                                                        </label>
                                                                    </td>
                                                                    <td>
                                                                        <div class="checkbox">
                                                                            <input id="local_checkbox_<?php echo $locales_data['id']; ?>"
                                                                                class="checkbox_locales_red checkbox_red checkbox_red_<?php echo $locales_data['id']; ?>"
                                                                                data-red="<?php echo $locales_data['id'] ?>" type="checkbox"></input>
                                                                            <label></label>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <?php
                                                                $query = "SELECT id,nombre,IF(ISNULL(red_id),'4',red_id) as red_id FROM tbl_locales WHERE red_id = '" . $locales_data['id'] . "' ORDER BY nombre ";
                                                                $result = $mysqli->query($query);
                                                                foreach ($result as $red_locales) {
                                                                ?>
                                                                    <tr class="rows_hidden_usuarios_permisos children_usuarios_permisos children_row_collapse_expand_<?php echo $locales_data['id']; ?> checkbox_me_usuarios_permisos">
                                                                        <td class="td_nombres_locales_usuarios">
                                                                            <span class="glyphicon glyphicon-home locales_usuarios_span"></span>
                                                                            <span class="td_id_locales_usuarios"><?php echo $red_locales['id']; ?></span>
                                                                            <span><?php echo $red_locales["nombre"]; ?></span>
                                                                        </td>
                                                                        <td class=""></td>
                                                                        <td class="td_red_locales_usuarios"><?php echo $locales_data['nombre']; ?></td>
                                                                        <td class="checkbox_me_locales_usuarios">
                                                                            <div class="checkbox ">
                                                                                <input id="local_checkbox_hijo_<?php echo $red_locales['id']; ?>"
                                                                                    type="checkbox"
                                                                                    value='<?php echo $red_locales["id"]; ?>' class="checkbox_locales_to_usuarios checkbox-primary
                                                                        checkbox_locales_to_usuarios_<?php echo $locales_data['id']; ?>" data-red="<?php echo $locales_data['id']; ?>" />
                                                                                <label></label>
                                                                            </div>
                                                                        </td>
                                                                    </tr>

                                                            <?php
                                                                }
                                                            }
                                                            ?>

                                                        </tbody>
                                                        <tfoot></tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- editado por alex asignar permisos usuarios (clase añadida y tamaño de 6 a 12 md lg-->
                            <div class="col-xs-12 col-md-12 col-lg-12 asignar_permisos_menu">
                                <div class="contenedor_permisos_menus_sub_menus_copiar">
                                    <div class="panel_heading_usuarios_menu_sub_menus">
                                        <h4 class="pull-left">
                                            <a id="titulo_menus_sub_menus_permisos_usuarios">
                                                Menu sub Menus
                                            </a>
                                        </h4>
                                        <button id="btn_copiar_permisos" class="btn btn-info btn-xs btn_copiar_user_settings_menus pull-right" data-button="copy" data-tipo-permisos="menus">COPIAR</button>
                                        <button id="btnActualizarPermisos" class="btn btn-warning btn-xs pull-right" style="margin-top: 7px; margin-right: 3px;">GUARDAR CAMBIOS</button>
                                    </div>
                                    <div id="collapseTwo_menus">
                                        <div class="panel-body-2">
                                            <input type="hidden" class="valor_menu_id" />
                                            <input type="hidden" class="valor_usuario_id" />
                                            <div class="container_table_menus_sub_menus_botones" style="height: 700px !important">
                                                <table class="table tbl_menu_sub_menu_botones" width="100%">
                                                    <?php $tipo_menu = 'permisos';
                                                    include '../sys/get_usuarios_menu.php'; ?>
                                                    <tfoot class="tbl_menu_sub_menu_botones_tfoot"></tfoot>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" name="btn_guardar_lista_multiple" class="btn btn-primary btn-block btn_save_settings_users_permisos">GUARDAR</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Copiar locales-->
    <div class="modal fade" id="modal_copiar_locales" role="dialog" aria-labelledby="localModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h5 class="modal-title" id="localModalLabel">Copiar Permisos</h5>
                </div>
                <div class="modal-body">

                    <p class="usuario_label text-success">Usuario Destino:</p>
                    <select class="select_permisos_usuario_a_copiar" id="usuario_a_copiar_permisos" name="select_permisos" style="width:100%;">
                        <option selected disabled>Seleccione usuario destino</option>
                        <?php
                        foreach ($sql_query_copiar_user as $row_user) {
                        ?>
                            <option value="<?php echo $row_user['id']; ?>">
                                [<?php echo $row_user['id']; ?>] - <?php echo $row_user['usuario']; ?> <?php echo $row_user['nombre']; ?> <?php echo $row_user['apellido_paterno']; ?> <?php echo $row_user['apellido_materno']; ?> - [<?php echo $row_user['sistema_id']; ?>] <?php echo $row_user['sistema']; ?>
                            </option>
                        <?php
                        }
                        ?>
                    </select>
                    <input type="hidden" class="usuario_seleccionado_a_copiar" />
                    <input type="hidden" class="usuario_objectivo_seleccionado_a_copiar" />
                    <input type="hidden" class="tipo_de_permisos_to_copy" />
                    <div class="contener_usuario_fuente">
                        <p class="title_usuario_seleccionado">Usuario Fuente:</p>&nbsp;<p id="current_user" class="current_user"></p>
                    </div>
                    <div class="contenedor_tabla_permisos_locales_copiar"></div>
                    <div class="contenedor_tabla_permisos_menus_copiar"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn_copy_settings_users_menu_permisos" text>GUARDAR</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para buscar usuarios por DNI y cambiar su estado -->
    <div class="modal fade" id="modal_search_dni">
        <div class="modal-dialog modal-sm">
            <div class="modal-content modal-rounded">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><span class="glyphicon glyphicon-search"></span> Comprobar usuarios por DNI</h4>
                </div>
                <div class="modal-body">
                    <label class="h4 strong block">Copie los DNI a buscar aquí abajo</label>
                    <p>Coloque los DNI uno debajo del otro (no usar comas)</p>
                    <textarea name="search_dni_textarea" id="search_dni_textarea" cols="34" rows="10"></textarea>
                    <button type="button" id="btn_search_dni" class="btn btn-warning"><span class="glyphicon glyphicon-search"></span> Comprobar DNI</button>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info btn_dismiss_dni"><span class="fa fa-user-times"></span> Desactivar Usuarios</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para visualizar los usuarios a eliminar -->
    <div class="modal fade" id="modal_users_to_close" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content modal-rounded">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Visualización de DNI</h4>
                </div>
                <div class="modal-body">
                    <table id="dt_users_to_close" class="table table-striped table-hover table-condensed table-bordered dt-responsive display" cellspacing="0" style="width: 100%;">
                        <thead>
                            <tr style="text-align: center;">
                                <th>ID USER</th>
                                <th>DNI</th>
                                <th>USER</th>
                                <th>NOMBRE</th>
                                <th>A. PATERNO</th>
                                <th>SISTEMA</th>
                                <th>AREA</th>
                                <th>CARGO</th>
                                <th>GRUPO</th>
                                <th>STATUS<br>PERSONAL</th>
                                <th>STATUS<br>USER</th>
                            </tr>
                        </thead>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-warning" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
<?php
}
?>