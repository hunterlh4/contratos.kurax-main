<?php

date_default_timezone_set("America/Lima");

$result=array();
include("db_connect.php");
include("sys_login.php");

include '/var/www/html/vendor/PHPMailer6.1.7/PHPMailer.php';
include '/var/www/html/vendor/PHPMailer6.1.7/SMTP.php';	
include '/var/www/html/sys/globalFunctions/generalInfo/parameterGeneral.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$area_desarrollo_de_negocios_id = 49;
$area_gerencia_id = 16;
$area_legal_id = 33;
$area_proyectos_id = 38;
$area_televentas_id = 31;

$cargo_gerente_id = 3;
$cargo_director_id = 26;
$cargo_asesor_id = 14;
$cargo_municipal_id = 25;
$cargo_cajero_id = 5;

$menu_id_consultar = $mysqli->query("SELECT id FROM tbl_menu_sistemas WHERE sec_id = 'mepa' AND sub_sec_id = 'mesa_partes' LIMIT 1")->fetch_assoc();
$menu_asignacion_caja_chica = $menu_id_consultar["id"];


if (isset($_POST["accion"]) && $_POST["accion"]==="guardar_solicitud_asignacion_caja_chica") 
{
	$usuario_id = $login?$login['id']:null;

	if((int)$usuario_id > 0)
	{
		$created_at = date("Y-m-d H:i:s");
		$error = '';

		$txt_motivo = $_POST["txt_motivo"];
		$int_banco = $_POST["int_banco"];
		$txt_numero_cuenta = $_POST["txt_numero_cuenta"];
		$txt_fondo_asignado = str_replace(",","",$_POST["txt_fondo_asignado"]);
		$int_usuario_asignado = $_POST["int_usuario_asignado"];
		$int_zona = $_POST["int_zona"];
		$int_empresa = $_POST["int_empresa"];
		$int_se_reportara_usuario = $_POST["int_se_reportara_usuario"];

		$reportar_usuario = $_POST["array_reportar_usuario"];
		$reportar_usuario = json_decode($reportar_usuario);
		
		if($int_empresa == 30)
		{
			if($int_zona == 39)
			{
				$int_zona = 42;
			}
		}

		$sel_query_buscar_creador = 
		"
			SELECT
				uad.id,
				uad.mepa_usuario_asignacion_id,
				uad.usuario_id
			FROM mepa_usuario_asignacion_detalle uad
				INNER JOIN tbl_usuarios u
				ON uad.usuario_id = u.id
				INNER JOIN tbl_personal_apt p
				ON u.personal_id = p.id
				LEFT JOIN tbl_razon_social rs
				ON p.razon_social_id = rs.id
				INNER JOIN tbl_areas a
				ON p.area_id = a.id
				INNER JOIN tbl_cargos c
				ON p.cargo_id = c.id
			WHERE uad.usuario_id = '".$usuario_id."' 
				AND uad.mepa_asignacion_rol_id = 1
				AND uad.status = 1
			LIMIT 1
		";

		$query = $mysqli->query($sel_query_buscar_creador);

		$cant_registro = $query->num_rows;

		if($cant_registro > 0)
		{
			while($sel = $query->fetch_assoc())
			{
				$mepa_usuario_asignacion_id = $sel['mepa_usuario_asignacion_id'];
				$usuario_id = $sel['usuario_id'];
			}

			$sel_query_aprobador = 
			"
				SELECT
					uad.mepa_usuario_asignacion_id,
					uad.usuario_id
				FROM mepa_usuario_asignacion_detalle uad
				WHERE uad.mepa_usuario_asignacion_id = '".$mepa_usuario_asignacion_id."' 
					AND uad.mepa_asignacion_rol_id = 2
					AND uad.status = 1
				LIMIT 1
			";

			$query = $mysqli->query($sel_query_aprobador);

			$cant_registro = $query->num_rows;

			if($cant_registro > 0)
			{
				while($sel = $query->fetch_assoc())
				{
					$mepa_usuario_asignacion_id = $sel['mepa_usuario_asignacion_id'];
					$usuario_id = $sel['usuario_id'];
				}

				$usuario_aprobador_id = $usuario_id;
			}
			else
			{
				$result["http_code"] = 300;
				$result["status"] = "Alerta.";
				$result["error"] = "No existe la persona quien aprobara la solicitud.";
				
				echo json_encode($result);
				exit();
			}
		}	
		else
		{
			$result["http_code"] = 300;
			$result["status"] = "Alerta.";
			$result["error"] = "No puedes crear solicitudes de asignación";
			
			echo json_encode($result);
			exit();
		}
		
		if($usuario_aprobador_id > 0)
		{
			//INICIO VALIDAR SI LA ASIGNACION YA EXISTE EN LA MISMA ZONA CON EL MISMO USUARIO AQUIEN SE ESTA ASIGNANDO

			$sel_asignacion_existentes = $mysqli->query("
							SELECT
								id
							FROM mepa_asignacion_caja_chica
							WHERE status = 1 
								AND usuario_asignado_id = '".$int_usuario_asignado."' 
								AND zona_asignacion_id = '".$int_zona."' 
								AND situacion_etapa_id IN (1, 6) 
								AND situacion_etapa_id_tesoreria IN (10, 11)
							");

			$cant_sel_asignacion_existentes = $sel_asignacion_existentes->num_rows;

			if($cant_sel_asignacion_existentes !=0)
			{
				$result["http_code"] = 300;
				$result["status"] = "No se pudo registrar, Ya existe una asignación con la misma Zona.";
				$result["error"] = $error;

				echo json_encode($result);
				exit();
			}

			//FIN VALIDAR SI LA ASIGNACION YA EXISTE EN LA MISMA ZONA CON EL MISMO USUARIO AQUIEN SE ESTA ASIGNANDO

			// Validar si el usuario a enviar esta activo
			$sel_query = $mysqli->query("
			SELECT
				id, usuario, estado
			FROM tbl_usuarios
			WHERE estado = 1 AND id = '".$usuario_aprobador_id."'
			LIMIT 1
			");

			$cant_registro = $sel_query->num_rows;

			if($cant_registro > 0)
			{
				$query_insert = "INSERT INTO mepa_asignacion_caja_chica
							(
								tipo_solicitud_id,
								usuario_asignado_id,
								situacion_etapa_id,
								motivo,
								fondo_asignado,
								saldo_disponible,
								reportar_directorio,
								status,
								user_created_id,
								created_at,
								user_updated_id,
								updated_at,
								zona_asignacion_id,
								empresa_id,
								situacion_etapa_id_tesoreria
							) 
							VALUES 
							(
								1,
								'".$int_usuario_asignado."',
								1,
								'".$txt_motivo."',
								'".$txt_fondo_asignado."',
								'".$txt_fondo_asignado."',
								'".$int_se_reportara_usuario."',
								1,
								'".$login["id"]."', 
								'".date('Y-m-d H:i:s')."',
								'".$login["id"]."', 
								'".date('Y-m-d H:i:s')."',
								'".$int_zona."',
								'".$int_empresa."',
								10
							)";
				$mysqli->query($query_insert);

				$asignacion_id = mysqli_insert_id($mysqli);

				$query_insert_cuenta = "
								INSERT INTO mepa_asignacion_cuenta_bancaria
								(
									asignacion_id,
									banco_id,
									num_cuenta,
									status,
									user_created_id,
									created_at,
									user_updated_id,
									updated_at,
									verificado_tesoreria
								)
								VALUES
								(
									'" . $asignacion_id . "',
									'" . $int_banco . "',
									'" . $txt_numero_cuenta . "',
									1,
									'".$login["id"]."', 
									'".date('Y-m-d H:i:s')."',
									'".$login["id"]."', 
									'".date('Y-m-d H:i:s')."',
									6
								)
								";
				
				$mysqli->query($query_insert_cuenta);

				// INICIO: INSERTAR REPORTAR A USUARIOS DIRECTOR
				
				if($int_se_reportara_usuario == 1)
				{
					foreach($reportar_usuario as $usuario_reportar)
					{
						$query_insert_reportar_usuario = 
						"
							INSERT INTO mepa_reportar_directorio
							(
								asignacion_id,
								usuario_reportar,
								status,
								user_created_id,
								created_at,
								user_updated_id,
								updated_at
							)
							VALUES
							(
								'" . $asignacion_id . "',
								'" . $usuario_reportar . "',
								1,
								'".$login["id"]."', 
								'".date('Y-m-d H:i:s')."',
								'".$login["id"]."', 
								'".date('Y-m-d H:i:s')."'
							)
						";

						$mysqli->query($query_insert_reportar_usuario);
					}
				}

				// FIN: INSERTAR REPORTAR A USUARIOS DIRECTOR
			}
			else
			{
				$result["http_code"] = 300;
				$result["status"] = "No se registro, el usuario quien aprobara su solicitud no esta activo.";
				$result["error"] = $error;

				echo json_encode($result);
				exit();
			}
		}
		else
		{
			$result["http_code"] = 300;
			$result["status"] = "Alerta.";
			$result["error"] = "No existe la persona quien aprobara la solicitud.";
			
			echo json_encode($result);
			exit();
		}


		if($mysqli->error)
		{
			$error = $mysqli->error;
		}

		if ($error == '') 
		{
			if($usuario_aprobador_id > 0)
			{
				$result["http_code"] = 200;
				$result["status"] = "Datos obtenidos de gestión.";
				$result["error"] = $error;
				//REGISTRAR EL CORRELATIVO DE ASIGNACION Y LIQUIDACION
				registrar_correlativo($asignacion_id, $int_usuario_asignado);
				//ENVIAR EMAIL AL USUARIO ASIGNADO
				send_email_solicitud_asignacion_caja_chica($asignacion_id);
			}
		}
		else 
		{
			$result["http_code"] = 400;
			$result["status"] = "Datos no obtenidos.";
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

if(isset($_POST["accion"]) && $_POST["accion"]==="sec_btn_buscar_dni_personal") 
{
	$result["http_code"] = 400;

	$error = '';
	$usuario_id = $login?$login['id']:null;

	if((int)$usuario_id > 0)
	{
		$usuario_dni = $login?$login['dni']:null;
		
		$txt_buscar_dni = $_POST["txt_buscar_dni"];

		$sel_query = "";
		$valor_auto_asignacion = 0;
		$auto_asignacion = false;
		
		if($usuario_dni == $txt_buscar_dni)
		{
			$auto_asignacion = true;
		}

		$sel_query_buscar_creador = 
		"
			SELECT
				uad.id,
				uad.mepa_usuario_asignacion_id,
				uad.usuario_id,
				ua.reportar_gerencia
			FROM mepa_usuario_asignacion_detalle uad
				INNER JOIN mepa_usuario_asignacion ua
    			ON uad.mepa_usuario_asignacion_id = ua.id
				INNER JOIN tbl_usuarios u
				ON uad.usuario_id = u.id
				INNER JOIN tbl_personal_apt p
				ON u.personal_id = p.id
				LEFT JOIN tbl_razon_social rs
				ON p.razon_social_id = rs.id
				INNER JOIN tbl_areas a
				ON p.area_id = a.id
				INNER JOIN tbl_cargos c
				ON p.cargo_id = c.id
			WHERE uad.usuario_id = '".$usuario_id."' 
				AND uad.mepa_asignacion_rol_id = 1
				AND uad.status = 1
				AND ua.status = 1
			LIMIT 1
		";

		$query = $mysqli->query($sel_query_buscar_creador);

		$cant_registro = $query->num_rows;

		if($cant_registro > 0)
		{
			while($sel = $query->fetch_assoc())
			{
				$mepa_usuario_asignacion_id = $sel['mepa_usuario_asignacion_id'];
				$usuario_id = $sel['usuario_id'];
				$reportar_gerencia = $sel['reportar_gerencia'];
			}

			if($auto_asignacion === true)
			{
				if($reportar_gerencia == 1)
				{
					$valor_auto_asignacion = 1;
				}
			}

			$sel_query = 
			"
				SELECT
					u.id, p.dni, 
				    concat(IFNULL(p.nombre, ''),' ', IFNULL(p.apellido_paterno, ''), ' ', IFNULL(p.apellido_materno, '')) AS nombre_personal,
					p.razon_social_id, rs.nombre AS razon_social, 
				    p.zona_id, p.area_id, a.nombre AS area, p.cargo_id, c.nombre AS cargo
				FROM mepa_usuario_asignacion_detalle uad
					INNER JOIN tbl_usuarios u
					ON uad.usuario_id = u.id
					INNER JOIN tbl_personal_apt p
					ON u.personal_id = p.id
					LEFT JOIN tbl_razon_social rs
					ON p.razon_social_id = rs.id
					INNER JOIN tbl_areas a
					ON p.area_id = a.id
					INNER JOIN tbl_cargos c
					ON p.cargo_id = c.id
				WHERE p.dni = '".$txt_buscar_dni."' 
					AND uad.mepa_usuario_asignacion_id = '".$mepa_usuario_asignacion_id."' 
					AND uad.mepa_asignacion_rol_id = 3
					AND uad.status = 1
			";
		}	
		else
		{
			$result["http_code"] = 300;
			$result["status"] = "Alerta.";
			$result["error"] = "No puedes crear solicitudes de asignación";
			
			echo json_encode($result);
			exit();
		}
		
		$query = $mysqli->query($sel_query);

		$cant_registro = $query->num_rows;

		if($cant_registro > 0)
		{
			while($sel = $query->fetch_assoc())
			{
				$id = $sel['id'];
				$dni = $sel['dni'];
				$nombre_personal = $sel['nombre_personal'];
				$razon_social_id = $sel['razon_social_id'];
				$razon_social = $sel['razon_social'];
			}

			if($mysqli->error)
			{
				$error = $mysqli->error;
			}

			if ($error == '') 
			{
				$result["http_code"] = 200;
				$result["status"] = "Datos obtenidos de gestión.";
				$result["error"] = $error;
				$result["id_personal_dni"] = $id;
				$result["personal_dni"] = $dni;
				$result["personal_nombre"] = $nombre_personal;
				$result["personal_razon_social_id"] = $razon_social_id;
				$result["personal_razon_social"] = $razon_social;
				$result["informacion_texto"] = "No se encontró la razón social del usuario.";
				$result["valor_auto_asignacion"] = $valor_auto_asignacion;
			}
			else 
			{
				$result["http_code"] = 400;
				$result["status"] = "Error.";
				$result["error"] = $error;
				$result["id_personal_dni"] = "";
				$result["personal_dni"] = "";
				$result["personal_nombre"] = "";
				$result["personal_razon_social_id"] = "";
				$result["personal_razon_social"] = "";
				$result["valor_auto_asignacion"] = $valor_auto_asignacion;
			}
		}	
		else
		{
			$result["http_code"] = 300;
			$result["status"] = "No se encontraron resultados.";
			//$result["status"] = $sel_query;
			$result["error"] = $error;
			$result["id_personal_dni"] = "";
			$result["personal_dni"] = "";
			$result["personal_nombre"] = "";
			$result["valor_auto_asignacion"] = $valor_auto_asignacion;
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

function registrar_correlativo($asignacion_caja_chica_id, $int_usuario_asignado)
{
	include("db_connect.php");
	include("sys_login.php");

	// INICIO - VERIFICAR SI EXISTE ASIGNACION DE LA PERSONA QUE SE ESTA ASIGNANDO
	$num_correlativo = 1;
	$select_correlativo = 
	"
		SELECT
			a.id, a.usuario_asignado_id,
		    c.num_correlativo, c.tipo_solicitud
		FROM mepa_asignacion_caja_chica a
			INNER JOIN mepa_documento_correlativo c
			ON a.id = c.asignacion_id
		WHERE usuario_asignado_id = '".$int_usuario_asignado."' AND tipo_solicitud = 2
		ORDER BY c.num_correlativo DESC
		LIMIT 1
	";

	$list_select_correlativo = $mysqli->query($select_correlativo);

	$row_count = $list_select_correlativo->num_rows;

	if ($row_count > 0) 
	{
		$row = $list_select_correlativo->fetch_assoc();
		$id = $row["id"];
		$usuario_asignado_id = $row["usuario_asignado_id"];
		$num_correlativo = $row["num_correlativo"];
		$tipo_solicitud = $row["tipo_solicitud"];
	}

	// FIN - VERIFICAR SI EXISTE ASIGNACION DE LA PERSONA QUE SE ESTA ASIGNANDO

	// INICIO CORRELATIVO LIQUIDACION


	$query_insert_documento_correlativo = "INSERT INTO mepa_documento_correlativo
											(
												num_correlativo,
												asignacion_id,
												tipo_solicitud,
												created_at,
												updated_at
											) 
											VALUES 
											(
												'".$num_correlativo."',
												'".$asignacion_caja_chica_id."',
												2,
												'".date('Y-m-d H:i:s')."',
												'".date('Y-m-d H:i:s')."'
											)";

	$mysqli->query($query_insert_documento_correlativo);

	// FIN CORRELATIVO LIQUIDACION
	
}

function send_email_solicitud_asignacion_caja_chica($asignacion_caja_chica_id)
{
	include("db_connect.php");
	include("sys_login.php");

	$host = $_SERVER["HTTP_HOST"];
	$titulo_email = "";
	
	$sel_query = $mysqli->query("
	SELECT
		ma.id, mts.nombre AS tipo_solicitud, concat(IFNULL(tp.nombre, ''),' ', IFNULL(tp.apellido_paterno, '')) AS usuario_asignado,
		concat(IFNULL(tps.nombre, ''),' ', IFNULL(tps.apellido_paterno, '')) AS usuario_solicitante,
	    tc.nombre AS usuario_asignado_cargo, ce.situacion AS estado_solicitud, ma.motivo, ma.created_at AS fecha_solicitud,
	    uada.usuario_id AS usuario_aprobador_id
	FROM mepa_asignacion_caja_chica ma
		INNER JOIN mepa_tipos_solicitud mts
		ON ma.tipo_solicitud_id = mts.id
		INNER JOIN tbl_usuarios tu
		ON ma.usuario_asignado_id = tu.id
		INNER JOIN tbl_personal_apt tp
		ON tu.personal_id = tp.id
		INNER JOIN tbl_usuarios tus
		ON ma.user_created_id = tus.id
		INNER JOIN tbl_personal_apt tps
		ON tus.personal_id = tps.id
		INNER JOIN tbl_cargos tc
		ON tp.cargo_id = tc.id
		INNER JOIN cont_etapa ce
		ON ma.situacion_etapa_id = ce.etapa_id
		INNER JOIN mepa_usuario_asignacion_detalle uad
		ON ma.usuario_asignado_id = uad.usuario_id 
		AND uad.mepa_asignacion_rol_id = 3 AND uad.status = 1
	    INNER JOIN mepa_usuario_asignacion ua
		ON uad.mepa_usuario_asignacion_id = ua.id
		INNER JOIN mepa_usuario_asignacion_detalle uada
		ON ua.id = uada.mepa_usuario_asignacion_id 
		AND uada.mepa_asignacion_rol_id = 2 AND uada.status = 1
	WHERE ma.id = '".$asignacion_caja_chica_id."' AND ma.situacion_etapa_id = 1
	");

	$body = "";
	$body .= '<html>';

	$usuario_creacion_correo = '';

	while($sel = $sel_query->fetch_assoc())
	{
		$id = $sel['id'];
		$tipo_solicitud = $sel['tipo_solicitud'];
		$usuario_asignado = $sel['usuario_asignado'];
		$usuario_solicitante = $sel['usuario_solicitante'];
		$usuario_asignado_cargo = $sel['usuario_asignado_cargo'];
		$estado_solicitud = $sel['estado_solicitud'];
		$motivo = $sel['motivo'];
		$fecha_solicitud = $sel['fecha_solicitud'];
		$usuario_aprobador_id = $sel['usuario_aprobador_id'];
		

		$body .= '<div>';
		$body .= '<table border = "1" cellpadding="5" cellspacing="0" style="font-family: arial; width: 500px;">';

		$body .= '<thead>';
		
		$body .= '<tr>';
			$body .= '<th colspan="2" style="color: #fff; background-color: #395168; vertical-align: middle; font-size: 16px">';
				$body .= '<b>Nueva solicitud</b>';
			$body .= '</th>';
		$body .= '</tr>';

		$body .= '</thead>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Solicitud:</b></td>';
			$body .= '<td>'.$sel["tipo_solicitud"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Usuario por Asignar:</b></td>';
			$body .= '<td>'.$sel["usuario_asignado"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd; width: 100px;"><b>Usuario solicitante:</b></td>';
			$body .= '<td>'.$sel["usuario_solicitante"].'</td>';
		$body .= '</tr>';
		
		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Cargo:</b></td>';
			$body .= '<td>'.$sel["usuario_asignado_cargo"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Estado solicitud:</b></td>';
			$body .= '<td>'.$sel["estado_solicitud"].'</td>';
		$body .= '</tr>';

		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Motivo:</b></td>';
			$body .= '<td>'.$sel["motivo"].'</td>';
		$body .= '</tr>';
	
		$body .= '<tr>';
			$body .= '<td style="background-color: #ffffdd"><b>Fecha solicitud:</b></td>';
			$body .= '<td>'.$fecha_solicitud.'</td>';
		$body .= '</tr>';

		$body .= '</table>';
		$body .= '</div>';

	}
		$body .= '<div>';
			$body .= '<br>';
		$body .= '</div>';

		$body .= '<div style="width: 500px; text-align: center; font-family: arial;">';
		    $body .= '<a href="'.$host.'/?sec_id=mepa&sub_sec_id=detalle_solicitud_asignacion&id='.$asignacion_caja_chica_id.'" target="_blank">';
		    	$body .= '<button style="background-color: green; color: white; cursor: pointer; width: 50%;">';
					$body .= '<b>Ver Solicitud</b>';
		    	$body .= '</button>';
		    $body .= '</a>';
		$body .= '</div>';

		$body .= '</html>';
		$body .= "";

	$sub_titulo_email = "";

	if (env('SEND_EMAIL') == 'test')
	{
		$sub_titulo_email = "TEST SISTEMAS: ";
	}

	$titulo_email = $sub_titulo_email."Gestion - Sistema Mesa de Partes - Nueva Solicitud de Asignación Caja Chica ID: ".$asignacion_caja_chica_id;
	
	$cc = [
	];

	$bcc = [
	];

	// INICIO: LISTAR APROBADOR DEL USUARIO A QUIEN SE LE ESTA ASIGNANDO EL FONDO
	$select_usuarios_enviar_a = 
	"
		SELECT 
			p.correo
		FROM tbl_personal_apt p
			INNER JOIN tbl_usuarios u
			ON p.id = u.personal_id
		WHERE u.id = '".$usuario_aprobador_id."'
	";

	$sel_query_usuarios_enviar_a = $mysqli->query($select_usuarios_enviar_a);

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
	// FIN: LISTAR APROBADOR DEL USUARIO A QUIEN SE LE ESTA ASIGNANDO EL FONDO

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

echo json_encode($result);

?>