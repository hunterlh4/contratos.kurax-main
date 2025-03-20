<?php
	if(isset($_POST["sec_caja_concar_excel"])){
		$get_data = $_POST["sec_caja_concar_excel"];
		$return = array();
		$return["memory_init"]=memory_get_usage();
		$return["time_init"] = microtime(true);
		date_default_timezone_set("America/Lima");
		include '/var/www/html/sys/globalFunctions/generalInfo/parameterGeneral.php';
		include("../sys/global_config.php");
		include("../sys/db_connect.php");
		include("../sys/sys_login.php");

		$local_id = $get_data["local_id"];
		$zona_id = $get_data["zona_id"];
		$tipo_cambio = $get_data["tipo_cambio"];
		$fecha_inicio = date("Y-m-d",strtotime($get_data["fecha_inicio_concar"]));
		$fecha_inicio_pretty = date("d-m-Y",strtotime($get_data["fecha_inicio_concar"]));
		$fecha_fin = date("Y-m-d",strtotime($get_data["fecha_fin_concar"]));
		$fecha_fin = date('Y-m-d', strtotime("+1 day", strtotime($fecha_fin)));
		$fecha_fin_pretty = date("d/m/Y",strtotime($get_data["fecha_fin_concar"]));
		$is_terminal = $get_data["is_terminal"];
		$red_id = $is_terminal == "true" ? "(7)" : "(1,16)";

		//	Filtrado por permisos de locales

		$permiso_locales="";
		if($login && $login["usuario_locales"]){
			$permiso_locales=" l.id IN (".implode(",", $login["usuario_locales"]).") ";
			}

		if($local_id !=""){
			
			$locales = array();
			$local_titulo = array();
			$sql_command = "SELECT id,nombre FROM tbl_locales";
			$sql_query = $mysqli->query($sql_command);
			while($itm=$sql_query->fetch_assoc()){
				$locales[$itm["id"]] = str_replace(" ","_",strtolower($itm["nombre"]));
				$local_titulo[$itm["id"]] = strtoupper($itm["nombre"]);
			}
			
			$caja_arr = array();
			$caja_command = "
				SELECT 
					c.id,
					c.fecha_operacion,
					c.turno_id,
					cd.id AS caja_deposito_id,
					cd.validar_registro,
					l.id AS local_id,
					l.nombre AS local_nombre,
					l.cc_id,
					(SELECT 
							SUM(IFNULL(df.valor,0))
						FROM tbl_caja_datos_fisicos df 
						WHERE df.caja_id = c.id AND df.tipo_id = '4') AS depo_venta,
					(SELECT 
							SUM(IFNULL(df.valor,0))
						FROM tbl_caja_datos_fisicos df 
						WHERE df.caja_id = c.id AND df.tipo_id = '3') AS depo_boveda,
					rtb.numero_movimiento,
                    rtb.codigo,
                    rtb.importe,
                    rtb.fecha_operacion as fecha_Excel,
                    rtb.oficina,
                    rtb.referencia,
					nc.num_cuenta_contable,
					tp.nombre AS tipo_pago_nombre,
					nc.tipo_pago_id,
					nc.subdiario,
					l.razon_social_id AS razon_social_local,
					nc.razon_social_id AS razon_social_cuenta_contable,
					rtb.id AS rep_transacciones_bancarias_id
				FROM tbl_caja c
				LEFT JOIN tbl_caja_depositos cd ON(cd.caja_id = c.id)
				LEFT JOIN tbl_local_cajas lc ON(lc.id = c.local_caja_id)
				LEFT JOIN tbl_locales l ON (l.id = lc.local_id)
                LEFT JOIN tbl_repositorio_transacciones_bancarias rtb ON(rtb.caja_id = c.id)
				INNER JOIN cont_num_cuenta nc ON rtb.cuenta_id = nc.id
				LEFT JOIN cont_tipo_programacion tp ON tp.id = nc.tipo_pago_id
				";
			// $caja_command.=" WHERE l.red_id = '1'";
			$caja_command.=" WHERE c.estado = '1'";
			$caja_command.=" AND l.red_id IN $red_id";
			$caja_command.=" AND c.validar = '1'";
			if($local_id=="_all_" || $local_id=="_all_terminales_"){
				// $caja_command.=" WHERE l.id != 1";
			}else{
				$caja_command.=" AND l.id = '".$local_id."'";
			}
			if($zona_id != "_all_"){
				$caja_command.=" AND l.zona_id = '".$zona_id."'";
			}
			$caja_command.=" AND l.zona_id IN (1,7,16,17,18,19,20,21,22,23) ";
			$caja_command.="AND c.fecha_operacion >= '".$fecha_inicio."'
							AND c.fecha_operacion < '".$fecha_fin."'
							AND rtb.tipo=0
							AND cd.validar_registro=1
							AND $permiso_locales 
							-- GROUP BY cd.caja_id
							ORDER BY c.fecha_operacion ASC, l.nombre ASC, c.turno_id ASC
								";
			//echo $caja_command; exit();
			$mysqli->query("SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED");
			
			$caja_query = $mysqli->query($caja_command);
			if($mysqli->error){
				print_r($mysqli->error);
				exit();
			};
			$mysqli->query("COMMIT");

			$table=array();
			$table["tbody"]=array();
			$caja_data = array();

			$codigo_cajas =array();
			$local_final = [];
			while($c=$caja_query->fetch_assoc()){
				$caja_data[]=$c;
				$local_final[] = $c["local_id"]; 
				array_push($codigo_cajas, $c['local_id']);
			}
			$codigo_cajas_agrupado=array_values(array_unique($codigo_cajas));

			$fecha_inicio_correlativo = date("Y-m-01",strtotime($get_data["fecha_inicio_concar"]));
			$fecha_fin_correlativo = date("Y-m-01",strtotime($get_data["fecha_fin_concar"]));

			$locales_concar = [];
			$result = $mysqli->query("SELECT id, cc_id FROM tbl_locales");
			while($r = $result->fetch_assoc()) $locales_concar[$r["cc_id"]] = $r["id"];

			$caja_correlativo=$get_data["correlativo_inicial"];
			foreach ($codigo_cajas_agrupado as $key => $valueCaja) { 
				$haber_importe_Original=0;
				$haber_importe_dolares=0;
				$haber_importe_soles=0;
				$nombre_Glosa="";
				$cc="";

				// $sql_correlativo_actual = "SELECT id,caja_id,correlativo,fecha FROM tbl_caja_correlativo_concar WHERE caja_id=".$valueCaja." and  fecha = '".$fecha_fin_correlativo."'";	
				// $result_ = $mysqli->query($sql_correlativo_actual);
				// if($result_->num_rows>0){
				// 	$row_ = $result_->fetch_assoc();
				// 	$caja_correlativo = $row_["correlativo"];
				// }
				// else{
				// 	$sql_correlativo_anterior = "SELECT id,caja_id,correlativo,fecha FROM tbl_caja_correlativo_concar WHERE caja_id=".$valueCaja." order by id desc  LIMIT 1";
				// 	$result_anterior = $mysqli->query($sql_correlativo_anterior);
				// 	if($result_anterior->num_rows>0){
				// 		$row_anterior = $result_anterior->fetch_assoc();
				// 		$caja_correlativo = $row_anterior["correlativo"];
				// 	}
				// 	else{
				// 		$sql_correlativo_mayor = "SELECT id,caja_id,correlativo,fecha from tbl_caja_correlativo_concar order by correlativo desc LIMIT 1";
				// 		$result_mayor = $mysqli->query($sql_correlativo_mayor);
				// 		if($result_mayor->num_rows>0){
				// 			$row_mayor = $result_mayor->fetch_assoc();
				// 			$caja_correlativo = $row_mayor["correlativo"]+1;
				// 		}
				// 		else{
				// 			$caja_correlativo=1;
				// 		}

				// 		$insert_command = "INSERT INTO tbl_caja_correlativo_concar (caja_id,correlativo,fecha)";
				// 		$insert_command.= "VALUES (".$valueCaja.",".$caja_correlativo.",'".$fecha_fin_correlativo."')";
				// 		$mysqli->query($insert_command);
				// 		if($mysqli->error){
				// 			print_r($mysqli->error);
				// 			echo "\n";
				// 			echo $insert_command_deposito;
				// 			exit();
				// 		}
				// 	}
				// }

				foreach ($caja_data as $key => $value) {

					if($valueCaja==$value["local_id"]){
						$nombre_Glosa='Recaudacion '.$value["cc_id"].' '.$value["local_nombre"];
						$cc=$value["cc_id"];
						$haber_importe_Original+=(float)$value["importe"];
						$haber_importe_dolares+=(float)$value["importe"]/(float)$tipo_cambio;
						$haber_importe_soles+=(float)$value["importe"];
						$fecha_Deposito =date("d/m/Y",strtotime($value["fecha_Excel"]));
						$nro_documento = '0'.date("m-Y", strtotime($value["fecha_Excel"]));
						$nro_comprobante = date("m", strtotime($value["fecha_Excel"])).zerofill($caja_correlativo,4);
						date("m").zerofill($caja_correlativo,4);
						$referencia = $value["referencia"];
						$razon_social_local=(int)$value["razon_social_local"];
						$razon_social_cuenta_contable=(int)$value["razon_social_cuenta_contable"];
						$cuenta_contable = $value["num_cuenta_contable"];
						$subdiario = $value["subdiario"];
						$rep_transacciones_bancarias_id = $value["rep_transacciones_bancarias_id"];
						$tipo_pago_id = $value["tipo_pago_id"];
						$tipo_pago_nombre = $value["tipo_pago_nombre"];

						//$cuenta_contable = $is_terminal == "true" ? '10411112' : '10411024';
						//$codigo_anexo = $is_terminal == "true" ? '10411112' : '10411024';

						//---------------- Condición para verificar si el local pertenece a la empresa IGH
						$razon_social_igh = getParameterGeneral('razon_social_igh');

						if((int)$razon_social_local==(int)$razon_social_igh && $tipo_pago_nombre =="Ingreso"){

								//---------------- Condición para verificar si la cuenta contable coincide con la razon social del local
								if($razon_social_local!=$razon_social_cuenta_contable){

									$selectQuery = "SELECT 
														nc.id,
														nc.num_cuenta_contable,
														nc.subdiario
													FROM cont_num_cuenta nc
													LEFT JOIN cont_tipo_programacion tp ON tp.id = nc.tipo_pago_id
													WHERE nc.status = '1' AND nc.razon_social_id = ? AND nc.num_cuenta_contable = ? AND tp.nombre = ?
													LIMIT 1";

									$selectStmt = $mysqli->prepare($selectQuery);
									$selectStmt->bind_param("iss", $razon_social_local,$cuenta_contable,$tipo_pago_nombre);
									$selectStmt->execute();
									$selectStmt->store_result();

									if ($selectStmt->num_rows > 0) {
											$selectStmt->bind_result($cuenta_contable_id, $num_cuenta_contable, $subdiario_cuenta);
											$selectStmt->fetch();
											$cuenta_contable= $num_cuenta_contable;
											$subdiario=$subdiario_cuenta;

											//---------------- Cambiar la cuenta contable adjuntada en la tabla tbl_repositorio_transacciones_bancarias a la que le corresponde por su razon social
											$UpdateQuery = "UPDATE tbl_repositorio_transacciones_bancarias
															SET
																cuenta_id = ?, 
																ultima_edicion = ?
															WHERE 
																id = ?";

											$updateStmt = $mysqli->prepare($UpdateQuery);

											$timestamp = date("Y-m-d H:i:s");
											$updateStmt->bind_param("isi", $cuenta_contable_id, $timestamp, $rep_transacciones_bancarias_id);
											$updateStmt->execute();
											$updateStmt->close();
											//----------------
										}else{
											$cuenta_contable= "Sin cuenta contable";
											$subdiario= "Sin cuenta contable";
										}
										$selectStmt->close();
										}

								}


						$codigo_anexo = $cuenta_contable;
						/*
						$tipo_pago_id = $value["tipo_pago_id"];
						if($tipo_pago_id == 4){
							$subdiario = $value["subdiario"];
						}else{
							$subdiario = '2120';
						}
						*/
						// if($referencia == "DEPOSITO SIN LIBRETA" || $referencia == "DEPOSITO SIN TARJETA" )
						// {/*CAJA PIURA*/
						// 	$cuenta_contable = "10411101";
						// 	$codigo_anexo = "10411101";
						// }
						// if($referencia == "DEPOSITO")
						// {/*CAJA HUANCAYO*/
						// 	$cuenta_contable = "10411128";
						// 	$codigo_anexo = "10411128";
						// }

						$tr=array();
						$tr["sub_Diario"]=$subdiario;
						$tr["nro_Comprobante"]=$nro_comprobante;
						$tr["fecha_Comprobante"]=$fecha_fin_pretty;
						$tr["codigo_Moneda"]='MN';
						$tr["glosa_Principal"]= cortar_cadena('Recaudacion '.$value["cc_id"].' '.$value["local_nombre"], 40);
						$tr["tipo_Cambio"]=$tipo_cambio;
						$tr["tipo_Conversion"]='V';
						$tr["flag_Conversion"]='S';
						$tr["fecha_Tipo_Cambio"]=$fecha_fin_pretty;
						$tr["cuenta_Contable"]= $cuenta_contable;
						$tr["codigo_Anexo"]= $codigo_anexo;
						$tr["codigo_Centro_Costo"]='';
						$tr["debe_Haber"]='D';
						$tr["importe_Original"]=(float)$value["importe"];
						$tr["importe_Dolares"]=(float)$value["importe"]/(float)$tipo_cambio;
						$tr["importe_Soles"]=(float)$value["importe"];
						$tr["tipo_Documento"]='EN';
						$tr["nro_Documento"]=(string)$value["numero_movimiento"];
						$tr["fecha_Documento"]=date("d/m/Y",strtotime($value["fecha_Excel"]));
						$tr["fecha_Vencimiento"]=date("d/m/Y",strtotime($value["fecha_Excel"]));
						$tr["codigo_Area"]='101';
						$tr["glosa_Detalle"]=cortar_cadena('Recaudacion '.$value["cc_id"].' '.$value["local_nombre"],30);
						$tr["codigo_Anexo_Auxiliar"]='';
						$tr["medio_Pago"]='001';
						$tr["tipo_Documento_Referencia"]='';
						$tr["numero_Documento_Referencia"]='';
						$tr["fecha_Documento_Referencia"]='';
						$tr["nro_Registradora"]='';
						$tr["base_Imponible"]='';
						$tr["igv_Documento"]='';
						$tr["tipo_Referencia"]='';
						$tr["numero_Serie"]='';
						$tr["fecha_Operacion"]='';
						$tr["tipo_Tasa"]='';
						$tr["tasa_Detraccion_Percepcion"]='';
						$tr["importe_Base_Dolares"]='';
						$tr["importe_Base_Soles"]='';
						$tr["tipo_Cambio_F"]='';
						$tr["importe_Igv_Fiscal"]='';

						$table["tbody"][]=$tr;

					}
				}
				$where =" WHERE created_at >= '{$fecha_inicio}' AND created_at < '{$fecha_fin}' AND local_id = ".$locales_concar[$cc];

				$atsnacks_total = 0;
				$result = $mysqli->query("SELECT IFNULL(sum(total),0) as total from tbl_repositorio_atsnacks_resumen".$where);
				while($r = $result->fetch_assoc()) $atsnacks_total = (($r["total"] > 0) ? $r["total"] : 0);

				$kasnet_total = 0;
				$result = $mysqli->query("SELECT IFNULL(sum(total),0) as total from tbl_repositorio_kasnet_resumen".$where);
				while($r = $result->fetch_assoc()) $kasnet_total = (($r["total"] > 0) ? $r["total"] : 0);

				if($haber_importe_Original >= $atsnacks_total) $haber_importe_Original -= $atsnacks_total;
				else{
					$atsnacks_total = $haber_importe_Original;
					$haber_importe_Original = 0;
				}

				if($haber_importe_Original >= $kasnet_total) $haber_importe_Original -= $kasnet_total;
				else{
					$kasnet_total = $haber_importe_Original;
					$haber_importe_Original = 0;
				}

				$haber_importe_soles = $haber_importe_Original;
				$haber_importe_dolares = ($haber_importe_Original/(float)$tipo_cambio);

				//ATSNACKS
					$tr=array();
					$tr["sub_Diario"]=$subdiario;
					$tr["nro_Comprobante"]=$nro_comprobante;
					$tr["fecha_Comprobante"]=$fecha_fin_pretty;
					$tr["codigo_Moneda"]='MN';
					$tr["glosa_Principal"]=cortar_cadena($nombre_Glosa,40);
					$tr["tipo_Cambio"]=$tipo_cambio;
					$tr["tipo_Conversion"]='V';
					$tr["flag_Conversion"]='S';
					$tr["fecha_Tipo_Cambio"]=$fecha_fin_pretty;
					$tr["cuenta_Contable"]='101121';
					$tr["codigo_Anexo"]=$cc.'-FONDO BOVEDA';
					$tr["codigo_Centro_Costo"]='';
					$tr["debe_Haber"]='H';
					$tr["importe_Original"]=number_format($atsnacks_total, 2, ".", "");
					$tr["importe_Dolares"]=number_format(($atsnacks_total/(float)$tipo_cambio), 2, ".", "");
					$tr["importe_Soles"]=number_format($atsnacks_total, 2, ".", "");
					$tr["tipo_Documento"]='ME';
					$tr["nro_Documento"]=$nro_documento;
					$tr["fecha_Documento"]=$fecha_Deposito;
					$tr["fecha_Vencimiento"]=$fecha_Deposito;
					$tr["codigo_Area"]='343';
					$tr["glosa_Detalle"]=cortar_cadena($nombre_Glosa,30);
					$tr["codigo_Anexo_Auxiliar"]='A0003';
					$tr["medio_Pago"]='';
					$tr["tipo_Documento_Referencia"]='ME';
					$tr["numero_Documento_Referencia"]=$nro_documento;
					$tr["fecha_Documento_Referencia"]=$fecha_Deposito;
					$tr["nro_Registradora"]='';
					$tr["base_Imponible"]='';
					$tr["igv_Documento"]='';
					$tr["tipo_Referencia"]='';
					$tr["numero_Serie"]='';
					$tr["fecha_Operacion"]='';
					$tr["tipo_Tasa"]='';
					$tr["tasa_Detraccion_Percepcion"]='';
					$tr["importe_Base_Dolares"]='';
					$tr["importe_Base_Soles"]='';
					$tr["tipo_Cambio_F"]='';
					$tr["importe_Igv_Fiscal"]='';
					$table["tbody"][]=$tr;

				//KASNET
					$tr=array();
					$tr["sub_Diario"]=$subdiario;
					$tr["nro_Comprobante"]=$nro_comprobante;
					$tr["fecha_Comprobante"]=$fecha_fin_pretty;
					$tr["codigo_Moneda"]='MN';
					$tr["glosa_Principal"]=cortar_cadena($nombre_Glosa,40);
					$tr["tipo_Cambio"]=$tipo_cambio;
					$tr["tipo_Conversion"]='V';
					$tr["flag_Conversion"]='S';
					$tr["fecha_Tipo_Cambio"]=$fecha_fin_pretty;
					$tr["cuenta_Contable"]='101121';
					$tr["codigo_Anexo"]=$cc.'-FONDO BOVEDA';
					$tr["codigo_Centro_Costo"]='';
					$tr["debe_Haber"]='H';
					$tr["importe_Original"]=number_format($kasnet_total, 2, ".", "");
					$tr["importe_Dolares"]=number_format(($kasnet_total/(float)$tipo_cambio), 2, ".", "");
					$tr["importe_Soles"]=number_format($kasnet_total, 2, ".", "");
					$tr["tipo_Documento"]='ME';
					$tr["nro_Documento"]=$nro_documento;
					$tr["fecha_Documento"]=$fecha_Deposito;
					$tr["fecha_Vencimiento"]=$fecha_Deposito;
					$tr["codigo_Area"]='343';
					$tr["glosa_Detalle"]=cortar_cadena($nombre_Glosa,30);
					$tr["codigo_Anexo_Auxiliar"]='A0003';
					$tr["medio_Pago"]='';
					$tr["tipo_Documento_Referencia"]='ME';
					$tr["numero_Documento_Referencia"]=$nro_documento;
					$tr["fecha_Documento_Referencia"]=$fecha_Deposito;
					$tr["nro_Registradora"]='';
					$tr["base_Imponible"]='';
					$tr["igv_Documento"]='';
					$tr["tipo_Referencia"]='';
					$tr["numero_Serie"]='';
					$tr["fecha_Operacion"]='';
					$tr["tipo_Tasa"]='';
					$tr["tasa_Detraccion_Percepcion"]='';
					$tr["importe_Base_Dolares"]='';
					$tr["importe_Base_Soles"]='';
					$tr["tipo_Cambio_F"]='';
					$tr["importe_Igv_Fiscal"]='';
					$table["tbody"][]=$tr;

				//APUESTAS
					$tr=array();
					$tr["sub_Diario"]=$subdiario;
					$tr["nro_Comprobante"]=$nro_comprobante;
					$tr["fecha_Comprobante"]=$fecha_fin_pretty;
					$tr["codigo_Moneda"]='MN';
					$tr["glosa_Principal"]=cortar_cadena($nombre_Glosa,40);
					$tr["tipo_Cambio"]=$tipo_cambio;
					$tr["tipo_Conversion"]='V';
					$tr["flag_Conversion"]='S';
					$tr["fecha_Tipo_Cambio"]=$fecha_fin_pretty;
					$tr["cuenta_Contable"]='101121';
					$tr["codigo_Anexo"]=$cc.'-FONDO BOVEDA';
					$tr["codigo_Centro_Costo"]='';
					$tr["debe_Haber"]='H';
					$tr["importe_Original"]=number_format($haber_importe_Original, 2, ".", "");
					$tr["importe_Dolares"]=number_format($haber_importe_dolares, 2, ".", "");
					$tr["importe_Soles"]=number_format($haber_importe_soles, 2, ".", "");
					$tr["tipo_Documento"]='ME';
					$tr["nro_Documento"]=$nro_documento;
					$tr["fecha_Documento"]=$fecha_Deposito;
					$tr["fecha_Vencimiento"]=$fecha_Deposito;
					$tr["codigo_Area"]='343';
					$tr["glosa_Detalle"]=cortar_cadena($nombre_Glosa,30);
					$tr["codigo_Anexo_Auxiliar"]='A0003';
					$tr["medio_Pago"]='';
					$tr["tipo_Documento_Referencia"]='ME';
					$tr["numero_Documento_Referencia"]=$nro_documento;
					$tr["fecha_Documento_Referencia"]=$fecha_Deposito;
					$tr["nro_Registradora"]='';
					$tr["base_Imponible"]='';
					$tr["igv_Documento"]='';
					$tr["tipo_Referencia"]='';
					$tr["numero_Serie"]='';
					$tr["fecha_Operacion"]='';
					$tr["tipo_Tasa"]='';
					$tr["tasa_Detraccion_Percepcion"]='';
					$tr["importe_Base_Dolares"]='';
					$tr["importe_Base_Soles"]='';
					$tr["tipo_Cambio_F"]='';
					$tr["importe_Igv_Fiscal"]='';
					$table["tbody"][]=$tr;

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
				'Importe de IGV sin derecho credito fisca',
				'Tasa IGV',
			);
				
			// $objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:D1');
		 
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
				->setCellValue('AN1',  $titulosColumnas[38])  //Titulo de las columnas
				->setCellValue('AO1',  $titulosColumnas[39])
				;  //Titulo de las columnas;
			
			
			$i= 4;
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
			//$objPHPExcel->getActiveSheet()->getStyle('A3:D3')->applyFromArray($estiloTituloColumnas);
			$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);
			//$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(1000);

			$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:AN".($i-1));

			// for($i = 'O'; $i <= 'D'; $i++){
			//     //$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
			    
			//     //$objPHPExcel->getActiveSheet()->getStyle('B2')->getNumberFormat()->setFormatCode('#,##0.00');
			// }

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

			// Se asigna el nombre a la hoja
			$objPHPExcel->getActiveSheet()->setTitle('Depositos');
			 
			// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
			$objPHPExcel->setActiveSheetIndex(0);
			 
			// Inmovilizar paneles
			//$objPHPExcel->getActiveSheet(0)->freezePane('A4');
			//$objPHPExcel->getActiveSheet(0)->freezePaneByColumnAndRow(0,4);

			$local_titulo_parcial = "";
			$local_id_parcial = "";
			$local_id_name = "";

			if ($get_data['local_id'] == '_all_'){
				$local_titulo_parcial = "Todos";
				$local_id_parcial = "Todos";
				$local_id_name = -1;
			} else if ($get_data['local_id'] == '_all_terminales_'){
				$local_titulo_parcial = "Todos_los_Terminales";
				$local_id_parcial = "Todos_los_Terminales";
				$local_id_name = -2;
			} else {
				$local_titulo_parcial = $local_titulo[$get_data['local_id']];
				$local_id_parcial = $locales[$get_data['local_id']];
				$local_id_name = $get_data['local_id'];
			}

			/*$local_titulo_parcial = ($get_data['local_id']=='_all_')?'Todos':$local_titulo[$get_data['local_id']];
			$local_id_parcial =($get_data['local_id']=='_all_')?'Todos':$locales[$get_data['local_id']];*/

			$titulo_reporte_cajas = "REPORTE CONCAR ".$local_titulo_parcial;
			$titulo_file_reporte_cajas = "Depositos_Caja_Concar_".$local_id_parcial."_".date("d-m-Y",strtotime($get_data["fecha_inicio_concar"]))."_al_".date("d-m-Y",strtotime($get_data["fecha_fin_concar"]))."_".date("Ymdhis");

			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'.$titulo_file_reporte_cajas.'.xls"');
			header('Cache-Control: max-age=0');

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$excel_path = '/var/www/html/export/files_exported/'.$titulo_file_reporte_cajas.'.xls';
			//$excel_path = '../export/files_exported/'.$titulo_file_reporte_cajas.'.xls';
			$excel_path_download = '/export/files_exported/'.$titulo_file_reporte_cajas.'.xls';
			$url = $titulo_file_reporte_cajas.'.xls';
			$objWriter->save($excel_path);

			$insert_cmd = "INSERT INTO tbl_exported_files (url,tipo,ext,size,fecha_registro,usuario_id)";
			$insert_cmd.= " VALUES ('".$url."','excel','xls','".filesize($excel_path)."','".date("Y-m-d h:i:s")."','".$login["id"]."')";
			$mysqli->query($insert_cmd);
			$exported_id = $mysqli->insert_id;

			$mysqli->query("
				INSERT INTO tbl_concar_historico (
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
					'".$get_data["fecha_inicio_concar"]."',
					'".$get_data["fecha_fin_concar"]."'
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
