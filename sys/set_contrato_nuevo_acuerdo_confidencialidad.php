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


function send_email_solicitud_acuerdo_confidencialidad($contrato_id)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$nombre_tienda = "";
	$empresa_suscribe_id = 0;
	$sel_query = $mysqli->query("
	SELECT
		c.empresa_suscribe_id,
		rs.nombre AS empresa_suscribe,
		c.ruc AS proveedor_ruc,
		c.razon_social AS proveedor_razon_social,
		c.nombre_comercial AS proveedor_nombre_comercial,
		c.detalle_servicio,
		CONCAT(c.periodo_numero, ' ', p.nombre) AS periodo,
		concat(IFNULL(per.nombre, ''),' ', IFNULL(per.apellido_paterno, '')) AS usuario_creacion,
		per.correo AS usuario_creacion_correo,
		c.fecha_inicio,
		ar.nombre AS area_creacion,
		c.check_gerencia_proveedor,
		c.fecha_atencion_gerencia_proveedor,
		c.aprobacion_gerencia_proveedor,
		co.sigla AS sigla_correlativo,
		c.codigo_correlativo,
		c.gerente_area_id,
		c.gerente_area_nombre,
		c.gerente_area_email,
		CONCAT(IFNULL(peg.nombre, ''),' ',IFNULL(peg.apellido_paterno, ''),' ',IFNULL(peg.apellido_materno, '')) AS nombre_del_gerente_area,
		peg.correo AS email_del_gerente_area,
		CONCAT(IFNULL(puap.nombre, ''),' ',IFNULL(puap.apellido_paterno, ''),' ',IFNULL(puap.apellido_materno, '')) AS aprobado_por,
		puap.correo AS email_del_aprobante
	FROM 
		cont_contrato c
		INNER JOIN cont_periodo p ON c.periodo = p.id
		INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
		INNER JOIN tbl_personal_apt per ON u.personal_id = per.id
		INNER JOIN tbl_areas ar ON per.area_id = ar.id
		INNER JOIN tbl_razon_social rs ON c.empresa_suscribe_id = rs.id
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
		LEFT JOIN tbl_usuarios ug ON c.gerente_area_id = ug.id
		LEFT JOIN tbl_personal_apt peg ON ug.personal_id = peg.id
		LEFT JOIN tbl_usuarios uap ON c.aprobado_por = uap.id
		LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id
	WHERE 
		c.contrato_id = $contrato_id
	");

	$body = "";
	$body .= '<html>';

	$usuario_creacion_correo = '';
	$correos_adicionales = [];

	while($sel = $sel_query->fetch_assoc())
	{
		$empresa_suscribe_id = $sel['empresa_suscribe_id'];
		$empresa_suscribe = $sel['empresa_suscribe'];

		$sigla_correlativo = $sel['sigla_correlativo'];
		$codigo_correlativo = $sel['codigo_correlativo'];
		
		$fecha_atencion_gerencia_proveedor = $sel['fecha_atencion_gerencia_proveedor'];
		$aprobacion_gerencia_proveedor = $sel['aprobacion_gerencia_proveedor'];
		$aprobado_por = $sel['aprobado_por'];

		$proveedor_ruc = $sel['proveedor_ruc'];
		$proveedor_razon_social_titulo = $sel['proveedor_razon_social'];
		$usuario_creacion_correo = $sel['usuario_creacion_correo'];

		$date = date_create($sel["fecha_inicio"]);
		$fecha_inicio_contrato = date_format($date, "Y/m/d");

		$gerente_area_id = trim($sel["gerente_area_id"]);

		if (empty($gerente_area_id)) {
			$gerente_area_nombre = trim($sel["gerente_area_nombre"]);
			$gerente_area_email = trim($sel["gerente_area_email"]);
			array_push($correos_adicionales, $gerente_area_email); //Correo Responsable de Area
		} else {
			$gerente_area_nombre = trim($sel["nombre_del_gerente_area"]);
			$gerente_area_email = trim($sel["email_del_gerente_area"]);
			array_push($correos_adicionales, $gerente_area_email); //Correo Responsable de Area
		}

		array_push($correos_adicionales, trim($sel['usuario_creacion_correo'])); //Correo Persona de Contacto
		array_push($correos_adicionales, trim($sel['email_del_aprobante'])); //Correo del Aprobante

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

		$body .= '<thead>';
		
		if(!is_null($fecha_atencion_gerencia_proveedor) && $aprobacion_gerencia_proveedor == 0)
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
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Empresa Contratante:</b></td>';
			$body .= '<td>'.$sel["empresa_suscribe"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>RUC Proveedor:</b></td>';
			$body .= '<td>'.$sel["proveedor_ruc"].'</td>';
		$body .= '</tr>';
		
		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Razon social Proveedor:</b></td>';
			$body .= '<td>'.$sel["proveedor_razon_social"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Nombre Comercial Proveedor:</b></td>';
			$body .= '<td>'.$sel["proveedor_nombre_comercial"].'</td>';
		$body .= '</tr>';

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

		if(!is_null($fecha_atencion_gerencia_proveedor) && $aprobacion_gerencia_proveedor == 0)
		{
			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd"><b>Rechazado por:</b></td>';
				$body .= '<td>' . $sel["aprobado_por"] . '</td>';
			$body .= '</tr>';
		}
		else if(!is_null($fecha_atencion_gerencia_proveedor) && $aprobacion_gerencia_proveedor == 1)
		{
			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd"><b>Aprobado por:</b></td>';
				$body .= '<td>' . $sel["aprobado_por"] . '</td>';
			$body .= '</tr>';
		}

		$body .= '</table>';
		$body .= '</div>';

	}
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$host.'/?sec_id=contrato&sub_sec_id=detalle_solicitud_acuerdo_confidencialidad&id='.$contrato_id.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
				$body .= '<b>Ver Solicitud</b>';
		    $body .= '</a>';
		$body .= '</div>';

		$body .= '</html>';
		$body .= "";

	$titulo_email = "";

	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_solicitud_acuerdo_confidencialidad([$usuario_creacion_correo]);

	if(!is_null($fecha_atencion_gerencia_proveedor) && $aprobacion_gerencia_proveedor == 0)
	{
		$cc = [
			"$usuario_creacion_correo"
		];

		$titulo_email = "Gestion - Sistema Contratos - Solicitud de Acuerdo de Confidencialidad Rechazada: CÓD - ";
	}
	else
	{
		$cc = $lista_correos['cc'];

		$titulo_email = "Gestion - Sistema Contratos - Nueva Solicitud de Acuerdo de Confidencialidad: Código - ";
	}

	if (env('SEND_EMAIL') == 'produccion') {
		if ( !(empty($gerente_area_email)) ) {
			if ( sec_contrato_nuevo_acuerdo_confidencialidad_is_valid_email($gerente_area_email) ) {
				array_push($cc, $gerente_area_email );
			}
		}
	}

	if($_POST['correos_adjuntos'] != ""){
		$validate = true;
		$emails = preg_split('[,|;]',$_POST['correos_adjuntos']);
		foreach($emails as $e){
		    if(preg_match('/^[_ña-z0-9-]+(\.[_ña-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/',trim($e)) == 0){
				$error_msg = "'" . $e . "'" ." no es un Correo Adjunto válido";
				if($e == "")
				{
					$error_msg = "Formato de Correo Incorrecto";
				}
		        $result["http_code"] = 500;
				$result["status"] = "error";
				$result["mensaje"] = $error_msg;
				echo json_encode($result);
				die;
		    }
		    else
		    {
				$cc[] = $e;
		    }
		}
	}

	$bcc = $lista_correos['bcc'];

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

function send_email_confirmacion_acuerdo_confidencialidad($contrato_id, $reenvio)
{
	include("db_connect.php");
	include("sys_login.php");

	$host= $_SERVER["HTTP_HOST"];
	$correos_add = [];

	$sel_query = $mysqli->query(
		"
		SELECT c.ruc,
			c.razon_social,
			c.nombre_comercial,
			c.dni_representante,
			c.nombre_representante,
			c.persona_contacto_proveedor,
			c.detalle_servicio,
			c.alcance_servicio,
			c.tipo_terminacion_anticipada_id,
			ta.nombre AS tipo_terminacion_anticipada,
			c.terminacion_anticipada,
			c.observaciones,
			CONCAT(c.periodo_numero, ' ', p.nombre) AS periodo,
			c.periodo_numero,
			p.id AS periodo_nombre_id,
			p.nombre AS periodo_nombre,
			c.fecha_inicio,
	        m.nombre AS tipo_moneda,
			m.simbolo AS tipo_moneda_simbolo,
			c.monto,
			c.forma_pago_id,
			f.nombre AS forma_pago,
			c.tipo_comprobante_id,
			t.nombre AS tipo_comprobante,
			c.plazo_pago,
			c.empresa_suscribe_id,
			r.nombre AS empresa_suscribe,
			c.user_created_id,
			CONCAT(IFNULL(pe.nombre, ''),' ',IFNULL(pe.apellido_paterno, ''),' ',IFNULL(pe.apellido_materno, '')) AS user_created,
			pe.correo AS email_solicitante,
			c.created_at,
			co.sigla AS sigla_correlativo,
			c.codigo_correlativo,
			c.gerente_area_id,
			c.gerente_area_nombre,
			c.gerente_area_email,
			CONCAT(IFNULL(peg.nombre, ''),' ',IFNULL(peg.apellido_paterno, ''),' ',IFNULL(peg.apellido_materno, '')) AS nombre_del_gerente_area,
			peg.correo AS email_del_gerente_area,
			pud.correo AS email_del_director
		FROM cont_contrato c
			INNER JOIN cont_periodo p
			ON c.periodo = p.id
	        LEFT JOIN tbl_moneda m ON c.moneda_id = m.id
			LEFT JOIN cont_forma_pago f ON c.forma_pago_id = f.id
			LEFT JOIN cont_tipo_comprobante t ON c.tipo_comprobante_id = t.id
			INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id
			INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
			INNER JOIN tbl_personal_apt pe ON u.personal_id = pe.id
			LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
			LEFT JOIN cont_tipo_terminacion_anticipada ta ON c.tipo_terminacion_anticipada_id = ta.id
			LEFT JOIN tbl_usuarios ug ON c.gerente_area_id = ug.id
			LEFT JOIN tbl_personal_apt peg ON ug.personal_id = peg.id
			LEFT JOIN tbl_usuarios ud ON c.director_aprobacion_id = ud.id
			LEFT JOIN tbl_personal_apt pud ON ud.personal_id = pud.id
		WHERE c.tipo_contrato_id = 5 AND c.contrato_id = '".$contrato_id."'
		");

	$body = "";
	$body .= '<html>';

	$list_emp_cont = $mysqli->query("SELECT tpg.valor FROM tbl_parametros_generales tpg WHERE tpg.codigo = 'empresa_contacto_de_contratos' AND tpg.estado = 1 LIMIT 1");
	$row_emp_cont = $list_emp_cont->fetch_assoc();
	$valor_empresa_contacto = isset($row_emp_cont['valor']) && $row_emp_cont['valor'] == 'Kurax' ? '(Kurax)':'(AT)';

	while($sel = $sel_query->fetch_assoc())
	{
		$dni_representante = $sel['dni_representante'];
		$nombre_representante = $sel['nombre_representante'];

		$sigla_correlativo = $sel['sigla_correlativo'];
		$codigo_correlativo = $sel['codigo_correlativo'];

		$proveedor_ruc = $sel['ruc'];
		$proveedor_razon_social_titulo = $sel['razon_social'];

		$date = date_create($sel["fecha_inicio"]);
		$fecha_inicio = date_format($date,"Y-m-d");

		$tipo_moneda_simbolo = $sel["tipo_moneda_simbolo"];

		$monto = $tipo_moneda_simbolo.' '.number_format($sel["monto"], 2, '.', ',');

		$periodo_nombre_id = $sel["periodo_nombre_id"];

		$periodo_numero = $sel["periodo_numero"];

		$gerente_area_id = trim($sel["gerente_area_id"]);
		$email_del_director = trim($sel["email_del_director"]);
		array_push($correos_add,$email_del_director);
		array_push($correos_add,$sel["email_solicitante"]);
		
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
						$body .= '<td style="background-color:#ffffdd;width:300px"><b>Empresa Arrendataria</b></td>';
						$body .= '<td>'.$sel["empresa_suscribe"].'</td>';
					$body .= '</tr>';
					$body .= '<tr>';
						$body .= '<td style="background-color:#ffffdd;width:300px"><b>Persona Contacto '.$valor_empresa_contacto.'</b></td>';
						$body .= '<td>'.$sel["persona_contacto_proveedor"].'</td>';
					$body .= '</tr>';
					$body .= '<tr>';
						$body .= '<td style="background-color: #ffffdd"><b>Responsable de Área:</b></td>';
						$body .= '<td>' . $gerente_area_nombre . '</td>';
					$body .= '</tr>';
					$body .= '<tr>';
						$body .= '<td style="background-color:#ffffdd;width:300px"><b>Registrado por</b></td>';
						$body .= '<td>'.$sel["user_created"].'</td>';
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

		$body .= '<div>';
			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
				$body .= '<tbody>';
					$body .= '<tr>';
						$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
							$body .= 'DATOS DEL PROVEEDOR';
						$body .= '</th>';
					$body .= '</tr>';
					$body .= '<tr>';
						$body .= '<td style="background-color:#ffffdd;width:300px"><b>RUC</b></td>';
						$body .= '<td>'.$sel["ruc"].'</td>';
					$body .= '</tr>';
					$body .= '<tr>';
						$body .= '<td style="background-color:#ffffdd;width:300px"><b>Raz&oacute;n Social</b></td>';
						$body .= '<td>'.$sel["razon_social"].'</td>';
					$body .= '</tr>';

					$body .= '<tr>';
						$body .= '<td style="background-color:#ffffdd;width:300px"><b>Nombre Comercial</b></td>';
						$body .= '<td>'.$sel["nombre_comercial"].'</td>';
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

		$row_count_rl = $sel_query_rl->num_rows;

		if ($row_count_rl == 0) {

			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
				$body .= '<tr>';
					$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
						$body .= 'DATOS DEL PROVEEDOR - REPRESENTANTE LEGAL';
					$body .= '</th>';
				$body .= '</tr>';
				$body .= '<tr>';
					$body .= '<td style="background-color:#ffffdd;width:300px;"><b>DNI del representante legal</b></td>';
					$body .= '<td>' . $dni_representante . '</td>';
				$body .= '</tr>';
				$body .= '<tr>';
					$body .= '<td style="background-color:#ffffdd;"><b>Nombre completo del representante legal</b></td>';
					$body .= '<td>' . $nombre_representante . '</td>';
				$body .= '</tr>';
			$body .= '</table>';

		} elseif ($row_count_rl > 0) {
			while($row_rl = $sel_query_rl->fetch_assoc()){
				$c = $c + 1; 

				$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<tr>';
						$body .= '<th colspan="2" style="color:#fff;background-color:#395168;vertical-align:middle;font-size:16px">';
							$body .= 'DATOS DEL PROVEEDOR - REPRESENTANTE LEGAL #' . $c;
						$body .= '</th>';
					$body .= '</tr>';
					$body .= '<tr>';
						$body .= '<td style="background-color:#ffffdd;width:300px;"><b>DNI del representante</b></td>';
						$body .= '<td>' . $row_rl["dni_representante"] . '</td>';
					$body .= '</tr>';
					$body .= '<tr>';
						$body .= '<td style="background-color:#ffffdd;"><b>Nombre completo del representante</b></td>';
						$body .= '<td>' . $row_rl["nombre_representante"] . '</td>';
					$body .= '</tr>';
				$body .= '</table>';
			}
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
		    $body .= '<a href="'.$host.'/?sec_id=contrato&sub_sec_id=detalle_solicitud_acuerdo_confidencialidad&id='.$contrato_id.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
				$body .= '<b>Ver Solicitud</b>';
		    $body .= '</a>';
		$body .= '</div>';

		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

	}
		$body .= '</html>';
		$body .= "";

	$renvio_titulo = "";

	if($reenvio)
	{
		// $renvio_titulo = "Reenvio - ";
		$renvio_titulo = "";
	}

	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_confirmacion_acuerdo_confidencialidad($correos_add);

	$cc = $lista_correos['cc'];
	$bcc = $lista_correos['bcc'];

	if (env('SEND_EMAIL') == 'produccion') {
		if ( !(empty($gerente_area_email)) ) {
			if ( sec_contrato_nuevo_acuerdo_confidencialidad_is_valid_email($gerente_area_email) ) {
				array_push($cc, $gerente_area_email );
			}
		}
	}

	$request = [
		//"subject" => "CONFIRMACIÓN DE SOLICITUD PROVEEDOR: ".$proveedor_razon_social_titulo. " - RUC " .$proveedor_ruc,
		"subject" => $renvio_titulo."Gestion - Sistema Contratos - Confirmación de Solicitud de Acuerdo de Confidencialidad: CÓD - " .$sigla_correlativo.$codigo_correlativo,
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

		if ($reenvio) 
		{
			$result["http_code"] = 200;
			$result["status"] = "Datos obtenidos de gestion.";
			echo json_encode($result);
			exit();
		}

	} 
	catch (Exception $e) 
	{
		if ($reenvio) 
		{
			$result["http_code"] = 400;
			$result["status"] = "Datos obtenidos de gestion.";
			$result["error"] = $mail->ErrorInfo;
			echo json_encode($result);
			exit();
		}	
		else
		{
			$resultado = $mail->ErrorInfo;
			//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
			//return false;
			echo json_encode($resultado);
		}
		
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_acuerdo_confidencialidad") {
	include("function_replace_invalid_caracters_contratos.php");
	//echo "<pre>";print_r($_POST);echo "</pre>";die();
	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");

	if ($usuario_id == null) {
		$result["status"] = 400;
		$result["message"] = 'Su sesion ha caducado, Ingrese de nuevo al sistema';
		$result["result"] = "error";
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}


	// $monto = str_replace(",","",$_POST["monto"]);
	$array_representantes = $_POST["rr_representantes"];
	$data_representantes = json_decode($array_representantes);

	$status = "success";

	$inputs_file = ["archivo_ficha_ruc","archivo_formato_contrato","archivo_know_your_client"];
	$extensiones = ["pdf","jpg","jpeg","png","doc","docx","odt","ppt","pptx","xls","xlsx","txt","7z","rar","zip"];
	$maxsize = 52428800; // 50 MB
	$error_msg = [];

	foreach ($inputs_file as $value) {
		if (isset($_FILES[$value]) && $_FILES[$value]['error'] === UPLOAD_ERR_OK) {
			$nombre_file=ucwords(str_replace("_"," ",$value));
			if($_FILES[$value]['size'] > $maxsize) {
				$error_msg[] = 'Archivo debe ser menor a 50MB';
				$status="error";
			}

			else if( !in_array(pathinfo(strtolower($_FILES[$value]['name']), PATHINFO_EXTENSION),$extensiones)){
				 $error_msg[] = $nombre_file.' debe tener extensión .png, .jpg, .pdf.';
				 $status="error";
			}
		}
	}

	if(count($error_msg)>0){
		$result["status"] = 400;
		$result["message"] = implode('<br> ', $error_msg);
		$result["result"] = "error";
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}

	if($_POST['correos_adjuntos'] != "") {
		$validate = true;
		$emails = preg_split('[,|;]',$_POST['correos_adjuntos']);
		foreach($emails as $e){
		    if(preg_match('/^[_ña-z0-9-]+(\.[_ña-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/',trim($e)) == 0){
				$error_msg = "'" . $e . "'" ." no es un Correo Adjunto válido";
				if($e == "")
				{
					$error_msg = "Formato de Correo Incorrecto";
				}
		        $result["http_code"] = 500;
				$result["status"] = "error";
				$result["message"] = $error_msg;
				echo json_encode($result);
				die;
		    }
		}
	}

	$query_update = "UPDATE cont_correlativo SET numero = numero + 1 WHERE tipo_contrato = 5 AND status = 1";
	
	$mysqli->query($query_update);

	if($mysqli->error)
	{
		$result["status"] = 400;
		$result["message"] = $mysqli->error;
		$result["result"] = "Error al actualizar el correlativo";
		$result["error"] = $error;
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
			WHERE tipo_contrato = 5 AND status = 1 LIMIT 1
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

		$aprobacion_obligatoria_id = trim($_POST["aprobacion_obligatoria_id"]);
		$director_aprobacion_id = trim($_POST["director_aprobacion_id"]);

		if (!empty($director_aprobacion_id)) {
			$aprobacion_obligatoria_id = 1;
		}

		$cargo_id_persona_contacto = !Empty($_POST['cargo_id_persona_contacto']) ? $_POST['cargo_id_persona_contacto'] :'0';
		$cargo_id_responsable = !Empty($_POST['cargo_id_responsable']) ? $_POST['cargo_id_responsable'] :'0';
		$cargo_id_aprobante = !Empty($_POST['cargo_id_aprobante']) ? $_POST['cargo_id_aprobante'] :'0';

		$query_insert = "INSERT INTO cont_contrato
		(
			  tipo_contrato_id
			, codigo_correlativo 
			, empresa_suscribe_id
			, area_responsable_id
			, persona_responsable_id
			, cargo_id_persona_contacto
			, cargo_id_responsable
			, cargo_id_aprobante
			, etapa_id
			, ruc
			, razon_social
			, nombre_comercial
			, check_gerencia_proveedor
			, director_aprobacion_id
			, persona_contacto_proveedor
			, detalle_servicio
			, periodo_numero
			, periodo
			, fecha_inicio 
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
			" . $_POST["sec_con_nuevo_empresa_susbribe"] . ",
			0,
			0,
			".$cargo_id_persona_contacto.",
			".$cargo_id_responsable.",
			".$cargo_id_aprobante.",
			1,
			" . $_POST["sec_con_nuevo_ruc"] . ",
			'" . replace_invalid_caracters($_POST["sec_con_nuevo_razon_social"]) . "',
			'" . replace_invalid_caracters($_POST["sec_con_nuevo_nombre_comercial"]) . "',
			$aprobacion_obligatoria_id,
			$director_aprobacion_id,
			'" . replace_invalid_caracters($_POST["sec_con_nuevo_persona_contacto_proveedor"]) . "',
			'" . replace_invalid_caracters($_POST["sec_con_nuevo_detalle_servicio"]) . "',
			1,
			1,
			'" . $_POST["sec_con_nuevo_fecha_inicio"] . "',
			'" . replace_invalid_caracters($_POST["sec_con_nuevo_observaciones"]). "',
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
			$result["status"] = 400;
			$result["message"] = $mysqli->error;
			$result["result"] = "Error al registrar el contrato";
			$result["error"] = $error;
			echo json_encode($result);
			exit();
		} else {
			$http_code=200;
			$mensaje="Registro Insertado.";

			// files
			$path = "/var/www/html/files_bucket/contratos/solicitudes/acuerdo_confidencialidad/";
			if (!is_dir($path)) mkdir($path, 0777, true);
			
			if (isset($_FILES['archivo_ficha_ruc']) && $_FILES['archivo_ficha_ruc']['error'] === UPLOAD_ERR_OK) {
				$filename =    $_FILES['archivo_ficha_ruc']['name'];
				$filenametem = $_FILES['archivo_ficha_ruc']['tmp_name'];
				$filesize = $_FILES['archivo_ficha_ruc']['size'];
				$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
				if($filename!=""){
					$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
					$nombre_archivo=$contrato_id. "_RUC_" . date('YmdHis') . "." . $fileExt;
					move_uploaded_file($filenametem, $path. $nombre_archivo);
					$comando=" INSERT INTO cont_archivos (
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
									1,
									'" . $nombre_archivo . "',
									'".$fileExt."',
									'" . $filesize . "',
									'".$path."',
									" . $usuario_id . ",
									'" . $created_at . "'
									)";
					$mysqli->query($comando);
					$imgruc_id = mysqli_insert_id($mysqli);
				}
			}

			if (isset($_FILES['archivo_formato_contrato']) && $_FILES['archivo_formato_contrato']['error'] === UPLOAD_ERR_OK) {
				$filename = $_FILES['archivo_formato_contrato']['name'];
				$filenametem = $_FILES['archivo_formato_contrato']['tmp_name'];
				$filesize = $_FILES['archivo_formato_contrato']['size'];
				$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
				if($filename!=""){
					$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
					$nombre_archivo=$contrato_id. "_FORMATO_CONTRATO_" . date('YmdHis') . "." . $fileExt;
					move_uploaded_file($filenametem, $path. $nombre_archivo);
					$comando=" INSERT INTO cont_archivos (
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
									30,
									'" . $nombre_archivo . "',
									'".$fileExt."',
									'" . $filesize . "',
									'".$path."',
									" . $usuario_id . ",
									'" . $created_at . "'
									)";
					$mysqli->query($comando);
					$imgrvig_id = mysqli_insert_id($mysqli);
				}
			}

			if (isset($_FILES['archivo_know_your_client']) && $_FILES['archivo_know_your_client']['error'] === UPLOAD_ERR_OK) {

				 	$filename = $_FILES['archivo_know_your_client']['name'];
					$filenametem = $_FILES['archivo_know_your_client']['tmp_name'];
					$filesize = $_FILES['archivo_know_your_client']['size'];
					$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
					if ($filename != "") {
						$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
						$nombre_archivo = $contrato_id . "_FORMATO_CONTRATO_" . date('YmdHis') . "." . $fileExt;
						move_uploaded_file($filenametem, $path . $nombre_archivo);
						$comando = " INSERT INTO cont_archivos (
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
									118,
									'" . $nombre_archivo . "',
									'" . $fileExt . "',
									'" . $filesize . "',
									'" . $path . "',
									" . $usuario_id . ",
									'" . $created_at . "'
									)";
						$mysqli->query($comando);
						$imgrvig_id = mysqli_insert_id($mysqli);
					}
				
			} 

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
				// INICIO DE CARGAR ARCHIVOS DNI NUEVO REPRESENTANTE LEGAL
				$name_file_dni_like_repr = "dni_nuevo_representante_" . $item->id_registro;

				$filename = $_FILES[$name_file_dni_like_repr]['name'];
				$filenametem = $_FILES[$name_file_dni_like_repr]['tmp_name'];
				$filesize = $_FILES[$name_file_dni_like_repr]['size'];
				$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
				$img_id_insert_dni = 0;
				if($filename!=""){
					$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
					$nombre_archivo = $contrato_id. "_DNI" . date('YmdHis') . "." . $fileExt;
					move_uploaded_file($filenametem, $path. $nombre_archivo);
					$comando=" INSERT INTO cont_archivos (
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
									3,
									'" . $nombre_archivo . "',
									'".$fileExt."',
									'" . $filesize . "',
									'".$path."',
									" . $usuario_id . ",
									'" . $created_at . "'
									)";
					$mysqli->query($comando);
					$img_id_insert_dni = mysqli_insert_id($mysqli);
				}
				// FIN DE CARGAR ARCHIVOS DNI NUEVO REPRESENTANTE LEGAL

				// INICIO DE CARGAR ARCHIVOS VIGENCIA NUEVO REPRESENTANTE LEGAL
				$name_file_vigencia_like_repr = "vigencia_nuevo_representante_" . $item->id_registro;

				$filename = $_FILES[$name_file_vigencia_like_repr]['name'];
				$filenametem = $_FILES[$name_file_vigencia_like_repr]['tmp_name'];
				$filesize = $_FILES[$name_file_vigencia_like_repr]['size'];
				$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
				$img_id_insert_vigencia = 0;
				if($filename!=""){
					$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
					$nombre_archivo = $contrato_id. "_VIG" . date('YmdHis') . "." . $fileExt;
					move_uploaded_file($filenametem, $path. $nombre_archivo);
					$comando=" INSERT INTO cont_archivos (
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
									2,
									'" . $nombre_archivo . "',
									'".$fileExt."',
									'" . $filesize . "',
									'".$path."',
									" . $usuario_id . ",
									'" . $created_at . "'
									)";
					$mysqli->query($comando);
					$img_id_insert_vigencia = mysqli_insert_id($mysqli);
				}
				// FIN DE CARGAR ARCHIVOS VIGENCIA NUEVO REPRESENTANTE LEGAL

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
					. $item->nro_cuenta_detraccion . "', 
					0, '" 
					. $item->nro_cuenta . "', '" 
					. $item->nro_cci . "', " 
					. $img_id_insert_vigencia . ", " 
					. $img_id_insert_dni . ", " 
					. $usuario_id . ", now())";


				$mysqli->query($query_insert_repr);
				$error = '';
				if($mysqli->error){
					$result["status"] = 400;
					$result["message"] = $mysqli->error;
					$result["result"] = "error representante legal";
					$result["error"] = $error;
					echo json_encode($result);
					exit();
				}
			}

		    if($aprobacion_obligatoria_id == 1)
			{
				send_email_confirmacion_acuerdo_confidencialidad($contrato_id, false);
			}
			else
			{
				send_email_solicitud_acuerdo_confidencialidad($contrato_id);
			}
		}
	}
	
	$result["status"] = 200;
	$result["message"] = "Se ha registrado la solicitud de acuerdo de confidencialidad correctamente.";
	$result["result"] = "";
	$result["error"] = '';
	echo json_encode($result);
	exit();

}


function sec_contrato_nuevo_acuerdo_confidencialidad_is_valid_email($str) {
	return (false !== filter_var($str, FILTER_VALIDATE_EMAIL));
}

?>