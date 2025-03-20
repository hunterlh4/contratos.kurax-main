<?php
$return = array();
$return["memory_init"]=memory_get_usage();
$return["time_init"] = microtime(true);
include("global_config.php");
include("db_connect.php");
include("set_clientes.php");
include("sys_login.php");
if(isset($_POST["opt"])){
	extract($_POST);
	if($opt=="add_item"){
		add_item($data);
	}
	if($opt=="switch_data"){
		switch_data($data);
	}
	if($opt=="save_item"){
		if($data["id"]=="new"){
			add_item($data);
		}else{
			save_item($data);
		}
	}
	if($opt=="del_item"){
		del_item($data);
	}
	if($opt=="sort_list"){
		sort_list($data);
	}
	if($opt=="save_adm_inputs"){
		//$return["post"]=$data;
		//print_r($data);
		foreach ($data["values"] as $key => $value) {
			$new_data = $value;
			$new_data["table"]=$data["table"];
			if($new_data["id"]=="new"){
				if(!empty($new_data["values"]["input_col"]) || !empty($new_data["values"]["label"])){
					add_item($new_data);
				}
			}else{
				//print_r($value);
				save_item($new_data);
			}
		}
	}
	if($opt=="add_cliente_form"){
		print_r($_POST);
		//add_cliente_form($data);
	}
	if($opt=="rec_reprocess"){
		rec_reprocess($data);
	}
	if($opt=="rec_gen_liq"){
		rec_gen_liq($data);
	}

	if($opt=="time_test"){
		sleep(1);
		print_r($_POST);
	}
	if($opt=="import_from_bc"){
		import_from_bc($_POST);		
	}
	if($opt=="liq_pro_action"){
		include("sys_recaudacion.php");
		liq_pro_action($_POST["data"]);
	}
	if($opt=="add_pago_manual"){
		include("sys_transacciones.php");
		include("sys_recaudacion.php");
		add_pago_manual($_POST["data"]);		
	}
}


function add_item($data=false){
	global $mysqli;
	global $return;

	$new_values = array();
	//ubigeo_
	$ubigeo_id = "";
	if(array_key_exists("ubigeo_cod_depa", $data["values"])){
		$ubigeo_id .= $data["values"]["ubigeo_cod_depa"];
	}
	if(array_key_exists("ubigeo_cod_prov", $data["values"])){
		$ubigeo_id .= $data["values"]["ubigeo_cod_prov"];		
	}
	if(array_key_exists("ubigeo_cod_dist", $data["values"])){
		$ubigeo_id .= $data["values"]["ubigeo_cod_dist"];		
	}
	if($ubigeo_id){
		$new_values["ubigeo_id"]=$ubigeo_id;
	}
	foreach ($data["values"] as $key => $value) {
		if(!in_array($key, array("ubigeo_cod_depa","ubigeo_cod_prov","ubigeo_cod_dist"))){	
			if($value){
				$new_values[$key]=$value;
			}
		}
	}
	//	/ubigeo_

	//print_r($data);
	$sql_insert = "INSERT INTO ";
	$sql_insert.= $data["table"];
	$sql_insert.= " (";
	$sql_insert.= "estado";
	//$sql_insert.= implode(",", array_keys($new_values));	
	$sql_insert.= ")";
	$sql_insert.= " VALUES ";
	$sql_insert.= " (";
	$sql_insert.= "'1'";
	$sql_insert.= ")";
	//$sql_insert.= "('";
	//print_r($data);

	//exit();

/*
	if ($data["values"]["grupo_id"]==4 AND $data["table"]=="tbl_menu_sistemas") {
			$sql_insert = "INSERT INTO ";
			$sql_insert.= "tbl_botones";
			$sql_insert.= " (";
			$sql_insert.= "boton,";
			$sql_insert.= "nombre,";	
			//$sql_insert.= "id_padre";			
			$sql_insert.= ")";
			$sql_insert.= " VALUES ";
			$sql_insert.= " (";
			$sql_insert.= "'".$data["values"]["titulo"]."',";
			$sql_insert.= "'".$data["values"]["titulo"]."',";
			//$sql_insert.= "'".$data["values"]["menu_sistema_id"]."'";			
			$sql_insert.= ")";

	}
*/
	
	//foreach ($new_values as $key => $value) {
	//	$new_values[$key]=$mysqli->real_escape_string($value);
	//}

	//$sql_insert.= implode("','", $new_values);	
	//$sql_insert.= "')";
	//$sql_insert.= "";
	$return["sql_insert"]=$sql_insert;
	$mysqli->query($sql_insert);
	$return["item_id"]=$mysqli->insert_id;
	$return["data"]=$data;
	$data["id"]=$return["item_id"];
	save_item($data);
}
function del_item($data){
	global $mysqli;
	$sql_delete = "DELETE FROM ";
	$sql_delete.= $data["table"];
	$sql_delete.= " WHERE ";
	$sql_delete.= " id ";
	$sql_delete.= " = ";
	$sql_delete.= "'";
	$sql_delete.= $data["id"];
	$sql_delete.= "'";
	//echo $sql_delete;
	$return["sql_delete"][]=$sql_delete;
	$mysqli->query($sql_delete);
	$return["data"]=$data;
}
function save_item($data){
	global $mysqli;
	global $return;
	//print_r($data);

	$new_values = array();
	//ubigeo_
	$ubigeo_id = "";
	if(array_key_exists("ubigeo_cod_depa", $data["values"])){
		$ubigeo_id .= $data["values"]["ubigeo_cod_depa"];
	}
	if(array_key_exists("ubigeo_cod_prov", $data["values"])){
		$ubigeo_id .= $data["values"]["ubigeo_cod_prov"];		
	}
	if(array_key_exists("ubigeo_cod_dist", $data["values"])){
		$ubigeo_id .= $data["values"]["ubigeo_cod_dist"];		
	}
	if($ubigeo_id){
		$new_values["ubigeo_id"]=$ubigeo_id;
	}
	foreach ($data["values"] as $key => $value) {
		if(!in_array($key, array("ubigeo_cod_depa","ubigeo_cod_prov","ubigeo_cod_dist"))){	
			$new_values[$key]=$value;
		}
	}

	if($data["table"]=="tbl_usuarios"){
		$new_values["password_md5"]=md5($new_values["password"]);
	}
/*	
	if ($data["table"]=="tbl_botones") {
		$sql_insert = "INSERT INTO ";
		$sql_insert.= $data["table"];
		$sql_insert.= " (";
		$sql_insert.= "boton";
		$sql_insert.= ")";
		$sql_insert.= " VALUES ";
		$sql_insert.= " (";
		$sql_insert.= "'lk'";
		$sql_insert.= ")";	
		$return["test"]=$sql_insert;	
	}
*/	
	
	
	$sql_update = "UPDATE ";
	$sql_update.= $data["table"];
	$sql_update.= " SET ";
	if(array_key_exists("values", $data)){		
		$vc_n=0;
		foreach ($new_values as $key => $value) {
			if($vc_n>0){ $sql_update.= ", ";}
			$sql_update.= "".$key."";
			$sql_update.= " = ";
			if($value){
				$sql_update.= "'".$mysqli->real_escape_string($value)."'";
			}else{
				$sql_update.= "NULL"; 
			}
			$vc_n++;
		}
	}
	
	$sql_update.= " WHERE ";
	$sql_update.= " id ";
	$sql_update.= " = ";
	$sql_update.= "'";
	$sql_update.= $data["id"];
	$sql_update.= "'";
	$return["sql_update"][]=$sql_update;
	$return["item_id"]=$data["id"];
	$mysqli->query($sql_update);
	$return["data"]=$data;



	if(array_key_exists("extra", $data)){
		foreach ($data["extra"] as $key => $extra) {
			if(array_key_exists("type", $extra)){
				if($extra["type"]=="usuario_permiso"){
					//print_r($extra);
					if($extra["checked"]){
						$exists_permiso = $mysqli->query("SELECT id FROM tbl_usuario_permisos WHERE usuario_id = '".$data["id"]."' AND menu_id = '".$extra["menu"]."' AND boton = '".$extra["boton"]."'")->fetch_assoc();
						if($exists_permiso){
							$sql_update_permiso = "UPDATE tbl_usuario_permisos SET estado = '1' WHERE usuario_id = '".$data["id"]."' AND menu_id = '".$extra["menu"]."' AND boton = '".$extra["boton"]."'";
							$mysqli->query($sql_update_permiso);
							$return["sql_update"][]=$sql_update_permiso;
						}else{
							$sql_insert_permiso = "INSERT INTO tbl_usuario_permisos (usuario_id,menu_id,boton,estado) VALUES ('".$data["id"]."','".$extra["menu"]."','".$extra["boton"]."','1')";
							$mysqli->query($sql_insert_permiso);
							$return["sql_insert"][]=$sql_insert_permiso;
						}
					}else{
						$sql_delete_permiso = "DELETE FROM tbl_usuario_permisos WHERE usuario_id = '".$data["id"]."' AND menu_id = '".$extra["menu"]."' AND boton = '".$extra["boton"]."'";
						$mysqli->query($sql_delete_permiso);
						$return["sql_delete"][]=$sql_delete_permiso;
					}
				}
			}
			if(array_key_exists("extra", $extra)){
				if($extra["extra"]=="servicio"){
					$exists=$mysqli->query("SELECT id,nombre FROM tbl_local_proveedor_id WHERE local_id = '".$data["id"]."' AND servicio_id = '".$extra["servicio_id"]."'")->fetch_assoc();
					
					if($exists){						
						if($extra["val"]==$exists["nombre"]){
							$sql_update_servicio = "UPDATE tbl_local_proveedor_id SET estado = '1' WHERE local_id = '".$data["id"]."' AND servicio_id = '".$extra["servicio_id"]."'";
							$return["sql_update"][]=$sql_update_servicio;
							$mysqli->query($sql_update_servicio);
						}elseif($extra["val"]==""){
							$sql_update_servicio = "UPDATE tbl_local_proveedor_id SET estado = '0' WHERE local_id = '".$data["id"]."' AND servicio_id = '".$extra["servicio_id"]."'";
							$return["sql_update"][]=$sql_update_servicio;
							$mysqli->query($sql_update_servicio);
						}else{
							$sql_update_servicio = "UPDATE tbl_local_proveedor_id SET nombre = '".$extra["val"]."', estado = '1' WHERE local_id = '".$data["id"]."' AND servicio_id = '".$extra["servicio_id"]."'";
							$return["sql_update"][]=$sql_update_servicio;
							$mysqli->query($sql_update_servicio);
						}				
					}else{
						if($extra["val"]){
							$sql_insert_servicio = "INSERT INTO tbl_local_proveedor_id (local_id,servicio_id,nombre,estado) VALUES ('".$data["id"]."','".$extra["servicio_id"]."','".$extra["val"]."','1')";
							$return["sql_insert"][]=$sql_insert_servicio;
							$mysqli->query($sql_insert_servicio);
						}
					}
				}
			}
			if(array_key_exists("formula_id", $extra)){
				$formula = $mysqli->query("SELECT ff.id, 
												ff.participante_id, 
												ff.operador_id, 
												ff.servicio_id, 
												ff.moneda_id,
												ff.sobre_id,
												ff.tipo_id
								FROM tbl_facturacion_formulas ff
								WHERE ff.id = '".$extra["formula_id"]."'")->fetch_assoc();
								
				if(array_key_exists("detalles", $extra)){
					$sql_update_estado = "UPDATE tbl_contrato_formulas SET estado = '0' WHERE contrato_id = '".$data["id"]."' AND producto_id = '".$extra["producto_id"]."'";
					$mysqli->query($sql_update_estado);

					foreach ($extra["detalles"] as $key => $value) {
						$sql_insert = "INSERT INTO tbl_contrato_formulas (formula_id,contrato_id,producto_id,participante_id,operador_id,servicio_id,moneda_id,sobre_id,tipo_id,desde,hasta,monto,estado) 
									VALUES ('".$formula["id"]."',".value_to_db($value["contrato_id"]).",".value_to_db($value["producto_id"]).",'".$formula["participante_id"]."','".$formula["operador_id"]."','".$formula["servicio_id"]."','".$formula["moneda_id"]."','".$formula["sobre_id"]."','".$formula["tipo_id"]."',".value_to_db($value["desde"]).",".value_to_db($value["hasta"]).",".value_to_db($value["monto"]).",'1')";
						$mysqli->query($sql_insert);
						$return["sql_insert"][]=$sql_insert;
					}
				}	
			}
		}
	}
	if(array_key_exists("lp_id", $data)){
		$lp_id_local_id = $data["id"];
		foreach ($data["lp_id"] as $key => $extra) {
			//print_r($extra);
			if($new_id = strstr($extra["id"],"new_")){
				$sql_insert_lp_id = "INSERT INTO tbl_local_proveedor_id (local_id,servicio_id,canal_de_venta_id,nombre,proveedor_id) VALUES ('".$extra["local_id"]."','".$extra["servicio_id"]."','".$extra["canal_de_venta_id"]."',".value_to_db($extra["nombre"]).",".value_to_db($extra["proveedor_id"]).")";
				$return["sql_insert"][]=$sql_insert_lp_id;
				$mysqli->query($sql_insert_lp_id);
			}else{
				$sql_update_lp_id = "UPDATE tbl_local_proveedor_id SET nombre = ".value_to_db($extra["nombre"]).", proveedor_id = ".value_to_db($extra["proveedor_id"])." WHERE id = '".$extra["id"]."'";
				$mysqli->query($sql_update_lp_id);
				$return["sql_update"][]=$sql_update_lp_id;
			}
			$lp_id_local_id = $extra["local_id"];
		}


		$lpi_query = $mysqli->query("SELECT canal_de_venta_id,proveedor_id,local_id 
									FROM tbl_local_proveedor_id 
									WHERE local_id = '".$lp_id_local_id."' 
									AND estado = '1'
									");
		while ($lpi=$lpi_query->fetch_assoc()) {
			$repo_command = "SELECT at_unique_id FROM tbl_transacciones_repositorio WHERE local_id IS NULL /*AND tipo = '1'*/ AND cashdesk_id = '".$lpi["proveedor_id"]."'";
			$repo_query = $mysqli->query($repo_command);
			$lpi["repo_num"]=$repo_query->num_rows;
			$mysqli->query("START TRANSACTION");
			while($repo = $repo_query->fetch_assoc()){
				$repo_update_command = "UPDATE tbl_transacciones_repositorio SET local_id = '".$lpi["local_id"]."', canal_de_venta_id = '".$lpi["canal_de_venta_id"]."' WHERE at_unique_id = '".$repo["at_unique_id"]."'";
				$mysqli->query($repo_update_command);
				// $lpi["repo_update_command"][]=$repo_update_command;
				$deta_update_command = "UPDATE tbl_transacciones_detalle SET local_id = '".$lpi["local_id"]."', canal_de_venta_id = '".$lpi["canal_de_venta_id"]."' WHERE at_unique_id = '".$repo["at_unique_id"]."'";
				$mysqli->query($deta_update_command);
				// $lpi["deta_update_command"][]=$deta_update_command;
			}
			$mysqli->query("COMMIT");

			$repo_paid_command = "SELECT at_unique_id,_paiddate_ FROM tbl_transacciones_repositorio WHERE /*tipo = '1' AND*/ paid_cash_desk_name = '".$lpi["proveedor_id"]."'";
			$repo_paid_query = $mysqli->query($repo_paid_command);
			$lpi["repo_paid_num"]=$repo_paid_query->num_rows;
			$mysqli->query("START TRANSACTION");
			while($repo_paid = $repo_paid_query->fetch_assoc()){
				$deta_paid_update_command = "UPDATE tbl_transacciones_detalle SET paid_canal_de_venta_id = '".$lpi["canal_de_venta_id"]."', paid_local_id = '".$lpi["local_id"]."', paid_day = '".$repo_paid["_paiddate_"]."' WHERE at_unique_id = '".$repo_paid["at_unique_id"]."'";
				$mysqli->query($deta_paid_update_command);
				// $lpi["deta_paid_update_command"][]=$deta_paid_update_command;
			}
			$mysqli->query("COMMIT");
			$return["lpi"][]=$lpi;
		}
	}
}
function sort_list($data){
	global $mysqli;
	foreach ($data["list"] as $key => $value) {
		$sql_update = "UPDATE ";
		$sql_update.= $data["tabla"];
		$sql_update.= " SET ";
		$sql_update.= " orden = '".$key."'";
		$sql_update.= " WHERE ";
		$sql_update.= " id ";
		$sql_update.= " = ";
		$sql_update.= "'";
		$sql_update.= $value;
		$sql_update.= "'";
		echo $sql_update;
		echo "\n";
		$mysqli->query($sql_update);
	}
}
function switch_data($data){
	global $mysqli;
	$sql_update = "UPDATE ";
	$sql_update.= $data["table"];
	$sql_update.= " SET ";
	$sql_update.= $data["col"];
	$sql_update.= " = ";
	$sql_update.= "'";
	$sql_update.= $data["val"];
	$sql_update.= "'";
	$sql_update.= " WHERE ";
	$sql_update.= " id ";
	$sql_update.= " = ";
	$sql_update.= "'";
	$sql_update.= $data["id"];
	$sql_update.= "'";
	echo $sql_update;
	//sleep(1);
	$mysqli->query($sql_update);
}
function value_to_db($v){
	global $mysqli;
	$tmp="";
	$nulls=array("null","");
	if(in_array($v, $nulls)){
		$tmp="NULL";
	}else{
		if(is_float($v)){
			$tmp="'".$v."'";
		}elseif(is_int($v)){
			$tmp=$v;
		}else{
			$v=str_replace(",", ".", $v);
			$tmp="'".trim($mysqli->real_escape_string($v))."'";
		}
	}
	return $tmp;
}
function rec_reprocess($data){
	global $mysqli;
	global $return;
	global $login;

	$proceso_command = "SELECT fecha_inicio,fecha_fin,servicio_id,tipo,archivo_id
									FROM tbl_transacciones_procesos
									WHERE id = '".$data["id"]."'";
	$proceso = $mysqli->query($proceso_command)->fetch_assoc();
	$tra_data = array();
	$tra_data["tabla"]="tbl_transacciones_repositorio";
	$tra_data["servicio_id"]=$proceso["servicio_id"];
	$tra_data["tipo"]=$proceso["tipo"];
	//$tra_data["inicio_fecha"]=substr(strstr($proceso["fecha_inicio"], " ",true),0);
	//$tra_data["inicio_hora"]=substr(strstr($proceso["fecha_inicio"], " "),1);
	//$tra_data["fin_fecha"]=substr(strstr($proceso["fecha_fin"], " ",true),0);
	//$tra_data["fin_hora"]=substr(strstr($proceso["fecha_fin"], " "),1);
	$tra_data["file_id"]=$proceso["archivo_id"];
	$tra_data["file_dir"]="../files_bucket/";
	require('sys_transacciones.php');
	if($tra_data["servicio_id"]==2 || $tra_data["servicio_id"]==3){
		$return["import_csv_to_db"]=import_csv_to_db($tra_data);
	}else{
		//$return["transacciones_repositorio"]=transacciones_repositorio($tra_data);
	}

	$proceso_update_command = "UPDATE tbl_transacciones_procesos SET estado = '0' WHERE id = '".$data["id"]."'";
	$mysqli->query($proceso_update_command);
	
}
function rec_gen_liq($data){
	global $mysqli;
	global $return;
	require('sys_transacciones.php');
	//$return["hola"]="chau";
	//print_r($data); exit();
	transacciones_build_liquidaciones($data);	
}
function import_from_bc($data){
	global $mysqli;
	global $return;
	require('sys_transacciones.php');
	if($data["servicio_id"]==2){
		import_csv_to_db($data);
	}else{
		//transacciones_import_from_bc($data);
	}
	//sleep(5);
}

$return["memory_end"]=memory_get_usage();
$return["time_end"] = microtime(true);
$return["memory_total"]=($return["memory_end"]-$return["memory_init"]);
$return["time_total"]=($return["time_end"]-$return["time_init"]);
print_r(json_encode($return));
?>