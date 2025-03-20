<?php

date_default_timezone_set("America/Lima");

$result=array();
include("db_connect.php");
include("sys_login.php");
require_once '/var/www/html/sys/helpers.php';

include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';	

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(isset($_POST["accion"]) && $_POST["accion"]==="mepa_solicitud_liquidacion_mostrar_movilidad_rango_fecha") 
{
	$param_fecha_del = $_POST["param_fecha_del"];
	$param_fecha_del = date("Y-m-d", strtotime($param_fecha_del));
	
	$param_fecha_al = $_POST["param_fecha_al"];
	$param_fecha_al = date("Y-m-d", strtotime($param_fecha_al));

	$usuario_id = $login?$login['id']:null;
	
	// INICIO: SELECT TODOS LOS IDS MOVILIDAD QUE YA FUERON SELECCIONADOS EN LAS SOLICTUDES DE LIQUIDACION

    $query_ids_movilidad_ya_usadas = "
        SELECT
            id,
            id_movilidad
        FROM mepa_caja_chica_liquidacion
        WHERE user_created_id = $usuario_id AND id_movilidad IS NOT NULL
        GROUP BY id_movilidad
    ";

    $list_query_detalle = $mysqli->query($query_ids_movilidad_ya_usadas);

    $row_count_detalle = $list_query_detalle->num_rows;

    $ids_movilidad_registrado = '0';
    $contador_ids = 0;
    
    if ($row_count_detalle > 0) 
    {
        while ($row = $list_query_detalle->fetch_assoc()) 
        {
            if ($contador_ids > 0) 
            {
                $ids_movilidad_registrado .= ',';
            }

            $ids_movilidad_registrado .= $row["id_movilidad"];            
            $contador_ids++;
        }
    }

    // FIN: SELECT TODOS LOS IDS MOVILIDAD QUE YA FUERON SELECCIONADOS EN LAS SOLICTUDES DE LIQUIDACION

	$query = "
				SELECT
					m.id, m.num_correlativo, m.monto_cierre
				FROM mepa_caja_chica_movilidad m
				WHERE m.estado = 1 AND m.status = 2 AND m.user_created_id = '".$usuario_id."' 
					AND m.id NOT IN (".$ids_movilidad_registrado.") 
					AND (m.fecha_del >= '".$param_fecha_del."' AND m.fecha_al <= '".$param_fecha_al."')
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

if(isset($_POST["accion"]) && $_POST["accion"]==="mepa_solicitud_liquidacion_listar_detalles_pendientes")
{
	$usuario_id = $login?$login['id']:null;
	
	$html = '';
	$tbody = '';
	$total_importe_detalle = 0;

	$query = 
	"
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
		    dl.ruc,
		    dl.codigo_provision_contable,
		    dl.download_file_xml
		FROM mepa_detalle_caja_chica_liquidacion dl
			INNER JOIN mepa_tipo_documento td
			ON dl.tipo_documento = td.id
		WHERE dl.user_created_id = '".$usuario_id."' 
			AND dl.mepa_caja_chica_liquidacion_id IS NULL
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
		$importe = 0;

		while ($row = $list_query->fetch_assoc()) 
		{
			$id = $row["id"];
			$fecha_documento = $row["fecha_documento"];
			$tipo_documento_nombre = $row["tipo_documento_nombre"];
			$serie_comprobante = $row["serie_comprobante"];
			$num_comprobante = $row["num_comprobante"];
			$centro_costo = $row["centro_costo"];
			$detalle = $row["detalle"];
			$importe = $row["importe"];
			$tasa_igv = $row["tasa_igv"];
			$download_file = $row["download_file"];
			$download_file_xml = $row["download_file_xml"];
			
			$tbody .= '<tr>';
				$tbody .= '<td class="text-center">' . $num . '</td>';
				$tbody .= '<td class="text-center">' . $fecha_documento . '</td>';
				$tbody .= '<td class="text-center">' . $tipo_documento_nombre . '</td>';
				$tbody .= '<td class="text-center">' . $serie_comprobante . '</td>';
				$tbody .= '<td class="text-center">' . $num_comprobante . '</td>';
				$tbody .= '<td class="text-center">' . $centro_costo . '</td>';
				$tbody .= '<td class="text-center">' . $detalle . '</td>';
				$tbody .= '<td class="text-center">S/ ' . $importe . '</td>';
				$tbody .= '<td class="text-center">' . $tasa_igv . ' %</td>';
				$tbody .= '<td class="text-center">';
					$tbody .= '<a type="button" class="btn btn-rounded btn-info btn-sm" href="'.$download_file.'" target="_blank" data-toggle="tooltip" data-placement="top" title="Ver Archivo">'; 
						$tbody .= '<i class="fa fa-eye"></i>';
					$tbody .= '</a>';
				$tbody .= '</td>';
				$tbody .= '<td class="text-center">';
					if(!empty($download_file_xml))
                	{
                	
						$tbody .= '<a type="button" class="btn btn-rounded btn-primary btn-sm" href="'.$download_file_xml.'" target="_blank" data-toggle="tooltip" data-placement="top" title="Ver Archivo">'; 
							$tbody .= '<i class="fa fa-eye"></i>';
						$tbody .= '</a>';
                	}
                $tbody .= '</td>';
				$tbody .= '<td>';
					$tbody .= '<a class="btn btn-danger btn-sm" data-toggle="tooltip" data-placement="top" title="Agregar a la programación de pagos" onclick="mepa_solicitud_liquidacion_elimnar_detalle(' . $id . ')">';
					$tbody .= '<i class="fa fa-remove"></i>';
					$tbody .= '</a>';
				$tbody .= '</td>';
			$tbody .= '</tr>';

			$total_importe_detalle += $importe;
			$num ++;
		}

		$tbody .= '<tr style="font-size: 13px;">';
			$tbody .= '<th colspan="7" style="text-align: right;">';
				$tbody .= 'Total importe:';
			$tbody .= '</th>';
			$tbody .= '<th style="text-align: right;">';
				$tbody .= 'S/ '.number_format($total_importe_detalle, 2, '.', ',');
			$tbody .= '</th>';
			$tbody .= '<th colspan="4" style="text-align: right;">';
			$tbody .= '<input type="hidden" id="mepa_solicitud_liquidacion_detalle_total_importe" value="'.$total_importe_detalle.'">';
			$tbody .= '</th>';
		$tbody .= '</tr>';

		$html .= $tbody;

	}
	else if ($row_count == 0)
	{
		$html .= '<tr>';
			$html .= '<td style="text-align: center;" colspan="12">';
			$html .= '<input type="hidden" id="mepa_solicitud_liquidacion_detalle_total_importe" value="'.$total_importe_detalle.'">';
			$html .= 'No existen registros';
			$html .= '</td>';
		$html .= '</tr>';
	}

	if ($row_count >= 0)
	{
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["result"] = $html;
	} 
	else 
	{
		$result["http_code"] = 400;
		$result["result"] = "No hay registros de Asignación por pagar.";
	}

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_solicitud_liquidacion_agregar_detalle")
{
	$usuario_id = $login?$login['id']:null;
	$error = '';

	if((int)$usuario_id > 0)
	{
		$param_fecha_documento = $_POST['fecha_documento'];
		$param_fecha_documento = date("Y-m-d", strtotime($param_fecha_documento));
		$param_tipo_documento = $_POST['tipo_documento'];
		$param_serie_comprobante = strtoupper($_POST['serie_comprobante']);
		$param_num_comprobante = strtoupper($_POST['num_comprobante']);
		$param_centro_costo = strtoupper($_POST['centro_costo']);
		$param_detalle = strtoupper($_POST['detalle']);
		$param_importe = str_replace(",","",$_POST["importe"]);
		$param_tasa_igv = str_replace(",","",$_POST["tasa_igv"]);
		
		$liquidacion_detalle_archivo = "nombre_file";
		$archivo_file_name = $_FILES[$liquidacion_detalle_archivo]['name'];
		$archivo_file_tmp = $_FILES[$liquidacion_detalle_archivo]['tmp_name'];
		$archivo_file_size = $_FILES[$liquidacion_detalle_archivo]['size'];
		$archivo_file_extension = strtolower(pathinfo($archivo_file_name, PATHINFO_EXTENSION));

		$path = "/var/www/html/files_bucket/mepa/solicitudes/liquidacion/";
		$download = "/files_bucket/mepa/solicitudes/liquidacion/";

		// INICIO ARCHIVO XML
			
		$liquidacion_detalle_archivo_xml = "nombre_file_xml";
		$archivo_file_name_xml = $_FILES[$liquidacion_detalle_archivo_xml]['name'];
		
		// FIN ARCHIVO XML

		if (!is_dir($path)) 
		{
			mkdir($path, 0777, true);
		}

		$nombreFileUpload = "user_".$usuario_id."_liquidacion_detalle_comprobante_fecha_".date('YmdHis'). ".".$archivo_file_extension;

		$nombreDownload = $download.$nombreFileUpload;
		move_uploaded_file($archivo_file_tmp, $path. $nombreFileUpload);

		if(empty($archivo_file_name_xml))
		{
			// NO EXISTE ARCHIVO XML
			$query_insert_liquidacion_detalle = "
				INSERT INTO mepa_detalle_caja_chica_liquidacion
				(
					item,
					fecha_documento,
					tipo_documento,
					serie_comprobante,
					num_comprobante,
					centro_costo,
					detalle,
					importe,
					tasa_igv,
					nombre_file,
					extension,
					size,
					ruta,
					download_file,
					status,
					user_created_id,
					created_at,
					user_updated_id,
					updated_at
				)
				VALUES 
				(
					'1',
					'".$param_fecha_documento."',
					'".$param_tipo_documento."',
					'".$param_serie_comprobante."',
					'".$param_num_comprobante."',
					'".$param_centro_costo."',
					'".$param_detalle."',
					'".$param_importe."',
					'".$param_tasa_igv."',
					'".$nombreFileUpload."',
					'".$archivo_file_extension."',
					'".$archivo_file_size."',
					'".$path."',
					'".$nombreDownload."',
					1,
					'".$login["id"]."', 
					'".date('Y-m-d H:i:s')."',
					'".$login["id"]."', 
					'".date('Y-m-d H:i:s')."'
				)";
		}
		else
		{
			// SI EXISTE ARCHIVO XML

			$archivo_file_tmp_xml = $_FILES[$liquidacion_detalle_archivo_xml]['tmp_name'];
			$archivo_file_size_xml = $_FILES[$liquidacion_detalle_archivo_xml]['size'];
			$archivo_file_extension_xml = strtolower(pathinfo($archivo_file_name_xml, PATHINFO_EXTENSION));


			$nombreFileUpload_xml = "user_".$usuario_id."_liquidacion_detalle_xml_fecha_".date('YmdHis'). ".".$archivo_file_extension_xml;

			$nombreDownload_xml = $download.$nombreFileUpload_xml;
			move_uploaded_file($archivo_file_tmp_xml, $path. $nombreFileUpload_xml);

			$query_insert_liquidacion_detalle = 
				"INSERT INTO mepa_detalle_caja_chica_liquidacion
				(
					item,
					fecha_documento,
					tipo_documento,
					serie_comprobante,
					num_comprobante,
					centro_costo,
					detalle,
					importe,
					tasa_igv,
					nombre_file,
					extension,
					size,
					ruta,
					download_file,
					status,
					user_created_id,
					created_at,
					user_updated_id,
					updated_at,
					nombre_file_xml,
					extension_xml,
					size_xml,
					ruta_xml,
					download_file_xml
				)
				VALUES 
				(
					'1',
					'".$param_fecha_documento."',
					'".$param_tipo_documento."',
					'".$param_serie_comprobante."',
					'".$param_num_comprobante."',
					'".$param_centro_costo."',
					'".$param_detalle."',
					'".$param_importe."',
					'".$param_tasa_igv."',
					'".$nombreFileUpload."',
					'".$archivo_file_extension."',
					'".$archivo_file_size."',
					'".$path."',
					'".$nombreDownload."',
					1,
					'".$login["id"]."', 
					'".date('Y-m-d H:i:s')."',
					'".$login["id"]."', 
					'".date('Y-m-d H:i:s')."',
					'".$nombreFileUpload_xml."',
					'".$archivo_file_extension_xml."',
					'".$archivo_file_size_xml."',
					'".$path."',
					'".$nombreDownload_xml."'
				)";
		}

		$mysqli->query($query_insert_liquidacion_detalle);

		if($mysqli->error)
		{
			$error .= $mysqli->error . $query_insert_liquidacion_detalle;
		}


		if ($error == '')
		{
			$result["http_code"] = 200;
			$result["status"] = "Datos registrados correctamente.";
			$result["error"] = $error;
		}
		else 
		{
			$result["http_code"] = 400;
			$result["status"] = "Error.";
			$result["error"] = $error;
		}
	}
	else
	{
		$result["http_code"] = 400;
		$result["status"] = "";
        $result["error"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";

	}

	echo json_encode($result);
	exit();
}

if (isset($_POST["accion"]) && $_POST["accion"] === "mepa_solicitud_liquidacion_elimnar_detalle")
{
	$detalle_id = $_POST["detalle_id"];
	
	$error = '';

	$query_update = 
	"
		DELETE FROM  mepa_detalle_caja_chica_liquidacion 						
		WHERE id = '".$detalle_id."'
	";

	$mysqli->query($query_update);

	if($mysqli->error)
	{
		$error = $mysqli->error;
	}

	if ($error == '') 
	{
		$result["http_code"] = 200;
		$result["mensaje"] = "Registro Eliminado";
	} 
	else 
	{
		$result["http_code"] = 400;
		$result["mensaje"] = $error;
	}

	echo json_encode($result);
	exit();
}

if(isset($_POST["accion"]) && $_POST["accion"]==="guardar_solicitud_liquidacion_caja_chica") 
{
	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	$error = '';
	$monto_cierre_movilidad = 0;
	
	if((int) $usuario_id>0)
	{
		$txt_asignacion_zona_id = $_POST["txt_asignacion_zona_id"];
		$txt_liquidacion_fecha_del = $_POST["txt_liquidacion_fecha_del"];
		$txt_liquidacion_fecha_del = date("Y-m-d", strtotime($txt_liquidacion_fecha_del));
		$txt_liquidacion_fecha_hasta = $_POST["txt_liquidacion_fecha_hasta"];
		$txt_liquidacion_fecha_hasta = date("Y-m-d", strtotime($txt_liquidacion_fecha_hasta));
		$monto_total_liquidacion = $_POST["monto_total_liquidacion"];
		$incluir_solicitud_moviliad = $_POST["incluir_solicitud_moviliad"];
		$id_movilidad_select = $_POST["id_movilidad"];
		$id_asignacion_usuario = $_POST["id_asignacion_usuario"];
		$ultima_caja_chica = $_POST["ultima_caja_chica"];
		$txt_motivo_cerrar = $_POST["txt_motivo_cerrar"];

		$query_sql = 
		"
			SELECT
	            a.id, a.usuario_asignado_id,
	            c.num_correlativo, c.tipo_solicitud
	        FROM mepa_asignacion_caja_chica a
	            INNER JOIN mepa_documento_correlativo c
	            ON a.id = c.asignacion_id
	        WHERE usuario_asignado_id = $usuario_id AND tipo_solicitud = 2
	        ORDER BY c.num_correlativo DESC
	        LIMIT 1
		";

	    $query = $mysqli->query($query_sql);
	    $row = $query->fetch_assoc();

	    $num_correlativo = $row["num_correlativo"];
		
		if($incluir_solicitud_moviliad == 1)
		{
			$query_insert_liquidacion_cabecera = "INSERT INTO mepa_caja_chica_liquidacion
											(
												asignacion_id,
												asignacion_zona_id,
												num_correlativo,
												fecha_desde,
												fecha_hasta,
												total_rendicion,
												solicitante_usuario_id,
												situacion_etapa_id_superior,
												situacion_etapa_id_contabilidad,
												status,
												user_created_id,
												created_at,
												user_updated_id,
												updated_at,
												id_movilidad,
												se_aplica_movilidad,
												etapa_id_se_envio_a_tesoreria,
												situacion_etapa_id_tesoreria
											) 
											VALUES 
											(
												'".$id_asignacion_usuario."',
												'".$txt_asignacion_zona_id."',
												'".$num_correlativo."',
												'".$txt_liquidacion_fecha_del."',
												'".$txt_liquidacion_fecha_hasta."',
												'".$monto_total_liquidacion."',
												'".$usuario_id."',
												1,
												1,
												1,
												'".$login["id"]."', 
												'".date('Y-m-d H:i:s')."',
												'".$login["id"]."', 
												'".date('Y-m-d H:i:s')."',
												'".$id_movilidad_select."',
												'".$incluir_solicitud_moviliad."',
												1,
												10
											)";
		}
		else
		{
			$query_insert_liquidacion_cabecera = "INSERT INTO mepa_caja_chica_liquidacion
											(
												asignacion_id,
												asignacion_zona_id,
												num_correlativo,
												fecha_desde,
												fecha_hasta,
												total_rendicion,
												solicitante_usuario_id,
												situacion_etapa_id_superior,
												situacion_etapa_id_contabilidad,
												status,
												user_created_id,
												created_at,
												user_updated_id,
												updated_at,
												se_aplica_movilidad,
												etapa_id_se_envio_a_tesoreria,
												situacion_etapa_id_tesoreria
											)
											VALUES 
											(
												'".$id_asignacion_usuario."',
												'".$txt_asignacion_zona_id."',
												'".$num_correlativo."',
												'".$txt_liquidacion_fecha_del."',
												'".$txt_liquidacion_fecha_hasta."',
												'".$monto_total_liquidacion."',
												'".$usuario_id."',
												1,
												1,
												1,
												'".$login["id"]."', 
												'".date('Y-m-d H:i:s')."',
												'".$login["id"]."', 
												'".date('Y-m-d H:i:s')."',
												'".$incluir_solicitud_moviliad."',
												1,
												10
											)";
		}
		
		$mysqli->query($query_insert_liquidacion_cabecera);

		$id_cabecera = $mysqli->insert_id;

		if($ultima_caja_chica == 1)
		{
			$query_update_liquidacion = 
			"
				UPDATE mepa_caja_chica_liquidacion 
					SET situacion_jefe_cerrar_caja_chica = 12,
					motivo_cerrar_caja_chica = '".$txt_motivo_cerrar."'
				WHERE id = '".$id_cabecera."'
			";

			$mysqli->query($query_update_liquidacion);

			if($mysqli->error)
			{
				$error = $mysqli->error;

				$result["http_code"] = 400;
				$result["status"] = "Error.";
				$result["error"] = $error;

				echo json_encode($result);
				exit();
			}

			$query_cerrar_asignacion = 
			"
				UPDATE mepa_asignacion_caja_chica 
					SET situacion_etapa_id = 8
				WHERE id = '".$id_asignacion_usuario."' 
			";

			$mysqli->query($query_cerrar_asignacion);

			if($mysqli->error)
			{
				$error = $mysqli->error;

				$result["http_code"] = 400;
				$result["status"] = "Error.";
				$result["error"] = $error;

				echo json_encode($result);
				exit();
			}

		}

		if($mysqli->error)
		{
			$error .= $mysqli->error . $query_insert_liquidacion_cabecera;
		}


		if ($error)
		{
			$result["http_code"] = 400;
			$result["status"] = "Error.";
			$result["error"] = $error;

			echo json_encode($result);
			exit();
		}

		//INICIO ACTUALIZAR EL ID LIQUIDACION DETALLE
		$query_update_id_detalle = 
		"
			UPDATE mepa_detalle_caja_chica_liquidacion
				SET mepa_caja_chica_liquidacion_id = '".$id_cabecera."'
			WHERE user_created_id = '".$usuario_id."' AND mepa_caja_chica_liquidacion_id IS NULL
		";

		$mysqli->query($query_update_id_detalle);
		//FIN ACTUALIZAR EL ID LIQUIDACION DETALLE



		$query_sql_movilidad = "
						       SELECT
									IFNULL(monto_cierre, 0) AS monto_cierre
								FROM mepa_caja_chica_movilidad
								WHERE id = '".$id_movilidad_select."'
								LIMIT 1
	       					 ";
	    $query = $mysqli->query($query_sql_movilidad);
	    $cant = $query->num_rows;
	    $row = $query->fetch_assoc();

	    if($cant > 0)
	    {
	    	$monto_cierre_movilidad = $row["monto_cierre"];
	    }

	    $suma_monto_total = $monto_total_liquidacion + $monto_cierre_movilidad;

		if($mysqli->error)
		{
			$error .= $mysqli->error . $query_insert_liquidacion_detalle;
		}


		if ($error == '')
		{
			$result["http_code"] = 200;
			$result["status"] = "Datos obtenidos de gestión.";
			$result["error"] = $error;
			send_enviar_nueva_solicitud_liquidacion($id_cabecera);

			//INICIO ACTUALIZAR EL SALDO DISPONIBLE
			$query_update_saldo = "
							UPDATE mepa_asignacion_caja_chica 
								SET saldo_disponible = saldo_disponible - '".$suma_monto_total."'
							WHERE id = '".$id_asignacion_usuario."' 
							";
			$mysqli->query($query_update_saldo);
			//FIN ACTUALIZAR EL SALDO DISPONIBLE

			//ACTUALIZAR EL CORRELATIVO SUMANDO 1
			// TIPO SOLICITUD = 2 (LIQUIDACION)
			$query_update = "UPDATE mepa_documento_correlativo SET num_correlativo = $num_correlativo + 1 WHERE asignacion_id = '".$id_asignacion_usuario."' AND tipo_solicitud = 2 ";
			$mysqli->query($query_update);

		}
		else 
		{
			$result["http_code"] = 400;
			$result["status"] = "Datos obtenidos de gestión.";
			$result["error"] = $error;
		}
	}
	else
	{
		$result["http_code"] = 400;
        $result["error"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
	}

	echo json_encode($result);
	exit();
	
}

function send_enviar_nueva_solicitud_liquidacion($liquidacion_id)
{
	include("db_connect.php");
	include("sys_login.php");

	$usuario_id = $login?$login['id']:null;
	$fecha_actual = date('Y-m-d');

	$host = $_SERVER["HTTP_HOST"];
	$titulo_email = "";
	$sub_total = 0;

	$sel_query = $mysqli->query("
								SELECT
									l.id,
								    l.asignacion_id,
								    a.zona_asignacion_id,
								    a.fondo_asignado,
								    a.reportar_directorio,
								    l.num_correlativo,
								    l.fecha_desde,
								    l.fecha_hasta,
								    l.total_rendicion AS total_liquidacion,
								    IFNULL(m.monto_cierre, 0) AS total_movilidad,
								    l.solicitante_usuario_id,
								    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_solicitante,
								    l.created_at AS fecha_solicitud
								FROM mepa_caja_chica_liquidacion l
									INNER JOIN mepa_asignacion_caja_chica a
									ON l.asignacion_id = a.id
									INNER JOIN tbl_usuarios tu
									ON a.usuario_asignado_id = tu.id
									INNER JOIN tbl_personal_apt tp
									ON tu.personal_id = tp.id
									LEFT JOIN mepa_caja_chica_movilidad m
									ON l.id_movilidad = m.id
								WHERE l.id = '".$liquidacion_id."'
							");

	$body = "";
	$body .= '<html>';

	while($sel = $sel_query->fetch_assoc())
	{
		$id = $sel['id'];
		$asignacion_id = $sel['asignacion_id'];
		$zona_asignacion_id = $sel['zona_asignacion_id'];
		$fondo_asignado = $sel['fondo_asignado'];
		$reportar_directorio = $sel['reportar_directorio'];
		$num_correlativo = $sel['num_correlativo'];
		$fecha_desde = $sel['fecha_desde'];
		$fecha_hasta = $sel['fecha_hasta'];
		$total_liquidacion = $sel['total_liquidacion'];
		$total_movilidad = $sel['total_movilidad'];
		$solicitante_usuario_id = $sel['solicitante_usuario_id'];
		$usuario_solicitante = $sel['usuario_solicitante'];
		$fecha_solicitud = $sel['fecha_solicitud'];
		
		$sub_total = $total_liquidacion + $total_movilidad;

		$body .= '<div>';
			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

				$body .= '<thead>';
				
					$body .= '<tr>';
						$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
							$body .= '<b>Nueva Solicitud</b>';
						$body .= '</th>';
					$body .= '</tr>';

				$body .= '</thead>';

				$body .= '<tr>';
					$body .= '<td style="background-color: #ffffdd; width: 155px;"><b>Nº Correlativo:</b></td>';
					$body .= '<td>'.$num_correlativo.'</td>';
				$body .= '</tr>';

				$body .= '<tr>';
					$body .= '<td style="background-color: #ffffdd; width: 155px;"><b>Periodo:</b></td>';
					$body .= '<td>'.$fecha_desde.' al '.$fecha_hasta.'</td>';
				$body .= '</tr>';

				$body .= '<tr>';
					$body .= '<td style="background-color: #ffffdd; width: 155px;"><b>Monto Total:</b></td>';
					$body .= '<td>S/ '.number_format($sub_total, 2, '.', ',').'</td>';
				$body .= '</tr>';

				if($sub_total > $fondo_asignado)
				{
					$body .= '<tr>';
						$body .= '<td style="background-color: #ffffdd; width: 155px;"><b>Observación:</b></td>';
						$body .= '<td>Caja Chica sobrepasa el fondo de asignación</td>';
					$body .= '</tr>';
				}

				$body .= '<tr>';
					$body .= '<td style="background-color: #ffffdd; width: 155px;"><b>Solicitante:</b></td>';
					$body .= '<td>'.$usuario_solicitante.'</td>';
				$body .= '</tr>';
			
				$body .= '<tr>';
					$body .= '<td style="background-color: #ffffdd"><b>Fecha Solicitud:</b></td>';
					$body .= '<td>'.$fecha_solicitud.'</td>';
				$body .= '</tr>';

			$body .= '</table>';
		$body .= '</div>';

		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$host.'/?sec_id=mepa&sub_sec_id=detalle_atencion_liquidacion&id='.$id.'" target="_blank">';
		    	$body .= '<button style="background-color: green; color: white; cursor: pointer; width: 50%;">';
					$body .= '<b>Ver Solicitud</b>';
		    	$body .= '</button>';
		    $body .= '</a>';
		$body .= '</div>';

	}
		$body .= '</html>';
		$body .= "";


	$sub_titulo_email = "";

	if(env('SEND_EMAIL') == 'test')
	{
		$sub_titulo_email = "TEST SISTEMAS: ";
	}

	$titulo_email = $sub_titulo_email."Gestion - Sistema Mesa de Partes: Nueva Solicitud de Liquidación ID: ".$liquidacion_id;
	
	$cc = [
	];

	$bcc = [
	];

	// INICIO: LISTAR USUARIOS
	// CREADOR
	// USUARIO QUIEN CREA LA LIQUIDACION
	// DIRECTORIO (SI EN CASO APLICA)
	$select_todos = "";

	$select_usuarios_enviar_jc = 
	"
		SELECT
			tp.correo
		FROM mepa_asignacion_caja_chica a
			INNER JOIN tbl_usuarios tu
			ON a.user_created_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
		WHERE a.id = '".$asignacion_id."'
	";


	$select_usuarios_enviar_liquidacion = 
	"
		SELECT
			tp.correo
		FROM mepa_asignacion_caja_chica a
			INNER JOIN tbl_usuarios tu
			ON a.usuario_asignado_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
		WHERE a.id = '".$asignacion_id."'
	";

	if($reportar_directorio == 1)
	{
		$select_usuarios_enviar_reportar_usuarios = 
		"
			SELECT
				tp.correo
			FROM mepa_asignacion_caja_chica a
				INNER JOIN mepa_reportar_directorio r
			    ON a.id = r.asignacion_id
				INNER JOIN tbl_usuarios tu
				ON r.usuario_reportar = tu.id
				INNER JOIN tbl_personal_apt tp
				ON tu.personal_id = tp.id
			WHERE a.id = '".$asignacion_id."'
		";

		$select_todos .= $select_usuarios_enviar_jc;
		$select_todos .= "UNION ALL";
		$select_todos .= $select_usuarios_enviar_liquidacion;
		$select_todos .= "UNION ALL";
		$select_todos .= $select_usuarios_enviar_reportar_usuarios;
	}
	else
	{
		$select_todos .= $select_usuarios_enviar_jc;
		$select_todos .= "UNION ALL";
		$select_todos .= $select_usuarios_enviar_liquidacion;
	}

	$sel_query_usuarios_enviar_a = $mysqli->query($select_todos);

	$row_count = $sel_query_usuarios_enviar_a->num_rows;

	if ($row_count > 0)
	{
		while($sel = $sel_query_usuarios_enviar_a->fetch_assoc())
		{
			if(!is_null($sel['correo']) AND !empty($sel['correo']))
			{
				array_push($cc, $sel['correo']);	
			}
		}
	}
	// FIN: LISTAR USUARIOS

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

echo json_encode($result);

?>