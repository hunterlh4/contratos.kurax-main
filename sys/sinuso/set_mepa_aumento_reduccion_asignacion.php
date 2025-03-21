<?php

date_default_timezone_set("America/Lima");

$result=array();

include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);


if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_aumento_asignacion_listar_asignacion")
{
	$usuario_id = $login?$login['id']:null;

	$param_usuario = $_POST['param_usuario'];
	
	$where_usuario_asignado = "";

	if($param_usuario != 0)
	{
		$where_usuario_asignado = " AND a.usuario_asignado_id = '".$param_usuario."' ";
	}

	$query = 
	"
		SELECT
			a.id, a.usuario_asignado_id,
		    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario,
		    a.empresa_id,
		    rs.nombre AS empresa,
		    a.zona_asignacion_id,
		    z.nombre AS zona,
		    a.fondo_asignado
		FROM mepa_asignacion_caja_chica a
			INNER JOIN tbl_usuarios tu
			ON a.usuario_asignado_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN mepa_usuario_asignacion_detalle uad
            ON a.usuario_asignado_id = uad.usuario_id 
            AND uad.mepa_asignacion_rol_id = 3 AND uad.status = 1
            INNER JOIN mepa_usuario_asignacion ua
            ON uad.mepa_usuario_asignacion_id = ua.id
            INNER JOIN mepa_usuario_asignacion_detalle uada
            ON ua.id = uada.mepa_usuario_asignacion_id 
            AND uada.mepa_asignacion_rol_id = 2 AND uada.status = 1
			INNER JOIN tbl_razon_social rs
			ON a.empresa_id = rs.id
			INNER JOIN mepa_zona_asignacion z
			ON a.zona_asignacion_id = z.id
		WHERE uada.usuario_id = '".$usuario_id."' 
			AND a.situacion_etapa_id = 6 AND a.status = 1
			".$where_usuario_asignado."
		ORDER BY 
			concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) ASC,
			rs.nombre ASC, 
			z.nombre ASC
	";

	$list_query = $mysqli->query($query);

	$data =  array();
	$num = 1;

	while($reg = $list_query->fetch_object()) 
	{
		$data[] = array(
			"0" => $num,
			"1" => $reg->usuario,
			"2" => $reg->empresa,
			"3" => $reg->zona,
			"4" => "S/ ".$reg->fondo_asignado,
			"5" => '
					<a   
                        class="btn btn-success btn-xs"
                        onclick="mepa_aumento_asignacion_nueva_solicitud('.$reg->id.', \''.$reg->usuario.'\', \''.$reg->empresa.'\', \''.$reg->zona.'\', '.$reg->fondo_asignado.')";
                        data-toggle="tooltip" 
                        data-placement="top" 
                        title="Acceder a la Solicitud">
                        <span class="fa fa-check"></span>
                        Acceder
                    </a>'
		);

		$num++;
	}

	$resultado = array(
		"sEcho" => 1,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);

	echo json_encode($resultado);
}

if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_aumento_asignacion_guardar_nueva_solicitud") 
{
	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	$error = '';
	
	$param_id_asignacion_nueva_solictud = $_POST["param_id_asignacion_nueva_solictud"];
	$param_txt_tipo_solicitud = $_POST["param_txt_tipo_solicitud"];
	
	$param_txt_monto = $_POST["param_txt_monto"];
	$param_txt_monto = str_replace(",","",$param_txt_monto);
	
	$param_txt_motivo = $_POST["param_txt_motivo"];

	$sel_aumento_pendiente = $mysqli->query("
						SELECT
							id, monto, situacion_etapa_id, situacion_tesoreria_etapa_id
						FROM mepa_aumento_asignacion
						WHERE situacion_etapa_id = 1 AND situacion_tesoreria_etapa_id = 10 AND user_created_id = '".$usuario_id."' AND asignacion_id = '".$param_id_asignacion_nueva_solictud."'
						");

	$cant_sel_aumento_pendiente = $sel_aumento_pendiente->num_rows;

	if($cant_sel_aumento_pendiente == 0)
	{
		$sel_aumento_pendiente_pago = $mysqli->query("
						SELECT
							id, monto, situacion_etapa_id, situacion_tesoreria_etapa_id
						FROM mepa_aumento_asignacion
						WHERE situacion_etapa_id = 6 AND situacion_tesoreria_etapa_id = 10 AND user_created_id = '".$usuario_id."' AND asignacion_id = '".$param_id_asignacion_nueva_solictud."'
						");

		$cant_sel_aumento_pendiente_pago = $sel_aumento_pendiente_pago->num_rows;

		if($cant_sel_aumento_pendiente_pago == 0)
		{
			$query_insert = "INSERT INTO mepa_aumento_asignacion
							(
								asignacion_id,
								tipo_solicitud_id,
								monto,
								motivo,
								situacion_etapa_id,
								situacion_tesoreria_etapa_id,
								status,
								user_created_id,
								created_at,
								user_updated_id,
								updated_at
							)
							VALUES
							(
								'".$param_id_asignacion_nueva_solictud."',
								'".$param_txt_tipo_solicitud."',
								'".$param_txt_monto."',
								'".$param_txt_motivo."',
								1,
								10,
								1,
								'".$login["id"]."', 
								'".date('Y-m-d H:i:s')."',
								'".$login["id"]."', 
								'".date('Y-m-d H:i:s')."'
							)
							";
		}
		else
		{
			$result["http_code"] = 300;
			$result["status"] = "Registro no Permitido.";
			$result["texto"] = "Existe una solicitud pendiente por pagar.";

			echo json_encode($result);
			exit;
		}
	}
	else
	{
		$result["http_code"] = 300;
		$result["status"] = "Registro no Permitido.";
		$result["texto"] = "Existe una solicitud en curso.";

		echo json_encode($result);
		exit;
	}
	
	
	$mysqli->query($query_insert);

	$aumento_asignacion_id = mysqli_insert_id($mysqli);

	
	if($mysqli->error)
	{
		$error = $mysqli->error;
	}

	if ($error == '') 
	{
		$result["http_code"] = 200;
		$result["status"] = "Solicitud Registrada.";
		$result["texto"] = "La solicitud fue Registrada exitosamente.";
		$result["error"] = $error;
		
	}
	else 
	{
		$result["http_code"] = 400;
		$result["status"] = "Error.";
		$result["texto"] = "La solicitud fue error.";
		$result["error"] = $error;
	}

	echo json_encode($result);
}

?>