<?php
include("global_config.php");
include("db_connect.php");
include("sys_login.php");
function data_to_db($d){
	global $mysqli;
	$tmp=array();
	$nulls=array("null","",false);
	foreach ($d as $k => $v) {
		// if($v===0){
		if(is_numeric($v)){
			// $tmp[$k]=$v;
			$tmp[$k]="'".$v."'";
		}elseif(in_array($v, $nulls)){
			$tmp[$k]="NULL";
		}else{
				if(is_float($v)){
					$tmp[$k]="'".$v."'";
				}elseif(is_int($v)){
					$tmp[$k]=$v;
				}else{
					$v=str_replace(",", ".", $v);
					$tmp[$k]="'".trim($mysqli->real_escape_string($v))."'";
				}
			}
	}
	return $tmp;
}

function pm_insert_bingo($data){
	global $mysqli;
	global $return;

	searchDupliBingo($data);

	$command = '';
	$command.= "INSERT INTO tbl_repositorio_bingo_tickets";
	$command.= "(";
	$command.=implode(", ", array_keys($data));
	$command.=")";
	$command.=" VALUES ";
	$command.="(";
	$command.=implode(", ", $data);
	$command.=")";
	$mysqli->query($command);

	if($mysqli->error){
		print_r($mysqli->error);
		exit();
	}
}

function pm_insert_hipica($data){
	global $mysqli;
	global $return;

	searchDupliHipica($data);

	$command = '';
	$command.= "INSERT INTO tbl_repositorio_tickets_america_simulcast";
	$command.= "(";
	$command.=implode(", ", array_keys($data));
	$command.=")";
	$command.=" VALUES ";
	$command.="(";
	$command.=implode(", ", $data);
	$command.=")";
	$mysqli->query($command);

	if($mysqli->error){
		print_r($mysqli->error);
		exit();
	}
}


function detalle_insert($detalle){
	global $mysqli;
	global $return;

	searchDupliDetalle($detalle['at_unique_id']);

	$command = "INSERT INTO tbl_transacciones_detalle ";
	$command.="(";
	$command.=implode(", ", array_keys($detalle));
	$command.=")";
	$command.=" VALUES ";
	$command.="(";
	$command.=implode(", ", $detalle);
	$command.=")";
	// $command.=" ON DUPLICATE KEY UPDATE ";
	// $uqn=0;
	// foreach ($detalle as $key => $value) {
	// 	if($uqn>0) { $command.=", "; }
	// 	$command.= $key." = ".$value."";
	// 	$uqn++;
	// }

	var_dump($command);

	$mysqli->query($command);
	$affected_rows = $mysqli->affected_rows;
	if($affected_rows==2){
		$return["deta_insert_command_UPDATE"][]=$command;
		// $return["detalles_updateados"]++;
	}elseif($affected_rows==1){
		$return["deta_insert_command_INSERT"][]=$command;
		// $return["detalles_insertados"]++;
	}else{
		$return["deta_insert_command_NOTHING"][]=$command;
		// $return["detalles_nothing"]++;
	}
	// return true;
};

function transacciones_consolidado_insert($detalle){
	global $mysqli;
	global $return;

	try {

		searchDupliConsolidadoDetalle($detalle['at_unique_id']);
		
		$TIPO_PAGO_MANUAL = 2;
		$transacciones_consolidado = [];
		$transacciones_consolidado['at_unique_id'] = $detalle['at_unique_id'];
		$transacciones_consolidado['local_id'] = $detalle['local_id'];
		$transacciones_consolidado['servicio_id'] = $detalle['servicio_id'];
		$transacciones_consolidado['canal_de_venta_id'] = $detalle['canal_de_venta_id'];
		$transacciones_consolidado['tipo_transaccion_id'] = $TIPO_PAGO_MANUAL;
		$transacciones_consolidado['fecha_consolidado'] = $detalle['created'];
		$transacciones_consolidado['apostado'] = isset($detalle['apostado']) ? $detalle['apostado'] : 0;
		$transacciones_consolidado['ganado'] = isset($detalle['ganado']) ? $detalle['ganado'] : 0;
		$transacciones_consolidado['cancelado'] = isset($detalle['cancelado']) ? $detalle['cancelado'] : 0;
		$transacciones_consolidado['pagado'] = isset($detalle['pagado']) ? $detalle['pagado'] : 0;
		$transacciones_consolidado['caja_deposito_terminal'] = isset($detalle['terminal_income']) ? $detalle['terminal_income'] : 0;
		$transacciones_consolidado['caja_retiro_terminal'] = isset($detalle['terminal_withdraw']) ? $detalle['terminal_withdraw'] : 0;
		$transacciones_consolidado['deposito_terminal'] = isset($detalle['income']) ? $detalle['income'] : 0;
		$transacciones_consolidado['estado'] = 1;
		$transacciones_consolidado['user_created_id'] = isset($detalle['user_created_id']) ? $detalle['user_created_id'] : 0;
		
		$command = "INSERT INTO kx_transacciones_consolidado (
						at_unique_id,
						local_id,
						servicio_id,
						canal_de_venta_id,
						tipo_transaccion_id,
						fecha_consolidado,
						apostado,
						ganado,
						cancelado,
						pagado,
						caja_deposito_terminal,
						caja_retiro_terminal,
						deposito_terminal,
						estado,
						user_created_id
					)VALUES(
						'".$transacciones_consolidado['at_unique_id']."',
						'".$transacciones_consolidado['local_id']."',
						'".$transacciones_consolidado['servicio_id']."',
						'".$transacciones_consolidado['canal_de_venta_id']."',
						'".$transacciones_consolidado['tipo_transaccion_id']."',
						'".$transacciones_consolidado['fecha_consolidado']."',
						'".$transacciones_consolidado['apostado']."',
						'".$transacciones_consolidado['ganado']."',
						'".$transacciones_consolidado['cancelado']."',
						'".$transacciones_consolidado['pagado']."',
						'".$transacciones_consolidado['caja_deposito_terminal']."',
						'".$transacciones_consolidado['caja_retiro_terminal']."',
						'".$transacciones_consolidado['deposito_terminal']."',
						'".$transacciones_consolidado['estado']."',
						'".$transacciones_consolidado['user_created_id']."'
					)";
		$mysqli->query($command);
		if($mysqli->error){
			return [
				'status' => 404,
				'message' => $mysqli->error,
			];
		}
		return [
			'status' => 200,
			'message' => "se ha registrado el pago manual correctamente",
		];

	} catch (\Exception $e) {
		return [
			'status' => 404,
			'message' => $e->getMessage(),
		];
	}
};


function pm_insert($pm){
	global $mysqli;
	global $return;


	searchDuplicated($pm['at_unique_id']);
	$command =" ";
	$command.= "INSERT INTO tbl_pago_manual ";
	$command.="(";
	$command.=implode(", ", array_keys($pm));
	$command.=")";
	$command.=" VALUES ";
	$command.="(";
	$command.=implode(", ", $pm);
	$command.=")";
	// $command.=" ON DUPLICATE KEY UPDATE ";
	// $uqn=0;
	// foreach ($pm as $key => $value) {
	// 	if($uqn>0) { $command.=", "; }
	// 	$command.= $key." = ".$value."";
	// 	$uqn++;
	// }
	$mysqli->query($command);

	if($mysqli->error){
		print_r($mysqli->error);
		exit();
	}
	// $affected_rows = $mysqli->affected_rows;
	// if($affected_rows==2){
		// $return["deta_insert_command_UPDATE"][]=$command;
		// $return["detalles_updateados"]++;
	// }elseif($affected_rows==1){
		// $return["deta_insert_command_INSERT"][]=$command;
		// $return["detalles_insertados"]++;
	// }else{
		// $return["deta_insert_command_NOTHING"][]=$command;
		// $return["detalles_nothing"]++;
	// }
	// return true;
};

function searchDupliBingo($data){
	global $mysqli;
	$arr = [];
	$query = "SELECT id, at_unique_id FROM tbl_repositorio_bingo_tickets WHERE at_unique_id =".$data['at_unique_id']." ";
	$resp =$mysqli->query($query);
	while($l = $resp-> fetch_assoc()){
		$arr=$l;
		$queryDel = "DELETE FROM tbl_repositorio_bingo_tickets WHERE at_unique_id = ".$data['at_unique_id']." ";
		$delete = $mysqli->query($queryDel);
		if($delete){
			echo "llego al el search de tickets y borro";
		}
	}
	return $arr;
};

function searchDupliHipica($data){
	global $mysqli;
	$arr = [];
	$query = "SELECT id FROM tbl_repositorio_tickets_america_simulcast WHERE ticket_id =".$data['ticket_id']." ";
	$resp =$mysqli->query($query);
	while($l = $resp-> fetch_assoc()){
		$arr=$l;
		$queryDel = "DELETE FROM tbl_repositorio_tickets_america_simulcast WHERE ticket_id = ".$data['ticket_id']." ";
		$delete = $mysqli->query($queryDel);
		if($delete){
			echo "llego al el search de tickets y borro";
		}
	}
	return $arr;
};


function searchDupliConsolidadoDetalle($at_id){
	global $mysqli;

	$arr = [];
	$query = "SELECT id,at_unique_id FROM kx_transacciones_consolidado WHERE estado = 1 AND at_unique_id = '".$at_id."'";
	$resp = $mysqli->query($query);
	// var_dump($query);
	while($l=$resp->fetch_assoc()){
		$arr=$l;
		$queryDel = "UPDATE kx_transacciones_consolidado SET estado = '5' WHERE id = ".$arr['id']." AND at_unique_id = '".$arr['at_unique_id']."' ";
			$delete = $mysqli->query($queryDel);
			if($delete){
				// echo "llego a el search de detalle y lo inactivo";
			}
	}
	return $arr;
};

function searchDupliDetalle($at_id){
	global $mysqli;

	$arr = [];
	$query = "SELECT id,at_unique_id FROM tbl_transacciones_detalle WHERE at_unique_id =".$at_id." ";
	$resp = $mysqli->query($query);
	// var_dump($query);
	while($l=$resp->fetch_assoc()){
		$arr=$l;
		$queryDel = "DELETE FROM tbl_transacciones_detalle WHERE id = ".$arr['id']." AND at_unique_id = '".$arr['at_unique_id']."' ";
			$delete = $mysqli->query($queryDel);
			if($delete){
				// echo "llego a el search de detalle y borro";
			}
	}
	return $arr;
};

function get_ccid($local_id){
	global $mysqli;

	$res = [];
	$query = "SELECT cc_id FROM tbl_locales WHERE id =".$local_id."; ";
	$resp = $mysqli->query($query);
	while($r=$resp->fetch_assoc())$res[]=$r;
	return $res[0]['cc_id'];
}


function searchDuplicated($at_id){
	global $mysqli;

	$ar = [];
	$query = "SELECT id, at_unique_id FROM tbl_pago_manual WHERE at_unique_id = ".$at_id." ";
	$resp = $mysqli->query($query);
	while($l=$resp->fetch_assoc()){
		$ar=$l;
		$queryDel = "DELETE FROM tbl_pago_manual WHERE id = ".$ar['id']." AND at_unique_id = '".$ar['at_unique_id']."' ";
			$delete = $mysqli->query($queryDel);
			if($delete){
				echo "llego a el search y borro";
			}
	}
	return $ar;
};

if(isset($_POST['add_pago_manual_bingo'])){
	$data = $_POST["add_pago_manual_bingo"];


	$new_bet_id = "pm_".time();
	if($data["at_unique_id"]==="new"){
		$at_unique_id = md5($data["cdv_id"].$data["tipo_id"].$new_bet_id);
	}else{
		$at_unique_id = $data["at_unique_id"];
		//AQUI SE SE TRAEL EL AT_UNIQUE_ID DEL FORMULARIO (EDITAR)
	}


	$pago_manual = array();

		$pago_manual["at_unique_id"]=$at_unique_id;
		$pago_manual["fecha_proceso"]=$datetime;
		$pago_manual["tipo_id"]=$data["tipo_id"];
		$pago_manual["motivo_id"]=$data["motivo_id"];
		$pago_manual["autorizacion_id"]=$data["autorizacion_id"];
		$pago_manual["usuario_id"]=$login["id"];

		$pago_manual["referencia"]=$data["referencia"];
		$pago_manual["fecha_pago"]=$data["fecha"];
		$pago_manual["local_id"]=$data["local_id"];
		$pago_manual["canal_de_venta_id"]=$data["cdv_id"];
		$pago_manual["monto"]=$data["monto"];
		$pago_manual["descripcion"]=$data["descripcion"];

		$pago_manual["estado"]=1;
	// print_r($pago_manual);
	$pago_manual_to_db = data_to_db($pago_manual);
	// print_r($pago_manual_to_db);
	//detalle_insert($deta_to_db);
   $temp = 'pm_'.date('Ymd').date('His');

   $idLocal = get_ccid($data["local_id"]);
   $data["local_id"] = $idLocal;

	$bingo_ticket = array();
	 $bingo_ticket_id = 'pm_'.date('Ymd').date('His');
	 // $bingo_ticket['id'] = 0;
	$bingo_ticket['at_unique_id'] = $at_unique_id;
	$bingo_ticket['ticket_id'] = $bingo_ticket_id;
	$bingo_ticket['game_id'] = '';

	if($pago_manual["tipo_id"] == '1'){

		$bingo_ticket['sell_local_id'] = $data["local_id"];
		$bingo_ticket['paid_local_id'] = '';
		$bingo_ticket['amount'] = $data["monto"];
		$bingo_ticket['winning'] = '';
		$bingo_ticket['status'] = 'Pending';

	}elseif($pago_manual["tipo_id"] == '2'){

		$bingo_ticket['sell_local_id'] = $data["local_id"];
		$bingo_ticket['paid_local_id'] = $data["local_id"];
		$bingo_ticket['amount'] = '';
		$bingo_ticket['winning'] = $data["monto"];
		$bingo_ticket['status'] = 'Paid';
		$bingo_ticket['paid_at'] = $data["fecha"];

	}

	$bingo_ticket['created'] = $data["fecha"];
	$bingo_ticket['created_at'] = date('Y-m-d H:i:s');
	$bingo_ticket['updated_at'] = date('Y-m-d H:i:s');

	$bingo_ticket_bd = data_to_db($bingo_ticket);

	pm_insert_bingo($bingo_ticket_bd);
	pm_insert($pago_manual_to_db);
	// var_dump($bingo_ticket_bd);
	// var_dump($pago_manual_to_db);
	// die;
};

if(isset($_POST['add_pago_manual_hipica'])){
	$data = $_POST["add_pago_manual_hipica"];


	$new_bet_id = "pm_".time();
	if($data["at_unique_id"]==="new"){
		$at_unique_id = md5($data["cdv_id"].$data["tipo_id"].$new_bet_id);
	}else{
		$at_unique_id = $data["at_unique_id"];
		//AQUI SE SE TRAEL EL AT_UNIQUE_ID DEL FORMULARIO (EDITAR)
	}


	$pago_manual = array();

		$pago_manual["at_unique_id"]=$at_unique_id;
		$pago_manual["fecha_proceso"]=$datetime;
		$pago_manual["tipo_id"]=$data["tipo_id"];
		$pago_manual["motivo_id"]=$data["motivo_id"];
		$pago_manual["autorizacion_id"]=$data["autorizacion_id"];
		$pago_manual["usuario_id"]=$login["id"];

		$pago_manual["referencia"]=$data["referencia"];
		$pago_manual["fecha_pago"]=$data["fecha"];
		$pago_manual["local_id"]=$data["local_id"];
		$pago_manual["canal_de_venta_id"]=$data["cdv_id"];
		$pago_manual["monto"]=$data["monto"];
		$pago_manual["descripcion"]=$data["descripcion"];

		$pago_manual["estado"]=1;

		$pago_manual_to_db = data_to_db($pago_manual);
	
		$bingo_ticket_id = 'pm_' . $data["local_id"]. date('Ymd').date('His');

		$bingo_ticket_id = 'pm_' . $data["local_id"]. date('Ymd').date('His');
		$bingo_ticket['ticket_id'] = $bingo_ticket_id;
		$bingo_ticket['user_id'] = '';
		$bingo_ticket['terminal_id'] = '';
		$bingo_ticket['local_id'] = $data["local_id"];
		$bingo_ticket['location_name'] = '';
		$bingo_ticket['currency_id'] = 'PEN';
		$bingo_ticket['amount'] = $data["monto"];
		$bingo_ticket['pool_type'] = 0;
		$bingo_ticket['bet_status'] = 2;
	
		if ($pago_manual["tipo_id"] == '1') {  //                     APOSTADO
			$bingo_ticket['transaction_type'] = 4;      
		} elseif ($pago_manual["tipo_id"] == '2') { //                 PAGADO
			$bingo_ticket['transaction_type'] = 5;
		}
	
		$bingo_ticket['track_event_id'] = $data["fecha"];
		$bingo_ticket['creation_date'] = $data["fecha"];
		$bingo_ticket['created_at'] = date('Y-m-d H:i:s');
		$bingo_ticket['updated_at'] = date('Y-m-d H:i:s');

	$bingo_ticket_bd = data_to_db($bingo_ticket);

	pm_insert_hipica($bingo_ticket_bd);
	pm_insert($pago_manual_to_db);
};


if(isset($_POST["add_pago_manual"])){
	$data=$_POST["add_pago_manual"];


	$detalle=array();
	$new_bet_id = "pm_".time();
	if($data["at_unique_id"]=="new"){
		$at_unique_id = md5($data["cdv_id"].$data["tipo_id"].$new_bet_id);
	}else{
		$at_unique_id = $data["at_unique_id"];
		//AQUI SE SE TRAEL EL AT_UNIQUE_ID DEL FORMULARIO (EDITAR)
	}

	// $detalle["ticket_id"]=$new_bet_id;
	$detalle["at_unique_id"]=$at_unique_id;
	$detalle["moneda_id"]=1;
	$detalle["canal_de_venta_id"]=$data["cdv_id"];

	$detalle["created"]=$data["fecha"];
	$detalle["local_id"]=$data["local_id"];
	if ($detalle["canal_de_venta_id"] == '42' || $detalle["canal_de_venta_id"] == '43') {
		$detalle["user_created_id"] = isset($login["id"]) ? $login["id"] : 0;
	}
	if($data["tipo_id"]==1){ //Apostado REVISADO
		// $detalle["created"]=$data["fecha"];
		// $detalle["local_id"]=$data["local_id"];
		if($data["cdv_id"]==15 || $data["cdv_id"]==16 || $data["cdv_id"]==17 || $data["cdv_id"]==19 || $data["cdv_id"]== 42 || $data["cdv_id"]== 43 ){
			$detalle["servicio_id"]=1;
			$detalle["tipo"]=1;
			$detalle["apostado"]=$data["monto"];
			if ($data["cdv_id"]== 42 || $data["cdv_id"]== 43) {
				$detalle["servicio_id"] = 17;
			}
		}elseif($data["cdv_id"]==21){
			$detalle["servicio_id"]=3;
			$detalle["tipo"]=4;
			$detalle["apostado"]=$data["monto"];
		}elseif($data["cdv_id"]==22){
			$detalle["servicio_id"]=5;
			$detalle["tipo"]=4;
			$detalle["apostado"]=$data["monto"];
		}else{
			$detalle=false;
		}
	}elseif($data["tipo_id"]==2){ //Pagado REVISADO
		$detalle["paid_day"]=$data["fecha"];
		$detalle["paid_local_id"]=$data["local_id"];
		$detalle["paid_canal_de_venta_id"]=$data["cdv_id"];
		if($data["cdv_id"]==15 || $data["cdv_id"]== 16 || $data["cdv_id"] == 42){
			$detalle["servicio_id"]=1;
			$detalle["tipo"]=1;
			$detalle["ganado"]=$data["monto"];
			if ($data["cdv_id"] == 42) {
				$detalle["servicio_id"] = 17;
				$detalle["ganado"]=0;
				$detalle["pagado"]=$data["monto"];
			}
		}elseif($data["cdv_id"]==17 || $data["cdv_id"]==19 || $data["cdv_id"] == 43){
			$detalle["servicio_id"]=1;
			$detalle["tipo"]=1;
			$detalle["ganado"]=$data["monto"];
			$detalle["paid_canal_de_venta_id"]=16;
			if ($data["cdv_id"]== 43) {
				$detalle["servicio_id"] = 17;
				$detalle["paid_canal_de_venta_id"] = 42;
			}
		}elseif($data["cdv_id"]==21){
			$detalle["servicio_id"]=3;
			$detalle["tipo"]=4;
			$detalle["ganado"]=$data["monto"];
		}elseif($data["cdv_id"]==22){
			$detalle["servicio_id"]=5;
			$detalle["tipo"]=4;
			$detalle["ganado"]=$data["monto"];
		}else{
			$detalle=false;
		}
	}elseif($data["tipo_id"]==3){ //Cash In
		// $detalle["created"]=$data["fecha"];
		// $detalle["local_id"]=$data["local_id"];
		if($data["cdv_id"]==16 || $data["cdv_id"] == 42){
			$detalle["servicio_id"]=1;
			$detalle["tipo"]=3;
			$detalle["terminal_income"]=$data["monto"];
			if ($data["cdv_id"] == 42) {
				$detalle["servicio_id"] = 17;
			}
		}elseif($data["cdv_id"]==17  || $data["cdv_id"] == 43){
			$detalle["servicio_id"]=1;
			$detalle["tipo"]=2;
			$detalle["income"]=$data["monto"];
			if ($data["cdv_id"] == 43) {
				$detalle["servicio_id"] = 17;
			}
		}elseif($data["cdv_id"]==21){
			$detalle["servicio_id"]=3;
			$detalle["tipo"]=4;
			$detalle["income"]=$data["monto"];
		}else{
			$detalle=false;
		}
	}elseif($data["tipo_id"]==4){ //Cash Out REVISADO
		// $detalle["created"]=$data["fecha"];
		// $detalle["local_id"]=$data["local_id"];
		if($data["cdv_id"]==16 || $data["cdv_id"] == 42){
			$detalle["servicio_id"]=1;
			$detalle["tipo"]=3;
			$detalle["terminal_withdraw"]=$data["monto"];
			if ($data["cdv_id"] == 42) {
				$detalle["servicio_id"] = 17;
			}
		}elseif($data["cdv_id"]==21){
			$detalle["servicio_id"]=3;
			$detalle["tipo"]=4;
			$detalle["withdraw"]=$data["monto"];
		}else{
			$detalle=false;
		}
	}elseif($data["tipo_id"]==5){ //Depositado Web REVISADO
		// $detalle["created"]=$data["fecha"];
		// $detalle["local_id"]=$data["local_id"];
		if($data["cdv_id"]==16 || $data["cdv_id"] == 42){
			$detalle["servicio_id"]=1;
			$detalle["tipo"]=3;
			$detalle["deposit"]=$data["monto"];
			if ($data["cdv_id"] == 42) {
				$detalle["servicio_id"] = 17;
			}
		}else{
			$detalle=false;
		}
	}elseif($data["tipo_id"]==6){ //Retirado Web REVISADO
		// $detalle["created"]=$data["fecha"];
		// $detalle["local_id"]=$data["local_id"];
		if($data["cdv_id"]==16 || $data["cdv_id"] == 42){
			$detalle["servicio_id"]=1;
			$detalle["tipo"]=3;
			$detalle["withdraw"]=$data["monto"];
			if ($data["cdv_id"] == 42) {
				$detalle["servicio_id"] = 17;
			}
		}else{
			$detalle=false;
		}
	}else{
		$detalle=false;
	}

	if( in_array( $data["cdv_id"] , [33,35,37,34] ) )
	{/*torito ,  disashop , calimaco saldo web simulcast*/
		$detalle = 1;
	}

	if($detalle){
		$deta_to_db = data_to_db($detalle);
		// detalle_insert($deta_to_db);
		//print_r($deta_to_db);

		$pago_manual = array();
			$pago_manual["at_unique_id"]=$at_unique_id;
			$pago_manual["fecha_proceso"]=$datetime;
			$pago_manual["tipo_id"]=$data["tipo_id"];
			$pago_manual["motivo_id"]=$data["motivo_id"];
			$pago_manual["autorizacion_id"]=$data["autorizacion_id"];
			$pago_manual["usuario_id"]=isset($login["id"]) ? $login["id"] : $data["usuario_id"];

			$pago_manual["referencia"]=$data["referencia"];
			$pago_manual["fecha_pago"]=$data["fecha"];
			$pago_manual["local_id"]=$data["local_id"];
			$pago_manual["canal_de_venta_id"]=$data["cdv_id"];
			$pago_manual["monto"]=$data["monto"];
			$pago_manual["descripcion"]=$data["descripcion"];

			$pago_manual["estado"]=1;
		// print_r($pago_manual);
		$pago_manual_to_db = data_to_db($pago_manual);
		// print_r($pago_manual_to_db);
		if( $detalle != 1 )
		{
			if ($detalle["canal_de_venta_id"] == 42 || $detalle["canal_de_venta_id"] == 43) { // KURAX MVR o KURAX SBT
				if ( !(isset($detalle['withdraw']) || isset($detalle['deposit']) ) ) { //ignorammos los depositos y retiros 
					transacciones_consolidado_insert($detalle);	
				}else{
					detalle_insert($deta_to_db);
				}
			}else{
				detalle_insert($deta_to_db);
			}
			
		}
		pm_insert($pago_manual_to_db);
	}else{
		echo "no_pm";
	}
};

if(isset($_POST["delete_pago_manual"])){

	try {
		$id = $_POST["id_pago_manual"];
		$usuario_id= isset($login["id"]) ? $login["id"] : 0;
		if ($usuario_id == 0) {
			$result['status'] = 404;
			$result['message'] = 'Su sesión ha finalizado. Ingrese de nuevo al sistema.';
			$result['result'] = 0;
			echo json_encode($result);
			exit();
		}

		$query = "SELECT id, at_unique_id, canal_de_venta_id FROM tbl_pago_manual WHERE id = ".$id;
		$resp = $mysqli->query($query);
		$pago_manual = $resp->fetch_assoc();

		if (isset($pago_manual['at_unique_id'])) {

			if  ($pago_manual['canal_de_venta_id'] == 42 || $pago_manual['canal_de_venta_id'] == 43) { // en caso que no sera del MVR DE KURAX
				//anula el pago manual
				$query_update_pm = "UPDATE tbl_pago_manual SET estado = 0 WHERE id = ".$pago_manual['id'];
				$mysqli->query($query_update_pm);
				//registrar historico de cambios
				$query_insert_historico = "INSERT INTO tbl_pago_manual_historico (id_pago_manual, action, id_usuario, state, created_at, user_created_id)
				VALUES ('".$pago_manual['id']."', 'Delete', '".$usuario_id."', '1', '".date('Y-m-d H:i:s')."', '".$usuario_id."')";
				$mysqli->query($query_insert_historico);
				//anular en transacciones consolidados
				$query_update_pm = "UPDATE kx_transacciones_consolidado SET estado = 5 WHERE estado = 1 AND at_unique_id = '".$pago_manual['at_unique_id']."'";
				$mysqli->query($query_update_pm);
				$result['at_unique_id'] = $pago_manual['at_unique_id'];
	
				$result['status'] = 200;
				$result['message'] = 'Se ha eliminado el pago manual correctamente.';
				$result['result'] = $id;
				echo json_encode($result);
				exit();
			}

			if  (($pago_manual['canal_de_venta_id'] == 21 || $pago_manual['canal_de_venta_id'] == 16)) { // goldenrace y betconstruct
				//anula el pago manual
				$query_update_pm = "UPDATE tbl_pago_manual SET estado = 0 WHERE id = ".$pago_manual['id'];
				$mysqli->query($query_update_pm);
				//registrar historico de cambios
				$query_insert_historico = "INSERT INTO tbl_pago_manual_historico (id_pago_manual, action, id_usuario, state, created_at, user_created_id)
				VALUES ('".$pago_manual['id']."', 'Delete', '".$usuario_id."', '1', '".date('Y-m-d H:i:s')."', '".$usuario_id."')";
				$mysqli->query($query_insert_historico);

				//Eliminar en transacciones detalle
				searchDupliDetalle("'".$pago_manual['at_unique_id']."'");
	
				$result['status'] = 200;
				$result['message'] = 'Se ha eliminado el pago manual correctamente.';
				$result['result'] = $id;
				echo json_encode($result);
				exit();
			}

			$result['status'] = 404;
			$result['message'] = 'No se puede eliminar el registro debido al canal de venta seleccionado.';
			$result['result'] = $id;
			echo json_encode($result);
			exit();

			
		}
		$result['status'] = 404;
		$result['message'] = 'No se ha podido eliminar el registro.';
		$result['result'] = $id;
		echo json_encode($result);
		exit();
		
	} catch (\Exception $e) {
		$result['status'] = 404;
		$result['message'] = 'A ocurrido un error: '.$e->getMessage();
		$result['result'] = 0;
		echo json_encode($result);
		exit();
	}
	
	
};
?>
