<?php
include("/var/www/html/sys/db_connect.php");
include("/var/www/html/sys/sys_login.php");
//date_default_timezone_set("America/Lima");


/*ini_set('memory_limit', '2G');
ini_set('max_execution_time', '500');
set_time_limit(0);

setlocale(LC_ALL, "es_ES");*/
// include("/var/www/html/sys/funtion_barcode.php");
$ticket = json_decode(json_encode($_POST));
ob_start();
$nombreImagen = "/var/www/html/img/logo_at_voucher.jpeg";
$imagenBase64 = "data:image/png;base64," . base64_encode(file_get_contents($nombreImagen));

?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ticket</title>
        <style>
            body {
                padding: 0;
                margin: -40px;
                font-family: Helvetica, "Trebuchet MS", Verdana, sans-serif;
                font-size: 10px;
            }

            table {
                margin-bottom: 10px;
            }

            .border {
                width: 100%;
                border: 1px solid black;
            }

            .bolder {
                font-weight: bolder;
            }

            tr.border-bottom-dashed td {
                border-bottom: 1px dotted black;
            }

            .align-center {
                text-align: center;
            }

            .align-right {
                text-align: right;
            }

            .t-footer {
                font-size: 16px;
            }

            .align-left {
                text-align: left;
            }

            .align-middle {
                vertical-align: middle;
            }

            .align-center-middle {
                text-align: center;
                vertical-align: middle;
            }

            .first-column {
                font-weight: bolder;
                text-transform: uppercase;
                width: 42%;
            }
        </style>
    </head>
    <body>
    <table width="100%">
        <tr>
            <td class="align-center"><img width="100px" src="<?php echo($imagenBase64); ?>" alt=""/></td>
        </tr>
    </table>
    <table width="100%" class="border">
        <tbody>
        <tr class="border-bottom-dashed">
            <td class="first-column">LOCAL</td>
            <td class="align-right"><?= $ticket->local_nombre ?></td>
        </tr>
        <tr class="border-bottom-dashed">
            <td class="first-column">TERMINAL ID</td>
            <td class="align-right"><?= $ticket->id_terminal_auto_servicio ?></td>
        </tr>
        <tr class="border-bottom-dashed">
            <td class="first-column">NOMBRE TERMINAL</td>
            <td class="align-right"><?= $ticket->nombre_terminal ?></td>
        </tr>
        <tr class="border-bottom-dashed">
            <td class="first-column">CENTRO DE COSTOS</td>
            <td class="align-right"><?= $ticket->cc_id ?></td>
        </tr>
        <tr class="border-bottom-dashed">
            <td class="first-column">CAJERO</td>
            <td class="align-right"><?= strtoupper($ticket->cajero) ?></td>
        </tr>
        <tr class="border-bottom-dashed">
            <td class="first-column">FECHA <?= strtoupper($ticket->tipo_transaccion); ?></td>
            <td class="align-right">
                <?php
                echo date('Y-m-d H:i:s', strtotime($ticket->updated_at));
                ?>
            </td>
        </tr>
        <?php if (isset($ticket->is_copy) && $ticket->is_copy) { ?>
            <tr>
                <td class="first-column">FECHA REIMPRESIÓN</td>
                <td class="align-right"><?= date('Y-m-d H:i:s'); ?></td>
            </tr>
        <?php } ?>
        </tbody>
    </table>

    <table class="border t-footer">
        <tr>
            <td class="first-column"><?= $ticket->tipo_transaccion ?></td>
            <td class="bolder align-right">
                <?= number_format($ticket->monto, 2, '.', '') ?>
                PEN
            </td>
        </tr>
    </table>
    <?php
    if (isset($ticket->is_copy) && $ticket->is_copy) {
        ?>
        <div class="align-center border">
            Este ticket es una copia
        </div>
        <?php
    }
    ?>
    </body>
    </html>


<?php
// exit();
$html = ob_get_clean();

require_once './dompdf/autoload.inc.php';

use Dompdf\Dompdf;

$dompdf = new Dompdf();
$option = $dompdf->getOptions();
$option->set(array('isRemoteEnable' => true));
$dompdf->setOptions($option);
$dompdf->loadHtml(($html));
// $dompdf->setPaper('letter');
$dompdf->setPaper('b7', 'portrait');
$dompdf->render();
$output = $dompdf->output();

$file_name = 'ticket' . strtotime("now") . '.pdf';
$filePath = '/var/www/html/export/files_export/terminal_ticket/' . $file_name;
file_put_contents($filePath, $output);
$route_path = '/export/files_export/terminal_ticket/' . $file_name;
print json_encode(["path" => $route_path], true);
//header("Location: " . $route_path);
exit(0);
