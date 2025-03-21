<?php

date_default_timezone_set("America/Lima");

$result=array();
include("db_connect.php");
include("sys_login.php");

include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';	

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(0);


if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_detalle_atencion_liquidacion_atencion_jefe") 
{
	$usuario_id = $login?$login['id']:null;
	
	if((int)$usuario_id > 0)
	{
		$created_at = date("Y-m-d H:i:s");
		$error = '';
		$query_update = "";

		$mepa_detalle_atencion_liquidacion_id = $_POST["mepa_detalle_atencion_liquidacion_id"];
		$txt_situacion_jefe = $_POST["txt_situacion_jefe"];
		$txt_motivo_rechazo_jefe = $_POST["txt_motivo_rechazo_jefe"];
		$txt_motivo_cerrar_jefe = $_POST["txt_motivo_cerrar_jefe"];

		//INICIO OBTENER EL ID ASIGNACION

		$query_sql = 
		"
	        SELECT
				asignacion_id,
				id_movilidad,
				se_aplica_movilidad,
				solicitar_eliminar_liquidacion
			FROM mepa_caja_chica_liquidacion
			WHERE id = '".$mepa_detalle_atencion_liquidacion_id."'
			LIMIT 1
		";

	    $list_query = $mysqli->query($query_sql);
	    
	    $row = $list_query->fetch_assoc();
	    $asignacion_id = $row["asignacion_id"];
	    $id_movilidad = $row["id_movilidad"];
	    $se_aplica_movilidad = $row["se_aplica_movilidad"];
	    $solicitar_eliminar_liquidacion = $row["solicitar_eliminar_liquidacion"];

		//FIN OBTENER EL ID ASIGNACION

		if($txt_situacion_jefe == 6 || $txt_situacion_jefe == 13)
		{
			//APROBADO O APROBAR DAR DE BAJA
			if($txt_situacion_jefe == 13)
			{
				if($solicitar_eliminar_liquidacion == 0)
				{
					$result["http_code"] = 400;
					$result["mepa_detalle_atencion_liquidacion_id"] = $mepa_detalle_atencion_liquidacion_id;
					$result["error"] = "No se pudo completar, porque el usuario revertio el dar de baja a esta liquidación.";

					echo json_encode($result);
					exit();
				}
			}
			$query_update = "
						UPDATE mepa_caja_chica_liquidacion 
							SET usuario_atencion_id_superior = '".$usuario_id."', 
							situacion_etapa_id_superior = '".$txt_situacion_jefe."',
							fecha_atencion_superior = '".date('Y-m-d H:i:s')."'
						WHERE id = '".$mepa_detalle_atencion_liquidacion_id."' ";
		}
		else if($txt_situacion_jefe == 12)
		{
			//APROBADO Y CERRAR CAJA CHICA
			$query_update = "
						UPDATE mepa_caja_chica_liquidacion 
							SET usuario_atencion_id_superior = '".$usuario_id."', 
							situacion_etapa_id_superior = 6,
							situacion_jefe_cerrar_caja_chica = '".$txt_situacion_jefe."',
							motivo_cerrar_caja_chica = '".$txt_motivo_cerrar_jefe."',
							fecha_atencion_superior = '".date('Y-m-d H:i:s')."'
						WHERE id = '".$mepa_detalle_atencion_liquidacion_id."' ";

			$query_cerrar_asignacion = "
						UPDATE mepa_asignacion_caja_chica 
							SET situacion_etapa_id = 8
						WHERE id = '".$asignacion_id."' 
						";
		}
		else
		{
			//RECHAZADO
			$query_update = "
						UPDATE mepa_caja_chica_liquidacion 
							SET usuario_atencion_id_superior = '".$usuario_id."', 
							situacion_etapa_id_superior = '".$txt_situacion_jefe."',
							situacion_motivo_superior = '".$txt_motivo_rechazo_jefe."',
							fecha_atencion_superior = '".date('Y-m-d H:i:s')."'
						WHERE id = '".$mepa_detalle_atencion_liquidacion_id."' ";

			
			// INICIO INSERTAMOS EN LA TABLA mepa_caja_chica_rechazadas PARA EL HISTORIAL
			$query_insert = "INSERT INTO mepa_caja_chica_rechazadas
							(
								id_liquidacion,
								motivo,
								id_tipos_solicitud_usuario,
								user_created_id,
								created_at
							)
							VALUES
							(
								'".$mepa_detalle_atencion_liquidacion_id."',
								'".$txt_motivo_rechazo_jefe."',
								5,
								'".$usuario_id."',
								'".date('Y-m-d H:i:s')."'
							)
							";
			$mysqli->query($query_insert);

			// FIN INSERTAMOS EN LA TABLA mepa_caja_chica_rechazadas PARA EL HISTORIAL
		}
		

		$mysqli->query($query_update);

		if($txt_situacion_jefe == 12)
		{
			$mysqli->query($query_cerrar_asignacion);
		}
		
		if($mysqli->error)
		{
			$error = $mysqli->error;
		}

		if ($error == '') 
		{
			//ENVIAR CORREO
			if($txt_situacion_jefe == 6 || $txt_situacion_jefe == 12)
			{
				//APROBADO O APROBADO Y CERRADO

				// INICIO: SI ES APROBADO Y CERRADO (ULTIMA CAJA CHICA)
				//CALCULAR SI PROCEDE CON LA DEVOLUCION DEL SALDO RESTANTE
				if($txt_situacion_jefe == 12)
				{
					$query_select = "
							SELECT
								a.id AS asignacion_id,
								a.fondo_asignado AS fondo_asignado,
								a.saldo_disponible AS saldo_disponible
							FROM mepa_asignacion_caja_chica a
							WHERE a.id = '".$asignacion_id."' AND situacion_etapa_id = 8
							";

					$data_query = $mysqli->query($query_select);

					while($row = $data_query->fetch_assoc())
					{
						$asignacion_id = $row["asignacion_id"];
						$fondo_asignado = $row["fondo_asignado"];
						$saldo_disponible = $row["saldo_disponible"];
					}

					if($saldo_disponible > 0)
					{
						$query_update_devolucion = "
									UPDATE mepa_asignacion_caja_chica 
										SET aplica_devolucion = 1,
											se_solicito_devolucion = 0
									WHERE id = '".$asignacion_id."'
							";
					}
					else
					{
						$query_update_devolucion = "
									UPDATE mepa_asignacion_caja_chica 
										SET aplica_devolucion = 0,
											se_solicito_devolucion = 0
									WHERE id = '".$asignacion_id."'
							";
					}

					$mysqli->query($query_update_devolucion);
					
				}
				// FIN: SI ES APROBADO Y CERRADO (ULTIMA CAJA CHICA)
				// CALCULAR SI PROCEDE CON LA DEVOLUCION DEL SALDO RESTANTE

				$result["http_code"] = 200;
				$result["mepa_detalle_atencion_liquidacion_id"] = $mepa_detalle_atencion_liquidacion_id;
				$result["status"] = "Solicitud Aprobada.";
				$result["texto"] = "La solicitud fue Aprobada exitosamente.";
				$result["error"] = $error;

				send_email_atencion_caja_chica_aprobado($mepa_detalle_atencion_liquidacion_id, false);

				echo json_encode($result);
				exit();
			}
			else if($txt_situacion_jefe == 7)
			{
				//RECHAZADO

				$result["http_code"] = 200;
				$result["mepa_detalle_atencion_liquidacion_id"] = $mepa_detalle_atencion_liquidacion_id;
				$result["status"] = "Solicitud Rechazada.";
				$result["texto"] = "La solicitud fue Rechazada.";
				$result["error"] = $error;

				send_email_atencion_caja_chica_rechazado($mepa_detalle_atencion_liquidacion_id, false, '');

				echo json_encode($result);
				exit();
			}
			else if($txt_situacion_jefe == 13)
			{
				$query_select = 
				"
					SELECT
						l.asignacion_id,
					    IFNULL(l.total_rendicion, 0) AS total_liquidacion,
						l.se_aplica_movilidad,
					    l.id_movilidad,
						IFNULL(m.monto_cierre, 0) AS total_movilidad
					FROM mepa_caja_chica_liquidacion l
						LEFT JOIN mepa_caja_chica_movilidad m
						ON l.id_movilidad = m.id
					WHERE l.id = '".$mepa_detalle_atencion_liquidacion_id."'
				";

				$data_query = $mysqli->query($query_select);

				$sub_total_a_devolver = 0;

				while($row = $data_query->fetch_assoc())
				{
					$asignacion_id = $row["asignacion_id"];
					$total_liquidacion = $row["total_liquidacion"];
					$se_aplica_movilidad = $row["se_aplica_movilidad"];
					$id_movilidad = $row["id_movilidad"];
					$total_movilidad = $row["total_movilidad"];

					$sub_total_a_devolver = $total_liquidacion + $total_movilidad;
				}


				$query_update_devolucion = 
				"
					UPDATE mepa_asignacion_caja_chica 
						SET saldo_disponible = saldo_disponible + '".$sub_total_a_devolver."'
					WHERE id = '".$asignacion_id."'
				";

				$mysqli->query($query_update_devolucion);

				if($mysqli->error)
				{
					$error .= $mysqli->error;
				}

				if ($error == '') 
				{
					$result["http_code"] = 200;
					$result["status"] = "Se registro correctamente.";
					$result["texto"] = "Se dio de baja la liquidación.";
					$result["result"] = $error;
				} 
				else 
				{
					$result["http_code"] = 400;
					$result["status"] = "No se pudo dar de baja.";
					$result["error"] = "No se pudo actualizar el saldo_disponible, ERROR: ".$error;
				}

				echo json_encode($result);
				exit();
			}

		} 
		else 
		{
			$result["http_code"] = 400;
			$result["mepa_detalle_atencion_liquidacion_id"] = $mepa_detalle_atencion_liquidacion_id;
			$result["status"] = "Datos no obtenidos.";
			$result["texto"] = "La solicitud fue error.";
			$result["error"] = $error;

			echo json_encode($result);
			exit();
		}
	}
	else
	{
		$result["http_code"] = 400;
        $result["error"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";

        echo json_encode($result);
		exit();
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_detalle_atencion_liquidacion_atencion_contabilidad") 
{
	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	$error = '';
	$query_update = "";

	$mepa_detalle_atencion_liquidacion_id = $_POST["mepa_detalle_atencion_liquidacion_id"];
	$mepa_detalle_atencion_liquidacion_empresa_id = $_POST["mepa_detalle_atencion_liquidacion_empresa_id"];
	$mepa_detalle_atencion_liquidacion_situacion_cerrar_caja_chica = $_POST["mepa_detalle_atencion_liquidacion_situacion_cerrar_caja_chica"];
	$txt_situacion_contabilidad = $_POST["txt_situacion_contabilidad"];
	$txt_motivo_rechazo_contabilidad = $_POST["txt_motivo_rechazo_contabilidad"];

	$correo_adjuntos = $_POST["txt_correo_adjuntos"];
	$correo_adjuntos = json_decode($correo_adjuntos);

	foreach($correo_adjuntos AS $correo)
	{
		if(preg_match('/^[_ña-z0-9-]+(\.[_ña-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/',trim($correo)) == 0)
		{
			$error = "'" . $correo . "'" ." no es un correo válido.";
			
			if($correo == "")
			{
				$error = "Formato de Correo Incorrecto";
			}
	        
			$result["http_code"] = 500;
			$result["mepa_detalle_atencion_liquidacion_id"] = $mepa_detalle_atencion_liquidacion_id;
			$result["status"] = "Error.";
			$result["error"] = $error;
			echo json_encode($result);
			exit;
	    }
	}

	$where_correlativo_tipo_solicitud_id = 0;
	
	if($mepa_detalle_atencion_liquidacion_empresa_id == 30)
	{
		$where_correlativo_tipo_solicitud_id = 19;
	}
	else
	{
		$where_correlativo_tipo_solicitud_id = 4;
	}
	
	$query_sql = 
	"
        SELECT
			id,
			num_correlativo
		FROM mepa_documento_correlativo
		WHERE tipo_solicitud = '".$where_correlativo_tipo_solicitud_id."'
	";

    $list_query_correlativo_concar = $mysqli->query($query_sql);

    if($mysqli->error)
	{
		$error = $mysqli->error;
	}

	if ($error == '') 
	{
		$row_count_correlativo_concar = $list_query_correlativo_concar->num_rows;

		if($row_count_correlativo_concar == 0)
		{
			$result["http_code"] = 400;
			$result["mepa_detalle_atencion_liquidacion_id"] = $mepa_detalle_atencion_liquidacion_id;
			$result["status"] = "Alerta.";
			$result["error"] = "El correlativo para el Concar no existe, porfavor contactarse con SISTEMAS";

			echo json_encode($result);

			exit;
		}
		else if($row_count_correlativo_concar > 1)
		{
			$result["http_code"] = 400;
			$result["mepa_detalle_atencion_liquidacion_id"] = $mepa_detalle_atencion_liquidacion_id;
			$result["status"] = "Alerta.";
			$result["error"] = "Existen muchos correlativos para generar el Concar, porfavor contactarse con SISTEMAS";

			echo json_encode($result);

			exit;
		}
		else if($row_count_correlativo_concar == 1)
		{
			$row = $list_query_correlativo_concar->fetch_assoc();
    		$id_correlativo = $row["id"];
    		$num_correlativo = $row["num_correlativo"];
		}
	}
	else
	{
		$result["http_code"] = 400;
		$result["mepa_detalle_atencion_liquidacion_id"] = $mepa_detalle_atencion_liquidacion_id;
		$result["status"] = "Error.";
		$result["error"] = $error;

		echo json_encode($result);
		
		exit;
	}
	

	if($txt_situacion_contabilidad == 6)
	{

		//APROBADO
		$query_update = 
		"
			UPDATE mepa_caja_chica_liquidacion 
				SET usuario_atencion_id_contabilidad = '".$usuario_id."', 
				situacion_etapa_id_contabilidad = '".$txt_situacion_contabilidad."',
				fecha_atencion_contabilidad = '".date('Y-m-d H:i:s')."',
				numero_comprobante_concar = '".$num_correlativo."',
				fecha_comprobante_concar = '".date('Y-m-d')."'
			WHERE id = '".$mepa_detalle_atencion_liquidacion_id."' 
		";

		if($mepa_detalle_atencion_liquidacion_situacion_cerrar_caja_chica == 12)
		{
			$query_update_devolucion = "";

			// INICIO: SI ES APROBADO
			// SI ES CERRADO (ULTIMA CAJA CHICA)
			// CALCULAR SI PROCEDE CON LA DEVOLUCION DEL SALDO RESTANTE
			$query_select = 
			"
				SELECT
					a.id AS asignacion_id,
					a.fondo_asignado AS fondo_asignado,
					a.saldo_disponible AS saldo_disponible
				FROM mepa_asignacion_caja_chica a
				INNER JOIN mepa_caja_chica_liquidacion l
				ON a.id = l.asignacion_id 
				WHERE l.id = '".$mepa_detalle_atencion_liquidacion_id."' 
					AND situacion_etapa_id = 8
			";

			$data_query = $mysqli->query($query_select);

			while($row = $data_query->fetch_assoc())
			{
				$asignacion_id = $row["asignacion_id"];
				$fondo_asignado = $row["fondo_asignado"];
				$saldo_disponible = $row["saldo_disponible"];
			}
			
			if($saldo_disponible > 0)
			{
				$query_update_devolucion = 
				"
					UPDATE mepa_asignacion_caja_chica 
						SET aplica_devolucion = 1,
							se_solicito_devolucion = 0
					WHERE id = '".$asignacion_id."'
				";
			}
			else
			{
				$query_update_devolucion = 
				"
					UPDATE mepa_asignacion_caja_chica 
						SET aplica_devolucion = 0,
							se_solicito_devolucion = 0
					WHERE id = '".$asignacion_id."'
				";
			}
			
			$mysqli->query($query_update_devolucion);
			// FIN: SI ES APROBADO
			// SI ES CERRADO (ULTIMA CAJA CHICA)
			// CALCULAR SI PROCEDE CON LA DEVOLUCION DEL SALDO RESTANTE
		}
	}
	else
	{
		//RECHAZADO
		$query_update = "
					UPDATE mepa_caja_chica_liquidacion 
						SET usuario_atencion_id_contabilidad = '".$usuario_id."', 
						situacion_etapa_id_contabilidad = '".$txt_situacion_contabilidad."',
						situacion_motivo_contabilidad = '".$txt_motivo_rechazo_contabilidad."',
						fecha_atencion_contabilidad = '".date('Y-m-d H:i:s')."'
					WHERE id = '".$mepa_detalle_atencion_liquidacion_id."' ";


		// INICIO INSERTAMOS EN LA TABAL mepa_caja_chica_rechazadas PARA EL HISTORIAL
		$query_insert = "INSERT INTO mepa_caja_chica_rechazadas
						(
							id_liquidacion,
							motivo,
							id_tipos_solicitud_usuario,
							user_created_id,
							created_at
						)
						VALUES
						(
							'".$mepa_detalle_atencion_liquidacion_id."',
							'".$txt_motivo_rechazo_contabilidad."',
							6,
							'".$usuario_id."',
							'".date('Y-m-d H:i:s')."'
						)
						";
		$mysqli->query($query_insert);

		// FIN INSERTAMOS EN LA TABAL mepa_caja_chica_rechazadas PARA EL HISTORIAL
	}
	

	$mysqli->query($query_update);

	if($mysqli->error)
	{
		$error = $mysqli->error;
	}

	if ($error == '') 
	{
		//ENVIAR CORREO
		if($txt_situacion_contabilidad == 6)
		{
			//INSERTAR EL REGISTRO DE CORRELATIVO PARA LA EXPORTACION DEL CONCAR DE CONTABILIDAD
			
		    $update_correlativo_concar = "UPDATE mepa_documento_correlativo
				SET 				
					num_correlativo = num_correlativo + 1
				WHERE id =  " .$id_correlativo ;

			$mysqli->query($update_correlativo_concar);

			//APROBADO O APROBADO Y CERRADO

			send_email_atencion_caja_chica_aprobado($mepa_detalle_atencion_liquidacion_id, true);

			$result["http_code"] = 200;
			$result["mepa_detalle_atencion_liquidacion_id"] = $mepa_detalle_atencion_liquidacion_id;
			$result["status"] = "Solicitud Aprobada.";
			$result["texto"] = "La solicitud fue Aprobada exitosamente.";
			$result["error"] = $error;

		}
		else if($txt_situacion_contabilidad == 7)
		{
			//RECHAZADO

			$result["http_code"] = 200;
			$result["mepa_detalle_atencion_liquidacion_id"] = $mepa_detalle_atencion_liquidacion_id;
			$result["status"] = "Solicitud Rechazada.";
			$result["texto"] = "La solicitud fue Rechazada.";
			$result["error"] = $error;

			$data = [
				$correo_adjuntos
			];
			
			send_email_atencion_caja_chica_rechazado($mepa_detalle_atencion_liquidacion_id, true, ...$data);
		}
		
	}
	else 
	{
		$result["http_code"] = 400;
		$result["mepa_detalle_atencion_liquidacion_id"] = $mepa_detalle_atencion_liquidacion_id;
		$result["status"] = "Error.";
		$result["error"] = $error;
		
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "generar_pdf_liquidacion")
{
	require_once '/var/www/html/sys/fpdf/fpdf.php';
	$id = $_POST["mepa_caja_chica_liquidacion_id"];
	$tipo_usuario_generar_pdf = $_POST["tipo_usuario_generar_pdf"];

	

	$sel_query = $mysqli->query("
				SELECT
					li.id,
					li.num_correlativo,
					rs.nombre as razon_social,
    				rs.ruc as razon_social_ruc,
				    li.solicitante_usuario_id as responsable_id,
				    concat(IFNULL(resp_p.nombre, ''),' ', IFNULL(resp_p.apellido_paterno, '')) AS responsable_nombre,
                    resp_p.dni as responsable_dni,
					resp_a.nombre as responsable_area,
				    li.usuario_atencion_id_superior as jefe_id,
					concat(IFNULL(jefe_p.nombre, ''),' ', IFNULL(jefe_p.apellido_paterno, '')) AS jefe_nombre,
					li.fecha_desde, 
					li.fecha_hasta,										
					li.total_rendicion AS monto_liquidacion,
				    li.created_at AS fecha_solicitud,			
                    mov.monto_cierre AS monto_movilidad,
                    li.se_aplica_movilidad,
				    li.id_movilidad
				FROM mepa_caja_chica_liquidacion li
					INNER JOIN tbl_usuarios resp_u ON resp_u.id = li.solicitante_usuario_id
					INNER JOIN tbl_personal_apt resp_p ON resp_p.id = resp_u.personal_id 
                    INNER JOIN tbl_areas resp_a on resp_a.id = resp_p.area_id
                	LEFT JOIN tbl_usuarios jefe_u ON jefe_u.id = li.usuario_atencion_id_superior
					LEFT JOIN tbl_personal_apt jefe_p ON jefe_p.id = jefe_u.personal_id
					LEFT JOIN mepa_caja_chica_movilidad mov ON li.id_movilidad = mov.id
					INNER JOIN mepa_asignacion_caja_chica a
					ON li.asignacion_id = a.id
					INNER JOIN tbl_razon_social rs
					ON a.empresa_id = rs.id
				WHERE li.id = $id
				");
	$liquidacion = $sel_query->fetch_assoc();

	//IMAGES
	$path = "/var/www/html/files_bucket/mepa/firmas/";

    $imageLayer = [];
    if (!is_dir($path)) mkdir($path, 0777, true);
    $file = null;

    $imgs = [];

    if($tipo_usuario_generar_pdf == 1)
    {
    	if($_FILES['firma_responsable']['name'] != "")
	    {
	    	$firma_query = $mysqli->query("
							SELECT firma,usuario_id					
							FROM mepa_usuario_firma
							WHERE usuario_id = {$liquidacion["responsable_id"]}
							");
	    	$firma = $firma_query->fetch_assoc();
	    	if($firma != ""){
	    		$mysqli->query("DELETE FROM mepa_usuario_firma WHERE usuario_id = {$liquidacion['responsable_id']}");
	    		@unlink($path.$firma["firma"]);
	    	}

		    $filename = $_FILES['firma_responsable']['tmp_name'];
		    $sourceProperties = getimagesize($filename);
		    $resizeFileName = $liquidacion["responsable_id"] . "_" . date('YmdHis');

		    $fileExt = pathinfo($_FILES['firma_responsable']['name'], PATHINFO_EXTENSION);
		    $uploadImageType = $sourceProperties[2];
		    $sourceImageWith = $sourceProperties[0];
		    $sourceImageHeight = $sourceProperties[1];

		    switch ($uploadImageType) {
		        case IMAGETYPE_JPEG:
		            $resourceType = imagecreatefromjpeg($filename);
		            $imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);
		            $file = imagejpeg($imageLayer[0], $path . $resizeFileName . '.' . $fileExt);
		            break;
		        case IMAGETYPE_PNG:
		            $resourceType = imagecreatefrompng($filename);
		            $imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);
		            $file = imagepng($imageLayer[0], $path . $resizeFileName .'.' . $fileExt);
		            break;
		        default:
		            break;
		    }
		    move_uploaded_file($file, $path . $resizeFileName . "." . $fileExt);
		    $mysqli->query("
		        INSERT INTO mepa_usuario_firma(usuario_id,firma)
		            VALUES(
		            ".$liquidacion["responsable_id"].",
		            '".$resizeFileName . "." . $fileExt."'
		            )
		    ");
		    $imgs["resp"] =$resizeFileName.".".$fileExt;
		}
    }
    else if($tipo_usuario_generar_pdf == 2)
    {
    	if($_FILES['firma_jefe']['name'] != "")
	    {
	    	$firma_query = $mysqli->query("
							SELECT firma,usuario_id					
							FROM mepa_usuario_firma
							WHERE usuario_id = {$liquidacion["jefe_id"]}
							");
	    	$firma = $firma_query->fetch_assoc();
	    	if($firma != ""){
	    		$mysqli->query("DELETE FROM mepa_usuario_firma WHERE usuario_id = {$liquidacion['jefe_id']}");
	    		unlink($path.$firma["firma"]);
	    	}

		    $filename = $_FILES['firma_jefe']['tmp_name'];
		    $sourceProperties = getimagesize($filename);
		    $resizeFileName = $liquidacion["jefe_id"] . "_" . date('YmdHis');

		    $fileExt = pathinfo($_FILES['firma_jefe']['name'], PATHINFO_EXTENSION);
		    $uploadImageType = $sourceProperties[2];
		    $sourceImageWith = $sourceProperties[0];
		    $sourceImageHeight = $sourceProperties[1];

		    switch ($uploadImageType) {
		        case IMAGETYPE_JPEG:
		            $resourceType = imagecreatefromjpeg($filename);
		            $imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);
		            $file = imagejpeg($imageLayer[0], $path . $resizeFileName . '.' . $fileExt);
		            break;
		        case IMAGETYPE_PNG:
		            $resourceType = imagecreatefrompng($filename);
		            $imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);
		            $file = imagepng($imageLayer[0], $path . $resizeFileName .'.' . $fileExt);
		            break;
		        default:
		            break;
		    }
		    move_uploaded_file($file, $path . $resizeFileName . "." . $fileExt);
		    $mysqli->query("
		        INSERT INTO mepa_usuario_firma(usuario_id,firma)
		            VALUES(
		            ".$liquidacion["jefe_id"].",
		            '".$resizeFileName . "." . $fileExt."'
		            )
		    ");
		    $imgs["jefe"] =$resizeFileName.".".$fileExt;
		}
    }

	//FIN IMAGES
	//$id = $_GET["id"] ;	
	$detalle_query = $mysqli->query("
					SELECT 
					d.fecha_documento, 
					td.nombre as tipo_documento,
					CONCAT(d.serie_comprobante, ' - ' ,d.num_comprobante) AS num_comprobante,
					d.centro_costo,
					d.detalle,
					d.importe
					FROM wwwapuestatotal_gestion.mepa_detalle_caja_chica_liquidacion d
					LEFT JOIN mepa_tipo_documento td ON td.id = d.tipo_documento
					WHERE mepa_caja_chica_liquidacion_id = $id
					");
	$detalle = [];
	while($d = $detalle_query->fetch_assoc())
	{
		$detalle[] = $d;
	}	

	if($liquidacion["se_aplica_movilidad"] == 1)
	{
		//$detalle[] = ["detalle"=> "'Planilla de Gastos de Movilidad Nro ".$liquidacion["se_aplica_movilidad"]];
		$movilidad_query = $mysqli->query("
						SELECT 
						'' AS fecha_documento,
						'' as tipo_documento,
						'' AS num_comprobante,
						'' AS centro_costo,
						CONCAT('Planilla de Gastos de Movilidad Nro ',m.num_correlativo) AS detalle,
						m.monto_cierre  AS importe
						FROM mepa_caja_chica_movilidad m						
						WHERE m.id = ".$liquidacion["id_movilidad"]
						);
		while($d = $movilidad_query->fetch_assoc())
		{
			$detalle[] = $d;
		}
	}
	
	$liquidacion["detalle"] = $detalle;

	$firma_query = $mysqli->query("
						SELECT
						firma,
						usuario_id
						FROM mepa_usuario_firma
						WHERE usuario_id IN ({$liquidacion['responsable_id']},{$liquidacion['jefe_id']})
						");
	$firmas = [];
	while($d = $firma_query->fetch_assoc())
	{
		$firmas[$d["usuario_id"]] = $d;
	}
	$liquidacion["firmas"] = $firmas;
	$pdf = generar_pdf_liquidacion($liquidacion);
	$path = '/var/www/html/files_bucket/mepa/descargas/detalle_liquidacion/pdf/';
    if (!is_dir($path)) mkdir($path, 0777, true);
	$nombre_archivo = "liquidacion_".date('YmdHis').".pdf";
	$attach_temp = $pdf->Output($path.$nombre_archivo, 'F');

	/*header('Content-type: application/pdf');
	header('Content-Disposition: inline; filename="' . $nombre_archivo . '"');
	header('Content-Transfer-Encoding: binary');
	header('Accept-Ranges: bytes');
	@readfile($path.$nombre_archivo);*/

	$result["file_pdf"] = "files_bucket/mepa/descargas/detalle_liquidacion/pdf/".$nombre_archivo;
	$result["imgs"] = $imgs;
}

function generar_pdf_liquidacion($liquidacion)
{
	$font_family = "helvetica";
	$pdf = new FPDF();
	//$pdf->AddFont('Calibri','','sys/fpdf/font/calibri.php');
	$pdf->AddPage();
	$pdf->SetFont($font_family,'B',10);
	$pdf->Ln();

	$x_image = 15;
	$pdf -> SetX($x_image);
	$pdf->Image('../images/logo_reporte_pdf_liquidacion.png',$x_image,10,21,18);

	$x_title_red = 38;
	$pdf -> SetX($x_title_red);
	$pdf->SetTextColor(255,0,0);//red
	$pdf->Cell($x_title_red,8,utf8_decode($liquidacion["razon_social"]));

	$pdf->Ln();
	$pdf -> SetX($x_title_red);
	$pdf->Cell($x_title_red,8,utf8_decode($liquidacion["razon_social_ruc"]));
	$pdf->Ln();
	$pdf->SetTextColor(0,0,0);
	$pdf->SetFont($font_family,'BU',12);
	$pdf -> SetX(65);
	$pdf->Cell(40,20,utf8_decode('LIQUIDACIÓN DE CAJA CHICA No '.$liquidacion["num_correlativo"]));
	$pdf->Ln();

	$y = $pdf->GetY();
	$x = $pdf->GetX();

	$pdf->SetY($y);
	$pdf->SetX(45);
	$pdf->Cell(20,10,'___________________');
	$pdf->SetY($y);

	$pdf->SetY($y);
	$pdf->SetX(102);
	$pdf->Cell(6,10,'________');
	$pdf->SetY($y);

	$pdf -> SetX(14);
	$pdf->SetFont($font_family,'B',8);
	$pdf->Cell(30,10,'RESPONSABLE:');
	$pdf->SetFont($font_family,'',8);
	$pdf->Cell(50,10,$liquidacion["responsable_nombre"],'',0,'C');


	//$pdf->SetY($y);
	$pdf->SetFont($font_family,'B',8);
	$pdf->Cell(10,10,'DNI:');
	$pdf->SetFont($font_family,'',8);
	$pdf->Cell(36,10,$liquidacion["responsable_dni"]);

	$pdf->SetFont($font_family,'B',8);
	$pdf->Cell(15,10,utf8_decode('ÁREA:'));
	$pdf->SetFont($font_family,'',8);
	$pdf->Cell(40,10,$liquidacion["responsable_area"]);

	$pdf->Ln();

	$y = $pdf->GetY();
	$pdf -> SetX(14);
	$pdf->SetFont($font_family,'B',8);
	$pdf->Cell(30,5,'JEFATURA');

	$pdf->Ln();

	$y = $pdf->GetY();
	$x = $pdf->GetX();
	$pdf->SetY($y-5);
	$pdf->SetX(46);
	$pdf->Cell(45,10,'','B',1,'C');

	$pdf->SetY($y-5);
	$pdf->SetX(152);
	$pdf->Cell(44,10,'','B',1,'C');
	$pdf->SetY($y);

	$pdf -> SetX(14);
	$pdf->SetFont($font_family,'B',8);
	$pdf->Cell(30,6,'INMEDIATA:');
	$pdf->SetFont($font_family,'',8);
	$pdf->Cell(50,6,$liquidacion["jefe_nombre"],'',0,'C');

	$pdf -> SetX(132);
	$pdf->SetFont($font_family,'B',8);
	$pdf->Cell(25,6,'FECHA DEL:');
	$pdf->SetFont($font_family,'',8);
	$pdf->Cell(60,6,date("d-m-Y",strtotime($liquidacion["fecha_desde"]))." al ".date("d-m-Y",strtotime($liquidacion["fecha_hasta"])));

	$pdf->Ln();
	$pdf->Ln();

	$w_col_fecha = 17;
	$w_col_documento = 40;
	$w_nro = 24;
	$w_col_centro_costo = 25;
	$w_col_detalle = 63;
	$w_col_importe = 14;

	$h_tr = 8;
	$h_tr1 = 8;
	$x_tabla1 = 14;
	$pdf->SetFont($font_family,'B',12);
	$pdf -> SetX($x_tabla1);
	$pdf->Cell($w_col_fecha,12,"",0);

	$pdf->SetFont($font_family,'B',8);
	$pdf -> SetX($x_tabla1);
	$pdf->SetTextColor(0,0,0);
	$pdf->setFillColor(189, 215, 238);

	$pdf->Cell($w_col_fecha,$h_tr,"Fecha Doc.",1,0,'C',TRUE);
	$pdf->Cell($w_col_documento,$h_tr,"Documento",1,0,'C',TRUE);
	$pdf->Cell($w_nro,$h_tr,"No Comprobante",1,0,'C',TRUE);
	$pdf->Cell($w_col_centro_costo,$h_tr,"Centro de Costo",1,0,'C',TRUE);
	$pdf->Cell($w_col_detalle,$h_tr,"Detalle",1,0,'C',TRUE);
	$pdf->Cell($w_col_importe,$h_tr,"Importe",1,0,'C',TRUE);
	$pdf->Ln();
	$pdf->setFillColor(255,255,255);

	$pdf->SetTextColor(0,0,0);
	$pdf->SetFont($font_family,'',8);
	$pdf -> SetX($x_tabla1);

	//echo "<pre>";print_r($liquidacion["detalle"]);echo "</pre>";die();
	$suma = 0;
	$h_tr1 = 6;
	$h_min = 6;
	foreach ($liquidacion["detalle"] as $key => $value) {
		$pdf -> SetX($x_tabla1);
		$length = strlen(utf8_decode($value["detalle"]));
		$lines = ceil($length / 36);
		$h_tr1 = $lines * $h_min;

		if(substr( utf8_decode($value["detalle"]), 0, 21 ) == "Planilla de Gastos de")
		{
			$h_tr1 = $h_min;
		}
		$fecha_temp = $value["fecha_documento"]?date("d-M",strtotime($value["fecha_documento"])):"";
		$pdf->Cell($w_col_fecha,$h_tr1,$fecha_temp,1,0,'C');
		$pdf->Cell($w_col_documento,$h_tr1, utf8_decode($value["tipo_documento"]) ,1,0,'C');
		$pdf->Cell($w_nro,$h_tr1,utf8_decode($value["num_comprobante"]),1,0,'C');
		$pdf->Cell($w_col_centro_costo,$h_tr1,utf8_decode($value["centro_costo"]),1,0,'C');

		$x_before = $pdf->GetX();
		$y_before = $pdf->GetY();
		$pdf->MultiCell($w_col_detalle,$h_min, utf8_decode($value["detalle"]),1,'1',true);
		$y_after = $pdf->GetY();
		$pdf->SetXY($x_before + $w_col_detalle ,$y_before);
		$pdf->Cell($w_col_importe,$h_tr1, number_format($value["importe"], 2, ".", ","),1,0,'R');
		$pdf->SetY($y_after);

		$suma += $value["importe"];
	}

	$pdf->SetFont($font_family,'B',9);
	//$pdf->SetTextColor(52, 73, 94);
	$pdf -> SetX($x_tabla1);
	$pdf->Cell($w_col_fecha,$h_tr,'',1);
	$pdf->Cell($w_col_documento,$h_tr, '', 1,0,'R');
	$pdf->Cell($w_nro,$h_tr, '',   1,0,'R');
	$pdf->Cell($w_col_centro_costo,$h_tr, '',1,0,'R');
	$pdf->Cell($w_col_detalle,$h_tr, utf8_decode("Total:"),1,0,'R');
	$pdf->Cell($w_col_importe,$h_tr, number_format($suma, 2, ".", ","),1,0,'R');
	$pdf->Ln();
	$pdf->Ln();

	$pdf->SetTextColor(0,0,0);
	$x_resumen1 = 70;
	$x_resumen2 = 100;
	$h_tr_resumen = 6;

	$pdf->SetX($x_resumen1);

	$pdf->SetFont($font_family,'B',8);
	$pdf->Cell(30,$h_tr_resumen,"RESUMEN",0,0,'L');
	$pdf->SetX($x_resumen2);

	$pdf->Cell(40,$h_tr_resumen, '' ,0,0,'R');
	$pdf->Ln();

	$pdf->SetX($x_resumen1);
	$pdf->SetFont($font_family,'',8);
	$pdf->Cell(30,$h_tr_resumen,utf8_decode("TOTAL RENDICIÓN"),0,0,'L');
	$pdf->SetFont($font_family,'',8);
	$pdf->SetX($x_resumen2);
	$pdf->Cell(40,$h_tr_resumen, number_format($suma, 2, ".", ",") ,'B',0,'R');
	$pdf->Ln();

	$pdf->SetX($x_resumen1);
	$pdf->SetFont($font_family,'B',8);
	$pdf->Cell(30,$h_tr_resumen,"TOTAL",0,0,'L');
	$pdf->SetX($x_resumen2);
	$pdf->Cell(40,$h_tr_resumen, number_format($suma, 2, ".", ",") ,0,0,'R');
	$pdf->Ln();

	$pdf->Ln();
	$pdf->Ln();

	$x_firma1 = 40;
	$x_firma2 = 125;
	$y = $pdf->GetY();
	$pdf->SetTextColor(0,0,0);
	if(isset($liquidacion["firmas"][$liquidacion["responsable_id"]])){
		$image1 = $liquidacion["firmas"][$liquidacion["responsable_id"]]["firma"];
		if(file_exists('../files_bucket/mepa/firmas/'.$image1)){
			$pdf->Image('../files_bucket/mepa/firmas/'.$image1 , $x_firma1+10, $y , 25,25);
		}
	}
	if(isset($liquidacion["firmas"][$liquidacion["jefe_id"]])){
		$image2 = $liquidacion["firmas"][$liquidacion["jefe_id"]]["firma"];
		if(file_exists('../files_bucket/mepa/firmas/'.$image2)){
			$pdf->Image('../files_bucket/mepa/firmas/'.$image2 , $x_firma2+10 ,$y , 25,25);
		}
	}

	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Ln();

	$y = $pdf->GetY();
	$pdf->SetFont($font_family,'B',10);	
	$pdf->SetX($x_firma1);
	$pdf->Cell(50,1,'','B',0,'C');

	$pdf->SetFont($font_family,'B',10);	
	$pdf->SetX($x_firma2);
	$pdf->Cell(50,1,'','B',0,'C');
	$pdf->Ln();

	$pdf->SetFont($font_family,'',8);
	$pdf->SetX($x_firma1);
	$pdf->SetFont($font_family,'B',10);
	$pdf->Cell(50,8,'FIRMA DEL RESPONSABLE',0,0,'C');

	$pdf->SetX($x_firma2);
	$pdf->SetFont($font_family,'B',10);
	$pdf->Cell(50,8,'FIRMA DEL JEFE INMEDIATO',0,0,'C');

	$pdf->Ln();
	$pdf->SetFont($font_family,'',8);
	$pdf->SetX($x_firma1);
	$pdf->Cell(50,8,strtoupper($liquidacion["responsable_nombre"])." - "."SUPERVISOR",0,0,'C' );

	$pdf->SetX($x_firma2);
	$pdf->Cell(50,8,strtoupper($liquidacion["jefe_nombre"])." - "."JEFE DE OPERACIONES" ,0,0,'C');

	$pdf->Ln();
	$pdf->SetX($x_firma1);
	$pdf->Cell(50,8, "APUESTAS DEPORTIVAS",0,0,'C');

	$pdf->SetX($x_firma2);
	$pdf->Cell(50,8, "APUESTAS DEPORTIVAS",0,0,'C');

	$pdf->Ln();
	
	return $pdf;
}

function resizeImage($resourceType, $image_width, $image_height)
{
    $imagelayer = [];
    if ($image_width < 1920 && $image_height < 1080) {
        $imagelayer[0] = imagecreatetruecolor($image_width, $image_height);
        imagecopyresampled($imagelayer[0], $resourceType, 0, 0, 0, 0, $image_width, $image_height, $image_width, $image_height);
    } else {
        $ratio = $image_width / $image_height;
        $escalaW = 1920 / $image_width;
        $escalaH = 1080 / $image_height;
        if ($ratio > 1) {
            $resizewidth = $image_width * $escalaW;
            $resizeheight = $image_height * $escalaW;

        } else {
            $resizeheight = $image_height * $escalaH;
            $resizewidth = $image_width * $escalaH;
        }
        $imagelayer[0] = imagecreatetruecolor($resizewidth, $resizeheight);
        imagecopyresampled($imagelayer[0], $resourceType, 0, 0, 0, 0, $resizewidth, $resizeheight, $image_width, $image_height);
    }

    return $imagelayer;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_detalle_liquidacion_ruc")
{
	$param_detalle_id = $_POST['detalle_id'];

	if($_POST["ruc"] == "" || strlen($_POST["ruc"]) != 11)
	{
		$result["titulo"] = "Alerta";
		$result["texto"] = "Ingresar RUC (11 dígitos)";
		$result["focus"] = "ruc";
		$result["error"] = true;
		echo json_encode($result);
		die();
	}
	$ruc = $_POST["ruc"] != "" ? $_POST["ruc"]:"null";
	
	//INICIO: VALIDAR DATOS EXISTENTES
	$query_select_validar = 
	"
		SELECT
			id, ruc
		FROM mepa_detalle_caja_chica_liquidacion
		WHERE id = '".$param_detalle_id."'
	";

	$query = $mysqli->query($query_select_validar);
    $cant_registro = $query->num_rows;

    if($cant_registro > 0)
    {
        $reg = $query->fetch_assoc();
        $select_id = $reg["id"];
        $select_ruc = $reg["ruc"];
    }
    else
    {
    	$result["titulo"] = "Error";
		$result["texto"] = "No se encontro el registro.";
		$result["focus"] = "ruc";
		$result["error"] = true;
		echo json_encode($result);
		die();
    }

    if($ruc == $select_ruc)
    {
    	$result["titulo"] = "Alerta";
		$result["texto"] = "El RUC ya existe, ingrese otro.";
		$result["focus"] = "ruc";
		$result["error"] = true;
		echo json_encode($result);
		die();
    }
	//FIN: VALIDAR DATOS EXISTENTES

	$query_update = "
					UPDATE mepa_detalle_caja_chica_liquidacion SET ruc = '".$ruc."'  WHERE id = ".$_POST["detalle_id"];

	$mysqli->query($query_update);
	$result["query"] = $query_update;
	$result["titulo"] = "";
	$result["texto"] = "RUC Actualizado.";
}

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_detalle_liquidacion_tipo_documento")
{
	$param_detalle_id = $_POST['detalle_id'];

	if($_POST["tipo_documento"] == "")
	{
		$result["titulo"] = "Alerta";
		$result["texto"] = "Ingresar Tipo Documento";
		$result["focus"] = "tipo_documento";
		$result["error"] = true;
		echo json_encode($result);
		die();
	}
	$tipo_documento = $_POST["tipo_documento"] != "" ? $_POST["tipo_documento"]:"null";
	
	//INICIO: VALIDAR DATOS EXISTENTES
	$query_select_validar = 
	"
		SELECT
			id, tipo_documento
		FROM mepa_detalle_caja_chica_liquidacion
		WHERE id = '".$param_detalle_id."'
	";

	$query = $mysqli->query($query_select_validar);
    $cant_registro = $query->num_rows;

    if($cant_registro > 0)
    {
        $reg = $query->fetch_assoc();
        $select_id = $reg["id"];
        $select_tipo_documento = $reg["tipo_documento"];
    }
    else
    {
    	$result["titulo"] = "Error";
		$result["texto"] = "No se encontro el registro.";
		$result["focus"] = "tipo_documento";
		$result["error"] = true;
		echo json_encode($result);
		die();
    }

    if($tipo_documento == $select_tipo_documento)
    {
    	$result["titulo"] = "Alerta";
		$result["texto"] = "El tipo documento ya existe, seleccione otro.";
		$result["focus"] = "tipo_documento";
		$result["error"] = true;
		echo json_encode($result);
		die();
    }
	//FIN: VALIDAR DATOS EXISTENTES

	$query_update = "
					UPDATE mepa_detalle_caja_chica_liquidacion 
							SET tipo_documento = '".$tipo_documento."'
							WHERE id = ".$_POST["detalle_id"];

	$mysqli->query($query_update);
	$result["query"] = $query_update;
	$result["titulo"] = "";
	$result["texto"] = "Tipo Documento Actualizado.";
}

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_detalle_liquidacion_codigo_provision")
{
	$param_detalle_id = $_POST['detalle_id'];

	if($_POST["codigo_provision"] == "")
	{
		$result["titulo"] = "Alerta";
		$result["focus"] = "codigo_provision";
		$result["texto"] = "Ingresar Codigo Provisión";
		$result["error"] = true;
		echo json_encode($result);
		die();
	}
	$codigo_provision = $_POST["codigo_provision"] != "" ? $_POST["codigo_provision"]:"null";

	//INICIO: VALIDAR DATOS EXISTENTES
	$query_select_validar = 
	"
		SELECT
			id, codigo_provision_contable
		FROM mepa_detalle_caja_chica_liquidacion
		WHERE id = '".$param_detalle_id."'
	";

	$query = $mysqli->query($query_select_validar);
    $cant_registro = $query->num_rows;

    if($cant_registro > 0)
    {
        $reg = $query->fetch_assoc();
        $select_id = $reg["id"];
        $select_codigo_provision = $reg["codigo_provision_contable"];
    }
    else
    {
    	$result["titulo"] = "Error";
		$result["texto"] = "No se encontro el registro.";
		$result["focus"] = "codigo_provision";
		$result["error"] = true;
		echo json_encode($result);
		die();
    }

    if($codigo_provision == $select_codigo_provision)
    {
    	$result["titulo"] = "Alerta";
		$result["texto"] = "La cuenta contable ya existe, seleccione otro.";
		$result["focus"] = "codigo_provision";
		$result["error"] = true;
		echo json_encode($result);
		die();
    }
	//FIN: VALIDAR DATOS EXISTENTES

	$query_update = "
					UPDATE mepa_detalle_caja_chica_liquidacion 
							SET codigo_provision_contable = '".$codigo_provision."'
							WHERE id = ".$_POST["detalle_id"];

	$mysqli->query($query_update);
	$result["query"] = $query_update;
	$result["titulo"] = "";
	$result["texto"] = "Codigo Provisión Actualizado.";
}

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_detalle_liquidacion_serie_comprobante")
{
	$param_detalle_id = $_POST['detalle_id'];

	if($_POST["serie_comprobante"] == "")
	{
		$result["titulo"] = "Alerta";
		$result["texto"] = "Ingresar Serie Comprobante.";
		$result["focus"] = "serie_comprobante";
		$result["error"] = true;
		echo json_encode($result);
		die();
	}
	$serie_comprobante = $_POST["serie_comprobante"] != "" ? $_POST["serie_comprobante"]:"null";
	
	//INICIO: VALIDAR DATOS EXISTENTES
	$query_select_validar = 
	"
		SELECT
			id, serie_comprobante
		FROM mepa_detalle_caja_chica_liquidacion
		WHERE id = '".$param_detalle_id."'
	";

	$query = $mysqli->query($query_select_validar);
    $cant_registro = $query->num_rows;

    if($cant_registro > 0)
    {
        $reg = $query->fetch_assoc();
        $select_id = $reg["id"];
        $select_serie_comprobante = $reg["serie_comprobante"];
    }
    else
    {
    	$result["titulo"] = "Error";
		$result["texto"] = "No se encontro el registro.";
		$result["focus"] = "serie_comprobante";
		$result["error"] = true;
		echo json_encode($result);
		die();
    }

    if($serie_comprobante == $select_serie_comprobante)
    {
    	$result["titulo"] = "Alerta";
		$result["texto"] = "La serie comprobante ya existe, ingrese otro.";
		$result["focus"] = "serie_comprobante";
		$result["error"] = true;
		echo json_encode($result);
		die();
    }
	//FIN: VALIDAR DATOS EXISTENTES

	$query_update = "
					UPDATE mepa_detalle_caja_chica_liquidacion 
						SET serie_comprobante = '".$serie_comprobante."'  
					WHERE id = ".$_POST["detalle_id"];

	$mysqli->query($query_update);
	$result["query"] = $query_update;
	$result["titulo"] = "";
	$result["texto"] = "Serie Comprobante Actualizado.";
}

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_detalle_liquidacion_num_comprobante")
{
	$param_detalle_id = $_POST['detalle_id'];

	if($_POST["num_comprobante"] == "")
	{
		$result["titulo"] = "Alerta";
		$result["texto"] = "Ingresar Nº Comprobante";
		$result["focus"] = "num_comprobante";
		$result["error"] = true;
		echo json_encode($result);
		die();
	}
	$num_comprobante = $_POST["num_comprobante"] != "" ? $_POST["num_comprobante"]:"null";
	
	//INICIO: VALIDAR DATOS EXISTENTES
	$query_select_validar = 
	"
		SELECT
			id, num_comprobante
		FROM mepa_detalle_caja_chica_liquidacion
		WHERE id = '".$param_detalle_id."'
	";

	$query = $mysqli->query($query_select_validar);
    $cant_registro = $query->num_rows;

    if($cant_registro > 0)
    {
        $reg = $query->fetch_assoc();
        $select_id = $reg["id"];
        $select_num_comprobante = $reg["num_comprobante"];
    }
    else
    {
    	$result["titulo"] = "Error";
		$result["texto"] = "No se encontro el registro.";
		$result["focus"] = "num_comprobante";
		$result["error"] = true;
		echo json_encode($result);
		die();
    }

    if($num_comprobante == $select_num_comprobante)
    {
    	$result["titulo"] = "Alerta";
		$result["texto"] = "El Nº comprobante ya existe, ingrese otro.";
		$result["focus"] = "num_comprobante";
		$result["error"] = true;
		echo json_encode($result);
		die();
    }
	//FIN: VALIDAR DATOS EXISTENTES

	$query_update = "
					UPDATE mepa_detalle_caja_chica_liquidacion 
						SET num_comprobante = '".$num_comprobante."'  
					WHERE id = ".$_POST["detalle_id"];

	$mysqli->query($query_update);
	$result["query"] = $query_update;
	$result["titulo"] = "";
	$result["texto"] = "Nº Comprobante Actualizado.";
}

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_detalle_liquidacion_centro_costo")
{
	$param_detalle_id = $_POST['detalle_id'];

	if($_POST["centro_costo"] == "")
	{
		$result["titulo"] = "Alerta";
		$result["texto"] = "Ingresar Centro Costo";
		$result["focus"] = "centro_costo";
		$result["error"] = true;
		echo json_encode($result);
		die();
	}
	$centro_costo = $_POST["centro_costo"] != "" ? $_POST["centro_costo"]:"null";
	
	//INICIO: VALIDAR DATOS EXISTENTES
	$query_select_validar = 
	"
		SELECT
			id, centro_costo
		FROM mepa_detalle_caja_chica_liquidacion
		WHERE id = '".$param_detalle_id."'
	";

	$query = $mysqli->query($query_select_validar);
    $cant_registro = $query->num_rows;

    if($cant_registro > 0)
    {
        $reg = $query->fetch_assoc();
        $select_id = $reg["id"];
        $select_centro_costo = $reg["centro_costo"];
    }
    else
    {
    	$result["titulo"] = "Error";
		$result["texto"] = "No se encontro el registro.";
		$result["focus"] = "centro_costo";
		$result["error"] = true;
		echo json_encode($result);
		die();
    }

    if($centro_costo == $select_centro_costo)
    {
    	$result["titulo"] = "Alerta";
		$result["texto"] = "El centro de costo ya existe, ingrese otro.";
		$result["focus"] = "centro_costo";
		$result["error"] = true;
		echo json_encode($result);
		die();
    }
	//FIN: VALIDAR DATOS EXISTENTES

	$query_update = "
					UPDATE mepa_detalle_caja_chica_liquidacion 
						SET centro_costo = '".$centro_costo."'  
					WHERE id = ".$_POST["detalle_id"];

	$mysqli->query($query_update);
	$result["query"] = $query_update;
	$result["titulo"] = "";
	$result["texto"] = "Centro Costo Actualizado.";
}

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_detalle_liquidacion_tasa_igv")
{
	$param_detalle_id = $_POST['detalle_id'];

	if($_POST["tasa_igv"] == "")
	{
		$result["titulo"] = "Alerta";
		$result["texto"] = "Ingresar Tasa IGV";
		$result["focus"] = "tasa_igv";
		$result["error"] = true;
		echo json_encode($result);
		die();
	}
	$tasa_igv = $_POST["tasa_igv"] != "" ? $_POST["tasa_igv"]:"null";

	//INICIO: VALIDAR DATOS EXISTENTES
	$query_select_validar = 
	"
		SELECT
			id, tasa_igv
		FROM mepa_detalle_caja_chica_liquidacion
		WHERE id = '".$param_detalle_id."'
	";

	$query = $mysqli->query($query_select_validar);
    $cant_registro = $query->num_rows;

    if($cant_registro > 0)
    {
        $reg = $query->fetch_assoc();
        $select_id = $reg["id"];
        $select_tasa_igv = $reg["tasa_igv"];
    }
    else
    {
    	$result["titulo"] = "Error";
		$result["texto"] = "No se encontro el registro.";
		$result["focus"] = "tasa_igv";
		$result["error"] = true;
		echo json_encode($result);
		die();
    }

    if($tasa_igv == $select_tasa_igv)
    {
    	$result["titulo"] = "Alerta";
		$result["texto"] = "El IGV ya existe, ingrese otro.";
		$result["focus"] = "tasa_igv";
		$result["error"] = true;
		echo json_encode($result);
		die();
    }
	//FIN: VALIDAR DATOS EXISTENTES

	$query_update = "
					UPDATE mepa_detalle_caja_chica_liquidacion 
						SET tasa_igv = '".$tasa_igv."'  
					WHERE id = ".$_POST["detalle_id"];

	$mysqli->query($query_update);
	$result["query"] = $query_update;
	$result["titulo"] = "";
	$result["texto"] = "Tasa IGV Actualizado.";
}

if (isset($_POST["accion"]) && $_POST["accion"] === "eliminar_detalle_liquidacion")
{
	$id = $_POST["id"];
	$detalle_id = $_POST["detalle_id"];
	$asignacion_id = $_POST["asignacion_id"];
	$importe = $_POST["importe"];
	$nombre_file = $_POST["nombre_file"];
	$error = '';

	$query = "
		DELETE FROM  mepa_detalle_caja_chica_liquidacion 						
		WHERE id = ".$_POST["detalle_id"];
	$mysqli->query($query);
	
	$path = "/var/www/html/files_bucket/mepa/solicitudes/liquidacion/";
	if(file_exists("/var/www/html/files_bucket/mepa/solicitudes/liquidacion/".$nombre_file))
	{
		unlink("/var/www/html/files_bucket/mepa/solicitudes/liquidacion/".$nombre_file);
	}

	//INICIO SUMAR EL MONTO EN EL SALDO DISPONIBLE DE LA ASIGNACION CORRESPONDIENTE

    $asignacion_importe_detalle_liquidacion = "UPDATE mepa_asignacion_caja_chica
												SET 				
													saldo_disponible = saldo_disponible + '".$importe."'
												WHERE id =  " .$asignacion_id ;

	$mysqli->query($asignacion_importe_detalle_liquidacion);
	//FIN SUMAR EL MONTO EN EL SALDO DISPONIBLE DE LA ASIGNACION CORRESPONDIENTE


	//UPDATE  total_rendicion  en  CABECERA
	$query_update = "
	UPDATE mepa_caja_chica_liquidacion 
		SET total_rendicion = (SELECT SUM(importe) FROM mepa_detalle_caja_chica_liquidacion WHERE mepa_caja_chica_liquidacion_id = {$id} AND status = 1)
	WHERE id = ".$id;
	$mysqli->query($query_update);

	if($mysqli->error)
	{
		$error = $mysqli->error;
	}

	if ($error == '') 
	{
		$result["http_code"] = 200;
		$result["mensaje"] = "Registro Eliminado.";
	} 
	else 
	{
		$result["http_code"] = 400;
		$result["mensaje"] = $error;
	}

}

if (isset($_POST["accion"]) && $_POST["accion"] === "agregar_detalle_liquidacion")
{
	extract($_POST);
	
	$importe = str_replace(",","",$_POST["importe"]);

	$error = '';

	foreach ($_POST as $key => $value) 
	{
		if($value == "" && $key != "detalle_id")
		{
			$result["focus"] = $key;
			$result["mensaje"] = "Ingresar el campo " .$key;
			$result["error"] = true;
			echo json_encode($result);
			die();
		}
	}

	if($_FILES['nombre_file']['name'] == "")
	{
		$result["focus"] = "nombre_file";
		$result["mensaje"] = "Ingresar Archivo";
		$result["error"] = true;
		echo json_encode($result);
		die();
	}

	$path = "/var/www/html/files_bucket/mepa/solicitudes/liquidacion/";
	$download = "/files_bucket/mepa/solicitudes/liquidacion/";

	if (!is_dir($path)) 
	{
		mkdir($path, 0777, true);
	}

	$imageLayer = [];
	$file = null;

    $filename = $_FILES['nombre_file']['tmp_name'];
	$nombre_file_size = $_FILES['nombre_file']['size'];
    $nombre_file_extension = pathinfo($_FILES['nombre_file']['name'], PATHINFO_EXTENSION);
    $nombre_file = "liquidacion_detalle_fecha".date('YmdHis') . "." . $nombre_file_extension;

	$nombreDownload = $download.$nombre_file;
	//move_uploaded_file($filename, $path. $nombreFileUpload);

	if($nombre_file_extension != "pdf")
	{
		$sourceProperties = getimagesize($filename);
	    $uploadImageType = $sourceProperties[2];
	    $sourceImageWith = $sourceProperties[0];
	    $sourceImageHeight = $sourceProperties[1];

	    switch ($uploadImageType) {
	        case IMAGETYPE_JPEG:
	            $resourceType = imagecreatefromjpeg($filename);
	            $imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);
	            $file = imagejpeg($imageLayer[0], $path . $nombre_file);
	            break;
	        case IMAGETYPE_PNG:
	            $resourceType = imagecreatefrompng($filename);
	            $imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);
	            $file = imagepng($imageLayer[0], $path . $nombre_file);
	            break;
	        default:
	            break;
	    }
	}
	else
	{
        $file = $_FILES['nombre_file']['tmp_name'];
	}

	move_uploaded_file($file, $path . $nombre_file);

	$filename_xml = $_FILES['nombre_file_xml']['tmp_name'];

	if(!empty($filename_xml))
	{
		$nombre_file_size_xml = $_FILES['nombre_file_xml']['size'];
	    $nombre_file_extension_xml = pathinfo($_FILES['nombre_file_xml']['name'], PATHINFO_EXTENSION);
	    $nombre_file_xml = "liquidacion_detalle_xml_fecha".date('YmdHis') . "." . $nombre_file_extension_xml;

	    $nombreDownload_xml = $download.$nombre_file_xml;

	    if($nombre_file_extension_xml != "xml")
		{
		    $sourceProperties = getimagesize($filename);
		    $uploadImageType = $sourceProperties[2];
		    $sourceImageWith = $sourceProperties[0];
		    $sourceImageHeight = $sourceProperties[1];

		    switch ($uploadImageType) {
		        case IMAGETYPE_JPEG:
		            $resourceType = imagecreatefromjpeg($filename);
		            $imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);
		            $file = imagejpeg($imageLayer[0], $path . $nombre_file_xml);
		            break;
		        case IMAGETYPE_PNG:
		            $resourceType = imagecreatefrompng($filename);
		            $imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);
		            $file = imagepng($imageLayer[0], $path . $nombre_file_xml);
		            break;
		        default:
		            break;
		    }
		}
		else
		{
	        $file = $_FILES['nombre_file_xml']['tmp_name'];
		}

		move_uploaded_file($file, $path . $nombre_file_xml);
	}

    $sel_query = $mysqli->query("
		SELECT count(id)+1 AS cont FROM mepa_detalle_caja_chica_liquidacion 
		WHERE mepa_caja_chica_liquidacion_id = {$mepa_caja_chica_liquidacion_id} ");
	$cont = $sel_query->fetch_assoc();
	$cont = isset($cont)?$cont["cont"]:1;

	if(empty($filename_xml))
	{
		// NO EXISTE ARCHIVO XML
		$query_insert_liquidacion_detalle = "INSERT INTO mepa_detalle_caja_chica_liquidacion
				(
					mepa_caja_chica_liquidacion_id,
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
					'".$mepa_caja_chica_liquidacion_id."',
					'".$cont."',
					'".$fecha_documento."',
					'".$tipo_documento."',
					'".strtoupper($serie_comprobante)."',
					'".strtoupper($num_comprobante)."',
					'".$centro_costo."',
					'".$detalle."',
					'".$importe."',
					'".$tasa_igv."',
					'".$nombre_file."',
					'".$nombre_file_extension."',
					'".$nombre_file_size."',
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
		$query_insert_liquidacion_detalle = "INSERT INTO mepa_detalle_caja_chica_liquidacion
				(
					mepa_caja_chica_liquidacion_id,
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
					'".$mepa_caja_chica_liquidacion_id."',
					'".$cont."',
					'".$fecha_documento."',
					'".$tipo_documento."',
					'".strtoupper($serie_comprobante)."',
					'".strtoupper($num_comprobante)."',
					'".$centro_costo."',
					'".$detalle."',
					'".$importe."',
					'".$tasa_igv."',
					'".$nombre_file."',
					'".$nombre_file_extension."',
					'".$nombre_file_size."',
					'".$path."',
					'".$nombreDownload."',
					1,
					'".$login["id"]."', 
					'".date('Y-m-d H:i:s')."',
					'".$login["id"]."', 
					'".date('Y-m-d H:i:s')."',
					'".$nombre_file_xml."',
					'".$nombre_file_extension_xml."',
					'".$nombre_file_size_xml."',
					'".$path."',
					'".$nombreDownload_xml."'
				)";
	}
	
	$mysqli->query($query_insert_liquidacion_detalle);

	if($mysqli->error)
	{
		$error = $mysqli->error;
	}

	if ($error == '') 
	{
		//UPDATE  total_rendicion  en  CABECERA
		$query_update = "
		UPDATE mepa_caja_chica_liquidacion 
			SET total_rendicion = (SELECT SUM(importe) FROM mepa_detalle_caja_chica_liquidacion WHERE mepa_caja_chica_liquidacion_id = {$mepa_caja_chica_liquidacion_id} )
		WHERE id = ".$mepa_caja_chica_liquidacion_id;
		$mysqli->query($query_update);

		//INICIO ACTUALIZAR EL SALDO DISPONIBLE
		$query_update_saldo = "
						UPDATE mepa_asignacion_caja_chica 
							SET saldo_disponible = saldo_disponible - '".$importe."'
						WHERE id = '".$asignacion_id."' 
						";
		$mysqli->query($query_update_saldo);
		//FIN ACTUALIZAR EL SALDO DISPONIBLE

		$result["mensaje"] = "Detalle liquidación Insertado";
	} 
	else 
	{
		$result["mensaje"] = "No se inserto: " .$error;	
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "guardar_detalle_liquidacion_add_detalle_fila")
{
	$param_detalle_id = $_POST["detalle_id"];
	$param_cant_fila = $_POST["txt_add_fila"];

	if($param_cant_fila == "0")
	{
		$result["focus"] = "txt_add_fila";
		$result["mensaje"] = "Ingresar número mayor a cero";
		$result["error"] = true;
		echo json_encode($result);
		die();
	}

	$query_select = 
	"
		SELECT
			id, mepa_caja_chica_liquidacion_id, item, fecha_documento, 
			tipo_documento, serie_comprobante, num_comprobante, centro_costo, 
			detalle, nombre_file, extension, size, ruta, download_file, 
		    status, user_created_id, created_at, user_updated_id, updated_at,
		    importe, ruc, nombre_file_xml, extension_xml, size_xml, 
		    ruta_xml, download_file_xml, codigo_provision_contable, tasa_igv
		FROM mepa_detalle_caja_chica_liquidacion
		WHERE id = '".$param_detalle_id."'
	";

	$list_query = $mysqli->query($query_select);

	$row_count = $list_query->num_rows;

	$campos_cabecera = "";
	$campos_valores = "";

	if ($row_count > 0) 
	{
		$row = $list_query->fetch_assoc();
		$id = $row["id"];
		$mepa_caja_chica_liquidacion_id = $row["mepa_caja_chica_liquidacion_id"];
		if(!empty($mepa_caja_chica_liquidacion_id))
		{
			$campos_cabecera .= "mepa_caja_chica_liquidacion_id, ";
			$campos_valores .= " '".$mepa_caja_chica_liquidacion_id."', ";
		}
		$fecha_documento = $row["fecha_documento"];
		if(!empty($fecha_documento))
		{
			$campos_cabecera .= "fecha_documento, ";
			$campos_valores .= " '".$fecha_documento."', ";
		}
		$tipo_documento = $row["tipo_documento"];
		if(!empty($tipo_documento))
		{
			$campos_cabecera .= "tipo_documento, ";
			$campos_valores .= " '".$tipo_documento."', ";
		}
		$serie_comprobante = $row["serie_comprobante"];
		if(!empty($serie_comprobante))
		{
			$campos_cabecera .= "serie_comprobante, ";
			$campos_valores .= " '".$serie_comprobante."', ";
		}
		$num_comprobante = $row["num_comprobante"];
		if(!empty($num_comprobante))
		{
			$campos_cabecera .= "num_comprobante, ";
			$campos_valores .= " '".$num_comprobante."', ";
		}
		$centro_costo = $row["centro_costo"];
		if(!empty($centro_costo))
		{
			$campos_cabecera .= "centro_costo, ";
			$campos_valores .= " '".$centro_costo."', ";
		}
		$detalle = $row["detalle"];
		if(!empty($detalle))
		{
			$campos_cabecera .= "detalle, ";
			$campos_valores .= " '".$detalle."', ";
		}
		$nombre_file = $row["nombre_file"];
		if(!empty($nombre_file))
		{
			$campos_cabecera .= "nombre_file, ";
			$campos_valores .= " '".$nombre_file."', ";
		}
		$extension = $row["extension"];
		if(!empty($extension))
		{
			$campos_cabecera .= "extension, ";
			$campos_valores .= " '".$extension."', ";
		}
		$size = $row["size"];
		if(!empty($size))
		{
			$campos_cabecera .= "size, ";
			$campos_valores .= " '".$size."', ";
		}
		$ruta = $row["ruta"];
		if(!empty($ruta))
		{
			$campos_cabecera .= "ruta, ";
			$campos_valores .= " '".$ruta."', ";
		}
		$download_file = $row["download_file"];
		if(!empty($download_file))
		{
			$campos_cabecera .= "download_file, ";
			$campos_valores .= " '".$download_file."', ";
		}
		$status = $row["status"];
		if(!empty($status))
		{
			$campos_cabecera .= "status, ";
			$campos_valores .= " '".$status."', ";
		}
		$user_created_id = $row["user_created_id"];
		if(!empty($user_created_id))
		{
			$campos_cabecera .= "user_created_id, ";
			$campos_valores .= " '".$user_created_id."', ";
		}
		$created_at = $row["created_at"];
		if(!empty($created_at))
		{
			$campos_cabecera .= "created_at, ";
			$campos_valores .= " '".$created_at."', ";
		}
		$user_updated_id = $row["user_updated_id"];
		if(!empty($user_updated_id))
		{
			$campos_cabecera .= "user_updated_id, ";
			$campos_valores .= " '".$user_updated_id."', ";
		}
		$updated_at = $row["updated_at"];
		if(!empty($updated_at))
		{
			$campos_cabecera .= "updated_at, ";
			$campos_valores .= " '".$updated_at."', ";
		}
		$importe = $row["importe"];
		$ruc = $row["ruc"];
		if(!empty($ruc))
		{
			$campos_cabecera .= "ruc, ";
			$campos_valores .= " '".$ruc."', ";
		}
		$nombre_file_xml = $row["nombre_file_xml"];
		if(!empty($nombre_file_xml))
		{
			$campos_cabecera .= "nombre_file_xml, ";
			$campos_valores .= " '".$nombre_file_xml."', ";
		}
		$extension_xml = $row["extension_xml"];
		if(!empty($extension_xml))
		{
			$campos_cabecera .= "extension_xml, ";
			$campos_valores .= " '".$extension_xml."', ";
		}
		$size_xml = $row["size_xml"];
		if(!empty($size_xml))
		{
			$campos_cabecera .= "size_xml, ";
			$campos_valores .= " '".$size_xml."', ";
		}
		$ruta_xml = $row["ruta_xml"];
		if(!empty($ruta_xml))
		{
			$campos_cabecera .= "ruta_xml, ";
			$campos_valores .= " '".$ruta_xml."', ";
		}
		$download_file_xml = $row["download_file_xml"];
		if(!empty($download_file_xml))
		{
			$campos_cabecera .= "download_file_xml, ";
			$campos_valores .= " '".$download_file_xml."', ";
		}
		$codigo_provision_contable = $row["codigo_provision_contable"];
		if(!empty($codigo_provision_contable))
		{
			$campos_cabecera .= "codigo_provision_contable, ";
			$campos_valores .= " '".$codigo_provision_contable."', ";
		}
		$tasa_igv = $row["tasa_igv"];
		if(!empty($tasa_igv))
		{
			$campos_cabecera .= "tasa_igv, ";
			$campos_valores .= " '".$tasa_igv."', ";
		}
	}
	else
	{
		$result["focus"] = "txt_add_fila";
		$result["mensaje"] = "No se encontro el registro, vulve a refrescar la página";
		$result["error"] = true;
		echo json_encode($result);
		die();
	}

	$param_importe = $importe / ($param_cant_fila + 1);

	if(!empty($param_importe))
	{
		$campos_cabecera .= "importe, ";
		$campos_valores .= " '".$param_importe."', ";
	}

	$campos_cabecera = rtrim($campos_cabecera, ", ");
	$campos_valores = rtrim($campos_valores, ", ");

	for($i = 1; $i <= $param_cant_fila; $i++)
	{
		$insert_detalle_liquidacion = 
		"
			INSERT INTO mepa_detalle_caja_chica_liquidacion
			(
				".$campos_cabecera."
			)
			VALUES
			(
				".$campos_valores."
			)
		";
		
		$mysqli->query($insert_detalle_liquidacion);

		if($mysqli->error)
		{
			$error = $mysqli->error;

			$result["focus"] = "txt_add_fila";
			$result["mensaje"] = "Ocurrió un error: ".$error;
			$result["error"] = true;
			echo json_encode($result);
			die();
		}
	}

	$query_update = 
	"
		UPDATE mepa_detalle_caja_chica_liquidacion 
			SET importe = '".$param_importe."'  
		WHERE id = '".$param_detalle_id."'
	";

	$mysqli->query($query_update);
	$result["query"] = $query_update;
	$result["mensaje"] = "Filas agregado correctamente.";
}

if (isset($_POST["accion"]) && $_POST["accion"] === "get_detalle_liquidacion")
{
	$query = "
		SELECT 
			id AS detalle_id,
			fecha_documento, 
			tipo_documento, 
			serie_comprobante, 
			num_comprobante,  
			centro_costo,
			detalle, 
			nombre_file,
			importe,
			tasa_igv,
			nombre_file_xml
		FROM mepa_detalle_caja_chica_liquidacion 						
		WHERE id = ".$_POST["detalle_id"];
	
	$row =  $mysqli->query($query)->fetch_assoc();

	$result["detalle_liquidacion"] = $row;
}

if (isset($_POST["accion"]) && $_POST["accion"] === "editar_detalle_liquidacion")
{
	extract($_POST);

	$importe = str_replace(",","",$_POST["importe"]);

	$error = '';

	foreach ($_POST as $key => $value) 
	{
		if($value == "" && $key != "nombre_file")
		{
			$result["focus"] = $key;
			$result["mensaje"] = "Ingresar el campo " .$key;
			$result["error"] = true;
			echo json_encode($result);
			die();
		}
	}
	$update_file = "";
	if($_FILES['nombre_file']['name'] != "")
	{

		$path = "/var/www/html/files_bucket/mepa/solicitudes/liquidacion/";
		$download = "/files_bucket/mepa/solicitudes/liquidacion/";

		if (!is_dir($path)) 
		{
			mkdir($path, 0777, true);
		}

		@unlink($path.$nombre_file_actual);

		$imageLayer = [];
		$file = null;

	    $filename = $_FILES['nombre_file']['tmp_name'];
		$nombre_file_size = $_FILES['nombre_file']['size'];
	    $nombre_file_extension = pathinfo($_FILES['nombre_file']['name'], PATHINFO_EXTENSION);
	    $nombre_file = "liquidacion_detalle_fecha".date('YmdHis') . "." . $nombre_file_extension;

	    $nombreDownload = $download.$nombre_file;

	    if($nombre_file_extension != "pdf")
	    {
		    $sourceProperties = getimagesize($filename);
		    $uploadImageType = $sourceProperties[2];
		    $sourceImageWith = $sourceProperties[0];
		    $sourceImageHeight = $sourceProperties[1];

		    switch ($uploadImageType) 
		    {
		        case IMAGETYPE_JPEG:
		            $resourceType = imagecreatefromjpeg($filename);
		            $imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);
		            $file = imagejpeg($imageLayer[0], $path . $nombre_file);
		            break;
		        case IMAGETYPE_PNG:
		            $resourceType = imagecreatefrompng($filename);
		            $imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);
		            $file = imagepng($imageLayer[0], $path . $nombre_file);
		            break;
		        default:
		            break;
		    }
		}
        else
        {
        	$file = $_FILES['nombre_file']['tmp_name'];
        }

	    move_uploaded_file($file, $path . $nombre_file);
	    $update_file = "nombre_file = '{$nombre_file}',extension = '{$nombre_file_extension}',size = '{$nombre_file_size}',ruta = '{$path}',download_file = '{$nombreDownload}',";
	}

	if($_FILES['nombre_file_xml']['name'] != "")
	{
		
		$path = "/var/www/html/files_bucket/mepa/solicitudes/liquidacion/";
		$download = "/files_bucket/mepa/solicitudes/liquidacion/";

		if (!is_dir($path)) 
		{
			mkdir($path, 0777, true);
		}

		@unlink($path.$nombre_file_xml_actual);

		$imageLayer = [];
		$file = null;

	    $filename = $_FILES['nombre_file_xml']['tmp_name'];
		$nombre_file_size = $_FILES['nombre_file_xml']['size'];
	    $nombre_file_extension = pathinfo($_FILES['nombre_file_xml']['name'], PATHINFO_EXTENSION);
	    $nombre_file_xml = "liquidacion_detalle_xml_fecha".date('YmdHis') . "." . $nombre_file_extension;

	    $nombreDownload = $download.$nombre_file_xml;

	    if($nombre_file_extension != "xml")
	    {
		    $sourceProperties = getimagesize($filename);
		    $uploadImageType = $sourceProperties[2];
		    $sourceImageWith = $sourceProperties[0];
		    $sourceImageHeight = $sourceProperties[1];

		    switch ($uploadImageType) 
		    {
		        case IMAGETYPE_JPEG:
		            $resourceType = imagecreatefromjpeg($filename);
		            $imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);
		            $file = imagejpeg($imageLayer[0], $path . $nombre_file_xml);
		            break;
		        case IMAGETYPE_PNG:
		            $resourceType = imagecreatefrompng($filename);
		            $imageLayer = resizeImage($resourceType, $sourceImageWith, $sourceImageHeight);
		            $file = imagepng($imageLayer[0], $path . $nombre_file_xml);
		            break;
		        default:
		            break;
		    }
		}
        else
        {
        	$file = $_FILES['nombre_file_xml']['tmp_name'];
        }

	    move_uploaded_file($file, $path . $nombre_file_xml);
	    $update_file = "nombre_file_xml = '{$nombre_file_xml}',extension_xml = '{$nombre_file_extension}',size_xml = '{$nombre_file_size}',ruta_xml = '{$path}',download_file_xml = '{$nombreDownload}',";
	}

	$query_sql = "
        SELECT
            importe
        FROM mepa_detalle_caja_chica_liquidacion
        WHERE id = '".$detalle_id."'
        LIMIT 1
        ";
    $query = $mysqli->query($query_sql);
    $row = $query->fetch_assoc();

    $importe_detalle_anterior = $row["importe"];

    $asignacion_importe_detalle_liquidacion = "UPDATE mepa_asignacion_caja_chica
		SET 				
			saldo_disponible = saldo_disponible + '".$importe_detalle_anterior."'
		WHERE id =  " .$asignacion_id ;

	$mysqli->query($asignacion_importe_detalle_liquidacion);


	$query_liquidacion_detalle = "UPDATE mepa_detalle_caja_chica_liquidacion
		SET 				
			fecha_documento = '".$fecha_documento."',
			tipo_documento = '".$tipo_documento."',
			serie_comprobante = '".strtoupper($serie_comprobante)."',
			num_comprobante = '".strtoupper($num_comprobante)."',
			centro_costo = '".$centro_costo."',
			detalle = '".$detalle."',
			importe = '".$importe."',
			tasa_igv = '".$tasa_igv."',
    		".$update_file."
			user_updated_id = '".$login["id"]."', 
			updated_at = '".date('Y-m-d H:i:s')."'
		WHERE id =  " .$detalle_id ;
	$mysqli->query($query_liquidacion_detalle);

	
	if($mysqli->error)
	{
		$error = $mysqli->error;
	}

	if ($error == '') 
	{
		//UPDATE  total_rendicion  en  CABECERA
		$query_update = "
		UPDATE mepa_caja_chica_liquidacion 
			SET total_rendicion = (SELECT SUM(importe) FROM mepa_detalle_caja_chica_liquidacion WHERE mepa_caja_chica_liquidacion_id = {$mepa_caja_chica_liquidacion_id} )
		WHERE id = ".$mepa_caja_chica_liquidacion_id;
		$mysqli->query($query_update);


		$asignacion_actualizar_saldo_disponible = "UPDATE mepa_asignacion_caja_chica
			SET 				
				saldo_disponible = saldo_disponible - '".$importe."'
			WHERE id =  " .$asignacion_id ;

		$mysqli->query($asignacion_actualizar_saldo_disponible);


		$result["mensaje"] = "Detalle liquidación Actualizado!";
	} 
	else 
	{
		$result["mensaje"] = "No se edito: " .$error;	
	}
	
}

if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_detalle_atencion_liquidacion_concar_excel")
{
	
	$liquidacion_id = $_POST['liquidacion_id'];

	$query_todos = "";
	$row_count_detalle_movilidad = 0;
	$row_count_detalle_liquidacion = 0;
	$suma_monto_total = 0;

	$importe_original = 0;
	$importe_dolares = 0;

	$fecha_dia = "";
	$fecha_mes = "";
	$fecha_anio = "";

	// INICIO SELECT DETALLE LIQUIDACION

	$query_detalle_liquidacion = 
	"
		SELECT
			dl.mepa_caja_chica_liquidacion_id,
		    dl.item,
		    td.cuenta_contable AS cuenta_contable_liquidacion,
		    dl.ruc AS codigo_anexo_liquidacion,
		    'D' AS deber_haber_liquidacion,
		    dl.importe AS importe_original_liquidacion,
			td.codigo AS tipo_documento_liquidacion,
		    dl.fecha_documento,
		    dl.serie_comprobante,
		    dl.num_comprobante,
		    tr.ruc AS empresa_ruc,
    		tr.nombre AS empresa_nombre
		FROM mepa_detalle_caja_chica_liquidacion dl
			INNER JOIN mepa_tipo_documento td
			ON dl.tipo_documento = td.id
			LEFT JOIN tbl_razon_social tr
    		ON dl.ruc = tr.ruc
		where dl.mepa_caja_chica_liquidacion_id = '".$liquidacion_id."' AND dl.status = 1
		ORDER BY dl.item ASC
	";

	$list_query_liquidacion = $mysqli->query($query_detalle_liquidacion);
	$list_query_liquidacion_validacion_ruc = $mysqli->query($query_detalle_liquidacion);

	$row_count_detalle_liquidacion = $list_query_liquidacion->num_rows;
	$row_count_detalle_liquidacion_validacion_ruc = $list_query_liquidacion->num_rows;

	if($row_count_detalle_liquidacion_validacion_ruc > 0)
	{
		while ($row = $list_query_liquidacion_validacion_ruc->fetch_array())
		{
			$codigo_anexo_liquidacion = $row["codigo_anexo_liquidacion"];

			if(is_null($codigo_anexo_liquidacion) OR empty($codigo_anexo_liquidacion))
			{
				echo json_encode(array(
					"ruta_archivo" => "No existe el ruc registrado en el detalle.",
					"estado_archivo" => 0
				));
				exit;
			}
		}
	}

	// FIN SELECT DETALLE LIQUIDACION


	$query_cabecera = 
		"
			SELECT
				l.id,
				l.total_rendicion AS total_rendicion_liquidacion,
				l.id_movilidad,
				m.num_correlativo AS correlativo_movilidad,
			    IFNULL(m.monto_cierre, 0) AS total_monto_cierre_movilidad,
				l.num_correlativo,
				l.numero_comprobante_concar,
				l.fecha_comprobante_concar,
				l.fecha_atencion_contabilidad,
				l.created_at AS fecha_creacion_liquidacion,
				tc.monto_venta AS tipo_cambio,
				'V' AS tipo_conversion,
				'S' AS flag_conversion_moneda,
				tp.dni,
				za.centro_costo AS centro_costo_asignacion,
				zal.centro_costo AS centro_costo_liquidacion,
				'D' AS deber_haber,
			    'CAJA CHICA Nº ' AS glosa,
			    UPPER(concat(IFNULL(SUBSTRING_INDEX(tp.nombre, ' ', 1), ''),' ', IFNULL(tp.apellido_paterno, ''))) AS usuario_solicitante,
				rs.id AS empresa_id,
			    'MN' AS codigo_moneda
			FROM mepa_caja_chica_liquidacion l
				LEFT JOIN mepa_caja_chica_movilidad m
    			ON l.id_movilidad = m.id
				LEFT JOIN tbl_tipo_cambio tc
				ON l.fecha_comprobante_concar = tc.fecha
				INNER JOIN tbl_usuarios tu
				ON l.user_created_id = tu.id
				INNER JOIN tbl_personal_apt tp
				ON tu.personal_id = tp.id
				INNER JOIN mepa_asignacion_caja_chica a
			    ON l.asignacion_id = a.id
			    INNER JOIN mepa_zona_asignacion za
			    ON a.zona_asignacion_id = za.id
			    LEFT JOIN mepa_zona_asignacion zal
			    ON l.asignacion_zona_id = zal.id
				INNER JOIN tbl_razon_social rs
				ON a.empresa_id = rs.id		
			WHERE l.id = '".$liquidacion_id."'
		";
	
	$list_query_cabecera = $mysqli->query($query_cabecera);

	$row_count_cabecera = $list_query_cabecera->num_rows;

	if($row_count_cabecera > 0)
	{
		$reg = $list_query_cabecera->fetch_assoc();
		$total_rendicion_liquidacion = $reg["total_rendicion_liquidacion"];
		$id_movilidad = $reg["id_movilidad"];
		$correlativo_movilidad = $reg["correlativo_movilidad"];
		$total_monto_cierre_movilidad = $reg["total_monto_cierre_movilidad"];
		$num_correlativo = $reg["num_correlativo"];
		$numero_comprobante_concar = $reg["numero_comprobante_concar"];
		$fecha_comprobante_concar = $reg["fecha_comprobante_concar"];
		$fecha_atencion_contabilidad = $reg["fecha_atencion_contabilidad"];
		$fecha_creacion_liquidacion = $reg["fecha_creacion_liquidacion"];
		$tipo_cambio = $reg["tipo_cambio"];
		$tipo_conversion = $reg["tipo_conversion"];
		$flag_conversion_moneda = $reg["flag_conversion_moneda"];
		$dni = $reg["dni"];
		$centro_costo_asignacion = $reg["centro_costo_asignacion"];
		$centro_costo_liquidacion = $reg["centro_costo_liquidacion"];
		$deber_haber = $reg["deber_haber"];
		$glosa = $reg["glosa"];
		$usuario_solicitante = $reg["usuario_solicitante"];
		$codigo_moneda = $reg["codigo_moneda"];
		$empresa_id = $reg["empresa_id"];

		if(is_null($tipo_cambio) OR empty($tipo_cambio))
		{
			echo json_encode(array(
				"ruta_archivo" => "No existe el tipo de cambio de la fecha ".$fecha_comprobante_concar,
				"estado_archivo" => 0
			));
			exit;
		}

		if($centro_costo_liquidacion != null)
		{
			// CECO DEL CAMPO asignacion_zona_id DE LA TABLA mepa_caja_chica_liquidacion
			$centro_costo_cabecera = $centro_costo_liquidacion;
		}
		else
		{
			// CECO DEL CAMPO zona_asignacion_id DE LA TABLA mepa_asignacion_caja_chica
			$centro_costo_cabecera = $centro_costo_asignacion;
		}

		$suma_monto_total = $total_rendicion_liquidacion + $total_monto_cierre_movilidad;
		$fecha_anio = date('Y', strtotime($fecha_comprobante_concar));
		$fecha_mes = date('m', strtotime($fecha_comprobante_concar));

		$fecha_creacion_liquidacion_anio = date('Y', strtotime($fecha_creacion_liquidacion));
		$fecha_creacion_liquidacion_mes = date('m', strtotime($fecha_creacion_liquidacion));
	}
	else
	{
		echo json_encode(array(
			"ruta_archivo" => "Caja Chica no existente.",
			"estado_archivo" => 0
		));
		exit;
	}

	if(!is_null($id_movilidad) OR !empty($id_movilidad))
	{
		$query_detalle_movilidad = 
		"
			SELECT
				md.fecha,
			    SUM(md.monto) AS monto,
			    '622301' AS cuenta_contable_movilidad,
			    'MV' AS tipo_documento_movilidad,
			    md.fecha AS numero_documento_movilidad
			FROM mepa_caja_chica_movilidad_detalle  md
			WHERE md.id_mepa_caja_chica_movilidad = '".$id_movilidad."' AND md.estado = 1
			GROUP BY md.fecha
			ORDER BY md.fecha ASC
		";
	
		$list_query_movildad = $mysqli->query($query_detalle_movilidad);

		$row_count_detalle_movilidad = $list_query_movildad->num_rows;
	}


	//PROCEDEMOS A CREAR LA CARPETA SI EN CASO NO EXISTA
	$path = "/var/www/html/files_bucket/mepa/descargas/contabilidad/concar_liquidacion/";

	if (!is_dir($path)) 
	{
		mkdir($path, 0777, true);
	}

	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/mepa/descargas/contabilidad/concar_liquidacion/*'); //obtenemos todos los nombres de los ficheros
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

	$titulosColumnas = array('Campo', 'Sub Diario', 'Número de Comprobante', 'Fecha de Comprobante', 'Código de Moneda', 'Glosa Principal', 'Tipo de Cambio', 'Tipo de Conversión', 'Flag de Conversión de Moneda', 'Fecha Tipo de Cambio', 'Cuenta Contable', 'Código de Anexo', 'Código de Centro de Costo', 'Debe / Haber', 'Importe Original', 'Importe en Dólares', 'Importe en Soles', 'Tipo de Documento', 'Número de Documento', 'Fecha de Documento', 'Fecha de Vencimiento', 'Código de Area', 'Glosa Detalle', 'Código de Anexo Auxiliar', 'Medio de Pago', 'Tipo de Documento de Referencia', 'Número de Documento Referencia', 'Fecha Documento Referencia', 'Nro Máq. Registradora Tipo Doc. Ref.', 'Base Imponible Documento Referencia', 'IGV Documento Provisión', 'Tipo Referencia en estado MQ', 'Número Serie Caja Registradora', 'Fecha de Operación', 'Tipo de Tasa', 'Tasa Detracción/Percepción', 'Importe Base Detracción/Percepción Dólares', 'Importe Base Detracción/Percepción Soles', 'Tipo Cambio para F', 'Importe de IGV sin derecho credito fiscal', 'Tasa IGV');

	$titulosColumnas_dos = array('Restricciones', 'Ver T.G. 02', '', '', 'Ver T.G. 03', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');

	$titulosColumnas_tres = array('Tamaño/Formato', '4 Caracteres', '6 Caracteres', 'dd/mm/aaaa', '2 Caracteres', '40 Caracteres', 'Numérico 11,6', '1 Caracteres', '1 Caracteres', 'dd/mm/aaaa', '12 Caracteres', '18 Caracteres', '6 Caracteres', '1 Carácter', 'Numérico 14,2', 'Numérico 14,2', 'Numérico 14,2', '2 Caracteres', '20 Caracteres', 'dd/mm/aaaa', 'dd/mm/aaaa', '3 Caracteres', '30 Caracteres', '18 Caracteres', '8 Caracteres', '2 Caracteres', '20 Caracteres', 'dd/mm/aaaa', '20 Caracteres', 'Numérico 14,2', 'Numérico 14,2', 'MQ', '15 caracteres', 'dd/mm/aaaa', '5 Caracteres', 'Numérico 14,2', 'Numérico 14,2', 'Numérico 14,2', '1 Caracter', 'Numérico 14,2', 'Numérico 14,2');


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
			    ->setCellValue('K1', $titulosColumnas[10])
			    ->setCellValue('L1', $titulosColumnas[11])
			    ->setCellValue('M1', $titulosColumnas[12])
			    ->setCellValue('N1', $titulosColumnas[13])
			    ->setCellValue('O1', $titulosColumnas[14])
			    ->setCellValue('P1', $titulosColumnas[15])
			    ->setCellValue('Q1', $titulosColumnas[16])
			    ->setCellValue('R1', $titulosColumnas[17])
			    ->setCellValue('S1', $titulosColumnas[18])
			    ->setCellValue('T1', $titulosColumnas[19])
			    ->setCellValue('U1', $titulosColumnas[20])
			    ->setCellValue('V1', $titulosColumnas[21])
			    ->setCellValue('W1', $titulosColumnas[22])
			    ->setCellValue('X1', $titulosColumnas[23])
			    ->setCellValue('Y1', $titulosColumnas[24])
			    ->setCellValue('Z1', $titulosColumnas[25])
			    ->setCellValue('AA1', $titulosColumnas[26])
			    ->setCellValue('AB1', $titulosColumnas[27])
			    ->setCellValue('AC1', $titulosColumnas[28])
			    ->setCellValue('AD1', $titulosColumnas[29])
			    ->setCellValue('AE1', $titulosColumnas[30])
			    ->setCellValue('AF1', $titulosColumnas[31])
			    ->setCellValue('AG1', $titulosColumnas[32])
			    ->setCellValue('AH1', $titulosColumnas[33])
			    ->setCellValue('AI1', $titulosColumnas[34])
			    ->setCellValue('AJ1', $titulosColumnas[35])
			    ->setCellValue('AK1', $titulosColumnas[36])
			    ->setCellValue('AL1', $titulosColumnas[37])
			    ->setCellValue('AM1', $titulosColumnas[38])
			    ->setCellValue('AN1', $titulosColumnas[39])
			    ->setCellValue('AO1', $titulosColumnas[40])
			    ->setCellValue('A2', $titulosColumnas_dos[0])  //Titulo de las columnas
			    ->setCellValue('B2', $titulosColumnas_dos[1])
			    ->setCellValue('C2', $titulosColumnas_dos[2])
			    ->setCellValue('D2', $titulosColumnas_dos[3])
			    ->setCellValue('E2', $titulosColumnas_dos[4])
			    ->setCellValue('F2', $titulosColumnas_dos[5])
			    ->setCellValue('G2', $titulosColumnas_dos[6])
			    ->setCellValue('H2', $titulosColumnas_dos[7])
			    ->setCellValue('I2', $titulosColumnas_dos[8])
			    ->setCellValue('J2', $titulosColumnas_dos[9])
			    ->setCellValue('K2', $titulosColumnas_dos[10])
			    ->setCellValue('L2', $titulosColumnas_dos[11])
			    ->setCellValue('M2', $titulosColumnas_dos[12])
			    ->setCellValue('N2', $titulosColumnas_dos[13])
			    ->setCellValue('O2', $titulosColumnas_dos[14])
			    ->setCellValue('P2', $titulosColumnas_dos[15])
			    ->setCellValue('Q2', $titulosColumnas_dos[16])
			    ->setCellValue('R2', $titulosColumnas_dos[17])
			    ->setCellValue('S2', $titulosColumnas_dos[18])
			    ->setCellValue('T2', $titulosColumnas_dos[19])
			    ->setCellValue('U2', $titulosColumnas_dos[20])
			    ->setCellValue('V2', $titulosColumnas_dos[21])
			    ->setCellValue('W2', $titulosColumnas_dos[22])
			    ->setCellValue('X2', $titulosColumnas_dos[23])
			    ->setCellValue('Y2', $titulosColumnas_dos[24])
			    ->setCellValue('Z2', $titulosColumnas_dos[25])
			    ->setCellValue('AA2', $titulosColumnas_dos[26])
			    ->setCellValue('AB2', $titulosColumnas_dos[27])
			    ->setCellValue('AC2', $titulosColumnas_dos[28])
			    ->setCellValue('AD2', $titulosColumnas_dos[29])
			    ->setCellValue('AE2', $titulosColumnas_dos[30])
			    ->setCellValue('AF2', $titulosColumnas_dos[31])
			    ->setCellValue('AG2', $titulosColumnas_dos[32])
			    ->setCellValue('AH2', $titulosColumnas_dos[33])
			    ->setCellValue('AI2', $titulosColumnas_dos[34])
			    ->setCellValue('AJ2', $titulosColumnas_dos[35])
			    ->setCellValue('AK2', $titulosColumnas_dos[36])
			    ->setCellValue('AL2', $titulosColumnas_dos[37])
			    ->setCellValue('AM2', $titulosColumnas_dos[38])
			    ->setCellValue('AN2', $titulosColumnas_dos[39])
			    ->setCellValue('AO2', $titulosColumnas_dos[40])
			    ->setCellValue('A3', $titulosColumnas_tres[0])  //Titulo de las columnas
			    ->setCellValue('B3', $titulosColumnas_tres[1])
			    ->setCellValue('C3', $titulosColumnas_tres[2])
			    ->setCellValue('D3', $titulosColumnas_tres[3])
			    ->setCellValue('E3', $titulosColumnas_tres[4])
			    ->setCellValue('F3', $titulosColumnas_tres[5])
			    ->setCellValue('G3', $titulosColumnas_tres[6])
			    ->setCellValue('H3', $titulosColumnas_tres[7])
			    ->setCellValue('I3', $titulosColumnas_tres[8])
			    ->setCellValue('J3', $titulosColumnas_tres[9])
			    ->setCellValue('K3', $titulosColumnas_tres[10])
			    ->setCellValue('L3', $titulosColumnas_tres[11])
			    ->setCellValue('M3', $titulosColumnas_tres[12])
			    ->setCellValue('N3', $titulosColumnas_tres[13])
			    ->setCellValue('O3', $titulosColumnas_tres[14])
			    ->setCellValue('P3', $titulosColumnas_tres[15])
			    ->setCellValue('Q3', $titulosColumnas_tres[16])
			    ->setCellValue('R3', $titulosColumnas_tres[17])
			    ->setCellValue('S3', $titulosColumnas_tres[18])
			    ->setCellValue('T3', $titulosColumnas_tres[19])
			    ->setCellValue('U3', $titulosColumnas_tres[20])
			    ->setCellValue('V3', $titulosColumnas_tres[21])
			    ->setCellValue('W3', $titulosColumnas_tres[22])
			    ->setCellValue('X3', $titulosColumnas_tres[23])
			    ->setCellValue('Y3', $titulosColumnas_tres[24])
			    ->setCellValue('Z3', $titulosColumnas_tres[25])
			    ->setCellValue('AA3', $titulosColumnas_tres[26])
			    ->setCellValue('AB3', $titulosColumnas_tres[27])
			    ->setCellValue('AC3', $titulosColumnas_tres[28])
			    ->setCellValue('AD3', $titulosColumnas_tres[29])
			    ->setCellValue('AE3', $titulosColumnas_tres[30])
			    ->setCellValue('AF3', $titulosColumnas_tres[31])
			    ->setCellValue('AG3', $titulosColumnas_tres[32])
			    ->setCellValue('AH3', $titulosColumnas_tres[33])
			    ->setCellValue('AI3', $titulosColumnas_tres[34])
			    ->setCellValue('AJ3', $titulosColumnas_tres[35])
			    ->setCellValue('AK3', $titulosColumnas_tres[36])
			    ->setCellValue('AL3', $titulosColumnas_tres[37])
			    ->setCellValue('AM3', $titulosColumnas_tres[38])
			    ->setCellValue('AN3', $titulosColumnas_tres[39])
			    ->setCellValue('AO3', $titulosColumnas_tres[40]);

	//Se agregan los datos a la lista del reporte
    $i = 4; //Numero de fila donde se va a comenzar a rellenar

	$monto_maximo_concar = 41;
	$monto_restante = 0;
	$var_indice_movilidad = 0;

	if($empresa_id==15 || $empresa_id==11 || $empresa_id==30)
	{
		$consulta_subdiario = $mysqli->query("SELECT subdiario_cancelacion_caja_chica 
												FROM tbl_razon_social 
												WHERE id= ".$empresa_id."")->fetch_assoc();

    	$subdiario_cancelacion_caja_chica = $consulta_subdiario["subdiario_cancelacion_caja_chica"];
		$subdiario_cancelacion_caja_chica = str_pad($subdiario_cancelacion_caja_chica, 4, "0", STR_PAD_LEFT);

		$sub_diario = $subdiario_cancelacion_caja_chica;
	}
	else
	{
		$sub_diario = '0220';
	}

	// INICIO DETALLE MOVILIDAD - SI EXISTE MOVILIDAD
	if ($row_count_detalle_movilidad > 0)
	{
		while ($row = $list_query_movildad->fetch_array()) 
		{
			
			$movilidad_fecha = $row["fecha"];
    		$movilidad_monto = $row["monto"];
    		$cuenta_contable_movilidad = $row["cuenta_contable_movilidad"];
    		$tipo_documento_movilidad = $row["tipo_documento_movilidad"];
    		$numero_documento_movilidad = $row["numero_documento_movilidad"];

    		$fecha_movilidad_anio = date('y', strtotime($numero_documento_movilidad));
			$fecha_movilidad_mes = date('m', strtotime($numero_documento_movilidad));
			$fecha_movilidad_dia = date('d', strtotime($numero_documento_movilidad));

			//$numero_documento_movilidad = $fecha_movilidad_dia.$fecha_movilidad_mes.$fecha_movilidad_anio;

			$numero_documento_movilidad = substr($correlativo_movilidad, 6, 9);

    		$monto_restante = 0;
    		$var_indice_movilidad = 1;

    		if($movilidad_monto > $monto_maximo_concar)
    		{
    			$monto_restante = $movilidad_monto - $monto_maximo_concar;

    			$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue('B'.$i, substr($sub_diario, 0, 4))
							->setCellValue('C'.$i, substr($fecha_mes.str_pad($numero_comprobante_concar, 4, "0", STR_PAD_LEFT), 0, 6))
							->setCellValue('D'.$i, date('d/m/Y', strtotime($fecha_comprobante_concar)))
							->setCellValue('E'.$i, substr($codigo_moneda, 0, 2))
							->setCellValue('F'.$i, substr($glosa.str_pad($num_correlativo, 3, "0", STR_PAD_LEFT).' '.$usuario_solicitante, 0, 40))
							->setCellValue('G'.$i, $tipo_cambio)
							->setCellValue('H'.$i, substr($tipo_conversion, 0, 1))
							->setCellValue('I'.$i, substr($flag_conversion_moneda, 0, 1))
							->setCellValue('J'.$i, date('d/m/Y', strtotime($fecha_comprobante_concar)))
							->setCellValue('K'.$i, substr($cuenta_contable_movilidad, 0, 12))
							->setCellValue('L'.$i, substr($dni, 0, 18))
							->setCellValue('M'.$i, substr($centro_costo_cabecera, 0, 6))
							->setCellValue('N'.$i, substr($deber_haber, 0, 1))
							->setCellValue('O'.$i, $monto_maximo_concar)
							->setCellValue('P'.$i, '')
							->setCellValue('Q'.$i, '')
							->setCellValue('R'.$i, substr($tipo_documento_movilidad, 0, 2))
							->setCellValue('S'.$i, substr($numero_documento_movilidad, 0, 20))
							->setCellValue('T'.$i, date('d/m/Y', strtotime($movilidad_fecha)))
							->setCellValue('U'.$i, date('d/m/Y', strtotime($movilidad_fecha)))
							->setCellValue('V'.$i, substr('', 0, 3))
							->setCellValue('W'.$i, substr('MOVILIDAD '.$usuario_solicitante, 0, 30))
							->setCellValue('X'.$i, substr('0'.$var_indice_movilidad++, 0, 18))
							->setCellValue('Y'.$i, substr('', 0, 8))
							->setCellValue('Z'.$i, substr('', 0, 2))
							->setCellValue('AA'.$i, substr('', 0, 20))
							->setCellValue('AB'.$i, '')
							->setCellValue('AC'.$i, substr('', 0, 20))
							->setCellValue('AD'.$i, '')
							->setCellValue('AE'.$i, '')
							->setCellValue('AF'.$i, '')
							->setCellValue('AG'.$i, substr('', 0, 15))
							->setCellValue('AH'.$i, '')
							->setCellValue('AI'.$i, substr('', 0, 5))
							->setCellValue('AJ'.$i, '')
							->setCellValue('AK'.$i, '')
							->setCellValue('AL'.$i, '')
							->setCellValue('AM'.$i, substr('', 0, 1))
							->setCellValue('AN'.$i, '')
							->setCellValue('AO'.$i, '');
				
				$i++;

				// INSERTAR LO QUE RESTA EN EL MONTO DE MOVILIDAD

				$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue('B'.$i, substr($sub_diario, 0, 4))
							->setCellValue('C'.$i, substr($fecha_mes.str_pad($numero_comprobante_concar, 4, "0", STR_PAD_LEFT), 0, 6))
							->setCellValue('D'.$i, date('d/m/Y', strtotime($fecha_comprobante_concar)))
							->setCellValue('E'.$i, substr($codigo_moneda, 0, 2))
							->setCellValue('F'.$i, substr($glosa.str_pad($num_correlativo, 3, "0", STR_PAD_LEFT).' '.$usuario_solicitante, 0, 40))
							->setCellValue('G'.$i, $tipo_cambio)
							->setCellValue('H'.$i, substr($tipo_conversion, 0, 1))
							->setCellValue('I'.$i, substr($flag_conversion_moneda, 0, 1))
							->setCellValue('J'.$i, date('d/m/Y', strtotime($fecha_comprobante_concar)))
							->setCellValue('K'.$i, substr($cuenta_contable_movilidad, 0, 12))
							->setCellValue('L'.$i, substr($dni, 0, 18))
							->setCellValue('M'.$i, substr($centro_costo_cabecera, 0, 6))
							->setCellValue('N'.$i, substr($deber_haber, 0, 1))
							->setCellValue('O'.$i, $monto_restante)
							->setCellValue('P'.$i, '')
							->setCellValue('Q'.$i, '')
							->setCellValue('R'.$i, substr($tipo_documento_movilidad, 0, 2))
							->setCellValue('S'.$i, substr($numero_documento_movilidad, 0, 20))
							->setCellValue('T'.$i, date('d/m/Y', strtotime($movilidad_fecha)))
							->setCellValue('U'.$i, date('d/m/Y', strtotime($movilidad_fecha)))
							->setCellValue('V'.$i, substr('', 0, 3))
							->setCellValue('W'.$i, substr('MOVILIDAD '.$usuario_solicitante, 0, 30))
							->setCellValue('X'.$i, substr('0'.$var_indice_movilidad++, 0, 18))
							->setCellValue('Y'.$i, substr('', 0, 8))
							->setCellValue('Z'.$i, substr('', 0, 2))
							->setCellValue('AA'.$i, substr('', 0, 20))
							->setCellValue('AB'.$i, '')
							->setCellValue('AC'.$i, substr('', 0, 20))
							->setCellValue('AD'.$i, '')
							->setCellValue('AE'.$i, '')
							->setCellValue('AF'.$i, '')
							->setCellValue('AG'.$i, substr('', 0, 15))
							->setCellValue('AH'.$i, '')
							->setCellValue('AI'.$i, substr('', 0, 5))
							->setCellValue('AJ'.$i, '')
							->setCellValue('AK'.$i, '')
							->setCellValue('AL'.$i, '')
							->setCellValue('AM'.$i, substr('', 0, 1))
							->setCellValue('AN'.$i, '')
							->setCellValue('AO'.$i, '');

    		}
    		else
    		{
    			// EL MONTO MOVILIDAD NO ES MAYOR AL MONTO MAXIMO, ENTONCES SE PROCEDE A REGISTRAR LO QUE ES EL MONTO

    			$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue('B'.$i, substr($sub_diario, 0, 4))
							->setCellValue('C'.$i, substr($fecha_mes.str_pad($numero_comprobante_concar, 4, "0", STR_PAD_LEFT), 0, 6))
							->setCellValue('D'.$i, date('d/m/Y', strtotime($fecha_comprobante_concar)))
							->setCellValue('E'.$i, substr($codigo_moneda, 0, 2))
							->setCellValue('F'.$i, substr($glosa.str_pad($num_correlativo, 3, "0", STR_PAD_LEFT).' '.$usuario_solicitante, 0, 40))
							->setCellValue('G'.$i, $tipo_cambio)
							->setCellValue('H'.$i, substr($tipo_conversion, 0, 1))
							->setCellValue('I'.$i, substr($flag_conversion_moneda, 0, 1))
							->setCellValue('J'.$i, date('d/m/Y', strtotime($fecha_comprobante_concar)))
							->setCellValue('K'.$i, substr($cuenta_contable_movilidad, 0, 12))
							->setCellValue('L'.$i, substr($dni, 0, 18))
							->setCellValue('M'.$i, substr($centro_costo_cabecera, 0, 6))
							->setCellValue('N'.$i, substr($deber_haber, 0, 1))
							->setCellValue('O'.$i, $movilidad_monto)
							->setCellValue('P'.$i, '')
							->setCellValue('Q'.$i, '')
							->setCellValue('R'.$i, substr($tipo_documento_movilidad, 0, 2))
							->setCellValue('S'.$i, substr($numero_documento_movilidad, 0, 20))
							->setCellValue('T'.$i, date('d/m/Y', strtotime($movilidad_fecha)))
							->setCellValue('U'.$i, date('d/m/Y', strtotime($movilidad_fecha)))
							->setCellValue('V'.$i, substr('', 0, 3))
							->setCellValue('W'.$i, substr('MOVILIDAD '.$usuario_solicitante, 0, 30))
							->setCellValue('X'.$i, substr('0'.$var_indice_movilidad++, 0, 18))
							->setCellValue('Y'.$i, substr('', 0, 8))
							->setCellValue('Z'.$i, substr('', 0, 2))
							->setCellValue('AA'.$i, substr('', 0, 20))
							->setCellValue('AB'.$i, '')
							->setCellValue('AC'.$i, substr('', 0, 20))
							->setCellValue('AD'.$i, '')
							->setCellValue('AE'.$i, '')
							->setCellValue('AF'.$i, '')
							->setCellValue('AG'.$i, substr('', 0, 15))
							->setCellValue('AH'.$i, '')
							->setCellValue('AI'.$i, substr('', 0, 5))
							->setCellValue('AJ'.$i, '')
							->setCellValue('AK'.$i, '')
							->setCellValue('AL'.$i, '')
							->setCellValue('AM'.$i, substr('', 0, 1))
							->setCellValue('AN'.$i, '')
							->setCellValue('AO'.$i, '');
    		}

    		$i++;
		}
	}

	// FIN DETALLE MOVILIDAD - SI EXISTE MOVILIDAD

	// INICIO DETALLE LIQUIDACION
	if($row_count_detalle_liquidacion > 0)
	{
		while ($row = $list_query_liquidacion->fetch_array())
		{
			$cuenta_contable_liquidacion = $row["cuenta_contable_liquidacion"];
    		$codigo_anexo_liquidacion = $row["codigo_anexo_liquidacion"];
    		$deber_haber_liquidacion = $row["deber_haber_liquidacion"];
    		$importe_original_liquidacion = $row["importe_original_liquidacion"];
    		$tipo_documento_liquidacion = $row["tipo_documento_liquidacion"];
    		$fecha_documento = $row["fecha_documento"];
    		$serie_comprobante = $row["serie_comprobante"];
    		$num_comprobante = $row["num_comprobante"];
    		$empresa_ruc = $row["empresa_ruc"];
    		$empresa_nombre = $row["empresa_nombre"];

    		if(!is_null($empresa_ruc) OR !empty($empresa_ruc))
    		{
    			$cuenta_contable_liquidacion = 422201;
    			$tipo_documento_liquidacion = 'AN';

    			$num_comprobante = str_pad($fecha_creacion_liquidacion_mes, 3, "0", STR_PAD_LEFT).'-'.$fecha_creacion_liquidacion_anio;
    		}
    		else
    		{
    			$num_comprobante = $serie_comprobante.'-'.$num_comprobante;
    		}
    		

    		$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue('B'.$i, substr($sub_diario, 0, 4))
							->setCellValue('C'.$i, substr($fecha_mes.str_pad($numero_comprobante_concar, 4, "0", STR_PAD_LEFT), 0, 6))
							->setCellValue('D'.$i, date('d/m/Y', strtotime($fecha_comprobante_concar)))
							->setCellValue('E'.$i, substr($codigo_moneda, 0, 2))
							->setCellValue('F'.$i, substr($glosa.str_pad($num_correlativo, 3, "0", STR_PAD_LEFT).' '.$usuario_solicitante, 0, 40))
							->setCellValue('G'.$i, $tipo_cambio)
							->setCellValue('H'.$i, substr($tipo_conversion, 0, 1))
							->setCellValue('I'.$i, substr($flag_conversion_moneda, 0, 1))
							->setCellValue('J'.$i, date('d/m/Y', strtotime($fecha_comprobante_concar)))
							->setCellValue('K'.$i, substr($cuenta_contable_liquidacion, 0, 12))
							->setCellValue('L'.$i, substr($codigo_anexo_liquidacion, 0, 18))
							->setCellValue('M'.$i, substr('', 0, 6))
							->setCellValue('N'.$i, substr($deber_haber_liquidacion, 0, 1))
							->setCellValue('O'.$i, $importe_original_liquidacion)
							->setCellValue('P'.$i, '')
							->setCellValue('Q'.$i, '')
							->setCellValue('R'.$i, substr($tipo_documento_liquidacion, 0, 2))
							->setCellValue('S'.$i, substr($num_comprobante, 0, 20))
							->setCellValue('T'.$i, date('d/m/Y', strtotime($fecha_documento)))
							->setCellValue('U'.$i, date('d/m/Y', strtotime($fecha_documento)))
							->setCellValue('V'.$i, substr('', 0, 3))
							->setCellValue('W'.$i, substr($glosa.str_pad($num_correlativo, 3, "0", STR_PAD_LEFT).' '.$usuario_solicitante, 0, 30))
							->setCellValue('X'.$i, substr('', 0, 18))
							->setCellValue('Y'.$i, substr('', 0, 8))
							->setCellValue('Z'.$i, substr('', 0, 2))
							->setCellValue('AA'.$i, substr('', 0, 20))
							->setCellValue('AB'.$i, '')
							->setCellValue('AC'.$i, substr('', 0, 20))
							->setCellValue('AD'.$i, '')
							->setCellValue('AE'.$i, '')
							->setCellValue('AF'.$i, '')
							->setCellValue('AG'.$i, substr('', 0, 15))
							->setCellValue('AH'.$i, '')
							->setCellValue('AI'.$i, substr('', 0, 5))
							->setCellValue('AJ'.$i, '')
							->setCellValue('AK'.$i, '')
							->setCellValue('AL'.$i, '')
							->setCellValue('AM'.$i, substr('', 0, 1))
							->setCellValue('AN'.$i, '')
							->setCellValue('AO'.$i, '');

			$i++;
		}
	}

	// FIN DETALLE LIQUIDACION

	// INICIO: ULTIMA FILA SUMA TOTAL DE LA CAJA CHICA

	$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('B'.$i, substr($sub_diario, 0, 4))
				->setCellValue('C'.$i, substr($fecha_mes.str_pad($numero_comprobante_concar, 4, "0", STR_PAD_LEFT), 0, 6))
				->setCellValue('D'.$i, date('d/m/Y', strtotime($fecha_comprobante_concar)))
				->setCellValue('E'.$i, substr($codigo_moneda, 0, 2))
				->setCellValue('F'.$i, substr($glosa.str_pad($num_correlativo, 3, "0", STR_PAD_LEFT).' '.$usuario_solicitante, 0, 40))
				->setCellValue('G'.$i, $tipo_cambio)
				->setCellValue('H'.$i, substr($tipo_conversion, 0, 1))
				->setCellValue('I'.$i, substr($flag_conversion_moneda, 0, 1))
				->setCellValue('J'.$i, date('d/m/Y', strtotime($fecha_comprobante_concar)))
				->setCellValue('K'.$i, substr(102101, 0, 12))
				->setCellValue('L'.$i, substr($dni, 0, 18))
				->setCellValue('M'.$i, substr('', 0, 6))
				->setCellValue('N'.$i, substr('H', 0, 1))
				->setCellValue('O'.$i, $suma_monto_total)
				->setCellValue('P'.$i, '')
				->setCellValue('Q'.$i, '')
				->setCellValue('R'.$i, substr('CJ', 0, 2))
				->setCellValue('S'.$i, substr('SG '.str_pad($num_correlativo, 3, "0", STR_PAD_LEFT).'-'.$fecha_anio, 0, 20))
				->setCellValue('T'.$i, date('d/m/Y', strtotime($fecha_atencion_contabilidad)))
				->setCellValue('U'.$i, date('d/m/Y', strtotime($fecha_atencion_contabilidad)))
				->setCellValue('V'.$i, substr('340', 0, 3))
				->setCellValue('W'.$i, substr($glosa.str_pad($num_correlativo, 3, "0", STR_PAD_LEFT).' '.$usuario_solicitante, 0, 30))
				->setCellValue('X'.$i, substr('', 0, 18))
				->setCellValue('Y'.$i, substr('', 0, 8))
				->setCellValue('Z'.$i, substr('', 0, 2))
				->setCellValue('AA'.$i, substr('', 0, 20))
				->setCellValue('AB'.$i, '')
				->setCellValue('AC'.$i, substr('', 0, 20))
				->setCellValue('AD'.$i, '')
				->setCellValue('AE'.$i, '')
				->setCellValue('AF'.$i, '')
				->setCellValue('AG'.$i, substr('', 0, 15))
				->setCellValue('AH'.$i, '')
				->setCellValue('AI'.$i, substr('', 0, 5))
				->setCellValue('AJ'.$i, '')
				->setCellValue('AK'.$i, '')
				->setCellValue('AL'.$i, '')
				->setCellValue('AM'.$i, substr('', 0, 1))
				->setCellValue('AN'.$i, '')
				->setCellValue('AO'.$i, '');

	$i++;

	// FIN: ULTIMA FILA SUMA TOTAL DE LA CAJA CHICA

	$estiloNombresFilas = array(
		'font' => array(
	        'name'      => 'Calibri',
	        'bold'      => true,
	        'italic'    => false,
	        'strike'    => false,
	        'size' =>11,
	        'color'     => array(
	            'rgb' => '000000'
	        )
	    ),
	    'fill' => array(
			  'type'  => PHPExcel_Style_Fill::FILL_SOLID,
			  'color' => array(
			        'rgb' => 'ffff00')
			),
	    'alignment' =>  array(
	        'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        'wrap'      => false
	    )
	);

	$estiloNombresUltimaFilas = array(
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
			        'rgb' => 'ffff00')
			)
	);


	$estiloColoFondoAmarillo = array(
	    'fill' => array(
	      'type'  => PHPExcel_Style_Fill::FILL_SOLID,
	      'color' => array(
	            'rgb' => 'ffff00')
	  )
	);

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
	$objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(55);
	$objPHPExcel->getActiveSheet()->getRowDimension('3')->setRowHeight(47);


	$objPHPExcel->getActiveSheet()->getStyle('A1:A1')->applyFromArray($estiloNombresFilas);
	$objPHPExcel->getActiveSheet()->getStyle('A2:A2')->applyFromArray($estiloNombresFilas);
	$objPHPExcel->getActiveSheet()->getStyle('A3:A3')->applyFromArray($estiloNombresFilas);

	$objPHPExcel->getActiveSheet()->getStyle('B1:Z1')->applyFromArray($estiloTituloColumnas);
	$objPHPExcel->getActiveSheet()->getStyle('AA1:AO1')->applyFromArray($estiloTituloColumnas);

	$objPHPExcel->getActiveSheet()->getStyle('B1:Z1')->applyFromArray($estiloColoFondoAmarilloOscuro);
	$objPHPExcel->getActiveSheet()->getStyle('AA1:AO1')->applyFromArray($estiloColoFondoAmarilloOscuro);

	$objPHPExcel->getActiveSheet()->getStyle('B2:Z2')->applyFromArray($estiloTituloColumnas);
	$objPHPExcel->getActiveSheet()->getStyle('AA2:AO2')->applyFromArray($estiloTituloColumnas);

	$objPHPExcel->getActiveSheet()->getStyle('B3:Z3')->applyFromArray($estiloTituloColumnas);
	$objPHPExcel->getActiveSheet()->getStyle('AA3:AO3')->applyFromArray($estiloTituloColumnas);
	
	$objPHPExcel->getActiveSheet()->getStyle('B3:Z3')->applyFromArray($estiloColoFondoAmarillo);
	$objPHPExcel->getActiveSheet()->getStyle('AA3:AO3')->applyFromArray($estiloColoFondoAmarillo);

	
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A4:AO".($i-1));


	// INICIO SOMBREAR LA FILA CON EL REGISTRO DE LA TOTALIDAD DEL CONCAR
	$objPHPExcel->getActiveSheet()->getStyle('A'.($i-1).':Z'.($i-1))->applyFromArray($estiloNombresUltimaFilas);
	$objPHPExcel->getActiveSheet()->getStyle('AA'.($i-1).':AO'.($i-1))->applyFromArray($estiloNombresUltimaFilas);
	// FIN SOMBREAR LA FILA CON EL REGISTRO DE LA TOTALIDAD DEL CONCAR

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
	$excel_path = '/var/www/html/files_bucket/mepa/descargas/contabilidad/concar_liquidacion/Plantilla Concar Reembolso Cajas Chicas.xls';
	$excel_path_download = '/files_bucket/mepa/descargas/contabilidad/concar_liquidacion/Plantilla Concar Reembolso Cajas Chicas.xls';

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

if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_detalle_atencion_liquidacion_concar_provision_gastos_excel")
{
	
	$liquidacion_id = $_POST['liquidacion_id'];

	$query_todos = "";
	$row_count_detalle_movilidad = 0;
	$row_count_detalle_liquidacion = 0;
	$suma_monto_total = 0;

	$importe_original = 0;
	$importe_dolares = 0;

	$fecha_dia = "";
	$fecha_mes = "";
	$fecha_anio = "";

	// INICIO SELECT DETALLE LIQUIDACION

	$query_detalle_liquidacion = 
	"
		SELECT
			dl.mepa_caja_chica_liquidacion_id,
		    dl.item,
			rs.id AS empresa_id,
		    UPPER(dl.fecha_documento) AS dt_fecha_documento,
		    UPPER(dl.codigo_provision_contable) AS dt_codigo_provision_contable,
		    -- td.nombre AS dt_cuenta_contable_nombre,
		    UPPER(dl.detalle) AS dt_cuenta_contable_nombre,
		    UPPER(td.cuenta_contable) AS dt_cuenta_contable_codigo,
			tdd.id AS cabecera_cuenta_contable_id,
			tdd.nombre AS cabecera_cuenta_contable_nombre,
			UPPER(tdd.cuenta_contable) AS cabecera_cuenta_contable,
		    UPPER(tc.monto_venta) AS dt_tipo_cambio,
			UPPER('V') AS dt_tipo_conversion,
			UPPER('S') AS dt_flag_conversion_moneda,
		    UPPER(dl.ruc) AS dt_ruc_codigo_anexo,
		    UPPER(dl.centro_costo) AS dt_centro_costo,
		    UPPER('D') AS dt_deber_haber,
		    UPPER(dl.importe) AS dt_importe,
		    UPPER(tdd.codigo) AS dt_tipo_documento_codigo,
		    UPPER(dl.serie_comprobante) AS dt_serie_comprobante,
		    UPPER(dl.num_comprobante) AS dt_num_comprobante,
		    UPPER('01') AS dt_codigo_anexo_auxiliar,
		    UPPER(dl.tasa_igv) AS dt_tasa_igv
		FROM mepa_detalle_caja_chica_liquidacion dl
			INNER JOIN mepa_caja_chica_liquidacion l
			ON l.id = dl.mepa_caja_chica_liquidacion_id
			INNER JOIN mepa_asignacion_caja_chica a
			ON l.asignacion_id = a.id
			INNER JOIN tbl_razon_social rs
			ON a.empresa_id = rs.id	
			INNER JOIN mepa_tipo_documento td
			ON dl.codigo_provision_contable = td.id
			LEFT JOIN tbl_tipo_cambio tc
			ON dl.fecha_documento = tc.fecha
			INNER JOIN mepa_tipo_documento tdd
			ON dl.tipo_documento = tdd.id
		where dl.mepa_caja_chica_liquidacion_id = '".$liquidacion_id."' AND dl.status = 1
			AND dl.ruc NOT IN(SELECT ruc FROM tbl_razon_social WHERE id IN (13, 20, 11))
		ORDER BY dl.item ASC
	";

	$list_query_liquidacion = $mysqli->query($query_detalle_liquidacion);
	$list_query_liquidacion_validacion_ruc = $mysqli->query($query_detalle_liquidacion);

	$row_count_detalle_liquidacion = $list_query_liquidacion->num_rows;
	$row_count_detalle_liquidacion_validacion_ruc = $list_query_liquidacion->num_rows;

	if($row_count_detalle_liquidacion_validacion_ruc > 0)
	{
		while ($row = $list_query_liquidacion_validacion_ruc->fetch_array())
		{
			$dt_ruc_codigo_anexo = $row["dt_ruc_codigo_anexo"];
			$dt_codigo_provision_contable = $row["dt_codigo_provision_contable"];
			$dt_tipo_cambio = $row["dt_tipo_cambio"];
			$dt_fecha_documento = $row["dt_fecha_documento"];
			$empresa_id = $row["empresa_id"];

			if(is_null($dt_ruc_codigo_anexo) OR empty($dt_ruc_codigo_anexo))
			{
				echo json_encode(array(
					"ruta_archivo" => "No existe el ruc registrado en el detalle.",
					"estado_archivo" => 0
				));
				exit;
			}

			if(is_null($dt_codigo_provision_contable) OR empty($dt_codigo_provision_contable))
			{
				echo json_encode(array(
					"ruta_archivo" => "Tiene que registrar el código de provisión en el detalle.",
					"estado_archivo" => 0
				));
				exit;
			}

			if(is_null($dt_tipo_cambio) OR empty($dt_tipo_cambio))
			{
				echo json_encode(array(
					"ruta_archivo" => "No existe el tipo de cambio de la fecha ".$dt_fecha_documento,
					"estado_archivo" => 0
				));
				exit;
			}
		}
	}

	// FIN SELECT DETALLE LIQUIDACION


	$query_cabecera = 
		"
			SELECT
				l.id,
				l.numero_comprobante_concar AS correlativo_concar_cabecera,
				UPPER(concat(IFNULL(SUBSTRING_INDEX(tp.nombre, ' ', 1), ''),' ', IFNULL(tp.apellido_paterno, ''))) AS usuario_solicitante_cabecera,
				'H' AS deber_haber_cabecera,
				'MN' AS codigo_moneda_cabecera,
				'421201' AS cuenta_contable_cabecera
			FROM mepa_caja_chica_liquidacion l
				INNER JOIN tbl_usuarios tu
				ON l.user_created_id = tu.id
				INNER JOIN tbl_personal_apt tp
				ON tu.personal_id = tp.id
			WHERE l.id = '".$liquidacion_id."'
		";
	
	$list_query_cabecera = $mysqli->query($query_cabecera);

	$row_count_cabecera = $list_query_cabecera->num_rows;

	if($row_count_cabecera > 0)
	{
		$reg = $list_query_cabecera->fetch_assoc();
		$id = $reg["id"];
		$correlativo_concar_cabecera = $reg["correlativo_concar_cabecera"];
		$usuario_solicitante_cabecera = $reg["usuario_solicitante_cabecera"];
		$deber_haber_cabecera = $reg["deber_haber_cabecera"];
		$codigo_moneda_cabecera = $reg["codigo_moneda_cabecera"];
		$cuenta_contable_cabecera = $reg["cuenta_contable_cabecera"];

		$fecha_creacion_liquidacion_anio = date('Y', strtotime($fecha_creacion_liquidacion));
		$fecha_creacion_liquidacion_mes = date('m', strtotime($fecha_creacion_liquidacion));
	}
	else
	{
		echo json_encode(array(
			"ruta_archivo" => "Caja Chica no existente.",
			"estado_archivo" => 0
		));
		exit;
	}

	//PROCEDEMOS A CREAR LA CARPETA SI EN CASO NO EXISTA
	$path = "/var/www/html/files_bucket/mepa/descargas/contabilidad/concar_liquidacion/";

	if (!is_dir($path)) 
	{
		mkdir($path, 0777, true);
	}

	//PROCEDEMOS A ELIMINAR TODOS LOS ARCHIVOS QUE ESTEN EN LA CARPETA DESCARGAS
	$files = glob('/var/www/html/files_bucket/mepa/descargas/contabilidad/concar_liquidacion/*'); //obtenemos todos los nombres de los ficheros
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

	$titulosColumnas = array('Campo', 'Sub Diario', 'Número de Comprobante', 'Fecha de Comprobante', 'Código de Moneda', 'Glosa Principal', 'Tipo de Cambio', 'Tipo de Conversión', 'Flag de Conversión de Moneda', 'Fecha Tipo de Cambio', 'Cuenta Contable', 'Código de Anexo', 'Código de Centro de Costo', 'Debe / Haber', 'Importe Original', 'Importe en Dólares', 'Importe en Soles', 'Tipo de Documento', 'Número de Documento', 'Fecha de Documento', 'Fecha de Vencimiento', 'Código de Area', 'Glosa Detalle', 'Código de Anexo Auxiliar', 'Medio de Pago', 'Tipo de Documento de Referencia', 'Número de Documento Referencia', 'Fecha Documento Referencia', 'Nro Máq. Registradora Tipo Doc. Ref.', 'Base Imponible Documento Referencia', 'IGV Documento Provisión', 'Tipo Referencia en estado MQ', 'Número Serie Caja Registradora', 'Fecha de Operación', 'Tipo de Tasa', 'Tasa Detracción/Percepción', 'Importe Base Detracción/Percepción Dólares', 'Importe Base Detracción/Percepción Soles', 'Tipo Cambio para F', 'Importe de IGV sin derecho credito fiscal', 'Tasa IGV');

	$titulosColumnas_dos = array('Restricciones', 'Ver T.G. 02', '', '', 'Ver T.G. 03', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '');

	$titulosColumnas_tres = array('Tamaño/Formato', '4 Caracteres', '6 Caracteres', 'dd/mm/aaaa', '2 Caracteres', '40 Caracteres', 'Numérico 11,6', '1 Caracteres', '1 Caracteres', 'dd/mm/aaaa', '12 Caracteres', '18 Caracteres', '6 Caracteres', '1 Carácter', 'Numérico 14,2', 'Numérico 14,2', 'Numérico 14,2', '2 Caracteres', '20 Caracteres', 'dd/mm/aaaa', 'dd/mm/aaaa', '3 Caracteres', '30 Caracteres', '18 Caracteres', '8 Caracteres', '2 Caracteres', '20 Caracteres', 'dd/mm/aaaa', '20 Caracteres', 'Numérico 14,2', 'Numérico 14,2', 'MQ', '15 caracteres', 'dd/mm/aaaa', '5 Caracteres', 'Numérico 14,2', 'Numérico 14,2', 'Numérico 14,2', '1 Caracter', 'Numérico 14,2', 'Numérico 14,2');


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
			    ->setCellValue('K1', $titulosColumnas[10])
			    ->setCellValue('L1', $titulosColumnas[11])
			    ->setCellValue('M1', $titulosColumnas[12])
			    ->setCellValue('N1', $titulosColumnas[13])
			    ->setCellValue('O1', $titulosColumnas[14])
			    ->setCellValue('P1', $titulosColumnas[15])
			    ->setCellValue('Q1', $titulosColumnas[16])
			    ->setCellValue('R1', $titulosColumnas[17])
			    ->setCellValue('S1', $titulosColumnas[18])
			    ->setCellValue('T1', $titulosColumnas[19])
			    ->setCellValue('U1', $titulosColumnas[20])
			    ->setCellValue('V1', $titulosColumnas[21])
			    ->setCellValue('W1', $titulosColumnas[22])
			    ->setCellValue('X1', $titulosColumnas[23])
			    ->setCellValue('Y1', $titulosColumnas[24])
			    ->setCellValue('Z1', $titulosColumnas[25])
			    ->setCellValue('AA1', $titulosColumnas[26])
			    ->setCellValue('AB1', $titulosColumnas[27])
			    ->setCellValue('AC1', $titulosColumnas[28])
			    ->setCellValue('AD1', $titulosColumnas[29])
			    ->setCellValue('AE1', $titulosColumnas[30])
			    ->setCellValue('AF1', $titulosColumnas[31])
			    ->setCellValue('AG1', $titulosColumnas[32])
			    ->setCellValue('AH1', $titulosColumnas[33])
			    ->setCellValue('AI1', $titulosColumnas[34])
			    ->setCellValue('AJ1', $titulosColumnas[35])
			    ->setCellValue('AK1', $titulosColumnas[36])
			    ->setCellValue('AL1', $titulosColumnas[37])
			    ->setCellValue('AM1', $titulosColumnas[38])
			    ->setCellValue('AN1', $titulosColumnas[39])
			    ->setCellValue('AO1', $titulosColumnas[40])
			    ->setCellValue('A2', $titulosColumnas_dos[0])  //Titulo de las columnas
			    ->setCellValue('B2', $titulosColumnas_dos[1])
			    ->setCellValue('C2', $titulosColumnas_dos[2])
			    ->setCellValue('D2', $titulosColumnas_dos[3])
			    ->setCellValue('E2', $titulosColumnas_dos[4])
			    ->setCellValue('F2', $titulosColumnas_dos[5])
			    ->setCellValue('G2', $titulosColumnas_dos[6])
			    ->setCellValue('H2', $titulosColumnas_dos[7])
			    ->setCellValue('I2', $titulosColumnas_dos[8])
			    ->setCellValue('J2', $titulosColumnas_dos[9])
			    ->setCellValue('K2', $titulosColumnas_dos[10])
			    ->setCellValue('L2', $titulosColumnas_dos[11])
			    ->setCellValue('M2', $titulosColumnas_dos[12])
			    ->setCellValue('N2', $titulosColumnas_dos[13])
			    ->setCellValue('O2', $titulosColumnas_dos[14])
			    ->setCellValue('P2', $titulosColumnas_dos[15])
			    ->setCellValue('Q2', $titulosColumnas_dos[16])
			    ->setCellValue('R2', $titulosColumnas_dos[17])
			    ->setCellValue('S2', $titulosColumnas_dos[18])
			    ->setCellValue('T2', $titulosColumnas_dos[19])
			    ->setCellValue('U2', $titulosColumnas_dos[20])
			    ->setCellValue('V2', $titulosColumnas_dos[21])
			    ->setCellValue('W2', $titulosColumnas_dos[22])
			    ->setCellValue('X2', $titulosColumnas_dos[23])
			    ->setCellValue('Y2', $titulosColumnas_dos[24])
			    ->setCellValue('Z2', $titulosColumnas_dos[25])
			    ->setCellValue('AA2', $titulosColumnas_dos[26])
			    ->setCellValue('AB2', $titulosColumnas_dos[27])
			    ->setCellValue('AC2', $titulosColumnas_dos[28])
			    ->setCellValue('AD2', $titulosColumnas_dos[29])
			    ->setCellValue('AE2', $titulosColumnas_dos[30])
			    ->setCellValue('AF2', $titulosColumnas_dos[31])
			    ->setCellValue('AG2', $titulosColumnas_dos[32])
			    ->setCellValue('AH2', $titulosColumnas_dos[33])
			    ->setCellValue('AI2', $titulosColumnas_dos[34])
			    ->setCellValue('AJ2', $titulosColumnas_dos[35])
			    ->setCellValue('AK2', $titulosColumnas_dos[36])
			    ->setCellValue('AL2', $titulosColumnas_dos[37])
			    ->setCellValue('AM2', $titulosColumnas_dos[38])
			    ->setCellValue('AN2', $titulosColumnas_dos[39])
			    ->setCellValue('A3', $titulosColumnas_tres[0])  //Titulo de las columnas
			    ->setCellValue('B3', $titulosColumnas_tres[1])
			    ->setCellValue('C3', $titulosColumnas_tres[2])
			    ->setCellValue('D3', $titulosColumnas_tres[3])
			    ->setCellValue('E3', $titulosColumnas_tres[4])
			    ->setCellValue('F3', $titulosColumnas_tres[5])
			    ->setCellValue('G3', $titulosColumnas_tres[6])
			    ->setCellValue('H3', $titulosColumnas_tres[7])
			    ->setCellValue('I3', $titulosColumnas_tres[8])
			    ->setCellValue('J3', $titulosColumnas_tres[9])
			    ->setCellValue('K3', $titulosColumnas_tres[10])
			    ->setCellValue('L3', $titulosColumnas_tres[11])
			    ->setCellValue('M3', $titulosColumnas_tres[12])
			    ->setCellValue('N3', $titulosColumnas_tres[13])
			    ->setCellValue('O3', $titulosColumnas_tres[14])
			    ->setCellValue('P3', $titulosColumnas_tres[15])
			    ->setCellValue('Q3', $titulosColumnas_tres[16])
			    ->setCellValue('R3', $titulosColumnas_tres[17])
			    ->setCellValue('S3', $titulosColumnas_tres[18])
			    ->setCellValue('T3', $titulosColumnas_tres[19])
			    ->setCellValue('U3', $titulosColumnas_tres[20])
			    ->setCellValue('V3', $titulosColumnas_tres[21])
			    ->setCellValue('W3', $titulosColumnas_tres[22])
			    ->setCellValue('X3', $titulosColumnas_tres[23])
			    ->setCellValue('Y3', $titulosColumnas_tres[24])
			    ->setCellValue('Z3', $titulosColumnas_tres[25])
			    ->setCellValue('AA3', $titulosColumnas_tres[26])
			    ->setCellValue('AB3', $titulosColumnas_tres[27])
			    ->setCellValue('AC3', $titulosColumnas_tres[28])
			    ->setCellValue('AD3', $titulosColumnas_tres[29])
			    ->setCellValue('AE3', $titulosColumnas_tres[30])
			    ->setCellValue('AF3', $titulosColumnas_tres[31])
			    ->setCellValue('AG3', $titulosColumnas_tres[32])
			    ->setCellValue('AH3', $titulosColumnas_tres[33])
			    ->setCellValue('AI3', $titulosColumnas_tres[34])
			    ->setCellValue('AJ3', $titulosColumnas_tres[35])
			    ->setCellValue('AK3', $titulosColumnas_tres[36])
			    ->setCellValue('AL3', $titulosColumnas_tres[37])
			    ->setCellValue('AM3', $titulosColumnas_tres[38])
			    ->setCellValue('AN3', $titulosColumnas_tres[39])
			    ->setCellValue('AO3', $titulosColumnas_tres[40]);

	//Se agregan los datos a la lista del reporte
    $i = 4; //Numero de fila donde se va a comenzar a rellenar

	// INICIO DETALLE LIQUIDACION
	if($row_count_detalle_liquidacion > 0)
	{
		$sub_diario = "";
		$importe_dolares = "";
		$importe_igv = "";

		while ($row = $list_query_liquidacion->fetch_array())
		{
			$dt_fecha_documento = $row["dt_fecha_documento"];
			$dt_codigo_provision_contable = $row["dt_codigo_provision_contable"];
			$dt_cuenta_contable_nombre = $row["dt_cuenta_contable_nombre"];
			$dt_cuenta_contable_codigo = $row["dt_cuenta_contable_codigo"];
			$dt_tipo_cambio = $row["dt_tipo_cambio"];
			$dt_tipo_conversion = $row["dt_tipo_conversion"];
			$dt_flag_conversion_moneda = $row["dt_flag_conversion_moneda"];
			$dt_ruc_codigo_anexo = $row["dt_ruc_codigo_anexo"];
			$dt_centro_costo = $row["dt_centro_costo"];
			$dt_deber_haber = $row["dt_deber_haber"];
			$dt_importe = $row["dt_importe"];
			$dt_tipo_documento_codigo = $row["dt_tipo_documento_codigo"];
			$dt_serie_comprobante = $row["dt_serie_comprobante"];
			$dt_num_comprobante = $row["dt_num_comprobante"];
			$dt_codigo_anexo_auxiliar = $row["dt_codigo_anexo_auxiliar"];
			$dt_tasa_igv = $row["dt_tasa_igv"];
			$cabecera_cuenta_contable = $row["cabecera_cuenta_contable"];
			$cabecera_cuenta_contable_id = $row["cabecera_cuenta_contable_id"];
			$cabecera_cuenta_contable_nombre = $row["cabecera_cuenta_contable_nombre"];


			if($empresa_id == 30){

				$proceso = "Provision de gastos";

				if($dt_tasa_igv != 0){

					$tipo_pago = "Provision de gastos con IGV";

					if($dt_tasa_igv == 10)
					{
						$importe_igv = (($dt_importe / 1.10) * $dt_tasa_igv) / 100;
					}
					else if($dt_tasa_igv == 18)
					{
						$importe_igv = (($dt_importe / 1.18) * $dt_tasa_igv) / 100;
					}
					else
					{
						echo json_encode(array(
							"ruta_archivo" => "El IGV '".$dt_tasa_igv."'% no es lo correcto en el detalle ",
							"estado_archivo" => 0
						));
						exit;
					}

				}else if($dt_tasa_igv == 0){

					$tipo_pago = "Provision de gastos sin IGV";
					$importe_igv = $dt_importe;

				}

				if($cabecera_cuenta_contable_nombre =="RECIBO POR HONORARIOS"){
					$subdiario_honorarios = " AND LEFT(nc.subdiario, 2) = 15 ";
					
				}else{
					$subdiario_honorarios = "";
				}

				$selectQuery = "SELECT 
									nc.num_cuenta_contable,
									nc.subdiario
								FROM cont_num_cuenta nc
								LEFT JOIN cont_tipo_programacion tp ON tp.id = nc.tipo_pago_id
								LEFT JOIN cont_num_cuenta_proceso cp ON cp.id = nc.cont_num_cuenta_proceso_id
								WHERE nc.status = '1' AND nc.razon_social_id = ? AND cp.nombre = ? AND tp.nombre = ? $subdiario_honorarios
								LIMIT 1";

				$selectStmt = $mysqli->prepare($selectQuery);
				$selectStmt->bind_param("iss", $empresa_id,$proceso,$tipo_pago);
				$selectStmt->execute();
				$selectStmt->store_result();

				if ($selectStmt->num_rows > 0) {
					$selectStmt->bind_result($num_cuenta_contable,$sub_diario);
					$selectStmt->fetch();

					$cabecera_principal_cuenta_contable = $num_cuenta_contable;

				}

			}else{
				$cabecera_principal_cuenta_contable = $cuenta_contable_cabecera;

				if($dt_tasa_igv != 0)
				{
					if($empresa_id==15 || $empresa_id==11)
					{
						$consulta_subdiario = $mysqli->query("SELECT subdiario_compra_con_igv 
															FROM tbl_razon_social 
															WHERE id= ".$empresa_id."")->fetch_assoc();

						$subdiario_compra_con_igv = $consulta_subdiario["subdiario_compra_con_igv"];
						$subdiario_compra_con_igv = str_pad($subdiario_compra_con_igv, 4, "0", STR_PAD_LEFT);
				
						$sub_diario = $subdiario_compra_con_igv;

					}
					else
					{
						$sub_diario = 1420;
					}			
					
					if($dt_tasa_igv == 10)
					{
						$importe_igv = (($dt_importe / 1.10) * $dt_tasa_igv) / 100;
					}
					else if($dt_tasa_igv == 18)
					{
						$importe_igv = (($dt_importe / 1.18) * $dt_tasa_igv) / 100;
					}
					else
					{
						echo json_encode(array(
							"ruta_archivo" => "El IGV '".$dt_tasa_igv."'% no es lo correcto en el detalle ",
							"estado_archivo" => 0
						));
						exit;
					}

				}
				else if($dt_tasa_igv == 0)
				{
					if($empresa_id==15 || $empresa_id==11 )
					{
						$consulta_subdiario = $mysqli->query("SELECT subdiario_compra_sin_igv 
															FROM tbl_razon_social 
															WHERE id= ".$empresa_id."")->fetch_assoc();

						$subdiario_compra_sin_igv = $consulta_subdiario["subdiario_compra_sin_igv"];
						$subdiario_compra_sin_igv = str_pad($subdiario_compra_sin_igv, 4, "0", STR_PAD_LEFT);
				
						$sub_diario = $subdiario_compra_sin_igv;					
					}
					else
					{
						$sub_diario = 1620;
					}
					
					$importe_igv = $dt_importe;
				}
			}
    		$dt_fecha_documento_mes = date('m', strtotime($dt_fecha_documento));
    		$importe_dolares = $dt_importe / $dt_tipo_cambio;

			$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue('B'.$i, substr($sub_diario, 0, 4))
							->setCellValue('C'.$i, substr($dt_fecha_documento_mes.str_pad($correlativo_concar_cabecera, 4, "0", STR_PAD_LEFT), 0, 6))
							->setCellValue('D'.$i, date('d/m/Y', strtotime($dt_fecha_documento)))
							->setCellValue('E'.$i, substr($codigo_moneda_cabecera, 0, 2))
							->setCellValue('F'.$i, substr($dt_cuenta_contable_nombre, 0, 40))
							->setCellValue('G'.$i, $dt_tipo_cambio)
							->setCellValue('H'.$i, substr($dt_tipo_conversion, 0, 1))
							->setCellValue('I'.$i, substr($dt_flag_conversion_moneda, 0, 1))
							->setCellValue('J'.$i, date('d/m/Y', strtotime($dt_fecha_documento)))
							->setCellValue('K'.$i, substr($dt_cuenta_contable_codigo, 0, 12))
							->setCellValue('L'.$i, substr($dt_ruc_codigo_anexo, 0, 18))
							->setCellValue('M'.$i, substr($dt_centro_costo, 0, 6))
							->setCellValue('N'.$i, substr($dt_deber_haber, 0, 1))
							->setCellValue('O'.$i, $dt_importe)
							->setCellValue('P'.$i, $importe_dolares)
							->setCellValue('Q'.$i, $dt_importe)
							->setCellValue('R'.$i, substr($dt_tipo_documento_codigo, 0, 2))
							->setCellValue('S'.$i, substr($dt_serie_comprobante.'-'.$dt_num_comprobante, 0, 20))
							->setCellValue('T'.$i, date('d/m/Y', strtotime($dt_fecha_documento)))
							->setCellValue('U'.$i, date('d/m/Y', strtotime($dt_fecha_documento)))
							->setCellValue('V'.$i, substr('', 0, 3))
							->setCellValue('W'.$i, substr($dt_cuenta_contable_nombre, 0, 30))
							->setCellValue('X'.$i, substr($dt_codigo_anexo_auxiliar, 0, 18))
							->setCellValue('Y'.$i, substr('', 0, 8))
							->setCellValue('Z'.$i, substr('', 0, 2))
							->setCellValue('AA'.$i, substr('', 0, 20))
							->setCellValue('AB'.$i, '')
							->setCellValue('AC'.$i, substr('', 0, 20))
							->setCellValue('AD'.$i, '')
							->setCellValue('AE'.$i, '')
							->setCellValue('AF'.$i, '')
							->setCellValue('AG'.$i, substr('', 0, 15))
							->setCellValue('AH'.$i, '')
							->setCellValue('AI'.$i, substr('', 0, 5))
							->setCellValue('AJ'.$i, '')
							->setCellValue('AK'.$i, '')
							->setCellValue('AL'.$i, '')
							->setCellValue('AM'.$i, substr('', 0, 1))
							->setCellValue('AN'.$i, '')
							->setCellValue('AO'.$i, '');

			$i++;

			$objPHPExcel->setActiveSheetIndex(0)
							->setCellValue('B'.$i, substr($sub_diario, 0, 4))
							->setCellValue('C'.$i, substr($dt_fecha_documento_mes.str_pad($correlativo_concar_cabecera, 4, "0", STR_PAD_LEFT), 0, 6))
							->setCellValue('D'.$i, date('d/m/Y', strtotime($dt_fecha_documento)))
							->setCellValue('E'.$i, substr($codigo_moneda_cabecera, 0, 2))
							->setCellValue('F'.$i, substr($dt_cuenta_contable_nombre, 0, 40))
							->setCellValue('G'.$i, $dt_tipo_cambio)
							->setCellValue('H'.$i, substr($dt_tipo_conversion, 0, 1))
							->setCellValue('I'.$i, substr($dt_flag_conversion_moneda, 0, 1))
							->setCellValue('J'.$i, date('d/m/Y', strtotime($dt_fecha_documento)))
							->setCellValue('K'.$i, substr($cabecera_principal_cuenta_contable, 0, 12))
							->setCellValue('L'.$i, substr($dt_ruc_codigo_anexo, 0, 18))
							->setCellValue('M'.$i, substr($dt_centro_costo, 0, 6))
							->setCellValue('N'.$i, substr($deber_haber_cabecera, 0, 1))
							->setCellValue('O'.$i, $dt_importe)
							->setCellValue('P'.$i, $importe_dolares)
							->setCellValue('Q'.$i, $dt_importe)
							->setCellValue('R'.$i, substr($dt_tipo_documento_codigo, 0, 2))
							->setCellValue('S'.$i, substr($dt_serie_comprobante.'-'.$dt_num_comprobante, 0, 20))
							->setCellValue('T'.$i, date('d/m/Y', strtotime($dt_fecha_documento)))
							->setCellValue('U'.$i, date('d/m/Y', strtotime($dt_fecha_documento)))
							->setCellValue('V'.$i, substr('', 0, 3))
							->setCellValue('W'.$i, substr($dt_cuenta_contable_nombre, 0, 30))
							->setCellValue('X'.$i, substr($dt_codigo_anexo_auxiliar, 0, 18))
							->setCellValue('Y'.$i, substr('', 0, 8))
							->setCellValue('Z'.$i, substr('', 0, 2))
							->setCellValue('AA'.$i, substr('', 0, 20))
							->setCellValue('AB'.$i, '')
							->setCellValue('AC'.$i, substr('', 0, 20))
							->setCellValue('AD'.$i, '')
							->setCellValue('AE'.$i, '')
							->setCellValue('AF'.$i, '')
							->setCellValue('AG'.$i, substr('', 0, 15))
							->setCellValue('AH'.$i, '')
							->setCellValue('AI'.$i, substr('', 0, 5))
							->setCellValue('AJ'.$i, '')
							->setCellValue('AK'.$i, '')
							->setCellValue('AL'.$i, '')
							->setCellValue('AM'.$i, substr('', 0, 1))
							->setCellValue('AN'.$i, number_format($importe_igv, 2, '.', ','))
							->setCellValue('AO'.$i, $dt_tasa_igv);

			$i++;
		}
	}

	// FIN DETALLE LIQUIDACION


	$estiloNombresFilas = array(
		'font' => array(
	        'name'      => 'Calibri',
	        'bold'      => true,
	        'italic'    => false,
	        'strike'    => false,
	        'size' =>11,
	        'color'     => array(
	            'rgb' => '000000'
	        )
	    ),
	    'fill' => array(
			  'type'  => PHPExcel_Style_Fill::FILL_SOLID,
			  'color' => array(
			        'rgb' => 'ffff00')
			),
	    'alignment' =>  array(
	        'horizontal'=> PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
	        'vertical'  => PHPExcel_Style_Alignment::VERTICAL_CENTER,
	        'wrap'      => false
	    )
	);

	$estiloColoFondoAmarillo = array(
	    'fill' => array(
	      'type'  => PHPExcel_Style_Fill::FILL_SOLID,
	      'color' => array(
	            'rgb' => 'ffff00')
	  )
	);

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
	$objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(55);
	$objPHPExcel->getActiveSheet()->getRowDimension('3')->setRowHeight(47);


	$objPHPExcel->getActiveSheet()->getStyle('A1:A1')->applyFromArray($estiloNombresFilas);
	$objPHPExcel->getActiveSheet()->getStyle('A2:A2')->applyFromArray($estiloNombresFilas);
	$objPHPExcel->getActiveSheet()->getStyle('A3:A3')->applyFromArray($estiloNombresFilas);

	$objPHPExcel->getActiveSheet()->getStyle('B1:Z1')->applyFromArray($estiloTituloColumnas);
	$objPHPExcel->getActiveSheet()->getStyle('AA1:AO1')->applyFromArray($estiloTituloColumnas);

	$objPHPExcel->getActiveSheet()->getStyle('B1:Z1')->applyFromArray($estiloColoFondoAmarilloOscuro);
	$objPHPExcel->getActiveSheet()->getStyle('AA1:AO1')->applyFromArray($estiloColoFondoAmarilloOscuro);

	$objPHPExcel->getActiveSheet()->getStyle('B2:Z2')->applyFromArray($estiloTituloColumnas);
	$objPHPExcel->getActiveSheet()->getStyle('AA2:AO2')->applyFromArray($estiloTituloColumnas);

	$objPHPExcel->getActiveSheet()->getStyle('B3:Z3')->applyFromArray($estiloTituloColumnas);
	$objPHPExcel->getActiveSheet()->getStyle('AA3:AO3')->applyFromArray($estiloTituloColumnas);
	
	$objPHPExcel->getActiveSheet()->getStyle('B3:Z3')->applyFromArray($estiloColoFondoAmarillo);
	$objPHPExcel->getActiveSheet()->getStyle('AA3:AO3')->applyFromArray($estiloColoFondoAmarillo);

	
	$objPHPExcel->getActiveSheet()->setSharedStyle($estiloInformacion, "A4:AO".($i-1));

	$objPHPExcel->getActiveSheet()->getStyle('G4:G'.($i-1))->getNumberFormat()->setFormatCode('#,##0.000');
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
	header('Content-Disposition: attachment;filename="Plantilla Concar Provisión Gastos Reembolso Cajas Chicas.xls');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
	$excel_path = '/var/www/html/files_bucket/mepa/descargas/contabilidad/concar_liquidacion/Plantilla Concar Provisión Gastos Reembolso Cajas Chicas.xls';
	$excel_path_download = '/files_bucket/mepa/descargas/contabilidad/concar_liquidacion/Plantilla Concar Provisión Gastos Reembolso Cajas Chicas.xls';

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

function send_email_atencion_caja_chica_rechazado($caja_chica_liquidacion_id, $param_contabilidad, $correos)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];
	$titulo_email = "";
	
	if($param_contabilidad === true)
	{
		// USUARIO ATENCION ES CONTABILIDAD, HACER JOIN CON LOS CAMPOS DE CONTABILIDAD

		$sel_query = $mysqli->query("
				SELECT
					l.id,
					'Solicitud de Liquidación' AS tipo_solicitud,
				    ce.situacion AS estado_solicitud,
				    l.situacion_motivo_contabilidad AS motivo,
				    concat(IFNULL(tpc.nombre, ''),' ', IFNULL(tpc.apellido_paterno, ''), ' ', IFNULL(tpc.apellido_materno, '')) AS nombre_usuario_atencion,
					l.fecha_atencion_contabilidad AS fecha_atencion,
					l.num_correlativo,
					IFNULL(l.total_rendicion, 0) AS total_rendicion,
					IFNULL(mov.monto_cierre, 0) AS total_movilidad,
					concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_solicitante,
					tp.correo,
					tps.correo AS correo_jefe_inmediato
				FROM mepa_caja_chica_liquidacion l
					INNER JOIN cont_etapa ce
					ON l.situacion_etapa_id_contabilidad = ce.etapa_id
					INNER JOIN tbl_usuarios tu
					ON l.solicitante_usuario_id = tu.id
					INNER JOIN tbl_personal_apt tp
					ON tu.personal_id = tp.id
				    INNER JOIN tbl_usuarios tuc
					ON l.usuario_atencion_id_contabilidad = tuc.id
					INNER JOIN tbl_personal_apt tpc
					ON tuc.personal_id = tpc.id
					LEFT JOIN mepa_caja_chica_movilidad mov
					ON l.id_movilidad = mov.id
					INNER JOIN tbl_usuarios tus
					ON l.usuario_atencion_id_superior = tus.id
					INNER JOIN tbl_personal_apt tps
					ON tus.personal_id = tps.id
				WHERE l.id = '".$caja_chica_liquidacion_id."'
		");
	}
	else
	{
		// USUARIO ATENCION ES JEFE INMEDIATO, HACER JOIN CON LOS CAMPOS DE JEFE INMEDIATO

		$sel_query = $mysqli->query("
				SELECT
					l.id,
					'Solicitud de Liquidación' AS tipo_solicitud,
				    ce.situacion AS estado_solicitud,
				    l.situacion_motivo_superior AS motivo,
				    concat(IFNULL(tps.nombre, ''),' ', IFNULL(tps.apellido_paterno, ''), ' ', IFNULL(tps.apellido_materno, '')) AS nombre_usuario_atencion,
					l.fecha_atencion_superior AS fecha_atencion,
					l.num_correlativo,
					IFNULL(l.total_rendicion, 0) AS total_rendicion,
					IFNULL(mov.monto_cierre, 0) AS total_movilidad,
					concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_solicitante,
					tp.correo,
					tps.correo AS correo_jefe_inmediato
				FROM mepa_caja_chica_liquidacion l
					INNER JOIN cont_etapa ce
					ON l.situacion_etapa_id_superior = ce.etapa_id
					INNER JOIN tbl_usuarios tu
					ON l.solicitante_usuario_id = tu.id
					INNER JOIN tbl_personal_apt tp
					ON tu.personal_id = tp.id
					INNER JOIN tbl_usuarios tus
					ON l.usuario_atencion_id_superior = tus.id
					INNER JOIN tbl_personal_apt tps
					ON tus.personal_id = tps.id
					LEFT JOIN mepa_caja_chica_movilidad mov
					ON l.id_movilidad = mov.id
				WHERE l.id = '".$caja_chica_liquidacion_id."'
		");
	}
	

	$sub_total = 0;

	$body = "";
	$body .= '<html>';

	$usuario_creacion_correo = '';

	while($sel = $sel_query->fetch_assoc())
	{
		$id = $sel['id'];
		$tipo_solicitud = $sel['tipo_solicitud'];
		$estado_solicitud = $sel['estado_solicitud'];
		$motivo = $sel['motivo'];
		$nombre_usuario_atencion = $sel['nombre_usuario_atencion'];
		$fecha_atencion_superior = $sel['fecha_atencion'];
		$num_correlativo = $sel['num_correlativo'];
		$total_rendicion = $sel['total_rendicion'];
		$total_movilidad = $sel['total_movilidad'];
		$usuario_solicitante = $sel['usuario_solicitante'];
		$correo = $sel['correo'];
		$correo_jefe_inmediato = $sel['correo_jefe_inmediato'];
		
		$sub_total = $total_rendicion + $total_movilidad; 
		

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

		$body .= '<thead>';
		
		$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>Solicitud Observada</b>';
			$body .= '</th>';
		$body .= '</tr>';

		$body .= '</thead>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 130px;"><b>Solicitud:</b></td>';
			$body .= '<td>'.$tipo_solicitud.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 130px;"><b>Usuario Atención:</b></td>';
			$body .= '<td>'.$nombre_usuario_atencion.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 130px;"><b>Usuario Solicitante:</b></td>';
			$body .= '<td>'.$usuario_solicitante.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 130px;"><b>Nº Caja Chica:</b></td>';
			$body .= '<td>'.$num_correlativo.'</td>';
		$body .= '</tr>';
		
		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Monto:</b></td>';
			$body .= '<td>S/ '.number_format($sub_total, 2, '.', ',').'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Estado:</b></td>';
			$body .= '<td>'.$estado_solicitud.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Motivo:</b></td>';
			$body .= '<td>'.$motivo.'</td>';
		$body .= '</tr>';
	
		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha Atención:</b></td>';
			$body .= '<td>'.$fecha_atencion_superior.'</td>';
		$body .= '</tr>';

		$body .= '</table>';
		$body .= '</div>';

	}
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$host.'/?sec_id=mepa&sub_sec_id=detalle_atencion_liquidacion&id='.$caja_chica_liquidacion_id.'" target="_blank">';
		    	$body .= '<button style="background-color: green; color: white; cursor: pointer; width: 50%;">';
					$body .= '<b>Ver Solicitud</b>';
		    	$body .= '</button>';
		    $body .= '</a>';
		$body .= '</div>';

		$body .= '</html>';
		$body .= "";

	$sub_titulo_email = "";

	if(env('SEND_EMAIL') == 'test')
	{
		$sub_titulo_email = "TEST SISTEMAS: ";
	}

	if($param_contabilidad)
	{
		$titulo_email = $sub_titulo_email."Gestion - Sistema Mesa de Partes - Caja Chica Rechazada Por Contabilidad (Observado) ID: ".$caja_chica_liquidacion_id;
	}
	else
	{
		$titulo_email = $sub_titulo_email."Gestion - Sistema Mesa de Partes - Caja Chica Rechazada Por Jefe Comercial (Observado) ID: ".$caja_chica_liquidacion_id;
	}

	$cc = [
	];

	$bcc = [
	];

	// INICIO CORREO SUPERVISOR DE LA LIQUIDACION
	if(!is_null($correo) AND !empty($correo))
	{
		array_push($cc, $correo);	
	}
	// FIN CORREO SUPERVISOR DE LA LIQUIDACION

	// INICIO CORREO DEL JEFE COMERCIAL - JEFE INMEDIATO DE LA LIQUIDACION
	if(!is_null($correo_jefe_inmediato) AND !empty($correo_jefe_inmediato))
	{
		array_push($cc, $correo_jefe_inmediato);
	}
	// FIN CORREO DEL JEFE COMERCIAL - JEFE INMEDIATO DE LA LIQUIDACION

	if($param_contabilidad === true)
	{
		for($i = 0; $i < count($correos); $i++)
		{
			if(!is_null($correos[$i]) AND !empty($correos[$i]))
			{
				array_push($cc, $correos[$i]);
			}
		}
	}

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
		return $cc;

	} 
	catch (Exception $e) 
	{
		return false;
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_detalle_atencion_liquidacion_enviar_notificacion_de_correciones")
{
	$liquidacion_id = $_POST["liquidacion_id"];
	$es_contabilidad = $_POST["es_contabilidad"];
	
	$host = $_SERVER["HTTP_HOST"];
	$titulo_email = "";
	
	if($es_contabilidad == 1)
	{
		// ES CONTABILIDAD, HACER SELECT A LOS CAMPOS DE CONTABILIDAD

		$sel_query = $mysqli->query("
				SELECT
					l.id,
					l.usuario_atencion_id_contabilidad AS usuario_atencion_id,
					l.num_correlativo,
					l.situacion_motivo_contabilidad AS motivo,
					l.fecha_atencion_contabilidad AS fecha_atencion,
					concat(IFNULL(tps.nombre, ''),' ', IFNULL(tps.apellido_paterno, ''), ' ', IFNULL(tps.apellido_materno, '')) AS usuario_solicitante,
					tp.correo AS correo_usuario_atencion
				FROM mepa_caja_chica_liquidacion l
					INNER JOIN tbl_usuarios tu
					ON l.usuario_atencion_id_contabilidad = tu.id
					INNER JOIN tbl_personal_apt tp
					ON tu.personal_id = tp.id
					INNER JOIN tbl_usuarios tus
					ON l.solicitante_usuario_id = tus.id
					INNER JOIN tbl_personal_apt tps
					ON tus.personal_id = tps.id
				WHERE l.id = '".$liquidacion_id."'
				LIMIT 1
		");
	}
	else
	{
		// ES JEFE INMEDIATO, HACER SELECT A LOS CAMPOS DE JEFE INMEDIATO

		$sel_query = $mysqli->query("
				SELECT
					l.id,
					l.usuario_atencion_id_superior AS usuario_atencion_id,
				    l.num_correlativo,
				    l.situacion_motivo_superior AS motivo,
				    l.fecha_atencion_superior AS fecha_atencion,
				    concat(IFNULL(tps.nombre, ''),' ', IFNULL(tps.apellido_paterno, ''), ' ', IFNULL(tps.apellido_materno, '')) AS usuario_solicitante,
				    tp.correo AS correo_usuario_atencion
				FROM mepa_caja_chica_liquidacion l
					INNER JOIN tbl_usuarios tu
					ON l.usuario_atencion_id_superior = tu.id
					INNER JOIN tbl_personal_apt tp
					ON tu.personal_id = tp.id
					INNER JOIN tbl_usuarios tus
					ON l.solicitante_usuario_id = tus.id
					INNER JOIN tbl_personal_apt tps
					ON tus.personal_id = tps.id
				WHERE l.id = '".$liquidacion_id."'
				LIMIT 1
		");
	}
	
	$body = "";
	$body .= '<html>';


	while($sel = $sel_query->fetch_assoc())
	{
		$id = $sel['id'];
		$usuario_atencion_id = $sel['usuario_atencion_id'];
		$num_correlativo = $sel['num_correlativo'];
		$motivo = $sel['motivo'];
		$fecha_atencion = $sel['fecha_atencion'];
		$usuario_solicitante = $sel['usuario_solicitante'];
		$correo_usuario_atencion = $sel['correo_usuario_atencion'];
		

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

		$body .= '<thead>';
		
		$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>Observaciones corregidas</b>';
			$body .= '</th>';
		$body .= '</tr>';

		$body .= '</thead>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 135px;"><b>Usuario solicitante:</b></td>';
			$body .= '<td>'.$usuario_solicitante.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 135px;"><b>Nº Caja Chica:</b></td>';
			$body .= '<td>'.$num_correlativo.'</td>';
		$body .= '</tr>';
		
		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Motivo del rechazo:</b></td>';
			$body .= '<td>'.$motivo.'</td>';
		$body .= '</tr>';
	
		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha del rechazo:</b></td>';
			$body .= '<td>'.$fecha_atencion.'</td>';
		$body .= '</tr>';

		$body .= '</table>';
		$body .= '</div>';

	}
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$host.'/?sec_id=mepa&sub_sec_id=detalle_atencion_liquidacion&id='.$liquidacion_id.'" target="_blank">';
		    	$body .= '<button style="background-color: green; color: white; cursor: pointer; width: 50%;">';
					$body .= '<b>Ver Solicitud</b>';
		    	$body .= '</button>';
		    $body .= '</a>';
		$body .= '</div>';

		$body .= '</html>';
		$body .= "";


	$titulo_email = "Gestion - Sistema Mesa de Partes - Caja Chica Corregida ID: ".$liquidacion_id;
	
	$cc = [
		
	];

	if(!is_null($correo_usuario_atencion) OR !empty($correo_usuario_atencion))
	{
		array_push($cc, $correo_usuario_atencion);	
	}
	else
	{
		$result["http_code"] = 400;
		$result["status"] = "Datos no obtenidos.";
		$result["error"] = "No existe el correo de la persona a enviar.";
		echo json_encode($result);
		exit();
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

function send_email_atencion_caja_chica_aprobado($caja_chica_liquidacion_id, $param_contabilidad)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];
	$titulo_email = "";
	
	if($param_contabilidad === true)
	{
		// USUARIO ATENCION ES CONTABILIDAD, HACER JOIN CON LOS CAMPOS DE CONTABILIDAD

		$sel_query = $mysqli->query("
				SELECT
					l.id,
				    'Solicitud de Liquidación' AS tipo_solicitud,
				    ce.situacion AS estado_solicitud,
				    concat(IFNULL(tpc.nombre, ''),' ', IFNULL(tpc.apellido_paterno, ''), ' ', IFNULL(tpc.apellido_materno, '')) AS nombre_usuario_atencion,
				    l.fecha_atencion_contabilidad AS fecha_atencion,
				    l.num_correlativo,
				    IFNULL(l.total_rendicion, 0) AS total_rendicion,
				    IFNULL(mov.monto_cierre, 0) AS total_movilidad,
				    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_solicitante,
				    tp.correo,
				    l.situacion_motivo_superior AS motivo
				FROM mepa_caja_chica_liquidacion l
					INNER JOIN cont_etapa ce
					ON l.situacion_etapa_id_contabilidad = ce.etapa_id
					
					INNER JOIN tbl_usuarios tu
					ON l.solicitante_usuario_id = tu.id
					INNER JOIN tbl_personal_apt tp
					ON tu.personal_id = tp.id
					
					INNER JOIN tbl_usuarios tuc
					ON l.usuario_atencion_id_contabilidad = tuc.id
					INNER JOIN tbl_personal_apt tpc
					ON tuc.personal_id = tpc.id

					LEFT JOIN mepa_caja_chica_movilidad mov
					ON l.id_movilidad = mov.id
				WHERE l.id = '".$caja_chica_liquidacion_id."'
		");
	}
	else
	{
		// USUARIO ATENCION ES JEFE INMEDIATO, HACER JOIN CON LOS CAMPOS DE JEFE INMEDIATO

		$sel_query = $mysqli->query("
				SELECT
					l.id,
				    'Solicitud de Liquidación' AS tipo_solicitud,
				    ce.situacion AS estado_solicitud,
				    concat(IFNULL(tps.nombre, ''),' ', IFNULL(tps.apellido_paterno, ''), ' ', IFNULL(tps.apellido_materno, '')) AS nombre_usuario_atencion,
				    l.fecha_atencion_superior AS fecha_atencion,
				    l.num_correlativo,
				    IFNULL(l.total_rendicion, 0) AS total_rendicion,
				    IFNULL(mov.monto_cierre, 0) AS total_movilidad,
				    concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, ''), ' ', IFNULL(tp.apellido_materno, '')) AS usuario_solicitante,
				    tp.correo,
				    l.situacion_motivo_superior AS motivo
				FROM mepa_caja_chica_liquidacion l
					INNER JOIN cont_etapa ce
					ON l.situacion_etapa_id_superior = ce.etapa_id
					
					INNER JOIN tbl_usuarios tu
					ON l.solicitante_usuario_id = tu.id
					INNER JOIN tbl_personal_apt tp
					ON tu.personal_id = tp.id
					
					INNER JOIN tbl_usuarios tus
					ON l.usuario_atencion_id_superior = tus.id
					INNER JOIN tbl_personal_apt tps
					ON tus.personal_id = tps.id

					LEFT JOIN mepa_caja_chica_movilidad mov
					ON l.id_movilidad = mov.id
				WHERE l.id = '".$caja_chica_liquidacion_id."'
		");
	}

	$sub_total = 0;

	$body = "";
	$body .= '<html>';

	$usuario_creacion_correo = '';

	while($sel = $sel_query->fetch_assoc())
	{
		$id = $sel['id'];
		$tipo_solicitud = $sel['tipo_solicitud'];
		$estado_solicitud = $sel['estado_solicitud'];
		$nombre_usuario_atencion = $sel['nombre_usuario_atencion'];
		$fecha_atencion_superior = $sel['fecha_atencion'];
		$num_correlativo = $sel['num_correlativo'];
		$total_rendicion = $sel['total_rendicion'];
		$total_movilidad = $sel['total_movilidad'];
		$usuario_solicitante = $sel['usuario_solicitante'];
		$motivo = $sel['motivo'];
		$correo = $sel['correo'];
		
		$sub_total = $total_rendicion + $total_movilidad; 
		

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

		$body .= '<thead>';
		
		$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>Solicitud Aprobada</b>';
			$body .= '</th>';
		$body .= '</tr>';

		$body .= '</thead>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Solicitud:</b></td>';
			$body .= '<td>'.$tipo_solicitud.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 130px;"><b>Usuario Atención:</b></td>';
			$body .= '<td>'.$nombre_usuario_atencion.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Usuario solicitante:</b></td>';
			$body .= '<td>'.$usuario_solicitante.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Nº Caja Chica:</b></td>';
			$body .= '<td>'.$num_correlativo.'</td>';
		$body .= '</tr>';
		
		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Monto:</b></td>';
			$body .= '<td>S/ '.$sub_total.'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Estado:</b></td>';
			$body .= '<td>'.$estado_solicitud.'</td>';
		$body .= '</tr>';
	
		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha atención:</b></td>';
			$body .= '<td>'.$fecha_atencion_superior.'</td>';
		$body .= '</tr>';

		$body .= '</table>';
		$body .= '</div>';

	}
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$host.'/?sec_id=mepa&sub_sec_id=detalle_atencion_liquidacion&id='.$caja_chica_liquidacion_id.'" target="_blank">';
		    	$body .= '<button style="background-color: green; color: white; cursor: pointer; width: 50%;">';
					$body .= '<b>Ver Solicitud</b>';
		    	$body .= '</button>';
		    $body .= '</a>';
		$body .= '</div>';

		$body .= '</html>';
		$body .= "";


	$sub_titulo_email = "";
	
	if(env('SEND_EMAIL') == 'test')
	{
		$sub_titulo_email = "TEST SISTEMAS: ";
	}

	if($param_contabilidad)
	{

		$titulo_email = $sub_titulo_email."Gestion - Sistema Mesa de Partes - Caja Chica Aprobada Por Contabilidad ID: ".$caja_chica_liquidacion_id;
	}
	else
	{
		$titulo_email = $sub_titulo_email."Gestion - Sistema Mesa de Partes - Caja Chica Aprobada Por Jefe Comercial ID: ".$caja_chica_liquidacion_id;
	}

	
	$cc = [
	];

	$bcc = [
	];

	// INICIO: LISTAR USUARIOS
	// ASISTENTES CONTABLE
	// APROBADOR (JEFE INMEDIATO)
	// USUARIO QUIEN CREA LA LIQUIDACION
	$select_todos = "";

	$select_usuarios_enviar_a_asistente_contable = 
	"
		SELECT
		    tp.correo
		FROM mepa_caja_chica_liquidacion l
			INNER JOIN mepa_asignacion_caja_chica a
			ON l.asignacion_id = a.id
			INNER JOIN mepa_atencion_solicitud_zona sz
			ON a.zona_asignacion_id = sz.id_zona
			INNER JOIN tbl_usuarios tu
			ON sz.id_usuario = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
		WHERE l.id = '".$caja_chica_liquidacion_id."' AND sz.status = 1
	";

	$select_usuarios_enviar_a_jc = 
	"
		SELECT
			tp.correo
		FROM mepa_caja_chica_liquidacion l
			INNER JOIN tbl_usuarios tu
			ON l.usuario_atencion_id_superior = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
		WHERE l.id = '".$caja_chica_liquidacion_id."'
	";

	$select_usuarios_enviar_a_user_liquidacion = 
	"
		SELECT
			tp.correo
		FROM mepa_caja_chica_liquidacion l
			INNER JOIN tbl_usuarios tu
			ON l.user_created_id = tu.id
			INNER JOIN tbl_personal_apt tp
			ON tu.personal_id = tp.id
		WHERE l.id = '".$caja_chica_liquidacion_id."'
	";

	$select_todos .= $select_usuarios_enviar_a_asistente_contable;
	$select_todos .= "UNION ALL";
	$select_todos .= $select_usuarios_enviar_a_jc;
	$select_todos .= "UNION ALL";
	$select_todos .= $select_usuarios_enviar_a_user_liquidacion;

	$sel_query_usuarios_enviar_a = $mysqli->query($select_todos);

	$row_count = $sel_query_usuarios_enviar_a->num_rows;

	if ($row_count > 0)
	{
		while($sel = $sel_query_usuarios_enviar_a->fetch_assoc())
		{
			if(!is_null($sel['correo']) OR !empty($sel['correo']))
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
		return true;

	} 
	catch (Exception $e) 
	{
		return false;
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "agregar_detalle_movilidad")
{
	extract($_POST);
	
	$monto = str_replace(",","",$_POST["monto"]);
	$movilidad_id = $_POST["movilidad_id"];
	$tipo_solicitud_movilidad = $_POST["tipo_solicitud_movilidad"];
	
	$error = '';

	foreach ($_POST as $key => $value) 
	{
		if(($value == "" && $key != "movilidad_id") || ($value == 0 && $key == "centro_costo"))
		{
			$result["focus"] = $key;
			$result["mensaje"] = "Ingresar el campo " .$key;
			$result["error"] = true;
			echo json_encode($result);
			die();
		}
	}

	//MOVILIDAD CAJERO VOLANTE
	if($tipo_solicitud_movilidad == 8)
	{
		$query_insert_movilidad_detalle = "INSERT INTO mepa_caja_chica_movilidad_detalle
		(
			id_mepa_caja_chica_movilidad,
			fecha,
			partida_destino,
			motivo_traslado,
			monto,
			estado,
			created_at,
			centro_costo
		) 
		VALUES 
		(
			'".$movilidad_id."',
			'".$fecha."',
			'".$partida_destino."',
			'".$motivo_traslado."',
			'".$monto."',
			'1',
			'".date('Y-m-d H:i:s')."',
			'".$centro_costo."'
		)";
	}
	else
	{
		$query_insert_movilidad_detalle = "INSERT INTO mepa_caja_chica_movilidad_detalle
			(
				id_mepa_caja_chica_movilidad,
				fecha,
				partida_destino,
				motivo_traslado,
				monto,
				estado,
				created_at
			) 
			VALUES 
			(
				'".$movilidad_id."',
				'".$fecha."',
				'".$partida_destino."',
				'".$motivo_traslado."',
				'".$monto."',
				'1',
				'".date('Y-m-d H:i:s')."'
			)";
	}

    $mysqli->query($query_insert_movilidad_detalle);

	if($mysqli->error)
	{
		$error = $mysqli->error;
	}

	if ($error == '') 
	{
		//INICIO: UPDATE  monto_cierre EN CABECERA
		$query_update = "
			UPDATE
				mepa_caja_chica_movilidad
			SET
				monto_cierre = REPLACE((
										SELECT 
											IF(sum(monto) IS NULL, 0.00, FORMAT(sum(monto), 2)) AS total
										FROM mepa_caja_chica_movilidad_detalle AS mccmd
										WHERE mccmd.id_mepa_caja_chica_movilidad = '".$movilidad_id."' AND mccmd.estado = 1
										), ',', '')
			WHERE id = '".$movilidad_id."' ";

		$mysqli->query($query_update);
		//FIN: UPDATE  monto_cierre EN CABECERA

		//INICIO ACTUALIZAR EL SALDO DISPONIBLE
		$query_update_saldo = "
						UPDATE mepa_asignacion_caja_chica 
							SET saldo_disponible = saldo_disponible - '".$monto."'
						WHERE id = '".$asignacion_movilidad_id."' 
						";
		$mysqli->query($query_update_saldo);
		//FIN ACTUALIZAR EL SALDO DISPONIBLE

		$result["mensaje"] = "Detalle Movilidad Insertado";
	} 
	else 
	{
		$result["mensaje"] = "No se inserto: " .$error;	
	}
}

if (isset($_POST["accion"]) && $_POST["accion"] === "sec_mepa_detalle_atencion_liquidacion_eliminar_detalle_movilidad")
{
	$detalle_movilidad_id = $_POST["detalle_movilidad_id"];
	$monto = $_POST["monto"];
	$asignacion_id = $_POST["asignacion_id"];
	$movilidad_id = $_POST["movilidad_id"];
	
	$error = '';

	$query = "
		DELETE FROM  mepa_caja_chica_movilidad_detalle 						
		WHERE id = ".$detalle_movilidad_id;
	$mysqli->query($query);
	
	//INICIO SUMAR EL MONTO EN EL SALDO DISPONIBLE DE LA ASIGNACION CORRESPONDIENTE

    $asignacion_importe = "
    	UPDATE mepa_asignacion_caja_chica
			SET saldo_disponible = saldo_disponible + '".$monto."'
		WHERE id = '".$asignacion_id."' ";

	$mysqli->query($asignacion_importe);
	//FIN SUMAR EL MONTO EN EL SALDO DISPONIBLE DE LA ASIGNACION CORRESPONDIENTE

	//INICIO: UPDATE  monto_cierre EN CABECERA
	$query_update = "
		UPDATE
			mepa_caja_chica_movilidad
		SET
			monto_cierre = REPLACE((
									SELECT 
										IF(sum(monto) IS NULL, 0.00, FORMAT(sum(monto), 2)) AS total
									FROM mepa_caja_chica_movilidad_detalle AS mccmd
									WHERE mccmd.id_mepa_caja_chica_movilidad = '".$movilidad_id."' AND mccmd.estado = 1
									), ',', '')
		WHERE id = '".$movilidad_id."' ";

	$mysqli->query($query_update);
	//FIN: UPDATE  monto_cierre EN CABECERA

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
}

if (isset($_POST["accion"]) && $_POST["accion"]==="mepa_detalle_atencion_liquidacion_eliminar_liquidacion") 
{
	$usuario_id = $login?$login['id']:null;
	
	if((int)$usuario_id > 0)
	{
		$created_at = date("Y-m-d H:i:s");
		$error = '';
		$query_update = "";
		$solicitar_eliminar = 0;

		$mepa_detalle_atencion_liquidacion_id = $_POST["mepa_detalle_atencion_liquidacion_id"];
		$txt_situacion = $_POST["txt_situacion"];
		$txt_motivo = $_POST["txt_motivo"];

		$respuesta_titulo = "";
		$respuesta_texto = "";

		//INICIO: OBTENEMOS LA INFORMACION DE LA LIQUIDACION
		$query_select = 
	    "
	        SELECT
				l.id,
				l.asignacion_id,
				IFNULL(l.total_rendicion, 0) AS total_liquidacion,
				l.se_aplica_movilidad, l.id_movilidad, 
				IFNULL(m.monto_cierre, 0) AS total_movilidad
			FROM mepa_caja_chica_liquidacion l
				LEFT JOIN mepa_caja_chica_movilidad m
				ON l.id_movilidad = m.id
				INNER JOIN cont_etapa ce
				ON l.situacion_etapa_id_superior = ce.etapa_id
				INNER JOIN cont_etapa cec
				ON l.situacion_etapa_id_contabilidad = cec.etapa_id
				INNER JOIN cont_etapa cet
				ON l.situacion_etapa_id_tesoreria = cet.etapa_id
			WHERE l.id = '".$mepa_detalle_atencion_liquidacion_id."'
	    ";

	    $query = $mysqli->query($query_select);
	    $cant_registros = $query->num_rows;

	    if($cant_registros > 0)
	    {
	        $reg = $query->fetch_assoc();
	        $select_asignacion_id = $reg["asignacion_id"];
	        $select_total_liquidacion = $reg["total_liquidacion"];
	        $select_total_movilidad = $reg["total_movilidad"];

	        $sub_total_liquidacion = $select_total_liquidacion + $select_total_movilidad;
	    }
	    else
	    {
	    	$result["http_code"] = 400;
			$result["titulo"] = "Error";
			$result["error"] = "No se encontro el registro de la liquidación.";

			echo json_encode($result);
			exit();
	    }
		//FIN: OBTENEMOS LA INFORMACION DE LA LIQUIDACION

		if($txt_situacion == 13)
		{
			$solicitar_eliminar = 1;
			$respuesta_titulo = "Solicitud de liquidación eliminada";
			$respuesta_texto = "";

			$query_update_saldo = 
			"
				UPDATE mepa_asignacion_caja_chica 
					SET saldo_disponible = saldo_disponible + '".$sub_total_liquidacion."'
				WHERE id = '".$select_asignacion_id."' 
			";
		}
		else
		{
			$respuesta_titulo = "Solicitud de liquidación revertido";
			$respuesta_texto = "";

			$query_update_saldo = 
			"
				UPDATE mepa_asignacion_caja_chica 
					SET saldo_disponible = saldo_disponible - '".$sub_total_liquidacion."'
				WHERE id = '".$select_asignacion_id."' 
			";
		}
		
		$query_update = 
		"
			UPDATE mepa_caja_chica_liquidacion 
				SET solicitar_eliminar_liquidacion = '".$solicitar_eliminar."',
					etapa_id_eliminar_liquidacion = '".$txt_situacion."', 
					motivo_eliminar = '".$txt_motivo."',
					fecha_eliminacion = '".$created_at."'
			WHERE id = '".$mepa_detalle_atencion_liquidacion_id."' 
		";

		$mysqli->query($query_update);

		if($mysqli->error)
		{
			$error = $mysqli->error;

			$result["http_code"] = 400;
			$result["titulo"] = "Error al grabar";
			$result["error"] = $error;

			echo json_encode($result);
			exit();
		}

		$mysqli->query($query_update_saldo);

		if($mysqli->error)
		{
			$error = $mysqli->error;

			$result["http_code"] = 400;
			$result["titulo"] = "Error al grabar";
			$result["error"] = $error;

			echo json_encode($result);
			exit();
		}

		$result["http_code"] = 200;
		$result["status"] = $respuesta_titulo;
		$result["texto"] = $respuesta_texto;
		$result["error"] = $error;

		echo json_encode($result);
		exit();
	}
	else
	{
		$result["http_code"] = 400;
        $result["error"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";

        echo json_encode($result);
		exit();
	}	
}

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_mepa_detalle_atencion_liquidacion_btn_regresar_etapa_anterior") 
{
	$usuario_id = $login?$login['id']:null;
	
	if((int)$usuario_id > 0)
	{
		$liquidacion_id = $_POST["liquidacion_id"];
		$verificar_etapa = $_POST["verificar_etapa"];
		$txt_situacion = $_POST["txt_situacion"];
		$situacion_asignacion_id = $_POST["situacion_asignacion_id"];
		$asignacion_id = $_POST["asignacion_id"];

		$created_at = date("Y-m-d H:i:s");
		$error = '';
		$query_update = "";

		if($verificar_etapa == 1)
		{
			// NIVEL JEFE
			$query_update = 
			"
				UPDATE mepa_caja_chica_liquidacion 
				SET 
					situacion_etapa_id_superior = '".$txt_situacion."',
					situacion_motivo_superior = 'REGRESAR A ETAPA ANTERIOR',
					etapa_id_se_envio_a_tesoreria = 1,
					situacion_jefe_cerrar_caja_chica = NULL,
					sub_total = NULL,
					user_updated_id = '".$usuario_id."',
					updated_at = '".$created_at."'
				WHERE id = '".$liquidacion_id."' 
			";

			// VERIFICAR SI LA ASIGNACION ESTA CERRADA
			if($situacion_asignacion_id == 8)
			{
				$query_update_asignacion = 
				"
					UPDATE mepa_asignacion_caja_chica 
					SET 
						situacion_etapa_id = 6,
						situacion_motivo = 'ACTIVAR POR REGRESAR A ETAPA ANTERIOR',
						user_updated_id = '".$usuario_id."',
						updated_at = '".$created_at."'
					WHERE id = '".$asignacion_id."' 
				";
			}
		}
		else if($verificar_etapa == 2)
		{
			// NIVEL CONTABILIDAD
			$query_update = 
			"
				UPDATE mepa_caja_chica_liquidacion 
				SET 
					situacion_etapa_id_contabilidad = '".$txt_situacion."',
					situacion_motivo_contabilidad = 'REGRESAR A ETAPA ANTERIOR',
					etapa_id_se_envio_a_tesoreria = 1,
					sub_total = NULL,
					user_updated_id = '".$usuario_id."',
					updated_at = '".$created_at."'
				WHERE id = '".$liquidacion_id."' 
			";
		}
		else
		{
			$result["http_code"] = 400;
			$result["status"] = "Error.";
			$result["error"] = "No se encontro la situacion de la etapa";

			echo json_encode($result);
			exit();
		}

		$mysqli->query($query_update);

		if($situacion_asignacion_id == 8)
		{
			$mysqli->query($query_update_asignacion);	
		}
		
		if($mysqli->error)
		{
			$error = $mysqli->error;
		}

		if ($error == '') 
		{
			$result["http_code"] = 200;
			$result["status"] = "Atención exitosa.";
			$result["texto"] = "La atención fue exitosamente.";
			$result["error"] = $error;

			echo json_encode($result);
			exit();
		} 
		else 
		{
			$result["http_code"] = 400;
			$result["status"] = "Error.";
			$result["texto"] = "La atención fue error.";
			$result["error"] = $error;

			echo json_encode($result);
			exit();
		}
	}
	else
	{
		$result["http_code"] = 400;
        $result["error"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";

        echo json_encode($result);
		exit();
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_mepa_detalle_atencion_liquidacion_atencion_jefe_dar_de_baja") 
{
	$usuario_id = $login?$login['id']:null;
	
	if((int)$usuario_id > 0)
	{
		$created_at = date("Y-m-d H:i:s");
		$error = '';
		$query_update = "";

		$mepa_detalle_atencion_liquidacion_id = $_POST["mepa_detalle_atencion_liquidacion_id"];
		$txt_situacion = $_POST["txt_situacion"];
		$txt_tipo = $_POST["txt_tipo"];
		$txt_motivo_cerrar = $_POST["txt_motivo_cerrar"];

		//INICIO OBTENER EL ID ASIGNACION

		$query_sql = 
		"
	        SELECT
				asignacion_id,
				id_movilidad,
				se_aplica_movilidad,
				solicitar_eliminar_liquidacion,
				user_created_id
			FROM mepa_caja_chica_liquidacion
			WHERE id = '".$mepa_detalle_atencion_liquidacion_id."'
			LIMIT 1
		";

	    $list_query = $mysqli->query($query_sql);
	    
	    $row = $list_query->fetch_assoc();
	    $asignacion_id = $row["asignacion_id"];
	    $id_movilidad = $row["id_movilidad"];
	    $se_aplica_movilidad = $row["se_aplica_movilidad"];
	    $solicitar_eliminar_liquidacion = $row["solicitar_eliminar_liquidacion"];
	    $usuario_liquidacion = $row["user_created_id"];

		//FIN OBTENER EL ID ASIGNACION

		//APROBADO Y CERRAR CAJA CHICA
		$query_update = 
		"
			UPDATE mepa_caja_chica_liquidacion 
				SET situacion_jefe_cerrar_caja_chica = '".$txt_situacion."',
					tipo_cerrar_caja_chica = '".$txt_tipo."',
					motivo_cerrar_caja_chica = '".$txt_motivo_cerrar."'
			WHERE id = '".$mepa_detalle_atencion_liquidacion_id."' 
		";

		$query_cerrar_asignacion = 
		"
			UPDATE mepa_asignacion_caja_chica 
				SET situacion_etapa_id = 8
			WHERE id = '".$asignacion_id."' 
		";
		
		$mysqli->query($query_update);

		$mysqli->query($query_cerrar_asignacion);
		
		if($txt_tipo == 1 || $txt_tipo == 4 || $txt_tipo == 5)
		{
			$query_quitar_grupo = 
			"
				UPDATE mepa_usuario_asignacion_detalle 
					SET status = 0,
						user_updated_id = '".$usuario_id."',
						updated_at = '".$created_at."'
				WHERE mepa_asignacion_rol_id = 3 AND usuario_id = '".$usuario_liquidacion."' 
			";

			$mysqli->query($query_quitar_grupo);
		}
		
		if($mysqli->error)
		{
			$error = $mysqli->error;
		}

		if ($error == '') 
		{
			// INICIO: CALCULAR SI PROCEDE CON LA DEVOLUCION DEL SALDO RESTANTE
			
			$query_select = 
			"
				SELECT
					a.id AS asignacion_id,
					a.fondo_asignado AS fondo_asignado,
					a.saldo_disponible AS saldo_disponible
				FROM mepa_asignacion_caja_chica a
				WHERE a.id = '".$asignacion_id."' AND situacion_etapa_id = 8
			";

			$data_query = $mysqli->query($query_select);

			while($row = $data_query->fetch_assoc())
			{
				$asignacion_id = $row["asignacion_id"];
				$fondo_asignado = $row["fondo_asignado"];
				$saldo_disponible = $row["saldo_disponible"];
			}

			if($saldo_disponible > 0)
			{
				$query_update_devolucion = "
							UPDATE mepa_asignacion_caja_chica 
								SET aplica_devolucion = 1,
									se_solicito_devolucion = 0
							WHERE id = '".$asignacion_id."'
					";
			}
			else
			{
				$query_update_devolucion = "
							UPDATE mepa_asignacion_caja_chica 
								SET aplica_devolucion = 0,
									se_solicito_devolucion = 0
							WHERE id = '".$asignacion_id."'
					";
			}

			$mysqli->query($query_update_devolucion);

			// FIN: CALCULAR SI PROCEDE CON LA DEVOLUCION DEL SALDO RESTANTE

			$result["http_code"] = 200;
			$result["mepa_detalle_atencion_liquidacion_id"] = $mepa_detalle_atencion_liquidacion_id;
			$result["status"] = "Atención exitosa.";
			$result["texto"] = "La atención fue exitosa.";
			$result["error"] = $error;

			echo json_encode($result);
			exit();
		}
		else 
		{
			$result["http_code"] = 400;
			$result["mepa_detalle_atencion_liquidacion_id"] = $mepa_detalle_atencion_liquidacion_id;
			$result["status"] = "Datos no obtenidos.";
			$result["texto"] = "La solicitud fue error.";
			$result["error"] = $error;

			echo json_encode($result);
			exit();
		}
	}
	else
	{
		$result["http_code"] = 400;
        $result["error"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";

        echo json_encode($result);
		exit();
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_mepa_detalle_atencion_liquidacion_btn_editar_correlativo_liquidacion") 
{
	$login_usuario_id = $login?$login['id']:null;
	
	if((int)$login_usuario_id > 0)
	{
		$liquidacion_id = $_POST["liquidacion_id"];
		$solicitante_usuario_id = $_POST["solicitante_usuario_id"];
		$num_correlativo = $_POST["num_correlativo"];
		
		$created_at = date("Y-m-d H:i:s");
		$error = '';
		$query_update = "";
		$query_update_correlativo_cabecera = "";

		$query_update = 
		"
			UPDATE mepa_caja_chica_liquidacion 
			SET 
				num_correlativo = '".$num_correlativo."',
				user_updated_id = '".$login_usuario_id."',
				updated_at = '".$created_at."'
			WHERE id = '".$liquidacion_id."' 
		";

		$mysqli->query($query_update);

		if($mysqli->error)
		{
			$error = $mysqli->error;

			$result["http_code"] = 400;
			$result["status"] = "Error.";
			$result["texto"] = "La edición tuvo error.";
			$result["error"] = $error;

			echo json_encode($result);
			exit();
		}
		
		$num_correlativo ++;

		$query_update_correlativo_cabecera = 
		"	
			UPDATE mepa_documento_correlativo 
				JOIN mepa_asignacion_caja_chica
				ON (mepa_asignacion_caja_chica.id = mepa_documento_correlativo.asignacion_id)
			SET mepa_documento_correlativo.num_correlativo = '".$num_correlativo."'
			WHERE mepa_asignacion_caja_chica.usuario_asignado_id = '".$solicitante_usuario_id."' 
				AND mepa_documento_correlativo.tipo_solicitud = 2
		";

		$mysqli->query($query_update_correlativo_cabecera);

		if($mysqli->error)
		{
			$error = $mysqli->error;

			$result["http_code"] = 400;
			$result["status"] = "Error.";
			$result["texto"] = "La edición tuvo error.";
			$result["error"] = $error;

			echo json_encode($result);
			exit();
		}

		if ($error == '') 
		{
			$result["http_code"] = 200;
			$result["status"] = "Edición exitoso.";
			$result["texto"] = "La edición fue exitosamente.";
			$result["error"] = $error;

			echo json_encode($result);
			exit();
		} 
		else 
		{
			$result["http_code"] = 400;
			$result["status"] = "Error.";
			$result["texto"] = "La edición tuvo error.";
			$result["error"] = $error;

			echo json_encode($result);
			exit();
		}
	}
	else
	{
		$result["http_code"] = 400;
        $result["error"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";

        echo json_encode($result);
		exit();
	}
}

if (isset($_POST["accion"]) && $_POST["accion"]==="sec_mepa_detalle_atencion_liquidacion_btn_editar_correlativo_movilidad") 
{
	$login_usuario_id = $login?$login['id']:null;
	
	if((int)$login_usuario_id > 0)
	{
		$id_movilidad = $_POST["id_movilidad"];
		$solicitante_usuario_id = $_POST["solicitante_usuario_id"];
		$num_correlativo = ltrim($_POST["num_correlativo"], "0");
		
		$created_at = date("Y-m-d H:i:s");
		$error = '';
		$query_update = "";
		$query_update_correlativo_cabecera = "";

		$num_correlativo = substr(str_repeat(0, 9).$num_correlativo, - 9);

		$query_update = 
		"
			UPDATE mepa_caja_chica_movilidad 
			SET 
				num_correlativo = '".$num_correlativo."',
				updated_at = '".$created_at."'
			WHERE id = '".$id_movilidad."' 
		";

		$mysqli->query($query_update);

		if($mysqli->error)
		{
			$error = $mysqli->error;

			$result["http_code"] = 400;
			$result["status"] = "Error.";
			$result["texto"] = "La edición tuvo error.";
			$result["error"] = $error;

			echo json_encode($result);
			exit();
		}
		
		$query_update_correlativo_cabecera = 
		"
			UPDATE mepa_documento_correlativo 
				SET num_correlativo = '".$num_correlativo."'
			WHERE id_usuario = '".$solicitante_usuario_id."' AND tipo_solicitud = 3
		";

		$mysqli->query($query_update_correlativo_cabecera);

		if($mysqli->error)
		{
			$error = $mysqli->error;

			$result["http_code"] = 400;
			$result["status"] = "Error.";
			$result["texto"] = "La edición tuvo error.";
			$result["error"] = $error;

			echo json_encode($result);
			exit();
		}

		if ($error == '') 
		{
			$result["http_code"] = 200;
			$result["status"] = "Edición exitoso.";
			$result["texto"] = "La edición fue exitosamente.";
			$result["error"] = $error;

			echo json_encode($result);
			exit();
		} 
		else
		{
			$result["http_code"] = 400;
			$result["status"] = "Error.";
			$result["texto"] = "La edición tuvo error.";
			$result["error"] = $error;

			echo json_encode($result);
			exit();
		}
	}
	else
	{
		$result["http_code"] = 400;
        $result["error"] ="Sesión perdida. Por favor vuelva a iniciar sesión.";

        echo json_encode($result);
		exit();
	}
}

if(isset($_POST["accion"]) && $_POST["accion"]==="mepa_detalle_atencion_liquidacion_mostrar_movilidad_rango_fecha") 
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
					m.id, m.num_correlativo, CONCAT('S/ ', m.monto_cierre) AS monto_cierre
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

	echo json_encode($result);
	exit();
}

if(isset($_POST["accion"]) && $_POST["accion"]==="mepa_detalle_atencion_liquidacion_guardar_incluir_movilidad") 
{
	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	$error = '';
	$monto_cierre_movilidad = 0;
	
	if((int) $usuario_id>0)
	{
		$param_liquidacion_id = $_POST["mepa_detalle_atencion_liquidacion_id"];
		$id_movilidad_select = $_POST["id_movilidad"];

		$query_update_incluir_movilidad = 
		"
			UPDATE mepa_caja_chica_liquidacion
				SET id_movilidad = '".$id_movilidad_select."',
					se_aplica_movilidad = 1
			WHERE id = '".$param_liquidacion_id."' 
		";
		
		$mysqli->query($query_update_incluir_movilidad);

		if($mysqli->error)
		{
			$error .= $mysqli->error . $query_update_incluir_movilidad;

			$result["http_code"] = 400;
			$result["titulo"] = "Error.";
			$result["descripcion"] = $error;

			echo json_encode($result);
			exit();
		}

		$query_sql_movilidad = 
		"
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
	    	$suma_monto_total = $row["monto_cierre"];
	    }

	    if($mysqli->error)
		{
			$error .= $mysqli->error . $query_sql_movilidad;

			$result["http_code"] = 400;
			$result["titulo"] = "Error.";
			$result["descripcion"] = $error;

			echo json_encode($result);
			exit();
		}

		//INICIO ACTUALIZAR EL SALDO DISPONIBLE
		$query_update_saldo = 
		"
			UPDATE mepa_asignacion_caja_chica a
				JOIN mepa_caja_chica_liquidacion l
				ON l.asignacion_id = a.id
				SET a.saldo_disponible = a.saldo_disponible - '".$suma_monto_total."'
			WHERE l.id = '".$param_liquidacion_id."' 
		";

		$mysqli->query($query_update_saldo);
		//FIN ACTUALIZAR EL SALDO DISPONIBLE

		$result["http_code"] = 200;
		$result["titulo"] = "La movilidad se incluyo exitosamente.";
		$result["descripcion"] = $error;

		echo json_encode($result);
		exit();
	}
	else
	{
		$result["http_code"] = 400;
        $result["titulo"] ="Sesión perdida";
        $result["descripcion"] ="Por favor vuelva a iniciar sesión.";
	}

	echo json_encode($result);
	exit();
}

if(isset($_POST["accion"]) && $_POST["accion"]==="mepa_detalle_atencion_liquidacion_anular_plantilla_movilidad") 
{
	$usuario_id = $login?$login['id']:null;
	$created_at = date("Y-m-d H:i:s");
	$error = '';
	$monto_cierre_movilidad = 0;
	
	if((int) $usuario_id>0)
	{
		$param_liquidacion_id = $_POST["mepa_detalle_atencion_liquidacion_id"];

		$query_select_data = 
		"
			SELECT
				id, asignacion_id, id_movilidad
			FROM mepa_caja_chica_liquidacion
			WHERE id = '".$param_liquidacion_id."' 
		";

		$query = $mysqli->query($query_select_data);
	    $cant = $query->num_rows;
	    $row = $query->fetch_assoc();

	    if($cant > 0)
	    {
	    	$id_asignacion_select = $row["asignacion_id"];
	    	$id_movilidad_select = $row["id_movilidad"];
	    }

		$query_update_anular_movilidad = 
		"
			UPDATE mepa_caja_chica_liquidacion
				SET id_movilidad = NULL,
					se_aplica_movilidad = 0
			WHERE id = '".$param_liquidacion_id."' 
		";
		
		$mysqli->query($query_update_anular_movilidad);

		if($mysqli->error)
		{
			$error .= $mysqli->error . $query_update_anular_movilidad;

			$result["http_code"] = 400;
			$result["titulo"] = "Error.";
			$result["descripcion"] = $error;

			echo json_encode($result);
			exit();
		}

		$query_sql_movilidad = 
		"
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
	    	$suma_monto_total = $row["monto_cierre"];
	    }

	    if($mysqli->error)
		{
			$error .= $mysqli->error . $query_sql_movilidad;

			$result["http_code"] = 400;
			$result["titulo"] = "Error.";
			$result["descripcion"] = $error;

			echo json_encode($result);
			exit();
		}

		//INICIO ACTUALIZAR EL SALDO DISPONIBLE
		$query_update_saldo = 
		"
			UPDATE mepa_asignacion_caja_chica 
				SET saldo_disponible = saldo_disponible + '".$suma_monto_total."'
			WHERE id = '".$id_asignacion_select."' 
		";

		$mysqli->query($query_update_saldo);
		//FIN ACTUALIZAR EL SALDO DISPONIBLE

		$result["http_code"] = 200;
		$result["titulo"] = "La movilidad se anuló.";
		$result["descripcion"] = $error;

		echo json_encode($result);
		exit();
	}
	else
	{
		$result["http_code"] = 400;
        $result["titulo"] ="Sesión perdida";
        $result["descripcion"] ="Por favor vuelva a iniciar sesión.";
	}

	echo json_encode($result);
	exit();
}

echo json_encode($result);

?>