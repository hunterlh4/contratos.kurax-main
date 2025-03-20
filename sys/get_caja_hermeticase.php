<?php
include_once("db_connect.php");
include_once("sys_login.php");
include_once("globalFunctions/generalInfo/parameterGeneral.php");

function excel_date_to_php($cell)
{
    try {
        $excelDate = $cell->getValue();
        if (PHPExcel_Shared_Date::isDateTime($cell)) {
            $excelDate = PHPExcel_Style_NumberFormat::toFormattedString($excelDate, 'YYYY-MM-DD');
        } elseif (DateTime::createFromFormat('d/m/Y H:i:s', $excelDate) !== false) {
        	$timestamp = DateTime::createFromFormat('d/m/Y H:i:s', $excelDate)->getTimestamp();
            $excelDate =   date('Y-m-d H:i:s', $timestamp); //date_format($date, 'd/m/Y H:i:s');
        } elseif (is_numeric($excelDate)) {
            //$unixDate = ($excelDate - 25569) * 86400;
            $unixDate = PHPExcel_Shared_Date::ExcelToPHP($excelDate);
            $excelDate = gmdate("d/m/Y H:i:s", $unixDate);
        } else {
            $excelDate = null;
        }
        return  $excelDate;
    } catch (Exception $ex) {
        set_status_code_response(500, $ex->message, null);
    }
}

function set_status_code_response($code, $message, $title)
{
    if (empty($code)) {
        $code = 200;
    }
    $http = array(
        100 => 'HTTP/1.1 100 Continue',
        101 => 'HTTP/1.1 101 Switching Protocols',
        200 => 'HTTP/1.1 200 OK',
        201 => 'HTTP/1.1 201 Created',
        202 => 'HTTP/1.1 202 Accepted',
        203 => 'HTTP/1.1 203 Non-Authoritative Information',
        204 => 'HTTP/1.1 204 No Content',
        205 => 'HTTP/1.1 205 Reset Content',
        206 => 'HTTP/1.1 206 Partial Content',
        300 => 'HTTP/1.1 300 Multiple Choices',
        301 => 'HTTP/1.1 301 Moved Permanently',
        302 => 'HTTP/1.1 302 Found',
        303 => 'HTTP/1.1 303 See Other',
        304 => 'HTTP/1.1 304 Not Modified',
        305 => 'HTTP/1.1 305 Use Proxy',
        307 => 'HTTP/1.1 307 Temporary Redirect',
        400 => 'HTTP/1.1 400 Formato de Archivo Incorrecto',
        401 => 'HTTP/1.1 401 Unauthorized',
        402 => 'HTTP/1.1 402 Payment Required',
        403 => 'HTTP/1.1 403 Forbidden',
        404 => 'HTTP/1.1 404 Not Found',
        405 => 'HTTP/1.1 405 Method Not Allowed',
        406 => 'HTTP/1.1 406 Not Acceptable',
        407 => 'HTTP/1.1 407 Proxy Authentication Required',
        408 => 'HTTP/1.1 408 Request Time-out',
        409 => 'HTTP/1.1 409 Conflict',
        410 => 'HTTP/1.1 410 Gone',
        411 => 'HTTP/1.1 411 Length Required',
        412 => 'HTTP/1.1 412 Precondition Failed',
        413 => 'HTTP/1.1 413 Request Entity Too Large',
        414 => 'HTTP/1.1 414 Request-URI Too Large',
        415 => 'HTTP/1.1 415 Unsupported Media Type',
        416 => 'HTTP/1.1 416 Requested Range Not Satisfiable',
        417 => 'HTTP/1.1 417 Expectation Failed',
        500 => 'HTTP/1.1 500 Internal Server Error',
        501 => 'HTTP/1.1 501 Not Implemented',
        502 => 'HTTP/1.1 502 Bad Gateway',
        503 => 'HTTP/1.1 503 Service Unavailable',
        504 => 'HTTP/1.1 504 Gateway Time-out',
        505 => 'HTTP/1.1 505 HTTP Version Not Supported',
    );
    if($title != null)
    {
    	$http[$code] = substr($http[$code],0,13). " " .$title;
    }

    header($http[$code]);
    header('Content-type: text/plain');

    if (!empty($message)) {
        exit($message);
    } else {
        exit();
    }
}

if(isset($_POST["sec_caja_depositos_hermeticase"])){

	$get_data = $_POST["sec_caja_depositos_hermeticase"];
	$local_id = $get_data["sec_caja_hermeticase_local_id"];
	$fecha_inicio = $get_data["sec_caja_hermeticase_fecha_inicio"];
	$fecha_inicio_pretty = date("d-m-Y",strtotime($get_data["sec_caja_hermeticase_fecha_inicio"]));
	$fecha_fin = date("Y-m-d",strtotime($get_data["sec_caja_hermeticase_fecha_fin"]." +1 day"));
	$fecha_fin_pretty = date("d-m-Y",strtotime($get_data["sec_caja_hermeticase_fecha_fin"]));

    $cantidad_inicio = null;
    $cantidad_fin =  null;

	//	Filtrado por permisos de locales
		$permiso_locales="";

		if($login["usuario_locales"]){
			$permiso_locales .=" AND l.id IN (".implode(",", $login["usuario_locales"]).") ";
			}

    $red_id = $local_id == "_all_terminales_" ? "(7)" : "(1, 9)";

    $caja_arr = array();
	$caja_command = "
	SELECT
		c.id,
		c.fecha_operacion,
		c.turno_id,
		l.id AS local_id,
		l.nombre AS local_nombre,
		l.cc_id,
		(SELECT
		SUM(IFNULL(df.valor,0))
		FROM tbl_caja_datos_fisicos df
		WHERE df.caja_id = c.id AND df.tipo_id = '27') AS hermeticase_venta,
		(SELECT
		SUM(IFNULL(df.valor,0))
	FROM tbl_caja_datos_fisicos df
	WHERE df.caja_id = c.id AND df.tipo_id = '26') AS hermeticase_boveda
	FROM tbl_caja c
	LEFT JOIN tbl_local_cajas lc ON(lc.id = c.local_caja_id)
	LEFT JOIN tbl_locales l ON (l.id = lc.local_id)";
	$caja_command .= " WHERE c.estado = '1'";
	$caja_command .= " AND l.red_id IN $red_id";
	$caja_command .= " AND c.validar = '1'";
	if(!($local_id == "_all_" || $local_id == "_all_terminales_")){
        $caja_command .= " AND l.id = '".$local_id."'";
	}else{
		$caja_command .= $permiso_locales;
	}
	$caja_command .= " AND c.fecha_operacion >= '".$fecha_inicio."'
	AND c.fecha_operacion < '".$fecha_fin."'
	GROUP BY c.fecha_operacion, l.id, c.turno_id
	ORDER BY c.fecha_operacion ASC, l.nombre ASC, c.turno_id ASC
	";
	$mysqli->query("SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED");
	$caja_query = $mysqli->query($caja_command);
	if($mysqli->error){
		print_r($mysqli->error);
		exit();
	}
	$mysqli->query("COMMIT");
	$caja_data = array();
	while($c = $caja_query->fetch_assoc()){
		$caja_data[] = $c;
	}
	$total = array();
	$total["venta"] = 0;
	$total["boveda"] = 0;
	$total["total"] = 0;
	$total["reporte"] = 0;
	$total["diferencia"] = 0;

	$table = array();
	$table["tbody"] = array();
	foreach ($caja_data AS $key => $tr) {
		$tr["total"] = ($tr["hermeticase_venta"] + $tr["hermeticase_boveda"]);
		$tr["reporte"] = 0;
		$tr["diferencia"] = 0;
		if($tr["total"] > 0 ){
			$sql_command_ = "
				SELECT *
				FROM tbl_transacciones_hermeticase th
				WHERE th.caja_id = '".$tr["id"]."' 
				AND tipo = 0
				ORDER BY th.id ASC";
			$sql_query_ = $mysqli->query($sql_command_);
			$input = "";
			$total_reporte = 0;
			if($sql_query_->num_rows){
				while($itm = $sql_query_->fetch_assoc()){
					$input .= '<button id="btnCheckDeposito" class="btn btn-block btn-xs btn-success" name="cons'.$itm["id"].'" data-id="'.$itm["id"].'" data-cajaid="'.$itm["caja_id"].'" data-monto="'.$itm["monto"].'" data-fecha="'.$itm["fecha_inicio"].'" data-mvto="'.$itm["id_transaccion"].'" data-nro_op="'.$itm["nro_operacion"].'" data-tipo="'.$itm["tipo"].'">'.$itm["monto"].'</button>';
					$total_reporte += $itm["monto"];
				}
			}
			else 
			{
				$input .= '<button id="btnCheckDeposito" class="btn btn-block btn-xs btn-warning" data-id="'.$tr["id"].'" data-tipo="0"><i class="glyphicon glyphicon-search"></i></button>';
			}
			$tr['reporte'] = $total_reporte;
			$tr['diferencia'] = ( $tr["total"] - $total_reporte );
			$tr['reporte_inputs'] = $input;

			$total["venta"] += $tr["hermeticase_venta"];
			$total["boveda"] += $tr["hermeticase_boveda"];
			$total["total"] += $tr["total"];
			$total["reporte"] += $tr["reporte"];
			$total["diferencia"] += $tr["diferencia"];

			$table["tbody"][]=$tr;
		}
	}
    ?>
	<p><button id="btnFilterConciliadosHermeticase" class="btn btn-default"><i id="icoFilterConciliadosHermeticase" class="glyphicon glyphicon-filter"></i> Filtrar Conciliados</button></p>
	<table id="tbl_caja_depositos_hermeticase" class="table table-condensed table-bordered table-striped">
		<thead>
			<tr>
				<th>Fecha</th>
				<th>CC</th>
				<th>Local</th>
				<th>Turno</th>
				<th>Hermeticase Venta</th>
				<th class="terminales-hide">Hermeticase Bóveda</th>
				<th> Total </th>
				<th>Hermeticase Reporte</th>
				<th>Total</th>
				<th>Diferencia</th>
			</tr>
		</thead>
		<tbody id="tbl_caja_depositos_body">
			<?php
			foreach ($table["tbody"] as $k => $tr) {
				?>
				<tr data-local_id = "<?php echo $tr['local_id'];?>">
					<td id="tblFecha"><?php echo $tr["fecha_operacion"]; ?></td>
					<td id="tblCC"><?php echo $tr["cc_id"]; ?></td>
					<td id="tblLocal"><?php echo $tr["local_nombre"]; ?></td>
					<td class="text-center" id="tblTurno"><?php echo $tr["turno_id"]; ?></td>

					<td class="text-right" id="tblMonto"><?php echo number_format($tr["hermeticase_venta"],2); ?></td>
					<td class="text-right" class="terminales-hide" id="tblBoveda"><?php echo number_format($tr["hermeticase_boveda"],2); ?></td>
					<td class="text-right" id="tblTotal"><?php echo number_format( ( $tr["total"] ) , 2 );?></td>
					<td ><?php echo   $tr["reporte_inputs"];?></td>
					<td class="text-right"><?php echo number_format( ( $tr["reporte"] ) , 2 );?></td>
					<td class="text-right td_diferencia"><?php echo number_format( ( $tr["diferencia"] ) , 2 );?></td>
				</tr>
				<?php
			}
			?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="4" class="text-right"><b>Total:</b></td>
				<td class="text-right"><b><?php echo number_format($total["venta"],2); ?></b></td>
				<td class="text-right"><b><?php echo number_format($total["boveda"],2); ?></b></td>
				<td class="text-right"><b><?php echo number_format($total["total"],2); ?></b></td>
				<td class="text-center"><b><?php echo number_format($total["reporte"],2); ?></b></td>
				<td class="text-right"><b><?php echo number_format($total["reporte"],2); ?></b></td>
				<td class="text-right"><b><?php echo number_format($total["diferencia"],2); ?></b></td>
			</tr>
		</tfoot>
	</table>
	<?php
};

if(isset($_POST["sec_caja_hermeticase_archivo"])){
	//set_status_code_response(400, "Error.", "No se proceso el archivo debido a los siguientes errores:" );
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
		libxml_use_internal_errors(TRUE);
		$excelObj = $excelReader->load($tmpfname);
		$worksheet = $excelObj->getSheet(0);
		$lastRow = $worksheet->getHighestRow();
		$firstRow = 6;

		$titulo_excel = $worksheet->getCell('A1')->getValue();
		if($titulo_excel == "Histórico de Movimientos" || $titulo_excel == "Histrico de Movimientos")
		{
			$firstRow = 11;
			$errors = [];
			$columns_format = [
				["value" => "F. Operación", "value2" =>"F. Operacin" , "column" => "A" ] ,
				["value" => "F. Valor", 	"column" => "B" ] ,
				["value" => "Código"  , "value2" => "Cdigo" ,	"column" => "C" ] ,
				["value" => "Nº. Doc.", "value2" => "N. Doc." , "column" => "D" ] ,
				["value" => "Concepto", 	"column" => "E" ] ,
				["value" => "Importe", 		"column" => "F" ] ,
				["value" => "Oficina",	"column" => "G" ]
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
			for ($row = $firstRow + 1; $row <= $lastRow; $row++) {
				if($worksheet->getCell('A'.$row)->getValue() != "" ){
					
					$fecha_operacion = excel_date_to_php($worksheet->getCell('A'.$row));
					$fecha_valor = excel_date_to_php($worksheet->getCell('B'.$row));
					$codigo = $worksheet->getCell('C'.$row)->getValue();
					$nro_doc = $worksheet->getCell('D'.$row)->getValue();
					$concepto = $worksheet->getCell('E'.$row)->getValue();
					$importe = str_replace(",", "", $worksheet->getCell('F'.$row)->getValue());

					$oficina = $worksheet->getCell('G'.$row)->getValue();

					if( $importe <= 0 )
					{
						continue;
					}
					if( substr( $concepto, 0, 14 ) != "COFRES HERMES " )
					{
						continue;
					}
					$uid = md5($nro_doc.$fecha_operacion.$concepto.$importe);
					$extract[$uid] = [
						"at_unique_id" => $uid,
						"fecha_operacion" => $fecha_operacion,
						"fecha_valor" => $fecha_valor,
						"codigo" => $codigo,
						"nro_doc" => $nro_doc,
						"concepto" => str_replace("'", "", $concepto),
						"importe" => (float)$importe,
						"oficina" => $oficina,
						"created_at" => date("Y-m-d"),
						"updated_at" => date("Y-m-d"),
						"insert_tipo" => "import",
						"estado" => 0
					];
				}
			}
			$query_in = "";
			foreach ($extract as $key => $row) $query_in .= "'".$key."',";
			$query_in = substr($query_in, 0,-1);
			$query = "SELECT at_unique_id FROM tbl_transacciones_hermeticase_movimientos WHERE at_unique_id IN(".$query_in.")";
			$result = $mysqli->query($query);
			while($uid = $result->fetch_assoc()){
				if(isset($extract[$uid["at_unique_id"]]))
					unset($extract[$uid["at_unique_id"]]);
			}
			if(!empty($extract)){
				feed_database($extract, 'tbl_transacciones_hermeticase_movimientos', 'at_unique_id', false);
			}
			echo json_encode(
				[   "error" => false , 
					"msg" => "Excel Importado"					
				] 
			);
		}
		else{

				$columns_format = [
						["value" => "Cliente", 			"column" => "A" ] ,
						["value" => "Id Transacción", 	"column" => "B" ] ,
						["value" => "Punto", 			"column" => "C" ] ,
						["value" => "Dirección Punto", 	"column" => "D" ] ,
						["value" => "Cod Usuario", 		"column" => "E" ] ,
						["value" => "Usuario", 			"column" => "F" ] ,
						["value" => "U.M.", 			"column" => "G" ] ,
						["value" => "Cuenta", 			"column" => "H" ] ,
						["value" => "Montos ", 			"column" => "I" ] ,
						["value" => "Fecha Inicio", 	"column" => "J" ] ,
						["value" => "Fecha Fin", 		"column" => "K" ] ,
						["value" => "Observaciones", 	"column" => "L" ] ,
						["value" => "Fecha de Corte", 	"column" => "O" ] ,
						["value" => "Monto Total", 		"column" => "P" ] ,
						["value" => "Nro Remito", 		"column" => "Q" ] ,
						["value" => "Nro Operación", 	"column" => "R" ] 
				];
				foreach ($columns_format as $key => $value) {
					$column_letter = $value["column"];/*A B C -...*/
					$column_xls_value = $worksheet->getCell($column_letter.$firstRow)->getValue();
					if( strtoupper($column_xls_value) != strtoupper($value["value"]) )
					{
						$errors[] = "Columna <b>".$column_letter."</b> incorrecta  -  Se esperaba <b>" .$value["value"] ."</b>";
					}
				}

				$column_xls_totales = $worksheet->getCell("G".$lastRow)->getValue();
				if($column_xls_totales != "TOTALES SOLES" )
				{
					$errors[] = "<b>G".$lastRow."</b> - Se esperaba <b>TOTALES SOLES</b>";
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

				/*tbl_locales*/
				$tbl_locales = [];
				$query = "SELECT id,cc_id, nombre FROM tbl_locales ORDER BY nombre ASC";
				$query_result = $mysqli->query($query);
				while($r = $query_result->fetch_assoc()){
					$tbl_locales[$r["cc_id"]] = $r;
				}

				$extract = array();
				$firstRow_datos = $firstRow + 2;
				for ($row = $firstRow_datos; $row <= $lastRow - 1; $row++) {
					if($worksheet->getCell('A'.$row)->getValue() != ""){
						$id_transaccion = $worksheet->getCell('B'.$row)->getValue();
						$fecha_op = excel_date_to_php($worksheet->getCell('J'.$row));
						$fecha_fin = excel_date_to_php($worksheet->getCell('K'.$row));
						$fecha_corte = excel_date_to_php($worksheet->getCell('O'.$row));

						$cc_id = substr($worksheet->getCell('F'.$row), 0, 4);

						if( !isset($tbl_locales[$cc_id]) )
						{
							continue;
						}
						$local_id = $tbl_locales[$cc_id]["id"];

						$uid = md5($id_transaccion.$fecha_op);
						$extract[$uid] = [
							"at_unique_id" => $uid,
							"local_id" => $local_id,
							"cliente" => $worksheet->getCell('A'.$row)->getValue(),
							"id_transaccion" => $worksheet->getCell('B'.$row)->getValue(),
							"punto" => $worksheet->getCell('C'.$row)->getValue(),
							"direccion" => $worksheet->getCell('D'.$row)->getValue(),
							"cod" => $worksheet->getCell('E'.$row)->getValue(),
							"usuario" => $worksheet->getCell('F'.$row)->getValue(),
							"u_m" => $worksheet->getCell('G'.$row)->getValue(),
							"cuenta" => $worksheet->getCell('H'.$row)->getValue(),
							"monto" => $worksheet->getCell('I'.$row)->getValue(),
							"fecha_inicio" => $fecha_op,
							"fecha_fin" => $fecha_fin,
							"observaciones" => $worksheet->getCell('L'.$row)->getValue(),
							"fecha_corte" => $fecha_corte,
							"monto_total" => $worksheet->getCell('P'.$row)->getValue(),
							"nro_remito" => $worksheet->getCell('Q'.$row)->getValue(),
							"nro_operacion" => $worksheet->getCell('R'.$row)->getValue(),
							"created_at" => date("Y-m-d H:i:s"),
						];
					}
				}
				$query_in = "";
				foreach ($extract as $key => $row) $query_in .= "'".$key."',";
				$query_in = substr($query_in, 0,-1);
				$query = "SELECT at_unique_id FROM tbl_transacciones_hermeticase where at_unique_id IN(".$query_in.")";
				$result = $mysqli->query($query);
				while($uid = $result->fetch_assoc()){
					if(isset($extract[$uid["at_unique_id"]]))
						unset($extract[$uid["at_unique_id"]]);
				}
				if(!empty($extract)){
					feed_database($extract, 'tbl_transacciones_hermeticase', 'at_unique_id', false);
				}
				echo json_encode(
						[   "error" => false , 
							"msg" => "Excel Importado"					
						] 
					);
			}
		}

}


if(isset($_POST["sec_caja_hermeticase_boveda_archivo"])){
	//set_status_code_response(400, "Error.", "No se proceso el archivo debido a los siguientes errores:" );
	$ext = pathinfo($_FILES['concar_hermeticase_file']['name'], PATHINFO_EXTENSION);
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
		$tmpfname = $_FILES['concar_hermeticase_file']['tmp_name'];
		$excelReader = PHPExcel_IOFactory::createReaderForFile($tmpfname);
		libxml_use_internal_errors(TRUE);
		$excelObj = $excelReader->load($tmpfname);
		$worksheet = $excelObj->getSheet(0);
		$lastRow = $worksheet->getHighestRow();
		$firstRow = 6;
		$titulo_excel = trim($worksheet->getCell('A1')->getValue());
		$titulo_razon_social = getParameterGeneral('hermeticase_razon_social');
		
		if( $titulo_excel == $titulo_razon_social )
		{
			$firstRow = 7;
			$errors = [];
			$columns_format = [
				["value" => "CUENTA" , "column" => "A"],
				["value" => "ANEXO     " , "column" => "B"],
				["value" => "NOMBRE" , "column" => "C"],
				["value" => "TIP" , "column" => "D"],
				["value" => "NUMERO " , "column" => "E"],
				["value" => "FECHA " , "column" => "F"],
				["value" => "FECHA" , "column" => "G"],
				["value" => "FECHA" , "column" => "H"],
				["value" => "SD" , "column" => "I"],
				["value" => "NUMERO" , "column" => "J"],
				["value" => "GLOSA" , "column" => "K"],
				["value" => "MO." , "column" => "L"],
				["value" => "SALDO DOLARES" , "column" => "M"],
				["value" => "" , "column" => "N"],
				["value" => "SALDO MONEDA NACIONAL" , "column" => "O"],
				["value" => "" , "column" => "P"]
			];

			foreach ($columns_format as $key => $value) {
				$column_letter = $value["column"];/*A B C -...*/
				$column_xls_value = $worksheet->getCell($column_letter.$firstRow)->getValue();
				$column_xls_value = trim($column_xls_value);
				if( trim(strtoupper($column_xls_value)) != trim(strtoupper($value["value"])) )
				{
					$errors[] = "Columna <b>".$column_letter.$firstRow."</b> incorrecta  -  Se esperaba <b>" .$value["value"] ."</b> - " .$column_xls_value;
				}
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
			$firstRow = 10;
			$extract = array();
			$filas = [];
			/*tbl_locales*/
			$tbl_locales = [];
			$query = "SELECT id,cc_id, nombre FROM tbl_locales ORDER BY nombre ASC";
			$query_result = $mysqli->query($query);
			while($r = $query_result->fetch_assoc()){
				$tbl_locales[$r["cc_id"]] = $r;
			}

			for ($row = $firstRow + 1; $row <= $lastRow; $row++) {
				if($worksheet->getCell('A'.$row)->getValue() != "" ){
					$fecha_documento = DateTime::createFromFormat("d/m/Y", $worksheet->getCell('F'. $row)->getValue());
					$fecha_documento = $fecha_documento ? $fecha_documento->format("Y-m-d") : "null";

					$fecha_comprobante = DateTime::createFromFormat("d/m/Y", $worksheet->getCell('G'. $row)->getValue());
					$fecha_comprobante = $fecha_comprobante ? $fecha_comprobante->format("Y-m-d") : "null";

					$fecha_vencimiento = DateTime::createFromFormat("d/m/Y", $worksheet->getCell('H'. $row)->getValue());
					$fecha_vencimiento = $fecha_vencimiento ? $fecha_vencimiento->format("Y-m-d") : "null";
					
					$saldo_moneda_nacional_debe  = $worksheet->getCell('O'. $row)->getValue();
					$saldo_moneda_nacional_haber  = $worksheet->getCell('P'. $row)->getValue();

					$sd = trim($worksheet->getCell('I'. $row)->getValue());
					$glosa = trim($worksheet->getCell('K'. $row)->getValue());

					if( $saldo_moneda_nacional_debe == "" )
					{
						continue;
					}
					if( $saldo_moneda_nacional_debe <= 0 )
					{
						continue;
					}
					if( trim($worksheet->getCell('D'.$row)) != "PR" )
					{
						continue;
					}
					if( $sd != "2220" )
					{
						continue;
					}
					if( !in_array($glosa ,["PRESTAMO BOVEDA" , "PRESTAMOS BOVEDA" , "PRESTAMOS BOVEDAS"]) )
					{
						continue;
					}
					$cc_id = substr(trim($worksheet->getCell('B'.$row)), 0, 4);
					if( !isset($tbl_locales[$cc_id]) )
					{
						continue;
					}

					$local_id = $tbl_locales[$cc_id]["id"];
					$importe = $saldo_moneda_nacional_haber ? $saldo_moneda_nacional_haber : $saldo_moneda_nacional_debe ;
					$data = [
						"cuenta" => trim($worksheet->getCell('A'. $row)->getValue()),
						"anexo" => trim($worksheet->getCell('B'. $row)->getValue()),
						"nombre" => trim($worksheet->getCell('C'. $row)->getValue()),
						"tipo" => trim($worksheet->getCell('D'. $row)->getValue()),
						"documento" => trim($worksheet->getCell('E'. $row)->getValue()),
						"fecha_documento" => $fecha_documento,
						"fecha_comprobante" => $fecha_comprobante,
						"fecha_vencimiento" => $fecha_vencimiento,
						"comprobante_sd" => $sd,
						"numero" => trim($worksheet->getCell('J'. $row)->getValue()),
						"glosa" => $glosa,
						"centro_costos" => $cc_id,
						"saldo_dolares_debe" => $worksheet->getCell('M'. $row)->getValue(),
						"saldo_dolares_haber" => $worksheet->getCell('N'. $row)->getValue(),
						"saldo_moneda_nacional_debe" => $saldo_moneda_nacional_debe,
						"saldo_moneda_nacional_haber" => $saldo_moneda_nacional_haber,
						"local_id" => $local_id,
						"importe" => $importe,
						"saldo" => $importe
					];
					$data["unique_id"] = md5(
						$data["comprobante_sd"] .
						$data["numero"] .
						$data["anexo"] .
						$data["documento"] .
						$data["fecha_documento"] .
						$data["centro_costos"] .
						$data["glosa"] .
						$data["saldo_moneda_nacional_debe"] .
						$data["saldo_moneda_nacional_haber"]);
					$filas[] = $data;
				}
			}
			if(!empty($filas)){
				$mysqli->query("TRUNCATE tbl_repositorio_hermeticase_prestamos_boveda;");
				feed_database($filas, 'tbl_repositorio_hermeticase_prestamos_boveda', 'unique_id', true, []);
				echo json_encode(
					[   "error" => false , 
						"msg" => "Excel Importado"
					] 
				);
				return;
			}
			
			echo json_encode(
				[   "error" => false , 
					"msg" => "Excel Importado"					
				] 
			);
		}
		// if( $titulo_excel == "OPERACIONES AT SAC" )
		// {

		// 	$firstRow = 7;
		// 	$errors = [];
			


		// 	$columns_format = [
		// 		["value" => "SD" , "column" => "A"],
		// 		["value" => "NUMERO " , "column" => "B"],
		// 		["value" => "F." , "column" => "C"],
		// 		["value" => "ANEXO     " , "column" => "D"],
		// 		["value" => "DOCUMENTO" , "column" => "E"],
		// 		["value" => "DOCUM. " , "column" => "F"],
		// 		["value" => "COSTOS" , "column" => "G"],
		// 		["value" => "GLOSA" , "column" => "H"],
		// 		["value" => "CAMB." , "column" => "I"],
		// 		["value" => "D E B E" , "column" => "J"],
		// 		["value" => "H A B E R" , "column" => "K"],
		// 		["value" => "D E B E" , "column" => "L"],
		// 		["value" => "H A B E R" , "column" => "M"]
		// 	];

		// 	foreach ($columns_format as $key => $value) {
		// 		$column_letter = $value["column"];/*A B C -...*/
		// 		$column_xls_value = $worksheet->getCell($column_letter.$firstRow)->getValue();
		// 		$column_xls_value = trim($column_xls_value);
		// 		if( trim(strtoupper($column_xls_value)) != trim(strtoupper($value["value"])) )
		// 		{
		// 			$errors[] = "Columna <b>".$column_letter.$firstRow."</b> incorrecta  -  Se esperaba <b>" .$value["value"] ."</b> - " .$column_xls_value;
		// 		}
		// 	}
		// 	/*$nro_registros = $lastRow - $firstRow - 2;
		// 	if($nro_registros <= 0 )
		// 	{
		// 		$errors[] = "<b>No hay registros</b>";
		// 	}*/

		// 	if( count($errors) > 0 )
		// 	{
		// 		echo json_encode(
		// 			["error" => true ,
		// 				"msg" => "Error en archivo xls",
		// 				"msg_error" => implode("<br>", $errors)
		// 			]
		// 		);
		// 		return;
		// 	}
		// 	$firstRow = 12;
		// 	$extract = array();
		// 	$filas = [];
		// 	/*tbl_locales*/
		// 	$tbl_locales = [];
		// 	$query = "SELECT id,cc_id, nombre FROM tbl_locales ORDER BY nombre ASC";
		// 	$query_result = $mysqli->query($query);
		// 	while($r = $query_result->fetch_assoc()){
		// 		$tbl_locales[$r["cc_id"]] = $r;
		// 	}

		// 	for ($row = $firstRow + 1; $row <= $lastRow; $row++) {
		// 		if($worksheet->getCell('A'.$row)->getValue() != "" ){
		// 			$fecha_documento = DateTime::createFromFormat("d/m/Y", $worksheet->getCell('F'. $row)->getValue());
		// 			$fecha_documento = $fecha_documento ? $fecha_documento->format("Y-m-d") : "null";
					
		// 			$saldo_moneda_nacional_debe  = $worksheet->getCell('L'. $row)->getValue();
		// 			$saldo_moneda_nacional_haber  = $worksheet->getCell('M'. $row)->getValue();
		// 			if( $saldo_moneda_nacional_debe <= 0 && $saldo_moneda_nacional_haber <= 0 )
		// 			{
		// 				continue;
		// 			}
		// 			$es_pr = substr(trim($worksheet->getCell('E'.$row)), 0, 2);
		// 			if( $es_pr != "PR" )
		// 			{
		// 				continue;
		// 			}
		// 			$cc_id = substr(trim($worksheet->getCell('D'.$row)), 0, 4);
		// 			if( !isset($tbl_locales[$cc_id]) )
		// 			{
		// 				continue;
		// 			}

		// 			$local_id = $tbl_locales[$cc_id]["id"];
		// 			$glosa = trim($worksheet->getCell('H'. $row)->getValue());
		// 			$importe = $saldo_moneda_nacional_haber ;
		// 			/*if( $glosa == 'PRESTAMOS BOVEDAS')
		// 			{
		// 				$importe = $saldo_moneda_nacional_debe ;
		// 			}*/
		// 			$data = [
		// 				"comprobante_sd" => trim($worksheet->getCell('A'. $row)->getValue()),
		// 				"numero" => trim($worksheet->getCell('B'. $row)->getValue()),
		// 				"f" => trim($worksheet->getCell('C'. $row)->getValue()),
		// 				"anexo" => trim($worksheet->getCell('D'. $row)->getValue()),
		// 				"documento" => trim($worksheet->getCell('E'. $row)->getValue()),
		// 				"fecha_documento" => $fecha_documento,
		// 				"centro_costos" => trim($worksheet->getCell('G'. $row)->getValue()),
		// 				"glosa" => $glosa,
		// 				"tipo_cambio" => trim($worksheet->getCell('I'. $row)->getValue()),
		// 				"saldo_dolares_debe" => $worksheet->getCell('J'. $row)->getValue(),
		// 				"saldo_dolares_haber" => $worksheet->getCell('K'. $row)->getValue(),
		// 				"saldo_moneda_nacional_debe" => $saldo_moneda_nacional_debe,
		// 				"saldo_moneda_nacional_haber" => $saldo_moneda_nacional_haber,
		// 				"local_id" => $local_id,
		// 				"importe" => $importe,
		// 				"saldo" => $importe
		// 			];
		// 			$data["unique_id"] = md5(
		// 				$data["comprobante_sd"] .
		// 				$data["numero"] .
		// 				$data["f"] .
		// 				$data["anexo"] .
		// 				$data["documento"] .
		// 				$data["fecha_documento"] .
		// 				$data["centro_costos"] .
		// 				$data["glosa"] .
		// 				$data["saldo_moneda_nacional_debe"] .
		// 				$data["saldo_moneda_nacional_haber"]);
		// 			$filas[] = $data;
		// 		}
		// 	}
		// 	if(!empty($filas)){
		// 		$mysqli->query("TRUNCATE tbl_repositorio_hermeticase_prestamos_boveda;");
		// 		feed_database($filas, 'tbl_repositorio_hermeticase_prestamos_boveda', 'unique_id', true, []);
		// 		echo json_encode(
		// 			[   "error" => false , 
		// 				"msg" => "Excel Importado"
		// 			] 
		// 		);
		// 		return;
		// 	}
			
		// 	echo json_encode(
		// 		[   "error" => false , 
		// 			"msg" => "Excel Importado"					
		// 		] 
		// 	);
		// }
		
		echo json_encode(
			[   "error" => true, 
				"msg" => "Importar Concar",
				"msg_error" => "Error al Importar Archivo"
			] 
		);
	}
}

if(isset($_POST["get_locales"])){
    $get_locales = $_POST["get_locales"];
    $red_id = "";
    $locales_command = "";
    $locales_arr = [];

    if($get_locales === "true"){
        $red_id = "(7)";
        $locales_arr[] = [
            "id" => "_all_terminales_",
            "nombre" => "Todos (Puede demorar)"
        ];
    } else {
        $red_id = "(1, 7, 9)";
        $locales_arr[] = [
            "id" => "_all_",
            "nombre" => "Todos (Puede demorar)"
        ];
    }

    if($login["usuario_locales"]){
        $locales_command = " AND l.id IN (".implode(",", $login["usuario_locales"]).")";
    }

    $locales_command = "
            SELECT l.id, l.cc_id, l.nombre FROM tbl_locales l
            WHERE l.red_id IN $red_id OR l.id = '200' $locales_command
            ORDER BY l.nombre ASC
        ";

    $locales_query = $mysqli->query($locales_command);
    if($mysqli->error){
        print_r($mysqli->error);
        exit();
    }
    while($l=$locales_query->fetch_assoc()){
        $locales_arr[] = [
                "id" => $l["id"],
                "nombre" => "[$l[cc_id]] $l[nombre]"
        ];
    }
    echo json_encode($locales_arr);
}

if(isset($_POST["sec_caja_hermeticase_Prevalidacion"])){

	$get_data = $_POST["sec_caja_hermeticase_Prevalidacion"];
	$local_id = $get_data["sec_caja_hermeticase_local_id"];
	$fecha_inicio = $get_data["sec_caja_hermeticase_fecha_inicio"];
	$fecha_inicio_pretty = date("d-m-Y",strtotime($get_data["sec_caja_hermeticase_fecha_inicio"]));
	$fecha_fin = date("Y-m-d",strtotime($get_data["sec_caja_hermeticase_fecha_fin"]." +1 day"));
	$fecha_fin_pretty = date("d-m-Y",strtotime($get_data["sec_caja_hermeticase_fecha_fin"]));

	//	Filtrado por permisos de locales

		$permiso_locales="";
		if($login["usuario_locales"]){
			$permiso_locales.=" AND l.id IN (".implode(",", $login["usuario_locales"]).") ";
			}

    $cantidad_inicio = null;
    $cantidad_fin =  null;

    $red_id = $local_id == "_all_terminales_" ? "(7)" : "(1, 9)";

    $caja_arr = array();
	$caja_command = "SELECT
			c.id,
			c.fecha_operacion,
			c.turno_id,
			l.id AS local_id,
			l.nombre AS local_nombre,
			l.cc_id,
			(SELECT
			SUM(IFNULL(df.valor,0))
			FROM tbl_caja_datos_fisicos df
			WHERE df.caja_id = c.id AND df.tipo_id = '27') AS hermeticase_venta,
			(SELECT
			SUM(IFNULL(df.valor,0))
		FROM tbl_caja_datos_fisicos df
		WHERE df.caja_id = c.id AND df.tipo_id = '26') AS hermeticase_boveda
		FROM tbl_caja c
		LEFT JOIN tbl_local_cajas lc ON(lc.id = c.local_caja_id)
		LEFT JOIN tbl_locales l ON (l.id = lc.local_id)";
	$caja_command .= " WHERE c.estado = '1'";
	$caja_command .= " AND l.red_id IN $red_id";
	$caja_command .= " AND c.validar = '1'";
	if(!($local_id == "_all_" || $local_id == "_all_terminales_")){
        $caja_command .= " AND l.id = '".$local_id."'";
	}else{
		$caja_command .= $permiso_locales;
	}
	$caja_command .= " AND c.fecha_operacion >= '".$fecha_inicio."'
	AND c.fecha_operacion < '".$fecha_fin."'
	GROUP BY c.fecha_operacion, l.id, c.turno_id
	ORDER BY c.fecha_operacion ASC, l.nombre ASC, c.turno_id ASC
	";
	$mysqli->query("SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED");
	$caja_query = $mysqli->query($caja_command);

	if($mysqli->error){
		print_r($mysqli->error);
		exit();
	}
	//echo $caja_command;
	$mysqli->query("COMMIT");
	$transactions = array();
	while($c = $caja_query->fetch_assoc()){
		$c["total"] = ( $c['hermeticase_venta'] + $c['hermeticase_boveda'] ) ; 
		$c["total"] = number_format($c["total"] ,2,".","");
		$auto_conc = array();
        if ($c['total'] == 0) {
            continue;
        }

        $sql_ = "SELECT * FROM tbl_transacciones_hermeticase
			WHERE DATE(fecha_inicio) = '$c[fecha_operacion]' 
			AND monto > 0
			AND caja_id IS NULL
			AND local_id = " .$c["local_id"] ;/*TRANSACCIONES hermeticase local_id = local_id   */
		$result_ = $mysqli->query($sql_);
		
		$transacciones = [];
		while($row = $result_->fetch_assoc()){
			$transacciones[ $row["nro_operacion"] ][] = $row;
		}
		$movimientos = array();
		foreach($transacciones as $index => $value) {
		if(!in_array($index, $movimientos)) {
				$movimientos[] = $index;
			}
		}
		//echo "<pre>transacciones:";print_r($transacciones);echo "</pre>";
		//echo "<pre>movimientos: ";print_r($movimientos);echo "</pre>";
		foreach($movimientos as $index => $value) {
			$nro_doc = $value;
			$sql_mov = "SELECT thm.nro_doc , thm.importe , thm.concepto,thm.fecha_operacion
				FROM tbl_transacciones_hermeticase_movimientos thm
				WHERE  TRIM(LEADING '0' FROM thm.nro_doc) = $nro_doc";

			$query_mov = $mysqli->query($sql_mov);
			if($query_mov){
				$result_movimiento = $query_mov->fetch_assoc();
				if($result_movimiento)
				{
					//echo "<pre>result_movimiento: ";print_r($result_movimiento);echo "</pre>";
					/*$auto_conc["transacciones_hermeticase"] = $transacciones[$nro_doc];
					$c["mov_nro_doc"] = $result_movimiento["nro_doc"];
					$c["mov_importe"] = $result_movimiento["importe"];
					$c["mov_fecha_operacion"] = $result_movimiento["fecha_operacion"];
	
					if(!empty($auto_conc)){
						$transactions[] = array_merge($c, $auto_conc);
					}*/
					$rows_aux = array();
					$ventas_sum = 0;
					$venta_total = str_replace(",", "" , $c['total']);
					foreach( $transacciones[$nro_doc] as $iii => $row ){
						$ventas_sum += $row['monto'];
						$rows_aux[] = $row;
						if ($row['monto'] == $venta_total){
							$auto_conc["transacciones_hermeticase"] = array();
							$auto_conc["transacciones_hermeticase"][] = $row;
						}
					}
					if ($ventas_sum == $venta_total && !array_key_exists('transacciones_hermeticase', $auto_conc)){
						$auto_conc["transacciones_hermeticase"] = $rows_aux;
					}
					if(!empty($auto_conc)){
						$transactions[] = array_merge($c, $auto_conc);
					}
				}
			}
		}
	}
	//echo "<pre>auto_conc final: ";print_r($transactions);echo "</pre>";

	echo json_encode($transactions); die;
}

if(isset($_POST["sec_caja_hermeticase_conciliar_automatico"])){
	$get_data = $_POST["sec_caja_hermeticase_conciliar_automatico"];
	$mysqli->query("START TRANSACTION");
	foreach ($get_data as $data) {
		$update_estado_data = "
		UPDATE tbl_transacciones_hermeticase 
		SET caja_id = '".$data["caja_id"]."' 
			,tipo = '".$data["tipo"]."' 
			WHERE id =  '".$data["id_transaccion"]."'";
		$mysqli->query($update_estado_data);
		/*$update_estado_data = "UPDATE tbl_caja_depositos SET validar_registro = 1 WHERE caja_id='".$data["caja_id"]."'";
		$mysqli->query($update_estado_data);*/
	}
	$mysqli->query("COMMIT");
}

if(isset($_POST["opt"]) && $_POST["opt"] == "sec_caja_hermeticase_get_concar" ){
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

	$searchQuery = " ";
	if($searchValue != ''){
	   $searchQuery = " AND (l.nombre like '%".$searchValue."%' or 
	        pd.correlativo like '%".$searchValue."%' or 
	        p.fecha_ingreso like'%".$searchValue."%' ) ";
	}
	$limit = " LIMIT ".$row.",".$rowperpage;
	if( $rowperpage == -1 ){
		$limit = "";
	}
	$query_all = "SELECT
			ch.id,
			ch.local_id,
			l.nombre,
			ch.cambio,
			ch.correlativo,
			u.usuario,
			f.url,
			ch.fecha_operacion,
			DATE(ch.fecha_inicio) AS fecha_inicio,
			DATE(ch.fecha_fin) AS fecha_fin
		FROM tbl_concar_hermeticase_historico ch
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


if(isset($_POST["opt"]) && $_POST["opt"] == "sec_caja_hermeticase_get_concar_boveda" ){
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

	$searchQuery = " ";
	if($searchValue != ''){
	   $searchQuery = " AND (l.nombre like '%".$searchValue."%' or 
	        pd.correlativo like '%".$searchValue."%' or 
	        p.fecha_ingreso like'%".$searchValue."%' ) ";
	}
	$limit = " LIMIT ".$row.",".$rowperpage;
	if( $rowperpage == -1 ){
		$limit = "";
	}
	$query_all = "SELECT
			ch.id,
			ch.local_id,
			l.nombre,
			ch.cambio,
			ch.correlativo,
			u.usuario,
			f.url,
			ch.fecha_operacion,
			DATE(ch.fecha_inicio) AS fecha_inicio,
			DATE(ch.fecha_fin) AS fecha_fin
		FROM tbl_concar_hermeticase_boveda_historico ch
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


if(isset($_POST["delete_concar_hermeticase_history"])){
	$get_data = $_POST["delete_concar_hermeticase_history"];
	$exported = [];
	$result = $mysqli->query("
		SELECT
			f.id,
			f.url
		FROM tbl_concar_hermeticase_historico ch
		INNER JOIN tbl_exported_files f ON f.id = ch.exported_id
		WHERE ch.id =".$get_data["id"]
	);
	while($r = $result->fetch_assoc()) $exported = $r;

	unlink("/var/www/html/export/files_exported/".$exported["url"]);
	$mysqli->query("DELETE FROM tbl_exported_files WHERE id =".$exported["id"]);
	$mysqli->query("DELETE FROM tbl_concar_hermeticase_historico WHERE id =".$get_data["id"]);
}
if(isset($_POST["delete_concar_hermeticase_boveda_history"])){
	$get_data = $_POST["delete_concar_hermeticase_boveda_history"];
	$exported = [];
	$result = $mysqli->query("
		SELECT
			f.id,
			f.url
		FROM tbl_concar_hermeticase_boveda_historico ch
		INNER JOIN tbl_exported_files f ON f.id = ch.exported_id
		WHERE ch.id =".$get_data["id"]
	);
	while($r = $result->fetch_assoc()) $exported = $r;

	unlink("/var/www/html/export/files_exported/".$exported["url"]);
	$mysqli->query("DELETE FROM tbl_exported_files WHERE id =".$exported["id"]);
	$mysqli->query("DELETE FROM tbl_concar_hermeticase_boveda_historico WHERE id =".$get_data["id"]);
}


?>