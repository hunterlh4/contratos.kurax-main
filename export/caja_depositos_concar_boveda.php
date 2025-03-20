<?php
if(isset($_POST["sec_caja_concar_excel_boveda"])){
    $get_data = $_POST["sec_caja_concar_excel_boveda"];
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
    $tipo_cambio = $get_data["tipo_cambio"] === "" ? "1" : $get_data["tipo_cambio"];
    $fecha_inicio = date("Y-m-d",strtotime($get_data["fecha_inicio_concar_boveda"]));
    $fecha_inicio_pretty = date("d-m-Y",strtotime($get_data["fecha_inicio_concar_boveda"]));
    $fecha_fin = date("Y-m-d",strtotime($get_data["fecha_fin_concar_boveda"]));
    $fecha_fin = date('Y-m-d', strtotime("+1 day", strtotime($fecha_fin)));
    $fecha_fin_pretty = date("d/m/Y",strtotime($get_data["fecha_fin_concar_boveda"]));
    $caja_correlativo = is_numeric($get_data["correlativo_inicial"]) ? $get_data["correlativo_inicial"] : 1;
    $razon_social_igh = getParameterGeneral('razon_social_igh');

    //	Filtrado por permisos de locales

	$permiso_locales="";
	if($login && $login["usuario_locales"]){
		$permiso_locales=" AND l.id IN (".implode(",", $login["usuario_locales"]).") ";
		}


    $locales = array();
    $local_titulo = array();
    $sql_command = "SELECT id,nombre FROM tbl_locales";
    $sql_query = $mysqli->query($sql_command);
    while($itm=$sql_query->fetch_assoc()){
        $locales[$itm["id"]] = str_replace(" ","_",strtolower($itm["nombre"]));
        $local_titulo[$itm["id"]] = strtoupper($itm["nombre"]);
    }

    if($local_id !=""){
        $query = "
                SELECT
                     l.cc_id as cc_id, l.nombre as local_nombre, l.razon_social_id, rtb.*, nc.num_cuenta_contable, nc.subdiario
                FROM
                    tbl_repositorio_transacciones_bancarias rtb
                LEFT JOIN
                    tbl_caja c
                ON
                    rtb.caja_id = c.id
                LEFT JOIN
                    tbl_local_cajas lc
                ON
                    lc.id = c.local_caja_id
                LEFT JOIN
                    tbl_locales l
                ON
                    l.id = lc.local_id
                INNER JOIN  cont_num_cuenta nc ON rtb.cuenta_id = nc.id
                WHERE
                    rtb.tipo = 1 AND
                    rtb.fecha_operacion >='$fecha_inicio' AND
                    rtb.fecha_operacion < '$fecha_fin' 
                    $permiso_locales 
                ORDER BY l.cc_id, fecha_operacion;
            ";

        $result = $mysqli->query($query);
        $transacciones = []; //D
        while($row = $result->fetch_assoc()){
            $transacciones[$row["cc_id"]][] = $row;
            $total = $transacciones[$row["cc_id"]]["total"] ?? 0;
            $transacciones[$row["cc_id"]]["total"] = $total + $row["importe"];
        }

        $query = "
            SELECT rp.*,
                    l.id,
                    l.cc_id,
                    l.razon_social_id
            FROM tbl_repositorio_prestamos_boveda rp
            JOIN tbl_locales l ON LEFT(rp.anexo, 4) = l.cc_id
            WHERE rp.tipo_doc = 'PR'
            $permiso_locales 
            ORDER BY rp.anexo, rp.fecha_documento;
        ";

        $result = $mysqli->query($query);
        $prestamos_boveda = []; //H
        while($row = $result->fetch_assoc()){
            
            $razon_social_local= $row["razon_social_id"];

            // Condición para locales de la empresa IGH 
            if( $razon_social_local == $razon_social_igh){

                $fecha_documento= $row["fecha_documento"];
                $rango_fecha = getParameterGeneral('concar_boveda_fecha_inicio_igh');

                $timestamp_fecha_documento = strtotime($fecha_documento);
                $timestamp_rango_fecha = strtotime($rango_fecha);

                // Condición de rango de fechas para la empresa IGH
                if($timestamp_fecha_documento >= $timestamp_rango_fecha){
                    $row["used"] = 0.00;
                    $row["cc_id"] = substr($row["anexo"], 0, 4);
                    $prestamos_boveda[$row["cc_id"]][] = $row;
                    $total = $prestamos_boveda[$row["cc_id"]]["total"] ?? 0;
                    $prestamos_boveda[$row["cc_id"]]["total"] = $total + $row["saldo_moneda_nacional_debe"];

                }

            }else{
            
                $row["used"] = 0.00;
                $row["cc_id"] = substr($row["anexo"], 0, 4);
                $prestamos_boveda[$row["cc_id"]][] = $row;
                $total = $prestamos_boveda[$row["cc_id"]]["total"] ?? 0;
                $prestamos_boveda[$row["cc_id"]]["total"] = $total + $row["saldo_moneda_nacional_debe"];

            }
        }

        $table=array();
        $table["tbody"]=array();

        foreach ($transacciones as $key => $transacciones_group){
            if(!array_key_exists($key, $prestamos_boveda)) continue;
            if ($transacciones_group["total"] <= $prestamos_boveda[$key]["total"]){
                $first_transaccion = $transacciones_group[0];
                $extra_data = [
                    "fecha_fin_pretty" => $fecha_fin_pretty,
                    "nombre_glosa" => cortar_cadena('Dev.Boveda '.$first_transaccion["cc_id"].' '.$first_transaccion["local_nombre"], 40),
                    "glosa_detalle" => cortar_cadena('Dev.Boveda '.$first_transaccion["cc_id"].' '.$first_transaccion["local_nombre"], 30),
                    "subdiario" => $first_transaccion["subdiario"],
                    "tipo_cambio" => $tipo_cambio,
                    "nro_comprobante" => date("m", strtotime($first_transaccion["fecha_operacion"])).zerofill($caja_correlativo,4)
                ];
                $transacciones_total = $transacciones_group["total"];
                foreach ($transacciones_group as $transacciones){
                    if (!is_array($transacciones)) continue;

                    $table["tbody"][] = format_row($transacciones, 0, $extra_data, 'd');
                }

                foreach ($prestamos_boveda[$key] as $prestamos){
                    if (!is_array($prestamos)) continue;

                    if ($transacciones_total <= $prestamos["saldo_moneda_nacional_debe"]){
                        $table["tbody"][] = format_row($prestamos, $transacciones_total, $extra_data, 'h');
                        break;
                    } else {
                        $transacciones_total -= $prestamos["saldo_moneda_nacional_debe"];
                        $table["tbody"][] = format_row($prestamos, $prestamos["saldo_moneda_nacional_debe"], $extra_data, 'h');
                    }
                }
                $caja_correlativo++;
            }
        }

        date_default_timezone_set('America/Mexico_City');
        if (PHP_SAPI == 'cli') die('Este archivo solo se puede ver desde un navegador web');

        require_once '../phpexcel/classes/PHPExcel.php';
        $objPHPExcel = new PHPExcel();
        $objPHPExcel->getProperties()->setCreator("GestionApuestaTotal") // Nombre del autor
        ->setTitle("Reporte Concar Boveda") // Titulo
        ->setSubject("Reporte Excel Concar Boveda") //Asunto
        ->setDescription("Reporte Depositos Boveda") //Descripción
        ->setKeywords("depositos") //Etiquetas
        ->setCategory("Reporte excel"); //Categorias

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
            'Importe de IGV sin derecho credito fisca');
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
            ->setCellValue('AN1',  $titulosColumnas[38]);

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

        $objPHPExcel->getActiveSheet()->getStyle('B1:AN1')->applyFromArray($estiloTituloColumnas);
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(40);
        $objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:AN".($i-1));

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

        $local_titulo_parcial =($get_data['local_id']=='_all_')?'Todos':$local_titulo[$get_data['local_id']];
        $local_id_parcial =($get_data['local_id']=='_all_')?'Todos':$locales[$get_data['local_id']];

        $titulo_reporte_cajas = "REPORTE CONCAR BOVEDA".$local_titulo_parcial;
        $titulo_file_reporte_cajas = "Depositos_Caja_Concar_Boveda".$local_id_parcial."_".date("d-m-Y",strtotime($get_data["fecha_inicio_concar_boveda"]))."_al_".date("d-m-Y",strtotime($get_data["fecha_fin_concar_boveda"]))."_".date("Ymdhis");

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="'.$titulo_file_reporte_cajas.'.xls"');
        header('Cache-Control: max-age=0');

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $excel_path = '/var/www/html/export/files_export/'.$titulo_file_reporte_cajas.'.xls';
        //$excel_path = '../export/files_exported/'.$titulo_file_reporte_cajas.'.xls';
        $excel_path_download = '/export/files_export/'.$titulo_file_reporte_cajas.'.xls';
        $url = $titulo_file_reporte_cajas.'.xls';
        $objWriter->save($excel_path);

        $insert_cmd = "INSERT INTO tbl_exported_files (url,tipo,ext,size,fecha_registro,usuario_id)";
        $insert_cmd.= " VALUES ('".$url."','excel','xls','".filesize($excel_path)."','".date("Y-m-d h:i:s")."','".$login["id"]."')";
        $mysqli->query($insert_cmd);
        $exported_id = $mysqli->insert_id;

        echo json_encode(array(
            "path" => $excel_path_download,
            "url" => $titulo_file_reporte_cajas.'.xls',
            "tipo" => "excel",
            "ext" => "xls",
            "size" => filesize($excel_path),
            "fecha_registro" => date("d-m-Y h:i:s"),
            "sql" => $insert_cmd
        ));

        $mysqli->query("
				INSERT INTO tbl_concar_boveda_historico (
					local_id,
					exported_id,
					usuario_id,
					cambio,
					correlativo,
					fecha_operacion,
					fecha_inicio,
					fecha_fin
				) VALUES (
					".(($get_data["local_id"] != "_all_") ? $get_data["local_id"] : 'null').",
					".$exported_id.",
					".$login["id"].",
					".$tipo_cambio.",
					".(($get_data["correlativo_inicial"] != "") ? "'".$get_data["correlativo_inicial"]."'" : "null").",
					'".date('Y-m-d H:i:s')."',
					'".$get_data["fecha_inicio_concar_boveda"]."',
					'".$get_data["fecha_fin_concar_boveda"]."'
				)
			");

        exit;
    }
    else{
        print_r('No hay resultados para mostrar');
    }
}


function zerofill($valor, $longitud){
    $res = str_pad($valor, $longitud, '0', STR_PAD_LEFT);
    return $res;
}


function cortar_cadena($cadena, $longitud){
    if (strlen($cadena) > $longitud)
    {
        $devolver = mb_substr($cadena, 0, $longitud);
    }
    else{
        $devolver = $cadena;
    }
    return $devolver;
}

function format_row($row, $amount, $extra_data, $type): array
{
    $tr=array();
    $tr["sub_Diario"]=$extra_data["subdiario"]; //'2120';
    $tr["nro_Comprobante"]= $extra_data["nro_comprobante"]; // proviene de rtb.fecha_operacion, todo_ es del mismo periodo, de inicio a fin de mes
    $tr["fecha_Comprobante"] = $extra_data["fecha_fin_pretty"];
    $tr["codigo_Moneda"]='MN';
    $tr["glosa_Principal"]= $extra_data["nombre_glosa"];
    $tr["tipo_Cambio"]= $extra_data["tipo_cambio"];
    $tr["tipo_Conversion"]='V';
    $tr["flag_Conversion"]='S';
    $tr["fecha_Tipo_Cambio"]= $extra_data["fecha_fin_pretty"];
//    $tr["cuenta_Contable"]='!';
//    $tr["codigo_Anexo"] = '!';
    $tr["codigo_Centro_Costo"]='';
//    $tr["debe_Haber"]='!';
//    $tr["importe_Original"]= "!";
//    $tr["importe_Dolares"]= "!";
//    $tr["importe_Soles"]= "!";
//    $tr["tipo_Documento"]='!';
//    $tr["nro_Documento"]='!';
//    $tr["fecha_Documento"]='!';
//    $tr["fecha_Vencimiento"]= '!';
//    $tr["codigo_Area"]='!';
    $tr["glosa_Detalle"]=$extra_data["glosa_detalle"];
//    $tr["codigo_Anexo_Auxiliar"] = '!';
//    $tr["medio_Pago"]='!';
//    $tr["tipo_Documento_Referencia"]='!';
//    $tr["numero_Documento_Referencia"]='!';
//    $tr["fecha_Documento_Referencia"]= '!';
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

    if ($type === "d"){
        if((int)$row["banco_id"]==12){ // Banco BBVA
                $tr["nro_Documento"] = $row["numero_movimiento"];
            }
        else if((int)$row["banco_id"]==15  || $row["numero_movimiento"]==""){ // Banco Caja Piura
            $tr["nro_Documento"] = date("dmy", strtotime($row["fecha_operacion"]));
        }
        $tr["cuenta_Contable"] = $row['num_cuenta_contable'];
        $tr["codigo_Anexo"] = $tr["cuenta_Contable"];

        $tr["debe_Haber"] = "D";
        $tr["importe_Original"] = (float) $row["importe"];
        $tr["importe_Dolares"] = (float) $row["importe"]/(float)$extra_data["tipo_cambio"];
        $tr["importe_Soles"] = (float) $row["importe"];
        $tr["tipo_Documento"] = 'EN';
        $tr["fecha_Documento"]= date("d/m/Y",strtotime($row["fecha_operacion"])); //date("d/m/Y",strtotime($value["fecha_Excel"]))
        $tr["fecha_Vencimiento"]= date("d/m/Y",strtotime($row["fecha_operacion"])); //date("d/m/Y",strtotime($value["fecha_Excel"]))
        $tr["codigo_Area"]='101'; // ES 101 HARCODED
        $tr["codigo_Anexo_Auxiliar"] = '';
        $tr["medio_Pago"]='001';
        $tr["tipo_Documento_Referencia"]='';
        $tr["numero_Documento_Referencia"]='';
        $tr["fecha_Documento_Referencia"]= '';
    } elseif ($type === "h"){
        $tr["cuenta_Contable"] = '101121';
        $tr["codigo_Anexo"] = $row["cc_id"] . '-FONDO BOVEDA';
        $tr["debe_Haber"] = "H";
        $tr["importe_Original"] = number_format($amount, 2, ".", "");
        $tr["importe_Dolares"] = number_format($amount/(float)$extra_data["tipo_cambio"], 2, ".", "");
        $tr["importe_Soles"] = number_format($amount, 2, ".", "");
        $tr["tipo_Documento"] = 'PR';
        $tr["nro_Documento"] = $row["numero_documento"]; // FechaExcel
        $tr["fecha_Documento"] = date("d/m/Y",strtotime($row["fecha_documento"])); //date("d/m/Y",strtotime($value["fecha_Excel"]))
        $tr["fecha_Vencimiento"]= date("d/m/Y",strtotime($row["fecha_documento"])); //date("d/m/Y",strtotime($value["fecha_Excel"]))
        $tr["codigo_Area"]='343';
        $tr["codigo_Anexo_Auxiliar"] = 'A0003';
        $tr["medio_Pago"]='';
        $tr["tipo_Documento_Referencia"]='PR';
        $tr["numero_Documento_Referencia"]= $row["numero_documento"]; // FechaExcel desde los más antiguos
        $tr["fecha_Documento_Referencia"]= date("d/m/Y",strtotime($row["fecha_documento"])); //date("d/m/Y",strtotime($value["fecha_Excel"]))
    }

    return $tr;
}

?>
