<?php

include("../sys/db_connect.php");
include("../sys/sys_login.php");

if($_POST["where"]=="liquidaciones"){
	$data = [];

	$cabecera_where = " WHERE id IS NOT NULL";
	$locales_command_where=" WHERE l.id IS NOT NULL";

	$fecha_inicio = date("Y-m-d H:i:s",strtotime("-1 week"));
	$fecha_fin = date("Y-m-d H:i:s");
	$is_liq_final = true;
	$red_id = false;
	$zona_id = false;
	if(array_key_exists("filtro", $_POST)){
		$filtro=$_POST["filtro"];
		if(array_key_exists("red_id", $filtro)){
			if(is_array($filtro["red_id"])){			
				$red_id = $filtro["red_id"];
				sort($red_id);
			}
		}
		if(array_key_exists("zona_id", $filtro)){
			if(is_array($filtro["zona_id"])){			
				$zona_id = $filtro["zona_id"];
				sort($zona_id);
			}
		}
		if(array_key_exists("fecha_inicio", $filtro)){
			$fecha_inicio = $filtro["fecha_inicio"];
		}
		if(array_key_exists("fecha_fin", $filtro)){
			// $fecha_fin = $filtro["fecha_fin"];
			$fecha_fin = date("Y-m-d",strtotime($filtro["fecha_fin"]." +1 day"));
		}
		
		// if(array_key_exists("locales", $filtro)){
		// 	if($filtro["locales"]){
		// 		if(in_array("all", $filtro["locales"])){

		// 		}else{
		// 			$cabecera_where .= " AND local_id IN ('".implode("','", $filtro["locales"])."')";
		// 			$locales_command_where .= " AND l.id IN ('".implode("','", $filtro["locales"])."')";
		// 		}
		// 	}
		// }
		if(array_key_exists("locales", $filtro)){
			if($filtro["locales"]){
				if(in_array("all", $filtro["locales"])){
					if($login["usuario_locales"]){
						// $filtro_where .= " AND l.id IN ('".implode("','", $login["usuario_locales"])."')";
						$cabecera_where .= " AND local_id IN ('".implode("','", $login["usuario_locales"])."')";
						$locales_command_where .= " AND l.id IN ('".implode("','", $login["usuario_locales"])."')";
					}
				}else{
					// $filtro_where .= " AND l.id IN ('".implode("','", $filtro["locales"])."')";
					$cabecera_where .= " AND local_id IN ('".implode("','", $filtro["locales"])."')";
					$locales_command_where .= " AND l.id IN ('".implode("','", $filtro["locales"])."')";
				}
			}else{
				if($login["usuario_locales"]){
					$cabecera_where .= " AND local_id IN ('".implode("','", $login["usuario_locales"])."')";
					$locales_command_where .= " AND l.id IN ('".implode("','", $login["usuario_locales"])."')";
				}
			}
		}else{
			if($login["usuario_locales"]){
				$cabecera_where .= " AND local_id IN ('".implode("','", $login["usuario_locales"])."')";
				$locales_command_where .= " AND l.id IN ('".implode("','", $login["usuario_locales"])."')";
			}			
		}

		if(array_key_exists("canales_de_venta", $filtro)){
			if($filtro["canales_de_venta"]){
				$cabecera_where .= " AND canal_de_venta_id IN ('".implode("','", $filtro["canales_de_venta"])."')";
			}
		}
		if(array_key_exists("proceso_unique_id", $filtro)){
			if($filtro["proceso_unique_id"]){
				$cabecera_where .= " AND proceso_unique_id = '".$filtro["proceso_unique_id"]."' ";
				$is_liq_final=false;						
			}
		}
	}

	if($red_id){
		//print_r($red_id);
		$locales_command_where.=" AND (";
		foreach ($red_id as $red_id_key => $red_id_value) {
			if($red_id_key>0){
				$locales_command_where.=" OR ";
			}
			if($red_id_value == 0){

				$locales_command_where.= "l.red_id IS NULL OR l.red_id = '0'";
				//print_r($locales_command_where);
			}else{
				$locales_command_where.= "l.red_id = '".$red_id_value."'";
			}
		}
		$locales_command_where.=" )";
	}

	// if($zona_id){
	// 	if(!$filtro["locales"] || in_array("all", $filtro["locales"])) {
	// 		$locales_command_where.=" AND (";
	// 		foreach ($zona_id as $zona_id_key => $zona_id_value) {
	// 			if($zona_id_key>0){
	// 				$locales_command_where.=" OR ";
	// 			}
	// 			if($zona_id_value == 0){
	// 				$locales_command_where.= "l.zona_id IS NULL OR l.zona_id = '0'";
	// 			}else{
	// 				$locales_command_where.= "l.zona_id = '".$zona_id_value."'";
	// 			}
	// 		}
	// 		$locales_command_where.=" )";
	// 	}
	// }

	if($is_liq_final){
		$cabecera_where .= " AND estado = '1'";
	}
	$pagina = 0;
	if(array_key_exists("pagina", $_POST)){
		//$pagina = $_POST["pagina"];
	}
	$numero = -1;
	if(array_key_exists("numero", $_POST)){
		//$numero = $_POST["numero"];
	}
	$limit_offset = ($pagina * $numero);
	$data["paginacion"]=[];
	$data["paginacion"]["pagina_actual"]=intval($pagina);
	$data["paginacion"]["numero_por_pagina"]=intval($numero);
	$locales = []; //LOCALES
	$locales_command_where.= " AND l.estado = '1' ";

	if($numero>0){
		$locales_command_limit = " LIMIT ".$limit_offset.",".$numero;
	}else{
		$locales_command_limit = "";
	}

		$locales_command_where .= " AND l.reportes_mostrar = '1'"; 
		
		// MOOOOOSSSTTRRRAAAARRRR_RRREEEEEPPPOOORRRTTTEEEEEEEEE
		$locales_command = "SELECT l.id,l.nombre 
		FROM tbl_locales l
		$locales_command_where 
		ORDER BY l.nombre ASC ";
		//exit();
		$return["locales_command_where"]=$locales_command_where;
		$locales_query = $mysqli->query($locales_command.$locales_command_limit);
		if($mysqli->error){
			$return["ERROR_MYSQL"]=$mysqli->error;
			print_r($mysqli->error);
			echo $locales_command;
		}
		while($lcl=$locales_query->fetch_assoc()){
			$locales[$lcl["id"]]=$lcl;
		}
		$data["paginacion"]["total_items"]=$mysqli->query($locales_command)->num_rows;
		if($numero>0){
			$data["paginacion"]["paginas"]=ceil($data["paginacion"]["total_items"] / $data["paginacion"]["numero_por_pagina"]);
			$data["paginacion"]["desde"]=$limit_offset+1;
			$data["paginacion"]["hasta"]=$limit_offset+$numero;
		}else{
			$data["paginacion"]["paginas"]=1;
			$data["paginacion"]["desde"]=1;
			$data["paginacion"]["hasta"]=$data["paginacion"]["total_items"];
		}
		
		if($red_id){
			$local_id_arr = array_keys($locales);
			$cabecera_where.=" AND local_id IN (".implode(",",$local_id_arr).")";
		}

		if($zona_id) $cabecera_where .= "AND zona_id IN(".implode(",", $zona_id).")";
			// $cabecera_where .= 	($filtro["locales"] && !in_array("all", $filtro["locales"])) ? 
			// 					filter_local_zona($filtro["locales"], $zona_id, $fecha_inicio, $fecha_fin, true) :
			// 					filter_local_zona(array_keys($locales), $zona_id, $fecha_inicio, $fecha_fin, false);				
		$cabecera_where .= " AND fecha >= '".$fecha_inicio."'";
		$cabecera_where .= " AND fecha < '".$fecha_fin."'";

	$transacciones_cabecera = []; // CABECERAS
	$transacciones_cabecera_command = "SELECT id
	,fecha
	,local_id
	,canal_de_venta_id
	,num_tickets
	,total_apostado
	,total_ganado
	,total_ingresado
	,total_pagado
	,total_produccion
	,moneda_id
	,total_depositado
	,total_anulado_retirado
	,total_depositado_web
	,total_retirado_web
	,total_caja_web
	,CONCAT(porcentaje_cliente,'%') AS porcentaje_cliente
	,total_cliente
	,CONCAT(porcentaje_freegames,'%') AS porcentaje_freegames
	,total_freegames
	,pagado_en_otra_tienda
	,pagado_de_otra_tienda
	,total_pagos_fisicos
	,retirado_de_otras_tiendas
	,caja_fisico
	,cashdesk_balance
	,(total_pagado - pagado_en_otra_tienda) AS pagados_en_su_punto_propios
	FROM tbl_transacciones_cabecera ".$cabecera_where;
	//echo $cabecera_where;exit();
	//echo $transacciones_cabecera_command; echo "\n";exit();
	$transacciones_cabecera_query = $mysqli->query($transacciones_cabecera_command);
	// $test_sum = 0;
	while ($tc=$transacciones_cabecera_query->fetch_assoc()) {
		if(array_key_exists($tc["local_id"], $locales)){
			$tc["test_in"]=0;
			$tc["test_out"]=0;
				if($tc["canal_de_venta_id"]==16){ //PBET
					$tc["test_in"] = $tc["total_apostado"] + $tc["total_depositado_web"] + $tc["pagado_en_otra_tienda"];
					$tc["test_out"] = $tc["total_pagado"] + $tc["total_retirado_web"] + $tc["pagado_de_otra_tienda"];
				}
				if($tc["canal_de_venta_id"]==17){ //SBT-Negocios
					$tc["test_in"] = $tc["pagado_en_otra_tienda"];
					$tc["test_out"] = $tc["total_pagado"];
				}
				if($tc["canal_de_venta_id"]==18){ //JV Global Bet
					// $tc["test_balance"] = $tc["total_produccion"];
				}
				if($tc["canal_de_venta_id"]==19){ //Tablet BC
					// $tc["test_balance"] = $tc["total_produccion"];
					$tc["test_out"] = $tc["total_pagado"];
				}
				if($tc["canal_de_venta_id"]==20){ //SBT-BC
					// $tc["test_balance"] = $tc["total_produccion"];
				}
				if($tc["canal_de_venta_id"]==21){ //JV Golden Race
					// $tc["test_balance"] = $tc["total_produccion"];
				}
				$tc["test_balance"]=($tc["test_in"] - $tc["test_out"]);
				$tc["test_diff"]=($tc["test_balance"] - $tc["cashdesk_balance"]);
				$transacciones_cabecera[]=$tc;
				// $test_sum = $test_sum + $tc["total_apostado"];				
			}
		}
	//echo "test_sum: ".$test_sum; echo "\n\n";
	$cdv_arr = []; // CANALES DE VENTA
	$cdv_command = "SELECT id, nombre, codigo FROM tbl_canales_venta WHERE estado = '1' ORDER BY codigo ASC";
	$cdv_query = $mysqli->query($cdv_command);
	while($cdv=$cdv_query->fetch_assoc()){
		$cdv_arr[$cdv["id"]]=$cdv;
	}
	$cdv_arr["total"]=["id"=>"total","nombre"=>"Total","codigo"=>"Total"];

	//	CONTEO DIAS
	$datetime1 = new DateTime($fecha_inicio);
	$datetime2 = new DateTime($fecha_fin);
	$difference = $datetime1->diff($datetime2);
	//	FIN CONTEO DIAS

	$totales = [];
	$totales["general"]=[];
	// $test_sum_2 = 0;
	foreach ($transacciones_cabecera as $tc_k => $tc_v) {
		// if(array_key_exists($tc_v["local_id"], $locales)){
			//$tc_v["canal_de_venta"]=$cdv_arr[$tc_v["canal_de_venta_id"]];
		$locales[$tc_v["local_id"]]["liquidaciones"]["diario"][$tc_v["fecha"]][]=$tc_v;
		// }
		// $test_sum_2 = $test_sum_2 + $tc_v["total_apostado"];
	}
	//echo "test_sum_2: ".$test_sum_2; echo "\n\n";
	foreach ($locales as $local_id => $local_data) {
		if(array_key_exists("liquidaciones", $local_data)){
			$total_rango_fecha=[];
			foreach ($local_data["liquidaciones"]["diario"] as $dia => $liq_data) {
				foreach ($liq_data as $liq_data_key => $liq_data_data) {
					if(!array_key_exists($liq_data_data["canal_de_venta_id"], $total_rango_fecha)){
						$total_rango_fecha[$liq_data_data["canal_de_venta_id"]]=[];
						//$total_rango_fecha[$liq_data_data["canal_de_venta_id"]]["canal_de_venta"]=$cdv_arr[$liq_data_data["canal_de_venta_id"]]["codigo"];
					}
					foreach ($liq_data_data as $key => $value) {
						if(array_key_exists($key, $total_rango_fecha[$liq_data_data["canal_de_venta_id"]])){
							if(in_array($key, ["num_tickets"
								,"total_apostado"
								,"total_ganado"
								,"total_ingresado"
								,"total_pagado"
								,"total_produccion"
								,"total_depositado"
								,"total_anulado_retirado"
								,"total_depositado_web"
								,"total_retirado_web"
								,"total_caja_web"
								,"total_cliente"
								,"total_freegames"
								,"pagado_en_otra_tienda"
								,"pagado_de_otra_tienda"
								,"total_pagos_fisicos"
								,"retirado_de_otras_tiendas"
								,"caja_fisico"
								,"cashdesk_balance"
								,"test_balance"
								,"test_diff"
								,"pagados_en_su_punto_propios"])){
								$total_rango_fecha[$liq_data_data["canal_de_venta_id"]][$key]+=$value;
						}
					}else{
							// $total_rango_fecha[$liq_data_data["canal_de_venta_id"]][$key]=$value;
						$total_rango_fecha[$liq_data_data["canal_de_venta_id"]][$key]=$value;
					}
				}
			}
		}
		$locales[$local_id]["liquidaciones"]["diario"]=false;
		$locales[$local_id]["liquidaciones"]["total_rango_fecha"]=$total_rango_fecha;
	}
}
foreach ($locales as $local_id => $local_data) {
	if($local_id!="all"){
		$liq = $local_data;

		$liq["local_id"]=$local_id;
		$liq["local_nombre"]=$local_data["nombre"];
		$liq["dias_procesados"]=$difference->days;

		if(array_key_exists("liquidaciones", $local_data)){
			$liq["liquidaciones"]["total_rango_fecha"]["total"]=[];
			foreach ($local_data["liquidaciones"]["total_rango_fecha"] as $cdv_id => $liq_data) {
				foreach ($liq_data as $liq_data_key => $liq_data_value) {
					if(array_key_exists($liq_data_key, $liq["liquidaciones"]["total_rango_fecha"]["total"])){
							// if($liq_data_key=="caja_fisico"){
							// 	// if($liq_data_value>0){
							// 	// 	$liq["liquidaciones"]["total_rango_fecha"]["total"][$liq_data_key]-=$liq_data_value;
							// 	// }else{
							// 		// $liq["liquidaciones"]["total_rango_fecha"]["total"][$liq_data_key]+=$liq_data_value;
							// 	// }

							// 	// $liq["liquidaciones"]["total_rango_fecha"]["total"][$liq_data_key]=($liq["liquidaciones"]["total_rango_fecha"]["total"][$liq_data_key] + $liq_data_value);
							// 	// $liq["liquidaciones"]["total_rango_fecha"]["total"][$liq_data_key]+=$liq_data["test_diff"];
							// 	$liq["liquidaciones"]["total_rango_fecha"]["total"][$liq_data_key]+=$liq_data_value;
							// }else{
						if(in_array($liq_data_key, ["num_tickets"
							,"total_apostado"
							,"total_ganado"
							,"total_ingresado"
							,"total_pagado"
							,"total_produccion"
							,"total_depositado"
							,"total_anulado_retirado"
							,"total_depositado_web"
							,"total_retirado_web"
							,"total_caja_web"
							,"total_cliente"
							,"total_freegames"
							,"pagado_en_otra_tienda"
							,"pagado_de_otra_tienda"
							,"total_pagos_fisicos"
							,"retirado_de_otras_tiendas"
							,"caja_fisico"
							,"cashdesk_balance"
							,"test_in"
							,"test_out"
							,"test_balance"
							,"test_diff"
							,"pagados_en_su_punto_propios"])){
							$liq["liquidaciones"]["total_rango_fecha"]["total"][$liq_data_key]+=$liq_data_value;
					}
							// }
				}else{
							// if($liq_data_key=="caja_fisico"){
							// 	// if($liq_data_value>0){
							// 	// 	$liq["liquidaciones"]["total_rango_fecha"]["total"][$liq_data_key]=-$liq_data_value;
							// 	// }else{
							// 		// $liq["liquidaciones"]["total_rango_fecha"]["total"][$liq_data_key]=$liq_data["test_diff"];
							// 	// }
							// 	// $liq["liquidaciones"]["total_rango_fecha"]["total"][$liq_data_key]=$liq_data["test_diff"];
							// 	$liq["liquidaciones"]["total_rango_fecha"]["total"][$liq_data_key]=$liq_data_value;
							// }else{
					$liq["liquidaciones"]["total_rango_fecha"]["total"][$liq_data_key]=$liq_data_value;
							// }
				}
			}
		}
				//$totales["general"][]=$liq["liquidaciones"]["total_rango_fecha"]["total"];
	}else{
		$liq["liquidaciones"]["total_rango_fecha"]=[];
	}
			// $liq["liquidaciones"]["total_rango_fecha"]["total"]["caja_fisico"]=$liq["liquidaciones"]["total_rango_fecha"]["total"]["test_diff"];
	$data["locales"][]=$liq;
}
}
if(isset($data["locales"])){
	foreach ($data["locales"] as $data_id => $data_val){
		if(array_key_exists("liquidaciones", $data_val)){
			if(array_key_exists("total_rango_fecha", $data_val["liquidaciones"])){
				if(array_key_exists("total", $data_val["liquidaciones"]["total_rango_fecha"])){
					foreach ($data_val["liquidaciones"]["total_rango_fecha"]["total"] as $t_key => $t_val) {
							//echo $t_key; echo "\n";
						if(in_array($t_key, ["num_tickets"
							,"total_apostado"
							,"total_ganado"
							,"total_ingresado"
							,"total_pagado"
							,"total_produccion"
							,"total_depositado"
							,"total_anulado_retirado"
							,"total_depositado_web"
							,"total_retirado_web"
							,"total_caja_web"
							,"total_cliente"
							,"total_freegames"
							,"pagado_en_otra_tienda"
							,"pagado_de_otra_tienda"
							,"total_pagos_fisicos"
							,"retirado_de_otras_tiendas"
							,"caja_fisico"
							,"cashdesk_balance"])){
							if(array_key_exists($t_key, $totales["general"])){
									//$totales[$t_key]+=$data_val["liquidaciones"]["total_rango_fecha"]["total"][$t_key];
									//$total[$t_key]+=$t_val;
								$totales["general"][$t_key]+=$t_val;
							}else{
									//$totales[$t_key]=$data_val["liquidaciones"]["total_rango_fecha"]["total"][$t_key];
								$totales["general"][$t_key]=$t_val;
							}
						}
					}
				}
			}
		}
	}
}
	//print_r($totales);
	//exit();


$data["totales"]=$totales;
$return["data"]=$data;
$table = array();
$table[]=array(
	"",
	"",
	"",
	"",
	"",
	"",
);
$table[]=array(
	"Nombre Local",
	"RESULTADO DEL NEGOCIO",
	"DEPOSITADO WEB",
	"RETIRADO WEB",
	"TK PAGADO EN OTRO PUNTO",
	"TK PAGADO DE OTRO PUNTO",
);
foreach ($data['locales'] as $item) {
	if(isset($item['liquidaciones']['total_rango_fecha']['total'])){		
		$table[]=array(
			$item['local_nombre'],
			$item['liquidaciones']['total_rango_fecha']['total']['total_produccion'],
			$item['liquidaciones']['total_rango_fecha']['total']['total_depositado_web'],
			$item['liquidaciones']['total_rango_fecha']['total']['total_retirado_web'],
			$item['liquidaciones']['total_rango_fecha']['total']['pagado_en_otra_tienda'],
			$item['liquidaciones']['total_rango_fecha']['total']['pagado_de_otra_tienda'],
		);		
	}
	else{		
	}
}

require_once('../phpexcel/classes/PHPExcel.php');
	
$doc = new PHPExcel();
$doc->setActiveSheetIndex(0);
$doc->getActiveSheet()->fromArray($table);
	
$filename = "reporte_recaudacion_liquidaciones_fr.xlsx";
$excel_path = '/var/www/html/export/files_exported/recaudacion/'.$filename;

$objWriter = PHPExcel_IOFactory::createWriter($doc, 'Excel2007');
$objWriter->save($excel_path);

echo json_encode(array(
	"path" => '/export/files_exported/recaudacion/'.$filename,
	"tipo" => "excel",
	"ext" => "xls",
	"size" => filesize($excel_path),
	"fecha_registro" => date("d-m-Y h:i:s"),
));

exit; 
}                

?>