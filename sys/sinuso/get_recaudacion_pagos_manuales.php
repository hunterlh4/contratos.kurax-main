<?php
include("db_connect.php");

if(isset($_POST["get_pago_manual"])){
	$data=$_POST["get_pago_manual"];
	// print_r($data);
	$pm_command = "SELECT
					pm.at_unique_id,
					pm.tipo_id,
					pm.canal_de_venta_id,
					pm.monto,
					DATE_FORMAT(pm.fecha_pago,'%Y-%m-%d') AS fecha,
					DATE_FORMAT(pm.fecha_pago,'%d-%m-%Y') AS fecha_pago_datepicker,
					pm.local_id,
					pm.motivo_id,
					pm.autorizacion_id,
					pm.referencia,
					pm.descripcion
					FROM tbl_pago_manual pm
					WHERE pm.id = '".$data["id"]."'";
	$pm_query = $mysqli->query($pm_command);
	if($mysqli->error){
		print_r($mysqli->error);
		exit();
	}
	$pm = $pm_query->fetch_assoc();
	print_r(json_encode($pm));
	// print_r($pm);
}
?>
