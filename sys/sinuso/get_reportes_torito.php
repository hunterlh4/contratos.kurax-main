<?php
$result=array();
include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);




//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER TRANSACCIONES X CAJERO
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"]==="listar_transacciones") {

	$usuario_id = $login['id'];
	$cargo_id   = $login['cargo_id'];
	$busqueda_fecha_inicio = date("Y/m/d", strtotime($_POST["fecha_inicio"]));
	$busqueda_fecha_fin    = date("Y/m/d", strtotime($_POST["fecha_fin"]));
	$busqueda_tipo_txn     = $_POST["tipo_transaccion"];
	$busqueda_cajero       = $_POST["cajero"];
	$busqueda_red          = $_POST["red"];
	$busqueda_local        = $_POST["local"];
	$busqueda_txn_id       = $_POST["num_transaccion"];
	$busqueda_estado      = $_POST["estado"];

	$where_fecha_inicio = " AND tt.date>= '".$busqueda_fecha_inicio."'";
	$where_fecha_fin    = " AND tt.date<= '".$busqueda_fecha_fin."'";
	$where_tipo_txn     = "";
	$where_txn_id       = "";
	if( (int) $busqueda_tipo_txn > 0 ){
		$where_tipo_txn = " AND tt.id_torito_tipo_transaccion='".$busqueda_tipo_txn."' ";
	}
	if( strlen($busqueda_txn_id) > 0 ){
		$where_txn_id = " AND tt.transactionid='".$busqueda_txn_id."' ";
	}

	$where_cajero = "";
	if( (int) $cargo_id === 5 ){
		$where_cajero = " AND tt.user_id = '".$usuario_id."' ";
	} else {
		if( (int) $busqueda_cajero > 0 ){
			$where_cajero = " AND tt.user_id = '".$busqueda_cajero."' ";
		}
	}

	$where_red = "";
	if( (int) $busqueda_red > 0 ){
		$where_red = " AND l.red_id = ".$busqueda_red." ";
		if( (int) $busqueda_red === 1 ){
			$where_red = " AND l.red_id IN (1,9) ";
		}
	}

	$where_local = "";
	if( (int) $cargo_id === 5 ){
		$where_local = "";
	} else {
		if( (int) $busqueda_local > 0 ){
			$where_local = " AND tt.cc_id='". str_pad( ((int) $busqueda_local), 4, "0", STR_PAD_LEFT) ."' ";
		}
	}

	$locales_command_where = '';
	if($login["usuario_locales"]){
		$locales_command_where= " AND l.id IN (";
		$locales_command_where.= implode(",", $login["usuario_locales"]);
		$locales_command_where.= ")";
	}

	$where_estado = '';
	if( (int) $busqueda_estado === 0 ){
		$where_estado = 'AND tt.status = 0';
	}elseif( (int) $busqueda_estado === 1 ){
		$where_estado = 'AND tt.status = 1';
	}

	$query_1 ="
		SELECT
			tt.status,
			tt.id,
			tt.transactionid hash,
			tt.user_id cod_cajero,
			tt.cc_id cod_local,
			IFNULL(lr.nombre, '') AS nombre_red,
			IFNULL(l.nombre,'') AS nombre_local,
			UPPER(u.usuario) usuario,
			CONCAT(
			IF
				( LENGTH( pl.apellido_paterno ) > 0, CONCAT( UPPER( pl.apellido_paterno ), ' ' ), '' ),
			IF
				( LENGTH( pl.apellido_materno ) > 0, CONCAT( UPPER( pl.apellido_materno ), ' ' ), '' ),
			IF
				( LENGTH( pl.nombre ) > 0, UPPER( pl.nombre ), '' ) 
			) nombre_cajero,
			IFNULL(l.nombre, 'SIN LOCAL') nombre_local,
			z.nombre AS nombre_zona,
			tt.id_torito_tipo_transaccion cod_tipo_transaccion,
			DATE_FORMAT(tt.date, '%d/%m/%Y') AS fecha,
			tt.time hora,
			tt.amount monto,
			ttt.transactiontype,
			CASE
			WHEN ttt.transactiontype='purchaseticket' THEN 'VENTA'
			WHEN ttt.transactiontype='prizepayment' THEN 'PAGO'
			WHEN ttt.transactiontype='topupwallet' THEN 'RECARGA'
			WHEN ttt.transactiontype='purchaseticket_mm' THEN 'VENTA MM'
			WHEN ttt.transactiontype='prizepayment_mm' THEN 'PAGO MM'
			WHEN ttt.transactiontype='purchaseticket_promo' THEN 'PROMO TORITO'
			WHEN ttt.transactiontype='purchaseticket_mm_promo' THEN 'PROMO TORITO MM'
			WHEN ttt.transactiontype='pcn_ticket' THEN 'CANJE TORITO'
			WHEN ttt.transactiontype='pcn_ticket_mm' THEN 'CANJE TORITO MM'
			WHEN ttt.transactiontype='generate_vltcode' THEN 'VENTA CODIGO'
			WHEN ttt.transactiontype='validate_cashoutpin' THEN 'PAGO CODIGO'
			WHEN ttt.transactiontype='vlt_purchaseticket' THEN 'VENTA VLT'
			WHEN ttt.transactiontype='vlt_prizepayment' THEN 'PAGO VLT'
			ELSE ''
			END AS tipo,
			CASE
			WHEN ttt.transactiontype='purchaseticket' THEN 0.13
			WHEN ttt.transactiontype='prizepayment' THEN 1.5
			WHEN ttt.transactiontype='topupwallet' THEN 0
			WHEN ttt.transactiontype='purchaseticket_mm' THEN 0
			WHEN ttt.transactiontype='prizepayment_mm' THEN 0
			ELSE 0
			END AS margen,
			CASE
			WHEN ttt.transactiontype='purchaseticket' THEN FORMAT( tt.amount * 0.13,2)
			WHEN ttt.transactiontype='prizepayment' THEN 1.5
			WHEN ttt.transactiontype='topupwallet' THEN 0
			WHEN ttt.transactiontype='purchaseticket_mm' THEN 0
			WHEN ttt.transactiontype='prizepayment_mm' THEN 0
			ELSE 0
			END AS comision,
			IF ((LEFT(upper(replace(l.nombre,' ', '')),5))= 'TELEV','Teleservicios','Tiendas') AS sub_canal,
			DAY(tt.date) AS dia,MONTH(tt.date) AS mes,YEAR(tt.date) AS anio,WEEK(tt.date)AS semana,
			IFNULL(tc.num_doc, '') num_doc,
			IF(tc.id>0,UPPER(CONCAT(IFNULL(tc.nombre, ''),' ',IFNULL(tc.apellido_paterno, ''),' ',IFNULL(tc.apellido_materno, ''))), '') cliente,
			CASE
				WHEN tt.status = 1 THEN 'Activo'
				WHEN tt.status = 0 THEN 'Inactivo'
			END AS estado_nombre
		FROM
			tbl_torito_transaccion tt
			JOIN tbl_usuarios u ON u.id = tt.user_id
			JOIN tbl_personal_apt pl ON pl.id = u.personal_id
			LEFT JOIN tbl_locales l ON l.cc_id = tt.cc_id
			LEFT JOIN tbl_locales_redes lr on lr.id=l.red_id
			LEFT JOIN tbl_zonas AS z ON z.id = l.zona_id
			JOIN tbl_torito_tipo_transaccion AS ttt ON ttt.id = tt.id_torito_tipo_transaccion
			LEFT JOIN tbl_televentas_clientes_transaccion tct ON tct.api_id = 9 and tct.txn_id = tt.transactionid
			LEFT JOIN tbl_televentas_clientes tc ON tc.id = tct.cliente_id
		WHERE 1
		".$where_estado ."
		".$where_fecha_inicio ."
		".$where_fecha_fin ."
		".$where_tipo_txn ."
		".$where_txn_id ."
		".$where_cajero ."
		".$where_red."
		".$where_local ."
		$locales_command_where
		ORDER BY tt.id ASC
	";
	//$result["consulta_query"] = $query_1;
	$list_query=$mysqli->query($query_1);
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["consulta_query"] = $query_1;
		$result["error"] = 'SQL error: ' . $mysqli->error;
		echo json_encode($result);
		exit;
	}
	$list_transaccion=array();
	while ($li=$list_query->fetch_assoc()) {
		$list_transaccion[]=$li;
	}

	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	}

	if(count($list_transaccion)==0){
		$result["http_code"] = 400;
		$result["status"] ="No hay transacciones.";
	} elseif(count($list_transaccion)>0){
		$result["http_code"] = 200;
		$result["status"] ="ok";
		$result["result"] =$list_transaccion;
		//$result["login"]=$login;
	} else{
		$result["http_code"] = 400;
		$result["status"] ="Ocurrió un error al consultar transacciones.";
	}

}





//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER TRANSACCIONES X CAJERO --> EXCEL
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"]==="listar_transacciones_export_xls") {

	$usuario_id = $login['id'];
	$cargo_id   = $login['cargo_id'];

	$busqueda_fecha_inicio = date("Y/m/d", strtotime($_POST["fecha_inicio"]));
	$busqueda_fecha_fin    = date("Y/m/d", strtotime($_POST["fecha_fin"]));
	$busqueda_tipo_txn     = $_POST["tipo_transaccion"];
	$busqueda_cajero       = $_POST["cajero"];
	$busqueda_red          = $_POST["red"];
	$busqueda_local        = $_POST["local"];
	$busqueda_txn_id       = $_POST["num_transaccion"];
	$busqueda_estado      = $_POST["estado"];

	$where_fecha_inicio = " AND tt.date>= '".$busqueda_fecha_inicio."'";
	$where_fecha_fin    = " AND tt.date<= '".$busqueda_fecha_fin."'";
	$where_tipo_txn     = "";
	$where_txn_id       = "";
	if( (int) $busqueda_tipo_txn > 0 ){
		$where_tipo_txn = " AND tt.id_torito_tipo_transaccion='".$busqueda_tipo_txn."' ";
	}
	if( strlen($busqueda_txn_id) > 0 ){
		$where_txn_id = " AND tt.transactionid='".$busqueda_txn_id."' ";
	}

	$where_cajero = "";
	if( (int) $cargo_id === 5 ){
		$where_cajero = " AND tt.user_id = '".$usuario_id."' ";
	} else {
		if( (int) $busqueda_cajero > 0 ){
			$where_cajero = " AND tt.user_id = '".$busqueda_cajero."' ";
		}
	}

	$where_red = "";
	if( (int) $busqueda_red > 0 ){
		$where_red = " AND l.red_id = ".$busqueda_red." ";
		if( (int) $busqueda_red === 1 ){
			$where_red = " AND l.red_id IN (1,9) ";
		}
	}

	$where_local = "";
	if( (int) $cargo_id === 5 ){
		$where_local = "";
	} else {
		if( (int) $busqueda_local > 0 ){
			$where_local = " AND tt.cc_id='". str_pad( ((int) $busqueda_local), 4, "0", STR_PAD_LEFT) ."' ";
		}
	}

	$locales_command_where = '';
	if($login["usuario_locales"]){
		$locales_command_where= " AND l.id IN (";
		$locales_command_where.= implode(",", $login["usuario_locales"]);
		$locales_command_where.= ")";
	}

	$where_estado = '';
	if( (int) $busqueda_estado === 0 ){
		$where_estado = 'AND tt.status = 0';
	}elseif( (int) $busqueda_estado === 1 ){
		$where_estado = 'AND tt.status = 1';
	}
	
	$query_1 ="
		SELECT
			tt.date fecha,
			tt.time hora,
			IFNULL(lr.nombre, '') AS nombre_red,
			tt.cc_id cod_local,
			IFNULL(l.nombre, 'SIN LOCAL') nombre_local,
			CONCAT(
			IF
				( LENGTH( pl.apellido_paterno ) > 0, CONCAT( UPPER( pl.apellido_paterno ), ' ' ), '' ),
			IF
				( LENGTH( pl.apellido_materno ) > 0, CONCAT( UPPER( pl.apellido_materno ), ' ' ), '' ),
			IF
				( LENGTH( pl.nombre ) > 0, UPPER( pl.nombre ), '' ) 
			) nombre_cajero,
			IFNULL(tc.num_doc, '') num_doc,
			IF(tc.id>0,UPPER(CONCAT(IFNULL(tc.nombre, ''),' ',IFNULL(tc.apellido_paterno, ''),' ',IFNULL(tc.apellido_materno, ''))), '') cliente,
			(CASE tt.id_torito_tipo_transaccion 
				WHEN '1' THEN 'VENTA GN' 
				WHEN '2' THEN 'PAGO GN' 
				WHEN '3' THEN 'RECARGA' 
				WHEN '4' THEN 'VENTA MM' 
				WHEN '5' THEN 'PAGO MM' 
				WHEN '6' THEN 'PROMO TORITO' 
				WHEN '7' THEN 'PROMO TORITO MM' 
				WHEN '8' THEN 'CANJE TORITO' 
				WHEN '9' THEN 'CANJE TORITO MM' 
				ELSE ttt.description
			END ) AS tipo_transaccion,
			tt.transactionid txn_id,
			tt.amount monto,
			CASE	
			WHEN ttt.transactiontype='purchaseticket' THEN 0.13
			WHEN ttt.transactiontype='prizepayment' THEN 1.5
			WHEN ttt.transactiontype='topupwallet' THEN 0
			WHEN ttt.transactiontype='purchaseticket_mm' THEN 0
			WHEN ttt.transactiontype='prizepayment_mm' THEN 0
			END AS margen,
			CASE	
			WHEN ttt.transactiontype='purchaseticket' THEN FORMAT( tt.amount * 0.13,2)
			WHEN ttt.transactiontype='prizepayment' THEN 1.5
			WHEN ttt.transactiontype='topupwallet' THEN 0
			WHEN ttt.transactiontype='purchaseticket_mm' THEN 0
			WHEN ttt.transactiontype='prizepayment_mm' THEN 0
			END AS comision,
			DAY(tt.date) AS dia,
			(CASE MONTH(tt.date)
				WHEN 1 THEN 'Enero'
				WHEN 2 THEN 'Febrero'
				WHEN 3 THEN 'Marzo'
				WHEN 4 THEN 'Abril'
				WHEN 5 THEN 'Mayo'
				WHEN 6 THEN 'Junio'
				WHEN 7 THEN 'Julio'
				WHEN 8 THEN 'Agosto'
				WHEN 9 THEN 'Septiembre'
				WHEN 10 THEN 'Octubre'
				WHEN 11 THEN 'Noviembre'
				WHEN 12 THEN 'Diciembre'
			END) AS mes,
			YEAR(tt.date) AS anio,WEEK(tt.date)AS semana,
			IF ((LEFT(upper(replace(l.nombre,' ', '')),5))= 'TELEV','Teleservicios','Tiendas') AS sub_canal,
			IFNULL(tt.numlines, 0) numlines,
			IFNULL(tt.numdraws, 0) numdraws,
			CASE
				WHEN tt.status = 1 THEN 'Activo'
				WHEN tt.status = 0 THEN 'Inactivo'
			END AS estado_nombre
		FROM
			tbl_torito_transaccion tt
			JOIN tbl_usuarios u ON u.id = tt.user_id
			JOIN tbl_personal_apt pl ON pl.id = u.personal_id
			LEFT JOIN tbl_locales l ON l.cc_id = tt.cc_id
			LEFT JOIN tbl_locales_redes lr on lr.id=l.red_id
			LEFT JOIN tbl_zonas AS z ON z.id = l.zona_id
			INNER JOIN tbl_torito_tipo_transaccion AS ttt ON ttt.id = tt.id_torito_tipo_transaccion
			LEFT JOIN tbl_televentas_clientes_transaccion tct ON tct.api_id = 9 and tct.txn_id = tt.transactionid
			LEFT JOIN tbl_televentas_clientes tc ON tc.id = tct.cliente_id
		WHERE 1
		".$where_estado ."
		".$where_fecha_inicio ."
		".$where_fecha_fin ."
		".$where_tipo_txn ."
		".$where_txn_id ."
		".$where_cajero ."
		".$where_red."
		".$where_local ."
		$locales_command_where
		ORDER BY tt.id ASC
	";
	//$result["consulta_query"] = $query_1;
	$list_query=$mysqli->query($query_1);
	if ($mysqli->error) {
		$result["http_code"] = 400;
		$result["consulta_query"] = $query_1;
		$result["error"] = 'Export error: ' . $mysqli->error;
		echo json_encode($result);
		exit;
	} else {
		$result_data=array();
		$venta_total=0;
		$pago_total=0;
		while ($li=$list_query->fetch_assoc()) {
			$result_data[]=$li;
			if($li['tipo_transaccion']==='VENTA GN'){
				$venta_total+=$li['monto'];
			}
			if($li['tipo_transaccion']==='PAGO GN'){
				$pago_total+=$li['monto'];
			}
		}

		if (!$result_data) {
			echo json_encode([
				"error" => "Export error"
			]);
			exit;
		}

		$result_totales=array();
		$result_totales['fecha']='';
		$result_totales['hora']='';
		$result_totales['nombre_red']='';
		$result_totales['cod_local']='';
		$result_totales['nombre_local']='';
		$result_totales['nombre_cajero']='';
		$result_totales['num_doc']='';
		$result_totales['cliente']='';
		$result_totales['tipo_transaccion']='VENTA TOTAL';
		$result_totales['txn_id']='';
		$result_totales['monto']=$venta_total;
		$result_totales['margen']='';
		$result_totales['comision']='';
		$result_totales['dia']='';
		$result_totales['mes']='';
		$result_totales['anio']='';
		$result_totales['semana']='';
		$result_totales['sub_canal']='';
		$result_totales['numlines']='';
		$result_totales['numdraws']='';
		$result_totales['estado_nombre']='';
		$result_data[]=$result_totales;

		$result_totales=array();
		$result_totales['fecha']='';
		$result_totales['hora']='';
		$result_totales['nombre_red']='';
		$result_totales['cod_local']='';
		$result_totales['nombre_local']='';
		$result_totales['nombre_cajero']='';
		$result_totales['num_doc']='';
		$result_totales['cliente']='';
		$result_totales['tipo_transaccion']='PAGO TOTAL';
		$result_totales['txn_id']='';
		$result_totales['monto']=$pago_total;
		$result_totales['margen']='';
		$result_totales['comision']='';
		$result_totales['dia']='';
		$result_totales['mes']='';
		$result_totales['anio']='';
		$result_totales['semana']='';
		$result_totales['sub_canal']='';
		$result_totales['numlines']='';
		$result_totales['numdraws']='';
		$result_totales['estado_nombre']='';
		$result_data[]=$result_totales;

		$headers = [
			"fecha" => "Fecha",
			"hora" => "Hora",
			"nombre_red" => "Red",
			"cod_local" => "C.C.",
			"nombre_local" => "Local",
			"nombre_cajero" => "Cajero",
			"num_doc" => "Num Doc",
			"cliente" => "Cliente",
			"tipo_transaccion" => "Tipo",
			"txn_id" => "Transacción ID",
			"monto" => "Monto",
			"margen" => "Margen",
			"comision"=> "Comisión",
			"dia"=> "Día",
			"mes"=> "Mes",
			"anio"=> "Año",
			"semana"=> "Semana",
			"sub_canal"=> "Sub_canal",
			"numlines"=> "Num_lineas",
			"numdraws"=> "Num_Sorteos",
			"estado_nombre"=> "Estado"
		];
		array_unshift($result_data, $headers);


		ini_set('display_errors', 0);
		ini_set('display_startup_errors', 0);
		error_reporting(0);

		require_once '../phpexcel/classes/PHPExcel.php';
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getActiveSheet()->fromArray($result_data, null, 'A1');
		$date = new DateTime();
		$file_title = "reporte_torito_" . $date->getTimestamp() . "_" . $usuario_id;

		if (!file_exists('/var/www/html/export/files_exported/reporte_torito/')) {
			mkdir('/var/www/html/export/files_exported/reporte_torito/', 0777, true);
		}

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . $file_title . '.xls"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$excel_path = '/var/www/html/export/files_exported/reporte_torito/' . $file_title . '.xls';
		$excel_path_download = '/export/files_exported/reporte_torito/' . $file_title . '.xls';
		$url = $file_title . '.xls';
		try {
			$objWriter->save($excel_path);
		} catch (PHPExcel_Writer_Exception $e) {
			echo json_encode(["error" => $e]);
			exit;
		}

		$insert_cmd = "INSERT INTO tbl_exported_files (url,tipo,ext,size,fecha_registro,usuario_id)";
		$insert_cmd .= " VALUES ('" . $url . "','excel','xls','" . filesize($excel_path) . "','" . date("Y-m-d h:i:s") . "','" . $login["id"] . "')";
		$mysqli->query($insert_cmd);

		echo json_encode(array(
			"path" => $excel_path_download,
			"url" => $file_title . '.xls',
			"tipo" => "excel",
			"ext" => "xls",
			"size" => filesize($excel_path),
			"fecha_registro" => date("d-m-Y h:i:s"),
			"sql" => $insert_cmd
		));
		exit;
	}


}elseif (isset($_POST["set_status_transaccion"])) {

    if (!isset($_POST["transaccion_id"], $_POST["new_status"], $_POST["old_status"])) {
        echo json_encode(['status' => 400, 'message' => 'Datos incompletos']);
        exit();
    }
    $transaccion_id = (int)$_POST["transaccion_id"];
    $new_status = (int)$_POST["new_status"];
    $old_status = (int)$_POST["old_status"];
    $id_usuario = (int)$login['id'];

    $result = [];

    $result_state = $new_status ? 'activó' : 'desactivó';

    $query_update = "UPDATE tbl_torito_transaccion SET status = $new_status WHERE id = $transaccion_id";
    $update_historico = $mysqli->query($query_update);

    if ($update_historico) {
		
        $query_insert = "INSERT INTO tbl_torito_transaccion_historial_cambios (
                            torito_transaccion_id, 
                            valor_anterior,
                            valor_nuevo,
                            status,
                            id_usuario,
                            created_at
                         ) VALUES (
                            $transaccion_id,
                            $old_status,
                            $new_status,
                            1,
                            $id_usuario,
                            NOW()
                         )";

        $insert_transaccion = $mysqli->query($query_insert);

        if ($insert_transaccion) {
            $result['status'] = 200;
            $result['message'] = "Se $result_state correctamente la transacción.";
        } else {
            $result['status'] = 500;
            $result['message'] = "Ocurrió un error al registrar el historial.";
        }

    } else {
        $result['status'] = 500;
        $result['message'] = "Ocurrió un error al actualizar la transacción.";
    }

    echo json_encode($result);
    exit();
}elseif (isset($_POST["accion"]) && $_POST["accion"] === "get_historico_transaccion_cambios") {
    
	$transaccion_id = $_POST['transaccion_id'];
    
    $query = "
        SELECT
        t2.transactionid,
        t1.valor_anterior,
		t1.valor_nuevo,
        DATE_FORMAT(t1.created_at, '%d/%m/%Y %H:%i:%s') AS created_at,
        t3.usuario
        FROM tbl_torito_transaccion_historial_cambios t1
        INNER JOIN tbl_torito_transaccion t2 ON t2.id = t1.torito_transaccion_id
        INNER JOIN tbl_usuarios t3 ON t3.id = t1.id_usuario
        WHERE t2.id = ?
		ORDER BY t1.created_at DESC;";
    
    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        error_log("Error preparando la consulta: " . $mysqli->error);
        echo json_encode([
            "sEcho" => 1,
            "iTotalRecords" => 0,
            "iTotalDisplayRecords" => 0,
            "aaData" => []
        ]);
        exit;
    }
    
    $stmt->bind_param("i", $transaccion_id);
    $stmt->execute();
    $list_query = $stmt->get_result();
    $stmt->close();

    $data = [];

    if ($mysqli->error) {
        error_log("Error en la consulta: " . $mysqli->error);
        $data[] = [
            "0" => "error",
            "1" => 'Comunicarse con Soporte, error: ' . $mysqli->error,
            "2" => '',
            "3" => '',
            "4" => ''
        ];
        $resultado = [
            "sEcho" => 1,
            "iTotalRecords" => 1,
            "iTotalDisplayRecords" => 1,
            "aaData" => $data
        ];
    } else {
        $cont = 1;
        while ($reg = $list_query->fetch_assoc()) {
			$valor_anterior = ($reg['valor_anterior'] == 1) ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
			$valor_nuevo = ($reg['valor_nuevo'] == 1) ? '<span class="badge bg-success">Activo</span>' : '<span class="badge bg-danger">Inactivo</span>';
            $data[] = [
                "0" => $cont,
                "1" => $reg['transactionid'],
                "2" => $valor_anterior,
                "3" => $valor_nuevo,
                "4" => $reg['created_at'],
                "5" => $reg['usuario']
            ];
            $cont++;
        }
        $resultado = [
            "sEcho" => 1,
            "iTotalRecords" => count($data),
            "iTotalDisplayRecords" => count($data),
            "aaData" => $data
        ];
    }
    echo json_encode($resultado);
    exit;
}

echo json_encode($result);

?>