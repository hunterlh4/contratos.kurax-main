<?php 

date_default_timezone_set("America/Lima");
$result=array();
include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_num_cuenta_cuenta_bancaria_nuevo")
{
	$usuario_id = $login?$login['id']:null;

	if((int)$usuario_id > 0)
	{
		$param_canal = $_POST['form_modal_sec_mantenimiento_num_cuenta_param_canal_id'];
		$param_empresa = $_POST['form_modal_sec_mantenimiento_num_cuenta_param_razon_social_id'];
		$param_banco = $_POST['form_modal_sec_mantenimiento_num_cuenta_param_banco_id'];
		$param_num_cuenta_bancaria = $_POST['form_modal_sec_mantenimiento_num_cuenta_param_num_cuenta_corriente'];
		$param_subdiario = $_POST['form_modal_sec_mantenimiento_num_cuenta_param_subdiario'];
		$param_moneda = $_POST['form_modal_sec_mantenimiento_num_cuenta_param_moneda_id'];
		$param_num_cuenta_contable = $_POST['form_modal_sec_mantenimiento_num_cuenta_param_num_cuenta_contable'];
        $param_num_cuenta_contable_haber = $_POST['form_modal_sec_mantenimiento_num_cuenta_param_num_cuenta_contable_haber'];
		$param_num_codigo_anexo = $_POST['form_modal_sec_mantenimiento_num_cuenta_param_cod_anexo'];
		$param_tipo_pago = $_POST['form_modal_sec_mantenimiento_num_cuenta_param_tipo_pago_id'];
		$param_proceso = $_POST['form_modal_sec_mantenimiento_num_cuenta_param_cont_num_cuenta_proceso_id'];
		

		$error = '';
		$tipo_cuenta_id = 0;

		if($param_banco == 12)
		{
			$tipo_cuenta_id = 1;
		}
		else
		{
			$tipo_cuenta_id = 2;
		}

		$query_insert = 
		"
			INSERT INTO cont_num_cuenta
			(
				canal_id,
				razon_social_id,
				banco_id,
				tipo_cuenta_id,
				num_cuenta_corriente,
				subdiario,
				moneda_id,
				num_cuenta_contable,
                num_cuenta_contable_haber,
				cod_anexo,
				tipo_pago_id,
				cont_num_cuenta_proceso_id,
				status,
				user_created_id,
				created_at,
				user_updated_id,
				updated_at
			)
			VALUES
			(
				'".$param_canal."',
				'".$param_empresa."',
				'".$param_banco."',
				'".$tipo_cuenta_id."',
				'".$param_num_cuenta_bancaria."',
				'".$param_subdiario."',
				'".$param_moneda."',
				'".$param_num_cuenta_contable."',
                '".$param_num_cuenta_contable_haber."',
				'".$param_num_codigo_anexo."',
				'".$param_tipo_pago."',
				'".$param_proceso."',
				1,
				'".$login["id"]."', 
				'".date('Y-m-d H:i:s')."',
				'".$login["id"]."', 
				'".date('Y-m-d H:i:s')."'
			)
		";

		$mysqli->query($query_insert);

		if($mysqli->error)
		{
			$error = $mysqli->error;

			$result["http_code"] = 400;
			$result["titulo"] = "Error al registrar.";
			$result["descripcion"] = $error;

			echo json_encode($result);
			exit();
		}

		if($error == '')
		{
			$result["http_code"] = 200;
			$result["titulo"] = "Registro exitoso";
			$result["descripcion"] = "La cuenta bancaria se registró exitosamente";
			
			echo json_encode($result);
			exit();
		}
		else
		{
			$result["http_code"] = 400;
			$result["titulo"] = "Error al registrar.";
			$result["descripcion"] = $error;

			echo json_encode($result);
			exit();
		}
	}
	else
	{
		$result["http_code"] = 400;
        $result["error"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
	}

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_num_cuenta_cuenta_bancaria_editar")
{
	$usuario_id = $login?$login['id']:null;
    $fecha = date('Y-m-d H:i:s');
	if((int)$usuario_id > 0)
	{
		$param_num_cuenta_id = $_POST['form_modal_sec_mantenimiento_num_cuenta_param_id'];
		$param_canal = $_POST['form_modal_sec_mantenimiento_num_cuenta_param_canal_id'];
		$param_empresa = $_POST['form_modal_sec_mantenimiento_num_cuenta_param_razon_social_id'];
		$param_banco = $_POST['form_modal_sec_mantenimiento_num_cuenta_param_banco_id'];
		$param_num_cuenta_bancaria = $_POST['form_modal_sec_mantenimiento_num_cuenta_param_num_cuenta_corriente'];
		$param_subdiario = $_POST['form_modal_sec_mantenimiento_num_cuenta_param_subdiario'];
		$param_moneda = $_POST['form_modal_sec_mantenimiento_num_cuenta_param_moneda_id'];
		$param_num_cuenta_contable = $_POST['form_modal_sec_mantenimiento_num_cuenta_param_num_cuenta_contable'];
        $param_num_cuenta_contable_haber = $_POST['form_modal_sec_mantenimiento_num_cuenta_param_num_cuenta_contable_haber'];
		$param_num_codigo_anexo = $_POST['form_modal_sec_mantenimiento_num_cuenta_param_cod_anexo'];
		$param_tipo_pago = $_POST['form_modal_sec_mantenimiento_num_cuenta_param_tipo_pago_id'];
		$param_proceso= $_POST['form_modal_sec_mantenimiento_num_cuenta_param_cont_num_cuenta_proceso_id'];


		$error = '';
		$tipo_cuenta_id = 0;

		if($param_banco == 12)
		{
			$tipo_cuenta_id = 1;
		}
		else
		{
			$tipo_cuenta_id = 2;
		}


        // --------------------------------- CONSULTAR ESTADO ANTERIOR AL CAMBIO

        $stmt = $mysqli->prepare("
                                SELECT
                                COALESCE(c.nombre,'Seleccione'),
                                COALESCE(rz.nombre,'Seleccione'),
                                COALESCE(b.nombre,'Seleccione'),
                                nc.num_cuenta_corriente,
                                nc.subdiario,
                                COALESCE(m.nombre,'Seleccione'),
                                nc.num_cuenta_contable,
                                nc.num_cuenta_contable_haber,
                                nc.cod_anexo,
                                COALESCE(tp.nombre,'Seleccione'),
                                COALESCE(ncp.nombre,'Seleccione')
                            FROM cont_num_cuenta nc
                                LEFT JOIN tbl_canales_at c
                                ON nc.canal_id = c.id
                                LEFT JOIN tbl_razon_social rz
                                ON nc.razon_social_id = rz.id
                                LEFT JOIN tbl_bancos b
                                ON nc.banco_id = b.id
                                LEFT JOIN tbl_moneda m
                                ON nc.moneda_id = m.id
                                LEFT JOIN cont_tipo_programacion tp
                                ON nc.tipo_pago_id = tp.id
                                LEFT JOIN cont_num_cuenta_proceso ncp
                                ON nc.cont_num_cuenta_proceso_id = ncp.id
                            WHERE nc.id = ?
                            LIMIT 1
                            ");

        $stmt->bind_param("i", $param_num_cuenta_id);
        $stmt->execute();
        $stmt->bind_result(
                            $canal_id, 
                            $razon_social_id,
                            $banco_id,
                            $num_cuenta_corriente,
                            $subdiario,
                            $moneda_id,
                            $num_cuenta_contable,
                            $num_cuenta_contable_haber,
                            $cod_anexo,
                            $tipo_pago_id,
                            $cont_num_cuenta_proceso_id);
        $stmt->fetch();
        $stmt->close();


        //-------------------------------
        $historial_query = $mysqli->prepare("
            INSERT INTO cont_num_cuenta_historial_cambios (
                cont_num_cuenta_id,
                valor_anterior,
                valor_nuevo,
                nombre_campo,
                usuario_id,
                fecha_registro
            ) VALUES (?, ?, ?, ?, ?,?)
        ");

        // Definir un array asociativo para mapear los campos a sus valores originales
        $campos_originales = array(
            'canal_id' => $canal_id,
            'razon_social_id' => $razon_social_id,
            'banco_id' => $banco_id,
            'num_cuenta_corriente' => $num_cuenta_corriente,
            'subdiario' => $subdiario,
            'moneda_id' => $moneda_id,
            'num_cuenta_contable' => $num_cuenta_contable,
            'num_cuenta_contable_haber' => $num_cuenta_contable_haber,
            'cod_anexo' => $cod_anexo,
            'tipo_pago_id' => $tipo_pago_id,
            'cont_num_cuenta_proceso_id' => $cont_num_cuenta_proceso_id
        );
        $cambios_realizados = false;

        foreach ($campos_originales as $campo => $valor_anterior) {
            // Comparar el valor original con el valor actual en $_POST
            //$valor_nuevo = $_POST['form_modal_sec_mantenimiento_num_cuenta_param_' . $campo];
            $valor_nuevo = $_POST[$campo];
            
            if ((string)$valor_anterior != (string)$valor_nuevo) {
                // Si hay un cambio, registrar en el historial
                $cambios_realizados = true;
                $historial_query->bind_param("isssis", $param_num_cuenta_id, $valor_anterior, $valor_nuevo, $campo, $usuario_id, $fecha );
                $historial_query->execute();
            }
        }

        $historial_query->close();
        //--------------------------------------------------------------------------------------

		$query_update = 
		"
			UPDATE cont_num_cuenta 
				SET canal_id = '".$param_canal."',
					razon_social_id = '".$param_empresa."',
					banco_id = '".$param_banco."',
					tipo_cuenta_id = '".$tipo_cuenta_id."',
					num_cuenta_corriente = '".$param_num_cuenta_bancaria."',
					subdiario = '".$param_subdiario."',
					moneda_id = '".$param_moneda."',
					num_cuenta_contable = '".$param_num_cuenta_contable."',
                    num_cuenta_contable_haber = '".$param_num_cuenta_contable_haber."',
					cod_anexo = '".$param_num_codigo_anexo."',
					tipo_pago_id = '".$param_tipo_pago."',
					cont_num_cuenta_proceso_id = '".$param_proceso."',
					user_updated_id = '".$login["id"]."',
					updated_at = '".date('Y-m-d H:i:s')."'
			WHERE id = {$param_num_cuenta_id}
		";
        if ($cambios_realizados) {
            $mysqli->query($query_update);

            if($mysqli->error)
            {
                $error = $mysqli->error;

                $result["http_code"] = 400;
                $result["titulo"] = "Error al editar.";
                $result["descripcion"] = $error;
                $result["query"] = $query_update;

                echo json_encode($result);
                exit();
            }

        }else{
                $result["http_code"] = 400;
                $result["titulo"] = "Editar";
                $result["descripcion"] = "No se realizaron cambios para guardar";

                echo json_encode($result);
                exit();
        }

		if($error == '')
		{
			$result["http_code"] = 200;
			$result["titulo"] = "Edición exitosa";
			$result["descripcion"] = "La cuenta bancaria se editó exitosamente";
			
			echo json_encode($result);
			exit();
		}
		else
		{
			$result["http_code"] = 400;
			$result["titulo"] = "Error al editar.";
			$result["descripcion"] = $error;

			echo json_encode($result);
			exit();
		}
	}
	else
	{
		$result["http_code"] = 400;
        $result["error"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
	}

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_num_cuenta_subdiario_guardar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");

    if ((int)$usuario_id > 0) {
        (int)$subdiario_id = $_POST["subdiario_id"];
		$cod_operacion = $_POST["cod_operacion"];
        $descripcion = $_POST["descripcion"];

        if ((int)$subdiario_id > 0) {
			mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            $sql_update_subdiario = "
					UPDATE cont_num_cuenta_subdiario
					SET cod_operacion = ?, descripcion = ?, updated_at = ?, user_updated_id = ?
					WHERE id = ?
            ";

            $stmt = $mysqli->prepare($sql_update_subdiario);


            if ($stmt) {
                $stmt->bind_param("sssii", $cod_operacion, $descripcion, $fecha, $usuario_id,$subdiario_id);

                try {
                    $stmt->execute();
                    $result["http_code"] = 200;
                    $result["status"] = "Datos obtenidos de gestión.";
                } catch (Exception $e) {
                    $result["http_code"] = 400;
                    $result["error"] = "Error al ejecutar la consulta: " . $e->getMessage();
                }

                $stmt->close();
            } else {
                $result["http_code"] = 400;
                $result["error"] = "Error en la preparación de la consulta: " . $mysqli->error;
            }

		}else{

            $error = '';

            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            $sql_insert_subdiario = "
                INSERT INTO cont_num_cuenta_subdiario (
                    cod_operacion,
                    descripcion,
                    status,
                    created_at,
                    user_created_id
                )  
                VALUES (?, ?, 1, ?, ?)
            ";

            $stmt = $mysqli->prepare($sql_insert_subdiario);


            if ($stmt) {
                $stmt->bind_param("sssi", $cod_operacion, $descripcion, $fecha, $usuario_id);

                try {
                    $stmt->execute();
                    $result["http_code"] = 200;
                    $result["status"] = "Datos obtenidos de gestión.";
                } catch (Exception $e) {
                    $result["http_code"] = 400;
                    $result["error"] = "Error al ejecutar la consulta: " . $e->getMessage();
                }

                $stmt->close();
            } else {
                $result["http_code"] = 400;
                $result["error"] = "Error en la preparación de la consulta: " . $mysqli->error;
            }
        }
    } else {
        $result["http_code"] = 400;
        $result["error"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña.";
    }

    echo json_encode($result);
    exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_num_cuenta_subdiario_eliminar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");

    if ((int)$usuario_id > 0) {
        $id_subdiario = $_POST["id_subdiario"];

        if ((int)$id_subdiario > 0) {
            $error = '';

            $query_update_subdiario = "
                UPDATE cont_num_cuenta_subdiario 
                SET 
                    status = 0,
                    user_updated_id = ?,
                    updated_at = ?
                WHERE 
                    id = ?
            ";

            $stmt = $mysqli->prepare($query_update_subdiario);
            $stmt->bind_param("iss", $usuario_id, $fecha, $id_subdiario);

            if ($stmt->execute()) {
                $result["http_code"] = 200;
                $result["status"] = "Datos obtenidos de gestion.";
            } else {
                $result["http_code"] = 400;
                $result["error"] = "Error al ejecutar la consulta: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $result["http_code"] = 400;
            $result["error"] = "No existe el subdiario";
        }
    } else {
        $result["http_code"] = 400;
        $result["error"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña.";
    }

    echo json_encode($result);
    exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_num_cuenta_proceso_guardar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");

    if ((int)$usuario_id > 0) {
        (int)$proceso_id = $_POST["proceso_id"];
		$nombre = $_POST["nombre"];
        $descripcion = $_POST["descripcion"];

        if ((int)$proceso_id > 0) {
			mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            $sql_update_subdiario = "
					UPDATE cont_num_cuenta_proceso
					SET nombre = ?, descripcion = ?, updated_at = ?, user_updated_id = ?
					WHERE id = ?
            ";

            $stmt = $mysqli->prepare($sql_update_subdiario);


            if ($stmt) {
                $stmt->bind_param("sssii", $nombre, $descripcion, $fecha, $usuario_id,$proceso_id);

                try {
                    $stmt->execute();

                    sendEmailNotificarNuevoProceso($nombre,$descripcion, $fecha, $login['usuario'], "Modificación de ");

                    $result["http_code"] = 200;
                    $result["status"] = "Datos obtenidos de gestión.";
                    
                } catch (Exception $e) {
                    $result["http_code"] = 400;
                    $result["error"] = "Error al ejecutar la consulta: " . $e->getMessage();
                }

                $stmt->close();
            } else {
                $result["http_code"] = 400;
                $result["error"] = "Error en la preparación de la consulta: " . $mysqli->error;
            }

		}else{

            $error = '';

            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            $sql_insert_subdiario = "
                INSERT INTO cont_num_cuenta_proceso (
                    nombre,
                    descripcion,
                    status,
                    created_at,
                    user_created_id
                )  
                VALUES (?, ?, 1, ?, ?)
            ";

            $stmt = $mysqli->prepare($sql_insert_subdiario);


            if ($stmt) {
                $stmt->bind_param("sssi", $nombre, $descripcion, $fecha, $usuario_id);

                try {
                    $stmt->execute();
                    $nuevo_proceso_id = $stmt->insert_id;

                    $result["http_code"] = 200;
                    $result["status"] = "Datos obtenidos de gestión.";

                    $selectQuery = " SELECT 
                                        ncp.nombre,
                                        ncp.descripcion,
                                        ncp.created_at,
                                        u.usuario AS usuario_creador                               
                                    FROM cont_num_cuenta_proceso ncp
                                    LEFT JOIN tbl_usuarios u
                                    ON ncp.user_created_id = u.id
                                    WHERE ncp.id = ?";

                    $selectStmt = $mysqli->prepare($selectQuery);
                    $selectStmt->bind_param("i", $nuevo_proceso_id);
                    $selectStmt->execute();
                    $selectStmt->store_result();

                    if ($selectStmt->num_rows > 0) {
                        $selectStmt->bind_result($nombre_proceso,$descripcion_proceso,$fecha_proceso,$usuario_creador);
                        $selectStmt->fetch();
                        sendEmailNotificarNuevoProceso($nombre_proceso,$descripcion_proceso, $fecha_proceso, $usuario_creador, "Nuevo");

                        }

                } catch (Exception $e) {
                    $result["http_code"] = 400;
                    $result["error"] = "Error al ejecutar la consulta: " . $e->getMessage();
                }

                $stmt->close();
            } else {
                $result["http_code"] = 400;
                $result["error"] = "Error en la preparación de la consulta: " . $mysqli->error;
            }
        }
    } else {
        $result["http_code"] = 400;
        $result["error"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña.";
    }

    echo json_encode($result);
    exit();
}

function sendEmailNotificarNuevoProceso($nombre_proceso,$descripcion_proceso, $fecha_proceso, $usuario_creador, $accion) {
    try {
        include '/var/www/html/sys/globalFunctions/generalInfo/parameterGeneral.php';
        include '/var/www/html/sys/mailer/class.phpmailer.php';

        $mail = new PHPMailer(true);
        $mail->IsSMTP();
        $mail->CharSet    = 'utf-8';
        $mail->SMTPDebug  = 1;
        $mail->SMTPAuth   = true;
		$mail->Host       = "smtp.gmail.com";
		$mail->Port       = 465;
		$mail->SMTPSecure = "ssl";

        //  Cuerpo del correo

        $body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

		$body .= '<thead>';
		$body .= '<tr>';
		$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
		$body .= '<b>Notificación de '.$accion.' proceso de cuenta contable </b>';
		$body .= '</th>';
		$body .= '</tr>';
		$body .= '</thead>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Nombre:</b></td>';
			$body .= '<td>'.$nombre_proceso.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Descripción proceso:</b></td>';
			$body .= '<td>'.$descripcion_proceso.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha Proceso:</b></td>';
			$body .= '<td>'.$fecha_proceso.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Usuario Creador:</b></td>';
			$body .= '<td>'.$usuario_creador.'</td>';
		$body .= '</tr>';

		$body .= '</table>';

		$body .= '</html>';
		$body .= "";


        if($estadoGestion=="test"){
            $subject = "Test - Automatización de ". $accion." Proceso ". $date . " - Cuentas Contables";
            
            $num_cuenta_contable_detalle= getParameterGeneral('notificacionNuevoProceso_cuenta_contable_maillist');
            $correos_en_registro = explode(',', $num_cuenta_contable_detalle);
            $correos = array_merge($correos, $correos_en_registro);

            foreach ($correos as $correo) {
                $mail->AddAddress($correo);
                }                     
        }
        else{
            $subject = "Automatización de ". $accion." Proceso ". $date . " - Cuentas Contables";                
            
            
            $correos = obtener_correos('enviar_solicitud');
            $num_cuenta_contable_detalle= getParameterGeneral('notificacionNuevoProceso_cuenta_contable_maillist');
            $correos_en_registro = explode(',', $num_cuenta_contable_detalle);
            $correos = array_merge($correos, $correos_en_registro);

            foreach ($correos as $correo) {
                $mail->AddAddress($correo);
                }   
        }
        
		$mail->Username = env('MAIL_GESTION_USER');
        $mail->Password = env('MAIL_GESTION_PASS');
        
        $mail->FromName = env('MAIL_GESTION_NAME');
        $mail->Subject  = $subject;
        $mail->Body     = $body;
        $mail->isHTML(true);
        $mail->send();

    } catch (phpmailerException $e) {
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
        
        $num_cuenta_contable_detalle= getParameterGeneral('notificacionNuevoProceso_cuenta_contable_maillist');
        $correos_en_registro = explode(',', $num_cuenta_contable_detalle);
        $correos = array_merge($correos, $correos_en_registro);

        foreach ($correos as $correo) {
            $mail->AddAddress($correo);
            }    
        
        $mail->FromName = "Apuesta Total - Automatización de Nuevo Proceso";
        $mail->Subject  = "Error de envio de emails :: Alertas phpmailerException - Automatización de Nuevo Proceso";
        $mail->Body     = $e->errorMessage();
        $mail->send();

    } catch (Exception $e) {
        $mail = new PHPMailer(true);
        $mail->IsSMTP();
        $mail->CharSet    = 'utf-8';
        $mail->SMTPDebug  = 1;
        $mail->SMTPAuth   = true;
		$mail->Host       = "smtp.gmail.com";
		$mail->Port       = 465;
		$mail->SMTPSecure = "ssl";
        
        $num_cuenta_contable_detalle= getParameterGeneral('notificacionNuevoProceso_cuenta_contable_maillist');
        $correos_en_registro = explode(',', $num_cuenta_contable_detalle);
        $correos = array_merge($correos, $correos_en_registro);

        foreach ($correos as $correo) {
            $mail->AddAddress($correo);
            }    
        
        $mail->Username = env('MAIL_GESTION_USER');
        $mail->Password = env('MAIL_GESTION_PASS');
       
        $mail->FromName = "Apuesta Total - Automatización de Nuevo Procesot - Fail";
        $mail->Subject  = "Error de envio de emails :: Automatización de Nuevo Proceso";
        $mail->Body     = $e->getMessage();
        $mail->send();
    }
}

function obtener_correos($metodo){
    global $mysqli;
    $correos = [];

    $sel_query = $mysqli->query("SELECT codigo,valor FROM tbl_parametros_generales WHERE codigo = '".$metodo."' AND valor IS NOT NULL");
    while($sel = $sel_query->fetch_assoc())
    {
        $correos_en_registro = explode(',', $sel['valor']);
        $correos = array_merge($correos, $correos_en_registro);
    }

    return $correos;
}


if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_num_cuenta_proceso_eliminar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");

    if ((int)$usuario_id > 0) {
        $id_proceso = $_POST["id_proceso"];

        if ((int)$id_proceso > 0) {
            $error = '';

            $query_update_proceso = "
                UPDATE cont_num_cuenta_proceso 
                SET 
                    status = 0,
                    user_updated_id = ?,
                    updated_at = ?
                WHERE 
                    id = ?
            ";

            $stmt = $mysqli->prepare($query_update_proceso);
            $stmt->bind_param("iss", $usuario_id, $fecha, $id_proceso);

            if ($stmt->execute()) {
                $result["http_code"] = 200;
                $result["status"] = "Datos obtenidos de gestion.";
            } else {
                $result["http_code"] = 400;
                $result["error"] = "Error al ejecutar la consulta: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $result["http_code"] = 400;
            $result["error"] = "No existe el proceso";
        }
    } else {
        $result["http_code"] = 400;
        $result["error"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña.";
    }

    echo json_encode($result);
    exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_num_cuenta_tipo_pago_guardar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");

    if ((int)$usuario_id > 0) {
        (int)$tipo_pago_id = $_POST["tipo_pago_id"];
		$nombre = $_POST["nombre"];

        if ((int)$tipo_pago_id > 0) {
			mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            $sql_update_subdiario = "
					UPDATE cont_tipo_programacion
					SET nombre = ?, updated_at = ?, user_updated_id = ?
					WHERE id = ?
            ";

            $stmt = $mysqli->prepare($sql_update_subdiario);


            if ($stmt) {
                $stmt->bind_param("ssii", $nombre, $fecha, $usuario_id,$tipo_pago_id);

                try {
                    $stmt->execute();
                    $result["http_code"] = 200;
                    $result["status"] = "Datos obtenidos de gestión.";
                } catch (Exception $e) {
                    $result["http_code"] = 400;
                    $result["error"] = "Error al ejecutar la consulta: " . $e->getMessage();
                }

                $stmt->close();
            } else {
                $result["http_code"] = 400;
                $result["error"] = "Error en la preparación de la consulta: " . $mysqli->error;
            }

		}else{

            $error = '';

            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

            $sql_insert_subdiario = "
                INSERT INTO cont_tipo_programacion (
                    nombre,
                    status,
                    created_at,
                    user_created_id
                )  
                VALUES (?, 1, ?, ?)
            ";

            $stmt = $mysqli->prepare($sql_insert_subdiario);


            if ($stmt) {
                $stmt->bind_param("ssi", $nombre, $fecha, $usuario_id);

                try {
                    $stmt->execute();
                    $result["http_code"] = 200;
                    $result["status"] = "Datos obtenidos de gestión.";
                } catch (Exception $e) {
                    $result["http_code"] = 400;
                    $result["error"] = "Error al ejecutar la consulta: " . $e->getMessage();
                }

                $stmt->close();
            } else {
                $result["http_code"] = 400;
                $result["error"] = "Error en la preparación de la consulta: " . $mysqli->error;
            }
        }
    } else {
        $result["http_code"] = 400;
        $result["error"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña.";
    }

    echo json_encode($result);
    exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_num_cuenta_tipo_pago_eliminar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");

    if ((int)$usuario_id > 0) {
        $id_tipo_pago = $_POST["id_tipo_pago"];

        if ((int)$id_tipo_pago > 0) {
            $error = '';

            $query_update_tipo_pago = "
                UPDATE cont_tipo_programacion 
                SET 
                    status = 0,
                    user_updated_id = ?,
                    updated_at = ?
                WHERE 
                    id = ?
            ";

            $stmt = $mysqli->prepare($query_update_tipo_pago);
            $stmt->bind_param("iss", $usuario_id, $fecha, $id_tipo_pago);

            if ($stmt->execute()) {
                $result["http_code"] = 200;
                $result["status"] = "Datos obtenidos de gestion.";
            } else {
                $result["http_code"] = 400;
                $result["error"] = "Error al ejecutar la consulta: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $result["http_code"] = 400;
            $result["error"] = "No existe el tipo_pago";
        }
    } else {
        $result["http_code"] = 400;
        $result["error"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña.";
    }

    echo json_encode($result);
    exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "mantenimiento_num_cuenta_inactivar") {
    $usuario_id = $login ? $login['id'] : null;
    $fecha = date("Y-m-d H:i:s");

    if ((int)$usuario_id > 0) {
        $id_cuenta = $_POST["id_cuenta"];

        if ((int)$id_cuenta > 0) {
            $error = '';

            $query_update_num_cuenta = "
                UPDATE cont_num_cuenta 
                SET 
                    status = 0,
                    user_updated_id = ?,
                    updated_at = ?
                WHERE 
                    id = ?
            ";
            

            $stmt = $mysqli->prepare($query_update_num_cuenta);
            $stmt->bind_param("iss", $usuario_id, $fecha, $id_cuenta);

            if ($stmt->execute()) {
                $result["http_code"] = 200;
                $result["status"] = "Datos obtenidos de gestion.";
            } else {
                $result["http_code"] = 400;
                $result["error"] = "Error al ejecutar la consulta: " . $stmt->error;
            }

            $stmt->close();
        } else {
            $result["http_code"] = 400;
            $result["error"] = "No existe la cuenta contable";
        }
    } else {
        $result["http_code"] = 400;
        $result["error"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña.";
    }

    echo json_encode($result);
    exit();
}

?>