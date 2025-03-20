<?php
include("sys/db_connect.php");
include("sys/sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function num_cuenta_18_a_20_digitos($num_cuenta_18) {
	$num_cuenta_solo_numeros = trim(str_replace('-','',$num_cuenta_18));

	// if (strlen($num_cuenta_solo_numeros) != 18) {
	// 	exit('El Número de cuenta ' . $num_cuenta_solo_numeros . ' no es de 18 dígitos es de ' . strlen($num_cuenta_solo_numeros) . 'dígitos, corregir.');
	// }

	$cad1 = intval(substr($num_cuenta_solo_numeros,4,1)) * 1;
	$cad1 = $cad1 . (intval(substr($num_cuenta_solo_numeros,5,1)) * 2);
	$cad1 = $cad1 . (intval(substr($num_cuenta_solo_numeros,6,1)) * 1);
	$cad1 = $cad1 . (intval(substr($num_cuenta_solo_numeros,7,1)) * 2);
	$valor = 3;

	for ($i = 0; $i < strlen($cad1); $i++) {
		$valor = $valor + substr($cad1,$i,1);
	}

	$dig1 = (intval($valor/10) + 1) * 10 - $valor;
	$dig1 = substr($dig1, -1, 1);

	$cad2 = "";

	for ($i = 8; $i < 18; $i+=2) {
		$cad2 = $cad2 . (intval(substr($num_cuenta_solo_numeros,$i,1)) * 1);
		$cad2 = $cad2 . (intval(substr($num_cuenta_solo_numeros,$i + 1,1)) * 2);
	}

	$valor = 0;

	for ($i = 0; $i < strlen($cad2); $i++) {
		$valor = $valor + substr($cad2,$i,1);
	}

	$dig2 = (intval($valor/10) + 1) * 10 - $valor;
	$dig2 = substr($dig2, -1, 1);

	$dc = $dig1 . $dig2;

	return substr($num_cuenta_solo_numeros,0,8) . $dc . substr($num_cuenta_solo_numeros,8,10);
}

function str_pad_unicode($str, $pad_len, $pad_str = ' ', $dir = STR_PAD_RIGHT) {
	$str_len = mb_strlen($str);
	$pad_str_len = mb_strlen($pad_str);

	if (!$str_len && ($dir == STR_PAD_RIGHT || $dir == STR_PAD_LEFT)) {
		$str_len = 1;
	}

	if (!$pad_len || !$pad_str_len || $pad_len <= $str_len) {
		return $str;
	}

	$result = null;
	$repeat = ceil($str_len - $pad_str_len + $pad_len);

	if ($dir == STR_PAD_RIGHT) {
		$result = $str . str_repeat($pad_str, $repeat);
		$result = mb_substr($result, 0, $pad_len);
	} else if ($dir == STR_PAD_LEFT) {
		$result = str_repeat($pad_str, $repeat) . $str;
		$result = mb_substr($result, -$pad_len);
	} else if ($dir == STR_PAD_BOTH) {
		$length = ($pad_len - $str_len) / 2;
		$repeat = ceil($length / $pad_str_len);
		$result = mb_substr(str_repeat($pad_str, $repeat), 0, floor($length))
		. $str
		. mb_substr(str_repeat($pad_str, $repeat), 0, ceil($length));
	}

	return $result;
}

function eliminar_acentos($cadena){

	$cadena = str_replace(
	array('Á', 'À', 'Â', 'Ä', 'á', 'à', 'ä', 'â', 'ª'),
	array('A', 'A', 'A', 'A', 'a', 'a', 'a', 'a', 'a'),
	$cadena
	);

	$cadena = str_replace(
	array('É', 'È', 'Ê', 'Ë', 'é', 'è', 'ë', 'ê'),
	array('E', 'E', 'E', 'E', 'e', 'e', 'e', 'e'),
	$cadena );

	$cadena = str_replace(
	array('Í', 'Ì', 'Ï', 'Î', 'í', 'ì', 'ï', 'î'),
	array('I', 'I', 'I', 'I', 'i', 'i', 'i', 'i'),
	$cadena );

	$cadena = str_replace(
	array('Ó', 'Ò', 'Ö', 'Ô', 'ó', 'ò', 'ö', 'ô'),
	array('O', 'O', 'O', 'O', 'o', 'o', 'o', 'o'),
	$cadena );

	$cadena = str_replace(
	array('Ú', 'Ù', 'Û', 'Ü', 'ú', 'ù', 'ü', 'û'),
	array('U', 'U', 'U', 'U', 'u', 'u', 'u', 'u'),
	$cadena );

	$cadena = str_replace(
	array('Ñ', 'ñ', 'Ç', 'ç'),
	array('N', 'n', 'C', 'c'),
	$cadena
	);

	return $cadena;
}

function downloadAsCsvFile( array &$csvTitle, array &$csvData, $fileName='' ) {
	if(empty($fileName)){ // Guardar nombre de archivo
	  $fileName = 'download_'.date('Ymd_His');
	}
	header('Content-type: application/csv');
	header('Content-Transfer-Encoding: binary; charset=utf-8');
	header('Content-Disposition: attachment; filename='.$fileName.'.csv');
	$fp = fopen("php://output", 'w');
	// Generar encabezado BOM
	fputs($fp, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));
	// Generar barra de título
	fputcsv($fp, $csvTitle);
	foreach ($csvData as $row) {
		fputcsv($fp, $row);
	}
	exit();
}

function downloadAsTxtFile( array &$txtTitle,  array &$txtData, $fileName='' ) {
	if(empty($fileName)){ // Guardar nombre de archivo
	$fileName = 'download_'.date('Ymd_His');
	}

	header("Content-Disposition: attachment; filename={$fileName}.txt");
	header("Content-Type: charset=utf-8");
	$fp = fopen("php://output", 'w');

	if (count($txtTitle) > 0) {
		$titleLine = implode("", $txtTitle) . "\n";
		fputs($fp, $titleLine, strlen($titleLine));
	}

	foreach ($txtData as $row) {
		// $line = implode("\t", $row)."\n";
		$line = implode("", $row)."\n";
		fputs($fp, $line, strlen($line));
	}
	exit();
}

$programacion_id = $_GET["id"];


// INICIO OBTENER PROGRAMACION
$query_programacion = "
SELECT 
	nc.num_cuenta_corriente,
	(
		CASE 
			WHEN p.moneda_id = 1 THEN 'PEN' 
			WHEN p.moneda_id = 2 THEN 'USD' 
		END
	) AS moneda,
	p.importe,
	p.tipo_programacion_id,
	p.process_at
FROM 
	cont_programacion p
	INNER JOIN cont_num_cuenta nc ON p.num_cuenta_id = nc.id
WHERE 
	p.id = $programacion_id
";

$list_query = $mysqli->query($query_programacion);

if($mysqli->error){
	exit($mysqli->error . $query_programacion);
}

$row_count = $list_query->num_rows;

if ($row_count > 0) {
	$row = $list_query->fetch_assoc();
	$num_cuenta_corriente = str_replace('-', '', trim($row["num_cuenta_corriente"]) );
	$moneda = trim($row["moneda"]);
	$importe = trim($row["importe"]);
	$tipo_programacion_id = trim($row["tipo_programacion_id"]);
	$process_at = date('d-m-y', strtotime($row["process_at"]));
} else {
	exit('No se puede obtener la cabecera de la programación');
}
// FIN OBTENER PROGRAMACION


// INICIO OBTENER DETALLE PROGRAMACION
$query_programacion_detalle = "
SELECT 
  	c.contrato_id,
	p.id,
	p.num_ruc AS num_ruc,
	td.nombre AS num_ruc_arrendador,
	b.nombre AS acreedor,
	b.num_docu,
	p.mes,
	p.anio,
	p.periodo_fin AS fecha_vencimiento,
	(
		CASE
			WHEN ce.tipo_moneda_id = 1 THEN 'MN'
			WHEN ce.tipo_moneda_id = 2 THEN 'ME'
		END
	) AS moneda,
	(
		CASE
			WHEN b.banco_id = 12 THEN 'P'
			ELSE 'I'
		END
	) AS tipo_cuenta_bancaria,
	b.num_cuenta_bancaria,
	b.num_cuenta_cci,
	p.importe AS programado,
	p.mes AS periodo,
	p.periodo_inicio AS registro_mes,
	c.cc_id AS centro_de_costos,
	c.nombre_tienda,
	rs.nombre AS razon_social,
	p.renta_bruta,
	ce.tipo_moneda_id
FROM
	cont_programacion_detalle pd
	INNER JOIN cont_provision p ON pd.provision_id = p.id
	INNER JOIN cont_condicion_economica ce ON p.condicion_economica_id = ce.condicion_economica_id
	LEFT JOIN cont_beneficiarios b ON ce.contrato_id = b.contrato_id
	LEFT JOIN cont_tipo_persona tp ON b.tipo_persona_id = tp.id
	LEFT JOIN cont_tipo_docu_identidad td ON b.tipo_docu_identidad_id = td.id
	LEFT JOIN cont_contrato c ON ce.contrato_id = c.contrato_id
	LEFT JOIN tbl_razon_social rs ON p.empresa_id = rs.id
WHERE 
	pd.programacion_id = $programacion_id
	AND pd.status = 1
	AND p.status = 1
	AND b.status = 1
";

$list_query_detalle = $mysqli->query($query_programacion_detalle);

if($mysqli->error){
	exit($mysqli->error . $query_programacion_detalle);
}

$row_count_detalle = $list_query_detalle->num_rows;

if ($row_count_detalle > 0) {
	$data = [];
	
	while ($row = $list_query_detalle->fetch_assoc()) {

		$registro_mes = substr(trim($row["registro_mes"]), 0, 4);
		$moneda_id = trim($row["tipo_moneda_id"]);

		if ($tipo_programacion_id == "2") {

			$tipo_cuenta_bancaria = trim($row["tipo_cuenta_bancaria"]);
			$num_cuenta_bancaria = trim($row["num_cuenta_bancaria"]);
			$num_cuenta_cci = trim($row["num_cuenta_cci"]);
			$num_docu = trim($row["num_docu"]);
			$acreedor = strtoupper(eliminar_acentos(implode(' ',array_filter(explode(' ',$row["acreedor"])))));

			if(isset($num_cuenta_bancaria) && !empty($num_cuenta_bancaria)){
				if ($tipo_cuenta_bancaria == 'P') {
					$num_cuenta = num_cuenta_18_a_20_digitos($num_cuenta_bancaria);
				} else if ($tipo_cuenta_bancaria == 'I') {
					$num_cuenta = $num_cuenta_cci;
				} 
			}else{
				$num_cuenta = '';
			}
			// $num_cuenta = '';
			if(isset($num_docu) && !empty($num_docu)){
				if (strlen($num_docu) == 8) {
					$sigla_num_docu = 'L';
				} elseif (strlen($num_docu) == 11) {
					$sigla_num_docu = 'R';
				} elseif(strlen($num_docu) == 9) {
					$sigla_num_docu = 'P';

				}else{

					exit($row["centro_de_costos"]." ".'El número de documento ' . $num_docu . ' de ' . strlen($num_docu) . ' dígitos no es número de RUC ni DNI' );
				}
	
			}else{
				$sigla_num_docu = 'N';
			}
			
			$data[] = array(
				'A'=> '002' . $sigla_num_docu . $num_docu,  // 16
				'B'=> $tipo_cuenta_bancaria . $num_cuenta . $acreedor, //61
				'C'=> str_pad(str_replace(".", "", $row["programado"]), 15, "0", STR_PAD_LEFT)
				 . 'F31' 
				 . str_pad(trim($row["mes"]), 2, "0", STR_PAD_LEFT) . trim($row["anio"]),//28
				'D'=> 'N'.strtoupper(substr($row["nombre_tienda"], 0, 20)),  //122
				'E'=> '00000000000000000000000000000000'
			);

		} elseif ($tipo_programacion_id == "3") {

			if ($moneda_id == 2) {

				$ultimo_dia_del_mes_del_vencimiento = date('Y-m-t', strtotime($row['fecha_vencimiento']));

				$select_tipo_de_cambio = "
				SELECT
					monto_venta
				FROM
					tbl_tipo_cambio
				WHERE
					fecha = '$ultimo_dia_del_mes_del_vencimiento'
					AND moneda_id = 2
				";

				$sel_query = $mysqli->query($select_tipo_de_cambio);
				$row_count = $sel_query->num_rows;

				if ($row_count > 0) {
					$row_tipo_de_cambio       = $sel_query->fetch_assoc();
					$valor_del_tipo_de_cambio = $row_tipo_de_cambio['monto_venta'];
				} else {
					$valor_del_tipo_de_cambio = 'No existe tipo de cambio el ' . $ultimo_dia_del_mes_del_vencimiento;
					exit($valor_del_tipo_de_cambio);
				}

				$renta_bruta = $row["renta_bruta"] * $valor_del_tipo_de_cambio;

			} else {
				$renta_bruta = $row["renta_bruta"];
			}

			if (trim($row["num_ruc"]) == '' || ( (int) (trim($row["num_ruc"])) ) == 0) {
				$num_ruc_txt = $row["num_ruc_arrendador"];
			} else {
				$num_ruc_txt = $row["num_ruc"];
			}

			$data_ir[] = array(
				'A'=>trim($num_ruc_txt), 
				'B'=>str_pad(trim($row["periodo"]), 2, "0", STR_PAD_LEFT),
				'C'=>$registro_mes,
				'D'=> 'N',
				'E'=> str_pad(round($renta_bruta), 12, "0", STR_PAD_LEFT) 
			);

		}

	}
} else {
	exit('No se puede obtener detalle programaciónlll'.$programacion_id);
}
// FIN OBTENER DETALLE PROGRAMACION

if ($tipo_programacion_id == "2") {

	$filename = 'TLBBVACONT'.date('YmdHis');
	$fecha = DateTime::createFromFormat("d-m-y", $process_at);
    
    // Obtener la fecha formateada en el formato "Ymd"
	

    $fecha_proceso = $fecha->format("Ymd");
	$download_title = array(
		str_pad('750' . num_cuenta_18_a_20_digitos($num_cuenta_corriente) . $moneda . str_pad(str_replace(".", "", $importe), 15, "0", STR_PAD_LEFT) .
		 'A'.$fecha_proceso."B"."Proveedores".str_repeat(' ', 14).str_pad($row_count_detalle, 6, "0", STR_PAD_LEFT)."S000000000000000000"   , 151), 
		// str_pad('Pago Prooved. '. $process_at, 25), 
		// str_pad(str_pad($row_count_detalle, 6, "0", STR_PAD_LEFT) .'S', 75)
	);
	 
	$download_data  = array();

	foreach ($data as $key => $row) {
		$download_data[$key]['A']   = str_pad(trim($row['A']), 16);
		$download_data[$key]['B']   = str_pad(trim($row['B']), 61);
		$download_data[$key]['C']   = str_pad(trim($row['C']), 28);
		$download_data[$key]['D']   = str_pad(trim($row['D']), 122);
		$download_data[$key]['E']   = str_pad(trim($row['E']), 50);
	}

} else if ($tipo_programacion_id == "3") {

	$filename = 'TLSCOTIABANK'.date('YmdHis');
	$download_title = array();
	$download_data  = array();

	foreach ($data_ir as $key => $row) {
		$download_data[$key]['A']   = str_pad(' ', 1);
		$download_data[$key]['B']   = str_pad($row['A'], 11);
		$download_data[$key]['C']   = str_pad($row['B'], 2);
		$download_data[$key]['D']   = str_pad($row['C'], 4);
		$download_data[$key]['E']   = str_pad($row['D'], 11);
		$download_data[$key]['F']   = str_pad($row['E'], 12);
	}

}
// Exportar archivo TXT
downloadAsTxtFile($download_title, $download_data, $filename);

// Exportar archivo CSV
//downloadAsCsvFile($download_title, $download_data, $filename);
?> 