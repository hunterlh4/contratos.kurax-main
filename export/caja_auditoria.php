<?php
	include("../sys/db_connect.php");
	include("../sys/sys_login.php");
	date_default_timezone_set("America/Lima");
	$post = array();
	$post = array("sec_caja_compare"=>array(
		"local_id" => $_POST["local_id"],
		"fecha_inicio" => $_POST["fecha_inicio"],
		"fecha_fin" => $_POST["fecha_fin"]	
	));

	if(isset($post["sec_caja_compare"])){
		$locales = array();
		$local_titulo = array();
		$sql_command = "SELECT id,nombre FROM tbl_locales";
		$sql_query = $mysqli->query($sql_command);
		while($itm=$sql_query->fetch_assoc()){
			$locales[$itm["id"]] = str_replace(" ","_",strtolower($itm["nombre"]));
			$local_titulo[$itm["id"]] = strtoupper($itm["nombre"]);
		}

		/***************************************** DATA ***********************************/
			$get_data = $post["sec_caja_compare"];
			// print_r($get_data);
			// exit();
			$local_id = $get_data["local_id"];
			$fecha_inicio = $get_data["fecha_inicio"];
			$fecha_inicio_pretty = date("d-m-Y",strtotime($get_data["fecha_inicio"]));
			// $fecha_fin = $get_data["fecha_fin"];
			$fecha_fin = date("Y-m-d",strtotime($get_data["fecha_fin"]." +1 day"));
			$fecha_fin_pretty = date("d-m-Y",strtotime($get_data["fecha_fin"]));
			$caja_arr = array();

			//  Obtener listado de operaciones de ingresos

				$op_ingreso = [];

				$op_ingreso_query = $mysqli->query("SELECT o.nombre
										FROM tbl_kasnet_operacion AS o
										WHERE o.status = 1  AND o.tipo_id = 1");
				while($op_ingresos = $op_ingreso_query->fetch_assoc())
				{
					$op_ingreso[] = "'" . $mysqli->real_escape_string($op_ingresos['nombre']) . "'";
				}
			
				$op_ingreso_list = implode(', ', $op_ingreso);
		
			//  Obtener listado de operaciones de salida
			
				$op_salida = [];
			
				$op_salida_query = $mysqli->query("SELECT o.nombre
										FROM tbl_kasnet_operacion AS o
										WHERE o.status = 1  AND o.tipo_id = 2");
				while($op_salidas = $op_salida_query->fetch_assoc())
				{
					$op_salida[] = "'" . $mysqli->real_escape_string($op_salidas['nombre']) . "'";
				}
			
				$op_salida_list = implode(', ', $op_salida);
		
			$caja_command = "SELECT 
								c.fecha_operacion,
								l.id AS local_id,
								l.nombre AS local_nombre,
								ca.dinero_cajero,
								-- ca.total_balance AS dinero_sistema,
								CAST(
									(
										SELECT
											CAST(
													(
														SUM(
															IF(
																r.tipo = 3,
																(r.bets_amount + r.terminal_deposit_amount + r.deposits),
																IF(r.tipo = 2,
																	(r.income_amount),
																	IF(r.tipo = 4,
																		r.stake,
																		0
																		)
																	)
																)
															)
													)
													-
													(
														SUM(
															IF(r.tipo = 3,
																(r.paid_win_amount + r.terminal_withdraw_amount + r.withdrawals),
																IF(r.tipo = 2,
																	(0),
																	IF(r.tipo = 4,
																		(IFNULL(r.paid_out_cash,0) + IFNULL(r.jackpot_paid,0) + IFNULL(r.mega_jackpot_paid,0)),
																		0
																		)
																	)
																)
															)
													)
													AS DECIMAL(20,2)
												) AS total_balance
										FROM tbl_transacciones_repositorio r
										WHERE r.local_id = l.id
										AND r.created = c.fecha_operacion
										AND r.tipo IN (2,3,4)
									) AS DECIMAL(20,2)
								) 
								+ (CAST((SELECT (IFNULL(SUM(total),0) - IFNULL(SUM(note_total),0)) AS atsnacks_total FROM tbl_repositorio_atsnacks_resumen WHERE local_id = l.id AND datediff(created_at,c.fecha_operacion) = 0) AS DECIMAL(20,2))) + 
						        (CAST(((SELECT 
						                IFNULL(SUM(monto_operacion),0)
						            FROM tbl_repositorio_kasnet_ventas
						            WHERE
						                local_id = l.id
										AND datediff(fecha_operacion,c.fecha_operacion) = 0
										AND descripcion_operacion IN($op_ingreso_list)
					                    AND estado = 'Correcta') - (SELECT 
						                IFNULL(SUM(monto_operacion),0)
						            FROM tbl_repositorio_kasnet_ventas
						            WHERE
						                local_id = l.id
										AND datediff(fecha_operacion,c.fecha_operacion) = 0
										AND descripcion_operacion IN($op_salida_list)
					                    AND estado = 'Correcta'))
						        AS DECIMAL (20 , 2 ))) AS dinero_sistema,
								ca.cajero_pagos_manuales,
								-- CAST(SUM(IF(cdf.tipo_id=5,cdf.valor,0)) AS DECIMAL(20,2)) AS dinero_cajero,
								-- CAST(SUM(IF(cdf.tipo_id=9,cdf.valor,0)) AS DECIMAL(20,2)) AS cajero_pagos_manuales,
								-- CAST(SUM(0) AS DECIMAL(20,2)) AS devoluciones_cajero,
								ca.devoluciones_cajero AS cajero_devolucion,
								-- CAST(SUM(0) AS DECIMAL(20,2)) AS sistema_pagos_manuales,
								CAST(SUM(0) AS DECIMAL(20,2)) AS no_reclamado,
								-- CAST(SUM(tc.caja_fisico) AS DECIMAL(20,2)) AS resultado_voucher,
								CAST(
									(
										SELECT 
											SUM(tc.caja_fisico) 
										FROM tbl_transacciones_cabecera tc 
										WHERE tc.local_id = l.id 
										AND tc.estado = '1' 
										AND tc.fecha = c.fecha_operacion
									) AS DECIMAL(20,2)
								)
								+ 
								(CAST((SELECT (IFNULL(SUM(total),0) - IFNULL(SUM(note_total),0)) AS atsnacks_total FROM tbl_repositorio_atsnacks_resumen WHERE local_id = l.id AND datediff(created_at,c.fecha_operacion) = 0) AS DECIMAL(20,2))) + 
						        (CAST(((SELECT 
						                IFNULL(SUM(monto_operacion),0)
						            FROM tbl_repositorio_kasnet_ventas
						            WHERE
						                local_id = l.id
										AND datediff(fecha_operacion,c.fecha_operacion) = 0
										AND descripcion_operacion IN($op_ingreso_list)
					                    AND estado = 'Correcta') - (SELECT 
						                IFNULL(SUM(monto_operacion),0)
						            FROM tbl_repositorio_kasnet_ventas
						            WHERE
						                local_id = l.id
										AND datediff(fecha_operacion,c.fecha_operacion) = 0
										AND descripcion_operacion IN($op_salida_list)
					                    AND estado = 'Correcta'))
						        AS DECIMAL (20 , 2 )))
								AS resultado_voucher,
								CAST(
									(
										SELECT 
											(
		 										SUM(ABS(IFNULL(pm.monto,0)))
											) AS monto
										FROM tbl_pago_manual pm
										WHERE pm.estado = '1'
										AND pm.local_id = l.id
										AND pm.fecha_pago = CONCAT(c.fecha_operacion,' 00:00:00')
									)
									AS DECIMAL(20,2)
								) AS sistema_pagos_manuales,
								CAST(
										(
											SELECT 
												SUM(tr.open_win)
											FROM tbl_transacciones_repositorio tr
											WHERE tr.local_id = l.id
											AND tr.tipo = 4
											AND tr.servicio_id = 3
											AND tr.created = c.fecha_operacion
										)
										AS DECIMAL(20,2)
									) AS premios_no_reclamados,
								CAST(
										(
											SELECT 
												SUM(tr.cancelled)
											FROM tbl_transacciones_repositorio tr
											WHERE tr.local_id = l.id
											AND tr.tipo = 4
											AND tr.servicio_id = 3
											AND tr.created = c.fecha_operacion
										)
										AS DECIMAL(20,2)
									) AS sistema_devolucion
							FROM tbl_caja c
							LEFT JOIN tbl_local_cajas lc ON(lc.id = c.local_caja_id)
							LEFT JOIN tbl_locales l ON (l.id = lc.local_id)
							LEFT JOIN view_caja_auditoria ca ON (ca.local_id = l.id AND ca.fecha_operacion = c.fecha_operacion)";

			if($local_id=="_all_"){
				$caja_command.=" WHERE l.id != 1";
			}else{
				$caja_command.=" WHERE l.id = '".$local_id."'";
			}
			$caja_command.=" AND c.fecha_operacion >= '".$fecha_inicio."'
							AND c.fecha_operacion < '".$fecha_fin."'
							GROUP BY c.fecha_operacion, l.id
							ORDER BY c.fecha_operacion ASC, l.nombre ASC
								";
			// echo $caja_command; exit();
			$myfile = fopen("/var/www/html/helpQuery.txt", "w") or die("Unable to open file!");
			$txt = $caja_command;
			fwrite($myfile, $txt);
			fclose($myfile);
			$mysqli->query("SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED");
			$caja_query = $mysqli->query($caja_command);
			if($mysqli->error){
				print_r($mysqli->error);
				exit();
			}
			$mysqli->query("COMMIT");
			$table=array();
			$table["tbody"]=array();
			$caja_data = array();
			while($c=$caja_query->fetch_assoc()){
				$caja_data[]=$c;
			}
			foreach ($caja_data as $key => $value) {
				// foreach ($value["datos_sistema"] as $kkey => $vvalue) {
				$tr=array();
				$tr["local_id"]=$value["local_id"];
				$tr["fecha_operacion"]=$value["fecha_operacion"];
				$tr["local_nombre"]=$value["local_nombre"];

				$tr["resultado_sistema"]=$value["dinero_sistema"];
				$tr["dinero_cajero"]=$value["dinero_cajero"];
				$tr["diferencia_resultado"]=($tr["dinero_cajero"] - $tr["resultado_sistema"]);


				$tr["cajero_pagos_manuales"]=$value["cajero_pagos_manuales"];
				$tr["sistema_pagos_manuales"]=$value["sistema_pagos_manuales"];
				$tr["diferencia_3"]=($tr["cajero_pagos_manuales"]-$tr["sistema_pagos_manuales"]);
				
				$tr["sistema_devolucion"]=$value["sistema_devolucion"];
				$tr["cajero_devolucion"]=$value["cajero_devolucion"];
				$tr["diferencia_4"]=($tr["sistema_devolucion"]-$tr["cajero_devolucion"]);

				$tr["resultado_voucher"]=$value["resultado_voucher"];
				$tr["premios_no_reclamados"]=$value["premios_no_reclamados"];
				$tr["diferencia_2"]=($tr["resultado_sistema"] - ($tr["resultado_voucher"]+$tr["sistema_devolucion"]+$tr["sistema_pagos_manuales"]));

				// $tr["canal_nombre"]=$value["lcdt_id"]." - ".$value["canal_nombre"];
				// $tr["s_ingreso"]=$value["s_ingreso"];
				// $tr["s_salida"]=$value["s_salida"];
				// $tr["opt"]="opt";
				$table["tbody"][]=$tr;
				// }
			}
		/**************************************FIN DATA ***********************************/
		$l = array();
		$cantidad_de_columnas_a_crear=1000; 
		$contador=0; 
		$letra='A'; 
		while($contador<=$cantidad_de_columnas_a_crear){ 
			$l[$contador] =  $letra;
			$contador++; 
			$letra++; 
		}	



		if ($get_data['local_id']=="_all_") {
				$titulo_resumen_caja_auditoria ="DEL ".date("d-m-Y",strtotime($get_data["fecha_inicio"]))." AL ".date("d-m-Y",strtotime($get_data["fecha_fin"]));
				$titulo_reporte_cajas_auditoria = "REPORTE CAJA AUDITORIA TODOS ".$titulo_resumen_caja_auditoria;
				$titulo_file_reporte_cajas_auditoria = "reporte_caja_auditoria_todos_".date("d-m-Y",strtotime($get_data["fecha_inicio"]))."_al_".date("d-m-Y",strtotime($get_data["fecha_fin"]))."_".date("Ymdhis");
		}else{
				$titulo_resumen_caja_auditoria ="DEL ".date("d-m-Y",strtotime($get_data["fecha_inicio"]))." AL ".date("d-m-Y",strtotime($get_data["fecha_fin"]));
				$titulo_reporte_cajas_auditoria = "REPORTE CAJA AUDITORIA ".$local_titulo[$get_data['local_id']]." ".$titulo_resumen_caja_auditoria;
				$titulo_file_reporte_cajas_auditoria = "reporte_caja_auditoria_".$locales[$get_data['local_id']]."_".date("d-m-Y",strtotime($get_data["fecha_inicio"]))."_al_".date("d-m-Y",strtotime($get_data["fecha_fin"]))."_".date("Ymdhis");	
		}




		if (isset($titulo_reporte_cajas_auditoria)) {
			require_once '../phpexcel/classes/PHPExcel.php';  
			$objPHPExcel = new PHPExcel();

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

			$estiloTituloCabeceraTablaPonerNombre = new PHPExcel_Style();
			$estiloTituloCabeceraTablaPonerNombre = array(
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
					'color' => array('argb' => '1cb787')
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
			$estiloTituloCabeceraTablaPagosManuales = new PHPExcel_Style();
			$estiloTituloCabeceraTablaPagosManuales = array(
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
					'color' => array('argb' => 'f0ad4e')
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
			$estiloTituloSubCabecera = new PHPExcel_Style();
			$estiloTituloSubCabecera = array(
				'font' => array(
					'name'      => 'Verdana',
					'bold'      => false,
					'italic'    => false,
					'strike'    => false,
					'size' =>9,
						'color'     => array(
							'rgb' => '333333'
						)
				),				
				'fill' => array(
					'type'  => PHPExcel_Style_Fill::FILL_SOLID,
					'color' => array('argb' => 'dddddd')
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
						'color' => array('rgb' => '888888')
					)
				)				
			);

			$estiloCeldasTablaResumen = new PHPExcel_Style();
			$estiloCeldasTablaResumen = array(
				'borders' => array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN,
						'color' => array('rgb' => '888888')
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

			$row_titulo = 1;
			$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue($l[0].$row_titulo,$titulo_reporte_cajas_auditoria);

			$row_first_head =3;
			$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue($l[0]."".$row_first_head,"Fecha")
			->setCellValue($l[1]."".$row_first_head,"Local")
			->setCellValue($l[2]."".$row_first_head,"Resultado")
			->setCellValue($l[5]."".$row_first_head,"Poner Nombre")
			->setCellValue($l[10]."".$row_first_head,"Pagos Manuales")                                                    
			->setCellValue($l[13]."".$row_first_head,"Devoluciones");
			
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells("A1:Q1");			
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells("A3:A4");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells("B3:B4");			
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells("C3:E3");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells("F3:J3");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells("K3:M3");
			$objPHPExcel->setActiveSheetIndex(0)->mergeCells("N3:P3");
			$objPHPExcel->getActiveSheet()->getStyle("A1:Q1")->applyFromArray($estiloTituloReporte);
			$objPHPExcel->getActiveSheet()->getStyle("A3")->applyFromArray($estiloTituloCabeceraTabla);
			$objPHPExcel->getActiveSheet()->getStyle("B3")->applyFromArray($estiloTituloCabeceraTabla);						
			$objPHPExcel->getActiveSheet()->getStyle("C3:E3")->applyFromArray($estiloTituloCabeceraTablaResultado);	
			$objPHPExcel->getActiveSheet()->getStyle("F3:J3")->applyFromArray($estiloTituloCabeceraTablaPonerNombre);	
			$objPHPExcel->getActiveSheet()->getStyle("K3:M3")->applyFromArray($estiloTituloCabeceraTablaPagosManuales);
			$objPHPExcel->getActiveSheet()->getStyle("N3:P3")->applyFromArray($estiloTituloCabeceraTabla);
			$objPHPExcel->getActiveSheet()->getStyle("C4:Q4")->applyFromArray($estiloTituloSubCabecera);
			
			$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
			$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(25);
			$objPHPExcel->getActiveSheet()->getRowDimension('3')->setRowHeight(18);
			$objPHPExcel->getActiveSheet()->getRowDimension('4')->setRowHeight(22);

			$row_second_head =4;
			$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue($l[2]."".$row_second_head,"Sistema")
			->setCellValue($l[3]."".$row_second_head,"Cajero")
			->setCellValue($l[4]."".$row_second_head,"Diferencia")
			->setCellValue($l[5]."".$row_second_head,"Resultado Sistema ")
			->setCellValue($l[6]."".$row_second_head,"Resultado Voucher ")                                                    
			->setCellValue($l[7]."".$row_second_head,"Devoluciones Sistema")
			->setCellValue($l[8]."".$row_second_head,"Pagos Manuales Sistema")
			->setCellValue($l[9]."".$row_second_head,"Diferencia")
			->setCellValue($l[10]."".$row_second_head,"Sistema")
			->setCellValue($l[11]."".$row_second_head,"Cajero")
			->setCellValue($l[12]."".$row_second_head,"Diferencia")	
			->setCellValue($l[13]."".$row_second_head,"Sistema")
			->setCellValue($l[14]."".$row_second_head,"Cajero")
			->setCellValue($l[15]."".$row_second_head,"Diferencia");
			$objPHPExcel->getActiveSheet()->freezePane('B5');

			$col_body= 0;
			$row_body=5;
			foreach ($table["tbody"] as $k => $tr) {

				$objPHPExcel->getActiveSheet()->getColumnDimension($l[$k])->setWidth(12);
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_body,$row_body,$tr["fecha_operacion"]);
				$col_body++;
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_body,$row_body,$tr["local_nombre"]);
				$col_body++;
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_body,$row_body,round($tr["resultado_sistema"],2));
				$col_body++;
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_body,$row_body,round($tr["dinero_cajero"],2));
				$col_body++;
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_body,$row_body,round($tr["diferencia_resultado"],2));
				$col_body++;

				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_body,$row_body,round($tr["resultado_sistema"],2));
				$col_body++;
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_body,$row_body,round($tr["resultado_voucher"],2));
				$col_body++;
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_body,$row_body,round($tr["sistema_devolucion"],2));
				$col_body++;
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_body,$row_body,round($tr["sistema_pagos_manuales"],2));
				$col_body++;
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_body,$row_body,round($tr["diferencia_2"],2));
				$col_body++;

				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_body,$row_body,round($tr["sistema_pagos_manuales"],2));
				$col_body++;
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_body,$row_body,round($tr["cajero_pagos_manuales"],2));
				$col_body++;
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_body,$row_body,round($tr["diferencia_3"],2));
				$col_body++;
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_body,$row_body,round($tr["sistema_devolucion"],2));	
				$col_body++;

				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_body,$row_body,round($tr["cajero_devolucion"],2));
				$col_body++;
				$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_body,$row_body,round($tr["diferencia_4"],2));
			
				$col_body = 0;
				$row_body++;
			}	

			$col_body_style= 0;
			$row_body_style=5;
			foreach ($table["tbody"] as $k => $tr) {
				foreach ($tr as $key => $value) {

					$objPHPExcel->getActiveSheet()->getStyle($l[$col_body_style]."".$row_body_style.":".$l[$col_body_style]."".$row_body_style)->applyFromArray($estiloCeldasTablaResumen);

					$conditionalStyles = $objPHPExcel->getActiveSheet()->getStyle("C".$row_body_style.":D".$row_body_style)->getConditionalStyles();
					array_push($conditionalStyles, $objConditionalNegativeNumber);
					$objPHPExcel->getActiveSheet()->getStyle("C".$row_body_style.":D".$row_body_style)->setConditionalStyles($conditionalStyles);

					$conditionalStyles = $objPHPExcel->getActiveSheet()->getStyle("F".$row_body_style.":J".$row_body_style)->getConditionalStyles();
					array_push($conditionalStyles, $objConditionalNegativeNumber);
					$objPHPExcel->getActiveSheet()->getStyle("F".$row_body_style.":J".$row_body_style)->setConditionalStyles($conditionalStyles);	

					$conditionalStyles = $objPHPExcel->getActiveSheet()->getStyle("K".$row_body_style.":M".$row_body_style)->getConditionalStyles();
					array_push($conditionalStyles, $objConditionalNegativeNumber);
					$objPHPExcel->getActiveSheet()->getStyle("K".$row_body_style.":M".$row_body_style)->setConditionalStyles($conditionalStyles);

					$conditionalStyles = $objPHPExcel->getActiveSheet()->getStyle("N".$row_body_style.":P".$row_body_style)->getConditionalStyles();
					array_push($conditionalStyles, $objConditionalNegativeNumber);
					$objPHPExcel->getActiveSheet()->getStyle("N".$row_body_style.":P".$row_body_style)->setConditionalStyles($conditionalStyles);
					
					if (floatval($tr["diferencia_resultado"])!=0 ) {
						$objPHPExcel->getActiveSheet()->getStyle("E".$row_body_style.":E".$row_body_style)->getFill()
						->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
						->getStartColor()->setARGB('FF0000');
						$objPHPExcel->getActiveSheet()->getStyle("E".$row_body_style.":E".$row_body_style)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);

					}
					if (floatval($tr["diferencia_2"])!=0) {
						$objPHPExcel->getActiveSheet()->getStyle("J".$row_body_style.":J".$row_body_style)->getFill()
						->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
						->getStartColor()->setARGB('FF0000');
						$objPHPExcel->getActiveSheet()->getStyle("J".$row_body_style.":J".$row_body_style)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
						
					}
					if (floatval($tr["diferencia_3"])!=0) {
						$objPHPExcel->getActiveSheet()->getStyle("M".$row_body_style.":M".$row_body_style)->getFill()
						->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
						->getStartColor()->setARGB('FF0000');
						$objPHPExcel->getActiveSheet()->getStyle("M".$row_body_style.":M".$row_body_style)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
						
					}					
					if (floatval($tr["diferencia_4"])!=0) {
						$objPHPExcel->getActiveSheet()->getStyle("P".$row_body_style.":P".$row_body_style)->getFill()
						->setFillType(PHPExcel_Style_Fill::FILL_SOLID)
						->getStartColor()->setARGB('FF0000');
						$objPHPExcel->getActiveSheet()->getStyle("P".$row_body_style.":P".$row_body_style)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_WHITE);
						
					}

					$objPHPExcel->getActiveSheet()->getStyle("C".$row_body_style.":".$l[$col_body_style]."".$row_body_style)->getNumberFormat()->setFormatCode('#,##0.00');

					$col_body_style++;
				}
				$col_body_style=0;
				$row_body_style++;
			}	

			// Se asigna el nombre a la hoja
			$objPHPExcel->getActiveSheet()->setTitle("Libro 1");

			// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
			$objPHPExcel->setActiveSheetIndex(0);

			// Se manda el archivo al navegador web, con el nombre que se indica (Excel2007)
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'.$titulo_file_reporte_cajas_auditoria.'.xls"');
			header('Cache-Control: max-age=0');

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$excel_path = '/var/www/html/export/files_exported/caja_auditoria/'.$titulo_file_reporte_cajas_auditoria.'.xls';
			$excel_path_download = '/export/files_exported/caja_auditoria/'.$titulo_file_reporte_cajas_auditoria.'.xls';
			$url = $titulo_file_reporte_cajas_auditoria.'.xls';			
			$objWriter->save($excel_path);


			$insert_cmd = "INSERT INTO tbl_exported_files (url,tipo,ext,size,fecha_registro,usuario_id)";
			$insert_cmd.= " VALUES ('".$url."','excel','xls','".filesize($excel_path)."','".date("Y-m-d h:i:s")."','".$login["id"]."')";
			$mysqli->query($insert_cmd);

			echo json_encode(array(
			    "path" => $excel_path_download,
			    "url" => $titulo_file_reporte_cajas_auditoria.'.xls',
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