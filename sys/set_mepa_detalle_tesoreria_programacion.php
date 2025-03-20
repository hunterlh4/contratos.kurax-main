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
error_reporting(0);


if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_tesoreria_guardar_comprobante_pago") 
{
	$error = '';

	$user_id = $login?$login['id']:null;
	
	if((int) $user_id>0)
	{
		$tesoreria_fecha_comprobante_pago = $_POST['tesoreria_fecha_comprobante_pago'];
		$tesoreria_fecha_comprobante_pago = date("Y-m-d", strtotime($tesoreria_fecha_comprobante_pago));

		$mepa_programacion_id = $_POST['mepa_programacion_id'];

		if(isset($_FILES['tesoreria_comprobante_pago']))
		{
			// INICIO: INSERTAMOS EL FILE EN LA TABLA mepa_caja_chica_programacion_files

			$path = "/var/www/html/files_bucket/mepa/programacion/comprobante_pago/";
			$download = "/files_bucket/mepa/programacion/comprobante_pago/";

			if (!is_dir($path)) 
			{
				mkdir($path, 0777, true);
			}

			$cant = 1;

			for ($i=0; $i < count($_FILES['tesoreria_comprobante_pago']['name']); $i++)
			{
				$file_name = $_FILES['tesoreria_comprobante_pago']['name'][$i];
				$file_tmp = $_FILES['tesoreria_comprobante_pago']['tmp_name'][$i];
				$file_size = $_FILES['tesoreria_comprobante_pago']['size'][$i];
				$file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
				

				$nombreFileUpload = "ID_".$mepa_programacion_id."_item_".$cant."_imagen".date('YmdHis'). ".".$file_extension;
				$nombreDownload = $download.$nombreFileUpload;
				move_uploaded_file($file_tmp, $path. $nombreFileUpload);

				$query_insert_file = "INSERT INTO mepa_caja_chica_programacion_files
									(
										mepa_caja_chica_programacion_id,
										imagen,
										extension,
										size,
										ruta,
									    download,
										status,
										user_created_id,
										created_at,
										user_updated_id,
										updated_at
									) 
									VALUES 
									(
										'".$mepa_programacion_id."',
										'".$nombreFileUpload."',
										'".$file_extension."',
										'".$file_size."',
										'".$path."',
									    '".$nombreDownload."',
									    1,
										'".$login["id"]."', 
										'".date('Y-m-d H:i:s')."',
										'".$login["id"]."', 
										'".date('Y-m-d H:i:s')."'
									)";

				$mysqli->query($query_insert_file);

				if($mysqli->error)
				{
					$error .= $mysqli->error;

					$result["http_code"] = 400;
					$result["status"] = "Ocurrio un error.";
					$result["error"] = $file_name;

					echo json_encode($result);
					exit();
				}

				$cant++;
			}

			// FIN: INSERTAMOS EL FILE EN LA TABLA mepa_caja_chica_programacion_files

			// INICIO: ACTUALIZAMOS LOS CAMPOS DE TESORERIA EN LA TABLA mepa_caja_chica_programacion

			$query_update = "
						UPDATE mepa_caja_chica_programacion 
							SET fecha_carga_comprobante = '".date('Y-m-d H:i:s')."',
							    user_id_carga_comprobante = '".$login["id"]."',
							    se_cargo_comprobante = 1,
							    fecha_comprobante = '".$tesoreria_fecha_comprobante_pago."',
							    situacion_etapa_id = 11
						WHERE id = '".$mepa_programacion_id."'
						";

			$mysqli->query($query_update);

			if($mysqli->error)
			{
				$error .= $mysqli->error;

				$result["http_code"] = 400;
				$result["status"] = "Ocurrio un error.";
				$result["error"] = $error;

				echo json_encode($result);
				exit();
			}

			// FIN: ACTUALIZAMOS LOS CAMPOS DE TESORERIA EN LA TABLA mepa_caja_chica_programacion
		}
		else
		{
			$result["http_code"] = 400;
			$result["status"] = "Ocurrio un error.";
			$result["error"] = "Por favor seleccionar un archivo.";

			echo json_encode($result);
			exit();
		}


		$sel_query_verificacion = $mysqli->query("
									SELECT
										p.id, p.tipo_solicitud_id, p.user_id_carga_comprobante,
										concat(IFNULL(tpc.nombre, ''),' ', IFNULL(tpc.apellido_paterno, ''), ' ', IFNULL(tpc.apellido_materno, '')) AS usuario_atencion_comprobante,
					    				p.fecha_comprobante
									FROM mepa_caja_chica_programacion p
										LEFT JOIN tbl_usuarios tuc
										ON p.user_id_carga_comprobante = tuc.id
										LEFT JOIN tbl_personal_apt tpc
										ON tuc.personal_id = tpc.id
									WHERE p.id = '".$mepa_programacion_id."'
								");

		while($sel = $sel_query_verificacion->fetch_assoc())
		{
			$id = $sel['id'];
			$tipo_solicitud_id = $sel['tipo_solicitud_id'];
			$usuario_atencion_comprobante = $sel['usuario_atencion_comprobante'];
			$fecha_comprobante = $sel['fecha_comprobante'];
			$file = $sel['file'];
		}

		// SI ES ASIGNACION, LISTAMOS TODOS LAS ASIGNACIONES QUE PERTENECEN A LA PROGRAMACION
		// EN EL WHERE EL CAMPO situacion_etapa_id_tesoreria TIENE QUE SER IGUAL A 10
		// PORQUE SOLO VAMOS A PAGAR A LAS ASIGNACIONES QUE AUN NO SE LOS HIZO EL PAGO (Pendiente de Pago)
		if($tipo_solicitud_id == 1)
		{
			$select_usuarios_actualizar_saldo = 
				"
					SELECT
						p.id, p.tipo_solicitud_id,
					    p.user_id_carga_comprobante,
					    concat(IFNULL(tpc.nombre, ''),' ', IFNULL(tpc.apellido_paterno, ''), ' ', IFNULL(tpc.apellido_materno, '')) AS usuario_atencion_comprobante,
					    p.fecha_comprobante,
					    dt.nombre_tabla, dt.nombre_tabla_id,
					    a.id AS asignacion_id,
					    a.usuario_asignado_id,
					    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado,
					    tp.correo
					FROM mepa_caja_chica_programacion p
						INNER JOIN mepa_caja_chica_programacion_detalle dt
						ON p.id = dt.mepa_caja_chica_programacion_id
						INNER JOIN mepa_asignacion_caja_chica a
						ON dt.nombre_tabla_id = a.id
						LEFT JOIN tbl_usuarios tuc
						ON p.user_id_carga_comprobante = tuc.id
						LEFT JOIN tbl_personal_apt tpc
						ON tuc.personal_id = tpc.id
						INNER JOIN tbl_usuarios tu
						ON a.usuario_asignado_id = tu.id
						INNER JOIN tbl_personal_apt tp
						ON tu.personal_id = tp.id
					WHERE p.id = '".$mepa_programacion_id."' AND p.situacion_etapa_id = 11 AND dt.status = 1 AND a.situacion_etapa_id_tesoreria = 10
				";

			$sel_query_usuarios_actualizar_pago = $mysqli->query($select_usuarios_actualizar_saldo);

			$row_count = $sel_query_usuarios_actualizar_pago->num_rows;

			if ($row_count > 0)
			{
				while($sel = $sel_query_usuarios_actualizar_pago->fetch_assoc())
				{
					$asignacion_id = $sel["asignacion_id"];

					$query_update = "
						UPDATE mepa_asignacion_caja_chica 
							SET situacion_etapa_id_tesoreria = 11
						WHERE id = '".$asignacion_id."'
						";

					$mysqli->query($query_update);

					if($mysqli->error)
					{
						$error .= $mysqli->error;
					}
				}
			}
		}
		// SI ES LIQUIDACION, LISTAMOS TODOS LAS LIQUIDACIONES QUE PERTENECEN A LA PROGRAMACION
		// EN EL WHERE EL CAMPO situacion_etapa_id_tesoreria TIENE QUE SER IGUAL A 10
		// PORQUE SOLO VAMOS A PAGAR A LAS LIQUIDACIONES QUE AUN NO SE LOS HIZO EL PAGO (Pendiente de Pago)
		else if($tipo_solicitud_id == 2)
		{
			$select_usuarios_actualizar_saldo = 
				"
					SELECT
						p.id, p.tipo_solicitud_id,
						p.user_id_carga_comprobante,
						concat(IFNULL(tpc.nombre, ''),' ', IFNULL(tpc.apellido_paterno, ''), ' ', IFNULL(tpc.apellido_materno, '')) AS usuario_atencion_comprobante,
						p.fecha_comprobante,
						dt.nombre_tabla, dt.nombre_tabla_id,
						a.id AS asignacion_id,
						a.usuario_asignado_id,
						concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado,
						tp.correo,
						l.id AS liquidacion_id,
					    l.total_rendicion AS monto_liquidacion,
						m.monto_cierre AS monto_movilidad
					FROM mepa_caja_chica_programacion p
						INNER JOIN mepa_caja_chica_programacion_detalle dt
						ON p.id = dt.mepa_caja_chica_programacion_id
					    INNER JOIN mepa_caja_chica_liquidacion l
					    ON dt.nombre_tabla_id = l.id
						INNER JOIN mepa_asignacion_caja_chica a
						ON l.asignacion_id = a.id
						LEFT JOIN tbl_usuarios tuc
						ON p.user_id_carga_comprobante = tuc.id
						LEFT JOIN tbl_personal_apt tpc
						ON tuc.personal_id = tpc.id
						INNER JOIN tbl_usuarios tu
						ON a.usuario_asignado_id = tu.id
						INNER JOIN tbl_personal_apt tp
						ON tu.personal_id = tp.id
					    LEFT JOIN mepa_caja_chica_movilidad m
						ON l.id_movilidad = m.id
					WHERE p.id = '".$mepa_programacion_id."' AND p.situacion_etapa_id = 11 AND dt.status = 1 AND l.situacion_etapa_id_tesoreria = 10
				";


			$sel_query_usuarios_actualizar_pago = $mysqli->query($select_usuarios_actualizar_saldo);

			$row_count = $sel_query_usuarios_actualizar_pago->num_rows;

			if ($row_count > 0)
			{
				while($sel = $sel_query_usuarios_actualizar_pago->fetch_assoc())
				{
					// ACTUALIZAR EL MONTO DEL REEMBOLSO EN LA TABLA DE ASIGNACION

					$asignacion_id = $sel["asignacion_id"];
					$liquidacion_id = $sel["liquidacion_id"];
					$suma_total = $sel["monto_liquidacion"] + $sel["monto_movilidad"];

					$query_update_situacion = "
									UPDATE mepa_caja_chica_liquidacion 
										SET situacion_etapa_id_tesoreria = 11
									WHERE id = '".$liquidacion_id."'
									";

					$mysqli->query($query_update_situacion);

					if($mysqli->error)
					{
						$error .= $mysqli->error;
					}

					$query_update_saldo = "
						UPDATE mepa_asignacion_caja_chica 
							SET saldo_disponible = saldo_disponible + '".$suma_total."'
						WHERE id = '".$asignacion_id."'
						";

					$mysqli->query($query_update_saldo);

					if($mysqli->error)
					{
						$error .= $mysqli->error;
					}
				}
			}
		}
		// SI ES AUMENTO, LISTAMOS TODOS LAS ASIGNACIONES QUE PERTENECEN A LA PROGRAMACION
		// EN EL WHERE EL CAMPO situacion_etapa_id_tesoreria TIENE QUE SER IGUAL A 10
		// PORQUE SOLO VAMOS A PAGAR LOS AUMENTOS QUE AUN NO SE LOS HIZO EL PAGO (Pendiente de Pago)
		else if($tipo_solicitud_id == 9)
		{
			$select_usuarios_actualizar_saldo = 
				"
					SELECT
						p.id, p.tipo_solicitud_id,
					    p.user_id_carga_comprobante,
					    concat(IFNULL(tpc.nombre, ''),' ', IFNULL(tpc.apellido_paterno, ''), ' ', IFNULL(tpc.apellido_materno, '')) AS usuario_atencion_comprobante,
					    p.fecha_comprobante,
					    dt.nombre_tabla, dt.nombre_tabla_id,
					    aa.id AS aumento_asignacion_id, 
					    aa.monto,
					    a.id AS asignacion_id,
					    a.usuario_asignado_id,
					    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado,
					    tp.correo
					FROM mepa_caja_chica_programacion p
						INNER JOIN mepa_caja_chica_programacion_detalle dt
						ON p.id = dt.mepa_caja_chica_programacion_id
						INNER JOIN mepa_aumento_asignacion aa
						ON dt.nombre_tabla_id = aa.id
						INNER JOIN mepa_asignacion_caja_chica a
						ON aa.asignacion_id = a.id
						LEFT JOIN tbl_usuarios tuc
						ON p.user_id_carga_comprobante = tuc.id
						LEFT JOIN tbl_personal_apt tpc
						ON tuc.personal_id = tpc.id
						INNER JOIN tbl_usuarios tu
						ON a.usuario_asignado_id = tu.id
						INNER JOIN tbl_personal_apt tp
						ON tu.personal_id = tp.id
					WHERE p.id = '".$mepa_programacion_id."' AND p.situacion_etapa_id = 11 AND dt.status = 1 AND aa.situacion_tesoreria_etapa_id = 10
				";

			$sel_query_usuarios_actualizar_pago = $mysqli->query($select_usuarios_actualizar_saldo);

			$row_count = $sel_query_usuarios_actualizar_pago->num_rows;

			if ($row_count > 0)
			{
				while($sel = $sel_query_usuarios_actualizar_pago->fetch_assoc())
				{
					$aumento_asignacion_id = $sel["aumento_asignacion_id"];
					$monto = $sel["monto"];
					$asignacion_id = $sel["asignacion_id"];

					$query_update = "
						UPDATE mepa_aumento_asignacion 
							SET situacion_tesoreria_etapa_id = 11
						WHERE id = '".$aumento_asignacion_id."'
						";

					$mysqli->query($query_update);

					
					// INICIO: ACTUALIZAR EL AUMENTO DE LA ASIGNACION
					
					$query_update_aumento = "
						UPDATE mepa_asignacion_caja_chica 
							SET fondo_asignado = fondo_asignado + '".$monto."',
								saldo_disponible = saldo_disponible + '".$monto."'
						WHERE id = '".$asignacion_id."'
						";
					
					$mysqli->query($query_update_aumento);

					// FIN: ACTUALIZAR EL AUMENTO DE LA ASIGNACION


					if($mysqli->error)
					{
						$error .= $mysqli->error;
					}
				}
			}
		}

		if ($error == '') 
		{
			$result["http_code"] = 200;
			$result["status"] = "Datos obtenidos de gestión.";
			$result["error"] = $error;
			mepa_enviar_email_comprobante_programacion_pago($mepa_programacion_id, false);
		}
		else
		{
			$result["http_code"] = 400;
			$result["status"] = "Ocurrio un error.";
			$result["error"] = $error;
		}
	}
	else
	{
		$result["http_code"] = 400;
        $result["error"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_tesoreria_editar_comprobante_pago") 
{
	$error = '';

	$tesoreria_fecha_comprobante_pago_edit = $_POST['tesoreria_fecha_comprobante_pago_edit'];
	$tesoreria_fecha_comprobante_pago_edit = date("Y-m-d", strtotime($tesoreria_fecha_comprobante_pago_edit));

	$tesoreria_motivo_comprobante_pago_edit = $_POST['tesoreria_motivo_comprobante_pago_edit'];
	$mepa_programacion_id = $_POST['mepa_programacion_id'];

	if(isset($_FILES['tesoreria_comprobante_pago_edit']))
	{
		// INICIO: ELIMINAMOS LOS REGISTROS EXISTENTES DE LA BD

		$query_delete = "
						DELETE FROM mepa_caja_chica_programacion_files 
						WHERE mepa_caja_chica_programacion_id = '".$mepa_programacion_id."'
						";

		$mysqli->query($query_delete);

		if($mysqli->error)
		{
			$error .= $mysqli->error;

			$result["http_code"] = 400;
			$result["status"] = "Ocurrio un error.";
			$result["error"] = $error;

			echo json_encode($result);
			exit();
		}

		// FIN: ELIMINAMOS LOS REGISTROS EXISTENTES DE LA BD

		// INICIO: INSERTAMOS EL FILE EN LA TABLA mepa_caja_chica_programacion_files

		$path = "/var/www/html/files_bucket/mepa/programacion/comprobante_pago/";
		$download = "/files_bucket/mepa/programacion/comprobante_pago/";

		if (!is_dir($path)) 
		{
			mkdir($path, 0777, true);
		}

		$cant = 1;

		for ($i=0; $i < count($_FILES['tesoreria_comprobante_pago_edit']['name']); $i++)
		{
			$file_name = $_FILES['tesoreria_comprobante_pago_edit']['name'][$i];
			$file_tmp = $_FILES['tesoreria_comprobante_pago_edit']['tmp_name'][$i];
			$file_size = $_FILES['tesoreria_comprobante_pago_edit']['size'][$i];
			$file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
			

			$nombreFileUpload = "ID_".$mepa_programacion_id."_item_".$cant."_imagen".date('YmdHis'). ".".$file_extension;
			$nombreDownload = $download.$nombreFileUpload;
			move_uploaded_file($file_tmp, $path. $nombreFileUpload);

			$query_insert_file = "INSERT INTO mepa_caja_chica_programacion_files
								(
									mepa_caja_chica_programacion_id,
									imagen,
									extension,
									size,
									ruta,
								    download,
									status,
									user_created_id,
									created_at,
									user_updated_id,
									updated_at
								) 
								VALUES 
								(
									'".$mepa_programacion_id."',
									'".$nombreFileUpload."',
									'".$file_extension."',
									'".$file_size."',
									'".$path."',
								    '".$nombreDownload."',
								    1,
									'".$login["id"]."', 
									'".date('Y-m-d H:i:s')."',
									'".$login["id"]."', 
									'".date('Y-m-d H:i:s')."'
								)";

			$mysqli->query($query_insert_file);

			if($mysqli->error)
			{
				$error .= $mysqli->error;

				$result["http_code"] = 400;
				$result["status"] = "Ocurrio un error.";
				$result["error"] = $file_name;

				echo json_encode($result);
				exit();
			}

			$cant++;
		}

		// FIN: INSERTAMOS EL FILE EN LA TABLA mepa_caja_chica_programacion_files

		// INICIO: ACTUALIZAMOS LOS CAMPOS DE TESORERIA EN LA TABLA mepa_caja_chica_programacion

		$query_update = "
					UPDATE mepa_caja_chica_programacion 
						SET fecha_carga_comprobante = '".date('Y-m-d H:i:s')."',
						    user_id_carga_comprobante = '".$login["id"]."',
						    se_cargo_comprobante = 1,
						    fecha_comprobante = '".$tesoreria_fecha_comprobante_pago_edit."',
						    comentario_edicion_comprobante = '".$tesoreria_motivo_comprobante_pago_edit."',
						    situacion_etapa_id = 11
					WHERE id = '".$mepa_programacion_id."'
					";

		$mysqli->query($query_update);

		if($mysqli->error)
		{
			$error .= $mysqli->error;

			$result["http_code"] = 400;
			$result["status"] = "Ocurrio un error.";
			$result["error"] = $error;

			echo json_encode($result);
			exit();
		}

		// FIN: ACTUALIZAMOS LOS CAMPOS DE TESORERIA EN LA TABLA mepa_caja_chica_programacion
	}
	else
	{
		$result["http_code"] = 400;
		$result["status"] = "Ocurrio un error.";
		$result["error"] = "Por favor seleccionar un archivo.";

		echo json_encode($result);
		exit();
	}

	if ($error == '') 
	{
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestión.";
		$result["error"] = $error;
		mepa_enviar_email_comprobante_programacion_pago($mepa_programacion_id, true);
	}
	else 
	{
		$result["http_code"] = 400;
		$result["status"] = "Ocurrio un error.";
		$result["error"] = $error;
	}

}

function mepa_enviar_email_comprobante_programacion_pago($mepa_programacion_id, $edicion_comprobante)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];
	$titulo_email = "";

	$sel_query_verificacion = $mysqli->query("
								SELECT
									p.id, p.tipo_solicitud_id, p.user_id_carga_comprobante,
									concat(IFNULL(tpc.nombre, ''),' ', IFNULL(tpc.apellido_paterno, ''), ' ', IFNULL(tpc.apellido_materno, '')) AS usuario_atencion_comprobante,
				    				p.fecha_comprobante, p.comentario_edicion_comprobante
								FROM mepa_caja_chica_programacion p
									LEFT JOIN tbl_usuarios tuc
									ON p.user_id_carga_comprobante = tuc.id
									LEFT JOIN tbl_personal_apt tpc
									ON tuc.personal_id = tpc.id
								WHERE p.id = '".$mepa_programacion_id."'
							");

	$body = "";
	$body .= '<html>';

	while($sel = $sel_query_verificacion->fetch_assoc())
	{
		$id = $sel['id'];
		$tipo_solicitud_id = $sel['tipo_solicitud_id'];
		$usuario_atencion_comprobante = $sel['usuario_atencion_comprobante'];
		$fecha_comprobante = $sel['fecha_comprobante'];
		$comentario_edicion_comprobante = $sel['comentario_edicion_comprobante'];
		//$file = $sel['file'];


		$body .= '<div>';
			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

				$body .= '<thead>';
				
					$body .= '<tr>';
						$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
							if ($edicion_comprobante) 
							{
								$body .= '<b>Edición de Comprobante</b>';
							}
							else
							{
								$body .= '<b>Pago Realizado</b>';
							}
							
						$body .= '</th>';
					$body .= '</tr>';

				$body .= '</thead>';

				if ($edicion_comprobante) 
				{
					$body .= '<tr>';
						$body .= '<td style="background-color: #ffffdd; width: 155px;"><b>Motivo:</b></td>';
						$body .= '<td>'.$comentario_edicion_comprobante.'</td>';
					$body .= '</tr>';
				}

				$body .= '<tr>';
					$body .= '<td style="background-color: #ffffdd; width: 155px;"><b>Atendido por:</b></td>';
					$body .= '<td>'.$usuario_atencion_comprobante.'</td>';
				$body .= '</tr>';
			
				$body .= '<tr>';
					$body .= '<td style="background-color: #ffffdd"><b>Fecha de Comprobante:</b></td>';
					$body .= '<td>'.$fecha_comprobante.'</td>';
				$body .= '</tr>';

			$body .= '</table>';
		$body .= '</div>';

	}
		$body .= '</html>';
		$body .= "";


	$sub_titulo_email = "";

	if (env('SEND_EMAIL') == 'test')
	{
		$sub_titulo_email = "TEST SISTEMAS: ";
	}

	$cc = [
	];

	$bcc = [
	];

	// SI ES ASIGNACION
	if($tipo_solicitud_id == 1)
	{
		if($edicion_comprobante)
		{
			$titulo_email = $sub_titulo_email."Gestion - Sistema Mesa de Partes - Tesoreria: Corrección de Comprobante de Pago de Asignación Caja Chica";
		}
		else
		{
			$titulo_email = $sub_titulo_email."Gestion - Sistema Mesa de Partes - Tesoreria: Pago Realizado de Asignación Caja Chica";
		}

		$select_usuarios_enviar_email = 
		"
			SELECT
				p.id, p.tipo_solicitud_id,
			    p.user_id_carga_comprobante,
			    concat(IFNULL(tpc.nombre, ''),' ', IFNULL(tpc.apellido_paterno, ''), ' ', IFNULL(tpc.apellido_materno, '')) AS usuario_atencion_comprobante,
			    p.fecha_comprobante,
			    dt.nombre_tabla, dt.nombre_tabla_id,
			    a.usuario_asignado_id,
			    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado,
			    tp.correo,
			    concat(IFNULL(tpj.nombre, ''),' ', IFNULL(tpj.apellido_paterno, ''), ' ', IFNULL(tpj.apellido_materno, '')) AS usuario_jefe,
				tpj.correo AS correo_jefe
			FROM mepa_caja_chica_programacion p
				INNER JOIN mepa_caja_chica_programacion_detalle dt
				ON p.id = dt.mepa_caja_chica_programacion_id
				INNER JOIN mepa_asignacion_caja_chica a
				ON dt.nombre_tabla_id = a.id
				LEFT JOIN tbl_usuarios tuc
				ON p.user_id_carga_comprobante = tuc.id
				LEFT JOIN tbl_personal_apt tpc
				ON tuc.personal_id = tpc.id
				INNER JOIN tbl_usuarios tu
				ON a.usuario_asignado_id = tu.id
				INNER JOIN tbl_personal_apt tp
				ON tu.personal_id = tp.id
				INNER JOIN tbl_usuarios tuj
				ON a.user_created_id = tuj.id
				INNER JOIN tbl_personal_apt tpj
				ON tuj.personal_id = tpj.id
			WHERE p.id = '".$mepa_programacion_id."' 
				AND p.situacion_etapa_id = 11 
				AND dt.status = 1 
				AND a.situacion_etapa_id_tesoreria = 11
		";

		$sel_query_usuarios_enviar_email = $mysqli->query($select_usuarios_enviar_email);

		$row_count = $sel_query_usuarios_enviar_email->num_rows;

		if ($row_count > 0)
		{
			while($sel = $sel_query_usuarios_enviar_email->fetch_assoc())
			{
				if(!is_null($sel['correo']) AND !empty($sel['correo']))
				{
					array_push($cc, $sel['correo']);	
				}

				if(!is_null($sel['correo_jefe']) AND !empty($sel['correo_jefe']))
				{
					array_push($cc, $sel['correo_jefe']);
				}
			}
		}
	}
	// SI ES LIQUIDACION
	else if($tipo_solicitud_id == 2)
	{
		if($edicion_comprobante)
		{
			$titulo_email = $sub_titulo_email."Gestion - Sistema Mesa de Partes - Tesoreria: Corrección de Comprobante de Pago de Reembolso Caja Chica";
		}
		else
		{
			$titulo_email = $sub_titulo_email."Gestion - Sistema Mesa de Partes - Tesoreria: Pago Realizado de Reembolso Caja Chica";
		}
		
		$select_usuarios_enviar_email = 
		"
			SELECT
				p.id, p.tipo_solicitud_id,
				p.user_id_carga_comprobante,
				concat(IFNULL(tpc.nombre, ''),' ', IFNULL(tpc.apellido_paterno, ''), ' ', IFNULL(tpc.apellido_materno, '')) AS usuario_atencion_comprobante,
				p.fecha_comprobante,
				dt.nombre_tabla, dt.nombre_tabla_id,
				a.id AS asignacion_id,
				a.usuario_asignado_id,
				concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado,
				tp.correo,
				concat(IFNULL(tpj.nombre, ''),' ', IFNULL(tpj.apellido_paterno, ''), ' ', IFNULL(tpj.apellido_materno, '')) AS usuario_jefe,
				tpj.correo AS correo_jefe,
			    l.total_rendicion AS monto_liquidacion,
				m.monto_cierre AS monto_movilidad
			FROM mepa_caja_chica_programacion p
				INNER JOIN mepa_caja_chica_programacion_detalle dt
				ON p.id = dt.mepa_caja_chica_programacion_id
			    INNER JOIN mepa_caja_chica_liquidacion l
			    ON dt.nombre_tabla_id = l.id
				INNER JOIN mepa_asignacion_caja_chica a
				ON l.asignacion_id = a.id
				LEFT JOIN tbl_usuarios tuc
				ON p.user_id_carga_comprobante = tuc.id
				LEFT JOIN tbl_personal_apt tpc
				ON tuc.personal_id = tpc.id
				INNER JOIN tbl_usuarios tu
				ON a.usuario_asignado_id = tu.id
				INNER JOIN tbl_personal_apt tp
				ON tu.personal_id = tp.id
				INNER JOIN tbl_usuarios tuj
				ON a.user_created_id = tuj.id
				INNER JOIN tbl_personal_apt tpj
				ON tuj.personal_id = tpj.id
			    LEFT JOIN mepa_caja_chica_movilidad m
				ON l.id_movilidad = m.id
			WHERE p.id = '".$mepa_programacion_id."' 
				AND p.situacion_etapa_id = 11 
				AND dt.status = 1 
				AND l.situacion_etapa_id_tesoreria = 11
		";

		$sel_query_usuarios_enviar_email = $mysqli->query($select_usuarios_enviar_email);

		$row_count = $sel_query_usuarios_enviar_email->num_rows;

		if ($row_count > 0)
		{
			while($sel = $sel_query_usuarios_enviar_email->fetch_assoc())
			{
				if(!is_null($sel['correo']) AND !empty($sel['correo']))
				{
					array_push($cc, $sel['correo']);
				}

				if(!is_null($sel['correo_jefe']) AND !empty($sel['correo_jefe']))
				{
					array_push($cc, $sel['correo_jefe']);
				}
			}
		}
	}

	//INICIO: LISTAR USUARIOS DE PAGO REALIZADO DE:
	//SOLICITUD DE ASIGNACION
	//SOLICITUD DE LIQUIDACION
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
		WHERE cg.metodo = 'mepa_pagos_realizado_area_tesoreria' 
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
	//FIN: LISTAR USUARIOS DE PAGO REALIZADO DE:

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

		if(isset($request["attach"]))
		{
			if(is_array($request["attach"]))
			{
				for ($i=0; $i < count($request["attach"]) ; $i++) 
				{
					$mail->addAttachment($request["attach"][$i]);
				}
			}
			else
			{
				$mail->addAttachment($request["attach"]);
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

if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_tesoreria_asignacion_concar_excel")
{
	$programacion_id = $_POST['programacion_id'];
	$num_comprobante = $_POST['programacion_num_comprobante'];
	$param_num_documento = $_POST['programacion_num_documento'];

	$query_update_num_comprobante = "
						UPDATE mepa_caja_chica_programacion 
							SET numero_comprobante_concar = '".$num_comprobante."',
								numero_documento_concar = '".$param_num_documento."'
						WHERE id = '".$programacion_id."'
						";

	$mysqli->query($query_update_num_comprobante);

	$query_todos = "";

	$importe_dolares = 0;

	$fecha_dia = "";
	$fecha_mes = "";
	$fecha_anio = "";

	$query_cabecera = 
		"
		SELECT
			p.id AS programacion_id,
		    nc.subdiario AS sub_diario,
			p.fecha_comprobante,
		    (
				CASE 
					WHEN p.moneda_id = 1 THEN 'MN' 
					WHEN p.moneda_id = 2 THEN 'ME'
				END
			) AS codigo_moneda,
		    (
				CASE 
					WHEN p.tipo_solicitud_id = 1 THEN 'ASIGNACION DE CAJA CHICA'
					WHEN p.tipo_solicitud_id = 2 THEN 'REEMBOLSO DE CAJA CHICA'
				END
			) AS glosa_principal,
		    tc.id,
		    tc.monto_venta AS tipo_cambio,
		    'V' AS tipo_conversion,
		    'S' AS flag_conversion_moneda,
		    tc.fecha AS fecha_tipo_cambio,
		    nc.num_cuenta_contable AS cuenta_contable,
		    nc.cod_anexo AS codigo_anexo,
		    '' AS codigo_centro_costo,
		    'H' AS deber_haber,
		    (
				SELECT
					sum(ai.fondo_asignado)
				FROM mepa_caja_chica_programacion_detalle pdi
				INNER JOIN mepa_asignacion_caja_chica ai
				ON (ai.id = pdi.nombre_tabla_id)
				WHERE pdi.mepa_caja_chica_programacion_id = p.id AND pdi.status = 1
			) AS importe_original,
		    'TR' AS tipo_documento ,
		    '$param_num_documento' AS num_documento,
		    p.fecha_comprobante AS fecha_documento,
		    p.fecha_comprobante AS fecha_vencimiento,
		    '220' AS codigo_area,
		    'ASIGNACION DE CAJA CHICA' AS glosa_detalle,
		    ''AS codigo_anexo_auxiliar,
		    '003' AS medio_pago,
		    '' AS tipo_documento_referencia,
		    '' AS num_documento_referencia,
		    '' AS fecha_documento_referencia,
		    '' AS maquina_registradora_tipo_documento,
		    '' AS base_imponible_documento_referencia,
		    '' AS igv_documento_provision,
		    '' AS tipo_referencia_estado,
		    '' AS num_serie_caja_registradora,
		    '' AS fecha_operacion,
		    '' AS tipo_tasa,
		    '' AS tasa_detraccion_percepcion,
		    '' AS importe_base_detraccion_percepcion_dolares,
		    '' AS importe_base_detraccion_percepcion_soles,
		    '' AS tipo_cambio_para_f,
		    '' AS importe_igv_sin_derecho_credito_fiscal,
		    '' AS tasa_igv
		FROM mepa_caja_chica_programacion p
			INNER JOIN cont_num_cuenta nc
			ON p.num_cuenta_id = nc.id
			LEFT JOIN tbl_tipo_cambio tc
			ON p.fecha_comprobante = tc.fecha
		WHERE p.id = {$programacion_id} AND p.status = 1
		";

	$query_detalle = 
		"
		SELECT
			p.id AS programacion_id,
			nc.subdiario AS sub_diario,
			p.fecha_comprobante,
			(
				CASE 
					WHEN p.moneda_id = 1 THEN 'MN' 
					WHEN p.moneda_id = 2 THEN 'ME'
				END
			) AS codigo_moneda,
			(
				CASE 
					WHEN p.tipo_solicitud_id = 1 THEN 'ASIGNACION DE CAJA CHICA'
					WHEN p.tipo_solicitud_id = 2 THEN 'REEMBOLSO DE CAJA CHICA'
				END
			) AS glosa_principal,
			tc.id,
			tc.monto_venta AS tipo_cambio,
			'V' AS tipo_conversion,
			'S' AS flag_conversion_moneda,
			tc.fecha AS fecha_tipo_cambio,
			'102101' AS cuenta_contable, 
			tp.dni AS codigo_anexo, -- DNI
			'' AS codigo_centro_costo,
			'D' AS deber_haber,
		    a.fondo_asignado AS importe_original,
			'CJ' AS tipo_documento ,
			'SG APERTURA' AS num_documento,
			p.fecha_comprobante AS fecha_documento,
			p.fecha_comprobante AS fecha_vencimiento,
			'140' AS codigo_area,
			'ASIGNACION DE CAJA CHICA' AS glosa_detalle,
			''AS codigo_anexo_auxiliar,
			'' AS medio_pago,
			'' AS tipo_documento_referencia,
			'' AS num_documento_referencia,
			'' AS fecha_documento_referencia,
			'' AS maquina_registradora_tipo_documento,
			'' AS base_imponible_documento_referencia,
			'' AS igv_documento_provision,
			'' AS tipo_referencia_estado,
			'' AS num_serie_caja_registradora,
			'' AS fecha_operacion,
			'' AS tipo_tasa,
			'' AS tasa_detraccion_percepcion,
			'' AS importe_base_detraccion_percepcion_dolares,
			'' AS importe_base_detraccion_percepcion_soles,
			'' AS tipo_cambio_para_f,
			'' AS importe_igv_sin_derecho_credito_fiscal,
			'' AS tasa_igv
		FROM mepa_caja_chica_programacion p
			INNER JOIN mepa_caja_chica_programacion_detalle pd
			ON p.id = pd.mepa_caja_chica_programacion_id
			INNER JOIN mepa_asignacion_caja_chica a
			ON a.id = pd.nombre_tabla_id
			LEFT JOIN tbl_tipo_cambio tc
			ON p.fecha_comprobante = tc.fecha
			INNER JOIN tbl_usuarios tu
			ON a.usuario_asignado_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN cont_num_cuenta nc
			ON p.num_cuenta_id = nc.id
		WHERE p.id = {$programacion_id} AND p.status = 1 AND pd.status = 1
		";
	
	$query_todos.= $query_cabecera;
	$query_todos.= "UNION ALL";
	$query_todos.= $query_detalle;

	$list_query = $mysqli->query($query_todos);

	//PROCEDEMOS A CREAR LA CARPETA SI EN CASO NO EXISTA
	$path = "/var/www/html/files_bucket/mepa/descargas/tesoreria/concar_asignacion/";

	if (!is_dir($path)) 
	{
		mkdir($path, 0777, true);
	}

	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/mepa/descargas/tesoreria/concar_asignacion/*'); //obtenemos todos los nombres de los ficheros
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

	$tituloReporte = "Reporte Concar";

	$titulosColumnas = array('Sub Diario', 'Número de Comprobante', 'Fecha de Comprobante', 'Código de Moneda', 'Glosa Principal', 'Tipo de Cambio', 'Tipo de Conversión', 'Flag de Conversión de Moneda', 'Fecha Tipo de Cambio', 'Cuenta Contable', 'Código de Anexo', 'Código de Centro de Costo', 'Debe / Haber', 'Importe Original', 'Importe en Dólares', 'Importe en Soles', 'Tipo de Documento', 'Número de Documento', 'Fecha de Documento', 'Fecha de Vencimiento', 'Código de Area', 'Glosa Detalle', 'Código de Anexo Auxiliar', 'Medio de Pago', 'Tipo de Documento de Referencia', 'Número de Documento Referencia', 'Fecha Documento Referencia', 'Nro Máq. Registradora Tipo Doc. Ref.', 'Base Imponible Documento Referencia', 'IGV Documento Provisión', 'Tipo Referencia en estado MQ', 'Número Serie Caja Registradora', 'Fecha de Operación', 'Tipo de Tasa', 'Tasa Detracción/Percepción', 'Importe Base Detracción/Percepción Dólares', 'Importe Base Detracción/Percepción Soles', 'Tipo Cambio para F', 'Importe de IGV sin derecho credito fiscal', 'Tasa IGV');


	// Se agregan los titulos del reporte
	$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('B1', $titulosColumnas[0])	//Titulo de las columnas
			    ->setCellValue('C1', $titulosColumnas[1])
			    ->setCellValue('D1', $titulosColumnas[2])
			    ->setCellValue('E1', $titulosColumnas[3])
			    ->setCellValue('F1', $titulosColumnas[4])
			    ->setCellValue('G1', $titulosColumnas[5])
			    ->setCellValue('H1', $titulosColumnas[6])
			    ->setCellValue('I1', $titulosColumnas[7])
			    ->setCellValue('J1', $titulosColumnas[8])
			    ->setCellValue('K1', $titulosColumnas[9])
			    ->setCellValue('L1', $titulosColumnas[10])
			    ->setCellValue('M1', $titulosColumnas[11])
			    ->setCellValue('N1', $titulosColumnas[12])
			    ->setCellValue('O1', $titulosColumnas[13])
			    ->setCellValue('P1', $titulosColumnas[14])
			    ->setCellValue('Q1', $titulosColumnas[15])
			    ->setCellValue('R1', $titulosColumnas[16])
			    ->setCellValue('S1', $titulosColumnas[17])
			    ->setCellValue('T1', $titulosColumnas[18])
			    ->setCellValue('U1', $titulosColumnas[19])
			    ->setCellValue('V1', $titulosColumnas[20])
			    ->setCellValue('W1', $titulosColumnas[21])
			    ->setCellValue('X1', $titulosColumnas[22])
			    ->setCellValue('Y1', $titulosColumnas[23])
			    ->setCellValue('Z1', $titulosColumnas[24])
			    ->setCellValue('AA1', $titulosColumnas[25])
			    ->setCellValue('AB1', $titulosColumnas[26])
			    ->setCellValue('AC1', $titulosColumnas[27])
			    ->setCellValue('AD1', $titulosColumnas[28])
			    ->setCellValue('AE1', $titulosColumnas[29])
			    ->setCellValue('AF1', $titulosColumnas[30])
			    ->setCellValue('AG1', $titulosColumnas[31])
			    ->setCellValue('AH1', $titulosColumnas[32])
			    ->setCellValue('AI1', $titulosColumnas[33])
			    ->setCellValue('AJ1', $titulosColumnas[34])
			    ->setCellValue('AK1', $titulosColumnas[35])
			    ->setCellValue('AL1', $titulosColumnas[36])
			    ->setCellValue('AM1', $titulosColumnas[37])
			    ->setCellValue('AN1', $titulosColumnas[38])
			    ->setCellValue('AO1', $titulosColumnas[39]);

	//Se agregan los datos a la lista del reporte

	$i = 4; //Numero de fila donde se va a comenzar a rellenar
	while ($fila = $list_query->fetch_array()) 
	{
		if(is_null($fila['tipo_cambio']) OR empty($fila['tipo_cambio']))
		{
			echo json_encode(array(
				"ruta_archivo" => "No existe el tipo de cambio de la fecha ".$fila['fecha_comprobante'],
				"estado_archivo" => 0
			));
			exit;
		}

		$fecha_anio = date('Y', strtotime($fila['fecha_comprobante']));
		$fecha_mes = date('m', strtotime($fila['fecha_comprobante']));

		$importe_dolares = $fila['importe_original'] / $fila['tipo_cambio'];

		$objPHPExcel->setActiveSheetIndex(0)
	         ->setCellValue('B'.$i, substr($fila['sub_diario'], 0, 4))
	         ->setCellValue('C'.$i, substr($fecha_mes.str_pad($num_comprobante, 4, '0', STR_PAD_LEFT), 0, 6))
	         ->setCellValue('D'.$i, date('d/m/Y', strtotime($fila['fecha_comprobante'])))
	         ->setCellValue('E'.$i, substr($fila['codigo_moneda'], 0, 2))
	         ->setCellValue('F'.$i, substr($fila['glosa_principal'], 0, 40))
	         ->setCellValue('G'.$i, $fila['tipo_cambio'])
	         ->setCellValue('H'.$i, $fila['tipo_conversion'])
	         ->setCellValue('I'.$i, substr($fila['flag_conversion_moneda'], 0, 1))
	         ->setCellValue('J'.$i, date('d/m/Y', strtotime($fila['fecha_tipo_cambio'])))
	         ->setCellValue('K'.$i, substr($fila['cuenta_contable'], 0, 12))
	         ->setCellValue('L'.$i, substr($fila['codigo_anexo'], 0, 18))
	         ->setCellValue('M'.$i, substr($fila['codigo_centro_costo'], 0, 6))
	         ->setCellValue('N'.$i, substr($fila['deber_haber'], 0, 1))
	         ->setCellValue('O'.$i, $fila['importe_original'])
	         ->setCellValue('P'.$i, $importe_dolares)
	         ->setCellValue('Q'.$i, $fila['importe_original']) //IMPORTE EN SOLES ES LO MISMO QUE EL IMPORTE ORIGINAL
	         ->setCellValue('R'.$i, substr($fila['tipo_documento'], 0, 2))
	         ->setCellValue('S'.$i, substr($fila['num_documento'], 0, 20))
	         ->setCellValue('T'.$i, date('d/m/Y', strtotime($fila['fecha_documento'])))
	         ->setCellValue('U'.$i, date('d/m/Y', strtotime($fila['fecha_vencimiento'])))
	         ->setCellValue('V'.$i, substr($fila['codigo_area'], 0, 3))
	         ->setCellValue('W'.$i, substr($fila['glosa_detalle'], 0, 30))
	         ->setCellValue('X'.$i, substr($fila['codigo_anexo_auxiliar'], 0, 18))
	         ->setCellValue('Y'.$i, substr($fila['medio_pago'], 0, 8))
	         ->setCellValue('Z'.$i, substr($fila['tipo_documento_referencia'], 0, 2))
	         ->setCellValue('AA'.$i, substr($fila['num_documento_referencia'], 0, 20))
	         ->setCellValue('AB'.$i, $fila['fecha_documento_referencia'])
	         ->setCellValue('AC'.$i, substr($fila['maquina_registradora_tipo_documento'], 0, 20))
	         ->setCellValue('AD'.$i, $fila['base_imponible_documento_referencia'])
	         ->setCellValue('AE'.$i, $fila['igv_documento_provision'])
	         ->setCellValue('AF'.$i, $fila['tipo_referencia_estado'])
	         ->setCellValue('AG'.$i, substr($fila['num_serie_caja_registradora'], 0, 15))
	         ->setCellValue('AH'.$i, $fila['fecha_operacion'])
	         ->setCellValue('AI'.$i, substr($fila['tipo_tasa'], 0, 5))
	         ->setCellValue('AJ'.$i, $fila['tasa_detraccion_percepcion'])
	         ->setCellValue('AK'.$i, $fila['importe_base_detraccion_percepcion_dolares'])
	         ->setCellValue('AL'.$i, $fila['importe_base_detraccion_percepcion_soles'])
	         ->setCellValue('AM'.$i, substr($fila['tipo_cambio_para_f'], 0, 1))
	         ->setCellValue('AN'.$i, $fila['importe_igv_sin_derecho_credito_fiscal'])
	         ->setCellValue('AO'.$i, $fila['tasa_igv']);
	     $i++;

	}


	$estiloColoFondoAmarilloOscuro = array(
	    'fill' => array(
	      'type'  => PHPExcel_Style_Fill::FILL_SOLID,
	      'color' => array(
	            'rgb' => 'ffc000')
	  )
	);
	  
	$estiloTituloColumnas = array(
	    'font' => array(
	        'name'  => 'Arial',
	        'bold'  => false,
	        'size' => 10,
	        'color' => array(
	            'rgb' => '000000'
	        )
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


	$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(57);



	$objPHPExcel->getActiveSheet()->getStyle('B1:Z1')->applyFromArray($estiloTituloColumnas);
	$objPHPExcel->getActiveSheet()->getStyle('AA1:AO1')->applyFromArray($estiloTituloColumnas);

	$objPHPExcel->getActiveSheet()->getStyle('B1:Z1')->applyFromArray($estiloColoFondoAmarilloOscuro);
	$objPHPExcel->getActiveSheet()->getStyle('AA1:AO1')->applyFromArray($estiloColoFondoAmarilloOscuro);

	
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A4:AN".($i-1));

	$objPHPExcel->getActiveSheet()->getStyle('O4:O'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');
	$objPHPExcel->getActiveSheet()->getStyle('P4:P'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');
	$objPHPExcel->getActiveSheet()->getStyle('Q4:Q'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');

	// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
	for($i = 'B'; $i <= 'Z'; $i++)
	{
	    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}

	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('Mesa de Partes');
	  
	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);
	
	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(0);
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Plantilla Concar Asignacion Cajas Chicas.xls');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$excel_path = '/var/www/html/files_bucket/mepa/descargas/tesoreria/concar_asignacion/Plantilla Concar Asignacion Cajas Chicas.xls';
	$excel_path_download = '/files_bucket/mepa/descargas/tesoreria/concar_asignacion/Plantilla Concar Asignacion Cajas Chicas.xls';

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
		"ruta_archivo" => $excel_path_download,
		"estado_archivo" => 1
	));
	exit;
}

if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_tesoreria_liquidacion_concar_excel")
{
	$programacion_id = $_POST['programacion_id'];
	$num_comprobante = $_POST['programacion_num_comprobante'];
	$param_num_documento = $_POST['programacion_num_documento'];

	$query_update_num_comprobante = "
						UPDATE mepa_caja_chica_programacion 
							SET numero_comprobante_concar = '".$num_comprobante."',
							numero_documento_concar = '".$param_num_documento."'
						WHERE id = '".$programacion_id."'
						";

	$mysqli->query($query_update_num_comprobante);

	$query_todos = "";

	$importe_original = 0;
	$importe_dolares = 0;

	$fecha_dia = "";
	$fecha_mes = "";
	$fecha_anio = "";

	$query_cabecera =
		"
		SELECT
			p.id AS programacion_id,
			nc.subdiario AS sub_diario,
			p.fecha_comprobante,
			(
				CASE 
					WHEN p.moneda_id = 1 THEN 'MN' 
					WHEN p.moneda_id = 2 THEN 'ME'
				END
			) AS codigo_moneda,
			(
				CASE 
					WHEN p.tipo_solicitud_id = 1 THEN 'ASIGNACION DE CAJA CHICA'
					WHEN p.tipo_solicitud_id = 2 THEN 'REEMBOLSO DE CAJA CHICA'
				END
			) AS glosa_principal,
			tc.id,
			tc.monto_venta AS tipo_cambio,
			'V' AS tipo_conversion,
			'S' AS flag_conversion_moneda,
			tc.fecha AS fecha_tipo_cambio,
			nc.num_cuenta_contable AS cuenta_contable,
		    nc.cod_anexo AS codigo_anexo,
			'' AS codigo_centro_costo,
			'H' AS deber_haber,
			(
				SELECT
					sum(l.sub_total)
				FROM mepa_caja_chica_programacion_detalle pdi
					INNER JOIN mepa_caja_chica_liquidacion l
					ON (l.id = pdi.nombre_tabla_id)
				WHERE pdi.mepa_caja_chica_programacion_id = p.id AND pdi.status = 1
			) AS importe_original_liquidacion,
			0 AS importe_original_liquidacion_movilidad,
			'TR' AS tipo_documento,
			'$param_num_documento' AS num_documento,
			p.fecha_comprobante AS fecha_documento,
			p.fecha_comprobante AS fecha_vencimiento,
			'220' AS codigo_area,
			'REEMBOLSO DE CAJA CHICA' AS glosa_detalle,
			''AS codigo_anexo_auxiliar,
			'003' AS medio_pago,
			'' AS tipo_documento_referencia,
			'' AS num_documento_referencia,
			'' AS fecha_documento_referencia,
			'' AS maquina_registradora_tipo_documento,
			'' AS base_imponible_documento_referencia,
			'' AS igv_documento_provision,
			'' AS tipo_referencia_estado,
			'' AS num_serie_caja_registradora,
			'' AS fecha_operacion,
			'' AS tipo_tasa,
			'' AS tasa_detraccion_percepcion,
			'' AS importe_base_detraccion_percepcion_dolares,
			'' AS importe_base_detraccion_percepcion_soles,
			'' AS tipo_cambio_para_f,
			'' AS importe_igv_sin_derecho_credito_fiscal,
			'' AS tasa_igv
		FROM mepa_caja_chica_programacion p
			INNER JOIN cont_num_cuenta nc
			ON p.num_cuenta_id = nc.id
			LEFT JOIN tbl_tipo_cambio tc
			ON p.fecha_comprobante = tc.fecha
		WHERE p.id = {$programacion_id} AND p.status = 1
		";

	$query_detalle = 
		"
		SELECT
			p.id AS programacion_id,
			nc.subdiario AS sub_diario,
			p.fecha_comprobante,
			(
				CASE 
					WHEN p.moneda_id = 1 THEN 'MN' 
					WHEN p.moneda_id = 2 THEN 'ME'
				END
			) AS codigo_moneda,
			(
				CASE 
					WHEN p.tipo_solicitud_id = 1 THEN 'ASIGNACION DE CAJA CHICA'
					WHEN p.tipo_solicitud_id = 2 THEN 'REEMBOLSO DE CAJA CHICA'
				END
			) AS glosa_principal,
			tc.id,
			tc.monto_venta AS tipo_cambio,
			'V' AS tipo_conversion,
			'S' AS flag_conversion_moneda,
			tc.fecha AS fecha_tipo_cambio,
			'102101' AS cuenta_contable, 
			tp.dni AS codigo_anexo,
			'' AS codigo_centro_costo,
			'D' AS deber_haber,
		    l.sub_total AS importe_original_liquidacion,
			0 AS importe_original_liquidacion_movilidad,
			'CJ' AS tipo_documento ,
			concat('SG ', LPAD(l.num_correlativo, 3, '0'), '-', DATE_FORMAT(p.fecha_comprobante, '%Y')) AS num_documento,
			p.fecha_comprobante AS fecha_documento,
			p.fecha_comprobante AS fecha_vencimiento,
			'140' AS codigo_area,
			'REEMBOLSO DE CAJA CHICA' AS glosa_detalle,
			''AS codigo_anexo_auxiliar,
			'' AS medio_pago,
			'' AS tipo_documento_referencia,
			'' AS num_documento_referencia,
			'' AS fecha_documento_referencia,
			'' AS maquina_registradora_tipo_documento,
			'' AS base_imponible_documento_referencia,
			'' AS igv_documento_provision,
			'' AS tipo_referencia_estado,
			'' AS num_serie_caja_registradora,
			'' AS fecha_operacion,
			'' AS tipo_tasa,
			'' AS tasa_detraccion_percepcion,
			'' AS importe_base_detraccion_percepcion_dolares,
			'' AS importe_base_detraccion_percepcion_soles,
			'' AS tipo_cambio_para_f,
			'' AS importe_igv_sin_derecho_credito_fiscal,
			'' AS tasa_igv
		FROM mepa_caja_chica_programacion p
			INNER JOIN mepa_caja_chica_programacion_detalle pd
			ON p.id = pd.mepa_caja_chica_programacion_id
			INNER JOIN mepa_caja_chica_liquidacion l
			ON pd.nombre_tabla_id = l.id
			INNER JOIN mepa_asignacion_caja_chica a
			ON l.asignacion_id = a.id
			LEFT JOIN tbl_tipo_cambio tc
			ON p.fecha_comprobante = tc.fecha
			INNER JOIN tbl_usuarios tu
			ON a.usuario_asignado_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN cont_num_cuenta nc
			ON p.num_cuenta_id = nc.id
			LEFT JOIN mepa_caja_chica_movilidad m
			ON l.id_movilidad = m.id
		WHERE p.id = {$programacion_id} AND p.status = 1 AND pd.status = 1
		";
	
	$query_todos.= $query_cabecera;
	$query_todos.= "UNION ALL";
	$query_todos.= $query_detalle;

	$list_query = $mysqli->query($query_todos);

	//PROCEDEMOS A CREAR LA CARPETA SI EN CASO NO EXISTA
	$path = "/var/www/html/files_bucket/mepa/descargas/tesoreria/concar_liquidacion/";

	if (!is_dir($path)) 
	{
		mkdir($path, 0777, true);
	}

	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/mepa/descargas/tesoreria/concar_liquidacion/*'); //obtenemos todos los nombres de los ficheros
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

	$tituloReporte = "Reporte Concar";

	$titulosColumnas = array('Sub Diario', 'Número de Comprobante', 'Fecha de Comprobante', 'Código de Moneda', 'Glosa Principal', 'Tipo de Cambio', 'Tipo de Conversión', 'Flag de Conversión de Moneda', 'Fecha Tipo de Cambio', 'Cuenta Contable', 'Código de Anexo', 'Código de Centro de Costo', 'Debe / Haber', 'Importe Original', 'Importe en Dólares', 'Importe en Soles', 'Tipo de Documento', 'Número de Documento', 'Fecha de Documento', 'Fecha de Vencimiento', 'Código de Area', 'Glosa Detalle', 'Código de Anexo Auxiliar', 'Medio de Pago', 'Tipo de Documento de Referencia', 'Número de Documento Referencia', 'Fecha Documento Referencia', 'Nro Máq. Registradora Tipo Doc. Ref.', 'Base Imponible Documento Referencia', 'IGV Documento Provisión', 'Tipo Referencia en estado MQ', 'Número Serie Caja Registradora', 'Fecha de Operación', 'Tipo de Tasa', 'Tasa Detracción/Percepción', 'Importe Base Detracción/Percepción Dólares', 'Importe Base Detracción/Percepción Soles', 'Tipo Cambio para F', 'Importe de IGV sin derecho credito fiscal', 'Tasa IGV');


	// Se agregan los titulos del reporte
	$objPHPExcel->setActiveSheetIndex(0)
			    ->setCellValue('B1', $titulosColumnas[0])	//Titulo de las columnas
			    ->setCellValue('C1', $titulosColumnas[1])
			    ->setCellValue('D1', $titulosColumnas[2])
			    ->setCellValue('E1', $titulosColumnas[3])
			    ->setCellValue('F1', $titulosColumnas[4])
			    ->setCellValue('G1', $titulosColumnas[5])
			    ->setCellValue('H1', $titulosColumnas[6])
			    ->setCellValue('I1', $titulosColumnas[7])
			    ->setCellValue('J1', $titulosColumnas[8])
			    ->setCellValue('K1', $titulosColumnas[9])
			    ->setCellValue('L1', $titulosColumnas[10])
			    ->setCellValue('M1', $titulosColumnas[11])
			    ->setCellValue('N1', $titulosColumnas[12])
			    ->setCellValue('O1', $titulosColumnas[13])
			    ->setCellValue('P1', $titulosColumnas[14])
			    ->setCellValue('Q1', $titulosColumnas[15])
			    ->setCellValue('R1', $titulosColumnas[16])
			    ->setCellValue('S1', $titulosColumnas[17])
			    ->setCellValue('T1', $titulosColumnas[18])
			    ->setCellValue('U1', $titulosColumnas[19])
			    ->setCellValue('V1', $titulosColumnas[20])
			    ->setCellValue('W1', $titulosColumnas[21])
			    ->setCellValue('X1', $titulosColumnas[22])
			    ->setCellValue('Y1', $titulosColumnas[23])
			    ->setCellValue('Z1', $titulosColumnas[24])
			    ->setCellValue('AA1', $titulosColumnas[25])
			    ->setCellValue('AB1', $titulosColumnas[26])
			    ->setCellValue('AC1', $titulosColumnas[27])
			    ->setCellValue('AD1', $titulosColumnas[28])
			    ->setCellValue('AE1', $titulosColumnas[29])
			    ->setCellValue('AF1', $titulosColumnas[30])
			    ->setCellValue('AG1', $titulosColumnas[31])
			    ->setCellValue('AH1', $titulosColumnas[32])
			    ->setCellValue('AI1', $titulosColumnas[33])
			    ->setCellValue('AJ1', $titulosColumnas[34])
			    ->setCellValue('AK1', $titulosColumnas[35])
			    ->setCellValue('AL1', $titulosColumnas[36])
			    ->setCellValue('AM1', $titulosColumnas[37])
			    ->setCellValue('AN1', $titulosColumnas[38])
			    ->setCellValue('AO1', $titulosColumnas[39]);

	//Se agregan los datos a la lista del reporte

	$i = 4; //Numero de fila donde se va a comenzar a rellenar
	while ($fila = $list_query->fetch_array()) 
	{
		if(is_null($fila['tipo_cambio']) OR empty($fila['tipo_cambio']))
		{
			echo json_encode(array(
				"ruta_archivo" => "No existe el tipo de cambio de la fecha ".$fila['fecha_comprobante'],
				"estado_archivo" => 0
			));
			exit;
		}

		$fecha_anio = date('Y', strtotime($fila['fecha_comprobante']));
		$fecha_mes = date('m', strtotime($fila['fecha_comprobante']));

		$importe_original = $fila['importe_original_liquidacion']; 
		$importe_dolares = $importe_original / $fila['tipo_cambio'];

		$objPHPExcel->setActiveSheetIndex(0)
	         ->setCellValue('B'.$i, substr($fila['sub_diario'], 0, 4))
	         ->setCellValue('C'.$i, substr($fecha_mes.str_pad($num_comprobante, 4, '0', STR_PAD_LEFT), 0, 6))
	         ->setCellValue('D'.$i, date('d/m/Y', strtotime($fila['fecha_comprobante']))) 		         
	         ->setCellValue('E'.$i, substr($fila['codigo_moneda'], 0, 2))
	         ->setCellValue('F'.$i, substr($fila['glosa_principal'], 0, 40))
	         ->setCellValue('G'.$i, $fila['tipo_cambio'])
	         ->setCellValue('H'.$i, $fila['tipo_conversion'])
	         ->setCellValue('I'.$i, substr($fila['flag_conversion_moneda'], 0, 1))
	         ->setCellValue('J'.$i, date('d/m/Y', strtotime($fila['fecha_tipo_cambio'])))
	         ->setCellValue('K'.$i, substr($fila['cuenta_contable'], 0, 12))
	         ->setCellValue('L'.$i, substr($fila['codigo_anexo'], 0, 18))
	         ->setCellValue('M'.$i, substr($fila['codigo_centro_costo'], 0, 6))
	         ->setCellValue('N'.$i, substr($fila['deber_haber'], 0, 1))
	         ->setCellValue('O'.$i, $importe_original)
	         ->setCellValue('P'.$i, $importe_dolares)
	         ->setCellValue('Q'.$i, $importe_original) //IMPORTE EN SOLES ES LO MISMO QUE EL IMPORTE ORIGINAL
	         ->setCellValue('R'.$i, substr($fila['tipo_documento'], 0, 2))
	         ->setCellValue('S'.$i, substr($fila['num_documento'], 0, 20))
	         ->setCellValue('T'.$i, date('d/m/Y', strtotime($fila['fecha_documento'])))
	         ->setCellValue('U'.$i, date('d/m/Y', strtotime($fila['fecha_vencimiento'])))
	         ->setCellValue('V'.$i, substr($fila['codigo_area'], 0, 3))
	         ->setCellValue('W'.$i, substr($fila['glosa_detalle'], 0, 30))
	         ->setCellValue('X'.$i, substr($fila['codigo_anexo_auxiliar'], 0, 18))
	         ->setCellValue('Y'.$i, substr($fila['medio_pago'], 0, 8))
	         ->setCellValue('Z'.$i, substr($fila['tipo_documento_referencia'], 0, 2))
	         ->setCellValue('AA'.$i, substr($fila['num_documento_referencia'], 0, 20))
	         ->setCellValue('AB'.$i, $fila['fecha_documento_referencia'])
	         ->setCellValue('AC'.$i, substr($fila['maquina_registradora_tipo_documento'], 0, 20))
	         ->setCellValue('AD'.$i, $fila['base_imponible_documento_referencia'])
	         ->setCellValue('AE'.$i, $fila['igv_documento_provision'])
	         ->setCellValue('AF'.$i, $fila['tipo_referencia_estado'])
	         ->setCellValue('AG'.$i, substr($fila['num_serie_caja_registradora'], 0, 15))
	         ->setCellValue('AH'.$i, $fila['fecha_operacion'])
	         ->setCellValue('AI'.$i, substr($fila['tipo_tasa'], 0, 5))
	         ->setCellValue('AJ'.$i, $fila['tasa_detraccion_percepcion'])
	         ->setCellValue('AK'.$i, $fila['importe_base_detraccion_percepcion_dolares'])
	         ->setCellValue('AL'.$i, $fila['importe_base_detraccion_percepcion_soles'])
	         ->setCellValue('AM'.$i, substr($fila['tipo_cambio_para_f'], 0, 1))
	         ->setCellValue('AN'.$i, $fila['importe_igv_sin_derecho_credito_fiscal'])
	         ->setCellValue('AO'.$i, $fila['tasa_igv']);
	     $i++;

	}


	$estiloColoFondoAmarilloOscuro = array(
	    'fill' => array(
	      'type'  => PHPExcel_Style_Fill::FILL_SOLID,
	      'color' => array(
	            'rgb' => 'ffc000')
	  )
	);
	  
	$estiloTituloColumnas = array(
	    'font' => array(
	        'name'  => 'Arial',
	        'bold'  => false,
	        'size' => 10,
	        'color' => array(
	            'rgb' => '000000'
	        )
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


	$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(57);



	$objPHPExcel->getActiveSheet()->getStyle('B1:Z1')->applyFromArray($estiloTituloColumnas);
	$objPHPExcel->getActiveSheet()->getStyle('AA1:AO1')->applyFromArray($estiloTituloColumnas);

	$objPHPExcel->getActiveSheet()->getStyle('B1:Z1')->applyFromArray($estiloColoFondoAmarilloOscuro);
	$objPHPExcel->getActiveSheet()->getStyle('AA1:AO1')->applyFromArray($estiloColoFondoAmarilloOscuro);

	
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A4:AN".($i-1));

	$objPHPExcel->getActiveSheet()->getStyle('O4:O'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');
	$objPHPExcel->getActiveSheet()->getStyle('P4:P'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');
	$objPHPExcel->getActiveSheet()->getStyle('Q4:Q'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');

	// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
	for($i = 'B'; $i <= 'Z'; $i++)
	{
	    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}

	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('Mesa de Partes');
	  
	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);
	
	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(0);
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Plantilla Concar Reembolso Cajas Chicas.xls');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$excel_path = '/var/www/html/files_bucket/mepa/descargas/tesoreria/concar_liquidacion/Plantilla Concar Reembolso Cajas Chicas.xls';
	$excel_path_download = '/files_bucket/mepa/descargas/tesoreria/concar_liquidacion/Plantilla Concar Reembolso Cajas Chicas.xls';

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
		"ruta_archivo" => $excel_path_download,
		"estado_archivo" => 1
	));
	exit;
}

if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_tesoreria_aumento_asignacion_concar_excel")
{
	$programacion_id = $_POST['programacion_id'];
	$num_comprobante = $_POST['programacion_num_comprobante'];
	$param_num_documento = $_POST['programacion_num_documento'];

	$query_update_num_comprobante = "
						UPDATE mepa_caja_chica_programacion 
							SET numero_comprobante_concar = '".$num_comprobante."',
								numero_documento_concar = '".$param_num_documento."'
						WHERE id = '".$programacion_id."'
						";

	$mysqli->query($query_update_num_comprobante);

	$query_todos = "";

	$importe_dolares = 0;

	$fecha_dia = "";
	$fecha_mes = "";
	$fecha_anio = "";

	$query_cabecera = 
		"
		SELECT
			p.id AS programacion_id,
		    nc.subdiario AS sub_diario,
			p.fecha_comprobante,
		    (
				CASE 
					WHEN p.moneda_id = 1 THEN 'MN' 
					WHEN p.moneda_id = 2 THEN 'ME'
				END
			) AS codigo_moneda,
		    (
				CASE 
					WHEN p.tipo_solicitud_id = 1 THEN 'ASIGNACION DE CAJA CHICA'
					WHEN p.tipo_solicitud_id = 2 THEN 'REEMBOLSO DE CAJA CHICA'
					WHEN p.tipo_solicitud_id = 9 THEN 'AUMENTO DE CAJA CHICA'
				END
			) AS glosa_principal,
		    tc.id,
		    tc.monto_venta AS tipo_cambio,
		    'V' AS tipo_conversion,
		    'S' AS flag_conversion_moneda,
		    tc.fecha AS fecha_tipo_cambio,
		    nc.num_cuenta_contable AS cuenta_contable,
		    nc.cod_anexo AS codigo_anexo,
		    '' AS codigo_centro_costo,
		    'H' AS deber_haber,
		    (
				SELECT
					sum(aa.monto)
				FROM mepa_caja_chica_programacion_detalle pdi
				INNER JOIN mepa_aumento_asignacion aa
				ON (aa.id = pdi.nombre_tabla_id)
				WHERE pdi.mepa_caja_chica_programacion_id = p.id AND pdi.status = 1
			) AS importe_original,
		    'TR' AS tipo_documento ,
		    '$param_num_documento' AS num_documento,
		    p.fecha_comprobante AS fecha_documento,
		    p.fecha_comprobante AS fecha_vencimiento,
		    '220' AS codigo_area,
		    'AUMENTO DE CAJA CHICA' AS glosa_detalle,
		    ''AS codigo_anexo_auxiliar,
		    '003' AS medio_pago,
		    '' AS tipo_documento_referencia,
		    '' AS num_documento_referencia,
		    '' AS fecha_documento_referencia,
		    '' AS maquina_registradora_tipo_documento,
		    '' AS base_imponible_documento_referencia,
		    '' AS igv_documento_provision,
		    '' AS tipo_referencia_estado,
		    '' AS num_serie_caja_registradora,
		    '' AS fecha_operacion,
		    '' AS tipo_tasa,
		    '' AS tasa_detraccion_percepcion,
		    '' AS importe_base_detraccion_percepcion_dolares,
		    '' AS importe_base_detraccion_percepcion_soles,
		    '' AS tipo_cambio_para_f,
		    '' AS importe_igv_sin_derecho_credito_fiscal,
		    '' AS tasa_igv
		FROM mepa_caja_chica_programacion p
			INNER JOIN cont_num_cuenta nc
			ON p.num_cuenta_id = nc.id
			LEFT JOIN tbl_tipo_cambio tc
			ON p.fecha_comprobante = tc.fecha
		WHERE p.id = {$programacion_id} AND p.status = 1
		";

	$query_detalle = 
		"
		SELECT
			p.id AS programacion_id,
			nc.subdiario AS sub_diario,
			p.fecha_comprobante,
			(
				CASE 
					WHEN p.moneda_id = 1 THEN 'MN' 
					WHEN p.moneda_id = 2 THEN 'ME'
				END
			) AS codigo_moneda,
			(
				CASE 
					WHEN p.tipo_solicitud_id = 1 THEN 'ASIGNACION DE CAJA CHICA'
					WHEN p.tipo_solicitud_id = 2 THEN 'REEMBOLSO DE CAJA CHICA'
					WHEN p.tipo_solicitud_id = 9 THEN 'AUMENTO DE CAJA CHICA'
				END
			) AS glosa_principal,
			tc.id,
			tc.monto_venta AS tipo_cambio,
			'V' AS tipo_conversion,
			'S' AS flag_conversion_moneda,
			tc.fecha AS fecha_tipo_cambio,
			'102101' AS cuenta_contable, 
			tp.dni AS codigo_anexo, -- DNI
			'' AS codigo_centro_costo,
			'D' AS deber_haber,
		    aa.monto AS importe_original,
			'CJ' AS tipo_documento ,
			'SG AUMENTO' AS num_documento,
			p.fecha_comprobante AS fecha_documento,
			p.fecha_comprobante AS fecha_vencimiento,
			'140' AS codigo_area,
			'AUMENTO DE CAJA CHICA' AS glosa_detalle,
			''AS codigo_anexo_auxiliar,
			'' AS medio_pago,
			'' AS tipo_documento_referencia,
			'' AS num_documento_referencia,
			'' AS fecha_documento_referencia,
			'' AS maquina_registradora_tipo_documento,
			'' AS base_imponible_documento_referencia,
			'' AS igv_documento_provision,
			'' AS tipo_referencia_estado,
			'' AS num_serie_caja_registradora,
			'' AS fecha_operacion,
			'' AS tipo_tasa,
			'' AS tasa_detraccion_percepcion,
			'' AS importe_base_detraccion_percepcion_dolares,
			'' AS importe_base_detraccion_percepcion_soles,
			'' AS tipo_cambio_para_f,
			'' AS importe_igv_sin_derecho_credito_fiscal,
			'' AS tasa_igv
		FROM mepa_caja_chica_programacion p
			INNER JOIN mepa_caja_chica_programacion_detalle pd
			ON p.id = pd.mepa_caja_chica_programacion_id
			INNER JOIN mepa_aumento_asignacion aa
			ON pd.nombre_tabla_id = aa.id
			INNER JOIN mepa_asignacion_caja_chica a
			ON a.id = aa.asignacion_id
			LEFT JOIN tbl_tipo_cambio tc
			ON p.fecha_comprobante = tc.fecha
			INNER JOIN tbl_usuarios tu
			ON a.usuario_asignado_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			INNER JOIN cont_num_cuenta nc
			ON p.num_cuenta_id = nc.id
		WHERE p.id = {$programacion_id} AND p.status = 1 AND pd.status = 1
		";
	
	$query_todos.= $query_cabecera;
	$query_todos.= "UNION ALL";
	$query_todos.= $query_detalle;

	$list_query = $mysqli->query($query_todos);

	//PROCEDEMOS A CREAR LA CARPETA SI EN CASO NO EXISTA
	$path = "/var/www/html/files_bucket/mepa/descargas/tesoreria/concar_asignacion/";

	if (!is_dir($path)) 
	{
		mkdir($path, 0777, true);
	}

	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/mepa/descargas/tesoreria/concar_asignacion/*'); //obtenemos todos los nombres de los ficheros
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

	$tituloReporte = "Reporte Concar";

	$titulosColumnas = array('Sub Diario', 'Número de Comprobante', 'Fecha de Comprobante', 'Código de Moneda', 'Glosa Principal', 'Tipo de Cambio', 'Tipo de Conversión', 'Flag de Conversión de Moneda', 'Fecha Tipo de Cambio', 'Cuenta Contable', 'Código de Anexo', 'Código de Centro de Costo', 'Debe / Haber', 'Importe Original', 'Importe en Dólares', 'Importe en Soles', 'Tipo de Documento', 'Número de Documento', 'Fecha de Documento', 'Fecha de Vencimiento', 'Código de Area', 'Glosa Detalle', 'Código de Anexo Auxiliar', 'Medio de Pago', 'Tipo de Documento de Referencia', 'Número de Documento Referencia', 'Fecha Documento Referencia', 'Nro Máq. Registradora Tipo Doc. Ref.', 'Base Imponible Documento Referencia', 'IGV Documento Provisión', 'Tipo Referencia en estado MQ', 'Número Serie Caja Registradora', 'Fecha de Operación', 'Tipo de Tasa', 'Tasa Detracción/Percepción', 'Importe Base Detracción/Percepción Dólares', 'Importe Base Detracción/Percepción Soles', 'Tipo Cambio para F', 'Importe de IGV sin derecho credito fiscal', 'Tasa IGV');


	// Se agregan los titulos del reporte
	$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('B1', $titulosColumnas[0])	//Titulo de las columnas
			    ->setCellValue('C1', $titulosColumnas[1])
			    ->setCellValue('D1', $titulosColumnas[2])
			    ->setCellValue('E1', $titulosColumnas[3])
			    ->setCellValue('F1', $titulosColumnas[4])
			    ->setCellValue('G1', $titulosColumnas[5])
			    ->setCellValue('H1', $titulosColumnas[6])
			    ->setCellValue('I1', $titulosColumnas[7])
			    ->setCellValue('J1', $titulosColumnas[8])
			    ->setCellValue('K1', $titulosColumnas[9])
			    ->setCellValue('L1', $titulosColumnas[10])
			    ->setCellValue('M1', $titulosColumnas[11])
			    ->setCellValue('N1', $titulosColumnas[12])
			    ->setCellValue('O1', $titulosColumnas[13])
			    ->setCellValue('P1', $titulosColumnas[14])
			    ->setCellValue('Q1', $titulosColumnas[15])
			    ->setCellValue('R1', $titulosColumnas[16])
			    ->setCellValue('S1', $titulosColumnas[17])
			    ->setCellValue('T1', $titulosColumnas[18])
			    ->setCellValue('U1', $titulosColumnas[19])
			    ->setCellValue('V1', $titulosColumnas[20])
			    ->setCellValue('W1', $titulosColumnas[21])
			    ->setCellValue('X1', $titulosColumnas[22])
			    ->setCellValue('Y1', $titulosColumnas[23])
			    ->setCellValue('Z1', $titulosColumnas[24])
			    ->setCellValue('AA1', $titulosColumnas[25])
			    ->setCellValue('AB1', $titulosColumnas[26])
			    ->setCellValue('AC1', $titulosColumnas[27])
			    ->setCellValue('AD1', $titulosColumnas[28])
			    ->setCellValue('AE1', $titulosColumnas[29])
			    ->setCellValue('AF1', $titulosColumnas[30])
			    ->setCellValue('AG1', $titulosColumnas[31])
			    ->setCellValue('AH1', $titulosColumnas[32])
			    ->setCellValue('AI1', $titulosColumnas[33])
			    ->setCellValue('AJ1', $titulosColumnas[34])
			    ->setCellValue('AK1', $titulosColumnas[35])
			    ->setCellValue('AL1', $titulosColumnas[36])
			    ->setCellValue('AM1', $titulosColumnas[37])
			    ->setCellValue('AN1', $titulosColumnas[38])
			    ->setCellValue('AO1', $titulosColumnas[39]);

	//Se agregan los datos a la lista del reporte

	$i = 4; //Numero de fila donde se va a comenzar a rellenar
	while ($fila = $list_query->fetch_array()) 
	{
		if(is_null($fila['tipo_cambio']) OR empty($fila['tipo_cambio']))
		{
			echo json_encode(array(
				"ruta_archivo" => "No existe el tipo de cambio de la fecha ".$fila['fecha_comprobante'],
				"estado_archivo" => 0
			));
			exit;
		}

		$fecha_anio = date('Y', strtotime($fila['fecha_comprobante']));
		$fecha_mes = date('m', strtotime($fila['fecha_comprobante']));

		$importe_dolares = $fila['importe_original'] / $fila['tipo_cambio'];

		$objPHPExcel->setActiveSheetIndex(0)
	         ->setCellValue('B'.$i, substr($fila['sub_diario'], 0, 4))
	         ->setCellValue('C'.$i, substr($fecha_mes.str_pad($num_comprobante, 4, '0', STR_PAD_LEFT), 0, 6))
	         ->setCellValue('D'.$i, date('d/m/Y', strtotime($fila['fecha_comprobante'])))
	         ->setCellValue('E'.$i, substr($fila['codigo_moneda'], 0, 2))
	         ->setCellValue('F'.$i, substr($fila['glosa_principal'], 0, 40))
	         ->setCellValue('G'.$i, $fila['tipo_cambio'])
	         ->setCellValue('H'.$i, $fila['tipo_conversion'])
	         ->setCellValue('I'.$i, substr($fila['flag_conversion_moneda'], 0, 1))
	         ->setCellValue('J'.$i, date('d/m/Y', strtotime($fila['fecha_tipo_cambio'])))
	         ->setCellValue('K'.$i, substr($fila['cuenta_contable'], 0, 12))
	         ->setCellValue('L'.$i, substr($fila['codigo_anexo'], 0, 18))
	         ->setCellValue('M'.$i, substr($fila['codigo_centro_costo'], 0, 6))
	         ->setCellValue('N'.$i, substr($fila['deber_haber'], 0, 1))
	         ->setCellValue('O'.$i, $fila['importe_original'])
	         ->setCellValue('P'.$i, $importe_dolares)
	         ->setCellValue('Q'.$i, $fila['importe_original']) //IMPORTE EN SOLES ES LO MISMO QUE EL IMPORTE ORIGINAL
	         ->setCellValue('R'.$i, substr($fila['tipo_documento'], 0, 2))
	         ->setCellValue('S'.$i, substr($fila['num_documento'], 0, 20))
	         ->setCellValue('T'.$i, date('d/m/Y', strtotime($fila['fecha_documento'])))
	         ->setCellValue('U'.$i, date('d/m/Y', strtotime($fila['fecha_vencimiento'])))
	         ->setCellValue('V'.$i, substr($fila['codigo_area'], 0, 3))
	         ->setCellValue('W'.$i, substr($fila['glosa_detalle'], 0, 30))
	         ->setCellValue('X'.$i, substr($fila['codigo_anexo_auxiliar'], 0, 18))
	         ->setCellValue('Y'.$i, substr($fila['medio_pago'], 0, 8))
	         ->setCellValue('Z'.$i, substr($fila['tipo_documento_referencia'], 0, 2))
	         ->setCellValue('AA'.$i, substr($fila['num_documento_referencia'], 0, 20))
	         ->setCellValue('AB'.$i, $fila['fecha_documento_referencia'])
	         ->setCellValue('AC'.$i, substr($fila['maquina_registradora_tipo_documento'], 0, 20))
	         ->setCellValue('AD'.$i, $fila['base_imponible_documento_referencia'])
	         ->setCellValue('AE'.$i, $fila['igv_documento_provision'])
	         ->setCellValue('AF'.$i, $fila['tipo_referencia_estado'])
	         ->setCellValue('AG'.$i, substr($fila['num_serie_caja_registradora'], 0, 15))
	         ->setCellValue('AH'.$i, $fila['fecha_operacion'])
	         ->setCellValue('AI'.$i, substr($fila['tipo_tasa'], 0, 5))
	         ->setCellValue('AJ'.$i, $fila['tasa_detraccion_percepcion'])
	         ->setCellValue('AK'.$i, $fila['importe_base_detraccion_percepcion_dolares'])
	         ->setCellValue('AL'.$i, $fila['importe_base_detraccion_percepcion_soles'])
	         ->setCellValue('AM'.$i, substr($fila['tipo_cambio_para_f'], 0, 1))
	         ->setCellValue('AN'.$i, $fila['importe_igv_sin_derecho_credito_fiscal'])
	         ->setCellValue('AO'.$i, $fila['tasa_igv']);
	     $i++;

	}


	$estiloColoFondoAmarilloOscuro = array(
	    'fill' => array(
	      'type'  => PHPExcel_Style_Fill::FILL_SOLID,
	      'color' => array(
	            'rgb' => 'ffc000')
	  )
	);
	  
	$estiloTituloColumnas = array(
	    'font' => array(
	        'name'  => 'Arial',
	        'bold'  => false,
	        'size' => 10,
	        'color' => array(
	            'rgb' => '000000'
	        )
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


	$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(57);



	$objPHPExcel->getActiveSheet()->getStyle('B1:Z1')->applyFromArray($estiloTituloColumnas);
	$objPHPExcel->getActiveSheet()->getStyle('AA1:AO1')->applyFromArray($estiloTituloColumnas);

	$objPHPExcel->getActiveSheet()->getStyle('B1:Z1')->applyFromArray($estiloColoFondoAmarilloOscuro);
	$objPHPExcel->getActiveSheet()->getStyle('AA1:AO1')->applyFromArray($estiloColoFondoAmarilloOscuro);

	
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A4:AN".($i-1));

	$objPHPExcel->getActiveSheet()->getStyle('O4:O'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');
	$objPHPExcel->getActiveSheet()->getStyle('P4:P'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');
	$objPHPExcel->getActiveSheet()->getStyle('Q4:Q'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');

	// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
	for($i = 'B'; $i <= 'Z'; $i++)
	{
	    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}

	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('Mesa de Partes');
	  
	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);
	
	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	error_reporting(0);
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Plantilla Concar Aumento Asignacion Cajas Chicas.xls');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$excel_path = '/var/www/html/files_bucket/mepa/descargas/tesoreria/concar_asignacion/Plantilla Concar Aumento Asignacion Cajas Chicas.xls';
	$excel_path_download = '/files_bucket/mepa/descargas/tesoreria/concar_asignacion/Plantilla Concar Aumento Asignacion Cajas Chicas.xls';

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
		"ruta_archivo" => $excel_path_download,
		"estado_archivo" => 1
	));
	exit;
}

if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_tesoreria_notificar_usuarios_cuenta_bancaria") 
{
	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	$error = '';

	$arreglo_data = $_POST["array_check_usuario_a_notificar"];
	$ids_data = json_decode($arreglo_data);
	
	$contador_array_ids = 0;
	$ids_usuario = '';

	foreach ($ids_data as $value) 
	{
		if ($contador_array_ids > 0) 
		{
			$ids_usuario .= ',';
		}
		
		$ids_usuario .= $value->usuario_asignado_id;			
		$contador_array_ids++;
	}

	$body = "";
	$body .= '<html>';

		$body .= '<div>';
			$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

			$body .= '<thead>';
			
			$body .= '<tr>';
				$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
					$body .= '<b>Observaciones</b>';
				$body .= '</th>';
			$body .= '</tr>';

			$body .= '</thead>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 135px;"><b>Área solicitante:</b></td>';
				$body .= '<td>Tesoreria</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 135px;"><b>Tipo de pago:</b></td>';
				$body .= '<td>Asignación Caja Chica</td>';
			$body .= '</tr>';

			$body .= '<tr>';
				$body .= '<td style="background-color: #ffffdd; width: 135px;"><b>Observación:</b></td>';
				$body .= '<td>El número de cuenta fue rechazado. Debe ingresar un número válido.</td>';
			$body .= '</tr>';

			$body .= '</table>';
		$body .= '</div>';

	$body .= '</html>';
	$body .= "";


	$titulo_email = "Gestion - Sistema Mesa de Partes: Rechazo de número de cuenta";
	
	if(env('SEND_EMAIL') == 'test')
	{
		$cc = [
			//TESORERIA USUARIOS TEST
			"tesoreria.at.test@testtest.gmail.com"
		];
	}
	else
	{
		$cc = [
			//TESORERIA USUARIOS PRODUCCION
			"jorge.paima@testtest.apuestatotal.com",
			"katherine.zegarra@testtest.apuestatotal.com"
		];
	}
	

	
	$select_usuarios_enviar_email = 
		"
			SELECT
				p.correo
			FROM tbl_usuarios u
				LEFT JOIN tbl_personal_apt p
				ON u.personal_id = p.id
			WHERE u.id IN ($ids_usuario)
		";


	$sel_query_usuarios_enviar_email = $mysqli->query($select_usuarios_enviar_email);

	$row_count = $sel_query_usuarios_enviar_email->num_rows;

	if ($row_count > 0)
	{
		while($sel = $sel_query_usuarios_enviar_email->fetch_assoc())
		{
			if(!is_null($sel['correo']) AND !empty($sel['correo']))
			{
				array_push($cc, $sel['correo']);
			}
		}
	}


	$bcc = [
		//SISTEMAS
		"gestion@testtest.apuestatotal.com"
	];

	$request = [
		"subject" => $titulo_email,
		"body"    => $body,
		"cc"      => $cc,
		"bcc"     => $bcc,
		"attach"  => [
			// $filepath . $file,
		],
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
		//return true;

		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestion.";
		echo json_encode($result);
		exit();

	} 
	catch (Exception $e) 
	{
		//return false;

		$result["http_code"] = 400;
		$result["status"] = "Datos obtenidos de gestion.";
		$result["error"] = $mail->ErrorInfo;
		echo json_encode($result);
		exit();
	}
}

echo json_encode($result);

?>
