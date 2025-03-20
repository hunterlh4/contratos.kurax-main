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
error_reporting(0);

function send_email_solicitud_contrato_agente_firmada($contrato_id)
{
	
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$sel_query = $mysqli->query("SELECT c.nombre_tienda, 
									 i.ubicacion, 
									 c.user_created_id,
									 concat( IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS usuario_solicitante,
									 tp.correo,
									 co.sigla AS sigla_correlativo,
									c.codigo_correlativo,
									c.created_at,
									c.nombre_agente,
									c.c_costos
								FROM cont_contrato c
									INNER JOIN cont_inmueble i
									ON c.contrato_id = i.contrato_id
									INNER JOIN tbl_usuarios tu
									ON tu.id = c.user_created_id
									INNER JOIN tbl_personal_apt tp
									ON tp.id = tu.personal_id
									LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
								WHERE c.contrato_id = '".$contrato_id."' LIMIT 1
				");

	$body = "";
	$body .= '<html>';

	$email_user_created = '';

	while($sel = $sel_query->fetch_assoc())
	{
		$sigla_correlativo = $sel['sigla_correlativo'];
		$codigo_correlativo = $sel['codigo_correlativo'];
		$nombre_agente = $sel['nombre_agente'];
		$c_costos = $sel['c_costos'];
		
		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

		$body .= '<thead>';
		$body .= '<tr>';
		$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
		$body .= '<b>Nuevo Agente</b>';
		$body .= '</th>';
		$body .= '</tr>';
		$body .= '</thead>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Nombre Agente:</b></td>';
			$body .= '<td>'.$sel["nombre_agente"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Centro de Costos:</b></td>';
			$body .= '<td>'.$sel["c_costos"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Ubicación:</b></td>';
			$body .= '<td>'.$sel["ubicacion"].'</td>';
		$body .= '</tr>';
		
		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha solicitud:</b></td>';
			$body .= '<td>'.$sel["created_at"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Usuario solicitante:</b></td>';
			$body .= '<td>'.$sel["usuario_solicitante"].'</td>';
		$body .= '</tr>';

		$body .= '</table>';
		$body .= '</div>';

		$email_user_created = $sel["correo"];
	}

		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
			$body .= '<a href="'.$host.'/?sec_id=contrato&sub_sec_id=detalle_agente&id='.$contrato_id.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
				$body .= '<b>Ver Contrato</b>';
			$body .= '</a>';
		$body .= '</div>';

		$body .= '</html>';
		$body .= "";

		$correos_ad = [];
		array_push($correos_ad, $email_user_created);
		$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
		$lista_correos = $correos->send_email_solicitud_contrato_agente_firmada($correos_ad);
		
		$cc = $lista_correos['cc'];
		$bcc = $lista_correos['bcc'];

		$request = [
			"subject" => "Gestion - Sistema Contratos - Contrato Firmado de Agente: " .$nombre_agente.": Centro de Costo - ".$c_costos,
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

function send_email_adenda_contrato_agente_firmada($adenda_id, $reenvio = false)
{
	include("db_connect.php");
	include("sys_login.php");
	
	$host = $_SERVER["HTTP_HOST"];

	$sel_query = $mysqli->query("SELECT c.contrato_id, c.nombre_tienda, 
									 i.ubicacion, 
									 c.user_created_id,
									 concat( IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS usuario_solicitante,
									 tp.correo,
									 co.sigla AS sigla_correlativo,
									c.codigo_correlativo,
									c.created_at,
									c.nombre_agente,
									c.c_costos,
									pab.correo as correo_abogado,
									ar.nombre AS nombre_area
								FROM cont_adendas AS a 
									INNER JOIN cont_contrato c ON c.contrato_id = a.contrato_id
									INNER JOIN cont_inmueble i
									ON c.contrato_id = i.contrato_id
									INNER JOIN tbl_usuarios tu
									ON tu.id = c.user_created_id
									INNER JOIN tbl_personal_apt tp
									ON tp.id = tu.personal_id
									INNER JOIN tbl_areas AS ar ON tp.area_id = ar.id
									LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato

									LEFT JOIN tbl_usuarios uab ON uab.id = a.abogado_id
									LEFT JOIN tbl_personal_apt pab ON pab.id = uab.personal_id
								WHERE a.id = $adenda_id
	");


	$body = "";
	$body .= '<html>';

	$usuario_creacion_correo = '';
	$contrato_id = 0;
	$correos_add = [];
	while($sel = $sel_query->fetch_assoc())
	{
		$contrato_id = $sel['contrato_id']; 
		$sigla_correlativo = $sel['sigla_correlativo'];
		$codigo_correlativo = $sel['codigo_correlativo'];
		$nombre_agente = $sel['nombre_agente'];
		$c_costos = $sel['c_costos'];

		if(!Empty($sel['correo'])){
			array_push($correos_add, $sel['correo']);
		}
		if(!Empty($sel['correo_abogado'])){
			array_push($correos_add, $sel['correo_abogado']);
		}


		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

		$body .= '<thead>';
		
		$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>Nueva solicitud</b>';
			$body .= '</th>';
		$body .= '</tr>';
		

		$body .= '</thead>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Area Solicitante:</b></td>';
			$body .= '<td>'.$sel["nombre_area"].'</td>';
		$body .= '</tr>';
		
		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Usuario solicitante:</b></td>';
			$body .= '<td>'.$sel["usuario_solicitante"].'</td>';
		$body .= '</tr>';
	
		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha Solicitud:</b></td>';
			$body .= '<td>'.$sel["created_at"].'</td>';
		$body .= '</tr>';

		$body .= '</table>';
		$body .= '</div>';

		$query = $mysqli->query("
		SELECT id,
			adenda_id,
			nombre_tabla,
			valor_original,
			nombre_menu_usuario,
			nombre_campo_usuario,
			nombre_campo,
			tipo_valor,
			valor_varchar,
			valor_int,
			valor_date,
			valor_decimal,
			valor_select_option,
			status
		FROM cont_adendas_detalle
		WHERE adenda_id = " . $adenda_id . "
		AND tipo_valor != 'id_tabla' AND tipo_valor != 'registro'
		AND status = 1");
		$row_count = $query->num_rows;
		$numero_adenda_detalle = 0;
		if ($row_count > 0) {
			$body .= '<div>';
			$body .= '<br>';
			$body .= '</div>';

			$body .= '<div>';
			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

			$body .= '<thead>';
			
			$body .= '<tr>';
				$body .= '<th colspan="5" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
					$body .= '<b>Detalle</b>';
				$body .= '</th>';
			$body .= '</tr>';
			

			$body .= '</thead>';

			$body .= '<tr>';
				$body .= '<td align="center" style="background-color: #ffffdd; width: 20px;"><b>#</b></td>';
				$body .= '<td align="center" style="background-color: #ffffdd;"><b>Menu:</b></td>';
				$body .= '<td align="center" style="background-color: #ffffdd;"><b>Campo:</b></td>';
				$body .= '<td align="center" style="background-color: #ffffdd;"><b>Valor Original:</b></td>';
				$body .= '<td align="center" style="background-color: #ffffdd;"><b>Nuevo Valor:</b></td>';
			$body .= '</tr>';

			while($row = $query->fetch_assoc()){
				$nombre_menu_usuario = $row["nombre_menu_usuario"];
				$nombre_campo_usuario = $row["nombre_campo_usuario"];
				$valor_original = $row["valor_original"];
				$tipo_valor = $row["tipo_valor"];
				if ($tipo_valor == 'varchar') {
					$nuevo_valor = $row['valor_varchar'];
				} else if ($tipo_valor == 'int') {
					$nuevo_valor = $row['valor_int'];
				} else if ($tipo_valor == 'date') {
					$nuevo_valor = $row['valor_date'];
				} else if ($tipo_valor == 'decimal') {
					$nuevo_valor = $row['valor_decimal'];
				} else if ($tipo_valor == 'select_option') {
					$nuevo_valor = $row['valor_select_option'];
				}
				$numero_adenda_detalle++;
												
				$body .= '<tr>';
					$body .= '<td>'.$numero_adenda_detalle.'</td>';
					$body .= '<td>'.$nombre_menu_usuario.'</td>';
					$body .= '<td>'.$nombre_campo_usuario.'</td>';
					$body .= '<td>'.$valor_original.'</td>';
					$body .= '<td>'.$nuevo_valor.'</td>';
				$body .= '</tr>';

			}
			$body .= '</table>';
			$body .= '</div>';
		}

		$query_sql = "
		SELECT id,
			adenda_id,
			nombre_tabla,
			valor_original,
			nombre_menu_usuario,
			nombre_campo_usuario,
			nombre_campo,
			tipo_valor,
			valor_varchar,
			valor_int,
			valor_date,
			valor_decimal,
			valor_select_option,
			status
		FROM cont_adendas_detalle
		WHERE adenda_id = " . $adenda_id . "
		AND tipo_valor = 'registro'
		AND status = 1";
		$query_reg = $mysqli->query($query_sql);
		$row_count = $query_reg->num_rows;
		$numero_adenda_detalle = 0;
		if ($row_count > 0) {
			while($row = $query_reg->fetch_assoc()){
				if ($row["nombre_menu_usuario"] == 'Cuenta Bancaria') {
					$query_pro = "
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
						rl.id IN ('" . $row["valor_int"] . "')
					";

					$valores_originales = [];
					$valores_nuevos = [];
					$list_query = $mysqli->query($query_pro);
					while ($li = $list_query->fetch_assoc()) {
						if ($li["id"] == $row["valor_int"]) {
							$valores_nuevos[] = $li;
						}
					}
					$body .= '<div>';
					$body .= '<br>';
					$body .= '</div>';

					$body .= '<div>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

					$body .= '<thead>';
					
					$body .= '<tr>';
						$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
							$body .= '<b>Nuevo Cuenta Bancaria</b>';
						$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
						$body .= '<td align="center" style="background-color: #ffffdd;"><b>Campo:</b></td>';
						$body .= '<td align="center" style="background-color: #ffffdd;"><b>Nuevo Valor:</b></td>';
					$body .= '</tr>';
					
					$body .= '</thead>';

					$body .= '<tr>';
						$body .= '<td >Banco</td>';
						$body .= '<td >'.$valores_nuevos[0]["banco_representante"].'</td>';
					$body .= '</tr>';

					$body .= '<tr>';
						$body .= '<td >Nro Cuenta</td>';
						$body .= '<td >'.$valores_nuevos[0]["nro_cuenta"].'</td>';
					$body .= '</tr>';

					$body .= '<tr>';
						$body .= '<td >Nro CCI</td>';
						$body .= '<td >'.$valores_nuevos[0]["nro_cci"].'</td>';
					$body .= '</tr>';

				

					$body .= '</table>';
					$body .= '</div>';
				}

				if ($row["nombre_menu_usuario"] == 'Contraprestación') {
					$query_cont = "
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
						c.id IN ('" . $row["valor_int"] . "')
					";

					$valores_originales = [];
					$valores_nuevos = [];
					$list_query = $mysqli->query($query_cont);
					while ($li = $list_query->fetch_assoc()) {
						if ($li["id"] == $row["valor_int"]) {
							$valores_nuevos[] = $li;
						}
					}
					$body .= '<div>';
					$body .= '<br>';
					$body .= '</div>';

					$body .= '<div>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

					$body .= '<thead>';
					
					$body .= '<tr>';
						$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
							$body .= '<b>Nueva Contraprestación</b>';
						$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
						$body .= '<td align="center" style="background-color: #ffffdd;"><b>Campo:</b></td>';
						$body .= '<td align="center" style="background-color: #ffffdd;"><b>Nuevo Valor:</b></td>';
					$body .= '</tr>';
					
					$body .= '</thead>';

					$body .= '<tr>';
					$body .= '<td>Tipo de moneda</td>';
					$body .= '<td>' . $valores_nuevos[0]["tipo_moneda"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Subtotal</td>';
					$body .= '<td>' . $valores_nuevos[0]["subtotal"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>IGV</td>';
					$body .= '<td>' . $valores_nuevos[0]["igv"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Monto Bruto</td>';
					$body .= '<td>' . $valores_nuevos[0]["monto"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Tipo de comprobante a emitir</td>';
					$body .= '<td>' . $valores_nuevos[0]["tipo_comprobante"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Plazo de Pago</td>';
					$body .= '<td>' . $valores_nuevos[0]["plazo_pago"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Forma de pago</td>';
					$body .= '<td>' . $valores_nuevos[0]["forma_pago_detallado"] . '</td>';
					$body .= '</tr>';


					$body .= '</table>';
					$body .= '</div>';
				}

				if ($row["nombre_menu_usuario"] == 'Propietario') {
					$query = "
						SELECT p.id AS persona_id,
							pr.propietario_id,
							tp.nombre AS tipo_persona,
							p.tipo_docu_identidad_id,
							td.nombre AS tipo_docu_identidad,
							p.num_docu,
							p.num_ruc,
							p.nombre,
							p.direccion,
							p.representante_legal,
							p.num_partida_registral,
							p.contacto_nombre,
							p.contacto_telefono,
							p.contacto_email
						FROM cont_propietario pr
						INNER JOIN cont_persona p ON pr.persona_id = p.id
						INNER JOIN cont_tipo_persona tp ON p.tipo_persona_id = tp.id
						INNER JOIN cont_tipo_docu_identidad td ON p.tipo_docu_identidad_id = td.id
						WHERE pr.propietario_id IN ('" . $row["valor_int"] . "')
					";
	
					$valores_originales = [];
					$valores_nuevos = [];
					$list_query = $mysqli->query($query);
					while ($li = $list_query->fetch_assoc()) {
						if ($li["propietario_id"] == $row["valor_int"]) {
							$valores_nuevos[] = $li;
						}
					}
	
					$body .= '<div>';
					$body .= '<br>';
					$body .= '</div>';

					$body .= '<div>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

					$body .= '<thead>';
					
					$body .= '<tr>';
						$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
							$body .= '<b>Nueva Propietario</b>';
						$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
						$body .= '<td align="center" style="background-color: #ffffdd;"><b>Campo:</b></td>';
						$body .= '<td align="center" style="background-color: #ffffdd;"><b>Nuevo Valor:</b></td>';
					$body .= '</tr>';
					
					$body .= '</thead>';
					$body .= '<tbody>';
	
					$body .= '<tr>';
					$body .= '<td>Tipo Persona</td>';
					$body .= '<td>' . $valores_nuevos[0]["tipo_persona"] . '</td>';
					$body .= '</tr>';
	
					$body .= '<tr>';
					$body .= '<td>Nombre</td>';
					$body .= '<td>' . $valores_nuevos[0]["nombre"] . '</td>';
					$body .= '</tr>';
	
					$body .= '<tr>';
					$body .= '<td>Tipo de Documento de Identidad</td>';
					$body .= '<td>' . $valores_nuevos[0]["tipo_docu_identidad"] . '</td>';
					$body .= '</tr>';
	
					if ($valores_nuevos[0]["tipo_docu_identidad_id"] != "2") {
						$body .= '<tr>';
						$body .= '<td>'.$valores_nuevos[0]["tipo_docu_identidad"].'</td>';
						$body .= '<td>' . $valores_nuevos[0]["num_docu"] . '</td>';
						$body .= '</tr>';
					}
	
					$body .= '<tr>';
					$body .= '<td>Número de RUC</td>';
					$body .= '<td>' . $valores_nuevos[0]["num_ruc"] . '</td>';
					$body .= '</tr>';
	
					$body .= '<tr>';
					$body .= '<td>Domicilio del propietario</td>';
					$body .= '<td>' . $valores_nuevos[0]["direccion"] . '</td>';
					$body .= '</tr>';
	
					$body .= '<tr>';
					$body .= '<td>Representante Legal</td>';
					$body .= '<td>' . $valores_nuevos[0]["representante_legal"] . '</td>';
					$body .= '</tr>';
	
					$body .= '<tr>';
					$body .= '<td>N° de Partida Registral de la empresa</td>';
					$body .= '<td>' . $valores_nuevos[0]["num_partida_registral"] . '</td>';
					$body .= '</tr>';
	
					$body .= '<tr>';
					$body .= '<td>Persona de contacto</td>';
					$body .= '<td>' . $valores_nuevos[0]["contacto_nombre"] . '</td>';
					$body .= '</tr>';
	
					$body .= '<tr>';
					$body .= '<td>Teléfono de la persona de contacto</td>';
					$body .= '<td>' . $valores_nuevos[0]["contacto_telefono"] . '</td>';
					$body .= '</tr>';
	
					$body .= '<tr>';
					$body .= '<td>E-mail de la persona de contacto</td>';
					$body .= '<td>' . $valores_nuevos[0]["contacto_email"] . '</td>';
					$body .= '</tr>';
	
					$body .= '</tbody>';
					$body .= '</table>';
				}
			}
		}



	}
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$body .= '<div style="width: 700px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$host.'/?sec_id=contrato&sub_sec_id=detalle_agente&id='.$contrato_id.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
				$body .= '<b>Ver Solicitud</b>';
		    $body .= '</a>';
		$body .= '</div>';

		$body .= '</html>';
		$body .= "";

	//lista de correos
	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_adenda_contrato_agente_firmada($correos_add);

	$cc = $lista_correos['cc'];

	$titulo_email = "Gestion - Sistema Contratos - Adenda Firmada de Contrato de Agente: Código - ";
	if ($reenvio){
		$titulo_email = "Gestion - Sistema Contratos - Reenvío de Adenda Firmada de Contrato de Agente: Código - ";
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

if (isset($_POST["accion"]) && $_POST["accion"]==="cont_listar_agentes")
{
	$empresa_id = $_POST['empresa_id'];
	$nombre_agente = $_POST['nombre_agente'];
	$cont_agente_param_cc_costos = $_POST['cont_agente_param_cc_costos'];
 
	$fecha_inicio_solicitud = $_POST['fecha_inicio_solicitud'];
	$fecha_fin_solicitud = $_POST['fecha_fin_solicitud'];
	$fecha_inicio_inicio = $_POST['fecha_inicio_inicio'];
	$fecha_fin_inicio = $_POST['fecha_fin_inicio'];

	$id_departamento = $_POST['id_departamento'];
	$id_provincia = $_POST['id_provincia'];
	$id_distrito = $_POST['id_distrito'];

	$where_empresa = "";
	$where_nombre_agente= "";
	$where_c_costos = "";
	$where_ubigeo = '';
	$where_fecha_solicitud = "";
	$where_fecha_inicio = "";

	if (!Empty($empresa_id))
	{
		$where_empresa = " AND c.empresa_suscribe_id = '".$empresa_id."' ";
	}
	
	if (!Empty($nombre_agente)) {
		$where_nombre_agente = " AND c.nombre_agente LIKE '%".$nombre_agente."%' ";
	}

	if (!Empty($cont_agente_param_cc_costos)) {
		$where_c_costos = " AND c.c_costos LIKE '%".$cont_agente_param_cc_costos."%' ";
	}

	if (!Empty($id_departamento)) {
		$where_ubigeo = " AND i.ubigeo_id LIKE '%".$id_departamento.$id_provincia.$id_distrito."%'";
	}

	if (!Empty($fecha_inicio_solicitud) && !Empty($fecha_fin_solicitud)) {
		$where_fecha_solicitud = " AND c.created_at BETWEEN '$fecha_inicio_solicitud 00:00:00' AND '$fecha_fin_solicitud 23:59:59'";
	} elseif (!Empty($fecha_inicio_solicitud)) {
		$where_fecha_solicitud = " AND c.created_at >= '$fecha_inicio_solicitud 00:00:00'";
	} elseif (!Empty($fecha_fin_solicitud)) {
		$where_fecha_solicitud = " AND c.created_at <= '$fecha_fin_solicitud 23:59:59'";
	}

	if (!Empty($fecha_inicio_inicio) && !Empty($fecha_fin_inicio)) {
		$where_fecha_inicio = " AND c.fecha_inicio_agente BETWEEN '$fecha_inicio_inicio 00:00:00' AND '$fecha_fin_inicio 23:59:59'";
	} elseif (!Empty($fecha_inicio_inicio)) {
		$where_fecha_inicio = " AND c.fecha_inicio_agente >= '$fecha_inicio_inicio 00:00:00'";
	} elseif (!Empty($fecha_fin_inicio)) {
		$where_fecha_inicio = " AND c.fecha_inicio_agente <= '$fecha_fin_inicio 23:59:59'";
	}

	$query = "
SELECT
	c.contrato_id, 
	a.nombre AS area, 
	CONCAT(IFNULL(p.nombre, ''), ' ',  IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS solicitante,
	r.ruc,
	r.nombre AS empresa_suscribe,
	c.fecha_suscripcion_contrato,
	co.sigla AS sigla_correlativo,
	c.codigo_correlativo,
	c.nombre_agente,
	c.num_dias_para_alertar_vencimiento,
	c.c_costos,
	c.created_at,
	c.fecha_inicio_agente,
	c.estado_resolucion,
	c.status
	
FROM 
	cont_contrato c
	INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
	INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
	INNER JOIN tbl_areas a ON p.area_id = a.id 
	INNER JOIN cont_inmueble i ON c.contrato_id = i.contrato_id
	LEFT JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id AND r.status = 1
	LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
WHERE 
	c.tipo_contrato_id = 6
	AND c.etapa_id=5 
	AND c.status IN (1,2) 
	$where_empresa
	$where_nombre_agente
	$where_c_costos
    $where_ubigeo
	$where_fecha_solicitud
	$where_fecha_inicio
ORDER BY c.contrato_id DESC
	";

	$list_query = $mysqli->query($query);
	$data = array();

	if($mysqli->error){
		$data[] = array(
			"0" => "error",
			"1" => 'Comunicarse con Soporte, error: ' . $mysqli->error,
			"2" => '',
			"3" => '',
			"4" => '', 
			"5" => '', 
			"6" => '',
			"7" => '',
			"8" => '',
			"9" => '',
			"10" => '',
			"11" => '',
			"12" => ''
		);
		$resultado = array(
			"sEcho" => 1,
			"iTotalREcords" => 1,
			"iTotalDisplayRecords" => 1,
			"aaData" => $data
		);
	} else {
		while($reg = $list_query->fetch_object()) 
		{
			if ( empty(trim($reg->num_dias_para_alertar_vencimiento))) {
				$alerta = '<button type="button" class="btn btn-primary btn-sm" title="Alerta por configurar" onclick="sec_contrato_agente_alerta('.$reg->contrato_id.')">
				  <i class="glyphicon glyphicon-bell"></i>
				</button>';
			} else {
				$alerta = '<button type="button" class="btn btn-success btn-sm" title="Alerta configurada" onclick="sec_contrato_agente_alerta('.$reg->contrato_id.')">
				  <i class="glyphicon glyphicon-bell"></i>
				</button>';
			}

			$data[] = array(
				"0" => $reg->sigla_correlativo.$reg->codigo_correlativo,
				"1" => $reg->c_costos,
				"2" => $reg->nombre_agente,
				"3" => $reg->solicitante,
				"4" => $reg->empresa_suscribe,
				"5" => $reg->ruc, 
				"6" => $reg->created_at, 
				"7" => $reg->fecha_suscripcion_contrato,
				"8" => $reg->fecha_inicio_agente, 
				"9" => $reg->estado_resolucion == 2 ? 'Resuelto':'Firmado', 
				"10" => '<a class="btn btn-rounded btn-primary btn-sm" 
							href="./?sec_id=contrato&amp;sub_sec_id=detalle_agente&id=' . $reg->contrato_id . '"
							title="Ver detalle">
							<i class="fa fa-eye"></i> Ver
						</a>',
				"11" => $alerta,
				"12" => $reg->status
			);
		}

		$resultado = array(
			"sEcho" => 1,
			"iTotalREcords" => count($data),
			"iTotalDisplayRecords" => count($data),
			"aaData" => $data
		);
	}
	echo json_encode($resultado);
}

if (isset($_POST["accion"]) && $_POST["accion"]==="cont_agente_reporte_excel")
{
	global $mysqli;
	$empresa_id = $_POST['empresa_id'];
	$nombre_agente = $_POST['nombre_agente'];
	$cont_agente_param_cc_costos = $_POST['cont_agente_cc_costos'];
	$fecha_inicio_solicitud = $_POST['fecha_inicio_solicitud'];
	$fecha_fin_solicitud = $_POST['fecha_fin_solicitud'];
	$fecha_inicio_inicio = $_POST['fecha_inicio_inicio'];
	$fecha_fin_inicio = $_POST['fecha_fin_inicio'];
	$id_departamento = $_POST['id_departamento'];
	$id_provincia = $_POST['id_provincia'];
	$id_distrito = $_POST['id_distrito'];
 
	$where_empresa = "";
	$where_nombre_agente= "";
	$where_c_costos = "";
	$where_ubigeo = '';
	$where_fecha_solicitud = "";
	$where_fecha_inicio = "";

	if (!Empty($empresa_id))
	{
		$where_empresa = " AND c.empresa_suscribe_id = '".$empresa_id."' ";
	}
	
	if (!Empty($nombre_agente)) {
		$where_nombre_agente = " AND c.nombre_agente LIKE '%".$nombre_agente."%' ";
	}

	if (!Empty($cont_agente_param_cc_costos)) {
		$where_c_costos = " AND c.c_costos LIKE '%".$cont_agente_param_cc_costos."%' ";
	}

	if (!Empty($id_departamento)) {
		$where_ubigeo = " AND i.ubigeo_id LIKE '%".$id_departamento.$id_provincia.$id_distrito."%'";
	}

	if (!Empty($fecha_inicio_solicitud) && !Empty($fecha_fin_solicitud)) {
		$where_fecha_solicitud = " AND c.created_at BETWEEN '$fecha_inicio_solicitud 00:00:00' AND '$fecha_fin_solicitud 23:59:59'";
	} elseif (!Empty($fecha_inicio_solicitud)) {
		$where_fecha_solicitud = " AND c.created_at >= '$fecha_inicio_solicitud 00:00:00'";
	} elseif (!Empty($fecha_fin_solicitud)) {
		$where_fecha_solicitud = " AND c.created_at <= '$fecha_fin_solicitud 23:59:59'";
	}

	if (!Empty($fecha_inicio_inicio) && !Empty($fecha_fin_inicio)) {
		$where_fecha_inicio = " AND c.fecha_inicio_agente BETWEEN '$fecha_inicio_inicio 00:00:00' AND '$fecha_fin_inicio 23:59:59'";
	} elseif (!Empty($fecha_inicio_inicio)) {
		$where_fecha_inicio = " AND c.fecha_inicio_agente >= '$fecha_inicio_inicio 00:00:00'";
	} elseif (!Empty($fecha_fin_inicio)) {
		$where_fecha_inicio = " AND c.fecha_inicio_agente <= '$fecha_fin_inicio 23:59:59'";
	}

	$query = "
SELECT
	c.contrato_id, 
	a.nombre AS area, 
	CONCAT(IFNULL(p.nombre, ''), ' ',  IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS solicitante,
	CONCAT(IFNULL(p2.nombre, ''),' ',IFNULL(p2.apellido_paterno, ''),' ',IFNULL(p2.apellido_materno, '')) AS persona_responsable,
	r.ruc,
	r.nombre AS empresa_suscribe,
	c.fecha_suscripcion_contrato,
	co.sigla AS sigla_correlativo,
	c.codigo_correlativo,
	c.nombre_agente,
	c.num_dias_para_alertar_vencimiento,
	c.c_costos,
	c.created_at,
	IF(c.periodo =1,'Año(s)','Mes(es)') as periodo,
	c.periodo_numero,
	ctp.nombre  as vigencia,
	c.fecha_inicio_agente,
	c.fecha_fin_agente,
	i.ubigeo_id,
	ude.nombre AS departamento, 
	upr.nombre AS provincia,
	udi.nombre AS distrito,
	i.ubicacion,
	tp.nombre AS tipo_persona,
													
		td.nombre AS tipo_docu_identidad,
		pers.num_docu,
		pers.num_ruc,
		pers.nombre,
		pers.direccion,
		pers.representante_legal,
		pers.num_partida_registral,
		pers.contacto_nombre,
		pers.contacto_telefono,
		pers.contacto_email,
		c.renovacion_automatica,
		c.estado_resolucion
FROM 
	cont_contrato c
	INNER JOIN tbl_usuarios u ON c.user_created_id = u.id
	INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
	LEFT JOIN tbl_usuarios u2 ON c.persona_responsable_id = u2.id
	LEFT JOIN tbl_personal_apt p2 ON u2.personal_id = p2.id
	INNER JOIN tbl_areas a ON p.area_id = a.id 
	INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id AND r.status = 1
	LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
	LEFT JOIN cont_inmueble i ON  i.contrato_id =c.contrato_id
	LEFT JOIN tbl_ubigeo ude ON SUBSTRING(i.ubigeo_id, 1, 2) = ude.cod_depa AND ude.cod_prov = '00' AND ude.cod_dist = '00'
	LEFT JOIN tbl_ubigeo upr ON SUBSTRING(i.ubigeo_id, 1, 2) = upr.cod_depa AND SUBSTRING(i.ubigeo_id, 3, 2) = upr.cod_prov AND upr.cod_dist = '00'
	LEFT JOIN tbl_ubigeo udi ON SUBSTRING(i.ubigeo_id, 1, 2) = udi.cod_depa AND SUBSTRING(i.ubigeo_id, 3, 2) = udi.cod_prov AND SUBSTRING(i.ubigeo_id, 5, 2) = udi.cod_dist

	LEFT JOIN cont_propietario pr ON pr.contrato_id=c.contrato_id
	LEFT  JOIN cont_persona pers ON pr.persona_id = pers.id
	LEFT JOIN cont_tipo_persona tp ON pers.tipo_persona_id = tp.id
	LEFT JOIN cont_tipo_docu_identidad td ON pers.tipo_docu_identidad_id = td.id
	
	LEFT JOIN cont_tipo_plazo ctp ON ctp .id =c.plazo_id_agente 
	
WHERE 
	c.tipo_contrato_id = 6
	AND c.etapa_id = 5 
	AND c.status = 1 
	$where_empresa
	$where_nombre_agente
	$where_c_costos
	$where_ubigeo
	$where_fecha_solicitud
	$where_fecha_inicio
ORDER BY c.contrato_id DESC
";

// $supervisor = trim($sel["persona_responsable"]);
	$list_query = $mysqli->query($query);

	if($list_query->num_rows == 0){
		// retorna mensaje indicando que no hay data
		echo json_encode(array(
			"estado_archivo" => 2
		));
		exit();
	}

	//PROCEDEMOS A CREAR LA CARPETASI EN CASO NO EXISTA
	$path = "/var/www/html/files_bucket/contratos/descargas/";

	if (!is_dir($path)) 
	{
		mkdir($path, 0777, true);
	}

	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/contratos/descargas/*'); 
	 //obtenemos todos los nombres de los ficheros
	foreach($files as $file)
	{
	    if(is_file($file))
	    unlink($file); //elimino el fichero
	}


	require_once '../phpexcel/classes/PHPExcel.php';
	$objPHPExcel = new PHPExcel();

	// Se asignan las propiedades del libro
	$objPHPExcel->getProperties()->setCreator("AT") // Nombre del autor
    ->setDescription("Reporte"); //Descripción

	$tituloReporte = "Reporte Agentes";


	//Se agregan los datos a la lista del reporte
	$vigencia = '';
	$cont = 0;

	$i = 2; //Numero de fila donde se va a comenzar a rellenar los datos
	$max_numero_columnas =0;
	// var_dump( $fila['contrato_id']);exit();

	if ($list_query->num_rows > 0) {
		while ($fila = $list_query->fetch_array()) 
		{
			$cont ++;
			$sel_query_ccom=null;
			$sel_query_ccom = $mysqli->query("SELECT 
			c.participacion_id,
			c.porcentaje_participacion,
			c.condicion_comercial_id,
			p.nombre as nombre_participacion,
			m.nombre as nombre_condicion
			 FROM cont_cc_agente c
			LEFT JOIN cont_participaciones p ON c.participacion_id = p.id 
			 LEFT JOIN cont_condiciones_comerciales m ON c.condicion_comercial_id = m.id
			 WHERE c.contrato_id =  " . $fila['contrato_id']);
				$columna = 'AH';
				// $array_base=['PORCENTAJE DE PARTICIPACIÓN','NOMBRE DE PARTICIPACIÓN','CONDICIÓN DE PARTICIPACIÓN'];
				$array=[];
				$cantidad_base=0;
				$resultados=[];
				while($row=$sel_query_ccom->fetch_assoc()){
					$resultados[$columna] = $row['nombre_participacion'];
					$columna++;
					$resultados[$columna] = $row['porcentaje_participacion'];
					$columna++;
					$resultados[$columna] = $row['nombre_condicion'];
					$columna++;
				}
				
			$renovacion_automatica = $fila['renovacion_automatica'] == 1 ? 'SI':'NO';

			$etapa_contrato = $fila['estado_resolucion'] == 2 ? 'Resuelto':'Firmado';
			$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A'.$i, $fila['sigla_correlativo'] . $fila['codigo_correlativo'])
			->setCellValue('B'.$i, $fila['c_costos'])
			->setCellValue('C'.$i, $fila['nombre_agente'])
			->setCellValue('D'.$i, $fila['area'])
			->setCellValue('E'.$i, $fila['solicitante'])
			->setCellValue('F'.$i, $fila['empresa_suscribe'])
			->setCellValue('G'.$i, $fila['ruc'])
			->setCellValue('H'.$i, $fila['vigencia'])
	
			->setCellValue('I'.$i, $fila['created_at'])
			->setCellValue('J'.$i, $fila['periodo'])
			->setCellValue('K'.$i, $fila['periodo_numero'])
	
	
			->setCellValue('L'.$i, $fila['fecha_suscripcion_contrato'])
			->setCellValue('M'.$i, $fila['fecha_inicio_agente'])
			->setCellValue('N'.$i, $fila['fecha_fin_agente'])
			->setCellValue('O'.$i, $etapa_contrato)
			->setCellValue('P'.$i, $renovacion_automatica)
			->setCellValue('Q'.$i, $fila['persona_responsable'])
	
			->setCellValue('R'.$i, $fila['ubigeo_id'])
			->setCellValue('S'.$i, $fila['departamento'])
			->setCellValue('T'.$i, $fila['provincia'])
			->setCellValue('U'.$i, $fila['distrito'])
			->setCellValue('V'.$i, $fila['ubicacion'])
	
			//DATOS DE PROPIETARIO 
			->setCellValue('W'.$i, $fila['tipo_persona'])
			->setCellValue('X'.$i, $fila['tipo_docu_identidad'])
			->setCellValue('Y'.$i, $fila['num_docu'])
			->setCellValue('Z'.$i, $fila['num_ruc'])
			->setCellValue('AA'.$i, $fila['nombre'])
			->setCellValue('AB'.$i, $fila['direccion'])
			->setCellValue('AC'.$i, $fila['representante_legal'])
			->setCellValue('AD'.$i, $fila['num_partida_registral'])
			->setCellValue('AE'.$i, $fila['contacto_nombre'])
			->setCellValue('AF'.$i, $fila['contacto_telefono'])
			->setCellValue('AG'.$i, $fila['contacto_email']);
			
			// Bucle para agregar las columnas adicionales - condiciones comerciales
			foreach ($resultados as $columna=>$value) {
	
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue($columna.$i,$value); // Cambia 'valor' por el valor que deseas agregar en la columna
				
			}
			if(sizeof($resultados)> $max_numero_columnas){
				$max_numero_columnas = sizeof($resultados);
			}
			$i++;
		}
	
	}
	

	$titulosColumnas = array('Nº', 'CENTRO DE COSTOS', 'NOMBRE DE AGENTE', 'ÁREA SOLICITANTE', 'SOLICITANTE',
	'EMPRESA QUE SUSCRIBE', 'RUC','VIGENCIA',  'FECHA SOLICITUD','PERIODO','NUMERO DE PERIODO','FECHA SUSCRIPCIÓN', 'FECHA INICIO', 'FECHA FIN','ETAPA','RENOVACIÓN AUTOMÁTICA',
	'SUPERVISOR' ,'UBIGEO','DEPARTAMENTO','PROVINCIA','DISTRITO','UBICACIÓN',
	'TIPO DE PERSONA PROPIETARIO','TIPO DE DOCUMENTO PROPIETARIO','NRO. DOCUMENTO PROPIETARIO','RUC PROPIETARIO','NOMBRE PROPIETARIO','DIRECCIÓN PROPIETARIO','REPRESENTANTE LEGAL','PARTIDA REGISTRAL',
	'CONTACTO','TELEFONO DE CONTACTO','EMAIL PROPIETARIO'
	);

	// Se agregan los titulos del reporte
	$objPHPExcel->setActiveSheetIndex(0)
	->setCellValue('A1', $titulosColumnas[0])    //Titulo de las columnas
	->setCellValue('B1', $titulosColumnas[1])
	->setCellValue('C1', $titulosColumnas[2])
	->setCellValue('D1', $titulosColumnas[3])
	->setCellValue('E1', $titulosColumnas[4])
	->setCellValue('F1', $titulosColumnas[5])
	->setCellValue('G1', $titulosColumnas[6])
	->setCellValue('H1', $titulosColumnas[7])
	->setCellValue('I1', $titulosColumnas[8])
	->setCellValue('J1', $titulosColumnas[9])
	->setCellValue('K1', $titulosColumnas[10])
	->setCellValue('L1', $titulosColumnas[11])
	
	->setCellValue('M1', $titulosColumnas[12])
	->setCellValue('N1', $titulosColumnas[13])
	->setCellValue('O1', $titulosColumnas[14])
	->setCellValue('P1', $titulosColumnas[15])
	->setCellValue('Q1', $titulosColumnas[16])

	// DATOS DE PROPIETARIO 
	->setCellValue('R1', $titulosColumnas[17])
	->setCellValue('S1', $titulosColumnas[18])
	->setCellValue('T1', $titulosColumnas[19])
	->setCellValue('U1', $titulosColumnas[20])
	->setCellValue('V1', $titulosColumnas[21])
	->setCellValue('W1', $titulosColumnas[22])
	->setCellValue('X1', $titulosColumnas[23])
	->setCellValue('Y1', $titulosColumnas[24])
	->setCellValue('Z1', $titulosColumnas[25])
	->setCellValue('AA1', $titulosColumnas[26])
	->setCellValue('AB1', $titulosColumnas[27])

	->setCellValue('AC1', $titulosColumnas[28])
	->setCellValue('AD1', $titulosColumnas[29])
	->setCellValue('AE1', $titulosColumnas[30])
	->setCellValue('AF1', $titulosColumnas[31])
	->setCellValue('AG1', $titulosColumnas[32])
	;
	$index=0;$columna='AH';
	$titulos_adicionales=['NOMBRE DE PARTICIPACIÓN','PORCENTAJE DE PARTICIPACIÓN','CONDICIÓN DE PARTICIPACIÓN'];
	if($list_query->num_rows > 0){
		for($i=0;$i<$max_numero_columnas;$i++){
			$index = $index<3?$index:0;
	
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($columna.'1',$titulos_adicionales[$index]); // Cambia 'valor' por el valor que deseas agregar en la columna
			$index++;
			$cel_ultima=$columna.'1';
			$columna++;
		}
	}else{
		$cel_ultima=$columna.'1';

	}
	
	
	// var_dump($max_numero_columnas);exit();
	$estiloNombresColumnas = array(
		'font' => array(
	        'name'      => 'Calibri',
	        'bold'      => true,
	        'italic'    => false,
	        'strike'    => false,
	        'size' =>10,
	        'color'     => array(
	            'rgb' => 'ffffff'
	        )
	    ),
	    'fill' => array(
			  'type'  => PHPExcel_Style_Fill::FILL_SOLID,
			  'color' => array(
			        'rgb' => '000000')
			),
	    'alignment' =>  array(
	        'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        'wrap'      => false
	    )
	);

	$estiloInformacion = new PHPExcel_Style();
	$estiloInformacion->applyFromArray( array(
	    'font' => array(
	        'name'  => 'Arial',
	        'color' => array(
	            'rgb' => '000000'
	        )
	    )
	));

	$estilo_centrar = array(
	    'alignment' =>  array(
	        'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        'wrap'      => false
	    )
	);

	$objPHPExcel->getActiveSheet()->getStyle('A1:'.$cel_ultima)->applyFromArray($estiloNombresColumnas);
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:M".($i-1));
	$objPHPExcel->getActiveSheet()->getStyle('A1:A'.($i-1))->applyFromArray($estilo_centrar);
	$objPHPExcel->getActiveSheet()->getStyle('H1:L'.($i-1))->applyFromArray($estilo_centrar);

	// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
	for($i = 'A'; $i <= 'G'; $i++)
	{
	    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}

	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('contrato agente');
	  
	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);
	
	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(0);
	
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Contrato de Agentes AT.xls');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$excel_path = '/var/www/html/files_bucket/contratos/descargas/Contrato_de_agentes_AT.xls';
	$excel_path_download = '/files_bucket/contratos/descargas/Contrato_de_agentes_AT.xls';

	try 
	{
		$objWriter->save($excel_path);
	} 
	catch (Exception $e)
	{
		echo json_encode(["error" => $e]);
		exit;
	}

	echo json_encode(array(
		"ruta_archivo" => $excel_path_download,
		"estado_archivo" => 1
	));
	exit;
}



if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_documentos_incompletos") {

	$contrato_id = $_POST["contrato_id"];
	$html = '';

	$sql = "
	SELECT 
		a.archivo_id,
		a.contrato_id,
		t.tipo_archivo_id,
		t.nombre_tipo_archivo,
		a.nombre,
		a.extension,
		a.ruta
	FROM
		cont_tipo_archivos t
		LEFT JOIN cont_archivos a ON t.tipo_archivo_id = a.tipo_archivo_id AND a.contrato_id = '$contrato_id' AND a.status = 1
	WHERE
		t.tipo_archivo_id IN (12,13,8, 4, 34, 11)
		AND a.nombre IS NULL
	ORDER BY t.tipo_archivo_id ASC
	";
	
	$query = $mysqli->query($sql);
	$row_count = $query->num_rows;

	if ($row_count > 0) 
	{
		$contador = 1;

		$html = '<table class="table table-bordered table-hover no-mb" style="font-size:11px; margin-top: 10px;">';
		$html .= '<thead>';

		$html .= '<tr>';
		$html .= '<th>#</th>';
		$html .= '<th>Documento</th>';
		$html .= '</tr>';

		$html .= '</thead>';
		$html .= '<tbody>';

		while ($row = $query->fetch_assoc()) {			
			
			$html .= '<tr>';
			$html .= '<td>' . $contador . '</td>';
			$html .= '<td>' . $row["nombre_tipo_archivo"] . '</td>';
			$html .= '</tr>';

			$contador++;			
		}

		$html .= '</tbody>';
		$html .= '</table>';

	}

	if($mysqli->error){
		$result["http_code"] = 400;
		$result["consulta_error"] = $mysqli->error;
	} else {
		$result["http_code"] = 200;
		$result["cant_mensaje"] = $row_count;
		$result["result"] = $html;
	}
	
	$result["status"] = "Datos obtenidos de gestion.";

	echo json_encode($result);
	exit();
}



if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_contrato_firmado") {
	$usuario_id = $login?$login['id']:null;
	if ((int) $usuario_id > 0) {
		$contrato_id = $_POST["contrato_id"];
		$renovacion_automatica = $_POST["renovacion_automatica"];
		$fecha_inicio_sin_formato = str_replace("/","-",$_POST["fecha_inicio"]);
		$fecha_suscripcion_sin_formato = str_replace("/","-",$_POST["fecha_suscripcion"]);
		$fecha_inicio = date("Y-m-d", strtotime($fecha_inicio_sin_formato));
		$plazo_id = $_POST["plazo_id"];
		if($plazo_id == 1){
			$fecha_fin_sin_formato = str_replace("/","-",$_POST["fecha_fin"]);
			$fecha_fin = "'".date("Y-m-d", strtotime($fecha_fin_sin_formato))."'"; 
		}else{
			$fecha_fin = "NULL";
		}
		$fecha_suscripcion = date("Y-m-d", strtotime($fecha_suscripcion_sin_formato)); 

		$query = "
		SELECT
			c.persona_responsable_id, 
			c.jefe_comercial_id,
			c.nombre_tienda,
			c.abogado_id,
			i.latitud,
			i.longitud,
			c.c_costos
		FROM 
			cont_contrato c
			INNER JOIN cont_inmueble i ON c.contrato_id = i.contrato_id
		WHERE 
			c.contrato_id = $contrato_id
		";

		$list_query = $mysqli->query($query);
		$row = $list_query->fetch_assoc();

		if (empty($row['c_costos'])) {
			$result["http_code"] = 400;
			$result["error"] = 'sin_asignar';
			$result["campo_incompleto"] = 'centro_de_costos';
			exit(json_encode($result));
		} elseif (empty($row['persona_responsable_id'])) {
			$result["http_code"] = 400;
			$result["error"] = 'sin_asignar';
			$result["campo_incompleto"] = 'supervisor';
			exit(json_encode($result));
		// } elseif (empty($row['abogado_id'])) {
		// 	$result["http_code"] = 400;
		// 	$result["error"] = 'sin_asignar';
		// 	$result["campo_incompleto"] = 'abogado';
		// 	exit(json_encode($result));
		} 

		$created_at = date("Y-m-d H:i:s");
		$error = '';

		$nombre_tienda = sec_contrato_agente_formato_nombre_de_agente( $_POST["nombre_tienda"] );

		$path = "/var/www/html/files_bucket/contratos/contratos_firmados/agentes/";

		if (isset($_FILES['archivo_contrato']) && $_FILES['archivo_contrato']['error'] === UPLOAD_ERR_OK) {
			if (!is_dir($path)) mkdir($path, 0777, true);

			$filename = $_FILES['archivo_contrato']['name'];
			$filenametem = $_FILES['archivo_contrato']['tmp_name'];
			$filesize = $_FILES['archivo_contrato']['size'];
			$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
			if($filename != ""){
				$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
				$nombre_archivo = $contrato_id . "_CONTRATO_FIRMADO_" . date('YmdHis') . "." . $fileExt;
				move_uploaded_file($filenametem, $path . $nombre_archivo);
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
								16,
								'" . $nombre_archivo . "',
								'" . $fileExt . "',
								'" . $filesize . "',
								'" . $path . "',
								" . $usuario_id . ",
								'" . $created_at . "'
								)";
				$mysqli->query($comando);

				if($mysqli->error){
					$error .= 'Error al guardar el archivo: ' . $mysqli->error . $comando;
				}
			}
		}

		$query_update = "
		UPDATE 
			cont_contrato 
		SET 
			fecha_inicio_agente = '$fecha_inicio',
			fecha_fin_agente = ".$fecha_fin.",
			fecha_suscripcion_contrato = '$fecha_suscripcion',
			usuario_id_contrato_aprobado_agente = '$usuario_id',
			nombre_agente = '$nombre_tienda',
			plazo_id_agente = $plazo_id,
			etapa_id = '5',
			renovacion_automatica = '$renovacion_automatica'
		WHERE 
			contrato_id = $contrato_id
		";
		$mysqli->query($query_update);

		if($mysqli->error){
			$error .= 'Error al cambiar la etapa del contrato: ' . $mysqli->error . $query_update;
		} 
		else 
		{
			send_email_solicitud_contrato_agente_firmada($_POST["contrato_id"]);
			// registrarFileConcar_Sispag($_POST["contrato_id"]);
			// insertar_nuevo_local_en_tbl_locales($_POST["contrato_id"]);
		}

		if ($error == '') {
			$result["http_code"] = 200;
		} else {
			$result["http_code"] = 400;
		}

		$result["error"] = $error;	
		$result["status"] = "Datos obtenidos de gestion.";
	} else {
		$result["http_code"] = 400;
		$result["error"] = "La sesión ha caducado. Se recomienda abrir una nueva pestaña e ingresar a gestión, ingresar su usuario y contraseña, ya logueado volver a esta pestaña y volver a hacer clic en el botón: Agregar contrato firmado.";
	}

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_adenda_contrato_agente_firmada") {
	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	$contrato_id = $_POST["contrato_id"];
	$index = $_POST["index"];
	$path = "/var/www/html/files_bucket/contratos/adendas/agentes/";

	$adenda_id = $_POST["adenda_id"];
	// $query = "SELECT a.abogado_id FROM cont_adendas a WHERE a.id = $adenda_id";
	// $list_query = $mysqli->query($query);
	// $row = $list_query->fetch_assoc();

	// if (empty($row['abogado_id'])) {
	// 	$result["http_code"] = 400;
	// 	$result["error"] = 'sin_asignar';
	// 	$result["campo_incompleto"] = 'abogado';
	// 	exit(json_encode($result));
	// }

	$name_file = 'adenda_firmada_'.$_POST["adenda_id"];
	$archivo_id = 0;
	if (isset($_FILES[$name_file]) && $_FILES[$name_file]['error'] === UPLOAD_ERR_OK) {
		if (!is_dir($path)) mkdir($path, 0777, true);

		$filename = $_FILES[$name_file]['name'];
		$filenametem = $_FILES[$name_file]['tmp_name'];
		$filesize = $_FILES[$name_file]['size'];
		$ext = pathinfo($filenametem, PATHINFO_EXTENSION);
		if($filename != ""){
			$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
			$nombre_archivo = $contrato_id . "_ADENDA_FIRMADA_" . date('YmdHis') . "." . $fileExt;
			move_uploaded_file($filenametem, $path . $nombre_archivo);
			$comando = "INSERT INTO cont_archivos (
							contrato_id,
							adenda_id,
							tipo_archivo_id,
							nombre,
							extension,
							size,
							ruta,
							user_created_id,
							created_at)
						VALUES(
							'" . $contrato_id . "',
							'" . $_POST["adenda_id"] . "',
							97,
							'" . $nombre_archivo . "',
							'" . $fileExt . "',
							'" . $filesize . "',
							'" . $path . "',
							" . $usuario_id . ",
							'" . $created_at . "'
							)";
			$mysqli->query($comando);
			$archivo_id = mysqli_insert_id($mysqli);
		}
	}

	$query = $mysqli->query("
	SELECT contrato_id
	FROM cont_adendas
	WHERE id = " . $_POST["adenda_id"] . "
	AND status = 1
	AND procesado = 0");
	$row_count = $query->num_rows;
	if ($row_count > 0) {
		$row = $query->fetch_assoc();
		$contrato_id = $row["contrato_id"];

		$query = $mysqli->query("
		SELECT id,
			adenda_id,
			nombre_tabla,
			valor_original,
			nombre_campo_usuario,
			nombre_menu_usuario,
			nombre_campo,
			tipo_valor,
			valor_varchar,
			valor_int,
			valor_date,
			valor_decimal,
			valor_select_option,
			valor_id_tabla,
			id_del_registro_a_modificar,
			status
		FROM cont_adendas_detalle
		WHERE adenda_id = " . $_POST["adenda_id"] . "
		AND status = 1
		");
		$row_count = $query->num_rows;
		if ($row_count > 0) {
			while($row = $query->fetch_assoc()){
				$nombre_tabla = $row["nombre_tabla"];
				$nombre_campo = $row["nombre_campo"];
				$valor_original = $row["valor_original"];
				$valor_id_tabla = $row['valor_id_tabla'];
				$id_del_registro_a_modificar = $row['id_del_registro_a_modificar'];

				$tipo_valor = $row["tipo_valor"];

				
				if ($tipo_valor == "registro"){
					// agregar nuevos registros beneficiario y contraprestación
					if ($row['nombre_menu_usuario'] == "Cuenta Bancaria"){
						$query_update = "
						UPDATE " . $nombre_tabla . " 
						SET 
							" . $nombre_campo . " = '" . $contrato_id . "'
						WHERE id = " . $row['valor_int'];
						$mysqli->query($query_update);

						$query_rep = $mysqli->query("
							SELECT 	
							rl.vigencia_archivo_id,
							rl.dni_archivo_id
							FROM cont_representantes_legales as rl
							WHERE rl.id = " . $row['valor_int'] . "
							");
							while($rl = $query_rep->fetch_assoc()){
								if($rl['vigencia_archivo_id'] > 0){
									$query_update = "
									UPDATE cont_archivos 
									SET contrato_id = '" . $contrato_id . "'
									WHERE archivo_id = " . $rl['vigencia_archivo_id'];
									$mysqli->query($query_update);
								}
								if($rl['dni_archivo_id'] > 0){
									$query_update = "
									UPDATE cont_archivos 
									SET contrato_id = '" . $contrato_id . "'
									WHERE archivo_id = " . $rl['dni_archivo_id'];
									$mysqli->query($query_update);
								}
							}
							
						
					}

					if ($row['nombre_menu_usuario'] == "Contraprestación"){
						$query_update = "
						UPDATE " . $nombre_tabla . " 
						SET 
							" . $nombre_campo . " = '" . $contrato_id . "'
						WHERE id = " . $row['valor_int'];
						$mysqli->query($query_update);
					}

					if ($row['nombre_menu_usuario'] == "Propietario"){
						$query_update = "
						UPDATE " . $nombre_tabla . " 
						SET 
							" . $nombre_campo . " = '" . $contrato_id . "'
						WHERE propietario_id = " . $row['valor_int'];
						$mysqli->query($query_update);
					}
	
					if($mysqli->error){
						$result["update_error"] = $mysqli->error;
					}
				}else {

					if($nombre_tabla == "cont_contrato" && $nombre_campo == "fecha_fin_agente"){
						$update_alerta = "UPDATE cont_contrato SET alerta_enviada_id = NULL WHERE contrato_id = ".$contrato_id;
						$mysqli->query($update_alerta);	
					}

					//modificar tablas
					if ($tipo_valor == 'varchar') {
						$nuevo_valor = $row['valor_varchar'];
					} else if ($tipo_valor == 'int') {
						$nuevo_valor = $row['valor_int'];
					} else if ($tipo_valor == 'date') {
						$nuevo_valor = $row['valor_date'];
					} else if ($tipo_valor == 'decimal') {
						$nuevo_valor = $row['valor_decimal'];
					} else if ($tipo_valor == 'select_option') {
						if($nombre_campo == "ubigeo_id"){
							$nuevo_valor = $row['valor_varchar'];
						}else{
							$nuevo_valor = $row['valor_int'];
						}
					} else if ($tipo_valor == 'id_tabla') {
						$nuevo_valor = $row['valor_int'];
					}
	
					if ($id_del_registro_a_modificar > 0) {
						$query_update = "
						UPDATE " . $nombre_tabla . " 
						SET 
							" . $nombre_campo . " = '" . $nuevo_valor . "'
						WHERE id = " . $id_del_registro_a_modificar;
					} else {
						$query_update = "
						UPDATE " . $nombre_tabla . " 
						SET 
							" . $nombre_campo . " = '" . $nuevo_valor . "'
						WHERE contrato_id = " . $contrato_id;
					}
						
					$mysqli->query($query_update);
	
					if($mysqli->error){
						$result["update_error"] = $mysqli->error;
					}
				}
				
			}

			$query_update = "
			UPDATE cont_adendas 
			SET 
				procesado = 1,
				archivo_id = ".$archivo_id."
			WHERE id = '". $_POST["adenda_id"] ."'";
			$mysqli->query($query_update);

			send_email_adenda_contrato_agente_firmada($_POST["adenda_id"], false);

			if($mysqli->error){
				$result["update_error"] = $mysqli->error;
			}
		}
	}

	$result["result"] = 'ok';
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="reenviar_adenda_firmada") {
	
	try{
		if($_POST['tipo_contrato'] == 6){
			send_email_adenda_contrato_agente_firmada($_POST["adenda_id"], true);
		}

		$result['status'] = 200;
		$result['result'] = '';
		$result['message'] = 'Se ha reenviado correctamente la adenda firmada.';
		echo json_encode($result);
		exit();
	} catch (\Throwable $th) {
		$result['status'] = 404;
		$result['result'] = $th->getMessage();
		$result['message'] = 'A ocurrido un error, intentelo mas tarde.';
		echo json_encode($result);
		exit();
	}
}




function sec_contrato_agente_formato_nombre_de_agente( $nombre_de_agente ) {
	$nombre_del_agente = str_replace( " At ", " AT ", ucwords( mb_strtolower( preg_replace(['/\s+/','/^\s|\s$/'],[' ',''], trim( $nombre_de_agente ) ) ) ) ) ;
	return $nombre_del_agente;
}

if (isset($_POST["accion"]) && $_POST["accion"]==="actualizar_alerta_contrato") {
	$contrato_id = $_POST["contrato_id"];
	$num_dias = $_POST["num_dias"];

	$query_update = "
	UPDATE cont_contrato
	SET num_dias_para_alertar_vencimiento = '$num_dias',
	alerta_enviada_id = NULL
	WHERE contrato_id = '$contrato_id' 
	";
	
	$mysqli->query($query_update);

	if($mysqli->error) {
		$result["http_code"] = 400;
		$result["error"] = $mysqli->error;
	} else {
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
	}

	echo json_encode($result);
	exit();
}
?>

