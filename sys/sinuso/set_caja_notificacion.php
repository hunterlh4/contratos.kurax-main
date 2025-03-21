<?php
include("db_connect.php");
include("sys_login.php");
require_once '/var/www/html/env.php';
require_once '/var/www/html/sys/mailer/class.phpmailer.php';

function sendEmail_notificacionNoVenta($body, $local, $usuario, $tipo, $estadoGestion, $zona_id): bool
{
    try {
        $mail = new PHPMailer(true);
        $mail->IsSMTP();

        $mail->Host = env('MAIL_GESTION_NET_HOST');
        $mail->SMTPAuth = true;
        $mail->Username = env('MAIL_GESTION_NET_USER');
        $mail->Password = env('MAIL_GESTION_NET_PASS');
        $mail->SMTPSecure = "ssl";
        $mail->Port = 465;
        $mail->CharSet = 'utf-8';
        $mail->SMTPKeepAlive 	= true;
        //$mail->SMTPDebug = 1;
        $mail->From = 'gestion@testtest.apuestatotal.net';
        $mail->FromName = env('MAIL_GESTION_NET_NAME');

        if ($estadoGestion === "TEST") {
            $subject = "TEST REPORTE DE NO VENTA - " . $local . " - " . $usuario . " - " . $tipo;
            $correos = obtener_correos_test();
            foreach ($correos as $correo) {
                $mail->AddAddress($correo);
            }
        } else {
            $subject = "REPORTE DE NO VENTA - " . $local . " - " . $usuario . " - " . $tipo;
            $correos = obtener_correos('notificacion_no_venta', $zona_id);
            foreach ($correos as $correo) {
                $mail->AddAddress($correo);
            }
        }

        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->isHTML();
        return $mail->send();
    } catch (phpmailerException $e) {
        $mail = new PHPMailer(true);
        $mail->IsSMTP();
        $mail->CharSet = 'utf-8';
        $mail->SMTPDebug = 1;
        $mail->SMTPAuth = true;
        $mail->Host = env('MAIL_GESTION_NET_HOST');
        $mail->Port = 465;
        $mail->From = 'gestion@testtest.apuestatotal.net';
        $mail->SMTPSecure = "ssl";

        $mail->Username = env('MAIL_GESTION_NET_USER');
        $mail->Password = env('MAIL_GESTION_NET_PASS');
        $mail->FromName = env('MAIL_GESTION_NET_NAME');

        if ($estadoGestion === "TEST") {
            $correos = obtener_correos_test();
        } else {
            $correos = obtener_correos_parametros_generales_exception('excepcion_cron_maillist');
        }

        foreach ($correos as $correo) {
            $mail->AddAddress($correo);
        }

        $mail->Subject = "Error de envio de emails :: Alertas phpmailerException";
        $mail->Body = $e->errorMessage();
        return $mail->send();
    } catch (Exception $e) {
        $mail = new PHPMailer(true);
        $mail->IsSMTP();
        $mail->CharSet = 'utf-8';
        $mail->SMTPDebug = 1;
        $mail->SMTPAuth = true;
        $mail->Host = env('MAIL_GESTION_NET_HOST');
        $mail->Port = 465;
        $mail->From = 'gestion@testtest.apuestatotal.net';
        $mail->SMTPSecure = "ssl";

        if ($estadoGestion === "TEST") {
            $correos = obtener_correos_test();
        } else {
            $correos = obtener_correos_parametros_generales_exception('excepcion_cron_maillist');
        }

        foreach ($correos as $correo) {
            $mail->AddAddress($correo);
        }

        $mail->Username = env('MAIL_GESTION_NET_USER');
        $mail->Password = env('MAIL_GESTION_NET_PASS');
        $mail->FromName = env('MAIL_GESTION_NET_NAME');

        $mail->Subject = "Error de envio de emails :: Kasnet";
        $mail->Body = $e->getMessage();
        return $mail->send();
    }
}

function obtener_correos($metodo, $zona_id)
{
    global $mysqli;
    $correos = [];

    $sel_query = $mysqli->query("SELECT p.correo
                                    FROM tbl_personal_apt AS p
                                    INNER JOIN tbl_usuarios AS u ON p.id = u.personal_id 
                                    INNER JOIN tbl_permisos AS pm ON pm.usuario_id = u.id 
                                    INNER JOIN tbl_botones AS b ON b.id = pm.boton_id 
                                    WHERE u.estado = 1 
                                    AND p.estado = 1 
                                    AND p.zona_id='" . $zona_id . "'
                                    AND b.boton = '" . $metodo . "'
                                    AND p.correo IS NOT NULL;");
    while ($sel = $sel_query->fetch_assoc()) {
        array_push($correos, $sel['correo']);
    }

    return $correos;
}


function obtener_correos_parametros_generales_exception($metodo)
{
    global $mysqli;
    $correos = [];

    $sel_query = $mysqli->query("SELECT codigo,valor FROM tbl_parametros_generales WHERE codigo = '" . $metodo . "' AND valor IS NOT NULL");
    while ($sel = $sel_query->fetch_assoc()) {
        $correos_en_registro = explode(',', $sel['valor']);
        $correos = array_merge($correos, $correos_en_registro);
    }

    return $correos;
}

function obtener_correos_test(): array
{
    return [
        'gorqui.chavez@testtest.kurax.dev',
        'neil.flores@testtest.kurax.dev',
        'luis.chambilla@testtest.kurax.dev',
        'francisco.illescas@testtest.kurax.dev'
    ];
}

?>
