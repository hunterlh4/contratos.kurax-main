<?php
/*
Enviar email
INPUT: info_mail = [
	address, cc, bcc, subject, body
]
OUPUT: 
*/
function emailSender($info_mail){
	$mail = new PHPMailer(true);

	try {
		$mail->isSMTP();                                            // Send using SMTP
		$mail->Host       = "smtp.gmail.com";
		$mail->SMTPAuth   = true;
		$mail->Username   = env('MAIL_GESTION_USER');
		$mail->Password   = env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
		$mail->Port       = 465;
		$mail->CharSet    = 'utf-8';
		$mail->SMTPKeepAlive 	= true;
		$mail->From       = env('MAIL_GESTION_USER');
		$mail->FromName   = env('MAIL_GESTION_NAME');
		$mail->Priority   = 2;
		$mail->AddCustomHeader("X-MSMail-Priority: Urgent");
		$mail->AddCustomHeader("Importance: High");

		$mails['address'] = $info_mail['address'];
		$mails['cc'] = $info_mail['cc'];
		$mails["bcc"] = $info_mail['bcc'];

		if($mails["address"]){
			foreach ($mails["address"] as $address) {
				$mail->addAddress($address);
			}
		}
		
		if($mails["cc"]){
			foreach ($mails["cc"] as $cc) {
				$mail->addCC($cc);
			}
		}

		if($mails["bcc"]){
			foreach ($mails["bcc"] as $bcc) {
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $info_mail['subject'];
		$mail->Body     = $info_mail['body'];

		$mail->send();
		
		return true;				
		
	} catch (Exception $e) {
		return "Error: {$mail->ErrorInfo}";
	}
}

