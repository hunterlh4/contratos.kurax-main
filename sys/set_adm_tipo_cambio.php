<?php
$return = array();
$return["memory_init"]=memory_get_usage();
$return["time_init"] = microtime(true);
include("global_config.php");
include("db_connect.php");
include("sys_login.php");

if(isset($_POST["sec_adm_tipo_cambio_save"])){
	$data = $_POST["sec_adm_tipo_cambio_save"];

	$fecha = $data["fecha"];
	$moneda_id = $data["moneda_id"];
	$monto_venta = $data["monto_venta"];
	$monto_compra = $data["monto_compra"];
	$id = $data["id"];
	if($monto_venta =="" || $monto_compra ==""){
		$return["error"] = true;
		$return["error_msg"] = "Ingrese Montos";
		print_r(json_encode($return));
		return;
	}

	if($data["id"]=="new"){
		$exists = $mysqli->query("SELECT id from tbl_tipo_cambio WHERE  moneda_id = '$moneda_id' AND fecha = '$fecha'")->fetch_assoc();
		if($exists){
			$return["error"]=true;
			$return["error_msg"]= "Ya existe un tipo de cambio para ".$fecha;
		}
		else
		{
			$insert_command = "INSERT INTO tbl_tipo_cambio (
										moneda_id ,
										fecha, 
										monto_venta,
										monto_compra,
										created_at,
										user_id ,
										updated_at ,
										user_update_id
									)
								VALUES ('".$moneda_id."',
									  '".$fecha."',
									  '".$monto_venta."',
									  '".$monto_compra."',
									   now(),
									   '".$login["id"]."',
									   now(),
									   '".$login["id"]."'
									)";
			$mysqli->query($insert_command);

			$id_tipo_cambio = $mysqli->insert_id;

			$insert_command = "INSERT INTO tbl_tipo_cambio_historial 
									   (tipo_cambio_id , 
										moneda_id,
										monto_venta,
										monto_compra,
										created_at,
										user_id)
								VALUES ('".$id_tipo_cambio."',
									  	'".$moneda_id."',
										'".$monto_venta."',
										'".$monto_compra."',
									   	now(),
									   	'".$login["id"]."'
									   )";
			$mysqli->query($insert_command);
			if($mysqli->error){
				print_r($mysqli->error);
				echo $insert_command;
				exit();
			}
			$return["id"] = $id_tipo_cambio; 
			$return["mensaje"]= "Tipo de Cambio :  $fecha - Insertado";
		}

	}
	else
	{
		$query_existe = "SELECT id from tbl_tipo_cambio WHERE  moneda_id = '$moneda_id' AND fecha = '$fecha' AND id !=".$id;
		$exists = $mysqli->query($query_existe)->fetch_assoc();
		if($exists){
			$return["error"]=true;
			$return["error_msg"]= "Ya existe un tipo de cambio para ".$fecha;
			print_r(json_encode($return));
			return;
		}
		else
		{
			$udpate_command = "UPDATE tbl_tipo_cambio SET 
					moneda_id = '".$moneda_id."'
					,fecha = '".$fecha."'
					,monto_venta = '".$monto_venta."'
					,monto_compra = '".$monto_compra."'
					,updated_at=now()
					,user_update_id = '".$login["id"]."'
					WHERE id = '".$data["id"]."'";
			$mysqli->query($udpate_command);

			$id_tipo_cambio = $data["id"];
			$insert_command = "INSERT INTO tbl_tipo_cambio_historial 
									   (tipo_cambio_id , 
										moneda_id,
										monto_venta,
										monto_compra,
										created_at,
										user_id)
								VALUES ('".$id_tipo_cambio."',
									  	'".$moneda_id."',
										'".$monto_venta."',
										'".$monto_compra."',
									   	now(),
									   	'".$login["id"]."'
									   )";
			$mysqli->query($insert_command);

			$return["mensaje"]= "Tipo de Cambio ".$data["id"]." Actualizado";
		}
	}
}


if(isset($_POST["sec_tipo_cambio_historial_list"])){
	$data=$_POST["sec_tipo_cambio_historial_list"];
	
	$comando_select = "
		SELECT
		m.nombre as moneda_nombre
		,tch.monto_venta
		,tch.monto_compra
		, tch.created_at 
		, u.usuario 
		FROM tbl_tipo_cambio_historial tch
		LEFT JOIN tbl_usuarios u on u.id = tch.user_id 
		LEFT JOIN tbl_moneda m on m.id = tch.moneda_id
		WHERE tch.tipo_cambio_id = ".$_POST['id']."
		ORDER BY tch.created_at DESC
	";
	$query = $mysqli->query($comando_select);
	$lista=[];
	while($d=$query->fetch_assoc()){
		$lista[]=$d;
	}
	$return["lista"]=$lista;
	$return["mensaje"]="lista realizada correctamente";
}


if(isset($_POST["sec_tipo_cambio_list"])){
	$data=$_POST["sec_tipo_cambio_list"];
	
	$comando_select = "
		SELECT 
		tc.id, 
		m.nombre as moneda_nombre,
		tc.fecha,
	    month(tc.fecha) AS mes,
        year(tc.fecha) AS year,
        tc.monto_venta,
		tc.monto_compra,
		tc.updated_at,
		u.usuario as usuario_updated
		FROM tbl_tipo_cambio tc
		LEFT JOIN tbl_moneda m on m.id = tc.moneda_id
		LEFT JOIN tbl_usuarios u on u.id = tc.user_update_id
		ORDER BY tc.id DESC
	";
	$query = $mysqli->query($comando_select);
	$lista=[];
	while($d=$query->fetch_assoc()){
		$lista[]=$d;
	}

	$commando_select_meses = "
	SELECT *from tbl_meses";
	$query = $mysqli->query($commando_select_meses);
	$lista_meses = [];
	while ($d = $query->fetch_assoc()) {
		$lista_meses[] = $d;
	}
	$return["lista"]=$lista;
	$return["lista_meses"] = $lista_meses;

	$return["mensaje"]="lista realizada correctamente";
}



$return["memory_end"]=memory_get_usage();
$return["time_end"] = microtime(true);
$return["memory_total"]=($return["memory_end"]-$return["memory_init"]);
$return["time_total"]=($return["time_end"]-$return["time_init"]);
print_r(json_encode($return));
?>