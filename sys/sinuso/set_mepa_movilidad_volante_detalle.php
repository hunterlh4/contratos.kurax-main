<?php

date_default_timezone_set("America/Lima");

$result=array();
include("db_connect.php");
include("sys_login.php");

include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';	

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_mepa_solicitud_movilidad_volante_listar")
{
	$param_id_caja_chica_movilidad = $_POST['param_id_caja_chica_movilidad'];
	
	$query = 
	"
		SELECT
			id, id_mepa_caja_chica_movilidad, fecha, partida_destino, centro_costo,
			motivo_traslado, monto
		FROM mepa_caja_chica_movilidad_detalle
		WHERE estado = 1 AND id_mepa_caja_chica_movilidad = '".$param_id_caja_chica_movilidad."'
		ORDER BY fecha
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
		$data[] = array(
			"0" => $reg->fecha,
			"1" => $reg->partida_destino,
			"2" => $reg->centro_costo,
			"3" => $reg->motivo_traslado,
			"4" => $reg->monto,
			"5" => '
					<a onclick="sec_mepa_movilidad_volante_eliminar_detalle('.$reg->id.', '.$reg->id_mepa_caja_chica_movilidad.')"; 
						class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top">
						<span class="fa fa-trash"></span> 
					</a>
					'
		);
	}

	$resultado = array(
		"sEcho" => 1,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);

	echo json_encode($resultado);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_mepa_movilidad_volante_detalle_listar_centro_costo") 
{

	$tipo_ceco = $_POST["tipo_ceco"];

	if($tipo_ceco == 3)
	{
		$query_select = 
		"
			SELECT
				id, cc_id AS ceco, nombre, estado
			FROM tbl_locales
			WHERE nombre IS NOT NULL
			ORDER BY nombre ASC
		";
	}
	else
	{
		$query_select = 
		"
			SELECT
				id, centro_costo AS ceco, nombre, status AS estado
			FROM mepa_zona_asignacion
			ORDER BY nombre ASC
		";
	}

	$list_query = $mysqli->query($query_select);
	$list = array();
	
	while ($li = $list_query->fetch_assoc()) 
	{
		$list[] = $li;
	}

	if($mysqli->error)
	{
		$result["consulta_error"] = $mysqli->error;
	}
	
	if(count($list) == 0)
	{
		$result["http_code"] = 400;
		$result["result"] = "No se encontro resultados.";
	} 
	elseif (count($list) > 0) 
	{
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $list;
	} 
	else 
	{
		$result["http_code"] = 400;
		$result["result"] = "No existen registros.";
	}

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_mepa_guardar_solicitud_movilidad_volante") 
{
	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	$error = '';

	$id_caja_chica_movilidad = $_POST["id_caja_chica_movilidad"];
	
	$param_fecha = $_POST['param_fecha'];
	$param_fecha = date("Y-m-d", strtotime($param_fecha));

	$param_destino = $_POST["param_destino"];
	$param_tipo_centro_costo = $_POST["param_tipo_centro_costo"];
	$param_centro_costo = $_POST["param_centro_costo"];
	$param_motivo = $_POST["param_motivo"];

	$param_monto = $_POST["param_monto"];
	$param_monto = str_replace(",","",$param_monto);
	
	$query_insert = 
	"
		INSERT INTO mepa_caja_chica_movilidad_detalle
		(
			id_mepa_caja_chica_movilidad,
			fecha,
			partida_destino,
			tipo_centro_costo,
			centro_costo,
			motivo_traslado,
			monto,
			estado,							
			created_at
		) 
		VALUES 
		(
			'".$id_caja_chica_movilidad."',
			'".$param_fecha."',
			'".$param_destino."',
			'".$param_tipo_centro_costo."',
			'".$param_centro_costo."',
			'".$param_motivo."',
			'".$param_monto."',
			1,
			'".date('Y-m-d H:i:s')."'
		)
	";

	$mysqli->query($query_insert);

	if($mysqli->error)
	{
		$error = $mysqli->error;
	}

	if ($error == '') 
	{
		$result["http_code"] = 200;
		$result["status"] = "Datos guardados correctamente.";
		$result["error"] = $error;
	} 
	else 
	{
		$result["http_code"] = 400;
		$result["status"] = "Error.";
		$result["error"] = $error;
	}

}

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_mepa_movilidad_volante_eliminar_detalle") 
{
	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	$error = '';

	$id_detalle_movilidad = $_POST["id_detalle_movilidad"];
	$id_movilidad = $_POST["id_movilidad"];

	$query_select = "
		SELECT mccm.id,
			mccm.num_correlativo,
			mccm.fecha_del,
			mccm.fecha_al,
			mccm.status,
			mccm.user_created_id,
			mccm.created_at,
			mccm.updated_at
		FROM mepa_caja_chica_movilidad AS mccm
		WHERE mccm.id = {$id_movilidad} AND mccm.status = 2
	";

	$rows_query_select = $mysqli->query($query_select);

    $cant_registros = $rows_query_select->num_rows;
	
	if($cant_registros > 0)
	{
		$result["http_code"] = 400;
		$result["message"] = "Registro de movilidad Ya cerrado";
		$result["error"] = $error;

		echo json_encode($result);
		exit();
	}
	else
	{
		$query_update = "UPDATE mepa_caja_chica_movilidad_detalle
						SET				
							estado = 0,
							updated_at = now()
						WHERE id = {$id_detalle_movilidad}
						";
		$mysqli->query($query_update);
	}
	

	if($mysqli->error)
	{
		$error = $mysqli->error;
	}

	if ($error == '') 
	{
		$result["http_code"] = 200;
		$result["message"] = "Registro eliminado.";
		$result["error"] = $error;
	} 
	else 
	{
		$result["http_code"] = 400;
		$result["message"] = "Error.";
		$result["error"] = $error;
	}

}

echo json_encode($result);

?>