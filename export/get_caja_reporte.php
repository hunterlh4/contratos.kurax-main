<?php
	include("../sys/db_connect.php");
	include("../sys/sys_login.php");
	date_default_timezone_set("America/Lima");
	$post = array();
	$post = array("sec_caja_export" => array(
			"local_id" => $_POST['local_id'],
			"fecha_inicio" => $_POST['fecha_inicio'],
			"fecha_fin" => $_POST['fecha_fin'],
			"group_by" => $_POST['group_by']
		)
	);			

	if(isset($post["sec_caja_export"])){
		$locales = array();
		$local_titulo = array();
		$sql_command = "SELECT id,nombre FROM tbl_locales";
		$sql_query = $mysqli->query($sql_command);
		while($itm=$sql_query->fetch_assoc()){
			$locales[$itm["id"]] = str_replace(" ","_",strtolower($itm["nombre"]));
			$local_titulo[$itm["id"]] = strtoupper($itm["nombre"]);
		}

		/********************************************** DATA ******************************************/
			$get_data = $post["sec_caja_export"];
			// print_r($get_data);
			// exit();
			$local_id = $get_data["local_id"];
			$fecha_inicio = $get_data["fecha_inicio"];
			$fecha_inicio_pretty = date("d-m-Y",strtotime($get_data["fecha_inicio"]));
			// $fecha_fin = $get_data["fecha_fin"];
			$fecha_fin = date("Y-m-d",strtotime($get_data["fecha_fin"]." +1 day"));
			$fecha_fin_pretty = date("d-m-Y",strtotime($get_data["fecha_fin"]));
			// $fecha_inicio = $get_data["year"]."-".$get_data["month"];
			// $fecha_fin = $get_data["year"]."-".$get_data["month"];
			// $local_id = 203;
			// $local_id = 328;
			$local = $mysqli->query("SELECT l.id, l.nombre FROM tbl_locales l WHERE l.id = '".$local_id."'")->fetch_assoc();
			$local["caja_config"]=array();
			$local_caja_config_command = "SELECT campo, valor FROM tbl_local_caja_config WHERE local_id = '".$local["id"]."' AND estado = '1'";
			$local_caja_config_query = $mysqli->query($local_caja_config_command);
			if($mysqli->error){
				print_r($mysqli->error);
				exit();
			}
			while($lcc=$local_caja_config_query->fetch_assoc()){
				$local["caja_config"][$lcc["campo"]]=$lcc["valor"];
			}
			// print_r($local);
			$table = array();
			$table["datos_sistema"]=array();

			$caja_arr = array();
			$caja_command = "SELECT 
								c.id AS caja_id,
								c.fecha_operacion,
								c.turno_id,
								c.observaciones,
								c.estado
							FROM tbl_caja c
							LEFT JOIN tbl_local_cajas lc ON(lc.id = c.local_caja_id)
							LEFT JOIN tbl_locales l ON (l.id = lc.local_id)
							WHERE c.id != 1
							AND l.id = '".$local_id."'
							AND c.fecha_operacion >= '".$fecha_inicio."'
							AND c.fecha_operacion < '".$fecha_fin."'
							ORDER BY c.fecha_operacion ASC, c.turno_id ASC
								";
			// echo $caja_command; exit();
			$caja_query = $mysqli->query($caja_command);
			if($mysqli->error){
				print_r($mysqli->error);
				exit();
			}
			$caja_data = array();
			while($c=$caja_query->fetch_assoc()){
				$c["datos_sistema"]=array();
				$ds_command = "SELECT 
									cd.id,
									cdt.nombre,
									IFNULL(cd.ingreso,0) AS ingreso,
									IFNULL(cd.salida,0) AS salida,
									CAST((IFNULL(cd.ingreso,0) - IFNULL(cd.salida,0)) AS DECIMAL(20,2)) AS resultado,
									lcdt.detalle_tipos_id
								FROM tbl_local_caja_detalle_tipos lcdt
								LEFT JOIN tbl_caja_detalle cd ON (cd.tipo_id = lcdt.id)
								LEFT JOIN tbl_caja_detalle_tipos cdt ON (cdt.id = lcdt.detalle_tipos_id)
								WHERE cd.caja_id = '".$c["caja_id"]."'
								ORDER BY lcdt.orden, cdt.ord ASC, lcdt.nombre ASC";
					$ds_query = $mysqli->query($ds_command);
					if($mysqli->error){
						print_r($mysqli->error);
						exit();
					}
					$array_tipos_id = array();
					while($ds_db=$ds_query->fetch_assoc()){
						$c["datos_sistema"][$ds_db["detalle_tipos_id"]][]=$ds_db;
						$array_tipos_id[$ds_db["detalle_tipos_id"]] = $ds_db["detalle_tipos_id"];						
					}

				$c["datos_fisicos"]=array();
					$df_command = "SELECT 
										df.tipo_id, IFNULL(df.valor,0) AS valor
									FROM tbl_caja_datos_fisicos df 
									WHERE df.caja_id = '".$c["caja_id"]."'";
					$df_query = $mysqli->query($df_command);
					if($mysqli->error){
						print_r($mysqli->error);
						exit();
					}
					while($df_db=$df_query->fetch_assoc()){
						$c["datos_fisicos"][$df_db["tipo_id"]]=$df_db;
					}
				$caja_data[]=$c;
			}
			if(count($caja_data)){
				// print_r($caja_data);
				$num_terminals = 0;
				$table["datos_sistema"]["cols"]=array();
				foreach ($caja_data as $ck => $c) {
					$new_num_terminals = 0;
					foreach ($c["datos_sistema"] as $detalle_tipos_id => $ds) {
						if($detalle_tipos_id==4){
							foreach ($ds as $key => $value) {
								$new_num_terminals++;
							}
						}
						$table["datos_sistema"]["cols"][$detalle_tipos_id]=$ds[0];
					}
					if($new_num_terminals>$num_terminals){
						$num_terminals=$new_num_terminals;
					}
				}
				$table["datos_sistema"]["col_num"]=count($table["datos_sistema"]["cols"]);
				$table["datos_sistema"]["num_terminals"]=$num_terminals;
				$table["datos_sistema"]["colspan"]=1;
				foreach ($table["datos_sistema"]["cols"] as $ds_k => $ds_v) {
					if($ds_k==4){
						$table["datos_sistema"]["colspan"]+=($table["datos_sistema"]["num_terminals"]+1);
					}else{
						$table["datos_sistema"]["colspan"]+=3;
					}
				}
				$report=array();
				$report["apertura"]=false;
				$report["efectivo_fisico"]=0;
				// foreach ($table["datos_sistema"]["cols"] as $key => $value) {
				// 	$table["datos_sistema"]["colspan"]+=1;
				// }
				// ($table["datos_sistema"]["col_num"] ? ((($table["datos_sistema"]["col_num"])*3)+2):1)
				$table["tbody"]=array();
				// $table["default"][""]
				$no_data = "-";
				// $total_arr = array();
				foreach ($caja_data as $data_id => $data) {
					// $tr_in
					$tr = array();
					// $tr["caja_id"]=$data["caja_id"];
					$tr["local_nombre"] = $local["nombre"];
					$tr["ano"] = substr($data["fecha_operacion"], 0,4);
					$tr["mes"] = substr($data["fecha_operacion"], 5,2);
					$tr["dia"] = substr($data["fecha_operacion"], 8,2);
					$tr["turno_id"] = $data["turno_id"];
					$tr["apertura"]=(array_key_exists(1, $data["datos_fisicos"]) ? $data["datos_fisicos"][1]["valor"] : 0);
					
					// $total_arr[""]


					foreach ($table["datos_sistema"]["cols"] as $ds_k => $ds_v) {
						if(array_key_exists($ds_k, $data["datos_sistema"])){
							if($ds_k==4){
								$t_sum = 0;
								foreach ($data["datos_sistema"][$ds_k] as $key => $value) {
									$tr["ds_".$ds_k."_t_".$key."_"."_in"]=$value["ingreso"];
									$t_sum+=$value["ingreso"];
								}
								$tr["ds_".$ds_k."_res"]=$t_sum;
							}else{
								$ds = $data["datos_sistema"][$ds_k][0];
								$tr["ds_".$ds_k."_in"]=$ds["ingreso"];
								$tr["ds_".$ds_k."_out"]=$ds["salida"];
								$tr["ds_".$ds_k."_res"]=$ds["resultado"];
							}
						}else{
							if($ds_k==4){
								for ($t=1; $t <= $table["datos_sistema"]["num_terminals"]; $t++) {
									$tr["ds_".$ds_k."_t_".$t."_"."_in"]=$no_data;
								}
								$tr["ds_".$ds_k."_res"]=$no_data;
							}else{
								$tr["ds_".$ds_k."_in"]=$no_data;
								$tr["ds_".$ds_k."_out"]=$no_data;
								$tr["ds_".$ds_k."_res"]=$no_data;
							}
						}
					}

					$tr["resultado"]="".(array_key_exists(5, $data["datos_fisicos"]) ? $data["datos_fisicos"][5]["valor"] : "0.00");
					$tr["visa"]="".(array_key_exists(6, $data["datos_fisicos"]) ? $data["datos_fisicos"][6]["valor"] : "0.00");
					$tr["mastercard"]="".(array_key_exists(7, $data["datos_fisicos"]) ? $data["datos_fisicos"][7]["valor"] : "0.00");
					$tr["devoluciones"]="".(array_key_exists(8, $data["datos_fisicos"]) ? $data["datos_fisicos"][8]["valor"] : "0.00");
					$tr["pagos_manuales"]="".(array_key_exists(9, $data["datos_fisicos"]) ? $data["datos_fisicos"][9]["valor"] : "0.00");

					$tr["prestamo_slot"]="".(array_key_exists(12, $data["datos_fisicos"]) ? $data["datos_fisicos"][12]["valor"] : "0.00");
					$tr["prestamo_boveda"]="".(array_key_exists(2, $data["datos_fisicos"]) ? $data["datos_fisicos"][2]["valor"] : "0.00");
					$tr["devolucion_slot"]="".(array_key_exists(13, $data["datos_fisicos"]) ? $data["datos_fisicos"][13]["valor"] : "0.00");
					$tr["devolucion_boveda"]="".(array_key_exists(3, $data["datos_fisicos"]) ? $data["datos_fisicos"][3]["valor"] : "0.00");
					$tr["deposito_venta"]="".(array_key_exists(4, $data["datos_fisicos"]) ? $data["datos_fisicos"][4]["valor"] : "0.00");

					$tr["deuda_slot"]="".(array_key_exists(16, $data["datos_fisicos"]) ? $data["datos_fisicos"][16]["valor"] : "0.00");
					
					// $tr["fondo_fijo"]=($local["caja_config"]["monto_inicial"] ? $local["caja_config"]["monto_inicial"] : 0);
					// $tr["valla"]=$local["caja_config"]["valla_deposito"];
					$tr["fondo_fijo"]="".(array_key_exists(14, $data["datos_fisicos"]) ? $data["datos_fisicos"][14]["valor"] : ($local["caja_config"]["monto_inicial"] ? $local["caja_config"]["monto_inicial"] : 0));
					$tr["saldo_kasnet"]="".(array_key_exists(20, $data["datos_fisicos"]) ? $data["datos_fisicos"][20]["valor"] : "0.00");
					$tr["valla"]="".(array_key_exists(15, $data["datos_fisicos"]) ? $data["datos_fisicos"][15]["valor"] : $local["caja_config"]["valla_deposito"]);

					$efectivo_fisico = (array_key_exists(11, $data["datos_fisicos"]) ? $data["datos_fisicos"][11]["valor"] : "0.00");
					// $deposito = $efectivo_fisico - $tr["fondo_fijo"];
					// if($deposito<0){ $deposito = 0; }
					$deposito = ($efectivo_fisico>$tr["fondo_fijo"] ? $efectivo_fisico - $tr["fondo_fijo"] : 0);
					$tr["deposito"]=round($deposito,2);
					$tr["accion"]=(($tr["apertura"]-$tr["fondo_fijo"]) > 0 ? "" : "No ")."Depositar";
					$tr["efectivo_sistema"]="".(array_key_exists(10, $data["datos_fisicos"]) ? $data["datos_fisicos"][10]["valor"] : "0.00");
					$tr["efectivo_fisico"]="".round($efectivo_fisico,2);
					$diff = ($tr["efectivo_fisico"]-$tr["efectivo_sistema"]);
					$tr["efectivo_sobrante"]=round($diff,2);
					$tr["observaciones"]=$data["observaciones"];
					$tr["estado"]=($data["estado"]==1 ? "Cerrado" : "Abierto");



					$table["tbody"][]=$tr;

					if($report["apertura"]===false){
						$report["apertura"]=$tr["apertura"];
					}
					$report["efectivo_fisico"]=$tr["efectivo_fisico"];

				}
				$table_total=array();
				// $table_total["apertura"]=0;
				// $table_total["resultado"]=0;
				// $table_total["devolucion_slot"]=0;
				// $table_total["devolucion_boveda"]=0;
				// $table_total["deposito_venta"]=0;
				// $table_total["visa"]=0;
				// $table_total["mastercard"]=0;
				// $table_total["devoluciones"]=0;
				// $table_total["pagos_manuales"]=0;
				// $table_total["efectivo_sobrante"]=0;
				// $table_total["__________________"]=0;
				$table_total_ignore=array();
				$table_total_ignore[]="local_nombre";
				$table_total_ignore[]="ano";
				$table_total_ignore[]="mes";
				$table_total_ignore[]="dia";
				$table_total_ignore[]="turno_id";
				$table_total_ignore[]="apertura";
				$table_total_ignore[]="observaciones";
				$table_total_ignore[]="accion";
				$table_total_ignore[]="fondo_fijo";
				$table_total_ignore[]="valla";
				$table_total_ignore[]="deposito";
				$table_total_ignore[]="efectivo_sistema";
				$table_total_ignore[]="efectivo_fisico";
				$table_total_ignore[]="deuda_slot";
				$table_total_ignore[]="saldo_kasnet";
				$table_total_ignore[]="estado";
				$table_total_ignore[]="caja_id";
				foreach ($table["tbody"] as $tr_k => $tr_v) {
					foreach ($tr_v as $key => $value) {
						if(in_array($key, $table_total_ignore)){
							if(in_array($key,array("turno_id","dia","mes","ano","apertura","accion","fondo_fijo","valla","observaciones","fondo_fijo","valla","deposito","efectivo_sistema","efectivo_fisico","deuda_slot","saldo_kasnet","estado"))){
								$value="-";
							}
							if($key != "caja_id"){
								$table_total[$key]=$value;
							}
						}else{
							if(array_key_exists($key, $table_total)){
								$table_total[$key]+=$value;
							}else{
								$table_total[$key]=$value;
							}
							// $table_total[$key] = round($table_total[$key],2);
						}
					}
				}
				// $table_total_orden = array();
				$table["total"]=$table_total;
				// print_r($table_total);
				$resumen = array();
				$resumen["apertura"]=($report["apertura"]===false ? 0 : $report["apertura"]);
				$resumen["resultado"]=$table_total["resultado"];
				$resumen["depositos"]=($table_total["deposito_venta"]);
				$resumen["tarjetas"]=($table_total["visa"]+$table_total["mastercard"]);
				$resumen["devo_manuales"]=($table_total["devoluciones"]+$table_total["pagos_manuales"]);
				$resumen["sobra_falta"]=$table_total["efectivo_sobrante"];
				$resumen["efectivo_fisico"]=($report["efectivo_fisico"]===false ? 0 : $report["efectivo_fisico"]);
				$resumen["prestamo_slotboveda"] = ($table_total["prestamo_slot"]+$table_total["prestamo_boveda"]);
				$resumen["devolucion_slotboveda"] = ($table_total["devolucion_slot"]+$table_total["devolucion_boveda"]);
				$resumen["diff_real"]=(
										$resumen["apertura"]
										+$resumen["resultado"]
										-$resumen["depositos"]
										-$resumen["tarjetas"]
										-$resumen["devo_manuales"]
										+$resumen["sobra_falta"]
										-$resumen["efectivo_fisico"]
										+$resumen["prestamo_slotboveda"]
										-$resumen["devolucion_slotboveda"]
									);
				
		/********************************************** FIN DATA ******************************************/
		/****************************************DATA RESUMEN POR DIA**************************************/
			if ($_POST["group_by"]=="day") {
				$ds_id_res_total_x_dia_in = array();
				$ds_id_res_total_x_dia_out = array();
				$ds_id_res_total_x_dia_res = array();						
				$visa_total_x_dia = array();
				$mastercard_total_x_dia = array();
				$devoluciones_total_x_dia = array();
				$pagos_manuales_total_x_dia = array();
				$prestamo_slot_total_x_dia = array();
				$prestamo_boveda_total_x_dia = array();
				$devolucion_slot_total_x_dia = array();
				$devolucion_boveda_total_x_dia = array();
				$deposito_venta_total_x_dia = array();
				$deuda_slot_total_x_dia = array();
				$fondo_fijo_total_x_dia = array();
				$saldo_kasnet_total_x_dia = array();
				$valla_total_x_dia = array();
				$deposito_total_x_dia = array();
				$accion_total_x_dia = array();
				$efectivo_sistema_total_x_dia = array();
				$efectivo_fisico_total_x_dia = array();
				$efectivo_sobrante_total_x_dia = array();
				$table_x_day = array();
				$apertura_x_day = array();
				$resultado_x_day = array();
				$ds_4_total_x_dia = array();

				foreach ($table["tbody"] as $key => $value) {
					$apertura_x_day[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["apertura"];
					$resultado_x_day[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["resultado"];

					foreach ($array_tipos_id as $id => $val_tipos_id) {
						if ($id==4) {
							for ($ds4=0; $ds4 < $table["datos_sistema"]["num_terminals"]; $ds4++) { 
								if (isset($value["ds_".$id."_t_".$ds4."__in"])) {
									$ds_4_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]]["ds_".$id."_t_".$ds4."__in"][] = $value["ds_".$id."_t_".$ds4."__in"];
								}else{
									$ds_4_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]]["ds_".$id."_t_".$ds4."__in"][] = "-";
								}
							}
							if (isset($value["ds_".$id."_res"])){
								$ds_4_res_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]]["ds_".$id."_res"][] = $value["ds_".$id."_res"];
							}else{
								$ds_4_res_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]]["ds_".$id."_res"][] = "-";
							}
						}else{

							if (isset($value["ds_".$id."_in"])){
								$ds_id_res_total_x_dia_in[$value["ano"]."".$value["mes"]."".$value["dia"]][$id][] = $value["ds_".$id."_in"];
							}else{
								$ds_id_res_total_x_dia_in[$value["ano"]."".$value["mes"]."".$value["dia"]][$id][] = "-";
							}
							if (isset($value["ds_".$id."_out"])){
								$ds_id_res_total_x_dia_out[$value["ano"]."".$value["mes"]."".$value["dia"]][$id][] = $value["ds_".$id."_out"];
							}else{
								$ds_id_res_total_x_dia_out[$value["ano"]."".$value["mes"]."".$value["dia"]][$id][] = "-";
							}
							if (isset($value["ds_".$id."_res"])){
								$ds_id_res_total_x_dia_res[$value["ano"]."".$value["mes"]."".$value["dia"]][$id][] = $value["ds_".$id."_res"];
							}else{
								$ds_id_res_total_x_dia_res[$value["ano"]."".$value["mes"]."".$value["dia"]][$id][] = "-";
							}
						}
					}

					if (isset($value["visa"])){
						$visa_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["visa"];
					}else{
						$visa_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-"; 
					}
					if (isset($value["mastercard"])){
						$mastercard_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["mastercard"];
					}else{
						$mastercard_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
					}
					if (isset($value["devoluciones"])){
						$devoluciones_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["devoluciones"];
					}else{
						$devoluciones_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
					}
					if (isset($value["pagos_manuales"])){
						$pagos_manuales_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["pagos_manuales"];
					}else{
						$pagos_manuales_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
					}				
					if (isset($value["prestamo_slot"])){
						$prestamo_slot_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["prestamo_slot"];
					}else{
						$prestamo_slot_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
					}
					if (isset($value["prestamo_boveda"])){
						$prestamo_boveda_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["prestamo_boveda"];
					}else{
						$prestamo_boveda_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
					}
					if (isset($value["devolucion_slot"])){
						$devolucion_slot_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["devolucion_slot"];
					}else{
						$devolucion_slot_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
					}
					if (isset($value["devolucion_boveda"])){
						$devolucion_boveda_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["devolucion_boveda"];
					}else{
						$devolucion_boveda_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
					}
					if (isset($value["deposito_venta"])){
						$deposito_venta_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["deposito_venta"];
					}else{
						$deposito_venta_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
					}
					if (isset($value["deuda_slot"])){
						$deuda_slot_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["deuda_slot"];
					}else{
						$deuda_slot_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
					}
					if (isset($value["fondo_fijo"])){
						$fondo_fijo_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["fondo_fijo"];
					}else{
						$fondo_fijo_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
					}
					if (isset($value["saldo_kasnet"])){
						$saldo_kasnet_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["saldo_kasnet"];
					}else{
						$saldo_kasnet_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
					}
					if (isset($value["valla"])){
						$valla_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["valla"];
					}else{
						$valla_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
					}
					if (isset($value["deposito"])){
						$deposito_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["deposito"];
					}else{
						$deposito_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
					}																					
					if (isset($value["accion"])){
						$accion_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["accion"];
					}else{
						$accion_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
					}
					if (isset($value["efectivo_sistema"])){
						$efectivo_sistema_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["efectivo_sistema"];
					}else{
						$efectivo_sistema_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
					}
					if (isset($value["efectivo_fisico"])){
						$efectivo_fisico_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["efectivo_fisico"];
					}else{
						$efectivo_fisico_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
					}
					if (isset($value["efectivo_sobrante"])){
						$efectivo_sobrante_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = $value["efectivo_sobrante"];
					}else{
						$efectivo_sobrante_total_x_dia[$value["ano"]."".$value["mes"]."".$value["dia"]][] = "-";
					}
				}

				foreach ($table["tbody"] as $num => $val) {
					$array =array();
					//$array["caja_id"] = $val["caja_id"];
					$array["local_nombre"] = $val["local_nombre"];
					$array["ano"] = $val["ano"];
					$array["mes"] = $val["mes"];
					$array["dia"] = $val["dia"];
					$array["turno_id"] = "";
					if (isset($apertura_x_day[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
						$array["apertura"] = current($apertura_x_day[$val["ano"]."".$val["mes"]."".$val["dia"]]);
					}else{
						$array["apertura"] = "-";
					}

					foreach ($array_tipos_id as $id => $val_tipos_id) {
						if ($id==4) {
							for ($i=0; $i < $table["datos_sistema"]["num_terminals"]; $i++) { 
								if (isset($ds_4_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]["ds_".$id."_t_".$i."__in"])) {
									$array["ds_".$id."_t_".$i."__in"] = array_sum($ds_4_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]["ds_".$id."_t_".$i."__in"]);
								}else{
									$array["ds_".$i."_t_".$i."__in"] = "-";
								}
							}
							if (isset($ds_4_res_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]["ds_".$id."_res"])) {
								$array["ds_".$id."_res"] = array_sum($ds_4_res_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]["ds_".$id."_res"]);
							}else{
								$array["ds_".$id."_res"] = "-";
							}

						}else{

							if (isset($ds_id_res_total_x_dia_in[$val["ano"]."".$val["mes"]."".$val["dia"]][$id])) {
								$array["ds_".$id."_in"] = array_sum($ds_id_res_total_x_dia_in[$val["ano"]."".$val["mes"]."".$val["dia"]][$id]);
							}else{
								$array["ds_".$id."_in"] = "-";
							}
							if (isset($ds_id_res_total_x_dia_out[$val["ano"]."".$val["mes"]."".$val["dia"]][$id])) {
								$array["ds_".$id."_out"] = array_sum($ds_id_res_total_x_dia_out[$val["ano"]."".$val["mes"]."".$val["dia"]][$id]);
							}else{
								$array["ds_".$id."_out"] = "-";
							}
							if (isset($ds_id_res_total_x_dia_res[$val["ano"]."".$val["mes"]."".$val["dia"]][$id])) {
								$array["ds_".$id."_res"] = array_sum($ds_id_res_total_x_dia_res[$val["ano"]."".$val["mes"]."".$val["dia"]][$id]); 
							}else{
								$array["ds_".$id."_res"] = "-";
							}
						}
					}



				
					if (isset($resultado_x_day[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
						$array["resultado"] = array_sum($resultado_x_day[$val["ano"]."".$val["mes"]."".$val["dia"]]);
					}else{
						$array["resultado"] = "-";
					}
					if (isset($visa_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
						$array["visa"] = array_sum($visa_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
					}else{
						$array["visa"] = "-";
					}
					if (isset($mastercard_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
						$array["mastercard"] = array_sum($mastercard_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
					}else{
						$array["mastercard"] = "-";
					}
					if (isset($devoluciones_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
						$array["devoluciones"] = array_sum($devoluciones_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
					}else{
						$array["devoluciones"] = "-";
					}
					if (isset($pagos_manuales_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
						$array["pagos_manuales"] = array_sum($pagos_manuales_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
					}else{
						$array["pagos_manuales"] = "-";
					}
					if (isset($prestamo_slot_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
						$array["prestamo_slot"] = array_sum($prestamo_slot_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
					}else{
						$array["prestamo_slot"] = "-";
					}
					if (isset($prestamo_boveda_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
						$array["prestamo_boveda"] = end($prestamo_boveda_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
					}else{
						$array["prestamo_boveda"] = "-";
					}
					if (isset($devolucion_slot_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
						$array["devolucion_slot"] = array_sum($devolucion_slot_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
					}else{
						$array["devolucion_slot"] = "-";
					}
					if (isset($devolucion_boveda_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
						$array["devolucion_boveda"] = array_sum($devolucion_boveda_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
					}else{
						$array["devolucion_boveda"] = "-";
					}
					if (isset($deposito_venta_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
						$array["deposito_venta"] = array_sum($deposito_venta_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
					}else{
						$array["deposito_venta"] = "-";
					}
					if (isset($deuda_slot_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
						$array["deuda_slot"] = end($deuda_slot_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
					}else{
						$array["deuda_slot"] = "-";
					}
					if (isset($fondo_fijo_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
						$array["fondo_fijo"] = end($fondo_fijo_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
					}else{
						$array["fondo_fijo"] = "-";
					}
					if (isset($saldo_kasnet_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
						$array["saldo_kasnet"] = end($saldo_kasnet_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
					}else{
						$array["saldo_kasnet"] = "-";
					}								            
					if (isset($valla_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
						$array["valla"] = end($valla_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
					}else{
						$array["valla"] = "-";
					}
					if (isset($deposito_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
						$array["deposito"] = end($deposito_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
					}else{
						$array["deposito"] = "-";
					}
					$array["accion"] = "";
					if (isset($efectivo_sistema_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
						$array["efectivo_sistema"] = end($efectivo_sistema_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
					}else{
						$array["efectivo_sistema"] = "-";
					}
					if (isset($efectivo_fisico_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
						$array["efectivo_fisico"] = end($efectivo_fisico_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
					}else{
						$array["efectivo_fisico"] = "-";
					}
					if (isset($efectivo_sobrante_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]])) {
						$array["efectivo_sobrante"] = array_sum($efectivo_sobrante_total_x_dia[$val["ano"]."".$val["mes"]."".$val["dia"]]);
					}else{
						$array["efectivo_sobrante"] = "-";
					}
					$array["observaciones"] = "";
					$array["estado"] = "";
					$period = $val["ano"]."".$val["mes"]."".$val["dia"];
					$table_x_day[(int)$period] = $array;
				}
				array_multisort($table_x_day);
			}	
		/***********************************FIN DATA RESUMEN POR DIA**************************************/

				$l = array();
				$cantidad_de_columnas_a_crear=1000; 
				$contador=0; 
				$letra='A'; 
				while($contador<=$cantidad_de_columnas_a_crear){ 
					$l[$contador] =  $letra;
					$contador++; 
					$letra++; 
				} 
				$titulo_resumen_caja ="RESUMEN DEL ".date("d-m-Y",strtotime($_POST["fecha_inicio"]))." AL ".date("d-m-Y",strtotime($_POST["fecha_fin"]));
				$titulo_reporte_cajas = "REPORTE CAJA ".$local_titulo[$_POST['local_id']];
				$titulo_file_reporte_cajas = "reporte_caja_".$locales[$_POST['local_id']]."_".date("d-m-Y",strtotime($_POST["fecha_inicio"]))."_al_".date("d-m-Y",strtotime($_POST["fecha_fin"]))."_".date("Ymdhis");

				if (isset($titulo_reporte_cajas)) {
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

					$estiloTituloResumen = new PHPExcel_Style();
					$estiloTituloResumen = array(
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
								'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
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
					$estiloTablaResumen = new PHPExcel_Style();
					$estiloTablaResumen = array(
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
					$estiloTotalesResumen = new PHPExcel_Style();
					$estiloTotalesResumen = array(
						'font' => array(
							'name'      => 'Verdana',
							'bold'      => false,
							'italic'    => false,
							'strike'    => false,
							'size' =>11
						),				
						'fill'  => array(
							'type'      => PHPExcel_Style_Fill::FILL_SOLID,
							'rotation'   => 90,
							'startcolor' => array(
								'rgb' => 'f0ad4e'
							),
							'endcolor'   => array(
								'argb' => 'f0ad4e'
							)
						),
						'borders' => array(
							'allborders' => array(
								'style' => PHPExcel_Style_Border::BORDER_THIN,
								'color' => array('rgb' => 'dddddd')
							)
						)								
					);	
					$estiloCabeceraTabla = new PHPExcel_Style();
					$estiloCabeceraTabla = array(
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
						'borders' => array(
							'allborders' => array(
								'style' => PHPExcel_Style_Border::BORDER_THIN,
								'color' => array('rgb' => 'faebcc')
							)
						)				
					);

					$estiloTotal = new PHPExcel_Style();
					$estiloTotal = array(
						'fill' => array(
							'type'  => PHPExcel_Style_Fill::FILL_SOLID,
							'color' => array('argb' => 'FFFFFF')
						),
						'borders' => array(
							'allborders' => array(
								'style' => PHPExcel_Style_Border::BORDER_THIN,
								'color' => array('rgb' => '333333')
							)
						)				
					);				

					$estiloTituloTabla = new PHPExcel_Style();
					$estiloTituloTabla = array(
						'alignment' =>  array(
								'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
								'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
								'rotation'   => 0,
								'wrap'          => TRUE
						)
					);

					$estiloDia = new PHPExcel_Style();
					$estiloDia = array(
						'alignment' =>  array(
								'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
								'vertical'   => PHPExcel_Style_Alignment::VERTICAL_CENTER,
								'rotation'   => 0,
								'wrap'          => TRUE
						)
					);	
					$estiloDiferenciaReal = new PHPExcel_Style();
					$estiloDiferenciaReal = array(
						'fill' => array(
							'type'  => PHPExcel_Style_Fill::FILL_SOLID,
							'color' => array('argb' => 'f0ad4e')
						),
						'borders' => array(
							'allborders' => array(
								'style' => PHPExcel_Style_Border::BORDER_THIN,
								'color' => array('rgb' => '888888')
							)
						)
					);								
					$estiloEstado = new PHPExcel_Style();
					$estiloEstado = array(
						'font' => array(
							'name'      => 'Verdana',
							'bold'      => false,
							'italic'    => false,
							'strike'    => false,
							'color'     => array(
									'rgb' => 'FFFFFF'
							)
						),					
						'fill' => array(
							'type'  => PHPExcel_Style_Fill::FILL_SOLID,
							'color' => array('argb' => '1cb787')
						),
						'borders' => array(
							'allborders' => array(
								'style' => PHPExcel_Style_Border::BORDER_THIN,
								'color' => array('rgb' => '888888')
							)
						)
					);		
					$estiloSubTitulo = new PHPExcel_Style();
					$estiloSubTitulo = array(
						'font' => array(
							'name'      => 'Verdana',
							'bold'      => false,
							'italic'    => false,
							'strike'    => false,
							'color'     => array(
									'rgb' => 'FFFFFF'
							)
						),					
						'fill' => array(
							'type'  => PHPExcel_Style_Fill::FILL_SOLID,
							'color' => array('argb' => '1cb787')
						),
						'borders' => array(
							'allborders' => array(
								'style' => PHPExcel_Style_Border::BORDER_THIN,
								'color' => array('rgb' => '888888')
							)
						)
					);						
					$estiloSubTituloOpcional = new PHPExcel_Style();
					$estiloSubTituloOpcional = array(
						'fill' => array(
							'type'  => PHPExcel_Style_Fill::FILL_SOLID,
							'color' => array('argb' => 'dddddd')
						),
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

					//resumen
					$ll =1;
					$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue($l[0].$ll,$titulo_reporte_cajas);			

					$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue($l[0]."3",$titulo_resumen_caja)
					->setCellValue($l[0]."4","Apertura Efectivo")
					->setCellValue($l[0]."5","Resultado Periodo")
					->setCellValue($l[0]."6","Depósitos de Venta")
					->setCellValue($l[0]."7","Ventas con Tarjetas")
					->setCellValue($l[0]."8","Devoluciones, Pagos Manuales")                                                    
					->setCellValue($l[0]."9","Sobrante / Faltante")
					->setCellValue($l[0]."10","Efectivo Fisico")
					->setCellValue($l[0]."11","Prestamo Slot Bóveda")
					->setCellValue($l[0]."12","Devolución Slot Bóveda")								
					->setCellValue($l[0]."13","Diferencia Real");

					$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue($l[4]."4",$resumen["apertura"])				
					->setCellValue($l[4]."5",$resumen["resultado"])
					->setCellValue($l[4]."6",$resumen["depositos"])
					->setCellValue($l[4]."7",$resumen["tarjetas"])
					->setCellValue($l[4]."8",$resumen["devo_manuales"])                                                    
					->setCellValue($l[4]."9",$resumen["sobra_falta"])
					->setCellValue($l[4]."10",$resumen["efectivo_fisico"])
					->setCellValue($l[4]."11",$resumen["prestamo_slotboveda"])
					->setCellValue($l[4]."12",$resumen["devolucion_slotboveda"])
					->setCellValue($l[4]."13",$resumen["diff_real"]);

					$objPHPExcel->getActiveSheet()->getStyle("E4")->getNumberFormat()->setFormatCode('#,##0.00');				
					$objPHPExcel->getActiveSheet()->getStyle("E5")->getNumberFormat()->setFormatCode('#,##0.00');
					$objPHPExcel->getActiveSheet()->getStyle("E6")->getNumberFormat()->setFormatCode('#,##0.00');				
					$objPHPExcel->getActiveSheet()->getStyle("E7")->getNumberFormat()->setFormatCode('#,##0.00');
					$objPHPExcel->getActiveSheet()->getStyle("E8")->getNumberFormat()->setFormatCode('#,##0.00');
					$objPHPExcel->getActiveSheet()->getStyle("E9")->getNumberFormat()->setFormatCode('#,##0.00');
					$objPHPExcel->getActiveSheet()->getStyle("E10")->getNumberFormat()->setFormatCode('#,##0.00');
					$objPHPExcel->getActiveSheet()->getStyle("E11")->getNumberFormat()->setFormatCode('#,##0.00');
					$objPHPExcel->getActiveSheet()->getStyle("E12")->getNumberFormat()->setFormatCode('#,##0.00');
					$objPHPExcel->getActiveSheet()->getStyle("E13")->getNumberFormat()->setFormatCode('#,##0.00');																								

					$p=15;
					$colspan_datos_del_sistema = $table["datos_sistema"]["colspan"];
					$merge_datos_del_sistema = $l[6].$p.':'.$l[(int)6+(int)($colspan_datos_del_sistema-1)].$p;
					$datos_fisicos_first = $colspan_datos_del_sistema+6;
					$datos_fisicos_last = $datos_fisicos_first+8;
					$merge_datos_fisicos = $l[$datos_fisicos_first].$p.":".$l[$datos_fisicos_last].$p;
					$info_first = $datos_fisicos_last+1;
					$info_last = $info_first+9;
					$merge_info = $l[$info_first].$p.":".$l[$info_last].$p;

					//cabecera
					$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue($l[0].$p,"Local")			
					->setCellValue($l[1].$p,"Fecha")
					->setCellValue($l[4].$p,"Turno")                                                    
					->setCellValue($l[5].$p,"Apertura Efectivo")
					->setCellValue($l[6].$p,"Datos del Sistema")
					->setCellValue($l[$datos_fisicos_first].$p,"Datos Fisicos")
					->setCellValue($l[$info_first].$p,"Información");
					
					$objPHPExcel->getActiveSheet()->getColumnDimension($l[0])->setAutoSize(true);				
					$objPHPExcel->getActiveSheet()->getColumnDimension($l[4])->setWidth(12);
					$objPHPExcel->getActiveSheet()->getColumnDimension($l[5])->setWidth(12);
					$objPHPExcel->getActiveSheet()->getStyle($l[0].$p)->applyFromArray($estiloCabeceraTabla);
					$objPHPExcel->getActiveSheet()->getStyle($l[1].$p)->applyFromArray($estiloCabeceraTabla);
					$objPHPExcel->getActiveSheet()->getStyle($l[4].$p)->applyFromArray($estiloCabeceraTabla);
					$objPHPExcel->getActiveSheet()->getStyle($l[5].$p)->applyFromArray($estiloCabeceraTabla);	
					$objPHPExcel->getActiveSheet()->getStyle($l[6].$p)->applyFromArray($estiloCabeceraTabla);
					$objPHPExcel->getActiveSheet()->getStyle($l[$datos_fisicos_first].$p)->applyFromArray($estiloCabeceraTabla);
					$objPHPExcel->getActiveSheet()->getStyle($l[$info_first].$p)->applyFromArray($estiloCabeceraTabla);															
					$sp=17;
					$objPHPExcel->setActiveSheetIndex(0)
					->setCellValue($l[1].$sp,"Año")			
					->setCellValue($l[2].$sp,"Mes")
					->setCellValue($l[3].$sp,"Dia");
					$objPHPExcel->getActiveSheet()->getStyle($l[1].$sp)->applyFromArray($estiloSubTituloOpcional);
					$objPHPExcel->getActiveSheet()->getStyle($l[2].$sp)->applyFromArray($estiloSubTituloOpcional);	
					$objPHPExcel->getActiveSheet()->getStyle($l[3].$sp)->applyFromArray($estiloSubTituloOpcional);										

					//datos del sistema
					$col = 6;
					$row = 16;
					foreach ($table["datos_sistema"]["cols"] as $ds_k => $ds_v) {
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col,$row,$ds_v["nombre"]);
						if ($ds_k==4) {	
							$count = $table["datos_sistema"]["num_terminals"]+1; $last = $col+$count;
						}else{
							$count = 3; $last = $col+$count;
						}
						$first = $last-$count;
						$objPHPExcel->getActiveSheet()->getStyle($l[$first]."".$row.":".$l[$last-1]."".$row)->applyFromArray($estiloSubTitulo);					
						$objPHPExcel->setActiveSheetIndex(0)->mergeCells($l[$first]."".$row.":".$l[$last-1]."".$row);
						$col=$col+$count;				
					}

					$col_resultado_dia = $col;
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_resultado_dia,$row,"Resultado del día");
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells($l[$col_resultado_dia].$row.":".$l[$col_resultado_dia].($row+1));
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_resultado_dia].$row.":".$l[$col_resultado_dia].($row+1))->applyFromArray($estiloTituloTabla);
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_resultado_dia].$row.":".$l[$col_resultado_dia].($row+1))->applyFromArray($estiloSubTituloOpcional);
					$objPHPExcel->getActiveSheet()->getColumnDimension($l[$col_resultado_dia])->setWidth(10);

					$col_sb = 6;
					$row_sb = 17;
					foreach ($table["datos_sistema"]["cols"] as $ds_k => $ds_v) {
						if($ds_k==4){
							for ($t=1; $t <= $table["datos_sistema"]["num_terminals"]; $t++) { 
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_sb,$row_sb,"Billetero ".$t);
								$objPHPExcel->getActiveSheet()->getStyle($l[$col_sb].$row_sb)->applyFromArray($estiloSubTituloOpcional);
								$objPHPExcel->getActiveSheet()->getColumnDimension($l[$col_sb])->setWidth(10);
								$col_sb++; 
							}
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_sb,$row_sb,"resultado");
							$objPHPExcel->getActiveSheet()->getStyle($l[$col_sb].$row_sb)->applyFromArray($estiloSubTituloOpcional);						
							$col_sb++;
						}else{
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_sb,$row_sb,"ingreso");
							$objPHPExcel->getActiveSheet()->getStyle($l[$col_sb].$row_sb)->applyFromArray($estiloSubTituloOpcional);
							$col_sb++;
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_sb,$row_sb,"salida");
							$objPHPExcel->getActiveSheet()->getStyle($l[$col_sb].$row_sb)->applyFromArray($estiloSubTituloOpcional);
							$col_sb++;
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_sb,$row_sb,"resultado");
							$objPHPExcel->getActiveSheet()->getStyle($l[$col_sb].$row_sb)->applyFromArray($estiloSubTituloOpcional);						
							$col_sb++;							
						}
					}

					$conditionalStyles = $objPHPExcel->getActiveSheet()->getStyle("E4:E13")->getConditionalStyles();
					array_push($conditionalStyles, $objConditionalNegativeNumber);
					$objPHPExcel->getActiveSheet()->getStyle("E4:E13")->setConditionalStyles($conditionalStyles);

					//datos fisicos
					$col_datos_fisicos_first = $col_resultado_dia+1;
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_datos_fisicos_first,$row,"Visa");
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells($l[$col_datos_fisicos_first].$row.":".$l[$col_datos_fisicos_first].($row+1));
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_datos_fisicos_first].$row.":".$l[$col_datos_fisicos_first].($row+1))->applyFromArray($estiloTituloTabla);
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_datos_fisicos_first].$row.":".$l[$col_datos_fisicos_first].($row+1))->applyFromArray($estiloSubTituloOpcional);								
					$col_datos_fisicos_first++;
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_datos_fisicos_first,$row,"Mastercard");
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells($l[$col_datos_fisicos_first].$row.":".$l[$col_datos_fisicos_first].($row+1));
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_datos_fisicos_first].$row.":".$l[$col_datos_fisicos_first].($row+1))->applyFromArray($estiloTituloTabla);
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_datos_fisicos_first].$row.":".$l[$col_datos_fisicos_first].($row+1))->applyFromArray($estiloSubTituloOpcional);					
					$objPHPExcel->getActiveSheet()->getColumnDimension($l[$col_datos_fisicos_first])->setWidth(11);			
					$col_datos_fisicos_first++;
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_datos_fisicos_first,$row,"Devoluciones");
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells($l[$col_datos_fisicos_first].$row.":".$l[$col_datos_fisicos_first].($row+1));
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_datos_fisicos_first].$row.":".$l[$col_datos_fisicos_first].($row+1))->applyFromArray($estiloTituloTabla);
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_datos_fisicos_first].$row.":".$l[$col_datos_fisicos_first].($row+1))->applyFromArray($estiloSubTituloOpcional);				
					$objPHPExcel->getActiveSheet()->getColumnDimension($l[$col_datos_fisicos_first])->setWidth(12);								
					$col_datos_fisicos_first++;
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_datos_fisicos_first,$row,"Pagos Manuales");
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells($l[$col_datos_fisicos_first].$row.":".$l[$col_datos_fisicos_first].($row+1));
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_datos_fisicos_first].$row.":".$l[$col_datos_fisicos_first].($row+1))->applyFromArray($estiloTituloTabla);	
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_datos_fisicos_first].$row.":".$l[$col_datos_fisicos_first].($row+1))->applyFromArray($estiloSubTituloOpcional);							
					$col_datos_fisicos_first++;
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_datos_fisicos_first,$row,"Prestamos");
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells($l[$col_datos_fisicos_first].$row.":".$l[$col_datos_fisicos_first+1].($row));
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_datos_fisicos_first].$row.":".$l[$col_datos_fisicos_first+1].($row))->applyFromArray($estiloTituloTabla);
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_datos_fisicos_first].$row.":".$l[$col_datos_fisicos_first+1].($row))->applyFromArray($estiloSubTitulo);	
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_datos_fisicos_first,($row+1),"Slot");
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_datos_fisicos_first].($row+1).":".$l[$col_datos_fisicos_first].($row+1))->applyFromArray($estiloSubTituloOpcional);
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col_datos_fisicos_first+1),($row+1),"Bóveda");
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_datos_fisicos_first+1].($row+1).":".$l[$col_datos_fisicos_first+1].($row+1))->applyFromArray($estiloSubTituloOpcional);				
					$col_datos_fisicos_first=$col_datos_fisicos_first+2;

					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_datos_fisicos_first,$row,"Depósitos");
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells($l[$col_datos_fisicos_first].$row.":".$l[$col_datos_fisicos_first+2].($row));
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_datos_fisicos_first].$row.":".$l[$col_datos_fisicos_first+2].($row))->applyFromArray($estiloTituloTabla);
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_datos_fisicos_first].$row.":".$l[$col_datos_fisicos_first+2].($row))->applyFromArray($estiloSubTitulo);


					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_datos_fisicos_first,($row+1),"Slot");
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_datos_fisicos_first].($row+1).":".$l[$col_datos_fisicos_first].($row+1))->applyFromArray($estiloSubTituloOpcional);					
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col_datos_fisicos_first+1),($row+1),"Bóveda");
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_datos_fisicos_first+1].($row+1).":".$l[$col_datos_fisicos_first+1].($row+1))->applyFromArray($estiloSubTituloOpcional);				
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(($col_datos_fisicos_first+2),($row+1),"Venta");
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_datos_fisicos_first+2].($row+1).":".$l[$col_datos_fisicos_first+2].($row+1))->applyFromArray($estiloSubTituloOpcional);				
					$col_datos_fisicos_first++;

					//información
					$col_info_first = $col_datos_fisicos_first+2;
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_info_first,$row,"Deuda Slot");
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_info_first].$row)->applyFromArray($estiloTituloTabla);
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_info_first].$row.":".$l[$col_info_first].($row+1))->applyFromArray($estiloSubTituloOpcional);				
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells($l[$col_info_first].$row.":".$l[$col_info_first].($row+1));				
					$col_info_first++;
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_info_first,$row,"Fondo Fijo");
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_info_first].$row)->applyFromArray($estiloTituloTabla);
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_info_first].$row.":".$l[$col_info_first].($row+1))->applyFromArray($estiloSubTituloOpcional);				
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells($l[$col_info_first].$row.":".$l[$col_info_first].($row+1));								
					$col_info_first++;
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_info_first,$row,"Saldo Kasnet");
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_info_first].$row)->applyFromArray($estiloTituloTabla);
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_info_first].$row.":".$l[$col_info_first].($row+1))->applyFromArray($estiloSubTituloOpcional);				
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells($l[$col_info_first].$row.":".$l[$col_info_first].($row+1));				
					$col_info_first++;
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_info_first,$row,"Mínimo Depósito");
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_info_first].$row)->applyFromArray($estiloTituloTabla);	
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_info_first].$row.":".$l[$col_info_first].($row+1))->applyFromArray($estiloSubTituloOpcional);				
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells($l[$col_info_first].$row.":".$l[$col_info_first].($row+1));							
					$col_info_first++;
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_info_first,$row,"Depósito");
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_info_first].$row)->applyFromArray($estiloTituloTabla);
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_info_first].$row.":".$l[$col_info_first].($row+1))->applyFromArray($estiloSubTituloOpcional);
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells($l[$col_info_first].$row.":".$l[$col_info_first].($row+1));				

					$col_info_first++;
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_info_first,$row,"Acción");
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_info_first].$row)->applyFromArray($estiloTituloTabla);
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_info_first].$row.":".$l[$col_info_first].($row+1))->applyFromArray($estiloSubTituloOpcional);				
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells($l[$col_info_first].$row.":".$l[$col_info_first].($row+1));
					$objPHPExcel->getActiveSheet()->getColumnDimension($l[$col_info_first])->setAutoSize(true);

					$col_info_first++;
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_info_first,$row,"Efectivo");
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_info_first].$row)->applyFromArray($estiloTituloTabla);	
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_info_first].$row.":".$l[$col_info_first+2].$row)->applyFromArray($estiloSubTitulo);				
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells($l[$col_info_first].$row.":".$l[$col_info_first+2].$row);	

					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_info_first,($row+1),"Sistema");
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_info_first].($row+1).":".$l[$col_info_first].($row+1))->applyFromArray($estiloSubTituloOpcional);				
					$col_info_first++;
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_info_first,($row+1),"Fisico");
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_info_first].($row+1).":".$l[$col_info_first].($row+1))->applyFromArray($estiloSubTituloOpcional);				
					$col_info_first++;
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_info_first,($row+1),"Sobrante/Faltante");
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_info_first].($row+1).":".$l[$col_info_first].($row+1))->applyFromArray($estiloSubTituloOpcional);				
					$objPHPExcel->getActiveSheet()->getColumnDimension($l[$col_info_first])->setWidth(16);

					$col_info_first=$col_info_first+1;
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_info_first,$row,"Observaciones");
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_info_first].$row)->applyFromArray($estiloTituloTabla);
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_info_first].$row.":".$l[$col_info_first].($row+1))->applyFromArray($estiloSubTituloOpcional);				
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells($l[$col_info_first].$row.":".$l[$col_info_first].($row+1));
					$objPHPExcel->getActiveSheet()->getColumnDimension($l[$col_info_first])->setAutoSize(true);	
					$col_info_first++;
					$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_info_first,$row,"Estado");
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_info_first].$row)->applyFromArray($estiloTituloTabla);
					$objPHPExcel->getActiveSheet()->getStyle($l[$col_info_first].$row.":".$l[$col_info_first].($row+1))->applyFromArray($estiloSubTituloOpcional);				
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells($l[$col_info_first].$row.":".$l[$col_info_first].($row+1));
					$objPHPExcel->getActiveSheet()->getColumnDimension($l[$col_info_first])->setAutoSize(true);				
					//body
					$row_body=18;
					$col_body= 0;

					if ($_POST["group_by"]=="day") {
						foreach ($table_x_day as $fecha_operacion => $tr) {
							foreach ($tr as $key => $val) {
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_body,$row_body,$val);
								$objPHPExcel->getActiveSheet()->getStyle("D".$row)->applyFromArray($estiloDia);

								$conditionalStyles = $objPHPExcel->getActiveSheet()->getStyle($l[$col_body]."".$row_body.":".$l[$col_body]."".$row_body)->getConditionalStyles();
								array_push($conditionalStyles, $objConditionalNegativeNumber);
								$objPHPExcel->getActiveSheet()->getStyle($l[$col_body]."".$row_body.":".$l[$col_body]."".$row_body)->setConditionalStyles($conditionalStyles);

								$objPHPExcel->getActiveSheet()->getStyle($l[$col_body]."".$row_body.":".$l[$col_body]."".$row_body)->applyFromArray($estiloCeldasTablaResumen);

								$objPHPExcel->getActiveSheet()->getStyle("C".$row_body.":D".$row_body)->applyFromArray($estiloDia);
								

								$objPHPExcel->getActiveSheet()->getColumnDimension($l[$col_body])->setWidth(12);						
								//format
								$objPHPExcel->getActiveSheet()->getStyle("F".$row_body.":".$l[$col_body]."".$row_body)->getNumberFormat()->setFormatCode('#,##0.00');

								$col_body++;
							}
							$objPHPExcel->getActiveSheet()->getStyle($l[$col_body-1]."".$row_body)->applyFromArray($estiloEstado);
							$col_body=0;
							$row_body++;
						}						
					}
					
					if ($_POST["group_by"]=="turno_id") {
						foreach ($table["tbody"] as $fecha_operacion => $tr) {
							foreach ($tr as $key => $val) {

								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_body,$row_body,$val);
								$objPHPExcel->getActiveSheet()->getStyle("D".$row)->applyFromArray($estiloDia);

								$conditionalStyles = $objPHPExcel->getActiveSheet()->getStyle($l[$col_body]."".$row_body.":".$l[$col_body]."".$row_body)->getConditionalStyles();
								array_push($conditionalStyles, $objConditionalNegativeNumber);
								$objPHPExcel->getActiveSheet()->getStyle($l[$col_body]."".$row_body.":".$l[$col_body]."".$row_body)->setConditionalStyles($conditionalStyles);

								$objPHPExcel->getActiveSheet()->getStyle($l[$col_body]."".$row_body.":".$l[$col_body]."".$row_body)->applyFromArray($estiloCeldasTablaResumen);

								$objPHPExcel->getActiveSheet()->getStyle("C".$row_body.":D".$row_body)->applyFromArray($estiloDia);

								$objPHPExcel->getActiveSheet()->getColumnDimension($l[$col_body])->setWidth(12);						
								//format
								$objPHPExcel->getActiveSheet()->getStyle("F".$row_body.":".$l[$col_body]."".$row_body)->getNumberFormat()->setFormatCode('#,##0.00');															
								$col_body++;
							}
							$objPHPExcel->getActiveSheet()->getStyle($l[$col_body-1]."".$row_body)->applyFromArray($estiloEstado);							
							$col_body=0;
							$row_body++;
						}					
					}

					//totales
					$col_body_total= 0;
					$row_body_total = $row_body;
					foreach ($table["total"] as $key => $t_val) {
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col_body_total,$row_body_total,$t_val);
						$objPHPExcel->getActiveSheet()->getStyle($l[$col_body_total].$row_body_total)->applyFromArray($estiloTotal);

						$conditionalStyles = $objPHPExcel->getActiveSheet()->getStyle($l[$col_body_total]."".$row_body_total.":".$l[$col_body_total]."".$row_body_total)->getConditionalStyles();
						array_push($conditionalStyles, $objConditionalNegativeNumber);
						$objPHPExcel->getActiveSheet()->getStyle($l[$col_body_total]."".$row_body_total.":".$l[$col_body_total]."".$row_body_total)->setConditionalStyles($conditionalStyles);	
						$objPHPExcel->getActiveSheet()->getStyle($l[$col_body_total]."".$row_body_total.":".$l[$col_body_total]."".$row_body_total)->applyFromArray($estiloTotalesResumen);					
						$objPHPExcel->getActiveSheet()->getStyle($l[$col_body_total]."".$row_body_total.":".$l[$col_body_total]."".$row_body_total)->applyFromArray($estiloCeldasTablaResumen);
						$objPHPExcel->getActiveSheet()->getStyle("C".$row_body_total.":D".$row_body_total)->applyFromArray($estiloDia);
						
						$objPHPExcel->getActiveSheet()->getColumnDimension($l[$row_body_total])->setWidth(12);

						//format
						$objPHPExcel->getActiveSheet()->getStyle("F".$row_body_total)->getNumberFormat()->setFormatCode('#,##0.00');
						$objPHPExcel->getActiveSheet()->getStyle("G".$row_body_total)->getNumberFormat()->setFormatCode('#,##0.00');
						$objPHPExcel->getActiveSheet()->getStyle("H".$row_body_total)->getNumberFormat()->setFormatCode('#,##0.00');
						$objPHPExcel->getActiveSheet()->getStyle("I".$row_body_total)->getNumberFormat()->setFormatCode('#,##0.00');
						$objPHPExcel->getActiveSheet()->getStyle("J".$row_body_total)->getNumberFormat()->setFormatCode('#,##0.00');
						$objPHPExcel->getActiveSheet()->getStyle("K".$row_body_total)->getNumberFormat()->setFormatCode('#,##0.00');
						$objPHPExcel->getActiveSheet()->getStyle("L".$row_body_total)->getNumberFormat()->setFormatCode('#,##0.00');
						$objPHPExcel->getActiveSheet()->getStyle("M".$row_body_total)->getNumberFormat()->setFormatCode('#,##0.00');
						$objPHPExcel->getActiveSheet()->getStyle("N".$row_body_total)->getNumberFormat()->setFormatCode('#,##0.00');
						$objPHPExcel->getActiveSheet()->getStyle("O".$row_body_total)->getNumberFormat()->setFormatCode('#,##0.00');
						$objPHPExcel->getActiveSheet()->getStyle("P".$row_body_total)->getNumberFormat()->setFormatCode('#,##0.00');
						$objPHPExcel->getActiveSheet()->getStyle("Q".$row_body_total)->getNumberFormat()->setFormatCode('#,##0.00');
						$objPHPExcel->getActiveSheet()->getStyle("R".$row_body_total)->getNumberFormat()->setFormatCode('#,##0.00');
						$objPHPExcel->getActiveSheet()->getStyle("S".$row_body_total)->getNumberFormat()->setFormatCode('#,##0.00');
						$objPHPExcel->getActiveSheet()->getStyle("T".$row_body_total)->getNumberFormat()->setFormatCode('#,##0.00');
						$objPHPExcel->getActiveSheet()->getStyle("U".$row_body_total)->getNumberFormat()->setFormatCode('#,##0.00');
						$objPHPExcel->getActiveSheet()->getStyle("V".$row_body_total)->getNumberFormat()->setFormatCode('#,##0.00');
						$objPHPExcel->getActiveSheet()->getStyle("W".$row_body_total)->getNumberFormat()->setFormatCode('#,##0.00');
						$objPHPExcel->getActiveSheet()->getStyle("X".$row_body_total)->getNumberFormat()->setFormatCode('#,##0.00');
						$objPHPExcel->getActiveSheet()->getStyle("Y".$row_body_total)->getNumberFormat()->setFormatCode('#,##0.00');
						$objPHPExcel->getActiveSheet()->getStyle("Z".$row_body_total)->getNumberFormat()->setFormatCode('#,##0.00');
						$objPHPExcel->getActiveSheet()->getStyle("AA".$row_body_total)->getNumberFormat()->setFormatCode('#,##0.00');
						$objPHPExcel->getActiveSheet()->getStyle("AB".$row_body_total)->getNumberFormat()->setFormatCode('#,##0.00');
						$objPHPExcel->getActiveSheet()->getStyle("AC".$row_body_total)->getNumberFormat()->setFormatCode('#,##0.00');
						$objPHPExcel->getActiveSheet()->getStyle("AD".$row_body_total)->getNumberFormat()->setFormatCode('#,##0.00');
						$objPHPExcel->getActiveSheet()->getStyle("AE".$row_body_total)->getNumberFormat()->setFormatCode('#,##0.00');
						$objPHPExcel->getActiveSheet()->getStyle("AF".$row_body_total)->getNumberFormat()->setFormatCode('#,##0.00');
						$objPHPExcel->getActiveSheet()->getStyle("AG".$row_body_total)->getNumberFormat()->setFormatCode('#,##0.00');
						$objPHPExcel->getActiveSheet()->getStyle("AH".$row_body_total)->getNumberFormat()->setFormatCode('#,##0.00');
						$objPHPExcel->getActiveSheet()->getStyle("AI".$row_body_total)->getNumberFormat()->setFormatCode('#,##0.00');
						$objPHPExcel->getActiveSheet()->getStyle("AJ".$row_body_total)->getNumberFormat()->setFormatCode('#,##0.00');
						$objPHPExcel->getActiveSheet()->getStyle("AK".$row_body_total)->getNumberFormat()->setFormatCode('#,##0.00');
						$objPHPExcel->getActiveSheet()->getStyle("AL".$row_body_total)->getNumberFormat()->setFormatCode('#,##0.00');
						$objPHPExcel->getActiveSheet()->getStyle("AM".$row_body_total)->getNumberFormat()->setFormatCode('#,##0.00');
						$objPHPExcel->getActiveSheet()->getStyle("AN".$row_body_total)->getNumberFormat()->setFormatCode('#,##0.00');

						$col_body_total++;
						
					}
					//fixed
					// $objPHPExcel->getActiveSheet()->freezePane('B2');			

					//estilos
					// $objPHPExcel->getActiveSheet()->getStyle("A15:AP17")->applyFromArray($estiloCabeceraTabla);
					$objPHPExcel->getActiveSheet()->getStyle("A1:AZ1")->applyFromArray($estiloTituloReporte);
					$objPHPExcel->getActiveSheet()->getStyle($merge_datos_del_sistema)->applyFromArray($estiloTituloTabla);				
					$objPHPExcel->getActiveSheet()->getStyle($merge_datos_fisicos)->applyFromArray($estiloTituloTabla);					
					$objPHPExcel->getActiveSheet()->getStyle($merge_info)->applyFromArray($estiloTituloTabla);	
					$objPHPExcel->getActiveSheet()->getStyle("A15:A17")->applyFromArray($estiloTituloTabla);
					$objPHPExcel->getActiveSheet()->getStyle("B15:D16")->applyFromArray($estiloTituloTabla);
					$objPHPExcel->getActiveSheet()->getStyle("E15:E17")->applyFromArray($estiloTituloTabla);
					$objPHPExcel->getActiveSheet()->getStyle("F15:F17")->applyFromArray($estiloTituloTabla);

					$objPHPExcel->getActiveSheet()->getStyle("B13")->applyFromArray($estiloTituloTabla);
					$objPHPExcel->getActiveSheet()->getStyle("C13")->applyFromArray($estiloTituloTabla);
					$objPHPExcel->getActiveSheet()->getStyle("D13")->applyFromArray($estiloTituloTabla);
					$objPHPExcel->getActiveSheet()->getStyle("A3:E3")->applyFromArray($estiloTituloResumen);
					$objPHPExcel->getActiveSheet()->getStyle("A4:E13")->applyFromArray($estiloTablaResumen);										
					$objPHPExcel->getActiveSheet()->getStyle("A13:E13")->applyFromArray($estiloDiferenciaReal);
					//merge
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells("A1:AZ1");
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells("A3:E3");
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells("A4:D4");
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells("A5:D5");
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells("A6:D6");
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells("A7:D7");
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells("A8:D8");
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells("A9:D9");
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells("A10:D10");
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells("A11:D11");				
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells("A12:D12");
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells("A13:D13");

					$objPHPExcel->setActiveSheetIndex(0)->mergeCells("B13:D13");
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells("B14:D14");

					$objPHPExcel->setActiveSheetIndex(0)->mergeCells($merge_datos_del_sistema);
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells($merge_datos_fisicos);
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells($merge_info);

					$objPHPExcel->setActiveSheetIndex(0)->mergeCells("A15:A17");				
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells("B15:D16");
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells("E15:E17");
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells("F15:F17");				

					//height
					$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(30);
					$objPHPExcel->getActiveSheet()->getRowDimension('3')->setRowHeight(20);

					// Se asigna el nombre a la hoja
					$objPHPExcel->getActiveSheet()->setTitle("Libro 1");

					// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
					$objPHPExcel->setActiveSheetIndex(0);

					// Se manda el archivo al navegador web, con el nombre que se indica (Excel2007)
					header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
					header('Content-Disposition: attachment;filename="'.$titulo_file_reporte_cajas.'.xls"');
					header('Cache-Control: max-age=0');

					$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
					$objWriter->save('php://output');
					exit; 

				}else{
					print_r('No hay resultados para mostrar');            
				}
			}	                  
	}

?>