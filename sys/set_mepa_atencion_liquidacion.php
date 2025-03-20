<?php 

date_default_timezone_set("America/Lima");

$result=array();
include("db_connect.php");
include("sys_login.php");

include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';	

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_mepa_solicitud_atencion_liquidacion_jefe_check_guardar_solo_check") 
{
	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	$error = '';

	$arreglo_data = $_POST["array_check_atencion_liquidacion_jefe_aprobar"];
	$ids_data = json_decode($arreglo_data);

	foreach($ids_data as $item)
	{

		$query_update = "
					UPDATE mepa_caja_chica_liquidacion 
						SET usuario_atencion_id_superior = '".$usuario_id."', 
						situacion_etapa_id_superior = 6,
						fecha_atencion_superior = '".date('Y-m-d H:i:s')."'
					WHERE id = '".$item->item_id."' ";

		$mysqli->query($query_update);
	}

	if($mysqli->error)
	{
		$error .= $mysqli->error;
	}


	if ($error == '') 
	{
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestión.";
		$result["error"] = $error;
	} 
	else 
	{
		$result["http_code"] = 400;
		$result["status"] = "Datos obtenidos de gestión.";
		$result["error"] = $error;
	}
}

if(isset($_POST["accion"]) && $_POST["accion"]==="sec_mepa_atencion_liquidacion_listar_liquidacion_pendiente")
{

	$usuario_id = $login?$login['id']:null;

	if((int)$usuario_id > 0)
	{
		$param_situacion = $_POST["param_situacion"];
		
		$tbody = '';

		$query = "
			SELECT
                li.id, li.asignacion_id, li.num_correlativo,
                asig.id AS id_asignacion, li.fecha_desde, li.fecha_hasta, total_rendicion as monto_liquidacion, li.solicitante_usuario_id, 
                rz.nombre AS empresa,
                za.nombre AS zona,
                concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, '')) AS usuario_solicitante,
                li.situacion_etapa_id_superior, ce.situacion AS estado_solicitud_etapa_superior,
                li.id_movilidad, li.se_aplica_movilidad, mov.monto_cierre, li.created_at AS fecha_solicitud,
                uada.usuario_id AS usuario_aprobador_id
            FROM mepa_caja_chica_liquidacion li
                INNER JOIN mepa_asignacion_caja_chica asig
                ON li.asignacion_id = asig.id
                INNER JOIN tbl_razon_social rz
                ON asig.empresa_id = rz.id
                INNER JOIN mepa_zona_asignacion za
                ON asig.zona_asignacion_id = za.id
                INNER JOIN tbl_usuarios tu
                ON li.solicitante_usuario_id = tu.id
                INNER JOIN tbl_personal_apt tp
                ON tu.personal_id = tp.id
                INNER JOIN cont_etapa ce
                ON li.situacion_etapa_id_superior = ce.etapa_id
                LEFT JOIN mepa_caja_chica_movilidad mov
                ON li.id_movilidad = mov.id
                INNER JOIN mepa_usuario_asignacion_detalle uad
				ON asig.usuario_asignado_id = uad.usuario_id
			    AND uad.mepa_asignacion_rol_id = 3 AND uad.status = 1
			    INNER JOIN mepa_usuario_asignacion ua
				ON uad.mepa_usuario_asignacion_id = ua.id
				INNER JOIN mepa_usuario_asignacion_detalle uada
				ON ua.id = uada.mepa_usuario_asignacion_id 
			    AND uada.mepa_asignacion_rol_id = 2 AND uada.status = 1
            WHERE uada.usuario_id = '".$usuario_id."' 
            	AND li.situacion_etapa_id_superior = 1
            ORDER BY concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, '')) ASC, li.num_correlativo ASC
		";

		$list_query = $mysqli->query($query);

		if($mysqli->error)
		{
			$result["http_code"] = 400;
			$result["result"] = 'Ocurrio un error al consultar: ' . $mysqli->error . $query;
			echo json_encode($result);
			exit();
		}

		$row_count = $list_query->num_rows;

		if($row_count > 0) 
		{
			$num = 1;

			while ($row = $list_query->fetch_assoc()) 
			{
				$tbody .= '<tr>';
					$tbody .= '<td class="text-center">' . $num . '</td>';
					$tbody .= '<td class="text-center">' . $row["id"] . '</td>';
					$tbody .= '<td class="text-center">' . $row["empresa"] . '</td>';
					$tbody .= '<td class="text-center">' . $row["zona"] . '</td>';
					$tbody .= '<td class="text-center">' . $row["usuario_solicitante"] . '</td>';
					$tbody .= '<td class="text-center">' . $row["num_correlativo"] . '</td>';
					$tbody .= '<td class="text-center">' . $row["estado_solicitud_etapa_superior"] . '</td>';
					$tbody .= '<td class="text-center">' . $row["fecha_solicitud"] . '</td>';
					$tbody .= '<td class="text-center">';
						$tbody .= '<a class="btn btn-rounded btn-info btn-sm" data-toggle="tooltip" data-placement="top" title="Ver Detalle" href="./?sec_id=mepa&amp;sub_sec_id=detalle_atencion_liquidacion&id='.$row["id"].'">';
						$tbody .= '<span class="fa fa-eye"></span>';
						$tbody .= '</a>';
					$tbody .= '</td>';
					$tbody .= '<td class="text-center">';
						$tbody .= '<input type="checkbox" value="'.$row["id"].'" name="check_atencion_liquidacion_jefe_'.$num.'" id="check_atencion_liquidacion_jefe_'.$num.'" style="width: 33%; height: 30px; padding-bottom: 0px; margin-bottom: 0px; vertical-align: middle;">';
					$tbody .= '</td>';
				$tbody .= '</tr>';

				$num += 1;
			}
		}
		else
		{
			$tbody .= '<tr>';
				$tbody .= '<td colspan="10" style="text-align: center;">No existen registros</td>';
			$tbody .= '</tr>';
		}

		if ($row_count >= 0) 
		{
			$result["http_code"] = 200;
			$result["status"] = "Datos obtenidos de gestion.";
			$result["result"] = $tbody;
			$result["total_registro"] = $row_count;
		} 
		else 
		{
			$result["http_code"] = 400;
			$result["result"] = "No existen registros";
			$result["total_registro"] = $row_count;
		}

		echo json_encode($result);
		exit();
	}
	else
	{
		$result["http_code"] = 400;
        $result["result"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";

        echo json_encode($result);
		exit();
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_mepa_atencion_liquidacion_listar_liquidacion_diferente_pendiente")
{
	$usuario_id = $login?$login['id']:null;

	$param_situacion = $_POST["param_situacion"];

	$where_situacion = "";

	if($param_situacion == "_00")
	{
		$where_situacion = " AND li.solicitar_eliminar_liquidacion = 1 AND li.situacion_etapa_id_superior != 13 ";
	}
	else if($param_situacion == 12)
	{
		$where_situacion = " AND li.situacion_etapa_id_superior = 6 AND li.situacion_jefe_cerrar_caja_chica = '".$param_situacion."' ";
	}
	else
	{
		$where_situacion = " AND li.situacion_etapa_id_superior = '".$param_situacion."' ";
	}

	$query_select = "
		SELECT
            li.id, li.asignacion_id, li.num_correlativo,
            asig.id AS id_asignacion, li.fecha_desde, li.fecha_hasta, total_rendicion as monto_liquidacion, li.solicitante_usuario_id, 
            rz.nombre AS empresa,
            za.nombre AS zona,
            concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, '')) AS usuario_solicitante,
            li.situacion_etapa_id_superior, ce.situacion AS estado_solicitud_etapa_superior,
            li.id_movilidad, li.se_aplica_movilidad, mov.monto_cierre, li.created_at AS fecha_solicitud
        FROM mepa_caja_chica_liquidacion li
            INNER JOIN mepa_asignacion_caja_chica asig
            ON li.asignacion_id = asig.id
            INNER JOIN tbl_razon_social rz
            ON asig.empresa_id = rz.id
            INNER JOIN mepa_zona_asignacion za
            ON asig.zona_asignacion_id = za.id
            INNER JOIN tbl_usuarios tu
            ON li.solicitante_usuario_id = tu.id
            INNER JOIN tbl_personal_apt tp
            ON tu.personal_id = tp.id
            INNER JOIN cont_etapa ce
            ON li.situacion_etapa_id_superior = ce.etapa_id
            LEFT JOIN mepa_caja_chica_movilidad mov
            ON li.id_movilidad = mov.id
        WHERE li.usuario_atencion_id_superior = '".$usuario_id."' 
        	".$where_situacion."
        ORDER BY li.id DESC
	";

	$list_query = $mysqli->query($query_select);

	//$li = $list_query->fetch_assoc();

	$data =  array();
	$cont = 1;

	while($reg = $list_query->fetch_object()) 
	{
		$data[] = array(
			"0" => $cont,
			"1" => $reg->id,
			"2" => $reg->empresa,
			"3" => $reg->zona,
			"4" => $reg->usuario_solicitante,
			"5" => $reg->num_correlativo,
			"6" => $reg->estado_solicitud_etapa_superior,
			"7" => $reg->fecha_solicitud,
			"8" => '<a class="btn btn-rounded btn-info btn-sm"
                        href="./?sec_id=mepa&amp;sub_sec_id=detalle_atencion_liquidacion&id='.$reg->id.'"
                        data-toggle="tooltip" data-placement="top" title="Ver Detalle">
                        <span class="fa fa-eye"></span>
                    </a>
			        '
		);

		$cont++;
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

echo json_encode($result);

?>