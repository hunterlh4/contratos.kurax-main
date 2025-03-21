<?php 

date_default_timezone_set("America/Lima");

$result=array();

include("db_connect.php");
include("sys_login.php");


if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_seguimiento_listar_asignacion")
{
	$param_usuario = $_POST['param_usuario'];
	
	$login_usuario_id = $login?$login['id']:null;

	$where_usuario_asignado = "";

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

	if($param_usuario != 0)
	{
		$where_usuario_asignado = "AND macc.usuario_asignado_id = '".$param_usuario."' ";
	}

	$query = "
		SELECT 
            macc.id, macc.tipo_solicitud_id, mts.nombre AS nombre_tipo_solicitud, macc.situacion_etapa_id, ce.situacion,
            concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado,
            macc.usuario_asignado_id, macc.created_at, macc.status, macc.fondo_asignado, macc.saldo_disponible, rs.nombre AS empresa, za.nombre AS zona, 
            macc.situacion_etapa_id_tesoreria,
            cet.situacion AS situacion_tesoreria
        FROM mepa_asignacion_caja_chica macc
            INNER JOIN mepa_tipos_solicitud mts
            ON macc.tipo_solicitud_id = mts.id
            INNER JOIN tbl_usuarios tu
			ON macc.usuario_asignado_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN tbl_razon_social rse
			ON tp.razon_social_id = rse.id
            INNER JOIN cont_etapa ce
            ON macc.situacion_etapa_id = ce.etapa_id
            INNER JOIN tbl_razon_social rs
            ON macc.empresa_id = rs.id
            INNER JOIN mepa_zona_asignacion za
            ON macc.zona_asignacion_id = za.id
            INNER JOIN cont_etapa cet
            ON macc.situacion_etapa_id_tesoreria = cet.etapa_id
		WHERE macc.status = 1 
			".$where_redes."
			".$where_usuario_asignado."
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->usuario_asignado,
			"2" => $reg->empresa,
			"3" => $reg->zona,
			"4" => "S/ ".number_format($reg->fondo_asignado, 2, '.', ','),
			"5" => "S/ ".number_format($reg->saldo_disponible, 2, '.', ','),
			"6" => $reg->situacion,
			"7" => $reg->situacion_tesoreria,
			"8" => '<a class="btn btn-warning btn-sm" 
						href="./?sec_id=mepa&amp;sub_sec_id=detalle_solicitud_asignacion&id='.$reg->id.'"
						target="_blank"
                        data-toggle="tooltip" data-placement="top" title="Acceder al detalle">
                        <span class="fa fa-eye"></span>
                    </a>'
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

if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_seguimiento_listar_liquidacion")
{
	$param_usuario = $_POST['param_usuario'];
	
	$login_usuario_id = $login?$login['id']:null;

	$where_usuario_asignado = "";

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

	if($param_usuario != 0)
	{
		$where_usuario_asignado = " AND a.usuario_asignado_id = '".$param_usuario."' ";
	}

	$suma_total = 0;
	$query = "
		SELECT
			l.id, l.asignacion_id, l.num_correlativo, l.fecha_desde, l.fecha_hasta, l.total_rendicion AS total_liquidacion,
		    l.se_aplica_movilidad, l.id_movilidad, m.monto_cierre AS total_movilidad,
		    ce.situacion AS situacion_jefe, cec.situacion AS situacion_contabilidad, cet.situacion AS situacion_tesoreria,
		    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''),' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado
		FROM mepa_caja_chica_liquidacion l
			INNER JOIN mepa_asignacion_caja_chica a
			ON l.asignacion_id = a.id
			INNER JOIN tbl_usuarios tu
			ON a.usuario_asignado_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN tbl_razon_social rs
			ON tp.razon_social_id = rs.id
			LEFT JOIN mepa_caja_chica_movilidad m
			ON l.id_movilidad = m.id
			INNER JOIN cont_etapa ce
			ON l.situacion_etapa_id_superior = ce.etapa_id
			INNER JOIN cont_etapa cec
			ON l.situacion_etapa_id_contabilidad = cec.etapa_id
			INNER JOIN cont_etapa cet
			ON l.situacion_etapa_id_tesoreria = cet.etapa_id
		WHERE l.status = 1 
			".$where_redes." 
			".$where_usuario_asignado."
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
		$suma_total = $reg->total_liquidacion + $reg->total_movilidad;

		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->usuario_asignado,
			"2" => $reg->num_correlativo,
			"3" => $reg->fecha_desde.' al '.$reg->fecha_hasta,
			"4" => "S/ ".$reg->total_liquidacion,
			"5" => ($reg->se_aplica_movilidad == 1) ? "S/ ".$reg->total_movilidad : "0.00",
			"6" => "S/ ".number_format($suma_total, 2, '.', ','),
			"7" => $reg->situacion_jefe,
			"8" => $reg->situacion_contabilidad,
			"9" => $reg->situacion_tesoreria,
			"10" => '<a class="btn btn-info btn-sm"
                        href="./?sec_id=mepa&amp;sub_sec_id=detalle_atencion_liquidacion&id='.$reg->id.'"
                        target="_blank"
                        data-toggle="tooltip" data-placement="top" title="Acceder al detalle">
                        <span class="fa fa-eye"></span>
                    </a>'
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

if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_seguimiento_listar_movilidad")
{
	$param_usuario = $_POST['param_usuario'];
	
	$login_usuario_id = $login?$login['id']:null;

	$where_usuario_asignado = "";

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

	if($param_usuario != 0)
	{
		$where_usuario_asignado = " AND m.user_created_id = '".$param_usuario."' ";
	}

	$query = 
	"
		SELECT
			m.id, m.num_correlativo, 
			concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS solicitante,
			m.id_tipo_solicitud_movilidad,
		    l.id_movilidad, l.num_correlativo AS num_liquidacion,
		    s.nombre AS tipo_solicitud_movilidad,
		    m.id_usuario_volante,
			concat(IFNULL(tpv.nombre, ''),' ', IFNULL(tpv.apellido_paterno, ''), ' ', IFNULL(tpv.apellido_materno, '')) AS usuario_volante,
		    m.fecha_del, m.fecha_al,
		    m.status,
		    IFNULL(
					(
						SELECT sum(monto) 
						FROM mepa_caja_chica_movilidad_detalle AS mdi 
						WHERE mdi.estado = 1 AND mdi.id_mepa_caja_chica_movilidad = m.id
					)
				,0) monto_detalle
		FROM mepa_caja_chica_movilidad m
			INNER JOIN mepa_tipos_solicitud s
			ON m.id_tipo_solicitud_movilidad = s.id
		    LEFT JOIN mepa_caja_chica_liquidacion l
			ON m.id = l.id_movilidad
		    LEFT JOIN tbl_usuarios tuv
			ON m.id_usuario_volante = tuv.id
			LEFT JOIN tbl_personal_apt tpv
			ON tuv.personal_id = tpv.id
			LEFT JOIN tbl_usuarios tu
			ON m.user_created_id = tu.id
			LEFT JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN tbl_razon_social rs
			ON tp.razon_social_id = rs.id
		WHERE m.estado = 1
			".$where_redes." 
			".$where_usuario_asignado."
		ORDER BY m.num_correlativo, m.id_tipo_solicitud_movilidad, concat(IFNULL(tpv.nombre, ''),' ', IFNULL(tpv.apellido_paterno, ''), ' ', IFNULL(tpv.apellido_materno, ''))
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->solicitante,
			"2" => $reg->num_correlativo,
			"3" => $reg->tipo_solicitud_movilidad,
			"4" => ($reg->id_tipo_solicitud_movilidad == 8) ? $reg->usuario_volante : 'No Aplica',
			"5" => $reg->fecha_del.' - '.$reg->fecha_al,
			"6" => $reg->monto_detalle,
			"7" => ($reg->status == 1) ? '<span class="badge badge-primary">Abierto</span>' : '<span class="badge badge-danger">Cerrado</span>',
			"8" => ($reg->id_movilidad == NULL) ? 'Movilidad sin Asignar' : 'Movilidad Asignada',
			"9" => $reg->num_liquidacion,
			"10" => ($reg->id_tipo_solicitud_movilidad == 7) ? 
				'
				<a   
                    class="btn btn-info btn btn-sm btn-round" 
                    href="./?sec_id=mepa&sub_sec_id=solicitud_movilidad_detalle&cc_movilidad='.$reg->id.'"
                    data-toggle="tooltip" 
                    data-placement="top" 
                    title="Acceder al detalle">
                    <i class="fa fa-eye"></i>
                </a>
				' 
				:
				'
				<a   
                    class="btn btn-info btn btn-sm btn-round" 
                    href="./?sec_id=mepa&sub_sec_id=solicitud_movilidad_volante_detalle&cc_movilidad='.$reg->id.'"
                    data-toggle="tooltip"
                    data-placement="top" 
                    title="Acceder al detalle">
                    <i class="fa fa-eye"></i>
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

?>