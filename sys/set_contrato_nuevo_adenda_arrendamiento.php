<?php
date_default_timezone_set("America/Lima");

$result = array();
include("db_connect.php");
include("sys_login.php");

include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';

require_once '/var/www/html/env.php';
include '/var/www/html/sys/envio_correos.php';
include '/var/www/html/sys/function_replace_invalid_caracters_contratos.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function send_email_solicitud_adenda_contrato_arrendamiento($adenda_id, $requiere_aprobacion_id)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];

	$sel_query = $mysqli->query("
	SELECT a.id, a.created_at, co.sigla, c.codigo_correlativo, c.contrato_id, tc.nombre,  c.nombre_tienda, c.cc_id,
	concat( IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS usuario_solicitante, ar.nombre as nombre_area,
	tpa.correo correo_aprobador, p2.correo correo_supervisor, p.correo correo_responsable, pjc.correo correo_comercial
	FROM cont_adendas AS a 
	INNER JOIN cont_contrato AS c ON c.contrato_id = a.contrato_id
	INNER JOIN tbl_razon_social r ON c.empresa_suscribe_id = r.id
	INNER JOIN cont_tipo_contrato AS tc ON tc.id = c.tipo_contrato_id
	INNER JOIN tbl_usuarios AS tu	ON tu.id = a.user_created_id
	INNER JOIN tbl_personal_apt AS tp ON tp.id = tu.personal_id

	INNER JOIN tbl_areas AS ar ON tp.area_id = ar.id
	LEFT JOIN cont_correlativo co ON c.tipo_contrato_id = co.tipo_contrato
	LEFT JOIN tbl_usuarios tu2 ON tu2.id = a.director_aprobacion_id
	LEFT JOIN tbl_personal_apt tpa ON tpa.id = tu2.personal_id
	LEFT JOIN tbl_usuarios u2 ON c.persona_responsable_id = u2.id
	LEFT JOIN tbl_personal_apt p2 ON u2.personal_id = p2.id
	LEFT JOIN tbl_usuarios u ON c.user_created_id = u.id
	LEFT JOIN tbl_personal_apt p ON u.personal_id = p.id
	LEFT JOIN tbl_usuarios ujc ON c.jefe_comercial_id = ujc.id
	LEFT JOIN tbl_personal_apt pjc ON ujc.personal_id = pjc.id
	WHERE a.id = $adenda_id
	");


	$body = "";
	$body .= '<html>';

	$usuario_creacion_correo = '';
	$nombre_tienda = '';

	$correos_adicionales = [];
	while ($sel = $sel_query->fetch_assoc()) {
		$contrato_id = $sel['contrato_id'];
		$sigla_correlativo = $sel['sigla'];
		$codigo_correlativo = $sel['codigo_correlativo'];

		$fecha_solicitud = $sel['created_at'];
		$nombre_tienda = $sel['nombre_tienda'];
		$usuario_solicitante = $sel['usuario_solicitante'];
		$correo_aprobador = $sel['correo_aprobador'];
		$correo_supervisor = $sel['correo_supervisor'];
		$correo_responsable = $sel['correo_responsable'];
		$correo_comercial = $sel['correo_comercial'];
		if (!empty($sel['correo'])) {
			array_push($correos_adicionales, $sel['correo']);
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
		$body .= '<td>' . $sel["nombre_area"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Centro de Costo:</b></td>';
		$body .= '<td>' . $sel["cc_id"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd; width: 200px;"><b>Nombre de Tienda:</b></td>';
		$body .= '<td>' . $sel["nombre_tienda"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Usuario solicitante:</b></td>';
		$body .= '<td>' . $sel["usuario_solicitante"] . '</td>';
		$body .= '</tr>';

		$body .= '<tr>';
		$body .= '<td style="background-color: #ffffdd"><b>Fecha Solicitud:</b></td>';
		$body .= '<td>' . $sel["created_at"] . '</td>';
		$body .= '</tr>';

		$body .= '</table>';
		$body .= '</div>';


		$query = $mysqli->query("
		SELECT a.id,
			a.adenda_id,
			a.nombre_tabla,
			a.valor_original,
			a.nombre_menu_usuario,
			a.nombre_campo_usuario,
			a.nombre_campo,
			a.tipo_valor,
			a.valor_varchar,
			a.valor_int,
			a.valor_date,
			a.valor_decimal,
			a.valor_select_option,
			a.status,
			cd.codigo
		FROM cont_adendas_detalle AS a
		LEFT JOIN cont_contrato_detalle AS cd ON cd.id = a.contrato_detalle_id
		WHERE a.adenda_id = " . $adenda_id . " 
		AND a.tipo_valor != 'id_tabla' AND a.tipo_valor != 'registro' AND a.tipo_valor != 'eliminar'
		AND a.status = 1");
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

			while ($row = $query->fetch_assoc()) {

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
				$codigo = !empty($row["codigo"]) ? '(#' . $row["codigo"] . ')' : '';

				$body .= '<tr>';
				$body .= '<td>' . $numero_adenda_detalle . '</td>';
				$body .= '<td>' . $nombre_menu_usuario . ' ' . $codigo . '</td>';
				$body .= '<td>' . $nombre_campo_usuario . '</td>';
				$body .= '<td>' . $valor_original . '</td>';
				$body .= '<td>' . $nuevo_valor . '</td>';
				$body .= '</tr>';
			}
			$body .= '</table>';
			$body .= '</div>';
		}

		$query_otros = "SELECT a.id, a.nombre_menu_usuario, a.valor_original,  a.valor_int, a.valor_id_tabla, cd.codigo
		FROM cont_adendas_detalle AS a
		LEFT JOIN cont_contrato_detalle AS cd ON cd.id = a.contrato_detalle_id
		WHERE a.tipo_valor = 'id_tabla'
			AND a.adenda_id = " . $adenda_id . "
			AND a.status = 1";

		$list_query_otros = $mysqli->query($query_otros);
		$row_count_otros = $list_query_otros->num_rows;

		if ($row_count_otros > 0) {
			$body .= '<div>';
			$body .= '<br>';
			$body .= '</div>';
			while ($row = $list_query_otros->fetch_assoc()) {
				$codigo_contrato = !empty($row['codigo']) ? '- Contrato #' . $row['codigo'] : '';
				$valores_originales = [];
				$valores_nuevos = [];

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


					$body .= '<div>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

					$body .= '<thead>';

					$body .= '<tr>';
					$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
					$body .= '<b>Cambio de Propietario ' . $codigo_contrato . '</b>';
					$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td align="center" style="background-color: #ffffdd;"><b>Campo:</b></td>';
					$body .= '<td align="center" style="background-color: #ffffdd;"><b>Valor Original:</b></td>';
					$body .= '<td align="center" style="background-color: #ffffdd;"><b>Nuevo Valor:</b></td>';
					$body .= '</tr>';

					$body .= '</thead>';

					$body .= '<tr>';
					$body .= '<td >Tipo de documento de persona</td>';
					$body .= '<td >' . $valores_originales[0]["tipo_persona"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["tipo_persona"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Nombre</td>';
					$body .= '<td >' . $valores_originales[0]["nombre"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["nombre"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Tipo de documento de identidad</td>';
					$body .= '<td >' . $valores_originales[0]["num_docu"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["num_docu"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Direccion</td>';
					$body .= '<td >' . $valores_originales[0]["direccion"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["direccion"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Representante legal</td>';
					$body .= '<td >' . $valores_originales[0]["representante_legal"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["representante_legal"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >N° de Partida Registral de la empresa</td>';
					$body .= '<td >' . $valores_originales[0]["num_partida_registral"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["num_partida_registral"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Contacto - Nombre</td>';
					$body .= '<td >' . $valores_originales[0]["contacto_nombre"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["contacto_nombre"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Contacto - Teléfono</td>';
					$body .= '<td >' . $valores_originales[0]["contacto_telefono"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["contacto_telefono"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Contacto - Email</td>';
					$body .= '<td >' . $valores_originales[0]["contacto_email"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["contacto_email"] . '</td>';
					$body .= '</tr>';

					$body .= '</table>';
					$body .= '</div>';
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

					$body .= '<div>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

					$body .= '<thead>';

					$body .= '<tr>';
					$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
					$body .= '<b>Cambio de Beneficiario ' . $codigo_contrato . '</b>';
					$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td align="center" style="background-color: #ffffdd;"><b>Campo:</b></td>';
					$body .= '<td align="center" style="background-color: #ffffdd;"><b>Valor Original:</b></td>';
					$body .= '<td align="center" style="background-color: #ffffdd;"><b>Nuevo Valor:</b></td>';
					$body .= '</tr>';

					$body .= '</thead>';

					$body .= '<tr>';
					$body .= '<td >Tipo de documento de persona</td>';
					$body .= '<td >' . $valores_originales[0]["tipo_persona"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["tipo_persona"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Nombre</td>';
					$body .= '<td >' . $valores_originales[0]["nombre"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["nombre"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Tipo de documento de identidad</td>';
					$body .= '<td >' . $valores_originales[0]["tipo_docu_identidad"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["tipo_docu_identidad"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Número de documento de identidad</td>';
					$body .= '<td >' . $valores_originales[0]["num_docu"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["num_docu"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Tipo de forma de pago</td>';
					$body .= '<td >' . $valores_originales[0]["forma_pago"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["forma_pago"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Nombre del Banco</td>';
					$body .= '<td >' . $valores_originales[0]["banco"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["banco"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >N° de la cuenta bancaria</td>';
					$body .= '<td >' . $valores_originales[0]["num_cuenta_bancaria"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["num_cuenta_bancaria"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >N° de CCI bancario</td>';
					$body .= '<td >' . $valores_originales[0]["num_cuenta_cci"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["num_cuenta_cci"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Tipo de monto a depositar</td>';
					$body .= '<td >' . $valores_originales[0]["tipo_monto"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["tipo_monto"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Monto</td>';
					$body .= '<td >' . $valores_originales[0]["monto"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["monto"] . '</td>';
					$body .= '</tr>';

					$body .= '</table>';
					$body .= '</div>';
				}

				if ($row["nombre_menu_usuario"] == 'Incremento') {
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

					$body .= '<div>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

					$body .= '<thead>';

					$body .= '<tr>';
					$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
					$body .= '<b>Cambio de Incremento ' . $codigo_contrato . '</b>';
					$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td align="center" style="background-color: #ffffdd;"><b>Campo:</b></td>';
					$body .= '<td align="center" style="background-color: #ffffdd;"><b>Valor Original:</b></td>';
					$body .= '<td align="center" style="background-color: #ffffdd;"><b>Nuevo Valor:</b></td>';
					$body .= '</tr>';

					$body .= '</thead>';

					$body .= '<tr>';
					$body .= '<td >Tipo de documento de persona</td>';
					$body .= '<td >' . $valores_originales[0]["tipo_persona"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["tipo_persona"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Nombre</td>';
					$body .= '<td >' . $valores_originales[0]["nombre"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["nombre"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Tipo de documento de identidad</td>';
					$body .= '<td >' . $valores_originales[0]["tipo_docu_identidad"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["tipo_docu_identidad"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Número de documento de identidad</td>';
					$body .= '<td >' . $valores_originales[0]["num_docu"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["num_docu"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Tipo de forma de pago</td>';
					$body .= '<td >' . $valores_originales[0]["forma_pago"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["forma_pago"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Nombre del Banco</td>';
					$body .= '<td >' . $valores_originales[0]["banco"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["banco"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >N° de la cuenta bancaria</td>';
					$body .= '<td >' . $valores_originales[0]["num_cuenta_bancaria"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["num_cuenta_bancaria"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >N° de CCI bancario</td>';
					$body .= '<td >' . $valores_originales[0]["num_cuenta_cci"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["num_cuenta_cci"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Tipo de monto a depositar</td>';
					$body .= '<td >' . $valores_originales[0]["tipo_monto"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["tipo_monto"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td >Monto</td>';
					$body .= '<td >' . $valores_originales[0]["monto"] . '</td>';
					$body .= '<td >' . $valores_nuevos[0]["monto"] . '</td>';
					$body .= '</tr>';

					$body .= '</table>';
					$body .= '</div>';
				}

				if ($row["nombre_menu_usuario"] == 'Responsable IR') {
					$query = "
						SELECT 
							r.id,
							r.contrato_id,
							r.tipo_documento_id,
							r.num_documento,
							r.nombres,
							r.estado_emisor,
							r.porcentaje,
							r.status,
							r.user_created_id,
							r.created_at,
							td.nombre AS tipo_documento
						FROM cont_responsable_ir as r
						LEFT JOIN cont_tipo_docu_identidad AS td ON td.id = r.tipo_documento_id
						WHERE r.id IN ('" . $row["valor_original"] . "' , '" . $row["valor_int"] . "')";
					$list_query = $mysqli->query($query);
					while ($li = $list_query->fetch_assoc()) {
						if ($li["id"] == $row["valor_original"]) {
							$valores_originales[] = $li;
						} else if ($li["id"] == $row["valor_int"]) {
							$valores_nuevos[] = $li;
						}
					}

					$body .= '<div>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';

					$body .= '<thead>';

					$body .= '<tr>';
					$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
					$body .= '<b>Cambiar Responsable IR ' . $codigo_contrato . '</b>';
					$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td align="center" style="background-color: #ffffdd;"><b>Campo:</b></td>';
					$body .= '<td align="center" style="background-color: #ffffdd;"><b>Valor Original:</b></td>';
					$body .= '<td align="center" style="background-color: #ffffdd;"><b>Nuevo Valor:</b></td>';
					$body .= '</tr>';

					$body .= '</thead>';

					$body .= '<tr>';
					$body .= '<td>Tipo de Documento de Identidad</td>';
					$body .= '<td>' . $valores_originales[0]["tipo_documento"] . '</td>';
					$body .= '<td>' . $valores_nuevos[0]["tipo_documento"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Nro Documento</td>';
					$body .= '<td>' . $valores_originales[0]["num_documento"] . '</td>';
					$body .= '<td>' . $valores_nuevos[0]["num_documento"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Nombres</td>';
					$body .= '<td>' . $valores_originales[0]["nombres"] . '</td>';
					$body .= '<td>' . $valores_nuevos[0]["nombres"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Porcentaje</td>';
					$body .= '<td>' . $valores_originales[0]["porcentaje"] . '</td>';
					$body .= '<td>' . $valores_nuevos[0]["porcentaje"] . '</td>';
					$body .= '</tr>';

					$body .= '</tbody>';
					$body .= '</table>';
				}
			}
		}

		// NUEVOS PROPIETARIOS Y BENEFICIARIOS Y INCREMENTOS
		$query_otros = "SELECT a.id, a.nombre_menu_usuario, a.valor_original,  a.valor_int, a.valor_id_tabla, cd.codigo
		FROM cont_adendas_detalle AS a
		LEFT JOIN cont_contrato_detalle AS cd ON cd.id = a.contrato_detalle_id
		WHERE a.tipo_valor = 'registro'
		AND a.adenda_id = " . $adenda_id . "
		AND a.status = 1";

		$list_query_otros = $mysqli->query($query_otros);
		$row_count_otros = $list_query_otros->num_rows;

		if ($row_count_otros > 0) {
			while ($row = $list_query_otros->fetch_assoc()) {
				$codigo_contrato = !empty($row['codigo']) ? '- Contrato #' . $row['codigo'] : '';
				$valores_originales = [];
				$valores_nuevos = [];

				if ($row["nombre_menu_usuario"] == 'Inflación') {
					$query = "SELECT i.id, DATE_FORMAT(i.fecha, '%d-%m-%Y') as fecha, tp.nombre  AS tipo_periodicidad, i.numero, p.nombre as tipo_anio_mes, m.sigla AS moneda, i.porcentaje_anadido, i.tope_inflacion, i.minimo_inflacion
					FROM cont_inflaciones AS i
					INNER JOIN cont_tipo_periodicidad AS tp ON tp.id = i.tipo_periodicidad_id
					LEFT JOIN tbl_moneda AS m ON m.id = i.moneda_id
					LEFT JOIN cont_periodo as p ON p.id = i.tipo_anio_mes
					WHERE i.id IN ('" . $row["valor_int"] . "')
					";
					$list_query = $mysqli->query($query);
					while ($li = $list_query->fetch_assoc()) {
						if ($li["id"] == $row["valor_int"]) {
							$valores_nuevos[] = $li;
						}
					}

					$body .= '<br>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<thead>';

					$body .= '<tr>';
					$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
					$body .= '<b>Nueva Inflación ' . $codigo_contrato . '</b>';
					$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<th>Campo</th>';
					$body .= '<th>Nuevo Valor</th>';
					$body .= '</tr>';

					$body .= '</thead>';
					$body .= '<tbody>';

					$body .= '<tr>';
					$body .= '<td>Fecha de Aplicación</td>';
					$body .= '<td>' . $valores_nuevos[0]["fecha"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Periodicidad</td>';
					$body .= '<td>' . $valores_nuevos[0]['tipo_periodicidad'] . ' ' . $valores_nuevos[0]['numero'] . ' ' . $valores_nuevos[0]['tipo_anio_mes'] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Curva</td>';
					$body .= '<td>' . $valores_nuevos[0]["moneda"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Porcentaje Añadido</td>';
					$body .= '<td>' . $valores_nuevos[0]["porcentaje_anadido"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Tope de Inflación</td>';
					$body .= '<td>' . $valores_nuevos[0]["tope_inflacion"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Minimo de Inflación</td>';
					$body .= '<td>' . $valores_nuevos[0]["minimo_inflacion"] . '</td>';
					$body .= '</tr>';


					$body .= '</tbody>';
					$body .= '</table>';
				}

				if ($row["nombre_menu_usuario"] == 'Cuota Extraordinaria') {
					$query = "SELECT c.id, m.nombre as mes, c.multiplicador, c.fecha
					FROM cont_cuotas_extraordinarias AS c
					INNER JOIN tbl_meses AS m ON m.id = c.mes
					WHERE c.id IN ('" . $row["valor_int"] . "')
					";

					$valores_originales = [];
					$valores_nuevos = [];
					$list_query = $mysqli->query($query);
					while ($li = $list_query->fetch_assoc()) {
						if ($li["id"] == $row["valor_int"]) {
							$valores_nuevos[] = $li;
						}
					}

					$body .= '<br>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<thead>';

					$body .= '<tr>';
					$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
					$body .= '<b>Nueva Cuota Extraordinaria ' . $codigo_contrato . '</b>';
					$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<th>Campo</th>';
					$body .= '<th>Nuevo Valor</th>';
					$body .= '</tr>';

					$body .= '</thead>';
					$body .= '<tbody>';

					$body .= '<tr>';
					$body .= '<td>Fecha de Aplicación</td>';
					$body .= '<td>' . $valores_nuevos[0]["fecha"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Mes</td>';
					$body .= '<td>' . $valores_nuevos[0]["mes"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Multiplicador</td>';
					$body .= '<td>' . $valores_nuevos[0]["multiplicador"] . '</td>';
					$body .= '</tr>';

					$body .= '</tbody>';
					$body .= '</table>';
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

					$body .= '<br>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<thead>';

					$body .= '<tr>';
					$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
					$body .= '<b>Nuevo Propietario</b>';
					$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<th>Campo</th>';
					$body .= '<th>Nuevo Valor</th>';
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
						$body .= '<td>' . $valores_nuevos[0]["tipo_docu_identidad"] . '</td>';
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

				if ($row["nombre_menu_usuario"] == 'Beneficiario') {
					$query = "
							SELECT b.id,
							tp.nombre AS tipo_persona,
							td.nombre AS tipo_docu_identidad,
							b.num_docu,
							b.nombre,
							f.nombre AS forma_pago,
							ba.nombre AS banco,
							b.num_cuenta_bancaria,
							b.num_cuenta_cci,
							b.tipo_monto_id,
							b.monto
						FROM cont_beneficiarios b
						LEFT JOIN cont_tipo_persona tp ON b.tipo_persona_id = tp.id
						LEFT JOIN cont_tipo_docu_identidad td ON b.tipo_docu_identidad_id = td.id
						INNER JOIN cont_forma_pago f ON b.forma_pago_id = f.id
						LEFT JOIN tbl_bancos ba ON b.banco_id = ba.id 
						WHERE b.id = " . $row["valor_int"];

					$valores_originales = [];
					$valores_nuevos = [];
					$list_query = $mysqli->query($query);
					while ($li = $list_query->fetch_assoc()) {
						if ($li["id"] == $row["valor_int"]) {
							$valores_nuevos[] = $li;
						}
					}

					$beneficiario_id = $valores_nuevos[0]["id"];
					$ben_tipo_persona = $valores_nuevos[0]["tipo_persona"];
					$ben_tipo_docu_identidad = $valores_nuevos[0]["tipo_docu_identidad"];
					$ben_num_docu = $valores_nuevos[0]["num_docu"];
					$ben_nombre = $valores_nuevos[0]["nombre"];
					$ben_direccion = '';
					$ben_forma_pago = $valores_nuevos[0]["forma_pago"];
					$ben_banco = $valores_nuevos[0]["banco"];
					$ben_num_cuenta_bancaria = $valores_nuevos[0]["num_cuenta_bancaria"];
					$ben_num_cuenta_cci = $valores_nuevos[0]["num_cuenta_cci"];
					$ben_tipo_monto_id = $valores_nuevos[0]["tipo_monto_id"];


					$ben_monto_beneficiario = number_format($valores_nuevos[0]["monto"], 2, '.', ',');


					$body .= '<br>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<thead>';

					$body .= '<tr>';
					$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
					$body .= '<b>Nuevo Beneficario ' . $codigo_contrato . '</b>';
					$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<th>Campo</th>';
					$body .= '<th>Nuevo Valor</th>';
					$body .= '</tr>';

					$body .= '</thead>';
					$body .= '<tbody>';

					$body .= '<tr>';
					$body .= '<td>Tipo de Persona</td>';
					$body .= '<td>' . $ben_tipo_persona . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Nombre</td>';
					$body .= '<td>' . $ben_nombre . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Tipo de Documento de Identidad</td>';
					$body .= '<td>' . $ben_tipo_docu_identidad . '</td>';
					$body .= '</tr>';


					$body .= '<tr>';
					$body .= '<td>Número de Documento de Identidad</td>';
					$body .= '<td>' . $ben_num_docu . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Tipo de forma de pago</td>';
					$body .= '<td>' . $ben_forma_pago . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Nombre del Banco</td>';
					$body .= '<td>' . $ben_banco . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>N° de la cuenta bancaria</td>';
					$body .= '<td>' . $ben_num_cuenta_bancaria . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>N° de CCI bancario</td>';
					$body .= '<td>' . $ben_num_cuenta_cci . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Monto</td>';
					$body .= '<td>' . $ben_monto_beneficiario . '</td>';
					$body .= '</tr>';



					$body .= '</tbody>';
					$body .= '</table>';
				}

				if ($row["nombre_menu_usuario"] == 'Incremento') {
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
						$valores_nuevos[] = $li;
					}

					$body .= '<br>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<thead>';

					$body .= '<tr>';
					$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
					$body .= '<b>Nuevo Incremento ' . $codigo_contrato . '</b>';
					$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<th>Campo</th>';
					$body .= '<th>Valor Actual</th>';
					$body .= '<th>Nuevo Valor</th>';
					$body .= '</tr>';

					$body .= '</thead>';
					$body .= '<tbody>';

					$body .= '<tr>';
					$body .= '<td>Valor</td>';
					$body .= '<td></td>';
					$body .= '<td>' . $valores_nuevos[0]["valor"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Tipo Valor</td>';
					$body .= '<td></td>';
					$body .= '<td>' . $valores_nuevos[0]["tipo_valor"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Continuidad</td>';
					$body .= '<td></td>';
					$body .= '<td>' . $valores_nuevos[0]["tipo_continuidad"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Apartir del</td>';
					$body .= '<td></td>';
					$body .= '<td>' . $valores_nuevos[0]["a_partir_del_año"] . '</td>';
					$body .= '</tr>';

					$body .= '</tbody>';
					$body .= '</table>';
				}

				if ($row["nombre_menu_usuario"] == 'Responsable IR') {
					$query = "
					SELECT 
						r.id,
						r.contrato_id,
						r.tipo_documento_id,
						r.num_documento,
						r.nombres,
						r.estado_emisor,
						r.porcentaje,
						r.status,
						r.user_created_id,
						r.created_at,
						td.nombre AS tipo_documento
					FROM cont_responsable_ir as r
					LEFT JOIN cont_tipo_docu_identidad AS td ON td.id = r.tipo_documento_id
					WHERE r.id = " . $row["valor_int"];


					$list_query = $mysqli->query($query);
					while ($li = $list_query->fetch_assoc()) {
						$valores_nuevos[] = $li;
					}

					$body .= '<br>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<thead>';

					$body .= '<tr>';
					$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
					$body .= '<b>Nuevo Responsable IR ' . $codigo_contrato . '</b>';
					$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<th>Campo</th>';
					$body .= '<th>Nuevo Valor</th>';
					$body .= '</tr>';

					$body .= '</thead>';
					$body .= '<tbody>';

					$body .= '<tr>';
					$body .= '<td>Tipo de Documento de Identidad</td>';
					$body .= '<td>' . $valores_nuevos[0]["tipo_documento"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Nro Documento</td>';
					$body .= '<td>' . $valores_nuevos[0]["num_documento"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Nombres</td>';
					$body .= '<td>' . $valores_nuevos[0]["nombres"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Porcentaje</td>';
					$body .= '<td>' . $valores_nuevos[0]["porcentaje"] . '</td>';
					$body .= '</tr>';

					$body .= '</tbody>';
					$body .= '</table>';
				}

				if ($row["nombre_menu_usuario"] == 'Suministro') {
					$query = "SELECT s.id, s.tipo_servicio_id, sp.nombre AS tipo_servicio, s.nro_suministro, s.tipo_compromiso_pago_id, ps.nombre AS tipo_compromiso, s.monto_o_porcentaje
					FROM cont_inmueble_suministros AS s
					LEFT JOIN cont_tipo_pago_servicio AS ps ON ps.id = s.tipo_compromiso_pago_id
					LEFT JOIN cont_tipo_servicio_publico AS sp ON sp.id = s.tipo_servicio_id
					INNER JOIN cont_inmueble AS i ON i.id = s.inmueble_id
					INNER JOIN cont_contrato_detalle AS cd ON cd.id = i.contrato_detalle_id
					WHERE s.id = " . $row["valor_int"];


					$list_query = $mysqli->query($query);
					while ($li = $list_query->fetch_assoc()) {
						$valores_nuevos[] = $li;
					}

					$body .= '<br>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<thead>';

					$body .= '<tr>';
					$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
					$body .= '<b>Nuevo Suministro ' . $codigo_contrato . '</b>';
					$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<th>Campo</th>';
					$body .= '<th>Nuevo Valor</th>';
					$body .= '</tr>';

					$body .= '</thead>';
					$body .= '<tbody>';

					$body .= '<tr>';
					$body .= '<td>Tipo de Servicio</td>';
					$body .= '<td>' . $valores_nuevos[0]["tipo_servicio"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>N° de Suministro</td>';
					$body .= '<td>' . $valores_nuevos[0]["nro_suministro"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Compromiso de pago</td>';
					$body .= '<td>' . $valores_nuevos[0]["tipo_compromiso"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Monto/Porcentaje</td>';
					$body .= '<td>' . $valores_nuevos[0]["monto_o_porcentaje"] . '</td>';
					$body .= '</tr>';

					$body .= '</tbody>';
					$body .= '</table>';
				}
			}
		}


		// Eliminar PROPIETARIOS Y BENEFICIARIOS Y INCREMENTOS
		$query_otros = "SELECT a.id, a.nombre_menu_usuario, a.valor_original,  a.valor_int, a.valor_id_tabla, cd.codigo
		FROM cont_adendas_detalle AS a
		LEFT JOIN cont_contrato_detalle AS cd ON cd.id = a.contrato_detalle_id
		WHERE a.tipo_valor = 'eliminar'
			AND a.adenda_id = " . $adenda_id . "
			AND a.status = 1";

		$list_query_otros = $mysqli->query($query_otros);
		$row_count_otros = $list_query_otros->num_rows;

		if ($row_count_otros > 0) {
			while ($row = $list_query_otros->fetch_assoc()) {
				$codigo_contrato = !empty($row['codigo']) ? '- Contrato #' . $row['codigo'] : '';
				$valores_originales = [];
				$valores_nuevos = [];

				if ($row["nombre_menu_usuario"] == 'Inflación') {
					$query = "SELECT i.id, DATE_FORMAT(i.fecha, '%d-%m-%Y') as fecha, tp.nombre  AS tipo_periodicidad, i.numero, p.nombre as tipo_anio_mes, m.sigla AS moneda, i.porcentaje_anadido, i.tope_inflacion, i.minimo_inflacion
					FROM cont_inflaciones AS i
					INNER JOIN cont_tipo_periodicidad AS tp ON tp.id = i.tipo_periodicidad_id
					LEFT JOIN tbl_moneda AS m ON m.id = i.moneda_id
					LEFT JOIN cont_periodo as p ON p.id = i.tipo_anio_mes
					WHERE i.id IN ('" . $row["valor_int"] . "')
					";

					$valores_originales = [];
					$valores_nuevos = [];
					$list_query = $mysqli->query($query);
					while ($li = $list_query->fetch_assoc()) {
						if ($li["id"] == $row["valor_int"]) {
							$valores_nuevos[] = $li;
						}
					}

					$body .= '<br>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<thead>';

					$body .= '<tr>';
					$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
					$body .= '<b>Eliminar Inflación  ' . $codigo_contrato . '</b>';
					$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<th>Campo</th>';
					$body .= '<th>Valor Actual</th>';
					$body .= '</tr>';

					$body .= '</thead>';
					$body .= '<tbody>';

					$body .= '<tr>';
					$body .= '<td>Fecha de Aplicación</td>';
					$body .= '<td>' . $valores_nuevos[0]["fecha"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Periodicidad</td>';
					$body .= '<td>' . $valores_nuevos[0]['tipo_periodicidad'] . ' ' . $valores_nuevos[0]['numero'] . ' ' . $valores_nuevos[0]['tipo_anio_mes'] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Curva</td>';
					$body .= '<td>' . $valores_nuevos[0]["moneda"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Porcentaje Añadido</td>';
					$body .= '<td>' . $valores_nuevos[0]["porcentaje_anadido"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Tope de Inflación</td>';
					$body .= '<td>' . $valores_nuevos[0]["tope_inflacion"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Minimo de Inflación</td>';
					$body .= '<td>' . $valores_nuevos[0]["minimo_inflacion"] . '</td>';
					$body .= '</tr>';


					$body .= '</tbody>';
					$body .= '</table>';
				}

				if ($row["nombre_menu_usuario"] == 'Cuota Extraordinaria') {
					$query = "SELECT c.id, m.nombre as mes, c.multiplicador, c.fecha
					FROM cont_cuotas_extraordinarias AS c
					INNER JOIN tbl_meses AS m ON m.id = c.mes
					WHERE c.id IN ('" . $row["valor_int"] . "')
					";

					$valores_originales = [];
					$valores_nuevos = [];
					$list_query = $mysqli->query($query);
					while ($li = $list_query->fetch_assoc()) {
						if ($li["id"] == $row["valor_int"]) {
							$valores_nuevos[] = $li;
						}
					}

					$body .= '<br>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<thead>';

					$body .= '<tr>';
					$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
					$body .= '<b>Eliminar Cuota Extraordinaria ' . $codigo_contrato . '</b>';
					$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<th>Campo</th>';
					$body .= '<th>Valor Actual</th>';
					$body .= '</tr>';

					$body .= '</thead>';
					$body .= '<tbody>';

					$body .= '<tr>';
					$body .= '<td>Fecha de Aplicación</td>';
					$body .= '<td>' . $valores_nuevos[0]["fecha"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Mes</td>';
					$body .= '<td>' . $valores_nuevos[0]["mes"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Multiplicador</td>';
					$body .= '<td>' . $valores_nuevos[0]["multiplicador"] . '</td>';
					$body .= '</tr>';

					$body .= '</tbody>';
					$body .= '</table>';
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

					$body .= '<br>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<thead>';

					$body .= '<tr>';
					$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
					$body .= '<b>Eliminar Propietario</b>';
					$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<th>Campo</th>';
					$body .= '<th>Valor</th>';
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
						$body .= '<td>' . $valores_nuevos[0]["tipo_docu_identidad"] . '</td>';
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

				if ($row["nombre_menu_usuario"] == 'Beneficiario') {
					$query = "
							SELECT b.id,
							tp.nombre AS tipo_persona,
							td.nombre AS tipo_docu_identidad,
							b.num_docu,
							b.nombre,
							f.nombre AS forma_pago,
							ba.nombre AS banco,
							b.num_cuenta_bancaria,
							b.num_cuenta_cci,
							b.tipo_monto_id,
							b.monto
						FROM cont_beneficiarios b
						LEFT JOIN cont_tipo_persona tp ON b.tipo_persona_id = tp.id
						LEFT JOIN cont_tipo_docu_identidad td ON b.tipo_docu_identidad_id = td.id
						INNER JOIN cont_forma_pago f ON b.forma_pago_id = f.id
						LEFT JOIN tbl_bancos ba ON b.banco_id = ba.id 
						WHERE b.id = " . $row["valor_int"];

					$valores_originales = [];
					$valores_nuevos = [];
					$list_query = $mysqli->query($query);
					while ($li = $list_query->fetch_assoc()) {
						if ($li["id"] == $row["valor_int"]) {
							$valores_nuevos[] = $li;
						}
					}

					$beneficiario_id = $valores_nuevos[0]["id"];
					$ben_tipo_persona = $valores_nuevos[0]["tipo_persona"];
					$ben_tipo_docu_identidad = $valores_nuevos[0]["tipo_docu_identidad"];
					$ben_num_docu = $valores_nuevos[0]["num_docu"];
					$ben_nombre = $valores_nuevos[0]["nombre"];
					$ben_direccion = '';
					$ben_forma_pago = $valores_nuevos[0]["forma_pago"];
					$ben_banco = $valores_nuevos[0]["banco"];
					$ben_num_cuenta_bancaria = $valores_nuevos[0]["num_cuenta_bancaria"];
					$ben_num_cuenta_cci = $valores_nuevos[0]["num_cuenta_cci"];
					$ben_tipo_monto_id = $valores_nuevos[0]["tipo_monto_id"];


					$ben_monto_beneficiario = number_format($valores_nuevos[0]["monto"], 2, '.', ',');


					$body .= '<br>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<thead>';

					$body .= '<tr>';
					$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
					$body .= '<b>Eliminar Beneficario ' . $codigo_contrato . '</b>';
					$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<th>Campo</th>';
					$body .= '<th>Valor Actual</th>';
					$body .= '</tr>';

					$body .= '</thead>';
					$body .= '<tbody>';

					$body .= '<tr>';
					$body .= '<td>Tipo de Persona</td>';
					$body .= '<td>' . $ben_tipo_persona . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Nombre</td>';
					$body .= '<td>' . $ben_nombre . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Tipo de Documento de Identidad</td>';
					$body .= '<td>' . $ben_tipo_docu_identidad . '</td>';
					$body .= '</tr>';


					$body .= '<tr>';
					$body .= '<td>Número de Documento de Identidad</td>';
					$body .= '<td>' . $ben_num_docu . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Tipo de forma de pago</td>';
					$body .= '<td>' . $ben_forma_pago . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Nombre del Banco</td>';
					$body .= '<td>' . $ben_banco . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>N° de la cuenta bancaria</td>';
					$body .= '<td>' . $ben_num_cuenta_bancaria . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>N° de CCI bancario</td>';
					$body .= '<td>' . $ben_num_cuenta_cci . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Monto</td>';
					$body .= '<td>' . $ben_monto_beneficiario . '</td>';
					$body .= '</tr>';



					$body .= '</tbody>';
					$body .= '</table>';
				}

				if ($row["nombre_menu_usuario"] == 'Responsable IR') {
					$query = "
					SELECT 
						r.id,
						r.contrato_id,
						r.tipo_documento_id,
						r.num_documento,
						r.nombres,
						r.estado_emisor,
						r.porcentaje,
						r.status,
						r.user_created_id,
						r.created_at,
						td.nombre AS tipo_documento
					FROM cont_responsable_ir as r
					LEFT JOIN cont_tipo_docu_identidad AS td ON td.id = r.tipo_documento_id
					WHERE r.id = " . $row["valor_int"];

					$valores_originales = [];
					$valores_nuevos = [];
					$list_query = $mysqli->query($query);
					while ($li = $list_query->fetch_assoc()) {
						if ($li["id"] == $row["valor_int"]) {
							$valores_nuevos[] = $li;
						}
					}

					$body .= '<br>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<thead>';

					$body .= '<tr>';
					$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
					$body .= '<b>Eliminar Responsable IR ' . $codigo_contrato . '</b>';
					$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<th>Campo</th>';
					$body .= '<th>Valor Actual</th>';
					$body .= '</tr>';

					$body .= '</thead>';
					$body .= '<tbody>';

					$body .= '<tr>';
					$body .= '<td>Tipo de Documento de Identidad</td>';
					$body .= '<td>' . $valores_nuevos[0]["tipo_documento"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Nro Documento</td>';
					$body .= '<td>' . $valores_nuevos[0]["num_documento"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Nombres</td>';
					$body .= '<td>' . $valores_nuevos[0]["nombres"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Porcentaje</td>';
					$body .= '<td>' . $valores_nuevos[0]["porcentaje"] . '</td>';
					$body .= '</tr>';

					$body .= '</tbody>';
					$body .= '</table>';
				}

				if ($row["nombre_menu_usuario"] == 'Suministro') {
					$query = "SELECT s.id, s.tipo_servicio_id, sp.nombre AS tipo_servicio, s.nro_suministro, s.tipo_compromiso_pago_id, ps.nombre AS tipo_compromiso, s.monto_o_porcentaje
					FROM cont_inmueble_suministros AS s
					LEFT JOIN cont_tipo_pago_servicio AS ps ON ps.id = s.tipo_compromiso_pago_id
					LEFT JOIN cont_tipo_servicio_publico AS sp ON sp.id = s.tipo_servicio_id
					INNER JOIN cont_inmueble AS i ON i.id = s.inmueble_id
					INNER JOIN cont_contrato_detalle AS cd ON cd.id = i.contrato_detalle_id
					WHERE s.id = " . $row["valor_int"];

					$valores_originales = [];
					$valores_nuevos = [];
					$list_query = $mysqli->query($query);
					while ($li = $list_query->fetch_assoc()) {
						if ($li["id"] == $row["valor_int"]) {
							$valores_nuevos[] = $li;
						}
					}

					$body .= '<br>';
					$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 700px;">';
					$body .= '<thead>';

					$body .= '<tr>';
					$body .= '<th colspan="4" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
					$body .= '<b>Eliminar Suministro ' . $codigo_contrato . '</b>';
					$body .= '</th>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<th>Campo</th>';
					$body .= '<th>Valor Actual</th>';
					$body .= '</tr>';

					$body .= '</thead>';
					$body .= '<tbody>';

					$body .= '<tr>';
					$body .= '<td>Tipo de Servicio</td>';
					$body .= '<td>' . $valores_nuevos[0]["tipo_servicio"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>N° de Suministro</td>';
					$body .= '<td>' . $valores_nuevos[0]["nro_suministro"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Compromiso de pago</td>';
					$body .= '<td>' . $valores_nuevos[0]["tipo_compromiso"] . '</td>';
					$body .= '</tr>';

					$body .= '<tr>';
					$body .= '<td>Monto/Porcentaje</td>';
					$body .= '<td>' . $valores_nuevos[0]["monto_o_porcentaje"] . '</td>';
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

	if ($requiere_aprobacion_id == 1) {
		$body .= '<div>';
		$body .= '<b>* Esta solicitud de adenda requiere su aprobación.</b><br><br>';
		$body .= '</div>';
	}

	$body .= '<div style="width: 700px; text-align: center; font-family: arial;">';
	$body .= '<a href="' . $host . '/?sec_id=contrato&sub_sec_id=detalle_solicitud&id=' . $contrato_id . '" target="_blank" style="background-color: #4CAF50; border: none; color: white; padding: 5px 20px; text-align: center; text-decoration: none; display: inline-block; font-size: 16px;">';
	$body .= '<b>Ver Solicitud</b>';
	$body .= '</a>';
	$body .= '</div>';

	$body .= '</html>';
	$body .= "";

	if ($requiere_aprobacion_id == 2) {
		//lista de correos
		$correos = new Correos(env('SEND_EMAIL'), env('EMAIL_TEST'));
		$lista_correos = $correos->send_email_solicitud_adenda_contrato_arrendamiento($correos_adicionales);
	} else {

		if (env('SEND_EMAIL') == 'produccion') {

			if (!empty($correo_aprobador)) {
				$lista_correos['cc'][] = $correo_aprobador;
			}
			if (!empty($correo_supervisor)) {
				$lista_correos['cc'][] = $correo_supervisor;
			}
			if (!empty($correo_responsable)) {
				$lista_correos['cc'][] = $correo_responsable;
			}
			if (!empty($correo_comercial)) {
				$lista_correos['cc'][] = $correo_comercial;
			}
			$lista_correos['cc'] = array_unique($lista_correos['cc']);
		} else {
			$lista_correos['cc'] = ['jeremi.nunez@testtest.apuestatotal.com'];
		}

		$lista_correos['bcc'] = [
			'jeremi.nunez@testtest.apuestatotal.com',
			'rossmery.garrido@testtest.kurax.dev',
			'jesus.cervantes@testtest.kurax.dev',
			'erika.polo@testtest.apuestatotal.com'
		];
	}

	$cc = $lista_correos['cc'];

	$titulo_email = "Gestion - Sistema Contratos - Nueva Solicitud de Adenda de Arrendamiento " . $nombre_tienda . ": Código - ";

	$bcc = $lista_correos['bcc'];

	$request = [
		"subject" => $titulo_email . $sigla_correlativo . $codigo_correlativo,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
	];

	$mail = new PHPMailer(true);

	try {
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;


		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

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

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();
		//return true;

	} catch (Exception $e) {
		$resultado = $mail->ErrorInfo;
		//echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		//return false;
		echo json_encode($resultado);
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_adenda_detalle") {

	$usuario_id = $login ? $login['id'] : null;
	$created_at = date("Y-m-d H:i:s");
	$valor_original = $_POST["valor_original"];
	$tipo_valor = $_POST["tipo_valor"];
	$contrato_detalle_id = 'NULL';
	if (isset($_POST["id_del_registro"]) && !empty($_POST["id_del_registro"])) {
		$id_del_registro = trim($_POST["id_del_registro"]);
		if ($_POST["nombre_tabla"] == "cont_contrato_detalle") {
			$contrato_detalle_id = $_POST["id_del_registro"];
		} else {
			//obtener el contrato detalle id
			if ($_POST["nombre_tabla"] == "cont_condicion_economica") { // llaves primarias de la tabla es diferente al campo ID
				$query_detalle_id = "SELECT contrato_detalle_id FROM " . $_POST["nombre_tabla"] . " WHERE condicion_economica_id = " . $id_del_registro;
			} else if ($_POST["nombre_tabla"] == "cont_inmueble_suministros") {
				$query_detalle_id = "SELECT i.contrato_detalle_id FROM cont_inmueble as i INNER JOIN cont_inmueble_suministros AS s ON i.id = s.inmueble_id WHERE s.id = " . $id_del_registro;
			} else { //llaves primarias de la tabla es igual al campo ID
				$query_detalle_id = "SELECT contrato_detalle_id FROM " . $_POST["nombre_tabla"] . " WHERE id = " . $id_del_registro;
			}

			$list_query = $mysqli->query($query_detalle_id);
			$row_count = $list_query->num_rows;
			if ($row_count > 0) {
				$row = $list_query->fetch_assoc();
				$contrato_detalle_id =  $row['contrato_detalle_id'];
			}
		}
	} else {
		$id_del_registro = 0;
	}

	if (isset($_POST['tipo_valor']) && $_POST['tipo_valor'] == "eliminar") {
		$contrato_detalle_id = isset($_POST['contrato_detalle_id']) ? $_POST['contrato_detalle_id'] : 'NULL';
	}

	$ubigeo_id_nuevo = isset($_POST["ubigeo_id_nuevo"]) ? $_POST["ubigeo_id_nuevo"] : '';
	$ubigeo_text_nuevo = isset($_POST["ubigeo_text_nuevo"]) ? $_POST["ubigeo_text_nuevo"] : '';



	if ($tipo_valor == 'varchar') {
		$valor_varchar = replace_invalid_caracters($_POST["valor_varchar"]);

		if ($_POST["nombre_campo"] == 'cc_id') {
			if (!empty(trim($_POST["valor_varchar"]))) {

				$query_centro_de_costo = "
					SELECT
						nombre
					FROM
						tbl_locales
					WHERE
						cc_id = $valor_varchar";
				$query = $mysqli->query($query_centro_de_costo);
				$row_count = $query->num_rows;

				if ($row_count > 0) {
					$row = $query->fetch_assoc();
					$nombre_local = $row["nombre"];

					$result["http_code"] = 400;
					$result["error"] = 'El local "' . $nombre_local . '" posee el centro de costos "' . trim($_POST["valor_varchar"]) . '"';
					exit(json_encode($result));
				}
			}
		}
		$valor_int = "NULL";
		$valor_date = "NULL";
		$valor_decimal = "NULL";
		$valor_select_option = "NULL";
		$valor_id_tabla = "NULL";
	} else if ($tipo_valor == 'textarea') {
		$tipo_valor = 'varchar';
		$valor_varchar = replace_invalid_caracters($_POST["valor_textarea"]);
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
		$valor_decimal = str_replace(",", "", $_POST["valor_decimal"]);
		$valor_select_option = "NULL";
		$valor_id_tabla = "NULL";
	} else if ($tipo_valor == 'select_option') {

		if ($_POST["nombre_campo"] == "ubigeo_id") {
			$valor_varchar = $ubigeo_id_nuevo;
			$valor_int = "NULL";
			$valor_date = "NULL";
			$valor_decimal = "NULL";
			$valor_select_option = $ubigeo_text_nuevo;
			$valor_id_tabla = "NULL";
		} else {
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
	} else if ($tipo_valor == 'eliminar') {
		$valor_varchar = "NULL";
		$valor_int = $_POST["valor_int"];
		$valor_date = "NULL";
		$valor_decimal = "NULL";
		$valor_select_option = "NULL";
		$valor_id_tabla = "NULL";
	}


	$query_insert = " INSERT INTO cont_adendas_detalle
	(
	contrato_detalle_id,
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
	" . $contrato_detalle_id . ",
	'" . $_POST["nombre_tabla"] . "',
	'" . replace_invalid_caracters($valor_original) . "',
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
	if ($mysqli->error) {
		$error = $mysqli->error . " | " . $query_insert;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $insert_id;
	$result["error"] = $error;

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "obtener_adendas_detalle") {

	$id_adendas = $_POST["id_adendas"];
	$html = '';

	$query = "SELECT a.id, a.nombre_menu_usuario, a.nombre_campo_usuario, a.valor_original, a.tipo_valor, a.valor_varchar, a.valor_int, a.valor_date, 
	a.valor_decimal, a.valor_select_option, cd.codigo
	FROM cont_adendas_detalle AS a
	LEFT JOIN cont_contrato_detalle AS cd ON cd.id = a.contrato_detalle_id
	WHERE a.tipo_valor != 'id_tabla' AND a.tipo_valor != 'registro' AND a.tipo_valor != 'eliminar' ";
	$data = json_decode($id_adendas);
	$contador = 0;
	$ids = '';
	foreach ($data as $value) {
		if ($contador > 0) {
			$ids .= ',';
		}
		$ids .= $value;
		$contador++;
	}
	$query .= " AND a.id IN(" . $ids . ")";

	$list_query = $mysqli->query($query);
	$row_count = $list_query->num_rows;

	if ($row_count > 0) {
		$html .= '<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th class="text-center">#</th>';
		$html .= '<th class="text-center">Menú</th>';
		$html .= '<th class="text-center">Campo</th>';
		$html .= '<th class="text-center">Valor Actual</th>';
		$html .= '<th class="text-center">Nuevo Valor</th>';
		$html .= '<th class="text-center"></th>';
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

			$codigo = !empty($row["codigo"]) ? '(#' . $row["codigo"] . ')' : '';

			$html .= '<tr>';
			$html .= '<td>' . $num . '</td>';
			$html .= '<td>' . $row["nombre_menu_usuario"] . ' ' . $codigo . '</td>';
			$html .= '<td>' . $row["nombre_campo_usuario"] . '</td>';
			$html .= '<td style="white-space: pre-line;">' . $row["valor_original"] . '</td>';
			$html .= '<td style="white-space: pre-line;">' . $nuevo_valor . '</td>';
			$html .= '<td>';
			$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_con_nuevo_aden_arrendamiento_eliminar_detalle_adenda(' . $row["id"] . ')"><i class="fa fa-trash"></i></a>';
			$html .= '</td>';
			$html .= '</tr>';

			$num += 1;
		}

		$html .= '</tbody>';
		$html .= '</table>';
	}

	// CAMBIO DE PROPIETARIOS,BENEFICIARIOS RESPONSABLES IR Y INCREMENTOS
	$query_otros = "SELECT a.id, a.nombre_menu_usuario, a.valor_original,  a.valor_int, a.valor_id_tabla, cd.codigo
	FROM cont_adendas_detalle AS a
	LEFT JOIN cont_contrato_detalle AS cd ON cd.id = a.contrato_detalle_id
	WHERE a.tipo_valor = 'id_tabla' ";

	$data = json_decode($id_adendas);
	$contador = 0;
	$ids = '';
	foreach ($data as $value) {
		if ($contador > 0) {
			$ids .= ',';
		}
		$ids .= $value;
		$contador++;
	}
	$query_otros .= " AND a.id IN(" . $ids . ")";

	$list_query_otros = $mysqli->query($query_otros);
	$row_count_otros = $list_query_otros->num_rows;

	if ($row_count_otros > 0) {
		while ($row = $list_query_otros->fetch_assoc()) {
			$codigo_contrato = !empty($row['codigo']) ? '- Contrato #' . $row['codigo'] : '';
			$valores_originales = [];
			$valores_nuevos = [];

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
				$html .= '<th colspan="4" style="text-align: center; vertical-align: middle;">Cambiar Propietario ' . $codigo_contrato . '</th>';
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
				$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_con_nuevo_aden_arrendamiento_eliminar_detalle_adenda(' . $row["id"] . ')"><i class="fa fa-trash"></i></a></td>';
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
				$html .= '<th colspan="4" style="text-align: center; vertical-align: middle;">Cambiar Beneficiario ' . $codigo_contrato . '</th>';
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
				$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_con_nuevo_aden_arrendamiento_eliminar_detalle_adenda(' . $row["id"] . ')"><i class="fa fa-trash"></i></a></td>';
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

			if ($row["nombre_menu_usuario"] == 'Incremento') {
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
				$html .= '<th colspan="4" style="text-align: center; vertical-align: middle;">Cambiar Incremento ' . $codigo_contrato . '</th>';
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
				$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_con_nuevo_aden_arrendamiento_eliminar_detalle_adenda(' . $row["id"] . ')"><i class="fa fa-trash"></i></a></td>';
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

			if ($row["nombre_menu_usuario"] == 'Responsable IR') {
				$query = "
				SELECT 
					r.id,
					r.contrato_id,
					r.tipo_documento_id,
					r.num_documento,
					r.nombres,
					r.estado_emisor,
					r.porcentaje,
					r.status,
					r.user_created_id,
					r.created_at,
					td.nombre AS tipo_documento
				FROM cont_responsable_ir as r
				LEFT JOIN cont_tipo_docu_identidad AS td ON td.id = r.tipo_documento_id
				WHERE r.id IN ('" . $row["valor_original"] . "' , '" . $row["valor_int"] . "')";
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
				$html .= '<th colspan="4" style="text-align: center; vertical-align: middle;">Cambiar Responsable IR ' . $codigo_contrato . '</th>';
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
				$html .= '<td>Tipo de Documento de Identidad</td>';
				$html .= '<td>' . $valores_originales[0]["tipo_documento"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["tipo_documento"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Nro Documento</td>';
				$html .= '<td>' . $valores_originales[0]["num_documento"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["num_documento"] . '</td>';
				$html .= '<td rowspan="9">';
				$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_con_nuevo_aden_arrendamiento_eliminar_detalle_adenda(' . $row["id"] . ')"><i class="fa fa-trash"></i></a></td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Nombres</td>';
				$html .= '<td>' . $valores_originales[0]["nombres"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["nombres"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Porcentaje</td>';
				$html .= '<td>' . $valores_originales[0]["porcentaje"] . '</td>';
				$html .= '<td>' . $valores_nuevos[0]["porcentaje"] . '</td>';
				$html .= '</tr>';

				$html .= '</tbody>';
				$html .= '</table>';
			}
		}
	}



	// NUEVOS PROPIETARIOS Y BENEFICIARIOS Y INCREMENTOS
	$query_otros = "SELECT a.id, a.nombre_menu_usuario, a.valor_original,  a.valor_int, a.valor_id_tabla, cd.codigo
	FROM cont_adendas_detalle AS a
	LEFT JOIN cont_contrato_detalle AS cd ON cd.id = a.contrato_detalle_id
	WHERE a.tipo_valor = 'registro' ";

	$data = json_decode($id_adendas);
	$contador = 0;
	$ids = '';
	foreach ($data as $value) {
		if ($contador > 0) {
			$ids .= ',';
		}
		$ids .= $value;
		$contador++;
	}
	$query_otros .= " AND a.id IN(" . $ids . ")";

	$list_query_otros = $mysqli->query($query_otros);
	$row_count_otros = $list_query_otros->num_rows;

	if ($row_count_otros > 0) {
		while ($row = $list_query_otros->fetch_assoc()) {
			$codigo_contrato = !empty($row['codigo']) ? '- Contrato #' . $row['codigo'] : '';
			$valores_originales = [];
			$valores_nuevos = [];

			if ($row["nombre_menu_usuario"] == 'Inflación') {
				$query = "SELECT i.id, DATE_FORMAT(i.fecha, '%d-%m-%Y') as fecha, tp.nombre  AS tipo_periodicidad, i.numero, p.nombre as tipo_anio_mes, m.sigla AS moneda, i.porcentaje_anadido, i.tope_inflacion, i.minimo_inflacion
				FROM cont_inflaciones AS i
				INNER JOIN cont_tipo_periodicidad AS tp ON tp.id = i.tipo_periodicidad_id
				LEFT JOIN tbl_moneda AS m ON m.id = i.moneda_id
				LEFT JOIN cont_periodo as p ON p.id = i.tipo_anio_mes
				WHERE i.id IN ('" . $row["valor_int"] . "')
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
				$html .= '<th colspan="4" style="text-align: center; vertical-align: middle;">Nueva Inflación ' . $codigo_contrato . '</th>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<th>Campo</th>';
				$html .= '<th>Nuevo Valor</th>';
				$html .= '<th></th>';
				$html .= '</tr>';

				$html .= '</thead>';
				$html .= '<tbody>';

				if (!empty($valores_nuevos[0]["fecha"])) {
					$html .= '<tr>';
					$html .= '<td>Fecha de Ajuste</td>';
					$html .= '<td>' . $valores_nuevos[0]["fecha"] . '</td>';
					$html .= '<td rowspan="9">';
					$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_con_nuevo_aden_arrendamiento_eliminar_detalle_adenda(' . $row["id"] . ')"><i class="fa fa-trash"></i></a></td>';
					$html .= '</tr>';
				}

				$html .= '<tr>';
				$html .= '<td>Periodicidad</td>';
				$html .= '<td>' . $valores_nuevos[0]['tipo_periodicidad'] . ' ' . $valores_nuevos[0]['numero'] . ' ' . $valores_nuevos[0]['tipo_anio_mes'] . '</td>';
				$html .= '</tr>';

				if (!empty($valores_nuevos[0]["moneda"])) {
					$html .= '<tr>';
					$html .= '<td>Curva</td>';
					$html .= '<td>' . $valores_nuevos[0]["moneda"] . '</td>';
					$html .= '</tr>';
				}

				$html .= '<tr>';
				$html .= '<td>Porcentaje Añadido</td>';
				$html .= '<td>' . $valores_nuevos[0]["porcentaje_anadido"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Tope de Inflación</td>';
				$html .= '<td>' . $valores_nuevos[0]["tope_inflacion"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Minimo de Inflación</td>';
				$html .= '<td>' . $valores_nuevos[0]["minimo_inflacion"] . '</td>';
				$html .= '</tr>';


				$html .= '</tbody>';
				$html .= '</table>';
			}

			if ($row["nombre_menu_usuario"] == 'Cuota Extraordinaria') {
				$query = "SELECT c.id, m.nombre as mes, c.multiplicador, c.fecha
				FROM cont_cuotas_extraordinarias AS c
				INNER JOIN tbl_meses AS m ON m.id = c.mes
				WHERE c.id IN ('" . $row["valor_int"] . "')
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
				$html .= '<th colspan="4" style="text-align: center; vertical-align: middle;">Nueva Cuota Extraordinaria ' . $codigo_contrato . '</th>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<th>Campo</th>';
				$html .= '<th>Nuevo Valor</th>';
				$html .= '<th></th>';
				$html .= '</tr>';

				$html .= '</thead>';
				$html .= '<tbody>';

				if (!empty($valores_nuevos[0]["fecha"])) {
					$html .= '<tr>';
					$html .= '<td>Fecha de Ajuste</td>';
					$html .= '<td>' . $valores_nuevos[0]["fecha"] . '</td>';
					$html .= '<td rowspan="9">';
					$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_con_nuevo_aden_arrendamiento_eliminar_detalle_adenda(' . $row["id"] . ')"><i class="fa fa-trash"></i></a></td>';
					$html .= '</tr>';
				}

				$html .= '<tr>';
				$html .= '<td>Mes</td>';
				$html .= '<td>' . $valores_nuevos[0]["mes"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Multiplicador</td>';
				$html .= '<td>' . $valores_nuevos[0]["multiplicador"] . '</td>';
				$html .= '</tr>';



				$html .= '</tbody>';
				$html .= '</table>';
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

				$html .= '<br>';
				$html .= '<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">';
				$html .= '<thead>';

				$html .= '<tr>';
				$html .= '<th colspan="4" style="text-align: center; vertical-align: middle;">Nuevo Propietario</th>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<th>Campo</th>';
				$html .= '<th>Nuevo Valor</th>';
				$html .= '<th></th>';
				$html .= '</tr>';

				$html .= '</thead>';
				$html .= '<tbody>';

				$html .= '<tr>';
				$html .= '<td>Tipo Persona</td>';
				$html .= '<td>' . $valores_nuevos[0]["tipo_persona"] . '</td>';
				$html .= '<td rowspan="9">';
				$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_con_nuevo_aden_arrend_eliminar_detalle_adenda(' . $row["id"] . ')"><i class="fa fa-trash"></i></a></td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Nombre</td>';
				$html .= '<td>' . $valores_nuevos[0]["nombre"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Tipo de Documento de Identidad</td>';
				$html .= '<td>' . $valores_nuevos[0]["tipo_docu_identidad"] . '</td>';
				$html .= '</tr>';

				if ($valores_nuevos[0]["tipo_docu_identidad_id"] != "2") {
					$html .= '<tr>';
					$html .= '<td>' . $valores_nuevos[0]["tipo_docu_identidad"] . '</td>';
					$html .= '<td>' . $valores_nuevos[0]["num_docu"] . '</td>';
					$html .= '</tr>';
				}

				$html .= '<tr>';
				$html .= '<td>Número de RUC</td>';
				$html .= '<td>' . $valores_nuevos[0]["num_ruc"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Domicilio del propietario</td>';
				$html .= '<td>' . $valores_nuevos[0]["direccion"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Representante Legal</td>';
				$html .= '<td>' . $valores_nuevos[0]["representante_legal"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>N° de Partida Registral de la empresa</td>';
				$html .= '<td>' . $valores_nuevos[0]["num_partida_registral"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Persona de contacto</td>';
				$html .= '<td>' . $valores_nuevos[0]["contacto_nombre"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Teléfono de la persona de contacto</td>';
				$html .= '<td>' . $valores_nuevos[0]["contacto_telefono"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>E-mail de la persona de contacto</td>';
				$html .= '<td>' . $valores_nuevos[0]["contacto_email"] . '</td>';
				$html .= '</tr>';

				$html .= '</tbody>';
				$html .= '</table>';
			}

			if ($row["nombre_menu_usuario"] == 'Beneficiario') {
				$query = "
						SELECT b.id,
						tp.nombre AS tipo_persona,
						td.nombre AS tipo_docu_identidad,
						b.num_docu,
						b.nombre,
						f.nombre AS forma_pago,
						ba.nombre AS banco,
						b.num_cuenta_bancaria,
						b.num_cuenta_cci,
						b.tipo_monto_id,
						b.monto
					FROM cont_beneficiarios b
					LEFT JOIN cont_tipo_persona tp ON b.tipo_persona_id = tp.id
					LEFT JOIN cont_tipo_docu_identidad td ON b.tipo_docu_identidad_id = td.id
					INNER JOIN cont_forma_pago f ON b.forma_pago_id = f.id
					LEFT JOIN tbl_bancos ba ON b.banco_id = ba.id 
					WHERE b.id = " . $row["valor_int"];

				$valores_originales = [];
				$valores_nuevos = [];
				$list_query = $mysqli->query($query);
				while ($li = $list_query->fetch_assoc()) {
					if ($li["id"] == $row["valor_int"]) {
						$valores_nuevos[] = $li;
					}
				}

				$beneficiario_id = $valores_nuevos[0]["id"];
				$ben_tipo_persona = $valores_nuevos[0]["tipo_persona"];
				$ben_tipo_docu_identidad = $valores_nuevos[0]["tipo_docu_identidad"];
				$ben_num_docu = $valores_nuevos[0]["num_docu"];
				$ben_nombre = $valores_nuevos[0]["nombre"];
				$ben_direccion = '';
				$ben_forma_pago = $valores_nuevos[0]["forma_pago"];
				$ben_banco = $valores_nuevos[0]["banco"];
				$ben_num_cuenta_bancaria = $valores_nuevos[0]["num_cuenta_bancaria"];
				$ben_num_cuenta_cci = $valores_nuevos[0]["num_cuenta_cci"];
				$ben_tipo_monto_id = $valores_nuevos[0]["tipo_monto_id"];


				$ben_monto_beneficiario = number_format($valores_nuevos[0]["monto"], 2, '.', ',');


				$html .= '<br>';
				$html .= '<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">';
				$html .= '<thead>';

				$html .= '<tr>';
				$html .= '<th colspan="4" style="text-align: center; vertical-align: middle;">Nuevo Beneficiario ' . $codigo_contrato . '</th>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<th>Campo</th>';
				$html .= '<th>Nuevo Valor</th>';
				$html .= '<th></th>';
				$html .= '</tr>';

				$html .= '</thead>';
				$html .= '<tbody>';

				$html .= '<tr>';
				$html .= '<td>Tipo de Persona</td>';
				$html .= '<td>' . $ben_tipo_persona . '</td>';
				$html .= '<td rowspan="9">';
				$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_con_nuevo_aden_arrend_eliminar_detalle_adenda(' . $row["id"] . ')"><i class="fa fa-trash"></i></a></td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Nombre</td>';
				$html .= '<td>' . $ben_nombre . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Tipo de Documento de Identidad</td>';
				$html .= '<td>' . $ben_tipo_docu_identidad . '</td>';
				$html .= '</tr>';


				$html .= '<tr>';
				$html .= '<td>Número de Documento de Identidad</td>';
				$html .= '<td>' . $ben_num_docu . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Tipo de forma de pago</td>';
				$html .= '<td>' . $ben_forma_pago . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Nombre del Banco</td>';
				$html .= '<td>' . $ben_banco . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>N° de la cuenta bancaria</td>';
				$html .= '<td>' . $ben_num_cuenta_bancaria . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>N° de CCI bancario</td>';
				$html .= '<td>' . $ben_num_cuenta_cci . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Monto</td>';
				$html .= '<td>' . $ben_monto_beneficiario . '</td>';
				$html .= '</tr>';



				$html .= '</tbody>';
				$html .= '</table>';
			}

			if ($row["nombre_menu_usuario"] == 'Incremento') {
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
				$html .= '<th colspan="2" style="text-align: center; vertical-align: middle;">Nuevo Incremento ' . $codigo_contrato . '</th>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<th>Campo</th>';
				$html .= '<th>Nuevo Valor</th>';
				$html .= '<th></th>';
				$html .= '</tr>';

				$html .= '</thead>';
				$html .= '<tbody>';

				$html .= '<tr>';
				$html .= '<td>Valor</td>';
				$html .= '<td>' . $valor_nuevo["valor"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Tipo Valor</td>';
				$html .= '<td>' . $valor_nuevo["tipo_valor"] . '</td>';
				$html .= '<td rowspan="9">';
				$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_con_nuevo_aden_arrend_eliminar_detalle_adenda(' . $row["id"] . ')"><i class="fa fa-trash"></i></a></td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Continuidad</td>';
				$html .= '<td>' . $valor_nuevo["tipo_continuidad"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Apartir del</td>';
				$html .= '<td>' . $valor_nuevo["a_partir_del_año"] . '</td>';
				$html .= '</tr>';

				$html .= '</tbody>';
				$html .= '</table>';
			}

			if ($row["nombre_menu_usuario"] == 'Responsable IR') {
				$query = "
				SELECT 
					r.id,
					r.contrato_id,
					r.tipo_documento_id,
					r.num_documento,
					r.nombres,
					r.estado_emisor,
					r.porcentaje,
					r.status,
					r.user_created_id,
					r.created_at,
					td.nombre AS tipo_documento
				FROM cont_responsable_ir as r
				LEFT JOIN cont_tipo_docu_identidad AS td ON td.id = r.tipo_documento_id
				WHERE r.id = " . $row["valor_int"];

				$list_query = $mysqli->query($query);
				while ($li = $list_query->fetch_assoc()) {
					$valor_nuevo = $li;
				}

				$html .= '<br>';
				$html .= '<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">';
				$html .= '<thead>';

				$html .= '<tr>';
				$html .= '<th colspan="4" style="text-align: center; vertical-align: middle;">Nuevo Responsable IR ' . $codigo_contrato . '</th>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<th>Campo</th>';
				$html .= '<th>Nuevo Valor</th>';
				$html .= '<th></th>';
				$html .= '</tr>';

				$html .= '</thead>';
				$html .= '<tbody>';

				$html .= '<tr>';
				$html .= '<td>Tipo de Documento de Identidad</td>';
				$html .= '<td>' . $valor_nuevo["tipo_documento"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Nro Documento</td>';
				$html .= '<td>' . $valor_nuevo["num_documento"] . '</td>';
				$html .= '<td rowspan="9">';
				$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_con_nuevo_aden_arrendamiento_eliminar_detalle_adenda(' . $row["id"] . ')"><i class="fa fa-trash"></i></a></td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Nombres</td>';
				$html .= '<td>' . $valor_nuevo["nombres"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Porcentaje</td>';
				$html .= '<td>' . $valor_nuevo["porcentaje"] . '</td>';
				$html .= '</tr>';

				$html .= '</tbody>';
				$html .= '</table>';
			}

			if ($row["nombre_menu_usuario"] == 'Suministro') {
				$query = "SELECT s.id, s.tipo_servicio_id, sp.nombre AS tipo_servicio, s.nro_suministro, s.tipo_compromiso_pago_id, ps.nombre AS tipo_compromiso, s.monto_o_porcentaje
				FROM cont_inmueble_suministros AS s
				LEFT JOIN cont_tipo_pago_servicio AS ps ON ps.id = s.tipo_compromiso_pago_id
				LEFT JOIN cont_tipo_servicio_publico AS sp ON sp.id = s.tipo_servicio_id
				INNER JOIN cont_inmueble AS i ON i.id = s.inmueble_id
				INNER JOIN cont_contrato_detalle AS cd ON cd.id = i.contrato_detalle_id
				WHERE s.id = " . $row["valor_int"];

				$list_query = $mysqli->query($query);
				while ($li = $list_query->fetch_assoc()) {
					$valor_nuevo = $li;
				}

				$html .= '<br>';
				$html .= '<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">';
				$html .= '<thead>';

				$html .= '<tr>';
				$html .= '<th colspan="4" style="text-align: center; vertical-align: middle;">Nuevo Suministro ' . $codigo_contrato . '</th>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<th>Campo</th>';
				$html .= '<th>Valor Actual</th>';
				$html .= '<th></th>';
				$html .= '</tr>';

				$html .= '</thead>';
				$html .= '<tbody>';

				$html .= '<tr>';
				$html .= '<td>Tipo de Servicio</td>';
				$html .= '<td>' . $valor_nuevo["tipo_servicio"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>N° de Suministro</td>';
				$html .= '<td>' . $valor_nuevo["nro_suministro"] . '</td>';
				$html .= '<td rowspan="9">';
				$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_con_nuevo_aden_arrendamiento_eliminar_detalle_adenda(' . $row["id"] . ')"><i class="fa fa-trash"></i></a></td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Compromiso de pago</td>';
				$html .= '<td>' . $valor_nuevo["tipo_compromiso"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Monto/Porcentaje</td>';
				$html .= '<td>' . $valor_nuevo["monto_o_porcentaje"] . '</td>';
				$html .= '</tr>';

				$html .= '</tbody>';
				$html .= '</table>';
			}
		}
	}



	// Eliminar DE PROPIETARIOS Y BENEFICIARIOS Y INCREMENTOS
	$query_otros = "SELECT a.id, a.nombre_menu_usuario, a.valor_original,  a.valor_int, a.valor_id_tabla, cd.codigo
	FROM cont_adendas_detalle AS a
	LEFT JOIN cont_contrato_detalle AS cd ON cd.id = a.contrato_detalle_id
	WHERE a.tipo_valor = 'eliminar' ";
	$data = json_decode($id_adendas);
	$contador = 0;
	$ids = '';
	foreach ($data as $value) {
		if ($contador > 0) {
			$ids .= ',';
		}
		$ids .= $value;
		$contador++;
	}
	$query_otros .= " AND a.id IN(" . $ids . ")";

	$list_query_otros = $mysqli->query($query_otros);
	$row_count_otros = $list_query_otros->num_rows;

	if ($row_count_otros > 0) {
		while ($row = $list_query_otros->fetch_assoc()) {
			$codigo_contrato = !empty($row['codigo']) ? '- Contrato #' . $row['codigo'] : '';
			$valores_originales = [];
			$valores_nuevos = [];

			if ($row["nombre_menu_usuario"] == 'Inflación') {
				$query = "SELECT i.id, DATE_FORMAT(i.fecha, '%d-%m-%Y') as fecha, tp.nombre  AS tipo_periodicidad, i.numero, p.nombre as tipo_anio_mes, m.sigla AS moneda, i.porcentaje_anadido, i.tope_inflacion, i.minimo_inflacion
				FROM cont_inflaciones AS i
				INNER JOIN cont_tipo_periodicidad AS tp ON tp.id = i.tipo_periodicidad_id
				LEFT JOIN tbl_moneda AS m ON m.id = i.moneda_id
				LEFT JOIN cont_periodo as p ON p.id = i.tipo_anio_mes
				WHERE i.id IN ('" . $row["valor_int"] . "')
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
				$html .= '<th colspan="4" style="text-align: center; vertical-align: middle;">Eliminar Inflación ' . $codigo_contrato . '</th>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<th>Campo</th>';
				$html .= '<th>Valor Actual</th>';
				$html .= '<th></th>';
				$html .= '</tr>';

				$html .= '</thead>';
				$html .= '<tbody>';

				if (!empty($valores_nuevos[0]["fecha"])) {
					$html .= '<tr>';
					$html .= '<td>Fecha de Ajuste</td>';
					$html .= '<td>' . $valores_nuevos[0]["fecha"] . '</td>';
					$html .= '<td rowspan="9">';
					$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_con_nuevo_aden_arrendamiento_eliminar_detalle_adenda(' . $row["id"] . ')"><i class="fa fa-trash"></i></a></td>';
					$html .= '</tr>';
				}

				$html .= '<tr>';
				$html .= '<td>Periodicidad</td>';
				$html .= '<td>' . $valores_nuevos[0]['tipo_periodicidad'] . ' ' . $valores_nuevos[0]['numero'] . ' ' . $valores_nuevos[0]['tipo_anio_mes'] . '</td>';
				$html .= '</tr>';

				if (!empty($valores_nuevos[0]["moneda"])) {
					$html .= '<tr>';
					$html .= '<td>Curva</td>';
					$html .= '<td>' . $valores_nuevos[0]["moneda"] . '</td>';
					$html .= '</tr>';
				}

				$html .= '<tr>';
				$html .= '<td>Porcentaje Añadido</td>';
				$html .= '<td>' . $valores_nuevos[0]["porcentaje_anadido"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Tope de Inflación</td>';
				$html .= '<td>' . $valores_nuevos[0]["tope_inflacion"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Minimo de Inflación</td>';
				$html .= '<td>' . $valores_nuevos[0]["minimo_inflacion"] . '</td>';
				$html .= '</tr>';


				$html .= '</tbody>';
				$html .= '</table>';
			}

			if ($row["nombre_menu_usuario"] == 'Cuota Extraordinaria') {
				$query = "SELECT c.id, m.nombre as mes, c.multiplicador, c.fecha
				FROM cont_cuotas_extraordinarias AS c
				INNER JOIN tbl_meses AS m ON m.id = c.mes
				WHERE c.id IN ('" . $row["valor_int"] . "')
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
				$html .= '<th colspan="4" style="text-align: center; vertical-align: middle;">Eliminar Cuota Extraordinaria ' . $codigo_contrato . '</th>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<th>Campo</th>';
				$html .= '<th>Valor Actual</th>';
				$html .= '<th></th>';
				$html .= '</tr>';

				$html .= '</thead>';
				$html .= '<tbody>';

				if (!empty($valores_nuevos[0]["fecha"])) {
					$html .= '<tr>';
					$html .= '<td>Fecha de Ajuste</td>';
					$html .= '<td>' . $valores_nuevos[0]["fecha"] . '</td>';
					$html .= '<td rowspan="9">';
					$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_con_nuevo_aden_arrendamiento_eliminar_detalle_adenda(' . $row["id"] . ')"><i class="fa fa-trash"></i></a></td>';
					$html .= '</tr>';
				}

				$html .= '<tr>';
				$html .= '<td>Mes</td>';
				$html .= '<td>' . $valores_nuevos[0]["mes"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Multiplicador</td>';
				$html .= '<td>' . $valores_nuevos[0]["multiplicador"] . '</td>';
				$html .= '</tr>';



				$html .= '</tbody>';
				$html .= '</table>';
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

				$html .= '<br>';
				$html .= '<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">';
				$html .= '<thead>';

				$html .= '<tr>';
				$html .= '<th colspan="4" style="text-align: center; vertical-align: middle;">Eliminar Propietario ' . $codigo_contrato . '</th>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<th>Campo</th>';
				$html .= '<th>Nuevo Valor</th>';
				$html .= '<th></th>';
				$html .= '</tr>';

				$html .= '</thead>';
				$html .= '<tbody>';

				$html .= '<tr>';
				$html .= '<td>Tipo Persona</td>';
				$html .= '<td>' . $valores_nuevos[0]["tipo_persona"] . '</td>';
				$html .= '<td rowspan="9">';
				$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_con_nuevo_aden_arrend_eliminar_detalle_adenda(' . $row["id"] . ')"><i class="fa fa-trash"></i></a></td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Nombre</td>';
				$html .= '<td>' . $valores_nuevos[0]["nombre"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Tipo de Documento de Identidad</td>';
				$html .= '<td>' . $valores_nuevos[0]["tipo_docu_identidad"] . '</td>';
				$html .= '</tr>';

				if ($valores_nuevos[0]["tipo_docu_identidad_id"] != "2") {
					$html .= '<tr>';
					$html .= '<td>' . $valores_nuevos[0]["tipo_docu_identidad"] . '</td>';
					$html .= '<td>' . $valores_nuevos[0]["num_docu"] . '</td>';
					$html .= '</tr>';
				}

				$html .= '<tr>';
				$html .= '<td>Número de RUC</td>';
				$html .= '<td>' . $valores_nuevos[0]["num_ruc"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Domicilio del propietario</td>';
				$html .= '<td>' . $valores_nuevos[0]["direccion"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Representante Legal</td>';
				$html .= '<td>' . $valores_nuevos[0]["representante_legal"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>N° de Partida Registral de la empresa</td>';
				$html .= '<td>' . $valores_nuevos[0]["num_partida_registral"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Persona de contacto</td>';
				$html .= '<td>' . $valores_nuevos[0]["contacto_nombre"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Teléfono de la persona de contacto</td>';
				$html .= '<td>' . $valores_nuevos[0]["contacto_telefono"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>E-mail de la persona de contacto</td>';
				$html .= '<td>' . $valores_nuevos[0]["contacto_email"] . '</td>';
				$html .= '</tr>';

				$html .= '</tbody>';
				$html .= '</table>';
			}

			if ($row["nombre_menu_usuario"] == 'Beneficiario') {
				$query = "
						SELECT b.id,
						tp.nombre AS tipo_persona,
						td.nombre AS tipo_docu_identidad,
						b.num_docu,
						b.nombre,
						f.nombre AS forma_pago,
						ba.nombre AS banco,
						b.num_cuenta_bancaria,
						b.num_cuenta_cci,
						b.tipo_monto_id,
						b.monto
					FROM cont_beneficiarios b
					LEFT JOIN cont_tipo_persona tp ON b.tipo_persona_id = tp.id
					LEFT JOIN cont_tipo_docu_identidad td ON b.tipo_docu_identidad_id = td.id
					INNER JOIN cont_forma_pago f ON b.forma_pago_id = f.id
					LEFT JOIN tbl_bancos ba ON b.banco_id = ba.id 
					WHERE b.id = " . $row["valor_int"];

				$valores_originales = [];
				$valores_nuevos = [];
				$list_query = $mysqli->query($query);
				while ($li = $list_query->fetch_assoc()) {
					if ($li["id"] == $row["valor_int"]) {
						$valores_nuevos[] = $li;
					}
				}

				$beneficiario_id = $valores_nuevos[0]["id"];
				$ben_tipo_persona = $valores_nuevos[0]["tipo_persona"];
				$ben_tipo_docu_identidad = $valores_nuevos[0]["tipo_docu_identidad"];
				$ben_num_docu = $valores_nuevos[0]["num_docu"];
				$ben_nombre = $valores_nuevos[0]["nombre"];
				$ben_direccion = '';
				$ben_forma_pago = $valores_nuevos[0]["forma_pago"];
				$ben_banco = $valores_nuevos[0]["banco"];
				$ben_num_cuenta_bancaria = $valores_nuevos[0]["num_cuenta_bancaria"];
				$ben_num_cuenta_cci = $valores_nuevos[0]["num_cuenta_cci"];
				$ben_tipo_monto_id = $valores_nuevos[0]["tipo_monto_id"];


				$ben_monto_beneficiario = number_format($valores_nuevos[0]["monto"], 2, '.', ',');


				$html .= '<br>';
				$html .= '<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">';
				$html .= '<thead>';

				$html .= '<tr>';
				$html .= '<th colspan="4" style="text-align: center; vertical-align: middle;">Eliminar Beneficiario ' . $codigo_contrato . '</th>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<th>Campo</th>';
				$html .= '<th>Valor Actual</th>';
				$html .= '<th></th>';
				$html .= '</tr>';

				$html .= '</thead>';
				$html .= '<tbody>';

				$html .= '<tr>';
				$html .= '<td>Tipo de Persona</td>';
				$html .= '<td>' . $ben_tipo_persona . '</td>';
				$html .= '<td rowspan="9">';
				$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_con_nuevo_aden_arrend_eliminar_detalle_adenda(' . $row["id"] . ')"><i class="fa fa-trash"></i></a></td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Nombre</td>';
				$html .= '<td>' . $ben_nombre . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Tipo de Documento de Identidad</td>';
				$html .= '<td>' . $ben_tipo_docu_identidad . '</td>';
				$html .= '</tr>';


				$html .= '<tr>';
				$html .= '<td>Número de Documento de Identidad</td>';
				$html .= '<td>' . $ben_num_docu . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Tipo de forma de pago</td>';
				$html .= '<td>' . $ben_forma_pago . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Nombre del Banco</td>';
				$html .= '<td>' . $ben_banco . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>N° de la cuenta bancaria</td>';
				$html .= '<td>' . $ben_num_cuenta_bancaria . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>N° de CCI bancario</td>';
				$html .= '<td>' . $ben_num_cuenta_cci . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Monto</td>';
				$html .= '<td>' . $ben_monto_beneficiario . '</td>';
				$html .= '</tr>';



				$html .= '</tbody>';
				$html .= '</table>';
			}

			if ($row["nombre_menu_usuario"] == 'Responsable IR') {
				$query = "
				SELECT 
					r.id,
					r.contrato_id,
					r.tipo_documento_id,
					r.num_documento,
					r.nombres,
					r.estado_emisor,
					r.porcentaje,
					r.status,
					r.user_created_id,
					r.created_at,
					td.nombre AS tipo_documento
				FROM cont_responsable_ir as r
				LEFT JOIN cont_tipo_docu_identidad AS td ON td.id = r.tipo_documento_id
				WHERE r.id = " . $row["valor_int"];

				$list_query = $mysqli->query($query);
				while ($li = $list_query->fetch_assoc()) {
					$valor_nuevo = $li;
				}

				$html .= '<br>';
				$html .= '<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">';
				$html .= '<thead>';

				$html .= '<tr>';
				$html .= '<th colspan="4" style="text-align: center; vertical-align: middle;">Eliminar Responsable IR ' . $codigo_contrato . '</th>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<th>Campo</th>';
				$html .= '<th>Valor Actual</th>';
				$html .= '<th></th>';
				$html .= '</tr>';

				$html .= '</thead>';
				$html .= '<tbody>';

				$html .= '<tr>';
				$html .= '<td>Tipo de Documento de Identidad</td>';
				$html .= '<td>' . $valor_nuevo["tipo_documento"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Nro Documento</td>';
				$html .= '<td>' . $valor_nuevo["num_documento"] . '</td>';
				$html .= '<td rowspan="9">';
				$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_con_nuevo_aden_arrendamiento_eliminar_detalle_adenda(' . $row["id"] . ')"><i class="fa fa-trash"></i></a></td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Nombres</td>';
				$html .= '<td>' . $valor_nuevo["nombres"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Porcentaje</td>';
				$html .= '<td>' . $valor_nuevo["porcentaje"] . '</td>';
				$html .= '</tr>';

				$html .= '</tbody>';
				$html .= '</table>';
			}

			if ($row["nombre_menu_usuario"] == 'Suministro') {
				$query = "SELECT s.id, s.tipo_servicio_id, sp.nombre AS tipo_servicio, s.nro_suministro, s.tipo_compromiso_pago_id, ps.nombre AS tipo_compromiso, s.monto_o_porcentaje
				FROM cont_inmueble_suministros AS s
				LEFT JOIN cont_tipo_pago_servicio AS ps ON ps.id = s.tipo_compromiso_pago_id
				LEFT JOIN cont_tipo_servicio_publico AS sp ON sp.id = s.tipo_servicio_id
				INNER JOIN cont_inmueble AS i ON i.id = s.inmueble_id
				INNER JOIN cont_contrato_detalle AS cd ON cd.id = i.contrato_detalle_id
				WHERE s.id = " . $row["valor_int"];

				$list_query = $mysqli->query($query);
				while ($li = $list_query->fetch_assoc()) {
					$valor_nuevo = $li;
				}

				$html .= '<br>';
				$html .= '<table class="table table-bordered table-striped no-mb" style="font-size:10px; margin-top: 10px;">';
				$html .= '<thead>';

				$html .= '<tr>';
				$html .= '<th colspan="4" style="text-align: center; vertical-align: middle;">Eliminar Suministro ' . $codigo_contrato . '</th>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<th>Campo</th>';
				$html .= '<th>Valor Actual</th>';
				$html .= '<th></th>';
				$html .= '</tr>';

				$html .= '</thead>';
				$html .= '<tbody>';

				$html .= '<tr>';
				$html .= '<td>Tipo de Servicio</td>';
				$html .= '<td>' . $valor_nuevo["tipo_servicio"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>N° de Suministro</td>';
				$html .= '<td>' . $valor_nuevo["nro_suministro"] . '</td>';
				$html .= '<td rowspan="9">';
				$html .= '<a class="btn btn-danger btn-xs" data-toggle="tooltip" data-placement="top" title="Eliminar" onclick="sec_con_nuevo_aden_arrendamiento_eliminar_detalle_adenda(' . $row["id"] . ')"><i class="fa fa-trash"></i></a></td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Compromiso de pago</td>';
				$html .= '<td>' . $valor_nuevo["tipo_compromiso"] . '</td>';
				$html .= '</tr>';

				$html .= '<tr>';
				$html .= '<td>Monto/Porcentaje</td>';
				$html .= '<td>' . $valor_nuevo["monto_o_porcentaje"] . '</td>';
				$html .= '</tr>';

				$html .= '</tbody>';
				$html .= '</table>';
			}
		}
	}

	$html .= '<br>';

	if ($mysqli->error) {
		$result["consulta_error"] = $mysqli->error;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $html;

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_adenda_detalle_nuevos_registros") {

	$usuario_id = $login ? $login['id'] : null;
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

	if ($_POST['tabla'] == "representante_legal") {
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
		if ($filename != "") {
			$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
			$nombre_archivo = $contrato_id . "_DNI" . date('YmdHis') . "." . $fileExt;
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
							'0',
							3,
							'" . $nombre_archivo . "',
							'" . $fileExt . "',
							'" . $filesize . "',
							'" . $path . "',
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
		if ($filename != "") {
			$fileExt = pathinfo($filename, PATHINFO_EXTENSION);
			$nombre_archivo = $contrato_id . "_VIG" . date('YmdHis') . "." . $fileExt;
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
							'0',
							2,
							'" . $nombre_archivo . "',
							'" . $fileExt . "',
							'" . $filesize . "',
							'" . $path . "',
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
			'" . $dniRepresentante . "', '"
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
		if ($mysqli->error) {
			$error .= $error = $mysqli->error;
			$result["http_code"] = 404;
			$result["status"] = "A ocurrido un error al registrar el propietario.";
			$result["result"] = 0;
			$result["error"] = $error;
			echo json_encode($result);
			exit();
		}


		$usuario_id = $login ? $login['id'] : null;
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
		if ($mysqli->error) {
			$error = $mysqli->error . " | " . $query_insert;
		}

		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestión.";
		$result["result"] = $insert_id;
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}

	if ($_POST['tabla'] == "contraprestacion") {


		$usuario_id = $login ? $login['id'] : null;
		$created_at = date("Y-m-d H:i:s");

		$moneda_id = $_POST["moneda_id"];
		$forma_pago = 0;
		$forma_pago_detallado = "'" . $_POST["forma_pago_detallado"] . "'";
		$tipo_comprobante = $_POST["tipo_comprobante"];
		$plazo_pago = "'" . $_POST["plazo_pago"] . "'";

		if (empty($_POST["subtotal"])) {
			$subtotal = 0;
		} else {
			$subtotal = str_replace(",", "", $_POST["subtotal"]);
		}

		if (empty($_POST["igv"])) {
			$igv = 0;
		} else {
			$igv = str_replace(",", "", $_POST["igv"]);
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
		if ($mysqli->error) {
			$error .= $error = $mysqli->error;
			$result["http_code"] = 404;
			$result["status"] = "A ocurrido un error al registrar la contraprestación.";
			$result["result"] = 0;
			$result["error"] = $error;
			echo json_encode($result);
			exit();
		}


		$usuario_id = $login ? $login['id'] : null;
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
		if ($mysqli->error) {
			$error = $mysqli->error . " | " . $query_insert;
		}

		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestión.";
		$result["result"] = $insert_id;
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}

	if ($_POST['tabla'] == "propietario") {
		$contrato_id = $_POST['contrato_id'];
		$id_persona = $_POST['id_persona'];

		$query_insert_prop = "INSERT INTO cont_propietario (
			contrato_id,
			persona_id, 
			status, 
			user_created_id, 
			created_at
		) VALUES (
			0,
			'" . $id_persona . "',
			1,
			" . $usuario_id . ",
			 now())";

		$mysqli->query($query_insert_prop);
		$insert_id = mysqli_insert_id($mysqli);
		$error = '';
		if ($mysqli->error) {
			$error .= $error = $mysqli->error;
			$result["http_code"] = 404;
			$result["status"] = "A ocurrido un error al registrar el propietario.";
			$result["result"] = 0;
			$result["error"] = $error;
			echo json_encode($result);
			exit();
		}


		$usuario_id = $login ? $login['id'] : null;
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
		'cont_propietario',
		'" . $valor_original . "',
		'contrato_id',
		'Propietario',
		'Nuevo Propietario',
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
		if ($mysqli->error) {
			$error = $mysqli->error . " | " . $query_insert;
		}

		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestión.";
		$result["result"] = $insert_id;
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_adenda") {
	$usuario_id = $login ? $login['id'] : null;
	$created_at = date("Y-m-d H:i:s");
	$tipo_contrato_id = $_POST["tipo_contrato_id"];
	$error = '';

	if ($usuario_id == null) {
		$result["status"] = 400;
		$result["message"] = 'Su sesion ha caducado, Ingrese de nuevo al sistema';
		$result["result"] = "error";
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}

	$aprobacion_obligatoria_id = $_POST["aprobacion_obligatoria_id"];
	$director_aprobacion_id = $_POST["director_aprobacion_id"];

	if ($director_aprobacion_id != 0) {
		$aprobacion_obligatoria_id = 1;
	}

	$nro_adenda = 0;
	$query_ade = "SELECT COUNT(a.id) AS total FROM cont_adendas AS a WHERE a.status = 1 AND a.cancelado_el IS NULL AND a.contrato_id = " . $_POST["contrato_id"];
	$list_count = $mysqli->query($query_ade);
	while ($row = $list_count->fetch_assoc()) {
		$nro_adenda = $row["total"];
	}
	$nro_adenda++;
	$nro_adenda = str_pad($nro_adenda, 2, "0", STR_PAD_LEFT);

	// INICIO INSERTAR EN ADENDA
	$query_insert = "	INSERT INTO cont_adendas
						(
							contrato_id,
							codigo,
							requiere_aprobacion_id,
							director_aprobacion_id,
							user_created_id,
							created_at
						)
						VALUES
						(
							" . $_POST["contrato_id"] . ",
							'" . $nro_adenda . "',
							" . $aprobacion_obligatoria_id . ",
							" . $director_aprobacion_id . ",
							" . $usuario_id . ",
							'" . $created_at . "'
						)
						";

	$mysqli->query($query_insert);
	$adenda_id = mysqli_insert_id($mysqli);

	if ($mysqli->error) {
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


	// INICIO ADENDA DETALLE
	$id_adendas = $_POST["id_adendas"];
	$data_adendas = json_decode($id_adendas);
	foreach ($data_adendas as $value_id_adenda_detalle) {
		$query_update = "
		UPDATE cont_adendas_detalle 
		SET 
			adenda_id = " . $adenda_id . ",
			user_updated_id = " . $usuario_id . ",
			updated_at = '" . $created_at . "'
		WHERE id = " . $value_id_adenda_detalle . "
		";
		$mysqli->query($query_update);
	}

	if ($mysqli->error) {
		$error .= $mysqli->error;
		$result["status"] = 400;
		$result["message"] = $error;
		$result["result"] = "error";
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}
	// FIN ADENDA DETALLE


	$path = "C:/laragon/www/contratos.kurax/files_bucket/contratos/adendas/locales/";
	// if(!file_exists($path)){
	// 	mkdir($path, 0777); //or die("Hubo un error al crear la carpeta");	
	// }
	if (!empty($_FILES)) {
		foreach ($_FILES as $nombreCampo => $infoArchivo) {
			if ($infoArchivo['error'] === UPLOAD_ERR_OK) {
				$archivo_explode = explode('_', $nombreCampo);
				$tipo_anexo_id = $archivo_explode[0];
				$contrato_detalle_id = $archivo_explode[1];

				$nombreArchivoSinExtension = pathinfo($infoArchivo['name'], PATHINFO_FILENAME);
				$filesize = $infoArchivo['size'];
				$fileExt = pathinfo($infoArchivo['name'], PATHINFO_EXTENSION);

				$nombre_archivo = $_POST["contrato_id"] . '_ANEXO_ADENDAS_' . date('YmdHis') . $fileExt;
				$dir = opendir($path);
				if (move_uploaded_file($infoArchivo['tmp_name'], $path . '/' . $nombre_archivo)) {
					$comando = "INSERT INTO cont_archivos (
						contrato_id,
						contrato_detalle_id,
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
						'" . $contrato_detalle_id . "',
						'" . $adenda_id . "',
						'" . $tipo_anexo_id . "',
						'" . $nombre_archivo . "',
						'" . $fileExt . "',
						'" . $filesize . "',
						'" . $path . "',
						" . $usuario_id . ",
						'" . $created_at . "'
						)";

					$mysqli->query($comando);
					if ($mysqli->error) {
						$error .= 'Error al guardar el nuevo anexo' . $mysqli->error . $comando;
					}
				}
				closedir($dir);
			}
		}
	}
	send_email_solicitud_adenda_contrato_arrendamiento($adenda_id, $aprobacion_obligatoria_id);


	$result["status"] = 200;
	$result["message"] = "Se ha modificado exitosamente la adenda de contrato de arrendamiento1.";
	$result["result"] = "ok";
	$result["error"] = $error;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_propietario") {
	$usuario_id = $login ? $login['id'] : null;
	$created_at = date("Y-m-d H:i:s");
	$tipo_solicitud = $_POST['tipo_solicitud'];
	if ($_POST["tipo_persona_contacto"] == 1) {
		$contacto_nombre = $_POST["nombre"];
	} else {
		$contacto_nombre = $_POST["contacto_nombre"];
	}

	if ($_POST["tipo_docu"] != 2) {
		$sql_existe = "SELECT 
		id, 
		tipo_persona_id, 
		nombre, 
		tipo_docu_identidad_id, 
		num_docu, 
		num_ruc,
		direccion, 
		representante_legal, 
		num_partida_registral, 
		contacto_nombre, 
		contacto_telefono, 
		contacto_email 
		FROM cont_persona WHERE num_docu like '" . $_POST["num_docu"] . "' AND status = 1";

		$query_existe = $mysqli->query($sql_existe);
		$row_existe = $query_existe->num_rows;
		if (($row_existe) > 0) {
			$result["http_code"] = 400;
			$result["status"] = "El Nro de Documento ya esta registrado.";
			$result["result"] = $row_existe;
			echo json_encode($result);
			exit();
		}
	} else if ($_POST["tipo_docu"] == 2) {
		$sql_existe = "SELECT 
		id, 
		tipo_persona_id, 
		nombre, 
		tipo_docu_identidad_id, 
		num_docu, 
		num_ruc,
		direccion, 
		representante_legal, 
		num_partida_registral, 
		contacto_nombre, 
		contacto_telefono, 
		contacto_email 
		FROM cont_persona WHERE num_ruc like '" . $_POST["num_ruc"] . "' AND status = 1";

		$query_existe = $mysqli->query($sql_existe);
		$row_existe = $query_existe->num_rows;
		if (($row_existe) > 0) {
			$result["http_code"] = 400;
			$result["status"] = "El RUC ya esta registrado.";
			$result["result"] = $row_existe;
			echo json_encode($result);
			exit();
		}
	}

	$query_insert = " INSERT INTO cont_persona
	(
	tipo_persona_id,
	tipo_docu_identidad_id,
	num_docu,
	num_ruc,
	nombre,
	direccion,
	representante_legal,
	num_partida_registral,
	contacto_nombre,
	contacto_telefono,
	contacto_email,
	user_created_id,
	created_at)
	VALUES
	(
	" . $_POST["tipo_persona"] . ",
	" . $_POST["tipo_docu"] . ",
	'" . $_POST["num_docu"] . "',
	'" . $_POST["num_ruc"] . "',
	'" . str_replace("'", "", trim($_POST["nombre"])) . "',
	'" . str_replace("'", "", trim($_POST["direccion"])) . "',
	'" . str_replace("'", "", trim($_POST["representante_legal"])) . "',
	'" . $_POST["num_partida_registral"] . "',
	'" . str_replace("'", "", trim($contacto_nombre)) . "',
	'" . $_POST["contacto_telefono"] . "',
	'" . $_POST["contacto_email"] . "',
	" . $usuario_id . ",
	'" . $created_at . "')";
	$mysqli->query($query_insert);
	$id_persona = mysqli_insert_id($mysqli);
	$error = '';
	if ($mysqli->error) {
		$error = $mysqli->error;
	}



	if ($tipo_solicitud == "NuevoPropietario") {

		$query_insert_prop = "INSERT INTO cont_propietario (
			contrato_id,
			persona_id, 
			status, 
			user_created_id, 
			created_at
		) VALUES (
			0,
			'" . $id_persona . "',
			1,
			" . $usuario_id . ",
			 now())";

		$mysqli->query($query_insert_prop);
		$insert_id = mysqli_insert_id($mysqli);


		$usuario_id = $login ? $login['id'] : null;
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
		'cont_propietario',
		'" . $valor_original . "',
		'contrato_id',
		'Propietario',
		'Nuevo Propietario',
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
		if ($mysqli->error) {
			$error = $mysqli->error . " | " . $query_insert;
		}

		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestión.";
		$result["result"] = $insert_id;
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}

	if ($tipo_solicitud == "CambiarPropietario") {
		$usuario_id = $login ? $login['id'] : null;
		$created_at = date("Y-m-d H:i:s");
		$valor_original = $_POST['id_persona_para_cambios'];
		$tipo_valor = 'id_tabla';

		$id_del_registro = 0;

		$valor_varchar = "NULL";
		$valor_int = $id_persona;
		$valor_date = "NULL";
		$valor_decimal = "NULL";
		$valor_select_option = "NULL";
		$valor_id_tabla = $_POST['id_propietario_para_cambios'];

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
		'cont_propietario',
		'" . $valor_original . "',
		'persona_id',
		'Propietario',
		'Cambio de Propietario',
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
		if ($mysqli->error) {
			$error = $mysqli->error . " | " . $query_insert;
		}

		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestión.";
		$result["result"] = $insert_id;
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_beneficiario") {
	$usuario_id = $login ? $login['id'] : null;
	$created_at = date("Y-m-d H:i:s");
	$tipo_solicitud = $_POST['tipo_solicitud'];
	$nombre = str_replace("'", "", trim($_POST["nombre"]));

	$id_forma_pago = $_POST["id_forma_pago"];

	if ($id_forma_pago == '3') {
		$id_banco = "NULL";
		$num_cuenta_bancaria = "NULL";
		$num_cuenta_cci = "NULL";
	} else {
		$id_banco = $_POST["id_banco"];
		$num_cuenta_bancaria = "'" . $_POST["num_cuenta_bancaria"] . "'";
		$num_cuenta_cci = "'" . $_POST["num_cuenta_cci"] . "'";
	}

	if (empty($_POST["monto"])) {
		$monto = "NULL";
	} else {
		$monto = str_replace(",", "", $_POST["monto"]);
	}

	$query_insert = "INSERT INTO cont_beneficiarios
	(
	contrato_id,
	contrato_detalle_id,
	tipo_persona_id,
	tipo_docu_identidad_id,
	num_docu,
	nombre,
	forma_pago_id,
	banco_id,
	num_cuenta_bancaria,
	num_cuenta_cci,
	tipo_monto_id,
	monto,
	status,
	user_created_id,
	created_at)
	VALUES
	(
	" . $_POST["contrato_id"] . ",
	" . $_POST["contrato_detalle_id"] . ",
	" . $_POST["tipo_persona"] . ",
	" . $_POST["tipo_docu"] . ",
	'" . $_POST["num_docu"] . "',
	'" . $nombre . "',
	" . $id_forma_pago . ",
	" . $id_banco . ",
	" . $num_cuenta_bancaria . ",
	" . $num_cuenta_cci . ",
	" . $_POST["tipo_monto"] . ",
	" . $monto . ",
	0,
	" . $usuario_id . ",
	'" . $created_at . "')";

	$mysqli->query($query_insert);
	$insert_id = mysqli_insert_id($mysqli);

	if ($tipo_solicitud == "NuevoBeneficiario") {
		$usuario_id = $login ? $login['id'] : null;
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
		contrato_detalle_id,
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
		" . $_POST['contrato_detalle_id'] . ",
		'cont_beneficiarios',
		'" . $valor_original . "',
		'status',
		'Beneficiario',
		'Nuevo Beneficiario',
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
		if ($mysqli->error) {
			$error = $mysqli->error . " | " . $query_insert;
		}

		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestión.";
		$result["result"] = $insert_id;
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}

	if ($tipo_solicitud == "CambiarBeneficiario") {

		$id_beneficiario_para_cambios = $_POST['id_beneficiario_para_cambios'];


		$usuario_id = $login ? $login['id'] : null;
		$created_at = date("Y-m-d H:i:s");
		$valor_original = $id_beneficiario_para_cambios;
		$tipo_valor = 'id_tabla';

		$id_del_registro = 0;

		$valor_varchar = "NULL";
		$valor_int = $insert_id;
		$valor_date = "NULL";
		$valor_decimal = "NULL";
		$valor_select_option = "NULL";
		$valor_id_tabla = $id_beneficiario_para_cambios;

		$query_insert = " INSERT INTO cont_adendas_detalle
		(
		contrato_detalle_id,
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
		" . $_POST['contrato_detalle_id'] . ",
		'cont_beneficiarios',
		'" . $valor_original . "',
		'contrato_id',
		'Beneficiario',
		'Cambio de Beneficiario',
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
		if ($mysqli->error) {
			$error = $mysqli->error . " | " . $query_insert;
		}

		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestión.";
		$result["result"] = $insert_id;
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_incremento_adenda") {
	$usuario_id = $login ? $login['id'] : null;
	$created_at = "'" . date("Y-m-d H:i:s") . "'";


	$sql = "SELECT c.tipo_moneda_id, c.fecha_inicio, c.contrato_id
	FROM  cont_condicion_economica AS c 
	WHERE c.contrato_detalle_id = " . $_POST['contrato_detalle_id'];
	$query = $mysqli->query($sql);
	$row_ce = $query->fetch_assoc();

	$contrato_id = $_POST["contrato_id"];
	$incremento_monto_o_porcentaje = str_replace(",", "", $_POST["incremento_monto_o_porcentaje"]);
	$incrementos_en = $_POST["incrementos_en"];
	$incrementos_continuidad = $_POST["incrementos_continuidad"];
	$contrato_detalle_id = $_POST["contrato_detalle_id"];
	$error = '';

	if (empty($_POST["incrementos_a_partir_de_año"])) {
		$incrementos_a_partir_de_año = "2";
	} else {
		$incrementos_a_partir_de_año = $_POST["incrementos_a_partir_de_año"];
	}

	$fecha_inicio = $row_ce['fecha_inicio'];
	$fecha_aplicacion = strtotime('+' . $incrementos_a_partir_de_año . ' year', strtotime($fecha_inicio));
	$fecha_aplicacion = date('Y-m-d', $fecha_aplicacion);

	$query_insert = "
	INSERT INTO cont_incrementos(
		contrato_detalle_id,
		contrato_id,
		valor,
		tipo_valor_id,
		tipo_continuidad_id,
		a_partir_del_año,
		fecha_cambio,
		estado,
		user_created_id,
		created_at
	) VALUES (
		$contrato_detalle_id,
		$contrato_id,
		$incremento_monto_o_porcentaje,
		$incrementos_en,
		$incrementos_continuidad,
		$incrementos_a_partir_de_año,
		'" . $fecha_aplicacion . "',
		0,
		$usuario_id,
		$created_at
	)";

	$mysqli->query($query_insert);
	$insert_id = mysqli_insert_id($mysqli);

	if ($mysqli->error) {
		$error = $mysqli->error . $query_insert;
	}

	if ($incrementos_en == 1) {
		$tipo_valor = ' soles o dolares (según el tipo de moneda del contrato)';
	} else if ($incrementos_en == 2) {
		$tipo_valor = '%';
		if (substr($incremento_monto_o_porcentaje, -3, 3) == ".00") {
			$incremento_monto_o_porcentaje = substr($incremento_monto_o_porcentaje, 0, -3);
		}
	}

	$a_partir_del_año = $_POST["incrementos_a_partir_de_año_text"];

	if ($incrementos_continuidad == 3) {
		$a_partir_del_año = '';
	}

	$valor_nuevo = $incremento_monto_o_porcentaje . $tipo_valor . ' ' . $_POST["incrementos_continuidad_text"] . ' ' . $a_partir_del_año;

	// $result["http_code"] = 200;
	// $result["id"] = $insert_id;
	// $result["nuevo_valor"] = $valor_nuevo;
	// $result["status"] = "Datos obtenidos de gestión.";
	// $result["error"] = $error;

	$usuario_id = $login ? $login['id'] : null;
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
		contrato_detalle_id,
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
		" . $contrato_detalle_id . ",
		'cont_incrementos',
		'" . $valor_original . "',
		'estado',
		'Incremento',
		'Nuevo Incremento',
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
	if ($mysqli->error) {
		$error = $mysqli->error . " | " . $query_insert;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $insert_id;
	$result["error"] = $error;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_inflacion") {

	$usuario_id = $login ? $login['id'] : null;
	$created_at = date('Y-m-d H:i:s');

	$sql = "SELECT c.tipo_moneda_id, c.fecha_inicio, c.contrato_id
	FROM  cont_condicion_economica AS c 
	WHERE c.contrato_detalle_id = " . $_POST['contrato_detalle_id'];
	$query = $mysqli->query($sql);
	$row_ce = $query->fetch_assoc();



	$fecha = "'" . date("Y-m-d", strtotime($_POST['fecha'])) . "'";
	$porcentaje_anadido = !empty($_POST['porcentaje_anadido']) ? $_POST['porcentaje_anadido'] : 'NULL';
	$tope_inflacion = !empty($_POST['tope_inflacion']) ? $_POST['tope_inflacion'] : 'NULL';
	$minimo_inflacion = !empty($_POST['minimo_inflacion']) ? $_POST['minimo_inflacion'] : 'NULL';
	$aplicacion_id = !empty($_POST['aplicacion_id']) ? $_POST['aplicacion_id'] : 'NULL';
	$numero = !empty($_POST['numero']) ? $_POST['numero'] : 0;
	$tipo_anio_mes = !empty($_POST['tipo_anio_mes']) ? $_POST['tipo_anio_mes'] : 0;
	$moneda_id = $row_ce['tipo_moneda_id'];

	$query_insert = " INSERT INTO cont_inflaciones
		(
			contrato_id,
			contrato_detalle_id,
			fecha,
			tipo_periodicidad_id,
			numero,
			tipo_anio_mes,
			moneda_id,
			porcentaje_anadido,
			tope_inflacion,
			minimo_inflacion,
			tipo_aplicacion_id,
			status,
			created_at,
			user_created_id)
		VALUES(
			'" . $_POST['contrato_id'] . "',
			'" . $_POST['contrato_detalle_id'] . "',
			" . $fecha . ",
			'" . $_POST['tipo_periodicidad_id'] . "',
			'" . $numero . "',
			'" . $tipo_anio_mes . "',
			'" . $moneda_id . "',
			" . $porcentaje_anadido . ",
			" . $tope_inflacion . ",
			" . $minimo_inflacion . ",
			" . $aplicacion_id . ",
			0,
			'" . $created_at . "',
			'" . $usuario_id . "')";

	$mysqli->query($query_insert);
	$insert_id = mysqli_insert_id($mysqli);
	$error = '';
	if ($mysqli->error) {
		$error = $mysqli->error . " | " . $query_insert;
		$result["http_code"] = 404;
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}

	$usuario_id = $login ? $login['id'] : null;
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
		contrato_detalle_id,
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
		'" . $_POST['contrato_detalle_id'] . "',
		'cont_inflaciones',
		'" . $valor_original . "',
		'status',
		'Inflación',
		'Nueva Inflación',
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
	if ($mysqli->error) {
		$error = $mysqli->error . " | " . $query_insert;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $insert_id;
	$result["error"] = $error;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "eliminar_inflacion") {

	$usuario_id = $login ? $login['id'] : null;
	$created_at = date('Y-m-d H:i:s');

	$query_update = " UPDATE cont_inflaciones SET 
		status = 0
	WHERE id = " . $_POST['inflacion_id'];

	$mysqli->query($query_update);
	$error = '';
	if ($mysqli->error) {
		$error = $mysqli->error . " | " . $query_update;
		$result["http_code"] = 404;
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["error"] = $error;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_cuota_extraordinaria") {

	$usuario_id = $login ? $login['id'] : null;
	$created_at = date('Y-m-d H:i:s');

	$fecha = "'" . date("Y-m-d", strtotime($_POST['fecha'])) . "'";

	$query_insert = " INSERT INTO cont_cuotas_extraordinarias
		(
			contrato_id,
			contrato_detalle_id,
			fecha,
			mes,
			multiplicador,
			status,
			created_at,
			user_created_id)
		VALUES(
			" . $_POST['contrato_id'] . ",
			" . $_POST['contrato_detalle_id'] . ",
			" . $fecha . ",
			'" . $_POST['mes'] . "',
			'" . $_POST['multiplicador'] . "',
			0,
			'" . $created_at . "',
			'" . $usuario_id . "')";

	$mysqli->query($query_insert);
	$insert_id = mysqli_insert_id($mysqli);
	$error = '';
	if ($mysqli->error) {
		$error = $mysqli->error . " | " . $query_insert;
		$result["http_code"] = 404;
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}

	$usuario_id = $login ? $login['id'] : null;
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
		contrato_detalle_id,
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
		" . $_POST['contrato_detalle_id'] . ",
		'cont_cuotas_extraordinarias',
		'" . $valor_original . "',
		'status',
		'Cuota Extraordinaria',
		'Nueva Cuota Extraordinaria',
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
	if ($mysqli->error) {
		$error = $mysqli->error . " | " . $query_insert;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $insert_id;
	$result["error"] = $error;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "eliminar_cuota_extraordinaria") {

	$query_update = " UPDATE cont_cuotas_extraordinarias SET 
		status = 0
	WHERE id = " . $_POST['cuota_extraordinaria_id'];

	$mysqli->query($query_update);
	$error = '';
	if ($mysqli->error) {
		$error = $mysqli->error . " | " . $query_update;
		$result["http_code"] = 404;
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["error"] = $error;
	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_responsable_ir") {
	$usuario_id = $login ? $login['id'] : null;
	$created_at = date('Y-m-d H:i:s');
	$tipo_solicitud = $_POST['tipo_solicitud'];
	$query_insert = " INSERT INTO cont_responsable_ir
		(
		contrato_id,
		contrato_detalle_id,
		tipo_documento_id,
		num_documento,
		nombres,
		estado_emisor,
		porcentaje,
		status,
		created_at,
		user_created_id)
		VALUES(
		" . $_POST['contrato_id'] . ",
		" . $_POST['contrato_detalle_id'] . ",
		'" . $_POST['tipo_docu'] . "',
		'" . $_POST['num_docu'] . "',
		'" . $_POST['nombre'] . "',
		0,
		'" . $_POST['num_porcentaje'] . "',
		0,
		'" . $created_at . "',
		'" . $usuario_id . "')";

	$mysqli->query($query_insert);
	$insert_id = mysqli_insert_id($mysqli);
	$error = '';
	if ($mysqli->error) {
		$error = $mysqli->error . " | " . $query_insert;
		$result["http_code"] = 404;
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}

	if ($tipo_solicitud == "NuevoResponsableIR") {
		$usuario_id = $login ? $login['id'] : null;
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
		contrato_detalle_id,
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
		" . $_POST['contrato_detalle_id'] . ",
		'cont_responsable_ir',
		'" . $valor_original . "',
		'status',
		'Responsable IR',
		'Nuevo Responsable IR',
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
		if ($mysqli->error) {
			$error = $mysqli->error . " | " . $query_insert;
		}

		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestión.";
		$result["result"] = $insert_id;
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}

	if ($tipo_solicitud == "CambiarResponsableIR") {

		$id_responsable_ir_para_cambios = $_POST['id_responsable_ir_para_cambios'];


		$usuario_id = $login ? $login['id'] : null;
		$created_at = date("Y-m-d H:i:s");
		$valor_original = $id_responsable_ir_para_cambios;
		$tipo_valor = 'id_tabla';

		$id_del_registro = 0;

		$valor_varchar = "NULL";
		$valor_int = $insert_id;
		$valor_date = "NULL";
		$valor_decimal = "NULL";
		$valor_select_option = "NULL";
		$valor_id_tabla = $id_responsable_ir_para_cambios;

		$query_insert = " INSERT INTO cont_adendas_detalle
		(
		contrato_detalle_id,
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
		" . $_POST['contrato_detalle_id'] . ",
		'cont_responsable_ir',
		'" . $valor_original . "',
		'contrato_id',
		'Responsable IR',
		'Cambio de Responsable IR',
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
		if ($mysqli->error) {
			$error = $mysqli->error . " | " . $query_insert;
		}

		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestión.";
		$result["result"] = $insert_id;
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_suministro") {
	$usuario_id = $login ? $login['id'] : null;
	$created_at = date('Y-m-d H:i:s');
	$contrato_id = $_POST['contrato_id'];
	$tipo_servicio = $_POST['tipo_servicio'];
	$inmueble_id = $_POST['inmueble_id'];
	$contrato_detalle_id = $_POST['contrato_detalle_id'];
	$nro_suministro = $_POST['nro_suministro'];
	$compromiso_pago = $_POST['compromiso_pago'];
	$monto_porcentaje = !empty($_POST['monto_porcentaje']) ? $_POST['monto_porcentaje'] : 0;

	$query_insert = " INSERT INTO cont_inmueble_suministros
		(
		contrato_id,
		inmueble_id,
		tipo_servicio_id,
		nro_suministro,
		tipo_compromiso_pago_id,
		monto_o_porcentaje,
		status,
		created_at,
		user_created_id)
		VALUES(
		" . $contrato_id . ",
		'" . $inmueble_id . "',
		'" . $tipo_servicio . "',
		'" . $nro_suministro . "',
		'" . $compromiso_pago . "',
		'" . $monto_porcentaje . "',
		0,
		'" . $created_at . "',
		'" . $usuario_id . "')";

	$mysqli->query($query_insert);
	$insert_id = mysqli_insert_id($mysqli);
	$error = '';
	if ($mysqli->error) {
		$error = $mysqli->error . " | " . $query_insert;
		$result["http_code"] = 404;
		$result["error"] = $error;
		echo json_encode($result);
		exit();
	}


	$usuario_id = $login ? $login['id'] : null;
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
		contrato_detalle_id,
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
		" . $_POST['contrato_detalle_id'] . ",
		'cont_inmueble_suministros',
		'" . $valor_original . "',
		'status',
		'Suministro',
		'Nuevo Suministro',
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
	if ($mysqli->error) {
		$error = $mysqli->error . " | " . $query_insert;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestión.";
	$result["result"] = $insert_id;
	$result["error"] = $error;
	echo json_encode($result);
	exit();
}


if (isset($_POST["accion"]) && $_POST["accion"] === "send_email_solicitud_adenda_arrendamiento") {
	$adenda_id = $_POST["adenda_id"];
	send_email_solicitud_adenda_contrato_arrendamiento($adenda_id, 2);
}
