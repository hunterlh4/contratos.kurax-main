<?php 
include("db_connect.php");
include("sys_login.php");

if ($_POST["where"]=="sec_excel_depositohermeticasesugerencias") {

	$locales_arr = array();
	$locales_command  =  "SELECT l.id, l.cc_id, l.nombre FROM tbl_locales l";
	$locales_command .= " WHERE l.red_id = '1' OR l.id = '200'";
	$locales_command .= " ORDER BY l.nombre ASC";
	$locales_query = $mysqli->query($locales_command);
	if($mysqli->error){
		print_r($mysqli->error);
		echo "\n";
		echo $locales_command;
		exit();
	}

	while($l = $locales_query->fetch_assoc()){
		array_push($locales_arr,$l["cc_id"]);
	}

	$result_final = array();
	$sql_ = "SELECT id,fecha_inicio,monto,id_transaccion , nro_operacion
		FROM tbl_transacciones_hermeticase 
		WHERE DATE(fecha_inicio) = '".$_POST['fecha']."'
		AND monto = ".str_replace(",", "" , $_POST['total'])." 
		and caja_id IS null
		AND nro_operacion IS NOT NULL
		AND local_id = " .$_POST["local_id"];

	$result_ = $mysqli->query($sql_);
	$i = 0;
	if($result_->num_rows > 0){
		while($row_ = $result_->fetch_assoc()) {
			$sql_mov = "SELECT thm.nro_doc , thm.importe , thm.concepto,thm.fecha_operacion
				FROM tbl_transacciones_hermeticase_movimientos thm
				WHERE thm.importe > 0
				AND TRIM(LEADING '0' FROM thm.nro_doc) = " . $row_["nro_operacion"] ;
			$query_mov = $mysqli->query($sql_mov);

			if($query_mov ->num_rows == 0)
			{
				continue;
			}

			$result_final[$i] = $row_;
			$i ++;
		} 
	}
	else{
		$date = $_POST['fecha'];
		$newdate = strtotime ('+2 day' , strtotime($date));
		$newdate2 = strtotime ('-2 day' , strtotime($date));
		$newdate = date('Y-m-d', $newdate);
		$newdate2 = date('Y-m-d', $newdate2);

		$sql_ = "SELECT id,fecha_inicio,monto ,id_transaccion , nro_operacion
		FROM tbl_transacciones_hermeticase 
		WHERE monto > 0 
		/*AND fecha_inicio >= '$newdate2'
		AND fecha_inicio <= '$newdate'*/
		AND DATE(fecha_inicio) = '" . $date . "'
		AND  caja_id is NULL
		AND local_id = {$_POST['local_id']}
		ORDER BY monto ASC";
		$result_2 = $mysqli->query($sql_);
		$i=0;
		while($row_2 = $result_2->fetch_assoc()) {

			$sql_mov = "SELECT thm.nro_doc , thm.importe , thm.concepto,thm.fecha_operacion
				FROM tbl_transacciones_hermeticase_movimientos thm
				WHERE thm.importe > 0
				AND  TRIM(LEADING '0' FROM thm.nro_doc)= " . $row_2["nro_operacion"];
			$query_mov = $mysqli->query($sql_mov);

			if(isset($query_mov->num_rows) && $query_mov->num_rows == 0)
			{
				continue;
			}
			$result_final[$i] = $row_2;
			$i ++;
		} 
	}
	echo json_encode($result_final);
}

if ($_POST["where"]=="sec_excel_depositosnovinculado") {
	$locales_arr = array();
	$locales_command = "SELECT l.id, l.cc_id, l.nombre FROM tbl_locales l";
	$locales_command.=" WHERE l.red_id = '1' OR l.id = '200'";
	$locales_command.=" ORDER BY l.nombre ASC";
	$locales_query = $mysqli->query($locales_command);
	if($mysqli->error){
		print_r($mysqli->error);
		echo "\n";
		echo $locales_command;
		exit();
	}

	while($l=$locales_query->fetch_assoc()){
		array_push($locales_arr,$l["cc_id"]);
	}

	$result_final=array();

	$date = $_POST['fecha'];
	$newdate = strtotime ('+2 day' , strtotime($date));
	$newdate = date('Y-m-d', $newdate);

	$sql_ = "SELECT id,fecha_inicio,monto,cod,id_transaccion,caja_id 
				FROM tbl_transacciones_hermeticase 
				WHERE (fecha_inicio >= '".$_POST['fecha']."' and fecha_inicio <= '".$newdate."')  and monto > 0 and caja_id is null";

	$result_ = $mysqli->query($sql_);
	$i=0;
	if($result_->num_rows>0){
		while($row_ = $result_->fetch_assoc()) {
			$result_final["sugerencia"][$i]=$row_["id"]."@".$row_["fecha_inicio"]."@".$row_["monto"]."@".trim($row_["id_transaccion"])."@".$row_["caja_id"]."@0";
			$i++;
		} 
	}
	else{

		$date = $_POST['fecha'];
		$newdate = strtotime ('+2 day' , strtotime($date));
		$newdate = date('Y-m-d', $newdate);

		$sql_ = "SELECT id,fecha_inicio,monto,cod,id_transaccion,caja_id FROM tbl_transacciones_hermeticase WHERE (fecha_inicio >= '".$_POST['fecha']."' and fecha_inicio <= '".$newdate."') and monto > 0 and caja_id is null";	

		$result_2 = $mysqli->query($sql_);
		$i = 0;
		while($row_2 = $result_2->fetch_assoc()) {
			$reference = $row_2["referencia"];
			$word_array = explode(' ', $reference);
			$encontrar = substr($word_array[0], -4);
			if(!in_array($encontrar, $locales_arr))
			{
				$result_final["sugerencia"][$i]=$row_2["id"]."@".$row_2["fecha_inicio"]."@".$row_2["monto"]."@".trim($row_2["id_transaccion"])."@".$row_2["caja_id"]."@1";
				$i++;
			}	
		} 
	}

	echo json_encode($result_final);
}

if ($_POST["where"] == "sec_relacionar") {
	$update_estado_data = "UPDATE tbl_transacciones_hermeticase SET caja_id='".$_POST['idcaja']."' , tipo='".$_POST['tipo']."' WHERE id = '".$_POST['idexcel']."'";
	$mysqli->query($update_estado_data);
	/*
	$sql_caja_deposito = "SELECT id,caja_id FROM tbl_caja_depositos WHERE caja_id = '".$_POST['idcaja']."'";	
	$result_caja_deposito = $mysqli->query($sql_caja_deposito);
	
	if($result_caja_deposito->num_rows == 0)
		$query_tbl_deposito = "INSERT INTO tbl_caja_depositos (caja_id,validar_registro) VALUES ('".$_POST['idcaja']."',1)";
	else 
		$query_tbl_deposito = "UPDATE tbl_caja_depositos SET validar_registro = 1 WHERE caja_id =".$_POST['idcaja'];
	*/
	$mysqli->query($query_tbl_deposito);
	if($mysqli->error){
		print_r($mysqli->error);
		echo "\n";
		echo $query_tbl_deposito;
		exit();
	}
	echo 1;
}

if ($_POST["where"]=="sec_quitarDeposito") {
	$update_estado_data = "UPDATE tbl_transacciones_hermeticase SET caja_id = null , tipo = null WHERE id =  '".$_POST['idexcel']."'";
	$mysqli->query($update_estado_data);
	/*$update_estado_data = "UPDATE tbl_caja_depositos SET validar_registro = 0 WHERE caja_id ='".$_POST['caja_id']."'";
	$mysqli->query($update_estado_data);*/
	echo 1;
}
?>