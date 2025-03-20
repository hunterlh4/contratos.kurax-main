<?php
if(isset($_POST["abrir_caja_monto_inicial_refresh"])){
	$get_data = $_POST["abrir_caja_monto_inicial_refresh"];
	$sql_command="SELECT 
				-- CAST(IF(df.valor, df.valor, 0) AS DECIMAL(20,2)) AS valor
				-- IF(df.valor,df.valor,'200') AS valor
				df.valor
				FROM tbl_caja_datos_fisicos df
				WHERE df.tipo_id = '11'
				AND df.caja_id = (SELECT c.id FROM tbl_caja c WHERE c.local_caja_id = '".$get_data["local_caja_id"]."' AND c.estado = '1' ORDER BY c.fecha_operacion DESC, c.fecha_cierre DESC LIMIT 1)";
	$sql_query = $mysqli->query($sql_command);
	$itm = $sql_query->fetch_assoc();
	// if($itm["valor"]<=0){
	// 	$itm=false;
	// }
	if(!$itm){
		$itm = $mysqli->query("SELECT IFNULL(valor,0) as valor FROM tbl_local_caja_config WHERE local_id = '".$get_data["local_id"]."' AND estado = '1' AND campo = 'monto_inicial'")->fetch_assoc();
	}
	// 	$local_caja_config = array();
	// 	$local_caja_config["monto_inicial"] = array("nombre"=>"Monto Inicial","valor"=>0);
	// 	// $local_caja_config["valla_deposito"] = array("nombre"=>"Valla Depósito","valor"=>0);

	// 	$local_caja_config_command = "SELECT  valor FROM tbl_local_caja_config WHERE local_id = '".$get_data["local_id"]."' AND estado = '1' AND campo = 'monto_inicial' GROUP BY campo";
	// 	$local_caja_config_query = $mysqli->query($local_caja_config_command);
	// 	$local_caja_config_fetch_assoc = $local_caja_config_query;
	// 	// while($lcc=$local_caja_config_query->fetch_assoc()){
	// 		$local_caja_config[$lcc["campo"]]["valor"]=$lcc["valor"];
	// 	// }
	// 		$itm["valor"]=$local_caja_config_fetch_assoc["valor"];
	// 	$itm["valor"]="1500.00";
	// }
	// print_r($itm);
	// echo $itm["valor"];
	$itm["valor"] = (isset($itm["valor"]) && $itm["valor"]) ? $itm["valor"] : number_format(0, 2);
	echo json_encode($itm,true);
}
?>