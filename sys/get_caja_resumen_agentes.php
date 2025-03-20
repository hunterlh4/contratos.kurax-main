<?php
include("db_connect.php");
include("sys_login.php");

function excel_date_to_php($cell)
{
    try {
        $excelDate = $cell->getValue();
        if (PHPExcel_Shared_Date::isDateTime($cell)) {
            //$excelDate = date('Y-m-d', strtotime($excelDate ,"d/m/Y" ));
            $excelDate = str_replace('/', '-', $excelDate);
            $excelDate =  date('Y-m-d', strtotime($excelDate));

        } elseif (DateTime::createFromFormat('d/m/Y H:i:s', $excelDate) !== false) {
        	$timestamp = DateTime::createFromFormat('d/m/Y H:i:s', $excelDate)->getTimestamp();
            $excelDate =   date('Y-m-d H:i:s', $timestamp); //date_format($date, 'd/m/Y H:i:s');
        } 
        elseif (DateTime::createFromFormat('d/m/Y', $excelDate) !== false) {
        	$timestamp = DateTime::createFromFormat('d/m/Y', $excelDate)->getTimestamp();
            $excelDate =   date('Y-m-d', $timestamp); //date_format($date, 'd/m/Y H:i:s');
        } 
        elseif (is_numeric($excelDate)) {
            $unixDate = PHPExcel_Shared_Date::ExcelToPHP($excelDate);

            $excelDate = gmdate("Y-m-d H:i:s", $unixDate);
        } 
        elseif (DateTime::createFromFormat('d-m-Y', $excelDate) !== false) {
        	$timestamp = DateTime::createFromFormat('d-m-Y', $excelDate)->getTimestamp();
            $excelDate =   date('Y-m-d', $timestamp); //date_format($date, 'd/m/Y H:i:s');
        } 
        else {
            $excelDate =   null;
        }
        return  $excelDate;
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}
function excel_time_to_php($cell)
{
    try {
        $excelDate = $cell->getValue();
        if (PHPExcel_Shared_Date::isDateTime($cell)) {
            $excelDate = PHPExcel_Style_NumberFormat::toFormattedString($excelDate, 'YYYY-MM-DD');
        } elseif (DateTime::createFromFormat('H:i:s', $excelDate) !== false) {
        	$timestamp = DateTime::createFromFormat('H:i:s', $excelDate)->getTimestamp();
            $excelDate =   date('H:i:s', $timestamp); //date_format($date, 'd/m/Y H:i:s');
        } 

        elseif (is_numeric($excelDate)) {
            $unixDate = PHPExcel_Shared_Date::ExcelToPHP($excelDate);
            $excelDate = gmdate("H:i:s", $unixDate);
        } 
        else {
            $excelDate =   null;
        }
        return  $excelDate;
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->getMessage(), null);
    }
}

if(isset($_POST["opt"]) && $_POST["opt"]=="lista_pagos_periodo"){
		extract($_POST);//$periodo_liquidacion_id, $local_id
		$lista_arr = array();
		$pagos_query = "
			SELECT
			pd.id ,
			p.id AS pago_id,
			p.pago_tipo_id,
			pt.nombre AS pago_tipo_nombre,
			pd.monto AS repartir,
			pd.descripcion AS 'descripcion',
			pd.nro_operacion,
			(SELECT sum(p2.abono) FROM tbl_pagos p2 
				WHERE p2.estado = 1
				AND p2.periodo_liquidacion_id = p.periodo_liquidacion_id
				AND p2.deuda_tipo_id IS NOT  null
				AND p2.pago_tipo_id = 5 
				AND p2.id = p.id
			 ) AS abono_saldo,
			( SELECT sum(p2.abono) FROM tbl_pagos p2 
				WHERE p2.estado = 1 
				AND p2.periodo_liquidacion_id = p.periodo_liquidacion_id
				AND p2.deuda_tipo_id IS NOT null
				AND p2.pago_detalle_id = p.pago_detalle_id
              )  AS abono ,
			(pd.monto - ( SELECT sum(p2.abono) FROM tbl_pagos p2 WHERE p2.estado = 1 AND p2.deuda_tipo_id IS NOT null
				AND p2.periodo_liquidacion_id = p.periodo_liquidacion_id
				AND p2.pago_detalle_id = p.pago_detalle_id
              ) ) AS saldo_favor,
			count(p.id) AS pagos_cantidad,
			p.fecha_ingreso
			FROM  tbl_pagos p
			LEFT JOIN tbl_pagos_detalle pd ON pd.id= p.pago_detalle_id
			LEFT JOIN tbl_pagos_tipos pt ON pt.id = p.pago_tipo_id
			WHERE p.estado = 1
			AND p.periodo_liquidacion_id = $periodo_liquidacion_id
			AND p.local_id = $local_id
	        AND p.pago_tipo_id != 5
			GROUP BY pd.id
			";
			//echo "<pre>";print_r($pagos_query);echo "<pre>";die();
		$pagos_query = $mysqli->query($pagos_query);
		if($mysqli->error){
			print_r($mysqli->error);
			exit();
		}
		while ($l = $pagos_query->fetch_assoc()) {
			$lista_arr[]=$l;
		}

		$pagos_saldos_query = "
            SELECT
            '' as id
            ,p.id AS pago_id
            ,p.pago_tipo_id
            ,pt.nombre AS pago_tipo_nombre
            ,p.abono AS repartir
            ,p.abono AS abono_saldo
            ,p.abono AS abono
            ,'' AS nro_operacion
            ,p.fecha_ingreso
            FROM tbl_pagos p
			LEFT JOIN tbl_pagos_tipos pt ON pt.id = p.pago_tipo_id
            WHERE p.pago_tipo_id = 5
            AND p.periodo_liquidacion_id = $periodo_liquidacion_id
            AND p.local_id = $local_id
            AND p.estado = 1
            AND p.deuda_tipo_id is not null
			";
		$pagos_saldos_query = $mysqli->query($pagos_saldos_query);
		if($mysqli->error){
			print_r($mysqli->error);
			exit();
		}
		while ($l = $pagos_saldos_query->fetch_assoc()) {
			$lista_arr[]=$l;
		}

		$return["lista"]=$lista_arr;
		print_r(json_encode($return));
	}

if(isset($_POST["opt"]) && $_POST["opt"] == "sec_caja_resumen_agentes_get_pagos"){
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value
	$searchValue = $mysqli->real_escape_string($searchValue);

	$local_id = $_POST["sec_caja_resumen_agentes_local"];
	$local_query = $local_id == "_all_" ? "" : " AND l.id = '$local_id' ";

	$fecha_inicio = $_POST['sec_caja_resumen_agentes_fecha_inicio'];
	$fecha_fin = date("Y-m-d",strtotime($_POST['sec_caja_resumen_agentes_fecha_fin']."+ 1 days"));

	$periodo = $_POST['periodo'];
	$periodo_inicio = $_POST['sec_caja_resumen_agentes_periodo_inicio'];
	$periodo_fin = $_POST['sec_caja_resumen_agentes_periodo_fin'];

	if(intval($periodo_inicio) > intval($periodo_fin)){
		$return["error"] = "¡Atención!";
		$return["error_msg"] = "El periodo inicio no puede pasar el periodo fin.";
		die(json_encode($return));
	}

	if($periodo == 'false'){
		$fecha_query  =  " AND pd.fecha_voucher >= '" . $fecha_inicio . "'";
		$fecha_query .=  " AND pd.fecha_voucher < '" . $fecha_fin . "'";
	}else{
		$fecha_query  =  " AND p.periodo_liquidacion_id >= '" . $periodo_inicio . "'";
		$fecha_query .=  " AND p.periodo_liquidacion_id <= '" . $periodo_fin . "'";
	}

	$searchQuery = " ";
	if($searchValue != ''){
	   $searchQuery = " AND (pt.nombre like '%".$searchValue."%' or 
	        l.nombre like '%".$searchValue."%' or 
	        pd.descripcion like '%".$searchValue."%' or 
	        pd.nro_operacion like '%".$searchValue."%' or 
	        p.fecha_ingreso like'%".$searchValue."%' ) ";
	}

	$limit = " LIMIT ".$row.",".$rowperpage;
	if( $rowperpage == -1 ){
		$limit = "";
	}

	$alias = [
		'LOCAL'			=> "l.nombre" ,
		'PAGO TIPO'		=> "pt.nombre"  ,
		'DESCRIPCIÓN' 	=> "pd.descripcion" ,
		'NRO OPERACIÓN'	=> "pd.nro_operacion",
		'MONTO'			=> "pd.monto" ,
		'FECHA' 		=> "p.fecha_ingreso",
		'PERIODO INICIO'=> "p.periodo_inicio",
		'PERIODO FIN'	=> "p.periodo_fin",
		'id_transaccion'=> 'id_transaccion'
	];

	// se obtiene los pagos y muestra si están conciliados o no
	// se agrega validación de importe para casos de id_pago_detalle repetido
	$query_all = "SELECT
					pd.id ,
					p.id AS pago_id,
					l.id AS local_id,
					p.pago_tipo_id,
					l.nombre AS 'LOCAL',
					p.periodo_liquidacion_id,
					p.periodo_inicio AS 'PERIODO INICIO',
                    p.periodo_fin AS 'PERIODO FIN',
					pt.nombre AS 'PAGO TIPO',
					pd.descripcion AS 'DESCRIPCIÓN',
					pd.nro_operacion AS 'NRO OPERACIÓN',
					pd.monto AS 'MONTO',		
					-- p.fecha_ingreso AS 'FECHA',
					IF (pd.fecha_voucher IS NULL ,p.fecha_ingreso , pd.fecha_voucher) AS 'FECHA',
					(SELECT id FROM tbl_transacciones_agentes WHERE id_pago_detalle = pd.id AND importe = pd.monto)  AS id_transaccion
				FROM  tbl_pagos p
				LEFT JOIN tbl_pagos_detalle pd ON pd.id =  p.pago_detalle_id
				LEFT JOIN tbl_pagos_tipos pt ON pt.id = p.pago_tipo_id
			 	LEFT JOIN tbl_locales l ON l.id = p.local_id
				WHERE p.estado = 1						
				AND p.pago_tipo_id != 5
				$searchQuery
				$fecha_query
				$local_query
				GROUP BY pd.id";
	$sel = $mysqli->query("SELECT count(*) AS allcount FROM
							( $query_all ) as  A ");
	$records = $sel->fetch_assoc();
	$totalRecords = $records['allcount'];

	$sel = $mysqli->query("SELECT count(*) AS allcount FROM (" . $query_all . ") AS subquery");	
	$records = $sel->fetch_assoc();
	$totalRecordwithFilter = $records['allcount'];
	
	$query_filtered = $query_all .
				" ORDER BY " .$alias[$columnName] . " " . $columnSortOrder
				.$limit;
	$query_records = $mysqli->query($query_filtered);

	$query_trans ="SELECT id,fecha_operacion , nro_doc,concepto ,importe,created_at,id_pago_detalle
	FROM tbl_transacciones_agentes WHERE id_pago_detalle is not NULL  ";
	$transactions = $mysqli->query($query_trans);
	$transacciones_agente = array();
	while ($row = $transactions->fetch_assoc()) {
	   $transacciones_agente[$row["id_pago_detalle"]] = $row;
	}

	//col 7 filter conc
	$filter_no_conciliados = false;
	if($_POST["columns"][7]["search"]["value"] != "" && $_POST["columns"][7]["search"]["value"] != "null"){
		$filter_no_conciliados = true;
	}
	$data = array();
	while ($row = $query_records->fetch_assoc()) {
		$row["id_transaccion"] = null;
		if(isset($transacciones_agente[$row["id"]]))
		{
			$row["id_transaccion"] = $transacciones_agente[$row["id"]]["id"];
			$row["concepto"] = $transacciones_agente[$row["id"]]["concepto"];
			$row["nro_doc"] = $transacciones_agente[$row["id"]]["nro_doc"];
			$row["fecha_tra"] = $transacciones_agente[$row["id"]]["fecha_operacion"];
			$row["concepto"] = $transacciones_agente[$row["id"]]["concepto"];
			$row["importe"] = $transacciones_agente[$row["id"]]["importe"];	
		}
		if($filter_no_conciliados)
		{
			if($row["id_transaccion"] == null)
			{
				$data[] = $row;
			}
		}
		else
		{
			$data[] = $row;
		}
	}

	$response = array(
	  "draw" => $draw,
	  "iTotalRecords" => $totalRecords,
	  "iTotalDisplayRecords" => $totalRecordwithFilter,
	  "aaData" => $data
	);
	echo json_encode($response);
	return;
}

if( isset($_POST["opt"]) && $_POST["opt"] == "sec_caja_resumen_agentes_archivo" ){
	$ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
	if($ext != "xls" && $ext != "xlsx" )
	{
		echo json_encode( ["error" => true , "msg" => "Extensión de archivo incorrecta,  xls"] );
		return;
	}
	if($ext == "xls"  || $ext == "xlsx" ){
		$errors = [];
		$return = array();
		$return["memory_init"] = memory_get_usage();
		$return["time_init"] = microtime(true);
		include("global_config.php");
		include("db_connect.php");
		include("sys_login.php");
		include("/var/www/html/sys/helpers.php");
		require_once '../phpexcel/classes/PHPExcel.php';
		$tmpfname = $_FILES['file']['tmp_name'];
		$excelReader = PHPExcel_IOFactory::createReaderForFile($tmpfname);
		//$excelReader->setInputEncoding('utf-8');
		libxml_use_internal_errors(TRUE);
		$excelObj = $excelReader->load($tmpfname);

		$worksheet = $excelObj->getSheet(0);
		//$excelReader->setInputEncoding('ISO-8859-1');

		$lastRow = $worksheet->getHighestRow();

		$titulo_excel = $worksheet->getCell('A2')->getValue();
		$tipo = null; //1 caja piura , 2 bbva
		if($titulo_excel == "Movimientos")/*EXCEL CAJA PIURA*/
		{
			$tipo = 1;
			$firstRow = 12;
			$columns_format = [
					["value" => "Fecha", 		"value2" =>""			,"column" => "A" ] ,
					["value" => "Hora", 		"value2" => ""			,"column" => "B" ] ,
					["value" => "Descripción", 	"value2" =>"Descripcin"	,"column" => "C" ] ,
					["value" => "Importe", 		"value2" =>""			,"column" => "D" ] ,
			];
			foreach ($columns_format as $key => $value) {
				$column_letter = $value["column"];/*A B C -...*/
				$column_xls_value = $worksheet->getCell($column_letter.$firstRow)->getValue();
				if( strtoupper($column_xls_value) != strtoupper($value["value"]) &&  strtoupper($column_xls_value) != strtoupper($value["value2"]) )
				{
					$errors[] = "Columna <b>".$column_letter.$firstRow."</b> incorrecta  -  Se esperaba <b>" .$value["value"] ."</b> - " .$column_xls_value;
				}
			}

			$nro_registros = $lastRow - $firstRow - 2;
			if($nro_registros <= 0 )
			{
				$errors[] = "<b>No hay registros</b>";
			}

			if( count($errors) > 0 )
			{
				echo json_encode(
					["error" => true , 
						"msg" => "Error en archivo xls",
						"msg_error" => implode("<br>", $errors)
					] 
				);
				return;
			}

			$extract = array();
			$firstRow_datos = $firstRow + 1;
			for ($row = $firstRow_datos; $row <= $lastRow ; $row ++ ) {
				if($worksheet->getCell('A'.$row)->getValue() != "" ){
					if($worksheet->getCell('D'.$row)->getValue() <= 0) 
					{
						continue;
					}
					if($worksheet->getCell('C'.$row)->getValue() != "DEPOSITO SIN TARJETA"
					&& $worksheet->getCell('C'.$row)->getValue() != "ABONO SIN TARJETA") 
					{
						continue;
					}
					$id_transaccion = $worksheet->getCell('D'.$row)->getValue();
					$fecha = excel_date_to_php($worksheet->getCell('A'.$row));
					$hora = excel_time_to_php($worksheet->getCell('B'.$row));
					$importe_temp = trim( $worksheet->getCell('D'.$row)->getValue() );
					$importe = number_format( str_replace( ',' , '' , $importe_temp) , 3, '.' , '' ) ;

					$uid = md5($id_transaccion.$fecha.$hora.$worksheet->getCell('C'.$row)->getValue().$importe);
					$extract[$uid] = [
						"at_unique_id" => $uid,
						"fecha_operacion" => $fecha,
						"hora" => $hora,
						"concepto" => $worksheet->getCell('C'.$row)->getValue(),
						"importe" => $importe,
						"tipo" => $tipo,
						"created_at" => date("Y-m-d H:i:s"),
					];
				}
			}
		}
		else
		{//bbva
			$tipo = 2;
			$firstRow = 11;
			$columns_format = [
					["value" => "F. Operación", "value2" =>"F. Operacin"	,"column" => "A" ] ,
					["value" => "F. Valor", 	"value2" =>""				,"column" => "B" ] ,
					["value" => "Código", 		"value2" =>"Cdigo"			,"column" => "C" ] ,
					["value" => "Nº. Doc.", 	"value2" =>"N. Doc."		,"column" => "D" ] ,
					["value" => "Concepto", 	"value2" =>""				,"column" => "E" ] ,
					["value" => "Importe", 		"value2" =>""				,"column" => "F" ] ,
					["value" => "Oficina", 		"value2" =>""				,"column" => "G" ] ,
			];
			foreach ($columns_format as $key => $value) {
				$column_letter = $value["column"];/*A B C -...*/
				$column_xls_value = $worksheet->getCell($column_letter.$firstRow)->getValue();
				if( strtoupper($column_xls_value) != strtoupper($value["value"]) &&  strtoupper($column_xls_value) != strtoupper($value["value2"]) )
				{
					$errors[] = "Columna <b>".$column_letter.$firstRow."</b> incorrecta  -  Se esperaba <b>" .$value["value"] ."</b> - " .$column_xls_value;
				}
			}

			$nro_registros = $lastRow - $firstRow - 2;
			if($nro_registros <= 0 )
			{
				$errors[] = "<b>No hay registros</b>";
			}

			if( count($errors) > 0 )
			{
				echo json_encode(
					["error" => true , 
						"msg" => "Error en archivo xls",
						"msg_error" => implode("<br>", $errors)
					] 
				);
				return;
			}

			$extract = array();
			$firstRow_datos = $firstRow + 2;
			for ($row = $firstRow_datos; $row <= $lastRow - 1; $row ++ ) {
				if($worksheet->getCell('A'.$row)->getValue() != "" ){
					if( substr( $worksheet->getCell('E'.$row)->getValue() , 0, 12 ) == "Saldo Final:" ){
						continue;
					}
					if($worksheet->getCell('F'.$row)->getValue() <= 0) 
					{
						continue;
					}
					$id_transaccion = $worksheet->getCell('D'.$row)->getValue();


					$fecha_operacion = excel_date_to_php($worksheet->getCell('A'.$row));
					$fecha_valor = excel_date_to_php($worksheet->getCell('B'.$row));

					/*$date_temp = PHPExcel_Shared_Date::ExcelToPHP($worksheet->getCell('A'.$row)->getValue());
					$fecha_operacion = gmdate("Y-m-d", $date_temp);
					$date_temp = PHPExcel_Shared_Date::ExcelToPHP($worksheet->getCell('B'.$row)->getValue());
					$fecha_valor = gmdate("Y-m-d", $date_temp);*/

					$importe_temp = trim( $worksheet->getCell('F'.$row)->getValue() );
					$importe = number_format( str_replace( ',' , '' , $importe_temp) , 3, '.' , '' ) ;

					$uid = md5($id_transaccion.$fecha_operacion);
					$extract[$uid] = [
						"at_unique_id" => $uid,
						"fecha_operacion" => $fecha_operacion,
						"fecha_valor" => $fecha_valor,
						"codigo" => $worksheet->getCell('C'.$row)->getValue(),
						"nro_doc" => $worksheet->getCell('D'.$row)->getValue(),
						"concepto" => $worksheet->getCell('E'.$row)->getValue(),
						"importe" => $importe,
						"tipo" => $tipo,
						"oficina" => $worksheet->getCell('G'.$row)->getValue(),
						"created_at" => date("Y-m-d H:i:s"),
					];
				}
			}					
		}

		if(!empty($extract)){
				feed_database($extract, 'tbl_transacciones_agentes', 'at_unique_id', false);
			}
		echo json_encode(
				[   "error" => false , 
					"msg" => "Excel Importado"					
				] 
		);
	}
}

if( isset($_POST["opt"]) && $_POST["opt"] == "sec_caja_resumen_agentes_Prevalidacion" ){
	$get_data = $_POST["sec_caja_resumen_agentes_Prevalidacion"];
	$local_id = $get_data["local_id"];
	$fecha_inicio = $get_data["fecha_inicio"];
	$fecha_inicio_pretty = date("d-m-Y",strtotime($get_data["fecha_inicio"]));
	$fecha_fin = date("Y-m-d",strtotime($get_data["fecha_fin"]." +1 day"));
	$fecha_fin_pretty = date("d-m-Y",strtotime($get_data["fecha_fin"]));
    $caja_arr = array();
	$command = "
			SELECT
			pd.id ,
			p.id AS pago_id,
			l.id AS local_id,
			p.pago_tipo_id,
			l.nombre AS 'LOCAL',
			pt.nombre AS 'PAGO TIPO',
			pd.descripcion AS 'DESCRIPCIÓN',
			pd.nro_operacion AS 'NRO OPERACIÓN',
			pd.monto AS 'MONTO',
			-- p.fecha_ingreso AS 'FECHA',
			IF (pd.fecha_voucher IS NULL ,p.fecha_ingreso , pd.fecha_voucher) AS 'FECHA',
			IF (pd.fecha_voucher IS NULL ,MONTH(p.fecha_ingreso) , MONTH(pd.fecha_voucher)) AS mes
		FROM  tbl_pagos p
		LEFT JOIN tbl_pagos_detalle pd ON pd.id =  p.pago_detalle_id
		LEFT JOIN tbl_pagos_tipos pt ON pt.id = p.pago_tipo_id
		LEFT JOIN tbl_locales l ON l.id = p.local_id
		WHERE p.estado = 1
		AND p.pago_tipo_id != 5
		AND date(IF (pd.fecha_voucher IS NULL ,p.fecha_ingreso , pd.fecha_voucher)) >= '$fecha_inicio' 
		AND date(IF (pd.fecha_voucher IS NULL ,p.fecha_ingreso , pd.fecha_voucher)) <= '$fecha_fin'
		GROUP BY pd.id";

	$mysqli->query("SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED");
	$pagos_query = $mysqli->query($command);

	if($mysqli->error){
		print_r($mysqli->error);
		exit();
	}
	$mysqli->query("COMMIT");
	$transactions = array();
	while($c = $pagos_query->fetch_assoc()){
		$auto_conc = array();
		$mes_p = $c["mes"];
		// busca transacciones que no han sido conciliadas, se valida por nro_doc y verifica que el id_pago_detalle no existe en un transaccion anterior
        $sql_ = "
	        SELECT * FROM tbl_transacciones_agentes
				WHERE importe > 0
				AND nro_doc = '" . $c["NRO OPERACIÓN"] . "'
				AND tipo = 2
				AND MONTH(fecha_operacion) = $mes_p
				AND id_pago_detalle IS NULL
				AND (select count(id_pago_detalle) from tbl_transacciones_agentes where id_pago_detalle = '" . $c["id"] . "' ) = 0
				";			
		$result_ = $mysqli->query($sql_);
        if($result_){
            if($result_->num_rows > 0){
                while($row_transaccion = $result_->fetch_assoc()){
                	$auto_conc["transacciones_agente"] = array();
                    $auto_conc["transacciones_agente"][] = $row_transaccion;                    
                }
            }
        }

        $sql_ = "
	        SELECT * FROM tbl_transacciones_agentes
				WHERE importe > 0
				AND fecha_operacion =  '" . $c["FECHA"] . "'
				AND importe = '" .$c["MONTO"] . "'
				AND tipo = 1
				AND id_pago_detalle IS NULL";
		$result_ = $mysqli->query($sql_);
        if($result_){
            if($result_->num_rows > 0){
                while($row_transaccion = $result_->fetch_assoc()){
                	$auto_conc["transacciones_agente"] = array();
                    $auto_conc["transacciones_agente"][] = $row_transaccion;                    
                }
            }
        }

		if(!empty($auto_conc)) $transactions[] = array_merge($c, $auto_conc);
	}
	echo json_encode($transactions); die;
}
if( isset($_POST["opt"]) && $_POST["opt"] == "sec_caja_resumen_agentes_conciliar_automatico" ){
	$get_data = $_POST["sec_caja_resumen_agentes_conciliar_automatico"];
	$mysqli->query("START TRANSACTION");
	foreach ($get_data as $data) {
		$update_estado_data = "
		UPDATE tbl_transacciones_agentes 
		SET id_pago_detalle = '".$data["id_pago_detalle"]."'
			WHERE id =  '".$data["id_transaccion"]."'";

		$mysqli->query($update_estado_data);
		/*$update_estado_data = "UPDATE tbl_caja_depositos SET validar_registro = 1 WHERE caja_id='".$data["caja_id"]."'";
		$mysqli->query($update_estado_data);*/
	}
	$mysqli->query("COMMIT");
}

if (isset($_POST["opt"]) && $_POST["opt"] == "sec_excel_depositosugerencias") {
	$result_final = array();
	$fecha = date("Y-m-d", strtotime($_POST['fecha']));
	$mes_p = date("m", strtotime($_POST['fecha']));

	$sql_ = "SELECT id
	,fecha_operacion
	,nro_doc
	,importe,concepto
	FROM tbl_transacciones_agentes 
	WHERE importe > 0 
	AND TRIM(LEADING '0' FROM nro_doc) = TRIM(LEADING '0' FROM '".$_POST['nro_doc']."')	
	AND MONTH(fecha_operacion) = $mes_p
	AND tipo = 2
	AND id_pago_detalle is NULL
	ORDER BY importe ASC";
	$result = $mysqli->query($sql_);
	$i = 0;
	while($row_2 = $result->fetch_assoc()) {
		$result_final[$i] = $row_2;
		$i++;
	}

	$sql_ = "
	SELECT id
		,fecha_operacion
		,nro_doc
		,importe,concepto
	FROM tbl_transacciones_agentes
	WHERE importe > 0
	AND fecha_operacion =  '" . $fecha . "'
	AND importe = '" .$_POST["monto"] . "'
	AND tipo = 1
	AND id_pago_detalle IS NULL";
	$result_ = $mysqli->query($sql_);
	while($row_2 = $result_->fetch_assoc()) {
		$result_final[$i] = $row_2;
		$i++;
	}
	echo json_encode($result_final);
}
if (isset($_POST["opt"]) && $_POST["opt"] == "sec_relacionar") {
	$update_estado_data = "UPDATE tbl_transacciones_agentes SET id_pago_detalle = '".$_POST['id_pago_detalle']."' WHERE id =  '".$_POST['id_transaccion']."'";
	$mysqli->query($update_estado_data);
	if($mysqli->error){
		print_r($mysqli->error);
		echo "\n";
		echo $update_estado_data;
		exit();
	}
	echo 1;
}

if (isset($_POST["opt"]) && $_POST["opt"] == "sec_quitarDeposito") {
	$update_estado_data = "UPDATE tbl_transacciones_agentes SET id_pago_detalle = null WHERE id =  '".$_POST['id_transaccion']."'";
	$mysqli->query($update_estado_data);
	echo 1;
}

if(isset($_POST["delete_concar_agentes_history"])){
	$get_data = $_POST["delete_concar_agentes_history"];
	$exported = [];
	$result = $mysqli->query("
		SELECT
			f.id,
			f.url
		FROM tbl_concar_agentes_historico ch
		INNER JOIN tbl_exported_files f ON f.id = ch.exported_id
		WHERE ch.id =".$get_data["id"]
	);
	while($r = $result->fetch_assoc()) $exported = $r;

	unlink("/var/www/html/export/files_exported/".$exported["url"]);
	$mysqli->query("DELETE FROM tbl_exported_files WHERE id =".$exported["id"]);
	$mysqli->query("DELETE FROM tbl_concar_agentes_historico WHERE id =".$get_data["id"]);
}

if(isset($_POST["opt"]) && $_POST["opt"] == "sec_caja_resumen_agentes_get_concar" ){
	$where = "";
	$table = [];
	$draw = $_POST['draw'];
	$row = $_POST['start'];
	$rowperpage = $_POST['length']; // Rows display per page
	$columnIndex = $_POST['order'][0]['column']; // Column index
	$columnName = $_POST['columns'][$columnIndex]['data']; // Column name
	
	$columnSortOrder = $_POST['order'][0]['dir']; // asc or desc
	$searchValue = $_POST['search']['value']; // Search value
	$searchValue = $mysqli->real_escape_string($searchValue);

	/*$local_id = $_POST["sec_caja_resumen_agentes_local"];
	$local_query = $local_id == "_all_" ? "" : " AND l.id = '$local_id' ";*/

	/*$fecha_inicio = $_POST['sec_caja_resumen_agentes_fecha_inicio'];
	$fecha_fin = date("Y-m-d",strtotime($_POST['sec_caja_resumen_agentes_fecha_fin']."+ 1 days"));*/

   /* $fecha_query  =  " AND p.fecha_ingreso >= '" . $fecha_inicio . "'";
    $fecha_query .=  " AND p.fecha_ingreso < '" . $fecha_fin . "'";*/
    ## Search
	$searchQuery = " ";
	if($searchValue != ''){
	   $searchQuery = " AND (l.nombre like '%".$searchValue."%' or 
	        pd.descripcion like '%".$searchValue."%' or 
	        p.fecha_ingreso like'%".$searchValue."%' ) ";
	}
	$limit = " LIMIT ".$row.",".$rowperpage;
	if( $rowperpage == -1 ){
		$limit = "";
	}
	$query_all = "
		SELECT
			ch.id,
			ch.local_id,
			l.nombre,
			ch.cambio,
			ch.correlativo,
			u.usuario,
			f.url,
			ch.fecha_operacion,
			ch.fecha_inicio,
			ch.fecha_fin
		FROM tbl_concar_agentes_historico ch
		INNER JOIN tbl_exported_files f ON f.id = ch.exported_id
		LEFT JOIN tbl_locales l ON l.id = ch.local_id
		LEFT JOIN tbl_usuarios u ON u.id = ch.usuario_id
        $searchQuery
		";
	$sel = $mysqli->query("SELECT count(*) AS allcount FROM
							( $query_all ) as  A ");
	$records = $sel->fetch_assoc();
	$totalRecords = $records['allcount'];

	$sel = $mysqli->query("SELECT count(*) AS allcount FROM (".$query_all.") AS subquery");	
	$records = $sel->fetch_assoc();
	$totalRecordwithFilter = $records['allcount'];
	
	$query_filtered = $query_all .
				" ORDER BY " .$columnName . " " . $columnSortOrder . $limit;
	$query_records = $mysqli->query($query_filtered);
	$data = array();
	while ($row = $query_records->fetch_assoc()) {
	   $data[] = $row;
	}
	$response = array(
	  "draw" => $draw,
	  "iTotalRecords" => $totalRecords,
	  "iTotalDisplayRecords" => $totalRecordwithFilter,
	  "aaData" => $data
	);
	echo json_encode($response);
	return;
}
?>