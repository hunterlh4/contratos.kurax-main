<?php

include '/var/www/html/sys/globalFunctions/generalInfo/parameterGeneral.php';

$this_menu = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = '" . $sec_id . "' LIMIT 1")->fetch_assoc();
$menu_id = $this_menu["id"];
// print_r($this_menu);
// print_r($menu_id);
if (array_key_exists($menu_id, $usuario_permisos) && in_array("view", $usuario_permisos[$menu_id])) {
    $cc = "";
    $nombre = "";
    if ($item_id) {
        $query = "
			SELECT 
				id,
				cc_id,
				nombre
			FROM tbl_locales
			WHERE id = '{$item_id}'
		";
        $query_result = $mysqli->query($query);
        if ($mysqli->error) {
            echo $mysqli->error;
            die;
        }
        if ($r = $query_result->fetch_assoc()) {
            $cc = $r["cc_id"];
            $nombre = $r["nombre"];
        }
    }

    $area_id = $login ? $login['area_id'] : 0;
    $cargo_id = $login ? $login['cargo_id'] : 0;

    $error_permiso_para_crear_locales_red_at = '';

    $query_permiso_para_crear_locales_red_at = "
	SELECT permiso 
	FROM tbl_otros_permisos 
	WHERE modulo = 'locales' AND accion = 'crear_locales_red_at'
	";

    $list_query_permiso_para_crear_locales_red_at = $mysqli->query($query_permiso_para_crear_locales_red_at);

    if ($mysqli->error) {
        $error_permiso_para_crear_locales_red_at .= 'Error al consultar el permiso para crear locales Red AT: ' . $mysqli->error;
    } else {
        $row_count_otros_permisos = $list_query_permiso_para_crear_locales_red_at->num_rows;
        if ($row_count_otros_permisos == 1) {
            $this_otros_permisos = $list_query_permiso_para_crear_locales_red_at->fetch_assoc();
            $permiso_para_crear_locales_red_at = (int)$this_otros_permisos["permiso"];
        } elseif ($row_count_otros_permisos == 0) {
            $error_permiso_para_crear_locales_red_at .= 'Error al consultar el permiso para crear locales Red AT: El permiso no existe en la tabla tbl_otros_permisos';
        } elseif ($row_count_otros_permisos > 1) {
            $error_permiso_para_crear_locales_red_at .= 'Error al consultar el permiso para crear locales Red AT: Existe más de un permiso crear_locales_red_at en la tabla tbl_otros_permisos';
        }
    }
    $supervisores = array();
    $admin_view = false;
    $query_supervisor = "
	SELECT id
	,usuario
	,password_md5
	,personal_id
	,grupo_id
	,sistema_id
	,estado
	,ip_restrict
	FROM tbl_usuarios
	WHERE id=  {$login['id']}";
    $result_supervisor = $mysqli->query($query_supervisor);
    while ($supervisor = $result_supervisor->fetch_assoc()) {
        if ($supervisor['grupo_id'] == 8 || ($login['cargo_id'] == '4' && $supervisor['grupo_id'] == 12)) {
            $admin_view = true;
            break;
        }
    }
    ?>
    <style>
        .swal2-title {
            font-size: 30px !important;
        }
        .swal2-html-container{
            font-size: 18px !important;
        }
        .swal2-confirm {
            padding: 14px 25px; /* Aumenta el padding para hacer el botón más grande */
            font-size: 16px !important; /* Tamaño de fuente */
        }
        .swal2-cancel {
            padding: 14px 25px; /* Aumenta el padding para hacer el botón más grande */
            font-size: 16px !important; /* Tamaño de fuente */
        }
    </style>
    <script src="js/sweetalert2@11.js"></script>
    <div class="content container-fluid content_locales">
        <div class="page-header wide">
            <div class="row">
                <div class="col-xs-12 text-center">
                    <div class="page-title"><i class="icon icon-inline fa fa-fw fa-building"></i> Locales -
                        [<?php echo $cc; ?>] <?php echo $nombre; ?> </div>
                    <input type="hidden" id="area_id_temporal" value="<?php echo $area_id ?>">
                    <input type="hidden" id="cargo_id_temporal" value="<?php echo $cargo_id ?>">
                    <input type="hidden" id="item_id_temporal" value="<?php echo isset($item_id) ? $item_id : '0'; ?>">
                    <input type="hidden" id="permiso_para_crear_locales_red_at_temporal"
                           value="<?php echo isset($permiso_para_crear_locales_red_at) ? $permiso_para_crear_locales_red_at : '0'; ?>">
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <?php
                    if ($item_id) {
                        ?>
                        <a class="btn btn-default btn-sm" href="./?sec_id=<?php echo $sec_id; ?>"><i
                                    class="glyphicon glyphicon-arrow-left"></i>Regresar</a>
                        <?php
                        if (array_key_exists($menu_id, $usuario_permisos) && in_array("save", $usuario_permisos[$menu_id])) {
                            ?>
                            <button type="button" data-then="exit" class="save_btn btn btn-success btn-sm">
                                <i class="glyphicon glyphicon-floppy-save"></i>
                                Guardar y Salir
                            </button>
                            <button type="button" data-then="reload" class="save_btn btn btn-success btn-sm">
                                <i class="glyphicon glyphicon-floppy-save"></i>
                                Guardar
                            </button>

                            <?php
                        }
                    } else {
                        if (array_key_exists($menu_id, $usuario_permisos) && in_array("new", $usuario_permisos[$menu_id])) {
                            ?>
                            <a href="/?sec_id=<?php echo $sec_id; ?>&amp;item_id=new"
                               class="btn btn-rounded btn-success btn-sm"><i class="glyphicon glyphicon-plus"></i>Agregar</a>
                            <?php
                        }
                        if (array_key_exists($menu_id, $usuario_permisos) && in_array("export", $usuario_permisos[$menu_id])) {
                            ?>
                            <div class="btn-group btn-group-separators">
                                <a href="export.php?export=tbl_locales&amp;type=lista"
                                   class="btn btn-success btn-sm export_list_btn" download="locales.xls"><span
                                            class="glyphicon glyphicon-export"></span> Exportar Lista</a>
                                <button type="button" class="btn btn-success btn-sm dropdown-toggle"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="caret"></span>
                                </button>
                                <button type="button" data-then="reload"
                                        class="check_local_paid_btn btn btn-primary btn-sm">
                                    <i class="glyphicon glyphicon-check"></i>
                                    Check
                                </button>
                                <ul class="dropdown-menu">
                                    <li><a href="export.php?export=tbl_locales" download="locales.xls"><span
                                                    class="glyphicon glyphicon-export"></span> Exportar Todo</a></li>
                                </ul>

                            </div>
                            <div class="btn-group btn-group-separators">
                                <a onclick="list_detalle_locales_excel();"
                                   class="btn btn-success btn-sm export_list_btn" download="locales.xls"><span
                                            class="glyphicon glyphicon-export"></span> Exportar Lista Detallada</a>
                            </div>
                            <?php
                        }
                        if (array_key_exists($menu_id, $usuario_permisos) && in_array("crear_red_at", $usuario_permisos[$menu_id]) && ($area_id == 6)) {
                            ?>
                            <div class="btn-group btn-group-separators">
                                <?php
                                if ($error_permiso_para_crear_locales_red_at == '') {
                                    ?>
                                    <select class="form-control mt-2"
                                            id="select_option_permiso_para_crear_locales_red_at">
                                        <option value="0" <?php echo ($permiso_para_crear_locales_red_at === 0) ? 'selected' : ''; ?> >
                                            JC y Supervisor NO puede crear locales Red AT
                                        </option>
                                        <option value="1" <?php echo ($permiso_para_crear_locales_red_at === 1) ? 'selected' : ''; ?> >
                                            JC y Supervisor SI puede crear locales Red AT
                                        </option>
                                    </select>
                                    <?php
                                } else {
                                    echo '<p>' . $error_permiso_para_crear_locales_red_at . '</p>';
                                }
                                ?>
                            </div>
                            <?php
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
        if ($item_id) {
            $item = $mysqli->query("SELECT id
            ,canal_id
			,cc_id
			,red_id
			,tipo_id
			,propiedad_id
			,cliente_id
			,razon_social_id
			,nombre
			,descripcion
			,zona_id
			,latitud
			,longitud
			,ubigeo_id
			,ubigeo_cod_depa
			,ubigeo_cod_prov
			,ubigeo_cod_dist
			,direccion
			,email
			,phone
			,fecha_registro
			,representante_id
			,asesor_id
			,estado_legal_id
			,area
			,otra_casa_apuestas
			,otra_casa_apuestas_des
			,experiencia_casa_apuestas
			,experiencia_casa_apuestas_des
			,estado
			,administracion_tipo
			,reportes_mostrar
			,username
			,password
			,zona_financiera
			,show_web
			,trastienda
            ,created_at
            ,fecha_alta
			FROM tbl_locales
			WHERE id = '" . $item_id . "'")->fetch_assoc();
            $itemServicios = $mysqli->query("SELECT local_id
			,internet_proveedor_id
			,internet_tipo_id
			,num_decos_internet
			,num_decos_directv
			FROM tbl_locales_servicios
			WHERE local_id = '" . $item_id . "'")->fetch_assoc();
            $itemEquipos = $mysqli->query("SELECT local_id
			,num_tv_apuestas_virtuales
			,num_tv_apuestas_deportivas
			FROM tbl_locales_equipos
			WHERE local_id = '" . $item_id . "'")->fetch_assoc();
            $itemEquiposComputo = $mysqli->query("SELECT local_id
			,num_cpu
			,num_monitores
			,num_autoservicios
			,num_allinone
			,num_terminales_hibrido
			,num_terminales_antiguo
			FROM tbl_locales_equipos_computo
			WHERE local_id = '" . $item_id . "'")->fetch_assoc();
            $local_servicios = [];
            $local_servicios_query = $mysqli->query("
			SELECT 
				id,
				servicio_id,
				canal_de_venta_id,
				proveedor_id,
				nombre,
                habilitado
			FROM tbl_local_proveedor_id 
			WHERE 
				local_id = '{$item_id}' 
				AND estado = '1'"
            );
            while ($ls = $local_servicios_query->fetch_assoc()) {
                $local_servicios[] = $ls;
            }

            $credenciales_tipo = [];
            $credenciales_tipo_query = $mysqli->query("SELECT id,descripcion
			FROM tbl_local_credencial_tipo");
            while ($registro = $credenciales_tipo_query->fetch_assoc()) {
                $credenciales_tipo[] = $registro;
            }

            ?>
            <div class="row">
                <!-- [<?php $item_id; ?>] <?php /*$item['nombre']*/; ?> -->
            </div>
            <input type="hidden" class="save_data" data-col="table" value="tbl_locales">
            <input type="hidden" class="save_data" data-col="id" value="<?php echo $item_id; ?>">
            <div class="col-md-10 ">
                <div class="tab-content">
                    <?php
                    if (array_key_exists($menu_id, $usuario_permisos) && in_array("local", $usuario_permisos[$menu_id])) {
                        include("sec_locales_local.php");
                    }
                    if (array_key_exists($menu_id, $usuario_permisos) && in_array("setting", $usuario_permisos[$menu_id])) {
                        include("sec_locales_setting.php");
                    }
                    if (array_key_exists($menu_id, $usuario_permisos) && in_array("settingv2", $usuario_permisos[$menu_id])) {
                        include("sec_locales_settingv2.php");
                    }
                    if (array_key_exists($menu_id, $usuario_permisos) && in_array("cctv_cameras", $usuario_permisos[$menu_id])) {
                        include("sec_locales_cctv_cameras.php");
                    }
                    if (array_key_exists($menu_id, $usuario_permisos) && in_array("credentials", $usuario_permisos[$menu_id])) {
                        include("sec_locales_credentials.php");
                    }
                    if ((array_key_exists($menu_id, $usuario_permisos) && in_array("promotions", $usuario_permisos[$menu_id])) || $admin_view) {
                        include("sec_locales_promotions.php");
                    }
                    if (array_key_exists($menu_id, $usuario_permisos) && in_array("cashdesk", $usuario_permisos[$menu_id])) {
                        include("sec_locales_cashdesk.php");
                    }
                    if (array_key_exists($menu_id, $usuario_permisos) && in_array("users", $usuario_permisos[$menu_id])) {
                        include("sec_locales_users.php");
                    }
                    if (array_key_exists($menu_id, $usuario_permisos) && in_array("solicitud_prestamo", $usuario_permisos[$menu_id])) {
                        include("sec_locales_requests.php");
                    }
                    if (array_key_exists($menu_id, $usuario_permisos) && in_array("horarios", $usuario_permisos[$menu_id])) {
                        include("sec_locales_horarios.php");
                    }
                    if (array_key_exists($menu_id, $usuario_permisos) && in_array("web_config", $usuario_permisos[$menu_id])) {
                        include("sec_locales_web.php");
                    }
                    if (array_key_exists($menu_id, $usuario_permisos) && in_array("servicios_local", $usuario_permisos[$menu_id])) {
                        include("sec_locales_servicios_local.php");
                    }
                    if (array_key_exists($menu_id, $usuario_permisos) && in_array("equipos", $usuario_permisos[$menu_id])) {
                        include("sec_locales_equipos.php");
                    }
                    if (array_key_exists($menu_id, $usuario_permisos) && in_array("local_comercial", $usuario_permisos[$menu_id])) {
                        include("sec_locales_comercial.php");
                    }
                    if ($area_id == 21 && ($cargo_id == 16 || $cargo_id == 4) || (array_key_exists($menu_id, $usuario_permisos) && in_array("addServicioPublico", $usuario_permisos[$menu_id])))
                    {
                        include("sec_locales_servicio_publico.php");
                    }
                    if ((array_key_exists($menu_id, $usuario_permisos) && in_array("local_terminal", $usuario_permisos[$menu_id])) || $admin_view) {
                        include("sec_locales_terminal.php");
                    }
                    if ((array_key_exists($menu_id, $usuario_permisos) && in_array("locales_aplicativos", $usuario_permisos[$menu_id])) || $admin_view) {
                        include("sec_locales_aplicativos.php");
                    }
                    ?>
                </div>
            </div>
            <div class="col-md-2 hidden-xs hidden-sm">
                <ul class="nav nav-tabs tabs-right local_tabs">
                    <?php if (array_key_exists($menu_id, $usuario_permisos) && in_array("local", $usuario_permisos[$menu_id])) { ?>
                        <li class="active"><a class="tab_btn" href="#tab_local" data-tab="tab_local">Local</a></li>
                    <?php } ?>
                    <?php if (array_key_exists($menu_id, $usuario_permisos) && in_array("setting", $usuario_permisos[$menu_id])) { ?>
                        <li class=""><a class="tab_btn" href="#tab_config" data-tab="tab_config">Configuracion</a></li>
                    <?php } ?>
                    <?php if (array_key_exists($menu_id, $usuario_permisos) && in_array("settingv2", $usuario_permisos[$menu_id])) { ?>
                        <li class=""><a class="tab_btn" href="#tab_config_v2" data-tab="tab_config_v2">Configuracion
                                v2</a></li>
                    <?php } ?>
                    <?php if (array_key_exists($menu_id, $usuario_permisos) && in_array("cctv_cameras", $usuario_permisos[$menu_id])) { ?>
                        <li class=""><a class="tab_btn" href="#cctv_cameras" data-tab="cctv_cameras">CCTV Cameras</a>
                        </li>
                    <?php } ?>
                    <?php if (array_key_exists($menu_id, $usuario_permisos) && in_array("credentials", $usuario_permisos[$menu_id])) { ?>
                        <li class=""><a class="tab_btn" href="#tab_credenciales" data-tab="tab_credenciales">Credenciales</a>
                        </li>
                    <?php } ?>
                    <?php if ((array_key_exists($menu_id, $usuario_permisos) && in_array("promotions", $usuario_permisos[$menu_id])) || $admin_view) { ?>
                        <li class=""><a class="tab_btn" href="#tab_marketing_promocion"
                                        data-tab="tab_marketing_promocion">Marketing Promoción</a></li>
                    <?php } ?>
                    <?php if (array_key_exists($menu_id, $usuario_permisos) && in_array("cashdesk", $usuario_permisos[$menu_id])) { ?>
                        <li class=""><a class="tab_btn" href="#tab_cajas" data-tab="tab_cajas">Cajas</a></li>
                    <?php } ?>
                    <?php if (array_key_exists($menu_id, $usuario_permisos) && in_array("users", $usuario_permisos[$menu_id])) { ?>
                        <li class=""><a class="tab_btn" href="#tab_users" data-tab="tab_users">Operaciones Usuarios</a>
                        </li>
                    <?php } ?>
                    <?php if (array_key_exists($menu_id, $usuario_permisos) && in_array("solicitud_prestamo", $usuario_permisos[$menu_id])) { ?>
                        <li class=""><a class="tab_btn" href="#tab_solicitudes"
                                        data-tab="tab_solicitudes">Solicitudes</a></li>
                    <?php } ?>
                    <?php if (array_key_exists($menu_id, $usuario_permisos) && in_array("horarios", $usuario_permisos[$menu_id])) { ?>
                        <li class=""><a class="tab_btn" href="#tab_horarios" data-tab="tab_horarios">Horarios</a></li>
                    <?php } ?>
                    <?php if (array_key_exists($menu_id, $usuario_permisos) && in_array("local_comercial", $usuario_permisos[$menu_id])) { ?>
                        <li class=""><a class="tab_btn" href="#tab_comercial" data-tab="tab_comercial">Comercial</a>
                        </li>
                    <?php } ?>
                    <?php if (array_key_exists($menu_id, $usuario_permisos) && in_array("web_config", $usuario_permisos[$menu_id])) { ?>
                        <li class=""><a class="tab_btn" href="#tab_web" data-tab="tab_web">Configuración Web</a></li>
                    <?php } ?>
                    <?php if (array_key_exists($menu_id, $usuario_permisos) && in_array("servicios_local", $usuario_permisos[$menu_id])) { ?>
                        <li class=""><a class="tab_btn" href="#tab_servicios_local" data-tab="tab_servicios_local">Servicios
                                del local</a></li>
                    <?php } ?>
                    <?php if (array_key_exists($menu_id, $usuario_permisos) && in_array("equipos", $usuario_permisos[$menu_id])) { ?>
                        <li class=""><a class="tab_btn" href="#tab_equipos" data-tab="tab_equipos">Equipos</a></li>
                    <?php } ?>
                    <?php if ($area_id == 21 && ($cargo_id == 16 || $cargo_id == 4) || (array_key_exists($menu_id, $usuario_permisos) && in_array("addServicioPublico", $usuario_permisos[$menu_id])))
                        { ?>
                        <li class="">
                            <a class="tab_btn" href="#tab_servicio_publico" data-tab="tab_servicio_publico">
                                Servicios Públicos
                            </a>
                        </li>
                    <?php 
                        } ?>
                    <?php if ((array_key_exists($menu_id, $usuario_permisos) && in_array("local_terminal", $usuario_permisos[$menu_id])) || $admin_view) { ?>
                        <li class=""><a class="tab_btn" href="#tab_local_terminal" data-tab="tab_local_terminal">Terminal
                                Autoservicio</a></li>
                    <?php } ?>
                    <?php
                    if ((array_key_exists($menu_id, $usuario_permisos) && in_array("locales_aplicativos", $usuario_permisos[$menu_id])) || $admin_view) {
                        ?>
                        <li class=""><a class="tab_btn" href="#tab_locales_aplicativos"
                                        data-tab="tab_locales_aplicativos">Aplicativos</a></li>
                    <?php } ?>
                </ul>
            </div>
            <div class="modal" id="locales_add_usuario_modal" data-backdrop="static">
                <div class="modal-dialog">
                    <div class="modal-content modal-rounded">
                        <div class="modal-header">
                            <button type="button" class="close add_local_usuario_cerrar_btn"><span aria-hidden="true">&times;</span>
                            </button>
                            <h4 class="modal-title">Agregar Usuario</h4>
                        </div>
                        <div class="modal-body">
                            <div class="form">
                                <div class="form-group">
                                    <label class="control-label">[id][usuario] Nombre Apellido (Area/Cargo)(Grupo) -
                                        (DNI)</label>
                                    <select class="form-control select2 set_data new_usuario_id" name="usuario_id">
                                        <option value="0">--- Seleccione o cree un usuario ---</option>
                                        <option value="new">+ CREAR NUEVO USUARIO +</option>
                                        <?php
                                        $new_local_users = [];
                                        $lu_command = "SELECT DISTINCT
                                            u.id, u.usuario, u.estado,
                                            ug.nombre as 'grupo_nombre',
                                            p.nombre,
                                            p.apellido_paterno,
                                            IFNULL(p.dni, '') as dni,
                                            IFNULL((SELECT a.nombre FROM tbl_areas a WHERE a.id = p.area_id), 'Sin Área') AS area,
                                            IFNULL((SELECT c.nombre FROM tbl_cargos c WHERE c.id = p.cargo_id), 'Sin Cargo') AS cargo
                                        FROM tbl_usuarios u
                                            LEFT JOIN tbl_usuarios_grupos ug ON ug.id = u.grupo_id
                                            LEFT JOIN tbl_personal_apt p ON (p.id = u.personal_id)
                                        WHERE
                                            u.estado = 1
                                        ";
                                        /*
                                        $lu_command = "SELECT u.id, u.usuario, u.estado,
                                        ug.nombre as 'grupo_nombre',
                                        p.nombre, p.apellido_paterno, IFNULL(p.dni, '') as dni,
                                        (SELECT a.nombre FROM tbl_areas a WHERE a.id = p.area_id) AS area,
                                        (SELECT c.nombre FROM tbl_cargos c WHERE c.id = p.cargo_id) AS cargo
                                        FROM tbl_usuarios u
                                        LEFT JOIN tbl_usuarios_grupos ug ON ug.id = u.grupo_id
                                        LEFT JOIN tbl_personal_apt p ON (p.id = u.personal_id)
                                        WHERE u.estado IS NOT NULL
                                        AND (p.area_id = ('21') AND p.cargo_id IN (4,5) OR (p.area_id = ('22') AND p.cargo_id != 3) OR u.grupo_id in (26,27,30)
                                        )
                                        AND (
                                        IF(
                                            (
                                                (SELECT count(*)
                                                FROM   tbl_usuarios_locales ul
                                                    LEFT JOIN tbl_usuarios u
                                                            ON  (u.id = ul.usuario_id)
                                                    LEFT JOIN tbl_personal_apt p
                                                            ON  (p.id = u.personal_id)
                                                WHERE  ul.local_id = '" . $item_id . "'
                                                    AND ul.estado = '1'
                                                    AND (
                                                            p.area_id = ('21')
                                                            AND p.cargo_id IN (4, 5)
                                                            OR (p.area_id = ('22') AND p.cargo_id != 3)
                                                            OR (p.area_id = '31' AND p.cargo_id = 5)
                                                            OR (p.area_id = '28' AND p.cargo_id = 5)
                                                        )
                                                    AND p.cargo_id = 4
                                            ) > 0)
                                            ,p.cargo_id NOT IN (4)
                                            ,p.cargo_id NOT IN('')
                                        )
                                    )
                                    ";
                                    */
                                        $lu_query = $mysqli->query($lu_command);
                                        while ($lu = $lu_query->fetch_assoc()) {
                                            ?>
                                            <option value="<?php echo $lu["id"]; ?>">[<?php echo $lu["id"]; ?>
                                                ][<?php echo $lu["usuario"]; ?>
                                                ] <?php echo $lu["nombre"]; ?> <?php echo $lu["apellido_paterno"]; ?>
                                                (<?php echo $lu["area"]; ?>/<?php echo $lu["cargo"]; ?>
                                                )(<?php echo $lu["grupo_nombre"] ?>) - (<?php echo $lu["dni"] ?>)
                                            </option>
                                            <?php
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="new_user_form hidden">
                                    <br>
                                    <div class="form-group">
                                        <label class="col-xs-4 control-label" for="varchar_usuario">DNI</label>
                                        <div class="input-group col-xs-8">
                                            <input
                                                type="text"
                                                name="dni"
                                                class="form-control set_data"
                                                id="locales_varchar_dni"
                                                placeholder="DNI"
                                                maxlength="8"
                                                pattern="[0-9]{1,}"
                                                title="Usuario">
                                            <span class="input-group-btn">
                                                <button class="btn btn-success btn-sm" onclick="locales_usuarios_obtener_por_dni(this.value);" type="button"><i class="fa fa-search"></i></button>
                                            </span>
                                        </div>
                                    </div>

                                    <div id="container-form-locales-usuario">
                                        <input type="hidden" 
                                                id="locales_usuarios_id" 
                                                name="locales_usuarios_id" 
                                                value="0"
                                                readonly>
                                        <input type="hidden" 
                                                id="locales_personal_id" 
                                                name="locales_personal_id" 
                                                value="0"
                                                readonly>              
                                        <div class="form-group">
                                            <label class="col-xs-4 control-label" for="varchar_usuario">Usuario</label>
                                            <div class="input-group col-xs-8">
                                                <input type="text" name="usuario" class="form-control set_data"
                                                    id="locales_usuarios_usuario" placeholder="nombre.apellido" title="Usuario" pattern="[a-zA-Z]+(\.[a-zA-Z]+)?"
                                                    >
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-xs-4 control-label" for="varchar_usuario">Nombre</label>
                                            <div class="input-group col-xs-8">
                                                <input type="text" name="nombre" class="form-control set_data"
                                                    id="locales_usuarios_nombre" placeholder="Nombre" title="Usuario">
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-xs-4 control-label" for="varchar_usuario">Apellido</label>
                                            <div class="input-group col-xs-8">
                                                <input type="text" name="apellido_paterno" class="form-control set_data"
                                                    id="locales_usuarios_apellido" placeholder="Apellido Paterno" title="Usuario">
                                            </div>
                                        </div>
                                    
                                        <?php
                                        $area = [];
                                        $cargo = [];
                                        if ($item["red_id"] == 8) {//televentas
                                            $area = ["id" => 31, "nombre" => "Televentas"];
                                            $cargo[] = ["id" => 5, "nombre" => "Cajero"];
                                            $cargo[] = ["id" => 21, "nombre" => "Validador"];
                                            $cargo[] = ["id" => 23, "nombre" => "Pagador"];
                                            $cargo[] = ["id" => 24, "nombre" => "Digitador"];
                                        } else if ($item["red_id"] == 5) {//agentes
                                            $area = ["id" => 28, "nombre" => "Agentes"];
                                            $cargo = ["id" => 5, "nombre" => "Cajero"];
                                        } else {
                                            //if( $item["red_id"] == 1){//at
                                            $area = ["id" => 21, "nombre" => "Operaciones"];
                                            $cargo = ["id" => 5, "nombre" => "Cajero"];
                                        }
                                        ?>
                                        <div class="form-group">
                                            <label class="col-xs-4 control-label">Area</label>
                                            <div class="input-group col-xs-8">
                                                <select id="area_id" name="area_id"
                                                        class="form-control select2 new_area_id set_data">
                                                    <!--<option value="21">Operaciones</option>-->
                                                    <option value="<?php echo $area['id']; ?>"><?php echo $area["nombre"] ?></option>
                                                    <!-- <option value="22">Control Interno</option> -->
                                                </select>
                                                <div class="form-group">

                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="col-xs-4 control-label">Cargo</label>
                                            <div class="input-group col-xs-8">
                                                <select id="cargo_id" class="form-control select2 new_area_id set_data"
                                                        name="cargo_id">
                                                    <!-- <option value="4">Supervisor</option> -->
                                                    <!--<option value="5">Cajero</option>-->
                                                    <?php
                                                    if ($item["red_id"] == 8) {/*televentas*/
                                                        foreach ($cargo as $key => $value) { ?>
                                                            <option value="<?php echo $value['id']; ?>"><?php echo $value["nombre"] ?></option>
                                                        <?php }
                                                    } else { ?>
                                                        <option value="<?php echo $cargo['id']; ?>"><?php echo $cargo["nombre"] ?></option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        </div>
                                </div>
                            </div>
                            <div class="user_locales hidden">
                                <table class="table table-condensed table-bordered">
                                    <thead>
                                    <tr>
                                        <th>Local</th>
                                        <th>opt</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <div id="container-form-locales-usuario_botones" >
                            <button class="btn btn-success add_local_usuario_btn">Agregar</button>
                            <button class="btn btn-default add_local_usuario_cerrar_btn">Cerrar</button>
                        </div> </div>
                    </div>
                </div>
            </div>
            <?php
        } else {
            ?>
            <link rel="stylesheet" href="css/simplePagination.css">
            <div class="row">
                <div class="col-sm-6 col-lg-5">
                    <div class="form-group form-inline">
                        Mostrar
                        <select id="cbLocalesLimit" name="cbLocalesLimit" class="form-control">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        Locales
                    </div>
                </div>
                <div class="col-xs-12 col-sm-6 col-lg-offset-4 col-lg-3">
                    <div class="form-group form-inline pull-right">
                        Buscar:
                        <div class="form-group has-feedback has-search">
                            <span style="margin-top:-2px" class="form-control-feedback"><i id="icoLocalesSpinner"
                                                                                           class="fa fa-spinner fa-spin"></i></span>
                            <input type="text" id="txtLocalesFilter" class="form-control"
                                   placeholder="Buscar nombre de local" width="100%">
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xs-12">
                    <div class="table-responsive">
                        <table id="tblLocales"
                               class="table table-striped table-hover table-condensed table-bordered dt-responsive"
                               cellspacing="0" width="100%">
                            <thead>
                            <tr class="small">
                                <th style="width: 60px;">ID</th>
                                <th style="width: 60px;">CC</th>
                                <th style="width: 200px;">Nombre</th>
                                <th style="width: 400px;">Dirección</th>
                                <th>Razón Social</th>
                                <th style="width: 70px;">Zona</th>
                                <th style="width: 120px;">Departamento</th>
                                <th style="width: 110px;">Provincia</th>
                                <th style="width: 160px;">Distrito</th>
                                <th style="width: 160px;">Coordenadas</th>
                                <th style="width: 160px;">Red</th>
                                <th style="width: 110px;">Opciones</th>
                            </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="pull-right">
                        <div id="paginationLocalesJS" data-page="" data-ini=""></div>
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
    </div>
    <div class="modal" id="locales_add_caja_modal" data-backdrop="static">
        <div class="modal-dialog modal-sm">
            <div class="modal-content modal-rounded">
                <div class="modal-header">
                    <button type="button" class="close add_caja_cerrar_btn"><span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title">Agregar Caja</h4>
                </div>
                <div class="modal-body">
                    <div class="form add_caja_form">
                        <input type="hidden" name="local_id" value="<?php echo $item_id; ?>" class="save_col">
                        <input type="hidden" name="estado" value="1" class="save_col">
                        <div class="form-group">
                            <label class="control-label">Tipo</label>
                            <select class="form-control save_col" name="caja_tipo_id">
                                <?php
                                $caja_tipos_command = 
                                "
                                    SELECT 
                                        id, nombre 
                                    FROM tbl_caja_tipos 
                                    WHERE estado = '1'
                                ";

                                $caja_tipos_query = $mysqli->query($caja_tipos_command);
                                while ($ct = $caja_tipos_query->fetch_assoc())
                                {
                                    ?>
                                        <option value="<?php echo $ct["id"]; ?>">
                                            <?php echo $ct["nombre"]; ?>
                                        </option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Nombre</label>
                            <select class="form-control save_col" 
                                name="numero_caja_nombre" 
                                id="sec_locales_numero_caja_nombre">
                                <?php
                                $numero_caja_command = 
                                "
                                    SELECT 
                                        id, nombre 
                                    FROM tbl_local_cajas_nombres 
                                    WHERE status = '1'
                                ";

                                $numero_caja_query = $mysqli->query($numero_caja_command);

                                while ($nc = $numero_caja_query->fetch_assoc())
                                {
                                    ?>
                                        <option value="<?php echo $nc["id"]; ?>">
                                            <?php echo $nc["nombre"]; ?>
                                        </option>
                                    <?php
                                }
                                ?>
                            </select>

                            <input 
                                type="text" 
                                id="sec_locales_numero_caja_nombre_txt" 
                                class="form-control save_col" 
                                name="nombre"
                                placeholder="Ingrese Nombre"
                                maxlength="50"
                                autocomplete="off" 
                                style="margin-top: 5px; display: none;">
                        </div>
                        <div class="form-group">
                            <label>Descripción</label>
                            <textarea class="form-control save_col" name="descripcion"></textarea>
                        </div>
                    </div>
                    <div class="col-xs-12">

                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success add_caja_btn">Agregar</button>
                    <button class="btn btn-default add_caja_cerrar_btn">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="locales_add_caja_cdv_modal" data-backdrop="static">
        <div class="modal-dialog modal-sm">
            <div class="modal-content modal-rounded">
                <div class="modal-header">
                    <button type="button" class="close add_caja_cdv_cerrar_btn"><span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="title-modal-cdv" >Agregar Canal de Venta</h4>
                </div>
                <div class="modal-body">
                    <div class="form add_caja_cdv_form">
                        <input type="hidden" name="local_id" value="<?php echo $item_id; ?>" class="save_col">
                        <input type="hidden" name="estado" value="1" class="save_col">
                        <div class="form-group" id="modal-sec-agregar-cdv">
                            <label class="control-label">Tipo</label>
                            <select class="form-control save_col" name="detalle_tipos_id">
                                <?php
                                $caja_tipos_command = "SELECT id, nombre, descripcion FROM tbl_caja_detalle_tipos WHERE estado = '1' order by nombre asc";
                                $caja_tipos_query = $mysqli->query($caja_tipos_command);
                                while ($ct = $caja_tipos_query->fetch_assoc()) {
                                    ?>
                                    <option value="<?php echo $ct["id"]; ?>" data-nombre="<?php echo $ct["nombre"]; ?>"
                                            data-descripcion="<?php echo $ct["descripcion"]; ?>"><?php echo $ct["nombre"]; ?></option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="hidden" class="form-control save_col" name="id" placeholder="">
                            <input type="text" class="form-control save_col" name="nombre" placeholder="">
                        </div>
                        <div class="form-group">
                            <label>Descripción</label>
                            <textarea class="form-control save_col" name="descripcion"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-success add_caja_cdv_btn">Agregar</button>
                    <button class="btn btn-default add_caja_cdv_cerrar_btn">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <?php
}
?>
