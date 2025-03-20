<?php
	include("../sys/db_connect.php");
	include("../sys/sys_login.php");
	date_default_timezone_set("America/Lima");

	$post = array();
	$post = array("comercial_reporte_total_pais"=>array(
		"canales_de_venta" => $_POST["filtro"]["canales_de_venta"],
		"canales_de_venta_nombre" => $_POST["filtro"]["nombre_canal_venta"],
		"fecha_inicio" => $_POST["filtro"]["fecha_inicio"],
		"fecha_fin" => $_POST["filtro"]["fecha_fin"]	
	));
	$data = array();
	$_where="";

	if(isset($post["comercial_reporte_total_pais"])){
		$get_data = $post["comercial_reporte_total_pais"];

		$fecha_inicio = $get_data["fecha_inicio"];
		$anio_inicio = date('Y', strtotime($get_data["fecha_inicio"]));
		$mes_inicio = date('m', strtotime($get_data["fecha_inicio"]));
		$fecha_inicio = $anio_inicio."-".$mes_inicio."-01";

		$fecha_fin = date("Y-m-d",strtotime($get_data["fecha_fin"]));
		$anio_fin = date('Y', strtotime($get_data["fecha_fin"]));
		$mes_fin = date('m', strtotime($get_data["fecha_fin"]));
		$ultimo_dia = date('t', strtotime($get_data["fecha_fin"]));
		$fecha_fin = $anio_fin."-".$mes_fin."-".$ultimo_dia;

		$lista_meses = "";
		$lista_meses = getMonthsInRange($fecha_inicio, $fecha_fin);

		$canal = array();

		if($get_data["canales_de_venta"]==''){
			$_where .= " AND cab.canal_de_venta_id  IN ('16','17','21','25','27')";
			array_push($canal,16);
			array_push($canal,17);
			array_push($canal,21);
		}else{
			if($get_data["canales_de_venta"][0]=="_all_"){
				$_where .= " AND cab.canal_de_venta_id  IN ('16','17','21','25','27')";
				array_push($canal,16);
				array_push($canal,17);
				array_push($canal,21);
			}
			else{
				foreach ($get_data["canales_de_venta"] as $l => $t) {
					array_push($canal,$t);
				};
				if(in_array("16", $get_data["canales_de_venta"])) 
				{ 
					array_push($get_data["canales_de_venta"],27);
				}

				if(in_array("17", $get_data["canales_de_venta"])) 
				{ 
					array_push($get_data["canales_de_venta"],25);
				}
				$_where .= " AND cab.canal_de_venta_id IN ('".implode("','", $get_data["canales_de_venta"])."')";
				
			}				
		}

		$_where.= " AND (cab.fecha >= '".$fecha_inicio."' and cab.fecha<='".$fecha_fin."')";
		$_where.= " AND l.estado=1 and cab.estado = '1'";

		$total_pais_command = "SELECT MAX(cab.fecha) as fecha_ultimo,
						CONCAT(MONTH(cab.fecha), '-', YEAR(cab.fecha)) as fecha,
						sum(cab.total_apostado) as total_apostado,
						cab.canal_de_venta_id
					 FROM tbl_transacciones_cabecera cab
						inner join tbl_locales l on l.id=cab.local_id 
						where 
						l.red_id=1 
						$_where   group by YEAR(cab.fecha),MONTH(cab.fecha),cab.canal_de_venta_id
 						order by YEAR(cab.fecha) asc,MONTH(cab.fecha) asc";
		//echo $total_pais_command; echo "\n\n";exit();
		// $return["liq_command"]=$liq_command;
		$total_pais_query = $mysqli->query($total_pais_command);
		if($mysqli->error){
			$return["ERROR_MYSQL"]=$mysqli->error;
			print_r($mysqli->error);
		};

		$total_pais=array();
		$fecha_ultima_busqueda ="";
		while ($result =$total_pais_query->fetch_assoc()) {
			$total_pais[]=$result;
			
		}
		//echo var_dump($total_pais); echo "\n\n";exit();
		if(mysqli_num_rows($total_pais_query) > 0){
			$fecha_fin_reporte = $total_pais[0]['fecha_ultimo'];
		}
		else{
			$fecha_fin_reporte = $fecha_fin;
		}

		$mes = array();
		foreach($total_pais as $posicion=>$t_mes)
		{
			array_push($mes,$t_mes["fecha"]);
		};
		$sin_repetidos = array_unique($mes);
		//echo $get_data["fecha_inicio"];exit();

		//$fecha_fin_file = $get_data["fecha_fin"];

		$meses_nombre = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
		$anio_inicio_file = date("Y",strtotime($get_data["fecha_inicio"]));
		$mes_inicio_file = date("m",strtotime($get_data["fecha_inicio"]));
		$fecha_inicio_file = $meses_nombre[(int)$mes_inicio_file-1].'_'.$anio_inicio_file;
		$anio_fin_file = date("Y",strtotime($get_data["fecha_fin"]));
		$mes_fin_file = date("m",strtotime($get_data["fecha_fin"]));
		$fecha_fin_file = $meses_nombre[(int)$mes_fin_file-1].'_'.$anio_fin_file;
		$titulo_reporte ="DEL ".date("d-m-Y",strtotime($get_data["fecha_inicio"]))." AL ".date("d-m-Y",strtotime($fecha_fin_reporte));
		$titulo_reporte_total_pais= "REPORTE TOTAL APOSTADO PAIS ".$titulo_reporte;
		$titulo_file_reporte_total_pais = "comercial_reporte_total_apostado_pais_".$fecha_inicio_file."_al_".$fecha_fin_file."_".date("Ymdhis");	

		if (isset($titulo_reporte_total_pais)) {
			require_once '../phpexcel/classes/PHPExcel.php';  
			$objPHPExcel = new PHPExcel();
			PHPExcel_Calculation::getInstance($objPHPExcel)->clearCalculationCache();
			PHPExcel_Calculation::getInstance($objPHPExcel)->disableCalculationCache();
			$estiloTituloReporte = new PHPExcel_Style();
			$estiloTituloReporte = array(
				'font' => array(
					'name'      => 'Verdana',
					'bold'      => false,
					'italic'    => false,
					'strike'    => false,
					'size' =>18,
						'color'     => array(
							'rgb' => '10407C'
						)
				),
				'fill' => array(
					'type'  => PHPExcel_Style_Fill::FILL_SOLID,
					'color' => array('argb' => 'FFFFFF')
				),
				'alignment' =>  array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
						'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
						'rotation'   => 0,
						'wrap'          => TRUE
				),
				'borders' => array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => array('rgb' => 'dddddd')
					)
				)				
			);

			$estiloTituloCabeceraTabla = new PHPExcel_Style();
			$estiloTituloCabeceraTabla = array(
				'font' => array(
					'name'      => 'Verdana',
					'bold'      => false,
					'italic'    => false,
					'strike'    => false,
					'size' =>11,
						'color'     => array(
							'rgb' => 'FFFFFF'
						)
				),				
				'fill' => array(
					'type'  => PHPExcel_Style_Fill::FILL_SOLID,
					'color' => array('argb' => '337ab7')
				),
				'alignment' =>  array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
						'rotation'   => 0,
						'wrap'          => TRUE
				),
				'borders' => array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => array('rgb' => 'dddddd')
					)
				)				
			);	

			$estiloTituloCabeceraTablaResultado = new PHPExcel_Style();
			$estiloTituloCabeceraTablaResultado = array(
				'font' => array(
					'name'      => 'Verdana',
					'bold'      => false,
					'italic'    => false,
					'strike'    => false,
					'size' =>11,
						'color'     => array(
							'rgb' => 'FFFFFF'
						)
				),				
				'fill' => array(
					'type'  => PHPExcel_Style_Fill::FILL_SOLID,
					'color' => array('argb' => '8E44AD')
				),
				'alignment' =>  array(
						'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
						'rotation'   => 0,
						'wrap'          => TRUE
				),
				'borders' => array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => array('rgb' => 'dddddd')
					)
				)				
			);						

			//Estilos condicionales numeros negativos/ menor que cero color rojo
			$objConditionalNegativeNumber = new PHPExcel_Style_Conditional();
			$objConditionalNegativeNumber->setConditionType(PHPExcel_Style_Conditional::CONDITION_CELLIS);
			$objConditionalNegativeNumber->setOperatorType(PHPExcel_Style_Conditional::OPERATOR_LESSTHAN);
			$objConditionalNegativeNumber->addCondition('0');
			$objConditionalNegativeNumber->getStyle()->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
			$objConditionalNegativeNumber->getStyle()->getFont()->setBold(false);

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B2',$titulo_reporte_total_pais);
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells("B2:Q2");	
			$objPHPExcel->getActiveSheet()->getStyle("B2:Q2")->applyFromArray($estiloTituloReporte);
			

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B3',"Canal Venta");
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('C3',empty($get_data["canales_de_venta_nombre"])?"Todos":$get_data["canales_de_venta_nombre"]);
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells("C3:F3");	

			$row_first_head =5;
			$row_letter = "C";
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B".$row_first_head,"MESES");
			$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(17);
			
			foreach($canal as $posicion=>$canales)
			{
				$nombre_canal="";
				switch ($canales) {
					case '16':
						$nombre_canal="Caja";
						break;
					case '17':
						$nombre_canal="Terminal";
						break;
					case '21':
						$nombre_canal="Juegos Virtuales";
						break;	
					default:
						# code...
						break;
				};
				
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue($row_letter.$row_first_head,$nombre_canal);	
				$objPHPExcel->getActiveSheet()->getColumnDimension($row_letter)->setWidth(17);
				$row_letter++;
			};

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($row_letter.$row_first_head,"TOTAL");
			$objPHPExcel->getActiveSheet()->getColumnDimension($row_letter)->setWidth(17);	
			$objPHPExcel->getActiveSheet()->getStyle("A2:Q2")->applyFromArray($estiloTituloReporte);
			$objPHPExcel->getActiveSheet()->getStyle("B5:".$row_letter."5")->applyFromArray($estiloTituloCabeceraTabla);

			$row_letter="B";
			$row_first_head=6;

			$meses_nombre = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
			
			$total_t =0;
			foreach($sin_repetidos as $posicion=>$fecha)
			{
				$varlor_fecha = explode("-",$fecha);
				$num_mes = $varlor_fecha[0];
				$nombre_mes = $meses_nombre[(int)$num_mes-1];
				$anio = $varlor_fecha[1];
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue($row_letter.$row_first_head,$nombre_mes." ".$anio);	
				
				$row_letter = "C";
				$total =0;

				foreach($canal as $posicion=>$canales)
				{
					$valor_apostado =0;
					foreach ($total_pais as $l_k => $tc_) {

						if($canales==16 && ($tc_["canal_de_venta_id"]==16 || $tc_["canal_de_venta_id"]==27) && $fecha == $tc_["fecha"]){
							$valor_apostado += $tc_["total_apostado"];
						}
						if($canales==17 && ($tc_["canal_de_venta_id"]==17 || $tc_["canal_de_venta_id"]==25) && $fecha == $tc_["fecha"]){
							$valor_apostado += $tc_["total_apostado"];
						}
						if($canales==21 && $tc_["canal_de_venta_id"]==21 && $fecha == $tc_["fecha"]){
							$valor_apostado += $tc_["total_apostado"];
						}

						// if($tc_["canal_de_venta_id"]==$canales && $fecha == $tc_["fecha"]){
						// 	$valor_apostado = $tc_["total_apostado"];
						// }
					};
					$total += $valor_apostado;
					$total_t +=$total;
					$objPHPExcel->setActiveSheetIndex(0)->setCellValue($row_letter.$row_first_head,number_format($valor_apostado,2,'.',false));	
					$row_letter++;
					$objPHPExcel->setActiveSheetIndex(0)->setCellValue($row_letter.$row_first_head,number_format($total,2,'.',false));	
				};
				$row_letter="B";
				$row_first_head++;
			};

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B".$row_first_head,"Total");
			$objPHPExcel->getActiveSheet()->getStyle("B".$row_first_head.":B".$row_first_head)->applyFromArray($estiloTituloCabeceraTabla);	

			$letter ="C";
			foreach($canal as $posicion=>$canales)
			{
				$objPHPExcel->getActiveSheet()->setCellValue($letter.$row_first_head,"=SUM(".$letter."6:".$letter."".($row_first_head-1).")");
				$letter++;
			};
			$objPHPExcel->getActiveSheet()->setCellValue($letter.$row_first_head,"=SUM(".$letter."6:".$letter."".($row_first_head-1).")");

			$objPHPExcel->getActiveSheet()->getStyle('C6:'.$letter.($row_first_head))->getNumberFormat()->setFormatCode('#,##0.00');

			$objPHPExcel->getActiveSheet()->setTitle("Libro 1");

			// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
			$objPHPExcel->setActiveSheetIndex(0);

			// Se manda el archivo al navegador web, con el nombre que se indica (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'.$titulo_file_reporte_total_pais.'.xls"');
			header('Cache-Control: max-age=0');

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->setPreCalculateFormulas(true);
			$excel_path = '/var/www/html/export/files_exported/'.$titulo_file_reporte_total_pais.'.xls';
			$excel_path_download = '/export/files_exported/'.$titulo_file_reporte_total_pais
			.'.xls';
			$url = $titulo_file_reporte_total_pais.'.xls';			
			$objWriter->save($excel_path);

			$insert_cmd = "INSERT INTO tbl_exported_files (url,tipo,ext,size,fecha_registro,usuario_id)";
			$insert_cmd.= " VALUES ('".$url."','excel','xls','".filesize($excel_path)."','".date("Y-m-d h:i:s")."','".$login["id"]."')";
			$mysqli->query($insert_cmd);

			echo json_encode(array(
			    "path" => $excel_path_download,
			    "url" => $titulo_file_reporte_total_pais.'.xls',
			    "tipo" => "excel",
			    "ext" => "xls",
			    "size" => filesize($excel_path),
			    "fecha_registro" => date("d-m-Y h:i:s"),
			    "sql" => $insert_cmd
			));
			exit;  
		}else{
			print_r('No hay resultados para mostrar');            
		} 	
	}	

	function getMonthsInRange($startDate, $endDate) {
		$months = array();
		while (strtotime($startDate) <= strtotime($endDate)) {
		    $months[] = array('year' => date('Y', strtotime($startDate)), 'month' => date('m', strtotime($startDate)), );
		    $startDate = date('d M Y', strtotime($startDate.
		        '+ 1 month'));
		}
		return $months;
	}
?>