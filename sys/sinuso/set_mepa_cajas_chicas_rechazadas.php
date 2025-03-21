<?php

date_default_timezone_set("America/Lima");

$result=array();

include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);


if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_cajas_chicas_rechazadas_listar_liquidacion")
{
	$param_fecha_desde = $_POST['param_fecha_desde'];
	$param_fecha_desde = date("Y-m-d", strtotime($param_fecha_desde));

	$param_fecha_hasta = $_POST['param_fecha_hasta'];
	$param_fecha_hasta = date("Y-m-d", strtotime($param_fecha_hasta));

	$login_usuario_id = $login?$login['id']:null;

	$suma_total = 0;
	
	// INICIO: VERIFICAR RED
	
	$where_redes = "";

	$select_red =
    "
        SELECT
            l.red_id
        FROM tbl_usuarios_locales ul
            INNER JOIN tbl_locales l
            ON ul.local_id = l.id
        WHERE ul.usuario_id = '".$login_usuario_id."'
        	AND ul.estado = 1 AND l.red_id IS NOT NULL
        GROUP BY l.red_id
    ";

    $data_select_red = $mysqli->query($select_red);

    $row_count_data_select_red = $data_select_red->num_rows;

    $ids_data_select_red = '';
    $contador_ids = 0;
    
    if ($row_count_data_select_red > 0) 
    {
        while ($row = $data_select_red->fetch_assoc()) 
        {
            if ($contador_ids > 0) 
            {
                $ids_data_select_red .= ',';
            }

            $ids_data_select_red .= $row["red_id"];           
            $contador_ids++;
        }

        $where_redes = " AND rse.red_id IN ($ids_data_select_red) ";
    }

	// FIN: VERIFICAR RED

	$query = "
		SELECT
			r.id, r.id_liquidacion,
		    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario,
		    rs.nombre AS empresa,
		    z.nombre AS zona,
		    l.num_correlativo,
		    l.total_rendicion AS total_liquidacion,
			m.monto_cierre AS total_movilidad,
		    l.created_at AS fecha_registro
		FROM mepa_caja_chica_rechazadas r
			INNER JOIN mepa_caja_chica_liquidacion l
			ON r.id_liquidacion = l.id
			INNER JOIN mepa_asignacion_caja_chica a
			ON l.asignacion_id = a.id
			INNER JOIN tbl_razon_social rs
			ON a.empresa_id = rs.id
			INNER JOIN mepa_zona_asignacion z
			ON a.zona_asignacion_id = z.id
			INNER JOIN tbl_usuarios tu
			ON l.user_created_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN tbl_razon_social rse
			ON tp.razon_social_id = rse.id
			LEFT JOIN mepa_caja_chica_movilidad m
			ON l.id_movilidad = m.id
		WHERE l.status = 1 
			".$where_redes." 
			AND DATE_FORMAT(l.created_at, '%Y-%m-%d') BETWEEN '".$param_fecha_desde."' AND '".$param_fecha_hasta."'
		GROUP BY r.id_liquidacion
	";

	$list_query = $mysqli->query($query);

	$data =  array();
	$num = 1;

	while($reg = $list_query->fetch_object()) 
	{
		$suma_total = $reg->total_liquidacion + $reg->total_movilidad;

		$data[] = array(
			"0" => $num,
			"1" => $reg->usuario,
			"2" => $reg->empresa,
			"3" => $reg->zona,
			"4" => $reg->num_correlativo,
			"5" => "S/ ".$suma_total,
			"6" => $reg->fecha_registro,
			"7" => '
					<a   
                        class="btn btn-info btn-sm"
                        onclick="mepa_cajas_rechazadas_ver_detalle('.$reg->id_liquidacion.', \''.$reg->usuario.'\', \''.$reg->empresa.'\', \''.$reg->zona.'\', '.$reg->num_correlativo.')";
                        data-toggle="tooltip" 
                        data-placement="top" 
                        title="Ver el detalle">
                        <span class="fa fa-eye"></span>
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

if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_cajas_rechazadas_ver_detalle")
{

	$id_liquidacion = $_POST["id_liquidacion"];
	
	$html = '';

	$query = "
		SELECT
			r.id, 
		    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario,
		    r.motivo,
		    r.created_at AS fecha_registro
		FROM mepa_caja_chica_rechazadas r
			INNER JOIN tbl_usuarios tu
			ON r.user_created_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
		WHERE r.id_liquidacion = '".$id_liquidacion."'
	";
	
	$query_select = $mysqli->query($query);
	$row_count = $query_select->num_rows;

	$html .= '<table class="table table-bordered table-hover dt-responsive">';
	$html .=	'<thead>';
	$html .=		'<tr>';
	$html .=			'<th>Usuario Observador</th>';
	$html .=			'<th>Motivo</th>';
	$html .=			'<th>Fecha</th>';
	$html .=		'</tr>';
	$html .=	'</thead>';
	$html .=	'<tbody>';
					
	if ($row_count > 0) 
	{
		while ($row = $query_select->fetch_assoc()) 
		{
			$html .=	'<tr>';
			$html .=		'<td>'.$row["usuario"].'</td>';
			$html .=		'<td>'.$row["motivo"].'</td>';
			$html .=		'<td>'.$row["fecha_registro"].'</td>';
			$html .=	'</tr>';
		}
	}

	$html .=	'</tbody>';
	$html .='</table>';

	if($mysqli->error)
	{
		$result["http_code"] = 400;
		$result["status"] = "Error.";
		$result["result"] = $mysqli->error;
		echo json_encode($result);
		exit;
	}
	
	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["result"] = $html;

	echo json_encode($result);
}

?>