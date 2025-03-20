<?php
$return = array();
$return["memory_init"]=memory_get_usage();
$return["time_init"] = microtime(true);
include("global_config.php");
include("db_connect.php");
include("sys_login.php");
include('/var/www/html/sys/mailer/class.phpmailer.php');
require_once '/var/www/html/sys/helpers.php';


function send_email_soporte($request){
	if ( !isset($request["subject"])) {
		return "Definir Subject";
	}
	if( !isset($request["body"]) ){
		return "Definir Body";
	}
	if ( !isset($request["cc"]) && !isset($request["bcc"]) ) {
		return "Definir CC o BCC";
	}

	$mail = new PHPMailer(true);
	$mail->IsSMTP();
	$mail->isHTML(true);

	$mail->CharSet      	= isset($request["CharSet"]) ? $request["CharSet"] : 'utf-8';
	$mail->SMTPDebug    	= isset($request["SMTPDebug"]) ? $request["SMTPDebug"] : 1;
	$mail->SMTPAuth     	= isset($request["SMTPAuth"]) ? $request["SMTPAuth"] : true;
	$mail->Host         	= isset($request["Host"]) ? $request["Host"] : "smtp.gmail.com";
	$mail->Port         	= isset($request["Port"]) ? $request["Port"] : 465;
	$mail->SMTPSecure   	= isset($request["SMTPSecure"]) ? $request["SMTPSecure"] : "ssl";
	$mail->Username     	= isset($request["Username"]) ? $request["Username"] : env('MAIL_SOPORTE_USER');
	$mail->Password     	= isset($request["Password"]) ? $request["Password"] : env('MAIL_SOPORTE_PASS');
	$mail->FromName     	= isset($request["FromName"]) ? $request["FromName"] : env('MAIL_SOPORTE_NAME');
	$mail->SMTPKeepAlive 	= true;

	$mail->Subject  = $request["subject"];
	$mail->Body     = $request["body"];

	if(isset($request["cc"])){
		foreach ($request["cc"] as $cc) {
			$mail->AddAddress($cc);
		}
	}

	if(isset($request["bcc"])){
		foreach ($request["bcc"] as $bcc) {
			$mail->AddBCC($bcc);
		}
	}

	if(isset($request["attach"])){
		if(is_array($request["attach"])){
			for ($i=0; $i < count($request["attach"]) ; $i++) {
				$mail->addAttachment($request["attach"][$i]);
			}
		}else{
			$mail->addAttachment($request["attach"]);
		}
	}

	$mail->Send();
	return true;
}

function generar_html_correo_rechazado($cliente){
	$html='<!doctype html>
				<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
				<head>
				  <!--[if !mso]><!-->
				  <meta http-equiv="X-UA-Compatible" content="IE=edge">
				  <!--<![endif]-->
				  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
				  <meta name="viewport" content="width=device-width, initial-scale=1">
				  <style type="text/css">
					#outlook a {
					  padding: 0;
					}

					body {
					  margin: 0;
					  padding: 0;
					  -webkit-text-size-adjust: 100%;
					  -ms-text-size-adjust: 100%;
					}

					table,
					td {
					  border-collapse: collapse;
					  mso-table-lspace: 0pt;
					  mso-table-rspace: 0pt;
					}

					img {
					  border: 0;
					  height: auto;
					  line-height: 100%;
					  outline: none;
					  text-decoration: none;
					  -ms-interpolation-mode: bicubic;
					}

					p {
					  display: block;
					  margin: 13px 0;
					}
				  </style>
				  <!--[if mso]>
						<xml>
						<o:OfficeDocumentSettings>
						  <o:AllowPNG/>
						  <o:PixelsPerInch>96</o:PixelsPerInch>
						</o:OfficeDocumentSettings>
						</xml>
						<![endif]-->
				  <!--[if lte mso 11]>
						<style type="text/css">
						  .mj-outlook-group-fix { width:100% !important; }
						</style>
						<![endif]-->
				  <!--[if !mso]><!-->
				  <link href="https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700" rel="stylesheet" type="text/css">
				  <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@100&display=swap" rel="stylesheet" type="text/css">
				  <style type="text/css">
					@import url(https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700);
					@import url(https://fonts.googleapis.com/css2?family=Raleway:wght@100&display=swap);
				  </style>
				  <!--<![endif]-->
				  <style type="text/css">
					@media only screen and (min-width:480px) {
					  .mj-column-per-100 {
						width: 100% !important;
						max-width: 100%;
					  }

					  .mj-column-per-35 {
						width: 35% !important;
						max-width: 35%;
					  }

					  .mj-column-per-5 {
						width: 5% !important;
						max-width: 5%;
					  }

					  .mj-column-per-15 {
						width: 15% !important;
						max-width: 15%;
					  }

					  .mj-column-per-45 {
						width: 45% !important;
						max-width: 45%;
					  }
					}
				  </style>
				  <style media="screen and (min-width:480px)">
					.moz-text-html .mj-column-per-100 {
					  width: 100% !important;
					  max-width: 100%;
					}

					.moz-text-html .mj-column-per-35 {
					  width: 35% !important;
					  max-width: 35%;
					}

					.moz-text-html .mj-column-per-5 {
					  width: 5% !important;
					  max-width: 5%;
					}

					.moz-text-html .mj-column-per-15 {
					  width: 15% !important;
					  max-width: 15%;
					}

					.moz-text-html .mj-column-per-45 {
					  width: 45% !important;
					  max-width: 45%;
					}
				  </style>
				  <style type="text/css">
					@media only screen and (max-width:480px) {
					  table.mj-full-width-mobile {
						width: 100% !important;
					  }

					  td.mj-full-width-mobile {
						width: auto !important;
					  }
					}
				  </style>
				</head>

				<body style="font-family: "Raleway", sans-serif; word-spacing: normal;">
				  <div style>
					<!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:570px;" width="570" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
					<div style="margin:0px auto;max-width:570px;">
					  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
						<tbody>
						  <tr>
							<td style="direction:ltr;font-size:0px;padding:20px 0;padding-top:0;text-align:center;">
							  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:570px;" ><![endif]-->
							  <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
								<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
								  <tbody>
									<tr>
									  <td align="center" style="font-size:0px;padding:0;padding-top:0;word-break:break-word;">
										<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
										  <tbody>
											<tr>
											  <td style="width:570px;">
												<img height="auto" src="https://storage.apuestatotal.com/mailing/cabecera.png" style="vertical-align: middle; border: 0; display: block; outline: none; text-decoration: none; height: auto; width: 100%; font-size: 13px;" width="570">
											  </td>
											</tr>
										  </tbody>
										</table>
									  </td>
									</tr>
								  </tbody>
								</table>
							  </div>
							  <!--[if mso | IE]></td></tr></table><![endif]-->
							</td>
						  </tr>
						</tbody>
					  </table>
					</div>
					<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:570px;" width="570" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
					<div style="margin:0px auto;max-width:570px;">
					  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
						<tbody>
						  <tr>
							<td style="direction:ltr;font-size:0px;padding:0;text-align:center;">
							  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:570px;" ><![endif]-->
							  <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
								<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
								  <tbody>
									<tr>
									  <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
										<div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:19px;font-weight:bolder;line-height:1;text-align:center;color:#000000;">Estimad@  '.$cliente.'</div>
									  </td>
									</tr>
									<tr>
									  <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
										<div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:17px;line-height:1;text-align:center;color:#000000;">Lamentamos informarte que no hemos podido crear tu cuenta <p style="text-align:center"> porque los datos enviados son incorrectos.</div>
									  </td>
									</tr>
									<tr>
									  <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
										<div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:17px;line-height:1;text-align:center;color:#000000;">Te invitamos a volver a registrarte en  <a href="https://registro.apuestatotal.com">registro.apuestatotal.com,</a> <p style="text-align:center"> no olvides adjuntar la imagen de tus documentos </p>
												   <p style="text-align:center"> por ambos lados. </p></div>
									  </td>
									</tr>                   
								  </tbody>
								</table>
							  </div>
							  <!--[if mso | IE]></td></tr></table><![endif]-->
							</td>
						  </tr>
						</tbody>
					  </table>
					</div>
					<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:570px;" width="570" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
					<div style="margin:0px auto;max-width:570px;">
					  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
						<tbody>
						  <tr>
							<td style="direction:ltr;font-size:0px;padding:0;text-align:center;">
							  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:570px;" ><![endif]-->
							  <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
								<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
								  <tbody>
									<tr>
									  <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
										<div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:18px;line-height:1;text-align:center;color:#000000;">Saludos,</div>
									  </td>
									</tr>
									<tr>
									  <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
										<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
										  <tbody>
											<tr>
											  <td style="width:124px;">
												<img height="28" src="https://storage.apuestatotal.com/mailing/equipoAT.png" style="vertical-align: middle; border: 0; display: block; outline: none; text-decoration: none; height: 28px; width: 100%; font-size: 13px;" width="124">
											  </td>
											</tr>
										  </tbody>
										</table>
									  </td>
									</tr>
								  </tbody>
								</table>
							  </div>
							  <!--[if mso | IE]></td></tr></table><![endif]-->
							</td>
						  </tr>
						</tbody>
					  </table>
					</div>
					<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:570px;" width="570" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
					<div style="margin:0px auto;max-width:570px;">
					  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
						<tbody>
						  <tr>
							<td style="direction:ltr;font-size:0px;padding:0;text-align:center;">
							  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:570px;" ><![endif]-->
							  <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
								<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
								  <tbody>
									<tr>
									  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
										<div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;font-weight:bold;line-height:1;text-align:left;color:#000000;">¿Necesitas ayuda?</div>
									  </td>
									</tr>
									<tr>
									  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
										<div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:1;text-align:left;color:#000000;">Contáctanos a traves de cualquiera de estos medios.</div>
									  </td>
									</tr>
									<tr>
									  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
										<div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:1;text-align:left;color:#000000;"><img width="24px" src="https://storage.apuestatotal.com/mailing/ico-mail.png" style="vertical-align: middle;">
										  <a href="mailto:soporte@testtest.apuestatotal.com" style="text-decoration: none;">soporte@testtest.apuestatotal.com</a>
										</div>
									  </td>
									</tr>
									<tr>
									  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
										<div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:14px;line-height:1;text-align:left;color:#000000;"><img width="24px" src="https://storage.apuestatotal.com/mailing/ico-chat.png" style="vertical-align: middle;"> Chatea en vivo desde nuestra web <a href="https://apuestatotal.com/" style="text-decoration: none;">www.apuestatotal.com</a></div>
									  </td>
									</tr>
									<tr>
									  <td style="background:#9a9a9a;font-size:0px;word-break:break-word;">
										<div style="height:1px;line-height:1px;">&#8202;</div>
									  </td>
									</tr>
									<tr>
									  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
										<div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:14px;font-weight:bold;line-height:1;text-align:left;color:#000000;">Revisa aquí nuestro <a href="https://www.apuestatotal.com/reglamento-de-juego" style="text-decoration: none;">reglamento de juego</a></div>
									  </td>
									</tr>
								  </tbody>
								</table>
							  </div>
							  <!--[if mso | IE]></td></tr></table><![endif]-->
							</td>
						  </tr>
						</tbody>
					  </table>
					</div>
					<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:570px;" width="570" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
					<div style="background:#ff0000;background-color:#ff0000;margin:0px auto;max-width:570px;">
					  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#ff0000;background-color:#ff0000;width:100%;">
						<tbody>
						  <tr>
							<td style="direction:ltr;font-size:0px;padding:10px;text-align:center;">
							  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:192.5px;" ><![endif]-->
							  <div class="mj-column-per-35 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
								<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
								  <tbody>
									<tr>
									  <td align="center" style="font-size:0px;padding:0;word-break:break-word;">
										<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
										  <tbody>
											<tr>
											  <td style="width:183px;">
												<a href="https://www.apuestatotal.com/nuestras-tiendas" target="_blank" style="text-decoration: none;">
												  <img height="auto" src="https://storage.apuestatotal.com/mailing/tiendas.png" style="vertical-align: middle; border: 0; display: block; outline: none; text-decoration: none; height: auto; width: 100%; font-size: 13px;" width="183">
												</a>
											  </td>
											</tr>
										  </tbody>
										</table>
									  </td>
									</tr>
								  </tbody>
								</table>
							  </div>
							  <!--[if mso | IE]></td><td class="vertical-white-line-outlook" style="vertical-align:top;width:27.5px;" ><![endif]-->
							  <div class="mj-column-per-5 mj-outlook-group-fix vertical-white-line" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
								<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
								  <tbody>
									<tr>
									  <td align="center" style="font-size:0px;padding:0;word-break:break-word;">
										<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
										  <tbody>
											<tr>
											  <td style="width:1px;">
												<img height="auto" src="https://storage.apuestatotal.com/mailing/rayita-separadora-blanca.png" style="vertical-align: middle; border: 0; display: block; outline: none; text-decoration: none; height: auto; width: 100%; font-size: 13px;" width="1">
											  </td>
											</tr>
										  </tbody>
										</table>
									  </td>
									</tr>
								  </tbody>
								</table>
							  </div>
							  <!--[if mso | IE]></td><td class="" style="vertical-align:top;width:82.5px;" ><![endif]-->
							  <div class="mj-column-per-15 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
								<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
								  <tbody>
									<tr>
									  <td align="center" style="font-size:0px;padding:0;word-break:break-word;">
										<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
										  <tbody>
											<tr>
											  <td style="width:82px;">
												<img height="auto" src="https://storage.apuestatotal.com/mailing/redes.png" style="vertical-align: middle; border: 0; display: block; outline: none; text-decoration: none; height: auto; width: 100%; font-size: 13px;" width="82">
											  </td>
											</tr>
										  </tbody>
										</table>
									  </td>
									</tr>
								  </tbody>
								</table>
							  </div>
							  <!--[if mso | IE]></td><td class="" style="vertical-align:top;width:247.5px;" ><![endif]-->
							  <div class="mj-column-per-45 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
								<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
								  <tbody>
									<!-- <mj-text padding="0" align="center">
					  <a href="https://www.facebook.com/apuestatotaloficial">
						<img width="30px" src="https://storage.apuestatotal.com/mailing/ico-facebook.png" />
					  </a>
					  <a href="https://www.instagram.com/apuestatotaloficial">
						<img width="30px" src="https://storage.apuestatotal.com/mailing/ico-instagram.png" />
					  </a>
					  <a href="https://www.youtube.com/channel/UC-8u8MoQ1Zdynkq1dybD0mg">
						<img width="30px" src="https://storage.apuestatotal.com/mailing/ico-youtube.png" />
					  </a>
					  <a href="https://twitter.com/apuestatotalof">
						<img width="30px" src="https://storage.apuestatotal.com/mailing/ico-twitter.png" />
					  </a>
					  <a href="https://www.linkedin.com/company/apuestatotal">
						<img width="30px" src="https://storage.apuestatotal.com/mailing/ico-linkedin.png" />
					  </a>
					</mj-text> -->
									<tr>
									  <td align="center" style="font-size:0px;padding:0;word-break:break-word;">
										<!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" ><tr><td><![endif]-->
										<table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="float:none;display:inline-table;">
										  <tr>
											<td style="padding:4px;vertical-align:middle;">
											  <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#3b5998;border-radius:3px;width:30px;">
												<tr>
												  <td style="font-size:0;height:30px;vertical-align:middle;width:30px;">
													<a href="https://www.facebook.com/sharer/sharer.php?u=https://www.facebook.com/apuestatotaloficial" target="_blank" style="text-decoration: none;">
													  <img title="facebook" height="30" src="https://www.mailjet.com/images/theme/v1/icons/ico-social/facebook.png" style="vertical-align: middle; border-radius: 3px; display: block;" width="30">
													</a>
												  </td>
												</tr>
											  </table>
											</td>
										  </tr>
										</table>
										<!--[if mso | IE]></td><td><![endif]-->
										<table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="float:none;display:inline-table;">
										  <tr>
											<td style="padding:4px;vertical-align:middle;">
											  <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#3f729b;border-radius:3px;width:30px;">
												<tr>
												  <td style="font-size:0;height:30px;vertical-align:middle;width:30px;">
													<a href="https://www.instagram.com/apuestatotaloficial" target="_blank" style="text-decoration: none;">
													  <img title="instagram" height="30" src="https://www.mailjet.com/images/theme/v1/icons/ico-social/instagram.png" style="vertical-align: middle; border-radius: 3px; display: block;" width="30">
													</a>
												  </td>
												</tr>
											  </table>
											</td>
										  </tr>
										</table>
										<!--[if mso | IE]></td><td><![endif]-->
										<table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="float:none;display:inline-table;">
										  <tr>
											<td style="padding:4px;vertical-align:middle;">
											  <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#EB3323;border-radius:3px;width:30px;">
												<tr>
												  <td style="font-size:0;height:30px;vertical-align:middle;width:30px;">
													<a href="https://www.youtube.com/channel/UC-8u8MoQ1Zdynkq1dybD0mg" target="_blank" style="text-decoration: none;">
													  <img title="youtube" height="30" src="https://www.mailjet.com/images/theme/v1/icons/ico-social/youtube.png" style="vertical-align: middle; border-radius: 3px; display: block;" width="30">
													</a>
												  </td>
												</tr>
											  </table>
											</td>
										  </tr>
										</table>
										<!--[if mso | IE]></td><td><![endif]-->
										<table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="float:none;display:inline-table;">
										  <tr>
											<td style="padding:4px;vertical-align:middle;">
											  <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#55acee;border-radius:3px;width:30px;">
												<tr>
												  <td style="font-size:0;height:30px;vertical-align:middle;width:30px;">
													<a href="https://twitter.com/intent/tweet?url=https://twitter.com/apuestatotalof" target="_blank" style="text-decoration: none;">
													  <img title="twitter" height="30" src="https://www.mailjet.com/images/theme/v1/icons/ico-social/twitter.png" style="vertical-align: middle; border-radius: 3px; display: block;" width="30">
													</a>
												  </td>
												</tr>
											  </table>
											</td>
										  </tr>
										</table>
										<!--[if mso | IE]></td><td><![endif]-->
										<table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="float:none;display:inline-table;">
										  <tr>
											<td style="padding:4px;vertical-align:middle;">
											  <table border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#0077b5;border-radius:3px;width:30px;">
												<tr>
												  <td style="font-size:0;height:30px;vertical-align:middle;width:30px;">
													<a href="https://www.linkedin.com/shareArticle?mini=true&url=https://www.linkedin.com/company/apuestatotal&title=&summary=&source=" target="_blank" style="text-decoration: none;">
													  <img title="linkedin" height="30" src="https://www.mailjet.com/images/theme/v1/icons/ico-social/linkedin.png" style="vertical-align: middle; border-radius: 3px; display: block;" width="30">
													</a>
												  </td>
												</tr>
											  </table>
											</td>
										  </tr>
										</table>
										<!--[if mso | IE]></td></tr></table><![endif]-->
									  </td>
									</tr>
								  </tbody>
								</table>
							  </div>
							  <!--[if mso | IE]></td></tr></table><![endif]-->
							</td>
						  </tr>
						</tbody>
					  </table>
					</div>
					<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:570px;" width="570" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
					<div style="margin:0px auto;max-width:570px;">
					  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
						<tbody>
						  <tr>
							<td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
							  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:570px;" ><![endif]-->
							  <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
								<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
								  <tbody>
									<tr>
									  <td align="center" style="font-size:0px;padding:0;word-break:break-word;">
										<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
										  <tbody>
											<tr>
											  <td style="width:461px;">
												<a href="https://www.apuestatotal.com/#/account/my-wallet/deposit" target="_blank" style="text-decoration: none;">
												  <img height="auto" src="https://storage.apuestatotal.com/mailing/metodos-pago.png" style="vertical-align: middle; border: 0; display: block; outline: none; text-decoration: none; height: auto; width: 100%; font-size: 13px;" width="461">
												</a>
											  </td>
											</tr>
										  </tbody>
										</table>
									  </td>
									</tr>
								  </tbody>
								</table>
							  </div>
							  <!--[if mso | IE]></td></tr></table><![endif]-->
							</td>
						  </tr>
						</tbody>
					  </table>
					</div>
					<!--[if mso | IE]></td></tr></table><![endif]-->
				  </div>
				</body>

				</html>';
	return $html;

}
function generar_html_correo_aprobado($cliente){
	$html='<!doctype html>
						<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
						<head>
						  <!--[if !mso]><!-->
						  <meta http-equiv="X-UA-Compatible" content="IE=edge">
						  <!--<![endif]-->
						  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
						  <meta name="viewport" content="width=device-width, initial-scale=1">
						  <style type="text/css">
							#outlook a {
							  padding: 0;
							}

							body {
							  margin: 0;
							  padding: 0;
							  -webkit-text-size-adjust: 100%;
							  -ms-text-size-adjust: 100%;
							}

							table,
							td {
							  border-collapse: collapse;
							  mso-table-lspace: 0pt;
							  mso-table-rspace: 0pt;
							}

							img {
							  border: 0;
							  height: auto;
							  line-height: 100%;
							  outline: none;
							  text-decoration: none;
							  -ms-interpolation-mode: bicubic;
							}

							p {
							  display: block;
							  margin: 13px 0;
							}
						  </style>
						  <!--[if mso]>
								<xml>
								<o:OfficeDocumentSettings>
								  <o:AllowPNG/>
								  <o:PixelsPerInch>96</o:PixelsPerInch>
								</o:OfficeDocumentSettings>
								</xml>
								<![endif]-->
						  <!--[if lte mso 11]>
								<style type="text/css">
								  .mj-outlook-group-fix { width:100% !important; }
								</style>
								<![endif]-->
						  <!--[if !mso]><!-->
						  <link href="https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700" rel="stylesheet" type="text/css">
						  <style type="text/css">
							@import url(https://fonts.googleapis.com/css?family=Ubuntu:300,400,500,700);
						  </style>
						  <!--<![endif]-->
						  <style type="text/css">
							@media only screen and (min-width:480px) {
							  .mj-column-per-100 {
								width: 100% !important;
								max-width: 100%;
							  }

							  .mj-column-per-35 {
								width: 35% !important;
								max-width: 35%;
							  }

							  .mj-column-per-10 {
								width: 10% !important;
								max-width: 10%;
							  }

							  .mj-column-per-20 {
								width: 20% !important;
								max-width: 20%;
							  }
							}
						  </style>
						  <style media="screen and (min-width:480px)">
							.moz-text-html .mj-column-per-100 {
							  width: 100% !important;
							  max-width: 100%;
							}

							.moz-text-html .mj-column-per-35 {
							  width: 35% !important;
							  max-width: 35%;
							}

							.moz-text-html .mj-column-per-10 {
							  width: 10% !important;
							  max-width: 10%;
							}

							.moz-text-html .mj-column-per-20 {
							  width: 20% !important;
							  max-width: 20%;
							}
						  </style>
						  <style type="text/css">
							@media only screen and (max-width:480px) {
							  table.mj-full-width-mobile {
								width: 100% !important;
							  }

							  td.mj-full-width-mobile {
								width: auto !important;
							  }
							}
						  </style>
						  <style type="text/css">
							body {
							  font-family: "Raleway", sans-serif;
							}

							img {
							  vertical-align: middle;
							}

							a {
							  text-decoration: none;
							}
						  </style>
						</head>

						<body style="word-spacing:normal;">
						  <div style="">
							<!--[if mso | IE]><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
							<div style="margin:0px auto;max-width:600px;">
							  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
								<tbody>
								  <tr>
									<td style="direction:ltr;font-size:0px;padding:0;text-align:center;">
									  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]-->
									  <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
										<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
										  <tbody>
											<tr>
											  <td align="center" style="font-size:0px;padding:0;word-break:break-word;">
												<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
												  <tbody>
													<tr>
													  <td style="width:600px;">
														<img height="auto" src="https://storage.apuestatotal.com/mailing/cabecera.png" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px;" width="600" />
													  </td>
													</tr>
												  </tbody>
												</table>
											  </td>
											</tr>
										  </tbody>
										</table>
									  </div>
									  <!--[if mso | IE]></td></tr></table><![endif]-->
									</td>
								  </tr>
								</tbody>
							  </table>
							</div>
							<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
							<div style="margin:0px auto;max-width:600px;">
							  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
								<tbody>
								  <tr>
									<td style="direction:ltr;font-size:0px;padding:0;text-align:center;">
									  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]-->
									  <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
										<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
										  <tbody>
											<tr>
											  <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
												<div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:19px;font-weight:bolder;line-height:1;text-align:center;color:#000000;">¡Felicitaciones '.$cliente.'!</div>
											  </td>
											</tr>
											<tr>
											  <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
												<div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:18px;line-height:1;text-align:center;color:#000000;">Tu cuenta ha sido creada satisfactoriamente <p>Haz click en el siguiente enlace para activar la cuenta</p></div>
											  </td>
											</tr>
											<tr>
											  <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
												<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
												  <tbody>
													<tr>
													  <td style="width:190px;">
														<a href="https://www.apuestatotal.com/#/verify?action=verify&code=" target="_blank">
														  <img height="auto" src="https://storage.apuestatotal.com/mailing/activar-cuenta.png" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px;" width="190" />
														</a>
													  </td>
													</tr>
												  </tbody>
												</table>
											  </td>
											</tr>
											<tr>
											  <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
												<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
												  <tbody>
													<tr>
													  <td style="width:458px;">
														<img height="auto" src="https://storage.apuestatotal.com/mailing/ban-apuesta-gratis.png" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px;" width="458" />
													  </td>
													</tr>
												  </tbody>
												</table>
											  </td>
											</tr>
											<tr>
											  <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
												<div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:18px;line-height:1;text-align:center;color:#000000;">Muchas gracias,</div>
											  </td>
											</tr>
											<tr>
											  <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
												<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
												  <tbody>
													<tr>
													  <td style="width:124px;">
														<img height="auto" src="https://storage.apuestatotal.com/mailing/equipoAT.png" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px;" width="124" />
													  </td>
													</tr>
												  </tbody>
												</table>
											  </td>
											</tr>
											<tr>
											  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
												<div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;font-weight:bold;line-height:1;text-align:left;color:#000000;">¿Necesitas ayuda?</div>
											  </td>
											</tr>
											<tr>
											  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
												<div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:1;text-align:left;color:#000000;">Contactanos a traves de cualquiera de estos medios.</div>
											  </td>
											</tr>
											<tr>
											  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
												<div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:1;text-align:left;color:#000000;"><img width="24px" src="https://storage.apuestatotal.com/mailing/ico-mail.png" />
												  <a href="mailto:soporte@testtest.apuestatotal.com">soporte@testtest.apuestatotal.com</a>
												</div>
											  </td>
											</tr>
											<tr>
											  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
												<div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:14px;line-height:1;text-align:left;color:#000000;"><img width="24px" src="https://storage.apuestatotal.com/mailing/ico-chat.png" /> Chatea en vivo desde nuestra web <a href="https://apuestatotal.com/">www.apuestatotal.com</a></div>
											  </td>
											</tr>
											<tr>
											  <td style="background:#9a9a9a;font-size:0px;word-break:break-word;">
												<div style="height:1px;line-height:1px;">&#8202;</div>
											  </td>
											</tr>
											<tr>
											  <td align="left" style="font-size:0px;padding:10px 25px;word-break:break-word;">
												<div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:14px;font-weight:bold;line-height:1;text-align:left;color:#000000;">Revisa aquí nuestro <a href="https://www.apuestatotal.com/reglamento-de-juego">reglamento de juego</a></div>
											  </td>
											</tr>
										  </tbody>
										</table>
									  </div>
									  <!--[if mso | IE]></td></tr></table><![endif]-->
									</td>
								  </tr>
								</tbody>
							  </table>
							</div>
							<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
							<div style="background:#ff0000;background-color:#ff0000;margin:0px auto;max-width:600px;">
							  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="background:#ff0000;background-color:#ff0000;width:100%;">
								<tbody>
								  <tr>
									<td style="direction:ltr;font-size:0px;padding:20px;text-align:center;">
									  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:196px;" ><![endif]-->
									  <div class="mj-column-per-35 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
										<table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
										  <tbody>
											<tr>
											  <td style="vertical-align:top;padding:0;">
												<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="" width="100%">
												  <tbody>
													<tr>
													  <td align="left" style="font-size:0px;padding:0;word-break:break-word;">
														<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
														  <tbody>
															<tr>
															  <td style="width:183px;">
																<a href="https://www.apuestatotal.com/nuestras-tiendas" target="_blank">
																  <img height="auto" src="https://storage.apuestatotal.com/mailing/tiendas.png" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px;" width="183" />
																</a>
															  </td>
															</tr>
														  </tbody>
														</table>
													  </td>
													</tr>
												  </tbody>
												</table>
											  </td>
											</tr>
										  </tbody>
										</table>
									  </div>
									  <!--[if mso | IE]></td><td class="" style="vertical-align:top;width:56px;" ><![endif]-->
									  <div class="mj-column-per-10 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
										<table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
										  <tbody>
											<tr>
											  <td style="vertical-align:top;padding:0;">
												<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="" width="100%">
												  <tbody>
													<tr>
													  <td align="center" style="font-size:0px;padding:0;word-break:break-word;">
														<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
														  <tbody>
															<tr>
															  <td style="width:1px;">
																<img height="auto" src="https://storage.apuestatotal.com/mailing/rayita-separadora-blanca.png" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px;" width="1" />
															  </td>
															</tr>
														  </tbody>
														</table>
													  </td>
													</tr>
												  </tbody>
												</table>
											  </td>
											</tr>
										  </tbody>
										</table>
									  </div>
									  <!--[if mso | IE]></td><td class="" style="vertical-align:top;width:112px;" ><![endif]-->
									  <div class="mj-column-per-20 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
										<table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
										  <tbody>
											<tr>
											  <td style="vertical-align:top;padding:0;">
												<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="" width="100%">
												  <tbody>
													<tr>
													  <td align="center" style="font-size:0px;padding:0;word-break:break-word;">
														<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
														  <tbody>
															<tr>
															  <td style="width:85px;">
																<img height="auto" src="https://storage.apuestatotal.com/mailing/redes.png" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px;" width="85" />
															  </td>
															</tr>
														  </tbody>
														</table>
													  </td>
													</tr>
												  </tbody>
												</table>
											  </td>
											</tr>
										  </tbody>
										</table>
									  </div>
									  <!--[if mso | IE]></td><td class="" style="vertical-align:top;width:196px;" ><![endif]-->
									  <div class="mj-column-per-35 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
										<table border="0" cellpadding="0" cellspacing="0" role="presentation" width="100%">
										  <tbody>
											<tr>
											  <td style="vertical-align:top;padding:0;">
												<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="" width="100%">
												  <tbody>
													<tr>
													  <td align="center" style="font-size:0px;padding:0;word-break:break-word;">
														<div style="font-family:Ubuntu, Helvetica, Arial, sans-serif;font-size:13px;line-height:1;text-align:center;color:#000000;"><a href="https://www.facebook.com/apuestatotaloficial">
															<img width="29px" src="https://storage.apuestatotal.com/mailing/ico-facebook.png" />
														  </a>
														  <a href="https://www.instagram.com/apuestatotaloficial">
															<img width="29px" src="https://storage.apuestatotal.com/mailing/ico-instagram.png" />
														  </a>
														  <a href="https://www.youtube.com/channel/UC-8u8MoQ1Zdynkq1dybD0mg">
															<img width="29px" src="https://storage.apuestatotal.com/mailing/ico-youtube.png" />
														  </a>
														  <a href="https://twitter.com/apuestatotalof">
															<img width="29px" src="https://storage.apuestatotal.com/mailing/ico-twitter.png" />
														  </a>
														  <a href="https://www.linkedin.com/company/apuestatotal">
															<img width="29px" src="https://storage.apuestatotal.com/mailing/ico-linkedin.png" />
														  </a>
														</div>
													  </td>
													</tr>
													<!-- <mj-social font-size="15px" icon-size="30px" mode="horizontal" padding="0">
								  <mj-social-element vertical-align="middle" name="facebook" title="facebook" href="https://www.facebook.com/apuestatotaloficial" />
								  <mj-social-element vertical-align="middle" name="instagram" title="instagram" href="https://www.instagram.com/apuestatotaloficial" />
								  <mj-social-element vertical-align="middle" name="youtube" title="youtube" href="https://www.youtube.com/channel/UC-8u8MoQ1Zdynkq1dybD0mg" />
								  <mj-social-element vertical-align="middle" name="twitter" title="twitter" href="https://twitter.com/apuestatotalof" />
								  <mj-social-element vertical-align="middle" name="linkedin" title="linkedin" href="https://www.linkedin.com/company/apuestatotal" />
								</mj-social> -->
												  </tbody>
												</table>
											  </td>
											</tr>
										  </tbody>
										</table>
									  </div>
									  <!--[if mso | IE]></td></tr></table><![endif]-->
									</td>
								  </tr>
								</tbody>
							  </table>
							</div>
							<!--[if mso | IE]></td></tr></table><table align="center" border="0" cellpadding="0" cellspacing="0" class="" style="width:600px;" width="600" ><tr><td style="line-height:0px;font-size:0px;mso-line-height-rule:exactly;"><![endif]-->
							<div style="margin:0px auto;max-width:600px;">
							  <table align="center" border="0" cellpadding="0" cellspacing="0" role="presentation" style="width:100%;">
								<tbody>
								  <tr>
									<td style="direction:ltr;font-size:0px;padding:20px 0;text-align:center;">
									  <!--[if mso | IE]><table role="presentation" border="0" cellpadding="0" cellspacing="0"><tr><td class="" style="vertical-align:top;width:600px;" ><![endif]-->
									  <div class="mj-column-per-100 mj-outlook-group-fix" style="font-size:0px;text-align:left;direction:ltr;display:inline-block;vertical-align:top;width:100%;">
										<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="vertical-align:top;" width="100%">
										  <tbody>
											<tr>
											  <td align="center" style="font-size:0px;padding:10px 25px;word-break:break-word;">
												<table border="0" cellpadding="0" cellspacing="0" role="presentation" style="border-collapse:collapse;border-spacing:0px;">
												  <tbody>
													<tr>
													  <td style="width:461px;">
														<a href="https://www.apuestatotal.com/#/account/my-wallet/deposit" target="_blank">
														  <img height="auto" src="https://storage.apuestatotal.com/mailing/metodos-pago.png" style="border:0;display:block;outline:none;text-decoration:none;height:auto;width:100%;font-size:13px;" width="461" />
														</a>
													  </td>
													</tr>
												  </tbody>
												</table>
											  </td>
											</tr>
										  </tbody>
										</table>
									  </div>
									  <!--[if mso | IE]></td></tr></table><![endif]-->
									</td>
								  </tr>
								</tbody>
							  </table>
							</div>
							<!--[if mso | IE]></td></tr></table><![endif]-->
						  </div>
						</body>

						</html>';
	return $html;
}
function registrar($register_id){

	$curl = curl_init();
	curl_setopt_array($curl, [
	  CURLOPT_URL => "https://api.apuestatotal.com/v2/betconstruct/register",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "POST",
	  CURLOPT_POSTFIELDS => json_encode(['register_id' => $register_id]),
	  CURLOPT_HTTPHEADER => [
		"Accept: application/json",
		"Authorization:Bearer ".env('SOPORTE_V2_TOKEN'),
		"Content-Type: application/json"
	  ],
	]);
	$response = curl_exec($curl);
	$err = curl_error($curl);
	curl_close($curl);
	if ($err) {
	  return "cURL Error #:" . $err;
	} else {
	  return $response;
	}

}
if (isset($_POST['get_archivos'])) {

	$id = $_POST['get_archivos']['id'];
	$consulta = [];
	$retorna = [];
	$query = "SELECT 
			atw_rf.file_path AS archivo,
			atw_rf.file_type 
			FROM at_web.register_files atw_rf 
			LEFT JOIN  wwwapuestatotal_gestion.tbl_web_registers wr on wr.register_id= atw_rf.registers_id
			WHERE wr.id= ".$id;
	$result = $mysqli->query($query);
	while ($r = $result->fetch_assoc()) $consulta[] = $r;
	foreach ($consulta as $key => $value) {
		$retorna[$key] = $value;
	}
	$objeto=[];
	$objeto["archivos"]=$retorna;
	$objeto["curr_login"]=$login;
	print_r(json_encode($objeto));
	return;
};



if(isset($_POST["set_registro_aprobacion"])){
		if(!isset($login)){
			$result["mensaje"]="Su sesión ha finalizado";
			$result["error"]=true;
			$result["recargar"]=true;
			print_r(json_encode($result));
			return;
		}
		$usu_id=$login["id"];
		$data=$_POST["set_registro_aprobacion"];
		$estado=$data["estado"];//1 apro,   2 rechaz
		$cliente=$data["cliente"];
		$motivo=(isset($data["motivo"])&& $data["motivo"]!="")?$data["motivo"]:"null";
		$id=$data["id"];
		$register_id=$data["register_id"];
		$update_enviar=false;
		$update_tbl=true;
		if($estado==1){//aprobar
			//send api para aprobar
			$curl= registrar($register_id);
			$response=json_decode($curl,TRUE);
			//print_r($response);
			if(isset($response["http_code"])){
				$resp=$response["result"];
				if($response["http_code"]==200){//aprobado en betconstruc  200
					//$msj="Su cuenta ha sido Aprobada";         	
					/*$resp_array=json_decode($resp,TRUE); 
					$id=$resp_array["Id"];*/
					$estado_msg="El registro fue Aprobado con éxito";
					$return["mensaje"] = " ".$cliente.":".$estado_msg." \n";
					$return["swaltipo"]="success";
					$subject="Aprobación Usuario";

					$update_enviar=false;
				//	$codigo=$resp;//codigo que devuelve para  enlace activar cuenta
					$html_correo='';//generar_html_correo_aprobado($cliente);
				}//fin if response ==200
				elseif($response["http_code"]==404){//not found   no registrado en sist
					$estado_msg="No registrado en sistema";
					$return["mensaje"] = " ".$cliente.":".$estado_msg." \n".$resp;
					$return["swaltipo"]="info";
					$update_enviar=false;
					$update_tbl=false;
				}
				elseif($response["http_code"]==401){//duplicate login,  cuenta ya creada   401
					$estado_msg="Cuenta ya creada";
					$return["mensaje"] = " ".$cliente.":".$estado_msg." \n".$resp;
					$return["swaltipo"]="info";
					$update_enviar=false;
					$update_tbl=false;

				}
				/*else{
					$estado=2;//rechaza
					$return["mensaje"] = $resp;
					$return["swaltipo"]="error";
					$motivo=$resp;
					$estado_msg="El registro no fue aprobado. \n".$resp;
					$msj=$resp;
					$update_tbl=false;
				};*/
			}
		}//fin  if aprobar
		else{
			$update_enviar=true;
			$estado_msg=" El registro fue Rechazado";
			$return["swaltipo"]="error";
			$return["mensaje"] = " ".$cliente.":".$estado_msg;
			$subject="Apuesta Total: cuenta incorrecta - ".$register_id."#".date('ymdhis');
			$html_correo=generar_html_correo_rechazado($cliente);
		}


		if($update_tbl){
			$command = "
					UPDATE tbl_web_registers set 
						status=$estado
						,motivo_txt='". $mysqli->real_escape_string($motivo)."'
						,updated_at=now()
						,update_user_id=$usu_id
						,update_user_at=now()
					where id=".$id;
			$mysqli->query($command);
			if($mysqli->error){
				print_r($mysqli->error);
				exit();
			}
		}

		if($update_enviar) {
			$correo=$data["correo"];

			$body = $html_correo;
			$request = [
					"subject" => $subject,//"Aprobación Usuario",
					"body" => $body,
					"cc" => [$correo],
					"bcc" => [$correo]
				];
			try {
				send_email_soporte($request);	
			} catch (Exception $e) {
				$return["mensaje"]="Error al enviar correo a $correo";
			}
		}
		//$return["mensaje"] = " ".$cliente.":".$estado_msg;
		$return["curr_login"]=$login;
}


if(isset($_POST["set_actualizar_datos_cliente"])){
	$data=$_POST["set_actualizar_datos_cliente"];
	$id=$data["id"];
	$nombre=$data["nombre"];
	$apellido=$data["apellido"];
	$email=$data["correo"];
	$command = "
				UPDATE at_web.registers 
				SET
					FirstName='". $mysqli->real_escape_string($nombre)."'
					,LastName='". $mysqli->real_escape_string($apellido)."'
					,Email='". $mysqli->real_escape_string($email)."'
					,updated_at=now()
				where id=".$id;
	$mysqli->query($command);
	if($mysqli->error){
		print_r($mysqli->error);
		exit();
	}
	$return["mensaje"]="Datos Actualizados Correctamente";

}
if(isset($_POST["sec_aprobacion_registros_list"])){
	$ID_LOGIN=$login["id"];
	$data=$_POST["sec_aprobacion_registros_list"];
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$column_index = $_POST['order'][0]['column']; // Column index
	$column_name = $_POST['columns'][$column_index]['data']; // Column name
	$column_sort_order = $_POST['order'][0]['dir']; // asc or desc
	$search_value = $_POST['search']['value']; // Search value
	$search_value = $mysqli->real_escape_string($search_value);

	$estado_select=$_POST["estado_select"];

	$search_query = " ";
	if($search_value != ''){
		//(CONCAT(reg.FirstName, ' ', IFNULL(reg.LastName, '')))  LIKE '%".$search_value."%' or 
	   $search_query = " and 
			reg.FirstName LIKE '%".$search_value."%' or
			reg.LastName LIKE '%".$search_value."%' or
			wreg.id LIKE '%".$search_value."%' or
			wreg.created_at LIKE '%".$search_value."%' or
			(CASE 
			WHEN reg.DocType=1 THEN 'DNI' 
			WHEN reg.DocType=2 THEN 'CEX' 
			WHEN reg.DocType=3 THEN 'PAS' 
		 END) LIKE '%".$search_value."%' or

			reg.DocNumber LIKE '%".$search_value."%' or
			(CASE 
				WHEN wreg.status=0 THEN 'Pendiente' 
				WHEN wreg.status=1 THEN 'Aprobado' 
				WHEN wreg.status=2 THEN 'Rechazado' 
				WHEN wreg.status=3 THEN 'Registrado' 
			END)  LIKE '%".$search_value."%' or
			usu.usuario LIKE '%".$search_value."%' or
			wreg.update_user_at LIKE '%".$search_value."%' or
			reg.Email LIKE '%".$search_value."%' or
			reg.MobilePhone LIKE '%".$search_value."%' 
			 ";
			
	}
	$SELECT=" SELECT 
		wreg.id
		,wreg.created_at
		,reg.ClientId
		,reg.id as register_id
		/*,(CONCAT(reg.FirstName, ' ', IFNULL(reg.LastName, ''))) as Cliente*/
		,reg.FirstName as nombre
		,reg.LastName as apellido
		,(CASE 
			WHEN reg.DocType=1 THEN 'DNI' 
			WHEN reg.DocType=2 THEN 'CEX' 
			WHEN reg.DocType=3 THEN 'PAS' 
		 END) as DocType

		,reg.DocNumber

		,reg.Email
		,(CASE 
				WHEN wreg.status=0 THEN 'Pendiente' 
				WHEN wreg.status=1 THEN 'Aprobado' 
				WHEN wreg.status=2 THEN 'Rechazado' 
				WHEN wreg.status=3 THEN 'Registrado' 
		  END) as estado
		  ,(SELECT 
			count(atw_rf.id)
			FROM at_web.register_files atw_rf 
			LEFT JOIN  wwwapuestatotal_gestion.tbl_web_registers wr on wr.id= atw_rf.registers_id
			WHERE atw_rf.registers_id= wreg.register_id) as cantidad

		  ,usu.usuario
		  ,wreg.update_user_at
		  ,wreg.motivo_txt


	 FROM wwwapuestatotal_gestion.tbl_web_registers  wreg
	 inner join  at_web.registers as reg on (reg.id= wreg.register_id and reg.DocType IN(2,3))
	 left join wwwapuestatotal_gestion.tbl_usuarios usu on usu.id= wreg.update_user_id
	";

	$where=" 1 ";
	if($estado_select!="Todos"){
		$where.=" and (CASE 
				WHEN wreg.status=0 THEN 'Pendiente' 
				WHEN wreg.status=1 THEN 'Aprobado' 
				WHEN wreg.status=2 THEN 'Rechazado' 
				WHEN wreg.status=3 THEN 'Registrado' 
		  END)  ='".$estado_select."' ";
	}

	$emp_query=   $SELECT."	WHERE $where ";
	//print_r($emp_query);
	$sel = $mysqli->query("SELECT count(*) AS allcount FROM ($SELECT WHERE $where) as filas");
	$records = $sel->fetch_assoc();
	$total_records = $records['allcount'];

	$emp_query=   $SELECT."	WHERE $where ".$search_query;
	//print_r($emp_query);

	$sel = $mysqli->query("SELECT count(*) AS allcount FROM (".$emp_query.") AS subquery");
	$records = $sel->fetch_assoc();
	$total_recordwith_filter = $records['allcount'];

	$limit=" limit ".$row.",".$rowperpage;
	if($rowperpage==-1){
		$limit="";
	}
	$emp_query= $SELECT." WHERE $where ".$search_query." order by ".$column_name." ".$column_sort_order.$limit;	
	$registros = $mysqli->query($emp_query);
	$data = array();

	while ($row = $registros->fetch_assoc()) {
	   $data[] = $row;
	}

	$response = array(
	  "draw" => $draw,
	  "iTotalRecords" => $total_records,
	  "iTotalDisplayRecords" => $total_recordwith_filter,
	  "aaData" => $data
	);

	echo json_encode($response);
	return;
}

$return["memory_end"]=memory_get_usage();
$return["time_end"] = microtime(true);
$return["memory_total"]=($return["memory_end"]-$return["memory_init"]);
$return["time_total"]=($return["time_end"]-$return["time_init"]);
print_r(json_encode($return));
?>