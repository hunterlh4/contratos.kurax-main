<?php
include '/var/www/html/sys/helpers.php';
//$return = array();
$return["file_registros"]=0;
$return["repositorios_insertados"]=0;
$return["repositorios_updateados"]=0;
$return["repositorios_nothing"]=0;
$return["cabeceras_insertadas"]=0;
$return["cabeceras_updateadas"]=0;
$return["cabeceras_nothing"]=0;
$return["detalles_insertados"]=0;
$return["detalles_updateados"]=0;
$return["detalles_nothing"]=0;
$return["locales_involucrados"]=0;
$return["ids_no_procesados"]=[];
//INSERT_FUNCTIONS

function repositorio_insert($repositorio){
	global $mysqli;
	global $return;

	$command = "INSERT INTO tbl_transacciones_repositorio";
	$command.="(";
	$command.=implode(",", array_keys($repositorio));
	$command.=")";
	$command.=" VALUES ";
	$command.="(";
	$command.=implode(",", $repositorio);
	$command.=")";
	$mysqli->query($command);
	$affected_rows = $mysqli->affected_rows;
	$mysqli_error = $mysqli->error;
	if($mysqli_error){
		$return["mysql_error"][]=$mysqli_error;
	}elseif($affected_rows==1){
		//$return["repo_insert_command_INSERT"][]=$command;
		$return["repositorios_insertados"]++;
	}else{
		//$return["repo_insert_command_NOTHING"][]=$command;
		$return["repositorios_nothing"]++;
	}
	return true;
}

function repositorio_update($repo){
	global $mysqli;
	global $return;

	$fields = "";
	foreach($repo as $key=>$field){
		if($key != 'at_unique_id') $fields .="{$key}={$field},";
	}
	$fields = substr($fields, 0, -1);

	//echo "<pre>"; var_dump("im here"); echo "</pre>"; die;

	$command = "UPDATE tbl_transacciones_repositorio SET {$fields} WHERE at_unique_id = ".$repo["at_unique_id"];
	$mysqli->query($command);

	preg_match_all('!\d+!', $mysqli->info, $m);
	if(isset($m[0]) && isset($m[0][0]) && $m[0][0] > 0) $return["repositorios_updateados"]++;
	else $return["repositorios_nothing"]++;

	return true;
}

function detalle_insert($detalle){
	global $mysqli;
	global $return;

	$command = "INSERT INTO tbl_transacciones_detalle";
	$command.="(";
	$command.=implode(",", array_keys($detalle));
	$command.=")";
	$command.=" VALUES ";
	$command.="(";
	$command.=implode(",", $detalle);
	$command.=")";
	
	$mysqli->query($command);
	$affected_rows = $mysqli->affected_rows;
	if(!array_key_exists("ERROR_MYSQL", $return)){
		$return["ERROR_MYSQL"]=[];
	}
	if($mysqli->error){
		$return["ERROR_MYSQL"][]=$mysqli->error;
		// print_r($mysqli->error);
		// echo $sql_insert;
		// exit();
	}elseif($affected_rows==1){
		// $return["deta_insert_command_INSERT"][]=$command;
		$return["detalles_insertados"]++;
	}else{
		// $return["deta_insert_command_NOTHING"][]=$command;
		$return["detalles_nothing"]++;
	}
	return true;
}

function detalle_update($detalle){
	global $mysqli;
	global $return;

	$fields = "";
	foreach($detalle as $key=>$field){
		if($key != 'at_unique_id') $fields .="{$key}={$field},";
	}
	$fields = substr($fields, 0, -1);

	$command = "UPDATE tbl_transacciones_detalle SET {$fields} WHERE at_unique_id = ".$detalle["at_unique_id"];
	$mysqli->query($command);

	preg_match_all('!\d+!', $mysqli->info, $m);
	if(isset($m[0]) && isset($m[0][0]) && $m[0][0] > 0) $return["detalles_updateados"]++;
	else $return["detalles_nothing"]++;
	return true;
}

function cabecera_insert($cabecera){
	global $mysqli;
	global $return;

	$command = "INSERT INTO tbl_transacciones_cabecera";
	$command.="(";
	$command.=implode(",", array_keys($cabecera));
	$command.=")";
	$command.=" VALUES ";
	$command.="(";
	$command.=implode(",", $cabecera);
	$command.=")";
	$command.=" ON DUPLICATE KEY UPDATE ";
	$uqn=0;
	foreach ($cabecera as $key => $value) {
		if($uqn>0) { $command.=","; }
		$command.= $key." = VALUES(".$key.")";
		$uqn++;
	}

	$mysqli->query($command);
	$affected_rows = $mysqli->affected_rows;
	if($affected_rows==2){
		//$return["cabeceras_insert_command_UPDATE"][]=$command;
		$return["cabeceras_updateadas"]++;
	}elseif($affected_rows==1){
		//$return["cabeceras_insert_command_INSERT"][]=$command;
		$return["cabeceras_insertadas"]++;
	}else{
		// $return["cabeceras_insert_command_NOTHING"][]=$command;
		$return["cabeceras_nothing"]++;
	}
	return true;
}

function import_csv_to_db($data){
	global $mysqli;
	global $return;
	global $login;

	$return["time_init"]=date("Y-m-d H:i:s");
	$time_init = microtime(true);

	$monedas_arr = [];
	$monedas_query = $mysqli->query("SELECT id,sigla FROM tbl_moneda");
	while($m=$monedas_query->fetch_assoc()){
		if(!in_array($m["sigla"], $monedas_arr)){
			$monedas_arr[$m["id"]]=$m["sigla"];
		}
	}
	$local_proveedor_id = [];
		$tmp = [];
			$tmp["id"]=false;
			$tmp["local_id"]=false;
			$tmp["nombre"]=false;
			$tmp["canal_de_venta_id"]=false;
			$tmp["proveedor_id"]=false;
			$tmp["servicio_id"]=$data["servicio_id"];
			$local_proveedor_id[0]=$tmp;

			$lpi_query = $mysqli->query("SELECT id,servicio_id,canal_de_venta_id,proveedor_id,nombre,local_id 
										FROM tbl_local_proveedor_id 
										WHERE servicio_id = '".$data["servicio_id"]."' 
										AND estado = '1'");
			while ($lpi=$lpi_query->fetch_assoc()) {
				$local_proveedor_id[]=$lpi;
			}
	$local_proveedor_id_array_column_proveedor_id = array_column($local_proveedor_id, 'proveedor_id');
	
	$upload_dir = $data["file_dir"];

	$csv_arr = [];
	$file = $f = $mysqli->query("SELECT archivo FROM tbl_archivos WHERE id = '".$data["file_id"]."'")->fetch_assoc();
	$csv_file = (file_get_contents($upload_dir.$file["archivo"]));
	$csv_arr = str_getcsv($csv_file,"\n");
	//echo var_dump($csv_arr);
	//exit();
	$repos=[];
	if($data["servicio_id"]==2){
		$fecha_proceso = strstr(substr(strstr(strstr($csv_arr[0],"from")," "),1)," ",true);
		$tipo=4;
		$opt1=true;
		$new_id=0;
		foreach($csv_arr as $csv_key => $csv_val){
			if($csv_key>=2){
				$csv_val = str_replace('"',"",$csv_val);
				$csv_val = str_replace('.',"",$csv_val);
				$csv_val = str_replace(',',".",$csv_val);
				$csv_val = str_replace(["\r","\n","\t"],"",$csv_val);
				$csv_val_arr = explode(";", $csv_val);
				$new_item = [];
				if($opt1){
					$new_id++;
					$tienda_name = trim(strrchr($csv_val_arr[0]," "));
					$lp_index = array_search($tienda_name, $local_proveedor_id_array_column_proveedor_id);
					$local = $local_proveedor_id[$lp_index];
					if(!$local["local_id"]){
						if(in_array($tienda_name,$return["ids_no_procesados"])){
						}else{
							$return["ids_no_procesados"][]=$tienda_name;
						}
					}
					$repos[$new_id]["local_id"]=$local["local_id"];
					$repos[$new_id]["servicio_id"]=$data["servicio_id"];
					$repos[$new_id]["canal_de_venta_id"]=18;
					$repos[$new_id]["cashdesk"]=$tienda_name;
					$repos[$new_id]["created"]=$fecha_proceso;
					$repos[$new_id]["tickets_count"]=$csv_val_arr[3];
					$repos[$new_id]["tipo"]=$tipo;
					$repos[$new_id]["at_unique_id"]=md5($local["servicio_id"].$tipo.$tienda_name.$fecha_proceso);
					//$repos[$new_id]["moneda_id"]=1;
				}else{
					$repos[$new_id]["currency"]=$csv_val_arr[4];
					$repos[$new_id]["total_in"]=$csv_val_arr[5];
					$repos[$new_id]["total_out"]=$csv_val_arr[6];
					$repos[$new_id]["open_payouts"]=$csv_val_arr[7];
					$repos[$new_id]["global_jackpot_payout"]=$csv_val_arr[8];
					$repos[$new_id]["regional_jackpot_payout"]=$csv_val_arr[9];
					$repos[$new_id]["shop_jackpot_payout"]=$csv_val_arr[10];
					$repos[$new_id]["global_jackpot_contribution"]=$csv_val_arr[11];
					$repos[$new_id]["regional_jackpot_contribution"]=$csv_val_arr[12];
					$repos[$new_id]["shop_jackpot_contribution"]=$csv_val_arr[13];
					$repos[$new_id]["reversal"]=$csv_val_arr[14];
					$repos[$new_id]["balance"]=$csv_val_arr[15];
				}
				if($opt1){ $opt1=false; }else{ $opt1=true; }
			}
		}
	}elseif($data["servicio_id"]==3){
		//$arr_columnas = array();
		//$arr_columnas["unit_id"]="[Unit ID] Name";
		$name_fecha = strstr(substr(strrchr($file["archivo"],"_"),1),".",true);
		$f_year="20".substr($name_fecha, 6,2);
		$f_month=substr($name_fecha, 3,2);
		$f_day=substr($name_fecha, 0,2);
		$fecha_proceso=$f_year."-".$f_month."-".$f_day;
		$tipo=4;
		$gr_tipo_de_archivo=1;

		//ini juegos - nuevo
		$column_title=[];
		foreach($csv_arr as $csv_key => $csv_val){
			$csv_val = str_replace(["\r","\n","\t"],"",$csv_val);
			$csv_val_arr = explode(",", $csv_val);
			if($csv_key==0){
				array_push($column_title,$csv_val_arr);
			}
		}
		$cantidad_juegos = count($column_title[0])-23;
		//echo var_dump($column_title);exit();
		$juegos_arr_id = [];
		$juegos_arr_nombre = [];
		$juegos_arr_abreviatura = [];
		$juegos_query = $mysqli->query("SELECT id,nombre,abreviatura FROM tbl_juegos");
		while($m=$juegos_query->fetch_assoc()){
			array_push($juegos_arr_id, $m["id"]);
			array_push($juegos_arr_nombre, $m["nombre"]);
			array_push($juegos_arr_abreviatura, $m["abreviatura"]);
		}
		//echo var_dump($juegos_arr_nombre);exit();
		//fin juegos - nuevo
		$script_insert="";

		foreach($csv_arr as $csv_key => $csv_val){
			$csv_val = str_replace(["\r","\n","\t"],"",$csv_val);

			//ini agregado_para_columnas_juegos
			$largo = strlen($csv_val);
			$pos = strpos($csv_val, '"',0);
			$cadena = substr($csv_val, $pos, $largo);
			$csv_val = substr($csv_val, 0, $pos-1);
			
			$cadena = str_replace(['",'],'";',$cadena);
			$cadena = str_replace([','],'.',$cadena);
			$cadena = str_replace([';'],',',$cadena);
			$cadena = str_replace(['"'],'',$cadena);
			$csv_val = $csv_val.",".$cadena;
			//fin agregado_para_columnas_juegos

			$csv_val_arr = explode(",", $csv_val);
			if($csv_key>0){
				$proveedor_id = strval(substr(strstr($csv_val_arr[1],"]",true),1)); //[Unit ID] Name
				$staff_id = strval(substr(strstr($csv_val_arr[2],"]",true),1)); //[Staff ID] Name
				//echo $proveedor_id; echo "\n";
			
				$local = $local_proveedor_id[array_search((strtolower($proveedor_id)), $local_proveedor_id_array_column_proveedor_id)];
				$repo = [];
				//$repo["at_unique_id"]=md5($data["servicio_id"].$tipo.$fecha_proceso.$proveedor_id);
				$repo["at_unique_id"]=md5($data["servicio_id"].$tipo.$fecha_proceso.$proveedor_id.$staff_id);
				$repo["local_id"]=$local["local_id"];
				//echo $local["local_id"]; echo "\n";
				//echo $proveedor_id; echo "\n --- \n";
				if(!$local["local_id"]){
					if(in_array($proveedor_id,$return["ids_no_procesados"])){
					}else{
						$return["ids_no_procesados"][]=$proveedor_id;
					}
				}

				$repo["servicio_id"]=$data["servicio_id"];
				$repo["canal_de_venta_id"]=21;
				$repo["created"]=$fecha_proceso;
				$repo["tipo"]=$tipo;
				$repo["cashdesk"]=$proveedor_id;
				$repo["online"]=$csv_val_arr[0]; //Online
				$repo["betshop"]=$csv_val_arr[1]; //[Unit ID] Name
				$repo["external_id"]=$csv_val_arr[2]; //[Staff ID] Name
				$repo["currency"]=$csv_val_arr[3]; //Currency
				//$repo["geoip_country"]=$csv_val_arr[3]; //Country
				$repo["stake"]=$csv_val_arr[5]; //Stake
				//$repo["tax_value"]=$csv_val_arr[5]; //Stake Tax
				$repo["cancelled"]=$csv_val_arr[7];//Cancelled
				if($gr_tipo_de_archivo=3){
					$repo["cancelled_tickets"]=$csv_val_arr[8];//Cancelled Tickets
					$repo["paid_out_cash"]=$csv_val_arr[11];//Paid out
					$repo["jackpot_paid"]=$csv_val_arr[13];//Jackpot Paid--------------------------------------
					$repo["mega_jackpot_paid"]=$csv_val_arr[14];//Mega Jackpot Paid--------------------------------------
					$repo["balance"]=$csv_val_arr[15];//Balance
					//$repo["balance_percentage"]=$csv_val_arr[14];//Balance %
					$repo["open_win"]=$csv_val_arr[17];//Open win
					$repo["jackpot_not_claimed"]=$csv_val_arr[18];//Jackpot not claimed--------------------------------------
					$repo["mega_jackpot_not_claimed"]=$csv_val_arr[19];//Mega Jackpot not claimed--------------------------------------
					$repo["jackpot_contribution"]=$csv_val_arr[20];//Jackpot contribution--------------------------------------
					$repo["mega_jackpot_contribution"]=$csv_val_arr[21];//Mega Jackpot contribution--------------------------------------
					$repo["tickets_count"]=$csv_val_arr[22];
				}else{
					//$repo["cancelled_tax"]=$csv_val_arr[7];//Cancelled Tax
					//$repo["commission"]=$csv_val_arr[8];//Commission
					$repo["paid_out_cash"]=$csv_val_arr[10];//Paid out
					//$repo["paid_out_tax"]=$csv_val_arr[10];//Paid out Tax
					$repo["jackpot_paid"]=$csv_val_arr[12];//Jackpot Paid--------------------------------------
					$repo["mega_jackpot_paid"]=$csv_val_arr[13];//Mega Jackpot Paid--------------------------------------
					$repo["balance"]=$csv_val_arr[14];//Balance
					//$repo["balance_percentage"]=$csv_val_arr[14];//Balance %
					$repo["open_win"]=$csv_val_arr[16];//Open win
					$repo["jackpot_not_claimed"]=$csv_val_arr[17];//Jackpot not claimed--------------------------------------
					$repo["mega_jackpot_not_claimed"]=$csv_val_arr[18];//Mega Jackpot not claimed--------------------------------------
					$repo["jackpot_contribution"]=$csv_val_arr[19];//Jackpot contribution--------------------------------------
					$repo["mega_jackpot_contribution"]=$csv_val_arr[20];//Mega Jackpot contribution--------------------------------------
					//$repo["________"]=$csv_val_arr[20];//Peru League 2017
					//$repo["________"]=$csv_val_arr[21];//Keno
					//$repo["________"]=$csv_val_arr[22];//Dogs
					//$repo["________"]=$csv_val_arr[23];//Total
					// 
				}
				if($gr_tipo_de_archivo == 1){
					$repo["tickets_count"]=$csv_val_arr[(count($csv_val_arr)-1)];
				}elseif($gr_tipo_de_archivo == 2){
					$repo["tickets_count"]=$csv_val_arr[21];
				}else{

				}
				
				$repos[]=$repo;

				if($local["local_id"]){
					for ($i = 23; $i < count($column_title[0]); $i++) {
						$nombre_juego = $column_title[0][$i];
						//echo $nombre_juego."</br>";
						//echo array_search($nombre_juego, $juegos_arr_nombre);

						$valor_juego = $csv_val_arr[$i];
						$valor_juego = str_replace(['(Stake: '],'',$valor_juego);
						$valor_juego = str_replace([')'],'',$valor_juego);
						$reg_juego_arr = explode(" ", $valor_juego);
						$index_juego = array_search($nombre_juego, $juegos_arr_nombre);
						if($index_juego>=0){
							$id_juego = $juegos_arr_id[$index_juego];
							$abreviatura_juego = $juegos_arr_abreviatura[$index_juego];
							
							$at_unique_id = md5($local["local_id"].$data["servicio_id"].$fecha_proceso.$id_juego.$staff_id);
							if($reg_juego_arr[0]>0){
								$script_insert .="('".$at_unique_id."','".$repo["at_unique_id"]."','".$local["local_id"]."','".$data["servicio_id"]."','".$tipo."',21,'".$fecha_proceso."','".$id_juego."','".$abreviatura_juego."','".$reg_juego_arr[0]."','".$reg_juego_arr[1]."','".$reg_juego_arr[2]."'),";
							}
							
						}
					}
				}
				//exit();
				// ALTER TABLE `tbl_transacciones_repositorio`
				// ADD `jackpot_paid` decimal(20,2) NULL COMMENT 'GR:12',
				// ADD `mega_jackpot_paid` decimal(20,2) NULL COMMENT 'GR:13',
				// ADD `jackpot_not_claimed` decimal(20,2) NULL COMMENT 'GR:17',
				// ADD `mega_jackpot_not_claimed` decimal(20,2) NULL COMMENT 'GR:18',
				// ADD `jackpot_contribution` decimal(20,2) NULL COMMENT 'GR:19',
				// ADD `mega_jackpot_contribution` decimal(20,2) NULL COMMENT 'GR:20';
			}else{
				if($csv_val_arr[21]=="Total"){
					$gr_tipo_de_archivo=2;
				}elseif($csv_val_arr[22]=="Total"){
					$gr_tipo_de_archivo=3;
				}
				//$csv_val_arr = explode(",", $csv_val);				
				//print_r($csv_val_arr);
			}
		}

		//exit();
		$script_insert = substr($script_insert, 0, -1);
		$mysqli->query("START TRANSACTION");
		$tbl_resumen_juego_command = "INSERT INTO tbl_resumen_juego (
										`at_unique_id`,
										`detalle_at_unique_id`,
										`local_id`, 
										`servicio_id`, 
										`tipo`, 
										`canal_de_venta_id`, 
										`created`, 
										`juego_id`, 
										`juego_abreviatura`, 
										`cantidad_tickets`, 
										`stake`,
										`currency`
										)values ".$script_insert."
										ON DUPLICATE KEY UPDATE cantidad_tickets=values(cantidad_tickets),stake=values(stake),currency=values(currency)";
		//echo "\n"; echo "\n"; echo $tbl_resumen_juego_command; echo "\n";
		//exit();
		$mysqli->query($tbl_resumen_juego_command);	
		$mysqli->query("COMMIT");
		
	}elseif($data["servicio_id"]==5){
		$tipo=4;

		foreach($csv_arr as $csv_key => $csv_val){
			if($csv_key>0){
				$csv_val_arr = explode(",", $csv_val);

				if(strpos($csv_val_arr[0], '/') !== false){
					$f_year=substr($csv_val_arr[0], 6,4);
					$f_month=substr($csv_val_arr[0], 3,2);
					$f_day=substr($csv_val_arr[0], 0,2);
					$fecha_proceso=$f_year."-".$f_month."-".$f_day;
				}else{
					$fecha_proceso = $csv_val_arr[0];
				}
				// $f_year=substr($csv_val_arr[0], 6,4);
				// $f_month=substr($csv_val_arr[0], 3,2);
				// $f_day=substr($csv_val_arr[0], 0,2);
				// $fecha_proceso=$f_year."-".$f_month."-".$f_day;
				// $fecha_proceso = $csv_val_arr[0];

				$proveedor_id = $csv_val_arr[1];
				// print_r($local_proveedor_id);
				$local = $local_proveedor_id[array_search($proveedor_id, $local_proveedor_id_array_column_proveedor_id)];

				if(!$local["local_id"]){
					if(in_array($proveedor_id,$return["ids_no_procesados"])){
					}else{
						$return["ids_no_procesados"][]=$proveedor_id;
					}
				}

				$repo = [];
				$repo["at_unique_id"]=md5($data["servicio_id"].$tipo.$fecha_proceso.$proveedor_id);
				$repo["local_id"]=$local["local_id"];
				$repo["servicio_id"]=$data["servicio_id"];
				$repo["canal_de_venta_id"]=22;
				$repo["created"]=$fecha_proceso;
				$repo["tipo"]=$tipo;
				$repo["cashdesk_id"]=$proveedor_id;
				$repo["currency"]="PEN";

				$repo["bets_amount"]=$csv_val_arr[2];
				$repo["paid_win_amount"]=$csv_val_arr[3];

				$repos[]=$repo;
				// print_r($repo);
				// print_r($csv_val_arr); echo "\n";

				// echo "\n";echo "\n";echo "\n";
				// if($csv_key>2){ exit(); }
			}
		}
		// exit();
	}elseif($data["servicio_id"]==6){
		//$arr_columnas = array();
		//$arr_columnas["unit_id"]="[Unit ID] Name";
		//echo var_dump($csv_arr);exit();
		$fecha_proceso = date("Y-m-d",strtotime(strstr(str_replace('.','-',strstr(strstr($csv_arr[0],":"),"-",true)),' ')));
		$tipo=4;
		//echo count($csv_arr);exit();

		foreach($csv_arr as $csv_key => $csv_val){
			$csv_val = str_replace(["\r","\n","\t"],"",$csv_val);
			$csv_val_arr = explode(";", $csv_val);
			
			if($csv_key>6 && $csv_key<count($csv_arr)-1){
				$proveedor_id = $csv_val_arr[0]; 
				$local = $local_proveedor_id[array_search((strtolower($proveedor_id)), $local_proveedor_id_array_column_proveedor_id)];
				$repo = [];
				//$repo["at_unique_id"]=md5($data["servicio_id"].$tipo.$fecha_proceso.$proveedor_id);
				$repo["at_unique_id"]=md5($data["servicio_id"].$tipo.$fecha_proceso.$proveedor_id);
				$repo["local_id"]=$local["local_id"];
				//echo $local["local_id"]; echo "\n";
				//echo $proveedor_id; echo "\n --- \n";
				if(!$local["local_id"]){
					if(in_array($proveedor_id,$return["ids_no_procesados"])){
					}else{
						$return["ids_no_procesados"][]=$proveedor_id;
					}
				}

				$sales_cash = explode("/",str_replace('=','',$csv_val_arr[3]));
				$sales_cash_math = (int)$sales_cash[0]/(int)$sales_cash[1];
				$winning_cash = explode("/",str_replace('=','',$csv_val_arr[4]));
				$winning_cash_math = (int)(str_replace('-','',$winning_cash[0]))/(int)$winning_cash[1];
			
				$repo["servicio_id"]=$data["servicio_id"];
				$repo["canal_de_venta_id"]=$local["canal_de_venta_id"];
				$repo["created"]=$fecha_proceso;
				$repo["tipo"]=$tipo;
				$repo["cashdesk"]=$proveedor_id;
				$repo["betshop"]=$csv_val_arr[1]; //place
				$repo["currency"]=$csv_val_arr[2]; //Currency
				$repo["apostado"]= $sales_cash_math ;//Sales Cash
				$repo["ganado"]= $winning_cash_math;//Winning Cash
				// $repo["cancelled"]= eval($csv_val_arr[5]);//Bonus Cash
				//---$repo["cancelled"]= eval($csv_val_arr[6]);//Net Cash resultado negocio
				// $repo["cancelled"]= eval($csv_val_arr[7]);//Cash Balance
				// $repo["cancelled"]= eval($csv_val_arr[8]);//Reg. member.card
				// $repo["cancelled"]= eval($csv_val_arr[9]);//Mem.card customers
				// $repo["cancelled"]= eval($csv_val_arr[10]);//Open P.
				// $repo["cancelled"]= eval($csv_val_arr[11]);//Cash book
				$repo["tickets_count"]= $csv_val_arr[12];//Tickets cash
				// $repo["cancelled"]= eval($csv_val_arr[13]);//Canceled T. cash
				// $repo["cancelled"]= eval($csv_val_arr[14]);//Open T. cash
				// $repo["cancelled"]= eval($csv_val_arr[15]);//Service C
				// $repo["cancelled"]= eval($csv_val_arr[16]);//Status
	
				$repos[]=$repo;
			}
		}
	}

	$data["tipo"]=$tipo;
	$return["file_registros"]=count($repos);
	$return["repos"]=$repos;
	//print_r($repos);
	//exit();

	$detalles = [];

	$inUnique = "";
	foreach ($repos as $repo_key => $repo_val) $inUnique .= "'".$repo_val["at_unique_id"]."',";
	$inUnique = substr($inUnique, 0, -1);

	$response = $mysqli->query("SELECT at_unique_id FROM tbl_transacciones_repositorio WHERE at_unique_id IN ({$inUnique})");
	$uniqueListRepo = [];
	while($r = $response->fetch_assoc()) $uniqueListRepo[] = $r["at_unique_id"];

	$response = $mysqli->query("SELECT at_unique_id FROM tbl_transacciones_detalle WHERE at_unique_id IN ({$inUnique})");
	$uniqueListDetalle = [];
	while($r = $response->fetch_assoc()) $uniqueListDetalle[] = $r["at_unique_id"];


	$mysqli->query("START TRANSACTION");
	foreach ($repos as $repo_key => $repo_val) {
		//print_r($repo_val);
		$repo_to_db = data_to_db($repo_val);
		//print_r($repo_to_db);
		if(!in_array($repo_val['at_unique_id'], $uniqueListRepo)) $repositorio_id = repositorio_insert($repo_to_db);
		else $repositorio_id = repositorio_update($repo_to_db);

		$detalle = [];
			$detalle["at_unique_id"]=$repo_val["at_unique_id"];
			$detalle["servicio_id"]=$repo_val["servicio_id"];
			$detalle["tipo"]=$repo_val["tipo"];
			$detalle["canal_de_venta_id"]=$repo_val["canal_de_venta_id"];
			$detalle["local_id"]=$repo_val["local_id"];
			$detalle["created"]=$repo_val["created"];
			$detalle["paid_day"]=$repo_val["created"];
			$detalle["paid_local_id"]=$repo_val["local_id"];
			$detalle["paid_canal_de_venta_id"]=$repo_val["canal_de_venta_id"];
			$detalle["moneda_id"]=array_search($repo_val["currency"], $monedas_arr);

			if($data["servicio_id"]==2){
				$detalle["income"]=$repo_val["total_in"];
				$detalle["ganado"]=$repo_val["total_out"];
				$detalle["apostado"]=($repo_val["total_in"]-$repo_val["reversal"]);
				$detalle["withdraw"]=$repo_val["reversal"];
				$detalle["num_tickets"]=$repo_val["tickets_count"];
			}
			if($data["servicio_id"]==3){
				//$detalle["income"]=($repo_val["stake"]-$repo_val["cancelled"]);
				$detalle["income"]=$repo_val["stake"];
				// $detalle["ganado"]=($repo_val["open_win"]+$repo_val["paid_out_cash"]);
				// $detalle["ganado"]=($repo_val["open_win"] + $repo_val["paid_out_cash"] + $repo_val["jackpot_paid"] + $repo_val["mega_jackpot_paid"]);
				$detalle["ganado"]=($repo_val["paid_out_cash"] + $repo_val["jackpot_paid"] + $repo_val["mega_jackpot_paid"]);
				$detalle["apostado"]=($repo_val["stake"]-$repo_val["cancelled"]);
				$detalle["withdraw"]=$repo_val["cancelled"];
				$detalle["num_tickets"]=$repo_val["tickets_count"];
			}
			if($data["servicio_id"]==5){
				$detalle["apostado"]=($repo_val["bets_amount"]);
				$detalle["ganado"]=($repo_val["paid_win_amount"]);
			}
			if($data["servicio_id"]==6){
				$detalle["num_tickets"]=$repo_val["tickets_count"];
				$detalle["apostado"]=($repo_val["apostado"]);
				$detalle["ganado"]=($repo_val["ganado"]);
			}
		$detalles[]=$detalle;
	}
	
	$mysqli->query("COMMIT");

	$mysqli->query("START TRANSACTION");
	foreach ($detalles as $deta_key => $deta_val) {
		$deta_to_db = data_to_db($deta_val);
		if(!in_array($deta_val['at_unique_id'], $uniqueListDetalle)) $detalle_id = detalle_insert($deta_to_db);
		else $detalle_id = detalle_update($deta_to_db);
	}
	$mysqli->query("COMMIT");

	//exit();

	$ids_no_procesados = "NULL";
	if(count($return["ids_no_procesados"])){
		$ids_no_procesados = "'".json_encode($return["ids_no_procesados"])."'";
	}
	//print_r($return["ids_no_procesados"]); echo "\n";
	//echo count($return["ids_no_procesados"]); echo "\n";
	//exit();
	$return["time_end"]=date("Y-m-d H:i:s");
	$proceso_id = md5(date("c").$data["file_id"]);
	$tbl_transacciones_procesos_command = "INSERT INTO tbl_transacciones_procesos (
												`at_unique_id`, 
												`servicio_id`, 
												`tipo`, 
												`archivo_id`, 
												`time_init`, 
												`file_registros`, 
												`repositorios_updateados`, 
												`repositorios_insertados`, 
												`repositorios_nothing`,
												`detalles_insertados`, 
												`detalles_updateados`, 
												`detalles_nothing`, 
												`locales_involucrados`, 
												`time_end`, 
												`ids_no_procesados`,
												`usuario_id`
											) VALUES (
												'".$proceso_id."',
												'".$data["servicio_id"]."',
												'".$data["tipo"]."',
												'".$data["file_id"]."',
												'".$return["time_init"]."',
												'".$return["file_registros"]."',
												'".$return["repositorios_updateados"]."',
												'".$return["repositorios_insertados"]."',
												'".$return["repositorios_nothing"]."',
												'".$return["detalles_insertados"]."',
												'".$return["detalles_updateados"]."',
												'".$return["detalles_nothing"]."',
												'".$return["locales_involucrados"]."',
												'".$return["time_end"]."',
												".($ids_no_procesados).",
												'".$login["id"]."'
												)";
	//echo "\n"; echo "\n"; echo $tbl_transacciones_procesos_command; echo "\n";
	$mysqli->query($tbl_transacciones_procesos_command);
}

function transacciones_repositorio($data){
	return false;
}

function process_data($what){
	global $mysqli;
	global $return;
	//print_r($what);
	//echo "\n\n";
	$tickets=[];
	$tickes_command = "SELECT 
							id
							,canal_de_venta_id
							,apostado
							,ganado
							,local_id
							,(SELECT zona_id FROM tbl_locales WHERE id = local_id) AS zona_id
							,moneda_id
							,repositorio_id
							,ticket_id
							,created
							,player_id
							,paid_day
							,paid_local_id
							,servicio_id
						FROM tbl_transacciones_detalle
						WHERE created >= '".$what["fecha_proceso_inicio"]."' 
							AND created <= '".$what["fecha_proceso_fin"]."'
							AND servicio_id = '".$what["servicio_id"]."'
							/*AND local_id = '104'*/";
	$tickes_query = $mysqli->query($tickes_command);
	//echo $tickes_command;
	//echo "\n\n";
	//print_r($tickes_query->num_rows);

	$big_data = [];
	//foreach ($tickets as $big_data_key => $big_data_value) {
	while($t=$tickes_query->fetch_assoc()){
		//$local_id = $t["local_id"];
		$big_data[$t["canal_de_venta_id"]][$t["local_id"]][]=$t;
	}

	foreach ($big_data as $canal_de_venta_id => $value) { //servicios
		foreach ($value as $local_id => $v) { //tiendas
			//echo $local_id;
			//echo "\n";
			$cabecera = [];
			$cabecera["fecha_proceso"]=NULL;
			$cabecera["canal_de_venta_id"]=$canal_de_venta_id;
			$cabecera["producto_id"] = in_array((int)$cabecera["canal_de_venta_id"], [15,16,17,19,23,24,25,26,27]) ? 1: 2;
			$cabecera["servicio_id"]=$what["servicio_id"];
			$cabecera["num_registros"]=count($v);
			$cabecera["num_tickets"]=count($v);
			$cabecera["total_apostado"]=array_sum(array_column($v, 'apostado')); 
			$cabecera["total_ganado"]=array_sum(array_column($v, 'ganado'));
			$cabecera["local_id"]=$local_id;
			$cabecera["total_pagado"]=0;

			foreach ($v as $t_k => $t_v) { //tickets
				if($t_v["zona_id"]) $cabecera["zona_id"]=(int)$t_v["zona_id"];
				$cabecera["moneda_id"]=$t_v["moneda_id"];
				$cabecera["fecha_proceso"]=$t_v["created"];
				if($t_v["paid_day"] == NULL){
				}else{
					//echo $t_v["paid_day"]; echo "\n";
					if($t_v["paid_local_id"]==$local_id){
						$cabecera["total_pagado"]+=$t_v["ganado"];
					}
				}
				//$cabecera["total_apostado"]+=$t_v["apostado"];
				//$cabecera["total_ganado"]+=$t_v["ganado"];
			}

			//$cabecera_to_db = data_to_db($cabecera);
			//$cabecera_id = cabecera_insert($cabecera_to_db);
			print_r($cabecera); echo "\n";
		}
	}
	//print_r($big_data);
	echo "\n\n";
}

function transacciones_build_liquidaciones($data){
	global $mysqli;
	global $return;
	global $login;
	//print_r($data); exit();
	if(array_key_exists("servicio_id", $data)){
		$date = date("Y-m-d H:i:s");
		$cabeceras = [];
		$data["fecha_inicio"] = date("Y-m-d",strtotime(substr($data["fecha"],0,15)));
		$data["fecha_fin"] = date("Y-m-d",strtotime($data["fecha_inicio"]." +1 day"));

		$return["data"]=$data;
		$return["servicio_id"] = $data["servicio_id"];

		$proceso_id = false;
		if(array_key_exists("proceso_id", $data)){
			$proceso_id = $data["proceso_id"];
			if($proceso_id=="false"){
				$proceso_id = false;
			}
		}

		$testing = false;
		// $testing = true;
		if(!$testing){
			if($proceso_id){
				$new_proceso_command_update = "UPDATE tbl_transacciones_procesos 
												SET 
													fecha_fin = '".$data["fecha_inicio"]."'
													,time_end = '".$date."'
												WHERE at_unique_id = '".$proceso_id."'";
			}else{
				$proceso_id = md5(date("c").$data["servicio_id"]);
				$new_proceso_command_insert = "INSERT INTO tbl_transacciones_procesos (at_unique_id) VALUES ('".$proceso_id."')";
				$new_proceso_query = $mysqli->query($new_proceso_command_insert);
				$new_proceso_command_update = "UPDATE tbl_transacciones_procesos 
												SET 
													fecha = '".$date."'
													,fecha_inicio = '".$data["fecha_inicio"]."'
													,fecha_fin = '".$data["fecha_inicio"]."'
													,servicio_id = '".$data["servicio_id"]."'
													,tipo = 'liquidacion'
													,time_init = '".$date."'
													,time_end = '".$date."'
													,usuario_id = '".$login["id"]."'
													,estado = '0'
												WHERE at_unique_id = '".$proceso_id."'";
			}
			$mysqli->query($new_proceso_command_update);
		}

		$return["proceso_id"]=$proceso_id;
		if((int)$data["servicio_id"] == 9){
			$data["fecha"] = $data["fecha_inicio"]; 
			$query = "
				SELECT
					l.id as local_id,
					l.cc_id,
					l.zona_id
				FROM tbl_locales l
				INNER JOIN tbl_local_caja_detalle_tipos dt ON dt.local_id = l.id
				WHERE dt.detalle_tipos_id = 15
			";
			$locales = [];
			$query_result = $mysqli->query($query);
			if($mysqli->error){ echo $mysqli->error; die; }
			while($r = $query_result->fetch_assoc()) $locales[$r["cc_id"]] = $r;


			$bingo_tickets = [];
			$query = "
				SELECT 
					bt.ticket_id,
					bt.sell_local_id,
					bt.paid_local_id,
					bt.amount,
					bt.winning,
					bt.status
				FROM tbl_repositorio_bingo_tickets bt
				WHERE 
					bt.created >= '{$data["fecha_inicio"]}'
					AND bt.created < '{$data["fecha_fin"]}'
			";
			$query_result = $mysqli->query($query);
			if($mysqli->error){ echo $mysqli->error; die; }
			while($r = $query_result->fetch_assoc()) $bingo_tickets["sell"][$r["sell_local_id"]][] = $r;

			$query = "
				SELECT 
					bt.ticket_id,
					bt.sell_local_id,
					bt.paid_local_id,
					bt.winning,
					bt.status,
					bt.amount
				FROM tbl_repositorio_bingo_tickets bt
				WHERE 
					bt.status IN('Paid', 'Refunded')
					AND bt.paid_at >= '{$data["fecha_inicio"]}'
					AND bt.paid_at < '{$data["fecha_fin"]}'
			";
			$query_result = $mysqli->query($query);
			if($mysqli->error){ echo $mysqli->error; die; }
			while($r = $query_result->fetch_assoc()) $bingo_tickets["paid"][$r["paid_local_id"]][] = $r;

			$db_cabeceras = [];
			foreach (array_keys($locales) as $cc_id) {
				// if(!in_array($cc_id, [3002,3313])){ continue; }
				// print_r($cc_id); exit();
				$response_tickets = [
					'count_tickets_apostados' => 0,
					'count_tickets_ganados' => 0,
					'count_tickets_pagados' => 0,
					'pagado_en_otra_tienda' => 0,
					'pagado_de_otra_tienda' => 0,
					'total_apostado' => 0,
					'total_ganado' => 0,
					'total_pagado' => 0,
					'total_anulado' => 0
				];

				$db_cabeceras[$cc_id] = [
					'at_unique_id' => md5($proceso_id.$data["fecha_inicio"]."30".$data["servicio_id"].$cc_id),
					'proceso_unique_id' => $proceso_id,
					'fecha_proceso' => $date,
					'fecha' => $data["fecha"],
					'local_id' => $locales[$cc_id]["local_id"],
					'zona_id' => $locales[$cc_id]["zona_id"],
					'servicio_id' => $data["servicio_id"],
					'canal_de_venta_id' => 30,
					'producto_id' => 4,
					'num_registros' => 0,
					'num_tickets' => 0,
					'num_tickets_ganados' => 0,
					'num_tickets_ganados_pagados' => 0,
					'moneda_id' => '1',
					'total_apostado' => 0,
					'cashdesk_apostado' => 0,
					'total_ganado' => 0,
					'cashdesk_ganado' => 0,
					'total_pagado' => 0,
					'cashdesk_pagado' => 0,
					'total_produccion' => 0,
					'cashdesk_produccion' => 0,
					'caja_fisico' => 0,
					'cashdesk_caja_fisico' => 0,
					'total_depositado' => 0,
					'total_anulado_retirado' => 0,
					'total_ingresado' => 0,
					'total_devuelto' => 0,
					'total_depositado_web' => null,
					'total_retirado_web' => null,
					'total_caja_web' => null,
					'porcentaje_cliente' => null,
					'total_cliente' => null,
					'porcentaje_freegames' => null,
					'total_freegames' => null,
					'pagado_en_otra_tienda' => 0,
					'pagado_de_otra_tienda' => 0,
					'total_pagos_fisicos' => 0,
					'retirado_de_otras_tiendas' => null,
					'cashdesk_balance' => null,
					'tipo_de_cambio_id' => null
				];

				if(isset($bingo_tickets["sell"][$cc_id])){
					foreach ($bingo_tickets["sell"][$cc_id] as $bingo_ticket) {
						$response_tickets["total_apostado"] += $bingo_ticket["amount"];
						$response_tickets["count_tickets_apostados"]++;

						if($bingo_ticket["winning"] > 0){
							// if($bingo_ticket["paid_local_id"] && $bingo_ticket["paid_local_id"] != $bingo_ticket["sell_local_id"]){
							// if($bingo_ticket["paid_local_id"] && $bingo_ticket["paid_local_id"] != $cc_id){
							// 	$response_tickets["pagado_en_otra_tienda"] += $bingo_ticket["winning"];
							// }
							// else{
								$response_tickets["total_ganado"] += $bingo_ticket["winning"];
							// }
							$response_tickets["count_tickets_ganados"]++;
						}
					}
				}

				if(isset($bingo_tickets["paid"][$cc_id])){
					foreach ($bingo_tickets["paid"][$cc_id] as $bingo_ticket) {
						if($bingo_ticket["status"] == 'Refunded'){
							$response_tickets["total_anulado"] += $bingo_ticket["amount"];
						}
						else{
							// if($bingo_ticket["paid_local_id"] != $bingo_ticket["sell_local_id"]){
							if($bingo_ticket["sell_local_id"] != $cc_id){
								$response_tickets["pagado_de_otra_tienda"] += $bingo_ticket["winning"];
							}
							else{
								$response_tickets["total_pagado"] += $bingo_ticket["winning"];
							}
							$response_tickets["count_tickets_pagados"]++;
						}	
					}
				}
				foreach ($bingo_tickets["paid"] as $paid_local_id => $tickets_paid) {
					foreach ($tickets_paid as $k => $bingo_ticket) {
					// print_r($bingo_ticket); exit();
						if($bingo_ticket["sell_local_id"] == $cc_id){
							if($paid_local_id != $cc_id){
								// print_r($tickets_paid);
								$response_tickets["pagado_en_otra_tienda"] += $bingo_ticket["winning"];
							}
						}
					}
				}
				// print_r($response_tickets["pagado_en_otra_tienda"]);
				// exit();


				$queryBingo = "
				SELECT 
				cf.id,
				cf.monto,
				c.local_id,
				c.id AS contrato_id ,
				l.nombre,
				l.zona_id,
				l.red_id,
				p.id AS producto,
				p.nombre AS pro_nombre
				FROM tbl_contrato_formulas cf
				LEFT JOIN tbl_contratos c ON (c.id = cf.contrato_id)
				LEFT JOIN tbl_locales l ON (l.id = c.local_id)
				LEFT JOIN tbl_contrato_productos cp ON (cp.id = cf.producto_id)
				LEFT JOIN tbl_productos p ON (p.id = cp.producto_id)
				WHERE cf.estado = 1 AND 
				p.id=8 AND
				c.local_id = ".$locales[$cc_id]["local_id"]
				;
				
				$porcentajeMontoFormulaBingoCliente=0;
				$red_id = 0;
				$totalBingoCliente=0;
				$porcentajeMontoFormulaBingoFreeGames=0;
				$totalBingoFreegames=0;
				try {
					$query_result = $mysqli->query($queryBingo);
					while($formulaBingo=$query_result->fetch_assoc()){
						$porcentajeMontoFormulaBingoCliente=$formulaBingo["monto"];	
						$red_id = $formulaBingo["red_id"];
					}
					if ($porcentajeMontoFormulaBingoCliente) {
						// $porcentajeMontoFormulaBingoFreeGames = number_format((100 - $porcentajeMontoFormulaBingoCliente),2);
						//if($red_id==5){
						//	$porcentajeMontoFormulaBingoFreeGames = number_format((100 - $porcentajeMontoFormulaBingoCliente),2);
						//	$resutadoDeNegocio = ($response_tickets["total_apostado"] - $response_tickets["total_ganado"]);
                        //	$totalBingoCliente = (($resutadoDeNegocio * $porcentajeMontoFormulaBingoCliente) / 100);
						//	$totalBingoFreegames = ($resutadoDeNegocio)-$totalBingoCliente;
							
						//}else{
							$porcentajeMontoFormulaBingoFreeGames = number_format((100 - $porcentajeMontoFormulaBingoCliente),2);
							$totalApostado = ($response_tickets["total_apostado"] - $response_tickets["total_anulado"]);
							$totalBingoCliente = (($totalApostado * $porcentajeMontoFormulaBingoCliente) / 100);
							$totalBingoFreegames = ($totalApostado)-$totalBingoCliente;	
						//}
						
					}					
				} catch (\Throwable $th) {
					
				}


				$db_cabeceras[$cc_id] = [
					'at_unique_id' => md5($proceso_id.$data["fecha_inicio"]."30".$data["servicio_id"].$cc_id),
					'proceso_unique_id' => $proceso_id,
					'fecha_proceso' => $date,
					'fecha' => $data["fecha"],
					'local_id' => $locales[$cc_id]["local_id"],
					'zona_id' => $locales[$cc_id]["zona_id"],
					'servicio_id' => $data["servicio_id"],
					'canal_de_venta_id' => 30,
					'producto_id' => 4,
					'num_registros' => $response_tickets["count_tickets_apostados"],
					'num_tickets' => $response_tickets["count_tickets_apostados"],
					'num_tickets_ganados' => $response_tickets["count_tickets_ganados"],
					'num_tickets_ganados_pagados' => $response_tickets["count_tickets_pagados"],
					'moneda_id' => '1',
					'total_apostado' =>  ($response_tickets["total_apostado"] - $response_tickets["total_anulado"]),
					'cashdesk_apostado' => ($response_tickets["total_apostado"] - $response_tickets["total_anulado"]),
					'total_ganado' => $response_tickets["total_ganado"],
					'cashdesk_ganado' => $response_tickets["total_ganado"],
					'total_pagado' => ($response_tickets["total_pagado"] + $response_tickets["pagado_en_otra_tienda"]),
					'cashdesk_pagado' => $response_tickets["total_pagado"],
					'total_produccion' => ($response_tickets["total_apostado"] - $response_tickets["total_anulado"] - $response_tickets["total_pagado"] - $response_tickets["pagado_en_otra_tienda"]),
					'resultado_negocio' => ($response_tickets["total_apostado"] - $response_tickets["total_ganado"]),
					'cashdesk_produccion' => ($response_tickets["total_apostado"] - $response_tickets["total_anulado"] - $response_tickets["total_pagado"]),
					'caja_fisico' => ($response_tickets["total_apostado"] - $response_tickets["total_anulado"] - $response_tickets["total_pagado"] - $response_tickets["pagado_de_otra_tienda"]),
					'cashdesk_caja_fisico' => ($response_tickets["total_apostado"] - $response_tickets["total_anulado"] - $response_tickets["total_pagado"] - $response_tickets["pagado_de_otra_tienda"]),
					'total_depositado' => $response_tickets["total_apostado"],
					'total_anulado_retirado' => $response_tickets["total_anulado"],
					'total_ingresado' => null,
					'total_devuelto' => null,
					'total_depositado_web' => 0, //null,
					'total_retirado_web' => 0, //null,
					'total_caja_web' => 0, //null,
					'porcentaje_cliente' => $porcentajeMontoFormulaBingoCliente,
					'total_cliente' => $totalBingoCliente,
					'porcentaje_freegames' => $porcentajeMontoFormulaBingoFreeGames,
					'total_freegames' => $totalBingoFreegames,
					'pagado_en_otra_tienda' => $response_tickets["pagado_en_otra_tienda"],
					'pagado_de_otra_tienda' => $response_tickets["pagado_de_otra_tienda"],
					'total_pagos_fisicos' => ($response_tickets["total_pagado"]),
					'retirado_de_otras_tiendas' => null,
					'cashdesk_balance' => null,
					'tipo_de_cambio_id' => null,
					'estado' => 1
				];
			}
			// exit();
			// print_r($db_cabeceras); exit();

			feed_database($db_cabeceras, 'tbl_transacciones_cabecera', 'at_unique_id', false);
		} elseif((int)$data["servicio_id"] == 12){ // TORITO
			/*
			servicio_id=12
			canal_venta=33
			producto_id=9
			*/
			$data["fecha"] = $data["fecha_inicio"]; 
			$query = "
				SELECT
					l.id as local_id,
					l.cc_id,
					l.zona_id
				FROM tbl_locales l
				INNER JOIN tbl_local_caja_detalle_tipos dt ON dt.local_id = l.id
				WHERE dt.detalle_tipos_id = 19
			";
			$locales = [];
			$query_result = $mysqli->query($query);
			if($mysqli->error){ echo $mysqli->error; die; }
			while($r = $query_result->fetch_assoc()) $locales[$r["cc_id"]] = $r;
			//print_r($locales);exit();


			$torito_tickets = [];
			$query = "
				SELECT
					tt.id,
					tt.cc_id,
					tt.transactionid ticket_id,
					tt.id_torito_tipo_transaccion cod_tipo,
					tt.amount, 
					tt.`status` 
				FROM
					tbl_torito_transaccion tt 
				WHERE
					tt.date >= '{$data["fecha_inicio"]}' 
					AND tt.date < '{$data["fecha_fin"]}' 
			";
			//echo $query;
			// status=1 : venta
			// status=2 : pago
			// status=3 : recarga
			$query_result = $mysqli->query($query);
			if($mysqli->error){ echo $mysqli->error; die; }
			while($r = $query_result->fetch_assoc()) $torito_tickets[$r["cc_id"]][] = $r;

			//print_r($torito_tickets);exit();

			$en_de_data = get_pagado_de_otra_tienda_torito($data["fecha_inicio"],$data["fecha_fin"]);
			$db_cabeceras = [];
			foreach (array_keys($locales) as $cc_id) {
				// if(!in_array($cc_id, [3002,3313])){ continue; }
				// print_r($cc_id); exit();
				$response_tickets = [
					'count_tickets_apostados' => 0,
					'count_tickets_ganados' => 0,
					'count_tickets_pagados' => 0,
					'pagado_en_otra_tienda' => 0,
					'pagado_de_otra_tienda' => 0,
					'total_apostado' => 0,
					'total_ganado' => 0,
					'total_pagado' => 0,
					'total_anulado' => 0
				];

				$db_cabeceras[$cc_id] = [
					'at_unique_id' => md5($proceso_id.$data["fecha_inicio"]."33".$data["servicio_id"].$cc_id),
					'proceso_unique_id' => md5(date("c").$data["servicio_id"]),
					'fecha_proceso' => $date,
					'fecha' => $data["fecha"],
					'local_id' => $locales[$cc_id]["local_id"],
					'zona_id' => $locales[$cc_id]["zona_id"],
					'servicio_id' => $data["servicio_id"],
					'canal_de_venta_id' => 33,
					'producto_id' => 9,
					'num_registros' => 0,
					'num_tickets' => 0,
					'num_tickets_ganados' => 0,
					'num_tickets_ganados_pagados' => 0,
					'moneda_id' => '1',
					'total_apostado' => 0,
					'cashdesk_apostado' => 0,
					'total_ganado' => 0,
					'cashdesk_ganado' => 0,
					'total_pagado' => 0,
					'cashdesk_pagado' => 0,
					'total_produccion' => 0,
					'cashdesk_produccion' => 0,
					'caja_fisico' => 0,
					'cashdesk_caja_fisico' => 0,
					'total_depositado' => 0,
					'total_anulado_retirado' => 0,
					'total_ingresado' => 0,
					'total_devuelto' => 0,
					'total_depositado_web' => null,
					'total_retirado_web' => null,
					'total_caja_web' => null,
					'porcentaje_cliente' => null,
					'total_cliente' => null,
					'porcentaje_freegames' => null,
					'total_freegames' => null,
					'pagado_en_otra_tienda' => 0,
					'pagado_de_otra_tienda' => 0,
					'total_pagos_fisicos' => 0,
					'retirado_de_otras_tiendas' => null,
					'cashdesk_balance' => null,
					'tipo_de_cambio_id' => null
				];

				/*
				$response_tickets["pagado_de_otra_tienda"]=0;
				$response_tickets["pagado_en_otra_tienda"]=0;
				*/

				if (isset($torito_tickets[$cc_id])) {
					foreach ($torito_tickets[$cc_id] as $torito_ticket) {
						//0_FAILED, 1_COMPLETED
						if ((int)$torito_ticket["status"] === 1) {
							if ((int)$torito_ticket["cod_tipo"] === 1) { //1_venta, 2_pago, 3_recarga
								$response_tickets["total_apostado"] += $torito_ticket["amount"];
								$response_tickets["count_tickets_apostados"]++;
							}
							if ((int)$torito_ticket["cod_tipo"] === 2) { //1_venta, 2_pago, 3_recarga
								$response_tickets["total_ganado"] += $torito_ticket["amount"];
								$response_tickets["count_tickets_ganados"]++;
								$response_tickets["total_pagado"] += $torito_ticket["amount"];
								$response_tickets["count_tickets_pagados"]++;
							}
							if ((int)$torito_ticket["cod_tipo"] === 4) {
								$response_tickets["total_apostado"] += $torito_ticket["amount"];
								$response_tickets["count_tickets_apostados"]++;
							}
							if ((int)$torito_ticket["cod_tipo"] === 5) {
								$response_tickets["total_ganado"] += $torito_ticket["amount"];
								$response_tickets["count_tickets_ganados"]++;
								$response_tickets["total_pagado"] += $torito_ticket["amount"];
								$response_tickets["count_tickets_pagados"]++;
							}
						} else {
							$response_tickets["total_anulado"] += $torito_ticket["amount"];
						}
					}
				}
				/*
				foreach ($bingo_tickets["paid"] as $paid_local_id => $tickets_paid) {
					foreach ($tickets_paid as $k => $bingo_ticket) {
					// print_r($bingo_ticket); exit();
						if($bingo_ticket["sell_local_id"] == $cc_id){
							if($paid_local_id != $cc_id){
								// print_r($tickets_paid);
								$response_tickets["pagado_en_otra_tienda"] += $bingo_ticket["winning"];
							}
						}
					}
				}
				*/
				// print_r($response_tickets["pagado_en_otra_tienda"]);
				// exit();

				//print_r($response_tickets);
				//exit();

				$db_cabeceras[$cc_id] = [
					'at_unique_id' => md5($proceso_id.$data["fecha_inicio"]."33".$data["servicio_id"].$cc_id),
					'proceso_unique_id' => $proceso_id,
					'fecha_proceso' => $date,
					'fecha' => $data["fecha"],
					'local_id' => $locales[$cc_id]["local_id"],
					'zona_id' => $locales[$cc_id]["zona_id"],
					'servicio_id' => $data["servicio_id"],
					'canal_de_venta_id' => 33,
					'producto_id' => 9,
					'num_registros' => $response_tickets["count_tickets_apostados"],
					'num_tickets' => $response_tickets["count_tickets_apostados"],
					'num_tickets_ganados' => $response_tickets["count_tickets_ganados"],
					'num_tickets_ganados_pagados' => $response_tickets["count_tickets_pagados"],
					'moneda_id' => '1',
					'total_apostado' =>  ($response_tickets["total_apostado"]),
					'cashdesk_apostado' => ($response_tickets["total_apostado"]),
					'total_ganado' => 0,//$response_tickets["total_ganado"],//ultimo
					'cashdesk_ganado' => 0,//$response_tickets["total_ganado"],
					'total_pagado' => ($response_tickets["total_pagado"] + $response_tickets["pagado_en_otra_tienda"]),//se repite
					'cashdesk_pagado' => $response_tickets["total_pagado"],
					'total_produccion' => ($response_tickets["total_apostado"] - $response_tickets["total_pagado"] - $response_tickets["pagado_en_otra_tienda"]),
					'cashdesk_produccion' => ($response_tickets["total_apostado"] - $response_tickets["total_pagado"]),
					'caja_fisico' => ($response_tickets["total_apostado"] - $response_tickets["total_pagado"] - $response_tickets["pagado_de_otra_tienda"]),
					'cashdesk_caja_fisico' => ($response_tickets["total_apostado"] - $response_tickets["total_pagado"] - $response_tickets["pagado_de_otra_tienda"]),
					'total_depositado' => 0,//$response_tickets["total_apostado"],
					'total_anulado_retirado' => $response_tickets["total_anulado"],
					'total_ingresado' => 0,//null,
					'total_devuelto' => 0,//null,
					'total_depositado_web' => 0,//null,
					'total_retirado_web' => 0,//null,
					'total_caja_web' => 0,//null,
					'porcentaje_cliente' => 0,//null,
					'total_cliente' => 0,//null,
					'porcentaje_freegames' => 0,//null,
					'total_freegames' => 0,//null,
					'pagado_en_otra_tienda' => (isset($en_de_data["en_otra_tienda"][$cc_id]))?number_format($en_de_data["en_otra_tienda"][$cc_id],2):0,//$response_tickets["pagado_en_otra_tienda"],
					'pagado_de_otra_tienda' => (isset($en_de_data["de_otra_tienda"][$cc_id]))?number_format($en_de_data["de_otra_tienda"][$cc_id],2):0,//$response_tickets["pagado_de_otra_tienda"],
					'total_pagos_fisicos' => ($response_tickets["total_pagado"]),
					'retirado_de_otras_tiendas' => 0,//null,
					'cashdesk_balance' => 0,//null,
					'tipo_de_cambio_id' => 0,//null
				];
			}
			//print_r($db_cabeceras);
			//exit();
			// print_r($db_cabeceras); exit();

			feed_database($db_cabeceras, 'tbl_transacciones_cabecera', 'at_unique_id', false);

		}
		elseif((int)$data["servicio_id"] == 13){ // CARRERA DE CABALLOS
			/*
			servicio_id=13
			canal_venta=34
			producto_id=10
			*/
			$data["fecha"] = $data["fecha_inicio"];
			/*tbl_caja_detalle_tipos 20*/
			$query = "
				SELECT
					l.id as local_id,
					l.cc_id,
					l.zona_id
				FROM tbl_locales l
				INNER JOIN tbl_local_caja_detalle_tipos dt ON dt.local_id = l.id
				WHERE dt.detalle_tipos_id = 20
			";
			$locales = [];
			$query_result = $mysqli->query($query);
			if($mysqli->error){ echo $mysqli->error; die; }
			while($r = $query_result->fetch_assoc()) $locales[$r["cc_id"]] = $r;
			$carrera_de_caballos_tickets = [];
			$query = "
				SELECT
				l.cc_id,
				ticket_id,
				rtas.transaction_type AS cod_tipo,
				rtas.amount,
				rtas.ticket_transaction_status AS status
				FROM   tbl_repositorio_tickets_america_simulcast AS rtas
				INNER JOIN tbl_locales l ON rtas.local_id = l.id
				WHERE
				rtas.creation_date >= '{$data["fecha_inicio"]}'
				AND rtas.creation_date < '{$data["fecha_fin"]}'
				AND rtas.bet_status is not null
				AND rtas.local_id = l.id  AND  ticket_id is not NULL
			";
			// status=1 : venta
			// status=2 : pago
			$query_result = $mysqli->query($query);
			//echo "<pre>";print_r($query);	echo "</pre>";die();

			if($mysqli->error){ echo $mysqli->error; die; }
			while($r = $query_result->fetch_assoc()) $carrera_de_caballos_tickets[$r["cc_id"]][] = $r;
			//echo "<pre>";print_r($carrera_de_caballos_tickets);	echo "</pre>";die();

			$db_cabeceras = [];
			$canal_venta = 34;
			$producto_id = 10;

			foreach (array_keys($locales) as $cc_id) {
				$response_tickets = [
					'count_tickets_apostados' => 0,
					'count_tickets_anulados' => 0,
					'count_tickets_ganados' => 0,
					'count_tickets_pagados' => 0,
					'pagado_en_otra_tienda' => 0,
					'pagado_de_otra_tienda' => 0,
					'total_apostado' => 0,
					'total_ganado' => 0,
					'total_pagado' => 0,
					'total_anulado' => 0
				];

				$db_cabeceras[$cc_id] = [
					'at_unique_id' => md5($proceso_id.$data["fecha_inicio"].$canal_venta.$data["servicio_id"].$cc_id),
					'proceso_unique_id' => md5(date("c").$data["servicio_id"]),
					'fecha_proceso' => $date,
					'fecha' => $data["fecha"],
					'local_id' => $locales[$cc_id]["local_id"],
					'zona_id' => $locales[$cc_id]["zona_id"],
					'servicio_id' => $data["servicio_id"],
					'canal_de_venta_id' => $canal_venta,
					'producto_id' => $producto_id,
					'num_registros' => 0,
					'num_tickets' => 0,
					'num_tickets_ganados' => 0,
					'num_tickets_ganados_pagados' => 0,
					'moneda_id' => '1',
					'total_apostado' => 0,
					'cashdesk_apostado' => 0,
					'total_ganado' => 0,
					'cashdesk_ganado' => 0,
					'total_pagado' => 0,
					'cashdesk_pagado' => 0,
					'total_produccion' => 0,
					'cashdesk_produccion' => 0,
					'caja_fisico' => 0,
					'cashdesk_caja_fisico' => 0,
					'total_depositado' => 0,
					'total_anulado_retirado' => 0,
					'total_ingresado' => 0,
					'total_devuelto' => 0,
					'total_depositado_web' => null,
					'total_retirado_web' => null,
					'total_caja_web' => null,
					'porcentaje_cliente' => null,
					'total_cliente' => null,
					'porcentaje_freegames' => null,
					'total_freegames' => null,
					'pagado_en_otra_tienda' => 0,
					'pagado_de_otra_tienda' => 0,
					'total_pagos_fisicos' => 0,
					'retirado_de_otras_tiendas' => null,
					'cashdesk_balance' => null,
					'tipo_de_cambio_id' => null
				];
				if (isset($carrera_de_caballos_tickets[$cc_id])) {
					foreach ($carrera_de_caballos_tickets[$cc_id] as $carrera_de_caballos_ticket) {
						if ((int)$carrera_de_caballos_ticket["cod_tipo"] === 4) { //4_venta, 5_pago,
							$response_tickets["total_apostado"] += $carrera_de_caballos_ticket["amount"];
							$response_tickets["count_tickets_apostados"]++;
						}
						if ((int)$carrera_de_caballos_ticket["cod_tipo"] === 5) { //4_venta, 5_pago,
							$response_tickets["total_ganado"] += $carrera_de_caballos_ticket["amount"];
							$response_tickets["count_tickets_ganados"]++;
							$response_tickets["total_pagado"] += $carrera_de_caballos_ticket["amount"];
							$response_tickets["count_tickets_pagados"]++;
						}
						if ((int)$carrera_de_caballos_ticket["cod_tipo"] === 10 && (int)$carrera_de_caballos_ticket["status"] === 4) {
							$response_tickets["total_anulado"] += $carrera_de_caballos_ticket["amount"];
							$response_tickets["count_tickets_anulados"]++;
							
						}
					}
				}

				/*%*/ 
				$queryHipica = "
					SELECT 
						cf.id,
						cf.monto,
						c.local_id,
						c.id AS contrato_id ,
						l.nombre,
						l.zona_id,
						l.red_id
					FROM tbl_contrato_formulas cf
					LEFT JOIN tbl_contratos c ON (c.id = cf.contrato_id)
					LEFT JOIN tbl_locales l ON (l.id = c.local_id)
					LEFT JOIN tbl_contrato_productos cp ON (cp.id = cf.producto_id)
					LEFT JOIN tbl_productos p ON (p.id = cp.producto_id)
					WHERE cf.estado = 1 
						AND p.id = 10 
						AND c.local_id = ".$locales[$cc_id]["local_id"]
				;
				$porcentaje_cliente = 0;
				$total_cliente = 0;
				$porcentaje_freegames = 0;
				$total_freegames = 0;
				try {
					$query_result = $mysqli->query($queryHipica);
					while($formulaHipica = $query_result->fetch_assoc()){
						$porcentaje_cliente = $formulaHipica["monto"];	
					}
					if ( $porcentaje_cliente )
					{
						$porcentaje_freegames   = number_format((100 - $porcentaje_cliente ),2);
						//$totalGanado = $response_tickets["total_ganado"];
						$resultado_negocio = ($response_tickets["total_apostado"] - $response_tickets["total_ganado"]);
						$total_cliente = (($resultado_negocio * $porcentaje_cliente ) / 100);
						$total_freegames   = ($resultado_negocio)-$total_cliente;
					}					
				} catch (\Throwable $th) {
				}
				/*%*/
				 
				$response_tickets["total_apostado"] = ($response_tickets["total_apostado"] - $response_tickets["total_anulado"]);

				$db_cabeceras[$cc_id] = [
					'at_unique_id' => md5($proceso_id.$data["fecha_inicio"].$canal_venta.$data["servicio_id"].$cc_id),
					'proceso_unique_id' => $proceso_id, // cambio de md5 a variable -  md5(date("c").$data["servicio_id"]),
					'fecha_proceso' => $date,
					'fecha' => $data["fecha"],
					'local_id' => $locales[$cc_id]["local_id"],
					'zona_id' => $locales[$cc_id]["zona_id"],
					'servicio_id' => $data["servicio_id"],
					'canal_de_venta_id' => $canal_venta,
					'producto_id' => $producto_id,
					'num_registros' => $response_tickets["count_tickets_apostados"]-$response_tickets["count_tickets_anulados"],
					'num_tickets' => $response_tickets["count_tickets_apostados"]-$response_tickets["count_tickets_anulados"],
					'num_tickets_ganados' => $response_tickets["count_tickets_ganados"],
					'num_tickets_ganados_pagados' => $response_tickets["count_tickets_pagados"],
					'moneda_id' => '1',
					'total_apostado' => $response_tickets["total_apostado"],
					'cashdesk_apostado' => ($response_tickets["total_apostado"]),
					'total_ganado' => $response_tickets["total_ganado"],
					'resultado_negocio' => ($response_tickets["total_apostado"] - $response_tickets["total_ganado"]),
					'cashdesk_ganado' => 0,
					'total_pagado' => ($response_tickets["total_pagado"] + $response_tickets["pagado_en_otra_tienda"]),//se repite
					'cashdesk_pagado' => $response_tickets["total_pagado"],
					'total_produccion' => ($response_tickets["total_apostado"] - $response_tickets["total_pagado"] - $response_tickets["pagado_en_otra_tienda"]),
					'cashdesk_produccion' => ($response_tickets["total_apostado"] - $response_tickets["total_pagado"]),
					'caja_fisico' => ($response_tickets["total_apostado"] - $response_tickets["total_pagado"] - $response_tickets["pagado_de_otra_tienda"]),
					'cashdesk_caja_fisico' => ($response_tickets["total_apostado"] - $response_tickets["total_pagado"] - $response_tickets["pagado_de_otra_tienda"]),
					'total_depositado' => 0,
					'total_anulado_retirado' => $response_tickets["total_anulado"],
					'total_ingresado' => 0,
					'total_devuelto' => 0,
					'total_depositado_web' => 0,
					'total_retirado_web' => 0,
					'total_caja_web' => 0,
					'porcentaje_cliente' => $porcentaje_cliente,
					'total_cliente' => $total_cliente,
					'porcentaje_freegames' => $porcentaje_freegames,
					'total_freegames' => $total_freegames,
					'pagado_en_otra_tienda' => 0,
					'pagado_de_otra_tienda' => 0,
					'total_pagos_fisicos' => ($response_tickets["total_pagado"]),
					'retirado_de_otras_tiendas' => 0,
					'cashdesk_balance' => 0,
					'tipo_de_cambio_id' => 0,
				];
			}
			feed_database($db_cabeceras, 'tbl_transacciones_cabecera', 'at_unique_id', false);
		}
		elseif((int)$data["servicio_id"] == 15){//calimaco
			//servicio_id = 15
			//cdv = 37
			//producto_id = 12

			$servicio_id = 15 ;
			$cdv_id = 37;
			$producto_id = 12 ;
			
			$locales = [];
			/*$command_locales = "
				SELECT 
					canal_de_venta_id,
					local_id
				FROM tbl_local_proveedor_id 
				WHERE canal_de_venta_id = 37 
				AND servicio_id = '".$data["servicio_id"]."'
			";*/
			$command_locales = "SELECT l.id
			FROM   tbl_saldo_web_transaccion  AS swt
				   LEFT JOIN tbl_locales l
						ON  l.cc_id = swt.cc_id
			WHERE  swt.created_at >= '" . $data["fecha_inicio"] . "'
				   AND swt.created_at < '" . $data["fecha_fin"] . "' AND swt.status = 1
			GROUP BY
				   l.id";

			$query_locales = $mysqli->query($command_locales);
			while($l_d = $query_locales->fetch_assoc()){
				$locales[] = $l_d;
			}
			$mysqli->next_result();
			ksort($locales);

			$total_saldo_web = [];
			$total_saldo_web_command = "SELECT
											l.id as local_id,
											l.cc_id,
											SUM(swt.monto) AS monto,
											swt.tipo_id,
											IF(swt.tipo_id = 1 ,'Dep','Retiro')
										FROM  tbl_saldo_web_transaccion AS swt
										LEFT JOIN tbl_locales l on l.cc_id = swt.cc_id
										WHERE swt.created_at >= '" . $data["fecha_inicio"] . "'
											AND swt.created_at < '" . $data["fecha_fin"] . "'
											AND swt.status = 1
										GROUP BY l.id , swt.tipo_id";
			$total_saldo_web_query = $mysqli->query($total_saldo_web_command);
			while( $tt = $total_saldo_web_query->fetch_assoc() ){
				$total_saldo_web[$tt["local_id"]]["depositos"] = (isset($total_saldo_web[$tt["local_id"]]["depositos"]) && $total_saldo_web[$tt["local_id"]]["depositos"]!=0)?$total_saldo_web[$tt["local_id"]]["depositos"]:0;
				$total_saldo_web[$tt["local_id"]]["retiros"] = (isset($total_saldo_web[$tt["local_id"]]["retiros"]) && $total_saldo_web[$tt["local_id"]]["retiros"]!=0)?$total_saldo_web[$tt["local_id"]]["retiros"]:0;
				if( $tt["tipo_id"] == 1 )
				{//DEP

					$total_saldo_web[$tt["local_id"]]["depositos"] = $tt["monto"];
				}
				if( $tt["tipo_id"] == 2 )
				{//RET
					$total_saldo_web[$tt["local_id"]]["retiros"] = $tt["monto"];
				}
			}

			if($proceso_id){
				foreach ($locales as $local_id => $l) {
					if(!isset($total_saldo_web[$l['id']]) )
					{
						continue;
					}
					$zona = $mysqli->query("SELECT zona_id, red_id FROM tbl_locales WHERE id = ".$l['id'])->fetch_assoc();
					$cabecera = [];
						$cabecera["at_unique_id"] = md5($proceso_id.$data["fecha_inicio"].$cdv_id.$data["servicio_id"].$l['id']);
						$cabecera["proceso_unique_id"] = $proceso_id;
						$cabecera["fecha"] = $data["fecha_inicio"];
						$cabecera["fecha_proceso"] = date("Y-m-d H:i:s");
						$cabecera["local_id"] = $l['id'];
						if(isset($zona["zona_id"])) $cabecera["zona_id"] = (int)$zona["zona_id"];
						$cabecera["servicio_id"] = $data["servicio_id"];
						$cabecera["canal_de_venta_id"] = $cdv_id;
						$cabecera["producto_id"] = $producto_id;
						$cabecera["moneda_id"] = 1;

						$cabecera["total_depositado_web"] = 0;
						$cabecera["total_retirado_web"] = 0;
						if( array_key_exists($l['id'], $total_saldo_web) ){
							$cabecera["total_depositado_web"] = $total_saldo_web[$l['id']]["depositos"];
							$cabecera["total_retirado_web"] = $total_saldo_web[$l['id']]["retiros"];
						}
						$cabecera["total_caja_web"] = ( $cabecera["total_depositado_web"] - $cabecera["total_retirado_web"] );
						$cabecera["caja_fisico"]    = ( $cabecera["total_depositado_web"] - $cabecera["total_retirado_web"] );

						$cabecera['num_registros']  =  0;
						$cabecera['num_tickets']  =  0;
						$cabecera['num_tickets_ganados']  =  0;
						$cabecera['num_tickets_ganados_pagados']  =  0;
						$cabecera['moneda_id']  =  '1';
						$cabecera['total_apostado']  =  0;
						$cabecera['cashdesk_apostado']  =  0;
						$cabecera['total_ganado']  =  0;
						$cabecera['cashdesk_ganado']  =  0;
						$cabecera['total_pagado']  =  0;
						$cabecera['cashdesk_pagado']  =  0;
						$cabecera['total_produccion']  =  0;
						$cabecera['cashdesk_produccion']  =  0;
						$cabecera['cashdesk_caja_fisico']  =  0;
						$cabecera['total_depositado']  =  0;
						$cabecera['total_anulado_retirado']  =  0;
						$cabecera['total_ingresado']  =  0;
						$cabecera['total_devuelto']  =  0;

						$query_producto_calimaco = "SELECT 
						cf.id,
						cf.monto,
						c.local_id,
						c.id AS contrato_id ,
						l.nombre,
						l.zona_id,
						l.red_id,
						p.id AS producto,
						p.nombre AS pro_nombre
						FROM tbl_contrato_formulas cf
						LEFT JOIN tbl_contratos c ON (c.id = cf.contrato_id)
						LEFT JOIN tbl_locales l ON (l.id = c.local_id)
						LEFT JOIN tbl_contrato_productos cp ON (cp.id = cf.producto_id)
						LEFT JOIN tbl_productos p ON (p.id = cp.producto_id)
						WHERE cf.estado = 1 AND 
						p.id=6 AND
						c.local_id = ".$l['id'];
						$porcentajeMontoFormulaSaldoWebCliente=0;
						$totalSaldoWebCliente=0;
						$porcentajeMontoFormulaSaldoWebFreeGames=0;
						$totalSaldoWebFreegames=0;
						try {
							$query_result_producto_calimaco = $mysqli->query($query_producto_calimaco);
							while ($formulaSaldoWeb=$query_result_producto_calimaco->fetch_assoc()) {
								$porcentajeMontoFormulaSaldoWebCliente=$formulaSaldoWeb["monto"];
							}
							if ($porcentajeMontoFormulaSaldoWebCliente) {
								$porcentajeMontoFormulaSaldoWebFreeGames = number_format((100 - $porcentajeMontoFormulaSaldoWebCliente),2);
								$totalSaldoWebCliente = (($cabecera["total_depositado_web"] * $porcentajeMontoFormulaSaldoWebCliente) / 100);
								$totalSaldoWebFreegames = ($cabecera["total_depositado_web"])-$totalSaldoWebCliente;
							}
						} catch (\Throwable $th) {
							//throw $th;
						}

						//$cabecera['porcentaje_cliente']  =  null;
						$cabecera['porcentaje_cliente'] = $porcentajeMontoFormulaSaldoWebCliente;
						//$cabecera['total_cliente']  =  0;
						$cabecera['total_cliente'] = $totalSaldoWebCliente;
						//$cabecera['porcentaje_freegames']  = null;
						$cabecera['porcentaje_freegames'] = $porcentajeMontoFormulaSaldoWebFreeGames;
						//$cabecera['total_freegames']  =  0;
						$cabecera['total_freegames'] = $totalSaldoWebFreegames;
						$cabecera['pagado_en_otra_tienda']  =  0;
						$cabecera['pagado_de_otra_tienda']  =  0;
						$cabecera['total_pagos_fisicos']  =  0;
						$cabecera['retirado_de_otras_tiendas']  =  0;
						$cabecera['cashdesk_balance']  =  0;
						$cabecera['tipo_de_cambio_id']  =  null;
					$cabeceras[]=$cabecera;
				}
				if(!empty($cabeceras)){
					feed_database($cabeceras, 'tbl_transacciones_cabecera', 'at_unique_id', false);
				}
			}/*fin ifproceso id*/
		}/*fin servicio 15 */
		elseif((int)$data["servicio_id"] == 16){//altenar
			$servicio_id = 16 ;			
			$cdv_id = 41;
			$producto_id = 13 ;
			$data_local_query = " SELECT
				ssql.id local_id,
				ssql.cc_id,
				ssql.zona_id,
				SUM(IF(ct.tipo_id = 4, ct.monto, 0)) AS apuesta_generada,
				SUM(IF(ct.tipo_id = 4, 1, 0)) AS cant_apuesta_generada,
				--
				SUM(IF(ct.tipo_id = 5, ct.monto, 0)) AS apuesta_pagada,
				SUM(IF(ct.tipo_id = 5, 1, 0)) AS cant_apuesta_pagada,
				--
				SUM(IF(ct.tipo_id = 19, ct.monto, 0)) AS apuesta_retornada,
				SUM(IF(ct.tipo_id = 19, 1, 0)) AS cant_apuesta_retornada,
				--
				SUM(IF(ct.tipo_id = 34, ct.monto, 0)) AS apuesta_cancelada,
				SUM(IF(ct.tipo_id = 34, 1, 0)) AS cant_apuesta_cancelada
			FROM
				tbl_televentas_clientes_transaccion ct
			JOIN tbl_televentas_clientes c ON
				c.id = ct.cliente_id
			LEFT JOIN tbl_caja sqc ON
				sqc.id = ct.turno_id
			LEFT JOIN tbl_local_cajas sqlc ON
				sqlc.id = sqc.local_caja_id
			LEFT JOIN tbl_locales ssql ON
				ssql.id = sqlc.local_id
			WHERE
				c.tipo_doc = 1
				AND LENGTH(c.num_doc) = 4
				AND ct.tipo_id IN (4,5, 19, 34) -- 4=Apuesta Generada 5=apuesta pagada 19=Apuesta Retornada  34= Apuesta Cancelada
				AND ct.estado = 1
				AND sqc.fecha_operacion >= '" . $data["fecha_inicio"] . "'
				AND sqc.fecha_operacion < '" . $data["fecha_fin"] . "'
			GROUP by local_id ,zona_id,cc_id;";
			$data_local_query_result = $mysqli->query($data_local_query);
			$data_local =[];
			while( $tt = $data_local_query_result->fetch_assoc() ){
				$data_local[] = $tt;
			}

			
			$porcentaje_cliente = 0;
			$total_cliente = 0;
			$porcentaje_freegames = 0;
			$total_freegames = 0;
		
		
			//$data_local = json_decode(get_data_test_aleternar(),true); 

			if($proceso_id){
				foreach ($data_local as $reg) {		
				$cantidad_retornada_cancelada =$reg["cant_apuesta_cancelada"]+$reg["cant_apuesta_retornada"];
				$total_retornada_cancelada    =$reg["apuesta_cancelada"]+$reg["apuesta_retornada"];
				$db_cabeceras[$reg["local_id"]] = [
				'at_unique_id' => md5($proceso_id.$data["fecha_inicio"].$cdv_id.$servicio_id.$reg["cc_id"]),
				'proceso_unique_id' => $proceso_id, // cambio de md5 a variable -  md5(date("c").$data["servicio_id"]),
				'fecha_proceso' => $date,
				'fecha' => $data["fecha_inicio"],
				'local_id' => $reg["local_id"],
				'zona_id' => $reg["zona_id"],
				'servicio_id' => $data["servicio_id"],
				'canal_de_venta_id' => $cdv_id,
				'producto_id' => $producto_id,
				'num_registros' => $reg["cant_apuesta_generada"]-$cantidad_retornada_cancelada,
				'num_tickets' => $reg["cant_apuesta_generada"]-$cantidad_retornada_cancelada,
				'num_tickets_ganados' => $reg["cant_apuesta_pagada"],
				'num_tickets_ganados_pagados' => $reg["cant_apuesta_pagada"],
				'moneda_id' => '1',
				'total_apostado' => $reg["apuesta_generada"],
				'cashdesk_apostado' => ($reg["apuesta_generada"]),
				'total_ganado' => $reg["apuesta_pagada"]+ $reg["apuesta_retornada"],
				'resultado_negocio' => ($reg["apuesta_generada"] - $reg["apuesta_pagada"]-$total_retornada_cancelada),
				'cashdesk_ganado' => 0,
				'total_pagado' => ($reg["apuesta_pagada"] + $reg["apuesta_retornada"]),//se repite
				'cashdesk_pagado' => $reg["apuesta_pagada"] + $reg["apuesta_retornada"],
				'total_produccion' => ($reg["apuesta_generada"] - $reg["apuesta_pagada"]-$total_retornada_cancelada),
				'cashdesk_produccion' => ($reg["apuesta_generada"] - $reg["apuesta_pagada"]-$total_retornada_cancelada),
				'caja_fisico' => ($reg["apuesta_generada"] - $reg["apuesta_pagada"] -$total_retornada_cancelada),
				'cashdesk_caja_fisico' => ($reg["apuesta_generada"] - $reg["apuesta_pagada"]-$total_retornada_cancelada),
				'total_depositado' => 0,
				'total_anulado_retirado' => $reg["apuesta_cancelada"],
				'total_ingresado' => 0,
				'total_devuelto' =>$reg["apuesta_retornada"],
				'total_depositado_web' => 0,
				'total_retirado_web' => 0,
				'total_caja_web' => 0,
				'porcentaje_cliente' => $porcentaje_cliente,
				'total_cliente' => $total_cliente,
				'porcentaje_freegames' => $porcentaje_freegames,
				'total_freegames' => $total_freegames,
				'pagado_en_otra_tienda' => 0,
				'pagado_de_otra_tienda' => 0,
				'total_pagos_fisicos' => ($reg["apuesta_pagada"]+ $reg["apuesta_retornada"]),
				'retirado_de_otras_tiendas' => 0,
				'cashdesk_balance' => 0,
				'tipo_de_cambio_id' => 0,
			];
			}
			}
			if(!empty($db_cabeceras)){
				feed_database($db_cabeceras, 'tbl_transacciones_cabecera', 'at_unique_id', false);
			}
			/*fin ifproceso id*/
		}/*fin servicio 16 */
		elseif((int)$data["servicio_id"] == 17){//KURAX MVR Y SBC
			$locales = [];
			$command_locales = "
				SELECT 
					canal_de_venta_id,
					local_id
				FROM tbl_local_proveedor_id 
				WHERE servicio_id = '".$data["servicio_id"]."'
			";
			$query_locales = $mysqli->query($command_locales);
			while($l_d=$query_locales->fetch_assoc()){
				if(array_key_exists($l_d["local_id"], $locales)){
					if(!in_array($l_d["canal_de_venta_id"], $locales[$l_d["local_id"]])){
						$locales[$l_d["local_id"]][]=$l_d["canal_de_venta_id"];
					}
				}else{
					$locales[$l_d["local_id"]][]=$l_d["canal_de_venta_id"];
				}
			}
			$mysqli->next_result();
			ksort($locales);

			$call_param = "'".$data["fecha_inicio"]."','".$data["fecha_fin"]."','".$data["servicio_id"]."'";

			$total_ktc=[];
			$total_ktc_command = "CALL get_total_kx_transacciones_consolidado(".$call_param.")";
			$total_ktc_query = $mysqli->query($total_ktc_command);
			while($tk=$total_ktc_query->fetch_assoc()){
				$total_ktc[$tk["local_id"]][$tk["canal_de_venta_id"]]=$tk;
			}
			$mysqli->next_result();

			$total_depositado = [];
			$get_depositado_command = "CALL get_depositado_kurax(".$call_param.")";
			$get_depositado_query = $mysqli->query($get_depositado_command);
			while ($gdq = $get_depositado_query->fetch_assoc()) {
				$total_depositado[$gdq["local_id"]] = $gdq;
			}
			$mysqli->next_result();

			$local_formulas = [];
			$get_local_formulas_command = "CALL get_locales_formulas()";
			$get_local_formulas_query = $mysqli->query($get_local_formulas_command);
			while($lf=$get_local_formulas_query->fetch_assoc()){
				if($lf["tipo"]=="normal"){
					$local_formulas[$lf["local_id"]][$lf["canal_de_venta_id"]]=$lf;
				}elseif($lf["tipo"]=="quiebre"){
					$local_formulas[$lf["local_id"]][$lf["canal_de_venta_id"]][]=$lf;
				}elseif($lf["tipo"]=="modular"){
					$local_formulas[$lf["local_id"]][$lf["canal_de_venta_id"]][]=$lf;
				}
			}
			$mysqli->next_result();

			$apostado_odds = [];
			$get_get_apostado_odds_command = "CALL get_apostado_odds(".$call_param.")";
			$get_get_apostado_odds_query = $mysqli->query($get_get_apostado_odds_command);
			while($ao=$get_get_apostado_odds_query->fetch_assoc()){
				$apostado_odds[$ao["local_id"]][$ao["canal_de_venta_id"]][]=$ao;
			}
			$mysqli->next_result();

			if ($proceso_id) {
				foreach ($locales as $local_id => $local_csdv) {
					foreach ($local_csdv as $key => $cdv_id) {
						$zona = $mysqli->query("SELECT zona_id FROM tbl_locales WHERE id = ".$local_id)->fetch_assoc();
						$cabecera = [];
						$cabecera["at_unique_id"]=md5($proceso_id.$data["fecha_inicio"].$cdv_id.$data["servicio_id"].$local_id);
						$cabecera["proceso_unique_id"]=$proceso_id;
						$cabecera["fecha"]=$data["fecha_inicio"];
						$cabecera["fecha_proceso"]=date("Y-m-d H:i:s");
						$cabecera["local_id"]=$local_id;
						if(isset($zona["zona_id"])) $cabecera["zona_id"]=(int)$zona["zona_id"];
						$cabecera["servicio_id"]=$data["servicio_id"];
						$cabecera["canal_de_venta_id"]=$cdv_id;
						$cabecera["producto_id"] = ((int)$cabecera["canal_de_venta_id"] == '42') ? 15:16;

						$cabecera["num_tickets"]=0;
						$cabecera["num_tickets_ganados"]=0;
						$cabecera["num_tickets_ganados_pagados"]=0;
						$cabecera["num_registros"]=0;
						$cabecera["moneda_id"]=1;
						$cabecera["total_apostado"]=0;
						$cabecera["cashdesk_apostado"]=0;
						$cabecera["total_ganado"]=0;
						$cabecera["pagado_en_otra_tienda"]=0;
						$cabecera["pagado_de_otra_tienda"]=0;
						$cabecera["total_pagado"]=0;
						$cabecera["cashdesk_pagado"]=0;
						$cabecera["total_depositado"]=0;
						$cabecera["total_anulado_retirado"]=0;
						$cabecera["total_depositado_web"]=0;
						$cabecera["total_retirado_web"]=0;
						$cabecera["total_caja_web"]=0;
						$cabecera["total_pagos_fisicos"]=0;
						$cabecera["caja_fisico"]=0;
						$cabecera["total_produccion"]=0;
						$cabecera["cashdesk_produccion"]=0;
						$cabecera["cashdesk_caja_fisico"]=0;
						$cabecera["resultado_negocio"]=0;
						$cabecera["cashdesk_balance"]=0;
						$cabecera["propio_pagado_en_su_tienda"]=0;

						$total_retiro_terminal = 0;
						if ($cdv_id == 43) {
							if (array_key_exists($local_id, $total_depositado)) {
								$cabecera["total_depositado"]=$total_depositado[$local_id]['total_depositado'];
								$total_retiro_terminal = $total_depositado[$local_id]['caja_retiro_terminal'];
							}
						}

						if(array_key_exists($local_id, $total_ktc)){
							if(array_key_exists($cdv_id, $total_ktc[$local_id])){
								$cabecera["num_tickets"]=$total_ktc[$local_id][$cdv_id]["num_tickets"];
								$cabecera["num_tickets_ganados"]=$total_ktc[$local_id][$cdv_id]["num_tickets_ganados"];
								$cabecera["num_tickets_ganados_pagados"]=$total_ktc[$local_id][$cdv_id]["num_tickets_ganados_pagados"];
								$cabecera["num_registros"]=$cabecera["num_tickets"];
								$cabecera["total_apostado"]=$total_ktc[$local_id][$cdv_id]["total_apostado"] + $total_ktc[$local_id][$cdv_id]["pagos_manuales_apostado"];
								$cabecera["total_ganado"]=$total_ktc[$local_id][$cdv_id]["total_ganado"];
								$cabecera["pagado_en_otra_tienda"]=$total_ktc[$local_id][$cdv_id]["pagado_en_otra_tienda"];
								$cabecera["pagado_de_otra_tienda"]=$total_ktc[$local_id][$cdv_id]["pagado_de_otra_tienda"];
								$cabecera["resultado_negocio"]=($total_ktc[$local_id][$cdv_id]["total_apostado"]-$cabecera["total_ganado"]);
								$cabecera["propio_pagado_en_su_tienda"] = $total_ktc[$local_id][$cdv_id]["propio_pagado_en_su_tienda"] + $total_ktc[$local_id][$cdv_id]["pagos_manuales_pagado"];
								$cabecera["total_pagos_fisicos"] = $cabecera["propio_pagado_en_su_tienda"] + $cabecera["pagado_de_otra_tienda"];
								$cabecera["total_pagado"]=$cabecera["pagado_en_otra_tienda"] + $cabecera["propio_pagado_en_su_tienda"];
								$cabecera["cashdesk_pagado"]=$total_ktc[$local_id][$cdv_id]["total_pagado"];
								$cabecera["cashdesk_apostado"]=$total_ktc[$local_id][$cdv_id]["total_apostado"];
								$cabecera["cashdesk_ganado"]=$total_ktc[$local_id][$cdv_id]["total_ganado"];
								$cabecera["cashdesk_produccion"]=$cabecera["cashdesk_apostado"]-$cabecera["cashdesk_pagado"];
								if ($cdv_id == 42) {
									// $cabecera["total_pagado"] = $cabecera["total_pagado"] - $cabecera["pagado_de_otra_tienda"];
									$cabecera["total_produccion"]=($cabecera["total_apostado"]-$cabecera["total_pagado"]);
									// $cabecera["total_pagos_fisicos"] = $cabecera["total_pagado"] - $cabecera["pagado_en_otra_tienda"];
									// + $cabecera["pagado_de_otra_tienda"] se está quitando
									$cabecera["caja_fisico"]=($cabecera["cashdesk_apostado"]-$cabecera["cashdesk_pagado"]+$cabecera["total_caja_web"] - $total_ktc[$local_id][$cdv_id]["pagos_manuales_pagado"] + $total_ktc[$local_id][$cdv_id]["pagos_manuales_apostado"]);
									// A caja fisico se le ha quitado la suma de pagado EN otra tienda y la resta del $cabecera["pagado_de_otra_tienda"]
								} elseif ($cdv_id == 43) {
									$cabecera["total_anulado_retirado"]=$total_retiro_terminal - $total_ktc[$local_id][$cdv_id]["pagado_de_otra_tienda"];
									$cabecera["total_produccion"]=($cabecera["total_depositado"]-$cabecera["total_anulado_retirado"]-$cabecera["total_pagado"]);
									// $cabecera["total_pagos_fisicos"] = $cabecera["total_pagado"] - $cabecera["pagado_en_otra_tienda"];
									$cabecera["caja_fisico"] = ($cabecera["total_depositado"] - $cabecera["total_anulado_retirado"]);
									//- $cabecera["total_pagado"] + $cabecera["pagado_en_otra_tienda"] - $cabecera["pagado_de_otra_tienda"]);
									// A caja fisico se le ha quitado la suma de pagado EN otra tienda y la resta del  $cabecera["pagado_en_otra_tienda"] otra tienda; también se omite total pagado, ya que este valor es sólo referencial (o sea , que se obtuvo un ticket de un terminal y se volvió a jugar en el mismo terminal)
								}
							} else {
								if ($cdv_id == 43) {
									if ($cabecera["total_depositado"] !== 0) {
										$cabecera["total_anulado_retirado"]=$total_retiro_terminal - $cabecera["pagado_de_otra_tienda"];
										$cabecera["total_produccion"]=($cabecera["total_depositado"]-$cabecera["total_anulado_retirado"]-$cabecera["total_pagado"]);
										$cabecera["caja_fisico"] = ($cabecera["total_depositado"] - $cabecera["total_anulado_retirado"] - $cabecera["total_pagado"] + $cabecera["pagado_en_otra_tienda"] - $cabecera["pagado_de_otra_tienda"]);
									}
								}
							}
						}

						$cabecera["porcentaje_cliente"]=0;
						$cabecera["total_cliente"]=0;
						$cabecera["porcentaje_freegames"]=0;
						$cabecera["total_freegames"]=0;

						if (array_key_exists($local_id, $local_formulas)) {
							if ($cdv_id == 42) {
								if (array_key_exists(42, $local_formulas[$local_id])) {
									$formula = $local_formulas[$local_id][42];
									if (array_key_exists(0, $formula)) {
										if ($formula[0]["formula_id"]==17) {
											$f = get_condicionales($formula[0]);
											if($formula[0]["tipo_contrato_id"]==4){ // Participacion FG
												$con = reset($f);
												$return["con_".$cdv_id]=$con;
												if($con["donde"]=="apostado"){
													$cabecera["total_freegames"] = ($cabecera["total_apostado"] * $con["valor"] / 100);
													$cabecera["porcentaje_freegames"] = $con["valor"];
												}
												$cabecera["total_cliente"] = ( $cabecera["total_produccion"] - $cabecera["total_freegames"]);
											}else{
												$tickets = get_tickets($local_id,$data);
												foreach ($tickets as $key => $t) {
													$t["comision"] = get_comision($f,$t);
													$cabecera["total_cliente"]+=$t["comision"];
												}
												$cabecera["total_freegames"] = ( $cabecera["caja_fisico"] - $cabecera["total_cliente"] );
											}
										} else {
											if ($formula[0]["fuente"]=="odds" || $formula[0]["columna"]=="apostado") {
												// $apostado_odds[$ao["local_id"]][$ao["canal_de_venta_id"]]=$ao;
												$quiebres = [];
												$grupos = [];
												$grupos_sumas = [];
												$grupos_calculados = [];
												$quiebres_to_db = [];
												foreach ($formula as $f_key => $f_val) {
													$next_key = $f_key+1;
													$new_quiebre = [];
													$new_quiebre["fuente"]=$formula[$f_key]["fuente"];
													$new_quiebre["columna"]=$formula[$f_key]["columna"];
													$new_quiebre["monto_cliente"]=$formula[$f_key]["monto_cliente"];
													$new_quiebre["monto_freegames"]=number_format((100 - $new_quiebre["monto_cliente"]),2);
													$new_quiebre["desde"]=$formula[$f_key]["desde"];
													if(array_key_exists($next_key, $formula)){
														$new_quiebre["hasta"]=$formula[$next_key]["desde"];
													}else{
														$new_quiebre["hasta"]=999999;
													}
													$quiebres[]=$new_quiebre;

													$quiebre_to_db = [];
													$quiebre_to_db["at_unique_id"]=md5($proceso_id.date("c").$local_id.$formula[$f_key]["desde"].$formula[$f_key]["hasta"].$new_quiebre["monto_cliente"].$new_quiebre["monto_freegames"]);
													$quiebre_to_db["proceso_unique_id"]=$proceso_id;
													$quiebre_to_db["local_id"]=$local_id;
													$quiebre_to_db["servicio_id"]=$data["servicio_id"];
													$quiebre_to_db["canal_de_venta_id"]=$cdv_id;
													$quiebre_to_db["fuente"]=$new_quiebre["fuente"];
													$quiebre_to_db["columna"]=$new_quiebre["columna"];
													$quiebre_to_db["desde"]=$formula[$f_key]["desde"];
													$quiebre_to_db["hasta"]=$formula[$f_key]["hasta"];
													$quiebre_to_db["porcentaje_cliente"]=$new_quiebre["monto_cliente"];
													$quiebre_to_db["porcentaje_freegames"]=$new_quiebre["monto_freegames"];

													$quiebre_to_db["contrato_id"]=$formula[$f_key]["contrato_id"];
													$quiebre_to_db["formula_id"]=$formula[$f_key]["formula_id"];
													$quiebre_to_db["tipo_contrato_id"]=$formula[$f_key]["tipo_contrato_id"];

													$quiebres_to_db[$new_quiebre["monto_cliente"]]=$quiebre_to_db;
												}
												// echo "formula";
												// print_r($formula);
												foreach ($quiebres as $q_key => $q_v) {
													if(array_key_exists($local_id, $apostado_odds)){
														if(array_key_exists($cdv_id, $apostado_odds[$local_id])){
															$tickets = $apostado_odds[$local_id][$cdv_id];
															// print_r($tickets);
															// exit();
															foreach ($tickets as $t_key => $t_val) {
																if($t_val["odds"] >= $q_v["desde"] && $t_val["odds"] < $q_v["hasta"]){
																	$grupos[$q_v["monto_cliente"]][]=$t_val;
																}
															}
														}
													}
												}
												// echo "quiebres";
												// print_r($quiebres);
												// exit();
												foreach ($grupos as $g_key => $g_val) {
													foreach ($g_val as $gt_key => $gt_val) {
														if(array_key_exists($g_key, $grupos_sumas)){
															$grupos_sumas[$g_key]+=$gt_val["apostado"];
														}else{
															$grupos_sumas[$g_key]=$gt_val["apostado"];
														}												
													}
												}
												// echo "grupos_sumas";
												// print_r($grupos_sumas);
												// exit();
												$prev_total_cliente = 0;
												foreach ($grupos_sumas as $gs_key => $gs_val) {
													$res = ($gs_val * $gs_key) / 100;
													$grupos_calculados[$gs_key]=$res;
													$prev_total_cliente+=$res;
													$quiebres_to_db[$gs_key]["total_cliente"]=$res;
													$quiebres_to_db[$gs_key]["total_freegames"]=($gs_val - $res);
													$quiebres_to_db[$gs_key]["columna_valor"]=$gs_val;
												}
												$cabecera["total_cliente"] = $prev_total_cliente;
												$cabecera["total_freegames"] = ($cabecera["caja_fisico"] - $prev_total_cliente);
												// echo "grupos_calculados";
												// print_r($grupos_calculados);

												// echo "quiebres_to_db";
												// print_r($quiebres_to_db);
												$mysqli->query("START TRANSACTION");
													foreach ($quiebres_to_db as $qtd_key => $qtd_val) {
														quiebres_insert(data_to_db($qtd_val));
													}
												$mysqli->query("COMMIT");
											} else {
												foreach ($formula as $f_key => $f_val) {
													$total_produccion = $cabecera["total_produccion"];
													$desde = $f_val["desde"];
													$hasta = $f_val["hasta"];
													$fuente = $f_val["fuente"];
													$columna = $f_val["columna"];
													$contrato_id = $f_val["contrato_id"];
													$formula_id = $f_val["formula_id"];
													$tipo_contrato_id = $f_val["tipo_contrato_id"];
													$f_val["monto_freegames"]=number_format((100 - $f_val["monto_cliente"]),2);
													if($hasta<=0){
														$hasta = 999999;
													}
													if($total_produccion >= $desde && $total_produccion < $hasta){
														$cabecera["porcentaje_cliente"]=$f_val["monto_cliente"];
													}
												}
												$cabecera["total_cliente"]=(($cabecera["total_produccion"] * $cabecera["porcentaje_cliente"]) / 100);
												$cabecera["porcentaje_freegames"] = (100 - $cabecera["porcentaje_cliente"]);
												$cabecera["total_freegames"] = ($cabecera["total_produccion"] - $cabecera["total_cliente"]);

												$quiebre_to_db = [];
												$quiebre_to_db["at_unique_id"]=md5($proceso_id.date("c").$local_id.$desde.$hasta.$cabecera["porcentaje_cliente"].$cabecera["porcentaje_freegames"]);
												$quiebre_to_db["proceso_unique_id"]=$proceso_id;
												$quiebre_to_db["local_id"]=$local_id;
												$quiebre_to_db["servicio_id"]=$data["servicio_id"];
												$quiebre_to_db["canal_de_venta_id"]=$cdv_id;
												$quiebre_to_db["fuente"]=$fuente;
												$quiebre_to_db["columna"]=$columna;
												$quiebre_to_db["desde"]=$desde;
												$quiebre_to_db["hasta"]=$hasta;
												$quiebre_to_db["porcentaje_cliente"]=$cabecera["porcentaje_cliente"];
												$quiebre_to_db["porcentaje_freegames"]=$cabecera["porcentaje_freegames"];

												$quiebre_to_db["contrato_id"]=$contrato_id;
												$quiebre_to_db["formula_id"]=$formula_id;
												$quiebre_to_db["tipo_contrato_id"]=$tipo_contrato_id;

												$quiebre_to_db["total_cliente"]=$cabecera["total_cliente"];
												$quiebre_to_db["total_freegames"]=$cabecera["total_freegames"];
												$quiebre_to_db["columna_valor"]=$cabecera["total_produccion"];
												quiebres_insert(data_to_db($quiebre_to_db));
											}
										}
									} else {
										// if($formula["formula_id"]==17){
										// }else{											
										if($formula["tipo"]=="normal"){
											if($formula["columna"]=="resultado"){
												if($formula["operador_id"]==1){
													$cabecera["porcentaje_cliente"]=$formula["monto_cliente"];
													$cabecera["total_cliente"]= (($cabecera["total_produccion"] * $cabecera["porcentaje_cliente"]) / 100);
													$cabecera["porcentaje_freegames"] = (100 - $cabecera["porcentaje_cliente"]);
													$cabecera["total_freegames"] = ($cabecera["total_produccion"] - $cabecera["total_cliente"]);


													$quiebre_to_db = [];
													$quiebre_to_db["at_unique_id"]=md5($proceso_id.date("c").$local_id.$formula["desde"].$formula["hasta"].$cabecera["porcentaje_cliente"].$cabecera["porcentaje_freegames"]);
													$quiebre_to_db["proceso_unique_id"]=$proceso_id;
													$quiebre_to_db["local_id"]=$local_id;
													$quiebre_to_db["servicio_id"]=$data["servicio_id"];
													$quiebre_to_db["canal_de_venta_id"]=$cdv_id;
													$quiebre_to_db["fuente"]=$formula["fuente"];
													$quiebre_to_db["columna"]=$formula["columna"];
													$quiebre_to_db["desde"]=$formula["desde"];
													$quiebre_to_db["hasta"]=$formula["hasta"];
													$quiebre_to_db["porcentaje_cliente"]=$cabecera["porcentaje_cliente"];
													$quiebre_to_db["porcentaje_freegames"]=$cabecera["porcentaje_freegames"];

													$quiebre_to_db["contrato_id"]=$formula["contrato_id"];
													$quiebre_to_db["formula_id"]=$formula["formula_id"];
													$quiebre_to_db["tipo_contrato_id"]=$formula["tipo_contrato_id"];

													$quiebre_to_db["total_cliente"]=$cabecera["total_cliente"];
													$quiebre_to_db["total_freegames"]=$cabecera["total_freegames"];
													$quiebre_to_db["columna_valor"]=$cabecera["total_produccion"];
													quiebres_insert(data_to_db($quiebre_to_db));
												}
											}elseif($formula["columna"]=="apostado"){
												if($formula["operador_id"]==1){
													$cabecera["porcentaje_cliente"]=$formula["monto_cliente"];
													$cabecera["total_cliente"]= (($cabecera["total_apostado"] * $cabecera["porcentaje_cliente"]) / 100);
													$cabecera["porcentaje_freegames"] = (100 - $cabecera["porcentaje_cliente"]);
													$cabecera["total_freegames"] = ($cabecera["caja_fisico"] - $cabecera["total_cliente"]);

													$quiebre_to_db = [];
													$quiebre_to_db["at_unique_id"]=md5($proceso_id.date("c").$local_id.$formula["desde"].$formula["hasta"].$cabecera["porcentaje_cliente"].$cabecera["porcentaje_freegames"]);
													$quiebre_to_db["proceso_unique_id"]=$proceso_id;
													$quiebre_to_db["local_id"]=$local_id;
													$quiebre_to_db["servicio_id"]=$data["servicio_id"];
													$quiebre_to_db["canal_de_venta_id"]=$cdv_id;
													$quiebre_to_db["fuente"]=$formula["fuente"];
													$quiebre_to_db["columna"]=$formula["columna"];
													$quiebre_to_db["desde"]=$formula["desde"];
													$quiebre_to_db["hasta"]=$formula["hasta"];
													$quiebre_to_db["porcentaje_cliente"]=$cabecera["porcentaje_cliente"];
													$quiebre_to_db["porcentaje_freegames"]=$cabecera["porcentaje_freegames"];

													$quiebre_to_db["contrato_id"]=$formula["contrato_id"];
													$quiebre_to_db["formula_id"]=$formula["formula_id"];
													$quiebre_to_db["tipo_contrato_id"]=$formula["tipo_contrato_id"];

													$quiebre_to_db["total_cliente"]=$cabecera["total_cliente"];
													$quiebre_to_db["total_freegames"]=$cabecera["total_freegames"];
													$quiebre_to_db["columna_valor"]=$cabecera["total_apostado"];
													quiebres_insert(data_to_db($quiebre_to_db));
												}
											}elseif($formula["columna"]=="nuevo_resultado"){
												if($formula["operador_id"]==1){
													$cabecera["porcentaje_cliente"]=$formula["monto_cliente"];
													$cabecera["total_cliente"]= (($cabecera["resultado_negocio"] * $cabecera["porcentaje_cliente"]) / 100);
													$cabecera["porcentaje_freegames"] = (100 - $cabecera["porcentaje_cliente"]);
													$cabecera["total_freegames"] = (($cabecera["resultado_negocio"]) - $cabecera["total_cliente"]);

													$quiebre_to_db = [];
													$quiebre_to_db["at_unique_id"]=md5($proceso_id.date("c").$local_id.$formula["desde"].$formula["hasta"].$cabecera["porcentaje_cliente"].$cabecera["porcentaje_freegames"]);
													$quiebre_to_db["proceso_unique_id"]=$proceso_id;
													$quiebre_to_db["local_id"]=$local_id;
													$quiebre_to_db["servicio_id"]=$data["servicio_id"];
													$quiebre_to_db["canal_de_venta_id"]=$cdv_id;
													$quiebre_to_db["fuente"]=$formula["fuente"];
													$quiebre_to_db["columna"]=$formula["columna"];
													$quiebre_to_db["desde"]=$formula["desde"];
													$quiebre_to_db["hasta"]=$formula["hasta"];
													$quiebre_to_db["porcentaje_cliente"]=$cabecera["porcentaje_cliente"];
													$quiebre_to_db["porcentaje_freegames"]=$cabecera["porcentaje_freegames"];

													$quiebre_to_db["contrato_id"]=$formula["contrato_id"];
													$quiebre_to_db["formula_id"]=$formula["formula_id"];
													$quiebre_to_db["tipo_contrato_id"]=$formula["tipo_contrato_id"];

													$quiebre_to_db["total_cliente"]=$cabecera["total_cliente"];
													$quiebre_to_db["total_freegames"]=$cabecera["total_freegames"];
													$quiebre_to_db["columna_valor"]=$cabecera["total_apostado"];
													quiebres_insert(data_to_db($quiebre_to_db));
												}
											}
										}
										// }
									}
								}
							}
							if ($cdv_id == 43) {
								if(array_key_exists(43, $local_formulas[$local_id])){
									$formula = $local_formulas[$local_id][43];
									if(array_key_exists(0, $formula)){
										if($formula[0]["formula_id"]==17){
											$f = get_condicionales($formula[0]);
											if($formula[0]["tipo_contrato_id"]==4){ // Participacion FG
												$con = reset($f);
												$return["con_".$cdv_id]=$con;
												if($con["donde"]=="apostado"){
													$cabecera["total_freegames"] = ($cabecera["total_apostado"] * $con["valor"] / 100);
													$cabecera["porcentaje_freegames"] = $con["valor"];
													// $cabecera["porcentaje_cliente"] = (100 - $cabecera["porcentaje_freegames"]);
												}
												$cabecera["total_cliente"] = ( $cabecera["total_produccion"] - $cabecera["total_freegames"] );
											}
										}
									}else{
										if($formula["tipo"]=="normal"){
											if($formula["columna"]=="resultado"){
												if($formula["operador_id"]==1){
													$cabecera["porcentaje_cliente"]=$formula["monto_cliente"];
													$cabecera["total_cliente"]= (($cabecera["total_produccion"] * $cabecera["porcentaje_cliente"]) / 100);
													$cabecera["porcentaje_freegames"]=(100 - $cabecera["porcentaje_cliente"]);
													$cabecera["total_freegames"]=($cabecera["total_produccion"] - $cabecera["total_cliente"]);


													$quiebre_to_db = [];
													$quiebre_to_db["at_unique_id"]=md5($proceso_id.date("c").$local_id.$formula["desde"].$formula["hasta"].$cabecera["porcentaje_cliente"].$cabecera["porcentaje_freegames"]);
													$quiebre_to_db["proceso_unique_id"]=$proceso_id;
													$quiebre_to_db["local_id"]=$local_id;
													$quiebre_to_db["servicio_id"]=$data["servicio_id"];
													$quiebre_to_db["canal_de_venta_id"]=$cdv_id;
													$quiebre_to_db["fuente"]=$formula["fuente"];
													$quiebre_to_db["columna"]=$formula["columna"];
													$quiebre_to_db["desde"]=$formula["desde"];
													$quiebre_to_db["hasta"]=$formula["hasta"];
													$quiebre_to_db["porcentaje_cliente"]=$cabecera["porcentaje_cliente"];
													$quiebre_to_db["porcentaje_freegames"]=$cabecera["porcentaje_freegames"];

													$quiebre_to_db["contrato_id"]=$formula["contrato_id"];
													$quiebre_to_db["formula_id"]=$formula["formula_id"];
													$quiebre_to_db["tipo_contrato_id"]=$formula["tipo_contrato_id"];

													$quiebre_to_db["total_cliente"]=$cabecera["total_cliente"];
													$quiebre_to_db["total_freegames"]=$cabecera["total_freegames"];
													$quiebre_to_db["columna_valor"]=$cabecera["total_produccion"];
													quiebres_insert(data_to_db($quiebre_to_db));
												}
											}elseif($formula["columna"]=="nuevo_resultado"){
												if($formula["operador_id"]==1){
													$cabecera["porcentaje_cliente"]=$formula["monto_cliente"];
													$cabecera["total_cliente"]= (($cabecera["resultado_negocio"] * $cabecera["porcentaje_cliente"]) / 100);
													$cabecera["porcentaje_freegames"] = (100 - $cabecera["porcentaje_cliente"]);
													$cabecera["total_freegames"] = (($cabecera["resultado_negocio"]) - $cabecera["total_cliente"]);

													$quiebre_to_db = [];
													$quiebre_to_db["at_unique_id"]=md5($proceso_id.date("c").$local_id.$formula["desde"].$formula["hasta"].$cabecera["porcentaje_cliente"].$cabecera["porcentaje_freegames"]);
													$quiebre_to_db["proceso_unique_id"]=$proceso_id;
													$quiebre_to_db["local_id"]=$local_id;
													$quiebre_to_db["servicio_id"]=$data["servicio_id"];
													$quiebre_to_db["canal_de_venta_id"]=$cdv_id;
													$quiebre_to_db["fuente"]=$formula["fuente"];
													$quiebre_to_db["columna"]=$formula["columna"];
													$quiebre_to_db["desde"]=$formula["desde"];
													$quiebre_to_db["hasta"]=$formula["hasta"];
													$quiebre_to_db["porcentaje_cliente"]=$cabecera["porcentaje_cliente"];
													$quiebre_to_db["porcentaje_freegames"]=$cabecera["porcentaje_freegames"];

													$quiebre_to_db["contrato_id"]=$formula["contrato_id"];
													$quiebre_to_db["formula_id"]=$formula["formula_id"];
													$quiebre_to_db["tipo_contrato_id"]=$formula["tipo_contrato_id"];

													$quiebre_to_db["total_cliente"]=$cabecera["total_cliente"];
													$quiebre_to_db["total_freegames"]=$cabecera["total_freegames"];
													$quiebre_to_db["columna_valor"]=$cabecera["total_apostado"];
													quiebres_insert(data_to_db($quiebre_to_db));
												}
											}
										}
									}
								}
							}
						}
						$cabeceras[]=$cabecera;
					}
				}

				$mysqli->query("START TRANSACTION");
				foreach ($cabeceras as $cabe_key => $cabe_val){
					$cabecera_to_db = cabecera_to_db($cabe_val);
					$cabecera_id = cabecera_insert($cabecera_to_db);
				}
				$mysqli->query("COMMIT");
			}
		}
		elseif((int)$data["servicio_id"] == 18){//saldo teleservicios
			$servicio_id = 18;
			$cdv_id = 44;
			$producto_id = 14;

			$locales = [];
			$command_locales = "
				SELECT l.id
				FROM tbl_saldo_teleservicios_transaccion str
					LEFT JOIN tbl_locales l ON (l.id = str.local_id)
				WHERE
					str.created_at >= '{$data["fecha_inicio"]}'
					AND str.created_at < '{$data["fecha_fin"]}'
					AND str.status = 1
				GROUP BY l.id
			";

			$query_locales = $mysqli->query($command_locales);
			while($l_d = $query_locales->fetch_assoc()){
				$locales[] = $l_d;
			}
			$mysqli->next_result();
			ksort($locales);

			$total_saldo_teleservicios = [];
			$total_saldo_teleservicios_command = "	SELECT
														l.id as local_id,
														l.cc_id,
														SUM(str.monto) AS monto,
														str.tipo_id,
														IF(str.tipo_id = 1 ,'Dep','Retiro')
													FROM tbl_saldo_teleservicios_transaccion str
														LEFT JOIN tbl_locales l ON (l.id = str.local_id)
														inner JOIN tbl_caja c ON c.id = str.turno_id
													WHERE
														str.created_at >= '{$data["fecha_inicio"]}'
														AND str.created_at < '{$data["fecha_fin"]}'
														AND str.status = 1
													GROUP BY l.id, str.tipo_id
												";
												
			$total_saldo_teleservicios_query = $mysqli->query($total_saldo_teleservicios_command);
			while( $tt = $total_saldo_teleservicios_query->fetch_assoc() ){
				$total_saldo_teleservicios[$tt["local_id"]]["depositos"] = (isset($total_saldo_teleservicios[$tt["local_id"]]["depositos"]) && $total_saldo_teleservicios[$tt["local_id"]]["depositos"]!=0)?$total_saldo_teleservicios[$tt["local_id"]]["depositos"]:0;
				$total_saldo_teleservicios[$tt["local_id"]]["retiros"] = (isset($total_saldo_teleservicios[$tt["local_id"]]["retiros"]) && $total_saldo_teleservicios[$tt["local_id"]]["retiros"]!=0)?$total_saldo_teleservicios[$tt["local_id"]]["retiros"]:0;
				if( $tt["tipo_id"] == 1 ) {//DEP
					$total_saldo_teleservicios[$tt["local_id"]]["depositos"] = $tt["monto"];
				}
				if( $tt["tipo_id"] == 2 ) {//RET
					$total_saldo_teleservicios[$tt["local_id"]]["retiros"] = $tt["monto"];
				}
			}

			if ($proceso_id) {
				foreach ($locales as $local_id => $l) {
					if(!isset($total_saldo_teleservicios[$l['id']]) || $l['id']==0) {
						continue;
					}
					$zona = $mysqli->query("SELECT zona_id, red_id FROM tbl_locales WHERE id = ".$l['id'])->fetch_assoc();
					$cabecera = [];
					$cabecera["at_unique_id"] = md5($proceso_id.$data["fecha_inicio"].$cdv_id.$data["servicio_id"].$l['id']);
					$cabecera["proceso_unique_id"] = $proceso_id;
					$cabecera["fecha"] = $data["fecha_inicio"];
					$cabecera["fecha_proceso"] = date("Y-m-d H:i:s");
					$cabecera["local_id"] = $l['id'];
					if(isset($zona["zona_id"])) $cabecera["zona_id"] = (int)$zona["zona_id"];
					$cabecera["servicio_id"] = $data["servicio_id"];
					$cabecera["canal_de_venta_id"] = $cdv_id;
					$cabecera["producto_id"] = $producto_id;
					$cabecera["moneda_id"] = 1;

					$cabecera["total_depositado_web"] = 0;
					$cabecera["total_retirado_web"] = 0;
					if( array_key_exists($l['id'], $total_saldo_teleservicios) ){
						$cabecera["total_depositado_web"] = $total_saldo_teleservicios[$l['id']]["depositos"];
						$cabecera["total_retirado_web"] = $total_saldo_teleservicios[$l['id']]["retiros"];
					}
					$cabecera["total_caja_web"] = ( $cabecera["total_depositado_web"] - $cabecera["total_retirado_web"] );
					$cabecera["caja_fisico"]    = ( $cabecera["total_depositado_web"] - $cabecera["total_retirado_web"] );

					$cabecera['num_registros']  =  0;
					$cabecera['num_tickets']  =  0;
					$cabecera['num_tickets_ganados']  =  0;
					$cabecera['num_tickets_ganados_pagados']  =  0;
					$cabecera['moneda_id']  =  '1';
					$cabecera['total_apostado']  =  0;
					$cabecera['cashdesk_apostado']  =  0;
					$cabecera['total_ganado']  =  0;
					$cabecera['cashdesk_ganado']  =  0;
					$cabecera['total_pagado']  =  0;
					$cabecera['cashdesk_pagado']  =  0;
					$cabecera['total_produccion']  =  0;
					$cabecera['cashdesk_produccion']  =  0;
					$cabecera['cashdesk_caja_fisico']  =  0;
					$cabecera['total_depositado']  =  0;
					$cabecera['total_anulado_retirado']  =  0;
					$cabecera['total_ingresado']  =  0;
					$cabecera['total_devuelto']  =  0;
					$cabecera['porcentaje_cliente'] = 0;
					$cabecera['total_cliente'] = 0;
					$cabecera['porcentaje_freegames'] = 0;
					$cabecera['total_freegames'] = 0;
					$cabecera['pagado_en_otra_tienda']  =  0;
					$cabecera['pagado_de_otra_tienda']  =  0;
					$cabecera['total_pagos_fisicos']  =  0;
					$cabecera['retirado_de_otras_tiendas']  =  0;
					$cabecera['cashdesk_balance']  =  0;
					$cabecera['tipo_de_cambio_id']  =  null;
					$cabeceras[]=$cabecera;
				}
				if(!empty($cabeceras)){
					feed_database($cabeceras, 'tbl_transacciones_cabecera', 'at_unique_id', false);
				}
			}
		}/*fin servicio 18 */
		else{
			$locales = [];
			$command_locales = "
				SELECT 
					canal_de_venta_id,
					local_id
				FROM tbl_local_proveedor_id 
				WHERE servicio_id = '".$data["servicio_id"]."'
			";
			$query_locales = $mysqli->query($command_locales);
			while($l_d=$query_locales->fetch_assoc()){
				if(array_key_exists($l_d["local_id"], $locales)){
					if(!in_array($l_d["canal_de_venta_id"], $locales[$l_d["local_id"]])){
						$locales[$l_d["local_id"]][]=$l_d["canal_de_venta_id"];
					}
				}else{
					$locales[$l_d["local_id"]][]=$l_d["canal_de_venta_id"];
				}
			}
			$mysqli->next_result();
			ksort($locales);

			$call_param = "'".$data["fecha_inicio"]."','".$data["fecha_fin"]."','".$data["servicio_id"]."'";

			$total_sumas=[];
			$total_sumas_command = "CALL get_sumas_test(".$call_param.")";
			$total_sumas_query = $mysqli->query($total_sumas_command);
			while($ts=$total_sumas_query->fetch_assoc()){
				$total_sumas[$ts["local_id"]][$ts["canal_de_venta_id"]]["total_apostado"]=$ts["total_apostado"];
				$total_sumas[$ts["local_id"]][$ts["canal_de_venta_id"]]["total_ganado"]=$ts["total_ganado"];
			}
			$mysqli->next_result();


			$total_resumen_turnover=[];
			$get_terminal_turnover_command = "CALL get_resumen_turnover(".$call_param.")";
			$get_terminal_turnover_query = $mysqli->query($get_terminal_turnover_command);
			while($tt=$get_terminal_turnover_query->fetch_assoc()){
				$total_resumen_turnover[$tt["local_id"]]=$tt;
			}
			$mysqli->next_result();

			$total_terminal_turnover=[];
			$get_terminal_turnover_command = "CALL get_terminal_turnover(".$call_param.")";
			$get_terminal_turnover_query = $mysqli->query($get_terminal_turnover_command);
			while($tt=$get_terminal_turnover_query->fetch_assoc()){
				$total_terminal_turnover[$tt["local_id"]]=$tt;
			}
			$mysqli->next_result();

			$total_cashdesk_turnover=[];
			$get_cashdesk_turnover_command = "CALL get_cashdesk_turnover(".$call_param.")";
			$get_cashdesk_turnover_query = $mysqli->query($get_cashdesk_turnover_command);
			while($tt=$get_cashdesk_turnover_query->fetch_assoc()){
				$total_cashdesk_turnover[$tt["local_id"]]=$tt;
			}
			$mysqli->next_result();

			$num_tickets=[];
			$num_tickets_command = "CALL get_num_tickets_test(".$call_param.")";
			$num_tickets_query = $mysqli->query($num_tickets_command);
			while($nt=$num_tickets_query->fetch_assoc()){
				$num_tickets[$nt["local_id"]][$nt["canal_de_venta_id"]]=$nt["num_tickets"];
			}
			$mysqli->next_result();

			$num_tickets_resumen=[];
			$num_tickets_resumen_command = "CALL get_num_tickets_resumen(".$call_param.")";
			$num_tickets_resumen_query = $mysqli->query($num_tickets_resumen_command);
			while($ntr=$num_tickets_resumen_query->fetch_assoc()){
				$num_tickets_resumen[$ntr["local_id"]][$ntr["canal_de_venta_id"]]=$ntr["num_tickets"];
			}
			//echo var_dump($num_tickets_resumen);exit();
			$mysqli->next_result();

			$num_tickets_ganados=[];
			$num_tickets_ganados_command = "CALL get_num_tickets_ganados(".$call_param.")";
			$num_tickets_ganados_query = $mysqli->query($num_tickets_ganados_command);
			while($ntg=$num_tickets_ganados_query->fetch_assoc()){
				$num_tickets_ganados[$ntg["local_id"]][$ntg["canal_de_venta_id"]]=$ntg["num_tickets"];
			}
			$mysqli->next_result();

			$num_tickets_ganados_pagados=[];
			$num_tickets_ganados_pagados_command = "CALL get_num_tickets_ganados_pagados(".$call_param.")";
			$num_tickets_ganados_pagados_query = $mysqli->query($num_tickets_ganados_pagados_command);
			while($ntgp=$num_tickets_ganados_pagados_query->fetch_assoc()){
				$num_tickets_ganados_pagados[$ntgp["local_id"]][$ntgp["canal_de_venta_id"]]=$ntgp["num_tickets"];
			}
			$mysqli->next_result();

			$total_pagados=[];
			$get_pagados_command = "CALL get_pagados_test(".$call_param.")";
			$get_pagados_query = $mysqli->query($get_pagados_command);
			while($tp=$get_pagados_query->fetch_assoc()){
				$total_pagados[$tp["local_id"]][$tp["canal_de_venta_id"]]["total_ganado_pagado"]=$tp;
			}
			$mysqli->next_result();

			$total_ganados=[];
			$get_ganados_command = "CALL get_ganados(".$call_param.")";
			$get_ganados_query = $mysqli->query($get_ganados_command);
			while($tp=$get_ganados_query->fetch_assoc()){
				$total_ganados[$tp["local_id"]][$tp["canal_de_venta_id"]]=$tp["total_ganado"];
			}
			//echo var_dump($total_ganados);exit();
			$mysqli->next_result();

			$caja_total_pagados=[];
			$get_pagados_command = "CALL get_pbet_pagados(".$call_param.")";
			$get_pagados_query = $mysqli->query($get_pagados_command);
			while($tp=$get_pagados_query->fetch_assoc()){
				$caja_total_pagados[$tp["local_id"]]=$tp["total_ganado_pagado"];
			}
			$mysqli->next_result();		

			$total_pagados_en_otra_tiendad=[];
			$get_pagados_en_otra_tienda_command = "CALL get_pagados_en_otra_tienda_test(".$call_param.")";
			$get_pagados_en_otra_tienda_query = $mysqli->query($get_pagados_en_otra_tienda_command);
			while($tp=$get_pagados_en_otra_tienda_query->fetch_assoc()){
				$total_pagados_en_otra_tiendad[$tp["local_id"]][$tp["canal_de_venta_id"]]["total_pagado"]=$tp;				
			}
			$mysqli->next_result();

			$total_pagados_de_otra_tiendad=[];
			$get_pagados_de_otra_tienda_command = "CALL get_pagados_de_otra_tienda_test(".$call_param.")";
			$get_pagados_de_otra_tienda_query = $mysqli->query($get_pagados_de_otra_tienda_command);
			while($tp=$get_pagados_de_otra_tienda_query->fetch_assoc()){
				$total_pagados_de_otra_tiendad[$tp["paid_local_id"]][$tp["paid_canal_de_venta_id"]]["total_pagado"]=$tp;	
			}
			$mysqli->next_result();

			$cashdesk_balance=[];
			$get_cashdesk_balance_command = "CALL get_cashdesk_balance(".$call_param.")";
			$get_cashdesk_balance_query = $mysqli->query($get_cashdesk_balance_command);
			while($tp=$get_cashdesk_balance_query->fetch_assoc()){
				$cashdesk_balance[$tp["local_id"]]=$tp;	
			}
			$mysqli->next_result();

			$terminal_premios_pagados=[];
			$get_terminal_premios_pagados_command = "CALL get_terminal_premios_pagados(".$call_param.")";
			$get_terminal_premios_pagados_query = $mysqli->query($get_terminal_premios_pagados_command);
			while($tp=$get_terminal_premios_pagados_query->fetch_assoc()){
				$terminal_premios_pagados[$tp["local_id"]]=$tp["premios_pagados"];	
			}
			$mysqli->next_result();

			$local_formulas = [];
			$get_local_formulas_command = "CALL get_locales_formulas()";
			$get_local_formulas_query = $mysqli->query($get_local_formulas_command);
			while($lf=$get_local_formulas_query->fetch_assoc()){
				if($lf["tipo"]=="normal"){
					$local_formulas[$lf["local_id"]][$lf["canal_de_venta_id"]]=$lf;
				}elseif($lf["tipo"]=="quiebre"){
					$local_formulas[$lf["local_id"]][$lf["canal_de_venta_id"]][]=$lf;
				}elseif($lf["tipo"]=="modular"){
					$local_formulas[$lf["local_id"]][$lf["canal_de_venta_id"]][]=$lf;
				}
			}
			$mysqli->next_result();

			$apostado_odds = [];
			$get_get_apostado_odds_command = "CALL get_apostado_odds(".$call_param.")";
			$get_get_apostado_odds_query = $mysqli->query($get_get_apostado_odds_command);
			while($ao=$get_get_apostado_odds_query->fetch_assoc()){
				$apostado_odds[$ao["local_id"]][$ao["canal_de_venta_id"]][]=$ao;
			}
			$mysqli->next_result();
			
			/*$proceso_id = false;
			if(array_key_exists("proceso_id", $data)){
				$proceso_id = $data["proceso_id"];
				if($proceso_id=="false"){
					$proceso_id = false;
				}
			}

			if($proceso_id){
				$new_proceso_command_update = "UPDATE tbl_transacciones_procesos 
												SET 
													fecha_fin = '".$data["fecha_inicio"]."'
													,time_end = '".$date."'
												WHERE at_unique_id = '".$proceso_id."'";
			}else{
				$proceso_id = md5(date("c").$data["servicio_id"]);
				$new_proceso_command_insert = "INSERT INTO tbl_transacciones_procesos (at_unique_id) VALUES ('".$proceso_id."')";
				$new_proceso_query = $mysqli->query($new_proceso_command_insert);
				$new_proceso_command_update = "UPDATE tbl_transacciones_procesos 
												SET 
													fecha = '".$date."'
													,fecha_inicio = '".$data["fecha_inicio"]."'
													,fecha_fin = '".$data["fecha_inicio"]."'
													,servicio_id = '".$data["servicio_id"]."'
													,tipo = 'liquidacion'
													,time_init = '".$date."'
													,time_end = '".$date."'
													,usuario_id = '".$login["id"]."'
													,estado = '0'
												WHERE at_unique_id = '".$proceso_id."'";
			}
			$mysqli->query($new_proceso_command_update);

			$return["proceso_id"]=$proceso_id;*/

			if($proceso_id){

				foreach ($locales as $local_id => $local_csdv) {
					foreach ($local_csdv as $key => $cdv_id) {
						$zona = $mysqli->query("SELECT zona_id FROM tbl_locales WHERE id = ".$local_id)->fetch_assoc();
						$cabecera = [];
							$cabecera["at_unique_id"]=md5($proceso_id.$data["fecha_inicio"].$cdv_id.$data["servicio_id"].$local_id);
							$cabecera["proceso_unique_id"]=$proceso_id;
							$cabecera["fecha"]=$data["fecha_inicio"];
							$cabecera["fecha_proceso"]=date("Y-m-d H:i:s");
							$cabecera["local_id"]=$local_id;
							if(isset($zona["zona_id"])) $cabecera["zona_id"]=(int)$zona["zona_id"];
							$cabecera["servicio_id"]=$data["servicio_id"];
							$cabecera["canal_de_venta_id"]=$cdv_id;
							$cabecera["producto_id"] = in_array((int)$cabecera["canal_de_venta_id"], [15,16,17,19,23,24,25,26,27]) ? 1: 2;


							$cabecera["num_tickets"]=0;
							if(array_key_exists($local_id, $num_tickets)){
								if(array_key_exists($cdv_id, $num_tickets[$local_id])){
									$cabecera["num_tickets"]=$num_tickets[$local_id][$cdv_id];
								}
							}
							if(array_key_exists($local_id, $num_tickets_resumen)){
								if(array_key_exists($cdv_id, $num_tickets_resumen[$local_id])){
									if($cdv_id==18 || $cdv_id==21 || $cdv_id==24 || $cdv_id==25 || $cdv_id==26 || $cdv_id==27){
										$cabecera["num_tickets"]=$num_tickets_resumen[$local_id][$cdv_id];
									}
								}
							}
							$cabecera["num_tickets_ganados"]=0;
							if(array_key_exists($local_id, $num_tickets_ganados)){
								if(array_key_exists($cdv_id, $num_tickets_ganados[$local_id])){
									$cabecera["num_tickets_ganados"]=$num_tickets_ganados[$local_id][$cdv_id];
								}
							}
							$cabecera["num_tickets_ganados_pagados"]=0;
							if(array_key_exists($local_id, $num_tickets_ganados_pagados)){
								if(array_key_exists($cdv_id, $num_tickets_ganados_pagados[$local_id])){
									$cabecera["num_tickets_ganados_pagados"]=$num_tickets_ganados_pagados[$local_id][$cdv_id];
								}
							}

							$cabecera["num_registros"]=$cabecera["num_tickets"];
							$cabecera["moneda_id"]=1;
							$cabecera["total_apostado"]=0;	
							$cabecera["cashdesk_apostado"]=0;
							if(array_key_exists($local_id, $total_sumas)){
								if(array_key_exists($cdv_id, $total_sumas[$local_id])){
									$cabecera["total_apostado"]=$total_sumas[$local_id][$cdv_id]["total_apostado"];
									// $cabecera["total_ganado"]=$total_sumas[$local_id][$cdv_id]["total_ganado"];
								}
							}
							if($cdv_id==16){
								if(array_key_exists($local_id, $cashdesk_balance)){
									$cabecera["cashdesk_apostado"] = $cashdesk_balance[$local_id]["apostado"];
								}
							}
							$cabecera["total_ganado"]=0;
							if($cdv_id==17){ //SBT-NEGOCIOS
								if(array_key_exists($local_id, $total_ganados)){
									if(array_key_exists($cdv_id, $total_ganados[$local_id])){
										$cabecera["total_ganado"] = $total_ganados[$local_id][$cdv_id];
									}
								}						
							}elseif($cdv_id==16){ //PBET
								if(array_key_exists($local_id, $total_ganados)){
									if(array_key_exists($cdv_id, $total_ganados[$local_id])){
										$cabecera["total_ganado"] = $total_ganados[$local_id][$cdv_id];
									}
								}
								if(array_key_exists($local_id, $cashdesk_balance)){
									$cabecera["cashdesk_ganado"] = $cashdesk_balance[$local_id]["ganado"];
								}
							}else{
								if(array_key_exists($local_id, $total_sumas)){
									if(array_key_exists($cdv_id, $total_sumas[$local_id])){
										$cabecera["total_ganado"]=$total_sumas[$local_id][$cdv_id]["total_ganado"];
									}
								}
							}

							$cabecera["pagado_en_otra_tienda"]=0;
							$cabecera["pagado_de_otra_tienda"]=0;
							if(array_key_exists($local_id, $total_pagados_en_otra_tiendad)){
								if(array_key_exists($cdv_id, $total_pagados_en_otra_tiendad[$local_id])){
									$t_pagado_en_otra_tienda = $total_pagados_en_otra_tiendad[$local_id][$cdv_id]["total_pagado"];
									$cabecera["pagado_en_otra_tienda"]=$t_pagado_en_otra_tienda["total_pagado"];
								}
							}
							if(array_key_exists($local_id, $total_pagados_de_otra_tiendad)){
								if(array_key_exists($cdv_id, $total_pagados_de_otra_tiendad[$local_id])){
									$t_pagado_de_otra_tienda = $total_pagados_de_otra_tiendad[$local_id][$cdv_id]["total_pagado"];
									$cabecera["pagado_de_otra_tienda"]=$t_pagado_de_otra_tienda["total_pagado"];
								}
							}


							$cabecera["total_pagado"]=0;
							$cabecera["cashdesk_pagado"]=0;
							
							if($cdv_id==17){ //SBT-NEGOCIOS
								if(array_key_exists($local_id, $terminal_premios_pagados)){
									$cabecera["total_pagado"] = $terminal_premios_pagados[$local_id];
								}						
							}elseif($cdv_id==16){ //PBET
								if(array_key_exists($local_id, $caja_total_pagados)){
									$cabecera["total_pagado"] = $caja_total_pagados[$local_id];
								}
								if(array_key_exists($local_id, $cashdesk_balance)){
									$cabecera["cashdesk_pagado"] = $cashdesk_balance[$local_id]["pagado"];
								}
							}elseif($cdv_id==15 || $cdv_id==24 || $cdv_id==25 || $cdv_id==26 || $cdv_id==27){
								$cabecera["total_pagado"]=$cabecera["total_ganado"];
							}else{
								if(array_key_exists($local_id, $total_pagados)){
									if(array_key_exists($cdv_id, $total_pagados[$local_id])){
										$t_pagados = $total_pagados[$local_id][$cdv_id]["total_ganado_pagado"];
										$cabecera["total_pagado"]=$t_pagados["total_ganado_pagado"];
									}
								}
							}


							$cabecera["total_depositado"]=0;
							$cabecera["total_anulado_retirado"]=0;

							if($cdv_id==17){ //SBT-NEGOCIOS
								if(array_key_exists($local_id, $total_terminal_turnover)){
									$cabecera["total_depositado"]+=$total_terminal_turnover[$local_id]["total_income"];
									$cabecera["total_anulado_retirado"]+=$total_terminal_turnover[$local_id]["total_terminal_withdraw"];
								}
								if(array_key_exists($local_id, $total_cashdesk_turnover)){
									$cabecera["total_depositado"]+=$total_cashdesk_turnover[$local_id]["terminal_income"];
									$cabecera["total_anulado_retirado"]+=$total_cashdesk_turnover[$local_id]["terminal_withdraw"];
								}
								$cabecera["total_anulado_retirado"]-=$cabecera["pagado_de_otra_tienda"];
							}
							if($cdv_id==18){ //JV GLOBAL BET
								if(array_key_exists($local_id, $total_resumen_turnover)){
									$cabecera["total_depositado"]=$total_resumen_turnover[$local_id]["total_income"];
									$cabecera["total_anulado_retirado"]=$total_resumen_turnover[$local_id]["total_withdraw"];
								}
							}
							if($cdv_id==21){ //JV GOLDEN RACE
								if(array_key_exists($local_id, $total_resumen_turnover)){
									$cabecera["total_depositado"]=$total_resumen_turnover[$local_id]["total_income"];
									$cabecera["total_anulado_retirado"]=$total_resumen_turnover[$local_id]["total_withdraw"];
								}
							}
							
							$cabecera["total_depositado_web"]=0;
							$cabecera["total_retirado_web"]=0;
							$cabecera["total_caja_web"]=0;

							// if($cdv_id==16){ //PBET
							// 	if(array_key_exists($local_id, $total_cashdesk_turnover)){
							// 		$cabecera["total_depositado_web"]=$total_cashdesk_turnover[$local_id]["total_deposit"];
							// 		$cabecera["total_retirado_web"]=$total_cashdesk_turnover[$local_id]["total_withdraw"];
							// 	}
							// 	$cabecera["total_caja_web"]=($cabecera["total_depositado_web"]-$cabecera["total_retirado_web"]);
							// }

							$cabecera["total_pagos_fisicos"]=0;

							if($cdv_id==17){
								$cabecera["total_pagos_fisicos"]=($cabecera["total_pagado"]-$cabecera["pagado_en_otra_tienda"]);
							}else{
								// $cabecera["total_pagos_fisicos"]=($cabecera["total_pagado"]-$cabecera["pagado_en_otra_tienda"]+$cabecera["pagado_de_otra_tienda"]);
								$cabecera["total_pagos_fisicos"]=($cabecera["total_pagado"]+$cabecera["pagado_de_otra_tienda"]-$cabecera["pagado_en_otra_tienda"]);
							}

							$cabecera["caja_fisico"]=0;
							$cabecera["cashdesk_produccion"]=0;
							$cabecera["cashdesk_caja_fisico"]=0;
							if($cdv_id==15){		
								$cabecera["total_produccion"]=($cabecera["total_apostado"]-$cabecera["total_pagado"]);					
							}elseif($cdv_id==16){
								$cabecera["total_produccion"]=($cabecera["total_apostado"]-$cabecera["total_pagado"]);
								$cabecera["cashdesk_produccion"]=($cabecera["cashdesk_apostado"]-$cabecera["cashdesk_pagado"]);
								// $cabecera["caja_fisico"]=($cabecera["total_apostado"]-$cabecera["total_pagado"]-$cabecera["pagado_de_otra_tienda"]+$cabecera["pagado_en_otra_tienda"]);
								// $cabecera["caja_fisico"]=($cabecera["total_apostado"]-$cabecera["total_pagado"]+$cabecera["total_caja_web"]-$cabecera["pagado_de_otra_tienda"]+$cabecera["pagado_en_otra_tienda"]);
								$cabecera["caja_fisico"]=($cabecera["total_apostado"]-$cabecera["total_pagado"]+$cabecera["total_caja_web"]-$cabecera["pagado_de_otra_tienda"]+$cabecera["pagado_en_otra_tienda"]);
								$cabecera["cashdesk_caja_fisico"]=($cabecera["cashdesk_apostado"]-$cabecera["cashdesk_pagado"]-$cabecera["pagado_de_otra_tienda"]+$cabecera["pagado_en_otra_tienda"]);
							}elseif(in_array($cdv_id, [17,19])){
								$cabecera["total_produccion"]=($cabecera["total_depositado"]-$cabecera["total_anulado_retirado"]-$cabecera["total_pagado"]);
								$cabecera["caja_fisico"] = ($cabecera["total_depositado"] - $cabecera["total_anulado_retirado"] - $cabecera["total_pagado"] + $cabecera["pagado_en_otra_tienda"] - $cabecera["pagado_de_otra_tienda"]);
								// $cabecera["caja_fisico"] = ($cabecera["total_pagado"] - $cabecera["pagado_en_otra_tienda"]);
							}elseif($cdv_id==24 || $cdv_id==25 || $cdv_id==26 || $cdv_id==27){
								$cabecera["total_produccion"]=($cabecera["total_apostado"]-$cabecera["total_ganado"]);
								// $cabecera["caja_fisico"] = ($cabecera["total_pagado"] - $cabecera["pagado_en_otra_tienda"]);
							}
							else{
								$cabecera["total_produccion"]=($cabecera["total_apostado"]-$cabecera["total_pagado"]);
								$cabecera["caja_fisico"]=$cabecera["total_produccion"];
							}

							$cabecera["resultado_negocio"]=($cabecera["total_apostado"]-$cabecera["total_ganado"]);	



							$cabecera["cashdesk_balance"]=0;
							if($cdv_id==16){
								if(array_key_exists($local_id, $cashdesk_balance)){
									$cabecera["cashdesk_balance"]=$cashdesk_balance[$local_id]["balance"];
								}
							}

							$cabecera["porcentaje_cliente"]=0;
							$cabecera["total_cliente"]=0;
							$cabecera["porcentaje_freegames"]=0;
							$cabecera["total_freegames"]=0;

							if(array_key_exists($local_id, $local_formulas)){
								// $return["local_formula"]=$local_formulas[$local_id];
								if($cdv_id==16){
									if(array_key_exists(16, $local_formulas[$local_id])){
										$formula = $local_formulas[$local_id][16];
										if(array_key_exists(0, $formula)){
											// $return["formula"]=$formula[0];
											if($formula[0]["formula_id"]==17){
												$f = get_condicionales($formula[0]);
												if($formula[0]["tipo_contrato_id"]==4){ // Participacion FG
													$con = reset($f);
													$return["con_".$cdv_id]=$con;
													if($con["donde"]=="apostado"){
														// $cabecera["total_freegames"] = ($cabecera["total_apostado"] * $con["valor"] / 100) + $cabecera["total_caja_web"];
														$cabecera["total_freegames"] = ($cabecera["total_apostado"] * $con["valor"] / 100);
														$cabecera["porcentaje_freegames"] = $con["valor"];
														// $cabecera["porcentaje_cliente"] = (100 - $cabecera["porcentaje_freegames"]);
													}
													$cabecera["total_cliente"] = ( $cabecera["total_produccion"] - $cabecera["total_freegames"]);
												}else{
													$tickets = get_tickets($local_id,$data);
													foreach ($tickets as $key => $t) {
														$t["comision"] = get_comision($f,$t);
														$cabecera["total_cliente"]+=$t["comision"];
													}
													$cabecera["total_freegames"] = ( $cabecera["caja_fisico"] - $cabecera["total_cliente"] );
												}
											}else{
												if($formula[0]["fuente"]=="odds" || $formula[0]["columna"]=="apostado"){
													// $apostado_odds[$ao["local_id"]][$ao["canal_de_venta_id"]]=$ao;
													$quiebres = [];
													$grupos = [];
													$grupos_sumas = [];
													$grupos_calculados = [];
													$quiebres_to_db = [];
													foreach ($formula as $f_key => $f_val) {
														$next_key = $f_key+1;
														$new_quiebre = [];
														$new_quiebre["fuente"]=$formula[$f_key]["fuente"];
														$new_quiebre["columna"]=$formula[$f_key]["columna"];
														$new_quiebre["monto_cliente"]=$formula[$f_key]["monto_cliente"];
														$new_quiebre["monto_freegames"]=number_format((100 - $new_quiebre["monto_cliente"]),2);
														$new_quiebre["desde"]=$formula[$f_key]["desde"];
														if(array_key_exists($next_key, $formula)){
															$new_quiebre["hasta"]=$formula[$next_key]["desde"];
														}else{
															$new_quiebre["hasta"]=999999;
														}
														$quiebres[]=$new_quiebre;

														$quiebre_to_db = [];
														$quiebre_to_db["at_unique_id"]=md5($proceso_id.date("c").$local_id.$formula[$f_key]["desde"].$formula[$f_key]["hasta"].$new_quiebre["monto_cliente"].$new_quiebre["monto_freegames"]);
														$quiebre_to_db["proceso_unique_id"]=$proceso_id;
														$quiebre_to_db["local_id"]=$local_id;
														$quiebre_to_db["servicio_id"]=$data["servicio_id"];
														$quiebre_to_db["canal_de_venta_id"]=$cdv_id;
														$quiebre_to_db["fuente"]=$new_quiebre["fuente"];
														$quiebre_to_db["columna"]=$new_quiebre["columna"];
														$quiebre_to_db["desde"]=$formula[$f_key]["desde"];
														$quiebre_to_db["hasta"]=$formula[$f_key]["hasta"];
														$quiebre_to_db["porcentaje_cliente"]=$new_quiebre["monto_cliente"];
														$quiebre_to_db["porcentaje_freegames"]=$new_quiebre["monto_freegames"];

														$quiebre_to_db["contrato_id"]=$formula[$f_key]["contrato_id"];
														$quiebre_to_db["formula_id"]=$formula[$f_key]["formula_id"];
														$quiebre_to_db["tipo_contrato_id"]=$formula[$f_key]["tipo_contrato_id"];

														$quiebres_to_db[$new_quiebre["monto_cliente"]]=$quiebre_to_db;
													}
													// echo "formula";
													// print_r($formula);
													foreach ($quiebres as $q_key => $q_v) {
														if(array_key_exists($local_id, $apostado_odds)){
															if(array_key_exists($cdv_id, $apostado_odds[$local_id])){
																$tickets = $apostado_odds[$local_id][$cdv_id];
																// print_r($tickets);
																// exit();
																foreach ($tickets as $t_key => $t_val) {
																	if($t_val["odds"] >= $q_v["desde"] && $t_val["odds"] < $q_v["hasta"]){
																		$grupos[$q_v["monto_cliente"]][]=$t_val;
																	}
																}
															}
														}
													}
													// echo "quiebres";
													// print_r($quiebres);
													// exit();
													foreach ($grupos as $g_key => $g_val) {
														foreach ($g_val as $gt_key => $gt_val) {
															if(array_key_exists($g_key, $grupos_sumas)){
																$grupos_sumas[$g_key]+=$gt_val["apostado"];
															}else{
																$grupos_sumas[$g_key]=$gt_val["apostado"];
															}												
														}
													}
													// echo "grupos_sumas";
													// print_r($grupos_sumas);
													// exit();
													$prev_total_cliente = 0;
													foreach ($grupos_sumas as $gs_key => $gs_val) {
														$res = ($gs_val * $gs_key) / 100;
														$grupos_calculados[$gs_key]=$res;
														$prev_total_cliente+=$res;
														$quiebres_to_db[$gs_key]["total_cliente"]=$res;
														$quiebres_to_db[$gs_key]["total_freegames"]=($gs_val - $res);
														$quiebres_to_db[$gs_key]["columna_valor"]=$gs_val;
													}
													$cabecera["total_cliente"] = $prev_total_cliente;
													$cabecera["total_freegames"] = ($cabecera["caja_fisico"] - $prev_total_cliente);
													// echo "grupos_calculados";
													// print_r($grupos_calculados);

													// echo "quiebres_to_db";
													// print_r($quiebres_to_db);
													$mysqli->query("START TRANSACTION");
														foreach ($quiebres_to_db as $qtd_key => $qtd_val) {
															quiebres_insert(data_to_db($qtd_val));
														}
													$mysqli->query("COMMIT");
													// exit();
												}else{
													foreach ($formula as $f_key => $f_val) {
														$total_produccion = $cabecera["total_produccion"];
														$desde = $f_val["desde"];
														$hasta = $f_val["hasta"];
														$fuente = $f_val["fuente"];
														$columna = $f_val["columna"];
														$contrato_id = $f_val["contrato_id"];
														$formula_id = $f_val["formula_id"];
														$tipo_contrato_id = $f_val["tipo_contrato_id"];
														$f_val["monto_freegames"]=number_format((100 - $f_val["monto_cliente"]),2);
														if($hasta<=0){
															$hasta = 999999;
														}
														if($total_produccion >= $desde && $total_produccion < $hasta){
															$cabecera["porcentaje_cliente"]=$f_val["monto_cliente"];
														}
													}
													$cabecera["total_cliente"]=(($cabecera["total_produccion"] * $cabecera["porcentaje_cliente"]) / 100);
													$cabecera["porcentaje_freegames"] = (100 - $cabecera["porcentaje_cliente"]);
													$cabecera["total_freegames"] = ($cabecera["total_produccion"] - $cabecera["total_cliente"]);

													$quiebre_to_db = [];
														$quiebre_to_db["at_unique_id"]=md5($proceso_id.date("c").$local_id.$desde.$hasta.$cabecera["porcentaje_cliente"].$cabecera["porcentaje_freegames"]);
														$quiebre_to_db["proceso_unique_id"]=$proceso_id;
														$quiebre_to_db["local_id"]=$local_id;
														$quiebre_to_db["servicio_id"]=$data["servicio_id"];
														$quiebre_to_db["canal_de_venta_id"]=$cdv_id;
														$quiebre_to_db["fuente"]=$fuente;
														$quiebre_to_db["columna"]=$columna;
														$quiebre_to_db["desde"]=$desde;
														$quiebre_to_db["hasta"]=$hasta;
														$quiebre_to_db["porcentaje_cliente"]=$cabecera["porcentaje_cliente"];
														$quiebre_to_db["porcentaje_freegames"]=$cabecera["porcentaje_freegames"];

														$quiebre_to_db["contrato_id"]=$contrato_id;
														$quiebre_to_db["formula_id"]=$formula_id;
														$quiebre_to_db["tipo_contrato_id"]=$tipo_contrato_id;

														$quiebre_to_db["total_cliente"]=$cabecera["total_cliente"];
														$quiebre_to_db["total_freegames"]=$cabecera["total_freegames"];
														$quiebre_to_db["columna_valor"]=$cabecera["total_produccion"];
													quiebres_insert(data_to_db($quiebre_to_db));
												}
											}
										}else{
											// if($formula["formula_id"]==17){
											// }else{											
												if($formula["tipo"]=="normal"){
													if($formula["columna"]=="resultado"){
														if($formula["operador_id"]==1){
															$cabecera["porcentaje_cliente"]=$formula["monto_cliente"];
															$cabecera["total_cliente"]= (($cabecera["total_produccion"] * $cabecera["porcentaje_cliente"]) / 100);
															$cabecera["porcentaje_freegames"] = (100 - $cabecera["porcentaje_cliente"]);
															$cabecera["total_freegames"] = ($cabecera["total_produccion"] - $cabecera["total_cliente"]);


															$quiebre_to_db = [];
																$quiebre_to_db["at_unique_id"]=md5($proceso_id.date("c").$local_id.$formula["desde"].$formula["hasta"].$cabecera["porcentaje_cliente"].$cabecera["porcentaje_freegames"]);
																$quiebre_to_db["proceso_unique_id"]=$proceso_id;
																$quiebre_to_db["local_id"]=$local_id;
																$quiebre_to_db["servicio_id"]=$data["servicio_id"];
																$quiebre_to_db["canal_de_venta_id"]=$cdv_id;
																$quiebre_to_db["fuente"]=$formula["fuente"];
																$quiebre_to_db["columna"]=$formula["columna"];
																$quiebre_to_db["desde"]=$formula["desde"];
																$quiebre_to_db["hasta"]=$formula["hasta"];
																$quiebre_to_db["porcentaje_cliente"]=$cabecera["porcentaje_cliente"];
																$quiebre_to_db["porcentaje_freegames"]=$cabecera["porcentaje_freegames"];

																$quiebre_to_db["contrato_id"]=$formula["contrato_id"];
																$quiebre_to_db["formula_id"]=$formula["formula_id"];
																$quiebre_to_db["tipo_contrato_id"]=$formula["tipo_contrato_id"];

																$quiebre_to_db["total_cliente"]=$cabecera["total_cliente"];
																$quiebre_to_db["total_freegames"]=$cabecera["total_freegames"];
																$quiebre_to_db["columna_valor"]=$cabecera["total_produccion"];
															quiebres_insert(data_to_db($quiebre_to_db));
														}
													}elseif($formula["columna"]=="apostado"){
														if($formula["operador_id"]==1){
															$cabecera["porcentaje_cliente"]=$formula["monto_cliente"];
															$cabecera["total_cliente"]= (($cabecera["total_apostado"] * $cabecera["porcentaje_cliente"]) / 100);
															$cabecera["porcentaje_freegames"] = (100 - $cabecera["porcentaje_cliente"]);
															$cabecera["total_freegames"] = ($cabecera["caja_fisico"] - $cabecera["total_cliente"]);

															$quiebre_to_db = [];
																$quiebre_to_db["at_unique_id"]=md5($proceso_id.date("c").$local_id.$formula["desde"].$formula["hasta"].$cabecera["porcentaje_cliente"].$cabecera["porcentaje_freegames"]);
																$quiebre_to_db["proceso_unique_id"]=$proceso_id;
																$quiebre_to_db["local_id"]=$local_id;
																$quiebre_to_db["servicio_id"]=$data["servicio_id"];
																$quiebre_to_db["canal_de_venta_id"]=$cdv_id;
																$quiebre_to_db["fuente"]=$formula["fuente"];
																$quiebre_to_db["columna"]=$formula["columna"];
																$quiebre_to_db["desde"]=$formula["desde"];
																$quiebre_to_db["hasta"]=$formula["hasta"];
																$quiebre_to_db["porcentaje_cliente"]=$cabecera["porcentaje_cliente"];
																$quiebre_to_db["porcentaje_freegames"]=$cabecera["porcentaje_freegames"];

																$quiebre_to_db["contrato_id"]=$formula["contrato_id"];
																$quiebre_to_db["formula_id"]=$formula["formula_id"];
																$quiebre_to_db["tipo_contrato_id"]=$formula["tipo_contrato_id"];

																$quiebre_to_db["total_cliente"]=$cabecera["total_cliente"];
																$quiebre_to_db["total_freegames"]=$cabecera["total_freegames"];
																$quiebre_to_db["columna_valor"]=$cabecera["total_apostado"];
															quiebres_insert(data_to_db($quiebre_to_db));
														}
													}elseif($formula["columna"]=="nuevo_resultado"){
														if($formula["operador_id"]==1){
															$cabecera["porcentaje_cliente"]=$formula["monto_cliente"];
															$cabecera["total_cliente"]= (($cabecera["resultado_negocio"] * $cabecera["porcentaje_cliente"]) / 100);
															$cabecera["porcentaje_freegames"] = (100 - $cabecera["porcentaje_cliente"]);
															$cabecera["total_freegames"] = (($cabecera["resultado_negocio"]) - $cabecera["total_cliente"]);

															$quiebre_to_db = [];
																$quiebre_to_db["at_unique_id"]=md5($proceso_id.date("c").$local_id.$formula["desde"].$formula["hasta"].$cabecera["porcentaje_cliente"].$cabecera["porcentaje_freegames"]);
																$quiebre_to_db["proceso_unique_id"]=$proceso_id;
																$quiebre_to_db["local_id"]=$local_id;
																$quiebre_to_db["servicio_id"]=$data["servicio_id"];
																$quiebre_to_db["canal_de_venta_id"]=$cdv_id;
																$quiebre_to_db["fuente"]=$formula["fuente"];
																$quiebre_to_db["columna"]=$formula["columna"];
																$quiebre_to_db["desde"]=$formula["desde"];
																$quiebre_to_db["hasta"]=$formula["hasta"];
																$quiebre_to_db["porcentaje_cliente"]=$cabecera["porcentaje_cliente"];
																$quiebre_to_db["porcentaje_freegames"]=$cabecera["porcentaje_freegames"];

																$quiebre_to_db["contrato_id"]=$formula["contrato_id"];
																$quiebre_to_db["formula_id"]=$formula["formula_id"];
																$quiebre_to_db["tipo_contrato_id"]=$formula["tipo_contrato_id"];

																$quiebre_to_db["total_cliente"]=$cabecera["total_cliente"];
																$quiebre_to_db["total_freegames"]=$cabecera["total_freegames"];
																$quiebre_to_db["columna_valor"]=$cabecera["total_apostado"];
															quiebres_insert(data_to_db($quiebre_to_db));
														}
													}
												}
											// }
										}
									}
								}
								if($cdv_id==17){
									if(array_key_exists(17, $local_formulas[$local_id])){
										$formula = $local_formulas[$local_id][17];
										if(array_key_exists(0, $formula)){
											if($formula[0]["formula_id"]==17){
												$f = get_condicionales($formula[0]);
												if($formula[0]["tipo_contrato_id"]==4){ // Participacion FG
													$con = reset($f);
													$return["con_".$cdv_id]=$con;
													if($con["donde"]=="apostado"){
														$cabecera["total_freegames"] = ($cabecera["total_apostado"] * $con["valor"] / 100);
														$cabecera["porcentaje_freegames"] = $con["valor"];
														// $cabecera["porcentaje_cliente"] = (100 - $cabecera["porcentaje_freegames"]);
													}
													$cabecera["total_cliente"] = ( $cabecera["total_produccion"] - $cabecera["total_freegames"] );
												}
											}
										}else{
											if($formula["tipo"]=="normal"){
												if($formula["columna"]=="resultado"){
													if($formula["operador_id"]==1){
														$cabecera["porcentaje_cliente"]=$formula["monto_cliente"];
														$cabecera["total_cliente"]= (($cabecera["total_produccion"] * $cabecera["porcentaje_cliente"]) / 100);
														$cabecera["porcentaje_freegames"]=(100 - $cabecera["porcentaje_cliente"]);
														$cabecera["total_freegames"]=($cabecera["total_produccion"] - $cabecera["total_cliente"]);


														$quiebre_to_db = [];
															$quiebre_to_db["at_unique_id"]=md5($proceso_id.date("c").$local_id.$formula["desde"].$formula["hasta"].$cabecera["porcentaje_cliente"].$cabecera["porcentaje_freegames"]);
															$quiebre_to_db["proceso_unique_id"]=$proceso_id;
															$quiebre_to_db["local_id"]=$local_id;
															$quiebre_to_db["servicio_id"]=$data["servicio_id"];
															$quiebre_to_db["canal_de_venta_id"]=$cdv_id;
															$quiebre_to_db["fuente"]=$formula["fuente"];
															$quiebre_to_db["columna"]=$formula["columna"];
															$quiebre_to_db["desde"]=$formula["desde"];
															$quiebre_to_db["hasta"]=$formula["hasta"];
															$quiebre_to_db["porcentaje_cliente"]=$cabecera["porcentaje_cliente"];
															$quiebre_to_db["porcentaje_freegames"]=$cabecera["porcentaje_freegames"];

															$quiebre_to_db["contrato_id"]=$formula["contrato_id"];
															$quiebre_to_db["formula_id"]=$formula["formula_id"];
															$quiebre_to_db["tipo_contrato_id"]=$formula["tipo_contrato_id"];

															$quiebre_to_db["total_cliente"]=$cabecera["total_cliente"];
															$quiebre_to_db["total_freegames"]=$cabecera["total_freegames"];
															$quiebre_to_db["columna_valor"]=$cabecera["total_produccion"];
														quiebres_insert(data_to_db($quiebre_to_db));
													}
												}elseif($formula["columna"]=="nuevo_resultado"){
													if($formula["operador_id"]==1){
														$cabecera["porcentaje_cliente"]=$formula["monto_cliente"];
														$cabecera["total_cliente"]= (($cabecera["resultado_negocio"] * $cabecera["porcentaje_cliente"]) / 100);
														$cabecera["porcentaje_freegames"] = (100 - $cabecera["porcentaje_cliente"]);
														$cabecera["total_freegames"] = (($cabecera["resultado_negocio"]) - $cabecera["total_cliente"]);

														$quiebre_to_db = [];
															$quiebre_to_db["at_unique_id"]=md5($proceso_id.date("c").$local_id.$formula["desde"].$formula["hasta"].$cabecera["porcentaje_cliente"].$cabecera["porcentaje_freegames"]);
															$quiebre_to_db["proceso_unique_id"]=$proceso_id;
															$quiebre_to_db["local_id"]=$local_id;
															$quiebre_to_db["servicio_id"]=$data["servicio_id"];
															$quiebre_to_db["canal_de_venta_id"]=$cdv_id;
															$quiebre_to_db["fuente"]=$formula["fuente"];
															$quiebre_to_db["columna"]=$formula["columna"];
															$quiebre_to_db["desde"]=$formula["desde"];
															$quiebre_to_db["hasta"]=$formula["hasta"];
															$quiebre_to_db["porcentaje_cliente"]=$cabecera["porcentaje_cliente"];
															$quiebre_to_db["porcentaje_freegames"]=$cabecera["porcentaje_freegames"];

															$quiebre_to_db["contrato_id"]=$formula["contrato_id"];
															$quiebre_to_db["formula_id"]=$formula["formula_id"];
															$quiebre_to_db["tipo_contrato_id"]=$formula["tipo_contrato_id"];

															$quiebre_to_db["total_cliente"]=$cabecera["total_cliente"];
															$quiebre_to_db["total_freegames"]=$cabecera["total_freegames"];
															$quiebre_to_db["columna_valor"]=$cabecera["total_apostado"];
														quiebres_insert(data_to_db($quiebre_to_db));
													}
												}
											}
										}
									}
								}
								if($cdv_id==18 || $cdv_id==21){
									if(array_key_exists(18, $local_formulas[$local_id])){
										$formula = $local_formulas[$local_id][18];
										if(array_key_exists(0, $formula)){
											if($formula[0]["formula_id"]==17){
												$f = get_condicionales($formula[0]);
												if($formula[0]["tipo_contrato_id"]==4){ // Participacion FG
													$con = reset($f);
													$return["con_".$cdv_id]=$con;
													if($con["donde"]=="resultado"){
														$cabecera["total_freegames"] = ($cabecera["total_produccion"] * $con["valor"] / 100);
														$cabecera["porcentaje_freegames"] = $con["valor"];
														// $cabecera["porcentaje_cliente"] = (100 - $cabecera["porcentaje_freegames"]);
													}
													$cabecera["total_cliente"] = ( $cabecera["total_produccion"] - $cabecera["total_freegames"] );
												}
											}
										}else{
											if($formula["tipo"]=="normal"){
												if($formula["columna"]=="resultado"){
													if($formula["operador_id"]==1){
														$cabecera["porcentaje_cliente"]=$formula["monto_cliente"];
														$cabecera["total_cliente"]= (($cabecera["total_produccion"] * $cabecera["porcentaje_cliente"]) / 100);
														$cabecera["porcentaje_freegames"]=(100 - $cabecera["porcentaje_cliente"]);
														$cabecera["total_freegames"]=($cabecera["total_produccion"] - $cabecera["total_cliente"]);

														$quiebre_to_db = [];
															$quiebre_to_db["at_unique_id"]=md5($proceso_id.date("c").$local_id.$formula["desde"].$formula["hasta"].$cabecera["porcentaje_cliente"].$cabecera["porcentaje_freegames"]);
															$quiebre_to_db["proceso_unique_id"]=$proceso_id;
															$quiebre_to_db["local_id"]=$local_id;
															$quiebre_to_db["servicio_id"]=$data["servicio_id"];
															$quiebre_to_db["canal_de_venta_id"]=$cdv_id;
															$quiebre_to_db["fuente"]=$formula["fuente"];
															$quiebre_to_db["columna"]=$formula["columna"];
															$quiebre_to_db["desde"]=$formula["desde"];
															$quiebre_to_db["hasta"]=$formula["hasta"];
															$quiebre_to_db["porcentaje_cliente"]=$cabecera["porcentaje_cliente"];
															$quiebre_to_db["porcentaje_freegames"]=$cabecera["porcentaje_freegames"];

															$quiebre_to_db["contrato_id"]=$formula["contrato_id"];
															$quiebre_to_db["formula_id"]=$formula["formula_id"];
															$quiebre_to_db["tipo_contrato_id"]=$formula["tipo_contrato_id"];

															$quiebre_to_db["total_cliente"]=$cabecera["total_cliente"];
															$quiebre_to_db["total_freegames"]=$cabecera["total_freegames"];
															$quiebre_to_db["columna_valor"]=$cabecera["total_produccion"];
														quiebres_insert(data_to_db($quiebre_to_db));
													}
												}elseif($formula["columna"]=="nuevo_resultado"){
													if($formula["operador_id"]==1){
														$cabecera["porcentaje_cliente"]=$formula["monto_cliente"];
														$cabecera["total_cliente"]= (($cabecera["resultado_negocio"] * $cabecera["porcentaje_cliente"]) / 100);
														$cabecera["porcentaje_freegames"] = (100 - $cabecera["porcentaje_cliente"]);
														$cabecera["total_freegames"] = (($cabecera["resultado_negocio"]) - $cabecera["total_cliente"]);

														$quiebre_to_db = [];
															$quiebre_to_db["at_unique_id"]=md5($proceso_id.date("c").$local_id.$formula["desde"].$formula["hasta"].$cabecera["porcentaje_cliente"].$cabecera["porcentaje_freegames"]);
															$quiebre_to_db["proceso_unique_id"]=$proceso_id;
															$quiebre_to_db["local_id"]=$local_id;
															$quiebre_to_db["servicio_id"]=$data["servicio_id"];
															$quiebre_to_db["canal_de_venta_id"]=$cdv_id;
															$quiebre_to_db["fuente"]=$formula["fuente"];
															$quiebre_to_db["columna"]=$formula["columna"];
															$quiebre_to_db["desde"]=$formula["desde"];
															$quiebre_to_db["hasta"]=$formula["hasta"];
															$quiebre_to_db["porcentaje_cliente"]=$cabecera["porcentaje_cliente"];
															$quiebre_to_db["porcentaje_freegames"]=$cabecera["porcentaje_freegames"];

															$quiebre_to_db["contrato_id"]=$formula["contrato_id"];
															$quiebre_to_db["formula_id"]=$formula["formula_id"];
															$quiebre_to_db["tipo_contrato_id"]=$formula["tipo_contrato_id"];

															$quiebre_to_db["total_cliente"]=$cabecera["total_cliente"];
															$quiebre_to_db["total_freegames"]=$cabecera["total_freegames"];
															$quiebre_to_db["columna_valor"]=$cabecera["total_apostado"];
														quiebres_insert(data_to_db($quiebre_to_db));
													}
												}
											}
										}
									}
								}
								if($cdv_id==19){
									if(array_key_exists(19, $local_formulas[$local_id])){
										$formula = $local_formulas[$local_id][19];
										if(array_key_exists(0, $formula)){									
										}else{
											if($formula["tipo"]=="normal"){
												if($formula["columna"]=="resultado"){
													if($formula["operador_id"]==1){
														$cabecera["porcentaje_cliente"]=$formula["monto_cliente"];
														$cabecera["total_cliente"]=(($cabecera["total_produccion"] * $cabecera["porcentaje_cliente"]) / 100);
														$cabecera["porcentaje_freegames"]=(100 - $cabecera["porcentaje_cliente"]);
														$cabecera["total_freegames"]=($cabecera["total_produccion"] - $cabecera["total_cliente"]);
														
														$quiebre_to_db = [];
															$quiebre_to_db["at_unique_id"]=md5($proceso_id.date("c").$local_id.$formula["desde"].$formula["hasta"].$cabecera["porcentaje_cliente"].$cabecera["porcentaje_freegames"]);
															$quiebre_to_db["proceso_unique_id"]=$proceso_id;
															$quiebre_to_db["local_id"]=$local_id;
															$quiebre_to_db["servicio_id"]=$data["servicio_id"];
															$quiebre_to_db["canal_de_venta_id"]=$cdv_id;
															$quiebre_to_db["fuente"]=$formula["fuente"];
															$quiebre_to_db["columna"]=$formula["columna"];
															$quiebre_to_db["desde"]=$formula["desde"];
															$quiebre_to_db["hasta"]=$formula["hasta"];
															$quiebre_to_db["porcentaje_cliente"]=$cabecera["porcentaje_cliente"];
															$quiebre_to_db["porcentaje_freegames"]=$cabecera["porcentaje_freegames"];

															$quiebre_to_db["contrato_id"]=$formula["contrato_id"];
															$quiebre_to_db["formula_id"]=$formula["formula_id"];
															$quiebre_to_db["tipo_contrato_id"]=$formula["tipo_contrato_id"];

															$quiebre_to_db["total_cliente"]=$cabecera["total_cliente"];
															$quiebre_to_db["total_freegames"]=$cabecera["total_freegames"];
															$quiebre_to_db["columna_valor"]=$cabecera["total_produccion"];
														quiebres_insert(data_to_db($quiebre_to_db));
													}
												}elseif($formula["columna"]=="nuevo_resultado"){
													if($formula["operador_id"]==1){
														$cabecera["porcentaje_cliente"]=$formula["monto_cliente"];
														$cabecera["total_cliente"]= (($cabecera["resultado_negocio"] * $cabecera["porcentaje_cliente"]) / 100);
														$cabecera["porcentaje_freegames"] = (100 - $cabecera["porcentaje_cliente"]);
														$cabecera["total_freegames"] = (($cabecera["resultado_negocio"]) - $cabecera["total_cliente"]);

														$quiebre_to_db = [];
															$quiebre_to_db["at_unique_id"]=md5($proceso_id.date("c").$local_id.$formula["desde"].$formula["hasta"].$cabecera["porcentaje_cliente"].$cabecera["porcentaje_freegames"]);
															$quiebre_to_db["proceso_unique_id"]=$proceso_id;
															$quiebre_to_db["local_id"]=$local_id;
															$quiebre_to_db["servicio_id"]=$data["servicio_id"];
															$quiebre_to_db["canal_de_venta_id"]=$cdv_id;
															$quiebre_to_db["fuente"]=$formula["fuente"];
															$quiebre_to_db["columna"]=$formula["columna"];
															$quiebre_to_db["desde"]=$formula["desde"];
															$quiebre_to_db["hasta"]=$formula["hasta"];
															$quiebre_to_db["porcentaje_cliente"]=$cabecera["porcentaje_cliente"];
															$quiebre_to_db["porcentaje_freegames"]=$cabecera["porcentaje_freegames"];

															$quiebre_to_db["contrato_id"]=$formula["contrato_id"];
															$quiebre_to_db["formula_id"]=$formula["formula_id"];
															$quiebre_to_db["tipo_contrato_id"]=$formula["tipo_contrato_id"];

															$quiebre_to_db["total_cliente"]=$cabecera["total_cliente"];
															$quiebre_to_db["total_freegames"]=$cabecera["total_freegames"];
															$quiebre_to_db["columna_valor"]=$cabecera["total_apostado"];
														quiebres_insert(data_to_db($quiebre_to_db));
													}
												}
											}
										}
									}
								}
							}else{
								// $return["local_formula"]="ño";

							}


							// $cabecera["total_cliente"]= (($cabecera["total_apostado"] * $cabecera["porcentaje_cliente"]) / 100);
							// $cabecera["porcentaje_freegames"] = (100 - $cabecera["porcentaje_cliente"]);
							// $cabecera["total_freegames"] = ($cabecera["total_produccion"] - $cabecera["total_cliente"]);




						$cabeceras[]=$cabecera;
						// print_r($cabecera);
					}
				}

				$mysqli->query("START TRANSACTION");
					foreach ($cabeceras as $cabe_key => $cabe_val){			
						$cabecera_to_db = cabecera_to_db($cabe_val);
						$cabecera_id = cabecera_insert($cabecera_to_db);
					}
				$mysqli->query("COMMIT");		
			}
		}
	}else{
		$return["error"]="no_servicio_id";
	}
}
// include("db_connect.php");
// $login["id"]=1;
// $test_data=[];
// $test_data["fecha"]="2021-02-16";
// // $test_data["fecha_fin"]="2021-02-16";
// $test_data["servicio_id"]=9;
// transacciones_build_liquidaciones($test_data);
function quiebres_insert($quiebre){
	global $mysqli;
	global $return;

	$command = "INSERT INTO tbl_liquidacion_quiebres";
	$command.="(";
	$command.=implode(",", array_keys($quiebre));
	$command.=")";
	$command.=" VALUES ";
	$command.="(";
	$command.=implode(",", $quiebre);
	$command.=")";
	$command.=" ON DUPLICATE KEY UPDATE ";
	$uqn=0;
	foreach ($quiebre as $key => $value) {
		if($uqn>0) { $command.=","; }
		$command.= $key." = VALUES(".$key.")";
		$uqn++;
	}
	// echo $command;
	// echo 
	$mysqli->query($command);
	if($mysqli->error){
		$return["ERROR_MYSQL"]=$mysqli->error;
		print_r($mysqli->error);
		echo "\n";
		echo $command;
		exit();
	}
	$affected_rows = $mysqli->affected_rows;
	if($affected_rows==2){
		//$return["quiebres_insert_command_UPDATE"][]=$command;
		// $return["quiebres_updateadas"]++;
	}elseif($affected_rows==1){
		//$return["quiebres_insert_command_INSERT"][]=$command;
		// $return["quiebres_insertadas"]++;
	}else{
		// $return["quiebres_insert_command_NOTHING"][]=$command;
		// $return["quiebres_nothing"]++;
	}
	return true;
}

function cabecera_to_db($d){
	global $mysqli;
	$tmp=[];
	// $nulls=array("null","",false);
	foreach ($d as $k => $v) {
		// if($v===0){
		// 	$tmp[$k]=$v;
		// }elseif(in_array($v, $nulls)){
		// 	$tmp[$k]="NULL";
		// }else{
			if(is_float($v)){
				$v=str_replace(",", ".", $v);
				$tmp[$k]="'".$v."'";
			}elseif(is_int($v)){
				$tmp[$k]=$v;
			}else{
				$v=str_replace(",", ".", $v);
				$tmp[$k]="'".trim($mysqli->real_escape_string($v))."'";
			}
		// }
	}
	return $tmp;
}

function get_condicionales($f){
	global $mysqli;
	$command = "
		SELECT
			fc.id,
			fc.var1,
			fc.var_operador,
			fc.var2,
			fc.is_true_id,
			fc.is_false_id,
			fc.valor,
			fc.valor_operador,
			fc.donde,
			fc.tipo
			-- CONCAT(fc.var2,)
		FROM tbl_contrato_formula_condicionales fc
		WHERE fc.estado = '1'
		AND fc.formula_id = '".$f["formula_id"]."'
		AND fc.contrato_id = '".$f["contrato_id"]."'
		AND fc.servicio_id = '".$f["servicio_id"]."'
		AND fc.producto_id = '".$f["producto_id"]."'
		AND fc.estado = '1'
		ORDER BY fc.ord ASC
		";
	$query = $mysqli->query($command);
	if($mysqli->error){
		print_r($mysqli->error);
		exit();
	}
	$return = [];
	while($fc = $query->fetch_assoc()){
		$return[$fc["id"]]=$fc;
	}
	return $return;
}

function get_tickets($local_id,$data){
	global $mysqli;
	$tks = [];
	$command =  "
		SELECT 
			d.id
			,d.ticket_id
			,d.odds
			,d.apostado
			-- ,r.type
			,LOWER(r.type) AS type
			,IF(r.is_live IS NOT NULL,1,0) AS is_live
		FROM tbl_transacciones_detalle d
		LEFT JOIN tbl_transacciones_repositorio r ON (r.at_unique_id = d.at_unique_id)
		LEFT JOIN tbl_contratos con ON (con.local_id = d.local_id)
		WHERE d.id IS NOT NULL
		AND d.local_id = '".$local_id."'
		AND d.created >= '".$data["fecha_inicio"]."'
		AND d.created < '".$data["fecha_fin"]."'
		AND d.servicio_id = '".$data["servicio_id"]."'
		AND d.tipo = '1'
		AND (
		IF(
			(con.tipo_contrato_id = 2)
			, (d.state != 'Returned')
			, (d.state IS NULL OR d.state != 'never_use_this_state')
			)
		)
		";
	$query = $mysqli->query($command);
	while ($tk = $query->fetch_assoc()) {
		$tks[]=$tk;
	}
	return $tks;
}

function get_comision($f,$tk){
	$comision = 0;
	// if(array_key_exists(0, array))
	if(count($f)){
		$eval = '';
		$eval = formula_exec($tk,$f,array_values($f)[0]["id"]);
		eval($eval);
	}
	return $comision;
}

function formula_exec($tk,$fs,$f_id){
	$curr_f = $fs[$f_id];
	$ret = '';
	if($curr_f["tipo"]=="if"){
		$ret.= 'if("'.$tk[$curr_f['var1']].'" '.$curr_f['var_operador'].' "'.$curr_f['var2'].'"){';
		if($curr_f['is_true_id']){
			$ret.= ''.formula_exec($tk,$fs,$curr_f['is_true_id']);
		}
		$ret.= '}';
		$ret.= 'else{';
		if($curr_f['is_false_id']){
			$ret.= ''.formula_exec($tk,$fs,$curr_f['is_false_id']);
		}
		$ret.= '}';
	}elseif($curr_f["tipo"]=="action"){
		$ret.= '
			$comision = '.($tk[$curr_f['donde']] * $curr_f['valor'] / 100).';
			';
	}else{
	}
	return $ret;
}

function formula_exec_OLD($tk,$fs,$f_id){
	$ret = '';
	// $ret.= '
	// ';
	$ret.= 'if("'.$tk[$fs[$f_id]['var1']].'" '.$fs[$f_id]['var_operador'].' "'.$fs[$f_id]['var2'].'"){';
	// $ret.= '
	// ';
	if($fs[$f_id]['is_true_id']){
		$ret.= '	'.formula_exec($tk,$fs,$fs[$f_id]['is_true_id']);
	// $ret.= '
	// ';
	}else{
		// $ret.= ' $comision = '.$f_id.'; ';
		$ret.= '
			$comision = '.($tk[$fs[$f_id]['donde']] * $fs[$f_id]['valor'] / 100).';
			';
	}
	// $ret.= '
	// ';
	$ret.= '}';
	$ret.= '
	';
	if($fs[$f_id]['is_false_id']){
		$ret.= 'else';
		$ret.= formula_exec($tk,$fs,$fs[$f_id]['is_false_id']);
	}else{
		// $ret.= ' $comision = '.($tk[$fs[$f_id]['donde']] * $fs[$f_id]['valor'] / 100).'; ';
	}
	return $ret;
}
function get_pagado_de_otra_tienda_torito($fecha_inicio,$fecha_fin){
	global $mysqli;
	$query = $mysqli->query(get_pagado_de_otra_tienda_torito_query($fecha_inicio,$fecha_fin));
	$en_otra_tienda=array();
	$de_otra_tienda=array();
	while ($tk = $query->fetch_assoc()) {
		$en_otra_tienda[$tk["venta_id_store"]]+=$tk["monto"];
		$de_otra_tienda[$tk["pago_id_store"]]+=$tk["monto"];
	}
	return["en_otra_tienda"=>$en_otra_tienda,"de_otra_tienda"=>$de_otra_tienda];
}
function get_pagado_de_otra_tienda_torito_query($fecha_inicio,$fecha_fin){
	

	$command="
	select 
	ty.venta_prize_id,
	ty.venta_fecha,
	IFNULL(l.nombre,'EXTERNO') local_venta, 
	IFNULL((select nombre from tbl_razon_social where id=l.razon_social_id limit 1),'EXTERNO')  venta_razon_social,
	ty.venta_id_store,
	-- IF(ty.venta_id_store<>ty.id_store,1,0) value,
	ty.id_store pago_id_store,
	IFNULL(l2.nombre,'LOCAL NO CONFIGURADO') local_pago, 
	IFNULL( (select nombre from tbl_razon_social where id=l2.razon_social_id limit 1),'NO CONFIGURADO') pago_razon_social,
	ty.date pago_fecha,
	ty.prize_id pago_prize_id,
	ty.amount monto
	from (
	SELECT
		IFNULL( (
		SELECT
			prize_id
		FROM
			tbl_torito_en_de_v2 tv
		WHERE
			tv.transactiontype in ('VENTA GN','VENTA MM')
			AND tv.prize_id LIKE CONCAT('%', CAST(t.prize_id AS CHAR), '%')
		LIMIT 1
		),'00000' ) AS venta_prize_id,
		IFNULL( (
		SELECT
			date
		FROM
			tbl_torito_en_de_v2 tv
		WHERE
			tv.transactiontype in ('VENTA GN','VENTA MM')
			AND tv.prize_id LIKE CONCAT('%', CAST(t.prize_id AS CHAR), '%')
		LIMIT 1
		),'1992-06-18' )AS venta_fecha,
		IFNULL( (
		SELECT
			id_store 
		FROM
			tbl_torito_en_de_v2 tv
		WHERE
			tv.transactiontype in ('VENTA GN','VENTA MM')
			AND tv.prize_id LIKE CONCAT('%', CAST(t.prize_id AS CHAR), '%')
		LIMIT 1
		),'000000000') AS venta_id_store,
		t.*
	FROM
		tbl_torito_en_de_v2 t
		WHERE
			t.transactiontype = 'PAGO GN'
			AND t.prize_id IS NOT NULL
			AND t.prize_id != ''
			AND t.date >= '{$fecha_inicio}'
			AND t.date < '{$fecha_fin}'
					) ty
	LEFT JOIN tbl_locales l on
		l.cc_id = ty.venta_id_store
	LEFT JOIN tbl_locales l2 on
		l2.cc_id = ty.id_store
	WHERE
		ty.venta_id_store <> ty.id_store
	
	";
	return $command;
}
?>