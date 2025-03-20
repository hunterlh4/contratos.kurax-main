<?php

date_default_timezone_set("America/Lima");

$result=array();
include("db_connect.php");
include("sys_login.php");

include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';

require_once '/var/www/html/env.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);


if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_solicitud_rendicion_caja_chica_obtener_usuarios_asignado") 
{

	$zona_id = $_POST["zona_id"];
	
	$query = "
				SELECT
					u.id,
				    concat(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS nombre
				FROM mepa_asignacion_caja_chica a
					INNER JOIN tbl_usuarios u
					ON a.usuario_asignado_id = u.id
					INNER JOIN tbl_personal_apt p
					ON u.personal_id = p.id
				WHERE a.status = 1 AND a.zona_asignacion_id = '".$zona_id."'
			";
	
	$list_query = $mysqli->query($query);
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
		$result["result"] = "El usuario no cuenta con registros.";
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
}

if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_rendicion_caja_chica_listar_liquidacion_datatable")
{
	$param_zona = $_POST['param_zona'];
	$param_usuario = $_POST['param_usuario'];
	$param_situacion_contabilidad = $_POST['param_situacion_contabilidad'];
	$param_tipo_red = $_POST['param_tipo_red'];

	$login_usuario_id = $login?$login['id']:null;

	$query = "";
	$where_zona = "";
	$where_usuario = "";
	$where_situacion_contabilidad = "";
	$where_redes = "";

	if($param_situacion_contabilidad != 0)
	{
		if($param_situacion_contabilidad == 1)
		{
			$where_situacion_contabilidad = " AND l.situacion_etapa_id_contabilidad IN (1, 7)";
		}
		else if($param_situacion_contabilidad == 6)
		{
			$where_situacion_contabilidad = " AND l.situacion_etapa_id_contabilidad IN (6)";
		}
	}
	
	if($param_zona == 0)
	{
		$query_zonas_atencion_usuario = 
		"
            SELECT
                z.id, z.nombre
            FROM mepa_atencion_solicitud_zona sz
                INNER JOIN mepa_zona_asignacion z
                ON sz.id_zona = z.id
            WHERE sz.id_usuario = '".$login_usuario_id."'
        ";

        $list_query_zonas_atencion_usuario = $mysqli->query($query_zonas_atencion_usuario);

		$row_count_zonas_atencion_usuario = $list_query_zonas_atencion_usuario->num_rows;

		$ids_zonas_atencion_registrado = '';
		$contador_ids = 0;
		
		if ($row_count_zonas_atencion_usuario > 0) 
		{
			while ($row = $list_query_zonas_atencion_usuario->fetch_assoc()) 
			{
				if ($contador_ids > 0) 
				{
					$ids_zonas_atencion_registrado .= ',';
				}

				$ids_zonas_atencion_registrado .= $row["id"];			
				$contador_ids++;
			}

			$where_zona = " AND a.zona_asignacion_id IN ($ids_zonas_atencion_registrado) ";
		}
		else
		{
			$where_zona = " AND a.zona_asignacion_id IN (0) ";
		}
	}
	else if($param_zona != 0)
	{
		$where_zona = " AND a.zona_asignacion_id = '".$param_zona."' ";
	}

	if($param_usuario != 0)
	{
		$where_usuario = " AND a.usuario_asignado_id = '".$param_usuario."' ";
	}

	if($param_tipo_red != 0)
	{
		$where_redes = " AND rs.red_id = '".$param_tipo_red."' ";
	}

	$query = "
		SELECT
			a.id AS asignacion_id, a.usuario_asignado_id, l.id AS liquidacion_id,
			concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado,
			tp.dni AS usuario_asignado_dni,
			z.nombre AS zona,
			l.num_correlativo,
			l.total_rendicion AS total_liquidacion,
			m.monto_cierre AS total_movilidad,
			l.situacion_etapa_id_contabilidad,
		    ec.situacion AS situacion_contabilidad,
			l.situacion_etapa_id_tesoreria,
		    et.situacion AS situacion_tesoreria,
		    l.etapa_id_se_envio_a_tesoreria,
    		eet.situacion AS situacion_enviado_a_tesoreria,
    		l.created_at AS fecha_solicitud,
			'' AS fecha_comprobante, l.flag_envio_documento_fisico
		FROM mepa_caja_chica_liquidacion l
			INNER JOIN mepa_asignacion_caja_chica a
			ON l.asignacion_id = a.id
			LEFT JOIN mepa_caja_chica_movilidad m
			ON l.id_movilidad = m.id
			INNER JOIN tbl_usuarios tu
			ON a.usuario_asignado_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN mepa_zona_asignacion z
			ON a.zona_asignacion_id = z.id
			INNER JOIN tbl_razon_social rs
            ON a.empresa_id = rs.id
			INNER JOIN cont_etapa ec
			ON l.situacion_etapa_id_contabilidad = ec.etapa_id
			INNER JOIN cont_etapa et
			ON l.situacion_etapa_id_tesoreria = et.etapa_id
			INNER JOIN cont_etapa eet
			ON l.etapa_id_se_envio_a_tesoreria = eet.etapa_id
		WHERE l.status = 1 AND a.status = 1 
			AND l.situacion_etapa_id_superior = 6
			".$where_zona." 
			".$where_usuario." 
			".$where_situacion_contabilidad."
			".$where_redes."
	";

	$list_query = $mysqli->query($query);

	$data =  array();
	$num = 1;

	while($reg = $list_query->fetch_object()) 
	{
		$suma_total = $reg->total_liquidacion + $reg->total_movilidad;

		$data[] = array(
			"0" => $num,
			"1" => $reg->usuario_asignado,
			"2" => $reg->usuario_asignado_dni,
			"3" => $reg->zona,
			"4" => $reg->num_correlativo,
			"5" => "S/ ".$suma_total,
			"6" => $reg->fecha_solicitud,
			"7" => $reg->situacion_contabilidad,
			"8" => $reg->situacion_enviado_a_tesoreria,
			"9" => ($reg->flag_envio_documento_fisico) ? 
					'<input class="mepa_switch" id="checkbox_'.$reg->liquidacion_id.'" type="checkbox" data-table="mepa_caja_chica_liquidacion" data-id="'.$reg->liquidacion_id.'" data-col="flag_envio_documento_fisico" data-on-value="1" data-off-value="0" checked="checked" data-ignore="true">'
					:
					'<input class="mepa_switch" id="checkbox_'.$reg->liquidacion_id.'" type="checkbox" data-table="mepa_caja_chica_liquidacion" data-id="'.$reg->liquidacion_id.'" data-col="flag_envio_documento_fisico" data-on-value="1" data-off-value="0" data-ignore="true">',
			"10" => '
					<a   
                        class="btn btn-info btn-sm" 
                        href="./?sec_id=mepa&sub_sec_id=detalle_atencion_liquidacion&id='.$reg->liquidacion_id.'"
                        data-toggle="tooltip" 
                        data-placement="top" 
                        title="Acceder al detalle">
                        <span class="fa fa-eye"></span>
                    </a>
					<a   
                        class="btn btn-success btn-sm" 
                        data-toggle="tooltip" 
                        data-placement="top" 
						onclick="exportarDetalleSolicitudLiquidacionExcel('.$reg->liquidacion_id.')"
                        title="Exportar Detalle">
                        <span class="fa fa-file-excel-o"></span>
                    </a>
					<a   
                        class="btn btn-sm" 
						style="background-color: #9370DB; color: white;"
                        data-toggle="tooltip" 
                        data-placement="top" 
						onclick="descargarComprobantesSolicitudLiquidacionExcel('.$reg->liquidacion_id.')"
                        title="Descargar comprobantes">
                        <span class="fa fa-download"></span>
                    </a>
					'
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

	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_rendicion_caja_chica_buscar_listar_pendientes_enviar_a_tesoreria")
{
	$param_tipo_red = $_POST['param_tipo_red'];

	$usuario_id = $login?$login['id']:null;

	$where_redes = "";

	if($param_tipo_red != 0)
	{
		$where_redes = " AND rs.red_id = '".$param_tipo_red."' ";
	}

	$query = "";

	// INICIO LISTAR TODAS LAS ZONAS EN LA CUAL EL ASISTENTE CONTABILIDAD PERTENECE
	$query_ids_zonas = 
	"
		SELECT
			sz.id_zona
		FROM mepa_atencion_solicitud_zona sz
		WHERE sz.id_usuario = '".$usuario_id."' AND sz.status = 1
	";

	$data_query_ids_zonas = $mysqli->query($query_ids_zonas);

	$query_todos = "";

	$ids_zona = '';
	$cont_ids_zona = 0;

	while($row = $data_query_ids_zonas->fetch_assoc())
	{
		if($cont_ids_zona > 0)
		{
			$ids_zona .= ',';
		}

		$ids_zona .= $row["id_zona"];

		$cont_ids_zona++;
	}
	// FIN LISTAR TODAS LAS ZONAS EN LA CUAL EL ASISTENTE CONTABILIDAD PERTENECE

	$query_uno = "
		SELECT
			a.id AS asignacion_id, a.usuario_asignado_id, l.id AS liquidacion_id,
			concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado,
			tp.dni AS usuario_asignado_dni,
			z.nombre AS zona,
			l.num_correlativo,
			a.fondo_asignado,
			IFNULL(l.total_rendicion, 0) AS total_liquidacion,
			IFNULL(m.monto_cierre, 0) AS total_movilidad,
			IFNULL(l.total_rendicion, 0) + IFNULL(m.monto_cierre, 0) AS total_calculado_a_reembolsar,
			l.situacion_etapa_id_contabilidad,
		    ec.situacion AS situacion_contabilidad,
			l.situacion_etapa_id_tesoreria,
		    et.situacion AS situacion_tesoreria,
		    l.etapa_id_se_envio_a_tesoreria,
    		eet.situacion AS situacion_enviado_a_tesoreria,
    		l.created_at AS fecha_solicitud,
			'' AS fecha_comprobante, l.situacion_jefe_cerrar_caja_chica
		FROM mepa_caja_chica_liquidacion l
			INNER JOIN mepa_asignacion_caja_chica a
			ON l.asignacion_id = a.id
			LEFT JOIN mepa_caja_chica_movilidad m
			ON l.id_movilidad = m.id
			INNER JOIN tbl_usuarios tu
			ON a.usuario_asignado_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN mepa_zona_asignacion z
			ON a.zona_asignacion_id = z.id
			INNER JOIN tbl_razon_social rs
            ON a.empresa_id = rs.id
			INNER JOIN cont_etapa ec
			ON l.situacion_etapa_id_contabilidad = ec.etapa_id
			INNER JOIN cont_etapa et
			ON l.situacion_etapa_id_tesoreria = et.etapa_id
			INNER JOIN cont_etapa eet
			ON l.etapa_id_se_envio_a_tesoreria = eet.etapa_id
		WHERE l.status = 1 AND a.status = 1 AND a.situacion_etapa_id = 6
			AND l.situacion_etapa_id_superior = 6 
			AND l.situacion_etapa_id_contabilidad = 6 
			AND l.situacion_jefe_cerrar_caja_chica IS NULL 
			AND l.etapa_id_se_envio_a_tesoreria = 1 
			AND a.zona_asignacion_id IN (".$ids_zona.")
			".$where_redes."
	";

	$query_dos = "
		SELECT
			a.id AS asignacion_id, a.usuario_asignado_id, l.id AS liquidacion_id,
			concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado,
			tp.dni AS usuario_asignado_dni,
			z.nombre AS zona,
			l.num_correlativo,
			a.fondo_asignado,
			IFNULL(l.total_rendicion, 0) AS total_liquidacion,
			IFNULL(m.monto_cierre, 0) AS total_movilidad,
			(
				SELECT
					SUM(IFNULL(li.total_rendicion, 0) + IFNULL(mi.monto_cierre, 0))
				FROM mepa_caja_chica_liquidacion li
					INNER JOIN mepa_asignacion_caja_chica ai
					ON li.asignacion_id = ai.id
					LEFT JOIN mepa_caja_chica_movilidad mi
					ON li.id_movilidad = mi.id
				WHERE li.situacion_etapa_id_superior = 6 
    				AND li.situacion_etapa_id_contabilidad = 6 
					AND	li.etapa_id_se_envio_a_tesoreria = 1
					AND li.situacion_etapa_id_tesoreria = 10
					AND ai.id = a.id
				ORDER BY l.id DESC
		    ) AS total_calculado_a_reembolsar,
			l.situacion_etapa_id_contabilidad,
		    ec.situacion AS situacion_contabilidad,
			l.situacion_etapa_id_tesoreria,
		    et.situacion AS situacion_tesoreria,
		    l.etapa_id_se_envio_a_tesoreria,
    		eet.situacion AS situacion_enviado_a_tesoreria,
    		l.created_at AS fecha_solicitud,
			'' AS fecha_comprobante, l.situacion_jefe_cerrar_caja_chica
		FROM mepa_caja_chica_liquidacion l
			INNER JOIN mepa_asignacion_caja_chica a
			ON l.asignacion_id = a.id
			LEFT JOIN mepa_caja_chica_movilidad m
			ON l.id_movilidad = m.id
			INNER JOIN tbl_usuarios tu
			ON a.usuario_asignado_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN mepa_zona_asignacion z
			ON a.zona_asignacion_id = z.id
			INNER JOIN tbl_razon_social rs
            ON a.empresa_id = rs.id
			INNER JOIN cont_etapa ec
			ON l.situacion_etapa_id_contabilidad = ec.etapa_id
			INNER JOIN cont_etapa et
			ON l.situacion_etapa_id_tesoreria = et.etapa_id
			INNER JOIN cont_etapa eet
			ON l.etapa_id_se_envio_a_tesoreria = eet.etapa_id
			INNER JOIN cont_etapa eec
			ON l.situacion_jefe_cerrar_caja_chica = eec.etapa_id
		WHERE l.status = 1 AND a.status = 1 
			AND (a.situacion_etapa_id = 6 OR a.situacion_etapa_id = 8)
			AND l.situacion_etapa_id_superior = 6 
			AND l.situacion_etapa_id_contabilidad = 6 
			AND l.situacion_jefe_cerrar_caja_chica = 12 
			AND l.etapa_id_se_envio_a_tesoreria = 1 
			AND
		    (
				SELECT
					SUM(IFNULL(li.total_rendicion, 0) + IFNULL(mi.monto_cierre, 0))
				FROM mepa_caja_chica_liquidacion li
					INNER JOIN mepa_asignacion_caja_chica ai
					ON li.asignacion_id = ai.id
					LEFT JOIN mepa_caja_chica_movilidad mi
					ON li.id_movilidad = mi.id
				WHERE li.situacion_etapa_id_superior = 6 
    				AND li.situacion_etapa_id_contabilidad = 6 
					AND li.situacion_etapa_id_tesoreria = 10
					AND ai.id = a.id
				ORDER BY l.id DESC
		    ) > a.fondo_asignado
			-- AND IFNULL(l.total_rendicion, 0) + IFNULL(m.monto_cierre, 0) > a.fondo_asignado
			AND a.zona_asignacion_id IN (".$ids_zona.")
			".$where_redes."
	";

	$query_todos.= $query_uno;
	$query_todos.= "UNION ALL";
	$query_todos.= $query_dos;

	$list_query = $mysqli->query($query_todos);

	$data =  array();
	$num = 1;

	while($reg = $list_query->fetch_object()) 
	{
		$suma_total_a_reembolsar = "";
		$suma_total = $reg->total_liquidacion + $reg->total_movilidad;

		if($reg->situacion_jefe_cerrar_caja_chica == 12)
		{
			$suma_total_a_reembolsar = $reg->total_calculado_a_reembolsar - $reg->fondo_asignado;	
		}
		else
		{
			$suma_total_a_reembolsar = $reg->total_calculado_a_reembolsar;
		}
		

		$data[] = array(
			"0" => $num,
			"1" => $reg->liquidacion_id,
			"2" => $reg->usuario_asignado,
			"3" => $reg->usuario_asignado_dni,
			"4" => $reg->zona,
			"5" => $reg->num_correlativo,
			"6" => "S/ ".number_format($suma_total, 2, '.', ','),
			"7" => "S/ ".number_format($suma_total_a_reembolsar, 2, '.', ','),
			"8" => $reg->situacion_enviado_a_tesoreria
		);

		$num++;
	}

	$result["http_code"] = 200;
	$result["status"] = "Datos obtenidos de gestion.";
	$result["aaData"] = $data;

}

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_mepa_rendicion_caja_chica_liquidacion_enviar_tesoreria_todos") 
{
	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	$error = '';
	$query_update = "";

	if((int) $usuario_id>0)
	{
		$param_tipo_red = $_POST['param_tipo_red'];

		$where_redes = "";

		if($param_tipo_red != 0)
		{
			$where_redes = " AND rs.red_id = '".$param_tipo_red."' ";
		}
		
		$query_todos = "";

		// INICIO LISTAR TODAS LAS ZONAS EN LA CUAL EL ASITENTE CONTABILIDAD PERTENECE
		$query_ids_zonas = 
		"
			SELECT
				sz.id_zona
			FROM mepa_atencion_solicitud_zona sz
			WHERE sz.id_usuario = '".$usuario_id."' AND sz.status = 1
		";

		$data_query_ids_zonas = $mysqli->query($query_ids_zonas);


		$ids_zona = '';
		$cont_ids_zona = 0;

		while($row = $data_query_ids_zonas->fetch_assoc())
		{
			if($cont_ids_zona > 0)
			{
				$ids_zona .= ',';
			}

			$ids_zona .= $row["id_zona"];

			$cont_ids_zona++;
		}
		// FIN LISTAR TODAS LAS ZONAS EN LA CUAL EL ASITENTE CONTABILIDAD PERTENECE

		// INICIO LISTAR TODAS LAS LIQUIDACIONES PENDIENTES POR ENVIAR A TESORERIA
		$query_ids_liquidacion_uno = "
							SELECT
								l.id AS liquidacion_id,
								'' AS fondo_asignado,
								'' AS total_liquidacion,
								'' AS total_movilidad
							FROM mepa_caja_chica_liquidacion l
								INNER JOIN mepa_asignacion_caja_chica a
								ON l.asignacion_id = a.id
								INNER JOIN tbl_razon_social rs
            					ON a.empresa_id = rs.id
							WHERE l.status = 1 AND a.status = 1 
								AND a.situacion_etapa_id = 6
								AND l.situacion_etapa_id_superior = 6 
								AND l.situacion_etapa_id_contabilidad = 6 
								AND l.situacion_jefe_cerrar_caja_chica IS NULL
								AND l.etapa_id_se_envio_a_tesoreria = 1 
								AND a.zona_asignacion_id IN (".$ids_zona.")
								".$where_redes."
							";

		$query_ids_liquidacion_dos = "
							SELECT
								l.id AS liquidacion_id,
								a.fondo_asignado,
								l.total_rendicion AS total_liquidacion,
								m.monto_cierre AS total_movilidad
							FROM mepa_caja_chica_liquidacion l
								INNER JOIN mepa_asignacion_caja_chica a
								ON l.asignacion_id = a.id
								INNER JOIN tbl_razon_social rs
            					ON a.empresa_id = rs.id
								LEFT JOIN mepa_caja_chica_movilidad m
								ON l.id_movilidad = m.id
							    INNER JOIN cont_etapa eec
								ON l.situacion_jefe_cerrar_caja_chica = eec.etapa_id
							WHERE l.status = 1 AND a.status = 1 
								AND (a.situacion_etapa_id = 6 OR a.situacion_etapa_id = 8)
								AND l.situacion_etapa_id_superior = 6 
								AND l.situacion_etapa_id_contabilidad = 6 
								AND l.situacion_jefe_cerrar_caja_chica = 12
								AND l.etapa_id_se_envio_a_tesoreria = 1 
								AND
							    (
									SELECT
										SUM(IFNULL(li.total_rendicion, 0) + IFNULL(mi.monto_cierre, 0))
									FROM mepa_caja_chica_liquidacion li
										INNER JOIN mepa_asignacion_caja_chica ai
										ON li.asignacion_id = ai.id
										LEFT JOIN mepa_caja_chica_movilidad mi
										ON li.id_movilidad = mi.id
									WHERE li.situacion_etapa_id_superior = 6 
					    				AND li.situacion_etapa_id_contabilidad = 6 
										AND li.situacion_etapa_id_tesoreria = 10
										AND ai.id = a.id
									ORDER BY l.id DESC
							    ) > a.fondo_asignado
								-- AND IFNULL(l.total_rendicion, 0) + IFNULL(m.monto_cierre, 0) > a.fondo_asignado
								AND a.zona_asignacion_id IN (".$ids_zona.")
								".$where_redes."
							";

		$query_todos.= $query_ids_liquidacion_uno;
		$query_todos.= "UNION ALL";
		$query_todos.= $query_ids_liquidacion_dos;

		$data_query_ids_liquidacion = $mysqli->query($query_todos);

		$ids_liquidacion = '';
		$cont_ids_liquidacion = 0;

		$arreglo_id_liquidacion_enviar_a_tesoreria = array();

		while($row = $data_query_ids_liquidacion->fetch_assoc())
		{
			if($cont_ids_liquidacion > 0)
			{
				$ids_liquidacion .= ',';
			}

			$ids_liquidacion .= $row["liquidacion_id"];

			$cont_ids_liquidacion++;

			array_push($arreglo_id_liquidacion_enviar_a_tesoreria, $row["liquidacion_id"]);
		}

		// FIN LISTAR TODAS LAS LIQUIDACIONES PENDIENTES POR ENVIAR A TESORERIA

		if($cont_ids_liquidacion > 0)
		{
			$query_select = 
			"
				SELECT
					a.id AS asignacion_id, a.usuario_asignado_id, l.id AS liquidacion_id,
					concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado,
					tp.dni AS usuario_asignado_dni,
					z.nombre AS zona,
					l.num_correlativo,
					a.fondo_asignado,
					IFNULL(l.total_rendicion, 0) AS total_liquidacion,
					IFNULL(m.monto_cierre, 0) AS total_movilidad,
					(
						SELECT
							SUM(IFNULL(li.total_rendicion, 0) + IFNULL(mi.monto_cierre, 0))
						FROM mepa_caja_chica_liquidacion li
							INNER JOIN mepa_asignacion_caja_chica ai
							ON li.asignacion_id = ai.id
							LEFT JOIN mepa_caja_chica_movilidad mi
							ON li.id_movilidad = mi.id
						WHERE li.situacion_etapa_id_superior = 6 
							AND li.situacion_etapa_id_contabilidad = 6 
							AND	li.etapa_id_se_envio_a_tesoreria = 1
							AND li.situacion_etapa_id_tesoreria = 10
							AND ai.id = a.id
						ORDER BY l.id DESC
					) AS total_calculado_a_reembolsar,
					l.situacion_etapa_id_contabilidad,
				    ec.situacion AS situacion_contabilidad,
					l.situacion_etapa_id_tesoreria,
				    et.situacion AS situacion_tesoreria,
				    l.etapa_id_se_envio_a_tesoreria,
		    		eet.situacion AS situacion_enviado_a_tesoreria,
		    		l.created_at AS fecha_solicitud,
					'' AS fecha_comprobante, l.situacion_jefe_cerrar_caja_chica
				FROM mepa_caja_chica_liquidacion l
					INNER JOIN mepa_asignacion_caja_chica a
					ON l.asignacion_id = a.id
					LEFT JOIN mepa_caja_chica_movilidad m
					ON l.id_movilidad = m.id
					INNER JOIN tbl_usuarios tu
					ON a.usuario_asignado_id = tu.id
					INNER JOIN tbl_personal_apt tp
					ON tu.personal_id = tp.id
					INNER JOIN mepa_zona_asignacion z
					ON a.zona_asignacion_id = z.id
					INNER JOIN cont_etapa ec
					ON l.situacion_etapa_id_contabilidad = ec.etapa_id
					INNER JOIN cont_etapa et
					ON l.situacion_etapa_id_tesoreria = et.etapa_id
					INNER JOIN cont_etapa eet
					ON l.etapa_id_se_envio_a_tesoreria = eet.etapa_id
				WHERE l.id IN (".$ids_liquidacion.")
			";

			$list_query = $mysqli->query($query_select);

			while ($row = $list_query->fetch_assoc())
			{
				$liquidacion_id = $row["liquidacion_id"];
				$situacion_jefe_cerrar_caja_chica = $row["situacion_jefe_cerrar_caja_chica"];
				$fondo_asignado = $row["fondo_asignado"];
				$total_calculado_a_reembolsar = $row["total_calculado_a_reembolsar"];
		    	
		    	
		    	//$suma_total = $row["total_liquidacion"] + $row["total_movilidad"];

				$suma_total = 0;
				
		    	if($situacion_jefe_cerrar_caja_chica == 12)
		    	{
		    		// SI LA CAJA CHICA ES CERRADA.
		    		// CALCULAR TODAS LAS CAJAS CHICA DE LA MISMA 
		    		// ASIGNACION DE LA CAJA CHICA QUE SE ESTA CERRANDO Y VERIFICAR EL SALDO.
		    		// SOLO PASA A TESORERIA EL MONTO EXCEDENTE.

		    		$suma_total = $row["total_calculado_a_reembolsar"] - $row["fondo_asignado"];
		    	}
		    	else
		    	{
		    		// SI LA CAJA CHICA NO FUE CERRADA, NO SE VERIFICA EL SALDO.
		    		// PASA A TESORERIA TODO EL MONTO DE LA CAJA CHICA.

		    		$suma_total = $row["total_liquidacion"] + $row["total_movilidad"];
		    	}
				
				$query_update = 
				"
					UPDATE mepa_caja_chica_liquidacion
					SET etapa_id_se_envio_a_tesoreria = 9,
						sub_total = '".$suma_total."'
					WHERE id = '".$liquidacion_id."'
				";

				$mysqli->query($query_update);

				if($mysqli->error)
				{
					$error .= $mysqli->error;

					$result["http_code"] = 400;
					$result["status"] = "Error";
					$result["error"] = $error;

					echo json_encode($result);
					exit();
				}

			}

			// INICIO: GUARDAR LA INFORMACION DE CAJAS CHICAS QUE SE ESTA ENVIANDO A TESORERIA

			$insert_enviar_a_tesoreria_cabecera = 
			"
				INSERT INTO mepa_contabilidad_liquidacion_enviada
				(
					status,
					user_created_id,
					created_at,
					user_updated_id,
					updated_at
				)
				VALUES
				(
					1,
					'".$login["id"]."', 
					'".date('Y-m-d H:i:s')."',
					'".$login["id"]."', 
					'".date('Y-m-d H:i:s')."'
				)
			";

			$mysqli->query($insert_enviar_a_tesoreria_cabecera);

			if($mysqli->error)
			{
				$error .= $mysqli->error;

				$result["http_code"] = 400;
				$result["status"] = "Error";
				$result["error"] = $error;

				echo json_encode($result);
				exit();
			}

			$id_cabecera = $mysqli->insert_id;

			foreach($arreglo_id_liquidacion_enviar_a_tesoreria as $valor)
			{
				$insert_enviar_a_tesoreria = 
				"
					INSERT INTO mepa_contabilidad_liquidacion_enviada_detalle
					(
						mepa_contabilidad_liquidacion_enviada_id,
						mepa_caja_chica_liquidacion_id,
						status
					)
					VALUES
					(
						'".$id_cabecera."',
						'".$valor."',
						'1'
					)
				";

				$mysqli->query($insert_enviar_a_tesoreria);

				if($mysqli->error)
				{
					$error .= $mysqli->error;

					$result["http_code"] = 400;
					$result["status"] = "Error";
					$result["error"] = $error;

					echo json_encode($result);
					exit();
				}
			}

			//FIN: GUARDAR LA INFORMACION DE CAJAS CHICAS QUE SE ESTA ENVIANDO A TESORERIA

			if ($error == '') 
			{
				$result["http_code"] = 200;
				$result["status"] = "Datos obtenidos de gestión.";
				$result["error"] = $error;
				
				send_email_notificar_liquidacion_aprobado($cont_ids_liquidacion);
			}
			else
			{
				$result["http_code"] = 400;
				$result["status"] = "Error";
				$result["error"] = $error;
			}
		}
		else
		{
			$result["http_code"] = 201;
			$result["status"] = "No existen registros.";
			$result["error"] = $error;
		}
	}
	else
	{
		$result["http_code"] = 400;
        $result["error"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
	}
	

}

function send_email_notificar_liquidacion_aprobado($cont_ids_liquidacion)
{
	include("db_connect.php");
	include("sys_login.php");

	$usuario_id = $login?$login['id']:null;
	$fecha_actual = date('Y-m-d');

	$host = $_SERVER["HTTP_HOST"];
	$titulo_email = "";

	$sel_query = $mysqli->query("
								SELECT
									u.id,
								    concat(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS usuario_atencion
								FROM tbl_usuarios u
									INNER JOIN tbl_personal_apt p
									ON u.personal_id = p.id
								WHERE u.id = '".$usuario_id."'
								LIMIT 1
							");

	$body = "";
	$body .= '<html>';

	while($sel = $sel_query->fetch_assoc())
	{
		$id = $sel['id'];
		$usuario_atencion = $sel['usuario_atencion'];
		

		$body .= '<div>';
			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

				$body .= '<thead>';
				
					$body .= '<tr>';
						$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
							$body .= '<b>Notificación de Cajas Chicas</b>';
						$body .= '</th>';
					$body .= '</tr>';

				$body .= '</thead>';

				$body .= '<tr>';
					$body .= '<td style="background-color: #ffffdd; width: 155px;"><b>Motivo:</b></td>';
					$body .= '<td>Se realizo el envió de las liquidaciones aprobadas</td>';
				$body .= '</tr>';

				$body .= '<tr>';
					$body .= '<td style="background-color: #ffffdd; width: 155px;"><b>Nº de atenciones:</b></td>';
					$body .= '<td>'.$cont_ids_liquidacion.'</td>';
				$body .= '</tr>';

				$body .= '<tr>';
					$body .= '<td style="background-color: #ffffdd; width: 155px;"><b>Atendido por:</b></td>';
					$body .= '<td>'.$usuario_atencion.'</td>';
				$body .= '</tr>';
			
				$body .= '<tr>';
					$body .= '<td style="background-color: #ffffdd"><b>Fecha de atención:</b></td>';
					$body .= '<td>'.$fecha_actual.'</td>';
				$body .= '</tr>';

			$body .= '</table>';
		$body .= '</div>';

	}
		$body .= '</html>';
		$body .= "";

	$sub_titulo_email = "";

	if(env('SEND_EMAIL') == 'test')
	{
		$sub_titulo_email = "TEST SISTEMAS: ";
	}

	$titulo_email = $sub_titulo_email."Gestion - Sistema Mesa de Partes: Notificación de Liquidaciones aprobadas por Contabilidad";
	
	$cc = [
	];

	$bcc = [
	];

	//INICIO: LISTAR USUARIOS
	$query_select_usuario = 
	"
		SELECT
			cg.id, cg.metodo, cg.status AS mepa_grupo_estado,
			cu.usuario_id, p.nombre, p.correo
		FROM mepa_mantenimiento_correo_grupo cg
			INNER JOIN mepa_mantenimiento_correo_usuario cu
			ON cg.id = cu.mepa_mantenimiento_correo_grupo_id
			LEFT JOIN tbl_usuarios u
			ON cu.usuario_id = u.id
			LEFT JOIN tbl_personal_apt p
			ON u.personal_id = p.id
		WHERE cg.metodo = 'mepa_envios_liquidaciones_a_tesoreria' 
			AND cg.status = 1 
			AND cu.status = 1
	";

	$sel_query_select_usuario = $mysqli->query($query_select_usuario);
	
	$row_count = $sel_query_select_usuario->num_rows;

	if ($row_count > 0)
	{
		while($sel = $sel_query_select_usuario->fetch_assoc())
		{
			if(!is_null($sel['correo']) AND !empty($sel['correo']))
			{
				array_push($cc, $sel['correo']);
			}
		}
	}
	//FIN: LISTAR USUARIOS

	//INICIO: LISTAR USUARIOS SISTEMAS DEL GRUPO - COPIA OCULTA
	$query_select_usuario_sistemas_cco = 
	"
		SELECT
			cg.id, cg.metodo, cg.status AS mepa_grupo_estado,
			cu.usuario_id, p.nombre, p.correo
		FROM mepa_mantenimiento_correo_grupo cg
			INNER JOIN mepa_mantenimiento_correo_usuario cu
			ON cg.id = cu.mepa_mantenimiento_correo_grupo_id
			LEFT JOIN tbl_usuarios u
			ON cu.usuario_id = u.id
			LEFT JOIN tbl_personal_apt p
			ON u.personal_id = p.id
		WHERE cg.metodo = 'mepa_area_sistemas_cco' 
			AND cg.status = 1 
			AND cu.status = 1
	";

	$sel_query_select_usuario_sistemas_cco = $mysqli->query($query_select_usuario_sistemas_cco);
	
	$row_count = $sel_query_select_usuario_sistemas_cco->num_rows;

	if ($row_count > 0)
	{
		while($sel = $sel_query_select_usuario_sistemas_cco->fetch_assoc())
		{
			if(!is_null($sel['correo']) AND !empty($sel['correo']))
			{
				array_push($bcc, $sel['correo']);
			}
		}
	}
	//FIN: LISTAR USUARIOS SISTEMAS DEL GRUPO - COPIA OCULTA

	$request = [
		"subject" => $titulo_email,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			//$filepath
		]
	];


	$mail = new PHPMailer(true);

	try 
	{
		$mail->isSMTP();
		$mail->Host = "smtp.gmail.com";
		$mail->SMTPAuth = true;
		$mail->Username = isset($request["Username"]) ? $request["Username"] : env('MAIL_GESTION_USER');
		$mail->Password = isset($request["Password"]) ? $request["Password"] : env('MAIL_GESTION_PASS');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;        
		$mail->Port = 465;
		$mail->From       = isset($request["From"]) ? $request["From"] : env('MAIL_GESTION_USER');
		$mail->FromName   = isset($request["FromName"]) ? $request["FromName"] : env('MAIL_GESTION_NAME');

		if(isset($request["cc"]))
		{
			foreach ($request["cc"] as $cc) 
			{
				$mail->addAddress($cc);
			}
		}

		if(isset($request["bcc"]))
		{
			foreach ($request["bcc"] as $bcc) 
			{
				$mail->addBCC($bcc);
			}
		}

		$mail->isHTML(true);
		$mail->Subject  = $request["subject"];
		$mail->Body     = $request["body"];
		$mail->CharSet = 'UTF-8';
		$mail->Encoding = 'base64';

		$mail->send();
		return true;

	} 
	catch (Exception $e) 
	{
		return false;
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_rendicion_caja_chica_listar_detalle_historial_enviado_a_tesoreria")
{
	$id_historial = $_POST['id_historial'];
	
	$query = "
		SELECT
			a.id AS asignacion_id, a.usuario_asignado_id, 
			l.id AS liquidacion_id,
			concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado,
			tp.dni AS usuario_asignado_dni,
			z.nombre AS zona,
			l.num_correlativo,
			IFNULL(l.total_rendicion, 0) AS total_liquidacion,
			IFNULL(m.monto_cierre, 0) AS total_movilidad
		FROM mepa_contabilidad_liquidacion_enviada_detalle ed
			INNER JOIN mepa_caja_chica_liquidacion l
			ON ed.mepa_caja_chica_liquidacion_id = l.id
			LEFT JOIN mepa_caja_chica_movilidad m
			ON l.id_movilidad = m.id
			INNER JOIN mepa_asignacion_caja_chica a
			ON l.asignacion_id = a.id
			INNER JOIN tbl_usuarios tu
			ON a.usuario_asignado_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN mepa_zona_asignacion z
			ON a.zona_asignacion_id = z.id
		WHERE ed.mepa_contabilidad_liquidacion_enviada_id = '".$id_historial."'
	";

	$list_query = $mysqli->query($query);

	$data =  array();
	$num = 1;

	while($reg = $list_query->fetch_object()) 
	{
		$suma_total = $reg->total_liquidacion + $reg->total_movilidad;

		$data[] = array(
			"0" => $num,
			"1" => $reg->usuario_asignado,
			"2" => $reg->usuario_asignado_dni,
			"3" => $reg->zona,
			"4" => $reg->num_correlativo,
			"5" => "S/ ".number_format($suma_total, 2, '.', ',')
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
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_rendicion_caja_chica_reporte_detalle_historial_enviado_a_tesoreria")
{
	$id_historial = $_POST['id_historial'];
	
	$query = "
		SELECT
			a.id AS asignacion_id, a.usuario_asignado_id, 
			l.id AS liquidacion_id,
			concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado,
			tp.dni AS usuario_asignado_dni,
			z.nombre AS zona,
			l.num_correlativo,
			IFNULL(l.total_rendicion, 0) AS total_liquidacion,
			IFNULL(m.monto_cierre, 0) AS total_movilidad
		FROM mepa_contabilidad_liquidacion_enviada_detalle ed
			INNER JOIN mepa_caja_chica_liquidacion l
			ON ed.mepa_caja_chica_liquidacion_id = l.id
			LEFT JOIN mepa_caja_chica_movilidad m
			ON l.id_movilidad = m.id
			INNER JOIN mepa_asignacion_caja_chica a
			ON l.asignacion_id = a.id
			INNER JOIN tbl_usuarios tu
			ON a.usuario_asignado_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN mepa_zona_asignacion z
			ON a.zona_asignacion_id = z.id
		WHERE ed.mepa_contabilidad_liquidacion_enviada_id = '".$id_historial."'
	";


	$list_query = $mysqli->query($query);

	//PROCEDEMOS A CREAR LA CARPETASI EN CASO NO EXISTA
	$path = "/var/www/html/files_bucket/mepa/descargas/contabilidad/historial_enviado_a_tesoreria/";

	if (!is_dir($path)) 
	{
		mkdir($path, 0777, true);
	}

	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/mepa/descargas/contabilidad/historial_enviado_a_tesoreria/*'); //obtenemos todos los nombres de los ficheros
	foreach($files as $file)
	{
	    if(is_file($file))
	    unlink($file); //elimino el fichero
	}


	require_once '../phpexcel/classes/PHPExcel.php';
	$objPHPExcel = new PHPExcel();

	// Se asignan las propiedades del libro
	$objPHPExcel->getProperties()->setCreator("AT") // Nombre del autor
    ->setDescription("Reporte"); //Descripción

	$tituloReporte = "Reporte Caja Chica - Liquidación";

	$titulosColumnas = array('Nº', 'USUARIO', 'DNI', 'ZONA', 'Nº CAJA', 'IMPORTE');

	// Se agregan los titulos del reporte
	$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A1', $titulosColumnas[0])  //Titulo de las columnas
    ->setCellValue('B1', $titulosColumnas[1])
    ->setCellValue('C1', $titulosColumnas[2])
    ->setCellValue('D1', $titulosColumnas[3])
    ->setCellValue('E1', $titulosColumnas[4])
    ->setCellValue('F1', $titulosColumnas[5]);

	$cont = 0;

	//Numero de fila donde se va a comenzar a rellenar los datos
	$i = 2; 

	//Se agregan los datos a la lista del reporte
	while ($fila = $list_query->fetch_array()) 
	{
		$cont ++;

		$suma_total = $fila['total_liquidacion'] + $fila['total_movilidad'];

		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('A'.$i, $cont)
		->setCellValue('B'.$i, $fila['usuario_asignado'])
		->setCellValue('C'.$i, $fila['usuario_asignado_dni'])
		->setCellValue('D'.$i, $fila['zona'])
		->setCellValue('E'.$i, $fila['num_correlativo'])
		->setCellValue('F'.$i, $suma_total);
		
		$i++;
	}

	$estiloNombresColumnas = array(
		'font' => array(
	        'name'      => 'Calibri',
	        'bold'      => true,
	        'italic'    => false,
	        'strike'    => false,
	        'size' =>10,
	        'color'     => array(
	            'rgb' => 'ffffff'
	        )
	    ),
	    'fill' => array(
			  'type'  => PHPExcel_Style_Fill::FILL_SOLID,
			  'color' => array(
			        'rgb' => '000000')
			),
	    'alignment' =>  array(
	        'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        'wrap'      => false
	    )
	);

	$estiloInformacion = new PHPExcel_Style();
	$estiloInformacion->applyFromArray( array(
	    'font' => array(
	        'name'  => 'Arial',
	        'color' => array(
	            'rgb' => '000000'
	        )
	    )
	));

	$estilo_centrar = array(
	    'alignment' =>  array(
	        'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        'wrap'      => false
	    )
	);

	$objPHPExcel->getActiveSheet()->getStyle('A1:F1')->applyFromArray($estiloNombresColumnas);
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:F".($i-1));
	$objPHPExcel->getActiveSheet()->getStyle('A1:A'.($i-1))->applyFromArray($estilo_centrar);

	$objPHPExcel->getActiveSheet()->getStyle('F2:F'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');

	// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
	for($i = 'A'; $i <= 'Z'; $i++)
	{
	    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}

	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('Mesa de Partes - Liquidación');
	  
	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);
	
	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Caja Chica Liquidación AT.xls');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$excel_path = '/var/www/html/files_bucket/mepa/descargas/contabilidad/historial_enviado_a_tesoreria/Historial Caja Chica enviado a Tesorería.xls';
	$excel_path_download = '/files_bucket/mepa/descargas//contabilidad/historial_enviado_a_tesoreria/Historial Caja Chica enviado a Tesorería.xls';

	try 
	{
		$objWriter->save($excel_path);
	} 
	catch (PHPExcel_Writer_Exception $e) 
	{
		echo json_encode(["error" => $e]);
		exit;
	}

	echo json_encode(array(
		"ruta_archivo" => $excel_path_download
	));
	exit;
}

if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_rendicion_caja_chica_reporte_enviado_a_tesoreria_todos")
{
	$param_tipo_red = $_POST['param_tipo_red'];
	
	$where_redes = "";

	if($param_tipo_red != 0)
	{
		$where_redes = " WHERE rs.red_id = '".$param_tipo_red."' ";
	}
	
	$query = "
		SELECT
			a.id AS asignacion_id, a.usuario_asignado_id, 
			l.id AS liquidacion_id,
			concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado,
			tp.dni AS usuario_asignado_dni,
			z.nombre AS zona,
			l.num_correlativo,
			IFNULL(l.total_rendicion, 0) AS total_liquidacion,
			IFNULL(m.monto_cierre, 0) AS total_movilidad
		FROM mepa_contabilidad_liquidacion_enviada_detalle ed
			INNER JOIN mepa_caja_chica_liquidacion l
			ON ed.mepa_caja_chica_liquidacion_id = l.id
			LEFT JOIN mepa_caja_chica_movilidad m
			ON l.id_movilidad = m.id
			INNER JOIN mepa_asignacion_caja_chica a
			ON l.asignacion_id = a.id
			INNER JOIN tbl_usuarios tu
			ON a.usuario_asignado_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN mepa_zona_asignacion z
			ON a.zona_asignacion_id = z.id
			INNER JOIN tbl_razon_social rs
            ON a.empresa_id = rs.id
		".$where_redes."
	";


	$list_query = $mysqli->query($query);

	//PROCEDEMOS A CREAR LA CARPETASI EN CASO NO EXISTA
	$path = "/var/www/html/files_bucket/mepa/descargas/contabilidad/historial_enviado_a_tesoreria/";

	if (!is_dir($path)) 
	{
		mkdir($path, 0777, true);
	}

	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/mepa/descargas/contabilidad/historial_enviado_a_tesoreria/*'); //obtenemos todos los nombres de los ficheros
	foreach($files as $file)
	{
	    if(is_file($file))
	    unlink($file); //elimino el fichero
	}


	require_once '../phpexcel/classes/PHPExcel.php';
	$objPHPExcel = new PHPExcel();

	// Se asignan las propiedades del libro
	$objPHPExcel->getProperties()->setCreator("AT") // Nombre del autor
    ->setDescription("Reporte"); //Descripción

	$tituloReporte = "Reporte Caja Chica - Liquidación";

	$titulosColumnas = array('Nº', 'USUARIO', 'DNI', 'ZONA', 'Nº CAJA', 'IMPORTE');

	// Se agregan los titulos del reporte
	$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A1', $titulosColumnas[0])  //Titulo de las columnas
    ->setCellValue('B1', $titulosColumnas[1])
    ->setCellValue('C1', $titulosColumnas[2])
    ->setCellValue('D1', $titulosColumnas[3])
    ->setCellValue('E1', $titulosColumnas[4])
    ->setCellValue('F1', $titulosColumnas[5]);

	$cont = 0;

	//Numero de fila donde se va a comenzar a rellenar los datos
	$i = 2; 

	//Se agregan los datos a la lista del reporte
	while ($fila = $list_query->fetch_array()) 
	{
		$cont ++;

		$suma_total = $fila['total_liquidacion'] + $fila['total_movilidad'];

		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('A'.$i, $cont)
		->setCellValue('B'.$i, $fila['usuario_asignado'])
		->setCellValue('C'.$i, $fila['usuario_asignado_dni'])
		->setCellValue('D'.$i, $fila['zona'])
		->setCellValue('E'.$i, $fila['num_correlativo'])
		->setCellValue('F'.$i, $suma_total);
		
		$i++;
	}

	$estiloNombresColumnas = array(
		'font' => array(
	        'name'      => 'Calibri',
	        'bold'      => true,
	        'italic'    => false,
	        'strike'    => false,
	        'size' =>10,
	        'color'     => array(
	            'rgb' => 'ffffff'
	        )
	    ),
	    'fill' => array(
			  'type'  => PHPExcel_Style_Fill::FILL_SOLID,
			  'color' => array(
			        'rgb' => '000000')
			),
	    'alignment' =>  array(
	        'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        'wrap'      => false
	    )
	);

	$estiloInformacion = new PHPExcel_Style();
	$estiloInformacion->applyFromArray( array(
	    'font' => array(
	        'name'  => 'Arial',
	        'color' => array(
	            'rgb' => '000000'
	        )
	    )
	));

	$estilo_centrar = array(
	    'alignment' =>  array(
	        'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        'wrap'      => false
	    )
	);

	$objPHPExcel->getActiveSheet()->getStyle('A1:F1')->applyFromArray($estiloNombresColumnas);
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:F".($i-1));
	$objPHPExcel->getActiveSheet()->getStyle('A1:A'.($i-1))->applyFromArray($estilo_centrar);

	$objPHPExcel->getActiveSheet()->getStyle('F2:F'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');

	// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
	for($i = 'A'; $i <= 'Z'; $i++)
	{
	    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}

	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('Mesa de Partes - Liquidación');
	  
	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);
	
	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Caja Chica Liquidación AT.xls');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$excel_path = '/var/www/html/files_bucket/mepa/descargas/contabilidad/historial_enviado_a_tesoreria/Historial Caja Chica enviado a Tesorería.xls';
	$excel_path_download = '/files_bucket/mepa/descargas//contabilidad/historial_enviado_a_tesoreria/Historial Caja Chica enviado a Tesorería.xls';

	try 
	{
		$objWriter->save($excel_path);
	} 
	catch (PHPExcel_Writer_Exception $e) 
	{
		echo json_encode(["error" => $e]);
		exit;
	}

	echo json_encode(array(
		"ruta_archivo" => $excel_path_download
	));
	exit;
}

if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_solicitud_rendicion_caja_chica_tipo_reporte_solicitud_liquidacion_btn_export")
{
	$param_tipo_red = $_POST['param_tipo_red'];
	$param_zona = $_POST['param_zona'];
	$param_usuario = $_POST['param_usuario'];
	$param_situacion_contabilidad = $_POST['param_situacion_contabilidad'];

	$login_usuario_id = $login?$login['id']:null;

	$query = "";
	$where_redes = "";
	$where_zona = "";
	$where_usuario = "";
	$where_situacion_contabilidad = "";

	if($param_tipo_red != 0)
	{
		$where_redes = " AND rs.red_id = '".$param_tipo_red."' ";
	}
	
	if($param_situacion_contabilidad != 0)
	{
		if($param_situacion_contabilidad == 1)
		{
			$where_situacion_contabilidad = " AND l.situacion_etapa_id_contabilidad IN (1, 7)";
		}
		else if($param_situacion_contabilidad == 6)
		{
			$where_situacion_contabilidad = " AND l.situacion_etapa_id_contabilidad IN (6)";
		}
	}

	if($param_zona == 0)
	{
		$query_zonas_atencion_usuario = 
		"
            SELECT
                z.id, z.nombre
            FROM mepa_atencion_solicitud_zona sz
                INNER JOIN mepa_zona_asignacion z
                ON sz.id_zona = z.id
            WHERE sz.id_usuario = '".$login_usuario_id."'
        ";

        $list_query_zonas_atencion_usuario = $mysqli->query($query_zonas_atencion_usuario);

		$row_count_zonas_atencion_usuario = $list_query_zonas_atencion_usuario->num_rows;

		$ids_zonas_atencion_registrado = '';
		$contador_ids = 0;
		
		if ($row_count_zonas_atencion_usuario > 0) 
		{
			while ($row = $list_query_zonas_atencion_usuario->fetch_assoc()) 
			{
				if ($contador_ids > 0) 
				{
					$ids_zonas_atencion_registrado .= ',';
				}

				$ids_zonas_atencion_registrado .= $row["id"];			
				$contador_ids++;
			}

			$where_zona = " AND a.zona_asignacion_id IN ($ids_zonas_atencion_registrado) ";
		}
		else
		{
			$where_zona = " AND a.zona_asignacion_id IN (0) ";
		}
	}
	else if($param_zona != 0)
	{
		$where_zona = " AND a.zona_asignacion_id = '".$param_zona."' ";
	}

	if($param_usuario != 0)
	{
		$where_usuario = " AND a.usuario_asignado_id = '".$param_usuario."' ";
	}

	$query = "
		SELECT
			a.id AS asignacion_id, a.usuario_asignado_id, l.id AS liquidacion_id,
			concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado,
			tp.dni AS usuario_asignado_dni,
			z.nombre AS zona,
			l.num_correlativo,
			l.total_rendicion AS total_liquidacion,
			m.monto_cierre AS total_movilidad,
			l.situacion_etapa_id_contabilidad,
		    ec.situacion AS situacion_contabilidad,
			l.situacion_etapa_id_tesoreria,
		    et.situacion AS situacion_tesoreria,
		    l.etapa_id_se_envio_a_tesoreria,
    		eet.situacion AS situacion_enviado_a_tesoreria,
    		l.created_at AS fecha_solicitud,
			'' AS fecha_comprobante, l.flag_envio_documento_fisico
		FROM mepa_caja_chica_liquidacion l
			INNER JOIN mepa_asignacion_caja_chica a
			ON l.asignacion_id = a.id
			LEFT JOIN mepa_caja_chica_movilidad m
			ON l.id_movilidad = m.id
			INNER JOIN tbl_usuarios tu
			ON a.usuario_asignado_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN mepa_zona_asignacion z
			ON a.zona_asignacion_id = z.id
			INNER JOIN cont_etapa ec
			ON l.situacion_etapa_id_contabilidad = ec.etapa_id
			INNER JOIN cont_etapa et
			ON l.situacion_etapa_id_tesoreria = et.etapa_id
			INNER JOIN cont_etapa eet
			ON l.etapa_id_se_envio_a_tesoreria = eet.etapa_id
			INNER JOIN tbl_razon_social rs
            ON a.empresa_id = rs.id
		WHERE l.status = 1 AND a.status = 1 AND l.situacion_etapa_id_superior = 6 
			".$where_redes." 
			".$where_zona." 
			".$where_usuario." 
			".$where_situacion_contabilidad."
	";

	$list_query = $mysqli->query($query);

	//PROCEDEMOS A CREAR LA CARPETASI EN CASO NO EXISTA
	$path = "/var/www/html/files_bucket/mepa/descargas/contabilidad/solicitud_liquidacion/";

	if (!is_dir($path)) 
	{
		mkdir($path, 0777, true);
	}

	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/mepa/descargas/contabilidad/solicitud_liquidacion/*'); //obtenemos todos los nombres de los ficheros
	foreach($files as $file)
	{
	    if(is_file($file))
	    unlink($file); //elimino el fichero
	}


	require_once '../phpexcel/classes/PHPExcel.php';
	$objPHPExcel = new PHPExcel();

	// Se asignan las propiedades del libro
	$objPHPExcel->getProperties()->setCreator("AT") // Nombre del autor
    ->setDescription("Reporte"); //Descripción

	$tituloReporte = "Reporte Solcitud Liquidación";

	$titulosColumnas = array('Nº', 'USUARIO SOLICITANTE', 'DNI', 'ZONA', 'Nº CAJA', 'IMPORTE', 'F. SOLICITUD', 'ESTADO CONTABILIDAD', 'ENVIADO A TESORERIA', 'DOCUMENTO FISICO');

	// Se agregan los titulos del reporte
	$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A1', $titulosColumnas[0])  //Titulo de las columnas
    ->setCellValue('B1', $titulosColumnas[1])
    ->setCellValue('C1', $titulosColumnas[2])
    ->setCellValue('D1', $titulosColumnas[3])
    ->setCellValue('E1', $titulosColumnas[4])
    ->setCellValue('F1', $titulosColumnas[5])
    ->setCellValue('G1', $titulosColumnas[6])
    ->setCellValue('H1', $titulosColumnas[7])
    ->setCellValue('I1', $titulosColumnas[8])
    ->setCellValue('J1', $titulosColumnas[9]);

    //Se agregan los datos a la lista del reporte
	$cont = 0;

	$i = 2; //Numero de fila donde se va a comenzar a rellenar los datos
	while ($fila = $list_query->fetch_array()) 
	{
		$cont ++;

		$suma_total = $fila['total_liquidacion'] + $fila['total_movilidad'];
		$flag_envio_documento_fisico = $fila['flag_envio_documento_fisico'] ? 'Ok' : 'Pendiente';

		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('A'.$i, $cont)
		->setCellValue('B'.$i, $fila['usuario_asignado'])
		->setCellValue('C'.$i, $fila['usuario_asignado_dni'])
		->setCellValue('D'.$i, $fila['zona'])
		->setCellValue('E'.$i, $fila['num_correlativo'])
		->setCellValue('F'.$i, 'S/ '.$suma_total)
		->setCellValue('G'.$i, $fila['fecha_solicitud'])
		->setCellValue('H'.$i, $fila['situacion_contabilidad'])
		->setCellValue('I'.$i, $fila['situacion_enviado_a_tesoreria'])
		->setCellValue('J'.$i, $flag_envio_documento_fisico);
		
		$i++;
	}

	$estiloNombresColumnas = array(
		'font' => array(
	        'name'      => 'Calibri',
	        'bold'      => true,
	        'italic'    => false,
	        'strike'    => false,
	        'size' =>10,
	        'color'     => array(
	            'rgb' => 'ffffff'
	        )
	    ),
	    'fill' => array(
			  'type'  => PHPExcel_Style_Fill::FILL_SOLID,
			  'color' => array(
			        'rgb' => '000000')
			),
	    'alignment' =>  array(
	        'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        'wrap'      => false
	    )
	);

	$estiloInformacion = new PHPExcel_Style();
	$estiloInformacion->applyFromArray( array(
	    'font' => array(
	        'name'  => 'Arial',
	        'color' => array(
	            'rgb' => '000000'
	        )
	    )
	));

	$estilo_centrar = array(
	    'alignment' =>  array(
	        'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        'wrap'      => false
	    )
	);

	$objPHPExcel->getActiveSheet()->getStyle('A1:J1')->applyFromArray($estiloNombresColumnas);
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:J".($i-1));
	$objPHPExcel->getActiveSheet()->getStyle('A1:A'.($i-1))->applyFromArray($estilo_centrar);
	$objPHPExcel->getActiveSheet()->getStyle('C1:C'.($i-1))->applyFromArray($estilo_centrar);
	$objPHPExcel->getActiveSheet()->getStyle('E1:E'.($i-1))->applyFromArray($estilo_centrar);
	$objPHPExcel->getActiveSheet()->getStyle('F1:F'.($i-1))->applyFromArray($estilo_centrar);
	$objPHPExcel->getActiveSheet()->getStyle('G1:G'.($i-1))->applyFromArray($estilo_centrar);
	$objPHPExcel->getActiveSheet()->getStyle('H1:H'.($i-1))->applyFromArray($estilo_centrar);
	$objPHPExcel->getActiveSheet()->getStyle('I1:I'.($i-1))->applyFromArray($estilo_centrar);
	$objPHPExcel->getActiveSheet()->getStyle('J1:J'.($i-1))->applyFromArray($estilo_centrar);

	$objPHPExcel->getActiveSheet()->getStyle('F2:F'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');

	// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
	for($i = 'A'; $i <= 'Z'; $i++)
	{
	    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}

	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('Mesa de Partes - Liquidación');
	  
	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);
	
	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Reporte Solcitud Liquidación.xls" ');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$excel_path = '/var/www/html/files_bucket/mepa/descargas/contabilidad/solicitud_liquidacion/Reporte Solcitud Liquidación.xls';
	$excel_path_download = '/files_bucket/mepa/descargas/contabilidad/solicitud_liquidacion/Reporte Solcitud Liquidación.xls';

	try 
	{
		$objWriter->save($excel_path);
	} 
	catch (Exception $e)
	{
		echo json_encode(["error" => $e]);
		exit;
	}

	echo json_encode(array(
		"ruta_archivo" => $excel_path_download
	));
	exit;
}
if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_detalle_solicitud_liquidacion_btn_export")
{
	$liquidacion_id = $_POST['liquidacion_id'];

	$queryResumenSolicitud = "
		SELECT
			a.id AS asignacion_id, a.usuario_asignado_id, l.id AS liquidacion_id,
			concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado,
			tp.dni AS usuario_asignado_dni,
			z.nombre AS zona,
			l.num_correlativo,
			l.total_rendicion AS total_liquidacion,
			m.monto_cierre AS total_movilidad,
			l.situacion_etapa_id_contabilidad,
		    ec.situacion AS situacion_contabilidad,
			l.situacion_etapa_id_tesoreria,
			l.se_aplica_movilidad, l.id_movilidad, 
		    et.situacion AS situacion_tesoreria,
		    l.etapa_id_se_envio_a_tesoreria,
    		eet.situacion AS situacion_enviado_a_tesoreria,
    		l.created_at AS fecha_solicitud,
			'' AS fecha_comprobante, l.flag_envio_documento_fisico
		FROM mepa_caja_chica_liquidacion l
			INNER JOIN mepa_asignacion_caja_chica a
			ON l.asignacion_id = a.id
			LEFT JOIN mepa_caja_chica_movilidad m
			ON l.id_movilidad = m.id
			INNER JOIN tbl_usuarios tu
			ON a.usuario_asignado_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN mepa_zona_asignacion z
			ON a.zona_asignacion_id = z.id
			INNER JOIN cont_etapa ec
			ON l.situacion_etapa_id_contabilidad = ec.etapa_id
			INNER JOIN cont_etapa et
			ON l.situacion_etapa_id_tesoreria = et.etapa_id
			INNER JOIN cont_etapa eet
			ON l.etapa_id_se_envio_a_tesoreria = eet.etapa_id
		WHERE l.status = 1 AND a.status = 1 AND l.situacion_etapa_id_superior = 6 AND l.id= '".$liquidacion_id."'
	";
	$list_query_resumen = $mysqli->query($queryResumenSolicitud)->fetch_assoc();

	//PROCEDEMOS A CREAR LA CARPETASI EN CASO NO EXISTA
	$path = "/var/www/html/files_bucket/mepa/descargas/contabilidad/solicitud_liquidacion/";

	if (!is_dir($path)) 
	{
		mkdir($path, 0777, true);
	}

	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/mepa/descargas/contabilidad/solicitud_liquidacion/*'); //obtenemos todos los nombres de los ficheros
	foreach($files as $file)
	{
	    if(is_file($file))
	    unlink($file); //elimino el fichero
	}


	require_once '../phpexcel/classes/PHPExcel.php';
	$objPHPExcel = new PHPExcel();

	// Se asignan las propiedades del libro
	$objPHPExcel->getProperties()->setCreator("AT") // Nombre del autor
    ->setDescription("Reporte"); //Descripción

	$tituloReporte = "Reporte Solcitud Liquidación";

	/////////////////////////////////////////////////////////////////////////
	// DETALLE LIQUIDACIÓN
	/////////////////////////////////////////////////////////////////////////
	$sel_query_detalle_liquidacion = $mysqli->query("
		SELECT
			dl.id, dl.mepa_caja_chica_liquidacion_id, 
			dl.item, dl.fecha_documento, dl.tipo_documento, 
			td.nombre AS tipo_documento_nombre, 
			dl.serie_comprobante, dl.num_comprobante,
			dl.centro_costo,
			UPPER(dl.detalle) AS detalle, 
			dl.nombre_file, dl.extension, 
			dl.ruta AS detalle_liquidacion_documento_ruta,
			dl.download_file,
			dl.importe,
			dl.tasa_igv,
			COALESCE(dl.ruc, '') AS ruc,
			dl.codigo_provision_contable,
			COALESCE(CONCAT(mtp.cuenta_contable, ' - ', mtp.nombre), '') AS nombre_cuenta_contable,
			dl.download_file_xml
		FROM mepa_detalle_caja_chica_liquidacion dl
		INNER JOIN mepa_tipo_documento td
			ON dl.tipo_documento = td.id
		INNER JOIN mepa_tipo_documento mtp
			ON mtp.id=dl.codigo_provision_contable
		WHERE dl.mepa_caja_chica_liquidacion_id = '".$liquidacion_id."'
			");

	$row_count_detalle = $sel_query_detalle_liquidacion->num_rows;
								
	if($row_count_detalle > 0)
		{
			$titulosColumnas = array('Nº', 'F. Documento', 'Tipo Documento', 'Serie Comprobante', 'Nº Comprobante', 'Centro Costo', 'Detalle', 'Importe', 'IGV', 'RUC','Cuenta contable');

			// Se agregan los titulos del reporte
			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A1', $titulosColumnas[0])  //Titulo de las columnas
				->setCellValue('B1', $titulosColumnas[1])
				->setCellValue('C1', $titulosColumnas[2])
				->setCellValue('D1', $titulosColumnas[3])
				->setCellValue('E1', $titulosColumnas[4])
				->setCellValue('F1', $titulosColumnas[5])
				->setCellValue('G1', $titulosColumnas[6])
				->setCellValue('H1', $titulosColumnas[7])
				->setCellValue('I1', $titulosColumnas[8])
				->setCellValue('J1', $titulosColumnas[9])
				->setCellValue('K1', $titulosColumnas[10]);

			//Se agregan los datos a la lista del reporte
			$cont = 1;
			$i = 2; 
			while($sel_deta_liqui = $sel_query_detalle_liquidacion->fetch_assoc()){
					$detalle_liquidacion_id = $sel_deta_liqui["id"];
					$detalle_liquidacion_item = $sel_deta_liqui["item"];
					$detalle_liquidacion_fecha_documento = $sel_deta_liqui["fecha_documento"];
					$detalle_liquidacion_tipo_documento = $sel_deta_liqui["tipo_documento"];
					$detalle_liquidacion_tipo_documento_nombre = $sel_deta_liqui["tipo_documento_nombre"];
					$detalle_liquidacion_serie_comprobante = $sel_deta_liqui["serie_comprobante"];
					$detalle_liquidacion_num_comprobante = $sel_deta_liqui["num_comprobante"];
					$detalle_liquidacion_centro_costo = $sel_deta_liqui["centro_costo"];
					$detalle_liquidacion_detalle = $sel_deta_liqui["detalle"];
					$detalle_liquidacion_nombre_file = $sel_deta_liqui["nombre_file"];
					$extension = $sel_deta_liqui["extension"];
					$detalle_liquidacion_documento_ruta = $sel_deta_liqui["detalle_liquidacion_documento_ruta"];
					$detalle_liquidacion_download_file = $sel_deta_liqui["download_file"];
					$detalle_liquidacion_importe = $sel_deta_liqui["importe"];
					$detalle_liquidacion_tasa_igv = $sel_deta_liqui["tasa_igv"];
					$detalle_liquidacion_ruc = $sel_deta_liqui["ruc"];
					//$detalle_liquidacion_codigo_provision = $sel_deta_liqui["codigo_provision_contable"];
					$detalle_liquidacion_cuenta_contable = $sel_deta_liqui["nombre_cuenta_contable"];
					$detalle_liquidacion_download_file_xml = $sel_deta_liqui["download_file_xml"];

					$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue('A'.$i, $cont)
						->setCellValue('B'.$i, $detalle_liquidacion_fecha_documento)
						->setCellValue('C'.$i, $detalle_liquidacion_tipo_documento)
						->setCellValue('D'.$i, $detalle_liquidacion_serie_comprobante)
						->setCellValue('E'.$i, $detalle_liquidacion_num_comprobante)
						->setCellValue('F'.$i, $detalle_liquidacion_centro_costo)
						->setCellValue('G'.$i, $detalle_liquidacion_detalle)
						->setCellValue('H'.$i, $detalle_liquidacion_importe)
						->setCellValue('I'.$i, $detalle_liquidacion_tasa_igv)
						->setCellValue('J'.$i, $detalle_liquidacion_ruc)
						->setCellValue('K'.$i, $detalle_liquidacion_cuenta_contable);
					
					$i++;
					$cont++;
				}
		$estiloNombresColumnas = array(
					'font' => array(
						'name'      => 'Calibri',
						'bold'      => true,
						'italic'    => false,
						'strike'    => false,
						'size' =>10,
						'color'     => array(
							'rgb' => 'ffffff'
						)
					),
					'fill' => array(
						  'type'  => PHPExcel_Style_Fill::FILL_SOLID,
						  'color' => array(
								'rgb' => '000000')
						),
					'alignment' =>  array(
						'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
						'wrap'      => false
					)
				);
			
		$estiloInformacion = new PHPExcel_Style();
		$estiloInformacion->applyFromArray( array(
					'font' => array(
						'name'  => 'Arial',
						'color' => array(
							'rgb' => '000000'
						)
					)
				));
			
				$estilo_centrar = array(
					'alignment' =>  array(
						'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
						'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
						'wrap'      => false
					)
				);
				// Recorre todas las filas mayores a 1 y aplica el ajuste automático de altura
		for ($i = 2; $i <= $objPHPExcel->getActiveSheet()->getHighestRow(); $i++) {
					$objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(18);
		}
		$objPHPExcel->getActiveSheet()->getStyle('A1:K1')->applyFromArray($estiloNombresColumnas);
		$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A2:K".($i-1));
		$objPHPExcel->getActiveSheet()->getStyle('A1:A'.($i-1))->applyFromArray($estilo_centrar);
		$objPHPExcel->getActiveSheet()->getStyle('B1:B'.($i-1))->applyFromArray($estilo_centrar);
		$objPHPExcel->getActiveSheet()->getStyle('C1:C'.($i-1))->applyFromArray($estilo_centrar);
		$objPHPExcel->getActiveSheet()->getStyle('D1:D'.($i-1))->applyFromArray($estilo_centrar);
		$objPHPExcel->getActiveSheet()->getStyle('E1:E'.($i-1))->applyFromArray($estilo_centrar);
		$objPHPExcel->getActiveSheet()->getStyle('F1:F'.($i-1))->applyFromArray($estilo_centrar);
		$objPHPExcel->getActiveSheet()->getStyle('G1:G'.($i-1))->applyFromArray($estilo_centrar);
		$objPHPExcel->getActiveSheet()->getStyle('H1:H'.($i-1))->applyFromArray($estilo_centrar);
		$objPHPExcel->getActiveSheet()->getStyle('I1:I'.($i-1))->applyFromArray($estilo_centrar);
		$objPHPExcel->getActiveSheet()->getStyle('J1:J'.($i-1))->applyFromArray($estilo_centrar);
			
		$objPHPExcel->getActiveSheet()->getStyle('H2:H'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');

		
	}
		
	/////////////////////////////////////////////////////////////////////////
	// DETALLE MOVILIDAD
	/////////////////////////////////////////////////////////////////////////
	if ($list_query_resumen["id_movilidad"] != null) {
		$id_movilidad=$list_query_resumen["id_movilidad"];
		$sel_query_detalle_movilidad = $mysqli->query("
			SELECT
				dm.id, dm.id_mepa_caja_chica_movilidad, dm.fecha, dm.partida_destino, dm.centro_costo,
				dm.motivo_traslado, dm.monto
			FROM mepa_caja_chica_movilidad_detalle dm
			WHERE dm.id_mepa_caja_chica_movilidad =  '".$id_movilidad."' AND dm.estado = 1
			");

		$row_count_detalle_movilidad = $sel_query_detalle_movilidad->num_rows;
							
		if($row_count_detalle_movilidad > 0)
		{
			$i++;

			$titulosColumnasMovilidad = array('Nº', 'ID', 'Fecha', 'Partida - Destino', 'Motivo', 'Monto');

			// Se agregan los titulos del reporte
			$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A'.$i, $titulosColumnasMovilidad[0])  //Titulo de las columnas
				->setCellValue('B'.$i, $titulosColumnasMovilidad[1])
				->setCellValue('C'.$i, $titulosColumnasMovilidad[2])
				->setCellValue('D'.$i, $titulosColumnasMovilidad[3])
				->setCellValue('E'.$i, $titulosColumnasMovilidad[4])
				->setCellValue('F'.$i, $titulosColumnasMovilidad[5]);

			$i++;
			$inicio_detalle_movilidad= $i;
			//Se agregan los datos a la lista del reporte
			$cont = 1;
			while($sel_deta_movilidad = $sel_query_detalle_movilidad->fetch_assoc()){
				$detalle_movilidad_id = $sel_deta_movilidad["id"];
				$detalle_movilidad_fecha = $sel_deta_movilidad["fecha"];
				$detalle_movilidad_partida_destino = $sel_deta_movilidad["partida_destino"];
				$detalle_movilidad_centro_costo = $sel_deta_movilidad["centro_costo"];
				$detalle_movilidad_motivo_traslado = $sel_deta_movilidad["motivo_traslado"];
				$detalle_movilidad_monto = $sel_deta_movilidad["monto"];

				$objPHPExcel->setActiveSheetIndex(0)
						->setCellValue('A'.$i, $cont)
						->setCellValue('B'.$i, $detalle_movilidad_id)
						->setCellValue('C'.$i, $detalle_movilidad_fecha)
						->setCellValue('D'.$i, $detalle_movilidad_partida_destino)
						->setCellValue('E'.$i, $detalle_movilidad_motivo_traslado)
						->setCellValue('F'.$i, $detalle_movilidad_monto);
				$i++;
				$cont++;
				}
			}

		$estiloNombresColumnas = array(
			'font' => array(
				'name'      => 'Calibri',
				'bold'      => true,
				'italic'    => false,
				'strike'    => false,
				'size' =>10,
				'color'     => array(
					'rgb' => 'ffffff'
				)
			),
			'fill' => array(
				  'type'  => PHPExcel_Style_Fill::FILL_SOLID,
				  'color' => array(
						'rgb' => '000000')
				),
			'alignment' =>  array(
				'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
				'wrap'      => false
			)
		);
	
		$estiloInformacion = new PHPExcel_Style();
		$estiloInformacion->applyFromArray( array(
			'font' => array(
				'name'  => 'Arial',
				'color' => array(
					'rgb' => '000000'
				)
			)
		));
	
		$estilo_centrar = array(
			'alignment' =>  array(
				'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
				'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
				'wrap'      => false
			)
		);
		for ($i = $inicio_detalle_movilidad; $i <= $objPHPExcel->getActiveSheet()->getHighestRow(); $i++) {
			$objPHPExcel->getActiveSheet()->getRowDimension($i)->setRowHeight(18);
		}
		$objPHPExcel->getActiveSheet()->getStyle('A'.($inicio_detalle_movilidad-1).':F'.($inicio_detalle_movilidad-1))->applyFromArray($estiloNombresColumnas);
		$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A".($inicio_detalle_movilidad).":F".($i-1));
		$objPHPExcel->getActiveSheet()->getStyle('A'.($inicio_detalle_movilidad-1).':A'.($i-1))->applyFromArray($estilo_centrar);
		$objPHPExcel->getActiveSheet()->getStyle('B'.($inicio_detalle_movilidad-1).':B'.($i-1))->applyFromArray($estilo_centrar);
		$objPHPExcel->getActiveSheet()->getStyle('C'.($inicio_detalle_movilidad-1).':C'.($i-1))->applyFromArray($estilo_centrar);
		$objPHPExcel->getActiveSheet()->getStyle('E'.($inicio_detalle_movilidad-1).':E'.($i-1))->applyFromArray($estilo_centrar);
		$objPHPExcel->getActiveSheet()->getStyle('F'.($inicio_detalle_movilidad-1).':F'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');
		$objPHPExcel->getActiveSheet()->getStyle('F'.($inicio_detalle_movilidad-1).':F'.($i-1))->applyFromArray($estilo_centrar);
		
	}

	// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
		
	for($i = 'A'; $i <= 'Z'; $i++)
	{
		$objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}
	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('Mesa de Partes - Liquidación');
	  
	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);
	
	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Reporte Solcitud Liquidación.xls" ');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$filename = "Reporte Solicitud Detalle " . $liquidacion_id . " - ".date("Y-m-d").".xls";
	$excel_path = '/var/www/html/files_bucket/mepa/descargas/contabilidad/solicitud_liquidacion/' . $filename;
	$excel_path_download = '/files_bucket/mepa/descargas/contabilidad/solicitud_liquidacion/' . $filename;

	try 
	{
		$objWriter->save($excel_path);
	} 
	catch (Exception $e)
	{
		echo json_encode(["error" => $e]);
		exit;
	}

	echo json_encode(array(
		"ruta_archivo" => $excel_path_download
	));
	exit;
}
/*
if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_detalle_solicitud_liquidacion_btn_export_comprobantes") {
    $liquidacion_id = $_POST['liquidacion_id'];

    // PROCEDEMOS A CREAR LA CARPETA SI EN CASO NO EXISTA
    $path = "/files_bucket/mepa/descargas/contabilidad/solicitud_liquidacion/";
	$path2 = "/var/www/html/files_bucket/mepa/descargas/contabilidad/solicitud_liquidacion/";

    if (!is_dir($path2)) {
        mkdir($path2, 0777, true);
    }

    // Crear un archivo ZIP en la dirección especificada
    $zipFilename = "archivos_liquidacion.zip";

	$zip = new ZipArchive();
	// Ruta absoluta
	$nombreArchivoZip = $path2 . "1-simple.zip";
	$nombreArchivoZipRuta = $path . "1-simple.zip";

	if (!$zip->open($nombreArchivoZip, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
		exit("Error abriendo ZIP en $nombreArchivoZip");
	}

	$rutaAbsoluta = "/var/www/html/files_bucket/mepa/solicitudes/liquidacion/liquidacion_detalle_fecha20230824111810.pdf";
	// Su nombre resumido, algo como "script.js"
	$nombre = basename($rutaAbsoluta);
	$zip->addFile($rutaAbsoluta, $nombre);

	// No olvides cerrar el archivo
	$resultado = $zip->close();
	if ($resultado) {
		
		$jsonResponse = json_encode(array(
            "ruta_archivo" => $nombreArchivoZipRuta
        ));

        echo $jsonResponse;

        // Cerrar la conexión a la base de datos
        //$mysqli->close();
		//echo "Archivo creado";
	} else {
		echo "Error creando archivo";
	}

	exit();

    
    if ($zip->open($zipFilename, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {

        $sel_query_detalle_liquidacion = $mysqli->prepare("
            SELECT
                dl.id,
                dl.nombre_file,
                dl.extension,
                dl.ruta AS detalle_liquidacion_documento_ruta,
                dl.download_file
            FROM mepa_detalle_caja_chica_liquidacion dl
            WHERE dl.mepa_caja_chica_liquidacion_id = ?
        ");
        $sel_query_detalle_liquidacion->bind_param("i", $liquidacion_id);
        $sel_query_detalle_liquidacion->execute();

        $list_query_liquidacion = $sel_query_detalle_liquidacion->get_result();
        $row_count_detalle_liquidacion = $list_query_liquidacion->num_rows;

        if ($row_count_detalle_liquidacion > 0) {
            while ($row = $list_query_liquidacion->fetch_assoc()) {
                $fileToInclude = $row['nombre_file'] . '.' . $row['extension'];
                $fileFullPath = "/var/www/html/".$row['download_file'];
                // Agregar el archivo al archivo ZIP
                $zip->addFile($fileFullPath, $fileToInclude);
            }
        }

        // Cerrar el archivo ZIP
        $zip->close();

        // Cerrar la consulta
        $sel_query_detalle_liquidacion->close();
		
        // Codificar la información en JSON
        $jsonResponse = json_encode(array(
            "ruta_archivo" => $zipFilename
        ));

        echo $jsonResponse;

        // Cerrar la conexión a la base de datos
        $mysqli->close();

        exit;
    } else {
        echo "Error al crear el archivo ZIP";
    }
}
*/
if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_detalle_solicitud_liquidacion_btn_export_comprobantes") {
    $liquidacion_id = $_POST['liquidacion_id'];


	// DATA DE RESUMEN DE SOLICITUD
	$queryResumenSolicitud = "
		SELECT
			a.id AS asignacion_id, a.usuario_asignado_id, l.id AS liquidacion_id,
			concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado,
			tp.dni AS usuario_asignado_dni,
			z.nombre AS zona,
			l.num_correlativo,
			l.situacion_etapa_id_contabilidad,
			l.situacion_etapa_id_tesoreria,
			l.se_aplica_movilidad, l.id_movilidad, 
			l.etapa_id_se_envio_a_tesoreria,
			l.created_at AS fecha_solicitud
		FROM mepa_caja_chica_liquidacion l
			INNER JOIN mepa_asignacion_caja_chica a
			ON l.asignacion_id = a.id
			LEFT JOIN mepa_caja_chica_movilidad m
			ON l.id_movilidad = m.id
			INNER JOIN tbl_usuarios tu
			ON a.usuario_asignado_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN mepa_zona_asignacion z
			ON a.zona_asignacion_id = z.id		
		WHERE l.status = 1 AND a.status = 1 AND l.situacion_etapa_id_superior = 6 AND l.id = '".$liquidacion_id."'
        ";
	$list_query_resumen = $mysqli->query($queryResumenSolicitud)->fetch_assoc();
	$usuario_asignado=$list_query_resumen["usuario_asignado"];
	$zona=$list_query_resumen["zona"];
	$fecha_solicitud=$list_query_resumen["fecha_solicitud"];


    // PROCEDEMOS A CREAR LA CARPETA SI EN CASO NO EXISTA
    $path = "/files_bucket/mepa/descargas/contabilidad/solicitud_liquidacion/";

    if (!is_dir($path2)) {
        mkdir($path2, 0777, true);
    }
	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/mepa/descargas/contabilidad/solicitud_liquidacion/*'); //obtenemos todos los nombres de los ficheros
	foreach($files as $file)
	{
	    if(is_file($file))
	    unlink($file); //elimino el fichero
	}

    // Crear un archivo ZIP en la dirección especificada
    $zipFilename = $usuario_asignado." - ".$zona." - ".$fecha_solicitud.".zip";

	$zip = new ZipArchive();
	// Ruta absoluta
	$nombreArchivoZip = "/var/www/html".$path . $zipFilename;
	$nombreArchivoZipRuta = $path . $zipFilename;

	if (!$zip->open($nombreArchivoZip, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
		exit("Error abriendo ZIP en $nombreArchivoZip");
	}

	////////////////////////////////////////////////////////////////////////////
	$sel_query_detalle_liquidacion = $mysqli->prepare("
            SELECT
                dl.id,
                dl.nombre_file,
                dl.extension,
                dl.ruta AS detalle_liquidacion_documento_ruta,
                dl.download_file
            FROM mepa_detalle_caja_chica_liquidacion dl
            WHERE dl.mepa_caja_chica_liquidacion_id = ?
        ");
        $sel_query_detalle_liquidacion->bind_param("i", $liquidacion_id);
        $sel_query_detalle_liquidacion->execute();

        $list_query_liquidacion = $sel_query_detalle_liquidacion->get_result();
        $row_count_detalle_liquidacion = $list_query_liquidacion->num_rows;

        if ($row_count_detalle_liquidacion > 0) {
            while ($row = $list_query_liquidacion->fetch_assoc()) {
                $fileToInclude = $row['nombre_file'];
                $fileFullPath = "/var/www/html/".$row['download_file'];
                // Agregar el archivo al archivo ZIP
                $zip->addFile($fileFullPath, $fileToInclude);
            }
        }
	/////////////////////////////////////////////////////////////////////////////
	
	unlink($zipFilename);
	// No olvides cerrar el archivo
	$resultado = $zip->close();
	if ($resultado) {
		
		$jsonResponse = json_encode(array(
            "ruta_archivo" => $nombreArchivoZipRuta
        ));

        echo $jsonResponse;
		// Cerrar la conexión a la base de datos
		$mysqli->close();
	} else {
		echo "Error creando archivo";
	}

	exit();
}

echo json_encode($result);

?>