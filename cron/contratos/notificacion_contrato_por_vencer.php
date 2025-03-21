<?php
// require_once '/var/www/html/cron/cron_db_connect.php';
include '/var/www/html/sys/mailer/class.phpmailer.php';
require_once '/var/www/html/cron/cron_pdo_connect.php';
require_once '/var/www/html/sys/helpers.php';
// require_once '/var/www/html/cron/validate.php';
require_once '/var/www/html/env.php';
include '/var/www/html/sys/envio_correos.php';




try {


	$host = $_SERVER["HTTP_HOST"];
	$fecha_hoy = date("Y-m-d");
	$titulo_del_correo = '🚨 ¡Alerta! - Contratos por vencer - Fecha de alerta: ' . $fecha_hoy;
	$cont      = 1;
	$num_contratos_por_vencer = 0;
	$html = '';
	$contrato_id_alertados = [];
	$cc_id = [];


	$header = '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 800px;">';

	$header .= '<thead>';
	$header .= '<tr>';
	$header .= '<th colspan="6" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
	$header .= '<b>Contratos de Arrendamiento por vencer</b>';
	$header .= '</th>';
	$header .= '</tr>';
	$header .= '</thead>';

	$header .= '<thead>';
	$header .= '<tr>';
	$header .= '<th class="col-md-2">';
	$header .= '<b>#</b>';
	$header .= '</th>';

	$header .= '<th>';
	$header .= '<b>Código</b>';
	$header .= '</th>';


	$header .= '<th>';
	$header .= '<b>Ubicación del inmueble</b>';
	$header .= '</th>';

	$header .= '<th>';
	$header .= '<b>Fecha fin del contrato</b>';
	$header .= '</th>';

	$header .= '<th>';
	$header .= '<b>Abogado</b>';
	$header .= '</th>';

	$header .= '<th>';
	$header .= '<b>Correo a notificar</b>';
	$header .= '</th>';

	$header .= '<th>';
	$header .= '</th>';

	$header .= '</tr>';
	$header .= '</thead>';

	$header .= '<tbody>';

	$footer = '</tbody>';
	$footer .= '</table>';

	$correos_arrendamiento = [];

	$tbody = '';




	$html_arrendamiento = '<html>' . $header . $tbody . $footer . '</html>';




	$correos       = new Correos(env('SEND_EMAIL'), env('EMAIL_TEST'));

	$cc = 'alexticoma4@gmail.com';
	$bcc = 'hola@gmail.com';

	$mail = [
		"subject" => $titulo_del_correo,
		"body"    => $html_arrendamiento,
		"cc"      => [$cc],
		"bcc"     => [$bcc],
	];

	send_email($mail);

	$html .= $header . $tbody . $footer;
} catch (\Exception $e) {
	print_r($e);
}
