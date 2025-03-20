<?php
date_default_timezone_set("America/Lima");

$result = array();
include "db_connect.php";
include "sys_login.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//////////   FUNCIONES PARA ZONAS


if (isset($_POST["accion"]) && $_POST["accion"] === "conci_anulacion_guardar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");

    if ((int)$usuario_id > 0) {
        (int)$transaccion_calimaco_id = $_POST["transaccion_calimaco_id"];
		$motivo = $_POST["motivo"];
        $tipo_id = $_POST["tipo_id"];
        $etapa_id = 1;
        if ((int)$transaccion_calimaco_id > 0) {
			$error = '';

            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            $sql_insert_solicitud_anulacion = "
                INSERT INTO tbl_conci_anulacion_solicitud (
                    motivo,
                    tipo_id,
                    etapa_id,
                    transaccion_id,
                    status,    
                    created_at,
                    user_created_id
                )  
                VALUES (?,?,?,?,1,?,?)
            ";

            $stmtSolicitudAnulacion = $mysqli->prepare($sql_insert_solicitud_anulacion);


            if ($stmtSolicitudAnulacion) {
                $stmtSolicitudAnulacion->bind_param("siiisi", 
                                        $motivo,
                                        $tipo_id,   
                                        $etapa_id,
                                        $transaccion_calimaco_id,                     
                                        $fecha,
                                        $usuario_id
                                    );
                try {
                    $stmtSolicitudAnulacion->execute();
                    $anulacion_id = $mysqli->insert_id;
                    sendEmailSolicitudAnulacion($anulacion_id, $motivo, $fecha);

                    $result["http_code"] = 200;
                    $result["status"] = "Datos obtenidos de gestión.";
                } catch (Exception $e) {
                    $result["http_code"] = 400;
                    $result["error"] = "Error al ejecutar la consulta: " . $e->getMessage();
                }

                $stmtSolicitudAnulacion->close();
            } else {
                $result["http_code"] = 400;
                $result["error"] = "Error en la preparación de la consulta: " . $mysqli->error;
            }

		}else{
            $result["http_code"] = 400;
            $result["error"] = "La transacción no esta activa. Contactarse con soporte";
        }
    } else {
        $result["http_code"] = 400;
        $result["error"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña.";
    }

    echo json_encode($result);
    exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_anulacion_editar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");

    if ((int)$usuario_id > 0) {
        (int)$id = $_POST["id"];
		$motivo = $_POST["motivo"];
        $tipo_id = $_POST["tipo_id"];
        $etapa_id = 1;
        if ((int)$id > 0) {
			$error = '';

            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            $sql_update_solicitud_anulacion = "
                UPDATE tbl_conci_anulacion_solicitud 
                SET
                    motivo = ?,
                    tipo_id = ?,
                    updated_at = ?,
                    user_updated_id =?
                
                WHERE id = ?
            ";

            $stmtSolicitudAnulacion = $mysqli->prepare($sql_update_solicitud_anulacion);


            if ($stmtSolicitudAnulacion) {
                $stmtSolicitudAnulacion->bind_param("sisii", 
                                        $motivo,
                                        $tipo_id,   
                                        $fecha,
                                        $usuario_id,
                                        $id
                                    );
                try {
                    $stmtSolicitudAnulacion->execute();

                    $result["http_code"] = 200;
                    $result["status"] = "Datos obtenidos de gestión.";
                } catch (Exception $e) {
                    $result["http_code"] = 400;
                    $result["error"] = "Error al ejecutar la consulta: " . $e->getMessage();
                }

                $stmtSolicitudAnulacion->close();
            } else {
                $result["http_code"] = 400;
                $result["error"] = "Error en la preparación de la consulta: " . $mysqli->error;
            }

		}else{
            $result["http_code"] = 400;
            $result["error"] = "La transacción no esta activa. Contactarse con soporte";
        }
    } else {
        $result["http_code"] = 400;
        $result["error"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña.";
    }

    echo json_encode($result);
    exit();
}


if (isset($_POST["accion"]) && $_POST["accion"] === "conci_anulacion_cambiar_etapa") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");
    $result = array(); // Inicializar el array result

    if ((int)$usuario_id > 0) {
        $solicitud_anulacion_id = isset($_POST["solicitud_anulacion_id"]) ? (int)$_POST["solicitud_anulacion_id"] : 0;
        $etapa_id = isset($_POST["etapa_id"]) ? (int)$_POST["etapa_id"] : 0;
        $etapa_nombre = isset($_POST["etapa_nombre"]) ? (int)$_POST["etapa_nombre"] : 0;

        if ($solicitud_anulacion_id > 0 && $etapa_id > 0) {

            if($etapa_id == 3){

                //  Actualizar solicitud

                $query_update_solicitud = "UPDATE tbl_conci_anulacion_solicitud 
                                SET 
                                    etapa_id = ?,
                                    user_authorized_id = ?,
                                    authorized_at = ?,
                                    user_updated_id = ?,
                                    updated_at = ?
                                WHERE 
                                    id = ?";
                $stmt = $mysqli->prepare($query_update_solicitud);
                if ($stmt) {

                    $stmt->bind_param("iisisi", $etapa_id, $usuario_id, $fecha, $usuario_id, $fecha, $solicitud_anulacion_id);
                    if ($stmt->execute()) {
                        $stmt->close();
                        $result["http_code"] = 200;

                    } else {
                        $result["http_code"] = 400;
                        $result["error"] = "Error al ejecutar la consulta de actualización de comprobante: " . $mysqli->error;
                    }
                }else{
                    $result["http_code"] = 500;
                    $result["error"] = "Error al preparar la consulta: " . $mysqli->error;
                }

                $selectQuery = "SELECT 
                    ct.id,ct.transaccion_id,IFNULL(p.monto_anulado,0),IFNULL(p.comision_anulada,0),IFNULL(p.anulados_count,0),ct.periodo_id
                    FROM tbl_conci_anulacion_solicitud ans
                    LEFT JOIN tbl_conci_calimaco_transaccion ct
                    ON ans.transaccion_id = ct.id
                    LEFT JOIN tbl_conci_periodo p
                    ON ct.periodo_id = p.id
                    WHERE ans.id = ?
                    LIMIT 1";
    
                $selectStmt = $mysqli->prepare($selectQuery);
                $selectStmt->bind_param("i", $solicitud_anulacion_id);
                $selectStmt->execute();
                $selectStmt->store_result();
    
                if ($selectStmt->num_rows > 0) {
    
                    $selectStmt->bind_result($transaccion_calimaco_id, $transaccion_id, $monto_anulado,$comision_anulada, $anulados_count,$periodo_id);
                    if($selectStmt->fetch()){
                        $selectStmt->close();

                        fn_conci_updateTransaccion($mysqli,$transaccion_calimaco_id,$usuario_id, $fecha);
                    }else{
                        $selectStmt->close();
                        $result["http_code"] = 400;
                        $result["error"] = "Error al ejecutar la consulta";
                    }
        
                }else{
                    $result["http_code"] = 400;
                    $result["error"] = "Sin nombre";
                }
    
            }else{

                //  Actualizar solicitud

                $query_update_solicitud = "UPDATE tbl_conci_anulacion_solicitud 
                                SET 
                                    etapa_id = ?,
                                    user_updated_id = ?,
                                    updated_at = ?
                                WHERE 
                                    id = ?";
                $stmt = $mysqli->prepare($query_update_solicitud);
                if ($stmt) {

                    $stmt->bind_param("iisi", $etapa_id, $usuario_id, $fecha, $solicitud_anulacion_id);
                    if ($stmt->execute()) {
                        $stmt->close();
                        $result["http_code"] = 200;

                    } else {
                        $result["http_code"] = 400;
                        $result["error"] = "Error al ejecutar la consulta de actualización de comprobante: " . $mysqli->error;
                    }
                }else{
                    $result["http_code"] = 500;
                    $result["error"] = "Error al preparar la consulta: " . $mysqli->error;
                }

            }

            $sql_insert_historico_etapa = "INSERT INTO tbl_conci_anulacion_solicitud_historial_etapa (
                solicitud_anulacion_id,
                etapa_id,
                status,
                created_at,
                user_created_id
            ) 
            VALUES (?, ?, 1, ?, ?)";

            $stmtEtapa = $mysqli->prepare($sql_insert_historico_etapa);
            if ($stmtEtapa) {          
                $stmtEtapa->bind_param("iisi", $solicitud_anulacion_id, $etapa_id, $fecha, $usuario_id);
                if ($stmtEtapa->execute()) {
                    $stmtEtapa->close();
                } else {
                    $result["http_code"] = 400;
                    $result["error"] = "Error al ejecutar la consulta de inserción de historial: " . $mysqli->error;
                }
            }

        } else {
            $result["http_code"] = 400;
            $result["error"] = "La etapa no existe. Comunicarse con soporte";
        }

    } else {
        $result["http_code"] = 400;
        $result["error"] = "La sesión ha caducado. Por favor, inicie sesión nuevamente.";
    }

    echo json_encode($result);
    exit();
}

function sendEmailSolicitudAnulacion($transaccion_id, $motivo, $fecha){
    include '/var/www/html/sys/mailer/class.phpmailer.php';
    include("db_connect.php");
    include("sys_login.php");

    try {
        $stmt = $mysqli->prepare("SELECT
            ans.id AS anulacion_id,
            ct.id,
            at.nombre AS nombre_tipo,
            cm.nombre AS nombre_metodo,
            IFNULL(cp.nombre, '') AS nombre_proveedor,
            ans.etapa_id,
            ae.nombre AS nombre_etapa,
            ct.transaccion_id,
            DATE_FORMAT(ct.fecha, '%d/%m/%Y') AS fecha,
            ce.nombre AS estado_calimaco,
            ct.cantidad,
            ct.status,
            p.correo AS correo_create,
            DATE_FORMAT(ans.created_at, '%d/%m/%Y %H:%i:%s') AS created_at,
            u.usuario AS usuario_create
        FROM tbl_conci_anulacion_solicitud ans
        LEFT JOIN tbl_conci_calimaco_transaccion ct ON ans.transaccion_id = ct.id
        LEFT JOIN tbl_conci_anulacion_tipo at ON ans.tipo_id = at.id
        LEFT JOIN tbl_conci_anulacion_etapa ae ON ans.etapa_id = ae.id
        LEFT JOIN tbl_conci_calimaco_estado ce ON ct.estado_id = ce.id
        LEFT JOIN tbl_conci_calimaco_metodo cm ON ct.metodo_id = cm.id
        LEFT JOIN tbl_conci_proveedor cp ON cm.proveedor_id = cp.id
        LEFT JOIN tbl_usuarios u ON u.id = ans.user_created_id
        LEFT JOIN tbl_personal_apt p ON p.id = u.personal_id 
        WHERE ans.status = 1 AND ans.id = ? LIMIT 1");
        $stmt->bind_param("i", $transaccion_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $body = '<html>';
        $host = $_SERVER["HTTP_HOST"];

        while ($sel = $result->fetch_assoc()) {
            $transaccion_id = $sel["transaccion_id"];
            $nombre_tipo = $sel["nombre_tipo"];
            $fecha = $sel["fecha"];
            $cantidad = $sel["cantidad"];
            $created_at = $sel["created_at"];
            $correo_create = $sel["correo_create"];
            $nombre_metodo = $sel["nombre_metodo"];
            $nombre_proveedor = $sel["nombre_proveedor"];
            $usuario_create = $sel["usuario_create"];

            $body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';
            $body .= '<thead><tr><th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px"><b>Notificacion de Solicitud de Anulación</b></th></tr></thead>';
            $body .= '<tr><td style="background-color: #ffffdd; width: 100px;"><b>ID:</b></td><td>'.$transaccion_id.'</td></tr>';
            $body .= '<tr><td style="background-color: #ffffdd; width: 100px;"><b>Metodo:</b></td><td>'.$nombre_metodo.'</td></tr>';
            $body .= '<tr><td style="background-color: #ffffdd; width: 100px;"><b>Fecha:</b></td><td>'.$fecha.'</td></tr>';
            $body .= '<tr><td style="background-color: #ffffdd"><b>Monto:</b></td><td>'.$cantidad.'</td></tr>';
            $body .= '<tr><td style="background-color: #ffffdd"><b>Usuario solicitante:</b></td><td>'.$usuario_create.'</td></tr>';
            $body .= '<tr><td style="background-color: #ffffdd"><b>Tipo de anulación:</b></td><td>'.$nombre_tipo.'</td></tr>';
            $body .= '<tr><td style="background-color: #ffffdd; width: 100px;"><b>Fecha de anulación:</b></td><td>'.$created_at.'</td></tr>';
            $body .= '<tr><td style="background-color: #ffffdd"><b>Motivo:</b></td><td>'.$motivo.'</td></tr>';
            $body .= '</table>';
        }

        $body .= '</html>';
        
        $mail = new PHPMailer(true);
        $mail->IsSMTP();
        $mail->CharSet    = 'utf-8';
        $mail->SMTPDebug  = 1;
        $mail->SMTPAuth   = true;
        $mail->Host       = "smtp.gmail.com";
        $mail->Port       = 465;
        $mail->SMTPSecure = "ssl";

        $sub_titulo_email = (env('SEND_EMAIL') == 'test') ? "TEST SISTEMAS: " : "";
        $subject = $sub_titulo_email."Gestión - Anulación Trx Web - ".$nombre_proveedor." " .$transaccion_id. " ".$fecha;


        $correos = conci_anulacion_obtener_correos('notificacion_anulacion');
        foreach ($correos as $correo) {
            $mail->AddAddress($correo);
        } 

        $mail->Username = env('MAIL_GESTION_USER');
        $mail->Password = env('MAIL_GESTION_PASS');
        $mail->FromName = env('MAIL_GESTION_NAME');
        $mail->Subject  = $subject;
        $mail->Body     = $body;
        $mail->isHTML(true);
        $mail->send();

    } catch (phpmailerException $e) {
        sendErrorEmail($correo_create, $e->errorMessage());
    } catch (Exception $e) {
        sendErrorEmail($correo_create, $e->getMessage());
    }
}

function sendErrorEmail($to, $errorMessage) {
    $mail = new PHPMailer(true);
    $mail->IsSMTP();
    $mail->CharSet    = 'utf-8';
    $mail->SMTPDebug  = 1;
    $mail->SMTPAuth   = true;
    $mail->Host       = "smtp.gmail.com";
    $mail->Port       = 465;
    $mail->SMTPSecure = "ssl";

    $mail->Username = env('MAIL_GESTION_USER');
    $mail->Password = env('MAIL_GESTION_PASS');
    $mail->AddAddress($to);
    $mail->FromName = "Apuesta Total - Conciliación de ventas - Anulación";
    $mail->Subject  = "Error de envio de emails :: Conciliación de ventas - Anulación";
    $mail->Body     = $errorMessage;
    $mail->send();
}

function fn_conci_updateTransaccion($mysqli, $transaccion_id,$usuario_id, $fecha){
    $query_update_transaccion = "UPDATE tbl_conci_calimaco_transaccion 
                                        SET 
                                            status = 0,
                                            user_updated_id = ?,
                                            updated_at = ?
                                        WHERE 
                                             id = ?";
    $stmt = $mysqli->prepare($query_update_transaccion);
    if (!$stmt) {
        $result["http_code"] = 500;
        $result["error"] = "Error al preparar la consulta para anulación: " . $mysqli->error;
    } else {
        $stmt->bind_param("isi", $usuario_id, $fecha, $transaccion_id);
        if ($stmt->execute()) {
            $stmt->close();

            return true;
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestión.";
                       
        } else {
            $result["http_code"] = 400;
            $result["error"] = "Error al anular la transacción: " . $mysqli->error;
        }
    }
}

function fn_conci_updatePeriodo($mysqli, $periodo_id, $usuario_id, $fecha,$monto_anulado,$comision_anulada, $anulados_count){


    $query_update_transaccion = "UPDATE tbl_conci_periodo
                                        SET 
                                            monto_anulado = ?,
                                            comision_anulada = ?,
                                            anulados_count = ?,
                                            user_updated_id = ?,
                                            updated_at = ?
                                        WHERE 
                                             id = ?";
    $stmt = $mysqli->prepare($query_update_transaccion);
    if (!$stmt) {
        $result["http_code"] = 500;
        $result["error"] = "Error al preparar la consulta para anulación: " . $mysqli->error;
    } else {
        $stmt->bind_param("ddiisi", $monto_anulado, $comision_anulada, $anulados_count,$usuario_id, $fecha, $periodo_id);
        if ($stmt->execute()) {
            $stmt->close();

            return true;
            $result["http_code"] = 200;
            $result["status"] = "Datos obtenidos de gestión.";
                       
        } else {
            $result["http_code"] = 400;
            $result["error"] = "Error al anular la transacción: " . $mysqli->error;
        }
    }
}

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_anulacion_eliminar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");
    $result = array(); // Inicializar el array result

    if ((int)$usuario_id > 0) {
        $anulacion_id = $_POST["anulacion_id"];

        if ((int)$anulacion_id > 0) {
            $error = '';

            //  ACTUALIZAR ESTADO
            $query_update_anulacion = "UPDATE tbl_conci_anulacion_solicitud 
                                         SET 
                                             status = 0,
                                             user_updated_id = ?,
                                             updated_at = ?
                                         WHERE 
                                             id = ?";
            $stmt = $mysqli->prepare($query_update_anulacion);
            $stmt->bind_param("iss", $usuario_id, $fecha, $anulacion_id);

            if ($stmt->execute()) {

                //  REGISTRAR CAMBIOS DE ESTADO
                $sql_insert_historico_estado = "INSERT INTO tbl_conci_anulacion_solicitud_historial_etapa (
                                                    solicitud_anulacion_id,
                                                    etapa_id,
                                                    status,
                                                    created_at,
                                                    user_created_id
                                                ) 
                                                VALUES (?, 0, 1, ?, ?)";
                $stmtEstado = $mysqli->prepare($sql_insert_historico_estado);
                //$motivo = ''; // Debes asignar un valor a $motivo aquí
                $stmtEstado->bind_param("isi", $anulacion_id,$fecha, $usuario_id);

                if ($stmtEstado->execute()) {
                    $result["http_code"] = 200;
                    $result["status"] = "Datos obtenidos de gestión.";
                } else {
                    $result["http_code"] = 400;
                    $result["error"] = "Error al ejecutar la consulta de inserción de historial: " . $stmtEstado->error;
                }
            } else {
                $result["http_code"] = 400;
                $result["error"] = "Error al ejecutar la consulta de actualización de comprobante: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $result["http_code"] = 400;
            $result["error"] = "El ID del comprobante no es válido.";
        }
    } else {
        $result["http_code"] = 400;
        $result["error"] = "La sesión ha caducado. Por favor, inicie sesión nuevamente.";
    }

    echo json_encode($result);
    exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_anulacion_rechazar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");
    $result = array(); // Inicializar el array result

    if ((int)$usuario_id > 0) {
        $anulacion_id = $_POST["anulacion_id"];
        $motivo_rechazo = $_POST["motivo"];

        if ((int)$anulacion_id > 0) {
            $error = '';

            //  ACTUALIZAR ESTADO
            $query_update_anulacion = "UPDATE tbl_conci_anulacion_solicitud 
                                         SET 
                                             etapa_id = 4,
                                             user_updated_id = ?,
                                             updated_at = ?
                                         WHERE 
                                             id = ?";
            $stmt = $mysqli->prepare($query_update_anulacion);
            $stmt->bind_param("iss", $usuario_id, $fecha, $anulacion_id);

            if ($stmt->execute()) {

                //  REGISTRAR CAMBIOS DE ESTADO
                $sql_insert_historico_estado = "INSERT INTO tbl_conci_anulacion_solicitud_historial_etapa (
                                                    solicitud_anulacion_id,
                                                    etapa_id,
                                                    motivo,
                                                    status,
                                                    created_at,
                                                    user_created_id
                                                ) 
                                                VALUES (?, 4,?, 1, ?, ?)";
                $stmtEstado = $mysqli->prepare($sql_insert_historico_estado);
                $stmtEstado->bind_param("issi", $anulacion_id,$motivo_rechazo,$fecha, $usuario_id);

                if ($stmtEstado->execute()) {

                    sendEmailRechazoSolicitudAnulacion($anulacion_id,$motivo_rechazo,$fecha);

                    $result["http_code"] = 200;
                    $result["status"] = "Datos obtenidos de gestión.";
                } else {
                    $result["http_code"] = 400;
                    $result["error"] = "Error al ejecutar la consulta de inserción de historial: " . $stmtEstado->error;
                }
                $stmtEstado->close();
            } else {
                $result["http_code"] = 400;
                $result["error"] = "Error al ejecutar la consulta de actualización de comprobante: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $result["http_code"] = 400;
            $result["error"] = "El ID del comprobante no es válido.";
        }
    } else {
        $result["http_code"] = 400;
        $result["error"] = "La sesión ha caducado. Por favor, inicie sesión nuevamente.";
    }

    echo json_encode($result);
    exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "conci_anulacion_aprobar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");
    $result = [];

    if ((int)$usuario_id > 0) {
        $anulacion_id = (int)$_POST["anulacion_id"];
        $cantidad_aprobaciones = (int)$_POST["cantidad_aprobaciones"];
        $first_user_authorized_id = (int)$_POST["first_user_authorized_id"];
        $calimaco_id = (int)$_POST["calimaco_id"];

        if ($anulacion_id > 0) {

            $autorizado_id = $cantidad_aprobaciones == 0 ? 'first_user_authorized_id' : 'second_user_authorized_id';
            $autorizado_at = $cantidad_aprobaciones == 0 ? 'first_authorized_at' : 'second_authorized_at';
            $historico_etapa_id = $cantidad_aprobaciones == 0 ? 2 : 3;
            $cantidad_aprobaciones= $cantidad_aprobaciones + 1;

            if($first_user_authorized_id != $usuario_id){

                $query_update_anulacion = "UPDATE tbl_conci_anulacion_solicitud 
                                        SET 
                                            etapa_id = ?,
                                            cantidad_aprobaciones = ?,
                                            $autorizado_id = ?,
                                            $autorizado_at = ?,
                                            user_updated_id = ?,
                                            updated_at = ?
                                        WHERE 
                                            id = ?";
                $stmt = $mysqli->prepare($query_update_anulacion);
                $stmt->bind_param("iiisisi", $historico_etapa_id, $cantidad_aprobaciones, $usuario_id, $fecha, $usuario_id, $fecha, $anulacion_id);

                if ($stmt->execute()) {

                    if($historico_etapa_id ==3){

                        $sql_insert_historico_estado = "INSERT INTO tbl_conci_anulacion_solicitud_historial_etapa (
                                                            solicitud_anulacion_id,
                                                            etapa_id,
                                                            status,
                                                            created_at,
                                                            user_created_id
                                                        ) 
                                                        VALUES (?, ?, 1, ?, ?)";
                        $stmtEstado = $mysqli->prepare($sql_insert_historico_estado);
                        $stmtEstado->bind_param("iisi", $anulacion_id, $historico_etapa_id, $fecha, $usuario_id);

                        if ($stmtEstado->execute()) {

                            //  Actualizar el estado a anulado

                                $query_update_calimaco = "UPDATE tbl_conci_calimaco_transaccion 
                                                            SET 
                                                                estado_liquidacion = 3
                                                            WHERE 
                                                                id = ?";
                                $stmtCalimaco = $mysqli->prepare($query_update_calimaco);
                                $stmtCalimaco->bind_param("i", $calimaco_id);

                                if ($stmtCalimaco->execute()) {
                                    $stmtCalimaco->close();

                                    //  Identificar los datos de comision

                                    $selectQueryProveedor = "SELECT 
                                        ct.id,ct.periodo_id, IFNULL(pt.monto,0),IFNULL(pt.comision_total_calculado,0),IFNULL(p.monto_anulado,0),IFNULL(p.comision_anulada,0),IFNULL(p.anulados_count,0)
                                        FROM tbl_conci_calimaco_transaccion ct
                                        LEFT JOIN tbl_conci_periodo p
                                        ON ct.periodo_id = p.id
                                        LEFT JOIN tbl_conci_proveedor_transaccion pt
                                        ON pt.id = (
                                            SELECT CAST(SUBSTRING_INDEX(ct.venta_proveedor_id, ',', 1) AS UNSIGNED)
                                        )
                                        WHERE ct.id = ? AND pt.status = 1
                                        LIMIT 1";
                        
                                    $selectStmtProveedor = $mysqli->prepare($selectQueryProveedor);
                                    $selectStmtProveedor->bind_param("i", $calimaco_id);
                                    $selectStmtProveedor->execute();
                                    $selectStmtProveedor->store_result();
                        
                                    if ($selectStmtProveedor->num_rows > 0) {
                        
                                        $selectStmtProveedor->bind_result($id, $periodo_id, $monto,$comision_total_calculado, $monto_anulado, $comision_anulada, $anulados_count);
                                        $selectStmtProveedor->fetch();
                                        $selectStmtProveedor->close();

                                        $monto_anulado = $monto_anulado + $monto;
                                        $comision_anulada = $comision_anulada + $comision_total_calculado;
                                        $anulados_count = $anulados_count +1;

                                        $check_Periodo = fn_conci_updatePeriodo($mysqli,$periodo_id,$usuario_id, $fecha,$monto_anulado,$comision_anulada, $anulados_count);
                            
                                        if(!$check_Periodo){
                                            $result["http_code"] = 400;
                                            $result["error"] = "Error al acutalizar el periodo";
                                        }


                                        $result["http_code"] = 200;
                        
                                    }else{
                                        $result["http_code"] = 400;
                                        $result["error"] = "Sin nombre";
                                    }

                                }

                            $result["http_code"] = 200;
                            $result["status"] = "Datos obtenidos de gestión.";
                        } else {
                            $result["http_code"] = 400;
                            $result["error"] = "Error al ejecutar la consulta de inserción de historial: " . $mysqli->error;
                        }
                        $stmtEstado->close();
                    }else{
                        $result["http_code"] = 200;
                        $result["status"] = "Datos obtenidos de gestión.";
                    }
                } else {
                    $result["http_code"] = 400;
                    $result["error"] = "Error al ejecutar la consulta de actualización de comprobante: " . $mysqli->error;
                }
                $stmt->close();
            
            }else{
                $result["http_code"] = 400;
                $result["error"] = "Un usuario no puede aprobar una solicitud dos veces. Tiene que aprobar la solicitud otro usuario";
                $result["titulo"] = "Aprobación denegada";
            }
        } else {
            $result["http_code"] = 400;
            $result["error"] = "El ID de la solicitud de anulación no es válido.";
            $result["titulo"] =  "Error al aprobar solicitud";
        }
    } else {
        $result["http_code"] = 400;
        $result["error"] = "La sesión ha caducado. Por favor, inicie sesión nuevamente.";
        $result["titulo"] =  "Error al aprobar solicitud";
    }

    echo json_encode($result);
    exit();
}

function sendEmailRechazoSolicitudAnulacion($anulación_id,$motivo_rechazo,$fecha){
    include '/var/www/html/sys/mailer/class.phpmailer.php';
    include("db_connect.php");
    include("sys_login.php");

    try {
        $stmt = $mysqli->prepare("SELECT
            ans.id AS anulacion_id,
            ct.id,
            at.nombre AS nombre_tipo,
            cm.nombre AS nombre_metodo,
            IFNULL(cp.nombre, '') AS nombre_proveedor,
            ans.etapa_id,
            ans.motivo,
            ae.nombre AS nombre_etapa,
            ct.transaccion_id,
            DATE_FORMAT(ct.fecha, '%d/%m/%Y') AS fecha,
            ce.nombre AS estado_calimaco,
            ct.cantidad,
            ct.status,
            p.correo AS correo_create,
            DATE_FORMAT(ans.created_at, '%d/%m/%Y %H:%i:%s') AS created_at,
            u.usuario AS usuario_create,
            DATE_FORMAT(ans.updated_at, '%d/%m/%Y %H:%i:%s') AS updated_at,
            us.usuario AS usuario_update
        FROM tbl_conci_anulacion_solicitud ans
        LEFT JOIN tbl_conci_calimaco_transaccion ct ON ans.transaccion_id = ct.id
        LEFT JOIN tbl_conci_anulacion_tipo at ON ans.tipo_id = at.id
        LEFT JOIN tbl_conci_anulacion_etapa ae ON ans.etapa_id = ae.id
        LEFT JOIN tbl_conci_calimaco_estado ce ON ct.estado_id = ce.id
        LEFT JOIN tbl_conci_calimaco_metodo cm ON ct.metodo_id = cm.id
        LEFT JOIN tbl_conci_proveedor cp ON cm.proveedor_id = cp.id
        LEFT JOIN tbl_usuarios u ON u.id = ans.user_created_id
        LEFT JOIN tbl_usuarios us ON us.id = ans.user_updated_id
        LEFT JOIN tbl_personal_apt p ON p.id = u.personal_id 
        WHERE ans.status = 1 AND ans.id = ? LIMIT 1");
        $stmt->bind_param("i", $anulación_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $body = '<html>';
        $host = $_SERVER["HTTP_HOST"];

        while ($sel = $result->fetch_assoc()) {
            $transaccion_id = $sel["transaccion_id"];
            $nombre_tipo = $sel["nombre_tipo"];
            $fecha = $sel["fecha"];
            $cantidad = $sel["cantidad"];
            $created_at = $sel["created_at"];
            $correo_create = $sel["correo_create"];
            $motivo = $sel["motivo"];
            $nombre_metodo = $sel["nombre_metodo"];
            $nombre_proveedor = $sel["nombre_proveedor"];
            $usuario_create = $sel["usuario_create"];
            $updated_at = $sel["updated_at"];
            $usuario_update = $sel["usuario_update"];

            $body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';
            $body .= '<thead><tr><th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px"><b>Notificacion de Rechazo de Solicitud de Anulación</b></th></tr></thead>';
            $body .= '<tr><td style="background-color: #ffffdd; width: 100px;"><b>ID:</b></td><td>'.$transaccion_id.'</td></tr>';
            $body .= '<tr><td style="background-color: #ffffdd; width: 100px;"><b>Metodo:</b></td><td>'.$nombre_metodo.'</td></tr>';
            $body .= '<tr><td style="background-color: #ffffdd; width: 100px;"><b>Fecha:</b></td><td>'.$fecha.'</td></tr>';
            $body .= '<tr><td style="background-color: #ffffdd"><b>Monto:</b></td><td>'.$cantidad.'</td></tr>';
            $body .= '<tr><td style="background-color: #ffffdd"><b>Usuario solicitante:</b></td><td>'.$usuario_create.'</td></tr>';
            $body .= '<tr><td style="background-color: #ffffdd"><b>Tipo de anulación:</b></td><td>'.$nombre_tipo.'</td></tr>';
            $body .= '<tr><td style="background-color: #ffffdd; width: 100px;"><b>Fecha de anulación:</b></td><td>'.$created_at.'</td></tr>';
            $body .= '<tr><td style="background-color: #ffffdd"><b>Motivo:</b></td><td>'.$motivo.'</td></tr>';
            $body .= '</table>';

            $body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';
            $body .= '<tr><td style="background-color: #ffffdd"><b>Usuario que rechazo:</b></td><td>'.$usuario_update.'</td></tr>';
            $body .= '<tr><td style="background-color: #ffffdd; width: 100px;"><b>Fecha de rechazo:</b></td><td>'.$updated_at.'</td></tr>';
            $body .= '<tr><td style="background-color: #ffffdd; width: 100px;"><b>Motivo de rechazo:</b></td><td>'.$motivo_rechazo.'</td></tr>';
            $body .= '</table>';
        }

        $body .= '</html>';

        $mail = new PHPMailer(true);
        $mail->IsSMTP();
        $mail->CharSet    = 'utf-8';
        $mail->SMTPDebug  = 1;
        $mail->SMTPAuth   = true;
        $mail->Host       = "smtp.gmail.com";
        $mail->Port       = 465;
        $mail->SMTPSecure = "ssl";

        $sub_titulo_email = (env('SEND_EMAIL') == 'test') ? "TEST SISTEMAS: " : "";
        $subject = $sub_titulo_email."Gestión - Rechazo de Anulación Trx Web - ".$nombre_proveedor." " .$transaccion_id. " ".$fecha;

        $mail->AddAddress($correo_create);
  
        $mail->Username = env('MAIL_GESTION_USER');
        $mail->Password = env('MAIL_GESTION_PASS');
        $mail->FromName = env('MAIL_GESTION_NAME');
        $mail->Subject  = $subject;
        $mail->Body     = $body;
        $mail->isHTML(true);
        $mail->send();

    } catch (phpmailerException $e) {
        sendErrorEmail($correo_create, $e->errorMessage());
    } catch (Exception $e) {
        sendErrorEmail($correo_create, $e->getMessage());
    }
}

function conci_anulacion_obtener_correos($metodo){
    $correos = [];
    include("db_connect.php");

    $sel_query = $mysqli->query("SELECT p.correo
                            FROM tbl_conci_correo AS mc
                            INNER JOIN tbl_conci_correo_metodo AS mt ON mt.id = mc.metodo_id
                            INNER JOIN tbl_usuarios AS u ON u.id = mc.usuario_id 
                            INNER JOIN tbl_personal_apt AS p ON p.id = u.personal_id AND p.estado = 1
                            WHERE mc.status = 1 AND u.estado = 1  AND mt.status = 1 AND mt.metodo = '".$metodo."'");
    while($sel = $sel_query->fetch_assoc())
    {
        $email = filter_var(trim($sel['correo']), FILTER_SANITIZE_EMAIL);
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            array_push($correos, $email);
        }
    }

    return $correos;
}