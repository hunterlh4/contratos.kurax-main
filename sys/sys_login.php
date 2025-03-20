<?php

$login = false;
$logout = false;
$login_tipo = "usuario";
$cookie_expire = time() + (3600 * 12);
$login_return = false;
$session_array = array();
$usuario_permisos = array();

require_once('/var/www/html/sys/sys_login_sms_validation.php');
//setcookie("kurax_login", "xxx", $cookie_expire,"/");
if (isset($_COOKIE["kurax_login"])) {

    $value_cookie = mysqli_real_escape_string($mysqli, $_COOKIE["kurax_login"]);
    $stmt = $mysqli->prepare(
        "SELECT 
								u.id
								, IF(u.personal_id,p.nombre,u.usuario) AS nombre
								, IF(u.personal_id,p.apellido_paterno,'') AS apellido_paterno
								, p.area_id
								, p.cargo_id
								, p.zona_id
								, p.correo
								, p.dni
								, p.razon_social_id AS empresa_id
								, l.logout_datetime
								, l.login_ip
								, l.logout_ip
								, l.sesion_cookie
								, u.usuario
								, u.grupo_id
								, u.estado
								, u.password_changed
                                , u.ip_restrict
							FROM tbl_login l
							LEFT JOIN tbl_usuarios u ON (u.id = l.usuario_id)
							LEFT JOIN tbl_personal_apt p ON (p.id = u.personal_id)
							WHERE 
								l.sesion_cookie = ?
							AND l.expire_datetime > '" . date("Y-m-d H:i:s") . "'
							-- AND l.logout_datetime IS NULL
							AND l.tipo = ?"
    );
    $stmt->bind_param('ss', $value_cookie, $login_tipo);
    $stmt->execute();

    if ($mysqli->error) {
        print_r($mysqli->error);
        exit();
    }
    $login_query = $stmt->get_result();
    $login = $login_query->fetch_assoc();
    if ($login) {


        if ($login["ip_restrict"] == 1) {
            require_once "global_config.php";
            global $ip;

            $where_grupo = $login["grupo_id"] ? "AND grupo_id IN (" . $login["grupo_id"] . " ,0 ) " : " AND grupo_id = 0";
            $login_ip_whitelist = $mysqli->query("
                        SELECT id
                        FROM tbl_login_ip_whitelist
                        WHERE ip = '$ip'
                        $where_grupo
                        AND estado = 1
                    ")->fetch_assoc();
            if (!$login_ip_whitelist) {
                $login_return = "restrict";
                if ($_SERVER["SERVER_PORT"] == "443") {
                    setcookie("kurax_login", "xxx", time() - 1000, "/", env("APP_URL", "apuestatotal.com"), true, true);
                } else {
                    setcookie("kurax_login", "xxx", time() - 1000, "/");
                }
                $login = false;
                $sec_id = "login";
                header("Location: ./");
                exit();
            }
        }

        if ($login["logout_datetime"]) {
            $logout = $login;
            $login = false;
            if ($_SERVER["SERVER_PORT"] == "443") {
                setcookie("kurax_login", "xxx", time() - 1000, "/", env("APP_URL", "apuestatotal.com"), true, true);
            }
            setcookie("kurax_login", "xxx", time() - 1000, "/");
        } else {
            if ($login["estado"]) {
                // $usuario_permisos = array();
                // $usuario_permisos_query = $mysqli->query("SELECT id,menu_id,boton FROM tbl_usuario_permisos WHERE usuario_id = '".$login["id"]."' AND estado = '1'");
                // 	while($usu_per=$usuario_permisos_query->fetch_assoc()){
                // 		$usuario_permisos[$usu_per["menu_id"]][]=$usu_per["boton"];
                // 	}
                $usuario_permisos = array();
                $usuario_permisos_query = $mysqli->query("SELECT p.usuario_id, p.menu_id, b.boton
														FROM tbl_permisos p
														LEFT JOIN tbl_botones b ON (p.boton_id = b.id)
														WHERE p.usuario_id = '" . $login["id"] . "' AND p.estado = '1'");
                // if ($result->num_rows > 0) {
                // $i = 0;
                while ($usu_per = $usuario_permisos_query->fetch_assoc()) {
                    $usuario_permisos[$usu_per["menu_id"]][] = $usu_per["boton"];
                    // $i++;
                }
                // }else{
                // echo "0 results";
                // }
                $login["usuario_locales"] = [];
                if (array_key_exists("id", $login)) {
                    $usuario_locales_command = "SELECT local_id FROM tbl_usuarios_locales WHERE usuario_id = '" . $login["id"] . "' AND estado = '1'";
                    $usuario_locales_query = $mysqli->query($usuario_locales_command);
                    if ($mysqli->error) {
                        print_r($mysqli->error);
                    }
                    while ($ul = $usuario_locales_query->fetch_assoc()) {
                        $login["usuario_locales"][] = $ul["local_id"];
                    }
                }

                //if ($login["area_id"] == 21 && in_array($login["cargo_id"],[4,5])) { // Cashier // Editado por Torito
                if (isset($_COOKIE["usuario_local_id"])) {
                    if (in_array($_COOKIE["usuario_local_id"], $login["usuario_locales"])) {
                        $login["local_id"] = $_COOKIE["usuario_local_id"];

                        $locales_query = $mysqli->query("SELECT id, nombre, cc_id FROM tbl_locales WHERE id = $_COOKIE[usuario_local_id]");
                        $local = $locales_query->fetch_assoc();
                        if ($local) {
                            $login["local_name"] = "[$local[cc_id]] $local[nombre]";
                            $login["cc_id"] = $local["cc_id"];
                        }
                    } else {
                        $login["local_id"] = '';
                        $login["cc_id"] = '';
                        //session_destroy();
                    }
                } else {
                    $login["local_id"] = '';
                    $login["cc_id"] = '';
                }
                //}

                // setcookie("kurax_login", $_COOKIE["kurax_login"], $cookie_expire,"/");
                if ($_SERVER["SERVER_PORT"] == "443") {
                    setcookie("kurax_login", $_COOKIE["kurax_login"], $cookie_expire, "/", env("APP_URL", "apuestatotal.com"), true, true);
                } else {
                    setcookie("kurax_login", $_COOKIE["kurax_login"], $cookie_expire, "/");
                }
            } else {
                // $login_return["username"]=$login["usuario"];
                $login_return = "inactivo";
                $login_usuario = $login["usuario"];
                $login = false;
                setcookie("kurax_login", "xxx", time() - 1000, "/");
                setcookie("usuario_local_id", "xxx", time() - 1000, "/");
                if ($_SERVER["SERVER_PORT"] == "443") {
                    setcookie("kurax_login", "xxx", time() - 1000, "/", env("APP_URL", "apuestatotal.com"), true, true);
                    setcookie("usuario_local_id", "xxx", time() - 1000, "/", env("APP_URL", "apuestatotal.com"), true, true);
                }
                // exit();
            }
        }
    } else {
        setcookie("kurax_login", "xxx", time() - 1000, "/");
        setcookie("usuario_local_id", "xxx", time() - 1000, "/");
        if ($_SERVER["SERVER_PORT"] == "443") {
            setcookie("kurax_login", "xxx", time() - 1000, "/", env("APP_URL", "apuestatotal.com"), true, true);
            setcookie("usuario_local_id", "xxx", time() - 1000, "/", env("APP_URL", "apuestatotal.com"), true, true);
        }
    }
}
if (isset($_GET["action"])) {
    if ($_GET["action"] == "logout") {
        ////snacks_disconnect($_COOKIE["kurax_login"]);

        if (isset($_COOKIE["kurax_login"])) {
            include("db_connect.php");
            $login_update = "UPDATE tbl_login 
							SET logout_datetime = '" . date("Y-m-d H:i:s") . "' ,
							logout_ip = '" . $ip . "'
							WHERE sesion_cookie = '" . $_COOKIE["kurax_login"] . "'";
            // echo $login_update;
            $mysqli->query($login_update);
            if ($mysqli->error) {
                print_r($mysqli->error);
                exit();
            }
        }
        setcookie("kurax_login", "xxx", time() - 1000, "/", env("APP_URL", "apuestatotal.com"), true, true);
        setcookie("kurax_login", "xxx", time() - 1000, "/at_liquidaciones_v2/sys/", env("APP_URL", "apuestatotal.com"), true, true);
        setcookie("kurax_login", "xxx", time() - 1000, "/sys/", env("APP_URL", "apuestatotal.com"), true, true);
        setcookie("kurax_login", "xxx", time() - 1000, "/sys", env("APP_URL", "apuestatotal.com"), true, true);
        setcookie("usuario_local_id", "xxx", time() - 1000, "/", env("APP_URL", "apuestatotal.com"), true, true);
        $login = false;
        session_destroy();
        header("Location: ./");
    }
}
//print_r($login);
if (isset($_POST["action"])) {
    //print_r($_POST);
    if ($_POST["action"] == "login") {
        $session_array = array();
        $user_name_text = trim($_POST["username"]);
        $regexPuntos = '/\.{2,}/';
        if (!preg_match('/^[a-zA-Z.ñÑ\d]*$/', $user_name_text)) {
            $login_return = "nouservalid";
            if ($_SERVER["SERVER_PORT"] == "443") {
                setcookie("kurax_login", "xxx", time() - 1000, "/", env("APP_URL", "apuestatotal.com"), true, true);
            } else {
                setcookie("kurax_login", "xxx", time() - 1000, "/");
            }
            $login = false;
        } else {
            if (preg_match($regexPuntos, $user_name_text)) {
                $login_return = "nouservalid";
                if ($_SERVER["SERVER_PORT"] == "443") {
                    setcookie("kurax_login", "xxx", time() - 1000, "/", env("APP_URL", "apuestatotal.com"), true, true);
                } else {
                    setcookie("kurax_login", "xxx", time() - 1000, "/");
                }
                $login = false;
            } else {
                $session_array["username"] = $user_name_text;
                $session_array["password"] = md5($_POST["password"]);
                if (isset($_POST["login_HTTP_REFERER"])) {
                    $session_array["login_HTTP_REFERER"] = $_POST['login_HTTP_REFERER'];
                } else {
                    $session_array["login_HTTP_REFERER"] = $_SERVER['HTTP_REFERER'];
                }

                $user = $mysqli->query("
					SELECT 
						u.id
						,u.usuario
						,p.dni
						,CONCAT(p.nombre, ' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS name
						,p.area_id
						,p.correo
						,u.estado
						,p.cargo_id
						,u.password_md5
						,u.grupo_id
						,u.ip_restrict
					FROM tbl_usuarios u
					LEFT JOIN tbl_personal_apt p ON (p.id = u.personal_id)
					WHERE u.usuario = '" . $session_array["username"] . "'
				")->fetch_assoc();

                if ($user) {
                    /*ip_restrict*/
                    if ($user["ip_restrict"] == 1) {
                        require_once "global_config.php";
                        global $ip;
                        $where_grupo = $user["grupo_id"] ? "AND grupo_id IN (" . $user["grupo_id"] . " ,0 ) " : " AND grupo_id = 0";
                        $login_ip_whitelist = $mysqli->query("
							SELECT id
							FROM tbl_login_ip_whitelist
							WHERE ip = '$ip'
							$where_grupo
							AND estado = 1
						")->fetch_assoc();
                        if (!$login_ip_whitelist) {
                            $login_return = "restrict";

                            $reason = "Ip restringida";
                            $stmt = $mysqli->prepare("INSERT INTO tbl_login_log (user_id, ip, reason) VALUES (?, ?, ?)");
                            $stmt->bind_param("iss", $user["id"], $ip, $reason);
                            $stmt->execute();
                            $stmt->close();


                            if ($_SERVER["SERVER_PORT"] == "443") {
                                setcookie("kurax_login", "xxx", time() - 1000, "/", env("APP_URL", "apuestatotal.com"), true, true);
                            } else {
                                setcookie("kurax_login", "xxx", time() - 1000, "/");
                            }
                            $login = false;
                            $sec_id = "login";
                            return;
                        }
                    }
                    /**/
                    if ($user["password_md5"] == $session_array["password"]) {

                        $verification_id = 0;

                        if (fncActiveValidationCodeSmsLogin() && fnc2FactoresValidation($user['id'])) {
                            $user_estado = (int) $user["estado"];
                            if (isset($_POST["code"]) && empty($_POST["code"]) && $user_estado === 1) {
                                $_SESSION["tmp_user"] = $session_array["username"];
                                $_SESSION["tmp_pass"] = $_POST["password"];
                                $_SESSION["tmp_code"] = "no_code";
                                fncSendValidationCodeSmsLogin($_POST["username"]);
                                //update tbl_usuario_codigo_verificacion
                                header("Location: ./");
                                exit();
                            }

                            $verification_id = fncGetValidationCodeSmsLogin($user['id'], $_POST["code"]);
                            if (!empty($_POST["code"]) && $verification_id == 0) {
                                header("Location: ./");
                                exit();
                            }
                        }

                        $sesion_cookie = md5($user["id"] . microtime());

                        $expire_datetime = date("Y-m-d H:i:s", $cookie_expire);

                        $login_update = "UPDATE tbl_login 
										SET logout_datetime = '" . date("Y-m-d H:i:s") . "',
										logout_ip = '" . $ip . "'
										WHERE tipo = '" . $login_tipo . "'
										AND usuario_id = '" . $user["id"] . "'
										AND expire_datetime > '" . date("Y-m-d H:i:s") . "'
										AND logout_datetime IS NULL";

                        // echo $login_update;
                        $mysqli->query($login_update);
                        if ($mysqli->error) {
                            print_r($mysqli->error);
                            exit();
                        }

                        $login_insert = "
							INSERT INTO tbl_login (
								sesion_cookie,
								tipo,
								usuario_id,
								login_datetime,
								expire_datetime,
								login_ip
							) 
							VALUES (
								'" . $sesion_cookie . "',
								'" . $login_tipo . "',
								'" . $user["id"] . "',
								'" . $datetime . "',
								'" . $expire_datetime . "',
								'" . $ip . "'
						)";

                        $mysqli->query($login_insert);

                        $last_login_id = $mysqli->insert_id;

                        if ($verification_id && $last_login_id) {
                            $updating_query = "UPDATE tbl_usuario_codigo_verificacion SET cod_valido = '{$last_login_id}' WHERE id = '{$verification_id}'";
                            $mysqli->query($updating_query);
                        }

                        // fncUpdatePasswordChanged($user['id']); // pedir cambio de contraseña por un tiempo determinado por un parametro

                        if ($_SERVER["SERVER_PORT"] === "443") {
                            setcookie("kurax_login", $sesion_cookie, $cookie_expire, "/", env("APP_URL", "apuestatotal.com"), true, true);
                            //snacks_connect($user);
                        } else {
                            setcookie("kurax_login", $sesion_cookie, $cookie_expire, "/");
                        }
                        //snacks_connect($user);

                        header("Location: " . $session_array["login_HTTP_REFERER"]);
                    } else {
                        $login_return = "nopass";

                        $reason = "Contraseña Incorrecta";
                        $stmt = $mysqli->prepare("INSERT INTO tbl_login_log (user_id, ip, reason) VALUES (?, ?, ?)");
                        $stmt->bind_param("iss", $user["id"], $ip, $reason);
                        $stmt->execute();
                        $stmt->close();

                        if ($_SERVER["SERVER_PORT"] === "443") {
                            setcookie("kurax_login", "xxx", time() - 1000, "/", env("APP_URL", "apuestatotal.com"), true, true);
                        } else {
                            setcookie("kurax_login", "xxx", time() - 1000, "/");
                        }
                        $login = false;
                    }
                } else {
                    $login_return = "nouser";
                }
            }
        }
    }
    if ($_POST["action"] == "logout") {
        //snacks_disconnect($_COOKIE["kurax_login"]);

        if (isset($_COOKIE["kurax_login"])) {
            $login_update = "UPDATE tbl_login 
							SET logout_datetime = '" . date("Y-m-d H:i:s") . "' ,
							logout_ip = '" . $ip . "'
							WHERE sesion_cookie = '" . $_COOKIE["kurax_login"] . "'";
            // echo $login_update;
            $mysqli->query($login_update);
            if ($mysqli->error) {
                print_r($mysqli->error);
                exit();
            }
        }
        setcookie("kurax_login", "xxx", time() - 1000, "/", env("APP_URL", "apuestatotal.com"), true, true);
        setcookie("kurax_login", "xxx", time() - 1000, "/at_liquidaciones_v2/sys/", env("APP_URL", "apuestatotal.com"), true, true);
        setcookie("kurax_login", "xxx", time() - 1000, "/sys/", env("APP_URL", "apuestatotal.com"), true, true);
        setcookie("kurax_login", "xxx", time() - 1000, "/sys", env("APP_URL", "apuestatotal.com"), true, true);
        setcookie("usuario_local_id", "xxx", time() - 1000, "/", env("APP_URL", "apuestatotal.com"), true, true);
        $login = false;
    }
}

if ($login) {
} else {
    $sec_id = "login";
}
// echo "login:"; print_r($login);
