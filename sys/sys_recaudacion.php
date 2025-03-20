<?php
function rec_data_to_db($d){
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
function liq_pro_action($post){
	global $mysqli;
	global $return;
	global $login;

	$proceso=false;
	$exists=false;
	$new_estado = 0;
	$finalizado = 0;
	if($post["opt"]=="cerrar"){
		$new_estado = 1;
		$proceso = $mysqli->query("SELECT at_unique_id, fecha_inicio, fecha_fin, servicio_id FROM tbl_transacciones_procesos WHERE at_unique_id = '".$post["id"]."'")->fetch_assoc();
		$exists = $mysqli->query("SELECT at_unique_id FROM tbl_transacciones_procesos WHERE estado = '1' AND fecha_inicio <= '".$proceso["fecha_inicio"]."' AND fecha_fin >= '".$proceso["fecha_fin"]."' AND servicio_id = '".$proceso["servicio_id"]."'")->fetch_assoc();
		if(!$exists){
			$borrar_procesos_abiertos_command = "UPDATE tbl_transacciones_procesos SET estado = '5' WHERE estado = '0' AND fecha_inicio >= '".$proceso["fecha_inicio"]."' AND fecha_fin <= '".$proceso["fecha_fin"]."' AND servicio_id = '".$proceso["servicio_id"]."'";
			$return["borrar_procesos_abiertos_command"]=$borrar_procesos_abiertos_command;
			$mysqli->query($borrar_procesos_abiertos_command);
			$return["borrar_procesos_abiertos_affected_rows"] = $mysqli->affected_rows;
			$return["borrar_procesos_abiertos_error"] = $mysqli->error;
		}
	}elseif($post["opt"]=="finalizar"){
		$new_estado = 1;
		$finalizado = 1;
		$proceso = $mysqli->query("SELECT at_unique_id, fecha_inicio, fecha_fin, servicio_id FROM tbl_transacciones_procesos WHERE at_unique_id = '".$post["id"]."'")->fetch_assoc();
		$exists = $mysqli->query("SELECT at_unique_id FROM tbl_transacciones_procesos WHERE estado = '1' AND finalizado = '1' AND fecha_inicio <= '".$proceso["fecha_inicio"]."' AND fecha_fin >= '".$proceso["fecha_fin"]."' AND servicio_id = '".$proceso["servicio_id"]."'")->fetch_assoc();

	}elseif($post["opt"]=="eliminar"){
		$new_estado = 5;
	}elseif($post["opt"]=="abrir"){
		$new_estado = 0;
		$abrir_deudas_command = "UPDATE tbl_deudas SET estado = '0' WHERE proceso_unique_id = '".$post["id"]."'";
		$mysqli->query($abrir_deudas_command);
	}
	
	$return["proceso"]=$proceso;	
	$return["exists"]=$exists;	

	if($exists){
	}else{
		$update_proceso_command = "UPDATE tbl_transacciones_procesos SET time_end = '".date('Y-m-d H:i:s')."', estado = '".$new_estado."', finalizado = '".$finalizado."' WHERE at_unique_id = '".$post["id"]."'";
		$return["update_proceso_command"]=$update_proceso_command;
		$mysqli->query($update_proceso_command);
		$return["update_proceso_affected_rows"] = $mysqli->affected_rows;
		$return["update_proceso_error"] = $mysqli->error;

		$update_cabeceras_command = "UPDATE tbl_transacciones_cabecera SET estado = '".$new_estado."' WHERE proceso_unique_id = '".$post["id"]."'";
		$return["update_cabeceras_command"]=$update_cabeceras_command;
		$mysqli->query($update_cabeceras_command);
		$return["update_cabeceras_affected_rows"] = $mysqli->affected_rows;
		$return["update_cabeceras_error"] = $mysqli->error;
	}


	if($post["opt"]=="finalizar" && !$exists){
		$proceso_unique_id = $post["id"];
		$deuda_command = "
			SELECT
				pro.at_unique_id AS proceso_unique_id,
				CAST(YEAR(cab.fecha) AS SIGNED) AS periodo_year,
				DATE_FORMAT(cab.fecha, '%m') AS periodo_mes,
				CONCAT(DATE_FORMAT(pro.fecha_inicio, '%d'),'-',DATE_FORMAT(pro.fecha_fin, '%d')) AS periodo_rango,
				DATE_FORMAT(pro.fecha_inicio,'%Y-%m-%d') AS periodo_inicio,
				DATE_FORMAT(pro.fecha_fin,'%Y-%m-%d') AS periodo_fin,
				CAST(CONCAT(DATE_FORMAT(pro.fecha_inicio, '%d'),DATE_FORMAT(pro.fecha_fin, '%d')) AS SIGNED) AS periodo_rango_int,
				cab.canal_de_venta_id AS canal_de_venta_id,
				l.id AS local_id,
				c.tipo_contrato_id,
				SUM(cab.total_freegames) AS part_fg,
				CAST(SUM(cab.pagado_en_otra_tienda) - SUM(cab.pagado_de_otra_tienda) AS DECIMAL(10,2)) AS dif_tk,
				SUM(cab.total_caja_web) AS web_total
			FROM
				tbl_transacciones_procesos pro
			LEFT JOIN tbl_transacciones_cabecera cab ON (cab.proceso_unique_id = pro.at_unique_id)
			LEFT JOIN tbl_locales l ON (l.id  = cab.local_id)
			LEFT JOIN tbl_contratos c ON (c.local_id = cab.local_id)
			WHERE
				pro.at_unique_id = '$proceso_unique_id'
			AND l.reportes_mostrar = '1'
			GROUP BY 
				periodo_year DESC,
				periodo_mes DESC,
				periodo_rango DESC,
				local_id ASC,
				canal_de_venta_id ASC";
		$deuda_query = $mysqli->query($deuda_command);
		if($mysqli_error = $mysqli->error){
			print_r($mysqli_error);
			echo "\n";
			echo $deuda_command;
			exit();
		}
		$temp_arr = array();
		while($d=$deuda_query->fetch_assoc()){
			$tmp = array();
				$tmp["proceso_unique_id"]=$d["proceso_unique_id"];
				$tmp["fecha_ingreso"]=date("Y-m-d H:i:s");
				$tmp["periodo_year"]=$d["periodo_year"];
				$tmp["periodo_mes"]=$d["periodo_mes"];
				$tmp["periodo_rango"]=$d["periodo_rango"];
				$tmp["periodo_inicio"]=$d["periodo_inicio"];
				$tmp["periodo_fin"]=$d["periodo_fin"];
				$tmp["periodo_rango_int"]=$d["periodo_rango_int"];
				$tmp["canal_de_venta_id"]=$d["canal_de_venta_id"];
				$tmp["local_id"]=$d["local_id"];
				$tmp["saldo"]=0;
				$tmp["estado"]=1;

			switch ($d["canal_de_venta_id"]) {
				case 15:
				break;
				case 16:
					$part_arr = $tmp;
					$part_arr["monto"]=$d["part_fg"];
					$temp_arr["part"][]=$part_arr;

					if($d["tipo_contrato_id"]==1){
						$tk_arr = $tmp;
						$tk_arr["monto"]=$d["dif_tk"];
						$temp_arr["dif_tk"][]=$tk_arr;
					}elseif($d["tipo_contrato_id"]==2){

					}else{
						$tk_arr = $tmp;
						$tk_arr["monto"]=$d["dif_tk"];
						$temp_arr["dif_tk"][]=$tk_arr;						
					}

					$web_arr = $tmp;
					$web_arr["monto"]=$d["web_total"];
					$temp_arr["web"][]=$web_arr;
				break;
				case 17:
					$part_arr = $tmp;
					$part_arr["monto"]=$d["part_fg"];
					$temp_arr["part"][]=$part_arr;

					if($d["tipo_contrato_id"]==1){
						$tk_arr = $tmp;
						$tk_arr["monto"]=$d["dif_tk"];
						$temp_arr["dif_tk"][]=$tk_arr;
					}elseif($d["tipo_contrato_id"]==2){

					}else{
						$tk_arr = $tmp;
						$tk_arr["monto"]=$d["dif_tk"];
						$temp_arr["dif_tk"][]=$tk_arr;						
					}
				break;
				case 18:
				break;
				case 19:
					$part_arr = $tmp;
					$part_arr["monto"]=$d["part_fg"];
					$temp_arr["part"][]=$part_arr;

					if($d["tipo_contrato_id"]==1){
						$tk_arr = $tmp;
						$tk_arr["monto"]=$d["dif_tk"];
						$temp_arr["dif_tk"][]=$tk_arr;
					}elseif($d["tipo_contrato_id"]==2){

					}else{
						$tk_arr = $tmp;
						$tk_arr["monto"]=$d["dif_tk"];
						$temp_arr["dif_tk"][]=$tk_arr;						
					}
				break;
				case 20:
				break;
				case 21:
					$part_arr = $tmp;
					$part_arr["monto"]=$d["part_fg"];
					$temp_arr["part"][]=$part_arr;
				break;
			}
		}

		$deudas_tipos_arr = array();
			$deudas_tipos_command = "SELECT id,codigo FROM tbl_deudas_tipos WHERE estado = '1'";
			$deudas_tipos_query = $mysqli->query($deudas_tipos_command);
			while ($dt = $deudas_tipos_query->fetch_assoc()) {
				$deudas_tipos_arr[$dt["id"]]=$dt["codigo"];
			}

		$deudas_arr = array();
		$return["num_update"]=0;
		$return["num_insert"]=0;
		$return["num_nothing"]=0;
		$mysqli->query("START TRANSACTION");
		foreach ($temp_arr as $tipo => $tmp_v) {
			foreach ($tmp_v as $v_k => $v) {
				$v["tipo"]=$tipo;
				$v["tipo_id"]=array_search($tipo, $deudas_tipos_arr);
				$v["at_unique_id"]=md5($v["proceso_unique_id"].$v["local_id"].$v["canal_de_venta_id"].$v["tipo"]); //RE-Finalizar
				// $v["at_unique_id"]=md5($v["periodo_year"].$v["periodo_mes"].$v["periodo_rango"].$v["local_id"].$v["canal_de_venta_id"].$v["tipo"]);
				$data_to_db=rec_data_to_db($v);
				$insert_command = "INSERT INTO tbl_deudas";
					$insert_command.="(";
					$insert_command.=implode(",", array_keys($data_to_db));
					$insert_command.=")";
					$insert_command.=" VALUES ";
					$insert_command.="(";
					$insert_command.=implode(",", $data_to_db);
					$insert_command.=")";
					$insert_command.=" ON DUPLICATE KEY UPDATE ";
					$uqn=0;
					$no_update_array = array("fecha_ingreso");
					foreach ($data_to_db as $key => $value) {
						if(!in_array($key, $no_update_array)){
							if($uqn>0) { $insert_command.=","; }
							$insert_command.= $key." = VALUES(".$key.")";
							$uqn++;
						}
					}
				$mysqli->query($insert_command);
				if($mysqli_error = $mysqli->error){
					print_r($mysqli_error);
					echo "\n";
					echo $insert_command;
					exit();
				}else{
					$affected_rows = $mysqli->affected_rows;
					if($affected_rows==2){
						$return["num_update"]++;
					}elseif($affected_rows==1){
						$return["num_insert"]++;
					}else{
						$return["num_nothing"]++;
					}
				}
			}
		}
		$mysqli->query("COMMIT");
	}
}
function add_pago_manual($post){
	global $mysqli;
	global $return;
	global $login;
	//print_r($post);
	$return["_POST"]=$post;

	$ticket_command = "SELECT at_unique_id, local_id, canal_de_venta_id, ticket_id, servicio_id, tipo, moneda_id
						FROM tbl_transacciones_detalle 
						WHERE ticket_id = '".$post["ref_bet_id"]."' 
						AND servicio_id = '".$post["ref_servicio_id"]."' 
						AND tipo = '1'";
	$ticket = $mysqli->query($ticket_command)->fetch_assoc();
	//print_r($ticket);
	$return["ticket"]=$ticket;

	$otras_modificaciones_command = "SELECT COUNT(id) AS num
									FROM tbl_transacciones_detalle
									WHERE ticket_id LIKE '".$ticket["ticket_id"]."_m%'
									AND servicio_id = '".$post["ref_servicio_id"]."' 
									AND tipo = '1'";
	$otras_modificaciones = $mysqli->query($otras_modificaciones_command)->fetch_assoc();


	$new_ticket_id = $ticket["ticket_id"]."_m".$otras_modificaciones["num"];

	$new_at_unique_id = md5($ticket["ticket_id"].$ticket["tipo"].$new_ticket_id);
		$detalle["ticket_id"]=$new_ticket_id;
		$detalle["at_unique_id"]=$new_at_unique_id;
		$detalle["servicio_id"]=$ticket["servicio_id"];
		$detalle["tipo"]=$ticket["tipo"];
		$detalle["canal_de_venta_id"]=$ticket["canal_de_venta_id"];
		$detalle["local_id"]=$ticket["local_id"];
		$detalle["moneda_id"]=$ticket["moneda_id"];

		$detalle["apostado"]=$post["apostado"];
		$detalle["ganado"]=$post["ganado"];
		$detalle["created"]=$post["created"];
		$detalle["paid_local_id"]=$post["paid_local_id"];
		$detalle["paid_day"]=$post["paid_day"];
		$detalle["paid_canal_de_venta_id"]=$post["paid_canal_de_venta_id"];
	$deta_to_db = rec_data_to_db($detalle);
	detalle_insert($deta_to_db);
	//print_r($detalle);
	$return["detalle"]=$detalle;


	$proceso_id = md5(date("c").$detalle["ticket_id"]);
	$tbl_transacciones_procesos_command = "INSERT INTO tbl_transacciones_procesos (at_unique_id, fecha, servicio_id, tipo, bet_id_m, `detalles_insertados`, `detalles_updateados`, `detalles_nothing`, `descripcion`, `usuario_id`, `bet_id`, `estado`) VALUES ('".$proceso_id."','".date("Y-m-d H:i:s")."','".$detalle["servicio_id"]."','pago_manual','".$detalle["ticket_id"]."','".$return["detalles_insertados"]."','".$return["detalles_updateados"]."','".$return["detalles_nothing"]."',".value_to_db($post["descripcion"]).",'".$login["id"]."','".$ticket["ticket_id"]."','1')";
	$return["tbl_transacciones_procesos_command"]=$tbl_transacciones_procesos_command;
	$mysqli->query($tbl_transacciones_procesos_command);
}
function del_pago_manual($post){
	global $mysqli;
	global $return;
	global $login;

	$detalle_command = "SELECT at_unique_id,servicio_id, canal_de_venta_id FROM tbl_transacciones_detalle WHERE ticket_id = (SELECT bet_id_m FROM tbl_transacciones_procesos WHERE at_unique_id = '".$post."')";
	$detalle = $mysqli->query($detalle_command)->fetch_assoc();

	$update_detalle_command = "UPDATE tbl_transacciones_detalle SET servicio_id = '".$detalle["servicio_id"]."9999', canal_de_venta_id = '".$detalle["canal_de_venta_id"]."9999', tipo = '5' WHERE at_unique_id = '".$detalle["at_unique_id"]."'";
	$mysqli->query($update_detalle_command);
	// echo $update_detalle_command;
	
	$update_proceso_command = "UPDATE tbl_transacciones_procesos SET estado = '5' WHERE at_unique_id = '".$post."'";
	$mysqli->query($update_proceso_command);
	// echo $update_proceso_command;

	$return["ok"]=true;
}
function recaudacion_add_trans_terminal($post){
	global $mysqli;
	global $return;
	global $login;
	// print_r($post);

	$at_unique_id = md5(time().$post["local_id"].$post["canal_de_venta_id"].$post["created"]);

	$detalle = array();
		$detalle["at_unique_id"]=$at_unique_id;
		$detalle["servicio_id"]=$post["servicio_id"];
		$detalle["tipo"]=$post["tipo"];
		$detalle["canal_de_venta_id"]=$post["canal_de_venta_id"];
		$detalle["local_id"]=$post["local_id"];
		$detalle["moneda_id"]=1;
		$detalle["ticket_id"] = $at_unique_id;
		$detalle["state"] = "ingreso_manual";
		$detalle["created"]=$post["created"];
		$detalle["paid_day"]=$post["created"];
		$detalle["terminal_income"]=$post["terminal_income"];
		$detalle["terminal_withdraw"]=$post["terminal_withdraw"];
	$deta_to_db = rec_data_to_db($detalle);
	detalle_insert($deta_to_db);
	$return["detalle"]=$detalle;


	$proceso_id = md5(time().$detalle["at_unique_id"]);
	$tbl_transacciones_procesos_command = "INSERT INTO tbl_transacciones_procesos (at_unique_id, fecha, servicio_id, tipo, detalles_insertados, detalles_updateados, detalles_nothing, descripcion, usuario_id, bet_id, bet_id_m, estado) VALUES ('".$proceso_id."','".date("Y-m-d H:i:s")."','".$detalle["servicio_id"]."','transaccion_manual','".$return["detalles_insertados"]."','".$return["detalles_updateados"]."','".$return["detalles_nothing"]."',".value_to_db($post["descripcion"]).",'".$login["id"]."','".$detalle["ticket_id"]."','".$detalle["ticket_id"]."','1')";
	$return["tbl_transacciones_procesos_command"]=$tbl_transacciones_procesos_command;
	$mysqli->query($tbl_transacciones_procesos_command);


	// print_r($detalle);
}
function recaudacion_add_trans_bancaria($post){
	global $mysqli;
	global $return;
	global $login;

	if(array_key_exists("at_unique_id", $post)){
		$post["ultima_edicion"]=date("Y-m-d H:i:s");
	}else{
		$post["at_unique_id"] = md5($post["banco_id"].$post["numero_movimiento"]);
		$post["fecha_ingreso"]=date("Y-m-d H:i:s");
		$post["usuario_id"]=$login["id"];
	}
	if(array_key_exists("local_id", $post)){
		$post["local_id"]=implode(",", $post["local_id"]);
	}

	// $trans["banco_id"]=$banco_id;
	// $trans["moneda_id"]=$post["moneda_id"];
	$post["insert_tipo"]="manual";
	$post["estado"]=1;
	// $trans["fecha_ingreso"]=date("Y-m-d H:i:s");
	// $trans["usuario_id"]=$login["id"];

	$data_to_db=array();
	$nulls=array("null","",false);
	foreach ($post as $k => $v) {
		if($v===0){
			$data_to_db[$k]=$v;
		}elseif(in_array($v, $nulls)){
			$data_to_db[$k]="NULL";
		}else{
			if(is_float($v)){
				$data_to_db[$k]="'".$v."'";
			}elseif(is_int($v)){
				$data_to_db[$k]=$v;
			}else{
				// $v=str_replace(",", ".", $v);
				$data_to_db[$k]="'".trim($mysqli->real_escape_string($v))."'";
			}
		}
	}
	// $post=$

	// $data_to_db = rec_data_to_db($post);

	$command = "INSERT INTO tbl_repositorio_transacciones_bancarias";
	$command.="(";
	$command.=implode(",", array_keys($data_to_db));
	$command.=")";
	$command.=" VALUES ";
	$command.="(";
	$command.=implode(",", $data_to_db);
	$command.=")";
	$command.=" ON DUPLICATE KEY UPDATE ";
	$uqn=0;
	foreach ($data_to_db as $key => $value) {
		if($uqn>0) { $command.=","; }
		$command.= $key." = VALUES(".$key.")";
		$uqn++;
	}
	$mysqli->query($command);
	if($mysqli->error){
		// $return["ERROR_MYSQL"]=$mysqli->error;
		print_r($mysqli->error);
		echo "\n";
		echo $command;
		exit();
	}
}
function recaudacion_hide_trans_bancaria($post){
	global $mysqli;
	global $return;
	$command = "UPDATE tbl_repositorio_transacciones_bancarias";
	$command.=" SET estado = '".$post["estado"]."'";
	$command.=" WHERE at_unique_id = '".$post["id"]."' ";
	$mysqli->query($command);
	if($mysqli->error){
		// $return["ERROR_MYSQL"]=$mysqli->error;
		print_r($mysqli->error);
		echo "\n";
		echo $command;
		exit();
	}
}
function recaudacion_process_trans_bancaria($post){
	global $mysqli;
	global $return;
	global $login;
	// sleep(1);
	// print_r($post); exit();
	$folder = "../files_bucket/";
	$file_data = $mysqli->query("SELECT archivo FROM tbl_archivos WHERE id = '".$post["file_id"]."'")->fetch_assoc();
	$banco_id = $post["banco_id"];

	$csv_file = (file_get_contents($folder.$file_data["archivo"]));
	$csv_arr = str_getcsv($csv_file,"\n");
	$process_continue = true;

	$banco_ok = array();
	$banco_ok[12]="F.Operac.,F.Valor,Referencia,Importe,ITF,Num.Mvto";//bbva
	$banco_ok[15]="Nro.,Fecha,Trans.,Documento,Oficina,Cargo,Abono";//bn
	$banco_ok[13]="Fecha de Op.,Fecha de Proc.,Movimiento,Detalle,Cod. de Operación,Canal,Cod. de Ubicación,Cargos,Abonos,Saldo Contable";//ibk

	$trans_arr = array();

	if(array_key_exists($banco_id, $banco_ok)){
		if(array_key_exists(0, $csv_arr)){
			if(str_replace(array(" ","\xef\xbb\xbf"),"",$csv_arr[0])==str_replace(array(" ","\xef\xbb\xbf"),"",$banco_ok[$banco_id])){
				foreach ($csv_arr as $csv_k => $csv_val) {
					if($csv_k > 0){
						// $csv_val = str_replace(array("S/"), "", $csv_val);
						$csv_val_arr = str_getcsv($csv_val);
						$trans_arr[]=$csv_val_arr;
					}
				}
			}else{
				$return["error"]="archivo_banco";
				$return["error_msg"]="El archivo no corresponde al banco.";
				$return["error_csv_header"]=$csv_arr[0];
				$return["error_banco_header"]=$banco_ok[$banco_id];
				$process_continue=false;
			}
		}else{
			$return["error"]="archivo_vacio";
			$return["error_msg"]="El archivo está vacio.";
			$process_continue=false;
		}
	}else{
		$return["error"]="banco_no_existe";
		$return["error_msg"]="El banco no existe.";
		$process_continue=false;
	}
	$trans_to_db = array();
	if($process_continue){
		// echo "continue";
		foreach ($trans_arr as $key => $val) {
			$trans = array();
			$trans["banco_id"]=$banco_id;
			$trans["moneda_id"]=$post["moneda_id"];
			$trans["insert_tipo"]="import";
			$trans["estado"]=1;
			$trans["fecha_ingreso"]=date("Y-m-d H:i:s");
			$trans["ultima_edicion"]=date("Y-m-d H:i:s");
			$trans["usuario_id"]=$login["id"];
			if($banco_id==12){//bbva

				// DD/MM/YYYY -> YYYY-MM-DD

				$year = substr($val[0],6,4);
				$month = substr($val[0],3,2);
				$day = substr($val[0],0,2);
				$trans["fecha_operacion"]=$year."-".$month."-".$day;
				// $trans["fecha_operacion"]="2017-02-02";

				if(preg_match("/(\d{4})\-(\d{2})\-(\d{2})$/", $trans["fecha_operacion"])){
					$trans["fecha_valor"]=substr($val[1],6,4)."-".substr($val[1],3,2)."-".substr($val[1],0,2);
					$trans["referencia"]=$val[2];
					$trans["importe"]=str_replace(array(","), "", $val[3]);

					$val[4] = str_replace(array(","), "", $val[4]);
					if(!is_numeric($val[4])){$val[4]=0;}
					$trans["itf"]=$val[4];
					$trans["numero_movimiento"]=$val[5];
					$trans["at_unique_id"]=md5($banco_id.$trans["fecha_operacion"].$trans["numero_movimiento"]);
					$trans["restante"]=$trans["importe"];
				}else{
					$return["error"]="no_data";
					$return["error_msg"]="Formato incorrecto:<br> ".$val[0];
					$return["error_msg"].="<br>Debe ser: DD/MM/YYYY";
					$process_continue = false;
					break;
				}

			}
			if($banco_id==15){//bn
				$trans["numero_movimiento"]=$val[0];
				$trans["fecha_operacion"]=substr($val[1],0,4)."-".substr($val[1],5,2)."-".substr($val[1],8,2);
				$trans["movimiento"]=$val[2];
				$trans["referencia"]=$val[3];
				$trans["cod_ubicacion"]=$val[4];
				$trans["cargo"]=$val[5];
				$trans["abono"]=$val[6];
				$trans["at_unique_id"]=md5($banco_id.$trans["fecha_operacion"].$trans["numero_movimiento"]);
				$trans["restante"]=$trans["abono"];
			}
			if($banco_id==13){//ibk
				$trans["fecha_operacion"]=substr($val[0],6,4)."-".substr($val[0],3,2)."-".substr($val[0],0,2);
				if($val[1]!="-"){
					$trans["fecha_valor"]=substr($val[1],6,4)."-".substr($val[1],3,2)."-".substr($val[1],0,2);
				}
				$trans["movimiento"]=$val[2];
				$trans["referencia"]=$val[3];
				$trans["numero_movimiento"]=$val[4];
				$trans["canal"]=$val[5];
				$trans["cod_ubicacion"]=$val[6];
				$trans["cargo"]=str_replace(array("S/",","), "", $val[7]);
				$trans["abono"]=str_replace(array("S/",","), "", $val[8]);
				$trans["saldo_contable"]=str_replace(array("S/",","), "", $val[9]);
				$trans["at_unique_id"]=md5($banco_id.$trans["fecha_operacion"].$trans["numero_movimiento"].$trans["movimiento"]);
				$trans["restante"]=$trans["abono"];
			}
			$trans["usado"]=0;
			$trans_to_db[]=$trans;
		}
	}
	// $process_continue = false; //TEST
	if($process_continue){
		$nulls=array("null","",false);
		$return["ok"]=true;
		$return["num_insert"]=0;
		$return["num_update"]=0;
		$return["num_nothing"]=0;
		$mysqli->query("START TRANSACTION");
		foreach ($trans_to_db as $trans_key => $trans_data) {
			$data_to_db=array();

			foreach ($trans_data as $k => $v) {
				if($v===0){
					$data_to_db[$k]=$v;
				}elseif(in_array($v, $nulls)){
					$data_to_db[$k]="NULL";
				}else{
					if(is_float($v)){
						$data_to_db[$k]="'".$v."'";
					}elseif(is_int($v)){
						$data_to_db[$k]=$v;
					}else{
						$data_to_db[$k]="'".trim($mysqli->real_escape_string($v))."'";
					}
				}
			}

			$command = "INSERT INTO tbl_repositorio_transacciones_bancarias";
			$command.="(";
			$command.=implode(",", array_keys($data_to_db));
			$command.=")";
			$command.=" VALUES ";
			$command.="(";
			$command.=implode(",", $data_to_db);
			$command.=")";
			$command.=" ON DUPLICATE KEY UPDATE ";

			$uqn=0;
			$no_update_array = array("local_id","comentario","fecha_ingreso","usado","restante");
			foreach ($data_to_db as $key => $value) {
				if(!in_array($key, $no_update_array)){
					if($uqn>0) { $command.=","; }
					$command.= $key." = VALUES(".$key.")";
					$uqn++;
				}
			}
			$mysqli->query($command);
			if($mysqli->error){
				print_r($mysqli->error);
				echo "\n";
				echo $command;
				exit();
			}
			$affected_rows = $mysqli->affected_rows;
			if($affected_rows==2){
				// $return["deta_insert_command_UPDATE"][]=$command;
				$return["num_update"]++;
			}elseif($affected_rows==1){
				// $return["deta_insert_command_INSERT"][]=$command;
				$return["num_insert"]++;
			}else{
				// $return["deta_insert_command_NOTHING"][]=$command;
				$return["num_nothing"]++;
			}
		}
		$mysqli->query("COMMIT");
	}else{

	}
	// print_r($trans_to_db);
}
function recaudacion_div_trans_bancaria($post){
	// print_r($post);
	global $mysqli;
	global $return;
	global $login;

	if(array_key_exists("data", $post)){
		if(array_key_exists("comentario", $post["data"])){
			$update_command = "UPDATE tbl_repositorio_transacciones_bancarias SET comentario = '".$post["data"]["comentario"]."' WHERE at_unique_id = '".$post["at_unique_id"]."'";
			$mysqli->query($update_command);
			// echo $update_command; echo "\n";
		}
	}
	if(array_key_exists("div", $post)){
		foreach ($post["div"] as $key => $div) {
			$div["trans_unique_id"]=$post["at_unique_id"];
			$div["estado"]=0;
			$div["at_unique_id"]=md5($key.$div["trans_unique_id"].$div["local_id"].$div["monto"]);
			$data_to_db = rec_data_to_db($div);
			$insert_div_command = "INSERT INTO tbl_transaccion_bancaria_division";
				$insert_div_command.="(";
				$insert_div_command.=implode(",", array_keys($data_to_db));
				$insert_div_command.=")";
				$insert_div_command.=" VALUES ";
				$insert_div_command.="(";
				$insert_div_command.=implode(",", $data_to_db);
				$insert_div_command.=")";
			// echo $insert_div_command; echo "\n";
				$insert_div_command.=" ON DUPLICATE KEY UPDATE ";
				// $insert_div_command.=" id = id";
				$uqn=0;
				foreach ($data_to_db as $key => $value) {
					if($uqn>0) { $insert_div_command.=","; }
					$insert_div_command.= $key." = VALUES(".$key.")";
					$uqn++;
				}
				$mysqli->query($insert_div_command);
				if($mysqli->error){
					// $return["ERROR_MYSQL"]=$mysqli->error;
					print_r($mysqli->error);
					echo "\n";
					echo $insert_div_command;
					exit();
				}
		}
	}
}
function recaudacion_frac_add($post){
	global $mysqli;
	global $return;
	global $login;
	// print_r($post)
	$return["_POST"]=$post;
	print_r($post);


	$frac = array();
	$frac["proceso_unique_id"]=$post["proceso_unique_id"];
	$frac["local_id"]=$post["local_id"];
	$frac["monto"]=$post["monto"];
	$frac["num_cuotas"]=$post["num_cuotas"];
	$frac["facturacion_ciclo"]=$post["facturacion_ciclo"];
	$frac["fecha_fraccionamiento"]=date("Y-m-d H:i:s");
	$frac["at_unique_id"]=md5($frac["proceso_unique_id"].$frac["local_id"].$frac["fecha_fraccionamiento"]);

	$frac_to_db = rec_data_to_db($frac);

	$frac_command = "INSERT INTO tbl_fraccionamientos";
		$frac_command.="(";
		$frac_command.=implode(",", array_keys($frac_to_db));
		$frac_command.=")";
		$frac_command.=" VALUES ";
		$frac_command.="(";
		$frac_command.=implode(",", $frac_to_db);
		$frac_command.=")";

	print_r($frac_command);
	if(array_key_exists("cuotas", $post)){
		foreach ($post["cuotas"] as $c_key => $c_val) {
			$cuota = array();
			$cuota["fraccionamiento_unique_id"]=$frac["at_unique_id"];
			$cuota["num"]=$c_val["num"];
			$cuota["monto"]=$c_val["monto"];
			$cuota["fecha_vencimiento"]=substr($c_val["fecha"], 6,4)."-".substr($c_val["fecha"], 3,2)."-".substr($c_val["fecha"], 0,2);
			$cuota["estado"]=0;
			$cuota["at_unique_id"]=md5($cuota["fraccionamiento_unique_id"].$cuota["num"]);

			$cuota_to_db = rec_data_to_db($frac);

			$cuota_command = "INSERT INTO tbl_fraccionamientos";
				$cuota_command.="(";
				$cuota_command.=implode(",", array_keys($cuota_to_db));
				$cuota_command.=")";
				$cuota_command.=" VALUES ";
				$cuota_command.="(";
				$cuota_command.=implode(",", $cuota_to_db);
				$cuota_command.=")";
			print_r($cuota_command);;
		}
	}
}
function recaudacion_transbanc_save($post){
	global $mysqli;
	global $return;
	global $login;
	// print_r($post);
	$trans_arr = array();
	$deudas_arr = array();
	$pagos_arr = array();

	$trans_command = "
		SELECT
			t.at_unique_id,
			IF(t.banco_id = 12, t.importe,t.abono) AS importe,
			t.usado,
			t.restante
		FROM tbl_repositorio_transacciones_bancarias t 
		WHERE t.at_unique_id IN ('".implode("','", explode(",",$post["trans_unique_id"]))."')";
	$trans_query = $mysqli->query($trans_command);
	if($mysqli->error){
		echo $mysqli->error;
		echo $trans_command;
		exit();
	}
	$total_importe = 0;
	$total_usado = 0;
	$total_restante = 0;
	while ($t=$trans_query->fetch_assoc()) {
		$trans_arr[$t["at_unique_id"]]=$t;
		$total_importe+=$t["importe"];
		$total_usado+=$t["usado"];
		$total_restante+=$t["restante"];
	}
	// echo '$trans_arr'; echo "\n"; print_r($trans_arr);
	$return["trans_arr_b4"]=$trans_arr;
	// echo "\n"; echo "\n";
	$deudas_arr = array();
	$pagos = array();
	foreach ($post["locales"] as $local_id => $local) {
		foreach ($local["deudas"] as $deuda_k => $deuda_v) {
			// $deuda_update=array();
			// $deuda_update = $deuda_v;
				// $deuda_update["abono"]
			foreach ($trans_arr as $trans_unique_id => $trans) {
				$pago = array();
					$pago["trans_unique_id"]=$trans_unique_id;
					
					$pago["fecha_ingreso"]=date("Y-m-d H:i:s");
					$pago["periodo_year"]=$post["periodo_year"];
					$pago["periodo_mes"]=$post["periodo_mes"];
					$pago["periodo_rango"]=$post["periodo_rango"];
					$pago["periodo_inicio"]=$pago["periodo_year"]."-".$pago["periodo_mes"]."-".strstr($pago["periodo_rango"],"-",true);
					$pago["periodo_fin"]=$pago["periodo_year"]."-".$pago["periodo_mes"]."-".substr(strstr($pago["periodo_rango"],"-"),1);
					$pago["periodo_rango_int"]=intval(str_replace("-", "", $post["periodo_rango"]));

					$pago["local_id"]=$local_id;
					$pago["deuda_tipo_id"]=$deuda_v["deuda_tipo_id"];
					$pago["pago_tipo_id"]=1; //trans/Trasferencia Bancaria

					$pago["estado"]=1;

					// $pago["deuda_tipo"]=$deuda_v["deuda_tipo"];
					// $pago["deuda_amort"]=$local["deudas"][$deuda_k]["amort"];
				if($trans_arr[$trans_unique_id]["restante"] >= $local["deudas"][$deuda_k]["amort"]){
					$pago_amort = $local["deudas"][$deuda_k]["amort"];
					$local["deudas"][$deuda_k]["amort"] -= $pago_amort;
					// $local["deudas"][$deuda_k]["amort"] -= $local["deudas"][$deuda_k]["amort"];
					// $trans_arr[$trans_unique_id]["usado"] += $local["deudas"][$deuda_k]["amort"];

				}else{
					$pago_amort = $trans_arr[$trans_unique_id]["restante"];
					$local["deudas"][$deuda_k]["amort"] -= $pago_amort;
					// $local["deudas"][$deuda_k]["amort"] -= $trans_arr[$trans_unique_id]["restante"];
					// $trans_arr[$trans_unique_id]["usado"] += $trans_arr[$trans_unique_id]["restante"];
				}
				// $trans_arr[$trans_unique_id]["restante"] -= $pago_amort;
				$trans_arr[$trans_unique_id]["usado"] += $pago_amort; 
				$trans_arr[$trans_unique_id]["restante"] = round($trans_arr[$trans_unique_id]["importe"] - $trans_arr[$trans_unique_id]["usado"],2);
				// $pago["amort_restante"]=$local["deudas"][$deuda_k]["amort"];

				$pago["abono"] = $pago_amort;
				$pago["at_unique_id"]=md5($pago["trans_unique_id"].$pago["periodo_year"].$pago["periodo_mes"].$pago["periodo_rango"].$pago["local_id"].$pago["deuda_tipo_id"]);
				if($pago_amort>0){
					// $trans_arr[$trans_unique_id]["usado"] += $pago_amort;
					$pagos_arr[]=$pago;
				}
			}
			// $deudas_arr[]=$deuda_update;
		}
	}
	
	// echo '$pagos_arr'; echo "\n"; print_r($pagos_arr);
	// echo '$trans_arr'; echo "\n"; print_r($trans_arr);
	$return["pagos_arr"]=$pagos_arr;
	$return["trans_arr"]=$trans_arr;

	$mysqli->query("START TRANSACTION");
		foreach ($trans_arr as $key => $v) {
			$data_to_db=rec_data_to_db($v);
			$update_command = "UPDATE tbl_repositorio_transacciones_bancarias";
			$update_command.=" SET ";
			$uqn=0;
			$no_update_array = array("at_unique_id","importe");
			foreach ($data_to_db as $data_k => $data_v) {
				if(!in_array($data_k, $no_update_array)){
					if($uqn>0) { $update_command.=","; }
					$update_command.= $data_k." = ".$data_v."";
					$uqn++;
				}
			}
			$update_command.=" WHERE at_unique_id = ".$data_to_db["at_unique_id"]."";
			$mysqli->query($update_command);
			if($mysqli_error = $mysqli->error){
				print_r($mysqli_error);
				echo "\n";
				echo $update_command;
				exit();
			}else{
				// $affected_rows = $mysqli->affected_rows;
				// if($affected_rows==2){
				// 	$return["num_update"]++;
				// }elseif($affected_rows==1){
				// 	$return["num_insert"]++;
				// }else{
				// 	$return["num_nothing"]++;
				// }
			}
			// echo $update_command; echo "\n";
		}
		foreach ($pagos_arr as $key => $v) {
			$data_to_db=rec_data_to_db($v);
			$insert_command = "INSERT INTO tbl_pagos";
				$insert_command.="(";
				$insert_command.=implode(",", array_keys($data_to_db));
				$insert_command.=")";
				$insert_command.=" VALUES ";
				$insert_command.="(";
				$insert_command.=implode(",", $data_to_db);
				$insert_command.=")";
				$insert_command.=" ON DUPLICATE KEY UPDATE ";
				$uqn=0;
				$no_update_array = array("at_unique_id");
				foreach ($data_to_db as $key => $value) {
					if(!in_array($key, $no_update_array)){
						if($uqn>0) { $insert_command.=","; }
						$insert_command.= $key." = VALUES(".$key.")";
						$uqn++;
					}
				}
			$mysqli->query($insert_command);
			if($mysqli_error = $mysqli->error){
				print_r($mysqli_error);
				echo "\n";
				echo $insert_command;
				exit();
			}else{
				// $affected_rows = $mysqli->affected_rows;
				// if($affected_rows==2){
				// 	$return["num_update"]++;
				// }elseif($affected_rows==1){
				// 	$return["num_insert"]++;
				// }else{
				// 	$return["num_nothing"]++;
				// }
			}
		}
	$mysqli->query("COMMIT");
}
function sec_rtb_assig_save($post){
	global $mysqli;
	global $return;
	global $login;

	foreach ($post["trans"] as $key => $trans_unique_id) {
		$reset_command = "UPDATE tbl_transaccion_bancaria_local SET estado = '0' WHERE trans_unique_id = '".$trans_unique_id."'";
		$mysqli->query($reset_command);
		if($mysqli->error){
			print_r($mysqli->error); exit();
		}
		// echo $reset_command; echo "\n";
		if(array_key_exists("locales", $post)){
			foreach ($post["locales"] as $key => $local_id) {
				$insert_command = "INSERT INTO tbl_transaccion_bancaria_local (trans_unique_id,local_id,fecha_ingreso,usuario_id,estado) 
									VALUES ('".$trans_unique_id."','".$local_id."','".date("Y-m-d H:i:s")."','".$login["id"]."','1')";
				$mysqli->query($insert_command);
				if($mysqli->error){
					print_r($mysqli->error); exit();
				}
				// echo $insert_command; echo "\n";
			}			
		}
	}
}
?>
