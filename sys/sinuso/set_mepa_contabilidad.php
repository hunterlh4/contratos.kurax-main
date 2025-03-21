<?php

date_default_timezone_set("America/Lima");

$result=array();

include("db_connect.php");
include("sys_login.php");


if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_contabilidad_listar_asignacion")
{
	$login_usuario_id = $login?$login['id']:null;

	$mepa_contabilidad_param_asignacion_fecha_desde = $_POST['mepa_contabilidad_param_asignacion_fecha_desde'];
	$mepa_contabilidad_param_asignacion_fecha_desde = date("Y-m-d", strtotime($mepa_contabilidad_param_asignacion_fecha_desde));

	$mepa_contabilidad_param_asignacion_fecha_hasta = $_POST['mepa_contabilidad_param_asignacion_fecha_hasta'];
	$mepa_contabilidad_param_asignacion_fecha_hasta = date("Y-m-d", strtotime($mepa_contabilidad_param_asignacion_fecha_hasta));


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

        $where_redes = " AND rs.red_id IN ($ids_data_select_red) ";
    }

	// FIN: VERIFICAR RED

	$query = "
		SELECT
			a.id, a.tipo_solicitud_id, ts.nombre AS tipo_solicitud_nombre,
		    a.usuario_asignado_id, 
		    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, '')) AS usuario_asignado,
		    a.usuario_atencion_id, 
		    concat(IFNULL(tpa.nombre, ''),' ', IFNULL(tpa.apellido_paterno, '')) AS usuario_atencion,
		    a.user_created_id, 
		    concat(IFNULL(tps.nombre, ''),' ', IFNULL(tps.apellido_paterno, '')) AS usuario_solicitante,
		    a.fondo_asignado, a.created_at AS fecha_solicitud, a.motivo
		FROM mepa_asignacion_caja_chica a
			INNER JOIN mepa_tipos_solicitud ts
			ON a.tipo_solicitud_id = ts.id
			INNER JOIN tbl_usuarios tu
			ON a.usuario_asignado_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN tbl_usuarios tua
			ON a.usuario_atencion_id = tua.id
			INNER JOIN tbl_razon_social rs
			ON tp.razon_social_id = rs.id
			INNER JOIN tbl_personal_apt tpa
			ON tua.personal_id = tpa.id
			INNER JOIN tbl_usuarios tus
			ON a.user_created_id = tus.id
			INNER JOIN tbl_personal_apt tps
			ON tus.personal_id = tps.id
		WHERE a.status = 1 AND a.situacion_etapa_id = 6 
			".$where_redes."
			AND DATE_FORMAT(a.created_at, '%Y-%m-%d') BETWEEN '".$mepa_contabilidad_param_asignacion_fecha_desde."' AND '".$mepa_contabilidad_param_asignacion_fecha_hasta."'
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->usuario_solicitante,
			"2" => $reg->usuario_atencion,
			"3" => $reg->usuario_asignado,
			"4" => "S/ ".$reg->fondo_asignado,
			"5" => $reg->motivo,
			"6" => $reg->fecha_solicitud
		);
	}

	$resultado = array(
		"sEcho" => 1,
		"iTotalREcords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	);

	echo json_encode($resultado);
}

if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_contabilidad_listar_liquidacion")
{
	$login_usuario_id = $login?$login['id']:null;

	$mepa_contabilidad_param_liquidacion_fecha_desde = $_POST['mepa_contabilidad_param_liquidacion_fecha_desde'];
	$mepa_contabilidad_param_liquidacion_fecha_desde = date("Y-m-d", strtotime($mepa_contabilidad_param_liquidacion_fecha_desde));

	$mepa_contabilidad_param_liquidacion_fecha_hasta = $_POST['mepa_contabilidad_param_liquidacion_fecha_hasta'];
	$mepa_contabilidad_param_liquidacion_fecha_hasta = date("Y-m-d", strtotime($mepa_contabilidad_param_liquidacion_fecha_hasta));

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

        $where_redes = " AND rs.red_id IN ($ids_data_select_red) ";
    }

	// FIN: VERIFICAR RED

	$query = "
		SELECT
			l.id,
		    l.num_correlativo,
		    l.solicitante_usuario_id,
		    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS usuario_solicitante,
		    l.total_rendicion AS total_liquidacion,
		    l.se_aplica_movilidad, l.id_movilidad, m.monto_cierre AS total_movilidad,
		    l.created_at AS fecha_solicitud
		FROM mepa_caja_chica_liquidacion l
			INNER JOIN tbl_usuarios tu
			ON l.solicitante_usuario_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN tbl_razon_social rs
			ON tp.razon_social_id = rs.id
			LEFT JOIN mepa_caja_chica_movilidad m
			ON l.id_movilidad = m.id
		WHERE l.status = 1 AND l.situacion_etapa_id_contabilidad = 6 
			".$where_redes." 	
			AND DATE_FORMAT(l.created_at, '%Y-%m-%d') BETWEEN '".$mepa_contabilidad_param_liquidacion_fecha_desde."' AND '".$mepa_contabilidad_param_liquidacion_fecha_hasta."'
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->usuario_solicitante,
			"2" => $reg->num_correlativo,
			"3" => "S/ ".$reg->total_liquidacion,
			"4" => ($reg->se_aplica_movilidad == 1) ? "Si" : "No",
			"5" => ($reg->se_aplica_movilidad == 1) ? "S/ ".$reg->total_movilidad : "",
			"6" => '
					<a   
                        class="btn btn-info btn-sm" 
                        href="./?sec_id=mepa&sub_sec_id=detalle_atencion_liquidacion&id='.$reg->id.'"
                        data-toggle="tooltip" 
                        data-placement="top" 
                        title="Acceder al detalle">
                        <span class="fa fa-eye"></span>
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
}


?>