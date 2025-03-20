<?php
$return = array();
$return["memory_init"] = memory_get_usage();
$return["time_init"] = microtime(true);
include("global_config.php");
include("db_connect.php");
include("sys_login.php");
require("globalFunctions/generalInfo/menu.php");
if (isset($_GET["action"])) {
    if ($_GET["action"] == "new_user") {
        $exists = $mysqli->query("SELECT id FROM tbl_usuarios WHERE usuario = '" . $_POST['usuario'] . "'")->fetch_assoc();
        $usuario = $_POST["usuario"];
        $regex = '/^[a-zñÑ]*\.?[a-zñÑ]*$/';

        // if (!preg_match($regex, $usuario)) {
        //     $return["error"] = "invalid_user";
        //     $return["error_msg"] = "El usuario ingresado es inválido.";
        //     $return["error_focus"] = "usuario";
        //     echo json_encode($return);
        //     exit();
        // } else {
        //     $primerCaracter = substr($usuario, 0, 1);
        //     if ($primerCaracter == ".") {
        //         $return["error"] = "invalid_user";
        //         $return["error_msg"] = "El usuario ingresado es inválido.";
        //         $return["error_focus"] = "usuario";
        //         echo json_encode($return);
        //         exit();
        //     }
        //     $longitud = strlen($usuario);
        //     if ($longitud > 25) {
        //         $return["error"] = "invalid_user";
        //         $return["error_msg"] = "El usuario puede tener un máximo de 20 caracteres.";
        //         $return["error_focus"] = "usuario";
        //         echo json_encode($return);
        //         exit();
        //     }
        //     //$patron = '/^\w+\.\w+$/';
        //     $patron = $regex;
        //     if (!preg_match($patron, $usuario)) {
        //         $return["error"] = "invalid_user";
        //         $return["error_msg"] = "El formato del usuario no es válido.";
        //         $return["error_focus"] = "usuario";
        //         echo json_encode($return);
        //         exit();
        //     }
        // }
        // modificado por alex 13-03-25

        if (empty($usuario)) {
            $return["error"] = "empty_user";
            $return["error_msg"] = "El usuario no puede estar vacío.";
            $return["error_focus"] = "usuario";
            echo json_encode($return);
            exit();
        }

        if (!preg_match($regex, $usuario)) {
            $return["error"] = "invalid_user";
            $return["error_msg"] = "El usuario ingresado es inválido.";
            $return["error_focus"] = "usuario";
            echo json_encode($return);
            exit();
        }

        $primerCaracter = substr($usuario, 0, 1);
        if ($primerCaracter == ".") {
            $return["error"] = "invalid_user";
            $return["error_msg"] = "El usuario ingresado es inválido.";
            $return["error_focus"] = "usuario";
            echo json_encode($return);
            exit();
        }

        $longitud = strlen($usuario);
        if ($longitud > 25) {
            $return["error"] = "invalid_user";
            $return["error_msg"] = "El usuario puede tener un máximo de 25 caracteres.";
            $return["error_focus"] = "usuario";
            echo json_encode($return);
            exit();
        }

        $patron = $regex;
        if (!preg_match($patron, $usuario)) {
            $return["error"] = "invalid_user";
            $return["error_msg"] = "El formato del usuario no es válido.";
            $return["error_focus"] = "usuario";
            echo json_encode($return);
            exit();
        }
        // fin de modificado
        $menu_manual_usuario_id = getMenuBySecId('manual_usuarios');
        if ($_POST['type_user'] == 'new') {
            $fecha_create = date("Y-m-d H:i:s");
            if ($exists) {
                $return["error"] = "exists";
                $return["error_msg"] = "El usuario ya existe!";
                $return["error_focus"] = "usuario";
            } else {
                $gid = $_POST["grupo_id"] != 0 ? $_POST["grupo_id"] : "null";
                $query = "INSERT INTO tbl_usuarios 
                (usuario,personal_id,sistema_id,grupo_id,estado,ip_restrict,created_at,user_created_id)
                VALUES ('" . $_POST['usuario'] . "','" . $_POST['personal_id'] . "','" . $_POST['sistema_id'] . "'," . $gid . ",'1','" . $_POST['ip_restrict'] . "','" . $fecha_create . "'," . $login["id"] . ")";
                $mysqli->query($query);
                if ($mysqli->error) {
                    print_r($mysqli->error);
                    echo "\n";
                    echo $query;
                    exit();
                }

                $return["id"] = $mysqli->insert_id;
                if ($_POST["grupo_id"] != 0) {
                    // Seleccionamos el id del nuevo usuario creado
                    $result = $mysqli->query("SELECT id FROM tbl_usuarios WHERE usuario = '" . $_POST['usuario'] . "'")->fetch_assoc();

                    $mysqli->query("DELETE FROM tbl_permisos WHERE usuario_id =" . $return["id"]);
                    $query = "INSERT INTO tbl_permisos 
                    (grupo_id,menu_id,boton_id,boton_nombre,estado,usuario_id)
                    SELECT grupo_id,menu_id,boton_id,boton_nombre,estado,'" . $result['id'] . "' FROM tbl_permisos
                    WHERE grupo_id = " . $_POST["grupo_id"] . " AND usuario_id=0";
                    $mysqli->query($query);

                    $query = "UPDATE tbl_permisos SET estado = 1
                    WHERE usuario_id = '" . $result['id'] . "' AND boton_id = 1 AND menu_id = '" . $menu_manual_usuario_id . "'";
                    $result = $mysqli->query($query);
                } else {

                    $query = "INSERT INTO tbl_permisos (grupo_id,menu_id,boton_id,boton_nombre,estado,usuario_id) VALUES(0,'" . $menu_manual_usuario_id . "',1,'Ver',1," . $return["id"] . ")";
                    $result = $mysqli->query($query);
                }
                $return["user_type_status"] = "new_success";
            }
        } else if ($_POST['type_user'] == 'update') {
            $fecha_update = date("Y-m-d H:i:s");

            $save  = true;
            if ($exists) {
                if ($exists["id"] != $_POST["id"]) {
                    $save = false;
                    $return["error"] = "exists";
                    $return["error_msg"] = "El usuario ya existe!";
                    $return["error_focus"] = "usuario";
                }
            }
            if ($save) {
                $update_command = "
                    UPDATE tbl_usuarios SET
                        usuario = '" . $_POST["usuario"] . "',
                      --  personal_id = '" . $_POST["personal_id"] . "',
                        sistema_id = '" . $_POST["sistema_id"] . "',
                        grupo_id = '" . $_POST["grupo_id"] . "',
                        updated_at = '" . $fecha_update . "',
                        user_updated_id = '" . $login["id"] . "'
                    WHERE id = '" . $_POST["id"] . "'";
                $mysqli->query($update_command);
                if ($_POST["grupo_id"] != 0) {
                    $query = "
                        SELECT 
                            estado,
                            menu_id,
                            boton_id 
                        FROM tbl_permisos WHERE
                            usuario_id=0 AND
                            menu_id != " . $menu_manual_usuario_id . " AND
                            grupo_id = " . $_POST["grupo_id"];
                    $groupPerms = $mysqli->query($query);
                    $mysqli->query("
                        UPDATE tbl_permisos SET 
                            grupo_id = " . $_POST["grupo_id"] . "
                        WHERE menu_id != " . $menu_manual_usuario_id . "  AND usuario_id = " . $_POST["id"]);

                    $mysqli->query("UPDATE tbl_permisos
                        SET estado = 0
                        WHERE manual = 0
                        AND menu_id != " . $menu_manual_usuario_id . "
                        AND  usuario_id = " . $_POST["id"]);

                    //------------------------------------------------------------------------------------------------------------------------------------------------------//
                    //Se actualiza permisos usuario con los permisos del nuevo grupo
                    $grupo_id = $_POST["grupo_id"];
                    $usuario_id = $_POST["id"];
                    $vsql_update = "update tbl_permisos grupo_antiguo
                        inner join (
                            select grupo_id, menu_id, boton_id, estado, manual, usuario_id from tbl_permisos use index(usuario_id) where grupo_id = $grupo_id and usuario_id=0 
                            and menu_id != $menu_manual_usuario_id
                        ) as grupo_nuevo
                        on (grupo_nuevo.menu_id = grupo_antiguo.menu_id and 
                            grupo_nuevo.boton_id = grupo_antiguo.boton_id)
                        set grupo_antiguo.estado =grupo_nuevo.estado
                        where grupo_antiguo.usuario_id=$usuario_id";
                    $vresultado_update = $mysqli->query($vsql_update);
                    //Inserta nuevos permisos que no tenga el usuario respecto del nuevo grupo
                    $vsql_insert = "INSERT INTO tbl_permisos (
                                        grupo_id,
                                        menu_id,
                                        boton_id,
                                        boton_nombre,
                                        estado,
                                        usuario_id)
                                SELECt grupo_nuevo.grupo_id, grupo_nuevo.menu_id, grupo_nuevo.boton_id, 
                                    grupo_nuevo.boton_nombre, grupo_nuevo.estado, $usuario_id
                                from tbl_permisos grupo_antiguo
                                right join (
                                    select id, grupo_id, menu_id, boton_id, boton_nombre, estado, manual, usuario_id
                                    from tbl_permisos use index(usuario_id) where grupo_id = $grupo_id and usuario_id=0 and menu_id != $menu_manual_usuario_id 
                                ) as grupo_nuevo
                                on (grupo_nuevo.menu_id = grupo_antiguo.menu_id and 
                                    grupo_nuevo.boton_id = grupo_antiguo.boton_id)
                                    and grupo_antiguo.usuario_id=$usuario_id
                                where
                                    grupo_antiguo.id is null";

                    $vresultado_insert = $mysqli->query($vsql_insert);
                    //------------------------------------------------------------------------------------------------------------------------------------------------------//

                }
            }

            $update_login_command = "UPDATE 
            tbl_login
            SET logout_datetime = '" . date("Y-m-d H:i:s") . "',
            logout_ip = 'user_changed' 
            WHERE usuario_id = '" . $_POST["id"] . "' 
            AND logout_datetime IS NULL";
            $mysqli->query($update_login_command);

            $return["user_type_status"] = "update_user";
        }
    } else if ($_GET['action'] == "info_user") {
        $query = "SELECT id
        ,usuario
        ,personal_id
        ,grupo_id
        ,sistema_id
        ,estado
        ,ip_restrict
        ,validacion_2fa
        FROM tbl_usuarios
        WHERE id = '" . $_POST['id'] . "'";
        $result = $mysqli->query($query);
        $item = $result->fetch_assoc();
        echo json_encode($item);
        exit();
    } else if ($_GET['action'] == "dismiss_user") {
        $return["data"]["id_switch"] = $_POST['id_switch'];
        $result = $mysqli->query("SELECT personal_id FROM tbl_usuarios where id = " . $_POST['id_switch'] . " LIMIT 1")->fetch_assoc();

        $return["data"]["personal_id"] = "" . $result['personal_id'] . "";
        $result_up = $mysqli->query("UPDATE tbl_personal_apt SET estado = 0 WHERE id = " . $result['personal_id'] . " LIMIT 1");
        $result_up = $mysqli->query("UPDATE tbl_usuarios SET estado = 0 WHERE personal_id = " . $result['personal_id'] . "");
    } else if ($_GET['action'] == "activate_user") {
        $return["data"]["id_switch"] = $_POST['id_switch'];
        $result = $mysqli->query("SELECT personal_id FROM tbl_usuarios where id = " . $_POST['id_switch'] . " LIMIT 1")->fetch_assoc();

        $return["data"]["personal_id"] = $result['personal_id'];
        $result_up = $mysqli->query("UPDATE tbl_personal_apt SET estado = 1 WHERE id = " . $result['personal_id'] . " LIMIT 1");
    } else if ($_GET["action"] == "busca_usuarios_dni") {
        $draw = $_POST['draw'];
        $start = $_POST['start'];

        $arr = explode("\n", trim($_POST['search_dni_textarea']));
        $search_dni_textarea = implode(',', $arr);


        $list_where = "";
        $to_search = $_POST['search'];
        if ($to_search['value'] != "") {
            $list_where .= " AND (
                u.usuario LIKE '%{$to_search['value']}%' OR 
                p.nombre LIKE '%{$to_search['value']}%' OR
                p.apellido_paterno LIKE '%{$to_search['value']}%' OR
                p.dni LIKE '%{$to_search['value']}%' OR
                s.nombre LIKE '%{$to_search['value']}%' OR
                a.nombre LIKE '%{$to_search['value']}%' OR
                c.nombre LIKE '%{$to_search['value']}%' OR
                g.nombre LIKE '%{$to_search['value']}%'
            ) ";
        }

        // Obtener los datos para el DataTable
        $sql = "SELECT 		
		u.id,
        p.dni,
		u.usuario,		
		p.nombre AS personal_nombre,
		p.apellido_paterno,
		s.nombre AS sistema,
		a.nombre AS area,
		c.nombre AS cargo,
		g.nombre AS grupo,
        p.estado AS estado_personal,
		u.estado AS estado_usuario
		FROM tbl_usuarios  u
		LEFT JOIN tbl_personal_apt p ON (p.id = u.personal_id)
		LEFT JOIN tbl_sistemas s ON (s.id = u.sistema_id)
		LEFT JOIN tbl_areas a ON (a.id = p.area_id)
		LEFT JOIN tbl_cargos c ON (c.id = p.cargo_id)
		LEFT JOIN tbl_usuarios_grupos g ON (g.id = u.grupo_id)
		WHERE p.dni IN ($search_dni_textarea)
        $list_where
		";
        $result = $mysqli->query($sql);
        $totalRecords = $result->num_rows;

        // Construir el arreglo de datos para el DataTable
        $data = array();
        while ($row = $result->fetch_assoc()) {
            if ($row['estado_personal'] == 1) {
                $row['estado_personal'] = '<div class="btn btn-sm btn-default text-success btn-estado"><span class="glyphicon glyphicon-ok-circle"></span></div>';
            } else {
                $row['estado_personal'] = '<div class="btn btn-sm btn-default text-danger btn-estado"><span class="glyphicon glyphicon-remove-circle"></span></div>';
            }

            if ($row['estado_usuario'] == 1) {
                $row['estado_usuario'] = '<div class="btn btn-sm btn-default text-success btn-estado"><span class="glyphicon glyphicon-ok-circle"></span></div>';
            } else {
                $row['estado_usuario'] = '<div class="btn btn-sm btn-default text-danger btn-estado"><span class="glyphicon glyphicon-remove-circle"></span></div>';
            }

            $data[] = array(
                $row['id'],
                $row['dni'],
                $row['usuario'],
                $row['personal_nombre'],
                $row['apellido_paterno'],
                $row['sistema'],
                $row['area'],
                $row['cargo'],
                $row['grupo'],
                $row['estado_personal'],
                $row['estado_usuario']
            );
        }
        $return["draw"] = $draw;
        $return["recordsTotal"] = $totalRecords;
        $return["recordsFiltered"] = $totalRecords;
        $return["data"] = $data;
    } else if ($_GET["action"] == "import_users_2fa") {
        if (isset($_FILES['archivo_csv']['name'])) {
            $archivo_nombre = $_FILES['archivo_csv']['name'];
            $archivo_temp = $_FILES['archivo_csv']['tmp_name'];

            $extension = pathinfo($archivo_nombre, PATHINFO_EXTENSION);
            if ($extension != 'csv') {
                $return['error'] = "El archivo debe ser un CSV";
            } else {
                $path = "/var/www/html/export/temp/";
                $filepath = $path . $archivo_nombre;

                if (move_uploaded_file($archivo_temp, $filepath)) {
                    if (($gestor = fopen($filepath, "r")) !== FALSE) {
                        while (($file_data = fgetcsv($gestor)) !== FALSE) {
                            $sql = "UPDATE tbl_personal_apt SET celular_coorporativo = '{$file_data[1]}', id_operador_coorporativo = {$file_data[2]} WHERE dni = '{$file_data[0]}'";
                            $mysqli->query($sql);

                            $sql_update_user_2fa = "UPDATE tbl_usuarios SET validacion_2fa = {$file_data[3]} WHERE personal_id IN (SELECT id FROM tbl_personal_apt WHERE dni = '{$file_data[0]}')";
                            $mysqli->query($sql_update_user_2fa);
                        }
                        fclose($gestor);
                        unlink($filepath);
                        $return['data']['mensaje'] = "Completado";
                    } else {
                        $return['error'] = "Error al leer el archivo CSV";
                    }
                } else {
                    $return['error'] = "Error al subir el archivo csv";
                }
            }
        } else {
            $return['error'] = "Debe seleccionarse un archivo csv";
        }
    }
}
if (isset($_POST)) {
    if (isset($_GET['action']) && $_GET['action'] == "get_permisos_auditoria") {

        $filtros_fecha = '';

        if ($_POST['fecha_inicio'] != '') {
            $filtros_fecha .= 'AND pa.created_at >= "' . $_POST['fecha_inicio'] . '"';
        }
        if ($_POST['fecha_fin'] != '') {
            $filtros_fecha .= 'AND pa.created_at <= "' . $_POST['fecha_fin'] . ' 23:59:59"';
        }


        $query =   "SELECT 
                        date_format(pa.created_at, '%d/%m/%Y %H:%i:%s')  as fecha, pa.usuario_id, u_by.usuario as updated_by_nombre, ms.descripcion as menu, pa.menu_descripcion, pa.boton_nombre, 
                        accion, 
                        if (accion=0, 'Quitado', if (accion=1, 'Agregado', '' )) as accion_nombre, pa.ip
                    FROM tbl_permisos_auditoria pa
                    INNER JOIN tbl_menu_sistemas ms ON pa.menu_id = ms.id
                    INNER JOIN tbl_botones b ON pa.boton_id = b.id
                    INNER JOIN tbl_usuarios u ON pa.usuario_id = u.id
                    INNER JOIN tbl_usuarios u_by ON pa.permiso_updated_by = u_by.id
                    WHERE pa.usuario_id = " . $_POST['usuario_id'] . "
                    $filtros_fecha
                    ";
        $result = $mysqli->query($query);

        $auditorias = $result->fetch_all(MYSQLI_ASSOC);

        // foreach ($auditorias as $key => $value) {
        //     $value['fecha'] = date('d-m-Y H:i:s', strtotime($value['fecha']) );
        // }

        $usuario = "SELECT * FROM  tbl_usuarios WHERE id = " . $_POST['usuario_id'] . ";";
        $usuario = $mysqli->query($usuario);

        $usuario = $usuario->fetch_assoc();

        $result = [
            'usuario' =>  $usuario,
            'auditoria' => $auditorias
        ];

        echo json_encode($result);
        exit();
    }
}
if (isset($_POST["sec_usuario_change_password"])) {
    $data = $_POST["sec_usuario_change_password"];
    $is_password = $mysqli->query("SELECT u.id FROM tbl_usuarios u WHERE u.id = '" . $login["id"] . "' AND u.password_md5 = '" . md5($data["current_password"]) . "'")->fetch_assoc();
    if ($is_password) {
        if ($data["new_password"] == $data["current_password"]) {
            $return["error"] = "no_same_pass";
            $return["error_msg"] = "La nueva contraseña no sebe ser igual a la contraseña anterior!";
            $return["error_focus"] = "new_password";
        } else {
            if ($data["new_password"] == $data["new_repassword"]) {
                $data["new_password"] = trim($data["new_password"]);

                $caracteres_minimos_password = $mysqli->query("SELECT p.id, p.valor FROM tbl_parametros_generales p WHERE p.codigo = 'caracteres_minimos_password'")->fetch_assoc();
                if ($caracteres_minimos_password) {
                    if (strlen($data["new_password"]) < (int) $caracteres_minimos_password['valor']) {
                        $return["error"] = "no_same_pass";
                        $return["error_msg"] = "¡La nueva contraseña debe tener como minimo " . $caracteres_minimos_password['valor'] . " caracteres !";
                        $return["error_focus"] = "new_password";

                        $return["memory_end"] = memory_get_usage();
                        $return["time_end"] = microtime(true);
                        $return["memory_total"] = ($return["memory_end"] - $return["memory_init"]);
                        $return["time_total"] = ($return["time_end"] - $return["time_init"]);
                        print_r(json_encode($return));
                        exit();
                    }
                }

                $password_md5 = md5($data["new_password"]);
                $new_password_sha1 = sha1($data["new_password"]);
                $current_password_sha1 = sha1($data["current_password"]);

                $previous_passwords = $mysqli->query("SELECT p.id, DATE_FORMAT(p.created_at,'%Y-%m-%d') as created_at FROM tbl_password_reset p WHERE p.usuario_id = '" . $login["id"] . "' AND p.password_sha1 = '" . $new_password_sha1 . "' ORDER BY p.id DESC LIMIT 1")->fetch_assoc();
                if ($previous_passwords) { /// Si la nuevo clave ingresada ya fue usada anteriormente

                    $dias_password_reset = $mysqli->query("SELECT p.id, p.valor FROM tbl_parametros_generales p WHERE p.codigo = 'dias_reset_password'")->fetch_assoc();

                    $fecha_actual = date_create(date('Y-m-d'));
                    $fecha_password = date_create($previous_passwords['created_at']);

                    $days = date_diff($fecha_actual, $fecha_password);
                    $diferenciaEnDias = $days->format('%a');

                    if ((int) $dias_password_reset['valor'] >= (int) $diferenciaEnDias) { //
                        $return["error"] = "no_same_pass";
                        $return["error_msg"] = "¡La nueva contraseña ya fue usada anteriormente!";
                        $return["error_focus"] = "new_password";

                        $return["memory_end"] = memory_get_usage();
                        $return["time_end"] = microtime(true);
                        $return["memory_total"] = ($return["memory_end"] - $return["memory_init"]);
                        $return["time_total"] = ($return["time_end"] - $return["time_init"]);
                        print_r(json_encode($return));
                        exit();
                    }
                }

                //Guardar contraseña anterior
                $insert_old_password_command = "INSERT INTO `tbl_password_reset` (`usuario_id`, `password_sha1`, `ip`, `state`, `created_at`) VALUES ('" . $login["id"] . "', '" . $new_password_sha1 . "', '" . $ip . "',  '1', '" . date('Y-m-d H:i:s') . "');";
                $mysqli->query($insert_old_password_command);
                if ($mysqli->error) {
                    print_r($mysqli->error);
                    exit();
                }
                //similar_text("yonathan","yonathb12",$percent);

                $update_password_command = "UPDATE tbl_usuarios SET password_md5 = '" . $password_md5 . "', password_changed = '1' WHERE id = '" . $login["id"] . "' AND password_md5 = '" . md5($data["current_password"]) . "'";
                $mysqli->query($update_password_command);
                $return["curr_login"] = $login;
                if ($mysqli->error) {
                    print_r($mysqli->error);
                    exit();
                }
                $login_update = "UPDATE tbl_login 
                SET logout_datetime = '" . date("Y-m-d H:i:s") . "' ,
                logout_ip = '" . $ip . "'
                WHERE sesion_cookie = '" . $_COOKIE["kurax_login"] . "'";
                $mysqli->query($login_update);
                if ($mysqli->error) {
                    print_r($mysqli->error);
                    exit();
                }
                setcookie("kurax_login", "xxx", time() - 1000, "/", "apuestatotal.com", true, true);
                $login = false;
            } else {
                $return["error"] = "no_new_pass";
                $return["error_msg"] = "¡La nueva contraseña no coincide!";
                $return["error_focus"] = "new_repassword";
            }
        }
    } else {
        $return["error"] = "no_current_pass";
        $return["error_msg"] = "¡La contraseña actual no es correcta!";
        $return["error_focus"] = "current_password";
    }
}
if (isset($_POST["sec_cashier_change_location"])) {
    $data = $_POST["sec_cashier_change_location"];
    $local_id = $data["local_id"];
    $cookie_expire = time() + (3600 * 12);

    if ($_SERVER["SERVER_PORT"] == "443") {
        setcookie("usuario_local_id", $local_id, $cookie_expire, "/", env("APP_URL", "apuestatotal.com"), true, true);
    } else {
        setcookie("usuario_local_id", $local_id, $cookie_expire, "/");
    }
}
if (isset($_POST["sec_usuarios_restore_password"])) {
    $password = substr(md5(date("c")), 5, 10);
    $password_md5 = md5($password);

    $update_password_command = "UPDATE 
	tbl_usuarios 
	SET password_md5 = '" . $password_md5 . "'
	, password_changed = NULL 
	WHERE id = '" . $_POST["sec_usuarios_restore_password"]["id"] . "'";
    $mysqli->query($update_password_command);
    // echo $update_password_command;
    $update_login_command = "UPDATE 
	tbl_login
	SET logout_datetime = '" . date("Y-m-d H:i:s") . "',
	logout_ip = 'pass_restored' 
	WHERE usuario_id = '" . $_POST["sec_usuarios_restore_password"]["id"] . "' 
	AND logout_datetime IS NULL";
    $mysqli->query($update_login_command);
    // $u = $mysqli->query("SELECT ")
    // print_r($_POST);
    $return["new_password"] = $password;

    $result = $mysqli->query("SELECT usuario from tbl_usuarios WHERE id = " . $_POST["sec_usuarios_restore_password"]["id"]);
    $usuario = mysqli_fetch_row($result)[0];

    $_POST["sec_usuarios_restore_password"]["local_id"] = isset($_POST["sec_usuarios_restore_password"]["local_id"]) ? $_POST["sec_usuarios_restore_password"]["local_id"] : 0;
    if (isset($_POST["sec_usuarios_restore_password"]["local_id"]) && isset($_POST["sec_usuarios_restore_password"]["id"])) {
        try {
            require_once("/var/www/html/app/Controllers/UsuarioLog/UsuarioLogController.php");
            $usuarioLogController = new UsuarioLogController();
            $data = [
                "usuario_id" => $_POST["sec_usuarios_restore_password"]["id"],
                "id" => $_POST["sec_usuarios_restore_password"]["local_id"],
            ];
            $dataMerge = array_merge($data, ["action" => "change_pass", "user_id" => $login["id"], "ip" => $ip]);
            $result = $usuarioLogController->store($dataMerge);
        } catch (\Throwable $th) {
        }
    }


    if (isset($_POST["sec_usuarios_restore_password"]["correo"]) && $_POST["sec_usuarios_restore_password"]["correo"]) {
        try {
            include('../sys/mailer/class.phpmailer.php');
            $mail = new PHPMailer(true);
            $mail->IsSMTP();
            $mail->SMTPDebug  = 1;
            $mail->SMTPAuth   = true;
            $mail->Host       = "smtp.gmail.com";
            $mail->Port       = 465;
            $mail->SMTPSecure = "ssl";
            $mail->AddAddress($_POST["sec_usuarios_restore_password"]["correo"]);
            $mail->Username   = env('MAIL_GESTION_USER');
            $mail->Password   = env('MAIL_GESTION_PASS');
            $mail->FromName = "Apuesta Total";
            $mail->Subject    = "Restablecer Password";
            $mail->Body = '<div style="margin: auto; right: 0; left: 0; width: 750px;"><h3>Restablecer Contraseña</h3><p>Su contraseña fue restablecida.</p><h4>Nuevos datos de acceso</h4><p><b>Usuario:</b> ' . $usuario . '<br/><b>Contraseña:</b> ' . $password . '</p><p>Haga click <a href="https://gestion.apuestatotal.com">en este enlace</a> para poder conectarse al sistema</p></div>';
            $mail->isHTML(true);
            if ($mail->Send()) $return["email_sent"] = "ok";
        } catch (phpmailerException $ex) {
            $return["email_error"] = $mail->ErrorInfo;
            $insert_data["is_error"] = $mail->ErrorInfo;
        }
    }
}
if (isset($_POST["sec_usuarios_save"])) {
    $data = $_POST["sec_usuarios_save"];
    $exists = $mysqli->query("SELECT id FROM tbl_usuarios WHERE usuario = '" . $data["usuario"] . "'")->fetch_assoc();
    if ($data["id"] == "new") {
        if ($exists) {
            $return["error"] = "exists";
            $return["error_msg"] = "El usuario ya existe!";
            $return["error_focus"] = "usuario";
        } else {
            $gid = $data["grupo_id"] != 0 ? $data["grupo_id"] : "null";
            $query = "INSERT INTO tbl_usuarios 
			(usuario,personal_id,sistema_id,grupo_id,estado)
			VALUES ('" . $data["usuario"] . "','" . $data["personal_id"] . "','" . $data["sistema_id"] . "'," . $gid . ",'1')";

            $mysqli->query($query);
            if ($mysqli->error) {
                print_r($mysqli->error);
                echo "\n";
                echo $query;
                exit();
            }
            $return["id"] = $mysqli->insert_id;
            if ($data["grupo_id"] != 0) {
                $mysqli->query("DELETE FROM tbl_permisos WHERE usuario_id =" . $return["id"]);
                $query = "INSERT INTO tbl_permisos 
				(grupo_id,menu_id,boton_id,boton_nombre,estado,usuario_id)
				SELECT grupo_id,menu_id,boton_id,boton_nombre,estado,'" . $return["id"] . "' FROM tbl_permisos
				WHERE grupo_id = " . $data["grupo_id"] . " AND usuario_id=0";
                $mysqli->query($query);
            }
            // $return["usuario_id"] = $mysqli->insert_id;
        }
    } else {
        $save  = true;
        if ($exists) {
            if ($exists["id"] != $data["id"]) {
                $save = false;
                $return["error"] = "exists";
                $return["error_msg"] = "El usuario ya existe!";
                $return["error_focus"] = "usuario";
            }
        }
        if ($save) {
            $update_command = "
				UPDATE tbl_usuarios SET
					usuario = '" . $data["usuario"] . "',
					personal_id = '" . $data["personal_id"] . "',
					sistema_id = '" . $data["sistema_id"] . "',
					grupo_id = '" . $data["grupo_id"] . "'
				WHERE id = '" . $data["id"] . "'";
            $mysqli->query($update_command);
            if ($data["grupo_id"] != 0) {
                $menu_manual_usuario_id = getMenuBySecId('manual_usuarios');
                $query = "
					SELECT 
						estado,
						menu_id,
						boton_id 
					FROM tbl_permisos WHERE
                        menu_id != " . $menu_manual_usuario_id . " AND
						usuario_id=0 AND
						grupo_id = " . $data["grupo_id"];
                $groupPerms = $mysqli->query($query);
                $mysqli->query("
					UPDATE tbl_permisos SET 
						grupo_id = " . $data["grupo_id"] . "
					WHERE menu_id !=  " . $menu_manual_usuario_id . " AND usuario_id = " . $data["id"]);

                $mysqli->query("UPDATE tbl_permisos
					SET estado = 0
					WHERE manual = 0
                    AND menu_id != " . $menu_manual_usuario_id . "
					AND  usuario_id = " . $data["id"]);

                foreach ($groupPerms as $perm) {
                    $getPerm = $mysqli->query("
						SELECT manual FROM tbl_permisos
						WHERE 
							menu_id=" . $perm["menu_id"] . " AND
							boton_id=" . $perm["boton_id"] . " AND
                            menu_id != " . $menu_manual_usuario_id . " AND
							usuario_id=" . $data["id"]);
                    if ($getPerm->num_rows > 0) {
                        $update_perms = "
							UPDATE tbl_permisos SET 
								estado = " . $perm["estado"] . "
							WHERE manual=0 AND
								menu_id=" . $perm["menu_id"] . " AND
								boton_id=" . $perm["boton_id"] . " AND
								usuario_id=" . $data["id"] . " AND menu_id != " . $menu_manual_usuario_id;
                        $mysqli->query($update_perms);
                    } else {
                        //foreach ($getPerm as $getManual) $manual = $getManual["manual"];
                        //if(isset($manual) && $manual == 0){
                        $queryInsertPerm = "INSERT INTO tbl_permisos (
							grupo_id,
							menu_id,
							boton_id,
							boton_nombre,
							estado,
							usuario_id)
							VALUES (
							" . $data["grupo_id"] . ",
							" . $perm["menu_id"] . ",
							" . $perm["boton_id"] . ",
							(SELECT nombre FROM tbl_menu_sistemas_botones WHERE boton=" . addslashes($perm["boton_id"]) . " AND menu_id=" . addslashes($perm["menu_id"]) . " LIMIT 1),
							" . $perm["estado"] . ",
							" . $data["id"] . ")";
                        $mysqli->query($queryInsertPerm);
                        //}
                    }
                }
            }
        }
        $update_login_command = "UPDATE 
		tbl_login
		SET logout_datetime = '" . date("Y-m-d H:i:s") . "',
		logout_ip = 'user_changed' 
		WHERE usuario_id = '" . $data["id"] . "' 
		AND logout_datetime IS NULL";
        $mysqli->query($update_login_command);
    }
}

$return["memory_end"] = memory_get_usage();
$return["time_end"] = microtime(true);
$return["memory_total"] = ($return["memory_end"] - $return["memory_init"]);
$return["time_total"] = ($return["time_end"] - $return["time_init"]);
print_r(json_encode($return));
