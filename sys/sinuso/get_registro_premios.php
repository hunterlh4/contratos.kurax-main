<?php
include("db_connect.php");
include("sys_login.php");
include '/var/www/html/sys/mailer/class.phpmailer.php';
require_once '/var/www/html/env.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function sorteo_variables(): array
{
    $SORTEO_NAVIDAD_ID_DEV = 43;
    $SORTEO_NAVIDAD_ID_PROD = 43;

    $TOKEN_SORTEO_DEV = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI2IiwianRpIjoiZjU1Njc3Y2FkYjczZDQwNGVkNzhhZjYzMDM0ZDdhOGU4NTRkYzY4NzdlNGU4MTZiYmUyYzYwNDRjNDhkNDkyMWQ5NGI5MzZhMmQyMTk0MDEiLCJpYXQiOjE3MTY2NDgxMDEsIm5iZiI6MTcxNjY0ODEwMSwiZXhwIjoxNzQ4MTg0MTAxLCJzdWIiOiIyMCIsInNjb3BlcyI6W119.asT-YP-f7N56g66fIn97wcLnme4ELqujkrjfhGX9c2uTM2cjAsX2rfAPdCWDaPYeVwSg0TnoNHye5R9h21cUInxNG9374WXgFdXMvElP44WmdKkTEx6haOhZzmT-K20IwLhq0-FK4g0BIKghQ1vg8fpjKLY8SSeTeWsAbbFtywFl90oQ5Q8GrVNC2441GMJrvTrydGcsE5aijxFsWkQFz_CHv8DhRTznmnyfce_WOlPb3vCx6D5QCAtg5U9n9xDX79Vxm8Ce4fxPq0SXrSGE7dGw3VHGpgApWHL52G6bl-tSS6MHn9TlsWaenCIT04mONy_l2Xx9WTq-XmN2EJ9ljyKnKAqU8QojIBbEFkCUc9Cg5j75E90g-WY07hN7cv9seM2l74JyYtnW6VyHMHJ4Ja8Td6w1WL2QiBgDw3-LyFv38MZo5uHIqUNW0rYkzxqeEMKy94aC2CX7ambsG9STbdpYhegC6yzooa1zB5nzPExOt_OAoKgbPtIHM3sZKoFTb5ILTuE6qcNyUT01NgeFparz8wvHs3wQ39hlGgq5ZO82xmh750ualDx7o6XTNYrf-kwKmdEK6wybjB6XeZhh9-X3B4PlyeRdJwMogSNHIKtuTJfC3E_bbSdAq-o9r5qpWrz8zOD1SWf8ATIF1-8PXNoLBlq1ISbb_xTPbUpZbSw";
    $TOKEN_SORTEO_PROD_2 = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiMzQwOGEyNmNjNTYzMDk5OGZjMWQ4NTk5OTU5NjcyZGI2MmRiNTIxNzI4YjU0NTRlM2IzNjhmYjc4MmU2ZTBlMGVhYmI1YjU5NjRiZmI2MjciLCJpYXQiOjE2ODY3NjI1ODksIm5iZiI6MTY4Njc2MjU4OSwiZXhwIjoxNzE4Mzg0OTg5LCJzdWIiOiIxMiIsInNjb3BlcyI6W119.A0Mb49tPFuioReaK47VLrUqE3mB4mgr09OY3KXwXjfNZeZKVJLwz7oP0qaqRCyNy3lhvu2itrV5YfAIXqLYTGqpoJ5pUKfAEW7RjnZXgjLsLuIBEclteXyDrrT7gvBcelLImRbtXTBW8_XY-Z4g00TCIxMg0K0lptC8YDwYYv2ZBZQeHDVCoMSXO9o55pHPyVpLzLqOz7y0s5GCVP6N_rcME1utLrF9RMfo2DG0F5c74k1zJBRhA1FJD4vH-_gwzxjuRXGq90V7EIYjndBVudJEVF8FKcWnDyVA_o0TbrmjJoDZGXn2wzcJuViOtV9z76xJan0yOI_DLlfXydjbHpJLZvE-ol87-8V4qO5wq8oyjWnOJsHH4x45nU7de64n76wIfXDATih5p0PEine9y3YOiUJawqCIP4Oy8JsWuuejv-9ocIIzl8tz2iZ-aEe32C2KO9NcBU6ad9n3AGcFXm4hM-CZjZKafY5Dlg7DtiSEEAWeYIpnf4FbdaL71ejp-js6DUbf9R8KljfzyYh-LqisHxrnwpDD_h6ugMK25Gb-b_3aEApNvLLGVIv02-AVmEFxFrbPdPgQisaLk0UX4El7XMQMC2CzKmlH8VvtkaEx_xnVy_54HmXvZfCaHjgzXRsFc5_JLWFwyoMSaA3c-9sL2KpWwxU2IPbLIS2feIb4";
    $TOKEN_SORTEO_PROD = "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiI2IiwianRpIjoiNjdhNzkxYzNmZGQ5ZTgwMWM0NjgyYWMwMWZkMWY2ZTAzYjY5MmE2ZjE1ODM4YTA0OTNjZjRkNTYyYmNhZTcyYjQ5NTUzMzA4NDcxOTc2NTgiLCJpYXQiOjE3MDIzOTMzNTksIm5iZiI6MTcwMjM5MzM1OSwiZXhwIjoxNzM0MDE1NzU5LCJzdWIiOiIxMiIsInNjb3BlcyI6W119.tM8VLuu820GJStAzazChYDVAKaFae3nqGVIpJ6uQPnKtU5xvaeUo-nGb-hbHBiOtFINox30azlfEzDFMjANq03MAFOp5uvZDkh9Qfv9Wl6zObTihjSgLccWNNfSGGc1n5MEuAX02Vf6xPw6tS6sg1_y6l9YOATaQ0Gw5W54S0cX4ZTXRSiJQN7V4OZdGcVXZt7yQl5NjpBNobSH2vFde_MpzMzrAfhG65BFzeDZqBooLe1yac7aRAnJSxRdQTLwt5hXtHNPiB6FxYcQiK-GfukhMHA2D22sCqPqVtiy5KUzQKTJAtcM8m0_MRjyyQ3QE4TB7x3e1ZQPDYIv-Go2yLqDxmkIuu1IJoRqveUZ-_QDyXEythGkYWERA14KbUKaPsk3NJ9D1UHaxuReUDFOXdSZMk3PVHomEq7eAcWYuToOoSpJPthiVcdnP0wmXP8slNixqlaV7qadUy3RPVIOhc4aWd6_kphA0W0fZTMf8JUJm24lups2YyClNXt8eYNqYHCju3oOl_qttLJ1VLVcVeX00HHrEXmPi_brZDu8QRLPfJq7bk8R41-H6_y9k7lwuoi-eBDe1vw-WLkFoHn_rQTMFMR2IGmSZJo5RKnxkHIlseAt3Nnp0DGw1nvdZILBugTjm6gKYjHeZBTSbsae-ROiSvDkQWPyadvaq6Pos5Vg";

    $SORTEO_ID_DEV = $SORTEO_NAVIDAD_ID_DEV;
    $SORTEO_ID_PROD = $SORTEO_NAVIDAD_ID_PROD;

    return compact("SORTEO_ID_DEV", "SORTEO_ID_PROD", "TOKEN_SORTEO_DEV", "TOKEN_SORTEO_PROD");
}

function action_response($code, $message = "")
{
    return json_encode(['code' => $code, 'message' => $message]);
}

function consultar_archivos($ticket_id, $type)
{
    $photo_type = "tipo = 'foto'";
    if (isset($type)) {
        if ($type === "markt") {
            $photo_type = "(tipo = 'foto' OR tipo = 'foto_markt')";
        } else {
            $photo_type = "tipo = 'foto_" . $type . "'";
        }
    }
    global $mysqli;
    $query = "SELECT count(id) AS cant from tbl_archivos WHERE item_id =" . $ticket_id . " AND tabla ='tbl_registro_premios' AND  " . $photo_type;
    $result = $mysqli->query($query);
    while ($r = $result->fetch_assoc()) $consulta = $r;
    return $consulta;
}


function consultar_archivos_firma($ticket_id)
{
    global $mysqli;
    $query = "SELECT count(id) AS cant from tbl_archivos WHERE item_id =" . $ticket_id . " AND tabla ='tbl_registro_premios' AND  tipo='signature' ";
    $result = $mysqli->query($query);
    while ($r = $result->fetch_assoc()) $consulta = $r;
    return $consulta;
}


function valida_repeat($ticket_id)
{
    global $mysqli;
    $queryR = "SELECT COUNT(id) AS cant FROM tbl_registro_premios WHERE ticket_id ='" . $ticket_id . "' ";
    $resultR = $mysqli->query($queryR);
    if ($resultR) {
        while ($rR = $resultR->fetch_assoc()) $consultaR = $rR;
    } else {
        $consultaR = 0;
    }
    return $consultaR['cant'];
}

function valida_repeat_goldenrace($ticket_id, $fecha)
{
    global $mysqli;
    $queryR = " SELECT COUNT(id) AS cant 
                FROM tbl_registro_premios 
                WHERE 
                    ticket_id ='" . $ticket_id . "' 
                    -- and DATE(created_at) BETWEEN DATE('" . $fecha . "') and DATE_ADD(DATE('" . $fecha . "'), INTERVAL + 2 DAY)
                    and created_at >= '2022-03-31 21:59:39'
                ";
    // 2022-03-31 21:59:39 -> fecha de corte de tickets version 1 
    $resultR = $mysqli->query($queryR);
    if ($resultR) {
        while ($rR = $resultR->fetch_assoc()) $consultaR = $rR;
    } else {
        $consultaR = 0;
    }
    return $consultaR['cant'];
}

$this_menu = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'registro' AND sub_sec_id = 'premios' LIMIT 1")->fetch_assoc();
$menu_id = $this_menu["id"];

if (isset($_POST["listado_tickets_jackpot"])) {
    $photo_types = array(
        array(
            "type" => "markt",
            "label" => "Marketing",
        ),
        array(
            "type" => "id",
            "label" => "Documento de Identidad",
        ),
        array(
            "type" => "vouch",
            "label" => "Comprobante",
        ),
    );

    if (!array_key_exists($menu_id, $usuario_permisos) && !in_array("view", $usuario_permisos[$menu_id])) {
        die(action_response('403', 'No Autorizado. No tienes permisos.'));
    }
    $actu = $_POST['listado_tickets_jackpot']['update'];
    $localId = $_POST['listado_tickets_jackpot']['localId'];

    if (isset($actu)) {
        $date = date('Y-m-d');
        $newFecha = strtotime('-1 day', strtotime($date));
        $newFecha = date('Y-m-d', $newFecha);
        $locales_acceso = "g.paid_local_id IN ($localId)";

        /*if ($login["usuario_locales"]) {
            $locales_acces = implode(',', $login["usuario_locales"]);
            $locales_acceso = "l.id IN  (" . $locales_acces . ") OR";
        }*/

        $result = $mysqli->query("
		SELECT
		g.id,
		g.ticket_id,
		l.nombre,
		g.created_at,
		g.monto_apostado,
		g.monto_entregado,
		g.num_doc,
		u.usuario,
		g.tipo_registro AS tipo_premio,
		g.tipo_doc,
		g.autoriza
		FROM
		tbl_registro_premios g
		LEFT JOIN tbl_locales l ON l.id = g.paid_local_id
		INNER JOIN tbl_usuarios u ON u.id = g.user_id
		WHERE
		" . $locales_acceso . "		
		AND g.status = 1
		ORDER BY g.id DESC LIMIT 10
		");

        //AND DATE(g.created_at) = DATE(now())
        $consulta = [];

        try {

            if (!$result) {
                throw new Exception('No hay data para listar');
            }

            while ($r = $result->fetch_assoc()) $consulta[] = $r;

            $html = "";


            foreach ($consulta as $key => $ticket) {

                $html .= "<tr>";
                $html .= "<td>" . $ticket['ticket_id'] . "</td>";

                switch ($ticket['tipo_premio']) {
                    case 0:
                        $html .= "<td> JACKPOT </td>";
                        break;
                    case 1:
                        $html .= "<td> BINGO </td>";
                        break;
                    case 2:
                        $html .= "<td> PREMIO MAYOR </td>";
                        break;
                    case 3:
                        $html .= "<td> SORTEO </td>";
                        break;
                    case 4:
                        $html .= "<td> MEGAJACKPOT </td>";
                        break;
                    case 6:
                        $html .= "<td> TORITO </td>";
                        break;

                    default:
                        $html .= "<td>" . $ticket['tipo_premio'] . "</td>";
                        break;
                }
                $t_local_nombre = $ticket['nombre'] ?? "No registrada";
                $html .= "<td>" . $t_local_nombre . "</td>";
                $html .= "<td>" . $ticket['created_at'] . "</td>";
                $html .= "<td>" . ($ticket['tipo_premio'] == 6 ? "" : $ticket['monto_apostado']) . "</td>";
                $html .= "<td>" . $ticket['monto_entregado'] . "</td>";
                $html .= "<td>" . $ticket['num_doc'] . "</td>";
                $html .= "<td>" . $ticket['usuario'] . "</td>";
                // NO BORRAR LA SIGUIENTE LINEA. servira para el futuro cuando se implemente la foto del ticket.
                $cantFirma = consultar_archivos_firma($ticket['id']);
                $conteo = consultar_archivos($ticket['id'], $photo_types[0]["type"]);

                /*     if ($conteo['cant'] > 0) {
                         $html .= "<td> <button class='btn btn-rounded btn-primary btn-xs addFoto' data-id-jackpot=" . $ticket['id'] . " data-cant=" . $conteo['cant'] . " data-toggle='tooltip' title='Cargar Imagen de Ticket'><i class='fa fa-camera' aria-hidden='true'></i> (" . $conteo['cant'] . ") </button></td>";
                     } else {
                         $html .= "<td> <button class='btn btn-rounded btn-danger btn-xs addFoto' data-id-jackpot=" . $ticket['id'] . " data-cant=" . $conteo['cant'] . " data-toggle='tooltip' title='Cargar Imagen de Ticket'><i class='fa fa-camera' aria-hidden='true'></i> (" . $conteo['cant'] . ") </button></td>";
                     }*/

                $conteoFotos = array();
                foreach ($photo_types as $photo_type) {
                    $conteoFotos[] = consultar_archivos($ticket['id'], $photo_type["type"]);
                }

                for ($i = 0; $i < count($photo_types); $i++) {
                    //$button_disabled = $photo_types[$i]["type"] === "markt" && $ticket['autoriza'] == 0? "disabled" : "";
                    $button_disabled = $photo_types[$i]["type"] === "markt" && $ticket['autoriza'] == 0 ? "style='display:none'" : "";
                    if ($conteoFotos[$i]['cant'] > 0) {
                        $html .= "<td> <button $button_disabled class='btn btn-rounded btn-primary btn-xs addFoto' data-id-jackpot=" . $ticket['id'] . " data-type = " . $photo_types[$i]["type"] . " data-cant=" . $conteoFotos[$i]['cant'] . " data-toggle='tooltip' title='Cargar Imagen de " . $photo_types[$i]["label"] . "'><i class='fa fa-camera' aria-hidden='true'></i> (" . $conteoFotos[$i]["cant"] . ") </button></td>";
                    } else {
                        $html .= "<td> <button $button_disabled class='btn btn-rounded btn-danger btn-xs addFoto'  data-id-jackpot=" . $ticket['id'] . " data-type = " . $photo_types[$i]["type"] . " data-cant=" . $conteoFotos[$i]['cant'] . " data-toggle='tooltip' title='Cargar Imagen de " . $photo_types[$i]["label"] . "'><i class='fa fa-camera' aria-hidden='true'></i> (" . $conteoFotos[$i]["cant"] . ") </button></td>";
                    }
                }


                if ($cantFirma['cant'] > 0) {
                    $html .= "<td> <button class='btn btn-rounded btn-primary btn-xs printOnlySignature' data-tipodoc=" . $ticket['tipo_doc'] . "  data-id-paper=" . $ticket['id'] . " data-cant=" . $conteo['cant'] . " data-toggle='tooltip' title='Imprimir Ticket'><i class='fa fa-file-text-o' aria-hidden='true'></i></button></td>";
                    $html .= "<td style='display: none'> <button class='btn btn-rounded btn-xs btnSignature btn-success' disabled='disabled' data-idTicket=" . $ticket['ticket_id'] . "  data-num-doc=" . $ticket['num_doc'] . "  data-tipodoc=" . $ticket['tipo_doc'] . "  data-id-paper=" . $ticket['id'] . " data-toggle='tooltip' title='Ticket Firmado'><i class='fa fa-pencil-square-o' aria-hidden='true'></i></button></td>";
                } else {
                    $html .= "<td> <button class='btn btn-rounded btn-primary btn-xs printOnly' data-tipodoc=" . $ticket['tipo_doc'] . "  data-id-paper=" . $ticket['id'] . " data-cant=" . $conteo['cant'] . " data-toggle='tooltip' title='Imprimir Ticket' ><i class='fa fa-file-text-o' aria-hidden='true'></i></button></td>";
                    $html .= "<td style='display: none'> <button class='btn btn-rounded btn-xs btnSignature btn-warning' data-idTicket=" . $ticket['ticket_id'] . "  data-num-doc=" . $ticket['num_doc'] . "  data-tipodoc=" . $ticket['tipo_doc'] . "  data-id-paper=" . $ticket['id'] . " data-toggle='tooltip' title='No tiene Firma Registrada'><i class='fa fa-pencil-square-o' aria-hidden='true'></i></button></td>";
                    /*if ($ticket['autoriza'] > 0) {
                        $html .= "<td> <button class='btn btn-rounded btn-primary btn-xs printOnly' data-tipodoc=" . $ticket['tipo_doc'] . "  data-id-paper=" . $ticket['id'] . " data-cant=" . $conteo['cant'] . " data-toggle='tooltip' title='Imprimir Ticket' ><i class='fa fa-file-text-o' aria-hidden='true'></i></button></td>";
                        $html .= "<td> <button class='btn btn-rounded btn-xs btnSignature btn-warning' data-idTicket=" . $ticket['ticket_id'] . "  data-num-doc=" . $ticket['num_doc'] . "  data-tipodoc=" . $ticket['tipo_doc'] . "  data-id-paper=" . $ticket['id'] . " data-toggle='tooltip' title='No tiene Firma Registrada'><i class='fa fa-pencil-square-o' aria-hidden='true'></i></button></td>";
                    } else {
                        $html .= "<td> <button class='btn btn-rounded btn-primary btn-xs printOnly' data-tipodoc=" . $ticket['tipo_doc'] . "  data-id-paper=" . $ticket['id'] . " data-cant=" . $conteo['cant'] . " data-toggle='tooltip' title='Imprimir Ticket' ><i class='fa fa-file-text-o' aria-hidden='true'></i></button></td>";
                        $html .= "<td> <button class='btn btn-rounded btn-xs btnSignature btn-danger' data-idTicket=" . $ticket['ticket_id'] . "  data-num-doc=" . $ticket['num_doc'] . "  data-tipodoc=" . $ticket['tipo_doc'] . "  data-id-paper=" . $ticket['id'] . " disabled data-toggle='tooltip' title='No autoriza Firma'><i class='fa fa-pencil-square-o' aria-hidden='true'></i></button></td>";
                    }*/
                }

                $html .= "<tr>";
            }
            echo die(action_response('001', $html));

        } catch (Exception $e) {
            echo('{"error":"' . $e->getMessage() . '"}');
        }
    }
}


if (isset($_POST["show_doc"])) {
    if (!array_key_exists($menu_id, $usuario_permisos) && !in_array("view", $usuario_permisos[$menu_id])) {
        die(action_response('403', 'No Autorizado. No tienes permisos para buscar DNIs en sistema.'));
    }

    $doc = $_POST['show_doc']['doc'];
    $tipo = $_POST['show_doc']['tipo'];

    if (preg_match("/^[0-9]{9}$/", $doc)) {

        $result = $mysqli->query("
		SELECT
		id,
		num_doc,
		tipo_doc,
		nombres,
		apellido_paterno,
		apellido_materno
		FROM tbl_cliente_extranjero
		WHERE
		num_doc = '$doc' AND tipo_doc='$tipo';
		");

        $html = "";

        try {
            while ($r = $result->fetch_assoc()) $consulta = $r;
            if (isset($consulta) && $tipo == 1) {
                echo die(action_response('201', $consulta));
            } elseif (isset($consulta) && $tipo == 2) {
                echo die(action_response('202', $consulta));
            } else {
                if ($tipo == 1) {
                    die(action_response('401', 'Nro de CE no registrado.'));
                }

                if ($tipo == 2) {
                    die(action_response('402', 'Nro de Pasaporte no registrado.'));
                }
            }

        } catch (Exception $e) {
            var_dump($e->getMessage());
        }

    } else {
        die(action_response('400', 'Documento Inválido (max. 9 digitos).'));
    }
}


if (isset($_POST["show_dni"])) {
    if (!array_key_exists($menu_id, $usuario_permisos) && !in_array("view", $usuario_permisos[$menu_id])) {
        die(action_response('403', 'No Autorizado. No tienes permisos para buscar DNIs en sistema.'));
    }

    $dni = $_POST["show_dni"]["dni"];
    $api = false;

    if (preg_match("/^[0-9]{8}$/", $dni)) {

        $es_cajero = $mysqli->query("SELECT u.id FROM tbl_personal_apt p
            LEFT JOIN tbl_usuarios u on u.personal_id = p.id
            WHERE  p.dni = '$dni'  AND p.cargo_id = 5 AND p.estado = 1")->fetch_assoc();
        if($es_cajero){
            die(action_response('405', 'Personal de la empresa, no puede registrar el ticket a su nombre. Consultar con Área de Soporte la baja del personal'));
        }

        $result = $mysqli->query("
		SELECT
		dni,
		nombres,
		apellido_paterno,
		apellido_materno
		FROM tbl_consultas_dni
		WHERE
		dni = '$dni';
		");

        $html = "";

        while ($r = $result->fetch_assoc()) $consulta = $r;

        if (empty($consulta)) {

            if (!array_key_exists($menu_id, $usuario_permisos) || !in_array("new", $usuario_permisos[$menu_id])) {
                die(action_response('401', 'No Autorizado. Solo puedes buscar DNIs contenidos en nuestra base de datos.'));
            }
            $api = true;
            $ch = curl_init();

            $accessToken = env('SOPORTE_V2_TOKEN');

            curl_setopt_array($ch, [
                CURLOPT_URL => "https://api.apuestatotal.com/v2/dni?dni=" . $dni,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_HTTPHEADER => [
                    "Accept: application/json",
                    "Authorization: Bearer " . $accessToken
                ],
            ]);

            $result = json_decode(curl_exec($ch), true);

            if (curl_errno($ch)) {
                echo 'Error:' . curl_error($ch);
            }
            curl_close($ch);
            $consulta = ($result["result"] ?? []);

        }


        if (isset($consulta["dni"]) && $consulta["dni"] === $dni) {

            if (empty($consulta["caracter_verificacion"])) {
                $consulta["caracter_verificacion"] = "";
            }
            if (empty($consulta["caracter_verificacion_anterior"])) {
                $consulta["caracter_verificacion_anterior"] = "";
            }

            $mysqli->query("
			INSERT INTO tbl_consultas_dni (
				dni,
				nombres,
				apellido_paterno,
				apellido_materno,
				caracter_verificacion,
				caracter_verificacion_anterior,
				created_at
				) VALUES(
					'" . $consulta["dni"] . "',
					'" . $consulta["nombres"] . "',
					'" . $consulta["apellido_paterno"] . "',
					'" . $consulta["apellido_materno"] . "',
					'" . $consulta["caracter_verificacion"] . "',
					'" . $consulta["caracter_verificacion_anterior"] . "',
					'" . date('Y-m-d H:i:s') . "'
					)
					");
        }

        echo die(action_response('200', $consulta));

    } else {
        die(action_response('400', 'DNI Inválido. Por Favor Digitar los 8 dígitos del DNI.'));
    }
}

if (isset($_POST["show_cliente_data"])) {
    if (!array_key_exists($menu_id, $usuario_permisos) && !in_array("view", $usuario_permisos[$menu_id])) {
        die(action_response('403', 'No Autorizado. No tienes permisos para buscar DNIs en sistema.'));
    }

    $num_doc = $_POST['show_cliente_data']['numDoc'];
    $tipo_doc = $_POST['show_cliente_data']['tipoDoc'];

    if (preg_match("/^[0-9]{8,9}$/", $num_doc)) {

        $result = $mysqli->query("
		SELECT
		data,
        tipo_data        
		FROM tbl_client_data
		WHERE
		    num_doc = '$num_doc' AND tipo_doc='$tipo_doc' AND status = 1;
		");

        try {
            $tel = "";
            $email = "";
            $profession = "";

            while ($r = $result->fetch_assoc()) {
                if ($r['tipo_data'] == 1) {
                    $tel = $r;
                } else if ($r['tipo_data'] == 2) {
                    $email = $r;
                }else if ($r['tipo_data'] == 4) {
                    $profession = $r;
                }
            };
            if (isset($tel) || isset($email) || isset($profession)) {
                echo die(action_response('200', [$tel, $email, $profession]));
            } else {
                die(action_response('401', 'No hay data para este cliente'));
            }

        } catch (Exception $e) {
            var_dump($e->getMessage());
        }

    } else {
        die(action_response('400', 'Documento Inválido (Cantidad de dígitos incorrecta).'));
    }
}

if (isset($_POST["reg_ticket_jackpot"])) {
    $data = $_POST['reg_ticket_jackpot'];

    if (!array_key_exists($menu_id, $usuario_permisos) && !in_array("generate", $usuario_permisos[$menu_id])) {
        die(action_response('403', 'No Autorizado. No tienes permisos para buscar DNIs en sistema.'));
    }

    $turno = get_turno_id($data["session_cookie"], $data["paid_local_id"], $mysqli);

    $repeated = $mysqli->query("
    SELECT
        rp.id,
        rp.created_at,
        rp.local_id,
        rp.tipo_doc,
        rp.num_doc,
        rp.ticket_id,
        rp.autoriza,
        rp.monto_apostado,
        rp.monto_entregado,
        rp.tipo_registro,
        rp.user_id,
        rp.paid_local_id,
        rp.caja_id
    FROM
        tbl_registro_premios AS rp
    WHERE ticket_id ='$data[ticket_id]'
        and rp.created_at >= '2022-03-31 21:59:39'
    limit 1
    ");
    // 2022-03-31 21:59:39 -> fecha de corte de tickets version 1 

    $repeated_ticket = array();
    while ($r = $repeated->fetch_assoc()) $repeated_ticket = $r;
    if (count($repeated_ticket)>0) {
        print_r(json_encode(["lastID" => $repeated_ticket['id']]));
    }
    else{
        $resul_nums = $mysqli->query("
            INSERT INTO 
                tbl_registro_premios (
                    created_at,
                    local_id,
                    tipo_doc,
                    num_doc,
                    ticket_id,
                    autoriza,
                    monto_apostado,
                    monto_entregado,
                    tipo_registro,
                    user_id,
                    paid_local_id,
                    caja_id                
                ) 
                VALUES(
                    '$data[created_at]',
                    '$data[local_id]',
                    '$data[tipo_doc]',
                    '$data[num_doc]',
                    '$data[ticket_id]',
                    '$data[autoriza]',
                    '$data[monto_apostado]',
                    '$data[monto_entregado]',
                    '$data[tipo_registro]',
                    '$data[session_cookie]',
                    '$data[paid_local_id]',
                    $turno 
                )
        ");
        $ultimoId = $mysqli->insert_id;
        print_r(json_encode(["lastID" => $ultimoId]));

        /*************************************************************************************
        Alerta por monto de más de 36 000 soles
         *************************************************************************************
        */
        if ($data['monto_entregado']>36000) {
            $body = "";
            $body .= '<table border="1" width="700px" cellpadding="5" cellspacing="0" style="font-family: arial">';
            $body .= '<thead>';
            $body .= '<tr>';
            $body .= '<th colspan="2" style="background-color: #F7DE10; vertical-align: middle; font-size: 16px">';
            $body .= 'TICKET GANADOR MAYOR A S/. 36,000.00';
            $body .= '</th>';
            $body .= '</tr>';
            $body .= '</thead>';
            $body .= '<tbody>';
            $body .= '<tr>';
            $body .= '<td valign="top"><b>Ticket ID</b></td>';
            $body .= '<td valign="top">'.$data["ticket_id"].'</td>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td valign="top"><b>N° Documento</b></td>';
            $body .= '<td valign="top">'.$data['num_doc'].'</td>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td valign="top"><b>Nombre Cliente</b></td>';
            $body .= '<td valign="top">'.$data['nombre'].' '.$data['apellido'].'</td>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td valign="top"><b>Ocupación</b></td>';
            $body .= '<td valign="top">'.$data['clientProfession'].'</td>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td valign="top"><b>Local de Apostado (CC)</b></td>';
            $body .= '<td valign="top">'.$data['nmbLocal'].'</td>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td valign="top"><b>Local de Cobro (CC)</b></td>';
            $body .= '<td valign="top">'.$data['paidLocalName'].'</td>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td valign="top"><b>Apostado</b></td>';
            $body .= '<td valign="top">'.$data['monto_apostado'].'</td>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td valign="top"><b>Ganado</b></td>';
            $body .= '<td valign="top">'.$data['monto_entregado'].'</td>';
            $body .= '</tr>';
            $body .= '<tr>';
            $body .= '<td valign="top"><b>Fecha de cobro</b></td>';
            $body .= '<td valign="top">'.$data['created_at'].'</td>';
            $body .= '</tr>';
            $body .= '</tbody>';
            $body .= '</table>';

            // Buscamos los correos de los supervisores del local de cobro
            $sql_busca_supervisores = "
            SELECT DISTINCT
                papt.correo
            FROM tbl_usuarios u
            INNER JOIN tbl_personal_apt papt ON u.personal_id = papt.id
            INNER JOIN tbl_usuarios_locales uloc ON uloc.usuario_id = u.id AND uloc.estado = 1
            INNER JOIN tbl_locales lo ON lo.id = uloc.local_id
            WHERE
                papt.area_id = 21
                AND papt.cargo_id = 4
                AND papt.estado = 1
                AND u.estado = 1
                AND lo.cc_id = ".$data['paidLocalName']."
            ";
            $supervisores_array_result = $mysqli->query($sql_busca_supervisores);
            $cc = array();
            while ($l = $supervisores_array_result->fetch_assoc()){
                $cc[] = $l['correo'];
            }
            array_push($cc, 'david.sanchez@testtest.apuestatotal.com', 'ATescucha@testtest.apuestatotal.com');

            // se envia email
            $request = [
                "subject" => "ALERTA PREMIO COBRADO MAYOR A 36 MIL SOLES ".date("Y-m-d H:i:s"),
                "body" => $body,
                "cc" => $cc,
                "bcc" => [
                ]
            ];
            sendEmail_rg($request);
        }
    }
}

//**************************************************************************************************************************** SORTEO 2
if (isset($_POST["reg_ticket_sorteo"])) {
    $data = $_POST['reg_ticket_sorteo'];

    if (!array_key_exists($menu_id, $usuario_permisos) && !in_array("generate", $usuario_permisos[$menu_id])) {
        die(action_response('403', 'No Autorizado. No tienes permisos para buscar DNIs en sistema.'));
    }

    check_or_create_client($data, $mysqli);

    $local_id = is_numeric($data["local_id"]) ? $data["local_id"] : 0;
    $real_local_id = get_real_local_id($local_id, $mysqli);

    $turno = get_turno_id($data["session_cookie"] ,$data["paid_local_id"],$mysqli);
    $tipo_codigo = (trim($data["premio_tipo_codigo"]) != "") ? "'". $data["premio_tipo_codigo"] . "'" : 'null';

    if($data["premio_tipo_codigo"] == "" && $data["tipo_registro"] == 3){
        $result["code"] = 400;
        $result["message"] = "Dar CTRL + F5 y pruebe nuevamente.";
        echo json_encode($result); exit();
    }

    $consulta_exist = "
        SELECT 
            id
        FROM 
            tbl_registro_premios
        WHERE
            ticket_id = '" . $data["ticket_id"] . "'
            AND num_doc = '" . $data["num_doc"] . "'
            AND tipo_registro = 3
            AND DATE(created_at) = DATE(now())
    ";
    $list_consult = [];
    $result_exists = $mysqli->query($consulta_exist);
    if ($mysqli->error) {
        $result["code"] = 400;
    }
    while ($r = $result_exists->fetch_assoc()) {
        $list_consult[] = $r;
    }

    if(count($list_consult) > 0){
        $result["code"] = 400;
        $result["message"] = "Registro ya existe.";
        echo json_encode($result); exit();
    }

    $query = "
        INSERT INTO
            tbl_registro_premios (
                created_at,
                local_id,
                tipo_doc,
                num_doc,
                ticket_id,
                autoriza,
                monto_apostado,
                monto_entregado,
                tipo_registro,
                user_id,
                paid_local_id,
                caja_id,
                tipo_codigo
            )
            VALUES(
                '$data[created_at]',
                '$real_local_id',
                '$data[tipo_doc]',
                '$data[num_doc]',
                '$data[ticket_id]',
                '$data[autoriza]',
                '$data[monto_apostado]',
                '$data[monto_entregado]',
                '$data[tipo_registro]',
                '$data[session_cookie]',
                '$data[paid_local_id]',
                $turno,
                $tipo_codigo
            )
    ";
    $result_nums = $mysqli->query($query);
    if ($mysqli->error) {
        $result["code"] = 400;
        $result["insert_tbl_registro_premios"] = $mysqli->error;
        echo json_encode($result);
    }else{
        $result["code"] = 200;
    }
    print_r(json_encode(["result" => $result_nums]));
    return $result_nums;
}


if (isset($_POST['reg_ticket_jackpot_ex'])) {
    $data = $_POST['reg_ticket_jackpot_ex'];
    //print_r($data);
    // die;

    if (!array_key_exists($menu_id, $usuario_permisos) && !in_array("generate", $usuario_permisos[$menu_id])) {
        die(action_response('403', 'No Autorizado. No tienes permisos para buscar DNIs en sistema.'));
    }

    $doc = $data['num_doc'];

    $tipodoc = $data['tipo_doc'];

    $result = $mysqli->query("
					SELECT
					num_doc
					FROM tbl_cliente_extranjero
					WHERE
					num_doc = '$doc' AND tipo_doc='$tipodoc';
					");

    while ($r = $result->fetch_assoc()) $consulta = $r;

    if (isset($consulta)) {

        try {

            if (($data["local_id"] != "00") && ($data["local_id"] != "") && ($data["created_at"] != "") && ($data["num_doc"] != "") && ($data["ticket_id"] != "") && ($data["autoriza"] != "") &&
                ($data["monto_entregado"] != "") && ($data["session_cookie"] != "")) {

                $turno = get_turno_id($data["session_cookie"] ,$data["paid_local_id"],$mysqli);

                $respSql = $mysqli->query("
                    INSERT INTO 
                        tbl_registro_premios (
                            local_id,
                            created_at,
                            num_doc,
                            tipo_doc,
                            ticket_id,
                            autoriza,
                            monto_apostado,
                            monto_entregado,
                            tipo_registro,
                            user_id,
                            paid_local_id,
                            caja_id
                        ) 
                        VALUES(
                            '$data[local_id]',
                            '$data[created_at]',
                            '$data[num_doc]',
                            '$data[tipo_doc]',
                            '$data[ticket_id]',
                            '$data[autoriza]',
                            '$data[monto_apostado]',
                            '$data[monto_entregado]',
                            '$data[tipo_registro]',
                            '$data[session_cookie]',
                            '$data[paid_local_id]',
                            $turno				
                        )
                ");

                $lastID = $mysqli->insert_id;

                if (!$respSql) {
                    throw new Exception('Error al registar en BD');
                } else {
                    // die(print_r(json_encode(["code" => "201", "message"=> "Se Registro Exitosamente!", "lastID" => $lastID])));
                    print_r(json_encode(['code' => '201', 'message' => 'Exito..', 'lastID' => $lastID]));
                    die;
                }

            } else {
                throw new Exception('No se admiten campos vacios');
            }


        } catch (Exception $e) {
            die(action_response('500', $e->getMessage()));
        }


    } else {

        //$tipodoc = $data['tipo_doc'];
        //($tipodoc == 'CE_PTP')?$tipo=1:$tipo=2;

        try {
            if (($data['apePat'] != "") && ($data['nombres'] != "") && ($data['num_doc'] != "") && ($data['local_id'] != "00") && ($data['local_id'] != "") &&
                ($data["created_at"] != "") && ($data["num_doc"] != "") && ($data["ticket_id"] != "") && ($data["autoriza"] != "") && ($data["monto_entregado"] != "") && ($data["session_cookie"] != "")) {

                $turno = get_turno_id($data["session_cookie"] ,$data["paid_local_id"],$mysqli);
                $mysqli->query("
										INSERT INTO tbl_cliente_extranjero (
											num_doc,
											tipo_doc,
											nombres,
											apellido_paterno,
											apellido_materno
											) VALUES(
												'" . $data["num_doc"] . "',
												'" . $data["tipo_doc"] . "',
												'" . $data["nombres"] . "',
												'" . $data["apePat"] . "',
												'" . $data["apeMat"] . "'
												)
												");


                $mysqli->query("
												INSERT INTO tbl_registro_premios (
													created_at,
													local_id,
													tipo_doc,
													num_doc,
													ticket_id,
													autoriza,
													monto_apostado,
													monto_entregado,
													tipo_registro,
													user_id,
                                                    paid_local_id,
                                                    caja_id
													) VALUES(
														'$data[created_at]',
														'$data[local_id]',
														'$data[tipo_doc]',
														'$data[num_doc]',
														'$data[ticket_id]',
														'$data[autoriza]',
														'$data[monto_apostado]',
														'$data[monto_entregado]',
														'$data[tipo_registro]',
														'$data[session_cookie]',
														'$data[paid_local_id]',
                                                        $turno
														)
														");

                print_r(json_encode(['code' => '201', 'message' => 'Se Registro Exitosamente!', 'lastID' => $mysqli->insert_id]));
                die;

            } else {
                throw new Exception('No se admiten campos vacios');
            }
        } catch (Exception $e) {
            die(action_response('500', $e->getMessage()));
        }

    }

}

if (isset($_POST["reg_client_data"])) {
    if (!array_key_exists($menu_id, $usuario_permisos) && !in_array("generate", $usuario_permisos[$menu_id])) {
        die(action_response('403', 'No Autorizado. No tienes permisos para buscar DNIs en sistema.'));
    }

    $num_doc = $_POST["reg_client_data"]["num_doc"];
    $tipo_doc = $_POST["reg_client_data"]["tipo_doc"];

    // tipo_data: 1 => phone, 2 => email, 4 => profession
    $client_data = [
        "1" => [
            "data" => $_POST["reg_client_data"]["phone"],
            "id" => null
        ],
        "2" => [
            "data" => $_POST["reg_client_data"]["email"],
            "id" => null,
        ],
        "4" => [
            "data" => $_POST["reg_client_data"]["clientProfession"],
            "id" => null
        ]
    ];

    $phone = $_POST["reg_client_data"]["phone"];
    $email = $_POST["reg_client_data"]["email"];
    $client_profession = $_POST["reg_client_data"]["clientProfession"];


    $result = $mysqli->query("
		SELECT
            id,
            data,
            tipo_data        
		FROM 
		     tbl_client_data
		WHERE
		    num_doc = '$num_doc' AND tipo_doc='$tipo_doc' AND status = 1 AND tipo_data IN (1,2,4);
		");

    try {
        while ($r = $result->fetch_assoc()) {
            foreach ($client_data as $key => $data){
                if ($key == $r["tipo_data"]) {
                    $client_data[$key]["id"] = $r["id"];
                }
            }
        }

        foreach ($client_data as $key => $data) {
            if (ctype_space($data["data"]) || $data["data"] === "") continue;
            $data_id = $data["id"] === null ? 'null' : $data["id"];
            $query = "INSERT INTO tbl_client_data (id, tipo_doc, num_doc, tipo_data, data, status, created_at, updated_at )
                        VALUES($data_id, '$tipo_doc', '$num_doc', '$key', '$data[data]', '1', NOW(), NOW())
                        ON DUPLICATE KEY UPDATE data = '$data[data]', updated_at = NOW()";
            $result = $mysqli->query($query);

        }
        die(action_response('201', 'Se Registró Exitosamente!'));
    } catch (Exception $e) {
        var_dump($e->getMessage());
    }

}


if (isset($_POST["search_ticket_jackpot"])) {
    $data = $_POST['search_ticket_jackpot']['idTicket'];

    if (!array_key_exists($menu_id, $usuario_permisos) && !in_array("generate", $usuario_permisos[$menu_id])) {
        die(action_response('403', 'No Autorizado. No tienes permisos para buscar DNIs en sistema.'));
    }
   
    if (preg_match("/^[0-9]{1,9}$/", $data)) {
    //if (ctype_digit($data)) {

        $accessToken = env('SOPORTE_V2_TOKEN');
        get_ticket_jackpot_tickedId($data, $accessToken);

        $query = "
			SELECT
			r.ticket_id,
			r.stake_amount,
			IFNULL(r.jackpot, 0) jackpot,
			IFNULL(r.local_id, 0) local_id,
			IFNULL(l.cc_id, '') cc_id,
			IFNULL(l.nombre, '') nombre,
            r.time_played as time_played
			FROM tbl_repositorio_tickets_goldenrace r 
            LEFT JOIN tbl_locales l ON r.local_id = l.id
			WHERE
            r.version='2'
			and r.ticket_id = '$data';
			";

        $result = $mysqli->query($query);

        while ($r = $result->fetch_assoc()) $consulta = $r;

        //echo var_dump($consulta);
        //echo "\n";
        /*
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        */

        if (isset($consulta)) {
            if (floatval($consulta['jackpot']) > 0) {
                $repear = valida_repeat_goldenrace($data, $consulta['time_played']);

                if (is_null($consulta['nombre'])) {
                    echo die(action_response('214', 'El ticket no tiene un local válido'));
                }

                if ($repear > 0) {
                    echo die(action_response('211', 'El ticket ya se ha registrado.'));
                } else {
                    echo die(action_response('210', $consulta));
                }
                echo die(action_response('210', $consulta));

            } else {
                echo die(action_response('213', 'El nro del ticket no corresponde a un jackpot'));
            }
        } else {

            echo die(action_response('215', 'Ticket no encontrado.'));

            /*
            get_ticket_jackpot_tickedId($data, $mysqli);
            //exec("php /var/www/html/cron/grtickets/gr_api.php");
            //echo (action_response('211', 'Ticket no encontrado.')); codigo de error.
            $result2 = $mysqli->query($query);

            while ($r = $result2->fetch_assoc()) $consulta2 = $r;

            //var_dump($consulta2);

            if (isset($consulta2)) {

                if (floatval($consulta2['jackpot']) > 0) {
                    $repear = valida_repeat($data);

                    if (is_null($consulta2['nombre'])) {
                        echo die(action_response('214-2', 'El ticket no tiene un local válido'));
                    }

                    if ($repear > 0) {
                        echo die(action_response('211-2', 'El ticket ya se ha registrado.'));
                    } else {
                        echo die(action_response('210-2', $consulta2));
                    }
                    echo die(action_response('210-2', $consulta2));

                } else {
                    echo die(action_response('213-2', 'El nro del ticket no corresponde a un jackpot'));
                }
            } else {
                echo die(action_response('211-2', 'Ticket no encontrado.'));
            }
            */
        }

    } else {
        echo die(action_response('212', 'Error en nro de ticket (max. 9).'));
    }

}


if (isset($_POST["search_ticket_teleservicios"])) {
    $data = $_POST['search_ticket_teleservicios']['idTicket'];

    if (!array_key_exists($menu_id, $usuario_permisos) && !in_array("generate", $usuario_permisos[$menu_id])) {
        die(action_response('403', 'No Autorizado. No tienes permisos para buscar DNIs en sistema.'));
    }
   
    //if (ctype_digit($data)) {

    $curl = curl_init();

    curl_setopt_array($curl, array(
      CURLOPT_URL => 'https://api.apuestatotal.com/v2/betconstruct/getBetsTelservicios',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>'{
        "BetId": '.$data.'
    }',
      CURLOPT_HTTPHEADER => array(
        'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiNWVlZDRhZGFhZTM4YmM0NzNmZWJiYjBlNGYxOGY1NTE3MGUxNDBjMDNhNWZlMmVmMWEyYTQyZmQzOTUyZDBmMDcwNTg5YjgxMjc0NWQxMjIiLCJpYXQiOjE2MzMxMzAxODQsIm5iZiI6MTYzMzEzMDE4NCwiZXhwIjoxNzI3ODI0NTg0LCJzdWIiOiIxNSIsInNjb3BlcyI6W119.Gv7iyWqKLRibV_l7z3YwDli0B44BO4OlYL37-2gE8rcagFmaIVBeu_QW4s_-iaAXj4NmDrBR1d7SghfViO93dRInjUedVG2oaQ24VC3O1V5u9bhMIJv7wl2PMx_bQJbdY-7PHSQOR948Mfhrr7IIGazytXNh2xPmwRzxqXjgUtx8ngSXJgX2hPhKDdNXAUnBsWXol2zWoiS9FztEqHS-n1PKFqoh4MowH5zrnD6DvedpmI8TVC-LYJzG0tpBARI7sDQIkDZrjQZ9TD_eJIn2-hY4JSeXbJg40AvpURizuM5a9pHMylUfmSCbcoq3yxe-M3FUn-0Ygv6iuR8vW1kA2iZ7QL-SF_ewRGHxPQMMV_ze3rVrzpIORxPcYIROrztpFbc_goB6Wz_gzapG1Na4JN1p4TCBir09ohMLcKyjrXGFvx6spN_SfQihouW2e-axZY3XJxsrh9SLRi3AGkZ4nDg2vfcxuvYNcW5eQAO10_1QlXu1TJPVNra1Xky7G7cww8jsvnbQPxc1d6seaoYOOM-oZh_NtJMrdfGq0JzhcRnjNIRTJqMuUMnRZEPdtXG3kEXGMOPq26yNXrzrUvP1xHH_ZH1gWEyHLx7Dgk8Xuo4xbHYmbbAKmfiQ4oN7gy0iMDR0S26Kh-6k5pCGJl4aTHvMshYzTAG8cJ5ldHK2G6U',
        'Content-Type: application/json'
      ),
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    $response_arr = json_decode($response, true);
    //echo $response;
    echo die(action_response('210', $response_arr["result"][0]));

}

if (isset($_POST["search_ticket_torito"])) {
    $data = $_POST['search_ticket_torito']['idTicket'];
    if (!array_key_exists($menu_id, $usuario_permisos) && !in_array("generate", $usuario_permisos[$menu_id])) {
        die(action_response('403', 'No Autorizado.'));
    }
    if (!empty($data)) {
        $query = "
                    SELECT
                    tt.transactionid as ticket_id,
                    tt.amount,
                    l.id as local_id,
                    l.nombre as nombre,
                    l.cc_id as cc_id
                    FROM tbl_torito_transaccion tt
                    LEFT JOIN tbl_locales l on l.cc_id = tt.cc_id
                    WHERE tt.transactionid = '{$data}'
                ";
        $result = $mysqli->query($query);

        while ($r = $result->fetch_assoc()) $consulta = $r;
        if (isset($consulta)) {
            $repear = valida_repeat($data);
            if (is_null($consulta['nombre'])) {
                echo die(action_response('214', 'El ticket no tiene un local válido'));
            }
            if ($repear > 0) {
                echo die(action_response('211', 'El ticket ya se ha registrado.'));
            } else {
                echo die(action_response('210', $consulta));
            }
        } else {
            echo die(action_response('212', 'Error en nro de ticket.'));
        }
    } else {
        echo die(action_response('212', 'Nro de ticket vacio.'));
    }
}

if (isset($_POST["search_ticket_bingo"])) {
    $data = $_POST['search_ticket_bingo']['idTicket'];

    if (!array_key_exists($menu_id, $usuario_permisos) && !in_array("generate", $usuario_permisos[$menu_id])) {
        die(action_response('403', 'No Autorizado.'));
    }

    if (!empty($data)) {

        $query = "
					SELECT
					b.ticket_id,
					b.sell_local_id,
					b.paid_local_id,
					b.amount,
					b.winning,
					b.created,
					l.id AS local_id,
					IF(l.cc_id IS NULL,' ',l.cc_id) AS cc_id,
					l.nombre
					FROM tbl_repositorio_bingo_tickets b LEFT JOIN tbl_locales l ON l.cc_id = b.sell_local_id
					WHERE
					b.ticket_id = '$data'
					AND winning > 0;
				";

        $result = $mysqli->query($query);

        while ($r = $result->fetch_assoc()) $consulta = $r;

        if (isset($consulta)) {

            $repear = valida_repeat($data);

            if (is_null($consulta['nombre'])) {
                echo die(action_response('214', 'El ticket no tiene un local válido'));
            }

            if ($repear > 0) {
                echo die(action_response('211', 'El ticket ya se ha registrado.'));
            } else {
                echo die(action_response('210', $consulta));
            }
        } else {
            echo die(action_response('212', 'Error en nro de ticket.'));
        }
    } else {
        echo die(action_response('212', 'Nro de ticket vacio.'));
    }
}


if (isset($_POST["search_ticket_mayor"])) {

    $idTicket = $_POST['search_ticket_mayor']['idTicket'];
    $data = $idTicket;
    if (!array_key_exists($menu_id, $usuario_permisos) && !in_array("generate", $usuario_permisos[$menu_id])) {
        die(action_response('403', 'No Autorizado.'));
    }
    if (!empty($data)) {
        /*$query = "
					SELECT
					b.ticket_id,
					b.apostado AS amount,
					b.ganado AS winning,
					b.created,
					l.id AS local_id,
					IF(l.cc_id IS NULL,' ',l.cc_id) AS cc_id,
					l.nombre
					FROM tbl_transacciones_detalle b LEFT JOIN tbl_locales l ON l.id = b.local_id
					WHERE
					b.ticket_id = '$data'
					AND b.servicio_id = 1
					AND ticket_id NOT LIKE '%pm_%'
					AND NOT local_id = 1
					AND b.ganado > 0;
				";

        $result = $mysqli->query($query);

        while ($r = $result->fetch_assoc()) $consulta = $r;

        if (isset($consulta)) {

            $repear = valida_repeat($data);

            if (is_null($consulta['nombre'])) {
                echo die(action_response('214', 'El ticket no tiene un local válido'));
            }

            if ($repear > 0) {
                echo die(action_response('211', 'El ticket ya se ha registrado.'));
            } else {
                echo die(action_response('210', $consulta));
            }
        }*/

        $curl = curl_init();
        $accessToken = env('SOPORTE_V2_TOKEN');
        $accessCookie = env('COOKIE_SOPORTE_V2');

        curl_setopt_array($curl, [
            CURLOPT_URL => "https://api.apuestatotal.com/v2/betconstruct/getBets",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode(['BetId' => $data]),
            CURLOPT_HTTPHEADER => [
                "Accept: application/json",
                "Authorization: Bearer ".$accessToken,
                "Content-Type: application/json",
                "Cookie: ".$accessCookie
            ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        $repear = valida_repeat($data);
        if ($repear > 0) {
            echo die(action_response('211', 'El ticket ya se ha registrado.'));
        }

        if ($err) {
            echo die(action_response('212', 'Error en nro de ticket.'));
        } else {
            $consulta = json_decode($response, true);
            if ($consulta['http_code'] === 200) {
                $ticket_data = $consulta["result"][0];
                $ticket_data['nombre'] = $ticket_data['local_name'];
                if (is_null($ticket_data['nombre'])) {
                    echo die(action_response('214', 'El ticket no tiene un local válido'));
                }
                echo die(action_response('210', $ticket_data));
            } else {
                echo die(action_response('212', 'Error en nro de ticket.'));
            }
        }

    } else {
        echo die(action_response('212', 'Nro de ticket vacio.'));
    }

}


if (isset($_POST["re_print_ticket"])) {
    $data = $_POST['re_print_ticket'];
    $consulta = [];
    $cliente = "";
    if (!array_key_exists($menu_id, $usuario_permisos) && !in_array("generate", $usuario_permisos[$menu_id])) {
        die(action_response('403', 'No Autorizado.'));
    }


    if ($data['tipodoc'] == 0) {
        $cliente = "INNER JOIN tbl_consultas_dni c ON c.dni = p.num_doc";
    } else {
        $cliente = "INNER JOIN tbl_cliente_extranjero c ON c.num_doc = p.num_doc";
    }

    if (!empty($data)) {
        $query = "SELECT
					p.id,
					p.ticket_id,
					p.created_at,
					CONCAT ('[', IF(l.cc_id IS NULL,' ',l.cc_id) ,'] ',l.nombre) AS local,
					p.num_doc,
					c.nombres AS nombre_cliente,
					CONCAT (c.apellido_paterno,' ',c.apellido_materno) AS apellidos_cliente,
					p.monto_entregado,
					p.autoriza,
                    p.tipo_registro,
                    pt.producto AS type
					FROM
					tbl_registro_premios p
					LEFT JOIN tbl_locales l ON p.paid_local_id = l.id
                    LEFT JOIN tbl_registro_premios_tipos pt ON p.tipo_codigo = pt.codigo
					" . $cliente . "
					WHERE
					p.id=" . $data['id'] . " ";


        $result = $mysqli->query($query);
        while ($r = $result->fetch_assoc()) $consulta[] = $r;
        if (isset($consulta)) {
            echo die(action_response('001', $consulta));
        } else {
            echo die(action_response('002', 'error.'));
        }
    }

}


if (isset($_POST["valida_firma"])) {
    $consulta = '';
    $idTicket = $_POST['valida_firma']['id'];

    if (isset($idTicket)) {
        $query = "
                    SELECT archivo from tbl_archivos 
                        WHERE item_id = " . $idTicket . "  
                        AND tabla ='tbl_registro_premios' 
                        AND tipo='signature'
                    ";
        $result = $mysqli->query($query);
        while ($r = $result->fetch_assoc()) $consulta = $r;

        if (isset($consulta['archivo'])) {
            echo die(action_response('001', $consulta['archivo']));
        } else {
            echo die(action_response('002', 'error.'));
        }
    }
}

//******************************************************************************************************* SORTEO 1
if (isset($_POST["search_sorteo_winner"])) {
    // include("function_replace_invalid_caracters.php");
    if (!array_key_exists($menu_id, $usuario_permisos) && !in_array("generate", $usuario_permisos[$menu_id])) {
        die(action_response('403', 'No Autorizado.'));
    }

    $data = $_POST['search_sorteo_winner'];
    $env_data = get_env_data($data["domain"]);
    $tipo_doc = get_tipo_doc($data["tipoDoc"]);

    $post_fields = [
        "document_type" => $tipo_doc,
        "document_number" => $data["numDoc"]
    ];

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "$env_data[domain]/api/check_winner",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer $env_data[token]"
        ),
        CURLOPT_POSTFIELDS => $post_fields
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    echo die(action_response('200', $response));
}


if (isset($_POST["search_sorteo_winner_teleservicios"])) {
    if (!array_key_exists($menu_id, $usuario_permisos) && !in_array("generate", $usuario_permisos[$menu_id])) {
        die(action_response('403', 'No Autorizado.'));
    }

    $data = $_POST['search_sorteo_winner_teleservicios'];
    $env_data = get_env_data($data["domain"]);
    $tipo_doc = get_tipo_doc($data["tipoDoc"]);

    $post_fields = [
        "document_type" => $tipo_doc,
        "document_number" => $data["numDoc"]
    ];

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "$env_data[domain]/api/check_winner/fidelidad",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_HTTPHEADER => array(
            "Authorization: Bearer $env_data[token]"
        ),
        CURLOPT_POSTFIELDS => $post_fields
    ));

    $response = curl_exec($curl);

    curl_close($curl);
    echo die(action_response('200', $response));
}


if (isset($_POST["send_registration_teleservicios"])) {
    if (!array_key_exists($menu_id, $usuario_permisos) && !in_array("generate", $usuario_permisos[$menu_id])) {
        die(action_response('403', 'No Autorizado.'));
    }

    $usuario_id = $login ? $login['id'] : 0;

    $data = $_POST['send_registration_teleservicios'];
    $env_data = get_env_data($data["domain"]);
    $id = $data["winner_id"];
    $ticket_id = $data["ticket_id"];
    $monto_ticket = $data["monto_ticket"];
    $idlocal_ticket = $data["idlocal_ticket"];
    $tipo_doc = $data["tipo_doc"];
    $num_doc = $data["num_doc"];
    $session_cookie = $data["session_cookie"];


    $curl = curl_init();

    curl_setopt_array($curl, array(
    CURLOPT_URL => "$env_data[domain]/api/check_winner/fidelidad/paid_at",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'PUT',
    CURLOPT_POSTFIELDS => "{\"id\": \"$id\" }",
    CURLOPT_HTTPHEADER => array(
        'Content-Type: application/json',
        'Accept: application/json',
        "Authorization: Bearer $env_data[token]"
    )
    ));

    $response = curl_exec($curl);

    curl_close($curl);

    $response_arr = json_decode($response, true);

    /*
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    */
    if((int)$response_arr["http_code"]==200){
        $mysqli->query("
            INSERT INTO tbl_registro_premios_freebet (
                    ticket_id,
                    created_user_id,
                    created_at              
                ) 
                VALUES(
                    '$ticket_id',
                    '$usuario_id',
                    now()
                )
        ");
        if ($mysqli->error) {
            echo $mysqli->error;
        }
        $respSql = $mysqli->query("
            INSERT INTO tbl_registro_premios (
                local_id,
                created_at,
                num_doc,
                tipo_doc,
                ticket_id,
                autoriza,
                monto_apostado,
                monto_entregado,
                tipo_registro,
                user_id,
                paid_local_id,
                caja_id
            ) VALUES(
                '$idlocal_ticket',
                now(),
                '$num_doc',
                '$tipo_doc',
                '',
                '0',
                '$monto_ticket',
                '$monto_ticket',
                '7',
                '$session_cookie',
                '$idlocal_ticket',
                0              
            )
        ");
        $lastID = $mysqli->insert_id;

        if ($mysqli->error) {
            echo $mysqli->error;
        }
        if (!$respSql) {
            throw new Exception('Error al registar en BD');
        } else {
            echo json_encode(['code' => '201', 'message' => 'Exito..', 'lastID' => $lastID]);
            die;
        }
        //echo die(action_response('200', $response_arr));
    } else {
        echo die(action_response('400', $response_arr));
    }
}

// *************************************************************************************** SORTEO 3
if (isset($_POST["send_registration"])) {
    include("function_replace_invalid_caracters.php");
    if (!array_key_exists($menu_id, $usuario_permisos) && !in_array("generate", $usuario_permisos[$menu_id])) {
        die(action_response('403', 'No Autorizado.'));
    }

    $data = $_POST['send_registration'];
    $env_data = get_env_data($data["domain"]);
    $id = htmlspecialchars($data["ticket_id"]);
    $regprem_validate_tls = $data["regprem_validate_tls"];

    $result = array();

    $usuario_id = $login ? $login['id'] : 0;
    if (!((int) $usuario_id > 0)) {
        $result["http_code"] = 400;
        $result["status"] = "Sesión perdida. Por favor vuelva a iniciar sesión.";
        echo json_encode($result);exit();
    }

    $turno = get_turno();
    if (!(count($turno) > 0)) {
        $result["http_code"] = 400;
        $result["status"] = "Debe abrir un turno.";
        $result["result"] = $turno;
        echo json_encode($result);exit();
    }
    $turno_id = $turno[0]["id"];

    $grupo_id = $login ? $login['grupo_id'] : 0;
    if( (int)$grupo_id===26 || (int)$grupo_id === 31 || (int)$regprem_validate_tls === 1 ) { // televentas-cajero y televentas-supervisor

        $client_tipo_doc = $data["tipo_doc"];
        $client_num_doc = $data["num_doc"];
        $where_tipo_doc = " = 0 ";
        if((int)$data["tipo_doc"]!==0){
            $where_tipo_doc = " != 0 ";
        }
        $client_id = 0;
        $result_client = $mysqli->query("
            SELECT
                c.id
            FROM tbl_televentas_clientes c 
            WHERE c.tipo_doc $where_tipo_doc 
            AND c.num_doc = '$client_num_doc' 
            LIMIT 1
            ");
        if ($mysqli->error) {
            $result["query_error"] = $mysqli->error;
            $result["http_code"] = 400;
            echo json_encode($result);exit();
        }
        while ($r_c = $result_client->fetch_assoc()) $rows_c = $r_c;
        if (isset($rows_c)) {
            $client_id = (int)$rows_c["id"];
        }
        if((int)$client_id===0){// REGISTRAMOS AL CLIENTE
            
            // Si es DNI usamos la API de DNI
            if ((int) $client_tipo_doc === 0) {
                $result_api_dni = get_cliente_por_dni($client_num_doc);
                if(isset($result_api_dni["dni"])){
                    if ((int) $result_api_dni["dni"] === (int) $client_num_doc) {
                        $query_insert_dni = "
                            INSERT INTO tbl_televentas_clientes (
                                    apellido_paterno,
                                    apellido_materno,
                                    nombre,
                                    tipo_doc,
                                    num_doc,
                                    cc_id,
                                    created_at,
                                    updated_at,
                                    block_user_id,
                                    bono_limite
                            ) VALUES (
                                    '" . strtoupper(replace_invalid_caracters(trim($result_api_dni["apellido_paterno"]))) . "',
                                    '" . strtoupper(replace_invalid_caracters(trim($result_api_dni["apellido_materno"]))) . "',
                                    '" . strtoupper(replace_invalid_caracters(trim($result_api_dni["nombres"]))) . "',
                                    '0',
                                    '" . $result_api_dni["dni"] . "',
                                    3900,
                                    now(),
                                    now(),
                                    '" . $usuario_id . "',
                                    '10000'
                            );
                            ";
                        $mysqli->query($query_insert_dni);
                        if ($mysqli->error) {
                            $result["query_insert_dni"] = $mysqli->error;
                            $result["http_code"] = 400;
                            echo json_encode($result);exit();
                        }
                    }
                }
            }
            if ((int) $client_tipo_doc !== 0 || !isset($query_insert_dni)) {
                $query_insert_otro_doc = "
                    INSERT INTO tbl_televentas_clientes (
                            tipo_doc,
                            num_doc,
                            cc_id,
                            created_at,
                            updated_at,
                            block_user_id,
                            bono_limite
                    ) VALUES (
                            '" . $client_tipo_doc . "',
                            '" . $client_num_doc . "',
                            3900,
                            now(),
                            now(),
                            '" . $usuario_id . "',
                            '10000'
                    );
                    ";
                $mysqli->query($query_insert_otro_doc);
                if ($mysqli->error) {
                    $result["query_insert_otro_doc"] = $mysqli->error;
                    $result["http_code"] = 400;
                    echo json_encode($result);exit();
                }
            }
            $result_client_2 = $mysqli->query("
                SELECT
                    c.id
                FROM tbl_televentas_clientes c 
                WHERE c.tipo_doc $where_tipo_doc 
                AND c.num_doc = '$client_num_doc' 
                LIMIT 1
            ");
            if ($mysqli->error) {
                $result["query_insert_otro_doc"] = $mysqli->error;
                $result["http_code"] = 400;
                echo json_encode($result);exit();
            }
            while ($r_c_2 = $result_client_2->fetch_assoc()) $rows_c_2 = $r_c_2;
            if (isset($rows_c_2)) {
                $client_id = (int)$rows_c_2["id"];
            }
        }
        if((int)$client_id>0){

            $con_sorteo_host = env('DB_SORTEOS_HOST');
            $con_sorteo_db   = env('DB_SORTEOS_DATABASE');
            $con_sorteo_user = env('DB_USERNAME');
            $con_sorteo_pass = env('DB_PASSWORD');
            $mysqli_sorteo   = new mysqli($con_sorteo_host, $con_sorteo_user, $con_sorteo_pass, $con_sorteo_db, 3306);
            if (mysqli_connect_errno()) {
                printf("Conexion fallida sorteos: %s\n", mysqli_connect_error());
                exit();
            }
            $mysqli_sorteo->query("SET CHARACTER SET utf8");

            $monto = 0;
            $result = $mysqli_sorteo->query("
                SELECT
                    amount
                FROM sorteos.event_winner ew
                WHERE ew.id = $id
            ");
            if ($mysqli_sorteo->error) {
                $result["query_insert_otro_doc"] = $mysqli_sorteo->error;
                $result["http_code"] = 400;
                echo json_encode($result);exit();
            }
            while ($r = $result->fetch_assoc()) $rows = $r;
            if (isset($rows)) {
                $monto = (double)$rows["amount"];
            }
            if(!($monto>0)){
                $result["http_code"] = 400;
                $result["status"] = "El monto ganado es cero.";
                $result["result"] = $turno;
                echo json_encode($result);exit();
            }

            // BALANCE BILLETERO
            $list_balance = array();
            $list_balance = obtener_balances($client_id);

            if(count($list_balance)>0){
                $balance_total_actual = (double)$list_balance[0]["balance"];
                $balance_detalle_actual = (double)$list_balance[0]["balance_retiro_disponible"];

                $balance_total_nuevo = $balance_total_actual + $monto;
                $balance_detalle_nuevo = $balance_detalle_actual + $monto;

                $tipo_balance = 5;

            } else {
                $result["http_code"] = 400;
                $result["status"] = "Ocurrió un error al consultar el balance.";
                echo json_encode($result);exit();
            }

            $fecha_hora = date('Y-m-d H:i:s');
            $insert_command = "
                INSERT INTO tbl_televentas_clientes_transaccion (
                    tipo_id,
                    api_id,
                    cliente_id,
                    turno_id,
                    txn_id,
                    monto,
                    nuevo_balance,
                    estado,
                    observacion_cajero,
                    user_id,
                    created_at
                ) VALUES (
                    32,
                    7,
                    " . $client_id . ",
                    " . $turno_id . ",
                    " . $id . ",
                    " . $monto . ",
                    " . $balance_total_nuevo . ",
                    1,
                    '',
                    " . $usuario_id . ",
                    '". $fecha_hora ."'
                )";
            $mysqli->query($insert_command);
            if ($mysqli->error) {
                $result["query_insert_otro_doc"] = $mysqli->error;
                $result["http_code"] = 400;
                echo json_encode($result);exit();
            }

            $query_3 = "
                SELECT id 
                FROM tbl_televentas_clientes_transaccion 
                WHERE tipo_id=32 
                AND cliente_id = '$client_id' 
                AND turno_id = '$turno_id' 
                AND user_id = '$usuario_id' 
                AND created_at = '$fecha_hora' 
                AND estado = 1
                ";
            $list_query = $mysqli->query($query_3);
            if ($mysqli->error) {
                $result["query_insert_otro_doc"] = $mysqli->error;
                $result["http_code"] = 400;
                echo json_encode($result);exit();
            }
            $list_transaccion2 = array();
            while ($li2 = $list_query->fetch_assoc()) {
                $list_transaccion2[] = $li2;
            }
            if (count($list_transaccion2) === 1) {
                $transaccion_id = $list_transaccion2[0]["id"];

                query_tbl_televentas_clientes_balance('update', $client_id, 1, $balance_total_nuevo);
                query_tbl_televentas_clientes_balance('update', $client_id, $tipo_balance, $balance_detalle_nuevo);

                query_tbl_televentas_clientes_balance_transaccion('insert', $transaccion_id, 
                    $client_id, 1, $balance_total_actual, $monto, $balance_total_nuevo);
                query_tbl_televentas_clientes_balance_transaccion('insert', $transaccion_id, 
                    $client_id, $tipo_balance, $balance_detalle_actual, $monto, $balance_detalle_nuevo);

            } else {
                $result["http_code"] = 400;
                $result["status"] = "Ocurrió un error al editar el balance.";
                echo json_encode($result);exit();
            }
        } else {
            $result["http_code"] = 400;
            $result["status"] = "No se encontro al cliente en TLS.";
            $result["result"] = $turno;
            echo json_encode($result);exit();
        }
    }

    $response = "";
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "$env_data[domain]/api/check_winner/paid_at",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'PUT',
        CURLOPT_POSTFIELDS => "{\"id\": \"$id\" }",
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Accept: application/json',
            "Authorization: Bearer $env_data[token]"
        )));
    $response = curl_exec($curl);
    curl_close($curl);
    echo die(action_response('200', $response));
}

if (isset($_POST["update_turno"])) {
    $data = $_POST['update_turno'];
    $register_ids = $data["ids"];
    $caja_id = $data["caja_id"];
    global $mysqli;

    foreach ($register_ids as $id){
        $mysqli->query("UPDATE tbl_registro_premios rp SET rp.caja_id = $caja_id WHERE rp.id = $id;");
    }
}


function get_tipo_doc($tipo): string
{
    switch ($tipo) {
        case 1:
            return "cex";
        case 2:
            return "pas";
        default:
            return "dni";
    }
}

function get_env_data($domain): array
{
    $sorteo_data = sorteo_variables();
    $data = [];
    if ($domain === "http://localhost" || $domain === "https://gestion.apuestatotal.dev") {
        $data["domain"] = "https://sorteos.kurax.dev";
        $data["token"] = $sorteo_data["TOKEN_SORTEO_DEV"];
        $data["sorteo_id"] = $sorteo_data["SORTEO_ID_DEV"];
    } else {
        $data["domain"] = "https://sorteos.apuestatotal.com";
        $data["token"] = $sorteo_data["TOKEN_SORTEO_PROD"];
        $data["sorteo_id"] = $sorteo_data["SORTEO_ID_PROD"];
    }
    return $data;
}

function check_or_create_client($data, $mysqli)
{
    $doc = $data['num_doc'];
    $tipodoc = $data['tipo_doc'];
    $nombres = strtoupper($data["nombres"]);
    $apellido_paterno = strtoupper($data["apellido_paterno"]);
    $apellido_materno = strtoupper($data["apellido_materno"]);

    if ($tipodoc > 0) {
        $result = $mysqli->query("SELECT
					num_doc 
					FROM tbl_cliente_extranjero
					WHERE
					num_doc = '$doc' AND tipo_doc ='$tipodoc';
					");
        while ($r = $result->fetch_assoc()) $consulta = $r;
        if (!isset($consulta)) {
            $mysqli->query
            ("
                INSERT INTO 
                    tbl_cliente_extranjero (
                        num_doc,
                        tipo_doc,
                        nombres,
                        apellido_paterno,
                        apellido_materno
                    ) 
                VALUES(
                    '$data[num_doc]',
                    '$data[tipo_doc]',
                    '$nombres',
                    '$apellido_paterno',
                    '$apellido_materno'
                    )
            ");
        }
    } else {
        $result = $mysqli->query("
					SELECT
					dni
					FROM tbl_consultas_dni
					WHERE
					dni = '$doc';
					");

        while ($r = $result->fetch_assoc()) $consulta = $r;
        if (!isset($consulta)) {
            $mysqli->query
            ("
                INSERT INTO 
                    tbl_consultas_dni (
                        dni,
                        nombres,                        
                        apellido_paterno,
                        apellido_materno,
                        created_at,
                        caracter_verificacion,
                        caracter_verificacion_anterior
                    ) 
                VALUES(
                    '$data[num_doc]',                    
                    '$nombres',
                    '$apellido_paterno',
                    '$apellido_materno',
                    now(),
                    ' ',
                    ' '
                    )
            ");
        }
    }
}

function get_real_local_id($local_id, $mysqli): int
{
    $result = $mysqli->query("
					Select 
					       id 
					from 
					     tbl_locales 
					where 
					      cc_id = $local_id 
					LIMIT 1;;
					");

    while ($r = $result->fetch_assoc()) $consulta = $r;
    if (isset($consulta)) {
        return $consulta["id"];
    }
    return 0;
}

function get_turno_id($user_id, $local_id, $mysqli): int
{
    $result = $mysqli->query("
        SELECT
            c.id as caja_id
        FROM
            tbl_caja AS c
        LEFT JOIN
            tbl_local_cajas AS lc
            ON c.local_caja_id = lc.id
        WHERE
            c.estado= 0
            AND c.usuario_id = $user_id
            AND lc.local_id = $local_id
        LIMIT 1 
    ");

    while ($r = $result->fetch_assoc()) $rows = $r;
    if (isset($rows)) {
        return $rows["caja_id"];
    }
    return 0;
}

function get_ticket_jackpot_tickedId($ticket_id, $accessToken): int
{

    //echo ($ticket_id);

    $url = "https://api.apuestatotal.com/v2/golden/curl";
    $rq = ['BetId' => $ticket_id];
    $request_headers = array();
    $request_headers[] = "Content-type: application/json";
    $request_headers[] = "Authorization:Bearer " . $accessToken;
    $request_json = json_encode($rq);
    $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request_json);
    $response = curl_exec($curl);
    $err = curl_error($curl);
    $response_arr = json_decode($response, true);
    curl_close($curl);

    //echo var_dump($response);

    if ($err) {
        $result["http_code"] = 400;
        $result["status"] = "Ocurrió un error al consumir el API.";
        $result["result"] = $response;
        $result["error"] = "cURL Error #:" . $err;
        return $result;
    } else {
        if(isset($response_arr["http_code"])){
            if((int)$response_arr["http_code"]===200){
                return 1;
            }
        }
    }

    return 0;
}

function sendEmail_rg($request){
	try{
		$mail = new PHPMailer(true);
		$mail->IsSMTP();
		$mail->CharSet 		= 'utf-8';
		$mail->SMTPDebug	= 1;
		$mail->SMTPAuth   	= true;
		$mail->Host       	= "smtp.gmail.com";
		$mail->Port       	= 465;
		$mail->SMTPSecure 	= "ssl";

		//$mail->AddAddress('david.sanchez@testtest.apuestatotal.com');
        //$mail->AddAddress('ATescucha@testtest.apuestatotal.com');

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

		$subject 			= $request["subject"];

		$mail->Username   	= env('MAIL_GESTION_USER');
		$mail->Password   	= env('MAIL_GESTION_PASS');
		$mail->From         = env('MAIL_GESTION_USER');
		$mail->FromName 	= env('MAIL_GESTION_NAME');
		$mail->Subject    	= $subject." REPORTE GESTION#".time();
		$mail->Body 		= $request["body"];
		$mail->isHTML(true);
		$mail->Send();

	}catch(phpmailerException $e) {
		$mail = new PHPMailer(true);
		$mail->IsSMTP();
		$mail->CharSet 		= 'utf-8';
		$mail->SMTPDebug	= 1;
		$mail->SMTPAuth   	= true;
		$mail->Host       	= "smtp.gmail.com";
		$mail->Port       	= 465;
		$mail->SMTPSecure 	= "ssl";

        $mail->AddAddress("bladimir.quispe@testtest.kurax.dev");

        $mail->AddBCC('neil.flores@testtest.kurax.dev');
        $mail->AddBCC('gorqui.chavez@testtest.kurax.dev');
        $mail->AddBCC("jhonny.quispe@testtest.apuestatotal.com");

		$mail->Username   	=env('MAIL_GESTION_USER');
		$mail->Password   	=env('MAIL_GESTION_PASS');
		$mail->FromName 	= "Apuesta Total";
		$mail->Subject    	= "Error de envio de emails :: Alerta Premio Cobrado Mayor a 36 mil Soles";
		$mail->Body 		= $e->errorMessage();
		$mail->Send();
	}
}





// FUNCIONES DE TLS

function get_turno() {
    global $login;
    global $mysqli;
    $usuario_id = $login['id'];
    //$command ="SELECT id FROM tbl_caja WHERE estado=0 AND usuario_id=".$usuario_id;
    $command = "
        SELECT
            sqc.id,
            ssql.id local_id,
            ssql.cc_id
        FROM
            tbl_caja sqc
            JOIN tbl_local_cajas sqlc ON sqlc.id = sqc.local_caja_id
            JOIN tbl_locales ssql ON ssql.id = sqlc.local_id 
        WHERE
            sqc.estado = 0 
            AND sqc.usuario_id = '" . $usuario_id . "' 
        ORDER BY sqc.id DESC
        LIMIT 1 
        ";
    $list_query = $mysqli->query($command);
    $list = array();
    while ($li = $list_query->fetch_assoc()) {
        $list[] = $li;
    }
    if ($mysqli->error) {
        print_r($mysqli->error);
    }
    return $list;
}

function get_cliente_por_dni($dni) {
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://api.apuestatotal.com/v2/dni?dni=" . $dni,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTPHEADER => [
            "Accept: application/json",
            "Authorization: Bearer " . env('TELEVENTAS_API_TOKEN')
        ],
    ]);
    $response = json_decode(curl_exec($curl), true);
    $err = curl_error($curl);
    curl_close($curl);
    $consulta = ($response["result"] ?? []);
    return $consulta;
}

function obtener_balances($cliente_id){
    global $mysqli;

    $query_balances = "
        SELECT 
          c.id,
          IFNULL(ba1.balance, -99999999) balance, 
          IFNULL(ba2.balance, -99999999) balance_bono_disponible, 
          IFNULL(ba3.balance, -99999999) balance_bono_utilizado, 
          IFNULL(ba4.balance, -99999999) balance_deposito,
          IFNULL(ba5.balance, -99999999) balance_retiro_disponible
        FROM 
          tbl_televentas_clientes c 
          LEFT JOIN tbl_televentas_clientes_balance ba1 ON ba1.cliente_id = c.id AND ba1.tipo_balance_id = 1 
          LEFT JOIN tbl_televentas_clientes_balance ba2 ON ba2.cliente_id = c.id AND ba2.tipo_balance_id = 2 
          LEFT JOIN tbl_televentas_clientes_balance ba3 ON ba3.cliente_id = c.id AND ba3.tipo_balance_id = 3 
          LEFT JOIN tbl_televentas_clientes_balance ba4 ON ba4.cliente_id = c.id AND ba4.tipo_balance_id = 4 
          LEFT JOIN tbl_televentas_clientes_balance ba5 ON ba5.cliente_id = c.id AND ba5.tipo_balance_id = 5 
        WHERE 
        c.id= $cliente_id
        ";
    $list_query_balances = $mysqli->query($query_balances);
    $list_balance = array();
    if (!($mysqli->error)) {
        while ($li = $list_query_balances->fetch_assoc()) { $list_balance[] = $li; }
        if(count($list_balance)>0){
            if((float)$list_balance[0]["balance"]<-9999999) {
                query_tbl_televentas_clientes_balance('insert', $cliente_id, 1, 0);
                $list_balance[0]["balance"] = number_format(0, 2, '.', '');
            }
            if((float)$list_balance[0]["balance_bono_disponible"]<-9999999) {
                query_tbl_televentas_clientes_balance('insert', $cliente_id, 2, 0);
                $list_balance[0]["balance_bono_disponible"] = number_format(0, 2, '.', '');
            }
            if((float)$list_balance[0]["balance_bono_utilizado"]<-9999999) {
                query_tbl_televentas_clientes_balance('insert', $cliente_id, 3, 0);
                $list_balance[0]["balance_bono_utilizado"] = number_format(0, 2, '.', '');
            }
            if((float)$list_balance[0]["balance_deposito"]<-9999999) {
                query_tbl_televentas_clientes_balance('insert', $cliente_id, 4, 0);
                $list_balance[0]["balance_deposito"] = number_format(0, 2, '.', '');
            }
            if((float)$list_balance[0]["balance_retiro_disponible"]<-9999999) {
                query_tbl_televentas_clientes_balance('insert', $cliente_id, 5, 0);
                $list_balance[0]["balance_retiro_disponible"] = number_format(0, 2, '.', '');
            }
        }
    }
    return $list_balance;
}

function query_tbl_televentas_clientes_balance($action, $cliente_id, $tipo_id, $balance){
    global $mysqli;

    if($action==='insert') {
        $query = " 
            INSERT INTO tbl_televentas_clientes_balance (
                cliente_id,
                tipo_balance_id,
                balance,
                created_at,
                updated_at
            ) VALUES (
                " . $cliente_id . ",
                " . $tipo_id . ",
                " . $balance . ",
                now(),
                now()
            )";
        $mysqli->query($query);
    }
    if($action==='update') {
        $query = " 
            UPDATE tbl_televentas_clientes_balance 
            SET
                balance = '" . $balance . "',
                updated_at = now()
            WHERE cliente_id = " . $cliente_id . " AND tipo_balance_id = " . $tipo_id . " 
        ";
        $mysqli->query($query);
    }
}

function query_tbl_televentas_clientes_balance_transaccion($action, $transaccion_id, $cliente_id, $tipo_balance_id, $balance_actual, $monto, $balance_nuevo){
    global $mysqli;
    global $login;

    $user_id = $login ? $login['id'] : 0;

    if($action==='insert') {
        $query = "
            INSERT INTO tbl_televentas_clientes_balance_transaccion (
                transaccion_id,
                cliente_id,
                tipo_balance_id,
                balance_actual,
                monto,
                balance_nuevo,
                user_id,
                created_at
            ) VALUES (
                '" . $transaccion_id . "',
                '" . $cliente_id . "',
                '" . $tipo_balance_id . "',
                '" . $balance_actual . "',
                '" . $monto . "',
                '" . $balance_nuevo . "',
                '" . $user_id . "',
                now()
            )
        ";
        $mysqli->query($query);
    }
}