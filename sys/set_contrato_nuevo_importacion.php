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

set_time_limit(0);


function replace_invalid_caracters($cadena) {
	$cadena = str_replace("\n", " ", $cadena);
	$cadena = str_replace("'", "", $cadena);
	$cadena = str_replace("#", "", $cadena);
	$cadena = str_replace("$", "", $cadena);
	$cadena = str_replace("%", "", $cadena);
	$cadena = str_replace("&", "", $cadena);
	$cadena = str_replace("'", "", $cadena);
	$cadena = str_replace("(", "", $cadena);
	$cadena = str_replace(")", "", $cadena);
	$cadena = str_replace("*", "", $cadena);
	$cadena = str_replace("+", "", $cadena);
	$cadena = str_replace("-", "", $cadena);
	$cadena = str_replace(".", "", $cadena);
	$cadena = str_replace("/", "", $cadena);
	$cadena = str_replace("<", "", $cadena);
	$cadena = str_replace("=", "", $cadena);
	$cadena = str_replace(">", "", $cadena);
	$cadena = str_replace("?", "", $cadena);
	$cadena = str_replace("@", "", $cadena);
	$cadena = str_replace("[", "", $cadena);
	$cadena = str_replace("\\", "", $cadena);
	$cadena = str_replace("]", "", $cadena);
	$cadena = str_replace("^", "", $cadena);
	$cadena = str_replace("_", "", $cadena);
	$cadena = str_replace("`", "", $cadena);
	$cadena = str_replace("{", "", $cadena);
	$cadena = str_replace("|", "", $cadena);
	$cadena = str_replace("}", "", $cadena);
	$cadena = str_replace("~", "", $cadena);
	$cadena = str_replace("¡", "", $cadena);
	$cadena = str_replace("¢", "", $cadena);
	$cadena = str_replace("£", "", $cadena);
	$cadena = str_replace("¤", "", $cadena);
	$cadena = str_replace("¥", "", $cadena);
	$cadena = str_replace("¦", "", $cadena);
	$cadena = str_replace("§", "", $cadena);
	$cadena = str_replace("¨", "", $cadena);
	$cadena = str_replace("©", "", $cadena);
	$cadena = str_replace("ª", "", $cadena);
	$cadena = str_replace("«", "", $cadena);
	$cadena = str_replace("¬", "", $cadena);
	$cadena = str_replace("®", "", $cadena);
	$cadena = str_replace("°", "", $cadena);
	$cadena = str_replace("±", "", $cadena);
	$cadena = str_replace("²", "", $cadena);
	$cadena = str_replace("³", "", $cadena);
	$cadena = str_replace("´", "", $cadena);
	$cadena = str_replace("µ", "", $cadena);
	$cadena = str_replace("¶", "", $cadena);
	$cadena = str_replace("·", "", $cadena);
	$cadena = str_replace("¸", "", $cadena);
	$cadena = str_replace("¹", "", $cadena);
	$cadena = str_replace("º", "", $cadena);
	$cadena = str_replace("»", "", $cadena);
	$cadena = str_replace("¼", "", $cadena);
	$cadena = str_replace("½", "", $cadena);
	$cadena = str_replace("¾", "", $cadena);
	$cadena = str_replace("¿", "", $cadena);
	$cadena = str_replace("À", "A", $cadena);
	$cadena = str_replace("Á", "A", $cadena);
	$cadena = str_replace("Â", "A", $cadena);
	$cadena = str_replace("Ã", "A", $cadena);
	$cadena = str_replace("Ä", "A", $cadena);
	$cadena = str_replace("Å", "A", $cadena);
	$cadena = str_replace("Æ", "", $cadena);
	$cadena = str_replace("Ç", "", $cadena);
	$cadena = str_replace("È", "E", $cadena);
	$cadena = str_replace("É", "E", $cadena);
	$cadena = str_replace("Ê", "E", $cadena);
	$cadena = str_replace("Ë", "E", $cadena);
	$cadena = str_replace("Ì", "I", $cadena);
	$cadena = str_replace("Í", "I", $cadena);
	$cadena = str_replace("Î", "I", $cadena);
	$cadena = str_replace("Ï", "I", $cadena);
	$cadena = str_replace("Ð", "", $cadena);
	$cadena = str_replace("Ñ", "N", $cadena);
	$cadena = str_replace("Ò", "O", $cadena);
	$cadena = str_replace("Ó", "O", $cadena);
	$cadena = str_replace("Ô", "O", $cadena);
	$cadena = str_replace("Õ", "O", $cadena);
	$cadena = str_replace("Ö", "O", $cadena);
	$cadena = str_replace("×", "", $cadena);
	$cadena = str_replace("Ø", "", $cadena);
	$cadena = str_replace("Ù", "U", $cadena);
	$cadena = str_replace("Ú", "U", $cadena);
	$cadena = str_replace("Û", "U", $cadena);
	$cadena = str_replace("Ü", "U", $cadena);
	$cadena = str_replace("Ý", "Y", $cadena);
	$cadena = str_replace("Þ", "", $cadena);
	$cadena = str_replace("ß", "", $cadena);
	$cadena = str_replace("à", "a", $cadena);
	$cadena = str_replace("á", "a", $cadena);
	$cadena = str_replace("â", "a", $cadena);
	$cadena = str_replace("ã", "a", $cadena);
	$cadena = str_replace("ä", "a", $cadena);
	$cadena = str_replace("å", "a", $cadena);
	$cadena = str_replace("æ", "", $cadena);
	$cadena = str_replace("ç", "", $cadena);
	$cadena = str_replace("è", "e", $cadena);
	$cadena = str_replace("é", "e", $cadena);
	$cadena = str_replace("ê", "e", $cadena);
	$cadena = str_replace("ë", "e", $cadena);
	$cadena = str_replace("ì", "i", $cadena);
	$cadena = str_replace("í", "i", $cadena);
	$cadena = str_replace("î", "i", $cadena);
	$cadena = str_replace("ï", "i", $cadena);
	$cadena = str_replace("ð", "o", $cadena);
	$cadena = str_replace("ñ", "n", $cadena);
	$cadena = str_replace("ò", "o", $cadena);
	$cadena = str_replace("ó", "o", $cadena);
	$cadena = str_replace("ô", "o", $cadena);
	$cadena = str_replace("õ", "o", $cadena);
	$cadena = str_replace("ö", "o", $cadena);
	$cadena = str_replace("÷", "", $cadena);
	$cadena = str_replace("ø", "", $cadena);
	$cadena = str_replace("ù", "u", $cadena);
	$cadena = str_replace("ú", "u", $cadena);
	$cadena = str_replace("û", "u", $cadena);
	$cadena = str_replace("ü", "u", $cadena);
	$cadena = str_replace("ý", "y", $cadena);
	$cadena = str_replace("þ", "", $cadena);
	$cadena = str_replace("ÿ", "", $cadena);
	$cadena = str_replace("Œ", "", $cadena);
	$cadena = str_replace("œ", "", $cadena);
	$cadena = str_replace("Š", "", $cadena);
	$cadena = str_replace("š", "", $cadena);
	$cadena = str_replace("Ÿ", "", $cadena);
	$cadena = str_replace("ƒ", "", $cadena);
	$cadena = str_replace("–", "", $cadena);
	$cadena = str_replace("—", "", $cadena);
	$cadena = str_replace("'", "", $cadena);
	$cadena = str_replace("'", "", $cadena);
	$cadena = str_replace("‚", "", $cadena);
	$cadena = str_replace('"', "", $cadena);
	$cadena = str_replace('"', "", $cadena);
	$cadena = str_replace("„", "", $cadena);
	$cadena = str_replace("†", "", $cadena);
	$cadena = str_replace("‡", "", $cadena);
	$cadena = str_replace("•", "", $cadena);
	$cadena = str_replace("…", "", $cadena);
	$cadena = str_replace("‰", "", $cadena);
	$cadena = str_replace("€", "", $cadena);
	$cadena = str_replace("™", "", $cadena);

	return $cadena;
}



if (isset($_POST["accion"]) && $_POST["accion"]==="contrato_arrendamiento") {
		
	$cont_user_created_id = 4014;
	$cont_created_at = date('Y-m-d H:i:s');

	$data = json_decode(file_get_contents("../files_bucket/migracion/data-arrendamiento-v4.json"), true);

	$data_contratos = $data['Contratos_de_Arrendamiento'];
	$data_propietarios = $data['Propietarios'];
	$data_incrementos = $data['Incrementos'];
	$data_beneficiaros = $data['Beneficiarios'];
	
	///IMPORTACION CONTRATOS
	$error = '';
	$contrato_id = '';
	$data = $data_contratos;
	for ($i=0; $i < count($data); $i++) { 

		// contrato
		$cont_tipo_contrato_id = 1;
		$cont_area_responsable_id = !isset($data[$i]['cont_area_responsable_id']) ? '21':$data[$i]['cont_area_responsable_id'];

		$cont_cc_id = !isset($data[$i]['cont_cc_id']) ? '':$data[$i]['cont_cc_id'];
		$cont_empresa_suscribe_id = !isset($data[$i]['cont_empresa_suscribe_id']) ? '':$data[$i]['cont_empresa_suscribe_id'];
		$cont_persona_responsable_id = !isset($data[$i]['cont_persona_responsable_id']) ? '':$data[$i]['cont_persona_responsable_id'];
		$cont_nombre_tienda = !isset($data[$i]['cont_nombre_tienda']) ? '':$data[$i]['cont_nombre_tienda'];
		$cont_observaciones = !isset($data[$i]['cont_observaciones']) ? '':$data[$i]['cont_observaciones'];
		$cont_etapa_id = !isset($data[$i]['cont_etapa_id']) ? '5':$data[$i]['cont_etapa_id'];
		$cont_jefe_comercial_id = !isset($data[$i]['cont_jefe_comercial_id']) ? '':$data[$i]['cont_jefe_comercial_id'];
		$cont_status = 1;

		if (!Empty($cont_empresa_suscribe_id)) {
			$cont_empresa_suscribe_id = explode("|", $cont_empresa_suscribe_id);
			$cont_empresa_suscribe_id = trim($cont_empresa_suscribe_id[0]);
		}
		if (!Empty($cont_area_responsable_id)) {
			$cont_area_responsable_id = explode("|", $cont_area_responsable_id);
			$cont_area_responsable_id = trim($cont_area_responsable_id[0]);
		}
		if (!Empty($cont_persona_responsable_id)) {
			$cont_persona_responsable_id = explode("|", $cont_persona_responsable_id);
			$cont_persona_responsable_id = trim($cont_persona_responsable_id[0]);
			$cont_persona_responsable_id = trim($cont_persona_responsable_id) == "No menciona" ? '4015':$cont_persona_responsable_id;
		}
		if (!Empty($cont_etapa_id)) {
			$cont_etapa_id = explode("|", $cont_etapa_id);
			$cont_etapa_id = trim($cont_etapa_id[0]);
		}
		if (!Empty($cont_jefe_comercial_id)) {
			$cont_jefe_comercial_id = explode("|", $cont_jefe_comercial_id);
			$cont_jefe_comercial_id = trim($cont_jefe_comercial_id[0]);
			$cont_jefe_comercial_id = trim($cont_jefe_comercial_id) == "No menciona" ? '4014':$cont_jefe_comercial_id;
		}

		//inmuebles
		$inm_ubigeo_id = !isset($data[$i]['inm_ubigeo_id']) ? '':$data[$i]['inm_ubigeo_id'];
		$inm_ubicacion = !isset($data[$i]['inm_ubicacion']) ? '':$data[$i]['inm_ubicacion'];
		$inm_area_arrendada = !isset($data[$i]['inm_area_arrendada']) ? '':$data[$i]['inm_area_arrendada'];
		$inm_num_partida_registral = !isset($data[$i]['inm_num_partida_registral']) ? '':$data[$i]['inm_num_partida_registral'];
		$inm_oficina_registral = !isset($data[$i]['inm_oficina_registral']) ? '':$data[$i]['inm_oficina_registral'];
		$inm_id_empresa_servicio_agua = !isset($data[$i]['inm_id_empresa_servicio_agua']) ? '':$data[$i]['inm_id_empresa_servicio_agua'];
		$inm_tipo_compromiso_pago_agua = !isset($data[$i]['inm_tipo_compromiso_pago_agua']) ? '':$data[$i]['inm_tipo_compromiso_pago_agua'];
		$inm_monto_o_porcentaje_agua = !isset($data[$i]['inm_monto_o_porcentaje_agua']) ? 0:$data[$i]['inm_monto_o_porcentaje_agua'];
		$inm_num_suministro_agua = !isset($data[$i]['inm_num_suministro_agua']) ? '':$data[$i]['inm_num_suministro_agua'];
		$inm_id_empresa_servicio_luz = !isset($data[$i]['inm_id_empresa_servicio_luz']) ? '':$data[$i]['inm_id_empresa_servicio_luz'];
		$inm_tipo_compromiso_pago_luz = !isset($data[$i]['inm_tipo_compromiso_pago_luz']) ? '':$data[$i]['inm_tipo_compromiso_pago_luz'];
		$inm_monto_o_porcentaje_luz = !isset($data[$i]['inm_monto_o_porcentaje_luz']) ? 0:$data[$i]['inm_monto_o_porcentaje_luz'];
		$inm_num_suministro_luz = !isset($data[$i]['inm_num_suministro_luz']) ? '':$data[$i]['inm_num_suministro_luz'];
		$inm_tipo_compromiso_pago_arbitrios = !isset($data[$i]['inm_tipo_compromiso_pago_arbitrios']) ? '':$data[$i]['inm_tipo_compromiso_pago_arbitrios'];
		$inm_porcentaje_pago_arbitrios = !isset($data[$i]['inm_porcentaje_pago_arbitrios']) ? 0:$data[$i]['inm_porcentaje_pago_arbitrios'];
		$inm_latitud = !isset($data[$i]['inm_latitud']) ? '':$data[$i]['inm_latitud'];
		$inm_longitud = !isset($data[$i]['inm_longitud']) ? '':$data[$i]['inm_longitud'];

		if (!Empty($inm_id_empresa_servicio_agua)) {
			$inm_id_empresa_servicio_agua = explode("|", $inm_id_empresa_servicio_agua);
			$inm_id_empresa_servicio_agua = trim($inm_id_empresa_servicio_agua[0]);
		}
		if (!Empty($inm_tipo_compromiso_pago_agua)) {
			$inm_tipo_compromiso_pago_agua = explode("|", $inm_tipo_compromiso_pago_agua);
			$inm_tipo_compromiso_pago_agua = trim($inm_tipo_compromiso_pago_agua[0]);
		}
		if (!Empty($inm_id_empresa_servicio_luz)) {
			$inm_id_empresa_servicio_luz = explode("|", $inm_id_empresa_servicio_luz);
			$inm_id_empresa_servicio_luz = trim($inm_id_empresa_servicio_luz[0]);
		}
		if (!Empty($inm_tipo_compromiso_pago_luz)) {
			$inm_tipo_compromiso_pago_luz = explode("|", $inm_tipo_compromiso_pago_luz);
			$inm_tipo_compromiso_pago_luz = trim($inm_tipo_compromiso_pago_luz[0]);
		}
		if (!Empty($inm_tipo_compromiso_pago_arbitrios)) {
			$inm_tipo_compromiso_pago_arbitrios = explode("|", $inm_tipo_compromiso_pago_arbitrios);
			$inm_tipo_compromiso_pago_arbitrios = trim($inm_tipo_compromiso_pago_arbitrios[0]);
		}
		
		if (!Empty($inm_area_arrendada)) {
			$inm_area_arrendada = trim($inm_area_arrendada) == "No menciona" ? 0 : $inm_area_arrendada;
		}

		if (!Empty($inm_monto_o_porcentaje_agua)) {
			$inm_monto_o_porcentaje_agua = trim($inm_monto_o_porcentaje_agua) == "Por consumo" || trim($inm_monto_o_porcentaje_agua) == "Por determinar" ? 'NULL' : $inm_monto_o_porcentaje_agua;
		}
		
		if (!Empty($inm_monto_o_porcentaje_luz)) {
			$inm_monto_o_porcentaje_luz = trim($inm_monto_o_porcentaje_luz) == "Por consumo" || trim($inm_monto_o_porcentaje_luz) == "Por determinar" ? 'NULL' : $inm_monto_o_porcentaje_luz;
		}

		if (!Empty($inm_porcentaje_pago_arbitrios)) {
			$inm_porcentaje_pago_arbitrios = trim($inm_porcentaje_pago_arbitrios) == "Por determinar" ? 'NULL' : $inm_porcentaje_pago_arbitrios;
		}

		//condiciones economicas
		$eco_tipo_moneda_id = !isset($data[$i]['eco_tipo_moneda_id']) ? '':$data[$i]['eco_tipo_moneda_id'];
		$eco_monto_renta = !isset($data[$i]['eco_monto_renta']) ? 0:$data[$i]['eco_monto_renta'];
		$eco_garantia_monto = !isset($data[$i]['eco_garantia_monto']) ? 0:$data[$i]['eco_garantia_monto'];
		$eco_tipo_adelanto_id = !isset($data[$i]['eco_tipo_adelanto_id']) ? '':$data[$i]['eco_tipo_adelanto_id'];
		$ade_1 = !isset($data[$i]['ade_1']) ? '':$data[$i]['ade_1'];
		$ade_2 = !isset($data[$i]['ade_2']) ? '':$data[$i]['ade_2'];
		$ade_3 = !isset($data[$i]['ade_3']) ? '':$data[$i]['ade_3'];
		$ade_4 = !isset($data[$i]['ade_4']) ? '':$data[$i]['ade_4'];
		$eco_cant_meses_contrato = !isset($data[$i]['eco_cant_meses_contrato']) ? 0:$data[$i]['eco_cant_meses_contrato'];
		$eco_fecha_inicio = !isset($data[$i]['eco_fecha_inicio']) ? '':$data[$i]['eco_fecha_inicio'];
		$eco_fecha_fin = !isset($data[$i]['eco_fecha_fin']) ? '':$data[$i]['eco_fecha_fin'];
		$eco_fecha_suscripcion = !isset($data[$i]['eco_fecha_suscripcion']) ? 2256:$data[$i]['eco_fecha_suscripcion'];
		$usuario_contrato_aprobado_id = !isset($data[$i]['usuario_contrato_aprobado_id']) ? '':$data[$i]['usuario_contrato_aprobado_id'];
		$eco_impuesto_a_la_renta_id = !isset($data[$i]['eco_impuesto_a_la_renta_id']) ? 0:$data[$i]['eco_impuesto_a_la_renta_id'];
		$eco_carta_de_instruccion_id = !isset($data[$i]['eco_carta_de_instruccion_id']) ? '':$data[$i]['eco_carta_de_instruccion_id'];
		$eco_periodo_gracia_id = !isset($data[$i]['eco_periodo_gracia_id']) ? '':$data[$i]['eco_periodo_gracia_id'];
		$eco_periodo_gracia_numero = !isset($data[$i]['eco_periodo_gracia_numero']) ? 0:$data[$i]['eco_periodo_gracia_numero'];
		$eco_tipo_incremento_id = !isset($data[$i]['eco_tipo_incremento_id']) ? '':$data[$i]['eco_tipo_incremento_id'];

		$eco_periodo_gracia_inicio = !isset($data[$i]['eco_periodo_gracia_inicio']) ? '':$data[$i]['eco_periodo_gracia_inicio'];
		$eco_periodo_gracia_fin = !isset($data[$i]['eco_periodo_gracia_fin']) ? '':$data[$i]['eco_periodo_gracia_fin'];
		$eco_dia_de_pago_id = !isset($data[$i]['eco_dia_de_pago_id']) ? '':$data[$i]['eco_dia_de_pago_id'];

		$observacion = !isset($data[$i]['Column47']) ? '':$data[$i]['Column47'];

		$cont_observaciones .= !Empty($cont_observaciones) ? " ".$observacion:$observacion;
		
		////
		if (!Empty($eco_tipo_moneda_id)) {
			$eco_tipo_moneda_id = explode("|", $eco_tipo_moneda_id);
			$eco_tipo_moneda_id = trim($eco_tipo_moneda_id[0]);
		}
		if (!Empty($eco_tipo_adelanto_id)) {
			$eco_tipo_adelanto_id = explode("|", $eco_tipo_adelanto_id);
			$eco_tipo_adelanto_id = trim($eco_tipo_adelanto_id[0]);
		}
		if (!Empty($eco_impuesto_a_la_renta_id)) {
			$eco_impuesto_a_la_renta_id = explode("|", $eco_impuesto_a_la_renta_id);
			$eco_impuesto_a_la_renta_id = trim($eco_impuesto_a_la_renta_id[0]);
		}
		if (!Empty($eco_carta_de_instruccion_id)) {
			$eco_carta_de_instruccion_id = explode("|", $eco_carta_de_instruccion_id);
			$eco_carta_de_instruccion_id = trim($eco_carta_de_instruccion_id[0]);
		}
		if (!Empty($eco_periodo_gracia_id)) {
			$eco_periodo_gracia_id = explode("|", $eco_periodo_gracia_id);
			$eco_periodo_gracia_id = trim($eco_periodo_gracia_id[0]);
			if($eco_periodo_gracia_id == 2){
				$eco_periodo_gracia_inicio = '';
				$eco_periodo_gracia_fin = '';
			}
		}
		if (!Empty($eco_tipo_incremento_id)) {
			$eco_tipo_incremento_id = explode("|", $eco_tipo_incremento_id);
			$eco_tipo_incremento_id = trim($eco_tipo_incremento_id[0]);
		}
		if (!Empty($eco_dia_de_pago_id)) {
			$eco_dia_de_pago_id = explode("|", $eco_dia_de_pago_id);
			$eco_dia_de_pago_id = trim($eco_dia_de_pago_id[0]);
			$eco_dia_de_pago_id = $eco_dia_de_pago_id == "No menciona" ? 'NULL':$eco_dia_de_pago_id;
		}
		
		
		$usuario_contrato_aprobado_id = 4016;
		
		if (!Empty($eco_periodo_gracia_numero)) {
			$eco_periodo_gracia_numero = $eco_periodo_gracia_numero == "No precisa" ? 'NULL':$eco_periodo_gracia_numero;
		}
		if (!Empty($eco_garantia_monto)) {
			$eco_garantia_monto = $eco_garantia_monto == "No menciona" ? 0:$eco_garantia_monto;
		}
		
		if (!Empty($ade_1)) {
			$ade_1 = explode("|", $ade_1);
			$ade_1 = trim($ade_1[0]);
		}
		if (!Empty($ade_2)) {
			$ade_2 = explode("|", $ade_2);
			$ade_2 = trim($ade_2[0]);
		}
		if (!Empty($ade_3)) {
			$ade_3 = explode("|", $ade_3);
			$ade_3 = trim($ade_3[0]);
		}
		if (!Empty($ade_4)) {
			$ade_4 = explode("|", $ade_4);
			$ade_4 = trim($ade_4[0]);
		}




		$eco_fecha_inicio = !Empty($eco_fecha_inicio) ? str_replace("\/", "/", $eco_fecha_inicio):'';
		$eco_fecha_fin = !Empty($eco_fecha_fin) ? str_replace("\/", "/", $eco_fecha_fin):'';
		$eco_fecha_suscripcion = !Empty($eco_fecha_suscripcion) ? str_replace("\/", "/", $eco_fecha_suscripcion):'';
		$eco_periodo_gracia_inicio = !Empty($eco_periodo_gracia_inicio) ? str_replace("\/", "/", $eco_periodo_gracia_inicio):'';
		$eco_periodo_gracia_fin = !Empty($eco_periodo_gracia_fin) ? str_replace("\/", "/", $eco_periodo_gracia_fin):'';

		$eco_fecha_inicio = !Empty($eco_fecha_inicio) ? "'".date("Y-m-d", strtotime($eco_fecha_inicio))."'":"NULL";
		$eco_fecha_fin = !Empty($eco_fecha_fin) ? "'".date("Y-m-d", strtotime($eco_fecha_fin))."'":"NULL";
		$eco_fecha_suscripcion = !Empty($eco_fecha_suscripcion) ? "'".date("Y-m-d", strtotime($eco_fecha_suscripcion))."'":"NULL";
		$eco_periodo_gracia_inicio = !Empty($eco_periodo_gracia_inicio) ? "'".date("Y-m-d", strtotime($eco_periodo_gracia_inicio))."'":"NULL";
		$eco_periodo_gracia_fin = !Empty($eco_periodo_gracia_fin) ? "'".date("Y-m-d", strtotime($eco_periodo_gracia_fin))."'":"NULL";

		// echo "<br>";
		// echo "cont_cc_id :".$cont_cc_id."<br>";
		// echo "eco_fecha_inicio :".$eco_fecha_inicio."<br>";
		// echo "eco_fecha_fin :".$eco_fecha_fin."<br>";
		// echo "eco_fecha_suscripcion :".$eco_fecha_suscripcion."<br>";
		// echo "eco_periodo_gracia_inicio :".$eco_periodo_gracia_inicio."<br>";
		// echo "eco_periodo_gracia_fin :".$eco_periodo_gracia_fin."<br>";


		$error = "";
		if (!Empty($cont_cc_id)) {
			
			$query_update = "UPDATE cont_correlativo SET numero = numero + 1 WHERE tipo_contrato = 1 AND status = 1 ";

			$mysqli->query($query_update);
		
			if($mysqli->error)
			{
				$error .= $mysqli->error;
				$result["status"] = 404;
				$result["message"] = 'correlativo';
				$result["error"] = $error;
				$result["query"] = $query_update;
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
					WHERE tipo_contrato = 1 AND status = 1 LIMIT 1
				";
		
				$list_query = $mysqli->query($select_correlativo);
		
				while($sel = $list_query->fetch_assoc())
				{
					$sigla = $sel["sigla"];
					$numero_correlativo = $sel["numero"];
				}
		
				// INICIO INSERTAR EN CONTRATO
				$query_insert = " INSERT INTO cont_contrato(
				tipo_contrato_id,
				codigo_correlativo,
				empresa_suscribe_id,
				area_responsable_id,
				persona_responsable_id,
				observaciones,
				tipo_inflacion_id,
				tipo_cuota_extraordinaria_id,
				nombre_tienda,
				jefe_comercial_id,
				cc_id,
				status,
				etapa_id,
				user_created_id,
				created_at)
				VALUES(
				" . $cont_tipo_contrato_id . ",
				" . $numero_correlativo . ",
				'" . $cont_empresa_suscribe_id . "',
				'" . $cont_area_responsable_id . "',
				'" . $cont_persona_responsable_id . "',
				'" . replace_invalid_caracters($cont_observaciones) . "',
				2,
				2,
				'" . $cont_nombre_tienda . "',
				'" . $cont_jefe_comercial_id . "',
				'" . $cont_cc_id . "',
				1,
				'". $cont_etapa_id. "',
				" . $cont_user_created_id . ",
				'" . $cont_created_at . "')";
		
				$mysqli->query($query_insert);
				$contrato_id = mysqli_insert_id($mysqli);
		
				if($mysqli->error){
					$error .= $mysqli->error;
					$result["status"] = 404;
					$result["message"] = 'insert contrato';
					$result["error"] = $error;
					$result["query"] = $query_insert;
					echo json_encode($result);
					exit();
				}
				// FIN INSERTAR EN CONTRATO
		
		
		
				// // INICIO GUARDAR INMUEBLE
				$query_insert = " INSERT INTO cont_inmueble
				(
				contrato_id,
				ubigeo_id,
				ubicacion,
				area_arrendada,
				num_partida_registral,
				oficina_registral,
				id_empresa_servicio_agua,
				num_suministro_agua,
				tipo_compromiso_pago_agua,
				monto_o_porcentaje_agua,
				id_empresa_servicio_luz,
				num_suministro_luz,
				tipo_compromiso_pago_luz,
				monto_o_porcentaje_luz,
				tipo_compromiso_pago_arbitrios,
				porcentaje_pago_arbitrios,
				latitud,
				longitud,
				user_created_id,
				created_at)
				VALUES
				(
				" . $contrato_id . ",
				'".trim($inm_ubigeo_id)."',
				'" . replace_invalid_caracters($inm_ubicacion) . "',
				'" . $inm_area_arrendada . "',
				'" . substr(replace_invalid_caracters($inm_num_partida_registral),0,11). "',
				'" . str_replace("'", "",trim($inm_oficina_registral)) . "',
				'" . $inm_id_empresa_servicio_agua . "',
				'" . substr($inm_num_suministro_agua,0,20). "',
				'" . $inm_tipo_compromiso_pago_agua . "',
				" . $inm_monto_o_porcentaje_agua. ",
				'" . $inm_id_empresa_servicio_luz. "',
				'" . substr($inm_num_suministro_luz,0,20). "',
				'" . $inm_tipo_compromiso_pago_luz . "',
				" . $inm_monto_o_porcentaje_luz . ",
				'" . $inm_tipo_compromiso_pago_arbitrios . "',
				" . $inm_porcentaje_pago_arbitrios . ",
				'" . $inm_latitud . "',
				'" . $inm_longitud. "',
				" . $cont_user_created_id . ",
				'" . $cont_created_at . "'
				)";
		
		
				$mysqli->query($query_insert);
				$insert_id = mysqli_insert_id($mysqli);
				if($mysqli->error){
					$error .= $mysqli->error;

					$result["status"] = 404;
					$result["message"] = 'insert inmueble';
					$result["error"] = $error;
					$result["query"] = $query_insert;
					echo json_encode($result);
					exit();
				}
				// // FIN GUARDAR INMUEBLE
		
		
				// // INICIO INSERTAR EN CONDICIONES DE CONTRATO
				$query_insert = "INSERT INTO cont_condicion_economica(
				contrato_id,
				monto_renta, 
				tipo_moneda_id,
				impuesto_a_la_renta_id,
				carta_de_instruccion_id,
				numero_cuenta_detraccion,
				periodo_gracia_id,
				periodo_gracia_numero,
				periodo_gracia_inicio,
				periodo_gracia_fin,
				dia_de_pago_id,
				garantia_monto,
				tipo_adelanto_id,
				cant_meses_contrato,
				fecha_inicio,
				fecha_fin,
				usuario_contrato_aprobado_id,
				tipo_incremento_id,
				fecha_suscripcion,
				status,
				user_created_id,
				created_at,

				pago_renta_id,
				cuota_variable,
				tipo_venta_id,
				afectacion_igv_id,
				plazo_id

				)
				VALUES
				(
				" . $contrato_id . ",
				'" . $eco_monto_renta . "',
				'" . $eco_tipo_moneda_id . "',
				" . $eco_impuesto_a_la_renta_id . ",
				'" . $eco_carta_de_instruccion_id . "',
				'',
				'" . $eco_periodo_gracia_id . "',
				" . $eco_periodo_gracia_numero . ",
				" . $eco_periodo_gracia_inicio . ",
				" . $eco_periodo_gracia_fin . ",
				" . $eco_dia_de_pago_id . ",
				
				" . $eco_garantia_monto . ",
				'" . $eco_tipo_adelanto_id . "',
				'" . $eco_cant_meses_contrato . "',
				" . $eco_fecha_inicio . ",
				" . $eco_fecha_fin . ",
				".$usuario_contrato_aprobado_id.",
				'" . $eco_tipo_incremento_id . "',
				" . $eco_fecha_suscripcion . ",
				1,
				'" . $cont_user_created_id . "',
				'" . $cont_created_at . "',
				1,
				0,
				1,
				NULL,
				1

				)";
		
				$mysqli->query($query_insert);
		
				if($mysqli->error){
					$error .= $mysqli->error;
					$result["status"] = 404;
					$result["message"] = 'insert condicion economica';
					$result["error"] = $error;
					$result["query"] = $query_insert;
					echo json_encode($result);
					exit();
				}
				// // FIN INSERTAR EN CONDICIONES DE CONTRATO
		
				// // INICIO ADELANTOS
				if ($eco_tipo_adelanto_id == '1') {
					if (!Empty($ade_1)) {
						$query_insert = "INSERT INTO cont_adelantos (
							contrato_id,num_periodo,status,user_created_id,created_at
						) VALUES (
							".$contrato_id.",
							'".$ade_1."',
							1,
							" . $cont_user_created_id . ",
							'" . $cont_created_at . "'
						)";
						$mysqli->query($query_insert);
					}	
					if (!Empty($ade_2)) {
						$query_insert = "INSERT INTO cont_adelantos (
							contrato_id,num_periodo,status,user_created_id,created_at
						) VALUES (
							".$contrato_id.",
							'".$ade_2."',
							1,
							" . $cont_user_created_id . ",
							'" . $cont_created_at . "'
						)";
						$mysqli->query($query_insert);
					}	
					if (!Empty($ade_3)) {
						$query_insert = "INSERT INTO cont_adelantos (
							contrato_id,num_periodo,status,user_created_id,created_at
						) VALUES (
							".$contrato_id.",
							'".$ade_3."',
							1,
							" . $cont_user_created_id . ",
							'" . $cont_created_at . "'
						)";
						$mysqli->query($query_insert);
					}	
					if (!Empty($ade_4)) {
						$query_insert = "INSERT INTO cont_adelantos (
							contrato_id,num_periodo,status,user_created_id,created_at
						) VALUES (
							".$contrato_id.",
							'".$ade_4."',
							1,
							" . $cont_user_created_id . ",
							'" . $cont_created_at . "'
						)";
						$mysqli->query($query_insert);
					}	
				}
				
				// // FIN ADELANTOS
			}
			
		}
	}


	echo "STATUS CONTRATOS";
	echo "<br>";
	$result["status"] = 200;
	$result["contrato_id"] = $contrato_id;
	$result["error"] = $error;
	echo json_encode($result);
	echo "<br>";
	echo "----------------------------";
	echo "<br>";

	echo "STATUS PROPIETARIOS";
	echo "<br>";
	$data_error = [];
	$error = '';
	$contrato_id = '';
	$data = $data_propietarios;

	for ($i=0; $i < count($data); $i++) {
		$contrato_id = "";
		$cc_id = !isset($data[$i]['cc_id']) ? '':$data[$i]['cc_id'];
		$tipo_persona_id = !isset($data[$i]['tipo_persona_id']) ? '':$data[$i]['tipo_persona_id'];
		$tipo_docu_identidad_id = !isset($data[$i]['tipo_docu_identidad_id']) ? '':$data[$i]['tipo_docu_identidad_id'];
		$num_docu = !isset($data[$i]['num_docu']) ? '':$data[$i]['num_docu'];
		$num_ruc = !isset($data[$i]['num_ruc']) ? '':$data[$i]['num_ruc'];
		$nombre = !isset($data[$i]['nombre']) ? '':$data[$i]['nombre'];
		$direccion = !isset($data[$i]['direccion']) ? '':$data[$i]['direccion'];
		$representante_legal = !isset($data[$i]['representante_legal']) ? '':$data[$i]['representante_legal'];
		$num_partida_registral = !isset($data[$i]['num_partida_registral']) ? '':$data[$i]['num_partida_registral'];
		$contacto_nombre = !isset($data[$i]['contacto_nombre']) ? '':$data[$i]['contacto_nombre'];
		$contacto_telefono = !isset($data[$i]['contacto_telefono']) ? '':$data[$i]['contacto_telefono'];
		$contacto_email = !isset($data[$i]['contacto_email']) ? '':$data[$i]['contacto_email'];

		if (!Empty($tipo_persona_id)) {
			$tipo_persona_id = explode("|", $tipo_persona_id);
			$tipo_persona_id = trim($tipo_persona_id[0]);
		}
		if (!Empty($tipo_docu_identidad_id)) {
			$tipo_docu_identidad_id = explode("|", $tipo_docu_identidad_id);
			$tipo_docu_identidad_id = trim($tipo_docu_identidad_id[0]);
		}

		if ($tipo_persona_id == 1) {
			$contacto_nombre = $nombre;
		} else {
			$contacto_nombre = $contacto_nombre;
		}

		if (!Empty($num_docu)) {
			$num_docu = trim($num_docu) == "No menciona" ? "":$num_docu;
		}
		if (!Empty($num_ruc)) {
			$num_ruc = trim($num_ruc) == "No menciona" ? "":$num_ruc;
		}
		if (!Empty($representante_legal)) {
			$representante_legal = trim($representante_legal) == "No aplica" ? "":$representante_legal;
		}
		if (!Empty($contacto_telefono)) {
			$contacto_telefono = trim($contacto_telefono) == "No menciona" ? "":$contacto_telefono;
		}
		if (!Empty($contacto_email)) {
			$contacto_email = trim($contacto_email) == "sincorreo@sincorreo.com" ? "":$contacto_email;
		}
		

		$query = "SELECT contrato_id FROM cont_contrato WHERE status = 1 AND cc_id = '".$cc_id."'";
		$list_query = $mysqli->query($query);
		while ($li = $list_query->fetch_assoc()) {
			$contrato_id = $li['contrato_id'];
		}

		if (!Empty($contrato_id)) {
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
				" . $tipo_persona_id . ",
				" . $tipo_docu_identidad_id. ",
				'" . $num_docu . "',
				'" . $num_ruc . "',
				'" . replace_invalid_caracters(trim($nombre)). "',
				'" . replace_invalid_caracters(trim($direccion)). "',
				'" . replace_invalid_caracters(trim($representante_legal)). "',
				'" . replace_invalid_caracters($num_partida_registral) . "',
				'" . replace_invalid_caracters(trim($contacto_nombre)). "',
				'" . $contacto_telefono . "',
				'" . $contacto_email . "',
				" . $cont_user_created_id . ",
				'" . $cont_created_at . "')";
				$mysqli->query($query_insert);
				$persona_id = mysqli_insert_id($mysqli);
				$error = '';
				if($mysqli->error){
					$error .= $mysqli->error;
					$result["status"] = 404;
					$result["message"] = 'insert persona';
					$result["error"] = $error;
					$result["query"] = $query_insert;
					echo json_encode($result);
					exit();
				}

				$query_insert = "INSERT INTO cont_propietario(
					contrato_id,
					persona_id,
					user_created_id)
					VALUES(
					" . $contrato_id . ",
					" . $persona_id . ",
					" . $cont_user_created_id . ")";

				$mysqli->query($query_insert);

				if($mysqli->error){
					$error .= $mysqli->error;
					$result["status"] = 404;
					$result["message"] = 'insert propietario';
					$result["error"] = $error;
					$result["query"] = $query_insert;
					echo json_encode($result);
					exit();
				}
		}else{
			array_push($data_error,$cc_id);
		}

		
	}

	$result["status"] = 200;
	$result["data_error"] = $data_error;
	$result["error"] = $error;
	echo json_encode($result);
	echo "<br>";
	echo "----------------------------";
	echo "<br>";

	

	echo "STATUS INCREMENTOS";
	echo "<br>";
	$data_error = [];
	$error = '';
	$contrato_id = '';
	$data = $data_incrementos;
	for ($i=0; $i < count($data); $i++) {
		$contrato_id = '';
		$cc_id = !isset($data[$i]['cc_id']) ? '':$data[$i]['cc_id'];
		$valor = !isset($data[$i]['valor']) ? '':$data[$i]['valor'];
		$tipo_valor_id = !isset($data[$i]['tipo_valor_id']) ? 0:$data[$i]['tipo_valor_id'];
		$tipo_continuidad_id = !isset($data[$i]['tipo_continuidad_id']) ? 0:$data[$i]['tipo_continuidad_id'];
		$a_partir_del_anio = !isset($data[$i]['a_partir_del_anio']) ? 0:$data[$i]['a_partir_del_anio'];

		if (!Empty($tipo_valor_id)) {
			if ($tipo_valor_id == "soles o dolares (según el tipo de moneda del contrato)") {
				$tipo_valor_id = 1;
			}
			if ($tipo_valor_id == "% (por ciento)") {
				$tipo_valor_id = 2;
			}
		}

		if (!Empty($tipo_continuidad_id)) {
			if ($tipo_continuidad_id == "anual") {
				$tipo_continuidad_id = 3;
			}
			if ($tipo_continuidad_id == "anual a partir del") {
				$tipo_continuidad_id = 2;
			}
			if ($tipo_continuidad_id == "el") {
				$tipo_continuidad_id = 1;
			}
		}

		if (!Empty($a_partir_del_anio)) {
			$a_partir_del_anio = explode("|", $a_partir_del_anio);
			$a_partir_del_anio = trim($a_partir_del_anio[0]);
		}

		if (empty($a_partir_del_anio)) {
			$incrementos_a_partir_de_año = "2";
		} else {
			$incrementos_a_partir_de_año = $a_partir_del_anio;
		}
		

		$query = "SELECT contrato_id FROM cont_contrato WHERE status = 1 AND cc_id = '".$cc_id."'";
		$list_query = $mysqli->query($query);
		while ($li = $list_query->fetch_assoc()) {
			$contrato_id = $li['contrato_id'];
		}

		if (!Empty($contrato_id)) {
			$query_insert = " INSERT INTO cont_incrementos
				(
				contrato_id,
				valor,
				tipo_valor_id,
				tipo_continuidad_id,
				a_partir_del_año,
				user_created_id,
				created_at)
				VALUES
				(
				" . $contrato_id . ",
				" . $valor . ",
				" . $tipo_valor_id . ",
				" . $tipo_continuidad_id . ",
				" . $incrementos_a_partir_de_año . ",
				" . $cont_user_created_id . ",
				'" . $cont_created_at . "')";

				$mysqli->query($query_insert);
				$insert_id = mysqli_insert_id($mysqli);
				$error = '';
				if($mysqli->error){
					$error=$mysqli->error;
					$result["status"] = 404;
					$result["message"] = 'insert_incrementos';
					$result["error"] = $error;
					$result["query"] = $query_insert;
					echo json_encode($result);
					exit();
				}
		}else{
			array_push($data_error,$cc_id);
		}
		

	}

	$result["status"] = 200;
	$result["data_error"] = $data_error;
	$result["error"] = $error;
	echo json_encode($result);
	echo "<br>";
	echo "----------------------------";
	echo "<br>";



	echo "STATUS BENEFICIARIOS";
	echo "<br>";
	$data_error = [];
	$error = '';
	$contrato_id = '';
	$data = $data_beneficiaros;
	for ($i=0; $i < count($data); $i++) {
		$contrato_id = '';
		$cc_id = !isset($data[$i]['cc_id']) ? '':$data[$i]['cc_id'];
		
		$tipo_persona_id = !isset($data[$i]['tipo_persona_id']) ? '':$data[$i]['tipo_persona_id'];
		$tipo_docu_identidad_id = !isset($data[$i]['tipo_docu_identidad_id']) ? '':$data[$i]['tipo_docu_identidad_id'];
		$num_docu = !isset($data[$i]['num_docu']) ? '':$data[$i]['num_docu'];
		$nombre = !isset($data[$i]['nombre']) ? '':$data[$i]['nombre'];
		$forma_pago_id = !isset($data[$i]['forma_pago_id']) ? '':$data[$i]['forma_pago_id'];
		$banco_id = !isset($data[$i]['banco_id']) ? '':$data[$i]['banco_id'];
		$num_cuenta_bancaria = !isset($data[$i]['num_cuenta_bancaria']) ? '':$data[$i]['num_cuenta_bancaria'];
		$num_cuenta_cci = !isset($data[$i]['num_cuenta_cci']) ? '':$data[$i]['num_cuenta_cci'];
		$tipo_monto_id = !isset($data[$i]['tipo_monto_id']) ? '':$data[$i]['tipo_monto_id'];
		$monto = !isset($data[$i]['monto']) ? '':$data[$i]['monto'];
		
		if (!Empty($tipo_persona_id)) {
			$tipo_persona_id = explode("|", $tipo_persona_id);
			$tipo_persona_id = trim($tipo_persona_id[0]);
		}
		if (!Empty($tipo_docu_identidad_id)) {
			$tipo_docu_identidad_id = explode("|", $tipo_docu_identidad_id);
			$tipo_docu_identidad_id = trim($tipo_docu_identidad_id[0]);
		}
		if (!Empty($forma_pago_id)) {
			$forma_pago_id = explode("|", $forma_pago_id);
			$forma_pago_id = trim($forma_pago_id[0]);

			$forma_pago_id = $forma_pago_id == "No menciona" ? 'NULL':$forma_pago_id;
		}
		if (!Empty($banco_id)) {
			$banco_id = explode("|", $banco_id);
			$banco_id = trim($banco_id[0]);
			$banco_id = $banco_id == "No menciona" ? "":$banco_id;
		}
		if (!Empty($tipo_monto_id)) {
			$tipo_monto_id = explode("|", $tipo_monto_id);
			$tipo_monto_id = trim($tipo_monto_id[0]);
		}

		if (!Empty($num_docu)) {
			$num_docu = $num_docu == "No menciona" ? "": $num_docu;
		}


		
		$banco_id = $banco_id == "" ? "NULL" : $banco_id;

		$query = "SELECT contrato_id FROM cont_contrato WHERE status = 1 AND cc_id = '".$cc_id."'";
		$list_query = $mysqli->query($query);
		while ($li = $list_query->fetch_assoc()) {
			$contrato_id = $li['contrato_id'];
		}

		if (!Empty($contrato_id)) {
			$nombre = str_replace("'", "",trim($nombre));

			$id_forma_pago = $forma_pago_id;

			if ( $id_forma_pago == '3') {
				$id_banco = "NULL";
				$num_cuenta_bancaria = "NULL";
				$num_cuenta_cci = "NULL";
			} else {
				$id_banco = $banco_id;
				$num_cuenta_bancaria = "'" . $num_cuenta_bancaria . "'";
				$num_cuenta_cci = "'" . $num_cuenta_cci . "'";
			}

			if (empty($monto)) {
				$monto = "NULL";
			}

			$query_insert = "INSERT INTO cont_beneficiarios
			(
			contrato_id,
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
			user_created_id,
			created_at)
			VALUES
			(
			".$contrato_id.",
			" . $tipo_persona_id . ",
			" . $tipo_docu_identidad_id . ",
			'" . $num_docu . "',
			'" . $nombre . "',
			" . $id_forma_pago . ",
			" . $id_banco . ",
			" . $num_cuenta_bancaria . ",
			" . $num_cuenta_cci . ",
			" . $tipo_monto_id . ",
			" . $monto . ",
			" . $cont_user_created_id . ",
			'" . $cont_created_at . "')";

			$mysqli->query($query_insert);
			$insert_id = mysqli_insert_id($mysqli);
			$error = '';
			if($mysqli->error){
				$error=$mysqli->error;
				$result["status"] = 404;
				$result["message"] = 'insert_beneficiario';
				$result["error"] = $error;
				$result["query"] = $query_insert;
				echo json_encode($result);
				exit();
			}
		}else{
			array_push($data_error,$cc_id);
		}		


	}

	$result["status"] = 200;
	$result["data_error"] = $data_error;
	$result["error"] = $error;
	echo json_encode($result);
	echo "<br>";
	echo "----------------------------";
	echo "<br>";

	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="contrato_proveedor") {
		
	$user_created_id = 4014;
	$created_at = date('Y-m-d H:i:s');

	//modificar
	$jsonString = '{
		"Contrato_Proveedores": [
			{
			"codigo" :1,
			 "area_responsable_id": "15|Comercial",
			 "empresa_suscribe_id": "5|FREE GAMES",
			 "persona_contacto_proveedor": "Marilu Acosta Vargas",
			 "director_aprobacion_id": "4287|Roxana Sanchez",
			 "gerente_area_id": "136|Marilu Acosta Vargas",
			 "razon_social": "ZENOVIA VIRGINIA RIVERA CIERTO VDA DE MORALES y JHONNY LEONIDAS MORALES RIVERA",
			 "nombre_comercial": "ZENOVIA VIRGINIA RIVERA CIERTO VDA DE MORALES y JHONNY LEONIDAS MORALES RIVERA",
			 "detalle_servicio": "ARRENDAMIENTO DEL INMUEBLE UBICADO EN URBANIZACIÓN EX ZONA COMERCIAL E INDUSTRIAL DE VENTANILLA MZ C1 LOTE 13 (4TO PISO , 250M2)",
			 "plazo_id": "1|Definido",
			 "periodo_numero": 3,
			 "periodo": "1|AÑOS",
			 "fecha_inicio": "01/11/2020",
			 "num_dias_para_alertar_vencimiento": 60,
			 "alcance_servicio": "ARRENDAMIENTO DEL INMUEBLE UBICADO EN URBANIZACIÓN EX ZONA COMERCIAL E INDUSTRIAL DE VENTANILLA MZ C1 LOTE 13 (4TO PISO , 250M2)",
			 "tipo_terminacion_anticipada_id": 2,
			 "etapa_id": "5|Contrato firmado",
			 "usuario_contrato_proveedor_aprobado_id": 4260,
			 "tipo_contrato_proveedor_id": "1 | Proveedor de Servicio",
			 "categoria_id": "6 | 4 - Arrendamiento",
			 "tipo_firma_id": "1 | Fisico",
			 "fecha_suscripcion_proveedor": "21/10/2020",
			 "fecha_vencimiento_indefinida_id": "31/10/2023"
			},
			{
			"codigo" :2,
			 "area_responsable_id": "15|Comercial",
			 "empresa_suscribe_id": "6|BUSINESS ADMINISTRATION SAC",
			 "persona_contacto_proveedor": "Marilu Acosta Vargas",
			 "director_aprobacion_id": "4287|Roxana Sanchez",
			 "gerente_area_id": "136|Marilu Acosta Vargas",
			 "razon_social": "ZENOVIA VIRGINIA RIVERA CIERTO VDA DE MORALES y JHONNY LEONIDAS MORALES RIVERA",
			 "nombre_comercial": "ZENOVIA VIRGINIA RIVERA CIERTO VDA DE MORALES y JHONNY LEONIDAS MORALES RIVERA",
			 "detalle_servicio": "ARRENDAMIENTO DEL INMUEBLE UBICADO EN URBANIZACIÓN EX ZONA COMERCIAL E INDUSTRIAL DE VENTANILLA MZ C1 LOTE 13 (5TO PISO , 170M2)",
			 "plazo_id": "1|Definido",
			 "periodo_numero": 3,
			 "periodo": "1|AÑOS",
			 "fecha_inicio": "15/11/2021",
			 "num_dias_para_alertar_vencimiento": 30,
			 "alcance_servicio": "ARRENDAMIENTO DEL INMUEBLE UBICADO EN URBANIZACIÓN EX ZONA COMERCIAL E INDUSTRIAL DE VENTANILLA MZ C1 LOTE 13 (5TO PISO , 170M2)",
			 "tipo_terminacion_anticipada_id": 2,
			 "etapa_id": "5|Contrato firmado",
			 "usuario_contrato_proveedor_aprobado_id": 4260,
			 "tipo_contrato_proveedor_id": "1 | Proveedor de Servicio",
			 "categoria_id": "6 | 4 - Arrendamiento",
			 "tipo_firma_id": "1 | Fisico",
			 "fecha_suscripcion_proveedor": "19/10/2021",
			 "fecha_vencimiento_indefinida_id": "14/11/2024"
			}
		   ],
		"Representante_Legal": [
				{
				"codigo": 1,
				"tipo_documento_id": "1|DNI",
				"dni_representante": "07983597",
				"nombre_representante": "ZENOVIA VIRGINIA RIVERA CIERTO VDA DE MORALES ",
				"id_banco": "14|SCOTIABANK",
				"nro_cuenta": "3860082464",
				"nro_cci": "00938620386008246441"
			   },
			   {
				"codigo": 2,
				"tipo_documento_id": "1|DNI",
				"dni_representante": 44133159,
				"nombre_representante": "JHONNY LEONIDAS MORALES RIVERA",
				"id_banco": "14|SCOTIABANK",
				"nro_cuenta": "3860082464",
				"nro_cci": "00938620386008246441"
			   }
		],
		"Contraprestaciones": [
			{
				"codigo": 1,
				"moneda_id": "1|Nuevos Soles",
				"subtotal": 2500,
				"monto": 2500,
				"tipo_comprobante_id": "3|Otro",
				"plazo_pago": "Mensual",
				"forma_pago_detallado": "a 10 dias"
			   },
			   {
				"codigo": 2,
				"moneda_id": "1|Nuevos Soles",
				"subtotal": 2000,
				"monto": 2000,
				"tipo_comprobante_id": "3|Otro",
				"plazo_pago": "Mensual",
				"forma_pago_detallado": "a 10 dias"
			   }
		]
	}';
	// var_dump($jsonString);exit();
	// Decodificar el JSON
	$data = json_decode($jsonString, true);
	
	// if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
	// 	// Manejo de error o mensaje adecuado si hay un problema con el JSON
	// 	echo 'Error al decodificar el JSON: ' . json_last_error_msg();
	// 	exit;
	// }
	$data_contratos = $data['Contrato_Proveedores'];
	$data_representantes = $data['Representante_Legal'];
	$data_contraprestaciones = $data['Contraprestaciones'];
	// var_dump($data);exit();

	///IMPORTACION CONTRATOS
	$error = '';
	$contrato_id = '';
	$data = $data_contratos;$array= [];
	for ($i=0; $i < count($data); $i++) { 

		// contrato
		$codigo = !isset($data[$i]['codigo']) ? '':$data[$i]['codigo'];
		$area_responsable_id = !isset($data[$i]['area_responsable_id']) ? '':$data[$i]['area_responsable_id'];
		$empresa_suscribe_id = !isset($data[$i]['empresa_suscribe_id']) ? '':$data[$i]['empresa_suscribe_id'];
		$persona_contacto_proveedor = !isset($data[$i]['persona_contacto_proveedor']) ? '':$data[$i]['persona_contacto_proveedor'];
		$gerente_area_id = !isset($data[$i]['gerente_area_id']) ? '':$data[$i]['gerente_area_id'];
		$director_aprobacion_id = !isset($data[$i]['director_aprobacion_id']) ? '':$data[$i]['director_aprobacion_id'];
		$gerente_area_nombre = !isset($data[$i]['gerente_area_nombre']) ? '':$data[$i]['gerente_area_nombre'];
		$gerente_area_email = !isset($data[$i]['gerente_area_email']) ? '':$data[$i]['gerente_area_email'];
		$ruc = !isset($data[$i]['ruc']) ? '':$data[$i]['ruc'];
		$razon_social = !isset($data[$i]['razon_social']) ? '':$data[$i]['razon_social'];
		$detalle_servicio = !isset($data[$i]['detalle_servicio']) ? '':$data[$i]['detalle_servicio'];
		$plazo = !isset($data[$i]['plazo']) ? "NULL":$data[$i]['plazo'];
		$periodo_numero = !isset($data[$i]['periodo_numero']) ? "NULL":$data[$i]['periodo_numero'];
		$periodo = !isset($data[$i]['periodo']) ? "NULL":$data[$i]['periodo'];
		$fecha_inicio = !isset($data[$i]['fecha_inicio']) ? '':$data[$i]['fecha_inicio'];
		$alcance_servicio = !isset($data[$i]['alcance_servicio']) ? '':$data[$i]['alcance_servicio'];
		$tipo_terminacion_anticipada_id = !isset($data[$i]['tipo_terminacion_anticipada_id']) ? '':$data[$i]['tipo_terminacion_anticipada_id'];
		$terminacion_anticipada = !isset($data[$i]['terminacion_anticipada']) ? '':$data[$i]['terminacion_anticipada'];
		$observaciones = !isset($data[$i]['observaciones']) ? '':$data[$i]['observaciones'];
		$etapa_id = !isset($data[$i]['etapa_id']) ? '':$data[$i]['etapa_id'];
		$categoria_id = !isset($data[$i]['categoria_id']) ? '':$data[$i]['categoria_id'];
		$tipo_contrato_proveedor_id = !isset($data[$i]['tipo_contrato_proveedor_id']) ? '':$data[$i]['tipo_contrato_proveedor_id'];
		$tipo_firma_id = !isset($data[$i]['tipo_firma_id']) ? '':$data[$i]['tipo_firma_id'];
		$fecha_suscripcion_proveedor = !isset($data[$i]['fecha_suscripcion_proveedor']) ? '':$data[$i]['fecha_suscripcion_proveedor'];
		$fecha_vencimiento_indefinida_id = !isset($data[$i]['fecha_vencimiento_indefinida_id']) ? "NULL":$data[$i]['fecha_vencimiento_indefinida_id'];
		$fecha_vencimiento_proveedor = !isset($data[$i]['fecha_vencimiento_proveedor']) ? "NULL":$data[$i]['fecha_vencimiento_proveedor'];
		$status = 1;
		$usuario_contrato_proveedor_aprobado_id = !isset($data[$i]['usuario_contrato_proveedor_aprobado_id']) ? '':$data[$i]['usuario_contrato_proveedor_aprobado_id'];

		// $fecha_inicio_agente = !Empty($fecha_inicio_agente) ? str_replace("\/", "/", $fecha_inicio_agente):'';


		if (!Empty($area_responsable_id)) {
			$area_responsable_id = explode("|", $area_responsable_id);
			$area_responsable_id = trim($area_responsable_id[0]);
		}
		if (!Empty($empresa_suscribe_id)) {
			$empresa_suscribe_id = explode("|", $empresa_suscribe_id);
			$empresa_suscribe_id = trim($empresa_suscribe_id[0]);
		}
		if (!Empty($gerente_area_id)) {
			$gerente_area_id = explode("|", $gerente_area_id);
			$gerente_area_id = trim($gerente_area_id[0]);
		}
		if (!Empty($director_aprobacion_id)) {
			$director_aprobacion_id = explode("|", $director_aprobacion_id);
			$director_aprobacion_id = trim($director_aprobacion_id[0]);
		}
		if (!Empty($plazo)) {
			$plazo = explode("|", $plazo);
			$plazo = trim($plazo[0]);
		}
		if (!Empty($periodo)) {
			$periodo = explode("|", $periodo);
			$periodo = trim($periodo[0]);
		}
		if (!Empty($tipo_terminacion_anticipada_id)) {
			$tipo_terminacion_anticipada_id = explode("|", $tipo_terminacion_anticipada_id);
			$tipo_terminacion_anticipada_id = trim($tipo_terminacion_anticipada_id[0]);
		}
		if (!Empty($etapa_id)) {
			$etapa_id = explode("|", $etapa_id);
			$etapa_id = trim($etapa_id[0]);
		}
		if (!Empty($categoria_id)) {
			$categoria_id = explode("|", $categoria_id);
			$categoria_id = trim($categoria_id[0]);
		}
		if (!Empty($tipo_contrato_proveedor_id)) {
			$tipo_contrato_proveedor_id = explode("|", $tipo_contrato_proveedor_id);
			$tipo_contrato_proveedor_id = trim($tipo_contrato_proveedor_id[0]);
		}
		if (!Empty($tipo_firma_id)) {
			$tipo_firma_id = explode("|", $tipo_firma_id);
			$tipo_firma_id = trim($tipo_firma_id[0]);
		}
		if (!Empty($fecha_vencimiento_indefinida_id)) {
			$fecha_vencimiento_indefinida_id = explode("|", $fecha_vencimiento_indefinida_id);
			$fecha_vencimiento_indefinida_id = trim($fecha_vencimiento_indefinida_id[0]);
		}
		if (!Empty($usuario_contrato_proveedor_aprobado_id)) {
			$usuario_contrato_proveedor_aprobado_id = explode("|", $usuario_contrato_proveedor_aprobado_id);
			$usuario_contrato_proveedor_aprobado_id = trim($usuario_contrato_proveedor_aprobado_id[0]);
		}

		$fecha_inicio = !Empty($fecha_inicio) ? str_replace("\/", "/", $fecha_inicio):'';
		$fecha_suscripcion_proveedor = !Empty($fecha_suscripcion_proveedor) ? str_replace("\/", "/", $fecha_suscripcion_proveedor):'';
		$fecha_vencimiento_proveedor = !Empty($fecha_vencimiento_proveedor) ? str_replace("\/", "/", $fecha_vencimiento_proveedor):'';

		list($dia, $mes, $anio) = explode('/', $fecha_inicio);
		list($dia2, $mes2, $anio2) = explode('/', $fecha_suscripcion_proveedor);
		// list($dia3, $mes3, $anio3) = explode('/', $fecha_vencimiento_proveedor);
		$fecha_inicio = !empty($fecha_inicio) ? "'" . date("Y-m-d", strtotime("$anio-$mes-$dia")) . "'" : "NULL";
		$fecha_suscripcion_proveedor = !empty($fecha_suscripcion_proveedor) ? "'" . date("Y-m-d", strtotime("$anio2-$mes2-$dia2")) . "'" : "NULL";
		if (preg_match("/^\d{2}\/\d{2}\/\d{4}$/", $fecha_vencimiento_proveedor)) {
			list($dia3, $mes3, $anio3) = explode('/', $fecha_vencimiento_proveedor);
			// Ahora puedes usar $dia3, $mes3 y $anio3
		$fecha_vencimiento_proveedor = !empty($fecha_vencimiento_proveedor) ? "'" . date("Y-m-d", strtotime("$anio3-$mes3-$dia3")) . "'" : "NULL";

		} 
		// $fecha_inicio = !Empty($fecha_inicio) ? "'".date("Y-m-d", strtotime($fecha_inicio))." ".date('H:i:s')."'":"NULL";
		// $fecha_suscripcion_proveedor = !Empty($fecha_suscripcion_proveedor) ? "'".date("Y-m-d", strtotime($fecha_suscripcion_proveedor))."'":"NULL";
		// $fecha_vencimiento_proveedor = !Empty($fecha_vencimiento_proveedor) ? "'".date("Y-m-d", strtotime($fecha_vencimiento_proveedor))."'":"NULL";

		
		$gerente_area_id = $gerente_area_id == "" ? "NULL" : $gerente_area_id;
		$tipo_terminacion_anticipada_id = $tipo_terminacion_anticipada_id == "" ? 0 : $tipo_terminacion_anticipada_id;
		$categoria_id = $categoria_id == "" ? 0 : $categoria_id;
		$tipo_contrato_proveedor_id = $tipo_contrato_proveedor_id == "" ? 0 : $tipo_contrato_proveedor_id;
		$tipo_firma_id = $tipo_firma_id == "" ? 0 : $tipo_firma_id;
		$fecha_vencimiento_indefinida_id = $fecha_vencimiento_indefinida_id == "NULL" ? "NULL" : $fecha_vencimiento_indefinida_id;
		
		
		if ($gerente_area_id == 'A') {
			$gerente_area_id = "NULL";
			$gerente_area_nombre = trim($gerente_area_nombre);
			$gerente_area_email = trim($gerente_area_email);
		}else{
			$gerente_area_nombre = '';
			$gerente_area_email = '';
		}
		if (!Empty($periodo_numero)) {
			$periodo_numero = explode("|", $periodo_numero);
			$periodo_numero = trim($periodo_numero[0]);
		}else{
			$periodo_numero = 0;
		}
		// array_push($array,[$codigo,$fecha_inicio,$fecha_suscripcion_proveedor,$fecha_vencimiento_proveedor]);
		// var_dump($array);
		if (!Empty($codigo)) {
			
			$query_update = "UPDATE cont_correlativo SET numero = numero + 1 WHERE tipo_contrato = 2 AND status = 1 ";

			$mysqli->query($query_update);
		
			if($mysqli->error)
			{
				$error .= $mysqli->error;
				$result["status"] = 404;
				$result["message"] = 'correlativo';
				$result["error"] = $error;
				$result["query"] = $query_update;
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
					WHERE tipo_contrato = 2 AND status = 1 LIMIT 1
				";
		
				$list_query = $mysqli->query($select_correlativo);
		
				while($sel = $list_query->fetch_assoc())
				{
					$sigla = $sel["sigla"];
					$numero_correlativo = $sel["numero"];
				}

				// INICIO INSERTAR EN CONTRATO
				$mysqli->begin_transaction();
				try {
					$query_insert = "INSERT INTO cont_contrato
					(
						tipo_contrato_id
						, codigo_correlativo 
						, empresa_suscribe_id
						, area_responsable_id
						, persona_responsable_id
						, etapa_id
						, ruc
						, razon_social
						, check_gerencia_proveedor
						, persona_contacto_proveedor
						, detalle_servicio
						, plazo_id
						, periodo_numero
						, periodo
						, fecha_inicio
						, alcance_servicio
						, tipo_terminacion_anticipada_id
						, terminacion_anticipada
						, observaciones
						, status
						, user_created_id
						, created_at
			
						, gerente_area_id
						, gerente_area_nombre
						, gerente_area_email
						, categoria_id
						, tipo_contrato_proveedor_id
						, tipo_firma_id
						, fecha_suscripcion_proveedor
						, fecha_vencimiento_indefinida_id
						, fecha_vencimiento_proveedor
						, usuario_contrato_proveedor_aprobado_id
						
					)
					VALUES
					(
						2,
						" . $numero_correlativo . ",
						" . $empresa_suscribe_id . ",
						" . $area_responsable_id . ",
						0,
						" . $etapa_id . ",
						'" . $ruc . "',
						'" . replace_invalid_caracters($razon_social) . "',
						'0',
						'" . replace_invalid_caracters($persona_contacto_proveedor) . "',
						'" . replace_invalid_caracters($detalle_servicio) . "',
						" . $plazo . ",
						" . $periodo_numero . ",
						" . $periodo . ",
						" . $fecha_inicio . ",
						'" . replace_invalid_caracters($alcance_servicio) . "',
						" . $tipo_terminacion_anticipada_id . ",
						'" . replace_invalid_caracters($terminacion_anticipada) . "',
						'" . replace_invalid_caracters($observaciones) . "',
						1,
						" . $user_created_id . ",
						'" . $created_at . "',
						
						" . $gerente_area_id . ",
						'" . $gerente_area_nombre . "',
						'" . $gerente_area_email . "',
						'" . $categoria_id . "',
						'" . $tipo_contrato_proveedor_id . "',
						'" . $tipo_firma_id . "',
						" . $fecha_suscripcion_proveedor . ",
						" . $fecha_vencimiento_indefinida_id . ",
						" . $fecha_vencimiento_proveedor . ",
						" . $usuario_contrato_proveedor_aprobado_id . "
						
					)";

					$mysqli->query($query_insert);
					$contrato_id = mysqli_insert_id($mysqli);
					$data_contratos[$i]['contrato_id'] = $contrato_id;
					// var_dump($query_insert,$mysqli->error);exit();

					if ($mysqli->error) {
						$mysqli->rollback();
						$error .= $mysqli->error;
						$result["status"] = 404;
						$result["message"] = 'insert contrato';
						$result["error"] = $mysqli->error;
						$result["query"] = $query_insert;
						echo json_encode($result);
						exit();
					}
					// FIN INSERTAR EN CONTRATO
					$mysqli->commit();
				} catch (Exception $e) {
					$mysqli->rollback();
					// var_dump("error contrato " . $result["error"]."  ".$e->getMessage());
					echo json_encode("error contrato proveedor" . $e->getMessage());
				}
				
			}
			
		}
	}


	echo "STATUS CONTRATOS";
	echo "<br>";
	$result["status"] = 200;
	$result["contrato_id"] = $contrato_id;
	$result["error"] = $error;
	echo json_encode($result);
	echo "<br>";
	echo "----------------------------";
	echo "<br>";


	echo "STATUS REPRESENTANTES LEGALES";
	echo "<br>";
	$data_error = [];
	$error = '';
	$contrato_id = '';
	$data = $data_representantes;

	for ($i=0; $i < count($data); $i++) {
		$contrato_id = "";

		

		$codigo = !isset($data[$i]['codigo']) ? '':$data[$i]['codigo'];
		$dni_representante = !isset($data[$i]['dni_representante']) ? '':$data[$i]['dni_representante'];
		$nombre_representante = !isset($data[$i]['nombre_representante']) ? '':$data[$i]['nombre_representante'];
		$id_banco = !isset($data[$i]['id_banco']) ? '':$data[$i]['id_banco'];
		$nro_cuenta = !isset($data[$i]['nro_cuenta']) ? '':$data[$i]['nro_cuenta'];
		$nro_cci = !isset($data[$i]['nro_cci']) ? '':$data[$i]['nro_cci'];

		$key = array_search($codigo, array_column($data_contratos, 'codigo'));
		$contrato_id = $data_contratos[$key]['contrato_id'];
		
		if (!Empty($id_banco)) {
			$id_banco = explode("|", $id_banco);
			$id_banco = trim($id_banco[0]);
		}else{
			$id_banco = 0;
		}

		if (!Empty($contrato_id)) {
			$mysqli->begin_transaction();

			try {
				$query_insert = " INSERT INTO cont_representantes_legales
				(
				contrato_id,
				dni_representante,
				nombre_representante,
				id_banco,
				nro_cuenta,
				nro_cci,
				vigencia_archivo_id,
				dni_archivo_id,
				id_user_created,
				created_at)
				VALUES
				(
				" . $contrato_id . ",
				'" . $dni_representante. "',
				'" . $nombre_representante . "',
				'" . $id_banco . "',
				'" . $nro_cuenta . "',
				'" . $nro_cci . "',
				0,
				0,
				" . $user_created_id . ",
				'" . $created_at . "')";
				$mysqli->query($query_insert);
				$persona_id = mysqli_insert_id($mysqli);
				$error = '';
				if($mysqli->error){
					$mysqli->rollback();
					$error .= $mysqli->error;
					$result["status"] = 404;
					$result["message"] = 'insert representante legal';
					$result["error"] = $error;
					$result["query"] = $query_insert;
					echo json_encode($result);
					exit();
				}
				$mysqli->commit();
			} catch (Exception $e) {
				$mysqli->rollback();
				// var_dump("error contrato " . $result["error"]."  ".$e->getMessage());
				echo json_encode("error contrato proveedor" . $e->getMessage());
			}
			
		}else{
			array_push($data_error,$codigo);
		}

		
	}

	$result["status"] = 200;
	$result["data_error"] = $data_error;
	$result["error"] = $error;
	echo json_encode($result);
	echo "<br>";
	echo "----------------------------";
	echo "<br>";






	echo "STATUS CONTRAPRESTACIONES";
	echo "<br>";
	$data_error = [];
	$error = '';
	$contrato_id = '';
	$data = $data_contraprestaciones;
	for ($i=0; $i < count($data); $i++) {
		$contrato_id = '';
		


		$codigo = !isset($data[$i]['codigo']) ? '':$data[$i]['codigo'];
		$moneda_id = !isset($data[$i]['moneda_id']) ? '':$data[$i]['moneda_id'];
		$subtotal = !isset($data[$i]['subtotal']) ? 0:$data[$i]['subtotal'];
		$igv = !isset($data[$i]['igv']) ? 0:$data[$i]['igv'];
		$monto = !isset($data[$i]['monto']) ? 0:$data[$i]['monto'];
		$tipo_comprobante_id = !isset($data[$i]['tipo_comprobante_id']) ? '':$data[$i]['tipo_comprobante_id'];
		$plazo_pago = !isset($data[$i]['plazo_pago']) ? '':$data[$i]['plazo_pago'];
		$forma_pago_detallado = !isset($data[$i]['forma_pago_detallado']) ? '':$data[$i]['forma_pago_detallado'];


		$key = array_search($codigo, array_column($data_contratos, 'codigo'));
		$contrato_id = $data_contratos[$key]['contrato_id'];


		if (!Empty($moneda_id)) {
			$moneda_id = explode("|", $moneda_id);
			$moneda_id = trim($moneda_id[0]);
		}
		if (!Empty($tipo_comprobante_id)) {
			$tipo_comprobante_id = explode("|", $tipo_comprobante_id);
			$tipo_comprobante_id = trim($tipo_comprobante_id[0]);
		}
		$mysqli->begin_transaction();
		try {
			if (!Empty($contrato_id)) {
				$query_insert = " INSERT INTO cont_contraprestacion
					(
					contrato_id,
					moneda_id,
					forma_pago_id,
					tipo_comprobante_id,
					plazo_pago,
					subtotal,
					igv,
					monto,
					forma_pago_detallado,
					status,
					user_created_id,
					created_at)
					VALUES
					(
					" . $contrato_id . ",
					" . $moneda_id . ",
					0,
					" . $tipo_comprobante_id . ",
					'" . $plazo_pago . "',
					" . $subtotal . ",
					" . $igv . ",
					" . $monto . ",
					'" . $forma_pago_detallado . "',
					1,
					" . $user_created_id . ",
					'" . $created_at . "')";
	
					$mysqli->query($query_insert);
					$insert_id = mysqli_insert_id($mysqli);
					$error = '';
					if($mysqli->error){
						$mysqli->rollback();

						$error=$mysqli->error;
						$result["status"] = 404;
						$result["message"] = 'insert_contraprestaciones';
						$result["error"] = $error;
						$result["query"] = $query_insert;
						echo json_encode($result);
						exit();
					}
			}else{
				array_push($data_error,$codigo);
			}
			$mysqli->commit();
		} catch (Exception $e) {
			$mysqli->rollback();
			// var_dump("error contrato " . $result["error"]."  ".$e->getMessage());
			echo json_encode("error contrato proveedor" . $e->getMessage());
		}
	
		

	}

	$result["status"] = 200;
	$result["data_error"] = $data_error;
	$result["error"] = $error;
	echo json_encode($result);
	echo "<br>";
	echo "----------------------------";
	echo "<br>";

}

if (isset($_POST["accion"]) && $_POST["accion"]==="contrato_archivos_arrendamiento") {

	$folder_import = array();
	$dir_path = "/var/www/html/files_bucket/importacion/";
	$folder_master  = scandir($dir_path);
	foreach ($folder_master as $key => $value) {
		if ($key > 1) {		
			$dir_path1 = $dir_path.$value;
			$sub_folder_1  = scandir($dir_path1);
			foreach ($sub_folder_1 as $key2 => $value2) {
				if ($key2 > 1) {
					$file = explode("-", $value2);

					$name_file = $value2;
					$route = $dir_path1."/".$name_file;
					$weight = filesize($route);
					$extension = new SplFileInfo($route);

					$filename = explode(".", trim($file[2]));
					array_push($folder_import, array(
						'type_request' => $value,
						'code' => trim($file[0]),
						'id_type_file' => trim($file[1]),
						'name' => $filename[0],
						'route' => $route,
						'weight' => $weight,
						'extension' => $extension->getExtension(),
					));
				}
			}
		}
		
	}

	
	
	$error = array();
	$error_sql_import = array();
	$error_copy_file = array();
	$usuario_id = 1;
	$created_at = date('Y-m-d H:i:s');
	for ($i=0; $i < count($folder_import) ; $i++) { 
	
		$contrato_id = "";
		$query_contrato = "SELECT contrato_id FROM cont_contrato WHERE cc_id = '".$folder_import[$i]['code']."' AND status = 1 LIMIT 1";
		// echo $query_contrato;
		$list_query = $mysqli->query($query_contrato);
		while($sel = $list_query->fetch_assoc()){ $contrato_id = $sel["contrato_id"]; }

		if (!Empty($contrato_id)) {
			$nombre_archivo = $contrato_id . "_".$folder_import[$i]['name']."_" . date('YmdHis') . "." . $folder_import[$i]['extension'];
			$new_path = "/var/www/html/files_bucket/contratos/".$folder_import[$i]['type_request']."/"."locales/";
			$new_route = $new_path.$nombre_archivo;

			if (rename($folder_import[$i]['route'],$new_route)) {
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
					".$folder_import[$i]['id_type_file'].",
					'" . $nombre_archivo . "',
					'" . $folder_import[$i]['extension'] . "',
					'" . $folder_import[$i]['weight'] . "',
					'" . $new_path . "',
					" . $usuario_id . ",
					'" . $created_at . "'
					)";
				$mysqli->query($comando);
				if($mysqli->error){
					array_push($error_sql_import, array('error' => 'Error al guardar la partida registral: ' . $mysqli->error, 'sql' => $comando));
				}
			}else{
				array_push($error_copy_file,
					array('route' => $route, 'route_new' => $new_route)
				);
			}

			
			
		}else{
			array_push($error,$folder_import[$i]['code']);
		}
	}


	$result['folder_import'] = $folder_import;
	$result['error'] = $error;
	$result['error_sql_import'] = $error_sql_import;
	$result['error_copy_file'] = $error_copy_file;

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="contrato_archivos_proveedores") {

	$folder_import = array();
	$dir_path = "/var/www/html/files_bucket/importacion/";
	$folder_master  = scandir($dir_path);
	foreach ($folder_master as $key => $value) {
		if ($key > 1) {		
			$dir_path1 = $dir_path.$value;

			$sub_folder_1  = scandir($dir_path1);
			foreach ($sub_folder_1 as $key2 => $value2) {
				if ($key2 > 1) {
					$file = explode("-", $value2);

					// Supongamos que tienes el primer elemento del array $file como "AG10".
					$firstItem = $file[0];
					$code_correlativo = preg_replace("/[^0-9]/", "", $firstItem);

					// Utiliza una expresión regular para encontrar y extraer los dígitos numéricos.
					// if (preg_match('/\d+/', $firstItem, $matches)) {
					// 	$code_correlativo = $matches[0]; // $numericValue contendrá "10" como una cadena.
					// 	// Si deseas convertirlo a un valor numérico (entero), puedes hacerlo así:
					// 	$code_correlativo = intval($matches[0]); // $numericValue contendrá 10 como un entero.
					// } else {
					// 	// Manejar el caso en el que no se encontraron dígitos numéricos.
					// 	$code_correlativo = 0; // O cualquier otro valor predeterminado que desees.
					// }

					$name_file = $value2;
					$route = $dir_path1."/".$name_file;
					$weight = filesize($route);
					$extension = new SplFileInfo($route);
					if($value != 'adendas'){
					$filename = explode(".", trim($file[2]));
						array_push($folder_import, array(
							'type_request' => $value,
							'code' => $code_correlativo,
							'id_type_file' => trim($file[1]),
							'name' => $filename[0],
							'route' => $route,
							'weight' => $weight,
							'extension' => $extension->getExtension(),
						));
					}
					
				}
			}
		}
		
	}
	// var_dump($folder_import);exit();

	$error = array();
	$error_sql_import = array();
	$usuario_id = 1;
	$created_at = date('Y-m-d H:i:s');
	for ($i=0; $i < count($folder_import) ; $i++) { 
		$contrato_id = "";
		// $query_contrato = "SELECT contrato_id FROM cont_contrato WHERE cc_id = '".$folder_import[$i]['code']."' AND status = 1 LIMIT 1";
		// $query_contrato = "SELECT contrato_id FROM cont_contrato WHERE cc_id = '".$folder_import[$i]['code']."' AND status = 1 LIMIT 1";
		$query_contrato = "SELECT contrato_id FROM cont_contrato WHERE codigo_correlativo = '".$folder_import[$i]['code']."'  AND status = 1
		AND tipo_contrato_id = 2
		LIMIT 1";
	// var_dump($query_contrato);exit();

		// echo $query_contrato;
		$list_query = $mysqli->query($query_contrato);
		while($sel = $list_query->fetch_assoc()){ $contrato_id = $sel["contrato_id"]; }
		if (!Empty($contrato_id)) {
			$nombre_archivo = $contrato_id . "_".$folder_import[$i]['name']."_" . date('YmdHis') . "." . $folder_import[$i]['extension'];
			$new_path = "/var/www/html/files_bucket/contratos/".$folder_import[$i]['type_request']."/"."proveedores/";
			$new_route = $new_path.$nombre_archivo;
						// var_dump($contrato_id,$cont);

			if (rename($folder_import[$i]['route'],$new_route)) {
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
					".$folder_import[$i]['id_type_file'].",
					'" . $nombre_archivo . "',
					'" . $folder_import[$i]['extension'] . "',
					'" . $folder_import[$i]['weight'] . "',
					'" . $new_path . "',
					" . $usuario_id . ",
					'" . $created_at . "'
					)";
					// var_dump($comando);

				$mysqli->query($comando);
				if($mysqli->error){
					array_push($error_sql_import, array('error' => 'Error al guardar la partida registral: ' . $mysqli->error, 'sql' => $comando));
				}
			}else{
				array_push($error_copy_file,
					array('route' => $route, 'route_new' => $new_route)
				);
			}
		}else{
			array_push($error,$folder_import['code']);
		}
	}


	$result['folder_import'] = $folder_import;
	$result['error'] = $error;
	$result['error_sql_import'] = $error_sql_import;

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="contrato_locales_municipal") {
		
	$data = json_decode(file_get_contents("../files_bucket/migracion/data-locales-municipal.json"), true);
	$error = array();
	$actualizados = array();
	for ($i=0; $i < count($data); $i++) { 
		$contrato_id = "";
		$cc_id = !isset($data[$i]['cc_id']) ? '':$data[$i]['cc_id'];
		$direccion_municipal = !isset($data[$i]['direccion_municipal']) ? '':$data[$i]['direccion_municipal'];

		if (!Empty($cc_id)) {
			$query_cont = "SELECT c.contrato_id FROM cont_contrato AS c WHERE c.status = 1 AND c.cc_id = '".$cc_id."'";
			$list_query = $mysqli->query($query_cont);
			while($sel = $list_query->fetch_assoc())
			{
				$contrato_id = $sel["contrato_id"];
			}

			if(Empty($contrato_id))
			{
				array_push($error,$cc_id);
			}
			else
			{
				// INICIO ACTUALIZAR INMUEBLE
				$query_update = "UPDATE cont_inmueble SET direccion_municipal = '" . replace_invalid_caracters($direccion_municipal) . "' 
				WHERE contrato_id = ".$contrato_id;
				$mysqli->query($query_update);
				if($mysqli->error){
					$result["status"] = 404;
					$result["message"] = 'update inmueble';
					$result["error"] = $error;
					$result["query"] = $query_update;
					echo json_encode($result);
					exit();
				}
				array_push($actualizados,$cc_id);
				// FIN ACTUALIZAR INMUEBLE
			}
			
		}
	}
	echo "STATUS INMUEBLE";
	echo "<br>";
	$result["status"] = 200;
	$result["contrato_id"] = $contrato_id;
	$result["error"] = $error;
	$result["registros_error"] = count($error);
	$result["actualizados"] = $actualizados;
	$result["registros_actualizados"] = count($actualizados);
	echo json_encode($result);
	echo "<br>";
	echo "----------------------------";
	echo "<br>";
	exit();
}
if (isset($_POST["accion"]) && $_POST["accion"] === "contrato_agente_carga") {

	$user_created_id = 4014;
	$persona_responsable_id_2 = 1137;
	$created_at = date('Y-m-d H:i:s');

	$data = json_decode(file_get_contents("/var/www/html/files_bucket/migracion/data_agentes_061023.json"), true);

	$data_contratos = $data['Contrato_Proveedores'];
	$data_propietarios_existentes = $data['propietarios'];
	$propietarios_nuevos = $data['propietarios_nuevos'];
	///IMPORTACION CONTRATOS AGENTES
	$error = '';
	$contrato_id = '';
	$data = $data_contratos;
	$array = [];
	for ($i = 0; $i < count($data); $i++) {

		// validar si existe el contrato con el ceco
		$existe_contrato =
			"SELECT *from cont_contrato cc 
					where cc.tipo_contrato_id =6  AND  cc.c_costos = " . $data[$i]['c_costos'];


		$list_query = $mysqli->query($existe_contrato);

		
			// contrato agente
			$codigo = !isset($data[$i]['codigo']) ? '' : $data[$i]['codigo'];

			$persona_responsable_id = !isset($data[$i]['persona_responsable_id']) ? '' : intval($data[$i]['persona_responsable_id']);

			$nombre_agente = !isset($data[$i]['nombre_agente']) ? '' : $data[$i]['nombre_agente'];
			$periodo_numero = !isset($data[$i]['periodo_numero']) ? '' : $data[$i]['periodo_numero'];
			$periodo = !isset($data[$i]['periodo']) ? '' : $data[$i]['periodo'];
			$fecha_suscripcion_contrato = !isset($data[$i]['fecha_suscripcion_contrato']) ? '' : $data[$i]['fecha_suscripcion_contrato'];
			$plazo_id_agente = !isset($data[$i]['plazo_id_agente']) ? '' : $data[$i]['plazo_id_agente'];
			$fecha_inicio_agente = !isset($data[$i]['fecha_inicio_agente']) ? '' : $data[$i]['fecha_inicio_agente'];
			$fecha_fin_agente = !isset($data[$i]['fecha_fin_agente']) ? '' : $data[$i]['fecha_fin_agente'];
			$renovacion_automatica = !isset($data[$i]['renovacion_automatica']) ? '' : $data[$i]['renovacion_automatica'];
			$cont_observaciones = !isset($data[$i]['observaciones']) ? '' : $data[$i]['observaciones'];
			$cont_cc_id = !isset($data[$i]['c_costos']) ? '' : $data[$i]['c_costos'];
			$fecha_inicio_agente = !Empty($fecha_inicio_agente) ? str_replace("\/", "/", $fecha_inicio_agente):'';
			$fecha_fin_agente = !Empty($fecha_fin_agente) ? str_replace("\/", "/", $fecha_fin_agente):'';

			// FORMATO DE FECHAS 
			list($dia, $mes, $anio) = explode('/', $fecha_inicio_agente);
			$fecha_inicio_agente = !empty($fecha_inicio_agente) ? "'" . date("Y-m-d", strtotime("$anio-$mes-$dia")) . "'" : "NULL";
			list($dia_, $mes_, $anio_) = explode('/', $fecha_fin_agente);
			$fecha_fin_agente = !empty($fecha_fin_agente) ? "'" . date("Y-m-d", strtotime("$anio_-$mes_-$dia_")) . "'" : "NULL";
			// $fecha_inicio_agente = !empty($fecha_inicio_agente) ? "'" . date("Y-m-d", strtotime($fecha_inicio_agente)) . "'" : "NULL";
			// $fecha_fin_agente = !empty($fecha_fin_agente) ? "'" . date("Y-m-d", strtotime($fecha_fin_agente)) . "'" : "NULL";
	

			//BETSHOP
			$betshop = !isset($data[$i]['betshop']) ? '' : $data[$i]['betshop'];
			$betshop_cp = !isset($data[$i]['betshop_cp']) ? '' : $data[$i]['betshop_cp'];
			//juegos_cp
			$juegos = !isset($data[$i]['juegos']) ? '' : $data[$i]['juegos'];
			$juegos_cp = !isset($data[$i]['juegos_cp']) ? '' : $data[$i]['juegos_cp'];
			//TERMINALES
			$terminales = !isset($data[$i]['terminales']) ? '' : $data[$i]['terminales'];
			$terminales_cp = !isset($data[$i]['terminales_cp']) ? '' : $data[$i]['terminales_cp'];
			//BINGO
			$bingo = !isset($data[$i]['bingo']) ? '' : $data[$i]['bingo'];
			$bingo_cp = !isset($data[$i]['bingo_cp']) ? '' : $data[$i]['bingo_cp'];
			//DEPOSITO
			$deposito = !isset($data[$i]['deposito']) ? '' : $data[$i]['deposito'];
			$deposito_cp = !isset($data[$i]['deposito_cp']) ? '' : $data[$i]['deposito_cp'];
			//CARRERAS
			$carreras = !isset($data[$i]['carreras']) ? '' : $data[$i]['carreras'];
			$carreras_cp = !isset($data[$i]['carreras_cp']) ? '' : $data[$i]['carreras_cp'];

			if (!empty($persona_responsable_id)) {
				$persona_responsable_id = explode("|", $persona_responsable_id);
				$persona_responsable_id = trim($persona_responsable_id[0]);
			}

			// if (!empty($nombre_agente)) {
			// 	$partes = explode(" ", $nombre_agente); // Divide la cadena en partes usando el espacio como separador

			// 	// Verifica si hay al menos dos partes antes de continuar
			// 	if (count($partes) >= 2) {
			// 		// Elimina la primera parte (Agente) y luego une las partes restantes
			// 		$nombre_agente = trim(implode(" ", array_slice($partes, 2)));
			// 	} else {
			// 	}
			// }
			if (!empty($cont_cc_id)) {
				$cont_cc_id = intval($cont_cc_id);
			}
			if (!empty($periodo_numero)) {
				$periodo_numero = intval($periodo_numero);
			}
			if (!empty($periodo)) {
				$periodo = explode("|", $periodo);
				$periodo = trim($periodo[0]);
			}

			$fecha_suscripcion_contrato = !empty($fecha_suscripcion_contrato) ? "'" . date("Y-m-d", strtotime($fecha_suscripcion_contrato)) . "'" : "NULL";
			if (!empty($plazo_id_agente)) {
				$plazo_id_agente = explode("|", $plazo_id_agente);
				$plazo_id_agente = trim($plazo_id_agente[0]);
			}

			// $fecha_inicio_agente = !empty($fecha_inicio_agente) ? "'" . date("Y-m-d", strtotime($fecha_inicio_agente)) . "'" : "NULL";
			// $fecha_fin_agente = !empty($fecha_fin_agente) ? "'" . date("Y-m-d", strtotime($fecha_fin_agente)) . "'" : "NULL";
			if (!empty($renovacion_automatica)) {
				$renovacion_automatica = explode("|", $renovacion_automatica);
				$renovacion_automatica = trim($renovacion_automatica[0]);
			}
			if (!empty($betshop_cp)) {
				$betshop_cp = explode("|", $betshop_cp);
				$betshop_cp = trim($betshop_cp[0]);
			}
			if (!empty($juegos_cp)) {
				$juegos_cp = explode("|", $juegos_cp);
				$juegos_cp = trim($juegos_cp[0]);
			}
			if (!empty($terminales_cp)) {
				$terminales_cp = explode("|", $terminales_cp);
				$terminales_cp = trim($terminales_cp[0]);
			}
			if (!empty($bingo_cp)) {
				$bingo_cp = explode("|", $bingo_cp);
				$bingo_cp = trim($bingo_cp[0]);
			}
			if (!empty($deposito_cp)) {
				$deposito_cp = explode("|", $deposito_cp);
				$deposito_cp = trim($deposito_cp[0]);
			}
			if (!empty($carreras_cp)) {
				$carreras_cp = explode("|", $carreras_cp);
				$carreras_cp = trim($carreras_cp[0]);
			}

			$cont_status = 1;

			//inmuebles
			$ubigeo_id = !isset($data[$i]['ubigeo_id']) ? '' : $data[$i]['ubigeo_id'];
			$ubicacion = !isset($data[$i]['ubicacion']) ? '' : $data[$i]['ubicacion'];
			// if (!empty($ubigeo_id)) {
			// 	$ubigeo_id = intval($ubigeo_id);
			// }
			if (is_int($ubigeo_id)) {
				// Si $ubigeo_id es un entero, lo convertimos a cadena y luego aplicamos str_pad
				$ubigeo_id = str_pad(strval($ubigeo_id), 6, '0', STR_PAD_LEFT);
			} elseif (is_string($ubigeo_id) && strlen($ubigeo_id) === 6) {
				// Si $ubigeo_id es una cadena de 6 caracteres, no hacemos cambios
				// Esto es útil si ya se envía como cadena desde la fuente de datos
			}
			


			//condiciones comerciales
			$eco_tipo_moneda_id = !isset($data[$i]['eco_tipo_moneda_id']) ? '' : $data[$i]['eco_tipo_moneda_id'];
			$eco_monto_renta = !isset($data[$i]['eco_monto_renta']) ? 0 : $data[$i]['eco_monto_renta'];

			$error = "";
			if (!empty($cont_cc_id)) {
				$mysqli->begin_transaction();
				try {
					$query_update = "UPDATE cont_contrato c
										INNER JOIN cont_inmueble i ON c.contrato_id = i.contrato_id 
										SET i.ubigeo_id = '".$ubigeo_id."' WHERE c.c_costos = ".$cont_cc_id;
						$mysqli->query($query_update);
						if ($mysqli->error) {
							$mysqli->rollback();
							$error .= $mysqli->error;
							$result["status"] = 404;
							$result["message"] = 'NO ACTUALIZADO contrato';
							$result["error"] = $error;
							$result["query"] = $query_update;
							echo json_encode($result);
							exit();
						}
						$mysqli->commit();
				} catch (Exception $e) {
						$mysqli->rollback();
						// var_dump("error contrato " . $result["error"]."  ".$e->getMessage());
						echo json_encode("error contrato" . $e->getMessage());
				}
				var_dump($query_update);
				

				$query_update = "UPDATE cont_correlativo SET numero = numero + 1 WHERE tipo_contrato = 6 AND status = 1 ";

				$mysqli->query($query_update);

				if ($mysqli->error) {
					$error .= $mysqli->error;
					$result["status"] = 404;
					$result["message"] = 'correlativo';
					$result["error"] = $error;
					$result["query"] = $query_update;
					echo json_encode($result);
					exit();
				} else {
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
					WHERE tipo_contrato = 6 AND status = 1 LIMIT 1
					";

					$list_query = $mysqli->query($select_correlativo);

					while ($sel = $list_query->fetch_assoc()) {
						$sigla = $sel["sigla"];
						$numero_correlativo = $sel["numero"];
					}

					// INICIO INSERTAR EN CONTRATO
					// Iniciar una transacción
					$mysqli->begin_transaction();

					try {
						// atualizacion de fechas 
						
						$query_insert = " INSERT INTO cont_contrato(
						tipo_contrato_id,
						codigo_correlativo,
						empresa_suscribe_id,
						area_responsable_id,
						persona_responsable_id,
						periodo_numero,
						periodo,
						observaciones,
						etapa_id,
						renovacion_automatica,
						status,
						plazo_id_agente, 	 
						estado_solicitud,
						usuario_responsable_estado_solicitud,
						user_created_id,
						created_at,
						fecha_suscripcion_contrato,
						nombre_agente,
						fecha_inicio_agente,
						fecha_fin_agente,
						c_costos)
						VALUES(
						6,
						" . $numero_correlativo . ",
						1,
						21,
						" . $persona_responsable_id_2 . ",
						" . $periodo_numero . ",
						" . $periodo . ",
						'" . $cont_observaciones . "',
						5,
						".$renovacion_automatica.",
						1,
						1,	
						1,
						0,
						" . $user_created_id . ",
						'" . $created_at . "',
						" . $fecha_suscripcion_contrato . ",
						'" . $nombre_agente . "',
						" . $fecha_inicio_agente . ",
						" . $fecha_fin_agente . ",
						'" . $cont_cc_id . "' )";

						$mysqli->query($query_insert);
						$contrato_id = mysqli_insert_id($mysqli);
						$data_contratos[$i]['contrato_id'] = $contrato_id;
						if ($mysqli->error) {
							$mysqli->rollback();
							$error .= $mysqli->error;
							$result["status"] = 404;
							$result["message"] = 'insert contrato';
							$result["error"] = $error;
							$result["query"] = $query_insert;
							echo json_encode($result);
							exit();
						}
						// FIN INSERTAR EN CONTRATO



						// INICIO GUARDAR INMUEBLE


						$ubigeo_id = $ubigeo_id;

						$query_insert = " INSERT INTO cont_inmueble
									(
									contrato_id,
									ubigeo_id,
									ubicacion,
									area_arrendada,
									num_partida_registral,
									oficina_registral,
									num_suministro_agua,
									tipo_compromiso_pago_agua,
									monto_o_porcentaje_agua,
									num_suministro_luz,
									tipo_compromiso_pago_luz,
									monto_o_porcentaje_luz,
									tipo_compromiso_pago_arbitrios,
									porcentaje_pago_arbitrios,
									latitud,
									longitud,
									user_created_id,
									created_at)
									VALUES
									(
									" . $contrato_id . ",
									'" . $ubigeo_id . "',
									'" . str_replace("'", "", trim($ubicacion)) . "',
									0,
									'',
									'',
									'',
									0,
									0,
									'',
									0,
									0,
									0,
									0,
									'',
									'',
									" . $user_created_id . ",
									'" . $created_at . "'
									)";


						$mysqli->query($query_insert);
						$insert_id = mysqli_insert_id($mysqli);
						if ($mysqli->error) {
							$mysqli->rollback();
							$error .= $mysqli->error . $query_insert;
						}

						// FIN GUARDAR INMUEBLE

						// INICIO GUARDAR CONDICIONES COMERCIAES
						$query_insert_cc = " INSERT INTO cont_cc_agente(
										contrato_id,			 
										participacion_id,
										porcentaje_participacion,
										condicion_comercial_id)
										VALUES(
										" . $contrato_id . ",		 
										1,
										" . $betshop . ",
										" . $betshop_cp . " )";

						$mysqli->query($query_insert_cc);

						if ($mysqli->error) {
							$mysqli->rollback();
							$error .= $mysqli->error . $query_insert_cc;
						}

						$query_insert_cc = " INSERT INTO cont_cc_agente(
										contrato_id,			 
										participacion_id,
										porcentaje_participacion,
										condicion_comercial_id)
										VALUES(
										" . $contrato_id . ",		 
										2,
										" . $juegos . ",
										" . $juegos_cp . " )";

						$mysqli->query($query_insert_cc);

						if ($mysqli->error) {
							$mysqli->rollback();
							$error .= $mysqli->error . $query_insert_cc;
						}

						$query_insert_cc = " INSERT INTO cont_cc_agente(
										contrato_id,			 
										participacion_id,
										porcentaje_participacion,
										condicion_comercial_id)
										VALUES(
										" . $contrato_id . ",		 
										3,
										" . $terminales . ",
										" . $terminales_cp . " )";

						$mysqli->query($query_insert_cc);

						if ($mysqli->error) {
							$mysqli->rollback();
							$error .= $mysqli->error . $query_insert_cc;
						}

						$query_insert_cc = " INSERT INTO cont_cc_agente(
										contrato_id,			 
										participacion_id,
										porcentaje_participacion,
										condicion_comercial_id)
										VALUES(
										" . $contrato_id . ",		 
										4,
										" . $bingo . ",
										" . $bingo_cp . " )";

						$mysqli->query($query_insert_cc);

						if ($mysqli->error) {
							$mysqli->rollback();
							$error .= $mysqli->error . $query_insert_cc;
						}

						$query_insert_cc = " INSERT INTO cont_cc_agente(
									contrato_id,			 
									participacion_id,
									porcentaje_participacion,
									condicion_comercial_id)
									VALUES(
									" . $contrato_id . ",		 
									5,
									" . $deposito . ",
									" . $deposito_cp . " )";

						$mysqli->query($query_insert_cc);

						if ($mysqli->error) {
							$mysqli->rollback();
							$error .= $mysqli->error . $query_insert_cc;
						}
						$query_insert_cc = " INSERT INTO cont_cc_agente(
								contrato_id,			 
								participacion_id,
								porcentaje_participacion,
								condicion_comercial_id)
								VALUES(
								" . $contrato_id . ",		 
								6,
								" . $carreras . ",
								" . $carreras_cp . " )";

						$mysqli->query($query_insert_cc);

						if ($mysqli->error) {
							$mysqli->rollback();
							$error .= $mysqli->error . $query_insert_cc;
						}


						// PROPIETARIOS 
						$codigo = !isset($data[$i]['cc_id']) ? '' : $data[$i]['cc_id'];
						$tipo_persona_id = !isset($data[$i]['tipo_persona']) ? '' : $data[$i]['tipo_persona'];
						$tipo_docu_identidad_id = !isset($data[$i]['tipo_documento']) ? '' : $data[$i]['tipo_documento'];
						$num_docu = !isset($data[$i]['num_documento']) ? '' : $data[$i]['num_documento'];
						$num_ruc = !isset($data[$i]['num_ruc']) ? '' : $data[$i]['num_ruc'];
						$nombre = !isset($data[$i]['nombres']) ? '' : $data[$i]['nombres'];
						$direccion = !isset($data[$i]['direccion']) ? '' : $data[$i]['direccion'];
						$representante_legal = !isset($data[$i]['representante_legal']) ? '' : $data[$i]['representante_legal'];
						$num_partida_registral = !isset($data[$i]['nro_partida_registral']) ? '' : $data[$i]['nro_partida_registral'];
						$contacto_nombre = !isset($data[$i]['nombre_persona_contacto']) ? '' : $data[$i]['nombre_persona_contacto'];
						$contacto_telefono = !isset($data[$i]['telefono_persona_contacto']) ? '' : $data[$i]['telefono_persona_contacto'];
						$contacto_email = !isset($data[$i]['email_persona_contacto']) ? '' : $data[$i]['email_persona_contacto'];
			
						if (!empty($tipo_persona_id)) {
							$tipo_persona_id = explode("|", $tipo_persona_id);
							$tipo_persona_id = trim($tipo_persona_id[0]);
						}
						if (!empty($tipo_docu_identidad_id)) {
							$tipo_docu_identidad_id = explode("|", $tipo_docu_identidad_id);
							$tipo_docu_identidad_id = trim($tipo_docu_identidad_id[0]);
						}
			
						if ($tipo_persona_id == 1) {
							$contacto_nombre = $nombre;
						} else {
							$contacto_nombre = $contacto_nombre;
						}
			
						if (!empty($num_docu)) {
							$num_docu = trim($num_docu) == "" ? "" : $num_docu;
						}
						if (!empty($num_ruc)) {
							$num_ruc = trim($num_ruc) == "" ? "" : $num_ruc;
						}
						if (!empty($representante_legal)) {
							$representante_legal = trim($representante_legal) == "" ? "" : $representante_legal;
						}
						if (!empty($contacto_telefono)) {
							$contacto_telefono = trim($contacto_telefono) == "" ? "" : $contacto_telefono;
						}
						if (!empty($contacto_email)) {
							$contacto_email = trim($contacto_email) == "" ? "" : $contacto_email;
						}
					if (isset($data[$i]['estado_existe'])) {


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
								" . $tipo_persona_id . ",
								" . $tipo_docu_identidad_id . ",
								'" . $num_docu . "',
								'" . $num_ruc . "',
								'" . replace_invalid_caracters(trim($nombre)) . "',
								'" . replace_invalid_caracters(trim($direccion)) . "',
								'" . replace_invalid_caracters(trim($representante_legal)) . "',
								'" . replace_invalid_caracters($num_partida_registral) . "',
								'" . replace_invalid_caracters(trim($contacto_nombre)) . "',
								'" . $contacto_telefono . "',
								'" . $contacto_email . "',
								" . $user_created_id . ",
								'" . $created_at . "')";
						$mysqli->query($query_insert);
						$persona_id = mysqli_insert_id($mysqli);
						$error = '';
						if ($mysqli->error) {
							$error .= $mysqli->error;
							$result["status"] = 404;
							$result["message"] = 'insert persona';
							$result["error"] = $error;
							$result["query"] = $query_insert;
							echo json_encode($result);
							exit();
						}

						$query_insert = "INSERT INTO cont_propietario(
									contrato_id,
									persona_id,
									user_created_id,
									created_at
									)
									VALUES(
									" . $contrato_id . ",
									" . $persona_id . ",
									" . $user_created_id . ",
									'" . $created_at . "')";

						$mysqli->query($query_insert);

						if ($mysqli->error) {
							$mysqli->rollback();
							$error .= $mysqli->error;
							$result["status"] = 404;
							$result["message"] = 'insert propietario';
							$result["error"] = $error;
							$result["query"] = $query_insert;
							echo json_encode($result);
							exit();
						}

					} else {
						$persona_id_propietario = !isset($data[$i]['persona_id']) ? '' : $data[$i]['persona_id'];
						if (!empty($persona_id_propietario)) {
							$persona_id_propietario = explode("|", $persona_id_propietario);
							$persona_id_propietario = trim($persona_id_propietario[0]);
						}



						$query_insert = "INSERT INTO cont_propietario(
									contrato_id,
									persona_id,
									user_created_id,
									created_at
									)
									VALUES(
									" . $contrato_id . ",
									" . $persona_id_propietario . ",
									" . $user_created_id . ",
									'" . $created_at . "')";

						$mysqli->query($query_insert);

						if ($mysqli->error) {
							$mysqli->rollback();
							$error .= $mysqli->error;
							$result["status"] = 404;
							$result["message"] = 'insert propietario existente';
							$result["error"] = $error;
							$result["query"] = $query_insert;
							echo json_encode($result);
							exit();
						}

					}


						$mysqli->commit();
					} catch (Exception $e) {
						$mysqli->rollback();
						// var_dump("error contrato " . $result["error"]."  ".$e->getMessage());
						echo json_encode("error contrato" . $e->getMessage());
					}




					// FIN INSERTAR EN CONDICIONES COMERCIALES


				}
			}
		
	}
	echo "STATUS CONTRATOS";
	echo "<br>";
	$result["status"] = 200;
	$result["contrato_id"] = $contrato_id;
	$result["error"] = $error;
	echo json_encode($result);
	echo "<br>";
	echo "----------------------------";
	echo "<br>";


	// $data_error = [];
	// $error = '';
	// $contrato_id = '';
	// $data = $propietarios_nuevos;
	// for ($i=0; $i < count($data); $i++) {
	// 	// validar si existe el contrato con el ceco
	// 	$existe_contrato =
	// 		"SELECT *from cont_contrato cc 
	// 				where cc.tipo_contrato_id =6  AND  cc.c_costos = " . $data[$i]['c_costos'];


	// 	$list_query = $mysqli->query($existe_contrato);

	// 	if ($list_query && mysqli_num_rows($list_query) > 0) {
	// 		// CONTRATOS EXISTEN 
	// 		echo "actualizarlos";
	// 	} else {
	// 		$contrato_id = "";

	// 		$codigo = !isset($data[$i]['cc_id']) ? '' : $data[$i]['cc_id'];
	// 		$tipo_persona_id = !isset($data[$i]['tipo_persona']) ? '' : $data[$i]['tipo_persona'];
	// 		$tipo_docu_identidad_id = !isset($data[$i]['tipo_documento']) ? '' : $data[$i]['tipo_documento'];
	// 		$num_docu = !isset($data[$i]['num_documento']) ? '' : $data[$i]['num_documento'];
	// 		$num_ruc = !isset($data[$i]['num_ruc']) ? '' : $data[$i]['num_ruc'];
	// 		$nombre = !isset($data[$i]['nombres']) ? '' : $data[$i]['nombres'];
	// 		$direccion = !isset($data[$i]['direccion']) ? '' : $data[$i]['direccion'];
	// 		$representante_legal = !isset($data[$i]['representante_legal']) ? '' : $data[$i]['representante_legal'];
	// 		$num_partida_registral = !isset($data[$i]['nro_partida_registral']) ? '' : $data[$i]['nro_partida_registral'];
	// 		$contacto_nombre = !isset($data[$i]['nombre_persona_contacto']) ? '' : $data[$i]['nombre_persona_contacto'];
	// 		$contacto_telefono = !isset($data[$i]['telefono_persona_contacto']) ? '' : $data[$i]['telefono_persona_contacto'];
	// 		$contacto_email = !isset($data[$i]['email_persona_contacto']) ? '' : $data[$i]['email_persona_contacto'];

	// 		if (!empty($tipo_persona_id)) {
	// 			$tipo_persona_id = explode("|", $tipo_persona_id);
	// 			$tipo_persona_id = trim($tipo_persona_id[0]);
	// 		}
	// 		if (!empty($tipo_docu_identidad_id)) {
	// 			$tipo_docu_identidad_id = explode("|", $tipo_docu_identidad_id);
	// 			$tipo_docu_identidad_id = trim($tipo_docu_identidad_id[0]);
	// 		}

	// 		if ($tipo_persona_id == 1) {
	// 			$contacto_nombre = $nombre;
	// 		} else {
	// 			$contacto_nombre = $contacto_nombre;
	// 		}

	// 		if (!empty($num_docu)) {
	// 			$num_docu = trim($num_docu) == "" ? "" : $num_docu;
	// 		}
	// 		if (!empty($num_ruc)) {
	// 			$num_ruc = trim($num_ruc) == "" ? "" : $num_ruc;
	// 		}
	// 		if (!empty($representante_legal)) {
	// 			$representante_legal = trim($representante_legal) == "" ? "" : $representante_legal;
	// 		}
	// 		if (!empty($contacto_telefono)) {
	// 			$contacto_telefono = trim($contacto_telefono) == "" ? "" : $contacto_telefono;
	// 		}
	// 		if (!empty($contacto_email)) {
	// 			$contacto_email = trim($contacto_email) == "" ? "" : $contacto_email;
	// 		}
	// 		$key = array_search($codigo, array_column($data_contratos, 'codigo'));
	// 		$contrato_id = $data_contratos[$key]['contrato_id'];
	// 		if (!empty($contrato_id)) {

	// 			if (isset($data[$i]['estado_existe'])) {

	// 				// Iniciar una transacción
	// 				$mysqli->begin_transaction();
	// 				try {
	// 					$query_insert = " INSERT INTO cont_persona
	// 			(
	// 			tipo_persona_id,
	// 			tipo_docu_identidad_id,
	// 			num_docu,
	// 			num_ruc,
	// 			nombre,
	// 			direccion,
	// 			representante_legal,
	// 			num_partida_registral,
	// 			contacto_nombre,
	// 			contacto_telefono,
	// 			contacto_email,
	// 			user_created_id,
	// 			created_at)
	// 			VALUES
	// 			(
	// 			" . $tipo_persona_id . ",
	// 			" . $tipo_docu_identidad_id . ",
	// 			'" . $num_docu . "',
	// 			'" . $num_ruc . "',
	// 			'" . replace_invalid_caracters(trim($nombre)) . "',
	// 			'" . replace_invalid_caracters(trim($direccion)) . "',
	// 			'" . replace_invalid_caracters(trim($representante_legal)) . "',
	// 			'" . replace_invalid_caracters($num_partida_registral) . "',
	// 			'" . replace_invalid_caracters(trim($contacto_nombre)) . "',
	// 			'" . $contacto_telefono . "',
	// 			'" . $contacto_email . "',
	// 			" . $user_created_id . ",
	// 			'" . $created_at . "')";
	// 					$mysqli->query($query_insert);
	// 					$persona_id = mysqli_insert_id($mysqli);
	// 					$error = '';
	// 					if ($mysqli->error) {
	// 						$error .= $mysqli->error;
	// 						$result["status"] = 404;
	// 						$result["message"] = 'insert persona';
	// 						$result["error"] = $error;
	// 						$result["query"] = $query_insert;
	// 						echo json_encode($result);
	// 						exit();
	// 					}

	// 					$query_insert = "INSERT INTO cont_propietario(
	// 				contrato_id,
	// 				persona_id,
	// 				user_created_id,
	// 				created_at
	// 				)
	// 				VALUES(
	// 				" . $contrato_id . ",
	// 				" . $persona_id . ",
	// 				" . $user_created_id . ",
	// 				'" . $created_at . "')";

	// 					$mysqli->query($query_insert);

	// 					if ($mysqli->error) {
	// 						$mysqli->rollback();
	// 						$error .= $mysqli->error;
	// 						$result["status"] = 404;
	// 						$result["message"] = 'insert propietario';
	// 						$result["error"] = $error;
	// 						$result["query"] = $query_insert;
	// 						echo json_encode($result);
	// 						exit();
	// 					}
	// 					$mysqli->commit();
	// 				} catch (Exception $e) {
	// 					$mysqli->rollback();
	// 					echo json_encode("error propietarios nuevos " . $e->getMessage());
	// 					// var_dump("error propietarios nuevos " . $e->getMessage());
	// 				}
	// 			} else {
	// 				$persona_id_propietario = !isset($data[$i]['persona_id']) ? '' : $data[$i]['persona_id'];
	// 				if (!empty($persona_id_propietario)) {
	// 					$persona_id_propietario = explode("|", $persona_id_propietario);
	// 					$persona_id_propietario = trim($persona_id_propietario[0]);
	// 				}
	// 				// Iniciar una transacción
	// 				$mysqli->begin_transaction();
	// 				try {


	// 					$query_insert = "INSERT INTO cont_propietario(
	// 					contrato_id,
	// 					persona_id,
	// 					user_created_id,
	// 					created_at
	// 					)
	// 					VALUES(
	// 					" . $contrato_id . ",
	// 					" . $persona_id_propietario . ",
	// 					" . $user_created_id . ",
	// 					'" . $created_at . "')";

	// 					$mysqli->query($query_insert);

	// 					if ($mysqli->error) {
	// 						$mysqli->rollback();
	// 						$error .= $mysqli->error;
	// 						$result["status"] = 404;
	// 						$result["message"] = 'insert propietario existente';
	// 						$result["error"] = $error;
	// 						$result["query"] = $query_insert;
	// 						echo json_encode($result);
	// 						exit();
	// 					}
	// 					$mysqli->commit();
	// 				} catch (Exception $e) {
	// 					$mysqli->rollback();
	// 					echo json_encode("error propietarios existente " . $e->getMessage());
	// 					// var_dump("error propietarios nuevos " . $e->getMessage());
	// 				}
	// 			}
	// 		} else {
	// 			array_push($data_error, $codigo);
	// 		}
	// 	}
		

		
	// }
	
	// echo "STATUS PROPIETARIOS NUEVOS";
	// echo "<br>";
	// $result["status"] = 200;
	// $result["contrato_id"] = $contrato_id;
	// $result["error"] = $error;
	// echo json_encode($result);
	// echo "<br>";
	// echo "----------------------------";
	// echo "<br>";

	

 
}
if (isset($_POST["accion"]) && $_POST["accion"]==="contrato_archivos_agentes") {

	$folder_import = array();
	$dir_path = "/var/www/html/files_bucket/importacion/";
	$folder_master  = scandir($dir_path);
	foreach ($folder_master as $key => $value) {
		if ($key > 1) {		
			$dir_path1 = $dir_path.$value;
			$sub_folder_1  = scandir($dir_path1);
			foreach ($sub_folder_1 as $key2 => $value2) {
				if ($key2 > 1) {
					$file = explode("-", $value2);
					// Supongamos que tienes el primer elemento del array $file como "AG10".
					$firstItem = $file[0];

					// Utiliza una expresión regular para encontrar y extraer los dígitos numéricos.
					if (preg_match('/\d+/', $firstItem, $matches)) {
						$code_correlativo = $matches[0]; // $numericValue contendrá "10" como una cadena.
						// Si deseas convertirlo a un valor numérico (entero), puedes hacerlo así:
						$code_correlativo = intval($matches[0]); // $numericValue contendrá 10 como un entero.
					} else {
						// Manejar el caso en el que no se encontraron dígitos numéricos.
						$code_correlativo = 0; // O cualquier otro valor predeterminado que desees.
					}

					$name_file = $value2;
					$route = $dir_path1."/".$name_file;
					$weight = filesize($route);
					$extension = new SplFileInfo($route);

					$filename = explode(".", trim($file[2]));
					array_push($folder_import, array(
						'type_request' => $value,
						'code' => $code_correlativo,
						'id_type_file' => trim($file[1]),
						'name' => $filename[0],
						'route' => $route,
						'weight' => $weight,
						'extension' => $extension->getExtension(),
					));
				}
			}
		}
		
	}
	// var_dump($folder_import);

	$error = array();
	$error_sql_import = array();
	$usuario_id = 1;
	$created_at = date('Y-m-d H:i:s');$cont=0;
	for ($i=0; $i < count($folder_import) ; $i++) { 
		$contrato_id = "";
		// $query_contrato = "SELECT contrato_id FROM cont_contrato WHERE cc_id = '".$folder_import[$i]['code']."' AND status = 1 LIMIT 1";
		$query_contrato = "SELECT contrato_id FROM cont_contrato WHERE codigo_correlativo = '".$folder_import[$i]['code']."'  AND status = 1
		AND tipo_contrato_id = 6
		LIMIT 1";
		echo $query_contrato;
		$list_query = $mysqli->query($query_contrato);
		while($sel = $list_query->fetch_assoc()){ $contrato_id = $sel["contrato_id"]; }

		if (!Empty($contrato_id)) {
			$nombre_archivo = $contrato_id . "_".$folder_import[$i]['name']."_" . date('YmdHis') . "." . $folder_import[$i]['extension'];
			$new_path = "/var/www/html/files_bucket/contratos/".$folder_import[$i]['type_request']."/"."agentes/";
			$new_route = $new_path.$nombre_archivo;$cont=$cont+1;
						// var_dump($contrato_id,$cont);
						var_dump($folder_import[$i]['route'],$new_route);

			if (rename($folder_import[$i]['route'],$new_route)) {
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
					".$folder_import[$i]['id_type_file'].",
					'" . $nombre_archivo . "',
					'" . $folder_import[$i]['extension'] . "',
					'" . $folder_import[$i]['weight'] . "',
					'" . $new_path . "',
					" . $usuario_id . ",
					'" . $created_at . "'
					)";
				$mysqli->query($comando);
				if($mysqli->error){
					array_push($error_sql_import, array('error' => 'Error al guardar la partida registral: ' . $mysqli->error, 'sql' => $comando));
				}
			}else{
				array_push($error_copy_file,
					array('route' => $route, 'route_new' => $new_route)
				);
			}
		}else{
			array_push($error,$folder_import['code']);
		}

	}


	$result['folder_import'] = $folder_import;
	$result['error'] = $error;
	$result['error_sql_import'] = $error_sql_import;

	echo json_encode($error_sql_import);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="contrato_actualizar_area") {
	$error = 'Correcto';
	$cont	= 0;
	$error_sql_import = array();

	$query = "SELECT cc.contrato_id, cc.user_created_id, codigo_correlativo,
                 p.area_id, a.nombre, cc.created_at
          FROM cont_contrato cc
          INNER JOIN tbl_usuarios u ON cc.user_created_id = u.id
          LEFT JOIN tbl_personal_apt p ON u.personal_id = p.id
          LEFT JOIN tbl_areas a ON p.area_id = a.id
          WHERE cc.tipo_contrato_id = 2 AND cc.status = 1
          ORDER BY cc.created_at DESC";
	
	$resultado = $mysqli->query($query);

	if ($resultado) {
		while ($fila = $resultado->fetch_assoc()) {
			$contrato_id = $fila['contrato_id'];
			$area_id = $fila['area_id'];
	
			// Actualizar el campo area_responsable_id en la tabla cont_contrato
			$updateQuery = "UPDATE cont_contrato SET area_responsable_id = $area_id WHERE contrato_id = $contrato_id";
			$mysqli->query($updateQuery);
			if($mysqli->error){
				array_push($error_sql_import, array('error' => 'Error al actualizar: ' . $mysqli->error));
			}else{
				$cont ++;

			}
		}
	} else {
		$error  =  $mysqli->error;
		echo "Error en la consulta: " . $mysqli->error;
	}

	

	echo "STATUS INMUEBLE";
	echo "<br>";
	$result["status"] = 200;
	$result['cantidad'] = $cont;

	$result['error_general'] = $error;
	$result['error_sql_update'] = $error_sql_import;
	echo json_encode($result);
	echo "<br>";
	echo "----------------------------";
	echo "<br>";
	exit();
	exit();
}
?>