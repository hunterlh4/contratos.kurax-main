<?php

date_default_timezone_set("America/Lima");
$result=array();
include("db_connect.php");
include("sys_login.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);


if (isset($_POST["accion"]) && $_POST["accion"]==="prestamo_tesoreria_listar_programacion_pago_boveda")
{
	$param_fecha_inicio = $_POST['param_fecha_inicio'];
	$param_fecha_inicio = date("Y-m-d", strtotime($param_fecha_inicio));

	$param_fecha_fin = $_POST['param_fecha_fin'];
	$param_fecha_fin = date("Y-m-d", strtotime($param_fecha_fin));

	$login_usuario_id = $login?$login['id']:null;
	
	// INICIO: VERIFICAR RED

	$where_locales_redes = "";

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

        $where_locales_redes = " AND pp.tipo_tienda IN ($ids_data_select_red) ";
    }

    // FIN: VERIFICAR RED

	$query = "
		SELECT
			pp.id, 
			lr.nombre AS tipo_tienda,
			etp.situacion AS tipo_prestamo,
			IF(pp.tipo_banco = 1, 'BBVA', 'Otros a BBVA') AS banco,
		    rs.nombre AS empresa,
		    pp.created_at AS fecha_programacion,
		    (
				SELECT
					IFNULL(SUM(bi.monto), 0) AS monto
				FROM tbl_prestamo_programacion_detalle pdi
					INNER JOIN tbl_caja_prestamo_boveda bi
					ON pdi.tbl_caja_prestamo_boveda_id = bi.id
				WHERE pdi.tbl_prestamo_programacion_id = pp.id AND pdi.status = 1
			) AS importe_total,
		    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, '')) AS usuario_creacion,
			pe.situacion AS situacion_programacion,
			pp.se_cargo_comprobante
		FROM tbl_prestamo_programacion pp
			INNER JOIN tbl_locales_redes lr
			ON pp.tipo_tienda = lr.id
			INNER JOIN tbl_prestamo_etapa etp
			ON pp.tipo_prestamo = etp.id
			INNER JOIN tbl_razon_social rs
			ON pp.empresa_id = rs.id
			INNER JOIN tbl_usuarios tu
			ON pp.user_created_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN tbl_prestamo_etapa pe
			ON pp.situacion_etapa_id = pe.id
		WHERE pp.status = 1 
			AND DATE_FORMAT(pp.created_at, '%Y-%m-%d') BETWEEN '".$param_fecha_inicio."' AND '".$param_fecha_fin."'
			".$where_locales_redes."
	";

	$list_query = $mysqli->query($query);

	//$li = $list_query->fetch_assoc();

	$data =  array();

	while($reg = $list_query->fetch_object()) 
	{
		$data[] = array(
			"0" => $reg->id,
			"1" => $reg->tipo_tienda,
			"2" => $reg->tipo_prestamo,
			"3" => $reg->banco,
			"4" => $reg->empresa,
			"5" => $reg->fecha_programacion,
			"6" => "S/ ".$reg->importe_total,
			"7" => $reg->usuario_creacion,
			"8" => $reg->situacion_programacion,
			"9" => ($reg->se_cargo_comprobante == 0) ? 
					'<a onclick="";
                        class="btn btn-info btn-sm"
                        href="./?sec_id=prestamo&amp;sub_sec_id=detalle_tesoreria_programacion&id='.$reg->id.'"
                        data-toggle="tooltip" data-placement="top" title="Ver Detalle">
                        <span class="fa fa-eye"></span>
                    </a>
                    <a onclick="";
                        class="btn btn-warning btn-sm"
                        href="./?sec_id=prestamo&amp;sub_sec_id=tesoreria_atencion&id='.$reg->id.'"
                        data-toggle="tooltip" data-placement="top" title="Editar Programación">
                        <span class="fa fa-edit"></span>
                    </a>
                    '
                    : 
                    '<a onclick="";
                        class="btn btn-info btn-sm"
                        href="./?sec_id=prestamo&amp;sub_sec_id=detalle_tesoreria_programacion&id='.$reg->id.'"
                        data-toggle="tooltip" data-placement="top" title="Ver Detalle">
                        <span class="fa fa-eye"></span>
                    </a>
                    ',
	        "10" => '
					<a type="button"class="btn btn-primary btn-sm" style="margin-left: 2px;" title="Exportar .txt" href="sec_prestamo_tesoreria_pago_export_txt.php?id='.$reg->id.'" target="_blank"><i class="fa fa-file-text-o"></i>
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