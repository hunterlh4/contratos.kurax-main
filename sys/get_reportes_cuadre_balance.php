<?php
$result=array();
include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER REGISTROS
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"]==="SecRptCBal_listar_registros") {
	
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$fecha_inicio              = $_POST["fecha_inicio"];
	$fecha_fin                 = $_POST["fecha_fin"];
	$cliente_tipo              = $_POST["cliente_tipo"];
	$cliente_texto             = $_POST["cliente_texto"];

	$where_cli = "";
	$where_cliente = "";
	$where_cliente_balances = "";
	if(strlen($cliente_texto) > 1){
		if((int)$cliente_tipo === 1){ // Por nombres
			$where_cli = " WHERE CONCAT( IFNULL(cli.nombre, ''), ' ', IFNULL(cli.apellido_paterno, ''), ' ', IFNULL(cli.apellido_materno, '') ) LIKE '%". $cliente_texto ."%' ";
		}
		if((int)$cliente_tipo === 2){ // Por num doc
			$where_cli = " WHERE IFNULL(cli.num_doc, '') = '". $cliente_texto ."' ";
		}
		if((int)$cliente_tipo === 3){ // Por web-id
			$where_cli = " WHERE IFNULL(cli.web_id, '') = '". $cliente_texto ."' ";
		}
		if((int)$cliente_tipo === 4){ // Por celular
			$where_cli = " WHERE IFNULL(cli.telefono, '') = '". $cliente_texto ."' ";
		}
		$query_busq_cli ="
			SELECT 
				cli.id 
			FROM  tbl_televentas_clientes cli
			$where_cli
			";

		$list_query_cli = $mysqli->query($query_busq_cli);
		$list_cli = array();
		if($mysqli->error){
			$result["consulta_error_cli"] = $mysqli->error;
		} else {
			while ($li_cli=$list_query_cli->fetch_assoc()) {
				$list_cli[]=$li_cli["id"];
			}
		}
		if(!empty($list_cli)){ 
			$l_cli = implode(",", $list_cli);
			$where_cliente = " AND tra.cliente_id IN (". $l_cli .") ";  
			$where_cliente_balances = " AND cliente_id IN (". $l_cli .") ";  
		}
	}

	$where_fecha = " tm.fecha >= '" . $fecha_inicio . "'";
	$where_fecha .= " AND tm.fecha <= '" . $fecha_fin . "'";

	// MONTOS POR TIPO DE TRANSACCIÓN
	$query_1 ="
		SELECT
			tm.fecha,
			tm.tipo_id,
			tt.nombre,
			tt.descripcion,
			IFNULL(SUM(tm.cant), 0) cant,
			IFNULL(SUM(tm.monto), 0) monto
		FROM tbl_televentas_res_fecha_tipo_monto tm
		INNER JOIN tbl_televentas_clientes_tipo_transaccion tt ON tt.id = tm.tipo_id
		WHERE 
			$where_fecha 
		GROUP BY tm.tipo_id
		ORDER BY tm.tipo_id
		";
	// $result["por_tipo"] = $query_1;
	$list_query = $mysqli->query($query_1);
	$list_transaccion = array();
	if($mysqli->error){
		$result["consulta_error"] = $mysqli->error;
	} else {
		while ($li=$list_query->fetch_assoc()) {
			$list_transaccion[]=$li;
		}
	}

	//BALANCE INICIAL
	$balance_inicial = 0;
	$cmd_get_transactions = "
		SELECT
			IFNULL(SUM(cb.balance), 0) as balance_inicial
		FROM tbl_televentas_res_fecha_cliente_balance cb
		LEFT JOIN tbl_televentas_clientes cli ON cb.cliente_id = cli.id
		WHERE 
			cb.fecha = DATE_SUB('" . $fecha_inicio . "', INTERVAL 1 DAY)
			AND LENGTH(cli.num_doc) != 4
	";
	// $result["bal_ini"] = $cmd_get_transactions;
	$res_transactions = $mysqli->query($cmd_get_transactions);
	if($mysqli->error){
		$result["cmd_get_transactions_error"] = $mysqli->error;
		$result["query"] = $cmd_get_transactions;
	} else {
		while ($li=$res_transactions->fetch_assoc()) {
			$balance_inicial=$li["balance_inicial"];
		}
	}

	//BALANCE CIERRE
	$balance_cierre = 0;
	$cmd_get_transactions = "
		SELECT
			IFNULL(SUM(cb.balance), 0) as balance_cierre
		FROM tbl_televentas_res_fecha_cliente_balance cb 
		LEFT JOIN tbl_televentas_clientes cli ON cb.cliente_id = cli.id
		WHERE cb.fecha = '" . $fecha_fin . "'
		AND LENGTH(cli.num_doc) != 4
	";
	// $result["bal_cierre"] = $cmd_get_transactions;
	$res_transactions = $mysqli->query($cmd_get_transactions);
	if($mysqli->error){
		$result["cmd_get_transactions_error"] = $mysqli->error;
		$result["query"] = $cmd_get_transactions;
	} else {
		while ($li=$res_transactions->fetch_assoc()) {
			$balance_cierre=$li["balance_cierre"];
		}
	}

	if(count($list_transaccion) === 0){
		$result["http_code"] = 400;
		$result["status"] ="No hay transacciones.";
		$result["result"] =$list_transaccion;
	} elseif(count($list_transaccion) > 0){
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["result"] = $list_transaccion;
		$result["balance_inicial"] = $balance_inicial;
		$result["balance_cierre"] = $balance_cierre;
		
		 
	} else {
		$result["http_code"] = 400;
		$result["status"] ="Ocurrió un error al consultar transacciones.";
		$result["result"] =$list_transaccion;
	}
}


//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER SALDO INICIAL - REGISTROS DE DIFERENCIAS
//*******************************************************************************************************************
//*******************************************************************************************************************

if (isset($_POST["accion"]) && $_POST["accion"]==="SecRptCBal_sal_ini_dif_reg") {
	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$fecha_inicio              = $_POST["fecha_inicio"];
	$fecha_fin                 = $_POST["fecha_fin"];
	$cliente_tipo              = $_POST["cliente_tipo"];
	$cliente_texto             = $_POST["cliente_texto"];

	$where_cli = "";
	$where_cliente = "";
	if(strlen($cliente_texto) > 1){
		if((int)$cliente_tipo === 1){ // Por nombres
			$where_cli = " WHERE CONCAT( IFNULL(cli.nombre, ''), ' ', IFNULL(cli.apellido_paterno, ''), ' ', IFNULL(cli.apellido_materno, '') ) LIKE '%". $cliente_texto ."%' ";
		}
		if((int)$cliente_tipo === 2){ // Por num doc
			$where_cli = " WHERE IFNULL(cli.num_doc, '') = '". $cliente_texto ."' ";
		}
		if((int)$cliente_tipo === 3){ // Por web-id
			$where_cli = " WHERE IFNULL(cli.web_id, '') = '". $cliente_texto ."' ";
		}
		if((int)$cliente_tipo === 4){ // Por celular
			$where_cli = " WHERE IFNULL(cli.telefono, '') = '". $cliente_texto ."' ";
		}
		$query_busq_cli ="
			SELECT 
				cli.id 
			FROM  tbl_televentas_clientes cli
			$where_cli
			";

		$list_query_cli = $mysqli->query($query_busq_cli);
		$list_cli = array();
		if($mysqli->error){
			$result["consulta_error_cli"] = $mysqli->error;
		} else {
			while ($li_cli=$list_query_cli->fetch_assoc()) {
				$list_cli[]=$li_cli["id"];
			}
		}
		if(!empty($list_cli)){ 
			$l_cli = implode(",", $list_cli);
			$where_cliente = " AND tra.cliente_id IN (". $l_cli .") ";  
		}
	}

	$where_fecha = " AND tra.created_at >= '" . $fecha_inicio . "'";
	$where_fecha .= " AND tra.created_at <= '" . $fecha_fin . "'";

	//INICIO
	$query_balances_inicio = "
		SELECT
			bt_1.cliente_id,
			bt_1.nuevo_balance 
		FROM tbl_televentas_clientes_transaccion bt_1
		INNER JOIN (
			SELECT
				MAX( btt.created_at ) max_date,
				btt.cliente_id 
			FROM
				tbl_televentas_clientes_transaccion btt
			WHERE
				IFNULL(btt.id_tipo_balance,0) != 6 and
				btt.created_at <= '" . $fecha_inicio . "' 
			GROUP BY
				btt.cliente_id 
		) bt_2 ON bt_2.max_date = bt_1.created_at AND bt_2.cliente_id = bt_1.cliente_id
		ORDER BY bt_1.cliente_id ASC, bt_1.id DESC
	";

	//$result["query_balances_inicio"] = $query_balances_inicio;
	$res_bal_inicio = $mysqli->query($query_balances_inicio);
	$list_balances_inicio = array();
	if($mysqli->error){
		$result["cmd_get_transactions_error"] = $mysqli->error;
	} else {
		$unique_bal_inicio_array = array();
		while ($li=$res_bal_inicio->fetch_assoc()) {
			$list_balances_inicio[]=$li;
			$client_id=$li["cliente_id"];
	        if (!array_key_exists($client_id, $unique_bal_inicio_array)) {
	            $unique_bal_inicio_array[$client_id] = $li;
	        }
		}
	}
	$result["list_balances_inicio"] = $list_balances_inicio;
	$result["unique_bal_inicio_array"] = $unique_bal_inicio_array;

}

//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER REGISTROS DE DIFERENCIAS
//*******************************************************************************************************************
//*******************************************************************************************************************

if (isset($_POST["accion"]) && $_POST["accion"]==="SecRptCBal_listar_diferencias_registros") {

	$fecha_inicio              = $_POST["fecha_inicio"];
	$fecha_fin                 = $_POST["fecha_fin"];
	$cliente_tipo              = $_POST["cliente_tipo"];
	$cliente_texto             = $_POST["cliente_texto"];

	$where_cli = "";
	$where_cliente = "";
	if(strlen($cliente_texto) > 1){
		if((int)$cliente_tipo === 1){ // Por nombres
			$where_cli = " WHERE CONCAT( IFNULL(cli.nombre, ''), ' ', IFNULL(cli.apellido_paterno, ''), ' ', IFNULL(cli.apellido_materno, '') ) LIKE '%". $cliente_texto ."%' ";
		}
		if((int)$cliente_tipo === 2){ // Por num doc
			$where_cli = " WHERE IFNULL(cli.num_doc, '') = '". $cliente_texto ."' ";
		}
		if((int)$cliente_tipo === 3){ // Por web-id
			$where_cli = " WHERE IFNULL(cli.web_id, '') = '". $cliente_texto ."' ";
		}
		if((int)$cliente_tipo === 4){ // Por celular
			$where_cli = " WHERE IFNULL(cli.telefono, '') = '". $cliente_texto ."' ";
		}
		$query_busq_cli ="
			SELECT 
				cli.id 
			FROM  tbl_televentas_clientes cli
			$where_cli
			";

		$list_query_cli = $mysqli->query($query_busq_cli);
		$list_cli = array();
		if($mysqli->error){
			$result["consulta_error_cli"] = $mysqli->error;
		} else {
			while ($li_cli=$list_query_cli->fetch_assoc()) {
				$list_cli[]=$li_cli["id"];
			}
		}
		if(!empty($list_cli)){ 
			$l_cli = implode(",", $list_cli);
			$where_cliente = " AND tra.cliente_id IN (". $l_cli .") ";  
		}
	}

	$where_fecha = " AND tra.created_at >= '" . $fecha_inicio . "'";
	$where_fecha .= " AND tra.created_at <= '" . $fecha_fin . "'";

	//INICIO
	$query_balances_inicio = "

		SELECT
			cb.cliente_id,
			cb.balance as nuevo_balance
		FROM tbl_televentas_res_fecha_cliente_balance cb
		LEFT JOIN tbl_televentas_clientes cli ON cb.cliente_id = cli.id
		WHERE cb.fecha = DATE_SUB('" . $fecha_inicio . "', INTERVAL 1 DAY)
		AND LENGTH(cli.num_doc) != 4
	";

	//$result["query_balances_inicio"] = $query_balances_inicio;
	$res_bal_inicio = $mysqli->query($query_balances_inicio);
	$list_balances_inicio = array();
	if($mysqli->error){
		$result["cmd_get_transactions_error"] = $mysqli->error;
	} else {
		$unique_bal_inicio_array = array();
		while ($li=$res_bal_inicio->fetch_assoc()) {
			$list_balances_inicio[]=$li;
			$client_id=$li["cliente_id"];
	        if (!array_key_exists($client_id, $unique_bal_inicio_array)) {
	            $unique_bal_inicio_array[$client_id] = $li;
	        }
		}
	}
	$result["list_balances_inicio"] = $list_balances_inicio;
	$result["unique_bal_inicio_array"] = $unique_bal_inicio_array;



	//CIERRE TEORICO
	$query_cierre_balance_teorico = "
		SELECT
			tra.cliente_id,
			SUM(IF(tra.tipo_id IN ( 1, 3, 26, 5, 12, 13, 14, 16, 17, 19, 20, 22, 25, 30, 31, 32, 34, 7 ), tra.monto, 0 )) - SUM(IF( tra.tipo_id IN ( 2, 4, 6, 9, 11, 15, 18, 21, 24, 28, 29, 33), tra.monto, 0 )) monto 
		FROM
			tbl_televentas_clientes_transaccion tra
			INNER JOIN tbl_televentas_clientes_tipo_transaccion tt ON tra.tipo_id = tt.id
			LEFT JOIN tbl_televentas_clientes cli ON tra.cliente_id = cli.id 
			LEFT JOIN tbl_televentas_clientes_transaccion tra2 ON tra.transaccion_id = tra2.id 
				AND tra2.tipo_id = 9
				AND IFNULL(tra2.id_tipo_balance,0) != 6
		WHERE
			IFNULL(tra.id_tipo_balance,0) != 6 and
			tra.created_at >= '" . $fecha_inicio . "' 
			AND tra.created_at <= '" . $fecha_fin . "' 
			AND (
				( tra.tipo_id = 1 AND tra.estado = 1 and tra.created_at < '	2022-10-14 16:42:22' ) /* depositos validados */
				OR (tra.tipo_id = 3) /* solicitud de recarga rollback */
				OR (tra.tipo_id = 26) /* depositos validados */
				OR (tra.tipo_id = 6 ) /* depositos rollback */
				OR (tra.tipo_id = 7 and tra.api_id = 4 ) /* Bingo Cancelado*/
				OR ( tra.tipo_id = 2 AND tra.estado = 1 ) /* recargas */
				OR ( tra.tipo_id IN ( 4, 5, 19, 20 ) AND tra.estado IN ( 1 ) ) /* apuestas, pagadas, retornadas, jackpot */
				OR (tra.tipo_id IN (4) and tra.estado in (1,3,4,5) and tra.api_id = 4) /* Todos los Bingos*/
				OR ( tra.tipo_id = 9 AND tra.estado IN ( 1, 3, 4, 5, 6 ) ) /* retiros: pendiente, en proceso, verificado */
				OR ( tra.tipo_id = 9 AND tra.estado = 2 AND tra.created_at >= '" . $fecha_inicio . "' AND tra.created_at <= '" . $fecha_fin . "' AND tra.updated_at > '" . $fecha_fin . "')
				OR ( tra.tipo_id = 11 AND tra.estado IN ( 2 ) AND tra2.created_at >= '" . $fecha_inicio . "' ) /* retiros: pagado */
				OR ( tra.tipo_id = 12 AND tra.estado IN ( 3 ) ) /* retiros: rechazado */
				OR ( tra.tipo_id = 13 AND tra.estado IN ( 4 ) ) /* retiros: cancelado */
				OR ( tra.tipo_id = 14 AND tra.estado IN ( 0, 1 ) ) /* terminal-deposit */
				OR ( tra.tipo_id IN ( 15, 16 ) ) /* cancer: donacion y correccion */
				OR ( tra.tipo_id IN ( 17, 18 ) ) /* subir y bajar balance */
				OR (tra.tipo_id = 21 and tra.estado IN (1,3,4,5,6))  and tra.tipo_rechazo_id is null  /* propinas: pendiente, en proceso, verificado */
				OR (tra.tipo_id = 24 and tra.estado IN (2)) /* propinas: pagado */
				OR (tra.tipo_id = 25 and tra.estado IN (3)) /* propinas: rechazado */
				OR (tra.tipo_id = 22 and tra.estado IN (4)) /* propinas: cancelado */
				OR (tra.tipo_id = 28 and tra.estado IN (1,3,4,5,6)) /* devolucion: pendiente, en proceso, verificado */
				OR (tra.tipo_id = 29 and tra.estado IN (2)) /* devolucion: pagado */
				OR (tra.tipo_id = 30 and tra.estado IN (3)) /* devolucion: rechazado */
				OR (tra.tipo_id = 31 and tra.estado IN (4)) /* devolucion: cancelado */
				OR (tra.tipo_id = 32 and tra.estado = 1) /* pago de premios */
				OR (tra.tipo_id = 33 and tra.estado = 1) /* terminal deposito tambo */
				OR (tra.tipo_id = 34 and tra.estado = 1) /* apuesta cancelada */
			) 
		GROUP BY
			tra.cliente_id 
	";

	//$result["query_cierre_balance_teorico"] = $query_cierre_balance_teorico;
	$res_bal_cierre_teorico = $mysqli->query($query_cierre_balance_teorico);
	$list_bal_cierre_teorico = array();
	if($mysqli->error){
		$result["cmd_get_transactions_error"] = $mysqli->error;
	} else {
		while ($li=$res_bal_cierre_teorico->fetch_assoc()) {
			$list_bal_cierre_teorico[]=$li;
		}
	}
	$result["list_bal_cierre_teorico"] = $list_bal_cierre_teorico;


	//BALANCE DESPUES
	$clientes_id_balance_inicio = array_column($list_balances_inicio, 'cliente_id');
	$clientes_id_cierre_teorico = array_column($list_bal_cierre_teorico, 'cliente_id');
	$all_cliente_ids = array_unique(array_merge($clientes_id_balance_inicio, $clientes_id_cierre_teorico));
	$balance_despues = [];
	foreach ($all_cliente_ids as $cliente_id) {
		$balance_despues_2 = 0;
		$balance_inicial = 0;
    	$monto = 0;

    	foreach ($list_balances_inicio as $elemento1) {
	        if ($elemento1['cliente_id'] === $cliente_id) {
	            $balance_inicial = $elemento1['nuevo_balance'];
	            break;
	        }
	    }

	    foreach ($list_bal_cierre_teorico as $elemento2) {
	        if ($elemento2['cliente_id'] === $cliente_id) {
	            $monto = $elemento2['monto'];
	            break;
	        }
	    }

	    $balance_despues_2 = (double)$balance_inicial + (double)$monto;
    	$balance_despues[] = ['cliente_id' => $cliente_id, 'balance_despues' => $balance_despues_2];
	}
	$result["balance_despues"] = $balance_despues;


	//CIERRE
	$query_balances_cierre = "
		SELECT
			cb.cliente_id,
			IFNULL(SUM(cb.balance), 0) as nuevo_balance
		FROM tbl_televentas_res_fecha_cliente_balance cb
		LEFT JOIN tbl_televentas_clientes cli ON cb.cliente_id = cli.id
		WHERE cb.fecha = '" . $fecha_fin . "'
		AND LENGTH(cli.num_doc) != 4
	";

	//$result["query_balances_cierre"] = $query_balances_cierre;
	$res_bal_cierre = $mysqli->query($query_balances_cierre);
	$list_balances_cierre = array();
	if($mysqli->error){
		$result["cmd_get_transactions_error"] = $mysqli->error;
	} else {
		$unique_bal_cierre_array = array();
		while ($li=$res_bal_cierre->fetch_assoc()) {
			$list_balances_cierre[]=$li;
			$client_id=$li["cliente_id"];
	        if (!array_key_exists($client_id, $unique_bal_cierre_array)) {
	            $unique_bal_cierre_array[$client_id] = $li;
	        }
		}
	}
	$result["list_balances_cierre"] = $list_balances_cierre;
	$result["unique_bal_cierre_array"] = $unique_bal_cierre_array;

	//DIFERENCIAS
	$clientes_id_bal_cierre = array_column($list_balances_cierre, 'cliente_id');
	$clientes_id_bal_balance_despues = array_column($balance_despues, 'cliente_id');
	$all_cliente_ids_dif = array_unique(array_merge($clientes_id_bal_cierre, $clientes_id_bal_balance_despues));
	$diferencias_array = [];
	foreach ($all_cliente_ids_dif as $cliente_id) {
		$balance_cierre = 0;
    	$balance_despues_2 = 0;
    	$diferencia = 0;

    	foreach ($list_balances_cierre as $elemento1) {
	        if ($elemento1['cliente_id'] === $cliente_id) {
	            $balance_cierre = $elemento1['nuevo_balance'];
	            break;
	        }
	    }

	    foreach ($balance_despues as $elemento2) {
	        if ($elemento2['cliente_id'] === $cliente_id) {
	            $balance_despues_2 = $elemento2['balance_despues'];
	            break;
	        }
	    }

	    $diferencia = round(($balance_despues_2 - $balance_cierre), 2);

		if((double)$diferencia != 0){
			$diferencias_array[] = ['cliente_id' => $cliente_id, 'diferencia' => $diferencia];

		}
    	
	}
	$result["diferencias_array"] = $diferencias_array;

	// Recorrer el array y verificar el nuevo_balance
	$cliente_ids = '';
	foreach ($diferencias_array as $elemento) {
	    if ($elemento['diferencia'] != 0) {
	        $cliente_ids .= $elemento['cliente_id'] . ',';
	    }
	}
	$cliente_ids = rtrim($cliente_ids, ',');


	$result["cliente_ids"] = $cliente_ids;

	$query_clientes = "
		SELECT 
			cli.id cliente_id,
			IFNULL(cli.num_doc, '') num_doc,
			IFNULL(cli.web_id, '') web_id,
			IFNULL(cli.telefono, '') telefono,
			UPPER(IFNULL(cli.nombre, '')) nombres,
			UPPER(IFNULL(cli.apellido_paterno, '')) ape_paterno,
			UPPER(IFNULL(cli.apellido_materno, '')) ape_materno
		FROM tbl_televentas_clientes cli
		WHERE cli.id IN (" . $cliente_ids . ")
	";
	$res_list_clientes = $mysqli->query($query_clientes);
	$list_clientes = array();
	if($mysqli->error){
		$result["cmd_get_transactions_error"] = $mysqli->error;
	} else {
		while ($li=$res_list_clientes->fetch_assoc()) {
			$list_clientes[]=$li;
		}
	}
	$result["list_clientes"] = $list_clientes;
	
	//LISTA FINAL
	$lista = [];
	foreach ($list_clientes as $cliente) {
	    $cliente_id = $cliente['cliente_id'];

	    $balance_inicial_info = array_filter($list_balances_inicio, function ($el) use ($cliente_id) {
	        return $el['cliente_id'] === $cliente_id;
	    });

	    $balance_cierre_teorico_info = array_filter($list_bal_cierre_teorico, function ($el) use ($cliente_id) {
	        return $el['cliente_id'] === $cliente_id;
	    });

	    $balance_cierre_info = array_filter($list_balances_cierre, function ($el) use ($cliente_id) {
	        return $el['cliente_id'] === $cliente_id;
	    });

	    $balance_diferencia_info = array_filter($diferencias_array, function ($el) use ($cliente_id) {
	        return $el['cliente_id'] === $cliente_id;
	    });

	    $balance_despues_info = array_filter($balance_despues, function ($el) use ($cliente_id) {
	        return $el['cliente_id'] === $cliente_id;
	    });

	    $valor_balance_inicial_info = (!empty($balance_inicial_info)) ? current($balance_inicial_info)['nuevo_balance'] : 0; 
	    $valor_balance_cierre_teorico_info = (!empty($balance_cierre_teorico_info)) ? current($balance_cierre_teorico_info)['monto'] : 0; 
	    $valor_balance_cierre_info = (!empty($balance_cierre_info)) ? current($balance_cierre_info)['nuevo_balance'] : 0;
	    $valor_balance_diferencia_info = (!empty($balance_diferencia_info)) ? current($balance_diferencia_info)['diferencia'] : 0;
	    $valor_balance_despues_info_info = (!empty($balance_despues_info)) ? current($balance_despues_info)['balance_despues'] : 0;
	    // Verificar si se encontró la información y construir el nuevo array
	    if (!empty($balance_diferencia_info)) {
	        $cliente_info = [
	            'cliente_id' => $cliente_id,
	            'num_doc' => $cliente['num_doc'],
	            'web_id' => $cliente['web_id'],
	            'telefono' => $cliente['telefono'],
	            'nombres' => $cliente['nombres'],
	            'ape_paterno' => $cliente['ape_paterno'],
	            'ape_materno' => $cliente['ape_materno'],

	            'balance_antes' => $valor_balance_inicial_info,
	            'monto' => $valor_balance_cierre_teorico_info,
	            'balance_despues' => $valor_balance_cierre_info,
	            'balance_despues_2' => $valor_balance_despues_info_info,
	            'balance_diferencia' => $valor_balance_diferencia_info
	        ];
	        $lista[] = $cliente_info;
	    }
	}

	$result["lista"] = $lista;


	if(count($lista) > 0){
		$result["http_code"] = 200;
		$result["status"] = "ok";
		$result["list_balance_diferencia"] = $lista;
	} else {
		$result["http_code"] = 400;
		$result["status"] ="Ocurrió un error al consultar transacciones.";
		$result["result"] =$lista;
	}
}

//*******************************************************************************************************************
//*******************************************************************************************************************
// OBTENER TRANSACCIONES --> EXCEL
//*******************************************************************************************************************
//*******************************************************************************************************************
if (isset($_POST["accion"]) && $_POST["accion"]==="SecRptCBal_exportar_xls") {
	global $mysqli;

	$usuario_id = $login ? $login['id'] : 0;
	$cargo_id = $login ? $login['cargo_id'] : 0;
	$area_id = $login ? $login['area_id'] : 0;

	$fecha_inicio              = $_POST["fecha_inicio"];
	$fecha_fin                 = $_POST["fecha_fin"];
	$cliente_tipo              = $_POST["cliente_tipo"];
	$cliente_texto             = $_POST["cliente_texto"];

	$where_cliente = "";
	if(strlen($cliente_texto) > 1){
		if((int)$cliente_tipo === 1){ // Por nombres
			$where_cliente = " AND CONCAT( IFNULL(cli.nombre, ''), ' ', IFNULL(cli.apellido_paterno, ''), ' ', IFNULL(cli.apellido_materno, '') ) LIKE '%". $cliente_texto ."%' ";
		}
		if((int)$cliente_tipo === 2){ // Por num doc
			$where_cliente = " AND IFNULL(cli.num_doc, '') = '". $cliente_texto ."' ";
		}
		if((int)$cliente_tipo === 3){ // Por web-id
			$where_cliente = " AND IFNULL(cli.web_id, '') = '". $cliente_texto ."' ";
		}
		if((int)$cliente_tipo === 4){ // Por celular
			$where_cliente = " AND IFNULL(cli.telefono, '') = '". $cliente_texto ."' ";
		}
	}

	$where_fecha = " AND tra.created_at >= '" . $fecha_inicio . "'";
	$where_fecha .= " AND tra.created_at <= '" . $fecha_fin . "'";

	$balance_inicial = 0;
	$query_balance_inicial = "
		SELECT sum(bt_1.nuevo_balance) as monto 
		FROM tbl_televentas_clientes_transaccion bt_1 
		INNER JOIN (
			SELECT 
				btt.cliente_id, 
				max(btt.id) id 
				FROM tbl_televentas_clientes_transaccion btt 
				INNER JOIN tbl_televentas_clientes cli ON btt.cliente_id = cli.id 
				WHERE 
				IFNULL(btt.id_tipo_balance,0) != 6 and
				btt.created_at <= '" . $fecha_inicio . "' 
				$where_cliente 
				GROUP BY btt.cliente_id 
		) bt_2 ON bt_2.id = bt_1.id 
		WHERE 
		IFNULL(bt_1.id_tipo_balance,0) != 6 
	";
	$result["query_balance_inicial"] = $query_balance_inicial;
	$res_balance_inicial = $mysqli->query($query_balance_inicial);
	if($mysqli->error){
		$result["query_balance_inicial_error"] = $mysqli->error;
	} else {
		while ($li=$res_balance_inicial->fetch_assoc()) {
			$balance_inicial = $li["monto"];
		}
	}

	$query_1 ="
		SELECT 			
			(CASE
				WHEN tra.tipo_id = 6 THEN 'Depósito Corrección'
				WHEN tra.tipo_id = 7 THEN 'Bingo Cancelado' 
				WHEN tra.tipo_id = 9 THEN 'Solicitud de Retiro (no pagados)'
				WHEN tra.tipo_id = 21 THEN 'Solicitud de Propina (no pagados)'
				ELSE tt.nombre
			END) nombre,
			COUNT( tra.id ) cant,
			SUM( IFNULL( tra.monto, 0 ) ) monto,
			tt.descripcion
		FROM tbl_televentas_clientes_transaccion tra
		INNER JOIN tbl_televentas_clientes_tipo_transaccion tt ON tra.tipo_id = tt.id
		LEFT JOIN tbl_televentas_clientes cli ON tra.cliente_id = cli.id
		LEFT JOIN tbl_televentas_clientes_transaccion tra2 ON tra.transaccion_id = tra2.id 
			AND tra2.tipo_id = 9
			and IFNULL(tra2.id_tipo_balance,0) != 6
		WHERE 
		IFNULL(tra.id_tipo_balance,0) != 6 and
		(
			(tra.tipo_id = 1 and tra.estado = 1 and tra.created_at < '	2022-10-14 16:42:22') /* depositos validados */
			OR (tra.tipo_id = 26) /* depositos validados */
			OR (tra.tipo_id = 6 ) /* depositos rollback */
			OR (tra.tipo_id = 7 and tra.api_id = 4 ) /* Bingo Cancelado*/
			OR (tra.tipo_id = 2 and tra.estado = 1) /* recargas */
			OR (tra.tipo_id IN (4,5,19,20) and tra.estado in (1)) /* apuestas, pagadas, retornadas, jackpot */
			OR (tra.tipo_id IN (4) and tra.estado in (1,3,4,5) and tra.api_id = 4) /* Todos los Bingos*/
			OR (tra.tipo_id = 9 and tra.estado IN (1,3,4,5,6)) /* retiros: pendiente, en proceso, verificado */
			OR ( tra.tipo_id = 9 AND tra.estado = 2 AND tra.created_at >= '$fecha_inicio' AND tra.created_at <= '$fecha_fin' AND tra.updated_at > '$fecha_fin')
			OR (tra.tipo_id = 11 and tra.estado IN (2) AND tra2.created_at >= '$fecha_inicio') /* retiros: pagado */
			OR (tra.tipo_id = 12 and tra.estado IN (3)) /* retiros: rechazado */
			OR (tra.tipo_id = 13 and tra.estado IN (4)) /* retiros: cancelado */
			OR (tra.tipo_id = 14 and tra.estado IN (0,1)) /* terminal-deposit */
			OR (tra.tipo_id IN (15,16)) /* cancer: donacion y correccion */
			OR (tra.tipo_id IN (17,18)) /* subir y bajar balance */
			OR (tra.tipo_id = 21 and tra.estado IN (1,3,4,5,6)) and tra.tipo_rechazo_id is null  /* propinas: pendiente, en proceso, verificado */
			OR (tra.tipo_id = 24 and tra.estado IN (2)) /* propinas: pagado */
			OR (tra.tipo_id = 25 and tra.estado IN (3)) /* propinas: rechazado */
			OR (tra.tipo_id = 22 and tra.estado IN (4)) /* propinas: cancelado */
			OR (tra.tipo_id = 28 and tra.estado IN (1,3,4,5,6)) /* devolucion: pendiente, en proceso, verificado */
			OR (tra.tipo_id = 29 and tra.estado IN (2)) /* devolucion: pagado */
			OR (tra.tipo_id = 30 and tra.estado IN (3)) /* devolucion: rechazado */
			OR (tra.tipo_id = 31 and tra.estado IN (4)) /* devolucion: cancelado */
			OR (tra.tipo_id = 32 and tra.estado = 1) /* pago de premios */
			OR (tra.tipo_id = 33 and tra.estado = 1) /* terminal deposito tambo */
			OR (tra.tipo_id = 34 and tra.estado = 1) /* apuestas canceladas */
		) 
		$where_fecha 
		$where_cliente
		GROUP BY
			tt.id,
			tt.descripcion,
			tt.nombre
		";
	$result["result_query"] = $query_1;
	$list_query = $mysqli->query($query_1);
	$list_transaccion = array();

	if($mysqli->error) {
		//$result["consulta_error"] = $mysqli->error;
		echo json_encode([
			"error" => "Export error"
		]);
		exit;
	}else {

		while ($li=$list_query->fetch_assoc()) {
			$list_transaccion[]=$li;

			$balance_cierre_teorico = $balance_inicial;
		}

		$balance_cierre = 0;
		$query_balance_cierre = "
			SELECT sum(bt_1.nuevo_balance) monto 
			FROM tbl_televentas_clientes_transaccion bt_1 
			INNER JOIN (
					SELECT 
						MAX(btt.id) id, btt.cliente_id 
					FROM tbl_televentas_clientes_transaccion btt 
					INNER JOIN tbl_televentas_clientes cli ON btt.cliente_id = cli.id 
					WHERE 
						IFNULL(btt.id_tipo_balance,0) != 6 and
						btt.created_at <= '" . $fecha_fin . "' 
						$where_cliente 
					GROUP BY btt.cliente_id
				) bt_2 ON bt_2.id = bt_1.id
			WHERE 
				IFNULL(bt_1.id_tipo_balance,0) != 6 
			";
		$result["query_balance_cierre"] = $query_balance_cierre;
		$res_balance_cierre = $mysqli->query($query_balance_cierre);
		if($mysqli->error){
			$result["query_balance_cierre_error"] = $mysqli->error;
		} else {
			while ($li=$res_balance_cierre->fetch_assoc()) {
				$balance_cierre = $li["monto"];
			}
		}

		$query_balance_diferencia = "
			SELECT
				z.*,
				(z.balance_despues_2 - z.balance_despues) balance_diferencia,
				IFNULL(cli.num_doc, '') num_doc,
				IFNULL(cli.web_id, '') web_id,
				IFNULL(cli.telefono, '') telefono,
				UPPER(IFNULL(cli.nombre, '')) nombres,
				UPPER(IFNULL(cli.apellido_paterno, '')) ape_paterno,
				UPPER(IFNULL(cli.apellido_materno, '')) ape_materno
			FROM
				(
				SELECT
					a.cliente_id,
					ifnull( b.nuevo_balance, 0 ) balance_antes,
					ifnull( c.monto, 0 ) monto,
					ifnull( a.nuevo_balance, 0 ) balance_despues,
					( ifnull( b.nuevo_balance, 0 ) + ifnull( c.monto, 0 ) ) balance_despues_2 
				FROM
					(
						SELECT
							bt_1.cliente_id,
							bt_1.nuevo_balance 
						FROM tbl_televentas_clientes_transaccion bt_1
						INNER JOIN (
							SELECT
								MAX( btt.id ) id,
								btt.cliente_id 
							FROM
								tbl_televentas_clientes_transaccion btt
								INNER JOIN tbl_televentas_clientes cli ON btt.cliente_id = cli.id 
							WHERE
								IFNULL(btt.id_tipo_balance,0) != 6 and
								btt.created_at <= '$fecha_fin' 
							GROUP BY
								btt.cliente_id 
						) bt_2 ON bt_2.id = bt_1.id 
					) a
					LEFT JOIN (
						SELECT
							bt_1.cliente_id,
							bt_1.nuevo_balance 
						FROM tbl_televentas_clientes_transaccion bt_1
						INNER JOIN (
							SELECT
								btt.cliente_id,
								max( btt.id ) id 
							FROM
								tbl_televentas_clientes_transaccion btt
								INNER JOIN tbl_televentas_clientes cli ON btt.cliente_id = cli.id 
							WHERE
								IFNULL(btt.id_tipo_balance,0) != 6 and
								btt.created_at <= '$fecha_inicio' 
							GROUP BY
								btt.cliente_id 
						) bt_2 ON bt_2.id = bt_1.id 
					) b ON b.cliente_id = a.cliente_id
					LEFT JOIN (
					SELECT
						tra.cliente_id,
						SUM(IF(tra.tipo_id IN ( 1, 26, 5, 12, 13, 14, 16, 17, 19, 20, 22, 25, 30, 31, 32, 34, 7 ), tra.monto, 0 )) - SUM(IF( tra.tipo_id IN ( 2, 4, 6, 9, 11, 15, 18, 21, 24, 28, 29, 33), tra.monto, 0 )) monto 
					FROM
						tbl_televentas_clientes_transaccion tra
						INNER JOIN tbl_televentas_clientes_tipo_transaccion tt ON tra.tipo_id = tt.id
						LEFT JOIN tbl_televentas_clientes cli ON tra.cliente_id = cli.id 
						LEFT JOIN tbl_televentas_clientes_transaccion tra2 ON tra.transaccion_id = tra2.id 
							AND tra2.tipo_id = 9
							AND IFNULL(tra2.id_tipo_balance,0) != 6
					WHERE
						IFNULL(tra.id_tipo_balance,0) != 6 and
						tra.created_at >= '$fecha_inicio' 
						AND tra.created_at <= '$fecha_fin' 
						AND (
							( tra.tipo_id = 1 AND tra.estado = 1 and tra.created_at < '	2022-10-14 16:42:22' ) /* depositos validados */
							OR (tra.tipo_id = 26) /* depositos validados */
							OR (tra.tipo_id = 6 ) /* depositos rollback */
							OR (tra.tipo_id = 7 and tra.api_id = 4 ) /* Bingo Cancelado*/
							OR ( tra.tipo_id = 2 AND tra.estado = 1 ) /* recargas */
							OR ( tra.tipo_id IN ( 4, 5, 19, 20 ) AND tra.estado IN ( 1 ) ) /* apuestas, pagadas, retornadas, jackpot */
							OR (tra.tipo_id IN (4) and tra.estado in (1,3,4,5) and tra.api_id = 4) /* Todos los Bingos*/
							OR ( tra.tipo_id = 9 AND tra.estado IN ( 1, 3, 4, 5, 6 ) ) /* retiros: pendiente, en proceso, verificado */
							OR ( tra.tipo_id = 9 AND tra.estado = 2 AND tra.created_at >= '$fecha_inicio' AND tra.created_at <= '$fecha_fin' AND tra.updated_at > '$fecha_fin')
							OR ( tra.tipo_id = 11 AND tra.estado IN ( 2 ) AND tra2.created_at >= '$fecha_inicio' ) /* retiros: pagado */
							OR ( tra.tipo_id = 12 AND tra.estado IN ( 3 ) ) /* retiros: rechazado */
							OR ( tra.tipo_id = 13 AND tra.estado IN ( 4 ) ) /* retiros: cancelado */
							OR ( tra.tipo_id = 14 AND tra.estado IN ( 0, 1 ) ) /* terminal-deposit */
							OR ( tra.tipo_id IN ( 15, 16 ) ) /* cancer: donacion y correccion */
							OR ( tra.tipo_id IN ( 17, 18 ) ) /* subir y bajar balance */
							OR (tra.tipo_id = 21 and tra.estado IN (1,3,4,5,6))  and tra.tipo_rechazo_id is null  /* propinas: pendiente, en proceso, verificado */
							OR (tra.tipo_id = 24 and tra.estado IN (2)) /* propinas: pagado */
							OR (tra.tipo_id = 25 and tra.estado IN (3)) /* propinas: rechazado */
							OR (tra.tipo_id = 22 and tra.estado IN (4)) /* propinas: cancelado */
							OR (tra.tipo_id = 28 and tra.estado IN (1,3,4,5,6)) /* devolucion: pendiente, en proceso, verificado */
							OR (tra.tipo_id = 29 and tra.estado IN (2)) /* devolucion: pagado */
							OR (tra.tipo_id = 30 and tra.estado IN (3)) /* devolucion: rechazado */
							OR (tra.tipo_id = 31 and tra.estado IN (4)) /* devolucion: cancelado */
							OR (tra.tipo_id = 32 and tra.estado = 1) /* pago de premios */
							OR (tra.tipo_id = 33 and tra.estado = 1) /* terminal deposito tambo */
							OR (tra.tipo_id = 34 and tra.estado = 1) /* apuesta cancelada */
						) 
					GROUP BY
						tra.cliente_id /* order by tra.cliente_id asc */
					) c ON c.cliente_id = a.cliente_id /* order by a.cliente_id */
				) z 
				JOIN tbl_televentas_clientes cli ON cli.id=z.cliente_id
			WHERE
				z.balance_despues != z.balance_despues_2 
			ORDER BY
				z.cliente_id ASC
			";
		$result["query_balance_diferencia"] = $query_balance_diferencia;
		$res_balance_diferencia = $mysqli->query($query_balance_diferencia);
		$list_balance_diferencia = array();
		if($mysqli->error){
			$result["query_balance_diferencia_error"] = $mysqli->error;
		} else {
			while ($li=$res_balance_diferencia->fetch_assoc()) {
				$list_balance_diferencia[] = $li;
			}
		}

		if(count($list_transaccion) > 0){

			$nob_saldo_ini_h = "Balance ".$fecha_inicio;
			$saldo_ini_h = "S/ ".$balance_inicial;

			$nob_balance_cierre_h = "Balance ".$fecha_fin;
			$balance_cierre_h = "S/ ".$balance_cierre;

			ini_set('display_errors', 0);
			ini_set('display_startup_errors', 0);
			error_reporting(0);

			require_once '../phpexcel/classes/PHPExcel.php';
			$objPHPExcel = new PHPExcel();

			$objPHPExcel->setActiveSheetIndex(0)
			->setCellValue('A1', $nob_saldo_ini_h)
			->setCellValue('C1', $saldo_ini_h);

			$objPHPExcel->setActiveSheetIndex(0);
			$sheet = $objPHPExcel->getActiveSheet();

			$estilo1 = array(
				'font' => array(
					'name'  => 'Arial',
					'bold'  => true,
					'size' => 10,
					'color' => array(
						'rgb' => '000000'
					)
				),
				'alignment' =>  array(
					'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					'wrap'      => false
				),
				'borders' => array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN
					)
				),
				'fill' => array(
						'type'  => PHPExcel_Style_Fill::FILL_SOLID,
						'color' => array(
							'rgb' => 'c1eaf2'
						)
				)
			);

			$objPHPExcel->getActiveSheet()->getStyle('A1:D1')->applyFromArray($estilo1);

			$estilo5 = array(
				'font' => array(
					'name'  => 'Arial',
					'bold'  => true,
					'size' => 10,
					'color' => array(
						'rgb' => 'ddd'
					)
				),
				'alignment' =>  array(
					'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					'wrap'      => false
				),
				'borders' => array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN
					)
				),
				'fill' => array(
						'type'  => PHPExcel_Style_Fill::FILL_SOLID,
						'color' => array(
							'rgb' => 'DE4F45'
						)
				)
			);

			$estilo6 = array(
				'font' => array(
					'name'  => 'Arial',
					'bold'  => true,
					'size' => 10,
					'color' => array(
						'rgb' => 'ddd'
					)
				),
				'alignment' =>  array(
					'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					'wrap'      => false
				),
				'borders' => array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN
					)
				),
				'fill' => array(
						'type'  => PHPExcel_Style_Fill::FILL_SOLID,
						'color' => array(
							'rgb' => '43ba58'
						)
				)
			);

			$rowIndex = 3;

			foreach ($list_transaccion as $key => $value) {

				if($value['descripcion'] == "-"){
					$balance_cierre_teorico = $balance_cierre_teorico - $value['monto'];
					$objPHPExcel->getActiveSheet()->getStyle('D'.$rowIndex)->applyFromArray($estilo5);
				}else if($value['descripcion'] == "+"){
					$balance_cierre_teorico = $balance_cierre_teorico + $value['monto'];
					$objPHPExcel->getActiveSheet()->getStyle('D'.$rowIndex)->applyFromArray($estilo6);
				}

				$monto_h = "S/ ".$value['monto'] ;

				$sheet->setCellValue('A' . $rowIndex, $value['nombre']);
				$sheet->setCellValue('B' . $rowIndex, $value['cant']);
				$sheet->setCellValue('C' . $rowIndex, $monto_h);
				$sheet->setCellValue('D' . $rowIndex, $value['descripcion']);
				
				$rowIndex++;

			}

			$balance_cierre_teorico_h = "S/ ".$balance_cierre_teorico;
			$diferencia = $balance_cierre_teorico - $balance_cierre;
			$diferencia_h = "S/ ".$diferencia ;
		 
			$lastRowIndex = $rowIndex + 1;

			$estilo2 = array(
				'font' => array(
					'name'  => 'Arial',
					'bold'  => true,
					'size' => 10,
					'color' => array(
						'rgb' => '000000'
					)
				),
				'alignment' =>  array(
					'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					'wrap'      => false
				),
				'borders' => array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN
					)
				),
				'fill' => array(
						'type'  => PHPExcel_Style_Fill::FILL_SOLID,
						'color' => array(
							'rgb' => 'edb376'
						)
				)
			);
			
			$sheet->setCellValue('A' . $lastRowIndex, 'Balance Cierre Teórico ');
			$sheet->setCellValue('C' . $lastRowIndex, $balance_cierre_teorico_h);

			$objPHPExcel->getActiveSheet()->getStyle('A'.$lastRowIndex.':D'.$lastRowIndex)->applyFromArray($estilo2);

			$lastRowIndex = $lastRowIndex + 1;

			$estilo3 = array(
				'font' => array(
					'name'  => 'Arial',
					'bold'  => true,
					'size' => 10,
					'color' => array(
						'rgb' => '000000'
					)
				),
				'alignment' =>  array(
					'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					'wrap'      => false
				),
				'borders' => array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN
					)
				),
				'fill' => array(
						'type'  => PHPExcel_Style_Fill::FILL_SOLID,
						'color' => array(
							'rgb' => '7ac3e8'
						)
				)
			);

			$sheet->setCellValue('A' . $lastRowIndex, $nob_balance_cierre_h);
			$sheet->setCellValue('C' . $lastRowIndex, $balance_cierre_h);

			$objPHPExcel->getActiveSheet()->getStyle('A'.$lastRowIndex.':D'.$lastRowIndex)->applyFromArray($estilo3);


			$lastRowIndex = $lastRowIndex + 1;

			$estilo4 = array(
				'font' => array(
					'name'  => 'Arial',
					'bold'  => true,
					'size' => 10,
					'color' => array(
						'rgb' => '000000'
					)
				),
				'alignment' =>  array(
					'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
					'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
					'wrap'      => false
				),
				'borders' => array(
					'allborders' => array(
						'style' => PHPExcel_Style_Border::BORDER_THIN
					)
				),
				'fill' => array(
						'type'  => PHPExcel_Style_Fill::FILL_SOLID,
						'color' => array(
							'rgb' => 'd8db2e'
						)
				)
			);

			$sheet->setCellValue('A' . $lastRowIndex, 'Diferencia ');
			$sheet->setCellValue('C' . $lastRowIndex, $diferencia_h);		
			
			$objPHPExcel->getActiveSheet()->getStyle('A'.$lastRowIndex.':D'.$lastRowIndex)->applyFromArray($estilo4);

			$lastRowIndex = $lastRowIndex + 2;

			if(count($list_balance_diferencia) > 0){

				$estilo7 = array(
					'font' => array(
						'name'  => 'Arial',
						'bold'  => true,
						'size' => 10,
						'color' => array(
							'rgb' => '000000'
						)
					),
					'alignment' =>  array(
						'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
						'wrap'      => false
					)
				);

				$sheet->setCellValue('A' . $lastRowIndex, 'LISTA DE DIFERENCIAS POR CLIENTE');

				$objPHPExcel->getActiveSheet()->getStyle('A'.$lastRowIndex)->applyFromArray($estilo7);

				$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A' . $lastRowIndex.':H'. $lastRowIndex);

				$lastRowIndex = $lastRowIndex + 1;

				$sheet->setCellValue('A' . $lastRowIndex, 'NºDOC.');
				$sheet->setCellValue('B' . $lastRowIndex, 'WEB-ID');
				$sheet->setCellValue('C' . $lastRowIndex, 'CLIENTE');
				$sheet->setCellValue('D' . $lastRowIndex, 'BAL.INICIO');
				$sheet->setCellValue('E' . $lastRowIndex, 'MONTO DIF.');
				$sheet->setCellValue('F' . $lastRowIndex, 'BAL.CIERRE TEO.');
				$sheet->setCellValue('G' . $lastRowIndex, 'BAL.CIERRE');
				$sheet->setCellValue('H' . $lastRowIndex, 'DIFERENCIA');

				$lastRowIndex = $lastRowIndex + 1;

				$rowIndex2 = $lastRowIndex;

				foreach ($list_balance_diferencia as $key => $value) {

					$nombres = $value['nombres']." ".$value['ape_paterno']." ".$value['ape_materno'] ;

					$balance_antes = "S/ ".$value['balance_antes'] ;
					$monto = "S/ ".$value['monto'] ;
					$balance_despues_2 = "S/ ".$value['balance_despues_2'] ;
					$balance_despues = "S/ ".$value['balance_despues'] ;
					$balance_diferencia = "S/ ".$value['balance_diferencia'] ;
	
					$sheet->setCellValue('A' . $rowIndex2, $value['num_doc']);
					$sheet->setCellValue('B' . $rowIndex2, $value['web_id']);
					$sheet->setCellValue('C' . $rowIndex2, $nombres);
					$sheet->setCellValue('D' . $rowIndex2, $balance_antes);
					$sheet->setCellValue('E' . $rowIndex2, $monto);
					$sheet->setCellValue('F' . $rowIndex2, $balance_despues_2);
					$sheet->setCellValue('G' . $rowIndex2, $balance_despues);
					$sheet->setCellValue('H' . $rowIndex2, $balance_diferencia);
	
					$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($rowIndex2)->setAutoSize(TRUE);
					
					$rowIndex2++;
	
				}
			
			}

			$date = new DateTime();
			$file_title = "cuadre_de_caja_" . $date->getTimestamp();

			if (!file_exists('/var/www/html/export/files_exported/cuadre_balance/')) {
				mkdir('/var/www/html/export/files_exported/cuadre_balance/', 0777, true);
			}

			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="' . $file_title . '.xls"');
			header('Cache-Control: max-age=0');

			$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
			$excel_path = '/var/www/html/export/files_exported/cuadre_balance/' . $file_title . '.xls';
			$excel_path_download = '/export/files_exported/cuadre_balance/' . $file_title . '.xls';
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
	}
}

echo json_encode($result);
?>