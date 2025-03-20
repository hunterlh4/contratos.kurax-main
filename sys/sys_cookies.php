<?php

/* contratos */
//print_r($_POST);
if(isset($_POST["contratos_list_cols_submit"])){
	if(isset($_POST["contratos_list_cols"])){
		setcookie('contratos_list_cols', json_encode($_POST["contratos_list_cols"]), time() + (1 * 365 * 24 * 60 * 60),"/");
	}else{
		//setcookie("contratos_list_cols","xxx",time()-1000);
		setcookie('contratos_list_cols', json_encode(""), time() + (1 * 365 * 24 * 60 * 60),"/");
	}
}
/* /contratos */


/*	liquidaciones */
$liq_filtro_cookie_expire = time() + (1 * 365 * 24 * 60 * 60);

$liquidaciones_filtro = array();
$liquidaciones_filtro["liq_filtro_inicio_fecha"]=date("Y-m-d",strtotime("-1 week"));
$liquidaciones_filtro["liq_filtro_inicio_hora"]="00:00";
$liquidaciones_filtro["liq_filtro_fin_fecha"]=date("Y-m-d");
$liquidaciones_filtro["liq_filtro_fin_hora"]="00:00";
$liquidaciones_filtro["liq_filtro_local_id"]="all";

foreach ($_COOKIE as $key => $value) {
	$filtro_header = strstr($key, "liq_filtro_");
	if($filtro_header){
		$liquidaciones_filtro[$key]=$value;
	}
}
if(isset($_POST["liquidaciones_filtro"])){
	foreach ($_POST as $key => $value) {
		$filtro_header = strstr($key, "liq_filtro_");
		if($filtro_header){
			$liquidaciones_filtro[$key]=$value;
			setcookie($key, $value, $liq_filtro_cookie_expire,"/");
		}		
	}
}
//print_r($_COOKIE);
/*	/liquidaciones	*/
?>