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
if (isset($_POST["accion"]) && $_POST["accion"]==="listar_transacciones_balance") {
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;


	$busqueda_fecha_inicio=$_POST["fecha_inicio"];
	$busqueda_fecha_fin=$_POST["fecha_fin"];
	$busqueda_tipo_transaccion=$_POST["tipo_transaccion"];
	$busqueda_cajero=$_POST["cajero"];
	$busqueda_cliente=$_POST["cliente"];

	$where_users_test="";
	if((int)$area_id!==6){
		$where_users_test="	
		AND tra.web_id not in ('3333200', '71938219') 
		AND tra.user_id not in (1, 249, 250, 2572, 3028) 
		";
		if((int)$busqueda_tipo_transaccion===1){
			$where_users_test="	
				AND tra.user_id not in (1, 249, 250, 2572, 3028) 
			";
		}
	}



	$where_fecha_inicio=" AND DATE(tra.created_at)>= '".$busqueda_fecha_inicio."'";
	$where_fecha_fin=" AND DATE(tra.created_at)<= '".$busqueda_fecha_fin."'";
	$where_tipo_transaccion="";
	if( (int) $busqueda_tipo_transaccion > 0 ){
		$where_tipo_transaccion=" AND tra.tipo_id='".$busqueda_tipo_transaccion."' ";
	}

	$where_cajero="";
	if( (int) $cargo_id === 5 ){
		$where_cajero=" AND tra.user_id='".$usuario_id."' ";
	} else {
		if( (int) $busqueda_cajero > 0 ){
			$where_cajero=" AND tra.user_id='".$busqueda_cajero."' ";
		}
	}

	$where_cliente="";
	if( (int) $busqueda_cliente > 0 ){
		$where_cliente=" AND tra.cliente_id='".$busqueda_cliente."' ";
	}

	$query_1 ="
		SELECT
			IFNULL(REPLACE(REPLACE(tra.observacion_cajero, '\n', ''), '\n', ''), '') observacion_cajero,
			IFNULL(REPLACE(REPLACE(tra.observacion_validador, '\n', ''), '\n', ''), '') observacion_validador,
			IFNULL(REPLACE(REPLACE(tra.observacion_supervisor, '\n', ''), '\n', ''), '') observacion_supervisor,
			tipo_rechazo_id, 
			update_user_at,
			tra.id,
			tra.cliente_id,
			IFNULL( tra.web_id, '' ) web_id,
			tra.tipo_id cod_tipo_transaccion,
			(CASE tra.tipo_id 
				WHEN '1' THEN 'DEPÓSITO' WHEN '2' THEN 'RECARGA WEB' WHEN '3' THEN 'RETONOR RECARGA WEB' ELSE 'NO DEFINIDO' 
			END ) AS tipo_transaccion,
			tra.created_at fecha_hora_registro,
			usu.usuario AS cajero,
			super.usuario AS supervisor,
			(CASE cli.tipo_doc 
				WHEN '0' THEN 'DNI' WHEN '8' THEN 'DNI' WHEN '1' THEN 'CARNÉ EXTRANJERÍA' WHEN '2' THEN 'PASAPORTE' ELSE 'NO DEFINIDO' 
			END) AS tipo_doc,
			IFNULL(cli.num_doc, '') num_doc,
			IFNULL(CONCAT( cli.nombre, ' ', IFNULL( cli.apellido_paterno, '' ), ' ', IFNULL( cli.apellido_materno, '' ) ), '') AS cliente,
			IFNULL(cli.telefono, '') telefono,
			tra.estado AS estado_id,
			IFNULL(cuen.cuenta_descripcion, '') AS cuenta,
			tra.monto AS monto,
			IFNULL(tra.bono_monto, 0) AS bono_monto,
			IFNULL(tra.total_recarga, 0) AS total_recarga
		FROM
			tbl_televentas_clientes_transaccion tra
			LEFT JOIN tbl_usuarios usu ON usu.id = tra.user_id
			LEFT JOIN tbl_usuarios super ON super.id = tra.update_user_id
			LEFT JOIN tbl_televentas_clientes cli ON cli.id = tra.cliente_id
			LEFT JOIN tbl_cuentas_apt cuen ON cuen.id = tra.cuenta_id
		WHERE
		tra.estado = 1 
		
		".$where_fecha_inicio ." 
		".$where_fecha_fin ." 
		".$where_tipo_transaccion ." 
		".$where_cajero ." 
		".$where_cliente ."
		ORDER BY tra.id ASC
	";
	//$result["consulta_query"] = $query_1;
	$list_query=$mysqli->query($query_1);
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

if (isset($_POST["accion"]) && $_POST["accion"]==="listar_transacciones_balance_v2") {
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;


	$busqueda_fecha_inicio=$_POST["fecha_inicio"];
	$busqueda_fecha_fin=$_POST["fecha_fin"];
	$busqueda_tipo_transaccion=$_POST["tipo_transaccion"];
	$busqueda_cajero=$_POST["cajero"];
	$busqueda_cliente=$_POST["cliente"];

	$where_users_test="";
	if((int)$area_id!==6){
		$where_users_test="	
		AND tra.web_id not in ('3333200', '71938219') 
		AND tra.user_id not in (1, 249, 250, 2572, 3028) 
		";
		if((int)$busqueda_tipo_transaccion===1){
			$where_users_test="	
				AND tra.user_id not in (1, 249, 250, 2572, 3028) 
			";
		}
	}
	$nombre_busqueda = "";
	if(isset($_POST["search"]["value"])){		
		$busqueda_nombre = $_POST["search"]["value"];
		if ($busqueda_nombre !="") {
			$nombre_busqueda = 'AND (cli.nombre LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.apellido_paterno LIKE "%'.$_POST["search"]["value"].'%"
			OR cli.num_doc LIKE "%'.$_POST["search"]["value"].'%"
			)';
		}else{
			$nombre_busqueda = "";
		}
	}
	$limit = "";
	if(isset($_POST["length"]))
	{
		if($_POST["length"] != -1){
			$limit = 'LIMIT ' . $_POST['start'] . ', ' . $_POST['length'];
		}	
	}


	$where_fecha_inicio=" AND DATE(tra.created_at)>= '".$busqueda_fecha_inicio."'";
	$where_fecha_fin=" AND DATE(tra.created_at)<= '".$busqueda_fecha_fin."'";
	$where_tipo_transaccion="";
	if( (int) $busqueda_tipo_transaccion > 0 ){
		$where_tipo_transaccion=" AND tra.tipo_id='".$busqueda_tipo_transaccion."' ";
	}

	$where_cajero="";
	if( (int) $cargo_id === 5 ){
		$where_cajero=" AND tra.user_id='".$usuario_id."' ";
	} else {
		if( (int) $busqueda_cajero > 0 ){
			$where_cajero=" AND tra.user_id='".$busqueda_cajero."' ";
		}
	}

	$where_cliente="";
	if( (int) $busqueda_cliente > 0 ){
		$where_cliente=" AND tra.cliente_id='".$busqueda_cliente."' ";
	}

	$query_1 ="
		SELECT
			IFNULL(REPLACE(REPLACE(tra.observacion_cajero, '\n', ''), '\n', ''), '') observacion_cajero,
			IFNULL(REPLACE(REPLACE(tra.observacion_validador, '\n', ''), '\n', ''), '') observacion_validador,
			IFNULL(REPLACE(REPLACE(tra.observacion_supervisor, '\n', ''), '\n', ''), '') observacion_supervisor,
			tipo_rechazo_id, 
			update_user_at,
			tra.id,
			tra.cliente_id,
			IFNULL( tra.web_id, '' ) web_id,
			tra.tipo_id cod_tipo_transaccion,
			(CASE tra.tipo_id 
				WHEN '1' THEN 'DEPÓSITO' WHEN '2' THEN 'RECARGA WEB' WHEN '3' THEN 'RETONOR RECARGA WEB' ELSE 'NO DEFINIDO' 
			END ) AS tipo_transaccion,
			tra.created_at fecha_hora_registro,
			usu.usuario AS cajero,
			super.usuario AS supervisor,
			(CASE cli.tipo_doc 
				WHEN '0' THEN 'DNI' WHEN '8' THEN 'DNI' WHEN '1' THEN 'CARNÉ EXTRANJERÍA' WHEN '2' THEN 'PASAPORTE' ELSE 'NO DEFINIDO' 
			END) AS tipo_doc,
			IFNULL(cli.num_doc, '') num_doc,
			IFNULL(CONCAT( cli.nombre, ' ', IFNULL( cli.apellido_paterno, '' ), ' ', IFNULL( cli.apellido_materno, '' ) ), '') AS cliente,
			IFNULL(cli.telefono, '') telefono,
			tra.estado AS estado_id,
			IFNULL(cuen.cuenta_descripcion, '') AS cuenta,
			tra.monto AS monto,
			IFNULL(tra.bono_monto, 0) AS bono_monto,
			IFNULL(tra.total_recarga, 0) AS total_recarga
		FROM
			tbl_televentas_clientes_transaccion tra
			LEFT JOIN tbl_usuarios usu ON usu.id = tra.user_id
			LEFT JOIN tbl_usuarios super ON super.id = tra.update_user_id
			LEFT JOIN tbl_televentas_clientes cli ON cli.id = tra.cliente_id
			LEFT JOIN tbl_cuentas_apt cuen ON cuen.id = tra.cuenta_id
		WHERE
		tra.estado = 1 
		".$where_users_test ." 
		".$where_fecha_inicio ." 
		".$where_fecha_fin ." 
		".$where_tipo_transaccion ." 
		".$where_cajero ." 
		".$where_cliente ."
		".$nombre_busqueda ."
		ORDER BY tra.id ASC
	".$limit;
	//$result["consulta_query"] = $query_1;
	$list_query=$mysqli->query($query_1);
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
		$result["data"] =$list_transaccion;
	} elseif(count($list_transaccion)>0){
		$total_registros = count_data_listar_transacciones_balance_v2(
			$where_users_test,
			$where_fecha_inicio,
			$where_fecha_fin,
			$where_tipo_transaccion,
			$where_cajero,
			$where_cliente,
			$nombre_busqueda);
	//	$result["draw"] =intval($_POST["draw"]);
		$result["recordsTotal"] =$total_registros;
		$result["recordsFiltered"] =$total_registros;
		$result["http_code"] = 200;
		$result["status"] ="ok";
		$result["data"] =$list_transaccion;
		//$result["login"]=$login;
	} else{
		$result["http_code"] = 400;
		$result["status"] ="Ocurrió un error al consultar transacciones.";
		$result["data"] =$list_transaccion;
	}

}

function count_data_listar_transacciones_balance_v2(
	$where_users_test,
	$where_fecha_inicio,
	$where_fecha_fin,
	$where_tipo_transaccion,
	$where_cajero,
	$where_cliente,
	$nombre_busqueda)
{
	
	global $mysqli;
	

	$query_1 ="
		SELECT
		count(*) AS cantidad
		FROM
			tbl_televentas_clientes_transaccion tra
			LEFT JOIN tbl_usuarios usu ON usu.id = tra.user_id
			LEFT JOIN tbl_usuarios super ON super.id = tra.update_user_id
			LEFT JOIN tbl_televentas_clientes cli ON cli.id = tra.cliente_id
			LEFT JOIN tbl_cuentas_apt cuen ON cuen.id = tra.cuenta_id
		WHERE
		tra.estado = 1 		
		".$where_users_test ." 
		".$where_fecha_inicio ." 
		".$where_fecha_fin ." 
		".$where_tipo_transaccion ." 
		".$where_cajero ." 
		".$where_cliente ."
		".$nombre_busqueda
	;
	//$result["consulta_query"] = $query_1;
	$list_query=$mysqli->query($query_1);
	$list_transaccion=array();
	while ($li=$list_query->fetch_assoc()) {
		$count=$li['cantidad'];
	}
	return (int)$count;
}


//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER TRANSACCIONES X CAJERO --> EXCEL
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"]==="listar_transacciones_export_xls") {
	global $mysqli;
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$busqueda_fecha_inicio=$_POST["fecha_inicio"];
	$busqueda_fecha_fin=$_POST["fecha_fin"];
	$busqueda_tipo_transaccion=$_POST["tipo_transaccion"];
	$busqueda_cajero=$_POST["cajero"];
	$busqueda_cliente=$_POST["cliente_id"];

	$where_users_test="";
	if((int)$area_id!==6){
		$where_users_test="	
		AND tra.web_id not in ('3333200', '71938219') 
		AND tra.user_id not in (1, 249, 250, 2572, 3028) 
		";
		if((int)$busqueda_tipo_transaccion===1){
			$where_users_test="	
				AND tra.user_id not in (1, 249, 250, 2572, 3028) 
			";
		}
	}

	$busqueda_fecha_inicio=$_POST["fecha_inicio"];
	$busqueda_fecha_fin=$_POST["fecha_fin"];
	$busqueda_tipo_transaccion=$_POST["tipo_transaccion"];
	$busqueda_cajero=$_POST["cajero"];
	$busqueda_cliente=$_POST["cliente_id"];

	$where_fecha_inicio=" AND DATE(tra.created_at)>= '".$busqueda_fecha_inicio."'";
	$where_fecha_fin=" AND DATE(tra.created_at)<= '".$busqueda_fecha_fin."'";

	$where_tipo_transaccion="";
	if( (int) $busqueda_tipo_transaccion > 0 ){
		$where_tipo_transaccion=" AND tra.tipo_id='".$busqueda_tipo_transaccion."' ";
	}
	
	$where_cajero="";
	if( (int) $cargo_id === 5 ){
		$where_cajero=" AND tra.user_id='".$usuario_id."' ";
	} else {
		if( (int) $busqueda_cajero > 0 ){
			$where_cajero=" AND tra.user_id='".$busqueda_cajero."' ";
		}
	}

	$where_cliente="";
	if( (int) $busqueda_cliente > 0 ){
		$where_cliente=" AND tra.cliente_id='".$busqueda_cliente."' ";
	}

	$query_1 ="
		SELECT
			(CASE tra.tipo_id WHEN '1' THEN 'DEPÓSITO' WHEN '2' THEN 'RECARGA WEB' WHEN '3' THEN 'APUESTA' ELSE 'NO DEFINIDO' END ) AS tipo_transaccion,
			tra.created_at fecha_hora_registro,
			usu.usuario AS cajero,
			IFNULL(cli.telefono, '') telefono,
			(CASE cli.tipo_doc WHEN '0' THEN 'DNI' WHEN '8' THEN 'DNI' WHEN '1' THEN 'CARNÉ EXTRANJERÍA' WHEN '2' THEN 'PASAPORTE' ELSE 'NO DEFINIDO' END) AS tipo_doc,
			IFNULL(cli.num_doc, '') num_doc,
			IFNULL( tra.web_id, '' ) web_id,
			IFNULL(CONCAT( cli.nombre, ' ', IFNULL( cli.apellido_paterno, '' ), ' ', IFNULL( cli.apellido_materno, '' ) ), '') AS cliente,
			IFNULL(cuen.cuenta_descripcion, '') AS cuenta,
			tra.monto AS monto,
			IFNULL(tra.bono_monto, 0) AS bono_monto,
			IFNULL(tra.total_recarga, 0) AS total_recarga
		FROM
			tbl_televentas_clientes_transaccion tra
			LEFT JOIN tbl_usuarios usu ON usu.id = tra.user_id
			LEFT JOIN tbl_televentas_clientes cli ON cli.id = tra.cliente_id
			LEFT JOIN tbl_cuentas_apt cuen ON cuen.id = tra.cuenta_id
		WHERE
		tra.estado = 1 
		".$where_users_test ." 
		".$where_fecha_inicio ." 
		".$where_fecha_fin ." 
		".$where_tipo_transaccion ." 
		".$where_cajero ." 
		".$where_cliente ."
		ORDER BY tra.id ASC
	";
	//$result["consulta_query"] = $query_1;
	$list_query=$mysqli->query($query_1);
	$result_data=array();
	$monto_total=0;
	$recarga_total=0;
	while ($li=$list_query->fetch_assoc()) {
		$result_data[]=$li;
		$monto_total+=$li['monto'];
		$recarga_total+=$li['total_recarga'];
	}


	if ($mysqli->error) {
		echo json_encode([
			"error" => "Export error"
		]);
		exit;
	}else {

		$result_totales=array();
		$result_totales['tipo_transaccion']='';
		$result_totales['fecha_hora_registro']='';
		$result_totales['cajero']='';
		$result_totales['telefono']='';
		$result_totales['tipo_doc']='';
		$result_totales['num_doc']='';
		$result_totales['web_id']='';
		$result_totales['cliente']='';
		$result_totales['cuenta']='';
		$result_totales['monto']=$monto_total;
		$result_totales['bono_monto']='';
		$result_totales['total_recarga']=$recarga_total;

		$result_data[]=$result_totales;

		$headers = [
			"tipo_transaccion" => "Tipo Transaccion",
			"fecha_hora_registro" => "Fecha y Hora Registro",
			"cajero" => "Usuario",
			"telefono" => "Telefono",
			"tipo_doc" => "Tipo de Documento",
			"num_doc" => "Numero de Documento",
			"web_id" => "WEB-ID",
			"cliente" => "Nombre de Cliente",
			"cuenta" => "Cuenta",
			"monto" => "Monto",
			"bono_monto" => "Bono",
			"total_recarga" => "Recarga"
		];
		array_unshift($result_data, $headers);
		
		ini_set('display_errors', 0);
		ini_set('display_startup_errors', 0);
		error_reporting(0);

		require_once '../phpexcel/classes/PHPExcel.php';
		$objPHPExcel = new PHPExcel();
		$objPHPExcel->getActiveSheet()->fromArray($result_data, null, 'A1');
		$date = new DateTime();
		$file_title = "reporte_televentas_" . $date->getTimestamp();

		if (!file_exists('/var/www/html/export/files_exported/reporte_premios/')) {
			mkdir('/var/www/html/export/files_exported/reporte_premios/', 0777, true);
		}

		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment;filename="' . $file_title . '.xls"');
		header('Cache-Control: max-age=0');

		$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
		$excel_path = '/var/www/html/export/files_exported/reporte_premios/' . $file_title . '.xls';
		$excel_path_download = '/export/files_exported/reporte_premios/' . $file_title . '.xls';
		$url = $file_title . '.xls';

		try {
			$objWriter->save($excel_path);
		} catch (PHPExcel_Writer_Exception $e) {
			echo json_encode(["error" => $e, "query" => $query_1]);
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

	

}













//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER TRANSACCIONES X CLIENTE
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"]==="listar_resumen_x_cliente") {
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$where_users_test="";
	if((int)$area_id!==6){
		$where_users_test="	
		AND IFNULL(tct.web_id, '') not in ('3333200', '71938219') 
		AND IFNULL(tct.user_id, '') not in (1, 249, 250, 2572, 3028) 
		";
	}


	$busqueda_fecha_inicio=$_POST["fecha_inicio"];
	$busqueda_fecha_fin=$_POST["fecha_fin"];

	$where_fecha_inicio=" AND DATE(tct.created_at)>= '".$busqueda_fecha_inicio."'";
	$where_fecha_fin=" AND DATE(tct.created_at)<= '".$busqueda_fecha_fin."'";

	$query_1 ="
		SELECT
			tc.created_at fecha_hora_registro,
			IFNULL( IF ( tc.cc_id = 3900, 'TELEVENTAS', l.nombre ), '' ) local_nombre,
			IFNULL( tc.web_id, '' ) web_id,
			IFNULL( tc.telefono, '' ) telefono,
			( CASE tc.tipo_doc WHEN '0' THEN 'DNI' WHEN '8' THEN 'DNI' WHEN '1' THEN 'CARNÉ EXTRANJERÍA' WHEN '2' THEN 'PASAPORTE' ELSE '' END ) AS tipo_doc,
			IFNULL( tc.num_doc, '' ) num_doc,
			UPPER(
				IFNULL(
					CONCAT( tc.nombre, ' ', IFNULL( tc.apellido_paterno, '' ), ' ', IFNULL( tc.apellido_materno, '' ) ),
					'' 
				) 
			) AS cliente,
			IFNULL( SUM( A.monto_deposito ), 0 ) total_deposito,
			IFNULL( SUM( A.monto_bono ), 0 ) total_bono,
			IFNULL( SUM( A.monto_recarga ), 0 ) total_recarga,
			IFNULL( SUM( A.cont_deposito ), 0 ) cont_deposito,
			IFNULL( SUM( A.cont_recarga ), 0 ) cont_recarga,
			IFNULL( SUM( A.cont_bono ), 0 ) cont_bono,
			tcb.balance
		FROM
			tbl_televentas_clientes tc
			LEFT JOIN tbl_locales l ON l.cc_id = tc.cc_id
			JOIN tbl_televentas_clientes_balance tcb ON tcb.cliente_id=tc.id
			LEFT JOIN (
			SELECT
				tct.cliente_id,
				tct.monto monto_deposito,
			IF
				( tct.bono_id > 0, tct.bono_monto, 0 ) monto_bono,
			IF
				( tct.tipo_id = 2, tct.total_recarga, 0 ) monto_recarga,
			IF
				( tct.tipo_id = 1, 1, 0 ) cont_deposito,
			IF
				( tct.tipo_id = 2, 1, 0 ) cont_recarga,
			IF
				( tct.bono_id > 0, 1, 0 ) cont_bono 
			FROM
				tbl_televentas_clientes_transaccion tct 
			WHERE
				tct.estado = 1 
				".$where_users_test ." 
				".$where_fecha_inicio ." 
				".$where_fecha_fin ." 
			) A ON A.cliente_id = tc.id 
		WHERE
			DATE( tc.created_at ) > '2021-08-01' 
		GROUP BY
			tc.id,
			tc.created_at,
			tc.web_id,
			tc.telefono,
			tc.tipo_doc,
			tc.num_doc,
			tc.nombre,
			tc.apellido_paterno,
			tc.apellido_materno
		ORDER BY 
			tc.created_at ASC,
			tc.nombre ASC,
			tc.apellido_paterno ASC,
			tc.apellido_materno ASC
		";
	//$result["consulta_query"] = $query_1;
	$list_query=$mysqli->query($query_1);
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
	} else{
		$result["http_code"] = 400;
		$result["status"] ="Ocurrió un error al consultar transacciones.";
	}

}


//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER TRANSACCIONES X CLIENTE --> EXCEL
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"]==="listar_resumen_x_cliente_export_xls") {
	global $mysqli;
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$where_users_test="";
	if((int)$area_id!==6){
		$where_users_test="	
		AND IFNULL(tct.web_id, '') not in ('3333200', '71938219') 
		AND IFNULL(tct.user_id, '') not in (1, 249, 250, 2572, 3028) 
		";
	}

	$busqueda_fecha_inicio=$_POST["fecha_inicio"];
	$busqueda_fecha_fin=$_POST["fecha_fin"];

	$where_fecha_inicio=" AND DATE(tct.created_at)>= '".$busqueda_fecha_inicio."'";
	$where_fecha_fin=" AND DATE(tct.created_at)<= '".$busqueda_fecha_fin."'";

	$query_1 ="
		SELECT
			tc.created_at fecha_hora_registro,
			IFNULL( IF ( tc.cc_id = 3900, 'TELEVENTAS', l.nombre ), '' ) local_nombre,
			IFNULL( tc.web_id, '' ) web_id,
			IFNULL( tc.telefono, '' ) telefono,
			(CASE tc.tipo_doc 
				WHEN '0' THEN 'DNI' 
				WHEN '8' THEN 'DNI' 
				WHEN '1' THEN 'CARNÉ EXTRANJERÍA' 
				WHEN '2' THEN 'PASAPORTE' 
				ELSE '' 
			END ) AS tipo_doc,
			IFNULL( tc.num_doc, '' ) num_doc,
			UPPER(TRIM(CONCAT(
				IFNULL( tc.nombre, ''), ' ', 
				IFNULL( tc.apellido_paterno, '' ), ' ', 
				IFNULL( tc.apellido_materno, '' )
				))) AS cliente,
			IFNULL( SUM( A.monto_deposito ), 0 ) total_deposito,
			IFNULL( SUM( A.monto_bono ), 0 ) total_bono,
			IFNULL( SUM( A.monto_recarga ), 0 ) total_recarga,
			IFNULL( SUM( A.cont_deposito ), 0 ) cont_deposito,
			IFNULL( SUM( A.cont_recarga ), 0 ) cont_recarga,
			IFNULL( SUM( A.cont_bono ), 0 ) cont_bono ,
			tcb.balance
		FROM
			tbl_televentas_clientes tc
			LEFT JOIN tbl_locales l ON l.cc_id = tc.cc_id
			JOIN tbl_televentas_clientes_balance tcb ON tcb.cliente_id=tc.id
			LEFT JOIN (
			SELECT
				tct.cliente_id,
				tct.monto monto_deposito,
			IF ( tct.bono_id > 0, tct.bono_monto, 0 ) monto_bono,
			IF ( tct.tipo_id = 2, tct.total_recarga, 0 ) monto_recarga,
			IF ( tct.tipo_id = 1, 1, 0 ) cont_deposito,
			IF ( tct.tipo_id = 2, 1, 0 ) cont_recarga,
			IF ( tct.bono_id > 0, 1, 0 ) cont_bono 
			FROM
				tbl_televentas_clientes_transaccion tct 
			WHERE
				tct.estado = 1 
				".$where_users_test ." 
				".$where_fecha_inicio ." 
				".$where_fecha_fin ." 
			) A ON A.cliente_id = tc.id 
		WHERE
			DATE( tc.created_at ) > '2021-08-01' 
		GROUP BY
			tc.id,
			tc.created_at,
			tc.web_id,
			tc.telefono,
			tc.tipo_doc,
			tc.num_doc,
			tc.nombre,
			tc.apellido_paterno,
			tc.apellido_materno
		ORDER BY 
			tc.created_at ASC,
			tc.nombre ASC,
			tc.apellido_paterno ASC,
			tc.apellido_materno ASC
		";
	$list_query=$mysqli->query($query_1);
	$result_data=array();
	while ($li=$list_query->fetch_assoc()) {
		$result_data[]=$li;
	}

	if (!$result_data) {
		echo json_encode([
			"error" => "Export error"
		]);
		exit;
	}

	$headers = [
		"fecha_hora_registro" => "Registro",
		"local_nombre" => "Local",
		"web_id" => "WEN-ID",
		"telefono" => "Telefono",
		"tipo_doc" => "Tipo de Documento",
		"num_doc" => "Numero de Documento",
		"cliente" => "Nombre de Cliente",
		"total_deposito" => "Total Monto",
		"total_bono" => "Total Bono",
		"total_recarga" => "Total Recarga",
		"cont_deposito" => "Cantidad Depositos",
		"cont_bono" => "Cantidad Bonos",
		"cont_recarga" => "Cantidad Recargas",
		"balance" => "Balance"
	];
	array_unshift($result_data, $headers);

	require_once '../phpexcel/classes/PHPExcel.php';
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getActiveSheet()->fromArray($result_data, null, 'A1');
	$date = new DateTime();
	$file_title = "reporte_televentas_clientes_" . $date->getTimestamp();

	if (!file_exists('/var/www/html/export/files_exported/reporte_premios/')) {
		mkdir('/var/www/html/export/files_exported/reporte_premios/', 0777, true);
	}

	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="' . $file_title . '.xls"');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$excel_path = '/var/www/html/export/files_exported/reporte_premios/' . $file_title . '.xls';
	$excel_path_download = '/export/files_exported/reporte_premios/' . $file_title . '.xls';
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






echo json_encode($result);

?>