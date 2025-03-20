<?php
	include("../sys/db_connect.php");
	include("../sys/sys_login.php");
	date_default_timezone_set("America/Lima");

	$post = array();
	$post = array("comercial_reporte_admin_productos"=>array(
		"zona" => $_POST["filtro"]["zona"],
		"zona_nombre" => $_POST["filtro"]["nombre_zona"],
		"jefe_operaciones" => $_POST["filtro"]["jefe_operaciones"],
		"jefe_operaciones_nombre" => $_POST["filtro"]["nombre_jefe_operaciones"],
		"fecha_inicio" => $_POST["filtro"]["fecha_inicio"],
		"fecha_fin" => $_POST["filtro"]["fecha_fin"]	
	));
	$data = array();
	$_where="";
	$_where_usuario="";

	if(isset($post["comercial_reporte_admin_productos"])){
		$get_data = $post["comercial_reporte_admin_productos"];

		$fecha_inicio = $get_data["fecha_inicio"];
		$anio_inicio = date('Y', strtotime($get_data["fecha_inicio"]));
		$mes_inicio = date('m', strtotime($get_data["fecha_inicio"]));
		$fecha_inicio = $anio_inicio."-".$mes_inicio."-01";

		$fecha_fin = date("Y-m-d",strtotime($get_data["fecha_fin"]));
		$anio_fin = date('Y', strtotime($get_data["fecha_fin"]));
		$mes_fin = date('m', strtotime($get_data["fecha_fin"]));
		$ultimo_dia = date('t', strtotime($get_data["fecha_fin"]));
		$fecha_fin = $anio_fin."-".$mes_fin."-".$ultimo_dia;

		$canal = array();
		$_where .= " AND cab.canal_de_venta_id  IN ('16','17','21','25','27')";
		array_push($canal,16);
		array_push($canal,21);
		array_push($canal,17);

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

		if(is_array($filtro["zona"]) && !in_array("_all_", $filtro["zona"])) $_where .= " AND cab.zona_id IN (".implode(",", $filtro["zona"]).")";

		$_where.= " AND (cab.fecha >= '".$fecha_inicio."' and cab.fecha<='".$fecha_fin."')";
		$_where.= " AND l.estado=1 and  cab.estado = '1'";

		$admin_productos_command = "SELECT MAX(cab.fecha) as fecha_ultimo,
						CONCAT(MONTH(cab.fecha), '-', YEAR(cab.fecha)) as fecha,
						sum(cab.total_apostado) as total_apostado,
						cab.canal_de_venta_id
					 FROM tbl_transacciones_cabecera cab
						inner join tbl_locales l on l.id=cab.local_id 
						
						where 
						l.red_id=1 
						$_where_usuario 
						$_where   group by YEAR(cab.fecha),MONTH(cab.fecha),cab.canal_de_venta_id
 						order by YEAR(cab.fecha) asc,MONTH(cab.fecha) asc";
		//echo $admin_productos_command; echo "\n\n";exit();
		// $return["liq_command"]=$liq_command;
		$admin_productos_query = $mysqli->query($admin_productos_command);
		if($mysqli->error){
			$return["ERROR_MYSQL"]=$mysqli->error;
			print_r($mysqli->error);
		};

		$total_admin_productos =array();
		while ($result =$admin_productos_query->fetch_assoc()) {
			$total_admin_productos[]=$result;
		}
		//echo var_dump($tota_admin_productos); echo "\n\n";exit();
		if(mysqli_num_rows($admin_productos_query) > 0){
			$fecha_fin_reporte = $total_admin_productos[0]['fecha_ultimo'];
		}
		else{
			$fecha_fin_reporte = $fecha_fin;
		}

		$mes = array();
		foreach($total_admin_productos as $posicion=>$t_mes)
		{
			array_push($mes,$t_mes["fecha"]);
		};
		$sin_repetidos = array_unique($mes);
		//echo $get_data["fecha_inicio"];exit();

		$meses_nombre = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
		$anio_inicio_file = date("Y",strtotime($get_data["fecha_inicio"]));
		$mes_inicio_file = date("m",strtotime($get_data["fecha_inicio"]));
		$fecha_inicio_file = $meses_nombre[(int)$mes_inicio_file-1].'_'.$anio_inicio_file;
		$anio_fin_file = date("Y",strtotime($get_data["fecha_fin"]));
		$mes_fin_file = date("m",strtotime($get_data["fecha_fin"]));
		$fecha_fin_file = $meses_nombre[(int)$mes_fin_file-1].'_'.$anio_fin_file;

		$titulo_reporte ="DEL ".date("d-m-Y",strtotime($get_data["fecha_inicio"]))." AL ".date("d-m-Y",strtotime($fecha_fin_reporte));
		$titulo_reporte_admin_productos= "REPORTE ADMIN PRODUCTOS ".$titulo_reporte;
		$titulo_file_reporte_admin_productos = "comercial_reporte_admin_productos_".$fecha_inicio_file."_al_".$fecha_fin_file."_".date("Ymdhis");	

		if (isset($titulo_reporte_admin_productos)) {
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

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B2',$titulo_reporte_admin_productos);
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells("B2:Q2");	
			$objPHPExcel->getActiveSheet()->getStyle("B2:Q2")->applyFromArray($estiloTituloReporte);
			

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('B3',"Zona");
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue('C3',empty($get_data["zona_nombre"])?"Todos":$get_data["zona_nombre"]);
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells("C3:F3");	

			$row_first_head =5;
			
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B".$row_first_head,"MESES");
			$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(17);
			$row_letter = "C";
			if(in_array(16, $canal) || in_array(17, $canal)){
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue($row_letter.$row_first_head,"Apuestas Deportivas");
				$objPHPExcel->getActiveSheet()->getColumnDimension($row_letter)->setWidth(18);
				$row_letter++;
			}
			if(in_array(21, $canal)){
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue($row_letter.$row_first_head,"Juegos Virtuales");
				$objPHPExcel->getActiveSheet()->getColumnDimension($row_letter)->setWidth(18);
				$row_letter++;
			}
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($row_letter.$row_first_head,"Total General");
			$objPHPExcel->getActiveSheet()->getColumnDimension($row_letter)->setWidth(18);

			$objPHPExcel->getActiveSheet()->getStyle("B".$row_first_head.":".$row_letter.$row_first_head)->applyFromArray($estiloTituloCabeceraTabla);

			$row_letter="B";
			$row_first_head=6;

			$meses_nombre = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
			
			foreach($sin_repetidos as $posicion=>$mes)
			{
				$valor_fecha = explode("-",$mes);
				$num_mes = $valor_fecha[0];
				$nombre_mes = $meses_nombre[(int)$num_mes-1];
				$total_fila = 0;
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue($row_letter.$row_first_head,$nombre_mes." ".$valor_fecha[1]);
				$row_letter++;
				$apuestas_deportivas = 0;
				$juegos_virtuales = 0;
				foreach($canal as $posicion=>$canal_id)
				{
					foreach($total_admin_productos as $posicion_buscar=>$data_buscar)
					{
						if($canal_id==16 && ($data_buscar["canal_de_venta_id"]==16 || $data_buscar["canal_de_venta_id"]==27) && $mes == $data_buscar["fecha"]){
							$apuestas_deportivas += $data_buscar["total_apostado"];
						}
						if($canal_id==17 && ($data_buscar["canal_de_venta_id"]==17 || $data_buscar["canal_de_venta_id"]==25) && $mes == $data_buscar["fecha"]){
							$apuestas_deportivas += $data_buscar["total_apostado"];
						}
						if($canal_id==21 && $data_buscar["canal_de_venta_id"]==21 && $mes == $data_buscar["fecha"]){
							$juegos_virtuales += $data_buscar["total_apostado"];
						}
					};
				};
				if(in_array(16, $canal) || in_array(17, $canal)){
					$objPHPExcel->setActiveSheetIndex(0)->setCellValue($row_letter.$row_first_head,number_format($apuestas_deportivas,2,'.',false));
					$row_letter++;
				}
				if(in_array(21, $canal)){
					$objPHPExcel->setActiveSheetIndex(0)->setCellValue($row_letter.$row_first_head,number_format($juegos_virtuales,2,'.',false));
					$row_letter++;
				}
				$total_fila = $apuestas_deportivas+$juegos_virtuales;
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue($row_letter.$row_first_head,number_format($total_fila,2,'.',false));
				$row_letter++;
				$row_first_head++;
				$row_letter="B";
			}

			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($row_letter.$row_first_head,"Total General");
			$objPHPExcel->getActiveSheet()->getStyle("B".$row_first_head.":B".$row_first_head)->applyFromArray($estiloTituloCabeceraTabla);	

			$row_letter++;
			if(in_array(16, $canal) || in_array(17, $canal)){
				$objPHPExcel->getActiveSheet(0)->setCellValue($row_letter.$row_first_head,"=SUM(".$row_letter."6:".$row_letter."".($row_first_head-1).")");
				$row_letter++;
			
			}
			if(in_array(21, $canal)){
				$objPHPExcel->getActiveSheet(0)->setCellValue($row_letter.$row_first_head,"=SUM(".$row_letter."6:".$row_letter."".($row_first_head-1).")");
				$row_letter++;
			}
			$objPHPExcel->getActiveSheet(0)->setCellValue($row_letter.$row_first_head,"=SUM(".$row_letter."6:".$row_letter."".($row_first_head-1).")");

			$objPHPExcel->getActiveSheet()->getStyle('C6:'.$row_letter.($row_first_head))->getNumberFormat()->setFormatCode('#,##0.00');

			$objPHPExcel->getActiveSheet()->setTitle("Libro 1");

			// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
			$objPHPExcel->setActiveSheetIndex(0);

			// Se manda el archivo al navegador web, con el nombre que se indica (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'.$titulo_file_reporte_admin_productos.'.xls"');
			header('Cache-Control: max-age=0');

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$objWriter->setPreCalculateFormulas(true);
			$excel_path = '/var/www/html/export/files_exported/'.$titulo_file_reporte_admin_productos.'.xls';
			$excel_path_download = '/export/files_exported/'.$titulo_file_reporte_admin_productos
			.'.xls';
			$url = $titulo_file_reporte_admin_productos.'.xls';			
			$objWriter->save($excel_path);

			$insert_cmd = "INSERT INTO tbl_exported_files (url,tipo,ext,size,fecha_registro,usuario_id)";
			$insert_cmd.= " VALUES ('".$url."','excel','xls','".filesize($excel_path)."','".date("Y-m-d h:i:s")."','".$login["id"]."')";
			$mysqli->query($insert_cmd);

			echo json_encode(array(
			    "path" => $excel_path_download,
			    "url" => $titulo_file_reporte_admin_productos.'.xls',
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

?>