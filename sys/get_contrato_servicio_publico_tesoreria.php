<?php
include("db_connect.php");
include("sys_login.php");
require('/var/www/html/sys/globalFunctions/generalInfo/local.php');
include '/var/www/html/sys/globalFunctions/generalInfo/parameterGeneral.php';


if (isset($_POST["accion"]) && $_POST["accion"] === "exportar_text_detalle") {
	$programacion_id = $_POST['programacion_id'];
	$fecha_filename = date('dm');

    // Guardar el contenido en el archivo
    try {

		$selectQuery = "SELECT
						cis.nombre_beneficiario,
						cis.nro_documento_beneficiario,
						cis.nro_cuenta_soles,
						rs.id,
						sp.total_pagar,
						tsp.nombre,
						l.nombre
					FROM cont_ser_pub_programacion_detalle pd
						INNER JOIN cont_ser_pub_programacion p 
						ON p.id= pd.cont_ser_pub_programacion_id
						INNER JOIN tbl_razon_social rs
						ON p.tipo_empresa_id = rs.id
						INNER JOIN cont_local_servicio_publico sp
						ON pd.cont_local_servicio_publico_id = sp.id
						INNER JOIN tbl_locales l
						ON sp.id_local = l.id
						INNER JOIN cont_inmueble_suministros cis
						ON sp.inmueble_suministros_id = cis.id
						INNER JOIN cont_inmueble i
						ON cis.inmueble_id = i.id
						INNER JOIN cont_tipo_pago_servicio tps
						ON cis.tipo_compromiso_pago_id = tps.id
						INNER JOIN cont_tipo_servicio_publico tsp
						ON sp.id_tipo_servicio_publico = tsp.id
						INNER JOIN cont_tipo_estado_servicio_publico AS esp 
						ON sp.estado = esp.id
						LEFT JOIN cont_local_servicio_publico_empresas ea
						ON i.id_empresa_servicio_agua = ea.id
						LEFT JOIN cont_local_servicio_publico_empresas el
						ON i.id_empresa_servicio_luz = el.id
						LEFT JOIN tbl_usuarios_locales tul 
						on tul.local_id = l.id
					WHERE pd.status = 1 
					AND pd.cont_ser_pub_programacion_id = ?
					GROUP BY pd.id";

		$selectStmt = $mysqli->prepare($selectQuery);
		$selectStmt->bind_param("i", $programacion_id);
		$selectStmt->execute();
		$selectStmt->store_result();


		$importe_total_cabecera =0;
		$cantidad_total_cabecera =0;
		$fecha_cabecera = date('Ymd');

		if ($selectStmt->num_rows > 0) {
			$selectStmt->bind_result($nombre_beneficiario, $nro_documento_beneficiario, $nro_cuenta_soles, $razon_social_id, $total_pagar, $tipo_servicio, $nombre_local);
			$registros = array();

			while ($selectStmt->fetch()) {
				$importe_total_cabecera += $total_pagar;
				$cantidad_total_cabecera ++;


				$monto = trim($total_pagar);
				$num_cuenta = trim($nro_cuenta_soles);
				$primeros_4_digitos_cuenta = substr($num_cuenta, 0, 4);

				$documento_beneficiario = trim($nro_documento_beneficiario);
				$beneficiario = strtoupper(eliminar_acentos(implode(' ',array_filter(explode(' ',$nombre_beneficiario)))));

				if ($primeros_4_digitos_cuenta == "0011") 
				{
					$num_cuenta = num_cuenta_18_a_20_digitos($num_cuenta);
					$tipo_cuenta_bancaria = 'P';
				} 
				else
				{
					$tipo_cuenta_bancaria = 'I';
				}

				if (strlen($documento_beneficiario) == 8) 
				{
					$sigla_num_docu = 'L';
				}
				else 
				{
					$sigla_num_docu = 'R';
				}

				$data[] = array(
					'A' => '002'. $sigla_num_docu. $documento_beneficiario, 
					'B' => $tipo_cuenta_bancaria . $num_cuenta . $beneficiario, 
					'C' => str_pad(str_replace(".", "", $monto), 15, "0", STR_PAD_LEFT) . 'F' . $tipo_servicio,
					'D' => 'N' . $nombre_local,
					'E' => '',
					'F'=> str_pad(0, 32, "0", STR_PAD_RIGHT)
				);

				/// ---------------------- Cambiar formato de datos
				
			}


			//-------------------------- Consulta cuenta contable ----------------------

			$proceso_pago_servicios_tesoreria = "PAGO SERVICIOS";
			$selectQuery = "SELECT num_cuenta_corriente, m.sigla
							FROM cont_num_cuenta nc
							LEFT JOIN cont_num_cuenta_proceso ncp ON ncp.id =nc.cont_num_cuenta_proceso_id
							LEFT JOIN tbl_moneda m ON m.id = nc.moneda_id	
							WHERE ncp.nombre= ? AND nc.razon_social_id = ?";

			$selectStmt = $mysqli->prepare($selectQuery);
			$selectStmt->bind_param("si", $proceso_pago_servicios_tesoreria,$razon_social_id);
			$selectStmt->execute();
			$selectStmt->store_result();

			if ($selectStmt->num_rows > 0) {
				$selectStmt->bind_result($num_cuenta_corriente,$monega_sigla);
				$selectStmt->fetch();

				// TITULO DE ARCHIVO TXT
				$importe_total_cabecera_formato= number_format($importe_total_cabecera, 2, '.', '');
				$download_title = array(
					str_pad('750' . $num_cuenta_corriente . $monega_sigla . str_pad(str_replace(".", "", $importe_total_cabecera_formato), 15, "0", STR_PAD_LEFT) . 'A', 42), 
					str_pad($fecha_cabecera . 'BSERV COMPARTIDOS', 34), 
					str_pad(str_pad($cantidad_total_cabecera, 6, "0", STR_PAD_LEFT) .'S000000000000000000', 75)
				);

			}else{
				echo json_encode(array(
					"error" => "No existe cuenta contable",
					"estado_archivo" => 0
					));
				exit;
			}

			//--------------------------------------------------------------------------

			$contenido = implode($download_title);
			$contenido .= "\n";  // Asegúrate de agregar una nueva línea entre el título y los registros


			$download_data  = array();

				foreach ($data as $key => $row) 
				{
					$download_data[$key]['A']   = str_pad(trim($row['A']), 16);
					$download_data[$key]['B']   = str_pad(trim($row['B']), 61);
					$download_data[$key]['C']   = str_pad(trim($row['C']), 28);
					$download_data[$key]['D']   = str_pad(trim($row['D']), 41);
					$download_data[$key]['E']   = str_pad(trim($row['E']), 51);
					$download_data[$key]['F']   = str_pad(trim($row['F']), 80);
				}
			
			foreach ($download_data as $row) {
				$contenido .= implode("", $row) . "\n";  // Agregar una nueva línea después de cada fila
			}
			
/*
			$download_data  = array();

				foreach ($data as $key => $row) 
				{
					$download_data[$key]['A']   = str_pad(trim($row['A']), 16);
					$download_data[$key]['B']   = str_pad(trim($row['B']), 61);
					$download_data[$key]['C']   = str_pad(trim($row['C']), 28);
					$download_data[$key]['D']   = str_pad(trim($row['D']), 41);
					$download_data[$key]['E']   = str_pad(trim($row['E']), 51);
					$download_data[$key]['F']   = str_pad(trim($row['F']), 80);
				}
			*/


			$razon_social_igh = getParameterGeneral('razon_social_igh');

			//$contenido .= implode($download_data);
			if ($razon_social_id == $razon_social_igh ){
				$siglas_empresa = "IGH";
			}else{
				$siglas_empresa = "FG";
			}
			// Definir el nombre del archivo
			$filename = "BBVA".$siglas_empresa.$fecha_filename."ServCompartido.txt";
			$txt_path = '/var/www/html/files_bucket/mepa/descargas/' . $filename;
			$txt_path_download = '/files_bucket/mepa/descargas/' . $filename;

			
			file_put_contents($txt_path, $contenido);

			echo json_encode(array(
				"ruta_archivo" => $txt_path_download,
				"filename" => $filename,
				"estado_archivo" => 1
				));
			exit;
		} else {
			echo json_encode(array(
				"error" => "No se registraron datos del beneficiario",
				"estado_archivo" => 0
				));
			exit;
		}

    } catch (Exception $e) {
        echo json_encode(["error" => $e->getMessage()]);
        exit;
    }
}

function eliminar_acentos($cadena)
{

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

function num_cuenta_18_a_20_digitos($num_cuenta_18) 
{
	$num_cuenta_solo_numeros = trim(str_replace('-','',$num_cuenta_18));

	if (strlen($num_cuenta_solo_numeros) != 18) 
	{
		echo json_encode(array(
			"error" => "'El Número de cuenta ".$num_cuenta_solo_numeros." debe contener 18 y no 20 digitos, corregir.",
			"estado_archivo" => 0
			));
	}

	$cad1 = intval(substr($num_cuenta_solo_numeros,4,1)) * 1;
	$cad1 = $cad1 . (intval(substr($num_cuenta_solo_numeros,5,1)) * 2);
	$cad1 = $cad1 . (intval(substr($num_cuenta_solo_numeros,6,1)) * 1);
	$cad1 = $cad1 . (intval(substr($num_cuenta_solo_numeros,7,1)) * 2);
	$valor = 3;

	for ($i = 0; $i < strlen($cad1); $i++) 
	{
		$valor = $valor + substr($cad1,$i,1);
	}

	$dig1 = (intval($valor/10) + 1) * 10 - $valor;
	$dig1 = substr($dig1, -1, 1);

	$cad2 = "";

	for ($i = 8; $i < 18; $i+=2) 
	{
		$cad2 = $cad2 . (intval(substr($num_cuenta_solo_numeros,$i,1)) * 1);
		$cad2 = $cad2 . (intval(substr($num_cuenta_solo_numeros,$i + 1,1)) * 2);
	}

	$valor = 0;

	for ($i = 0; $i < strlen($cad2); $i++) 
	{
		$valor = $valor + substr($cad2,$i,1);
	}

	$dig2 = (intval($valor/10) + 1) * 10 - $valor;
	$dig2 = substr($dig2, -1, 1);

	$dc = $dig1 . $dig2;

	return substr($num_cuenta_solo_numeros,0,8) . $dc . substr($num_cuenta_solo_numeros,8,10);
}


?>