<?php
require_once '/var/www/html/sys/db_connect.php';
require_once '/var/www/html/env.php';
require_once '/var/www/html/sys/mailer/class.phpmailer.php';
require_once '/var/www/html/sys/helpers.php';

function validacion_reportes_altenar(string $fecha_inicio, string $fecha_fin, $qa_test = false): bool
{
    $data_curl = validacion_reportes_alternar_curl($fecha_inicio);

    if (!$data_curl) {

        cron_print_log("<br> La API de validación de Reportes Altenar no devolvió los datos esperados.\n");

        $subject = "ALERTA LA API DE VALIDACIÓN DE REPORTES ALTENAR NO DEVOLVIÓ LOS DATOS ESPERADOS " . $fecha_inicio . " - " . time();

        $body = getTableTemplate("ALERTA DE VALIDACIÓN DE REPORTES ALTENAR", "La Api de validación de reportes Altenar no retorno los datos esperados a la fecha " . $fecha_inicio . " revise el log para mayor información.", $fecha_inicio, 'URGENTE');

        sendAlertEmail($body, $subject, $qa_test);

    } else {
        global $mysqli;

        $query_string = "SELECT Fecha, sum(Tickets) Tickets, sum(Apostado) AS Apostado, sum(Ganado) AS Ganado
                        FROM (
                        SELECT CAST(Fecha AS DATE) Fecha, count(TicketID) Tickets,sum(Apostado) AS Apostado, 0 AS Ganado
                        FROM at_altenar.Daily_summary_alt_bet
                        WHERE CAST(Fecha AS DATE)>= '$fecha_inicio' AND CAST(Fecha AS DATE) < '$fecha_fin'
                            group by CAST(Fecha AS DATE)
                        UNION ALL
                        SELECT CAST(FechaCalculo AS DATE) Fecha, 0 AS Tickets, 0 AS Apostado,  sum(Ganado) AS Ganado
                        FROM at_altenar.Daily_summary_alt_bet
                        WHERE CAST(FechaCalculo AS DATE)>= '$fecha_inicio' AND CAST(FechaCalculo AS DATE) < '$fecha_fin'
                            group by CAST(FechaCalculo AS DATE)
                        ) Tb
                        group by Fecha
                        order by Fecha desc";

        $query_result = $mysqli->query($query_string);

        if ($mysqli->error) {
            cron_print_log("<br>" . $mysqli->error . ".\n");
        }

        if ($query_result) {
            $row = $query_result->fetch_assoc();
            if ($row && is_array($row)) {
                $apostado_diferencia = 0.00;
                $apostado_bd = (float)$row['Apostado'];
                $apostado_api = (float)$data_curl["montoApostado"];
                if ($apostado_bd > 0) {
                    if ( $apostado_bd !== $apostado_api) {
                        cron_print_log("<br> Existe una diferencia entre el monto apostado obtenido desde la BD y el monto obtenido desde la API Calimaco. Se procede a enviar un correo de Alerta.\n");
                        cron_print_log("<br> Monto Apostado de la BD Altenar: " . number_format($apostado_bd, 2) . ", monto apostado de la API: " . number_format($apostado_api, 2)) . ", diferencia: " . number_format($apostado_diferencia, 2);
                        if ($apostado_bd > $apostado_api) {
                            $apostado_diferencia = $apostado_bd - $apostado_api;
                        } else {
                            $apostado_diferencia = $apostado_api - $apostado_bd;
                        }
                        $subject = "ALERTA POR DIFERENCIAS EN EL MONTO APOSTADO DE LA BD Y LA API DE ALTENAR - " . $fecha_inicio . " - " . time();
                        $body = getTableTemplate("ALERTA DE VALIDACIÓN DE REPORTES ALTENAR", "Existe diferencia de datos apostados entre la BD y la API de Altenar. Monto apostado BD: " . number_format($apostado_bd, 2) . ", monto apostado API " . number_format($apostado_api, 2) . ", diferencia " . number_format($apostado_diferencia, 2) . ".", $fecha_inicio, 'URGENTE');
                        sendAlertEmail($body, $subject, $qa_test);
                    }
                    return true;
                }

                $subject = "ALERTA EL MONTO APOSTADO EN LA BD ALTENAR ES 0.00 A LA FECHA " . $fecha_inicio . " - " . time();

                $body = getTableTemplate("ALERTA DE VALIDACIÓN DE REPORTES ALTENAR", "El monto apostado en la BD Altenar es 0.00.", $fecha_inicio, 'URGENTE');

                sendAlertEmail($body, $subject, $qa_test);
            }
        }
    }

    return false;
}


function validacion_reportes_alternar_curl($fecha)
{
    $curl = curl_init();
    $key = env('VALIDATOR_TL');
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.apuestatotal.com/v2/calimaco/getSummaryForDay',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '{
        "Date":"' . $fecha . '"
    }',
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Bearer ' . $key,

        ),
    ));

    $response = curl_exec($curl);

    if ($response) {
        $responseData = json_decode($response, true);

        if ($responseData) {

            if (((int)$responseData['http_code']) === 200) {
                if(isset($responseData['result']) && is_array($responseData['result']) && count($responseData['result']) > 0) {
                    $data = $responseData['result'][0];
                    if($data['monto_apostado'] !== null) {
                        $cantTks = $data['cant_tks'];
                        $montoApostado = $data['monto_apostado'];
                        $responseData['cant_tickets'] = (int)$cantTks;
                        $responseData['montoApostado'] = (float)$montoApostado;
                        curl_close($curl);
                        return $responseData;
                    }

                    cron_print_log("<br> La API de validación de reportes Altenar devolvió un monto apostado nulo.\n");
                } else {
                    cron_print_log("<br> La API de validación de reportes Altenar devolvió un resultado vacío.\n");
                }
            } else {
                cron_print_log("<br> La API de validación de reportes Altenar respondión con código " . $responseData['http_code'] . " con respuesta '" . (is_array($responseData['result']) ? implode(",", $responseData['result']) : $responseData['result']) . "'.\n");
            }
        } else {
            cron_print_log("<br> La respuesta de la API de validación de reportes Altenar no se pudo parsear a formato JSON.\n");
        }
    } else {
        cron_print_log("<br> La API de validación de reportes Altenar no respondió.\n");
    }

    curl_close($curl);
    return false;
}

function sendAlertEmail($body, $subject, $qa_test)
{
    try {
        $mail = new PHPMailer(true);
        $mail->IsSMTP();
        $mail->CharSet = 'utf-8';
        $mail->SMTPDebug = 1;
        $mail->SMTPAuth = true;
        $mail->Host = "smtp.gmail.com";
        $mail->Port = 465;
        $mail->SMTPSecure = "ssl";

        if ($qa_test !== false) {
            $mail->AddBCC('erika.polo@testtest.apuestatotal.com');
        }
        // $mail->AddBCC("bladimir.quispe@testtest.kurax.dev");
        // $mail->AddBCC("neil.flores@testtest.kurax.dev");
        //$mail->AddBCC("gorqui.chavez@testtest.kurax.dev");
        $mail->AddBCC('ricardo.lanchipa@testtest.kurax.dev');
        //$mail->AddBCC("luis.chambilla@testtest.kurax.dev");
        //$mail->AddBCC("francisco.illescas@testtest.kurax.dev");

        $mail->Username = env('MAIL_GESTION_USER');
        $mail->Password = env('MAIL_GESTION_PASS');
        $mail->FromName = env('MAIL_GESTION_NAME');
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->isHTML();
        $mail->send();

    } catch (phpmailerException $e) {
        cron_print_log("<br>" . $e->getMessage() . "</br>/n");
    } catch (Exception $e) {
        cron_print_log("<br>" . $e->getMessage() . "</br>/n");
    }
}

function getTableTemplate($titulo, $descripcion, $fecha, $nivel)
{
    return '<table style="border: 1px solid #000;">
<tr>
    <th colspan="3" style="text-align:center; padding: 5px; border: 1px solid #000; background-color:#000; color:#FFF;">' . $titulo . '</th>
  </tr>
  <tr>
    <th  style="text-align:center; padding: 5px; border: 1px solid #000; width: 90px;">Fecha</th>
    <th  style="text-align:center; padding: 5px; border: 1px solid #000;">Descripción</th>
    <th  style="text-align:center; padding: 5px; border: 1px solid #000;">Nivel</th>
  </tr>
  <tr>
    <td   style="padding: 5px; border: 1px solid #000; width: 90px;">' . $fecha . '</td>
    <td   style="padding: 5px; border: 1px solid #000;">' . $descripcion . '</td>
    <td   style="padding: 5px; border: 1px solid #000;">' . $nivel . '</td>
  </tr>
</table>';
}
