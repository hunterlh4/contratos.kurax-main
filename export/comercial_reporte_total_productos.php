<?php
	include("../sys/db_connect.php");
	include("../sys/sys_login.php");
	date_default_timezone_set("America/Lima");

	$post = array();
	$post = array("comercial_reporte_total_productos"=>array(
		"canales_de_venta" => $_POST["filtro"]["canales_de_venta"],
		"zona" => $_POST["filtro"]["zona"],
		"zona_nombre" => $_POST["filtro"]["nombre_zona"],
		"jefe_operaciones" => $_POST["filtro"]["jefe_operaciones"],
		"canales_de_venta_nombre" => $_POST["filtro"]["nombre_canal_venta"],
		"jefe_operaciones_nombre" => $_POST["filtro"]["nombre_jefe_operaciones"],
		"fecha_inicio" => $_POST["filtro"]["fecha_inicio"],
		"fecha_fin" => $_POST["filtro"]["fecha_fin"]	
	));
	$data = array();
	$_where="";
	$_where_usuario="";

	if(isset($post["comercial_reporte_total_productos"])){
		$get_data = $post["comercial_reporte_total_productos"];

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
			$_where .= " AND c.canal_de_venta_id  IN ('16','17','21','21','25','27')";
			array_push($canal,16);
			array_push($canal,17);
			array_push($canal,21);
		}else{
			if($get_data["canales_de_venta"][0]=="_all_"){
				$_where .= " AND c.canal_de_venta_id  IN ('16','17','21','21','25','27')";
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
				$_where .= " AND c.canal_de_venta_id IN ('".implode("','", $get_data["canales_de_venta"])."')";
				
			}				
		}

		if(is_array($filtro["zona"]) && !in_array("_all_", $filtro["zona"])) $_where .= " AND c.zona_id IN (".implode(",", $filtro["zona"]).")";

		// if($get_data["jefe_operaciones"]==''){

		// 	$oper_command = "SELECT u.id,a.area_id,a.cargo_id,a.nombre,a.apellido_paterno,a.apellido_materno 
		// 					FROM tbl_personal_apt a
		// 					join tbl_usuarios u on a.id= u.personal_id
		// 					where a.area_id=21 and a.cargo_id=16 and a.estado=1 and u.estado=1;";
		// 	$oper_query = $mysqli->query($oper_command);
		// 	$total_jefe=array();

		// 	while ($result =$oper_query->fetch_assoc()) {
		// 		array_push($total_jefe,$result["id"]);
		// 	}
		// 	$_where_usuario .= " AND u.usuario_id IN ('".implode("','", $total_jefe)."')";
		// }else{
		// 	if($get_data["jefe_operaciones"][0]=='_all_'){
		// 		$oper_command = "SELECT u.id,a.area_id,a.cargo_id,a.nombre,a.apellido_paterno,a.apellido_materno 
		// 					FROM tbl_personal_apt a
		// 					join tbl_usuarios u on a.id= u.personal_id
		// 					where a.area_id=21 and a.cargo_id=16 and a.estado=1 and u.estado=1;";
		// 		$oper_query = $mysqli->query($oper_command);
		// 		$total_jefe=array();

		// 		while ($result =$oper_query->fetch_assoc()) {
		// 			array_push($total_jefe,$result["id"]);
		// 		}
		// 		$_where_usuario .= " AND u.usuario_id IN ('".implode("','", $total_jefe)."')";
		// 	}
		// 	else{
		// 		$_where_usuario .= " AND u.usuario_id IN ('".implode("','", $get_data["jefe_operaciones"])."')";
		// 	}
		// }

		$_where.= " AND (c.fecha >= '".$fecha_inicio."' and c.fecha<='".$fecha_fin."')";

		$total_producto_command = "SELECT 
							MAX(c.fecha) as fecha_ultimo,
							c.canal_de_venta_id canal_id,
							CONCAT(LPAD(MONTH(c.fecha), 2, '0'), '-', YEAR(c.fecha)) as fecha,
							sum(c.total_apostado) as total_apostado
							FROM tbl_transacciones_cabecera c
							join tbl_locales l on c.local_id=l.id
							where l.red_id=1 and c.estado=1
							$_where 
							$_where_usuario
							group by YEAR(c.fecha),MONTH(c.fecha),c.canal_de_venta_id
							order by YEAR(c.fecha) asc,MONTH(c.fecha) asc";
		//echo $total_producto_command; echo "\n\n";exit();
		$total_producto_query = $mysqli->query($total_producto_command);

		if($mysqli->error){
			$return["ERROR_MYSQL"]=$mysqli->error;
			print_r($mysqli->error);
		};

		$_data_producto = array();
		while ($result =$total_producto_query->fetch_assoc()) {
			$_data_producto[]=$result;
		}

		//echo $get_data["fecha_inicio"];exit();
		if(mysqli_num_rows($total_producto_query) > 0){
			$fecha_fin_reporte = $_data_producto[(int)mysqli_num_rows($total_producto_query)-1]['fecha_ultimo'];
		}
		else{
			$fecha_fin_reporte = $fecha_fin;
		}

		$meses_nombre = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
		$anio_inicio_file = date("Y",strtotime($get_data["fecha_inicio"]));
		$mes_inicio_file = date("m",strtotime($get_data["fecha_inicio"]));
		$fecha_inicio_file = $meses_nombre[(int)$mes_inicio_file-1].'_'.$anio_inicio_file;
		$anio_fin_file = date("Y",strtotime($get_data["fecha_fin"]));
		$mes_fin_file = date("m",strtotime($get_data["fecha_fin"]));
		$fecha_fin_file = $meses_nombre[(int)$mes_fin_file-1].'_'.$anio_fin_file;


		$titulo_reporte ="DEL ".date("d-m-Y",strtotime($get_data["fecha_inicio"]))." AL ".date("d-m-Y",strtotime($fecha_fin_reporte));
		$titulo_reporte_total_productos= "REPORTE TOTAL PRODUCTO ".$titulo_reporte;
		$titulo_file_reporte_total_productos = "comercial_reporte_total_producto_".$fecha_inicio_file."_al_".$fecha_fin_file."_".date("Ymdhis");	

		if (isset($titulo_reporte_total_productos)) {
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

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B2',$titulo_reporte_total_productos);
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells("B2:Q2");	

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B3',"Canal Venta");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells("B3:C3");
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('D3',empty($get_data["canales_de_venta_nombre"])?"Todos":$get_data["canales_de_venta_nombre"]);
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells("D3:F3");	

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B4',"Zona");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells("B4:C4");
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('D4',empty($get_data["zona_nombre"])?"Todos":$get_data["zona_nombre"]);
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells("D4:F4");

			$row_first_head =5;
			$row_letter = "C";
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B".$row_first_head,"MES");
			$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(17);
			
			foreach($canal as $posicion=>$canales)
			{
				$nombre_canal="";
				switch ($canales) {
					case '16':
						$nombre_canal="Caja";
						break;
					case '21':
						$nombre_canal="Juegos Virtuales";
						break;
					case '17':
						$nombre_canal="Terminal";
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
						
			$col_body= 1;
			$row_body=6;
			$total_t = 0;

			$meses_nombre = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
			foreach($lista_meses as $posicion=>$mes)
			{
				$num_mes = $mes["month"];
				$nombre_mes = $meses_nombre[(int)$num_mes-1];
				$total_fila = 0;
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_body,$row_body,$nombre_mes." ".$mes["year"]);
				$fecha_mes = $mes["month"]."-".$mes["year"];
				$total_canal =0;
				foreach($canal as $posicion=>$canal_id)
				{
					$valor_canal=0;
					foreach($_data_producto as $posicion_buscar=>$data_buscar)
					{
						if($canal_id==16 && ($data_buscar["canal_id"]==16 || $data_buscar["canal_id"]==27) && $fecha_mes == $data_buscar["fecha"]){
							$valor_canal += $data_buscar["total_apostado"];
						}
						if($canal_id==17 && ($data_buscar["canal_id"]==17 || $data_buscar["canal_id"]==25) && $fecha_mes == $data_buscar["fecha"]){
							$valor_canal += $data_buscar["total_apostado"];
						}
						if($canal_id==21 && $data_buscar["canal_id"]==21 && $fecha_mes == $data_buscar["fecha"]){
							$valor_canal += $data_buscar["total_apostado"];
						}
						$total_fila+=$valor_canal;
						
					};
					$col_body++;
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_body,$row_body,number_format($valor_canal,2,'.',false));
				};
				$col_body++;
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_body,$row_body,number_format($total_fila,2,'.',false));
				$total_t +=$total_fila;
				$row_body++;
				$col_body = 1;
			};
			$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_body,$row_body,"TOTAL");
			$col_body++;
			$letter = "C";
			foreach($canal as $posicion=>$canal_id){
				$objPHPExcel->getActiveSheet()->setCellValue($letter.$row_body,"=SUM(".$letter."6:".$letter."".($row_body-1).")");
				$letter++;
				$col_body++;
			};
			$objPHPExcel->getActiveSheet()->getStyle("B".$row_body.":B".$row_body)->applyFromArray($estiloTituloCabeceraTabla);
			$objPHPExcel->getActiveSheet()->setCellValue($letter.$row_body,"=SUM(".$letter."6:".$letter."".($row_body-1).")");

			$objPHPExcel->getActiveSheet()->getStyle('C6:'.$letter.$row_body)->getNumberFormat()->setFormatCode('#,##0.00');

			$objPHPExcel->getActiveSheet()->setTitle("Libro 1");

			// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
			$objPHPExcel->setActiveSheetIndex(0);

			// Se manda el archivo al navegador web, con el nombre que se indica (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'.$titulo_file_reporte_total_productos.'.xls"');
			header('Cache-Control: max-age=0');

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->setPreCalculateFormulas(true);
			$excel_path = '/var/www/html/export/files_exported/'.$titulo_file_reporte_total_productos.'.xls';
			$excel_path_download = '/export/files_exported/'.$titulo_file_reporte_total_productos.'.xls';
			$url = $titulo_file_reporte_total_productos.'.xls';			
			$objWriter->save($excel_path);

			$insert_cmd = "INSERT INTO tbl_exported_files (url,tipo,ext,size,fecha_registro,usuario_id)";
			$insert_cmd.= " VALUES ('".$url."','excel','xls','".filesize($excel_path)."','".date("Y-m-d h:i:s")."','".$login["id"]."')";
			$mysqli->query($insert_cmd);

			echo json_encode(array(
			    "path" => $excel_path_download,
			    "url" => $titulo_file_reporte_total_productos.'.xls',
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