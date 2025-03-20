<?php

class ContratoMandatoController
{
    public function registrar()
    {
        $db = Database::getInstance();
        $connection = $db->getConnection();

        try {

            $request = $_POST;
            $connection->beginTransaction();

            $model_contrato =  new ContratoMandato();
            if (($model_contrato->get_usuario_id()) > 0) {


                $created_at = date("Y-m-d H:i:s");
                $correlativo = new Correlativo();
                $correlativo = $correlativo->obtener_correlativo($request['tipo_contrato_id']);
                if ($correlativo['status'] == 404) {
                    $connection->rollback();
                    return json_encode($correlativo, JSON_UNESCAPED_UNICODE);
                }
                //registro de datos generales del contrato
                $data_cotrato['tipo_contrato_id'] = $request['tipo_contrato_id'];
                $data_cotrato['codigo_correlativo'] = $correlativo['result']['numero'];
                $data_cotrato['empresa_suscribe_id'] = $request['empresa_suscribe_id'];
                $data_cotrato['area_responsable_id'] = 21; // Comercial por defecto
                $data_cotrato['aprobador_id'] = NULL;
                $data_cotrato['cargo_aprobador_id'] = NULL;

                $data_cotrato['abogado_id'] = $request['abogado_id'];

                $data_cotrato['cargo_aprobador_id'] = $request['cargo_aprobador_id'];
                $contratos_fecha_suscripcion = json_decode($request['contratos'], true);

                $data_cotrato['fecha_suscripcion'] = $contratos_fecha_suscripcion[0]['fecha_suscripcion'];
                // $data_cotrato['abogado_id'] = $request['abogado_id'];
                $contratos = json_decode($request['contratos'], true);

                $data_cotrato['observaciones'] = '';
                $data_cotrato['status'] = 1;
                $data_cotrato['etapa_id'] = 1;
                $data_cotrato['user_created_id'] = $model_contrato->get_usuario_id();
                $data_cotrato['created_at'] = $created_at;

                $insert_contrato = $model_contrato->registrar($data_cotrato);

                if ($insert_contrato['status'] == 404) {
                    $connection->rollback();
                    return json_encode($insert_contrato, JSON_UNESCAPED_UNICODE);
                }
                $contrato_id = $insert_contrato['result'];

                //Registro de ficha de condiciones (inmuebles, condicion economica, incementos, inflaciones, cuotas extraorindarias, adelantos, responsable IR)
                $contratos = json_decode($request['contratos'], true);
                $ficha_contratos = $this->registrar_ficha_contratoV2($contrato_id, $contratos);
                if ($ficha_contratos['status'] == 404) {
                    $connection->rollback();
                    return json_encode($ficha_contratos, JSON_UNESCAPED_UNICODE);
                }

                //Registro de Propietarios
                $propietarios = $request['propietarios'];
                $propietarios = json_decode($request['propietarios'], true);
                $model_propietario = new Propietario();
                foreach ($propietarios as $propietario) {
                    $data_propietario['contrato_id'] = $contrato_id;
                    $data_propietario['persona_id'] = $propietario['id'];
                    $data_propietario['user_created_id'] = $model_contrato->get_usuario_id();
                    $insert_propietario = $model_propietario->registrar_propietario($data_propietario);
                    if ($insert_propietario['status'] == 404) {
                        $connection->rollback();
                        return json_encode($insert_propietario, JSON_UNESCAPED_UNICODE);
                    }
                }
                // $model_contraprestacion = new ContratoMandato();

                // $data_contraprestacion = array(
                //     'contrato_id' => $contrato_id,
                //     'monto' => $contratos[0]['monto'],
                //     'user_created_id' => $model_contrato->get_usuario_id(),
                //     'created_at' => $created_at
                // );

                // $insert_contraprestacion = $model_contraprestacion->registrar_contraprestacion($data_contraprestacion);

                // if ($insert_contraprestacion['status'] == 404) {
                //     return $insert_contraprestacion;
                // }


                //Subida de todos los archivos adjuntos;

                $modal_contrato_detalle = new ContratoArrendamientoDetalle();
                $update_contrato_Detalle = $modal_contrato_detalle->update_codigo($contrato_id);
                if ($update_contrato_Detalle['status'] == 404) {
                    $connection->rollback();
                    return json_encode($update_contrato_Detalle, JSON_UNESCAPED_UNICODE);
                }

                $this->enviar_email_confirmacion_solicitud_contrato_mandato($contrato_id, false);
                
                // $this->send_email_solicitud_contrato_arrendamiento_detallado($contrato_id, false, false);

                $connection->commit();
                $result['status'] = 200;
                $result['result'] = $contrato_id;
                $result['message'] = 'El contrato se ha registrado exitosamente';
                return json_encode($result, JSON_UNESCAPED_UNICODE);
            }


            $result['status'] = 404;
            $result['result'] = '';
            $result['message'] = 'Su session ha finalizado, por favor ingrese de nuevo.';
            $result['errors_uploads'] = '';
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $connection->rollback();
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.sss';
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }

      //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //NOTIFICAR A LOS ABOGADOS CARGO 34 SOBRE LA CREACION DE SOLICITUD DE LOCACION //////////////////////////////
            function enviar_email_confirmacion_solicitud_contrato_mandato($contrato_id, $reenviar)
            {
                $host = $_SERVER["HTTP_HOST"];
                $model_contrato = new ContratoMandato();
                $data_contrato2 = $model_contrato->obtener_solicitud_contrato_mandato_detallado($contrato_id);
                // var_dump(($contrato_id));
                // die("contenido de locacion");

                $body = "";
                $body .= '<html>';
                $body .= '<!DOCTYPE html>
                <html lang="es">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Detalle de Solicitud de Contrato</title>
                    <style>
                        body { font-family: Arial, sans-serif; width: 700px; }
                        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; width: 700px; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #395168; text-align: center; color: white; }
                        .title { font-size: 18px; font-weight: bold; margin-bottom: 10px; text-align: center; width: 700px; }
                    </style>
                </head>
                <body>
                    <div class="title">Solicitud de Contrato: Mandato</div>';
                
                // Datos Generales del Contrato
                $body .= '
                <table>
                    <tr>
                        <th colspan="2">Datos Generales del Contrato</th>
                    </tr>
                    <tr>
                        <td><strong>Empresa Suscriptora</strong></td>
                        <td>' . $data_contrato2['datos_generales']['empresa_suscribe'] . '</td>
                    </tr>
                    <tr>
                        <td><strong>RUC</strong></td>
                        <td>' . $data_contrato2['datos_generales']['empresa_ruc'] . '</td>
                    </tr>
                    <tr>
                        <td><strong>Representante Legal</strong></td>
                        <td>' . $data_contrato2['datos_generales']['empresa_representante'] . '</td>
                    </tr>
                    <tr>
                        <td><strong>Dirección</strong></td>
                        <td>' . $data_contrato2['datos_generales']['empresa_direccion'] . '</td>
                    </tr>
                    <tr>
                        <td><strong>Código de Contrato</strong></td>
                        <td>M' . $data_contrato2['datos_generales']['codigo_correlativo'] . '</td>
                    </tr>
                    <tr>
                        <td><strong>Fecha de Creación</strong></td>
                        <td>' . $data_contrato2['datos_generales']['created_at'] . '</td>
                    </tr>
                    <tr>
                        <td><strong>Fecha de Suscripción</strong></td>
                        <td>' . $data_contrato2['datos_generales']['fecha_suscripcion_contrato'] . '</td>
                    </tr>
                    <tr>
                        <td><strong>Responsable</strong></td>
                        <td>' . $data_contrato2['datos_generales']['user_created'] . '</td>
                    </tr>
                    <tr>
                        <td><strong>Abogado</strong></td>
                        <td>' . $data_contrato2['datos_generales']['abogado'] . ' (' . $data_contrato2['datos_generales']['cargo_abogado'] . ')</td>
                    </tr>
                </table>';
                
                // Datos de la Locación
                foreach ($data_contrato2['data_mandato'] as $mandato) {
                    $body .= '
                    <table>
                        <tr>
                            <th colspan="2">Datos del Contrato</th>
                        </tr>
                        <tr>
                            <td><strong>Mandante</strong></td>
                            <td>' . $mandato['mandante_antecedente'] . '</td>
                        </tr>
                        <tr>
                            <td><strong>Obligación de la Mandataria</strong></td>
                            <td>' . $mandato['mandataria_objetivo'] . '</td>
                        </tr>
                        <tr>
                            <td><strong>Mandataria</strong></td>
                            <td>' . $mandato['mandataria_retribucion'] . '</td>
                        </tr>
                        <tr>
                            <td><strong>Fecha de Inicio</strong></td>
                            <td>' . $mandato['fecha_inicio'] . '</td>
                        </tr>
                        <tr>
                            <td><strong>Fecha de Fin</strong></td>
                            <td>' . $mandato['fecha_fin'] . '</td>
                        </tr>
                        <tr>
                            <td><strong>Plazo de duración</strong></td>
                            <td>' . $mandato['plazo_duracion'] . '</td>
                        </tr>
                    </table>';
                }
                
                $body .= '
                    <!-- Enlace para ver la solicitud -->
                    <div style="width: 700px; text-align: center; font-family: arial;">
                        <a href="http://contratos.kurax.test:90/?sec_id=contrato&sub_sec_id=detalle_solicitud_mandato&id=' . $contrato_id . '" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">
                            <b>Ver Solicitud</b>
                        </a>
                    </div>
                </body>
                </html>';

                // var_dump($body);
                // die("correo forma");


                $correos = new Correos(env('SEND_EMAIL'), env('EMAIL_TEST'));
                $lista_correos = $correos->enviar_correo_contrato_arrendamiento_cargo_34();

                $cc = $lista_correos['cc'];
                $bcc = $lista_correos['bcc'];

                if ($reenviar) {
                    $titulo = "Gestion - Sistema Contratos - Reenviar Confirmación de Solicitud de Contrato Locación de Servicios: Código - " . "M" . $data_contrato2['datos_generales']['codigo_correlativo'];
                } else {
                    $titulo = "Gestion - Sistema Contratos - Confirmación de Solicitud de Contrato Locación de Servicios: Código - " . "M" . $data_contrato2['datos_generales']['codigo_correlativo'];
                }

                $request = [
                    "subject" => $titulo,
                    "body"    => $body,
                    "cc"      => $cc,
                    "bcc"     => $bcc,
                    "attach"  => [
                        // $filepath . $file,
                    ],
                ];

                $mail = new PHPMailer(true);

                try {
                    $mail->isSMTP();
                    $mail->Host = "smtp.gmail.com";
                    $mail->SMTPAuth = true;


                    $mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
                    $mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port = 465;
                    $mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
                    $mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

                    if (isset($request["cc"])) {
                        foreach ($request["cc"] as $cc) {
                            $mail->addAddress($cc);
                        }
                    }

                    if (isset($request["bcc"])) {
                        foreach ($request["bcc"] as $bcc) {
                            $mail->addBCC($bcc);
                        }
                    }

                    $mail->isHTML(true);
                    $mail->Subject  = $request["subject"];
                    $mail->Body     = $request["body"];
                    $mail->CharSet = 'UTF-8';
                    $mail->Encoding = 'base64';

                    $mail->send();
                } catch (Exception $e) {
                    $result["http_code"] = 400;
                    $result["status"] = "Datos obtenidos de gestion.";
                    $result["error_title"] = "La solicitud se registro correctamente, pero no se pudo enviar el email.";
                    $result["error"] = $mail->ErrorInfo;
                    echo json_encode($result);
                    exit();
                }
            }

             //NOTIFICAR AL ABOGADO SELECCIONADO EN LA APROBACION DE LA SOLICITUD DE LOCACION ///////////////////////////////////
             function enviar_email_confirmacion_aprobar_contrato_mandato($contrato_id, $reenviar = false) {
                $contrato_id = $_POST['contrato_id'];
                $host = $_SERVER["HTTP_HOST"];
                $model_contrato = new ContratoMandato();
                $data_contrato2 = $model_contrato->obtener_solicitud_contrato_mandato_detallado($contrato_id);
            
                // Extraer datos importantes
                $codigo_correlativo = $data_contrato2['datos_generales']['codigo_correlativo'];
                $empresa_suscribe = $data_contrato2['datos_generales']['empresa_suscribe'];
                $abogado = $data_contrato2['datos_generales']['abogado'];
            
                // Lógica de estado de aprobación
                $estado_aprobacion = $data_contrato2['datos_generales']['estado_aprobacion'] ?? null;
                $fecha_aprobacion = $data_contrato2['datos_generales']['fecha_aprobacion'] ?? null;
            
                // Determinar estado y mensaje
                if (!is_null($fecha_aprobacion)) {
                    if ($estado_aprobacion == 1) {
                        $titulo_estado = "Solicitud de Contrato Aprobada";
                        $mensaje_estado = "La solicitud de contrato ha sido aprobada el " . $fecha_aprobacion;
                    } else {
                        $titulo_estado = "Solicitud de Contrato Rechazada";
                        $mensaje_estado = "La solicitud de contrato ha sido rechazada el " . $fecha_aprobacion;
                    }
                } else {
                    $titulo_estado = "Solicitud de Contrato Pendiente";
                    $mensaje_estado = "La solicitud de contrato está pendiente de aprobación.";
                }
            
                $body = '<!DOCTYPE html>
                <html lang="es">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Detalle de Solicitud de Contrato de Mandato</title>
                    <style>
                        body { font-family: Arial, sans-serif; width: 700px; }
                        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; width: 700px; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #395168; text-align: center; color: white; }
                        .title { font-size: 18px; font-weight: bold; margin-bottom: 10px; text-align: center; width: 700px; }
                    </style>
                </head>
                <body>
                    <div class="title">Aprobación de Solicitud de Contrato de Mandato</div>
                    <table>
                        <tr>
                            <th colspan="2">Datos Generales del Contrato</th>
                        </tr>
                        <tr>
                            <td><strong>Código de Contrato</strong></td>
                            <td>MD' . $codigo_correlativo . '</td>
                        </tr>
                        <tr>
                            <td><strong>Empresa Suscriptora</strong></td>
                            <td>' . $empresa_suscribe . '</td>
                        </tr>
                        <tr>
                            <td><strong>Fecha de Creación</strong></td>
                            <td>' . $data_contrato2['datos_generales']['created_at'] . '</td>
                        </tr>
                        <tr>
                            <td><strong>Responsable</strong></td>
                            <td>' . $data_contrato2['datos_generales']['user_created'] . '</td>
                        </tr>
                        <tr>
                            <td><strong>Estado de Aprobación</strong></td>
                            <td>' . $titulo_estado . '</td>
                        </tr>
                        <tr>
                            <td><strong>Mensaje de Estado</strong></td>
                            <td>' . $mensaje_estado . '</td>
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <th colspan="2">Detalles del Mandato</th>
                        </tr>
                        <tr>
                            <td><strong>Mandante Antecedente</strong></td>
                            <td>' . $data_contrato2['data_mandato'][0]['mandante_antecedente'] . '</td>
                        </tr>
                        <tr>
                            <td><strong>Mandataria Objetivo</strong></td>
                            <td>' . $data_contrato2['data_mandato'][0]['mandataria_objetivo'] . '</td>
                        </tr>
                        <tr>
                            <td><strong>Mandataria Retribución</strong></td>
                            <td>' . $data_contrato2['data_mandato'][0]['mandataria_retribucion'] . '</td>
                        </tr>
                        <tr>
                            <td><strong>Fecha de Inicio</strong></td>
                            <td>' . $data_contrato2['data_mandato'][0]['fecha_inicio'] . '</td>
                        </tr>
                        <tr>
                            <td><strong>Fecha de Fin</strong></td>
                            <td>' . $data_contrato2['data_mandato'][0]['fecha_fin'] . '</td>
                        </tr>
                        <tr>
                            <td><strong>Plazo de Duración</strong></td>
                            <td>' . $data_contrato2['data_mandato'][0]['plazo_duracion'] . ' días</td>
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <th colspan="2">Información de la Empresa</th>
                        </tr>
                        <tr>
                            <td><strong>RUC</strong></td>
                            <td>' . $data_contrato2['datos_generales']['empresa_ruc'] . '</td>
                        </tr>
                        <tr>
                            <td><strong>Dirección</strong></td>
                            <td>' . $data_contrato2['datos_generales']['empresa_direccion'] . '</td>
                        </tr>
                    </table>
                    <div style="width: 700px; text-align: center; font-family: arial;">
                        <a href="http://contratos.kurax.test:90/?sec_id=contrato&sub_sec_id=detalle_solicitud_mandato&id=' . $contrato_id . '" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">
                            <b>Ver Solicitud</b>
                        </a>
                    </div>
                </body>
                </html>';
            
                // var_dump( $body );
                // die("cuerpo del correo");
                $correos = new Correos(env('SEND_EMAIL'), env('EMAIL_TEST'));
                $lista_correos = $correos->enviar_correo_notificacion_abogado($contrato_id);
                $cc = $lista_correos['cc'];
                $bcc = $lista_correos['bcc'];
            
                // Título para solicitud de mandato
                if ($reenviar) {
                    $titulo = "Gestión - Sistema Contratos - Reenviar Confirmación de Contrato de Mandato: Código - MD" . $codigo_correlativo;
                } else {
                    $titulo = "Gestión - Sistema Contratos - Confirmación de Contrato de Mandato: Código - MD" . $codigo_correlativo;
                }
            
                $request = [
                    "subject" => $titulo,
                    "body"    => $body,
                    "cc"      => $cc,
                    "bcc"     => $bcc,
                    "attach"  => [],
                ];
            
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = "smtp.gmail.com";
                    $mail->SMTPAuth = true;
                    $mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
                    $mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port = 465;
                    $mail->From = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
                    $mail->FromName = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');
                    if (isset($request["cc"])) {
                        foreach ($request["cc"] as $cc) {
                            $mail->addAddress($cc);
                        }
                    }
                    if (isset($request["bcc"])) {
                        foreach ($request["bcc"] as $bcc) {
                            $mail->addBCC($bcc);
                        }
                    }
                    $mail->isHTML(true);
                    $mail->Subject = $request["subject"];
                    $mail->Body = $request["body"];
                    $mail->CharSet = 'UTF-8';
                    $mail->Encoding = 'base64';
                    $mail->send();
                } catch (Exception $e) {
                    $result["http_code"] = 400;
                    $result["status"] = "Datos obtenidos de gestión.";
                    $result["error_title"] = "La solicitud se registró correctamente, pero no se pudo enviar el email.";
                    $result["error"] = $mail->ErrorInfo;
                    echo json_encode($result);
                    exit();
                }
            }
            ///
            //NOTIFICAR AL ABOGADO SELECCIONADO EN LA FIRMA DE UN CONTRATO DE MANDATO ///////////////////////////////////
            function enviar_email_confirmacion_firmar_contrato_mandato($contrato_id, $reenviar = false) {
                $contrato_id = $_POST['contrato_id'];
                $host = $_SERVER["HTTP_HOST"];
                $model_contrato = new ContratoMandato();
                $data_contrato2 = $model_contrato->obtener_solicitud_contrato_mandato_detallado($contrato_id);
            
                // Extraer datos importantes
                $codigo_correlativo = $data_contrato2['datos_generales']['codigo_correlativo'];
                $empresa_suscribe = $data_contrato2['datos_generales']['empresa_suscribe'];
            
                $body = '<!DOCTYPE html>
                <html lang="es">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Confirmación de Firma de Contrato de Mandato</title>
                    <style>
                        body { font-family: Arial, sans-serif; width: 700px; }
                        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; width: 700px; }
                        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                        th { background-color: #395168; text-align: center; color: white; }
                        .title { font-size: 18px; font-weight: bold; margin-bottom: 10px; text-align: center; width: 700px; }
                        .status-signed { color: green; font-weight: bold; }
                    </style>
                </head>
                <body>
                    <div class="title">Confirmación de Contrato de Mandato Firmado</div>
                    <table>
                        <tr>
                            <th colspan="2">Detalles de la Firma</th>
                        </tr>
                        <tr>
                            <td><strong>Código de Contrato</strong></td>
                            <td>M' . $codigo_correlativo . '</td>
                        </tr>
                        <tr>
                            <td><strong>Empresa Suscriptora</strong></td>
                            <td>' . $empresa_suscribe . '</td>
                        </tr>
                        <tr>
                            <td><strong>Estado</strong></td>
                            <td class="status-signed">CONTRATO FIRMADO</td>
                        </tr>
                        <tr>
                            <td><strong>Fecha de Firma</strong></td>
                            <td>' . date('Y-m-d H:i:s') . '</td>
                        </tr>
                    </table>
                    <table>
                        <tr>
                            <th colspan="2">Detalles del Mandato</th>
                        </tr>
                        <tr>
                            <td><strong>Mandante Antecedente</strong></td>
                            <td>' . $data_contrato2['data_mandato'][0]['mandante_antecedente'] . '</td>
                        </tr>
                        <tr>
                            <td><strong>Mandataria Objetivo</strong></td>
                            <td>' . $data_contrato2['data_mandato'][0]['mandataria_objetivo'] . '</td>
                        </tr>
                        <tr>
                            <td><strong>Mandataria Retribución</strong></td>
                            <td>' . $data_contrato2['data_mandato'][0]['mandataria_retribucion'] . '</td>
                        </tr>
                        <tr>
                            <td><strong>Fecha de Inicio</strong></td>
                            <td>' . $data_contrato2['data_mandato'][0]['fecha_inicio'] . '</td>
                        </tr>
                        <tr>
                            <td><strong>Fecha de Fin</strong></td>
                            <td>' . $data_contrato2['data_mandato'][0]['fecha_fin'] . '</td>
                        </tr>
                        <tr>
                            <td><strong>Plazo de Duración</strong></td>
                            <td>' . $data_contrato2['data_mandato'][0]['plazo_duracion'] . ' días</td>
                        </tr>
                    </table>
                    <div style="width: 700px; text-align: center; font-family: arial;">
                        <a href="http://contratos.kurax.test:90/?sec_id=contrato&sub_sec_id=detalle_solicitud_mandato&id=' . $contrato_id . '" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">
                            <b>Ver Detalles del Contrato</b>
                        </a>
                    </div>
                </body>
                </html>';
                // var_dump($body);
                // die("body");
            
                $correos = new Correos(env('SEND_EMAIL'), env('EMAIL_TEST'));
                $lista_correos = $correos->enviar_correo_notificacion_abogado($contrato_id);
                $cc = $lista_correos['cc'];
                $bcc = $lista_correos['bcc'];
            
                // Título específico para contrato firmado
                $titulo = "Gestión - Sistema Contratos - Contrato de Mandato Firmado: Código - M" . $codigo_correlativo;
            
                $request = [
                    "subject" => $titulo,
                    "body"    => $body,
                    "cc"      => $cc,
                    "bcc"     => $bcc,
                    "attach"  => [],
                ];
            
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = "smtp.gmail.com";
                    $mail->SMTPAuth = true;
                    $mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
                    $mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port = 465;
                    $mail->From = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
                    $mail->FromName = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');
                    if (isset($request["cc"])) {
                        foreach ($request["cc"] as $cc) {
                            $mail->addAddress($cc);
                        }
                    }
                    if (isset($request["bcc"])) {
                        foreach ($request["bcc"] as $bcc) {
                            $mail->addBCC($bcc);
                        }
                    }
                    $mail->isHTML(true);
                    $mail->Subject = $request["subject"];
                    $mail->Body = $request["body"];
                    $mail->CharSet = 'UTF-8';
                    $mail->Encoding = 'base64';
                    $mail->send();
                } catch (Exception $e) {
                    $result["http_code"] = 400;
                    $result["status"] = "Datos obtenidos de gestión.";
                    $result["error_title"] = "La solicitud se registró correctamente, pero no se pudo enviar el email.";
                    $result["error"] = $mail->ErrorInfo;
                    echo json_encode($result);
                    exit();
                }
            }
            //////////////////////////////////////////////////

    public function registrar_ficha_contrato($contrato_id, $contratos)
    {


        try {

            $model_contrato =  new ContratoArrendamiento();
            if (($model_contrato->get_usuario_id()) > 0) {
                $created_at = date("Y-m-d H:i:s");
                $nuevos_detalles_id = [];
                //contratos
                for ($l = 0; $l < count($contratos); $l++) {
                    $contrato = $contratos[$l];

                    $modal_contrato_detalle = new ContratoArrendamientoDetalle();
                    $data_contrato_detalle['contrato_id'] = $contrato_id;
                    $data_contrato_detalle['codigo'] = NULL;
                    $data_contrato_detalle['observaciones'] = $contrato['observaciones'];
                    $data_contrato_detalle['status'] = 1;
                    $data_contrato_detalle['user_created_id'] = $model_contrato->get_usuario_id();
                    $data_contrato_detalle['created_at'] = $created_at;
                    $insert_contrato_detalle = $modal_contrato_detalle->registrar($data_contrato_detalle);
                    if ($insert_contrato_detalle['status'] == 404) {
                        return $insert_contrato_detalle;
                    }

                    $contrato_detalle_id = $insert_contrato_detalle['result'];
                    $contratos[$l]['contrato_detalle_id'] = $contrato_detalle_id;
                    array_push($nuevos_detalles_id, $contrato_detalle_id);
                    //inmueble
                    $inmueble = $contrato['inmuebles'];

                    if (empty($inmueble["porcentaje_pago_arbitrios"])) {
                        $porcentaje_pago_arbitrios = "NULL";
                    } else {
                        $porcentaje_pago_arbitrios = $inmueble["porcentaje_pago_arbitrios"];
                    }

                    $model_inmueble = new Inmueble();
                    $data_inmueble['contrato_id'] = $contrato_id;
                    $data_inmueble['contrato_detalle_id'] = $contrato_detalle_id;
                    $data_inmueble['ubigeo_id'] = $inmueble['ubigeo_id'];
                    $data_inmueble['ubicacion'] = $inmueble['ubicacion'];
                    $data_inmueble['area_arrendada'] = str_replace(",", "", trim($inmueble['area_arrendada']));
                    $data_inmueble['num_partida_registral'] = $inmueble['num_partida_registral'];
                    $data_inmueble['oficina_registral'] = $inmueble['oficina_registral'];
                    $data_inmueble['num_suministro_agua'] = ''; // ya no se usa
                    $data_inmueble['tipo_compromiso_pago_agua'] = 0; // ya no se usa
                    $data_inmueble['monto_o_porcentaje_agua'] = 0; // ya no se usa
                    $data_inmueble['num_suministro_luz'] = ''; // ya no se usa
                    $data_inmueble['tipo_compromiso_pago_luz'] = 0; // ya no se usa
                    $data_inmueble['monto_o_porcentaje_luz'] = 0; // ya no se usa
                    $data_inmueble['tipo_compromiso_pago_arbitrios'] = $inmueble['tipo_compromiso_pago_arbitrios'];
                    $data_inmueble['porcentaje_pago_arbitrios'] = $porcentaje_pago_arbitrios;
                    $data_inmueble['latitud'] = $inmueble['latitud'];
                    $data_inmueble['longitud'] = $inmueble['longitud'];
                    $data_inmueble['user_created_id'] = $model_contrato->get_usuario_id();
                    $data_inmueble['created_at'] = $created_at;
                    $insert_inmueble = $model_inmueble->registrar($data_inmueble);
                    if ($insert_inmueble['status'] == 404) {
                        return $insert_inmueble;
                    }

                    $inmueble_suministro = $inmueble['inmueble_servicio_agua'];
                    foreach ($inmueble_suministro as $suministro) {
                        if (empty($suministro["monto_o_porcentaje"])) {
                            $monto_o_porcentaje = "NULL";
                        } else {
                            $monto_o_porcentaje = str_replace(",", "", $suministro['monto_o_porcentaje']);
                        }

                        $model_inmueble_suministro = new InmuebleSuministro();
                        $data_inmueble_suministro['contrato_id'] = $contrato_id;
                        $data_inmueble_suministro['inmueble_id'] = $insert_inmueble['result'];
                        $data_inmueble_suministro['tipo_servicio_id'] = 2; //agua
                        $data_inmueble_suministro['nro_suministro'] = $suministro['nro_suministro'];
                        $data_inmueble_suministro['tipo_compromiso_pago_id'] = 3; //3- Medidor propio (Totalidad del servicio)
                        $data_inmueble_suministro['monto_o_porcentaje'] = $monto_o_porcentaje;
                        $data_inmueble_suministro['tipo_documento_beneficiario'] = isset($suministro['tipo_documento_beneficiario']) ? $suministro['tipo_documento_beneficiario'] : '';
                        $data_inmueble_suministro['nombre_beneficiario'] = isset($suministro['nombre_beneficiario']) ? $suministro['nombre_beneficiario'] : '';
                        $data_inmueble_suministro['nro_documento_beneficiario'] = isset($suministro['nro_documento_beneficiario']) ? $suministro['nro_documento_beneficiario'] : '';
                        $data_inmueble_suministro['nro_cuenta_soles'] = isset($suministro['nro_cuenta_soles']) ? $suministro['nro_cuenta_soles'] : '';
                        $data_inmueble_suministro['status'] = 1;
                        $data_inmueble_suministro['user_created_id'] = $model_contrato->get_usuario_id();
                        $data_inmueble_suministro['created_at'] = $created_at;
                        $insert_inmueble_suministro = $model_inmueble_suministro->registrar($data_inmueble_suministro);
                        if ($insert_inmueble_suministro['status'] == 404) {
                            return $insert_inmueble_suministro;
                        }
                    }


                    $inmueble_suministro = $inmueble['inmueble_servicio_luz'];
                    foreach ($inmueble_suministro as $suministro) {
                        if (empty($suministro["monto_o_porcentaje"])) {
                            $monto_o_porcentaje = "NULL";
                        } else {
                            $monto_o_porcentaje = str_replace(",", "", $suministro['monto_o_porcentaje']);
                        }
                        $model_inmueble_suministro = new InmuebleSuministro();
                        $data_inmueble_suministro['contrato_id'] = $contrato_id;
                        $data_inmueble_suministro['inmueble_id'] = $insert_inmueble['result'];
                        $data_inmueble_suministro['tipo_servicio_id'] = 1; // luz
                        $data_inmueble_suministro['nro_suministro'] = $suministro['nro_suministro'];
                        $data_inmueble_suministro['tipo_compromiso_pago_id'] = 3; //3- Medidor propio (Totalidad del servicio)
                        $data_inmueble_suministro['monto_o_porcentaje'] = $monto_o_porcentaje;
                        $data_inmueble_suministro['tipo_documento_beneficiario'] = isset($suministro['tipo_documento_beneficiario']) ? $suministro['tipo_documento_beneficiario'] : '';
                        $data_inmueble_suministro['nombre_beneficiario'] = isset($suministro['nombre_beneficiario']) ? $suministro['nombre_beneficiario'] : '';
                        $data_inmueble_suministro['nro_documento_beneficiario'] = isset($suministro['nro_documento_beneficiario']) ? $suministro['nro_documento_beneficiario'] : '';
                        $data_inmueble_suministro['nro_cuenta_soles'] = isset($suministro['nro_cuenta_soles']) ? $suministro['nro_cuenta_soles'] : '';
                        $data_inmueble_suministro['status'] = 1;
                        $data_inmueble_suministro['user_created_id'] = $model_contrato->get_usuario_id();
                        $data_inmueble_suministro['created_at'] = $created_at;
                        $insert_inmueble_suministro = $model_inmueble_suministro->registrar($data_inmueble_suministro);
                        if ($insert_inmueble_suministro['status'] == 404) {
                            return $insert_inmueble_suministro;
                        }
                    }




                    //condicion_economica
                    $condicion_economica = $contrato['condicion_economica'];
                    $model_condicion_economica = new CondicionEconomica();
                    $data_cond_eco['contrato_id'] = $contrato_id;
                    $data_cond_eco['contrato_detalle_id'] = $contrato_detalle_id;
                    $data_cond_eco['monto_renta'] = str_replace(",", "", $condicion_economica["monto_renta"]);
                    $data_cond_eco['tipo_moneda_id'] = $condicion_economica["tipo_moneda_id"];
                    $data_cond_eco['pago_renta_id'] = $condicion_economica["pago_renta_id"];
                    $data_cond_eco['afectacion_igv_id'] = $condicion_economica["afectacion_igv_id"];
                    $data_cond_eco['impuesto_a_la_renta_id'] = $condicion_economica["impuesto_a_la_renta_id"];
                    $data_cond_eco['carta_de_instruccion_id'] = $condicion_economica["carta_de_instruccion_id"];
                    $data_cond_eco['garantia_monto'] = str_replace(",", "", $condicion_economica["garantia_monto"]);
                    $data_cond_eco['tipo_adelanto_id'] = $condicion_economica["tipo_adelanto_id"];
                    $data_cond_eco['plazo_id'] = $condicion_economica["plazo_id"];
                    $data_cond_eco['cant_meses_contrato'] = trim($condicion_economica["cant_meses_contrato"]);
                    $data_cond_eco['fecha_inicio'] = $condicion_economica["fecha_inicio"];
                    $data_cond_eco['fecha_fin'] = $condicion_economica["fecha_fin"];
                    $data_cond_eco['fecha_suscripcion'] = trim($condicion_economica["fecha_suscripcion"]);
                    $data_cond_eco['periodo_gracia_id'] = $condicion_economica["periodo_gracia_id"];
                    $data_cond_eco['periodo_gracia_numero'] = trim($condicion_economica["periodo_gracia_numero"]);
                    $data_cond_eco['tipo_incremento_id'] = $condicion_economica["tipo_incremento_id"];
                    $data_cond_eco['tipo_inflacion_id'] = $condicion_economica["tipo_inflacion_id"];
                    $data_cond_eco['tipo_cuota_extraordinaria_id'] = $condicion_economica["tipo_cuota_extraordinaria_id"];
                    $data_cond_eco['tipo_terminacion_renovacion_id'] = $condicion_economica["tipo_terminacion_renovacion_id"];
                    $data_cond_eco['usuario_contrato_aprobado_id'] = $condicion_economica["usuario_contrato_aprobado_id"];
                    $data_cond_eco['user_created_id'] = $model_contrato->get_usuario_id();
                    $data_cond_eco['created_at'] = $created_at;

                    if ((int) $data_cond_eco['pago_renta_id'] == 2) {
                        $data_cond_eco['cuota_variable'] = $condicion_economica["cuota_variable"];
                        $data_cond_eco['tipo_venta_id'] = $condicion_economica["tipo_venta_id"];
                    } else {
                        $data_cond_eco['cuota_variable'] = "NULL";
                        $data_cond_eco['tipo_venta_id'] = "NULL";
                    }

                    if ((int) $data_cond_eco['impuesto_a_la_renta_id'] == 4) {
                        $data_cond_eco['numero_cuenta_detraccion'] = "'" . trim($condicion_economica["numero_cuenta_detraccion"]) . "'";
                    } else {
                        $data_cond_eco['numero_cuenta_detraccion'] = "NULL";
                    }

                    if ($data_cond_eco['plazo_id'] == 1) {
                        if (empty($data_cond_eco['cant_meses_contrato'])) {
                            $data_cond_eco['cant_meses_contrato'] = "NULL";
                        }

                        if (empty($data_cond_eco['fecha_inicio'])) {
                            $data_cond_eco['fecha_inicio'] = "NULL";
                        } else {
                            $data_cond_eco['fecha_inicio'] = "'" . date("Y-m-d", strtotime($data_cond_eco['fecha_inicio'])) . "'";
                        }

                        if (empty($data_cond_eco['fecha_fin'])) {
                            $data_cond_eco['fecha_fin'] = "NULL";
                        } else {
                            $data_cond_eco['fecha_fin'] = "'" . date("Y-m-d", strtotime($data_cond_eco['fecha_fin'])) . "'";
                        }

                        if (empty($data_cond_eco['fecha_suscripcion'])) {
                            $data_cond_eco['fecha_suscripcion'] = "NULL";
                        } else {
                            $data_cond_eco['fecha_suscripcion'] = "'" . date("Y-m-d", strtotime($data_cond_eco['fecha_suscripcion'])) . "'";
                        }
                    } else {

                        $data_cond_eco['cant_meses_contrato'] = "NULL";

                        if (empty($data_cond_eco['fecha_inicio'])) {
                            $data_cond_eco['fecha_inicio'] = "NULL";
                        } else {
                            $data_cond_eco['fecha_inicio'] = "'" . date("Y-m-d", strtotime($data_cond_eco['fecha_inicio'])) . "'";
                        }

                        $data_cond_eco['fecha_fin'] = "NULL";

                        if (empty($data_cond_eco['fecha_suscripcion'])) {
                            $data_cond_eco['fecha_suscripcion'] = "NULL";
                        } else {
                            $data_cond_eco['fecha_suscripcion'] = "'" . date("Y-m-d", strtotime($data_cond_eco['fecha_suscripcion'])) . "'";
                        }
                    }

                    if ($data_cond_eco['periodo_gracia_id'] == "0") {
                        $data_cond_eco['periodo_gracia_id'] = "NULL";
                        $data_cond_eco['periodo_gracia_numero'] = "NULL";
                    } else if ($data_cond_eco['periodo_gracia_id'] == "1") {
                        if (empty($data_cond_eco['periodo_gracia_numero'])) {
                            $data_cond_eco['periodo_gracia_numero'] = "NULL";
                        }
                    } else if ($data_cond_eco['periodo_gracia_id'] == "2") {
                        $data_cond_eco['periodo_gracia_numero'] = "NULL";
                    }

                    $insert_cond_eco = $model_condicion_economica->registrar($data_cond_eco);
                    if ($insert_cond_eco['status'] == 404) {

                        return $insert_cond_eco;
                    }


                    //incrementos
                    if ($data_cond_eco['tipo_incremento_id'] == 1) {
                        $incrementos = $condicion_economica["incrementos"];
                        $model_incremento = new Incremento();
                        foreach ($incrementos as $incremento) {
                            if (empty($incremento['a_partir_del_anio_id'])) {
                                $a_partir_del_anio_id = "2";
                            } else {
                                $a_partir_del_anio_id = $incremento['a_partir_del_anio_id'];
                            }
                            $fecha_cambio = 'NULL';
                            $fecha_inicio_contrato = str_replace("'", "", $data_cond_eco['fecha_inicio']);
                            if ($fecha_inicio_contrato != "NULL") {
                                if ($incremento['tipo_continuidad_id'] == 1) { //el
                                    $fecha_inicio_contrato = $fecha_inicio_contrato;
                                    $fecha_cambio = strtotime('+' . $a_partir_del_anio_id . ' year', strtotime($fecha_inicio_contrato));
                                    $fecha_cambio = "'" . date('Y-m-d', $fecha_cambio) . "'";
                                } else if ($incremento['tipo_continuidad_id'] == 2) { //al inicio deL
                                    $fecha_inicio_contrato = $fecha_inicio_contrato;
                                    $fecha_cambio = strtotime('+' . $a_partir_del_anio_id . ' year', strtotime($fecha_inicio_contrato));
                                    $fecha_cambio = "'" . date('Y-m-d', $fecha_cambio) . "'";
                                } else if ($incremento['tipo_continuidad_id'] == 3) { //anual
                                    $fecha_inicio_contrato = $fecha_inicio_contrato;
                                    $fecha_cambio = strtotime('+1 year', strtotime($fecha_inicio_contrato));
                                    $fecha_cambio = "'" . date('Y-m-d', $fecha_cambio) . "'";
                                }
                            }

                            $data_incremento['contrato_id'] = $contrato_id;
                            $data_incremento['contrato_detalle_id'] = $contrato_detalle_id;
                            $data_incremento['valor'] = str_replace(",", "", $incremento["valor"]);
                            $data_incremento['tipo_valor_id'] = $incremento['tipo_valor_id'];
                            $data_incremento['tipo_continuidad_id'] = $incremento['tipo_continuidad_id'];
                            $data_incremento['a_partir_del_anio_id'] = $a_partir_del_anio_id;
                            $data_incremento['fecha_cambio'] = $fecha_cambio;
                            $data_incremento['estado'] = 1;
                            $data_incremento['user_created_id'] = $model_contrato->get_usuario_id();
                            $data_incremento['created_at'] = $created_at;
                            $insert_incremento = $model_incremento->registrar($data_incremento);
                            if ($insert_incremento['status'] == 404) {

                                return $insert_incremento;
                            }
                        }
                    }

                    //inflaciones
                    if ($data_cond_eco['tipo_inflacion_id'] == 1) {
                        $inflaciones = $condicion_economica["inflaciones"];
                        $model_inflacion = new Inflacion();
                        foreach ($inflaciones as $inflacion) {
                            $numero = isset($inflacion['numero']) && !empty($inflacion['numero']) ? $inflacion['numero'] : '0';
                            $tipo_anio_mes_id = isset($inflacion['tipo_anio_mes_id']) && !empty($inflacion['tipo_anio_mes_id']) ? $inflacion['tipo_anio_mes_id'] : '0';
                            $porcentaje_anadido = !empty($inflacion['porcentaje_anadido']) ? $inflacion['porcentaje_anadido'] : '0';
                            $tope_inflacion = !empty($inflacion['tope_inflacion']) ? $inflacion['tope_inflacion'] : '0';
                            $minimo_inflacion = !empty($inflacion['minimo_inflacion']) ? $inflacion['minimo_inflacion'] : '0';

                            $fecha_cambio = 'NULL';
                            $fecha_inicio_contrato = str_replace("'", "", $data_cond_eco['fecha_inicio']);
                            if ($inflacion['tipo_periodicidad_id'] == 1) { //cada
                                if ($tipo_anio_mes_id == 1) { //año
                                    $fecha_cambio = strtotime('+' . $numero . ' year', strtotime($fecha_inicio_contrato));
                                    $fecha_cambio = "'" . date('Y-m-d', strtotime($fecha_cambio)) . "'";
                                }
                                if ($tipo_anio_mes_id == 2) { //meses
                                    $fecha_cambio = strtotime('+' . $numero . ' month', strtotime($fecha_inicio_contrato));
                                    $fecha_cambio = "'" . date('Y-m-d', strtotime($fecha_cambio)) . "'";
                                }
                            } else if ($inflacion['tipo_periodicidad_id'] == 2) { // Al inicio de cada año
                                $anio = date('Y', strtotime($fecha_inicio_contrato));
                                $anio = (int)$anio + 1;

                                $fecha_cambio = "'" . $anio . "-01-01'";
                            }

                            $data_inflacion['contrato_id'] = $contrato_id;
                            $data_inflacion['contrato_detalle_id'] = $contrato_detalle_id;
                            $data_inflacion['fecha'] = $fecha_cambio;
                            $data_inflacion['tipo_periodicidad_id'] = $inflacion['tipo_periodicidad_id'];
                            $data_inflacion['numero'] = $numero;
                            $data_inflacion['tipo_anio_mes'] = $tipo_anio_mes_id;
                            $data_inflacion['moneda_id'] = $data_cond_eco['tipo_moneda_id'];
                            $data_inflacion['porcentaje_anadido'] = $porcentaje_anadido;
                            $data_inflacion['tope_inflacion'] = $tope_inflacion;
                            $data_inflacion['minimo_inflacion'] = $minimo_inflacion;
                            $data_inflacion['tipo_aplicacion_id'] = $inflacion['tipo_aplicacion_id'];
                            $data_inflacion['status'] = 1;
                            $data_inflacion['user_created_id'] = $model_contrato->get_usuario_id();
                            $data_inflacion['created_at'] = $created_at;

                            $insert_inflacion = $model_inflacion->registrar($data_inflacion);
                            if ($insert_inflacion['status'] == 404) {

                                return $insert_inflacion;
                            }
                        }
                    }

                    //cuotas extraordinarias
                    if ($data_cond_eco['tipo_cuota_extraordinaria_id'] == 1) {
                        $cuotas_extraordinarias = $condicion_economica["cuotas_extraordinarias"];
                        $model_cuota_extraordinaria = new CuotaExtraordinaria();
                        foreach ($cuotas_extraordinarias as $cuota_extraordinaria) {
                            $data_cuota_extraordinaria['contrato_id'] = $contrato_id;
                            $data_cuota_extraordinaria['contrato_detalle_id'] = $contrato_detalle_id;
                            $data_cuota_extraordinaria['mes'] = $cuota_extraordinaria['mes_id'];
                            $data_cuota_extraordinaria['multiplicador'] = $cuota_extraordinaria['multiplicador'];
                            $data_cuota_extraordinaria['status'] = 1;
                            $data_cuota_extraordinaria['user_created_id'] = $model_contrato->get_usuario_id();
                            $data_cuota_extraordinaria['created_at'] = $created_at;

                            $insert_cuota = $model_cuota_extraordinaria->registrar($data_cuota_extraordinaria);
                            if ($insert_cuota['status'] == 404) {

                                return $insert_cuota;
                            }
                        }
                    }

                    //Beneficiarios
                    $beneficiarios = $condicion_economica["beneficiarios"];
                    $modal_beneficiario = new Beneficiario();
                    foreach ($beneficiarios as $beneficiario) {
                        $data_beneficiario['id'] = $beneficiario['id'];
                        $data_beneficiario['contrato_detalle_id'] = $contrato_detalle_id;
                        $data_beneficiario['contrato_id'] = $contrato_id;
                        $data_beneficiario['user_updated_id'] = $model_contrato->get_usuario_id();
                        $data_beneficiario['updated_at'] = $created_at;

                        $insert_beneficiario = $modal_beneficiario->asignar_contrato($data_beneficiario);
                        if ($insert_beneficiario['status'] == 404) {

                            return $insert_beneficiario;
                        }
                    }


                    //Responsable IR
                    $responsables_ir = $condicion_economica["responsables_ir"];
                    $modal_responsable_ir = new ResponsableIR();
                    foreach ($responsables_ir as $responsable_ir) {
                        $data_responsable_ir['id'] = $responsable_ir['id'];
                        $data_responsable_ir['contrato_detalle_id'] = $contrato_detalle_id;
                        $data_responsable_ir['contrato_id'] = $contrato_id;
                        $data_responsable_ir['user_updated_id'] = $model_contrato->get_usuario_id();
                        $data_responsable_ir['updated_at'] = $created_at;

                        $insert_beneficiario = $modal_responsable_ir->asignar_contrato($data_responsable_ir);
                        if ($insert_beneficiario['status'] == 404) {

                            return $insert_beneficiario;
                        }
                    }


                    //adelantos
                    if ($data_cond_eco['tipo_adelanto_id'] == 1) {
                        $adelantos = $condicion_economica["adelantos"];
                        $modal_adelanto = new Adelanto();
                        foreach ($adelantos as $adelanto) {
                            $data_adelanto['num_periodo'] = $adelanto['id'];
                            $data_adelanto['contrato_id'] = $contrato_id;
                            $data_adelanto['contrato_detalle_id'] = $contrato_detalle_id;
                            $data_adelanto['status'] = 1;
                            $data_adelanto['user_created_id'] = $model_contrato->get_usuario_id();
                            $data_adelanto['created_at'] = $created_at;

                            $insert_adelante = $modal_adelanto->registrar($data_adelanto);
                            if ($insert_adelante['status'] == 404) {

                                return $insert_adelante;
                            }
                        }
                    }
                }

                $result['status'] = 200;
                $result['result'] = [];
                $result['nuevos_detalles_id'] = $nuevos_detalles_id;
                $result['contratos'] = $contratos;
                $result['message'] = 'El contrato se ha registrado exitosamente';
                return $result;
            }
            $result['status'] = 404;
            $result['result'] = '';
            $result['nuevos_detalles_id'] = [];
            $result['contratos'] = $contratos;
            $result['message'] = 'Su session ha finalizado, por favor ingrese de nuevo.';
            $result['errors_uploads'] = '';
            return $result;
        } catch (Exception $th) {

            $result['status'] = 404;
            $result['result'] = $th;
            $result['contratos'] = [];
            $result['message'] = 'A ocurrido un error aa.';
            return $result;
        }
    }
    public function registrar_ficha_contratoV2($contrato_id, $contratos)
    {


        try {

            $model_contrato =  new ContratoMandato();
            if (($model_contrato->get_usuario_id()) > 0) {
                $created_at = date("Y-m-d H:i:s");
                $nuevos_detalles_id = [];
                //contratos
                for ($l = 0; $l < count($contratos); $l++) {
                    $contrato = $contratos[$l];

                    $modal_contrato_detalle = new ContratoArrendamientoDetalle();
                    $data_contrato_detalle['contrato_id'] = $contrato_id;
                    $data_contrato_detalle['codigo'] = NULL;
                    $data_contrato_detalle['observaciones'] = $contrato['observaciones'];
                    $data_contrato_detalle['status'] = 1;
                    $data_contrato_detalle['user_created_id'] = $model_contrato->get_usuario_id();
                    $data_contrato_detalle['created_at'] = $created_at;
                    $insert_contrato_detalle = $modal_contrato_detalle->registrar($data_contrato_detalle);
                    if ($insert_contrato_detalle['status'] == 404) {
                        return $insert_contrato_detalle;
                    }

                    $contrato_detalle_id = $insert_contrato_detalle['result'];
                    $contratos[$l]['contrato_detalle_id'] = $contrato_detalle_id;
                    array_push($nuevos_detalles_id, $contrato_detalle_id);

                    $model_mandato = new ContratoMandato();
                    //var_dump($contrato);
                    //die();
                    // idcontrato,
                    // mandante_antecedente,
                    // mandataria_objetivo,
                    // mandataria_retribucion,
                    // plazo_duracion,
                    // fechainicio,
                    // fechafin
                    $data_mandato = array(
                        'idcontrato' => $contrato_id,
                        'mandante_antecedente' => $contrato['mandante_antecedente'],
                        'fecha_inicio' => $contrato['fecha_inicio'],
                        'fecha_fin' => $contrato['fecha_fin'],
                        'mandataria_objetivo' => $contrato['mandataria_objetivo'],
                        'mandataria_retribucion' => $contrato['mandataria_retribucion'],
                        'plazo_duracion' => $contrato['plazo_duracion']
                    );

                    $insert_locacion = $model_mandato->registrar_mandato($data_mandato);

                    if ($insert_locacion['status'] == 404) {
                        return $insert_locacion;
                    }
                }

                $result['status'] = 200;
                $result['result'] = [];
                $result['nuevos_detalles_id'] = $nuevos_detalles_id;
                $result['contratos'] = $contratos;
                $result['message'] = 'El contrato se ha registrado exitosamente';
                return $result;
            }
            $result['status'] = 404;
            $result['result'] = '';
            $result['nuevos_detalles_id'] = [];
            $result['contratos'] = $contratos;
            $result['message'] = 'Su session ha finalizado, por favor ingrese de nuevo.';
            $result['errors_uploads'] = '';
            return $result;
        } catch (Exception $th) {

            $result['status'] = 404;
            $result['result'] = $th;
            $result['contratos'] = [];
            $result['message'] = 'A ocurrido un error aa.';
            return $result;
        }
    }

    public function subir_archivos_contrato_arrendamiento($contrato_id, $files, $contratos)
    {
        try {

            $created_at = date("Y-m-d H:i:s");
            $model_contrato =  new ContratoArrendamiento();
            $errors_uploads = '';
            // CARGA DE ANEXOS
            // INICIO CARGAR PDF
            $path = "/var/www/html/files_bucket/contratos/solicitudes/locales/";

            for ($file_index = 0; $file_index < count($contratos); $file_index++) {

                $contrato = $contratos[$file_index];
                if (isset($files['archivo_partida_registral_' . $file_index]) && $files['archivo_partida_registral_' . $file_index]['error'] === UPLOAD_ERR_OK) {
                    if (!is_dir($path)) mkdir($path, 0777, true);

                    $filename = $files['archivo_partida_registral_' . $file_index]['name'];
                    $filenametem = $files['archivo_partida_registral_' . $file_index]['tmp_name'];
                    $filesize = $files['archivo_partida_registral_' . $file_index]['size'];
                    $ext = pathinfo($filenametem, PATHINFO_EXTENSION);
                    if ($filename != "") {
                        $fileExt = pathinfo($filename, PATHINFO_EXTENSION);

                        $nombre_archivo = $contrato_id . "_PARTIDA_REGISTRAL_" . date('YmdHis') . "." . $fileExt;
                        $model_archivo = new Archivo();
                        $data_archivo['contrato_id'] = $contrato_id;
                        $data_archivo['contrato_detalle_id'] = $contrato['contrato_detalle_id'];
                        $data_archivo['tipo_archivo_id'] = 8;
                        $data_archivo['nombre'] = $nombre_archivo;
                        $data_archivo['extension'] = $fileExt;
                        $data_archivo['size'] = $filesize;
                        $data_archivo['ruta'] = $path;
                        $data_archivo['user_created_id'] = $model_contrato->get_usuario_id();
                        $data_archivo['created_at'] = $created_at;
                        $insert_archivo = $model_archivo->registrar($data_archivo);

                        if ($insert_archivo['status'] == 404) {
                            $errors_uploads .= 'Error al guardar la partida registral. ';
                        } else {
                            move_uploaded_file($filenametem, $path . $nombre_archivo);
                        }
                    }
                }

                if (isset($files['archivo_recibo_agua_' . $file_index]) && $files['archivo_recibo_agua_' . $file_index]['error'] === UPLOAD_ERR_OK) {
                    if (!is_dir($path)) mkdir($path, 0777, true);

                    $filename = $files['archivo_recibo_agua_' . $file_index]['name'];
                    $filenametem = $files['archivo_recibo_agua_' . $file_index]['tmp_name'];
                    $filesize = $files['archivo_recibo_agua_' . $file_index]['size'];
                    $ext = pathinfo($filenametem, PATHINFO_EXTENSION);
                    if ($filename != "") {
                        $fileExt = pathinfo($filename, PATHINFO_EXTENSION);

                        $nombre_archivo = $contrato_id . "_RECIBO_AGUA_" . date('YmdHis') . "." . $fileExt;
                        $model_archivo = new Archivo();
                        $data_archivo['contrato_id'] = $contrato_id;
                        $data_archivo['contrato_detalle_id'] = $contrato['contrato_detalle_id'];
                        $data_archivo['tipo_archivo_id'] = 9;
                        $data_archivo['nombre'] = $nombre_archivo;
                        $data_archivo['extension'] = $fileExt;
                        $data_archivo['size'] = $filesize;
                        $data_archivo['ruta'] = $path;
                        $data_archivo['user_created_id'] = $model_contrato->get_usuario_id();
                        $data_archivo['created_at'] = $created_at;
                        $insert_archivo = $model_archivo->registrar($data_archivo);

                        if ($insert_archivo['status'] == 404) {
                            $errors_uploads .= 'Error al guardar el recibo de agua. ';
                        } else {
                            move_uploaded_file($filenametem, $path . $nombre_archivo);
                        }
                    }
                }

                if (isset($files['archivo_recibo_luz_' . $file_index]) && $files['archivo_recibo_luz_' . $file_index]['error'] === UPLOAD_ERR_OK) {
                    if (!is_dir($path)) mkdir($path, 0777, true);

                    $filename = $files['archivo_recibo_luz_' . $file_index]['name'];
                    $filenametem = $files['archivo_recibo_luz_' . $file_index]['tmp_name'];
                    $filesize = $files['archivo_recibo_luz_' . $file_index]['size'];
                    $ext = pathinfo($filenametem, PATHINFO_EXTENSION);
                    if ($filename != "") {
                        $fileExt = pathinfo($filename, PATHINFO_EXTENSION);

                        $nombre_archivo = $contrato_id . "_RECIBO_LUZ_" . date('YmdHis') . "." . $fileExt;
                        $model_archivo = new Archivo();
                        $data_archivo['contrato_id'] = $contrato_id;
                        $data_archivo['contrato_detalle_id'] = $contrato['contrato_detalle_id'];
                        $data_archivo['tipo_archivo_id'] = 10;
                        $data_archivo['nombre'] = $nombre_archivo;
                        $data_archivo['extension'] = $fileExt;
                        $data_archivo['size'] = $filesize;
                        $data_archivo['ruta'] = $path;
                        $data_archivo['user_created_id'] = $model_contrato->get_usuario_id();
                        $data_archivo['created_at'] = $created_at;
                        $insert_archivo = $model_archivo->registrar($data_archivo);

                        if ($insert_archivo['status'] == 404) {
                            $errors_uploads .= 'Error al guardar el recibo de luz. ';
                        } else {
                            move_uploaded_file($filenametem, $path . $nombre_archivo);
                        }
                    }
                }

                if (isset($files['archivo_dni_propietario_' . $file_index]) && $files['archivo_dni_propietario_' . $file_index]['error'] === UPLOAD_ERR_OK) {
                    if (!is_dir($path)) mkdir($path, 0777, true);

                    $filename = $files['archivo_dni_propietario_' . $file_index]['name'];
                    $filenametem = $files['archivo_dni_propietario_' . $file_index]['tmp_name'];
                    $filesize = $files['archivo_dni_propietario_' . $file_index]['size'];
                    $ext = pathinfo($filenametem, PATHINFO_EXTENSION);
                    if ($filename != "") {
                        $fileExt = pathinfo($filename, PATHINFO_EXTENSION);

                        $nombre_archivo = $contrato_id . "_DNI_PROPIETARIO_" . date('YmdHis') . "." . $fileExt;
                        $model_archivo = new Archivo();
                        $data_archivo['contrato_id'] = $contrato_id;
                        $data_archivo['contrato_detalle_id'] = $contrato['contrato_detalle_id'];
                        $data_archivo['tipo_archivo_id'] = 11;
                        $data_archivo['nombre'] = $nombre_archivo;
                        $data_archivo['extension'] = $fileExt;
                        $data_archivo['size'] = $filesize;
                        $data_archivo['ruta'] = $path;
                        $data_archivo['user_created_id'] = $model_contrato->get_usuario_id();
                        $data_archivo['created_at'] = $created_at;
                        $insert_archivo = $model_archivo->registrar($data_archivo);

                        if ($insert_archivo['status'] == 404) {
                            $errors_uploads .= 'Error al guardar el DNI del propietario. ';
                        } else {
                            move_uploaded_file($filenametem, $path . $nombre_archivo);
                        }
                    }
                }

                if (isset($files['archivo_vigencia_poder_' . $file_index]) && $files['archivo_vigencia_poder_' . $file_index]['error'] === UPLOAD_ERR_OK) {
                    if (!is_dir($path)) mkdir($path, 0777, true);

                    $filename = $files['archivo_vigencia_poder_' . $file_index]['name'];
                    $filenametem = $files['archivo_vigencia_poder_' . $file_index]['tmp_name'];
                    $filesize = $files['archivo_vigencia_poder_' . $file_index]['size'];
                    $ext = pathinfo($filenametem, PATHINFO_EXTENSION);
                    if ($filename != "") {
                        $fileExt = pathinfo($filename, PATHINFO_EXTENSION);

                        $nombre_archivo = $contrato_id . "_VIGENCIA_PODER_" . date('YmdHis') . "." . $fileExt;
                        $model_archivo = new Archivo();
                        $data_archivo['contrato_id'] = $contrato_id;
                        $data_archivo['contrato_detalle_id'] = $contrato['contrato_detalle_id'];
                        $data_archivo['tipo_archivo_id'] = 12;
                        $data_archivo['nombre'] = $nombre_archivo;
                        $data_archivo['extension'] = $fileExt;
                        $data_archivo['size'] = $filesize;
                        $data_archivo['ruta'] = $path;
                        $data_archivo['user_created_id'] = $model_contrato->get_usuario_id();
                        $data_archivo['created_at'] = $created_at;
                        $insert_archivo = $model_archivo->registrar($data_archivo);

                        if ($insert_archivo['status'] == 404) {
                            $errors_uploads .= 'Error al guardar la vigencia poder. ';
                        } else {
                            move_uploaded_file($filenametem, $path . $nombre_archivo);
                        }
                    }
                }

                if (isset($files['archivo_dni_representante_legal_' . $file_index]) && $files['archivo_dni_representante_legal_' . $file_index]['error'] === UPLOAD_ERR_OK) {
                    if (!is_dir($path)) mkdir($path, 0777, true);

                    $filename = $files['archivo_dni_representante_legal_' . $file_index]['name'];
                    $filenametem = $files['archivo_dni_representante_legal_' . $file_index]['tmp_name'];
                    $filesize = $files['archivo_dni_representante_legal_' . $file_index]['size'];
                    $ext = pathinfo($filenametem, PATHINFO_EXTENSION);
                    if ($filename != "") {
                        $fileExt = pathinfo($filename, PATHINFO_EXTENSION);

                        $nombre_archivo = $contrato_id . "_DNI_REPRESENTANTE_LEGAL_" . date('YmdHis') . "." . $fileExt;
                        $model_archivo = new Archivo();
                        $data_archivo['contrato_id'] = $contrato_id;
                        $data_archivo['contrato_detalle_id'] = $contrato['contrato_detalle_id'];
                        $data_archivo['tipo_archivo_id'] = 13;
                        $data_archivo['nombre'] = $nombre_archivo;
                        $data_archivo['extension'] = $fileExt;
                        $data_archivo['size'] = $filesize;
                        $data_archivo['ruta'] = $path;
                        $data_archivo['user_created_id'] = $model_contrato->get_usuario_id();
                        $data_archivo['created_at'] = $created_at;
                        $insert_archivo = $model_archivo->registrar($data_archivo);

                        if ($insert_archivo['status'] == 404) {
                            $errors_uploads .= 'Error al guardar el DNI del representante legal. ';
                        } else {
                            move_uploaded_file($filenametem, $path . $nombre_archivo);
                        }
                    }
                }

                if (isset($files['archivo_hr_inmueble_' . $file_index]) && $files['archivo_hr_inmueble_' . $file_index]['error'] === UPLOAD_ERR_OK) {
                    if (!is_dir($path)) mkdir($path, 0777, true);

                    $filename = $files['archivo_hr_inmueble_' . $file_index]['name'];
                    $filenametem = $files['archivo_hr_inmueble_' . $file_index]['tmp_name'];
                    $filesize = $files['archivo_hr_inmueble_' . $file_index]['size'];
                    $ext = pathinfo($filenametem, PATHINFO_EXTENSION);
                    if ($filename != "") {
                        $fileExt = pathinfo($filename, PATHINFO_EXTENSION);

                        $nombre_archivo = $contrato_id . "_HR_INMUEBLE_" . date('YmdHis') . "." . $fileExt;
                        $model_archivo = new Archivo();
                        $data_archivo['contrato_id'] = $contrato_id;
                        $data_archivo['contrato_detalle_id'] = $contrato['contrato_detalle_id'];
                        $data_archivo['tipo_archivo_id'] = 14;
                        $data_archivo['nombre'] = $nombre_archivo;
                        $data_archivo['extension'] = $fileExt;
                        $data_archivo['size'] = $filesize;
                        $data_archivo['ruta'] = $path;
                        $data_archivo['user_created_id'] = $model_contrato->get_usuario_id();
                        $data_archivo['created_at'] = $created_at;
                        $insert_archivo = $model_archivo->registrar($data_archivo);

                        if ($insert_archivo['status'] == 404) {
                            $errors_uploads .= 'Error al guardar el HR del inmueble. ';
                        } else {
                            move_uploaded_file($filenametem, $path . $nombre_archivo);
                        }
                    }
                }

                if (isset($files['archivo_pu_inmueble_' . $file_index]) && $files['archivo_pu_inmueble_' . $file_index]['error'] === UPLOAD_ERR_OK) {
                    if (!is_dir($path)) mkdir($path, 0777, true);

                    $filename = $files['archivo_pu_inmueble_' . $file_index]['name'];
                    $filenametem = $files['archivo_pu_inmueble_' . $file_index]['tmp_name'];
                    $filesize = $files['archivo_pu_inmueble_' . $file_index]['size'];
                    $ext = pathinfo($filenametem, PATHINFO_EXTENSION);
                    if ($filename != "") {
                        $fileExt = pathinfo($filename, PATHINFO_EXTENSION);

                        $nombre_archivo = $contrato_id . "_PU_INMUEBLE_" . date('YmdHis') . "." . $fileExt;
                        $model_archivo = new Archivo();
                        $data_archivo['contrato_id'] = $contrato_id;
                        $data_archivo['contrato_detalle_id'] = $contrato['contrato_detalle_id'];
                        $data_archivo['tipo_archivo_id'] = 15;
                        $data_archivo['nombre'] = $nombre_archivo;
                        $data_archivo['extension'] = $fileExt;
                        $data_archivo['size'] = $filesize;
                        $data_archivo['ruta'] = $path;
                        $data_archivo['user_created_id'] = $model_contrato->get_usuario_id();
                        $data_archivo['created_at'] = $created_at;
                        $insert_archivo = $model_archivo->registrar($data_archivo);

                        if ($insert_archivo['status'] == 404) {
                            $errors_uploads .= 'Error al guardar el PU del inmueble. ';
                        } else {
                            move_uploaded_file($filenametem, $path . $nombre_archivo);
                        }
                    }
                }

                if (isset($files['archivo_pago_recibo_agua_' . $file_index]) && $files['archivo_pago_recibo_agua_' . $file_index]['error'] === UPLOAD_ERR_OK) {
                    if (!is_dir($path)) mkdir($path, 0777, true);

                    $filename = $files['archivo_pago_recibo_agua_' . $file_index]['name'];
                    $filenametem = $files['archivo_pago_recibo_agua_' . $file_index]['tmp_name'];
                    $filesize = $files['archivo_pago_recibo_agua_' . $file_index]['size'];
                    $ext = pathinfo($filenametem, PATHINFO_EXTENSION);
                    if ($filename != "") {
                        $fileExt = pathinfo($filename, PATHINFO_EXTENSION);

                        $nombre_archivo = $contrato_id . "_RECIBO_PAGO_AGUA_" . date('YmdHis') . "." . $fileExt;
                        $model_archivo = new Archivo();
                        $data_archivo['contrato_id'] = $contrato_id;
                        $data_archivo['contrato_detalle_id'] = $contrato['contrato_detalle_id'];
                        $data_archivo['tipo_archivo_id'] = 20;
                        $data_archivo['nombre'] = $nombre_archivo;
                        $data_archivo['extension'] = $fileExt;
                        $data_archivo['size'] = $filesize;
                        $data_archivo['ruta'] = $path;
                        $data_archivo['user_created_id'] = $model_contrato->get_usuario_id();
                        $data_archivo['created_at'] = $created_at;
                        $insert_archivo = $model_archivo->registrar($data_archivo);

                        if ($insert_archivo['status'] == 404) {
                            $errors_uploads .= 'Error al guardar el recibo de pago de agua del inmueble. ';
                        } else {
                            move_uploaded_file($filenametem, $path . $nombre_archivo);
                        }
                    }
                }

                if (isset($files['archivo_pago_recibo_luz_' . $file_index]) && $files['archivo_pago_recibo_luz_' . $file_index]['error'] === UPLOAD_ERR_OK) {
                    if (!is_dir($path)) mkdir($path, 0777, true);

                    $filename = $files['archivo_pago_recibo_luz_' . $file_index]['name'];
                    $filenametem = $files['archivo_pago_recibo_luz_' . $file_index]['tmp_name'];
                    $filesize = $files['archivo_pago_recibo_luz_' . $file_index]['size'];
                    $ext = pathinfo($filenametem, PATHINFO_EXTENSION);
                    if ($filename != "") {
                        $fileExt = pathinfo($filename, PATHINFO_EXTENSION);

                        $nombre_archivo = $contrato_id . "_RECIBO_PAGO_LUZ_" . date('YmdHis') . "." . $fileExt;
                        $model_archivo = new Archivo();
                        $data_archivo['contrato_id'] = $contrato_id;
                        $data_archivo['contrato_detalle_id'] = $contrato['contrato_detalle_id'];
                        $data_archivo['tipo_archivo_id'] = 21;
                        $data_archivo['nombre'] = $nombre_archivo;
                        $data_archivo['extension'] = $fileExt;
                        $data_archivo['size'] = $filesize;
                        $data_archivo['ruta'] = $path;
                        $data_archivo['user_created_id'] = $model_contrato->get_usuario_id();
                        $data_archivo['created_at'] = $created_at;
                        $insert_archivo = $model_archivo->registrar($data_archivo);

                        if ($insert_archivo['status'] == 404) {
                            $errors_uploads .= 'Error al guardar el recibo de pago de luz del inmueble. ';
                        } else {
                            move_uploaded_file($filenametem, $path . $nombre_archivo);
                        }
                    }
                }

                if (isset($files['archivo_pago_impuesto_predial_' . $file_index]) && $files['archivo_pago_impuesto_predial_' . $file_index]['error'] === UPLOAD_ERR_OK) {
                    if (!is_dir($path)) mkdir($path, 0777, true);

                    $filename = $files['archivo_pago_impuesto_predial_' . $file_index]['name'];
                    $filenametem = $files['archivo_pago_impuesto_predial_' . $file_index]['tmp_name'];
                    $filesize = $files['archivo_pago_impuesto_predial_' . $file_index]['size'];
                    $ext = pathinfo($filenametem, PATHINFO_EXTENSION);
                    if ($filename != "") {
                        $fileExt = pathinfo($filename, PATHINFO_EXTENSION);

                        $nombre_archivo = $contrato_id . "_RECIBO_PAGO_IMPUESTO_PREDIAL_" . date('YmdHis') . "." . $fileExt;
                        $model_archivo = new Archivo();
                        $data_archivo['contrato_id'] = $contrato_id;
                        $data_archivo['contrato_detalle_id'] = $contrato['contrato_detalle_id'];
                        $data_archivo['tipo_archivo_id'] = 22;
                        $data_archivo['nombre'] = $nombre_archivo;
                        $data_archivo['extension'] = $fileExt;
                        $data_archivo['size'] = $filesize;
                        $data_archivo['ruta'] = $path;
                        $data_archivo['user_created_id'] = $model_contrato->get_usuario_id();
                        $data_archivo['created_at'] = $created_at;
                        $insert_archivo = $model_archivo->registrar($data_archivo);

                        if ($insert_archivo['status'] == 404) {
                            $errors_uploads .= 'Error al guardar el recibo de pago de impuesto predial del inmueble. ';
                        } else {
                            move_uploaded_file($filenametem, $path . $nombre_archivo);
                        }
                    }
                }

                if (isset($files['archivo_pago_arbitrios_' . $file_index]) && $files['archivo_pago_arbitrios_' . $file_index]['error'] === UPLOAD_ERR_OK) {
                    if (!is_dir($path)) mkdir($path, 0777, true);

                    $filename = $files['archivo_pago_arbitrios_' . $file_index]['name'];
                    $filenametem = $files['archivo_pago_arbitrios_' . $file_index]['tmp_name'];
                    $filesize = $files['archivo_pago_arbitrios_' . $file_index]['size'];
                    $ext = pathinfo($filenametem, PATHINFO_EXTENSION);
                    if ($filename != "") {
                        $fileExt = pathinfo($filename, PATHINFO_EXTENSION);

                        $nombre_archivo = $contrato_id . "_RECIBO_PAGO_ARBITRIOS_" . date('YmdHis') . "." . $fileExt;
                        $model_archivo = new Archivo();
                        $data_archivo['contrato_id'] = $contrato_id;
                        $data_archivo['contrato_detalle_id'] = $contrato['contrato_detalle_id'];
                        $data_archivo['tipo_archivo_id'] = 23;
                        $data_archivo['nombre'] = $nombre_archivo;
                        $data_archivo['extension'] = $fileExt;
                        $data_archivo['size'] = $filesize;
                        $data_archivo['ruta'] = $path;
                        $data_archivo['user_created_id'] = $model_contrato->get_usuario_id();
                        $data_archivo['created_at'] = $created_at;
                        $insert_archivo = $model_archivo->registrar($data_archivo);

                        if ($insert_archivo['status'] == 404) {
                            $errors_uploads .= 'Error al guardar el recibo de pago de arbitrios del inmueble. ';
                        } else {
                            move_uploaded_file($filenametem, $path . $nombre_archivo);
                        }
                    }
                }

                $otros_anexos = $contrato['otros_anexos'];
                // INICIO DE CARGAR NUEVOS ANEXOS
                if (isset($files["miarchivo_" . $file_index])) {
                    if ($files["miarchivo_" . $file_index]) {
                        //Recorre el array de los archivos a subir
                        $h = '';
                        foreach ($files["miarchivo_" . $file_index]['tmp_name'] as $key => $tmp_name) {
                            //Si el archivo existe
                            if ($files["miarchivo_" . $file_index]["name"][$key]) {
                                $file_name = $files["miarchivo_" . $file_index]["name"][$key];
                                $fuente = $files["miarchivo_" . $file_index]["tmp_name"][$key];
                                $filesize = $files['miarchivo_' . $file_index]['size'][$key];

                                $fileExt = pathinfo($file_name, PATHINFO_EXTENSION);
                                $tipo_archivo_id = 0;
                                $nombre_tipo_archivo = "";

                                foreach ($otros_anexos as $value) {
                                    if ($value['file_name'] . "." . $value['file_extension'] == $file_name && $value['file_size']) {
                                        $nombre_tipo_archivo = str_replace(' ', '_', $value['file_name']);
                                        $nombre_tipo_archivo = strtoupper($nombre_tipo_archivo);
                                        $tipo_archivo_id = $value['id'];
                                    }
                                }

                                $nombre_archivo = $contrato_id . "_" . $nombre_tipo_archivo . "_" . date('YmdHis') . "." . $fileExt;
                                if ($tipo_archivo_id > 0) {
                                    if (!file_exists($path)) {
                                        mkdir($path, 0777); //or die("Hubo un error al crear la carpeta");	
                                    }
                                    $dir = opendir($path);
                                    if (move_uploaded_file($fuente, $path . '/' . $nombre_archivo)) {
                                        $model_archivo = new Archivo();
                                        $data_archivo['contrato_id'] = $contrato_id;
                                        $data_archivo['contrato_detalle_id'] = $contrato['contrato_detalle_id'];
                                        $data_archivo['tipo_archivo_id'] = $tipo_archivo_id;
                                        $data_archivo['nombre'] = $nombre_archivo;
                                        $data_archivo['extension'] = $fileExt;
                                        $data_archivo['size'] = $filesize;
                                        $data_archivo['ruta'] = $path;
                                        $data_archivo['user_created_id'] = $model_contrato->get_usuario_id();
                                        $data_archivo['created_at'] = $created_at;
                                        $insert_archivo = $model_archivo->registrar($data_archivo);

                                        if ($insert_archivo['status'] == 404) {
                                            $errors_uploads .= 'Error al guardar mas anexos. ';
                                        }
                                    }
                                    closedir($dir);
                                }
                            }
                        }
                    }
                }
                // FIN DE CARGAR NUEVOS ANEXOS

            }

            $result['status'] = 200;
            $result['errors_uploads'] = $errors_uploads;
            $result['message'] = 'Se ha subido los archivos';
            return $result;
        } catch (\Exception $e) {
            $result['status'] = 404;
            $result['result'] = $e->getMessage();
            $result['message'] = 'A ocurrido un error al subir los anexos.';
            return $result;
        }
    }

    public function calcular_monto_segun_impuesto()
    {
        try {

            $request = file_get_contents('php://input');
            $request = json_decode($request, true);

            $tipo_moneda_id = $request["tipo_moneda_id"];
            $monto_renta = str_replace(",", "", $request["monto_renta"]);
            $impuesto_a_la_renta_id = $request["impuesto_a_la_renta_id"];
            $carta_de_instruccion_id = $request["carta_de_instruccion_id"];

            $moneda_contrato = "";
            $simbolo_moneda = "";
            $model = new DataContrato();
            $moneda = $model->obtener_mondeda_por_id($tipo_moneda_id);
            if ($moneda['status'] == 200) {
                $moneda_contrato = $moneda['result']['nombre'];
                $simbolo_moneda = $moneda['result']['simbolo'];
            }

            $factor = 1.05265;
            $renta_bruta = 0;
            $renta_neta = 0;
            $impuesto_a_la_renta = 0;
            $ir_detalle = array();

            if ($impuesto_a_la_renta_id == 1) {
                $impuesto_a_la_renta = round($monto_renta * 0.05);
                $renta_bruta = $monto_renta;

                if ($carta_de_instruccion_id == 1) {
                    $renta_neta = $monto_renta - $impuesto_a_la_renta;
                    $quien_paga = 'AT';
                } elseif ($carta_de_instruccion_id == 2) {
                    $renta_neta = $monto_renta;
                    $quien_paga = 'Arrendador';
                }

                $detalle = 'AT deposita la renta (' . $simbolo_moneda . ' ' . number_format($renta_neta, 2, '.', ',') . ' ' . $moneda_contrato . ') al Arrendador. El ' . $quien_paga . ' realiza el pago del impuesto a la renta (' . $simbolo_moneda . ' ' . number_format($impuesto_a_la_renta, 0, '.', ',') . ' ' . $moneda_contrato . ') a SUNAT.';
            } elseif ($impuesto_a_la_renta_id == 2) {
                $impuesto_a_la_renta = round(($monto_renta * $factor) - $monto_renta);
                $renta_bruta = $monto_renta + round($impuesto_a_la_renta);
                $renta_neta = $monto_renta;

                if ($carta_de_instruccion_id == 1) {
                    $renta_neta = $monto_renta;
                    $quien_paga = 'AT';
                    $detalle = 'AT deposita renta (' . $simbolo_moneda . ' ' . number_format($monto_renta, 2, '.', ',') . ' ' . $moneda_contrato . ') al Arrendador. AT realiza el pago del impuesto a la renta (' . $simbolo_moneda . ' ' . number_format($impuesto_a_la_renta, 0, '.', ',') . ' ' . $moneda_contrato . ') a SUNAT.';
                } elseif ($carta_de_instruccion_id == 2) {
                    $renta_neta = $monto_renta + $impuesto_a_la_renta;
                    $quien_paga = 'Arrendador';
                    $detalle = 'AT deposita ' . $simbolo_moneda . ' ' . number_format($renta_neta, 2, '.', ',') . ' ' . $moneda_contrato . ' al Arrendador. El Arrendador realiza el pago del impuesto a la renta (' . $simbolo_moneda . ' ' . number_format($impuesto_a_la_renta, 0, '.', ',') . ' ' . $moneda_contrato . ') a SUNAT.';
                }
            }

            $ir_detalle["impuesto_a_la_renta"] = $simbolo_moneda . ' ' . number_format($impuesto_a_la_renta, 0, '.', ',') . ' ' . $moneda_contrato;
            $ir_detalle["renta_bruta"] = $simbolo_moneda . ' ' . number_format($renta_bruta, 2, '.', ',') . ' ' . $moneda_contrato;
            $ir_detalle["renta_neta"] = $simbolo_moneda . ' ' . number_format($renta_neta, 2, '.', ',') . ' ' . $moneda_contrato;
            $ir_detalle["detalle"] = $detalle;


            $ir_detalle = mb_convert_encoding($ir_detalle, 'UTF-8');
            $result['status'] = 200;
            $result['result'] = $ir_detalle;
            $result['message'] = 'Datos obtenidos de gestión';

            return json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';

            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }

    public function actualizar_codigo_contrato()
    {

        $db = Database::getInstance();
        $connection = $db->getConnection();
        $connection->beginTransaction();
        try {
            set_time_limit(0);
            $model_contrato = new ContratoArrendamiento();
            $update_contrato = $model_contrato->actualizar_codigo_contrato();
            if ($update_contrato['status'] == 404) {
                $connection->rollback();
                return json_encode($update_contrato, JSON_UNESCAPED_UNICODE);
            }

            $connection->commit();
            return json_encode($update_contrato, JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            $connection->rollback();
            $result['status'] = 404;
            $result['result'] = $e->getMessage();
            $result['message'] = 'A ocurrido un error.';
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }

    public function obtener_propietarios()
    {

        try {
            $request = file_get_contents('php://input');
            $request = json_decode($request, true);
            $modal_propietario = new Propietario();
            $propietarios = $modal_propietario->obtener_propietarios($request['contrato_id']);
            return json_encode($propietarios, JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            $result['status'] = 404;
            $result['result'] = $e->getMessage();
            $result['message'] = 'A ocurrido un error.';
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }

    public function agregar_contrato_arrendamiento()
    {

        $db = Database::getInstance();
        $connection = $db->getConnection();
        $connection->beginTransaction();
        try {

            $request = $_POST;
            $contratos = json_decode($request['contratos'], true);
            $ficha_contratos = $this->registrar_ficha_contrato($request['contrato_id'], $contratos);
            if ($ficha_contratos['status'] == 404) {
                $connection->rollback();
                return json_encode($ficha_contratos, JSON_UNESCAPED_UNICODE);
            }

            //Subida de todos los archivos adjuntos;
            $files_upload = $this->subir_archivos_contrato_arrendamiento($request['contrato_id'], $_FILES, $ficha_contratos['contratos']);
            if ($files_upload['status'] != 200) {
                $connection->rollback();
                return json_encode($files_upload, JSON_UNESCAPED_UNICODE);
            }

            //actualizar codigo de contrato detalle
            $modal_contrato_detalle = new ContratoArrendamientoDetalle();
            $update_contrato_Detalle = $modal_contrato_detalle->update_codigo($request['contrato_id']);
            if ($update_contrato_Detalle['status'] == 404) {
                $connection->rollback();
                return json_encode($update_contrato_Detalle, JSON_UNESCAPED_UNICODE);
            }

            $this->send_email_solicitud_contrato_detalle($request['contrato_id'], $ficha_contratos['nuevos_detalles_id'], false, false);

            $connection->commit();
            $result['status'] = 200;
            $result['result'] = $request['contrato_id'];
            $result['message'] = 'El contrato se ha registrado exitosamente';
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            $connection->rollback();
            $result['status'] = 404;
            $result['result'] = $e->getMessage();
            $result['message'] = 'A ocurrido un error.';
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }


    public function reenviar_email_solicitud_contrato_locales_detallado()
    {
        try {
            $request = file_get_contents('php://input');
            $request = json_decode($request, true);

            $message = $this->send_email_confirmacion_solicitud_contrato_arrendamiento($request['contrato_id'], true, true);
            $result['status'] = 200;
            $result['result'] = $request['contrato_id'];
            $result['message'] = 'El contrato se ha reenviado exitosamente';
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            $result['status'] = 404;
            $result['result'] = $e->getMessage();
            $result['message'] = 'A ocurrido un error.';
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }

    public function reenviar_email_solicitud_arrendamiento()
    {
        try {
            $request = file_get_contents('php://input');
            $request = json_decode($request, true);

            $this->send_email_solicitud_contrato_arrendamiento($request['contrato_id'], true);
            $result = $this->send_email_solicitud_contrato_arrendamiento_detallado($request['contrato_id'], true, true);
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            $result['status'] = 404;
            $result['result'] = $e->getMessage();
            $result['message'] = 'A ocurrido un error.';
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }

    public function enviar_email_solicitud_arrendamiento()
    {
        try {
            // $request = file_get_contents('php://input');
            // $request = json_decode($request,true);
            $request = $_POST;

            $this->send_email_solicitud_contrato_arrendamiento($request['contrato_id'], false);
            $result = $this->send_email_solicitud_contrato_arrendamiento_detallado($request['contrato_id'], true, false);
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            $result['status'] = 404;
            $result['result'] = $e->getMessage();
            $result['message'] = 'A ocurrido un error.';
            return json_encode($result, JSON_UNESCAPED_UNICODE);
        }
    }














    //envio de correos
    function send_email_confirmacion_solicitud_contrato_arrendamiento($contrato_id, $reenviar)
    {

        $host = $_SERVER["HTTP_HOST"];
        $model_contrato = new ContratoArrendamiento();
        $data_contrato = $model_contrato->obtener_solicitud_contrato_local($contrato_id);

        $body = "";
        $body .= '<html>';

        $email_user_created = '';
        $correos_ad = [];
        foreach ($data_contrato['result'] as $sel) {
            $sigla_correlativo = $sel['sigla_correlativo'];
            $codigo_correlativo = $sel['codigo_correlativo'];
            $correo_aprobador = $sel['correo_aprobador'];
            $correo_supervisor = $sel['correo_supervisor'];
            $correo_responsable = $sel['correo_responsable'];

            if (!empty($correo_aprobador)) {
                array_push($correos_ad, $correo_aprobador);
            }
            if (!empty($correo_responsable)) {
                array_push($correos_ad, $correo_responsable);
            }


            $body .= '<div>';
            $body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

            $body .= '<thead>';
            $body .= '<tr>';
            $body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
            $body .= '<b>Nueva solicitud</b>';
            $body .= '</th>';
            $body .= '</tr>';
            $body .= '</thead>';

            $body .= '<tr>';
            $body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Ubicación:</b></td>';
            $body .= '<td>' . $sel["ubicacion"] . '</td>';
            $body .= '</tr>';

            $body .= '<tr>';
            $body .= '<td style="background-color: #ffffdd"><b>Fecha solicitud:</b></td>';
            $body .= '<td>' . $sel["created_at"] . '</td>';
            $body .= '</tr>';

            $body .= '<tr>';
            $body .= '<td style="background-color: #ffffdd"><b>Usuario solicitante:</b></td>';
            $body .= '<td>' . $sel["usuario_solicitante"] . '</td>';
            $body .= '</tr>';

            $body .= '</table>';
            $body .= '</div>';

            //$email_user_created = $sel["correo"];
        }

        $body .= '<div>';
        $body .= '<br>';
        $body .= '</div>';

        $body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
        $body .= '<a href="' . $host . '/?sec_id=contrato&sub_sec_id=detalle_solicitud&id=' . $contrato_id . '" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
        $body .= '<b>Ver Solicitud</b>';
        $body .= '</a>';
        $body .= '</div>';

        $body .= '</html>';
        $body .= "";

        $correos = new Correos(env('SEND_EMAIL'), env('EMAIL_TEST'));
        $lista_correos = $correos->send_email_confirmacion_solicitud_contrato_arrendamiento($correos_ad);


        if (env('SEND_EMAIL') == 'TEST') { // Imprimir lista de correos que se enviarian en producción pero solo se visualizara en Desarrollo 
            $correos_produccion = implode(", ", $lista_correos['cc_dev']);

            $body .= '<div>';
            $body .= '<br>';
            $body .= '</div>';

            $body .= '<div>';
            $body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
            $body .= '<thead>';
            $body .= '<tr>';
            $body .= '<th style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
            $body .= '<b>Lista de Correos</b>';
            $body .= '</th>';
            $body .= '</tr>';
            $body .= '</thead>';
            $body .= '<tbody>';
            $body .= '<tr>';
            $body .= '<td>' . $correos_produccion . '</td>';
            $body .= '</tr>';
            $body .= '</tfoot>';
            $body .= '<tr>';
            $body .= '<td style="text-align: center;"><small>*** Esta sección solo se envia en desarroollo ***</small></td>';
            $body .= '</tr>';
            $body .= '</tfoot>';
            $body .= '</table>';
            $body .= '</div>';
        }

        $cc = $lista_correos['cc'];
        $bcc = $lista_correos['bcc'];

        if ($reenviar) {
            $titulo = "Gestion - Sistema Contratos - Reenviar Confirmación de Solicitud de Contrato Arrendamiento: Código - " . $sigla_correlativo . $codigo_correlativo;
        } else {
            $titulo = "Gestion - Sistema Contratos - Confirmación de Solicitud de Contrato Arrendamiento: Código - " . $sigla_correlativo . $codigo_correlativo;
        }

        $request = [
            "subject" => $titulo,
            "body"    => $body,
            "cc"      => $cc,
            "bcc"     => $bcc,
            "attach"  => [
                // $filepath . $file,
            ],
        ];

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;


            $mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
            $mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
            $mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
            $mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

            if (isset($request["cc"])) {
                foreach ($request["cc"] as $cc) {
                    $mail->addAddress($cc);
                }
            }

            if (isset($request["bcc"])) {
                foreach ($request["bcc"] as $bcc) {
                    $mail->addBCC($bcc);
                }
            }

            $mail->isHTML(true);
            $mail->Subject  = $request["subject"];
            $mail->Body     = $request["body"];
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            $mail->send();
        } catch (Exception $e) {
            $result["http_code"] = 400;
            $result["status"] = "Datos obtenidos de gestion.";
            $result["error_title"] = "La solicitud se registro correctamente, pero no se pudo enviar el email.";
            $result["error"] = $mail->ErrorInfo;
            echo json_encode($result);
            exit();
        }
    }

    function send_email_solicitud_contrato_arrendamiento($contrato_id, $reenvio = false)
    {

        $host = $_SERVER["HTTP_HOST"];
        $model_contrato = new ContratoArrendamiento();
        $data_contrato = $model_contrato->obtener_solicitud_contrato_local($contrato_id);

        $body = "";
        $body .= '<html>';

        $email_user_created = '';
        $correos_add = [];
        $estado_aprobacion = true;
        foreach ($data_contrato['result'] as $sel) {
            $sigla_correlativo = $sel['sigla_correlativo'];
            $codigo_correlativo = $sel['codigo_correlativo'];
            $correo_aprobador = $sel['correo_aprobado_por'];
            $correo_supervisor = $sel['correo_supervisor'];
            $correo_responsable = $sel['correo_responsable'];
            $correo_jefe_comercial = $sel['correo_jefe_comercial'];

            if (!empty($correo_aprobador)) {
                array_push($correos_add, $correo_aprobador);
            }
            if (!empty($correo_supervisor)) {
                array_push($correos_add, $correo_supervisor);
            }
            if (!empty($correo_responsable)) {
                array_push($correos_add, $correo_responsable);
            }
            if (!empty($correo_jefe_comercial)) {
                array_push($correos_add, $correo_jefe_comercial);
            }

            if (!is_null($sel['fecha_aprobacion']) && $sel['estado_aprobacion'] == 0) {
                $estado_aprobacion = false; // en caso el contrato este rechazado
            }

            $body .= '<div>';
            $body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

            $body .= '<thead>';
            $body .= '<tr>';
            $body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
            $body .= '<b>Nueva solicitud</b>';
            $body .= '</th>';
            $body .= '</tr>';
            $body .= '</thead>';

            $body .= '<tr>';
            $body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Ubicación:</b></td>';
            $body .= '<td>' . $sel["ubicacion"] . '</td>';
            $body .= '</tr>';

            $body .= '<tr>';
            $body .= '<td style="background-color: #ffffdd"><b>Fecha solicitud:</b></td>';
            $body .= '<td>' . $sel["created_at"] . '</td>';
            $body .= '</tr>';

            $body .= '<tr>';
            $body .= '<td style="background-color: #ffffdd"><b>Usuario solicitante:</b></td>';
            $body .= '<td>' . $sel["usuario_solicitante"] . '</td>';
            $body .= '</tr>';

            $body .= '</table>';
            $body .= '</div>';

            //    $email_user_created = $sel["correo"];
        }

        $body .= '<div>';
        $body .= '<br>';
        $body .= '</div>';

        $body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
        $body .= '<a href="' . $host . '/?sec_id=contrato&sub_sec_id=detalle_solicitud&id=' . $contrato_id . '" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
        $body .= '<b>Ver Solicitud</b>';
        $body .= '</a>';
        $body .= '</div>';



        $correos = new Correos(env('SEND_EMAIL'), env('EMAIL_TEST'));
        if ($estado_aprobacion) { // aprobado
            $lista_correos = $correos->send_email_solicitud_contrato_arrendamiento($correos_add);
            $cc = $lista_correos['cc'];
            $bcc = $lista_correos['bcc'];
            $titulo = "Gestion - Sistema Contratos - Nueva Solicitud de Arrendamiento: Código - " . $sigla_correlativo . $codigo_correlativo;
            if ($reenvio) {
                $titulo = "Gestion - Sistema Contratos - Reenvio - Nueva Solicitud de Arrendamiento: Código - " . $sigla_correlativo . $codigo_correlativo;
            }
        } else {  // rechazado
            $lista_correos = $correos->send_email_solicitud_rechazada([$correo_responsable]);
            $cc = $lista_correos['cc'];
            $bcc = $lista_correos['bcc'];
            $titulo = "Gestion - Sistema Contratos - Solicitud de Arrendamiento Rechazada: Código - " . $sigla_correlativo . $codigo_correlativo;
            if ($reenvio) {
                $titulo = "Gestion - Sistema Contratos - Reenvio - Solicitud de Arrendamiento Rechazada: Código - " . $sigla_correlativo . $codigo_correlativo;
            }
        }

        if (env('SEND_EMAIL') == 'TEST') { // Imprimir lista de correos que se enviarian en producción pero solo se visualizara en Desarrollo 
            $correos_produccion = implode(", ", $lista_correos['cc_dev']);

            $body .= '<div>';
            $body .= '<br>';
            $body .= '</div>';

            $body .= '<div>';
            $body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
            $body .= '<thead>';
            $body .= '<tr>';
            $body .= '<th style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
            $body .= '<b>Lista de Correos</b>';
            $body .= '</th>';
            $body .= '</tr>';
            $body .= '</thead>';
            $body .= '<tbody>';
            $body .= '<tr>';
            $body .= '<td>' . $correos_produccion . '</td>';
            $body .= '</tr>';
            $body .= '</tfoot>';
            $body .= '<tr>';
            $body .= '<td style="text-align: center;"><small>*** Esta sección solo se envia en desarroollo ***</small></td>';
            $body .= '</tr>';
            $body .= '</tfoot>';
            $body .= '</table>';
            $body .= '</div>';
        }

        $cc = $lista_correos['cc'];
        $bcc = $lista_correos['bcc'];

        $request = [
            "subject" => $titulo,
            "body"    => $body,
            "cc"      => $cc,
            "bcc"     => $bcc,
            "attach"  => [
                // $filepath . $file,
            ],
        ];

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;


            $mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
            $mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
            $mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
            $mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

            if (isset($request["cc"])) {
                foreach ($request["cc"] as $cc) {
                    $mail->addAddress($cc);
                }
            }

            if (isset($request["bcc"])) {
                foreach ($request["bcc"] as $bcc) {
                    $mail->addBCC($bcc);
                }
            }

            $mail->isHTML(true);
            $mail->Subject  = $request["subject"];
            $mail->Body     = $request["body"];
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            $mail->send();
        } catch (Exception $e) {
            $result["http_code"] = 400;
            $result["status"] = "Datos obtenidos de gestion.";
            $result["error_title"] = "La solicitud se registro correctamente, pero no se pudo enviar el email.";
            $result["error"] = $mail->ErrorInfo;
            echo json_encode($result);
            exit();
        }
    }
    function send_email_solicitud_contrato_arrendamiento_detallado($contrato_id, $enviar_respuesta, $reenvio)
    {
        $host = $_SERVER["HTTP_HOST"];
        $model_contrato = new ContratoArrendamiento();
        $data_contrato = $model_contrato->obtener_solicitud_contrato_local_detallado($contrato_id);

        $contratos = $data_contrato['result']['contratos'];
        $body = '';
        $correos_add = [];
        $estado_aprobacion = true;

        foreach ($data_contrato['result']['datos_generales'] as $sel) {


            $sigla_correlativo = $sel['sigla_correlativo'];
            $codigo_correlativo = $sel['codigo_correlativo'];

            $correo_aprobador = $sel['correo_aprobado_por'];
            $correo_supervisor = $sel['correo_supervisor'];
            $correo_responsable = $sel['correo_responsable'];
            $correo_jefe_comercial = $sel['correo_jefe_comercial'];

            if (!empty($correo_aprobador)) {
                array_push($correos_add, $correo_aprobador);
            }
            if (!empty($correo_supervisor)) {
                array_push($correos_add, $correo_supervisor);
            }
            if (!empty($correo_responsable)) {
                array_push($correos_add, $correo_responsable);
            }
            if (!empty($correo_jefe_comercial)) {
                array_push($correos_add, $correo_jefe_comercial);
            }

            if (!is_null($sel['fecha_aprobacion']) && $sel['estado_aprobacion'] == 0) {
                $estado_aprobacion = false; // en caso el contrato este rechazado
            }


            $body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
            $body .= '<tr>';
            $body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Datos Generales</th>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Empresa Arrendataria</b></td>';
            $body .= '<td>' . $sel["empresa_suscribe"] . '</td>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd;"><b>Supervisor</b></td>';
            $body .= '<td>' . $sel["persona_responsable"] . '</td>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd;"><b>Registrado por</b></td>';
            $body .= '<td>' . $sel["user_created"] . '</td>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd;"><b>Fecha de Registro</b></td>';
            $body .= '<td>' . $sel["created_at"] . '</td>';
            $body .= '</tr>';
            $body .= '</table>';
        }

        $body .= '<br>';

        foreach ($data_contrato['result']['data_propietarios'] as $sel) {

            $body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
            $body .= '<tr>';
            $body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Datos del Propietario</th>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Tipo de Persona</b></td>';
            $body .= '<td>' . $sel["tipo_persona"] . '</td>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd;"><b>Nombre</b></td>';
            $body .= '<td>' . $sel["nombre"] . '</td>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd;"><b>Tipo de Documento de Identidad</b></td>';
            $body .= '<td>' . $sel["tipo_docu_identidad"] . '</td>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd;"><b>Número de Documento de Identidad</b></td>';
            $body .= '<td>' . $sel["num_docu"] . '</td>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd;"><b>Domicilio del propietario</b></td>';
            $body .= '<td>' . $sel["direccion"] . '</td>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd;"><b>Representante Legal</b></td>';
            $body .= '<td>' . $sel["representante_legal"] . '</td>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd;"><b>N° de Partida Registral de la empresa</b></td>';
            $body .= '<td>' . $sel["num_partida_registral"] . '</td>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd;"><b>Persona de contacto</b></td>';
            $body .= '<td>' . $sel["contacto_nombre"] . '</td>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd;"><b>Teléfono de la persona de contacto</b></td>';
            $body .= '<td>' . $sel["contacto_telefono"] . '</td>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd;"><b>E-mail de la persona de contacto</b></td>';
            $body .= '<td>' . $sel["contacto_email"] . '</td>';
            $body .= '</tr>';
            $body .= '</table>';
        }

        $body .= '<br>';



        foreach ($contratos as $contrato) {
            $observaciones = $contrato["observaciones"];
            $body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
            $body .= '<tr>';
            $body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Contrato #' . $contrato['codigo'] . '</th>';
            $body .= '</tr>';
            $body .= '</table>';
            $body .= '<br>';

            foreach ($contrato['data_inmuebles'] as $sel) {

                $body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
                $body .= '<tr>';
                $body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Datos del Inmueble</th>';
                $body .= '</tr>';
                $body .= '<tr>';
                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Departamento</b></td>';
                $body .= '<td>' . $sel["departamento"] . '</td>';
                $body .= '</tr>';
                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>Provincia</b></td>';
                $body .= '<td>' . $sel["provincia"] . '</td>';
                $body .= '</tr>';
                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>Distrito</b></td>';
                $body .= '<td>' . $sel["distrito"] . '</td>';
                $body .= '</tr>';
                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>Ubicación</b></td>';
                $body .= '<td>' . $sel["ubicacion"] . '</td>';
                $body .= '</tr>';
                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>Área arrendada(m2)</b></td>';
                $body .= '<td>' . $sel["area_arrendada"] . ' m2' . '</td>';
                $body .= '</tr>';
                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>N° Partida Registral</b></td>';
                $body .= '<td>' . $sel["num_partida_registral"] . '</td>';
                $body .= '</tr>';
                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>Oficina Registral (Sede)</b></td>';
                $body .= '<td>' . $sel["oficina_registral"] . '</td>';
                $body .= '</tr>';

                $index_agua = 1;
                $index_luz = 1;
                $index_servicio = '';
                foreach ($contrato['data_inmuebles_suministros'] as $suministro) {

                    if ($suministro['tipo_servicio_id'] == 1) {
                        $index_servicio = $index_luz;
                        $index_luz++;
                    } else if ($suministro['tipo_servicio_id'] == 2) {
                        $index_servicio = $index_agua;
                        $index_agua++;
                    }

                    $body .= '<tr>';
                    $body .= '<td style="background-color:#ffffdd;"><b>Servicio de ' . $suministro['tipo_servicio'] . ' #' . $index_servicio . ' - N° de Suministro</b></td>';
                    $body .= '<td>' . $suministro["nro_suministro"] . '</td>';
                    $body .= '</tr>';
                    $body .= '<tr>';
                    $body .= '<td style="background-color:#ffffdd;"><b>Servicio de ' . $suministro['tipo_servicio'] . ' #' . $index_servicio . ' - Compromiso de pago</b></td>';
                    $body .= '<td>' . $suministro["tipo_compromiso"] . '</td>';
                    $body .= '</tr>';

                    if ($suministro["tipo_compromiso_pago_id"] == 1 || $suministro["tipo_compromiso_pago_id"] == 2 || $suministro["tipo_compromiso_pago_id"] == 6 || $suministro["tipo_compromiso_pago_id"] == 7) {
                        $monto_o_porcentaje = $suministro["monto_o_porcentaje"];

                        if ($suministro["tipo_compromiso_pago_id"] == 1) {
                            $compromiso_pago = 'Porcentaje del recibo';
                            $valor_compromiso_pago = $monto_o_porcentaje . '%';
                        } else {
                            $valor_compromiso_pago = 'S/. ' . $monto_o_porcentaje;

                            if ($suministro["tipo_compromiso_pago_id"] == 2) {
                                $compromiso_pago = 'Monto Fijo';
                            } else if ($suministro["tipo_compromiso_pago_id"] == 6) {
                                $compromiso_pago = 'Monto Base';
                            } else if ($suministro["tipo_compromiso_pago_id"] == 7) {
                                $compromiso_pago = 'Monto a facturar';
                            }
                        }

                        $compromiso_pago = 'Servicio de ' . $suministro['tipo_servicio'] . ' #' . $index_servicio . ' - ' . $compromiso_pago;

                        if (empty($monto_o_porcentaje)) {
                            $valor_compromiso_pago = 'Sin asignar';
                        }

                        $body .= '<tr>';
                        $body .= '<td style="background-color:#ffffdd;"><b>' . $compromiso_pago . '</b></td>';
                        $body .= '<td>' . $valor_compromiso_pago . '</td>';
                        $body .= '</tr>';
                    }

                    if ($suministro["tipo_compromiso_pago_id"] == 5) {
                        $body .= '<tr>';
                        $body .= '<td style="background-color:#ffffdd;"><b>Servicio de ' . $suministro['tipo_servicio'] . ' #' . $index_servicio . ' - Tipo de Documento</b></td>';
                        $body .= '<td>' . $suministro["tipo_documento_beneficiario"] . '</td>';
                        $body .= '</tr>';

                        $body .= '<tr>';
                        $body .= '<td style="background-color:#ffffdd;"><b>Servicio de ' . $suministro['tipo_servicio'] . ' #' . $index_servicio . ' - Nro Documento</b></td>';
                        $body .= '<td>' . $suministro["nro_documento_beneficiario"] . '</td>';
                        $body .= '</tr>';

                        $body .= '<tr>';
                        $body .= '<td style="background-color:#ffffdd;"><b>Servicio de ' . $suministro['tipo_servicio'] . ' #' . $index_servicio . ' - Nombres</b></td>';
                        $body .= '<td>' . $suministro["nombre_beneficiario"] . '</td>';
                        $body .= '</tr>';

                        $body .= '<tr>';
                        $body .= '<td style="background-color:#ffffdd;"><b>Servicio de ' . $suministro['tipo_servicio'] . ' #' . $index_servicio . ' - Nro de Cuenta Soles</b></td>';
                        $body .= '<td>' . $suministro["nro_cuenta_soles"] . '</td>';
                        $body .= '</tr>';
                    }
                }

                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>Arbitrios - Compromiso de pago</b></td>';
                $body .= '<td>' . $sel["tipo_pago_arbitrios"] . '</td>';
                $body .= '</tr>';

                if ($sel["tipo_compromiso_pago_arbitrios"] != 2) {
                    $porcentaje_pago_arbitrios = $sel["porcentaje_pago_arbitrios"] . '%';

                    $body .= '<tr>';
                    $body .= '<td style="background-color:#ffffdd;"><b>Porcentaje del Pago de Arbitrios (%)</b></td>';
                    $body .= '<td>' . $porcentaje_pago_arbitrios . '</td>';
                    $body .= '</tr>';
                }

                $body .= '</table>';
            }

            $body .= '<br>';


            foreach ($contrato['data_condiciones_economicas'] as $row) {
                $simbolo_moneda = $row["simbolo_moneda"];
                $moneda_contrato = $row["moneda_contrato"];
                $monto_renta = $simbolo_moneda . ' ' . number_format($row["monto_renta"], 2, '.', ',');
                $impuesto_a_la_renta_id = $row["impuesto_a_la_renta_id"];
                $impuesto_a_la_renta = $row["impuesto_a_la_renta"];
                $numero_cuenta_detraccion = $row["numero_cuenta_detraccion"];
                $garantia_monto = $simbolo_moneda . ' ' . number_format($row["garantia_monto"], 2, '.', ',');
                $tipo_adelanto_id = $row["tipo_adelanto_id"];
                $tipo_adelanto = $row["tipo_adelanto"];

                $plazo_id = $row["plazo_id"];
                $nombre_plazo = $row["nombre_plazo"];
                $cant_meses_contrato = $row["cant_meses_contrato"];
                $fecha_inicio = $row["fecha_inicio"];
                $fecha_fin = $row["fecha_fin"];
                $fecha_suscripcion = $row["fecha_suscripcion"];


                $periodo_gracia_id = $row["periodo_gracia_id"];
                $periodo_gracia = trim($row["periodo_gracia"]);
                $periodo_gracia_numero = trim($row["periodo_gracia_numero"]);


                if ($plazo_id == 1) {
                    if (empty($cant_meses_contrato)) {
                        $cant_meses_contrato = 'Sin asignar';
                    } else {
                        $model_data = new DataContrato();
                        $cant_meses_contrato = $model_data->sec_contrato_nuevo_de_meses_a_anios_y_meses($cant_meses_contrato) . ' (' . $cant_meses_contrato . ' meses)';
                    }

                    if (empty($fecha_inicio)) {
                        $contrato_inicio_fecha = 'Sin asignar';
                    } else {
                        $contrato_inicio_fecha = date("d/m/Y", strtotime($fecha_inicio));
                    }

                    if (empty($fecha_fin)) {
                        $contrato_fin_fecha = 'Sin asignar';
                    } else {
                        $contrato_fin_fecha = date("d/m/Y", strtotime($fecha_fin));
                    }

                    if (empty($fecha_suscripcion)) {
                        $contrato_fecha_suscripcion = 'Sin asignar';
                    } else {
                        $contrato_fecha_suscripcion = date("d/m/Y", strtotime($fecha_suscripcion));
                    }
                } else {

                    $cant_meses_contrato = 'Sin asignar';

                    if (empty($fecha_inicio)) {
                        $contrato_inicio_fecha = 'Sin asignar';
                    } else {
                        $contrato_inicio_fecha = date("d/m/Y", strtotime($fecha_inicio));
                    }

                    $contrato_fin_fecha = 'Sin asignar';

                    if (empty($fecha_suscripcion)) {
                        $contrato_fecha_suscripcion = 'Sin asignar';
                    } else {
                        $contrato_fecha_suscripcion = date("d/m/Y", strtotime($fecha_suscripcion));
                    }
                }


                if ($periodo_gracia_id == '1') {
                    $periodo_gracia_inicio = $row["periodo_gracia_inicio"];

                    if (empty($periodo_gracia_inicio)) {
                        $periodo_gracia_inicio_fecha = 'Sin asignar';
                    } else {
                        $periodo_gracia_inicio_fecha = date("d/m/Y", strtotime($periodo_gracia_inicio));
                    }

                    $periodo_gracia_fin = $row["periodo_gracia_fin"];

                    if (empty($periodo_gracia_fin)) {
                        $periodo_gracia_fin_fecha = 'Sin asignar';
                    } else {
                        $periodo_gracia_fin_fecha = date("d/m/Y", strtotime($periodo_gracia_fin));
                    }
                }

                if (empty($periodo_gracia)) {
                    $periodo_gracia = 'Sin asignar';
                }

                if (empty($periodo_gracia_numero)) {
                    $periodo_gracia_numero = 'Sin asignar';
                } else {
                    if ($periodo_gracia_numero == 1) {
                        $periodo_gracia_numero .= ' día';
                    } elseif ($periodo_gracia_numero > 1) {
                        $periodo_gracia_numero .= ' días';
                    }
                }
            }

            $body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
            $body .= '<tr>';
            $body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Condiciones Económicas y Comerciales</th>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Moneda del contrato</b></td>';
            $body .= '<td>' . $moneda_contrato . '</td>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd;"><b>Monto de Renta Pactada</b></td>';
            $body .= '<td>' . $monto_renta . '</td>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd;"><b>Impuesto a la renta</b></td>';
            $body .= '<td>' . $impuesto_a_la_renta . '</td>';
            $body .= '</tr>';

            if ((int) $impuesto_a_la_renta_id == 4) {

                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>N°. de cuenta de detracción (Banco de la Nación)</b></td>';
                $body .= '<td>' . $numero_cuenta_detraccion . '</td>';
                $body .= '</tr>';
            }

            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd;"><b>Monto de la garantías</b></td>';
            $body .= '<td>' . $garantia_monto . '</td>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd;"><b>Adelantos</b></td>';
            $body .= '<td>' . $tipo_adelanto . '</td>';
            $body .= '</tr>';

            if ($tipo_adelanto_id == '1') {

                $row_count = count($contrato['data_adelantos']);
                $meses_de_adelanto = '';
                if ($row_count > 0) {
                    $contador = 1;
                    foreach ($contrato['data_adelantos'] as $row) {
                        $num_periodo = $row["num_periodo"];

                        if ($num_periodo == 'x') {
                            $mes_adelanto = 'Antepenúltimo';
                        } else if ($num_periodo == 'y') {
                            $mes_adelanto = 'Penúltimo';
                        } else if ($num_periodo == 'z') {
                            $mes_adelanto = 'Último';
                        } else {
                            $mes_adelanto = $num_periodo;
                        }

                        $mes_adelanto = $mes_adelanto . ' mes';

                        if ($contador == 1) {
                            $meses_de_adelanto = $mes_adelanto;
                        } else {
                            $meses_de_adelanto .= ', ' . $mes_adelanto;
                        }

                        $contador++;
                    }
                }

                if ($row_count == 1) {
                    $cant_meses_adelando = 'mes';
                } else {
                    $cant_meses_adelando = 'meses';
                }

                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>Meses de adelanto</b></td>';
                $body .= '<td>' . $row_count . ' ' . $cant_meses_adelando . '(' . $meses_de_adelanto . ')' . '</td>';
                $body .= '</tr>';
            }

            $body .= '</table>';

            $body .= '<br>';

            $body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
            $body .= '<tr>';
            $body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Vigencia</th>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Vigencia</b></td>';
            $body .= '<td>' . $nombre_plazo . '</td>';
            $body .= '</tr>';

            if ($plazo_id == 1) {
                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Vigencia del Contrato</b></td>';
                $body .= '<td>' . $cant_meses_contrato . '</td>';
                $body .= '</tr>';
            }
            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd;"><b>Contrato - Fecha de Inicio</b></td>';
            $body .= '<td>' . $contrato_inicio_fecha . '</td>';
            $body .= '</tr>';

            if ($plazo_id == 1) {
                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>Contrato - Fecha de Fin</b></td>';
                $body .= '<td>' . $contrato_fin_fecha . '</td>';
                $body .= '</tr>';
            }

            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd;"><b>Periodo de gracia</b></td>';
            $body .= '<td>' . $periodo_gracia . '</td>';
            $body .= '</tr>';

            if ($periodo_gracia_id == '1') {

                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>Periodo de gracia - Número de días</b></td>';
                $body .= '<td>' . $periodo_gracia_numero . '</td>';
                $body .= '</tr>';
                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>Periodo de gracia - Fecha de Inicio</b></td>';
                $body .= '<td>' . $periodo_gracia_inicio_fecha . '</td>';
                $body .= '</tr>';
                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>Periodo de gracia - Fecha de Fin</b></td>';
                $body .= '<td>' . $periodo_gracia_fin_fecha . '</td>';
                $body .= '</tr>';
            }

            $body .= '</table>';

            $body .= '<br>';

            $body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
            $body .= '<tr>';
            $body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Incrementos</th>';
            $body .= '</tr>';

            $num_incremento_contador = 0;

            $row_count_incrementos = count($contrato['data_incrementos']);

            if ($row_count_incrementos > 0) {
                foreach ($contrato['data_incrementos'] as $sel) {
                    $num_incremento_contador++;

                    $a_partir_del_año = $sel["a_partir_del_año"] . ' año';
                    if ($sel["tipo_continuidad_id"] == 3) {
                        $a_partir_del_año = '';
                    }

                    $body .= '<tr>';
                    $body .= '<td style="background-color:#ffffdd; width: 300px;"><b>N° 0' . $num_incremento_contador . '</b></td>';
                    $body .= '<td>' . $sel["valor"] . ' ' . $sel["tipo_valor"] . ' ' . $sel["tipo_continuidad"] . ' ' . $a_partir_del_año . '</td>';
                    $body .= '</tr>';
                }
            } else {

                $body .= '<tr>';
                $body .= '<td colspan="2">El presente contrato no posee incrementos</td>';
                $body .= '</tr>';
            }
            $body .= '</table>';
            $body .= '<br>';





            $body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
            $body .= '<tr>';
            $body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Inflaciones</th>';
            $body .= '</tr>';


            $num_inflacion = 0;

            $row_count_inflacion = count($contrato['data_inflaciones']);

            if ($row_count_inflacion > 0) {
                foreach ($contrato['data_inflaciones'] as $sel) {
                    $num_inflacion++;

                    $fecha = $sel['fecha'];
                    $periocidad = $sel['tipo_periodicidad'] . ' ' . $sel['numero'] . ' ' . $sel['tipo_anio_mes'];
                    $moneda = $sel['moneda'];
                    $porcentaje_anadido = $sel['porcentaje_anadido'];
                    $tope_inflacion = $sel['tope_inflacion'];
                    $minimo_inflacion = $sel['minimo_inflacion'];

                    $body .= '<tr>';
                    $body .= '<td colspan="2" style="background-color:#ffffdd;"><b>N° ' . $num_inflacion . '</b></td>';
                    $body .= '</tr>';

                    if (!empty($fecha)) {
                        $body .= '<tr>';
                        $body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Fecha de Ajuste</b></td>';
                        $body .= '<td>' . $fecha . '</td>';
                        $body .= '</tr>';
                    }
                    $body .= '<tr>';
                    $body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Periodicidad</b></td>';
                    $body .= '<td>' . $periocidad . '</td>';
                    $body .= '</tr>';

                    $body .= '<tr>';
                    $body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Porcentaje Añadido</b></td>';
                    $body .= '<td>' . $porcentaje_anadido . '</td>';
                    $body .= '</tr>';

                    $body .= '<tr>';
                    $body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Tope de Inflación</b></td>';
                    $body .= '<td>' . $tope_inflacion . '</td>';
                    $body .= '</tr>';

                    $body .= '<tr>';
                    $body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Minimo de Inflación</b></td>';
                    $body .= '<td>' . $minimo_inflacion . '</td>';
                    $body .= '</tr>';
                }
            } else {

                $body .= '<tr>';
                $body .= '<td colspan="2">El presente contrato no posee inflaciones</td>';
                $body .= '</tr>';
            }

            $body .= '</table>';

            $body .= '<br>';



            $body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
            $body .= '<tr>';
            $body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Cuota Extraordinaria</th>';
            $body .= '</tr>';


            $num_cuota_extraordinaria = 0;
            $row_count_cuota = count($contrato['data_cuota_extraordinarias']);

            if ($row_count_cuota > 0) {
                foreach ($contrato['data_cuota_extraordinarias'] as $sel) {
                    $num_cuota_extraordinaria++;

                    $mes = $sel['mes'];
                    $multiplicador = $sel['multiplicador'];
                    $fecha = $sel['fecha'];

                    $body .= '<tr>';
                    $body .= '<td colspan="2" style="background-color:#ffffdd;"><b>N° ' . $num_cuota_extraordinaria . '</b></td>';
                    $body .= '</tr>';

                    if (!empty($fecha)) {
                        $body .= '<tr>';
                        $body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Fecha de Ajuste</b></td>';
                        $body .= '<td>' . $fecha . '</td>';
                        $body .= '</tr>';
                    }
                    $body .= '<tr>';
                    $body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Mes</b></td>';
                    $body .= '<td>' . $mes . '</td>';
                    $body .= '</tr>';

                    $body .= '<tr>';
                    $body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Multiplicador</b></td>';
                    $body .= '<td>' . $multiplicador . '</td>';
                    $body .= '</tr>';
                }
            } else {

                $body .= '<tr>';
                $body .= '<td colspan="2">El presente contrato no posee cuotas extraordinarias</td>';
                $body .= '</tr>';
            }

            $body .= '</table>';

            $body .= '<br>';



            foreach ($contrato['data_beneficiarios'] as $sel) {
                if ($sel["tipo_monto_id"] == 3) {
                    $monto_beneficiario = $monto_renta;
                } else {
                    if ($sel["tipo_monto_id"] == 2) {
                        $monto_beneficiario = $sel["monto"] . '%';
                    } else {
                        $monto_beneficiario = $simbolo_moneda . ' ' . number_format($sel["monto"], 2, '.', ',');
                    }
                }

                $body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
                $body .= '<tr>';
                $body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Datos del Beneficiario</th>';
                $body .= '</tr>';
                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Tipo de Persona</b></td>';
                $body .= '<td>' . $sel["tipo_persona"] . '</td>';
                $body .= '</tr>';
                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>Nombre</b></td>';
                $body .= '<td>' . $sel["nombre"] . '</td>';
                $body .= '</tr>';
                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>Tipo de Documento de Identidad</b></td>';
                $body .= '<td>' . $sel["tipo_docu_identidad"] . '</td>';
                $body .= '</tr>';
                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>Número de Documento de Identidad</b></td>';
                $body .= '<td>' . $sel["num_docu"] . '</td>';
                $body .= '</tr>';
                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>Tipo de forma de pago</b></td>';
                $body .= '<td>' . $sel["forma_pago"] . '</td>';
                $body .= '</tr>';

                if ($sel["forma_pago_id"] != '3') {

                    $body .= '<tr>';
                    $body .= '<td style="background-color:#ffffdd;"><b>Nombre del Banco</b></td>';
                    $body .= '<td>' . $sel["banco"] . '</td>';
                    $body .= '</tr>';
                    $body .= '<tr>';
                    $body .= '<td style="background-color:#ffffdd;"><b>N° de la cuenta bancaria</b></td>';
                    $body .= '<td>' . $sel["num_cuenta_bancaria"] . '</td>';
                    $body .= '</tr>';
                    $body .= '<tr>';
                    $body .= '<td style="background-color:#ffffdd;"><b>N° de CCI bancario</b></td>';
                    $body .= '<td>' . $sel["num_cuenta_cci"] . '</td>';
                    $body .= '</tr>';
                }

                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>Monto a depositar</b></td>';
                $body .= '<td>' . $sel["tipo_monto_a_depositar"] . '</td>';
                $body .= '</tr>';

                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>Monto</b></td>';
                $body .= '<td>' . $monto_beneficiario . '</td>';
                $body .= '</tr>';
                $body .= '</table>';
            }

            $body .= '<br>';

            $body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
            $body .= '<tr>';
            $body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Fecha de suscripción del contrato</th>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Fecha de suscripción del contrato</b></td>';
            $body .= '<td>' . $contrato_fecha_suscripcion . '</td>';
            $body .= '</tr>';
            $body .= '</table>';

            $body .= '<br>';
        }




        $body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
        $body .= '<tr>';
        $body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Observaciones</th>';
        $body .= '</tr>';
        $body .= '<tr>';
        $body .= '<tr>';
        $body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Observaciones</b></td>';
        $body .= '<td>' . $observaciones . '</td>';
        $body .= '</tr>';
        $body .= '</table>';

        $body .= '<div>';
        $body .= '<br>';
        $body .= '</div>';

        $body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
        $body .= '<a href="' . $host . '/?sec_id=contrato&sub_sec_id=detalle_solicitud&id=' . $contrato_id . '" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
        $body .= '<b>Ver Solicitud</b>';
        $body .= '</a>';
        $body .= '</div>';

        $pre_asunto = '';

        if ($reenvio) {
            $usuario_id = $model_contrato->get_usuario_id();
            $created_at = date('Y-m-d H:i:s');

            $data_email['contrato_id'] = $contrato_id;
            $data_email['usuario_id'] = $usuario_id;
            $data_email['created_at'] = $created_at;
            $model_contrato->registrar_email_enviados($data_email);
        }


        $correos = new Correos(env('SEND_EMAIL'), env('EMAIL_TEST'));
        if ($estado_aprobacion) { // aprobado
            $lista_correos = $correos->send_email_solicitud_contrato_arrendamiento_detallado([]);
            $cc = $lista_correos['cc'];
            $bcc = $lista_correos['bcc'];
            $titulo = "Gestion - Sistema Contratos - Nueva Solicitud de Arrendamiento: Código - " . $sigla_correlativo . $codigo_correlativo;
            if ($reenvio) {
                $titulo = "Gestion - Sistema Contratos - Reenvio - Nueva Solicitud de Arrendamiento: Código - " . $sigla_correlativo . $codigo_correlativo;
            }
        } else {  // rechazado
            $lista_correos = $correos->send_email_solicitud_rechazada([$correo_responsable]);
            $cc = $lista_correos['cc'];
            $bcc = $lista_correos['bcc'];
            $titulo = "Gestion - Sistema Contratos - Solicitud de Arrendamiento Rechazada: Código - " . $sigla_correlativo . $codigo_correlativo;
            if ($reenvio) {
                $titulo = "Gestion - Sistema Contratos - Reenvio - Solicitud de Arrendamiento Rechazada: Código - " . $sigla_correlativo . $codigo_correlativo;
            }
        }

        if (env('SEND_EMAIL') == 'TEST') { // Imprimir lista de correos que se enviarian en producción pero solo se visualizara en Desarrollo 
            $correos_produccion = implode(", ", $lista_correos['cc_dev']);

            $body .= '<div>';
            $body .= '<br>';
            $body .= '</div>';

            $body .= '<div>';
            $body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
            $body .= '<thead>';
            $body .= '<tr>';
            $body .= '<th style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
            $body .= '<b>Lista de Correos</b>';
            $body .= '</th>';
            $body .= '</tr>';
            $body .= '</thead>';
            $body .= '<tbody>';
            $body .= '<tr>';
            $body .= '<td>' . $correos_produccion . '</td>';
            $body .= '</tr>';
            $body .= '</tfoot>';
            $body .= '<tr>';
            $body .= '<td style="text-align: center;"><small>*** Esta sección solo se envia en desarroollo ***</small></td>';
            $body .= '</tr>';
            $body .= '</tfoot>';
            $body .= '</table>';
            $body .= '</div>';
        }

        $cc = $lista_correos['cc'];
        $bcc = $lista_correos['bcc'];


        $request = [
            "subject" => $titulo,
            "body"    => $body,
            "cc"      => $cc,
            "bcc"     => $bcc,
            "attach"  => [
                // $filepath . $file,
            ],
        ];

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;


            $mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
            $mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
            $mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
            $mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

            if (isset($request["cc"])) {
                foreach ($request["cc"] as $cc) {
                    $mail->addAddress($cc);
                }
            }

            if (isset($request["bcc"])) {
                foreach ($request["bcc"] as $bcc) {
                    $mail->addBCC($bcc);
                }
            }

            $mail->isHTML(true);
            $mail->Subject  = $request["subject"];
            $mail->Body     = $request["body"];
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            $mail->send();

            if ($enviar_respuesta) {
                $result["status"] = 200;
                $result["messate"] = "Se ha enviado correctamente el correo";
                return $result;
            }
        } catch (Exception $e) {
            if ($enviar_respuesta) {
                $result["status"] = 400;
                $result["message"] = "Error al enviar el email. " . $mail->ErrorInfo;
                return $result;
            }
        }
    }

    function send_email_solicitud_contrato_detalle($contrato_id, $contrato_detalle_id, $enviar_respuesta, $reenvio)
    {

        $model_contrato = new ContratoArrendamiento();
        $data_contrato = $model_contrato->obtener_solicitud_contrato_detallado($contrato_id, $contrato_detalle_id);

        $contratos = $data_contrato['result']['contratos'];
        $body = '';

        foreach ($data_contrato['result']['datos_generales'] as $sel) {

            $sigla_correlativo = $sel['sigla_correlativo'];
            $codigo_correlativo = $sel['codigo_correlativo'];

            $body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
            $body .= '<tr>';
            $body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Datos Generales</th>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Empresa Arrendataria</b></td>';
            $body .= '<td>' . $sel["empresa_suscribe"] . '</td>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd;"><b>Supervisor</b></td>';
            $body .= '<td>' . $sel["persona_responsable"] . '</td>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd;"><b>Registrado por</b></td>';
            $body .= '<td>' . $sel["user_created"] . '</td>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd;"><b>Fecha de Registro</b></td>';
            $body .= '<td>' . $sel["created_at"] . '</td>';
            $body .= '</tr>';
            $body .= '</table>';
        }

        $body .= '<br>';

        foreach ($contratos as $contrato) {
            $observaciones = $contrato["observaciones"];

            $body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
            $body .= '<tr>';
            $body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Contrato #' . $contrato['codigo'] . '</th>';
            $body .= '</tr>';
            $body .= '</table>';
            $body .= '<br>';

            foreach ($contrato['data_inmuebles'] as $sel) {

                $body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
                $body .= '<tr>';
                $body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Datos del Inmueble</th>';
                $body .= '</tr>';
                $body .= '<tr>';
                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Departamento</b></td>';
                $body .= '<td>' . $sel["departamento"] . '</td>';
                $body .= '</tr>';
                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>Provincia</b></td>';
                $body .= '<td>' . $sel["provincia"] . '</td>';
                $body .= '</tr>';
                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>Distrito</b></td>';
                $body .= '<td>' . $sel["distrito"] . '</td>';
                $body .= '</tr>';
                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>Ubicación</b></td>';
                $body .= '<td>' . $sel["ubicacion"] . '</td>';
                $body .= '</tr>';
                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>Área arrendada(m2)</b></td>';
                $body .= '<td>' . $sel["area_arrendada"] . ' m2' . '</td>';
                $body .= '</tr>';
                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>N° Partida Registral</b></td>';
                $body .= '<td>' . $sel["num_partida_registral"] . '</td>';
                $body .= '</tr>';
                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>Oficina Registral (Sede)</b></td>';
                $body .= '<td>' . $sel["oficina_registral"] . '</td>';
                $body .= '</tr>';
                $body .= '<tr>';


                $index_agua = 1;
                $index_luz = 1;
                $index_servicio = '';
                foreach ($contrato['data_inmuebles_suministros'] as $suministro) {

                    if ($suministro['tipo_servicio_id'] == 1) {
                        $index_servicio = $index_luz;
                        $index_luz++;
                    } else if ($suministro['tipo_servicio_id'] == 2) {
                        $index_servicio = $index_agua;
                        $index_agua++;
                    }

                    $body .= '<tr>';
                    $body .= '<td style="background-color:#ffffdd;"><b>Servicio de ' . $suministro['tipo_servicio'] . ' #' . $index_servicio . ' - N° de Suministro</b></td>';
                    $body .= '<td>' . $suministro["nro_suministro"] . '</td>';
                    $body .= '</tr>';
                    $body .= '<tr>';
                    $body .= '<td style="background-color:#ffffdd;"><b>Servicio de ' . $suministro['tipo_servicio'] . ' #' . $index_servicio . ' - Compromiso de pago</b></td>';
                    $body .= '<td>' . $suministro["tipo_compromiso"] . '</td>';
                    $body .= '</tr>';

                    if ($suministro["tipo_compromiso_pago_id"] == 1 || $suministro["tipo_compromiso_pago_id"] == 2 || $suministro["tipo_compromiso_pago_id"] == 6 || $suministro["tipo_compromiso_pago_id"] == 7) {
                        $monto_o_porcentaje = $suministro["monto_o_porcentaje"];

                        if ($suministro["tipo_compromiso_pago_id"] == 1) {
                            $compromiso_pago = 'Porcentaje del recibo';
                            $valor_compromiso_pago = $monto_o_porcentaje . '%';
                        } else {
                            $valor_compromiso_pago = 'S/. ' . $monto_o_porcentaje;

                            if ($suministro["tipo_compromiso_pago_id"] == 2) {
                                $compromiso_pago = 'Monto Fijo';
                            } else if ($suministro["tipo_compromiso_pago_id"] == 6) {
                                $compromiso_pago = 'Monto Base';
                            } else if ($suministro["tipo_compromiso_pago_id"] == 7) {
                                $compromiso_pago = 'Monto a facturar';
                            }
                        }

                        $compromiso_pago = 'Servicio de ' . $suministro['tipo_servicio'] . ' #' . $index_servicio . ' - ' . $compromiso_pago;

                        if (empty($monto_o_porcentaje)) {
                            $valor_compromiso_pago = 'Sin asignar';
                        }

                        $body .= '<tr>';
                        $body .= '<td style="background-color:#ffffdd;"><b>' . $compromiso_pago . '</b></td>';
                        $body .= '<td>' . $valor_compromiso_pago . '</td>';
                        $body .= '</tr>';
                    }
                }

                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>Arbitrios - Compromiso de pago</b></td>';
                $body .= '<td>' . $sel["tipo_pago_arbitrios"] . '</td>';
                $body .= '</tr>';

                if ($sel["tipo_compromiso_pago_arbitrios"] != 2) {
                    $porcentaje_pago_arbitrios = $sel["porcentaje_pago_arbitrios"] . '%';

                    $body .= '<tr>';
                    $body .= '<td style="background-color:#ffffdd;"><b>Porcentaje del Pago de Arbitrios (%)</b></td>';
                    $body .= '<td>' . $porcentaje_pago_arbitrios . '</td>';
                    $body .= '</tr>';
                }

                $body .= '</table>';
            }

            $body .= '<br>';


            foreach ($contrato['data_condiciones_economicas'] as $row) {
                $simbolo_moneda = $row["simbolo_moneda"];
                $moneda_contrato = $row["moneda_contrato"];
                $monto_renta = $simbolo_moneda . ' ' . number_format($row["monto_renta"], 2, '.', ',');
                $impuesto_a_la_renta_id = $row["impuesto_a_la_renta_id"];
                $impuesto_a_la_renta = $row["impuesto_a_la_renta"];
                $numero_cuenta_detraccion = $row["numero_cuenta_detraccion"];
                $garantia_monto = $simbolo_moneda . ' ' . number_format($row["garantia_monto"], 2, '.', ',');
                $tipo_adelanto_id = $row["tipo_adelanto_id"];
                $tipo_adelanto = $row["tipo_adelanto"];

                $plazo_id = $row["plazo_id"];
                $nombre_plazo = $row["nombre_plazo"];
                $cant_meses_contrato = $row["cant_meses_contrato"];
                $fecha_inicio = $row["fecha_inicio"];
                $fecha_fin = $row["fecha_fin"];
                $fecha_suscripcion = $row["fecha_suscripcion"];


                $periodo_gracia_id = $row["periodo_gracia_id"];
                $periodo_gracia = trim($row["periodo_gracia"]);
                $periodo_gracia_numero = trim($row["periodo_gracia_numero"]);


                if ($plazo_id == 1) {
                    if (empty($cant_meses_contrato)) {
                        $cant_meses_contrato = 'Sin asignar';
                    } else {
                        $model_data = new DataContrato();
                        $cant_meses_contrato = $model_data->sec_contrato_nuevo_de_meses_a_anios_y_meses($cant_meses_contrato) . ' (' . $cant_meses_contrato . ' meses)';
                    }

                    if (empty($fecha_inicio)) {
                        $contrato_inicio_fecha = 'Sin asignar';
                    } else {
                        $contrato_inicio_fecha = date("d/m/Y", strtotime($fecha_inicio));
                    }

                    if (empty($fecha_fin)) {
                        $contrato_fin_fecha = 'Sin asignar';
                    } else {
                        $contrato_fin_fecha = date("d/m/Y", strtotime($fecha_fin));
                    }

                    if (empty($fecha_suscripcion)) {
                        $contrato_fecha_suscripcion = 'Sin asignar';
                    } else {
                        $contrato_fecha_suscripcion = date("d/m/Y", strtotime($fecha_suscripcion));
                    }
                } else {

                    $cant_meses_contrato = 'Sin asignar';

                    if (empty($fecha_inicio)) {
                        $contrato_inicio_fecha = 'Sin asignar';
                    } else {
                        $contrato_inicio_fecha = date("d/m/Y", strtotime($fecha_inicio));
                    }

                    $contrato_fin_fecha = 'Sin asignar';

                    if (empty($fecha_suscripcion)) {
                        $contrato_fecha_suscripcion = 'Sin asignar';
                    } else {
                        $contrato_fecha_suscripcion = date("d/m/Y", strtotime($fecha_suscripcion));
                    }
                }


                if ($periodo_gracia_id == '1') {
                    $periodo_gracia_inicio = $row["periodo_gracia_inicio"];

                    if (empty($periodo_gracia_inicio)) {
                        $periodo_gracia_inicio_fecha = 'Sin asignar';
                    } else {
                        $periodo_gracia_inicio_fecha = date("d/m/Y", strtotime($periodo_gracia_inicio));
                    }

                    $periodo_gracia_fin = $row["periodo_gracia_fin"];

                    if (empty($periodo_gracia_fin)) {
                        $periodo_gracia_fin_fecha = 'Sin asignar';
                    } else {
                        $periodo_gracia_fin_fecha = date("d/m/Y", strtotime($periodo_gracia_fin));
                    }
                }

                if (empty($periodo_gracia)) {
                    $periodo_gracia = 'Sin asignar';
                }

                if (empty($periodo_gracia_numero)) {
                    $periodo_gracia_numero = 'Sin asignar';
                } else {
                    if ($periodo_gracia_numero == 1) {
                        $periodo_gracia_numero .= ' día';
                    } elseif ($periodo_gracia_numero > 1) {
                        $periodo_gracia_numero .= ' días';
                    }
                }
            }

            $body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
            $body .= '<tr>';
            $body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Condiciones Económicas y Comerciales</th>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Moneda del contrato</b></td>';
            $body .= '<td>' . $moneda_contrato . '</td>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd;"><b>Monto de Renta Pactada</b></td>';
            $body .= '<td>' . $monto_renta . '</td>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd;"><b>Impuesto a la renta</b></td>';
            $body .= '<td>' . $impuesto_a_la_renta . '</td>';
            $body .= '</tr>';

            if ((int) $impuesto_a_la_renta_id == 4) {

                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>N°. de cuenta de detracción (Banco de la Nación)</b></td>';
                $body .= '<td>' . $numero_cuenta_detraccion . '</td>';
                $body .= '</tr>';
            }

            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd;"><b>Monto de la garantías</b></td>';
            $body .= '<td>' . $garantia_monto . '</td>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd;"><b>Adelantos</b></td>';
            $body .= '<td>' . $tipo_adelanto . '</td>';
            $body .= '</tr>';

            if ($tipo_adelanto_id == '1') {

                $row_count = count($contrato['data_adelantos']);
                $meses_de_adelanto = '';
                if ($row_count > 0) {
                    $contador = 1;
                    foreach ($contrato['data_adelantos'] as $row) {
                        $num_periodo = $row["num_periodo"];

                        if ($num_periodo == 'x') {
                            $mes_adelanto = 'Antepenúltimo';
                        } else if ($num_periodo == 'y') {
                            $mes_adelanto = 'Penúltimo';
                        } else if ($num_periodo == 'z') {
                            $mes_adelanto = 'Último';
                        } else {
                            $mes_adelanto = $num_periodo;
                        }

                        $mes_adelanto = $mes_adelanto . ' mes';

                        if ($contador == 1) {
                            $meses_de_adelanto = $mes_adelanto;
                        } else {
                            $meses_de_adelanto .= ', ' . $mes_adelanto;
                        }

                        $contador++;
                    }
                }

                if ($row_count == 1) {
                    $cant_meses_adelando = 'mes';
                } else {
                    $cant_meses_adelando = 'meses';
                }

                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>Meses de adelanto</b></td>';
                $body .= '<td>' . $row_count . ' ' . $cant_meses_adelando . '(' . $meses_de_adelanto . ')' . '</td>';
                $body .= '</tr>';
            }

            $body .= '</table>';

            $body .= '<br>';

            $body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
            $body .= '<tr>';
            $body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Vigencia</th>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Vigencia</b></td>';
            $body .= '<td>' . $nombre_plazo . '</td>';
            $body .= '</tr>';

            if ($plazo_id == 1) {
                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Vigencia del Contrato</b></td>';
                $body .= '<td>' . $cant_meses_contrato . '</td>';
                $body .= '</tr>';
            }
            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd;"><b>Contrato - Fecha de Inicio</b></td>';
            $body .= '<td>' . $contrato_inicio_fecha . '</td>';
            $body .= '</tr>';

            if ($plazo_id == 1) {
                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>Contrato - Fecha de Fin</b></td>';
                $body .= '<td>' . $contrato_fin_fecha . '</td>';
                $body .= '</tr>';
            }

            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd;"><b>Periodo de gracia</b></td>';
            $body .= '<td>' . $periodo_gracia . '</td>';
            $body .= '</tr>';

            if ($periodo_gracia_id == '1') {

                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>Periodo de gracia - Número de días</b></td>';
                $body .= '<td>' . $periodo_gracia_numero . '</td>';
                $body .= '</tr>';
                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>Periodo de gracia - Fecha de Inicio</b></td>';
                $body .= '<td>' . $periodo_gracia_inicio_fecha . '</td>';
                $body .= '</tr>';
                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>Periodo de gracia - Fecha de Fin</b></td>';
                $body .= '<td>' . $periodo_gracia_fin_fecha . '</td>';
                $body .= '</tr>';
            }

            $body .= '</table>';

            $body .= '<br>';

            $body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
            $body .= '<tr>';
            $body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Incrementos</th>';
            $body .= '</tr>';

            $num_incremento_contador = 0;

            $row_count_incrementos = count($contrato['data_incrementos']);

            if ($row_count_incrementos > 0) {
                foreach ($contrato['data_incrementos'] as $sel) {
                    $num_incremento_contador++;

                    $a_partir_del_año = $sel["a_partir_del_año"] . ' año';
                    if ($sel["tipo_continuidad_id"] == 3) {
                        $a_partir_del_año = '';
                    }

                    $body .= '<tr>';
                    $body .= '<td style="background-color:#ffffdd; width: 300px;"><b>N° 0' . $num_incremento_contador . '</b></td>';
                    $body .= '<td>' . $sel["valor"] . ' ' . $sel["tipo_valor"] . ' ' . $sel["tipo_continuidad"] . ' ' . $a_partir_del_año . '</td>';
                    $body .= '</tr>';
                }
            } else {

                $body .= '<tr>';
                $body .= '<td colspan="2">El presente contrato no posee incrementos</td>';
                $body .= '</tr>';
            }
            $body .= '</table>';
            $body .= '<br>';





            $body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
            $body .= '<tr>';
            $body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Inflaciones</th>';
            $body .= '</tr>';


            $num_inflacion = 0;

            $row_count_inflacion = count($contrato['data_inflaciones']);

            if ($row_count_inflacion > 0) {
                foreach ($contrato['data_inflaciones'] as $sel) {
                    $num_inflacion++;

                    $fecha = $sel['fecha'];
                    $periocidad = $sel['tipo_periodicidad'] . ' ' . $sel['numero'] . ' ' . $sel['tipo_anio_mes'];
                    $moneda = $sel['moneda'];
                    $porcentaje_anadido = $sel['porcentaje_anadido'];
                    $tope_inflacion = $sel['tope_inflacion'];
                    $minimo_inflacion = $sel['minimo_inflacion'];

                    $body .= '<tr>';
                    $body .= '<td colspan="2" style="background-color:#ffffdd;"><b>N° ' . $num_inflacion . '</b></td>';
                    $body .= '</tr>';

                    if (!empty($fecha)) {
                        $body .= '<tr>';
                        $body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Fecha de Ajuste</b></td>';
                        $body .= '<td>' . $fecha . '</td>';
                        $body .= '</tr>';
                    }
                    $body .= '<tr>';
                    $body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Periodicidad</b></td>';
                    $body .= '<td>' . $periocidad . '</td>';
                    $body .= '</tr>';

                    $body .= '<tr>';
                    $body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Porcentaje Añadido</b></td>';
                    $body .= '<td>' . $porcentaje_anadido . '</td>';
                    $body .= '</tr>';

                    $body .= '<tr>';
                    $body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Tope de Inflación</b></td>';
                    $body .= '<td>' . $tope_inflacion . '</td>';
                    $body .= '</tr>';

                    $body .= '<tr>';
                    $body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Minimo de Inflación</b></td>';
                    $body .= '<td>' . $minimo_inflacion . '</td>';
                    $body .= '</tr>';
                }
            } else {

                $body .= '<tr>';
                $body .= '<td colspan="2">El presente contrato no posee inflaciones</td>';
                $body .= '</tr>';
            }

            $body .= '</table>';

            $body .= '<br>';



            $body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
            $body .= '<tr>';
            $body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Cuota Extraordinaria</th>';
            $body .= '</tr>';


            $num_cuota_extraordinaria = 0;
            $row_count_cuota = count($contrato['data_cuota_extraordinarias']);

            if ($row_count_cuota > 0) {
                foreach ($contrato['data_cuota_extraordinarias'] as $sel) {
                    $num_cuota_extraordinaria++;

                    $mes = $sel['mes'];
                    $multiplicador = $sel['multiplicador'];
                    $fecha = $sel['fecha'];

                    $body .= '<tr>';
                    $body .= '<td colspan="2" style="background-color:#ffffdd;"><b>N° ' . $num_cuota_extraordinaria . '</b></td>';
                    $body .= '</tr>';

                    if (!empty($fecha)) {
                        $body .= '<tr>';
                        $body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Fecha de Ajuste</b></td>';
                        $body .= '<td>' . $fecha . '</td>';
                        $body .= '</tr>';
                    }
                    $body .= '<tr>';
                    $body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Mes</b></td>';
                    $body .= '<td>' . $mes . '</td>';
                    $body .= '</tr>';

                    $body .= '<tr>';
                    $body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Multiplicador</b></td>';
                    $body .= '<td>' . $multiplicador . '</td>';
                    $body .= '</tr>';
                }
            } else {

                $body .= '<tr>';
                $body .= '<td colspan="2">El presente contrato no posee cuotas extraordinarias</td>';
                $body .= '</tr>';
            }

            $body .= '</table>';

            $body .= '<br>';



            foreach ($contrato['data_beneficiarios'] as $sel) {
                if ($sel["tipo_monto_id"] == 3) {
                    $monto_beneficiario = $monto_renta;
                } else {
                    if ($sel["tipo_monto_id"] == 2) {
                        $monto_beneficiario = $sel["monto"] . '%';
                    } else {
                        $monto_beneficiario = $simbolo_moneda . ' ' . number_format($sel["monto"], 2, '.', ',');
                    }
                }

                $body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
                $body .= '<tr>';
                $body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Datos del Beneficiario</th>';
                $body .= '</tr>';
                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Tipo de Persona</b></td>';
                $body .= '<td>' . $sel["tipo_persona"] . '</td>';
                $body .= '</tr>';
                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>Nombre</b></td>';
                $body .= '<td>' . $sel["nombre"] . '</td>';
                $body .= '</tr>';
                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>Tipo de Documento de Identidad</b></td>';
                $body .= '<td>' . $sel["tipo_docu_identidad"] . '</td>';
                $body .= '</tr>';
                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>Número de Documento de Identidad</b></td>';
                $body .= '<td>' . $sel["num_docu"] . '</td>';
                $body .= '</tr>';
                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>Tipo de forma de pago</b></td>';
                $body .= '<td>' . $sel["forma_pago"] . '</td>';
                $body .= '</tr>';

                if ($sel["forma_pago_id"] != '3') {

                    $body .= '<tr>';
                    $body .= '<td style="background-color:#ffffdd;"><b>Nombre del Banco</b></td>';
                    $body .= '<td>' . $sel["banco"] . '</td>';
                    $body .= '</tr>';
                    $body .= '<tr>';
                    $body .= '<td style="background-color:#ffffdd;"><b>N° de la cuenta bancaria</b></td>';
                    $body .= '<td>' . $sel["num_cuenta_bancaria"] . '</td>';
                    $body .= '</tr>';
                    $body .= '<tr>';
                    $body .= '<td style="background-color:#ffffdd;"><b>N° de CCI bancario</b></td>';
                    $body .= '<td>' . $sel["num_cuenta_cci"] . '</td>';
                    $body .= '</tr>';
                }

                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>Monto a depositar</b></td>';
                $body .= '<td>' . $sel["tipo_monto_a_depositar"] . '</td>';
                $body .= '</tr>';

                $body .= '<tr>';
                $body .= '<td style="background-color:#ffffdd;"><b>Monto</b></td>';
                $body .= '<td>' . $monto_beneficiario . '</td>';
                $body .= '</tr>';
                $body .= '</table>';
            }

            $body .= '<br>';

            $body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
            $body .= '<tr>';
            $body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Fecha de suscripción del contrato</th>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Fecha de suscripción del contrato</b></td>';
            $body .= '<td>' . $contrato_fecha_suscripcion . '</td>';
            $body .= '</tr>';
            $body .= '</table>';

            $body .= '<br>';

            $body .= '<table border="1" cellpadding="5" cellspacing="0" style="font-family:arial;" width="700px">';
            $body .= '<tr>';
            $body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">Observaciones</th>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<tr>';
            $body .= '<td style="background-color:#ffffdd; width: 300px;"><b>Observaciones</b></td>';
            $body .= '<td>' . $observaciones . '</td>';
            $body .= '</tr>';
            $body .= '</table>';

            $body .= '<br>';
        }


        $pre_asunto = '';

        if ($reenvio) {
            $pre_asunto = 'Reenvío - ';

            $usuario_id = $model_contrato->get_usuario_id();
            $created_at = date('Y-m-d H:i:s');

            $data_email['contrato_id'] = $contrato_id;
            $data_email['usuario_id'] = $usuario_id;
            $data_email['created_at'] = $created_at;
            $model_contrato->registrar_email_enviados($data_email);
        }

        //lista de correos
        $correos = new Correos(env('SEND_EMAIL'), env('EMAIL_TEST'));
        $lista_correos = $correos->send_email_solicitud_contrato_arrendamiento_detallado([]);

        $cc = $lista_correos['cc'];
        $bcc = $lista_correos['bcc'];

        $request = [
            "subject" => $pre_asunto . "Gestion - Sistema Contratos - Nuevo Contrato de Arrendamiento: Código - " . $sigla_correlativo . $codigo_correlativo,
            "body"    => $body,
            "cc"      => $cc,
            "bcc"     => $bcc,
            "attach"  => [
                // $filepath . $file,
            ],
        ];

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;


            $mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
            $mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
            $mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
            $mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

            if (isset($request["cc"])) {
                foreach ($request["cc"] as $cc) {
                    $mail->addAddress($cc);
                }
            }

            if (isset($request["bcc"])) {
                foreach ($request["bcc"] as $bcc) {
                    $mail->addBCC($bcc);
                }
            }

            $mail->isHTML(true);
            $mail->Subject  = $request["subject"];
            $mail->Body     = $request["body"];
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            $mail->send();

            if ($enviar_respuesta) {
                $result["status"] = 200;
                $result["messate"] = "Se ha enviado correctamente el correo";
                return $result;
            }
        } catch (Exception $e) {
            if ($enviar_respuesta) {
                $result["status"] = 400;
                $result["message"] = "Error al enviar el email. " . $mail->ErrorInfo;
                return $result;
            }
        }
    }
















    /*
    public function Listar (){
        
        try {
            $model =  new ContratoArrendamiento();
            $nuevo_contrato = $model->ObtenerTodosContratos();
            return json_encode($nuevo_contrato,JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
        
    }
    public function obtener_datos_generales($request){
        
        try {
            $contrato_id = $request['contrato_id'];

            $model =  new ContratoArrendamiento();
            $nuevo_contrato = $model->obtener_datos_generales($contrato_id);
            $nuevo_contrato = mb_convert_encoding($nuevo_contrato, 'UTF-8');

            return json_encode($nuevo_contrato,JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
        
    }
    public function obtener_datos_propietario($request){
        
        try {
            $contrato_id = $request['contrato_id'];

            $model =  new ContratoArrendamiento();
            $nuevo_contrato = $model->obtener_datos_propietario($contrato_id);
            $nuevo_contrato = mb_convert_encoding($nuevo_contrato, 'UTF-8');


            return json_encode($nuevo_contrato,JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
        
    }
    public function obtener_datos_inmueble($request){
        
        try {
            $contrato_id = $request['contrato_id'];

            $model =  new ContratoArrendamiento();
            $nuevo_contrato = $model->obtener_datos_inmueble($contrato_id);
            $nuevo_contrato = mb_convert_encoding($nuevo_contrato, 'UTF-8');


            return json_encode($nuevo_contrato,JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
        
    }
    public function obtener_datos_condiciones_economicas($request){
        try {
            $contrato_id = $request['contrato_id'];

            $model =  new ContratoArrendamiento();
            $condiciones_economicas = $model->obtener_datos_condiciones_economicas($contrato_id);
            $condiciones_economicas = mb_convert_encoding($condiciones_economicas, 'UTF-8');


            return json_encode($condiciones_economicas,JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
    }
    public function obtener_datos_adelantos($request){
        try {
            $contrato_id = $request['contrato_id'];

            $model =  new ContratoArrendamiento();
            $condiciones_economicas = $model->obtener_datos_adelantos($contrato_id);
            $condiciones_economicas = mb_convert_encoding($condiciones_economicas, 'UTF-8');


            return json_encode($condiciones_economicas,JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
    }

    public function obtener_datos_incrementos($request){
        try {
            $contrato_id = $request['contrato_id'];

            $model =  new ContratoArrendamiento();
            $incrementos = $model->obtener_datos_incrementos($contrato_id);
            $incrementos = mb_convert_encoding($incrementos, 'UTF-8');


            return json_encode($incrementos,JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';
            
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
    }

    public function obtener_datos_beneficiarios($request){
        try {
            $contrato_id = $request['contrato_id'];

            $model =  new ContratoArrendamiento();
            $incrementos = $model->obtener_datos_beneficiarios($contrato_id);
            $incrementos = mb_convert_encoding($incrementos, 'UTF-8');


            return json_encode($incrementos,JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';
            
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
    }

    public function obtener_datos_inflaciones($request){
        try {
            $contrato_id = $request['contrato_id'];

            $model =  new ContratoArrendamiento();
            $incrementos = $model->obtener_datos_inflaciones($contrato_id);
            $incrementos = mb_convert_encoding($incrementos, 'UTF-8');


            return json_encode($incrementos,JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';
            
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
    }
    public function obtener_datos_cuotas_extraordinarias($request){
        try {
            $contrato_id = $request['contrato_id'];

            $model =  new ContratoArrendamiento();
            $incrementos = $model->obtener_datos_cuotas_extraordinarias($contrato_id);
            $incrementos = mb_convert_encoding($incrementos, 'UTF-8');


            return json_encode($incrementos,JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';
            
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
    }
    public function obtener_datos_cambios_auditoria($request){
        try {
            $contrato_id = $request['contrato_id'];

            $model =  new ContratoArrendamiento();
            $incrementos = $model->obtener_datos_cambios_auditoria($contrato_id);
            $incrementos = mb_convert_encoding($incrementos, 'UTF-8');


            return json_encode($incrementos,JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';
            
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
    }
    public function obtener_datos_direccion_municipal($request){
        try {
            $contrato_id = $request['contrato_id'];

            $model =  new ContratoArrendamiento();
            $incrementos = $model->obtener_datos_direccion_municipal($contrato_id);
            $incrementos = mb_convert_encoding($incrementos, 'UTF-8');


            return json_encode($incrementos,JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';
            
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
    }

    public function obtener_datos_licencia_funcionamiento($request){
        try {
            $contrato_id = $request['contrato_id'];

            $model =  new ContratoArrendamiento();
            $incrementos = $model->obtener_datos_licencia_funcionamiento($contrato_id);
            $incrementos = mb_convert_encoding($incrementos, 'UTF-8');


            return json_encode($incrementos,JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';
            
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
    }

    public function obtener_datos_certificado_indeci($request){
        try {
            $contrato_id = $request['contrato_id'];

            $model =  new ContratoArrendamiento();
            $incrementos = $model->obtener_datos_certificado_indeci($contrato_id);
            $incrementos = mb_convert_encoding($incrementos, 'UTF-8');


            return json_encode($incrementos,JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';
            
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
    }

    public function obtener_datos_anuncio_publicitario($request){
        try {
            $contrato_id = $request['contrato_id'];

            $model =  new ContratoArrendamiento();
            $incrementos = $model->obtener_datos_anuncio_publicitario($contrato_id);
            $incrementos = mb_convert_encoding($incrementos, 'UTF-8');


            return json_encode($incrementos,JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';
            
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
    }

    public function obtener_datos_declaracion_jurada($request){
        try {
            $contrato_id = $request['contrato_id'];

            $model =  new ContratoArrendamiento();
            $incrementos = $model->obtener_datos_declaracion_jurada($contrato_id);
            $incrementos = mb_convert_encoding($incrementos, 'UTF-8');


            return json_encode($incrementos,JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';
            
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
    }
    public function obtener_datos_contratos_firmados($request){
        try {
            $contrato_id = $request['contrato_id'];

            $model =  new ContratoArrendamiento();
            $incrementos = $model->obtener_datos_contratos_firmados($contrato_id);
            $incrementos = mb_convert_encoding($incrementos, 'UTF-8');


            return json_encode($incrementos,JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';
            
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
    }

    public function obtener_datos_archivos_contrato($request){
        try {
            $contrato_id = $request['contrato_id'];
            $tipo_archivo_id = $request['tipo_archivo_id'];

            $model =  new ContratoArrendamiento();
            $incrementos = $model->obtener_datos_archivos_contrato($contrato_id,$tipo_archivo_id);
            $incrementos = mb_convert_encoding($incrementos, 'UTF-8');


            return json_encode($incrementos,JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';
            
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
    }
    public function obtener_datos_adendas($request){
        try {
            $contrato_id = $request['contrato_id'];

            $model =  new ContratoArrendamiento();
            $incrementos = $model->obtener_datos_adendas($contrato_id);
            $incrementos = mb_convert_encoding($incrementos, 'UTF-8');


            return json_encode($incrementos,JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';
            
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
    }

    public function obtener_datos_adendas_detalle($request){
        try {
            $adenda_id = $request['adenda_id'];

            $model =  new ContratoArrendamiento();
            $incrementos = $model->obtener_datos_adendas_detalle($adenda_id);
            $incrementos = mb_convert_encoding($incrementos, 'UTF-8');


            return json_encode($incrementos,JSON_UNESCAPED_UNICODE);
        } catch (Exception $th) {
            $result['status'] = 404;
            $result['result'] = $th->getMessage();
            $result['message'] = 'A ocurrido un error.';
            
            return json_encode($result,JSON_UNESCAPED_UNICODE);
        }
    }

    */
}
