<?php  

date_default_timezone_set("America/Lima");
$result=array();
include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);


if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_tesoreria_listar_programaciones_asignacion")
{
	$mepa_tesoreria_param_tipo_solicitud = $_POST["mepa_tesoreria_param_tipo_solicitud"];
	
	$mepa_tesoreria_param_fecha_inicio = $_POST['mepa_tesoreria_param_fecha_inicio'];
	$mepa_tesoreria_param_fecha_inicio = date("Y-m-d", strtotime($mepa_tesoreria_param_fecha_inicio));

	$mepa_tesoreria_param_fecha_fin = $_POST['mepa_tesoreria_param_fecha_fin'];
	$mepa_tesoreria_param_fecha_fin = date("Y-m-d", strtotime($mepa_tesoreria_param_fecha_fin));

	$login_usuario_id = $login?$login['id']:null;

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
			p.id, p.tipo_solicitud_id, s.nombre AS nombre_solicitud, p.tipo_banco AS banco, rs.nombre AS empresa, p.fecha_programacion, p.user_created_id, p.numero_comprobante_concar,
		    (
				SELECT
					sum(ai.fondo_asignado)
		        FROM mepa_caja_chica_programacion_detalle pdi
		        INNER JOIN mepa_asignacion_caja_chica ai
		        ON (ai.id = pdi.nombre_tabla_id)
		        WHERE pdi.mepa_caja_chica_programacion_id = p.id AND pdi.status = 1
		    ) AS importe_total,
		    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, '')) AS usuario_creacion,
		    p.situacion_etapa_id,
		    ce.situacion AS situacion_programacion,
		    p.se_cargo_comprobante
		FROM mepa_caja_chica_programacion p
			INNER JOIN tbl_razon_social rs
			ON p.empresa_id = rs.id
			INNER JOIN mepa_tipos_solicitud s
			ON p.tipo_solicitud_id = s.id
			INNER JOIN tbl_usuarios tu
			ON p.user_created_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN cont_etapa ce
			ON p.situacion_etapa_id = ce.etapa_id
		WHERE p.status = 1 AND p.tipo_solicitud_id = 1 
			".$where_redes."
			AND DATE_FORMAT(p.fecha_programacion, '%Y-%m-%d') BETWEEN '".$mepa_tesoreria_param_fecha_inicio."' AND '".$mepa_tesoreria_param_fecha_fin."'
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->nombre_solicitud,
			"2" => ($reg->banco == 1) ? 'BBVA':'Otros a BBVA',
			"3" => $reg->empresa,
			"4" => $reg->fecha_programacion,
			"5" => "S/ ".$reg->importe_total,
			"6" => $reg->usuario_creacion,
			"7" => $reg->situacion_programacion,
			"8" => ($reg->se_cargo_comprobante == 0) ? 
					'<a onclick="";
                        class="btn btn-info btn-sm"
                        href="./?sec_id=mepa&amp;sub_sec_id=detalle_tesoreria_programacion&id='.$reg->id.'"
                        data-toggle="tooltip" data-placement="top" title="Ver Detalle">
                        <span class="fa fa-eye"></span>
                    </a>
                    <a onclick="";
                        class="btn btn-warning btn-sm"
                        href="./?sec_id=mepa&amp;sub_sec_id=tesoreria_atencion&id='.$reg->id.'"
                        data-toggle="tooltip" data-placement="top" title="Editar Programación">
                        <span class="fa fa-edit"></span>
                    </a>
                    '
                    : 
                    '<a onclick="";
                        class="btn btn-info btn-sm"
                        href="./?sec_id=mepa&amp;sub_sec_id=detalle_tesoreria_programacion&id='.$reg->id.'"
                        data-toggle="tooltip" data-placement="top" title="Ver Detalle">
                        <span class="fa fa-eye"></span>
                    </a>
                    ',
	        "9" => '
					<a type="button"class="btn btn-primary btn-sm" style="margin-left: 2px;" title="Exportar .txt" href="mepa_tesoreria_programacion_pago_export_txt.php?id='.$reg->id.'" target="_blank"><i class="fa fa-file-text-o"></i>
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

if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_tesoreria_listar_programaciones_liquidacion")
{
	$mepa_tesoreria_param_tipo_solicitud = $_POST["mepa_tesoreria_param_tipo_solicitud"];
	
	$mepa_tesoreria_param_fecha_inicio = $_POST['mepa_tesoreria_param_fecha_inicio'];
	$mepa_tesoreria_param_fecha_inicio = date("Y-m-d", strtotime($mepa_tesoreria_param_fecha_inicio));

	$mepa_tesoreria_param_fecha_fin = $_POST['mepa_tesoreria_param_fecha_fin'];
	$mepa_tesoreria_param_fecha_fin = date("Y-m-d", strtotime($mepa_tesoreria_param_fecha_fin));

	$login_usuario_id = $login?$login['id']:null;

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

	$suma_total = 0;

	$query = "
		SELECT
			p.id, p.tipo_solicitud_id, s.nombre AS nombre_solicitud, p.tipo_banco AS banco, rs.nombre AS empresa, p.fecha_programacion, p.user_created_id, p.numero_comprobante_concar,
		    (
				SELECT
					sum(l.sub_total)
		        FROM mepa_caja_chica_programacion_detalle pdi
		        	INNER JOIN mepa_caja_chica_liquidacion l
		        	ON (l.id = pdi.nombre_tabla_id)
		        WHERE pdi.mepa_caja_chica_programacion_id = p.id AND pdi.status = 1
		    ) AS sub_total,
		    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, '')) AS usuario_creacion,
		    p.situacion_etapa_id,
		    ce.situacion AS situacion_programacion,
		    p.se_cargo_comprobante
		FROM mepa_caja_chica_programacion p
			INNER JOIN tbl_razon_social rs
			ON p.empresa_id = rs.id
			INNER JOIN mepa_tipos_solicitud s
			ON p.tipo_solicitud_id = s.id
			INNER JOIN tbl_usuarios tu
			ON p.user_created_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN cont_etapa ce
			ON p.situacion_etapa_id = ce.etapa_id
		WHERE p.status = 1 AND p.tipo_solicitud_id = 2 
			".$where_redes."
			AND DATE_FORMAT(p.fecha_programacion, '%Y-%m-%d') BETWEEN '".$mepa_tesoreria_param_fecha_inicio."' AND '".$mepa_tesoreria_param_fecha_fin."'
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->nombre_solicitud,
			"2" => ($reg->banco == 1) ? 'BBVA':'Otros a BBVA',
			"3" => $reg->empresa,
			"4" => $reg->fecha_programacion,
			"5" => "S/ ".$reg->sub_total,
			"6" => $reg->usuario_creacion,
			"7" => $reg->situacion_programacion,
			"8" => ($reg->se_cargo_comprobante == 0) ? 
					'<a onclick="";
                        class="btn btn-info btn-sm"
                        href="./?sec_id=mepa&amp;sub_sec_id=detalle_tesoreria_programacion&id='.$reg->id.'"
                        data-toggle="tooltip" data-placement="top" title="Ver Detalle">
                        <span class="fa fa-eye"></span>
                    </a>
                    <a onclick="";
                        class="btn btn-warning btn-sm"
                        href="./?sec_id=mepa&amp;sub_sec_id=tesoreria_atencion&id='.$reg->id.'"
                        data-toggle="tooltip" data-placement="top" title="Editar Programación">
                        <span class="fa fa-edit"></span>
                    </a>
                    ':
                    '
                    <a onclick="";
                        class="btn btn-info btn-sm"
                        href="./?sec_id=mepa&amp;sub_sec_id=detalle_tesoreria_programacion&id='.$reg->id.'"
                        data-toggle="tooltip" data-placement="top" title="Ver Detalle">
                        <span class="fa fa-eye"></span>
                    </a>
                    ',
            "9" => '
            		<a type="button"class="btn btn-primary btn-sm" style="margin-left: 2px;" title="Exportar .txt" href="mepa_tesoreria_programacion_pago_export_txt.php?id='.$reg->id.'" target="_blank"><i class="fa fa-file-text-o"></i>
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

if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_tesoreria_listar_programaciones_aumento_asignacion")
{
	$mepa_tesoreria_param_tipo_solicitud = $_POST["mepa_tesoreria_param_tipo_solicitud"];
	
	$mepa_tesoreria_param_fecha_inicio = $_POST['mepa_tesoreria_param_fecha_inicio'];
	$mepa_tesoreria_param_fecha_inicio = date("Y-m-d", strtotime($mepa_tesoreria_param_fecha_inicio));

	$mepa_tesoreria_param_fecha_fin = $_POST['mepa_tesoreria_param_fecha_fin'];
	$mepa_tesoreria_param_fecha_fin = date("Y-m-d", strtotime($mepa_tesoreria_param_fecha_fin));

	$login_usuario_id = $login?$login['id']:null;

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
			p.id, p.tipo_solicitud_id, s.nombre AS nombre_solicitud, p.tipo_banco AS banco, rs.nombre AS empresa, p.fecha_programacion, p.user_created_id, p.numero_comprobante_concar,
		    (
				SELECT
					sum(aa.monto)
		        FROM mepa_caja_chica_programacion_detalle pdi
		        INNER JOIN mepa_aumento_asignacion aa
		        ON (aa.id = pdi.nombre_tabla_id)
		        WHERE pdi.mepa_caja_chica_programacion_id = p.id AND pdi.status = 1
		    ) AS importe_total,
		    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, '')) AS usuario_creacion,
		    p.situacion_etapa_id,
		    ce.situacion AS situacion_programacion,
		    p.se_cargo_comprobante
		FROM mepa_caja_chica_programacion p
			INNER JOIN tbl_razon_social rs
			ON p.empresa_id = rs.id
			INNER JOIN mepa_tipos_solicitud s
			ON p.tipo_solicitud_id = s.id
			INNER JOIN tbl_usuarios tu
			ON p.user_created_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN cont_etapa ce
			ON p.situacion_etapa_id = ce.etapa_id
		WHERE p.status = 1 AND p.tipo_solicitud_id = 9 
			".$where_redes."
			AND DATE_FORMAT(p.fecha_programacion, '%Y-%m-%d') BETWEEN '".$mepa_tesoreria_param_fecha_inicio."' AND '".$mepa_tesoreria_param_fecha_fin."'
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->nombre_solicitud,
			"2" => ($reg->banco == 1) ? 'BBVA':'Otros a BBVA',
			"3" => $reg->empresa,
			"4" => $reg->fecha_programacion,
			"5" => "S/ ".$reg->importe_total,
			"6" => $reg->usuario_creacion,
			"7" => $reg->situacion_programacion,
			"8" => ($reg->se_cargo_comprobante == 0) ? 
					'<a onclick="";
                        class="btn btn-info btn-sm"
                        href="./?sec_id=mepa&amp;sub_sec_id=detalle_tesoreria_programacion&id='.$reg->id.'"
                        data-toggle="tooltip" data-placement="top" title="Ver Detalle">
                        <span class="fa fa-eye"></span>
                    </a>
                    <a onclick="";
                        class="btn btn-warning btn-sm"
                        href="./?sec_id=mepa&amp;sub_sec_id=tesoreria_atencion&id='.$reg->id.'"
                        data-toggle="tooltip" data-placement="top" title="Editar Programación">
                        <span class="fa fa-edit"></span>
                    </a>
                    '
                    : 
                    '<a onclick="";
                        class="btn btn-info btn-sm"
                        href="./?sec_id=mepa&amp;sub_sec_id=detalle_tesoreria_programacion&id='.$reg->id.'"
                        data-toggle="tooltip" data-placement="top" title="Ver Detalle">
                        <span class="fa fa-eye"></span>
                    </a>
                    ',
	        "9" => '
					<a type="button"class="btn btn-primary btn-sm" style="margin-left: 2px;" title="Exportar .txt" href="mepa_tesoreria_programacion_pago_export_txt.php?id='.$reg->id.'" target="_blank"><i class="fa fa-file-text-o"></i>
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