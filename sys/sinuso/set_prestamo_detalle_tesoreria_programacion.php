<?php 

date_default_timezone_set("America/Lima");

$result=array();
include("db_connect.php");
include("sys_login.php");
require_once '/var/www/html/sys/helpers.php';


include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';	
include '/var/www/html/sys/globalFunctions/generalInfo/parameterGeneral.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);



if (isset($_POST["accion"]) && $_POST["accion"]==="prestamo_tesoreria_guardar_comprobante_pago") 
{
	$error = '';

	$user_id = $login?$login['id']:null;
	
	if((int)$user_id > 0)
	{
		$tesoreria_fecha_comprobante_pago = $_POST['tesoreria_fecha_comprobante_pago'];
		$tesoreria_fecha_comprobante_pago = date("Y-m-d", strtotime($tesoreria_fecha_comprobante_pago));

		$prestamo_programacion_id = $_POST['prestamo_programacion_id'];

		if(isset($_FILES['prestamo_tesoreria_comprobante_pago']))
		{
			// INICIO: INSERTAMOS EL FILE EN LA TABLA tbl_prestamo_boveda_programacion_files

			$path = "/var/www/html/files_bucket/prestamos/boveda/programacion/comprobante_pago/";
			$download = "/files_bucket/prestamos/boveda/programacion/comprobante_pago/";

			if (!is_dir($path)) 
			{
				mkdir($path, 0777, true);
			}

			$cant = 1;

			for ($i=0; $i < count($_FILES['prestamo_tesoreria_comprobante_pago']['name']); $i++)
			{
				$file_name = $_FILES['prestamo_tesoreria_comprobante_pago']['name'][$i];
				$file_tmp = $_FILES['prestamo_tesoreria_comprobante_pago']['tmp_name'][$i];
				$file_size = $_FILES['prestamo_tesoreria_comprobante_pago']['size'][$i];
				$file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
				

				$nombreFileUpload = "ID_".$prestamo_programacion_id."_item_".$cant."_imagen".date('YmdHis'). ".".$file_extension;
				$nombreDownload = $download.$nombreFileUpload;
				move_uploaded_file($file_tmp, $path. $nombreFileUpload);

				$query_insert_file = "INSERT INTO tbl_prestamo_boveda_programacion_files
									(
										tbl_prestamo_programacion_id,
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
										'".$prestamo_programacion_id."',
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
					$result["error"] = $mysqli->error;

					echo json_encode($result);
					exit();
				}

				$cant++;
			}

			// FIN: INSERTAMOS EL FILE EN LA TABLA mepa_caja_chica_programacion_files

			// INICIO: ACTUALIZAMOS LOS CAMPOS DE TESORERIA EN LA TABLA tbl_prestamo_programacion

			$query_update = "
						UPDATE tbl_prestamo_programacion 
							SET fecha_carga_comprobante = '".date('Y-m-d H:i:s')."',
							    user_id_carga_comprobante = '".$login["id"]."',
							    se_cargo_comprobante = 1,
							    fecha_comprobante = '".$tesoreria_fecha_comprobante_pago."',
							    situacion_etapa_id = 3
						WHERE id = '".$prestamo_programacion_id."'
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

		// INICIO: ACTUALIZAR LA SITUACION DEL PRESTAMO COMO PAGO REALIZADO

		$select_usuarios_actualizar_saldo = 
			"
				SELECT
					p.id, p.tipo_tienda,
				    p.user_id_carga_comprobante,
				    concat(IFNULL(tpc.nombre, ''),' ', IFNULL(tpc.apellido_paterno, ''), ' ', IFNULL(tpc.apellido_materno, '')) AS usuario_atencion_comprobante,
				    p.fecha_comprobante,
				    dt.tbl_caja_prestamo_boveda_id,
				    b.id AS prestamo_boveda_id,
				    b.user_created_id,
				    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado,
				    tp.correo
				FROM tbl_prestamo_programacion p
					INNER JOIN tbl_prestamo_programacion_detalle dt
					ON p.id = dt.tbl_prestamo_programacion_id
					INNER JOIN tbl_caja_prestamo_boveda b
					ON dt.tbl_caja_prestamo_boveda_id = b.id
					LEFT JOIN tbl_usuarios tuc
					ON p.user_id_carga_comprobante = tuc.id
					LEFT JOIN tbl_personal_apt tpc
					ON tuc.personal_id = tpc.id
					INNER JOIN tbl_usuarios tu
					ON b.user_created_id = tu.id
					INNER JOIN tbl_personal_apt tp
					ON tu.personal_id = tp.id
				WHERE p.id = '".$prestamo_programacion_id."' AND p.situacion_etapa_id = 3 AND dt.status = 1 AND b.situacion_tesoreria_etapa_id = 1
			";

		$sel_query_usuarios_actualizar_pago = $mysqli->query($select_usuarios_actualizar_saldo);

		$row_count = $sel_query_usuarios_actualizar_pago->num_rows;

		if ($row_count > 0)
		{
			while($sel = $sel_query_usuarios_actualizar_pago->fetch_assoc())
			{
				$prestamo_boveda_id = $sel["prestamo_boveda_id"];
				$prestamo_tipo_tienda = $sel["tipo_tienda"];

				$query_update = "
					UPDATE tbl_caja_prestamo_boveda 
						SET situacion_tesoreria_etapa_id = 3
					WHERE id = '".$prestamo_boveda_id."'
					";

				$mysqli->query($query_update);

				if($mysqli->error)
				{
					$error .= $mysqli->error;
				}
			}
		}

		// FIN: ACTUALIZAR LA SITUACION DEL PRESTAMO COMO PAGO REALIZADO
		

		if ($error == '') 
		{
			$result["http_code"] = 200;
			$result["status"] = "Datos obtenidos de gestión.";
			$result["error"] = $error;
			$respuesta_email = prestamo_boveda_enviar_email_comprobante_programacion_pago($prestamo_programacion_id, $prestamo_tipo_tienda);
			$result["respuesta_email"] = $respuesta_email;
			$result["prestamo_tipo_tienda"] = $prestamo_tipo_tienda;
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

if(isset($_POST["accion"]) && $_POST["accion"]==="prestamo_tesoreria_editar_comprobante_pago") 
{
	$error = '';

	$tesoreria_fecha_comprobante_pago_edit = $_POST['tesoreria_fecha_comprobante_pago_edit'];
	$tesoreria_fecha_comprobante_pago_edit = date("Y-m-d", strtotime($tesoreria_fecha_comprobante_pago_edit));

	$tesoreria_motivo_comprobante_pago_edit = $_POST['tesoreria_motivo_comprobante_pago_edit'];
	$prestamo_programacion_id = $_POST['prestamo_programacion_id'];

	if(isset($_FILES['prestamo_tesoreria_comprobante_pago_edit']))
	{
		// INICIO: ELIMINAMOS LOS REGISTROS EXISTENTES DE LA BD

		$query_delete = "
						DELETE FROM tbl_prestamo_boveda_programacion_files 
						WHERE tbl_prestamo_programacion_id = '".$prestamo_programacion_id."'
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

		$path = "/var/www/html/files_bucket/prestamos/boveda/programacion/comprobante_pago/";
		$download = "/files_bucket/prestamos/boveda/programacion/comprobante_pago/";

		if (!is_dir($path)) 
		{
			mkdir($path, 0777, true);
		}

		$cant = 1;

		for ($i=0; $i < count($_FILES['prestamo_tesoreria_comprobante_pago_edit']['name']); $i++)
		{
			$file_name = $_FILES['prestamo_tesoreria_comprobante_pago_edit']['name'][$i];
			$file_tmp = $_FILES['prestamo_tesoreria_comprobante_pago_edit']['tmp_name'][$i];
			$file_size = $_FILES['prestamo_tesoreria_comprobante_pago_edit']['size'][$i];
			$file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
			

			$nombreFileUpload = "ID_".$prestamo_programacion_id."_item_".$cant."_imagen".date('YmdHis'). ".".$file_extension;
			$nombreDownload = $download.$nombreFileUpload;
			move_uploaded_file($file_tmp, $path. $nombreFileUpload);

			$query_insert_file = "INSERT INTO tbl_prestamo_boveda_programacion_files
									(
										tbl_prestamo_programacion_id,
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
										'".$prestamo_programacion_id."',
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
				$result["error"] = $error;

				echo json_encode($result);
				exit();
			}

			$cant++;
		}

		// FIN: INSERTAMOS EL FILE EN LA TABLA mepa_caja_chica_programacion_files

		// INICIO: ACTUALIZAMOS LOS CAMPOS DE TESORERIA EN LA TABLA tbl_prestamo_programacion

		$query_update = "
					UPDATE tbl_prestamo_programacion 
						SET fecha_carga_comprobante = '".date('Y-m-d H:i:s')."',
						    user_id_carga_comprobante = '".$login["id"]."',
						    se_cargo_comprobante = 1,
						    fecha_comprobante = '".$tesoreria_fecha_comprobante_pago_edit."',
						    comentario_edicion_comprobante = '".$tesoreria_motivo_comprobante_pago_edit."',
						    situacion_etapa_id = 3
					WHERE id = '".$prestamo_programacion_id."'
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

		// FIN: ACTUALIZAMOS LOS CAMPOS DE TESORERIA EN LA TABLA tbl_prestamo_programacion
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
	}
	else 
	{
		$result["http_code"] = 400;
		$result["status"] = "Ocurrio un error.";
		$result["error"] = $error;
	}
}

function prestamo_boveda_enviar_email_comprobante_programacion_pago($programacion_id, $prestamo_tipo_tienda)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];
	$titulo_email = "";
	$respuesta_email = 0;

	$sel_query_verificacion = $mysqli->query("
		SELECT
			pd.id, pd.tbl_caja_prestamo_boveda_id, pd.status,
			concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado, tp.dni, tp.correo,
			pb.id AS prestamo_boveda_id,
			pb.monto, tb.nombre AS asignacion_banco, ac.num_cuenta,
			l.nombre AS local,
			pb.asignacion_id_num_cuenta,
		    concat(IFNULL(tpc.nombre, ''),' ', IFNULL(tpc.apellido_paterno, ''), ' ', IFNULL(tpc.apellido_materno, '')) AS usuario_asignado_cajero,
			tpc.dni AS dni_cajero, tpc.correo AS correo_cajero, tbc.nombre AS banco_cajero,
			pb.numero_cuenta_cajero,
			pb.cajero_usuario_id, pb.tipo_tienda,
			concat(IFNULL(tps.nombre, ''),' ', IFNULL(tps.apellido_paterno, ''), ' ', IFNULL(tps.apellido_materno, '')) AS usuario_sportbars,
			tps.dni AS usuario_dni_sportbars
		FROM tbl_prestamo_programacion_detalle pd
			INNER JOIN tbl_caja_prestamo_boveda pb
			ON pd.tbl_caja_prestamo_boveda_id = pb.id
			INNER JOIN tbl_locales l
			ON pb.local_id = l.id
			LEFT JOIN tbl_usuarios tu
			ON pb.user_created_id = tu.id
			LEFT JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			LEFT JOIN mepa_asignacion_caja_chica a
			ON pb.user_created_id = a.usuario_asignado_id AND pb.asignacion_id_num_cuenta = a.id
			LEFT JOIN mepa_asignacion_cuenta_bancaria ac
			ON ac.asignacion_id = a.id AND ac.status = 1
			LEFT JOIN tbl_bancos tb
			ON ac.banco_id = tb.id
			LEFT JOIN tbl_usuarios tuc
			ON pb.cajero_usuario_id = tuc.id
			LEFT JOIN tbl_personal_apt tpc
			ON tuc.personal_id = tpc.id
			LEFT JOIN tbl_bancos tbc
			ON pb.banco_cajero_id = tbc.id
			LEFT JOIN tbl_usuarios tus
			ON pb.user_created_id = tus.id
			LEFT JOIN tbl_personal_apt tps
			ON tus.personal_id = tps.id
		WHERE pd.status = 1 AND pd.tbl_prestamo_programacion_id = '".$programacion_id."'
	");

	$cont = 1;

	$body = "";
	$body .= '<html>';

	$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 730px;">';
			$body .= '<thead>';
				$body .= '<tr>';
					$body .= '<th colspan="8" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
						$body .= '<b>Pagó Realizado</b>';
					$body .= '</th>';
				$body .= '</tr>';
			$body .= '</thead>';

			$body .= '<thead>';
				$body .= '<tr style="background-color: #ffffdd;">';
					$body .= '<th class="text-center">Nº</th>';
					$body .= '<th class="text-center">ID Préstamo</th>';
					$body .= '<th class="text-center">Usuario</th>';
					$body .= '<th class="text-center">DNI</th>';
					
					if($prestamo_tipo_tienda == 1 || $prestamo_tipo_tienda == 16)
					{
						// RED AT
						// RED IGH
						$body .= '<th class="text-center">Banco</th>';
						$body .= '<th class="text-center">Nº. Cuenta</th>';
					}
					
					$body .= '<th class="text-center">Tienda</th>';
					$body .= '<th class="text-center">Monto</th>';
				$body .= '</tr>';
			$body .= '</thead>';

			$body .= '<tbody>';

			while($sel = $sel_query_verificacion->fetch_assoc())
			{
				$asignacion_id_num_cuenta = $sel["asignacion_id_num_cuenta"];
				$boveda_cajero_usuario_id = $sel["cajero_usuario_id"];
				$boveda_tipo_tienda = $sel["tipo_tienda"];

				if($boveda_tipo_tienda == 9)
				{
					// TIENDAS SPORTBARS
					if($boveda_cajero_usuario_id != NULL)
					{
						$usuario_asignado = $sel["usuario_asignado_cajero"];
						$dni = $sel["dni_cajero"];
						$banco = $sel["banco_cajero"];
						$num_cuenta = $sel["numero_cuenta_cajero"];
					}
					else
					{
						$usuario_asignado = $sel["usuario_sportbars"];
						$dni = $sel["usuario_dni_sportbars"];
						$banco = "";
						$num_cuenta = "";
					}
				}
				else if($boveda_tipo_tienda == 1 || $boveda_tipo_tienda == 16)
				{
					// RED AT
					// RED IGH
					if($asignacion_id_num_cuenta == 0)
					{
						$usuario_asignado = $sel["usuario_asignado_cajero"];
						$dni = $sel["dni_cajero"];
						$banco = $sel["banco_cajero"];
						$num_cuenta = $sel["numero_cuenta_cajero"];
					}
					else
					{
						$usuario_asignado = $sel["usuario_asignado"];
						$dni = $sel["dni"];
						$banco = $sel["asignacion_banco"];
						$num_cuenta = $sel["num_cuenta"];
					}
				}

				$body .= '<tr>';
					$body .= '<td class="text-center">';
						$body .= $cont;
					$body .= '</td>';

					$body .= '<td class="text-center">';
						$body .= $sel['prestamo_boveda_id'];
					$body .= '</td>';

					$body .= '<td class="text-center">';
						$body .= $usuario_asignado;	
					$body .= '</td>';

					$body .= '<td class="text-center">';
						$body .= $dni;
					$body .= '</td>';

					if($prestamo_tipo_tienda == 1 || $prestamo_tipo_tienda == 16)
					{
						// RED AT
						// RED IGH
						$body .= '<td class="text-center">';
							$body .= $banco;
						$body .= '</td>';

						$body .= '<td class="text-center">';
							$body .= $num_cuenta;
						$body .= '</td>';
					}

					$body .= '<td class="text-center">';
						$body .= $sel['local'];
					$body .= '</td>';

					$body .= '<td class="text-center">';
						$body .= 'S/ '.$sel['monto'];
					$body .= '</td>';
				$body .= '</tr>';

				$cont ++;
			}

			$body .= '</tbody>';
		$body .= '</table>';
	$body .= '</div>';

	$body .= '</html>';
	$body .= "";

	$titulo_email = "";
	$sub_titulo = "";

	if(env('SEND_EMAIL') == 'test')
	{
		$sub_titulo = "TEST SISTEMAS: ";
	}

	$titulo_email = $sub_titulo."Préstamo Bóveda: Tesoreria - Pago Realizado";

	$cc = [	
	];

	$bcc = [
	];

	$select_usuarios_enviar_email = 
	"
		SELECT
			pd.id, pd.tbl_caja_prestamo_boveda_id, pd.status,
			concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado, tp.dni, tp.correo,
			pb.id AS prestamo_boveda_id,
			pb.monto, tb.nombre AS asignacion_banco, ac.num_cuenta,
			l.nombre AS local,
			pb.asignacion_id_num_cuenta,
		    concat(IFNULL(tpc.nombre, ''),' ', IFNULL(tpc.apellido_paterno, ''), ' ', IFNULL(tpc.apellido_materno, '')) AS usuario_asignado_cajero,
			tpc.dni AS dni_cajero, tpc.correo AS correo_cajero, tbc.nombre AS banco_cajero,
			pb.numero_cuenta_cajero,
			tpj.correo AS correo_jefe
		FROM tbl_prestamo_programacion_detalle pd
			INNER JOIN tbl_caja_prestamo_boveda pb
			ON pd.tbl_caja_prestamo_boveda_id = pb.id
			INNER JOIN tbl_locales l
			ON pb.local_id = l.id
			LEFT JOIN tbl_usuarios tu
			ON pb.user_created_id = tu.id
			LEFT JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			LEFT JOIN mepa_asignacion_caja_chica a
			ON pb.user_created_id = a.usuario_asignado_id AND pb.asignacion_id_num_cuenta = a.id
			LEFT JOIN mepa_asignacion_cuenta_bancaria ac
			ON ac.asignacion_id = a.id AND ac.status = 1
			LEFT JOIN tbl_bancos tb
			ON ac.banco_id = tb.id
			LEFT JOIN tbl_usuarios tuc
			ON pb.cajero_usuario_id = tuc.id
			LEFT JOIN tbl_personal_apt tpc
			ON tuc.personal_id = tpc.id
			LEFT JOIN tbl_bancos tbc
			ON pb.banco_cajero_id = tbc.id
			LEFT JOIN tbl_usuarios tuj
			ON pb.usuario_jefe_atencion_id = tuj.id
			LEFT JOIN tbl_personal_apt tpj
			ON tuj.personal_id = tpj.id
		WHERE pd.status = 1 AND pd.tbl_prestamo_programacion_id = '".$programacion_id."'
	";

	$sel_query_usuarios_enviar_email = $mysqli->query($select_usuarios_enviar_email);

	$row_count = $sel_query_usuarios_enviar_email->num_rows;

	if ($row_count > 0)
	{
		while($sel = $sel_query_usuarios_enviar_email->fetch_assoc())
		{
			if(!is_null($sel['correo_jefe']) AND !empty($sel['correo_jefe']))
			{
				array_push($cc, $sel['correo_jefe']);
			}
		}
	}

	//INICIO: LISTAR USUARIOS DEL GRUPO
	$select_usuario_pagar_prestamo_area_tesoreria = 
	"
		SELECT
			pg.id, pg.metodo, pg.status AS prestamo_grupo_estado,
		    pu.usuario_id, p.nombre, p.correo
		FROM tbl_prestamo_mantenimiento_correo_grupo pg
			INNER JOIN tbl_prestamo_mantenimiento_correo_usuario pu
			ON pg.id = pu.tbl_prestamo_mantenimiento_correo_grupo_id
			LEFT JOIN tbl_usuarios u
			ON pu.usuario_id = u.id
			LEFT JOIN tbl_personal_apt p
			ON u.personal_id = p.id
		WHERE pg.metodo = 'pagar_prestamo_boveda_area_tesoreria' 
			AND pg.status = 1 
			AND pu.status = 1
	";

	$sel_usuario_pagar_prestamo_area_tesoreria = $mysqli->query($select_usuario_pagar_prestamo_area_tesoreria);

	$row_count = $sel_usuario_pagar_prestamo_area_tesoreria->num_rows;

	if ($row_count > 0)
	{
		while($sel = $sel_usuario_pagar_prestamo_area_tesoreria->fetch_assoc())
		{
			if(!is_null($sel['correo']) AND !empty($sel['correo']))
			{
				array_push($cc, $sel['correo']);
			}
		}
	}
	//FIN: LISTAR USUARIOS DEL GRUPO

	//INICIO: LISTAR USUARIOS DEL GRUPO AREA SISTEMAS - COPIA OCULTA
	$query_select_usuario_sistemas_cco = 
	"
		SELECT
			pg.id, pg.metodo, pg.status AS prestamo_grupo_estado,
		    pu.usuario_id, p.nombre, p.correo
		FROM tbl_prestamo_mantenimiento_correo_grupo pg
			INNER JOIN tbl_prestamo_mantenimiento_correo_usuario pu
			ON pg.id = pu.tbl_prestamo_mantenimiento_correo_grupo_id
			LEFT JOIN tbl_usuarios u
			ON pu.usuario_id = u.id
			LEFT JOIN tbl_personal_apt p
			ON u.personal_id = p.id
		WHERE pg.metodo = 'prestamo_boveda_area_sistemas_cco' 
			AND pg.status = 1 
			AND pu.status = 1
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
	//FIN: LISTAR USUARIOS DEL GRUPO AREA SISTEMAS - COPIA OCULTA

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

		return $respuesta_email = true;

	}
	catch (Exception $e) 
	{
		return $respuesta_email = $e;
	}
}

if(isset($_POST["accion"]) && $_POST["accion"]==="sec_prestamo_detalle_tesoreria_programacion_rechazar_usuario") 
{
	$error = '';

	$id_prestamo_detalle = $_POST['id_prestamo_detalle'];
	$id_prestamo = $_POST['id_prestamo'];
	$motivo_rechazo = $_POST['motivo_rechazo'];

	$query_delete = "
					DELETE FROM tbl_prestamo_programacion_detalle 
					WHERE id = '".$id_prestamo_detalle."'
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

	$query_update = "
				UPDATE tbl_caja_prestamo_boveda 
					SET situacion_tesoreria_etapa_id = 4,
						motivo_atencion_tesoreria = '".$motivo_rechazo."'
				WHERE id = '".$id_prestamo."'
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

	if ($error == '')
	{
		$result["http_code"] = 200;
		$result["status"] = "Datos obtenidos de gestión.";
		$result["error"] = $error;
	}
	else
	{
		$result["http_code"] = 400;
		$result["status"] = "Ocurrio un error.";
		$result["error"] = $error;
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_prestamo_detalle_tesoreria_btn_reporte_boveda")
{
	$programacion_id = $_POST["programacion_id"];
	
	$query = "
		SELECT
			pd.id, pd.tbl_caja_prestamo_boveda_id, pd.status,
			concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_asignado, tp.dni,
			pb.monto, tb.nombre AS asignacion_banco, ac.num_cuenta,
			l.nombre AS local,
		    l.cc_id AS ceco,
			l.zona_id,
			z.nombre AS local_zona,
			pb.asignacion_id_num_cuenta,
			concat(IFNULL(tpc.nombre, ''),' ', IFNULL(tpc.apellido_paterno, ''), ' ', IFNULL(tpc.apellido_materno, '')) AS usuario_asignado_cajero,
			tpc.dni AS dni_cajero, tpc.correo AS correo_cajero, tbc.nombre AS banco_cajero,
			pb.numero_cuenta_cajero,
			pb.cajero_usuario_id, pb.tipo_tienda,
			concat(IFNULL(tps.nombre, ''),' ', IFNULL(tps.apellido_paterno, ''), ' ', IFNULL(tps.apellido_materno, '')) AS usuario_sportbars,
			tps.dni AS usuario_dni_sportbars
		FROM tbl_prestamo_programacion_detalle pd
			INNER JOIN tbl_caja_prestamo_boveda pb
			ON pd.tbl_caja_prestamo_boveda_id = pb.id
			INNER JOIN tbl_locales l
			ON pb.local_id = l.id
		    INNER JOIN tbl_zonas z
			ON l.zona_id = z.id
			INNER JOIN tbl_usuarios tu
			ON pb.user_created_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
			LEFT JOIN mepa_asignacion_caja_chica a
			ON pb.user_created_id = a.usuario_asignado_id AND pb.asignacion_id_num_cuenta = a.id
			LEFT JOIN mepa_asignacion_cuenta_bancaria ac
			ON ac.asignacion_id = a.id AND ac.status = 1
			LEFT JOIN tbl_bancos tb
			ON ac.banco_id = tb.id
			LEFT JOIN tbl_usuarios tuc
			ON pb.cajero_usuario_id = tuc.id
			LEFT JOIN tbl_personal_apt tpc
			ON tuc.personal_id = tpc.id
			LEFT JOIN tbl_bancos tbc
			ON pb.banco_cajero_id = tbc.id
			LEFT JOIN tbl_usuarios tus
			ON pb.user_created_id = tus.id
			LEFT JOIN tbl_personal_apt tps
			ON tus.personal_id = tps.id
		WHERE pd.status = 1 AND pd.tbl_prestamo_programacion_id = '".$programacion_id."'
	";

	$list_query = $mysqli->query($query);

	//PROCEDEMOS A CREAR LA CARPETA SI EN CASO NO EXISTA
	$path = "/var/www/html/files_bucket/prestamos/boveda/reporte/";

	if (!is_dir($path)) 
	{
		mkdir($path, 0777, true);
	}

	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/prestamos/boveda/reporte*'); //obtenemos todos los nombres de los ficheros
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

    $tituloReporte = "Relación de préstamo bóveda";

	$titulosColumnas = array('Nº', 'Beneficiario', 'DNI', 'Banco', 'Num cuenta', 'Importe', 'Tienda', 'Zona', 'CECO');

	// Se combinan las celdas A1 hasta K1, para colocar ahí el titulo del reporte
	$objPHPExcel->setActiveSheetIndex(0)->mergeCells('A1:I1');
	
	$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A1', $tituloReporte);

	// Se agregan los titulos del reporte
	$objPHPExcel->setActiveSheetIndex(0)
    ->setCellValue('A2', $titulosColumnas[0])  //Titulo de las columnas
    ->setCellValue('B2', $titulosColumnas[1])
    ->setCellValue('C2', $titulosColumnas[2])
    ->setCellValue('D2', $titulosColumnas[3])
    ->setCellValue('E2', $titulosColumnas[4])
    ->setCellValue('F2', $titulosColumnas[5])
    ->setCellValue('G2', $titulosColumnas[6])
    ->setCellValue('H2', $titulosColumnas[7])
    ->setCellValue('I2', $titulosColumnas[8]);

    //Se agregan los datos a la lista del reporte
	$cont = 0;

	$i = 3; //Numero de fila donde se va a comenzar a rellenar los datos
	while ($sel = $list_query->fetch_array()) 
	{
		$cont ++;

		$asignacion_id_num_cuenta = $sel["asignacion_id_num_cuenta"];
		$boveda_cajero_usuario_id = $sel["cajero_usuario_id"];
		$boveda_tipo_tienda = $sel["tipo_tienda"];

		if($boveda_tipo_tienda == 5)
		{
			// TIENDAS SPORTBARS
			if($boveda_cajero_usuario_id != NULL)
			{
				$usuario_asignado = $sel["usuario_asignado_cajero"];
				$dni = $sel["dni_cajero"];
				$banco = $sel["banco_cajero"];
				$num_cuenta = $sel["numero_cuenta_cajero"];
			}
			else
			{
				$usuario_asignado = $sel["usuario_sportbars"];
				$dni = $sel["usuario_dni_sportbars"];
				$banco = "";
				$num_cuenta = "";
			}
		}
		else if($boveda_tipo_tienda == 1 || $boveda_tipo_tienda == 16)
		{
			// RED AT
			// RED IGH
			if($asignacion_id_num_cuenta == 0)
			{
				$usuario_asignado = $sel["usuario_asignado_cajero"];
				$dni = $sel["dni_cajero"];
				$banco = $sel["banco_cajero"];
				$num_cuenta = $sel["numero_cuenta_cajero"];
			}
			else
			{
				$usuario_asignado = $sel["usuario_asignado"];
				$dni = $sel["dni"];
				$banco = $sel["asignacion_banco"];
				$num_cuenta = $sel["num_cuenta"];
			}
		}

		$objPHPExcel->setActiveSheetIndex(0)
		->setCellValue('A'.$i, $cont)
		->setCellValue('B'.$i, $usuario_asignado)
		->setCellValue('C'.$i, $dni)
		->setCellValue('D'.$i, $banco)
		->setCellValue('E'.$i, $num_cuenta)
		->setCellValue('F'.$i, 'S/ '.$sel['monto'])
		->setCellValue('G'.$i, $sel['local'])
		->setCellValue('H'.$i, $sel['local_zona'])
		->setCellValue('I'.$i, $sel['ceco']);
		
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
	            'rgb' => '000000'
	        )
	    ),
	    'fill' => array(
			  'type'  => PHPExcel_Style_Fill::FILL_SOLID,
			  'color' => array(
			        'rgb' => 'FFFFFF')
			),
	    'alignment' =>  array(
	        'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        'wrap'      => false
	    ),
	    'borders' => array(
		        'allborders' => array(
		            'style' => PHPExcel_Style_Border::BORDER_THIN
		        )
		    )
	);

	$estiloSubuTituloColumnas = array(
	    'font' => array(
	        'name'  => 'Arial',
	        'bold'  => false,
	        'size' => 10,
	        'color' => array(
	            'rgb' => 'ffffff'
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

	$estiloColoFondoMorado = array(
	    'fill' => array(
	      'type'  => PHPExcel_Style_Fill::FILL_SOLID,
	      'color' => array(
	            'rgb' => '993366')
	  )
	);

	$estiloColoFondoAzul = array(
	    'fill' => array(
	      'type'  => PHPExcel_Style_Fill::FILL_SOLID,
	      'color' => array(
	            'rgb' => '4472c4')
	  )
	);

	$estilo_centrar = array(
	    'alignment' =>  array(
	        'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        'wrap'      => false
	    )
	);

	// SIRVE PARA PONER ALTURA A LA FILA, EN ESTE CASO A LA FILA 1.
	$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(63);
	$objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(30);

	$objPHPExcel->getActiveSheet()->getStyle('A1:I1')->applyFromArray($estiloNombresColumnas);
	
	$objPHPExcel->getActiveSheet()->getStyle('A1:I'.($i-1))->applyFromArray($estilo_centrar);

	$objPHPExcel->getActiveSheet()->getStyle('F3:F'.($i-1))->getNumberFormat()->setFormatCode('#,##0.00');

	$objPHPExcel->getActiveSheet()->getStyle('A2:F2')->applyFromArray($estiloColoFondoMorado);
	$objPHPExcel->getActiveSheet()->getStyle('G2:I2')->applyFromArray($estiloColoFondoAzul);

	$objPHPExcel->getActiveSheet()->getStyle('A2:I2')->applyFromArray($estiloSubuTituloColumnas);

	// ASIGNAR ANCHO A LA COLUMNA DE MANERA AUTOMATICA EN BASE AL CONTENIDO
	for($i = 'A'; $i <= 'Z'; $i++)
	{
	    $objPHPExcel->setActiveSheetIndex(0)->getColumnDimension($i)->setAutoSize(TRUE);
	}

	// Se asigna el nombre a la hoja
	$objPHPExcel->getActiveSheet()->setTitle('Préstamo Bóveda');
	  
	// Se activa la hoja para que sea la que se muestre cuando el archivo se abre
	$objPHPExcel->setActiveSheetIndex(0);
	
	// Se manda el archivo al navegador web, con el nombre que se indica, en formato 2007
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="Préstamo Bóveda.xls" ');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$excel_path = '/var/www/html/files_bucket/prestamos/boveda/reporte/Préstamo Bóveda.xls';
	$excel_path_download = '/files_bucket/prestamos/boveda/reporte/Préstamo Bóveda.xls';

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
if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_prestamo_boveda_concar_excel")
{
	
	$programacion_boveda_id = $_POST['programacion_boveda_id'];
	$num_comprobante = $_POST['programacion_boveda_num_comprobante'];
	$param_num_documento = $_POST['programacion_boveda_num_documento'];

	$query_update_num_comprobante = "
						UPDATE tbl_prestamo_programacion 
							SET numero_comprobante_concar = '".$num_comprobante."',
								numero_documento_concar = '".$param_num_documento."'
								,
								cod_moneda_parametro_general = 'codigo_moneda_nacional'
						WHERE id = '".$programacion_boveda_id."'
						";

	$mysqli->query($query_update_num_comprobante);
	// echo $query_update_num_comprobante;
	// exit();
	$query_todos = "";

	$importe_dolares = 0;

	$fecha_dia = "";
	$fecha_mes = "";
	$fecha_anio = "";

	$query_cabecera = 
		"
		SELECT
			pp.id AS programacion_id,
		    rs.subdiario AS sub_diario,
			pp.fecha_comprobante,
			pp.numero_comprobante_concar,
		    pp.cod_moneda_parametro_general AS codigo_moneda,
			pp.tipo_prestamo, -- glosa_principal
		    '".getParameterGeneral('prestamo_bodeva_glosa_principal')."' AS glosa_principal,
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
					sum(pb.monto)
				FROM tbl_prestamo_programacion_detalle pd
				INNER JOIN tbl_caja_prestamo_boveda pb
				ON pd.tbl_caja_prestamo_boveda_id = pb.id
				WHERE pd.tbl_prestamo_programacion_id = pp.id AND pd.status = 1
			) AS importe_original,
		    'TR' AS tipo_documento ,
		    numero_documento_concar as num_documento,
		    pp.fecha_comprobante AS fecha_documento,
		    pp.fecha_comprobante AS fecha_vencimiento,
		    '".getParameterGeneral('prestamo_bodeva_cod_area_haber')."' AS codigo_area,
		    '".getParameterGeneral('prestamo_bodeva_glosa_detalle')."' AS glosa_detalle,
		    '".getParameterGeneral('prestamo_bodeva_medio_pago_haber')."' AS medio_pago,
		    '' AS codigo_anexo_auxiliar,
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
		FROM tbl_prestamo_programacion pp
			INNER JOIN cont_num_cuenta nc
			ON pp.num_cuenta_id = nc.id
			INNER JOIN tbl_razon_social rs
			ON nc.razon_social_id = rs.id
			LEFT JOIN tbl_tipo_cambio tc
			ON pp.fecha_comprobante = tc.fecha
			
		WHERE pp.id = {$programacion_boveda_id} AND pp.status = 1
		";

	$query_detalle = 
		"
		SELECT
			pp.id AS programacion_boveda_id,
		    rs.subdiario AS sub_diario,
			pp.fecha_comprobante,
			pp.numero_comprobante_concar,
		    pp.cod_moneda_parametro_general AS codigo_moneda,
			pp.tipo_prestamo, -- glosa_principal
		    '".getParameterGeneral('prestamo_bodeva_glosa_principal')."' AS glosa_principal,
		    tc.id,
		    tc.monto_venta AS tipo_cambio,
		    'V' AS tipo_conversion,
		    'S' AS flag_conversion_moneda,
		    tc.fecha AS fecha_tipo_cambio,
		    '".getParameterGeneral('prestamo_bodeva_cuenta_contable_debe')."' AS cuenta_contable,
		    CONCAT(l.cc_id,'-','".getParameterGeneral('prestamo_bodeva_text_cod_anexo')."') AS codigo_anexo,
			'' AS codigo_centro_costo,
		    'D' AS deber_haber,
		    pb.monto AS importe_original,
			'PR' AS tipo_documento ,
			concat('0',DATE_FORMAT(pp.fecha_comprobante, '%m'), '-', DATE_FORMAT(pp.fecha_comprobante, '%Y')) AS num_documento,
			pp.fecha_comprobante AS fecha_documento,
			pp.fecha_comprobante AS fecha_vencimiento,
			'".getParameterGeneral('prestamo_bodeva_cod_area_debe')."' AS codigo_area,
		    '".getParameterGeneral('prestamo_bodeva_glosa_detalle')."' AS glosa_detalle,
			'' AS medio_pago,
		    '".getParameterGeneral('prestamo_bodeva_codigo_anexo_auxiliar')."' AS codigo_anexo_auxiliar,
			'PR' AS tipo_documento_referencia,
			concat('0',DATE_FORMAT(pp.fecha_comprobante, '%m'), '-', DATE_FORMAT(pp.fecha_comprobante, '%Y')) AS num_documento_referencia,
			pp.fecha_comprobante AS fecha_documento_referencia,
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
		FROM tbl_prestamo_programacion pp
			INNER JOIN tbl_prestamo_programacion_detalle pd
			ON pp.id = pd.tbl_prestamo_programacion_id
			INNER JOIN tbl_caja_prestamo_boveda pb
			ON pd.tbl_caja_prestamo_boveda_id = pb.id
			INNER JOIN tbl_locales l
			ON pb.local_id = l.id
			LEFT JOIN tbl_tipo_cambio tc
			ON pp.fecha_comprobante = tc.fecha
			INNER JOIN tbl_razon_social rs
			ON pp.empresa_id = rs.id
			WHERE pp.id = {$programacion_boveda_id} AND pp.status = 1 AND pd.status = 1
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

		$importe_dolares = $fila['importe_original'] / $fila['tipo_cambio'];

		$objPHPExcel->setActiveSheetIndex(0)
	         ->setCellValue('B'.$i, substr($fila['sub_diario'], 0, 4))
	         ->setCellValue('C'.$i, substr($fecha_mes.str_pad($num_comprobante, 4, '0', STR_PAD_LEFT), 0, 6))
	         ->setCellValue('D'.$i, date('d/m/Y', strtotime($fila['fecha_comprobante'])))
	         ->setCellValue('E'.$i, substr(getParameterGeneral($fila['codigo_moneda']), 0, 2))
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
	         ->setCellValue('AB'.$i, empty($fila['fecha_documento_referencia'])?'':date('d/m/Y', strtotime($fila['fecha_documento_referencia'])))
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
	header('Content-Disposition: attachment;filename="Plantilla Concar Prestamos Boveda.xls');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$excel_path = '/var/www/html/files_bucket/mepa/descargas/tesoreria/concar_liquidacion/Plantilla Concar Prestamos Boveda.xls';
	$excel_path_download = '/files_bucket/mepa/descargas/tesoreria/concar_liquidacion/Plantilla Concar Prestamos Boveda.xls';

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
*/
if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_prestamo_boveda_concar_excel")
{
	require_once '../phpexcel/classes/PHPExcel.php';

	$id_programacion_boveda = $_POST['programacion_boveda_id'];
	$programacion_boveda_tipo_prestamo = $_POST['programacion_boveda_tipo_prestamo'];
	$num_comprobante = $_POST['programacion_boveda_num_comprobante'];
	$param_num_documento = $_POST['programacion_boveda_num_documento'];
	
	try {

		$path = "/var/www/html/files_bucket/mepa/descargas/tesoreria/concar_liquidacion/";

		if($programacion_boveda_tipo_prestamo == 7){
			$excel_nombre = "Plantilla Concar - Prestamos Boveda.xls";
		}else{
			$excel_nombre = "Plantilla Concar - Pago de Premios.xls";
		}
		if (!is_dir($path)) 
		{
			throw new Exception('No existe la carpeta "concar_liquidacion" en la ruta "/files_bucket/mepa/descargas/tesoreria/" del servidor. Contactarse con soporte');
		}


		// Tu código actual aquí
		$query_update_concar = "UPDATE tbl_prestamo_programacion 
								SET 
								numero_comprobante_concar = ?,
								numero_documento_concar = ?,
								cod_moneda_parametro_general = ?
								WHERE 
								id = ?";
		$stmt = $mysqli->prepare($query_update_concar);
		$stmt->bind_param("sssi", $num_comprobante, $param_num_documento, $codigo_moneda_nacional, $id_programacion_boveda);
	
		if ($stmt->execute()) {
			$selectQueryCabecera = "SELECT
						pp.empresa_id AS razon_social_id,
						pp.fecha_comprobante,
						pp.numero_comprobante_concar,
						pp.cod_moneda_parametro_general AS codigo_moneda,
						pp.tipo_prestamo,
						pp.numero_documento_concar as num_documento,
						pp.fecha_comprobante AS fecha_documento,
						pp.fecha_comprobante AS fecha_vencimiento,
						pp.num_cuenta_id
					FROM tbl_prestamo_programacion pp
					WHERE pp.id = ? AND pp.status = 1";

			$selectStmtCabecera = $mysqli->prepare($selectQueryCabecera);
			$selectStmtCabecera->bind_param("i", $id_programacion_boveda);
			$selectStmtCabecera->execute();
			$selectStmtCabecera->store_result();

			if ($selectStmtCabecera->num_rows > 0) {
				$selectStmtCabecera->bind_result(
													$razon_social_id, 
													$fecha_comprobante,
													$num_comprobante,
													$codigo_moneda,
													$tipo_prestamo,
													$num_documento,
													$fecha_documento,
													$fecha_vencimiento,
													$num_cuenta_id
												);
				$selectStmtCabecera->fetch();

				//	Verificar si existe la cuenta contable

				$selectQuery = "SELECT 
									nc.num_cuenta_contable,
									nc.num_cuenta_contable_haber,
									nc.subdiario,
									nc.cod_anexo
								FROM cont_num_cuenta nc
								LEFT JOIN cont_num_cuenta_proceso ncp ON ncp.id=nc.cont_num_cuenta_proceso_id
								LEFT JOIN cont_tipo_programacion tp ON nc.tipo_pago_id = tp.id
								WHERE nc.id = ? -- AND ncp.id = ? AND tp.id = ?
								LIMIT 1";

				$selectStmt = $mysqli->prepare($selectQuery);
				$selectStmt->bind_param("i", $num_cuenta_id);
				$selectStmt->execute();
				$selectStmt->store_result();

				if ($selectStmt->num_rows > 0) {
					$selectStmt->bind_result($cc_haber_cuenta_contable,$cc_debe_cuenta_contable,$cc_haber_subdiario,$cc_haber_cod_anexo);
					$selectStmt->fetch();

					if($cc_debe_cuenta_contable != ""){
					

						//	Verificar si existe el tipo de cambio
						$selectQuery = "SELECT monto_venta
						FROM tbl_tipo_cambio 
						WHERE fecha = ?
						LIMIT 1";

						$selectStmt = $mysqli->prepare($selectQuery);
						$selectStmt->bind_param("s", $fecha_comprobante);
						$selectStmt->execute();
						$selectStmt->store_result();

						if ($selectStmt->num_rows > 0) {
						$selectStmt->bind_result($tipo_cambio);
						$selectStmt->fetch();

						
						//	Parametros generales para header

						$codigo_moneda = getParameterGeneral('codigo_moneda_nacional');
						$glosa_principal = getParameterGeneral('prestamo_bodeva_glosa_principal');
						$glosa_principal= substr($glosa_principal, 0, 40);
						$tipo_conversion = getParameterGeneral('kasnet_tipo_conversion');
						$flag_conversion_moneda = getParameterGeneral('kasnet_flag_conversion_moneda');
						$header_cod_haber= getParameterGeneral('kasnet_codigo_haber');
						$header_cod_haber= substr($header_cod_haber, 0, 1);
						$tipo_documento= getParameterGeneral('kasnet_tipo_documento');
						$codigo_area=getParameterGeneral('prestamo_bodeva_cod_area_haber');
						$glosa_detalle = getParameterGeneral('prestamo_bodeva_glosa_detalle');
						$header_medio_pago = getParameterGeneral('prestamo_bodeva_medio_pago_haber');
						$header_medio_pago = substr($header_medio_pago, 0, 8);
						// Procesamiento de datos
						$fecha_anio = date('Y', strtotime($fecha_comprobante));
						$fecha_mes = date('m', strtotime($fecha_comprobante));

						$header_num_documento = substr($num_documento, 0, 20);


						$header_num_comprobante = substr($fecha_mes.str_pad($num_comprobante, 4, '0', STR_PAD_LEFT), 0, 6);
						$header_fecha_comprobante = date('d/m/Y', strtotime($fecha_comprobante));
						$header_fecha_documento = $header_fecha_comprobante;

						$header_fecha_tipo_cambio =$header_fecha_comprobante;
						$header_tipo_documento = substr($tipo_documento, 0, 2);

						//	Creación de archivo excel

						$objPHPExcel = new PHPExcel();
						$objPHPExcel->getProperties()->setCreator("AT")->setDescription("Reporte");   
						$tituloReporte = "Plantilla de egresos - Kasnet";
						
						$titulosColumnas = array(
							'Sub Diario',
							'Número de Comprobante',
							'Fecha de Comprobante',
							'Código de Moneda',
							'Glosa Principal',
							'Tipo de Cambio',
							'Tipo de Conversión',
							'Flag de Conversión de Moneda',
							'Fecha Tipo de Cambio',
							'Cuenta Contable',
							'Código de Anexo',
							'Código de Centro de Costo',
							'Debe / Haber',
							'Importe Original',
							'Importe en Dólares',
							'Importe en Soles',
							'Tipo de Documento',
							'Número de Documento',
							'Fecha de Documento',
							'Fecha de Vencimiento',
							'Código de Area',
							'Glosa Detalle',
							'Código de Anexo Auxiliar',
							'Medio de Pago',
							'Tipo de Documento de Referencia',
							'Número de Documento Referencia',
							'Fecha Documento Referencia',
							'Nro Máq. Registradora Tipo Doc. Ref.',
							'Base Imponible Documento Referencia',
							'IGV Documento Provisión',
							'Tipo Referencia en estado MQ',
							'Número Serie Caja Registradora',
							'Fecha de Operación',
							'Tipo de Tasa',
							'Tasa Detracción/Percepción',
							'Importe Base Detracción/Percepción Dólares',
							'Importe Base Detracción/Percepción Soles',
							"Tipo Cambio para 'F'",
							'Importe de IGV sin derecho crédito fiscal',
							'Tasa IGV'
						);

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

                   		$i = 4;

						$objPHPExcel->setActiveSheetIndex(0)
						   ->setCellValue('B'.$i, $cc_haber_subdiario)
						   ->setCellValue('C'.$i, $header_num_comprobante)
						   ->setCellValue('D'.$i, $header_fecha_comprobante)
						   ->setCellValue('E'.$i, $codigo_moneda)
						   ->setCellValue('F'.$i, $glosa_principal)
						   ->setCellValue('G'.$i, $tipo_cambio)
						   ->setCellValue('H'.$i, $tipo_conversion)
						   ->setCellValue('I'.$i, $flag_conversion_moneda)
						   ->setCellValue('J'.$i, $header_fecha_tipo_cambio)
						   ->setCellValue('K'.$i, $cc_haber_cuenta_contable)
						   ->setCellValue('L'.$i, $cc_haber_cod_anexo)
						   ->setCellValue('N'.$i, $header_cod_haber)
						   ->setCellValue('R'.$i, $header_tipo_documento)
						   ->setCellValue('S'.$i, $header_num_documento)
						   ->setCellValue('T'.$i, $header_fecha_documento)
						   ->setCellValue('U'.$i, $header_fecha_documento)
						   ->setCellValue('V'.$i, $codigo_area)
						   ->setCellValue('W'.$i, $glosa_detalle)
						   ->setCellValue('Y'.$i, $header_medio_pago);
						$i = 5;

						//	DETALLE

						$selectQueryDetalle = "SELECT
													l.cc_id,
													CASE WHEN l.red_id = 16 THEN 30 ELSE 5 END,
													pb.monto AS importe_original						
												FROM tbl_prestamo_programacion_detalle pd
												LEFT JOIN tbl_caja_prestamo_boveda pb
													ON pd.tbl_caja_prestamo_boveda_id = pb.id
												LEFT JOIN tbl_locales l
													ON pb.local_id = l.id
												WHERE pd.tbl_prestamo_programacion_id = ? AND pd.status = 1
												";
						$selectStmtDetalle = $mysqli->prepare($selectQueryDetalle);
						$selectStmtDetalle->bind_param("i", $id_programacion_boveda);
						$selectStmtDetalle->execute();

						$selectStmtDetalle->store_result(); // Almacenamos el resultado para poder obtener el número de filas
						$num_rows = $selectStmtDetalle->num_rows;

						if ($num_rows > 0) {
							$selectStmtDetalle->bind_result($local_cc_id, $local_razon_social, $detalle_importe_original);
							
							while ($selectStmtDetalle->fetch()) {

								//	Verificar si existe la cuenta contable

								$importe_total += $detalle_importe_original;
						
								//	Parametros generales para el detalle de programación de pago

								$detalle_cod_debe = getParameterGeneral('kasnet_codigo_debe');
								$detalle_cod_debe = substr($detalle_cod_debe, 0, 1);

								$detalle_cod_anexo = getParameterGeneral('prestamo_bodeva_text_cod_anexo');
								$detalle_cod_anexo = $local_cc_id.'-'.$detalle_cod_anexo;
								$detalle_cod_anexo = substr($detalle_cod_anexo, 0, 20);
								$detalle_cod_area_debe = getParameterGeneral('prestamo_bodeva_cod_area_debe');
								$detalle_cod_area_debe = substr($detalle_cod_area_debe, 0, 3);
								$detalle_glosa = getParameterGeneral('prestamo_bodeva_glosa_detalle');
								$detalle_glosa = substr($detalle_glosa, 0, 30);
								$detalle_cod_anexo_auxiliar = getParameterGeneral('prestamo_bodeva_codigo_anexo_auxiliar');
								$detalle_cod_anexo_auxiliar = substr($detalle_cod_anexo_auxiliar, 0, 18);

								//	Procesamiento de datos de detalle

								$importe_original_detalle_solicitud = (float)$row["saldo_incremento"];
								$detalle_importe_dolares = $detalle_importe_original / $tipo_cambio;
								$detalle_tipo_documento = getParameterGeneral('prestamo_bodeva_tipo_documento_debe');
								$detalle_tipo_documento = substr($detalle_tipo_documento, 0, 2);
								$detalle_tipo_documento_referencia = $detalle_tipo_documento;

								//$detalle_tipo_documento= getParameterGeneral('kasnet_tipo_documento_detalle_solicitud');

								$detalle_num_documento = "0".$fecha_mes."-".$fecha_anio;
								$detalle_num_documento = substr($detalle_num_documento, 0, 20);
								$detalle_num_documento_referencia = $detalle_num_documento;

								$detalle_fecha_documento_referencia = $header_fecha_comprobante;

								$objPHPExcel->setActiveSheetIndex(0)
											->setCellValue('B'.$i, $cc_haber_subdiario)
											->setCellValue('C'.$i, $header_num_comprobante)
											->setCellValue('D'.$i, $header_fecha_comprobante)
											->setCellValue('E'.$i, $codigo_moneda)
											->setCellValue('F'.$i, $glosa_principal)
											->setCellValue('G'.$i, $tipo_cambio)
											->setCellValue('H'.$i, $tipo_conversion)
											->setCellValue('I'.$i, $flag_conversion_moneda)
											->setCellValue('J'.$i, $header_fecha_tipo_cambio)
											->setCellValue('K'.$i, $cc_debe_cuenta_contable)
											->setCellValue('L'.$i, $detalle_cod_anexo)
											->setCellValue('N'.$i, $detalle_cod_debe)
											->setCellValue('O'.$i, $detalle_importe_original)
											->setCellValue('P'.$i, $detalle_importe_dolares)
											->setCellValue('Q'.$i, $detalle_importe_original)
											->setCellValue('R'.$i, $detalle_tipo_documento)
											->setCellValue('S'.$i, $detalle_num_documento)
											->setCellValue('T'.$i, $header_fecha_comprobante)
											->setCellValue('U'.$i, $header_fecha_documento)
											->setCellValue('V'.$i, $detalle_cod_area_debe)
											->setCellValue('W'.$i, $detalle_glosa)
											->setCellValue('X'.$i, $detalle_cod_anexo_auxiliar)
											->setCellValue('Z'.$i, $detalle_tipo_documento_referencia)
											->setCellValue('AA'.$i, $detalle_num_documento_referencia)
											->setCellValue('AB'.$i, $detalle_fecha_documento_referencia);
								$i++;								
							}

							$importe_total_dolares = $importe_total/$tipo_cambio;

							$objPHPExcel->setActiveSheetIndex(0)
								   ->setCellValue('O4',$importe_total)
								   ->setCellValue('P4',$importe_total_dolares)
								   ->setCellValue('Q4',$importe_total);

							//	Aplicación de estilos

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
							header('Content-Disposition: attachment;filename="Plantilla Concar Prestamos.xls');
							header('Cache-Control: max-age=0');

							$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
							$excel_path = $path.$excel_nombre;
							$excel_path_download = '/files_bucket/mepa/descargas/tesoreria/concar_liquidacion/'.$excel_nombre;

							$objWriter->save($excel_path);

							echo json_encode(array(
								"ruta_archivo" => $excel_path_download,
								"estado_archivo" => 1
							));
							exit;

						}else{
							throw new Exception("La programación de pago no tiene registros de detalle");
						}

						}else {
							throw new Exception("No existe el tipo de cambio de ese dia. Contactarse con soporte");
						}
					}else{
						throw new Exception("No se registro una cuenta contable haber en la cuenta registrada con esa programación de pago con el id =".$num_cuenta_id);
					}	
				}else {
					throw new Exception("No existe la cuenta contable registrada de la programación de pago con el id=".$num_cuenta_id);
				}
				
			} else {
				throw new Exception("No existe la programación de pago. Vuelva a cargar la página");
			}
		} else {
			throw new Exception("Error al guardar el número de comprobante y documento");
		}
	} catch (Exception $e) {
		echo json_encode(array(
			"error" => $e->getMessage(),
			"estado_archivo" => 0
			));
		exit;
	}
}
if(isset($_POST["accion"]) && $_POST["accion"]==="sec_prestamo_detalle_tesoreria_programacion_anular_prestamo") 
{
	$error = '';

	$id_prestamo_detalle = $_POST['id_prestamo_detalle'];
	$id_prestamo = $_POST['id_prestamo'];

	$query_delete = 
	"
		DELETE FROM tbl_prestamo_programacion_detalle 
		WHERE id = '".$id_prestamo_detalle."'
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

	$query_update = 
	"
		UPDATE tbl_caja_prestamo_boveda 
			SET situacion_tesoreria_etapa_id = 1
		WHERE id = '".$id_prestamo."'
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

	if ($error == '')
	{
		$result["http_code"] = 200;
		$result["status"] = "Datos correctos.";
		$result["error"] = $error;
	}
	else
	{
		$result["http_code"] = 400;
		$result["status"] = "Ocurrio un error.";
		$result["error"] = $error;
	}
}


echo json_encode($result);

?>