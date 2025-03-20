<?php
date_default_timezone_set("America/Lima");

$result=array();
include("db_connect.php");
include("sys_login.php");

include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';

require_once '/var/www/html/env.php';
include '/var/www/html/sys/envio_correos.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function send_email_confirmacion_solicitud_contrato_interno($contrato_id)
{
	include("db_connect.php");
	include("sys_login.php");

	$host= $_SERVER["HTTP_HOST"];

	$list_emp_cont = $mysqli->query("SELECT tpg.valor FROM tbl_parametros_generales tpg WHERE tpg.codigo = 'empresa_contacto_de_contratos' AND tpg.estado = 1 LIMIT 1");
	$row_emp_cont = $list_emp_cont->fetch_assoc();
	$pref_empresa_contacto = isset($row_emp_cont['valor']) && $row_emp_cont['valor'] == 'Kurax' ? 'Kurax':'AT';

	$sel_query = $mysqli->query(
		"
		SELECT
		c.empresa_suscribe_id,
		rs1.nombre AS empresa_at1,
		rs2.nombre AS empresa_at2,
		c.detalle_servicio,
		c.plazo_id,
		tp.nombre AS plazo,
		CONCAT(c.periodo_numero, ' ', p.nombre) AS periodo,
		concat(IFNULL(per.nombre, ''),' ', IFNULL(per.apellido_paterno, '')) AS usuario_creacion,
		per.correo AS usuario_creacion_correo,
		c.fecha_inicio,
		ar.nombre AS area_creacion,
		c.check_gerencia_interno,
		c.fecha_atencion_gerencia_interno,
		c.aprobacion_gerencia_interno,
		co.sigla AS sigla_correlativo,
		c.codigo_correlativo,
		c.observaciones,
		c.created_at,
		c.codigo_correlativo,
		c.gerente_area_id,
		c.gerente_area_nombre,
		c.gerente_area_email,
		CONCAT(IFNULL(peg.nombre, ''),' ',IFNULL(peg.apellido_paterno, ''),' ',IFNULL(peg.apellido_materno, '')) AS nombre_del_gerente_area,
		peg.correo AS email_del_gerente_area
	FROM 
		cont_contrato c
		LEFT JOIN cont_tipo_plazo tp ON c.plazo_id = tp.id
		LEFT JOIN cont_periodo p ON c.periodo = p.id
		INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
		INNER JOIN tbl_personal_apt per ON u.personal_id = per.id
		INNER JOIN tbl_areas ar ON per.area_id = ar.id
		INNER JOIN tbl_razon_social rs1 ON c.empresa_suscribe_id = rs1.id
		INNER JOIN tbl_razon_social rs2 ON c.empresa_grupo_at_2 = rs2.id
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
		LEFT JOIN tbl_usuarios ug ON c.gerente_area_id = ug.id
		LEFT JOIN tbl_personal_apt peg ON ug.personal_id = peg.id
	WHERE c.tipo_contrato_id = 7 AND	c.contrato_id = '".$contrato_id."'
		");

	$body = "";
	$body .= '<html>';
	$correos_add  = [];
	$correo_abogado = '';
	while($sel = $sel_query->fetch_assoc())
	{
	

		$sigla_correlativo = $sel['sigla_correlativo'];
		$codigo_correlativo = $sel['codigo_correlativo'];

		$plazo_id = $sel["plazo_id"];
		$plazo = $sel["plazo"];

		$date = date_create($sel["fecha_inicio"]);
		$fecha_inicio = date_format($date,"Y-m-d");

		$periodo = $sel["periodo"];

		$gerente_area_id = trim($sel["gerente_area_id"]);
		array_push($correos_add, $sel["usuario_creacion_correo"]);
		if (empty($gerente_area_id)) {
			$gerente_area_nombre = trim($sel["gerente_area_nombre"]);
			$gerente_area_email = trim($sel["gerente_area_email"]);
		} else {
			$gerente_area_nombre = trim($sel["nombre_del_gerente_area"]);
			$gerente_area_email = trim($sel["email_del_gerente_area"]);
		}

		
		$body .= '<div>';
			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
				$body .= '<tbody>';
					$body .= '<tr>';
						$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
							$body .= 'DATOS GENERALES';
						$body .= '</th>';
					$body .= '</tr>';
					$body .= '<tr>';
						$body .= '<td style="background-color:#ffffdd;width:300px"><b>Empresa Grupo '.$pref_empresa_contacto.' 1</b></td>';
						$body .= '<td>'.$sel["empresa_at1"].'</td>';
					$body .= '</tr>';
					$body .= '<tr>';
						$body .= '<td style="background-color:#ffffdd;width:300px"><b>Empresa Grupo '.$pref_empresa_contacto.' 2</b></td>';
						$body .= '<td>'.$sel["empresa_at2"].'</td>';
					$body .= '</tr>';
					$body .= '<tr>';
						$body .= '<td style="background-color: #ffffdd"><b>Responsable de Área:</b></td>';
						$body .= '<td>' . $gerente_area_nombre . '</td>';
					$body .= '</tr>';
					$body .= '<tr>';
					$body .= '<td style="background-color:#ffffdd;width:300px"><b>Registrado por</b></td>';
					$body .= '<td>'.$sel["usuario_creacion"].'</td>';
				$body .= '</tr>';
					$body .= '<tr>';
						$body .= '<td style="background-color:#ffffdd;width:300px"><b>Fecha de Registro</b></td>';
						$body .= '<td>'.$sel["created_at"].'</td>';
					$body .= '</tr>';
				$body .= '</tbody>';
			$body .= '</table>';
		$body .= '</div>';

		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$sel_query_rl = $mysqli->query("
		SELECT 
			rl.id, 
			rl.dni_representante, 
			rl.nombre_representante, 
			rl.nro_cuenta_detraccion,
			rl.id_banco, 
			b.nombre as banco_representante, 
			rl.nro_cuenta, 
			rl.nro_cci, 
			rl.vigencia_archivo_id,
			rl.dni_archivo_id
		FROM 
			cont_representantes_legales rl
			LEFT JOIN tbl_bancos b on b.id = rl.id_banco
		WHERE 
			rl.contrato_id = $contrato_id"
		);
		$c = 0;
		while($row_rl = $sel_query_rl->fetch_assoc()){
			$c = $c + 1; 

			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
				$body .= '<tr>';
					$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
						$body .= 'DATOS DE LA CUENTA BANCARIA #' . $c;
					$body .= '</th>';
				$body .= '</tr>';
				$body .= '<tr>';
					$body .= '<td style="background-color:#ffffdd;width:300px;"><b>Banco</b></td>';
					$body .= '<td>' . $row_rl["banco_representante"] . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
					$body .= '<td style="background-color:#ffffdd;"><b>Número de cuenta bancaria</b></td>';
					$body .= '<td>' . $row_rl["nro_cuenta"] . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
					$body .= '<td style="background-color:#ffffdd;"><b>Número de CCI</b></td>';
					$body .= '<td>' . $row_rl["nro_cci"] . '</td>';
				$body .= '</tr>';
			$body .= '</table>';
		}
		

		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$body .= '<div>';
			$body .= '<div class="form-group">';
				$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<tbody>';
						
						$body .= '<tr>';
							$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
								$body .= 'OBJETO DEL CONTRATO';
							$body .= '</th>';
						$body .= '</tr>';
						$body .= '<tr>';
							$body .= '<td style="background-color:#ffffdd;width:300px"><b>Objeto</b></td>';
							$body .= '<td>'.$sel["detalle_servicio"].'</td>';
						$body .= '</tr>';

					$body .= '</tbody>';
				$body .= '</table>';
			$body .= '</div>';
		$body .= '</div>';

		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';
		
		$body .= '<div>';
			$body .= '<div class="form-group">';
				$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<tbody>';
						
						$body .= '<tr>';
							$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
								$body .= 'PLAZO DEL CONTRATO';
							$body .= '</th>';
						$body .= '</tr>';

						$body .= '<tr>';
							$body .= '<td style="background-color: #ffffdd"><b>Plazo</b></td>';
							$body .= '<td>' . $plazo . '</td>';
						$body .= '</tr>';

						if($plazo_id == 1) {

						$body .= '<tr>';
							$body .= '<td style="background-color: #ffffdd"><b>Periodo:</b></td>';
							$body .= '<td>'.$sel["periodo"].'</td>';
						$body .= '</tr>';

						}

						$body .= '<tr>';
							$body .= '<td style="background-color:#ffffdd;width:300px"><b>Fecha de inicio</b></td>';
							$body .= '<td>'.$fecha_inicio.'</td>';
						$body .= '</tr>';

					$body .= '</tbody>';
				$body .= '</table>';
			$body .= '</div>';
		$body .= '</div>';

		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$sql_contraprestacion = "
		SELECT 
			c.id,
			c.moneda_id,
			m.nombre AS tipo_moneda,
			m.simbolo AS tipo_moneda_simbolo,
			c.subtotal,
			c.igv,
			c.monto,
			c.forma_pago_detallado,
			c.tipo_comprobante_id,
			t.nombre AS tipo_comprobante,
			c.plazo_pago
		FROM 
			cont_contraprestacion c
			INNER JOIN tbl_moneda m ON c.moneda_id = m.id
			INNER JOIN cont_tipo_comprobante t ON c.tipo_comprobante_id = t.id
		WHERE 
			c.contrato_id = $contrato_id
		";

		$query = $mysqli->query($sql_contraprestacion);
	
		$contador_contraprestacion = 1;
		while($row = $query->fetch_assoc()){
			$contraprestacion_id = $row["id"];
			$tipo_moneda = $row["tipo_moneda"];
			$tipo_moneda_simbolo = $row["tipo_moneda_simbolo"];
			$subtotal = $tipo_moneda_simbolo.' '.number_format($row["subtotal"], 2, '.', ',');
			$igv = $tipo_moneda_simbolo.' '.number_format($row["igv"], 2, '.', ',');
			$monto = $tipo_moneda_simbolo.' '.number_format($row["monto"], 2, '.', ',');
			$forma_pago_detallado = $row["forma_pago_detallado"];
			$tipo_comprobante = $row["tipo_comprobante"];
			$plazo_pago = $row["plazo_pago"];

			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
				$body .= '<tr>';
					$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
						$body .= 'CONTRAPRESTACIÓN # ' . $contador_contraprestacion;
					$body .= '</th>';
				$body .= '</tr>';
				$body .= '<tr>';
					$body .= '<td style="background-color:#ffffdd;width:300px;"><b>Tipo de moneda</b></td>';
					$body .= '<td>' . $tipo_moneda  . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
					$body .= '<td style="background-color:#ffffdd;"><b>Subtotal</b></td>';
					$body .= '<td>' . $subtotal . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
					$body .= '<td style="background-color:#ffffdd;"><b>IGV</b></td>';
					$body .= '<td>' . $igv . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
					$body .= '<td style="background-color:#ffffdd;"><b>Monto Bruto</b></td>';
					$body .= '<td>' . $monto . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
					$body .= '<td style="background-color:#ffffdd;"><b>Forma de pago</b></td>';
					$body .= '<td>' . $forma_pago_detallado . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
					$body .= '<td style="background-color:#ffffdd;"><b>Tipo de comprobante a emitir</b></td>';
					$body .= '<td>' . $tipo_comprobante . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
					$body .= '<td style="background-color:#ffffdd;"><b>Plazo de Pago</b></td>';
					$body .= '<td>' . $plazo_pago . '</td>';
				$body .= '</tr>';
			$body .= '</table>';
			$body .= '<br>';

			$contador_contraprestacion++;
		}
		
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

	
		$body .= '<div>';
			$body .= '<div class="form-group">';
				$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<tbody>';
						
						$body .= '<tr>';
							$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
								$body .= 'OBSERVACIONES';
							$body .= '</th>';
						$body .= '</tr>';
						$body .= '<tr>';
							$body .= '<td style="background-color:#ffffdd;width:300px"><b>Observaciones</b></td>';
							$body .= '<td>'.$sel["observaciones"].'</td>';
						$body .= '</tr>';

					$body .= '</tbody>';
				$body .= '</table>';
			$body .= '</div>';
		$body .= '</div>';

		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$body .= '<div style="width: 600px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$host.'/?sec_id=contrato&sub_sec_id=detalle_solicitud_interno&id='.$contrato_id.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
				$body .= '<b>Ver Solicitud</b>';
		    $body .= '</a>';
		$body .= '</div>';

		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

	}
		$body .= '</html>';
		$body .= "";

	
	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_confirmacion_solicitud_contrato_interno($correos_add);

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];
	
	$request = [
		//"subject" => "CONFIRMACIÓN DE SOLICITUD PROVEEDOR: ".$proveedor_razon_social_titulo. " - RUC " .$proveedor_ruc,
		"subject" => "Gestion - Sistema Contratos - Confirmación de Solicitud de Contrato Interno: CÓD - " .$sigla_correlativo.$codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];


	$mail = new PHPMailer(true);

	try 
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;        
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc) 
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc) 
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->send();
		//return true;

	} 
	catch (Exception $e) 
	{
		
			$resultado = $mail->ErrorInfo;
			//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
			//return false;
			echo json_encode($resultado);
		
		
	}
}

function send_email_solicitud_contrato_interno_confirmacion_gerencia($contrato_id)
{
	include("db_connect.php");
	include("sys_login.php");

	$host= $_SERVER["HTTP_HOST"];

	$list_emp_cont = $mysqli->query("SELECT tpg.valor FROM tbl_parametros_generales tpg WHERE tpg.codigo = 'empresa_contacto_de_contratos' AND tpg.estado = 1 LIMIT 1");
	$row_emp_cont = $list_emp_cont->fetch_assoc();
	$pref_empresa_contacto = isset($row_emp_cont['valor']) && $row_emp_cont['valor'] == 'Kurax' ? 'Kurax':'AT';

	$sel_query = $mysqli->query(
		"
		SELECT
		c.empresa_suscribe_id,
		rs1.nombre AS empresa_at1,
		rs2.nombre AS empresa_at2,
		c.detalle_servicio,
		c.plazo_id,
		tp.nombre AS plazo,
		CONCAT(c.periodo_numero, ' ', p.nombre) AS periodo,
		concat(IFNULL(per.nombre, ''),' ', IFNULL(per.apellido_paterno, '')) AS usuario_creacion,
		per.correo AS usuario_creacion_correo,
		c.fecha_inicio,
		ar.nombre AS area_creacion,
		c.check_gerencia_interno,
		c.fecha_atencion_gerencia_interno,
		c.aprobacion_gerencia_interno,
		co.sigla AS sigla_correlativo,
		c.codigo_correlativo,
		c.observaciones,
		c.created_at,
		c.codigo_correlativo,
		c.gerente_area_id,
		c.gerente_area_nombre,
		c.gerente_area_email,
		CONCAT(IFNULL(peg.nombre, ''),' ',IFNULL(peg.apellido_paterno, ''),' ',IFNULL(peg.apellido_materno, '')) AS nombre_del_gerente_area,
		peg.correo AS email_del_gerente_area
	FROM 
		cont_contrato c
		LEFT JOIN cont_tipo_plazo tp ON c.plazo_id = tp.id
		LEFT JOIN cont_periodo p ON c.periodo = p.id
		INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
		INNER JOIN tbl_personal_apt per ON u.personal_id = per.id
		INNER JOIN tbl_areas ar ON per.area_id = ar.id
		INNER JOIN tbl_razon_social rs1 ON c.empresa_suscribe_id = rs1.id
		INNER JOIN tbl_razon_social rs2 ON c.empresa_grupo_at_2 = rs2.id
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
		LEFT JOIN tbl_usuarios ug ON c.gerente_area_id = ug.id
		LEFT JOIN tbl_personal_apt peg ON ug.personal_id = peg.id
	WHERE c.tipo_contrato_id = 7 AND	c.contrato_id = '".$contrato_id."'
		");

	$body = "";
	$body .= '<html>';

	while($sel = $sel_query->fetch_assoc())
	{
	

		$sigla_correlativo = $sel['sigla_correlativo'];
		$codigo_correlativo = $sel['codigo_correlativo'];

		$plazo_id = $sel["plazo_id"];
		$plazo = $sel["plazo"];

		$date = date_create($sel["fecha_inicio"]);
		$fecha_inicio = date_format($date,"Y-m-d");

		$periodo = $sel["periodo"];

		$gerente_area_id = trim($sel["gerente_area_id"]);

		if (empty($gerente_area_id)) {
			$gerente_area_nombre = trim($sel["gerente_area_nombre"]);
			$gerente_area_email = trim($sel["gerente_area_email"]);
		} else {
			$gerente_area_nombre = trim($sel["nombre_del_gerente_area"]);
			$gerente_area_email = trim($sel["email_del_gerente_area"]);
		}

		
		$body .= '<div>';
			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
				$body .= '<tbody>';
					$body .= '<tr>';
						$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
							$body .= 'DATOS GENERALES';
						$body .= '</th>';
					$body .= '</tr>';
					$body .= '<tr>';
						$body .= '<td style="background-color:#ffffdd;width:300px"><b>Empresa Grupo '.$pref_empresa_contacto.' 1</b></td>';
						$body .= '<td>'.$sel["empresa_at1"].'</td>';
					$body .= '</tr>';
					$body .= '<tr>';
						$body .= '<td style="background-color:#ffffdd;width:300px"><b>Empresa Grupo '.$pref_empresa_contacto.' 2</b></td>';
						$body .= '<td>'.$sel["empresa_at2"].'</td>';
					$body .= '</tr>';
					$body .= '<tr>';
						$body .= '<td style="background-color: #ffffdd"><b>Responsable de Área:</b></td>';
						$body .= '<td>' . $gerente_area_nombre . '</td>';
					$body .= '</tr>';
					$body .= '<tr>';
						$body .= '<td style="background-color:#ffffdd;width:300px"><b>Registrado por</b></td>';
						$body .= '<td>'.$sel["usuario_creacion"].'</td>';
					$body .= '</tr>';
					$body .= '<tr>';
						$body .= '<td style="background-color:#ffffdd;width:300px"><b>Fecha de Registro</b></td>';
						$body .= '<td>'.$sel["created_at"].'</td>';
					$body .= '</tr>';
				$body .= '</tbody>';
			$body .= '</table>';
		$body .= '</div>';

		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$sel_query_rl = $mysqli->query("
		SELECT 
			rl.id, 
			rl.dni_representante, 
			rl.nombre_representante, 
			rl.nro_cuenta_detraccion,
			rl.id_banco, 
			b.nombre as banco_representante, 
			rl.nro_cuenta, 
			rl.nro_cci, 
			rl.vigencia_archivo_id,
			rl.dni_archivo_id
		FROM 
			cont_representantes_legales rl
			LEFT JOIN tbl_bancos b on b.id = rl.id_banco
		WHERE 
			rl.contrato_id = $contrato_id"
		);
		$c = 0;
		while($row_rl = $sel_query_rl->fetch_assoc()){
			$c = $c + 1; 

			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
				$body .= '<tr>';
					$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
						$body .= 'DATOS DE LA CUENTA BANCARIA #' . $c;
					$body .= '</th>';
				$body .= '</tr>';
				$body .= '<tr>';
					$body .= '<td style="background-color:#ffffdd;width:300px;"><b>Banco</b></td>';
					$body .= '<td>' . $row_rl["banco_representante"] . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
					$body .= '<td style="background-color:#ffffdd;"><b>Número de cuenta bancaria</b></td>';
					$body .= '<td>' . $row_rl["nro_cuenta"] . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
					$body .= '<td style="background-color:#ffffdd;"><b>Número de CCI</b></td>';
					$body .= '<td>' . $row_rl["nro_cci"] . '</td>';
				$body .= '</tr>';
			$body .= '</table>';
		}
		

		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$body .= '<div>';
			$body .= '<div class="form-group">';
				$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<tbody>';
						
						$body .= '<tr>';
							$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
								$body .= 'OBJETO DEL CONTRATO';
							$body .= '</th>';
						$body .= '</tr>';
						$body .= '<tr>';
							$body .= '<td style="background-color:#ffffdd;width:300px"><b>Objeto</b></td>';
							$body .= '<td>'.$sel["detalle_servicio"].'</td>';
						$body .= '</tr>';

					$body .= '</tbody>';
				$body .= '</table>';
			$body .= '</div>';
		$body .= '</div>';

		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';
		
		$body .= '<div>';
			$body .= '<div class="form-group">';
				$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<tbody>';
						
						$body .= '<tr>';
							$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
								$body .= 'PLAZO DEL CONTRATO';
							$body .= '</th>';
						$body .= '</tr>';

						$body .= '<tr>';
							$body .= '<td style="background-color: #ffffdd"><b>Plazo</b></td>';
							$body .= '<td>' . $plazo . '</td>';
						$body .= '</tr>';

						if($plazo_id == 1) {

						$body .= '<tr>';
							$body .= '<td style="background-color: #ffffdd"><b>Periodo:</b></td>';
							$body .= '<td>'.$sel["periodo"].'</td>';
						$body .= '</tr>';

						}

						$body .= '<tr>';
							$body .= '<td style="background-color:#ffffdd;width:300px"><b>Fecha de inicio</b></td>';
							$body .= '<td>'.$fecha_inicio.'</td>';
						$body .= '</tr>';

					$body .= '</tbody>';
				$body .= '</table>';
			$body .= '</div>';
		$body .= '</div>';

		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$sql_contraprestacion = "
		SELECT 
			c.id,
			c.moneda_id,
			m.nombre AS tipo_moneda,
			m.simbolo AS tipo_moneda_simbolo,
			c.subtotal,
			c.igv,
			c.monto,
			c.forma_pago_detallado,
			c.tipo_comprobante_id,
			t.nombre AS tipo_comprobante,
			c.plazo_pago
		FROM 
			cont_contraprestacion c
			INNER JOIN tbl_moneda m ON c.moneda_id = m.id
			INNER JOIN cont_tipo_comprobante t ON c.tipo_comprobante_id = t.id
		WHERE 
			c.contrato_id = $contrato_id
		";

		$query = $mysqli->query($sql_contraprestacion);
	
		$contador_contraprestacion = 1;
		while($row = $query->fetch_assoc()){
			$contraprestacion_id = $row["id"];
			$tipo_moneda = $row["tipo_moneda"];
			$tipo_moneda_simbolo = $row["tipo_moneda_simbolo"];
			$subtotal = $tipo_moneda_simbolo.' '.number_format($row["subtotal"], 2, '.', ',');
			$igv = $tipo_moneda_simbolo.' '.number_format($row["igv"], 2, '.', ',');
			$monto = $tipo_moneda_simbolo.' '.number_format($row["monto"], 2, '.', ',');
			$forma_pago_detallado = $row["forma_pago_detallado"];
			$tipo_comprobante = $row["tipo_comprobante"];
			$plazo_pago = $row["plazo_pago"];

			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
				$body .= '<tr>';
					$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
						$body .= 'CONTRAPRESTACIÓN # ' . $contador_contraprestacion;
					$body .= '</th>';
				$body .= '</tr>';
				$body .= '<tr>';
					$body .= '<td style="background-color:#ffffdd;width:300px;"><b>Tipo de moneda</b></td>';
					$body .= '<td>' . $tipo_moneda  . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
					$body .= '<td style="background-color:#ffffdd;"><b>Subtotal</b></td>';
					$body .= '<td>' . $subtotal . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
					$body .= '<td style="background-color:#ffffdd;"><b>IGV</b></td>';
					$body .= '<td>' . $igv . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
					$body .= '<td style="background-color:#ffffdd;"><b>Monto Bruto</b></td>';
					$body .= '<td>' . $monto . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
					$body .= '<td style="background-color:#ffffdd;"><b>Forma de pago</b></td>';
					$body .= '<td>' . $forma_pago_detallado . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
					$body .= '<td style="background-color:#ffffdd;"><b>Tipo de comprobante a emitir</b></td>';
					$body .= '<td>' . $tipo_comprobante . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
					$body .= '<td style="background-color:#ffffdd;"><b>Plazo de Pago</b></td>';
					$body .= '<td>' . $plazo_pago . '</td>';
				$body .= '</tr>';
			$body .= '</table>';
			$body .= '<br>';

			$contador_contraprestacion++;
		}
		
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

	
		$body .= '<div>';
			$body .= '<div class="form-group">';
				$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<tbody>';
						
						$body .= '<tr>';
							$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
								$body .= 'OBSERVACIONES';
							$body .= '</th>';
						$body .= '</tr>';
						$body .= '<tr>';
							$body .= '<td style="background-color:#ffffdd;width:300px"><b>Observaciones</b></td>';
							$body .= '<td>'.$sel["observaciones"].'</td>';
						$body .= '</tr>';

					$body .= '</tbody>';
				$body .= '</table>';
			$body .= '</div>';
		$body .= '</div>';

	
		$sql_obs = "SELECT 
		o.observaciones,
		o.created_at
		FROM cont_observaciones o
		WHERE o.contrato_id = " . $contrato_id . "
		AND o.status = 1
		AND o.user_created_id = 3218
		ORDER BY o.created_at DESC";
		
		$query_obs = $mysqli->query($sql_obs);
		$row_count = $query_obs->num_rows;
		if ($row_count > 0) 
		{
			$body .= '<div>';
				$body .= '<br>';
			$body .= '</div>';

			$body .= '<div>';
				$body .= '<div class="form-group">';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
						$body .= '<tbody>';
							
							$body .= '<tr>';
								$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
									$body .= 'OBSERVACION DE GERENCIA';
								$body .= '</th>';
							$body .= '</tr>';
							$i = 1;
							while ($row = $query_obs->fetch_assoc()) {
								$body .= '<tr>';
									$body .= '<td style="background-color:#ffffdd;width:300px"><b>Observación #'.$i.'</b></td>';
									$body .= '<td>'.$row["observaciones"].'</td>';
								$body .= '</tr>';
								$body .= '<tr>';
									$body .= '<td style="background-color:#ffffdd;width:300px"><b>Fecha </b></td>';
									$body .= '<td>'.$row["created_at"].'</td>';
								$body .= '</tr>';
								$i++;
							}

							

						$body .= '</tbody>';
					$body .= '</table>';
				$body .= '</div>';
			$body .= '</div>';
			
		}

		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$body .= '<div style="width: 600px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$host.'/?sec_id=contrato&sub_sec_id=detalle_solicitud_interno&id='.$contrato_id.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
				$body .= '<b>Ver Solicitud</b>';
		    $body .= '</a>';
		$body .= '</div>';

		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

	}
		$body .= '</html>';
		$body .= "";

	
	$correos_add  = [];
	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_confirmacion_solicitud_contrato_interno($correos_add);

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];
	
	$request = [
		//"subject" => "CONFIRMACIÓN DE SOLICITUD PROVEEDOR: ".$proveedor_razon_social_titulo. " - RUC " .$proveedor_ruc,
		"subject" => "Gestion - Sistema Contratos - Confirmación de Solicitud de Contrato Interno (Observación Corregida): CÓD - " .$sigla_correlativo.$codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];


	$mail = new PHPMailer(true);

	try 
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;        
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc) 
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc) 
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->send();
		//return true;

	} 
	catch (Exception $e) 
	{
		
			$resultado = $mail->ErrorInfo;
			//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
			//return false;
			echo json_encode($resultado);
		
		
	}
}

function send_email_solicitud_contrato_interno($contrato_id)
{

	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$list_emp_cont = $mysqli->query("SELECT tpg.valor FROM tbl_parametros_generales tpg WHERE tpg.codigo = 'empresa_contacto_de_contratos' AND tpg.estado = 1 LIMIT 1");
	$row_emp_cont = $list_emp_cont->fetch_assoc();
	$pref_empresa_contacto = isset($row_emp_cont['valor']) && $row_emp_cont['valor'] == 'Kurax' ? 'Kurax':'AT';

	$nombre_tienda = "";
	$empresa_suscribe_id = 0;
	$correos_add = [];
	$sel_query = $mysqli->query(
		"
		SELECT
		c.empresa_suscribe_id,
		c.empresa_grupo_at_2,
		rs1.nombre AS empresa_at1,
		rs2.nombre AS empresa_at2,
		c.detalle_servicio,
		c.plazo_id,
		tp.nombre AS plazo,
		CONCAT(c.periodo_numero, ' ', p.nombre) AS periodo,
		concat(IFNULL(per.nombre, ''),' ', IFNULL(per.apellido_paterno, '')) AS usuario_creacion,
		per.correo AS usuario_creacion_correo,
		c.fecha_inicio,
		ar.nombre AS area_creacion,
		c.check_gerencia_interno,
		c.fecha_atencion_gerencia_interno,
		c.aprobacion_gerencia_interno,
		co.sigla AS sigla_correlativo,
		c.codigo_correlativo,
		c.observaciones,
		c.created_at,
		c.gerente_area_id,
		c.gerente_area_nombre,
		c.gerente_area_email,
		CONCAT(IFNULL(peg.nombre, ''),' ',IFNULL(peg.apellido_paterno, ''),' ',IFNULL(peg.apellido_materno, '')) AS nombre_del_gerente_area,
		peg.correo AS email_del_gerente_area
	FROM 
		cont_contrato c
		LEFT JOIN cont_tipo_plazo tp ON c.plazo_id = tp.id
		LEFT JOIN cont_periodo p ON c.periodo = p.id
		INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
		INNER JOIN tbl_personal_apt per ON u.personal_id = per.id
		INNER JOIN tbl_areas ar ON per.area_id = ar.id
		INNER JOIN tbl_razon_social rs1 ON c.empresa_suscribe_id = rs1.id
		INNER JOIN tbl_razon_social rs2 ON c.empresa_grupo_at_2 = rs2.id
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
		LEFT JOIN tbl_usuarios ug ON c.gerente_area_id = ug.id
		LEFT JOIN tbl_personal_apt peg ON ug.personal_id = peg.id

	WHERE c.tipo_contrato_id = 7 AND	c.contrato_id = '".$contrato_id."'
		");

	$body = "";
	$body .= '<html>';

	$usuario_creacion_correo = '';
	$correo_abogado = '';
	while($sel = $sel_query->fetch_assoc())
	{
		$id_empresa_grupo_at_1 = $sel['empresa_suscribe_id'];
		$id_empresa_grupo_at_2 = $sel['empresa_grupo_at_2'];
		$empresa_at1 = $sel['empresa_at1'];
		$empresa_at2 = $sel['empresa_at2'];
		

		$sigla_correlativo = $sel['sigla_correlativo'];
		$codigo_correlativo = $sel['codigo_correlativo'];

		$plazo_id = $sel["plazo_id"];
		$plazo = $sel["plazo"];
		
		$fecha_atencion_gerencia_interno = $sel['fecha_atencion_gerencia_interno'];
		$aprobacion_gerencia_interno = $sel['aprobacion_gerencia_interno'];

		$usuario_creacion_correo = $sel['usuario_creacion_correo'];

		$date = date_create($sel["fecha_inicio"]);
		$fecha_inicio_contrato = date_format($date, "Y/m/d");

		$gerente_area_id = trim($sel["gerente_area_id"]);

		if (empty($gerente_area_id)) {
			$gerente_area_nombre = trim($sel["gerente_area_nombre"]);
			$gerente_area_email = trim($sel["gerente_area_email"]);
		} else {
			$gerente_area_nombre = trim($sel["nombre_del_gerente_area"]);
			$gerente_area_email = trim($sel["email_del_gerente_area"]);
		}

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

		$body .= '<thead>';
		
		if(!is_null($fecha_atencion_gerencia_interno) && $aprobacion_gerencia_interno == 0)
		{
			$body .= '<tr>';
				$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
					$body .= '<b>Solicitud Rechazada</b>';
				$body .= '</th>';
			$body .= '</tr>';
		}
		else
		{
			$body .= '<tr>';
				$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
					$body .= '<b>Nueva solicitud</b>';
				$body .= '</th>';
			$body .= '</tr>';
		}

		$body .= '</thead>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Empresa Grupo '.$pref_empresa_contacto.' 1:</b></td>';
			$body .= '<td>'.$sel["empresa_at1"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd;"><b>Empresa Grupo '.$pref_empresa_contacto.' 2:</b></td>';
			$body .= '<td>'.$sel["empresa_at2"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Plazo</b></td>';
			$body .= '<td>' . $plazo . '</td>';
		$body .= '</tr>';

		if($plazo_id == 1) {

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Periodo:</b></td>';
			$body .= '<td>'.$sel["periodo"].'</td>';
		$body .= '</tr>';

		}

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Usuario solicitante:</b></td>';
			$body .= '<td>'.$sel["usuario_creacion"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Responsable de Área:</b></td>';
			$body .= '<td>' . $gerente_area_nombre . '</td>';
		$body .= '</tr>';
	
		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha inicio:</b></td>';
			$body .= '<td>'.$fecha_inicio_contrato.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Detalle servicio:</b></td>';
			$body .= '<td>'.$sel["detalle_servicio"].'</td>';
		$body .= '</tr>';

		if(!is_null($fecha_atencion_gerencia_interno) && $aprobacion_gerencia_interno == 0)
		{
			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd"><b>Rechazado por:</b></td>';
				$body .= '<td>Lourdes Britto</td>';
			$body .= '</tr>';
		}
		else if(!is_null($fecha_atencion_gerencia_interno) && $aprobacion_gerencia_interno == 1)
		{
			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd"><b>Aprobado por:</b></td>';
				$body .= '<td>Lourdes Britto</td>';
			$body .= '</tr>';
		}

		$body .= '</table>';
		$body .= '</div>';

	}
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$host.'/?sec_id=contrato&sub_sec_id=detalle_solicitud_interno&id='.$contrato_id.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
				$body .= '<b>Ver Solicitud</b>';
		    $body .= '</a>';
		$body .= '</div>';

		$body .= '</html>';
		$body .= "";

	$titulo_email = "";

	if(!is_null($fecha_atencion_gerencia_interno) && $aprobacion_gerencia_interno == 0)
	{
		array_push($correos_add,$usuario_creacion_correo);
		$titulo_email = "Gestion - Sistema Contratos - Solicitud de Contrato Interno Rechazada: CÓD - ";
	}
	else
	{
		if ($id_empresa_grupo_at_1 != 0) {
			$sql_contador_tesorero = "
			SELECT 
				p.correo
			FROM
				cont_usuarios_razones_sociales urs
				INNER JOIN tbl_usuarios u ON urs.user_id = u.id
				INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
			WHERE
				urs.razon_social_id = ".$id_empresa_grupo_at_1."
				AND p.estado = 1  AND u.estado = 1 
			";
			$sel_query = $mysqli->query($sql_contador_tesorero);

			$row_count = $sel_query->num_rows;
			if ($row_count > 0) {
				while($sel = $sel_query->fetch_assoc()){
					if (!Empty($sel['correo'])) {
						array_push($correos_add,$sel['correo']);
					}
				}
			}
		}

		if ($id_empresa_grupo_at_2 != 0) {
			$sql_contador_tesorero = "
			SELECT 
				p.correo
			FROM
				cont_usuarios_razones_sociales urs
				INNER JOIN tbl_usuarios u ON urs.user_id = u.id
				INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
			WHERE
				urs.razon_social_id = ".$id_empresa_grupo_at_2."
				AND p.estado = 1  AND u.estado = 1 
			";
			$sel_query = $mysqli->query($sql_contador_tesorero);

			$row_count = $sel_query->num_rows;
			if ($row_count > 0) {
				while($sel = $sel_query->fetch_assoc()){
					if (!Empty($sel['correo'])) {
						array_push($correos_add,$sel['correo']);
					}
				}
			}
		}

		$titulo_email = "Gestion - Sistema Contratos - Nueva Solicitud de Contrato Interno: Código - ";
	}

	$sel_query = $mysqli->query("SELECT email FROM cont_contrato_correos WHERE status = 1 AND contrato_id = '".$contrato_id."'");
	while($row = $sel_query->fetch_assoc()){
		array_push($correos_add,$row['email']);
	}

	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_solicitud_contrato_interno($correos_add);

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];

	if (env('SEND_EMAIL') == 'produccion') {
		if ( !(empty($gerente_area_email)) ) {
			if ( sec_contrato_nuevo_interno_is_valid_email($gerente_area_email) ) {
				array_push($cc, $gerente_area_email );
			}
		}
	}

	$request = [
		"subject" => $titulo_email.$sigla_correlativo.$codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try 
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;        
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc) 
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc) 
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();
		//return true;

	} 
	catch (Exception $e) 
	{
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}
}


if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_contrato_interno") {

	//echo "<pre>";print_r($_POST);echo "</pre>";die();
	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	$array_representantes = $_POST["rr_representantes"];
	$data_representantes = json_decode($_POST["rr_representantes"]);
	$correos_adjuntos = isset($_POST["array_correos_adjuntos"]) ? json_decode($_POST['array_correos_adjuntos'],true): [];
	$id_tipo_contrato = 7; ///contratos internos

	if ($usuario_id == null) {
		$result["status"] = 404;
		$result["message"] = "Su sesion ha caducado, Ingrese de nuevo al sistema.";
		$result["result"] = '';
		echo json_encode($result);
		exit();
	}


	$errors = "";

	$query_update = "UPDATE cont_correlativo SET numero = numero + 1 WHERE tipo_contrato = ".$id_tipo_contrato." AND status = 1";
	$mysqli->query($query_update);
	if($mysqli->error)
	{
		$result["status"] = 404;
		$result["message"] = $mysqli->error;
		$result["result"] = '';
		echo json_encode($result);
		exit();
	}
	else
	{
		$numero_correlativo = "";

		$select_correlativo = 
		"
			SELECT
				tipo_contrato,
				sigla,
				numero,
				status
			FROM
			cont_correlativo
			WHERE tipo_contrato = ".$id_tipo_contrato." AND status = 1 LIMIT 1
		";

		$list_query = $mysqli->query($select_correlativo);

		while($sel = $list_query->fetch_assoc())
		{
			$sigla = $sel["sigla"];
			$numero_correlativo = $sel["numero"];
		}

		$gerente_area_id = trim($_POST["gerente_area_id"]);
		$gerente_area_nombre = '';
		$gerente_area_email = '';

		if ($gerente_area_id == 'A') {
			$gerente_area_id = "NULL";
			$gerente_area_nombre = trim($_POST["nombre_del_gerente_del_area"]);
			$gerente_area_email = trim($_POST["email_del_gerente_del_area"]);
		}

		$plazo_id = trim($_POST["plazo_id"]);
		$periodo_numero = trim($_POST["sec_con_nuevo_periodo_numero"]);
		$periodo = trim($_POST["sec_con_nuevo_periodo"]);
		$fecha_inicio = trim($_POST["sec_con_nuevo_fecha_inicio"]);
		$num_dias_para_alertar_vencimiento = trim($_POST["num_dias_para_alertar_vencimiento"]);
		$alerta_vencimiento_por_fecha_id = trim($_POST["alerta_vencimiento_por_fecha_id"]);
		$fecha_de_la_alerta = "'" . trim($_POST["fecha_de_la_alerta"]) . "'";

		if ($plazo_id == 1) {
			$alerta_vencimiento_por_fecha_id = "NULL";
			$fecha_de_la_alerta = "NULL";
			
		} elseif ($plazo_id == 2) {
			$periodo_numero = "NULL";
			$periodo = "NULL";
			$num_dias_para_alertar_vencimiento = "NULL";
			if ($alerta_vencimiento_por_fecha_id == 0) {
				$alerta_vencimiento_por_fecha_id = "NULL";
				$fecha_de_la_alerta = "NULL";
			}
		}

		$cargo_id_persona_contacto = !Empty($_POST['cargo_id_persona_contacto']) ? $_POST['cargo_id_persona_contacto'] :'0';
		$cargo_id_responsable = !Empty($_POST['cargo_id_responsable']) ? $_POST['cargo_id_responsable'] :'0';
		$cargo_id_aprobante = !Empty($_POST['cargo_id_aprobante']) ? $_POST['cargo_id_aprobante'] :'0';

		$query_insert = "INSERT INTO cont_contrato
		(
			  tipo_contrato_id
			, codigo_correlativo 
			, empresa_suscribe_id
			, empresa_grupo_at_2
			, area_responsable_id
			, persona_responsable_id
			, cargo_id_persona_contacto
			, cargo_id_responsable
			, cargo_id_aprobante
			, etapa_id
			, ruc
			, razon_social
			, check_gerencia_interno
			, detalle_servicio
			, plazo_id
			, periodo_numero
			, periodo
			, fecha_inicio
			, num_dias_para_alertar_vencimiento
			, alerta_vencimiento_por_fecha_id
			, fecha_de_la_alerta
			, observaciones
			, status
			, user_created_id
			, created_at
			, estado_solicitud
			, gerente_area_id
			, gerente_area_nombre
			, gerente_area_email
		)
		VALUES
		(
			" . $_POST["tipo_contrato_id"] . ",
			" . $numero_correlativo . ",
			" . $_POST["sec_con_nuevo_empresa_grupo_at_1"] . ",
			" . $_POST["sec_con_nuevo_empresa_grupo_at_2"] . ",
			0,
			0,
			".$cargo_id_persona_contacto.",
			".$cargo_id_responsable.",
			".$cargo_id_aprobante.",
			1,
			'',
			'',
			'" . $_POST["check_gerencia_interno"] . "',
			'" . $_POST["sec_con_nuevo_detalle_servicio"] . "',
			$plazo_id,
			$periodo_numero,
			$periodo,
			'" . $_POST["sec_con_nuevo_fecha_inicio"] . "',
			$num_dias_para_alertar_vencimiento,
			$alerta_vencimiento_por_fecha_id,
			$fecha_de_la_alerta,
			'" . $_POST["sec_con_nuevo_observaciones"] . "',
			1,
			" . $usuario_id . ",
			'" . $created_at . "',
			1,
			" . $gerente_area_id . ",
			'" . $gerente_area_nombre . "',
			'" . $gerente_area_email . "'
		)";
		//echo "<pre>";print_r($query_insert);echo "</pre>";die();

		$mysqli->query($query_insert);
		$contrato_id = mysqli_insert_id($mysqli);
		$error = '';
		if($mysqli->error){
			$result["status"] = 404;
			$result["message"] = $mysqli->error;
			$result["result"] = '';
			echo json_encode($result);
			exit();
		} else {
			// files
			$path = "/var/www/html/files_bucket/contratos/solicitudes/contratos_internos/";
			if (!is_dir($path)) mkdir($path, 0777, true);

			// INICIO DE CARGAR NUEVOS ANEXOS
			if (isset($_FILES["miarchivo"])) {
			    if($_FILES["miarchivo"]){
			        //Recorre el array de los archivos a subir
			        $h = '';
			        foreach($_FILES["miarchivo"]['tmp_name'] as $key => $tmp_name){
			            //Si el archivo existe
			            if($_FILES["miarchivo"]["name"][$key]){
			                $file_name = $_FILES["miarchivo"]["name"][$key]; 
			                $fuente = $_FILES["miarchivo"]["tmp_name"][$key]; 
							$filesize = $_FILES['miarchivo']['size'][$key];
							//$file_id = $_FILES['miarchivo']['id'][$key];
			                $fileExt = pathinfo($file_name, PATHINFO_EXTENSION);
			                $tipo_archivo = 0;
			                $nombre_tipo_archivo = "";

			                $array_nuevos_files_anexos = $_POST["array_nuevos_files_anexos"];
							$cont = 0;

							$data = json_decode($array_nuevos_files_anexos);
							$ids = '';

							foreach ($data as $value) {
								$result["file_name"] = $file_name . " - data filename: " . $value->nombre_archivo;
								$result["fileSize"] = $filesize . " - data fileSize: " . $value->tamano_archivo;
								$result["fileExt"] = $fileExt . " - data fileExt: " . $value->extension;
								if($value->nombre_archivo == $file_name && $value->tamano_archivo == $filesize && $value->extension == $fileExt){
									$nombre_tipo_archivo = str_replace(' ', '_', $value->tip_doc_nombre);
									$nombre_tipo_archivo = strtoupper($nombre_tipo_archivo);
									$tipo_archivo = $value->id_tip_documento;
								}
							}

			                $nombre_archivo = $contrato_id . "_" . $nombre_tipo_archivo . "_" . date('YmdHis') . "." . $fileExt;
			               	
			                if(!file_exists($path)){
			                    mkdir($path, 0777); //or die("Hubo un error al crear la carpeta");	
			                }
			                $dir=opendir($path);
			                if(move_uploaded_file($fuente, $path.'/'.$nombre_archivo)){	
			                	$comando = "INSERT INTO cont_archivos (
									contrato_id,
									tipo_archivo_id,
									nombre,
									extension,
									size,
									ruta,
									user_created_id,
									created_at)
									VALUES(
									'" . $contrato_id . "',
									'" . $tipo_archivo . "',
									'" . $nombre_archivo . "',
									'" . $fileExt . "',
									'" . $filesize . "',
									'" . $path . "',
									" . $usuario_id . ",
									'" . $created_at . "'
									)";

									$mysqli->query($comando);
									if($mysqli->error){
										$error .= 'Error al guardar el nuevo anexo' . $mysqli->error . $comando;
									}
			                }else{	
			                }
			                closedir($dir); 
			            }
			        }
			    }
		    }
			// FIN DE CARGAR NUEVOS ANEXOS

			//REPRESENTANTES
			foreach($data_representantes as $item)
			{
				$img_id_insert_dni = 0;
				$img_id_insert_vigencia = 0;
				$query_insert_repr = 
				"INSERT INTO cont_representantes_legales (
					contrato_id, 
					dni_representante, 
					nombre_representante, 
					nro_cuenta_detraccion, 
					id_banco, 
					nro_cuenta, 
					nro_cci, 
					vigencia_archivo_id, 
					dni_archivo_id, 
					id_user_created, 
					created_at
				) VALUES (" 
					. $contrato_id . ", '" 
					. $item->dni_representante . "', '" 
					. $item->nombre_representante . "', '" 
					. $item->nro_cuenta_detraccion . "', " 
					. $item->banco . ", '" 
					. $item->nro_cuenta . "', '" 
					. $item->nro_cci . "', " 
					. $img_id_insert_vigencia . ", " 
					. $img_id_insert_dni . ", " 
					. $usuario_id . ", now())";
				$mysqli->query($query_insert_repr);
				$error = '';
				if($mysqli->error){
					$error=$mysqli->error;
					$mensaje=$query_insert_repr;
					$http_code=500;
				}else{
					$http_code=200;
					$mensaje="Solicitud de Proveedor Enviada.";
				}
			}


			// INICIO CONTRAPRESTACIÓN
			$contraprestaciones = $_POST["array_contraprestaciones"];
			$data_contraprestaciones = json_decode($contraprestaciones);
			foreach ($data_contraprestaciones as $contraprestacion) {
				$query_insert = "INSERT INTO cont_contraprestacion
					(
					contrato_id,
					moneda_id,
					forma_pago_id,
					forma_pago_detallado,
					tipo_comprobante_id,
					plazo_pago,
					subtotal,
					igv,
					monto,
					user_created_id,
					created_at)
					VALUES
					(
					" .$contrato_id. ",
					" . $contraprestacion->moneda_id . ",
					0,
					'" . $contraprestacion->forma_pago_detallado . "',
					'" . $contraprestacion->tipo_comprobante . "',
					'" . $contraprestacion->plazo_pago . "',
					'" . $contraprestacion->subtotal . "',
					'" . $contraprestacion->igv . "',
					'" . $contraprestacion->monto . "',
					'" . $usuario_id . "',
					'" . $created_at . "')";

				$mysqli->query($query_insert);
				if($mysqli->error){
					echo $mysqli->error . $query_insert;
				}
			}
			// FIN CONTRAPRESTACIÓN


			///FIN CORREOS

			for ($i=0; $i < count($correos_adjuntos); $i++) { 

				if (!Empty($correos_adjuntos[$i])) {
					$query_insert = "INSERT INTO cont_contrato_correos
					(
					contrato_id,
					email,
					status,
					user_created_id,
					created_at)
					VALUES
					(
					" .$contrato_id. ",
					'" . $correos_adjuntos[$i] . "',
					1,
					'" . $usuario_id . "',
					'" . $created_at . "')";

				$mysqli->query($query_insert);
				}
				
			}

		    if($_POST["check_gerencia_interno"] == 1)
			{
				send_email_confirmacion_solicitud_contrato_interno($contrato_id);
			}
			else
			{
				send_email_solicitud_contrato_interno($contrato_id);
			}
			
			$result["status"] = 200;
			$result["message"] = 'La solicitud de contrato interno fue registrado correctamente';
			$result["result"] = $contrato_id;
			echo json_encode($result);
			exit();						
		}
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="reenviar_correo_contrato_interno") {
	$contrato_id = $_POST['contrato_id'];
	send_email_confirmacion_solicitud_contrato_interno($contrato_id);
	$result["status"] = 200;
	$result["message"] = 'Se ha reenviado el correo exitosamente';
	$result["result"] = '';
	echo json_encode($result);
	exit();	
}

if (isset($_POST["accion"]) && $_POST["accion"]==="reenviar_correo_contrato_interno_gerencia") {
	$contrato_id = $_POST['contrato_id'];
	send_email_solicitud_contrato_interno_confirmacion_gerencia($contrato_id);
	$result["status"] = 200;
	$result["message"] = 'Se ha reenviado el correo exitosamente';
	$result["result"] = '';
	echo json_encode($result);
	exit();	
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_abogados") {

	$query = "SELECT  u.id ,
	CONCAT(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, ''),' ', IFNULL(p.apellido_materno, '')) AS nombre 
	FROM 
		tbl_usuarios u
		INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		INNER JOIN tbl_cargos AS c ON c.id = p.cargo_id
	WHERE 
		u.estado = 1
		AND p.estado = 1
		AND p.area_id IN (33)
	ORDER BY 
		p.nombre ASC,
		p.apellido_paterno ASC";
	$list_query = $mysqli->query($query);
	
	$list = [];
	while ($li = $list_query->fetch_assoc()) {
		$list[] = $li;
	}
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $list;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_contrato_detalle_interno_aprobar_solicitud_gerencia") 
{
	
	$usuario_id = $login?$login['id']:null;

	if ( !((int) $usuario_id > 0) ) {
		$result["http_code"] = 500;
		$result["status"] = "error";
		$result["mensaje"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y hacer click e el boton verde: Aceptar solicitud.";
		echo json_encode($result);
		exit();
	}

	$contrato_id = $_POST["contrato_id"];
	$cont_detalle_interno_aprobacion_gerencia_param = $_POST['cont_detalle_interno_aprobacion_gerencia_param'];

	$query_update = 
	"
		UPDATE cont_contrato 
			SET 
			aprobacion_gerencia_interno = '".$cont_detalle_interno_aprobacion_gerencia_param."',
			fecha_atencion_gerencia_interno = '" .date("Y-m-d H:i:s")."',
			aprobado_por = $usuario_id
		WHERE contrato_id = '" . $contrato_id . "'
	";

	$mysqli->query($query_update);

	send_email_solicitud_contrato_interno($contrato_id);
	
	if($mysqli->error)
	{
		$result["status"] = 404;
		$result["message"] = $mysqli->error;
	}
	else 
	{
		$result["status"] = 200;
		$result["message"] = $cont_detalle_interno_aprobacion_gerencia_param == 1 ? "El contrato interno ha sido aceptado.":"El contrato interno fue rechazado.";
	}

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_cargo_usuario") {

	$usuario_id = $_POST['usuario_id'];
	if ($_POST['type'] == 'persona_contacto'){

		$usuario_id = $login?$login['id']:null;
		$query = "SELECT pe.id, pe.cargo_id FROM tbl_usuarios AS u 
		INNER JOIN tbl_personal_apt AS pe ON pe.id = u.personal_id AND pe.estado = 1
		WHERE u.id = ".$usuario_id;
		$list_query = $mysqli->query($query);
		$data = $list_query->fetch_assoc();

		if(isset($data['cargo_id'])){
			$result["status"] = 200;
			$result["message"] = "Datos obtenidos de gestion.";
			$result["result"] = $data['cargo_id'];
			echo json_encode($result);
			exit();
		}
	
		$result["status"] = 400;
		$result["message"] = "Datos obtenidos de gestion.";
		$result["result"] = 0;
		echo json_encode($result);
		exit();
	}else{
		if(isset( $_POST['usuario_id']) && !($usuario_id == "0" || $usuario_id == "A") ){
			$query = "SELECT pe.id, pe.cargo_id FROM tbl_usuarios AS u 
			INNER JOIN tbl_personal_apt AS pe ON pe.id = u.personal_id AND pe.estado = 1
			WHERE u.id = ".$usuario_id;
			$list_query = $mysqli->query($query);
			$data = $list_query->fetch_assoc();
	
			if(isset($data['cargo_id'])){
				$result["status"] = 200;
				$result["message"] = "Datos obtenidos de gestion.";
				$result["result"] = $data['cargo_id'];
				echo json_encode($result);
				exit();
			}
		
			$result["status"] = 400;
			$result["message"] = "Datos obtenidos de gestion.";
			$result["result"] = 0;
			echo json_encode($result);
			exit();
		}else{
			$result["status"] = 400;
			$result["message"] = "Datos obtenidos de gestion.";
			$result["result"] = 0;
			echo json_encode($result);
			exit();
		}
	}
	
}

function sec_contrato_nuevo_interno_is_valid_email($str) {
	return (false !== filter_var($str, FILTER_VALIDATE_EMAIL));
}
?>