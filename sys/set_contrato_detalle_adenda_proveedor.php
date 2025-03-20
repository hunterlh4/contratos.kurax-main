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


function send_email_solicitud_adenda_contrato_proveedor($adenda_id)
{
	include("db_connect.php");
	include("sys_login.php");
	
	$host = $_SERVER["HTTP_HOST"];

	$sel_query = $mysqli->query("
	SELECT 
		a.id, 
		a.created_at, 
		co.sigla, 
		c.codigo_correlativo, 
		c.contrato_id, 
		c.empresa_suscribe_id,
		tc.nombre,
		concat( IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS usuario_solicitante,
		r.nombre AS empresa_suscribe, ar.nombre AS nombre_area,
		tp.correo as email_creacion_adenda,
		per.correo AS email_creacion_contrato,
		peg.correo AS email_del_gerente_area,
		puap.correo AS email_del_aprobante,
		pera.correo AS email_del_aprobante_adenda
	FROM 
		cont_adendas AS a 
		INNER JOIN cont_contrato AS c ON c.contrato_id = a.contrato_id
		INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id
		INNER JOIN cont_tipo_contrato AS tc ON tc.id = c.tipo_contrato_id
		INNER JOIN tbl_usuarios AS tu	ON tu.id = a.user_created_id
		INNER JOIN tbl_personal_apt AS tp ON tp.id = tu.personal_id
		INNER JOIN tbl_areas AS ar ON tp.area_id = ar.id
		LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato

		LEFT JOIN tbl_usuarios uaa ON a.aprobado_por_id = uaa.id
		LEFT JOIN tbl_personal_apt pera ON uaa.personal_id = pera.id

		LEFT JOIN tbl_usuarios u ON c.user_created_id = u.id
		LEFT JOIN tbl_personal_apt per ON u.personal_id = per.id

		LEFT JOIN tbl_usuarios ug ON c.gerente_area_id = ug.id
		LEFT JOIN tbl_personal_apt peg ON ug.personal_id = peg.id

		LEFT JOIN tbl_usuarios uap ON c.aprobado_por = uap.id
		LEFT JOIN tbl_personal_apt puap ON uap.personal_id = puap.id
	WHERE a.id = $adenda_id
	");


	$body = "";
	$body .= '<html>';

	$usuario_creacion_correo = '';
	$contrato_id = 0;
	$correos_adicionales = [];

	while($sel = $sel_query->fetch_assoc())
	{
		$contrato_id = $sel['contrato_id']; 
		$empresa_suscribe_id = $sel['empresa_suscribe_id']; 
		$sigla_correlativo = $sel['sigla'];
		$codigo_correlativo = $sel['codigo_correlativo'];
		$empresa_suscribe = $sel['empresa_suscribe'];
		$fecha_solicitud = $sel['created_at'];
		$usuario_solicitante = $sel['usuario_solicitante'];
		if(!Empty($sel['email_creacion_adenda'])){
			array_push($correos_adicionales, $sel['email_creacion_adenda']);
		}
		if(!Empty($sel['email_creacion_contrato'])){
			array_push($correos_adicionales, $sel['email_creacion_contrato']);
		}
		if(!Empty($sel['email_del_gerente_area'])){
			array_push($correos_adicionales, $sel['email_del_gerente_area']);
		}
		if(!Empty($sel['email_del_aprobante'])){
			array_push($correos_adicionales, $sel['email_del_aprobante']);
		}
		if(!Empty($sel['email_del_aprobante_adenda'])){
			array_push($correos_adicionales, $sel['email_del_aprobante_adenda']);
		}

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

		$body .= '<thead>';
		
		$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>Solicitud Modificada</b>';
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
		AND tipo_valor = 'registro'
		AND status = 1");
		$row_count = $query->num_rows;
		$numero_adenda_detalle = 0;
		if ($row_count > 0) {
			while($row = $query->fetch_assoc()){
				if ($row["nombre_menu_usuario"] == 'Representante Legal') {
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
							$body .= '<b>Nuevo Representante Legal</b>';
						$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
						$body .= '<td align="center" style="background-color: #ffffdd;"><b>Campo:</b></td>';
						$body .= '<td align="center" style="background-color: #ffffdd;"><b>Nuevo Valor:</b></td>';
					$body .= '</tr>';
					
					$body .= '</thead>';

					$body .= '<tr>';
						$body .= '<td >DNI del representante legal</td>';
						$body .= '<td >'.$valores_nuevos[0]["dni_representante"].'</td>';
					$body .= '</tr>';

					$body .= '<tr>';
						$body .= '<td >Nombre completo del representante legal</td>';
						$body .= '<td >'.$valores_nuevos[0]["nombre_representante"].'</td>';
					$body .= '</tr>';

					$body .= '<tr>';
						$body .= '<td >Nro cuenta de detraccion (Banco de la nación)</td>';
						$body .= '<td >'.$valores_nuevos[0]["nro_cuenta_detraccion"].'</td>';
					$body .= '</tr>';

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

				
			}
		}



	}
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$body .= '<div style="width: 700px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$host.'/?sec_id=contrato&sub_sec_id=detalleSolicitudProveedor&id='.$contrato_id.'" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
					$body .= '<b>Ver Solicitud</b>';
		    $body .= '</a>';
		$body .= '</div>';

		$body .= '</html>';
		$body .= "";

	//lista de correos

	if ($empresa_suscribe_id != 0) {
		$sql_contador_tesorero = "
		SELECT 
			p.correo
		FROM
			cont_usuarios_razones_sociales urs
			INNER JOIN tbl_usuarios u ON urs.user_id = u.id
			INNER JOIN tbl_personal_apt p ON u.personal_id = p.id
		WHERE
			urs.razon_social_id = ".$empresa_suscribe_id."
			AND p.estado = 1  AND u.estado = 1 
		";
		$sel_query = $mysqli->query($sql_contador_tesorero);

		$row_count = $sel_query->num_rows;

		if ($row_count > 0) {
			while($sel = $sel_query->fetch_assoc()){
				array_push($correos_adicionales, $sel['correo']);
			}
		}
	}

	$correos = new Correos(env('SEND_EMAIL'),env('EMAIL_TEST'));
	$lista_correos = $correos->send_email_solicitud_adenda_contrato_proveedor($correos_adicionales);

	$cc = $lista_correos['cc'];

	$titulo_email = "Gestion - Sistema Contratos - Solicitud Modificada de Adenda de Proveedor: Código - ";

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

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_adenda_detalle_contrato") {
	$adenda_id = $_POST['adenda_id'];
	$query = $mysqli->query("SELECT id,
							adenda_id,
							nombre_tabla,
							valor_original,
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
						AND status = 1");
	
	$adendas = array();
	while($row = $query->fetch_assoc()){
		array_push($adendas,array(
			'id' => intval($row['id']),
			'type' => 'registrado',
			'state' => 1,
		));
	}

	$result["status"] = 200;
	$result["message"] = "Se ha registrado exitosamente la adenda de contrato de proveedor.";
	$result["result"] = $adendas;
	$result["error"] = '';
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_adenda_detalle") {

	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	$valor_original = $_POST["valor_original"];
	$tipo_valor = $_POST["tipo_valor"];

	if(isset($_POST["id_del_registro"])) {
		$id_del_registro = empty(trim($_POST["id_del_registro"])) ? 0 : $_POST["id_del_registro"];
	} else {
		$id_del_registro = 0;
	}
	
	$ubigeo_id_nuevo = isset($_POST["ubigeo_id_nuevo"]) ? $_POST["ubigeo_id_nuevo"]:'';
	$ubigeo_text_nuevo = isset($_POST["ubigeo_text_nuevo"]) ? $_POST["ubigeo_text_nuevo"]:''; 

	if ($tipo_valor == 'varchar') {
		$valor_varchar = $_POST["valor_varchar"];
		$valor_int = "NULL";
		$valor_date = "NULL";
		$valor_decimal = "NULL";
		$valor_select_option = "NULL";
		$valor_id_tabla = "NULL";
	} else if ($tipo_valor == 'int') {
		$valor_varchar = "NULL";
		$valor_int = $_POST["valor_int"];
		$valor_date = "NULL";
		$valor_decimal = "NULL";
		$valor_select_option = "NULL";
		$valor_id_tabla = "NULL";
	} else if ($tipo_valor == 'date') {
		$valor_varchar = "NULL";
		$valor_int = "NULL";
		$valor_original = date("Y-m-d", strtotime($valor_original));
		$valor_date = "'" . date("Y-m-d", strtotime($_POST["valor_date"])) . "'";
		$valor_decimal = "NULL";
		$valor_select_option = "NULL";
		$valor_id_tabla = "NULL";
	} else if ($tipo_valor == 'decimal') {
		$valor_varchar = "NULL";
		$valor_int = "NULL";
		$valor_date = "NULL";
		$valor_decimal = str_replace(",","",$_POST["valor_decimal"]);
		$valor_select_option = "NULL";
		$valor_id_tabla = "NULL";
	} else if ($tipo_valor == 'select_option') {

		if($_POST["nombre_campo"] == "ubigeo_id"){
			$valor_varchar = $ubigeo_id_nuevo;
			$valor_int = "NULL";
			$valor_date = "NULL";
			$valor_decimal = "NULL";
			$valor_select_option = $ubigeo_text_nuevo;
			$valor_id_tabla = "NULL";
		}else{
			$valor_varchar = "NULL";
			$valor_int = $_POST["valor_int"];
			$valor_date = "NULL";
			$valor_decimal = "NULL";
			$valor_select_option = $_POST["valor_select_option"];
			$valor_id_tabla = "NULL";
		}
		
	} else if ($tipo_valor == 'id_tabla') {
		$valor_varchar = "NULL";
		$valor_int = $_POST["valor_int"];
		$valor_date = "NULL";
		$valor_decimal = "NULL";
		$valor_select_option = "NULL";
		$valor_id_tabla = $_POST["valor_id_tabla"];
	}


	$query_insert = " INSERT INTO cont_adendas_detalle
	(
	nombre_tabla,
	valor_original,
	nombre_campo,
	nombre_menu_usuario,
	nombre_campo_usuario,
	tipo_valor,
	valor_varchar,
	valor_int,
	valor_date,
	valor_decimal,
	valor_select_option,
	valor_id_tabla,
	id_del_registro_a_modificar,
	user_created_id,
	created_at)
	VALUES
	(
	'" . $_POST["nombre_tabla"] . "',
	'" . $valor_original . "',
	'" . $_POST["nombre_campo"] . "',
	'" . $_POST["nombre_menu_usuario"] . "',
	'" . $_POST["nombre_campo_usuario"] . "',
	'" . $tipo_valor . "',
	'" . $valor_varchar . "',
	" . $valor_int . ",
	" . $valor_date . ",
	" . $valor_decimal . ",
	'" . $valor_select_option . "',
	" . $valor_id_tabla . ",
	" . $id_del_registro . ",
	" . $usuario_id . ",
	'" . $created_at . "')";

	$mysqli->query($query_insert);
	$insert_id = mysqli_insert_id($mysqli);
	$error = '';
	if($mysqli->error){
		$error=$mysqli->error. " | ".$query_insert;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $insert_id;
	$result["error"] = $error;

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_adendas_detalle") {

	
	$adendas = $_POST["adendas"];
	$ids = '';
	$contador = 0;
	for ($i=0; $i < count($adendas); $i++) { 
		if ($adendas[$i]['state'] == 1) {
			if ($contador > 0) {
				$ids .= ',';
			}
			$ids .= $adendas[$i]['id'];			
			$contador++;
		}
	}

	$ids = $ids == ''?999999: $ids;
	$html = '';

	$query = "SELECT id, nombre_menu_usuario, nombre_campo_usuario, valor_original, tipo_valor, valor_varchar, valor_int, valor_date, valor_decimal, valor_select_option
	FROM cont_adendas_detalle 
	WHERE tipo_valor != 'id_tabla' AND tipo_valor != 'registro' ";
	$query .= " AND id IN(" . $ids . ")";
	
	$list_query = $mysqli->query($query);
	$row_count = $list_query->num_rows;

	if ($row_count > 0) {
		$html .= '<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th>#</th>';
		$html .= '<th>Menú</th>';
		$html .= '<th>Campo</th>';
		$html .= '<th>Valor Actual</th>';
		$html .= '<th>Nuevo Valor</th>';
		$html .= '<th></th>';
		$html .= '</tr>';
		$html .= '</thead>';
		$html .= '<tbody>';

		$num = 1;

		while ($row = $list_query->fetch_assoc()) {
			$tipo_valor = $row["tipo_valor"];		

			if ($tipo_valor == 'varchar') {
				$nuevo_valor = $row["valor_varchar"];
			} else if ($tipo_valor == 'int') {
				$nuevo_valor = $row["valor_int"];
			} else if ($tipo_valor == 'date') {
				$nuevo_valor = $row["valor_date"];
			} else if ($tipo_valor == 'decimal') {
				$nuevo_valor = $row["valor_decimal"];
			} else if ($tipo_valor == 'select_option') {
				$nuevo_valor = $row["valor_select_option"];
			}

			$html .= '<tr>';
			$html .= '<td>' . $num . '</td>';
			$html .= '<td>' . $row["nombre_menu_usuario"] . '</td>';
			$html .= '<td>' . $row["nombre_campo_usuario"] . '</td>';
			$html .= '<td style="white-space: pre-line;">' . $row["valor_original"] . '</td>';
			$html .= '<td style="white-space: pre-line;">' . $nuevo_valor . '</td>';
			$html .= '<td>';
			$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_con_detalle_aden_prov_eliminar_detalle_adenda(' . $row["id"] . ')"><i class="fa fa-trash"></i></a>';
			$html .= '</td>';
			$html .= '</tr>';

			$num += 1;	    
		}

		$html .= '</tbody>';
		$html .= '</table>';
	}

	// CAMBIO DE PROPIETARIOS Y BENEFICIARIOS Y INCREMENTOS
	$query_otros = "SELECT id, nombre_menu_usuario, valor_original, valor_int, valor_id_tabla
	FROM cont_adendas_detalle 
	WHERE tipo_valor = 'id_tabla' ";
	$query_otros .= " AND id IN(" . $ids . ")";
	
	$list_query_otros = $mysqli->query($query_otros);
	$row_count_otros = $list_query_otros->num_rows;

	if ($row_count_otros > 0) {
		while ($row = $list_query_otros->fetch_assoc()) {
			if ($row["nombre_menu_usuario"] == 'Propietario') {
				$query = "
				SELECT 
					p.id,
					tp.nombre AS tipo_persona,
					td.nombre AS tipo_docu_identidad,
					p.tipo_persona_id,
					p.tipo_docu_identidad_id,
					p.num_docu,
					p.nombre,
					p.direccion,
					p.representante_legal,
					p.num_partida_registral,
					p.contacto_nombre,
					p.contacto_telefono,
					p.contacto_email
				FROM
					cont_persona p
					INNER JOIN cont_tipo_persona tp ON p.tipo_persona_id = tp.id
					INNER JOIN cont_tipo_docu_identidad td ON p.tipo_docu_identidad_id = td.id
				WHERE
					p.id IN ('" . $row["valor_original"] . "' , '" . $row["valor_int"] . "')
				";

				$valores_originales = [];
				$valores_nuevos = [];
				$list_query = $mysqli->query($query);
				while ($li = $list_query->fetch_assoc()) {
					if ($li["id"] == $row["valor_original"]) {
						$valores_originales[] = $li;
					} else if ($li["id"] == $row["valor_int"]) {
						$valores_nuevos[] = $li;
					}
				}

				$html .= '<br>';
				$html .= '<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">';
				$html .= '<thead>';

				$html .= '<tr>';
				$html .= '<th colspan="4" style="text-align: center; vertical-align: middle;">Propietario</th>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<th>Campo</th>';
				$html .= '<th>Valor Actual</th>';
				$html .= '<th>Nuevo Valor</th>';
				$html .= '<th></th>';
				$html .= '</tr>';

				$html .= '</thead>';
				$html .= '<tbody>';

				$html .= '<tr>';
				$html .= '<td>Tipo de documento de persona</td>';
				$html .= '<td>' . $valores_originales[0]["tipo_persona"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["tipo_persona"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Nombre</td>';
				$html .= '<td>' . $valores_originales[0]["nombre"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["nombre"] . '</td>';
				$html .= '<td rowspan="9">';
				$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_con_detalle_aden_prov_eliminar_detalle_adenda(' . $row["id"] . ')"><i class="fa fa-trash"></i></a></td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Tipo de documento de identidad</td>';
				$html .= '<td>' . $valores_originales[0]["tipo_docu_identidad"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["tipo_docu_identidad"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Número de documento de identidad</td>';
				$html .= '<td>' . $valores_originales[0]["num_docu"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["num_docu"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Dirección</td>';
				$html .= '<td>' . $valores_originales[0]["direccion"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["direccion"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Representante legal</td>';
				$html .= '<td>' . $valores_originales[0]["representante_legal"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["representante_legal"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>N° de Partida Registral de la empresa</td>';
				$html .= '<td>' . $valores_originales[0]["num_partida_registral"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["num_partida_registral"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Contacto - Nombre</td>';
				$html .= '<td>' . $valores_originales[0]["contacto_nombre"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["contacto_nombre"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Contacto - Teléfono</td>';
				$html .= '<td>' . $valores_originales[0]["contacto_telefono"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["contacto_telefono"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Contacto - Email</td>';
				$html .= '<td>' . $valores_originales[0]["contacto_email"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["contacto_email"] . '</td>';
				$html .= '</tr>';

				$html .= '</tbody>';
				$html .= '</table>';
			}

			if ($row["nombre_menu_usuario"] == 'Beneficiario') {
				$query = "
				SELECT 
					b.id,
					tp.nombre AS tipo_persona,
					td.nombre AS tipo_docu_identidad,
					b.num_docu,
					b.nombre,
					f.nombre AS forma_pago,
					ba.nombre AS banco,
					b.num_cuenta_bancaria,
					b.num_cuenta_cci,
					b.tipo_monto_id,
					tm.nombre AS tipo_monto,
					b.monto
				FROM
					cont_beneficiarios b
					LEFT JOIN cont_tipo_persona tp ON b.tipo_persona_id = tp.id
					LEFT JOIN cont_tipo_docu_identidad td ON b.tipo_docu_identidad_id = td.id
					INNER JOIN cont_forma_pago f ON b.forma_pago_id = f.id
					LEFT JOIN tbl_bancos ba ON b.banco_id = ba.id
					INNER JOIN cont_tipo_monto_a_depositar tm ON b.tipo_monto_id = tm.id
				WHERE
					b.id IN ('" . $row["valor_original"] . "' , '" . $row["valor_int"] . "')
				";

				$valores_originales = [];
				$valores_nuevos = [];
				$list_query = $mysqli->query($query);
				while ($li = $list_query->fetch_assoc()) {
					if ($li["id"] == $row["valor_original"]) {
						$valores_originales[] = $li;
					} else if ($li["id"] == $row["valor_int"]) {
						$valores_nuevos[] = $li;
					}
				}

				$html .= '<br>';
				$html .= '<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">';
				$html .= '<thead>';

				$html .= '<tr>';
				$html .= '<th colspan="4" style="text-align: center; vertical-align: middle;">Beneficiario</th>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<th>Campo</th>';
				$html .= '<th>Valor Actual</th>';
				$html .= '<th>Nuevo Valor</th>';
				$html .= '<th></th>';
				$html .= '</tr>';

				$html .= '</thead>';
				$html .= '<tbody>';

				$html .= '<tr>';
				$html .= '<td>Tipo de documento de persona</td>';
				$html .= '<td>' . $valores_originales[0]["tipo_persona"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["tipo_persona"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Nombre</td>';
				$html .= '<td>' . $valores_originales[0]["nombre"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["nombre"] . '</td>';
				$html .= '<td rowspan="9">';
				$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_con_detalle_aden_prov_eliminar_detalle_adenda(' . $row["id"] . ')"><i class="fa fa-trash"></i></a></td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Tipo de documento de identidad</td>';
				$html .= '<td>' . $valores_originales[0]["tipo_docu_identidad"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["tipo_docu_identidad"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Número de documento de identidad</td>';
				$html .= '<td>' . $valores_originales[0]["num_docu"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["num_docu"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Tipo de forma de pago</td>';
				$html .= '<td>' . $valores_originales[0]["forma_pago"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["forma_pago"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Nombre del Banco</td>';
				$html .= '<td>' . $valores_originales[0]["banco"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["banco"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>N° de la cuenta bancaria</td>';
				$html .= '<td>' . $valores_originales[0]["num_cuenta_bancaria"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["num_cuenta_bancaria"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>N° de CCI bancario</td>';
				$html .= '<td>' . $valores_originales[0]["num_cuenta_cci"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["num_cuenta_cci"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Tipo de monto a depositar</td>';
				$html .= '<td>' . $valores_originales[0]["tipo_monto"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["tipo_monto"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Monto</td>';
				$html .= '<td>' . $valores_originales[0]["monto"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["monto"] . '</td>';
				$html .= '</tr>';

				$html .= '</tbody>';
				$html .= '</table>';
			}

			if ($row["nombre_menu_usuario"] == 'Nuevo Incremento') {
				$query = "
				SELECT 
					i.id, 
					i.valor, 
					i.tipo_valor_id,
					tp.nombre AS tipo_valor, 
					i.tipo_continuidad_id, 
					tc.nombre AS tipo_continuidad, 
					i.a_partir_del_año
				FROM 
					cont_incrementos i
					INNER JOIN cont_tipo_pago_incrementos tp ON i.tipo_valor_id = tp.id
					INNER JOIN cont_tipo_continuidad_pago tc ON i.tipo_continuidad_id = tc.id
				WHERE 
					i.id = " . $row["valor_int"];


				$list_query = $mysqli->query($query);
				while ($li = $list_query->fetch_assoc()) {
					$valor_nuevo = $li;
				}

				$html .= '<br>';
				$html .= '<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">';
				$html .= '<thead>';

				$html .= '<tr>';
				$html .= '<th colspan="4" style="text-align: center; vertical-align: middle;">Nuevo Incremento</th>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<th>Campo</th>';
				$html .= '<th>Valor Actual</th>';
				$html .= '<th>Nuevo Valor</th>';
				$html .= '<th></th>';
				$html .= '</tr>';

				$html .= '</thead>';
				$html .= '<tbody>';

				$html .= '<tr>';
				$html .= '<td>Valor</td>';
				$html .= '<td></td>';
				$html .= '<td>' . $valor_nuevo["valor"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Tipo Valor</td>';
				$html .= '<td></td>';
				$html .= '<td>' . $valor_nuevo["tipo_valor"] . '</td>';
				$html .= '<td rowspan="9">';
				$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_con_detalle_aden_prov_eliminar_detalle_adenda(' . $row["id"] . ')"><i class="fa fa-trash"></i></a></td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Continuidad</td>';
				$html .= '<td></td>';
				$html .= '<td>' . $valor_nuevo["tipo_continuidad"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Apartir del</td>';
				$html .= '<td></td>';
				$html .= '<td>' . $valor_nuevo["a_partir_del_año"] . '</td>';
				$html .= '</tr>';

				$html .= '</tbody>';
				$html .= '</table>';
			}
		}
	}


	
	// CAMBIO DE PROPIETARIOS Y BENEFICIARIOS Y INCREMENTOS
	$query_otros = "SELECT id, nombre_menu_usuario, valor_original, valor_int, valor_id_tabla
	FROM cont_adendas_detalle 
	WHERE tipo_valor = 'registro' ";
	$query_otros .= " AND id IN(" . $ids . ")";
	
	$list_query_otros = $mysqli->query($query_otros);
	$row_count_otros = $list_query_otros->num_rows;

	if ($row_count_otros > 0) {
		while ($row = $list_query_otros->fetch_assoc()) {
			if ($row["nombre_menu_usuario"] == 'Representante Legal') {
				$query = "
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
				$list_query = $mysqli->query($query);
				while ($li = $list_query->fetch_assoc()) {
					if ($li["id"] == $row["valor_int"]) {
						$valores_nuevos[] = $li;
					}
				}

				$html .= '<br>';
				$html .= '<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">';
				$html .= '<thead>';

				$html .= '<tr>';
				$html .= '<th colspan="4" style="text-align: center; vertical-align: middle;">Nuevo Representante Legal</th>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<th>Campo</th>';
				$html .= '<th>Nuevo Valor</th>';
				$html .= '<th></th>';
				$html .= '</tr>';

				$html .= '</thead>';
				$html .= '<tbody>';

				$html .= '<tr>';
				$html .= '<td>DNI del representante legal</td>';
				$html .= '<td>' . $valores_nuevos[0]["dni_representante"] . '</td>';
				$html .= '<td rowspan="9">';
				$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_con_detalle_aden_prov_eliminar_detalle_adenda(' . $row["id"] . ')"><i class="fa fa-trash"></i></a></td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Nombre completo del representante legal</td>';
				$html .= '<td>' . $valores_nuevos[0]["nombre_representante"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Número de cuenta de detracción (Banco de la Nación)</td>';
				$html .= '<td>' . $valores_nuevos[0]["nro_cuenta_detraccion"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Banco</td>';
				$html .= '<td>' . $valores_nuevos[0]["banco_representante"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Nro Cuenta</td>';
				$html .= '<td>' . $valores_nuevos[0]["nro_cuenta"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Nro CCI</td>';
				$html .= '<td>' . $valores_nuevos[0]["nro_cci"] . '</td>';
				$html .= '</tr>';


				$html .= '</tbody>';
				$html .= '</table>';
			}

			if ($row["nombre_menu_usuario"] == 'Contraprestación') {
				$query = "
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
				$list_query = $mysqli->query($query);
				while ($li = $list_query->fetch_assoc()) {
					if ($li["id"] == $row["valor_int"]) {
						$li["subtotal"] = $li["tipo_moneda_simbolo"].' '.number_format($li["subtotal"], 2, '.', ',');
						$li["igv"] = $li["tipo_moneda_simbolo"].' '.number_format($li["igv"], 2, '.', ',');
						$li["monto"] = $li["tipo_moneda_simbolo"].' '.number_format($li["monto"], 2, '.', ',');
						$valores_nuevos[] = $li;
					}
				}

				$html .= '<br>';
				$html .= '<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">';
				$html .= '<thead>';

				$html .= '<tr>';
				$html .= '<th colspan="4" style="text-align: center; vertical-align: middle;">Nueva Contraprestación</th>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<th>Campo</th>';
				$html .= '<th>Nuevo Valor</th>';
				$html .= '<th></th>';
				$html .= '</tr>';

				$html .= '</thead>';
				$html .= '<tbody>';

				$html .= '<tr>';
				$html .= '<td>Tipo de moneda</td>';
				$html .= '<td>' . $valores_nuevos[0]["tipo_moneda"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Subtotal</td>';
				$html .= '<td>' . $valores_nuevos[0]["subtotal"] . '</td>';
				$html .= '<td rowspan="9">';
				$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_con_detalle_aden_prov_eliminar_detalle_adenda(' . $row["id"] . ')"><i class="fa fa-trash"></i></a></td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>IGV</td>';
				$html .= '<td>' . $valores_nuevos[0]["igv"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Monto Bruto</td>';
				$html .= '<td>' . $valores_nuevos[0]["monto"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Tipo de comprobante a emitir</td>';
				$html .= '<td>' . $valores_nuevos[0]["tipo_comprobante"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Plazo de Pago</td>';
				$html .= '<td>' . $valores_nuevos[0]["plazo_pago"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Forma de pago</td>';
				$html .= '<td>' . $valores_nuevos[0]["forma_pago_detallado"] . '</td>';
				$html .= '</tr>';


				$html .= '</tbody>';
				$html .= '</table>';
			}

		}
	}

	$html .= '<br>';

	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}
	
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $html;

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_adenda_detalle_nuevos_registros") {

	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	$insert_id = '';
	$result = array();

	if ($usuario_id == null) {
		$result["http_code"] = 404;
		$result["status"] = "Su sesion ha caducado, Ingrese de nuevo al sistema.";
		$result["result"] = 0;
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}

	if($_POST['tabla'] == "representante_legal"){
		$contrato_id = $_POST['contrato_id'];
		$dniRepresentante = $_POST['dniRepresentante'];
		$nombreRepresentante = $_POST['nombreRepresentante'];
		$banco = $_POST['banco'];
		$nro_cuenta = $_POST['nro_cuenta'];
		$nro_cci = $_POST['nro_cci'];
		$nro_cuenta_detraccion = '';
		
		$path = "/var/www/html/files_bucket/contratos/solicitudes/contratos_internos/";
		// INICIO DE CARGAR ARCHIVOS DNI NUEVO REPRESENTANTE LEGAL
		$filename = $_FILES['modal_prov_ade_int_file_dni_nuevo_rl']['name'];
		$filenametem = $_FILES['modal_prov_ade_int_file_dni_nuevo_rl']['tmp_name'];
		$filesize = $_FILES['modal_prov_ade_int_file_dni_nuevo_rl']['size'];
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
							'0',
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
		$filename = $_FILES['modal_prov_ade_int_file_vigencia_nuevo_rl']['name'];
		$filenametem = $_FILES['modal_prov_ade_int_file_vigencia_nuevo_rl']['tmp_name'];
		$filesize = $_FILES['modal_prov_ade_int_file_vigencia_nuevo_rl']['size'];
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
							'0',
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
		) VALUES (
			0,
			'". $dniRepresentante . "', '" 
			. $nombreRepresentante . "', '" 
			. $nro_cuenta_detraccion . "', " 
			. $banco . ", '" 
			. $nro_cuenta . "', '" 
			. $nro_cci . "', " 
			. $img_id_insert_vigencia . ", " 
			. $img_id_insert_dni . ", " 
			. $usuario_id . ", now())";

		$mysqli->query($query_insert_repr);
		$insert_id = mysqli_insert_id($mysqli);
		$error = '';
		if($mysqli->error){
			$error .= $error=$mysqli->error;
			$result["http_code"] = 404;
			$result["status"] = "A ocurrido un error al registrar el propietario.";
			$result["result"] = 0;
			$result["error"] = $error;
			echo json_encode($result);
			exit();
		}


		$usuario_id = $login?$login['id']:null;
		$created_at = date("Y-m-d H:i:s");
		$valor_original = "";
		$tipo_valor = 'registro';

		$id_del_registro = 0;
		
		$valor_varchar = "NULL";
		$valor_int = $insert_id;
		$valor_date = "NULL";
		$valor_decimal = "NULL";
		$valor_select_option = "NULL";
		$valor_id_tabla = "NULL";

		$query_insert = " INSERT INTO cont_adendas_detalle
		(
		nombre_tabla,
		valor_original,
		nombre_campo,
		nombre_menu_usuario,
		nombre_campo_usuario,
		tipo_valor,
		valor_varchar,
		valor_int,
		valor_date,
		valor_decimal,
		valor_select_option,
		valor_id_tabla,
		id_del_registro_a_modificar,
		user_created_id,
		created_at)
		VALUES
		(
		'cont_representantes_legales',
		'" . $valor_original . "',
		'contrato_id',
		'Representante Legal',
		'Nueva Representante Legal',
		'" . $tipo_valor . "',
		'" . $valor_varchar . "',
		" . $valor_int . ",
		" . $valor_date . ",
		" . $valor_decimal . ",
		'" . $valor_select_option . "',
		" . $valor_id_tabla . ",
		" . $id_del_registro . ",
		" . $usuario_id . ",
		'" . $created_at . "')";

		$mysqli->query($query_insert);
		$insert_id = mysqli_insert_id($mysqli);
		$error = '';
		if($mysqli->error){
			$error=$mysqli->error. " | ".$query_insert;
		}

		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestión.";
		$result["result"] = $insert_id;
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}

	if($_POST['tabla'] == "contraprestacion"){
		

		$usuario_id = $login?$login['id']:null;
		$created_at = date("Y-m-d H:i:s");

		$moneda_id = $_POST["moneda_id"];
		$forma_pago = 0;
		$forma_pago_detallado = "'" . $_POST["forma_pago_detallado"] . "'";
		$tipo_comprobante = $_POST["tipo_comprobante"];
		$plazo_pago = "'" . $_POST["plazo_pago"] . "'";

		if (empty($_POST["subtotal"])) {
			$subtotal = 0;
		} else {
			$subtotal = str_replace(",","",$_POST["subtotal"]);
		}

		if (empty($_POST["igv"])) {
			$igv = 0;
		} else {
			$igv = str_replace(",","",$_POST["igv"]);
		}

		$monto = $subtotal + $igv;

		$query_insert = "INSERT INTO cont_contraprestacion
		(
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
		" . $moneda_id . ",
		" . $forma_pago . ",
		" . $forma_pago_detallado . ",
		" . $tipo_comprobante . ",
		" . $plazo_pago . ",
		" . $subtotal . ",
		" . $igv . ",
		" . $monto . ",
		" . $usuario_id . ",
		'" . $created_at . "')";

		$mysqli->query($query_insert);
		$insert_id = mysqli_insert_id($mysqli);
		$error = '';
		if($mysqli->error){
			$error .= $error=$mysqli->error;
			$result["http_code"] = 404;
			$result["status"] = "A ocurrido un error al registrar la contraprestación.";
			$result["result"] = 0;
			$result["error"] = $error;
			echo json_encode($result);
			exit();
		}


		$usuario_id = $login?$login['id']:null;
		$created_at = date("Y-m-d H:i:s");
		$valor_original = "";
		$tipo_valor = 'registro';

		$id_del_registro = 0;
		
		$valor_varchar = "NULL";
		$valor_int = $insert_id;
		$valor_date = "NULL";
		$valor_decimal = "NULL";
		$valor_select_option = "NULL";
		$valor_id_tabla = "NULL";

		$query_insert = " INSERT INTO cont_adendas_detalle
		(
		nombre_tabla,
		valor_original,
		nombre_campo,
		nombre_menu_usuario,
		nombre_campo_usuario,
		tipo_valor,
		valor_varchar,
		valor_int,
		valor_date,
		valor_decimal,
		valor_select_option,
		valor_id_tabla,
		id_del_registro_a_modificar,
		user_created_id,
		created_at)
		VALUES
		(
		'cont_contraprestacion',
		'" . $valor_original . "',
		'contrato_id',
		'Contraprestación',
		'Nueva Contraprestación',
		'" . $tipo_valor . "',
		'" . $valor_varchar . "',
		" . $valor_int . ",
		" . $valor_date . ",
		" . $valor_decimal . ",
		'" . $valor_select_option . "',
		" . $valor_id_tabla . ",
		" . $id_del_registro . ",
		" . $usuario_id . ",
		'" . $created_at . "')";

		$mysqli->query($query_insert);
		$insert_id = mysqli_insert_id($mysqli);
		$error = '';
		if($mysqli->error){
			$error=$mysqli->error. " | ".$query_insert;
		}

		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestión.";
		$result["result"] = $insert_id;
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}
	
	

}

if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_adenda") {
	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	$tipo_contrato_id = $_POST["tipo_contrato_id"];
	$error = '';
	$adendas = json_decode($_POST["adendas"],true);
	$adenda_id = $_POST["adenda_id"];

	if ($usuario_id == null) {
		$result["status"] = 400;
		$result["message"] = 'Su sesion ha caducado, Ingrese de nuevo al sistema';
		$result["result"] = "error";
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}

	$query = $mysqli->query("SELECT a.procesado FROM cont_adendas a WHERE a.id = $adenda_id");
	$row = $query->fetch_assoc();
	$procesado = $row["procesado"];

	if ($procesado != 0) {
		$error .= $mysqli->error;
		$result["status"] = 400;
		$result["message"] = 'La adenda ya fue procesada';
		$result["result"] = "error";
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}
	

	if($mysqli->error){
		$error .= $mysqli->error;
		$result["status"] = 400;
		$result["message"] = $error;
		$result["result"] = "error";
		$result["error"] = $error;
		echo json_encode($result);
		exit();
		//echo $query_insert;
	}
	// FIN INSERTAR EN ADENDA

	$query_update = "
	UPDATE cont_adendas 
	SET 
		user_updated_id = " . $usuario_id . ",
		updated_at = '" . $created_at . "'
	WHERE id = ". $adenda_id;
	$mysqli->query($query_update);

	// INICIO ADENDA DETALLE
	
	for ($i=0; $i < count($adendas); $i++) { 
		if ($adendas[$i]['type'] == 'nuevo') {
			if ($adendas[$i]['state'] == 1) {
				$query_update = "
				UPDATE cont_adendas_detalle 
				SET 
					adenda_id = ". $adenda_id .",
					user_updated_id = " . $usuario_id . ",
					updated_at = '" . $created_at . "'
				WHERE id = ". $adendas[$i]['id'] ."
				";
				$mysqli->query($query_update);
			}
		}
		if ($adendas[$i]['type'] == 'registrado') {
			if ($adendas[$i]['state'] == 0) {
				$query_update = "
				UPDATE cont_adendas_detalle 
				SET 
					adenda_id = ". $adenda_id .",
					user_updated_id = " . $usuario_id . ",
					updated_at = '" . $created_at . "',
					status = '0'
				WHERE id = ". $adendas[$i]['id'] ."
				";
				$mysqli->query($query_update);
			}
		}

	}
	if($mysqli->error){
		$error .= $mysqli->error;
		$result["status"] = 400;
		$result["message"] = $error;
		$result["result"] = "error";
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}


	$path = "/var/www/html/files_bucket/contratos/adendas/proveedores/";
	if(!file_exists($path)){
		mkdir($path, 0777); //or die("Hubo un error al crear la carpeta");	
	}
	if (!empty($_FILES)) {
		foreach ($_FILES as $nombreCampo => $infoArchivo) {
			if ($infoArchivo['error'] === UPLOAD_ERR_OK) {
				$archivo_explode = explode('_', $nombreCampo);
				$tipo_anexo_id = $archivo_explode[0];

				$nombreArchivoSinExtension = pathinfo($infoArchivo['name'], PATHINFO_FILENAME);
				$filesize = $infoArchivo['size'];
				$fileExt = pathinfo($infoArchivo['name'], PATHINFO_EXTENSION);

				$nombre_archivo = $_POST["contrato_id"].'_ANEXO_ADENDAS_'.date('YmdHis').$fileExt;
				$dir=opendir($path);
				if(move_uploaded_file($infoArchivo['tmp_name'], $path.'/'.$nombre_archivo)){	
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
						'" . $_POST["contrato_id"] . "',
						'".$adenda_id."',
						'".$tipo_anexo_id."',
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
				}
				closedir($dir); 
			}
		}
	}
	send_email_solicitud_adenda_contrato_proveedor($adenda_id);
	$result["status"] = 200;
	$result["message"] = "Se ha modificado exitosamente la adenda de contrato de proveedor.";
	$result["result"] = "ok";
	$result["error"] = $error;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="obtener_archivos_adendas") {
	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	$adenda_id = $_POST["adenda_id"];

	if ($usuario_id == null) {
		$result["status"] = 400;
		$result["message"] = 'Su sesion ha caducado, Ingrese de nuevo al sistema';
		$result["result"] = "error";
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}

	$query = $mysqli->query("SELECT a.archivo_id, ta.nombre_tipo_archivo, a.nombre, a.extension, a.ruta
	FROM cont_archivos AS a 
	INNER JOIN cont_tipo_archivos AS ta ON ta.tipo_archivo_id = a.tipo_archivo_id
	WHERE 1 = 1 
	AND a.status = 1
	AND a.adenda_id = $adenda_id");

	$existe = false;
	$archivos = '
	<div class="col-md-12">
		<strong class="text-left">Archivos Anexos:</strong>
		<br>
	</div>

	<div class="w-100">
		<table class="table table-bordered table-striped no-mb">
			<thead>
				<tr>
					<th class="text-center" width="5%">#</th>
					<th class="text-center" width="85%">Nombre</th>
					<th class="text-center" width="10%">Acc.</th>
				</tr>
			</thead>
			<tbody>
			</tbody>';
			$index = 1;
			while ($row = $query->fetch_assoc()){
				$existe = true;
				$archivos .= '
				<tr>
					<td class="text-center">'.$index.'</td>
					<td class="text-left">'.$row['nombre_tipo_archivo'].'</td>
					<td class="text-center">
						<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_con_detalle_aden_prov_eliminar_archivo_anexo('.$row['archivo_id'].')"><i class="fa fa-trash"></i></a>
					</td>
				</tr>';
				$index++;
			}
	$archivos .= '
		</table>
	</div>
	<div class="col-md-12">
		<br>
	</div>
	
	';

	$result["status"] = 200;
	$result["message"] = "Datos obtenidos de gestión";
	$result["result"] = $existe ? $archivos:'';
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="eliminar_archivo_anexo") {
	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	$archivo_id = $_POST["archivo_id"];

	if ($usuario_id == null) {
		$result["status"] = 400;
		$result["message"] = 'Su sesion ha caducado, Ingrese de nuevo al sistema';
		$result["result"] = "error";
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}

	try {
		$query = $mysqli->query("UPDATE cont_archivos  SET status ='0',  user_updated_id = '".$usuario_id."', updated_at = '".$created_at."' WHERE  archivo_id = ".$archivo_id);
		$mysqli->query($query);
	
		$result["status"] = 200;
		$result["message"] = "Se ha eliminado el archivo existosamente";
		$result["result"] = '';
		echo json_encode($result);
		exit();
	} catch (\Exception $th) {
		$result["status"] = 404;
		$result["message"] = "A ocurrido un error, intentelo mas tarde.";
		$result["result"] = $th->getMessage();
		echo json_encode($result);
		exit();
	}
}

?>
