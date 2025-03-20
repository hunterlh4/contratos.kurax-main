<?php
	if(isset($_POST["sec_caja_agentes_concar_excel"])){
		$get_data = $_POST["sec_caja_agentes_concar_excel"];
		$return = array();
		$return["memory_init"]=memory_get_usage();
		$return["time_init"] = microtime(true);
		date_default_timezone_set("America/Lima");
		include("../sys/global_config.php");
		include("../sys/db_connect.php");
		include("../sys/sys_login.php");

		$local_id = $get_data["concar_local_id"];
		$tipo_cambio = $get_data["tipo_cambio"];
		$correlativo_inicial = $get_data["correlativo_inicial"];
		$fecha_inicio = date("Y-m-d",strtotime($get_data["sec_caja_resumen_agentes_fecha_inicio_concar"]));
		$fecha_inicio_pretty = date("d-m-Y",strtotime($get_data["sec_caja_resumen_agentes_fecha_inicio_concar"]));
		$fecha_fin = date("Y-m-d",strtotime($get_data["sec_caja_resumen_agentes_fecha_fin_concar"]));
		$fecha_fin = date('Y-m-d', strtotime("+1 day", strtotime($fecha_fin)));
		$fecha_fin_pretty = date("d/m/Y",strtotime($get_data["sec_caja_resumen_agentes_fecha_fin_concar"]));

		$fecha_query  =  " AND pd.fecha_voucher >= '" . $fecha_inicio . "'";
		$fecha_query .=  " AND pd.fecha_voucher < '" . $fecha_fin . "'";

		$local_query = "";
		if( $local_id != "_all_" && $local_id != "_all_terminales_" )
		{
			$local_query = " AND l.id = '".$local_id."'";
		}
		/*$fecha_query = "AND tta.fecha_operacion >= '".$fecha_inicio."'
							AND tta.fecha_operacion < '".$fecha_fin."'
							ORDER BY tta.fecha_operacion ASC, l.nombre ASC";*/

		if($local_id != "" ){
			$locales = array();
			$local_titulo = array();
			$sql_command = "
				SELECT l.id,
					l.nombre,
					(
						SELECT cli.ruc FROM tbl_contratos c
						LEFT JOIN tbl_clientes cli ON cli.id = c.cliente_id
						WHERE c.local_id = l.id
	                    AND cli.estado = 1
	                    LIMIT 1
					)
					AS ruc
				FROM tbl_locales l
				WHERE l.id NOT IN (1)
				AND l.reportes_mostrar = '1'
				AND l.operativo in (1,2)
				AND l.red_id = 5
			";
			$sql_query = $mysqli->query($sql_command);
			$locales_array = [];
			while($itm = $sql_query->fetch_assoc()){
				$locales[$itm["id"]] = str_replace(" ","_",strtolower($itm["nombre"]));
				$locales_array[$itm["id"]] = $itm;
				$local_titulo[$itm["id"]] = strtoupper($itm["nombre"]);
			}
			
			$caja_arr = array();
			$pago_command = "
				SELECT
					pd.id ,
					p.id AS pago_id,
					l.id AS local_id,
					p.pago_tipo_id,
					l.cc_id,
					l.nombre AS 'local_nombre',
					pt.nombre AS 'PAGO TIPO',
					pd.descripcion AS 'DESCRIPCIÓN',
					tta.nro_doc AS 'nro_operacion',
					tta.importe AS 'importe',		
					tta.fecha_operacion AS 'fecha_Excel',
					tta.tipo AS 'tipo_transaccion'
				FROM  tbl_pagos p
				LEFT JOIN tbl_pagos_detalle pd ON pd.id =  p.pago_detalle_id
				LEFT JOIN tbl_pagos_tipos pt ON pt.id = p.pago_tipo_id
			 	LEFT JOIN tbl_locales l ON l.id = p.local_id
                INNER JOIN tbl_transacciones_agentes tta ON tta.id_pago_detalle = p.pago_detalle_id
				WHERE p.estado = 1 AND p.pago_tipo_id != 5
				$fecha_query
				$local_query
				GROUP BY pd.id
                ";
			
			$mysqli->query("SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED");
			$pago_query = $mysqli->query($pago_command);
			if($mysqli->error){
				print_r($mysqli->error);
				exit();
			};
			$mysqli->query("COMMIT");

			$table = array();
			$table["tbody"] = array();
			$pagos_data = array();

			$codigo_cajas = array();
			$local_final = [];
			while($c = $pago_query->fetch_assoc()){
				$pagos_data[] = $c;
				$local_final[] = $c["local_id"]; 
				array_push($codigo_cajas, $c['local_id']);
			}
			$codigo_cajas_agrupado = array_values(array_unique($codigo_cajas));

			$fecha_inicio_correlativo = date("Y-m-01",strtotime($get_data["sec_caja_resumen_agentes_fecha_inicio_concar"]));
			$fecha_fin_correlativo = date("Y-m-01",strtotime($get_data["sec_caja_resumen_agentes_fecha_fin_concar"]));

			$caja_correlativo = $get_data["correlativo_inicial"];

			foreach ($codigo_cajas_agrupado as $key => $valueCaja) { 
				$haber_importe_Original = 0;
				$haber_importe_dolares = 0;
				$haber_importe_soles = 0;
				$nombre_Glosa = "";
				$cc = "";

				foreach ($pagos_data as $key => $value) {
					if($valueCaja == $value["local_id"]){
						$nombre_Glosa = $value["local_nombre"];
						$cc = $value["cc_id"];
						$haber_importe_Original += (float)$value["importe"];
						$haber_importe_dolares += (float)$value["importe"]/(float)$tipo_cambio;
						$haber_importe_soles += (float)$value["importe"];
						$fecha_Deposito = date("d/m/Y",strtotime($value["fecha_Excel"]));
						$nro_documento = '0'.date("m-Y", strtotime($value["fecha_Excel"]));
						$nro_comprobante = date("m", strtotime($value["fecha_Excel"])).zerofill($caja_correlativo,4);

						$cuenta_contable =  '10411167';// '10411099';//bbva
						if($value["tipo_transaccion"] == 1)
						{ //caja piura
							$cuenta_contable = '10411170'; // '10411131';
						}
						date("m").zerofill($caja_correlativo,4);
						$tr = array();
						$tr["sub_Diario"] = '2120';
						$tr["nro_Comprobante"] = $nro_comprobante;
						$tr["fecha_Comprobante"] = $fecha_fin_pretty;
						$tr["codigo_Moneda"] = 'MN';
						$tr["glosa_Principal"] =  cortar_cadena(''.$value["local_nombre"], 40);
						$tr["tipo_Cambio"] = $tipo_cambio;
						$tr["tipo_Conversion"] = 'V';
						$tr["flag_Conversion"] = 'S';
						$tr["fecha_Tipo_Cambio"] = $fecha_fin_pretty;
						$tr["cuenta_Contable"] =  $cuenta_contable;
						$tr["codigo_Anexo"] =  $cuenta_contable;
						$tr["codigo_Centro_Costo"] = '';
						$tr["debe_Haber"] = 'D';
						$tr["importe_Original"] = (float)$value["importe"];
						$tr["importe_Dolares"] = (float)$value["importe"]/(float)$tipo_cambio;
						$tr["importe_Soles"] = (float)$value["importe"];
						$tr["tipo_Documento"] = 'EN';
						$tr["nro_Documento"] = (string)$value["nro_operacion"];
						$tr["fecha_Documento"] = date("d/m/Y",strtotime($value["fecha_Excel"]));
						$tr["fecha_Vencimiento"] = date("d/m/Y",strtotime($value["fecha_Excel"]));
						$tr["codigo_Area"] = '101';
						$tr["glosa_Detalle"] = cortar_cadena($value["local_nombre"],30);
						$tr["codigo_Anexo_Auxiliar"] = '';
						$tr["medio_Pago"] = '001';
						$tr["tipo_Documento_Referencia"] = '';
						$tr["numero_Documento_Referencia"] = '';
						$tr["fecha_Documento_Referencia"] = '';
						$tr["nro_Registradora"] = '';
						$tr["base_Imponible"] = '';
						$tr["igv_Documento"] = '';
						$tr["tipo_Referencia"] = '';
						$tr["numero_Serie"] = '';
						$tr["fecha_Operacion"] = '';
						$tr["tipo_Tasa"] = '';
						$tr["tasa_Detraccion_Percepcion"] = '';
						$tr["importe_Base_Dolares"] = '';
						$tr["importe_Base_Soles"] = '';
						$tr["tipo_Cambio_F"] = '';
						$tr["importe_Igv_Fiscal"] = '';

						$table["tbody"][] = $tr;
					}
				}

				$haber_importe_soles = $haber_importe_Original;
				$haber_importe_dolares = ($haber_importe_Original/(float)$tipo_cambio);
				$tr = array();
				$tr["sub_Diario"] = '2120';
				$tr["nro_Comprobante"] = $nro_comprobante;
				$tr["fecha_Comprobante"] = $fecha_fin_pretty;
				$tr["codigo_Moneda"] = 'MN';
				$tr["glosa_Principal"] = cortar_cadena($nombre_Glosa,40);
				$tr["tipo_Cambio"] = $tipo_cambio;
				$tr["tipo_Conversion"] = 'V';
				$tr["flag_Conversion"] = 'S';
				$tr["fecha_Tipo_Cambio"] = $fecha_fin_pretty;
				$tr["cuenta_Contable"] = '122001';
				$tr["codigo_Anexo"] =  $locales_array[$valueCaja]["ruc"];
				$tr["codigo_Centro_Costo"] = '';
				$tr["debe_Haber"] = 'H';
				$tr["importe_Original"] = number_format($haber_importe_soles, 2, ".", "");
				$tr["importe_Dolares"] = number_format(($haber_importe_soles/(float)$tipo_cambio), 2, ".", "");
				$tr["importe_Soles"] = number_format($haber_importe_soles, 2, ".", "");
				$tr["tipo_Documento"] = 'AN';
				$tr["nro_Documento"] = $nro_documento;
				$tr["fecha_Documento"] = $fecha_Deposito;
				$tr["fecha_Vencimiento"] = $fecha_Deposito;
				$tr["codigo_Area"] = '';
				$tr["glosa_Detalle"] = cortar_cadena($nombre_Glosa,30);
				$tr["codigo_Anexo_Auxiliar"] = '';
				$tr["medio_Pago"] = '';
				$tr["tipo_Documento_Referencia"] = '';
				$tr["numero_Documento_Referencia"] = "";
				$tr["fecha_Documento_Referencia"] = "";
				$tr["nro_Registradora"] = '';
				$tr["base_Imponible"] = '';
				$tr["igv_Documento"] = '';
				$tr["tipo_Referencia"] = '';
				$tr["numero_Serie"] = '';
				$tr["fecha_Operacion"] = '';
				$tr["tipo_Tasa"] = '';
				$tr["tasa_Detraccion_Percepcion"] = '';
				$tr["importe_Base_Dolares"] = '';
				$tr["importe_Base_Soles"] = '';
				$tr["tipo_Cambio_F"] = '';
				$tr["importe_Igv_Fiscal"] = '';
				$table["tbody"][] = $tr;

				$caja_correlativo++;
			}
			date_default_timezone_set('America/Mexico_City');

			if (PHP_SAPI == 'cli')
				die('Este archivo solo se puede ver desde un navegador web');

			require_once '../phpexcel/classes/PHPExcel.php';  
			$objPHPExcel = new PHPExcel();
			$objPHPExcel->getProperties()->setCreator("GestionApuestaTotal") // Nombre del autor
			    ->setLastModifiedBy("Codedrinks") //Ultimo usuario que lo modificó
			    ->setTitle("Reporte Concar") // Titulo
			    ->setSubject("Reporte Excel Concar") //Asunto
			    ->setDescription("Reporte Depositos") //Descripción
			    ->setKeywords("depositos") //Etiquetas
			    ->setCategory("Reporte excel"); //Categorias
			$tituloReporte = "Ingresos";
			$titulosColumnas = array('Sub Diario',
				'Número de Comprobante',
				'Fecha de Comprobante',
				'Código de Moneda',
				'Glosa Principal',
				'Tipo de Cambio',
				'Tipo de Conversión',
				'Flag de Conversión de Moneda',
				'Fecha Tipo de Cambio',
				'Cuenta Contable',
				'Código de Anexo',
				'Código de Centro de Costo',
				'Debe / Haber',
				'Importe Original',
				'Importe en Dólares',
				'Importe en Soles',
				'Tipo de Documento',
				'Número de Documento',
				'Fecha de Documento',
				'Fecha de Vencimiento', 
				'Código de Area', 
				'Glosa Detalle', 
				'Código de Anexo Auxiliar',
				'Medio de Pago', 
				'Tipo de Documento de Referencia', 
				'Número de Documento Referencia', 
				'Fecha Documento Referencia',
				'Nro Máq. Registradora Tipo Doc. Ref.',
				'Base Imponible Documento Referencia',
				'IGV Documento Provisión',
				'Tipo Referencia en estado MQ',
				'Número Serie Caja Registradora',
				'Fecha de Operación',
				'Tipo de Tasa',
				'Tasa Detracción/Percepción',
				'Importe Base Detracción/Percepción Dólares',
				'Importe Base Detracción/Percepción Soles',
				'Tipo Cambio para "F"',
				'Importe de IGV sin derecho credito fiscal',
				'Tasa IGV');
			// Se agregan los titulos del reporte
			$objPHPExcel->setActiveSheetIndex(0)
			    // ->setCellValue('A1',$tituloReporte) // Titulo del reporte
			    ->setCellValue('B1',  $titulosColumnas[0])  //Titulo de las columnas
			    ->setCellValue('C1',  $titulosColumnas[1])
			    ->setCellValue('D1',  $titulosColumnas[2])
			    ->setCellValue('E1',  $titulosColumnas[3])
				->setCellValue('F1',  $titulosColumnas[4])  //Titulo de las columnas
			    ->setCellValue('G1',  $titulosColumnas[5])
			    ->setCellValue('H1',  $titulosColumnas[6])
			    ->setCellValue('I1',  $titulosColumnas[7])
			    ->setCellValue('J1',  $titulosColumnas[8])  //Titulo de las columnas
			    ->setCellValue('K1',  $titulosColumnas[9])
			    ->setCellValue('L1',  $titulosColumnas[10])
			    ->setCellValue('M1',  $titulosColumnas[11])
				->setCellValue('N1',  $titulosColumnas[12])  //Titulo de las columnas
			    ->setCellValue('O1',  $titulosColumnas[13])
			    ->setCellValue('P1',  $titulosColumnas[14])
			    ->setCellValue('Q1',  $titulosColumnas[15])
			    ->setCellValue('R1',  $titulosColumnas[16])
			    ->setCellValue('S1',  $titulosColumnas[17])
				->setCellValue('T1',  $titulosColumnas[18])  //Titulo de las columnas
			    ->setCellValue('U1',  $titulosColumnas[19])
			    ->setCellValue('V1',  $titulosColumnas[20])
			    ->setCellValue('W1',  $titulosColumnas[21])
			    ->setCellValue('X1',  $titulosColumnas[22])  //Titulo de las columnas
			    ->setCellValue('Y1',  $titulosColumnas[23])
			    ->setCellValue('Z1',  $titulosColumnas[24])
			    ->setCellValue('AA1',  $titulosColumnas[25])
				->setCellValue('AB1',  $titulosColumnas[26])  //Titulo de las columnas
			    ->setCellValue('AC1',  $titulosColumnas[27])
			    ->setCellValue('AD1',  $titulosColumnas[28])
			    ->setCellValue('AE1',  $titulosColumnas[29])
			    ->setCellValue('AF1',  $titulosColumnas[30])
			    ->setCellValue('AG1',  $titulosColumnas[31])
				->setCellValue('AH1',  $titulosColumnas[32])  //Titulo de las columnas
			    ->setCellValue('AI1',  $titulosColumnas[33])
			    ->setCellValue('AJ1',  $titulosColumnas[34])
			    ->setCellValue('AK1',  $titulosColumnas[35])
			    ->setCellValue('AL1',  $titulosColumnas[36])
			    ->setCellValue('AM1',  $titulosColumnas[37])
				->setCellValue('AN1',  $titulosColumnas[38])
				->setCellValue('AO1',  $titulosColumnas[39]);  //Titulo de las columnas;
			$i = 4;
			foreach ($table["tbody"] as $k => $tr) {
				$objPHPExcel->getActiveSheet()->setCellValue('B'.$i,$tr["sub_Diario"]);
				$objPHPExcel->getActiveSheet()->setCellValue('C'.$i,$tr["nro_Comprobante"]);
				$objPHPExcel->getActiveSheet()->setCellValue('D'.$i,$tr["fecha_Comprobante"]);
				$objPHPExcel->getActiveSheet()->setCellValue('E'.$i,$tr["codigo_Moneda"]);
				$objPHPExcel->getActiveSheet()->setCellValue('F'.$i,$tr["glosa_Principal"]);
				$objPHPExcel->getActiveSheet()->setCellValue('G'.$i,$tr["tipo_Cambio"]);
				$objPHPExcel->getActiveSheet()->setCellValue('H'.$i,$tr["tipo_Conversion"]);
				$objPHPExcel->getActiveSheet()->setCellValue('I'.$i,$tr["flag_Conversion"]);
				$objPHPExcel->getActiveSheet()->setCellValue('J'.$i,$tr["fecha_Tipo_Cambio"]);
				$objPHPExcel->getActiveSheet()->setCellValue('K'.$i,$tr["cuenta_Contable"]);
				$objPHPExcel->getActiveSheet()->setCellValue('L'.$i,$tr["codigo_Anexo"]);
				$objPHPExcel->getActiveSheet()->setCellValue('M'.$i,$tr["codigo_Centro_Costo"]);
				$objPHPExcel->getActiveSheet()->setCellValue('N'.$i,$tr["debe_Haber"]);
				$objPHPExcel->getActiveSheet()->setCellValue('O'.$i,$tr["importe_Original"]);
				$objPHPExcel->getActiveSheet()->setCellValue('P'.$i,$tr["importe_Dolares"]);
				$objPHPExcel->getActiveSheet()->setCellValue('Q'.$i,$tr["importe_Soles"]);
				$objPHPExcel->getActiveSheet()->setCellValue('R'.$i,$tr["tipo_Documento"]);
				$objPHPExcel->getActiveSheet()->setCellValue('S'.$i,$tr["nro_Documento"]);
				$objPHPExcel->getActiveSheet()->setCellValue('T'.$i,$tr["fecha_Documento"]);
				$objPHPExcel->getActiveSheet()->setCellValue('U'.$i,$tr["fecha_Vencimiento"]);
				$objPHPExcel->getActiveSheet()->setCellValue('V'.$i,$tr["codigo_Area"]);
				$objPHPExcel->getActiveSheet()->setCellValue('W'.$i,$tr["glosa_Detalle"]);
				$objPHPExcel->getActiveSheet()->setCellValue('X'.$i,$tr["codigo_Anexo_Auxiliar"]);
				$objPHPExcel->getActiveSheet()->setCellValue('Y'.$i,$tr["medio_Pago"]);
				$objPHPExcel->getActiveSheet()->setCellValue('Z'.$i,$tr["tipo_Documento_Referencia"]);
				$objPHPExcel->getActiveSheet()->setCellValue('AA'.$i,$tr["numero_Documento_Referencia"]);
				$objPHPExcel->getActiveSheet()->setCellValue('AB'.$i,$tr["fecha_Documento_Referencia"]);
				$objPHPExcel->getActiveSheet()->setCellValue('AC'.$i,$tr["nro_Registradora"]);
				$objPHPExcel->getActiveSheet()->setCellValue('AD'.$i,$tr["base_Imponible"]);
				$objPHPExcel->getActiveSheet()->setCellValue('AE'.$i,$tr["igv_Documento"]);
				$objPHPExcel->getActiveSheet()->setCellValue('AF'.$i,$tr["tipo_Referencia"]);
				$objPHPExcel->getActiveSheet()->setCellValue('AG'.$i,$tr["numero_Serie"]);
				$objPHPExcel->getActiveSheet()->setCellValue('AH'.$i,$tr["fecha_Operacion"]);
				$objPHPExcel->getActiveSheet()->setCellValue('AI'.$i,$tr["tipo_Tasa"]);
				$objPHPExcel->getActiveSheet()->setCellValue('AJ'.$i,$tr["tasa_Detraccion_Percepcion"]);
				$objPHPExcel->getActiveSheet()->setCellValue('AK'.$i,$tr["importe_Base_Dolares"]);
				$objPHPExcel->getActiveSheet()->setCellValue('AL'.$i,$tr["importe_Base_Soles"]);
				$objPHPExcel->getActiveSheet()->setCellValue('AM'.$i,$tr["tipo_Cambio_F"]);
				$objPHPExcel->getActiveSheet()->setCellValue('AN'.$i,$tr["importe_Igv_Fiscal"]);
				$i++;
			}		

			$estiloTituloReporte = array(
			    'font' => array(
			        'name'      => 'Verdana',
			        'bold'      => true,
			        'italic'    => false,
			        'strike'    => false,
			        'size' =>16,
			        'color'     => array(
			            'rgb' => 'FFFFFF'
			        )
			    ),
			    'fill' => array(
			      'type'  => PHPExcel_Style_Fill::FILL_SOLID,
			      'color' => array(
			            'argb' => 'FF220835')
			  	),
			    'borders' => array(
			        'allborders' => array(
			            'style' => PHPExcel_Style_Border::BORDER_NONE
			        )
			    ),
			    'alignment' => array(
			        'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			        'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			        'rotation' => 0,
			        'wrap' => TRUE
			    )
			);
			 
			$estiloTituloColumnas = array(
			    'font' => array(
			        'name'  => 'calibri',
			        'bold'  => false,
			        'size'  => 10,
			        'color' => array(
			            'rgb' => '000000'
			        )
			    ),
			    'fill' => array(
			        'type'       => PHPExcel_Style_Fill::FILL_SOLID,
			  		'color'=>array('rgb'=>'FFC000')
			    ),
			    'borders' => array(
			        'allborders' => array(
			            'style' => PHPExcel_Style_Border::BORDER_THIN ,
			            'color' => array(
			                'rgb' => '000000'
			            )
			        ),
			    ),
			    'alignment' =>  array(
			        'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
			        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
			        'wrap'      => true
			    )
			);
			 
			$estiloInformacion = new PHPExcel_Style();
			$estiloInformacion->applyFromArray( array(
			    'font' => array(
			        'name'  => 'calibri',
			        'bold'  => false,
			        'size'  => 10,
			        'color' => array(
			            'rgb' => '000000'
			        )
			    ),
			    
			));  
			
			$objPHPExcel->getActiveSheet()->getStyle('B1:AO1')->applyFromArray($estiloTituloColumnas);
			$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);

			$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:AO".($i-1));

			for($j=2;$j<=$i;$j++){
				$objPHPExcel->getActiveSheet()->getStyle('G'.$j)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
				$objPHPExcel->getActiveSheet()->getStyle('O'.$j)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
				$objPHPExcel->getActiveSheet()->getStyle('P'.$j)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
				$objPHPExcel->getActiveSheet()->getStyle('Q'.$j)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
				$objPHPExcel->getActiveSheet()->getStyle('L'.$j)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
				$objPHPExcel->getActiveSheet()->getStyle('S'.$j)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
			}

			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(12);
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
			$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
			$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(28);
			$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(15);
			$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(15);
			$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(15);
			$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(15);
			$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(12);
			$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(12);
			$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setWidth(10);
			$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setWidth(14);
			$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setWidth(15);
			$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setWidth(12);
			$objPHPExcel->getActiveSheet()->getColumnDimension('R')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('R')->setWidth(15);
			$objPHPExcel->getActiveSheet()->getColumnDimension('S')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('S')->setWidth(16);
			$objPHPExcel->getActiveSheet()->getColumnDimension('T')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('T')->setWidth(12);
			$objPHPExcel->getActiveSheet()->getColumnDimension('U')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('U')->setWidth(12);
			$objPHPExcel->getActiveSheet()->getColumnDimension('V')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('V')->setWidth(15);
			$objPHPExcel->getActiveSheet()->getColumnDimension('W')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('W')->setWidth(28);
			$objPHPExcel->getActiveSheet()->getColumnDimension('X')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('X')->setWidth(15);
			$objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('Y')->setWidth(16);
			$objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('Z')->setWidth(15);
			$objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('AA')->setWidth(15);
			$objPHPExcel->getActiveSheet()->getColumnDimension('AB')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('AB')->setWidth(15);
			$objPHPExcel->getActiveSheet()->getColumnDimension('AC')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('AC')->setWidth(18);
			$objPHPExcel->getActiveSheet()->getColumnDimension('AD')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('AD')->setWidth(18);
			$objPHPExcel->getActiveSheet()->getColumnDimension('AE')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('AE')->setWidth(18);
			$objPHPExcel->getActiveSheet()->getColumnDimension('AF')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('AF')->setWidth(18);
			$objPHPExcel->getActiveSheet()->getColumnDimension('AG')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('AG')->setWidth(20);
			$objPHPExcel->getActiveSheet()->getColumnDimension('AH')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('AH')->setWidth(18);
			$objPHPExcel->getActiveSheet()->getColumnDimension('AI')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('AI')->setWidth(12);
			$objPHPExcel->getActiveSheet()->getColumnDimension('AJ')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('AJ')->setWidth(18);
			$objPHPExcel->getActiveSheet()->getColumnDimension('AK')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('AK')->setWidth(19);
			$objPHPExcel->getActiveSheet()->getColumnDimension('AL')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('AL')->setWidth(19);
			$objPHPExcel->getActiveSheet()->getColumnDimension('AM')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('AM')->setWidth(15);
			$objPHPExcel->getActiveSheet()->getColumnDimension('AN')->setAutoSize(false);	
			$objPHPExcel->getActiveSheet()->getColumnDimension('AN')->setWidth(18);

			$objPHPExcel->getActiveSheet()->setTitle('Depositos');
			$objPHPExcel->setActiveSheetIndex(0);
			// Inmovilizar paneles
			//$objPHPExcel->getActiveSheet(0)->freezePane('A4');
			//$objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);

			$local_titulo_parcial = "";
			$local_id_parcial = "";
			$local_id_name = "";

			if ($get_data['concar_local_id'] == '_all_'){
				$local_titulo_parcial = "Todos";
				$local_id_parcial = "Todos";
				$local_id_name = -1;
			}  
			else 
			{
				$local_titulo_parcial = $local_titulo[$get_data['concar_local_id']];
				$local_id_parcial = $locales[$get_data['concar_local_id']];
				$local_id_name = $get_data['concar_local_id'];
			}

			$titulo_reporte_cajas = "REPORTE CONCAR ".$local_titulo_parcial;
			$titulo_file_reporte_cajas = "Depositos_Agente_Caja_Concar_".$local_id_parcial."_".date("d-m-Y",strtotime($get_data["sec_caja_resumen_agentes_fecha_inicio_concar"]))."_al_".date("d-m-Y",strtotime($get_data["sec_caja_resumen_agentes_fecha_fin_concar"]))."_".date("Ymdhis");

			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'.$titulo_file_reporte_cajas.'.xls"');
			header('Cache-Control: max-age=0');

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$excel_export = 'export/files_exported/'.$titulo_file_reporte_cajas.'.xls';
			$excel_path = '/var/www/html/' . $excel_export;
			$excel_path_download = $excel_export;
			$url = $titulo_file_reporte_cajas.'.xls';
			$objWriter->save($excel_path);

			$insert_cmd = "INSERT INTO tbl_exported_files (url,tipo,ext,size,fecha_registro,usuario_id)";
			$insert_cmd.= " VALUES ('".$url."','excel','xls','".filesize($excel_path)."','".date("Y-m-d h:i:s")."','".$login["id"]."')";
			$mysqli->query($insert_cmd);
			$exported_id = $mysqli->insert_id;

			$mysqli->query("
				INSERT INTO tbl_concar_agentes_historico (
					local_id,
					exported_id,
					usuario_id,
					cambio,
					correlativo,
					fecha_operacion,
					fecha_inicio,
					fecha_fin
				) VALUES (
					'". $local_id_name ."',
					".$exported_id.",
					".$login["id"].",
					".$get_data["tipo_cambio"].",
					".(($get_data["correlativo_inicial"] != "") ? "'".$get_data["correlativo_inicial"]."'" : "null").",
					'".date('Y-m-d H:i:s')."',
					'".$get_data["sec_caja_resumen_agentes_fecha_inicio_concar"]."',
					'".$get_data["sec_caja_resumen_agentes_fecha_fin_concar"]."'
				)
			");

			echo json_encode(array(
			    "path" => $excel_path_download,
			    "url" => $titulo_file_reporte_cajas.'.xls',
			    "tipo" => "excel",
			    "ext" => "xls",
			    "size" => filesize($excel_path),
			    "fecha_registro" => date("d-m-Y h:i:s"),
			    "sql" => $insert_cmd
			));

			exit;
		}
		else{
		    print_r('No hay resultados para mostrar');
		}
	}

	function zerofill($valor,
		$longitud){
		$res = str_pad($valor, $longitud, '0', STR_PAD_LEFT);
		return $res;
	}

	function cortar_cadena($cadena, $longitud){
		$devolver="";
		if (strlen($cadena) > $longitud) 
		{
			$devolver = substr($cadena, 0, $longitud);
		}
		else{
			$devolver = $cadena;
		};
		return $devolver;
	}
?>
